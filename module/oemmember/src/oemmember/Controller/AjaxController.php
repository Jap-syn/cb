<?php
namespace oemmember\Controller;

use Coral\Coral\Mail\CoralMail;
use oemmember\Application;
use oemmember\classes\SearchUtility;
use oemmember\classes\DuplicateRegistrationConfig;
use models\Logic\LogicSmbcRelation;
use models\Table\TableCancel;
use models\Table\TableOemClaimFee;
use models\Table\TableOemSettlementFee;
use models\Table\TablePayingAndSales;
use models\Table\TableStampFee;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Json\Json;
use Zend\Db\ResultSet\ResultSet;
use models\Table\TablePostalCode;
use models\Table\TableCsvSchema;
use models\Table\TableOrder;
use models\Logic\LogicCancel;
use models\Logic\OrderCancelException;
use models\Table\TableUser;
use models\Table\TableTemplateField;
use models\Table\ATableOrder;

class AjaxController extends AbstractActionController {
    /**
     * Adapter
     *
     * @var Adapter
     */
    private $db;

    /**
     * TablePostalCode
     *
     * @var TablePostalCode
     */
    private $postalCode;

    /**
     * 指定の郵便番号に前方一致する郵便番号データを返すアクション
     */
    public function getPostalDataAction() {
        $db = Application::getInstance()->dbAdapter;

        $res = $this->params()->fromPost( 'postalcode', '' );

        $code = preg_replace(
            '/[^\d]/',
            '',
            $res
        );
        if( ! empty( $code ) ) $code = $code . '%';

        $sql = " SELECT * FROM M_PostalCode WHERE PostalCode7 Like :code ";
        $prm = array(
                ':code' => $code,
        );
        $stm = $db->query($sql);
        $rs = new ResultSet();
        $rows = $rs->initialize($stm->execute($prm));

        echo Json::encode( $rows->toArray(), true );

        $res = $this->getResponse();
        $res->getHeaders()->addHeaderLine( 'Content-Type', 'text/html; charset=utf-8' );

        return $this->getResponse();
    }

    /**
     * 指定の注文データのキャンセル依頼を行うアクション
     */
    public function requestCancelAction() {
        $canceller = new LogicCancel( Application::getInstance()->dbAdapter );
        $mdlorder = new TableOrder(Application::getInstance()->dbAdapter);

        $orderSeq = $this->params()->fromPost( 'orderSeq', -1 );
        $reason = $this->params()->fromPost( 'reason', '' );
        $reasonCode = $this->params()->fromPost( 'reason_code', -1 );
        $userId = $this->params()->fromPost( 'userid', -1 );
        // 2.6 mail
        $cancelMailFlag = $this->params()->fromPost( 'cancel_mail_flag', 0 );
        // 取りまとめ注文か判定
        $order = $mdlorder->find((int)$orderSeq)->current();
        $combinedFlg = false;
        if (($order['CombinedClaimTargetStatus'] == 91 || $order['CombinedClaimTargetStatus'] == 92 )) {
            $combinedFlg = true;
        }
        // キャンセル処理
        $isToDo = 0;
        if ($canceller->_usedTodo2Pay($orderSeq) == true) {
            $isToDo = 1;
        }

        try {
            $resultSbps = $canceller->applies( (int)$orderSeq, $reason, $reasonCode, 0, true, $userId, $isToDo, Application::getInstance()->sbpsLogger);
            if (strlen( $resultSbps ) == 0) {
                if ( $isToDo == 1) {
                    // キャンセル確定メール送信
                    CoralMail::create( Application::getInstance()->dbAdapter, Application::getInstance()->smtpServer )->SendCancelMail($orderSeq, $userId);
                }
                $flag = true;
                $reasonCodeMes = $resultSbps;
                $errCodeSbps = '';
            } else {
                $flag = false;
                $reasonCodeMes = $resultSbps;
                $errCodeSbps = $resultSbps;
                if (strpos($resultSbps, "__sbps") !== false) {
                    $temp = explode('__sbps', $resultSbps);
                    $reasonCodeMes = $canceller->_SBPaymentMakeErrorInfoForAjax($temp[0]);
                    $errCodeSbps = $temp[0];
                }
            }

            echo Json::encode( array(
                'result' => $flag,
                'reasonCode' => $reasonCodeMes,
                'request' => array(
                    'order' => $orderSeq,
                    'reason' => $reason,
                    'reason_code' => $reasonCode,
                    'userid' => $userId
                ),
                'combinedFlg' => $combinedFlg,
                'isToDo' => $isToDo,
                'errCodeSbps' => $errCodeSbps,
                'Exc' => 0,
            ), true );
        } catch(OrderCancelException $cancelError) {
            echo Json::encode( array(
                'result' => false,
                'reasonCode' => $cancelError->getMessage(),
                'request' => array(
                    'order' => $orderSeq,
                    'reason' => $reason,
                    'reason_code' => $reasonCode,
                    'userid' => $userId
                ),
                'combinedFlg' => $combinedFlg,
                'isToDo' => $isToDo,
                'errCodeSbps' => '',
                'Exc' => 1,
            ), true );
        }

        $res = $this->getResponse();
        $res->getHeaders()->addHeaderLine( 'Content-Type', 'text/html; charset=utf-8' );

        return $this->getResponse();
    }

    /**
     * 指定の注文データのキャンセル依頼を行うアクション
     */
    public function requestCancelCancelAction() {
        $canceller = new LogicCancel( Application::getInstance()->dbAdapter );
        $mdlorder = new TableOrder(Application::getInstance()->dbAdapter);

        $orderSeq = $this->params()->fromPost( 'orderSeq', -1 );
        $userId = $this->params()->fromPost( 'userid', -1 );

        try {
            // 取りまとめ注文か判定
            $order = $mdlorder->find((int)$orderSeq)->current();
            $combinedFlg = false;
            if (($order['CombinedClaimTargetStatus'] == 91 || $order['CombinedClaimTargetStatus'] == 92 )) {
                $combinedFlg = true;
            }
            // キャンセル取消
            $result = $canceller->cancelApplies( (int)$orderSeq, true, $userId );

            echo Json::encode( array(
                    'result' => ( strlen( $result ) == 0 ? true : false ),
                    'reasonCode' => $result,
                    'request' => array(
                        'order' => $orderSeq,
                        'userid' => $userId
                    ),
                    'combinedFlg' => $combinedFlg,
            ), true );
        } catch(OrderCancelException $cancelError) {
            echo Json::encode( array(
                    'result' => false,
                    'reasonCode' => $cancelError->getMessage(),
                    'request' => array(
                        'order' => $orderSeq,
                        'userid' => $userId
                    ),
                    'combinedFlg' => $combinedFlg,
            ), true );
        }

        $res = $this->getResponse();
        $res->getHeaders()->addHeaderLine( 'Content-Type', 'text/html; charset=utf-8' );

        return $this->getResponse();
    }

    /**
     * 現在のアカウントのパスワードマッチングを行うアクション
     */
    public function checkPasswordAction() {
        try {
            $account = $this->getCurrentAccountData();

            $password = $this->params()->fromPost('pwd', '');

            $authUtil = Application::getInstance()->getAuthUtility()->setHashDisabled($account['Hashed'] ? false : true);

            $password = $authUtil->generatePasswordHash($account['LoginId'], trim($password));
            $curPassword = trim($account['LoginPasswd']);
            echo Json::encode( array('result' => (strcasecmp($curPassword, $password ) == 0)) );
        }
        catch(\Exception $err) {
            echo Json::encode(array('result' => 0, 'message' => $err->getMessage()));
        }

        $res = $this->getResponse();
        $res->getHeaders()->addHeaderLine( 'Content-Type', 'text/html; charset=utf-8' );

        return $this->getResponse();
    }

    /**
     * 現在のアカウントの、履歴検索結果のカラムオーダーを問い合わせるアクション
     */
    public function getSearchResultOrderAction() {
        $entId = Application::getInstance()->authManager->getUserInfo()->EnterpriseId;

        $result = array();
        $table = new TableCsvSchema( Application::getInstance()->dbAdapter );

        $defaultSchema = $table->getSchema( 0, SearchUtility::SCHEMA_CLASS );    // デフォルトスキーマを取得
        $maxOrder = -1;
        foreach( $table->getSchema( $entId, SearchUtility::SCHEMA_CLASS ) as $rowObj ) {
            $data = Json::decode( $rowObj['ApplicationData'] );

            $result[ $rowObj['ColumnName'] ] = array(
                'column' => $rowObj['ColumnName'],
                'order' => $rowObj['Ordinal'],
                'caption' => $rowObj['Caption'],
                'hidden' => $data->hidden ? true : false
            );

            $maxOrder = max($maxOrder, $rowObj->Ordinal);
        }
        // デフォルトスキーマよりスキーマサイズが小さい場合は不足分を補完
        $resultCount = 0;
        if (!empty($result)) {
            $resultCount = count($result);
        }
        $defSchCount = 0;
        if (!empty($defaultSchema)) {
            $defSchCount = count($defaultSchema);
        }
        if( $resultCount < $defSchCount ) {
            foreach($defaultSchema as $rowObj) {
                if( ! isset($result[$rowObj->ColumnName]) ) {
                    $data = Json::decode( $rowObj->ApplicationData );
                    $result[ $rowObj->ColumnName ] = array(
                        'column' => $rowObj->ColumnName,
                        'order' => $maxOrder++,
                        'caption' => $rowObj->Caption,
                        'hidden' => $data['hidden'] ? true : false
                    );
                }
            }
        }

        echo Json::encode( array(
            'result' => true,
            'data' => $result
        ) );

        $res = $this->getResponse();
        $res->getHeaders()->addHeaderLine( 'Content-Type', 'text/html; charset=utf-8' );

        return $this->getResponse();
    }

    /**
     * 現在のアカウントの、検索履歴結果のカラムスキーマを設定するアクション
     */
    public function setSearchResultColumnModifyAction() {
        try {
            $db = Application::getInstance()->dbAdapter;
            $entId = Application::getInstance()->authManager->getUserInfo()->EnterpriseId;

            $table = new TableCsvSchema( $db );
            if( ! $table->hasDelivedSchema($entId, SearchUtility::SCHEMA_CLASS) ) {
                $table->createSchema($entId, SearchUtility::SCHEMA_CLASS);
            }

            $db->getDriver()->getConnection()->beginTransaction();

            // 送信されたJSONデータを連想配列に復元
            $config = Json::decode( process_slashes($this->params()->fromPost('postData', '{}')) );

            // 共通の検索条件
//            $where = array(
//                $this->db->quoteInto( 'EnterpriseId = ?', $entId ),
//                $this->db->quoteInto( 'CsvClass = ?', SearchUtility::SCHEMA_CLASS )
//            );

            // 現在のスキーマデータを配列として退避
//            $rows = $table->fetchAll( $where )->toArray();
            $rs = new ResultSet();
            $rows = $rs->initialize($table->getSchema($entId, SearchUtility::SCHEMA_CLASS))->toArray();

            // デフォルトスキーマから未定義カラムを取得して追加しておく
            $max = $table->getMaxOrdinal($entId, SearchUtility::SCHEMA_CLASS);
//            foreach( $table->fetchAll( array(
//                $this->db->quoteInto('EnterpriseId = ?', 0),
//                $this->db->quoteInto('CsvClass = ?', SearchUtility::SCHEMA_CLASS),
//                $this->db->quoteInto("ColumnName NOT IN (SELECT ColumnName FROM T_CsvSchema WHERE EnterpriseId = ? AND CsvClass = ?)", $entId, SearchUtility::SCHEMA_CLASS)
//                ) ) as $defRow) {
//                $defRow->EnterpriseId = $entId;
//                $defRow->Ordinal = ++$max;
//                $rows[] = $defRow->toArray();
//            }
            $sql = " SELECT * FROM T_CsvSchema WHERE EnterpriseId = :EnterpriseId AND CsvClass = :CsvClass AND ColumnName NOT IN (SELECT ColumnName FROM T_CsvSchema WHERE EnterpriseId = :EnterpriseId AND CsvClass = :CsvClass) ";
            $prm = array(
                    ':EnterpriseId' => $entId,
                    ':CsvClass' => SearchUtility::SCHEMA_CLASS,
            );
            $stm = $db->query($sql);
            $defRow = $rs->initialize($stm->execute($prm))->toArray();
            if( !empty( $defRow ) ) {
                foreach( $defRow as $row ) {
                    $row['EnterpriseId'] = $entId;
                    $row['Ordinal'] = ++$max;
                    $rows += $row;
                }
            }

            // POSTされたデータで必要プロパティを上書きした挿入行を作成
            $newRows = array();
            foreach( $rows as $row ) {
                // 現在のApplicationDataを復元
                $appData = Json::decode( $row['ApplicationData'] );

                // 送信データを取得
                $data = $config->$row['ColumnName'];

                $row['Ordinal'] = (int)($data->order);
                $appData->hidden = $data->hidden;
                $row['ApplicationData'] = Json::encode( $appData );

                $newRows[ $row['Ordinal'] ] = $row;
            }

            // 現在行を削除
//            foreach($table->fetchAll( $where ) as $rowObj) {
//                $rowObj->delete();
//            };
            $sql = " DELETE FROM T_CsvSchema WHERE EnterpriseId = :EnterpriseId AND CsvClass = :CsvClass ";
            $prm = array(
                    ':EnterpriseId' => $entId,
                    ':CsvClass' => SearchUtility::SCHEMA_CLASS,
            );
            $stm = $db->query($sql);
            $stm->execute($prm);


            // 編集済みの行を挿入
            $sql = " INSERT INTO T_CsvSchema ( EnterpriseId, CsvClass, Ordinal, TableName, ColumnName, PrimaryFlg, ValidationRegex, Caption, ApplicationData ) VALUES ( :EnterpriseId, :CsvClass, :Ordinal, :TableName, :ColumnName, :PrimaryFlg, :ValidationRegex, :Caption, :ApplicationData ) ";
            foreach($newRows as $row) {
//                $table->insert( $row );
                $prm = array(
                        ':EnterpriseId' => $row['EnterpriseId'],
                        ':CsvClass' => $row['CsvClass'],
                        ':Ordinal' => $row['Ordinal'],
                        ':TableName' => $row['TableName'],
                        ':ColumnName' => $row['ColumnName'],
                        ':PrimaryFlg' => $row['PrimaryFlg'],
                        ':ValidationRegex' => $row['ValidationRegex'],
                        ':Caption' => $row['Caption'],
                        ':ApplicationData' => $row['ApplicationData']
                );
                $stm = $db->query($sql);
                $stm->execute($prm);
            }

            // コミット
            $db->getDriver()->getConnection()->commit();

            echo Json::encode( array(
                'result' => true
            ) );

        } catch(\Exception $err) {
            try {
                $db->getDriver()->getConnection()->rollback();
            } catch(\Exception $inError) {
            }

            echo Json::encode( array(
                'result' => false,
                'reason' => $err->getMessage()
            ) );
        }

        $res = $this->getResponse();
        $res->getHeaders()->addHeaderLine( 'Content-Type', 'text/html; charset=utf-8' );

        return $this->getResponse();
    }

    /**
     * 現在のアカウントの検索結果スキーマに対し、指定のカラムの表示状態を変更するアクション
     */
    public function setSearchResultColumnDisplayAction() {
        try {
            $db = Application::getInstance()->dbAdapter;

            $entId = Application::getInstance()->authManager->getUserInfo()->EnterpriseId;
            $column = $this->params()->fromRoute('col', '');

            $table = new TableCsvSchema( $db );
            if( ! $table->hasDelivedSchema($entId, SearchUtility::SCHEMA_CLASS) ) {
                $table->createSchema($entId, SearchUtility::SCHEMA_CLASS);
//                // クライアントにリトライさせる
//                echo Zend_Json::encode( array(
//                    'result' => false,
//                    'reason' => 'retry'
//                ) );
//Application::getInstance()->logger->debug("retry toggle column's visibility. column = $column.");
//                return;
            }

            $value = $this->params()->fromRoute('hidden');
            $value = ((bool)(preg_match('/^false$/i', $value) ? false : $value)) ? true : false;

            $row = null;
            // DBから現在のデータを取得
//             foreach( $table->fetchAll( array(
//                 $this->db->quoteInto( 'EnterpriseId = ?', $entId ),
//                 $this->db->quoteInto( 'CsvClass = ?', SearchUtility::SCHEMA_CLASS ),
//                 $this->db->quoteInto( 'ColumnName = ?', $column )
//             )) as $rowObj ) {
//                 $row = $rowObj;
// Application::getInstance()->logger->debug("target column found. column = $column.");
//                 break;
//             }
            $sql = " SELECT * FROM T_CsvSchema WHERE EnterpriseId = :EnterpriseId AND CsvClass = :CsvClass AND ColumnName = :ColumnName ";
            $prm = array(
                    ':EnterpriseId' => $entId,
                    ':CsvClass' => SearchUtility::SCHEMA_CLASS,
                    ':ColumnName' => $column
            );
            $stm = $db->query($sql);
            $row = $stm->execute($prm)->current();
Application::getInstance()->logger->debug("target column found. column = $column.");

            // 存在しない場合はデフォルトスキーマからの取得を試みる
            if( $row == null ) {
//                 foreach( $table->fetchAll( array(
//                     $this->db->quoteInto( 'EnterpriseId = ?', 0 ),
//                     $this->db->quoteInto( 'CsvClass = ?', SearchUtility::SCHEMA_CLASS ),
//                     $this->db->quoteInto( 'ColumnName = ?', $column )
//                 )) as $rowObj ) {
//                     $rowArray = $rowObj->toArray();
//                     $rowArray['EnterpriseId'] = $entId;        // EnterpriseIdを上書き
//                     // Ordinalを現在の最大値＋1に設定
//                     $rowArray['Ordinal'] = $table->getMaxOrdinal($entId, SearchUtility::SCHEMA_CLASS) + 1;
//                     $row = $table->createRow($rowArray);
// Application::getInstance()->logger->debug("target column cloned. column = $column.");
//                     break;
//                 }
                $sql = " SELECT * FROM T_CsvSchema WHERE EnterpriseId = :EnterpriseId AND CsvClass = :CsvClass AND ColumnName = :ColumnName ";
                $prm = array(
                        ':EnterpriseId' => 0,
                        ':CsvClass' => SearchUtility::SCHEMA_CLASS,
                        ':ColumnName' => $column
                );
                $stm = $db->query($sql);
                $rs = new ResultSet();
                $rowArray = $rs->initialize($stm->execute($prm))->current();
                $rowArray['EnterpriseId'] = $entId;        // EnterpriseIdを上書き
                // Ordinalを現在の最大値＋1に設定
                $rowArray['Ordinal'] = $table->getMaxOrdinal($entId, SearchUtility::SCHEMA_CLASS) + 1;
                $data = Json::decode($rowArray['ApplicationData']);
                $row = $rowArray;
Application::getInstance()->logger->debug("target column cloned. column = $column.");
            }

            // デフォルトスキーマにも存在しない場合は未定義なのでエラー
            if( $row == null ) {
                throw new \Exception( 'schema or data not found.' );
            }

            $data = Json::decode($row['ApplicationData']);
            $data->hidden = $value;
            $row['ApplicationData'] = Json::encode( $data );
//            $row->save();
            $sql = " UPDATE T_CsvSchema SET ApplicationData = :ApplicationData WHERE EnterpriseId = :EnterpriseId AND CsvClass = :CsvClass AND ColumnName = :ColumnName ";
            $prm = array(
                    ':ApplicationData' => $row['ApplicationData'],
                    ':EnterpriseId' => $entId,
                    ':CsvClass' => SearchUtility::SCHEMA_CLASS,
                    ':ColumnName' => $column
            );
            $stm = $db->query($sql);
            $stm->execute($prm);

            echo Json::encode( array(
                'result' => true,
                'data' => $data
            ) );
        } catch(\Exception $err) {
            echo Json::encode( array(
                'result' => false,
                'reason' => $err->getMessage()
            ) );
        }

        $res = $this->getResponse();
        $res->getHeaders()->addHeaderLine( 'Content-Type', 'text/html; charset=utf-8' );

        return $this->getResponse();
    }

    /**
     * 現在のアカウントの検索結果スキーマをデフォルトにリセットするアクション
     */
    public function resetSearchResultColumnSchemaAction() {
        try {
            $db = Application::getInstance()->dbAdapter;
            $entId = Application::getInstance()->authManager->getUserInfo()->EnterpriseId;

            $table = new TableCsvSchema( $db );

            $db->getDriver()->getConnection()->beginTransaction();


//             foreach( $table->fetchAll( array(
//                 $this->db->quoteInto( 'EnterpriseId = ?', $entId ),
//                 $this->db->quoteInto( 'CsvClass = ?', SearchUtility::SCHEMA_CLASS )
//             ) ) as $rowObj ) {
//                 $rowObj->delete();
//             }

            $sql = " DELETE FROM T_CsvSchema WHERE EnterpriseId = :EnterpriseId AND CsvClass = :CsvClass ";
            $prm = array(
                    ':EnterpriseId' => $entId,
                    ':CsvClass' => SearchUtility::SCHEMA_CLASS,
            );
            $stm = $db->query($sql);
            $stm->execute($prm);

            $db->getDriver()->getConnection()->commit();

            echo Json::encode( array(
                'result' => true
            ) );
        } catch(\Exception $err) {
            $db->getDriver()->getConnection()->rollback();
            echo Json::encode( array(
                'result' => false,
                'reason' => $err->getMessage()
            ) );
        }

        $res = $this->getResponse();
        $res->getHeaders()->addHeaderLine( 'Content-Type', 'text/html; charset=utf-8' );

        return $this->getResponse();
    }

    /**
     * 現在のアカウントの、伝票登録前の注文件数の情報を返すアクション。与信中および伝票登録待ちの情報を返す。
     */
    public function getOrderSummariesAction() {
        try {
            $entId = Application::getInstance()->authManager->getUserInfo()->EnterpriseId;

            $q = "SELECT
    CASE V.StatusMode
        WHEN 1 THEN 'summary_ready_count'
        ELSE 'summary_incre_count'
    END AS DataId,
    COUNT(*) AS Count
FROM
    (
        SELECT
            CASE DataStatus
                WHEN 31 THEN 1
                ELSE 0
            END AS StatusMode
        FROM
            T_Order
        WHERE
            EnterpriseId = :entId AND
            Cnl_Status = 0 AND
            DataStatus <= 31
    ) V
GROUP BY
    V.StatusMode";

    $stm = Application::getInstance()->dbAdapter->query($q);
    $prm = array(
       ':entId' => $entId,
    );
    $rs = new ResultSet();
    $rows = $rs->initialize($stm->execute($prm))->toArray();
            echo Json::encode( array(
                'result' => true,
                'data' => $rows
            ) );
        } catch(\Exception $err) {
            echo Json::encode( array(
                'result' => false,
                'reason' => $err->getMessage()
            ) );
        }

        $res = $this->getResponse();
        $res->getHeaders()->addHeaderLine( 'Content-Type', 'text/html; charset=utf-8' );

        return $this->getResponse();
    }

    /**
     * 指定された任意注文番号を検索する
     */
    public function entorderidcheckAction() {
        //注文情報
        $mdlorder = new TableOrder(Application::getInstance()->dbAdapter);

        $str = $this->params()->fromPost('entid');
        $entId = Application::getInstance()->authManager->getUserInfo()->EnterpriseId;

        try {

            $entId = $mdlorder->searchEntId($str, $entId);

            if(!empty($entId["Ent_OrderId"])) {
                $msg = '1';
            } else {
                $msg = '0';
            }
        } catch(\Exception $e) {
            $msg = $e->getMessage();
        }

        echo Json::encode(array('status' => $msg));

        $res = $this->getResponse();
        $res->getHeaders()->addHeaderLine( 'Content-Type', 'text/html; charset=utf-8' );

        $this->getResponse();
    }

    public function dupconfigAction() {
        $config = new DuplicateRegistrationConfig();

        $mode = $this->params()->fromRoute('mode', 'get');
        $key = $this->params()->fromRoute('key', '');
        $value = $this->params()->fromRoute('value', '');
        $value = preg_match('/^(true|1|yes)$/i', $value) ? true : false;

        if($mode == 'set') {
            $config->$key = $value;
        }

        echo $config->export();

        return $this->response;
    }

    protected function getCurrentAccountData() {
        $app = Application::getInstance();
        $table = new \models\Table\TableEnterprise($app->dbAdapter);
        $row = $table->find($app->authManager->getUserInfo()->EnterpriseId)->current();
        if ($row) {
            return $row;
        }
        throw new \Exception('account data not found');
    }

    /**
     * 保留注文を次回から表示しない場合
     */
    public function defectInvisibleAction() {
        $app = Application::getInstance();
        $mdlao = new ATableOrder($app->dbAdapter);

        // トランザクション開始
        $app->dbAdapter->getDriver()->getConnection()->beginTransaction();
        try {

$sql =<<<EOQ
SELECT  o.OrderSeq
FROM  T_Order o
      INNER JOIN AT_Order ao ON o.OrderSeq = ao.OrderSeq
WHERE 1 = 1
AND   ao.DefectFlg = 1
AND   ao.DefectInvisibleFlg = 0
AND   o.Cnl_Status = 0
AND   o.DataStatus < 31
AND   o.EnterpriseId = :EnterpriseId
EOQ;

            $ri = $app->dbAdapter->query($sql)->execute(array(':EnterpriseId' => $app->authManager->getUserInfo()->EnterpriseId));
            foreach ($ri as $row) {
                $oseq = $row['OrderSeq'];

                // AT_Orderの更新
                $data = array(
                    'DefectInvisibleFlg' => '1',
                );
                $mdlao->saveUpdate($data, $oseq);
            }

            // コミット
            $app->dbAdapter->getDriver()->getConnection()->commit();

            echo Json::encode(array('result' => 1));
        }
        catch(\Exception $err) {
            // ロールバック
            $app->dbAdapter->getDriver()->getConnection()->rollback();
            echo Json::encode(array('result' => 0, 'message' => $err->getMessage()));
        }

        $res = $this->getResponse();
        $res->getHeaders()->addHeaderLine( 'Content-Type', 'text/html; charset=utf-8' );

        return $this->getResponse();
    }
}

