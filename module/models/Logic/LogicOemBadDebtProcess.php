<?php

namespace models\Logic;

use Zend\Db\Adapter\Adapter;
use models\Table\TableOrder;
use models\Table\TableOemBadDebt;
use models\Table\TableOemClaimed;
use models\Table\TableOemEnterpriseClaimed;
use models\Table\TableOemSettlementFee;
use models\Table\TableUser;
use models\Table\TableOemClaimFee;
use models\Table\TableOemAgencyFee;
use models\Table\TablePayingControl;
use models\Table\TableOemAdjustmentAmount;
use models\Table\TableOem;
use models\Table\TableEnterprise;
use Coral\Coral\History\CoralHistoryOrder;
use models\Table\TableSite;
use models\Table\ATableOemClaimed;
use models\Table\TableSystemProperty;
use models\Table\ATableEnterprise;
use models\Table\TableBusinessCalendar;
use DateTime;

/**
 * OEM債権明細ロジッククラス
 */
class LogicOemBadDebtProcess {

    /**
     * DBアダプタ
     *
     * @var Adapter
     */
    private $db;

    /**
     * バッチ処理用ユーザID
     *
     * @var int
     */
    private $_userId;

    /**
     * コンストラクタ
     *
     * @param Zend_Db_Adapter_Abstract $dbAdapter
     *            DBアダプタ
     *
     */
    function __construct(Adapter $dbAdapter) {
        $this->db = $dbAdapter;
    }

    /**
     * 債権明細集計
     *
     * @param int $oemId
     * @throws Exception
     */
    public function counting($oemId) {
        try {
            $this->db->beginTransaction ();

            // 債権明細集計
            $fixedMonth = date ( "Y-m-d", strtotime ( sprintf ( '%s -1 month', date ( "Y-m-01" ) ) ) );
            $this->countingBadDebt ( $oemId, $fixedMonth );

            $this->db->commit ();
        } catch ( Exception $e ) {
            $this->db->rollBack ();
            throw $e;
        }
    }

    /**
     * 債権明細の集計を行う
     *
     * @param int $oemId
     * @param date $fixedMonth
     */
    private function countingBadDebt($oemId, $fixedMonth) {
        // 締め月の一日と末日
        $spanFrom = date ( 'Y-m-01', strtotime ( $fixedMonth ) );
        $spanTo = date ( 'Y-m-t', strtotime ( $fixedMonth ) );

        // 120日前の日付
        $limitDateFrom = date ( 'Y-m-d', strtotime ( sprintf ( "%s - %d days", $spanFrom, Table_Order::CLAIM_DAY ) ) );
        $limitDateTo = date ( 'Y-m-d', strtotime ( sprintf ( "%s - %d days", $spanTo, Table_Order::CLAIM_DAY ) ) );

        $mdlOBD = new Table_OemBadDebt ( $this->db );

        $bdData ["OemId"] = $oemId;
        $bdData ["FixedMonth"] = $fixedMonth;
        $newSeq = $mdlOBD->saveNew ( $bdData );

        // 入金済みの注文にマーキング
        $this->udstReceiptMoneyForBadDebt ( $oemId, $limitDateFrom, $limitDateTo, $newSeq );

        // 不払いの注文にマーキング
        $this->udstBadDebt ( $oemId, $limitDateFrom, $limitDateTo, $newSeq );

        // 請求件数金額取得
        $claim_ca = $this->getClaimForBadDebt ( $newSeq );

        // 入金件数金額取得
        $receiptMoney_ca = $this->getReceiptMoneyForBadDebt ( $newSeq );

        // 不払い件数金額取得
        $badDebt_ca = $this->getBadDebt ( $newSeq );

        // データ
        $bdData ["ProcessDate"] = date ( "Y-m-d" );
        $bdData ["SpanFrom"] = $spanFrom;
        $bdData ["SpanTo"] = $spanTo;
        $bdData ["FcSpanFrom"] = $limitDateFrom;
        $bdData ["FcSpanTo"] = $limitDateTo;
        $bdData ["ClaimCount"] = $claim_ca [0] ["CNT"];
        $bdData ["ClaimAmount"] = $claim_ca [0] ["UseAmount"] == null ? 0 : $claim_ca [0] ["UseAmount"];
        $bdData ["ReceiptMoneyCount"] = $receiptMoney_ca [0] ["CNT"];
        $bdData ["ReceiptMoneyAmount"] = $receiptMoney_ca [0] ["UseAmount"] == null ? 0 : $receiptMoney_ca [0] ["UseAmount"];
        $bdData ["BadDebtCount"] = $badDebt_ca [0] ["CNT"];
        $bdData ["BadDebtAmount"] = $badDebt_ca [0] ["UseAmount"] == null ? 0 : $badDebt_ca [0] ["UseAmount"];

        // 債権明細更新
        $mdlOBD->saveUpdate ( $bdData, $newSeq );
    }

    /**
     * 債権明細 - 請求件数金額取得
     *
     * @param int $oemBadDebtSeq
     * @return array
     */
    private function getClaimForBadDebt($oemBadDebtSeq) {
        $query = <<<EOQ
			SELECT
				COUNT(*) AS CNT,
				SUM(UseAmount) AS UseAmount
			FROM
				T_Order
			WHERE
				OemBadDebtSeq = %d
EOQ;

        $query = sprintf ( $query, $oemBadDebtSeq );

        return $this->db->query ( $query )->fetchAll ();
    }

    /**
     * 債権明細 - 入金件数金額取得
     *
     * @param int $oemBadDebtSeq
     * @return array
     */
    private function getReceiptMoneyForBadDebt($oemBadDebtSeq) {
        $query = <<<EOQ
			SELECT
				COUNT(*) AS CNT,
				SUM(UseAmount) AS UseAmount
			FROM
				T_Order
			WHERE
				OemBadDebtSeq = %d AND
				OemBadDebtType = 1
EOQ;

        $query = sprintf ( $query, $oemBadDebtSeq );

        return $this->db->query ( $query )->fetchAll ();
    }

    /**
     * 債権明細 - 債権件数金額取得
     *
     * @param int $oemBadDebtSeq
     * @return array
     */
    private function getBadDebt($oemBadDebtSeq) {
        $query = <<<EOQ
			SELECT
				COUNT(*) AS CNT,
				SUM(UseAmount) AS UseAmount
			FROM
				T_Order
			WHERE
				OemBadDebtSeq = %d AND
				OemBadDebtType = 2
EOQ;

        $query = sprintf ( $query, $oemBadDebtSeq );

        return $this->db->query ( $query )->fetchAll ();
    }

    /**
     * 債権明細 - 入金件数金額取得に該当するステータスの更新
     *
     * @param int $oemId
     * @param date $limitDateFrom
     * @param date $limitDateTo
     * @param int $oemBadDebtSeq
     */
    private function udstReceiptMoneyForBadDebt($oemId, $limitDateFrom, $limitDateTo, $oemBadDebtSeq) {
        $query = <<<EOQ
			UPDATE
				T_Order
			SET
				OemBadDebtSeq = %d,
				OemBadDebtType = 1
			WHERE
				%s
EOQ;

        $wheres = array (
                $this->db->quoteInto ( 'Clm_F_LimitDate >= ?', $limitDateFrom ),
                $this->db->quoteInto ( 'Clm_F_LimitDate <= ?', $limitDateTo ),
                $this->db->quoteInto ( 'OemId = ?', $oemId ),
                'DataStatus = 91',
                'Rct_Status = 1',
                'Cnl_Status = 0',
                'OemBadDebtSeq IS NULL'
        );

        $query = sprintf ( $query, $oemBadDebtSeq, join ( ' AND ', $wheres ) );
        $stmt = $this->db->query ( $query );
        $stmt->execute ();
    }

    /**
     * 債権明細 - 債権件数金額取得に該当するステータスの更新
     *
     * @param int $oemId
     * @param date $limitDateFrom
     * @param date $limitDateTo
     * @param int $oemBadDebtSeq
     */
    private function udstBadDebt($oemId, $limitDateFrom, $limitDateTo, $oemBadDebtSeq) {
        $query = <<<EOQ
			UPDATE
				T_Order
			SET
				OemBadDebtSeq = %d,
				OemBadDebtType = 2
			WHERE
				%s
EOQ;

        $wheres = array (
                $this->db->quoteInto ( 'Clm_F_LimitDate >= ?', $limitDateFrom ),
                $this->db->quoteInto ( 'Clm_F_LimitDate <= ?', $limitDateTo ),
                $this->db->quoteInto ( 'OemId = ?', $oemId ),
                'DataStatus = 51',
                'Rct_Status = 0', // DataStaus = 51 と同意だが指定しておく
                'Cnl_Status = 0',
                'OemBadDebtSeq IS NULL'
        );

        $query = sprintf ( $query, $oemBadDebtSeq, join ( ' AND ', $wheres ) );
        $stmt = $this->db->query ( $query );
        $stmt->execute ();
    }

    /**
     * 全てOEM仮締め処理
     * @throws Exception
     */
    public function countingAll() {
        try {
            // トランザクション開始
            $this->db->getDriver ()->getConnection ()->beginTransaction ();

            // 2.0 日付を算出
            // 前月の一日と末日
            $spanFromDay = date ( 'Y-m-01', strtotime ( "-1 month" ) );
            $spanToDay = date ( 'Y-m-t', strtotime ( "-1 month" ) );
            // 当月の一日
            $nowFromDay = date('Y-m-01');

            // 120日前の日付
            $limitDateFrom = date ( 'Y-m-d', strtotime ( sprintf ( "%s - %d days", $spanFromDay, TableOrder::CLAIM_DAY ) ) );
            $limitDateTo = date ( 'Y-m-d', strtotime ( sprintf ( "%s - %d days", $spanToDay, TableOrder::CLAIM_DAY ) ) );

            // -----------------------------------------------
            // 1.初期処理
            // -----------------------------------------------
            // バッチ処理用ユーザIDを取得
            $user = new TableUser ( $this->db );
            $this->_userId = $user->getUserId ( 99, 1 );

            // 1.3 OEM債権明細より注文.OEM債権明細タイプとOEM債権明細を初期化
            $this->initializeOemBadDebt ($spanFromDay);

            // 1.4 OEM加盟店請求とOEM請求の削除、およびその関連テーブルの初期化
            $ar1 = $this->initializeOemClaimed ();

            // 1.5 OEM請求の初期化
            $oc = new TableOemClaimed ( $this->db );
            $oc->deleteByPayingControlStatus ();

            // -----------------------------------------------
            // 2.OEM債権明細作成処理
            // -----------------------------------------------

            $now = date ( "d" );
            $oemClaimed = new TableOemClaimed($this->db);
            $osf = new TableOemSettlementFee($this->db);
            $ocf = new TableOemClaimFee($this->db);
            $oaf = new TableOemAgencyFee($this->db);
            $oaa = new TableOemAdjustmentAmount($this->db);
            $mdloem = new TableOem($this->db);
            $mdle = new TableEnterprise($this->db);
            $mdlsys = new TableSystemProperty($this->db);

            // 2.1 入金情報を取得
            $query = <<<EOQ
SELECT  IFNULL(o.OemId, 0) AS OemId,
        count(*) as Cnt,
        sum(( SELECT ReceiptAmountTotal FROM T_ClaimControl cc WHERE cc.OrderSeq = o.OrderSeq)) as Sum,
        sum(o.UseAmount) as SumUseAmount
FROM    T_Order o
        INNER JOIN T_ClaimControl cc ON o.P_OrderSeq = cc.OrderSeq
WHERE   cc.F_LimitDate >= :dateFrom
AND     cc.F_LimitDate <= :dateTo
AND     o.DataStatus = 91
AND     o.Rct_Status = 1
AND     o.Cnl_Status = 0
AND     o.OemBadDebtSeq is null
AND     o.ValidFlg = 1
GROUP BY
        IFNULL(o.OemId, 0)
EOQ;

            $receiptAmount = $this->db->query ( $query )->execute ( array (':dateFrom' => $limitDateFrom, ':dateTo' => $limitDateTo) );

            $receiptAmountMap = array ();
            foreach ( $receiptAmount as $row ) {
                $oemId = $row ["OemId"];
                $receiptAmountMap [$oemId] = $row;
            }

            // 2.2 債権情報を取得
            $query = <<<EOQ
SELECT  IFNULL(o.OemId, 0) AS OemId,
        count(*) as Cnt,
        sum(( SELECT ClaimedBalance FROM T_ClaimControl cc WHERE cc.OrderSeq = o.OrderSeq)) as Sum,
        sum(o.UseAmount) as SumUseAmount
FROM    T_Order o
        INNER JOIN T_ClaimControl cc ON  o.P_OrderSeq = cc.OrderSeq
WHERE   cc.F_LimitDate >= :dateFrom
AND     cc.F_LimitDate <= :dateTo
AND     o.DataStatus IN( 51, 61 )
AND     o.Cnl_Status = 0
AND     o.OemBadDebtSeq is null
AND     o.ValidFlg = 1
GROUP BY
        IFNULL(o.OemId, 0)
EOQ;

            $claimedBalance = $this->db->query ( $query )->execute ( array (':dateFrom' => $limitDateFrom, ':dateTo' => $limitDateTo) );

            $claimedBalanceMap = array ();
            foreach ( $claimedBalance as $row ) {
                $oemId = $row ["OemId"];
                $claimedBalanceMap [$oemId] = $row;
            }

            $to = new TableOem ( $this->db );

            // OEM別で債権明細情報を作成
            // 2.3
            foreach ( $to->getAllValidOem () as $row ) {
                $oemId = $row ["OemId"];
                $processDate = $spanToDay;
                $spanFrom = $spanFromDay;
                $spanTo = $spanToDay;

                $receiptCnt = 0;
                $receiptAmt = 0;
                $receiptUseAmount = 0;
                if (isset ( $receiptAmountMap [$oemId] ) && is_array ( $receiptAmountMap [$oemId] )) {
                    $receiptCnt = $receiptAmountMap [$oemId] ["Cnt"];
                    $receiptAmt = $receiptAmountMap [$oemId] ["Sum"];
                    $receiptUseAmount = $receiptAmountMap [$oemId] ["SumUseAmount"];
                }
                $badDebtCnt = 0;
                $badDebtAmt = 0;
                $badDebtUseAmount = 0;
                if (isset ( $claimedBalanceMap [$oemId] ) && is_array ( $claimedBalanceMap [$oemId] )) {
                    $badDebtCnt = $claimedBalanceMap [$oemId] ["Cnt"];
                    $badDebtAmt = $claimedBalanceMap [$oemId] ["Sum"];
                    $badDebtUseAmount = $claimedBalanceMap [$oemId] ["SumUseAmount"];
                }

//              ※現行にあわせるため、締めパターンによらず前月の一日と末日を設定する
//                if ($fixPattern == 2) {
//                    if ($now <= $oemFixedDay1) {
//                        $processDate = date('Y-m-d', strtotime(sprintf ( "%s + %d days", $spanFromDay, $settlementDay2 - 1 )));
//                        $spanFrom = date('Y-m-d', strtotime(sprintf ( "%s + %d days", $spanFromDay, $oemFixedDay1)));
//                    } else {
//                        $processDate = date('Y-m-d', strtotime(sprintf ( "%s + %d days", $nowFromDay, $settlementDay1 - 1 )));
//                        $spanFrom = $nowFromDay;
//                        $spanTo = date('Y-m-d', strtotime(sprintf ( "%s + %d days", $nowFromDay, $oemFixedDay1 - 1 )));
//                    }
//                }

                // 2.4
                $obd = new TableOemBadDebt ( $this->db );
                $seq = $obd->saveNew ( array (
                        'OemId' => $oemId,
                        'FixedMonth' => $spanFromDay,
                        'ProcessDate' => $processDate,
                        'SpanFrom' => $spanFrom,
                        'SpanTo' => $spanTo,
                        'FcSpanFrom' => $limitDateFrom,
                        'FcSpanTo' => $limitDateTo,
                        'ClaimCount' => $receiptCnt + $badDebtCnt,
                        'ClaimAmount' => $receiptUseAmount + $badDebtUseAmount,
                        'ReceiptMoneyCount' => $receiptCnt,
                        'ReceiptMoneyAmount' => $receiptAmt,
                        'BadDebtCount' => $badDebtCnt,
                        'BadDebtAmount' => $badDebtAmt,
                        'RegistId' => $this->_userId,
                        'UpdateId' => $this->_userId,
                        'ValidFlg' => 1
                ) );

                // 2.4.1
                $sql = <<<EOQ
UPDATE  T_Order
SET     OemBadDebtSeq = :seq
    ,   OemBadDebtType = :obdType
    ,   UpdateDate = :date
    ,   UpdateId = :userId
WHERE   DataStatus = 91
AND     Rct_Status = :status
AND     Cnl_Status = 0
AND     OemBadDebtSeq is null
AND     ValidFlg = 1
AND     OemId = :OemId
AND     P_OrderSeq IN (SELECT OrderSeq FROM T_ClaimControl WHERE F_LimitDate >= :dateFrom AND F_LimitDate <= :dateTo)
EOQ;
                // 2.4.1.1 注文を更新（入金分）
                $this->db->query ( $sql )->execute ( array (
                        ':seq' => $seq,
                        ':obdType' => 1,                              // 入金
                        ':date' => date('Y-m-d H:i:s'),
                        ':userId' => $this->_userId,
                        ':status' => 1,
                        ':OemId' => $oemId,
                        ':dateFrom' => $limitDateFrom,
                        ':dateTo' => $limitDateTo
                ) );

                $sql = <<<EOQ
UPDATE  T_Order
SET     OemBadDebtSeq = :seq
    ,   OemBadDebtType = :obdType
    ,   UpdateDate = :date
    ,   UpdateId = :userId
WHERE   DataStatus IN( 51, 61 )
AND     Cnl_Status = 0
AND     OemBadDebtSeq is null
AND     ValidFlg = 1
AND     OemId = :OemId
AND     P_OrderSeq IN (SELECT OrderSeq FROM T_ClaimControl WHERE F_LimitDate >= :dateFrom AND F_LimitDate <= :dateTo)
EOQ;
                // 2.4.1.2 注文を更新（債権分）
                $this->db->query ( $sql )->execute ( array (
                        ':seq' => $seq,
                        ':obdType' => 2,                              // 債権
                        ':date' => date('Y-m-d H:i:s'),
                        ':userId' => $this->_userId,
                        ':OemId' => $oemId,
                        ':dateFrom' => $limitDateFrom,
                        ':dateTo' => $limitDateTo
                ) );
            }

            // -----------------------------------------------
            // 3 OEM別の集計、OEM・加盟店別の集計を行う
            // -----------------------------------------------
            // 3.1 OEM対象を抽出
            // OEMはすべて抽出→日付算出後、対象データがない場合はスキップ
            $riOem = $to->getAllValidOem();
            // 3.2 対象件数分、ﾙｰﾌﾟする。
            foreach ( $riOem as $wk ) {
                $oemId = $wk ['OemId'];

                // ---------------------------
                // 日付の算出
                // ---------------------------
                $sql = <<<EOQ
SELECT
  OemFixedPattern
, OemFixedDay1
, OemFixedDay2
, OemFixedDay3
, OemFixedDay_Week
, SettlementDay1
, SettlementDay2
, SettlementDay3
, SettlementDay_Week
FROM
  T_Oem
WHERE 1 = 1
AND OemId = :OemId
EOQ;
                $oem = $this->db->query ( $sql )->execute ( array (':OemId' => $oemId) )->current();

                // 各変数を初期化
                $fixPattern = null;
                $processDate = null;
                $spanFrom = null;
                $spanTo = null;
                $ordinal = null;

                if (isset ( $oem ) && is_array ( $oem )) {
                    // 3.2.1 OEM対象期間の決定
                    // 取得ﾃﾞｰﾀから締め日１～３、精算予定日１～３を変数へ代入
                    $oemFixedPattern   = $oem['OemFixedPattern'];
                    $oemFixedDay1      = $oem['OemFixedDay1'];
                    $oemFixedDay2      = $oem['OemFixedDay2'];
                    $oemFixedDay3      = $oem['OemFixedDay3'];
                    $oemFixedDayWeek   = $oem['OemFixedDay_Week'];
                    $settlementDay1    = $oem['SettlementDay1'];
                    $settlementDay2    = $oem['SettlementDay2'];
                    $settlementDay3    = $oem['SettlementDay3'];
                    $settlementDayWeek = $oem['SettlementDay_Week'];

                    $processDate = $spanToDay;
                    $spanFrom = $spanFromDay;
                    $spanTo = $spanToDay;
                    $ordinal = 1;

                    // 締めパターンの決定
                    if (empty($oemFixedDay2) || $oemFixedDay2 == 0) {
                        $fixPattern = 1;
                    } else if (empty($oemFixedDay3) || $oemFixedDay3 == 0) {
                        $fixPattern = 2;
                    } else {
                        $fixPattern = 3;
                    }

                    // OEM仮締め対象期間の決定
                    $this->getPeriodCurrentYear(
                          $oemFixedPattern
                        , $oemFixedDay1
                        , $oemFixedDay2
                        , $oemFixedDay3
                        , $oemFixedDayWeek
                        , $settlementDay1
                        , $settlementDay2
                        , $settlementDay3
                        , $settlementDayWeek
                        , $now
                        , $fixPattern
                        , $processDate
                        , $spanFrom
                        , $spanTo
                        , $ordinal
                    );

                    // 同期間のOEM請求
                    $sql = <<<EOQ
SELECT  COUNT(*) AS cnt
FROM    T_OemClaimed
WHERE   OemId = :OemId
AND     SpanFrom = :SpanFrom
AND     SpanTo = :SpanTo
AND     PayingControlStatus = 1
AND     ValidFlg = 1
EOQ;
                    $oemClaimedCnt = $this->db->query ( $sql )->execute ( array (
                            ':OemId' => $oemId,
                            ':SpanFrom' => $spanFrom,
                            ':SpanTo' => $spanTo,
                    ) )->current ();

                    if ($oemClaimedCnt != false && $oemClaimedCnt['cnt'] > 0) {
                        // 同期間のOEM請求が存在する場合、次の期間に変更

                        // 当月の月末
                        $nowToDay = date ('Y-m-t');

                        $processDate = $nowToDay;
                        $spanFrom = $nowFromDay;
                        $spanTo = $nowToDay;

                        // OEM仮締め対象期間の決定
                        $this->getPeriodNextFiscal(
                              $oemFixedPattern
                            , $oemFixedDay1
                            , $oemFixedDay2
                            , $oemFixedDay3
                            , $oemFixedDayWeek
                            , $settlementDay1
                            , $settlementDay2
                            , $settlementDay3
                            , $settlementDayWeek
                            , $now
                            , $fixPattern
                            , $processDate
                            , $spanFrom
                            , $spanTo
                            , $ordinal
                        );
                    }
                }

                // 3.2.2 加盟店集計情報を取得（WK1）
                // OEM精算仮締め対象外有無確認
                $sql = "SELECT Class1 FROM M_Code WHERE CodeId = 221 AND KeyCode = :OemId";
                $class1 = $this->db->query($sql)->execute(array(':OemId'=>$oemId))->current()["Class1"];
                if ($class1 == 0) {
                    $sql = $this->getOemEnterpriseClaimedSql2();     // 2015/10/16 Y.Suzuki 取得SQLをﾒｿｯﾄﾞ化 Mod
                } else {
                    $sql = $this->getOemEnterpriseClaimedSql();     // 2015/10/16 Y.Suzuki 取得SQLをﾒｿｯﾄﾞ化 Mod
                }
                $prm = array(
                    ':OemId' => $oemId,
                    ':dateTo' => $spanTo,
                    ':FixedDate' => $spanTo,
                );
                $ri = $this->db->query ( $sql )->execute ( $prm );
                // 集計する対象がない場合スキップ
                if ($ri->count() == 0) {
                    continue;
                }
                $oemEnterpriseClaimed = new TableOemEnterpriseClaimed ( $this->db );
                $isContinue = false; // 下記foreach後、スキップするか否か
                $multiSeqList = array();
                foreach ( $ri as $wk1 ) {

                    // データが存在しない場合はスキップ対象
                    if ( $wk1['Cnt'] <= 0 ) {
                        $isContinue = true;
                        break;
                    }

                    $seqList = $wk1['SeqList'];
                    $multiSeqList[] = $seqList;

                    // OEM決済手数料率を税込み率に変換
                    // 小数点以下第4位を切り捨て（第3位まで使用）
                    $wk1 ['OemSettlementFeeRate'] = $mdlsys->getIncludeTaxRate($spanTo, $wk1 ['OemSettlementFeeRate'], 3);

                    // 3.2.2.1 加盟店の注文情報取得しサマリー（WK2）
                    $sql = <<<EOQ
SELECT  pc.EnterpriseId                                                                                             /* 加盟店ID */
    ,   MAX(pc.AddUpFixedMonth) AS AddUpFixedMonth                                                                  /* 月次計上月度 */
    ,   SUM(pc.ChargeCount) AS ChargeCount                                                                          /* 加盟店立替注文件数 */
    ,   SUM(pc.ChargeAmount) AS ChargeAmount                                                                        /* 加盟店立替金額 */
    ,   SUM(pc.SettlementFee) AS SettlementFee                                                                      /* 加盟店決済手数料 */
    ,   SUM(pc.ClaimFee) AS ClaimFee                                                                                /* 加盟店請求手数料 */
    ,   SUM(pc.CancelCount) AS CancelCount                                                                          /* 加盟店キャンセル件数 */
    ,   SUM(pc.CalcelAmount) AS CalcelAmount                                                                        /* 加盟店キャンセル精算金額 */
    ,   SUM(pc.StampFeeCount) AS StampFeeCount                                                                      /* 加盟店印紙代発生件数 */
    ,   SUM(pc.StampFeeTotal) AS StampFeeTotal                                                                      /* 加盟店印紙代精算金額 */
    ,   SUM(pc.PayBackCount) AS PayBackCount                                                                        /* 加盟店立替精算戻し件数 */
    ,   SUM(pc.PayBackAmount) AS PayBackAmount                                                                      /* 加盟店立替精算戻し金額 */
    ,   SUM(CASE WHEN pc.ChargeMonthlyFeeFlg = 1 THEN pc.MonthlyFee ELSE 0 END) AS MonthlyFee                       /* 加盟店月額固定費 */
    ,   SUM(CASE WHEN pc.ChargeMonthlyFeeFlg = 1 THEN IFNULL(at.OemMonthlyFeeWithoutTax, 0)
                                                    + IFNULL(at.OemMonthlyFeeTax, 0)
                                                    + IFNULL(at.OemIncludeMonthlyFee, 0)
                                                    + IFNULL(at.OemIncludeMonthlyFeeTax, 0)
                                                    + IFNULL(at.OemApiMonthlyFee, 0)
                                                    + IFNULL(at.OemApiMonthlyFeeTax, 0)
                                                    + IFNULL(at.OemCreditNoticeMonthlyFee, 0)
                                                    + IFNULL(at.OemCreditNoticeMonthlyFeeTax, 0)
                                                    + IFNULL(at.OemNCreditNoticeMonthlyFee, 0)
                                                    + IFNULL(at.OemNCreditNoticeMonthlyFeeTax, 0)
                                                    + IFNULL(at.OemReserveMonthlyFee, 0)
                                                    + IFNULL(at.OemReserveMonthlyFeeTax, 0)
                                                 ELSE 0 END) AS MonthlyFeeCB      /* CB月額固定費 */
    ,   SUM(pc.TransferCommission) AS TransferCommission                                                            /* 加盟店振込手数料 */
    ,   SUM(CASE WHEN ((oem.PayingMethod = 0 AND pc.DecisionPayment > 0) OR (oem.PayingMethod = 1)) THEN pc.DecisionPayment ELSE 0 END) AS DecisionPayment /* 加盟店振込確定金額 */
    ,   SUM(pc.AdjustmentAmount) AS AdjustmentAmount                                                                /* 加盟店調整額 */
    ,   MAX(pc.ChargeMonthlyFeeFlg) AS ChargeMonthlyFeeFlg                                                          /* 月額固定費課金 */
FROM    T_PayingControl pc
        INNER JOIN T_Enterprise e ON e.EnterpriseId = pc.EnterpriseId
        INNER JOIN AT_PayingControl at ON pc.Seq = at.Seq
        INNER JOIN T_Oem oem ON pc.OemId = oem.OemId
WHERE   pc.Seq IN ($seqList)
AND     pc.EnterpriseId = :EnterpriseId
AND     pc.OemClaimedAddUpFlg = 0
GROUP BY
        pc.EnterpriseId
EOQ;

                    $wk2 = $this->db->query ( $sql )->execute ( array (':EnterpriseId' => $wk1 ['EnterpriseId']) )->current ();

                    // 繰越額は指定期間内の最初のデータを取得
                    $sql  = "";
                    $sql .= " SELECT IFNULL(CarryOver, 0) AS CarryOver ";  // NULLの過去データは存在しないが、NOTNULL制約はないので念のためIFNULLで保護
                    $sql .= " FROM T_PayingControl pc ";
                    $sql .= " WHERE pc.Seq IN ($seqList) ";
                    $sql .= " AND pc.EnterpriseId = :EnterpriseId ";
                    $sql .= " AND pc.OemClaimedAddUpFlg = 0 ";
                    $sql .= " ORDER BY Seq LIMIT 1 ";
                    $row = $this->db->query($sql)->execute(array (':EnterpriseId' => $wk1 ['EnterpriseId']))->current();

                    // 繰越額設定
                    $wk2['CarryOver'] = $row ? $row['CarryOver'] : 0;

                    $enterprise = $mdle->findEnterprise ( $wk2 ['EnterpriseId'] )->current ();

                    // CB月額固定費、OEM月額固定費の判定
                    $monthlyFeeOfCB = 0;        // CB月額固定費
                    $monthlyFeeOfOem = 0;       // OEM月額固定費
                    $lastDay = date('Y-m-d', strtotime("last day of " . $enterprise ['ServiceInDate']));   // サービス開始月の月末日
                    // 締め処理日　≦　加盟店.サービス開始日の月の末日の場合
                    if ($processDate <= $lastDay) {
                        // 月額固定費は0
                        $monthlyFeeOfCB = 0;
                        $monthlyFeeOfOem = 0;
                    }
                    // 締め処理日　＞　加盟店.サービス開始日の月の末日の場合
                    else {
                        // 2015/10/15 Y.Suzuki Mod 会計対応 Stt
                        // 取得した次回請求OEM月額固定費は[税抜]金額のため、税額を算出して足しこむ
                        // CB月額固定費＝加盟店.次回請求OEM月額固定費
                        $monthlyFeeOfCB = $wk2['MonthlyFeeCB'];
                        // 立替振込管理から取得している月額固定費は[税込]金額のため、対応なし
                        // OEM月額固定費＝立替振込管理.月額固定費－CB月額固定費
                        $monthlyFeeOfOem = $wk2 ['MonthlyFee'] - $monthlyFeeOfCB;
                        // 2015/10/15 Y.Suzuki Mod 会計対応 End
                    }
                    $wk2 ['MonthlyFeeOfCB'] = $monthlyFeeOfCB;
                    $wk2 ['MonthlyFeeOfOem'] = $monthlyFeeOfOem;
                    $wk2 ['CancelAmountMinus'] = $wk2 ['CalcelAmount'] * - 1;

                    // 3.2.2.2 加盟店の注文キャンセル情報取得しサマリー（WK3）
                    if($class1 == "1"){
                    $sql = <<<EOQ
SELECT  T_PayingControl.OemId,
        T_PayingControl.EnterpriseId,
        count(*) as CNT,
        sum(IFNULL(T_OemClaimFee.ClaimFee, 0) + IFNULL(T_OemSettlementFee.SettlementFee, 0)) as OemCancelFee,
        sum(IFNULL(T_PayingAndSales.ClaimFee, 0) + IFNULL(T_PayingAndSales.SettlementFee, 0)
            - IFNULL(T_OemClaimFee.ClaimFee, 0) - IFNULL(T_OemSettlementFee.SettlementFee, 0)) as CBCancelFee
FROM    T_Cancel
INNER JOIN  T_PayingControl
ON      T_Cancel.PayingControlSeq = T_PayingControl.Seq
INNER JOIN  T_PayingAndSales
ON      T_Cancel.OrderSeq = T_PayingAndSales.OrderSeq
INNER JOIN  T_OemSettlementFee
ON      T_PayingAndSales.OrderSeq = T_OemSettlementFee.OrderSeq
INNER JOIN  T_OemClaimFee
ON      T_PayingAndSales.OrderSeq = T_OemClaimFee.OrderSeq
WHERE   T_PayingControl.Seq IN ($seqList)
AND     T_PayingControl.EnterpriseId = :EnterpriseId
AND     T_PayingControl.OemClaimedAddUpFlg = 0
AND     T_Cancel.CancelPhase IN (2,3)
AND     T_Cancel.ValidFlg = 1
GROUP BY T_PayingControl.OemId
       , T_PayingControl.EnterpriseId
EOQ;
                    }else{
                        $sql = <<<EOQ
SELECT  T_PayingControl.OemId,
        T_PayingControl.EnterpriseId,
        count(*) as CNT,
        sum(IFNULL(T_OemClaimFee.ClaimFee, 0) + IFNULL(T_OemSettlementFee.SettlementFee, 0)) as OemCancelFee,
        sum(IFNULL(T_PayingAndSales.ClaimFee, 0) + IFNULL(T_PayingAndSales.SettlementFee, 0)
            - IFNULL(T_OemClaimFee.ClaimFee, 0) - IFNULL(T_OemSettlementFee.SettlementFee, 0)) as CBCancelFee
FROM    T_Cancel
INNER JOIN  T_PayingControl
ON      T_Cancel.PayingControlSeq = T_PayingControl.Seq
INNER JOIN  T_PayingAndSales
ON      T_Cancel.OrderSeq = T_PayingAndSales.OrderSeq
INNER JOIN  T_OemSettlementFee
ON      T_PayingAndSales.OrderSeq = T_OemSettlementFee.OrderSeq
INNER JOIN  T_OemClaimFee
ON      T_PayingAndSales.OrderSeq = T_OemClaimFee.OrderSeq
INNER JOIN  AT_Order
ON      T_Cancel.OrderSeq = AT_Order.OrderSeq
WHERE   T_PayingControl.Seq IN ($seqList)
AND     T_PayingControl.EnterpriseId = :EnterpriseId
AND     T_PayingControl.OemClaimedAddUpFlg = 0
AND     T_Cancel.CancelPhase IN (2,3)
AND     T_Cancel.ValidFlg = 1
AND     AT_Order.ExtraPayType IS NULL
GROUP BY T_PayingControl.OemId
       , T_PayingControl.EnterpriseId
EOQ;
                    }
                    $wk3 = $this->db->query ( $sql )->execute ( array (':EnterpriseId' => $wk1 ['EnterpriseId']) )->current ();

                    // OEM利益総額を算出
                    $oemTotalProfit = $wk1 ['OEMSettlementFeeSum']      // OEM決済手数料合計
                                    + $wk1 ['OEMClaimFeeSumBS']         // OEM請求手数料（別送）
                                    + $wk1 ['OEMClaimFeeSumDK']         // OEM請求手数料（同梱）
                                    + $wk2 ['MonthlyFeeOfOem']          // OEM月額固定費
                                    - $wk3['CBCancelFee']               // CBキャンセル返金額
                    ;
                    $fixedTransferAmount = $oemTotalProfit;
                    if ($wk1['PayingMethod'])
                    {
                        // OEM立替の場合、OEM利益＋振込確定金額＋振込手数料とする
                        $fixedTransferAmount = $oemTotalProfit + $wk2['DecisionPayment'] + $wk2['TransferCommission'];
                    }

                    $cbTotalProfit = $wk1 ['CBSettlementFeeSum']        // CB決済手数料合計
                                   + $wk1['CBClaimFeeSumBS']            // CB請求手数料（別送）
                                   + $wk1 ['CBClaimFeeSumDK']           // CB請求手数料（同梱）
                                   + $wk2 ['MonthlyFeeOfCB']            // CB月額固定費
                                   - $wk3['OemCancelFee']               // OEMキャンセル返金額
                    ;

                    // 3.2.2.3 OEM加盟店請求の出力
                    $oemEnterpriseClaimedData = array (
                            'OemId' => $wk1 ['OemId'],
                            'EnterpriseId' => $wk1 ['EnterpriseId'],
                            'FixedMonth' => $wk2 ['AddUpFixedMonth'],
                            'ProcessDate' => $processDate,
                            'SpanFrom' => $spanFrom,
                            'SpanTo' => $spanTo,
                            'OrderCount' => $wk1 ['OrderSeqCnt'],
                            'UseAmount' => $wk1 ['UseAmountSum'],
                            'CB_MonthlyFee' => $wk2 ['MonthlyFeeOfCB'],
                            'AppPlan' => $wk1 ['Plan'],
                            'SettlementFeeRate' => $wk1 ['SettlementFeeRate'],
                            'OemSettlementFeeRate' => $wk1 ['OemSettlementFeeRate'],
                            'CB_SettlementCount' => $wk1 ['CBSettlementCnt'],
                            'CB_SettlementFee' => $wk1 ['CBSettlementFeeSum'],
                            'CB_ClaimCountBS' => $wk1 ['CBCntBS'],
                            'CB_ClaimFeeBS' => $wk1 ['CBClaimFeeSumBS'],
                            'CB_ClaimCountDK' => $wk1 ['CBCntDK'],
                            'CB_ClaimFeeDK' => $wk1 ['CBClaimFeeSumDK'],
                            'CB_EntMonthlyFee' => $wk2 ['MonthlyFeeOfCB'],
                            'CB_AdjustmentAmount' => 0,
                            'CB_ClaimTotal' => $cbTotalProfit,
                            'OM_SettlementCount' => $wk1 ['OEMSettlementCnt'],
                            'OM_SettlementFee' => $wk1 ['OEMSettlementFeeSum'],
                            'OM_ClaimCountBS' => $wk1 ['OEMCntBS'],
                            'OM_ClaimFeeBS' => $wk1 ['OEMClaimFeeSumBS'],
                            'OM_ClaimCountDK' => $wk1 ['OEMCntDK'],
                            'OM_ClaimFeeDK' => $wk1 ['OEMClaimFeeSumDK'],
                            'OM_EntMonthlyFee' => $wk2 ['MonthlyFeeOfOem'],
                            'OM_AdjustmentAmount' => 0,
                            'OM_TotalProfit' => $oemTotalProfit,
                            'CR_TotalAmount' => $wk2 ['CancelAmountMinus'],
                            'CR_OemAmount' => $wk3 ['OemCancelFee'],
                            'CR_EntAmount' => $wk3 ['CBCancelFee'],
                            'PayingMethod' => $wk1 ['PayingMethod'],
                            'PC_CarryOver' => $wk2 ['CarryOver'],
                            'PC_ChargeCount' => $wk2 ['ChargeCount'],
                            'PC_ChargeAmount' => $wk2 ['ChargeAmount'],
                            'PC_SettlementFee' => $wk2 ['SettlementFee'],
                            'PC_ClaimFee' => $wk2 ['ClaimFee'],
                            'PC_CancelCount' => $wk2 ['CancelCount'],
                            'PC_CalcelAmount' => $wk2 ['CalcelAmount'],
                            'PC_StampFeeCount' => $wk2 ['StampFeeCount'],
                            'PC_StampFeeTotal' => $wk2 ['StampFeeTotal'],
                            'PC_MonthlyFee' => $wk2 ['MonthlyFee'],
                            'PC_TransferCommission' => $wk2 ['TransferCommission'],
                            'PC_DecisionPayment' => $wk2 ['DecisionPayment'],
                            'PC_AdjustmentAmount' => $wk2 ['AdjustmentAmount'],
                            'FixedTransferAmount' => $fixedTransferAmount,
                            'OemClaimedSeq' => null,
                            'PayBackCount' => $wk2 ['PayBackCount'],
                            'PayBackAmount' => $wk2 ['PayBackAmount'],
                            'AgencyFee' => $wk1['AgencyFeeSum'],
                            'RegistId' => $this->_userId,
                            'UpdateId' => $this->_userId,
                            'ValidFlg' => 1
                    );
                    $oemEnterpriseClaimed->saveNew ( $oemEnterpriseClaimedData );
                }

                // continue指示があれば次へ
                if ($isContinue) {
                    continue;
                }

                // 3.2.3 OEM加盟店請求をサマリー読み込み（WK4）
                $sql = $this->getOemClaimedSql();       // 2015/10/16 Y.Suzuki 取得SQLをﾒｿｯﾄﾞ化 Mod

                $wk4 = $this->db->query ( $sql )->execute ( array (
                        ':OemId' => $oemId
                ) )->current ();

                // OEM調整額管理の保存配列より、OEM調整額管理を追加する
                $wk4 ['OM_AdjustmentAmountSum'] = 0;
                $ar1Count = 0;
                if(!empty($ar1[$wk4['OemId']])) {
                    $ar1Count = count($ar1[$wk4['OemId']]);
                }
                for ($i = 0; $i < $ar1Count; $i++) {
                    if (isset($ar1[$wk4['OemId']][$i]) && is_array($ar1[$wk4['OemId']][$i])) {
                        $adjustAmount = $ar1[$wk4['OemId']][$i];

                        $wk4 ['OM_AdjustmentAmountSum'] += $adjustAmount['AdjustmentAmount'];
                    }else{
                        break;
                    }
                }

                // 指定月度内にすでに締めデータが存在するか判断
                $isFirstCloseOnMonth = $this->isOemClaimedByFixedMonth($oemId, $wk4 ['FixedMonth']);

                // 3.2.5 OEM請求を出力
                $oemClaimedData = array (
                        'OemId' => $wk4 ['OemId'],
                        'FixedMonth' => $wk4 ['FixedMonth'],
                        'Ordinal' => $ordinal,
                        'ProcessDate' => $wk4 ['ProcessDate'],
                        'SettlePlanDate' => $wk4['ProcessDate'],
                        'SpanFrom' => $wk4 ['SpanFrom'],
                        'SpanTo' => $wk4 ['SpanTo'],
                        'OrderCount' => $wk4 ['OrderCountSum'],
                        'UseAmount' => $wk4 ['UseAmountSum'],
                        'CB_MonthlyFee' => $wk4 ['CB_MonthlyFeeSum'],
                        'CB_SettlementCountRKF' => $wk4 ['CB_SettlementCountSum11'],
                        'CB_SettlementFeeRKF' => $wk4 ['CB_SettlementFeeSum11'],
                        'CB_SettlementCountSTD' => $wk4 ['CB_SettlementCountSum21'],
                        'CB_SettlementFeeSTD' => $wk4 ['CB_SettlementFeeSum21'],
                        'CB_SettlementCountEXP' => $wk4 ['CB_SettlementCountSum31'],
                        'CB_SettlementFeeEXP' => $wk4 ['CB_SettlementFeeSum31'],
                        'CB_SettlementCountSPC' => $wk4 ['CB_SettlementCountSum41'],
                        'CB_SettlementFeeSPC' => $wk4 ['CB_SettlementFeeSum41'],
                        'CB_SettlementCount' => $wk4 ['CB_SettlementCountSum'],
                        'CB_SettlementFee' => $wk4 ['CB_SettlementFeeSum'],
                        'CB_ClaimCountBS' => $wk4 ['CB_ClaimCountBSSum'],
                        'CB_ClaimFeeBS' => $wk4 ['CB_ClaimFeeBSSum'],
                        'CB_ClaimCountDK' => $wk4 ['CB_ClaimCountDKSum'],
                        'CB_ClaimFeeDK' => $wk4 ['CB_ClaimFeeDKSum'],
                        'CB_EntMonthlyCountRKF' => $wk4 ['CB_EntMonthlyCountSum11'],
                        'CB_EntMonthlyFeeRKF' => $wk4 ['CB_EntMonthlyFeeSum11'],
                        'CB_EntMonthlyCountSTD' => $wk4 ['CB_EntMonthlyCountSum21'],
                        'CB_EntMonthlyFeeSTD' => $wk4 ['CB_EntMonthlyFeeSum21'],
                        'CB_EntMonthlyCountEXP' => $wk4 ['CB_EntMonthlyCountSum31'],
                        'CB_EntMonthlyFeeEXP' => $wk4 ['CB_EntMonthlyFeeSum31'],
                        'CB_EntMonthlyCountSPC' => $wk4 ['CB_EntMonthlyCountSum41'],
                        'CB_EntMonthlyFeeSPC' => $wk4 ['CB_EntMonthlyFeeSum41'],
                        'CB_EntMonthlyCount' => $wk4 ['CB_EntMonthlyCountSum'],
                        'CB_EntMonthlyFee' => $wk4 ['CB_EntMonthlyFeeSum'],
                        'OpDkInitCount' => 0,
                        'OpDkInitFee' => 0,
                        'OpDkMonthlyCount' => 0,
                        'OpDkMonthlyFee' => 0,
                        'OpApiRegOrdMonthlyCount' => 0,
                        'OpApiRegOrdMonthlyFee' => 0,
                        'OpApiAllInitCount' => 0,
                        'OpApiAllInitFee' => 0,
                        'OpApiAllMonthlyCount' => 0,
                        'OpApiAllMonthlyFee' => 0,
                        'TfReclaimCount' => 0,
                        'TfReclaimFee' => 0,
                        'TfDamageInterestCount' => 0,
                        'TfDamageInterestAmount' => 0,
                        'TfMissedReceiptCount' => 0,
                        'TfMissedReceiptAmount' => 0,
                        'TfDoubleReceiptCount' => 0,
                        'TfDoubleReceiptAmount' => 0,
                        'TfCancelCount' => 0,
                        'TfCancelAmount' => 0,
                        'TfDevideReceiptCount' => 0,
                        'TfDevideReceiptAmount' => 0,
                        'CB_AdjustmentAmount' => $wk4 ['CB_AdjustmentAmountSum'],
                        'CB_ClaimTotal' => $wk4 ['CB_ClaimTotalSum'],
                        'OM_ShopTotal' => $wk4 ['OM_ShopTotal'],
                        'OM_SettleShopTotal' => $wk4 ['OM_SettleShopTotal'],
                        'OM_SettlementCountRKF' => $wk4 ['OM_SettlementCountSum11'],
                        'OM_SettlementFeeRKF' => $wk4 ['OM_SettlementFeeSum11'],
                        'OM_SettlementCountSTD' => $wk4 ['OM_SettlementCountSum21'],
                        'OM_SettlementFeeSTD' => $wk4 ['OM_SettlementFeeSum21'],
                        'OM_SettlementCountEXP' => $wk4 ['OM_SettlementCountSum31'],
                        'OM_SettlementFeeEXP' => $wk4 ['OM_SettlementFeeSum31'],
                        'OM_SettlementCountSPC' => $wk4 ['OM_SettlementCountSum41'],
                        'OM_SettlementFeeSPC' => $wk4 ['OM_SettlementFeeSum41'],
                        'OM_SettlementCount' => $wk4 ['OM_SettlementCountSum'],
                        'OM_SettlementFee' => $wk4 ['OM_SettlementFeeSum'],
                        'OM_ClaimCountBS' => $wk4 ['OM_ClaimCountBSSum'],
                        'OM_ClaimFeeBS' => $wk4 ['OM_ClaimFeeBSSum'],
                        'OM_ClaimCountDK' => $wk4 ['OM_ClaimCountDKSum'],
                        'OM_ClaimFeeDK' => $wk4 ['OM_ClaimFeeDKSum'],
                        'OM_EntMonthlyCountRKF' => $wk4 ['OM_EntMonthlyCountSum11'],
                        'OM_EntMonthlyFeeRKF' => $wk4 ['OM_EntMonthlyFeeSum11'],
                        'OM_EntMonthlyCountSTD' => $wk4 ['OM_EntMonthlyCountSum21'],
                        'OM_EntMonthlyFeeSTD' => $wk4 ['OM_EntMonthlyFeeSum21'],
                        'OM_EntMonthlyCountEXP' => $wk4 ['OM_EntMonthlyCountSum31'],
                        'OM_EntMonthlyFeeEXP' => $wk4 ['OM_EntMonthlyFeeSum31'],
                        'OM_EntMonthlyCountSPC' => $wk4 ['OM_EntMonthlyCountSum41'],
                        'OM_EntMonthlyFeeSPC' => $wk4 ['OM_EntMonthlyFeeSum41'],
                        'OM_EntMonthlyCount' => $wk4 ['OM_EntMonthlyCountSum'],
                        'OM_EntMonthlyFee' => $wk4 ['OM_EntMonthlyFeeSum'],
                        'OM_AdjustmentAmount' => $wk4 ['OM_AdjustmentAmountSum'],
                        'OM_TotalProfit' => $wk4 ['OM_TotalProfitSum'],
                        'CR_TotalAmount' => $wk4 ['CR_TotalAmountSum'],
                        'CR_OemAmount' => $wk4 ['CR_OemAmountSum'],
                        'CR_EntAmount' => $wk4 ['CR_EntAmountSum'],
                        'PayingMethod' => $wk4 ['PayingMethod'],
                        'PC_CarryOver' => $wk4 ['PC_CarryOverSum'],
                        'PC_ChargeCount' => $wk4 ['PC_ChargeCountSum'],
                        'PC_ChargeAmount' => $wk4 ['PC_ChargeAmountSum'],
                        'PC_SettlementFee' => $wk4 ['PC_SettlementFeeSum'],
                        'PC_ClaimFee' => $wk4 ['PC_ClaimFeeSum'],
                        'PC_CancelCount' => $wk4 ['PC_CancelCountSum'],
                        'PC_CalcelAmount' => $wk4 ['PC_CalcelAmountSum'],
                        'PC_StampFeeCount' => $wk4 ['PC_StampFeeCountSum'],
                        'PC_StampFeeTotal' => $wk4 ['PC_StampFeeTotalSum'],
                        'PC_MonthlyFee' => $wk4 ['PC_MonthlyFeeSum'],
                        'PC_TransferCommission' => $wk4 ['PC_TransferCommissionSum'],
                        'PC_DecisionPayment' => $wk4 ['PC_DecisionPaymentSum'],
                        'PC_AdjustmentAmount' => $wk4 ['PC_AdjustmentAmountSum'],
                        'FixedTransferAmount' => $wk4 ['FixedTransferAmountSum'] + $wk4 ['OM_AdjustmentAmountSum'],
                        'PayBackCount' => $wk4 ['PayBackCountSum'],
                        'PayBackAmount' => $wk4 ['PayBackAmountSum'],
                        'PayingControlStatus' => 0,
                        'AgencyFee' => $wk4 ['AgencyFeeSum'],
                        'CB_SettlementCountPlan'    => $wk4['CB_SettlementCountPlan'],
                        'CB_SettlementFeePlan'      => $wk4['CB_SettlementFeePlan'],
                        'CB_EntMonthlyCountPlan'    => $wk4['CB_EntMonthlyCountPlan'],
                        'CB_EntMonthlyFeePlan'      => $wk4['CB_EntMonthlyFeePlan'],
                        'OM_SettlementCountPlan'    => $wk4['OM_SettlementCountPlan'],
                        'OM_SettlementFeePlan'      => $wk4['OM_SettlementFeePlan'],
                        'OM_EntMonthlyCountPlan'    => $wk4['OM_EntMonthlyCountPlan'],
                        'OM_EntMonthlyFeePlan'      => $wk4['OM_EntMonthlyFeePlan'],
                        'N_MonthlyFeeWithoutTax'    => $wk['N_MonthlyFee'],
                        'N_MonthlyFeeTax'           => $mdlsys->getIncludeTaxAmount(date('Y-m-d'), $wk['N_MonthlyFee']) - $wk['N_MonthlyFee'],
                        'RegistId' => $this->_userId,
                        'UpdateId' => $this->_userId,
                        'ValidFlg' => 1
                );

                // OEM請求データを登録
                $oemClaimedSeq = $oemClaimed->saveNew($oemClaimedData);

                // 2015/10/06 Y.Suzuki Add 会計対応 Stt
                // 会計用項目のINSERT
                $mdlatoc = new ATableOemClaimed($this->db);
                $mdlatoc->saveNew(array('OemClaimedSeq' => $oemClaimedSeq, 'DailySummaryFlg' => 0));
                // 2015/10/06 Y.Suzuki Add 会計対応 End

                $addUpFixedMonth = date('Y-m-01', strtotime($spanFrom));

                // 3.2.6

                foreach ($multiSeqList as $rowSeqList) {

                    // 処理対象となる注文SEQ一覧の取得
                     $sql = " SELECT IFNULL(GROUP_CONCAT(OrderSeq),'0') AS OrderSeqs FROM T_PayingAndSales pas WHERE  pas.PayingControlSeq IN (" . $rowSeqList . ") ";
                     $orderSeqs = $this->db->query($sql)->execute(null)->current()['OrderSeqs'];

                    // ---------------------------------------
                    // 3.2.6.1 OEM決済手数料データを更新
                    // ---------------------------------------
                    $sql = <<<EOQ
UPDATE T_OemSettlementFee osf
SET    osf.AddUpFlg = 1
      ,osf.AddUpFixedMonth = :AddUpFixedMonth
      ,osf.OemClaimedSeq = :OemClaimedSeq
      ,osf.UpdateId = :UpdateId
      ,osf.UpdateDate = :UpdateDate
WHERE  1 = 1
AND    osf.AddUpFlg = 0
AND    osf.OemId = :OemId
AND    osf.OrderSeq IN ($orderSeqs)
EOQ;
                    $prm = array(
                        ':AddUpFixedMonth' => $addUpFixedMonth,
                        ':OemClaimedSeq' => $oemClaimedSeq,
                        ':UpdateId' => $this->_userId,
                        ':OemId' => $wk4['OemId'],
                        ':UpdateDate' => date('Y-m-d'),
                    );
                    $this->db->query($sql)->execute($prm);
                    // ---------------------------------------
                    // 3.2.6.2 OEM請求手数料データを更新
                    // ---------------------------------------
                    $sql = <<<EOQ
UPDATE T_OemClaimFee ocf
SET    ocf.AddUpFlg = 1
      ,ocf.AddUpFixedMonth = :AddUpFixedMonth
      ,ocf.OemClaimedSeq = :OemClaimedSeq
      ,ocf.UpdateId = :UpdateId
      ,ocf.UpdateDate = :UpdateDate
WHERE  1 = 1
AND    ocf.AddUpFlg = 0
AND    ocf.OemId = :OemId
AND    ocf.OrderSeq IN ($orderSeqs)
EOQ;
                    $prm = array(
                            ':AddUpFixedMonth' => $addUpFixedMonth,
                            ':OemClaimedSeq' => $oemClaimedSeq,
                            ':UpdateId' => $this->_userId,
                            ':OemId' => $wk4['OemId'],
                            ':UpdateDate' => date('Y-m-d'),
                    );
                    $this->db->query($sql)->execute($prm);

                    // ---------------------------------------
                    // 3.2.6.3 OEM代理店手数料データを更新
                    // ---------------------------------------
                    $sql = <<<EOQ
UPDATE T_OemAgencyFee oaf
SET    oaf.AddUpFlg = 1
      ,oaf.AddUpFixedMonth = :AddUpFixedMonth
      ,oaf.OemClaimedSeq = :OemClaimedSeq
      ,oaf.UpdateId = :UpdateId
      ,oaf.UpdateDate = :UpdateDate
WHERE  1 = 1
AND    oaf.AddUpFlg = 0
AND    oaf.OemId = :OemId
AND    oaf.OrderSeq IN ($orderSeqs)
EOQ;
                    $prm = array(
                            ':AddUpFixedMonth' => $addUpFixedMonth,
                            ':OemClaimedSeq' => $oemClaimedSeq,
                            ':UpdateId' => $this->_userId,
                            ':OemId' => $wk4['OemId'],
                            ':UpdateDate' => date('Y-m-d'),
                    );
                    $this->db->query($sql)->execute($prm);

                    // ---------------------------------------
                    // 3.2.6.4 立替振込管理データを更新
                    // ---------------------------------------
                    $sql = <<<EOQ
UPDATE  T_PayingControl
SET     OemClaimedAddUpFlg = :OemClaimedAddUpFlg,
        OemClaimedSeq = :OemClaimedSeq,
        UpdateDate = :UpdateDate,
        UpdateId = :UpdateId
WHERE   OemId = :OemId
AND     OemClaimedAddUpFlg = 0
AND     PayingControlStatus = 1
AND     Seq IN ($rowSeqList)
EOQ;
                    $this->db->query($sql)->execute(array(
                        ':OemClaimedAddUpFlg' => 1,
                        ':OemClaimedSeq' => $oemClaimedSeq,
                        ':UpdateDate' => date('Y-m-d H:i:s'),
                        ':UpdateId' => $this->_userId,
                        ':OemId' => $wk4['OemId']
                    ));

                }

                // 3.2.6.5 OEM調整額管理の保存配列より、OEM調整額管理を追加する
                for ($i = 0; $i < $ar1Count; $i++) {

                    if (isset($ar1[$wk4['OemId']][$i]) && is_array($ar1[$wk4['OemId']][$i])) {

                        $adjustAmount = $ar1[$wk4['OemId']][$i];

                        $data = array(
                                'OemClaimedSeq' => $oemClaimedSeq,
                                'SerialNumber' => $adjustAmount['SerialNumber'],
                                'OrderId' => $adjustAmount['OrderId'],
                                'OrderSeq' => $adjustAmount['OrderSeq'],
                                'ItemCode' => $adjustAmount['ItemCode'],
                                'AdjustmentAmount' => $adjustAmount['AdjustmentAmount'],
                                'RegistId' => $this->_userId,
                                'UpdateId' => $this->_userId,
                                'ValidFlg' => 1
                        );
                        $oaa->saveNew($data);
                    }else{
                        break;
                    }
                }

                if ( $isFirstCloseOnMonth ) {
                    // 月内初回の場合のみ処理する
                    // 3.2.6.6 OEMデータを更新
                    $oemData = $mdloem->find($wk4['OemId'])->current();
                    $monthlyFee = $oemData['MonthlyFee'];
                    $mdloem->saveUpdate(array(
                            'N_MonthlyFee' => $monthlyFee,
                            'UpdateId' => $this->_userId
                    ), $wk4['OemId']);

                    // 3.2.6.7 加盟店データを更新
                    $mdlat = new ATableEnterprise($this->db);
                    foreach($mdle->findOemEnterprise($wk4['OemId'], null) as $enterpriseData)
                    {

                        // 追加されたOEM月額固定費を取得
                        $enterpriseData2 = $mdlat->find($enterpriseData['EnterpriseId'])->current();

                        $enterpriseData = array_merge($enterpriseData, $enterpriseData2);
                        $oemMonthlyFee = $enterpriseData['OemMonthlyFee'];
                        $oemIncludeMonthlyFee = $enterpriseData['OemIncludeMonthlyFee'];
                        $oemApiMonthlyFee = $enterpriseData['OemApiMonthlyFee'];
                        $oemCreditNoticeMonthlyFee = $enterpriseData['OemCreditNoticeMonthlyFee'];
                        $oemNCreditNoticeMonthlyFee = $enterpriseData['OemNCreditNoticeMonthlyFee'];
                        $oemReserveMonthlyFee = $enterpriseData['OemReserveMonthlyFee'];

                        $mdle->saveUpdate(array(
                                'N_OemMonthlyFee' => $oemMonthlyFee,
                                'UpdateId' => $this->_userId
                        ), $enterpriseData['EnterpriseId']);

                        $mdlat->saveUpdate(array(
                            'N_OemIncludeMonthlyFee' => $oemIncludeMonthlyFee,
                            'N_OemApiMonthlyFee' => $oemApiMonthlyFee,
                            'N_OemCreditNoticeMonthlyFee' => $oemCreditNoticeMonthlyFee,
                            'N_OemNCreditNoticeMonthlyFee' => $oemNCreditNoticeMonthlyFee,
                            'N_OemReserveMonthlyFee' => $oemReserveMonthlyFee,
                        ), $enterpriseData['EnterpriseId']);
                    }
                }

                // 3.2.6.8 OEM加盟店請求更新
                $sql = <<<EOQ
UPDATE  T_OemEnterpriseClaimed
SET     OemClaimedSeq = :OemClaimedSeq,
        UpdateDate = :UpdateDate,
        UpdateId = :UpdateId
WHERE   OemId = :OemId
AND     OemClaimedSeq IS NULL
EOQ;
                $this->db->query($sql)->execute(array(
                        ':OemClaimedSeq' => $oemClaimedSeq,
                        ':UpdateDate' => date('Y-m-d H:i:s'),
                        ':UpdateId' => $this->_userId,
                        ':OemId' => $oemId,
                ));

            }

            $this->db->getDriver()->getConnection()->commit();

        } catch ( \Exception $e ) {
            $this->db->getDriver ()->getConnection ()->rollBack ();
            throw $e;
        }
    }

// 2015/10/15 Y.Suzuki 対象期間の決定ﾛｼﾞｯｸの修正に併せてﾒｿｯﾄﾞ化 Add Stt
    /**
     * OEM締めの対象期間を決定（本締め前の対象期間は当期）
     *
     * @param string $oemFixedPattern 締パターン
     * @param string $oemFixedDay1 締め日１
     * @param string $oemFixedDay2 締め日２
     * @param string $oemFixedDay3 締め日３
     * @param string $oemFixedDayWeek 締め日（週締め）
     * @param string $settlementDay1 精算予定日１
     * @param string $settlementDay2 精算予定日２
     * @param string $settlementDay3 精算予定日３
     * @param string $settlementDayWeek 精算予定日（週締め）
     * @param string $now ｼｽﾃﾑ日付の日にち
     * @param string $fixPattern 締めパターン
     * @param string $processDate 締め処理日
     * @param string $spanFrom 対象期間FROM
     * @param string $spanTo 対象期間TO
     * @param string $ordinal 月内〆順序
     */
    protected function getPeriodCurrentYear(
          $oemFixedPattern
        , $oemFixedDay1
        , $oemFixedDay2
        , $oemFixedDay3
        , $oemFixedWeek
        , $settlementDay1
        , $settlementDay2
        , $settlementDay3
        , $settlementWeek
        , $now
        , $fixPattern
        , &$processDate
        , &$spanFrom
        , &$spanTo
        , &$ordinal
    ) {
        $mdlbc = new TableBusinessCalendar( $this->db );

        // 対象期間の取得
        $nPrevYm = date('Y-m', strtotime("-2 month"));      // 前々月
        $prevYm = date('Y-m', strtotime("-1 month"));       // 前月
        $nowYm = date('Y-m');                               // 当月
        $nextYm = date('Y-m', strtotime("1 month"));        // 翌月

        // 締めパターンが日付指定の場合
        if ( $oemFixedPattern == 0 ) {
            //2) WK.精算予定日1 が 41～45の場合
            if($settlementDay1 > 40 && $settlementDay1 < 46){
                $codetoday1 = '';
                if($settlementDay1==41)$codetoday1 = "Monday";
                if($settlementDay1==42)$codetoday1 = "Tuesday";
                if($settlementDay1==43)$codetoday1 = "Wednesday";
                if($settlementDay1==44)$codetoday1 = "Thursday";
                if($settlementDay1==45)$codetoday1 = "Friday";

                $codetoday2 = '';
                if($settlementDay2==41)$codetoday2 = "Monday";
                if($settlementDay2==42)$codetoday2 = "Tuesday";
                if($settlementDay2==43)$codetoday2 = "Wednesday";
                if($settlementDay2==44)$codetoday2 = "Thursday";
                if($settlementDay2==45)$codetoday2 = "Friday";

                $codetoday3 = '';
                if($settlementDay3==41)$codetoday3 = "Monday";
                if($settlementDay3==42)$codetoday3 = "Tuesday";
                if($settlementDay3==43)$codetoday3 = "Wednesday";
                if($settlementDay3==44)$codetoday3 = "Thursday";
                if($settlementDay3==45)$codetoday3 = "Friday";

                // 締めパターン１の場合
                if($fixPattern == 1) {
                    if ($now <= $oemFixedDay1) {
                        //前月年月+WK.OEM締め日1の翌週 WK.精算予定日1（※1）
                        $wk_ProcessDate = date('Y-m-d',strtotime($codetoday1 . ' next week' . $this->makeDate($prevYm, $oemFixedDay1 + 1)));
                        $processDate = $mdlbc->getPrevBusinessDate($wk_ProcessDate);
                        // 対象期間FROM
                        //(前々月年月+WK.OEM締め日1)+1日
                        $spanFrom = date('Y-m-d', strtotime($this->makeDate($nPrevYm, $oemFixedDay1) . " +1 days"));
                        // 対象期間TO
                        //前月年月+WK.OEM締め日1
                        $spanTo = $this->makeDate($prevYm, $oemFixedDay1);
                        // 月内〆順序
                        $ordinal = 1;
                    }else if ($oemFixedDay1 < $now) {
                        //当月年月+WK.OEM締め日1の翌週 WK.精算予定日1（※1）
                        $wk_ProcessDate = date('Y-m-d',strtotime($codetoday1 . ' next week' . $this->makeDate($nowYm, $oemFixedDay1 + 1)));
                        $processDate = $mdlbc->getPrevBusinessDate($wk_ProcessDate);
                        // 対象期間FROM
                        //(前月年月+WK.OEM締め日1)+1日
                        $spanFrom = date('Y-m-d', strtotime($this->makeDate($prevYm, $oemFixedDay1) . " +1 days"));
                        // 対象期間TO
                        //当月年月+WK.OEM締め日1
                        $spanTo = $this->makeDate($nowYm, $oemFixedDay1);
                        // 月内〆順序
                        $ordinal = 1;
                    }
                    // 締めパターン２の場合
                }else if($fixPattern == 2){
                    if ($now <= $oemFixedDay1) {
                        //前月年月+WK.OEM締め日2	の翌週 WK.精算予定日2（※1）
                        $wk_ProcessDate = date('Y-m-d',strtotime($codetoday2 . ' next week' . $this->makeDate($prevYm, $oemFixedDay2 + 1)));
                        $processDate = $mdlbc->getPrevBusinessDate($wk_ProcessDate);

                        // 対象期間FROM
                        //(前月年月＋WK.OEM締め日1)+1日
                        $spanFrom = date('Y-m-d', strtotime($this->makeDate($prevYm, $oemFixedDay1) . " +1 days"));
                        // 対象期間TO
                        //前月年月＋WK.OEM締め日2
                        $spanTo = $this->makeDate($prevYm, $oemFixedDay2);
                        // 月内〆順序
                        $ordinal = 2;

                    }else if ($oemFixedDay1 < $now && $now <= $oemFixedDay2) {
                        //当月年月+WK.OEM締め日1	の翌週 WK.精算予定日1（※1）
                        $wk_ProcessDate = date('Y-m-d',strtotime($codetoday1 . ' next week' . $this->makeDate($nowYm, $oemFixedDay1 + 1)));
                        $processDate = $mdlbc->getPrevBusinessDate($wk_ProcessDate);
                        // 対象期間FROM
                        // (前月年月＋WK.OEM締め日2)+1日
                        $spanFrom = date('Y-m-d', strtotime($this->makeDate($prevYm, $oemFixedDay2) . " +1 days"));
                        // 対象期間TO
                        // 当月年月+WK.OEM締め日1
                        $spanTo = $this->makeDate($nowYm, $oemFixedDay1);
                        // 月内〆順序
                        $ordinal = 1;

                    }else if ($oemFixedDay2 < $now) {
                        //当月年月+WK.OEM締め日2の翌週 WK.精算予定日2（※1）
                        $wk_ProcessDate = date('Y-m-d',strtotime($codetoday2 . ' next week' . $this->makeDate($nowYm, $oemFixedDay2 + 1)));
                        $processDate = $mdlbc->getPrevBusinessDate($wk_ProcessDate);
                        // 対象期間FROM
                        // (当月年月＋WK.OEM締め日1)+1日
                        $spanFrom = date('Y-m-d', strtotime($this->makeDate($nowYm, $oemFixedDay1) . " +1 days"));
                        // 対象期間TO
                        //当月年月＋WK.OEM締め日2
                        $spanTo = $this->makeDate($nowYm, $oemFixedDay2);
                        // 月内〆順序
                        $ordinal = 2;

                    }
                    // 締めパターン３の場合
                }else{
                    if ($now <= $oemFixedDay1) {
                        //前月年月+WK.OEM締め日3 の翌週 WK.精算予定日3（※1）
                        $wk_ProcessDate = date('Y-m-d',strtotime($codetoday3 . ' next week' . $this->makeDate($prevYm, $oemFixedDay3 + 1)));
                        $processDate = $mdlbc->getPrevBusinessDate($wk_ProcessDate);
                        // 対象期間FROM
                        // (前月年月＋WK.OEM締め日2)+1日
                        $spanFrom = date('Y-m-d', strtotime($this->makeDate($prevYm, $oemFixedDay2) . " +1 days"));
                        // 対象期間TO
                        // 前月年月＋WK.OEM締め日3
                        $spanTo = $this->makeDate($prevYm, $oemFixedDay3);
                        // 月内〆順序
                        $ordinal = 3;

                    }else if ($oemFixedDay1 < $now && $now <= $oemFixedDay2) {
                        //当月年月+WK.OEM締め日1の翌週 WK.精算予定日1（※1）
                        $wk_ProcessDate = date('Y-m-d',strtotime($codetoday1 . ' next week' . $this->makeDate($nowYm, $oemFixedDay1 + 1)));
                        $processDate = $mdlbc->getPrevBusinessDate($wk_ProcessDate);
                        // 対象期間FROM
                        // (前月年月＋WK.OEM締め日3)+1日
                        $spanFrom = date('Y-m-d', strtotime($this->makeDate($prevYm, $oemFixedDay3) . " +1 days"));
                        // 対象期間TO
                        // 当月年月+WK.OEM締め日1
                        $spanTo = $this->makeDate($nowYm, $oemFixedDay1);
                        // 月内〆順序
                        $ordinal = 1;

                    }else if ($oemFixedDay2 < $now && $now <= $oemFixedDay3) {
                        //当月年月+WK.OEM締め日2の翌週 WK.精算予定日2（※1）
                        $wk_ProcessDate = date('Y-m-d',strtotime($codetoday2 . ' next week' . $this->makeDate($nowYm, $oemFixedDay2 + 1)));
                        $processDate = $mdlbc->getPrevBusinessDate($wk_ProcessDate);
                        // 対象期間FROM
                        // (当月年月＋WK.OEM締め日1)+1日
                        $spanFrom = date('Y-m-d', strtotime($this->makeDate($nowYm, $oemFixedDay1) . " +1 days"));
                        // 対象期間TO
                        // 当月年月＋WK.OEM締め日2
                        $spanTo = $this->makeDate($nowYm, $oemFixedDay2);
                        // 月内〆順序
                        $ordinal = 2;

                    }else if ($oemFixedDay3 < $now) {
                        //当月年月+WK.OEM締め日3の翌週 WK.精算予定日3（※1）
                        $wk_ProcessDate = date('Y-m-d',strtotime($codetoday3 . ' next week' . $this->makeDate($nowYm, $oemFixedDay3 + 1)));
                        $processDate = $mdlbc->getPrevBusinessDate($wk_ProcessDate);

                        // 対象期間FROM
                        // (当月年月＋WK.OEM締め日2)+1日
                        $spanFrom = date('Y-m-d', strtotime($this->makeDate($nowYm, $oemFixedDay2) . " +1 days"));
                        // 対象期間TO
                        // 当月年月＋WK.OEM締め日3
                        $spanTo = $this->makeDate($nowYm, $oemFixedDay3);
                        // 月内〆順序
                        $ordinal = 3;

                    }

                }

                //1) WK.精算予定日1 が 41～45以外の場合
            }else{
                // 締めパターン１の場合
                if ($fixPattern == 1) {
                    // ｼｽﾃﾑ日付の日にち <= 締め日１の場合、対象期間は前月

                    if ($now <= $oemFixedDay1) {
                        // 精算予定日１ <= 締め日１
                        if ($settlementDay1 <= $oemFixedDay1) {
                            // 当月年月 + 精算予定日１
                            $processDate = $this->makeDate($nowYm, $settlementDay1);    // 締め処理日
                            // 締め日１ < 精算予定日１
                        } else if ($oemFixedDay1 < $settlementDay1) {
                            // 前月年月 + 精算予定日１
                            $processDate = $this->makeDate($prevYm, $settlementDay1);   // 締め処理日
                        }
                        // 対象期間FROM
                        // 前々月＋締め日１＋1日
                        $spanFrom = date('Y-m-d', strtotime($this->makeDate($nPrevYm, $oemFixedDay1) . " +1 days"));
                        // 対象期間TO
                        // 前月＋締め日１
                        $spanTo = $this->makeDate($prevYm, $oemFixedDay1);
                        // 月内〆順序
                        $ordinal = 1;
                    }
                    // 締め日１ < ｼｽﾃﾑ日付の日にちの場合、対象期間は当月
                    else if ($oemFixedDay1 < $now) {
                        // 精算予定日１ <= 締め日１
                        if ($settlementDay1 <= $oemFixedDay1) {
                            // 翌月年月 + 精算予定日１
                            $processDate = $this->makeDate($nextYm, $settlementDay1);    // 締め処理日
                        }
                        // 締め日 < 精算予定日１
                        else if ($oemFixedDay1 < $settlementDay1) {
                            // 当月年月 + 精算予定日１
                            $processDate = $this->makeDate($nowYm, $settlementDay1);    // 締め処理日
                        }
                        // 対象期間FROM
                        // 前月＋締め日１＋1日
                        $spanFrom = date('Y-m-d', strtotime($this->makeDate($prevYm, $oemFixedDay1) . " +1 days"));
                        // 対象期間TO
                        // 当月＋締め日１
                        $spanTo = $this->makeDate($nowYm, $oemFixedDay1);
                        // 月内〆順序
                        $ordinal = 1;
                    }
                }
                // 締めパターン２の場合
                else if ($fixPattern == 2) {
                    // ｼｽﾃﾑ日付の日にち <= 締め日１の場合、対象期間は前月の締め日２
                    if ($now <= $oemFixedDay1) {
                        // 精算予定日２ <= 締め日２
                        if ($settlementDay2 <= $oemFixedDay2) {
                            // 当月年月 + 精算予定日２
                            $processDate = $this->makeDate($nowYm, $settlementDay2);    // 締め処理日
                        }
                        // 締め日２ < 精算予定日２
                        else if ($oemFixedDay2 < $settlementDay2) {
                            // 前月年月 + 精算予定日２
                            $processDate = $this->makeDate($prevYm, $settlementDay2);    // 締め処理日
                        }
                        // 対象期間FROM
                        // 前月＋締め日１＋1日
                        $spanFrom = date('Y-m-d', strtotime($this->makeDate($prevYm, $oemFixedDay1) . " +1 days"));
                        // 対象期間TO
                        // 前月＋締め日２
                        $spanTo = $this->makeDate($prevYm, $oemFixedDay2);
                        // 月内〆順序
                        $ordinal = 2;
                    }
                    // 締め日１ < ｼｽﾃﾑ日付の日にち <= 締め日２の場合、対象期間は当月の締め日１
                    else if ($oemFixedDay1 < $now && $now <= $oemFixedDay2) {
                        // 精算予定日１ <= 締め日１
                        if ($settlementDay1 <= $oemFixedDay1) {
                            // 翌月年月 + 精算予定日１
                            $processDate = $this->makeDate($nextYm, $settlementDay1);    // 締め処理日
                        }
                        // 締め日１ < 精算予定日１
                        else if ($oemFixedDay1 < $settlementDay1) {
                            // 当月年月 + 精算予定日１
                            $processDate = $this->makeDate($nowYm, $settlementDay1);    // 締め処理日
                        }
                        // 対象期間FROM
                        // 前月＋締め日２＋1日
                        $spanFrom = date('Y-m-d', strtotime($this->makeDate($prevYm, $oemFixedDay2) . " +1 days"));
                        // 対象期間TO
                        // 当月＋締め日１
                        $spanTo = $this->makeDate($nowYm, $oemFixedDay1);
                        // 月内〆順序
                        $ordinal = 1;
                    }
                    // 締め日２ < ｼｽﾃﾑ日付の日にちの場合、対象期間は当月の締め日２
                    else if ($oemFixedDay2 < $now) {
                        // 精算予定日２ <= 締め日２
                        if ($settlementDay2 <= $oemFixedDay2) {
                            // 翌月年月 + 精算予定日２
                            $processDate = $this->makeDate($nextYm, $settlementDay2);    // 締め処理日
                        }
                        // 締め日２ < 精算予定日２
                        else if ($oemFixedDay2 < $settlementDay2) {
                            // 当月年月 + 精算予定日２
                            $processDate = $this->makeDate($nowYm, $settlementDay2);    // 締め処理日
                        }
                        // 対象期間FROM
                        // 当月＋締め日１＋1日
                        $spanFrom = date('Y-m-d', strtotime($this->makeDate($nowYm, $oemFixedDay1) . " +1 days"));
                        // 対象期間TO
                        // 当月＋締め日２
                        $spanTo = $this->makeDate($nowYm, $oemFixedDay2);
                        // 月内〆順序
                        $ordinal = 2;
                    }
                }
                // 締めパターン３の場合
                else {
                    // ｼｽﾃﾑ日付の日にち <= 締め日１の場合、対象期間は前月の締め日３
                    if ($now <= $oemFixedDay1) {
                        // 精算予定日３ <= 締め日３
                        if ($settlementDay3 <= $oemFixedDay3) {
                            // 当月年月 + 精算予定日３
                            $processDate = $this->makeDate($nowYm, $settlementDay3);    // 締め処理日
                        }
                        // 締め日３ < 精算予定日３
                        else if ($oemFixedDay3 < $settlementDay3) {
                            // 前月年月 + 精算予定日３
                            $processDate = $this->makeDate($prevYm, $settlementDay3);    // 締め処理日
                        }
                        // 対象期間FROM
                        // 前月＋締め日２＋1日
                        $spanFrom = date('Y-m-d', strtotime($this->makeDate($prevYm, $oemFixedDay2) . " +1 days"));
                        // 対象期間TO
                        // 前月＋締め日３
                        $spanTo = $this->makeDate($prevYm, $oemFixedDay3);
                        // 月内〆順序
                        $ordinal = 3;
                    }
                    // 締め日１ < ｼｽﾃﾑ日付の日にち <= 締め日２の場合、対象期間は当月の締め日１
                    else if ($oemFixedDay1 < $now && $now <= $oemFixedDay2) {
                        // 精算予定日１ <= 締め日１
                        if ($settlementDay1 <= $oemFixedDay1) {
                            // 翌月年月 + 精算予定日１
                            $processDate = $this->makeDate($nextYm, $settlementDay1);    // 締め処理日
                        }
                        // 締め日１ < 精算予定日１
                        else if ($oemFixedDay1 < $settlementDay1) {
                            // 当月年月 + 精算予定日１
                            $processDate = $this->makeDate($nowYm, $settlementDay1);    // 締め処理日
                        }
                        // 対象期間FROM
                        // 前月＋締め日３＋1日
                        $spanFrom = date('Y-m-d', strtotime($this->makeDate($prevYm, $oemFixedDay3) . " +1 days"));
                        // 対象期間TO
                        // 当月＋締め日１
                        $spanTo = $this->makeDate($nowYm, $oemFixedDay1);
                        // 月内〆順序
                        $ordinal = 1;
                    }
                    // 締め日２ < ｼｽﾃﾑ日付の日にち <= 締め日３の場合、対象期間は当月の締め日２
                    else if ($oemFixedDay2 < $now && $now <= $oemFixedDay3) {
                        // 精算予定日２ <= 締め日２
                        if ($settlementDay2 <= $oemFixedDay2) {
                            // 翌月年月 + 精算予定日２
                            $processDate = $this->makeDate($nextYm, $settlementDay2);    // 締め処理日
                        }
                        // 締め日２ < 精算予定日２
                        else if ($oemFixedDay2 < $settlementDay2) {
                            // 当月年月 + 精算予定日２
                            $processDate = $this->makeDate($nowYm, $settlementDay2);    // 締め処理日
                        }
                        // 対象期間FROM
                        // 当月＋締め日１＋1日
                        $spanFrom = date('Y-m-d', strtotime($this->makeDate($nowYm, $oemFixedDay1) . " +1 days"));
                        // 対象期間TO
                        // 当月＋締め日２
                        $spanTo = $this->makeDate($nowYm, $oemFixedDay2);
                        // 月内〆順序
                        $ordinal = 2;
                    }
                    // 締め日３ < ｼｽﾃﾑ日付の日にちの場合、対象期間は当月の締め日３
                    else if ($oemFixedDay3 < $now) {
                        // 精算予定日３ <= 締め日３
                        if ($settlementDay3 <= $oemFixedDay3) {
                            // 翌月年月 + 精算予定日３
                            $processDate = $this->makeDate($nextYm, $settlementDay3);    // 締め処理日
                        }
                        // 締め日３ < 精算予定日３
                        else if ($oemFixedDay3 < $settlementDay3) {
                            // 当月年月 + 精算予定日３
                            $processDate = $this->makeDate($nowYm, $settlementDay3);    // 締め処理日
                        }
                        // 対象期間FROM
                        // 当月＋締め日２＋1日
                        $spanFrom = date('Y-m-d', strtotime($this->makeDate($nowYm, $oemFixedDay2) . " +1 days"));
                        // 対象期間TO
                        // 当月＋締め日３
                        $spanTo = $this->makeDate($nowYm, $oemFixedDay3);
                        // 月内〆順序
                        $ordinal = 3;
                    }
                }
            }
        } else {
            // 週締め
            $tmpDT = new DateTime();
            $toWeek = $tmpDT->format('w');
            $toWeek = ( $toWeek == 0 ) ? 7 : $toWeek; // 日曜日は 7 とする

            // OEM締日の曜日を文字列に変換
            $codeToDay = '';
            if ( $oemFixedWeek == 1 ) $codeToDay = "Monday";
            if ( $oemFixedWeek == 2 ) $codeToDay = "Tuesday";
            if ( $oemFixedWeek == 3 ) $codeToDay = "Wednesday";
            if ( $oemFixedWeek == 4 ) $codeToDay = "Thursday";
            if ( $oemFixedWeek == 5 ) $codeToDay = "Friday";
            if ( $oemFixedWeek == 6 ) $codeToDay = "Saturday";
            if ( $oemFixedWeek == 7 ) $codeToDay = "Sunday";

            // 当日から見て直近の曜日に移動する
            if ($toWeek <= $oemFixedWeek) {
                $tmpDT->modify($codeToDay. ' last week');
            } else {
                $tmpDT->modify($codeToDay. ' this week');
            }

            // 締め期間の終了日を取得
            $spanTo = $tmpDT->format('Y-m-d');

            // ６日前にさらに移動する
            $tmpDT->modify('-6 day');

            // 締め期間の開始日を取得
            $spanFrom = $tmpDT->format('Y-m-d');

            // 戻す
            $tmpDT->modify('+6 day');

            // 精算予定日の曜日を文字列に変換
            $codeToDay = '';
            if ( $settlementWeek == 1) $codeToDay = "Monday";
            if ( $settlementWeek == 2) $codeToDay = "Tuesday";
            if ( $settlementWeek == 3) $codeToDay = "Wednesday";
            if ( $settlementWeek == 4) $codeToDay = "Thursday";
            if ( $settlementWeek == 5) $codeToDay = "Friday";

            //週初を月曜日->日曜日になるよう補正
            $tmpDT->modify('+1 day');

            // 締日から見て翌週の精算日を算出
            $tmpDT->modify($codeToDay. ' next week');
            $wk_ProcessDate = $tmpDT->format('Y-m-d');
            $processDate = $mdlbc->getPrevBusinessDate($wk_ProcessDate);

            // 月内〆順序
            $ordinal = 1;
            list( $y, $m, $d ) = explode( "-", $spanTo );

            // 曜日。フルスペル形式。SundayからSaturday
            $l = date( "l", mktime( 0, 0, 0, $m, $d, $y ) );

            // 月。フルスペルの文字。January から December
            $f = date( "F", mktime( 0, 0, 0, $m, $d, $y ) );

            // 例えば date("j",strtotime("first Sunday of June 2019")) は 2
            if ( date("j", strtotime( "first  {$l} of {$f} {$y}" ) ) == $d ) $ordinal = 1;
            if ( date("j", strtotime( "second {$l} of {$f} {$y}" ) ) == $d ) $ordinal = 2;
            if ( date("j", strtotime( "third  {$l} of {$f} {$y}" ) ) == $d ) $ordinal = 3;
            if ( date("j", strtotime( "fourth {$l} of {$f} {$y}" ) ) == $d ) $ordinal = 4;
            if ( date("j", strtotime( "fifth  {$l} of {$f} {$y}" ) ) == $d ) $ordinal = 5;
        }
    }


    /**
     * OEM締めの対象期間を決定（本締め後の対象期間は翌期）
     *
     * @param string $oemFixedPattern 締パターン
     * @param string $oemFixedDay1 締め日１
     * @param string $oemFixedDay2 締め日２
     * @param string $oemFixedDay3 締め日３
     * @param string $oemFixedDayWeek 締め日（週締め）
     * @param string $settlementDay1 精算予定日１
     * @param string $settlementDay2 精算予定日２
     * @param string $settlementDay3 精算予定日３
     * @param string $settlementDayWeek 精算予定日（週締め）
     * @param string $now ｼｽﾃﾑ日付の日にち
     * @param string $fixPattern 締めパターン
     * @param string $processDate 締め処理日
     * @param string $spanFrom 対象期間FROM
     * @param string $spanTo 対象期間TO
     * @param string $ordinal 月内〆順序
     */
    protected function getPeriodNextFiscal(
          $oemFixedPattern
        , $oemFixedDay1
        , $oemFixedDay2
        , $oemFixedDay3
        , $oemFixedWeek
        , $settlementDay1
        , $settlementDay2
        , $settlementDay3
        , $settlementWeek
        , $now
        , $fixPattern
        , &$processDate
        , &$spanFrom
        , &$spanTo
        , &$ordinal
    ) {
        $mdlbc = new TableBusinessCalendar( $this->db );

        // 対象期間の取得
        $prevYm = date('Y-m', strtotime("-1 month"));       // 前月
        $nowYm = date('Y-m');                               // 当月
        $nextYm = date('Y-m', strtotime("1 month"));        // 翌月
        $nNextYm = date('Y-m', strtotime("2 month"));       // 翌々月

        // 締めパターンが日付指定の場合
        if ( $oemFixedPattern == 0 ) {

            if($settlementDay1 > 40 && $settlementDay1 < 46){

                $codetoday1 = '';
                if($settlementDay1==41)$codetoday1 = "Monday";
                if($settlementDay1==42)$codetoday1 = "Tuesday";
                if($settlementDay1==43)$codetoday1 = "Wednesday";
                if($settlementDay1==44)$codetoday1 = "Thursday";
                if($settlementDay1==45)$codetoday1 = "Friday";

                $codetoday2 = '';
                if($settlementDay2==41)$codetoday2 = "Monday";
                if($settlementDay2==42)$codetoday2 = "Tuesday";
                if($settlementDay2==43)$codetoday2 = "Wednesday";
                if($settlementDay2==44)$codetoday2 = "Thursday";
                if($settlementDay2==45)$codetoday2 = "Friday";

                $codetoday3 = '';
                if($settlementDay3==41)$codetoday3 = "Monday";
                if($settlementDay3==42)$codetoday3 = "Tuesday";
                if($settlementDay3==43)$codetoday3 = "Wednesday";
                if($settlementDay3==44)$codetoday3 = "Thursday";
                if($settlementDay3==45)$codetoday3 = "Friday";

                // 締めパターン１の場合
                if ($fixPattern == 1) {
                    if ($now <= $oemFixedDay1) {
                        //当月年月+WK.OEM締め日1の翌週 WK.精算予定日1（※1）
                        $wk_ProcessDate = date('Y-m-d',strtotime($codetoday1 . ' next week' . $this->makeDate($nowYm, $oemFixedDay1 + 1)));
                        $processDate = $mdlbc->getPrevBusinessDate($wk_ProcessDate);

                        // 対象期間FROM
                        // (前月年月+WK.OEM締め日1)+1日
                        $spanFrom = date('Y-m-d', strtotime($this->makeDate($prevYm, $oemFixedDay1) . " +1 days"));
                        // 対象期間TO
                        // 当月年月+WK.OEM締め日1
                        $spanTo = $this->makeDate($nowYm, $oemFixedDay1);
                        // 月内〆順序
                        $ordinal = 1;

                    }else if ($oemFixedDay1 < $now) {
                        //翌月年月+WK.OEM締め日1の翌週 WK.精算予定日1（※1）
                        $wk_ProcessDate = date('Y-m-d',strtotime($codetoday1 . ' next week' . $this->makeDate($nextYm, $oemFixedDay1 + 1)));
                        $processDate = $mdlbc->getPrevBusinessDate($wk_ProcessDate);

                        // 対象期間FROM
                        // (当月年月+WK.OEM締め日1)+1日
                        $spanFrom = date('Y-m-d', strtotime($this->makeDate($nowYm, $oemFixedDay1) . " +1 days"));
                        // 対象期間TO
                        // 翌月年月+WK.OEM締め日1
                        $spanTo = $this->makeDate($nextYm, $oemFixedDay1);
                        // 月内〆順序
                        $ordinal = 1;

                    }
                }else if ($fixPattern == 2) {
                    if ($now <= $oemFixedDay1) {
                        //当月年月+WK.OEM締め日1の翌週 WK.精算予定日1（※1）
                        $wk_ProcessDate = date('Y-m-d',strtotime($codetoday1 . ' next week' . $this->makeDate($nowYm, $oemFixedDay1 + 1)));
                        $processDate = $mdlbc->getPrevBusinessDate($wk_ProcessDate);

                        // 対象期間FROM
                        // (前月年月＋WK.OEM締め日2)+1日
                        $spanFrom = date('Y-m-d', strtotime($this->makeDate($prevYm, $oemFixedDay2) . " +1 days"));
                        // 対象期間TO
                        // 当月年月+WK.OEM締め日1
                        $spanTo = $this->makeDate($nowYm, $oemFixedDay1);
                        // 月内〆順序
                        $ordinal = 1;

                    }else if ($oemFixedDay1 < $now && $now <= $oemFixedDay2) {
                        //当月年月+WK.OEM締め日2の翌週 WK.精算予定日2（※1）
                        $wk_ProcessDate = date('Y-m-d',strtotime($codetoday2 . ' next week' . $this->makeDate($nowYm, $oemFixedDay2 + 1)));
                        $processDate = $mdlbc->getPrevBusinessDate($wk_ProcessDate);

                        // 対象期間FROM
                        // (当月年月＋WK.OEM締め日1)+1日
                        $spanFrom = date('Y-m-d', strtotime($this->makeDate($nowYm, $oemFixedDay1) . " +1 days"));
                        // 対象期間TO
                        // 当月年月＋WK.OEM締め日2
                        $spanTo = $this->makeDate($nowYm, $oemFixedDay2);
                        // 月内〆順序
                        $ordinal = 2;

                    }else if ($oemFixedDay2 < $now) {
                        //翌月年月+WK.OEM締め日1の翌週 WK.精算予定日1（※1）
                        $wk_ProcessDate = date('Y-m-d',strtotime($codetoday1 . ' next week' . $this->makeDate($nextYm, $oemFixedDay1 + 1)));
                        $processDate = $mdlbc->getPrevBusinessDate($wk_ProcessDate);

                        // 対象期間FROM
                        // (当月年月＋WK.OEM締め日2)+1日
                        $spanFrom = date('Y-m-d', strtotime($this->makeDate($nowYm, $oemFixedDay2) . " +1 days"));
                        // 対象期間TO
                        // 翌月年月+WK.OEM締め日1
                        $spanTo = $this->makeDate($nextYm, $oemFixedDay1);
                        // 月内〆順序
                        $ordinal = 1;

                    }
                } else {
                    if ($now <= $oemFixedDay1) {
                        //当月年月+WK.OEM締め日1の翌週 WK.精算予定日1（※1）
                        $wk_ProcessDate = date('Y-m-d',strtotime($codetoday1 . ' next week' . $this->makeDate($nowYm, $oemFixedDay1 + 1)));
                        $processDate = $mdlbc->getPrevBusinessDate($wk_ProcessDate);

                        // 対象期間FROM
                        // (前月年月＋WK.OEM締め日3)+1日
                        $spanFrom = date('Y-m-d', strtotime($this->makeDate($prevYm, $oemFixedDay3) . " +1 days"));
                        // 対象期間TO
                        // 当月年月+WK.OEM締め日1
                        $spanTo = $this->makeDate($nowYm, $oemFixedDay1);
                        // 月内〆順序
                        $ordinal = 1;

                    }else if ($oemFixedDay1 < $now && $now <= $oemFixedDay2) {
                        //当月年月+WK.OEM締め日2の翌週 WK.精算予定日2（※1）
                        $wk_ProcessDate = date('Y-m-d',strtotime($codetoday2 . ' next week' . $this->makeDate($nowYm, $oemFixedDay2 + 1)));
                        $processDate = $mdlbc->getPrevBusinessDate($wk_ProcessDate);

                        // 対象期間FROM
                        // (当月年月＋WK.OEM締め日1)+1日
                        $spanFrom = date('Y-m-d', strtotime($this->makeDate($nowYm, $oemFixedDay1) . " +1 days"));
                        // 対象期間TO
                        // 当月年月＋WK.OEM締め日2
                        $spanTo = $this->makeDate($nowYm, $oemFixedDay2);
                        // 月内〆順序
                        $ordinal = 2;

                    }else if ($oemFixedDay2 < $now && $now <= $oemFixedDay3) {
                        //当月年月+WK.OEM締め日3の翌週 WK.精算予定日3（※1）
                        $wk_ProcessDate = date('Y-m-d',strtotime($codetoday3 . ' next week' . $this->makeDate($nowYm, $oemFixedDay3 + 1)));
                        $processDate = $mdlbc->getPrevBusinessDate($wk_ProcessDate);

                        // 対象期間FROM
                        // (当月年月＋WK.OEM締め日2)+1日
                        $spanFrom = date('Y-m-d', strtotime($this->makeDate($nowYm, $oemFixedDay2) . " +1 days"));
                        // 対象期間TO
                        // 当月年月＋WK.OEM締め日3
                        $spanTo = $this->makeDate($nowYm, $oemFixedDay3);
                        // 月内〆順序
                        $ordinal = 3;

                    }else if ($oemFixedDay3 < $now) {
                        //翌月年月+WK.OEM締め日1の翌週 WK.精算予定日1（※1）
                        $wk_ProcessDate = date('Y-m-d',strtotime($codetoday1 . ' next week' . $this->makeDate($nextYm, $oemFixedDay1 + 1)));
                        $processDate = $mdlbc->getPrevBusinessDate($wk_ProcessDate);

                        // 対象期間FROM
                        // (当月年月＋WK.OEM締め日3)+1日
                        $spanFrom = date('Y-m-d', strtotime($this->makeDate($nowYm, $oemFixedDay3) . " +1 days"));
                        // 対象期間TO
                        // 翌月年月+WK.OEM締め日1
                        $spanTo = $this->makeDate($nextYm, $oemFixedDay1);
                        // 月内〆順序
                        $ordinal = 1;

                    }
                }
            }else{

                // 締めパターン１の場合
                if ($fixPattern == 1) {
                    // ｼｽﾃﾑ日付の日にち <= 締め日１の場合、対象期間は当月
                    if ($now <= $oemFixedDay1) {
                        // 精算予定日１ <= 締め日１
                        if ($settlementDay1 <= $oemFixedDay1) {
                            // 翌月年月 + 精算予定日１
                            $processDate = $this->makeDate($nextYm, $settlementDay1);    // 締め処理日
                            // 締め日１ < 精算予定日１
                        } else if ($oemFixedDay1 < $settlementDay1) {
                            // 当月年月 + 精算予定日１
                            $processDate = $this->makeDate($nowYm, $settlementDay1);   // 締め処理日
                        }
                        // 対象期間FROM
                        // 前月＋締め日１＋1日
                        $spanFrom = date('Y-m-d', strtotime($this->makeDate($prevYm, $oemFixedDay1) . " +1 days"));
                        // 対象期間TO
                        // 当月＋締め日１
                        $spanTo = $this->makeDate($nowYm, $oemFixedDay1);
                        // 月内〆順序
                        $ordinal = 1;
                    }
                    // 締め日１ < ｼｽﾃﾑ日付の日にちの場合、対象期間は翌月
                    else if ($oemFixedDay1 < $now) {
                        // 精算予定日１ <= 締め日１
                        if ($settlementDay1 <= $oemFixedDay1) {
                            // 翌々月年月 + 精算予定日１
                            $processDate = $this->makeDate($nNextYm, $settlementDay1);    // 締め処理日
                        }
                        // 締め日 < 精算予定日１
                        else if ($oemFixedDay1 < $settlementDay1) {
                            // 翌月年月 + 精算予定日１
                            $processDate = $this->makeDate($nextYm, $settlementDay1);    // 締め処理日
                        }
                        // 対象期間FROM
                        // 当月＋締め日１＋1日
                        $spanFrom = date('Y-m-d', strtotime($this->makeDate($nowYm, $oemFixedDay1) . " +1 days"));
                        // 対象期間TO
                        // 翌月＋締め日１
                        $spanTo = $this->makeDate($nextYm, $oemFixedDay1);
                        // 月内〆順序
                        $ordinal = 1;
                    }
                }
                // 締めパターン２の場合
                else if ($fixPattern == 2) {
                    // ｼｽﾃﾑ日付の日にち <= 締め日１の場合、対象期間は当月の締め日１
                    if ($now <= $oemFixedDay1) {
                        // 精算予定日１ <= 締め日１
                        if ($settlementDay1 <= $oemFixedDay1) {
                            // 翌月年月 + 精算予定日１
                            $processDate = $this->makeDate($nextYm, $settlementDay1);    // 締め処理日
                        }
                        // 締め日１ < 精算予定日１
                        else if ($oemFixedDay1 < $settlementDay1) {
                            // 当月年月 + 精算予定日１
                            $processDate = $this->makeDate($nowYm, $settlementDay1);    // 締め処理日
                        }
                        // 対象期間FROM
                        // 前月＋締め日２＋1日
                        $spanFrom = date('Y-m-d', strtotime($this->makeDate($prevYm, $oemFixedDay2) . " +1 days"));
                        // 対象期間TO
                        // 当月＋締め日１
                        $spanTo = $this->makeDate($nowYm, $oemFixedDay1);
                        // 月内〆順序
                        $ordinal = 1;
                    }
                    // 締め日１ < ｼｽﾃﾑ日付の日にち <= 締め日２の場合、対象期間は当月の締め日２
                    else if ($oemFixedDay1 < $now && $now <= $oemFixedDay2) {
                        // 精算予定日２ <= 締め日２
                        if ($settlementDay2 <= $oemFixedDay2) {
                            // 翌月年月 + 精算予定日２
                            $processDate = $this->makeDate($nextYm, $settlementDay2);    // 締め処理日
                        }
                        // 締め日２ < 精算予定日２
                        else if ($oemFixedDay2 < $settlementDay2) {
                            // 当月年月 + 精算予定日２
                            $processDate = $this->makeDate($nowYm, $settlementDay2);    // 締め処理日
                        }
                        // 対象期間FROM
                        // 当月＋締め日１＋1日
                        $spanFrom = date('Y-m-d', strtotime($this->makeDate($nowYm, $oemFixedDay1) . " +1 days"));
                        // 対象期間TO
                        // 当月＋締め日２
                        $spanTo = $this->makeDate($nowYm, $oemFixedDay2);
                        // 月内〆順序
                        $ordinal = 2;
                    }
                    // 締め日２ < ｼｽﾃﾑ日付の日にちの場合、対象期間は翌月の締め日１
                    else if ($oemFixedDay2 < $now) {
                        // 精算予定日１ <= 締め日１
                        if ($settlementDay1 <= $oemFixedDay1) {
                            // 翌々月年月 + 精算予定日１
                            $processDate = $this->makeDate($nNextYm, $settlementDay1);    // 締め処理日
                        }
                        // 締め日１ < 精算予定日１
                        else if ($oemFixedDay1 < $settlementDay1) {
                            // 翌月年月 + 精算予定日１
                            $processDate = $this->makeDate($nextYm, $settlementDay1);    // 締め処理日
                        }
                        // 対象期間FROM
                        // 当月＋締め日２＋1日
                        $spanFrom = date('Y-m-d', strtotime($this->makeDate($nowYm, $oemFixedDay2) . " +1 days"));
                        // 対象期間TO
                        // 翌月＋締め日１
                        $spanTo = $this->makeDate($nextYm, $oemFixedDay1);
                        // 月内〆順序
                        $ordinal = 1;
                    }
                }
                // 締めパターン３の場合
                else {
                    // ｼｽﾃﾑ日付の日にち <= 締め日１の場合、対象期間は当月の締め日１
                    if ($now <= $oemFixedDay1) {
                        // 精算予定日１ <= 締め日１
                        if ($settlementDay1 <= $oemFixedDay1) {
                            // 翌月年月 + 精算予定日１
                            $processDate = $this->makeDate($nextYm, $settlementDay1);    // 締め処理日
                        }
                        // 締め日１ < 精算予定日１
                        else if ($oemFixedDay1 < $settlementDay1) {
                            // 当月年月 + 精算予定日１
                            $processDate = $this->makeDate($nowYm, $settlementDay1);    // 締め処理日
                        }
                        // 前月＋締め日３＋1日
                        $spanFrom = date('Y-m-d', strtotime($this->makeDate($prevYm, $oemFixedDay3) . " +1 days"));
                        // 対象期間TO
                        // 当月＋締め日１
                        $spanTo = $this->makeDate($nowYm, $oemFixedDay1);
                        // 月内〆順序
                        $ordinal = 1;
                    }
                    // 締め日１ < ｼｽﾃﾑ日付の日にち <= 締め日２の場合、対象期間は当月の締め日２
                    else if ($oemFixedDay1 < $now && $now <= $oemFixedDay2) {
                        // 精算予定日２ <= 締め日２
                        if ($settlementDay2 <= $oemFixedDay2) {
                            // 翌月年月 + 精算予定日２
                            $processDate = $this->makeDate($nextYm, $settlementDay2);    // 締め処理日
                        }
                        // 締め日２ < 精算予定日２
                        else if ($oemFixedDay2 < $settlementDay2) {
                            // 当月年月 + 精算予定日２
                            $processDate = $this->makeDate($nowYm, $settlementDay2);    // 締め処理日
                        }
                        // 当月＋締め日１＋1日
                        $spanFrom = date('Y-m-d', strtotime($this->makeDate($nowYm, $oemFixedDay1) . " +1 days"));
                        // 対象期間TO
                        // 当月＋締め日２
                        $spanTo = $this->makeDate($nowYm, $oemFixedDay2);
                        // 月内〆順序
                        $ordinal = 2;
                    }
                    // 締め日２ < ｼｽﾃﾑ日付の日にち <= 締め日３の場合、対象期間は当月の締め日３
                    else if ($oemFixedDay2 < $now && $now <= $oemFixedDay3) {
                        // 精算予定日３ <= 締め日３
                        if ($settlementDay3 <= $oemFixedDay3) {
                            // 翌月年月 + 精算予定日３
                            $processDate = $this->makeDate($nextYm, $settlementDay3);    // 締め処理日
                        }
                        // 締め日３ < 精算予定日３
                        else if ($oemFixedDay3 < $settlementDay3) {
                            // 当月年月 + 精算予定日３
                            $processDate = $this->makeDate($nowYm, $settlementDay3);    // 締め処理日
                        }
                        // 対象期間FROM
                        // 当月＋締め日２＋1日
                        $spanFrom = date('Y-m-d', strtotime($this->makeDate($nowYm, $oemFixedDay2) . " +1 days"));
                        // 対象期間TO
                        // 当月＋締め日３
                        $spanTo = $this->makeDate($nowYm, $oemFixedDay3);
                        // 月内〆順序
                        $ordinal = 3;
                    }
                    // 締め日３ < ｼｽﾃﾑ日付の日にちの場合、対象期間は翌月の締め日１
                    else if ($oemFixedDay3 < $now) {
                        // 精算予定日１ <= 締め日１
                        if ($settlementDay1 <= $oemFixedDay1) {
                            // 翌々月年月 + 精算予定日１
                            $processDate = $this->makeDate($nNextYm, $settlementDay1);    // 締め処理日
                        }
                        // 締め日１ < 精算予定日１
                        else if ($oemFixedDay1 < $settlementDay1) {
                            // 翌月年月 + 精算予定日１
                            $processDate = $this->makeDate($nextYm, $settlementDay1);    // 締め処理日
                        }
                        // 対象期間FROM
                        // 当月＋締め日３＋1日
                        $spanFrom = date('Y-m-d', strtotime($this->makeDate($nowYm, $oemFixedDay3) . " +1 days"));
                        // 対象期間TO
                        // 翌月＋締め日１
                        $spanTo = $this->makeDate($nextYm, $oemFixedDay1);
                        // 月内〆順序
                        $ordinal = 1;
                    }
                }
            }
        } else {
            // 週締め
            $tmpDT = new DateTime();
            $toWeek = $tmpDT->format('w');
            $toWeek = ( $toWeek == 0 ) ? 7 : $toWeek; // 日曜日は 7 とする

            // OEM締日の曜日を文字列に変換
            $codeToDay = '';
            if ( $oemFixedWeek == 1 ) $codeToDay = "Monday";
            if ( $oemFixedWeek == 2 ) $codeToDay = "Tuesday";
            if ( $oemFixedWeek == 3 ) $codeToDay = "Wednesday";
            if ( $oemFixedWeek == 4 ) $codeToDay = "Thursday";
            if ( $oemFixedWeek == 5 ) $codeToDay = "Friday";
            if ( $oemFixedWeek == 6 ) $codeToDay = "Saturday";
            if ( $oemFixedWeek == 7 ) $codeToDay = "Sunday";

            // 当日から見て直近の曜日に移動する
            if ($toWeek <= $oemFixedWeek) {
                $tmpDT->modify($codeToDay. ' this week');
            } else {
                $tmpDT->modify($codeToDay. ' next week');
            }

            // 締め期間の終了日を取得
            $spanTo = $tmpDT->format('Y-m-d');

            // ６日前にさらに移動する
            $tmpDT->modify('-6 day');

            // 締め期間の開始日を取得
            $spanFrom = $tmpDT->format('Y-m-d');

            // 戻す
            $tmpDT->modify('+6 day');

            // 精算予定日の曜日を文字列に変換
            $codeToDay = '';
            if ( $settlementWeek == 1) $codeToDay = "Monday";
            if ( $settlementWeek == 2) $codeToDay = "Tuesday";
            if ( $settlementWeek == 3) $codeToDay = "Wednesday";
            if ( $settlementWeek == 4) $codeToDay = "Thursday";
            if ( $settlementWeek == 5) $codeToDay = "Friday";

            //週初を月曜日->日曜日になるよう補正
            $tmpDT->modify('+1 day');

            // 締日から見て翌週の精算日を算出
            $tmpDT->modify($codeToDay. ' next week');
            $wk_ProcessDate = $tmpDT->format('Y-m-d');
            $processDate = $mdlbc->getPrevBusinessDate($wk_ProcessDate);

            // 月内〆順序
            $ordinal = 1;
            list( $y, $m, $d ) = explode( "-", $spanTo );

            // 曜日。フルスペル形式。SundayからSaturday
            $l = date( "l", mktime( 0, 0, 0, $m, $d, $y ) );

            // 月。フルスペルの文字。January から December
            $f = date( "F", mktime( 0, 0, 0, $m, $d, $y ) );

            // 例えば date("j",strtotime("first Sunday of June 2019")) は 2
            if ( date("j", strtotime( "first  {$l} of {$f} {$y}" ) ) == $d ) $ordinal = 1;
            if ( date("j", strtotime( "second {$l} of {$f} {$y}" ) ) == $d ) $ordinal = 2;
            if ( date("j", strtotime( "third  {$l} of {$f} {$y}" ) ) == $d ) $ordinal = 3;
            if ( date("j", strtotime( "fourth {$l} of {$f} {$y}" ) ) == $d ) $ordinal = 4;
            if ( date("j", strtotime( "fifth  {$l} of {$f} {$y}" ) ) == $d ) $ordinal = 5;
        }
    }

// 2015/10/15 Y.Suzuki 対象期間の決定ﾛｼﾞｯｸの修正に併せてﾒｿｯﾄﾞ化 Add End

// 2015/10/16 Y.Suzuki ﾃﾞｰﾀ取得SQLを見直し、ﾒｿｯﾄﾞ化 Add Stt
    /**
     * OEM加盟店請求作成用ベースSQL取得
     * @return string ベースSQL
     *
     * @see 現行機能しているSQLと今回作成したSQLでの取得ﾃﾞｰﾀに若干の差異があったので差異が出ないように修正。
     */
    protected function getOemEnterpriseClaimedSql()
    {
        return <<<EOQ
SELECT  GROUP_CONCAT(DISTINCT pc.Seq SEPARATOR ',') AS SeqList                                      /* 立替振込管理Seqリスト */
    ,   oem.OemId                                                                                   /* OEMID */
    ,   e.EnterpriseId                                                                              /* 加盟店ID */
    ,   COUNT(o.OrderSeq) AS OrderSeqCnt                                                            /* 利用件数 */
    ,   SUM(IFNULL(pas.UseAmount, 0)) AS UseAmountSum                                               /* 利用額合計 */
    ,   MAX(e.Plan) AS Plan                                                                         /* 利用プラン */
    ,   MAX(s.SettlementFeeRate) AS SettlementFeeRate                                               /* 決済手数料率 */
    ,   MAX(s.OemSettlementFeeRate) AS OemSettlementFeeRate                                         /* OEM決済手数料率 */
    ,   COUNT(pc.Seq) AS CBSettlementCnt                                                            /* CB決済手数料件数 */
    ,   SUM(IFNULL(osf.SettlementFee, 0)) AS CBSettlementFeeSum                                     /* CB決済手数料合計 */
    ,   SUM( CASE WHEN ocf.ClaimFeeType = 1 THEN 1 ELSE 0 END) AS CBCntBS                           /* CB請求手数料件数（別送） */
    ,   SUM( CASE WHEN ocf.ClaimFeeType = 1 THEN IFNULL(ocf.ClaimFee, 0) ELSE 0 END ) AS CBClaimFeeSumBS   /* CB請求手数料（別送） */
    ,   SUM( CASE WHEN ocf.ClaimFeeType = 2 THEN 1 ELSE 0 END) AS CBCntDK                           /* CB請求手数料件数（同梱） */
    ,   SUM( CASE WHEN ocf.ClaimFeeType = 2 THEN IFNULL(ocf.ClaimFee, 0) ELSE 0 END ) AS CBClaimFeeSumDK   /* CB請求手数料（同梱） */
    ,   COUNT(pc.Seq) AS OEMSettlementCnt                                                           /* OEM決済手数料件数 */
    ,   SUM(IFNULL(pas.SettlementFee, 0) - IFNULL(osf.SettlementFee, 0)) AS OEMSettlementFeeSum     /* OEM決済手数料合計 */
    ,   SUM( CASE WHEN ocf.ClaimFeeType = 1 THEN 1 ELSE 0 END) AS OEMCntBS                          /* OEM請求手数料件数（別送） */
    ,   SUM( CASE WHEN ocf.ClaimFeeType = 1 THEN IFNULL(pas.ClaimFee, 0) - IFNULL(ocf.ClaimFee, 0) ELSE 0 END ) AS OEMClaimFeeSumBS /* OEM請求手数料（別送） */
    ,   SUM( CASE WHEN ocf.ClaimFeeType = 2 THEN 1 ELSE 0 END) AS OEMCntDK                          /* OEM請求手数料件数（同梱） */
    ,   SUM( CASE WHEN ocf.ClaimFeeType = 2 THEN IFNULL(pas.ClaimFee, 0) - IFNULL(ocf.ClaimFee, 0) ELSE 0 END ) AS OEMClaimFeeSumDK /* OEM請求手数料（同梱） */
    ,   MAX(oem.PayingMethod) AS PayingMethod                                                       /* CB/OEM立替区分 */
    ,   sum(IFNULL(oaf.AgencyFee, 0)) as AgencyFeeSum                                               /* 代理店手数料 */
    ,   COUNT(1) AS Cnt                                                                             /* 対象データ件数 */
FROM    T_PayingControl pc
        LEFT OUTER JOIN T_PayingAndSales pas ON (pas.PayingControlSeq = pc.Seq)
        LEFT OUTER JOIN T_Order o ON (o.OrderSeq = pas.OrderSeq)
        INNER JOIN T_Enterprise e ON (e.EnterpriseId = pc.EnterpriseId)
        INNER JOIN (SELECT  EnterpriseId
                        ,   MAX( F_GetCampaignVal(EnterpriseId, SiteId, :dateTo, 'SettlementFeeRate') ) AS SettlementFeeRate
                        ,   MAX( F_GetCampaignVal(EnterpriseId, SiteId, :dateTo, 'OemSettlementFeeRate') ) AS OemSettlementFeeRate
                    FROM    T_Site
                    GROUP BY EnterpriseId
                   ) s ON (s.EnterpriseId = e.EnterpriseId)
        LEFT OUTER JOIN T_OemSettlementFee osf ON (osf.OrderSeq = pas.OrderSeq)
        LEFT OUTER JOIN T_OemClaimFee ocf ON (ocf.OrderSeq = pas.OrderSeq)
        LEFT OUTER JOIN T_OemAgencyFee oaf ON (oaf.OrderSeq = pas.OrderSeq)
        INNER JOIN T_Oem oem ON (oem.OemId = pc.OemId)
WHERE   pc.OemClaimedAddUpFlg = 0
AND     pc.OemId = :OemId
AND     pc.PayingControlStatus = 1
AND     pc.ValidFlg = 1
AND     pc.FixedDate <= :FixedDate
GROUP BY
        oem.OemId
    ,   e.EnterpriseId
EOQ;
    }
    protected function getOemEnterpriseClaimedSql2()
    {
        return <<<EOQ
SELECT  GROUP_CONCAT(DISTINCT pc.Seq SEPARATOR ',') AS SeqList                                      /* 立替振込管理Seqリスト */
    ,   oem.OemId                                                                                   /* OEMID */
    ,   e.EnterpriseId                                                                              /* 加盟店ID */
    ,   COUNT(o.OrderSeq) AS OrderSeqCnt                                                            /* 利用件数 */
    ,   SUM(IFNULL(pas.UseAmount, 0)) AS UseAmountSum                                               /* 利用額合計 */
    ,   MAX(e.Plan) AS Plan                                                                         /* 利用プラン */
    ,   MAX(s.SettlementFeeRate) AS SettlementFeeRate                                               /* 決済手数料率 */
    ,   MAX(s.OemSettlementFeeRate) AS OemSettlementFeeRate                                         /* OEM決済手数料率 */
    ,   COUNT(pc.Seq) AS CBSettlementCnt                                                            /* CB決済手数料件数 */
    ,   SUM(IFNULL(osf.SettlementFee, 0)) AS CBSettlementFeeSum                                     /* CB決済手数料合計 */
    ,   SUM( CASE WHEN ocf.ClaimFeeType = 1 THEN 1 ELSE 0 END) AS CBCntBS                           /* CB請求手数料件数（別送） */
    ,   SUM( CASE WHEN ocf.ClaimFeeType = 1 THEN IFNULL(ocf.ClaimFee, 0) ELSE 0 END ) AS CBClaimFeeSumBS   /* CB請求手数料（別送） */
    ,   SUM( CASE WHEN ocf.ClaimFeeType = 2 THEN 1 ELSE 0 END) AS CBCntDK                           /* CB請求手数料件数（同梱） */
    ,   SUM( CASE WHEN ocf.ClaimFeeType = 2 THEN IFNULL(ocf.ClaimFee, 0) ELSE 0 END ) AS CBClaimFeeSumDK   /* CB請求手数料（同梱） */
    ,   COUNT(pc.Seq) AS OEMSettlementCnt                                                           /* OEM決済手数料件数 */
    ,   SUM(IFNULL(pas.SettlementFee, 0) - IFNULL(osf.SettlementFee, 0)) AS OEMSettlementFeeSum     /* OEM決済手数料合計 */
    ,   SUM( CASE WHEN ocf.ClaimFeeType = 1 THEN 1 ELSE 0 END) AS OEMCntBS                          /* OEM請求手数料件数（別送） */
    ,   SUM( CASE WHEN ocf.ClaimFeeType = 1 THEN IFNULL(pas.ClaimFee, 0) - IFNULL(ocf.ClaimFee, 0) ELSE 0 END ) AS OEMClaimFeeSumBS /* OEM請求手数料（別送） */
    ,   SUM( CASE WHEN ocf.ClaimFeeType = 2 THEN 1 ELSE 0 END) AS OEMCntDK                          /* OEM請求手数料件数（同梱） */
    ,   SUM( CASE WHEN ocf.ClaimFeeType = 2 THEN IFNULL(pas.ClaimFee, 0) - IFNULL(ocf.ClaimFee, 0) ELSE 0 END ) AS OEMClaimFeeSumDK /* OEM請求手数料（同梱） */
    ,   MAX(oem.PayingMethod) AS PayingMethod                                                       /* CB/OEM立替区分 */
    ,   sum(IFNULL(oaf.AgencyFee, 0)) as AgencyFeeSum                                               /* 代理店手数料 */
    ,   COUNT(1) AS Cnt                                                                             /* 対象データ件数 */
FROM    T_PayingControl pc
        LEFT OUTER JOIN T_PayingAndSales pas ON (pas.PayingControlSeq = pc.Seq)
        LEFT OUTER JOIN T_Order o ON (o.OrderSeq = pas.OrderSeq)
        LEFT OUTER JOIN AT_Order ao ON (ao.OrderSeq = o.OrderSeq)
        INNER JOIN T_Enterprise e ON (e.EnterpriseId = pc.EnterpriseId)
        INNER JOIN (SELECT  EnterpriseId
                        ,   MAX( F_GetCampaignVal(EnterpriseId, SiteId, :dateTo, 'SettlementFeeRate') ) AS SettlementFeeRate
                        ,   MAX( F_GetCampaignVal(EnterpriseId, SiteId, :dateTo, 'OemSettlementFeeRate') ) AS OemSettlementFeeRate
                    FROM    T_Site
                    GROUP BY EnterpriseId
                   ) s ON (s.EnterpriseId = e.EnterpriseId)
        LEFT OUTER JOIN T_OemSettlementFee osf ON (osf.OrderSeq = pas.OrderSeq)
        LEFT OUTER JOIN T_OemClaimFee ocf ON (ocf.OrderSeq = pas.OrderSeq)
        LEFT OUTER JOIN T_OemAgencyFee oaf ON (oaf.OrderSeq = pas.OrderSeq)
        INNER JOIN T_Oem oem ON (oem.OemId = pc.OemId)
WHERE   pc.OemClaimedAddUpFlg = 0
AND     pc.OemId = :OemId
AND     pc.PayingControlStatus = 1
AND     pc.ValidFlg = 1
AND     pc.FixedDate <= :FixedDate
AND     IFNULL(ao.ExtraPayType, 0) <> 1
GROUP BY
        oem.OemId
    ,   e.EnterpriseId
EOQ;
    }

    /**
     * OEM請求作成用ベースSQL取得
     * @return string ベースSQL
     *
     * @see 一部設計ﾊﾞｸﾞが合ったので、現行機能しているSQLで取得できるﾃﾞｰﾀに近いﾃﾞｰﾀが取得できるように修正。
     *
     */
    protected function getOemClaimedSql()
    {
        return <<<EOQ
SELECT  OEC.OemId                                                                                                                                           /* OEMID */
    ,   MAX(OEC.FixedMonth) AS FixedMonth                                                                                                                   /* 月度 */
    ,   MAX(OEC.ProcessDate) AS ProcessDate                                                                                                                 /* OEM締め処理日 */
    ,   MAX(OEC.SpanFrom) AS SpanFrom                                                                                                                       /* 対象期間FROM */
    ,   MAX(OEC.SpanTo) AS SpanTo                                                                                                                           /* 対象期間TO */
    ,   SUM(OEC.OrderCount) AS OrderCountSum                                                                                                                /* 注文総数 */
    ,   SUM(OEC.UseAmount) AS UseAmountSum                                                                                                                  /* 利用総額（注文総額） */
    ,   SUM(OEC.CB_MonthlyFee) AS CB_MonthlyFeeSum                                                                                                          /* CB利益ー月額固定費 */
    ,   SUM(CASE WHEN OEC.AppPlan = 11 THEN IFNULL(OEC.CB_SettlementCount, 0) ELSE 0 END) AS CB_SettlementCountSum11                                        /* CB利益ー決済件数（リスクフリー） */
    ,   SUM(CASE WHEN OEC.AppPlan = 11 THEN IFNULL(OEC.CB_SettlementFee, 0) ELSE 0 END) AS CB_SettlementFeeSum11                                            /* CB利益ー決済手数料（リスクフリー） */
    ,   SUM(CASE WHEN OEC.AppPlan = 21 THEN IFNULL(OEC.CB_SettlementCount, 0) ELSE 0 END) AS CB_SettlementCountSum21                                        /* CB利益ー決済件数（スタンダード） */
    ,   SUM(CASE WHEN OEC.AppPlan = 21 THEN IFNULL(OEC.CB_SettlementFee, 0) ELSE 0 END) AS CB_SettlementFeeSum21                                            /* CB利益ー決済手数料（リスクフリー） */
    ,   SUM(CASE WHEN OEC.AppPlan = 31 THEN IFNULL(OEC.CB_SettlementCount, 0) ELSE 0 END) AS CB_SettlementCountSum31                                        /* CB利益ー決済件数（エキスパート） */
    ,   SUM(CASE WHEN OEC.AppPlan = 31 THEN IFNULL(OEC.CB_SettlementFee, 0) ELSE 0 END) AS CB_SettlementFeeSum31                                            /* CB利益ー決済手数料（エキスパート） */
    ,   SUM(CASE WHEN OEC.AppPlan = 41 THEN IFNULL(OEC.CB_SettlementCount, 0) ELSE 0 END) AS CB_SettlementCountSum41                                        /* CB利益ー決済件数（スペシャル） */
    ,   SUM(CASE WHEN OEC.AppPlan = 41 THEN IFNULL(OEC.CB_SettlementFee, 0) ELSE 0 END) AS CB_SettlementFeeSum41                                            /* CB利益ー決済手数料（スペシャル） */
    ,   SUM(OEC.CB_SettlementCount) AS CB_SettlementCountSum                                                                                                /* CB利益ー決済件数（合計） */
    ,   SUM(OEC.CB_SettlementFee) AS CB_SettlementFeeSum                                                                                                    /* CB利益ー決済手数料（合計） */
    ,   SUM(OEC.CB_ClaimCountBS) AS CB_ClaimCountBSSum                                                                                                      /* CB利益ー請求件数（別送） */
    ,   SUM(OEC.CB_ClaimFeeBS) AS CB_ClaimFeeBSSum                                                                                                          /* CB利益ー請求手数料（別送） */
    ,   SUM(OEC.CB_ClaimCountDK) AS CB_ClaimCountDKSum                                                                                                      /* CB利益ー請求件数（同梱） */
    ,   SUM(OEC.CB_ClaimFeeDK) AS CB_ClaimFeeDKSum                                                                                                          /* CB利益ー請求手数料（同梱） */
    ,   SUM(CASE WHEN OEC.AppPlan = 11 AND IFNULL(OEC.CB_EntMonthlyFee, 0) > 0 THEN 1 ELSE 0 END) AS CB_EntMonthlyCountSum11                                /* CB利益ー店舗月額-リスクフリー件数 */
    ,   SUM(CASE WHEN OEC.AppPlan = 11 AND IFNULL(OEC.CB_EntMonthlyFee, 0) > 0 THEN IFNULL(OEC.CB_EntMonthlyFee, 0) ELSE 0 END) AS CB_EntMonthlyFeeSum11    /* CB利益ー店舗月額-リスクフリー固定費 */
    ,   SUM(CASE WHEN OEC.AppPlan = 21 AND IFNULL(OEC.CB_EntMonthlyFee, 0) > 0 THEN 1 ELSE 0 END) AS CB_EntMonthlyCountSum21                                /* CB利益ー店舗月額-スタンダード件数 */
    ,   SUM(CASE WHEN OEC.AppPlan = 21 AND IFNULL(OEC.CB_EntMonthlyFee, 0) > 0 THEN IFNULL(OEC.CB_EntMonthlyFee, 0) ELSE 0 END) AS CB_EntMonthlyFeeSum21    /* CB利益ー店舗月額-スタンダード固定費 */
    ,   SUM(CASE WHEN OEC.AppPlan = 31 AND IFNULL(OEC.CB_EntMonthlyFee, 0) > 0 THEN 1 ELSE 0 END) AS CB_EntMonthlyCountSum31                                /* CB利益ー店舗月額-エキスパート件数 */
    ,   SUM(CASE WHEN OEC.AppPlan = 31 AND IFNULL(OEC.CB_EntMonthlyFee, 0) > 0 THEN IFNULL(OEC.CB_EntMonthlyFee, 0) ELSE 0 END) AS CB_EntMonthlyFeeSum31    /* CB利益ー店舗月額-エキスパート固定費 */
    ,   SUM(CASE WHEN OEC.AppPlan = 41 AND IFNULL(OEC.CB_EntMonthlyFee, 0) > 0 THEN 1 ELSE 0 END) AS CB_EntMonthlyCountSum41                                /* CB利益ー店舗月額-スペシャル件数 */
    ,   SUM(CASE WHEN OEC.AppPlan = 41 AND IFNULL(OEC.CB_EntMonthlyFee, 0) > 0 THEN IFNULL(OEC.CB_EntMonthlyFee, 0) ELSE 0 END) AS CB_EntMonthlyFeeSum41    /* CB利益ー店舗月額-スペシャル固定費 */
    ,   SUM(CASE WHEN OEC.CB_EntMonthlyFee > 0 THEN 1 ELSE 0 END) AS CB_EntMonthlyCountSum                                                                  /* CB利益ー店舗月額-件数合計 */
    ,   SUM(OEC.CB_EntMonthlyFee) AS CB_EntMonthlyFeeSum                                                                                                    /* CB利益ー店舗月額-固定費合計 */
    ,   SUM(OEC.CB_AdjustmentAmount) AS CB_AdjustmentAmountSum                                                                                              /* CB利益ー調整額 */
    ,   SUM(OEC.CB_ClaimTotal) AS CB_ClaimTotalSum                                                                                                          /* CB利益ー請求総額 */
    ,   (SELECT COUNT(*) FROM T_Enterprise WHERE OemId = OEC.OemId) AS OM_ShopTotal                                                                         /* OEM登録店舗数 */
    ,   COUNT(*) AS OM_SettleShopTotal                                                                                                                      /* OEM精算店舗数 */
    ,   SUM(CASE WHEN OEC.AppPlan = 11 THEN IFNULL(OEC.OM_SettlementCount, 0) ELSE 0 END) AS OM_SettlementCountSum11                                        /* OEM利益ー決済件数（リスクフリー） */
    ,   SUM(CASE WHEN OEC.AppPlan = 11 THEN IFNULL(OEC.OM_SettlementFee, 0) ELSE 0 END) AS OM_SettlementFeeSum11                                            /* OEM利益ー決済手数料（リスクフリー） */
    ,   SUM(CASE WHEN OEC.AppPlan = 21 THEN IFNULL(OEC.OM_SettlementCount, 0) ELSE 0 END) AS OM_SettlementCountSum21                                        /* OEM利益ー決済件数（スタンダード） */
    ,   SUM(CASE WHEN OEC.AppPlan = 21 THEN IFNULL(OEC.OM_SettlementFee, 0) ELSE 0 END) AS OM_SettlementFeeSum21                                            /* OEM利益ー決済手数料（スタンダード） */
    ,   SUM(CASE WHEN OEC.AppPlan = 31 THEN IFNULL(OEC.OM_SettlementCount, 0) ELSE 0 END) AS OM_SettlementCountSum31                                        /* OEM利益ー決済件数（エキスパート） */
    ,   SUM(CASE WHEN OEC.AppPlan = 31 THEN IFNULL(OEC.OM_SettlementFee, 0) ELSE 0 END) AS OM_SettlementFeeSum31                                            /* OEM利益ー決済手数料（エキスパート） */
    ,   SUM(CASE WHEN OEC.AppPlan = 41 THEN IFNULL(OEC.OM_SettlementCount, 0) ELSE 0 END) AS OM_SettlementCountSum41                                        /* OEM利益ー決済件数（スペシャル） */
    ,   SUM(CASE WHEN OEC.AppPlan = 41 THEN IFNULL(OEC.OM_SettlementFee, 0) ELSE 0 END) AS OM_SettlementFeeSum41                                            /* OEM利益ー決済手数料（スペシャル） */
    ,   SUM(OEC.OM_SettlementCount) AS OM_SettlementCountSum                                                                                                /* OEM利益ー決済件数（合計） */
    ,   SUM(OEC.OM_SettlementFee) AS OM_SettlementFeeSum                                                                                                    /* OEM利益ー決済手数料（合計） */
    ,   SUM(OEC.OM_ClaimCountBS) AS OM_ClaimCountBSSum                                                                                                      /* OEM利益ー請求件数（別送） */
    ,   SUM(OEC.OM_ClaimFeeBS) AS OM_ClaimFeeBSSum                                                                                                          /* OEM利益ー請求手数料（別送） */
    ,   SUM(OEC.OM_ClaimCountDK) AS OM_ClaimCountDKSum                                                                                                      /* OEM利益ー請求件数（同梱） */
    ,   SUM(OEC.OM_ClaimFeeDK) AS OM_ClaimFeeDKSum                                                                                                          /* OEM利益ー請求手数料（同梱） */
    ,   SUM(CASE WHEN OEC.AppPlan = 11 AND IFNULL(OEC.CB_EntMonthlyFee, 0) > 0 THEN 1 ELSE 0 END) AS OM_EntMonthlyCountSum11                                /* OEM利益ー店舗月額-リスクフリー件数 */
    ,   SUM(CASE WHEN OEC.AppPlan = 11 AND IFNULL(OEC.CB_EntMonthlyFee, 0) > 0 THEN IFNULL(OEC.OM_EntMonthlyFee, 0) ELSE 0 END) AS OM_EntMonthlyFeeSum11    /* OEM利益ー店舗月額-リスクフリー固定費 */
    ,   SUM(CASE WHEN OEC.AppPlan = 21 AND IFNULL(OEC.CB_EntMonthlyFee, 0) > 0 THEN 1 ELSE 0 END) AS OM_EntMonthlyCountSum21                                /* OEM利益ー店舗月額-スタンダード件数 */
    ,   SUM(CASE WHEN OEC.AppPlan = 21 AND IFNULL(OEC.CB_EntMonthlyFee, 0) > 0 THEN IFNULL(OEC.OM_EntMonthlyFee, 0) ELSE 0 END) AS OM_EntMonthlyFeeSum21    /* OEM利益ー店舗月額-スタンダード固定費 */
    ,   SUM(CASE WHEN OEC.AppPlan = 31 AND IFNULL(OEC.CB_EntMonthlyFee, 0) > 0 THEN 1 ELSE 0 END) AS OM_EntMonthlyCountSum31                                /* OEM利益ー店舗月額-エキスパート件数 */
    ,   SUM(CASE WHEN OEC.AppPlan = 31 AND IFNULL(OEC.CB_EntMonthlyFee, 0) > 0 THEN IFNULL(OEC.OM_EntMonthlyFee, 0) ELSE 0 END) AS OM_EntMonthlyFeeSum31    /* OEM利益ー店舗月額-エキスパート固定費 */
    ,   SUM(CASE WHEN OEC.AppPlan = 41 AND IFNULL(OEC.CB_EntMonthlyFee, 0) > 0 THEN 1 ELSE 0 END) AS OM_EntMonthlyCountSum41                                /* OEM利益ー店舗月額-スペシャル件数 */
    ,   SUM(CASE WHEN OEC.AppPlan = 41 AND IFNULL(OEC.CB_EntMonthlyFee, 0) > 0 THEN IFNULL(OEC.OM_EntMonthlyFee, 0) ELSE 0 END) AS OM_EntMonthlyFeeSum41    /* OEM利益ー店舗月額-スペシャル固定費 */
    ,   SUM(CASE WHEN OEC.OM_EntMonthlyFee > 0 THEN 1 ELSE 0 END) AS OM_EntMonthlyCountSum                                                                  /* OEM利益ー店舗月額-件数合計 */
    ,   SUM(OEC.OM_EntMonthlyFee) AS OM_EntMonthlyFeeSum                                                                                                    /* OEM利益ー店舗月額-固定費合計 */
    ,   SUM(OEC.OM_AdjustmentAmount) AS OM_AdjustmentAmountSum                                                                                              /* OEM利益ー調整額 */
    ,   SUM(OEC.OM_TotalProfit) AS OM_TotalProfitSum                                                                                                        /* OEM利益ー利益総額 */
    ,   SUM(OEC.CR_TotalAmount) AS CR_TotalAmountSum                                                                                                        /* キャンセル返金 */
    ,   SUM(OEC.CR_OemAmount) AS CR_OemAmountSum                                                                                                            /* キャンセル返金ーOEM返金分 */
    ,   SUM(OEC.CR_EntAmount) AS CR_EntAmountSum                                                                                                            /* キャンセル返金ー加盟店返金分 */
    ,   OEC.PayingMethod                                                                                                                                    /* 立替方法 */
    ,   SUM(OEC.PC_CarryOver) AS PC_CarryOverSum                                                                                                            /* 立替ー繰越 */
    ,   SUM(OEC.PC_ChargeCount) AS PC_ChargeCountSum                                                                                                        /* 立替ー立替注文件数 */
    ,   SUM(OEC.PC_ChargeAmount) AS PC_ChargeAmountSum                                                                                                      /* 立替ー立替金額 */
    ,   SUM(OEC.PC_SettlementFee) AS PC_SettlementFeeSum                                                                                                    /* 立替ー決済手数料 */
    ,   SUM(OEC.PC_ClaimFee) AS PC_ClaimFeeSum                                                                                                              /* 立替ー請求手数料 */
    ,   SUM(OEC.PC_CancelCount) AS PC_CancelCountSum                                                                                                        /* 立替ーキャンセル件数 */
    ,   SUM(OEC.PC_CalcelAmount) AS PC_CalcelAmountSum                                                                                                      /* 立替ーキャンセル精算金額 */
    ,   SUM(OEC.PC_StampFeeCount) AS PC_StampFeeCountSum                                                                                                    /* 立替ー印紙代発生件数 */
    ,   SUM(OEC.PC_StampFeeTotal) AS PC_StampFeeTotalSum                                                                                                    /* 立替ー印紙代精算金額 */
    ,   SUM(OEC.PC_MonthlyFee) AS PC_MonthlyFeeSum                                                                                                          /* 立替ー月額固定費 */
    ,   SUM(OEC.PC_TransferCommission) AS PC_TransferCommissionSum                                                                                          /* 立替ー振込手数料 */
    ,   SUM(OEC.PC_DecisionPayment) AS PC_DecisionPaymentSum                                                                                                /* 立替ー振込確定金額 */
    ,   SUM(OEC.PC_AdjustmentAmount) AS PC_AdjustmentAmountSum                                                                                              /* 立替ー調整額 */
    ,   SUM(OEC.FixedTransferAmount) AS FixedTransferAmountSum                                                                                              /* 確定振込額 */
    ,   SUM(OEC.PayBackCount) AS PayBackCountSum                                                                                                            /* 立替精算戻し件数 */
    ,   SUM(OEC.PayBackAmount) AS PayBackAmountSum                                                                                                          /* 立替精算戻し金額 */
    ,   SUM(OEC.AgencyFee) AS AgencyFeeSum                                                                                                                  /* 代理店手数料 */
-- -------------------------
-- CB_SettlementCountPlan
-- -------------------------
    ,   (
         SELECT CONCAT('[',
                    GROUP_CONCAT(CAST(CONCAT('{', TEMP.AppPlan, ':\'', TEMP.CB_SettlementCount) AS char), '\'}'),
                      ']')
         FROM   (
                 SELECT W.AppPlan, SUM(W.CB_SettlementCount) AS CB_SettlementCount
                 FROM T_OemEnterpriseClaimed W
                 WHERE W.OemId = :OemId
                 AND   W.OemClaimedSeq is null
                 GROUP BY W.AppPlan
                ) TEMP
        ) AS CB_SettlementCountPlan
-- -------------------------
-- CB_SettlementFeePlan
-- -------------------------
    ,   (
         SELECT CONCAT('[',
                    GROUP_CONCAT(CAST(CONCAT('{', TEMP.AppPlan, ':\'', TEMP.CB_SettlementFee) AS char), '\'}'),
                      ']')
         FROM   (
                 SELECT W.AppPlan, SUM(W.CB_SettlementFee) AS CB_SettlementFee
                 FROM T_OemEnterpriseClaimed W
                 WHERE W.OemId = :OemId
                 AND   W.OemClaimedSeq is null
                 GROUP BY W.AppPlan
                ) TEMP
        ) AS CB_SettlementFeePlan
-- -------------------------
-- CB_EntMonthlyCountPlan
-- -------------------------
    ,   (
         SELECT CONCAT('[',
                    GROUP_CONCAT(CAST(CONCAT('{', TEMP.AppPlan, ':\'', TEMP.CB_SettlementCount) AS char), '\'}'),
                      ']')
         FROM   (
                 SELECT W.AppPlan, SUM(W.CB_SettlementCount) AS CB_SettlementCount
                 FROM T_OemEnterpriseClaimed W
                 WHERE W.OemId = :OemId
                 AND   W.OemClaimedSeq is null
                 GROUP BY W.AppPlan
                ) TEMP
        ) AS CB_EntMonthlyCountPlan
-- -------------------------
-- CB_EntMonthlyFeePlan
-- -------------------------
    ,   (
         SELECT CONCAT('[',
                    GROUP_CONCAT(CAST(CONCAT('{', TEMP.AppPlan, ':\'', TEMP.CB_EntMonthlyFee) AS char), '\'}'),
                      ']')
         FROM   (
                 SELECT W.AppPlan, SUM(W.CB_EntMonthlyFee) AS CB_EntMonthlyFee
                 FROM T_OemEnterpriseClaimed W
                 WHERE W.OemId = :OemId
                 AND   W.OemClaimedSeq is null
                 GROUP BY W.AppPlan
             ) TEMP
        ) AS CB_EntMonthlyFeePlan
-- -------------------------
-- OM_SettlementCountPlan
-- -------------------------
    ,   (
         SELECT CONCAT('[',
                    GROUP_CONCAT(CAST(CONCAT('{', TEMP.AppPlan, ':\'', TEMP.OM_SettlementCount) AS char), '\'}'),
                      ']')
         FROM   (
                 SELECT W.AppPlan, SUM(W.OM_SettlementCount) AS OM_SettlementCount
                 FROM T_OemEnterpriseClaimed W
                 WHERE W.OemId = :OemId
                 AND   W.OemClaimedSeq is null
                 GROUP BY W.AppPlan
                ) TEMP
        ) AS OM_SettlementCountPlan
-- -------------------------
-- OM_SettlementFeePlan
-- -------------------------
    ,   (
         SELECT CONCAT('[',
                    GROUP_CONCAT(CAST(CONCAT('{', TEMP.AppPlan, ':\'', TEMP.OM_SettlementFee) AS char), '\'}'),
                      ']')
         FROM   (
                 SELECT W.AppPlan, SUM(W.OM_SettlementFee) AS OM_SettlementFee
                 FROM T_OemEnterpriseClaimed W
                 WHERE W.OemId = :OemId
                 AND   W.OemClaimedSeq is null
                 GROUP BY W.AppPlan
                ) TEMP
        ) AS OM_SettlementFeePlan
-- -------------------------
-- OM_EntMonthlyCountPlan
-- -------------------------
    ,   (
         SELECT CONCAT('[',
                    GROUP_CONCAT(CAST(CONCAT('{', TEMP.AppPlan, ':\'', TEMP.OM_SettlementCount) AS char), '\'}'),
                      ']')
         FROM   (
                 SELECT W.AppPlan, SUM(W.OM_SettlementCount) AS OM_SettlementCount
                 FROM T_OemEnterpriseClaimed W
                 WHERE W.OemId = :OemId
                 AND   W.OemClaimedSeq is null
                 GROUP BY W.AppPlan
                ) TEMP
        ) AS OM_EntMonthlyCountPlan
-- -------------------------
-- OM_EntMonthlyFeePlan
-- -------------------------
    ,   (
         SELECT CONCAT('[',
                    GROUP_CONCAT(CAST(CONCAT('{', TEMP.AppPlan, ':\'', TEMP.OM_EntMonthlyFee) AS char), '\'}'),
                      ']')
         FROM   (
                 SELECT W.AppPlan, SUM(W.OM_EntMonthlyFee) AS OM_EntMonthlyFee
                 FROM T_OemEnterpriseClaimed W
                 WHERE W.OemId = :OemId
                 AND   W.OemClaimedSeq is null
                 GROUP BY W.AppPlan
                ) TEMP
        ) AS OM_EntMonthlyFeePlan
FROM    T_OemEnterpriseClaimed OEC
WHERE   OEC.OemId = :OemId
AND     OEC.OemClaimedSeq is null
GROUP BY
        OEC.OemId
EOQ;
    }
// 2015/10/16 Y.Suzuki 加盟店集計情報取得SQLを見直し、ﾒｿｯﾄﾞ化 Add End

    /**
     * 債権明細関連初期化処理
     * 20151103 : 指定の月度のみをクリアする処理に変更
     */
    private function initializeOemBadDebt($fixedMonth) {
        $query = <<<EOQ
SELECT  T_OemBadDebt.Seq
FROM    T_OemBadDebt
WHERE   1 = 1
AND     T_OemBadDebt.FixedMonth = :FixedMonth
EOQ;

        $prm = array(
            ':FixedMonth' => $fixedMonth,
        );
        $order = new TableOrder ( $this->db );
        $obd = new TableOemBadDebt ( $this->db );
        foreach ( $this->db->query ( $query )->execute ($prm) as $row ) {
            $seq = $row ["Seq"];

            // 注文情報を更新
            $data = array (
                    'OemBadDebtSeq' => null,
                    'OemBadDebtType' => null,
                    'UpdateId' => $this->_userId
            );
            $condition = array (
                   'OemBadDebtSeq'=> $seq,
            );
            $order->saveUpdateWhere ( $data, $condition );

            $obd->deleteBySeq ( $seq );
        }
    }

    /**
     * OEM請求関連初期化処理
     *
     * @return mixed
     */
    private function initializeOemClaimed() {
        $query = <<<EOQ
SELECT  T_OemClaimed.OemClaimedSeq,
        T_OemClaimed.OemId
FROM    T_OemClaimed
WHERE   T_OemClaimed.PayingControlStatus = 0
EOQ;

        $oec = new TableOemEnterpriseClaimed ( $this->db );
        $osf = new TableOemSettlementFee ( $this->db );
        $ocf = new TableOemClaimFee ( $this->db );
        $oaf = new TableOemAgencyFee ( $this->db );
        $pc = new TablePayingControl ( $this->db );
        $oaa = new TableOemAdjustmentAmount ( $this->db );

        $result = array ();

        foreach ( $this->db->query ( $query )->execute () as $row ) {
            $seq = $row ["OemClaimedSeq"];
            $oemId = $row ["OemId"];

            // OEM加盟店請求データを削除
            $oec->deleteBySeq ( $seq );

            $data = array (
                    'AddUpFlg' => 0,
                    'AddUpFixedMonth' => null,
                    'OemClaimedSeq' => null,
                    'UpdateId' => $this->_userId
            );

            $condition = array ('OemClaimedSeq' => $seq );

            // OEM決済手数料データを更新
            $osf->saveUpdateWhere ( $data, $condition );
            // OEM請求手数料データを更新
            $ocf->saveUpdateWhere ( $data, $condition );
            // OEM代理店手数料データを更新
            $oaf->saveUpdateWhere ( $data, $condition );

            $data = array (
                    'OemClaimedAddUpFlg' => 0,
                    'OemClaimedSeq' => null,
                    'UpdateId' => $this->_userId
            );

            // 立替振込管理データを更新
            $pc->saveUpdateWhere ( $data, $condition );

            // OEM調整額管理の配列保存
            foreach ( $oaa->findBySeq ( $seq ) as $row2 ) {
                $result [$oemId][] = $row2;
            }
            // OEM調整額管理データを削除
            $oaa->deleteBySeq ( $seq );
        }
        return $result;
    }

    /**
     * OEM債権移管処理
     *
     * @return string '':成功 ''以外:失敗
     */
    public function oemClaimTrans() {
        try {
            $this->db->getDriver ()->getConnection ()->beginTransaction ();

            $mdlu = new TableUser ( $this->db );
            $mdloe = new TableOem ( $this->db );
            $mdlor = new TableOrder ( $this->db );
            // 処理用ユーザIDを取得
            $userId = $mdlu->getUserId ( TableUser::USERCLASS_SYSTEM, TableUser::SEQ_BATCH_USER );

            $sql = <<<EOQ
SELECT	O.OrderSeq
FROM	T_Order O
INNER JOIN T_ClaimControl C
ON		O.OrderSeq = C.OrderSeq
INNER JOIN T_PayingAndSales PAS
ON      PAS.OrderSeq = O.OrderSeq
WHERE	O.DataStatus IN (51, 61)
AND		O.ValidFlg = 1
AND		O.OemClaimTransDate is NULL
AND     DATE_ADD(C.F_ClaimDate, INTERVAL :claimTransDays DAY) < CURRENT_DATE
AND     O.OemId = :oemId
AND     PAS.ClearConditionForCharge = 1;
EOQ;
            $currentDate = date('Y-m-d');
            //OEM処理対象を抽出
            foreach ($mdloe->getClaimTrans() as $row) {
                $wkClaimTransDays = $row['OemClaimTransDays'];
                $wkOemId = $row['OemId'];
                if (isset($wkClaimTransDays)) {
                    //処理対象の注文Seqを抽出
                    $ri = $this->db->query($sql)->execute(array(
                            ':claimTransDays' => $wkClaimTransDays,
                            ':oemId' => $wkOemId
                    ));

                    foreach($ri as $row2) {
                        $wkOrderSeq = $row2['OrderSeq'];
                        if (isset($wkOrderSeq)) {
                            //注文テーブルを更新
                            $mdlor->saveUpdate(array(
                                    'OemClaimTransDate' => $currentDate,
                                    'OemClaimTransFlg' => 0,
                                    'UpdateId' => $userId
                            ), $wkOrderSeq);
                            // 注文履歴へ登録
                            $history = new CoralHistoryOrder($this->db);
                            $history->InsOrderHistory($wkOrderSeq, 101, $userId);
                        }
                    }
                }
            }
            $this->db->getDriver ()->getConnection ()->commit ();
            return "";
        } catch ( Exception $e ) {
            $this->db->getDriver ()->getConnection ()->rollBack ();
            throw $e;
        }
    }

    /**
     * 指定年月、指定日の日付をyyyy-mm-ddの形式に変換する。
     * 存在しない日付になる場合、月末に編集する。
     * @param string $ym 年月 yyyy-mm形式
     * @param int $day 日付
     */
    private function makeDate($ym, $day) {
        // 指定年月の月末日
        $lastDay = date('Y-m-t', strtotime($ym));

        // そのまま繋げる
        $ymd = sprintf('%s-%02d', $ym, $day);

        // そのまま繋げた日付が指定年月の月末日を超えている場合、月末日を使用
        if (strcmp($lastDay, $ymd) < 0) {
            return $lastDay;
        }
        else {
            return $ymd;
        }
    }

    /**
     * 指定OEM、月度のOEM締めデータが存在するか算出する
     * @param OEMID $oemId
     * @param 締め月度 $fixedMonth
     * @return boolean
     */
    private function isOemClaimedByFixedMonth($oemId, $fixedMonth) {
        // 締め月のOEM締めデータを算出する
        // ここでは、本締め済みか否かは判断しない
        $sql = <<<EOQ
SELECT  COUNT(*) AS cnt
FROM    T_OemClaimed
WHERE   OemId = :OemId
AND     FixedMonth = :FixedMonth
AND     ValidFlg = 1
EOQ;

        $prm = array(
            ':OemId' => $oemId,
            ':FixedMonth' => $fixedMonth,
        );
        $ri = $this->db->query ( $sql )->execute ($prm);
        $cnt = $ri->current()['cnt'];
        return $cnt > 0 ;
    }

}
