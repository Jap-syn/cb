<?php
namespace oemmember\Controller;

use Coral\Base\Reflection\BaseReflectionUtility;
use Coral\Coral\Controller\CoralControllerAction;
use models\Logic\LogicCancel;
use models\Table\TableCode;
use Zend\Db\Adapter\Adapter;
use Zend\Config\Reader\Ini;
use Zend\Session\Container;
use oemmember\Application;
use oemmember\classes\SearchUtility;
use oemmember\classes\SearchExpressionInfo;
use models\Table\TableSite;
use models\Table\TableDeliMethod;
use models\Table\TableOrderItems;
use models\Table\TableSystemProperty;
use models\Table\TableOrder;
use models\Table\TableUser;
use models\Table\ATableOrder;
use models\Table\TableEnterprise;
use models\Logic\LogicTemplate;
use models\Logic\LogicDeliveryMethod;
use Coral\Base\BaseGeneralUtils;
use Coral\Base\Controller\BaseControllerUtility;
use Coral\Coral\CoralPager;
use Coral\Coral\Mail\CoralMail;
use Coral\Coral\History\CoralHistoryOrder;
use models\Table\TableOrderAddInfo;
use models\Table\TableSiteSbpsPayment;

class SearchController extends CoralControllerAction {
	/**
	 * SELECT要素によるリスト選択項目で「未選択」を表すフィールド値の定数
	 *
	 */
	const UNSELECT_LIST_ITEM = -99;

	const RESULT_CSV_PREFIX = 'search_result';

	/**
	 * 任意注文番号検索向け追加オプション：「数値として検索」オプションのフィールド名
	 */
	const SEARCH_OPT_EXP_EOID_AS_NUMERIC = 'Ent_OrderId_SearchAsNumeric';

	/**
	 * 任意注文番号検索向け「数値として検索」オプションを永続化するためのcookieベースキー
	 */
	const SEARCH_OPT_EXP_EOID_AS_NUMERIC_CKEY = 'entOrderIdSearchAsNumeric';

	/**
	 * V_OrderSearchを抽出
	 */
	const V_ORDER_SEARCH = <<<EOQ
    SELECT
        O.OrderSeq AS OrderSeq,
        O.ReceiptOrderDate AS ReceiptOrderDate,
        O.DataStatus AS DataStatus,
        O.EnterpriseId AS EnterpriseId,
        O.SiteId AS SiteId,
        O.OrderId AS OrderId,
        O.Ent_OrderId AS Ent_OrderId,
        O.Ent_Note AS Ent_Note,
        O.UseAmount AS UseAmount,
        O.RegistDate AS RegistDate,
        IFNULL(O.OutOfAmends, 0) AS OutOfAmends,
        (CASE
            WHEN (O.Dmi_Status = 1 OR (O.Dmi_Status IS NULL AND O.Incre_Status = 1)) THEN 1
            WHEN (O.Dmi_Status = -1 OR O.Incre_Status = - 1) THEN - 1
            ELSE 0
        END) AS IncreStatus,
        S.CarriageFee AS CarriageFee,
        S.ChargeFee AS ChargeFee,
        O.Chg_ExecDate AS Chg_ExecDate,
        O.Cnl_CantCancelFlg AS Cnl_CantCancelFlg,
        O.Cnl_Status AS Cnl_Status,
        O.AnotherDeliFlg AS AnotherDeliFlg,
        O.CombinedClaimTargetStatus AS CombinedClaimTargetStatus,
        O.P_OrderSeq,
        O.CombinedClaimParentFlg AS CombinedClaimParentFlg,
        O.ClaimSendingClass AS ClaimSendingClass,
        O.ServiceExpectedDate AS ServiceExpectedDate,
        C.CustomerId AS CustomerId,
        C.NameKj AS NameKj,
        C.NameKn AS NameKn,
        C.PostalCode AS PostalCode,
        C.UnitingAddress AS UnitingAddress,
        C.Phone AS Phone,
        C.MailAddress AS MailAddress,
        C.EntCustId AS EntCustId,
        S.DestNameKj AS DestNameKj,
        S.DestNameKn AS DestNameKn,
        S.DestPostalCode AS DestPostalCode,
        S.DestUnitingAddress AS DestUnitingAddress,
        S.DestPhone AS DestPhone,
        S.OrderItemId AS OrderItemId,
        S.OrderItemNames AS OrderItemNames,
        S.ItemNameKj AS ItemNameKj,
        S.ItemCount AS ItemCount,
        S.Deli_JournalIncDate AS Deli_JournalIncDate,
        S.Deli_DeliveryMethod AS Deli_DeliveryMethod,
        S.Deli_DeliveryMethodName AS Deli_DeliveryMethodName,
        S.Deli_JournalNumber AS Deli_JournalNumber,
        L.CancelDate AS CancelDate,
        L.CancelReason AS CancelReason,
        L.ApprovalDate AS ApprovalDate,
        L.CancelReasonCode AS CancelReasonCode,
        P.ExecScheduleDate AS ExecScheduleDate,
        AO.ExtraPayKey AS ExtraPayKey,
        CL.ClaimDate AS ClaimDate,
        (CASE
            WHEN ISNULL(O.Cnl_ReturnSaikenCancelFlg) THEN 0
            ELSE O.Cnl_ReturnSaikenCancelFlg
        END) AS Cnl_ReturnSaikenCancelFlg,
        (CASE
            WHEN (O.Cnl_Status = 0) THEN 0
            WHEN
                ((O.Cnl_Status = 1)
                    AND (IFNULL(O.Cnl_ReturnSaikenCancelFlg, 0) = 0))
            THEN
                1
            WHEN
                ((O.Cnl_Status = 2)
                    AND (IFNULL(O.Cnl_ReturnSaikenCancelFlg, 0) = 0))
            THEN
                2
            WHEN
                ((O.Cnl_Status = 1)
                    AND (O.Cnl_ReturnSaikenCancelFlg = 1))
            THEN
                11
            WHEN
                ((O.Cnl_Status = 2)
                    AND (O.Cnl_ReturnSaikenCancelFlg = 1))
            THEN
                12
        END) AS RealCancelStatus,
        (CASE
            WHEN
                (SELECT MAX(AlertSign) FROM T_StagnationAlert WHERE AlertClass = 0 AND OrderSeq = O.OrderSeq AND ValidFlg = 1)
            THEN
                1
            ELSE 0
        END) AS Deli_JournalNumberAlert,
        (CASE
            WHEN
                (SELECT MAX(AlertSign) FROM T_StagnationAlert WHERE AlertClass = 1 AND OrderSeq = O.OrderSeq AND ValidFlg = 1)
            THEN
                1
            ELSE 0
        END) AS ArrivalConfirmAlert,
        (CASE
            WHEN CL.ClaimedBalance <= 0 THEN 1
            WHEN CL.ClaimedBalance < CL.ClaimAmount THEN 2
            WHEN CL.ClaimedBalance >= CL.ClaimAmount or
                 (CL.ClaimId is null and O.ClaimSendingClass >= 12 and O.DataStatus = 41 and O.ConfirmWaitingFlg = 1) THEN 3
            ELSE 4
        END) AS IsWaitForReceipt,
        RC.ReceiptDate as ReceiptDate,
        RC.ReceiptClass as ReceiptClass,
        RC.ReceiptProcessDate as ReceiptProcessDate,
        IFNULL(EC.RequestStatus, 0) as RequestStatus,
        EC.RequestSubStatus as RequestSubStatus,
        EC.RequestCompDate as RequestCompDate,
        CC.CreditTransferFlg AS CreditTransferFlg,
        AO.CreditTransferRequestFlg AS CreditTransferRequestFlg,
        CASE CH.CreditTransferMethod
           WHEN '3' THEN '1'
           ELSE ''
        END AS CreditTransferMethod1,
        CASE CH.CreditTransferMethod
           WHEN '1' THEN '1'
           ELSE ''
        END AS CreditTransferMethod2,
        E.AppFormIssueCond AS AppFormIssueCond,
        O.CloseReason AS CloseReason
    FROM
        T_Order O
        INNER JOIN T_Customer C
                ON C.OrderSeq = O.OrderSeq
        INNER JOIN T_EnterpriseCustomer EC
                ON EC.EntCustSeq = C.EntCustSeq
        INNER JOIN T_OrderSummary S
                ON S.OrderSeq = O.OrderSeq
        LEFT  JOIN T_Cancel L
                ON L.OrderSeq = O.OrderSeq
               AND L.ValidFlg = 1
        LEFT  JOIN T_PayingControl P
                ON P.Seq = O.Chg_Seq
        LEFT  JOIN T_ClaimControl CL
                ON CL.OrderSeq = O.P_OrderSeq
        LEFT  JOIN T_ReceiptControl RC
                ON RC.ReceiptSeq = CL.LastReceiptSeq
        LEFT  JOIN AT_Order AO
                ON AO.OrderSeq = O.OrderSeq
        LEFT  JOIN T_ClaimControl CC
                ON CC.OrderSeq = O.P_OrderSeq
        LEFT  JOIN T_ClaimHistory CH ON (
            O.P_OrderSeq = CH.OrderSeq AND
            CH.Seq = (
                SELECT MAX(Seq)
                FROM T_ClaimHistory CH2
                WHERE CH2.OrderSeq = O.P_OrderSeq
                AND CH2.ClaimPattern = 1
                AND CH2.ValidFlg = 1
            )
        )
        INNER JOIN T_Enterprise E
                ON E.EnterpriseId = O.EnterpriseId
EOQ;
	/**
	 * ビューコンポーネントのルートディレクトリパス
	 *
	 * @var string
	 */
	protected $_componentRoot = './application/views/components';

	/**
	 * DBアダプタ
	 *
	 * @var Abstract
	 */
	protected $dbAdapter;

	/**
	 * ログイン中の事業者アカウント情報
	 *
	 * @var mixed
	 */
	protected $userInfo;

	/**
	 * 検索実行用のSELECT文字列
	 *
	 * @var string
	 */
	protected $_select;

	/**
	 * 検索(件数確認)実行用のSELECT文字列
	 *
	 * @var string
	 */
	protected $_selectForCount;

	/**
	 * アプリケーションオブジェクト
	 * @var Application
	 */
	private $app;

	/**
	 * ビューに割り当てる、select要素向けの連想配列を格納する配列
	 *
	 * @var array
	 */
	protected $_lists;

	protected $_ignoreColumns;

	protected $current_page = 1;

	/**
	 * 任意注文番号検索向け「数値として検索」オプションを永続化するためのcookieキー
	 *
	 * @access protected
	 * @var string
	 */
	protected $entOrderIdSearchAsNumKey;

	/**
	 * クラス固有の初期化処理
	 */
	protected function _init() {

	    $app = Application::getInstance();

		$this->dbAdapter = $app->dbAdapter;

		// ビューへスタイルシートとJSを追加
		$this
			->addJavaScript( '../../js/json+.js' )
			->addJavaScript( '../../js/prototype.js' )
			->addJavaScript( '../../js/bytefx.js' )
			->addJavaScript( '../../js/corelib.js' )
			->addJavaScript( '../../js/base.ui.js')
			->addJavaScript( '../../js/base.ui.datepicker.js')
			->addJavaScript( '../../js/json_format.js' )
			->addStyleSheet( '../../oemmember/css/members.css' )
			->addStyleSheet( '../../oemmember/css/index.css' )
			->addStyleSheet( '../../oemmember/css/search.css' )
			->addStyleSheet( '../../css/base.ui.datepicker.css' );

        // メニュー情報をマスタから取得
        $menu_info = $app->getMenuInfo();
        foreach( $menu_info as $key => $info ) {
             $this->view->assign( $key, $info );
        }

		// ログイン中のアカウント情報取得
		$this->userInfo = $app->authManager->getUserInfo();

		// ベースクエリを構築
		// レスポンス対策のため、Viewから隔離(20150902_2242_suzuki_h)
		$view = self::V_ORDER_SEARCH . " WHERE O.EnterpriseId = " . $this->userInfo->EnterpriseId . ' ';

        // 件数カウント用ベースクエリ構築（10.4.6 eda）
		$this->_selectForCount = " SELECT COUNT(*) AS TotalRows FROM ($view) V WHERE 1 = 1 ";

        $this->_lists = array();

        // サイト一覧
        $siteTable = new TableSite($this->dbAdapter);
        $sites = array( self::UNSELECT_LIST_ITEM => '-' );
        $ri = $siteTable->getAll($this->userInfo->EnterpriseId);
        foreach( $ri as $row ) {
            $sites[ $row['SiteId'] ] = $row['SiteNameKj'];
            $siteids[] = $row['SiteId'];
        }
        $this->_lists['SiteId'] = $sites;

        // 審査結果リスト
        $this->_lists['IncreStatus'] = array(
                self::UNSELECT_LIST_ITEM => '-',
                0 => '与信中',
                -1 => 'NG',
                1 => 'OK'
        );

        // 配送方法
        $delilogic = new LogicDeliveryMethod( $this->dbAdapter );
        $this->_lists['Deli_DeliveryMethod'] = $delilogic->getEnterpriseDeliveryMethodListAllOrderBy($this->userInfo->EnterpriseId);

        // キャンセル状態リスト
        $this->_lists['RealCancelStatus'] = array(
                0 => '未キャンセル',
                1 => '申請中',
                2 => 'キャンセル済',
                11 => '返却依頼中',
                12 => '返却済'
        );

        // 入金方法
        $this->_lists['Rct_ReceiptMethod'] = array (
                1 => 'コンビニ',
                2 => '郵便局',
                3 => '銀行',
                4 => 'LINE Pay'
        );

        // 同梱/別送
        $this->_lists['ClaimSendingClass'] = array(
                21 => '別送',
                11 => '同梱'
        );

        // 伝票登録
        $this->_lists['Deli_JournalNumberAlert'] = array(
            1 => '長期伝票番号未登録'
        );

        // 保証有無
        $this->_lists['OutOfAmends'] = array(
            1 => '保証無し',
            0 => '保証有り'
        );

        // キャンセル理由
        $sql  = ' SELECT KeyCode ';
        $sql .= ' ,      KeyContent ';
        $sql .= '        FROM M_Code ';
        $sql .= '        WHERE CodeId = 90 ';
        $stm = $this->dbAdapter->query( $sql );
        $cancelReasons = ResultInterfaceToArray( $stm->execute( null ) );

        $this->_lists['CancelReasonCode'] = array( self::UNSELECT_LIST_ITEM => '-' );
        foreach( $cancelReasons as $cancelReason ) {
            $this->_lists['CancelReasonCode'][$cancelReason['KeyCode']] = $cancelReason['KeyContent'];
        }

        // 着荷確認
        $this->_lists['ArrivalConfirmAlert'] = array(
                1 => '長期着荷未確認'
        );

        // 入金状態
        $this->_lists['IsWaitForReceipt'] = array(
            1 => '入金完了',
            2 => '一部入金',
            3 => '未入金',
            4 => '未請求',
        );

        // NG無保証
        $this->_lists['NgNoGuaranteeChange'] = array(
            1 => '無保証変更可',
            0 => '無保証変更不可'
        );

        // 口座振替申込区分
        $this->_lists['CreditTransferRequestFlg'] = array(
            self::UNSELECT_LIST_ITEM => '-',
            0 => '利用しない',
            99 => '利用する（全て）',
            2 => '利用する（紙面申込み）',
            1 => '利用する（WEB申込み）'
        );

        // 申込ステータス
        $sql  = ' SELECT KeyCode ';
        $sql .= ' ,      KeyContent ';
        $sql .= '        FROM M_Code ';
        $sql .= '        WHERE CodeId = 196 ';
        $stm = $this->dbAdapter->query( $sql );
        $requestStatuses = ResultInterfaceToArray( $stm->execute( null ) );

        $this->_lists['RequestStatus'] = array( self::UNSELECT_LIST_ITEM => '-', 0 => '未設定' );
        foreach( $requestStatuses as $requestStatus ) {
            $this->_lists['RequestStatus'][$requestStatus['KeyCode']] = $requestStatus['KeyContent'];
        }

        // 申込サブステータス
        $sql  = ' SELECT KeyCode ';
        $sql .= ' ,      KeyContent ';
        $sql .= '        FROM M_Code ';
        $sql .= '        WHERE CodeId = 210 ';
        $stm = $this->dbAdapter->query( $sql );
        $requestSubStatuses = ResultInterfaceToArray( $stm->execute( null ) );

        $this->_lists['RequestSubStatus'] = array( self::UNSELECT_LIST_ITEM => '-' );
        foreach( $requestSubStatuses as $requestSubStatus ) {
            $this->_lists['RequestSubStatus'][$requestSubStatus['KeyCode']] = $requestSubStatus['KeyContent'];
        }

        // 口座振替
        $this->_lists['CreditTransferMethod1'] = array(
            self::UNSELECT_LIST_ITEM => '-',
            1 => '対象',
            0 => '対象外'
        );

        // 初回申込用紙発行
        $this->_lists['CreditTransferMethod2'] = array(
            self::UNSELECT_LIST_ITEM => '-',
            1 => '対象',
            0 => '対象外'
        );

        // 入金方法
        // コードマスターから入金方法のコメントを取得
        // キャンセル理由
        // Display field ReceiptClassList
        // get sbps payment by Site Id
        $sitePaymentsData = array();
        $tblSbpsPayment = new TableSiteSbpsPayment($this->dbAdapter);
        foreach($siteids as $val){
        $sitePayments = ResultInterfaceToArray($tblSbpsPayment->getAll($val));
        if ($sitePayments) {
            foreach ($sitePayments as $sitePayment) {
                $sitePaymentsData[] = $sitePayment;
            }
        }
        }
        $selection = array_column($sitePaymentsData, 'PaymentId');
        $paymentid = array_unique($selection);
        $mdlc = new TableCode ( $this->dbAdapter );
        $receiptMethod = $mdlc->getMasterByClass5 ( 198 );
        $receiptMethod = ResultInterfaceToArray($receiptMethod);
        $common = array_column($receiptMethod, 'KeyCode');
        //$paymentid = array_merge($common, $paymentid);
        
        $this->_lists['ReceiptClass'] = array( self::UNSELECT_LIST_ITEM => '-' );
        //届いてから
        foreach($paymentid as $key=>$val){
            $sql = 'SELECT KeyContent FROM M_Code WHERE KeyCode = :KeyCode AND CodeId = 198';
            if($val === '10'){
                $paykey = '5';
            }elseif($val === '11'){
                $paykey = '21';
            }elseif($val === '12'){
                $paykey = '22';
            }elseif($val === '13'){
                $paykey = '15';
            }elseif($val === '14'){
                $paykey = '16';
            }elseif($val === '15'){
                $paykey = '17';
            }elseif($val === '16'){
                $paykey = '18';
            }elseif($val === '17'){
                $paykey = '19';
            }elseif($val === '18'){
                $paykey = '20';
            }else{
                $paykey = $val;
            }
            $this->_lists['ReceiptClass'][$paykey] = $this->dbAdapter->query($sql)->execute(array(':KeyCode' => $paykey))->current()['KeyContent'];
        }
        //共通項目
        foreach($common as $key=>$val){
            $sql = 'SELECT KeyContent FROM M_Code WHERE KeyCode = :KeyCode AND CodeId = 198';
            $this->_lists['ReceiptClass'][$val] = $this->dbAdapter->query($sql)->execute(array(':KeyCode' => $val))->current()['KeyContent'];
        }
        foreach($siteids as $val){
            $sql = 'SELECT PaymentId FROM T_SitePayment WHERE SiteId = :SiteId AND UseFlg = 1';
            $sitepaymentids[] = ResultInterfaceToArray($this->dbAdapter->query($sql)->execute(array(':SiteId' => $val)));
        }
        $result = array_reduce($sitepaymentids, 'array_merge', array());
        $sitepaymentids = array_column($result, 'PaymentId');
        $sitepaymentids = array_unique($sitepaymentids);
        $oemid = $this->userInfo->OemId;
        //テモナ対応と共通項目・届いてから以外
        if($oemid === '5'){
            foreach($sitepaymentids as $key=>$val){
                $sql = 'SELECT KeyContent FROM M_Code WHERE KeyCode = :KeyCode AND CodeId = 198';
                if($val === '37'){
                    $paykey = '7';
                }elseif($val === '39'){
                    $paykey = '4';
                }elseif($val === '40'){
                    $paykey = '11';
                }elseif($val === '41'){
                    $paykey = '14';
                }elseif($val === '42'){
                    $paykey = '10';
                }elseif($val === '38'){
                    $paykey = '6';
                }else{
                    $paykey = $val;
                }
                $this->_lists['ReceiptClass'][$paykey] = $this->dbAdapter->query($sql)->execute(array(':KeyCode' => $paykey))->current()['KeyContent'];
            }
        }else{
        foreach($sitepaymentids as $key=>$val){
            $sql = 'SELECT KeyContent FROM M_Code WHERE KeyCode = :KeyCode AND CodeId = 198';
            if($val === '13'){
                $paykey = '7';
            }elseif($val === '15'){
                $paykey = '4';
            }elseif($val === '16'){
                $paykey = '11';
            }elseif($val === '17'){
                $paykey = '14';
            }elseif($val === '18'){
                $paykey = '10';
            }else{
                $paykey = $val;
            }
            $this->_lists['ReceiptClass'][$paykey] = $this->dbAdapter->query($sql)->execute(array(':KeyCode' => $paykey))->current()['KeyContent'];
        }
        }
        ksort($this->_lists['ReceiptClass']);

        // $mdlc = new TableCode ( $this->dbAdapter );
        // $receiptMethod = $mdlc->getMasterByClass5 ( 198 );
        // $receiptMethod = ResultInterfaceToArray($receiptMethod);
        // $this->_lists['ReceiptClass'] = array_merge($this->_lists['ReceiptClass'], $receiptMethod);
        // usort($receiptMethod, function ($item1, $item2) {
        //     return $item1['Class4'] <=> $item2['Class4'];
        // });
        // $this->_lists['ReceiptClass'] = array( self::UNSELECT_LIST_ITEM => '-' );
        // foreach( $receiptMethod as $rMethod ) {
        //     $this->_lists['ReceiptClass'][$rMethod['KeyCode']] = $rMethod['KeyContent'];
        // }

        // ビューに参照リストと入力された検索条件をセットする
        $this->view->assign( 'entId', $this->userInfo->EnterpriseId );
        $this->view->assign( 'masters', $this->_lists );

        $params = array();
        $params = array_merge( $_POST , $_GET );
        $this->view->assign( 'searchValues', $params );

        // 現在のページ数
        $this->current_page = isset($this->view->searchValues['Page']) ? $this->view->searchValues['Page'] : 0;
        if( ! BaseReflectionUtility::isPositiveInteger( $this->current_page ) ) {
            $this->current_page = 1;
        }
        $this->view->assign( 'current_page', $this->current_page );
        $this->view->assign( 'cssName', "search" );

        $this->app = $app;

        $searchUtility = new SearchUtility();
        $searchUtility->setEnterpriseId( $this->userInfo->EnterpriseId );

        // 検索オプションを永続化させるcookieキーを確定
        $this->entOrderIdSearchAsNumKey = sprintf( '%s-E%06d', self::SEARCH_OPT_EXP_EOID_AS_NUMERIC_CKEY, $this->userInfo->EnterpriseId );

        $this->view->assign("userInfo",$this->app->authManager->getUserInfo());
	}

	/**
	 * indexアクション。searchアクションへフォワードする
	 *
	 */
	public function indexAction() {
	    return $this->_forward( 'search' );
	}

	/**
	 * searchアクション。検索フォームを表示する
	 *
	 */
	public function searchAction() {
        $this
            ->addStyleSheet( '../../css/base.ui.modaldialog.css' )
            ->addJavaScript( '../../js/base.ui.modaldialog.js' )
            ->addJavaScript( '../../oemmember/js/searchcolumneditor.js' );

        // 直前の検索条件をセッションデータから削除
        $session = $this->getSessionStorage();
        if( isset( $session->lastExpressions ) ) {
            unset( $session->lastExpressions );
        }

        $this->setPageTitle( '取引履歴検索' );

        if( isset($this->view->searchValues['SiteId'] ) ) {
            $siteId = $this->view->searchValues['SiteId'];
        }

        $this->view->assign( 'postTarget', 'search/result' );
        $this->view->assign( 'searchConditions', SearchUtility::getSearchConditions( $this->userInfo->EnterpriseId, $this->_getUseTemplatePatternSiteID($this->userInfo->EnterpriseId, $siteId) ) );
        $this->view->assign( 'defaultConditions', SearchUtility::getSearchConditions( $this->userInfo->EnterpriseId ) );

        $this->view->assign( 'groupMap', SearchUtility::getGroupMap() );

        $this->view->assign( 'entOrderIdAsNumeric', $_COOKIE[$this->entOrderIdSearchAsNumKey] );

        // OEM毎のｻﾝﾌﾟﾙ注文ID設定
        $orderIdPrefix = $this->app->dbAdapter->query(" SELECT OrderIdPrefix FROM T_Oem WHERE OemId = :OemId ")->execute(array(':OemId' => $this->userInfo->OemId))->current()['OrderIdPrefix'];
        $this->view->assign("sampleOemOrderId", nvl($orderIdPrefix,'ak') . '10000000');

        return $this->view;
	}

	/**
	 * resultアクション。検索結果を表示する
	 *
	 */
	public function resultAction() {

		$this
			->setPageTitle( '取引履歴検索結果' )
            ->addStyleSheet( '../../css/base.ui.modaldialog.css' )
			->addStyleSheet( '../../oemmember/css/search_result.css' )
			->addStyleSheet( '../../css/base.ui.tableex.css' )
			->addStyleSheet( '../../css/base.ui.customlist.css' )
			->addJavaScript( '../../js/base.ui.tableex.js' )
			->addJavaScript( '../../js/base.ui.customlist.js')
            ->addJavaScript( '../../js/base.ui.modaldialog.js' );

		// リクエストからPOSTパラメータのみ抽出
		$params = $this->getPureParams();
		// 検索条件の目視用リスト
		$expInfo = array();

		// 任意注文番号の「数値として検索」オプションの指定をcookieに永続化
		$c_lifetime = time() + ( 86400 * 365 );  // cookie有効期間は1年
		setcookie( $this->entOrderIdSearchAsNumKey, $params[self::SEARCH_OPT_EXP_EOID_AS_NUMERIC] ? 1 : 0, $c_lifetime );

        //表示順設定
        $orderStr = "";
        switch( $params["display_order"] ) {
            case '0':
                $orderStr = " ORDER BY ReceiptOrderDate DESC, OrderSeq DESC ";
                break;
            case '1':
                $orderStr = " ORDER BY ReceiptOrderDate ASC, OrderSeq ASC ";
                break;
            case '2':
                $orderStr = " ORDER BY OrderSeq DESC ";
                break;
            case '3':
                $orderStr = " ORDER BY OrderSeq ASC ";
                break;
            default:
                $orderStr = " ORDER BY ReceiptOrderDate DESC ";
                break;
        }

		// 検索条件の組み立て
		$wheres = '';     // カウント用条件式
		$wheres2 = '';    // 抽出用条件式
		$parameters = array();
		foreach( $this->buildSearchExpressions( $params ) as $expression ) {
			$where = $expression->getExpression();
			$prm = $expression->getParameter();
			if( $where != null ) {
                if (isset($prm[':RequestStatus']) && ($prm[':RequestStatus'] ==0)) {
                    $wheres .= ' AND (RequestStatus IS NULL OR RequestStatus = :RequestStatus)';
                    $wheres2 .= ' AND (EC.RequestStatus IS NULL OR EC.RequestStatus = :RequestStatus)';
                } else if (isset($prm[':CreditTransferRequestFlg']) && ($prm[':CreditTransferRequestFlg'] ==99)) {
                    $wheres .= ' AND CreditTransferRequestFlg IN (1, 2)';
                    $wheres2 .= ' AND CreditTransferRequestFlg IN (1, 2)';
                } else {
                    $wheres .= ' AND ' . $where;
                    $wheres2 .= ' AND ' . $this->_convertWhereCondition($where);
                }
			    $parameters = array_merge( $parameters, $prm );
			    $expInfo[] = $expression->getInformation();
			}
		}

		// 件数カウント用のクエリを_selectから作成 → カウント用Zend_Db_Selectの導入につき廃止（10.4.6 eda）
		//$q = preg_replace('/^select\s*.+\s*from/i', 'SELECT COUNT(*) AS TotalRows FROM', $this->_select->__toString() );
//$this->app->logger->info($q);
		// 今回の条件のヒット件数を取得
		$sql  = " SELECT COUNT(O.OrderSeq) AS cnt ";
		$sql .= " FROM ";
		$sql .= "     T_Order O ";
		$sql .= "     INNER JOIN T_Customer C ";
		$sql .= "             ON C.OrderSeq = O.OrderSeq ";
        $sql .= "     INNER JOIN T_EnterpriseCustomer EC ";
        $sql .= "             ON EC.EntCustSeq = C.EntCustSeq ";
		$sql .= "     INNER JOIN T_OrderSummary S ";
		$sql .= "             ON S.OrderSeq = O.OrderSeq ";
		$sql .= "     LEFT  JOIN T_Cancel L ";
		$sql .= "             ON L.OrderSeq = O.OrderSeq ";
		$sql .= "            AND L.ValidFlg = 1 ";
		$sql .= "     LEFT  JOIN T_PayingControl P ";
		$sql .= "             ON P.Seq = O.Chg_Seq ";
		$sql .= "     LEFT  JOIN T_ClaimControl CL ";
		$sql .= "             ON CL.OrderSeq = O.P_OrderSeq ";
		$sql .= "     LEFT  JOIN T_ReceiptControl RC ";
		$sql .= "             ON RC.ReceiptSeq = CL.LastReceiptSeq ";
		$sql .= "     LEFT  JOIN AT_Order AO ";
		$sql .= "             ON AO.OrderSeq = O.OrderSeq ";
        $sql .= "     LEFT  JOIN T_ClaimHistory CH ON ( ";
        $sql .= "         O.P_OrderSeq = CH.OrderSeq AND ";
        $sql .= "         CH.Seq = ( ";
        $sql .= "         SELECT MAX(Seq) ";
        $sql .= "             FROM T_ClaimHistory CH2 ";
        $sql .= "             WHERE CH2.OrderSeq = O.P_OrderSeq ";
        $sql .= "     AND CH2.ClaimPattern = 1 ";
        $sql .= "     AND CH2.ValidFlg = 1 ";
        $sql .= "         ) ";
        $sql .= "     ) ";
		$sql .= " WHERE O.EnterpriseId = " . $this->userInfo->EnterpriseId . ' ' . $wheres2;
		$row_count = $this->app->dbAdapter->query($sql)->execute( $parameters )->current()['cnt'];
		$this->view->assign( 'totalCount', $row_count );
		// [paging] 1ページあたりの項目数
		// ※：config.iniからの取得を追加（08.04.03）
 		$cn = $this->getControllerName();

 		//ページの表示件数をT_Enterprise.DisplayCountから取得するように変更したためコメントアウト
 		//$ipp = isset( $this->app->paging_conf ) ? $this->app->paging_conf->$cn : 50;
 		$enterprises = new TableEnterprise($this->app->dbAdapter);
 		$ipp = $enterprises->findEnterprise($this->userInfo->EnterpriseId)->current()['DisplayCount'];

 		if( ! BaseReflectionUtility::isPositiveInteger($ipp) ) $ipp = 50;
 		// ページャ初期化
 		$pager = new CoralPager( $row_count, $ipp );
 		// ビューに割り当てる
 		$this->view->assign( 'pager', $pager );
		// ページャ設定にしたがってlimit追加
		if( $row_count > $ipp ) {
            $limitStr = ' LIMIT ' . $pager->getStartIndex( $this->current_page ) . ', ' . $ipp;
		}

		$expressions  = (self::V_ORDER_SEARCH . " WHERE O.EnterpriseId = " . $this->userInfo->EnterpriseId);
		$expressions .= $wheres2;
		$expressions .= ($orderStr . $limitStr);

		$results = $this->execSearch( $expressions, $parameters, $params['SiteId'] );
		$mdlo = new TableOrder( $this->dbAdapter );

		$resultsCount = 0;
		if (!empty($results)) {
		    $resultsCount = count($results);
		}
        $lgCancel = new LogicCancel($this->app->dbAdapter);
		for ($i = 0 ; $i < $resultsCount ; $i++) {
			$results[$i]['NoGuaranteeFlg'] = 0;
		    $order = $mdlo->find($results[$i]['_OrderSeq'])->current();
			if($order) {
				$results[$i]['Cnl_ReturnSaikenCancelFlg'] = $order['Cnl_ReturnSaikenCancelFlg'];

				// 「無保証に変更」ボタン表示設定
				$ChangeFlg = 0;
				$aosql = 'SELECT * FROM AT_Order WHERE OrderSeq = :OrderSeq ';
				$aorow = $this->app->dbAdapter->query($aosql)->execute(array(':OrderSeq' => $results[$i]['_OrderSeq']))->current();
				if ($aorow['NgButtonFlg'] == 1 && date('Y-m-d') <= $aorow['NoGuaranteeChangeLimitDay']) {
					$ChangeFlg = '1';
				}
				$results[$i]['NoGuaranteeFlg'] = $ChangeFlg;
                $results[$i]['flagTodoItekara'] = false;
				if ($aorow['ExtraPayType'] == 1) {
                    $results[$i]['flagTodoItekara'] = true;
                }
			}
			$sasql = " SELECT AlertClass FROM T_StagnationAlert WHERE OrderSeq = :OrderSeq AND AlertClass = 1 AND ValidFlg = 1 ";
			$AlertClass[$i] = $this->app->dbAdapter->query($sasql)->execute(array(':OrderSeq' => $results[$i]['_OrderSeq']))->current();
		}

		$this->view->assign( 'alClass', $AlertClass);
		$this->view->assign( 'searchResult', $results );
		$this->view->assign( 'searchExpressions', $expInfo );
		$this->view->assign( 'sorting', $params["display_order"] );
		//$this->view->assign( 'searchConditions', SearchUtility::getSearchConditions( $this->userInfo->EnterpriseId ) );
		// 出力定義の取得を自身のファクトリに変更（09.6.19 eda）
		$this->view->assign( 'searchConditions', SearchUtility::getSearchConditions( $this->userInfo->EnterpriseId, $this->_getUseTemplatePatternSiteID($this->userInfo->EnterpriseId, $params['SiteId']) ) );
		$this->view->assign( 'cancelList', $this->_lists['CancelReasonCode'] );

        // 受付サイト指定がある場合はサイト名をビューへアサイン
        if ((int)$params['SiteId'] > 0) {
            $siteNameKj = $this->app->dbAdapter->query(" SELECT SiteNameKj FROM T_Site WHERE SiteId = :SiteId "
                )->execute(array(':SiteId' => $params['SiteId']))->current()['SiteNameKj'];
            $this->view->assign( 'siteNameKj', $siteNameKj );
        }

		// ユーザーIDの取得
		$obj = new TableUser( $this->app->dbAdapter );
		getUserInfoForMember( $this->app, $userClass, $seq );
		$userId = $obj->getUserId( $userClass, $seq );
		$this->view->assign( 'userId', $userId );

		// 検索条件をセッションデータに保存
		$session = $this->getSessionStorage();
		$session->lastExpressions = $params;

		return $this->view;
	}

	/**
	 * detailアクション。指定のOrderSeqに一致する注文データの詳細を表示する
	 */
	public function detailAction() {

        $this
            ->setPageTitle( '取引情報詳細' )
            ->addStyleSheet( '../../oemmember/css/search_detail.css' )
            ->addStyleSheet( '../../css/base.ui.modaldialog.css' )
            ->addStyleSheet( '../../oemmember/css/search_result.css' )
            ->addJavaScript( '../../js/base.ui.customlist.js')
            ->addJavaScript( '../../js/base.ui.modaldialog.js' );

        $params = $this->getParams();
        $orderId = isset($params['id']) ? $params['id'] : -1;

        // postされたかgetだったのかをviewへ通知
        $this->view->assign( 'isPost', $this->params()->fromPost()['id'] == $orderId );

        // 注文検索し該当が１件も得られない場合は該当なしで直ちに戻る
        $cnt = (int)$this->app->dbAdapter->query(" SELECT COUNT(1) AS cnt FROM T_Order WHERE EnterpriseId = :EnterpriseId AND OrderId = :OrderId "
        )->execute(array(':EnterpriseId' => $this->userInfo->EnterpriseId, ':OrderId' => $orderId))->current()['cnt'];
        if ($cnt == 0) {
            $this->setTemplate('no-detail');
            return $this->view;
        }

        // サブクエリとして、１件に絞り込んでから結合する方式に変更
        $view = self::V_ORDER_SEARCH ;
        $view .= ' WHERE O.EnterpriseId = :EnterpriseId ';
        $view .= ' AND O.OrderId = :OrderId ';

        $sql  = " SELECT v.* ";
        $sql .= " ,      o.ApiUserId AS ApiUserId ";
        $sql .= " ,      o.ServiceTargetClass AS ServiceTargetClass ";
        $sql .= " ,      o.CreditReplyDate AS CreditReplyDate ";
        $sql .= " ,      o.Deli_ConfirmArrivalFlg AS Deli_ConfirmArrivalFlg ";
        $sql .= " ,      a.CreditTransferRequestFlg AS CreditTransferRequestFlg ";
        $sql .= " ,      e.CreditTransferFlg AS CreditTransferFlg ";
        $sql .= " ,      ( ";
        $sql .= "        CASE ";
        $sql .= "         WHEN o.RegistDate <= '2021-09-28 03:00:00' ";
        $sql .= "          THEN NULL ";
        $sql .= "         ELSE cc.CreditTransferFlg ";
        $sql .= "        END ";
        $sql .= "        ) AS CC_CreditTransferFlg ";
//        $sql .= " ,      cc.CreditTransferFlg AS CC_CreditTransferFlg ";
        $sql .= " ,      e.BillingAgentFlg AS BillingAgentFlg ";
        $sql .= " ,      ( ";
        $sql .= "         SELECT KeyContent ";
        $sql .= "                FROM M_Code ";
        $sql .= "                WHERE KeyCode = o.PendingReasonCode ";
        $sql .= "                AND   CodeId = 92 ";
        $sql .= "        ) AS PendingReasonCode ";
        $sql .= " ,      c.Occupation AS Occupation ";
        $sql .= " ,      c.CorporateName AS CorporateName ";
        $sql .= " ,      c.DivisionName AS DivisionName ";
        $sql .= " ,      c.CpNameKj AS CpNameKj ";
        $sql .= " ,      ( ";
        $sql .= "        CASE ";
        $sql .= "         WHEN s.T_OrderClass = 0 ";
        $sql .= "          THEN -1 ";
        $sql .= "         ELSE o.T_OrderClass ";
        $sql .= "        END ";
        $sql .= "        ) AS T_OrderClass ";
        $sql .= " ,      MAX( rc.ReceiptDate ) AS ReceiptDate ";
        $sql .= " ,      rc.ReceiptClass AS Rct_ReceiptMethod ";
        $sql .= " ,      (SELECT DispDecimalPoint FROM T_Enterprise WHERE EnterpriseId = v.EnterpriseId) AS DispDecimalPoint ";
        $sql .= " ,      s.PaymentAfterArrivalFlg AS PaymentAfterArrivalFlg ";
        $sql .= " ,      a.ExtraPayKey AS ExtraPayKey ";
        $sql .= " ,      ( ";
        $sql .= "        CASE ";
        $sql .= "         WHEN o.RegistDate <= '2021-09-28 03:00:00' ";
        $sql .= "          THEN NULL ";
        $sql .= "         ELSE ch.CreditTransferMethod ";
        $sql .= "        END ";
        $sql .= "        ) AS CreditTransferMethod ";
//        $sql .= " ,      ch.CreditTransferMethod AS CreditTransferMethod ";
        $sql .= " FROM ($view) v ";
        $sql .= " STRAIGHT_JOIN T_Order o ON o.OrderSeq = v.OrderSeq ";
        $sql .= " STRAIGHT_JOIN T_Customer c ON c.OrderSeq = v.OrderSeq ";
        $sql .= " STRAIGHT_JOIN T_Site s ON s.SiteId = o.SiteId ";
        $sql .= " LEFT OUTER JOIN T_Cancel l ON l.OrderSeq = v.OrderSeq AND l.ValidFlg = 1 ";
        $sql .= " LEFT OUTER JOIN T_ReceiptControl rc ON rc.OrderSeq = o.P_OrderSeq ";
        $sql .= " STRAIGHT_JOIN AT_Order a ON a.OrderSeq = o.OrderSeq ";
        $sql .= " STRAIGHT_JOIN T_Enterprise e ON e.EnterpriseId = o.EnterpriseId ";
        $sql .= " LEFT OUTER JOIN T_ClaimControl cc ON cc.OrderSeq = o.OrderSeq ";
        $sql .= " LEFT OUTER JOIN T_ClaimHistory ch ON ch.OrderSeq = o.OrderSeq AND ch.seq=(SELECT MAX(Seq) FROM T_ClaimHistory ch2 WHERE ch2.OrderSeq = o.OrderSeq AND ch2.ClaimPattern = 1 AND ch2.ValidFlg = 1) ";
        $stm = $this->app->dbAdapter->query($sql);
        $prm = array(
                ':EnterpriseId' => $this->userInfo->EnterpriseId,
                ':OrderId' => strtoupper($orderId),
        );
        $ri = $stm->execute($prm);
        $rs = new \Zend\Db\ResultSet\ResultSet();
        $rs->initialize($ri);
        $rows = $rs->toArray();

        if( empty($rows) ) {
            $this->setTemplate('no-detail');
            return $this->view;
        }

        if (! empty ( $rows[0] ['Rct_ReceiptMethod'] )) {
            // コードマスターから入金方法のコメントを取得
            $mdlc = new TableCode ( $this->app->dbAdapter );
            $ReceiptMethod = $mdlc->find ( 198, $rows[0] ['Rct_ReceiptMethod'] )->current ();
            $ReceiptMethod = $ReceiptMethod['KeyContent'];
            $this->view->assign ( 'receiptMethod', $ReceiptMethod );
        }

        // 申込サブステータス
        $mdlc = new TableCode ( $this->app->dbAdapter );
        $RequestSubStatus = $mdlc->find ( 210, $rows[0] ['RequestSubStatus'] )->current ();
        $this->view->assign ( 'requestSubStatus', $RequestSubStatus );

        $this->view->assign( 'detailData', $rows[0] );

        // 商品明細を取得
        $itemTable = new TableOrderItems($this->app->dbAdapter);
        $ri = $itemTable->findByOrderSeq( $rows[0]['OrderSeq'] );
        $rs->initialize($ri);
        $this->view->assign( 'itemList', $rs->toArray() );

        // 消費税率をを取得
        $propertyTable = new TableSystemProperty($this->app->dbAdapter);
        $taxRate = $propertyTable->getTaxRateAt(date('Y-m-d'));
        $this->view->assign( 'taxRate', $taxRate );

        // 請求取りまとめで取りまとめ先（親）があった場合には対象の注文IDを取得する
        $mdlo = new TableOrder($this->app->dbAdapter);

        if($rows[0]['OrderSeq'] != $rows[0]['P_OrderSeq']) {
            $parentorder = $mdlo->find($rows[0]['P_OrderSeq'])->current();

            $this->view->assign( 'parentOrderId', $parentorder['OrderId'] );

            // 総請求(利用)金額
            $claimAmount = $this->app->dbAdapter->query(" SELECT IFNULL(SUM(UseAmount), 0) AS ClaimAmount FROM T_Order WHERE P_OrderSeq = :P_OrderSeq AND Cnl_Status = 0 "
                )->execute(array(':P_OrderSeq' => $rows[0]['P_OrderSeq']))->current()['ClaimAmount'];
            $this->view->assign( 'claimAmount', $claimAmount );
        }

        // 請求取りまとめで取りまとめ元（子）があった場合には対象の注文IDを取得する
        if($rows[0]['CombinedClaimParentFlg']) {
            $ri = $this->app->dbAdapter->query(" SELECT * FROM T_Order WHERE P_OrderSeq = :P_OrderSeq AND P_OrderSeq <> OrderSeq ")->execute(array(':P_OrderSeq' => $rows[0]['P_OrderSeq']));
            $orders = $rs->initialize($ri)->toArray();

            $orderIds = array();
            foreach($orders as $target) {
                array_push($orderIds, $target['OrderId']);
            }
            $this->view->assign( 'childOrderIds', $orderIds );

            // 総請求(利用)金額
            $claimAmount = $this->app->dbAdapter->query(" SELECT IFNULL(SUM(UseAmount), 0) AS ClaimAmount FROM T_Order WHERE P_OrderSeq = :P_OrderSeq AND Cnl_Status = 0 "
                )->execute(array(':P_OrderSeq' => $rows[0]['P_OrderSeq']))->current()['ClaimAmount'];
            $this->view->assign( 'claimAmount', $claimAmount );
        }
        $this->view->assign( 'cancelList', $this->_lists['CancelReasonCode'] );

        // 与信NG理由
        $Sitesql = 'SELECT * FROM T_Site WHERE SiteId = :SiteId ';
        $Sitedata = $this->app->dbAdapter->query($Sitesql)->execute(array(':SiteId' => $rows[0]['SiteId']))->current();
        if($rows[0]['IncreStatus'] == -1 && $Sitedata['ShowNgReason'] == 1){
            $sql = 'SELECT * FROM AT_Order WHERE OrderSeq = :OrderSeq ';
            $NgReason = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $rows[0]['OrderSeq']))->current();
            $Reason = null;
            if (nvl($NgReason['AutoJudgeNgReasonCode'], 0) != 0) {
                // 自動NG
                if ($NgReason['AutoJudgeNgReasonCode'] == '7') {
                    // 審査システムスコア判定
                    $sql = <<<EOQ
SELECT
  MC.Note
FROM
  T_CjResult_Detail AS TCD
  INNER JOIN T_CjResult AS TC ON TCD.CjrSeq = TC.Seq
  LEFT JOIN M_Code AS MC ON MC.CodeId = '197' AND MC.ValidFlg = '1' AND TCD.DetectionPatternName LIKE CONCAT('%', MC.KeyContent, '%')
WHERE 1 = 1
  AND TC.OrderSeq = :OrderSeq
ORDER BY
  TC.Seq ASC
, TCD.Seq ASC
, MC.KeyCode ASC
LIMIT
  1
EOQ;
                    $Reason = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $rows[0]['OrderSeq']))->current()['Note'];
                    // 取得できない場合
                    if (empty($Reason))
                    {
                        $sql = 'SELECT Note FROM M_Code WHERE CodeId = 191 AND KeyCode = :KeyCode ';
                        $Reason = $this->app->dbAdapter->query($sql)->execute(array(':KeyCode' => $NgReason['AutoJudgeNgReasonCode']))->current()['Note'];
                    }
                } else {
                    // その他（旧来通り）
                    $sql = 'SELECT Note FROM M_Code WHERE CodeId = 191 AND KeyCode = :KeyCode ';
                    $Reason = $this->app->dbAdapter->query($sql)->execute(array(':KeyCode' => $NgReason['AutoJudgeNgReasonCode']))->current()['Note'];
                }
                $Reason = '（' . $Reason . '）';
            } elseif (nvl($NgReason['ManualJudgeNgReasonCode'], 0) != 0) {
                // 手動NG
                $sql = 'SELECT Note FROM M_Code WHERE CodeId = 190 AND KeyCode = :KeyCode ';
                $Reason = $this->app->dbAdapter->query($sql)->execute(array(':KeyCode' => $NgReason['ManualJudgeNgReasonCode']))->current()['Note'];
                $Reason = '（' . $Reason . '）';
            }
            $this->view->assign( 'NgReason', $Reason );
        }

        // 無保証に変更ボタン
        if ($rows[0]['IncreStatus'] == -1) {
            $ChangeFlg = 0;
            $aosql = 'SELECT * FROM AT_Order WHERE OrderSeq = :OrderSeq ';
            $aorow = $this->app->dbAdapter->query($aosql)->execute(array(':OrderSeq' => $rows[0]['OrderSeq']))->current();
            if ($aorow['NgButtonFlg'] == 1 && date('Y-m-d') <= $aorow['NoGuaranteeChangeLimitDay']) {
                $ChangeFlg = '1';
            }

            $this->view->assign( 'ChangeFlg', $ChangeFlg );
        }

		// ユーザーIDの取得
		$obj = new TableUser( $this->app->dbAdapter );
		getUserInfoForMember( $this->app, $userClass, $seq );
		$userId = $obj->getUserId( $userClass, $seq );
		$this->view->assign( 'userId', $userId );

		// 立替状態の取得
		$sql = " SELECT pc.ExecDate AS ExecDate, IFNULL(pc.ExecFlg, 0) AS ExecFlg FROM T_PayingAndSales ps INNER JOIN T_PayingControl pc ON (pc.Seq = ps.PayingControlSeq) WHERE ps.OrderSeq = :OrderSeq; ";
		$PayCtrl = ResultInterfaceToArray($this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $rows[0]['OrderSeq'])));

		if (!empty($PayCtrl) && $PayCtrl[0]['ExecFlg'] != 0) {
		    $AdvState = "立替済（" . $PayCtrl[0]['ExecDate'] . "）";
		} else {
		    if ($rows[0]['Deli_ConfirmArrivalFlg'] == 1) {
		        $AdvState = "着荷済み未立替";
		    } else {
		        $AdvState = "未着荷";
		    }
		}

		$this->view->assign( 'AdvState', $AdvState);
        $lgCancel = new LogicCancel($this->app->dbAdapter);
        $this->view->assign( 'todoitekara', $lgCancel->_usedTodo2Pay($rows[0]['OrderSeq']));
        return $this->view;
	}

	/**
	 * downloadアクション。直前の検索結果をCSVファイルでダウンロードさせる
	 *
	 */
	public function downloadAction() {
	    $prm = array();
		// セッションデータから直前の検索条件を復元
		$params = $this->getSessionStorage()->lastExpressions;
		// 検索条件の組み立て
		$wheres = '';     // カウント用条件式
		$wheres2 = '';    // 抽出用条件式
		$parameters = array();
		foreach( $this->buildSearchExpressions( $params ) as $expression ) {
			$where = $expression->getExpression();
			$prm = $expression->getParameter();
			if( $where != null ) {
                if (isset($prm[':RequestStatus']) && ($prm[':RequestStatus'] ==0)) {
                    $wheres .= ' AND (RequestStatus IS NULL OR RequestStatus = :RequestStatus)';
                    $wheres2 .= ' AND (EC.RequestStatus IS NULL OR EC.RequestStatus = :RequestStatus)';
                } else if (isset($prm[':CreditTransferRequestFlg']) && ($prm[':CreditTransferRequestFlg'] ==99)) {
                    $wheres .= ' AND CreditTransferRequestFlg IN (1, 2)';
                    $wheres2 .= ' AND CreditTransferRequestFlg IN (1, 2)';
                } else {
                    $wheres .= ' AND ' . $where;
                    $wheres2 .= ' AND ' . $this->_convertWhereCondition($where);
                }
			    $parameters = array_merge( $parameters, $prm );
			    $expInfo[] = $expression->getInformation();
			}
		}

	        //表示順設定
        $orderStr = "";
        switch( $params["display_order"] ) {
            case '0':
                $orderStr = " ORDER BY ReceiptOrderDate DESC, OrderSeq DESC ";
                break;
            case '1':
                $orderStr = " ORDER BY ReceiptOrderDate ASC, OrderSeq ASC ";
                break;
            case '2':
                $orderStr = " ORDER BY OrderSeq DESC ";
                break;
            case '3':
                $orderStr = " ORDER BY OrderSeq ASC ";
                break;
            default:
                $orderStr = " ORDER BY ReceiptOrderDate DESC, OrderSeq DESC ";
                break;
        }

		// 検索実行
        $expressions  = (self::V_ORDER_SEARCH . " WHERE O.EnterpriseId = " . $this->userInfo->EnterpriseId);
        $expressions .= $wheres2;
        $expressions .= $orderStr;

		$results = $this->execSearch( $expressions, $parameters, $params['SiteId'] );

		$sql .= " SELECT F_GetLoginUserName( O.RegistId ) AS RegistName ";
		$sql .= " ,      F_GetLoginUserName( O.UpdateId ) AS UpdateName ";
		$sql .= " ,      O.UpdateDate ";
		$sql .= " ,      AO.NgButtonFlg ";
		$sql .= " ,      AO.NoGuaranteeChangeLimitDay ";
		$sql .= " ,      O.RegistId ";
		$sql .= " ,      O.UpdateId ";
		$sql .= " FROM T_Order O";
		$sql .= "      LEFT JOIN AT_Order AO ON (O.OrderSeq = AO.OrderSeq) ";
		$sql .= " WHERE O.OrderSeq = :OrderSeq ";
		$sql .= " AND   O.EnterpriseId = :EnterpriseId ";
		$stm = $this->dbAdapter->query( $sql );

		// OEM先名の取得
		$userName = 'キャッチボール';
		if ($this->userInfo->OemId != 2) {
		    $userName = $this->dbAdapter->query("SELECT OemNameKj FROM T_Oem WHERE OemId = :OemId")->execute(array(':OemId' => $this->userInfo->OemId))->current()['OemNameKj'];
		}

		foreach( $results as $i => $result ) {
		    $prm = array( ':OrderSeq' => $result['_OrderSeq'], ':EnterpriseId' => $this->userInfo->EnterpriseId );
		    $result['RegistName'] = $stm->execute( $prm )->current()['RegistName'];
		    $result['UpdateName'] = $stm->execute( $prm )->current()['UpdateName'];
		    $result['UpdateDate'] = $stm->execute( $prm )->current()['UpdateDate'];
		    $result['NgNoGuaranteeChange'] = $stm->execute( $prm )->current()['NgButtonFlg'];
		    $result['NoGuaranteeChangeLimitDay'] = $stm->execute( $prm )->current()['NoGuaranteeChangeLimitDay'];

		    // UserClass 0:CB、1:OEM、2:加盟店、3:APIユーザー、99:システム
		    // 登録者の加工
		    $registId = $stm->execute( $prm )->current()['RegistId'];
		    $userClass = nvl($this->dbAdapter->query("SELECT UserClass FROM T_User WHERE UserId = :UserId")->execute(array(':UserId' => $registId))->current()['UserClass'], 0);
		    if ($userClass == 0 || $userClass == 1 || $userClass == 99) {
		        $result['RegistName'] = $userName;
		    }

		    // 更新者の加工
		    $updateId = $stm->execute( $prm )->current()['UpdateId'];
		    $userClass = nvl($this->dbAdapter->query("SELECT UserClass FROM T_User WHERE UserId = :UserId")->execute(array(':UserId' => $updateId))->current()['UserClass'], 0);
		    if ($userClass == 0 || $userClass == 1 || $userClass == 99) {
		        $result['UpdateName'] = $userName;
		    }

            $results[$i] = $result;
		}

		// 変換用Array
		// サイト
		$siteTable = new TableSite($this->dbAdapter);
		$sites = array();
		$ri = $siteTable->getAll($this->userInfo->EnterpriseId);
		foreach( $ri as $row ) {
		    $sites[ $row['SiteId'] ] = $row['SiteNameKj'];
		}
		// 同梱/別送
		$claimSendingClass = array(
		        21 => '別送',
		        12 => '別送',
		        11 => '同梱',
		);
		// 配送会社目
		$delilogic = new LogicDeliveryMethod( $this->dbAdapter );
		$deli_DeliveryMethod = $delilogic->getEnterpriseDeliveryMethodListAllOrderBy($this->userInfo->EnterpriseId);
		// 伝票登録
		$deli_JournalNumberAlert = array(
		        1 => '長期伝票未登録',
		);
		// 保証有無
		$outOfAmends = array(
		        1 => '保証無し',
		        0 => '保証有り',
		);
		// キャンセル状況
		$realCancelStatus = array(
		        0 => '未キャンセル',
		        1 => '申請中',
		        2 => 'キャンセル済',
		        11 => '返却依頼中',
		        12 => '返却済',
		);
		// キャンセル理由
		$sql  = ' SELECT KeyCode ';
		$sql .= ' ,      KeyContent ';
		$sql .= '        FROM M_Code ';
		$sql .= '        WHERE CodeId = 90 ';
		$stm = $this->dbAdapter->query( $sql );
		$ri = $stm->execute( null );
		$cancelReasons = array();
		foreach( $ri as $row ) {
		    $cancelReasons[$row['KeyCode']] = $row['KeyContent'];
		}
		// 着荷確認
		$arrivalConfirmAlert = array(
		        1 => '長期着荷未確認'
		);
		// 入金状態
		$isWaitForReceipt = array(
		        1 => '入金完了',
		        2 => '一部入金',
		        3 => '未入金',
		        4 => '未請求',
		);
        // 口座振替申込区分
        $creditTransferRequestFlg = array(
            0 => '利用しない',
            1 => '利用する（WEB申込み）',
            2 => '利用する（紙面申込み）',
        );
        // 申込ステータス
        $sql  = ' SELECT KeyCode ';
        $sql .= ' ,      KeyContent ';
        $sql .= '        FROM M_Code ';
        $sql .= '        WHERE CodeId = 196 ';
        $stm = $this->dbAdapter->query( $sql );
        $ri = $stm->execute( null );
        $requestStatus = array();
        foreach( $ri as $row ) {
            $requestStatus[$row['KeyCode']] = $row['KeyContent'];
        }
        $requestStatus[0] = '未設定';
        // 申込サブステータス
        $sql  = ' SELECT KeyCode ';
        $sql .= ' ,      KeyContent ';
        $sql .= '        FROM M_Code ';
        $sql .= '        WHERE CodeId = 210 ';
        $stm = $this->dbAdapter->query( $sql );
        $ri = $stm->execute( null );
        $requestSubStatus = array();
        foreach( $ri as $row ) {
            $requestSubStatus[$row['KeyCode']] = $row['KeyContent'];
        }

		// CSV出力データの構築
 		$rows = array();
 		foreach( $results as $line ) {
 			$row = array();
 			// ID、区分を名称に変換
            foreach( $line as $key => $value ) {
                // 審査結果
                if( $key == 'IncreStatus' ) {
     				switch( $value ) {
     				    case -1:
     				        $value = 'NG';
     				        break;
     				    case 1:
     				        $value = 'OK';
     				        break;
     				    case 0:
     				        if( $line['_Cnl_Status'] == 1 ) {
     				            $value = '与信中キャンセル';
     				        }
     				        else if( $line['_Cnl_Status'] == 2 ) {
                                $value = '与信中キャンセル済';
     				        }
     				        else {
     				            $value = '与信中';
     				        }
     				        break;
     				    default:
     				        $value = '存在しないステータス';
     				        break;
     				}
                }
                // 受付サイト
                elseif( $key == 'SiteId' ) {
                    $value = $sites[$value];
                }
                // 同梱/別送
                elseif( $key == 'ClaimSendingClass' ) {
                    $value = $claimSendingClass[$value];
                }
                // 配送会社名
                elseif( $key == 'Deli_DeliveryMethod' ) {
                    $value = $deli_DeliveryMethod[$value];
                }
                // 伝票登録
                elseif( $key == 'Deli_JournalNumberAlert' ) {
                    $value = $deli_JournalNumberAlert[$value];
                }
                // 保証有無
                elseif( $key == 'OutOfAmends' ) {
                    $value = $outOfAmends[$value];
                }
                // キャンセル状況
                elseif( $key == 'RealCancelStatus' ) {
                    $value = $realCancelStatus[$value];
                }
                // キャンセル理由
                elseif( $key == 'CancelReasonCode' ) {
                    $value = $cancelReasons[$value];
                }
                // 着荷確認
                elseif( $key == 'ArrivalConfirmAlert' ) {
                    $value = $arrivalConfirmAlert[$value];
                }
                // 入金状態
                elseif( $key == 'IsWaitForReceipt' ) {
                    $value = $isWaitForReceipt[$value];
                }
                // NG無保証
                elseif( $key == 'NgNoGuaranteeChange' ) {
                    switch( $value ) {
                        case 1:
                            if( date('Y-m-d') <= $line['NoGuaranteeChangeLimitDay'] && $line['IncreStatus'] == -1 ) {
                                $value = '無保証変更可';
                            } elseif ( $line['IncreStatus'] == 1 ) {
                                $value = '';
                            } else {
                                $value = '無保証変更不可';
                            }
                            break;
                        case 0:
                            if ( $line['IncreStatus'] == -1 ) {
                                $value = '無保証変更不可';
                            } else {
                                $value = '';
                            }
                            break;
                        default :
                            $value = '';
                            break;
                    }
                }
                // 口座振替申込区分
                elseif( $key == 'CreditTransferRequestFlg' ) {
                    $value = $creditTransferRequestFlg[$value];
                }
                // 申込ステータス
                elseif( $key == 'RequestStatus' ) {
                    if (is_null($value) || ($value == 0)) {
                        $value = '未設定';
                    } else {
                        $value = $requestStatus[$value];
                    }
                }
                // 申込サブステータス
                elseif( $key == 'RequestSubStatus' ) {
                    $value = $requestSubStatus[$value];
                }
                // 口座振替
                elseif( $key == 'CreditTransferMethod1' ) {
                    if ($value == 1) {
                        $value = '口座振替';
                    }
                }
                // 初回申込用紙発行
                elseif( $key == 'CreditTransferMethod2' ) {
                    if ($value == 1) {
                        $value = '初回申込用紙発行';
                    }
                }
                //入金方法
                elseif($key == 'ReceiptClass'){
                // コードマスターから入金方法のコメントを取得
                $mdlc = new TableCode ( $this->app->dbAdapter );
                $ReceiptMethod = $mdlc->find ( 198, $value)->current ();
                $ReceiptMethod = $ReceiptMethod['KeyContent'];
                $value = $ReceiptMethod;
                }
 				$row[$key] = $value;
            }
 			$rows[] = $row;
 		}

		// ファイル名生成
		// TODO: 仕様上安全なマルチバイトファイル名のエンコード方法を調査する
		$fileName = self::RESULT_CSV_PREFIX . '_' . date('Ymd_His') . '.csv';

		// 出力実行
		$templatePattern = $this->_getUseTemplatePatternSiteID($this->userInfo->EnterpriseId, $params['SiteId']);
		$logicTemplate = new LogicTemplate( $this->app->dbAdapter );
		$response = $logicTemplate->convertArraytoResponse( $rows, $fileName, SearchUtility::TEMPLATE_ID, SearchUtility::TEMPLATE_CLASS, $this->userInfo->EnterpriseId, $templatePattern, $this->getResponse() );

		if( $response == false ) {
		    throw new \Exception( $logicTemplate->getErrorMessage() );
		}

		return $response;
	}

	/**
	 * クイック検索アクション
	 */
	public function quickAction() {
	    $params = $this->getPureParams();
        return $this->_redirect( 'search/detail/id/' . htmlspecialchars($params['SearchKey'] ) );
	}

	/**
	 * キャンセル申請アクション
	 */
	public function cancelAction() {
	    return $this->view;
	}

// Del By Takemasa(NDC) 20150126 Stt マジックメソッド廃止
// 	/**
// 	 * マジックメソッド。searchアクションへ付け替える
// 	 *
// 	 * @param string $method
// 	 * @param array $args
// 	 */
// 	public function __call($method, $args) {
// 		$this->_forward('search');
// 	}
// Del By Takemasa(NDC) 20150126 End マジックメソッド廃止

	/**
	 * @access private
	 *
	 * フォームからPOSTされた検索条件を元にWHERE句を組み立てるための
	 * SearchExpressionInfoのリストを作成する
	 *
	 * @param array $params 検索条件
	 * @return array $paramsを元に構築されたSearchExpressionInfoの配列
	 */
	private function buildSearchExpressions(array $params) {
	    $expressions = array();

	    // 任意注文番号を数値として検索するかのオプション
	    $searchEntOrderIdAsNumeric = isset( $params[self::SEARCH_OPT_EXP_EOID_AS_NUMERIC]) && $params[self::SEARCH_OPT_EXP_EOID_AS_NUMERIC];
		foreach( $params as $id => $value ) {
		    // 伝票番号未登録の場合はここで判定
		    if( $id == 'Deli_Conditions' && $value == '2' ) {
		        $expressions[] = new SearchExpressionInfo(
		        "Deli_JournalNumber IS NULL",
		        "配送伝票番号 が 未登録 である",
		        array()
		        );
		    }
		    else if( $id == 'Deli_Conditions' && $value == '1' ) {
		        $expressions[] = new SearchExpressionInfo(
		        "Deli_JournalNumber IS NOT NULL",
		        "配送伝票番号 に 登録 がある",
		        array()
		        );
		    }
		    else if( $id == 'IsWaitForReceipt' && count($value) >= count($this->_lists['IsWaitForReceipt'])) {
		        // 入金状態全指定時は条件追加しない
		        continue;
		    }
			$condition = SearchUtility::findConditionByColumn( $id );
			if( $condition === null ) continue;

			// 住所に対する問い合わせは数値の半角変換を適用 (2008.02.20 追加 eda)
			if( $id == 'UnitingAddress' || $id == 'DestUnitingAddress' ) {
				$params[$id] = BaseGeneralUtils::convertNumberWideToNarrow( $params[$id] );
			}
			switch( $condition['control'] ) {
			case SearchUtility::CONTROL_TYPE_DATE_SPAN:
			case SearchUtility::CONTROL_TYPE_TEXT_SPAN:
				// 日付範囲と文字列範囲の場合
				if( $params[ "{$id}_Mode" ] ) {
					// 範囲選択をする場合
					if( $params[$id] === null || $params[$id] === '' ) {
						// 左側のフィールドが空の場合は検索タイプをlteにし、右側フィールドの値をコピー
						$condition['type'] = SearchUtility::SEARCH_TYPE_LESS_THAN_OR_EQUAL_TO;
						$params[$id] = $params[ "{$id}_2" ];
						// フィールドタイプをテキストに変更
						$condition['control'] = SearchUtility::CONTROL_TYPE_SIMPLE_TEXT;

					} else if( $params[ "{$id}_2" ] === null || $params[ "{$id}_2" ] === '' ) {
						// 右側のフィールドが空の場合は検索タイプをgteにする
						$condition['type'] = SearchUtility::SEARCH_TYPE_GREATER_THAN_OR_EQUAL_TO;
						// フィールドタイプをテキストに変更
						$condition['control'] = SearchUtility::CONTROL_TYPE_SIMPLE_TEXT;

					}
				}

				break;
			case SearchUtility::CONTROL_TYPE_LIST:
				// リスト選択の場合
				if( $value == self::UNSELECT_LIST_ITEM ) {
					// 「未選択」の場合
					$params[$id] = null;
				}
				break;
			}

			// 値を引きなおす
			$value = $params[$id];
			if( $value === null || $value === '' ) continue;

			$option = $id == 'Ent_OrderId' && $searchEntOrderIdAsNumeric;

			$expressions[] = SearchUtility::createExpression( $condition, $params, $this->_lists[$id], $option );
		}
		return $expressions;
	}

	/**
	 * @access private
	 *
	 * 現在のZend_Db_Selectを使用して検索を実行し、結果のリストを返す
	 *
	 * @var string $this->_selectへの句
	 * @var array $this->_selectへのWHERE句のパラメータ
	 * @return array 現在の$this->_selectを実行した結果の配列
	 */
	private function execSearch( $expression, $prms, $templatePattern ) {
	    $results = array();
		$schema = SearchUtility::getSearchConditions( $this->userInfo->EnterpriseId, $this->_getUseTemplatePatternSiteID($this->userInfo->EnterpriseId, $templatePattern) );

		$rows = $this->dbAdapter->query($expression)->execute($prms);

		foreach( $rows as $row ) {
				$newRow = array();
				foreach( $schema as $config ) {
				$col = $config['column'];
				$value = $row[ $col ];
				switch( $col ) {
					// T_OrderSummary.OrderItemNamesはすべての商品名を改行で結合したフィールドなので、
					// 表示する商品名は別カラム（ItemNameKj → 代表商品名）を参照して構築する
					case 'OrderItemNames':
						$count = (int)($row['ItemCount']) - 1;
						if( $count > 0 ) {
							$value = "{$row['ItemNameKj']}(他 $count 件)";
						} else {
							$value = $row['ItemNameKj'];
						}
						break;
					case 'ItemCount':
						// 商品点数は商品名で利用しているので出力に使用しない
						continue;
						break;
				}
				if( ! $config['hidden'] ) {
					$newRow[$col] = $value;
				}
			}
			// 状態判断に使用されるフィールドは必ず追加しておく
			foreach( array(
						'OrderSeq', 'OrderId', 'Cnl_CantCancelFlg',
						'IncreStatus', 'Cnl_Status', 'NameKj',
						'ClaimDate', 'Cnl_ReturnSaikenCancelFlg',
			            'CombinedClaimTargetStatus',
						'AppFormIssueCond',
						'DataStatus',
						'CloseReason',
                        'CreditTransferRequestFlg', 'UseAmount',
			         ) as $sp_col ) {

				$newRow["_$sp_col"] = $row[ $sp_col ];
			}

			$results[] = $newRow;
		}

		return $results;
	}

	/**
	 * このコントローラクラス固有のセッション名前空間を取得する
	 *
	 * @return Zend\Session\Container
	 */
	private function getSessionStorage() {
        return new Container( $this->app->getApplicationId() . '_SearchResults' );
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
                $enableinfo = array (
                        'isEnableServiceTargetClass' => 0,
                        'isEnableSelfBillingFlg'     => 0,
                );
            }
            else {
                $enableinfo = array (
                        'isEnableServiceTargetClass' => $row['ServiceTargetClass'],     // 役務対象区分[ServiceTargetClass](0：通常／1:役務)
                        'isEnableSelfBillingFlg'     => ($row['SelfBillingFlg'] == 1 && $row['HideToCbButton'] == 0) ? 1 : 0,// 請求書別送[SelfBillingFlg](0：行わない／1：行う)、同梱ツール別送ボタン非表示フラグ[HideToCbButton](0：非表示にしない（規定動作）／1：非表示にする)
                );
            }

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
     * 検索条件補正
     *
     * @param string $str オリジナルの条件式
     * @return string 補正後文字列
     */
	protected function _convertWhereCondition($str) {

        if (false !== strpos($str, 'DestNameKj')) {
            return $this->_convertWhereCondition_sub($str, 'DestNameKj', 'S');
        }
        else if (false !== strpos($str, 'NameKj')) {
            return $this->_convertWhereCondition_sub($str, 'NameKj', 'C');
        }
        else if (false !== strpos($str, 'DestNameKn')) {
            return $this->_convertWhereCondition_sub($str, 'DestNameKn', 'S');
        }
        else if (false !== strpos($str, 'NameKn')) {
            return $this->_convertWhereCondition_sub($str, 'NameKn', 'C');
        }
        else if (false !== strpos($str, 'Ent_OrderId')) {
            return $this->_convertWhereCondition_sub($str, 'Ent_OrderId', 'O');
        }
        else if (false !== strpos($str, 'OrderId')) {
            return $this->_convertWhereCondition_sub($str, 'OrderId', 'O');
        }
        else if (false !== strpos($str, 'DestUnitingAddress')) {
            return $this->_convertWhereCondition_sub($str, 'DestUnitingAddress', 'S');
        }
        else if (false !== strpos($str, 'UnitingAddress')) {
            return $this->_convertWhereCondition_sub($str, 'UnitingAddress', 'C');
        }
        else if (false !== strpos($str, 'DestPhone')) {
            return $this->_convertWhereCondition_sub($str, 'DestPhone', 'S');
        }
        else if (false !== strpos($str, 'Phone')) {
            return $this->_convertWhereCondition_sub($str, 'Phone', 'C');
        }
        else if (false !== strpos($str, 'ReceiptOrderDate')) {
            return $this->_convertWhereCondition_sub($str, 'ReceiptOrderDate', 'O');
        }
        else if (false !== strpos($str, 'SiteId')) {
            return $this->_convertWhereCondition_sub($str, 'SiteId', 'O');
        }
        else if (false !== strpos($str, 'OrderItemNames')) {
            return $this->_convertWhereCondition_sub($str, 'OrderItemNames', 'S');
        }
        else if (false !== strpos($str, 'IncreStatus')) {
            $sql  = " (CASE ";
            $sql .= "     WHEN (O.Dmi_Status = 1 OR (O.Dmi_Status IS NULL AND O.Incre_Status = 1)) THEN 1 ";
            $sql .= "     WHEN (O.Dmi_Status = -1 OR O.Incre_Status = - 1) THEN - 1 ";
            $sql .= "     ELSE 0 ";
            $sql .= " END) ";
            return $this->_convertWhereCondition_sub_sp($str, 'IncreStatus', $sql);
        }
        else if (false !== strpos($str, 'UseAmount')) {
            return $this->_convertWhereCondition_sub($str, 'UseAmount', 'O');
        }
        else if (false !== strpos($str, 'Ent_Note')) {
            return $this->_convertWhereCondition_sub($str, 'Ent_Note', 'O');
        }
        else if (false !== strpos($str, 'Deli_JournalIncDate')) {
            return $this->_convertWhereCondition_sub($str, 'Deli_JournalIncDate', 'S');
        }
        else if (false !== strpos($str, 'Deli_DeliveryMethod')) {
            return $this->_convertWhereCondition_sub($str, 'Deli_DeliveryMethod', 'S');
        }
        else if (false !== strpos($str, 'ExecScheduleDate')) {
            return $this->_convertWhereCondition_sub($str, 'ExecScheduleDate', 'P');
        }
        else if (false !== strpos($str, 'ApprovalDate')) {
            return $this->_convertWhereCondition_sub($str, 'ApprovalDate', 'L');
        }
        else if (false !== strpos($str, 'MailAddress')) {
            return $this->_convertWhereCondition_sub($str, 'MailAddress', 'C');
        }
        else if (false !== strpos($str, 'EntCustId')) {
            return $this->_convertWhereCondition_sub($str, 'EntCustId', 'C');
        }
        else if (false !== strpos($str, 'ServiceExpectedDate')) {
            return $this->_convertWhereCondition_sub($str, 'ServiceExpectedDate', 'O');
        }
        else if (false !== strpos($str, 'ClaimSendingClass')) {
            return $this->_convertWhereCondition_sub($str, 'ClaimSendingClass', 'O');
        }
        else if (false !== strpos($str, 'RegistDate')) {
            return $this->_convertWhereCondition_sub($str, 'RegistDate', 'O');
        }
        else if (false !== strpos($str, 'OutOfAmends')) {
            $sql  = " IFNULL(O.OutOfAmends, 0) ";
            return $this->_convertWhereCondition_sub_sp($str, 'OutOfAmends', $sql);
        }
        else if (false !== strpos($str, 'CancelReasonCode')) {
            return $this->_convertWhereCondition_sub($str, 'CancelReasonCode', 'L');
        }
        else if (false !== strpos($str, 'Deli_JournalNumberAlert')) {
            $sql  = " (CASE ";
            $sql .= "     WHEN ";
            $sql .= "         (SELECT MAX(AlertSign) FROM T_StagnationAlert WHERE AlertClass = 0 AND OrderSeq = O.OrderSeq AND ValidFlg = 1) ";
            $sql .= "     THEN ";
            $sql .= "         1 ";
            $sql .= "     ELSE 0 ";
            $sql .= " END) ";
            return $this->_convertWhereCondition_sub_sp($str, 'Deli_JournalNumberAlert', $sql);
        }
        else if (false !== strpos($str, 'Deli_JournalNumber')) {
            return $this->_convertWhereCondition_sub($str, 'Deli_JournalNumber', 'S');
        }
        else if (false !== strpos($str, 'Cnl_ReturnSaikenCancelFlg')) {
            $sql  = " (CASE ";
            $sql .= "     WHEN ISNULL(O.Cnl_ReturnSaikenCancelFlg) THEN 0 ";
            $sql .= "     ELSE O.Cnl_ReturnSaikenCancelFlg ";
            $sql .= " END) ";
            return $this->_convertWhereCondition_sub_sp($str, 'Cnl_ReturnSaikenCancelFlg', $sql);
        }
        else if (false !== strpos($str, 'RealCancelStatus')) {
            $sql  = " (CASE ";
            $sql .= "     WHEN (O.Cnl_Status = 0) THEN 0 ";
            $sql .= "     WHEN ";
            $sql .= "         ((O.Cnl_Status = 1) ";
            $sql .= "             AND (IFNULL(O.Cnl_ReturnSaikenCancelFlg, 0) = 0)) ";
            $sql .= "     THEN ";
            $sql .= "         1 ";
            $sql .= "     WHEN ";
            $sql .= "         ((O.Cnl_Status = 2) ";
            $sql .= "             AND (IFNULL(O.Cnl_ReturnSaikenCancelFlg, 0) = 0)) ";
            $sql .= "     THEN ";
            $sql .= "         2 ";
            $sql .= "     WHEN ";
            $sql .= "         ((O.Cnl_Status = 1) ";
            $sql .= "             AND (O.Cnl_ReturnSaikenCancelFlg = 1)) ";
            $sql .= "     THEN ";
            $sql .= "         11 ";
            $sql .= "     WHEN ";
            $sql .= "         ((O.Cnl_Status = 2) ";
            $sql .= "             AND (O.Cnl_ReturnSaikenCancelFlg = 1)) ";
            $sql .= "     THEN ";
            $sql .= "         12 ";
            $sql .= " END) ";
            return $this->_convertWhereCondition_sub_sp($str, 'RealCancelStatus', $sql);
        }
        else if (false !== strpos($str, 'ArrivalConfirmAlert')) {
            $sql  = " (CASE ";
            $sql .= "     WHEN ";
            $sql .= "         (SELECT MAX(AlertSign) FROM T_StagnationAlert WHERE AlertClass = 1 AND OrderSeq = O.OrderSeq AND ValidFlg = 1) ";
            $sql .= "     THEN ";
            $sql .= "         1 ";
            $sql .= "     ELSE 0 ";
            $sql .= " END) ";
            return $this->_convertWhereCondition_sub_sp($str, 'ArrivalConfirmAlert', $sql);
        }
        else if (false !== strpos($str, 'IsWaitForReceipt')) {
            $sql  = " (CASE ";
            $sql .= "     WHEN CL.ClaimedBalance <= 0 THEN 1 ";
            $sql .= "     WHEN CL.ClaimedBalance < CL.ClaimAmount THEN 2 ";
            $sql .= "     WHEN CL.ClaimedBalance >= CL.ClaimAmount or  ";
            $sql .= "          (CL.ClaimId is null and O.ClaimSendingClass >= 12 and O.DataStatus = 41 and O.ConfirmWaitingFlg = 1) THEN 3 ";
            $sql .= "     ELSE 4 ";
            $sql .= " END) ";
            return $this->_convertWhereCondition_sub_sp($str, 'IsWaitForReceipt', $sql);
        }
        else if (false !== strpos($str, 'ReceiptDate')) {
            return $this->_convertWhereCondition_sub($str, 'ReceiptDate', 'RC');
        }
        else if (false !== strpos($str, 'NgNoGuaranteeChange')) {
            $sql  = " (CASE ";
            $sql .= "     WHEN ";
            $sql .= "         ((O.Dmi_Status = -1 OR O.Incre_Status = -1) ";
            $sql .= "             AND (AO.NgButtonFlg = 1 AND CURDATE() <= AO.NoGuaranteeChangeLimitDay)) ";
            $sql .= "     THEN ";
            $sql .= "         1 ";
            $sql .= "     WHEN ";
            $sql .= "         ((O.Dmi_Status = -1 OR O.Incre_Status = -1) ";
            $sql .= "             AND ((AO.NgButtonFlg = 1 AND CURDATE() > AO.NoGuaranteeChangeLimitDay)";
            $sql .= "             OR   IFNULL(AO.NgButtonFlg, 0) = 0)) ";
            $sql .= "     THEN ";
            $sql .= "         0 ";
            $sql .= " END) ";
            return $this->_convertWhereCondition_sub_sp($str, 'NgNoGuaranteeChange', $sql);
        }
        else if (false !== strpos($str, 'CancelReason')) {
            return $this->_convertWhereCondition_sub($str, 'CancelReason', 'L');
        }
        else if (false !== strpos($str, 'RequestStatus')) {
            return $this->_convertWhereCondition_sub($str, 'RequestStatus', 'EC');
        }
        else if (false !== strpos($str, 'RequestSubStatus')) {
            return $this->_convertWhereCondition_sub($str, 'RequestSubStatus', 'EC');
        }
        else if (false !== strpos($str, 'RequestCompDate')) {
            return $this->_convertWhereCondition_sub($str, 'RequestCompDate', 'EC');
        }
        else if (false !== strpos($str, 'CreditTransferMethod1')) {
            $sql  = " (CASE CH.CreditTransferMethod ";
            $sql .= "     WHEN '3' THEN '1' ";
            $sql .= "     ELSE 0 ";
            $sql .= " END) ";
            return $this->_convertWhereCondition_sub_sp($str, 'CreditTransferMethod1', $sql);
        }
        else if (false !== strpos($str, 'CreditTransferMethod2')) {
            $sql  = " (CASE CH.CreditTransferMethod ";
            $sql .= "     WHEN '1' THEN '1' ";
            $sql .= "     ELSE 0 ";
            $sql .= " END) ";
            return $this->_convertWhereCondition_sub_sp($str, 'CreditTransferMethod2', $sql);
        } else if (false !== strpos($str, 'ReceiptClass')) {
            return $this->_convertWhereCondition_sub($str, 'ReceiptClass', 'RC');
        }
        else if (false !== strpos($str, 'ReceiptProcessDate')) {
            return $this->_convertWhereCondition_sub($str, 'ReceiptProcessDate', 'RC');
        }

        return $str;// 上記の何れにも該当しないときはオリジナルの文字列を戻す
	}

    /**
     * 検索条件補正(サブ関数)
     *
     * @param string $str オリジナルの条件式
     * @param string $fieldName フィールド名(Ex..'NameKj')
     * @param string $prefix プレフィックス(テーブル名のエイリアス Ex..'C')
     * @return string 補正後文字列
     */
	protected function _convertWhereCondition_sub($str, $fieldName, $prefix) {
	    $ret = str_replace($fieldName, ($prefix . '.' . $fieldName), $str);
	    return str_replace((':' . $prefix . '.' . $fieldName), (':' . $fieldName), $ret);
	}

	/**
	 * 検索条件補正(サブSP関数)
	 *
	 * @param string $str オリジナルの条件式
	 * @param string $fieldName フィールド名(Ex..'NameKj')
	 * @param string $spstr 特殊条件文
	 * @return string 補正後文字列
	 */
	protected function _convertWhereCondition_sub_sp($str, $fieldName, $spstr) {
        $bind_org = (':' . $fieldName);
        $ret = str_replace($bind_org, '__SPECIAL_BIND_STRING__', $str);
        $ret = str_replace($fieldName, $spstr, $ret);
        return str_replace('__SPECIAL_BIND_STRING__', $bind_org, $ret);
	}

	/**
	 * 使用テンプレートパターン(サイト)ID取得
	 *
	 * @param string $eid 加盟店ID
	 * @param string | null $sid サイトID
	 * @return string | null 使用テンプレートパターン(サイト)ID
	 * @see 確定できない場合はnullが戻る
	 */
	protected function _getUseTemplatePatternSiteID($eid, $sid) {

        // 1. 有効な加盟店サイト設定の有無を確認し、存在すればその値(サイトID:TemplatePattern)を戻す
        $sql = " SELECT COUNT(1) AS cnt FROM M_TemplateHeader WHERE ValidFlg = 1 AND TemplateId = :TemplateId AND TemplateClass = :TemplateClass AND Seq = :Seq AND TemplatePattern = :TemplatePattern ";
        $cnt = $this->app->dbAdapter->query($sql)->execute(
            array(  ':TemplateId'       => 'CKA01005_1',
                    ':TemplateClass'    => 2,
                    ':Seq'              => $eid,
                    ':TemplatePattern'  => $sid
            ))->current()['cnt'];
        if ($cnt == 1) {
            return $sid;
        }

        // 2. 同一加盟店での設定の有無を確認し、存在すればその最小値(サイトID:TemplatePattern)を戻す
        //    (取得できない時はnullを戻す)
        $sql = " SELECT MIN(TemplatePattern) AS MinTemplatePattern FROM M_TemplateHeader WHERE ValidFlg = 1 AND TemplateId = :TemplateId AND TemplateClass = :TemplateClass AND Seq = :Seq ";
        $minTemplatePattern = $this->app->dbAdapter->query($sql)->execute(
            array(  ':TemplateId'       => 'CKA01005_1',
                    ':TemplateClass'    => 2,
                    ':Seq'              => $eid
            ))->current()['MinTemplatePattern'];
        return $minTemplatePattern;
	}

	public function noguaranteeAction()
	{
	    try
	    {
	        $params = $this->getParams();

	        // ユーザーIDの取得
	        $obj = new TableUser( $this->app->dbAdapter );
	        getUserInfoForMember( $this->app, $userClass, $seq );
	        $userId = $obj->getUserId( $userClass, $seq );
	        $userNm = $obj->getUserName($userId);

	        $oseq = isset($params['OrderSeq']) ? $params['OrderSeq'] : 0;
	        $mdlo = new TableOrder($this->app->dbAdapter);
	        $odata = $mdlo->find($oseq)->current();
	        $udata['Incre_Note'] = date('Y-m-d H:i ') . $userNm . " 【無保証に変更】" . "\n" . $odata['Incre_Note'];
	        $udata['OutOfAmends'] = '1';

	        // 注文情報 更新
	        $mdlo->saveUpdate($udata, $oseq);

	        // 会計注文 NG無保証変更日 更新
	        $updata['NgNoGuaranteeChangeDate'] = date("Y-m-d H:i:s");
	        $mdlao = new ATableOrder($this->app->dbAdapter);
	        $mdlao->saveUpdate($updata, $oseq);

	        // 与信NG復活機能の利用が可能な場合だけ処理する
	        if ($this->canOrderRevival($oseq))
	        {
	            $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
	            try {
	                $udata["Incre_Status"]      = 1;    // 社内与信OK
	                $udata["Dmi_Status"]        = 1;    // DMI与信OK
	                $udata["DataStatus"]        = 31;   // 伝票入力待ち
	                $udata["CloseReason"]       = 0;    // クローズ理由を0にしておく。
	                $udata["CreditNgHiddenFlg"] = 0;    // 与信NG非表示フラグオフ

	                $mdlo = new TableOrder($this->app->dbAdapter);
	                $mdlo->saveUpdate($udata, $oseq);

	                // 注文履歴へ登録
	                $history = new CoralHistoryOrder($this->app->dbAdapter);
	                $history->InsOrderHistory($oseq, 27, $userId);

	                // 伝票番号の仮登録実行
	                $shippingLogic = new \models\Logic\LogicShipping($this->app->dbAdapter, $userId);
	                $datastatus = $udata["DataStatus"];
	                if ($udata["DataStatus"] == 31) {
	                    $jnResult = $shippingLogic->registerTemporaryJournalNumber($oseq);
	                    $datastatus = ($jnResult) ? 41 : $datastatus;
	                }

	                // テスト注文時のクローズ処理
	                if ($datastatus == 41) {
	                    $shippingLogic->closeIfTestOrder($oseq);
	                }

	                $this->app->dbAdapter->getDriver()->getConnection()->commit();
	            }
	            catch(\Exception $e) {
	                $this->app->dbAdapter->getDriver()->getConnection()->rollBack();
	                return $this->_redirect('error/nop');
	            }
	        }

	        // 注文情報の取得
	        $orderitem = $mdlo->find($oseq)->current();

	        $coralmail = new CoralMail($this->app->dbAdapter, $this->app->smtpServer);

	        $coralmail->SendCbNoGuaranteeChange(
	        $orderitem['EnterpriseId'],
	        $orderitem['SiteId'],
	        $orderitem['OrderId'],
	        $oseq,
	        $userId
	        );

	        $coralmail->SendEntNoGuaranteeChange(
	        $orderitem['EnterpriseId'],
	        $oseq,
	        $orderitem['OrderId'],
	        $userId
	        );


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
	 * 指定注文が与信NG復活機能による復活が可能であるかを判断する。
	 *
	 * @access protected
	 * @param int $oseq 注文SEQ
	 * @return boolean
	 */
	protected function canOrderRevival($oseq)
	{
	    $orders = new TableOrder($this->app->dbAdapter);

	    $order = $orders->find($oseq)->current();
	    if(!$order) return false;                       // 注文SEQ不正時はfalse
	    if($order['DataStatus'] != 91) return false;    // 注文がクローズされていなければfalse
	    if($order['CloseReason'] != 3) return false;    // クローズ事由が与信NGでなければfalse

	    $enterprises = new TableEnterprise($this->app->dbAdapter);
	    $ent = $enterprises->find($order['EnterpriseId'])->current();
	    if(!$ent) return false;                         // 事業者が見つからない場合はfalse
	    if($ent['OrderRevivalDisabled']) return false;  // 与信NG復帰が事業者で禁止されている場合はfalse

	    // ここまでたどり着いたら利用可能
	    // → 与信NGクローズされていて且つ事業者が与信NG復活機能の利用を禁止されていない
	    return true;
	}
}
