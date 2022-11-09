<?php

namespace models\Logic;

use Zend\Db\Adapter\Adapter;
use models\Table\TableUser;
use models\Table\TableOrder;
use models\Table\TableCombinedDictate;
use models\Sequence\SequenceGeneral;
use Coral\Coral\History\CoralHistoryOrder;
use Coral\Coral\Mail\CoralMail;
use models\Table\TableEnterprise;
use Zend\Db\Sql\Predicate\IsNull;
use models\Table\TablePayingAndSales;
use models\Table\TableOemClaimFee;
use models\Table\TableOrderItems;
use models\Table\TableOrderSummary;
use models\Table\TableOemSettlementFee;
use models\Logic\RwarvlData\LogicRwarvlDataImporter;
use models\Table\ATablePayingAndSales;

/**
 * 不払率算出/更新ロジック
 */
class LogicCombinedDictate {

    /**
     * アダプタ
     *
     * @var Adapter
     */
    protected $_adapter = null;

    /**
     * SMTPサーバー
     *
     * @var string
     */
    private $_smtp;

    /**
     * システムメール送信先
     *
     * @var string
     */
    private $_sysmailaddress;

    /**
     * ユーザID
     *
     * @var int
     */
    protected $_userId = null;

    /**
     * コンストラクタ
     *
     * @param Adapter $adapter
     *            アダプタ
     */
    public function __construct(Adapter $adapter, $smtp, $sysmailaddress) {
        $this->_adapter = $adapter;
        $this->_smtp = $smtp;
        $this->_sysmailaddress = $sysmailaddress;
    }

    /**
     * 請求取りまとめを行う
     * @throws Exception
     */
    public function combine() {
        try {
            // トランザクション開始
            $this->_adapter->getDriver ()->getConnection ()->beginTransaction ();

            $user = new TableUser ( $this->_adapter );
            $order = new TableOrder ( $this->_adapter );
            $combinedDictate = new TableCombinedDictate ( $this->_adapter );
            $seqGeneral = new SequenceGeneral ( $this->_adapter );
            $mdlpas = new TablePayingAndSales( $this->_adapter );
            $mdlapas = new ATablePayingAndSales( $this->_adapter );

            $this->_userId = $user->getUserId(99, 1);

            // 加盟店別請求取りまとめサマリー情報を取得
            $entQuery = <<<EOQ
SELECT	T_Order.EnterpriseId,
		T_Customer.RegNameKj,
        T_Customer.RegPhone
FROM 	T_Order
INNER JOIN T_Customer
ON		T_Order.OrderSeq = T_Customer.OrderSeq
INNER JOIN T_Enterprise
ON 		T_Order.EnterpriseId = T_Enterprise.EnterpriseId
WHERE   T_Order.CombinedClaimTargetStatus IN (1, 2)
AND 	T_Order.ValidFlg = 1
AND		T_Order.DataStatus = 41
AND 	T_Order.Cnl_Status = 0
AND		(T_Order.LetterClaimStopFlg is null
		OR T_Order.LetterClaimStopFlg = 0)
AND		T_Enterprise.CombinedClaimMode = 1
AND		(CASE WHEN day(current_date) = day(last_day(current_date))
			THEN T_Enterprise.AutoCombinedClaimDay >= day(last_day(current_date))
            ELSE T_Enterprise.AutoCombinedClaimDay = day(current_date)
		END)
GROUP BY
		T_Order.EnterpriseId,
        T_Customer.RegNameKj,
        T_Customer.RegPhone
EOQ;

            // 取りまとめる注文を取得
            $orderEntQuery = <<<EOQ
SELECT	T_Order.OrderSeq
FROM 	T_Order
INNER JOIN T_Customer
ON		T_Order.OrderSeq = T_Customer.OrderSeq
INNER JOIN T_Enterprise
ON 		T_Order.EnterpriseId = T_Enterprise.EnterpriseId
WHERE   T_Order.CombinedClaimTargetStatus IN (1, 2)
AND 	T_Order.ValidFlg = 1
AND		T_Order.DataStatus = 41
AND 	T_Order.Cnl_Status = 0
AND		(T_Order.LetterClaimStopFlg is null
		OR T_Order.LetterClaimStopFlg = 0)
AND		T_Order.EnterpriseId = :enterpriseId
AND		T_Customer.RegNameKj = :regNameKj
AND 	T_Customer.RegPhone = :regPhone
AND		T_Enterprise.CombinedClaimMode = 1
AND		(CASE WHEN day(current_date) = day(last_day(current_date))
			THEN T_Enterprise.AutoCombinedClaimDay >= day(last_day(current_date))
            ELSE T_Enterprise.AutoCombinedClaimDay = day(current_date)
		END)
EOQ;
            foreach ( $this->_adapter->query ( $entQuery )->execute () as $summary ) {
                $arrOrderSeq = array ();
                foreach ( $this->_adapter->query ( $orderEntQuery )->execute ( array (
                        ':enterpriseId' => $summary ['EnterpriseId'],
                        ':regNameKj' => $summary ['RegNameKj'],
                        ':regPhone' => $summary ['RegPhone']
                ) ) as $orderSeq ) {
                    $arrOrderSeq [] = $orderSeq ['OrderSeq'];
                }

                $orderSeqs = join ( ',', $arrOrderSeq );

                // 注文．取りまとめ対象注文ステータスをまとめ指示に更新
                $order->updateCombinedClaimTargetStatus ( $orderSeqs, $this->_userId );

                $cdGroupValue = $seqGeneral->nextValue ( 'CombinedDictateGroup' );

                // 取りまとめ指示データを作成
                foreach ( $arrOrderSeq as $seq ) {
                    $combinedDictate->saveNew ( array (
                            'CombinedDictateGroup' => $cdGroupValue,
                            'OrderSeq' => $seq,
                            'CombinedStatus' => 0,
                            'IndicationDate' => date ( 'Y-m-d H:i:s' ),
                            'ExecDate' => null,
                            'CancelDate' => null,
                            'EnterpriseId' => $summary ['EnterpriseId'],
                            'ErrorMsg' => null,
                            'RegistId' => $this->_userId,
                            'UpdateId' => $this->_userId,
                            'ValidFlg' => 1
                    ) );
                }
            }

            // サイト別請求取りまとめサマリー情報を取得
            $siteQuery = <<<EOQ
SELECT	T_Order.SiteId,
		T_Customer.RegNameKj,
        T_Customer.RegPhone
FROM 	T_Order
INNER JOIN T_Customer
ON		T_Order.OrderSeq = T_Customer.OrderSeq
INNER JOIN T_Enterprise
ON 		T_Order.EnterpriseId = T_Enterprise.EnterpriseId
INNER JOIN T_Site
ON		T_Order.SiteId = T_Site.SiteId
WHERE   T_Order.CombinedClaimTargetStatus IN (1, 2)
AND 	T_Order.ValidFlg = 1
AND		T_Order.DataStatus = 41
AND 	T_Order.Cnl_Status = 0
AND		(T_Order.LetterClaimStopFlg is null
		OR T_Order.LetterClaimStopFlg = 0)
AND 	T_Enterprise.CombinedClaimMode = 2
AND		(CASE WHEN day(current_date) = day(last_day(current_date))
	       THEN T_Enterprise.AutoCombinedClaimDay >= day(last_day(current_date))
           ELSE T_Enterprise.AutoCombinedClaimDay = day(current_date)
        END)
AND 	T_Site.ValidFlg = 1
AND		T_Site.CombinedClaimFlg = 1
GROUP BY
		T_Order.SiteId,
        T_Customer.RegNameKj,
        T_Customer.RegPhone
EOQ;

            // 取りまとめる注文を取得
            $orderSiteQuery = <<<EOQ
SELECT	T_Order.OrderSeq
FROM 	T_Order
INNER JOIN T_Customer
ON		T_Order.OrderSeq = T_Customer.OrderSeq
INNER JOIN T_Enterprise
ON 		T_Order.EnterpriseId = T_Enterprise.EnterpriseId
INNER JOIN T_Site
ON		T_Order.SiteId = T_Site.SiteId
WHERE   T_Order.CombinedClaimTargetStatus IN (1, 2)
AND 	T_Order.ValidFlg = 1
AND		T_Order.DataStatus = 41
AND 	T_Order.Cnl_Status = 0
AND		(T_Order.LetterClaimStopFlg is null
		OR T_Order.LetterClaimStopFlg = 0)
AND 	T_Order.SiteId = :siteId
AND 	T_Customer.RegNameKj = :regNameKj
AND 	T_Customer.RegPhone = :regPhone
AND 	T_Enterprise.CombinedClaimMode = 2
AND		(CASE WHEN day(current_date) = day(last_day(current_date))
	       THEN T_Enterprise.AutoCombinedClaimDay >= day(last_day(current_date))
           ELSE T_Enterprise.AutoCombinedClaimDay = day(current_date)
        END)
AND 	T_Site.ValidFlg = 1
AND		T_Site.CombinedClaimFlg = 1
EOQ;

            foreach ( $this->_adapter->query ( $siteQuery )->execute () as $summary ) {
                $arrOrderSeq = array ();
                foreach ( $this->_adapter->query ( $orderSiteQuery )->execute ( array (
                        ':siteId' => $summary ['SiteId'],
                        ':regNameKj' => $summary ['RegNameKj'],
                        ':regPhone' => $summary ['RegPhone']
                ) ) as $orderSeq ) {
                    $arrOrderSeq [] = $orderSeq ['OrderSeq'];
                }

                $orderSeqs = join ( ',', $arrOrderSeq );

                // 注文．取りまとめ対象注文ステータスをまとめ指示に更新
                $order->updateCombinedClaimTargetStatus ( $orderSeqs, $this->_userId );

                $cdGroupValue = $seqGeneral->nextValue ( 'CombinedDictateGroup' );

                // 取りまとめ指示データを作成
                foreach ( $arrOrderSeq as $seq ) {
                    $enterpriseId = $order->find($seq)->current()['EnterpriseId'];

                    $combinedDictate->saveNew ( array (
                            'CombinedDictateGroup' => $cdGroupValue,
                            'OrderSeq' => $seq,
                            'CombinedStatus' => 0,
                            'IndicationDate' => date ( 'Y-m-d H:i:s' ),
                            'ExecDate' => null,
                            'CancelDate' => null,
                            'EnterpriseId' => $enterpriseId,
                            'ErrorMsg' => null,
                            'RegistId' => $this->_userId,
                            'UpdateId' => $this->_userId,
                            'ValidFlg' => 1
                    ) );
                }
            }

            $cdOrderSql = <<<EOQ
SELECT 	T_Order.OrderSeq
FROM 	T_Order
INNER JOIN T_CombinedDictate
ON		T_Order.OrderSeq = T_CombinedDictate.OrderSeq
WHERE	T_Order.ValidFlg = 1
AND		T_CombinedDictate.CombinedDictateGroup = :cdGroup
AND 	T_CombinedDictate.CombinedStatus = 0
AND		T_CombinedDictate.ValidFlg = 1
AND 	T_Order.Cnl_Status = 0
AND 	T_Order.DataStatus = 41
AND 	T_Order.CombinedClaimTargetStatus IN (11, 12)
EOQ;
            // 請求取りまとめを実施
            foreach ( $combinedDictate->getCombinedDictateGroups () as $cdGroup ) {
                $arrCdOrderSeq = array ();
                foreach ( $this->_adapter->query ( $cdOrderSql )->execute ( array (
                        ':cdGroup' => $cdGroup ['CombinedDictateGroup']
                ) ) as $cdOrderSeq ) {
                    $arrCdOrderSeq [] = $cdOrderSeq ['OrderSeq'];
                }

                $cdOrderSeqs = join ( ',', $arrCdOrderSeq );

                $arrErrorMessages = array ();

                if (! $this->checkRegNameKj ( $cdOrderSeqs ))
                    $arrErrorMessages [] = '請求先氏名不一致のため取りまとめ不可';

                if (! $this->checkLetterClaimStop ( $cdOrderSeqs ))
                    $arrErrorMessages [] = '紙請求ストップのため取りまとめ不可';

                $arrCdOrderSeqCount = 0;
                if(!empty($arrCdOrderSeq)) {
                    $arrCdOrderSeqCount = count($arrCdOrderSeq);
                }
                if ($arrCdOrderSeqCount >= 1000)
                    $arrErrorMessages [] = '1000件以上の取りまとめは不可';

                if (!empty( $arrErrorMessages )) {
                    // 取りまとめ指示にエラーメッセージを保存
                    $combinedDictate->saveUpdateWhere ( array (
                            'ErrorMsg' => join ( ', ', $arrErrorMessages ),
                            'UpdateId' => $this->_userId
                    ), array (
                            'CombinedDictateGroup' => $cdGroup ['CombinedDictateGroup'],
                            'CombinedStatus' => 0
                    ) );

                    // 送信処理
                    // TODO: 送信処理　1．加盟店送信、2．CB送信
                    try
                    {
                        $maildate = array();
                        $OrderList = array();
                        $coralmail = new CoralMail($this->_adapter, $this->_smtp);
                        $mdle = new TableEnterprise($this->_adapter);

                        // 加盟店送信
                        $enterpriseId = $this->getEnterpriseId($cdGroup ['CombinedDictateGroup']);
                        $maildate = $mdle->findEnterprise($enterpriseId)->current();

                        $maildate['ErrorMsgs'] = join ( "\r\n", $arrErrorMessages );
                        foreach ($this->getOrderObj( $cdOrderSeqs ) as $orderId){
                            $orderInfo  = $orderId['OrderId'];
                            $orderInfo .= strlen($orderId['Ent_OrderId']) > 0 ? ' ' . $orderId['Ent_OrderId']: '';
                            $orderInfo .= $orderId['NameKj'];
                            $OrderList[] = $orderInfo;
                        }
                        $maildate['OrderList'] = join ("\r\n", $OrderList);
                        $coralmail->SendCombClaimMailToEnt($maildate, $this->_userId);

                        $maildate['MailAddress'] = $this->_sysmailaddress;
                        $maildate['CpNameKj'] = 'キャッチボール';
                        $coralmail->SendCombClaimMailToCb($maildate, $this->_userId);

                    } catch(\Exception $e)
                    {
                        echo $e->getMessage() . "\n";
                    }
                    continue;
                }

                if ($this->updateOrderCombinedClaimTargetStatusExcluded ( $cdOrderSeqs )) {
                    $this->updateCombinedStatus ( $cdOrderSeqs, $cdGroup ['CombinedDictateGroup'] );
                    continue;
                }

                $minOrder = $this->getMinOrderSeq ( $cdOrderSeqs );

                // 注文の取りまとめ処理を実行
                if ($arrCdOrderSeqCount == 1) {
                    $this->updateOrderCombinedClaimTargetStatusSingleSeq ( $cdOrderSeqs, $minOrder ['MinOrderId'] );
                } else if ($arrCdOrderSeqCount > 1) {
                    $orderIds = $this->getAllChildrenOrderId ( $cdOrderSeqs, $minOrder ['OrderSeq'] );
                    $this->updateOrderCombinedClaimTargetStatusPSeq ( $minOrder ['OrderSeq'], $orderIds );
                    $this->updateOrderCombinedClaimTargetStatusCSeq ( $cdOrderSeqs, $minOrder ['OrderSeq'], $minOrder ['MinOrderId'] );
                }

                 // 取りまとめ指示テーブルを更新
                $this->updateCombinedStatus ( $cdOrderSeqs, $cdGroup ['CombinedDictateGroup'] );

                // 取りまとめ注文の請求手数料は親注文にのみ付与する。
                // 親注文Seqで注文データを取得
                $odata = ResultInterfaceToArray($order->findOrder(array('P_OrderSeq' => $minOrder['OrderSeq'])));

                $mdlpas = new TablePayingAndSales($this->_adapter);
                $mdle = new TableEnterprise($this->_adapter);
                $mdlOi = new TableOrderItems($this->_adapter);
                $mdlOs = new TableOrderSummary($this->_adapter);
                $mdlOsf = new TableOemSettlementFee($this->_adapter);

                // 親注文Seqで取得できた件数分、ループして更新する。
                foreach ($odata as $key => $value) {
                    // 子注文の場合、以下処理を行う。
                    if ($minOrder['OrderSeq'] != $value['OrderSeq']) {
                        // 注文Seqに紐づく立替売上管理の請求額と立替金額を取得する
                        $pas = $mdlpas->findPayingAndSales(array('OrderSeq' => $value['OrderSeq']))->current();
                        $fee = $pas['ClaimFee'];
                        $charge = $pas['ChargeAmount'];
                        $settlementFeeRate = $pas['AppSettlementFeeRate'];
                        // 立替・売上管理テーブルの更新（請求手数料は 0、立替金額は 請求手数料分を加算する）
                        $mdlpas->saveUpdateWhere(array('ClaimFee' => 0, 'ChargeAmount' => $charge + $fee, 'UpdateId' => $this->_userId), array('OrderSeq' => $value['OrderSeq']));

                        // 親注文のみ店舗手数料を取る場合
                        $ent = $mdle->find($minOrder ['EnterpriseId'])->current();
                        if ($ent['CombinedClaimChargeFeeFlg'] == 1) {
                            // 子注文の店舗手数料を0にする
                            $mdlOi->saveUpdateWhere(array('UnitPrice' => 0, 'SumMoney' => 0, 'UpdateId' => $this->_userId)
                                , array('OrderSeq' => $value['OrderSeq'], 'DataClass' => 3));

                            // 子注文の利用額を再計算する
                            $sql = <<<EOQ
SELECT SUM(SumMoney) AS UseAmount
FROM   T_OrderItems
WHERE  ValidFlg = 1
AND    OrderSeq = :OrderSeq
EOQ;
                            $useAmount = $this->_adapter->query ( $sql )->execute ( array('OrderSeq' => $value['OrderSeq']) )->current()['UseAmount'];
                            $order->saveUpdateWhere(array('UseAmount' => $useAmount, 'UpdateId' => $this->_userId), array('OrderSeq' => $value['OrderSeq']));

                            // 子注文の注文サマリーの店舗手数料を0にする
                            $oSummary = $mdlOs->findByOrderSeq($value['OrderSeq'])->current();
                            $mdlOs->saveUpdate(array('ChargeFee' => 0, 'UpdateId' => $this->_userId), $oSummary['SummaryId']);

                            // 子注文の立替金額の再計算を行う（請求手数料は0）
                            $pasData = TablePayingAndSales::calcFeeAndAmount($useAmount, $settlementFeeRate, 0);
                            $pasData['UpdateId'] = $this->_userId;
                            $mdlpas->saveUpdate($pasData, $pas['Seq']);

                            // OEM加盟店の場合
                            if ($minOrder ['OemId'] > 0) {
                                // 子注文のOEM決済手数料を更新する
                                $osf = $mdlOsf->findOrder($value['OrderSeq'], true)->current();
                                if ($osf !== false) {
                                    $settlementFee = floor(strval( ( $useAmount * $osf['AppSettlementFeeRate'] ) / 100 ) );
                                    $mdlOsf->saveUpdate(array('UseAmount' => $useAmount, 'SettlementFee' => $settlementFee, 'UpdateId' => $this->_userId), $osf['Seq']);
                                }
                            }
                        }
                    }
                }

                //履歴登録処理（注文単位に履歴登録する）
                foreach ($arrCdOrderSeq as $oseq) {
                    // 注文履歴へ登録
                    $history = new CoralHistoryOrder($this->_adapter);
                    $history->InsOrderHistory($oseq, 40, $this->_userId);
                }

                // 着荷確認処理（注文単位に着荷確認を行う）
                foreach ($arrCdOrderSeq as $oseq) {
                    // 注文サマリを取得
                    $OsData = $mdlOs->findByOrderSeq($oseq)->current();
                    // 取りまとめ着荷確認を取得
                    $CAsql = " SELECT * FROM T_CombinedArrival WHERE OrderSeq = :OrderSeq AND Deli_JournalNumber = :Deli_JournalNumber ORDER BY 1 DESC LIMIT 1 ";
                    $CaData = $this->_adapter->query($CAsql)->execute(array(':OrderSeq' => $oseq, ':Deli_JournalNumber' => $OsData['Deli_JournalNumber']))->current();
                    if (empty($CaData)){
                        continue;
                    }
                    if ($order->isCanceled($oseq)) { ; }// キャンセルされているので何もしない。
                    else {
                        $arrFlg = 1;                            // 着荷確認フラグ
                        $noArrReason = 0;                       // 未確認理由
                        // 着荷確認の結果をDBに書き込み
                        $this->setArrConfirm(
                        $oseq,
                        $arrFlg,
                        $noArrReason,
                        $CaData['Deli_ConfirmArrivalDate']
                        );

                        // 既に[立替条件クリアフラグ]が[1：条件をクリアしている]か？の取得
                        $isAlreadyClearCondition = $mdlpas->IsAlreadyClearCondition($oseq);

                        // 立替売上管理.立替条件クリアフラグが"0：条件をクリアしていない"であれば更新実施（判定は下記メソッド内で行う）
                        $mdlpas->clearConditionForCharge2($oseq, $CaData['Deli_ConfirmArrivalDate'], $userId);

                        // [着荷入力日時]のセット
                        $row_pas = $this->_adapter->query(" SELECT Seq, ClearConditionDate FROM T_PayingAndSales WHERE OrderSeq = :OrderSeq "
                        )->execute(array(':OrderSeq' => $oseq))->current();
                        $mdlapas->saveUpdate(array('Deli_ConfirmArrivalInputDate' => date('Y-m-d H:i:s')), $row_pas['Seq']);

                        if (!$isAlreadyClearCondition) {
                            // 着荷により立替条件クリアフラグが１化されるとき => '1:着荷'として更新
                            $mdlapas->saveUpdate(array('ATUriType' => 1, 'ATUriDay' => date('Ymd', strtotime($row_pas['ClearConditionDate']))), $row_pas['Seq']);
                        }
                    }

                }
            }
            $this->_adapter->getDriver ()->getConnection ()->commit ();
        } catch ( \Exception $e ) {
            $this->_adapter->getDriver ()->getConnection ()->rollBack ();
            $result = $e->getMessage ();
            throw $e;
        }
    }

    /**
     * 正規化された氏名一致チェック
     *
     * @param string $orderSeqs
     * @return boolean
     */
    private function checkRegNameKj($orderSeqs) {
        $sql = <<<EOQ
SELECT	T_Customer.RegNameKj
FROM 	T_Order
INNER JOIN T_Customer
ON 		T_Order.OrderSeq = T_Customer.OrderSeq
WHERE	T_Order.OrderSeq IN ( %s )
GROUP BY
		T_Customer.RegNameKj
EOQ;
        return 1 == $this->_adapter->query ( sprintf ( $sql, $orderSeqs ) )->execute ()->count ();
    }

    /**
     * 紙請求ストップ存在チェック
     *
     * @param string $orderSeqs
     * @return boolean
     */
    private function checkLetterClaimStop($orderSeqs) {
        $order = new TableOrder ( $this->_adapter );

        return 0 == $order->getLetterClaimStopCnt ( $orderSeqs );
    }

    /**
     * 注文の取りまとめ対象注文ステータス更新処理
     *
     * @param string $orderSeqs
     */
    private function updateOrderCombinedClaimTargetStatusExcluded($orderSeqs) {
        $sqlSite = <<<EOQ
UPDATE	T_Order, T_Enterprise, T_Site
SET		T_Order.CombinedClaimTargetStatus = 0,
		T_Order.UpdateDate = :date,
        T_Order.UpdateId = :userId
WHERE	T_Order.EnterpriseId = T_Enterprise.EnterpriseId
AND 	T_Order.SiteId = T_Site.SiteId
AND		T_Enterprise.CombinedClaimMode = 2
AND		(T_Site.CombinedClaimFlg = 0
		OR	T_Site.CombinedClaimFlg is null)
AND     T_Order.OrderSeq IN (%s)
EOQ;

        $sqlEnt = <<<EOQ
UPDATE	T_Order, T_Enterprise
SET		T_Order.CombinedClaimTargetStatus = 0,
		T_Order.UpdateDate = :date,
        T_Order.UpdateId = :userId
WHERE	T_Order.EnterpriseId = T_Enterprise.EnterpriseId
AND		(T_Enterprise.CombinedClaimMode = 0
		OR	T_Enterprise.CombinedClaimMode is null)
AND     T_Order.OrderSeq IN (%s)
EOQ;

        $updateDate = date ( 'Y-m-d H:i:s' );
        $rtnSite = $this->_adapter->query ( sprintf ( $sqlSite, $orderSeqs ) )->execute ( array (
                ':date' => $updateDate,
                ':userId' => $this->_userId
        ) )->getAffectedRows();

        $rtnEnt = $this->_adapter->query ( sprintf ( $sqlEnt, $orderSeqs ) )->execute ( array (
                ':date' => $updateDate,
                ':userId' => $this->_userId
        ) )->getAffectedRows();

        return $rtnSite > 0 || $rtnEnt > 0 ? true : false ;
    }

    /**
     * 取りまとめ対象注文リスト中の注文のうちで最小の注文IDを取得
     *
     * @param string $orderSeqs
     */
    private function getMinOrderSeq($orderSeqs) {
        $sql = <<<EOQ
SELECT 	MIN(OrderSeq) AS OrderSeq , MIN(OrderId) as MinOrderId, MIN(EnterpriseId) AS EnterpriseId, IFNULL(MIN(OemId), 0) AS OemId
FROM 	T_Order
WHERE 	OrderSeq IN ( %s )
EOQ;
        return $this->_adapter->query ( sprintf ( $sql, $orderSeqs ) )->execute ()->current ();
    }

    /**
     * 取りまとめ対象注文リストの注文が1件を取りまとめ更新
     *
     * @param string $orderSeqs
     * @param string $orderId
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    private function updateOrderCombinedClaimTargetStatusSingleSeq($orderSeqs, $orderId) {
        $note = sprintf ( "%s %s 全取りまとめ済み　", $orderId, date ( 'Y/m/d' ) );
        $sql = <<<EOQ
UPDATE	T_Order
SET		CombinedClaimTargetStatus = 0,
		Incre_Note = CONCAT(:note, IFNULL(Incre_Note ,'')),
        Ent_Note = CONCAT(:note, IFNULL(Ent_Note, '')),
        UpdateDate = :updateDate,
        UpdateId = :updateId
WHERE	OrderSeq IN ( %s )
EOQ;
        return $this->_adapter->query ( sprintf ( $sql, $orderSeqs ) )->execute ( array (
                ':note' => $note,
                ':updateDate' => date ( 'Y-m-d H:i:s' ),
                ':updateId' => $this->_userId
        ) );
    }

    /**
     * 取りまとめ対象注文リストの親注文を取りまとめ更新
     *
     * @param int $orderSeq
     * @param string $orderId
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    private function updateOrderCombinedClaimTargetStatusPSeq($orderSeq, $orderIds) {
        $note = sprintf ( "%s　", $orderIds );
        $sql = <<<EOQ
UPDATE	T_Order
SET		CombinedClaimParentFlg = 1,
        CombinedClaimTargetStatus = CombinedClaimTargetStatus + 80,
		Incre_Note = CONCAT(:note, IFNULL(Incre_Note ,'')),
        Ent_Note = CONCAT(:note, IFNULL(Ent_Note ,'')),
        UpdateDate = :updateDate,
        UpdateId = :updateId
WHERE	OrderSeq = :orderSeq
EOQ;
        return $this->_adapter->query ( $sql )->execute ( array (
                ':note' => $note,
                ':updateDate' => date ( 'Y-m-d H:i:s' ),
                ':updateId' => $this->_userId,
                ':orderSeq' => $orderSeq
        ) );
    }

    /**
     * 取りまとめ対象注文リストの子注文を取りまとめ更新
     *
     * @param string $orderSeqs
     * @param int $minOrderSeq
     * @param string $orderId
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    private function updateOrderCombinedClaimTargetStatusCSeq($orderSeqs, $minOrderSeq, $orderId) {
        $note = sprintf ( "%s %s 取りまとめ済み　", $orderId, date ( 'Y/m/d' ) );
        $sql = <<<EOQ
UPDATE	T_Order
SET		CombinedClaimParentFlg = 0,
        CombinedClaimTargetStatus = CombinedClaimTargetStatus + 80,
		Incre_Note = CONCAT(:note, IFNULL(Incre_Note ,'')),
        Ent_Note = CONCAT(:note, IFNULL(Ent_Note, '')),
        P_OrderSeq = :P_OrderSeq,
        UpdateDate = :updateDate,
        UpdateId = :updateId
WHERE	OrderSeq IN ( %s )
AND     OrderSeq <> :OrderSeq
EOQ;
        return $this->_adapter->query ( sprintf ( $sql, $orderSeqs ) )->execute ( array (
                ':note' => $note,
                ':P_OrderSeq' => $minOrderSeq,
                ':OrderSeq' => $minOrderSeq,
                ':updateDate' => date ( 'Y-m-d H:i:s' ),
                ':updateId' => $this->_userId,
        ) );
    }

    /**
     * 子注文の注文IDの羅列を取得
     *
     * @param string $orderSeqs
     * @param int $minOrderSeq
     */
    private function getAllChildrenOrderId($orderSeqs, $minOrderSeq) {
        $sql = <<<EOQ
SELECT 	OrderId
FROM	T_Order
WHERE	OrderSeq IN ( %s )
AND		OrderSeq <> %d
ORDER BY
		OrderId
EOQ;
        $arrOrderId = array ();
        foreach ( $this->_adapter->query ( sprintf ( $sql, $orderSeqs, $minOrderSeq ) )->execute ()  as $orderId ) {
            $arrOrderId [] = $orderId ['OrderId'];
        }

        return join ( ',', $arrOrderId );
    }

    /**
     * 取りまとめ指示テーブルを処理完了に更新
     *
     * @param string $orderSeqs
     * @param int $group
     */
    private function updateCombinedStatus($orderSeqs, $group) {
        $sql = <<<EOQ
UPDATE	T_CombinedDictate
SET		CombinedStatus = 1,
		ExecDate = :date,
        UpdateDate = :date,
        UpdateId = :userId
WHERE	OrderSeq IN ( %s )
AND		CombinedDictateGroup = :group
EOQ;
        return $this->_adapter->query ( sprintf ( $sql, $orderSeqs ) )->execute ( array (
                ':date' => date ( 'Y-m-d H:i:s' ),
                ':userId' => $this->_userId,
                ':group' => $group
        ) );
    }

    /**
     * 送信加盟店対象取得
     *
     * @param string $orderSeqs
     * @return boolean
     */
    private function getEnterpriseId($group) {

        $sql = <<<EOQ
SELECT MIN(EnterpriseId) as EnterpriseId
FROM   T_CombinedDictate
WHERE  CombinedDictateGroup = :group
EOQ;

       return $this->_adapter->query ( $sql )->execute (array (':group' => $group))->current()['EnterpriseId'];
    }

    /**
     * 指定の注文データを取得
     *
     * @param string $orderSeqs
     * @return boolean
     */
    private function getOrderObj($orderSeqs) {

        $sql = <<<EOQ
SELECT	T_Order.OrderId
     ,  T_Order.Ent_OrderId
     ,  T_Customer.NameKj
FROM 	T_Order
        INNER JOIN T_Customer ON T_Order.OrderSeq = T_Customer.OrderSeq
WHERE	T_Order.OrderSeq IN ( %s )
EOQ;

        return $this->_adapter->query ( sprintf ( $sql, $orderSeqs ) )->execute ();
    }

    /**
     * 着荷確認の結果をDBに反映する。
     *
     * @param int $oseq 注文Seq
     * @param int $arrFlg 確認結果
     * @param int $noArrReason 未着荷確認理由
     * @param date $caDate 着荷確認日
     */
    private function setArrConfirm($oseq, $arrFlg, $noArrReason, $caDate)
    {
        $mdloi = new TableOrderItems($this->_adapter);
        $mdlo = new TableOrder($this->_adapter);

        // ユーザーIDの取得
        $obj = new \models\Table\TableUser($this->_adapter);
        $userId = $obj->getUserId(99, 1);

        // 指定の注文シーケンスに関連付けられた注文商品を検索
        $datas = ResultInterfaceToArray($mdloi->findByOrderSeq($oseq));

        $datasLen = 0;
        if(!empty($datas)) {
            $datasLen = count($datas);
        }
        for ($i = 0 ; $i < $datasLen; $i++)
        {
            unset($udata);
            $udata["Deli_ConfirmArrivalFlg"] = $arrFlg;                                             // 着荷確認
            $udata["Deli_ConfirmNoArrivalReason"] = $noArrReason;                                   // 未着荷確認理由
            $udata["Deli_ConfirmArrivalOpId"] = 1;


            $udata["Deli_ConfirmArrivalDate"] = $caDate;
            $udata['UpdateId'] = $userId;

            $mdloi->saveUpdate($udata, $datas[$i]['OrderItemId']);

        // 着荷確認一覧表示速度改善のために、T_Order側のDel_ConfrimArrivalを追加
            unset($orderData);
            $orderData["Deli_ConfirmArrivalFlg"] = $arrFlg;                                         // 着荷確認
            $orderData["Deli_ConfirmArrivalDate"] = $caDate;
            $orderData["UpdateId"] = $userId;

            $mdlo->saveUpdate($orderData, $datas[$i]['OrderSeq']);
        }
        // 確認結果が「着荷：1」の場合のみ、履歴へ登録する。
        if ($arrFlg == 1) {
        // 注文履歴へ登録
            $history = new CoralHistoryOrder($this->_adapter);
            $history->InsOrderHistory($oseq, 51, $userId);
        }
    }
}

