<?php

namespace models\Logic;

use Zend\Db\Adapter\Adapter;
use models\Table\TableUser;
use models\Table\TableOrder;
use models\Table\TableCombinedDictate;
use models\Sequence\SequenceGeneral;
use models\Table\TableCombinedList;
use models\Table\TableManagementCustomer;
use models\Table\TableCombinedHistory;
use models\Table\TableSystemProperty;
use models\Table\TableEnterpriseCustomer;
use Zend\Db\ResultSet\ResultSet;
use Zend\Http\Client;
use Coral\Base\IO\BaseIOUtility;

/**
 * 名寄せリスト作成／更新ロジック
 */
class LogicCombinedList {

    /**
     * アダプタ
     *
     * @var Adapter
     */
    protected $_adapter = null;

    /**
     * ユーザID
     *
     * @var int
     */
    protected $_userId = null;

    /**
     * アプリケーション設定
     *
     * @access protected
     * @var array (Zend\Config\Reader\Ini)
     */
    protected $_config = null;

    /**
     * コンストラクタ
     *
     * @param Adapter $adapter
     *            アダプタ
     */
    public function __construct(Adapter $adapter, $config = null) {
        $this->_adapter = $adapter;
        $this->_config = $config;
    }

    /**
     * 顧客統合指示を行った顧客を統合
     * @throws Exception
     */
    public function combine() {
        try {
            // トランザクション開始
            $this->_adapter->getDriver ()->getConnection ()->beginTransaction ();

            $mdlu = new TableUser ( $this->_adapter );
            $mdlcl = new TableCombinedList ( $this->_adapter );
            $mdlmc = new TableManagementCustomer ( $this->_adapter );
            $mdlch = new TableCombinedHistory ( $this->_adapter );
            $mdlsg = new SequenceGeneral ( $this->_adapter );
            $mdlsp = new TableSystemProperty ( $this->_adapter );
            $mdlec = new TableEnterpriseCustomer( $this->_adapter );

            $this->_userId = $mdlu->getUserId ( TableUser::USERCLASS_SYSTEM, TableUser::SEQ_BATCH_USER );

            // 名寄せリストから顧客統合対象リストを取得する
            $ri = $mdlcl->getCustomerCombinedList ();
            $rs = new ResultSet();
            $custCombinedList = $rs->initialize($ri)->toArray();

            if (isset ( $custCombinedList ) && is_array ( $custCombinedList )) {
                $combinedListIds = array ();
                foreach ( $custCombinedList as $custCombined ) {
                    $combinedListId = $custCombined ['CombinedListId'];

                    $manCustList = $mdlcl->getManCustList ( $combinedListId );
                    $minManCustId = $mdlcl->getMinManCustId ( $combinedListId );    // 最小の管理顧客番号取得
                    $maxManCustId = $mdlcl->getMaxManCustId ( $combinedListId );    // 最大の管理顧客番号取得
                    $maxManCustInfo = $mdlmc->find( $maxManCustId )->current();     // 情報を残す管理顧客
                    $minManCustInfo = $mdlmc->find( $minManCustId )->current();     // 統合対象管理顧客

                    if (isset ( $manCustList ) && is_array ( $manCustList )) {

                        // メモ設定: '顧客ID' + 管理顧客.管理顧客番号 + ' ' + 管理顧客.メモ + \r\n
                        $note = "";
                        foreach ( $manCustList as $manCustInfo ) {
                            if (isset ( $manCustInfo ['Note'] )) {
                                $note = $note .'顧客ID' . $manCustInfo ['ManCustId'] . ' '. $manCustInfo ['Note'] . '\r\n';
                            }
                        }

                        // 統合先の顧客に審査システム顧客IDがない場合、統合元顧客から取得
                        $iluCustomerId = $minManCustInfo['IluCustomerId'];
                        if (strlen($iluCustomerId) == 0) {
                            $sql = <<<EOQ
SELECT MCA.IluCustomerId
FROM   T_ManagementCustomer MCA
WHERE  MCA.ManCustId = ( SELECT MIN(MCB.ManCustId)
                         FROM   T_ManagementCustomer MCB
                                JOIN T_CombinedList CL ON MCB.ManCustId = CL.ManCustId
                         WHERE  MCB.IluCustomerId IS NOT NULL
                         AND    CL.CombinedDictateFlg = 1
                         AND    CL.CombinedListId = $combinedListId
                       )
EOQ;
                            $ri = $this->_adapter->query ( $sql )->execute ( null );

                            // 取得できた場合、審査システム顧客IDを設定
                            if ($ri->count() > 0) {
                                $minIlu = $ri->current();
                                if (strlen($minIlu['IluCustomerId']) > 0) {
                                    $iluCustomerId = $minIlu['IluCustomerId'];
                                }
                            }
                        }

                        // 統合指示された顧客のうち、統合元に紐づいた加盟店顧客の管理顧客番号を更新する
                        $mdlec->saveManCustId($minManCustId, $combinedListId, $this->_userId);

                        foreach ( $manCustList as $manCustInfo ) {
                            $combinedClass = 0;

                            if ($manCustInfo ['ManCustId'] == $minManCustId) {
                                // 最小の管理顧客番号を更新
                                $mdlmc->saveUpdate ( array (
                                        'GoodFlg' => $custCombined ['Good'] > 0 ? 1 : 0,
                                        'BlackFlg' => $custCombined ['Black'] > 0 ? 1 : 0,
                                        'ClaimerFlg' => $custCombined ['Claimer'] > 0 ? 1 : 0,
                                        'RemindStopFlg' => $custCombined ['RemindStop'] > 0 ? 1 : 0,
                                        'IdentityDocumentFlg' => $custCombined ['IdentityDocument'] > 0 ? 1 : 0,
                                        'NameKj' => $maxManCustInfo ['NameKj'],
                                        'NameKn' => $maxManCustInfo ['NameKn'],
                                        'PostalCode' => $maxManCustInfo ['PostalCode'],
                                        'PrefectureCode' => $maxManCustInfo ['PrefectureCode'],
                                        'PrefectureName' => $maxManCustInfo ['PrefectureName'],
                                        'City' => $maxManCustInfo ['City'],
                                        'Town' => $maxManCustInfo ['Town'],
                                        'Building' => $maxManCustInfo ['Building'],
                                        'UnitingAddress' => $maxManCustInfo ['UnitingAddress'],
                                        'Phone' => $maxManCustInfo ['Phone'],
                                        'MailAddress' => $maxManCustInfo ['MailAddress'],
                                        'Note' => $note,
                                        'RegNameKj' => $maxManCustInfo ['RegNameKj'],
                                        'RegUnitingAddress' => $maxManCustInfo ['RegUnitingAddress'],
                                        'RegPhone' => $maxManCustInfo ['RegPhone'],
                                        'SearchNameKj' => $maxManCustInfo ['SearchNameKj'],
                                        'SearchNameKn' => $maxManCustInfo ['SearchNameKn'],
                                        'SearchPhone' => $maxManCustInfo ['SearchPhone'],
                                        'SearchUnitingAddress' => $maxManCustInfo ['SearchUnitingAddress'],
                                        'IluCustomerId' => $iluCustomerId,
				//20160211 sode 審査システムへの顧客編集連携フラグを立てる
					'IluCustomerListFlg' => 1,
                                        'UpdateId' => $this->_userId
                                ), $minManCustId );

                                $combinedClass = 2;
                            } else {
                                // 以外の管理顧客を論理削除
                                $mdlmc->saveUpdate ( array (
                                        'UpdateId' => $this->_userId,
                                        'ValidFlg' => 0
                                ), $manCustInfo ['ManCustId'] );

                                if ($manCustInfo ['ManCustId'] == $maxManCustId) {
                                    $combinedClass = 1;
                                } else {
                                    $combinedClass = 0;
                                }
                            }

                            // 顧客統合履歴SEQを取得
                            $chSeq = $mdlsg->nextValue ( 'CombinedHistorySeq' );

                            // 処理結果を名寄せ履歴に追加
                            $mdlch->saveNew ( array (
                                    'CombinedHistorySeq' => $chSeq,
                                    'ManCustId' => $manCustInfo ['ManCustId'],
                                    'CombinedClass' => $combinedClass,
                                    'CombinedListId' => $combinedListId,
                                    'GoodFlg' => $manCustInfo ['GoodFlg'],
                                    'BlackFlg' => $manCustInfo ['BlackFlg'],
                                    'ClaimerFlg' => $manCustInfo ['ClaimerFlg'],
                                    'RemindStopFlg' => $manCustInfo ['RemindStopFlg'],
                                    'IdentityDocumentFlg' => $manCustInfo ['IdentityDocumentFlg'],
                                    'NameKj' => $manCustInfo ['NameKj'],
                                    'NameKn' => $manCustInfo ['NameKn'],
                                    'PostalCode' => $manCustInfo ['PostalCode'],
                                    'PrefectureCode' => $manCustInfo ['PrefectureCode'],
                                    'PrefectureName' => $manCustInfo ['PrefectureName'],
                                    'City' => $manCustInfo ['City'],
                                    'Town' => $manCustInfo ['Town'],
                                    'Building' => $manCustInfo ['Building'],
                                    'Phone' => $manCustInfo ['Phone'],
                                    'MailAddress' => $manCustInfo ['MailAddress'],
                                    'Note' => $manCustInfo ['Note'],
                                    'IluCustomerListFlg' => 1,
                                    'RegistId' => $this->_userId,
                                    'UpdateId' => $this->_userId,
                                    'ValidFlg' => $manCustInfo ['ValidFlg']
                            ) );
                        }

                        // 名寄せリストを更新
                        $mdlcl->updateCombinedDictateFlg ( array (
                                'CombinedListId' => $combinedListId,
                                'CombinedDictateFlg' => 2,
                                'CombinedDate' =>  date ( 'Y-m-d H:i:s' ),
                                'UpdateId' => $this->_userId
                        ));
                    }
                    $combinedListIds [] = $combinedListId;
                }
                // 名寄せリスト表示期間（日数）取得
                $passedDays = $mdlsp->getValue ('[DEFAULT]', 'systeminfo', 'CombinedListDays');

                if (isset ($passedDays)) {
                    $passedDays = intval($passedDays);
                    $sql = <<<EOQ
UPDATE  T_CombinedList
SET     ValidFlg = 0,
        UpdateDate = :UpdateDate,
        UpdateId = :UpdateId
WHERE	DATE_ADD(RegistDate, INTERVAL %d DAY) < NOW()
EOQ;
                    $sql = sprintf ( $sql, $passedDays );
                    if (isset ( $combinedListIds ) && is_array ( $combinedListIds ) && !empty($combinedListIds) ) {
                        $sql .= sprintf ( ' OR CombinedListId IN (%s)', join ( ',', $combinedListIds ) );
                    }
                    // 名寄せリストから　名寄せ済み及び期限切れの名寄せ候補を削除する
                    $this->_adapter->query ( $sql )->execute ( array (
                        ':UpdateDate' => date ( 'Y-m-d H:i:s' ),
                        ':UpdateId' => $this->_userId
                    ) );
                }
            }

            // TODO:与信審査ｼｽﾃﾑの顧客名寄せ編集APIのURL、審査ｼｽﾃﾑｼｽﾃﾑIDを取得する
            // 統合した結果を与信審査ｼｽﾃﾑに反映する。

            // 顧客名寄せ編集URL取得処理
            $aggregationDataUrl = $mdlsp->getValue ('cbadmin', 'cj_api', 'SetAggregationData');

            // 名寄せ履歴から次のデータを取得する。
            $sql = <<<EOQ
SELECT      ch.CombinedListId,
            mc.IluCustomerId,
            ch.CombinedClass
From        T_CombinedHistory ch
INNER JOIN  T_ManagementCustomer mc ON mc.ManCustId = ch.ManCustId
WHERE       ch.IluCustomerListFlg = 1
ORDER BY    ch.CombinedListId,
            (CASE WHEN ch.CombinedClass = 2 THEN 0 ELSE 1 END),
            mc.IluCustomerId
EOQ;
            $combListIds = $this->_adapter->query ( $sql )->execute (null);

            // API連携対象が存在する場合のみ連携
            if ($combListIds->count() > 0 ) {

                // XMLデータ作成
                $doc = new \DOMDocument("1.0", "utf-8");
                // aggregarion_edit_list
     //           $rootElement = $doc->appendChild( $doc->createElement("aggregarion_edit_list") );
	//20160211-sode
 	$rootElement = $doc->appendChild( $doc->createElement("aggrgation_edit_list") );

		//20160211-sode-add
	    $rootElement->setAttribute("xmlns:xsd", "http://www.w3.org/2001/XMLSchema");

		//20160211-sode-add
	    $rootElement->setAttribute("xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance");

                $combListIdBak = "";
                $skipCombListId = "";
                $requestGroupCnt = 0;
                $delegate_customer_id = "";
                foreach ( $combListIds as $combListId )
                {
                    // 審査システム顧客IDが1つの名寄せリストグループはスキップ
                    if ($skipCombListId == $combListId['CombinedListId']) {
                        continue;
                    }

                    if ( $combListIdBak != $combListId['CombinedListId'] )
                    {
                        // 審査システム顧客IDが1つの名寄せリストグループはスキップ
                        $prm = $combListId['CombinedListId'];
                        $sql = <<<EOQ
SELECT COUNT(DISTINCT MC.IluCustomerId) AS cnt
FROM   T_ManagementCustomer MC
       JOIN T_CombinedHistory CH ON MC.ManCustId = CH.ManCustId
WHERE  CH.CombinedListId = $prm
AND    MC.IluCustomerId IS NOT NULL
EOQ;
                        $iluCustCnt = $this->_adapter->query ( $sql )->execute (null)->current()['cnt'];
                        if ($iluCustCnt <= 1) {
                            $skipCombListId = $prm;
                            continue;
                        }

                        $combListIdBak = $combListId['CombinedListId'];

                        // aggregarion_edit_list/aggregation_group
                        $groupElement = $doc->createElement("aggregation_group");
//                        $groupElement->appendChild( $doc->createTextNode(strval($combListId[ 'CombinedListId' ])));
                        $rootElement->appendChild($groupElement);
                        $requestGroupCnt++;
                    }

                    if ( $combListId[ 'CombinedClass' ] == 2 )
                    {
                        // aggregarion_edit_list/aggregation_group/delegate_customer_id
                        $delegateElement = $doc->createElement('delegate_customer_id');
                        $delegateElement->appendChild( $doc->createTextNode(strval($combListId[ 'IluCustomerId' ])));
                        $groupElement->appendChild($delegateElement);
                        $delegate_customer_id = $combListId[ 'IluCustomerId' ];

                    }
		//20160211-sode customer_id句のメンバにも代表注文ID含める
      /*              else if ( $combListId[ 'CombinedClass' ] == 0 || $combListId[ 'CombinedClass' ] == 1)
                    {
                        // 代表と異なる場合のみ送信
                        if ($delegate_customer_id != $combListId[ 'IluCustomerId' ]) { */
                            // aggregarion_edit_list/aggregation_group/customer_id
                            $customerElement = $doc->createElement( 'customer_id' );
                            $customerElement->appendChild( $doc->createTextNode(strval($combListId[ 'IluCustomerId' ])));
                            $groupElement->appendChild($customerElement);
                      /*  }
                    } */
                }

                $xmlData = $doc->saveXML();

//20160211-sode temp
	echo $xmlData;

                if ($requestGroupCnt > 0) {
                    // WebAPI連携実行
                    $response = $this->apiConnect($aggregationDataUrl, $xmlData);

                    // レスポンス解析
                    $resData = $this->getResponseInfo($response);
                }
                else {
                    $resData = array();
                    $resData['result'] = 'OK';
                }

                // レスポンス.結果=OK時は、送信データを審査ｼｽﾃﾑ連携済みに更新する。
                if ($resData['result'] == 'OK')
                {
                    $sql = <<<EOQ
UPDATE  T_CombinedHistory
SET     IluCustomerListFlg = 0
WHERE   IluCustomerListFlg = 1
EOQ;
                    $this->_adapter->query ( $sql )->execute ( null );
                }

            }

            $this->_adapter->getDriver ()->getConnection ()->commit ();

        } catch ( \Exception $e ) {
            $this->_adapter->getDriver ()->getConnection ()->rollBack ();
            throw $e;
        }
    }

    /**
     * API連携実行
     *
     * @param string $url API連携URL
     * @param string $params POSTするリクエストパラメータ
     * @throws LogicCreditJudgeSystemConnectException
     * @throws Exception
     */
    private function apiConnect($url, $params) {
        $client = new Client($url);

        //タイムアウト時刻取得
        $time_out = $this->_config['cj_api']['timeout_time'];

        //タイムアウト設定
        $client->setOptions(array('timeout' => $time_out, 'keepalive' => true, 'maxredirects' => 1));  // 試行回数(maxredirects) を 1 に設定

        try {
            // データ送信を実行する
            $response = $client
                ->setRawBody($params)
                ->setEncType('application/xml; charset=UTF-8', ';')
                ->setMethod('Post')
                ->send();

            // 結果を取得する
            $status = $response->getStatusCode();
            $res_msg = $response->getReasonPhrase();
            $res_msg = mb_convert_encoding($res_msg, mb_internal_encoding(), BaseIOUtility::detectEncoding($res_msg));

            if($status == 200) {
                // HTTPステータスが200だったら受信内容をそのまま返す
                return $response->getBody();
            }
            // ステータスが200以外の場合はコード1で例外をスロー
            throw new \Exception(sprintf('%s Request failed (%s : %s)', $url, $status, $res_msg), 1);
        }
        catch(\Exception $err) {
            if(!$err->getCode()) {
                // コード指定がない場合はタイムアウトと見なす
                throw new \Exception(sprintf('%s Request Timed out (%s)', $url, $err->getMessage()));
            }
            else {
                // それ以外はそのままキャッチした例外をスロー
                throw $err;
            }
        }
    }

    /**
     * APIのレスポンス解析
     *
     * @param string $response レスポンスデータ（XML文字列）
     * @return array レスポンス解析結果
     */
    private function getResponseInfo($response) {

	//20160211-sode temp
	echo $response;

        // SimpleXMLを経由して連想配列に展開
        $xml_param = simplexml_load_string($response);
        $resp_data = get_object_vars($xml_param);

        // 結果
        $result = '';
        if (!is_null($resp_data['result'])) {
            $result = $resp_data['result'];
        }

        // エラーメッセージ
        $error_message = '';
        if (!is_null($resp_data['error_message'])) {
            $error_message = $resp_data['error_message'];
        }

        // 解析結果をまとめる
        $respResult = array();
        $respResult['result'] = $result;
        $respResult['error_message'] = $error_message;

        return $respResult;
    }
}

