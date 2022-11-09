<?php
namespace oemmember\Controller;

use Zend\Config\Reader\Ini;
use Zend\Json\Json;
use Coral\Base\BaseGeneralUtils;
use Coral\Base\BaseHtmlUtils;
use Coral\Base\BaseUtility;
use Coral\Base\IO\BaseIOCsvWriter;
use Coral\Base\Reflection\BaseReflectionUtility;
use Coral\Coral\Controller\CoralControllerAction;
use Coral\Coral\CoralCodeMaster;
use Coral\Coral\CoralPager;
use Coral\Coral\History\CoralHistoryOrder;
use oemmember\Application;
use models\Logic\LogicTemplate;
use models\Table\TableClaimControl;
use models\Table\TableClaimHistory;
use models\Table\TableEnterprise;
use models\Table\TableHeader;
use models\Table\TableOrder;
use models\Table\TableOrderItems;
use models\Table\TableOemClaimFee;
use models\Table\TableSite;
use models\Table\TableTemplateField;
use models\Table\TableTemplateHeader;
use models\Table\TableUser;
use Coral\Coral\CoralValidate;
use models\Logic\LogicCampaign;
use models\Table\TablePayingAndSales;
use models\Logic\LogicMypageOrder;
use models\Table\TableSystemProperty;
use DOMPDFModule\View\Model\PdfModel;
use models\Logic\LogicNormalizer;
use models\Table\TableClaimError;
use models\Logic\Exception\LogicClaimException;
use models\Table\TableCode;
use models\Logic\LogicPayeasy;
use models\Table\TableSiteSbpsPayment;

class RwclaimController extends CoralControllerAction
{
	protected $_componentRoot = './application/views/components';

	const SESS_AFTER_MSG = 'SESS_AFTER_MSG';
	const SESS_PA_AFTER_MSG = 'SESS_PA_AFTER_MSG';
	const SESSION_JOB_PARAMS = 'OMRWCLAIM_JOB_PARAMS';

	/**
	 * アプリケーションオブジェクト
	 * @var Application
	 */
	private $app;

	/**
	 * 必要なクエリテンプレート
	 *
	 * @var array
	 */
	private $queries;

	/**
	 * １ページ最大表示件数
	 *
	 * @var int
	 */
	const PAGE_LINE_MAX = 100;

	protected function _init()
	{
        $app = Application::getInstance();
        $this->app = $app;

        // メニュー情報をマスタから取得
        $menu_info = $this->app->getMenuInfo();

        foreach( $menu_info as $key => $info ) {
            $this->view->assign( $key, $info );
        }

        // ログイン中のアカウント情報取得
        $this->userInfo = $app->authManager->getUserInfo();

        $this->addStyleSheet( '../../oemmember/css/members.css' );
        $this->addStyleSheet( '../../oemmember/css/rwclaim_member.css');
        $this->addStyleSheet( '../../oemmember/css/combinedclaim.css' );
        $this->addJavaScript( '../../js/prototype.js' );
        $this->addJavaScript( '../../js/bytefx.js' );
        $this->addJavaScript( '../../js/corelib.js' );
        $this->addJavaScript( '../../js/base.ui.js' );
        $this->addJavaScript( '../../js/base.ui.tableex.js' );
        $this->addJavaScript( '../../js/sortable_ja.js' );
        $this->addStyleSheet( '../../css/base.ui.customlist.css');
        $this->addJavaScript( '../../js/base.ui.customlist.js');

        $this->view->assign( 'cssName', "rwclaim" );
	}

	/**
	 * 請求書発行(同梱待ちリスト)
	 */
	public function listAction()
    {
        $this->setPageTitle( '請求書発行(同梱待ちリスト)' );

        $params = $this->getParams();

        // 抽出条件
        $cmbAnotherDelivery   = isset($params['cad']) ? $params['cad'] : -1;
        $cmbClaimSendingClass = isset($params['ccsc']) ? $params['ccsc'] : -1;
        $cmbPrintSts          = isset($params['cps']) ? $params['cps'] : -1;
        $cmbSite              = isset($params['cst']) ? $params['cst'] : -1;

        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);

        // SQL
        $sql =<<<EOQ
 SELECT
        '' AS No
 ,      odr.OrderId
 ,      CASE WHEN sum.RegNameKj <> sum.RegDestNameKj THEN '指定あり' ELSE '' END AS AnotherDeliFlgStr
 ,      CASE WHEN (SELECT MAX(ConfirmWaitingFlg) FROM T_Order WHERE P_OrderSeq = odr.OrderSeq AND Cnl_Status = 0) = 0 THEN '未発行' ELSE '確定待ち' END AS ConfirmWaitingFlgStr
 ,      CASE WHEN odr.ClaimSendingClass  = 12 THEN '別送' ELSE '同梱' END AS ClaimSendingClassStr
 ,      odr.Ent_OrderId
 ,      odr.ReceiptOrderDate
 ,      odr.RegistDate
 ,      cus.NameKj
 ,      cus.UnitingAddress
 ,      (SELECT SUM(UseAmount) FROM T_Order WHERE P_OrderSeq = odr.OrderSeq AND Cnl_Status = 0) AS UseAmount
 ,      odr.P_OrderSeq  -- (非表示項目)
 ,      sit.SiteId      -- (非表示項目)
 ,      sit.PrintFormDK -- (非表示項目)
 FROM   T_Order odr
        INNER JOIN T_Customer cus
                ON cus.OrderSeq = odr.OrderSeq
        INNER JOIN T_Enterprise ent
                ON ent.EnterpriseId = odr.EnterpriseId
        INNER JOIN T_Site sit
                ON sit.SiteId = odr.SiteId
        INNER JOIN T_OrderSummary sum
                ON sum.OrderSeq = odr.OrderSeq
WHERE   odr.OrderSeq = odr.P_OrderSeq
 AND    (SELECT MIN(DataStatus) FROM T_Order WHERE P_OrderSeq = odr.OrderSeq AND Cnl_Status = 0) = 41
 AND    ( ent.SelfBillingMode > 0 AND sit.SelfBillingFlg = 1 )  -- 同梱加盟店、同梱サイト
 AND    IFNULL(odr.CombinedClaimTargetStatus, 0) IN (0, 91, 92) -- 請求取りまとめ済みのもの
 AND    odr.EnterpriseId = :EnterpriseId
EOQ;

        // (別配送先設定)
        if ($cmbAnotherDelivery != -1) {
            $sql .= " AND    (CASE WHEN sum.RegNameKj <> sum.RegDestNameKj THEN 1 ELSE 0 END) = :CmbAnotherDelivery";
        }
        // (同梱/別送設定)
        if ($cmbClaimSendingClass == 0 ) {      // 同梱のみ
            $sql .= " AND    odr.ClaimSendingClass <> 12 ";
        }
        else if ($cmbClaimSendingClass == 1 ) { // 別送のみ
            $sql .= " AND    odr.ClaimSendingClass = 12 ";
        }
        // (印刷の状態)
        if ($cmbPrintSts != -1) {
            $sql .= " AND    (SELECT MAX(ConfirmWaitingFlg) FROM T_Order WHERE P_OrderSeq = odr.OrderSeq AND Cnl_Status = 0) = :CmbPrintSts";
        }
        // (サイト設定)
        if ($cmbSite != -1) {
            $sql .= " AND    odr.SiteId = :SiteId";
        }

        // 加盟店情報取得
        $mdle = new TableEnterprise($this->app->dbAdapter);
        $edata = $mdle->findEnterprise($this->app->authManager->getUserInfo()->EnterpriseId)->current();

        $order1 = isset($params['ClmOdr1']) ? $params['ClmOdr1'] : $edata['ClaimOrder1'];
        $order2 = isset($params['ClmOdr2']) ? $params['ClmOdr2'] : $edata['ClaimOrder2'];

        // ソート順 設定
        if ($order2 == 0){
            $ord = 'ASC';
        } else {
            $ord = 'DESC';
        }

        switch ($order1) {
            case 0:
                $OrderSql = " odr.OrderId $ord ";
                break;
            case 1:
                $OrderSql = " (CASE WHEN sum.RegNameKj <> sum.RegDestNameKj THEN 1 ELSE 0 END) $ord, odr.OrderId ";
                break;
            case 2:
                $OrderSql = " (CASE WHEN (SELECT MAX(ConfirmWaitingFlg) FROM T_Order WHERE P_OrderSeq = odr.OrderSeq AND Cnl_Status = 0) = 0 THEN 1 ELSE 0 END) $ord, odr.OrderId ";
                break;
            case 3:
                $OrderSql = " (CASE WHEN odr.ClaimSendingClass  = 12 THEN 1 ELSE 0 END) $ord, odr.OrderId ";
                break;
            case 4:
                $OrderSql = " odr.Ent_OrderId $ord, odr.OrderId ";
                break;
            case 5:
                $OrderSql = " odr.ReceiptOrderDate $ord, odr.OrderId ";
                break;
            case 6:
                $OrderSql = " odr.RegistDate $ord, odr.OrderId ";
                break;
            case 7:
                $OrderSql = " cus.NameKj $ord, odr.OrderId ";
                break;
            case 8:
                $OrderSql = " cus.UnitingAddress $ord, odr.OrderId ";
                break;
            case 9:
                $OrderSql = " UseAmount $ord, odr.OrderId ";
                break;
            default:
                break;
            }

        $sql .= " ORDER BY $OrderSql ";
        $prm = array(
                ':EnterpriseId' =>  $this->app->authManager->getUserInfo()->EnterpriseId,
        );
        if ($cmbAnotherDelivery != -1) {
            $prm[':CmbAnotherDelivery'] = $cmbAnotherDelivery;
        }
        if ($cmbPrintSts != -1) {
            $prm[':CmbPrintSts'] = $cmbPrintSts;
        }
        if ($cmbSite != -1) {
            $prm[':SiteId'] = $cmbSite;
        }

        $ri = $this->app->dbAdapter->query($sql)->execute($prm);

        $datas = ResultInterfaceToArray($ri);

        $numOfHistory = 0;
        if (!empty($datas)) {
            $numOfHistory = count($datas);  // 総件数の取得
        }
        $printFormDKList = array();
        for ($i =0; $i<$numOfHistory; $i++) {
            // 番号付与
            $datas[$i]['No'] = (1 + $i);
            // 用紙種類判定
            $printFormDKList[ $datas[$i]['PrintFormDK'] ] += 1;
        }

        // サイト情報取得
        $siteTable = new TableSite($this->app->dbAdapter);
        $ri = $siteTable->getValidAll($this->app->authManager->getUserInfo()->EnterpriseId);
        $sites = ResultInterfaceToArray($ri);
        // ビューでリスト表示するため連想配列で構築
        $siteList = array(-1 => '--サイト設定--');
        foreach( $sites as $row ) {
            $siteList[ $row['SiteId'] ] = $row['SiteNameKj'];
        }
        $this->view->assign("cstTag",BaseHtmlUtils::SelectTag('cst',$siteList,$cmbSite));
        $this->view->assign("list", $datas);
        $this->view->assign("numOfHistory", $numOfHistory);
        $this->view->assign("cadTag",BaseHtmlUtils::SelectTag('cad',array(-1 => '--別配送先設定--', 0 => '別配送先あり除く', 1 => '別配送先ありのみ', ),$cmbAnotherDelivery));
        $this->view->assign("ccscTag",BaseHtmlUtils::SelectTag('ccsc',array(-1 => '--同梱/別送設定--', 0 => '同梱のみ', 1 => '別送のみ', ),$cmbClaimSendingClass));
        $this->view->assign("cpsTag",BaseHtmlUtils::SelectTag('cps',$codeMaster->getMasterCodes(78, array(-1 => '--印刷の状態--')),$cmbPrintSts));
        $this->view->assign('HideToCbButton', $this->userInfo->HideToCbButton);
        $this->view->assign('SelfBillingExportAllow', $this->userInfo->SelfBillingExportAllow);
        $this->view->assign('PrintFormDKList', $printFormDKList);
        $this->view->assign('ChargeClass', $edata['ChargeClass']);

        // セッションに値を保管
        $_SESSION['MRWCLAIM_cmbAnotherDelivery']   = $cmbAnotherDelivery;
        $_SESSION['MRWCLAIM_cmbClaimSendingClass'] = $cmbClaimSendingClass;
        $_SESSION['MRWCLAIM_cmbPrintSts']          = $cmbPrintSts;
        $_SESSION['MRWCLAIM_cmbClaimFormat']       = $cmbClaimFormat;

        $SearchUrl = "rwclaim/list?cad=" . $cmbAnotherDelivery . "&ccsc=" . $cmbClaimSendingClass . "&cps=" . $cmbPrintSts . "&cst=" . $cmbSite;

        for ($i=0; $i<10; $i++) {
            $OdrSort = "0";
            if (nvl($params['ClmOdr1'], -1) == $i && nvl($params['ClmOdr2'], -1) == 0) {
                $OdrSort = "1";
                $img[$i] = "../../images/arrow-up.gif";
            } elseif (nvl($params['ClmOdr1'], -1) == $i && nvl($params['ClmOdr2'], -1) == 1) {
                $OdrSort = "0";
                $img[$i] = "../../images/arrow-down.gif";
            } else {
                $img[$i] = "../../images/arrow-none.gif";
            }
            $url[$i] = $SearchUrl . "&ClmOdr1=" . $i . "&ClmOdr2=" . $OdrSort;
        }

        $this->view->assign('sorturl', $url);
        $this->view->assign('arrowimg', $img);

        return $this->view;
    }

    /**
     * (印刷済に)更新
     */
    public function upAction()
    {
        $params = $this->getParams();

        // 抽出条件
        $cmbAnotherDelivery   = isset($_SESSION['MRWCLAIM_cmbAnotherDelivery']) ? $_SESSION['MRWCLAIM_cmbAnotherDelivery'] : -1;
        $cmbClaimSendingClass = isset($_SESSION['MRWCLAIM_cmbClaimSendingClass']) ? $_SESSION['MRWCLAIM_cmbClaimSendingClass'] : -1;
        $cmbPrintSts          = isset($_SESSION['MRWCLAIM_cmbPrintSts']) ? $_SESSION['MRWCLAIM_cmbPrintSts'] : -1;
        $cmbClaimFormat       = isset($_SESSION['MRWCLAIM_cmbClaimFormat']) ? $_SESSION['MRWCLAIM_cmbClaimFormat'] : -1;

        // ユーザーIDの取得
        $obj = new \models\Table\TableUser($this->app->dbAdapter);
        getUserInfoForMember($this->app, $userClass, $seq);
        $userId = $obj->getUserId($userClass, $seq);

        // 請求関連処理SQL
        $stm = $this->app->dbAdapter->query($this->getBaseP_ClaimControl());

        // SQL実行結果取得用のSQL
        $getretvalsql = " SELECT @po_ret_sts AS po_ret_sts, @po_ret_errcd AS po_ret_errcd, @po_ret_sqlcd AS po_ret_sqlcd, @po_ret_msg AS po_ret_msg ";

        $counter = 0;

        $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
        try {
            // 各モデルインスタンス生成
            $mdlcc  = new TableClaimControl($this->app->dbAdapter);
            $mdlch  = new TableClaimHistory($this->app->dbAdapter);
            $mdlent = new TableEnterprise($this->app->dbAdapter);
            $mdlodr = new TableOrder($this->app->dbAdapter);
            $mdlocf = new TableOemClaimFee($this->app->dbAdapter);
            $mdlsit = new \models\Table\TableSite($this->app->dbAdapter);

            $i = 0;
            while (isset($params['P_OrderSeq' . $i])) {
                if (!isset($params['chkWaitDecision' . $i])) { $i++; continue; }

                $poseq = $params['P_OrderSeq' . $i];

$sql = <<<EOQ
SELECT COUNT(*) AS cnt
  FROM T_Order o
 WHERE EXISTS(SELECT * FROM T_Order t WHERE t.P_OrderSeq = o.OrderSeq AND t.Cnl_Status = 0)
   AND o.OrderSeq = :OrderSeq
EOQ;
                $prm = array(
                        ':OrderSeq' => $poseq,
                );
                $ret = $this->app->dbAdapter->query($sql)->execute($prm)->current()['cnt'];
                if ($ret == 0) {
                    // 有効な注文がいない場合はスキップ
                    $i++;
                    continue;
                }

                // 請求履歴が有効かどうか判定
                if ($mdlch->getReservedCount($poseq) <= 0) {
                    // 処理をスキップ
                    $i++;
                    continue;
                }

                // 請求履歴データを取得
                $data = $mdlch->findClaimHistory(array( 'PrintedFlg' => 0, 'ValidFlg' => 1, 'OrderSeq' => $poseq ))->current();

                // 請求関連処理呼び出し用パラメータの設定
                $prm = array(
                        ':pi_history_seq'   => $data['Seq'],
                        ':pi_button_flg'       => 1,
                        ':pi_user_id'          => $userId,
                );

                $ri = $stm->execute($prm);

                // SQL実行例外なしもエラー戻り値の時は例外をｽﾛｰ
                $retval = $this->app->dbAdapter->query($getretvalsql)->execute(null)->current();
                if ($retval['po_ret_sts'] != 0) {
                    throw new \Exception($retval['po_ret_msg']);
                }

                // 注文履歴登録用に親注文Seqから子注文Seqを再取得する。
                $sql = <<<EOQ
                    SELECT  OrderSeq
                    FROM    T_Order
                    WHERE   P_OrderSeq = :P_OrderSeq
                    AND     Cnl_Status = 0
                    ;
EOQ;

                $ri = $this->app->dbAdapter->query($sql)->execute(array(':P_OrderSeq' => $params['P_OrderSeq' . $i]));
                $oseqs = ResultInterfaceToArray($ri);

                // 注文履歴へ登録
                $history = new CoralHistoryOrder($this->app->dbAdapter);
                // 取得できた件数分ループする
                foreach ($oseqs as $row) {
                    // 備考に保存
                    $mdlodr->appendPrintedInfoToOemNote($row["OrderSeq"]);
                    // 注文履歴登録
                    $history->InsOrderHistory($row["OrderSeq"], 41, $userId);
                }

                // 請求履歴．印刷ステータス(PrintedStatus)を"9"(印刷済み)に更新する
                $this->app->dbAdapter->query(" UPDATE T_ClaimHistory SET PrintedStatus = 9 WHERE Seq = :Seq ")->execute(array(':Seq' => $data['Seq']));

                $counter++;
                $i++;
            }

            $this->app->dbAdapter->getDriver()->getConnection()->commit();
            $_SESSION['MRWCLAIM_message'] = ($counter . '件更新しました');
        }
        catch (\Exception $e) {
            $this->app->dbAdapter->getDriver()->getConnection()->rollback();
            $_SESSION['MRWCLAIM_message'] = '更新エラーが発生しました';
        }

        $ar = array(
                'cad' => $cmbAnotherDelivery,
                'ccsc' => $cmbClaimSendingClass,
                'cps' => $cmbPrintSts,
                'ccf' => $cmbClaimFormat,
        );

        return $this->_forward('list', $ar);
    }

    /**
     * 画面情報をセッションに保存
     */
    public function jobparamsetAction()
    {
        $status = 1;
        $message = '';

        // セッションに情報をセットする
        unset($_SESSION[self::SESSION_JOB_PARAMS]);
        $_SESSION[self::SESSION_JOB_PARAMS] = $this->getParams();

        try {
            // ジョブ転送
            $ceSeqs = array();

            if(date('Y-m-d') > '2019-09-30'){
                //10月1日以降用
                $this->jobTransfer2($ceSeqs);
            }else{
                $this->jobTransfer($ceSeqs);
            }
            if (!empty($ceSeqs)) {
                $seqs = implode(',', $ceSeqs);

                $sql = <<<EOQ
SELECT
CONCAT(o.OrderId,
    CASE ce.ErrorCode
        WHEN 1 THEN ' SMBC連携'
        WHEN 4 THEN ' ペイジー連携'
        ELSE ' SMBC連携'
    END,
    'エラー(', ce.ErrorMsg, ')') AS msg
FROM T_ClaimError ce
     INNER JOIN T_Order o
             ON ce.OrderSeq = o.OrderSeq
WHERE 1 = 1
AND   ce.Seq IN ($seqs);
EOQ;
                $ri = $this->app->dbAdapter->query($sql)->execute();

                $status = 2;
                foreach ($ri as $row) {
                    $message .= $row['msg'] . "\n";
                }
            }
        } catch(\Exception $e) {
            $status = 9;
            $message .= $e->getMessage() . "\n";
            $message .= $e->getTraceAsString() . "\n";
        }

        echo \Zend\Json\Json::encode(array('status' => $status, 'message' => $message));

        return $this->response;
    }

    /**
     * CSV出力処理
     */
    public function csvoutputAction()
    {
        if(date('Y-m-d') > '2019-09-30'){
            //10月1日以降用
            // CSVダウンロード
            $csvData = $this->csvDownload2();
        }else{
            // CSVダウンロード
            $csvData = $this->csvDownload();
        }
        // セッションクリア
        unset($_SESSION[self::SESSION_JOB_PARAMS]);

        return $csvData;
    }

    /**
     * 印刷処理
     */
    public function printAction()
    {

        if(date('Y-m-d') > '2019-09-30'){
            //10月1日以降用
            // PDF出力
            $pdf = $this->pdfDownload2();
        }else{
            // PDF出力
            $pdf = $this->pdfDownload();
        }
        // セッションクリア
        unset($_SESSION[self::SESSION_JOB_PARAMS]);

        return $pdf;
    }

	/**
     * (Ajax)別送に更新処理
     */
    public function upbsAction()
    {
        try
        {
            $params = $this->getParams();

            // ユーザーIDの取得
            $obj = new \models\Table\TableUser($this->app->dbAdapter);
            getUserInfoForMember($this->app, $userClass, $seq);
            $userId = $obj->getUserId($userClass, $seq);

            // 注文の更新（注文.請求書送付区分を[12（同梱 → 別送）]へ更新）
            $sql  = " UPDATE T_Order ";
            $sql .= " SET    ClaimSendingClass = 12 ";
            $sql .= " ,      UpdateId          = :UpdateId ";
            $sql .= " ,      UpdateDate        = :UpdateDate ";
            $sql .= " WHERE  P_OrderSeq        = :P_OrderSeq ";

            $stm = $this->app->dbAdapter->query($sql);

            $i = 0;

            while (isset($params['P_OrderSeq' . $i]))
            {
                if (!isset($params['chkBs' . $i])) { $i++; continue; }

                $stm->execute(array(':UpdateId' => $userId, ':UpdateDate' => date('Y-m-d H:i:s'), ':P_OrderSeq' => $params['P_OrderSeq' . $i],));

                $i++;
            }

            // 成功指示
            $msg = '1';
        }
        catch(\Exception $e)
        {
            $msg = $e->getMessage();
        }

        echo \Zend\Json\Json::encode(array('status' => $msg));
        return $this->response;
    }

    /**
     * CSVダウンロード
     */
    protected function csvDownload() {

        $params = $_SESSION[self::SESSION_JOB_PARAMS];

        // ユーザーIDの取得
        $obj = new \models\Table\TableUser($this->app->dbAdapter);
        getUserInfoForMember($this->app, $userClass, $seq);
        $userId = $obj->getUserId($userClass, $seq);

        $datas = array();
        $i = 0;
        while( isset( $params['P_OrderSeq' . $i] ) ) {
            if( !isset( $params['chkPrintCsv' . $i ] ) ) {
                $i++;
                continue;
            }
            $data = array();
            $prm = array( ':P_OrderSeq' => $params['P_OrderSeq' . $i] );

            $sql  = ' SELECT c.PostalCode ';
            $sql .= ' ,      c.UnitingAddress ';
            $sql .= ' ,      c.NameKj ';
            $sql .= ' ,      o.OrderId ';
            $sql .= ' ,      o.Ent_OrderId ';
            $sql .= ' ,      DATE_FORMAT(o.ReceiptOrderDate, \'%Y/%m/%d\') AS ReceiptOrderDate ';
            $sql .= ' ,      s.SiteNameKj ';
            $sql .= ' ,      s.Url ';
            $sql .= ' ,      e.ContactPhoneNumber AS Phone ';
            $sql .= ' ,      ch.ClaimAmount ';
            $sql .= ' ,      DATE_FORMAT(ch.LimitDate, \'%Y/%m/%d\') AS LimitDate ';
            $sql .= ' ,      ca.Cv_BarcodeData ';
            $sql .= ' ,      ca.Cv_BarcodeString1 ';
            $sql .= ' ,      ca.Cv_BarcodeString2 ';
            $sql .= ' ,      ca.Yu_DtCode ';
            $sql .= ' ,      o.Ent_OrderId AS Ent_OrderId2 ';
            $sql .= ' ,      ca.TaxAmount ';
            $sql .= ' ,      ca.Cv_ReceiptAgentName ';
            $sql .= ' ,      ca.Cv_SubscriberName ';
            $sql .= ' ,      ca.Bk_BankCode ';
            $sql .= ' ,      ca.Bk_BranchCode ';
            $sql .= ' ,      ca.Bk_BankName ';
            $sql .= ' ,      ca.Bk_BranchName ';
            $sql .= ' ,      ca.Bk_DepositClass ';
            $sql .= ' ,      ca.Bk_AccountNumber ';
            $sql .= ' ,      ca.Bk_AccountHolder ';
            $sql .= ' ,      ca.Bk_AccountHolderKn ';
            $sql .= ' ,      ca.Yu_SubscriberName ';
            $sql .= ' ,      ca.Yu_AccountNumber ';
            $sql .= ' ,      ca.Yu_ChargeClass ';
            $sql .= ' ,      ca.Yu_AccountNumber ';
            $sql .= ' ,      ca.Yu_MtOcrCode1 ';
            $sql .= ' ,      ca.Yu_MtOcrCode2 ';
            $sql .= ' ,      e.PrintEntComment ';
            $sql .= ' ,      e.PrintEntOrderIdOnClaimFlg ';
            $sql .= ' ,      cd105.KeyContent AS MypageUrl ';
            $sql .= ' ,      c.CorporateName ';
            $sql .= ' ,      c.DivisionName ';
            $sql .= ' ,      c.CpNameKj ';
            $sql .= ' FROM   T_Order o INNER JOIN ';
            $sql .= '        T_Customer c ON ( o.OrderSeq = c.OrderSeq ) INNER JOIN ';
            $sql .= '        T_Enterprise e ON ( o.EnterpriseId = e.EnterpriseId ) INNER JOIN ';
            $sql .= '        T_Site s ON ( o.SiteId = s.SiteId ) INNER JOIN ';
            $sql .= '        T_ClaimHistory ch ON ( o.OrderSeq = ch.OrderSeq AND ch.PrintedFlg = 0 AND ch.ValidFlg = 1 ) INNER JOIN ';
            $sql .= '        T_OemClaimAccountInfo ca ON( ch.Seq = ca.ClaimHistorySeq AND ca.Status = 1 ) LEFT OUTER JOIN ';
            $sql .= '        M_Code cd105 ON( cd105.CodeId = 105 AND IFNULL(o.OemId, 0) = cd105.KeyCode) ';
            $sql .= ' WHERE  o.P_OrderSeq = :P_OrderSeq ';
            $sql .= '   AND  EXISTS (SELECT * FROM T_Order t WHERE t.P_OrderSeq = o.OrderSeq AND t.Cnl_Status = 0) ';
            $data = $this->app->dbAdapter->query( $sql )->execute( $prm )->current();

            if (!$data) {
                // 有効な注文データがない場合はスキップ
                $i++;
                continue;
            }

            // 請求金額が30万円以上だった場合
            if( $data['ClaimAmount'] >= 300000 ) {
                $data['Cv_BarcodeData'] = '収納代行の規約によりコンビニエンスストアで30万円以上のお支払はできません';
                $data['Cv_BarcodeString1'] = '';
                $data['Cv_BarcodeString2'] = '';
            }

            // 任意注文番号非表示の加盟店
            if ($data['PrintEntOrderIdOnClaimFlg'] == 0) {
                $data['Ent_OrderId'] = '';
                $data['Ent_OrderId2'] = '';
            }

            // 注文商品
            $sql  = ' SELECT itm.ItemNameKj ';
            $sql .= ' ,      itm.ItemNum ';
            $sql .= ' ,      itm.UnitPrice ';
            $sql .= ' ,      itm.SumMoney ';
            $sql .= ' FROM   T_Order o INNER JOIN ';
            $sql .= '        T_OrderItems itm ON ( o.OrderSeq = itm.OrderSeq ) ';
            $sql .= ' WHERE  itm.DataClass = 1 AND ';
            $sql .= '        o.P_OrderSeq = :P_OrderSeq ';
            $sql .= ' AND    o.Cnl_Status = 0 ';
            $sql .= ' AND    itm.ValidFlg = 1 ';
            $sql .= ' ORDER BY OrderItemId ';
            $items = ResultInterfaceToArray( $this->app->dbAdapter->query( $sql )->execute( $prm ) );
            $data['OrderItems'] = $items;

            // 小計
            $sql  = ' SELECT SUM(itm.SumMoney) AS SumMoney ';
            $sql .= ' FROM   T_Order o INNER JOIN ';
            $sql .= '        T_OrderItems itm ON ( o.OrderSeq = itm.OrderSeq ) ';
            $sql .= ' WHERE  itm.DataClass = 1 AND ';
            $sql .= '        o.P_OrderSeq = :P_OrderSeq ';
            $sql .= ' AND    o.Cnl_Status = 0 ';
            $sql .= ' AND    itm.ValidFlg = 1 ';
            $data['TotalItemPrice'] = $this->app->dbAdapter->query( $sql )->execute( $prm )->current()['SumMoney'];
            $data['TotalItemPrice2'] = $data['TotalItemPrice'];//NOTE:同値設定

            // 送料
            $sql  = ' SELECT SUM( itm.SumMoney ) AS CarriageFee ';
            $sql .= ' FROM   T_Order o INNER JOIN ';
            $sql .= '        T_OrderItems itm ON ( o.OrderSeq = itm.OrderSeq ) ';
            $sql .= ' WHERE  itm.DataClass = 2 AND ';
            $sql .= '        o.P_OrderSeq = :P_OrderSeq ';
            $sql .= ' AND    o.Cnl_Status = 0 ';
            $data = array_merge( $data, $this->app->dbAdapter->query( $sql )->execute( $prm )->current() );

            // 決済手数料
            $sql  = ' SELECT SUM( itm.SumMoney ) AS ChargeFee ';
            $sql .= ' FROM   T_Order o INNER JOIN ';
            $sql .= '        T_OrderItems itm ON ( o.OrderSeq = itm.OrderSeq ) ';
            $sql .= ' WHERE  itm.DataClass = 3 AND ';
            $sql .= '        o.P_OrderSeq = :P_OrderSeq ';
            $sql .= ' AND    o.Cnl_Status = 0 ';
            $data = array_merge( $data, $this->app->dbAdapter->query( $sql )->execute( $prm )->current() );

            // 請求回数
            $data['ReIssueCount'] = 0;

            // 店舗からのお知らせ
            $data['PrintEntComment'] = preg_replace('/((\r\n)|[\r\n])/', "\n", f_e($data['PrintEntComment']));  // 改行コードを\nに統一
            $printEntComments = explode("\n", $data['PrintEntComment'], 11);
            for( $j = 1; $j <= 10; $j++ ) {
                $data[sprintf('PrintEntComment%02d', $j)] = isset($printEntComments[$j - 1]) ? $printEntComments[$j - 1] : '';
            }

            // マイページログインパスワード
            $row_mypageorder = $this->app->dbAdapter->query(" SELECT Token FROM T_MypageOrder WHERE OrderSeq = :OrderSeq ORDER BY Seq DESC LIMIT 1 "
            )->execute(array(':OrderSeq' => $params['P_OrderSeq' . $i]))->current();
            $data['MypageToken'] = ($row_mypageorder) ? $row_mypageorder['Token'] : '';

            // 請求書CSV対応
            // ・二重引用符全角の二重引用符に置換
            // ・改行記号（CRFL、CR、LF）は半角スペースに置換
            // ・フォームフィード文字および垂直タブ文字（ASCII：0x0B）は除去
            // ・タブ文字は半角スペースに置換
            $search  = array('"'    , "\r\n"   , "\r"  , "\n"  , "\f"  , "\v" , "\t");
            $replace = array('”'   , ' '      , ' '   , ' '   , ''    , ''   , ' ');
            $data = str_replace($search, $replace, $data);

            // 法人名が入力されており、担当者名がブランクの場合は、「担当者名」へ購入者名を出力する
            if ((nvl($data['CorporateName'],'') != '') && nvl($data['CpNameKj'],'') == '') {
                $data['CpNameKj'] = $data['NameKj'];
            }
            // 法人名が入力されている場合、「顧客氏名」は出力しない
            if ((nvl($data['CorporateName'],'') != '')) {
                $data['NameKj'] = '';
            }

            $datas[] = $data;

            // 出力した請求履歴データに対する更新処理
            $sql  = " UPDATE T_ClaimHistory ";
            $sql .= " SET    PrintedStatus = 2 ";
            $sql .= " ,      UpdateId = :UpdateId ";
            $sql .= " ,      UpdateDate = :UpdateDate ";
            $sql .= " WHERE  OrderSeq = :OrderSeq ";
            $sql .= " AND    PrintedFlg = 0 ";
            $sql .= " AND    ValidFlg = 1 ";

            $this->app->dbAdapter->query($sql)->execute(array(
                    ':UpdateId' => $userId,
                    ':UpdateDate' => date('Y-m-d H:i:s'),
                    ':OrderSeq' => $params['P_OrderSeq' . $i]
            ));

            $i++;
        }

        $templateId = 'CKA04016_1'; // 請求書発行(同梱）
        $templateClass = 2;
        $seq = $this->userInfo->EnterpriseId;
        $templatePattern = 0;

        $logicTemplate = new \models\Logic\LogicTemplateSelfBilling(
            $this->app->dbAdapter, $this->app->authManager->getUserInfo()->DispDecimalPoint );
        $response = $logicTemplate->convertArraytoResponse( $datas, sprintf( 'SelfBilling_%s.csv', date('YmdHis') ), $templateId, $templateClass, $seq, $templatePattern, $this->getResponse() );

        if( $response == false ) {
            throw new \Exception( $logicTemplate->getErrorMessage() );
        }

        return $response;
    }

    /**
     * CSVダウンロード
     */
    protected function csvDownload2() {

        $params = $_SESSION[self::SESSION_JOB_PARAMS];

        // ユーザーIDの取得
        $obj = new \models\Table\TableUser($this->app->dbAdapter);
        getUserInfoForMember($this->app, $userClass, $seq);
        $userId = $obj->getUserId($userClass, $seq);

        $datas = array();
        $mdlSitePayment = new TableSiteSbpsPayment($this->app->dbAdapter);
        $i = 0;
        while( isset( $params['P_OrderSeq' . $i] ) ) {
            if( !isset( $params['chkPrintCsv' . $i ] ) ) {
                $i++;
                continue;
            }
            $data = array();
            $prm = array( ':P_OrderSeq' => $params['P_OrderSeq' . $i] );

            $sql  = ' SELECT c.PostalCode ';
            $sql .= ' ,      c.UnitingAddress ';
            $sql .= ' ,      c.NameKj ';
            $sql .= ' ,      o.OrderId ';
            $sql .= ' ,      o.Ent_OrderId ';
            $sql .= ' ,      DATE_FORMAT(o.ReceiptOrderDate, \'%Y/%m/%d\') AS ReceiptOrderDate ';
            $sql .= ' ,      s.SiteNameKj ';
            $sql .= ' ,      s.Url ';
            $sql .= ' ,      s.SiteId ';
            $sql .= ' ,      s.PaymentAfterArrivalFlg ';
            $sql .= ' ,      e.ContactPhoneNumber AS Phone ';
            $sql .= ' ,      ch.ClaimAmount ';
            $sql .= ' ,      DATE_FORMAT(ch.LimitDate, \'%Y/%m/%d\') AS LimitDate ';
            $sql .= ' ,      ca.Cv_BarcodeData ';
            $sql .= ' ,      ca.Cv_BarcodeString1 ';
            $sql .= ' ,      ca.Cv_BarcodeString2 ';
            $sql .= ' ,      ca.Yu_DtCode ';
            $sql .= ' ,      o.Ent_OrderId AS Ent_OrderId2 ';
            $sql .= ' ,      ca.TaxAmount ';
            $sql .= ' ,      ca.SubUseAmount_1 ';
            $sql .= ' ,      ca.SubTaxAmount_1 ';
            $sql .= ' ,      ca.SubUseAmount_2 ';
            $sql .= ' ,      ca.SubTaxAmount_2 ';
            $sql .= ' ,      ca.Cv_ReceiptAgentName ';
            $sql .= ' ,      ca.Cv_SubscriberName ';
            $sql .= ' ,      ca.Bk_BankCode ';
            $sql .= ' ,      ca.Bk_BranchCode ';
            $sql .= ' ,      ca.Bk_BankName ';
            $sql .= ' ,      ca.Bk_BranchName ';
            $sql .= ' ,      ca.Bk_DepositClass ';
            $sql .= ' ,      ca.Bk_AccountNumber ';
            $sql .= ' ,      ca.Bk_AccountHolder ';
            $sql .= ' ,      ca.Bk_AccountHolderKn ';
            $sql .= ' ,      ca.Yu_SubscriberName ';
            $sql .= ' ,      ca.Yu_AccountNumber ';
            $sql .= ' ,      ca.Yu_ChargeClass ';
            $sql .= ' ,      ca.Yu_AccountNumber ';
            $sql .= ' ,      ca.Yu_MtOcrCode1 ';
            $sql .= ' ,      ca.Yu_MtOcrCode2 ';
            $sql .= ' ,      ca.CustomerNumber ';
            $sql .= ' ,      ca.ConfirmNumber ';
            $sql .= ' ,      e.PrintEntComment ';
            $sql .= ' ,      e.PrintEntOrderIdOnClaimFlg ';
            $sql .= ' ,      cd105.KeyContent AS MypageUrl ';
            $sql .= ' ,      c.CorporateName ';
            $sql .= ' ,      c.DivisionName ';
            $sql .= ' ,      c.CpNameKj ';
            $sql .= ' FROM   T_Order o INNER JOIN ';
            $sql .= '        T_Customer c ON ( o.OrderSeq = c.OrderSeq ) INNER JOIN ';
            $sql .= '        T_Enterprise e ON ( o.EnterpriseId = e.EnterpriseId ) INNER JOIN ';
            $sql .= '        T_Site s ON ( o.SiteId = s.SiteId ) INNER JOIN ';
            $sql .= '        T_ClaimHistory ch ON ( o.OrderSeq = ch.OrderSeq AND ch.PrintedFlg = 0 AND ch.ValidFlg = 1 ) INNER JOIN ';
            $sql .= '        T_OemClaimAccountInfo ca ON( ch.Seq = ca.ClaimHistorySeq AND ca.Status = 1 ) LEFT OUTER JOIN ';
            $sql .= '        M_Code cd105 ON( cd105.CodeId = 105 AND IFNULL(o.OemId, 0) = cd105.KeyCode) ';
            $sql .= ' WHERE  o.P_OrderSeq = :P_OrderSeq ';
            $sql .= '   AND  EXISTS (SELECT * FROM T_Order t WHERE t.P_OrderSeq = o.OrderSeq AND t.Cnl_Status = 0) ';
            $data = $this->app->dbAdapter->query( $sql )->execute( $prm )->current();

            if (!$data) {
                // 有効な注文データがない場合はスキップ
                $i++;
                continue;
            }

            //ペイジー収納機関番号取得
            $mdlCode = new TableCode($this->app->dbAdapter);
            $data['Bk_Number'] = $mdlCode->find(LogicPayeasy::PAYEASY_CODEID, LogicPayeasy::BK_NUMBER_KEYCODE)->current()['Note'];

            // 請求金額が30万円以上だった場合
            if( $data['ClaimAmount'] >= 300000 ) {
                $data['Cv_BarcodeData'] = '収納代行の規約によりコンビニエンスストアで30万円以上のお支払はできません';
                $data['Cv_BarcodeString1'] = '';
                $data['Cv_BarcodeString2'] = '';
            }

            // 任意注文番号非表示の加盟店
            if ($data['PrintEntOrderIdOnClaimFlg'] == 0) {
                $data['Ent_OrderId'] = '';
                $data['Ent_OrderId2'] = '';
            }

            // 注文商品
            $sql  = ' SELECT itm.ItemNameKj ';
            $sql .= ' ,      itm.ItemNum ';
            $sql .= ' ,      itm.UnitPrice ';
            $sql .= ' ,      itm.SumMoney ';
            $sql .= ' ,      itm.TaxRate ';
            $sql .= ' FROM   T_Order o INNER JOIN ';
            $sql .= '        T_OrderItems itm ON ( o.OrderSeq = itm.OrderSeq ) ';
            $sql .= ' WHERE  itm.DataClass = 1 AND ';
            $sql .= '        o.P_OrderSeq = :P_OrderSeq ';
            $sql .= ' AND    o.Cnl_Status = 0 ';
            $sql .= ' AND    itm.ValidFlg = 1 ';
            $sql .= ' ORDER BY OrderItemId ';
            $items = ResultInterfaceToArray( $this->app->dbAdapter->query( $sql )->execute( $prm ) );

            // 単価に-が含まれている消費税率を0にする
            for( $j = 1; $j <= 19; $j++ ) {
                if($items[$j - 1]['UnitPrice'] < 0){
                    $items[$j - 1]['TaxRate'] = 0;
                }
            }
            $data['OrderItems'] = $items;

            // 小計
            $sql  = ' SELECT SUM(itm.SumMoney) AS SumMoney ';
            $sql .= ' FROM   T_Order o INNER JOIN ';
            $sql .= '        T_OrderItems itm ON ( o.OrderSeq = itm.OrderSeq ) ';
            $sql .= ' WHERE  itm.DataClass = 1 AND ';
            $sql .= '        o.P_OrderSeq = :P_OrderSeq ';
            $sql .= ' AND    o.Cnl_Status = 0 ';
            $sql .= ' AND    itm.ValidFlg = 1 ';
            $data['TotalItemPrice'] = $this->app->dbAdapter->query( $sql )->execute( $prm )->current()['SumMoney'];
            $data['TotalItemPrice2'] = $data['TotalItemPrice'];//NOTE:同値設定

            // 送料
            $sql  = ' SELECT SUM( itm.SumMoney ) AS CarriageFee ';
            $sql .= ' FROM   T_Order o INNER JOIN ';
            $sql .= '        T_OrderItems itm ON ( o.OrderSeq = itm.OrderSeq ) ';
            $sql .= ' WHERE  itm.DataClass = 2 AND ';
            $sql .= '        o.P_OrderSeq = :P_OrderSeq ';
            $sql .= ' AND    o.Cnl_Status = 0 ';
            $data = array_merge( $data, $this->app->dbAdapter->query( $sql )->execute( $prm )->current() );

            // 決済手数料
            $sql  = ' SELECT SUM( itm.SumMoney ) AS ChargeFee ';
            $sql .= ' FROM   T_Order o INNER JOIN ';
            $sql .= '        T_OrderItems itm ON ( o.OrderSeq = itm.OrderSeq ) ';
            $sql .= ' WHERE  itm.DataClass = 3 AND ';
            $sql .= '        o.P_OrderSeq = :P_OrderSeq ';
            $sql .= ' AND    o.Cnl_Status = 0 ';
            $data = array_merge( $data, $this->app->dbAdapter->query( $sql )->execute( $prm )->current() );

            // 請求回数
            $data['ReIssueCount'] = 0;

            // 店舗からのお知らせ
            $data['PrintEntComment'] = preg_replace('/((\r\n)|[\r\n])/', "\n", f_e($data['PrintEntComment']));  // 改行コードを\nに統一
            $printEntComments = explode("\n", $data['PrintEntComment'], 11);
            for( $j = 1; $j <= 10; $j++ ) {
                $data[sprintf('PrintEntComment%02d', $j)] = isset($printEntComments[$j - 1]) ? $printEntComments[$j - 1] : '';
            }

            // マイページログインパスワード
            $row_mypageorder = $this->app->dbAdapter->query(" SELECT Token FROM T_MypageOrder WHERE OrderSeq = :OrderSeq ORDER BY Seq DESC LIMIT 1 "
            )->execute(array(':OrderSeq' => $params['P_OrderSeq' . $i]))->current();
            $data['MypageToken'] = ($row_mypageorder) ? $row_mypageorder['Token'] : '';

            // 請求書CSV対応
            // ・二重引用符全角の二重引用符に置換
            // ・改行記号（CRFL、CR、LF）は半角スペースに置換
            // ・フォームフィード文字および垂直タブ文字（ASCII：0x0B）は除去
            // ・タブ文字は半角スペースに置換
            $search  = array('"'    , "\r\n"   , "\r"  , "\n"  , "\f"  , "\v" , "\t");
            $replace = array('”'   , ' '      , ' '   , ' '   , ''    , ''   , ' ');
            $data = str_replace($search, $replace, $data);

            // 法人名が入力されており、担当者名がブランクの場合は、「担当者名」へ購入者名を出力する
            if ((nvl($data['CorporateName'],'') != '') && nvl($data['CpNameKj'],'') == '') {
                $data['CpNameKj'] = $data['NameKj'];
            }
            // 法人名が入力されている場合、「顧客氏名」は出力しない
            if ((nvl($data['CorporateName'],'') != '')) {
                $data['NameKj'] = '';
            }

            $data['CreditLimitDate'] = "";
            // 届いてから払い手続き期限日 = 請求履歴.請求日 + 届いてから決済のサイト別の支払可能種類.Max(利用期間)
            if ($data['PaymentAfterArrivalFlg'] == 1) {
                $sql = ' SELECT MIN(ClaimDate) AS MinClaimDate FROM T_ClaimHistory WHERE OrderSeq = :P_OrderSeq ';
                $minClaimDate = $this->app->dbAdapter->query($sql)->execute($prm)->current()['MinClaimDate'];
                $siteId = $data['SiteId'];
                unset($data['SiteId']);
                unset($data['PaymentAfterArrivalFlg']);
                $creditSettlementDays = 0;
                if (!empty($minClaimDate)) {
                    $maxNumUseDay = $mdlSitePayment->getMaxNumUseDay($siteId, $minClaimDate);
                    if (!empty($maxNumUseDay)) {
                        $creditSettlementDays = $maxNumUseDay;
                        $data['CreditLimitDate'] = date('Y/m/d', strtotime($minClaimDate. '+'. $creditSettlementDays. ' days') );
                    }
                }
            }

            $datas[] = $data;

            // 出力した請求履歴データに対する更新処理
            $sql  = " UPDATE T_ClaimHistory ";
            $sql .= " SET    PrintedStatus = 2 ";
            $sql .= " ,      UpdateId = :UpdateId ";
            $sql .= " ,      UpdateDate = :UpdateDate ";
            $sql .= " WHERE  OrderSeq = :OrderSeq ";
            $sql .= " AND    PrintedFlg = 0 ";
            $sql .= " AND    ValidFlg = 1 ";

            $this->app->dbAdapter->query($sql)->execute(array(
                    ':UpdateId' => $userId,
                    ':UpdateDate' => date('Y-m-d H:i:s'),
                    ':OrderSeq' => $params['P_OrderSeq' . $i]
            ));

            $i++;
        }

        $mdlEnt = new TableEnterprise($this->app->dbAdapter);
        $entInfo = $mdlEnt->find($this->userInfo->EnterpriseId)->current();
        $logicpayeasy = new LogicPayeasy($this->app->dbAdapter);

        if($logicpayeasy->isPayeasyOem($entInfo['OemId'])){
            $templateId = 'CKA04016_2'; // ペイジー用請求書発行(同梱）
        }else{
            $templateId = 'CKA04016_1'; // 請求書発行(同梱）
        }
        $templateClass = 2;
        $seq = $this->userInfo->EnterpriseId;
        $templatePattern = 0;

        $logicTemplate = new \models\Logic\LogicTemplateSelfBilling(
        $this->app->dbAdapter, $this->app->authManager->getUserInfo()->DispDecimalPoint );
        $response = $logicTemplate->convertArraytoResponse( $datas, sprintf( 'SelfBilling_%s.csv', date('YmdHis') ), $templateId, $templateClass, $seq, $templatePattern, $this->getResponse() );

        if( $response == false ) {
            throw new \Exception( $logicTemplate->getErrorMessage() );
        }

        return $response;
    }

    /**
     * CSV設定画面
     */
    public function csvsettingAction() {
        // メッセージ設定
        if (isset($_SESSION[self::SESS_AFTER_MSG])) {
            $this->view->assign( 'message', $_SESSION[self::SESS_AFTER_MSG] );
            unset($_SESSION[self::SESS_AFTER_MSG]);
        }

        // タイトルの設定
        $this->setPageTitle( '請求書　CSV設定' );

        $mdlent = new TableEnterprise($this->app->dbAdapter);
        $eid = $this->userInfo->EnterpriseId;
        $ent = $mdlent->find($eid)->current();

        $logicpayeasy = new LogicPayeasy($this->app->dbAdapter);
        if($logicpayeasy->isPayeasyOem($ent['OemId'])){
            $templateId = 'CKA04016_2'; // ペイジー用請求書発行(同梱）
        }else{
            $templateId = 'CKA04016_1'; // 請求書発行(同梱）
        }
        $templateClass = 2;
        $templatePattern = 0;

        // TemplateSeq取得
        $mdlth = new TableTemplateHeader( $this->app->dbAdapter );
        $templateSeq = $mdlth->getTemplateSeq( $templateId, $templateClass, $eid, $templatePattern, false );
        $mdltf = new TableTemplateField( $this->app->dbAdapter );

        // テンプレートが見つからなかったらCBの設定を元に新規作成
        if( empty( $templateSeq ) ) {
            $templateSeq = $mdlth->getTemplateSeq( $templateId, 0, 0, 0, false );
            if( empty( $templateSeq ) ) {
                throw new \Exception( 'テンプレートが存在しません。' );
            }
            if( !empty( $templateSeq ) ) {
                // ユーザーIDの取得
                $mdlu = new TableUser( $this->app->dbAdapter );
                getUserInfoForMember( $this->app, $userClass, $seq );
                $userId = $mdlu->getUserId( $userClass, $seq );

                // 新しくテンプレート作成
                $header = $mdlth->find( $templateSeq )->current();
                $header['TemplateClass'] = $templateClass;
                $header['Seq'] = $eid;
                $header['TemplatePattern'] = $templatePattern;
                $header['RegistId'] = $userId;
                $header['UpdateId'] = $userId;

                $newTemplateSeq = $mdlth->saveNew( $header );

                $fields = ResultInterfaceToArray( $mdltf->get( $templateSeq ) );
                foreach( $fields as $field ) {
                    $field['TemplateSeq'] = $newTemplateSeq;
                    $field['RegistId'] = $userId;
                    $field['UpdateId'] = $userId;
                    $mdltf->saveNew( $field );
                }
                $templateSeq = $newTemplateSeq;
            }
        }
        $data = $mdlth->find( $templateSeq )->current();

        $encodes = array( 'SJIS-win', 'UTF-8', 'eucJP-win', 'UTF-8(BOM)' );

        $reserve = Json::decode($data['Reserve'], Json::TYPE_ARRAY);

        // 商品明細出力区分
        $itemsType = isset($reserve['itemsType']) ? $reserve['itemsType'] : 0;
        if ($ent['SystemClass'] == 1) {
            // 新システムは新レイアウト固定、操作不可
            $itemsType = 0;
            $option = 'disabled';
        }
        $itemsTypeList = BaseHtmlUtils::SelectTag('itemsType', array( 0 => '新レイアウト', 1 => '旧レイアウト'), $itemsType, $option);

        $this->view->assign( 'data', $data );
        $this->view->assign( 'encodes', $encodes );
        $this->view->assign( 'items', (int)$reserve['items'] ); // 商品明細数
        $this->view->assign( 'itemsType', $itemsTypeList );

        return $this->view;
    }

	/**
     * CSV設定変更画面
     */
    public function updateAction() {

        // パラメータ取得
        $params = $this->getParams();

        $tId = $params['tid'];
        $tClass = $params['tclass'];
        $eId = $params['eid'];

        // ユーザーIDの取得
        $mdlu = new TableUser( $this->app->dbAdapter );
        getUserInfoForMember( $this->app, $userClass, $seq );
        $userId = $mdlu->getUserId( $userClass, $seq );

        // テンプレートSeq取得
        $mdlth = new TableTemplateHeader( $this->app->dbAdapter );
        $templateSeq = $mdlth->getTemplateSeq( $tId, $tClass, $eId, 0 );

        // テンプレートヘッダー更新
        $data['UpdateId'] = $userId;
        $data['TitleClass'] = ( isset( $params['title'] ) ) ? 1 : 0;
        $data['Reserve'] = Json::encode(array('items' => $params['items'], 'itemsType' => isset($params['itemsType']) ? $params['itemsType'] : 0));
        $data['CharacterCode'] = $params['encode'];
        $data['NoDataFieldSettingFlg'] = ( isset( $params['nodatafield'] ) ) ? 1 : 0;

        $mdlth->saveUpdate( $data, $templateSeq  );

        // メッセージ設定
        $_SESSION[self::SESS_AFTER_MSG] = '<font color="red"><b>請求書出力CSVの設定を更新しました。</b></font>';

        // リダイレクト
        return $this->_redirect('rwclaim/csvsetting');
    }

    /**
     * 請求関連処理ファンクションの基礎SQL取得。
     *
     * @return 請求関連処理ファンクションの基礎SQL
     */
    protected function getBaseP_ClaimControl() {
        return <<<EOQ
CALL P_ClaimControl(
    :pi_history_seq
,   :pi_button_flg
,   :pi_user_id
,   @po_ret_sts
,   @po_ret_errcd
,   @po_ret_sqlcd
,   @po_ret_msg
    )
EOQ;
    }

    /**
     * 初期設定処理
     */
    public function initAction() {

        // パラメータ取得
        $params = $this->getParams();

        $tId = $params['tid'];
        $tClass = $params['tclass'];
        $eId = $params['eid'];

        // ユーザーIDの取得
        $mdlu = new TableUser( $this->app->dbAdapter );
        getUserInfoForMember( $this->app, $userClass, $seq );
        $userId = $mdlu->getUserId( $userClass, $seq );

        // テンプレートSeq取得
        $mdlth = new TableTemplateHeader( $this->app->dbAdapter );
        $templateSeq = $mdlth->getTemplateSeq( $tId, $tClass, $eId, 0 );

        // テンプレートヘッダー更新
        $data['UpdateId'] = $userId;
        $data['TitleClass'] = 0;                // チェックオフ
        $data['CharacterCode'] = 'SJIS-win';    // Shift-JIS
        $data['NoDataFieldSettingFlg'] = 0;     // チェックオフ
        $data['Reserve'] = Json::encode(array('items' => '16'));    // 商品明細数

        $mdlth->saveUpdate( $data, $templateSeq );

        $_SESSION[self::SESS_AFTER_MSG] = '<font color="red"><b>請求書出力CSVの設定を初期化しました。</b></font>';

        // リダイレクト
        return $this->_redirect('rwclaim/csvsetting');
    }

    /**
     * CSV設定変更画面
     */
    public function changecsvAction() {
        $this->clearStyleSheets();
        $this->addStyleSheet( '../../oemmember/css/members.css' );
        $this->addStyleSheet( '../../oemmember/css/column_table.css' );
        $this->addJavaScript( '../../oemmember/js/column_table.js' );

        $params = $this->getParams();

        $tId = $params['tid'];
        $tClass = $params['tclass'];
        $eId = $params['eid'];

        // ユーザーIDの取得
        $mdlu = new TableUser( $this->app->dbAdapter );
        getUserInfoForMember( $this->app, $userClass, $seq );
        $userId = $mdlu->getUserId( $userClass, $seq );

        // テンプレートSeq取得
        $mdlth = new TableTemplateHeader( $this->app->dbAdapter );
        $templateSeq = $mdlth->getTemplateSeq( $tId, $tClass, $eId, 0 );

        //加盟店情報を取得
        $mdlent = new TableEnterprise($this->app->dbAdapter);
        $ent = $mdlent->find($eId)->current();

        // ListNumber順にTemplateFieldを取り出す
        $mdltf = new TableTemplateField( $this->app->dbAdapter );
        $templateFieldList = ResultInterfaceToArray( $mdltf->get( $templateSeq ) );

        //加盟店に紐付く届いてから決済を利用するサイトの数を取得
        $sql = ' SELECT COUNT(1) AS cnt FROM T_Enterprise e INNER JOIN T_Site s ON s.EnterpriseId = e.EnterpriseId WHERE e.EnterpriseId = :EnterpriseId and s.PaymentAfterArrivalFlg = 1 ';
        $cnt = $this->app->dbAdapter->query($sql)->execute(array(':EnterpriseId' => $eId))->current()['cnt'];

        $validList = array();
        $invalidList = array();

        //項目の設定
        if($tId == 'CKA04016_1' || $tId == 'CKA04016_2' && date('Y-m-d') > '2019-09-30'){
            foreach( $templateFieldList as $templateField ) {
                //クレジット手続き期限日 かつ 届いてから決済を利用するサイトを含まない場合continue
                if( $templateField['PhysicalName'] == 'CreditLimitDate' && $cnt <= 0){
                    continue;
                }
                if( $templateField['ValidFlg'] == 1 ) {
                    $validList[] = $templateField;
                }
                elseif( $templateField['ValidFlg'] == 0 ) {
                    $invalidList[] = $templateField;
                }
            }
        }else{
            foreach( $templateFieldList as $templateField ) {
                //クレジット手続き期限日 かつ 届いてから決済を利用するサイトを含まない場合continue
                if( $templateField['PhysicalName'] == 'CreditLimitDate' && $cnt <= 0){
                    continue;
                }
                if( $templateField['ValidFlg'] == 1 ) {
                    $validList[] = $templateField;
                }
                elseif( $templateField['ValidFlg'] == 0 && (!(($templateField['LogicalName'] == '消費税率' || $templateField['LogicalName'] == '８％対象合計金額' || $templateField['LogicalName'] == '８％対象消費税額' || $templateField['LogicalName'] == '１０％対象合計金額' || $templateField['LogicalName'] == '１０％対象消費税額' || $templateField['LogicalName'] == '事業者登録番号')))) {
                    $invalidList[] = $templateField;
                }
            }
        }

        // テンプレート設定
        $this->setTemplate('changecsv');

        // 名称取得
        $templateName = $mdlth->find( $templateSeq )->current()['TemplateName'];

        // タイトルの設定
        $this->setPageTitle( 'テンプレートID：' . $tId . '　テンプレート名：' . $templateName );

        $this->view->assign( 'validList', $validList );
        $this->view->assign( 'invalidList', $invalidList );

        $this->view->assign( 'userId', $userId );
        $this->view->assign( 'templateSeq', $templateSeq );

        // リダイレクト先設定
        $redirect = 'rwclaim/changecsv/tid/' .$tId . '/tclass/' . $tClass .'/eid/' . $eId;
        $this->view->assign( 'redirect', $redirect );

        return $this->view;
    }

    /**
     * SMBCバーチャル口座オープン用のロックアイテムを獲得する
     *
     * @access protected
     * @param array 対象注文の行オブジェクト
     * @return \models\Logic\ThreadPool\LogicThreadPoolItem | null
     */
    protected function getLockItemForSmbcpaAccount($orderRow = null)
    {
        if(!$orderRow) return null;

        $smbcpaTable = new \models\Table\TableSmbcpa($this->app->dbAdapter);
        $smbcpa = $smbcpaTable->findByOemId((int)$orderRow['OemId'])->current();
        if(!$smbcpa) return null;

        $pool = \models\Logic\LogicThreadPool::getPoolForSmbcpaAccountOpen($smbcpa['SmbcpaId'], $this->app->dbAdapter);
        return $pool->openAsSingleton($orderRow['OrderSeq']);
    }

    /**
     * 請求書余白設定画面
     */
    public function printadjustAction()
    {
        // タイトルの設定
        $this->setPageTitle( '請求書　余白設定' );

        // メッセージ設定
        if (isset($_SESSION[self::SESS_PA_AFTER_MSG])) {
            $this->view->assign( 'message', $_SESSION[self::SESS_PA_AFTER_MSG] );
            unset($_SESSION[self::SESS_PA_AFTER_MSG]);
        }

        // 加盟店情報取得
        $mdlent = new TableEnterprise($this->app->dbAdapter);

        $ent = $mdlent->find($this->userInfo->EnterpriseId)->current();

        // 上マージン、左マージン設定
        $this->view->assign('PrintAdjustmentY', $ent['PrintAdjustmentY']);
        $this->view->assign('PrintAdjustmentX', $ent['PrintAdjustmentX']);

        return $this->view;
    }

    public function saveprintadjustAction()
    {
        // パラメータ取得
        $params = $this->getParams();

        // 入力内容チェック
        $errors = array();
        // 上マージン
        if (!isset($params['PrintAdjustmentY']) || strlen($params['PrintAdjustmentY']) == 0) {
            // 上マージンが未入力
            $errors[] = "上マージンが未入力です。";
        }
        else {
            // 上マージンの入力あり
            if (!BaseReflectionUtility::isInteger($params['PrintAdjustmentY'])) {
                // 上マージンが整数でない
                $errors[] = "上マージンには整数を入力してください。";
            }
        }
        // 左マージン
        if (!isset($params['PrintAdjustmentX']) || strlen($params['PrintAdjustmentX']) == 0) {
            // 左マージンが未入力
            $errors[] = "左マージンが未入力です。";
        }
        else {
            // 左マージンの入力あり
            if (!BaseReflectionUtility::isInteger($params['PrintAdjustmentX'])) {
                // 左マージンが整数でない
                $errors[] = "左マージンには整数を入力してください。";
            }
        }
        if (!empty($errors)) {
            // タイトルの設定
            $this->setPageTitle( '請求書　余白設定' );

            // エラー情報設定
            $this->view->assign('errors', $errors);

            // 上マージン、左マージン設定
            $this->view->assign('PrintAdjustmentY', $params['PrintAdjustmentY']);
            $this->view->assign('PrintAdjustmentX', $params['PrintAdjustmentX']);

            // 元の画面に戻る
            $this->setTemplate('printadjust');
            return $this->view;
        }
        else {
            // ユーザーIDの取得
            $mdlu = new TableUser( $this->app->dbAdapter );
            getUserInfoForMember( $this->app, $userClass, $seq );
            $userId = $mdlu->getUserId( $userClass, $seq );

            //加盟店更新
            $mdlent = new TableEnterprise($this->app->dbAdapter);

            $mdlent->saveUpdate(array(
                    'PrintAdjustmentY' => $params['PrintAdjustmentY'],
                    'PrintAdjustmentX' => $params['PrintAdjustmentX'],
                    'UpdateId' => $userId,

            ), $this->userInfo->EnterpriseId);

            // メッセージ設定
            $_SESSION[self::SESS_PA_AFTER_MSG] = '<font color="red"><b>請求書余白の設定を更新しました。</b></font>';

            return $this->_redirect('rwclaim/printadjust');
        }
    }

    /**
     * ジョブ転送処理を行う
     * @param array $prmCeSeqs 請求エラーSEQを配列に格納する
     * @throws Ambigous <Exception, \models\Logic\Exception\LogicClaimException>
     */
    protected function jobTransfer(&$prmCeSeqs) {

        $params = $_SESSION[self::SESSION_JOB_PARAMS];

        $mdlch = new TableClaimHistory($this->app->dbAdapter);
        $mdls = new TableSite($this->app->dbAdapter);
        $mdlo = new TableOrder($this->app->dbAdapter);
        $mdlce = new TableClaimError($this->app->dbAdapter);
        $mdloi = new TableOrderItems($this->app->dbAdapter);

        $payings = new TablePayingAndSales($this->app->dbAdapter);
        $logicmo = new LogicMypageOrder($this->app->dbAdapter);

        // 請求書発行手数料を取得 → T_Site.ClaimFeeDK または T_Site.ClaimFeeBS
        // 注文に紐づくサイト情報を取得する。（キャンペーン対応含む）
        $lgcc = new LogicCampaign($this->app->dbAdapter);
        $mdlSysP = new TableSystemProperty($this->app->dbAdapter);

        // ユーザーIDの取得
        $obj = new \models\Table\TableUser($this->app->dbAdapter);
        getUserInfoForMember($this->app, $userClass, $seq);
        $userId = $obj->getUserId($userClass, $seq);

        // 認証関連
        $authUtil = $this->app->getAuthUtility();

        $i = 0;
        $transferCount = 0;
        $errorCount = 0;
        $prmCeSeqs = array();

        while (isset($params['P_OrderSeq' . $i])) {
            if (!isset($params['chkPrintCsv' . $i])) { $i++; continue; }
            $oseq = $params['P_OrderSeq' . $i];

            // ----------------------------------------
            // チェック処理
            // ----------------------------------------
            // 有効な注文か
            $sql = <<<EOQ
SELECT COUNT(*) AS cnt
  FROM T_Order o
 WHERE EXISTS(SELECT * FROM T_Order t WHERE t.P_OrderSeq = o.OrderSeq AND t.Cnl_Status = 0)
   AND o.OrderSeq = :OrderSeq
   AND o.DataStatus = 41
EOQ;
            $prm = array(
                    ':OrderSeq' => $oseq,
            );
            $ret = $this->app->dbAdapter->query($sql)->execute($prm)->current()['cnt'];
            if ($ret == 0) {
                // 有効な注文がいない場合はスキップ
                $i++;
                continue;
            }

            // 注文データを取得
            $order = $mdlo->find($oseq)->current();

            // SMBCバーチャル口座オープン用にロック獲得を試行
            $lockItem = $this->getLockItemForSmbcpaAccount($order);

            // ジョブ転送中か
            if ($mdlch->getReservedCount($oseq) > 0) {
                try {
                    if($lockItem) {
                        $lockItem->terminate();
                    }
                } catch (\Exception $e) { ; }
                // ジョブ転送中のデータがいる場合はスキップ
                $i++;
                continue;
            }

            try {
                // トランザクション開始
                $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();

                $propertyTable = new TableSystemProperty($this->app->dbAdapter);
                $taxRate = $propertyTable->getTaxRateAt(date('Y-m-d'));

                // 注文商品の更新
                $taxrateData = array(
                        'TaxRate' => $taxRate, // 消費税率
                        'UpdateId' => $userId, // 更新者
                );
                $mdloi->updateTaxrateBefore($taxrateData,$oseq);

                // 請求金額の再取得
                //原則画面と同じになるが、一部キャンセルされた場合を想定
$sql = <<<EOQ
SELECT SUM(UseAmount) AS UseAmount
  FROM T_Order o
 WHERE o.Cnl_Status = 0
   AND o.P_OrderSeq = :OrderSeq
EOQ;
                $prm = array(
                        ':OrderSeq' => $oseq,
                );
                $useAmount = $this->app->dbAdapter->query($sql)->execute($prm)->current()['UseAmount'];

                $limitDays = $this->app->selfBillingConfig['payment_limit_days'];

                $limitDate = $mdls->getLimitDate($params['SiteId' . $i], isset($limitDays) ? $limitDays : 14);

                // 請求履歴の作成
                $data = array(
                        'OrderSeq' => $oseq,                                                                                    // 注文Seq
                        'ClaimDate' => date('Y-m-d'),                                                                           // 請求日
                        'ClaimCpId' => -1,                                                                                      // 請求担当者
                        'ClaimPattern' => 1,                                                                                    // 請求パターン（初回請求）
                        'LimitDate' => $limitDate,                                                                              // 支払期限
                        'DamageDays' => 0,                                                                                      // 遅延日数
                        'DamageInterestAmount' => 0,                                                                            // 遅延損害金
                        'ClaimFee' => 0,                                                                                        // 請求手数料
                        'AdditionalClaimFee' => 0,                                                                              // 請求追加手数料
                        'PrintedFlg' => 0,                                                                                      // 印刷－処理フラグ
                        'MailFlg' => 0,                                                                                         // 請求書発行通知メール
                        'EnterpriseBillingCode' => $this->app->authManager->getUserInfo()->EnterpriseId,                        // 同梱ツールアクセスキー（次期システムでは加盟店ID）
                        'ClaimAmount' => $useAmount,                                                                            // 請求金額
                        'RegistId' => $userId,                                                                                  // 登録者
                        'UpdateId' => $userId,                                                                                  // 更新者
                  );
                $hisSeq = $mdlch->saveNew($oseq, $data);

                // 立替・売上管理の更新
                // 立替・売上管理更新用に親注文Seqから子注文Seqを再取得する。
$sql = <<<EOQ
                SELECT OrderSeq, EnterpriseId, SiteId
                  FROM T_Order
                 WHERE P_OrderSeq = :P_OrderSeq
                   AND Cnl_Status = 0
EOQ;
                $ri = $this->app->dbAdapter->query($sql)->execute(array(':P_OrderSeq' => $oseq));
                $rows = ResultInterfaceToArray($ri);

                // 取得できた件数分ループする
                foreach ($rows as $row) {

                    // 親注文と子注文で処理をわける
                    if ($row['OrderSeq'] == $oseq) {
                    $campdata = $lgcc->getCampaignInfo($row['EnterpriseId'], $row['SiteId']);
                    $fee = CoralValidate::isInt($campdata['ClaimFeeDK']) ? (int)$campdata['ClaimFeeDK'] : (int)$campdata['ClaimFeeBS'];
                    // 税込み金額に変換
                    $fee = $mdlSysP->getIncludeTaxAmount(date('Y-m-d'), $fee);
                    } else {
                        // 子注文の場合、請求手数料は 0
                        $fee = 0;
                    }

                    // 対応するT_PayingAndSalesの請求手数料（ClaimFee）を同梱出力時用に更新する
                    $paying_row = $payings->findPayingAndSales(array('OrderSeq' => $row['OrderSeq']))->current();

                    // 請求手数料が変更されたので立替金額（ChargeAmount）の再計算を行う（2013.9.27 eda）
                    $charge_amount = $paying_row['UseAmount'] - ($paying_row['SettlementFee'] + $fee);

                    // 請求手数料と立替金額のみ更新
                    $payings->saveUpdate( array('ClaimFee' => $fee, 'ChargeAmount' => $charge_amount, 'UpdateId' => $userId), $paying_row['Seq'] );
                }

                //OEMID取得
                $oem_id = $mdlo->getOemId($oseq);

                //OEM判定
                if(!is_null($oem_id) && $oem_id != 0){

                    $mdlocf = new TableOemClaimFee($this->app->dbAdapter);

                    //OEM請求手数料書き込み
                    $mdlocf->saveOemClaimFee($oseq, $userId, true);

                }

                // 注文の確定待ちフラグをアップ
                $uOrder = array(
                        'ConfirmWaitingFlg' => '1',
                        'UpdateId'          => $userId,
                );
                $mdlo->saveUpdateWhere($uOrder, array('P_OrderSeq' => $oseq));

                // 注文マイページを作成する
                $logicmo->createMypageOrder($oseq, $limitDate, $oem_id, $userId, $authUtil);

                // コミット
                $this->app->dbAdapter->getDriver()->getConnection()->commit();

            } catch (\Exception $e) {
                // ロールバック
                $this->app->dbAdapter->getDriver()->getConnection()->rollback();

                $ceSeq = 0;

                // SMBC連携エラー
                if ($e instanceof LogicClaimException) {
                    // エラー情報を登録
                    $data = array(
                            'OrderSeq' => $oseq,
                            'ErrorCode' => $e->getCode(),      // 20171207 初期実装時点ではSMBC連携エラーのみ
                            'ErrorMsg' => $e->getMessage(),
                    );
                    $ceSeq = $mdlce->saveNew($data);
                    $prmCeSeqs[] = $ceSeq;
                }

                // ロックを獲得していたら開放
                try {
                    if($lockItem) {
                        $lockItem->terminate();
                    }
                } catch (\Exception $err) { ; }

                if ($ceSeq == 0) {
                    // 処理失敗
                    throw $e;
                }
            }

            $i++;

            // ロックを獲得していたら開放
            try {
                if($lockItem) {
                    $lockItem->terminate();
                }
            } catch (\Exception $e) { ; }
        }
    }

    /**
     * ジョブ転送処理を行う
     * @param array $prmCeSeqs 請求エラーSEQを配列に格納する
     * @throws Ambigous <Exception, \models\Logic\Exception\LogicClaimException>
     */
    protected function jobTransfer2(&$prmCeSeqs) {

        $params = $_SESSION[self::SESSION_JOB_PARAMS];

        $mdlch = new TableClaimHistory($this->app->dbAdapter);
        $mdls = new TableSite($this->app->dbAdapter);
        $mdlo = new TableOrder($this->app->dbAdapter);
        $mdlce = new TableClaimError($this->app->dbAdapter);
        $mdloi = new TableOrderItems($this->app->dbAdapter);

        $payings = new TablePayingAndSales($this->app->dbAdapter);
        $logicmo = new LogicMypageOrder($this->app->dbAdapter);

        // 請求書発行手数料を取得 → T_Site.ClaimFeeDK または T_Site.ClaimFeeBS
        // 注文に紐づくサイト情報を取得する。（キャンペーン対応含む）
        $lgcc = new LogicCampaign($this->app->dbAdapter);
        $mdlSysP = new TableSystemProperty($this->app->dbAdapter);

        // ユーザーIDの取得
        $obj = new \models\Table\TableUser($this->app->dbAdapter);
        getUserInfoForMember($this->app, $userClass, $seq);
        $userId = $obj->getUserId($userClass, $seq);

        // 認証関連
        $authUtil = $this->app->getAuthUtility();

        $i = 0;
        $transferCount = 0;
        $errorCount = 0;
        $prmCeSeqs = array();

        while (isset($params['P_OrderSeq' . $i])) {
            if (!isset($params['chkPrintCsv' . $i])) { $i++; continue; }
            $oseq = $params['P_OrderSeq' . $i];

            // ----------------------------------------
            // チェック処理
            // ----------------------------------------
            // 有効な注文か
            $sql = <<<EOQ
SELECT COUNT(*) AS cnt
  FROM T_Order o
 WHERE EXISTS(SELECT * FROM T_Order t WHERE t.P_OrderSeq = o.OrderSeq AND t.Cnl_Status = 0)
   AND o.OrderSeq = :OrderSeq
   AND o.DataStatus = 41
EOQ;
            $prm = array(
                    ':OrderSeq' => $oseq,
            );
            $ret = $this->app->dbAdapter->query($sql)->execute($prm)->current()['cnt'];
            if ($ret == 0) {
                // 有効な注文がいない場合はスキップ
                $i++;
                continue;
            }

            // 注文データを取得
            $order = $mdlo->find($oseq)->current();

            // SMBCバーチャル口座オープン用にロック獲得を試行
            $lockItem = $this->getLockItemForSmbcpaAccount($order);

            // ジョブ転送中か
            if ($mdlch->getReservedCount($oseq) > 0) {
                try {
                    if($lockItem) {
                        $lockItem->terminate();
                    }
                } catch (\Exception $e) { ; }
                // ジョブ転送中のデータがいる場合はスキップ
                $i++;
                continue;
            }

            try {
                // トランザクション開始
                $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();

                $propertyTable = new TableSystemProperty($this->app->dbAdapter);
                $taxRate = $propertyTable->getTaxRateAt(date('Y-m-d'));

                // 注文商品の更新
                $taxrateData = array(
                        'TaxRate' => $taxRate, // 消費税率
                        'UpdateId' => $userId, // 更新者
                );
                $mdloi->updateTaxrate($taxrateData,$oseq);

                // 請求金額の再取得
                //原則画面と同じになるが、一部キャンセルされた場合を想定
                $sql = <<<EOQ
SELECT SUM(UseAmount) AS UseAmount
  FROM T_Order o
 WHERE o.Cnl_Status = 0
   AND o.P_OrderSeq = :OrderSeq
EOQ;
                $prm = array(
                        ':OrderSeq' => $oseq,
                );
                $useAmount = $this->app->dbAdapter->query($sql)->execute($prm)->current()['UseAmount'];

                $limitDays = $this->app->selfBillingConfig['payment_limit_days'];

                $limitDate = $mdls->getLimitDate($params['SiteId' . $i], isset($limitDays) ? $limitDays : 14);

                // 請求履歴の作成
                $data = array(
                        'OrderSeq' => $oseq,                                                                                    // 注文Seq
                        'ClaimDate' => date('Y-m-d'),                                                                           // 請求日
                        'ClaimCpId' => -1,                                                                                      // 請求担当者
                        'ClaimPattern' => 1,                                                                                    // 請求パターン（初回請求）
                        'LimitDate' => $limitDate,                                                                              // 支払期限
                        'DamageDays' => 0,                                                                                      // 遅延日数
                        'DamageInterestAmount' => 0,                                                                            // 遅延損害金
                        'ClaimFee' => 0,                                                                                        // 請求手数料
                        'AdditionalClaimFee' => 0,                                                                              // 請求追加手数料
                        'PrintedFlg' => 0,                                                                                      // 印刷－処理フラグ
                        'MailFlg' => 0,                                                                                         // 請求書発行通知メール
                        'EnterpriseBillingCode' => $this->app->authManager->getUserInfo()->EnterpriseId,                        // 同梱ツールアクセスキー（次期システムでは加盟店ID）
                        'ClaimAmount' => $useAmount,                                                                            // 請求金額
                        'RegistId' => $userId,                                                                                  // 登録者
                        'UpdateId' => $userId,                                                                                  // 更新者
                );
                $hisSeq = $mdlch->saveNew2($oseq, $data);

                // 立替・売上管理の更新
                // 立替・売上管理更新用に親注文Seqから子注文Seqを再取得する。
                $sql = <<<EOQ
                SELECT OrderSeq, EnterpriseId, SiteId
                  FROM T_Order
                 WHERE P_OrderSeq = :P_OrderSeq
                   AND Cnl_Status = 0
EOQ;
                $ri = $this->app->dbAdapter->query($sql)->execute(array(':P_OrderSeq' => $oseq));
                $rows = ResultInterfaceToArray($ri);

                // 取得できた件数分ループする
                foreach ($rows as $row) {

                    // 親注文と子注文で処理をわける
                    if ($row['OrderSeq'] == $oseq) {
                        $campdata = $lgcc->getCampaignInfo($row['EnterpriseId'], $row['SiteId']);
                        $fee = CoralValidate::isInt($campdata['ClaimFeeDK']) ? (int)$campdata['ClaimFeeDK'] : (int)$campdata['ClaimFeeBS'];
                        // 税込み金額に変換
                        $fee = $mdlSysP->getIncludeTaxAmount(date('Y-m-d'), $fee);
                    } else {
                        // 子注文の場合、請求手数料は 0
                        $fee = 0;
                    }

                    // 対応するT_PayingAndSalesの請求手数料（ClaimFee）を同梱出力時用に更新する
                    $paying_row = $payings->findPayingAndSales(array('OrderSeq' => $row['OrderSeq']))->current();

                    // 請求手数料が変更されたので立替金額（ChargeAmount）の再計算を行う（2013.9.27 eda）
                    $charge_amount = $paying_row['UseAmount'] - ($paying_row['SettlementFee'] + $fee);

                    // 請求手数料と立替金額のみ更新
                    $payings->saveUpdate( array('ClaimFee' => $fee, 'ChargeAmount' => $charge_amount, 'UpdateId' => $userId), $paying_row['Seq'] );
                }

                //OEMID取得
                $oem_id = $mdlo->getOemId($oseq);

                //OEM判定
                if(!is_null($oem_id) && $oem_id != 0){

                    $mdlocf = new TableOemClaimFee($this->app->dbAdapter);

                    //OEM請求手数料書き込み
                    $mdlocf->saveOemClaimFee($oseq, $userId, true);

                }

                // 注文の確定待ちフラグをアップ
                $uOrder = array(
                        'ConfirmWaitingFlg' => '1',
                        'UpdateId'          => $userId,
                );
                $mdlo->saveUpdateWhere($uOrder, array('P_OrderSeq' => $oseq));

                // 注文マイページを作成する
                $logicmo->createMypageOrder($oseq, $limitDate, $oem_id, $userId, $authUtil);

                // コミット
                $this->app->dbAdapter->getDriver()->getConnection()->commit();

            } catch (\Exception $e) {
                // ロールバック
                $this->app->dbAdapter->getDriver()->getConnection()->rollback();

                $ceSeq = 0;

                // SMBC連携エラー
                if ($e instanceof LogicClaimException) {
                    // エラー情報を登録
                    $data = array(
                            'OrderSeq' => $oseq,
                            'ErrorCode' => $e->getCode(),      // 20171207 初期実装時点ではSMBC連携エラーのみ
                            'ErrorMsg' => $e->getMessage(),
                    );
                    $ceSeq = $mdlce->saveNew($data);
                    $prmCeSeqs[] = $ceSeq;
                }

                // ロックを獲得していたら開放
                try {
                    if($lockItem) {
                        $lockItem->terminate();
                    }
                } catch (\Exception $err) { ; }

                if ($ceSeq == 0) {
                    // 処理失敗
                    throw $e;
                }
            }

            $i++;

            // ロックを獲得していたら開放
            try {
                if($lockItem) {
                    $lockItem->terminate();
                }
            } catch (\Exception $e) { ; }
        }
    }

    /**
     * PDFダウンロード
     */
    protected function pdfDownload() {

        $params = $_SESSION[self::SESSION_JOB_PARAMS];

        // 余白設定
        $mdlEnt = new TableEnterprise($this->app->dbAdapter);
        $enterprise = $mdlEnt->find($this->app->authManager->getUserInfo()->EnterpriseId)->current();

        // 用紙
        $paperType = isset($params['paperTypeVal']) ? $params['paperTypeVal'] : 1;

        // バーコード、QRコード作成用
        $barcode = Application::getInstance()->config['barcode'];
        set_include_path(get_include_path() . PATH_SEPARATOR . $barcode['barcode_lib']);
        require_once 'QR.php';
        require_once 'EAN128.php';
        $qrCode = new \SharedQR();
        $qrCode->version = 5;           // バージョン 1～40を指定　デフォルト5
        $qrCode->error_level = 'M';     // エラーレベル　L,M,Q,Hを指定　デフォルトM
        $ean128 = new \EAN128();
        $ean128->TextWrite = false;

        $datas = array();
        $i = 0;
        while( isset( $params['P_OrderSeq' . $i] ) ) {
            if( !isset( $params['chkPrintCsv' . $i ] ) ) {
                $i++;
                continue;
            }
            $data = array();
            $prm = array( ':P_OrderSeq' => $params['P_OrderSeq' . $i] );

            $sql  = ' SELECT c.PostalCode ';
            $sql .= ' ,      c.UnitingAddress ';
            $sql .= ' ,      c.NameKj ';
            $sql .= ' ,      o.OrderId ';
            $sql .= ' ,      o.Ent_OrderId ';
            $sql .= ' ,      o.ReceiptOrderDate ';
            $sql .= ' ,      s.SiteNameKj ';
            $sql .= ' ,      e.ContactPhoneNumber ';
            $sql .= ' ,      e.PrintEntComment ';
            $sql .= ' ,      e.PrintEntOrderIdOnClaimFlg ';
            $sql .= ' ,      ch.ClaimAmount ';
            $sql .= ' ,      ch.LimitDate ';
            $sql .= ' ,      ch.ClaimFee ';
            $sql .= ' ,      ch.AdditionalClaimFee ';
            $sql .= ' ,      ch.DamageInterestAmount ';
            $sql .= ' ,      ca.Cv_BarcodeData ';
            $sql .= ' ,      ca.Cv_BarcodeString1 ';
            $sql .= ' ,      ca.Cv_BarcodeString2 ';
            $sql .= ' ,      ca.TaxAmount ';
            $sql .= ' ,      ca.Bk_BankName ';
            $sql .= ' ,      ca.Bk_BranchName ';
            $sql .= ' ,      ca.Bk_DepositClass ';
            $sql .= ' ,      ca.Bk_AccountNumber ';
            $sql .= ' ,      ca.Bk_AccountHolderKn ';
            $sql .= ' ,      ca.Yu_SubscriberName ';
            $sql .= ' ,      ca.Yu_AccountNumber ';
            $sql .= ' ,      ca.Yu_MtOcrCode1 ';
            $sql .= ' ,      ca.Yu_MtOcrCode2 ';
            $sql .= ' ,      IFNULL(o.OemId, 0) AS OemId ';
            $sql .= ' ,      oem.PostalCode AS OemPostalCode ';
            $sql .= ' ,      oem.PrefectureName AS OemPrefectureName ';
            $sql .= ' ,      oem.City AS OemCity ';
            $sql .= ' ,      oem.Town AS OemTown ';
            $sql .= ' ,      oem.Building AS OemBuilding ';
            $sql .= ' ,      oem.OemNameKj ';
            $sql .= ' ,      oem.ContactPhoneNumber AS OemContactPhoneNumber ';
            $sql .= ' ,      oem.LargeLogo AS OemLogo ';
            $sql .= ' ,      cd105.KeyContent AS MypageUrl ';
            $sql .= ' ,      cd108.KeyContent AS PrintContactPhoneNumber ';
            $sql .= ' FROM   T_Order o INNER JOIN ';
            $sql .= '        T_Customer c ON ( o.OrderSeq = c.OrderSeq ) INNER JOIN ';
            $sql .= '        T_Enterprise e ON ( o.EnterpriseId = e.EnterpriseId ) INNER JOIN ';
            $sql .= '        T_Site s ON ( o.SiteId = s.SiteId ) INNER JOIN ';
            $sql .= '        T_ClaimHistory ch ON ( o.OrderSeq = ch.OrderSeq AND ch.PrintedFlg = 0 AND ch.ValidFlg = 1 ) INNER JOIN ';
            $sql .= '        T_OemClaimAccountInfo ca ON( ch.Seq = ca.ClaimHistorySeq AND ca.Status = 1 ) LEFT OUTER JOIN ';
            $sql .= '        T_Oem oem ON( o.OemId = oem.OemId ) LEFT OUTER JOIN ';
            $sql .= '        M_Code cd105 ON( cd105.CodeId = 105 AND IFNULL(o.OemId, 0) = cd105.KeyCode) LEFT OUTER JOIN ';
            $sql .= '        M_Code cd108 ON( cd108.CodeId = 108 AND IFNULL(o.OemId, 0) = cd108.KeyCode) ';
            $sql .= ' WHERE  o.P_OrderSeq = :P_OrderSeq ';
            $sql .= '   AND  EXISTS (SELECT * FROM T_Order t WHERE t.P_OrderSeq = o.OrderSeq AND t.Cnl_Status = 0) ';
            $data = $this->app->dbAdapter->query( $sql )->execute( $prm )->current();

            if (!$data) {
                // 有効な注文データがない場合はスキップ
                $i++;
                continue;
            }

            // 請求金額が30万円以上だった場合
            if( $data['ClaimAmount'] >= 300000 ) {
                $data['Cv_BarcodeData'] = '収納代行の規約によりコンビニエンスストアで30万円以上のお支払はできません';
                $data['Cv_BarcodeString1'] = '';
                $data['Cv_BarcodeString2'] = '';
            }

            // 任意注文番号非表示の加盟店
            if ($data['PrintEntOrderIdOnClaimFlg'] == 0) {
                $data['Ent_OrderId'] = '';
            }

            // 初回はブランク
            $data['ClaimFee'] = '';
            $data['DamageInterestAmount'] = '';

            // 注文商品
            $sql  = ' SELECT itm.ItemNameKj ';
            $sql .= ' ,      itm.ItemNum ';
            $sql .= ' ,      itm.UnitPrice ';
            $sql .= ' ,      itm.SumMoney ';
            $sql .= ' ,      e.DispDecimalPoint ';  /* 表示用小数点桁数 */
            $sql .= ' FROM   T_Order o INNER JOIN ';
            $sql .= '        T_OrderItems itm ON ( o.OrderSeq = itm.OrderSeq ) ';
            $sql .= '        INNER JOIN T_Enterprise e ON ( e.EnterpriseId = o.EnterpriseId ) ';
            $sql .= ' WHERE  itm.DataClass = 1 AND ';
            $sql .= '        o.P_OrderSeq = :P_OrderSeq ';
            $sql .= ' AND    o.Cnl_Status = 0 ';
            $sql .= ' AND    itm.ValidFlg = 1 ';
            $sql .= ' ORDER BY OrderItemId ';
            $items = ResultInterfaceToArray( $this->app->dbAdapter->query( $sql )->execute( $prm ) );
            $itemsAmt = 0;

            for( $j = 1; $j <= 19; $j++ ) {
                $data['ItemNameKj_' . $j] = isset( $items[$j - 1]['ItemNameKj'] ) ? $items[$j - 1]['ItemNameKj']: '';
                $data['ItemNum_' . $j] = isset( $items[$j - 1]['ItemNum'] ) ? $items[$j - 1]['ItemNum'] : '';
                if ($data['ItemNum_' . $j] != '') {
                    // [表示用小数点桁数]考慮
                    $data['ItemNum_' . $j] = number_format($data['ItemNum_' . $j], $items[$j - 1]['DispDecimalPoint'], '.', '');
                }
                $data['UnitPrice_' . $j] = isset( $items[$j - 1]['UnitPrice'] ) ? $items[$j - 1]['UnitPrice'] : '';
                $data['SumMoney_' . $j] = isset( $items[$j - 1]['SumMoney'] ) ? $items[$j - 1]['SumMoney'] : '';
                $itemsAmt += $items[$j - 1]['SumMoney'];
            }

            // 小計
            $data['TotalItemPrice'] = 0;

            foreach ($items as $row) {
                $data['TotalItemPrice'] += $row['SumMoney'];
            }

            // 送料
            $sql  = ' SELECT SUM( itm.SumMoney ) AS CarriageFee ';
            $sql .= ' FROM   T_Order o INNER JOIN ';
            $sql .= '        T_OrderItems itm ON ( o.OrderSeq = itm.OrderSeq ) ';
            $sql .= ' WHERE  itm.DataClass = 2 AND ';
            $sql .= '        o.P_OrderSeq = :P_OrderSeq ';
            $sql .= ' AND    o.Cnl_Status = 0 ';
            $data = array_merge( $data, $this->app->dbAdapter->query( $sql )->execute( $prm )->current() );

            // 決済手数料
            $sql  = ' SELECT SUM( itm.SumMoney ) AS ChargeFee ';
            $sql .= ' FROM   T_Order o INNER JOIN ';
            $sql .= '        T_OrderItems itm ON ( o.OrderSeq = itm.OrderSeq ) ';
            $sql .= ' WHERE  itm.DataClass = 3 AND ';
            $sql .= '        o.P_OrderSeq = :P_OrderSeq ';
            $sql .= ' AND    o.Cnl_Status = 0 ';
            $data = array_merge( $data, $this->app->dbAdapter->query( $sql )->execute( $prm )->current() );

            // 消費税(外税額レコード確認)
            $sql  = ' SELECT COUNT(itm.OrderItemId) AS cnt ';
            $sql .= ' FROM   T_Order o INNER JOIN ';
            $sql .= '        T_OrderItems itm ON ( o.OrderSeq = itm.OrderSeq ) ';
            $sql .= ' WHERE  itm.DataClass = 4 AND ';
            $sql .= '        o.P_OrderSeq = :P_OrderSeq ';
            $sql .= ' AND    o.Cnl_Status = 0 ';
            $data['TaxClass'] = ((int)$this->app->dbAdapter->query( $sql )->execute( $prm )->current()['cnt'] > 0) ? 1 : 0;

            // 請求回数
            $data['ReIssueCount'] = 0;

            // マイページログインパスワード
            $row_mypageorder = $this->app->dbAdapter->query(" SELECT Token FROM T_MypageOrder WHERE OrderSeq = :OrderSeq ORDER BY Seq DESC LIMIT 1 "
            )->execute(array(':OrderSeq' => $params['P_OrderSeq' . $i]))->current();
            $data['MypageToken'] = ($row_mypageorder) ? $row_mypageorder['Token'] : '';

            $itemsCount = 0;
            if (!empty($items)) {
                $itemsCount = count($items);
            }
            // 商品合計数
            $data['ItemsCount'] = $itemsCount;

            // その他点数
            $data['ItemsCountEtc'] = $itemsCount - 19;

            // その他合計額
            $data['TotalItemPriceEtc'] = $data['TotalItemPrice'] - $itemsAmt;

            // 請求金額
            $data['BilledAmt'] = nvl( $data['ClaimAmount'], 0 ) - nvl( $data['ReceiptAmountTotal'], 0 );

            // 店舗からのお知らせ
            $data['PrintEntComment'] = preg_replace('/((\r\n)|[\r\n])/', "\n", f_e($data['PrintEntComment'])) ; // 改行コードを\nに統一
            $printEntComments = explode("\n", $data['PrintEntComment'], 11);
            for( $j = 1; $j <= 10; $j++ ) {
                $data[sprintf('PrintEntComment%02d', $j)] = isset($printEntComments[$j - 1]) ? $printEntComments[$j - 1] : '';
            }

            // CB、OEMで切り替えるデータ
            if ($data['OemId'] == 0) {
                // CB

                // 発行元
                $printPost = '';
                $printAddress = '';
                $printName = '';
                $printTel = '';
                $printMessage = '';
                $printLogo = '';

                // 払込取扱表番号
                $paymentNumber = '';

                // 受取人
                $accept = '株式会社キャッチボール';

                // 口座記号番号（プレ印字されているので不要）
                $data['Yu_AccountNumber'] = '';
            }
            else {
                //OEM

                // 発行元
                if (substr($data['OrderId'], 0, 2) != 'AB') {
                    // SMBC以外の場合印字
                    $printPost = '〒' .$data['OemPostalCode'];
                    $printAddress = $data['OemPrefectureName'] . $data['OemCity'] . $data['OemTown'] . $data['OemBuilding'];
                    $printName = $data['OemNameKj'];
                    $printMessage = 'お支払いに関するお問合せ';
                    $printTel = strlen($data['PrintContactPhoneNumber']) > 0 ? 'TEL：' . $data['PrintContactPhoneNumber'] : '';

                    if (substr($data['OrderId'], 0, 2) != 'EA') {
                        // Eストア以外の場合ロゴ印刷
                        $printLogo = sprintf('data:image/jpeg;base64,%s', $data['OemLogo']);
                    }
                    else {
                        // Eストアの場合ロゴ印刷なし
                        $printLogo = '';
                    }
                }
                else {
                    // SMBCの場合印字なし
                    $printPost = '';
                    $printAddress = '';
                    $printName = '';
                    $printTel = '';
                    $printMessage = '';
                    $printLogo = '';
                }

                // 払込取扱表番号
                if (substr($data['OrderId'], 0, 2) == 'EA') {
                    // Eストア
                    $paymentNumber = '4';
                }
                elseif (substr($data['OrderId'], 0, 2) == 'SC') {
                    // セイノー
                    $paymentNumber = '5';
                }
                elseif (substr($data['OrderId'], 0, 2) == 'AB') {
                    // SMBC
                    $paymentNumber = '5';
                }
                else {
                    // その他
                    $paymentNumber = '3';
                }

                // 受取人
                if (substr($data['OrderId'], 0, 2) != 'AB') {
                    // SMBC以外の場合
                    $accept = $data['OemNameKj'];
                }
                else {
                    // SMBCの場合
                    $accept = '株式会社キャッチボール';
                }
            }

            $data['PrintPost'] = $printPost;
            $data['PrintAddress'] = $printAddress;
            $data['PrintName'] = $printName;
            $data['PrintTel'] = $printTel;
            $data['PrintLogo'] = $printLogo;
            $data['PrintMessage'] = $printMessage;
            $data['PaymentNumber'] = $paymentNumber;
            $data['Accept'] = $accept;

            // QRコード
            $qrCodeImg = $qrCode->draw_by_size($data['MypageUrl'], 1);
            ob_start();
            imagegif($qrCodeImg);
            $qrCodeImgData = ob_get_clean();
            $qrCodeSrc = sprintf('data:image/gif;base64,%s', base64_encode($qrCodeImgData));
            $data['QrCode'] = $qrCodeSrc;

            // バーコード
            $data['Ean128'] = '';
            if( $data['ClaimAmount'] < 300000 ) {
                $ean128Img = $ean128->drawConvenience('{FNC1}' . $data['Cv_BarcodeData'], 1, 50);
                ob_start();
                imagegif($ean128Img);
                $ean128ImgData = ob_get_clean();
                $ean128Src = sprintf('data:image/gif;base64,%s', base64_encode($ean128ImgData));
                $data['Ean128'] = $ean128Src;
            }

            // 請求履歴データを取得
            $mdlch = new TableClaimHistory($this->app->dbAdapter);
            $row_ch = $mdlch->findClaimHistory(array( 'PrintedFlg' => 0, 'ValidFlg' => 1, 'OrderSeq' => $params['P_OrderSeq' . $i] ))->current();
            // 請求履歴．印刷ステータス(PrintedStatus)を"3"(PDF印刷済み)に更新する
            $this->app->dbAdapter->query(" UPDATE T_ClaimHistory SET PrintedStatus = 3 WHERE Seq = :Seq ")->execute(array(':Seq' => $row_ch['Seq']));

            $datas[] = $data;
            $i++;
        }

        if ($paperType == 1) {
            // 同梱A4
            $fileName = sprintf( 'Seikyu_%s.pdf', date( "YmdHis" ) );

            $this->view->assign('datas', $datas);
            $this->view->assign('documentRoot', $_SERVER['DOCUMENT_ROOT']);
            $this->view->assign('title', $fileName);
            $this->view->assign('PrintAdjustmentX', $enterprise['PrintAdjustmentX']);
            $this->view->assign('PrintAdjustmentY', $enterprise['PrintAdjustmentY']);

            $this->setTemplate('billeddokon');

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
        elseif ($paperType == 2) {
            // 同梱牛乳
            $fileName = sprintf( 'Gyunyu_%s.pdf', date( "YmdHis" ) );

            $this->view->assign('datas', $datas);
            $this->view->assign('documentRoot', $_SERVER['DOCUMENT_ROOT']);
            $this->view->assign('title', $fileName);
            $this->view->assign('PrintAdjustmentX', $enterprise['PrintAdjustmentX']);
            $this->view->assign('PrintAdjustmentY', $enterprise['PrintAdjustmentY']);

            $this->setTemplate('billedgyunyu');

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
        else {
            // その他 → 空で返す
            return $this->response;
        }
    }

    /**
     * PDFダウンロード
     */
    protected function pdfDownload2() {

        $params = $_SESSION[self::SESSION_JOB_PARAMS];

        // 余白設定
        $mdlEnt = new TableEnterprise($this->app->dbAdapter);
        $enterprise = $mdlEnt->find($this->app->authManager->getUserInfo()->EnterpriseId)->current();

        // 用紙
        $paperType = isset($params['paperTypeVal']) ? $params['paperTypeVal'] : 1;

        // バーコード、QRコード作成用
        $barcode = Application::getInstance()->config['barcode'];
        set_include_path(get_include_path() . PATH_SEPARATOR . $barcode['barcode_lib']);
        require_once 'QR.php';
        require_once 'EAN128.php';
        $qrCode = new \SharedQR();
        $qrCode->version = 5;           // バージョン 1～40を指定　デフォルト5
        $qrCode->error_level = 'M';     // エラーレベル　L,M,Q,Hを指定　デフォルトM
        $ean128 = new \EAN128();
        $ean128->TextWrite = false;

        $datas = array();
        $i = 0;
        while( isset( $params['P_OrderSeq' . $i] ) ) {
            if( !isset( $params['chkPrintCsv' . $i ] ) ) {
                $i++;
                continue;
            }
            $data = array();
            $prm = array( ':P_OrderSeq' => $params['P_OrderSeq' . $i] );

            $sql  = ' SELECT c.PostalCode ';
            $sql .= ' ,      c.UnitingAddress ';
            $sql .= ' ,      c.NameKj ';
            $sql .= ' ,      o.OrderId ';
            $sql .= ' ,      o.Ent_OrderId ';
            $sql .= ' ,      o.ReceiptOrderDate ';
            $sql .= ' ,      s.SiteNameKj ';
            $sql .= ' ,      e.ContactPhoneNumber ';
            $sql .= ' ,      e.PrintEntComment ';
            $sql .= ' ,      e.PrintEntOrderIdOnClaimFlg ';
            $sql .= ' ,      ch.ClaimAmount ';
            $sql .= ' ,      ch.LimitDate ';
            $sql .= ' ,      ch.ClaimFee ';
            $sql .= ' ,      ch.AdditionalClaimFee ';
            $sql .= ' ,      ch.DamageInterestAmount ';
            $sql .= ' ,      ca.Cv_BarcodeData ';
            $sql .= ' ,      ca.Cv_BarcodeString1 ';
            $sql .= ' ,      ca.Cv_BarcodeString2 ';
            $sql .= ' ,      ca.TaxAmount ';
            $sql .= ' ,      ca.Bk_BankName ';
            $sql .= ' ,      ca.Bk_BranchName ';
            $sql .= ' ,      ca.Bk_DepositClass ';
            $sql .= ' ,      ca.Bk_AccountNumber ';
            $sql .= ' ,      ca.Bk_AccountHolderKn ';
            $sql .= ' ,      ca.Yu_SubscriberName ';
            $sql .= ' ,      ca.Yu_AccountNumber ';
            $sql .= ' ,      ca.Yu_MtOcrCode1 ';
            $sql .= ' ,      ca.Yu_MtOcrCode2 ';
            $sql .= ' ,      ca.SubUseAmount_1 ';
            $sql .= ' ,      ca.SubTaxAmount_1 ';
            $sql .= ' ,      ca.SubUseAmount_2 ';
            $sql .= ' ,      ca.SubTaxAmount_2 ';
            $sql .= ' ,      IFNULL(o.OemId, 0) AS OemId ';
            $sql .= ' ,      oem.PostalCode AS OemPostalCode ';
            $sql .= ' ,      oem.PrefectureName AS OemPrefectureName ';
            $sql .= ' ,      oem.City AS OemCity ';
            $sql .= ' ,      oem.Town AS OemTown ';
            $sql .= ' ,      oem.Building AS OemBuilding ';
            $sql .= ' ,      oem.OemNameKj ';
            $sql .= ' ,      oem.ContactPhoneNumber AS OemContactPhoneNumber ';
            $sql .= ' ,      oem.LargeLogo AS OemLogo ';
            $sql .= ' ,      cd105.KeyContent AS MypageUrl ';
            $sql .= ' ,      cd108.KeyContent AS PrintContactPhoneNumber ';
            $sql .= ' FROM   T_Order o INNER JOIN ';
            $sql .= '        T_Customer c ON ( o.OrderSeq = c.OrderSeq ) INNER JOIN ';
            $sql .= '        T_Enterprise e ON ( o.EnterpriseId = e.EnterpriseId ) INNER JOIN ';
            $sql .= '        T_Site s ON ( o.SiteId = s.SiteId ) INNER JOIN ';
            $sql .= '        T_ClaimHistory ch ON ( o.OrderSeq = ch.OrderSeq AND ch.PrintedFlg = 0 AND ch.ValidFlg = 1 ) INNER JOIN ';
            $sql .= '        T_OemClaimAccountInfo ca ON( ch.Seq = ca.ClaimHistorySeq AND ca.Status = 1 ) LEFT OUTER JOIN ';
            $sql .= '        T_Oem oem ON( o.OemId = oem.OemId ) LEFT OUTER JOIN ';
            $sql .= '        M_Code cd105 ON( cd105.CodeId = 105 AND IFNULL(o.OemId, 0) = cd105.KeyCode) LEFT OUTER JOIN ';
            $sql .= '        M_Code cd108 ON( cd108.CodeId = 108 AND IFNULL(o.OemId, 0) = cd108.KeyCode) ';
            $sql .= ' WHERE  o.P_OrderSeq = :P_OrderSeq ';
            $sql .= '   AND  EXISTS (SELECT * FROM T_Order t WHERE t.P_OrderSeq = o.OrderSeq AND t.Cnl_Status = 0) ';
            $data = $this->app->dbAdapter->query( $sql )->execute( $prm )->current();

            if (!$data) {
                // 有効な注文データがない場合はスキップ
                $i++;
                continue;
            }

            // 請求金額が30万円以上だった場合
            if( $data['ClaimAmount'] >= 300000 ) {
                $data['Cv_BarcodeData'] = '収納代行の規約によりコンビニエンスストアで30万円以上のお支払はできません';
                $data['Cv_BarcodeString1'] = '';
                $data['Cv_BarcodeString2'] = '';
            }

            // 任意注文番号非表示の加盟店
            if ($data['PrintEntOrderIdOnClaimFlg'] == 0) {
                $data['Ent_OrderId'] = '';
            }

            // 初回はブランク
            $data['ClaimFee'] = '';
            $data['DamageInterestAmount'] = '';

            // 注文商品
            $sql  = ' SELECT itm.ItemNameKj ';
            $sql .= ' ,      itm.ItemNum ';
            $sql .= ' ,      itm.UnitPrice ';
            $sql .= ' ,      itm.SumMoney ';
            $sql .= ' ,      itm.TaxRate ';
            $sql .= ' ,      e.DispDecimalPoint ';  /* 表示用小数点桁数 */
            $sql .= ' FROM   T_Order o INNER JOIN ';
            $sql .= '        T_OrderItems itm ON ( o.OrderSeq = itm.OrderSeq ) ';
            $sql .= '        INNER JOIN T_Enterprise e ON ( e.EnterpriseId = o.EnterpriseId ) ';
            $sql .= ' WHERE  itm.DataClass = 1 AND ';
            $sql .= '        o.P_OrderSeq = :P_OrderSeq ';
            $sql .= ' AND    o.Cnl_Status = 0 ';
            $sql .= ' AND    itm.ValidFlg = 1 ';
            $sql .= ' ORDER BY OrderItemId ';
            $items = ResultInterfaceToArray( $this->app->dbAdapter->query( $sql )->execute( $prm ) );
            $itemsAmt = 0;

             $symbolAddFlg = false;

         for( $j = 1; $j <= 19; $j++ ) {
                if($items[$j - 1]['TaxRate'] == '8' && $items[$j - 1]['UnitPrice'] > 0){
                    $data['ItemNameKj_' . $j] = isset( $items[$j - 1]['ItemNameKj'] )
                    ? $items[$j - 1]['ItemNameKj'] : '';
                    $symbolAddFlg = true;
                }else{
                    $data['ItemNameKj_' . $j] = isset( $items[$j - 1]['ItemNameKj'] ) ? $items[$j - 1]['ItemNameKj'] : '';
                }
                $data['ItemNum_' . $j] = isset( $items[$j - 1]['ItemNum'] ) ? $items[$j - 1]['ItemNum'] : '';
                if ($data['ItemNum_' . $j] != '') {
                    // [表示用小数点桁数]考慮
                    $data['ItemNum_' . $j] = number_format($data['ItemNum_' . $j], $items[$j - 1]['DispDecimalPoint'], '.', '');
                }
                $data['UnitPrice_' . $j] = isset( $items[$j - 1]['UnitPrice'] ) ? $items[$j - 1]['UnitPrice'] : '';
                $data['SumMoney_' . $j] = isset( $items[$j - 1]['SumMoney'] ) ? $items[$j - 1]['SumMoney'] : '';
                $data['TaxRate' . $j] = isset( $items[$j - 1]['TaxRate'] ) ? $items[$j - 1]['TaxRate'] : '';
                $itemsAmt += $items[$j - 1]['SumMoney'];
            }

            //※の説明文作成
            if($symbolAddFlg == true){
                $data['TaxRate8Str'] = "※印は軽減税率（8%）適用商品";
            }

            // 小計
            $data['TotalItemPrice'] = 0;
            foreach ($items as $row) {
                $data['TotalItemPrice'] += $row['SumMoney'];
            }

            // 送料
            $sql  = ' SELECT SUM( itm.SumMoney ) AS CarriageFee ';
            $sql .= ' FROM   T_Order o INNER JOIN ';
            $sql .= '        T_OrderItems itm ON ( o.OrderSeq = itm.OrderSeq ) ';
            $sql .= ' WHERE  itm.DataClass = 2 AND ';
            $sql .= '        o.P_OrderSeq = :P_OrderSeq ';
            $sql .= ' AND    o.Cnl_Status = 0 ';
            $data = array_merge( $data, $this->app->dbAdapter->query( $sql )->execute( $prm )->current() );

            // 決済手数料
            $sql  = ' SELECT SUM( itm.SumMoney ) AS ChargeFee ';
            $sql .= ' FROM   T_Order o INNER JOIN ';
            $sql .= '        T_OrderItems itm ON ( o.OrderSeq = itm.OrderSeq ) ';
            $sql .= ' WHERE  itm.DataClass = 3 AND ';
            $sql .= '        o.P_OrderSeq = :P_OrderSeq ';
            $sql .= ' AND    o.Cnl_Status = 0 ';
            $data = array_merge( $data, $this->app->dbAdapter->query( $sql )->execute( $prm )->current() );

            // 消費税(外税額レコード確認)
            $sql  = ' SELECT COUNT(itm.OrderItemId) AS cnt ';
            $sql .= ' FROM   T_Order o INNER JOIN ';
            $sql .= '        T_OrderItems itm ON ( o.OrderSeq = itm.OrderSeq ) ';
            $sql .= ' WHERE  itm.DataClass = 4 AND ';
            $sql .= '        o.P_OrderSeq = :P_OrderSeq ';
            $sql .= ' AND    o.Cnl_Status = 0 ';
            $data['TaxClass'] = ((int)$this->app->dbAdapter->query( $sql )->execute( $prm )->current()['cnt'] > 0) ? 1 : 0;

            // 請求回数
            $data['ReIssueCount'] = 0;

            // 請求回数
            $data['ReIssueCount'] = 0;

            // マイページログインパスワード
            $row_mypageorder = $this->app->dbAdapter->query(" SELECT Token FROM T_MypageOrder WHERE OrderSeq = :OrderSeq ORDER BY Seq DESC LIMIT 1 "
            )->execute(array(':OrderSeq' => $params['P_OrderSeq' . $i]))->current();
            $data['MypageToken'] = ($row_mypageorder) ? $row_mypageorder['Token'] : '';

            $itemsCount = 0;
            if (!empty($items)) {
                $itemsCount = count($items);
            }
            // 商品合計数
            $data['ItemsCount'] = $itemsCount;

            // その他点数
            $data['ItemsCountEtc'] = $itemsCount - 19;

            // その他合計額
            $data['TotalItemPriceEtc'] = $data['TotalItemPrice'] - $itemsAmt;

            // 請求金額
            $data['BilledAmt'] = nvl( $data['ClaimAmount'], 0 ) - nvl( $data['ReceiptAmountTotal'], 0 );

            // 店舗からのお知らせ
            $data['PrintEntComment'] = preg_replace('/((\r\n)|[\r\n])/', "\n", f_e($data['PrintEntComment']));  // 改行コードを\nに統一
            $printEntComments = explode("\n", $data['PrintEntComment'], 11);
            for( $j = 1; $j <= 10; $j++ ) {
                $data[sprintf('PrintEntComment%02d', $j)] = isset($printEntComments[$j - 1]) ? $printEntComments[$j - 1] : '';
            }

            // CB、OEMで切り替えるデータ
            if ($data['OemId'] == 0) {
                // CB

                // 発行元
                $printPost = '';
                $printAddress = '';
                $printName = '';
                $printTel = '';
                $printMessage = '';
                $printLogo = '';

                // 払込取扱表番号
                $paymentNumber = '';

                // 受取人
                $accept = '株式会社キャッチボール';

                // 口座記号番号（プレ印字されているので不要）
                $data['Yu_AccountNumber'] = '';
            }
            else {
                //OEM

                // 発行元
                if (substr($data['OrderId'], 0, 2) != 'AB') {
                    // SMBC以外の場合印字
                    $printPost = '〒' .$data['OemPostalCode'];
                    $printAddress = $data['OemPrefectureName'] . $data['OemCity'] . $data['OemTown'] . $data['OemBuilding'];
                    $printName = $data['OemNameKj'];
                    $printMessage = 'お支払いに関するお問合せ';
                    $printTel = strlen($data['PrintContactPhoneNumber']) > 0 ? 'TEL：' . $data['PrintContactPhoneNumber'] : '';

                    if (substr($data['OrderId'], 0, 2) != 'EA') {
                        // Eストア以外の場合ロゴ印刷
                        $printLogo = sprintf('data:image/jpeg;base64,%s', $data['OemLogo']);
                    }
                    else {
                        // Eストアの場合ロゴ印刷なし
                        $printLogo = '';
                    }
                }
                else {
                    // SMBCの場合印字なし
                    $printPost = '';
                    $printAddress = '';
                    $printName = '';
                    $printTel = '';
                    $printMessage = '';
                    $printLogo = '';
                }

                // 払込取扱表番号
                if (substr($data['OrderId'], 0, 2) == 'EA') {
                    // Eストア
                    $paymentNumber = '4';
                }
                elseif (substr($data['OrderId'], 0, 2) == 'SC') {
                    // セイノー
                    $paymentNumber = '5';
                }
                elseif (substr($data['OrderId'], 0, 2) == 'AB') {
                    // SMBC
                    $paymentNumber = '5';
                }
                else {
                    // その他
                    $paymentNumber = '3';
                }

                // 受取人
                if (substr($data['OrderId'], 0, 2) != 'AB') {
                    // SMBC以外の場合
                    $accept = $data['OemNameKj'];
                }
                else {
                    // SMBCの場合
                    $accept = '株式会社キャッチボール';
                }
            }

            $data['PrintPost'] = $printPost;
            $data['PrintAddress'] = $printAddress;
            $data['PrintName'] = $printName;
            $data['PrintTel'] = $printTel;
            $data['PrintLogo'] = $printLogo;
            $data['PrintMessage'] = $printMessage;
            $data['PaymentNumber'] = $paymentNumber;
            $data['Accept'] = $accept;

            // QRコード
            $qrCodeImg = $qrCode->draw_by_size($data['MypageUrl'], 1);
            ob_start();
            imagegif($qrCodeImg);
            $qrCodeImgData = ob_get_clean();
            $qrCodeSrc = sprintf('data:image/gif;base64,%s', base64_encode($qrCodeImgData));
            $data['QrCode'] = $qrCodeSrc;

            // バーコード
            $data['Ean128'] = '';
            if( $data['ClaimAmount'] < 300000 ) {
                $ean128Img = $ean128->drawConvenience('{FNC1}' . $data['Cv_BarcodeData'], 1, 50);
                ob_start();
                imagegif($ean128Img);
                $ean128ImgData = ob_get_clean();
                $ean128Src = sprintf('data:image/gif;base64,%s', base64_encode($ean128ImgData));
                $data['Ean128'] = $ean128Src;
            }

            // 請求履歴データを取得
            $mdlch = new TableClaimHistory($this->app->dbAdapter);
            $row_ch = $mdlch->findClaimHistory(array( 'PrintedFlg' => 0, 'ValidFlg' => 1, 'OrderSeq' => $params['P_OrderSeq' . $i] ))->current();
            // 請求履歴．印刷ステータス(PrintedStatus)を"3"(PDF印刷済み)に更新する
            $this->app->dbAdapter->query(" UPDATE T_ClaimHistory SET PrintedStatus = 3 WHERE Seq = :Seq ")->execute(array(':Seq' => $row_ch['Seq']));

            $datas[] = $data;
            $i++;
        }

        if ($paperType == 1) {
            // 同梱A4
            $fileName = sprintf( 'Seikyu_%s.pdf', date( "YmdHis" ) );

            $this->view->assign('datas', $datas);
            $this->view->assign('documentRoot', $_SERVER['DOCUMENT_ROOT']);
            $this->view->assign('title', $fileName);
            $this->view->assign('PrintAdjustmentX', $enterprise['PrintAdjustmentX']);
            $this->view->assign('PrintAdjustmentY', $enterprise['PrintAdjustmentY']);

            $this->setTemplate('billeddokon');

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
        elseif ($paperType == 2) {
            // 同梱牛乳
            $fileName = sprintf( 'Gyunyu_%s.pdf', date( "YmdHis" ) );

            $this->view->assign('datas', $datas);
            $this->view->assign('documentRoot', $_SERVER['DOCUMENT_ROOT']);
            $this->view->assign('title', $fileName);
            $this->view->assign('PrintAdjustmentX', $enterprise['PrintAdjustmentX']);
            $this->view->assign('PrintAdjustmentY', $enterprise['PrintAdjustmentY']);

            $this->setTemplate('billedgyunyu');

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
        else {
            // その他 → 空で返す
            return $this->response;
        }
    }

    /**
     * テスト印刷
     */
    public function testprintAction() {

        $params = $this->getParams();

        // 余白設定
        $printAdjustmentX = (int)nvl($params['PrintAdjustmentX'], 0);
        $printAdjustmentY = (int)nvl($params['PrintAdjustmentY'], 0);

        // バーコード、QRコード作成用
        $barcode = Application::getInstance()->config['barcode'];
        set_include_path(get_include_path() . PATH_SEPARATOR . $barcode['barcode_lib']);
        require_once 'QR.php';
        require_once 'EAN128.php';
        $qrCode = new \SharedQR();
        $qrCode->version = 5;           // バージョン 1～40を指定　デフォルト5
        $qrCode->error_level = 'M';     // エラーレベル　L,M,Q,Hを指定　デフォルトM
        $ean128 = new \EAN128();
        $ean128->TextWrite = false;

        $datas = array();
        $data = array();

        $prm = array( ':EnterpriseId' => $this->app->authManager->getUserInfo()->EnterpriseId );
        $sql  = ' SELECT s.SiteNameKj ';
        $sql .= ' ,      e.ContactPhoneNumber ';
        $sql .= ' ,      e.PrintEntComment ';
        $sql .= ' ,      e.PrintEntOrderIdOnClaimFlg ';
        $sql .= ' ,      e.DispDecimalPoint ';
        $sql .= ' ,      e.TaxClass ';
        $sql .= ' ,      IFNULL(e.OemId, 0) AS OemId ';
        $sql .= ' ,      oem.PostalCode AS OemPostalCode ';
        $sql .= ' ,      oem.PrefectureName AS OemPrefectureName ';
        $sql .= ' ,      oem.City AS OemCity ';
        $sql .= ' ,      oem.Town AS OemTown ';
        $sql .= ' ,      oem.Building AS OemBuilding ';
        $sql .= ' ,      oem.OemNameKj ';
        $sql .= ' ,      oem.ContactPhoneNumber AS OemContactPhoneNumber ';
        $sql .= ' ,      oem.LargeLogo AS OemLogo ';
        $sql .= ' ,      oem.OrderIdPrefix ';
        $sql .= ' ,      cd105.KeyContent AS MypageUrl ';
        $sql .= ' ,      cd108.KeyContent AS PrintContactPhoneNumber ';
        $sql .= ' FROM   T_Enterprise e INNER JOIN ';
        $sql .= '        T_Site s ON ( e.EnterpriseId = s.EnterpriseId ) LEFT OUTER JOIN ';
        $sql .= '        T_Oem oem ON( e.OemId = oem.OemId ) LEFT OUTER JOIN ';
        $sql .= '        M_Code cd105 ON( cd105.CodeId = 105 AND IFNULL(e.OemId, 0) = cd105.KeyCode) LEFT OUTER JOIN ';
        $sql .= '        M_Code cd108 ON( cd108.CodeId = 108 AND IFNULL(e.OemId, 0) = cd108.KeyCode) ';
        $sql .= ' WHERE  e.EnterpriseId = :EnterpriseId ';
        $sql .= ' ORDER BY s.SiteId ';
        $data = $this->app->dbAdapter->query( $sql )->execute( $prm )->current();

        $data['PostalCode'] = '999-9999';
        $data['UnitingAddress'] = '住所－－－－－－－＋－－－－－－－－－＋－－－－－－－－－＋－－－－－－－－－＋－－－－－－－－－＋－－－－－－－－－＋－－－－－－－－－＋－－－－－－－－－＋－－－－－－－－－＋';
        $data['NameKj'] = '顧客氏名－－－－－＋－－－－－－－－－＋－－－－－－－－－＋－－－－－－－－－＋－－－－－－－－－＋－－－－－Ｘ';
        $data['OrderId'] = 'XX12345678';
        $data['Ent_OrderId'] = '123456789';
        $data['ReceiptOrderDate'] = date('Y-m-d');
        $data['ClaimAmount'] = '99999999';
        $data['LimitDate'] = date('Y-m-d');
        $data['ClaimFee'] = '';
        $data['AdditionalClaimFee'] = '';
        $data['DamageInterestAmount'] = '';
        $data['Cv_BarcodeData'] = '12345678901234567890123456789012345678901234';
        $data['Cv_BarcodeString1'] = '(12) 345678-9012345678901234567890';
        $data['Cv_BarcodeString2'] = '123456-7-890123-4';
        $data['TaxAmount'] = '9999999';
        $data['Bk_BankName'] = 'サンプル銀行';
        $data['Bk_BranchName'] = 'サンプル支店';
        $data['Bk_DepositClass'] = '0';
        $data['Bk_AccountNumber'] = '1234567';
        $data['Bk_AccountHolderKn'] = 'サンプルカナ';
        $data['Yu_SubscriberName'] = '加入者名－－－－－＋';
        $data['Yu_AccountNumber'] = '123456789012';
        $data['Yu_MtOcrCode1'] = '999999999999999999999999999999999999999   X';
        $data['Yu_MtOcrCode2'] = '99999999999999999999999999999999999999999999';

        // 任意注文番号非表示の加盟店
        if ($data['PrintEntOrderIdOnClaimFlg'] == 0) {
            $data['Ent_OrderId'] = '';
        }

        // 注文商品
        $itemsAmt = 0;
        for( $j = 1; $j <= 19; $j++ ) {
            $itemname = sprintf('商品%02d－－－－－＋', $j);
            $data['ItemNameKj_' . $j] = BaseGeneralUtils::convertNumberNarrowToWide($itemname);
            $data['ItemNum_' . $j] = 9999;
            // [表示用小数点桁数]考慮
//            $data['ItemNum_' . $j] = number_format($data['ItemNum_' . $j], $data['DispDecimalPoint'], '.', '');
            $data['UnitPrice_' . $j] = 9999999;
            $data['SumMoney_' . $j] = 9999999;
        }

        // 小計
        $data['TotalItemPrice'] = 9999999;

        // 送料
        $data['CarriageFee'] = 9999999;

        // 決済手数料
        $data['ChargeFee'] = 9999999;

        // 請求回数
        $data['ReIssueCount'] = 0;

        // マイページログインパスワード
        $data['MypageToken'] = 'password';

        // 商品合計数
        $data['ItemsCount'] = 99;

        // その他点数
        $data['ItemsCountEtc'] = 99;

        // その他合計額
        $data['TotalItemPriceEtc'] = 9999999;

        // 請求金額
        $data['BilledAmt'] = $data['ClaimAmount'];

        // 店舗からのお知らせ
        $data['PrintEntComment'] = preg_replace('/((\r\n)|[\r\n])/', "\n", f_e($data['PrintEntComment']));  // 改行コードを\nに統一
        $printEntComments = explode("\n", $data['PrintEntComment'], 11);
        for( $j = 1; $j <= 10; $j++ ) {
            $data[sprintf('PrintEntComment%02d', $j)] = isset($printEntComments[$j - 1]) ? $printEntComments[$j - 1] : '';
        }

        // CB、OEMで切り替えるデータ
        if ($data['OemId'] == 0) {
            // CB

            // 発行元
            $printPost = '';
            $printAddress = '';
            $printName = '';
            $printTel = '';
            $printMessage = '';
            $printLogo = '';

            // 払込取扱表番号
            $paymentNumber = '';

            // 受取人
            $accept = '株式会社キャッチボール';

            // 口座記号番号（プレ印字されているので不要）
            $data['Yu_AccountNumber'] = '';
        }
        else {
            //OEM

            // 発行元
            if ($data['OrderIdPrefix'] != 'AB') {
                // SMBC以外の場合印字
                $printPost = '〒' . $data['OemPostalCode'];
                $printAddress = $data['OemPrefectureName'] . $data['OemCity'] . $data['OemTown'] . $data['OemBuilding'];
                $printName = $data['OemNameKj'];
                $printMessage = 'お支払いに関するお問合せ';
                $printTel = strlen($data['PrintContactPhoneNumber']) > 0 ? 'TEL：' . $data['PrintContactPhoneNumber'] : '';

                if ($data['OrderIdPrefix'] != 'EA') {
                    // Eストア以外の場合ロゴ印刷
                    $printLogo = sprintf('data:image/jpeg;base64,%s', $data['OemLogo']);
                }
                else {
                    // Eストアの場合ロゴ印刷なし
                    $printLogo = '';
                }
            }
            else {
                // SMBCの場合印字なし
                $printPost = '';
                $printAddress = '';
                $printName = '';
                $printTel = '';
                $printMessage = '';
                $printLogo = '';
            }

            // 払込取扱表番号
            if ($data['OrderIdPrefix'] == 'EA') {
                // Eストア
                $paymentNumber = '4';
            }
            elseif ($data['OrderIdPrefix'] == 'SC') {
                // セイノー
                $paymentNumber = '5';
            }
            elseif ($data['OrderIdPrefix'] == 'AB') {
                // SMBC
                $paymentNumber = '5';
            }
            else {
                // その他
                $paymentNumber = '3';
            }

            // 受取人
            if ($data['OrderIdPrefix'] != 'AB') {
                // SMBC以外の場合
                $accept = $data['OemNameKj'];
            }
            else {
                // SMBCの場合
                $accept = '株式会社キャッチボール';
            }
        }

        $data['PrintPost'] = $printPost;
        $data['PrintAddress'] = $printAddress;
        $data['PrintName'] = $printName;
        $data['PrintTel'] = $printTel;
        $data['PrintLogo'] = $printLogo;
        $data['PrintMessage'] = $printMessage;
        $data['PaymentNumber'] = $paymentNumber;
        $data['Accept'] = $accept;

        // QRコード
        $qrCodeImg = $qrCode->draw_by_size($data['MypageUrl'], 1);
        ob_start();
        imagegif($qrCodeImg);
        $qrCodeImgData = ob_get_clean();
        $qrCodeSrc = sprintf('data:image/gif;base64,%s', base64_encode($qrCodeImgData));
        $data['QrCode'] = $qrCodeSrc;

        // バーコード
        $ean128Img = $ean128->drawConvenience('{FNC1}' . $data['Cv_BarcodeData'], 1, 50);
        ob_start();
        imagegif($ean128Img);
        $ean128ImgData = ob_get_clean();
        $ean128Src = sprintf('data:image/gif;base64,%s', base64_encode($ean128ImgData));
        $data['Ean128'] = $ean128Src;

        $datas[] = $data;

        // 同梱A4
        $fileName = sprintf( 'Test_Seikyu_%s.pdf', date( "YmdHis" ) );

        $this->view->assign('datas', $datas);
        $this->view->assign('documentRoot', $_SERVER['DOCUMENT_ROOT']);
        $this->view->assign('title', $fileName);
        $this->view->assign('PrintAdjustmentX', $printAdjustmentX);
        $this->view->assign('PrintAdjustmentY', $printAdjustmentY);

        $this->setTemplate('billeddokon');

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
}