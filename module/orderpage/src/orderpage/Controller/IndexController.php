<?php
namespace orderpage\Controller;

use orderpage\Application;
use models\Table\TableMypageTempRegist;
use models\Table\TableSystemProperty;
use models\Table\TableMypageToBackIF;
use models\View\MypageViewOrder;
use models\View\MypageViewCustomer;
use models\View\MypageViewSystemProperty;
use models\View\MypageViewCode;
use models\View\MypageViewReceiptControl;
use Coral\Base\BaseGeneralUtils;
use Coral\Coral\CoralCodeMaster;
use Coral\Coral\Controller\CoralControllerAction;
use Zend\Db\Adapter\Adapter;
use Zend\Validator\StringLength;
use Zend\Json\Json;
use models\Logic\LogicSbps;

class IndexController extends CoralControllerAction {
    protected $_componentRoot = './application/views/components';

    /**
     * @var Application
    */
    private $app;

    /**
     * @var string
    */
    private $title;

    /** */
	const SESSION_JOB_PARAMS = 'MYORDER_JOB_PARAMS';

    /**
     * 初期化処理
    */
    protected function _init() {
        $this->app = Application::getInstance();

        $this->userInfo = Application::getInstance()->authManager->getUserInfo();
        $this->altUserInfo = Application::getInstance()->authManager->getAlternativeUserInfo();

        // 購入者情報取得
        $mdlc = new MypageViewCustomer( $this->app->dbAdapter );
        $customerInfo = $mdlc->findCustomer( array( 'OrderSeq' => $this->userInfo->OrderSeq ) )->current();

        // OemId取得
        $mdlo = new MypageViewOrder( $this->app->dbAdapter );
        $oemId = $mdlo->find( $this->userInfo->OrderSeq )->current()['OemId'];

        // ページタイトルとスタイルシート、JavaScriptを設定
        $this->addJavaScript( '../js/prototype.js' )
            ->addJavaScript( '../js/bytefx.js' )
            ->addJavaScript( '../js/json+.js' )
            ->addJavaScript( '../js/corelib.js' )
            ->addJavaScript( '../js/base.ui.js' );

        if ($this->is_mobile_request()) {
            $this->addStyleSheet( './css_sp/orderpage.css' )
                 ->addStyleSheet( './css_sp/orderpage_index.css' );
        } else {
            $this->addStyleSheet( './css/orderpage.css' )
                 ->addStyleSheet( './css/orderpage_index.css' );
        }

        // タイトル文字取得
        $this->title = $this->altUserInfo ?
           sprintf( '%s (%s)　様', $customerInfo['NameKj'], $this->altUserInfo->NameKj ) :
           sprintf( '%s　様', $customerInfo['NameKj'] );

        $sql  = ' SELECT mc.ManCustId ';
        $sql .= ' FROM   MV_Customer c LEFT OUTER JOIN ';
        $sql .= '        MV_EnterpriseCustomer ec ON ( ec.EntCustSeq = c.EntCustSeq ) LEFT OUTER JOIN ';
        $sql .= '        MV_ManagementCustomer mc ON ( mc.ManCustId = ec.ManCustId ) ';
        $sql .= ' WHERE  c.EntCustSeq = :EntCustSeq ';
        $manCustId = $this->app->dbAdapter->query( $sql )->execute( array( ':EntCustSeq' => $customerInfo['EntCustSeq'] ) )->current()['ManCustId'];

        $oemId = empty( $oemId ) ? 0 : $oemId;

        // リンク生成
        if( $this->checkMypageRegist( $oemId, $manCustId) ) {
            // マイページ登録済
            $linkName = 'マイページログイン';
            $link = '';
        }
        else {
            $linkName = 'マイページ登録';

            if( !empty( $customerInfo['MailAddress'] ) ) {
                // メールアドレスあり → 本登録から
                $link = '/index/mypageregist';
                $registFlg = 1;
            }
            else {
                // メールアドレスなし → 仮登録から
                $link = '/regist/preregist/orderseq/' . $this->userInfo->OrderSeq;
            }
        }

        // リンクアサイン
        $this->view->assign( 'linkName', $linkName );
        $this->view->assign( 'link', $link );
        $this->view->assign( 'registFlg', $registFlg );
        // 購入者情報
        $this->view->assign( 'custInfo', $customerInfo );
    }

    /**
     * 購入履歴表示
    */
    public function indexAction() {

        $params = $this->getParams();
        if ( isset( $params['oseq'] ) && !empty( $params['oseq'] ) ) {
            if ( $params['oseq'] <> $this->userInfo->OrderSeq ) {
                $this->_redirect( 'login/login' );
                return;
            }
        }

        $orderSeq = $this->userInfo->OrderSeq;

        // 注文情報
        $sql = <<<EOQ
SELECT o.*
,      c.EntCustSeq
,      cc.ClaimDate
,      cc.ClaimCpId
,      cc.ClaimPattern
,      cc.LimitDate
,      cc.UseAmountTotal
,      cc.DamageDays
,      cc.DamageBaseDate
,      cc.DamageInterestAmount
,      cc.ClaimFee
,      cc.AdditionalClaimFee
,      cc.PrintedDate
,      cc.ClaimAmount
,      cc.ReceiptAmountTotal
,      cc.ClaimedBalance
,      cc.Clm_Count
,      cc.F_ClaimDate
,      cc.F_LimitDate
,      cc.ReissueClass
,      cc.ReissueRequestDate
,      cc.MypageReissueClass
,      cc.MypageReissueRequestDate
,      s.SiteNameKj
,      s.Url
,      DATE_FORMAT(cnl.CancelDate, '%Y-%m-%d') AS CancelDate
,      (SELECT MAX(ReceiptDate) FROM MV_ReceiptControl WHERE OrderSeq = o.OrderSeq AND ReceiptDate IS NOT NULL) AS MAXReceiptDate
,      e.EnterpriseNameKj
,      (SELECT ClaimDate FROM MV_ClaimHistory WHERE OrderSeq = o.OrderSeq Order By Seq DESC Limit 1) AS MAXClaimDate
,      (SELECT LimitDate FROM MV_ClaimHistory WHERE OrderSeq = o.OrderSeq Order By Seq DESC Limit 1) AS MAXLimitDate
,      c.PostalCode
,      c.UnitingAddress
,      ec.RequestStatus
,      ao.CreditTransferRequestFlg
,      ec.FfName
,      ec.FfBranchName
,      ec.FfAccountClass
,      ec.FfAccountNumber
,      ao.ExtraPayType
,      ao.ExtraPayKey
,      ao.ExtraPayNote
,      s.PaymentAfterArrivalFlg
,      s.ReceiptUsedFlg
,      (SELECT MAX(rc.ReceiptSeq)
          FROM MV_ReceiptControl AS rc
         WHERE rc.OrderSeq = o.OrderSeq
           AND ReceiptDate IS NOT NULL
       ) AS MaxReceiptSeq
,      (SELECT MIN(ch.ClaimDate) FROM MV_ClaimHistory AS ch WHERE ch.OrderSeq = o.OrderSeq AND ch.ClaimPattern = 1) AS MinClaimDate

,      (SELECT count(DISTINCT ss.PaymentId) FROM MV_SiteSbpsPayment ss
		INNER JOIN MV_SbpsPayment sp on (sp.SbpsPaymentId=ss.PaymentId)
		WHERE ss.SiteId=o.SiteId AND ss.ValidFlg=1 AND ss.UseStartDate <= :Now AND sp.PaymentName NOT LIKE '%credit%'
		AND DATE_ADD((SELECT MIN(ch.ClaimDate) FROM MV_ClaimHistory AS ch WHERE ch.OrderSeq = o.OrderSeq AND ch.ClaimPattern = 1), 
		INTERVAL ss.NumUseDay DAY) >= CURRENT_DATE()) AS AppCountPaymentMethod
		
,      (SELECT MAX(ss.NumUseDay) FROM MV_SiteSbpsPayment ss
		INNER JOIN MV_SbpsPayment sp on (sp.SbpsPaymentId=ss.PaymentId)
		WHERE ss.SiteId=o.SiteId AND ss.ValidFlg=1 AND ss.UseStartDate <= :Now AND sp.PaymentName NOT LIKE '%credit%'
		AND DATE_ADD((SELECT MIN(ch.ClaimDate) FROM MV_ClaimHistory AS ch WHERE ch.OrderSeq = o.OrderSeq AND ch.ClaimPattern = 1) ,
		INTERVAL ss.NumUseDay DAY) >= CURRENT_DATE()) AS AppMaxNumUseDay
		
,      (SELECT GROUP_CONCAT(sp.LogoUrl ORDER BY sp.SortId ASC) FROM MV_SbpsPayment sp
		INNER JOIN MV_SiteSbpsPayment ss on (ss.PaymentId=sp.SbpsPaymentId)		
		WHERE ss.SiteId=o.SiteId AND ss.ValidFlg=1 AND ss.UseStartDate <= :Now AND sp.PaymentName NOT LIKE '%credit%'
		AND DATE_ADD((SELECT MIN(ch.ClaimDate) FROM MV_ClaimHistory AS ch WHERE ch.OrderSeq = o.OrderSeq AND ch.ClaimPattern = 1), 
		INTERVAL ss.NumUseDay DAY) >= CURRENT_DATE()) AS AppLogoUrl
		
,      (SELECT count(DISTINCT ss.PaymentId) FROM MV_SiteSbpsPayment ss
		INNER JOIN MV_SbpsPayment sp on (sp.SbpsPaymentId=ss.PaymentId)
		WHERE ss.SiteId=o.SiteId AND ss.ValidFlg=1 AND ss.UseStartDate <= :Now AND sp.PaymentName LIKE '%credit%'
		AND DATE_ADD((SELECT MIN(ch.ClaimDate) FROM MV_ClaimHistory AS ch WHERE ch.OrderSeq = o.OrderSeq AND ch.ClaimPattern = 1), 
		INTERVAL ss.NumUseDay DAY) >= CURRENT_DATE()) AS CreditCountPaymentMethod
		
,      (SELECT MAX(ss.NumUseDay) FROM MV_SiteSbpsPayment ss
		INNER JOIN MV_SbpsPayment sp on (sp.SbpsPaymentId=ss.PaymentId)
		WHERE ss.SiteId=o.SiteId AND ss.ValidFlg=1 AND ss.UseStartDate <= :Now AND sp.PaymentName LIKE '%credit%'
		AND DATE_ADD((SELECT MIN(ch.ClaimDate) FROM MV_ClaimHistory AS ch WHERE ch.OrderSeq = o.OrderSeq AND ch.ClaimPattern = 1), 
		INTERVAL ss.NumUseDay DAY) >= CURRENT_DATE()) AS  CreditMaxNumUseDay
		
,      (SELECT GROUP_CONCAT(sp.LogoUrl ORDER BY sp.SortId ASC) FROM MV_SbpsPayment sp
		INNER JOIN MV_SiteSbpsPayment ss on (ss.PaymentId=sp.SbpsPaymentId)
		WHERE ss.SiteId=o.SiteId AND ss.ValidFlg=1 AND ss.UseStartDate <= :Now AND sp.PaymentName LIKE '%credit%'
		AND DATE_ADD((SELECT MIN(ch.ClaimDate) FROM MV_ClaimHistory AS ch WHERE ch.OrderSeq = o.OrderSeq AND ch.ClaimPattern = 1), 
		INTERVAL ss.NumUseDay DAY) >= CURRENT_DATE()) AS CreditLogoUrl

,      (SELECT GROUP_CONCAT(p.LogoUrl ORDER BY p.SortId ASC) FROM MV_Payment p
		INNER JOIN MV_SitePayment sp on (sp.PaymentId=p.PaymentId)
		WHERE sp.SiteId=o.SiteId AND sp.ValidFlg=1 AND sp.UseStartFixFlg = 1 AND sp.UseFlg = 1) AS CombiniLogoUrl
,      s.SpecificTransUrl
FROM   MV_Order o INNER JOIN
       MV_Customer c ON ( c.OrderSeq = o.OrderSeq ) LEFT OUTER JOIN
       MV_ClaimControl cc ON ( cc.OrderSeq = o.OrderSeq ) LEFT OUTER JOIN
       MV_Site s ON ( s.SiteId = o.SiteId ) LEFT OUTER JOIN
       MV_Cancel cnl ON ( cnl.OrderSeq = o.OrderSeq AND cnl.ValidFlg = 1 ) LEFT OUTER JOIN
       MV_Enterprise e ON ( e.EnterpriseId = s.EnterpriseId ) LEFT JOIN 
       MV_EnterpriseCustomer AS ec ON c.EntCustSeq = ec.EntCustSeq LEFT JOIN 
       MAV_Order AS ao ON c.OrderSeq = ao.OrderSeq
WHERE  o.ValidFlg = 1 AND
       c.ValidFlg = 1 AND
       o.OrderSeq = :OrderSeq
EOQ;

        $prm = array( ':OrderSeq' => $orderSeq, ':Now' => date('Y-m-d H:i:s'));
        $order = $this->app->dbAdapter->query( $sql )->execute( $prm )->current();
        //$logicSbps = new LogicSbps($this->app->dbAdapter);
        //$order['countSite'] = $logicSbps->checkHasPaymentAfterArrivalFlg($order['EnterpriseId'], 'MV_Site');

        // 注文状態
        $sql = <<<EOQ
SELECT o.OrderSeq
,      o.Cnl_Status
,      o.DataStatus
,      o.Rct_Status
FROM   MV_Order o
WHERE  o.P_OrderSeq = :OrderSeq
ORDER BY o.OrderSeq
EOQ;
        $stm = $this->app->dbAdapter->query( $sql );
        $prm = array( ':OrderSeq' => $order['OrderSeq'] );
        $orderStatuses = ResultInterfaceToArray( $stm->execute( $prm ) );
        $cnlStatusJudge = array(0 => 0, 1 => 0, 2 => 0);
        $DataStatusJudge = 0;
        $RctStatusJudge = 0;
        foreach( $orderStatuses as $orderSt ) {
            // キャンセル状態
            $cnlStatusJudge[$orderSt['Cnl_Status']]++;

            // データステータス
            if ($orderSt['Cnl_Status'] == 0 && $DataStatusJudge < $orderSt['DataStatus']) {
                $DataStatusJudge = $orderSt['DataStatus'];
            }

            // 入金済み
            if ($orderSt['Rct_Status'] == 1) {
                $RctStatusJudge++;
            }
        }

        // キャンセル状態
        // 全明細がキャンセル済み→キャンセル済み
        $orderStatusesCount = 0;
        if( !empty($orderStatuses) ){
            $orderStatusesCount = count($orderStatuses);
        }

        if ($cnlStatusJudge[2] == $orderStatusesCount) {
            $orderStatus['Cnl_Status'] = 2;
        }
        // 全明細がキャンセル申請以降→キャンセル申請中
        elseif (($cnlStatusJudge[2] + $cnlStatusJudge[1]) == $orderStatusesCount) {
            $orderStatus['Cnl_Status'] = 1;
        }
        else {
            $orderStatus['Cnl_Status'] = 0;
        }

        // データステータス
        // 有効な注文で最大のステータスを使用。全てキャンセルされている場合は先頭を使用
        $orderStatus['DataStatus'] = $DataStatusJudge > 0 ? $DataStatusJudge : $orderStatuses[0]['DataStatus'];

        // 1レコードでも入金済みがあれば入金済み
        $orderStatus['Rct_Status'] = $RctStatusJudge > 0 ? 1 : 0;

        // 注文商品と配送先取得
        $sql = <<<EOQ
SELECT i.OrderSeq
,      i.ItemNameKj
,      i.UnitPrice
,      i.ItemNum
,      i.SumMoney
,      i.DataClass
,      dd.DestNameKj
,      dd.PostalCode
,      dd.UnitingAddress
,      dd.Phone
,      o.Cnl_Status
FROM   MV_OrderItems i INNER JOIN
       MV_Order o ON o.OrderSeq = i.OrderSeq INNER JOIN
       MV_DeliveryDestination dd ON dd.DeliDestId = ( SELECT DeliDestId FROM MV_OrderItems WHERE OrderSeq = o.OrderSeq AND DataClass = 1 AND ValidFlg = 1 LIMIT 1 )
WHERE  o.P_OrderSeq = :OrderSeq AND
       i.ValidFlg = 1 AND
       o.ValidFlg = 1 AND
       dd.ValidFlg = 1
ORDER BY i.DataClass, i.OrderItemId
EOQ;

        $stm = $this->app->dbAdapter->query( $sql );
        $prm = array( ':OrderSeq' => $orderSeq );
        $orderItems = ResultInterfaceToArray( $stm->execute( $prm ) );

        // 送料 と 決済手数料 と 税額 算出
        foreach( $orderItems as $orderItem ) {
            $orderSeqs = $orderItem['OrderSeq'];
            if( $orderItem['DataClass'] == 1 ) {
                $deliDests[$orderSeqs][] = $orderItem;
            }
            if( $orderItem['DataClass'] == 2 ) {
                if ($orderStatus['Cnl_Status'] == 0) {
                    $sumCarriage += $orderItem['Cnl_Status'] == 0 ? $orderItem['SumMoney'] : 0;
                }
                else {
                    $sumCarriage += $orderItem['SumMoney'];
                }
            }
            else if( $orderItem['DataClass'] == 3 ) {
                if ($orderStatus['Cnl_Status'] == 0) {
                    $sumSettlementFee += $orderItem['Cnl_Status'] == 0 ? $orderItem['SumMoney'] : 0;
                }
                else {
                    $sumSettlementFee += $orderItem['SumMoney'];
                }
            }
            else if( $orderItem['DataClass'] == 4 ) {
                if ($orderStatus['Cnl_Status'] == 0) {
                    $sumTax += $orderItem['Cnl_Status'] == 0 ? $orderItem['SumMoney'] : 0;
                }
                else {
                    $sumTax += $orderItem['SumMoney'];
                }
            }

            if ($orderStatus['Cnl_Status'] == 0) {
                $orderClaimAmount += $orderItem['Cnl_Status'] == 0 ? $orderItem['SumMoney'] : 0;
            }
            else {
                $orderClaimAmount += $orderItem['SumMoney'];
            }
        }

        // 請求金額に再請求手数料、遅延損害金、追加請求手数料を含める
        $orderClaimAmount += nvl($order['ClaimFee'], 0) + nvl($order['DamageInterestAmount'], 0) + nvl($order['AdditionalClaimFee'], 0);

        // 注文マイページｱｸｾｽ用URLからの遷移か否か
        $accessKeyValidToDate = null;
        if (isset($_SESSION['loginAccessId'])) {
            $accessKeyValidToDate = $this->userInfo->AccessKeyValidToDate;
        }

        // 顧客情報を取得する
$sql = <<<EOQ
SELECT  ec.NameKj
     ,  ec.NameKn
     ,  ec.MailAddress
     ,  ec.EntCustSeq
FROM    MV_Order o
            INNER JOIN MV_Customer c
                    ON (c.OrderSeq = o.OrderSeq)
            INNER JOIN MV_EnterpriseCustomer ec
                    ON (ec.EntCustSeq = c.EntCustSeq)
WHERE  o.OrderSeq =:OrderSeq
EOQ;
        $prm = array(
                ':OrderSeq' => $orderSeq,
        );
        $list = $this->app->dbAdapter->query($sql)->execute($prm)->current();

        $list['phoneClass'] = ($this->isCelAddress($list['MailAddress']) == true) ? 1 : 0;  // 1:モバイルメール  0: PCメール

        $params = $this->getParams();
        if ($params["smbc_res"] == 1) {
            $list['ErrorMessage'] = '口座振替の申込みが完了しました';
        }
        else if ($params["smbc_res"] == 2) {
            $list['ErrorMessage'] = '口座振替の登録でエラーが発生しました。'.'</br>'.'　口座情報を確認し、再度お手続きください。';
        }

        // システム条件から情報を取得する
        $mdlsp = new \models\Table\TableSystemProperty($this->app->dbAdapter);
        $list['syuno_co_cd'] = $mdlsp->getValue(\models\Table\TableSystemProperty::DEFAULT_MODULE, 'systeminfo', 'CATSSyunoCoCode');
        $list['shop_cd'] = $mdlsp->getValue(\models\Table\TableSystemProperty::DEFAULT_MODULE, 'systeminfo', 'CATSShopCode');
        $list['shop_pwd'] = $mdlsp->getValue(\models\Table\TableSystemProperty::DEFAULT_MODULE, 'systeminfo', 'CATSShopPassword');

        // 書式変換
        $sendNameKn = mb_convert_kana($list['NameKn'],'kvrnhas');
        $sendNameKj = mb_convert_kana($list['NameKj'],'KVRNAS');
        $sendSiteNameKj = mb_convert_kana($order['SiteNameKj'],'KVRNAS');
        // エンコード（ UTF-8 → sjis-win ）
        $sendNameKj = mb_convert_encoding($sendNameKj, 'sjis-win', 'UTF-8');
        $sendSiteNameKj = mb_convert_encoding($sendSiteNameKj, 'sjis-win', 'UTF-8');
        // バイト制限
        $sendNameKj = mb_strcut($sendNameKj, 0, 60, 'sjis-win');
        $sendSiteNameKj = mb_strcut($sendSiteNameKj, 0, 80, 'sjis-win');
        // エンコード戻し（ sjis-win → UTF-8 ）
        $sendNameKj = mb_convert_encoding($sendNameKj, 'UTF-8', 'sjis-win');
        $sendSiteNameKj = mb_convert_encoding($sendSiteNameKj, 'UTF-8', 'sjis-win');

        //受け渡し準備
        $list['ConvertNameKn'] = $sendNameKn;
        $list['ConvertNameKj'] = $sendNameKj;
        $list['ConvertSiteNameKj'] = $sendSiteNameKj;

        // 口座振替情報の登録状況を確認しリンクの表示を制御する（WEB申込み＋未申請、申請中、中止）
        $order['Url_Credit'] = null;
        if ((empty($order['RequestStatus']) || $order['RequestStatus'] == 1 || $order['RequestStatus'] == 9) && $order['CreditTransferRequestFlg'] == 1)
        {
            $order['Url_Credit'] = $mdlsp->getValue(\models\Table\TableSystemProperty::DEFAULT_MODULE, 'systeminfo', 'CATSShopUrl');
        }

        // 申請が完了の場合のみとする
        if ($order['RequestStatus'] == 2 && $order['CreditTransferRequestFlg'] == 1)
        {
            //口座番号はマスクする
            $acNumberEdit = substr($order['FfAccountNumber'], (mb_strlen($order['FfAccountNumber']) - 2));
            $acNumberEdit = str_pad($acNumberEdit, 15, "*", STR_PAD_LEFT);
            $order['FfAccountNumber'] = $acNumberEdit;
            //預金種目の名称取得
            $order['FfAccountClassKeyContent'] = null;
            $sql = "SELECT * FROM MV_Code WHERE CodeId = 51 AND KeyCode = :KeyCode";
            $prm = array(
                    ':KeyCode' => $order['FfAccountClass'],
            );
            $mvCode = $this->app->dbAdapter->query($sql)->execute($prm)->current();
            foreach ($mvCode as $row) {
                $order['FfAccountClassKeyContent'] = $mvCode['KeyContent'];
            }
        } else {
            $order['FfName'] = null;
            $order['FfBranchName'] = null;
            $order['FfAccountClass'] = null;
            $order['FfAccountNumber'] = null;
            $order['FfAccountClassKeyContent'] = null;
        }

        // クレジット決済期間確認
        $mvcd = new MypageViewCode( $this->app->dbAdapter );
        $creditSettlementDays = $mvcd->find('199', '0')->current()['Note'];
        $creditSettlementDays = preg_replace( '/[^0-9]/', '', $creditSettlementDays );
        $minClaimDate = $order['MinClaimDate'];
        if (empty($creditSettlementDays)) {
            $creditSettlementDays = 0;
        }
        $judgeDate = date('Ymd', strtotime($minClaimDate. '+'. $order['CreditMaxNumUseDay']. ' days') );
        $sysDate = date('Ymd');
        $creditSettlementDaysJudge = ($sysDate > $judgeDate) ? '1' : '0';
        $order['CreditLimitDate'] = $judgeDate;

        // 領収書印刷回数
        $sql = "";
        $sql .= " SELECT COUNT(*) AS ReceiptIssueCount";
        $sql .= " FROM T_ReceiptIssueHistory AS rih";
        $sql .= " WHERE rih.OrderSeq = :OrderSeq";
        $sql .= " AND rih.ValidFlg = 1";
        $ri = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $order['OrderSeq']))->current();
        $claimCount = $ri['ReceiptIssueCount'];

        // クレジット決済ステータス
        $mdlMyToBack = new TableMypageToBackIF($this->app->dbAdapter);
        $prmMTB = array(
            'OrderSeq' => $order['OrderSeq'],
            'IFClass' => '4',
            'ValidFlg' => '1',
        );
        if ( empty($order['ExtraPayNote']) ) {
            // 追加支払い方法備考が空欄（null）の場合、連携テーブルを確認する
            $myToBackCount = $mdlMyToBack->findMypageToBackIF($prmMTB, false)->count();
            if ($myToBackCount > 0) {
                $order['CreditSettlementStatus'] = '2' ;
            } else {
                $order['CreditSettlementStatus'] = '0' ;
            }

        } else {
            // 追加支払い方法区分が 1:クレジット の場合
            if ( $order['ExtraPayType'] == '1' ) {
                $order['CreditSettlementStatus'] = '2' ;
            } else {
                $order['CreditSettlementStatus'] = '0' ;
            }

        }

        //クレジット決済利用表示内容制御
        if ( $order['CreditSettlementStatus'] == 2 ) {
            if ($order['ReceiptUsedFlg'] == 1) {
                //クレジット決済完了済
                $order['CreditSettlementComment'] = $mvcd->find('199', '3')->current()['Note'];
                $order['CreditSettlementButton'] = '1';
                $order['CreditSettlementButtonUrl'] = "";
                if ( $claimCount >= 1 ) {
                    $order['CreditSettlementButtonName'] = '領収書再発行';
                } else {
                    $order['CreditSettlementButtonName'] = '領収書発行';
                }
            } else {
                $order['CreditSettlementButton'] = '0';
                $order['CreditSettlementButtonName'] = '';
                $order['CreditSettlementButtonUrl'] = '';
            }
        } else if ( $order['CreditCountPaymentMethod'] < 1 
            || $order['UseAmountTotal'] != $order['ClaimedBalance'] 
            || $order['UseAmountTotal'] != $order['ClaimAmount'] 
            || $creditSettlementDaysJudge == '1' 
            || $orderStatus['DataStatus'] == 91 
            || $orderStatus['Cnl_Status'] > 0 ) {
            //クレジット決済利用不可
            //クレジット決済利用期間超過
            $order['CreditSettlementComment'] = $mvcd->find('199', '1')->current()['Note'];
            $order['CreditSettlementButton'] = '0';
            $order['CreditSettlementButtonName'] = '';
            $order['CreditSettlementButtonUrl'] = '';

        } else if ( $order['CreditSettlementStatus'] == 1 ) {
            //クレジット決済申請中
            $order['CreditSettlementComment'] = $mvcd->find('199', '2')->current()['Note'];
            $order['CreditSettlementButton'] = '0';
            $order['CreditSettlementButtonName'] = '';
            $order['CreditSettlementButtonUrl'] = '';

        } else {
            //クレジット決済可能
            $order['CreditSettlementComment'] = $mvcd->find('199', '4')->current()['Note'];
            $order['CreditSettlementButton'] = '2';
            $order['CreditSettlementButtonName'] = '次へ';
            $order['CreditSettlementButtonUrl'] = "onclick=\"window.open('creditsettlement/input?oseq=". $orderSeq. "', '');\"";

        }
        
        $judgeAppDate = date('Ymd', strtotime($minClaimDate. '+'. $order['AppMaxNumUseDay']. ' days') );
        $sysDate = date('Ymd');
        $appSettlementDaysJudge = ($sysDate > $judgeAppDate) ? '1' : '0';
        $order['AppLimitDate'] = $judgeAppDate;
        //アプリ決済利用表示内容制御
        if ( $order['AppCountPaymentMethod'] < 1 
            || $order['UseAmountTotal'] != $order['ClaimedBalance'] 
            || $order['UseAmountTotal'] != $order['ClaimAmount'] 
            || $appSettlementDaysJudge == '1' 
            || $orderStatus['DataStatus'] == 91 
            || $orderStatus['Cnl_Status'] > 0 ) {
            //アプリ決済利用不可
            //アプリ決済利用期間超過
            $order['AppSettlementButton'] = '0';
        } else {
            //アプリ決済可能
            $order['AppSettlementButton'] = '2';
        }

        // スマホ用入金方法等
        $order['ReceiptClassName'] = '';
        $order['ReceiptDate'] = '';
        if ( $order['CreditSettlementStatus'] == 2 ) {
            // 届いてから処理済
            $sql = <<<Q_END
SELECT
  sbps.MailParameterNameKj as "Payment"
, rc.ReceiptDate
  FROM MV_Order AS ord 
 INNER JOIN T_SbpsReceiptControl AS rc ON (rc.OrderSeq = ord.OrderSeq)
 INNER JOIN MV_SbpsPayment AS sbps ON (rc.PaymentName = sbps.PaymentName AND sbps.OemId=ord.OemId)
 WHERE ord.OrderSeq = :OrderSeq
 AND rc.PayType = 1
 AND rc.ValidFlg = 1
Q_END;
            $orderClaim = $this->app->dbAdapter->query( $sql )->execute( $prm )->current();
            $order['ReceiptClassName'] = $orderClaim['Payment'];
            $order['ReceiptDate'] = $orderClaim['ReceiptDate'];

        } else if ( $orderStatus['Rct_Status'] == 1 && $order['ClaimedBalance'] <= 0 ) {
            // 入金済みかつ残金無し
            $mdlrc = new MypageViewReceiptControl($this->app->dbAdapter);
            $receiptInfo = $mdlrc->find($order['MaxReceiptSeq'])->current();
            $order['ReceiptClassName'] = $mvcd->find('198', $receiptInfo['ReceiptClass'])->current()['KeyContent'];
            $order['ReceiptDate'] = $receiptInfo['ReceiptDate'];

        }

        // スマホ用支払方法リンクURL
        $order['CreditUrl']  = 'creditsettlement/input?oseq='. $orderSeq;
        $order['SbpsUrl']  = 'sbpssettlement/index?oseq='. $orderSeq;
        $order['LINEPayUrl'] = $mvcd->find('199', '20')->current()['Note'];
        $order['PayPayUrl']  = $mvcd->find('199', '21')->current()['Note'];
        $order['CVSUrl']     = $mvcd->find('199', '22')->current()['Note'];
        $order['PostUrl']    = $mvcd->find('199', '23')->current()['Note'];
        $order['BankUrl']    = $mvcd->find('199', '24')->current()['Note'];
        $order['PayHelpUrl'] = $mvcd->find('199', '99')->current()['Note'];

        // CB_B2C_DEV-213：届いてから払いの過渡期の表示
        // 未入金、且つ、着荷済みの場合は従来のレイアウト表示を行うため、あえてPaymentAfterArrivalFlgをOffにする
        if (($order['PaymentAfterArrivalFlg'] == 1) && ($order['Deli_ConfirmArrivalFlg'] == 1) && ($orderStatus['Rct_Status'] == 0)) {
            $order['PaymentAfterArrivalFlg'] = 0;
        }

        // ManCustId取得
        $this->view->assign( 'custid', $this->userInfo->ManCustId );

        $this->setPageTitle( 'ご購入情報' );
        $this->view->assign( 'userInfo', $this->title );
        $this->view->assign( 'OrderSeq', $orderSeq );
        $this->view->assign( 'order', $order );
        $this->view->assign( 'orderItems', $orderItems );
        $this->view->assign( 'sumCarriage', $sumCarriage );
        $this->view->assign( 'sumSettlementFee', $sumSettlementFee );
        $this->view->assign( 'sumTax', $sumTax );
        $this->view->assign( 'orderClaimAmount', $orderClaimAmount);
        $this->view->assign( 'deliList', $deliDests );
        $this->view->assign( 'orderStatus', $orderStatus);
        $this->view->assign( 'accessKeyValidToDate', $accessKeyValidToDate);
        $this->view->assign( 'list', $list );

        return $this->view;
    }

    /**
     * マイページ登録済み判定
     * @param $oemId OemId
     * @param $manCustId ManCustId
     * @return bool true: 登録済 / false: 未登録
     */
    private function checkMypageRegist( $oemId, $manCustId ) {
        $sql = 'SELECT * FROM T_MypageCustomer WHERE IFNULL( OemId, 0 ) = :OemId AND ManCustId = :ManCustId AND ValidFlg = 1 ';
        $prm = array(
                ':OemId' => $oemId,
                ':ManCustId' => $manCustId
        );

        $mypageInfo = $this->app->dbAdapter->query( $sql )->execute( $prm )->current();

        if( !$mypageInfo ) {
            return false;
        }
        else {
            return true;
        }
    }


    /**
     * マイページ仮登録済み判定
     * @param $oemId OemId
     * @param $mailAddress MailAddress
     * @return UrlParameter / false: 未登録
     */
    private function checkMypagePreregist( $oemId, $mailAddress ) {
        $sql = 'SELECT * FROM T_MypageTempRegist WHERE IFNULL( OemId, 0 ) = :OemId AND MailAddress = :MailAddress AND ValidFlg = 1 ';
        $prm = array(
                ':OemId' => $oemId,
                ':MailAddress' => $mailAddress
        );

        $mypageTempInfo = $this->app->dbAdapter->query( $sql )->execute( $prm )->current();

        if( !$mypageTempInfo ) {
            // 未仮登録
            return false;
        }
        else {
            return $mypageTempInfo['UrlParameter'];
        }
    }

    /**
     * マイページ仮登録
     * @param $oemId OemId
     * @param $mailAddress MailAddress
     * @param $orderSeq OrderSeq
     * @return UrlParameter / false (登録済み)
     */
    private function mypagePreregist( $oemId, $mailAddress, $orderSeq ) {
        // システムプロパティからハッシュキー取得
        $mdlsp = new MypageViewSystemProperty( $this->app->dbAdapter );
        $hash_salt = $mdlsp->getValue( 'mypage', 'url', 'hash_salt' );

        $sql  = " INSERT INTO T_MypageTempRegist ( OemId, MailAddress, UrlParameter, CreateDate, ValidDate, OrderSeq, RegistDate, UpdateDate, ValidFlg ) VALUES (";
        $sql .= "   :OemId ";
        $sql .= " , :MailAddress ";
        $sql .= " , :UrlParameter ";
        $sql .= " , :CreateDate ";
        $sql .= " , :ValidDate ";
        $sql .= " , :OrderSeq ";
        $sql .= " , :RegistDate ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->app->dbAdapter->query( $sql );

        // 作成日時
        $createDate = date( 'Y-m-d H:i:s' );
        // 有効日時
        $validDate = date( 'Y-m-d H:i:s', strtotime( '+ 1 day' ) );
        // UrlParameter
        $urlParameter = hash( 'sha256', sprintf( '%s%s%s', $mailAddress, $createDate, $hash_salt ) );

        $prm = array(
                ':OemId' => $oemId,
                ':MailAddress' => $mailAddress,
                ':UrlParameter' => $urlParameter,
                ':CreateDate' => $createDate,
                ':ValidDate' => $validDate,
                ':OrderSeq' => $orderSeq,
                ':RegistDate' => date( 'Y-m-d H:i:s' ),
                ':UpdateDate' => date( 'Y-m-d H:i:s' ),
                ':ValidFlg' => 1,
        );

        $ri = $stm->execute( $prm );

        return $urlParameter;
    }

    /**
     * マイページ登録
     */
    function mypageregistAction() {
        // 購入者情報取得
        $mdlc = new MypageViewCustomer( $this->app->dbAdapter );
        $customerInfo = $mdlc->findCustomer( array( 'OrderSeq' => $this->userInfo->OrderSeq ) )->current();

        // OemId取得
        $mdlo = new MypageViewOrder( $this->app->dbAdapter );
        $oemId = $mdlo->find( $this->userInfo->OrderSeq )->current()['OemId'];

        // マイページ仮登録検索
        $mypageTempRegist = new TableMypageTempRegist($this->app->dbAdapter);
        $cond = array(
            'OemId' => $oemId,
            'MailAddress' => $customerInfo['MailAddress'],
            'OrderSeq' => $this->userInfo->OrderSeq,
        );
        $temp = $mypageTempRegist->findTempRegist($cond, false)->current();
        // 有効で期限内のレコードがある場合、それを使用
        if ($temp != false && strtotime( date( 'Y-m-d H:i:s' ) ) <= strtotime( $temp['ValidDate'] ) && $temp['ValidFlg'] == 1) {
            $param = $temp['UrlParameter'];
        }
        else {
            // マイページ仮登録実行
            $param = $this->mypagePreregist( $oemId, $customerInfo['MailAddress'], $this->userInfo->OrderSeq );
        }

        // 本登録画面へ遷移
        $url = str_replace( '/orderpage', '/mypage', $this->getBaseUrl() ) . '/regist/regist/token/' . $param;
        return $this->redirect()->toUrl($url);
    }

    /**
     * 携帯アドレスか否かをチェックする
     *
     * @param string $celAddress メールアドレス
     * @return boolean
     */
    private function isCelAddress($celAddress)
    {
        $celAddresses = array('docomo.ne.jp','vodafone.ne.jp','ezweb.ne.jp','softbank.ne.jp','pdx.ne.jp','fishbone.tv');

        return BaseGeneralUtils::isMatchAddress($celAddress, $celAddresses);
    }

    /**
     * 顧客氏名カナを半角に変換する。
     *
     */
    public function isValidNameKnAction()
    {
        $params = $this->getParams();

        $encName = mb_internal_encoding();

        // 全角→半角
        $billKana1 = $params['bill_kana'];
        $billKana2 = mb_convert_kana($billKana1, "kvrnhas");
        $billKana3 = mb_substr($billKana2, 0, 30);

        // 正規表現パターン設定（半角用）
        $pregPtrn = "";
        $pregPtrn .= "/[^";
        $pregPtrn .= "0-9"; // 数字
        $pregPtrn .= "A-Z"; // 大英字
        $pregPtrn .= "a-z"; // 小英字
        $pregPtrn .= "ｱｲｳｴｵｶｷｸｹｺｻｼｽｾｿﾀﾁﾂﾃﾄﾅﾆﾇﾈﾉﾊﾋﾌﾍﾎﾏﾐﾑﾒﾓﾔﾕﾖﾗﾘﾙﾚﾛﾜｦﾝｧｨｩｪｫｬｭｮｯ"; // カタカナ
        $pregPtrn .= "ﾞﾟ().\/\-\ "; // 記号
        $pregPtrn .= "]/";
        // 正規表現チェック（半角：有効文字のみに変換）
        //$billKanaPreg = preg_replace( '/[^\x30-\x39\x41-\x5A\x61-\x7A\x28\x29\x2E\x2F\x2D\xA6-\xDF\x20]/', '', $billKana3 );
        $billKanaPreg = preg_replace( $pregPtrn, '', $billKana3 );

        // 氏名漢字とサイト名
        $billName = $params['bill_name'];
        $shopName = $params['shop_name'];

        // 正規表現チェック（全角：有効文字のみに変換）
        $billNameEreg = $this->checkDoubleByteCharacter( $billName );
        $shopNameEreg = $this->checkDoubleByteCharacter( $shopName );

// $this->app->logger->debug('$billKana    ='. $billKana3);
// $this->app->logger->debug('$billKanaPreg='. $billKanaPreg);
// $this->app->logger->debug('$billName    ='. $billName);
// $this->app->logger->debug('$billNameEreg='. $billNameEreg);
// $this->app->logger->debug('$shopName    ='. $shopName);
// $this->app->logger->debug('$shopNameEreg='. $shopNameEreg);

        // エラーチェック
        if ( empty($billKana1) ) {
            // 氏名カナが未入力
            $msg['bill_kana'] = '';
            $msg['bill_kana_msg'] = '氏名カナは必須入力です。';

        } else if ( $billKana2 != $billKanaPreg ) {
            // 対象外の文字を除外した情報と一致しない場合
            $msg['bill_kana'] = $billKana3;
            $msg['bill_kana_msg'] = '氏名カナの入力が不正です。';

        } else if ( ( $billName != $billNameEreg ) ) {
            // 対象外の文字を除外した情報と一致しない場合
            $msg['bill_kana'] = $billKana3;
            $msg['bill_kana_msg'] = '氏名漢字に不正な文字が存在します。キャッチボールへお問い合わせください。';

        } else if ( ( $shopName != $shopNameEreg ) ) {
            // 対象外の文字を除外した情報と一致しない場合
            $msg['bill_kana'] = $billKana3;
            $msg['bill_kana_msg'] = 'サイト名に不正な文字が存在します。キャッチボールへお問い合わせください。';

        } else {
            $msg['bill_kana'] = $billKana3;
            $msg['bill_kana_msg'] = '';

        }

        echo \Zend\Json\Json::encode($msg);
        return $this->response;
    }

    /**
     * 全角用正規表現チェック
     *
     * @param string $targetText 対象文字列
     * @return string 有効な文字列のみ返す
     */
    private function checkDoubleByteCharacter($targetText)
    {
        $eregPtrns = array(
            'etc0'      => '　、。，．・：；！゛゜´｀¨＾￣＿ヽヾゝゞ〃仝々〆〇ー―‐／＼～∥｜…‥‘’“”（）〔〕［］｛｝〈〉《》「」『』【】＋－±×÷＝≠＜＞≦≧∞∴♂♀°′″℃￥＄￠￡％＃＆＊＠§☆★○●◎◇',
            'etc1'      => '０１２３４５６７８９ＡＢＣＤＥＦＧＨＩＪＫＬＭＮＯＰＱＲＳＴＵＶＷＸＹＺｚａｂｃｄｅｆｇｈｉｊｋｌｍｎｏｐｑｒｓｔｕｖｗｘｙｚぁあぃいぅうぇえぉおかがきぎくぐけげこごさざしじすずせぜそぞただちぢっつづてでとどなにぬねのはばぱひびぴふぶぷへべぺほぼぽまみむめもゃやゅゆょよらりるれろゎわゐゑをんァアィイ',
            'etc2'      => 'ゥウェエォオカガキギクグケゲコゴサザシジスズセゼソゾタダチヂッツヅテデトドナニヌネノハバパヒビピフブプヘベペホボポマミムメモャヤュユョヨラリルレロヮワヰヱヲンヴヵヶ',
            'SJIS_1_00' => '亜唖娃阿哀愛挨姶逢葵茜穐悪握渥旭葦芦鯵梓圧斡扱宛姐虻飴絢綾鮎或粟袷安庵按暗案闇鞍杏以伊位依偉囲夷委威尉惟意慰易椅為畏異移維緯胃萎衣謂違遺医井亥域育郁磯一壱溢逸稲茨芋鰯允印咽員因姻引飲淫胤蔭院陰隠韻吋右宇烏羽迂雨卯鵜窺丑碓臼渦嘘唄欝蔚鰻姥厩浦瓜閏噂云運雲荏餌叡営嬰影映曳栄永泳洩瑛盈穎頴英衛詠鋭液疫益駅',
            'SJIS_1_01' => '悦謁越閲榎厭円園堰奄宴延怨掩援沿演炎焔煙燕猿縁艶苑薗遠鉛鴛塩於汚甥凹央奥往応押旺横欧殴王翁襖鴬鴎黄岡沖荻億屋憶臆桶牡乙俺卸恩温穏音下化仮何伽価佳加可嘉夏嫁家寡科暇果架歌河火珂禍禾稼箇花苛茄荷華菓蝦課嘩貨迦過霞蚊俄峨我牙画臥芽蛾賀雅餓駕介会解回塊壊廻快怪悔恢懐戒拐改魁晦械海灰界皆絵芥蟹開階貝凱劾外咳害',
            'SJIS_1_02' => '崖慨概涯碍蓋街該鎧骸浬馨蛙垣柿蛎鈎劃嚇各廓拡撹格核殻獲確穫覚角赫較郭閣隔革学岳楽額顎掛笠樫橿梶鰍潟割喝恰括活渇滑葛褐轄且鰹叶椛樺鞄株兜竃蒲釜鎌噛鴨栢茅萱粥刈苅瓦乾侃冠寒刊勘勧巻喚堪姦完官寛干幹患感慣憾換敢柑桓棺款歓汗漢澗潅環甘監看竿管簡緩缶翰肝艦莞観諌貫還鑑間閑関陥韓館舘丸含岸巌玩癌眼岩翫贋雁頑顔願',
            'SJIS_1_03' => '企伎危喜器基奇嬉寄岐希幾忌揮机旗既期棋棄機帰毅気汽畿祈季稀紀徽規記貴起軌輝飢騎鬼亀偽儀妓宜戯技擬欺犠疑祇義蟻誼議掬菊鞠吉吃喫桔橘詰砧杵黍却客脚虐逆丘久仇休及吸宮弓急救朽求汲泣灸球究窮笈級糾給旧牛去居巨拒拠挙渠虚許距鋸漁禦魚亨享京供侠僑兇競共凶協匡卿叫喬境峡強彊怯恐恭挟教橋況狂狭矯胸脅興蕎郷鏡響饗驚仰',
            'SJIS_1_04' => '凝尭暁業局曲極玉桐粁僅勤均巾錦斤欣欽琴禁禽筋緊芹菌衿襟謹近金吟銀九倶句区狗玖矩苦躯駆駈駒具愚虞喰空偶寓遇隅串櫛釧屑屈掘窟沓靴轡窪熊隈粂栗繰桑鍬勲君薫訓群軍郡卦袈祁係傾刑兄啓圭珪型契形径恵慶慧憩掲携敬景桂渓畦稽系経継繋罫茎荊蛍計詣警軽頚鶏芸迎鯨劇戟撃激隙桁傑欠決潔穴結血訣月件倹倦健兼券剣喧圏堅嫌建憲懸',
            'SJIS_1_05' => '拳捲検権牽犬献研硯絹県肩見謙賢軒遣鍵険顕験鹸元原厳幻弦減源玄現絃舷言諺限乎個古呼固姑孤己庫弧戸故枯湖狐糊袴股胡菰虎誇跨鈷雇顧鼓五互伍午呉吾娯後御悟梧檎瑚碁語誤護醐乞鯉交佼侯候倖光公功効勾厚口向后喉坑垢好孔孝宏工巧巷幸広庚康弘恒慌抗拘控攻昂晃更杭校梗構江洪浩港溝甲皇硬稿糠紅紘絞綱耕考肯肱腔膏航荒行衡講',
            'SJIS_1_06' => '貢購郊酵鉱砿鋼閤降項香高鴻剛劫号合壕拷濠豪轟麹克刻告国穀酷鵠黒獄漉腰甑忽惚骨狛込此頃今困坤墾婚恨懇昏昆根梱混痕紺艮魂些佐叉唆嵯左差査沙瑳砂詐鎖裟坐座挫債催再最哉塞妻宰彩才採栽歳済災采犀砕砦祭斎細菜裁載際剤在材罪財冴坂阪堺榊肴咲崎埼碕鷺作削咋搾昨朔柵窄策索錯桜鮭笹匙冊刷察拶撮擦札殺薩雑皐鯖捌錆鮫皿晒三',
            'SJIS_1_07' => '傘参山惨撒散桟燦珊産算纂蚕讃賛酸餐斬暫残仕仔伺使刺司史嗣四士始姉姿子屍市師志思指支孜斯施旨枝止死氏獅祉私糸紙紫肢脂至視詞詩試誌諮資賜雌飼歯事似侍児字寺慈持時次滋治爾璽痔磁示而耳自蒔辞汐鹿式識鴫竺軸宍雫七叱執失嫉室悉湿漆疾質実蔀篠偲柴芝屡蕊縞舎写射捨赦斜煮社紗者謝車遮蛇邪借勺尺杓灼爵酌釈錫若寂弱惹主取',
            'SJIS_1_08' => '守手朱殊狩珠種腫趣酒首儒受呪寿授樹綬需囚収周宗就州修愁拾洲秀秋終繍習臭舟蒐衆襲讐蹴輯週酋酬集醜什住充十従戎柔汁渋獣縦重銃叔夙宿淑祝縮粛塾熟出術述俊峻春瞬竣舜駿准循旬楯殉淳準潤盾純巡遵醇順処初所暑曙渚庶緒署書薯藷諸助叙女序徐恕鋤除傷償勝匠升召哨商唱嘗奨妾娼宵将小少尚庄床廠彰承抄招掌捷昇昌昭晶松梢樟樵沼',
            'SJIS_1_09' => '消渉湘焼焦照症省硝礁祥称章笑粧紹肖菖蒋蕉衝裳訟証詔詳象賞醤鉦鍾鐘障鞘上丈丞乗冗剰城場壌嬢常情擾条杖浄状畳穣蒸譲醸錠嘱埴飾拭植殖燭織職色触食蝕辱尻伸信侵唇娠寝審心慎振新晋森榛浸深申疹真神秦紳臣芯薪親診身辛進針震人仁刃塵壬尋甚尽腎訊迅陣靭笥諏須酢図厨逗吹垂帥推水炊睡粋翠衰遂酔錐錘随瑞髄崇嵩数枢趨雛据杉椙',
            'SJIS_1_10' => '菅頗雀裾澄摺寸世瀬畝是凄制勢姓征性成政整星晴棲栖正清牲生盛精聖声製西誠誓請逝醒青静斉税脆隻席惜戚斥昔析石積籍績脊責赤跡蹟碩切拙接摂折設窃節説雪絶舌蝉仙先千占宣専尖川戦扇撰栓栴泉浅洗染潜煎煽旋穿箭線繊羨腺舛船薦詮賎践選遷銭銑閃鮮前善漸然全禅繕膳糎噌塑岨措曾曽楚狙疏疎礎祖租粗素組蘇訴阻遡鼠僧創双叢倉喪壮',
            'SJIS_1_11' => '奏爽宋層匝惣想捜掃挿掻操早曹巣槍槽漕燥争痩相窓糟総綜聡草荘葬蒼藻装走送遭鎗霜騒像増憎臓蔵贈造促側則即息捉束測足速俗属賊族続卒袖其揃存孫尊損村遜他多太汰詑唾堕妥惰打柁舵楕陀駄騨体堆対耐岱帯待怠態戴替泰滞胎腿苔袋貸退逮隊黛鯛代台大第醍題鷹滝瀧卓啄宅托択拓沢濯琢託鐸濁諾茸凧蛸只叩但達辰奪脱巽竪辿棚谷狸鱈樽',
            'SJIS_1_12' => '誰丹単嘆坦担探旦歎淡湛炭短端箪綻耽胆蛋誕鍛団壇弾断暖檀段男談値知地弛恥智池痴稚置致蜘遅馳築畜竹筑蓄逐秩窒茶嫡着中仲宙忠抽昼柱注虫衷註酎鋳駐樗瀦猪苧著貯丁兆凋喋寵帖帳庁弔張彫徴懲挑暢朝潮牒町眺聴脹腸蝶調諜超跳銚長頂鳥勅捗直朕沈珍賃鎮陳津墜椎槌追鎚痛通塚栂掴槻佃漬柘辻蔦綴鍔椿潰坪壷嬬紬爪吊釣鶴亭低停偵剃',
            'SJIS_1_13' => '貞呈堤定帝底庭廷弟悌抵挺提梯汀碇禎程締艇訂諦蹄逓邸鄭釘鼎泥摘擢敵滴的笛適鏑溺哲徹撤轍迭鉄典填天展店添纏甜貼転顛点伝殿澱田電兎吐堵塗妬屠徒斗杜渡登菟賭途都鍍砥砺努度土奴怒倒党冬凍刀唐塔塘套宕島嶋悼投搭東桃梼棟盗淘湯涛灯燈当痘祷等答筒糖統到董蕩藤討謄豆踏逃透鐙陶頭騰闘働動同堂導憧撞洞瞳童胴萄道銅峠鴇匿得',
            'SJIS_1_14' => '徳涜特督禿篤毒独読栃橡凸突椴届鳶苫寅酉瀞噸屯惇敦沌豚遁頓呑曇鈍奈那内乍凪薙謎灘捺鍋楢馴縄畷南楠軟難汝二尼弐迩匂賑肉虹廿日乳入如尿韮任妊忍認濡禰祢寧葱猫熱年念捻撚燃粘乃廼之埜嚢悩濃納能脳膿農覗蚤巴把播覇杷波派琶破婆罵芭馬俳廃拝排敗杯盃牌背肺輩配倍培媒梅楳煤狽買売賠陪這蝿秤矧萩伯剥博拍柏泊白箔粕舶薄迫曝',
            'SJIS_1_15' => '漠爆縛莫駁麦函箱硲箸肇筈櫨幡肌畑畠八鉢溌発醗髪伐罰抜筏閥鳩噺塙蛤隼伴判半反叛帆搬斑板氾汎版犯班畔繁般藩販範釆煩頒飯挽晩番盤磐蕃蛮匪卑否妃庇彼悲扉批披斐比泌疲皮碑秘緋罷肥被誹費避非飛樋簸備尾微枇毘琵眉美鼻柊稗匹疋髭彦膝菱肘弼必畢筆逼桧姫媛紐百謬俵彪標氷漂瓢票表評豹廟描病秒苗錨鋲蒜蛭鰭品彬斌浜瀕貧賓頻敏',
            'SJIS_1_16' => '瓶不付埠夫婦富冨布府怖扶敷斧普浮父符腐膚芙譜負賦赴阜附侮撫武舞葡蕪部封楓風葺蕗伏副復幅服福腹複覆淵弗払沸仏物鮒分吻噴墳憤扮焚奮粉糞紛雰文聞丙併兵塀幣平弊柄並蔽閉陛米頁僻壁癖碧別瞥蔑箆偏変片篇編辺返遍便勉娩弁鞭保舗鋪圃捕歩甫補輔穂募墓慕戊暮母簿菩倣俸包呆報奉宝峰峯崩庖抱捧放方朋法泡烹砲縫胞芳萌蓬蜂褒訪',
            'SJIS_1_17' => '豊邦鋒飽鳳鵬乏亡傍剖坊妨帽忘忙房暴望某棒冒紡肪膨謀貌貿鉾防吠頬北僕卜墨撲朴牧睦穆釦勃没殆堀幌奔本翻凡盆摩磨魔麻埋妹昧枚毎哩槙幕膜枕鮪柾鱒桝亦俣又抹末沫迄侭繭麿万慢満漫蔓味未魅巳箕岬密蜜湊蓑稔脈妙粍民眠務夢無牟矛霧鵡椋婿娘冥名命明盟迷銘鳴姪牝滅免棉綿緬面麺摸模茂妄孟毛猛盲網耗蒙儲木黙目杢勿餅尤戻籾貰問',
            'SJIS_1_18' => '悶紋門匁也冶夜爺耶野弥矢厄役約薬訳躍靖柳薮鑓愉愈油癒諭輸唯佑優勇友宥幽悠憂揖有柚湧涌猶猷由祐裕誘遊邑郵雄融夕予余与誉輿預傭幼妖容庸揚揺擁曜楊様洋溶熔用窯羊耀葉蓉要謡踊遥陽養慾抑欲沃浴翌翼淀羅螺裸来莱頼雷洛絡落酪乱卵嵐欄濫藍蘭覧利吏履李梨理璃痢裏裡里離陸律率立葎掠略劉流溜琉留硫粒隆竜龍侶慮旅虜了亮僚両',
            'SJIS_1_19' => '凌寮料梁涼猟療瞭稜糧良諒遼量陵領力緑倫厘林淋燐琳臨輪隣鱗麟瑠塁涙累類令伶例冷励嶺怜玲礼苓鈴隷零霊麗齢暦歴列劣烈裂廉恋憐漣煉簾練聯蓮連錬呂魯櫓炉賂路露労婁廊弄朗楼榔浪漏牢狼篭老聾蝋郎六麓禄肋録論倭和話歪賄脇惑枠鷲亙亘鰐詫藁蕨椀湾碗腕',
            'SJIS_2_00' => '弌丐丕个丱丶丼丿乂乖乘亂亅豫亊舒弍于亞亟亠亢亰亳亶从仍仄仆仂仗仞仭仟价伉佚估佛佝佗佇佶侈侏侘佻佩佰侑佯來侖儘俔俟俎俘俛俑俚俐俤俥倚倨倔倪倥倅伜俶倡倩倬俾俯們倆偃假會偕偐偈做偖偬偸傀傚傅傴傲僉僊傳僂僖僞僥僭僣僮價僵儉儁儂儖儕儔儚儡儺儷儼儻儿兀兒兌兔兢竸兩兪兮冀冂囘册冉冏冑冓冕冖冤冦冢冩冪冫决冱冲冰况冽',
            'SJIS_2_01' => '凅凉凛几處凩凭凰凵凾刄刋刔刎刧刪刮刳刹剏剄剋剌剞剔剪剴剩剳剿剽劍劔劒剱劈劑辨辧劬劭劼劵勁勍勗勞勣勦飭勠勳勵勸勹匆匈甸匍匐匏匕匚匣匯匱匳匸區卆卅丗卉卍凖卞卩卮夘卻卷厂厖厠厦厥厮厰厶參簒雙叟曼燮叮叨叭叺吁吽呀听吭吼吮吶吩吝呎咏呵咎呟呱呷呰咒呻咀呶咄咐咆哇咢咸咥咬哄哈咨咫哂咤咾咼哘哥哦唏唔哽哮哭哺哢唹啀啣',
            'SJIS_2_02' => '啌售啜啅啖啗唸唳啝喙喀咯喊喟啻啾喘喞單啼喃喩喇喨嗚嗅嗟嗄嗜嗤嗔嘔嗷嘖嗾嗽嘛嗹噎噐營嘴嘶嘲嘸噫噤嘯噬噪嚆嚀嚊嚠嚔嚏嚥嚮嚶嚴囂嚼囁囃囀囈囎囑囓囗囮囹圀囿圄圉圈國圍圓團圖嗇圜圦圷圸坎圻址坏坩埀垈坡坿垉垓垠垳垤垪垰埃埆埔埒埓堊埖埣堋堙堝塲堡塢塋塰毀塒堽塹墅墹墟墫墺壞墻墸墮壅壓壑壗壙壘壥壜壤壟壯壺壹壻壼壽夂夊',
            'SJIS_2_03' => '夐夛梦夥夬夭夲夸夾竒奕奐奎奚奘奢奠奧奬奩奸妁妝佞侫妣妲姆姨姜妍姙姚娥娟娑娜娉娚婀婬婉娵娶婢婪媚媼媾嫋嫂媽嫣嫗嫦嫩嫖嫺嫻嬌嬋嬖嬲嫐嬪嬶嬾孃孅孀孑孕孚孛孥孩孰孳孵學斈孺宀它宦宸寃寇寉寔寐寤實寢寞寥寫寰寶寳尅將專對尓尠尢尨尸尹屁屆屎屓屐屏孱屬屮乢屶屹岌岑岔妛岫岻岶岼岷峅岾峇峙峩峽峺峭嶌峪崋崕崗嵜崟崛崑崔崢',
            'SJIS_2_04' => '崚崙崘嵌嵒嵎嵋嵬嵳嵶嶇嶄嶂嶢嶝嶬嶮嶽嶐嶷嶼巉巍巓巒巖巛巫已巵帋帚帙帑帛帶帷幄幃幀幎幗幔幟幢幤幇幵并幺麼广庠廁廂廈廐廏廖廣廝廚廛廢廡廨廩廬廱廳廰廴廸廾弃弉彝彜弋弑弖弩弭弸彁彈彌彎弯彑彖彗彙彡彭彳彷徃徂彿徊很徑徇從徙徘徠徨徭徼忖忻忤忸忱忝悳忿怡恠怙怐怩怎怱怛怕怫怦怏怺恚恁恪恷恟恊恆恍恣恃恤恂恬恫恙悁悍惧',
            'SJIS_2_05' => '悃悚悄悛悖悗悒悧悋惡悸惠惓悴忰悽惆悵惘慍愕愆惶惷愀惴惺愃愡惻惱愍愎慇愾愨愧慊愿愼愬愴愽慂慄慳慷慘慙慚慫慴慯慥慱慟慝慓慵憙憖憇憬憔憚憊憑憫憮懌懊應懷懈懃懆憺懋罹懍懦懣懶懺懴懿懽懼懾戀戈戉戍戌戔戛戞戡截戮戰戲戳扁扎扞扣扛扠扨扼抂抉找抒抓抖拔抃抔拗拑抻拏拿拆擔拈拜拌拊拂拇抛拉挌拮拱挧挂挈拯拵捐挾捍搜捏掖掎',
            'SJIS_2_06' => '掀掫捶掣掏掉掟掵捫捩掾揩揀揆揣揉插揶揄搖搴搆搓搦搶攝搗搨搏摧摯摶摎攪撕撓撥撩撈撼據擒擅擇撻擘擂擱擧舉擠擡抬擣擯攬擶擴擲擺攀擽攘攜攅攤攣攫攴攵攷收攸畋效敖敕敍敘敞敝敲數斂斃變斛斟斫斷旃旆旁旄旌旒旛旙无旡旱杲昊昃旻杳昵昶昴昜晏晄晉晁晞晝晤晧晨晟晢晰暃暈暎暉暄暘暝曁暹曉暾暼曄暸曖曚曠昿曦曩曰曵曷朏朖朞朦朧',
            'SJIS_2_07' => '霸朮朿朶杁朸朷杆杞杠杙杣杤枉杰枩杼杪枌枋枦枡枅枷柯枴柬枳柩枸柤柞柝柢柮枹柎柆柧檜栞框栩桀桍栲桎梳栫桙档桷桿梟梏梭梔條梛梃檮梹桴梵梠梺椏梍桾椁棊椈棘椢椦棡椌棍棔棧棕椶椒椄棗棣椥棹棠棯椨椪椚椣椡棆楹楷楜楸楫楔楾楮椹楴椽楙椰楡楞楝榁楪榲榮槐榿槁槓榾槎寨槊槝榻槃榧樮榑榠榜榕榴槞槨樂樛槿權槹槲槧樅榱樞槭樔槫樊',
            'SJIS_2_08' => '樒櫁樣樓橄樌橲樶橸橇橢橙橦橈樸樢檐檍檠檄檢檣檗蘗檻櫃櫂檸檳檬櫞櫑櫟檪櫚櫪櫻欅蘖櫺欒欖鬱欟欸欷盜欹飮歇歃歉歐歙歔歛歟歡歸歹歿殀殄殃殍殘殕殞殤殪殫殯殲殱殳殷殼毆毋毓毟毬毫毳毯麾氈氓气氛氤氣汞汕汢汪沂沍沚沁沛汾汨汳沒沐泄泱泓沽泗泅泝沮沱沾沺泛泯泙泪洟衍洶洫洽洸洙洵洳洒洌浣涓浤浚浹浙涎涕濤涅淹渕渊涵淇淦涸淆',
            'SJIS_2_09' => '淬淞淌淨淒淅淺淙淤淕淪淮渭湮渮渙湲湟渾渣湫渫湶湍渟湃渺湎渤滿渝游溂溪溘滉溷滓溽溯滄溲滔滕溏溥滂溟潁漑灌滬滸滾漿滲漱滯漲滌漾漓滷澆潺潸澁澀潯潛濳潭澂潼潘澎澑濂潦澳澣澡澤澹濆澪濟濕濬濔濘濱濮濛瀉瀋濺瀑瀁瀏濾瀛瀚潴瀝瀘瀟瀰瀾瀲灑灣炙炒炯烱炬炸炳炮烟烋烝烙焉烽焜焙煥煕熈煦煢煌煖煬熏燻熄熕熨熬燗熹熾燒燉燔燎燠',
            'SJIS_2_10' => '燬燧燵燼燹燿爍爐爛爨爭爬爰爲爻爼爿牀牆牋牘牴牾犂犁犇犒犖犢犧犹犲狃狆狄狎狒狢狠狡狹狷倏猗猊猜猖猝猴猯猩猥猾獎獏默獗獪獨獰獸獵獻獺珈玳珎玻珀珥珮珞璢琅瑯琥珸琲琺瑕琿瑟瑙瑁瑜瑩瑰瑣瑪瑶瑾璋璞璧瓊瓏瓔珱瓠瓣瓧瓩瓮瓲瓰瓱瓸瓷甄甃甅甌甎甍甕甓甞甦甬甼畄畍畊畉畛畆畚畩畤畧畫畭畸當疆疇畴疊疉疂疔疚疝疥疣痂疳痃疵疽',
            'SJIS_2_11' => '疸疼疱痍痊痒痙痣痞痾痿痼瘁痰痺痲痳瘋瘍瘉瘟瘧瘠瘡瘢瘤瘴瘰瘻癇癈癆癜癘癡癢癨癩癪癧癬癰癲癶癸發皀皃皈皋皎皖皓皙皚皰皴皸皹皺盂盍盖盒盞盡盥盧盪蘯盻眈眇眄眩眤眞眥眦眛眷眸睇睚睨睫睛睥睿睾睹瞎瞋瞑瞠瞞瞰瞶瞹瞿瞼瞽瞻矇矍矗矚矜矣矮矼砌砒礦砠礪硅碎硴碆硼碚碌碣碵碪碯磑磆磋磔碾碼磅磊磬磧磚磽磴礇礒礑礙礬礫祀祠祗祟',
            'SJIS_2_12' => '祚祕祓祺祿禊禝禧齋禪禮禳禹禺秉秕秧秬秡秣稈稍稘稙稠稟禀稱稻稾稷穃穗穉穡穢穩龝穰穹穽窈窗窕窘窖窩竈窰窶竅竄窿邃竇竊竍竏竕竓站竚竝竡竢竦竭竰笂笏笊笆笳笘笙笞笵笨笶筐筺笄筍笋筌筅筵筥筴筧筰筱筬筮箝箘箟箍箜箚箋箒箏筝箙篋篁篌篏箴篆篝篩簑簔篦篥籠簀簇簓篳篷簗簍篶簣簧簪簟簷簫簽籌籃籔籏籀籐籘籟籤籖籥籬籵粃粐粤粭',
            'SJIS_2_13' => '粢粫粡粨粳粲粱粮粹粽糀糅糂糘糒糜糢鬻糯糲糴糶糺紆紂紜紕紊絅絋紮紲紿紵絆絳絖絎絲絨絮絏絣經綉絛綏絽綛綺綮綣綵緇綽綫總綢綯緜綸綟綰緘緝緤緞緻緲緡縅縊縣縡縒縱縟縉縋縢繆繦縻縵縹繃縷縲縺繧繝繖繞繙繚繹繪繩繼繻纃緕繽辮繿纈纉續纒纐纓纔纖纎纛纜缸缺罅罌罍罎罐网罕罔罘罟罠罨罩罧罸羂羆羃羈羇羌羔羞羝羚羣羯羲羹羮羶羸',
            'SJIS_2_14' => '譱翅翆翊翕翔翡翦翩翳翹飜耆耄耋耒耘耙耜耡耨耿耻聊聆聒聘聚聟聢聨聳聲聰聶聹聽聿肄肆肅肛肓肚肭冐肬胛胥胙胝胄胚胖脉胯胱脛脩脣脯腋隋腆脾腓腑胼腱腮腥腦腴膃膈膊膀膂膠膕膤膣腟膓膩膰膵膾膸膽臀臂膺臉臍臑臙臘臈臚臟臠臧臺臻臾舁舂舅與舊舍舐舖舩舫舸舳艀艙艘艝艚艟艤艢艨艪艫舮艱艷艸艾芍芒芫芟芻芬苡苣苟苒苴苳苺莓范苻',
            'SJIS_2_15' => '苹苞茆苜茉苙茵茴茖茲茱荀茹荐荅茯茫茗茘莅莚莪莟莢莖茣莎莇莊荼莵荳荵莠莉莨菴萓菫菎菽萃菘萋菁菷萇菠菲萍萢萠莽萸蔆菻葭萪萼蕚蒄葷葫蒭葮蒂葩葆萬葯葹萵蓊葢蒹蒿蒟蓙蓍蒻蓚蓐蓁蓆蓖蒡蔡蓿蓴蔗蔘蔬蔟蔕蔔蓼蕀蕣蕘蕈蕁蘂蕋蕕薀薤薈薑薊薨蕭薔薛藪薇薜蕷蕾薐藉薺藏薹藐藕藝藥藜藹蘊蘓蘋藾藺蘆蘢蘚蘰蘿虍乕虔號虧虱蚓蚣蚩蚪蚋',
            'SJIS_2_16' => '蚌蚶蚯蛄蛆蚰蛉蠣蚫蛔蛞蛩蛬蛟蛛蛯蜒蜆蜈蜀蜃蛻蜑蜉蜍蛹蜊蜴蜿蜷蜻蜥蜩蜚蝠蝟蝸蝌蝎蝴蝗蝨蝮蝙蝓蝣蝪蠅螢螟螂螯蟋螽蟀蟐雖螫蟄螳蟇蟆螻蟯蟲蟠蠏蠍蟾蟶蟷蠎蟒蠑蠖蠕蠢蠡蠱蠶蠹蠧蠻衄衂衒衙衞衢衫袁衾袞衵衽袵衲袂袗袒袮袙袢袍袤袰袿袱裃裄裔裘裙裝裹褂裼裴裨裲褄褌褊褓襃褞褥褪褫襁襄褻褶褸襌褝襠襞襦襤襭襪襯襴襷襾覃覈覊覓',
            'SJIS_2_17' => '覘覡覩覦覬覯覲覺覽覿觀觚觜觝觧觴觸訃訖訐訌訛訝訥訶詁詛詒詆詈詼詭詬詢誅誂誄誨誡誑誥誦誚誣諄諍諂諚諫諳諧諤諱謔諠諢諷諞諛謌謇謚諡謖謐謗謠謳鞫謦謫謾謨譁譌譏譎證譖譛譚譫譟譬譯譴譽讀讌讎讒讓讖讙讚谺豁谿豈豌豎豐豕豢豬豸豺貂貉貅貊貍貎貔豼貘戝貭貪貽貲貳貮貶賈賁賤賣賚賽賺賻贄贅贊贇贏贍贐齎贓賍贔贖赧赭赱赳趁趙',
            'SJIS_2_18' => '跂趾趺跏跚跖跌跛跋跪跫跟跣跼踈踉跿踝踞踐踟蹂踵踰踴蹊蹇蹉蹌蹐蹈蹙蹤蹠踪蹣蹕蹶蹲蹼躁躇躅躄躋躊躓躑躔躙躪躡躬躰軆躱躾軅軈軋軛軣軼軻軫軾輊輅輕輒輙輓輜輟輛輌輦輳輻輹轅轂輾轌轉轆轎轗轜轢轣轤辜辟辣辭辯辷迚迥迢迪迯邇迴逅迹迺逑逕逡逍逞逖逋逧逶逵逹迸遏遐遑遒逎遉逾遖遘遞遨遯遶隨遲邂遽邁邀邊邉邏邨邯邱邵郢郤扈郛',
            'SJIS_2_19' => '鄂鄒鄙鄲鄰酊酖酘酣酥酩酳酲醋醉醂醢醫醯醪醵醴醺釀釁釉釋釐釖釟釡釛釼釵釶鈞釿鈔鈬鈕鈑鉞鉗鉅鉉鉤鉈銕鈿鉋鉐銜銖銓銛鉚鋏銹銷鋩錏鋺鍄錮錙錢錚錣錺錵錻鍜鍠鍼鍮鍖鎰鎬鎭鎔鎹鏖鏗鏨鏥鏘鏃鏝鏐鏈鏤鐚鐔鐓鐃鐇鐐鐶鐫鐵鐡鐺鑁鑒鑄鑛鑠鑢鑞鑪鈩鑰鑵鑷鑽鑚鑼鑾钁鑿閂閇閊閔閖閘閙閠閨閧閭閼閻閹閾闊濶闃闍闌闕闔闖關闡闥闢阡阨阮',
            'SJIS_2_20' => '阯陂陌陏陋陷陜陞陝陟陦陲陬隍隘隕隗險隧隱隲隰隴隶隸隹雎雋雉雍襍雜霍雕雹霄霆霈霓霎霑霏霖霙霤霪霰霹霽霾靄靆靈靂靉靜靠靤靦靨勒靫靱靹鞅靼鞁靺鞆鞋鞏鞐鞜鞨鞦鞣鞳鞴韃韆韈韋韜韭齏韲竟韶韵頏頌頸頤頡頷頽顆顏顋顫顯顰顱顴顳颪颯颱颶飄飃飆飩飫餃餉餒餔餘餡餝餞餤餠餬餮餽餾饂饉饅饐饋饑饒饌饕馗馘馥馭馮馼駟駛駝駘駑駭駮',
            'SJIS_2_21' => '駱駲駻駸騁騏騅駢騙騫騷驅驂驀驃騾驕驍驛驗驟驢驥驤驩驫驪骭骰骼髀髏髑髓體髞髟髢髣髦髯髫髮髴髱髷髻鬆鬘鬚鬟鬢鬣鬥鬧鬨鬩鬪鬮鬯鬲魄魃魏魍魎魑魘魴鮓鮃鮑鮖鮗鮟鮠鮨鮴鯀鯊鮹鯆鯏鯑鯒鯣鯢鯤鯔鯡鰺鯲鯱鯰鰕鰔鰉鰓鰌鰆鰈鰒鰊鰄鰮鰛鰥鰤鰡鰰鱇鰲鱆鰾鱚鱠鱧鱶鱸鳧鳬鳰鴉鴈鳫鴃鴆鴪鴦鶯鴣鴟鵄鴕鴒鵁鴿鴾鵆鵈鵝鵞鵤鵑鵐鵙鵲鶉鶇鶫',
            'SJIS_2_22' => '鵯鵺鶚鶤鶩鶲鷄鷁鶻鶸鶺鷆鷏鷂鷙鷓鷸鷦鷭鷯鷽鸚鸛鸞鹵鹹鹽麁麈麋麌麒麕麑麝麥麩麸麪麭靡黌黎黏黐黔黜點黝黠黥黨黯黴黶黷黹黻黼黽鼇鼈皷鼕鼡鼬鼾齊齒齔齣齟齠齡齦齧齬齪齷齲齶龕龜龠堯槇遙瑤凜熙',
        );

        $returnText = '';
        for ($i = 0; $i < mb_strlen($targetText); $i++) {
            // １文字ずつチェック
            $target = mb_substr($targetText, $i, 1);
            foreach ($eregPtrns as $eregPtrn) {
                // 正規表現チェック
                $tmpText = mb_ereg_replace( '[^'. $eregPtrn. ']', '', $target );
                // パターンに一致した文字のみ残る
                if ( !empty($tmpText) ) {
                    $returnText .= $tmpText;
                    break;
                }
            }
        }

        return $returnText;
    }
}
