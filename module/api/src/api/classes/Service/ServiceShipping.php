<?php
namespace api\classes\Service;

use api\Application;
use api\classes\Service\ServiceAbstract;
use api\classes\Service\Shipping\ServiceShippingConst;
use api\classes\Service\Response\ServiceResponseShipping;
use api\classes\Service\ServiceException;
use models\Logic\MergeOrder\LogicMergeOrderHelper;
use models\Table\TableOemSettlementFee;
use models\Table\TableOrder;
use models\Table\TableOrderItems;
use models\Table\TablePayingAndSales;
use models\Table\TableEnterprise;
use models\Table\TableOrderSummary;
use zend\Db\ResultSet\ResultSet;
use models\Logic\LogicDeliveryMethod;
use models\Table\TableSite;
use models\Table\TableSystemProperty;
use models\Table\TablePricePlan;
use models\Table\TableUser;
use Coral\Coral\History\CoralHistoryOrder;
use models\Table\ATablePayingAndSales;
use models\Logic\LogicCampaign;
use models\Table\TableBatchLock;
use models\Table\TablePrePayingAndSales;


/**
 * 伝票番号登録サービスクラス
 */
class ServiceShipping extends ServiceAbstract {
    /**
     * 検証対象の入力パラメータとラベルのペアを格納したスキーマを取得する
     *
     * @static
     * @access protected
     * @return array
     */
    protected static function __getParamsSchema() {
        return array(
            array('key' => ServiceShippingConst::ORDER_ID, 'label' => '注文ID'),
            array('key' => ServiceShippingConst::DELIV_ID, 'label' => '配送会社')
        );
    }

    /**
     * 伝票番号登録APIのサービスID
     * @var string
     */
    protected $_serviceId = "04";

    /**
     * 注文ID
     *
     * @var string
     */
    public $orderId;

    /**
     * 配送会社ID
     *
     * @var string
     */
    public $delivId;

    /**
     * 伝票番号
     *
     * @var string
     */
    public $journalNumber;

    /**
     * ユーザーID
     *
     * @var string
     */
    public $opId;

    /**
     * 初期化処理
     *
     * @access protected
     */
    protected function init() {
        // サイトIDチェックは行わない
        $this->_checkSiteId = false;

        // レスポンスを初期化
        $this->_response = new ServiceResponseShipping();

        // 認証用
        $this->_apiUserId = $this->_data[ServiceShippingConst::API_USER_ID];
        $this->_enterpriseId = $this->_data[ServiceShippingConst::ENTERPRISE_ID];

        // 登録向けデータ
        $this->orderId = $this->_data[ServiceShippingConst::ORDER_ID];
        $this->delivId = trim($this->_data[ServiceShippingConst::DELIV_ID]);
        $this->journalNumber = $this->_data[ServiceShippingConst::JOURNAL_NUMBER];
        // 登録データをレスポンスへ反映
        $this->_response->orderId = $this->orderId;
        $this->_response->delivId = $this->delivId;
        $this->_response->journalNum = $this->journalNumber;

        // ログ出力
        Application::getInstance()->logger->info(
            get_class($this) . '#init() ' .
            join(', ', array(
                sprintf('%s: %s', ServiceShippingConst::ENTERPRISE_ID, $this->_enterpriseId),
                sprintf('%s: %s', ServiceShippingConst::API_USER_ID, $this->_apiUserId),
                sprintf('RemoteAddr: %s', f_get_client_address())       // 2015/09/23 Y.Suzuki Mod f_get_client_address をｺｰﾙするように変更
            )) );
    }

    /**
     * 入力に対する検証を行う
     *
     * @access protected
     * @return boolean 検証結果
     */
    protected function check() {
        $props = array(
            array('key' => ServiceShippingConst::ORDER_ID, 'label' => '注文ID'),
            array('key' => ServiceShippingConst::DELIV_ID, 'label' => '配送会社'),
            array('key' => ServiceShippingConst::JOURNAL_NUMBER, 'label' => '伝票番号')
        );
        try {
            // 注文ID、配送会社ID、伝票番号のnull/ブランクチェック
            $this->checkRequireParams($this->_data);

            // 注文データの存在確認
            // → 登録可能データがない場合はgetOrderData()で例外があがるのでチェック不要
            $order = $this->getOrderData($this->orderId);

            // 配送会社データ取得
            // → 登録可能データがない場合はgetDelivMaster()で例外があがるのでチェック不要
            $delivData = $this->getDelivMaster($this->delivId);

            // 物販用の配送会社ではありません
            if ($delivData['ProductServiceClass'] != '0') {
                throw new ServiceException(
                    '物販用の配送会社ではありません', $this->_serviceId, '202' );
            }

            // 配送方法が確定したので配送会社名をレスポンスへ反映
            $this->_response->delivName = $delivData['DeliMethodName'];

            // 伝票番号の検証
            if ($delivData['JournalRegistClass'] == '0') {
                ;// 伝票番号登録区分(0：不要)が指定されている場合はチェックなし(20151119)
            }else{
                // 配送会社.伝票番号登録区分が1:要で伝票番号が未入力
                if(!strlen($this->journalNumber)) {
                    throw new ServiceException(
                    sprintf('%s : データを0または空にすることはできません', '伝票番号'), $this->_serviceId, '201' );
                }
                $this->checkJournalNumber($this->delivId, $this->journalNumber);
            }
        } catch(ServiceException $svcErr) {
            // 検証エラー
            $this->_response->addMessage(
                $svcErr->getFormattedErrorCode(),
                $svcErr->getMessage() );
        } catch(\Exception $err) {
            // その他のエラー
            Application::getInstance()->logger->info(sprintf('%s#check() ERROR: %s', get_class($this), $err->getMessage()));
            $this->errorHandle($err);
        }

        return !empty($this->_response->messages) ? false : true;
    }

    /**
     * サービスを実行する
     *
     * @access protected
     * @return boolean サービス実行結果
     */
    protected function exec() {
        $db = $this->_db;
        $db->getDriver()->getConnection()->beginTransaction();
        try {
            try {
                // 対象の注文データを取得
                /** @var string */
                $order = $this->getOrderData($this->orderId);

                // 配送方法を取得する
                /** @var array */
                $deliMethod = $this->getDelivMaster($this->delivId);

                 if(strlen($this->journalNumber)) {
                     // 伝票番号を再確認
                     $this->checkJournalNumber($this->delivId, $this->journalNumber);
                 }
            } catch(ServiceException $svcErr) {
                // 検証エラーで終了
                $this->_response->addMessage(
                    $svcErr->getFormattedErrorCode(),
                    $svcErr->getMessage() );
                return false;
            }

            // APIユーザーID
            $mdluser = new TableUser($this->_db);
            $this->opId = $mdluser->getUserId(3, $this->_data[ServiceShippingConst::API_USER_ID]);

            if ($order['T_OrderClass'] == '1') {
                $order['DataStatus'] = 91;          // 91：クローズ
                $order['CloseReason'] = 5;          // 5：テスト注文クローズ
            } else {
                // ステータスを請求書発行待ちにする
                $order['DataStatus'] = 41;
            }

            // 配送方法に応じたキャンセル可能フラグ設定を行う
            $order['Cnl_CantCancelFlg'] = $deliMethod['EnableCancelFlg'] == 1 ? 0 : 1;
            // 永続化
//            $order->save();
            $sql  = ' UPDATE T_Order SET ';
            $sql .= ' DataStatus = :DataStatus, CloseReason = :CloseReason, Cnl_CantCancelFlg = :Cnl_CantCancelFlg, ';
            $sql .= ' UpdateDate = :UpdateDate, UpdateId = :UpdateId ';
// Mod By Takemasa(NDC) 20151203 Stt メール便(伝票番号登録区分が[0：不要]な配送方法時)の着荷確認を取らない
//             if ($deliMethod['JournalRegistClass'] == 0 ) {
//                 $sql .= ' , Deli_ConfirmArrivalFlg = :Deli_ConfirmArrivalFlg ';
//                 $sql .= ' , Deli_ConfirmArrivalDate = :Deli_ConfirmArrivalDate ';
//             }
            $sql .= ' , Deli_ConfirmArrivalFlg = 0 ';
// Mod By Takemasa(NDC) 20151203 End メール便(伝票番号登録区分が[0：不要]な配送方法時)の着荷確認を取らない
            $sql .= ' WHERE OrderSeq = :OrderSeq ';
            $prm = array(
                    ':DataStatus'           => $order['DataStatus'],
                    ':CloseReason'          => $order['CloseReason'],
                    ':Cnl_CantCancelFlg'    => $order['Cnl_CantCancelFlg'],
                    ':UpdateDate'           => date('Y-m-d H:i:s'),
                    ':UpdateId'             => $this->opId,
                    ':OrderSeq'             => $order['OrderSeq'],
            );
// Del By Takemasa(NDC) 20151203 Stt メール便(伝票番号登録区分が[0：不要]な配送方法時)の着荷確認を取らない
//             if ($deliMethod['JournalRegistClass'] == 0 ) {
//                 $prm = array_merge($prm, array(':Deli_ConfirmArrivalFlg' => 1, ':Deli_ConfirmArrivalDate' => date('Y-m-d H:i:s')));
//             }
// Del By Takemasa(NDC) 20151203 End メール便(伝票番号登録区分が[0：不要]な配送方法時)の着荷確認を取らない
            $stm = $db->query($sql);
            $stm->execute($prm);

            // 注文商品に配送方法関連データを設定
            /** @var TableOrderItems */
//            $items = $this->getOrderItems($order['OrderSeq']);
//            foreach($items as $orderItem) {
//                $orderItem['Deli_DeliveryMethod'] = $deliMethod['DeliMethodId'];
//                $orderItem['Deli_JournalNumber'] = $this->journalNumber;
//                $orderItem['Deli_JournalIncDate'] = date('Y-m-d');
//                $orderItem->save();
//            }
            $mdls = new TableSite( $this->_db );
            $mdloi = new TableOrderItems( $this->_db );
            $mdlsp = new TableSystemProperty( $this->_db );

            $site =  $mdls->findSite( $order['SiteId'] )->current();

            $deliConfirmArrivalFlg = 0;
            $deliConfirmArrivalDate = null;
            $deliConfirmArrivalOpId = null;

            // 伝票番号登録区分(0：不要)が指定されている場合でも、入力値がある場合はそれを優先する(20151119)
            if ($deliMethod['JournalRegistClass'] == 0 && strlen($this->journalNumber) == 0) {
                $this->journalNumber = $mdlsp->getValue( '[DEFAULT]', 'systeminfo', DummyJournalNumber );
            }

// Del By Takemasa(NDC) 20151203 Stt メール便(伝票番号登録区分が[0：不要]な配送方法時)の着荷確認を取らない
//             if ($deliMethod['JournalRegistClass'] == 0 ) {
//                 $deliConfirmArrivalFlg = 1;         // "1"(着荷確認)
//                 $deliConfirmArrivalDate = date('Y-m-d H:i:s');
//                 $deliConfirmArrivalOpId = $this->_apiUserId;
//             }
// Del By Takemasa(NDC) 20151203 End メール便(伝票番号登録区分が[0：不要]な配送方法時)の着荷確認を取らない

            $data = array(
                    'Deli_DeliveryMethod'      => $deliMethod['DeliMethodId'],
                    'Deli_JournalNumber'       => $this->journalNumber,
                    'Deli_JournalIncDate'      => date('Y-m-d H:i:s'),
                    'Deli_ConfirmArrivalFlg'   => $deliConfirmArrivalFlg,
                    'Deli_ConfirmArrivalDate'  => $deliConfirmArrivalDate,
                    'Deli_ConfirmArrivalOpId'  => $deliConfirmArrivalOpId,
                    'UpdateDate'               => date('Y-m-d H:i:s'),
                    'UpdateId'                 => $this->opId,
            );
            $prm = array(
                    'OrderSeq'             => $order['OrderSeq'],
            );

            $mdloi->saveUpdateWhere($data, $prm);

            // 20131210 tkaki 請求取りまとめ　メール便の場合には請求取りまとめを落とす
            $mghelper = new LogicMergeOrderHelper($db, $order['OrderSeq']);
            // 配送方法の状況によって請求取りまとめを更新する
            if($mghelper->chkCcTargetStatusByDelivery($deliMethod['DeliMethodId']) != 9) {
                $order['CombinedClaimTargetStatus'] = $mghelper->chkCcTargetStatusByDelivery($deliMethod['DeliMethodId']);
//                $order->save();
                $sql = ' UPDATE T_Order SET CombinedClaimTargetStatus = :CombinedClaimTargetStatus WHERE OrderSeq = :OrderSeq ';
                $prm = array(
                        ':CombinedClaimTargetStatus'    => $order['CombinedClaimTargetStatus'],
                        ':OrderSeq'                     => $order['OrderSeq'],
                );
                $stm = $db->query($sql);
                $stm->execute($prm);
            }

            // 立替・売上データを新規作成
            $this->insertPayingAndSales($order, $deliMethod);

            // OEM決済手数料データを新規作成（2014.9.9 eda）
            $order['RegistId'] = $this->opId;
            $order['UpdateId'] = $this->opId;
            $this->insertOemSettlementFee($order);

            // 注文サマリを更新
            $this->updateOrderSummary($order['OrderSeq']);

            // 注文履歴の登録
            $history = new CoralHistoryOrder($this->_db);
            $userId = $this->opId;
            $history->InsOrderHistory($order['OrderSeq'], 31, $userId);

            // DB更新確定
            $db->getDriver()->getConnection()->commit();
        } catch(\Exception $err) {
            // 例外発生時はロールバックだけ行って上位に再スロー
            $db->getDriver()->getConnection()->rollBack();
            throw $err;
        }
        return true;
    }

    /**
     * 処理結果を文字列として返却する
     *
     * @access protected
     * @return string 処理結果
     */
    protected function returnResponse() {
        return $this->_response->serialize();
    }

    /**
     * 入力パラメータの必須検証を行う。
     * 検証エラーが発生した場合は{@link ServiceException}がスローされる
     *
     * @access protected
     * @return bool
     */
    protected function checkRequireParams(array $data) {
        $schema = self::__getParamsSchema();
        foreach($schema as $param) {
            $val = trim((string)$data[$param['key']]);
            if(!strlen($val)) {
                throw new ServiceException(
                    sprintf('%s : データを0または空にすることはできません', $param['label']), $this->_serviceId, '201' );
            }
        }
        return true;
    }

    /**
     * 指定注文IDの注文データを取得する。
     * 対象のデータが未キャンセルで伝票番号登録待ちの場合のみデータを返し
     * それ以外は{@link ServiceException}がスローされる
     *
     * @access protected
     * @param string $orderId 注文ID
     * @return array
     */
    protected function getOrderData($orderId) {
        $db = $this->_db;
//         $orders = new TableOrder($this->_db);
//         $where = join(' AND ', array(
//             $db->quoteInto('OrderId = ?', $orderId),
//             $db->quoteInto('EnterpriseId = ?', $this->_enterpriseId),
//             'DataStatus = 31',
//             'Cnl_Status = 0'
//         ));
//         $order = $orders->fetchAll($where);
        $sql = ' SELECT * FROM T_Order WHERE OrderId = :OrderId AND EnterpriseId = :EnterpriseId AND DataStatus = 31 AND Cnl_Status = 0 AND ValidFlg = 1 ';
        $where = array(
            ':OrderId'      =>  $orderId,
            ':EnterpriseId' =>  $this->_enterpriseId,
        );
        $stm = $db->query($sql);
        $rs = new ResultSet();
        $order = $rs->initialize($stm->execute($where))->toArray();
        $ordrCount = 0;
		if (!empty($order)) {
		    $orderCount = count($order);
		}
		if($orderCount != 1) {
            throw new ServiceException(
                '指定の注文は登録されていないか伝票番号登録可能ではありません',
                $this->_serviceId, '301');
        }
        return current($order);
    }

    /**
     * 指定IDに一致する配送方法データを取得する。
     * 対象のマスターデータが存在しない場合は{@link ServiceException}がスローされる
     *
     * @access protected
     * @param int $delivId 配送方法ID
     * @return array
     */
    protected function getDelivMaster($delivId) {

        $delilogic = new LogicDeliveryMethod($this->_db);

        // 加盟店向け配送方法を取得する
        $masters = $delilogic->getEnterpriseDeliveryMethodList($this->_enterpriseId, false);

        if (!isset($masters[$delivId])) {
            throw new ServiceException(
                '配送会社 : 入力データが不正です',
                $this->_serviceId, '202' );
        }

        return $this->_db->query(" SELECT * FROM M_DeliveryMethod WHERE DeliMethodId = :DeliMethodId "
            )->execute(array(':DeliMethodId' => $delivId))->current();
    }

    /**
     * 指定の伝票番号を指定の配送方法のパターンで検証する。
     * 配送方法指定の不備や検証エラーが発生した場合は{@link ServiceException}がスローされる
     *
     * @access protected
     * @param mixed $delivId 配送方法ID
     * @param string $journalNumber 伝票番号
     * @return bool
     */
    protected function checkJournalNumber($delivId, $journalNumber) {
        $delivData = $this->getDelivMaster($delivId);
        $reg = $delivData['ValidateRegex'];
        if(!strlen($reg)) $reg = '^.{1,255}$';
        if(strlen($journalNumber) > 255 || !mb_ereg($reg, $journalNumber)) {
            throw new ServiceException(
                '伝票番号 : 入力データが不正です', $this->_serviceId, '202' );
        }
        return true;
    }

    /**
     * 指定注文に関連する注文商品データを取得する
     *
     * @param int $orderSeq 注文SEQ
     * @return array
     */
    protected function getOrderItems($orderSeq) {
//        $table = new TableOrderItems($this->_db);
//        return $table->fetchAll($this->_db->quoteInto('OrderSeq = ?', $orderSeq));
        $sql = ' SELECT * FROM T_OrderItems WHERE OrderSeq = :OrderSeq ';
        $stm = $this->_db->query($sql);
        $prm = array(
                ':OrderSeq' => $orderSeq,
        );
        $rs = new ResultSet();
        $orderItems = $rs->initialize($stm->execute($prm))->toArray();

        return $orderItems;
    }

    /**
     * 注文データをベースに、立替・売上管理データを新規挿入する
     *
     * @access protected
     * @param array $orderRow 注文データ
     * @return ServiceShipping このインスタンス
     */
    protected function insertPayingAndSales($orderRow, $deliMethod) {
        $pasTable = new TablePayingAndSales($this->_db);
        $blTable = new TableBatchLock($this->_db);
        $ppasTable = new TablePrePayingAndSales($this->_db);

        // キャンペーン期間中はキャンペーン情報で登録する
        // 決済手数料率と請求手数料の取得
        $logic = new LogicCampaign($this->_db);
        $campaign = $logic->getCampaignInfo($this->_enterpriseId, $orderRow['SiteId']);

        // 2015/10/14 Y.Suzuki Add 会計対応 Stt
        // マスタから取得した請求手数料は税抜金額のため、消費税額を算出後、足しこむ。
        $mdlsys = new TableSystemProperty($this->_db);
        $taxRate = ($mdlsys->getTaxRateAt(date('Y-m-d')) / 100);

        // 請求手数料
        $claimFee = (int)($campaign['ClaimFeeBS'] + ($campaign['ClaimFeeBS'] * $taxRate));
        $campaign['ClaimFeeBS'] = $claimFee;        // 算出した税込金額を上書き。
        // 2015/10/14 Y.Suzuki Add 会計対応 End

        $pasRow = $pasTable->newRow(
            $orderRow['OrderSeq'],
            $orderRow['UseAmount'],
            $campaign['SettlementFeeRate'],
            $campaign['ClaimFeeBS']
        );
//        $pasRow->save();

// Del By Takemasa(NDC) 20151203 Stt メール便(伝票番号登録区分が[0：不要]な配送方法時)の着荷確認を取らない
//         // 伝票番号登録不要 かつ 補償外でない場合
//         if ($deliMethod['JournalRegistClass'] == 0 && nvl($orderRow['OutOfAmends'], 0) != 1) {
//             $pasRow['ClearConditionForCharge'] = 1;                 // 立替条件クリアフラグ
//             $pasRow['ClearConditionDate'] = date('Y-m-d');          // 立替条件クリア日
//         }
// Del By Takemasa(NDC) 20151203 End メール便(伝票番号登録区分が[0：不要]な配送方法時)の着荷確認を取らない

        $pasRow['RegistId'] = $this->opId; // 登録者
        $pasRow['UpdateId'] = $this->opId; // 更新者

        //立替精算仮締めバッチロック状態取得
        $Locked = $blTable->findId(4, 1)->current()['BatchLock'];

        //立替精算仮締め処理中は「退避_立替・売上管理テーブル」に保存
        if($Locked == 0){
            $seq = $pasTable->saveNew($pasRow);        // 2015/10/06 Y.Suzuki 会計対応 Mod
        }else{
            $ppasTable->saveNew($pasRow);
        }

        // 立替精算仮締め処理中でないときのみ保存
        if($Locked == 0){
            // 2015/10/06 Y.Suzuki Add 会計対応 Stt
            // 会計用項目のINSERT
            $mdlatpas = new ATablePayingAndSales($this->_db);
            $mdlatpas->saveNew(array('Seq' => $seq));
            // 2015/10/06 Y.Suzuki Add 会計対応 End
        }

        return $this;
    }

    /**
     * 指定注文のサマリデータを更新する
     *
     * @param int $orderSeq 注文SEQ
     * @return ServiceShipping このインスタンス
     */
    protected function updateOrderSummary($orderSeq) {
        $tbl = new TableOrderSummary($this->_db);
        $tbl->updateSummary($orderSeq, $this->opId);

        return $this;
    }

    /**
     * 指定注文からOEM決済手数料を新規挿入する
     *
     * @access protected
     * @param array $orderRow 注文データ
     * @return ServiceShipping このインスタンス
     */
    protected function insertOemSettlementFee($orderRow) {
        $tbl = new TableOemSettlementFee($this->_db);
        $tbl->saveOemSettlementFee($orderRow);

        return $this;
    }
}
