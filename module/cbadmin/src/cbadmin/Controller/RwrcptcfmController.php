<?php
namespace cbadmin\Controller;

use Zend\Config\Reader\Ini;
use Zend\Db\ResultSet\ResultSet;
use cbadmin\Application;
use Coral\Base\BaseGeneralUtils;
use Coral\Base\BaseHtmlUtils;
use Coral\Coral\Controller\CoralControllerAction;
use Coral\Coral\CoralCodeMaster;
use Coral\Coral\CoralValidate;
use Coral\Coral\External\CoralExternalAtPayment;
use Coral\Coral\External\CoralExternalAtPaymentEx;
use Coral\Coral\External\CoralExternalAtPaymentDataEx;
use Coral\Coral\History\CoralHistoryOrder;
use Coral\Coral\Mail\CoralMail;
use models\Table\TableClaimControl;
use models\Table\TableClaimHistory;
use models\Table\TableDeliMethod;
use models\Table\TableOrder;
use models\Table\TableOrderItems;
use models\Table\TablePayingAndSales;
use models\Table\TableReceiptControl;
use models\Table\TableStampFee;
use models\View\ViewArrivalConfirm;
use models\View\ViewDelivery;
use models\View\ViewOrderCustomer;
use models\Table\TableSundryControl;
use models\Table\TableStagnationAlert;
use models\Table\TableUser;
use models\Table\ATableReceiptControl;
use models\Table\TableImportedReceiptFile;
use models\Table\TableEnterpriseCustomer;
use Zend\Json\Json;
use models\Table\ATableOrder;
use models\Table\TableSystemProperty;
use models\Table\TableToBackMypageIF;
use models\Table\ATablePayingAndSales;

class RwrcptcfmController extends CoralControllerAction
{
    protected $_componentRoot = './application/views/components';

    /**
     * アプリケーションオブジェクト
     * @var Application
     */
    private $app;

    /**
     * Controllerを初期化する
     */
    public function _init()
    {
        $this->app = Application::getInstance();
        $this->view->assign('userInfo', $this->app->authManagerAdmin->getUserInfo());

        $this->addStyleSheet('../css/default02.css')
        ->addStyleSheet('../css/cbadmin/rwrcptcfm/list/default.css')
        ->addJavaScript('../js/prototype.js')
        ->addJavaScript('../js/bytefx.js');
        $this->addJavaScript("../js/corelib.js");
        $this->addJavaScript("../js/base.ui.js");
        $this->addJavaScript("../js/base.ui.datepicker2.js");
        $this->addStyleSheet("../css/base.ui.datepicker.css");

        $this->setPageTitle("後払い.com - 入金確認");

        // このコントローラでは実行限界時間を未設定にする
        ini_set( 'max_execution_time', 0 );
    }

    /**
     * 入金確認待ちのリストを表示する。
     */
    public function listAction()
    {
$start = microtime(true);
        // [paging] Coral_Pagerのロードと必要なCSS/JSのアサイン
//        Application::getInstance()->addClass('Coral_Pager');
        $this
            ->addStyleSheet('../css/base.ui.customlist.css')
            ->addJavaScript('../js/corelib.js')
            ->addJavaScript('../js/base.ui.js')
            ->addJavaScript('../js/base.ui.customlist.js');

        // [paging] 1ページあたりの項目数
        // ※：config.iniからの取得を追加（08.04.03）
        // ※：ページング適用しないので意味ないけど。。。
//        $cn = $this->getRequest()->getControllerName();
//        $ipp = isset( $this->app->paging_conf ) ? $this->app->paging_conf->$cn : 20;
//        if( ! NetB_Reflection_Utility::isPositiveInteger($ipp) ) $ipp = 20;

        // [paging] 指定ページを取得
//        $current_page = (int)($this->getRequest()->getParam('page', 1));
        $current_page = (int)($this->params()->fromRoute('page','1'));
        if( $current_page < 1 ) $current_page = 1;

        $confirmationCnt = 0;       // 入金確認待ち件数
        $confirmationAmount = 0;    // 入金確認待ち金額
        $expireCnt = 0;             // 初回請求期限切れ件数
        $expireAmount = 0;          // 初回請求期限切れ金額
        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
        $mdloc = new ViewOrderCustomer($this->app->dbAdapter);

//echo sprintf('initializing: %s<br/>', microtime(true) - $start);
        //　パラメータによってリストの取得を変更する
//        $delay = $this->getRequest()->getParam('delay');
        $delay = $this->params()->fromRoute('delay');
        if(isset($delay)) {
            // 300日前の計算
            $targetDate = date('Y-m-d', strtotime('- 300 day'));
        } else {
            $targetDate = null;
        }

        $param = $this->getParams();
        //顧客詳細（一覧、加盟店、管理顧客）から遷移されてきた際の引数取得
        $entcustseq = $param['ecs'];
        $mancustid = $param['mcid'];

        // 入金確認待ちのデータを取得する。
        $datas = $mdloc->findByDs51($targetDate, $delay,$entcustseq, $param['oseq'], $mancustid);
        $rs = new ResultSet();
        $datasAry = $rs->initialize($datas)->toArray();

//echo sprintf('data fetch: %s<br/>', microtime(true) - $start);
//        $baseDate = new Zend_Date();
        $pat_cache = array();
        foreach($codeMaster->getClaimPatternMaster() as $pat => $cap) {
            // 請求パターンキャッシュは先行構築するよう変更（10.5.25 eda）
            $pat_cache[$pat] = $codeMaster->getClaimPatternCaption($pat, true);
        }
//echo sprintf('master preparing: %s<br/>', microtime(true) - $start);
        //for ($i = 0, $l = count($datas); $i < $l; $i++)
        foreach($datasAry as $i => &$data)
        {
            $data = $datasAry[$i];

            // 日付関連 → 延滞計算は日付文字列ベース、日付フォーマットはビュースクリプトに移管のため廃止（10.5.25 eda）

            // 請求額
            $data['ClaimTotalAmount'] = $data['ClaimAmount'];

            // 請求パターン
            $data['ClaimPattern'] = $pat_cache[$data['ClaimPattern']];

            // 入金形態
            // ↓ ページングのためのスライシングに伴うname属性のサフィックスずれを解消するため別ループで処理（08.03.25）
            /*
            $datas[$i]['receiptFormTag'] = NetB_HtmlUtils::InputRadioTag(
                'Rct_ReceiptMethod' . $i,
                $codeMaster->getReceiptMethodMaster(),
                0);
             */

            // 入金待ち件数金額
            $confirmationCnt++;
            $confirmationAmount += $data['ClaimTotalAmount'];

            // 初回請求期限切れ件数金額 → 延滞日付計算メソッドを文字列ベース版に変更（10.5.25 eda）
            // $baseDate = new Zend_Date(); // → ループ外に出してある
            $pdays = BaseGeneralUtils::CalcSpanDaysFromString($data['F_LimitDate'], date('Y-m-d H:i:s'));

            if ($pdays > 0)
            {
                $expireCnt++;
                $expireAmount += $data['ClaimTotalAmount'];
                $data['DamageRecord'] = 'class="damage"';
            }

            $datasAry[$i] = $data;
        }

//echo sprintf('main loop: %s<br/>', microtime(true) - $start);
        // [paging] ページャ初期化
//        $pager = new Coral_Pager( count($datas), $ipp );
        // [paging] 指定ページを補正
//        if( $current_page > $pager->getTotalPage() ) $current_page = $pager->getTotalPage();
        // [paging] 対象リストをページング情報に基づいて対象リストをスライス
//        if( count($datas) > 0 ) $datas = array_slice( $datas, $pager->getStartIndex( $current_page ), $ipp );
        // [paging] 入金形態要素を作成
        $recepitMasterList = $codeMaster->getReceiptMethodMaster();

        // count関数対策
        $datasLen = 0;
        if(!empty($datas)) {
            $datasLen = count($datas);
        }

        for($i = 0, $l = $datasLen; $i < $l; $i++) {
            // 入金形態
            $datasAry[$i]['receiptFormTag'] = BaseHtmlUtils::InputRadioTag(
                'Rct_ReceiptMethod' . $i,
                $recepitMasterList,
                0);
        }
//echo sprintf('tag preparing: %s<br/>', microtime(true) - $start);
        // [paging] ページングナビゲーション情報
//        $page_links = array( 'base' => 'rwrcptcfm/list/page' );
//        $page_links['prev'] = $page_links['base'] . '/' . ( $current_page - 1 );
//        $page_links['next'] = $page_links['base'] . '/' . ( $current_page + 1 );
        // [paging] ページング関連の情報をビューへアサイン
        $this->view->assign( 'current_page', $current_page );
//        $this->view->assign( 'pager', $pager );
//        $this->view->assign( 'page_links', $page_links );

        $this->view->assign("list", $datasAry);
        $this->view->assign('confirmationCnt', $confirmationCnt);
        $this->view->assign('confirmationAmount', $confirmationAmount);
        $this->view->assign('expireCnt', $expireCnt);
        $this->view->assign('expireAmount', $expireAmount);
//echo sprintf('logic complete: %s<br/>', microtime(true) - $start);

        return $this->view;
    }


// Del By Takemasa(NDC) 20150526 Stt 廃止
//     /**
//      * 入金確認待ちのリストを表示の旧ロジック。いずれ削除予定（10.5.26 eda）
//      */
//     public function list2Action()
// Del By Takemasa(NDC) 20150526 End 廃止

// Del By Takemasa(NDC) 20150806 Stt 未使用故コメントアウト化
//     /**
//      * 入金確認リスト(単体)
//      */
//     public function simplelistAction() {
// Del By Takemasa(NDC) 20150806 End 未使用故コメントアウト化

// 2015/09/29 Y.Suzuki Del 会計対応 Stt
// 会計対応で入金確認画面からの入金処理がなくなるので、入金確認処理の内容を削除。
//     /**
//      * 入金確認フォーム
//      */
//     public function confirmAction() {

// 会計対応で入金確認画面からの入金処理がなくなるので、入金確定処理の内容を削除。
//     /**
//      * 入金確定
//      */
//     public function rcptAction() {
// 2015/09/29 Y.Suzuki Del 会計対応 End

    /**
     * 入金関連処理ファンクションの基礎SQL取得。
     *
     * @return 入金関連処理ファンクションの基礎SQL
     */
    protected function getBaseP_ReceiptControl() {
        return <<<EOQ
CALL P_ReceiptControl(
    :pi_receipt_amount
,   :pi_order_seq
,   :pi_receipt_date
,   :pi_receipt_class
,   :pi_branch_bank_id
,   :pi_receipt_agent_id
,   :pi_deposit_date
,   :pi_user_id
,   :pi_receipt_note
,   @po_ret_sts
,   @po_ret_errcd
,   @po_ret_sqlcd
,   @po_ret_msg
    )
EOQ;
    }

    /**
     * 詳細入金処理(登録画面)
     */
    public function dtlrcptformAction()
    {
        $params = $this->getParams();

        $oseq = isset($params['oseq']) ? $params['oseq'] : 0;

        // 注文状態取得
        $sql = <<<EOQ
SELECT  SUM(CASE
                WHEN o.DataStatus IN (51, 61) OR (o.DataStatus = 91 AND o.CloseReason = 1) THEN 1
                ELSE 0
            END
        ) AS OrderStatus
     ,  SUM(CASE
                WHEN (pbc.PayDecisionFlg = 0 AND pbc.ValidFlg = 1) THEN 1
                ELSE 0
            END
        ) AS PayingBackStatus
FROM    T_Order o
        LEFT OUTER JOIN T_PayingBackControl pbc ON (pbc.OrderSeq = o.OrderSeq)
WHERE   o.P_OrderSeq = :OrderSeq
EOQ;
        $statusRow = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current();
        
        $status = 0;
        // 入金確認待ち、一部入金、入金済みｸﾛｰｽﾞで、有効な立替精算戻し指示がない場合、入金可能
        if ($statusRow['OrderStatus'] > 0 && $statusRow['PayingBackStatus'] == 0) {
            $status = 1;
        }

        $this->view->assign('status', $status);

        // 注文状況
        $claimdata = $this->app->dbAdapter->query($this->getDtlrcptformSql1())->execute(array(':OrderSeq' => $oseq))->current();
        // 入金状況
        $receiptList = ResultInterfaceToArray($this->app->dbAdapter->query($this->getDtlrcptformSql2())->execute(array(':OrderSeq' => $oseq)));

        $this->view->assign('claimdata', $claimdata);
        $this->view->assign('receiptList', $receiptList);

        // 入金方法
        // 2015/09/24 Y.Suzuki Mod 会計対応 Stt
        $sql = "SELECT KeyCode, KeyContent, Class4 FROM M_Code WHERE CodeId = 163 AND Class2 IN ('0','1','2')";
        $ri = $this->app->dbAdapter->query($sql)->execute(null);
        $receiptMethod = ResultInterfaceToArray($ri);
        usort($receiptMethod, function ($item1, $item2) {
            return $item1['Class4'] <=> $item2['Class4'];
        });
        $this->view->assign('receiptMethods', $receiptMethod);
        $str1  = '';
        $str1 .= '<option value="0">-----</option>';
        foreach ($receiptMethod as $value) {
            $str1 .= '<option value="' . $value['KeyCode'] . '">' . $value['KeyContent'] . '</option>';
        }
        $this->view->assign('str1', $str1);

        // 入金方法
        // 2022/03/15 trungtq
        $sql = "SELECT KeyCode, KeyContent, Class4 FROM M_Code WHERE CodeId = 163 AND Class2 IN ('1','2', '98', '99') ";
        $ri = $this->app->dbAdapter->query($sql)->execute(null);
        $receiptMethod2 = ResultInterfaceToArray($ri);
        usort($receiptMethod2, function ($item1, $item2) {
            return $item1['Class4'] <=> $item2['Class4'];
        });
        $str2  = '';
        $str2 .= '<option value="0">-----</option>';
        foreach ($receiptMethod2 as $key => $value) {
            $str2 .= '<option value="' . $value['KeyCode'] . '">' . $value['KeyContent'] . '</option>';
        }
        $this->view->assign('receiptMethods2', $str2);

        // 銀行支店
        // （会計対応）コンボボックスの値はコードマスタから取得する。
        $mdl = new CoralCodeMaster($this->app->dbAdapter);
        $this->view->assign('branchBankTag',BaseHtmlUtils::SelectTag("branchBank", $mdl->getMasterCodes(153, array(-1 => '-----')), 0));
        // コンビニ収納代行
        // コンボボックスの値は、コンビニ収納代行会社マスタではなく、コードマスタから取得する
        $this->view->assign('cvsReceiptAgentTag',BaseHtmlUtils::SelectTag("cvsReceiptAgent", $mdl->getCvsReceiptAgentMaster(), 0));
        // 2015/09/24 Y.Suzuki Mod 会計対応 End

        // 2015/09/24 Y.Suzuki Add 会計対応 Stt
        // 入金元 コードマスタ（識別ID：154）から取得
        $sql = "SELECT KeyCode, KeyContent FROM M_Code WHERE CodeId = 154 AND Class2 = 1";
        $ri = $this->app->dbAdapter->query($sql)->execute(null);
        $receiptClass = ResultInterfaceToArray($ri);
        $this->view->assign('receiptClasses', $receiptClass);
        // 詳細入金方法 コードマスタ（識別ID：155）から取得
        $sql = "SELECT KeyCode, KeyContent FROM M_Code WHERE CodeId = 155 AND Class2 = 1";
        $ri = $this->app->dbAdapter->query($sql)->execute(null);
        $classDetails = ResultInterfaceToArray($ri);
        $this->view->assign('classDetails', $classDetails);
        // 口座番号(郵便) コードマスタ（識別ID：152）から取得
        $this->view->assign('accountNumberTag',BaseHtmlUtils::SelectTag("accountNumber", $mdl->getMasterCodes(152, array(-1 => '-----')), 0, ''));
        // 2015/09/24 Y.Suzuki Add 会計対応 End

        $this->view->assign('oseq', $oseq);

        // 入金取消ボタンの制御
        $sql = <<<EOQ
SELECT  COUNT(1) AS cnt
FROM    T_Order o
        INNER JOIN T_ClaimControl cc ON (cc.OrderSeq = o.P_OrderSeq)
        INNER JOIN T_PayingAndSales pas ON (pas.OrderSeq = o.OrderSeq)
WHERE   o.Cnl_Status = 0                            /* 未ｷｬﾝｾﾙ */
AND     (o.DataStatus = 61 OR (o.DataStatus = 91 AND o.CloseReason = 1))   /* 入金済みｸﾛｰｽﾞ OR 一部入金 */
AND     cc.RepayAmountTotal = 0                     /* 未返金 */
AND     o.P_OrderSeq = :OrderSeq
AND     (SELECT COUNT(1) FROM T_RepaymentControl WHERE ClaimId = cc.ClaimId AND RepayStatus IN (0, 1)) = 0
EOQ;
        $cnt = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current()['cnt'];
        $this->view->assign('cnt', $cnt);

        // アラート取消ボタンの制御
        $sql = "SELECT COUNT(1) AS alertcnt FROM T_StagnationAlert WHERE AlertClass = 3 AND AlertSign = 1 AND OrderSeq = :OrderSeq";
        $alertcnt = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current()['alertcnt'];
        $this->view->assign('alertcnt', $alertcnt);

        $this->view->assign('today', date('Y-m-d'));
        $this->view->assign('receiptNote', '');
        $this->view->assign('receiptCheckMail', 1);

        return $this->view;
}

    /**
     * 詳細入金処理(登録処理)
     */
    public function dtlrcptsaveAction()
    {
        $params = $this->getParams();

        $oseq = isset($params['oseq']) ? $params['oseq'] : 0;

        $data['receiptMethod'] = $params['receiptMethod'];
        $data['ReceiptDate'] = $params['ReceiptDate'];
        $data['ReceiptAmount'] = $params['ReceiptAmount'];
        $data['branchBank'] = $params['branchBank'];
        $data['cvsReceiptAgent'] = $params['cvsReceiptAgent'];
        // 2015/09/24 Y.Suzuki Mod 会計対応 Stt
        $data['receiptClass'] = $params['receiptClass'];
        $data['DepositDate'] = $params['DepositDate'];
        $data['classDetails'] = $params['classDetails'];
        $data['accountNumber'] = $params['accountNumber'];
        $data['bankFlg'] = '2';     // 銀行入金区分：2（画面からの入金なので 2 固定）
        // 2015/09/24 Y.Suzuki Mod 会計対応 End
        $receiptNote = $params['ReceiptNote'];
//        $receiptCheckMail = ($params['ReceiptCheckMail'] == 'on') ? 1 : 0;
        $receiptCheckMail = $params['ReceiptCheckMail'];
        $errors = $this->validateDtlrcptform($data, $oseq);

        // count関数対策
        if (!empty($errors)) {
            // エラーがあればエラーメッセージをセット
            $this->view->assign('error', $errors);

            // 注文状態取得
            $sql = <<<EOQ
SELECT  SUM(CASE
                WHEN o.DataStatus IN (51, 61) OR (o.DataStatus = 91 AND o.CloseReason = 1) THEN 1
                ELSE 0
            END
        ) AS OrderStatus
     ,  SUM(CASE
                WHEN (pbc.PayDecisionFlg = 0 AND pbc.ValidFlg = 1) THEN 1
                ELSE 0
            END
        ) AS PayingBackStatus
FROM    T_Order o
        LEFT OUTER JOIN T_PayingBackControl pbc ON (pbc.OrderSeq = o.OrderSeq)
WHERE   o.P_OrderSeq = :OrderSeq
EOQ;
            $statusRow = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current();
            $status = 0;
            // 入金確認待ち、一部入金、入金済みｸﾛｰｽﾞで、有効な立替精算戻し指示がない場合、入金可能
            if ($statusRow['OrderStatus'] > 0 && $statusRow['PayingBackStatus'] == 0) {
                $status = 1;
            }

            $this->view->assign('status', $status);

            // 注文状況
            $claimdata = $this->app->dbAdapter->query($this->getDtlrcptformSql1())->execute(array(':OrderSeq' => $oseq))->current();
            // 入金状況
            $receiptList = ResultInterfaceToArray($this->app->dbAdapter->query($this->getDtlrcptformSql2())->execute(array(':OrderSeq' => $oseq)));

            $this->view->assign('claimdata', $claimdata);
            $this->view->assign('receiptList', $receiptList);

            // 入金方法
            // 2015/09/24 Y.Suzuki Mod 会計対応 Stt
            $sql = "SELECT KeyCode, KeyContent, Class4 FROM M_Code WHERE CodeId = 163 AND Class2 IN ('0','1','2')";
            $ri = $this->app->dbAdapter->query($sql)->execute(null);
            $receiptMethod = ResultInterfaceToArray($ri);
            usort($receiptMethod, function ($item1, $item2) {
                return $item1['Class4'] <=> $item2['Class4'];
            });
            $this->view->assign('receiptMethods', $receiptMethod);
            $str1  = '';
            $str1 .= '<option value="0">-----</option>';
            foreach ($receiptMethod as $value) {
                $str1 .= '<option value="' . $value['KeyCode'] . '">' . $value['KeyContent'] . '</option>';
            }
            $this->view->assign('str1', $str1);
    
            // 入金方法
            // 2022/03/15 trungtq
            $sql = "SELECT KeyCode, KeyContent, Class4 FROM M_Code WHERE CodeId = 163 AND Class2 IN ('1','2', '98', '99') ";
            $ri = $this->app->dbAdapter->query($sql)->execute(null);
            $receiptMethod2 = ResultInterfaceToArray($ri);
            usort($receiptMethod2, function ($item1, $item2) {
                return $item1['Class4'] <=> $item2['Class4'];
            });
            $str2  = '';
            $str2 .= '<option value="0">-----</option>';
            foreach ($receiptMethod2 as $key => $value) {
                $str2 .= '<option value="' . $value['KeyCode'] . '">' . $value['KeyContent'] . '</option>';
            }
            $this->view->assign('receiptMethods2', $str2);
            if ($params['receiptClass'] == 9) {
                $this->view->assign('receiptMethods', $receiptMethod2);
            }

            $this->view->assign('receiptMethod', $data['receiptMethod']);

            // 銀行支店
            // （会計対応）コンボボックスの値はコードマスタから取得する。
            $mdl = new CoralCodeMaster($this->app->dbAdapter);
            $this->view->assign('branchBankTag',BaseHtmlUtils::SelectTag("branchBank", $mdl->getMasterCodes(153, array(-1 => '-----')), $data['branchBank']));
            // コンビニ収納代行
            $this->view->assign('cvsReceiptAgentTag',BaseHtmlUtils::SelectTag("cvsReceiptAgent", $mdl->getCvsReceiptAgentMaster(), $data['cvsReceiptAgent']));
            // 2015/09/24 Y.Suzuki Mod 会計対応 End

            // 2015/09/24 Y.Suzuki Add 会計対応 Stt
            // 入金元 コードマスタ（識別ID：154）から取得
            $sql = "SELECT KeyCode, KeyContent FROM M_Code WHERE CodeId = 154 AND Class2 = 1";
            $ri = $this->app->dbAdapter->query($sql)->execute(null);
            $receiptClass = ResultInterfaceToArray($ri);
            $this->view->assign('receiptClasses', $receiptClass);
            $this->view->assign('receiptClass', $data['receiptClass']);
            // 詳細入金方法
            $sql = "SELECT KeyCode, KeyContent FROM M_Code WHERE CodeId = 155 AND Class2 = 1";
            $ri = $this->app->dbAdapter->query($sql)->execute(null);
            $classDetails = ResultInterfaceToArray($ri);
            $this->view->assign('classDetails', $classDetails);
            $this->view->assign('classDetail', $data['classDetails']);
            // 口座番号(郵便) コードマスタ（識別ID：152）から取得
            $this->view->assign('accountNumberTag',BaseHtmlUtils::SelectTag("accountNumber", $mdl->getMasterCodes(152, array(-1 => '-----')), $data['accountNumber'], ''));
            // 2015/09/24 Y.Suzuki Add 会計対応 End

            $this->view->assign('oseq', $oseq);
            $this->view->assign('receiptNote', $receiptNote);
            $this->view->assign('receiptCheckMail', $receiptCheckMail);
            $this->view->assign('ReceiptDate', $data['ReceiptDate']);
            $this->view->assign('ReceiptAmount', $data['ReceiptAmount']);
            $this->view->assign('DepositDate', $data['DepositDate']);       // 2015/09/24 Y.Suzuki 会計対応 Add

            $this->view->assign('today', date('Y-m-d'));

            $this->setTemplate('dtlrcptform');
            return $this->view;
        }

        // 更新処理実施
        $errorCount = 0;
        $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
        try {
            // ユーザIDの取得
            $userTable = new \models\Table\TableUser($this->app->dbAdapter);
            $userId = $userTable->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

            // 2015/10/19 Y.Suzuki Add 会計対応 Stt
            if (($data['receiptClass'] == 9 && $data['receiptMethod'] == 3) || ($data['receiptClass'] == 9 && $data['receiptMethod'] == 9 && $data['classDetails'] == 4)) {
                // コードマスタから銀行支店IDを取得する。
                $sql = "SELECT Class1 FROM M_Code WHERE CodeId = 153 AND KeyCode = :KeyCode";
                $branchBankId = $this->app->dbAdapter->query($sql)->execute(array(':KeyCode' => $data['branchBank']))->current()['Class1'];
            }
            // 2015/10/19 Y.Suzuki Add 会計対応 End

            // 入金前データステータスの取得
            $datastatusBeforeReceipt = (int)$this->app->dbAdapter->query(" SELECT DataStatus FROM T_Order WHERE OrderSeq = :OrderSeq "
                )->execute(array(':OrderSeq' => (int)$oseq))->current()['DataStatus'];

            // 入金関連処理SQL
            $stm = $this->app->dbAdapter->query($this->getBaseP_ReceiptControl());

            // SQL実行結果取得用のSQL
            $getretvalsql = " SELECT @po_ret_sts AS po_ret_sts, @po_ret_errcd AS po_ret_errcd, @po_ret_sqlcd AS po_ret_sqlcd, @po_ret_msg AS po_ret_msg ";

            $prm = array(
                    ':pi_receipt_amount'   => $data['ReceiptAmount'],
                    ':pi_order_seq'        => $oseq,
                    ':pi_receipt_date'     => $data['ReceiptDate'],
                    ':pi_receipt_class'    => $data['receiptMethod'],
                    ':pi_branch_bank_id'   => (! empty($branchBankId)) ? $branchBankId : null,                      // 2015/10/10 Y.Suzuki 会計対応 Mod
                    ':pi_receipt_agent_id' => ($data['receiptClass'] == 0) ? $data['cvsReceiptAgent'] : null,       // 2015/11/27 Y.Suzuki 会計対応 Mod
                    ':pi_deposit_date'     => (! empty($data['DepositDate'])) ? $data['DepositDate'] : null,        // 2015/11/10 Y.Suzuki 会計対応 Mod
                    ':pi_user_id'          => $userId,
                    ':pi_receipt_note'     => $receiptNote,
            );

            $ri = $stm->execute($prm);

            // SQL実行例外なしもエラー戻り値の時は例外をｽﾛｰ
            $retval = $this->app->dbAdapter->query($getretvalsql)->execute(null)->current();
            if ($retval['po_ret_sts'] != 0) {
                throw new \Exception($retval['po_ret_msg']);
            }

            // 未印刷の請求書印刷予約データを削除
            $mdlch = new TableClaimHistory($this->app->dbAdapter);
            $mdlch->deleteReserved($oseq, $userId);

            // 立替・売上管理テーブル更新
            $mdlpas = new TablePayingAndSales($this->app->dbAdapter);
            $mdlo = new TableOrder($this->app->dbAdapter);
            // 注文データを取得
            $ri = $mdlo->findOrder(array('P_OrderSeq' => $oseq));
            $order = ResultInterfaceToArray($ri);

            $mdlapas = new \models\Table\ATablePayingAndSales($this->app->dbAdapter);
            $oid = 0;
            // 取得できた件数分、ループする
            foreach ($order as $key => $value) {
                // 入金済み正常クローズの場合、無条件に立替対象とする。
                if ($value['DataStatus'] == 91 && $value['CloseReason'] == 1) {
                    // 既に[立替条件クリアフラグ]が[1：条件をクリアしている]か？の取得
                    $isAlreadyClearCondition = $mdlpas->IsAlreadyClearCondition($value['OrderSeq']);

                    $mdlpas->clearConditionForCharge($value['OrderSeq'], 1, $userId);

                    if (!$isAlreadyClearCondition) {
                        $row_pas = $this->app->dbAdapter->query(" SELECT Seq FROM T_PayingAndSales WHERE OrderSeq = :OrderSeq "
                            )->execute(array(':OrderSeq' => $value['OrderSeq']))->current();

                        // 入金により立替条件クリアフラグが１化されるとき => '2:入金'として更新(顧客入金日をセットする)
                        $mdlapas->saveUpdate(array('ATUriType' => 2, 'ATUriDay' => date('Ymd', strtotime($data['ReceiptDate']))), $row_pas['Seq']);
                    }
                }
                if (!is_null($value['OemId'])) {
                    $oid = $value['OemId'];
                }
            }

            // 作成した入金管理Seqを取得する（注文Seqに対する入金は複数存在する可能性があるため、MAXの入金Seqを取得する）
            $sql = "SELECT MAX(ReceiptSeq) AS ReceiptSeq FROM T_ReceiptControl WHERE OrderSeq = :OrderSeq GROUP BY OrderSeq";
            $rcptSeq = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current()['ReceiptSeq'];

            if ($datastatusBeforeReceipt != 91 && $order[0]['DataStatus'] == 91) {// [91：クローズ]からの入金はメール対象から除外
                try
                {
                    // 入金確認メール送信
                    $sql = "SELECT COUNT(*) AS cnt FROM M_Code WHERE CodeId=163 AND Class3=1 AND KeyCode = :KeyCode";
                    $cnt = $this->app->dbAdapter->query($sql)->execute(array(':KeyCode' => $data['receiptMethod']))->current()['cnt'];
                    if ($receiptCheckMail == 1 & $cnt < 1) {
                        $mail = new CoralMail($this->app->dbAdapter, $this->app->mail['smtp']);
                        $mail->SendRcptConfirmMail($rcptSeq, $userId);
                    }
                }
                catch(\Exception $e) {  }
            }

            // 注文履歴登録用に親注文Seqから子注文Seqを再取得する。
            $sql = "SELECT OrderSeq FROM T_Order WHERE P_OrderSeq = :P_OrderSeq AND Cnl_Status = 0";

            $ri = $this->app->dbAdapter->query($sql)->execute(array(':P_OrderSeq' => $params['oseq']));
            $rows = ResultInterfaceToArray($ri);

            // 注文履歴へ登録
            $history = new CoralHistoryOrder($this->app->dbAdapter);
            // 取得できた件数分ループする
            foreach ($rows as $row) {
                // 注文履歴登録
                $history->InsOrderHistory($row["OrderSeq"], 61, $userId);
            }

            // 2016/01/05 Y.Suzuki Mod 会計関連_入金取消対応（全ての更新処理が完了したあとで入金管理_会計のINSERT処理をする必要が発生したため、位置を移動） Stt
            // 2015/10/05 Y.Suzuki Add 会計対応 Stt
            // 会計用項目をINSERT
            // 2015/12/14 Y.Suzuki Del 上（513～514行目）で取得しているので削除 Stt
//             // 入金管理Seqの取得（複数存在する場合を考慮して、MAX値を取得する）
//             $sql = "SELECT MAX(ReceiptSeq) AS ReceiptSeq FROM T_ReceiptControl WHERE OrderSeq = :OrderSeq";
//             $rcptSeq = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current()['ReceiptSeq'];
            // 2015/12/14 Y.Suzuki Del 上（513～514行目）で取得しているので削除 End

            // 2016/01/05 Y.Suzuki Add 更新後のデータを取得 Stt
            // 入金取消前立替クリアフラグ、入金取消前立替クリア日
            $sql = "SELECT ClearConditionForCharge, ClearConditionDate FROM T_PayingAndSales WHERE OrderSeq = :OrderSeq";
            $ri = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq));
            $clearConditionForCharge = $ri->current()['ClearConditionForCharge'];
            $clearConditionDate = $ri->current()['ClearConditionDate'];
            // 入金取消前キャンセル－ステータス、入金取消前配送－着荷確認
            $sql = "SELECT Cnl_Status, Deli_ConfirmArrivalFlg FROM T_Order WHERE OrderSeq = :OrderSeq";
            $ri = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq));
            $cnlStatus = $ri->current()['Cnl_Status'];
            $deliConfirmArrivalFlg = $ri->current()['Deli_ConfirmArrivalFlg'];
            // 2016/01/05 Y.Suzuki Add 更新後のデータを取得 End

            // 入金元[その他] かつ 入金方法[郵政] の場合
            if ($data['receiptClass'] == 9 && $data['receiptMethod'] == 2) {
                // コードマスタから取得する
                $sql = "SELECT Class1 FROM M_Code WHERE CodeId = 152 AND KeyCode = :KeyCode";
                $accountNumber = $this->app->dbAdapter->query($sql)->execute(array(':KeyCode' => $data['accountNumber']))->current()['Class1'];
                $data['accountNumber'] = $accountNumber;
            } else {
                $data['accountNumber'] = null;
            }
            // 入金元[その他] かつ 入金方法[その他] の場合
            if ($data['receiptClass'] == 9 && $data['receiptMethod'] == 9) {
                $data['classDetails'] = $data['classDetails'];
            } else {
                $data['classDetails'] = null;
            }
            $atdata = array(
                    'ReceiptSeq' => $rcptSeq,
                    'AccountNumber' => $data['accountNumber'],
                    'ClassDetails' => $data['classDetails'],
                    'BankFlg' => $data['bankFlg'],
                    // 2016/01/05 Y.Suzuki Add 会計対応_入金取消関連（INSERT項目追加） Stt
                    'Before_ClearConditionForCharge' => $clearConditionForCharge,   // 入金取消前立替クリアフラグ
                    'Before_ClearConditionDate' => $clearConditionDate,             // 入金取消前立替クリア日
                    'Before_Cnl_Status' => $cnlStatus,                              // 入金取消前キャンセル－ステータス
                    'Before_Deli_ConfirmArrivalFlg' => $deliConfirmArrivalFlg,      // 入金取消前配送－着荷確認
                    // 2016/01/05 Y.Suzuki Add 会計対応_入金取消関連（INSERT項目追加） End
            );

            $mdlatrc = new ATableReceiptControl($this->app->dbAdapter);
            $mdlatrc->saveNew($atdata);
            // 2015/10/05 Y.Suzuki Add 会計対応 End
            // 2016/01/05 Y.Suzuki Mod 会計関連_入金取消対応（全ての更新処理が完了したあとで入金管理_会計のINSERT処理をする必要が発生したため、位置を移動） End
            $sql = "SELECT COUNT(*) AS cnt FROM M_Code WHERE CodeId=163 AND Class3=1 AND KeyCode = :KeyCode ";
            $cnt = $this->app->dbAdapter->query($sql)->execute(array(':KeyCode' => $data['receiptMethod']))->current()['cnt'];
            if ($cnt > 0) {
                //update AT_Order
                $mdlao = new ATableOrder($this->app->dbAdapter);
                $sql = "SELECT Class1,Class3 FROM M_Code WHERE CodeId = 198 AND KeyCode = :KeyCode";
                $ri = $this->app->dbAdapter->query($sql)->execute(array(':KeyCode' => $params['receiptMethod']));
                $class1 = $ri->current()['Class1'];
                $ifData = array(
                    'payment_method'     => $class1,
                );
                $prm = array(
                    'ExtraPayType' => 1,
                    'ExtraPayNote' => Json::encode($ifData),
                );
                $mdlao->saveUpdateExtraPayNote($prm, $oseq);
                // update T_PayingAndSales
                $paymentId = 0;
                if (!is_null($class1)) {
                    $sqlSbps = "SELECT SbpsPaymentId FROM M_SbpsPayment WHERE PaymentName = :PaymentName AND OemId = :OemId";
                    $paymentId = $this->app->dbAdapter->query($sqlSbps)->execute(array(':PaymentName' => $class1, ':OemId' => $oid))->current()['SbpsPaymentId'];
                }
                $sql = "";
                $sql .= "SELECT s.* ";
                $sql .= ", o.UseAmount ";
                $sql .= ", o.ClaimSendingClass ";
                $sql .= " FROM T_Order AS o";
                $sql .= " INNER JOIN T_SiteSbpsPayment AS s ON s.SiteId = o.SiteId";
                $sql .= " WHERE o.OrderSeq = ". $oseq;
                $sql .= " AND s.PaymentId = ". $paymentId;
                $orderSite = $this->app->dbAdapter->query($sql)->execute()->current();

                $mdlpas = new TablePayingAndSales($this->app->dbAdapter);
                $mdlsp = new TableSystemProperty($this->app->dbAdapter);
                // 立替・売上管理の取得
                $payingAndSales = $mdlpas->findPayingAndSales( array('OrderSeq' => $oseq) )->current();
                $payingAndSalesSeq = $payingAndSales['Seq'];
                $useAmount = 0;
                $appSettlementFeeRate = ( ( !isset($orderSite['SettlementFeeRate']) || empty($orderSite['SettlementFeeRate']) ) ? 0 : $orderSite['SettlementFeeRate'] );
                $settlementFee = floor( $orderSite['UseAmount'] * ($appSettlementFeeRate / 100) );
                $cSClaimFeeDK = ( ( !isset($orderSite['ClaimFeeDK']) || empty($orderSite['ClaimFeeDK']) ) ? 0 : $orderSite['ClaimFeeDK'] );
                $cSClaimFeeBS = ( ( !isset($orderSite['ClaimFeeBS']) || empty($orderSite['ClaimFeeBS']) ) ? 0 : $orderSite['ClaimFeeBS'] );
                $claimFee = ( $orderSite['ClaimSendingClass'] == '11' ? $cSClaimFeeDK : $cSClaimFeeBS );
                $claimFee = $mdlsp->getIncludeTaxAmount(date('Y-m-d'), $claimFee);
                $chargeAmount = ( -1 * ( $settlementFee + $claimFee ) );

                // 立替・売上管理の更新
                $prm = array(
                    'UseAmount'               => $useAmount,
                    'AppSettlementFeeRate'    => $appSettlementFeeRate,
                    'SettlementFee'           => $settlementFee,
                    'ClaimFee'                => $claimFee,
                    'ChargeAmount'            => $chargeAmount,
                );
                $mdlpas->saveUpdate($prm, $payingAndSalesSeq);

                //insert TableToBackMypageIF
                $mdltbi = new TableToBackMypageIF($this->app->dbAdapter);
                $mypageIFdata = array(
                    'Status' => 0,
                    'IFClass' => 1,
                    'IFData' => Json::encode($ifData),
                    'OrderSeq' => $oseq,
                    'RegistDate' => date('Y-m-d H:i:s'),
                    'UpdateDate' => date('Y-m-d H:i:s'),
                    'ValidFlg' => 1,
                );
                $mdltbi->saveNew($mypageIFdata);
                // send mail SendCreditBuyingCompleteMail
                if ($receiptCheckMail == 1) {
                    $mail = new CoralMail($this->app->dbAdapter, $this->app->mail['smtp']);
                    $mail->SendCreditBuyingCompleteMail($oseq, $userId, date('Y-m-d H:i:s'));
                }
            }

            $this->app->dbAdapter->getDriver()->getConnection()->commit();
        }
        catch(\Exception $err) {
            $this->app->dbAdapter->getDriver()->getConnection()->rollBack();
            $errorCount = 1;
        }

        $this->view->assign('errorCount', $errorCount);

        return $this->view;
    }

    /**
     * 詳細入金処理フォームでの[注文状況]ベースSQL取得
     * @return string ベースSQL取得
     */
    protected function getDtlrcptformSql1()
    {
        return <<<EOQ
SELECT  o.OrderSeq              /* 注文Seq */
    ,   o.OrderId               /* 注文ID */
    ,   o.Ent_OrderId           /* 任意注文番号 */
    ,   o.ReceiptOrderDate      /* 注文受日時 */
    /* ステータス */
    ,   CASE
            WHEN (SELECT COUNT(*)
                    FROM T_Order
                   WHERE P_OrderSeq = o.OrderSeq
                     AND DataStatus > 51
                     AND NOT (DataStatus = 91 AND CloseReason IN (2, 3))
                 ) > 0 THEN '入金済'
            ELSE '未入金'
        END AS DataStatus
    ,   cc.UseAmountTotal AS UseAmount  /* 利用額 */
    ,   cc.ClaimAmount          /* 請求額 */
    ,   cc.ClaimedBalance       /* 残金 */
FROM    T_Order o
        INNER JOIN T_ClaimControl cc ON (o.P_OrderSeq = cc.OrderSeq)
WHERE   o.OrderSeq = :OrderSeq
EOQ;
    }

    /**
     * 詳細入金処理フォームでの[入金状況]ベースSQL取得
     * @return string ベースSQL取得
     */
    protected function getDtlrcptformSql2()
    {
        return <<<EOQ
SELECT '1'
     , IF(rc.ReceiptClass=9, (SELECT KeyContent FROM M_Code WHERE CodeId = 155 AND KeyCode = arc.ClassDetails), (SELECT KeyContent FROM M_Code WHERE CodeId = 198 AND KeyCode = rc.ReceiptClass)) AS ReceiptClass /* 入金形態 */
     , DATE(rc.ReceiptDate) AS ReceiptDate     /* 入金日 */
     , rc.ReceiptAmount                        /* 実入金額 */
     , rc.ReceiptSeq as Seq1
     , rc.ReceiptSeq as Seq2
     , rc.Receipt_Note as ReceiptNote
     , rc.RegistDate as RegistDate
     , rc.ReceiptSeq
     , rc.OrderSeq
FROM T_ReceiptControl rc
     INNER JOIN AT_ReceiptControl arc
             ON rc.ReceiptSeq = arc.ReceiptSeq
WHERE arc.Rct_CancelFlg = 0
  AND rc.OrderSeq       = :OrderSeq
UNION ALL 
SELECT '3'
     , (
	  		CASE
            WHEN tsc.SundryType = 1 THEN CONCAT('雑収入.', (SELECT KeyContent FROM M_Code WHERE CodeId = 96 AND KeyCode = tsc.SundryClass))
            ELSE  CONCAT('雑損失.', (SELECT KeyContent FROM M_Code WHERE CodeId = 96 AND KeyCode = tsc.SundryClass))
        END
		  ) AS ReceiptClass
     , DATE(tsc.ProcessDate) AS ReceiptDate    /* 入金日 */
     , tsc.SundryAmount as ReceiptAmount               /* 実入金額 */
     , tsc.SundrySeq as Seq1
     , tsc.SundrySeq as Seq2
     , tsc.Note as ReceiptNote
     , tsc.RegistDate as RegistDate
     , tsc.SundrySeq as ReceiptSeq
     , tsc.OrderSeq
FROM T_SundryControl tsc
WHERE tsc.OrderSeq       = :OrderSeq
UNION ALL 
SELECT '4'
     , '印紙代' AS ReceiptClass /* 入金形態 */
     , DATE(tsf.DecisionDate) AS ReceiptDate    /* 入金日 */
     , tsf.StampFee as ReceiptAmount               /* 実入金額 */
     , tsf.Seq as Seq1
     , tsf.Seq as Seq2
     , '' as ReceiptNote
     , tsf.RegistDate as RegistDate
     , tsf.Seq as ReceiptSeq
     , tsf.OrderSeq
FROM T_StampFee tsf
WHERE tsf.OrderSeq       = :OrderSeq
UNION ALL
SELECT '2'
     , IF(rc2.ReceiptClass=9, (SELECT KeyContent FROM M_Code WHERE CodeId = 155 AND KeyCode = arc2.ClassDetails), (SELECT KeyContent FROM M_Code WHERE CodeId = 198 AND KeyCode = rc2.ReceiptClass)) AS ReceiptClass /* 入金形態 */
     , DATE(rc2.ReceiptDate) AS ReceiptDate    /* 入金日 */
     , rc2.ReceiptAmount * -1                  /* 実入金額 */
     , rc.ReceiptSeq as Seq1
     , rc2.ReceiptSeq as Seq2
     , rc.Receipt_Note as ReceiptNote
     , rc.RegistDate as RegistDate
     , rc.ReceiptSeq
     , rc.OrderSeq
FROM T_ReceiptControl rc
     INNER JOIN AT_ReceiptControl arc
             ON rc.ReceiptSeq = arc.ReceiptSeq
     INNER JOIN T_ReceiptControl rc2
             ON rc2.ReceiptSeq < rc.ReceiptSeq
            AND rc2.OrderSeq = rc.OrderSeq
     INNER JOIN AT_ReceiptControl arc2
             ON rc2.ReceiptSeq = arc2.ReceiptSeq
WHERE arc.Rct_CancelFlg = 1
  AND rc2.ReceiptSeq BETWEEN (SELECT IFNULL(MAX(tmp.ReceiptSeq), 0) FROM T_ReceiptControl tmp, AT_ReceiptControl tmp2 WHERE tmp.ReceiptSeq = tmp2.ReceiptSeq AND tmp.ReceiptSeq < rc.ReceiptSeq AND tmp.OrderSeq = rc.OrderSeq AND tmp2.Rct_CancelFlg = 1) + 1 AND rc.ReceiptSeq - 1
  AND rc.OrderSeq       = :OrderSeq
ORDER BY RegistDate DESC
EOQ;
    }

    /**
     * 詳細入金処理フォームの内容を検証する
     * @param array $data 詳細入金処理フォームデータ
     * @return array エラーメッセージの配列
     */
    protected function validateDtlrcptform(array $data, $oseq = null)
    {
        $errors = array();

        // 2015/09/24 Y.Suzuki Mod 会計対応 Stt
        // 注文状況:入金元 ※必ず有効な値が設定されている為、チェック不要

        // 注文状況:入金方法
        $key = 'receiptMethod';
        if (!isset($errors[$key]) && !((int)$data[$key] > 0)) {
            $errors[$key] = array("'入金方法'を選択してください");
        }

        // 入金元で[収納代行会社]が選択されている場合、入金方法で[コンビニ] OR [郵政] 以外が選択されていたらエラー
        // if (!isset($errors[$key]) && ($data['receiptClass'] == 0 && ! ($data[$key] == 1 || $data[$key] == 2 || $data[$key] == 13))) {
        //     $errors[$key] = array("'入金元'が'収納代行会社'の場合、'入金方法'は'コンビニ'または'郵政'または'口座振替'を選択してください");
        // }
        /*
        // 入金元で[その他]が選択されている場合、入金方法で[郵政] OR [銀行] OR [その他] 以外が選択されていたらエラー
        if (!isset($errors[$key]) && ($data['receiptClass'] == 9 && ! ($data[$key] == 2 || $data[$key] == 3 || $data[$key] == 9))) {
            $errors[$key] = array("'入金元'が'その他'の場合、'入金方法'は'郵政'または'銀行'または'その他'を選択してください");
        }*/

        if (!isset($errors[$key]) && ((int)$data['receiptMethod'] == 0)) {
            $errors[$key] = array("入金元と入金方法が紐付きません。正しい入金方法を選択してください。");
        }

        // 2015/09/24 Y.Suzuki Mod 会計対応 End

        // 注文状況:入金日
        $key = 'ReceiptDate';
        if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
            $errors[$key] = array("'入金日'を入力してください");
        }
        if (!isset($errors[$key]) && !IsValidFormatDate($data[$key])) {
            $errors[$key] = array("'入金日'の形式が不正です");
        }
        if (!isset($errors[$key]) && (date('Y-m-d') < date('Y-m-d', strtotime($data[$key])))) {
            $errors[$key] = array("'入金日'に未来日を指定することはできません");
        }

        // 注文状況:実入金額
        $key = 'ReceiptAmount';
        if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
            $errors[$key] = array("'実入金額'を入力してください");
        }
        if (!isset($errors[$key]) && !(is_numeric($data[$key]))) {
            $errors[$key] = array("'実入金額'の指定が不正です");
        }
        if (!isset($errors[$key]) && !((int)$data[$key] > 0)) {
            $errors[$key] = array("'実入金額'の指定が不正です");
        }

        // 2015/09/24 Y.Suzuki Mod 会計対応 Stt
        // 注文状況:銀行支店
        $key = 'branchBank';
        if (!isset($errors[$key]) && (($data['receiptMethod'] == 3 || $data['classDetails'] == 4) && !((int)$data[$key] > -1))) {
            // 入金方法で[銀行] OR 詳細入金方法で[弁護士入金]が指定されているにもかかわらず、銀行支店が選択されていないとき
            $errors[$key] = array("'銀行支店'を選択してください");
        }

        // 注文状況:収納代行
        $key = 'cvsReceiptAgent';
        if (!isset($errors[$key]) && ($data['receiptClass'] == 0 && !((int)$data[$key] > 0))) {
            // 入金元で[収納代行会社]が指定されているにもかかわらず、収納代行が選択されていないとき
            $errors[$key] = array("'収納代行'を選択してください");
        }
        // 2015/09/24 Y.Suzuki Mod 会計対応 End

        // 2015/09/24 Y.Suzuki Add 会計対応 Stt
        // 注文状況:入金予定日
        $key = 'DepositDate';
        if (!isset($errors[$key]) && ($data['receiptClass'] == 0 && !(strlen($data[$key]) > 0))) {
            // 入金元で[収納代行会社]が指定されているにもかかわらず、入金予定日が入力されていないとき
            $errors[$key] = array("'入金予定日'を入力してください");
        }
        if (!isset($errors[$key]) && ($data['receiptClass'] == 0 && !IsValidFormatDate($data[$key]))) {
            $errors[$key] = array("'入金予定日'の形式が不正です");
        }

        // 注文状況:詳細入金方法
        $key = 'classDetails';
        if (!isset($errors[$key]) && (($data['receiptClass'] == 9 && $data['receiptMethod'] == 9) && !((int)$data[$key] > 0))) {
            // 入金元で[その他] かつ 入金方法で[その他]が指定されているにもかかわらず、詳細入金方法が選択されていないとき
            $errors[$key] = array("'詳細入金方法'を選択してください");
        }

        // 注文状況:口座番号(郵便)
        $key = 'accountNumber';
        if (!isset($errors[$key]) && (($data['receiptClass'] == 9 && $data['receiptMethod'] == 2) && !((int)$data[$key] > -1))) {
            // 入金元で[その他] かつ 入金方法で[郵政]が指定されているにもかかわらず、口座番号(郵便)が選択されていないとき
            $errors[$key] = array("'口座番号(郵便)'を選択してください");
        }
        // 2015/09/24 Y.Suzuki Add 会計対応 End

        return $errors;
    }

    /**
     * (ajax)入金取消処理
     */
    public function rcptcancelAction()
    {
        $params = $this->getParams();

        $oseq = isset($params['oseq']) ? $params['oseq'] : 0;

        // 更新処理を行う。
        $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
        try {
            // ユーザーIDの取得
            $obj = new \models\Table\TableUser($this->app->dbAdapter);
            $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

            // -------------------------
            // エラーチェック
            // -------------------------
            // 注文データを取得
            $sql = "SELECT COUNT(*) AS cnt FROM T_Order WHERE OrderSeq = :OrderSeq AND Cnl_Status = 0";
            $cnt = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current()['cnt'];
            // 未キャンセル以外の場合エラー（未キャンセルのデータが存在していれば処理が流れる）
            if ($cnt == 0) {
                $msg = 'キャンセル申請中、もしくはキャンセル済みの注文のため、取消できません。';
                // ロールバック
                $this->app->dbAdapter->getDriver()->getConnection()->rollback();
            } else {
                // -------------------------
                // 入金データを取得
                // -------------------------
                $mdlrc = new TableReceiptControl($this->app->dbAdapter);

                // 直近の1件を取得(登録日で降順ソートしたLIMIT1を取得)
                $data = $this->app->dbAdapter->query("SELECT * FROM T_ReceiptControl WHERE OrderSeq = :OrderSeq ORDER BY RegistDate DESC LIMIT 1")->execute(array(':OrderSeq' => $oseq))->current();

                // 注文Seqでｻﾏﾘして金額項目を取得
                $sql = <<<EOQ
SELECT  OrderSeq
    ,   SUM(ReceiptAmount) AS ReceiptAmount
    ,   SUM(CheckingUseAmount) AS CheckingUseAmount
    ,   SUM(CheckingClaimFee) AS CheckingClaimFee
    ,   SUM(CheckingDamageInterestAmount) AS CheckingDamageInterestAmount
    ,   SUM(CheckingAdditionalClaimFee) AS CheckingAdditionalClaimFee
FROM    T_ReceiptControl
WHERE   OrderSeq = :OrderSeq
GROUP BY
        OrderSeq
;
EOQ;

                $amountData = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current();

                // 金額項目のみ -1 を掛け、入金処理日はシステム日時。
                $amount = array(
                        'ReceiptProcessDate' => date('Y-m-d H:i:s'),
                        'ReceiptAmount' => $amountData['ReceiptAmount'] * -1,
                        'CheckingUseAmount' => $amountData['CheckingUseAmount'] * -1,
                        'CheckingClaimFee' => $amountData['CheckingClaimFee'] * -1,
                        'CheckingDamageInterestAmount' => $amountData['CheckingDamageInterestAmount'] * -1,
                        'CheckingAdditionalClaimFee' => $amountData['CheckingAdditionalClaimFee'] * -1,
                        'RegistDate' => date('Y-m-d H:i:s'),
                        'RegistId' => $userId,
                        'UpdateDate' => date('Y-m-d H:i:s'),
                        'UpdateId' => $userId,
                        'Receipt_Note' => '',
                );
                // 取得データに金額項目をマージして新規登録
                $rcptSeq = $mdlrc->saveNew(array_merge($data, $amount));        // 2015/11/16 Y.Suzuki 会計対応 Mod

                // 2015/11/16 Y.Suzuki Add 会計対応 Stt
                $mdlatrc = new ATableReceiptControl($this->app->dbAdapter);
                // 入金取消した会計用のデータを取得
                $atdata = $mdlatrc->find($data['ReceiptSeq'])->current();

                // 2016/01/05 Y.Suzuki Add 入金取消前のデータを取得 Stt
                // 入金取消前立替クリアフラグ、入金取消前立替クリア日
                $sql = "SELECT ClearConditionForCharge, ClearConditionDate FROM T_PayingAndSales WHERE OrderSeq = :OrderSeq";
                $ri = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq));
                $clearConditionForCharge = $ri->current()['ClearConditionForCharge'];
                $clearConditionDate = $ri->current()['ClearConditionDate'];
                // 入金取消前立替処理－ステータス、入金取消前配送－着荷確認
                $sql = "SELECT Cnl_Status, Deli_ConfirmArrivalFlg FROM T_Order WHERE OrderSeq = :OrderSeq";
                $ri = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq));
                $cnlStatus = $ri->current()['Cnl_Status'];
                $deliConfirmArrivalFlg = $ri->current()['Deli_ConfirmArrivalFlg'];
                $candata = array(
                        'ReceiptSeq' => $rcptSeq,
                        'Rct_CancelFlg' => 1,
                        'Before_ClearConditionForCharge' => $clearConditionForCharge,
                        'Before_ClearConditionDate' => $clearConditionDate,
                        'Before_Cnl_Status' => $cnlStatus,
                        'Before_Deli_ConfirmArrivalFlg' => $deliConfirmArrivalFlg
                );
                // 2016/01/05 Y.Suzuki Add 入金取消前のデータを取得 End

                // 取得データに入金管理Seqをマージして新規登録
                $mdlatrc->saveNew(array_merge($atdata, $candata));      // 2016/01/05 Y.Suzuki 会計関連_入金取消対応 Mod
                // 2015/11/16 Y.Suzuki Add 会計対応 End

                // -------------------------
                // 雑損失データを取得
                // -------------------------
                $mdlsc = new TableSundryControl($this->app->dbAdapter);

                // 会計対象外データを取得
                // 直近の1件を取得(登録日で降順ソートしたLIMIT1を取得)
                $sql = "SELECT * FROM T_SundryControl WHERE SundryType = 1 AND SundryClass = 99 AND OrderSeq = :OrderSeq ORDER BY RegistDate DESC LIMIT 1";
                $data = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current();

                // データが取得出来た場合のみ、以下処理を行う。
                if (! empty($data)) {
                    // 注文Seqでｻﾏﾘして金額項目を取得
                    $sql = <<<EOQ
SELECT  OrderSeq
    ,   SUM(SundryAmount) AS SundryAmount
    ,   SUM(CheckingUseAmount) AS CheckingUseAmount
    ,   SUM(CheckingClaimFee) AS CheckingClaimFee
    ,   SUM(CheckingDamageInterestAmount) AS CheckingDamageInterestAmount
    ,   SUM(CheckingAdditionalClaimFee) AS CheckingAdditionalClaimFee
FROM    T_SundryControl
WHERE   SundryType = 1
AND     SundryClass = 99
AND     OrderSeq = :OrderSeq
GROUP BY
        OrderSeq
;
EOQ;

                    $amountData = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current();

                    // 取得データのSundryAmount が 0 の場合は処理しない
                    if ($amountData['SundryAmount'] > 0) {
                        // 金額項目のみ -1 を掛け、発生日はシステム日時
                        $amount = array(
                                'ProcessDate' => date('Y-m-d H:i:s'),
                                'SundryAmount' => $amountData['SundryAmount'] * -1,
                                'CheckingUseAmount' => $amountData['CheckingUseAmount'] * -1,
                                'CheckingClaimFee' => $amountData['CheckingClaimFee'] * -1,
                                'CheckingDamageInterestAmount' => $amountData['CheckingDamageInterestAmount'] * -1,
                                'CheckingAdditionalClaimFee' => $amountData['CheckingAdditionalClaimFee'] * -1,
                                'RegistId' => $userId,
                                'UpdateId' => $userId,
                        );
                        // 取得データに金額項目をマージして新規登録
                        $mdlsc->saveNew(array_merge($data, $amount));
                    }
                }

                // 会計対象データを取得
                // 直近の1件を取得(登録日で降順ソートしたLIMIT1を取得)
                $sql = "SELECT * FROM T_SundryControl WHERE SundryType = 1 AND SundryClass <> 99 AND OrderSeq = :OrderSeq ORDER BY RegistDate DESC LIMIT 1";
                $data = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current();

                // データが取得できた場合のみ、以下処理を行う。
                if (! empty($data)) {
                    // 注文Seqでｻﾏﾘして金額項目を取得
                    $sql = <<<EOQ
SELECT  OrderSeq
    ,   SUM(SundryAmount) AS SundryAmount
    ,   SUM(CheckingUseAmount) AS CheckingUseAmount
    ,   SUM(CheckingClaimFee) AS CheckingClaimFee
    ,   SUM(CheckingDamageInterestAmount) AS CheckingDamageInterestAmount
    ,   SUM(CheckingAdditionalClaimFee) AS CheckingAdditionalClaimFee
FROM    T_SundryControl
WHERE   SundryType = 1
AND     SundryClass <> 99
AND     OrderSeq = :OrderSeq
GROUP BY
        OrderSeq
;
EOQ;

                    $amountData = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current();

                    // 取得データのSundryAmount が 0 の場合は処理しない
                    if ($amountData['SundryAmount'] > 0) {
                        // 金額項目のみ -1 を掛け、発生日はシステム日時
                        $amount = array(
                                'ProcessDate' => date('Y-m-d H:i:s'),
                                'SundryAmount' => $amountData['SundryAmount'] * -1,
                                'CheckingUseAmount' => $amountData['CheckingUseAmount'] * -1,
                                'CheckingClaimFee' => $amountData['CheckingClaimFee'] * -1,
                                'CheckingDamageInterestAmount' => $amountData['CheckingDamageInterestAmount'] * -1,
                                'CheckingAdditionalClaimFee' => $amountData['CheckingAdditionalClaimFee'] * -1,
                                'RegistId' => $userId,
                                'UpdateId' => $userId,
                        );
                        // 取得データに金額項目をマージして新規登録
                        $mdlsc->saveNew(array_merge($data, $amount));
                    }
                }

                // -------------------------
                // 雑収入データを取得
                // -------------------------
                // 直近の1件を取得(登録日で降順ソートしたLIMIT1を取得)
                $sql = "SELECT * FROM T_SundryControl WHERE SundryType = 0 AND OrderSeq = :OrderSeq ORDER BY RegistDate DESC LIMIT 1";
                $data = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current();

                // データが取得できた場合のみ、以下処理を行う。
                if (! empty($data)) {
                    // 注文Seqでｻﾏﾘして金額項目を取得
                    $sql = <<<EOQ
SELECT  OrderSeq
    ,   SUM(SundryAmount) AS SundryAmount
    ,   SUM(CheckingUseAmount) AS CheckingUseAmount
    ,   SUM(CheckingClaimFee) AS CheckingClaimFee
    ,   SUM(CheckingDamageInterestAmount) AS CheckingDamageInterestAmount
    ,   SUM(CheckingAdditionalClaimFee) AS CheckingAdditionalClaimFee
FROM    T_SundryControl
WHERE   SundryType = 0
AND     OrderSeq = :OrderSeq
GROUP BY
        OrderSeq
;
EOQ;

                    $amountData = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current();

                    // 取得データのSundryAmount が 0 の場合は処理しない
                    if ($amountData['SundryAmount'] > 0) {
                        // 金額項目のみ -1 を掛け、発生日はシステム日時
                        $amount = array(
                                'ProcessDate' => date('Y-m-d H:i:s'),
                                'SundryAmount' => $amountData['SundryAmount'] * -1,
                                'CheckingUseAmount' => $amountData['CheckingUseAmount'] * -1,
                                'CheckingClaimFee' => $amountData['CheckingClaimFee'] * -1,
                                'CheckingDamageInterestAmount' => $amountData['CheckingDamageInterestAmount'] * -1,
                                'CheckingAdditionalClaimFee' => $amountData['CheckingAdditionalClaimFee'] * -1,
                                'RegistId' => $userId,
                                'UpdateId' => $userId,
                        );
                        // 取得データに金額項目をマージして新規登録
                        $mdlsc->saveNew(array_merge($data, $amount));
                    }
                }

                // -------------------------
                // 印紙代データを取得
                // -------------------------
                // 直近の1件を取得(登録日で降順ソートしたLIMIT1を取得)
                $sql = "SELECT * FROM T_StampFee WHERE OrderSeq = :OrderSeq ORDER BY RegistDate DESC LIMIT 1";
                $data = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current();

                // データが取得できた場合のみ、以下処理を行う。
                if (! empty($data)) {
                    // 注文Seqでｻﾏﾘして金額項目を取得
                    $sql = "SELECT OrderSeq , SUM(StampFee) AS StampFee FROM T_StampFee WHERE OrderSeq = :OrderSeq GROUP BY OrderSeq";
                    $amountData = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current();

                    // 取得データのStampFee が 0 の場合は処理しない
                    if ($amountData['StampFee'] > 0) {
                        // 金額項目のみ -1 を掛け、発生確定日はシステム日時
                        $amount = array(
                                'DecisionDate' => date('Y-m-d H:i:s'),
                                'StampFee' => $amountData['StampFee'] * -1,
                                'RegistId' => $userId,
                                'UpdateId' => $userId,
                        );
                        // 取得データに金額項目をマージして新規登録
                        $mdlsf = new TableStampFee($this->app->dbAdapter);
                        $mdlsf->saveNew(array_merge($data, $amount));
                    }
                }

                // 注文データを更新
                $mdlo = new TableOrder($this->app->dbAdapter);
                $mdlo->saveUpdateWhere(array('DataStatus' => 61, 'CloseReason' => 0, 'UpdateId' => $userId), array('P_OrderSeq' => $oseq));

                // 注文データを取得
                $orderData = ResultInterfaceToArray($mdlo->findOrder(array('P_OrderSeq' => $oseq)));

                // 取得件数分、ループする
                foreach ($orderData as $key => $value) {
                    // 立替・売上管理データを取得
                    $mdlpas = new TablePayingAndSales($this->app->dbAdapter);
                    $pasData = $this->app->dbAdapter->query("SELECT * FROM T_PayingAndSales WHERE OrderSeq = :OrderSeq")->execute(array(':OrderSeq' => $value['OrderSeq']))->current();

                    // 立替クリアフラグが上がっており、未立替　かつ　着荷確認済みでない　場合は、立替クリアフラグを落とす
                    if ($pasData['ClearConditionForCharge'] == 1 && $pasData['PayingControlStatus'] == 0 && $value['Deli_ConfirmArrivalFlg'] <> 1) {
                        // 立替・売上管理データを更新
                        $mdlpas->saveUpdate(array('ClearConditionForCharge' => 0, 'ClearConditionDate' => null, 'UpdateId' => $userId), $pasData['Seq']);

                        // 立替・売上管理_会計更新(売上ﾀｲﾌﾟ、売上日の初期化)
                        $row_pas = $this->app->dbAdapter->query(" SELECT Seq FROM T_PayingAndSales WHERE OrderSeq = :OrderSeq ")->execute(array(':OrderSeq' => $value['OrderSeq']))->current();
                        $mdlapas = new \models\Table\ATablePayingAndSales($this->app->dbAdapter);
                        $mdlapas->saveUpdate(array('ATUriType' => 99, 'ATUriDay' => '99999999'), $row_pas['Seq']);
                    }
                }

                // 請求管理更新
                // 請求額 = 請求残高へ更新する。
                $sql = <<<EOQ
UPDATE  T_ClaimControl
SET     ClaimedBalance = ClaimAmount
    ,   ReceiptAmountTotal = 0
    ,   SundryLossTotal = 0
    ,   SundryIncomeTotal = 0
    ,   CheckingClaimAmount = 0
    ,   CheckingUseAmount = 0
    ,   CheckingClaimFee = 0
    ,   CheckingDamageInterestAmount = 0
    ,   CheckingAdditionalClaimFee = 0
    ,   BalanceClaimAmount = ClaimAmount
    ,   BalanceUseAmount = UseAmountTotal
    ,   BalanceClaimFee = ClaimFee
    ,   BalanceDamageInterestAmount = DamageInterestAmount
    ,   BalanceAdditionalClaimFee = AdditionalClaimFee
    ,   UpdateDate = :UpdateDate
    ,   UpdateId = :UpdateId
    ,   LastReceiptSeq = :LastReceiptSeq
WHERE   OrderSeq = :OrderSeq
;
EOQ;

                // 更新実行
                $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq, ':UpdateId' => $userId, ':UpdateDate' => date('Y-m-d H:i:s'), ':LastReceiptSeq' => $rcptSeq));

                // 停滞アラートを更新
                $mdlsa = new TableStagnationAlert($this->app->dbAdapter);
                $mdlsa->saveUpdateWhere(array('AlertSign' => 0, 'UpdateId' => $userId), array('OrderSeq' => $oseq));

                try
                {
                    // 入金未確認ﾒｰﾙを送信する。
                    // 詳細が決定するまで保留。
                }
                catch(\Exception $e) {  }

                // 注文履歴登録用に親注文Seqから子注文Seqを再取得する。
                    $sql = <<<EOQ
SELECT  OrderSeq
FROM    T_Order
WHERE   P_OrderSeq = :P_OrderSeq
AND     Cnl_Status = 0
;
EOQ;

                $ri = $this->app->dbAdapter->query($sql)->execute(array(':P_OrderSeq' => $oseq));
                $rows = ResultInterfaceToArray($ri);

                // 注文履歴へ登録
                $history = new CoralHistoryOrder($this->app->dbAdapter);
                // 親注文Seqに紐づく子注文分、ループする。
                foreach ($rows as $row) {
                    // 注文履歴登録
                    $history->InsOrderHistory($row['OrderSeq'], 65, $userId);
                }
                $receiptMethod = $data['ReceiptClass'];
                $sql = "SELECT COUNT(*) AS cnt FROM T_ReceiptControl WHERE OrderSeq = :OrderSeq AND ReceiptClass IN (SELECT KeyCode FROM M_Code WHERE CodeId=163 AND Class3=1) ";
                $cnt = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current()['cnt'];
                if ($cnt > 0) {
                    $mdlao = new ATableOrder($this->app->dbAdapter);
                    $prm = array(
                        'ExtraPayType' => null,
                        'ExtraPayKey' => null,
                        'ExtraPayNote' => null,
                    );
                    $mdlao->saveUpdateExtraPay($prm, $oseq);
                    $sql = "SELECT Class1,Class3 FROM M_Code WHERE CodeId = 198 AND KeyCode = :KeyCode";
                    $ri = $this->app->dbAdapter->query($sql)->execute(array(':KeyCode' => $receiptMethod));
                    $class1 = $ri->current()['Class1'];
                    $paymentId = 0;
                    if (!is_null($class1)) {
                        $sqlSbps = "SELECT SbpsPaymentId FROM M_SbpsPayment WHERE PaymentName = :PaymentName";
                        $paymentId = $this->app->dbAdapter->query($sqlSbps)->execute(array(':PaymentName' => $class1))->current()['SbpsPaymentId'];
                    }

                    $sql = "";
                    $sql .= "SELECT s.* ";
                    $sql .= ", o.UseAmount ";
                    $sql .= ", o.ClaimSendingClass ";
                    $sql .= " FROM T_Order AS o";
                    $sql .= " INNER JOIN T_SiteSbpsPayment AS s ON s.SiteId = o.SiteId";
                    $sql .= " WHERE o.OrderSeq = ". $oseq;
                    $sql .= " AND s.PaymentId = ". $paymentId;
                    $orderSite = $this->app->dbAdapter->query($sql)->execute()->current();

                    // 立替・売上管理の取得
                    $mdlpas = new TablePayingAndSales($this->app->dbAdapter);
                    $mdlsp = new TableSystemProperty($this->app->dbAdapter);
                    $payingAndSales = $mdlpas->findPayingAndSales( array('OrderSeq' => $oseq) )->current();
                    $payingAndSalesSeq = $payingAndSales['Seq'];
                    $appSettlementFeeRate = ( ( !isset($orderSite['SettlementFeeRate']) || empty($orderSite['SettlementFeeRate']) ) ? 0 : $orderSite['SettlementFeeRate'] );
                    $settlementFee = floor( $orderSite['UseAmount'] * ($appSettlementFeeRate / 100) );
                    $cSClaimFeeDK = ( ( !isset($orderSite['ClaimFeeDK']) || empty($orderSite['ClaimFeeDK']) ) ? 0 : $orderSite['ClaimFeeDK'] );
                    $cSClaimFeeBS = ( ( !isset($orderSite['ClaimFeeBS']) || empty($orderSite['ClaimFeeBS']) ) ? 0 : $orderSite['ClaimFeeBS'] );
                    $claimFee = ( $orderSite['ClaimSendingClass'] == '11' ? $cSClaimFeeDK : $cSClaimFeeBS );
                    $claimFee = $mdlsp->getIncludeTaxAmount(date('Y-m-d'), $claimFee);
                    $chargeAmount = ( -1 * ( $settlementFee + $claimFee ) );

                    // 立替・売上管理の更新
                    $prm = array(
                        'UseAmount'               => $orderSite['UseAmount'],
                        'AppSettlementFeeRate'    => $appSettlementFeeRate,
                        'SettlementFee'           => $settlementFee,
                        'ClaimFee'                => $claimFee,
                        'ChargeAmount'            => $orderSite['UseAmount'] - ( $settlementFee + $claimFee ),
                        'ClearConditionForCharge' => 1,
                        'UpdateDate'              => date('Y-m-d H:i:s'),
                        'UpdateId'                => $userId,
                    );
                    $mdlpas->saveUpdate($prm, $payingAndSalesSeq);

                    $mdltbi = new TableToBackMypageIF($this->app->dbAdapter);
                    $mypageIFdata = array(
                        'Status' => 0,
                        'IFClass' => 2,
                        'IFData' => $rcptSeq,
                        'OrderSeq' => $oseq,
                        'RegistDate' => date('Y-m-d H:i:s'),
                        'UpdateDate' => date('Y-m-d H:i:s'),
                        'ValidFlg' => 1,
                    );
                    $mdltbi->saveNew($mypageIFdata);
                }
                // コミット
                $this->app->dbAdapter->getDriver()->getConnection()->commit();
                // 成功指示
                $msg = '1';
            }
        } catch (\Exception $e) {
            // ロールバック
            $this->app->dbAdapter->getDriver()->getConnection()->rollback();
            // エラー内容吐き出し
            $msg = $e->getMessage();
        }

        echo \Zend\Json\Json::encode(array('status' => $msg));
        return $this->response;
    }

    /**
     * (ajax)アラート取消処理
     */
    public function alertcancelAction()
    {
        try {
            $params = $this->getParams();

            $oseq = isset($params['oseq']) ? $params['oseq'] : 0;

            // ユーザーIDの取得
            $obj = new TableUser($this->app->dbAdapter);
            $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

            // 停滞アラート更新処理
            $mdlsa = new TableStagnationAlert($this->app->dbAdapter);
            $mdlsa->saveUpdateWhere(array('AlertSign' => 0, 'UpdateId' => $userId), array('AlertClass' => 3, 'OrderSeq' => $oseq));

            // 成功指示
            $msg = '1';
        } catch (\Exception $e) {
            // エラー内容吐き出し
            $msg = $e->getMessage();
        }

        echo \Zend\Json\Json::encode(array('status' => $msg));
        return $this->response;
    }

    /**
     * アプラスインポートフォームの表示
     */
    public function impapformAction()
    {
        return $this->view;
    }

    /**
     * ＠Paymentインポートフォームの表示
     */
    public function impatpayformAction()
    {
        return $this->view;
    }

    /**
     * ＠ペイメント（CB・OEM）インポートフォームの表示
     */
    public function impatpaycbformAction()
    {
        return $this->view;
    }

    /**
     * SMBC決済ステーションインポートフォームの表示
     */
    public function impsmbcformAction()
    {
        return $this->view;
    }

    /**
     * MTデータインポートフォームの表示
     */
    public function impmtformAction()
    {
        return $this->view;
    }

    /**
     * アプラス入金CSVインポート
     */
    public function impapAction()
    {
        $offset = 25;	// レコード中、バーコードの先頭の文字位置。
        $tmpName = $_FILES["cres"]["tmp_name"];

        // TODO: kashira - ファイルエンコーディング確認
        //BaseIOUtility::convertFileEncoding($tmpName, null, null, true);

        try {
            // ユーザーIDの取得
            $obj = new \models\Table\TableUser($this->app->dbAdapter);
            $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

            $stm = $this->app->dbAdapter->query($this->getBaseP_ReceiptControl());

            // SQL実行結果取得用のSQL
            $getretvalsql = " SELECT @po_ret_sts AS po_ret_sts, @po_ret_errcd AS po_ret_errcd, @po_ret_sqlcd AS po_ret_sqlcd, @po_ret_msg AS po_ret_msg ";

            $handle = @fopen($tmpName, "r");

            if (!$handle) {
                // ファイルオープン失敗
                $message = '<span style="font-size: 18px; color: red;">入金CSVファイルのオープンに失敗しました。<br />再試行してください。</span>';
            } else {
$this->app->logger->info(' impapAction start(' . $_FILES["cres"]["name"] . ' / filesize : ' . filesize($tmpName) . ') ');
                $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();

                // 入金ループ
                while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                    // レコード区分
                    $recordCode = (int)substr($data[0], 0, 1);

                    // データ識別
                    $dataCode = (int)substr($data[0], 1, 2);

                    // 印紙税フラッグ 2014.2.21
                    $stampFlag = (int)substr($data[0], 105, 1);

                    // データレコードで、かつ速報だったら処理する。
                    if ($recordCode == 2 && $dataCode == 1) {
                        $mdlo = new TableOrder($this->app->dbAdapter);
                        unset($udata);
                        unset($vdata);

                        // OrderSeqを取り出す。
                        $orderSeq = (int)(substr($data[0], 25, 11) . substr($data[0], 38, 2));

                        // 処理しようとしている注文データが入金待ち、一部入金、入金済みクローズであるかのチェック
                        $sql = "SELECT COUNT(*) AS cnt FROM T_Order WHERE (DataStatus IN (51, 61) OR (DataStatus = 91 AND CloseReason = 1)) AND Cnl_Status = 0 AND OrderSeq = :OrderSeq";
                        $checkCount = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $orderSeq))->current()['cnt'];
                        //クレジットカードで支払った注文のチェック
                        $sqlCredit = "SELECT COUNT(*) AS cnt FROM AT_Order WHERE ExtraPayType = 1 AND ExtraPayKey IS NOT NULL AND OrderSeq = :OrderSeq";
                        $checkCountCredit = $this->app->dbAdapter->query($sqlCredit)->execute(array(':OrderSeq' => $orderSeq))->current()['cnt'];

                        if ($checkCount <= 0 || $checkCountCredit >= 1) {
                            // エラーメッセージを入れておく。
                            $mdlv = new ViewOrderCustomer($this->app->dbAdapter);
                            $orderData = $mdlv->findOrderCustomerByOrderSeq($orderSeq)->current();

                            $vdata[0] = $orderData['OrderId'];
                            if ($checkCountCredit <= 0)
                            {
                                $vdata[6] = sprintf('入金待ちではない　<a href="rworder/detail/oseq/%d" target="_blank">→注文情報</a>', $orderSeq);
                            } else {
                                $vdata[6] = sprintf('クレジットカードで支払った注文　<a href="rworder/detail/oseq/%d" target="_blank">→注文情報</a>', $orderSeq);
                            }
                        } else if ($checkCount > 1) {
                            // エラーメッセージを入れておく。
                            $vdata[6] = '注文重複';				// これはありえない。
                        } else {
                            // 2015/10/19 Y.Suzuki Add 会計対応 Stt
                            // 注文SeqからOEMIDを取得し、コードマスタ（識別ID：153）と紐付けて銀行支店IDを取得する。
                            $orderData = $mdlo->find($orderSeq)->current();
                            // OEMID抜き出し
                            $oemId = (is_null($orderData['OemId'])) ? 0 : $orderData['OemId'];
                            $sql = "SELECT Class1 FROM M_Code WHERE CodeId = 153 AND KeyCode = :KeyCode";
                            $branchBankId = $this->app->dbAdapter->query($sql)->execute(array(':KeyCode' => $oemId))->current()['Class1'];
                            // 2015/10/19 Y.Suzuki Add 会計対応 End

                            // 入金前データステータスの取得
                            $datastatusBeforeReceipt = (int)$orderData['DataStatus'];

                            // エラーはないので確定する。
                            $prm = array(
                                    ':pi_receipt_amount'   => (int)substr($data[0], 56, 6),                     // 入金額（バーコード上の請求額）
                                    ':pi_order_seq'        => $orderSeq,                                        // 注文Seq
                                    ':pi_receipt_date'     => date('Ymd', strtotime(substr($data[0], 3, 8))),   // 入金日
                                    ':pi_receipt_class'    => 1,                                                // 入金方法（コンビニ固定）
                                    ':pi_branch_bank_id'   => $branchBankId,                                    // 銀行支店ID       // 2015/10/19 Y.Suzuki 会計対応 Mod
                                    ':pi_receipt_agent_id' => 1,                                                // 収納代行ID(1:アプラス)
                                    ':pi_deposit_date'     => null,                                             // 口座入金日
                                    ':pi_user_id'          => $userId,
                                    ':pi_receipt_note'     => null,
                            );

                            try {
                                $ri = $stm->execute($prm);

                                // SQL実行例外なしもエラー戻り値の時は例外をｽﾛｰ
                                $retval = $this->app->dbAdapter->query($getretvalsql)->execute(null)->current();
                                if ($retval['po_ret_sts'] != 0) {
                                    throw new \Exception($retval['po_ret_msg']);
                                }
                            }
                            catch(\Exception $e) { throw $e; }

                            // 未印刷の請求書印刷予約データを削除
                            $mdlch = new TableClaimHistory($this->app->dbAdapter);
                            $mdlch->deleteReserved($orderSeq, $userId);

                            // 請求額、差額を取得する。
                            $mdlcc = new TableClaimControl($this->app->dbAdapter);
                            $claimAmount = $mdlcc->findClaim(array("OrderSeq" => $orderSeq))->current()['ClaimAmount'];
                            $sagaku = $claimAmount - (int)substr($data[0], 56, 6);

                            // 立替・売上管理データ更新
                            $mdlps = new TablePayingAndSales($this->app->dbAdapter);
                            $mdld = new ViewDelivery($this->app->dbAdapter);
                            $deli = $mdld->findByOrderSeq($orderSeq)->current();
                            $orderData = $mdlo->find($orderSeq)->current();

                            // 入金正常ｸﾛｰｽﾞの場合、無条件に立替対象にする。
                            if ($orderData['DataStatus'] == 91 && $orderData['CloseReason'] == 1) {

                                $mdlapas = new \models\Table\ATablePayingAndSales($this->app->dbAdapter);
                                // 既に[立替条件クリアフラグ]が[1：条件をクリアしている]か？の取得
                                $isAlreadyClearCondition = $mdlps->IsAlreadyClearCondition($orderSeq);

                                $mdlps->clearConditionForCharge($orderSeq, 1, $userId);

                                if (!$isAlreadyClearCondition) {
                                    $row_pas = $this->app->dbAdapter->query(" SELECT Seq FROM T_PayingAndSales WHERE OrderSeq = :OrderSeq "
                                        )->execute(array(':OrderSeq' => $orderSeq))->current();

                                    // 入金により立替条件クリアフラグが１化されるとき => '2:入金'として更新(顧客入金日をセットする)
                                    $mdlapas->saveUpdate(array('ATUriType' => 2, 'ATUriDay' => date('Ymd', strtotime(substr($data[0], 3, 8)))), $row_pas['Seq']);
                                }
                            }

                            // 印紙代発生の有無
                            //if ($rcptAmount >= 31500)
                            // 2014.2.21 印紙税の発生有無を金額依存では無く、バーコードの印紙税フラグに依存する
                            if ($stampFlag == 1)
                            {
                                $stampFee['OrderSeq']       = $orderSeq;
                                $stampFee['DecisionDate']   = date('Y-m-d');
                                $stampFee['StampFee']       = 200;
                                $stampFee['ClearFlg']       = 0;
                                $stampFee['CancelFlg']      = 0;

                                $mdlstmp = new TableStampFee($this->app->dbAdapter);
                                $mdlstmp->saveNew($stampFee);
                            }

                            $vdata[7] = $claimAmount;
                            $vdata[8] = $sagaku;

                            $vdata[0] = $orderData['OrderId'];
                            $vdata[1] = "";
                            $vdata[2] = (int)substr($data[0], 56, 6);
                            $vdata[3] = "";
                            $vdata[4] = date('Ymd', strtotime(substr($data[0], 3, 8)));
                            $vdata[5] = "";

                            // 作成した入金管理Seqを取得する。（1注文に対する入金は複数存在するので、注文に紐づく入金のMAX値を取得）
                            $sql = "SELECT MAX(ReceiptSeq) AS ReceiptSeq FROM T_ReceiptControl WHERE OrderSeq = :OrderSeq GROUP BY OrderSeq";
                            $rcptSeq = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $orderSeq))->current()['ReceiptSeq'];

                            if ($datastatusBeforeReceipt != 91 && $orderData['DataStatus'] == 91) {// [91：クローズ]からの入金はメール対象から除外
                            try {
                                // 入金確認メール送信
                                $mail = new CoralMail($this->app->dbAdapter, $this->app->mail['smtp']);
                                $mail->SendRcptConfirmMail($rcptSeq, $userId);
                            } catch(\Exception $e) {
                                // エラーメッセージを入れておく。
                                $vdata[6] = 'メール送信NG';
                            }
                            }
                            // 注文履歴へ登録
                            $history = new CoralHistoryOrder($this->app->dbAdapter);
                            $history->InsOrderHistory($orderSeq, 61, $userId);

                            // 2016/01/05 Y.Suzuki Mod 会計関連_入金取消対応（全ての更新処理が完了したあとで入金管理_会計のINSERT処理をする必要が発生したため、位置を移動） Stt
                            // 2015/10/19 Y.Suzuki Add 会計対応 Stt
                            // 会計用項目をINSERT
                            // 2015/12/21 Y.Suzuki Del 上（1286～1287行目）で取得しているので削除 Stt
//                             // 入金管理Seqの取得（複数存在する場合を考慮して、MAX値を取得する）
//                             $sql = "SELECT MAX(ReceiptSeq) AS ReceiptSeq FROM T_ReceiptControl WHERE OrderSeq = :OrderSeq";
//                             $rcptSeq = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $orderSeq))->current()['ReceiptSeq'];
                            // 2015/12/21 Y.Suzuki Del 上（1286～1287行目）で取得しているので削除 End

                            // 2016/01/05 Y.Suzuki Add 入金取消前のデータを取得 Stt
                            // 入金取消前立替クリアフラグ、入金取消前立替クリア日
                            $sql = "SELECT ClearConditionForCharge, ClearConditionDate FROM T_PayingAndSales WHERE OrderSeq = :OrderSeq";
                            $ri = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq));
                            $clearConditionForCharge = $ri->current()['ClearConditionForCharge'];
                            $clearConditionDate = $ri->current()['ClearConditionDate'];
                            // 入金取消前キャンセル－ステータス、入金取消前配送－着荷確認
                            $sql = "SELECT Cnl_Status, Deli_ConfirmArrivalFlg FROM T_Order WHERE OrderSeq = :OrderSeq";
                            $ri = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $orderSeq));
                            $cnlStatus = $ri->current()['Cnl_Status'];
                            $deliConfirmArrivalFlg = $ri->current()['Deli_ConfirmArrivalFlg'];
                            // 2016/01/05 Y.Suzuki Add 入金取消前のデータを取得 End

                            $atdata = array(
                                    'ReceiptSeq' => $rcptSeq,
                                    'AccountNumber' => null,
                                    'ClassDetails' => null,
                                    'BankFlg' => 1,     // 銀行入金区分：1（入金取り込みなので 1 固定）
                                    // 2016/01/05 Y.Suzuki Add 会計対応_入金取消関連（INSERT項目追加） Stt
                                    'Before_ClearConditionForCharge' => $clearConditionForCharge,   // 入金取消前立替クリアフラグ
                                    'Before_ClearConditionDate' => $clearConditionDate,             // 入金取消前立替クリア日
                                    'Before_Cnl_Status' => $cnlStatus,                              // 入金取消前立替処理－ステータス
                                    'Before_Deli_ConfirmArrivalFlg' => $deliConfirmArrivalFlg,      // 入金取消前配送－着荷確認
                                    // 2016/01/05 Y.Suzuki Add 会計対応_入金取消関連（INSERT項目追加） End
                            );

                            $mdlatrc = new ATableReceiptControl($this->app->dbAdapter);
                            $mdlatrc->saveNew($atdata);
                            // 2015/10/19 Y.Suzuki Add 会計対応 End
                            // 2016/01/05 Y.Suzuki Mod 会計関連_入金取消対応（全ての更新処理が完了したあとで入金管理_会計のINSERT処理をする必要が発生したため、位置を移動） End
                        }

                        $resultViewData[] = $vdata;
                    } else if ($recordCode == 2 && $dataCode == 3) {
                        // 取消レコード
                        $mdlv = new ViewOrderCustomer($this->app->dbAdapter);
                        unset($vdata);

                        // OrderSeqを取り出す。
                        $orderSeq = (int)(substr($data[0], 25, 11) . substr($data[0], 38, 2));

                        // OrderDataを求める。
                        $orderData = $mdlv->findOrderCustomerByOrderSeq($orderSeq)->current();

                        $vdata[0] = $orderData['OrderId'];
                        $vdata[1] = $orderData['NameKj'];

                        $resultViewCancelData[] = $vdata;

                        // 取消データの場合は停滞アラートを作成する
                        $mdlsa = new TableStagnationAlert($this->app->dbAdapter);
                        $data = array(
                                'AlertClass' => 3,                                      // 停滞アラート区分(3：入金取消)※ 仮の区分
                                'AlertSign' => 1,                                       // アラートサイン(1：アラート)
                                'OrderSeq' => $orderSeq,                                // 注文SEQ
                                'StagnationDays' => NULL,                               // 停滞期間日数
                                'EnterpriseId' => $orderData['EnterpriseId'],           // 加盟店ID
                                'AlertJudgDate' => date('Y-m-d H:i:s'),                 // アラート抽出日時
                                'RegistId' => $userId,                                  // 登録者
                                'UpdateId' => $userId,                                  // 更新者
                                'ValidFlg' => 1,                                        // 有効フラグ
                        );
                        // 新規登録
                        $mdlsa->saveNew($data);
                    }
                }

                fclose($handle);

                $this->app->dbAdapter->getDriver()->getConnection()->commit();

                $message = sprintf("アプラス入金ファイル　「%s」　をインポートしました。", f_e($_FILES["cres"]["name"]));
$this->app->logger->info(' impapAction completed(' . $message . ') ');
            }
        } catch(\Exception $e) {
            $message = $e->getMessage();
            $this->app->dbAdapter->getDriver()->getConnection()->rollBack();
$this->app->logger->info(' impapAction error(' . $message . ') ');
        }

        $this->view->assign('message', $message);
        $this->view->assign('data', $resultViewData);
        $this->view->assign('canceldata', $resultViewCancelData);

        return $this->view;
    }

    /**
     * ＠Paymentインポート
     */
    public function impatpayAction()
    {
        $reader = new Ini();

        // サマリー変数定義と初期化
        $classSummary = array (
                'recordCount'   => 0,   // 取込件数
                'paymentAmount' => 0,   // 支払金額総額
                'claimAmount'   => 0,   // 請求金額総額
                'sagakuAmount'  => 0,   // 差額金額総額
        );
        $summary = array(
                1 => $classSummary,     // 1:コンビニ
                2 => $classSummary,     // 2:郵便局
                3 => $classSummary,     // 3:銀行
        );

        // config.ini からシステムプロパティーに移動したので、システムプロパティから取得する。
//         $atPaymentConfig = $this->app->config['at_payment'];
        $sql = "select * from T_SystemProperty where Module = :Module and Category = :Category and Name = :Name";
        $ri1 = $this->app->dbAdapter->query($sql)->execute(array(':Module' => 'cbadmin', ':Category' => 'at_payment', ':Name' => 'corp_code'))->current();
        $ri2 = $this->app->dbAdapter->query($sql)->execute(array(':Module' => 'cbadmin', ':Category' => 'at_payment', ':Name' => 'cust_num'))->current();
        $ri3 = $this->app->dbAdapter->query($sql)->execute(array(':Module' => 'cbadmin', ':Category' => 'at_payment', ':Name' => 'enable_stamp_fee'))->current();
        $atPaymentConfig = array();
        // 取得データを配列に格納
        $atPaymentConfig['corp_code'] = $ri1['PropValue'];
        $atPaymentConfig['cust_num'] = $ri2['PropValue'];
        $atPaymentConfig['enable_stamp_fee'] = $ri3['PropValue'];

        $mdlo = new TableOrder($this->app->dbAdapter);
        $tmpName = $_FILES["cres"]["tmp_name"];

        try {
            // ユーザーIDの取得
            $obj = new \models\Table\TableUser($this->app->dbAdapter);
            $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

            $stm = $this->app->dbAdapter->query($this->getBaseP_ReceiptControl());

            // SQL実行結果取得用のSQL
            $getretvalsql = " SELECT @po_ret_sts AS po_ret_sts, @po_ret_errcd AS po_ret_errcd, @po_ret_sqlcd AS po_ret_sqlcd, @po_ret_msg AS po_ret_msg ";

$this->app->logger->info(' impatpayAction start(' . $_FILES["cres"]["name"] . ' / filesize : ' . filesize($tmpName) . ') ');
            $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();

            // 取込み可能ファイルか？の確認
            $mdlirf = new TableImportedReceiptFile($this->app->dbAdapter);
            if (!$mdlirf->isCanImport(0, $_FILES["cres"]["name"])) {
                throw new \Exception('既に取込み済みのファイルです。');
            }

            $atp = new CoralExternalAtPayment($tmpName, $atPaymentConfig['corp_code'], $atPaymentConfig['cust_num']);

            $datas = $atp->getApDatas();
            $edatas = $atp->getApErrorDatas();
            $cdatas = $atp->getApCancelDatas();

            foreach ($datas as $data) {
                unset($vdata);
                unset($udata);

                $orderId = "AK" . sprintf("%08d", $data->AtobaraiOrderId);

                // 処理しようとしている注文データが入金待ち、一部入金、入金済みクローズであるかのチェック
                $sql = "SELECT COUNT(*) AS cnt FROM T_Order WHERE (DataStatus IN (51, 61) OR (DataStatus = 91 AND CloseReason = 1)) AND Cnl_Status = 0 AND OrderId = :OrderId";
                $checkCount = $this->app->dbAdapter->query($sql)->execute(array(':OrderId' => $orderId))->current()['cnt'];
                //クレジットカードで支払った注文のチェック
                $sqlCredit = "SELECT COUNT(*) AS cnt FROM AT_Order AORD INNER JOIN T_Order ORD ON (ORD.OrderSeq = AORD.OrderSeq) 
                                        WHERE AORD.ExtraPayType = 1 AND AORD.ExtraPayKey IS NOT NULL AND ORD.OrderId = :OrderId";
                $checkCountCredit = $this->app->dbAdapter->query($sqlCredit)->execute(array(':OrderId' => $orderId))->current()['cnt'];

                if ($checkCount <= 0 || $checkCountCredit >= 1)
                {
                    // エラーメッセージを入れておく。
                    $mdlv = new ViewOrderCustomer($this->app->dbAdapter);
                    $orderData = $mdlv->findOrderCustomer(array("OrderId" => $orderId))->current();

                    if (!$orderData) {
                        $vdata[0] = "該当無し";
                        $vdata[1] = $data->PayWayType;
                        $vdata[6] = sprintf('該当注文無し　：　指定された注文ID = %s', $orderId);
                    } else {
                        $vdata[0] = $orderData['OrderId'];
                        $vdata[1] = $data->PayWayType;
                        if ($checkCountCredit <= 0) {
                            $vdata[6] = sprintf('入金待ちではない　<a href="rworder/detail/oseq/%d" target="_blank">→注文情報</a>', $orderData['OrderSeq']);
                        } else {
                            $vdata[6] = sprintf('クレジットカードで支払った注文　<a href="rworder/detail/oseq/%d" target="_blank">→注文情報</a>', $orderData['OrderSeq']);
                        }

                        $this->updateIncreNote($orderData['OrderSeq'], $userId);
                    }
                    $resultViewData[] = $vdata;
                } else if ($checkCount > 1) {

                    throw new \Exception(sprintf('複数該当　（指定された注文ID = %s）', $orderId));
                } else {
                    // OrderDataを求める。
                    $sql = "SELECT * FROM T_Order WHERE (DataStatus IN (51, 61) OR (DataStatus = 91 AND CloseReason = 1)) AND OrderId = :OrderId";
                    $orderData = $this->app->dbAdapter->query($sql)->execute(array(':OrderId' => $orderId))->current();

                    // 2015/10/19 Y.Suzuki Add 会計対応 Stt
                    // 注文SeqからOEMIDを取得し、コードマスタ（識別ID：153）と紐付けて銀行支店IDを取得する。
                    // OEMID抜き出し
                    $oemId = (is_null($orderData['OemId'])) ? 0 : $orderData['OemId'];
                    $sql = "SELECT Class1 FROM M_Code WHERE CodeId = 153 AND KeyCode = :KeyCode";
                    $branchBankId = $this->app->dbAdapter->query($sql)->execute(array(':KeyCode' => $oemId))->current()['Class1'];
                    // 2015/10/19 Y.Suzuki Add 会計対応 End

                    // 入金前データステータスの取得
                    $datastatusBeforeReceipt = (int)$orderData['DataStatus'];

                    $prm = array(
                            ':pi_receipt_amount'   => $data->PaymentAmount,                             // 入金額（バーコード上の請求額）
                            ':pi_order_seq'        => $orderData['OrderSeq'],                           // 注文Seq
                            ':pi_receipt_date'     => date('Ymd', strtotime($data->CustPaymentDate)),   // 入金日
                            ':pi_receipt_class'    => $data->PayWayType,                                // 入金方法
                            ':pi_branch_bank_id'   => $branchBankId,                                    // 銀行支店ID       // 2015/10/19 Y.Suzuki 会計対応 Mod
                            ':pi_receipt_agent_id' => 2,                                                // 収納代行ID(2:@ﾍﾟｲﾒﾝﾄ)
                            ':pi_deposit_date'     => (IsValidDate($data->AccountPaymentDate)) ? $data->AccountPaymentDate : null,           // 口座入金日       // 2015/12/09 Y.Suzuki 会計対応 Mod
                            ':pi_user_id'          => $userId,
                            ':pi_receipt_note'     => null,
                    );

                    try {
                        $ri = $stm->execute($prm);

                        // SQL実行例外なしもエラー戻り値の時は例外をｽﾛｰ
                        $retval = $this->app->dbAdapter->query($getretvalsql)->execute(null)->current();
                        if ($retval['po_ret_sts'] != 0) {
                            throw new \Exception($retval['po_ret_msg']);
                        }
                    }
                    catch(\Exception $e) { throw $e; }

                    // 未印刷の請求書印刷予約データを削除
                    $mdlch = new TableClaimHistory($this->app->dbAdapter);
                    $mdlch->deleteReserved($orderData['OrderSeq'], $userId);

                    // 請求額、差額を取得する。
                    $mdlcc = new TableClaimControl($this->app->dbAdapter);
                    $claimAmount = $mdlcc->findClaim(array("OrderSeq" => $orderData['OrderSeq']))->current()['ClaimAmount'];
                    $sagaku = $claimAmount - $data->PaymentAmount;

                    // 立替・売上管理データ更新
                    $mdlps = new TablePayingAndSales($this->app->dbAdapter);
                    $mdld = new ViewDelivery($this->app->dbAdapter);
                    $deli = $mdld->findByOrderSeq($orderData['OrderSeq'])->current();
                    $orderData = $mdlo->findOrder(array('OrderId' => $orderId))->current();

                    // 入金済み正常ｸﾛｰｽﾞの場合、立替対象にする。
                    if ($orderData['DataStatus'] == 91 && $orderData['CloseReason'] == 1) {
                        $mdlapas = new \models\Table\ATablePayingAndSales($this->app->dbAdapter);
                        // 既に[立替条件クリアフラグ]が[1：条件をクリアしている]か？の取得
                        $isAlreadyClearCondition = $mdlps->IsAlreadyClearCondition($orderData['OrderSeq']);

                        $mdlps->clearConditionForCharge($orderData['OrderSeq'], 1, $userId);

                        if (!$isAlreadyClearCondition) {
                            $row_pas = $this->app->dbAdapter->query(" SELECT Seq FROM T_PayingAndSales WHERE OrderSeq = :OrderSeq "
                                )->execute(array(':OrderSeq' => $orderData['OrderSeq']))->current();

                            // 入金により立替条件クリアフラグが１化されるとき => '2:入金'として更新(顧客入金日をセットする)
                            $mdlapas->saveUpdate(array('ATUriType' => 2, 'ATUriDay' => date('Ymd', strtotime($data->CustPaymentDate))), $row_pas['Seq']);
                        }
                    }

                    // 印紙代発生の有無
                    // 2014.2.21　印紙税発生の条件を金額依存では無くバーコードに含まれるフラッグに依存するように変更
                    //if ($atPaymentConfig->enable_stamp_fee && $data->PaymentAmount >= 31500 && $data->PayWayType == 1)
                    if ($atPaymentConfig['enable_stamp_fee'] && $data->StampFlag)
                    {
                    	// 支払方法区分２取得
					    $sql = "SELECT cd.Class2 as Class2
					              FROM T_ReceiptControl as rc 
					              LEFT JOIN M_Code as cd ON cd.CodeId = 198 AND cd.KeyCode = rc.ReceiptClass 
					             WHERE rc.OrderSeq = :OrderSeq
					             ORDER BY rc.ReceiptSeq DESC LIMIT 1;";
					    $Class2 = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $orderData['OrderSeq']))->current()['Class2'];
					    //支払方法区分2が0:印紙代対象
					    if($Class2==0){
                            $stampFee['OrderSeq']       = $orderData['OrderSeq'];
                            $stampFee['DecisionDate']   = date('Y-m-d');
                            $stampFee['StampFee']       = 200;
                            $stampFee['ClearFlg']       = 0;
                            $stampFee['CancelFlg']      = 0;

                            $mdlstmp = new TableStampFee($this->app->dbAdapter);
                            $mdlstmp->saveNew($stampFee);
                        }
                    }
                    $vdata[7] = $claimAmount;
                    $vdata[8] = $sagaku;

                    $vdata[0] = $orderData['OrderId'];
                    $vdata[1] = $data->PayWayType;
                    $vdata[2] = $data->PaymentAmount;
                    $vdata[3] = "";
                    $vdata[4] = date('Ymd', strtotime($data->CustPaymentDate));
                    $vdata[5] = "";

                    // 作成した入金管理Seqを取得する。（1注文に対する入金は複数存在するので、注文に紐づく入金のMAX値を取得）
                    $sql = "SELECT MAX(ReceiptSeq) AS ReceiptSeq FROM T_ReceiptControl WHERE OrderSeq = :OrderSeq GROUP BY OrderSeq";
                    $rcptSeq = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $orderData['OrderSeq']))->current()['ReceiptSeq'];

                    if ($datastatusBeforeReceipt != 91) {// [91：クローズ]からの入金はメール対象から除外

                        // NOTE : 本箇所でのﾒｰﾙ送信は行わない(ﾊﾞｯﾁによるｽｹｼﾞｭｰﾘﾝｸﾞ送信)
                        // T_ReceiptControl.MailFlgの[0：未送信(送信対象)]化
                        $this->app->dbAdapter->query(" UPDATE T_ReceiptControl SET MailFlg = 0 WHERE ReceiptSeq = :ReceiptSeq ")->execute(array(':ReceiptSeq' => $rcptSeq));
                    }

                    // 注文履歴へ登録
                    $history = new CoralHistoryOrder($this->app->dbAdapter);
                    $history->InsOrderHistory($orderData['OrderSeq'], 61, $userId);

                    // 2016/01/05 Y.Suzuki Mod 会計関連_入金取消対応（全ての更新処理が完了したあとで入金管理_会計のINSERT処理をする必要が発生したため、位置を移動） Stt
                    // 2015/10/19 Y.Suzuki Add 会計対応 Stt
                    // 会計用項目をINSERT
                    // 2016/01/05 Y.Suzuki Del 上（1531～1532行目）で取得しているので削除 Stt
//                     // 入金管理Seqの取得（複数存在する場合を考慮して、MAX値を取得する）
//                     $sql = "SELECT MAX(ReceiptSeq) AS ReceiptSeq FROM T_ReceiptControl WHERE OrderSeq = :OrderSeq";
//                     $rcptSeq = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $orderData['OrderSeq']))->current()['ReceiptSeq'];
                    // 2016/01/05 Y.Suzuki Del 上（1531～1532行目）で取得しているので削除 End

                    // 2016/01/05 Y.Suzuki Add 更新後のデータを取得 Stt
                    // 入金取消前立替クリアフラグ、入金取消前立替クリア日
                    $sql = "SELECT ClearConditionForCharge, ClearConditionDate FROM T_PayingAndSales WHERE OrderSeq = :OrderSeq";
                    $ri = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq));
                    $clearConditionForCharge = $ri->current()['ClearConditionForCharge'];
                    $clearConditionDate = $ri->current()['ClearConditionDate'];
                    // 入金取消前立替処理－ステータス、入金取消前配送－着荷確認
                    $sql = "SELECT Cnl_Status, Deli_ConfirmArrivalFlg FROM T_Order WHERE OrderSeq = :OrderSeq";
                    $ri = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $orderData['OrderSeq']));
                    $cnlStatus = $ri->current()['Cnl_Status'];
                    $deliConfirmArrivalFlg = $ri->current()['Deli_ConfirmArrivalFlg'];
                    // 2016/01/05 Y.Suzuki Add 更新後のデータを取得 End

                    $atdata = array(
                            'ReceiptSeq' => $rcptSeq,
                            'AccountNumber' => null,
                            'ClassDetails' => null,
                            'BankFlg' => 1,     // 銀行入金区分：1（入金取り込みなので 1 固定）
                            // 2016/01/05 Y.Suzuki Add 会計対応_入金取消関連（INSERT項目追加） Stt
                            'Before_ClearConditionForCharge' => $clearConditionForCharge,   // 入金取消前立替クリアフラグ
                            'Before_ClearConditionDate' => $clearConditionDate,             // 入金取消前立替クリア日
                            'Before_Cnl_Status' => $cnlStatus,                              // 入金取消前立替処理－ステータス
                            'Before_Deli_ConfirmArrivalFlg' => $deliConfirmArrivalFlg,      // 入金取消前配送－着荷確認
                            // 2016/01/05 Y.Suzuki Add 会計対応_入金取消関連（INSERT項目追加） End
                            'KeyInfo' => $data->KeyInfo,            // 確報取込対応(キー情報)
                            'SokuhoRegistDate' => date('Y-m-d'),    // 確報取込対応(速報データ取込日)
                            'KakuhoRegistDate' => null,             // 確報取込対応(確報データ取込日)
                    );

                    $mdlatrc = new ATableReceiptControl($this->app->dbAdapter);
                    $mdlatrc->saveNew($atdata);
                    // 2015/10/19 Y.Suzuki Add 会計対応 End
                    // 2016/01/05 Y.Suzuki Mod 会計関連_入金取消対応（全ての更新処理が完了したあとで入金管理_会計のINSERT処理をする必要が発生したため、位置を移動） End

                    // サマリー変数更新
                    $summary[$data->PayWayType]['recordCount']   += 1;
                    $summary[$data->PayWayType]['paymentAmount'] += $data->PaymentAmount;
                    $summary[$data->PayWayType]['claimAmount']   += $claimAmount;
                    $summary[$data->PayWayType]['sagakuAmount']  += $sagaku;
                }
            }

            // 取消対象データが存在する場合は、以下処理を行う。
            if (! empty($cdatas)) {
                // 取消対象データ数分、ループする。
                foreach ($cdatas as $data) {
                    unset($udata);

                    $orderId = "AK" . sprintf("%08d", $data->AtobaraiOrderId);

                    // OrderDataを求める。
                    $orderData = $mdlo->findOrder(array('OrderId' => $orderId))->current();

                    // アラート作成
                    $mdlsa = new TableStagnationAlert($this->app->dbAdapter);
                    $udata = array(
                            'AlertClass' => 3,                                      // 停滞アラート区分(3：入金取消)※ 仮の区分
                            'AlertSign' => 1,                                       // アラートサイン(1：アラート)
                            'OrderSeq' => $orderData['OrderSeq'],                   // 注文SEQ
                            'StagnationDays' => NULL,                               // 停滞期間日数
                            'EnterpriseId' => $orderData['EnterpriseId'],           // 加盟店ID
                            'AlertJudgDate' => date('Y-m-d H:i:s'),                 // アラート抽出日時
                            'RegistId' => $userId,                                  // 登録者
                            'UpdateId' => $userId,                                  // 更新者
                            'ValidFlg' => 1,                                        // 有効フラグ
                    );
                    // 新規登録
                    $mdlsa->saveNew($udata);

                    $data->AtobaraiOrderId = $orderId;
                }
            }

            // T_ImportedReceiptFile(取込済み入金ファイル)登録
            $mdlirf->saveNew(array(
                    'ReceiptProcessClass' => 0, // 0：＠Payment
                    'FileName' => $_FILES["cres"]["name"],
                    'RegistId' => $userId,
                    'UpdateId' => $userId,
                    'ValidFlg' => 1,
                ));

            // その他(入金処理対象外データ)
            $summary2 = array(
                    1 => $classSummary, // 1:(確報)コンビニ
                    2 => $classSummary, // 2:(確報)郵便局
                    3 => $classSummary, // 3:(確報)銀行
                    9 => $classSummary, // 9:(速報)逆サイド
            );

            foreach ($edatas as $row) {
                $type = ($row->DataKind == 2) ? $row->PayWayType : 9;   // 確報時=>PayWayType／速報時=>[9]
                $summary2[$type]['recordCount']   += 1;
                $summary2[$type]['paymentAmount'] += $row->PaymentAmount;
                // ｺﾝﾋﾞﾆ確報取込対応
                if ($type == 1) {
                    $this->app->dbAdapter->query(" UPDATE AT_ReceiptControl SET KakuhoRegistDate = :KakuhoRegistDate WHERE KeyInfo = :KeyInfo "
                        )->execute(array(':KakuhoRegistDate' => date('Y-m-d'), ':KeyInfo' => $row->KeyInfo));
                }
            }

            $this->app->dbAdapter->getDriver()->getConnection()->commit();

            $message = sprintf("＠Payment入金ファイル　「%s」　をインポートしました。", f_e($_FILES["cres"]["name"]));
$this->app->logger->info(' impatpayAction completed(' . $message . ') ');

            $this->view->assign('datas', $resultViewData);
            $this->view->assign('cdatas', $cdatas);
            $this->view->assign('summary', $summary);
            $this->view->assign('summary2', $summary2);

        } catch(\Exception $e) {
            $this->app->dbAdapter->getDriver()->getConnection()->rollBack();
            $message = '<h4 style="color:red;">処理中にエラーが発生したため全件の入金をキャンセルしました。<br/>詳細　：　' . $e->getMessage() . "</h4>";
$this->app->logger->info(' impatpayAction error(' . $message . ') ');
        }

        $this->view->assign('message', $message);

        return $this->view;
    }

    /**
     * ＠ペイメント（CB・OEM）インポート
     */
    public function impatpaycbAction()
    {
        $claimAccountLogic = new \models\Logic\LogicOemClaimAccount($this->app->dbAdapter);

        $reader = new Ini();

        // サマリー変数定義と初期化
        $classSummary = array (
                'recordCount'   => 0,   // 取込件数
                'paymentAmount' => 0,   // 支払金額総額
                'claimAmount'   => 0,   // 請求金額総額
                'sagakuAmount'  => 0,   // 差額金額総額
        );
        $summary = array(
            1 => $classSummary,         // 1:コンビニ
            2 => $classSummary,         // 2:郵便局
            3 => $classSummary,         // 3:銀行
        );

        // config.ini からシステムプロパティーに移動したので、システムプロパティから取得する。
//         $atPaymentConfig = $this->app->config['at_payment'];
        $sql = "select * from T_SystemProperty where Module = :Module and Category = :Category and Name = :Name";
        $ri1 = $this->app->dbAdapter->query($sql)->execute(array(':Module' => 'cbadmin', ':Category' => 'at_payment', ':Name' => 'corp_code'))->current();
        $ri2 = $this->app->dbAdapter->query($sql)->execute(array(':Module' => 'cbadmin', ':Category' => 'at_payment', ':Name' => 'cust_num'))->current();
        $ri3 = $this->app->dbAdapter->query($sql)->execute(array(':Module' => 'cbadmin', ':Category' => 'at_payment', ':Name' => 'enable_stamp_fee'))->current();
        $atPaymentConfig = array();
        // 取得データを配列に格納
        $atPaymentConfig['corp_code'] = $ri1['PropValue'];
        $atPaymentConfig['cust_num'] = $ri2['PropValue'];
        $atPaymentConfig['enable_stamp_fee'] = $ri3['PropValue'];

        $mdlo = new TableOrder($this->app->dbAdapter);
        $tmpName = $_FILES["cres"]["tmp_name"];

        try {
            // ユーザーIDの取得
            $obj = new \models\Table\TableUser($this->app->dbAdapter);
            $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

            $stm = $this->app->dbAdapter->query($this->getBaseP_ReceiptControl());

            // SQL実行結果取得用のSQL
            $getretvalsql = " SELECT @po_ret_sts AS po_ret_sts, @po_ret_errcd AS po_ret_errcd, @po_ret_sqlcd AS po_ret_sqlcd, @po_ret_msg AS po_ret_msg ";

$this->app->logger->info(' impatpaycbAction start(' . $_FILES["cres"]["name"] . ' / filesize : ' . filesize($tmpName) . ') ');
            $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();

            // 取込み可能ファイルか？の確認
            $mdlirf = new TableImportedReceiptFile($this->app->dbAdapter);
            if (!$mdlirf->isCanImport(1, $_FILES["cres"]["name"])) {
                throw new \Exception('既に取込み済みのファイルです。');
            }

            $atp = new CoralExternalAtPaymentEx($tmpName);
            // 加入者固有コード等による制限を掛けるには以下のコメントアウトを解除する(2014.9.25 eda)
            //$atp
            //	->setValidCvsSubscriberCode($claimAccountLogic->getAllCvsSubscriberCodes())
            //	->setValidYuchoSubscriberData($claimAccountLogic->getAllYuchoSubscriberDatas());

            $datas = $atp->getApDatas();
            $edatas = $atp->getApErrorDatas();
            $cdatas = $atp->getApCancelDatas();

            /** @var CoralExternalAtPaymentDataEx */
            $data = null;
            foreach ($datas as $data) {
                $vdata = array();
                $udata = array();

                // キー情報から得たOrderSeqで注文の存在を確認
                $orderRow = $mdlo->find($data->AtobaraiOrderSeq)->current();

                $checkCount = 0;
                $orderId = null;
                if($orderRow) {
                    $orderId = $orderRow['OrderId'];

                    // 処理しようとしている注文データが入金待ち、一部入金、入金済みクローズであるかのチェック
                    $sql = "SELECT COUNT(*) AS cnt FROM T_Order WHERE (DataStatus IN (51, 61) OR (DataStatus = 91 AND CloseReason = 1)) AND Cnl_Status = 0 AND OrderId = :OrderId";
                    $checkCount = $this->app->dbAdapter->query($sql)->execute(array(':OrderId' => $orderId))->current()['cnt'];
                    //クレジットカードで支払った注文のチェック
                    $sqlCredit = "SELECT COUNT(*) AS cnt FROM AT_Order WHERE ExtraPayType = 1 AND ExtraPayKey IS NOT NULL AND OrderSeq = :OrderSeq";
                    $checkCountCredit = $this->app->dbAdapter->query($sqlCredit)->execute(array(':OrderSeq' => $data->AtobaraiOrderSeq))->current()['cnt'];
                }

                if ($data->CorporateCode == 15){
                    $data->PayWayType = 4; // LINEPay
                }else if ($data->CorporateCode == 16){
                    $data->PayWayType = 6; // PayPay
 //               }else if ($data->CorporateCode == 17){
 //                   $data->PayWayType = 7; // PayB
                }else if (($data->CorporateCode == 17) && ($data->etcAddCode== 9901)){
                	$data->PayWayType = 10; // ゆうちょPay
                }else if (($data->CorporateCode == 17) && ($data->etcAddCode!= 9901)){
                	$data->PayWayType = 7; // PayB
                }else if ($data->CorporateCode == 18){
                	$data->PayWayType = 11; // 楽天銀行コンビニ払
                }

                if ($checkCount <= 0 || $checkCountCredit >= 1) {
                    // エラーメッセージを入れておく。
                    $mdlv = new ViewOrderCustomer($this->app->dbAdapter);
                    $orderData = $mdlv->findOrderCustomer(array("OrderId" => $orderId))->current();

                    if (!$orderData) {
                        $vdata[0] = "該当無し";
                        $vdata[1] = $data->PayWayType;
                        $vdata[6] = sprintf('該当注文無し　：　指定された注文SEQ = %s', $data->AtobaraiOrderSeq);
                    } else {
                        $vdata[0] = $orderData['OrderId'];
                        $vdata[1] = $data->PayWayType;
                        if ($checkCountCredit <= 0)
                        {
                            $vdata[6] = sprintf('入金待ちではない　<a href="rworder/detail/oseq/%d" target="_blank">→注文情報</a>', $orderData['OrderSeq']);
                        } else {
                            $vdata[6] = sprintf('クレジットカードで支払った注文　<a href="rworder/detail/oseq/%d" target="_blank">→注文情報</a>', $orderData['OrderSeq']);
                        }

                        $this->updateIncreNote($orderData['OrderSeq'], $userId);
                    }
                    $resultViewData[] = $vdata;
                } else if ($checkCount > 1) {

                    throw new \Exception(sprintf('複数該当　（指定された注文ID = %s）', $orderId));
                } else {
                    // OrderDataを求める。
                    $sql = "SELECT * FROM T_Order WHERE (DataStatus IN (51, 61) OR (DataStatus = 91 AND CloseReason = 1)) AND OrderId = :OrderId";
                    $orderData = $this->app->dbAdapter->query($sql)->execute(array(':OrderId' => $orderId))->current();

                    // 2015/10/19 Y.Suzuki Add 会計対応 Stt
                    // 注文SeqからOEMIDを取得し、コードマスタ（識別ID：153）と紐付けて銀行支店IDを取得する。
                    // OEMID抜き出し
                    $oemId = (is_null($orderData['OemId'])) ? 0 : $orderData['OemId'];
                    $sql = "SELECT Class1 FROM M_Code WHERE CodeId = 153 AND KeyCode = :KeyCode";
                    $branchBankId = $this->app->dbAdapter->query($sql)->execute(array(':KeyCode' => $oemId))->current()['Class1'];
                    // 2015/10/19 Y.Suzuki Add 会計対応 End

                    // 入金前データステータスの取得
                    $datastatusBeforeReceipt = (int)$orderData['DataStatus'];

                    $prm = array(
                            ':pi_receipt_amount'   => $data->PaymentAmount,                             // 入金額（バーコード上の請求額）
                            ':pi_order_seq'        => $orderData['OrderSeq'],                           // 注文Seq
                            ':pi_receipt_date'     => date('Y-m-d', strtotime($data->CustPaymentDate)), // 入金日
                            ':pi_receipt_class'    => $data->PayWayType,                                // 入金方法
                            ':pi_branch_bank_id'   => $branchBankId,                                    // 銀行支店ID       // 2015/10/19 Y.Suzuki 会計対応 Mod
                            ':pi_receipt_agent_id' => 2,                                                // 収納代行ID(2:@ﾍﾟｲﾒﾝﾄ)
                            ':pi_deposit_date'     => (IsValidDate($data->AccountPaymentDate)) ? $data->AccountPaymentDate : null,           // 口座入金日       // 2015/12/09 Y.Suzuki 会計対応 Mod
                            ':pi_user_id'          => $userId,
                            ':pi_receipt_note'     => null,
                    );

                    try {
                        $ri = $stm->execute($prm);

                        // SQL実行例外なしもエラー戻り値の時は例外をｽﾛｰ
                        $retval = $this->app->dbAdapter->query($getretvalsql)->execute(null)->current();
                        if ($retval['po_ret_sts'] != 0) {
                            throw new \Exception($retval['po_ret_msg']);
                        }
                    }
                    catch(\Exception $e) { throw $e; }

                    // 未印刷の請求書印刷予約データを削除
                    $mdlch = new TableClaimHistory($this->app->dbAdapter);
                    $mdlch->deleteReserved($orderData['OrderSeq'], $userId);

                    // 請求額、差額を取得する。
                    $mdlcc = new TableClaimControl($this->app->dbAdapter);
                    $claimAmount = $mdlcc->findClaim(array("OrderSeq" => $orderData['OrderSeq']))->current()['ClaimAmount'];
                    $sagaku = $claimAmount - $data->PaymentAmount;

                    // 立替・売上管理データ更新
                    $mdlps = new TablePayingAndSales($this->app->dbAdapter);
                    $mdld = new ViewDelivery($this->app->dbAdapter);
                    $deli = $mdld->findByOrderSeq($orderData['OrderSeq'])->current();
                    $orderData = $mdlo->find($data->AtobaraiOrderSeq)->current();

                    // 入金済み正常ｸﾛｰｽﾞの場合、立替対象とする。
                    if ($orderData['DataStatus'] == 91 && $orderData['CloseReason'] == 1) {
                        $mdlapas = new \models\Table\ATablePayingAndSales($this->app->dbAdapter);
                        // 既に[立替条件クリアフラグ]が[1：条件をクリアしている]か？の取得
                        $isAlreadyClearCondition = $mdlps->IsAlreadyClearCondition($orderData['OrderSeq']);

                        $mdlps->clearConditionForCharge($orderData['OrderSeq'], 1, $userId);

                        if (!$isAlreadyClearCondition) {
                            $row_pas = $this->app->dbAdapter->query(" SELECT Seq FROM T_PayingAndSales WHERE OrderSeq = :OrderSeq "
                                )->execute(array(':OrderSeq' => $orderData['OrderSeq']))->current();

                            // 入金により立替条件クリアフラグが１化されるとき => '2:入金'として更新(顧客入金日をセットする)
                            $mdlapas->saveUpdate(array('ATUriType' => 2, 'ATUriDay' => date('Ymd', strtotime($data->CustPaymentDate))), $row_pas['Seq']);
                        }
                    }

                    // 印紙代発生の有無
                    // 2014.2.21　印紙税発生の条件を金額依存では無くバーコードに含まれるフラッグに依存するように変更
                    //if ($atPaymentConfig->enable_stamp_fee && $data->PaymentAmount >= 31500 && $data->PayWayType == 1)
                    if ($atPaymentConfig['enable_stamp_fee'] && $data->StampFlag) {
                    	// 支払方法区分２取得
					    $sql = "SELECT cd.Class2 as Class2
					              FROM T_ReceiptControl as rc 
					              LEFT JOIN M_Code as cd ON cd.CodeId = 198 AND cd.KeyCode = rc.ReceiptClass 
					             WHERE rc.OrderSeq = :OrderSeq
					             ORDER BY rc.ReceiptSeq DESC LIMIT 1;";
					    $Class2 = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $orderData['OrderSeq']))->current()['Class2'];
					    //支払方法区分2が0:印紙代対象
					    if($Class2==0){
                            $stampFee['OrderSeq']       = $orderData['OrderSeq'];
                            $stampFee['DecisionDate']   = date('Y-m-d');
                            $stampFee['StampFee']       = 200;
                            $stampFee['ClearFlg']       = 0;
                            $stampFee['CancelFlg']      = 0;

                            $mdlstmp = new TableStampFee($this->app->dbAdapter);
                            $mdlstmp->saveNew($stampFee);
                        }
                    }
                    $vdata[7] = $claimAmount;
                    $vdata[8] = $sagaku;

                    $vdata[0] = $orderData['OrderId'];
                    $vdata[1] = $data->PayWayType;
                    $vdata[2] = $data->PaymentAmount;
                    $vdata[3] = "";
                    $vdata[4] = date('Y-m-d', strtotime($data->CustPaymentDate));
                    $vdata[5] = "";

                    // 作成した入金管理Seqを取得する。（1注文に対する入金は複数存在するので、注文に紐づく入金のMAX値を取得）
                    $sql = "SELECT MAX(ReceiptSeq) AS ReceiptSeq FROM T_ReceiptControl WHERE OrderSeq = :OrderSeq GROUP BY OrderSeq";
                    $rcptSeq = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $orderData['OrderSeq']))->current()['ReceiptSeq'];

                    if ($datastatusBeforeReceipt != 91) {// [91：クローズ]からの入金はメール対象から除外

                        // NOTE : 本箇所でのﾒｰﾙ送信は行わない(ﾊﾞｯﾁによるｽｹｼﾞｭｰﾘﾝｸﾞ送信)
                        // T_ReceiptControl.MailFlgの[0：未送信(送信対象)]化
                        $this->app->dbAdapter->query(" UPDATE T_ReceiptControl SET MailFlg = 0 WHERE ReceiptSeq = :ReceiptSeq ")->execute(array(':ReceiptSeq' => $rcptSeq));
                    }
                    // 注文履歴へ登録
                    $history = new CoralHistoryOrder($this->app->dbAdapter);
                    $history->InsOrderHistory($orderData['OrderSeq'], 61, $userId);

                    // 2016/01/05 Y.Suzuki Mod 会計関連_入金取消対応（全ての更新処理が完了したあとで入金管理_会計のINSERT処理をする必要が発生したため、位置を移動） Stt
                    // 2015/10/19 Y.Suzuki Add 会計対応 Stt
                    // 会計用項目をINSERT
                    // 2015/12/21 Y.Suzuki Del 上（1531～1532行目）で取得しているので削除 Stt
//                     // 入金管理Seqの取得（複数存在する場合を考慮して、MAX値を取得する）
//                     $sql = "SELECT MAX(ReceiptSeq) AS ReceiptSeq FROM T_ReceiptControl WHERE OrderSeq = :OrderSeq";
//                     $rcptSeq = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $orderData['OrderSeq']))->current()['ReceiptSeq'];
                    // 2015/12/21 Y.Suzuki Del 上（1531～1532行目）で取得しているので削除 End

                    // 2016/01/05 Y.Suzuki Add 更新後のデータを取得 Stt
                    // 入金取消前立替クリアフラグ、入金取消前立替クリア日
                    $sql = "SELECT ClearConditionForCharge, ClearConditionDate FROM T_PayingAndSales WHERE OrderSeq = :OrderSeq";
                    $ri = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq));
                    $clearConditionForCharge = $ri->current()['ClearConditionForCharge'];
                    $clearConditionDate = $ri->current()['ClearConditionDate'];
                    // 入金取消前立替処理－ステータス、入金取消前配送－着荷確認
                    $sql = "SELECT Cnl_Status, Deli_ConfirmArrivalFlg FROM T_Order WHERE OrderSeq = :OrderSeq";
                    $ri = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $orderData['OrderSeq']));
                    $cnlStatus = $ri->current()['Cnl_Status'];
                    $deliConfirmArrivalFlg = $ri->current()['Deli_ConfirmArrivalFlg'];
                    // 2016/01/05 Y.Suzuki Add 更新後のデータを取得 End

                    $atdata = array(
                            'ReceiptSeq' => $rcptSeq,
                            'AccountNumber' => null,
                            'ClassDetails' => null,
                            'BankFlg' => 1,     // 銀行入金区分：1（入金取り込みなので 1 固定）
                            // 2016/01/05 Y.Suzuki Add 会計対応_入金取消関連（INSERT項目追加） Stt
                            'Before_ClearConditionForCharge' => $clearConditionForCharge,   // 入金取消前立替クリアフラグ
                            'Before_ClearConditionDate' => $clearConditionDate,             // 入金取消前立替クリア日
                            'Before_Cnl_Status' => $cnlStatus,                              // 入金取消前立替処理－ステータス
                            'Before_Deli_ConfirmArrivalFlg' => $deliConfirmArrivalFlg,      // 入金取消前配送－着荷確認
                            // 2016/01/05 Y.Suzuki Add 会計対応_入金取消関連（INSERT項目追加） End
                            'KeyInfo' => $data->KeyInfo,            // 確報取込対応(キー情報)
                            'SokuhoRegistDate' => date('Y-m-d'),    // 確報取込対応(速報データ取込日)
                            'KakuhoRegistDate' => null,             // 確報取込対応(確報データ取込日)
                    );

                    $mdlatrc = new ATableReceiptControl($this->app->dbAdapter);
                    $mdlatrc->saveNew($atdata);
                    // 2015/10/19 Y.Suzuki Add 会計対応 End
                    // 2016/01/05 Y.Suzuki Mod 会計関連_入金取消対応（全ての更新処理が完了したあとで入金管理_会計のINSERT処理をする必要が発生したため、位置を移動） End

                    // サマリー変数更新
                    $summary[$data->PayWayType]['recordCount']   += 1;
                    $summary[$data->PayWayType]['paymentAmount'] += $data->PaymentAmount;
                    $summary[$data->PayWayType]['claimAmount']   += $claimAmount;
                    $summary[$data->PayWayType]['sagakuAmount']  += $sagaku;
                }
            }

            // 取消対象データが存在する場合は、以下処理を行う。
            if (! empty($cdatas)) {
                // 取消対象データ数分、ループする。
                foreach ($cdatas as $data) {
                    unset($udata);

                    // OrderDataを求める。
                    $orderData = $mdlo->find($data->AtobaraiOrderSeq)->current();

                    // アラート作成
                    $mdlsa = new TableStagnationAlert($this->app->dbAdapter);
                    $udata = array(
                            'AlertClass' => 3,                                      // 停滞アラート区分(3：入金取消)※ 仮の区分
                            'AlertSign' => 1,                                       // アラートサイン(1：アラート)
                            'OrderSeq' => $orderData['OrderSeq'],                   // 注文SEQ
                            'StagnationDays' => NULL,                               // 停滞期間日数
                            'EnterpriseId' => $orderData['EnterpriseId'],           // 加盟店ID
                            'AlertJudgDate' => date('Y-m-d H:i:s'),                 // アラート抽出日時
                            'RegistId' => $userId,                                  // 登録者
                            'UpdateId' => $userId,                                  // 更新者
                            'ValidFlg' => 1,                                        // 有効フラグ
                    );
                    // 新規登録
                    $mdlsa->saveNew($udata);
                }
            }

            // T_ImportedReceiptFile(取込済み入金ファイル)登録
            $mdlirf->saveNew(array(
                    'ReceiptProcessClass' => 1, // 1：＠ペイメント
                    'FileName' => $_FILES["cres"]["name"],
                    'RegistId' => $userId,
                    'UpdateId' => $userId,
                    'ValidFlg' => 1,
                ));

            // その他(入金処理対象外データ)
            $summary2 = array(
                    1 => $classSummary, // 1:(確報)コンビニ
                    2 => $classSummary, // 2:(確報)郵便局
                    3 => $classSummary, // 3:(確報)銀行
                    9 => $classSummary, // 9:(速報)逆サイド
            );
            foreach ($edatas as $row) {
                $type = ($row->DataKind == 2) ? $row->PayWayType : 9;   // 確報時=>PayWayType／速報時=>[9]
                $summary2[$type]['recordCount']   += 1;
                $summary2[$type]['paymentAmount'] += $row->PaymentAmount;
                // ｺﾝﾋﾞﾆ確報取込対応
                if ($type == 1) {
                    $this->app->dbAdapter->query(" UPDATE AT_ReceiptControl SET KakuhoRegistDate = :KakuhoRegistDate WHERE KeyInfo = :KeyInfo "
                        )->execute(array(':KakuhoRegistDate' => date('Y-m-d'), ':KeyInfo' => $row->KeyInfo));

                    // キー情報がイコール且つ入金予定日が未設定の場合は、確報にある日付で更新する
                    $sql = " SELECT IFNULL(GROUP_CONCAT(rc.ReceiptSeq),'') AS ReceiptSeq FROM T_ReceiptControl rc INNER JOIN AT_ReceiptControl arc ON (arc.ReceiptSeq = rc.ReceiptSeq) WHERE arc.KeyInfo = :KeyInfo AND rc.DepositDate IS NULL ";
                    $row_rc = $this->app->dbAdapter->query($sql)->execute(array(':KeyInfo' => $row->KeyInfo))->current();
                    if ($row_rc['ReceiptSeq'] != '' ) {
                        $this->app->dbAdapter->query(" UPDATE T_ReceiptControl SET DepositDate = :DepositDate WHERE ReceiptSeq IN (:ReceiptSeq) "
                            )->execute(array(':DepositDate' =>  $row->AccountPaymentDate, ':ReceiptSeq' => $row_rc['ReceiptSeq']));
                    }
                }
            }

            $this->app->dbAdapter->getDriver()->getConnection()->commit();

            $message = sprintf("＠Payment入金ファイル　「%s」　をインポートしました。", f_e($_FILES["cres"]["name"]));
$this->app->logger->info(' impatpaycbAction completed(' . $message . ') ');

            $this->view->assign('datas', $resultViewData);
            $this->view->assign('cdatas', $cdatas);
            $this->view->assign('summary', $summary);
            $this->view->assign('summary2', $summary2);

        } catch(\Exception $e) {
            $this->app->dbAdapter->getDriver()->getConnection()->rollBack();
            $message = '<h4 style="color:red;">処理中にエラーが発生したため全件の入金をキャンセルしました。<br/>詳細　：　' . $e->getMessage() . "</h4>";
$this->app->logger->info(' impatpaycbAction error(' . $message . ') ');
        }

        $this->view->assign('message', $message);

        return $this->view;
    }

    /**
     * SMBC決済ステーションインポート
     * @see 取消対象のデータがあった場合はアラートを作成する。 → 取消データの判定が不明なため、未実装。場合によってはLogicSmbcRelationReceipt も修正する必要あり。
     */
    public function impsmbcAction()
    {
        $logic = new \models\Logic\SmbcRelation\LogicSmbcRelationReceipt($this->app->dbAdapter);

        $tmpName = $_FILES["cres"]["tmp_name"];
        \Coral\Base\IO\BaseIOUtility::convertFileEncoding($tmpName, null, mb_internal_encoding(), true);

        $orderTable = $logic->getOrderTable();
        $hisTable = new TableClaimHistory($this->app->dbAdapter);
        $pasTable = new TablePayingAndSales($this->app->dbAdapter);
        $deliView = new ViewDelivery($this->app->dbAdapter);
        $stampFeeTable = new TableStampFee($this->app->dbAdapter);
        $mdlapas = new \models\Table\ATablePayingAndSales($this->app->dbAdapter);

        $message = '';
        $more_infos = array();

        $edatas = array();  // 入金処理対象外データ
        $cdatas = array();  // 取消対象データ

        // サマリー変数定義と初期化
        $kakuhoSummary = array (
                'recordCount'   => 0,   // 取込件数
                'paymentAmount' => 0,   // 支払金額総額
        );

        try
        {
            // ユーザーIDの取得
            $obj = new \models\Table\TableUser($this->app->dbAdapter);
            $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

            $stm = $this->app->dbAdapter->query($this->getBaseP_ReceiptControl());

            // SQL実行結果取得用のSQL
            $getretvalsql = " SELECT @po_ret_sts AS po_ret_sts, @po_ret_errcd AS po_ret_errcd, @po_ret_sqlcd AS po_ret_sqlcd, @po_ret_msg AS po_ret_msg ";

$this->app->logger->info(' impsmbcAction start(' . $_FILES["cres"]["name"] . ' / filesize : ' . filesize($tmpName) . ') ');
            $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();

            // 取込み可能ファイルか？の確認
            $mdlirf = new TableImportedReceiptFile($this->app->dbAdapter);
            if (!$mdlirf->isCanImport(3, $_FILES["cres"]["name"])) {
                throw new \Exception('既に取込み済みのファイルです。');
            }

            foreach($logic->read($tmpName) as $row)
            {
                /** @var LogicSmbcRelationReceiptItem */
                $data = $row['data'];
                $order = $orderTable->find($data->orderSeq)->current();

                // 2015/10/19 Y.Suzuki Add 会計対応 Stt
                // 注文SeqからOEMIDを取得し、コードマスタ（識別ID：153）と紐付けて銀行支店IDを取得する。
                // OEMID抜き出し
                $oemId = (is_null($order['OemId'])) ? 0 : $order['OemId'];
                $sql = "SELECT Class1 FROM M_Code WHERE CodeId = 153 AND KeyCode = :KeyCode";
                $branchBankId = $this->app->dbAdapter->query($sql)->execute(array(':KeyCode' => $oemId))->current()['Class1'];
                // 2015/10/19 Y.Suzuki Add 会計対応 End

                // 入金前データステータスの取得
                $datastatusBeforeReceipt = (int)$order['DataStatus'];

                // 収納機関コードが 980008 の場合
                if ($data->syunoKikanCd == "980008")
                {
                    $payType = 4;
                } else {
                    $payType = $data->payWayType;
                }

                $prm = array(
                        ':pi_receipt_amount'   => $data->receiptAmount,                             // 入金額（バーコード上の請求額）
                        ':pi_order_seq'        => $order['OrderSeq'],                               // 注文Seq
                        ':pi_receipt_date'     => date('Y-m-d', strtotime($data->receivedDate)),    // 入金日
                        ':pi_receipt_class'    => $payType,                                         // 入金方法
                        ':pi_branch_bank_id'   => $branchBankId,                                    // 銀行支店ID       // 2015/10/19 Y.Suzuki 会計対応 Mod
                        ':pi_receipt_agent_id' => 3,                                                // 収納代行ID(3:SMBC決済ｽﾃｰｼｮﾝ)
                        ':pi_deposit_date'     => ($data->depositDate == '') ? null : date('Y-m-d', strtotime($data->depositDate)),     // 口座入金日
                        ':pi_user_id'          => $userId,
                        ':pi_receipt_note'     => null,
                );

                try {
                    $ri = $stm->execute($prm);

                    // SQL実行例外なしもエラー戻り値の時は例外をｽﾛｰ
                    $retval = $this->app->dbAdapter->query($getretvalsql)->execute(null)->current();
                    if ($retval['po_ret_sts'] != 0) {
                        throw new \Exception($retval['po_ret_msg']);
                    }
                }
                catch(\Exception $e) { throw $e; }

                // 未印刷の請求書印刷予約データを削除
                $mdlch = new TableClaimHistory($this->app->dbAdapter);
                $mdlch->deleteReserved($order['OrderSeq'], $userId);

                // 立替・売上管理データ更新
                $deli = $deliView->findByOrderSeq($order['OrderSeq'])->current();
                $order = $orderTable->find($data->orderSeq)->current();

                // 請求額、差額を取得する。
                $mdlcc = new TableClaimControl($this->app->dbAdapter);
                $claimAmount = $mdlcc->findClaim(array("OrderSeq" => $order['OrderSeq']))->current()['ClaimAmount'];
                $sagaku = $claimAmount - $data->receiptAmount;

                // 入金済み正常ｸﾛｰｽﾞの場合、立替対象にする
                if ($order['DataStatus'] == 91 && $order['CloseReason'] == 1) {
                    // 既に[立替条件クリアフラグ]が[1：条件をクリアしている]か？の取得
                    $isAlreadyClearCondition = $pasTable->IsAlreadyClearCondition($order['OrderSeq']);

                    $pasTable->clearConditionForCharge($order['OrderSeq'], 1, $userId);

                    if (!$isAlreadyClearCondition) {
                        $row_pas = $this->app->dbAdapter->query(" SELECT Seq FROM T_PayingAndSales WHERE OrderSeq = :OrderSeq "
                            )->execute(array(':OrderSeq' => $order['OrderSeq']))->current();

                        // 入金により立替条件クリアフラグが１化されるとき => '2:入金'として更新(顧客入金日をセットする)
                        $mdlapas->saveUpdate(array('ATUriType' => 2, 'ATUriDay' => date('Ymd', strtotime($data->receivedDate))), $row_pas['Seq']);
                    }
                }

                // 印紙代発生の有無
                if($data->stampFlag)
                {
                    // 支払方法区分２取得
					$sql = "SELECT cd.Class2 as Class2
				              FROM T_ReceiptControl as rc 
				              LEFT JOIN M_Code as cd ON cd.CodeId = 198 AND cd.KeyCode = rc.ReceiptClass 
				             WHERE rc.OrderSeq = :OrderSeq
				             ORDER BY rc.ReceiptSeq DESC LIMIT 1;";
				    $Class2 = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $order['OrderSeq']))->current()['Class2'];
				    //支払方法区分2が0:印紙代対象
				    if($Class2==0){
                        $stampData = array(
                            'OrderSeq' => $order['OrderSeq'],
                            'DecisionDate' => date('Y-m-d'),
                            'StampFee' => 200,
                            'ClearFlg' => 0,
                            'CancelFlg' => 0
                        );
                        $stampFeeTable->saveNew($stampData);
                    }
                }
                $more_infos[$row['index']] = array(
                    'rcptDate' => date('Y-m-d', strtotime($data->receivedDate)),
                    'claimAmount' => $claimAmount,
                    'sagaku' => $sagaku
                );

                // 作成した入金管理Seqを取得する。（1注文に対する入金は複数存在するので、注文に紐づく入金のMAX値を取得）
                $sql = "SELECT MAX(ReceiptSeq) AS ReceiptSeq FROM T_ReceiptControl WHERE OrderSeq = :OrderSeq GROUP BY OrderSeq";
                $rcptSeq = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $order['OrderSeq']))->current()['ReceiptSeq'];

                if ($datastatusBeforeReceipt != 91 && $order['DataStatus'] == 91) {// [91：クローズ]からの入金はメール対象から除外
                // 入金確認メール送信
                try
                {
                    $mail = new CoralMail($this->app->dbAdapter, $this->app->mail['smtp']);
                    $mail->SendRcptConfirmMail($rcptSeq, $userId);
                }
                catch(\Exception $mailErr)
                {
                    $more_infos[$row['index']]['info'] = 'メール送信NG';
                }
                }
                // 注文履歴へ登録
                $history = new CoralHistoryOrder($this->app->dbAdapter);
                $history->InsOrderHistory($order['OrderSeq'], 61, $userId);

                // 2016/01/05 Y.Suzuki Mod 会計関連_入金取消対応（立替ｸﾘｱしたあとで入金管理_会計のINSERT処理をする必要が発生したため、位置を移動） Stt
                // 2015/11/17 Y.Suzuki Add 会計対応 Stt
                // 会計用項目をINSERT
                // 2016/01/05 Y.Suzuki Del 上（1987～1988行目）で取得しているので削除 Stt
//                 // 入金管理Seqの取得（複数存在する場合を考慮して、MAX値を取得する）
//                 $sql = "SELECT MAX(ReceiptSeq) AS ReceiptSeq FROM T_ReceiptControl WHERE OrderSeq = :OrderSeq";
//                 $rcptSeq = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $order['OrderSeq']))->current()['ReceiptSeq'];
                // 2016/01/05 Y.Suzuki Del 上（1987～1988行目）で取得しているので削除 End

                // 2016/01/05 Y.Suzuki Add 更新後のデータを取得 Stt
                // 入金取消前立替クリアフラグ、入金取消前クリア日
                $sql = "SELECT ClearConditionForCharge, ClearConditionDate FROM T_PayingAndSales WHERE OrderSeq = :OrderSeq";
                $ri = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq));
                $clearConditionForCharge = $ri->current()['ClearConditionForCharge'];
                $clearConditionDate = $ri->current()['ClearConditionDate'];
                // 入金取消前立替処理－ステータス、入金取消前配送－着荷確認
                $sql = "SELECT Cnl_Status, Deli_ConfirmArrivalFlg FROM T_Order WHERE OrderSeq = :OrderSeq";
                $ri = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $order['OrderSeq']));
                $cnlStatus = $ri->current()['Cnl_Status'];
                $deliConfirmArrivalFlg = $ri->current()['Deli_ConfirmArrivalFlg'];
                // 2016/01/05 Y.Suzuki Add 更新後のデータを取得 End

                $atdata = array(
                        'ReceiptSeq' => $rcptSeq,
                        'AccountNumber' => null,
                        'ClassDetails' => null,
                        'BankFlg' => 1,     // 銀行入金区分：1（入金取り込みなので 1 固定）
                        // 2016/01/05 Y.Suzuki Add 会計対応_入金取消関連（INSERT項目追加） Stt
                        'Before_ClearConditionForCharge' => $clearConditionForCharge,   // 入金取消前立替クリアフラグ
                        'Before_ClearConditionDate' => $clearConditionDate,             // 入金取消前立替クリア日
                        'Before_Cnl_Status' => $cnlStatus,                              // 入金取消前立替処理－ステータス
                        'Before_Deli_ConfirmArrivalFlg' => $deliConfirmArrivalFlg,      // 入金取消前配送－着荷確認
                        // 2016/01/05 Y.Suzuki Add 会計対応_入金取消関連（INSERT項目追加） End
                        'KeyInfo' => $data->shopOrderNumber,    // 確報取込対応(キー情報)
                        'SokuhoRegistDate' => date('Y-m-d'),    // 確報取込対応(速報データ取込日)
                        'KakuhoRegistDate' => null,             // 確報取込対応(確報データ取込日)
                );

                $mdlatrc = new ATableReceiptControl($this->app->dbAdapter);
                $mdlatrc->saveNew($atdata);
                // 2015/11/17 Y.Suzuki Add 会計対応 End
                // 2016/01/05 Y.Suzuki Mod 会計関連_入金取消対応（全ての更新処理が完了したあとで入金管理_会計のINSERT処理をする必要が発生したため、位置を移動） End
            }

            // コンビニ確報取消取込対応
            $errItems = $logic->getErrorItems();
            foreach ($errItems as $errItem) {
                if ($errItem['data']->payWayType == 1 && $errItem['data']->acceptCode == '02') {
                    // (コンビニ＆確報)

                    // ｷｰ情報に一致する[ReceiptSeq]を取得
                    $row_arc = $this->app->dbAdapter->query(" SELECT ReceiptSeq FROM AT_ReceiptControl WHERE KeyInfo = :KeyInfo "
                        )->execute(array(':KeyInfo' => $errItem['data']->shopOrderNumber))->current();
                    if ($row_arc) {
                        // 確報データ取込日の設定
                        $this->app->dbAdapter->query(" UPDATE AT_ReceiptControl SET KakuhoRegistDate = :KakuhoRegistDate WHERE ReceiptSeq = :ReceiptSeq "
                            )->execute(array(':KakuhoRegistDate' => date('Y-m-d'), ':ReceiptSeq' => $row_arc['ReceiptSeq']));
                        // 入金予定日の設定(ｺﾝﾋﾞﾆ速報では本ﾊﾟﾗﾒﾀが通知されず、確報で通知される為)
                        $this->app->dbAdapter->query(" UPDATE T_ReceiptControl SET DepositDate = :DepositDate WHERE ReceiptSeq = :ReceiptSeq "
                            )->execute(array(':DepositDate' => $errItem['data']->depositDate, ':ReceiptSeq' => $row_arc['ReceiptSeq']));
                    }

                    // その他(入金処理対象外データ)として積上げ
                    $kakuhoSummary['recordCount']   += 1;
                    $kakuhoSummary['paymentAmount'] += $errItem['raw_data'][13];
                }
                else if ($errItem['data']->payWayType == 1 && $errItem['data']->acceptCode == '03') {
                    // (コンビニ＆取消)
                    $row_order = $this->app->dbAdapter->query(" SELECT EnterpriseId FROM T_Order WHERE OrderSeq = :OrderSeq "
                        )->execute(array(':OrderSeq' => $errItem['data']->orderSeq))->current();
                    if (!$row_order) { continue; }

                    // アラート作成
                    $mdlsa = new TableStagnationAlert($this->app->dbAdapter);
                    $udata = array(
                            'AlertClass' => 3,                                      // 停滞アラート区分(3：入金取消)※ 仮の区分
                            'AlertSign' => 1,                                       // アラートサイン(1：アラート)
                            'OrderSeq' => $errItem['data']->orderSeq,               // 注文SEQ
                            'StagnationDays' => NULL,                               // 停滞期間日数
                            'EnterpriseId' => $row_order['EnterpriseId'],           // 加盟店ID
                            'AlertJudgDate' => date('Y-m-d H:i:s'),                 // アラート抽出日時
                            'RegistId' => $userId,                                  // 登録者
                            'UpdateId' => $userId,                                  // 更新者
                            'ValidFlg' => 1,                                        // 有効フラグ
                    );
                    // 新規登録
                    $mdlsa->saveNew($udata);

                    $cdatas[] = $errItem;   // 取消対象データとして積上げ
                }
                else {
                    $edatas[] = $errItem;   // 入金処理対象外データとして積上げ
                    if ($errItem['reason'] == '入金待ちではありません') {
                        $this->updateIncreNote($errItem['data']->orderSeq, $userId);
                    }
                }
            }

            // T_ImportedReceiptFile(取込済み入金ファイル)登録
            $mdlirf->saveNew(array(
                    'ReceiptProcessClass' => 3, // 3：SMBC
                    'FileName' => $_FILES["cres"]["name"],
                    'RegistId' => $userId,
                    'UpdateId' => $userId,
                    'ValidFlg' => 1,
            ));

            $this->app->dbAdapter->getDriver()->getConnection()->commit();

            // count関数対策
            $validItemsLen = 0;
            if(!empty($logic->getValidItems())) {
                $validItemsLen = count($logic->getValidItems());
            }

            // count関数対策
            $edatasLen = 0;
            if(!empty($edatas)) {
                $edatasLen = count($edatas);
            }

            $message = sprintf('入金ファイル「%s」をインポートしました。（入金対象： %d 件、入金対象外： %d 件）', f_e($_FILES["cres"]["name"]),  $validItemsLen, $edatasLen);
$this->app->logger->info(' impsmbcAction completed(' . $message . ') ');
        }
        catch(\Exception $err)
        {
            $this->app->dbAdapter->getDriver()->getConnection()->rollBack();
            $message = sprintf('<h4 style="color:red">処理中にエラーが発生したため全件の入力をキャンセルしました。<br />詳細：%s</h4>', f_e($err->getMessage()));
$this->app->logger->info(' impsmbcAction error(' . $message . ') ');
        }

        $this->view->assign('valid_items', $logic->getValidItems());
        $this->view->assign('error_items', $edatas);
        $this->view->assign('cancel_items',$cdatas);
        $this->view->assign('kakuhoSummary',$kakuhoSummary);
        $this->view->assign('more_infos', $more_infos);
        $this->view->assign('message', $message);

        return $this->view;
    }

    /**
     * MTデータインポート
     */
    public function impmtAction()
    {
        $tmpName = $_FILES["cres"]["tmp_name"];

        $list = array();    // 対象リスト
        $listNa = array();  // 対象外リスト

        $handle = null;
        try {
            // ユーザーIDの取得
            $obj = new \models\Table\TableUser($this->app->dbAdapter);
            $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

            $stm = $this->app->dbAdapter->query($this->getBaseP_ReceiptControl());

            // SQL実行結果取得用のSQL
            $getretvalsql = " SELECT @po_ret_sts AS po_ret_sts, @po_ret_errcd AS po_ret_errcd, @po_ret_sqlcd AS po_ret_sqlcd, @po_ret_msg AS po_ret_msg ";

            if ($tmpName == '') {
                $this->view->assign('message', '<span style="font-size: 18px; color: red;">入金CSVファイルのオープンに失敗しました。<br />ファイルを選択してください。</span>');
                $this->view->assign('list', $list);
                $this->view->assign('listNa', $listNa);
                return $this->view;
            }
            $handle = @fopen($tmpName, "r");

            if (!$handle) {
                // ファイルオープン失敗
                $message = '<span style="font-size: 18px; color: red;">入金CSVファイルのオープンに失敗しました。<br />再試行してください。</span>';
            }
            else {
$this->app->logger->info(' impmtAction start(' . $_FILES["cres"]["name"] . ' / filesize : ' . filesize($tmpName) . ') ');
                $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();

                $accNo = '';        // 口座番号
                $accUpdate = '';    // 口座更新年月日
                $sumPayAmount = ''; // 合計払込金額
                $sumLine = '';      // 合計口数
                $custName = '';     // 加入者名
                $tapeCrdate = '';   // テープ作成年月日
                $dummy = '';        // ダミー

                $cntBodyLine = 0;
                $sumBodyLine = 0;

                // 入金ループ
                while (($data = fgetcsv($handle, 1000, ",")) !== false) {

                    // チェック
                    if (mb_strlen($data[0], 'sjis-win') != 128) {
                        throw new \Exception('<span style="font-size: 18px; color: red;">レコード形式が不正です</span>');
                    }

                    // レコード区分
                    $recordCode = (int)substr($data[0], 0, 1);

                    if ($recordCode == 1) {
                        //--------------------------------------------
                        // (ヘッダ)
                        $accNo          = substr($data[0], 1, 11);
                        $accUpdate      = substr($data[0], 12, 8);
                        $sumPayAmount   = substr($data[0], 20, 12);
                        $sumLine        = substr($data[0], 32, 8);
                        $custName       = substr($data[0], 40, 30);
                        $tapeCrdate     = substr($data[0], 70, 8);
                        $dummy          = substr($data[0], 78, 50);
                    }
                    else if ($recordCode == 2) {
                        //--------------------------------------------
                        // (ボディ)
                        $bodyCustdata   = substr($data[0], 1, 44);
                        $bodyOseq       = substr($data[0], 3, 42);
                        $bodyPayAmount  = substr($data[0], 86, 11);
                        $bodyYmd        = substr($data[0], 104, 8);

                        // 集計
                        $cntBodyLine += 1;
                        $sumBodyLine += (int)$bodyPayAmount;

                        // 注文SEQに該当する注文の有無チェック
                        $sql = " SELECT OrderId FROM T_Order WHERE OrderSeq = :OrderSeq ";
                        $row_order = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => (int)$bodyOseq))->current();
                        if (!$row_order) {
                            $listNa[] = array(
                                'OrderId'   => $bodyCustdata,
                                'PayAmount' => (int)$bodyPayAmount,
                                'Ymd'       => substr($bodyYmd, 0, 4) . '/' . substr($bodyYmd, 4, 2) . '/' . substr($bodyYmd, 6, 2),
                                'Error'     => "該当する注文が見つかりません",
                                'OrderSeq'  => '',
                            );
                            continue;
                        }

                        // 注文SEQに該当する注文の入金待ちチェック
                        $sql = " SELECT COUNT(1) AS cnt FROM T_Order WHERE OrderSeq = :OrderSeq AND Cnl_Status = 0 AND (DataStatus = 51 OR DataStatus = 61 OR (DataStatus = 91 AND CloseReason = 1)) ";
                        $cnt = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => (int)$bodyOseq))->current()['cnt'];
                        if ($cnt == 0) {
                            $listNa[] = array(
                                'OrderId'   => $row_order['OrderId'],
                                'PayAmount' => (int)$bodyPayAmount,
                                'Ymd'       => substr($bodyYmd, 0, 4) . '/' . substr($bodyYmd, 4, 2) . '/' . substr($bodyYmd, 6, 2),
                                'Error'     => "入金待ちではありません",
                                'OrderSeq'  => (int)$bodyOseq,
                            );

                            $this->updateIncreNote((int)$bodyOseq, $userId);
                            continue;
                        }
                        
                        //クレジットカードで支払った注文のチェック
                        $sqlCredit = "SELECT COUNT(*) AS cnt FROM AT_Order WHERE ExtraPayType = 1 AND ExtraPayKey IS NOT NULL AND OrderSeq = :OrderSeq";
                        $checkCountCredit = $this->app->dbAdapter->query($sqlCredit)->execute(array(':OrderSeq' => (int)$bodyOseq))->current()['cnt'];
                        if ($checkCountCredit >= 1) {
                            $listNa[] = array(
                                'OrderId'   => $row_order['OrderId'],
                                'PayAmount' => (int)$bodyPayAmount,
                                'Ymd'       => substr($bodyYmd, 0, 4) . '/' . substr($bodyYmd, 4, 2) . '/' . substr($bodyYmd, 6, 2),
                                'Error'     => "クレジットカードで支払った注文",
                                'OrderSeq'  => (int)$bodyOseq,
                            );
                            
                            $this->updateIncreNote((int)$bodyOseq, $userId);
                            continue;
                        }

                        // 本段階で入金可能な注文SEQ通知が行われた

                        // 入金前データステータスの取得
                        $datastatusBeforeReceipt = (int)$this->app->dbAdapter->query(" SELECT DataStatus FROM T_Order WHERE OrderSeq = :OrderSeq "
                            )->execute(array(':OrderSeq' => (int)$bodyOseq))->current()['DataStatus'];

                        // 請求残高の取得
                        $sql = " SELECT cc.ClaimedBalance FROM T_Order o INNER JOIN T_ClaimControl cc ON (cc.OrderSeq = o.OrderSeq) WHERE o.OrderSeq = :OrderSeq ";
                        $claimedBalance = (int)$this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => (int)$bodyOseq))->current()['ClaimedBalance'];

                        // ①入金プロシージャー(P_ReceiptControl)呼び出し
                        $prm = array(
                                ':pi_receipt_amount'   => (int)$bodyPayAmount,                              // 入金額（バーコード上の請求額）
                                ':pi_order_seq'        => (int)$bodyOseq,                                   // 注文Seq
                                ':pi_receipt_date'     => substr($bodyYmd, 0, 4) . '-' . substr($bodyYmd, 4, 2) . '-' . substr($bodyYmd, 6, 2),   // 入金日
                                ':pi_receipt_class'    => 2,                                                // 入金方法（郵便局）
                                ':pi_branch_bank_id'   => null,                                             // 銀行支店ID
                                ':pi_receipt_agent_id' => null,                                             // 収納代行ID
                                ':pi_deposit_date'     => null,                                             // 口座入金日
                                ':pi_user_id'          => $userId,
                                ':pi_receipt_note'     => null,
                        );

                        try {
                            $ri = $stm->execute($prm);

                            // SQL実行例外なしもエラー戻り値の時は例外をｽﾛｰ
                            $retval = $this->app->dbAdapter->query($getretvalsql)->execute(null)->current();
                            if ($retval['po_ret_sts'] != 0) {
                                throw new \Exception($retval['po_ret_msg']);
                            }
                        }
                        catch(\Exception $e) { throw $e; }

                        // ②未印刷の請求書印刷予約データを削除
                        $mdlch = new TableClaimHistory($this->app->dbAdapter);
                        $mdlch->deleteReserved((int)$bodyOseq, $userId);

                        // ③立替・売上管理データ更新
                        $sql = " SELECT o.DataStatus, o.CloseReason, cc.ClaimedBalance FROM T_Order o INNER JOIN T_ClaimControl cc ON (cc.OrderSeq = o.OrderSeq) WHERE o.OrderSeq = :OrderSeq ";
                        $row = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => (int)$bodyOseq))->current();
                        if ($row['DataStatus'] == 91 AND $row['CloseReason'] == 1) {
                            // (①の処理後、注文が入金済み正常クローズ（DataStatus=91、CloseReason=1）となった場合)
                            $mdlpas = new TablePayingAndSales($this->app->dbAdapter);

                            $mdlapas = new \models\Table\ATablePayingAndSales($this->app->dbAdapter);
                            // 既に[立替条件クリアフラグ]が[1：条件をクリアしている]か？の取得
                            $isAlreadyClearCondition = $mdlpas->IsAlreadyClearCondition((int)$bodyOseq);

                            $mdlpas->clearConditionForCharge((int)$bodyOseq, 1, $userId);

                            if (!$isAlreadyClearCondition) {
                                $row_pas = $this->app->dbAdapter->query(" SELECT Seq FROM T_PayingAndSales WHERE OrderSeq = :OrderSeq "
                                    )->execute(array(':OrderSeq' => (int)$bodyOseq))->current();

                                // 入金により立替条件クリアフラグが１化されるとき => '2:入金'として更新(顧客入金日をセットする)
                                $mdlapas->saveUpdate(array('ATUriType' => 2, 'ATUriDay' => substr($bodyYmd, 0, 8)), $row_pas['Seq']);
                            }
                        }

                        // ④入金確認メールの送信（送信エラーは無視して以降の処理を継続する）
                        $sql = " SELECT MAX(ReceiptSeq) AS ReceiptSeq FROM T_ReceiptControl WHERE OrderSeq = :OrderSeq ";
                        $receiptSeq = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => (int)$bodyOseq))->current()['ReceiptSeq'];
                        $sendMailError = '';
                        if ($datastatusBeforeReceipt != 91 && $row['DataStatus'] == 91) {// [91：クローズ]からの入金はメール対象から除外
                        try {
                            $mail = new CoralMail($this->app->dbAdapter, $this->app->mail['smtp']);
                            $mail->SendRcptConfirmMail($receiptSeq, $userId);
                        } catch(\Exception $e) {
                            // エラーメッセージを入れておく。
                            $sendMailError = 'メール送信NG';
                        }
                        }

                        // ⑤注文履歴の登録
                        $history = new CoralHistoryOrder($this->app->dbAdapter);
                        $history->InsOrderHistory((int)$bodyOseq, 61, $userId);

                        // $listへ積み上げる
                        $list[] = array(
                                'OrderId'           => $row_order['OrderId'],
                                'PayAmount'         => (int)$bodyPayAmount,
                                'Ymd'               => substr($bodyYmd, 0, 4) . '/' . substr($bodyYmd, 4, 2) . '/' . substr($bodyYmd, 6, 2),
                                'ClaimedBalance'    => $claimedBalance,
                                'Sagaku'            => ($claimedBalance - (int)$bodyPayAmount),
                                'OrderSeq'          => (int)$bodyOseq,
                                'Note'          => $sendMailError,
                        );

                        // AT_ReceiptControl登録
                        $mdl_atrc = new \models\Table\ATableReceiptControl($this->app->dbAdapter);
                        // 2015/12/22 Y.Suzuki Mod 会計対応 Stt
                        // 「BankFlg：銀行入金区分」の情報が足りないので追加
                        //   → 「入金先トータル表」作成時に BankFlg を条件にしてデータを取得しているため、
                        //      今のままだと入金先トータル表のMTデータインポートからのデータが取得できなくなる
                        //        → 入金取り込みからの処理なので、銀行入金区分：1（バーチャル口座）で登録する！！！
                        // 2016/01/05 Y.Suzuki Add 更新後のデータを取得 Stt
                        // 入金取消前立替クリアフラグ、入金取消前立替クリア日
                        $sql = "SELECT ClearConditionForCharge, ClearConditionDate FROM T_PayingAndSales WHERE OrderSeq = :OrderSeq";
                        $ri = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq));
                        $clearConditionForCharge = $ri->current()['ClearConditionForCharge'];
                        $clearConditionDate = $ri->current()['ClearConditionDate'];
                        // 入金取消前立替処理－ステータス、入金取消前配送－着荷確認
                        $sql = "SELECT Chg_Status, Deli_ConfirmArrivalFlg FROM T_Order WHERE OrderSeq = :OrderSeq";
                        $ri = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => (int)$bodyOseq));
                        $chgStatus = $ri->current()['Chg_Status'];
                        $deliConfirmArrivalFlg = $ri->current()['Deli_ConfirmArrivalFlg'];
                        $atdata = array(
                                'ReceiptSeq' => $receiptSeq,
                                'AccountNumber' => $accNo,
                                'BankFlg' => 1,
                                'Before_ClearConditionForCharge' => $clearConditionForCharge,
                                'Before_ClearConditionDate' => $clearConditionDate,
                                'Before_Chg_Status' => $chgStatus,
                                'Before_Deli_ConfirmArrivalFlg' => $deliConfirmArrivalFlg
                        );
                        // 2016/01/05 Y.Suzuki Add 更新後のデータを取得 End

                        $mdl_atrc->saveNew($atdata);        // 2016/01/05 Y.Suzuki 会計対応_入金取消関連（INSERT項目追加） Mod
                        // 2015/12/22 Y.Suzuki Mod 会計対応 End
                    }
                    else if ($recordCode == 9) {
                        //--------------------------------------------
                        // (エンド : 合計口数,合計払込金額チェック)
                        if ((int)$sumLine != $cntBodyLine) {
                            throw new \Exception('<span style="font-size: 18px; color: red;">共通レコードの合計口数と個別レコードの行数が一致しません</span>');
                        }
                        if ((int)$sumPayAmount != $sumBodyLine) {
                            throw new \Exception('<span style="font-size: 18px; color: red;">共通レコードの合計払込金額と個別レコードの合計金額が一致しません</span>');
                        }
                    }
                }

                fclose($handle);

                $this->app->dbAdapter->getDriver()->getConnection()->commit();

                // count関数対策
                $listLen = 0;
                if(!empty($list)) {
                    $listLen = count($list);
                }

                $listNaLen = 0;
                if(!empty($listNa)) {
                    $listNaLen = count($listNa);
                }

                $message = sprintf("入金ファイル　「%s」　をインポートしました。（入金対象： %d 件、入金対象外： %d 件）",
                    f_e($_FILES["cres"]["name"]), $listLen, $listNaLen);
$this->app->logger->info(' impmtAction completed(' . $message . ') ');
            }
        } catch(\Exception $e) {
            $message = $e->getMessage();
            $this->app->dbAdapter->getDriver()->getConnection()->rollBack();
$this->app->logger->info(' impmtAction error(' . $message . ') ');
            // (初期化処理)
            if ($handle) { fclose($handle); }
            $list = array();
            $listNa = array();
        }

        $this->view->assign('message', $message);
        $this->view->assign('list', $list);
        $this->view->assign('listNa', $listNa);

        return $this->view;
    }

    /**
     * 詳細入金データインポートフォームの表示
     */
    public function impdtlformAction()
    {
        return $this->view;
    }

    /**
     * 詳細入金データインポート
     */
    public function impdtlAction()
    {
        $tmpName = $_FILES["cres"]["tmp_name"];
        if ($tmpName == '') {
            $this->view->assign('message', '<span style="font-size: 18px; color: red;">詳細入金ファイルが選択されていません。<br />再試行してください。</span>');
            return $this->view;
        }

        // サマリー変数定義と初期化
        $classSummary = array (
                'recordCount'   => 0,   // 取込件数
                'paymentAmount' => 0,   // 支払金額総額
                'claimAmount'   => 0,   // 請求金額総額
                'sagakuAmount'  => 0,   // 差額金額総額
        );
        $summary = array(
                1 => $classSummary,         // 1:コンビニ
                2 => $classSummary,         // 2:郵便局
                3 => $classSummary,         // 3:銀行
                9 => $classSummary,         // 9:その他
        );

        $edata = array();   // エラーデータ
        $idata = array();   // インフォメーション(過不足入金)データ

        $handle = null;
        try {
            // ユーザーIDの取得
            $obj = new \models\Table\TableUser($this->app->dbAdapter);
            $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

            $stm = $this->app->dbAdapter->query($this->getBaseP_ReceiptControl());

            // SQL実行結果取得用のSQL
            $getretvalsql = " SELECT @po_ret_sts AS po_ret_sts, @po_ret_errcd AS po_ret_errcd, @po_ret_sqlcd AS po_ret_sqlcd, @po_ret_msg AS po_ret_msg ";

            $buf = mb_convert_encoding(file_get_contents($tmpName), 'utf-8', 'sjis-win');
            if (!$buf) {
                // ファイルオープン失敗
                $message = '<span style="font-size: 18px; color: red;">詳細入金ファイルのオープンに失敗しました。<br />再試行してください。</span>';
            } else {
                $this->app->logger->info(' impdtlAction start(' . $_FILES["cres"]["name"] . ' / filesize : ' . filesize($tmpName) . ') ');
                $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();

                // 取込み可能ファイルか？の確認
                $mdlirf = new TableImportedReceiptFile($this->app->dbAdapter);
                if (!$mdlirf->isCanImport(2, $_FILES["cres"]["name"])) {
                    throw new \Exception('既に取込み済みのファイルです。');
                }

                // ｺｰﾄﾞﾏｽﾀ情報取得(KeyCode⇒KeyContent逆転版、で取得)
                $ccm = new CoralCodeMaster($this->app->dbAdapter);
                $masterReceiptMethod    = $ccm->getMasterCodesReverseKeyValue(163);                     // (入金方法)
                $masterBranchBank       = $ccm->getMasterCodesReverseKeyValue(153);                     // (銀行支店)
                $masterCvsReceiptAgent  = $ccm->getMasterCodesReverseKeyValue(101);                     // (コンビニ収納代行)
                $masterReceiptClass     = $ccm->getMasterCodesReverseKeyValue(154, ' AND Class2 = 1 '); // (入金元)
                $masterClassDetail      = $ccm->getMasterCodesReverseKeyValue(155, ' AND Class2 = 1 '); // (詳細入金方法)
                $masterAccountNumber    = $ccm->getMasterCodesReverseKeyValue(152);                     // (口座番号(郵便))

                $handle = tmpfile();
                fwrite($handle, $buf);
                rewind($handle);

                // 入金ループ
                $datas = array();
                // (チェックループ)
                $rowCount = 0;
                while (($row = fgetcsv($handle, 1000, ",")) !== false) {

                    // 1行目時のみ特別対応を実施
                    if ($rowCount == 0) {

                        // count関数対策
                        $rowLen = 0;
                        if(!empty($row)) {
                            $rowLen = count($row);
                        }

                        // (要素数が7にならない場合は、データ形式不正の例外とする)
                        if ($rowLen != 7) {
                            throw new \Exception('データ形式が不正です。');
                        }
                        // (見出しの為、ｶｳﾝﾄｱｯﾌﾟのみ実施)
                        $rowCount++;
                        continue;
                    }

                    $data = array(
                            'OrderId'           => $row[0],
                            'receiptMethod'     => nvl($masterReceiptMethod[$row[2]], -1),
                            'ReceiptDate'       => $row[4],
                            'ReceiptAmount'     => $row[6],
                            'branchBank'        => nvl($masterBranchBank[$row[3]], -1),
                            'cvsReceiptAgent'   => nvl($masterCvsReceiptAgent[$row[3]], -1),
                            'receiptClass'      => nvl($masterReceiptClass[$row[1]], -1),
                            'DepositDate'       => $row[5],
                            'classDetails'      => nvl($masterClassDetail[$row[3]], -1),
                            'accountNumber'     => nvl($masterAccountNumber[$row[3]], -1),
                            'bankFlg'           => '2',     // 銀行入金区分：2
                            'Row'               => $row,    // 行情報をそのまま保管しておく
                    );

                    // 入金元=>'その他'、入金方法=>'その他'、詳細入金方法=>'弁護士入金'、の時「銀行支店=>'三井住友'」を設定する
                    if ($data['receiptClass'] == 9 && $data['receiptMethod'] == 9 && $data['classDetails'] == 4) {
                        $data['branchBank'] = 0;
                    }

                    $errors = $this->validateImpDtl($data);

                    // count関数対策
                    if (!empty($errors)) {
                        // エラーあり ⇒ エラーデータへ積上げる
                        $edata[] = array( 'LineNumber' => $rowCount, 'DataRow' => $row, 'ErrorInfo' => $errors );
                    }

                    // count関数対策
                    if (empty($edata)) {
                        // エラーデータ積上げがなければ、登録用データへ積み上げる
                        $datas[] = $data;
                    }

                    $rowCount++;
                }

                // (本段階でエラーが１件でもある場合は、後続処理を行わない)
                // count関数対策
                if (!empty($edata)) {
                    throw new \Exception('データに不備があります。「入金エラーデータ」を確認してください。');
                }

                // count関数対策
                $datasLen = 0;
                if(!empty($datas)) {
                    $datasLen = count($datas);
                }

                // (登録処理ループ)
                for ($i=0; $i<$datasLen; $i++) {

                    $data = $datas[$i];
                    $oseq = $data['OrderSeq'];

                    // 更新処理実施
                    if (($data['receiptClass'] == 9 && $data['receiptMethod'] == 3) || ($data['receiptClass'] == 9 && $data['receiptMethod'] == 9 && $data['classDetails'] == 4)) {
                        // コードマスタから銀行支店IDを取得する。
                        $sql = "SELECT Class1 FROM M_Code WHERE CodeId = 153 AND KeyCode = :KeyCode";
                        $branchBankId = $this->app->dbAdapter->query($sql)->execute(array(':KeyCode' => $data['branchBank']))->current()['Class1'];
                    }

                    // 入金前データステータスの取得
                    $datastatusBeforeReceipt = (int)$this->app->dbAdapter->query(" SELECT DataStatus FROM T_Order WHERE OrderSeq = :OrderSeq "
                        )->execute(array(':OrderSeq' => (int)$oseq))->current()['DataStatus'];

                    $prm = array(
                            ':pi_receipt_amount'   => $data['ReceiptAmount'],
                            ':pi_order_seq'        => $oseq,
                            ':pi_receipt_date'     => $data['ReceiptDate'],
                            ':pi_receipt_class'    => $data['receiptMethod'],
                            ':pi_branch_bank_id'   => (! empty($branchBankId)) ? $branchBankId : null,
                            ':pi_receipt_agent_id' => ($data['receiptClass'] == 0) ? $data['cvsReceiptAgent'] : null,
                            ':pi_deposit_date'     => (! empty($data['DepositDate'])) ? $data['DepositDate'] : null,
                            ':pi_user_id'          => $userId,
                            ':pi_receipt_note'     => null,

                    );


                    $ri = $stm->execute($prm);

                    // SQL実行例外なしもエラー戻り値の時は例外をｽﾛｰ
                    $retval = $this->app->dbAdapter->query($getretvalsql)->execute(null)->current();
                    if ($retval['po_ret_sts'] != 0) {
                        throw new \Exception($retval['po_ret_msg']);
                    }

                    // 未印刷の請求書印刷予約データを削除
                    $mdlch = new TableClaimHistory($this->app->dbAdapter);
                    $mdlch->deleteReserved($oseq, $userId);

                    // 請求額、差額を取得する。
                    $mdlcc = new TableClaimControl($this->app->dbAdapter);
                    $claimAmount = $mdlcc->findClaim(array("OrderSeq" => $data['OrderSeq']))->current()['ClaimAmount'];
                    $sagaku = $claimAmount - $data['ReceiptAmount'];

                    // 立替・売上管理テーブル更新
                    $mdlpas = new TablePayingAndSales($this->app->dbAdapter);
                    $mdlo = new TableOrder($this->app->dbAdapter);
                    // 注文データを取得
                    $ri = $mdlo->findOrder(array('P_OrderSeq' => $oseq));
                    $order = ResultInterfaceToArray($ri);

                    $mdlapas = new \models\Table\ATablePayingAndSales($this->app->dbAdapter);
                    // 取得できた件数分、ループする
                    foreach ($order as $key => $value) {
                        // 入金済み正常クローズの場合、無条件に立替対象とする。
                        if ($value['DataStatus'] == 91 && $value['CloseReason'] == 1) {
                            // 既に[立替条件クリアフラグ]が[1：条件をクリアしている]か？の取得
                            $isAlreadyClearCondition = $mdlpas->IsAlreadyClearCondition($value['OrderSeq']);

                            $mdlpas->clearConditionForCharge($value['OrderSeq'], 1, $userId);

                            if (!$isAlreadyClearCondition) {
                                $row_pas = $this->app->dbAdapter->query(" SELECT Seq FROM T_PayingAndSales WHERE OrderSeq = :OrderSeq "
                                )->execute(array(':OrderSeq' => $value['OrderSeq']))->current();

                                // 入金により立替条件クリアフラグが１化されるとき => '2:入金'として更新(顧客入金日をセットする)
                                $mdlapas->saveUpdate(array('ATUriType' => 2, 'ATUriDay' => date('Ymd', strtotime($data['ReceiptDate']))), $row_pas['Seq']);
                            }
                        }
                    }

                    // 作成した入金管理Seqを取得する（注文Seqに対する入金は複数存在する可能性があるため、MAXの入金Seqを取得する）
                    $sql = "SELECT MAX(ReceiptSeq) AS ReceiptSeq FROM T_ReceiptControl WHERE OrderSeq = :OrderSeq GROUP BY OrderSeq";
                    $rcptSeq = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current()['ReceiptSeq'];

                    if ($datastatusBeforeReceipt != 91) {// [91：クローズ]からの入金はメール対象から除外

                        // NOTE : 本箇所でのﾒｰﾙ送信は行わない(ﾊﾞｯﾁによるｽｹｼﾞｭｰﾘﾝｸﾞ送信)
                        // T_ReceiptControl.MailFlgの[0：未送信(送信対象)]化
                        $this->app->dbAdapter->query(" UPDATE T_ReceiptControl SET MailFlg = 0 WHERE ReceiptSeq = :ReceiptSeq ")->execute(array(':ReceiptSeq' => $rcptSeq));
                    }

                    // 注文履歴登録用に親注文Seqから子注文Seqを再取得する。
                    $sql = "SELECT OrderSeq FROM T_Order WHERE P_OrderSeq = :P_OrderSeq AND Cnl_Status = 0";

                    $ri = $this->app->dbAdapter->query($sql)->execute(array(':P_OrderSeq' => $oseq));
                    $rows = ResultInterfaceToArray($ri);

                    // 注文履歴へ登録
                    $history = new CoralHistoryOrder($this->app->dbAdapter);
                    // 取得できた件数分ループする
                    foreach ($rows as $row) {
                        // 注文履歴登録
                        $history->InsOrderHistory($row["OrderSeq"], 61, $userId);
                    }

                    // 入金取消前立替クリアフラグ、入金取消前立替クリア日
                    $sql = "SELECT ClearConditionForCharge, ClearConditionDate FROM T_PayingAndSales WHERE OrderSeq = :OrderSeq";
                    $ri = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq));
                    $clearConditionForCharge = $ri->current()['ClearConditionForCharge'];
                    $clearConditionDate = $ri->current()['ClearConditionDate'];
                    // 入金取消前キャンセル－ステータス、入金取消前配送－着荷確認
                    $sql = "SELECT Cnl_Status, Deli_ConfirmArrivalFlg FROM T_Order WHERE OrderSeq = :OrderSeq";
                    $ri = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq));
                    $cnlStatus = $ri->current()['Cnl_Status'];
                    $deliConfirmArrivalFlg = $ri->current()['Deli_ConfirmArrivalFlg'];

                    // 入金元[その他] かつ 入金方法[郵政] の場合
                    if ($data['receiptClass'] == 9 && $data['receiptMethod'] == 2) {
                        // コードマスタから取得する
                        $sql = "SELECT Class1 FROM M_Code WHERE CodeId = 152 AND KeyCode = :KeyCode";
                        $accountNumber = $this->app->dbAdapter->query($sql)->execute(array(':KeyCode' => $data['accountNumber']))->current()['Class1'];
                        $data['accountNumber'] = $accountNumber;
                    } else {
                        $data['accountNumber'] = null;
                    }
                    // 入金元[その他] かつ 入金方法[その他] の場合
                    if ($data['receiptClass'] == 9 && $data['receiptMethod'] == 9) {
                        $data['classDetails'] = $data['classDetails'];
                    } else {
                        $data['classDetails'] = null;
                    }
                    $atdata = array(
                            'ReceiptSeq' => $rcptSeq,
                            'AccountNumber' => $data['accountNumber'],
                            'ClassDetails' => $data['classDetails'],
                            'BankFlg' => $data['bankFlg'],
                            'Before_ClearConditionForCharge' => $clearConditionForCharge,   // 入金取消前立替クリアフラグ
                            'Before_ClearConditionDate' => $clearConditionDate,             // 入金取消前立替クリア日
                            'Before_Cnl_Status' => $cnlStatus,                              // 入金取消前キャンセル－ステータス
                            'Before_Deli_ConfirmArrivalFlg' => $deliConfirmArrivalFlg,      // 入金取消前配送－着荷確認
                    );

                    $mdlatrc = new ATableReceiptControl($this->app->dbAdapter);
                    $mdlatrc->saveNew($atdata);

                    // サマリー変数更新
                    $summary[$data['receiptMethod']]['recordCount']   += 1;
                    $summary[$data['receiptMethod']]['paymentAmount'] += $data['ReceiptAmount'];
                    $summary[$data['receiptMethod']]['claimAmount']   += $claimAmount;
                    $summary[$data['receiptMethod']]['sagakuAmount']  += $sagaku;

                    // インフォメーション( 請求残高が0でない ⇒ インフォメーションデータへ積上げる)
                    $claimedBalance = $mdlcc->findClaim(array("OrderSeq" => $data['OrderSeq']))->current()['ClaimedBalance'];
                    if ($claimedBalance != 0) {
                        $idata[] = array(
                                'LineNumber'    => ($i + 1),
                                'DataRow'       => $data['Row'],
                                'ClaimedBalance'=> $claimedBalance,
                                'OrderSeq'      => $data['OrderSeq']
                        );
                    }
                }

                // T_ImportedReceiptFile(取込済み入金ファイル)登録
                $mdlirf->saveNew(array(
                        'ReceiptProcessClass' => 2, // 2：詳細入金ﾌｧｲﾙ
                        'FileName' => $_FILES["cres"]["name"],
                        'RegistId' => $userId,
                        'UpdateId' => $userId,
                        'ValidFlg' => 1,
                ));

                $this->app->dbAdapter->getDriver()->getConnection()->commit();

                $message = sprintf("詳細入金ファイル　「%s」　をインポートしました。", f_e($_FILES["cres"]["name"]));
                $this->app->logger->info(' impdtlAction completed(' . $message . ') ');

                $this->view->assign('summary', $summary);
            }
        } catch(\Exception $e) {
            $this->app->dbAdapter->getDriver()->getConnection()->rollBack();
            $message = '<h4 style="color:red;">処理中にエラーが発生したため全件の入金をキャンセルしました。<br/>詳細　：　' . $e->getMessage() . "</h4>";
            $this->app->logger->info(' impdtlAction error(' . $message . ') ');
            $this->view->assign('isException', true);   // 例外が発生したことをビューへ通知
        }

        if ($handle) { fclose($handle); }   // ファイルハンドルが有効であればクローズ処理

        $this->view->assign('message', $message);
        $this->view->assign('edata', $edata);
        $this->view->assign('idata', $idata);

        return $this->view;
    }

    /**
     * 詳細入金データ内容を検証する
     *
     * @param array $data 詳細入金データ
     * @return array エラーメッセージの配列
     * @see 注文ID(OrderId)に入力があり、且つ該当注文が存在する(ﾃﾞｰﾀｽﾃｰﾀｽは問わない)時、$dataには[注文SEQ(OrderSeq)]が追加される。
     */
    protected function validateImpDtl(array &$data)
    {
        // (基本処理)関数[validateDtlrcptform]呼出し
        $errors = $this->validateDtlrcptform($data);

        // 入金元(validateDtlrcptformではﾁｪｯｸ対象外。故、本箇所でのﾁｪｯｸが必要)
        $key = 'receiptClass';
        if (!isset($errors[$key]) && $data['receiptClass'] == -1) {
            // (未入力 or 不正値)
            $errors[$key] = array("'入金元'を入力してください");
        }

        // 入金予定日と入金日
        $key = 'DepositDate';
        if (!isset($errors[$key]) && (strlen($data[$key]) > 0) && (strlen($data['ReceiptDate']) > 0) && ($data[$key] < $data['ReceiptDate'])) {
            // 入金予定日が入金日より小さいとき
            $errors[$key] = array("'入金予定日'は'入金日'より過去日を入力できません");
        }

        // 注文ID
        $key = 'OrderId';
        if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
            // (未入力チェック)
            $errors[$key] = array("'注文ID'を入力してください");
        }
        if (!isset($errors[$key])) {
            // (存在チェック)
            $row = $this->app->dbAdapter->query(" SELECT OrderSeq FROM T_Order WHERE OrderId = :OrderId ")->execute(array(':OrderId' => $data[$key]))->current();
            if (!$row) {
                $errors[$key] = array("'注文ID'に該当する注文が特定できません");
            }
            else {
                $data['OrderSeq'] = $row['OrderSeq'];
            }
        }
        if (!isset($errors[$key])) {
            // (入金可能チェック : 入金確認待ち、一部入金、入金済みｸﾛｰｽﾞで、有効な立替精算戻し指示がない場合、入金可能)
            $sql = <<<EOQ
SELECT  SUM(CASE WHEN o.DataStatus IN (51, 61) OR (o.DataStatus = 91 AND o.CloseReason = 1) THEN 1 ELSE 0 END ) AS OrderStatus
     ,  SUM(CASE WHEN (pbc.PayDecisionFlg = 0 AND pbc.ValidFlg = 1) THEN 1 ELSE 0 END ) AS PayingBackStatus
FROM    T_Order o
        LEFT OUTER JOIN T_PayingBackControl pbc ON (pbc.OrderSeq = o.OrderSeq)
WHERE   o.P_OrderSeq = :OrderSeq
EOQ;
            $statusRow = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $data['OrderSeq']))->current();
            if (!($statusRow['OrderStatus'] > 0 && $statusRow['PayingBackStatus'] == 0)) {
                $errors[$key] = array("'注文ID'に該当する注文は入金処理ができません");
            }
        }
        if (!isset($errors[$key])) {
            //クレジットカードで支払った注文のチェック
            $sqlCredit = "SELECT COUNT(*) AS cnt FROM AT_Order WHERE ExtraPayType = 1 AND ExtraPayKey IS NOT NULL AND OrderSeq = :OrderSeq";
            $checkCountCredit = $this->app->dbAdapter->query($sqlCredit)->execute(array(':OrderSeq' => $data['OrderSeq']))->current()['cnt'];
            if ($checkCountCredit >= 1) {
                $errors[$key] = array("クレジットカードで支払った注文");
            }
        }

        return $errors;
    }

    /**
     * 注文情報 備考を更新する
     *
     * @param string $odrSeq 注文Seq
     * @param string $usrId ユーザーID
     */
    private function updateIncreNote($odrSeq, $usrId)
    {
        $mdlo = new TableOrder($this->app->dbAdapter);
        $obj = new \models\Table\TableUser($this->app->dbAdapter);
        $userName = $obj->getUserName($usrId);
        $odrData = $mdlo->find($odrSeq)->current();

        $uOrder['Incre_Note'] = $odrData['Incre_Note'] . "\n" . date('Y-m-d') . "@入金有/" . $userName;

        $mdlo->saveUpdateParts($uOrder, $odrSeq);
        return;
    }

    //---------------------------------------------------------------------------------------------
    // 以下、口座振替関連
    //---------------------------------------------------------------------------------------------
    /**
     * 振替結果インポートフォームの表示
     */
    public function impacctrnsformAction()
    {
        $this->setPageTitle("後払い.com - 振替結果インポート");

        return $this->view;
    }

    /**
     * 振替結果インポート
     */
    public function impacctrnsAction()
    {
        // 選択ファイルなし
        if ($_FILES["cres"]["name"] == "") {
            $errormessage = '<h4 style="color:red;">振替結果ファイルが指定されていません。' . "</h4>";
            $this->view->assign('errormessage', $errormessage);
            $this->setTemplate('impacctrnsform');
            return $this->view;
        }

        // 取込み可能ファイルか？の確認
        $mdliatf = new \models\Table\TableImportedAccountTransferFile($this->app->dbAdapter);
        if (!$mdliatf->isCanImport($_FILES["cres"]["name"], 1)) {
            $errormessage = '<h4 style="color:red;">既に取込み済みのファイルです。' . "</h4>";
            $this->view->assign('errormessage', $errormessage);
            $this->setTemplate('impacctrnsform');
            return $this->view;
        }

        // ファイル一時保存ディレクトリへコピー
        $mdlsp = new \models\Table\TableSystemProperty($this->app->dbAdapter);
        $transDir = $mdlsp->getValue(\models\Table\TableSystemProperty::DEFAULT_MODULE, 'systeminfo', 'TempFileDir');
        $savePathFileName = $transDir . '/' . $_FILES["cres"]["name"];
        if (file_exists($savePathFileName)) {
            unlink($savePathFileName);
        }
        copy($_FILES["cres"]["tmp_name"], $savePathFileName);

        // バッチ(importAccoutTransfer.php)非同期呼出し
        //zzz LINUX側で要検証(20191220_1340)
        if (\Coral\Base\BaseProcessInfo::isWin()) {
            $fp = popen('start php ./tools/importAccoutTransfer.php ' . $_FILES["cres"]["name"], 'r');
            pclose($fp);
        }
        else {
            exec('php ./tools/importAccoutTransfer.php ' . $_FILES["cres"]["name"] . ' > /dev/null &');
        }

        sleep(1); // 画面遷移後の初期登録を待つ
        return $this->_redirect('rwrcptcfm/impacctrnslist');
    }

    /**
     * 振替結果一覧フォームの表示
     */
    public function impacctrnslistAction()
    {
        $this->setPageTitle("後払い.com - 振替結果一覧");

        $ri = $this->app->dbAdapter->query(" SELECT * FROM T_ImportedAccountTransferFile WHERE CreditTransferFlg=1 ORDER BY RegistDate DESC ")->execute(null);
        if ($ri->count() > 0) {
            $this->view->assign("acctrnslist", ResultInterfaceToArray($ri));
        }

        return $this->view;
    }

    /**
     * 振替結果詳細フォームの表示
     */
    public function impacctrnsdtlAction()
    {
        $this->setPageTitle("後払い.com - 振替結果詳細");

        $params = $this->getParams();

        $ri = $this->app->dbAdapter->query(" SELECT FileName, ReceiptResult FROM T_ImportedAccountTransferFile WHERE Seq = :Seq ")->execute(array(':Seq' => (int)$params['seq']));
        if ($ri->count() > 0) {
            $row = $ri->current();
            $receiptresult = \Zend\Json\Json::decode($row['ReceiptResult'], \Zend\Json\Json::TYPE_ARRAY);

            if (isset($receiptresult['summary'])) {
                $this->view->assign("summary", $receiptresult['summary']);
            }
            if (isset($receiptresult['infodata'])) {
                $this->view->assign("infodata", $receiptresult['infodata']);
            }
            if (isset($receiptresult['infodata2'])) {
                $this->view->assign("infodata2", $receiptresult['infodata2']);
            }
            if (isset($receiptresult['errordata'])) {
                $errormessage = '<h4 style="color:red;">処理中にエラーが発生したため全件の入金をキャンセルしました。<br/>詳細　：　' . f_e($receiptresult['errordata'][0]) . "</h4>";
                $this->view->assign("errormessage", $errormessage);
            }

            $this->view->assign("filename", $row['FileName']);
        }

        return $this->view;
    }

    /**
     * 振替請求データ作成フォームの表示
     */
    public function creacctrnsformAction()
    {
        $this->setPageTitle("後払い.com - 振替請求データ作成");

        return $this->view;
    }

    /**
     * 振替請求データ作成
     */
    public function creacctrnsAction()
    {
        // バッチ(createAccoutTransfer.php)非同期呼出し
        //zzz LINUX側で要検証(20191220_1340)
        if (\Coral\Base\BaseProcessInfo::isWin()) {
            $fp = popen('start php ./tools/createAccoutTransfer.php', 'r');
            pclose($fp);
        }
        else {
            exec('php ./tools/createAccoutTransfer.php > /dev/null &');
        }

        sleep(1); // 画面遷移後の初期登録を待つ
        return $this->_redirect('rwrcptcfm/dlacctrnslist');
    }

    /**
     * 振替請求データダウンロードフォームの表示
     */
    public function dlacctrnslistAction()
    {
        $this->setPageTitle("後払い.com - 振替請求データダウンロード");

        $ri = $this->app->dbAdapter->query(" SELECT * FROM T_ClaimAccountTransferFile WHERE CreditTransferFlg=1 ORDER BY RegistDate DESC ")->execute(null);
        if ($ri->count() > 0) {
            $this->view->assign("dlacctrnslist", ResultInterfaceToArray($ri));
        }

        return $this->view;
    }

    /**
     * 振替請求データダウンロード
     * (例):rwrcptcfm/dlacctrns/seq/1
     */
    public function dlacctrnsAction()
    {
        $params = $this->getParams();

        $mdlsp = new \models\Table\TableSystemProperty($this->app->dbAdapter);
        $transDir = $mdlsp->getValue(\models\Table\TableSystemProperty::DEFAULT_MODULE, 'systeminfo', 'TempFileDir');

        $row = $this->app->dbAdapter->query(" SELECT FileName, ClaimFile FROM T_ClaimAccountTransferFile WHERE Seq = :Seq "
        )->execute(array(':Seq' => $params['seq']))->current();

        $filename = $row['FileName'] . '.zip';
        $fileBlob = $row['ClaimFile'];

        // ファイルフルパス
        $pathFileName = $transDir . '/' . $filename;

        // 同名ファイルがある場合はファイル削除
        if (file_exists($pathFileName)) {
            unlink($pathFileName);
        }

        // ファイルに保存
        file_put_contents($pathFileName, $fileBlob);

        // レスポンスヘッダの出力
        $filename = mb_convert_encoding($filename, 'sjis-win');
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=$filename");

        // データ出力
        readfile($pathFileName);

        unlink($pathFileName);

        //zzz ↓ 本段階で[Status]を[作成済(ﾀﾞｳﾝﾛｰﾄﾞ済)]に、変更しようかな？

        return $this->response;
    }

    /**
     * 振替結果(振替不能)情報CSVダウンロード
     * SMBC,みずほ,MUFJ
     */
    public function csvdownloadAction()
    {
        $params = $_GET['csv'];

        $mdlsp = new \models\Table\TableSystemProperty($this->app->dbAdapter);
        $category = 'systeminfo';
        $name = 'TempFileDir';
        $transDir = $mdlsp->getValue(\models\Table\TableSystemProperty::DEFAULT_MODULE, $category, $name);

        $filename = '';
        $fileBlob = null;

        $fileBlob = $this->app->dbAdapter->query(" SELECT csv FROM T_ImportedAccountTransferFile WHERE FileName = :FileName "
            )->execute(array(':FileName' => $params))->current()['csv'];
        $filename = '振替結果(振替不能)情報_' . $params . '.csv';
        
        // ファイルフルパス
        $pathFileName = $transDir . '/' . $filename;

        // 同名ファイルがある場合はファイル削除
        if (file_exists($pathFileName)) {
            unlink($pathFileName);
        }

        // ファイルに保存
        file_put_contents($pathFileName, $fileBlob);

        // レスポンスヘッダの出力
        $filename = mb_convert_encoding($filename, 'sjis-win');
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=$filename");

        // データ出力
        readfile($pathFileName);

        unlink($pathFileName);

        

        return $this->response;
    }

    /**
     * 振替結果(MUFJ)インポートフォームの表示
     */
    public function impacctrnsmufjformAction()
    {
        $this->setPageTitle("後払い.com - 振替結果(MUFJ)インポート");

        return $this->view;
    }

    /**
     * 振替結果(MUFJ)インポート
     */
    public function impacctrnsmufjAction()
    {
        // 選択ファイルなし
        if ($_FILES["cres"]["name"] == "") {
            $errormessage = '<h4 style="color:red;">振替結果ファイルが指定されていません。' . "</h4>";
            $this->view->assign('errormessage', $errormessage);
            $this->setTemplate('impacctrnsmufjform');
            return $this->view;
        }

        // 取込み可能ファイルか？の確認
        $mdliatf = new \models\Table\TableImportedAccountTransferFile($this->app->dbAdapter);
        if (!$mdliatf->isCanImport($_FILES["cres"]["name"], 2)) {
            $errormessage = '<h4 style="color:red;">既に取込み済みのファイルです。' . "</h4>";
            $this->view->assign('errormessage', $errormessage);
            $this->setTemplate('impacctrnsform');
            return $this->view;
        }

        // ファイル一時保存ディレクトリへコピー
        $mdlsp = new \models\Table\TableSystemProperty($this->app->dbAdapter);
        $transDir = $mdlsp->getValue(\models\Table\TableSystemProperty::DEFAULT_MODULE, 'systeminfo', 'TempFileDir');
        $savePathFileName = $transDir . '/' . $_FILES["cres"]["name"];
        if (file_exists($savePathFileName)) {
            unlink($savePathFileName);
        }
        copy($_FILES["cres"]["tmp_name"], $savePathFileName);

        // バッチ(importAccoutTransfer.php)非同期呼出し
        //zzz LINUX側で要検証(20191220_1340)
        if (\Coral\Base\BaseProcessInfo::isWin()) {
            $fp = popen('start php ./tools/importAccoutTransferMufj.php ' . $_FILES["cres"]["name"], 'r');
            pclose($fp);
        }
        else {
            exec('php ./tools/importAccoutTransferMufj.php ' . $_FILES["cres"]["name"] . ' > /dev/null &');
        }

        sleep(1); // 画面遷移後の初期登録を待つ
        return $this->_redirect('rwrcptcfm/impacctrnsmufjlist');
    }

    /**
     * 振替結果(MUFJ)一覧フォームの表示
     */
    public function impacctrnsmufjlistAction()
    {
        $this->setPageTitle("後払い.com - 振替結果(MUFJ)一覧");

        $ri = $this->app->dbAdapter->query(" SELECT * FROM T_ImportedAccountTransferFile WHERE CreditTransferFlg=2 ORDER BY RegistDate DESC ")->execute(null);
        if ($ri->count() > 0) {
            $this->view->assign("acctrnslist", ResultInterfaceToArray($ri));
        }

        return $this->view;
    }

    /**
     * 振替結果詳細(MUFJ)フォームの表示
     */
    public function impacctrnsmufjdtlAction()
    {
        $this->setPageTitle("後払い.com - 振替結果(MUFJ)詳細");

        $params = $this->getParams();

        $ri = $this->app->dbAdapter->query(" SELECT FileName, ReceiptResult FROM T_ImportedAccountTransferFile WHERE Seq = :Seq ")->execute(array(':Seq' => (int)$params['seq']));
        if ($ri->count() > 0) {
            $row = $ri->current();
            $receiptresult = \Zend\Json\Json::decode($row['ReceiptResult'], \Zend\Json\Json::TYPE_ARRAY);

            if (isset($receiptresult['summary'])) {
                $this->view->assign("summary", $receiptresult['summary']);
            }
            if (isset($receiptresult['infodata'])) {
                $this->view->assign("infodata", $receiptresult['infodata']);
            }
            if (isset($receiptresult['infodata2'])) {
                $this->view->assign("infodata2", $receiptresult['infodata2']);
            }
            if (isset($receiptresult['errordata'])) {
                $errormessage = '<h4 style="color:red;">処理中にエラーが発生したため全件の入金をキャンセルしました。<br/>詳細　：　' . f_e($receiptresult['errordata'][0]) . "</h4>";
                $this->view->assign("errormessage", $errormessage);
            }

            $this->view->assign("filename", $row['FileName']);
        }

        return $this->view;
    }

    /**
     * 振替請求（MUFJ）データ作成フォームの表示
     */
    public function creacctrnsmufjformAction()
    {
        $this->setPageTitle("後払い.com - 振替請求データ（MUFJ）作成");

        return $this->view;
    }

    /**
     * 振替請求（MUFJ）データ作成
     */
    public function creacctrnsmufjAction()
    {
        // バッチ(createAccoutTransfer.php)非同期呼出し
        //zzz LINUX側で要検証(20191220_1340)
        if (\Coral\Base\BaseProcessInfo::isWin()) {
            $fp = popen('start php ./tools/createAccoutTransferMufj.php', 'r');
            pclose($fp);
        }
        else {
            exec('php ./tools/createAccoutTransferMufj.php > /dev/null &');
        }

        sleep(1); // 画面遷移後の初期登録を待つ
        return $this->_redirect('rwrcptcfm/dlacctrnsmufjlist');
    }

    /**
     * 振替請求データ（MUFJ）ダウンロードフォームの表示
     */
    public function dlacctrnsmufjlistAction()
    {
        $this->setPageTitle("後払い.com - 振替請求データ（MUFJ）ダウンロード");

        $ri = $this->app->dbAdapter->query(" SELECT * FROM T_ClaimAccountTransferFile WHERE CreditTransferFlg=2 ORDER BY RegistDate DESC ")->execute(null);
        if ($ri->count() > 0) {
            $this->view->assign("dlacctrnslist", ResultInterfaceToArray($ri));
        }

        return $this->view;
    }

    /**
     * 振替請求データダウンロード
     * (例):rwrcptcfm/dlacctrnsmufj/seq/1
     */
    public function dlacctrnsmufjAction()
    {
        $params = $this->getParams();

        $mdlsp = new \models\Table\TableSystemProperty($this->app->dbAdapter);
        $transDir = $mdlsp->getValue(\models\Table\TableSystemProperty::DEFAULT_MODULE, 'systeminfo', 'TempFileDir');

        $row = $this->app->dbAdapter->query(" SELECT FileName, ClaimFile FROM T_ClaimAccountTransferFile WHERE Seq = :Seq "
        )->execute(array(':Seq' => $params['seq']))->current();

        $filename = $row['FileName'] . '.zip';
        $fileBlob = $row['ClaimFile'];

        // ファイルフルパス
        $pathFileName = $transDir . '/' . $filename;

        // 同名ファイルがある場合はファイル削除
        if (file_exists($pathFileName)) {
            unlink($pathFileName);
        }

        // ファイルに保存
        file_put_contents($pathFileName, $fileBlob);

        // レスポンスヘッダの出力
        $filename = mb_convert_encoding($filename, 'sjis-win');
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=$filename");

        // データ出力
        readfile($pathFileName);

        unlink($pathFileName);

        //zzz ↓ 本段階で[Status]を[作成済(ﾀﾞｳﾝﾛｰﾄﾞ済)]に、変更しようかな？

        return $this->response;
    }

    /**
     * 振替結果(MHF)インポートフォームの表示
     */
    public function impacctrnsmhfformAction()
    {
        $this->setPageTitle("後払い.com - 振替結果(MHF)インポート");

        return $this->view;
    }

    /**
     * 振替結果(MHF)インポート
     */
    public function impacctrnsmhfAction()
    {
        // 選択ファイルなし
        if ($_FILES["cres"]["name"] == "") {
            $errormessage = '<h4 style="color:red;">振替結果ファイルが指定されていません。' . "</h4>";
            $this->view->assign('errormessage', $errormessage);
            $this->setTemplate('impacctrnsmhfform');
            return $this->view;
        }

        // 取込み可能ファイルか？の確認
        $mdliatf = new \models\Table\TableImportedAccountTransferFile($this->app->dbAdapter);
        if (!$mdliatf->isCanImport($_FILES["cres"]["name"], 3)) {
            $errormessage = '<h4 style="color:red;">既に取込み済みのファイルです。' . "</h4>";
            $this->view->assign('errormessage', $errormessage);
            $this->setTemplate('impacctrnsform');
            return $this->view;
        }

        // ファイル一時保存ディレクトリへコピー
        $mdlsp = new \models\Table\TableSystemProperty($this->app->dbAdapter);
        $transDir = $mdlsp->getValue(\models\Table\TableSystemProperty::DEFAULT_MODULE, 'systeminfo', 'TempFileDir');
        $savePathFileName = $transDir . '/' . $_FILES["cres"]["name"];
        if (file_exists($savePathFileName)) {
            unlink($savePathFileName);
        }
        copy($_FILES["cres"]["tmp_name"], $savePathFileName);

        // バッチ(importAccoutTransfer.php)非同期呼出し
        //zzz LINUX側で要検証(20191220_1340)
        if (\Coral\Base\BaseProcessInfo::isWin()) {
            $fp = popen('start php ./tools/importAccoutTransferMhf.php ' . $_FILES["cres"]["name"], 'r');
            pclose($fp);
        }
        else {
            exec('php ./tools/importAccoutTransferMhf.php ' . $_FILES["cres"]["name"] . ' > /dev/null &');
        }

        sleep(1); // 画面遷移後の初期登録を待つ
        return $this->_redirect('rwrcptcfm/impacctrnsmhflist');
    }

    /**
     * 振替結果(MHF)一覧フォームの表示
     */
    public function impacctrnsmhflistAction()
    {
        $this->setPageTitle("後払い.com - 振替結果(MHF)一覧");

        $ri = $this->app->dbAdapter->query(" SELECT * FROM T_ImportedAccountTransferFile WHERE CreditTransferFlg=3 ORDER BY RegistDate DESC ")->execute(null);
        if ($ri->count() > 0) {
            $this->view->assign("acctrnslist", ResultInterfaceToArray($ri));
        }

        return $this->view;
    }

    /**
     * 振替結果詳細(MHF)フォームの表示
     */
    public function impacctrnsmhfdtlAction()
    {
        $this->setPageTitle("後払い.com - 振替結果(MHF)詳細");

        $params = $this->getParams();

        $ri = $this->app->dbAdapter->query(" SELECT FileName, ReceiptResult FROM T_ImportedAccountTransferFile WHERE Seq = :Seq ")->execute(array(':Seq' => (int)$params['seq']));
        if ($ri->count() > 0) {
            $row = $ri->current();
            $receiptresult = \Zend\Json\Json::decode($row['ReceiptResult'], \Zend\Json\Json::TYPE_ARRAY);

            if (isset($receiptresult['summary'])) {
                $this->view->assign("summary", $receiptresult['summary']);
            }
            if (isset($receiptresult['infodata'])) {
                $this->view->assign("infodata", $receiptresult['infodata']);
            }
            if (isset($receiptresult['infodata2'])) {
                $this->view->assign("infodata2", $receiptresult['infodata2']);
            }
            if (isset($receiptresult['errordata'])) {
                $errormessage = '<h4 style="color:red;">処理中にエラーが発生したため全件の入金をキャンセルしました。<br/>詳細　：　' . f_e($receiptresult['errordata'][0]) . "</h4>";
                $this->view->assign("errormessage", $errormessage);
            }

            $this->view->assign("filename", $row['FileName']);
        }

        return $this->view;
    }

    /**
     * 振替請求（MHF）データ作成フォームの表示
     */
    public function creacctrnsmhfformAction()
    {
        $this->setPageTitle("後払い.com - 振替請求データ（MHF）作成");

        return $this->view;
    }

    /**
     * 振替請求（MHF）データ作成
     */
    public function creacctrnsmhfAction()
    {
        // バッチ(createAccoutTransfer.php)非同期呼出し
        //zzz LINUX側で要検証(20191220_1340)
        if (\Coral\Base\BaseProcessInfo::isWin()) {
            $fp = popen('start php ./tools/createAccoutTransferMhf.php', 'r');
            pclose($fp);
        }
        else {
            exec('php ./tools/createAccoutTransferMhf.php > /dev/null &');
        }

        sleep(1); // 画面遷移後の初期登録を待つ
        return $this->_redirect('rwrcptcfm/dlacctrnsmhflist');
    }

    /**
     * 振替請求データ（MHF）ダウンロードフォームの表示
     */
    public function dlacctrnsmhflistAction()
    {
        $this->setPageTitle("後払い.com - 振替請求データ（MHF）ダウンロード");

        $ri = $this->app->dbAdapter->query(" SELECT * FROM T_ClaimAccountTransferFile WHERE CreditTransferFlg=3 ORDER BY RegistDate DESC ")->execute(null);
        if ($ri->count() > 0) {
            $this->view->assign("dlacctrnslist", ResultInterfaceToArray($ri));
        }

        return $this->view;
    }

    /**
     * 振替請求データダウンロード
     * (例):rwrcptcfm/dlacctrnsmhf/seq/1
     */
    public function dlacctrnsmhfAction()
    {
        $params = $this->getParams();

        $mdlsp = new \models\Table\TableSystemProperty($this->app->dbAdapter);
        $transDir = $mdlsp->getValue(\models\Table\TableSystemProperty::DEFAULT_MODULE, 'systeminfo', 'TempFileDir');

        $row = $this->app->dbAdapter->query(" SELECT FileName, ClaimFile FROM T_ClaimAccountTransferFile WHERE Seq = :Seq "
        )->execute(array(':Seq' => $params['seq']))->current();

        $filename = $row['FileName'] . '.zip';
        $fileBlob = $row['ClaimFile'];

        // ファイルフルパス
        $pathFileName = $transDir . '/' . $filename;

        // 同名ファイルがある場合はファイル削除
        if (file_exists($pathFileName)) {
            unlink($pathFileName);
        }

        // ファイルに保存
        file_put_contents($pathFileName, $fileBlob);

        // レスポンスヘッダの出力
        $filename = mb_convert_encoding($filename, 'sjis-win');
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=$filename");

        // データ出力
        readfile($pathFileName);

        unlink($pathFileName);

        //zzz ↓ 本段階で[Status]を[作成済(ﾀﾞｳﾝﾛｰﾄﾞ済)]に、変更しようかな？

        return $this->response;
    }

    /**
     * 口座情報取込フォームの表示
     */
    public function impaccountinformationformAction()
    {
        return $this->view;
    }

    /**
     * 口座情報取込
     */
    public function impaccountinformationAction()
    {
        //ファイルが選択されているか確認
        $tmpName = $_FILES["cres"]["tmp_name"];
        if ($tmpName == '') {
            $this->view->assign('message', '<span style="font-size: 18px; color: red;">口座情報振替申込結果ファイルが選択されていません。<br />再試行してください。</span>');
            return $this->view;
        }

        $edata = array();   // エラーデータ
        $idata = array();   // 正常データ

        $handle = null;
        try {
            // ユーザーIDの取得
            $obj = new \models\Table\TableUser($this->app->dbAdapter);
            $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

            $stm = $this->app->dbAdapter->query($this->getBaseP_ReceiptControl());

            // SQL実行結果取得用のSQL
            $getretvalsql = " SELECT @po_ret_sts AS po_ret_sts, @po_ret_errcd AS po_ret_errcd, @po_ret_sqlcd AS po_ret_sqlcd, @po_ret_msg AS po_ret_msg ";

            $buf = mb_convert_encoding(file_get_contents($tmpName), 'utf-8', 'sjis-win');
            if (!$buf) {
                // ファイルオープン失敗
                $message = '<span style="font-size: 18px; color: red;">口座情報振替申込結果ファイルのオープンに失敗しました。<br />再試行してください。</span>';
            } else {
                $this->app->logger->info(' impdtlAction start(' . $_FILES["cres"]["name"] . ' / filesize : ' . filesize($tmpName) . ') ');
                //トランザクション開始
                $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();

                $handle = tmpfile();
                fwrite($handle, $buf);
                rewind($handle);

                // 口座情報取込ループ
                $datas = array();
                // (チェックループ)
                $rowCount = 1;
                //正常処理のカウント変数
                $dataCount = 0;
                while (($row = fgetcsv($handle, 3000, ",")) !== false) {
                    $data = array(
                            //口座振替受付ステータス
                            'CreditTransferAcceptStatus'           => $row[39],
                            //顧客番号エラーフラグ
                            'CustomerNumberErrorFlag'           => $row[53],
                            //顧客番号エラーメッセージ
                            'CustomerNumberErrorMessage'     => $row[54],
                            //口座名義エラーフラグ
                            'AccountHolderErrorFlag'       => $row[55],
                            //口座名義エラーメッセージ
                            'AccountHolderErrorMessage'     => $row[56],
                            //顧客番号
                            'CustomerNumber'           => $row[58],
                            //金融機関名
                            'FfName'           => $row[60],
                            //金融機関コード
                            'FfCode'     => $row[59],
                            //支店名
                            'FfBranchName'       => $row[62],
                            //支店コード
                            'FfBranchCode'     => $row[61],
                            //預金種目コード
                            'FfAccountClass'        => $row[63],
                            //口座番号
                            'FfAccountNumber'   => $row[65],
                            //口座名義
                            'FfAccountName'      => $row[66],
                            //口座名義
                            'RequestCompScheduleDate' => date('Y-m-d H:i:s'),
                            //更新者
                            'UpdateId'     => $userId,
                            //行番号
                            'LineNumber'     => $rowCount,
                    );
                    if($data['CreditTransferAcceptStatus'] != 02){
                        continue;
                    }
                    //エラーチェック
                    $errors = $this->validateImpAccountInformation($data);

                    // count関数対策
                    if (!empty($errors)) {
                        // エラーあり ⇒ エラーデータへ積上げる
                        $edata[] = array( 'LineNumber' => $rowCount, 'CustomerNumber' => $data['CustomerNumber'], 'ErrorInfo' => $errors );
                    }else {
                        $customerNumber = $data ['CustomerNumber'];

                        // 顧客番号に合致する加盟店顧客を特定
                        $sql = "SELECT EntCustSeq, RequestStatus FROM T_EnterpriseCustomer WHERE EntCustSeq = :CustomerNumber AND ValidFlg = 1 ";
                        $enterpriseCustomer = $this->app->dbAdapter->query ( $sql )->execute ( array (
                                ':CustomerNumber' => $customerNumber
                        ) )->current ();

                        $key = 'CustomerNumber';
                        // 検索処理で顧客番号を取得できなかった場合
                        if (empty ( $enterpriseCustomer['EntCustSeq'] )) {
                            $error1['CustomerNumber'] = array (
                                    "顧客番号が登録されていません。"
                            );
                            // エラーあり ⇒ エラーデータへ積上げる
                            $edata [] = array (
                                    'LineNumber' => $data['LineNumber'],
                                    'CustomerNumber' => $customerNumber,
                                    'ErrorInfo' => $error1
                            );
                            $rowCount++;
                            continue;
                        }
                        // 申込ステータスがnullもしくは、"9"：中止でない場合
                        if (!(is_null($enterpriseCustomer['RequestStatus']) || $enterpriseCustomer['RequestStatus'] == 9 || $enterpriseCustomer['RequestStatus'] == 1)) {
                            $error2['CustomerNumber'] = array (
                                    "既に登録されています。"
                            );
                            // エラーあり ⇒ エラーデータへ積上げる
                            $edata [] = array (
                                    'LineNumber' => $data['LineNumber'],
                                    'CustomerNumber' => $customerNumber,
                                    'ErrorInfo' => $error2
                            );
                            $rowCount++;
                            continue;
                        }
                        //申込ステータス:2(完了)
                        $data['RequestStatus'] = 2;
                        $data['RequestSubStatus'] = 0;
                        $data['RequestCompDate'] = date('Y-m-d');
                        // 加盟店顧客の更新
                        $TableEnterpriseCustomer = new TableEnterpriseCustomer( $this->app->dbAdapter );
                        $result = $TableEnterpriseCustomer->saveUpdate ($data,$enterpriseCustomer['EntCustSeq']);
                        $dataCount++;

                    }
                    $rowCount++;
//                     //ロールバック確認用のコード
//                    throw new \Exception();
                }

                $this->app->dbAdapter->getDriver()->getConnection()->commit();

                $message = sprintf("口座情報取込ファイル　「%s」　をインポートしました。（処理件数：" . " $dataCount" . "　件）", f_e($_FILES["cres"]["name"]));
                $this->app->logger->info(' impdtlAction completed(' . $message . ') ');

            }
        } catch(\Exception $e) {
            $this->app->dbAdapter->getDriver()->getConnection()->rollBack();
            $message = '<h4 style="color:red;">例外が発生したため処理を実行できませんでした。システム管理者に連絡してください。 </h4>';
            $this->view->assign('isException', true);   // 例外が発生したことをビューへ通知
        }
        if ($handle) { fclose($handle); }   // ファイルハンドルが有効であればクローズ処理
        $this->view->assign('message', $message);
        $this->view->assign('edata', $edata);
        $this->view->assign('idata', $idata);

        return $this->view;
    }

    /**
     *入力値エラーがないか確認する
     *
     * @param array $data 詳細入金データ
     * @return array エラーメッセージの配列
     */
    protected function validateImpAccountInformation(array $data)
    {

        //顧客番号エラーフラグ
        $key = 'CustomerNumberErrorFlag';
        if ( !isset($errors[$key]) && !( $data[$key] === "" || $data[$key] == "0" || $data[$key] == "1") ) {
            $errors[$key] = array("データが不正です。（顧客番号エラーフラグ)");
        }

        //顧客番号エラーフラグが"1"の場合
        $key = 'CustomerNumberErrorFlag';
        if (!isset($errors[$key]) && $data[$key] == 1) {
            // (未入力 or 不正値)
            $errors[$key] = array("連携時エラー（" . $data['CustomerNumberErrorMessage'] . ")");
        }

        //口座名義エラーフラグ
        $key = 'AccountHolderErrorFlag';
        if (!isset($errors[$key]) && !( $data[$key] === "" || $data[$key] == "0" || $data[$key] == "1") ) {
            $errors[$key] = array("データが不正です。（口座名義エラーフラグ)");
        }

        //口座名義エラーフラグが"1"の場合
        $key = 'AccountHolderErrorFlag';
        if (!isset($errors[$key]) && $data[$key] == 1) {
            // (未入力 or 不正値)
            $errors[$key] = array("連携時エラー（" . $data['AccountHolderErrorMessage'] . ")");
        }

        //顧客番号が空白の場合
        $key = 'CustomerNumber';
        if (!isset($errors[$key]) && $data[$key] === "") {
            $errors[$key] = array("顧客番号が設定されていません。");
        }

        //顧客番号
        $key = 'CustomerNumber';
        if (!isset($errors[$key]) && ( !preg_match("/[0-9]+$/", ($data[$key])))) {
              $errors[$key] = array("データが不正です。（顧客番号)");
        }

        //金融機関コード
        $key = 'FfCode';
        if (!isset($errors[$key]) && (!preg_match("/^[0-9]{4}$/", ($data[$key])))) {
            $errors[$key] = array("データが不正です。（金融機関コード)");
        }

        // 支店コード
        $key = 'FfBranchCode';
        if (! empty ( $data [$key] )) {
            if (!isset($errors[$key]) &&(!preg_match("/^[0-9]{3}$/", ($data[$key])))) {
                $errors [$key] = array ("データが不正です。（支店コード)");
            }
        }

        //預金種目コード
        $key = 'FfAccountClass';
        if (!isset($errors[$key]) && !( $data[$key] === ""  || $data[$key] == "1" || $data[$key] == "2") ) {
            $errors[$key] = array("データが不正です。（預金種目コード)");
        }

        // 口座番号
        $key = 'FfAccountNumber';
        if ($data[$key] !== "") {
            if (! isset ( $errors [$key] ) && (!preg_match("/^[0-9]{7}$/", ($data[$key])))) {
                $errors [$key] = array ("データが不正です。（口座番号)");
            }
        }

        // 口座名義
        $key = 'FfAccountName';
        if ($data[$key] !== "") {
            //mb_strlen:全角、半角を1としてカウント
            $len = mb_strlen($data[$key], "UTF-8");
            //mb_strlen:全角を2、半角を1としてカウント
            $wdt = mb_strwidth($data[$key], "UTF-8");
            if (! isset ( $errors [$key] ) &&($len != $wdt)) {
                $errors [$key] = array ("データが不正です。（口座名義)");
            }
        }
        return $errors;
    }

    //---------------------------------------------------------------------------------------------
    // 以下、NTTスマートトレード関連
    //---------------------------------------------------------------------------------------------
    /**
     * NTTスマートトレードインポートフォームの表示
     */
    public function impnttstformAction()
    {
        $this->setPageTitle("後払い.com - NTTスマートトレードインポート");

        return $this->view;
    }

    /**
     * NTTスマートトレードインポート
     */
    public function impnttstAction()
    {
        // 選択ファイルなし
        if ($_FILES["cres"]["name"] == "") {
            $errormessage = '<h4 style="color:red;">NTTスマートトレード結果ファイルが指定されていません。' . "</h4>";
            $this->view->assign('errormessage', $errormessage);
            $this->setTemplate('impnttstform');
            return $this->view;
        }

        // 取込み可能ファイルか？の確認
        $mdlinst = new \models\Table\TableImportedNttSmartTrade($this->app->dbAdapter);
        if (!$mdlinst->isCanImport($_FILES["cres"]["name"])) {
            $errormessage = '<h4 style="color:red;">既に取込み済みのファイルです。' . "</h4>";
            $this->view->assign('errormessage', $errormessage);
            $this->setTemplate('impnttstform');
            return $this->view;
        }

        // ファイル一時保存ディレクトリへコピー
        $mdlsp = new \models\Table\TableSystemProperty($this->app->dbAdapter);
        $transDir = $mdlsp->getValue(\models\Table\TableSystemProperty::DEFAULT_MODULE, 'systeminfo', 'TempFileDir');
        $savePathFileName = $transDir . '/' . $_FILES["cres"]["name"];
        if (file_exists($savePathFileName)) {
            unlink($savePathFileName);
        }
        copy($_FILES["cres"]["tmp_name"], $savePathFileName);

        // バッチ(importNttSmartTrade.php)非同期呼出し
        if (\Coral\Base\BaseProcessInfo::isWin()) {
            $fp = popen('start php ./tools/importNttSmartTrade.php ' . $_FILES["cres"]["name"], 'r');
            pclose($fp);
        }
        else {
            exec('php ./tools/importNttSmartTrade.php ' . $_FILES["cres"]["name"] . ' > /dev/null &');
        }

        sleep(1); // 画面遷移後の初期登録を待つ
        return $this->_redirect('rwrcptcfm/impnttstlist');
    }

    /**
     * NTTスマートトレード結果一覧フォームの表示
     */
    public function impnttstlistAction()
    {
        $this->setPageTitle("後払い.com - NTTスマートトレード結果一覧");

        $ri = $this->app->dbAdapter->query(" SELECT * FROM T_ImportedNttSmartTrade ORDER BY RegistDate DESC ")->execute(null);
        if ($ri->count() > 0) {
            $this->view->assign("nttstlist", ResultInterfaceToArray($ri));
        }

        return $this->view;
    }

    /**
     * NTTスマートトレード結果詳細フォームの表示
     */
    public function impnttstdtlAction()
    {
        $this->setPageTitle("後払い.com - NTTスマートトレード結果詳細");

        $params = $this->getParams();

        $ri = $this->app->dbAdapter->query(" SELECT FileName, ReceiptResult FROM T_ImportedNttSmartTrade WHERE Seq = :Seq ")->execute(array(':Seq' => (int)$params['seq']));
        if ($ri->count() > 0) {
            $row = $ri->current();
            $receiptresult = \Zend\Json\Json::decode($row['ReceiptResult'], \Zend\Json\Json::TYPE_ARRAY);

            if (isset($receiptresult['summary'])) {
                $this->view->assign("summary", $receiptresult['summary']);
            }
            if (isset($receiptresult['edata'])) {
                $this->view->assign("edata", $receiptresult['edata']);
            }
            if (isset($receiptresult['cdata'])) {
                $this->view->assign("cdata", $receiptresult['cdata']);
            }
            if (isset($receiptresult['adata'])) {
                $this->view->assign("adata", $receiptresult['adata']);
            }
            if (isset($receiptresult['errordata'])) {
                $errormessage = '<h4 style="color:red;">処理中にエラーが発生したため全件の入金をキャンセルしました。<br/>詳細　：　' . f_e($receiptresult['errordata'][0]) . "</h4>";
                $this->view->assign("errormessage", $errormessage);
            }

            $this->view->assign("filename", $row['FileName']);
        }

        return $this->view;
    }

    //---------------------------------------------------------------------------------------------
    // 以下、届いてから払い関連
    //---------------------------------------------------------------------------------------------
    /**
     * 届いてから払いインポートフォームの表示
     */
    public function imppaymentafterformAction()
    {
        $this->setPageTitle( '後払い.com - 届いてから払いインポート' );

        return $this->view;
    }

    /**
     * 届いてから払いインポート
     */
    public function impPaymentAfterArrivalAction()
    {
        // 選択ファイルなし
        if ($_FILES["cres"]["name"] == "") {
            $errormessage = '<h4 style="color:red;">届いてから払い結果ファイルが指定されていません。'. '</h4>';
            $this->view->assign( 'errormessage', $errormessage );
            $this->setTemplate( 'imppaymentafterform' );
            return $this->view;
        }

        // 取込み可能ファイルか？の確認
        $mdlinst = new \models\Table\TableImportedPaymentAfterArrival($this->app->dbAdapter);
        if (!$mdlinst->isCanImport($_FILES["cres"]["name"])) {
            $errormessage = '<h4 style="color:red;">既に取込み済みのファイルです。' . "</h4>";
            $this->view->assign('errormessage', $errormessage);
            $this->setTemplate('imppaymentafterform');
            return $this->view;
        }

        // ファイル一時保存ディレクトリへコピー
        $mdlsp = new \models\Table\TableSystemProperty($this->app->dbAdapter);
        $transDir = $mdlsp->getValue(\models\Table\TableSystemProperty::DEFAULT_MODULE, 'systeminfo', 'TempFileDir');
        $savePathFileName = $transDir . '/' . $_FILES["cres"]["name"];
        if (file_exists($savePathFileName)) {
            unlink($savePathFileName);
        }
        copy($_FILES["cres"]["tmp_name"], $savePathFileName);

        // バッチ(importPaymentAfterArrival.php)非同期呼出し
        if (\Coral\Base\BaseProcessInfo::isWin()) {
            $fp = popen('start php ./tools/importPaymentAfterArrival.php ' . $_FILES["cres"]["name"], 'r');
            pclose($fp);
        }
        else {
            exec('php ./tools/importPaymentAfterArrival.php ' . $_FILES["cres"]["name"] . ' > /dev/null &');
        }

        sleep(1); // 画面遷移後の初期登録を待つ
        return $this->_redirect('rwrcptcfm/imppaymentafterlist');
    }

    /**
     * 届いてから払い結果一覧フォームの表示
     */
    public function imppaymentafterlistAction()
    {
        $this->setPageTitle( '後払い.com - 届いてから払い結果一覧' );

        $sql = 'SELECT * FROM T_ImportedPaymentAfterArrival ORDER BY RegistDate DESC';
        $ri = $this->app->dbAdapter->query( $sql )->execute( null );
        if ($ri->count() > 0) {
            $this->view->assign( 'imppaymentafterlist', ResultInterfaceToArray( $ri ) );
        }

        return $this->view;
    }

    /**
     * 届いてから払い結果詳細フォームの表示
     */
    public function imppaymentafterdtlAction()
    {
        $this->setPageTitle("後払い.com - 届いてから払い結果詳細");

        $params = $this->getParams();

        // 結果データの取得
        $sql = 'SELECT FileName, ReceiptResult FROM T_ImportedPaymentAfterArrival WHERE Seq = :Seq';
        $prm = array(
            ':Seq' => (int)$params['seq']
        );
        $ri = $this->app->dbAdapter->query( $sql )->execute( $prm );

        // 取得できた場合
        if ( $ri->count() > 0 ) {
            $row = $ri->current();
            $receiptresult = \Zend\Json\Json::decode( $row['ReceiptResult'], \Zend\Json\Json::TYPE_ARRAY );

            // 正常処理分がある場合
            if ( isset( $receiptresult['summary'] ) ) {
                $this->view->assign('summary', $receiptresult['summary'] );
            }

            // エラー処理分がある場合
            if ( isset( $receiptresult['edata'] ) ) {
                $this->view->assign( 'edata', $receiptresult['edata'] );
            }

            // 返品処理分がある場合
            if ( isset( $receiptresult['rdata'] ) ) {
                $this->view->assign( 'rdata', $receiptresult['rdata'] );
            }

            //
            if ( isset( $receiptresult['errordata'] ) ) {
                $errormessage = '<h4 style="color:red;">処理中にエラーが発生したため全件の入金をキャンセルしました。';
                $errormessage .= '<br/>詳細　：　'. f_e( $receiptresult['errordata'][0] ). '</h4>';
                $this->view->assign( 'errormessage', $errormessage );
            }

            $this->view->assign( 'filename', $row['FileName'] );
        }

        return $this->view;
    }

    //---------------------------------------------------------------------------------------------
    // 以下、みずほファクター関連
    //---------------------------------------------------------------------------------------------
    /**
     * みずほファクターインポートフォームの表示
     */
    public function impmizuhofactorformAction()
    {
        $this->setPageTitle("後払い.com - みずほファクターインポート");

        return $this->view;
    }

    /**
     * みずほファクターインポート
     */
    public function impmizuhofactorAction()
    {
        // 選択ファイルなし
        if ($_FILES["cres"]["name"] == "") {
            $errormessage = '<h4 style="color:red;">みずほファクター結果ファイルが指定されていません。' . "</h4>";
            $this->view->assign('errormessage', $errormessage);
            $this->setTemplate('impmizuhofactorform');
            return $this->view;
        }

        // 取込み可能ファイルか？の確認
        $mdlinst = new \models\Table\TableImportedMizuhoFactor($this->app->dbAdapter);
        if (!$mdlinst->isCanImport($_FILES["cres"]["name"])) {
            $errormessage = '<h4 style="color:red;">既に取込み済みのファイルです。' . "</h4>";
            $this->view->assign('errormessage', $errormessage);
            $this->setTemplate('impmizuhofactorform');
            return $this->view;
        }

        // ファイル一時保存ディレクトリへコピー
        $mdlsp = new \models\Table\TableSystemProperty($this->app->dbAdapter);
        $transDir = $mdlsp->getValue(\models\Table\TableSystemProperty::DEFAULT_MODULE, 'systeminfo', 'TempFileDir');
        $savePathFileName = $transDir . '/' . $_FILES["cres"]["name"];
        if (file_exists($savePathFileName)) {
            unlink($savePathFileName);
        }
        copy($_FILES["cres"]["tmp_name"], $savePathFileName);
        // バッチ(importMizuhoFactor.php)非同期呼出し
        if (\Coral\Base\BaseProcessInfo::isWin()) {
            $fp = popen('start php ./tools/importMizuhoFactor.php ' . $_FILES["cres"]["name"], 'r');
            pclose($fp);
        }
        else {
            exec('php ./tools/importMizuhoFactor.php ' . $_FILES["cres"]["name"] . ' > /dev/null &');
        }

        sleep(1); // 画面遷移後の初期登録を待つ
        return $this->_redirect('rwrcptcfm/impmizuhofactorlist');
    }

    /**
     * みずほファクター結果一覧フォームの表示
     */
    public function impmizuhofactorlistAction()
    {
        $this->setPageTitle("後払い.com - みずほファクター結果一覧");

        $ri = $this->app->dbAdapter->query("SELECT * FROM T_ImportedMizuhoFactor ORDER BY RegistDate DESC")->execute(null);
        if ($ri->count() > 0) {
            $this->view->assign("mizuhofactorlist", ResultInterfaceToArray($ri));
        }

        return $this->view;
    }

    /**
     * みずほファクター結果詳細フォームの表示
     */
    public function impmizuhofactordtlAction()
    {
        $this->setPageTitle("後払い.com - みずほファクター結果詳細");

        $params = $this->getParams();

        $ri = $this->app->dbAdapter->query("SELECT FileName, ReceiptResult FROM T_ImportedMizuhoFactor WHERE Seq = :Seq ")->execute(array(':Seq' => (int)$params['seq']));
        if ($ri->count() > 0) {
            $row = $ri->current();
            $receiptresult = \Zend\Json\Json::decode($row['ReceiptResult'], \Zend\Json\Json::TYPE_ARRAY);

            if (isset($receiptresult['summary'])) {
                $this->view->assign("summary", $receiptresult['summary']);
            }
            if (isset($receiptresult['edata'])) {
                $this->view->assign("edata", $receiptresult['edata']);
            }
            if (isset($receiptresult['cdata'])) {
                $this->view->assign("cdata", $receiptresult['cdata']);
            }
            if (isset($receiptresult['adata'])) {
                $this->view->assign("adata", $receiptresult['adata']);
            }
            if (isset($receiptresult['errordata'])) {
                $errormessage = '<h4 style="color:red;">処理中にエラーが発生したため全件の入金をキャンセルしました。<br/>詳細　：　' . f_e($receiptresult['errordata'][0]) . "</h4>";
                $this->view->assign("errormessage", $errormessage);
            }

            $this->view->assign("filename", $row['FileName']);
        }

        return $this->view;
    }


    //---------------------------------------------------------------------------------------------
    // 以下、SMBCパーフェクト口座関連
    //---------------------------------------------------------------------------------------------
    /**
     * SMBCパーフェクト口座インポートフォームの表示
     */
    public function impsmbcperfectformAction()
    {
        $this->setPageTitle( '後払い.com - SMBCパーフェクト口座インポート' );

        return $this->view;
    }

    /**
     * SMBCパーフェクト口座インポート
     */
    public function impsmbcperfectAction()
    {
        // 選択ファイルなし
        if ($_FILES["cres"]["name"] == "") {
            $errormessage = '<h4 style="color:red;">SMBCパーフェクト口座結果ファイルが指定されていません。'. '</h4>';
            $this->view->assign( 'errormessage', $errormessage );
            $this->setTemplate( 'impsmbcperfectform' );
            return $this->view;
        }

        // 取込み可能ファイルか？の確認
        $mdlinst = new \models\Table\TableImportedSmbcPerfect($this->app->dbAdapter);
        if (!$mdlinst->isCanImport($_FILES["cres"]["name"])) {
            $errormessage = '<h4 style="color:red;">既に取込み済みのファイルです。' . "</h4>";
            $this->view->assign('errormessage', $errormessage);
            $this->setTemplate('impsmbcperfectform');
            return $this->view;
        }

        // ファイル一時保存ディレクトリへコピー
        $mdlsp = new \models\Table\TableSystemProperty($this->app->dbAdapter);
        $transDir = $mdlsp->getValue(\models\Table\TableSystemProperty::DEFAULT_MODULE, 'systeminfo', 'TempFileDir');
        $savePathFileName = $transDir . '/' . $_FILES["cres"]["name"];
        if (file_exists($savePathFileName)) {
            unlink($savePathFileName);
        }
        copy($_FILES["cres"]["tmp_name"], $savePathFileName);

        // バッチ(importSmbcPerfect.php)非同期呼出し
        if (\Coral\Base\BaseProcessInfo::isWin()) {
            $fp = popen('start php ./tools/importSmbcPerfect.php ' . $_FILES["cres"]["name"], 'r');
            pclose($fp);
        }
        else {
            exec('php ./tools/importSmbcPerfect.php ' . $_FILES["cres"]["name"] . ' > /dev/null &');
        }

        sleep(1); // 画面遷移後の初期登録を待つ
        return $this->_redirect('rwrcptcfm/impsmbcperfectlist');
    }

    /**
     * SMBCパーフェクト口座結果一覧フォームの表示
     */
    public function impsmbcperfectlistAction()
    {
        $this->setPageTitle( '後払い.com - SMBCパーフェクト口座結果一覧' );

        $sql = 'SELECT * FROM T_ImportedSmbcPerfect ORDER BY RegistDate DESC';
        $ri = $this->app->dbAdapter->query( $sql )->execute( null );
        if ($ri->count() > 0) {
            $this->view->assign( 'impsmbcperfectlist', ResultInterfaceToArray( $ri ) );
        }

        return $this->view;
    }

    /**
     * SMBCパーフェクト口座結果詳細フォームの表示
     */
    public function impsmbcperfectdtlAction()
    {
        $this->setPageTitle("後払い.com - SMBCパーフェクト口座結果詳細");

        $params = $this->getParams();

        // 結果データの取得
        $sql = 'SELECT FileName, ReceiptResult FROM T_ImportedSmbcPerfect WHERE Seq = :Seq';
        $prm = array(
            ':Seq' => (int)$params['seq']
        );
        $ri = $this->app->dbAdapter->query( $sql )->execute( $prm );

        // 取得できた場合
        if ( $ri->count() > 0 ) {
            $row = $ri->current();
            $receiptresult = \Zend\Json\Json::decode( $row['ReceiptResult'], \Zend\Json\Json::TYPE_ARRAY );

            // 正常処理分がある場合
            if ( isset( $receiptresult['summary'] ) ) {
                $this->view->assign('summary', $receiptresult['summary'] );
            }

            // エラー処理分がある場合
            if ( isset( $receiptresult['edata'] ) ) {
                $this->view->assign( 'edata', $receiptresult['edata'] );
            }

            // 返品処理分がある場合
            if ( isset( $receiptresult['rdata'] ) ) {
                $this->view->assign( 'rdata', $receiptresult['rdata'] );
            }

            //
            if ( isset( $receiptresult['errordata'] ) ) {
                $errormessage = '<h4 style="color:red;">処理中にエラーが発生したため全件の入金をキャンセルしました。';
                $errormessage .= '<br/>詳細　：　'. f_e( $receiptresult['errordata'][0] ). '</h4>';
                $this->view->assign( 'errormessage', $errormessage );
            }

            $this->view->assign( 'filename', $row['FileName'] );
        }

        return $this->view;
    }
    
    public function getClass2Action() {
        try
        {
            $params = $this->getParams();
            
            // パラメーターの取得
            $keyCode = $params['keycode'];
            
            $sql = "SELECT Class2 FROM M_Code WHERE CodeId = 163 AND KeyCode = :KeyCode";
            $class2 = $this->app->dbAdapter->query($sql)->execute(array(':KeyCode' => $keyCode))->current(['Class2']);
            
        }
        catch(\Exception $e)
        {
            $msg = $e->getMessage();
            $this->app->logger->err($msg);
        }
        
        echo \Zend\Json\Json::encode($class2['Class2']);
        return $this->response;
    }

}
