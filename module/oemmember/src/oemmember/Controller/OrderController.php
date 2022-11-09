<?php
namespace oemmember\Controller;

use Zend\Json\Json;
use Zend\Config\Reader\Ini;
use Zend\Session\Container;
use Zend\Db\ResultSet\ResultSet;
use Coral\Base\BaseGeneralUtils;
use Coral\Base\BaseHtmlUtils;
use Coral\Base\BaseUtility;
use Coral\Base\IO\BaseIOCsvWriter;
use Coral\Base\IO\BaseIOUtility;
use Coral\Base\IO\BaseIOCsvReader;
use Coral\Coral\CoralCodeMaster;
use Coral\Coral\Controller\CoralControllerAction;
use Coral\Coral\Form\CoralFormManager;
use Coral\Coral\Validate\CoralValidateUtility;
use Coral\Coral\Mail\CoralMail;
use Coral\Coral\Mail\CoralMailException;
use Coral\Coral\CsvHandler\CoralCsvHandlerLine;
use Coral\Coral\CsvHandler\CoralCsvHandlerOrder;
use oemmember\Application;
use oemmember\classes\OrderInputInfo;
use oemmember\classes\OrderEnums;
use oemmember\classes\OrderEditor;
use models\Table\TableSite;
use models\Table\TablePrefecture;
use models\Table\TableOrder;
use models\Table\TableOrderSummary;
use models\Table\TableDeliveryDestination;
use models\Table\TableCustomer;
use models\Table\TableOrderItems;
use models\Table\TableDeliMethod;
use models\Table\TablePostalCode;
use models\Logic\MergeOrder\LogicMergeOrderHelper;
use Coral\Coral\History\CoralHistoryOrder;
use models\Logic\LogicTemplate;
use models\Logic\LogicOrderRegister;
use models\Table\TableOrderHistory;
use models\Table\TableOrderNotClose;
use models\Table\ATableOrder;
use models\Table\TableSystemProperty;
use models\Logic\LogicNormalizer;
use models\Table\TableEnterprise;
//use models\Logic\Normalizer\LogicNormalizerZenHyphens;
//use models\Logic\Normalizer\LogicNormalizerZenHyphenCompaction;
use models\Logic\LogicPayeasy;
use models\Logic\LogicConstant;

/**
 * 注文登録コントローラ
 *
 */
class OrderController extends CoralControllerAction {
	/**
	 * 特殊商品「送料」を指定するキー
	 *
	 * @var string
	 */
	const CARRIAGE_KEY_NAME = 'order_item_carriage__';

	/**
	 * 特殊商品「店舗手数料」を指定するキー
	 *
	 * @var string
	 */
	const CHARGE_KEY_NAME = 'order_item_charge__';

	/**
	 * 特殊商品「外税」を指定するキー
	 *
	 * @var string
	 */
	const TAXCLASS_KEY_NAME = 'order_item_taxclass__';

	/**
	 * 一括伝票入力用CSVファイルのファイル名
	 *
	 */
	const DEAL_CSV_FILE_NAME = 'deal.csv';

	/**
	 * 一括登録時のファイルフィールドのname属性
	 *
	 * @var string
	 */
	const UPLOAD_FIELD_NAME = 'Csv_File';

	/**
	 * ビューコンポーネントのルートディレクトリパス
	 *
	 * @var string
	 */
	protected $_componentRoot = './application/views/components';

	/**
	 * 現在のアカウントに関連付けられたサイト情報
	 *
	 * @var ResultInterface
	 */
	private $sites;

	/**
	 * 都道府県情報
	 *
	 * @var ResultInterface
	 */
	private $prefectures;

	/**
	 * 注文フォーム向けのCoralFormManager
	 *
	 * @var CoralFormManager
	 */
	private $formManager;

	/**
	 * Cookieの情報
	 *
	 * @var CoralFormManager
	 */
	private $cookieValue;

	/**
	 * アプリケーションオブジェクト
	 * @var Application
	 */
	private $app;

	/**
	 * 加盟店の口座振替利用
	 *
	 * @var creditTransferFlg
	 */
	private $creditTransferFlg;

	/**
	 * コントローラを初期化する
	 *
	 */
protected function _init()
	{
        $this->app = Application::getInstance();

        // ビューへスタイルシートとJSを追加
        $this
            ->addStyleSheet( '../../oemmember/css/members.css' )
            ->addStyleSheet( '../../oemmember/css/index.css' )
            ->addStyleSheet( '../../css/base.ui.datepicker.css' )
            ->addStyleSheet( '../../css/base.ui.tableex.css' )
            ->addStyleSheet( '../../css/base.ui.modaldialog.css' )
            ->addStyleSheet( '../../oemmember/css/csv_table.css' )
            ->addStyleSheet( '../../oemmember/css/order_form.css' )
            ->addJavaScript( '../../js/prototype.js' )
            ->addJavaScript( '../../js/bytefx.js' )
            ->addJavaScript( '../../js/json+.js' )
            ->addJavaScript( '../../js/corelib.js' )
            ->addJavaScript( '../../js/base.ui.js' )
            ->addJavaScript( '../../js/base.ui.datepicker.js' )
            ->addJavaScript( '../../js/base.ui.tableex.js' )
            ->addJavaScript( '../../js/base.ui.modaldialog.js' )
            ->addJavaScript( '../../js/form_validator.js' );

        // プロパティ構築に必要なテーブルクラスを初期化
        $siteTable = new TableSite($this->app->dbAdapter);
        $prefTable = new TablePrefecture($this->app->dbAdapter);

        $enterpriseId = $this->app->authManager->getUserInfo()->EnterpriseId;

        // 現在のアカウントで定義されているサイト情報をビューにアサイン
        $ri = $siteTable->getValidAll($enterpriseId);
        $this->sites = ResultInterfaceToArray($ri);
        $this->view->assign( 'site_list', $this->sites );

        // ビューでリスト表示するため連想配列でも構築
        $sites = array();
        foreach( $this->view->site_list as $row ) { $sites[ $row['SiteId'] ] = $row['SiteNameKj']; }
        $this->view->assign('site_list_array', $sites);

        // 都道府県情報をビューへアサイン
        $this->prefectures = $prefTable->getAll();
        $prefectures = array();
        foreach( $this->prefectures as $prefRow ) {
            $prefectures[ $prefRow['PrefectureCode'] ] = $prefRow['PrefectureName'];
        }
        $this->view->assign( 'prefecture_info', $prefectures );

        $t_orderclass = array();
        $t_orderclass[0] = 'しない';
        $t_orderclass[1] = 'する';
        $this->view->assign( 'testorderclass_info', $t_orderclass );

        // 請求書別送(11:同梱／12同梱⇒別送／21:別送)
        $claimsendingclass = array(11 => 'しない', 12 => 'する');
        $this->view->assign( 'claimsendingclass_info', $claimsendingclass );

        //口座振替(0:利用しない／1:利用する（WEB申込み）／2:利用する（紙面申込み）)
        $creditTransferRequestFlg = array(0 => '利用しない', 1 => '利用する（WEB申込み）', 2 =>' 利用する（紙面申込み）');
        $this->view->assign( 'creditTransferRequestFlg_info', $creditTransferRequestFlg );

        $sql = <<<EOQ
SELECT *
FROM   T_Enterprise
WHERE  EnterpriseId = :EnterpriseId
EOQ;

        $ent = $this->app->dbAdapter->query($sql)->execute(array(':EnterpriseId' => $enterpriseId))->current();
        $creditTransferFlg = $ent['CreditTransferFlg'];
        $oemId = $ent['OemId'];
        $logicpayeasy = new LogicPayeasy($this->app->dbAdapter,$this->app->logger);

        // フォームマネージャの構築
        $validationMap = array();

        $this->formManager = OrderInputInfo::createFormManager($validationMap,$creditTransferFlg,$logicpayeasy->isPayeasyOem($oemId));
        $this->formManager->getItem( '/^T_Order\.SiteId$/i', CoralFormManager::SEARCH_BY_COLUMN )->setOptions($sites);
        $this->formManager->getItem( '/^T_Customer\.PrefectureCode$/i', CoralFormManager::SEARCH_BY_COLUMN )->setOptions($prefectures);
        $this->formManager->getItem( '/^T_DeliveryDestination\.PrefectureCode$/i', CoralFormManager::SEARCH_BY_COLUMN )->setOptions($prefectures);
        $this->formManager->getItem( '/^T_Order\.T_OrderClass$/i', CoralFormManager::SEARCH_BY_COLUMN )->setOptions($t_orderclass);
        $this->formManager->getItem( '/^T_Order\.ClaimSendingClass$/i', CoralFormManager::SEARCH_BY_COLUMN )->setOptions($claimsendingclass);
        if (($creditTransferFlg == 1) || ($creditTransferFlg == 2) || ($creditTransferFlg == 3)) {
            $this->formManager->getItem( '/^AT_Order\.CreditTransferRequestFlg$/i', CoralFormManager::SEARCH_BY_COLUMN )->setOptions($creditTransferRequestFlg);
        }

        $this->view->assign( 'form_info', $this->formManager );

        //加盟店の口座振替利用
        $this->view->assign( 'creditTransferFlg', $creditTransferFlg);
        $this->creditTransferFlg = $creditTransferFlg;

        // メニュー情報をマスタから取得
        $menu_info = $this->app->getMenuInfo();

        foreach( $menu_info as $key => $info ) {
            $this->view->assign( $key, $info );
        }

        // クライアントサイドで検証を行うかのフラグを設定
        $this->view->assign( 'validate_on_client', $this->app->orderItemConfig['validate_on_client'] );

        // 検証エラーリスト
        $this->view->assign( 'postErrors', array() );
        $this->view->assign( 'postItemErrors', array() );

        // 商品送料と店舗手数料の商品名を設定から取得
        $this->view->assign( self::CARRIAGE_KEY_NAME, isset( $this->app->orderItemConfig['order_item_carriage'] ) ?
            $this->app->orderItemConfig['order_item_carriage'] : '__ORDER_ITEM_CARRIAGE__' );
        $this->view->assign( self::CHARGE_KEY_NAME, isset( $this->app->orderItemConfig['order_item_charge'] ) ?
            $this->app->orderItemConfig['order_item_charge'] : '__ORDER_ITEM_CHARGE__' );
        $this->view->assign( self::TAXCLASS_KEY_NAME, isset( $this->app->orderItemConfig['order_item_taxclass'] ) ?
            $this->app->orderItemConfig['order_item_taxclass'] : '__ORDER_ITEM_TAXCLASS__' );

        // POSTされたデータを連想配列にして登録
        $posts = array();
        foreach( $_POST as $key => $val ) {
            $posts[ $key ] = $val;
        }
        $this->view->assign( 'postData', $posts );
        $this->view->assign( 'cssName', "order" );

        // このコントローラでは実行限界時間を未設定にする
        ini_set( 'max_execution_time', 0 );

        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
        $ar = $codeMaster->getMasterCodes(87);
        $this->view->assign("cmst87Tag",BaseHtmlUtils::SelectTag('cmst87',$ar,0));
        $this->view->assign("cmst87ar",$ar);
        $this->view->assign("userInfo",$this->app->authManager->getUserInfo());
	}

	/**
	 * indexアクション
	 *
	 */
	public function indexAction()
	{
	    return $this->_forward( 'order' );
	}

	/**
	 * orderアクション。個別注文登録フォームを表示する
	 *
	 */
	public function orderAction()
	{
	    $this->setPageTitle( '注文登録（個別登録）' );

	    $session = new Container('OrderAction');

	    $param_route = $this->params()->fromRoute();
	    $postdata_mode = isset($param_route['mode']) ? $param_route['mode'] : null;

	    $reorderFlg = isset($session->orderInfo['reorderflg']) ? $session->orderInfo['reorderflg'] : 0;
	    if (isset($param_route['id'])) {
	        $reorder = $this->_getReOrder($param_route['id']);
	        $reorder['reorderflg'] = 1;
	        $this->view->assign( 'postData', $reorder );
	        if (isset($reorder['cmst87'])) {
	            $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
	            $ar = $codeMaster->getMasterCodes(87);
	            $this->view->assign("cmst87Tag",BaseHtmlUtils::SelectTag('cmst87',$ar,$reorder['cmst87']));
	        }
	        $reorderFlg = 1;
	    }
	    $this->view->assign('ReOrderFlg', $reorderFlg);
	    $mdlEnt = new TableEnterprise($this->app->dbAdapter);
	    $oemid = $mdlEnt->find($this->app->authManager->getUserInfo()->EnterpriseId)->current()['OemId'];
	    $logicpayeasy = new LogicPayeasy($this->app->dbAdapter, $this->app->logger);
	    $this->view->assign('PayeasyFlg', $logicpayeasy->isPayeasyOem($oemid));
		$this->view->assign( 'mode', $postdata_mode );
		if( $postdata_mode == 'rollback' ) {
		    // confirmから戻ってきた場合
		    if( isset($session->orderInfo) ) $this->view->assign( 'postData', $session->orderInfo );
	        if (isset($session->orderInfo['cmst87'])) {
	            $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
	            $ar = $codeMaster->getMasterCodes(87);
	            $this->view->assign("cmst87Tag",BaseHtmlUtils::SelectTag('cmst87',$ar,$session->orderInfo['cmst87']));
	        }

		    // 任意注文番号検索
		    $order = new TableOrder($this->app->dbAdapter);
		    $caution = 0;
		    if(!empty($session->orderInfo['O_Ent_OrderId'])) {
		        $entOrderId = $order->searchEntId($session->orderInfo['O_Ent_OrderId'], $this->app->authManager->getUserInfo()->EnterpriseId);
		        if(trim($entOrderId) !== '') {
		            $caution = 1;
		        }
		    }
		    $this->view->assign( 'caution', $caution );
		}

		// 消費税率を初期画面時の設定
		$propertyTable = new TableSystemProperty($this->app->dbAdapter);
		$taxRate = $propertyTable->getTaxRateAt(date('Y-m-d'));
		$postedItem ['i_taxrate'] = $taxRate;

		$this->view->assign( 'postedItem', $postedItem );

		// セッションデータを廃棄
		if( isset($session->orderInfo) ) unset( $session->orderInfo );

		return $this->view;
	}

	/**
	 * confirmアクション。個別注文登録の確認画面を表示する
	 *
	 */
	public function confirmAction()
	{
        $errors = array();
        $itemErrors = array();
        $ignores = array();

        $posts = $this->view->postData;

        $postMaps = array();

        $anotherDeliFlg = isset($posts['AnotherDeliFlg']) ? $posts['AnotherDeliFlg'] : null;
        if( $anotherDeliFlg != OrderEnums::AnotherDeliFlg_ANOTHER_SPEC ) {
            // 別配送先が指定されていない場合は検証用の値のマップを作成する
            $postMaps = array(
                    'D_PostalCode' => 'C_PostalCode',
                    'D_PrefectureName' => 'C_PrefectureName',
                    'D_City' => 'C_City',
                    'D_Town' => 'C_Town',
                    'D_Building' => 'C_Building',
                    'D_Address' => 'C_Address',
                    'D_NameKj' => 'C_NameKj',
                    'D_NameKn' => 'C_NameKn',
                    'D_Phone' => 'C_Phone'
            );
            $ignores = array_keys( $postMaps );
        }

        // 選択されたサイトがメールアドレスを必須としない場合はC_MailAddressを無視リストに追加
        if( ! $this->getMailRequire( $posts['O_SiteId'] ) ) $ignores[] = 'C_MailAddress';

        // 選択されたサイトが[役務対象区分=1:役務]でなければO_ServiceExpectedDateを無視リストに追加
        $serviceTargetClass = $this->app->dbAdapter->query(" SELECT ServiceTargetClass FROM T_Site WHERE SiteId = :SiteId "
            )->execute(array(':SiteId' => $posts['O_SiteId']))->current()['ServiceTargetClass'];
        if( $serviceTargetClass != 1 ) $ignores[] = 'O_ServiceExpectedDate';

//        // 番地区切りの補正処理（08.7.14 追加）
//        foreach( array( 'C_City', 'C_Town', 'C_Building', 'C_Address', 'D_City', 'D_Town', 'D_Building', 'D_Address' ) as $key ) {
//            if( isset( $posts[$key] ) ) {
//                // 全角ハイフン（SJIS:0x815D）を半角マイナス記号に置換 → 印刷時消失対応
//                $posts[$key] = preg_replace( '/‐/', '-', $posts[$key] );
//                if ($this->view->postData[$key] != $posts[$key]) {
//                    $this->view->postData[$key] = $posts[$key];
//                }
//            }
//        }

//        //ハイフンの正規化
//        $lnzh = new LogicNormalizerZenHyphens();
//        $lnzhc = new LogicNormalizerZenHyphenCompaction();
//        foreach(array('C_NameKj','C_City','C_Town','C_Building','C_Address','D_NameKj','D_City','D_Town','D_Building','D_Address') as $key){
//            if(isset($posts[$key])){
//                $posts[$key] = $lnzhc->normalize($lnzh->normalize($posts[$key]));
//            }
//        }

        // 日付検証の対象リストを作成 (2008.02.21 追加 eda)
        $dateItems = array(
                'O_ReceiptOrderDate',
        );

        // 商品情報以外の検証
        mb_regex_encoding('UTF-8');
        foreach( $this->formManager->getAllItems() as $item ) {
            $key = $item->getName();

            if( empty( $key ) ) continue;
            $real_key = empty( $postMaps[$key] ) ? $key : $postMaps[$key];
            if( ! in_array( $key, $ignores ) ) {

            if ($key == 'O_ClaimSendingClass' || $key == 'O_T_OrderClass') { continue; }

                if($posts['reorderflg'] == 1 && ($key == 'C_PrefectureName' || $key == 'C_City' || $key == 'C_Town'
                    || ($posts['AnotherDeliFlg'] == 1 && ($key == 'D_PrefectureName' || $key == 'D_City' || $key == 'D_Town')))) { continue; }

                if($posts['reorderflg'] == 0 && ($key == 'C_Address' || ($posts['AnotherDeliFlg'] == 1 || $key == 'D_Address'))) { continue; }

                if( ! mb_ereg( $item->getEregValidation(), $posts[ $key ] ) ) {
                    $errors[] = $item;
                }
            }

            // 日付検証の対象の場合は日付としての妥当性も確認する (2008.02.21 追加 eda)
            if( in_array( $key, $dateItems ) ) {
                if( ! IsValidFormatDate(str_replace('/', '-', $posts[ $key ])) ) {
                    $errors[] = $item;
                }
            }

            // 役務提供予定日に30日以上過去日は指定できない
            $diffDate = BaseGeneralUtils::CalcSpanDays($posts[$key], date('Y-m-d'));
            if ($key == 'O_ServiceExpectedDate' && $serviceTargetClass == 1 && $diffDate >= 30) {
                $errors[] = $item;
            }
        }

        // 商品情報の検証
        $mdlEnt = new TableEnterprise($this->app->dbAdapter);
        $oemid = $mdlEnt->find($this->app->authManager->getUserInfo()->EnterpriseId)->current()['OemId'];
        $postItems = Json::decode( process_slashes( $this->view->postData['I_ItemList'] ), Json::TYPE_ARRAY );
        if( $postItems == null ) {
            $itemErrors[] = 0;
        } else {
            $postItemsCount = 0;
            if (!empty($postItems)) {
                $postItemsCount = count($postItems);
            }
            for($i = 0; $i < $postItemsCount; $i++) {
                $postItem = $postItems[$i];
                if(
                    empty( $postItem['i_itemnamekj'] ) ||
                    ! preg_match( CoralValidateUtility::INTEGER, $postItem['i_unitprice'] ) ||
                    ! preg_match( '/\d+(\.\d+)?/', $postItem['i_itemnum'] )
                ) {
                    $itemErrors[] = $i;
                } else if(
                    ((int)($postItem['i_itemnum'])) <= 0
                ) {
                    $itemErrors[] = $i;
                } else if(
                    (LogicConstant::OEM_ID_SMBC == $oemid) &&
                    (((int)($postItem['i_itemnum'])) > 999)
                ) {
                    $itemErrors[] = $i;
                }
            }
        }

        // 氏名カナ化(全角ひらがな⇒、半角カタカナ⇒、に対応する)
        if (isset($posts['C_NameKn'])) { $posts['C_NameKn'] = mb_convert_kana($posts['C_NameKn'], 'CK', 'UTF-8'); }
        if (isset($posts['D_NameKn'])) { $posts['D_NameKn'] = mb_convert_kana($posts['D_NameKn'], 'CK', 'UTF-8'); }

        //電話番号とメールアドレスを半角に変換
        if (isset($posts['C_Phone'])) { $posts['C_Phone'] = BaseGeneralUtils::convertWideToNarrow( $posts['C_Phone'] ); }
        if (isset($posts['C_MailAddress'])) { $posts['C_MailAddress'] = BaseGeneralUtils::convertWideToNarrow( $posts['C_MailAddress'] ); }

        //ペイジーの検証
//        $mdlEnt = new TableEnterprise($this->app->dbAdapter);
//        $oemid = $mdlEnt->find($this->app->authManager->getUserInfo()->EnterpriseId)->current()['OemId'];
        $logicpayeasy = new LogicPayeasy($this->app->dbAdapter, $this->app->logger);
        if($logicpayeasy->isPayeasyOem($oemid)){
            foreach( $this->formManager->getAllItems() as $item ) {
                $key = $item->getName();

                if($key == 'C_NameKj'){
                    $err = CoralValidateUtility::checkPeNameKj($posts[$key]);
                    if (isset($err)) {
                        $errors[] = $item;
                    }
                }else if($key == 'C_NameKn'){
                    $err = CoralValidateUtility::checkPeNameKn($posts[$key]);
                    if (isset($err)) {
                        $errors[] = $item;
                    }
                }else if($key == 'C_MailAddress'){
                    $err = CoralValidateUtility::checkPeMailAddress($posts[$key]);
                    if (isset($err)) {
                        $errors[] = $item;
                    }
                }else if($key == 'C_Phone'){
                    $err = CoralValidateUtility::checkPePhoneNumber($posts[$key]);
                    if (isset($err)) {
                        $errors[] = $item;
                    }
                }
            }
        }
        if( !empty($errors) || !empty($itemErrors) ) {
            $messages = array( '以下の項目のデータに誤りがあります。' );
            foreach( $errors as $error_item ) {
                $messages[] = '・' . $error_item->getCaption();
            }
            foreach( $itemErrors as $item_num ) {
                $num = $item_num + 1;
                $messages[] = "・商品情報 ( $num 番目 )";
            }
            $this->view->assign( 'systemMessages', array_reverse( $messages ) );
            $this->view->assign( 'postErrors', $errors );
            $this->view->assign( 'postItemErrors', $itemErrors );
            $this->view->assign( 'postData', $posts );
            $this->view->assign( 'ReOrderFlg', $posts['reorderflg'] );
            $this->setTemplate('order');
            return $this->view;
        }

        // 任意注文番号検索
        $order = new TableOrder($this->app->dbAdapter);
        $caution = 0;
        if($posts['O_Ent_OrderId'] != "") {
            $entOrderId = $order->searchEntId($posts['O_Ent_OrderId'], $this->app->authManager->getUserInfo()->EnterpriseId);
            if(trim($entOrderId) != '') {
                $caution = 1;
            }
        }
        $this->view->assign( 'caution', $caution );
        $this->view->assign( 'postData', $posts );

        // 注文日が妥当か？
        $caution2 = 0;
        if (!$this->isValidOrderdate(date('Y-m-d', strtotime($posts['O_ReceiptOrderDate'])))) {
            $caution2 = 1;
        }
        $this->view->assign( 'caution2', $caution2 );

        $mdl = new TablePostalCode($this->app->dbAdapter);
        $mdlprefecture = new TablePrefecture($this->app->dbAdapter);
        // 郵便番号に対する住所が妥当か？
        $caution3 = 0;
        // (ご注文者（請求先）情報)
        if ($posts['reorderflg'] == 0) {
            if (!$mdl->isValidPostAddressKanji($posts['C_PostalCode'],
            $mdlprefecture->getPrefectureName($posts['C_PrefectureName']) . $posts['C_City'] . $posts['C_Town'] . $posts['C_Building'])) {
                $caution3 = 1;
            }
            if ($caution3 == 0 && isset($posts['D_PostalCode'])) {
                if (!$mdl->isValidPostAddressKanji($posts['D_PostalCode'],
                        $mdlprefecture->getPrefectureName($posts['D_PrefectureName']) . $posts['D_City'] . $posts['D_Town'] . $posts['D_Building'])) {
                    $caution3 = 1;
                }
            }
        } elseif ($posts['reorderflg'] == 1) {
            if (!$mdl->isValidPostAddressKanji($posts['C_PostalCode'], $posts['C_Address'])) {
                $caution3 = 1;
            }
            if ($caution3 == 0 && isset($posts['D_PostalCode'])) {
                if (!$mdl->isValidPostAddressKanji($posts['D_PostalCode'], $posts['D_Address'])) {
                    $caution3 = 1;
                }
            }
        }

        $this->view->assign( 'caution3', $caution3 );

        // 郵便番号に対する住所が、検索状態のままであるか？
        $caution4 = 0;
        // (ご注文者（請求先）情報)
        if ($posts['reorderflg'] == 0) {
            if ($mdl->isPerfectMatchPostAddressKanji($posts['C_PostalCode'],
            $mdlprefecture->getPrefectureName($posts['C_PrefectureName']) . $posts['C_City'] . $posts['C_Town'] . $posts['C_Building'])) {
                $caution4 = 1;
            }
            if ($caution4 == 0 && isset($posts['D_PostalCode'])) {
                if ($mdl->isPerfectMatchPostAddressKanji($posts['D_PostalCode'],
                $mdlprefecture->getPrefectureName($posts['D_PrefectureName']) . $posts['D_City'] . $posts['D_Town'] . $posts['D_Building'])) {
                    $caution4 = 1;
                }
            }
        } elseif ($posts['reorderflg'] == 1) {
            if ($mdl->isPerfectMatchPostAddressKanji($posts['C_PostalCode'], $posts['C_Address'])) {
                $caution4 = 1;
            }
            if ($caution4 == 0 && isset($posts['D_PostalCode'])) {
                if ($mdl->isPerfectMatchPostAddressKanji($posts['D_PostalCode'], $posts['D_Address'])) {
                    $caution4 = 1;
                }
            }
        }
        $this->view->assign( 'caution4', $caution4 );

        $this->setPageTitle( '登録内容の確認' );
        $this->addStyleSheet( '../../oemmember/css/order_confirm.css' );

        // 今回POSTされたデータをセッションオブジェクトに格納しておく
        $session = new Container('OrderAction');
        $session->orderInfo = $posts;

        $this->view->assign("userInfo",$this->app->authManager->getUserInfo());

        //------------------------
        // 場合により[表示/非表示]が切替わる項目
        //------------------------
        $sql = " SELECT * FROM T_Site WHERE SiteId = :SiteId ";
        $row = $this->app->dbAdapter->query($sql)->execute(array(':SiteId' => $posts['O_SiteId']))->current();
        // 役務対象区分
        $this->view->assign("isShowServiceTargetClass", ($row['ServiceTargetClass'] == 1) ? true : false);
        // テスト注文
        $this->view->assign("isShowTOrderClass", ($row['T_OrderClass'] == 1) ? true : false);
        // 請求書別送
        $this->view->assign("isShowSelfBillingFlg", (($this->app->authManager->getUserInfo()->SelfBillingMode > 0) && ($row['SelfBillingFlg'] == 1)) ? true : false);
        // 再請求
        $this->view->assign("isShowReOrderFlg", ($posts['reorderflg'] == 1) ? true : false);

        return $this->view;
	}

	/**
	 * saveアクション。個別注文登録データをDBへ保存する
	 *
	 */
	public function saveAction()
	{
        $session = new Container('OrderAction');
        $posts = $session->orderInfo;

        // ユーザーIDの取得
        $obj = new \models\Table\TableUser($this->app->dbAdapter);
        getUserInfoForMember($this->app, $userClass, $seq);
        $userId = $obj->getUserId($userClass, $seq);

        $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
        try {
            $arrays = $this->createOrderArraysFromPost( $posts );
            $arrays['customer']['ClaimSendingClass'] = $arrays['order']['ClaimSendingClass'];
            // (請求先)都道府県コード、都道府県名の調整
            if ($arrays[OrderInputInfo::ARRKEY_CUSTO]['PrefectureCode'] == 0) {
                // 0は一旦NULLに設定
                $arrays[OrderInputInfo::ARRKEY_CUSTO]['PrefectureCode'] = null;
                $arrays[OrderInputInfo::ARRKEY_CUSTO]['PrefectureName'] = '';
                // 逆引きできれば設定する
                $sql = "SELECT * FROM M_Prefecture WHERE :UnitingAddress LIKE CONCAT(PrefectureName, '%') AND PrefectureCode > 0";
                $row = $this->app->dbAdapter->query($sql)->execute(array(':UnitingAddress' => $arrays[OrderInputInfo::ARRKEY_CUSTO]['UnitingAddress']))->current();
                if ($row) {
                    $arrays[OrderInputInfo::ARRKEY_CUSTO]['PrefectureCode'] = $row['PrefectureCode'];
                    $arrays[OrderInputInfo::ARRKEY_CUSTO]['PrefectureName'] = $row['PrefectureName'];
                }
            }
            // (配送先)都道府県コード、都道府県名の調整
            if ($arrays[OrderInputInfo::ARRKEY_DELIV]['PrefectureCode'] == 0) {
                // 0は一旦NULLに設定
                $arrays[OrderInputInfo::ARRKEY_DELIV]['PrefectureCode'] = null;
                $arrays[OrderInputInfo::ARRKEY_DELIV]['PrefectureName'] = '';
                // 逆引きできれば設定する
                $sql = "SELECT * FROM M_Prefecture WHERE :UnitingAddress LIKE CONCAT(PrefectureName, '%') AND PrefectureCode > 0";
                $row = $this->app->dbAdapter->query($sql)->execute(array(':UnitingAddress' => $arrays[OrderInputInfo::ARRKEY_DELIV]['UnitingAddress']))->current();
                if ($row) {
                    $arrays[OrderInputInfo::ARRKEY_DELIV]['PrefectureCode'] = $row['PrefectureCode'];
                    $arrays[OrderInputInfo::ARRKEY_DELIV]['PrefectureName'] = $row['PrefectureName'];
                }
            }

            $seq = $this->saveOrderData($arrays, $userId);

            // 注文履歴へ登録
            $history = new CoralHistoryOrder($this->app->dbAdapter);
            $history->InsOrderHistory($seq, 11, $userId);

            $this->app->dbAdapter->getDriver()->getConnection()->commit();
        }
        catch(\Exception $err) {
            $this->app->dbAdapter->getDriver()->getConnection()->rollBack();
            throw $err;
        }

        // セッションデータを廃棄
        if( isset($session->orderInfo) ) unset( $session->orderInfo );

        // 注文登録メールを送信
        try {
            CoralMail::create( $this->app->dbAdapter, $this->app->smtpServer )->SendOrderedMail( $this->app->authManager->getUserInfo()->EnterpriseId, array( $seq ), $userId );
        }
        catch(CoralMailException $e) {
            // CoralMail内での例外のみ捕捉
            $this->flashMessenger()->addMessage('メール送信失敗');
        }

        // 完了ページへリダイレクト
        return $this->_redirect( 'order/complete' );
	}

	/**
	 * completeアクション。個別注文登録の完了画面を表示する
	 *
	 */
	public function completeAction()
	{
	    $this->setPageTitle( '注文登録完了（個別登録）');
        $this->view->assign('systemMessages', $this->flashMessenger()->getMessages() );
        return $this->view;
	}

	/**
	 * orderCsvアクション。一括注文登録フォームを表示する
	 *
	 */
	public function orderCsvAction()
	{
        // セッションオブジェクトがあったら廃棄
        $session = new Container( 'OrderAction' );
        if( isset( $session->dbRows ) ) {
            unset( $session->dbRows );
        }

        // タイトルだけ変更（暫定）
        $this->setPageTitle( '一括注文登録（CSV）' );
        $this->view->assign( 'field_name', self::UPLOAD_FIELD_NAME );

        return $this->view;
	}

	/**
	 * (CSV読込み時)テンポラリファイル名の生成
	 */
	private function makeTempFileName() {
        // 一時ファイルの先指定
        $mdlsp = new \models\Table\TableSystemProperty($this->app->dbAdapter);
        $tempDir = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'TempFileDir');

        return ($tempDir . '/__tmpcsv_' . $this->app->authManager->getUserInfo()->EnterpriseId . '_' . $this->app->authManager->getUserInfo()->LoginId . '__');
	}

	/**
	 * confirmCsvアクション。一括注文登録の確認画面を表示する
	 *
	 */
	public function confirmCsvAction()
	{
        $this->addStyleSheet( '../../oemmember/css/shipping_confirm_csv.css' );
        $params = $this->getParams();

        // 引きずり回し情報があれば、その内容を[$file]へアサイン
        $file = ( (isset($params['load_file_info'])) ? Json::decode($params['load_file_info'], Json::TYPE_ARRAY) : $_FILES[ self::UPLOAD_FIELD_NAME ] );

        $fileName = $file['name'];
        move_uploaded_file($file['tmp_name'], $this->makeTempFileName());   // テンポラリファイル名での一時退避

        $success_by_force = false;// 強制サクセスフラグ

        if( ! preg_match( '/\.xl.$/i', $fileName ) &&  $file['tmp_name'] != "") {
            $path = $this->makeTempFileName();                              // ファイルパスを[一時退避]名にアサイン
            BaseIOUtility::convertFileEncoding( $path, null, null, true );
            $reader = new BaseIOCsvReader( $path );
            $handler = new CoralCsvHandlerOrder( $reader, array(
                    CoralCsvHandlerOrder::OPTIONS_DBADAPTER => $this->app->dbAdapter,
                    CoralCsvHandlerOrder::OPTIONS_ENT_ID => $this->app->authManager->getUserInfo()->EnterpriseId
            ) );

            // 修正確認での編集内容通知
            if (isset($params['load_file_info'])) {
                $handler->setControllerParams($params);
            }

            $success = $handler->exec();

            // エラーがあっても[CSV一括登録区分(1：エラーがあってもOK分のみ登録)]であれば、強制的に登録実行を許す
            if ($this->app->authManager->getUserInfo()->CsvRegistClass == 1 && !empty($handler->getExceptions()) ) {
                $success_by_force = true;
            }

            $csvCaution = 0;
            $caution = 0;
            $duplication = array();
            $duplicateId = array();
            if( $success || $success_by_force ) {

                // 注文データのブロックを作成
                $i = 0;
                $totalItemCount = 0;
                $mapLineBloack = array();
                $orderData = array();
                foreach( $handler->getBuilders() as $builder ) {
                    $orderData[] = $builder->build();
                    $totalItemCount += $builder->itemCount();
                    $mapLineBloack[$i] = $totalItemCount;
                    $i++;
                }

                //任意注文番号チェック
                $checkEntId = array();
                $checkLine = array();
                $i = 0;
                $mdlo = new TableOrder( $this->app->dbAdapter );
                foreach( $orderData as $order ) {
                    $order[OrderInputInfo::ARRKEY_ORDER]['line'] = $i;
                    if("" != $order[OrderInputInfo::ARRKEY_ORDER]['Ent_OrderId']) {
                        $entOrderId = $mdlo->searchEntId($order[OrderInputInfo::ARRKEY_ORDER]['Ent_OrderId'], $this->app->authManager->getUserInfo()->EnterpriseId);
                        if('' !== trim($entOrderId)) {
                            $duplicateId[] = $entOrderId;
                            $caution = 1;
                        } else {
                            $checkEntId[] = $order[OrderInputInfo::ARRKEY_ORDER]['Ent_OrderId'];
                            $checkLine[] = $i;
                        }
                    }
                    $i++;
                }

                //csv重複チェック
                $i = 0;
                foreach( $orderData as $order ) {
                    $checkEntIdCount = 0;
                    if (!empty($checkEntId)) {
                        $checkEntIdCount = count($checkEntId);
                    }
                    for($j = 0; $j < $checkEntIdCount; $j++) {
                        if($checkEntId[$j] == $order[OrderInputInfo::ARRKEY_ORDER]['Ent_OrderId'] && $checkLine[$j] !== $i) {
                            $csvCaution = 1;
                            $duplication[] = $order[OrderInputInfo::ARRKEY_ORDER]['Ent_OrderId'];
                        }
                    }
                    $i++;
                }

                $mapAnotherWarning = array();

                // 注文日が妥当か？
                $i = 0;
                $csvCaution2 = 0;
                foreach( $orderData as $order ) {
                    if (!$this->isValidOrderdate(date('Y-m-d', strtotime($order['order']['ReceiptOrderDate'])))) {
                        $csvCaution2 = 1;
                        $mapAnotherWarning[$mapLineBloack[$i]] = true;
                    }
                    $i++;
                }
                $this->view->assign( 'csvCaution2', $csvCaution2 );

                $mdl = new TablePostalCode($this->app->dbAdapter);
                $mdlprefecture = new TablePrefecture($this->app->dbAdapter);
                // 郵便番号に対する住所が妥当か？
                $i = 0;
                $csvCaution3 = 0;
                foreach( $orderData as $order ) {
                    if (!$mdl->isValidPostAddressKanji($order['customer']['PostalCode'], $order['customer']['UnitingAddress'])) {
                        $csvCaution3 = 1;
                        $mapAnotherWarning[$mapLineBloack[$i]] = true;
                    }
                    if ($csvCaution3 == 0 && !$mdl->isValidPostAddressKanji($order['delivery']['DestPostalCode'], $order['delivery']['DestUnitingAddress'])) {
                        $csvCaution3 = 1;
                        $mapAnotherWarning[$mapLineBloack[$i]] = true;
                    }
                    $i++;
                }
                $this->view->assign( 'csvCaution3', $csvCaution3 );

                // 郵便番号に対する住所が、検索状態のままであるか？
                $i = 0;
                $csvCaution4 = 0;
                foreach( $orderData as $order ) {
                    if ($mdl->isPerfectMatchPostAddressKanji($order['customer']['PostalCode'], $order['customer']['UnitingAddress'])) {
                        $csvCaution4 = 1;
                        $mapAnotherWarning[$mapLineBloack[$i]] = true;
                    }
                    if ($csvCaution4 == 0 && $mdl->isPerfectMatchPostAddressKanji($order['delivery']['DestPostalCode'], $order['delivery']['DestUnitingAddress'])) {
                        $csvCaution4 = 1;
                        $mapAnotherWarning[$mapLineBloack[$i]] = true;
                    }
                    $i++;
                }
                $this->view->assign( 'csvCaution4', $csvCaution4 );

                $this->view->assign( 'mapAnotherWarning', $mapAnotherWarning );
            }

            $getAllResultsCount = 0;
            if (!empty($handler->getAllResults())) {
                $getAllResultsCount = count($handler->getAllResults());
            }
            $getBuildersCount = 0;
            if (!empty($handler->getBuilders())) {
                $getBuildersCount = count($handler->getBuilders());
            }
            $this->view->assign( 'colSchema', $handler->getCsvSchema() );
            $this->view->assign( 'importedRows', $handler->getResults() );
            $this->view->assign( 'errorRows', $handler->getExceptions() );
            $this->view->assign( 'headerRow', $handler->getHeader() );
            $this->view->assign( 'totalCount', $getAllResultsCount);
            $this->view->assign( 'totalOrderCount', $getBuildersCount );
            $this->view->assign( 'caution', $caution);
            $this->view->assign( 'csvCaution', $csvCaution);
            $this->view->assign( 'duplication', $duplication);
            $this->view->assign( 'duplicateId', $duplicateId);
            $this->view->assign( 'entId', $this->app->authManager->getUserInfo()->EnterpriseId);


//            //ハイフン正規化
//            $handler->hyphenNormalize($handler->getCsvSchema());
            //注文データ数が0件
            if($getAllResultsCount <= 0){
                $errors = array(
                        new CoralCsvHandlerLine(
                        array( 0 => array( 'データエラー' => 'データがありません' ) ),
                        0,
                        CoralCsvHandlerLine::TYPE_ERROR
                        ) );
            }else{
                $errors = $handler->getExceptions();
            }

            $this->view->assign( 'colSchema', $handler->getCsvSchema() );
            $this->view->assign( 'importedRows', $handler->getResults() );
            $this->view->assign( 'errorRows', $errors );
            $this->view->assign( 'headerRow', $handler->getHeader() );
            $this->view->assign( 'totalCount', $getAllResultsCount);
            $this->view->assign( 'totalOrderCount', $getBuildersCount );
            $this->view->assign( 'caution', $caution);
            $this->view->assign( 'csvCaution', $csvCaution);
            $this->view->assign( 'duplication', $duplication);
            $this->view->assign( 'duplicateId', $duplicateId);
            $this->view->assign( 'entId', $this->app->authManager->getUserInfo()->EnterpriseId);

            $clone_file = $file;
            $clone_file['tmp_name'] = $this->makeTempFileName();
            $this->view->assign( 'load_file_info', Json::encode($clone_file) );
        }
        else {
            $success = false;

            $this->view->assign( 'colSchema', new \stdClass() );
            $this->view->assign( 'errorRows', array(
                    new CoralCsvHandlerLine(
                        array( 0 => array( 'ファイル形式' => 'ファイル形式が適切ではありません。CSVファイルを登録してください' ) ),
                        0,
                        CoralCsvHandlerLine::TYPE_ERROR
                    ) )
            );
        }

        if( $success || $success_by_force ) {
            // エラーなし
            $this->setPageTitle( 'CSV登録確認' );

            // インポート結果をセッションオブジェクトに退避
            $db_rows = array();
            foreach( $handler->getBuilders() as $builder ) {
                $db_rows[] = $builder->build();
            }

//            //ハイフン正規化
//            $db_rows = $this->hyphenNormalize($db_rows);

            $session = new Container('OrderAction');
            $session->dbRows = $db_rows;

            // エラーがあっても[CSV一括登録区分(1：エラーがあってもOK分のみ登録)]であれば、強制的に登録実行を許す
            if (!$success_by_force) {
                if (isset($session->skipBlockList)) { unset($session->skipBlockList); }
                $this->view->assign( 'success_by_force', false );
            }
            else {
                $session->skipBlockList = $this->_makeSkipBlockList($db_rows, $handler->getExceptions());
                $this->view->assign( 'success_by_force', true );
            }
        } else {
            // エラーあり
            $this->setPageTitle( 'CSV登録エラー' );
            $this->view->assign( 'success_by_force', false );
        }

        return $this->view;
	}

    /**
     * 注文関連データの連想配列にハイフン正規化処理を行う
     * @return array 正規化した注文関連データの連想配列
     */
//	private function hyphenNormalize($db_rows){
//	    $lnzh = new LogicNormalizerZenHyphens();
//	    $lnzhc = new LogicNormalizerZenHyphenCompaction();
//	    for($i = 0; $i < count($db_rows);$i++){
//	        foreach(array('UnitingAddress', 'NameKj') as $key){
//	            if(isset ($db_rows[$i]['customer'][$key])){
//	                $db_rows[$i]['customer'][$key] = $lnzhc->normalize($lnzh->normalize($db_rows[$i]['customer'][$key]));
//	            }
//	        }
//	        foreach(array('DestUnitingAddress', 'DestNameKj') as $key){
//	            if(isset ($db_rows[$i]['delivery'][$key])){
//	                $db_rows[$i]['delivery'][$key] = $lnzhc->normalize($lnzh->normalize($db_rows[$i]['delivery'][$key]));
//	            }
//	        }
//	    }
//	    return $db_rows;
//	}
    /**
     * エラーあり時も強制的に登録実行を許す時の、処理除外ブロックリストの生成
     *
     * @param array $db_rows CSVブロック行情報
     * @param array $err_rows エラー行情報
     * @return array 処理除外ブロックリスト
     */
    protected function _makeSkipBlockList($db_rows, $err_rows)
    {
        // 何行目(1から)のデータが何ブロック番号(1から)に属しているかを、配列にて戻す
        $lineNumber = 0;
        $blockNumber = 0;
        $aryTmp = array();

        foreach ($db_rows as $db_row) {
            $blockNumber++;// ブロック番号加算

            foreach ($db_row['order_items'] as $roi) {
                if ($roi['DataClass'] != 1) { continue; }

                $lineNumber++;// ライン番号加算
                $aryTmp[$lineNumber] = $blockNumber;// 行番号とブロック番号のマッピング
            }
        }

        // スキップブロックリストの生成
        $ary_skipblocklist = array();
        foreach ($err_rows as $err_row) {
            $err_block_no = $aryTmp[$err_row->getLineNumber()];
            if (!in_array($err_block_no, $ary_skipblocklist)) {
                $ary_skipblocklist[] = $err_block_no;
            }
        }

        return $ary_skipblocklist;
    }

	/**
	 * saveCsvアクション。一括注文登録データをDBへ保存する
	 *
	 */
	public function saveCsvAction()
	{
        $params = $this->getParams();

        $session = new Container('OrderAction');

        if( isset( $session->dbRows ) ) {

            // ユーザーIDの取得
            $obj = new \models\Table\TableUser($this->app->dbAdapter);
            getUserInfoForMember($this->app, $userClass, $seq);
            $userId = $obj->getUserId($userClass, $seq);

            $history = new CoralHistoryOrder($this->app->dbAdapter);

            $seqs = array();
            $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
            try {
                // 件数分処理をする
                $offset = 0;
                foreach( $session->dbRows as $db_row ) {

                    // CSV一括登録区分(1：エラーがあってもOK分のみ登録)対応
                    $offset++;
                    if (isset($session->skipBlockList) && in_array($offset, $session->skipBlockList)) {
                        continue;
                    }

                    $newseq = $this->saveOrderData($db_row, $userId);

                    $sql_s = " SELECT * FROM T_Site WHERE SiteId = :SiteId ";
                    $row_site = $this->app->dbAdapter->query($sql_s)->execute(array(':SiteId' =>  $db_row[OrderInputInfo::ARRKEY_ORDER]['SiteId']))->current();
                    if ( $this->app->authManager->getUserInfo()->SelfBillingMode > 0 && $row_site['SelfBillingFlg'] == 1 && $row_site['SelfBillingFixFlg'] == 1 ) {
                        $sql_o = " UPDATE T_Order SET ClaimSendingClass = 11 WHERE OrderSeq = :OrderSeq ";
                        $this->app->dbAdapter->query($sql_o)->execute(array(':OrderSeq' => $newseq));
                    }

                    // 注文履歴へ登録
                    $history->InsOrderHistory($newseq, 12, $userId);

                    $seqs[] = $newseq;
                }

                $this->app->dbAdapter->getDriver()->getConnection()->commit();
            }
            catch(\Exception $err) {
                $this->app->dbAdapter->getDriver()->getConnection()->rollback();
                throw $err;
            }

            try {
                CoralMail::create( $this->app->dbAdapter, $this->app->smtpServer )->SendOrderedMail(
                    $this->app->authManager->getUserInfo()->EnterpriseId, $seqs, $userId);
            }
            catch(CoralMailException $e) {
                // CoralMail内での例外のみ捕捉
                $this->flashMessenger()->addMessage('メール送信失敗');
            }
        }

        return $this->_redirect( 'order/completeCsv' );
	}

	/**
	 * completeCsvアクション。一括注文登録の完了画面を表示する
	 *
	 */
	public function completeCsvAction()
	{
        $this->setPageTitle( '注文登録完了（CSV一括登録）');
        $this->setTemplate('complete');
        return $this->view;
	}

	/**
	 * downloadアクション。一括登録用のCSVファイルをダウンロードする
	 *
	 */
	public function downloadAction()
	{
        $templateId = 'CKA01001_1';    // 注文キャンセル申請（CSV）
        $templateClass = 2;
        $seq = $this->app->authManager->getUserInfo()->EnterpriseId;
        $templatePattern = 0;

        $logicTemplate = new LogicTemplate( $this->app->dbAdapter );
        $response = $logicTemplate->convertArraytoResponse( '', self::DEAL_CSV_FILE_NAME, $templateId, $templateClass, $seq, $templatePattern, $this->getResponse() );

        if( $response == false ) {
            throw new \Exception( $logicTemplate->getErrorMessage() );
        }

        return $response;
	}

    /**
     * editアクション。注文情報を修正する
     */
    public function editAction()
    {
        $params = array_merge($this->params()->fromRoute(), $this->params()->fromPost());

        $this->setPageTitle( '注文登録内容の修正' );

        $orderId = isset($params['id']) ? $params['id'] : -1;

        $row = $this->_getOrderForEdit($orderId);
        if( ! $row ) {
            $this->setTemplate('no-detail');
            return $this->view;
        }
        $order_seq = $row['OrderSeq'];

        // 整合性チェック用にOrderSeqとOrderIdからハッシュコードを作成
        $this->view->assign('verifyKey', $this->_calcOrderIdHash($row['OrderSeq'], $row['OrderId'], $row['CustomerId']));

        // 商品明細を取得
        $itemList = $this->_getOrderItemsForEdit($order_seq);

        $editor = new OrderEditor($this->app->dbAdapter);
        $modifiability = $editor->judgeOrderModifiability($order_seq);

        // 確認画面から戻ってきた場合は登録内容をマージ
        $input = unserialize(base64_decode($params['confirmedData']));

        if($input) {
            $editor = new OrderEditor($this->app->dbAdapter);

            if($modifiability[OrderEditor::GROUP_ORDER]) {
                foreach($input['Order'] as $key => $value) {
                    $row[$key] = $value;
                }
            }
            if($modifiability[OrderEditor::GROUP_CUSTOMER]) {
                foreach($input['Customer'] as $key => $value) {
                    $row[$key] = $value;
                }
            }
            if($modifiability[OrderEditor::GROUP_DELIVERY]) {
                foreach($input['Destination'] as $key => $value) {
                    if(in_array($key, array('UnitingAddress', 'PostalCode', 'Phone'))) {
                        $row['Dest' . $key] = $value;
                    } else {
                        $row[$key] = $value;
                    }
                }
            }
            if($modifiability[OrderEditor::GROUP_JOURNAL]) {
                foreach($input['Journal'] as $key => $value) {
                    $row[$key] = $value;
                }
            }

            $items = $this->_getOrderItemsForEdit($input['Order']['OrderSeq']);
            if($modifiability[OrderEditor::GROUP_ITEMS]) {
                $summary = 0;
                foreach($input['Items'] as $i => $item_info) {
                    if(isset($items[$i])) {
                        if(is_array($items[$i])) {
                            // 注文商品データが登録済みの場合
                            if( $items[$i]['OrderItemId'] == $item_info['OrderItemId']) {
                                $items[$i] = array_merge($items[$i], $item_info);
                            // すでに追加済みの場合
                            } else if ($item_info['OrderItemId'] == 'a') {
                                $items[$i] = $item_info;
                            }
                        }
                    // 新規で追加された場合
                    } else {
                        $items[$i] = $item_info;
                    }
                    $items[$i]['SumMoney'] = $items[$i]['UnitPrice'] * ($items[$i]['DataClass'] == 1 ? (double)$items[$i]['ItemNum'] : 1);
                    if ((int)$this->app->authManager->getUserInfo()->UseAmountFractionClass == 0) { $items[$i]['SumMoney'] = floor( $items[$i]['SumMoney'] ); }
                    if ((int)$this->app->authManager->getUserInfo()->UseAmountFractionClass == 1) { $items[$i]['SumMoney'] = round( $items[$i]['SumMoney'] ); }
                    if ((int)$this->app->authManager->getUserInfo()->UseAmountFractionClass == 2) { $items[$i]['SumMoney'] = ceil(  $items[$i]['SumMoney'] ); }

                    $summary += $items[$i]['SumMoney'];
                }
                $row['UseAmount'] = $summary;
            }
            $itemList = $items;
        }
        // 消費税率の設定
        $propertyTable = new TableSystemProperty($this->app->dbAdapter);
        $taxRate = $propertyTable->getTaxRateAt(date('Y-m-d'));

        //ペイジー検証用にOemIdを追加
        $sql = " SELECT OemId FROM T_Enterprise WHERE EnterpriseId = :EnterpriseId ";
        $oemId = $this->app->dbAdapter->query($sql)
        ->execute(array(':EnterpriseId'=>$this->app->authManager->getUserInfo()->EnterpriseId))->current()['OemId'];
        $logicpayeasy = new LogicPayeasy($this->app->dbAdapter,$this->app->logger);

        $this->view->assign( 'isPayeasyOem', $logicpayeasy->isPayeasyOem( $oemId ));
        $this->view->assign( 'taxRate', $taxRate );
        $this->view->assign( 'detailData', $row );
        $this->view->assign( 'itemList', $itemList );
        $this->view->assign( 'modifiability', $modifiability );
        $this->view->assign( 'delList', (is_null($input['deleteNum'])) ? array() : $input['deleteNum'] );

        // 加盟店が選択可能な配送方法の一覧を取得
        $this->_prepareMasterForEdit();

        return $this->view;
	}

	/**
	 * 指定注文IDのデータを注文修正向けに取得する
	 *
	 * @access protected
	 * @param int $orderId 注文ID
	 * @param null|boolean $use_seq $orderIdを注文Seqとして扱う場合はtrueを指定。省略時はfalse
	 * @return array 注文データ
	 */
	protected function _getOrderForEdit($orderId, $use_seq = false)
	{
        $prm = array();

        $sql = <<<EOQ
SELECT O.OrderSeq AS OrderSeq
,      O.ReceiptOrderDate AS ReceiptOrderDate
,      O.DataStatus AS DataStatus
,      O.EnterpriseId AS EnterpriseId
,      O.SiteId AS SiteId
,      O.OrderId AS OrderId
,      O.Ent_OrderId AS Ent_OrderId
,      O.Ent_Note AS Ent_Note
,      O.UseAmount AS UseAmount
,      O.RegistDate AS RegistDate
,      O.OutOfAmends AS OutOfAmends
,      (CASE WHEN (O.Incre_Status = 1) THEN 1 WHEN (O.Incre_Status = -(1)) THEN -(1) ELSE 0 END) AS IncreStatus
,      S.CarriageFee AS CarriageFee
,      S.ChargeFee AS ChargeFee
,      O.Chg_ExecDate AS Chg_ExecDate
,      O.Cnl_CantCancelFlg AS Cnl_CantCancelFlg
,      O.Cnl_Status AS Cnl_Status
,      O.AnotherDeliFlg AS AnotherDeliFlg
,      O.CombinedClaimTargetStatus AS CombinedClaimTargetStatus
,      O.P_OrderSeq
,      O.CombinedClaimParentFlg AS CombinedClaimParentFlg
,      O.ClaimSendingClass AS ClaimSendingClass
,      O.ServiceExpectedDate AS ServiceExpectedDate
,      C.CustomerId AS CustomerId
,      C.NameKj AS NameKj
,      C.NameKn AS NameKn
,      C.RegNameKj AS RegNameKj
,      C.PostalCode AS PostalCode
,      C.UnitingAddress AS UnitingAddress
,      C.RegUnitingAddress AS RegUnitingAddress
,      C.Phone AS Phone
,      C.MailAddress AS MailAddress
,      C.EntCustId AS EntCustId
,      S.DestNameKj AS DestNameKj
,      S.DestNameKn AS DestNameKn
,      S.DestPostalCode AS DestPostalCode
,      S.DestUnitingAddress AS DestUnitingAddress
,      S.DestPhone AS DestPhone
,      S.OrderItemId AS OrderItemId
,      S.OrderItemNames AS OrderItemNames
,      S.ItemNameKj AS ItemNameKj
,      S.ItemCount AS ItemCount
,      S.Deli_JournalIncDate AS Deli_JournalIncDate
,      S.Deli_DeliveryMethod AS Deli_DeliveryMethod
,      S.Deli_DeliveryMethodName AS Deli_DeliveryMethodName
,      S.Deli_JournalNumber AS Deli_JournalNumber
,      L.CancelDate AS CancelDate
,      L.CancelReason AS CancelReason
,      L.ApprovalDate AS ApprovalDate
,      L.CancelReasonCode AS CancelReasonCode
,      P.ExecScheduleDate AS ExecScheduleDate
,      CL.ClaimDate AS ClaimDate
,      (CASE WHEN ISNULL(O.Cnl_ReturnSaikenCancelFlg) THEN 0 ELSE O.Cnl_ReturnSaikenCancelFlg END) AS Cnl_ReturnSaikenCancelFlg,
       (CASE WHEN (O.Cnl_Status = 0) THEN 0
             WHEN ((O.Cnl_Status = 1) AND (IFNULL(O.Cnl_ReturnSaikenCancelFlg, 0) = 0)) THEN 1
             WHEN ((O.Cnl_Status = 2) AND (IFNULL(O.Cnl_ReturnSaikenCancelFlg, 0) = 0)) THEN 2
             WHEN ((O.Cnl_Status = 1) AND (O.Cnl_ReturnSaikenCancelFlg = 1)) THEN 11
             WHEN ((O.Cnl_Status = 2) AND (O.Cnl_ReturnSaikenCancelFlg = 1)) THEN 12
       END) AS RealCancelStatus
,      (CASE WHEN ((SA.AlertClass = 0) AND (SA.AlertSign = 1)) THEN 1 ELSE 0 END) AS Deli_JournalNumberAlert
,      (CASE WHEN ((SA.AlertClass = 1) AND (SA.AlertSign = 1)) THEN 1 ELSE 0 END) AS ArrivalConfirmAlert
/* 以下、追加項目(20150423_1330) */
,      O.ServiceExpectedDate AS ServiceExpectedDate /* 役務提供予定日 */
,      (SELECT KeyContent FROM M_Code WHERE CodeId = 92 AND KeyCode = O.PendingReasonCode) AS PendingReason /* 保留理由 */
,      O.CreditReplyDate AS CreditReplyDate /* 与信返信時間 */
,      O.T_OrderClass
,      (CASE WHEN O.T_OrderClass = 1 THEN '1：テスト注文' ELSE '0：通常注文' END) AS OrderClassStr
,      C.Occupation AS Occupation /* 職業 */
,      C.CorporateName AS CorporateName /* 法人名 */
,      C.DivisionName AS DivisionName /* 部署名 */
,      C.CpNameKj AS CpNameKj /* 担当者名 */
,      O.ApiUserId AS ApiUserId /* APIユーザー */
,      O.ServiceExpectedDate AS OrgServiceExpectedDate /* (Org)役務提供予定日 */
,      ATO.CreditTransferRequestFlg /* 口座振替 */
FROM   T_Order O
       STRAIGHT_JOIN T_Customer C ON (C.OrderSeq = O.OrderSeq)
       STRAIGHT_JOIN T_OrderSummary S ON (S.OrderSeq = O.OrderSeq)
       LEFT OUTER JOIN T_Cancel L ON (L.OrderSeq = O.OrderSeq AND L.ValidFlg = 1)
       LEFT OUTER JOIN T_PayingControl P ON (P.Seq = O.Chg_Seq)
       LEFT OUTER JOIN T_ClaimControl CL ON (CL.OrderSeq = O.OrderSeq)
       LEFT OUTER JOIN T_StagnationAlert SA ON (SA.OrderSeq = O.Chg_Seq)
       STRAIGHT_JOIN AT_Order ATO ON (ATO.OrderSeq = O.OrderSeq)
EOQ;
        $sql .= " WHERE  O.EnterpriseId = :EnterpriseId ";
        $prm += array(':EnterpriseId' => $this->app->authManager->getUserInfo()->EnterpriseId);

        if($use_seq) {
            // シーケンスで問い合わせ
            $sql .= " AND  O.OrderSeq = :OrderSeq ";
            $prm += array(':OrderSeq' => strtoupper($orderId));
        } else {
            // 注文IDで問い合わせ
            $sql .= " AND  O.OrderId = :OrderId ";
            $prm += array(':OrderId' => strtoupper($orderId));
        }

        $stm = $this->app->dbAdapter->query($sql);

        $row = $stm->execute($prm)->current();

        return ($row) ? $row : null;
	}

	/**
	 * 指定注文シーケンスに関連付けられた注文商品データを取得する
	 * @access protected
	 * @param int $order_seq 注文シーケンス
	 * @return array
	 */
	protected function _getOrderItemsForEdit($order_seq)
	{
        $itemTable = new TableOrderItems($this->app->dbAdapter);
        $ri = $itemTable->findByOrderSeq($order_seq);
        return ResultInterfaceToArray($ri);
	}

	/**
	 * 注文修正向けに現在のビューにマスターデータをアサインする
	 * @access protected
	 */
	protected function _prepareMasterForEdit()
	{
        // 配送先マスタの取得
        $delilogic = new \models\Logic\LogicDeliveryMethod($this->app->dbAdapter);
	    $deliv_masters = $delilogic->getEnterpriseDeliveryMethodList($this->app->authManager->getUserInfo()->EnterpriseId, false);

        // 配送先マスタをビューへ登録
        $this->view->assign( 'deliv_masters', $deliv_masters );
	}

	/**
	 * 注文修正向けに現在のビューにマスターデータをアサインする
	 * (加盟店が選択可能な配送方法の一覧を取得)
	 *
	 * @access protected
	 */
	protected function _prepareMasterForEdit2()
	{
        $sql = <<<EOQ
SELECT d.*
FROM   M_DeliveryMethod d
       INNER JOIN T_EnterpriseDelivMethod e ON d.DeliMethodId = e.DeliMethodId
WHERE  e.EnterpriseId = :EnterpriseId
AND    d.ValidFlg = 1
AND    e.ValidFlg = 1
ORDER BY e.ListNumber, d.ListNumber
EOQ;

        $deliv_masters = array();
        $ri = $this->app->dbAdapter->query($sql)->execute(array(':EnterpriseId' => $this->app->authManager->getUserInfo()->EnterpriseId));
        foreach($ri as $row) {
            $deliv_masters[$row['DeliMethodId']] = $row['DeliMethodName'];
        }

        // 配送先マスタをビューへ登録
        $this->view->assign( 'deliv_masters', $deliv_masters );
	}

	/**
	 * T_Order.OrderSeq, T_Order.OrderId, T_Customer.CustomerId からハッシュコードを作成する
	 * @access protected
	 * @param int|string $order_seq 注文シーケンス
	 * @param string $order_id 注文ID
	 * @param int|string $customer_id 購入者ID
	 * @return string パラメータから算出したMD5ハッシュ値
	 */
	protected function _calcOrderIdHash($order_seq, $order_id, $customer_id)
	{
	    return md5(base64_encode(sprintf('%s:%s:%s', $order_seq, $order_id, $customer_id)));
	}

    /**
     * editconfirmアクション。注文修正内容の確認画面を表示する
     */
    public function editconfirmAction()
    {
        $editor = new OrderEditor($this->app->dbAdapter);

        $params = $this->params()->fromPost();
        $input = isset($params['form']) ? $params['form'] : array();
        $input = $editor->fixInputParams($input);

        // 削除対象のOrderItemIdを取得
        $deleteNumbers = array();
        foreach ($params as $key => $param) {
            if( strstr( $key, 'item_delete_chk_' ) != false ) {
                $deleteNumbers[] = str_replace( 'item_delete_chk_', '', $key );
            }
        }
        $input['deleteNum'] = $deleteNumbers;

        // 元の注文データを取得しておく
        $order = $this->_getOrderForEdit($input['Order']['OrderSeq'], true);

        // シーケンス指定の整合性チェック
        if($this->_calcOrderIdHash($order['OrderSeq'], $order['OrderId'], $order['CustomerId']) != $params['verifyKey']) {
            $this->view->assign('errors', array('' => array('0' => 'シーケンス整合性エラー')));
            return $this->_forward('edit', array('id' => $order['OrderId']));
        }

        // グループ単位での修正可能性情報を取得
        $modifiability = $editor->judgeOrderModifiability($input['Order']['OrderSeq']);

        // サイトデータを取得
        $siteInfo = $modifiability[OrderEditor::GROUP_ORDER] ?
            $this->_getSiteInfo($input['Order']['SiteId']) :		// 注文基本情報が修正可能なら入力からサイト情報取得
            $this->_getSiteInfo($order['SiteId']);					// 注文情報修正不可時は現在の登録内容からサイト情報取得

        //電話番号とメールアドレスを半角に変換
        if (isset($input['Customer']['Phone'])) { $input['Customer']['Phone'] = BaseGeneralUtils::convertWideToNarrow( $input['Customer']['Phone'] ); }

        //事業者の口座振替利用を設定
        $input['Customer']['CreditTransferFlg'] = $this->creditTransferFlg;

        //ペイジー検証用にOemIdを追加
        $sql = " SELECT OemId FROM T_Enterprise WHERE EnterpriseId = :EnterpriseId ";
        $oemId = $this->app->dbAdapter->query($sql)
            ->execute(array(':EnterpriseId'=>$this->app->authManager->getUserInfo()->EnterpriseId))->current()['OemId'];

        $logicpayeasy = new LogicPayeasy($this->app->dbAdapter,$this->app->logger);
        $input['Order']['PayeasyFlg'] = $logicpayeasy->isPayeasyOem($oemId);

        $mail_required = $siteInfo ? $siteInfo['ReqMailAddrFlg'] : false;
        $validate_results = $editor->validateForEdit($input, $mail_required, $oemId);

        if ($siteInfo['ServiceTargetClass'] != 1) {
            // 役務対象サイトでの注文でない場合は[役務提供予定日]をヌル化
            $input['Order']['ServiceExpectedDate'] = "";
        }

        $errors = $this->_convertValidateMessages($validate_results);

        // サイトIDの正常性検査
        if(!$siteInfo) {
            $siteError = $errors['Order.SiteId'];
            if(! is_array($siteError)) $siteError = array();
            $siteError[] = "'受付サイト'の指定が不正です";
            $errors['Order.SiteId'] = $siteError;
        }

        // postされたデータを入力フォーム用に再割り当てする
        // → ついでに再与信対象項目の変化もチェック（再与信となるかのチェックは永続化前にも行う！）
        $has_changed = array(
                'Customer' => false,
                'Destination' => false,
                'Items' => false
        );

//        //正規化する際のfilter
//        $map = array(
//                'NameKj' => LogicNormalizer::FILTER_FOR_NAME,
//                'UnitingAddress' => LogicNormalizer::FILTER_FOR_ADDRESS,
//                'Phone' => LogicNormalizer::FILTER_FOR_TEL,
//                'MailAddress' => LogicNormalizer::FILTER_FOR_MAIL,
//                'DestNameKj' => LogicNormalizer::FILTER_FOR_NAME,
//                'DestUnitingAddress' => LogicNormalizer::FILTER_FOR_ADDRESS,
//                'DestPhone' => LogicNormalizer::FILTER_FOR_TEL,
//        );
        if($modifiability[OrderEditor::GROUP_ORDER]) {
            foreach($input['Order'] as $key => $value) {
                if(in_array($key, array('OrderSeq', 'OrderId'))) continue;
                $order[$key] = $value;
            }
        }
        if($modifiability[OrderEditor::GROUP_CUSTOMER]) {
            foreach($input['Customer'] as $key => $value) {
//                            //正規化
//                if(in_array($key,array_keys($map))){
//                    $regOrderVal = LogicNormalizer::create($map[$key])->normalize($order[$key]);
//                    $regInputVal = LogicNormalizer::create($map[$key])->normalize($value);
//                } else {
//                    $regOrderVal = $order[$key];
//                    $regInputVal = $value;
//                }
                // 再与信となるかをチェック
                if(in_array($key, array('NameKj', 'PostalCode', 'UnitingAddress', 'Phone'))) {
//                   if($regOrderVal != $regInputVal) $has_changed['Customer'] = true;
                    $ordernamekj = LogicNormalizer::create( LogicNormalizer::FILTER_FOR_NAME )->normalize( $order['NameKj'] );
                    $valuenamekj = LogicNormalizer::create( LogicNormalizer::FILTER_FOR_NAME )->normalize( $input['Customer']["NameKj"] );
                    $orderpostalcode = LogicNormalizer::create( LogicNormalizer::FILTER_FOR_POSTALCODE )->normalize( $order['PostalCode'] );
                    $valuepostalcode = LogicNormalizer::create( LogicNormalizer::FILTER_FOR_POSTALCODE )->normalize( $input['Customer']["PostalCode"] );
                    $orderunitingaddress = LogicNormalizer::create( LogicNormalizer::FILTER_FOR_ADDRESS )->normalize( $order['UnitingAddress'] );
                    $valueunitingaddress = LogicNormalizer::create( LogicNormalizer::FILTER_FOR_ADDRESS )->normalize( $input['Customer']["UnitingAddress"] );
                    $orderphone = LogicNormalizer::create( LogicNormalizer::FILTER_FOR_TEL )->normalize( $order['Phone'] );
                    $valuephone = LogicNormalizer::create( LogicNormalizer::FILTER_FOR_TEL )->normalize( $input['Customer']["Phone"] );
                    if($ordernamekj != $valuenamekj) $has_changed['Customer'] = true;
                    elseif($orderpostalcode != $valuepostalcode) $has_changed['Customer'] = true;
                    elseif($orderunitingaddress != $valueunitingaddress) $has_changed['Customer'] = true;
                    elseif($orderphone != $valuephone) $has_changed['Customer'] = true;
                } else
                    if($mail_required && $key == 'MailAddress') {
//                        $regOrderVal = LogicNormalizer::create($map[$key])->normalize($order[$key]);
//                        $regInputVal = LogicNormalizer::create($map[$key])->normalize($value);
//                        if($regOrderVal != $regInputVal) $has_changed['Customer'] = true;
                        if($order[$key] != $value) $has_changed['Customer'] = true;
                    }
                $order[$key] = $value;
            }
        }
        if($modifiability[OrderEditor::GROUP_DELIVERY]) {
            foreach($input['Destination'] as $key => $value) {
                if(in_array($key, array('UnitingAddress', 'PostalCode', 'Phone'))) {
                    $key = 'Dest' . $key;
                }
//                //正規化
//                if(in_array($key,array_keys($map))){
//                    $regOrderVal = LogicNormalizer::create($map[$key])->normalize($order[$key]);
//                    $regInputVal = LogicNormalizer::create($map[$key])->normalize($value);
//                } else {
//                    $regOrderVal = $order[$key];
//                    $regInputVal = $value;
//                }
                // 再与信となるかをチェック
                if(in_array($key, array('DestNameKj', 'DestPostalCode', 'DestUnitingAddress', 'DestPhone'))) {
//                    if($regOrderVal != $regInputVal) $has_changed['Destination'] = true;
                    $orderDestNameKj = LogicNormalizer::create( LogicNormalizer::FILTER_FOR_NAME )->normalize( $order['DestNameKj'] );
                    $valueDestNameKj = LogicNormalizer::create( LogicNormalizer::FILTER_FOR_NAME )->normalize( $input['Destination']["DestNameKj"] );
                    $orderDestPostalCode = LogicNormalizer::create( LogicNormalizer::FILTER_FOR_POSTALCODE )->normalize( $order['DestPostalCode'] );
                    $valueDestPostalCode = LogicNormalizer::create( LogicNormalizer::FILTER_FOR_POSTALCODE )->normalize( $input['Destination']["PostalCode"] );
                    $orderDestUnitingAddress = LogicNormalizer::create( LogicNormalizer::FILTER_FOR_ADDRESS )->normalize( $order['DestUnitingAddress'] );
                    $valueDestUnitingAddress = LogicNormalizer::create( LogicNormalizer::FILTER_FOR_ADDRESS )->normalize( $input['Destination']["UnitingAddress"] );
                    $orderDestPhone = LogicNormalizer::create( LogicNormalizer::FILTER_FOR_TEL )->normalize( $order['DestPhone'] );
                    $valueDestPhone = LogicNormalizer::create( LogicNormalizer::FILTER_FOR_TEL )->normalize( $input['Destination']["Phone"] );
                    if($orderDestNameKj != $valueDestNameKj) $has_changed['Customer'] = true;
                    elseif($orderDestPostalCode != $valueDestPostalCode) $has_changed['Customer'] = true;
                    elseif($orderDestUnitingAddress != $valueDestUnitingAddress) $has_changed['Customer'] = true;
                    elseif($orderDestPhone != $valueDestPhone) $has_changed['Customer'] = true;
                }
                $order[$key] = $value;
            }
        }
        if($modifiability[OrderEditor::GROUP_JOURNAL]) {
            foreach($input['Journal'] as $key => $value) {
                $order[$key] = $value;
            }
        }
//        //ハイフンの正規化
//        $lnzh = new LogicNormalizerZenHyphens();
//        $lnzhc = new LogicNormalizerZenHyphenCompaction ();
//        if($modifiability[OrderEditor::GROUP_CUSTOMER]) {
//            foreach( array('NameKj', 'UnitingAddress') as $key) {
//                if(isset($input['Customer'][$key])) {
//                    $input['Customer'][$key] = $lnzhc->normalize($lnzh->normalize($input['Customer'][$key]));
//                }
//                $order[$key] = $input['Customer'][$key];
//            }
//        }
        if($modifiability[OrderEditor::GROUP_DELIVERY]) {
            $order_keys = array('DestNameKj' => 'DestNameKj','UnitingAddress' => 'DestUnitingAddress');
            foreach(array('DestNameKj', 'UnitingAddress') as $key) {
//                if(isset($input['Destination'][$key])) {
//                    $input['Destination'][$key] = $lnzhc->normalize($lnzh->normalize($input['Destination'][$key]));
//                }
                $order[$order_keys[$key]] = $input['Destination'][$key];
            }
        }
        $this->view->assign('detailData', $order);
        // 商品編集が可能である場合は[$input['Items']]を、そうでない時は検索結果をアサイン(20150803)
        $items = ($modifiability[OrderEditor::GROUP_ITEMS]) ? $input['Items'] : $this->_getOrderItemsForEdit($input['Order']['OrderSeq']);

        if($modifiability[OrderEditor::GROUP_ITEMS]) {
            $summary = 0;
            foreach($input['Items'] as $i => $item_info) {
                if ($item_info['DataClass'] != '1') {
                    // 商品でない[送料,手数料,外税]時は、[UnitPrice]を加算のみする
                    $summary += (int)$item_info['UnitPrice'];
                    $items[$i]['SumMoney'] = (int)$item_info['UnitPrice'];
                }
                else {
                    // 商品の時は、[利用額端数計算設定]に従った算出結果を加算する
                    // 但し、削除対象は加算しない
                    $lineSummary = ((int)$item_info['UnitPrice'] * (double)$item_info['ItemNum']);
                    if ((int)$this->app->authManager->getUserInfo()->UseAmountFractionClass == 0) { $lineSummary = floor( $lineSummary ); }
                    if ((int)$this->app->authManager->getUserInfo()->UseAmountFractionClass == 1) { $lineSummary = round( $lineSummary ); }
                    if ((int)$this->app->authManager->getUserInfo()->UseAmountFractionClass == 2) { $lineSummary = ceil(  $lineSummary ); }

                    $items[$i]['SumMoney'] = (int)$lineSummary;
                    if ($item_info['OrderItemId'] == 'a' || !in_array($item_info['OrderItemId'],$deleteNumbers)) {
                        $summary += (int)$lineSummary;
                    }
                }
            }

            // 合計金額が3,000以上増加していたら再与信
            $ohis = new TableOrderHistory($this->app->dbAdapter);
            $lastCreditJudgeAmount = $ohis->getLastCreditJudgeAmount($input['Order']['OrderSeq']);
            if (is_null($lastCreditJudgeAmount)) {
                // 過去に与信金額がない場合は現在の金額で判定
                $lastCreditJudgeAmount = $order['UseAmount'];
            }
            if($summary - $lastCreditJudgeAmount > 2999) {
                $has_changed['Items'] = true;
            }
            $order['UseAmount'] = $summary;
            $this->view->assign('detailData', $order);
        }
        // 消費税率の設定
        $propertyTable = new TableSystemProperty($this->app->dbAdapter);
        $taxRate = $propertyTable->getTaxRateAt(date('Y-m-d'));

        $this->view->assign( 'taxRate', $taxRate );
        $this->view->assign('itemList', $items);
        $this->view->assign('delList', $deleteNumbers);
        $this->view->assign('hasChanged', $has_changed);

        // 加盟店が選択可能な配送方法の一覧を取得
        $this->_prepareMasterForEdit();

        // 検証エラーがあった場合
        if(!empty($errors)) {
            $this->view->assign('errors', $errors);
            $this->view->assign('verifyKey', $params['verifyKey']);	// シーケンスハッシュはそのまま引き継ぐ
            $this->view->assign('modifiability', $modifiability);

            $this->setTemplate('edit');
            return $this->view;
        }

        // post内容をシリアライズ＋base64エンコードしてビューに割り当てる
        $this->view->assign('confirmedData', base64_encode(serialize($input)));
        // 同時に配列内容のハッシュを取る
        $this->view->assign('verifyKey', md5(serialize($input)));

        return $this->view;
    }

	/**
	 * 指定サイトIDに一致するサイトデータを取得する
	 * @access protected
	 * @param int $siteId サイトID
	 * @return array
	 */
	protected function _getSiteInfo($siteId)
	{
	    foreach($this->_getSitesList() as $row) {
			if($row['SiteId'] == $siteId) return $row;
		}
		return null;
	}

	/**
	 * このアカウントに関連付けられたサイト情報をすべて取得する
	 * @access protected
	 * @return array
	 */
	protected function _getSitesList()
	{
	    return $this->view->site_list;
	}

	/**
	 * OrderEditor::validateForEdit()の検証結果配列を、キーをHTML要素IDとする連想配列に組み替える
	 * @access protected
	 * @param array $messages OrderEditor::validateForEdit()による検証結果の配列
	 * @return array
	 */
	protected function _convertValidateMessages($messages)
	{
	    $result = array();
		foreach($messages as $group => $result_list) {
			if($group == 'Items') {
				foreach($result_list as $index => $sub_list) {
					foreach($sub_list as $key => $msg_list) {
						$result[sprintf('%s.%s_%s', $group, $key, $index)] = $msg_list;
					}
				}
			} else {
				foreach($result_list as $key => $msg_list) {
					$result[sprintf('%s.%s', $group, $key)] = $msg_list;
				}
			}
		}
		return $result;
	}

    /**
     * editdoneアクション。注文修正処理を完了させる
     */
    public function editdoneAction()
    {
        $params = $this->params()->fromPost();

        // 確定データを復元
        $input = unserialize(base64_decode($params['confirmedData']));
        if(! $input)
            throw new \Exception('invalid request');

        // ハッシュデータと突合
        $verifyKey = $params['verifyKey'];
        if(! $verifyKey || $verifyKey != md5(serialize($input)))
            throw new \Exception('invalid request.(hash data unmatch)');

        // 対象シーケンス確定
        $order_seq = $input['Order']['OrderSeq'];

        // 再与信判断用に元の注文データを取得しておく
        $order = $this->_getOrderForEdit($order_seq, true);
        if(! $order)
            throw new \Exception('invalid request.(invalid sequence specified)');

        $order_id = $order['OrderId'];

        // グループ単位での修正可能性情報を取得
        $editor = new OrderEditor($this->app->dbAdapter);
        $modifiability = $editor->judgeOrderModifiability($order_seq);

        // メールアドレス必須判断用にサイトデータを取得
        $siteInfo = $modifiability[OrderEditor::GROUP_ORDER] ?
            $this->_getSiteInfo($input['Order']['SiteId']) :		// 注文基本情報が修正可能なら入力からサイト情報取得
            $this->_getSiteInfo($order['SiteId']);					// 注文情報修正不可時は現在の登録内容からサイト情報取得
        $mail_required = $siteInfo ? $siteInfo['ReqMailAddrFlg'] : false;

        // 再与信となるかどうか、変更をチェック
        $has_changed = false;
        $change_part = array();

//        //正規化する際のfilter
//        $map = array(
//                'NameKj' => LogicNormalizer::FILTER_FOR_NAME,
//                'UnitingAddress' => LogicNormalizer::FILTER_FOR_ADDRESS,
//                'Phone' => LogicNormalizer::FILTER_FOR_TEL,
//                'MailAddress' => LogicNormalizer::FILTER_FOR_MAIL,
//                'DestNameKj' => LogicNormalizer::FILTER_FOR_NAME,
//                'DestUnitingAddress' => LogicNormalizer::FILTER_FOR_ADDRESS,
//                'DestPhone' => LogicNormalizer::FILTER_FOR_TEL,
//        );
        if($modifiability[OrderEditor::GROUP_CUSTOMER]) {
            // 購入者情報チェック
            $ordernamekj = LogicNormalizer::create( LogicNormalizer::FILTER_FOR_NAME )->normalize( $order['NameKj'] );
            $valuenamekj = LogicNormalizer::create( LogicNormalizer::FILTER_FOR_NAME )->normalize( $input['Customer']["NameKj"] );
            $orderpostalcode = LogicNormalizer::create( LogicNormalizer::FILTER_FOR_POSTALCODE )->normalize( $order['PostalCode'] );
            $valuepostalcode = LogicNormalizer::create( LogicNormalizer::FILTER_FOR_POSTALCODE )->normalize( $input['Customer']["PostalCode"] );
            $orderunitingaddress = LogicNormalizer::create( LogicNormalizer::FILTER_FOR_ADDRESS )->normalize( $order['UnitingAddress'] );
            $valueunitingaddress = LogicNormalizer::create( LogicNormalizer::FILTER_FOR_ADDRESS )->normalize( $input['Customer']["UnitingAddress"] );
            $orderphone = LogicNormalizer::create( LogicNormalizer::FILTER_FOR_TEL )->normalize( $order['Phone'] );
            $valuephone = LogicNormalizer::create( LogicNormalizer::FILTER_FOR_TEL )->normalize( $input['Customer']["Phone"] );
            $ordermail = LogicNormalizer::create( LogicNormalizer::FILTER_FOR_MAIL )->normalize( $order['MailAddress'] );
            $valuemail = LogicNormalizer::create( LogicNormalizer::FILTER_FOR_MAIL )->normalize( $input['Customer']["MailAddress"] );
            if($ordernamekj != $valuenamekj){
                $has_changed = true;
                $change_part['Customer'] = true;
            } elseif($orderpostalcode != $valuepostalcode){
                $has_changed = true;
                $change_part['Customer'] = true;
            } elseif($orderunitingaddress != $valueunitingaddress){
                $has_changed = true;
                $change_part['Customer'] = true;
            } elseif($orderphone != $valuephone){
                $has_changed = true;
                $change_part['Customer'] = true;
            } elseif($ordermail != $valuemail){
                $has_changed = true;
                $change_part['Customer'] = true;
            }
//            foreach(array('NameKj', 'PostalCode', 'UnitingAddress', 'Phone', 'MailAddress') as $key) {
//                if($key == 'MailAddress' && !$mail_required) continue;
//                //正規化
//                if(in_array($key,array_keys($map))){
//                    $regOrderVal = LogicNormalizer::create($map[$key])->normalize($order[$key]);
//                    $regInputVal = LogicNormalizer::create($map[$key])->normalize($input['Customer'][$key]);
//                } else {
//                    $regOrderVal = $order[$key];
//                    $regInputVal = $input['Customer'][$key];
//                }
                //正規化後の値を比較
//                if($regOrderVal != $regInputVal) {
//                if($order[$key] != $input['Customer'][$key]) {
//                    $has_changed = true;
//                    $change_part['Customer'] = true;
//                }
//            }
        }
        if($modifiability[OrderEditor::GROUP_DELIVERY]) {
            // 配送先情報チェック
            $orderDestNameKj = LogicNormalizer::create( LogicNormalizer::FILTER_FOR_NAME )->normalize( $order['DestNameKj'] );
            $valueDestNameKj = LogicNormalizer::create( LogicNormalizer::FILTER_FOR_NAME )->normalize( $input['Destination']["DestNameKj"] );
            $orderDestPostalCode = LogicNormalizer::create( LogicNormalizer::FILTER_FOR_POSTALCODE )->normalize( $order['DestPostalCode'] );
            $valueDestPostalCode = LogicNormalizer::create( LogicNormalizer::FILTER_FOR_POSTALCODE )->normalize( $input['Destination']["PostalCode"] );
            $orderDestUnitingAddress = LogicNormalizer::create( LogicNormalizer::FILTER_FOR_ADDRESS )->normalize( $order['DestUnitingAddress'] );
            $valueDestUnitingAddress = LogicNormalizer::create( LogicNormalizer::FILTER_FOR_ADDRESS )->normalize( $input['Destination']["UnitingAddress"] );
            $orderDestPhone = LogicNormalizer::create( LogicNormalizer::FILTER_FOR_TEL )->normalize( $order['DestPhone'] );
            $valueDestPhone = LogicNormalizer::create( LogicNormalizer::FILTER_FOR_TEL )->normalize( $input['Destination']["Phone"] );
            if($orderDestNameKj != $valueDestNameKj){
                $has_changed = true;
                $change_part['Destination'] = true;
            } elseif($orderDestPostalCode != $valueDestPostalCode){
                $has_changed = true;
                $change_part['Destination'] = true;
            } elseif($orderDestUnitingAddress != $valueDestUnitingAddress){
                $has_changed = true;
                $change_part['Destination'] = true;
            } elseif($orderDestPhone != $valueDestPhone){
                $has_changed = true;
                $change_part['Destination'] = true;
            }
//            foreach(array('DestNameKj', 'DestPostalCode', 'DestUnitingAddress', 'DestPhone') as $key) {
//                $sub_key = (!in_array($key, array('DestNameKj', 'DestNameKn'))) ? preg_replace('/^Dest/', '', $key) : $key;
//                //正規化
//                if(in_array($key,array_keys($map))){
//                    $regOrderVal = LogicNormalizer::create($map[$key])->normalize($order[$key]);
//                    $regInputVal = LogicNormalizer::create($map[$key])->normalize($input['Destination'][$sub_key]);
//                } else {
//                    $regOrderVal = $order[$key];
//                    $regInputVal = $input['Destination'][$sub_key];
//                }
                //正規化後の値を比較
//                if($regOrderVal != $regInputVal) {
//                if($order[$key] != $input['Destination'][$sub_key]) {
//                    $has_changed = true;
//                    $change_part['Destination'] = true;
//                }
//            }
        }
        if($modifiability[OrderEditor::GROUP_ITEMS]) {
            $sum = 0;
            foreach($input['Items'] as $item_info) {
                // 更新実行フラグ
                $updateFlg = true;
                // 削除対象の商品明細の金額は加算しない
                foreach ($input['deleteNum'] as $i => $delNumber) {
                    if ($item_info['OrderItemId'] == $delNumber) {
                        $updateFlg = false;
                        break;
                    }
                }
                if ($updateFlg) {
                    $sumMoney = $item_info['UnitPrice'] * (($item_info['DataClass'] == 1) ? (double)$item_info['ItemNum'] : 1);
                    if ((int)$this->app->authManager->getUserInfo()->UseAmountFractionClass == 0) { $sumMoney = floor( $sumMoney ); }
                    if ((int)$this->app->authManager->getUserInfo()->UseAmountFractionClass == 1) { $sumMoney = round( $sumMoney ); }
                    if ((int)$this->app->authManager->getUserInfo()->UseAmountFractionClass == 2) { $sumMoney = ceil(  $sumMoney ); }
                    $sum += $sumMoney;
                }
            }
            // 合計金額が3,000以上増加していたら再与信
            $ohis = new TableOrderHistory($this->app->dbAdapter);
            $lastCreditJudgeAmount = $ohis->getLastCreditJudgeAmount($order_seq);
            if (is_null($lastCreditJudgeAmount)) {
                // 過去に与信金額がない場合は現在の金額で判定
                $lastCreditJudgeAmount = $order['UseAmount'];
            }
            if($sum - $lastCreditJudgeAmount > 2999) {
                $has_changed = true;
                $change_part['Items'] = true;
            }
            // 合算したUseAmountを$input[Order]に反映
            $input['Order']['UseAmount'] = $sum;
        }

        // ユーザーIDの取得
        $obj = new \models\Table\TableUser($this->app->dbAdapter);
        getUserInfoForMember($this->app, $userClass, $seq);
        $userId = $obj->getUserId($userClass, $seq);

        // 永続化処理
        $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
        try {
            $order_table = new TableOrder($this->app->dbAdapter);
            $customer_table = new TableCustomer($this->app->dbAdapter);
            $delidest_table = new TableDeliveryDestination($this->app->dbAdapter);
            $items_table = new TableOrderItems($this->app->dbAdapter);
            $summary_table = new TableOrderSummary($this->app->dbAdapter);
            $postalcodeTable = new TablePostalCode( $this->app->dbAdapter );
            $mghelper = new LogicMergeOrderHelper($this->app->dbAdapter, $order_seq);

            $u_order = $order_table->find($order_seq)->current();
            $u_customer = $customer_table->findCustomer(array('OrderSeq' => $order_seq))->current();
            $u_items_list = $items_table->findByOrderSeq($order_seq);

            $sql = " select * from T_DeliveryDestination where ValidFlg = 1 AND DeliDestId in (select DeliDestId from T_OrderItems where OrderSeq = :OrderSeq) ";
            $stm = $this->app->dbAdapter->query($sql);
            $ri = $stm->execute(array(':OrderSeq' => $order_seq));
            $u_deliv_list = null;
            if ($ri->count() > 0) {
                $rs = new ResultSet();
                $u_deliv_list = $rs->initialize($ri)->toArray();
            }

            // 再与信対象なら、T_Orderの不要フィールドはクリアする
            if($has_changed) {
                $u_order = $this->_cleanupOrderForReCredit($u_order);
                $u_order['DataStatus'] = 11;		// 社内与信実行待ちに戻す
                // 備考にメッセージ追加
                $u_order['Incre_Note'] = sprintf("(事業者側修正により再与信戻し[%s])\n----\n%s",  date('Y-m-d H:i:s'), $u_order['Incre_Note']);

                $u_order['DailySummaryFlg'] = 0;
                $u_order['PendingReasonCode'] = null;
                $u_order['CreditReplyDate'] = null;
                $u_order['OemClaimTransDate'] = null;
                $u_order['CreditNgHiddenFlg'] = 0;
            }

            // 役務対象区分
            $u_order['ServiceTargetClass'] = $siteInfo['ServiceTargetClass'];

            // 注文基本情報の更新
            if($modifiability[OrderEditor::GROUP_ORDER]) {
                foreach($input['Order'] as $key => $value) {
                    if(in_array($key, array('OrderSeq', 'OrderId'))) continue;
                    $u_order[$key] = $value;
                }
            }

            // 役務提供予定日が空の場合、NULLに値を挿げ替える
            $u_order['ServiceExpectedDate'] = (($u_order['ServiceExpectedDate'] == '') ? null : $u_order['ServiceExpectedDate']);

            // 請求書送付区分
            if (isset($input['Order']['SiteId'])) {
                $u_order['ClaimSendingClass'] = $input['Customer']['ClaimSendingClass']; // Ex.. 11 or 12
                $selfBillingFlg = $this->app->dbAdapter->query(" SELECT SelfBillingFlg FROM T_Site WHERE SiteId = " . $input['Order']['SiteId'])->execute(null)->current()['SelfBillingFlg'];
                $u_order['ClaimSendingClass'] = ($selfBillingFlg == 0) ? 21 : $u_order['ClaimSendingClass'];
            }

            // UseAmountの適用
            if(isset($input['Order']['UseAmount'])) $u_order['UseAmount'] = $input['Order']['UseAmount'];
            $u_order['UpdateId'] = $userId;  // 更新者
            $order_table->saveUpdate($u_order, $order_seq);

            // 購入者情報の更新
            if($modifiability[OrderEditor::GROUP_CUSTOMER]) {
                foreach($input['Customer'] as $key => $value) {
                    $u_customer[$key] = $value;
                }
            }
            $u_customer['AddressKn'] = $postalcodeTable->getAddressKanaStr($u_customer['PostalCode']);// 住所カナ
            $u_customer['UpdateId'] = $userId;  // 更新者
            $customer_table->saveUpdate($u_customer, $u_customer['CustomerId']);

            // 配送先情報の更新
            if($modifiability[OrderEditor::GROUP_DELIVERY] && $u_deliv_list) {
                foreach($u_deliv_list as $u_deliv) {
                    if($u_order['AnotherDeliFlg']) {
                        // 別配送先指定あり
                        foreach($input['Destination'] as $key => $value) {
                            $u_deliv[$key] = $value;
                        }
                    } else {
                        // 請求先に同じ
                        foreach(array('NameKj', 'NameKn', 'PostalCode', 'UnitingAddress', 'Phone', 'PrefectureCode', 'PrefectureName') as $key) {
                            $sub_key = ($key == 'NameKj' || $key == 'NameKn') ? ('Dest' . $key) : $key;
                            $u_deliv[$sub_key] = $u_customer[$key];
                        }
                    }
                    $u_deliv['UpdateId'] = $userId;  // 更新者
                    $delidest_table->saveUpdate($u_deliv, $u_deliv['DeliDestId']);
                }
            }

            // 最新配送先情報の準備(※追加行時に使用する)
            $deliv_for_new_rec = $this->app->dbAdapter->query(
                " select * from T_DeliveryDestination where ValidFlg = 1 AND DeliDestId in (select DeliDestId from T_OrderItems where OrderSeq = :OrderSeq) "
                )->execute(array(':OrderSeq' => $order_seq))->current();

            // 別配送先フラグがONの状態でも、購入者情報と配送先情報の以下項目が全て一致する場合、別配送先フラグを0に落とす
            // 氏名、氏名カナ、郵便番号、都道府県コード、結合住所、電話番号
            if ($u_order['AnotherDeliFlg'] == 1 &&
                (($u_customer['NameKj'] == $u_deliv['DestNameKj']) && ($u_customer['NameKn'] == $u_deliv['DestNameKn']) &&
                 ($u_customer['PostalCode'] == $u_deliv['PostalCode']) && ($u_customer['PrefectureCode'] == $u_deliv['PrefectureCode']) &&
                 ($u_customer['UnitingAddress'] == $u_deliv['UnitingAddress']) && ($u_customer['Phone'] == $u_deliv['Phone']))) {
                // 別配送先フラグを 0 で更新
                $order_table->saveUpdate(array('AnotherDeliFlg' => 0, 'UpdateId' => $userId), $order_seq);
            }

            // 注文商品/配送伝票の更新
            if($modifiability[OrderEditor::GROUP_ITEMS]) {
                // 注文商品／配送情報の論理削除
                $delItemList = array(); // 削除対象のリスト
                foreach ($input['deleteNum'] as $i => $delNumber) {
                    // 該当の注文商品の配送先IDを取得する
                    $sql = <<<EOQ
                        SELECT  DeliDestId
                        FROM    T_OrderItems
                        WHERE   OrderItemId = :OrderItemId
                        ;
EOQ;
                    $data = $this->app->dbAdapter->query($sql)->execute(array( ':OrderItemId' => $delNumber ))->current();
                    $deliDestId = $data['DeliDestId'];

                    // 配送先情報の論理削除
                    $delidest_table->saveUpdate(array('ValidFlg' => 0, 'UpdateId' => $userId), $deliDestId);

                    // 注文商品データの論理削除
                    $items_table->saveUpdate(array('ValidFlg' => 0, 'UpdateId' => $userId), $delNumber);

                    $delItemList[] = $delNumber;
                }
                foreach ($input['Items'] as $item_data) {

                    if (!in_array($item_data, $delItemList)) {

                        // OrderItemIdが数値に変換できるなら既存行
                        if (is_numeric($item_data['OrderItemId'])) {

                            if (isset($u_item)) {
                                unset($u_item);
                            }
                            if ($item_data['DataClass'] == 1) {
                                // 商品明細行の場合は商品名と数量を上書き
                                $u_item['ItemNameKj'] = $item_data['ItemNameKj'];
                                $u_item['ItemNum'] = $item_data['ItemNum'];
                                // 消費税率
                                if( $item_data ['TaxrateNotsetFlg'] == 1 && $item_data['TaxRate'] == NULL ) {
                                    // 消費税率の設定
                                    $propertyTable = new TableSystemProperty($this->app->dbAdapter);
                                    $item_data['TaxRate'] = $propertyTable->getTaxRateAt(date('Y-m-d'));
                                }
                                $u_item['TaxRate'] = $item_data['TaxRate'];
                                $u_item['TaxrateNotsetFlg'] = 0;
                            } else {
                                // 送料／手数料／外税額は（念のため）数量を1で上書き
                                $u_item['ItemNum'] = 1;
                            }
                            // 単価
                            $u_item['UnitPrice'] = (int)$item_data['UnitPrice'];
                            // 金額を再計算
                            $u_item['SumMoney'] = $u_item['UnitPrice'] * $u_item['ItemNum'];
                            if ((int)$this->app->authManager->getUserInfo()->UseAmountFractionClass == 0) { $u_item['SumMoney'] = floor( $u_item['SumMoney'] ); }
                            if ((int)$this->app->authManager->getUserInfo()->UseAmountFractionClass == 1) { $u_item['SumMoney'] = round( $u_item['SumMoney'] ); }
                            if ((int)$this->app->authManager->getUserInfo()->UseAmountFractionClass == 2) { $u_item['SumMoney'] = ceil(  $u_item['SumMoney'] ); }

                            $u_item['UpdateId'] = $userId;  // 更新者
                            // T_OrderItems の更新
                            $items_table->saveUpdate($u_item, $item_data['OrderItemId']);
                        // 数値に変換できない場合は新規行
                        } else {
                            // 配送先情報追加(T_DeliveryDestinationのINSERT)
                            $delivId = $delidest_table->newRow( array_merge( $deliv_for_new_rec,
                                array(
                                        'RegistId' => $userId,
                                        'UpdateId' => $userId,
                                ))
                            );

                            $sumMoney = $item_data['UnitPrice'] * $item_data['ItemNum'];
                            if ((int)$this->app->authManager->getUserInfo()->UseAmountFractionClass == 0) { $sumMoney = floor( $sumMoney ); }
                            if ((int)$this->app->authManager->getUserInfo()->UseAmountFractionClass == 1) { $sumMoney = round( $sumMoney ); }
                            if ((int)$this->app->authManager->getUserInfo()->UseAmountFractionClass == 2) { $sumMoney = ceil(  $sumMoney ); }

                            // 消費税率
                            $u_item['TaxRate'] = $item_data['TaxRate'];

                            // 商品情報新規追加用データの生成
                            $u_item = array(
                                    'ItemNameKj' => $item_data['ItemNameKj'],
                                    'UnitPrice' => $item_data['UnitPrice'],
                                    'ItemNum' => $item_data['ItemNum'],
                                    'SumMoney' => $sumMoney,// Mod By Takemasa(NDC) 20150803 呼出し元でSumMoneyを設定する
                                    'TaxRate' => $item_data['TaxRate'],
                                    'TaxrateNotsetFlg' => 0,
                                    'CombinedTargetFlg' => 1,
                                    'RegistId'      => $userId,
                                    'UpdateId'      => $userId,
                            );

                            // 注文商品追加(T_OrderItemsのINSERT)
                            $orderItemId = $items_table->newRow( $order_seq, $delivId, 1, $u_item);
                        }
                    }
                }
            }

            // 配送伝票の更新
            if ($modifiability[OrderEditor::GROUP_JOURNAL]) {

                // 配送会社もしくは伝票番号が変更された場合は、[配送－伝票番号入力日]を更新する
                $oirow = $this->app->dbAdapter->query(" SELECT Deli_DeliveryMethod, Deli_JournalNumber FROM T_OrderItems WHERE OrderSeq = :OrderSeq "
                )->execute(array(':OrderSeq' => $order_seq))->current();
                $isUpdDeliJournalIncDate = ($oirow['Deli_DeliveryMethod'] == $input['Journal']['Deli_DeliveryMethod'] &&
                $oirow['Deli_JournalNumber'] == $input['Journal']['Deli_JournalNumber']) ? false : true;

                // 配送伝票番号が未入力で通知された場合は、伝票番号不要配送方法が選択されている時であり、初期値設定が必要(20151120)
                if ($input['Journal']['Deli_JournalNumber'] == '') {
                    $mdlsp = new \models\Table\TableSystemProperty($this->app->dbAdapter);
                    $input['Journal']['Deli_JournalNumber'] = $mdlsp->getValue( '[DEFAULT]', 'systeminfo', 'DummyJournalNumber' );
                }

                //NOTE:ValidFlg=1を条件に含めないことに注意
                $sql  = " UPDATE T_OrderItems ";
                $sql .= " SET    Deli_DeliveryMethod = :Deli_DeliveryMethod ";
                $sql .= " ,      Deli_JournalNumber = :Deli_JournalNumber ";
                if ($isUpdDeliJournalIncDate) {
                    $sql .= " ,      Deli_JournalIncDate = :Deli_JournalIncDate ";
                }
                $sql .= " ,      UpdateId = :UpdateId ";
                $sql .= " ,      UpdateDate = :UpdateDate ";
                $sql .= " WHERE  OrderSeq = :OrderSeq ";

                $prm = array(
                    ':Deli_DeliveryMethod' => $input['Journal']['Deli_DeliveryMethod'],
                    ':Deli_JournalNumber' => $input['Journal']['Deli_JournalNumber'],
                    ':UpdateId' => $userId,
                    ':UpdateDate' => date('Y-m-d H:i:s'),
                    ':OrderSeq' => $order_seq,
                );
                if ($isUpdDeliJournalIncDate) {
                    $prm[':Deli_JournalIncDate'] = date('Y-m-d H:i:s');
                }

                $this->app->dbAdapter->query($sql)->execute($prm);
            }

            // 請求取りまとめステータスの設定 20131205 tkaki
            // 配送方法の状況によって請求取りまとめを更新する
            $u_items_list = $items_table->findByOrderSeq($order_seq);// rewindできないので再取得
            $deliverymethod = "";
            foreach($u_items_list as $ois) {
                $deliverymethod = $ois['Deli_DeliveryMethod'];
            }

            if($mghelper->chkCcTargetStatusByDelivery($deliverymethod) != 9) {
                $uOrder['CombinedClaimTargetStatus'] = $mghelper->chkCcTargetStatusByDelivery($deliverymethod);
                $order_table->saveUpdate($uOrder, $u_order['OrderSeq']);
            }

            // OrderSummaryを更新
            $summary_table->updateSummary($u_order['OrderSeq'], $userId);

            // 加盟店顧客、管理顧客の紐付け
            $logicOr = new LogicOrderRegister( $this->app->dbAdapter );
            $logicOr->updateCustomer( $u_order['OrderSeq'] , $userId );

            // 注文_会計テーブルの更新
            $mdlao = new ATableOrder($this->app->dbAdapter);
            if ($has_changed) {
                $udata = array(
                        'DefectFlg' => 0,
                        'DefectNote' => null,
                        'DefectCancelPlanDate' => null,
                        'CreditTransferRequestFlg' => $input['Order']['CreditTransferRequestFlg'],
                );
                $mdlao->saveUpdate($udata, $u_order['OrderSeq']);
            }else{
                $udata = array(
                        'CreditTransferRequestFlg' => $input['Order']['CreditTransferRequestFlg'],
                        );
                $mdlao->saveUpdate($udata, $u_order['OrderSeq']);
            }

            // 注文履歴へ登録
            $history = new CoralHistoryOrder($this->app->dbAdapter);
            // 履歴登録用理由コードの設定
            if ($modifiability[OrderEditor::GROUP_JOURNAL]) {
                $reasonCode = 32;       // 伝票修正
            } else if ($has_changed) {
                $reasonCode = 14;       // 注文修正（再与信戻し）
            } else {
                $reasonCode = 15;       // 注文修正（再与信なし）
            }
            // 履歴登録する
            $history->InsOrderHistory($u_order['OrderSeq'], $reasonCode, $userId);

            $this->app->dbAdapter->getDriver()->getConnection()->commit();
        }
        catch(\Exception $err) {
            $this->app->dbAdapter->getDriver()->getConnection()->rollBack();
            throw $err;
        }

        // 注文修正メールを送信
        try {
            CoralMail::create( $this->app->dbAdapter, $this->app->smtpServer )->SendOrderModifiedMail(
            $this->app->authManager->getUserInfo()->EnterpriseId, array( $order_seq ), $userId );
        }
        catch(CoralMailException $e) {
            // CoralMail内での例外のみ捕捉
            $this->flashMessenger()->addMessage('メール送信失敗');
        }

        // 正常に完了したので完了画面へリダイレクト
        $url = sprintf('order/completeedit/id/%s', $order_id);
        if($has_changed) $url .= '/is_recredited/true';
        return $this->_redirect($url);
	}

	/**
	 * 再与信向けに指定のT_Order行の初期化を実行する
	 * @access protected
	 * @param array $row 初期化対象の行データ
	 * @return array $rowに初期値を適用したデータ
	 */
	protected function _cleanupOrderForReCredit(array $row)
	{
        // 保護するカラム一覧
        $ignore_keys = array(
                'OrderSeq',
                'OrderId',
                'RegistDate',
                'ReceiptOrderDate',
                'EnterpriseId',
                'SiteId',
                'UseAmount',
                'AnotherDeliFlg',
                'DataStatus',
                'Ent_OrderId',
                'Ent_Note',
                'Incre_Note',
                'OrderRegisterMethod',
                'ApiUserId',
                'OemId',
                'Oem_OrderId',
                'Oem_Note',
                /* 20150625_1200_追加ﾌｨｰﾙﾄﾞに関しては一旦保護対象ｶﾗﾑとして扱う(ここから) */
                'T_OrderClass',
                'T_OrderAutoCreditJudgeClass',
                'ServiceTargetClass',
                'ServiceExpectedDate',
                'DailySummaryFlg',
                'PendingReasonCode',
                'ClaimSendingClass',
                'P_OrderSeq',
                'CancelBefDataStatus',
                'Tel30DaysFlg',
                'Tel90DaysFlg',
                'NewSystemFlg',
                'CreditReplyDate',
                'OemClaimTransDate',
                'OemClaimTransFlg',
                'AccSalesArrivalFlg',
                'AccSalesClosedFlg',
                'ConfirmWaitingFlg',
                'CreditNgHiddenFlg',
                'Incre_JudgeScoreTotal',
                'Incre_CoreScoreTotal',
                'Incre_ItemScoreTotal',
                'Incre_NoteScore',
                'Incre_PastOrderScore',
                'Incre_UnpaidScore',
                'Incre_NonPaymentScore',
                'Incre_IdentityDocumentScore',
                'Incre_MischiefCancelScore',
                'Chg_NonChargeFlg',
                'Dmg_DecisionUseAmount',
                'Dmg_DecisionClaimFee',
                'Dmg_DecisionDamageInterestAmount',
                'Dmg_DecisionAdditionalClaimFee',
                'RegistId',
                'UpdateDate',
                'UpdateId',
                'ValidFlg',
                /* 20150625_1200_追加ﾌｨｰﾙﾄﾞに関しては一旦保護対象ｶﾗﾑとして扱う(ここまで) */
                'ReverseOrderId',
        );

        // null以外の値を適用するカラムと初期値のマップ
        $sp_confs = array(
                'DataStatus' => 11,
                'Chg_Status' => 0,
                'Rct_Status' => 0,
                'Rct_MailFlg' => 0,
                'Cnl_CantCancelFlg' => 0,
                'Cnl_Status' => 0,
                'Dmg_DecisionFlg' => 0,
                'Deli_ConfirmArrivalFlg' => 0
        );

        foreach(array_keys($row) as $key) {
            if(in_array($key, $ignore_keys)) continue;
            // 保護対象カラム以外は初期値を設定
            $row[$key] = isset($sp_confs[$key]) ? $sp_confs[$key] : null;
        }
        return $row;
	}

	/**
	 * completeEditアクション。修正完了画面を表示する
	 */
	public function completeEditAction()
	{
	    $this->setPageTitle( '登録内容修正完了');

        $params = array_merge($this->params()->fromRoute(), $this->params()->fromPost());

        $this->view->assign('id', $params['id']);
        $this->view->assign('is_recredited', isset($params['is_recredited']) ? $params['is_recredited'] : false);

        return $this->view;
	}

	/**
	 * 指定のサイトIDがメールアドレス必須であるかを問い合わせる
	 *
	 * @param int $siteId 対象のサイトID
	 * @return bool
	 */
	private function getMailRequire($siteId)
	{
	    foreach( $this->sites as $siteRow ) {
			if( $siteRow['SiteId'] == $siteId ) {
				return $siteRow['ReqMailAddrFlg'] ? true : false;
			}
		}
		return true;
	}

	/**
	 * 注文データ一式を格納した連想配列を元にデータをDBへ保存する
	 *
	 * @param array $data 注文データ
	 * @param int $userId ユーザID
	 * @return int 注文シーケンス番号
	 */
	private function saveOrderData($data, $userId)
	{
        // CSVｽｷｰﾏ調整(ここから)(20150714_1330)
        if (isset($data[OrderInputInfo::ARRKEY_DELIV]['DestPostalCode'])) {
            $data[OrderInputInfo::ARRKEY_DELIV]['PostalCode'] = $data[OrderInputInfo::ARRKEY_DELIV]['DestPostalCode'];
            unset($data[OrderInputInfo::ARRKEY_DELIV]['DestPostalCode']);
        }
        if (isset($data[OrderInputInfo::ARRKEY_DELIV]['DestUnitingAddress'])) {
            $data[OrderInputInfo::ARRKEY_DELIV]['UnitingAddress'] = $data[OrderInputInfo::ARRKEY_DELIV]['DestUnitingAddress'];
            unset($data[OrderInputInfo::ARRKEY_DELIV]['DestUnitingAddress']);
        }
        if (isset($data[OrderInputInfo::ARRKEY_DELIV]['DestPhone'])) {
            $data[OrderInputInfo::ARRKEY_DELIV]['Phone'] = $data[OrderInputInfo::ARRKEY_DELIV]['DestPhone'];
            unset($data[OrderInputInfo::ARRKEY_DELIV]['DestPhone']);
        }
        // CSVｽｷｰﾏ調整(ここまで)(20150714_1330)

        // ↓↓↓CSV定義で送料、手数料が未指定の場合にデータが作成されない問題の解決
        $booCarriage = false;
        $booCharge = false;
        $intItemIdx = 0;
        foreach( $data[OrderInputInfo::ARRKEY_ITEMS] as $item ) {
            switch ( intval($item['DataClass']) ) {
                case 1:
                    $intItemIdx++;
                    break;
                case 2:
                    $booCarriage = true;
                    break;
                case 3:
                    $booCharge = true;
                    break;
            }
        }

        $itemCarriage = array();
        $itemCharge = array();
        if ( $booCarriage == false ) {
            // 送料が存在しない
            $itemCarriage[] = array(
                    'ItemNameKj' => '送料',
                    'UnitPriceCarriage' => '0',
                    'ItemNum' => 1,
                    'DataClass' => 2,
            );
            array_splice($data[OrderInputInfo::ARRKEY_ITEMS], $intItemIdx, 0, $itemCarriage);
        }
        if ( $booCharge == false ) {
            // 手数料が存在しない
            $itemCharge[] = array(
                    'ItemNameKj' => '店舗手数料',
                    'UnitPriceCarriage' => '0',
                    'ItemNum' => 1,
                    'DataClass' => 3,
            );
            array_splice($data[OrderInputInfo::ARRKEY_ITEMS], $intItemIdx + 1, 0, $itemCharge);
        }
        // ↑↑↑CSV定義で送料、手数料が未指定の場合にデータが作成されない問題の解決

        $orderTable = new TableOrder( $this->app->dbAdapter );
        $custoTable = new TableCustomer(  $this->app->dbAdapter );
        $delivTable = new TableDeliveryDestination(  $this->app->dbAdapter );
        $itemsTable = new TableOrderItems(  $this->app->dbAdapter );

        // 郵便番号カナ文字列取得
        $postalcodeTable = new TablePostalCode( $this->app->dbAdapter );
        $addressKanaStr = $postalcodeTable->getAddressKanaStr($data[OrderInputInfo::ARRKEY_CUSTO]['PostalCode']);

        $orderInfo = array();

        // 購入者情報と配送先情報の郵便番号を整形 (2008.02.08 追加 eda)
        $data[OrderInputInfo::ARRKEY_CUSTO]['PostalCode']=
            CoralValidateUtility::fixPostalCode( $data[OrderInputInfo::ARRKEY_CUSTO]['PostalCode'] );
        $data[OrderInputInfo::ARRKEY_DELIV]['PostalCode'] =
            CoralValidateUtility::fixPostalCode( $data[OrderInputInfo::ARRKEY_DELIV]['PostalCode'] );

        // 購入者情報と配送先情報の電話番号およびメールアドレスを半角に統一 (2008.02.20 追加 eda)
        $data[OrderInputInfo::ARRKEY_CUSTO]['Phone'] =
            BaseGeneralUtils::convertWideToNarrow( $data[OrderInputInfo::ARRKEY_CUSTO]['Phone'] );
        $data[OrderInputInfo::ARRKEY_CUSTO]['MailAddress'] =
            BaseGeneralUtils::convertWideToNarrow( $data[OrderInputInfo::ARRKEY_CUSTO]['MailAddress'] );
        $data[OrderInputInfo::ARRKEY_DELIV]['Phone'] =
            BaseGeneralUtils::convertWideToNarrow( $data[OrderInputInfo::ARRKEY_DELIV]['Phone'] );

        // 購入者情報と配送先情報の住所中の数字をすべて全角に統一 (2008.02.20 追加 eda)
        foreach( array( 'PrefectureName', 'City', 'Town', 'Building', 'UnitingAddress' ) as $key ) {
            $data[OrderInputInfo::ARRKEY_CUSTO][$key] =
                BaseGeneralUtils::convertNumberWideToNarrow( $data[OrderInputInfo::ARRKEY_CUSTO][$key] );
            $data[OrderInputInfo::ARRKEY_DELIV][$key] =
                BaseGeneralUtils::convertNumberWideToNarrow( $data[OrderInputInfo::ARRKEY_DELIV][$key] );
        }

        // 注文データ生成(注文シーケンス確定)
        // (サイト情報の取得)
        $sql = " SELECT * FROM T_Site WHERE SiteId = :SiteId ";
        $row_site = $this->app->dbAdapter->query($sql)->execute(array(':SiteId' =>  $data[OrderInputInfo::ARRKEY_ORDER]['SiteId']))->current();
        // (請求書送付区分)
        $claimSendingClass = 21;
        if (($this->app->authManager->getUserInfo()->SelfBillingMode > 0) &&  ($row_site['SelfBillingFlg'] == 1)) {
            $claimSendingClass = $data[OrderInputInfo::ARRKEY_CUSTO]['ClaimSendingClass'];
            // 以下、CSV取込み対応(20150805_1155)
            if ($claimSendingClass == 0 || $claimSendingClass == 1) {
                $claimSendingClass += 11;
            }
        }

        // 別配送先チェックありも、郵便番号･住所･氏名･電話番号が[ご注文者情報]と同じ時は、[AnotherDeliFlg]を[0]にする
        if (($data[OrderInputInfo::ARRKEY_ORDER]['AnotherDeliFlg'] == 1)
            &&
            (($data[OrderInputInfo::ARRKEY_CUSTO]['PostalCode'] == $data[OrderInputInfo::ARRKEY_DELIV]['PostalCode']) &&
             ($data[OrderInputInfo::ARRKEY_CUSTO]['NameKj'] == $data[OrderInputInfo::ARRKEY_DELIV]['DestNameKj']) &&
             ($data[OrderInputInfo::ARRKEY_CUSTO]['NameKn'] == $data[OrderInputInfo::ARRKEY_DELIV]['DestNameKn']) &&
             ($data[OrderInputInfo::ARRKEY_CUSTO]['Phone'] == $data[OrderInputInfo::ARRKEY_DELIV]['Phone']))) {

             if(!empty($data[OrderInputInfo::ARRKEY_CUSTO]['UnitingAddress'])){
                 //再登録時 UnitingAddressと比較
                 if(($data[OrderInputInfo::ARRKEY_CUSTO]['UnitingAddress'] == $data[OrderInputInfo::ARRKEY_DELIV]['UnitingAddress'])){

                     $data[OrderInputInfo::ARRKEY_ORDER]['AnotherDeliFlg'] = 0;
                 }
             }else{
                 //郵便番号・住所を比較
                 if(($data[OrderInputInfo::ARRKEY_CUSTO]['PrefectureCode'] == $data[OrderInputInfo::ARRKEY_DELIV]['PrefectureCode']) &&
                 ($data[OrderInputInfo::ARRKEY_CUSTO]['City'] == $data[OrderInputInfo::ARRKEY_DELIV]['City']) &&
                 ($data[OrderInputInfo::ARRKEY_CUSTO]['Town'] == $data[OrderInputInfo::ARRKEY_DELIV]['Town']) &&
                 ($data[OrderInputInfo::ARRKEY_CUSTO]['Building'] == $data[OrderInputInfo::ARRKEY_DELIV]['Building'])){

                     $data[OrderInputInfo::ARRKEY_ORDER]['AnotherDeliFlg'] = 0;
                 }
             }
         }

        // INSERT(T_Order)
        $orderSeq = $orderTable->newRow(
            $data[OrderInputInfo::ARRKEY_ORDER]['ReceiptOrderDate'],
            $this->app->authManager->getUserInfo()->EnterpriseId,
            $data[OrderInputInfo::ARRKEY_ORDER]['SiteId'],
            array_merge($data[OrderInputInfo::ARRKEY_ORDER],
                array(
                    'OemId' => $this->app->authManager->getUserInfo()->OemId,
                    'T_OrderClass' => ($row_site['T_OrderClass'] == 1 && $data[OrderInputInfo::ARRKEY_ORDER]['T_OrderClass'] == 1) ? 1 : 0,
                    'T_OrderAutoCreditJudgeClass' => ($row_site['T_OrderClass'] == 1 && $data[OrderInputInfo::ARRKEY_ORDER]['T_OrderClass'] == 1) ? $data[OrderInputInfo::ARRKEY_ORDER]['cmst87'] : 0,
                    'ServiceTargetClass' => $row_site['ServiceTargetClass'],
                    'ServiceExpectedDate' => (($data[OrderInputInfo::ARRKEY_ORDER]['ServiceExpectedDate'] == '') ? null : $data[OrderInputInfo::ARRKEY_ORDER]['ServiceExpectedDate']),
                    'ClaimSendingClass' => $claimSendingClass,
                    'NewSystemFlg' => 1,
                    'RegistId' => $userId,
                    'UpdateId' => $userId,
                )
            )
        );
        // UPDATE(T_Order[P_OrderSeq])
        $this->app->dbAdapter->query(" UPDATE T_Order SET P_OrderSeq = :OrderSeq WHERE OrderSeq = :OrderSeq ")->execute(array(':OrderSeq' => $orderSeq));

        // 2015/10/06 Y.Suzuki Add 会計対応 Stt
        // 会計用項目のINSERT
        $mdlato = new ATableOrder($this->app->dbAdapter);
        $mdlato->saveNew(array('OrderSeq' => $orderSeq, 'CreditTransferRequestFlg' => $data[OrderInputInfo::ARRKEY_ORDER]['CreditTransferRequestFlg']));
        // 2015/10/06 Y.Suzuki Add 会計対応 End

        // 未クローズ注文を登録する
        $tOrderNotClose = new TableOrderNotClose($this->app->dbAdapter);
        $tOrderNotClose->saveNew(array('OrderSeq' => $orderSeq, 'RegistId' => $userId, 'UpdateId' => $userId));

        // 注文者データ作成(CustomerId確定)
        // INSERT(T_Customer)
        $customerId = $custoTable->newRow( $orderSeq, array_merge( $data[OrderInputInfo::ARRKEY_CUSTO],
            array(
                'CorporateName' => $data[OrderInputInfo::ARRKEY_CUSTO]['CorporateName'],
                'DivisionName'  => $data[OrderInputInfo::ARRKEY_CUSTO]['DivisionName'],
                'CpNameKj'      => $data[OrderInputInfo::ARRKEY_CUSTO]['CpNameKj'],
                'EntCustSeq'    => 0,/* 処理最後に更新あり */
                'AddressKn'     => $addressKanaStr,
                'EntCustId'     => $data[OrderInputInfo::ARRKEY_CUSTO]['EntCustId'],
                'RegistId'      => $userId,
                'UpdateId'      => $userId,
            ))
        );

        // ＮＴＴファシリティーズの加盟店IDを取得
        $sql = " SELECT PropValue FROM T_SystemProperty WHERE Module = '[DEFAULT]' AND Category = 'systeminfo' AND Name = 'NTTFEnterpriseId' ";
        $nttEntId = (int)$this->app->dbAdapter->query($sql)->execute()->current()['PropValue'];

        // 商品数分だけ商品と配送先データを生成
        $delivDataList = array();
        $itemsDataList = array();
        foreach( $data[OrderInputInfo::ARRKEY_ITEMS] as $item ) {

            // 株式会社ＮＴＴファシリティーズ対応(内税加盟店につき、外税額のレコードを作成しない)
            if ($this->app->authManager->getUserInfo()->EnterpriseId == $nttEntId && $item['DataClass'] == 4 && nvl($item['UnitPriceTax'], 0) == 0) {
                continue;
            }

            // 先に配送先確定
            $delivId = -1;
            if( $item['DataClass'] == 1 ) {
                // INSERT(T_DeliveryDestination)
                $delivId = $delivTable->newRow( array_merge( $data[OrderInputInfo::ARRKEY_DELIV],
                    array(
                        'RegistId' => $userId,
                        'UpdateId' => $userId,
                    ))
                );
                $sql = " SELECT * FROM T_DeliveryDestination WHERE DeliDestId = :DeliDestId ";
                $stm = $this->app->dbAdapter->query($sql);
                $delivDataList[] = $stm->execute(array(':DeliDestId' => $delivId))->current();
            }

            // INSERT(T_OrderItems)
            // [送料][手数料][外税]は"UnitPrice"ではないことに注意(20150714_1240)
            $unitprice = 0;
            if      (isset($item['UnitPrice'])        ) { $unitprice = $item['UnitPrice']; }
            else if (isset($item['UnitPriceCarriage'])) { $unitprice = $item['UnitPriceCarriage']; }
            else if (isset($item['UnitPriceCharge'])  ) { $unitprice = $item['UnitPriceCharge']; }
            else if (isset($item['UnitPriceTax'])     ) { $unitprice = $item['UnitPriceTax']; }

            $cvtitem = array(
                    'ItemNameKj' => $item['ItemNameKj'],
                    'UnitPrice'  => (double)$unitprice,
                    'ItemNum'    => (double)$item['ItemNum'],
                    'DataClass'  => $item['DataClass'],
            );

            //消費税率設定
            if($item['DataClass'] ==1 && $item['TaxRate'] != NULL){
                $cvtitem['TaxRate'] = $item['TaxRate'];
                $cvtitem['TaxrateNotsetFlg'] = 0;
            }else{
                $cvtitem['TaxRate'] = NULL;
                $cvtitem['TaxrateNotsetFlg'] = 1;
            }

            // (SumMoney)
            $sumMoney = ((double)$unitprice * (double)$item['ItemNum']);
            if ((int)$this->app->authManager->getUserInfo()->UseAmountFractionClass == 0) { $sumMoney = floor( $sumMoney ); }
            if ((int)$this->app->authManager->getUserInfo()->UseAmountFractionClass == 1) { $sumMoney = round( $sumMoney ); }
            if ((int)$this->app->authManager->getUserInfo()->UseAmountFractionClass == 2) { $sumMoney = ceil(  $sumMoney ); }
            $cvtitem['SumMoney'] = $sumMoney;

            $orderItemId = $itemsTable->newRow( $orderSeq, $delivId, $cvtitem['DataClass'], array_merge( $cvtitem,
                array(
                        'CombinedTargetFlg' => 1,
                        'RegistId'      => $userId,
                        'UpdateId'      => $userId,
                ))
            );

            $sql = " SELECT * FROM T_OrderItems WHERE OrderItemId = :OrderItemId ";
            $stm = $this->app->dbAdapter->query($sql);
            $itemsDataList[] = $stm->execute(array(':OrderItemId' => $orderItemId))->current();

        }
        $sql = " SELECT SUM(SumMoney) AS TotalAmount FROM T_OrderItems WHERE OrderSeq = :OrderSeq ";
        $stm = $this->app->dbAdapter->query($sql);
        $totalAmount = (int)$stm->execute(array(':OrderSeq' => $orderSeq))->current()['TotalAmount'];


        // UPDATE(T_Order)合計金額を注文データに設定して再度保存※OemId含め
        $prm = array();
        $sql  = " UPDATE T_Order SET UseAmount = :UseAmount ";
        $prm += array(':UseAmount' => $totalAmount);
        if (!is_null($oemid)) {
            $sql .= " , OemId = :OemId ";
            $prm += array(':OemId' => $oemid);
        }
        $sql .= " WHERE OrderSeq = :OrderSeq ";
        $prm += array(':OrderSeq' => $orderSeq);
        $stm = $this->app->dbAdapter->query($sql);
        $stm->execute($prm);

        $orderInfo[OrderInputInfo::ARRKEY_ORDER] = $this->app->dbAdapter->query(" SELECT * FROM T_Order WHERE OrderSeq = " . $orderSeq)->execute(null)->current();
        $orderInfo[OrderInputInfo::ARRKEY_CUSTO] = $this->app->dbAdapter->query(" SELECT * FROM T_Customer WHERE CustomerId = " . $customerId)->execute(null)->current();
        $orderInfo[OrderInputInfo::ARRKEY_DELIV] = $delivDataList;
        $orderInfo[OrderInputInfo::ARRKEY_ITEMS] = $itemsDataList;

        //-----------------------------------------
        // 今作成した注文データのサマリーを作成する
        //-----------------------------------------
        $summaries = new TableOrderSummary($this->app->dbAdapter);
        $summaries->updateSummary( $orderSeq, $userId );

        //-----------------------------------------
        // 加盟店顧客、管理顧客の紐付け
        //-----------------------------------------
        $logicOr = new LogicOrderRegister($this->app->dbAdapter);
        $logicOr->updateCustomer($orderSeq, $userId);

        return $orderSeq;
	}

	/**
	 * POSTされた個別注文フォームの情報をDB向けの連想配列に展開する
	 *
	 * @param mixed $postData 個別注文フォームから登録されたPOSTデータ
	 * @return array
	 */
	private function createOrderArraysFromPost($postData)
	{
	    $arrays = array();
		$config = array(
			OrderInputInfo::ARRKEY_ORDER => '/^T_Order\.(.+)$/i',
			OrderInputInfo::ARRKEY_CUSTO => '/^T_Customer\.(.+)$/i',
			OrderInputInfo::ARRKEY_DELIV => '/^T_DeliveryDestination\.(.+)$/i'
		);

		foreach($config as $name => $pattern) {
			foreach( $this->formManager->searchItem( $pattern, CoralFormManager::SEARCH_BY_COLUMN ) as $item ) {
				preg_match( $pattern, $item->getColumnMap(), $match );
				$col = $match[1];
				$arr = isset($arrays[ $name ]) ? $arrays[ $name ] : null;
				if( $arr == null ) $arr = array();

				$key = $item->getName();
				$value = isset($postData[ $key ]) ? $postData[ $key ] : null;
				switch( $key ) {
					case 'C_PrefectureName':
					case 'D_PrefectureName':
						$arr[ 'PrefectureName' ] = $this->getPrefectureName( $value );
						break;
				}

				$arr[ $col ] = $value;

				$arrays[ $name ] = $arr;
			}
		}
		$arrays[OrderInputInfo::ARRKEY_ORDER]['AnotherDeliFlg'] = ((int)(isset($postData['AnotherDeliFlg']) ? $postData['AnotherDeliFlg'] : 0));


		$arrays[OrderInputInfo::ARRKEY_ORDER]['cmst87'] = $postData['cmst87'];// テスト注文-設定与信結果
		//口座振替申込区分を設定
		$arrays[OrderInputInfo::ARRKEY_ORDER]['CreditTransferRequestFlg'] = $postData['O_CreditTransferRequestFlg'];

		// 別配送指定がない場合は注文者情報から配送先データを生成
		if( (isset($postData['AnotherDeliFlg']) ? $postData['AnotherDeliFlg'] : null) != OrderEnums::AnotherDeliFlg_ANOTHER_SPEC ) {
			$arrays[OrderInputInfo::ARRKEY_DELIV]['DestNameKj'] = $arrays[OrderInputInfo::ARRKEY_CUSTO]['NameKj'];
			$arrays[OrderInputInfo::ARRKEY_DELIV]['DestNameKn'] = $arrays[OrderInputInfo::ARRKEY_CUSTO]['NameKn'];
			foreach( array(
				TableDeliveryDestination::ADDR_POSTAL_CODE,
				TableDeliveryDestination::ADDR_PREF_CODE,
				TableDeliveryDestination::ADDR_PREF_NAME,
				TableDeliveryDestination::ADDR_CITY,
				TableDeliveryDestination::ADDR_TOWN,
				TableDeliveryDestination::ADDR_BDL,
				'Phone',
			    'UnitingAddress'
			) as $key ) {
				$arrays[OrderInputInfo::ARRKEY_DELIV][$key] = $arrays[OrderInputInfo::ARRKEY_CUSTO][$key];
			}
		}

		// 商品情報の展開
		$order_items = array();
		$postedItems = Json::decode( process_slashes( $postData['I_ItemList'] ), Json::TYPE_ARRAY );
		if( $postedItems == null ) $postedItems = array();
		foreach( $postedItems as $postedItem ) {
			$order_items[] = array(
				'ItemNameKj' => $postedItem['i_itemnamekj'],
				'DataClass' => 1,
				'UnitPrice' => ((int)($postedItem['i_unitprice'])),
				'ItemNum' => (($postedItem['i_itemnum'])),
		        'TaxRate' => $postedItem['i_taxrate']
			);
		}

		// 送料と手数料の追加
		$order_items[] = array(
			'ItemNameKj' => $postData[ 'I_ItemNameKj_' . $this->view->order_item_carriage__ ],
			'DataClass' => 2,
			'UnitPrice' => ((int)($postData[ 'I_UnitPrice_' . $this->view->order_item_carriage__ ] )),
			'ItemNum' => 1,
		    'TaxRate' => null
		);
		$order_items[] = array(
			'ItemNameKj' => $postData[ 'I_ItemNameKj_' . $this->view->order_item_charge__ ],
			'DataClass' => 3,
			'UnitPrice' => ((int)($postData[ 'I_UnitPrice_' . $this->view->order_item_charge__ ])),
			'ItemNum' => 1,
		    'TaxRate' => null
		);
		// 外税の加盟店時は、以下の処理が必要
		if ($this->app->authManager->getUserInfo()->TaxClass == 1) {
		    $order_items[] = array(
		            'ItemNameKj' => $postData[ 'I_ItemNameKj_' . $this->view->order_item_taxclass__ ],
		            'DataClass' => 4,
		            'UnitPrice' => ((int)($postData[ 'I_UnitPrice_' . $this->view->order_item_taxclass__ ])),
		            'ItemNum' => 1,
		            'TaxRate' => null
		    );
		}

		$arrays[OrderInputInfo::ARRKEY_ITEMS] = $order_items;

		return $arrays;
	}

	/**
	 * 指定の都道府県コードに対応する都道府県名を取得する
	 *
	 * @param int $prefCode 都道府県コード
	 * @return string
	 */
	private function getPrefectureName($prefCode)
	{
	    return isset($this->view->prefecture_info[ $prefCode ]) ? $this->view->prefecture_info[ $prefCode ] : null;
	}

    /**
     * (Ajax)画面上選択されたサイトから、表示エネーブルに関連する項目の取得
     * 通知例 : order/enablesite/siteid/410
     */
    public function enablesiteAction()
    {
        try
        {
            $params = $this->getParams();

            $siteid = isset($params['siteid']) ? $params['siteid'] : -1;

            $row = $this->app->dbAdapter->query(" SELECT s.*, e.HideToCbButton FROM T_Site s INNER JOIN T_Enterprise e ON (e.EnterpriseId = s.EnterpriseId) WHERE SiteId = :SiteId ")->execute(array(':SiteId' => $siteid))->current();
            if (!$row) {
                throw new \Exception('該当サイトなし');
            }

            $enableinfo = array (
                    'isEnableServiceTargetClass' => $row['ServiceTargetClass'],     // 役務対象区分[ServiceTargetClass](0：通常／1:役務)
                    'isEnableTOrderClass'        => $row['T_OrderClass'],           // テスト注文可否区分[T_OrderClass](0：不可／1:可能)
                    'isEnableSelfBillingFlg'     => ($row['SelfBillingFlg'] == 1 && $row['HideToCbButton'] == 0) ? 1 : 0,// 請求書別送[SelfBillingFlg](0：行わない／1：行う)、同梱ツール別送ボタン非表示フラグ[HideToCbButton](0：非表示にしない（規定動作）／1：非表示にする)
            );

            // 成功指示
            $msg = '1';
        }
        catch(\Exception $e)
        {
            $msg = $e->getMessage();
        }

        echo \Zend\Json\Json::encode(array('status' => $msg, 'enableinfo' => $enableinfo));
        return $this->response;
    }

    /**
     * 注文日が妥当か？(画面上入力された注文日の期間チェック)
     *
	 * @param string $compDate (画面上検証対象)注文日 yyyy-MM-dd形式で通知
	 * @return boolean
     */
    private function isValidOrderdate($compDate)
    {
        // 注文登録標準期間日数、の取得
        $obj = new \models\Table\TableSystemProperty($this->app->dbAdapter);
        // (過去日:デフォルト 60日)
        $daysPast   = (int)$obj->getValue('[DEFAULT]', 'systeminfo', 'OrderDefaultDaysPast'  );
        $daysPast   = ($daysPast > 0) ? $daysPast : 60;
        // (未来日:デフォルト180日)
        $daysFuture = (int)$obj->getValue('[DEFAULT]', 'systeminfo', 'OrderDefaultDaysFuture');
        $daysFuture = ($daysFuture > 0) ? $daysFuture : 180;

        // 注文日が本日と一致する時はtrueを戻す
        $today = date('Y-m-d');
        if (date('Y-m-d') < $compDate) {
            // 未来日が指定されている時
            $diffDate = BaseGeneralUtils::CalcSpanDays($today, $compDate);
            return ($diffDate < $daysFuture) ? true : false;
        }
        else if (date('Y-m-d') > $compDate) {
            // 過去日が指定されている時
            $diffDate = BaseGeneralUtils::CalcSpanDays($compDate, $today);
            return ($diffDate < $daysPast) ? true : false;
        }

        // ($today == $date) 注文日＝本日
        return true;
    }

    /**
     * CSV設定変更画面
     */
    public function changecsvAction() {
        $this->addStyleSheet( '../../oemmember/css/column_table.css' );
        $this->addJavaScript( '../../oemmember/js/column_table.js' );

        $params = $this->getParams();

        $tId = $params['tid'];
        $tClass = $params['tclass'];
        $eId = $params['eid'];
        $sId = 0;

        $mdlth = new \models\Table\TableTemplateHeader( $this->app->dbAdapter );
        $mdltf = new \models\Table\TableTemplateField( $this->app->dbAdapter );
        $mdle = new TableEnterprise( $this->app->dbAdapter );

        // ユーザーIDの取得
        $mdlu = new \models\Table\TableUser( $this->app->dbAdapter );
        getUserInfoForMember( $this->app, $userClass, $seq );
        $userId = $mdlu->getUserId( $userClass, $seq );

        // 加盟店情報の取得
        $ent = $mdle->find( $this->app->authManager->getUserInfo()->EnterpriseId )->current();

        // 該当のテンプレート取得
        $templateSeq = $mdlth->getTemplateSeq( $tId, $tClass, $eId, $sId, false );

        // 該当のテンプレートがなかった場合
        if( empty( $templateSeq ) ) {

            // デフォルト取得
            $templateSeq = $mdlth->getTemplateSeq( $tId, 0, 0, 0, false );

            if( !empty( $templateSeq ) ) {
                // 新しくテンプレート作成
                $header = $mdlth->find( $templateSeq )->current();
                $header['TemplateClass'] = $tClass;
                $header['Seq'] = $eId;
                $header['TemplatePattern'] = $sId;
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
            else {
                throw new \Exception( 'テンプレートが存在しません。' );
            }
        }

        $templateName = $mdlth->find( $templateSeq )->current()['TemplateName'];

        // ListNumber順にTemplateFieldを取り出す
        $templateFieldList = ResultInterfaceToArray( $mdltf->get( $templateSeq ) );

        $validList = array();
        $invalidList = array();

        foreach( $templateFieldList as $templateField ) {
            if ( ( $ent['BillingAgentFlg'] != '1' )
              && (mb_substr($templateField['PhysicalName'], 0, 4) == 'Free')
              && ($tId == 'CKA01001_1' || $tId == 'CKA01001_2')
               )
            {
                $this->app->logger->debug('PhysicalName='. $templateField['PhysicalName']);
                continue;
            }
            if(strcmp($templateField['PhysicalName'],"CreditTransferRequestFlg")  == 0 && strcmp($this->creditTransferFlg, "0") == 0 ){
                continue;
            }
            if( $templateField['ValidFlg'] == 1 ) {
                $validList[] = $templateField;
            }
            elseif( $templateField['ValidFlg'] == 0 ) {
                $invalidList[] = $templateField;
            }
        }

        // タイトルの設定
        $this->setPageTitle( 'テンプレートID：' . $tId . '　テンプレート名：' . $templateName );

        $this->view->assign( 'validList', $validList );
        $this->view->assign( 'invalidList', $invalidList );

        $this->view->assign( 'userId', $userId );
        $this->view->assign( 'templateSeq', $templateSeq );

        // リダイレクト先設定
        $redirect = 'order/changecsv/tid/' .$tId . '/tclass/' . $tClass .'/eid/' . $eId . '/sid/' . $sId;
        $this->view->assign( 'redirect', $redirect );

        return $this->view;
    }

    //******************************************************
    // 以下、一括注文修正(CSV使用)関連
    //******************************************************
    /**
     * editCsvアクション。一括注文修正フォームを表示する
     */
    public function editCsvAction()
    {
        // セッションオブジェクトがあったら廃棄
        $session = new Container( 'OrderEditAction' );
        if( isset( $session->dbRows ) ) {
            unset( $session->dbRows );
        }

        // タイトルだけ変更
        $this->setPageTitle( '一括注文修正（CSV）' );
        $this->view->assign( 'field_name', self::UPLOAD_FIELD_NAME );

        return $this->view;
    }

    /**
     * CSV設定変更画面
     */
    public function changeeditCsvAction() {
        $this->addStyleSheet( '../../oemmember/css/column_table.css' );
        $this->addJavaScript( '../../oemmember/js/column_table.js' );

        $params = $this->getParams();

        $tId = $params['tid'];
        $tClass = $params['tclass'];
        $eId = $params['eid'];
        $sId = 0;

        $mdlth = new \models\Table\TableTemplateHeader( $this->app->dbAdapter );
        $mdltf = new \models\Table\TableTemplateField( $this->app->dbAdapter );
        $mdle = new TableEnterprise( $this->app->dbAdapter );


        // ユーザーIDの取得
        $mdlu = new \models\Table\TableUser( $this->app->dbAdapter );
        getUserInfoForMember( $this->app, $userClass, $seq );
        $userId = $mdlu->getUserId( $userClass, $seq );

        // 該当のテンプレート取得
        $templateSeq = $mdlth->getTemplateSeq( $tId, $tClass, $eId, $sId, false );

        // 加盟店情報の取得
        $ent = $mdle->find( $this->app->authManager->getUserInfo()->EnterpriseId )->current();

        // 該当のテンプレートがなかった場合
        if( empty( $templateSeq ) ) {

            // デフォルト取得
            $templateSeq = $mdlth->getTemplateSeq( $tId, 0, 0, 0, false );

            if( !empty( $templateSeq ) ) {
                // 新しくテンプレート作成
                $header = $mdlth->find( $templateSeq )->current();
                $header['TemplateClass'] = $tClass;
                $header['Seq'] = $eId;
                $header['TemplatePattern'] = $sId;
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
            else {
                throw new \Exception( 'テンプレートが存在しません。' );
            }
        }

        $templateName = $mdlth->find( $templateSeq )->current()['TemplateName'];

        // ListNumber順にTemplateFieldを取り出す
        $templateFieldList = ResultInterfaceToArray( $mdltf->get( $templateSeq ) );

        $validList = array();
        $invalidList = array();

        foreach( $templateFieldList as $templateField ) {
            if ( ( $ent['BillingAgentFlg'] != '1' )
              && (mb_substr($templateField['PhysicalName'], 0, 4) == 'Free')
              && ($tId == 'CKA01001_1' || $tId == 'CKA01001_2')
               )
            {
                $this->app->logger->debug('PhysicalName='. $templateField['PhysicalName']);
                continue;
            }
            if(strcmp($templateField['PhysicalName'],"CreditTransferRequestFlg")  == 0 && strcmp($this->creditTransferFlg, "0") == 0 ){
                continue;
            }

            if( $templateField['ValidFlg'] == 1 ) {
                $validList[] = $templateField;
            }
            elseif( $templateField['ValidFlg'] == 0 ) {
                $invalidList[] = $templateField;
            }
        }

        // タイトルの設定
        $this->setPageTitle( 'テンプレートID：' . $tId . '　テンプレート名：' . $templateName );

        $this->view->assign( 'validList', $validList );
        $this->view->assign( 'invalidList', $invalidList );

        $this->view->assign( 'userId', $userId );
        $this->view->assign( 'templateSeq', $templateSeq );

        // リダイレクト先設定
        $redirect = 'order/changeeditCsv/tid/' .$tId . '/tclass/' . $tClass .'/eid/' . $eId . '/sid/' . $sId;
        $this->view->assign( 'redirect', $redirect );

        return $this->view;
    }

    /**
     * confirmEditCsvアクション。一括注文修正の確認画面を表示する
     *
     */
    public function confirmeditCsvAction()
    {
        $this->addStyleSheet( './css/shipping_confirm_csv.css' );
        $params = $this->getParams();

        // 引きずり回し情報があれば、その内容を[$file]へアサイン
        $file = ( (isset($params['load_file_info'])) ? Json::decode($params['load_file_info'], Json::TYPE_ARRAY) : $_FILES[ self::UPLOAD_FIELD_NAME ] );

        $fileName = $file['name'];
        move_uploaded_file($file['tmp_name'], $this->makeTempFileName());   // テンポラリファイル名での一時退避

        $success_by_force = false;// 強制サクセスフラグ

        if( ! preg_match( '/\.xl.$/i', $fileName ) &&  $file['tmp_name'] != "") {
            $path = $this->makeTempFileName();                              // ファイルパスを[一時退避]名にアサイン
            BaseIOUtility::convertFileEncoding( $path, null, null, true );
            $reader = new BaseIOCsvReader( $path );
            $handler = new \Coral\Coral\CsvHandler\CoralEditCsvHandlerOrder( $reader, array(
                    CoralCsvHandlerOrder::OPTIONS_DBADAPTER => $this->app->dbAdapter,
                    CoralCsvHandlerOrder::OPTIONS_ENT_ID => $this->app->authManager->getUserInfo()->EnterpriseId
            ) );

            // 修正確認での編集内容通知
            if (isset($params['load_file_info'])) {
                $handler->setControllerParams($params);
            }

            $success = $handler->exec();

            // エラーがあっても[CSV一括登録区分(1：エラーがあってもOK分のみ登録)]であれば、強制的に登録実行を許す
            if ($this->app->authManager->getUserInfo()->CsvRegistClass == 1 && !empty($handler->getExceptions()) ) {
                $success_by_force = true;
            }

            $csvCaution = 0;
            $caution = 0;
            $duplication = array();
            $duplicateId = array();

            if( $success || $success_by_force ) {

                // 注文データのブロックを作成
                $i = 0;
                $totalItemCount = 0;
                $mapLineBloack = array();
                $orderData = array();
                foreach( $handler->getBuilders() as $builder ) {
                    $orderData[] = $builder->build();
                    $totalItemCount += $builder->itemCount();
                    $mapLineBloack[$i] = $totalItemCount;
                    $i++;
                }

                // NOTE : ここでの[任意注文番号チェック][csv重複チェック]は行わない(20151009_1330)

                $mapAnotherWarning = array();

                // 注文日が妥当か？
                $i = 0;
                $csvCaution2 = 0;
                foreach( $orderData as $order ) {
                    if (!$this->isValidOrderdate(date('Y-m-d', strtotime($order['order']['ReceiptOrderDate'])))) {
                        $csvCaution2 = 1;
                        $mapAnotherWarning[$mapLineBloack[$i]] = true;
                    }
                    $i++;
                }
                $this->view->assign( 'csvCaution2', $csvCaution2 );

                $mdl = new TablePostalCode($this->app->dbAdapter);
                $mdlprefecture = new TablePrefecture($this->app->dbAdapter);
                // 郵便番号に対する住所が妥当か？
                $i = 0;
                $csvCaution3 = 0;
                foreach( $orderData as $order ) {
                    if (!$mdl->isValidPostAddressKanji($order['customer']['PostalCode'], $order['customer']['UnitingAddress'])) {
                        $csvCaution3 = 1;
                        $mapAnotherWarning[$mapLineBloack[$i]] = true;
                    }
                    if ($csvCaution3 == 0 && !$mdl->isValidPostAddressKanji($order['delivery']['DestPostalCode'], $order['delivery']['DestUnitingAddress'])) {
                        $csvCaution3 = 1;
                        $mapAnotherWarning[$mapLineBloack[$i]] = true;
                    }
                    $i++;
                }
                $this->view->assign( 'csvCaution3', $csvCaution3 );

                // 郵便番号に対する住所が、検索状態のままであるか？
                $i = 0;
                $csvCaution4 = 0;
                foreach( $orderData as $order ) {
                    if ($mdl->isPerfectMatchPostAddressKanji($order['customer']['PostalCode'], $order['customer']['UnitingAddress'])) {
                        $csvCaution4 = 1;
                        $mapAnotherWarning[$mapLineBloack[$i]] = true;
                    }
                    if ($csvCaution4 == 0 && $mdl->isPerfectMatchPostAddressKanji($order['delivery']['DestPostalCode'], $order['delivery']['DestUnitingAddress'])) {
                        $csvCaution4 = 1;
                        $mapAnotherWarning[$mapLineBloack[$i]] = true;
                    }
                    $i++;
                }
                $this->view->assign( 'csvCaution4', $csvCaution4 );

                $this->view->assign( 'mapAnotherWarning', $mapAnotherWarning );
            }

            $getAllResultsCount = 0;
            if (!empty($handler->getAllResults())) {
                $getAllResultsCount = count($handler->getAllResults());
            }
            $getBuildersCount = 0;
            if (!empty($handler->getBuilders())) {
                $getBuildersCount = count($handler->getBuilders());
            }

//            //ハイフン正規化
//            $handler->hyphenNormalize($handler->getCsvSchema());
            //注文データ数が0件
            if($getAllResultsCount <= 0){
                $errors = array(
                        new CoralCsvHandlerLine(
                        array( 0 => array( 'データエラー' => 'データがありません' ) ),
                        0,
                        CoralCsvHandlerLine::TYPE_ERROR
                        ) );
            }else{
                $errors = $handler->getExceptions();
            }

            $this->view->assign( 'colSchema', $handler->getCsvSchema() );
            $this->view->assign( 'importedRows', $handler->getResults() );
            $this->view->assign( 'errorRows', $errors );
            $this->view->assign( 'headerRow', $handler->getHeader() );
            $this->view->assign( 'totalCount', $getAllResultsCount );
            $this->view->assign( 'totalOrderCount', $getBuildersCount );
            $this->view->assign( 'caution', $caution);
            $this->view->assign( 'csvCaution', $csvCaution);
            $this->view->assign( 'duplication', $duplication);
            $this->view->assign( 'duplicateId', $duplicateId);
            $this->view->assign( 'entId', $this->app->authManager->getUserInfo()->EnterpriseId);

            $clone_file = $file;
            $clone_file['tmp_name'] = $this->makeTempFileName();
            $this->view->assign( 'load_file_info', Json::encode($clone_file) );
        }
        else {
            $success = false;

            $this->view->assign( 'colSchema', new \stdClass() );
            $this->view->assign( 'errorRows', array(
                    new CoralCsvHandlerLine(
                    array( 0 => array( 'ファイル形式' => 'ファイル形式が適切ではありません。CSVファイルを登録してください' ) ),
                    0,
                    CoralCsvHandlerLine::TYPE_ERROR
                    ) )
            );
        }

        if( $success || $success_by_force ) {
            // エラーなし
            $this->setPageTitle( 'CSV登録(修正用)確認' );

            // インポート結果をセッションオブジェクトに退避
            $db_rows = array();
            foreach( $handler->getBuilders() as $builder ) {
                $db_rows[] = $builder->build();
            }

//            //ハイフン正規化
//            $db_rows = $this->hyphenNormalize($db_rows);

            $session = new Container('OrderEditAction');
            $session->dbRows = $db_rows;

            // エラーがあっても[CSV一括登録区分(1：エラーがあってもOK分のみ登録)]であれば、強制的に登録実行を許す
            if (!$success_by_force) {
                if (isset($session->skipBlockList)) { unset($session->skipBlockList); }
                $this->view->assign( 'success_by_force', false );
            }
            else {
                $session->skipBlockList = $this->_makeSkipBlockList($db_rows, $handler->getExceptions());
                $this->view->assign( 'success_by_force', true );
            }


            // 再与信対象となる注文IDをビューへアサイン
            $offset = 0;
            $saiyoshinOrderIdList = array();
            foreach( $session->dbRows as $db_row ) {

                // CSV一括登録区分(1：エラーがあってもOK分のみ登録)対応
                $offset++;
                if (isset($session->skipBlockList) && in_array($offset, $session->skipBlockList)) {
                    continue;
                }

                // 再与信判断用に元の注文データを取得
                $order = $this->_getOrderForEdit($db_row['order']['OrderId']);

                // 再与信対象か？調査
                if ($this->IsSaiyoshin($db_row, $order)) {
                    $saiyoshinOrderIdList[] = $db_row['order']['OrderId'];
                }
            }

            $this->view->assign( 'saiyoshinOrderIdList', $saiyoshinOrderIdList );

        } else {
            // エラーあり
            $this->setPageTitle( 'CSV登録(修正用)エラー' );
            $this->view->assign( 'success_by_force', false );
        }

        return $this->view;
    }

    /**
     * saveEditCsvアクション。一括注文修正データをDBへ保存する
     *
     */
    public function saveEditCsvAction()
    {
        $params = $this->getParams();

        $session = new Container('OrderEditAction');

        if( isset( $session->dbRows ) ) {

            // ユーザーIDの取得
            $obj = new \models\Table\TableUser($this->app->dbAdapter);
            getUserInfoForMember($this->app, $userClass, $seq);
            $userId = $obj->getUserId($userClass, $seq);

            $isSaiyoshinOn = false;

            $order_seqs = array();
            $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
            try {
                // 件数分処理をする
                $offset = 0;
                foreach( $session->dbRows as $db_row ) {

                    // CSV一括登録区分(1：エラーがあってもOK分のみ登録)対応
                    $offset++;
                    if (isset($session->skipBlockList) && in_array($offset, $session->skipBlockList)) {
                        continue;
                    }

                    // 注文データ一式を格納した連想配列を元にデータをDBへ保存(修正更新)する
                    $retIsSaiyoshin = false;
                    $orderSeq = $this->modifyCsvOrderData($db_row, $userId, $retIsSaiyoshin);
                    $isSaiyoshinOn |= $retIsSaiyoshin;

                    $order_seqs[] = $orderSeq;// 修正対象の注文SEQを配列へ保管
                }

                 $this->app->dbAdapter->getDriver()->getConnection()->commit();
            }
            catch(\Exception $err) {
                $this->app->dbAdapter->getDriver()->getConnection()->rollback();
                throw $err;
            }

            // 注文修正メールを送信
            if (!empty($order_seqs)) {
                try {
                    CoralMail::create( $this->app->dbAdapter, $this->app->smtpServer )->SendOrderModifiedMail(
                        $this->app->authManager->getUserInfo()->EnterpriseId, $order_seqs, $userId );
                }
                catch(CoralMailException $e) {
                    // CoralMail内での例外のみ捕捉
                    $this->flashMessenger()->addMessage('メール送信失敗');
                }
            }
        }

        return $this->_redirect( 'order/completeeditCsv' . (($isSaiyoshinOn) ? '/is_recredited/true' : '') );
    }

    /**
     * completeeditcsvアクション。一括注文修正の完了画面を表示する
     *
     */
    public function completeeditCsvAction()
    {
        $params = $this->getParams();

        $this->setPageTitle( '注文修正完了（CSV一括修正）');
        if (isset($params['is_recredited'])) {
            $this->view->assign( 'is_recredited', true );
        }
        $this->setTemplate('completeedit');
        return $this->view;
    }

    /**
     * 注文データ一式を格納した連想配列を元にデータをDBへ保存(修正更新)する
     *
     * @param array $data 注文データ
     * @param int $userId ユーザID
     * @param bool $retIsSaiyoshin 再与信対象か？
     * @return int 注文SEQ
     */
    private function modifyCsvOrderData($data, $userId, &$retIsSaiyoshin)
    {
        // 再与信判断用に元の注文データを取得しておく
        $order = $this->_getOrderForEdit($data['order']['OrderId']);

        // 再与信対象か？調査
        $isSaiyoshin = $this->IsSaiyoshin($data, $order);

        $order_table = new TableOrder($this->app->dbAdapter);
        $site_table = new TableSite($this->app->dbAdapter);
        $customer_table = new TableCustomer($this->app->dbAdapter);
        $postalcodeTable = new TablePostalCode($this->app->dbAdapter);
        $delivTable = new TableDeliveryDestination($this->app->dbAdapter);
        $itemsTable = new TableOrderItems($this->app->dbAdapter);

        $u_order = $order_table->find($order['OrderSeq'])->current();
        $u_customer = $customer_table->findCustomer(array('OrderSeq' => $order['OrderSeq']))->current();
        $siteInfo = $site_table->findSite($data['order']['SiteId'])->current();

        // 再与信対象なら、T_Orderの不要フィールドはクリアする
        if ($isSaiyoshin) {
            // 再与信向けに指定のT_Order行の初期化を実行する
            $u_order = $this->_cleanupOrderForReCredit($u_order);
            $u_order['DataStatus'] = 11;		// 社内与信実行待ちに戻す
            $u_order['Incre_Note'] = sprintf("(事業者側修正により再与信戻し[%s])\n----\n%s",  date('Y-m-d H:i:s'), $u_order['Incre_Note']);// 備考にメッセージ追加
            $u_order['DailySummaryFlg'] = 0;
            $u_order['PendingReasonCode'] = null;
            $u_order['CreditReplyDate'] = null;
            $u_order['OemClaimTransDate'] = null;
            $u_order['CreditNgHiddenFlg'] = 0;
        }

        // 注文情報の更新
        $u_order['ReceiptOrderDate'] = $data['order']['ReceiptOrderDate'];
        $u_order['UseAmount'] = $data['order']['UseAmount'];
        $u_order['SiteId'] = $data['order']['SiteId'];
        $u_order['Ent_OrderId'] = $data['order']['Ent_OrderId'];
        $u_order['Ent_Note'] = $data['order']['Ent_Note'];
        $u_order['ServiceExpectedDate'] = ($data['order']['ServiceExpectedDate'] == '') ? null : $data['order']['ServiceExpectedDate'];
        $u_order['ServiceTargetClass'] = $siteInfo['ServiceTargetClass'];
        if(isset($data['customer']['ClaimSendingClass'])) {
            $u_order['ClaimSendingClass'] = ($data['customer']['ClaimSendingClass'] == '0') ? 11 : 12;
            $selfBillingFlg = $this->app->dbAdapter->query(" SELECT SelfBillingFlg FROM T_Site WHERE SiteId = " . $data['order']['SiteId'])->execute(null)->current()['SelfBillingFlg'];
            $u_order['ClaimSendingClass'] = ($selfBillingFlg == 0) ? 21 : $u_order['ClaimSendingClass'];
        } else {
            unset($u_order['ClaimSendingClass']);
        }
        $u_order['AnotherDeliFlg'] = $data['order']['AnotherDeliFlg'];
        if ($u_order['AnotherDeliFlg'] == 1) {
            if (($data['customer']['NameKj'] == $data['delivery']['DestNameKj']) &&
                ($data['customer']['NameKn'] == $data['delivery']['DestNameKn']) &&
                ($data['customer']['PostalCode'] == $data['delivery']['DestPostalCode']) &&
                ($data['customer']['PrefectureCode'] == $data['delivery']['PrefectureCode']) &&
                ($data['customer']['UnitingAddress'] == $data['delivery']['DestUnitingAddress']) &&
                ($data['customer']['Phone'] == $data['delivery']['DestPhone'])
               ) {
                // 別配送先
                $u_order['AnotherDeliFlg'] = 0;
            }
        }
        $u_order['UpdateId'] = $userId;
        $order_table->saveUpdate($u_order, $order['OrderSeq']);// (更新実施)

        // 購入者情報の更新
        $u_customer['PostalCode'] = $data['customer']['PostalCode'];
        $u_customer['UnitingAddress'] = $data['customer']['UnitingAddress'];
        $u_customer['NameKj'] = $data['customer']['NameKj'];
        $u_customer['NameKn'] = $data['customer']['NameKn'];
        $u_customer['Phone'] = $data['customer']['Phone'];
        $u_customer['MailAddress'] = $data['customer']['MailAddress'];
        $u_customer['Occupation'] = $data['customer']['Occupation'];
        $u_customer['EntCustSeq'] = $data['customer']['EntCustSeq'];
        $u_customer['CorporateName'] = $data['customer']['CorporateName'];
        $u_customer['DivisionName'] = $data['customer']['DivisionName'];
        $u_customer['CpNameKj'] = $data['customer']['CpNameKj'];
        $u_customer['PrefectureCode'] = $data['customer']['PrefectureCode'];
        $u_customer['PrefectureName'] = $data['customer']['PrefectureName'];
        $u_customer['AddressKn'] = $postalcodeTable->getAddressKanaStr($data['customer']['PostalCode']);
        $u_customer['UpdateId'] = $userId;
        $customer_table->saveUpdate($u_customer, $u_customer['CustomerId']);// (更新実施)

        // 配送先を削除
        $sql = 'SELECT DeliDestId FROM T_OrderItems WHERE DeliDestId <> -1 AND OrderSeq =:OrderSeq';
        $ary_delidestid = array();
        $ri = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $order['OrderSeq']));
        foreach ($ri as $row) {
            $ary_delidestid[] = $row['DeliDestId'];
        }
        $sql = 'DELETE FROM T_DeliveryDestination WHERE DeliDestId IN (' . implode(',', $ary_delidestid) . ')';
        $this->app->dbAdapter->query($sql)->execute(null);

        // 注文商品を削除
        $sql = 'DELETE FROM T_OrderItems WHERE OrderSeq = :OrderSeq';
        $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $order['OrderSeq']));

        // 商品数分だけ商品と配送先データを生成
        foreach( $data['order_items'] as $item ) {
            // 先に配送先確定
            $delivId = -1;
            if( $item['DataClass'] == 1 ) {
                // INSERT(T_DeliveryDestination)
                $prm_deliv = array(
                    'DestNameKj' => $data['delivery']['DestNameKj'],
                    'DestNameKn' => $data['delivery']['DestNameKn'],
                    'PostalCode' => $data['delivery']['DestPostalCode'],
                    'PrefectureCode' => $data['delivery']['PrefectureCode'],
                    'PrefectureName' => $data['delivery']['PrefectureName'],
                    'UnitingAddress' => $data['delivery']['DestUnitingAddress'],
                    'Phone' => $data['delivery']['DestPhone'],
                    'RegistId' => $userId,
                    'UpdateId' => $userId,
                );
                $delivId = $delivTable->newRow($prm_deliv);
            }

            $unitprice = 0;
            if      (isset($item['UnitPrice'])        ) { $unitprice = $item['UnitPrice']; }
            else if (isset($item['UnitPriceCarriage'])) { $unitprice = $item['UnitPriceCarriage']; }
            else if (isset($item['UnitPriceCharge'])  ) { $unitprice = $item['UnitPriceCharge']; }
            else if (isset($item['UnitPriceTax'])     ) { $unitprice = $item['UnitPriceTax']; }

            $cvtitem = array(
                    'ItemNameKj' => $item['ItemNameKj'],
                    'UnitPrice'  => $unitprice,
                    'ItemNum'    => (double)$item['ItemNum'],
                    'DataClass'  => $item['DataClass'],
            );

            //消費税率設定
            if($item['DataClass'] ==1 && $item['TaxRate'] != NULL){
                $cvtitem['TaxRate'] = $item['TaxRate'];
                $cvtitem['TaxrateNotsetFlg'] = 0;
            }else{
                $cvtitem['TaxRate'] = NULL;
                $cvtitem['TaxrateNotsetFlg'] = 1;
            }

            $sumMoney = ($unitprice * (double)$item['ItemNum']);
            if ($this->app->authManager->getUserInfo()->UseAmountFractionClass == 0) { $sumMoney = floor( $sumMoney ); }
            if ($this->app->authManager->getUserInfo()->UseAmountFractionClass == 1) { $sumMoney = round( $sumMoney ); }
            if ($this->app->authManager->getUserInfo()->UseAmountFractionClass == 2) { $sumMoney = ceil(  $sumMoney ); }
            $cvtitem['SumMoney'] = $sumMoney;

            $orderItemId = $itemsTable->newRow( $order['OrderSeq'], $delivId, $cvtitem['DataClass'], array_merge( $cvtitem,
                array(
                        'CombinedTargetFlg' => 1,
                        'RegistId'      => $userId,
                        'UpdateId'      => $userId,
                ))
            );
        }

        // 今作成した注文データのサマリーを作成する
        $summaries = new TableOrderSummary($this->app->dbAdapter);
        $summaries->updateSummary( $order['OrderSeq'], $userId );

        // 加盟店顧客、管理顧客の紐付け
        $logicOr = new LogicOrderRegister($this->app->dbAdapter);
        $logicOr->updateCustomer($order['OrderSeq'], $userId);

        // 注文_会計テーブルの更新
        $mdlao = new ATableOrder ( $this->app->dbAdapter );
        if ($isSaiyoshin) {
            // 再与信戻しの場合はフラグ更新
            if (! isset ( $data ['order'] ['CreditTransferRequestFlg'] )) {
                // 口座振替申込区分が空の場合
                $udata = array (
                        'DefectFlg' => 0,
                        'DefectNote' => null,
                        'DefectCancelPlanDate' => null
                );
            } else {
                // 口座振替申込区分が値有の場合
                $udata = array (
                        'DefectFlg' => 0,
                        'DefectNote' => null,
                        'DefectCancelPlanDate' => null,
                        'CreditTransferRequestFlg' => $data ['order'] ['CreditTransferRequestFlg']
                );
            }
            $mdlao->saveUpdate ( $udata, $order ['OrderSeq'] );
        } else {
            if (isset ( $data ['order'] ['CreditTransferRequestFlg'] )) {
                // 口座振替申込区分
                $udata = array (
                        'CreditTransferRequestFlg' => $data ['order'] ['CreditTransferRequestFlg']
                );
                $mdlao->saveUpdate ( $udata, $order ['OrderSeq'] );
            }

        }

        // 注文履歴へ登録
        $history = new CoralHistoryOrder($this->app->dbAdapter);
        $history->InsOrderHistory($order['OrderSeq'], ($isSaiyoshin) ? 14 : 15, $userId);

        $retIsSaiyoshin = $isSaiyoshin;

        return $order['OrderSeq'];
    }

    /**
     * 再与信対象か？
     *
     * @param array $data 注文データ
     * @param array $order 注文情報（関数[_getOrderForEdit]呼出し結果）
     * @return boolean true:再与信対象／false:再与信対象外
     */
    protected function IsSaiyoshin($data, $order) {

        // ※検証項目[NameKj／PostalCode／UnitingAddress／Phone／MailAddress]
        $regnamekj = LogicNormalizer::create( LogicNormalizer::FILTER_FOR_NAME )->normalize( $data['customer']['NameKj'] );
        $regunitingaddress = LogicNormalizer::create( LogicNormalizer::FILTER_FOR_ADDRESS )->normalize( $data['customer']['UnitingAddress'] );
        $ordername = LogicNormalizer::create( LogicNormalizer::FILTER_FOR_NAME )->normalize( $order['DestNameKj'] );
        $dataname = LogicNormalizer::create( LogicNormalizer::FILTER_FOR_NAME )->normalize( $data['delivery']['DestNameKj'] );
        $orderaddress = LogicNormalizer::create( LogicNormalizer::FILTER_FOR_ADDRESS )->normalize( $order['DestUnitingAddress'] );
        $dataaddress = LogicNormalizer::create( LogicNormalizer::FILTER_FOR_ADDRESS )->normalize( $data['delivery']['DestUnitingAddress'] );
        $orderphone = LogicNormalizer::create( LogicNormalizer::FILTER_FOR_TEL )->normalize( $order['Phone'] );
        $dataphone = LogicNormalizer::create( LogicNormalizer::FILTER_FOR_TEL )->normalize( $data['customer']['Phone'] );
        $orderdestphone = LogicNormalizer::create( LogicNormalizer::FILTER_FOR_TEL )->normalize( $order['DestPhone'] );
        $datadestphone = LogicNormalizer::create( LogicNormalizer::FILTER_FOR_TEL )->normalize( $data['delivery']['DestPhone'] );
        if ($order['RegNameKj'] != $regnamekj) { return true; }
        if ($order['PostalCode'] != $data['customer']['PostalCode']) { return true; }
        if ($order['RegUnitingAddress'] != $regunitingaddress) { return true; }
        if ($orderphone != $dataphone) { return true; }

        //メールアドレスが必須の時のみ
        if($this->getMailRequire($data['order']['SiteId'])){
            if ($order['MailAddress'] != '' && $data['customer']['MailAddress'] != '') {
                if ($order['MailAddress'] != $data['customer']['MailAddress']) { return true; }
            }
        }
        // 再与信となるかどうか、変更をチェック(配送先に対して) ※検証項目[DestNameKj／DestPostalCode／DestUnitingAddress／DestPhone]
        if ($ordername != $dataname) { return true; }
        if ($order['DestPostalCode'] != $data['delivery']['DestPostalCode']) { return true; }
        if ($orderaddress != $dataaddress) { return true; }
        if ($orderdestphone != $datadestphone) { return true; }

        // 再与信となるかどうか、変更をチェック(利用額に対して) ※検証項目[UseAmount]
        if ((int)$data['order']['UseAmount'] - (int)$order['UseAmount'] > 2999) { return true; }

        return false;// 上記の何れにも該当しないときはfalseを戻す
    }

    /**
     * 指定注文IDのデータを注文修正向けに取得する
     *
     * @access protected
     * @param int $orderId 注文ID
     * @param null|boolean $use_seq $orderIdを注文Seqとして扱う場合はtrueを指定。省略時はfalse
     * @return array 注文データ
     */
    protected function _getReOrder($orderId)
    {
        $prm = array();

        $sql = <<<EOQ
SELECT O.OrderSeq
,      DATE_FORMAT(O.ReceiptOrderDate, '%Y/%m/%d') AS O_ReceiptOrderDate
,      O.SiteId AS O_SiteId
,      O.Ent_OrderId AS O_Ent_OrderId
,      O.Ent_Note AS O_Ent_Note
,      O.T_OrderAutoCreditJudgeClass AS cmst87
,      DATE_FORMAT(O.ServiceExpectedDate, '%Y/%m/%d') AS O_ServiceExpectedDate
,      C.NameKj AS C_NameKj
,      C.NameKn AS C_NameKn
,      C.PostalCode AS C_PostalCode
,      C.UnitingAddress AS C_Address
,      C.Phone AS C_Phone
,      C.MailAddress AS C_MailAddress
,      C.EntCustId AS C_EntCustId
,      S.DestNameKj AS D_NameKj
,      S.DestNameKn AS D_NameKn
,      S.DestPostalCode AS D_PostalCode
,      S.DestUnitingAddress AS D_Address
,      S.DestPhone AS D_Phone
,      O.T_OrderClass AS O_T_OrderClass
,      C.Occupation AS C_Occupation
,      C.CorporateName AS C_CorporateName
,      C.DivisionName AS C_DivisionName
,      C.CpNameKj AS C_CpNameKj
,      O.ClaimSendingClass AS O_ClaimSendingClass
,      O.AnotherDeliFlg AS AnotherDeliFlg
,      A.CreditTransferRequestFlg AS O_CreditTransferRequestFlg
FROM   T_Order O
       STRAIGHT_JOIN T_Customer C ON (C.OrderSeq = O.OrderSeq)
       STRAIGHT_JOIN T_OrderSummary S ON (S.OrderSeq = O.OrderSeq)
       STRAIGHT_JOIN AT_Order A ON (A.OrderSeq = O.OrderSeq)
EOQ;
        $sql .= " WHERE  O.EnterpriseId = :EnterpriseId ";
        $prm += array(':EnterpriseId' => $this->app->authManager->getUserInfo()->EnterpriseId);

        // 注文IDで問い合わせ
        $sql .= " AND  O.OrderId = :OrderId ";
        $prm += array(':OrderId' => strtoupper($orderId));

        $stm = $this->app->dbAdapter->query($sql);

        $row = $stm->execute($prm)->current();

        // 商品明細・送料等
        $itemsql = "SELECT ItemNameKj, UnitPrice, ItemNum, TaxRate, DataClass FROM T_OrderItems WHERE OrderSeq = :OrderSeq AND ValidFlg = 1 ORDER BY OrderItemId ";
        $items = ResultInterfaceToArray($this->app->dbAdapter->query($itemsql)->execute(array(':OrderSeq' => $row['OrderSeq'])));

        // 表示用小数点桁数取得
        $sql = " SELECT e.DispDecimalPoint FROM T_Order o INNER JOIN T_Enterprise e ON o.EnterpriseId = e.EnterpriseId WHERE o.OrderSeq = :OrderSeq ";
        $dispDecimalPoint = (int)$this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $row['OrderSeq']))->current()['DispDecimalPoint'];

        $cnt = 0;

        $itemsCount = 0;
        if (!empty($items)) {
            $itemsCount = count($items);
        }
        for($i = 0; $i < $itemsCount; $i++) {
            if($items[$i]['DataClass'] == '1') {
                if ($cnt == 0) {
                    $itemlist = "[";
                } else {
                    $itemlist .= ",";
                }
                $wkList['i_itemnamekj'] = $items[$i]['ItemNameKj'];
                $wkList['i_unitprice'] = $items[$i]['UnitPrice'];
                $wkList['i_itemnum'] = number_format($items[$i]['ItemNum'], $dispDecimalPoint);
                $wkList['i_taxrate'] = $items[$i]['TaxRate'];
                $itemlist = $itemlist . Json::encode($wkList);
//                $itemlist .= '{"i_itemnamekj":"' . $items[$i]['ItemNameKj'] . '","i_unitprice":"' . $items[$i]['UnitPrice'] . '","i_itemnum":"' . number_format($items[$i]['ItemNum'], $dispDecimalPoint) . '"}' ;
                $cnt += 1;
            } elseif($items[$i]['DataClass'] == '2') {
                $row['I_UnitPrice___ORDER_ITEM_CARRIAGE__'] = $items[$i]['UnitPrice'];
            } elseif($items[$i]['DataClass'] == '3') {
                $row['I_UnitPrice___ORDER_ITEM_CHARGE__'] = $items[$i]['UnitPrice'];
            } elseif($items[$i]['DataClass'] == '4') {
                $row['I_UnitPrice___ORDER_ITEM_TAXCLASS__'] = $items[$i]['UnitPrice'];
            }
        }
        $row['I_ItemList'] = $itemlist . ']';

        return ($row) ? $row : null;
    }

    /**
     * defectlistアクション。入力不備注文リストを表示する
     */
    public function defectlistAction()
    {

        $this->addStyleSheet('../../oemmember/css/defectlist.css');

        $this->setPageTitle( '保留注文リスト');

        $params = $this->getParams();

        // 対象リスト取得
        $sql =<<<EOQ
SELECT  o.OrderId
      , o.ReceiptOrderDate
      , c.NameKj
      , c.Phone
      , c.PostalCode
      , c.UnitingAddress
      , o.Ent_OrderId
      , ao.DefectNote
FROM  T_Order o
      INNER JOIN AT_Order ao ON o.OrderSeq = ao.OrderSeq
      INNER JOIN T_Customer c ON o.OrderSeq = c.OrderSeq
WHERE 1 = 1
AND   ao.DefectFlg = 1
AND   o.Cnl_Status = 0
AND   o.DataStatus < 31
AND   o.EnterpriseId = :EnterpriseId
EOQ;
        $prm = array(
            ':EnterpriseId' => $this->app->authManager->getUserInfo()->EnterpriseId,
        );
        $ri = $this->app->dbAdapter->query($sql)->execute($prm);

        // Viewにデータを渡す
        $this->view->assign("list", $ri);

        return $this->view;

    }
}
