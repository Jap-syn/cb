<?php
namespace oemmember\Controller;

use oemmember\Application;
use Coral\Base\BaseGeneralUtils;
use Coral\Coral\Controller\CoralControllerAction;
use Zend\Db\Adapter\Adapter;
use models\Table\TableEnterprise;
use Zend\Db\ResultSet\ResultSet;
use Zend\Config\Reader\Ini;
use Coral\Base\IO\BaseIOCsvWriter;
use models\Logic\LogicTemplate;
use DOMPDFModule\View\Model\PdfModel;

class ClaimController extends CoralControllerAction {

 	const RESULT_CSV_PREFIX = 'meisai';

	protected $_componentRoot = './application/views/components';

	/**
	 * アプリケーションオブジェクト
	 * @var Application
	 */
	private $app;

	protected function _init()
	{
        $this->app = Application::getInstance();

        // メニュー情報をマスタから取得
        $menu_info = $this->app->getMenuInfo();

        foreach( $menu_info as $key => $info ) {
            $this->view->assign( $key, $info );
        }

        $this->addStyleSheet( '../../oemmember/css/members.css' )
            ->addStyleSheet( '../../css/base.ui.tableex.css' )
            ->addStyleSheet( '../../oemmember/css/tab_support.css' )
            ->addStyleSheet( '../../oemmember/css/claim.css' )
            ->addStyleSheet( '../../oemmember/css/monthly.css' )   /* confirmNews対応(20150401_1330) */
            ->addJavaScript( '../../js/prototype.js' )
            ->addJavaScript( '../../js/bytefx.js' )
            ->addJavaScript( '../../js/corelib.js' )
            ->addJavaScript( '../../js/base.ui.js' )
            ->addJavaScript( '../../js/base.ui.tableex.js' );

            $this->view->assign('cssName', "claim" );
            
        return $this->view;
	}

	/**
	 * indexアクション
	 */
	public function indexAction()
	{
        $entId = $this->app->authManager->getUserInfo()->EnterpriseId;

        // 立替締め日リストを取得
        $dateList = $this->getFixedDateList($entId);

        $params = array_merge(
            array(
                'fixedDate' => $dateList[0]['FixedDate'],
                'dateList' => $dateList,
                'enterprise' => $this->app->authManager->getUserInfo()
            ),
            $this->getPureParams()
        );
        $fixedDate = null;
        if (IsValidDate($params['fixedDate'])) {
            $fixedDate = date('Y-m-d', strtotime($params['fixedDate']));
        }
        $execScheduleDate = null;
        $dateListCount = 0;
        if (!empty($dateList)) {
            $dateListCount = count($dateList);
        }
        for ($i=0; $i<$dateListCount; $i++) {
            if ($dateList[$i]['FixedDate'] == $fixedDate) {
                $execScheduleDate = $dateList[$i]['ExecScheduleDate'];
                break;
            }
        }
        $this->setPageTitle( 'ご利用明細（締め日別） ' . f_df($fixedDate, 'Y/m/d') . ' (' . f_df($execScheduleDate, 'y/m/d') . ' 支払分)' );

        //--------------------------
        // 各種データ取得
        //--------------------------
        $prm = array(':EnterpriseId' => $entId, ':FixedDate' => $params['fixedDate']);

        // ①請求書兼領収書：oemmember/claim/summary.php
        $sql = <<<Q_END
SELECT CONCAT(DATE_FORMAT(pc.FixedDate, '%Y/%m/%d'), '(', DATE_FORMAT(pc.ExecScheduleDate, '%y/%m/%d'), ' 支払分)') AS FixedExecDate
,      pc.DecisionDate
,      pc.ExecScheduleDate
,      pc.ChargeCount
,      (
         pc.ChargeAmount +
         pc.SettlementFee +
         pc.ClaimFee
       ) AS ChargeAmount
,      pc.SettlementFee
,      pc.ClaimFee
,      pc.StampFeeTotal
,      pc.MonthlyFee
,      pc.CarryOver
,      pc.CalcelAmount
,      pc.TransferCommission
,      pc.PayBackAmount
,      pc.AdjustmentAmount
,      -1 *
       (
         (-1 * pc.SettlementFee) +
         (-1 * pc.ClaimFee) +
         (-1 * pc.StampFeeTotal) +
         (-1 * pc.MonthlyFee) +
         pc.CarryOver +
         pc.CalcelAmount +
         (-1 * pc.TransferCommission) +
         pc.PayBackAmount +
         pc.AdjustmentAmount
       ) AS ClaimAmount
,      (
         pc.ChargeAmount +
         pc.SettlementFee +
         pc.ClaimFee +
         (-1 * pc.SettlementFee) +
         (-1 * pc.ClaimFee) +
         (-1 * pc.StampFeeTotal) +
         (-1 * pc.MonthlyFee) +
         pc.CarryOver +
         pc.CalcelAmount +
         (-1 * pc.TransferCommission) +
         pc.PayBackAmount +
         pc.AdjustmentAmount
       ) AS PaymentAmount
/* 以下、クレジット決済関連 */
,      (SELECT COUNT(ao.OrderSeq)
        FROM   T_PayingAndSales pas
               INNER JOIN T_Order o ON (o.OrderSeq = pas.OrderSeq)
               INNER JOIN AT_Order ao ON (ao.OrderSeq = o.OrderSeq)
        WHERE  pas.PayingControlSeq = pc.Seq
        AND    ao.ExtraPayType = 1
        AND    o.ClaimSendingClass = 11
       ) AS ChargeCountExtraPay1DK
,      (SELECT COUNT(ao.OrderSeq)
        FROM   T_PayingAndSales pas
               INNER JOIN T_Order o ON (o.OrderSeq = pas.OrderSeq)
               INNER JOIN AT_Order ao ON (ao.OrderSeq = o.OrderSeq)
        WHERE  pas.PayingControlSeq = pc.Seq
        AND    ao.ExtraPayType = 1
        AND    o.ClaimSendingClass <> 11
       ) AS ChargeCountExtraPay1BS
,      (SELECT SUM(pas.SettlementFee)
        FROM   T_PayingAndSales pas
               INNER JOIN T_Order o ON (o.OrderSeq = pas.OrderSeq)
               INNER JOIN AT_Order ao ON (ao.OrderSeq = o.OrderSeq)
        WHERE  pas.PayingControlSeq = pc.Seq
        AND    ao.ExtraPayType = 1
       ) AS SettlementFeeExtraPay1
,      (SELECT SUM(pas.ClaimFee)
        FROM   T_PayingAndSales pas
               INNER JOIN T_Order o ON (o.OrderSeq = pas.OrderSeq)
               INNER JOIN AT_Order ao ON (ao.OrderSeq = o.OrderSeq)
        WHERE  pas.PayingControlSeq = pc.Seq
        AND    ao.ExtraPayType = 1
        AND    o.ClaimSendingClass = 11
       ) AS ClaimFeeExtraPay1DK
,      (SELECT SUM(pas.ClaimFee)
        FROM   T_PayingAndSales pas
               INNER JOIN T_Order o ON (o.OrderSeq = pas.OrderSeq)
               INNER JOIN AT_Order ao ON (ao.OrderSeq = o.OrderSeq)
        WHERE  pas.PayingControlSeq = pc.Seq
        AND    ao.ExtraPayType = 1
        AND    o.ClaimSendingClass <> 11
       ) AS ClaimFeeExtraPay1BS
,      (SELECT SUM(can.RepayTotal)
        FROM   T_PayingAndSales pas
               INNER JOIN T_Order o ON (o.OrderSeq = pas.OrderSeq)
               INNER JOIN AT_Order ao ON (ao.OrderSeq = o.OrderSeq)
               INNER JOIN T_Cancel can ON (o.OrderSeq = can.OrderSeq)
        WHERE  pas.PayingControlSeq = pc.Seq
        AND    ao.ExtraPayType = 1
        AND    o.ClaimSendingClass = 11
       ) AS CalcelAmountExtraPay1DK
,      (SELECT SUM(can.RepayTotal)
        FROM   T_PayingAndSales pas
               INNER JOIN T_Order o ON (o.OrderSeq = pas.OrderSeq)
               INNER JOIN AT_Order ao ON (ao.OrderSeq = o.OrderSeq)
               INNER JOIN T_Cancel can ON (o.OrderSeq = can.OrderSeq)
        WHERE  pas.PayingControlSeq = pc.Seq
        AND    ao.ExtraPayType = 1
        AND    o.ClaimSendingClass <> 11
       ) AS CalcelAmountExtraPay1BS
        FROM   T_PayingControl pc
WHERE  1 = 1
AND    pc.EnterpriseId = :EnterpriseId
AND    pc.FixedDate = :FixedDate
Q_END;
        $ri = $this->app->dbAdapter->query($sql)->execute($prm);
        $params['summary'] = $ri->current();


        // ②お取引明細：oemmember/claim/charge_list.php
        // SettlementFee,ClaimFee,StampFeeはﾏｲﾅｽ-1をかけた値で取得する(20150629_1430)
        $sql = <<<Q_END
SELECT s.SiteId
,      s.SiteNameKj
,      '' AS No
,      o.OrderId
,      o.OrderSeq
,      o.Ent_OrderId
,      c.NameKj
,      o.ReceiptOrderDate
,      (SELECT MAX(Deli_JournalIncDate) FROM T_OrderItems WHERE OrderSeq = o.OrderSeq) AS MaxDeliJournalIncDate
,      pc.FixedDate
,      pas.UseAmount
,      (-1 * pas.SettlementFee) AS SettlementFee
,      (-1 * pas.ClaimFee) AS ClaimFee
,      0 AS StampFee
,      (
         pas.UseAmount +
         (-1 * pas.SettlementFee) +
         (-1 * pas.ClaimFee)
       ) AS sagaku
FROM   T_PayingControl pc
       INNER JOIN T_PayingAndSales pas ON pas.PayingControlSeq = pc.Seq
       INNER JOIN T_Order o ON o.OrderSeq = pas.OrderSeq
       LEFT OUTER JOIN T_StampFee sf ON sf.OrderSeq = o.OrderSeq AND sf.ClearFlg = 1
       INNER JOIN T_Customer c ON c.OrderSeq = o.OrderSeq
       INNER JOIN T_Site s ON s.SiteId = o.SiteId
WHERE  1 = 1
AND    pc.EnterpriseId = :EnterpriseId
AND    pc.FixedDate = :FixedDate
ORDER BY SiteId, OrderId
Q_END;
        $ri = $this->app->dbAdapter->query($sql)->execute($prm);
        $params['charge_list'] = $this->_grouping(ResultInterfaceToArray($ri), 'SiteNameKj');


        // ③印紙代明細：oemmember/claim/stamp_list.php
        // StampFeeはそのまま値で取得する
        $sql = <<<Q_END
SELECT s.SiteId
,      s.SiteNameKj
,      '' AS No
,      o.OrderId
,      o.OrderSeq
,      o.Ent_OrderId
,      c.NameKj
,      o.ReceiptOrderDate
,      pc2.FixedDate
,      o.UseAmount
,      sf.StampFee AS StampFee
FROM   T_PayingControl pc
       INNER JOIN T_StampFee sf ON sf.PayingControlSeq = pc.Seq
       INNER JOIN T_Order o ON o.OrderSeq = sf.OrderSeq
       INNER JOIN T_Customer c ON c.OrderSeq = o.OrderSeq
       INNER JOIN T_Site s ON s.SiteId = o.SiteId
       LEFT OUTER JOIN T_PayingAndSales pas ON pas.Orderseq = o.OrderSeq
       LEFT OUTER JOIN T_PayingControl pc2 ON pc2.Seq = pas.PayingControlSeq
WHERE  1 = 1
AND    pc.EnterpriseId = :EnterpriseId
AND    pc.FixedDate = :FixedDate
ORDER BY SiteId, OrderId
Q_END;
        $ri = $this->app->dbAdapter->query($sql)->execute($prm);
        $params['stamp_list'] = $this->_grouping(ResultInterfaceToArray($ri), 'SiteNameKj');


        // ④キャンセル返金明細：oemmember/claim/cancel_list.php
        $sql = <<<Q_END
SELECT s.SiteId
,      s.SiteNameKj
,      '' AS No
,      o.OrderId
,      o.OrderSeq
,      o.Ent_OrderId
,      c.NameKj
,      o.ReceiptOrderDate
,      pc2.FixedDate
,      cncl.CancelDate
,      o.UseAmount
,      cncl.RepayTotal
FROM   T_PayingControl pc
       INNER JOIN T_Cancel cncl ON cncl.PayingControlSeq = pc.Seq
       INNER JOIN T_Order o ON o.OrderSeq = cncl.OrderSeq
       INNER JOIN T_Customer c ON c.OrderSeq = o.OrderSeq
       INNER JOIN T_Site s ON s.SiteId = o.SiteId
       LEFT OUTER JOIN T_PayingAndSales pas ON pas.Orderseq = o.OrderSeq
       LEFT OUTER JOIN T_PayingControl pc2 ON pc2.Seq = pas.PayingControlSeq
WHERE  1 = 1
AND    pc.EnterpriseId = :EnterpriseId
AND    pc.FixedDate = :FixedDate
AND    cncl.ValidFlg = 1
ORDER BY SiteId, OrderId
Q_END;
        $ri = $this->app->dbAdapter->query($sql)->execute($prm);
        $params['cancel_list'] = $this->_grouping(ResultInterfaceToArray($ri), 'SiteNameKj');


        // ⑤調整額内訳明細：oemmember/claim/adjustment_list.php
        $sql = <<<Q_END
SELECT '' AS No
,      pc2.FixedDate
,      aa.OrderId
,      aa.OrderSeq
,      o.Ent_OrderId
,      c.NameKj
,      o.ReceiptOrderDate
,      (SELECT KeyContent FROM M_Code WHERE CodeId = 89 AND KeyCode = aa.ItemCode) AS kamoku
,      aa.AdjustmentAmount
,      aa.SerialNumber
FROM   T_PayingControl pc
       INNER JOIN T_AdjustmentAmount aa ON aa.PayingControlSeq = pc.Seq
       INNER JOIN T_Enterprise e ON e.EnterpriseId = pc.EnterpriseId
       LEFT OUTER JOIN T_Order o ON o.OrderSeq = aa.OrderSeq
       LEFT OUTER JOIN T_Customer c ON c.OrderSeq = aa.OrderSeq
       LEFT OUTER JOIN T_PayingAndSales pas ON pas.Orderseq = o.OrderSeq
       LEFT OUTER JOIN T_PayingControl pc2 ON pc2.Seq = pas.PayingControlSeq
WHERE  1 = 1
AND    pc.EnterpriseId = :EnterpriseId
AND    pc.FixedDate = :FixedDate
ORDER BY SerialNumber
Q_END;
        $ri = $this->app->dbAdapter->query($sql)->execute($prm);
        $params['adjustment_list'] = ResultInterfaceToArray($ri);


//         // ⑥立替精算戻し明細：oemmember/claim/payback_list.list
//         $sql = <<<Q_END
// SELECT s.SiteId
// ,      s.SiteNameKj
// ,      '' AS No
// ,      o.OrderId
// ,      o.OrderSeq
// ,      o.Ent_OrderId
// ,      c.NameKj
// ,      o.ReceiptOrderDate
// ,      (SELECT MAX(Deli_JournalIncDate) FROM T_OrderItems WHERE OrderSeq = o.OrderSeq) AS MaxDeliJournalIncDate
// ,      pc2.FixedDate
// ,      pas.UseAmount
// ,      cc.ReceiptAmountTotal
// ,      pbc.PayBackAmount
// FROM   T_PayingControl pc
//        INNER JOIN T_PayingBackControl pbc ON pbc.PayingControlSeq = pc.Seq
//        INNER JOIN T_PayingAndSales pas ON pas.OrderSeq = pbc.OrderSeq
//        INNER JOIN T_Order o ON o.OrderSeq = pas.OrderSeq
//        INNER JOIN T_Customer c ON c.OrderSeq = o.OrderSeq
//        INNER JOIN T_Site s ON s.SiteId = o.SiteId
//        LEFT OUTER JOIN T_ClaimControl cc ON o.OrderSeq = cc.OrderSeq
//        LEFT OUTER JOIN T_PayingControl pc2 ON pc2.Seq = pas.PayingControlSeq
// WHERE  1 = 1
// AND    pc.EnterpriseId = :EnterpriseId
// AND    pc.FixedDate = :FixedDate
// ORDER BY SiteId, OrderId
// Q_END;
//         $ri = $this->app->dbAdapter->query($sql)->execute($prm);
//         $params['payback_list'] = $this->_grouping(ResultInterfaceToArray($ri), 'SiteNameKj');

        // (補助データ)②お取引明細／⑥立替精算戻し明細、共通
        $sql = <<<Q_END
SELECT pc.StampFeeTotal
,      IFNULL(pp.PricePlanName, '') AS PricePlanName
,      pc.MonthlyFee
,      pc.CarryOver
,      pc.CalcelAmount
,      pc.TransferCommission
,      pc.PayBackAmount
,      pc.AdjustmentAmount
,      pc.DecisionPayment
FROM   T_PayingControl pc
       INNER JOIN T_Enterprise e ON e.EnterpriseId = pc.EnterpriseId
       INNER JOIN T_Site s ON s.EnterpriseId = e.EnterpriseId
       INNER JOIN M_PricePlan pp ON pp.PricePlanId = F_GetCampaignVal(pc.EnterpriseId, s.SiteId, date(pc.FixedDate), 'AppPlan')
WHERE  1 = 1
AND    pc.EnterpriseId = :EnterpriseId
AND    pc.FixedDate = :FixedDate
ORDER BY s.SiteId
Q_END;
        $ri = $this->app->dbAdapter->query($sql)->execute($prm);
        $params['entpay_list_sub'] = $ri->current();

        // 同梱ツール関連の追加情報
        $sql = <<<Q_END
SELECT pc.Seq AS PCSeq
,      pc.ClaimFee AS ControlClaimFee
,      pas.Seq AS PASSeq
,      pas.ClaimFee AS SalesClaimFee
,      pas.PayingControlSeq
,      pas.OrderSeq
,      CASE WHEN ch.EnterpriseBillingCode IS NULL THEN 0
           ELSE pas.ClaimFee
       END AS SelfBillingClaimFee
,      CASE WHEN ch.EnterpriseBillingCode IS NULL THEN 0
            ELSE 1
       END AS HasInfo
FROM   T_PayingControl pc
       INNER JOIN T_PayingAndSales pas ON pas.PayingControlSeq = pc.Seq
       LEFT OUTER JOIN T_Order o ON o.OrderSeq = pas.OrderSeq
       LEFT OUTER JOIN T_ClaimControl cc ON cc.OrderSeq = o.OrderSeq
       LEFT OUTER JOIN T_ClaimHistory ch ON ch.OrderSeq = o.P_OrderSeq
WHERE  1 = 1
AND    pc.EnterpriseId = :EnterpriseId
AND    pc.FixedDate = :FixedDate
AND    ch.Seq = (SELECT MIN(Seq) FROM T_ClaimHistory ch2 where ch2.OrderSeq = o.P_OrderSeq)
Q_END;
        $ri = $this->app->dbAdapter->query($sql)->execute($prm);
        $rows = ResultInterfaceToArray($ri);
        $fee_total = 0;
        $has_fee = false;
        foreach($rows as $row) {
            if($row['HasInfo']) $has_fee = true;
            $fee_total += (int)$row['SelfBillingClaimFee'];
        }
        $selfBillingInfo = array(
                'HasInfo' => $has_fee,
                'ClaimFee' => $fee_total
        );
        $params['self_billing_info'] = $selfBillingInfo;

        foreach( $params as $key => $value ) {
            $this->view->assign( $key, $value );
        }

        //届いてから決済を利用するサイトが紐付いているか
        $sql = "SELECT COUNT(1) AS cnt FROM T_Site WHERE EnterpriseId = :EnterpriseId AND PaymentAfterArrivalFlg = 1";
        $cnt = $this->app->dbAdapter->query($sql)->execute(array(':EnterpriseId' => $entId))->current()['cnt'];
        if(isset($cnt)){
            $this->view->assign( 'PaymentAfterArrivalFlg', 0 < $cnt ? 1 : 0);
        }

        return $this->view;
    }

    /**
     * 明細一覧ダウンロード
     */
    public function downloadAction() {
        // 立替締め日
        $params = $this->getParams();
        $fixedDate = $params['fixedDate'];

        // ZIPファイル作成
        $zip = new \ZipArchive();

        // 出力ファイル名
        $outFileName= 'MeisaiIchiran.zip';

        // TEMP領域作成
        $tmpFilePath = tempnam( sys_get_temp_dir(), 'tmp' );

        // ZIPファイルオープン
        $zip->open( $tmpFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE );

        $unlinkList = array();

        // 明細1作成(精算書)
        $tmpFileName1 = $this->createMeisai1( $fixedDate, $tmpFilePath );
        $unlinkList[] = $tmpFileName1;
        $fileName1 = str_replace( $tmpFilePath, '', $tmpFileName1 );

        // ZIPファイルにファイル追加
        $addFilePath = file_get_contents( $tmpFileName1 );
        $zip->addFromString( mb_convert_encoding($fileName1, 'sjis-win'), $addFilePath );


        // 明細2作成(お取引明細)
        $tmpFileName2 = $this->createMeisai2( $fixedDate, $tmpFilePath );
        $unlinkList[] = $tmpFileName2;
        $fileName2 = str_replace( $tmpFilePath, '', $tmpFileName2 );

        // ZIPファイルにファイル追加
        $addFilePath = file_get_contents( $tmpFileName2 );
        $zip->addFromString( mb_convert_encoding($fileName2, 'sjis-win'), $addFilePath );


        // 明細3作成(印紙代明細)
        $tmpFileName3 = $this->createMeisai3( $fixedDate, $tmpFilePath );
        $unlinkList[] = $tmpFileName3;
        $fileName3 = str_replace( $tmpFilePath, '', $tmpFileName3 );

        // ZIPファイルにファイル追加
        $addFilePath = file_get_contents( $tmpFileName3 );
        $zip->addFromString( mb_convert_encoding($fileName3, 'sjis-win'), $addFilePath );


        // 明細4作成(キャンセル返金明細)
        $tmpFileName4 = $this->createMeisai4( $fixedDate, $tmpFilePath );
        $unlinkList[] = $tmpFileName4;
        $fileName4 = str_replace( $tmpFilePath, '', $tmpFileName4 );

        // ZIPファイルにファイル追加
        $addFilePath = file_get_contents( $tmpFileName4 );
        $zip->addFromString( mb_convert_encoding($fileName4, 'sjis-win'), $addFilePath );


        // 明細5作成(調整額明細)
        $tmpFileName5 = $this->createMeisai5( $fixedDate, $tmpFilePath );
        $unlinkList[] = $tmpFileName5;
        $fileName5 = str_replace( $tmpFilePath, '', $tmpFileName5 );

        // ZIPファイルにファイル追加
        $addFilePath = file_get_contents( $tmpFileName5 );
        $zip->addFromString( mb_convert_encoding($fileName5, 'sjis-win'), $addFilePath );


// 2015/10/29 Y.Suzuki Del 立替精算戻し明細のダウンロード処理は行わない Stt
//         // 明細5作成
//         $tmpFileName6 = $this->createMeisai6( $fixedDate, $tmpFilePath );
//         $unlinkList[] = $tmpFileName6;
//         $fileName6 = str_replace( $tmpFilePath, '', $tmpFileName6 );

//         // ZIPファイルにファイル追加
//         $addFilePath = file_get_contents( $tmpFileName6 );
//         $zip->addFromString( $fileName6, $addFilePath );
// 2015/10/29 Y.Suzuki Del 立替精算戻し明細のダウンロード処理は行わない End

        // ZIPファイルクローズ
        $zip->close();

        // ヘッダ
        header( 'Content-Type: application/octet-stream; name="' . $outFileName . '"' );
        header( 'Content-Disposition: attachment; filename="' . $outFileName . '"' );
        header( 'Content-Length: ' . filesize( $tmpFilePath ) );

        // 出力
        echo readfile( $tmpFilePath );

        // TEMP領域削除
        $unlinkListCount = 0;
        if (!empty($unlinkList)) {
            $unlinkListCount = count($unlinkList);
        }
        for ($i=0; $i<$unlinkListCount; $i++) {
            unlink( $unlinkList[$i] );
        }
        unlink( $tmpFilePath );
        die();
    }

    /**
     * 請求書発行
     */
    public function billissueAction() {
        $params = $this->getParams();
        $fixedDate = $params['fixedDate'];

        // 加盟店ID
        $entId = $this->app->authManager->getUserInfo()->EnterpriseId;

        // SQL用のパラメータ設定
        $prm = array( ':EnterpriseId' => $entId, ':FixedDate' => $fixedDate );

        // PDF用データ取得
        // 請求書兼領収書
        $sql = <<<Q_END
SELECT pc.ExecDate
,      e.EnterpriseNameKj
,      e.PostalCode
,      CONCAT( e.PrefectureName, e.City, e.Town, e.Building ) AS Address
,      pc.FixedDate
,      pc.DecisionDate
,      pc.ExecScheduleDate
,      pc.ChargeCount
,      (
         ( pc.ChargeAmount ) +
         ( pc.SettlementFee ) +
         ( pc.ClaimFee )
       ) AS ChargeAmount
,      ( -1 * pc.SettlementFee ) AS SettlementFee
,      ( -1 * pc.ClaimFee ) AS ClaimFee
,      ( -1 * pc.StampFeeTotal ) AS StampFeeTotal
,      ( -1 * pc.MonthlyFee ) AS MonthlyFee
,      pc.CarryOver
,      pc.CalcelAmount
,      ( -1 * pc.TransferCommission ) AS TransferCommission
,      pc.PayBackAmount
,      pc.AdjustmentAmount
,      ( -1 * (
        ( -1 * pc.SettlementFee ) +
        ( -1 * pc.ClaimFee ) +
        ( -1 * pc.StampFeeTotal ) +
        ( -1 * pc.MonthlyFee ) +
        ( pc.CarryOver ) +
        ( pc.CalcelAmount ) +
        ( -1 * pc.TransferCommission ) +
        ( pc.PayBackAmount ) +
        ( pc.AdjustmentAmount )
       ) ) AS ClaimAmount
,      (
        ( ( pc.ChargeAmount ) +
          ( pc.SettlementFee ) +
          ( pc.ClaimFee )
        ) -
        ( -1 * (
         ( -1 * pc.SettlementFee ) +
         ( -1 * pc.ClaimFee ) +
         ( -1 * pc.StampFeeTotal ) +
         ( -1 * pc.MonthlyFee ) +
         ( pc.CarryOver ) +
         ( pc.CalcelAmount ) +
         ( -1 * pc.TransferCommission ) +
         ( pc.PayBackAmount ) +
         ( pc.AdjustmentAmount )
       ) ) ) AS TotalAmount
/* 以下、クレジット決済関連 */
,      (SELECT COUNT(ao.OrderSeq)
        FROM   T_PayingAndSales pas
               INNER JOIN T_Order o ON (o.OrderSeq = pas.OrderSeq)
               INNER JOIN AT_Order ao ON (ao.OrderSeq = o.OrderSeq)
        WHERE  pas.PayingControlSeq = pc.Seq
        AND    ao.ExtraPayType = 1
        AND    o.ClaimSendingClass = 11
       ) AS ChargeCountExtraPay1DK
,      (SELECT COUNT(ao.OrderSeq)
        FROM   T_PayingAndSales pas
               INNER JOIN T_Order o ON (o.OrderSeq = pas.OrderSeq)
               INNER JOIN AT_Order ao ON (ao.OrderSeq = o.OrderSeq)
        WHERE  pas.PayingControlSeq = pc.Seq
        AND    ao.ExtraPayType = 1
        AND    o.ClaimSendingClass <> 11
       ) AS ChargeCountExtraPay1BS
,      (-1) * (SELECT SUM(pas.SettlementFee)
        FROM   T_PayingAndSales pas
               INNER JOIN T_Order o ON (o.OrderSeq = pas.OrderSeq)
               INNER JOIN AT_Order ao ON (ao.OrderSeq = o.OrderSeq)
        WHERE  pas.PayingControlSeq = pc.Seq
        AND    ao.ExtraPayType = 1
       ) AS SettlementFeeExtraPay1
,      (-1) * (SELECT SUM(pas.ClaimFee)
        FROM   T_PayingAndSales pas
               INNER JOIN T_Order o ON (o.OrderSeq = pas.OrderSeq)
               INNER JOIN AT_Order ao ON (ao.OrderSeq = o.OrderSeq)
        WHERE  pas.PayingControlSeq = pc.Seq
        AND    ao.ExtraPayType = 1
        AND    o.ClaimSendingClass = 11
       ) AS ClaimFeeExtraPay1DK
,      (-1) * (SELECT SUM(pas.ClaimFee)
        FROM   T_PayingAndSales pas
               INNER JOIN T_Order o ON (o.OrderSeq = pas.OrderSeq)
               INNER JOIN AT_Order ao ON (ao.OrderSeq = o.OrderSeq)
        WHERE  pas.PayingControlSeq = pc.Seq
        AND    ao.ExtraPayType = 1
        AND    o.ClaimSendingClass <> 11
       ) AS ClaimFeeExtraPay1BS
,      (-1) * (SELECT SUM(can.RepayTotal)
        FROM   T_PayingAndSales pas
               INNER JOIN T_Order o ON (o.OrderSeq = pas.OrderSeq)
               INNER JOIN AT_Order ao ON (ao.OrderSeq = o.OrderSeq)
               INNER JOIN T_Cancel can ON (o.OrderSeq = can.OrderSeq)
        WHERE  pas.PayingControlSeq = pc.Seq
        AND    ao.ExtraPayType = 1
        AND    o.ClaimSendingClass = 11
       ) AS CalcelAmountExtraPay1DK
,      (-1) * (SELECT SUM(can.RepayTotal)
        FROM   T_PayingAndSales pas
               INNER JOIN T_Order o ON (o.OrderSeq = pas.OrderSeq)
               INNER JOIN AT_Order ao ON (ao.OrderSeq = o.OrderSeq)
               INNER JOIN T_Cancel can ON (o.OrderSeq = can.OrderSeq)
        WHERE  pas.PayingControlSeq = pc.Seq
        AND    ao.ExtraPayType = 1
        AND    o.ClaimSendingClass <> 11
       ) AS CalcelAmountExtraPay1BS
        FROM   T_PayingControl pc
       INNER JOIN T_Enterprise e ON pc.EnterpriseId = e.EnterpriseId
WHERE  1 = 1
AND    pc.EnterpriseId = :EnterpriseId
AND    pc.FixedDate = :FixedDate
Q_END;
        $datas1 = $this->app->dbAdapter->query( $sql )->execute( $prm )->current();

        // 請求書手数料合計(内自社印刷分)
        $sql = <<<Q_END
SELECT pc.Seq AS PCSeq
,      pc.ClaimFee AS ControlClaimFee
,      pas.Seq AS PASSeq
,      pas.ClaimFee AS SalesClaimFee
,      pas.PayingControlSeq
,      pas.OrderSeq
,      CASE WHEN ch.EnterpriseBillingCode IS NULL THEN 0
           ELSE pas.ClaimFee
       END AS SelfBillingClaimFee
,      CASE WHEN ch.EnterpriseBillingCode IS NULL THEN 0
            ELSE 1
       END AS HasInfo
FROM   T_PayingControl pc
       INNER JOIN T_PayingAndSales pas ON pas.PayingControlSeq = pc.Seq
       LEFT OUTER JOIN T_Order o ON o.OrderSeq = pas.OrderSeq
       LEFT OUTER JOIN T_ClaimControl cc ON cc.OrderSeq = o.OrderSeq
       LEFT OUTER JOIN T_ClaimHistory ch ON ch.OrderSeq = o.P_OrderSeq
WHERE  1 = 1
AND    pc.EnterpriseId = :EnterpriseId
AND    pc.FixedDate = :FixedDate
AND    ch.Seq = (SELECT MIN(Seq) FROM T_ClaimHistory ch2 where ch2.OrderSeq = o.P_OrderSeq)
Q_END;
        $ri = $this->app->dbAdapter->query($sql)->execute($prm);
        $rows = ResultInterfaceToArray($ri);
        $fee_total = 0;
        $has_fee = false;
        foreach($rows as $row) {
            if($row['HasInfo']) $has_fee = true;
            $fee_total += (int)$row['SelfBillingClaimFee'];
        }
        $selfBillingInfo = array(
                'HasInfo' => $has_fee,
                'ClaimFee' => $fee_total * -1
        );

        // お取引明細
        $sql = <<<Q_END
SELECT pc.ExecDate
,      e.EnterpriseNameKj
,      pc.FixedDate
,      pc.ExecScheduleDate
,      pc.ChargeCount
,      s.SiteNameKj
,      o.OrderId
,      o.Ent_OrderId
,      c.NameKj
,      o.ReceiptOrderDate
,      ( SELECT MAX( Deli_JournalIncDate ) FROM T_OrderItems WHERE OrderSeq = o.OrderSeq ) AS Deli_JournalIncDate
,      pc.FixedDate
,      pas.UseAmount
,      ( -1 * pas.SettlementFee ) AS SettlementFee
,      ( -1 * pas.ClaimFee ) AS ClaimFee
,      0 AS StampFee
,      (
        ( pas.UseAmount ) +
        ( -1 * pas.SettlementFee ) +
        ( -1 * pas.ClaimFee )
       ) AS ChargeAmount
FROM   T_PayingControl pc
       INNER JOIN T_PayingAndSales pas ON pas.PayingControlSeq = pc.Seq
       INNER JOIN T_Order o ON o.OrderSeq = pas.OrderSeq
       LEFT OUTER JOIN T_StampFee sf ON sf.OrderSeq = o.OrderSeq
       INNER JOIN T_Customer c ON c.OrderSeq = o.OrderSeq
       INNER JOIN T_Enterprise e ON e.EnterpriseId = o.EnterpriseId
       INNER JOIN T_Site s ON s.SiteId = o.SiteId
WHERE  1 = 1
AND    pc.EnterpriseId = :EnterpriseId
AND    pc.FixedDate = :FixedDate
ORDER BY o.SiteId, o.OrderId
Q_END;
        $ri = $this->app->dbAdapter->query( $sql )->execute( $prm );
        $datas2 = $this->_grouping(ResultInterfaceToArray($ri), 'SiteNameKj');

        // 印紙代明細
        $sql = <<<Q_END
SELECT pc.ExecDate
,      e.EnterpriseNameKj
,      pc.FixedDate
,      pc.ExecScheduleDate
,      pc.StampFeeCount
,      s.SiteNameKj
,      o.OrderId
,      o.Ent_OrderId
,      c.NameKj
,      o.ReceiptOrderDate
,      pc2.FixedDate AS FixedDate2
,      o.UseAmount
,      sf.StampFee
FROM   T_PayingControl pc
       INNER JOIN T_StampFee sf ON sf.PayingControlSeq = pc.Seq
       INNER JOIN T_Order o ON o.OrderSeq = sf.OrderSeq
       INNER JOIN T_Customer c ON c.OrderSeq = o.OrderSeq
       INNER JOIN T_Enterprise e ON e.EnterpriseId = pc.EnterpriseId
       INNER JOIN T_Site s ON s.SiteId = o.SiteId
       LEFT OUTER JOIN T_PayingAndSales pas ON pas.OrderSeq = o.OrderSeq
       LEFT OUTER JOIN T_PayingControl pc2 ON pc2.Seq = pas.PayingControlSeq
WHERE  1 = 1
AND    pc.EnterpriseId = :EnterpriseId
AND    pc.FixedDate = :FixedDate
ORDER BY o.SiteId, o.OrderId
Q_END;
        $ri = $this->app->dbAdapter->query($sql)->execute($prm);
        $datas3 = $this->_grouping(ResultInterfaceToArray($ri), 'SiteNameKj');

        // キャンセル明細
        $sql = <<<Q_END
SELECT pc.ExecDate
,      e.EnterpriseId
,      e.EnterpriseNameKj
,      pc.FixedDate
,      pc.ExecScheduleDate
,      pc.CancelCount
,      s.SiteNameKj
,      o.OrderId
,      o.Ent_OrderId
,      c.NameKj
,      o.ReceiptOrderDate
,      pc2.FixedDate AS FixedDate2
,      cncl.CancelDate
,      o.UseAmount
,      ( -1 * cncl.RepayTotal ) AS RepayTotal
FROM   T_PayingControl pc
       INNER JOIN T_Cancel cncl ON cncl.PayingControlSeq = pc.Seq
       INNER JOIN T_Order o ON o.OrderSeq = cncl.OrderSeq
       INNER JOIN T_Customer c ON c.OrderSeq = o.OrderSeq
       INNER JOIN T_Enterprise e ON e.EnterpriseId = pc.EnterpriseId
       INNER JOIN T_Site s ON s.SiteId = o.SiteId
       LEFT OUTER JOIN T_PayingAndSales pas ON pas.OrderSeq = o.OrderSeq
       LEFT OUTER JOIN T_PayingControl pc2 ON pc2.Seq = pas.PayingControlSeq
WHERE  1 = 1
AND    pc.EnterpriseId = :EnterpriseId
AND    pc.FixedDate = :FixedDate
AND    cncl.ValidFlg = 1
ORDER BY o.SiteId, o.OrderId
Q_END;
        $ri = $this->app->dbAdapter->query($sql)->execute($prm);
        $datas4 = $this->_grouping(ResultInterfaceToArray($ri), 'SiteNameKj');

        // 調整額内訳明細
        $sql = <<<Q_END
SELECT pc.ExecDate
,      e.EnterpriseNameKj
,      pc.FixedDate
,      pc.ExecScheduleDate
,      pc.AdjustmentCount
,      o.OrderId
,      o.Ent_OrderId
,      c.NameKj
,      o.ReceiptOrderDate
,      ( SELECT KeyContent FROM M_Code WHERE CodeId = 89 AND KeyCode = aa.ItemCode ) AS ItemCodeName
,      aa.AdjustmentAmount
FROM   T_PayingControl pc
       INNER JOIN T_AdjustmentAmount aa ON aa.PayingControlSeq = pc.Seq
       INNER JOIN T_Enterprise e ON e.EnterpriseId = pc.EnterpriseId
       LEFT OUTER JOIN T_Order o ON o.OrderSeq = aa.OrderSeq
       LEFT OUTER JOIN T_Customer c ON c.OrderSeq = aa.OrderSeq
WHERE  1 = 1
AND    pc.EnterpriseId = :EnterpriseId
AND    pc.FixedDate = :FixedDate
ORDER BY aa.SerialNumber
Q_END;
        $ri = $this->app->dbAdapter->query($sql)->execute($prm);
        $datas5 = ResultInterfaceToArray($ri);

// 2015/10/29 Y.Suzuki Del 立替精算戻し明細の印刷処理は行わない Stt
//         // 立替精算戻し明細
//         $sql = <<<Q_END
// SELECT pc.ExecDate
// ,      e.EnterpriseNameKj
// ,      pc.FixedDate
// ,      pc.ExecScheduleDate
// ,      pc.PayBackCount
// ,      s.SiteNameKj
// ,      o.OrderId
// ,      o.Ent_OrderId
// ,      c.NameKj
// ,      o.ReceiptOrderDate
// ,      ( SELECT MAX( Deli_JournalIncDate ) FROM T_OrderItems WHERE OrderSeq = o.OrderSeq ) AS Deli_JournalIncDate
// ,      pc2.FixedDate AS FixedDate2
// ,      pas.UseAmount
// ,      cc.ReceiptAmountTotal
// ,      pbc.PayBackAmount
// FROM   T_PayingControl pc
//        INNER JOIN T_PayingBackControl pbc ON pbc.PayingControlSeq = pc.Seq
//        INNER JOIN T_PayingAndSales pas ON pas.OrderSeq = pbc.OrderSeq
//        INNER JOIN T_Order o ON o.OrderSeq = pbc.OrderSeq
//        INNER JOIN T_Customer c ON c.OrderSeq = o.OrderSeq
//        INNER JOIN T_Enterprise e ON e.EnterpriseId = pc.EnterpriseId
//        INNER JOIN T_Site s ON s.SiteId = o.SiteId
//        LEFT OUTER JOIN T_ClaimControl cc ON o.OrderSeq = cc.OrderSeq
//        LEFT OUTER JOIN T_PayingControl pc2 ON pc2.Seq = pas.PayingControlSeq
// WHERE  1 = 1
// AND    pc.EnterpriseId = :EnterpriseId
// AND    pc.FixedDate = :FixedDate
// ORDER BY o.SiteId, o.OrderId
// Q_END;
//         $ri = $this->app->dbAdapter->query($sql)->execute($prm);
//         $datas6 = $this->_grouping(ResultInterfaceToArray($ri), 'SiteNameKj');
// 2015/10/29 Y.Suzuki Del 立替精算戻し明細の印刷処理は行わない End

        // (補助データ)お取引明細／立替精算戻し明細、共通
        $sql = <<<Q_END
SELECT ( -1 * pc.StampFeeTotal) AS StampFeeTotal
,      IFNULL(pp.PricePlanName, '') AS PricePlanName
,      ( -1 * pc.MonthlyFee ) AS MonthlyFee
,      pc.CarryOver
,      pc.CalcelAmount
,      ( -1 * pc.TransferCommission ) AS TransferCommission
,      pc.PayBackAmount
,      pc.AdjustmentAmount
,      pc.DecisionPayment
FROM   T_PayingControl pc
       INNER JOIN T_Enterprise e ON e.EnterpriseId = pc.EnterpriseId
       INNER JOIN T_Site s ON s.EnterpriseId = e.EnterpriseId
       INNER JOIN M_PricePlan pp ON pp.PricePlanId = F_GetCampaignVal(pc.EnterpriseId, s.SiteId, date(pc.FixedDate), 'AppPlan')
WHERE  1 = 1
AND    pc.EnterpriseId = :EnterpriseId
AND    pc.FixedDate = :FixedDate
ORDER BY s.SiteId
Q_END;
        $dataSub = $this->app->dbAdapter->query( $sql )->execute( $prm )->current();

        // OEMデータ
        $oemData = $this->app->getCurrentOemData();

        //届いてから決済を利用するサイトが紐付いているか
        $sql = "SELECT COUNT(1) AS cnt FROM T_Site WHERE EnterpriseId = :EnterpriseId AND PaymentAfterArrivalFlg = 1";
        $cnt = $this->app->dbAdapter->query($sql)->execute(array(':EnterpriseId' => $entId))->current()['cnt'];
        if(isset($cnt)){
            $this->view->assign( 'PaymentAfterArrivalFlg', 0 < $cnt ? 1 : 0);
        }

        // PDF印刷
        $fileName = sprintf( 'Seikyu_%s_%s.pdf', date( "YmdHis" ), $entId );

        $this->view->assign( 'datas', $datas1 );                        // 請求書兼領収書
        $this->view->assign( 'selfBillingInfo', $selfBillingInfo );     // 請求書兼領収書の請求書手数料合計(内自社印刷分)
        $this->view->assign( 'datas2', $datas2 );                       // お取引明細
        $this->view->assign( 'datas3', $datas3 );                       // 印紙代明細
        $this->view->assign( 'datas4', $datas4 );                       // キャンセル明細
        $this->view->assign( 'datas5', $datas5 );                       // 調整額内訳明細
        $this->view->assign( 'dataSub', $dataSub );                     // (補助データ)お取引明細／立替精算戻し明細、共通
        $this->view->assign( 'documentRoot', $_SERVER['DOCUMENT_ROOT'] );
        $this->view->assign( 'logo', $oemData['SmallLogo'] );           // ロゴ小
        $this->view->assign( 'title', $fileName );

        $viewRender = $this->getServiceLocator()->get('ViewRenderer');
        $html = $viewRender->render($this->view);

        // 一時ファイルの保存先
        $mdlsp = new \models\Table\TableSystemProperty($this->app->dbAdapter);
        $tempDir = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'TempFileDir');
        $tempDir = realpath($tempDir);

        // 出力ファイル名
        $outFileName = $fileName;

        // 中間ファイル名
        $fname_html = ($tempDir . '/__tmp_' . $this->app->authManager->getUserInfo()->EnterpriseId . '_' . $this->app->authManager->getUserInfo()->LoginId . '__.html');
        $fname_pdf  = ($tempDir . '/__tmp_' . $this->app->authManager->getUserInfo()->EnterpriseId . '_' . $this->app->authManager->getUserInfo()->LoginId . '__.pdf');

        // HTML出力
        file_put_contents($fname_html, $html);

        // PDF変換(外部プログラム起動)
        $ename = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'wkhtmltopdf');
        $option = " --margin-left 0 --margin-right 0 --margin-top 0 --margin-bottom 0 ";
        exec($ename . $option . $fname_html . ' ' . $fname_pdf);

        unlink($fname_html);

        header( 'Content-Type: application/octet-stream; name="' . $outFileName . '"' );
        header( 'Content-Disposition: attachment; filename="' . $outFileName . '"' );
        header( 'Content-Length: ' . filesize( $fname_pdf ) );

        // 出力
        echo readfile( $fname_pdf );

        unlink( $fname_pdf );
        die();
    }

	/**
	 * 立替締め日リストを取得する
	 *
	 * @return array
	 */
	private function getFixedDateList($entId) {
		$result = array();

		$end = date('Y-m-d');

		$start = date('Y-m-d', strtotime('-10 year +1 day'));

		$sql  = " SELECT DISTINCT FixedDate, ExecScheduleDate ";
		$sql .= " FROM   T_PayingControl ";
		$sql .= " WHERE  1 = 1 ";
		$sql .= " AND    EnterpriseId = :entId ";
		$sql .= " AND    FixedDate BETWEEN :start AND :end ";
		$sql .= " AND    PayingControlStatus = 1 ";       // 本締め／仮締め区分(1：本締め、に限定)
		$sql .= " ORDER BY FixedDate DESC ";

		$stmt = $this->app->dbAdapter->query($sql);

		$param = array( ':entId' => $entId, ':start' => $start, ':end' => $end );
		$ri = $stmt->execute($param);
		foreach( $ri as $row ) {
			$result[] = $row;
		}
		return $result;
	}

	/**
	 * _execQuery()の結果の配列を、指定のカラム名でグルーピングした
	 * 連想配列として詰めなおす
	 *
	 * @param array $list クエリ実行結果の配列
	 * @param string $key_name グルーピングキーとなる、$listの1要素中のカラム名
	 * @return array
	 */
	private function _grouping($list, $key_name) {
	    $results = array();
	    foreach($list as $row) {
	        $key = $row[$key_name];
	        if( is_array($results[$key]) ) {
	            $results[$key][] = $row;
	        } else {
	            $results[$key] = array($row);
	        }
	    }
	    return $results;
	}

	/**
	 * 立替速報確認
	 */
	public function confirmNewsAction()
    {
		$this->setPageTitle( '立替速報確認' );

		$params = $this->getParams();

        $sql  = " SELECT FixedDate ";
        $sql .= " ,      ExecScheduleDate ";
        $sql .= " ,      ChargeCount ";
        $sql .= " ,      ChargeAmount ";
        $sql .= " ,      SettlementFee ";
        $sql .= " ,      ClaimFee ";
        $sql .= " ,      StampFeeTotal ";
        $sql .= " ,      MonthlyFee ";
        $sql .= " ,      CarryOver ";
        $sql .= " ,      CalcelAmount ";
        $sql .= " ,      TransferCommission ";
        $sql .= " ,      StampFeeTotal ";
        $sql .= " ,      PayBackAmount ";
        $sql .= " ,      DecisionPayment ";
        $sql .= " ,      AdjustmentAmount ";
        $sql .= " FROM   T_PayingControl ";
        $sql .= " WHERE  1 = 1 ";
        $sql .= " AND    ValidFlg = 1 ";
        $sql .= " AND    EnterpriseId = " . $this->app->authManager->getUserInfo()->EnterpriseId;
        $sql .= " ORDER BY PayingControlStatus ASC, Seq DESC ";
        $sql .= " LIMIT 1 ";

        $ri = $this->app->dbAdapter->query($sql)->execute(null);

        $this->view->assign("list", $ri->current());
        $this->view->assign('genzai', BaseGeneralUtils::getDateString(date('Y-m-d')));

        return $this->view;
    }

    /**
     * 明細1CSV作成
     * @param $fixedDate 立替締め日
     * @param $tmpFilePath TEMP領域
     * @return ファイル名
     */
    private function createMeisai1( $fixedDate, $tmpFilePath ) {
        // 加盟店ID
        $entId = $this->app->authManager->getUserInfo()->EnterpriseId;

        // CSVデータ取得
        $sql = <<<Q_END
SELECT e.EnterpriseId
,      e.EnterpriseNameKj
,      e.PostalCode
,      CONCAT( e.PrefectureName, e.City, e.Town, e.Building ) AS Address
,      pc.FixedDate
,      pc.ExecScheduleDate
,      pc.ChargeCount
,      (
         ( pc.ChargeAmount ) +
         ( pc.SettlementFee ) +
         ( pc.ClaimFee )
       ) AS ChargeAmount
,      ( -1 * pc.SettlementFee ) AS SettlementFee
,      ( -1 * pc.ClaimFee ) AS ClaimFee
,      ( -1 * pc.StampFeeTotal ) AS StampFeeTotal
,      ( -1 * pc.MonthlyFee ) AS MonthlyFee
,      pc.CarryOver
,      pc.CalcelAmount
,      ( -1 * pc.TransferCommission ) AS TransferCommission
,      pc.PayBackAmount
,      pc.AdjustmentAmount
,      ( -1 * (
        ( -1 * pc.SettlementFee ) +
        ( -1 * pc.ClaimFee ) +
        ( -1 * pc.StampFeeTotal ) +
        ( -1 * pc.MonthlyFee ) +
        ( pc.CarryOver ) +
        ( pc.CalcelAmount ) +
        ( -1 * pc.TransferCommission ) +
        ( pc.PayBackAmount ) +
        ( pc.AdjustmentAmount )
       ) ) AS ClaimAmount
,      (
        ( ( pc.ChargeAmount ) +
          ( pc.SettlementFee ) +
          ( pc.ClaimFee )
        ) -
        ( -1 * (
         ( -1 * pc.SettlementFee ) +
         ( -1 * pc.ClaimFee ) +
         ( -1 * pc.StampFeeTotal ) +
         ( -1 * pc.MonthlyFee ) +
         ( pc.CarryOver ) +
         ( pc.CalcelAmount ) +
         ( -1 * pc.TransferCommission ) +
         ( pc.PayBackAmount ) +
         ( pc.AdjustmentAmount )
       ) ) ) AS TotalAmount
FROM   T_PayingControl pc
       INNER JOIN T_Enterprise e ON pc.EnterpriseId = e.EnterpriseId
WHERE  1 = 1
AND    pc.EnterpriseId = :EnterpriseId
AND    pc.FixedDate = :FixedDate
Q_END;

        // SQL用のパラメータ設定
        $prm = array( ':EnterpriseId' => $entId, ':FixedDate' => $fixedDate );
        $datas = ResultInterfaceToArray( $this->app->dbAdapter->query( $sql )->execute( $prm ) );

        // テンプレートヘッダー/フィールドマスタを使用して、CSVをResposeに入れる
        $templateId = 'CKA11019_1';     // テンプレートID       ご利用明細
        $templateClass = 2;             // 区分                 加盟店
        $seq = $entId;                  // シーケンス           加盟店ID
        $templatePattern = 0;           // テンプレートパターン

        $logicTemplate = new LogicTemplate( $this->app->dbAdapter );

        $fileName = sprintf('%s_精算書.csv', date('Ymd'));

        $tmpFileName = $tmpFilePath . $fileName;

        $result = $logicTemplate->convertArraytoFile( $datas, $tmpFileName, $templateId, $templateClass, $seq, $templatePattern );

        if( $result == false ) {
            throw new \Exception( $logicTemplate->getErrorMessage() );
        }

        return $tmpFileName;
    }

    /**
     * 明細2CSV作成
     * @param $fixedDate 立替締め日
     * @param $tmpFilePath TEMP領域
     * @return ファイル名
     */
    private function createMeisai2( $fixedDate, $tmpFilePath ) {
        // 加盟店ID
        $entId = $this->app->authManager->getUserInfo()->EnterpriseId;

        // CSVデータ取得
        $sql = <<<Q_END
SELECT e.EnterpriseId
,      e.EnterpriseNameKj
,      pc.FixedDate
,      pc.ExecScheduleDate
,      pc.ChargeCount
,      s.SiteId
,      s.SiteNameKj
,      o.OrderId
,      o.Ent_OrderId
,      c.NameKj
,      o.ReceiptOrderDate
,      ( SELECT MAX( Deli_JournalIncDate ) FROM T_OrderItems WHERE OrderSeq = o.OrderSeq ) AS Deli_JournalIncDate
,      pc.FixedDate AS FixedDate2
,      pas.UseAmount
,      ( -1 * pas.SettlementFee ) AS SettlementFee
,      ( -1 * pas.ClaimFee ) AS ClaimFee
,      0 AS StampFee
,      (
        ( pas.UseAmount ) +
        ( -1 * pas.SettlementFee ) +
        ( -1 * pas.ClaimFee )
       ) AS ChargeAmount
FROM   T_PayingControl pc
       INNER JOIN T_PayingAndSales pas ON pas.PayingControlSeq = pc.Seq
       INNER JOIN T_Order o ON o.OrderSeq = pas.OrderSeq
       LEFT OUTER JOIN T_StampFee sf ON sf.OrderSeq = o.OrderSeq
       INNER JOIN T_Customer c ON c.OrderSeq = o.OrderSeq
       INNER JOIN T_Enterprise e ON e.EnterpriseId = o.EnterpriseId
       INNER JOIN T_Site s ON s.SiteId = o.SiteId
WHERE  1 = 1
AND    pc.EnterpriseId = :EnterpriseId
AND    pc.FixedDate = :FixedDate
ORDER BY o.SiteId, o.OrderId
Q_END;

        // SQL用のパラメータ設定
        $prm = array( ':EnterpriseId' => $entId, ':FixedDate' => $fixedDate );
        $datas = ResultInterfaceToArray( $this->app->dbAdapter->query( $sql )->execute( $prm ) );

        // テンプレートヘッダー/フィールドマスタを使用して、CSVをResposeに入れる
        $templateId = 'CKA11019_2';     // テンプレートID       ご利用明細
        $templateClass = 2;             // 区分                 加盟店
        $seq = $entId;                  // シーケンス           加盟店ID
        $templatePattern = 0;           // テンプレートパターン

        $logicTemplate = new LogicTemplate( $this->app->dbAdapter );

        $fileName = sprintf( '%s_お取引明細.csv', date('Ymd') );

        $tmpFileName = $tmpFilePath . $fileName;

        $result = $logicTemplate->convertArraytoFile( $datas, $tmpFileName, $templateId, $templateClass, $seq, $templatePattern );

        if( $result == false ) {
            throw new \Exception( $logicTemplate->getErrorMessage() );
        }

        return $tmpFileName;
    }

    /**
     * 明細3CSV作成
     * @param $fixedDate 立替締め日
     * @param $tmpFilePath TEMP領域
     * @return ファイル名
     */
    private function createMeisai3( $fixedDate, $tmpFilePath ) {
        // 加盟店ID
        $entId = $this->app->authManager->getUserInfo()->EnterpriseId;

        // CSVデータ取得
        $sql = <<<Q_END
SELECT e.EnterpriseId
,      e.EnterpriseNameKj
,      pc.FixedDate
,      pc.ExecScheduleDate
,      pc.ChargeCount
,      s.SiteId
,      s.SiteNameKj
,      o.OrderId
,      o.Ent_OrderId
,      c.NameKj
,      o.ReceiptOrderDate
,      pc2.FixedDate AS FixedDate2
,      o.UseAmount
,      sf.StampFee
FROM   T_PayingControl pc
       INNER JOIN T_StampFee sf ON sf.PayingControlSeq = pc.Seq
       INNER JOIN T_Order o ON o.OrderSeq = sf.OrderSeq
       INNER JOIN T_Customer c ON c.OrderSeq = o.OrderSeq
       INNER JOIN T_Enterprise e ON e.EnterpriseId = pc.EnterpriseId
       INNER JOIN T_Site s ON s.SiteId = o.SiteId
       LEFT OUTER JOIN T_PayingAndSales pas ON pas.OrderSeq = o.OrderSeq
       LEFT OUTER JOIN T_PayingControl pc2 ON pc2.Seq = pas.PayingControlSeq
WHERE  1 = 1
AND    pc.EnterpriseId = :EnterpriseId
AND    pc.FixedDate = :FixedDate
ORDER BY o.SiteId, o.OrderId
Q_END;

        // SQL用のパラメータ設定
        $prm = array( ':EnterpriseId' => $entId, ':FixedDate' => $fixedDate );
        $datas = ResultInterfaceToArray( $this->app->dbAdapter->query( $sql )->execute( $prm ) );

        // テンプレートヘッダー/フィールドマスタを使用して、CSVをResposeに入れる
        $templateId = 'CKA11019_3';     // テンプレートID       ご利用明細
        $templateClass = 2;             // 区分                 加盟店
        $seq = $entId;                  // シーケンス           加盟店ID
        $templatePattern = 0;           // テンプレートパターン

        $logicTemplate = new LogicTemplate( $this->app->dbAdapter );

        $fileName = sprintf( '%s_印紙代明細.csv', date('Ymd') );

        $tmpFileName = $tmpFilePath . $fileName;

        $result = $logicTemplate->convertArraytoFile( $datas, $tmpFileName, $templateId, $templateClass, $seq, $templatePattern );

        if( $result == false ) {
            throw new \Exception( $logicTemplate->getErrorMessage() );
        }

        return $tmpFileName;
    }

    /**
     * 明細4CSV作成
     * @param $fixedDate 立替締め日
     * @param $tmpFilePath TEMP領域
     * @return ファイル名
     */
    private function createMeisai4( $fixedDate, $tmpFilePath ) {
        // 加盟店ID
        $entId = $this->app->authManager->getUserInfo()->EnterpriseId;

        // CSVデータ取得
        $sql = <<<Q_END
SELECT e.EnterpriseId
,      e.EnterpriseNameKj
,      pc.FixedDate
,      pc.ExecScheduleDate
,      pc.ChargeCount
,      s.SiteId
,      s.SiteNameKj
,      o.OrderId
,      o.Ent_OrderId
,      c.NameKj
,      o.ReceiptOrderDate
,      pc2.FixedDate AS FixedDate2
,      cncl.CancelDate
,      o.UseAmount
,      ( -1 * cncl.RepayTotal ) AS RepayTotal
FROM   T_PayingControl pc
       INNER JOIN T_Cancel cncl ON cncl.PayingControlSeq = pc.Seq
       INNER JOIN T_Order o ON o.OrderSeq = cncl.OrderSeq
       INNER JOIN T_Customer c ON c.OrderSeq = o.OrderSeq
       INNER JOIN T_Enterprise e ON e.EnterpriseId = pc.EnterpriseId
       INNER JOIN T_Site s ON s.SiteId = o.SiteId
       LEFT OUTER JOIN T_PayingAndSales pas ON pas.OrderSeq = o.OrderSeq
       LEFT OUTER JOIN T_PayingControl pc2 ON pc2.Seq = pas.PayingControlSeq
WHERE  1 = 1
AND    pc.EnterpriseId = :EnterpriseId
AND    pc.FixedDate = :FixedDate
AND    cncl.ValidFlg = 1
ORDER BY o.SiteId, o.OrderId
Q_END;

        // SQL用のパラメータ設定
        $prm = array( ':EnterpriseId' => $entId, ':FixedDate' => $fixedDate );
        $datas = ResultInterfaceToArray( $this->app->dbAdapter->query( $sql )->execute( $prm ) );

        // テンプレートヘッダー/フィールドマスタを使用して、CSVをResposeに入れる
        $templateId = 'CKA11019_4';     // テンプレートID       ご利用明細
        $templateClass = 2;             // 区分                 加盟店
        $seq = $entId;                  // シーケンス           加盟店ID
        $templatePattern = 0;           // テンプレートパターン

        $logicTemplate = new LogicTemplate( $this->app->dbAdapter );

        $fileName = sprintf( '%s_キャンセル返金明細.csv', date('Ymd') );

        $tmpFileName = $tmpFilePath . $fileName;

        $result = $logicTemplate->convertArraytoFile( $datas, $tmpFileName, $templateId, $templateClass, $seq, $templatePattern );

        if( $result == false ) {
            throw new \Exception( $logicTemplate->getErrorMessage() );
        }

        return $tmpFileName;
    }

    /**
     * 明細5CSV作成
     * @param $fixedDate 立替締め日
     * @param $tmpFilePath TEMP領域
     * @return ファイル名
     */
    private function createMeisai5( $fixedDate, $tmpFilePath ) {
        // 加盟店ID
        $entId = $this->app->authManager->getUserInfo()->EnterpriseId;

        // CSVデータ取得
        $sql = <<<Q_END
SELECT e.EnterpriseId
,      e.EnterpriseNameKj
,      pc.FixedDate
,      pc.ExecScheduleDate
,      pc.ChargeCount
,      pc2.FixedDate AS FixedDate2
,      o.OrderId
,      o.Ent_OrderId
,      c.NameKj
,      o.ReceiptOrderDate
,      ( SELECT KeyContent FROM M_Code WHERE CodeId = 89 AND KeyCode = aa.ItemCode ) AS ItemCodeName
,      aa.AdjustmentAmount
FROM   T_PayingControl pc
       INNER JOIN T_AdjustmentAmount aa ON aa.PayingControlSeq = pc.Seq
       INNER JOIN T_Enterprise e ON e.EnterpriseId = pc.EnterpriseId
       LEFT OUTER JOIN T_Order o ON o.OrderSeq = aa.OrderSeq
       LEFT OUTER JOIN T_Customer c ON c.OrderSeq = aa.OrderSeq
       LEFT OUTER JOIN T_PayingAndSales pas ON pas.OrderSeq = o.OrderSeq
       LEFT OUTER JOIN T_PayingControl pc2 ON pc2.Seq = pas.PayingControlSeq
WHERE  1 = 1
AND    pc.EnterpriseId = :EnterpriseId
AND    pc.FixedDate = :FixedDate
ORDER BY aa.SerialNumber
Q_END;

        // SQL用のパラメータ設定
        $prm = array( ':EnterpriseId' => $entId, ':FixedDate' => $fixedDate );
        $datas = ResultInterfaceToArray( $this->app->dbAdapter->query( $sql )->execute( $prm ) );

        // テンプレートヘッダー/フィールドマスタを使用して、CSVをResposeに入れる
        $templateId = 'CKA11019_5';     // テンプレートID       ご利用明細
        $templateClass = 2;             // 区分                 加盟店
        $seq = $entId;                  // シーケンス           加盟店ID
        $templatePattern = 0;           // テンプレートパターン

        $logicTemplate = new LogicTemplate( $this->app->dbAdapter );

        $fileName = sprintf( '%s_調整額明細.csv', date('Ymd') );

        $tmpFileName = $tmpFilePath . $fileName;

        $result = $logicTemplate->convertArraytoFile( $datas, $tmpFileName, $templateId, $templateClass, $seq, $templatePattern );

        if( $result == false ) {
            throw new \Exception( $logicTemplate->getErrorMessage() );
        }

        return $tmpFileName;
    }

    /**
     * 明細6CSV作成
     * @param $fixedDate 立替締め日
     * @param $tmpFilePath TEMP領域
     * @return ファイル名
     */
    private function createMeisai6( $fixedDate, $tmpFilePath ) {
        // 加盟店ID
        $entId = $this->app->authManager->getUserInfo()->EnterpriseId;

        // CSVデータ取得
        $sql = <<<Q_END
SELECT e.EnterpriseId
,      e.EnterpriseNameKj
,      pc.FixedDate
,      pc.ExecScheduleDate
,      pc.ChargeCount
,      s.SiteId
,      s.SiteNameKj
,      o.OrderId
,      o.Ent_OrderId
,      c.NameKj
,      o.ReceiptOrderDate
,      ( SELECT MAX( Deli_JournalIncDate ) FROM T_OrderItems WHERE OrderSeq = o.OrderSeq ) AS Deli_JournalIncDate
,      pc2.FixedDate AS FixedDate2
,      pas.UseAmount
,      cc.ReceiptAmountTotal
,      pbc.PayBackAmount
FROM   T_PayingControl pc
       INNER JOIN T_PayingBackControl pbc ON pbc.PayingControlSeq = pc.Seq
       INNER JOIN T_PayingAndSales pas ON pas.OrderSeq = pbc.OrderSeq
       INNER JOIN T_Order o ON o.OrderSeq = pbc.OrderSeq
       INNER JOIN T_Customer c ON c.OrderSeq = o.OrderSeq
       INNER JOIN T_Enterprise e ON e.EnterpriseId = pc.EnterpriseId
       INNER JOIN T_Site s ON s.SiteId = o.SiteId
       LEFT OUTER JOIN T_ClaimControl cc ON o.OrderSeq = cc.OrderSeq
       LEFT OUTER JOIN T_PayingControl pc2 ON pc2.Seq = pas.PayingControlSeq
WHERE  1 = 1
AND    pc.EnterpriseId = :EnterpriseId
AND    pc.FixedDate = :FixedDate
ORDER BY o.SiteId, o.OrderId
Q_END;

        // SQL用のパラメータ設定
        $prm = array( ':EnterpriseId' => $entId, ':FixedDate' => $fixedDate );
        $datas = ResultInterfaceToArray( $this->app->dbAdapter->query( $sql )->execute( $prm ) );

        // テンプレートヘッダー/フィールドマスタを使用して、CSVをResposeに入れる
        $templateId = 'CKA11019_6';     // テンプレートID       ご利用明細
        $templateClass = 2;             // 区分                 加盟店
        $seq = $entId;                  // シーケンス           加盟店ID
        $templatePattern = 0;           // テンプレートパターン

        $logicTemplate = new LogicTemplate( $this->app->dbAdapter );

        $fileName = sprintf( 'Meisai_6_%s.csv', date('YmdHis') );

        $tmpFileName = $tmpFilePath . $fileName;

        $result = $logicTemplate->convertArraytoFile( $datas, $tmpFileName, $templateId, $templateClass, $seq, $templatePattern );

        if( $result == false ) {
            throw new \Exception( $logicTemplate->getErrorMessage() );
        }

        return $tmpFileName;
    }
}
