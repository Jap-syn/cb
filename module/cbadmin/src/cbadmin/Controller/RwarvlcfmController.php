<?php
namespace cbadmin\Controller;

use Zend\Db\ResultSet\ResultSet;
use Zend\Session\Container;
use Coral\Base\BaseGeneralUtils;
use Coral\Base\BaseHtmlUtils;
use Coral\Base\BaseUtility;
use Coral\Base\Reflection\BaseReflectionUtility;
use Coral\Coral\Controller\CoralControllerAction;
use Coral\Coral\Validate\CoralValidateRequired;
use Coral\Coral\CoralCodeMaster;
use Coral\Coral\Mail\CoralMail;
use Coral\Coral\CoralPager;
use cbadmin\Application;
use models\Table\TableOrder;
use models\Table\TableOrderItems;
use models\Table\TablePayingAndSales;
use models\Table\TableDeliMethod;
use models\Table\TableOem;
use models\Table\TableCode;
use models\Logic\LogicTemplate;
use models\View\ViewArrivalConfirm;
use Coral\Coral\History\CoralHistoryOrder;

class RwarvlcfmController extends CoralControllerAction
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

		$this
			->addStyleSheet('../css/default02.css')
			->addJavaScript('../js/prototype.js')
			->addJavaScript('../js/json+.js');

		$this->setPageTitle("後払い.com - 着荷確認");
	}

	/**
	 * 着荷確認待ちのリストを表示する。
	 */
	public function listAction()
	{
        // 必要なCSS/JSのアサイン
        $this
            ->addStyleSheet('../css/base.ui.customlist.css')
            ->addJavaScript('../js/corelib.js')
            ->addJavaScript('../js/base.ui.js')
            ->addJavaScript('../js/base.ui.customlist.js')
            ->addJavaScript('../js/base.ui.datepicker.js')
            ->addStyleSheet('../css/base.ui.datepicker.css');

        // [paging] 1ページあたりの項目数
        // ※：config.iniからの取得を追加（08.04.03）
        $cn = $this->getControllerName();
        $ipp = (isset($this->app->paging_conf)) ? $this->app->paging_conf[$cn] : 10;
        if( ! BaseReflectionUtility::isPositiveInteger($ipp) ) $ipp = 10;

        // [paging] 指定ページを取得
        $prm_get = $this->params()->fromRoute();
        $current_page = (isset($prm_get['page'])) ? (int)$prm_get['page'] : 1;
        if( $current_page < 1 ) $current_page = 1;

        // 入金状況ごとの色分け用CSSのアサイン
        $filename = (isset($this->app->tools['orderstatus']['style'])) ? $this->app->tools['orderstatus']['style'] : 'default';
        $this->addStyleSheet( '../css/cbadmin/orderstatus/' . $filename . '.css');

        $params = $this->getParams();

        // 伝票登録日(FROM)
        $limitDate = isset($params['journal']) ? BaseUtility::escapeWildcard($params['journal']) : '';

        // 伝票登録日(TO)
        $limitDateT = isset($params['journalT']) ? BaseUtility::escapeWildcard($params['journalT']) : '';
        if (!IsValidFormatDate($limitDateT)) {
            $limitDateT = date("Y-m-d"); // 非通知時or無効日付時は[本日]
        }

        //伝票登録日(FROM)入力チェック
        if(!preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/",$limitDate)){
            $limitDate = '';
        }

        //伝票登録日(FROM) > 伝票登録日(TO)の場合の処理
        if($limitDate > $limitDateT){
            $limitDate = $limitDateT;
        }
        // 締めパターン
        $fixPattern = isset($params['fixPattern']) ? $params['fixPattern'] : 0; // 非通知時は[0:全て]
        $fixPattern = (is_numeric($fixPattern)) ? $fixPattern : 0;
        // 加盟店ID
        $entid = isset($params['entid']) ? BaseUtility::escapeWildcard($params['entid']) : ''; // 非通知時は[空欄]

        //同梱請求書の印刷済確定前を除くの初期値はON
        if (!array_key_exists('existsControlFlg', $params))
        {
            $params['exceptPrintedBilling'] = 1;
        } else {
            if (!array_key_exists('exceptPrintedBilling', $params)) {
                $params['exceptPrintedBilling'] = 0;
            }
        }

        $this->view->assign( 'existsControlFlg', 0 );
        $this->view->assign( 'exceptPrintedBilling', $params['exceptPrintedBilling'] );

        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
        $mdlac = new ViewArrivalConfirm($this->app->dbAdapter);

        // パラメーターで指定されたOEM
        $oemId = (isset($params['oem'])) ? $params['oem'] : -1; // 非通知時は[-1:全て]

        // ViewArrivalConfirm関数通知用の追加検索条件(自動「その他」注文のみ／締めパターン／加盟店ID)
        $aryExCondition = array(
                'onlyetc'       => (isset($params['onlyetc'])) ? true : false
            ,   'fixPattern'    => $fixPattern
            ,   'entid'         => $entid
            ,   'onlyprnt'      => (isset($params['onlyprnt'])) ? true : false
            ,   'exceptPrintedBilling' => $params['exceptPrintedBilling']
        );

        // OEM毎件数の取得
        $oemCountList = $mdlac->getArrivalConfirmCountByOem($limitDate, $limitDateT, $aryExCondition);

        // OEMナビゲーションタグを作成
        $ary = array();
        $stm = $this->app->dbAdapter->query(" SELECT OemNameKj FROM T_Oem WHERE OemId = :OemId ");
        foreach ($oemCountList as $key => $value) {
            if      ($key == -1) { $ary[$key] = '(全て)'; }
            else if ($key ==  0) { $ary[$key] = 'キャッチボール'; }
            else                 { $ary[$key] = $stm->execute(array(':OemId' => $key))->current()['OemNameKj']; }
        }

        $oemTag = BaseHtmlUtils::SelectTag('oemnavisel', $ary, $oemId, ' onChange="javascript:oemnavi();"');

        $this->view->assign("oemTag", $oemTag);

        // パラメーターで指定された配送－着荷確認(Deli_ConfirmArrivalFlg)
        $caflg = (isset($params['caflg'])) ? $params['caflg'] : -2; // 非通知時は[-2:全て]

        // 配送方法を取得する
        $mdldm = new TableDeliMethod($this->app->dbAdapter);
        $dmethods = ResultInterfaceToArray($mdldm->getValidAll());

        // パラメーターで指定された配送方法
        $deliMethodId = (isset($params['dm'])) ? $params['dm'] : $mdlac->getArrivalConfirmMinDeliMethodByOem($limitDate, $limitDateT, $oemId, $aryExCondition);
        // ナビゲーションネタの作成
        $waitTotalCnt = 0;// 着荷確認待ちの総数（クエリー発行ではなく合算で出す）
        $waitCnt = $mdlac->getArrivalConfirmCount3($limitDate, $limitDateT, $oemId, $aryExCondition); // 着荷確認待ち件数の取得

        // count関数対策
        $len = 0;
        if(!empty($dmethods)) {
            $len = count($dmethods);
        }

        for ($i = 0 ; $i < $len ; $i++)
        {
            if ($dmethods[$i]["DeliMethodId"] == $deliMethodId)
            {
                $confirmUrl = $dmethods[$i]["ArrivalConfirmUrl"];
            }

            // 件数(取得できれば値を、そうでなければ 0 とする)
            $count = (isset($waitCnt[$dmethods[$i]["DeliMethodId"]]) ? $waitCnt[$dmethods[$i]["DeliMethodId"]] : 0);

            // 件数が0の場合はリスト上に表示させない
            if (!($count > 0)) { continue; }

            $dmneta[$dmethods[$i]["DeliMethodId"]] = sprintf('%s　(%d)　', $dmethods[$i]['DeliMethodName'], $count);
            $waitTotalCnt += $count;

        }

        $caflgTag = BaseHtmlUtils::SelectTag('caflgnavisel', array(-2 => '(全て)'), -2, ' onChange="javascript:caflgnavi();"');
        if ((int)$deliMethodId != -1) {
            $caflgTag = BaseHtmlUtils::SelectTag('caflgnavisel', array(-2 => '(全て)', -1 => '未着荷のみ'), $caflg, ' onChange="javascript:caflgnavi();"');
        }
        $this->view->assign("caflgTag", $caflgTag);

        // ナビゲーションタグを作成
        $naviTag = BaseHtmlUtils::SelectTag('navisel', array(0 => '(選択可能な配送方法はありません)'), 0, ' onChange="javascript:navi();"');
        if ((int)$deliMethodId != -1) {
            $naviTag = BaseHtmlUtils::SelectTag('navisel', $dmneta, $deliMethodId, ' onChange="javascript:navi();"');
        }

        // 着荷確認待ちのデータを取得する。
        $sql = $this->_getBaseQuery();

        // (配送方法)
        $sql .= " AND    SUM.Deli_DeliveryMethod = " . $deliMethodId;
        // (配送－伝票番号入力日)
        if ($limitDateT != '') {
        $sql .= " AND    DATE(SUM.Deli_JournalIncDate) <= '" . $limitDateT . "' ";
        }
        //  (伝票登録日(FROM))
        if ($limitDate != '') {
        $sql .= " AND    DATE(SUM.Deli_JournalIncDate) >= '" . $limitDate . "' ";
        }
        // (OEM)
        if ($oemId != -1) {
        $sql .= " AND    IFNULL(ORDC.OemId, 0) =" . $oemId;
        }
        // (未着荷のみ)
        if ($caflg == -1) {
        $sql .= " AND    ORDC.Deli_ConfirmArrivalFlg = -1 ";
        }
        // (「その他」着荷を自動で取る)
        if (isset($params['onlyetc'])) {
        $sql .= " AND    (SIT.EtcAutoArrivalFlg = 1 OR (SIT.EtcAutoArrivalFlg = 2 AND SIT.EtcAutoArrivalNumber = OITM.Deli_JournalNumber)) ";
        $sql .= " AND    ENT.Special01Flg = 1 ";
        }
        // (締めパターン)
        if ($fixPattern != 0) {
        $sql .= " AND    MPC.FixPattern = " . $fixPattern;
        }
        // (加盟店ID)
        if ($entid != '') {
        $sql .= " AND    ENT.LoginId LIKE '%" . $entid . "' ";
        }
        // (同梱請求書の印刷済確定前を除く)
        if ($params['exceptPrintedBilling'] == 1) {
            $sql .= " AND  (( ORDC.DataStatus >= 51 ";
            $sql .= "OR  ORDC.ClaimSendingClass = 12 ";
            $sql .= "OR ENT.SelfBillingMode IS NULL ";
            $sql .= "OR ENT.SelfBillingMode = 0 ) ";
            $sql .= "OR SIT.SelfBillingFlg = 0 )";
        }
        // (取りまとめ注文のみ)
        if (isset($params['onlyprnt'])) {
        $sql .= " AND    IFNULL(ORDC.CombinedClaimTargetStatus, 0) IN (91, 92) ";
        }
        $sql .= " ORDER BY MPC.FixPattern, ENT.EnterpriseNameKj, SUM.Deli_JournalIncDate, ORDC.OrderId ";

        $ri = $this->app->dbAdapter->query($sql)->execute( );

        $datas = ResultInterfaceToArray($ri);

        // count関数対策
        $datasLen = 0;
        if(!empty($datas)) {
            $datasLen = count($datas);
        }

        // [paging] ページャ初期化
        $pager = new CoralPager( $datasLen, $ipp );
        // [paging] 指定ページを補正
        if( $current_page > $pager->getTotalPage() ) $current_page = $pager->getTotalPage();
        // [paging] 対象リストをページング情報に基づいて対象リストをスライス
        if( $datasLen > 0 ) $datas = array_slice( $datas, $pager->getStartIndex( $current_page ), $ipp );
        // [paging] ページングナビゲーション情報
        $pagerurlbase = "rwarvlcfm/list/oem/$oemId/dm/$deliMethodId/caflg/$caflg/journal/$limitDate/journalT/$limitDateT/fixPattern/$fixPattern";

        $pagerurlbase .= "/existsControlFlg/". $params['existsControlFlg'];
        $pagerurlbase .= "/exceptPrintedBilling/". $params['exceptPrintedBilling'];

        if (isset($params['onlyetc'])) {
            $pagerurlbase .= "/onlyetc/1";
        }
        if ($entid != '') {
            $pagerurlbase .= "/entid/$entid";
        }
        if (isset($params['onlyprnt'])) {
            $pagerurlbase .= "/onlyprnt/1";
        }
        $pagerurlbase .= "/page";
        $page_links = array( 'base' => $pagerurlbase );
        $page_links['prev'] = $page_links['base'] . '/' . ( $current_page - 1 );
        $page_links['next'] = $page_links['base'] . '/' . ( $current_page + 1 );
        // [paging] ページング関連の情報をビューへアサイン
        $this->view->assign( 'current_page', $current_page );
        $this->view->assign( 'pager', $pager );
        $this->view->assign( 'page_links', $page_links );

        // 入金状況取得のためにTableOrderを使用 08.03.04
        $s = array();
        $mdlo = new TableOrder( $this->app->dbAdapter );

        // count関数対策
        $len = 0;
        if(!empty($datas)) {
            $len = count($datas);
        }

        // 締めパターンのインラインマスター定義
        $fixPatterns = array();
        $ri = $this->app->dbAdapter->query(" SELECT KeyCode, IFNULL(Class2, KeyContent) AS KeyValue FROM M_Code WHERE CodeId = 2 ")->execute(null);
        foreach ($ri as $row) {
            $fixPatterns[$row['KeyCode']] = $row['KeyValue'];
        }

        // 着荷確認日付リストを生成
        $d = date('Y-m-d');
        $youbi = array(1 => '月', 2 => '火', 3 => '水', 4 => '木', 5 => '金', 6 => '土', 0 => '日');
        //今日の日付
        $nowDate = sprintf('%s(%s)', date('m/d', strtotime($d)), $youbi[(int)date('w',strtotime($d))]);
        for ($depth = 1 ; $depth <= 16 ; $depth++) {
            $s[$depth] = sprintf('%s(%s)', date('m/d', strtotime($d)), $youbi[(int)date('w',strtotime($d))]);
            $d = date("Y-m-d", strtotime($d . " -1 Day"));
        }

        // 未確認理由マスタ
        $noArrReasons = $codeMaster->getNoArrReasonMaster();

        // 行ループ処理
        for ($i = 0 ; $i < $len ; $i++)
        {
            // 確認タグ
            $datas[$i]["confirmTag"] = BaseHtmlUtils::InputRadioTag("Deli_ConfirmArrivalFlg" . $i,array(0 => '未確認', 1 => '着荷', -1 => '未着荷'),$datas[$i]['Deli_ConfirmArrivalFlg']);

            // 未確認理由
            $datas[$i]["noArrReasonTag"] = BaseHtmlUtils::SelectTag("Deli_ConfirmNoArrivalReason" . $i, $noArrReasons, $datas[$i]['Deli_ConfirmNoArrivalReason'], 'style="font-size: 11px;"');

            $datas[$i]["Deli_ConfirmArrivalDate"] = BaseGeneralUtils::toDateStringMMDD2($datas[$i]["Deli_ConfirmArrivalDate"]);
            $datas[$i]["Deli_JournalIncDate"] = BaseGeneralUtils::toDateStringMMDD2($datas[$i]["Deli_JournalIncDate"]);

            // 締めパターン
            $datas[$i]["FixPattern"] = $fixPatterns[$datas[$i]["FixPattern"]];

            if(intval($datas[$i]['TimemachineNgFlg']) === 1 ) {
                $datas[$i]['cadTag'] = $nowDate;
            }else{
                $datas[$i]['cadTag'] = BaseHtmlUtils::SelectTag('CAD' . $i, $s, null, 'style="font-size: 11px;"');
            }

            $datas[$i]['is_receipted'] = $datas[$i]['Rct_Status'] ? true : false;
            $datas[$i]['receipted_info'] = $datas[$i]['is_receipted'] ? "{$datas[$i]['ReceiptDate']} 入金済み" : '未入金';
        }

        $this->view->assign("list", $datas);
        $this->view->assign("cnt", $waitTotalCnt);
        $this->view->assign("naviTag", $naviTag);
        $this->view->assign("confirmUrl", $confirmUrl);
        // 確認日一括変換用のSELECT要素（09.06.05 eda）
        $this->view->assign('gcadTag', BaseHtmlUtils::SelectTag('gcad', $s, null, 'style="font-size: 11pt;"'));
        $this->view->assign("limitDate", $limitDate);
        $this->view->assign("limitDateT", $limitDateT);
        if (isset($params['onlyetc'])) { $this->view->assign("onlyetc", 1); }   // (ﾁｪｯｸ状態)自動「その他」注文のみ
        $this->view->assign("fixPatternTag",BaseHtmlUtils::SelectTag('fixPattern',$codeMaster->getMasterCodes(2, array(0 => '(全て)')),$fixPattern));
        $this->view->assign("entid", $entid);
        if (isset($params['onlyprnt'])) { $this->view->assign("onlyprnt", 1); }   // (ﾁｪｯｸ状態)取りまとめ注文のみ

        return $this->view;
	}

    /**
     * 着荷確認待ちのリストを表示する(単体)
     */
    public function simplelistAction()
    {
        // 必要なCSS/JSのアサイン
        $this
            ->addStyleSheet('../css/base.ui.customlist.css')
            ->addJavaScript('../js/corelib.js')
            ->addJavaScript('../js/base.ui.js')
            ->addJavaScript('../js/base.ui.customlist.js')
            ->addJavaScript('../js/base.ui.datepicker.js')
            ->addStyleSheet('../css/base.ui.datepicker.css');

        // 入金状況ごとの色分け用CSSのアサイン
        $filename = (isset($this->app->tools['orderstatus']['style'])) ? $this->app->tools['orderstatus']['style'] : 'default';
        $this->addStyleSheet( '../css/cbadmin/orderstatus/' . $filename . '.css');

        $params = $this->getParams();

        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
        $mdlac = new ViewArrivalConfirm($this->app->dbAdapter);

        // パラメーターで指定された配送－着荷確認(Deli_ConfirmArrivalFlg)
        $caflg = (isset($params['caflg'])) ? $params['caflg'] : -2; // 非通知時は[-2:全て]
        $caflgTag = BaseHtmlUtils::SelectTag('caflgnavisel', array(-2 => '(全て)', -1 => '未着荷のみ'), $caflg, ' onChange="javascript:caflgnavi();"');
        $this->view->assign("caflgTag", $caflgTag);

        // 配送方法を取得する
        $mdldm = new TableDeliMethod($this->app->dbAdapter);
        $dmethods = ResultInterfaceToArray($mdldm->getValidAll());

        // 着荷確認待ちのデータを取得する。
        $sql = $this->_getBaseQuery();

        // パラメータの注文Seqを条件に検索する
        $sql .= " AND    ORDC.OrderSeq = :OrderSeq ";
        $sql .= " ORDER BY MPC.FixPattern, ENT.EnterpriseNameKj, SUM.Deli_JournalIncDate, ORDC.OrderId ";

        $prm= array(
            ':OrderSeq'          => $params['oseq'],
        );

        $ri = $this->app->dbAdapter->query($sql)->execute( $prm );

        $datas = ResultInterfaceToArray($ri);

        // 配送方法
        $deliMethodId = $datas[0]['Deli_DeliveryMethod'];

        // ナビゲーションネタの作成
        // count関数対策
        $len = 0;
        if(!empty($dmethods)) {
            $len = count($dmethods);
        }

        for ($i = 0 ; $i < $len ; $i++)
        {
            if ($dmethods[$i]["DeliMethodId"] == $deliMethodId)
            {
                $confirmUrl = $dmethods[$i]["ArrivalConfirmUrl"];
            }
        }

        // 入金状況取得のためにTableOrderを使用 08.03.04
        $s = array();
        $mdlo = new TableOrder( $this->app->dbAdapter );

        // count関数対策
        $len = 0;
        if(!empty($datas)) {
            $len = count($datas);
        }

        // 締めパターンのインラインマスター定義
        $fixPatterns = array();
        $ri = $this->app->dbAdapter->query(" SELECT KeyCode, IFNULL(Class2, KeyContent) AS KeyValue FROM M_Code WHERE CodeId = 2 ")->execute(null);
        foreach ($ri as $row) {
            $fixPatterns[$row['KeyCode']] = $row['KeyValue'];
        }

        // 着荷確認日付リストを生成
        $d = date('Y-m-d');
        $youbi = array(1 => '月', 2 => '火', 3 => '水', 4 => '木', 5 => '金', 6 => '土', 0 => '日');
        //今日の日付
        $nowDate = sprintf('%s(%s)', date('m/d', strtotime($d)), $youbi[(int)date('w',strtotime($d))]);
        for ($depth = 1 ; $depth <= 16 ; $depth++) {
            $s[$depth] = sprintf('%s(%s)', date('m/d', strtotime($d)), $youbi[(int)date('w',strtotime($d))]);
            $d = date("Y-m-d", strtotime($d . " -1 Day"));
        }

        // 未確認理由マスタ
        $noArrReasons = $codeMaster->getNoArrReasonMaster();

        // 行ループ処理
        for ($i = 0 ; $i < $len ; $i++)
        {
            // 確認タグ
            $datas[$i]["confirmTag"] = BaseHtmlUtils::InputRadioTag("Deli_ConfirmArrivalFlg" . $i,array(0 => '未確認', 1 => '着荷', -1 => '未着荷'),$datas[$i]['Deli_ConfirmArrivalFlg']);

            // 未確認理由
            $datas[$i]["noArrReasonTag"] = BaseHtmlUtils::SelectTag("Deli_ConfirmNoArrivalReason" . $i, $noArrReasons, $datas[$i]['Deli_ConfirmNoArrivalReason'], 'style="font-size: 11px;"');

            $datas[$i]["Deli_ConfirmArrivalDate"] = BaseGeneralUtils::toDateStringMMDD2($datas[$i]["Deli_ConfirmArrivalDate"]);
            $datas[$i]["Deli_JournalIncDate"] = BaseGeneralUtils::toDateStringMMDD2($datas[$i]["Deli_JournalIncDate"]);

            // 締めパターン
            $datas[$i]["FixPattern"] = $fixPatterns[$datas[$i]["FixPattern"]];

            if(intval($datas[$i]['TimemachineNgFlg']) === 1 ) {
                $datas[$i]['cadTag'] = $nowDate;
            }else{
                $datas[$i]['cadTag'] = BaseHtmlUtils::SelectTag('CAD' . $i, $s, null, 'style="font-size: 11px;"');
            }

            $datas[$i]['is_receipted'] = $datas[$i]['Rct_Status'] ? true : false;
            $datas[$i]['receipted_info'] = $datas[$i]['is_receipted'] ? "{$datas[$i]['ReceiptDate']} 入金済み" : '未入金';
        }

        $this->view->assign("list", $datas);
        $this->view->assign("cnt", $waitTotalCnt);
        $this->view->assign("naviTag", $naviTag);
        $this->view->assign("confirmUrl", $confirmUrl);
        // 確認日一括変換用のSELECT要素（09.06.05 eda）
        $this->view->assign('gcadTag', BaseHtmlUtils::SelectTag('gcad', $s, null, 'style="font-size: 11pt;"'));
        $this->view->assign("limitDate", $limitDate);

        return $this->view;
    }

	/**
	 * 一発着荷確認待ちのフィルタ入力フォームを表示する
	 */
	public function lumpAction()
	{
        $this
            ->addJavaScript('../js/corelib.js')
            ->addJavaScript('../js/base.ui.js')
            ->addJavaScript("../js/base.ui.datepicker.js")
            ->addStyleSheet("../css/base.ui.datepicker.css");
        // 入金状況ごとの色分け用CSSのアサイン
        $filename = (isset($this->app->tools['orderstatus']['style'])) ? $this->app->tools['orderstatus']['style'] : 'default';
        $this->addStyleSheet( '../css/cbadmin/orderstatus/' . $filename . '.css');

        $this->view->assign('hasList', false);
        $this->view->assign('exp', array('journalDate' => date('Y-m-d'), 'receiptDate' => date('Y-m-d')));
        $this->view->assign('error', array());
        // 締めパターン
        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
        $this->view->assign("fixPatternTag",BaseHtmlUtils::SelectTag('exp[fixPattern]',$codeMaster->getMasterCodes(2, array(0 => '(全て)')),$fixPattern));
        // OEM
        $mdloem = new TableOem($this->app->dbAdapter);
        $this->view->assign("oemTag",BaseHtmlUtils::SelectTag('oem', $mdloem->getOemIdList(), $oem));
        // 配送業者
        $arDeliMethod['-1'] = '(全て)';
        $mdldm = new TableDeliMethod($this->app->dbAdapter);
        $ri = $mdldm->getValidAll();
        foreach ($ri as $row) {
            $arDeliMethod[$row['DeliMethodId']] = $row['DeliMethodName'];
        }
        $this->view->assign("deliMethodTag",BaseHtmlUtils::SelectTag('deliMethod', $arDeliMethod, $deliMethod));

        return $this->view;
	}

	/**
	 * 指定フィルタ条件で抽出された一発着荷確認待ちデータを表示する
	 */
	public function filterAction()
	{
        $this
            ->addJavaScript('../js/corelib.js')
            ->addJavaScript('../js/base.ui.js')
            ->addJavaScript("../js/base.ui.datepicker.js")
            ->addStyleSheet("../css/base.ui.datepicker.css");

        // 入金状況ごとの色分け用CSSのアサイン
        $filename = (isset($this->app->tools['orderstatus']['style'])) ? $this->app->tools['orderstatus']['style'] : 'default';
        $this->addStyleSheet( '../css/cbadmin/orderstatus/' . $filename . '.css');

        $params = $this->getParams();

        // 伝票登録日(有効な書式年月日でない場合は、システム年月日とする)
        $journalDate = (IsValidFormatDate($params['exp']['journalDate'])) ? $params['exp']['journalDate'] : date('Y-m-d');
        $exp['journalDate'] = $journalDate;
        // 入金日(有効な書式年月日でない場合は、システム年月日とする)
        $receiptDate = (IsValidFormatDate($params['exp']['receiptDate'])) ? $params['exp']['receiptDate'] : date('Y-m-d');
        $exp['receiptDate'] = $receiptDate;
        // 締めパターン
        $fixPattern = isset($params['exp']['fixPattern']) ? $params['exp']['fixPattern'] : 0;
        $exp['fixPattern'] = $fixPattern;
        // OEM
        $oem = isset($params['oem']) ? $params['oem'] : 0;
        // 加盟店ID
        $entid = isset($params['entid']) ? $params['entid'] : '';
        // 加盟店名
        $entnm = isset($params['entnm']) ? $params['entnm'] : '';
        // 配送業者
        $deliMethod = isset($params['deliMethod']) ? $params['deliMethod'] : -1;

        // 指定条件の一括着荷確認処理対象データを取得する
        $sql .= " SELECT ORDC.OrderSeq ";
        $sql .= " ,      ORDC.DataStatus ";
        $sql .= " ,      ORDC.CloseReason ";
        $sql .= " ,      ORDC.UseAmount ";
        $sql .= " ,      ORDC.OutOfAmends ";
        $sql .= " ,      SUM.Deli_JournalIncDate ";
        $sql .= " ,      SUM.Deli_DeliveryMethod ";
        $sql .= " ,      SUM.Deli_DeliveryMethodName ";
        $sql .= " ,      MDELI.EnableCancelFlg as Deli_EnableCancelFlg ";
        $sql .= " ,      MDELI.PayChgCondition as Deli_PayChgCondition ";
        $sql .= " ,      OITM.Deli_JournalNumber ";
        $sql .= " ,      SUM.DestNameKj ";
        $sql .= " ,      SUM.DestPostalCode ";
        $sql .= " ,      SUM.DestUnitingAddress ";
        $sql .= " ,      SUM.DestPhone ";
        $sql .= " ,      ORDC.Deli_ConfirmArrivalFlg ";
        $sql .= " ,      OITM.Deli_ConfirmNoArrivalReason ";
        $sql .= " ,      ORDC.Deli_ConfirmArrivalDate ";
        $sql .= " ,      OITM.Deli_ConfirmArrivalOpId ";
        $sql .= " ,      ENT.EnterpriseId ";
        $sql .= " ,      ENT.EnterpriseNameKj ";
        $sql .= " ,      MPC.FixPattern ";
        $sql .= " ,      ENT.Special01Flg ";
        $sql .= " ,      ORDC.OrderId ";
        $sql .= " ,      ORDC.Rct_Status ";
        $sql .= " ,      TRC.ReceiptDate ";
        $sql .= " ,      PAY.ClearConditionForCharge ";
        $sql .= " ,      OEM.TimemachineNgFlg ";
        $sql .= " FROM   T_Order ORDC FORCE INDEX (Idx_T_Order15) ";
        $sql .= "        STRAIGHT_JOIN T_OrderSummary SUM ON ORDC.OrderSeq = SUM.OrderSeq ";
        $sql .= "        STRAIGHT_JOIN T_OrderItems OITM ON OITM.OrderItemId = SUM.OrderItemId ";
        $sql .= "        STRAIGHT_JOIN M_DeliveryMethod MDELI ON MDELI.DeliMethodId = SUM.Deli_DeliveryMethod ";
        $sql .= "        INNER JOIN T_Enterprise ENT ON ENT.EnterpriseId = ORDC.EnterpriseId ";
        $sql .= "        INNER JOIN T_PayingAndSales PAY ON PAY.OrderSeq = ORDC.OrderSeq ";
        $sql .= "        INNER JOIN M_PayingCycle MPC ON MPC.PayingCycleId = ENT.PayingCycleId ";
        $sql .= "        INNER JOIN (SELECT OrderSeq, MAX(ReceiptDate) AS ReceiptDate FROM T_ReceiptControl WHERE ReceiptDate IS NOT NULL GROUP BY OrderSeq ) TRC ON TRC.OrderSeq = ORDC.P_OrderSeq ";
        $sql .= "        LEFT OUTER JOIN T_Oem OEM ON OEM.OemId = ENT.OemId ";
        $sql .= "        INNER JOIN AT_Order AS ao ON ao.OrderSeq = ORDC.OrderSeq ";
        $sql .= " WHERE  1 = 1 ";
        $sql .= " AND    (ORDC.Deli_ConfirmArrivalFlg IS NULL OR ORDC.Deli_ConfirmArrivalFlg IN (-1, 0)) ";
        $sql .= " AND    ORDC.Cnl_Status = 0 ";
        $sql .= " AND    ORDC.DataStatus > 31 ";
        $sql .= " AND    (ORDC.CloseReason IS NULL OR ORDC.CloseReason IN (0, 1)) ";
        $sql .= " AND    (ORDC.CombinedClaimTargetStatus is NULL OR ORDC.CombinedClaimTargetStatus in (0, 91 ,92)) ";
        $sql .= " AND    ORDC.Rct_Status = 1 ";
        $sql .= " AND    (ORDC.OutOfAmends IS NULL OR ORDC.OutOfAmends = 0) ";       // 2015/10/29 条件を追加 補償対象外は表示しない
        $sql .= " AND    IFNULL(ao.ExtraPayType, '0') <> '1' ";
        // (必須項目)入金日
        $sql .= " AND    TRC.ReceiptDate <= '" . $receiptDate . "' ";
        // (伝票登録日)
        if ($journalDate) {
            $sql .= " AND    DATE(SUM.Deli_JournalIncDate) <= '" . $journalDate . "' ";
        }
        // (締めパターン)
        if ($fixPattern != 0) {
            $sql .= " AND MPC.FixPattern = " . $fixPattern;
        }
        // (OEM)
        $prms = array();
        if ($oem != 0) {
            $sql .= " AND ORDC.OemId = :OemId ";
            $prms += array(':OemId' => $oem);
        }
        // (加盟店ID)※ログインIDの後方一致検索
        if ($entid != '') {
            $sql .= " AND ENT.LoginId like '%" . BaseUtility::escapeWildcard($entid) . "' ";
        }
        // (加盟店名)
        if ($entnm != '') {
            $sql .= " AND ENT.EnterpriseNameKj like '%" . BaseUtility::escapeWildcard($entnm) . "%' ";
        }
        // (配送業者)
        if ($deliMethod != -1) {
            $sql .= " AND SUM.Deli_DeliveryMethod = :DeliMethod ";
            $prms += array(':DeliMethod' => $deliMethod);
        }
        $sql .= " ORDER BY FixPattern, ENT.EnterpriseNameKj, SUM.Deli_JournalIncDate, ORDC.OrderId ";

        $ri = $this->app->dbAdapter->query($sql)->execute($prms);

        $list = ResultInterfaceToArray($ri);

        // 件数
        // count関数対策
        $count = 0;
        if(!empty($list)) {
            $count = count($list);
        }

        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
        $noArrReasons = $codeMaster->getNoArrReasonMaster();

        // 入金状況取得のためにTableOrderを使用 08.03.04
        $s = array();
        $orders = new TableOrder( $this->app->dbAdapter );

        // 締めパターンのインラインマスター定義
        $fixPatterns = array();
        $ri = $this->app->dbAdapter->query(" SELECT KeyCode, IFNULL(Class2, KeyContent) AS KeyValue FROM M_Code WHERE CodeId = 2 ")->execute(null);
        foreach ($ri as $row) {
            $fixPatterns[$row['KeyCode']] = $row['KeyValue'];
        }

        // 着荷確認日付リストを生成
        $d = date('Y-m-d');
        $youbi = array(1 => '月', 2 => '火', 3 => '水', 4 => '木', 5 => '金', 6 => '土', 0 => '日');
        //今日の日付
        $nowDate = sprintf('%s(%s)', date('m/d', strtotime($d)), $youbi[(int)date('w',strtotime($d))]);
        for ($depth = 1 ; $depth <= 16 ; $depth++) {
            $s[$depth] = sprintf('%s(%s)', date('m/d', strtotime($d)), $youbi[(int)date('w',strtotime($d))]);
            $d = date("Y-m-d", strtotime($d . " -1 Day"));
        }

        // 行ループ処理
        foreach($list as $i => &$item)
        {
            $item["Deli_ConfirmArrivalDate"] = BaseGeneralUtils::toDateStringYYMMDD($item["Deli_ConfirmArrivalDate"]);
            $item["Deli_JournalIncDate"] = BaseGeneralUtils::toDateStringYYMMDD($item["Deli_JournalIncDate"]);
            $item["ReceiptDate"] = BaseGeneralUtils::toDateStringYYMMDD($item["ReceiptDate"]);

            // 締めパターン
            $item["FixPattern"] = $fixPatterns[$item["FixPattern"]];

            // 確認日のSELECTタグ作成
            if(intval($item['TimemachineNgFlg']) === 1 ) {
                $item['cadTag'] = $nowDate;
            }else{
                $item['cadTag'] = BaseHtmlUtils::SelectTag('CAD' . $i, $s, null, 'style="font-size: 11px;"');
            }

            // T_Order.Rct_Statusの値から入金済みか未入金かを設定
            $item['is_receipted'] = $item['Rct_Status'] ? true : false;
            $item['receipted_info'] = $item['is_receipted'] ? "{$item['ReceiptDate']} 入金済み" : '未入金';
        }

        $this->view->assign("list", $list);
        $this->view->assign("cnt", $count);
        $this->view->assign('gcadTag', BaseHtmlUtils::SelectTag('gcad', $s, null, 'style="font-size: 11pt;"'));
        $this->view->assign('exp', $exp);
        $this->view->assign('date', $req);
        // 締めパターン
        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
        $this->view->assign("fixPatternTag",BaseHtmlUtils::SelectTag('exp[fixPattern]',$codeMaster->getMasterCodes(2, array(0 => '(全て)')),$fixPattern));
        // OEM
        $mdloem = new \models\Table\TableOem($this->app->dbAdapter);
        $this->view->assign("oemTag",BaseHtmlUtils::SelectTag('oem', $mdloem->getOemIdList(), $oem));
        // 加盟店ID
        $this->view->assign("entid", $entid);
        // 加盟店名
        $this->view->assign("entnm", $entnm);
        // 配送業者
        $arDeliMethod['-1'] = '(全て)';
        $mdldm = new TableDeliMethod($this->app->dbAdapter);
        $ri = $mdldm->getValidAll();
        foreach ($ri as $row) {
            $arDeliMethod[$row['DeliMethodId']] = $row['DeliMethodName'];
        }
        $this->view->assign("deliMethodTag",BaseHtmlUtils::SelectTag('deliMethod', $arDeliMethod, $deliMethod));

        return $this->view;
	}

	/**
	 * 着荷確認確定
	 */
	public function upAction()
	{
        $mdlps = new TablePayingAndSales($this->app->dbAdapter);
        $mdlo = new TableOrder($this->app->dbAdapter);
        $mdlapas = new \models\Table\ATablePayingAndSales($this->app->dbAdapter);

        $params = $this->getParams();

        // ユーザーIDの取得
        $obj = new \models\Table\TableUser($this->app->dbAdapter);
        $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

        $i = 0;
        // トランザクション開始
        $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
        try {
            while(isset($params['OrderSeq' . $i]))
            {
                if ($mdlo->isCanceled($params['OrderSeq' . $i])) { ; }// キャンセルされているので何もしない。
                else {
                    //CADが設定されていない場合本日とする
                    if(is_null($params['CAD' . $i])){
                        $params['CAD' . $i] = 1;

                    }

                    // 着荷確認の結果をDBに書き込み
                    $this->setArrConfirm(
                    $params['OrderSeq' . $i],
                    $params['Deli_ConfirmArrivalFlg' . $i],
                    $params['Deli_ConfirmNoArrivalReason' . $i],
                    $params['CAD' . $i]
                    );

                    // 既に[立替条件クリアフラグ]が[1：条件をクリアしている]か？の取得
                    $isAlreadyClearCondition = $mdlps->IsAlreadyClearCondition($params['OrderSeq' . $i]);

                    if ($params['Deli_ConfirmArrivalFlg' . $i] == 1)
                    {
                        // 立替売上管理.立替条件クリアフラグが"0：条件をクリアしていない"であれば更新実施（判定は下記メソッド内で行う）
                        $mdlps->clearConditionForCharge($params['OrderSeq' . $i], $params['CAD' . $i], $userId);
                    }

                    // [着荷入力日時]のセット
                    $row_pas = $this->app->dbAdapter->query(" SELECT Seq, ClearConditionDate FROM T_PayingAndSales WHERE OrderSeq = :OrderSeq "
                        )->execute(array(':OrderSeq' => $params['OrderSeq' . $i]))->current();
                    $mdlapas->saveUpdate(array('Deli_ConfirmArrivalInputDate' => date('Y-m-d H:i:s')), $row_pas['Seq']);

                    if (($params['Deli_ConfirmArrivalFlg' . $i] == 1) && !$isAlreadyClearCondition) {
                        // 着荷により立替条件クリアフラグが１化されるとき => '1:着荷'として更新
                        $mdlapas->saveUpdate(array('ATUriType' => 1, 'ATUriDay' => date('Ymd', strtotime($row_pas['ClearConditionDate']))), $row_pas['Seq']);
                    }
                }
                $i++;
            }
            // コミット
            $this->app->dbAdapter->getDriver()->getConnection()->commit();
        } catch (\Exception $e) {
            // ロールバック
            $this->app->dbAdapter->getDriver()->getConnection()->rollBack();
            throw $e;
        }

        $strref = strstr($_SERVER['HTTP_REFERER'], 'rwarvlcfm/');

        return $this->_redirect(isset($strref) ? $strref : null);
	}

	/**
	 * 一括着荷確認確定
	 */
	public function lumpupAction()
	{
        $mdlps = new TablePayingAndSales($this->app->dbAdapter);
        $mdlo = new TableOrder($this->app->dbAdapter);
        $mdlapas = new \models\Table\ATablePayingAndSales($this->app->dbAdapter);

        $params = $this->getParams();

        // ユーザーIDの取得
        $obj = new \models\Table\TableUser($this->app->dbAdapter);
        $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

        $i = 0;
        $count = 0;

        $logger = $this->app->logger;
        $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
        $logger->debug('一括着荷確認開始');
        $start_timestamp = microtime(true);
        try {
            while(isset($params['OrderSeq' . $i]))
            {
                if ($mdlo->isCanceled($params['OrderSeq' . $i])) { ; }//キャンセルされているので何もしない。
                else if( ! $params['Deli_ConfirmArrivalFlg' . $i] ) { ; }// チェックされていないので処理しない
                else
                {
                    $logger->debug($params['OrderSeq'.$i]);
                    // 着荷確認の結果をDBに書き込み
                    $this->setArrConfirm(
                        $params['OrderSeq' . $i],
                        1, // Deli_ConfirmArrivalFlgは無条件に'1'を設定（2010.5.28 eda） || $params['Deli_ConfirmArrivalFlg' . $i],
                        nvl($params['Deli_ConfirmNoArrivalReason' . $i], 0),	// 未確認理由が未指定の場合は0をセット（10.5.28 eda）
                        $params['CAD' . $i]
                    );

                    // 既に[立替条件クリアフラグ]が[1：条件をクリアしている]か？の取得
                    $isAlreadyClearCondition = $mdlps->IsAlreadyClearCondition($params['OrderSeq' . $i]);

                    // 着荷確認済みで、立替え条件が着荷確認の注文は、
                    // 立替・売上管理データを立替対象にする。
                    //if ($params['Deli_ConfirmArrivalFlg' . $i] == 1 && $params['Deli_PayChgCondition' . $i] == 1 && $params['OutOfAmends' . $i] != 1)
                    // 立替対象の判断を、「立替対象に設定済みか否か」のみに変更 → このパートへ到達するには着荷確認済み且つ入金済みの場合のみのため
                    if(!$params['ClearConditionForCharge'.$i])
                    {
                        $mdlps->clearConditionForCharge($params['OrderSeq' . $i], $params['CAD' . $i], $userId);
                    }

                    // [着荷入力日時]のセット
                    $row_pas = $this->app->dbAdapter->query(" SELECT Seq, ClearConditionDate FROM T_PayingAndSales WHERE OrderSeq = :OrderSeq "
                        )->execute(array(':OrderSeq' => $params['OrderSeq' . $i]))->current();
                    $mdlapas->saveUpdate(array('Deli_ConfirmArrivalInputDate' => date('Y-m-d H:i:s')), $row_pas['Seq']);

                    if ((!$params['ClearConditionForCharge'.$i]) && !$isAlreadyClearCondition) {
                        // 着荷により立替条件クリアフラグが１化されるとき => '1:着荷'として更新
                        $mdlapas->saveUpdate(array('ATUriType' => 1, 'ATUriDay' => date('Ymd', strtotime($row_pas['ClearConditionDate']))), $row_pas['Seq']);
                    }

                    $count++;
                }
                $i++;
            }
            $this->app->dbAdapter->getDriver()->getConnection()->commit();
        }
        catch (\Exception $err) {
            $this->app->dbAdapter->getDriver()->getConnection()->rollBack();
            throw $err;
        }

        $logger->debug(sprintf('一括着荷確認終了、所要時間：%s sec', microtime(true) - $start_timestamp));
        $url = sprintf('rwarvlcfm/lumpcomplete/cnt/%s/jnl/%s/rct/%s/fix/%s',
                    $count,
                    $params['lastexp']['journalDate'],
                    $params['lastexp']['receiptDate'],
                    $params['lastexp']['fixPattern']
        );

        return $this->_redirect($url);
	}

	/**
	 * 一括着荷確認完了画面を表示する
	 */
	public function lumpcompleteAction()
	{
        $params = $this->getParams();

        $exp = array(
                'journalDate' => (isset($params['jnl'])) ? $params['jnl'] : date('Y-m-d'),
                'receiptDate' => (isset($params['rct'])) ? $params['rct'] : date('Y-m-d'),
                'fixPattern'  => (isset($params['fix'])) ? $params['fix'] : 0
        );
        $this->view->assign('process_count', (isset($params['cnt'])) ? $params['cnt'] : 0);
        $this->view->assign('clear_count'  , (isset($params['clr'])) ? $params['clr'] : 0);
        $this->view->assign('exp'          , $exp);
        return $this->view;
	}

	public function sendmailAction()
	{
        $params = $this->getParams();
        $order_seq = (isset($params['seq'])) ? $params['seq'] : -1;
        $result = array(
                'status' => 0,
                'msg' => ''
        );
        // ユーザーIDの取得
        $obj = new \models\Table\TableUser($this->app->dbAdapter);
        $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);
        try {
            $mail = new CoralMail($this->app->dbAdapter, $this->app->mail['smtp']);
            $mail->SendSlipNumberDifference($order_seq, $userId);

            $result = array_merge($result, array(
                    'status' => 1
            ));
        } catch(\Exception $err) {
            $result = array_merge($result, array(
                    'msg' => $err->getMessage()
            ));
        }
        $json = \Zend\Json\Json::encode($result);

        echo $json;
        return $this->response;
	}

	/**
	 * 着荷確認の結果をDBに反映する。
	 *
	 * @param int $oseq 注文Seq
	 * @param int $arrFlg 確認結果
	 * @param int $noArrReason 未着荷確認理由
	 * @param int $cad 着荷確認日識別　（1：本日　2：昨日　3：一昨日）
	 */
	private function setArrConfirm($oseq, $arrFlg, $noArrReason, $cad = 1)
	{
        $mdloi = new TableOrderItems($this->app->dbAdapter);
        $mdlo = new TableOrder($this->app->dbAdapter);

        // ユーザーIDの取得
        $obj = new \models\Table\TableUser($this->app->dbAdapter);
        $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

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
            $udata["Deli_ConfirmArrivalOpId"] = $this->app->authManagerAdmin->getUserInfo()->OpId;

            // 着荷確認日
            $today = date('Y-m-d');

            // 着荷確認日が何日前でも大丈夫にする。 2009.01.07 kashira
            $backDays = 1 - $cad;

            $deliConfirmArrivalDate = null;
            $deliConfirmArrivalDate = ($backDays < 0) ? date('Y-m-d', strtotime($today . " " . $backDays . " Day")) : date("Y-m-d H:i:s");
            $udata["Deli_ConfirmArrivalDate"] = $deliConfirmArrivalDate;
            $udata['UpdateId'] = $userId;

            $mdloi->saveUpdate($udata, $datas[$i]['OrderItemId']);

            // 着荷確認一覧表示速度改善のために、T_Order側のDel_ConfrimArrivalを追加
            unset($orderData);
            $orderData["Deli_ConfirmArrivalFlg"] = $arrFlg;                                         // 着荷確認
            $orderData["Deli_ConfirmArrivalDate"] = $deliConfirmArrivalDate;
            $orderData["UpdateId"] = $userId;

            $mdlo->saveUpdate($orderData, $datas[$i]['OrderSeq']);
        }
        // 確認結果が「着荷：1」の場合のみ、履歴へ登録する。
        if ($arrFlg == 1) {
            // 注文履歴へ登録
            $history = new CoralHistoryOrder($this->app->dbAdapter);
            $history->InsOrderHistory($oseq, 51, $userId);
        }
    }

// Del By Takemasa(NDC) 20150623 Stt 入力チェック廃止
// 	/**
// 	 * 一括着荷確認フィルタの入力検証処理
// 	 *
// 	 * @access protected
// 	 * @param array $data
// 	 * @return array
// 	 */
// 	protected function validateExpression($data = array())
//
// 	/**
// 	 * 着荷確認フィルタの入力検証処理
// 	 *
// 	 * @access protected
// 	 * @param array $data
// 	 * @return array
// 	 */
// 	protected function journalValidateExpression($data)
// Del By Takemasa(NDC) 20150623 End 入力チェック廃止

	/**
	 * 一覧のCSVダウンロード
	 */
	public function dcsvAction()
	{
        $params  = $this->getParams();

        $datas = array();
        $i = 0;
        while( isset( $params['OrderSeq' . $i] ) ) {
            $prm = array( ':OrderSeq' => $params['OrderSeq' . $i] );
            $sql  = ' SELECT ORD.OrderSeq ';
            $sql .= ' ,      ORD.OrderId ';
            $sql .= ' ,      ORD.ReceiptOrderDate ';
            $sql .= ' ,      ENT.EnterpriseId ';
            $sql .= ' ,      ENT.LoginId ';
            $sql .= ' ,      ENT.EnterpriseNameKj ';
            $sql .= ' ,      SIT.SiteId ';
            $sql .= ' ,      SIT.SiteNameKj ';
            $sql .= ' ,      ORD.UseAmount ';
            $sql .= ' ,      ITM.OrderItemId ';
            $sql .= ' ,      ITM.DeliDestId ';
            $sql .= ' ,      ITM.ItemNameKj ';
            $sql .= ' ,      ITM.ItemNameKn ';
            $sql .= ' ,      ITM.UnitPrice ';
            $sql .= ' ,      ITM.ItemNum ';
            $sql .= ' ,      ITM.SumMoney ';
            $sql .= ' ,      ( CASE ITM.DataClass ';
            $sql .= '          WHEN 1 THEN "商品" ';
            $sql .= '          WHEN 2 THEN "送料" ';
            $sql .= '          WHEN 3 THEN "手数料" ';
            $sql .= '          WHEN 4 THEN "税額" ';
            $sql .= '        END ) AS DataClass ';
            $sql .= ' ,      ITM.Deli_JournalIncDate ';
            $sql .= ' ,      ITM.Deli_DeliveryMethod ';
            $sql .= ' ,      DM.DeliMethodName ';
            $sql .= ' ,      ITM.Deli_JournalNumber ';
            $sql .= ' ,      ITM.Deli_ShipDate ';
            $sql .= ' ,      ( CASE ITM.Deli_ConfirmArrivalFlg ';
            $sql .= '          WHEN -1 THEN "着荷未確認" ';
            $sql .= '          WHEN  0 THEN "確認待ち" ';
            $sql .= '          WHEN  1 THEN "着荷確認" ';
            $sql .= '        END ) AS Deli_ConfirmArrivalFlg ';
            $sql .= ' ,      ITM.Deli_ConfirmArrivalDate ';
            $sql .= ' ,      ITM.Deli_ConfirmArrivalOpId ';
            $sql .= ' ,      ITM.Deli_ConfirmNoArrivalReason ';
            $sql .= ' ,      DD.DestNameKj ';
            $sql .= ' ,      PC.FixPattern ';
            $sql .= ' FROM   T_Order ORD INNER JOIN ';
            $sql .= '        T_OrderItems ITM ON( ORD.OrderSeq = ITM.OrderSeq ) INNER JOIN ';
            $sql .= '        T_Enterprise ENT ON ( ORD.EnterpriseId = ENT.EnterpriseId ) INNER JOIN ';
            $sql .= '        T_Site SIT ON ( ORD.SiteId = SIT.SiteId ) INNER JOIN ';
            $sql .= '        M_PayingCycle PC ON ( ENT.PayingCycleId = PC.PayingCycleId ) LEFT OUTER JOIN ';
            $sql .= '        T_DeliveryDestination DD ON ( ITM.DeliDestId = DD.DeliDestId ) LEFT OUTER JOIN';
            $sql .= '        M_DeliveryMethod DM ON ( ITM.Deli_DeliveryMethod = DM.DeliMethodId ) ';
            $sql .= ' WHERE  ORD.OrderSeq = :OrderSeq ';

            $data = ResultInterfaceToArray( $this->app->dbAdapter->query( $sql )->execute( $prm ) );

            $datas = $datas + $data;

            $i++;
        }

        $templateId = 'CKI05044_1'; // 着荷確認CSV
        $templateClass = 0;
        $seq = 0;
        $templatePattern = 0;

        $logicTemplate = new LogicTemplate( $this->app->dbAdapter );
        $response = $logicTemplate->convertArraytoResponse( $datas, sprintf( 'Deli_ConfirmArrival_%s.csv', date('YmdHis') ), $templateId, $templateClass, $seq, $templatePattern, $this->getResponse() );

        if( $response == false ) {
            throw new \Exception( $logicTemplate->getErrorMessage() );
        }

        return $response;
    }

    /**
     * (Ajax)配送伝票色戻し
     */
    public function backdelimodefyAction()
    {
        $params = $this->getParams();
        $oseq = (isset($params['seq'])) ? $params['seq'] : -1;
        $result = array(
                'status' => 0,
                'msg' => ''
        );

        // ユーザーIDの取得
        $obj = new \models\Table\TableUser($this->app->dbAdapter);
        $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

        try {
            // 注文履歴(伝票番号色戻し)へ登録
            $history = new CoralHistoryOrder($this->app->dbAdapter);
            $history->InsOrderHistory($oseq, 34, $userId);

            $result = array_merge($result, array(
                    'status' => 1
            ));
        } catch(\Exception $err) {
            $result = array_merge($result, array(
                    'msg' => $err->getMessage()
            ));
        }
        $json = \Zend\Json\Json::encode($result);

        echo $json;
        return $this->response;
    }

    /**
     * 着荷確認画面のベースクエリ
     */
    protected function _getBaseQuery()
    {
        $sql .= " SELECT ORDC.OrderSeq ";
        $sql .= " ,      ORDC.DataStatus ";
        $sql .= " ,      ORDC.CloseReason ";
        $sql .= " ,      ORDC.UseAmount ";
        $sql .= " ,      ORDC.OutOfAmends ";
        $sql .= " ,      SUM.Deli_JournalIncDate ";
        $sql .= " ,      SUM.Deli_DeliveryMethod ";
        $sql .= " ,      SUM.Deli_DeliveryMethodName ";
        $sql .= " ,      MDELI.EnableCancelFlg as Deli_EnableCancelFlg ";
        $sql .= " ,      MDELI.PayChgCondition as Deli_PayChgCondition ";
        $sql .= " ,      OITM.Deli_JournalNumber ";
        $sql .= " ,      SUM.DestNameKj ";
        $sql .= " ,      SUM.DestPostalCode ";
        $sql .= " ,      SUM.DestUnitingAddress ";
        $sql .= " ,      SUM.DestPhone ";
        $sql .= " ,      ORDC.Deli_ConfirmArrivalFlg ";
        $sql .= " ,      OITM.Deli_ConfirmNoArrivalReason ";
        $sql .= " ,      ORDC.Deli_ConfirmArrivalDate ";
        $sql .= " ,      OITM.Deli_ConfirmArrivalOpId ";
        $sql .= " ,      ENT.EnterpriseId ";
        $sql .= " ,      ENT.EnterpriseNameKj ";
        $sql .= " ,      MPC.FixPattern ";
        $sql .= " ,      ENT.Special01Flg ";
        $sql .= " ,      ORDC.OrderId ";
        $sql .= " ,      ORDC.Rct_Status ";
        $sql .= " ,      ORDC.P_OrderSeq ";
        $sql .= " ,      (SELECT MAX(ReceiptDate) FROM T_ReceiptControl WHERE ReceiptDate IS NOT NULL AND OrderSeq = ORDC.P_OrderSeq) AS ReceiptDate ";
        $sql .= " ,      OEM.TimemachineNgFlg ";
        $sql .= " ,      (SELECT MAX(RegistDate) FROM T_OrderHistory WHERE HistoryReasonCode = 32 AND OrderSeq = ORDC.OrderSeq) AS Hrc32RegDate ";
        $sql .= " ,      (SELECT MAX(RegistDate) FROM T_OrderHistory WHERE HistoryReasonCode = 34 AND OrderSeq = ORDC.OrderSeq) AS Hrc34RegDate ";
        $sql .= " FROM   T_Order ORDC ";
        $sql .= "        STRAIGHT_JOIN T_OrderSummary SUM ON ORDC.OrderSeq = SUM.OrderSeq ";
        $sql .= "        STRAIGHT_JOIN T_OrderItems OITM ON OITM.OrderItemId = SUM.OrderItemId ";
        $sql .= "        STRAIGHT_JOIN M_DeliveryMethod MDELI ON MDELI.DeliMethodId = SUM.Deli_DeliveryMethod ";
        $sql .= "        INNER JOIN T_Enterprise ENT ON ENT.EnterpriseId = ORDC.EnterpriseId ";
        $sql .= "        INNER JOIN M_PayingCycle MPC ON MPC.PayingCycleId = ENT.PayingCycleId ";
        $sql .= "        LEFT OUTER JOIN T_Oem OEM ON OEM.OemId = ENT.OemId ";
        $sql .= "        INNER JOIN T_Site SIT ON SIT.SiteId = ORDC.SiteId ";
        $sql .= "        INNER JOIN AT_Order AS ao ON ao.OrderSeq = ORDC.OrderSeq ";
        $sql .= " WHERE  1 = 1 ";
        $sql .= " AND    (ORDC.Deli_ConfirmArrivalFlg IS NULL OR ORDC.Deli_ConfirmArrivalFlg IN (-1, 0)) ";
        $sql .= " AND    ORDC.Cnl_Status = 0 ";
        $sql .= " AND    ORDC.DataStatus > 31 ";
        $sql .= " AND    (ORDC.CloseReason IS NULL OR ORDC.CloseReason IN (0, 1)) ";
        $sql .= " AND    (ORDC.CombinedClaimTargetStatus is NULL OR ORDC.CombinedClaimTargetStatus in (0, 91 ,92)) ";
        $sql .= " AND    (ORDC.OutOfAmends IS NULL OR ORDC.OutOfAmends = 0) ";       // 2015/10/29 条件を追加 補償対象外は表示しない
        $sql .= " AND    ( ao.CreditTransferRequestFlg <> '0' OR (";
        $sql .= "        IFNULL(ao.ExtraPayType, '0') <> '1' ";
        $sql .= " AND    NOT (SIT.PaymentAfterArrivalFlg = 1 ";
        $sql .= " AND    DATE_ADD( (SELECT MIN(ch.ClaimDate) FROM T_ClaimHistory AS ch WHERE ch.ClaimPattern = 1 AND ch.OrderSeq = ORDC.OrderSeq), INTERVAL (SELECT MAX(sbps.NumUseDay) AS MaxNumUseDay FROM T_SiteSbpsPayment sbps WHERE sbps.SiteId = ORDC.SiteId AND sbps.ValidFlg = 1 AND sbps.UseStartDate < CURRENT_DATE()) DAY ) >= CURRENT_DATE() ";
        $sql .= " AND    NOT EXISTS (SELECT 1 FROM T_ReceiptControl AS rc WHERE rc.OrderSeq = ORDC.P_OrderSeq))) ";
        $sql .= " ) ";

        return $sql;
    }
}

