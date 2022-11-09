<?php
namespace cbadmin\Controller;

use Zend\Db\ResultSet\ResultSet;
use Coral\Base\BaseHtmlUtils;
use Coral\Base\BaseGeneralUtils;
use Coral\Base\BaseUtility;
use Coral\Base\IO\BaseIOCsvWriter;
use Coral\Coral\Controller\CoralControllerAction;
use Coral\Coral\CoralCodeMaster;
use Coral\Coral\CoralPager;
use Coral\Coral\CoralValidate;
use cbadmin\Application;
use models\Table\TableOem;
use models\Table\TableOperator;
use models\Table\TableClaimHistory;
use models\Table\TableCancel;
use models\Table\TableOrderItems;
use models\Table\TableOrder;
use models\View\ViewDelivery;
use models\Logic\LogicNormalizer;
use models\Logic\LogicTemplate;
use models\Table\TableCode;
use models\Table\TableCreditTransfer;

class SearchoController extends CoralControllerAction
{
	/**
	 * 定型検索定数：与信OK検索
	 * 指定月に登録され与信結果がOKでキャンセルされていない注文を検索
	 *
	 * @var string
	 */
	const SSEARCH_TYPE_OK_CREDITS = 'ok_credits';

	/**
	 * 定型検索定数：与信NG検索
	 * 指定月に登録され与信結果がNGの注文を検索
	 *
	 * @var string
	 */
	const SSEARCH_TYPE_NG_CREDITS = 'ng_credits';

	/**
	 * 定型検索定数：初回支払期限経過検索
	 * 指定の1ヶ月間に初回請求書が発行され、入金待ちのキャンセルされていない注文を検索
	 *
	 * @var string
	 */
	const SSEARCH_TYPE_03 = 'ssearch_type_03';

	/**
	 * 定型検索定数：初回支払期限経過補償対象外検索
	 * 指定の1ヶ月間に初回請求書が発行され、補償対象外で入金待ちのキャンセルされていない注文を検索
	 *
	 * @var string
	 */
	const SSEARCH_TYPE_04 = 'ssearch_type_04';

	/**
	 * 定型検索定数：備考が自動与信の検索
	 * 指定の1ヶ月間に備考が自動与信である、キャンセルされていない注文を検索
	 *
	 * @var string
	 */
	const SSEARCH_TYPE_05 = 'ssearch_type_05';

	/**
	 * 定型検索定数：サービサー委託準備の検索
	 * 指定の1ヶ月間に登録され、キャンセルされていない入金待ち状態を検索
	 *
	 * @var string
	 */
	const SSEARCH_TYPE_06 = 'ssearch_type_06';

	/**
	 * 簡易検索/定型検索でパラメータ不正時に表示するエラーメッセージ定数
	 *
	 * @var string
	 */
	const ERR_NO_PARAMS = '検索条件が指定されていないか不正です。正しく条件を指定してください。';

	protected $_componentRoot = './application/views/components';

	/**
	 * アプリケーションオブジェクト
	 * @var Application
	 */
	private $app;

	/**
	 * 氏名・氏名かなの検索データを作成するための不要文字抽出用正規表現
	 *
	 * @var string
	 */
	const REGEXP_TRIM_NAME = '[ 　\r\n\t\v]';

	/**
	 * 電話番号の検索データを作成するための不要文字抽出用正規表現
	 *
	 * @var string
	 */
	const REGEXP_TRIM_PHONE = '[^0-9]';

	/**
	 * 定型検索タイプ設定
	 *
	 * @access private
	 * @var array
	 */
	private $_ssearch_types;

	/**
	 * １ページ最大表示件数
	 *
	 * @var int
	 */
	const PAGE_LINE_MAX = 1000;

	/**
	 * 検索結果サマリ
	 *
	 * @access private
	 * @var array
	 */
	private $_search_summary;

	/**
	 * Controllerを初期化する
	 */
	public function _init()
	{
        $this->app = Application::getInstance();
        $this->view->assign('userInfo', $this->app->authManagerAdmin->getUserInfo());

        $this->addStyleSheet('../css/default02.css')
            ->addStyleSheet( '../css/base.ui.tableex.css' )
            ->addJavaScript( '../js/json+.js' )
            ->addJavaScript( '../js/prototype.js' )
            ->addJavaScript( '../js/corelib.js' )
            ->addJavaScript( '../js/base.ui.js')
            ->addJavaScript( '../js/base.ui.tableex.js' )
            ->addJavaScript( '../js/base.ui.datepicker.js')
            ->addJavaScript( '../js/sortable_ja.js');

        $this->addJavaScript('../js/bytefx.js');
        $this->addStyleSheet('../css/base.ui.customlist.css');
        $this->addJavaScript('../js/base.ui.customlist.js');

        $this->setPageTitle("後払い.com - 注文検索");

        $this->_ssearch_types = array(
                array(
                        'type' => self::SSEARCH_TYPE_OK_CREDITS,
                        'title' => '与信OK',
                        'desc' => '指定の1ヶ月間に登録され、与信結果がOKでキャンセルされていない注文を検索します'
                ),
                array(
                        'type' => self::SSEARCH_TYPE_NG_CREDITS,
                        'title' => '与信NG',
                        'desc' => '指定の1ヶ月間に登録され、与信結果がNGの注文を検索します'
                ),
                array(
                        'type' => self::SSEARCH_TYPE_03,
                        'title' => '初回支払期限経過',
                        'desc' => '指定の1ヶ月間に初回請求書が発行され、入金待ちのキャンセルされていない注文を検索します'
                ),
                array(
                        'type' => self::SSEARCH_TYPE_04,
                        'title' => '初回支払期限経過補償対象外',
                        'desc' => '指定の1ヶ月間に初回請求書が発行され、補償対象外で入金待ちのキャンセルされていない注文を検索します'
                ),
                array(
                        'type' => self::SSEARCH_TYPE_05,
                        'title' => '備考が自動与信の',
                        'desc' => '指定の1ヶ月間に備考が自動与信である、キャンセルされていない注文を検索します'
                ),
                array(
                        'type' => self::SSEARCH_TYPE_06,
                        'title' => 'サービサー委託準備の',
                        'desc' => '指定の1ヶ月間に登録され、キャンセルされていない入金待ち状態を検索します'
                )
        );

        // 締めパターン
        $obj = new CoralCodeMaster($this->app->dbAdapter);
        $this->view->assign('fixPatternTag', BaseHtmlUtils::SelectTag("fixPattern", $obj->getMasterCodes(2, array(0 => '－')), 0));
        // 料金プラン
        $this->view->assign('planTag', BaseHtmlUtils::SelectTag("Plan", $obj->getPlanMaster(), 0));

        // 入金方法
        // コードマスターから入金方法のコメントを取得
        $mdlc = new TableCode ( $this->app->dbAdapter );
        $receiptMethod = $mdlc->getMasterByClass ( 198 );
        $result = array(0 => null);
        foreach($receiptMethod as $data) {
            $result[$data['KeyCode']] = $data['KeyContent'];
        }
        $this->view->assign ( 'ReceiptMethod', $result );
	}

	/**
	 * 検索フォームの表示
	 */
	public function formAction()
	{
        $mdlOem = new TableOem($this->app->dbAdapter);

        //OEM先リスト取得
        $oem_list = $mdlOem->getOemIdList();

        //OEM先リストSELECTタグ
        $this->view->assign('oemTag', BaseHtmlUtils::SelectTag("Oem", $oem_list));

        // 顧客ステータス
        $this->view->assign('custStsTag',BaseHtmlUtils::SelectTag("custSts",array(0 => '全て', 1 => 'ブラック', 2 => '優良')));

		// 請求代行プラン
		$this->view->assign('BillingAgentStsTag',BaseHtmlUtils::SelectTag("BillingAgentSts",array(0 => '含めない', 1 => '含める', 2 => 'のみ')));

        // キャンセル理由
        $obj = new CoralCodeMaster($this->app->dbAdapter);
        $this->view->assign('cancelreasonTag',BaseHtmlUtils::SelectTag("cancelreason", $obj->getCancelReasonMaster()));

        // 口座振替サービス
        $mdlct = new TableCreditTransfer($this->app->dbAdapter);
        $this->view->assign('creditTransferTag',BaseHtmlUtils::SelectTag("CreditTransferFlg", $mdlct->getSelectData()));

        // 申込ステータス
        $this->view->assign('requestStatusTag',BaseHtmlUtils::SelectTag("RequestStatus", $obj->getRequestStatusMaster()));

        // 申込サブステータス
        $this->view->assign('requestSubStatusTag',BaseHtmlUtils::SelectTag("RequestSubStatus", $obj->getRequestSubStatusMaster()));

        // 口座振替
        $this->view->assign('CreditTransferMethodTag1',BaseHtmlUtils::SelectTag("CreditTransferMethod1",array(0 => '全て', 1 => '対象', 2 => '対象外')));

        // 初回申込用紙発
        $this->view->assign('CreditTransferMethodTag2',BaseHtmlUtils::SelectTag("CreditTransferMethod2",array(0 => '全て', 1 => '対象', 2 => '対象外')));

        // 配送方法
        $mdldm = new \models\Table\TableDeliMethod($this->app->dbAdapter);
        $mrows = $mdldm->getValidAll();
        $mitems[0] = '-----';
        if (isset($mrows)) {
            foreach ($mrows as $mrow) {
                $mitems[$mrow['DeliMethodId']] = $mrow['DeliMethodName'];
            }
        }
        $this->view->assign('deliveryMethodTag', BaseHtmlUtils::SelectTag("Deli_DeliveryMethod", $mitems, 0));

        // 審査担当者
        $mdlop = new TableOperator($this->app->dbAdapter);
        $oprows = $mdlop->getAll();
        $opitems[0] = '－　　　　';
        $opitems[-1] = '自動';
        $opitems[-2] = '手動';
        if (isset($oprows)) {
            foreach ($oprows as $oprow) {
                $opitems[$oprow['OpId']] ="[" . $oprow['OpId'] . "]" . $oprow['NameKj'];
            }
        }
        $this->view->assign('OperatorTag', BaseHtmlUtils::SelectTag("OperatorId", $opitems, 0));

        // 当日日付
        $StartDate = date('Y-m-01');
        $this->view->assign('StartDate',$StartDate);

        return $this->view;
	}

	/**
	 * 簡易検索フォームの表示
	 */
	public function qformAction()
	{
        return $this->view;
	}

	/**
	 * 定型検索フォームの表示
	 */
	public function sformAction()
	{
	    $this->view->assign('search_config', $this->_buildSpecialSearchConfig());

	    return $this->view;
	}

	/**
	 * 注文検索結果CSV
	 */
	public function rformcsvAction()
	{
	    $params = $this->getParams();
	    //検索パターン1を選択して選択ボタンを押下、注文検索結果CSVリンクを押下した際のデフォルトのチェックボックスをチェック有に設定する。
	    if(($params['SearchPattern'] == "1" && isset($params['select_button'])) || empty($params['SearchPattern'])){
	        //抽出項目 入金額
	        $this->view->assign('ReceiptAmount', "1");
	        //抽出項目 注文金額
	        $this->view->assign('UseAmount', "1");
	    }
	    if(empty($params['SearchPattern'])){
	        $params['SearchPattern'] =1;
	    }

	    if($params['SearchPattern'] == "2" ){
	        if (isset($params['select_button'])){
	                //抽出項目 注文登録日
	                $this->view->assign('RegistDate', "1");
	                //抽出項目 注文日
	                $this->view->assign('ReceiptOrderDate', "1");
	                //抽出項目 サイトID
	                $this->view->assign('SiteId', "1");
	                //抽出項目 会社名
	                $this->view->assign('EnterpriseNameKj', "1");
	                //抽出項目 注文者名
	                $this->view->assign('NameKj', "1");
	                //抽出項目 備考
	                $this->view->assign('Incre_Note', "1");
	                //抽出項目 注文者TEL
	                $this->view->assign('Phone', "1");
	                //抽出項目 注文者メアド
	                $this->view->assign('MailAddress', "1");
	                //抽出項目 注文者住所
	                $this->view->assign('UnitingAddress', "1");
	                //抽出項目 配送先住所
	                $this->view->assign('DestUnitingAddress', "1");
	                //抽出項目 初回支払期限
	                $this->view->assign('F_LimitDate', "1");
	                //抽出項目 与信担当者
	                $this->view->assign('Incre_DecisionOpId', "1");
	                //抽出項目 社内与信スコア
	                $this->view->assign('Incre_ScoreTotal', "1");
	                //抽出項目 審査システムスコア
	                $this->view->assign('TotalScore', "1");
	                //抽出項目 入金日
	                $this->view->assign('ReceiptDate', "1");
	                //抽出項目 入金額
	                $this->view->assign('ReceiptAmountTotal', "1");
	                //抽出項目 キャンセル状態
	                $this->view->assign('Cnl_Status', "1");
	                //抽出項目 商品名
	                $this->view->assign('ItemNameKj', "1");
	                //抽出項目 商品単価
	                $this->view->assign('UnitPrice', "1");
	                //抽出項目 利用額
	                $this->view->assign('UseAmount', "1");
	                //抽出項目 審査結果
	                $this->view->assign('Incre_Status', "1");
	        }
	    }
	    $remindClassTag = "";
	    if($params['SearchPattern'] == "3" ){
	        if (isset($params['select_button'])){
	                //抽出項目 注文日
	                $this->view->assign('ReceiptOrderDate', "1");
	                //抽出項目 会社名
	                $this->view->assign('EnterpriseNameKj', "1");
	                //抽出項目 注文者名
	                $this->view->assign('NameKj', "1");
	                //抽出項目 注文者TEL
	                $this->view->assign('Phone', "1");
	                //抽出項目 注文者メアド
	                $this->view->assign('MailAddress', "1");
	                //抽出項目 注文者郵便番号
	                $this->view->assign('PostalCode', "1");
	                //抽出項目 注文者住所
	                $this->view->assign('UnitingAddress', "1");
	                //抽出項目 着荷確認日
	                $this->view->assign('Deli_ConfirmArrivalDate', "1");
	                //抽出項目 立替予定日
	                $this->view->assign('ExecScheduleDate', "1");
	                //抽出項目 利用額
	                $this->view->assign('UseAmount', "1");
	                //抽出項目 商品１名前
	                $this->view->assign('ItemNameKj', "1");
	                //抽出項目 審査結果
	                $this->view->assign('Incre_Status', "1");
	                //抽出項目 支払約束日
	                $this->view->assign('PromPayDate', "1");
	                //抽出項目 督促分類
	                $this->view->assign('RemindClass', "1");
	                //抽出項目 取りまとめ
	                $this->view->assign('CombinedClaimTargetStatus', "1");
	        }
	        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
	        // 督促分類
	        unset($iMaster);
	        unset($seletedIMaster);
	        $iMaster = $codeMaster->getRemindClassMaster();
	        foreach($iMaster as $value => $key)
	        {
	            $seletedIMaster[] = $value;
	        }
	        $remindClassTag = BaseHtmlUtils::InputCheckBoxTag('RemindClass', $iMaster, $seletedIMaster);
	        $this->view->assign('RemindClassTag', $remindClassTag);
	    }

	    $this->view->assign('SearchPattern', $params['SearchPattern']);

        if(!empty($params['search_buttonValue'])){
	        // CSVダウンロードURL
	        unset($params['controller']);
	        unset($params['action']);
	        unset($params['module']);
	        unset($params['__NAMESPACE__']);
	        unset($params['__CONTROLLER__']);

	        return $this->rdcsvAction($params, $remindClassTag);
        }

	    return $this->view;
	}

	/**
	 * 検索実行
	 */
	public function searchAction()
	{
        $params = $this->getParams();

        // $params調整関数呼び出し
        $params = $this->adjustmentParams($params);

        // [paging] 1ページあたりの項目数
        $ipp = self::PAGE_LINE_MAX;
        // [paging] 指定ページを取得
        $current_page = (isset($params['page'])) ? (int)$params['page'] : 1;
        if ($current_page < 1) $current_page = 1;

        // 取得データ行数制限
        $params['limit_offset'] = (" LIMIT " . ($current_page - 1) * $ipp . ", " . $ipp);

        try {
            $datas = $this->getSearchResult($params);
        }
        catch (SearchoControllerException $err) {
            // 検索条件エラーが発生したのでメッセージをセットして
            $mdlOem = new TableOem($this->app->dbAdapter);

            //OEM先リスト取得
            $oem_list = $mdlOem->getOemIdList();

            //OEM先リストSELECTタグ
            $this->view->assign('oemTag', BaseHtmlUtils::SelectTag("Oem", $oem_list));

            // 顧客ステータス
            $this->view->assign('custStsTag',BaseHtmlUtils::SelectTag("custSts",array(0 => '全て', 1 => 'ブラック', 2 => '優良')));


            // 請求代行プラン
            $this->view->assign('BillingAgentStsTag',BaseHtmlUtils::SelectTag("BillingAgentSts",array(0 => '含めない', 1 => '含める', 2 => 'のみ')));

            // キャンセル理由
            $obj = new CoralCodeMaster($this->app->dbAdapter);
            $this->view->assign('cancelreasonTag',BaseHtmlUtils::SelectTag("cancelreason", $obj->getCancelReasonMaster()));
            // 申込ステータス
            $this->view->assign('requestStatusTag',BaseHtmlUtils::SelectTag("RequestStatus", $obj->getRequestStatusMaster()));
            // 申込サブステータス
            $this->view->assign('requestSubStatusTag',BaseHtmlUtils::SelectTag("RequestSubStatus", $obj->getRequestSubStatusMaster()));

            // 配送方法
            $mdldm = new \models\Table\TableDeliMethod($this->app->dbAdapter);
            $mrows = $mdldm->getValidAll();
            $mitems[0] = '-----';
            if (isset($mrows)) {
                foreach ($mrows as $mrow) {
                    $mitems[$mrow['DeliMethodId']] = $mrow['DeliMethodName'];
                }
            }
            $this->view->assign('deliveryMethodTag', BaseHtmlUtils::SelectTag("Deli_DeliveryMethod", $mitems, (isset($params['Deli_DeliveryMethod'])) ? $params['Deli_DeliveryMethod'] : 0));
            $this->view->assign('entCustSeq', $params['EntCustSeqList'] );

            // 条件入力画面を表示
            $this->view->assign('SearchExpressionError', $err->getMessage());

            $this->setTemplate('form');
            return $this->view;
        }

        // (金額集計)
        $val_item_count = $this->_search_summary['TotalRecCnt'];
        $val_total_amount = $this->_search_summary['TotalUseAmount'];
        $val_calc_amount = $this->_search_summary['CalcReceiptAmount'];
        $val_calc_noamount = $this->_search_summary['CalcNoReceiptAmount'];
        $val_now_totalamount = $this->_search_summary['NowTotalAmount'];

        // 類似住所検索結果の色分け用CSSのアサイン
        $this->addStyleSheet( '../css/cbadmin/orderstatus/' .
            ( $this->app->tools['orderstatus']['style'] ? $this->app->tools['orderstatus']['style'] : 'default' ) .
            '.css' );

        // [paging] ページャ初期化
        $pager = new CoralPager($this->_search_summary['TotalRecCnt'], $ipp);
        // [paging] 指定ページを補正
        if( $current_page > $pager->getTotalPage() ) $current_page = $pager->getTotalPage();
        // [paging] ページングナビゲーション情報
        $page_links = array( 'base' => "searcho/search/page" );
        $page_links['prev'] = $page_links['base'] . '/' . ( $current_page - 1 );
        $page_links['next'] = $page_links['base'] . '/' . ( $current_page + 1 );
        // [paging] ページング関連の情報をビューへアサイン
        $this->view->assign( 'current_page', $current_page );
        $this->view->assign( 'pager', $pager );
        $this->view->assign( 'page_links', $page_links );

        $this->view->assign('list', $datas);
        $this->view->assign('item_count', $val_item_count);
        $this->view->assign('total_amount', $val_total_amount);
        $this->view->assign('calc_amount', $val_calc_amount);
        $this->view->assign('calc_noamount', $val_calc_noamount);
        $this->view->assign('now_totalamount', $val_now_totalamount);

        // CSVダウンロードURL
        unset($params['controller']);
        unset($params['action']);
        unset($params['module']);
        unset($params['__NAMESPACE__']);
        unset($params['__CONTROLLER__']);
        $this->view->assign('dlaction', 'searcho/dcsv');
        $this->view->assign('srchparams', serialize($params));

        return $this->view;
	}

	/**
	 * 簡易検索実行
	 */
	public function qsearchAction()
	{
        $params = $this->getParams();

        // $params調整関数呼び出し
        $params = $this->adjustmentParams($params);

        if (!$this->isValidQSearchCondition($params)) {
            // 条件未指定
            $this->view->assign('noParamsError', 1);
            $this->setTemplate('qform');
            return $this->view;
        }

        // [paging] 1ページあたりの項目数
        $ipp = self::PAGE_LINE_MAX;
        // [paging] 指定ページを取得
        $current_page = (isset($params['page'])) ? (int)$params['page'] : 1;
        if ($current_page < 1) $current_page = 1;

        // 取得データ行数制限
        $params['limit_offset'] = (" LIMIT " . ($current_page - 1) * $ipp . ", " . $ipp);

        $datas = $this->getQuickSearchResult($params);

        if($datas === false)
        {
            // 条件未指定
            $this->view->assign('noParamsError', 1);
            $this->setTemplate('qform');
            return $this->view;
        }

        // (金額集計)
        $val_item_count = $this->_search_summary['TotalRecCnt'];
        $val_total_amount = $this->_search_summary['TotalUseAmount'];
        $val_calc_amount = $this->_search_summary['CalcReceiptAmount'];
        $val_calc_noamount = $this->_search_summary['CalcNoReceiptAmount'];
        $val_now_totalamount = $this->_search_summary['NowTotalAmount'];

        // 類似住所検索結果の色分け用CSSのアサイン
        $this->addStyleSheet( '../css/cbadmin/orderstatus/' .
            ( $this->app->tools['orderstatus']['style'] ? $this->app->tools['orderstatus']['style'] : 'default' ) .
            '.css' );

        // [paging] ページャ初期化
        $pager = new CoralPager($this->_search_summary['TotalRecCnt'], $ipp);
        // [paging] 指定ページを補正
        if( $current_page > $pager->getTotalPage() ) $current_page = $pager->getTotalPage();
        // [paging] ページングナビゲーション情報
        $page_links = array( 'base' => "searcho/qsearch/page" );
        $page_links['prev'] = $page_links['base'] . '/' . ( $current_page - 1 );
        $page_links['next'] = $page_links['base'] . '/' . ( $current_page + 1 );
        // [paging] ページング関連の情報をビューへアサイン
        $this->view->assign( 'current_page', $current_page );
        $this->view->assign( 'pager', $pager );
        $this->view->assign( 'page_links', $page_links );

        $this->view->assign('list', $datas);
        $this->view->assign('item_count', $val_item_count);
        $this->view->assign('total_amount', $val_total_amount);
        $this->view->assign('calc_amount', $val_calc_amount);
        $this->view->assign('calc_noamount', $val_calc_noamount);
        $this->view->assign('now_totalamount', $val_now_totalamount);

        if ($params['postalcode'] != null || $params['phonelink'] != null){
            $this->view->assign('postalcode', $params['postalcode']);
            $this->view->assign('phonelink', $params['phonelink']);
            $this->view->assign('days', $params['registdate']);

            $claimcount = 0;

            // count関数対策
            $datasLen = 0;
            if(!empty($datas)) {
                $datasLen = count($datas);
            }

            for ($i = 0 ; $i < $datasLen ; $i++) {
                if ($datas[$i]['PostalCode'] == $params['postalcode']){
                    $claimcount += 1;
                }
                if ($datas[$i]['Phone'] == $params['phonelink']){
                    $claimcount += 1;
                }
            }
            $delicount = $val_item_count - $claimcount;
            $this->view->assign('claimcount', $claimcount);
            $this->view->assign('delicount', $delicount);
        }

        // CSVダウンロードURL
        unset($params['controller']);
        unset($params['action']);
        unset($params['module']);
        unset($params['__NAMESPACE__']);
        unset($params['__CONTROLLER__']);
        $this->view->assign('dlaction', 'searcho/qdcsv');
        $this->view->assign('srchparams', serialize($params));

        $this->setTemplate('search');

        return $this->view;
	}

	/**
	 * 有効な簡易検索条件か？
	 *
	 * @param array $params 検索条件
	 * @return  boolean 結果 true:OK／false:NG
	 */
	private function isValidQSearchCondition($params)
	{
        $normalizers = array(
                'order_id' => LogicNormalizer::create(LogicNormalizer::FILTER_FOR_ID),
                'name' => LogicNormalizer::create(LogicNormalizer::FILTER_FOR_NAME),
                'phone' => LogicNormalizer::create(LogicNormalizer::FILTER_FOR_TEL),
                'address' => LogicNormalizer::create(LogicNormalizer::FILTER_FOR_ADDRESS),
                'deliphone' => LogicNormalizer::create(LogicNormalizer::FILTER_FOR_TEL),
                'deliaddress' => LogicNormalizer::create(LogicNormalizer::FILTER_FOR_ADDRESS),
                'mail' => LogicNormalizer::create(LogicNormalizer::FILTER_FOR_MAIL),
                'postalcode' => LogicNormalizer::create(LogicNormalizer::FILTER_FOR_POSTALCODE),
                'phonelink' => LogicNormalizer::create(LogicNormalizer::FILTER_FOR_TEL)
        );

        // 正規化実施
        $val_order_id = $normalizers['order_id']->normalize($params['order_id']);
        $val_name = $normalizers['name']->normalize($params['name']);
        $val_phone = $normalizers['phone']->normalize($params['phone']);
        $val_address = $normalizers['address']->normalize($params['address']);
        $val_mail = $normalizers['mail']->normalize($params['mail']);
        $val_deliphone = $normalizers['deliphone']->normalize($params['deliphone']);
        $val_deliaddress = $normalizers['deliaddress']->normalize($params['deliaddress']);
        $val_postalcode = $params['postalcode'];
        $val_phonelink = $normalizers['phonelink']->normalize($params['phonelink']);

        if ($val_order_id == '' &&
            $val_name == '' &&
            $val_phone == '' &&
            $val_address == '' &&
            $val_deliphone == '' &&
            $val_deliaddress == '' &&
            $val_mail == '' &&
            $val_postalcode == '' &&
            $val_phonelink == '' &&
            trim(str_replace('　', ' ', $params['claim_amount_from'])) == '' &&
            trim(str_replace('　', ' ', $params['claim_amount_to'])) == '') {

            // 条件未指定
            return false;
        }

        return true;
	}

	/**
	 * 定型検索実行
	 */
	public function ssearchAction()
	{
        $params = $this->getParams();

        // $params調整関数呼び出し
        $params = $this->adjustmentParams($params);

        // [paging] 1ページあたりの項目数
        $ipp = self::PAGE_LINE_MAX;
        // [paging] 指定ページを取得
        $current_page = (isset($params['page'])) ? (int)$params['page'] : 1;
        if ($current_page < 1) $current_page = 1;

        // 取得データ行数制限
        $params['limit_offset'] = (" LIMIT " . ($current_page - 1) * $ipp . ", " . $ipp);

        $datas = $this->getSpecialSearchResult($params);
        if($datas === false)
        {
            // 条件未指定
            $this->view->assign('noParamsError', 1);
            $this->view->assign('search_config', $this->_buildSpecialSearchConfig());

            $this->setTemplate('sform');
            return $this->view;
        }

        // (金額集計)
        $val_item_count = $this->_search_summary['TotalRecCnt'];
        $val_total_amount = $this->_search_summary['TotalUseAmount'];
        $val_calc_amount = $this->_search_summary['CalcReceiptAmount'];
        $val_calc_noamount = $this->_search_summary['CalcNoReceiptAmount'];
        $val_now_totalamount = $this->_search_summary['NowTotalAmount'];

        // 類似住所検索結果の色分け用CSSのアサイン
        $this->addStyleSheet( '../css/cbadmin/orderstatus/' .
            ( $this->app->tools['orderstatus']['style'] ? $this->app->tools['orderstatus']['style'] : 'default' ) .
            '.css' );

        // [paging] ページャ初期化
        $pager = new CoralPager($this->_search_summary['TotalRecCnt'], $ipp);
        // [paging] 指定ページを補正
        if( $current_page > $pager->getTotalPage() ) $current_page = $pager->getTotalPage();
        // [paging] ページングナビゲーション情報
        $page_links = array( 'base' => "searcho/ssearch/page" );
        $page_links['prev'] = $page_links['base'] . '/' . ( $current_page - 1 );
        $page_links['next'] = $page_links['base'] . '/' . ( $current_page + 1 );
        // [paging] ページング関連の情報をビューへアサイン
        $this->view->assign( 'current_page', $current_page );
        $this->view->assign( 'pager', $pager );
        $this->view->assign( 'page_links', $page_links );

        $this->view->assign('list', $datas);
        $this->view->assign('item_count', $val_item_count);
        $this->view->assign('total_amount', $val_total_amount);
        $this->view->assign('calc_amount', $val_calc_amount);
        $this->view->assign('calc_noamount', $val_calc_noamount);
        $this->view->assign('now_totalamount', $val_now_totalamount);

		// 検索条件情報をアサイン
		foreach($this->_ssearch_types as $config)
		{
			if($config['type'] == $params['type'])
			{
				$date_span = $this->_getSpecialSearchDateSpan($params['month'], $params['type']);
				$this->view->assign('page_title',
									sprintf('%s検索 (対象期間：%s ～ %s)',
											$config['title'],
											f_df($date_span['start'], 'Y年 m月 d日'),
											f_df($date_span['end']  , 'Y年 m月 d日') ) );
				break;
			}
		}

		// CSVダウンロードURL
		unset($params['controller']);
		unset($params['action']);
		unset($params['module']);
		unset($params['__NAMESPACE__']);
		unset($params['__CONTROLLER__']);
		$this->view->assign('dlaction', 'searcho/sdcsv');
		$this->view->assign('srchparams', serialize($params));

		$this->setTemplate('search');
		return $this->view;
	}

	/**
	 * ダウンロード実行
	 */
	public function dcsvAction()
	{
        $params = $this->getParams();
        if (isset($params['srchparams'])) {
            $srchparams = unserialize($params['srchparams']);
            unset($params['srchparams']);
            $params = array_merge($params, $srchparams);
        }
        // 全件ダウンロード
        unset($params['limit_offset']);

        try {
            $datas = $this->getSearchResult( $params );
        } catch ( SearchoControllerException $err ) {
            // 検索条件エラーが発生したので0行として扱う
            $datas = array();
        }
        $fileName = sprintf( "result_%s.csv", date( "YmdHis" ) );

        return $this->_execCsvDownload( $datas, $fileName );
	}

	/**
	 * 簡易検索CSVダウンロード実行
	 */
	public function qdcsvAction()
	{
        $params = $this->getParams();
        if (isset($params['srchparams'])) {
            $srchparams = unserialize($params['srchparams']);
            unset($params['srchparams']);
            $params = array_merge($params, $srchparams);
        }
        // 全件ダウンロード
        unset($params['limit_offset']);

        $datas = $this->getQuickSearchResult( $params );

        if( $datas === false ) $datas = array();
        $fileName = sprintf( "q_result_%s.csv", date( "YmdHis" ) );

       return $this->_execCsvDownload( $datas, $fileName );
	}

	/**
	 * 定型検索CSVダウンロード実行
	 */
	public function sdcsvAction()
	{
	    $start_time = microtime(true);
		$params = $this->getParams();
		if (isset($params['srchparams'])) {
		    $srchparams = unserialize($params['srchparams']);
		    unset($params['srchparams']);
		    $params = array_merge($params, $srchparams);
		}
		// 全件ダウンロード
		unset($params['limit_offset']);

		$datas = $this->getSpecialSearchResult($params);

		if($datas === false) $datas = array();
		$fileName = sprintf("sp_%s_result_%s.csv", nvl($params['type'], 'unknown'), date("YmdHis"));

		return $this->_execCsvDownload($datas, $fileName);
	}

	/**
	 * 注文検索結果CSVダウンロード実行
	 */
	public function rdcsvAction(array $params, $remindClassTag)
	{

	    $this->view->assign('SearchPattern', $params['SearchPattern']);
	    $templateId = '';
	    try{
	        switch($params['SearchPattern']){
	            case "1":
	                $datas = $this->getSP1SearchResult($params);
	                $templateId = 'CKI01033_2';    // 注文情報CSV(検索パターン1)
	                break;
	            case "2":
	                $datas = $this->getSP2SearchResult($params);
	                // CSV出力情報を設定
	                for ( $i = 0; $i < count( $datas ); $i++ ) {
	                    //備考
	                    $datas[$i]['Incre_Note'] = str_replace("\r", " ", str_replace( "\n", " ", $datas[$i]['Incre_Note']));
	                    //キャンセル状態
	                    switch( $datas[$i]['Cnl_Status'] ) {
	                        case 0:
	                            $datas[$i]['Cnl_Status'] = '';
	                            break;
	                        case 1:
	                            if( $datas[$i]['Cnl_ReturnSaikenCancelFlg'] == '1' ) {
	                                $datas[$i]['Cnl_Status'] = '返却依頼中';
	                            }
	                            else {
	                                $datas[$i]['Cnl_Status'] = 'キャンセル依頼中';
	                            }
	                            break;
	                        case 2:
	                            if( $datas[$i]['Cnl_ReturnSaikenCancelFlg'] == '1' ) {
	                                $datas[$i]['Cnl_Status'] = '返却済み';
	                            }
	                            else {
	                                $datas[$i]['Cnl_Status'] = 'キャンセル済み';
	                            }
	                            break;
	                    }
	                }
	                $templateId = 'CKI01033_3';    // 注文情報CSV(検索パターン2)
	                break;
	            case "3":
	                $this->view->assign('RemindClassTag', $remindClassTag);
	                $datas = $this->getSP3SearchResult($params);
	                $templateId = 'CKI01033_4';    // 注文情報CSV(検索パターン3)
	                break;
	            default:
	                break;
	        }
	    }


	    catch (SearchoControllerException $err) {
            // 検索条件エラーが発生したのでメッセージをセットして条件入力画面を表示
            $this->view->assign('SearchExpressionError', $err->getMessage());
	     switch($params['SearchPattern']){
	            case "1":
	                //抽出項目 入金額
	                $this->view->assign('ReceiptAmount', $params['ReceiptAmount']);
	                //抽出項目 注文金額
	                $this->view->assign('UseAmount', $params['UseAmount']);
	                break;
	            case "2":
	                //検索条件 支払期限日
	                $this->view->assign('LimitDateF', $params['LimitDateF']);
	                $this->view->assign('LimitDateT', $params['LimitDateT']);
	                //抽出項目 注文登録日
	                $this->view->assign('RegistDate', $params['RegistDate']);
	                //抽出項目 注文日
	                $this->view->assign('ReceiptOrderDate', $params['ReceiptOrderDate']);
	                //抽出項目 サイトID
	                $this->view->assign('SiteId', $params['SiteId']);
	                //抽出項目 会社名
	                $this->view->assign('EnterpriseNameKj', $params['EnterpriseNameKj']);
	                //抽出項目 注文者名
	                $this->view->assign('NameKj', $params['NameKj']);
	                //抽出項目 備考
	                $this->view->assign('Incre_Note', $params['Incre_Note']);
	                //抽出項目 注文者TEL
	                $this->view->assign('Phone', $params['Phone']);
	                //抽出項目 注文者メアド
	                $this->view->assign('MailAddress', $params['MailAddress']);
	                //抽出項目 注文者住所
	                $this->view->assign('UnitingAddress', $params['UnitingAddress']);
	                //抽出項目 配送先住所
	                $this->view->assign('DestUnitingAddress', $params['DestUnitingAddress']);
	                //抽出項目 初回支払期限
	                $this->view->assign('F_LimitDate', $params['F_LimitDate']);
	                //抽出項目 与信担当者
	                $this->view->assign('Incre_DecisionOpId', $params['Incre_DecisionOpId']);
	                //抽出項目 社内与信スコア
	                $this->view->assign('Incre_ScoreTotal', $params['Incre_ScoreTotal']);
	                //抽出項目 審査システムスコア
	                $this->view->assign('TotalScore', $params['TotalScore']);
	                //抽出項目 入金日
	                $this->view->assign('ReceiptDate', $params['ReceiptDate']);
	                //抽出項目 入金額
	                $this->view->assign('ReceiptAmountTotal', $params['ReceiptAmountTotal']);
	                //抽出項目 キャンセル状態
	                $this->view->assign('Cnl_Status', $params['Cnl_Status']);
	                //抽出項目 商品名
	                $this->view->assign('ItemNameKj', $params['ItemNameKj']);
	                //抽出項目 商品単価
	                $this->view->assign('UnitPrice', $params['UnitPrice']);
	                //抽出項目 利用額
	                $this->view->assign('UseAmount', $params['UseAmount']);
	                //抽出項目 審査結果
	                $this->view->assign('Incre_Status', $params['Incre_Status']);
	                break;
	            case "3":
	                //抽出項目 注文日
	                $this->view->assign('ReceiptOrderDate', $params['ReceiptOrderDate']);
	                //抽出項目 会社名
	                $this->view->assign('EnterpriseNameKj', $params['EnterpriseNameKj']);
	                //抽出項目 注文者名
	                $this->view->assign('NameKj', $params['NameKj']);
	                //抽出項目 注文者TEL
	                $this->view->assign('Phone', $params['Phone']);
	                //抽出項目 注文者メアド
	                $this->view->assign('MailAddress', $params['MailAddress']);
	                //抽出項目 注文者郵便番号
	                $this->view->assign('PostalCode', $params['PostalCode']);
	                //抽出項目 注文者住所
	                $this->view->assign('UnitingAddress', $params['UnitingAddress']);
	                //抽出項目 着荷確認日
	                $this->view->assign('Deli_ConfirmArrivalDate', $params['Deli_ConfirmArrivalDate']);
	                //抽出項目 立替予定日
	                $this->view->assign('ExecScheduleDate', $params['ExecScheduleDate']);
	                //抽出項目 利用額
	                $this->view->assign('UseAmount', $params['UseAmount']);
	                //抽出項目 商品１名前
	                $this->view->assign('ItemNameKj', $params['ItemNameKj']);
	                //抽出項目 審査結果
	                $this->view->assign('Incre_Status', $params['Incre_Status']);
	                //抽出項目 支払約束日
	                $this->view->assign('PromPayDate', $params['PromPayDate']);
	                //抽出項目 督促分類
	                $this->view->assign('RemindClass', $params['RemindClass']);
	                //抽出項目 取りまとめ
	                $this->view->assign('CombinedClaimTargetStatus', $params['CombinedClaimTargetStatus']);
	                break;
	            default:
	                break;
	        }
            $this->setTemplate('rformcsv');
            return $this->view;
        }

	    if($datas === false) $datas = array();
	    $fileName = sprintf( "r_result_%s.csv", date( "YmdHis" ) );

        $templateClass = 0;
        $seq = 0;
        $templatePattern = 0;

        $logicTemplate = new LogicTemplate( $this->app->dbAdapter );
        $response = $logicTemplate->convertArraytoResponse2( $datas, $fileName, $templateId, $templateClass, $seq, $templatePattern, $this->getResponse(), $params );
        if( $response == false ) {
            throw new \Exception( $logicTemplate->getErrorMessage() );
        }

        return $response;
	}

	/**
	 * 指定データから作成されたCSVファイルの指定ファイル名でのダウンロードを実行する
	 *
	 * @access private
	 * @param array $datas CSV向けデータ配列
	 * @param string $fileName ダウンロードファイル名
	 */
	private function _execCsvDownload(array $datas, $fileName)
	{
        // ※：簡易検索/定型検索でも共用できるよう、dcsvActionから分離（2013.4.1 eda）
        ini_set('max_execution_time', 0);   // 実行タイムアウトを無効にする

        $mdldeli = new ViewDelivery( $this->app->dbAdapter );
        $mdlch = new TableClaimHistory( $this->app->dbAdapter );
        $mdlcl = new TableCancel( $this->app->dbAdapter );
        $mdloi = new TableOrderItems( $this->app->dbAdapter );
        $mdlo = new TableOrder( $this->app->dbAdapter );

        // 個々の注文の情報追加
        for ( $i = 0; $i < count( $datas ); $i++ ) {
            // 立替予定日
            $execScheduleDate = $this->getExecScheduleDate( $datas[$i]['OrderSeq'] );

            // 配送情報
            // 配送情報の取得方法を生SQL実行に変更（2013.3.15 eda）
            $query = <<<EOQ
SELECT
    s.DestNameKj,
    s.DestNameKn,
    s.DestPhone,
    s.DestPostalCode,
    s.DestUnitingAddress,
    s.Deli_JournalIncDate,
    m.DeliMethodName,
    s.Deli_DeliveryMethodName,
    s.Deli_JournalNumber,
    i.Deli_ConfirmArrivalDate
FROM
    T_OrderSummary s STRAIGHT_JOIN
    T_OrderItems i ON i.OrderItemId = s.OrderItemId LEFT OUTER JOIN
    M_DeliveryMethod m ON m.DeliMethodId = i.Deli_DeliveryMethod
WHERE
    s.OrderSeq = :OrderSeq
EOQ;

            if( $datas[$i]['HasDeliInfo'] )
            {
                // 元データに配送先情報が含まれている（＝簡易検索＆定型検索）場合は
                // SQLを発行せず元データから必要情報を構築（2013.4.2 eda）
                $deli = array(
                        'DestNameKj' => $datas[$i]['DestNameKj'],
                        'DestNameKn' => $datas[$i]['DestNameKn'],
                        'DestPhone' => $datas[$i]['DestPhone'],
                        'DestPostalCode' => $datas[$i]['DestPostalCode'],
                        'DestUnitingAddress' => $datas[$i]['DestUnitingAddress'],
                        'Deli_JournalIncDate' => $datas[$i]['Deli_JournalIncDate'],
                        'Deli_DeliveryMethodName' => $datas[$i]['Deli_DeliveryMethodName'],
                        'Deli_JournalNumber' => $datas[$i]['Deli_JournalNumber'],
                        'Deli_ConfirmArrivalDate' => $datas[$i]['Deli_ConfirmArrivalDate']
                );
            }
            else
            {
                // 元データに配送先情報が含まれていない（＝通常検索）場合は
                // 従来通りSQLを発行して必要情報を取得（2013.4.2 eda）
                $deli = $this->app->dbAdapter->query( $query )->execute( array( ':OrderSeq' => $datas[$i]['OrderSeq'] ) )->current();
            }

             $record[$i] = array(
                     'OrderId' => $datas[$i]['OrderId'],
                     'Ent_OrderId' => $datas[$i]['Ent_OrderId'],
                     'Oem_OrderId' => $datas[$i]['Oem_OrderId'],
                     'ReceiptOrderDate' => $datas[$i]['ReceiptOrderDate'],
                     'OemNameKj' => $datas[$i]['OemNameKj'],
                     'SiteNameKj' => $datas[$i]['SiteNameKj'],
                     'EnterpriseNameKj' => $datas[$i]['EnterpriseNameKj'],
                     'Incre_Note' => str_replace("\r", " ", str_replace( "\n", " ", $datas[$i]['Incre_Note'] ) ),
                     'Oem_Note' => str_replace("\r", " ", str_replace( "\n", " ", $datas[$i]['Oem_Note'] ) ),
                     'NameKj' => $datas[$i]['NameKj'],
                     'NameKn' => $datas[$i]['NameKn'],
                     'Phone' => $datas[$i]['Phone'],
                     'MailAddress' => $datas[$i]['MailAddress'],
                     'PostalCode' => $datas[$i]['PostalCode'],
                     'UnitingAddress' => $datas[$i]['UnitingAddress'],
                     'MailLimitPassageDate' => $datas[$i]['MailLimitPassageDate'],

                     // 取得方法変更に伴い値の参照方法を修正（2013.3.15 eda）
                     'DestNameKj' => $deli['DestNameKj'],
                     'DestNameKn' => $deli['DestNameKn'],
                     'DestPhone' => $deli['DestPhone'],
                     'DestPostalCode' => $deli['DestPostalCode'],
                     'DestUnitingAddress' => $deli['DestUnitingAddress'],
                     'Deli_JournalIncDate' => $deli['Deli_JournalIncDate'],
                     'Deli_DeliveryMethodName' => $deli['Deli_DeliveryMethodName'],
                     'Deli_JournalNumber' => $deli['Deli_JournalNumber'],
                     'Deli_ConfirmArrivalDate' => $deli['Deli_ConfirmArrivalDate'],

                     'ExecScheduleDate' => $execScheduleDate,

                     'F_ClaimDate' => $datas[$i]['F_ClaimDate'],
                     'F_LimitDate' => $datas[$i]['F_LimitDate'],
                     'eDen' => $datas[$i]['eDen'],
                     'PhoneHistory' => $datas[$i]['PhoneHistory'],
                     'RealSendMailResult' => $datas[$i]['RealSendMailResult'],
                     'Incre_DecisionOpId' => $datas[$i]['Incre_DecisionOpId'],
                     'Incre_ScoreTotal' => $datas[$i]['Incre_ScoreTotal'],
                     'TotalScore' => $datas[$i]['TotalScore'],
                     'Salesman' => $datas[$i]['Salesman'],
                     'CorporateName' => $datas[$i]['CorporateName'],
                     'CreditTransferFlg' => $datas[$i]['CreditTransferFlg'],
                     'CreditTransferRequestFlg' => $datas[$i]['CreditTransferRequestFlg'],
                     'RequestStatus' => $datas[$i]['RequestStatus'],
                     'RequestSubStatus' => $datas[$i]['RequestSubStatus'],
                     'RequestCompDate' => $datas[$i]['RequestCompDate'],
                     'CreditTransferMethod1' => $datas[$i]['CreditTransferMethod1'],
                     'CreditTransferMethod2' => $datas[$i]['CreditTransferMethod2'],
                     'EntCustId' => $datas[$i]['EntCustId'],
                     'Ent_Note' => $datas[$i]['Ent_Note'],                     
             );

            // 再請求日・再請求額を追加する。
            $reclaimHistory = $mdlch->getReClaimHistory( $datas[$i]['OrderSeq'] );
            $rs = new ResultSet();
            $rs->initialize( $reclaimHistory );
            $reclaimHistory = $rs->toArray();

            $itemMax = 6;
            $itemCount = count( $reclaimHistory );
            // 再請求日の部分
            for( $j = 0; $j < $itemMax; $j++ ) {
                $record[$i]['ClaimDate_' . ( $j + 1 ) ] = $reclaimHistory[$j]['ClaimDate'];
                if( empty( $reclaimHistory[$j]['ClaimDate'] ) ) {
                    $record[$i]['ClaimDate_' . ( $j + 1 ) ] = '';
                }
            }

            // 再請求額の部分
            $beforeAmount = 0;
            for ( $j = 0; $j < $itemMax; $j++ ) {
                $nowAmount = (int)$reclaimHistory[$j]['ClaimFee'];
                if( empty( $nowAmount ) ) {
                    $record[$i]['ClaimFee_' . ( $j + 1 ) ] = '';
                }
                else {
                    $record[$i]['ClaimFee_' . ( $j + 1 ) ] = sprintf( '%d', $nowAmount - $beforeAmount );
                    $beforeAmount = $nowAmount;
                }
            }

            // 入金関連・クラス
            $record[$i]['ReceiptDate'] = $datas[$i]['ReceiptDate'];
            $record[$i]['ReceiptAmount'] = $datas[$i]['ReceiptAmount'];
            $record[$i]['ReceiptMethodLabel'] = $datas[$i]['ReceiptMethodLabel'];
            $record[$i]['Incre_ArAddr'] = $datas[$i]['Incre_ArAddr'];
            $record[$i]['Incre_ArTel'] = $datas[$i]['Incre_ArTel'];

            // キャンセル日を追加する。
            $cancel = $mdlcl->findCancel( array( 'OrderSeq' => $datas[$i]['OrderSeq'] ) )->current();
            $record[$i]['CancelDate'] = ( $cancel ) ? $cancel['CancelDate'] : '';

            // キャンセル状態を設定する
            // 注文情報
            // 元々のデータに含まれている項目なのでクエリの発行自体を廃止（2013.3.15 eda）
            // クエリ発行中止に伴い、値の参照方法を修正（2013.3.15 eda）
            switch( $datas[$i]['Cnl_Status'] ) {
                case 0:
                    $record[$i]['Cnl_Status'] = '';
                    break;
                case 1:
                    if( $datas[$i]['Cnl_ReturnSaikenCancelFlg'] == '1' ) {
                        $record[$i]['Cnl_Status'] = '返却依頼中';
                    }
                    else {
                        $record[$i]['Cnl_Status'] = 'キャンセル依頼中';
                    }
                    break;
                case 2:
                    if( $datas[$i]['Cnl_ReturnSaikenCancelFlg'] == '1' ) {
                        $record[$i]['Cnl_Status'] = '返却済み';
                    }
                    else {
                        $record[$i]['Cnl_Status'] = 'キャンセル済み';
                    }
                    break;
            }

            // 商品情報を追加する。
            $deliveryFee = 0;
            $settlementFee = 0;
            // 取得方法を生SQLに変更（2013.3.15 eda）
            $sql = " select * from T_OrderItems where OrderSeq = :OrderSeq and ValidFlg = 1 order by DataClass, OrderItemId ";
            $itemsNeta = $this->app->dbAdapter->query( $sql )->execute( array( ':OrderSeq' => $datas[$i]['OrderSeq'] ) );
            unset( $items );
            $items = array();
            // パフォーマンス改善目的で出力行配列への追加方法などを修正（2013.3.15 eda）

            $j = 0;
            $deliveryFee = 0;
            $settlementFee = 0;
            foreach( $itemsNeta as $item ) {
                if( $j == 30 ) { break; }
                switch( (int)$item['DataClass'] ) {
                    case 2: // 送料
                        $deliveryFee += $item['SumMoney'];
                        break;
                    case 3: // 手数料
                        $settlementFee += $item['SumMoney'];
                        break;
                    default:
                        $record[$i]['ItemNameKj_' . ( $j + 1)] = $item['ItemNameKj'];
                        $record[$i]['UnitPrice_' . ( $j + 1)] = $item['UnitPrice'];
                        $record[$i]['ItemNum_' . ( $j + 1)] = $item['ItemNum'];
                        $j++;
                        break;
                }
            }

            $record[$i]['DeliveryFee'] = $deliveryFee;          // 送料
            $record[$i]['SettlementFee'] = $settlementFee;      // 手数料
            $record[$i]['UseAmount'] = $datas[$i]['UseAmount']; // 利用額

            // 注文登録日
            $record[$i]['RegistDate'] = $datas[$i]['RegistDate'];

            // 審査結果
            if ($datas[$i]['Dmi_Status'] == 1 || ($datas[$i]['Dmi_Status'] == NULL && $datas[$i]['Incre_Status'] == 1)){
                $record[$i]['ExaminationResult'] = 'OK';
            } else if ($datas[$i]['Dmi_Status'] == -1 || $datas[$i]['Incre_Status'] == -1) {
                $record[$i]['ExaminationResult'] = 'NG';
            } else {
                $record[$i]['ExaminationResult'] = '';
            }

            // 支払い約束日
            $record[$i]['PromPayDate'] = $datas[$i]['PromPayDate'];

            // 督促分類
            $record[$i]['RemindClass'] = $datas[$i]['RemindContent'];

            // 訪問未済
            if ($datas[$i]['VisitFlg'] == 1){
                $record[$i]['VisitFlg'] = '訪問済み';
            } else {
                $record[$i]['VisitFlg'] = '未訪問';
            }

            // とりまとめフラグ
            if ($datas[$i]['CombinedClaimTargetStatus'] == 91 || $datas[$i]['CombinedClaimTargetStatus'] == 92){
                if ($datas[$i]['OrderSeq'] == $datas[$i]['P_OrderSeq']){
                    $record[$i]['CombinedFlg'] = '親';
                } else {
                    $record[$i]['CombinedFlg'] = '子';
                }
            } else {
                $record[$i]['CombinedFlg'] = 'なし';
            }

            // NG無保証変更日
            $record[$i]['NgNoGuaranteeChangeDate'] = $datas[$i]['NgNoGuaranteeChangeDate'];

            // NG理由
            if (isset($datas[$i]['AutoNgReason'])) {
                $record[$i]['NgReason'] = $datas[$i]['AutoNgReason'];
            }
            if (isset($datas[$i]['ManualNgReason'])) {
                $record[$i]['NgReason'] = $datas[$i]['ManualNgReason'];
            }
            //トラッキングID
            if($datas[$i]['ExtraPayType'] == 1){
                $record[$i]['ExtraPayKey'] = $datas[$i]['ExtraPayKey'];
            }

            // 顧客ID（事業者別）
            $record[$i]['EntCustSeq'] = $datas[$i]['EntCustSeq'];

        }

        $templateId = 'CKI01033_1';    // 注文情報CSV
        $templateClass = 0;
        $seq = 0;
        $templatePattern = 0;

        $logicTemplate = new LogicTemplate( $this->app->dbAdapter );
        $response = $logicTemplate->convertArraytoResponse( $record, $fileName, $templateId, $templateClass, $seq, $templatePattern, $this->getResponse() );

        if( $response == false ) {
            throw new \Exception( $logicTemplate->getErrorMessage() );
        }

        return $response;
	}

	/**
	 * 立替予定日を取得する。
	 *
	 * @param $oseq 注文Seq
	 * @return string 立替予定日
	 */
	private function getExecScheduleDate($oseq)
	{
        $sql = " SELECT PC.ExecScheduleDate FROM T_PayingControl PC, T_PayingAndSales PAS WHERE PC.Seq = PAS.PayingControlSeq AND PAS.OrderSeq = :OrderSeq ";

        $stm = $this->app->dbAdapter->query($sql);

        $ri = $stm->execute(array(':OrderSeq' => $oseq));

        foreach ($ri as $row) {
            return $row['ExecScheduleDate'];
        }

        return '';
	}

	/**
	 * ビューを使わない検索
	 *
	 * @param $params リクエストパラメーター
	 * @return array 結果配列
	 */
	public function getSearchResult($params)
	{
        $start_time = microtime(true);

        // 過剰入金色分けしきい値
        $excessPaymentColorThreshold = $this->app->dbAdapter->query(
            " SELECT PropValue FROM T_SystemProperty WHERE Module = '[DEFAULT]' AND Category = 'systeminfo' AND Name = 'ExcessPaymentColorThreshold' ")->execute(null)->current()['PropValue'];

        $query = "
            SELECT DISTINCT
                ORD.OrderSeq,
                CASE WHEN ORD.CombinedClaimTargetStatus IN (91, 92)
                    THEN (CASE ORD.P_OrderSeq WHEN ORD.OrderSeq THEN '親' ELSE '子' END)
                    ELSE ''
                END AS CombinedClaim,
                (SELECT COUNT(1) FROM T_Order WHERE P_OrderSeq = ORD.P_OrderSeq AND OrderSeq <> P_OrderSeq) AS CombinedCnt,
                ORD.OrderId,
                ORD.Ent_OrderId,
                ORD.Oem_OrderId,
                ENT.EnterpriseNameKj,
                SITE.SiteNameKj,
                ORD.ReceiptOrderDate,
                ORD.ServiceExpectedDate,
                CUS.NameKj,
                CUS.NameKn,
                CUS.PostalCode,
                CUS.UnitingAddress,
                CUS.Phone,
                CUS.MailAddress,
                (SELECT MAX(Deli_JournalIncDate) FROM T_OrderItems WHERE OrderSeq = ORD.OrderSeq AND ValidFlg = 1) AS Deli_JournalIncDate,
                MDM.DeliMethodName,
                ITM.Deli_JournalNumber,
                CC.F_ClaimDate,
                CC.F_LimitDate,
                CC.F_LimitDate AS Clm_F_LimitDate,
                ORD.UseAmount,
                RC.ReceiptDate,
                CC.ReceiptAmountTotal AS ReceiptAmount,
                RC.ReceiptClass,
            	CASE ORD.Rct_Status
                   WHEN '0' THEN '未入金'
                   WHEN '1' THEN (SELECT KeyContent FROM M_Code WHERE CodeId = 198 AND KeyCode = RC.ReceiptClass)
                   ELSE ''
                END AS ReceiptMethodLabel,
                ORD.Incre_Note,
                ORD.MailLimitPassageDate,
                (SELECT KeyContent FROM M_Code WHERE CodeId = 13 AND KeyCode = CUS.eDen) AS eDen,
                (SELECT KeyContent FROM M_Code WHERE CodeId = 14 AND KeyCode = CUS.PhoneHistory) AS PhoneHistory,
                CASE WHEN IFNULL(ORD.Incre_DecisionOpId, 0) = 0 THEN ORD.Incre_DecisionOpId
                     ELSE (SELECT NameKj FROM T_Operator WHERE OpId = ORD.Incre_DecisionOpId)
                END AS Incre_DecisionOpId,
                (SELECT Class2 FROM M_Code WHERE CodeId = 4 AND KeyCode = CUS.Incre_ArAddr) AS Incre_ArAddr,
                (SELECT Class2 FROM M_Code WHERE CodeId = 4 AND KeyCode = CUS.Incre_ArTel) AS Incre_ArTel,
                CASE CUS.RealSendMailResult WHEN 1 THEN 'OK' WHEN 2 THEN 'NG' ELSE '' END AS RealSendMailResult,
                ORD.DataStatus,
                ORD.CloseReason,
                ORD.Rct_Status,
                /* RC.ReceiptDate AS ReceiptDate, */
                ENT.Salesman,
                ORD.Cnl_Status,
                ORD.Cnl_ReturnSaikenCancelFlg,
                OEM.OemNameKj,
                ORD.Oem_Note,
                ORD.Incre_ScoreTotal,
                CASE WHEN CJR.TotalScoreWeighting IS NOT NULL THEN (CJR.TotalScoreWeighting + IFNULL(ORD.Incre_JudgeScoreTotal,0))
                     WHEN CJR.TotalScore IS NOT NULL THEN (CJR.TotalScore + IFNULL(ORD.Incre_JudgeScoreTotal,0))
                     ELSE NULL
                END AS TotalScore,
                CC.ReceiptAmountTotal AS InstallmentPlanAmount,
                CUS.CustomerId,
                ORD.P_OrderSeq,
                VCR.ReceiptDate AS CloseReceiptDate,
                ORD.RegistDate,
                ORD.Dmi_Status,
                ORD.Incre_Status,
                ORD.PromPayDate,
                (SELECT KeyContent FROM M_Code WHERE CodeId = 18 And KeyCode = ORD.RemindClass) AS RemindContent,
                ORD.VisitFlg,
                ORD.CombinedClaimTargetStatus,
                CC.ClaimedBalance AS Rct_DifferentialAmount,
                $excessPaymentColorThreshold AS ExcessPaymentColorThreshold,
                CASE WHEN AORD.AutoJudgeNgReasonCode IS NOT NULL THEN (SELECT Note FROM M_Code WHERE CodeId = 191 AND KeyCode = AORD.AutoJudgeNgReasonCode)
                     ELSE NULL
                END AS AutoNgReason,
                CASE WHEN AORD.ManualJudgeNgReasonCode IS NOT NULL THEN (SELECT Note FROM M_Code WHERE CodeId = 190 AND KeyCode = AORD.ManualJudgeNgReasonCode)
                     ELSE NULL
                END AS ManualNgReason,
                AORD.NgNoGuaranteeChangeDate AS NgNoGuaranteeChangeDate,
                AORD.ExtraPayType AS ExtraPayType,
                AORD.ExtraPayKey AS ExtraPayKey,
                EC.EntCustSeq AS EntCustSeq,
                CUS.CorporateName AS CorporateName,
                CASE WHEN CC.CreditTransferFlg = 0 THEN ''
                    WHEN CC.CreditTransferFlg = 1 THEN '利用する（SMBC）'
                    WHEN CC.CreditTransferFlg = 2 THEN '利用する（MUFJ）'
                    WHEN CC.CreditTransferFlg = 3 THEN '利用する（みずほ）'
                    ELSE ''
                END AS CreditTransferFlg,
                CASE WHEN AORD.CreditTransferRequestFlg = 0 THEN ''
                    WHEN AORD.CreditTransferRequestFlg = 1 THEN '利用する（WEB）'
                    WHEN AORD.CreditTransferRequestFlg = 2 THEN '利用する（紙）'
                    ELSE ''
                END AS CreditTransferRequestFlg,
            	CASE EC.RequestStatus
                   WHEN NULL THEN ''
                   WHEN '0' THEN ''
                   ELSE (SELECT KeyContent FROM M_Code WHERE CodeId = 196 AND KeyCode = EC.RequestStatus)
                END AS RequestStatus,
            	CASE EC.RequestSubStatus
                   WHEN NULL THEN ''
                   WHEN '0' THEN ''
                   ELSE (SELECT KeyContent FROM M_Code WHERE CodeId = 210 AND KeyCode = EC.RequestSubStatus)
                END AS RequestSubStatus,
                EC.RequestCompDate AS RequestCompDate,
            	CASE CH.CreditTransferMethod
                   WHEN '3' THEN '口座振替'
                   ELSE ''
                END AS CreditTransferMethod1,
            	CASE CH.CreditTransferMethod
                   WHEN '1' THEN '初回申込用紙発行'
                   ELSE ''
                END AS CreditTransferMethod2,
                CUS.EntCustId,
                ORD.Ent_Note
            FROM
                T_Order ORD %s
                INNER JOIN T_Customer CUS ON (ORD.OrderSeq = CUS.OrderSeq)
                INNER JOIN T_OrderItems ITM ON (ORD.OrderSeq = ITM.OrderSeq)
                LEFT OUTER JOIN M_DeliveryMethod MDM ON (ITM.Deli_DeliveryMethod = MDM.DeliMethodId)
                INNER JOIN T_DeliveryDestination DELI ON (ITM.DeliDestId = DELI.DeliDestId)
                INNER JOIN T_Enterprise ENT ON (ORD.EnterpriseId = ENT.EnterpriseId)
                LEFT OUTER JOIN T_Oem OEM ON (OEM.OemId = ENT.OemId)
                LEFT OUTER JOIN T_CjResult CJR ON (
                    ORD.OrderSeq = CJR.OrderSeq AND
                    CJR.Seq = (
                        SELECT MAX(Seq)
                        FROM T_CjResult CJR2
                        WHERE CJR2.OrderSeq = ORD.OrderSeq
                    )
                )
                LEFT OUTER JOIN T_CjResult_Detail CJRD ON (CJRD.CjrSeq = CJR.Seq)
                INNER JOIN T_Site SITE ON (ORD.SiteId = SITE.SiteId)
                LEFT OUTER JOIN T_ClaimControl CC ON (CC.OrderSeq = ORD.P_OrderSeq)
                LEFT OUTER JOIN T_ReceiptControl RC ON RC.ReceiptSeq = CC.LastReceiptSeq
                LEFT OUTER JOIN T_EnterpriseCustomer EC ON (EC.EntCustSeq = CUS.EntCustSeq)
                LEFT OUTER JOIN T_ManagementCustomer MC ON (MC.ManCustId = EC.ManCustId)
                LEFT OUTER JOIN T_Cancel CNCL ON (CNCL.OrderSeq = ORD.OrderSeq AND CNCL.ValidFlg = 1)
                LEFT OUTER JOIN T_PayingControl PC ON (PC.Seq = ORD.Chg_Seq)
                LEFT OUTER JOIN T_PayingAndSales PAS ON (PAS.OrderSeq = ORD.OrderSeq)
                LEFT OUTER JOIN T_PayingBackControl PBC ON (PBC.OrderSeq = ORD.OrderSeq AND PBC.ValidFlg = 1)
                LEFT OUTER JOIN M_PayingCycle MPC ON (MPC.PayingCycleId = ENT.PayingCycleId)
                LEFT OUTER JOIN V_CloseReceiptControl VCR ON (VCR.OrderSeq = CC.OrderSeq)
                INNER JOIN AT_Order AORD ON (ORD.OrderSeq = AORD.OrderSeq)
                LEFT OUTER JOIN T_MypageOrder MO ON (ORD.OrderSeq = MO.OrderSeq AND MO.ValidFlg = 1)
                LEFT OUTER JOIN MPV_MypageOrderLogin VMOL ON (MO.Seq = VMOL.Seq)
                LEFT OUTER JOIN T_ClaimHistory CH ON (
                    ORD.P_OrderSeq = CH.OrderSeq AND
                    CH.Seq = (
                        SELECT MAX(Seq)
                        FROM T_ClaimHistory CH2
                        WHERE CH2.OrderSeq = ORD.P_OrderSeq
                        AND CH2.ClaimPattern = 1
                        AND CH2.ValidFlg = 1
                    )
                )
            WHERE
                1 = 1
                AND ITM.ValidFlg = 1
                AND DELI.ValidFlg = 1
                %s
            ";

        $queryCnt = "
            SELECT DISTINCT
                ORD.OrderSeq,
                ORD.UseAmount,
                ORD.Cnl_Status,
                ORD.DataStatus,
                ORD.CloseReason,
                CASE WHEN (IFNULL(ORD.Cnl_Status, 0) != 0) OR (ORD.DataStatus = 91 AND ORD.CloseReason = 4) THEN 0 -- ｷｬﾝｾﾙ OR 貸し倒れは除外
                     WHEN IFNULL((SELECT ReceiptAmountTotal FROM T_ClaimControl WHERE OrderSeq = ORD.OrderSeq), 0)
                       >= IFNULL((SELECT UseAmountTotal FROM T_ClaimControl WHERE OrderSeq = ORD.OrderSeq), 0) THEN IFNULL((SELECT UseAmountTotal FROM T_ClaimControl WHERE OrderSeq = ORD.OrderSeq), 0)
                     ELSE IFNULL((SELECT ReceiptAmountTotal FROM T_ClaimControl WHERE OrderSeq = ORD.OrderSeq), 0)
                END AS CalcReceiptAmount
            FROM
                T_Order ORD %s
                INNER JOIN T_Customer CUS ON (ORD.OrderSeq = CUS.OrderSeq)
                INNER JOIN T_OrderItems ITM ON (ORD.OrderSeq = ITM.OrderSeq)
                LEFT OUTER JOIN M_DeliveryMethod MDM ON (ITM.Deli_DeliveryMethod = MDM.DeliMethodId)
                INNER JOIN T_DeliveryDestination DELI ON (ITM.DeliDestId = DELI.DeliDestId)
                INNER JOIN T_Enterprise ENT ON (ORD.EnterpriseId = ENT.EnterpriseId)
                LEFT OUTER JOIN T_Oem OEM ON (OEM.OemId = ENT.OemId)
                LEFT OUTER JOIN T_CjResult CJR ON (
                    ORD.OrderSeq = CJR.OrderSeq AND
                    CJR.Seq = (
                        SELECT MAX(Seq)
                        FROM T_CjResult CJR2
                        WHERE CJR2.OrderSeq = ORD.OrderSeq
                    )
                )
                LEFT OUTER JOIN T_CjResult_Detail CJRD ON (CJRD.CjrSeq = CJR.Seq)
                INNER JOIN T_Site SITE ON (ORD.SiteId = SITE.SiteId)
                LEFT OUTER JOIN T_ClaimControl CC ON (CC.OrderSeq = ORD.P_OrderSeq)
                LEFT OUTER JOIN T_ReceiptControl RC ON RC.ReceiptSeq = CC.LastReceiptSeq
                LEFT OUTER JOIN T_EnterpriseCustomer EC ON (EC.EntCustSeq = CUS.EntCustSeq)
                LEFT OUTER JOIN T_ManagementCustomer MC ON (MC.ManCustId = EC.ManCustId)
                LEFT OUTER JOIN T_Cancel CNCL ON (CNCL.OrderSeq = ORD.OrderSeq AND CNCL.ValidFlg = 1)
                LEFT OUTER JOIN T_PayingControl PC ON (PC.Seq = ORD.Chg_Seq)
                LEFT OUTER JOIN T_PayingAndSales PAS ON (PAS.OrderSeq = ORD.OrderSeq)
                LEFT OUTER JOIN T_PayingBackControl PBC ON (PBC.OrderSeq = ORD.OrderSeq AND PBC.ValidFlg = 1)
                LEFT OUTER JOIN M_PayingCycle MPC ON (MPC.PayingCycleId = ENT.PayingCycleId)
                LEFT OUTER JOIN V_CloseReceiptControl VCR ON (VCR.OrderSeq = CC.OrderSeq)
                INNER JOIN AT_Order AORD ON (ORD.OrderSeq = AORD.OrderSeq)
                LEFT OUTER JOIN T_MypageOrder MO ON (ORD.OrderSeq = MO.OrderSeq AND MO.ValidFlg = 1)
                LEFT OUTER JOIN MPV_MypageOrderLogin VMOL ON (MO.Seq = VMOL.Seq)
                LEFT OUTER JOIN T_ClaimHistory CH ON (
                    ORD.P_OrderSeq = CH.OrderSeq AND
                    CH.Seq = (
                        SELECT MAX(Seq)
                        FROM T_ClaimHistory CH2
                        WHERE CH2.OrderSeq = ORD.P_OrderSeq
                        AND CH2.ClaimPattern = 1
                        AND CH2.ValidFlg = 1
                    )
                )
            WHERE
                1 = 1
                AND ITM.ValidFlg = 1
                AND DELI.ValidFlg = 1
                %s
            ";

        $relatePayingControl = "";		// 2013.12.6 kashira T_PayingControl をリレーションする場合にセットされる
        $where = "";
        $whereReceipt = "";

        // WHERE句の追加

        //検索範囲
        switch (intval($params['SearchRange'])) {
            //キャッチボールのみ
            case 1 :
                $search_where .= " AND IFNULL(ENT.OemId,0) = 0";
                break;
                //OEM先のみ
            case 2 :
                $search_where .= " AND IFNULL(ENT.OemId,0) <> 0";
                break;
                //それ以外 全てと同じ扱いとする
            default :
                break;
        }

				//請求代行プラン BillingAgentPlan
				switch($params['BillingAgentSts'])
        {
            case '0':
                $where .= " AND (ENT.BillingAgentFlg = 0)";
                break;
            case '2':
                $where .= " AND ENT.BillingAgentFlg = 1";
                break;
            default:
                break;
        }

        // OEMID
        if ($params['Oem'] != 0) {
            $where .= " AND OEM.OemId like '%" . mb_convert_kana($params['Oem'], 'a', 'UTF-8') . "'";
        }

        // OEM注文ID
        if ($params['Oem_OrderId'] != '') {
            $where .= " AND ORD.Oem_OrderId like '%" . mb_convert_kana(BaseUtility::escapeWildcard($params['Oem_OrderId']), 'a', 'UTF-8')  . "'";
        }

        // クレジット決済
        switch (intval($params['ExtraPayType'])) {
            //含めない
            case 0 :
                $where .= " AND IFNULL(AORD.ExtraPayType, 0) <> 1 ";
                break;
            //のみ
            case 2 :
               $where .= " AND AORD.ExtraPayType = 1 ";
                break;
            //それ以外
            default :
                break;
        }

        // 取りまとめ親子
        if(isset($params['TrackingId']) && trim($params['TrackingId'])) {
            $TrackingId = $params['TrackingId'];
            $where .= " AND AORD.ExtraPayKey = '" . $TrackingId . "'";
        }

        // 注文ID
        if ($params['OrderId'] != '') {
            $orderIdList = mb_convert_kana(strrev($params['OrderId']), 'a', 'UTF-8');
            $orderIdList = preg_replace("/\s/", ",", $orderIdList);
            if(!preg_match('/^[a-zA-Z0-9,]+$/', $orderIdList)) {
                throw new SearchoControllerException('注文IDに不正な文字が含まれています');
            }
            $orderId = explode(",", $orderIdList);

            // count関数対策
            $orderIdLen = 0;
            if(!empty($orderId)) {
                $orderIdLen = count($orderId);
            }

            for ($i = 0 ; $i < $orderIdLen ; $i++) {
                if ($orderId[$i] != "" && $OrderIdwhere == "") {
                    $OrderIdwhere = " AND ( ORD.ReverseOrderId like '" . $orderId[$i] . "%'";
                } elseif ($orderId[$i] != "" && $OrderIdwhere != "") {
                    $OrderIdwhere .= " OR ORD.ReverseOrderId like '" . $orderId[$i] . "%'";
                }
            }

            if ($OrderIdwhere != "") {
                $where .= $OrderIdwhere . " ) ";
            }
        }

        // 注文登録日
        $wRegistDate = BaseGeneralUtils::makeWhereDateTime(
            'ORD.RegistDate',
            BaseGeneralUtils::convertWideToNarrow($params['RegistDateF']),
            BaseGeneralUtils::convertWideToNarrow($params['RegistDateT'])
        );
        if ($wRegistDate != '') {
            $where .= " AND " . $wRegistDate;
        }

        // 注文日
        $wReceiptOrderDate = BaseGeneralUtils::makeWhereDate(
            'ORD.ReceiptOrderDate',
            BaseGeneralUtils::convertWideToNarrow($params['OrderDateF']),
            BaseGeneralUtils::convertWideToNarrow($params['OrderDateT'])
        );
        if ($wReceiptOrderDate != '') {
            $where .= " AND " . $wReceiptOrderDate;
        }

        // 役務提供予定日
        $wServiceExpectedDate = BaseGeneralUtils::makeWhereDate(
            'ORD.ServiceExpectedDate',
            BaseGeneralUtils::convertWideToNarrow($params['ServiceExpectedDateF']),
            BaseGeneralUtils::convertWideToNarrow($params['ServiceExpectedDateT'])
        );
        if ($wServiceExpectedDate != '') {
            $where .= " AND " . $wServiceExpectedDate;
        }

        // 与信クラス(住所)
        switch($params['CreditClass'])
        {
            case '1':                                   // ブラック
                $where .= " AND CUS.Incre_ArAddr = 5";
                break;
            case '2':                                   // 優良
                $where .= " AND CUS.Incre_ArAddr = 2";
                break;
            default:
                break;
        }

        // 与信クラス(TEL)
        switch($params['CreditTelClass'])
        {
            case '1':                                   // ブラック
                $where .= " AND CUS.Incre_ArTel = 5";
                break;
            case '2':                                   // 優良
                $where .= " AND CUS.Incre_ArTel = 2";
                break;
            default:
                break;
        }

        // 別管理
        switch($params['BetsuKanri'])
        {
            case '1':
                $where .= " AND (ORD.Bekkan IS NULL OR ORD.Bekkan = 0)";
                break;
            case '2':
                $where .= " AND ORD.Bekkan = 1";
                break;
            default:
                break;
        }

        // ステータス
        if (isset($params['Status']) && $params['Status'] != '0' && is_numeric($params['Status'])) {
            $where .= " AND ORD.DataStatus = " . $params['Status'];
        }

        // 注文状態
        switch($params['OrderStatus'])
        {
            case '1':
                $where .= " AND ORD.DataStatus = 91 AND ORD.CloseReason = 6 ";
                break;
            case '2':
                $where .= " AND ORD.DataStatus = 91 AND ORD.CloseReason = 5 ";
                break;
            case '3':
                $where .= " AND ORD.DataStatus = 91 AND ORD.CloseReason = 4 ";
                break;
            case '4':
                $where .= " AND ORD.DataStatus = 91 AND ORD.CloseReason = 3 ";
                break;
            case '5':
                $where .= " AND ORD.DataStatus = 91 AND ORD.CloseReason = 2 AND IFNULL(ORD.Cnl_ReturnSaikenCancelFlg, 0) = 0 ";
                break;
            case '6':
                $where .= " AND ORD.DataStatus = 91 AND ORD.CloseReason = 2 AND IFNULL(ORD.Cnl_ReturnSaikenCancelFlg, 0) = 1 ";
                break;
            case '7':
                $where .= " AND ORD.Cnl_Status = 1 AND IFNULL(ORD.Cnl_ReturnSaikenCancelFlg, 0) = 0 ";
                break;
            case '8':
                $where .= " AND ORD.Cnl_Status = 1 AND IFNULL(ORD.Cnl_ReturnSaikenCancelFlg, 0) = 1 ";
                break;
            case '9':
                $where .= " AND ORD.DataStatus < 61 AND ORD.Cnl_Status = 0 AND IFNULL(CC.F_LimitDate, '2100-12-31') >= DATE(NOW()) ";
                break;
            case '10':
                $where .= " AND ORD.DataStatus = 61 AND ORD.Cnl_Status = 0 ";
                break;
            case '11':
                $where .= " AND ORD.DataStatus = 91 AND ORD.CloseReason = 1 AND ORD.Cnl_Status = 0 AND CC.F_LimitDate >= VCR.ReceiptDate ";
                break;
            case '12':
                $where .= " AND ORD.DataStatus = 91 AND ORD.CloseReason = 1 AND ORD.Cnl_Status = 0 AND CC.F_LimitDate < VCR.ReceiptDate ";
                break;
            case '13':
                $where .= " AND ORD.DataStatus < 61 AND ORD.Cnl_Status = 0 AND IFNULL(CC.F_LimitDate, '2100-12-31') < DATE(NOW()) ";
                break;
            default:
                break;
        }

        // 請求先氏名
        if ($params['NameKj'] != '') {
            $where .= " AND CUS.SearchNameKj like '%" . mb_ereg_replace(self::REGEXP_TRIM_NAME, '', BaseUtility::escapeWildcard($params['NameKj'])) . "%'";
        }

        // 請求先カナ氏名
        if ($params['NameKn'] != '') {
            $where .= " AND CUS.SearchNameKn like '%" . mb_ereg_replace(self::REGEXP_TRIM_NAME, '', BaseUtility::escapeWildcard($params['NameKn'])) . "%'";
        }

        // 請求先郵便番号
        if ($params['PostalCode'] != '') {
            $where .= " AND CUS.PostalCode = '" . BaseGeneralUtils::convertNumberWideToNarrow(BaseUtility::escapeWildcard($params['PostalCode'])) . "'";
        }

        // 請求先住所
        if ($params['Address'] != '') {
            $where .= " AND CUS.SearchUnitingAddress like '%" . mb_ereg_replace(self::REGEXP_TRIM_NAME, '', BaseUtility::escapeWildcard($params['Address'])) . "%'";
        }

        // 請求先電話番号
        if ($params['Phone'] != '') {
            $where .= " AND CUS.SearchPhone like '%" . BaseGeneralUtils::convertWideToNarrow(mb_ereg_replace(self::REGEXP_TRIM_PHONE, '', $params['Phone'])) . "%'";
        }

        // 請求先メールアドレス
        if ($params['MailAddress'] != '') {
            $where .= " AND CUS.MailAddress like '%" . BaseGeneralUtils::convertWideToNarrow(BaseUtility::escapeWildcard($params['MailAddress'])) . "%'";
        }

        // 加盟店顧客番号
        if ($params['EntCustId'] != '') {
            $where .= " AND CUS.EntCustId like '%" . BaseGeneralUtils::convertWideToNarrow(BaseUtility::escapeWildcard($params['EntCustId'])) . "%'";
        }

        // 配送先氏名
        if ($params['DeliNameKj'] != '') {
            $where .= " AND DELI.SearchDestNameKj like '%" . mb_ereg_replace(self::REGEXP_TRIM_NAME, '', BaseUtility::escapeWildcard($params['DeliNameKj'])) . "%'";
        }

        // 配送先氏名カナ
        if ($params['DeliNameKn'] != '') {
            $where .= " AND DELI.SearchDestNameKn like '%" . mb_ereg_replace(self::REGEXP_TRIM_NAME, '', BaseUtility::escapeWildcard($params['DeliNameKn'])) . "%'";
        }

        // 配送先郵便番号
        if ($params['DeliPostalCode'] != '') {
            $where .= " AND DELI.PostalCode = '" . BaseGeneralUtils::convertNumberWideToNarrow(BaseUtility::escapeWildcard($params['DeliPostalCode'])) . "'";
        }

        // 配送先住所
        if ($params['DeliAddress'] != '') {
            $where .= " AND DELI.SearchUnitingAddress like '%" . mb_ereg_replace(self::REGEXP_TRIM_NAME, '', BaseUtility::escapeWildcard($params['DeliAddress'])) . "%'";
        }

        // 配送先電話
        if ($params['DeliPhone'] != '') {
            $where .= " AND DELI.SearchPhone like '%" . BaseGeneralUtils::convertWideToNarrow(mb_ereg_replace(self::REGEXP_TRIM_PHONE, '', $params['DeliPhone'])) . "%'";
        }

        // 事業者名
        if ($params['EnterpriseNameKj'] != '') {
            $EntSql = "SELECT GROUP_CONCAT(EnterpriseId) AS EntIdList FROM T_Enterprise WHERE EnterpriseNameKj like " . $this->app->dbAdapter->getPlatform()->quoteValue("%" . $params['EnterpriseNameKj'] . "%") . ";";

            $entid = $this->app->dbAdapter->query($EntSql)->execute(null)->current()['EntIdList'];
            $entid = "-1," . $entid;

            $where .= " AND ENT.EnterpriseId IN ($entid) ";
        }

        // 事業者ID
        if ($params['LoginId'] != '')
        {
            // 指定IDに後方一致するすべてのログインIDのバリエーションをリストとして取得
            $ids = $this->_fixEntLoginIdForSearch(BaseGeneralUtils::convertWideToNarrow(BaseUtility::escapeWildcard($params['LoginId'])));
            // 取得できたログインIDが0件の場合は存在しないIDが指定されたものとする（※ロジック上ありえないが…）
            // count関数対策
            if(empty($ids)) $ids = array('AT00000000');
            // INで条件指定
            $where .= (" AND ENT.LoginId IN (" . MakeQueryValStrPhraseInWithCoat($ids) . ") ");
        }

        // サイト名
        if ($params['SiteName'] != '') {
            $where .= " AND SITE.SiteNameKj like " . $this->app->dbAdapter->getPlatform()->quoteValue("%" . $params['SiteName'] . "%");
        }

        if ($params['SiteID'] != '') {
            $siteID = $params['SiteID'];
            $where .= " AND ORD.SiteId = '" . $siteID . "'";
        }

        // 与信点数
        $wCreditScore = BaseGeneralUtils::makeWhereInt('ORD.Incre_ScoreTotal', $params['CreditScoreF'], $params['CreditScoreT']);
        if ($wCreditScore != '') {
            $where .= " AND " . $wCreditScore;
        }

        // 与信結果
        switch($params['CreditResult'])
        {
            case '1':
                $where .= " AND (ORD.Dmi_Status = 1 OR (ORD.Dmi_Status IS NULL AND ORD.Incre_Status = 1)) ";
                break;
            case '2':
                $where .= " AND (ORD.Dmi_Status = -1 OR ORD.Incre_Status = -1)";
                break;
            default:
                break;
        }

        // NG無保証
        if (isset($params['NgNoGuaranteeChange']) || isset($params['NgNoGuaranteeChange0'])) {
            $where .= " AND (ORD.Dmi_Status = -1 OR ORD.Incre_Status = -1) ";
            if (isset($params['NgNoGuaranteeChange']) && !isset($params['NgNoGuaranteeChange0'])) {
                $where .= " AND AORD.NgButtonFlg = 1 AND CURDATE() <= AORD.NoGuaranteeChangeLimitDay ";
            } else if (!isset($params['NgNoGuaranteeChange']) && isset($params['NgNoGuaranteeChange0'])) {
                $where .= " AND ((AORD.NgButtonFlg = 1 AND CURDATE() > AORD.NoGuaranteeChangeLimitDay) ";
                $where .= " OR    IFNULL(AORD.NgButtonFlg, 0) = 0) ";
            } else {
                ;// [無保証変更可][無保証変更不可]ともにオフ時は条件指定不要
            }
        }

        // 審査担当者
        if ($params['OperatorId'] == -1) {
            $where .= " AND IFNULL(ORD.Incre_DecisionOpId, 0) = 0 ";
        } elseif ($params['OperatorId'] == -2) {
            $where .= " AND IFNULL(ORD.Incre_DecisionOpId, 0) > 0 ";
        } elseif ($params['OperatorId'] > 0) {
            $where .= " AND ORD.Incre_DecisionOpId = " . $params['OperatorId'];
        }

        // OKチケット発行日
        $wTicketRegistDate = BaseGeneralUtils::makeWhereDateTime(
            'RegistDate',
            BaseGeneralUtils::convertWideToNarrow($params['TicketRegistDateF']),
            BaseGeneralUtils::convertWideToNarrow($params['TicketRegistDateT'])
        );
        if ($wTicketRegistDate != '') {
            $where .= " AND ORD.OrderSeq IN ( SELECT OrderSeq FROM T_CreditOkTicket WHERE 1 = 1 AND Status IN (0, 1) AND $wTicketRegistDate ) ";
        }

        // OKチケット使用日
        $wTicketUseDate = BaseGeneralUtils::makeWhereDateTime(
            'UseDate',
            BaseGeneralUtils::convertWideToNarrow($params['TicketUseDateF']),
            BaseGeneralUtils::convertWideToNarrow($params['TicketUseDateT'])
        );
        if ($wTicketUseDate != '') {
            $where .= " AND ORD.OrderSeq IN ( SELECT UseOrderSeq FROM T_CreditOkTicket WHERE 1 = 1 AND Status IN (0, 1) AND $wTicketUseDate ) ";
        }

        // NG無保証変更日
        $wNgNoGuaranteeChangeDate = BaseGeneralUtils::makeWhereDateTime(
        'AORD.NgNoGuaranteeChangeDate',
        BaseGeneralUtils::convertWideToNarrow($params['NgNoGuaranteeChangeDateF']),
        BaseGeneralUtils::convertWideToNarrow($params['NgNoGuaranteeChangeDateT'])
        );
        if ($wNgNoGuaranteeChangeDate != '') {
            $where .= " AND $wNgNoGuaranteeChangeDate ";
        }

        // 保留ボックス
        if (isset($params['IsHoldBox'])) {
            $where .= " AND AORD.DefectFlg = 1 ";
            $where .= " AND ORD.Cnl_Status = 0 ";
            $where .= " AND ORD.DataStatus < 31 ";
        }


        // 伝票番号登録(登録済み／未登録)
        switch($params['RegistJournal'])
        {
            case '1':
                $where .= " AND ITM.DataClass = 1 AND ITM.Deli_JournalIncDate IS NOT NULL";
                break;
            case '2':
                $where .= " AND ITM.DataClass = 1 AND ITM.Deli_JournalIncDate IS NULL";
                break;
            default:
                break;
        }

        // 伝票番号登録(必要／不要)
        switch($params['JournalRegistClass'])
        {
            case '1':
                $where .= " AND MDM.JournalRegistClass = 1 ";
                break;
            case '2':
                $where .= " AND MDM.JournalRegistClass = 0";
                break;
            default:
                break;
        }

        // 伝票番号登録日
        $wDeli_JournalIncDate = BaseGeneralUtils::makeWhereDateTime(
            'ITM.Deli_JournalIncDate',
            BaseGeneralUtils::convertWideToNarrow($params['Deli_JournalIncDateF']),
            BaseGeneralUtils::convertWideToNarrow($params['Deli_JournalIncDateT'])
        );
        if ($wDeli_JournalIncDate != '') {
            $where .= " AND " . $wDeli_JournalIncDate;
        }

        // 配送方法
        if (isset($params['Deli_DeliveryMethod']) && $params['Deli_DeliveryMethod'] != '0' && is_numeric($params['Deli_DeliveryMethod'])) {
            $where .= " AND MDM.DeliMethodId = " . $params['Deli_DeliveryMethod'];
        }

        // 請求日
        $wClaimDate = BaseGeneralUtils::makeWhereDate(
            'CC.F_ClaimDate',
            BaseGeneralUtils::convertWideToNarrow($params['ClaimDateF']),
            BaseGeneralUtils::convertWideToNarrow($params['ClaimDateT'])
        );
        if ($wClaimDate != '') {
            $where .= " AND " . $wClaimDate;
        }

        // 支払期限
        $wLimitDate = BaseGeneralUtils::makeWhereDate(
            'CC.F_LimitDate',
            BaseGeneralUtils::convertWideToNarrow($params['LimitDateF']),
            BaseGeneralUtils::convertWideToNarrow($params['LimitDateT'])
        );
        if ($wLimitDate != '') {
            $where .= " AND " . $wLimitDate;
        }

        // 請求金額
        $wClaimAmount = BaseGeneralUtils::makeWhereInt(
            'ORD.UseAmount',
            BaseGeneralUtils::convertWideToNarrow($params['ClaimAmountF']),
            BaseGeneralUtils::convertWideToNarrow($params['ClaimAmountT'])
        );
        if ($wClaimAmount != '') {
            $where .= " AND " . $wClaimAmount;
        }

        // 戻り請求書
        if (isset($params['IsReturnClaim'])) {
            $where .= " AND ORD.ReturnClaimFlg = 1";
        }

        // 同梱/別送
        if (isset($params['ClaimSendingClass1']) && !isset($params['ClaimSendingClass2'])) {
            $where .= " AND ORD.ClaimSendingClass = 11 ";
        }
        else if (!isset($params['ClaimSendingClass1']) && isset($params['ClaimSendingClass2'])) {
            $where .= " AND ORD.ClaimSendingClass IN (12, 21) ";
        }
        else {
            ;// // [同梱請求書][別送請求書]ともにオンorオフ時は条件指定不要
        }

        // 着荷確認
        switch($params['ArrivalConfirm'])
        {
            case '1':
                $where .= " AND ITM.DataClass = 1 AND ITM.Deli_ConfirmArrivalFlg = 1";
                break;
            case '2':
                $where .= " AND ITM.DataClass = 1 AND ITM.Deli_ConfirmArrivalFlg < 1";
                break;
            default:
                break;
        }

        // 着荷確認日 2013.12.6 kashira
        $wDeliConfirmArrivalDate = BaseGeneralUtils::makeWhereDateTime(
            'ORD.Deli_ConfirmArrivalDate',
            BaseGeneralUtils::convertWideToNarrow($params['DeliConfirmArrivalDateF']),
            BaseGeneralUtils::convertWideToNarrow($params['DeliConfirmArrivalDateT'])
        );
        if ($wDeliConfirmArrivalDate != '') {
            $where .= " AND ORD.Deli_ConfirmArrivalFlg = 1 AND " . $wDeliConfirmArrivalDate;
        }

        // 立替予定日 2013.12.6 kashira
        $wExecScheduleDate = BaseGeneralUtils::makeWhereDate(
            'PC.ExecScheduleDate',
            BaseGeneralUtils::convertWideToNarrow($params['ExecScheduleDateF']),
            BaseGeneralUtils::convertWideToNarrow($params['ExecScheduleDateT'])
        );
        if ($wExecScheduleDate != '') {
            $where .= " AND " . $wExecScheduleDate;
        }

        // 立替実行
        // 立替実行は立替確定が行われているか否かの検索条件に変更
        switch($params['ExecCharge'])
        {
            case '1':
                $where .= " AND PAS.PayingControlStatus = 1 ";
                break;
            case '2':
                $where .= " AND PAS.PayingControlStatus = 0 ";
                break;
            default:
                break;
        }

        // 臨時立替日
        $wSpecialPayingDate = BaseGeneralUtils::makeWhereDate(
            'PAS.SpecialPayingDate',
            BaseGeneralUtils::convertWideToNarrow($params['SpecialPayingDateF']),
            BaseGeneralUtils::convertWideToNarrow($params['SpecialPayingDateT'])
        );
        if ($wSpecialPayingDate != '') {
            $where .= " AND " . $wSpecialPayingDate;
        }

        // 立替精算戻し日
        $wPayBackIndicationDate = BaseGeneralUtils::makeWhereDate(
            'PBC.PayDecisionDate',
            BaseGeneralUtils::convertWideToNarrow($params['PayBackIndicationDateF']),
            BaseGeneralUtils::convertWideToNarrow($params['PayBackIndicationDateT'])
        );
        if ($wPayBackIndicationDate != '') {
            $where .= " AND " . $wPayBackIndicationDate;
        }

        // 入金確認日
        $wReceiptConfirm = BaseGeneralUtils::makeWhereDateTime(
            'R1.ReceiptProcessDate',
            BaseGeneralUtils::convertWideToNarrow($params['ReceiptConfirmF']),
            BaseGeneralUtils::convertWideToNarrow($params['ReceiptConfirmT'])
        );
        if ($wReceiptConfirm != '') {
            $whereReceipt .= " AND " . $wReceiptConfirm;
        }

        //入金日
        $wReceipt = BaseGeneralUtils::makeWhereDate(
            'R1.ReceiptDate',
            BaseGeneralUtils::convertWideToNarrow($params['ReceiptF']),
            BaseGeneralUtils::convertWideToNarrow($params['ReceiptT'])
        );

        if ( $wReceipt != '' ) {
            $whereReceipt .= " AND " . $wReceipt;
        }

        // 入金額
        $wReceiptAmount = BaseGeneralUtils::makeWhereInt(
        'CC.ReceiptAmountTotal',
        BaseGeneralUtils::convertWideToNarrow($params['ReceiptAmountF']),
        BaseGeneralUtils::convertWideToNarrow($params['ReceiptAmountT'])
        );
        if ($wReceiptAmount != '') {
            $where .= " AND " . $wReceiptAmount;
        }

        // 入金方法
        $wRcpt = "0";
        if (isset ( $params ['ReceiptClassList'] )) {
            foreach ( $params ['ReceiptClassList'] as $receiptClass ) {
                // 0は空白を選択した場合なので除外する
                if (! preg_match ( "/^[0]{1}/", $receiptClass )) {
                    if ($wRcpt ) {
                        $wRcpt .= "," . $receiptClass;
                    } else {
                        $wRcpt = $receiptClass;
                    }
                }
            }
        }
        if ($wRcpt != '0') {
            $whereReceipt .= sprintf ( " AND R1.ReceiptClass IN (%s) ", $wRcpt );
        }

        // 入金状態
        if (isset($params['IsWaitForReceipt']) && !isset($params['IsWaitForReceipt2'])) {
            $where .= " AND ORD.DataStatus IN (51, 61) ";
        }
        else if (!isset($params['IsWaitForReceipt']) && isset($params['IsWaitForReceipt2'])) {
            $where .= " AND ORD.DataStatus NOT IN (51, 61) ";
        }
        else {
            ;// [入金待ちである][入金待ちでない]ともにオンorオフ時は条件指定不要
        }

        // 延滞状態
        if (isset($params['IsToLateFirst'])) {
            $where .= " AND ORD.DataStatus = 51 AND CC.F_LimitDate < CURDATE()";
        }

        if (isset($params['IsToLateLatest'])) {
            $where .= " AND ORD.DataStatus = 51 AND CC.LimitDate < CURDATE()";
        }

        // 一部入金
        if (isset($params['ichibunyukin'])) {
            $where .= " AND ORD.DataStatus = 61 ";
        }

        // 紙請求ストップ
        if (isset($params['IsStopLetterClaim'])) {
            $where .= " AND ORD.LetterClaimStopFlg = 1";
        }

        // 請求ストップ
        if (isset($params['IsStopMailClaim'])) {
            $where .= " AND ORD.MailClaimStopFlg = 1";
        }

        // 最終請求日
        $wLatestClaimDate = BaseGeneralUtils::makeWhereDate(
            'CC.ClaimDate',
            BaseGeneralUtils::convertWideToNarrow($params['LatestClaimDateF']),
            BaseGeneralUtils::convertWideToNarrow($params['LatestClaimDateT'])
        );
        if ($wLatestClaimDate != '') {
            $where .= " AND " . $wLatestClaimDate;
        }

        // 内容証明
        switch($params['NaiyoSyomei'])
        {
            case '1':
                $where .= " AND (SELECT COUNT(*) FROM T_ClaimHistory WHERE OrderSeq = ORD.OrderSeq AND PrintedFlg = 1 AND ClaimPattern = 5) > 0";
                break;
            case '2':
                $where .= " AND (SELECT COUNT(*) FROM T_ClaimHistory WHERE OrderSeq = ORD.OrderSeq AND PrintedFlg = 1 AND ClaimPattern = 5) = 0";
                break;
            default:
                break;
        }

        // キャンセル申請日
        if ($params['CancelDateF'] != '' || $params['CancelDateT'] != '') {
            $retval = BaseGeneralUtils::makeWhereDateTime("CNCL.CancelDate", $params['CancelDateF'], $params['CancelDateT']);
            if ($retval != '') {
                $where .= (" AND (CNCL.ValidFlg = 1 AND " . $retval . " ) ");
            }
        }

        // キャンセル日
        if ($params['CancelConfirmDateF'] != '' || $params['CancelConfirmDateT'] != '') {
            $retval = BaseGeneralUtils::makeWhereDateTime("CNCL.ApprovalDate", $params['CancelConfirmDateF'], $params['CancelConfirmDateT']);
            if ($retval != '') {
                $where .= (" AND (CNCL.ValidFlg = 1 AND CNCL.ApproveFlg = 1 AND " . $retval . " ) ");
            }
        }

        // キャンセル状態
        if ($params['IsNotCancel'] == '1') {
            $where .= " AND ORD.Cnl_Status = 0";
        }
        elseif($params['IsNotCancel'] == '2') {
            $where .= " AND (ORD.Cnl_Status = 1 OR ORD.Cnl_Status = 2)";
        }

        //キャンセル区分
        if ($params['classifyCancel'] == '1' && $params['IsNotCancel'] == '2') {
            $where .= " AND (ORD.Cnl_ReturnSaikenCancelFlg = 0 OR ORD.Cnl_ReturnSaikenCancelFlg IS NULL)";
        }
        elseif($params['classifyCancel'] == '2' && $params['IsNotCancel'] == '2') {
            $where .= " AND ORD.Cnl_ReturnSaikenCancelFlg = 1";
        }

        // キャンセル理由
        if ((int)$params['cancelreason'] > 0) {
            $where .= (" AND CNCL.CancelReasonCode = " . (int)$params['cancelreason']);
        }

        // 備考
        if ($params['Note'] != '') {
            $where .= " AND ORD.Incre_Note like " . $this->app->dbAdapter->getPlatform()->quoteValue("%" . $params['Note'] . "%");
        }

        // OEM先備考
        if ($params['Oem_Note'] != '') {
            $where .= " AND ORD.Oem_Note like " . $this->app->dbAdapter->getPlatform()->quoteValue("%" . $params['Oem_Note'] . "%");
        }

        // 任意番号
        if ($params['Ent_OrderId'] != '') {
            $where .= " AND ORD.Ent_OrderId like " . $this->app->dbAdapter->getPlatform()->quoteValue("%" . $params['Ent_OrderId']);
        }

        // 2009.05.14 masuyama 検索条件追加
        // 営業担当
        if ($params['Salesman'] != '') {
            $where .= " AND ENT.Salesman like " . $this->app->dbAdapter->getPlatform()->quoteValue("%" . $params['Salesman'] . "%");
        }

        // プラン
        if ($params['Plan'] != 0) {
            $where .= (" AND ENT.Plan = " . $params['Plan']);
        }

        // 締日パターン
        if ($params['fixPattern'] != 0) {
            $where .= (" AND MPC.FixPattern = " . $params['fixPattern']);
        }

        // 住民票
        switch($params['ResidentCard'])
        {
            case '1':                                   // 手
                $where .= " AND CUS.ResidentCard = 1";
                break;
            case '2':                                   // 申
                $where .= " AND CUS.ResidentCard = 2";
                break;
            case '3':                                   // ○
                $where .= " AND CUS.ResidentCard = 3";
                break;
            case '4':                                   // ×
                $where .= " AND CUS.ResidentCard = 4";
                break;
            default:
                break;
        }

        // 事業者任意欄
        if ($params['Ent_Note'] != '') {
            $where .= " AND ORD.Ent_Note like " . $this->app->dbAdapter->getPlatform()->quoteValue("%" . $params['Ent_Note'] . "%");
        }

        // 補償外案件
        if (isset($params['OutOfAmends']) && !isset($params['OutOfAmends0'])) {
            $where .= " AND ORD.OutOfAmends = 1";
        }
        else if (!isset($params['OutOfAmends']) && isset($params['OutOfAmends0'])) {
            $where .= " AND IFNULL(ORD.OutOfAmends,0) = 0";
        }
        else {
            ;// [補償外][通常]ともにオンorオフ時は条件指定不要
        }

        // 督促ストップ
        if (isset($params['RemindStopFlg'])) {
            $where .= " AND MC.RemindStopFlg = 1";
        }

        // 審査システム結果
        if ($params['Incre_Note'] != '') {
            $where .= " AND CJRD.DetectionPatternName like " . $this->app->dbAdapter->getPlatform()->quoteValue("%" . $params['Incre_Note'] . "%");
        }

        // 顧客ID（事業者別）
        if ($params['EntCustSeqList'] != '') {

            // 区切り文字をカンマに統一
            $entCustSeqList = preg_replace("/\s/", ",", $params['EntCustSeqList']);
            $entCustSeqList = BaseGeneralUtils::convertWideToNarrow(BaseUtility::escapeWildcard($entCustSeqList));

            //配列化
            $entCustSeqList = explode(',',$entCustSeqList);

            //不要なデータを除外
            foreach($entCustSeqList as $key => $val){
                if(empty($val)){
                    unset($entCustSeqList[$key]);
                }
            }

            //文字列化
            $entCustSeqList = implode(',', $entCustSeqList);

            //変換後にもう一度 未入力判定
            if(!empty($entCustSeqList)){
                // カンマと数値以外を含んでいたらエラー
                if(!preg_match('/^[0-9,]+$/', $entCustSeqList)) {
                    throw new SearchoControllerException('顧客ID（事業者別）に不正な文字が含まれています');
                }
                $where .= " AND EC.EntCustSeq IN (". ($entCustSeqList) . ") ";
            }
        }

        // 顧客ステータス
        if ((int)$params['custSts'] == 1) {
            $where .= " AND MC.BlackFlg = 1 ";
        }
        else if ((int)$params['custSts'] == 2) {
            $where .= " AND MC.GoodFlg = 1 ";
        }

        // 取りまとめ(取りまとめデータのみ、時条件追加)
        if($params['CombinedClaimTargetStatus'] == '2') {
            $where .= " AND (ORD.CombinedClaimTargetStatus IS NOT NULL AND ORD.CombinedClaimTargetStatus <> 0) ";
        }

        // 取りまとめ代表(代表のみ、時条件追加)
        if($params['CombinedClaimParentFlg'] == '2') {
            $where .= " AND ORD.CombinedClaimParentFlg = 1 ";
        }

        //マイページ最終ログイン日
        $wLastLoginDate = BaseGeneralUtils::makeWhereDateTime(
            'VMOL.LastLoginDate',
            BaseGeneralUtils::convertWideToNarrow($params['LastLoginDateF']),
            BaseGeneralUtils::convertWideToNarrow($params['LastLoginDateT'])
        );
        if ($wLastLoginDate != '') {
            $where .= " AND " . $wLastLoginDate;
        }

        // OrderSeqリスト指定
        // count関数対策
        if (isset($params['seqs']) && is_array($params['seqs']) && (!empty($params['seqs']))) {
            foreach ($params['seqs'] as &$seqs) {
                if (!is_numeric($seqs)) {
                    $seqs = '-1';
                }
            }
            $where .= " AND ORD.OrderSeq IN ( " . MakeQueryValStrPhraseIn($params['seqs']) . ") ";
        }

        // 入金情報は1注文に複数あるので、該当するデータが含まれる注文が対象
        if (strlen($whereReceipt) > 0) {
            $oseqsql = " SELECT GROUP_CONCAT(OrderSeq) AS OrderSeqList FROM T_ReceiptControl R1 WHERE 1 = 1 " . $whereReceipt . " AND R1.ReceiptSeq > (SELECT IFNULL(MAX(R2.ReceiptSeq), 0) FROM T_ReceiptControl R2, AT_ReceiptControl AR2 WHERE R2.ReceiptSeq = AR2.ReceiptSeq AND R1.OrderSeq = R2.OrderSeq AND AR2.Rct_CancelFlg = 1) ";
            $oseq = $this->app->dbAdapter->query($oseqsql)->execute(null)->current()['OrderSeqList'];
            if (isset($oseq))
            {
                $oseq = "-1," . $oseq;
            }
            else
            {
                $oseq = "-1";
            }

            //$oseq = "-1," . $oseq;
            // レスポンス対策。EXISTSだと相関副問い合わせになるので、IN句にして回避
            $where .= " AND ORD.P_OrderSeq IN ($oseq) ";
        }

        // 取りまとめ親子
        if(isset($params['p_orderseq'])) {
            $where .= " AND ORD.P_OrderSeq = " .$params['p_orderseq'];
        }

        // 口座振替サービス
        if(isset($params['CreditTransferFlg']) && ($params['CreditTransferFlg'] > 0)) {
            $where .= " AND CC.CreditTransferFlg = " .$params['CreditTransferFlg'];
        }

        // 口座振替利用
        if(isset($params['CreditTransferRequestFlg'])) {
            switch ($params['CreditTransferRequestFlg']) {
                case 0:
                case 1:
                case 2:
                    $where .= " AND AORD.CreditTransferRequestFlg = " .$params['CreditTransferRequestFlg'];
                    break;
                case 99:
                    $where .= " AND AORD.CreditTransferRequestFlg IN (1,2) ";
                    break;
            }
        }

        // 申込ステータス
        if(isset($params['RequestStatus']) && ($params['RequestStatus'] >= 0)) {
            $where .= " AND ENT.CreditTransferFlg <> 0 ";
            switch ($params['RequestStatus']) {
                case 0:
                    $where .= " AND (EC.RequestStatus = 0 OR EC.RequestStatus IS NULL) ";
                    break;
                default:
                    $where .= " AND EC.RequestStatus = ".$params['RequestStatus'];
                    break;
            }
        }

        // 申込サブステータス
        if(isset($params['RequestSubStatus']) && ($params['RequestSubStatus'] >= 0)) {
            $where .= " AND ENT.CreditTransferFlg <> 0 ";
            switch ($params['RequestSubStatus']) {
                case 0:
                    $where .= " AND (EC.RequestSubStatus = 0 OR EC.RequestSubStatus IS NULL) ";
                    break;
                default:
                    $where .= " AND EC.RequestSubStatus = ".$params['RequestSubStatus'];
                    break;
            }
        }

        // 申込完了日
        if ($params['RequestCompDateF'] != '' || $params['RequestCompDateT'] != '') {
            $retval = BaseGeneralUtils::makeWhereDateTime("EC.RequestCompDate", $params['RequestCompDateF'], $params['RequestCompDateT']);
            if ($retval != '') {
                $where .= " AND (ENT.CreditTransferFlg <> 0 AND " . $retval . " ) ";
            }
        }

        // 口座振替
        if(isset($params['CreditTransferMethod1']) && ($params['CreditTransferMethod1'] > 0)) {
            switch ($params['CreditTransferMethod1']) {
                case 1:
                    $where .= " AND CH.CreditTransferMethod = 3 ";
                    break;
                default:
                    $where .= " AND CH.CreditTransferMethod <> 3 ";
                    break;
            }
        }

        // 初回申込用紙発行
        if(isset($params['CreditTransferMethod2']) && ($params['CreditTransferMethod2'] > 0)) {
            switch ($params['CreditTransferMethod2']) {
                case 1:
                    $where .= " AND CH.CreditTransferMethod = 1 ";
                    break;
                default:
                    $where .= " AND CH.CreditTransferMethod <> 1 ";
                    break;
            }
        }

        // ここまで条件がなかったら例外
        if(!strlen($where)) {
            throw new SearchoControllerException('検索条件が入力されていません');
        }
        $where .= $search_where;

        //コメント追加
        $where .= ' -- order_search_mark ';

        // クエリー生成
        $query = sprintf($query, $relatePayingControl, $where);

        //---------- 検索結果サマリ取得(ここから) ----------
        $sql  = " SELECT COUNT(1) as TotalRecCnt ";
        $sql .= " ,      SUM(UseAmount) AS TotalUseAmount ";
        $sql .= " ,      SUM(CalcReceiptAmount) AS CalcReceiptAmount ";
        $sql .= " ,      SUM(CASE WHEN (IFNULL(tmp.Cnl_Status, 0) != 0) OR (tmp.DataStatus = 91 AND tmp.CloseReason = 4) THEN 0 ";
        $sql .= "                 WHEN IFNULL((SELECT ClaimAmount FROM T_ClaimControl WHERE OrderSeq = tmp.OrderSeq), 0) <= 0 THEN 0 ";
        $sql .= "                 ELSE IFNULL((SELECT UseAmountTotal FROM T_ClaimControl WHERE OrderSeq = tmp.OrderSeq), 0) - tmp.CalcReceiptAmount ";
        $sql .= "            END ";
        $sql .= "           ) AS CalcNoReceiptAmount ";
        $sql .= " ,      SUM(CASE WHEN (IFNULL(tmp.Cnl_Status, 0) != 0) OR (tmp.DataStatus = 91 AND tmp.CloseReason = 4) THEN 0 ";
        $sql .= "                 ELSE IFNULL((SELECT ClaimedBalance FROM T_ClaimControl WHERE OrderSeq = tmp.OrderSeq), 0) END ) AS NowTotalAmount ";
        $sql .= " FROM ( ";
        $sql .= sprintf($queryCnt, $relatePayingControl, $where);
        $sql .= " ) tmp ";

        $this->_search_summary = $this->app->dbAdapter->query($sql)->execute(null)->current();
        //---------- 検索結果サマリ取得(ここまで) ----------

        // 取得データ行数制限
        if (isset($params['limit_offset'])) {
            $query .= $params['limit_offset'];
        }

        return ResultInterfaceToArray($this->app->dbAdapter->query($query)->execute(null));
	}

	/**
	 * 注文検索クエリの実行結果に与信担当者などの不足情報を補間する
	 *
	 * @access private
	 * @param array $datas 検索実行結果の配列
	 * @return array
	 */
	private function _fillSearchResult($datas)
	{
        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
        $mdlop = new TableOperator($this->app->dbAdapter);
        $op_cache = array();    // TableOperator::findOperatorの実行結果キャッシュ

        // count関数対策
        $datasLen = 0;
        if(!empty($datas)) {
            $datasLen = count($datas);
        }

        for ($i = 0 ; $i < $datasLen ; $i++)
        {
            $datas[$i]['Incre_ArAddr'] = $codeMaster->getCreditClassShortCaption($datas[$i]['Incre_ArAddr']);
            $datas[$i]['Incre_ArTel'] = $codeMaster->getCreditClassShortCaption($datas[$i]['Incre_ArTel']);
            $datas[$i]['eDen'] = $codeMaster->getEDenCaption($datas[$i]['eDen']);
            $datas[$i]['PhoneHistory'] = $codeMaster->getPhoneHistoryCaption($datas[$i]['PhoneHistory']);

            // 与信担当者名の解決。担当IDが空の場合は放置
            if( ! empty( $datas[$i]['Incre_DecisionOpId'])) {
                // DB問い合わせはキャッシュされていない場合のみ
                if( ! isset( $op_cache[ $datas[$i]['Incre_DecisionOpId'] ] ) ) {
                    $op_cache[ $datas[$i]['Incre_DecisionOpId'] ] = $mdlop->findOperator($datas[$i]['Incre_DecisionOpId'])->current()['NameKj'];
                }
                $datas[$i]['Incre_DecisionOpId'] = $op_cache[ $datas[$i]['Incre_DecisionOpId']];
            }

            switch($datas[$i]['RealSendMailResult'])
            {
                case 1:
                    $datas[$i]['RealSendMailResult'] = 'OK';
                    break;
                case 2:
                    $datas[$i]['RealSendMailResult'] = 'NG';
                    break;
                default:
                    $datas[$i]['RealSendMailResult'] = '';
                    break;
            }

            if ($datas[$i]['Bekkan'] == '' || $datas[$i]['Bekkan'] == '0') {
                $datas[$i]['Bekkan'] = '通常';
            }
            else {
                $datas[$i]['Bekkan'] = '別管';
            }
        }

        return $datas;
	}

	/**
	 * 簡易検索実行ロジック
	 *
	 * @param array $params
	 * @return array
	 */
	public function getQuickSearchResult($params)
	{
$stt = microtime(true);
        // 指定条件に一致するOrderSeqのみを抽出する予備クエリを組み立てる。
        // 生成されるSQLは
        // SELECT o.OrderSeq FROM T_Order o INNER JOIN T_Customer c ON c.OrderSeq = o.OrderSeq
        // がベースとなる
        $pre_select  = "SELECT o.OrderSeq ";
        $pre_select .= "FROM   T_Order o ";
        $pre_select .= "       INNER JOIN T_Customer c ON c.OrderSeq = o.OrderSeq ";
        $pre_select .= "       INNER JOIN T_OrderSummary os ON os.OrderSeq = o.OrderSeq ";
        $pre_select .= "       LEFT OUTER JOIN T_ClaimControl cc ON cc.OrderSeq = o.P_OrderSeq ";
        $pre_select .= "WHERE 1 = 1 ";

        // 条件部分の組み立てを実行
        $normalizers = array(
                'OemId' => LogicNormalizer::create(LogicNormalizer::FILTER_FOR_ID),
                'order_id' => LogicNormalizer::create(LogicNormalizer::FILTER_FOR_ID),
                'name' => LogicNormalizer::create(LogicNormalizer::FILTER_FOR_NAME),
                'phone' => LogicNormalizer::create(LogicNormalizer::FILTER_FOR_TEL),
                'address' => LogicNormalizer::create(LogicNormalizer::FILTER_FOR_ADDRESS),
                'deliphone' => LogicNormalizer::create(LogicNormalizer::FILTER_FOR_TEL),
                'deliaddress' => LogicNormalizer::create(LogicNormalizer::FILTER_FOR_ADDRESS),
                'mail' => LogicNormalizer::create(LogicNormalizer::FILTER_FOR_MAIL),
                'postalcode' => LogicNormalizer::create(LogicNormalizer::FILTER_FOR_POSTALCODE),
                'phonelink' => LogicNormalizer::create(LogicNormalizer::FILTER_FOR_TEL)
        );

        $has_expression = false;
        foreach($params as $key => $value)
        {
            $value = nvl($value);
            // 入力があって、正規化可能なパラメータのみ処理する
            if(!strlen($value) || !isset($normalizers[$key])) continue;

            // 正規化実施
            $value = $normalizers[$key]->normalize($value);
            switch($key)
            {
                case 'OemId':
                    // OEMIDの後方一致条件
                    $pre_select .= (" AND o.OemId like '%" . $value . "' ");
                    $has_expression = true;
                    break;
                case 'order_id':
// ↓↓↓純粋な後方一致検索に変更→レスポンステストで問題があれば対策を考慮する(20150822_1516_suzuki_h)
//                     // 注文IDの後方一致条件
//                     // ※：SQL自体はINによる完全一致だが入力を後方桁と見なして不足補完を行う
//                     $ids = $this->_fixOrderIdForQuickSearch($value);

//                     // ID生成0件は条件未指定扱い
//                     if(!count($ids)) continue;
//                     $pre_select .= " AND o.OrderId IN (" . MakeQueryValStrPhraseInWithCoat($ids) . ") ";
// ↑↑↑純粋な後方一致検索に変更→レスポンステストで問題があれば対策を考慮する(20150822_1516_suzuki_h)
                    $orderId = mb_convert_kana($value, 'a', 'UTF-8');
                    $pre_select .= sprintf(" AND o.ReverseOrderId LIKE '%s%%' ", BaseUtility::escapeWildcard(strrev($orderId)));// 反転した注文ID、を検索(インデックス検索)
                    $has_expression = true;
                    break;
                case 'name':
                    // 請求先氏名の前方一致条件
                    $pre_select .= sprintf(" AND c.RegNameKj LIKE '%s%%'", BaseUtility::escapeWildcard($value));
                    $has_expression = true;
                    break;
                case 'phone':
                    // 請求先電話番号の前方一致条件
                    $pre_select .= sprintf(" AND c.RegPhone LIKE '%s%%'", BaseUtility::escapeWildcard($value));
                    $has_expression = true;
                    break;
                case 'address':
                    // 請求先住所の前方一致条件
                    $pre_select .= sprintf(" AND c.RegUnitingAddress LIKE '%s%%'", BaseUtility::escapeWildcard($value));
                    $has_expression = true;
                    break;
                case 'mail':
                    // メールアドレスの前方一致条件
                    $pre_select .= sprintf(" AND c.MailAddress LIKE '%s%%'", BaseUtility::escapeWildcard($value));
                    $has_expression = true;
                    break;
                case 'deliphone':
                    // 配送先電話番号の前方一致条件
                    $pre_select .= sprintf(" AND os.RegDestPhone LIKE '%s%%'", BaseUtility::escapeWildcard($value));
                    $has_expression = true;
                    break;
                case 'deliaddress':
                    // 配送先住所の前方一致条件
                    $pre_select .= sprintf(" AND os.RegDestUnitingAddress LIKE '%s%%'", BaseUtility::escapeWildcard($value));
                    $has_expression = true;
                    break;
                case 'phonelink':
                    // 注文詳細 電話番号リンク条件
                    $pre_select .= sprintf(" AND (os.RegPhone = '%s'", BaseUtility::escapeWildcard($value));
                    $pre_select .= sprintf(" OR os.RegDestPhone = '%s')", BaseUtility::escapeWildcard($value));
                    $has_expression = true;
                    break;
            }
        }

        // 請求金額の範囲指定検索
        if(isset($params['claim_amount_from']) && strlen(trim($params['claim_amount_from']))) {
            $ca_from = (int)$params['claim_amount_from'];
        } else {
            $ca_from = false;
        }
        if(isset($params['claim_amount_to']) && strlen(trim($params['claim_amount_to']))) {
            $ca_to = (int)$params['claim_amount_to'];
        } else {
            $ca_to = false;
        }

        // 郵便番号検索
        if(isset($params['registdate'])) {
            if(!(is_numeric($params['registdate']))) {
                $params['registdate'] = 30;
            }
            $pre_select .= " AND o.RegistDate > DATE_ADD(CURRENT_DATE, INTERVAL -" .$params['registdate'] . " DAY)";
        }

        if(isset($params['postalcode'])) {
            $pre_select .= " AND (os.PostalCode = '" . BaseGeneralUtils::convertNumberWideToNarrow(BaseUtility::escapeWildcard($params['postalcode'])) . "' OR os.DestPostalCode = '" . BaseGeneralUtils::convertNumberWideToNarrow(BaseUtility::escapeWildcard($params['postalcode'])) ."')";
            $has_expression = true;
        }

        if($ca_from !== false || $ca_to !== false) {
            $pre_select .= " AND o.DataStatus IN (51, 61) ";
            $pre_select .= " AND o.Cnl_Status = 0 ";

            $tmpl1 = 'cc.ClaimAmount';
            $tmpl2 = 'cc.ClaimedBalance';

            if($ca_from === false) {
                $pre_select .= sprintf(" AND ((%s < %d) OR (%s < %d)) ", $tmpl1, $ca_to, $tmpl2, $ca_to);
            }
            else if($ca_to === false) {
                $pre_select .= sprintf(" AND ((%s > %d) OR (%s > %d)) ", $tmpl1, $ca_from, $tmpl2, $ca_from);
            }
            else {
                 $pre_select .= sprintf(" AND ((%s BETWEEN %d AND %d) OR (%s BETWEEN %d AND %d)) ", $tmpl1, $ca_from, $ca_to, $tmpl2, $ca_from, $ca_to);
            }
            $has_expression = true;
        }

        // 条件が1つも入力されていなかったらFALSEを返して終了
        if(!$has_expression) return false;

        // 本検索
        $query = $this->_getQuickOrSpecialSearchBaseQuery();
        $where = " ORD.OrderSeq IN (" . $pre_select . " ) ";
        $query = sprintf($query, $where);

        //---------- 検索結果サマリ取得(ここから) ----------
        $sql  = " SELECT COUNT(1) as TotalRecCnt ";
        $sql .= " ,      SUM(UseAmount) AS TotalUseAmount ";
        $sql .= " ,      SUM(CalcReceiptAmount) AS CalcReceiptAmount ";
        $sql .= " ,      SUM(CASE WHEN (IFNULL(tmp.Cnl_Status, 0) != 0) OR (tmp.DataStatus = 91 AND tmp.CloseReason = 4) THEN 0 ";
        $sql .= "                 WHEN IFNULL((SELECT ClaimAmount FROM T_ClaimControl WHERE OrderSeq = tmp.OrderSeq), 0) <= 0 THEN 0 ";
        $sql .= "                 ELSE IFNULL((SELECT UseAmountTotal FROM T_ClaimControl WHERE OrderSeq = tmp.OrderSeq), 0) - tmp.CalcReceiptAmount ";
        $sql .= "            END ";
        $sql .= "           ) AS CalcNoReceiptAmount ";
        $sql .= " ,      SUM(CASE WHEN (IFNULL(tmp.Cnl_Status, 0) != 0) OR (tmp.DataStatus = 91 AND tmp.CloseReason = 4) THEN 0 ";
        $sql .= "                 ELSE IFNULL((SELECT ClaimedBalance FROM T_ClaimControl WHERE OrderSeq = tmp.OrderSeq), 0) END ) AS NowTotalAmount ";
        $sql .= " FROM ( ";
        $sql .= sprintf($this->_getQuickOrSpecialSearchBaseQueryForCount(), $where);
        $sql .= " ) tmp ";

        $this->_search_summary = $this->app->dbAdapter->query($sql)->execute(null)->current();
        //---------- 検索結果サマリ取得(ここまで) ----------

        // 取得データ行数制限
        if (isset($params['limit_offset'])) {
            $query .= $params['limit_offset'];
        }
        return ResultInterfaceToArray($this->app->dbAdapter->query($query)->execute(null));
	}

	/**
	 * 定型検索実行ロジック
	 *
	 * @param array $params
	 * @return array
	 */
	public function getSpecialSearchResult($params)
	{
        $start_time = microtime(true);

        $where = '';
        switch($params['type'])
        {
            case self::SSEARCH_TYPE_OK_CREDITS:
                // 与信OK検索
                // ・注文登録日が指定月の月初～月末
                // ・与信OK（＝社内与信OK）
                // ・キャンセルされていない
                $where = <<<EOW1
    ORD.RegistDate BETWEEN
        (DATE_FORMAT(:date, '%Y-%m-1') + INTERVAL 0 SECOND) AND
        (DATE_FORMAT(:date, '%Y-%m-1') + INTERVAL 1 MONTH - INTERVAL 1 SECOND) AND
    (ORD.Dmi_Status = 1 OR (ORD.Dmi_Status IS NULL AND ORD.Incre_Status = 1)) AND
    ORD.Cnl_Status = 0
EOW1;
                break;

            case self::SSEARCH_TYPE_NG_CREDITS:
                // 与信NG検索
                // ・注文登録日が指定月の月初～月末
                // ・与信NG（＝社内与信NG）
                $where = <<<EOW2
    ORD.RegistDate BETWEEN
        (DATE_FORMAT(:date, '%Y-%m-1') + INTERVAL 0 SECOND) AND
        (DATE_FORMAT(:date, '%Y-%m-1') + INTERVAL 1 MONTH - INTERVAL 1 SECOND) AND
    (ORD.Dmi_Status = -1 OR ORD.Incre_Status = - 1)
EOW2;
                break;

            case self::SSEARCH_TYPE_03:
                // 03.初回支払期限経過検索
                $where = <<<EOW3
    CC.F_LimitDate BETWEEN
        (DATE_FORMAT(:date, '%Y-%m-1') + INTERVAL 0 SECOND) AND
        (DATE_FORMAT(:date, '%Y-%m-1') + INTERVAL 1 MONTH - INTERVAL 1 SECOND) AND
    ORD.DataStatus IN (51, 61) AND
    ORD.Cnl_Status = 0
EOW3;
                break;

            case self::SSEARCH_TYPE_04:
                // 04.初回支払期限経過補償対象外検索
                $where = <<<EOW4
    CC.F_LimitDate BETWEEN
        (DATE_FORMAT(:date, '%Y-%m-1') + INTERVAL 0 SECOND) AND
        (DATE_FORMAT(:date, '%Y-%m-1') + INTERVAL 1 MONTH - INTERVAL 1 SECOND) AND
    ORD.OutOfAmends = 1 AND
    ORD.DataStatus IN (51, 61) AND
    ORD.Cnl_Status = 0
EOW4;
                break;

            case self::SSEARCH_TYPE_05:
                // 05.備考が自動与信の検索
                $where = <<<EOW5
    ORD.RegistDate BETWEEN
        (DATE_FORMAT(:date, '%Y-%m-1') + INTERVAL 0 SECOND) AND
        (DATE_FORMAT(:date, '%Y-%m-1') + INTERVAL 1 MONTH - INTERVAL 1 SECOND) AND
    ORD.Incre_Note like '%与信自動ＯＫ%' AND
    ORD.Cnl_Status = 0
EOW5;
                break;

            case self::SSEARCH_TYPE_06:
                // 06.サービサー委託準備の検索
                $where = <<<EOW6
    ORD.RegistDate BETWEEN
        (DATE_FORMAT(:date, '%Y-%m-1') + INTERVAL 0 SECOND) AND
        (DATE_FORMAT(:date, '%Y-%m-1') + INTERVAL 1 MONTH - INTERVAL 1 SECOND) AND
    ORD.Cnl_Status = 0 AND
    ORD.DataStatus IN (51, 61)
EOW6;
                break;
        }

        // 指定日付のチェック
        if(f_df($params['month'], 'Y-m-d') != $params['month']) $where = '';

        // 適合する検索方法がないか日付指定がない場合は
        // FALSEを返して終了
        if(!strlen($where)) return false;

        // 加盟店指定時
        if (isset($params['entListTag']) && ($params['entListTag'] > 0)) {
            $where .= (" AND ENT.EnterpriseId = " . $params['entListTag']);
        }

        $query = sprintf($this->_getQuickOrSpecialSearchBaseQuery(), $where);

        //---------- 検索結果サマリ取得(ここから) ----------
        $sql  = " SELECT COUNT(1) as TotalRecCnt ";
        $sql .= " ,      SUM(UseAmount) AS TotalUseAmount ";
        $sql .= " ,      SUM(CalcReceiptAmount) AS CalcReceiptAmount ";
        $sql .= " ,      SUM(CASE WHEN (IFNULL(tmp.Cnl_Status, 0) != 0) OR (tmp.DataStatus = 91 AND tmp.CloseReason = 4) THEN 0 ";
        $sql .= "                 WHEN IFNULL((SELECT ClaimAmount FROM T_ClaimControl WHERE OrderSeq = tmp.OrderSeq), 0) <= 0 THEN 0 ";
        $sql .= "                 ELSE IFNULL((SELECT UseAmountTotal FROM T_ClaimControl WHERE OrderSeq = tmp.OrderSeq), 0) - tmp.CalcReceiptAmount ";
        $sql .= "            END ";
        $sql .= "           ) AS CalcNoReceiptAmount ";
        $sql .= " ,      SUM(CASE WHEN (IFNULL(tmp.Cnl_Status, 0) != 0) OR (tmp.DataStatus = 91 AND tmp.CloseReason = 4) THEN 0 ";
        $sql .= "                 ELSE IFNULL((SELECT ClaimedBalance FROM T_ClaimControl WHERE OrderSeq = tmp.OrderSeq), 0) END ) AS NowTotalAmount ";
        $sql .= " FROM ( ";
        $sql .= sprintf($this->_getQuickOrSpecialSearchBaseQueryForCount(), $where);
        $sql .= " ) tmp ";

        $this->_search_summary = $this->app->dbAdapter->query($sql)->execute(array('date' => $params['month']))->current();
        //---------- 検索結果サマリ取得(ここまで) ----------

        // 取得データ行数制限
        if (isset($params['limit_offset'])) {
            $query .= $params['limit_offset'];
        }

        return ResultInterfaceToArray($this->app->dbAdapter->query($query)->execute(array('date' => $params['month'])));
	}

	/**
	 * 簡易検索・定型検索向けのSQLの共通パートを取得する
	 *
	 * @access private
	 * @return string
	 */
	private function _getQuickOrSpecialSearchBaseQuery()
	{
        // 過剰入金色分けしきい値
        $excessPaymentColorThreshold = $this->app->dbAdapter->query(
            " SELECT PropValue FROM T_SystemProperty WHERE Module = '[DEFAULT]' AND Category = 'systeminfo' AND Name = 'ExcessPaymentColorThreshold' ")->execute(null)->current()['PropValue'];
	    return <<<EOQ
SELECT
    ORD.OrderSeq,
    CASE WHEN ORD.CombinedClaimTargetStatus IN (91, 92)
        THEN (CASE ORD.P_OrderSeq WHEN ORD.OrderSeq THEN '親' ELSE '子' END)
        ELSE ''
    END AS CombinedClaim,
    (SELECT COUNT(1) FROM T_Order WHERE P_OrderSeq = ORD.P_OrderSeq AND OrderSeq <> P_OrderSeq) AS CombinedCnt,
    ORD.OrderId,
    ORD.Ent_OrderId,
    ORD.Oem_OrderId,
    ENT.EnterpriseNameKj,
    SITE.SiteNameKj,
    ORD.ReceiptOrderDate,
    SUM.NameKj,
    SUM.NameKn,
    SUM.PostalCode,
    SUM.UnitingAddress,
    SUM.Phone,
    SUM.MailAddress,
    SUM.Deli_JournalIncDate,
    DELI.DeliMethodName,
    SUM.Deli_DeliveryMethodName,
    SUM.Deli_JournalNumber,
    CC.F_ClaimDate,
    CC.F_LimitDate,
    CC.F_LimitDate AS Clm_F_LimitDate,
    ORD.UseAmount,
    RC.ReceiptDate,
    CC.ReceiptAmountTotal AS ReceiptAmount,
    RC.ReceiptClass,
	CASE ORD.Rct_Status
       WHEN '0' THEN '未入金'
       WHEN '1' THEN (SELECT KeyContent FROM M_Code WHERE CodeId = 198 AND KeyCode = RC.ReceiptClass)
       ELSE ''
    END AS ReceiptMethodLabel,
    ORD.Incre_Note,
    ORD.Oem_Note,
    ORD.MailLimitPassageDate,
    (SELECT KeyContent FROM M_Code WHERE CodeId = 13 AND KeyCode = CUS.eDen) AS eDen,
    (SELECT KeyContent FROM M_Code WHERE CodeId = 14 AND KeyCode = CUS.PhoneHistory) AS PhoneHistory,
    CASE WHEN IFNULL(ORD.Incre_DecisionOpId, 0) = 0 THEN ORD.Incre_DecisionOpId
         ELSE (SELECT NameKj FROM T_Operator WHERE OpId = ORD.Incre_DecisionOpId)
    END AS Incre_DecisionOpId,
    (SELECT Class2 FROM M_Code WHERE CodeId = 4 AND KeyCode = CUS.Incre_ArAddr) AS Incre_ArAddr,
    (SELECT Class2 FROM M_Code WHERE CodeId = 4 AND KeyCode = CUS.Incre_ArTel) AS Incre_ArTel,
    CASE CUS.RealSendMailResult WHEN 1 THEN 'OK' WHEN 2 THEN 'NG' ELSE '' END AS RealSendMailResult,
    ORD.DataStatus,
    ORD.CloseReason,
    ORD.Rct_Status,
    ENT.Salesman,
    ORD.Cnl_Status,
    ORD.Cnl_ReturnSaikenCancelFlg,
    1 AS HasDeliInfo,
    SUM.DestNameKj,
    SUM.DestNameKn,
    SUM.DestPhone,
    SUM.DestPostalCode,
    SUM.DestUnitingAddress,
    ITM.Deli_ConfirmArrivalDate,
    OEM.OemNameKj,
	CC.ReceiptAmountTotal AS InstallmentPlanAmount,
	CUS.CustomerId,
	ORD.P_OrderSeq,
    VCR.ReceiptDate AS CloseReceiptDate,
    ORD.RegistDate,
    ORD.Dmi_Status,
    ORD.Incre_Status,
    ORD.PromPayDate,
    (SELECT KeyContent FROM M_Code WHERE CodeId = 18 And KeyCode = ORD.RemindClass) AS RemindContent,
    ORD.VisitFlg,
    ORD.CombinedClaimTargetStatus,
    CC.ClaimedBalance AS Rct_DifferentialAmount,
    $excessPaymentColorThreshold AS ExcessPaymentColorThreshold,
    CASE WHEN AORD.AutoJudgeNgReasonCode IS NOT NULL THEN (SELECT Note FROM M_Code WHERE CodeId = 191 AND KeyCode = AORD.AutoJudgeNgReasonCode)
         ELSE NULL
    END AS AutoNgReason,
    CASE WHEN AORD.ManualJudgeNgReasonCode IS NOT NULL THEN (SELECT Note FROM M_Code WHERE CodeId = 190 AND KeyCode = AORD.ManualJudgeNgReasonCode)
         ELSE NULL
    END AS ManualNgReason,
    AORD.NgNoGuaranteeChangeDate AS NgNoGuaranteeChangeDate,
    AORD.ExtraPayType AS ExtraPayType,
    CUS.EntCustSeq AS EntCustSeq,
    AORD.ExtraPayKey AS ExtraPayKey,
    CUS.CorporateName AS CorporateName,
    CASE WHEN CC.CreditTransferFlg = 0 THEN ''
        WHEN CC.CreditTransferFlg = 1 THEN '利用する（SMBC）'
        WHEN CC.CreditTransferFlg = 2 THEN '利用する（MUFJ）'
        WHEN CC.CreditTransferFlg = 3 THEN '利用する（みずほ）'
        ELSE ''
    END AS CreditTransferFlg,
    CASE WHEN AORD.CreditTransferRequestFlg = 0 THEN ''
         WHEN AORD.CreditTransferRequestFlg = 1 THEN '利用する（WEB）'
         WHEN AORD.CreditTransferRequestFlg = 2 THEN '利用する（紙）'
         ELSE ''
    END AS CreditTransferRequestFlg,
    CASE EC.RequestStatus
       WHEN NULL THEN ''
       WHEN '0' THEN ''
       ELSE (SELECT KeyContent FROM M_Code WHERE CodeId = 196 AND KeyCode = EC.RequestStatus)
    END AS RequestStatus,
    CASE EC.RequestSubStatus
       WHEN NULL THEN ''
       WHEN '0' THEN ''
       ELSE (SELECT KeyContent FROM M_Code WHERE CodeId = 210 AND KeyCode = EC.RequestSubStatus)
    END AS RequestSubStatus,
    EC.RequestCompDate AS RequestCompDate,
    CASE CH.CreditTransferMethod
       WHEN '3' THEN '口座振替'
       ELSE ''
    END AS CreditTransferMethod1,
    CASE CH.CreditTransferMethod
       WHEN '1' THEN '初回申込用紙発行'
       ELSE ''
    END AS CreditTransferMethod2,
    CUS.EntCustId,
    ORD.Ent_Note
FROM
    T_Order ORD
        STRAIGHT_JOIN
    T_OrderSummary SUM ON SUM.OrderSeq = ORD.OrderSeq
        STRAIGHT_JOIN
    T_Customer CUS ON CUS.OrderSeq = ORD.OrderSeq
        STRAIGHT_JOIN
    T_Enterprise ENT ON ENT.EnterpriseId = ORD.EnterpriseId
        STRAIGHT_JOIN
    T_Site SITE ON SITE.SiteId = ORD.SiteId
        LEFT OUTER JOIN
    M_DeliveryMethod DELI ON DELI.DeliMethodId = SUM.Deli_DeliveryMethod
        INNER JOIN
    T_OrderItems ITM ON ITM.OrderItemId = SUM.OrderItemId
        LEFT OUTER JOIN
    T_Oem OEM ON OEM.OemId = ORD.OemId
        LEFT OUTER JOIN
    T_ClaimControl CC ON CC.OrderSeq = ORD.P_OrderSeq
        LEFT OUTER JOIN
    T_ReceiptControl RC ON RC.ReceiptSeq = CC.LastReceiptSeq
        LEFT OUTER JOIN
    V_CloseReceiptControl VCR ON VCR.OrderSeq = CC.OrderSeq
        INNER JOIN
    AT_Order AORD ON (ORD.OrderSeq = AORD.OrderSeq)
        LEFT OUTER JOIN
    T_EnterpriseCustomer EC ON (EC.EntCustSeq = CUS.EntCustSeq)
        LEFT OUTER JOIN
    T_ClaimHistory CH ON (
        ORD.OrderSeq = CH.OrderSeq AND
            CH.Seq = (
                SELECT MAX(Seq)
                FROM T_ClaimHistory CH2
                WHERE CH2.OrderSeq = ORD.OrderSeq
                AND CH2.ClaimPattern = 1
                AND CH2.ValidFlg = 1
            )
        )
WHERE
    %s
EOQ;
	}

	/**
	 * 簡易検索・定型検索向けのSQLの共通パートを取得する(集計専用)
	 *
	 * @access private
	 * @return string
	 */
	private function _getQuickOrSpecialSearchBaseQueryForCount()
	{
	    return <<<EOQ
SELECT
    ORD.OrderSeq,
    ORD.UseAmount,
    ORD.Cnl_Status,
    ORD.DataStatus,
    ORD.CloseReason,
    CASE WHEN (IFNULL(ORD.Cnl_Status, 0) != 0) OR (ORD.DataStatus = 91 AND ORD.CloseReason = 4) THEN 0 -- ｷｬﾝｾﾙ OR 貸し倒れは除外
         WHEN IFNULL((SELECT ReceiptAmountTotal FROM T_ClaimControl WHERE OrderSeq = ORD.OrderSeq), 0)
	       >= IFNULL((SELECT UseAmountTotal FROM T_ClaimControl WHERE OrderSeq = ORD.OrderSeq), 0) THEN IFNULL((SELECT UseAmountTotal FROM T_ClaimControl WHERE OrderSeq = ORD.OrderSeq), 0)
         ELSE IFNULL((SELECT ReceiptAmountTotal FROM T_ClaimControl WHERE OrderSeq = ORD.OrderSeq), 0)
    END AS CalcReceiptAmount
FROM
    T_Order ORD
        STRAIGHT_JOIN
    T_OrderSummary SUM ON SUM.OrderSeq = ORD.OrderSeq
        STRAIGHT_JOIN
    T_Customer CUS ON CUS.OrderSeq = ORD.OrderSeq
        STRAIGHT_JOIN
    T_Enterprise ENT ON ENT.EnterpriseId = ORD.EnterpriseId
        STRAIGHT_JOIN
    T_Site SITE ON SITE.SiteId = ORD.SiteId
        LEFT OUTER JOIN
    M_DeliveryMethod DELI ON DELI.DeliMethodId = SUM.Deli_DeliveryMethod
        INNER JOIN
    T_OrderItems ITM ON ITM.OrderItemId = SUM.OrderItemId
        LEFT OUTER JOIN
    T_Oem OEM ON OEM.OemId = ORD.OemId
        LEFT OUTER JOIN
    T_ClaimControl CC ON CC.OrderSeq = ORD.P_OrderSeq
        LEFT OUTER JOIN
    T_ReceiptControl RC ON RC.ReceiptSeq = CC.LastReceiptSeq
        INNER JOIN
    AT_Order AORD ON (ORD.OrderSeq = AORD.OrderSeq)
WHERE
    %s
EOQ;
	}

// Del By Takemasa(NDC) 20150917 Stt 機能廃止故コメントアウト化
// 	/**
// 	 * 簡易検索で入力された注文IDの後方パートの不足分を
// 	 * DBから取得した情報で補って検索用注文IDの配列を生成する。
// 	 * 入力されたIDの数字部分が8桁以上ある場合は後方8桁分から1件のみ生成する。
// 	 * 入力桁数が3桁未満の場合は、ID指定がなかったものと見なして空の配列を返す。
// 	 *
// 	 * @access private
// 	 * @param string $id 注文ID。呼出し前に正規化されている必要がある
// 	 * @return array
// 	 */
// 	private function _fixOrderIdForQuickSearch($id)
// Del By Takemasa(NDC) 20150917 End 機能廃止故コメントアウト化

	/**
	 * 注文検索結果CSV実行ロジック(検索パターン1)
	 *
	 * @param array $params
	 * @return array
	 */
	public function getSP1SearchResult($params)
	{
        $query = "
            SELECT
                ORD.OrderId,
                SUM(RC.ReceiptAmount) AS ReceiptAmount,
                ORD.UseAmount
            FROM
                T_ReceiptControl RC
                INNER JOIN T_ClaimControl CC ON CC.OrderSeq = RC.OrderSeq
                INNER JOIN T_Order ORD ON ORD.OrderSeq = RC.OrderSeq
           WHERE 1 = 1
                %s
           GROUP BY ORD.OrderSeq
            ";

        $where = "";
        // 支払期限
        $wLimitDate = BaseGeneralUtils::makeWhereDate(
            'CC.F_LimitDate',
            BaseGeneralUtils::convertWideToNarrow($params['LimitDateF']),
            BaseGeneralUtils::convertWideToNarrow($params['LimitDateT'])
        );

        if ($wLimitDate != '') {
            $where .= " AND " . $wLimitDate;
        }

        //入金日
        $ReceiptDate .= BaseGeneralUtils::makeWhereDate(
        'RC.ReceiptDate',
        BaseGeneralUtils::convertWideToNarrow($params['ReceiptDateF']),
        BaseGeneralUtils::convertWideToNarrow($params['ReceiptDateT'])
        );

        if ($ReceiptDate != '') {
            $where .= " AND " . $ReceiptDate;
        }

        // ここまで条件がなかったら例外
        if(!strlen($where)) {
            throw new SearchoControllerException('検索条件が入力されていません');
        }

        // クエリー生成
        $query = sprintf($query, $where);

	    return ResultInterfaceToArray($this->app->dbAdapter->query($query)->execute(null));
	}

	/**
	 * 注文検索結果CSV実行ロジック(検索パターン2)
	 *
	 * @param array $params
	 * @return array
	 */
	public function getSP2SearchResult($params)
	{
	    $query = "
            SELECT DISTINCT
                ORD.OrderId,
                ORD.RegistDate,
                ORD.ReceiptOrderDate,
                SITE.SiteId,
                ENT.EnterpriseNameKj,
                CUS.NameKj,
                ORD.Incre_Note,
                CUS.Phone,
                CUS.MailAddress,
                CUS.UnitingAddress,
                DELI.UnitingAddress AS DestUnitingAddress,
                CC.F_LimitDate,
                ORD.Incre_DecisionOpId,
                ORD.Incre_ScoreTotal,
                CASE WHEN CJR.TotalScoreWeighting IS NOT NULL THEN (CJR.TotalScoreWeighting + IFNULL(ORD.Incre_JudgeScoreTotal,0))
                     WHEN CJR.TotalScore IS NOT NULL THEN (CJR.TotalScore + IFNULL(ORD.Incre_JudgeScoreTotal,0))
                     ELSE NULL
                END AS TotalScore,
                RC.ReceiptDate,
                CC.ReceiptAmountTotal,
                ORD.Cnl_Status,
                ORD.Cnl_ReturnSaikenCancelFlg,
                OS.ItemNameKj,
                ITM.UnitPrice,
                ORD.UseAmount,
                CASE ORD.Incre_Status WHEN 1 THEN 'OK' WHEN -1 THEN 'NG' ELSE '確定待ち' END AS Incre_Status
            FROM
                T_Order ORD
                INNER JOIN T_Customer CUS ON (ORD.OrderSeq = CUS.OrderSeq)
                INNER JOIN T_OrderSummary OS ON (ORD.OrderSeq = OS.OrderSeq)
                INNER JOIN T_OrderItems ITM ON (OS.OrderItemId = ITM.OrderItemId)
                INNER JOIN T_DeliveryDestination DELI ON (ITM.DeliDestId = DELI.DeliDestId)
                INNER JOIN T_Enterprise ENT ON (ORD.EnterpriseId = ENT.EnterpriseId)
                LEFT OUTER JOIN T_CjResult CJR ON (
                    ORD.OrderSeq = CJR.OrderSeq AND
                    CJR.Seq = (
                        SELECT MAX(Seq)
                        FROM T_CjResult CJR2
                        WHERE CJR2.OrderSeq = ORD.OrderSeq
                    )
                )
                INNER JOIN T_Site SITE ON (ORD.SiteId = SITE.SiteId)
                LEFT OUTER JOIN T_ClaimControl CC ON (CC.OrderSeq = ORD.P_OrderSeq)
                LEFT OUTER JOIN T_ReceiptControl RC ON RC.ReceiptSeq = CC.LastReceiptSeq
                WHERE
                    1 = 1
                    %s
                    "
	            ;

	    $where = "";

        // 注文登録日
        $wRegistDate = BaseGeneralUtils::makeWhereDateTime(
            'ORD.RegistDate',
            BaseGeneralUtils::convertWideToNarrow($params['RegistDateF']),
            BaseGeneralUtils::convertWideToNarrow($params['RegistDateT'])
        );
        if ($wRegistDate != '') {
            $where .= " AND " . $wRegistDate;
        } else {
            throw new SearchoControllerException('検索条件：注文登録日は必須です。');
        }
        // 支払期限
        $wLimitDate = BaseGeneralUtils::makeWhereDate(
        'CC.F_LimitDate',
        BaseGeneralUtils::convertWideToNarrow($params['LimitDateF']),
        BaseGeneralUtils::convertWideToNarrow($params['LimitDateT'])
        );

        if ($wLimitDate != '') {
            $where .= " AND " . $wLimitDate;
        }

	    // クエリー生成
	    $query = sprintf($query, $where);

	    return ResultInterfaceToArray($this->app->dbAdapter->query($query)->execute(null));
	}

	/**
	 * 注文検索結果CSV実行ロジック(検索パターン3)
	 *
	 * @param array $params
	 * @return array
	 */
	public function getSP3SearchResult($params)
	{
	    $query = "
select temp.eseq,temp.useam,temp.cnt from (select tmp.ecseq as eseq,sum(tmp.uam) as useam,count(*) as cnt from (SELECT DISTINCT
       DATEDIFF(CURDATE(), CC.F_LimitDate) AS PastDays
,      P_ORD.OrderSeq
,      P_ORD.OrderId
,      P_ENT.EnterpriseNameKj
,      P_SITE.SiteNameKj
,      P_ORD.ReceiptOrderDate
,      P_ORD.ServiceExpectedDate
,      P_CUS.NameKj
,      P_CUS.NameKn
,      P_CUS.PostalCode
,      P_CUS.UnitingAddress
,      P_CUS.Phone
,      P_CUS.MailAddress
,      P_CUS.EntCustSeq as ecseq
,      P_MC.GoodFlg
,      P_MC.BlackFlg
,      (SELECT MAX(Deli_JournalIncDate) FROM T_OrderItems WHERE OrderSeq = P_ORD.OrderSeq AND ValidFlg = 1) AS Deli_JournalIncDate
,      P_MDM.DeliMethodName
,      P_ITM.Deli_JournalNumber
,      CC.F_ClaimDate
,      CC.F_LimitDate
,      P_ORD.UseAmount as uam
,      RC.ReceiptDate
,      RC.ReceiptAmount
,      RC.ReceiptClass
,      P_ORD.Incre_Note
,      P_ORD.MailLimitPassageDate
,      P_CUS.eDen
,      P_CUS.PhoneHistory
,      P_ORD.Incre_DecisionOpId
,      P_CUS.Incre_ArAddr
,      P_CUS.Incre_ArTel
,      P_CUS.RealSendMailResult
,      P_ORD.DataStatus
,      P_ORD.CloseReason
,      P_ORD.Rct_Status
,      P_ORD.RemindClass
,      P_ORD.TouchHistoryFlg
,      P_ORD.BriefNote
,      P_ORD.VisitFlg
,      P_ORD.FinalityRemindDate
,      P_CUS.ValidTel
,      P_CUS.ValidMail
,      P_CUS.ValidAddress
,      P_CUS.ResidentCard
,      CC.ReceiptAmountTotal AS InstallmentPlanAmount
,      CC.ClaimedBalance
,      (SELECT CallResult FROM T_AutoCall WHERE Status = 1 AND OrderSeq = P_ORD.OrderSeq ORDER BY Seq DESC LIMIT 1 ) AS RemindResult
,      VCR.ReceiptDate AS CloseReceiptDate
,      P_ORD.Ent_OrderId
,      P_ORD.Oem_OrderId
,      P_OEM.OemNameKj
,      P_ORD.Ent_Note
,      P_ORD.Oem_Note
,      P_DELI.DestNameKj
,      P_DELI.DestNameKn
,      P_DELI.Phone AS DestPhone
,      P_DELI.PostalCode AS DestPostalCode
,      P_DELI.UnitingAddress AS DestUnitingAddress
,      (SELECT MAX(Deli_ConfirmArrivalDate) FROM T_OrderItems WHERE OrderSeq = P_ORD.OrderSeq AND ValidFlg = 1) AS Deli_ConfirmArrivalDate
,      PC.ExecScheduleDate
,      P_ORD.Incre_ScoreTotal
,      P_ENT.Salesman
,      RC.ReceiptProcessDate
,      (SELECT SUM(UseAmount) FROM T_Order WHERE P_OrderSeq = P_ORD.OrderSeq) AS P_UseAmount
,      P_ORD.Cnl_Status
,      CASE WHEN (IFNULL(P_ORD.Cnl_Status, 0) != 0) OR (P_ORD.DataStatus = 91 AND P_ORD.CloseReason = 4) THEN 0 -- ｷｬﾝｾﾙ OR 貸し倒れは除外
            WHEN IFNULL((SELECT ReceiptAmountTotal FROM T_ClaimControl WHERE OrderSeq = P_ORD.OrderSeq), 0)
              >= IFNULL((SELECT UseAmountTotal FROM T_ClaimControl WHERE OrderSeq = P_ORD.OrderSeq), 0) THEN IFNULL((SELECT UseAmountTotal FROM T_ClaimControl WHERE OrderSeq = P_ORD.OrderSeq), 0)
            ELSE IFNULL((SELECT ReceiptAmountTotal FROM T_ClaimControl WHERE OrderSeq = P_ORD.OrderSeq), 0)
       END AS CalcReceitAmount
,      IFNULL((SELECT ClaimAmount FROM T_ClaimControl WHERE OrderSeq = P_ORD.OrderSeq), 0) AS ClaimAmount
,      IFNULL((SELECT ClaimedBalance FROM T_ClaimControl WHERE OrderSeq = P_ORD.OrderSeq), 0) AS TotalAmount

FROM   T_Order ORD
       INNER JOIN T_Customer CUS ON (CUS.OrderSeq = ORD.OrderSeq)
       INNER JOIN T_OrderItems ITM ON (ITM.OrderSeq = ORD.OrderSeq)
       INNER JOIN T_DeliveryDestination DELI ON (DELI.DeliDestId = ITM.DeliDestId)
       INNER JOIN T_Enterprise ENT ON (ENT.EnterpriseId = ORD.EnterpriseId)
       INNER JOIN T_Site SITE ON (SITE.SiteId = ORD.SiteId)
       INNER JOIN T_EnterpriseCustomer EC ON (CUS.EntCustSeq = EC.EntCustSeq)
       INNER JOIN T_ManagementCustomer MC ON (EC.ManCustId = MC.ManCustId)
       INNER JOIN T_ClaimControl CC ON(CC.OrderSeq = ORD.P_OrderSeq)
       LEFT OUTER JOIN M_DeliveryMethod MDM ON (MDM.DeliMethodId = ITM.Deli_DeliveryMethod)
       LEFT OUTER JOIN T_Oem OEM ON (OEM.OemId = ENT.OemId)
       LEFT OUTER JOIN T_ReceiptControl RC ON (RC.ReceiptSeq = CC.LastReceiptSeq)

       LEFT OUTER JOIN T_PayingControl PC ON (PC.Seq = ORD.Chg_Seq)

       LEFT OUTER JOIN V_CloseReceiptControl VCR ON (VCR.OrderSeq = CC.OrderSeq)

       /* 親注文の情報 */
       INNER JOIN T_Order P_ORD ON (P_ORD.OrderSeq  = ORD.P_OrderSeq)
       INNER JOIN T_Customer P_CUS ON (P_CUS.OrderSeq = P_ORD.OrderSeq)
       INNER JOIN T_OrderItems P_ITM ON (P_ITM.OrderSeq = P_ORD.OrderSeq)
       INNER JOIN T_DeliveryDestination P_DELI ON (P_DELI.DeliDestId = P_ITM.DeliDestId)
       INNER JOIN T_Enterprise P_ENT ON (P_ENT.EnterpriseId = P_ORD.EnterpriseId)
       INNER JOIN T_Site P_SITE ON (P_SITE.SiteId = P_ORD.SiteId)
       INNER JOIN T_EnterpriseCustomer P_EC ON (P_CUS.EntCustSeq = P_EC.EntCustSeq)
       INNER JOIN T_ManagementCustomer P_MC ON (P_EC.ManCustId = P_MC.ManCustId)
       LEFT OUTER JOIN M_DeliveryMethod P_MDM ON (P_MDM.DeliMethodId = P_ITM.Deli_DeliveryMethod)
       LEFT OUTER JOIN T_Oem P_OEM ON (P_OEM.OemId = P_ENT.OemId)

WHERE  1 = 1
AND    ORD.Cnl_Status = 0
AND    DATEDIFF(CURDATE(), CC.F_LimitDate) > 3
AND    ITM.ValidFlg = 1
AND    DELI.ValidFlg = 1
AND    P_ITM.ValidFlg = 1
AND    P_DELI.ValidFlg = 1
AND  (ORD.DataStatus = 51)
AND    P_ORD.DataStatus = 51
        %s
        ) tmp
-- ORDER BY PastDays DESC,RemindClass,UseAmount DESC,PostalCode,SiteNameKj
group by tmp.ecseq) temp
        %s
        "

	    ;

		$where = "";
	    // 支払約束日
	    $wPromPayDate = BaseGeneralUtils::makeWhereDate(
	    'ORD.PromPayDate',
	    BaseGeneralUtils::convertWideToNarrow($params['PromPayDateF']),
	    BaseGeneralUtils::convertWideToNarrow($params['PromPayDateT'])
	    );
	    if ($wPromPayDate != '') {
	        $where .= " AND " . $wPromPayDate;
	    }
	    // 支払約束日(親注文)
	    $wPPromPayDate = BaseGeneralUtils::makeWhereDate(
	    'P_ORD.PromPayDate',
	    BaseGeneralUtils::convertWideToNarrow($params['PromPayDateF']),
	    BaseGeneralUtils::convertWideToNarrow($params['PromPayDateT'])
	    );
	    if ($wPPromPayDate != '') {
	        $where .= " AND " . $wPPromPayDate;
	    }
	    // 注文金額
	    if ($params['UseAmountF'] == '' && $params['UseAmountT'] != '' && CoralValidate::isInt($params['UseAmountT'])) {
	        $wUseAmount = (" ORD.UseAmount = " . $params['UseAmountT']);
	    }
	    else {
	        $wUseAmount = BaseGeneralUtils::makeWhereInt(
	        'ORD.UseAmount',
	        BaseGeneralUtils::convertWideToNarrow($params['UseAmountF']),
	        BaseGeneralUtils::convertWideToNarrow($params['UseAmountT'])
	        );
	    }
	    if ($wUseAmount != '') {
	        $where .= " AND " . $wUseAmount;
	    }

	    $bindparams = array();
	    // 超過日数
	    if (CoralValidate::isInt($params['PastDaysF']) && CoralValidate::isInt($params['PastDaysT'])) {
	        $where .= " AND  DATEDIFF(CURDATE(), CC.F_LimitDate) BETWEEN :PastDaysF AND :PastDaysT ";
	        $bindparams += array(':PastDaysF' => $params['PastDaysF']);
	        $bindparams += array(':PastDaysT' => $params['PastDaysT']);
	    }
	    else if (CoralValidate::isInt($params['PastDaysF'])) {
	        $where .= " AND  DATEDIFF(CURDATE(), CC.F_LimitDate) >= :PastDaysF ";
	        $bindparams += array(':PastDaysF' => $params['PastDaysF']);
	    }
	    else if (CoralValidate::isInt($params['PastDaysT'])) {
	        $where .= " AND  DATEDIFF(CURDATE(), CC.F_LimitDate) <= :PastDaysT ";
	        $bindparams += array(':PastDaysT' => $params['PastDaysT']);
	    }

	    // 督促分類
        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
        $wRemindClass = $this->makeWhereFromCheckboxes('ORD.RemindClass', 'RemindClass_', $params, $codeMaster->getRemindClassMaster());
        if ($wRemindClass != '') {
            $where .= " AND " . $wRemindClass;
        }
        // 督促分類
        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
        $wRemindClass = $this->makeWhereFromCheckboxes('P_ORD.RemindClass', 'RemindClass_', $params, $codeMaster->getRemindClassMaster());
        if ($wRemindClass != '') {
            $where .= " AND " . $wRemindClass;
        }

	    $where2 = "";
	    $countFlg = false;
	    //件数条件
	    if(CoralValidate::isInt($params['Count'])){
	        $where2 .= " where  temp.cnt >= :Count ";
	        $bindparams += array(':Count' => $params['Count']);
	        $countFlg = true;
	    }
	    //総額
	    if(CoralValidate::isInt($params['UseAmountSUM'] && $countFlg)){
	        $where2 .= " and temp.useam >= :UseAmountSUM ";
	        $bindparams += array(':UseAmountSUM' => $params['UseAmountSUM']);
	    } else if(CoralValidate::isInt($params['UseAmountSUM'])){
	        $where2 .= " where temp.useam >= :UseAmountSUM ";
	        $bindparams += array(':UseAmountSUM' => $params['UseAmountSUM']);
	    }

	    // ここまで条件がなかったら例外
	    if(!strlen($where) && !strlen($where2)) {
	        throw new SearchoControllerException('検索条件が入力されていません');
	    }

	    // クエリー生成
	    $query = sprintf($query, $where, $where2);

	    $datas = ResultInterfaceToArray($this->app->dbAdapter->query($query)->execute($bindparams));

	   //取得できなかった場合後続の処理をしない
	   if(empty($datas)){
	       return ;
	   }
	   $ecseq = "";
	   foreach ($datas as $data){
	       if(empty($ecseq)){
	           $ecseq .= $data['eseq'];
	       } else {
	           $ecseq .= ',' . $data['eseq'];
	       }

	   }

	   $sql = "
	   SELECT
	   o.OrderId,
	   o.ReceiptOrderDate,
	   e.EnterpriseNameKj,
	   os.NameKj,
	   os.Phone,
	   os.MailAddress,
	   os.PostalCode,
	   os.UnitingAddress,
	   vd.Deli_ConfirmArrivalDate,
	   pc.ExecScheduleDate,
	   o.UseAmount,
	   os.ItemNameKj,
	   CASE o.Incre_Status WHEN 1 THEN 'OK' WHEN -1 THEN 'NG' ELSE '確定待ち' END AS Incre_Status,
	   o.PromPayDate,
	   (SELECT KeyContent FROM M_Code WHERE CodeId = 18 And KeyCode = o.RemindClass) AS RemindClass,
	   o.CombinedClaimTargetStatus
	   FROM T_Order o
	   INNER JOIN T_OrderSummary os on (o.OrderSeq = os.OrderSeq)
	   INNER JOIN T_Enterprise e on (o.EnterpriseId = e.EnterpriseId)
	   INNER JOIN T_Customer c on (o.OrderSeq = c.OrderSeq)
	   INNER JOIN T_EnterpriseCustomer ec on (c.EntCustSeq = ec.EntCustSeq)
	   INNER JOIN T_PayingAndSales pas on (o.OrderSeq = pas.OrderSeq)
	   INNER JOIN T_PayingControl pc on (pc.Seq = pas.PayingControlseq)
	   INNER JOIN V_Delivery vd on (o.OrderSeq = vd.OrderSeq)
	   where ec.EntCustSeq in ( %s )
	   and o.DataStatus = 51
	   group by OrderId
	   order by ec.EntCustSeq
	   ";

	   // クエリー生成
	   $sql = sprintf($sql, $ecseq);

	    return ResultInterfaceToArray($this->app->dbAdapter->query($sql)->execute(null));
	}

	/**
	 * チェックボックスの配列による検索フォームに対応するWHERE句を作成する。
	 *
	 * @access private
	 *
	 * @param $fieldName データベースフィールド名
	 * @param $tagFrefixName エレメントのNAME属性の接頭語
	 * @param $params ポストデータ
	 * @param $master 検索フィールドを構成するマスター
	 *
	 * @return string WHERE句
	 */
	private function makeWhereFromCheckboxes($fieldName, $tagFrefixName, $params, $master)
	{
	    $whereIn = '';
	    $whereNull = '';

	    foreach($master as $value => $key)
	    {
	        if ($value > 0 && isset($params[$tagFrefixName . $value]))
	        {
	            $whereIn .= $value . ',';
	        }
	        else if ($value == 0 && isset($params[$tagFrefixName . $value]))
	        {
	            $whereNull = sprintf("(%s = 0 OR %s IS NULL)", $fieldName, $fieldName);
	        }
	    }

	    if ($whereIn != '')
	    {
	        $whereIn = substr($whereIn, 0, strlen($whereIn) - 1);
	        $whereIn = sprintf("%s IN (%s)", $fieldName, $whereIn);
	    }

	    if ($whereIn != '' && $whereNull != '')
	    {
	        $where = sprintf("(%s OR %s)", $whereNull, $whereIn);
	    }
	    else if ($whereIn != '')
	    {
	        $where = $whereIn;
	    }
	    else if ($whereNull != '')
	    {
	        $where = $whereNull;
	    }
	    else
	    {
	        $where = "";
	    }

	    return $where;
	}

	/**
	 * 検索で入力された事業者ログインIDの後方パートの不足分を
	 * DBから取得した情報で補って検索用ログインIDの配列を生成する。
	 * 入力されたIDの数字部分が8桁以上ある場合は後方8桁分から1件のみ生成する。
	 * 入力桁数が3桁未満の場合は、ID指定がなかったものと見なして空の配列を返す。
	 *
	 * @access private
	 * @param string $id 事業者ログインID。呼出し前に正規化されている必要がある
	 * @return array
	 */
    private function _fixEntLoginIdForSearch($id)
    {
        // 要求IDをプレフィックス部分とID部分に分離
        preg_match('/^([a-z]{2})?(.*)$/i', $id, $matches);
        $prefix = strtoupper($matches[1]);
        $id = substr($matches[2], max(-8, -1 * strlen($matches[2])));  // ID部は末尾から最大8文字

        // 不足桁数を算出
        $dif = 8 - strlen($id);

        // 入力段階で8桁ありプレフィックスもある場合はそのまま元プレフィックスを追加して返す
        if($dif == 0 && strlen($prefix)) return array(sprintf('%s%s', $prefix, $id));

        $result = array();
        if($dif == 0) {
            // この時点で8桁ある場合はそのまま元プレフィックスを追加して返す
            $result = array(sprintf('%s%s', $prefix, $id));
        }
        else {
            // 足りない桁数分をDBから取得して必要なIDを生成する
            $q = sprintf('SELECT DISTINCT SUBSTRING(LoginId, 1, %d) AS substrloginid FROM T_Enterprise', $dif + 2);
            if(strlen($prefix)) {
                // プレフィックス指定がある場合は一致するプレフィックスのみに絞り込む
                $q .= sprintf(" WHERE LoginId LIKE '%s%%'", BaseUtility::escapeWildcard($prefix));
            }
            $ri = $this->app->dbAdapter->query($q)->execute(null);
            foreach($ri as $row) {
                $result[] = ($row['substrloginid'] . $id);
            }
        }

        // 生成したすべてのIDをリストで返す
        return $result;
    }

	/**
	 * 定型検索フォーム設定を構築する
	 *
	 * @access private
	 * @return array
	 */
	private function _buildSpecialSearchConfig()
	{
        $date_list = array();
        $now = date('Y-m-01');
        $now = date('Y-m-d', strtotime($now . ' -1 month'));

        for($i = 0; $i < 12; $i++) {
            $date_list[] = date('Y-m-d', strtotime($now));
            $now = date('Y-m-d', strtotime($now . ' -1 month'));
        }

        $search_config = array();
        foreach($this->_ssearch_types as $index => $config) {
            $search_config[] = array_merge(array('dates' => $date_list), $config);
        }

        // 加盟店リストの取得とドロップダウン化
        $ri = $this->app->dbAdapter->query(" SELECT EnterpriseId, EnterpriseNameKj FROM T_Enterprise ORDER BY EnterpriseId ")->execute();
        $entList = array(0 => '--事業者　ALL--');
        foreach ($ri as $row) {
            $entList[$row['EnterpriseId']] = $row['EnterpriseNameKj'];
        }
        $obj = BaseHtmlUtils::SelectTag("entListTag", $entList, 0, ' style="width: 200px" ');

        // count関数対策
        $search_configLen = 0;
        if(!empty($search_config)) {
            $search_configLen = count($search_config);
        }

        for ($i=0; $i<$search_configLen; $i++) {
            $search_config[$i]['entListTag'] = $obj;
        }

        return $search_config;
	}

	/**
	 * 定型検索の基準日から対象期間を取得する
	 *
	 * @access private
	 * @param string $date 基準日
	 * @param null|string $type 定型検索タイプ
	 * @return array キー'start'と'end'に日付文字列を格納した連想配列
	 */
	private function _getSpecialSearchDateSpan($date, $type = null)
	{
	    $q = <<<EOQ1
SELECT
	(DATE_FORMAT(:date, '%Y-%m-01') + INTERVAL 0 SECOND) AS start,
	(DATE_FORMAT(:date, '%Y-%m-01') + INTERVAL 1 MONTH - INTERVAL 1 SECOND) AS end
EOQ1;
        return $this->app->dbAdapter->query($q)->execute(array('date' => $date))->current();
	}

	/**
	 * 指定の配列に含まれる配列から指定キーの値のみ抽出して配列として返す。
	 * 戻り値の配列は引数$arrayと同じ長さ・同じキーで構成され、値は子配列の$fieldに
	 * 対応する要素が格納される
	 *
	 * @param mixed $field 子配列の値を抽出するキー
	 * @param array $array 元の親配列。ジャグ配列（各要素が配列になっている配列）である必要がある
	 * @return array
	 */
	private function collect_field($field, $array) {
        $result = array();
        foreach($array as $key => $row ) {
            $result[] = is_array($row) ? $row[$field] : null;
        }
        return $result;
	}

	/**
	 * 指定の配列に含まれる配列から指定キーの値のみ抽出して配列として返す。
	 * グループキーがの値が重複する行は1行のみ抽出する。
	 * 戻り値の配列は引数$arrayと同じ長さ・同じキーで構成され、値は子配列の$fieldに
	 * 対応する要素が格納される
	 *
	 * @param mixed $field 子配列の値を抽出するキー
	 * @param array $array 元の親配列。ジャグ配列（各要素が配列になっている配列）である必要がある
	 * @return array
	 */
	private function collect_field2($field, $group, $array) {
	    $result = array();
	    $add = array();
	    foreach($array as $key => $row ) {
	        if (is_array($row)) {
	            if (!in_array($row[$group], $add)) {
	               $result[] =  $row[$field];
	               $add[] = $row[$group];
	            }
	        }
	    }
	    return $result;
	}

	/**
	 * $params(コントロールへの通知項目)のpager対応調整
	 *
	 * @param array $params controllerへの通知内容
	 * @return array 調整された$params
	 */
	private function adjustmentParams($params)
	{
        $ret = array();

        if (!isset($params['page'])) {
            // 1. [$params] 内に、キー[page]が存在しないとき
            // a. 各画面からの検索ボタン押下イベントと判定
            // b. [$params] を [$_SESSION['SESS_SEARCHO']]へアサインする（一度セッション情報を破棄後、設定しゴミを含ませないようにする）
            unset($_SESSION['SESS_SEARCHO']);
            $_SESSION['SESS_SEARCHO'] = $params;

            $ret = $params;
        }
        else {
            // 2. [$params] 内に、キー[page]が存在するとき
            // a. ページング[前][後]ボタン押下イベントと判定
            // b. [$_SESSION['SESS_SEARCHO']] 内の、キー[page] 値を [$param['page']] 値で更新する
            // c. $param = $_SESSION['SESS_SEARCHO'] とする
            $_SESSION['SESS_SEARCHO']['page'] = $params['page'];

            $ret = $_SESSION['SESS_SEARCHO'];
        }

        return $ret;
	}
}

/** SearchoController固有の例外クラス */
class SearchoControllerException extends \Exception {}
