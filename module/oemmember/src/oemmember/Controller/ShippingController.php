<?php
namespace oemmember\Controller;

use oemmember\Application;
use Coral\Coral\Controller\CoralControllerAction;
use Coral\Base\IO\BaseIOCsvReader;
use Coral\Coral\CsvHandler\CoralCsvHandlerPayingAndSales;
use Coral\Coral\CsvHandler\CoralCsvHandlerLine;
use models\Table\TableDeliMethod;
use models\Table\TableEnterprise;
use models\Table\TableOrder;
use models\Table\TableOrderItems;
use models\Table\TablePayingAndSales;
use models\Table\TableOrderSummary;
use models\Table\TableOemSettlementFee;
use models\Table\TableCode;
use models\Table\TableUser;
use models\Table\TableTemplateHeader;
use models\Table\TableTemplateField;
use models\Table\TableSystemProperty;
use models\Table\TablePricePlan;
use models\Table\TableSite;
use models\Logic\MergeOrder\LogicMergeOrderHelper;
use models\Logic\LogicTemplate;
use Zend\Config\Reader\Ini;
use Zend\Session\Container;
use Zend\Db\ResultSet\ResultSet;
use Zend\Json\Json;
use Coral\Coral\History\CoralHistoryOrder;
use models\Logic\LogicCampaign;
use models\Table\ATablePayingAndSales;

/**
 * 注文登録コントローラ
 *
 */
class ShippingController extends CoralControllerAction {
    /**
     * 一括登録時のファイルフィールドのname属性
     *
     * @var string
     */
    const UPLOAD_FIELD_NAME = 'Csv_File';

    /**
     * SELECT要素によるリスト選択項目で「未選択」を表すフィールド値の定数
     *
     */
    const UNSELECT_LIST_ITEM = -99;

    /**
     * ビューコンポーネントのルートディレクトリパス
     *
     * @var string
     */
    protected $_componentRoot = './application/views/components';

    /**
     * DBアダプタ
     *
     * @var Adapter
     */
    private $dbAdapter;

    /**
     * 事業者のアカウント情報
     *
     * @var mixed
     */
    private $userInfo;

    /**
     * 伝票入力時に必要な注文データを問い合わせるSQLスクリプトソース
     *
     * @var string
     */
    private $orderQuery;

    /**
     * 配送先マスタデータ
     *
     * @var TableDeliMethod
     */
    private $deliMasters;

    /**
     * @var array
     */
    private $csv_schema;

    /**
     * CSVファイルを読み込んだときのT_OrderItemsの更新時間
     *
     * @var array
     */
    private $orderItemsUpdateDate;

    /**
     * 処理対象の日付リスト
     *
     * @access private
     * @var array
     */
    private $target_dates = array();

    /**
     * 処理対象日数
     *
     * @access private
     * @var int
     */
    private $limitDaySpan = 30;

    /**
     * ビューに割り当てる、select要素向けの連想配列を格納する配列
     *
     * @var array
     */
    protected $_lists;

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
            ->addStyleSheet( '../../oemmember/css/shipping.css' )
            ->addStyleSheet( '../../oemmember/css/shipping_sp.css' )
            ->addStyleSheet( '../../css/base.ui.tableex.css' )
            ->addStyleSheet( '../../css/search.css' )
            ->addStyleSheet( '../../css/base.ui.datepicker.css' )
            ->addStyleSheet( '../../css/base.ui.tableex.css' );


        // メニュー情報をマスタから取得
        $menu_info = $app->getMenuInfo();

        foreach( $menu_info as $key => $info ) {
            $this->view->assign( $key, $info );
        }

        // ログイン中のアカウント情報取得
        $this->userInfo = $app->authManager->getUserInfo();

        $this->orderQuery = <<<Q_END
SELECT
    O.OrderSeq,
    O.OrderId,
    O.Ent_OrderId,
    O.ReceiptOrderDate,
    O.EnterpriseId,
    O.SiteId,
    O.UseAmount,
    C.CustomerId,
    C.NameKj,
    C.Phone,
    D.UnitingAddress,
    I.OrderItemId,
    O.DataStatus,
    O.CloseReason,
    CASE O.DataStatus
        WHEN 31 THEN 1
        ELSE 0
    END AS CanInput,
    COALESCE(
        O.Incre_DecisionDate,
        DATE('1900-01-01' )
    ) AS ConditionFixedDate,
    (
        SELECT COUNT(*)
        FROM T_Cancel
        WHERE OrderSeq = O.OrderSeq AND
              ValidFlg = 1
    ) AS CancelCount,
    F_GetLoginUserName( O.RegistId ) AS RegistName,
    O.RegistDate,
    F_GetLoginUserName( O.UpdateId ) AS UpdateName,
    O.UpdateDate,
    O.CombinedClaimTargetStatus,
    O.Incre_Status
FROM
    T_Order O INNER JOIN
    T_Enterprise E ON E.EnterpriseId = O.EnterpriseId INNER JOIN
    T_Customer C ON C.OrderSeq = O.OrderSeq INNER JOIN
    T_OrderItems I ON I.OrderSeq = O.OrderSeq INNER JOIN
    T_DeliveryDestination D ON I.DeliDestId = D.DeliDestId INNER JOIN
    T_Site S ON S.SiteId = O.SiteId
WHERE
    O.EnterpriseId = :EnterpriseId AND
    I.OrderItemId = (
        SELECT MIN( OrderItemId )
        FROM T_OrderItems
        WHERE OrderSeq = O.OrderSeq AND DataClass = 1 AND ValidFlg = 1
    ) AND
    (
        (
            O.DataStatus = 31 AND
            O.Cnl_Status = 0 AND
            O.Incre_Status = 1      # 社内与信OK
        ) OR (
            O.DataStatus = 91 AND
            O.CloseReason = 3 AND
            DATEDIFF(
                CURRENT_DATE,
                COALESCE(
                    O.Incre_DecisionDate,
                    DATE('1900-01-01')
                )
            ) <= IFNULL(E.CreditNgDispDays,7)
        ) OR (
            O.DataStatus < 31 AND
            O.Cnl_Status = 0
        )
    ) AND
    O.CreditNgHiddenFlg = 0 AND     # 与信NG非表示フラグOFF
    S.ServiceTargetClass <> 1 AND   # 役務区分でない
    O.ValidFlg = 1 AND
    C.ValidFlg = 1 AND
    I.ValidFlg = 1 AND
    D.ValidFlg = 1 AND
    S.ValidFlg = 1
ORDER BY
    CASE E.DispOrder1
        WHEN 0 THEN O.OrderId
        WHEN 1 THEN C.NameKj
        WHEN 2 THEN O.Ent_OrderId
        ELSE O.ReceiptOrderDate
    END,
    CASE E.DispOrder2
        WHEN 0 THEN O.OrderId
        WHEN 1 THEN C.NameKj
        WHEN 2 THEN O.Ent_OrderId
        ELSE O.ReceiptOrderDate
    END,
    CASE E.DispOrder3
        WHEN 0 THEN O.OrderId
        WHEN 1 THEN C.NameKj
        WHEN 2 THEN O.Ent_OrderId
        ELSE O.ReceiptOrderDate
    END
Q_END;
        // 配送先マスタの取得
        $lgc = new \models\Logic\LogicDeliveryMethod($app->dbAdapter);
        $ary = $lgc->getEnterpriseDeliveryMethodList($app->authManager->getUserInfo()->EnterpriseId, false);
        $this->deliMasters = array();
        $deliMasters = array();
        foreach( $ary as $key => $val ) {
            $sql = " SELECT * FROM M_DeliveryMethod WHERE DeliMethodId = :DeliMethodId ";
            $deliMasters[] = $this->dbAdapter->query($sql)->execute(array(':DeliMethodId' => $key))->current();
        }
        $this->deliMasters = $deliMasters;

        // 使用できる配送先をビューへ登録 (2015/04/02)
        $this->view->assign( 'deliv_masters', $deliMasters );

        // 伝票登録絞込条件をビューへ登録 (2015/04/01)
        $codeId = 88;
        $code = new TableCode( $this->dbAdapter );
        $filters = ResultInterfaceToArray( $code->getMasterByClass( $codeId ) );

        $this->view->assign( 'credit_filters', $filters );
        $this->view->assign( 'cssName', "shipping" );

        // 日付リスト初期化
        $this->target_dates = $this->generateChangeTargetDates();

        // このコントローラでは実行限界時間を未設定にする
        ini_set( 'max_execution_time', 0 );

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
    }

    /**
     * indexアクション。registActionへフォワードされる
     *
     */
    public function indexAction() {
        return $this->_forward( 'regist' );
    }

    /**
     * registアクション。個別伝票入力フォームを表示する
     *
     */
    public function registAction() {
        $this->setPageTitle( '配送伝票番号入力（個別入力）' );
        $this->addStyleSheet( '../../css/base.ui.modaldialog.css' )
             ->addStyleSheet( '../../oemmember/css/shipping_regist.css' )
             ->addStyleSheet( '../../css/base.ui.tableex.css' )
             ->addStyleSheet( '../../css/base.ui.customlist.css' )
             ->addJavaScript( '../../js/base.ui.tableex.js' )
             ->addJavaScript( '../../js/base.ui.customlist.js')
             ->addJavaScript( '../../js/base.ui.modaldialog.js' )
             ->addJavaScript( '../../js/form_validator.js' );

        $params = $this->getParams();
        $filter = isset($params['credit_filter']) ? $params['credit_filter'] : 0;

        // 伝票入力可能な注文データのリストを取得
        $order_list = $this->getTargets();

        // confirmActionから戻された場合はセッションデータと元データをマージ
        $session = $this->getSessionStorage();
        if( isset( $session->posts ) ) {
            // confirmアクションから差し戻された場合のみセッションデータを反映
            if( $this->params()->fromRoute('mode') == 'rollback' ) {
                $newList = array();
                foreach( $order_list as $row ) {
                    foreach( $session->posts as $post ) {
                        if( $row['OrderSeq'] == $post->OrderSeq ) {
                            $row = array_merge( $row, (array)$post );
                            break;
                        }
                    }
                    $newList[] = $row;
                }
                $order_list = $newList;

                // NG非表示選択リストの生成とビューへのアサイン
                $creditNgHiddenFlgList = array();
                foreach ($session->posts as $post) {
                    if (isset($post->CreditNgHiddenFlg)) {
                        $creditNgHiddenFlgList[] = $post->CreditNgHiddenFlg;
                    }
                }
                $this->view->assign( 'creditNgHiddenFlgList', $creditNgHiddenFlgList );
            }
            // セッションデータを廃棄
            unset( $session->posts );
        }

        // 入力待ち件数と与信NG件数をカウント
        $counts = array(
            'order' => 0,
            'progress' => 0,
            'ng' => 0
        );

        // フィルター用のリスト作成 (2015/03/31)
        $credit_ok_list = array();
        $cregit_ng_list = array();
        foreach( $order_list as $row ) {
            if( $row['CanInput'] ) {
                $counts['order'] = $counts['order'] + 1;
                // 与信OKのみ
                $credit_ok_list[] = $row;
            } else {
                $key = $row['DataStatus'] < 31 ? 'progress' : 'ng';
                $counts[$key] = $counts[$key] + 1;
                // 与信NGのみ
                if( $row['DataStatus'] > 31 ) {
                    $credit_ng_list[] = $row;
                }
            }
        }

        // 与信OKのみフィルター
        if( $filter == 1 ) {
            $order_list = $credit_ok_list;
        }
        // 与信NGのみフィルター
        elseif( $filter == 2 ) {
            $order_list = $credit_ng_list;
        }

        // 注文データリストをビューへ登録
        $this->view->assign( 'order_list', $order_list );

        // 合計件数、入力待ち件数、与信NG件数をビューへ登録
        $order_list_count = 0;
        if (!empty($order_list)) {
            $order_list_count = count($order_list);
        }
        $this->view->assign( 'list_count', $order_list_count);
        $this->view->assign( 'order_count', $filter == 2 ? 0 : $counts['order'] );
        $this->view->assign( 'progress_count', $filter == 0 ? $counts['progress'] : 0 );
        $this->view->assign( 'ng_count', $filter == 1 ? 0 : $counts['ng'] );
        $this->view->assign( 'credit_filter', $filter );
        $this->view->assign( 'cancelList', $this->_lists['CancelReasonCode'] );

        // アプリケーションクラスへのユーザーIDセット
        $dbparam = $this->dbAdapter;
        $app = Application::getInstance();

        // ユーザーIDの取得
        $obj = new TableUser( $dbparam );
        getUserInfoForMember( $app, $userClass, $seq );
        $userId = $obj->getUserId( $userClass, $seq );
        $this->view->assign( 'userId', $userId );

        return $this->view;
    }

    /**
     * キャンセル申請アクション
     */
    public function cancelAction() {
        return $this->view;
    }

    /**
     * confirmアクション。個別伝票入力の確認画面を表示する
     *
     */
    public function confirmAction() {
        $this->setPageTitle( '入力内容の確認' )
            ->addStyleSheet( '../../oemmember/css/shipping_confirm.css' );
        $this->setCommonScripts();

        // フォームからの入力はJSONなのでデコードする
        $posts = Json::decode( process_slashes( $this->params()->fromPost('regist_data') ) );

        if (is_null($posts)) { $posts = array(); }

        //伝票番号チェック
        $caution = 0;
        $checkData = array();
        $orderItem = new TableOrderItems( $this->dbAdapter );
        foreach($posts as $post) {
            if("" !== $post->Deli_JournalNumber) {
                $jounalNumber = $orderItem->getJournalNumber($post->Deli_JournalNumber, $post->Deli_DeliveryMethod, $this->userInfo->EnterpriseId);
                if(trim(nvl($jounalNumber)) != '') {
                    $caution = 1;
                } else {
                    $checkData[] = $post;
                }
            }
        }

        //伝票番号重複チェック
        $journalCaution = 0;
        $duplication = array();
        foreach($posts as $post) {
            $checkDataCount = 0;
            if (!empty($checkData)) {
                $checkDataCount = count($checkData);
            }
            for($i = 0; $i < $checkDataCount; $i++) {
                if($checkData[$i]->Deli_JournalNumber == $post->Deli_JournalNumber && $checkData[$i]->OrderSeq !== $post->OrderSeq) {
                    $journalCaution = 1;
                    $duplication[] = $checkData[$i]->OrderSeq;
                }
            }
        }

        // セッションデータへ保存
        $session = $this->getSessionStorage();
        // NG非表示項目も$postsに追加 (2015/03/31)
        $hide_ng = $this->params()->fromPost( 'hide_ng' );
        $postsCount = 0;
        if (!empty($posts)) {
            $postsCount = count($posts);
        }
        $cnt = $postsCount;
        if( isset( $hide_ng ) ) {
            $sql =<<<EOQ
SELECT DISTINCT '' AS Deli_JournalNumber
,      o.OrderSeq
,      '' AS Deli_DeliveryMathod
,      o.ReceiptOrderDate
,      o.OrderId
,      c.NameKj
,      c.Phone
,      o.UseAmount
,      dd.UnitingAddress
,      o.Ent_OrderId
FROM   T_Order o
       INNER JOIN T_Customer c ON (c.OrderSeq = o.OrderSeq)
       INNER JOIN T_OrderItems oi ON (oi.OrderSeq = o.OrderSeq)
       INNER JOIN T_DeliveryDestination dd ON (dd.DeliDestId = oi.DeliDestId)
WHERE  o.OrderSeq = :OrderSeq
EOQ;
            foreach( $hide_ng as $value ) {
                $row = $this->dbAdapter->query($sql)->execute(array(':OrderSeq' => $value))->current();

                $posts[$cnt] = new \stdClass();
                $posts[$cnt]->Deli_JournalNumber = $row['Deli_JournalNumber'];
                $posts[$cnt]->OrderSeq = $row['OrderSeq'];
                $posts[$cnt]->Deli_DeliveryMathod = $row['Deli_DeliveryMathod'];
                $posts[$cnt]->ReceiptOrderDate = str_replace('-', '/', $row['ReceiptOrderDate']);
                $posts[$cnt]->OrderId = $row['OrderId'];
                $posts[$cnt]->NameKj = $row['NameKj'];
                $posts[$cnt]->Phone = $row['Phone'];
                $posts[$cnt]->UseAmount = number_format($row['UseAmount'], 0);
                $posts[$cnt]->UnitingAddress = $row['UnitingAddress'];
                $posts[$cnt]->Ent_OrderId = $row['Ent_OrderId'];
                $posts[$cnt]->CreditNgHiddenFlg = $value;

                $cnt++;
            }
        }
        $session->posts = $posts;

        $this->view->assign( 'postData', $posts );
        $this->view->assign( 'caution', $caution );
        $this->view->assign( 'journalCaution', $journalCaution);
        $this->view->assign( 'duplication', $duplication);

        return $this->view;
    }

    /**
     * saveアクション。個別伝票入力のデータをDBへ登録する
     *
     */
    public function saveAction() {
        // 戻り先を取得 (2015/04/02)
        $back = $this->params()->fromRoute( 'back' );

        // セッションから入力データを復元
        $session = $this->getSessionStorage();
        if( ! isset( $session->posts ) || empty($session->posts) ) throw new \Exception( 'データが登録されていません' );

        $posts = $session->posts;
        // セッションデータを廃棄
        unset( $session->posts );

        // モデルの初期化
        $dbparam = $this->dbAdapter;
        $orderTable = new TableOrder( $dbparam );
        $itemTable = new TableOrderItems( $dbparam );
        $payingTable = new TablePayingAndSales( $dbparam );
        // サマリテーブルを使用（2008.02.15追加）
        $summaryTable = new TableOrderSummary( $dbparam );

        // OEM決済手数料の初期化
        $oemSettlementFeeTable = new TableOemSettlementFee( $dbparam );

        // アプリケーションクラスへのユーザーIDセット
        $app = Application::getInstance();

        // ユーザーIDの取得
        $obj = new TableUser( $dbparam );
        getUserInfoForMember( $app, $userClass, $seq );
        $userId = $obj->getUserId( $userClass, $seq );

        // 配送伝票番号登録ロジック･インスタンス生成
        $shippingLogic = new \models\Logic\LogicShipping($this->dbAdapter, $userId);

        $dbparam->getDriver()->getConnection()->beginTransaction();
        try {
            foreach($posts as $post) {
                if( property_exists( $post, 'CreditNgHiddenFlg' ) ) {
                    // 与信NG非表示フラグ
                    $order = $orderTable->find( $post->OrderSeq )->current();
                    $order['CreditNgHiddenFlg'] = 1;
                    $order['UpdateId'] = $userId;
                    $orderTable->saveUpdate( $order, $order['OrderSeq'] );
                    continue;
                }
                // 配送伝票番号登録不要の配送会社があるため下記条件削除 (2015/06/03)
//                if(trim(nvl($post->Deli_JournalNumber)) != '') {
                    $order = $orderTable->find( $post->OrderSeq )->current();

                    // キャンセルされている場合スキップ
                    if ($order['Cnl_Status'] != 0) {
                        continue;
                    }

                    $order['UpdateId'] = $userId;

                    // 現在更新可能なデータでない場合は無視^H^H例外扱い（08.04.03）
                    // 更新可能なデータとして 一括請求書印刷待ち も追加 (2015/06/03)
                    // 更新可能なのは、「請求書発行待ち」「入金待ち」
                    if( ( $order['DataStatus'] != 31 && $back == 'registCsv' ) || ( !($order['DataStatus'] == 41 || $order['DataStatus'] == 51) && $back == 'changeCsv' ) ) {
                        throw new \Exception( "注文ID {$post->OrderId} の状態が不正です" );
                    }

                    if ($back != 'changeCsv') {
                        // ステータスを「請求書印刷待ち」に変更
                        $order['DataStatus'] = 41;
                    }
                    // 指定の配送方法からキャンセル可能フラグを設定
                    $deli = $this->findDeliveryMaster( $post->Deli_DeliveryMethod );
                    if( $deli == null ) {
                        $order['Cnl_CantCancelFlg'] = 0;
                    } else {
                        $order['Cnl_CantCancelFlg'] = $deli['EnableCancelFlg'] == 1 ? 0 : 1;
                    }

// Mod By Takemasa(NDC) 20151203 Stt メール便(伝票番号登録区分が[0：不要]な配送方法時)の着荷確認を取らない
//                     // 伝票番号登録不要の配送会社の場合、着荷確認済みとする。
//                     if ($deli['JournalRegistClass'] == 0) {
//                         $order['Deli_ConfirmArrivalFlg'] = 1;
//                         $order['Deli_ConfirmArrivalDate'] = date('Y-m-d H:i:s');
//                     } else {
//                         $order['Deli_ConfirmArrivalFlg'] = 0;
//                     }
                    $order['Deli_ConfirmArrivalFlg'] = 0;
// Mod By Takemasa(NDC) 20151203 End メール便(伝票番号登録区分が[0：不要]な配送方法時)の着荷確認を取らない

                    //一括配送伝票番号修正（CSV）の場合、更新しない
                    if ($back != 'changeCsv') {
                        $orderTable->saveUpdate( $order,$order['OrderSeq'] );
                    }

                    // 注文商品データの生成
                    $rs = new ResultSet();
                    $order_items = $rs->initialize($itemTable->findByOrderSeq($post->OrderSeq))->toArray();
                    foreach( $order_items as $order_item ) {

                        $order_item['Deli_DeliveryMethod'] = $post->Deli_DeliveryMethod;
                        // 配送伝票番号登録不要の配送会社 の場合、ダミー伝票番号入力
                        // 但し、入力値がある場合はそれを優先する(20151119)
                        $mdls = new TableSite( $this->dbAdapter );
                        if( $deli['JournalRegistClass'] == 0 && strlen($post->Deli_JournalNumber) == 0) {
                            $mdlsp = new TableSystemProperty( $this->dbAdapter );
                            $order_item['Deli_JournalNumber'] = $mdlsp->getValue( '[DEFAULT]', 'systeminfo', 'DummyJournalNumber' );
                        }
                        else {
                            $order_item['Deli_JournalNumber'] = $post->Deli_JournalNumber;
                        }
                        $order_item['Deli_JournalIncDate'] = date('Y-m-d H:i:s');
// Mod By Takemasa(NDC) 20151203 Stt メール便(伝票番号登録区分が[0：不要]な配送方法時)の着荷確認を取らない
//                         // 配送伝票番号登録不要の配送会社の場合着荷確認済みとする
//                         if( $deli['JournalRegistClass'] == 0 ) {
//                             $order_item['Deli_ConfirmArrivalFlg'] = 1;
//                             $order_item['Deli_ConfirmArrivalDate'] = date('Y-m-d H:i:s');
//                             $order_item['Deli_ConfirmArrivalOpId'] = $userId;
//                         }
//                         else {
//                             $order_item['Deli_ConfirmArrivalFlg'] = 0;
//                         }
                        $order_item['Deli_ConfirmArrivalFlg'] = 0;
// Mod By Takemasa(NDC) 20151203 End メール便(伝票番号登録区分が[0：不要]な配送方法時)の着荷確認を取らない
                        $order_item['UpdateId'] = $userId;

                        $itemTable->saveUpdate( $order_item,$order_item['OrderItemId'] );
                    }
                    // 20131210 tkaki 請求取りまとめ　メール便の場合には請求取りまとめを落とす
                    $mghelper = new LogicMergeOrderHelper($this->dbAdapter, $post->OrderSeq);
                    // 配送方法の状況によって請求取りまとめを更新する
                    if($mghelper->chkCcTargetStatusByDelivery($post->Deli_DeliveryMethod) != 9) {
                        $order['CombinedClaimTargetStatus'] = $mghelper->chkCcTargetStatusByDelivery($post->Deli_DeliveryMethod);
                        //一括配送伝票番号修正（CSV）の場合、更新しない
                        if ($back != 'changeCsv') {
                            $orderTable->saveUpdate( $order,$order['OrderSeq'] );
                        }
                    }

                    // キャンペーン期間中はキャンペーン情報で更新/登録する
                    // 決済手数料率計算
                    $logic = new LogicCampaign($this->dbAdapter);
                    $campaign = $logic->getCampaignInfo($this->userInfo->EnterpriseId, $order['SiteId']);

                    // 2015/10/14 Y.Suzuki Mod 会計対応 Stt
                    // マスタから取得した請求手数料は税抜金額のため、消費税額を算出後、足しこむ。
                    $mdlsys = new TableSystemProperty($this->dbAdapter);
                    $taxRate = ($mdlsys->getTaxRateAt(date('Y-m-d')) / 100);

                    // 請求手数料
                    $claimFee = (int)($campaign['ClaimFeeBS'] + ($campaign['ClaimFeeBS'] * $taxRate));
                    // 2015/10/14 Y.Suzuki Mod 会計対応 End

                    // 立替・売上データの作成
                    $payRow = $payingTable->newRow(
                        $order['OrderSeq'],
                        $order['UseAmount'],
                        $campaign['SettlementFeeRate'],
                        $claimFee
                    );
                    $payRow['UpdateId'] = $userId;

// Del By Takemasa(NDC) 20151203 Stt メール便(伝票番号登録区分が[0：不要]な配送方法時)の着荷確認を取らない
//                     // 伝票番号登録不要の配送会社の場合は立替条件クリアフラグを1、立替条件クリア日を設定 (2015/04/02)
//                     // 補償外でない場合のみ
//                     if( $deli['JournalRegistClass'] == 0 && nvl($order['OutOfAmends'], 0) != 1) {
//                         $payRow['ClearConditionForCharge'] = 1;
//                         $payRow['ClearConditionDate'] = date('Y-m-d');
//                     }
// Del By Takemasa(NDC) 20151203 End メール便(伝票番号登録区分が[0：不要]な配送方法時)の着荷確認を取らない
                    if( $back == 'changeCsv' ) {
// Del By Takemasa(NDC) 20160222 Stt 伝票番号修正時はT_PayingAndSales更新しない
//                         $payingTable->saveUpdateWhere( $payRow, array( 'OrderSeq' => $order['OrderSeq'] ) );
// Del By Takemasa(NDC) 20160222 End 伝票番号修正時はT_PayingAndSales更新しない
                    }
                    else {
                        $payRow['RegistId'] = $userId;
                        $seq = $payingTable->saveNew( $payRow );        // 2015/10/14 Y.Suzuki 会計対応 Mod

                        // 2015/10/14 Y.Suzuki Add 会計対応 Stt
                        // 会計用項目のINSERT
                        $mdlatpas = new ATablePayingAndSales($this->dbAdapter);
                        $mdlatpas->saveNew(array('Seq' => $seq));
                        // 2015/10/14 Y.Suzuki Add 会計対応 End
                    }

                    // 注文サマリを更新（2008.02.15追加）
                    $summaryTable->updateSummary( $order['OrderSeq'], $userId );

                    // OEM対応(OEM決済手数料登録)
                    if(!is_null($order['OemId']) && $order['OemId'] != 0) {
                        if( $back == 'changeCsv' ) {
//                            $oemSettlementFeeTable->saveUpdateOemSettlementFee( $order );
                        } else {
                            $oemSettlementFeeTable->saveOemSettlementFee( $order );
                        }
                    }

                    // 注文履歴へ登録
                    $history = new CoralHistoryOrder($this->dbAdapter);
                    if( $back == 'changeCsv' ) {
                        $reasonCode = 32;       // 伝票修正
                    } else {
                        $reasonCode = 31;       // 伝票登録
                    }
                    $history->InsOrderHistory($order['OrderSeq'], $reasonCode, $userId);

//                }

                // テスト注文時のクローズ処理
                $shippingLogic->closeIfTestOrder($order['OrderSeq']);
            }
            $dbparam->getDriver()->getConnection()->commit();

        } catch(\Exception $err) {
            $this->orderItemsUpdateDate = array();
            $dbparam->getDriver()->getConnection()->rollBack();
            throw $err;
        }

        $this->orderItemsUpdateDate = null;
        if( $back == 'changeCsv' ) {
            return $this->_redirect( 'shipping/completeChangeCsv' );
        }
        else {
            return $this->_redirect( 'shipping/complete' );
        }
    }

    /**
     * completeアクション。個別伝票入力の完了画面を表示する
     *
     */
    public function completeAction() {
        $this->setPageTitle( '配送伝票入力完了' );
        $this->setCommonScripts();

        return $this->view;
    }

    /**
     * downloadアクション。一括伝票入力用のテンプレートCSVをクライアントへ送信する
     *
     */
    public function downloadAction() {
        $csv_rows = array();
        foreach( $this->getTargets() as $row ) {
            if( $row['CanInput'] ) {
                $csv_rows[] = $row;
            }
        }

        $templateId = 'CKA03011_1';    // 一括配送伝票入力雛形CSV兼一覧
        $templateClass = 2;
        $seq = $this->userInfo->EnterpriseId;
        $templatePattern = 0;

        $logicTemplate = new LogicTemplate( $this->dbAdapter );
        $response = $logicTemplate->convertArraytoResponse( $csv_rows, sprintf( 'Shipping_%s.csv', date('YmdHis') ), $templateId, $templateClass, $seq, $templatePattern, $this->getResponse() );

        if( $response == false ) {
            throw new \Exception( $logicTemplate->getErrorMessage() );
        }

        return $response;
    }

    /**
     * registCsvアクション。一括伝票入力フォームを表示する
     *
     */
    public function registCsvAction() {
        if( $this->params()->fromRoute('mode', 'rollback') != 'save' ) {
            $session = $this->getSessionStorage();
            if( isset( $session->posts ) ) unset( $session->posts );
        }
        $this->setPageTitle('一括配送伝票番号入力（CSV）');
        $this->setCommonScripts();
        $this->view->assign( 'field_name', self::UPLOAD_FIELD_NAME );

        return $this->view;
    }

    /**
     * changeCsvアクション。一括伝票修正フォームを表示する
     *
     */
    public function changeCsvAction() {
        if( $this->params()->fromRoute('mode', 'rollback') != 'save' ) {
            $session = $this->getSessionStorage();
            if( isset( $session->posts ) ) unset( $session->posts );
        }
        $mdle = new TableEnterprise($this->dbAdapter);
        $e = $mdle->find($this->userInfo->EnterpriseId)->current();
        $this->setPageTitle('一括配送伝票番号修正（CSV）');
        $this->setCommonScripts();
        $this->view->assign( 'field_name', self::UPLOAD_FIELD_NAME );
        $this->view->assign('count_list', $this->getChangeTargetsCount());
        $this->view->assign('selfbillingmode', $e['SelfBillingMode']);

        return $this->view;
    }

    /**
     * confirmCsvアクション。一括伝票入力CSVの内容確認画面を表示する
     *
     */
    public function confirmCsvAction() {
        $this->setCommonScripts();
        $this
        ->addStyleSheet( '../../oemmember/css/csv_table.css' )
        ->addStyleSheet( '../../oemmember/css/shipping_confirm_csv.css' );

        $this->csv_schema = $this->getCsvSchema();
        if( empty( $this->csv_schema ) ) {
            $this->view->assign( 'colSchema', new \stdClass() );
            $this->view->assign( 'errorRows', array(
                    new CoralCsvHandlerLine(
                    array( 0 => array( 'ファイル形式' => 'エラーが発生しました。システム管理者へご連絡ください' ) ),
                    0,
                    CoralCsvHandlerLine::TYPE_ERROR
                    ) )
            );

            $this->view->assign( 'back', 'registCsv' );

            return $this->view;
        }

        $templateId = 'CKA03011_1';    // 一括配送伝票入力雛形CSV兼一覧
        $templateClass = 2;
        $seq = $this->userInfo->EnterpriseId;
        $templatePattern = 0;

        $csv = $_FILES[ self::UPLOAD_FIELD_NAME ]['tmp_name'];

        if( ! preg_match( '/\.xl.$/i', $_FILES[ self::UPLOAD_FIELD_NAME ]['name'] ) && $csv != "" ) {
            $reader = new BaseIOCsvReader( $csv );
            $handler = new CoralCsvHandlerPayingAndSales( $reader, array(
                    CoralCsvHandlerPayingAndSales::OPTIONS_DBADAPTER => $this->dbAdapter,
                    CoralCsvHandlerPayingAndSales::OPTIONS_ENT_ID => $this->userInfo->EnterpriseId,
                    CoralCsvHandlerPayingAndSales::OPTIONS_DELI_MASTER => $this->deliMasters,
                    CoralCsvHandlerPayingAndSales::OPTIONS_ORDER_LIST => $this->getAllOrders(),
                    CoralCsvHandlerPayingAndSales::OPTIONS_CSV_SCHEMA => $this->csv_schema,
                    CoralCsvHandlerPayingAndSales::OPTIONS_MODE => CoralCsvHandlerPayingAndSales::MODE_REGIST
            ) );

            // CSV解析実行
            $logicTemplate = new LogicTemplate( $this->dbAdapter );
            $logicTemplate->setForceTitleClass(0);// タイトル行区分[0：なし]を強制する
            $rows = $logicTemplate->convertFiletoArray( $csv, $templateId, $templateClass, $seq, $templatePattern );

            if (!empty($rows) && !IsValidDate($rows[0]['ReceiptOrderDate'])) {
                // 1行目の[注文日]が日付型として有効と判断されない時は[ヘッダ行あり]と判断しarray_shiftを実施
                array_shift($rows);
            }
            $rowsCount = 0;
            if(!empty($rows)) {
                $rowsCount = count($rows);
            }
            for ($idx=0; $idx<$rowsCount; $idx++) {
                $cvtdate = fixDateString($rows[$idx]['ReceiptOrderDate']);
                $rows[$idx]['ReceiptOrderDate'] = (IsValidDate($cvtdate)) ? date('Y-m-d', strtotime($cvtdate)) : '';
            }

            $success = $rows == false ? false : true;

            // 検証実施
            if( $success != false ) {
                $line = 0;
                $continue = true;

                foreach( $rows as $row ) {
                    if( $continue == false ) {
                        break;
                    }
                    $validate = array();
                    foreach( $row as $value ) {
                        $validate[] = $value;
                    }
                    $result = $handler->validateLine( $validate, $line++, $reader );
                    if( is_array( $result ) ) {
                        $row = $result;
                    } else {
                        // コマンド定数が返されたら処理を制御
                        // それ以外の場合は特別な処理はしない
                        switch( $result ) {
                            case BaseIOCsvReader::COMMAND_STOP_READING:
                            case BaseIOCsvReader::COMMAND_STOP_AFTER_READING:
                                // 停止命令
                                $continue = false;
                                // COMMAND_STOP_READING は現在行も廃棄する
                                if( $cmd == BaseIOCsvReader::COMMAND_STOP_READING ) $row = null;
                                break;
                            case BaseIOCsvReader::COMMAND_SKIP_LINE:
                                // スキップ命令
                                $row = null;
                                break;
                        }
                    }
                }
                //伝票番号チェック
                $duplicateJournalNumberInDbOrderIdList  = array();    // 伝票番号の重複する注文IDのリスト(ﾃﾞｰﾀﾍﾞｰｽ内)
                $duplicateJournalNumberInCsvOrderIdList = array();    // 伝票番号の重複する注文IDのリスト(CSVﾌｧｲﾙ内)
                $caution = 0;
                $checkData = array();
                $orderItem = new TableOrderItems( $this->dbAdapter );
                $rows = $handler->getResults();

                $rowsCount = 0;
                if (!empty($rows)) {
                    $rowsCount = count($rows);
                }
                for($i = 0; $i < $rowsCount; $i++) {
                    $row = $rows[$i]->getData();
                    if("" !== $row['Deli_JournalNumber']) {
                        $jounalNumber = $orderItem->getJournalNumber($row['Deli_JournalNumber'], $row['Deli_DeliveryMethod'], $this->userInfo->EnterpriseId);
                        if(trim(nvl($jounalNumber)) != '') {
                            $caution = 1;
                            if (!in_array($row['OrderSeq'], $duplicateJournalNumberInDbOrderIdList)) {
                                $duplicateJournalNumberInDbOrderIdList[] = $row['OrderSeq'];
                            }
                        } else {
                            $checkData[] = $row;
                        }
                    }
                }

                //伝票番号重複チェック
                $csvCaution = 0;
                $duplication = array();
                for($i = 0; $i < $rowsCount; $i++) {
                    $row = $rows[$i]->getData();

                    $checkDataCount = 0;
                    if (!empty($checkData)) {
                        $checkDataCount = count($checkData);
                    }
                    for($j = 0; $j < $checkDataCount; $j++) {
                        if($checkData[$j]['Deli_JournalNumber'] == $row['Deli_JournalNumber'] && $checkData[$j]['OrderSeq'] !== $row['OrderSeq']) {
                            $csvCaution = 1;
                            $duplication[] = $checkData[$j]['OrderSeq'];
                            if (!in_array($checkData[$j]['OrderSeq'], $duplicateJournalNumberInCsvOrderIdList)) {
                                $duplicateJournalNumberInCsvOrderIdList[] = $checkData[$j]['OrderSeq'];
                            }
                        }
                    }
                }

                // ビューのタイトルを設定
                if( empty($handler->getExceptions()) ) {
                    $success = true;
                    $this->setPageTitle( '登録内容の確認' );
                }
                else {
                    $success = false;
                    $this->setPageTitle( '一括配送伝票入力　CSV登録エラー' );
                }

                foreach( $this->csv_schema as $schema ) {
                    if( $schema['Caption'] != null ) {
                        $headerRow[] = $schema['Caption'];
                    }
                }

                // 解析結果をビューにアサイン
                $allResultsCount = 0;
                if (!empty($handler->getAllResults())) {
                    $allResultsCount = count($handler->getAllResults());
                }
                $this->view->assign( 'colSchema', $this->csv_schema );
                $this->view->assign( 'headerRow', $headerRow );
                $this->view->assign( 'validRows', $handler->getResults() );
                $this->view->assign( 'errorRows', $handler->getExceptions() );
                $this->view->assign( 'warningRows', $handler->getWarnings() );
                $this->view->assign( 'allData', $handler->getAllResults() );
                $this->view->assign( 'totalCount', $allResultsCount);
                $this->view->assign( 'caution', $caution );
                $this->view->assign( 'csvCaution', $csvCaution);
                $this->view->assign( 'duplication', $duplication);
                $this->view->assign( 'duplicateJournalNumberInDbOrderIdList',  $duplicateJournalNumberInDbOrderIdList);
                $this->view->assign( 'duplicateJournalNumberInCsvOrderIdList', $duplicateJournalNumberInCsvOrderIdList);
            }
            else {
                $success = false;
                $this->setPageTitle( '一括配送伝票入力　CSV登録エラー' );
                $this->view->assign( 'colSchema', new \stdClass() );
                $this->view->assign( 'errorRows', array(
                    new CoralCsvHandlerLine(
                        array( 0 => array( 'ファイル形式' => $logicTemplate->getErrorMessage() ) ),
                        0,
                        CoralCsvHandlerLine::TYPE_ERROR
                    ) )
                );
            }
        } else {
            $success = false;
            $this->setPageTitle( '一括配送伝票入力　CSV登録エラー' );
            $this->view->assign( 'colSchema', new \stdClass() );
            $this->view->assign( 'errorRows', array(
                new CoralCsvHandlerLine(
                    array( 0 => array( 'ファイル形式' => 'ファイル形式が適切ではありません。CSVファイルを登録してください' ) ),
                    0,
                    CoralCsvHandlerLine::TYPE_ERROR
                ) )
            );
        }

        // エラーがなかったのでセッションオブジェクトに結果を保存
        if( $success ) {
            $rows = array();
            foreach( $handler->getResults() as $result ) {
                $rows[] = (object)$result->getData();
            }
            $session = $this->getSessionStorage();
            $session->posts = $rows;
        }

        $this->view->assign( 'back', 'registCsv' );

        return $this->view;
    }

    /**
     * confirmChangeCsvアクション。一括伝票修正CSVの内容確認画面を表示する
     *
     */
    public function confirmChangeCsvAction() {
        $templateId = 'CKA03011_3';    // 一括配送伝票更新
        $this->setCommonScripts();
        $this
        ->addStyleSheet( '../../oemmember/css/csv_table.css' )
        ->addStyleSheet( '../../oemmember/css/shipping_confirm_csv.css' );

        $this->csv_schema = $this->getCsvSchema($templateId);
        if( empty( $this->csv_schema ) ) {
            $this->view->assign( 'hasData', false );
            $this->view->assign( 'colSchema', new \stdClass() );
            $this->view->assign( 'errorRows', array(
                    new CoralCsvHandlerLine(
                    array( 0 => array( 'ファイル形式' => 'エラーが発生しました。システム管理者へご連絡ください' ) ),
                    0,
                    CoralCsvHandlerLine::TYPE_ERROR
                    ) )
            );
            $this->view->assign( 'errorCount', 1 );

            $this->view->assign( 'back', 'changeCsv' );

            return $this->view;
        }

        $templateClass = 2;
        $seq = $this->userInfo->EnterpriseId;
        $templatePattern = 0;

        $csv = $_FILES[ self::UPLOAD_FIELD_NAME ]['tmp_name'];

        if( ! preg_match( '/\.xl.$/i', $_FILES[ self::UPLOAD_FIELD_NAME ]['name'] ) && $csv != "" ) {
            $reader = new BaseIOCsvReader( $csv );
            $handler = new CoralCsvHandlerPayingAndSales( $reader, array(
                    CoralCsvHandlerPayingAndSales::OPTIONS_DBADAPTER => $this->dbAdapter,
                    CoralCsvHandlerPayingAndSales::OPTIONS_ENT_ID => $this->userInfo->EnterpriseId,
                    CoralCsvHandlerPayingAndSales::OPTIONS_DELI_MASTER => $this->deliMasters,
                    CoralCsvHandlerPayingAndSales::OPTIONS_ORDER_LIST => $this->getAllOrders(),
                    CoralCsvHandlerPayingAndSales::OPTIONS_CSV_SCHEMA => $this->csv_schema,
                    CoralCsvHandlerPayingAndSales::OPTIONS_MODE => CoralCsvHandlerPayingAndSales::MODE_CHANGE
            ) );

            // CSV解析実行
            $logicTemplate = new LogicTemplate( $this->dbAdapter );
            $logicTemplate->setForceTitleClass(0);// タイトル行区分[0：なし]を強制する
            $rows = $logicTemplate->convertFiletoArray( $csv, $templateId, $templateClass, $seq, $templatePattern );
            if (!$rows) {
                $templateId = 'CKA03011_4';
                $rows = $logicTemplate->convertFiletoArray( $csv, $templateId, $templateClass, $seq, $templatePattern );
                $this->csv_schema = $this->getCsvSchema($templateId);
                $handler->setCsvSchema($this->csv_schema);
            }
            if ($rows && !empty($rows) && !IsValidDate($rows[0]['ReceiptOrderDate'])) {
                // 1行目の[注文日]が日付型として有効と判断されない時は[ヘッダ行あり]と判断しarray_shiftを実施
                array_shift($rows);
            }

            if ($rows) {
                $rowsCount = 0;
                if (!empty($rows)) {
                    $rowsCount = count($rows);
                }

                for ($idx=0; $idx<$rowsCount; $idx++) {
                    $cvtdate = fixDateString($rows[$idx]['ReceiptOrderDate']);
                    $rows[$idx]['ReceiptOrderDate'] = (IsValidDate($cvtdate)) ? date('Y-m-d', strtotime($cvtdate)) : '';
                }
            }

            $success = $rows == false ? false : true;

            // 検証実施
            if( $success != false ) {
                $line = 0;
                $continue = true;

                foreach( $rows as $row ) {

                    if( $continue == false ) {
                        break;
                    }
                    $validate = array();
                    foreach( $row as $value ) {
                        $validate[] = $value;
                    }
                    $result = $handler->validateLine( $validate, $line++, $reader );

                    if( is_array( $result ) ) {
                        $row = $result;
                    } else {
                        // コマンド定数が返されたら処理を制御
                        // それ以外の場合は特別な処理はしない
                        switch( $result ) {
                            case BaseIOCsvReader::COMMAND_STOP_READING:
                            case BaseIOCsvReader::COMMAND_STOP_AFTER_READING:
                                // 停止命令
                                $continue = false;
                                // COMMAND_STOP_READING は現在行も廃棄する
                                if( $cmd == BaseIOCsvReader::COMMAND_STOP_READING ) $row = null;
                                break;
                            case BaseIOCsvReader::COMMAND_SKIP_LINE:
                                // スキップ命令
                                $row = null;
                                break;
                        }
                    }
                }
                $success = (!empty($handler->getResults())) ? true : false;

                // ビューのタイトルを設定
                $this->setPageTitle(sprintf('%s - %s', '一括配送伝票番号修正（CSV）', ($success ? '登録内容の確認' : 'CSV登録エラー')));

                foreach( $this->csv_schema as $schema ) {
                    if( $schema['Caption'] != null ) {
                        $headerRow[] = $schema['Caption'];
                    }
                }

                // 解析結果をビューにアサイン
                $resultsCount = 0;
                if (!empty($handler->getResults())) {
                    $resultsCount = count($handler->getResults());
                }

                $exceptionsCount = 0;
                if (!empty($handler->getExceptions())) {
                    $exceptionsCount = count($handler->getExceptions());
                }

                $warningsCount = 0;
                if (!empty($handler->getWarnings())) {
                    $warningsCount = count($handler->getWarnings());
                }

                $allResultsCount = 0;
                if (!empty($handler->getAllResults())) {
                    $allResultsCount = count($handler->getAllResults());
                }
                $this->view->assign( 'hasData', true );
                $this->view->assign( 'colSchema', $this->csv_schema );
                $this->view->assign( 'headerRow', $headerRow );
                $this->view->assign( 'validRows', $handler->getResults() );
                $this->view->assign( 'validCount', $resultsCount );
                $this->view->assign( 'errorRows', $handler->getExceptions() );
                $this->view->assign( 'warningRows', $handler->getWarnings() );
                $this->view->assign( 'errorCount', $exceptionsCount + $warningsCount );
                $this->view->assign( 'allData', $handler->getAllResults() );
                $this->view->assign( 'totalCount', $allResultsCount );
                $this->view->assign( 'caution', $caution );
                $this->view->assign( 'csvCaution', $csvCaution);
                $this->view->assign( 'duplication', $duplication);
            }
            else {
                $success = false;
                $this->setPageTitle( '一括配送伝票修正　CSV登録エラー' );
                $this->view->assign( 'hasData', false );
                $this->view->assign( 'colSchema', new \stdClass() );
                $this->view->assign( 'errorRows', array(
                    new CoralCsvHandlerLine(
                        array( 0 => array( 'ファイル形式' => $logicTemplate->getErrorMessage() ) ),
                        0,
                        CoralCsvHandlerLine::TYPE_ERROR
                    ) )
                );
                $this->view->assign( 'errorCount', 1 );
            }
        } else {
            $success = false;
            $this->setPageTitle( '一括配送伝票修正　CSV登録エラー' );
            $this->view->assign( 'hasData', false );
            $this->view->assign( 'colSchema', new \stdClass() );
            $this->view->assign( 'errorRows', array(
                new CoralCsvHandlerLine(
                    array( 0 => array( 'ファイル形式' => 'ファイル形式が適切ではありません。CSVファイルを登録してください' ) ),
                    0,
                    CoralCsvHandlerLine::TYPE_ERROR
                ) )
            );
            $this->view->assign( 'errorCount', 1 );
        }

        // エラーがなかったのでセッションオブジェクトに結果を保存
        if( $success ) {
            $rows = array();
            foreach( $handler->getResults() as $result ) {
                $rows[] = (object)$result->getData();
            }
            $session = $this->getSessionStorage();
            $session->posts = $rows;
        }

        $this->view->assign( 'back', 'changeCsv' );

        return $this->view;
    }

    /**
     * saveCsvアクション。一括伝票入力データをDBへ登録する
     *
     */
    public function saveCsvAction() {
        // エラーの際のrollback先を設定 (2015/04/02)
        $back = $this->params()->fromRoute('back');
        $param['back'] = $back;

        return $this->_forward( 'save', $param );
    }

    /**
     * completeChangeCsvアクション。一括伝票修正完了画面を表示する
     *
     */
    public function completeChangeCsvAction() {
        $this->setPageTitle( '配送伝票修正完了' );
        $this->setCommonScripts();

        return $this->view;
    }

    /**
     * 伝票入力可能な注文データのリストを連想配列で作成する
     *
     * @return array
     */
    private function getTargets() {
        $stm = $this->dbAdapter->query($this->orderQuery);
        $prm = array(
                ':EnterpriseId' => $this->userInfo->EnterpriseId,
        );
        $rs = new ResultSet();
        $rows = $rs->initialize($stm->execute($prm))->toArray();

        return $rows;
    }

    /**
     * 注文データのリストを連想配列で作成する
     *
     * @return array
     */
    private function getAllOrders() {
        $sql = <<<Q_END
SELECT
    O.OrderSeq,
    O.OrderId,
    O.Ent_OrderId,
    O.ReceiptOrderDate,
    O.EnterpriseId,
    O.SiteId,
    O.UseAmount,
    C.CustomerId,
    C.NameKj,
    C.Phone,
    D.UnitingAddress,
    I.OrderItemId,
    O.DataStatus,
    O.CloseReason,
    I.Deli_JournalNumber,
    O.Incre_Status,         # 社内与信ステータス
    S.ServiceTargetClass,   # 通常役務区分
    I.UpdateDate,           # 注文更新時間
    O.Cnl_Status
FROM
    T_Order O INNER JOIN
    T_Customer C ON C.OrderSeq = O.OrderSeq INNER JOIN
    T_OrderItems I ON I.OrderSeq = O.OrderSeq INNER JOIN
    T_DeliveryDestination D ON I.DeliDestId = D.DeliDestId INNER JOIN
    T_Site S ON S.SiteId = O.SiteId
WHERE
    O.EnterpriseId = :EnterpriseId AND
    I.OrderItemId = (
        SELECT MIN( OrderItemId )
        FROM T_OrderItems
        WHERE OrderSeq = O.OrderSeq AND DataClass = 1 AND ValidFlg = 1
    ) AND
    O.Incre_Status = 1 AND
    O.Cnl_Status = 0 AND
    O.ValidFlg = 1 AND
    C.ValidFlg = 1 AND
    I.ValidFlg = 1 AND
    D.ValidFlg = 1 AND
    S.ValidFlg = 1
ORDER BY
    O.OrderSeq DESC
Q_END;

        $stm = $this->dbAdapter->query( $sql );
        $prm = array(
                ':EnterpriseId' => $this->userInfo->EnterpriseId,
        );
        $rows = ResultInterfaceToArray( $stm->execute( $prm ) );

        // 注文商品の更新時間を取得し登録時に既に更新されていたらエラーにする (2015/04/02)
        foreach( $rows as $row ) {
            $this->orderItemsUpdateDate[$row['OrderSeq']] = $row['UpdateDate'] == null ? '1970-01-01 00:00:00' : $row['UpdateDate'];
        }

        return $rows;
    }

    /**
     * このコントローラクラス固有のセッション名前空間を取得する
     *
     * @return Container
     */
    private function getSessionStorage() {
        return new Container( Application::getInstance()->getApplicationId() . '_ShippingData' );
    }

    /**
     * ビューにJavaScriptのリンクを設定
     *
     * @return CoralControllerAction
     */
    private function setCommonScripts() {
        return $this
            ->addJavaScript( '../../js/prototype.js' )
            ->addJavaScript( '../../js/bytefx.js' )
            ->addJavaScript( '../../js/json+.js' )
            ->addJavaScript( '../../js/corelib.js' )
            ->addJavaScript( '../../js/json_format.js' )
            ->addJavaScript( '../../js/base.ui.js' )
            ->addJavaScript( '../../js/base.ui.tableex.js' )
            ->addJavaScript( '../../js/form_validator.js' );

    }

    /**
     * 伝票登録向けのCSVスキーマ定義データを取得する
     * @return array
     */
    private function getCsvSchema($templateId = 'CKA03011_1') {
        // TemplateSeq取得
        $templateClass = 2;
        $seq = $this->userInfo->EnterpriseId;
        $templatePattern = 0;

        $templateHeader = new TableTemplateHeader( $this->dbAdapter );
        $templateSeq = $templateHeader->getTemplateSeq( $templateId, $templateClass, $seq, $templatePattern );

        // 該当のテンプレートが存在しなかった場合
        if( $templateSeq == null ) {
            return null;
        }

        $sql  = " SELECT TemplateSeq ";
        $sql .= " ,      TitleClass ";
        $sql .= "        FROM M_TemplateHeader ";
        $sql .= "        WHERE TemplateSeq = :TemplateSeq ";
        $stm = $this->dbAdapter->query( $sql );
        $prm = array(
                ':TemplateSeq' => $templateSeq,
        );
        $titleClass = $stm->execute( $prm )->current()['TitleClass'];

        // PhysicalName取得
        $templateField = new TableTemplateField( $this->dbAdapter );
        $templates = ResultInterfaceToArray( $templateField->findTemplateField( array('TemplateSeq' => $templateSeq, 'ValidFlg' => 1), true ) );
        $i = 0;
        foreach( $templates as $template ) {
            $csvshemas[$i]['ColumnName'] = $template['PhysicalName'];
            $csvshemas[$i]['Ordinal'] = $template['ListNumber'];
            if( $titleClass == 1 ) {
                $csvshemas[$i]['Caption'] = $template['LogicalName'];
            }
            elseif( $titleClass == 2 ) {
                $csvshemas[$i]['Caption'] = $template['PhysicalName'];
            }
            switch( $template['PhysicalName'] ) {
                case 'OrderId':
                    $csvshemas[$i]['ValidationRegex'] = '/^.{0,100}$/';
                    break;
                case 'Deli_DeliveryMethod':
                    $csvshemas[$i]['ValidationRegex'] = '/^([1-9]|([1-9]\\d+))$/';
                    break;
                case 'Deli_JournalNumber':
                    $csvshemas[$i]['ValidationRegex'] = '/^.{1,255}$/';
                    break;
                case 'ReceiptOrderDate':
                    $csvshemas[$i]['ValidationRegex'] = '/^(\\d{2,4}([\\/\\-.]\\d{1,2}){2})/';
                    break;
                case 'NameKj':
                    $csvshemas[$i]['ValidationRegex'] = '/^.*$/m';
                    break;
                case 'Phone':
                    $csvshemas[$i]['ValidationRegex'] = '/^.*$/m';
                    break;
                case 'UseAmount':
                    $csvshemas[$i]['ValidationRegex'] = '/^.*$/m';
                    break;
                case 'UnitingAddress':
                    $csvshemas[$i]['ValidationRegex'] = '/^.*$/m';
                    break;
                case 'Ent_OrderId':
                    $csvshemas[$i]['ValidationRegex'] = '/^.{0,100}$/';
                    break;
                case 'IsSelfBilling':
                    $csvshemas[$i]['ValidationRegex'] = '/^.*$/m';
                    break;
            }
            $i++;
        }

        return $csvshemas;
    }

    /**
     * 配送方法マスタから、指定の配送方法IDのデータを取得する
     *
     * @param int|string $methodId
     * @return TableDeliMethod|null
     */
    private function findDeliveryMaster($methodId) {
        foreach( $this->deliMasters as $deliRow ) {
            if( $deliRow['DeliMethodId'] == $methodId ) return $deliRow;
        }
        return null;
    }

    /**
     * 処理対象の日付リストを生成する。
     * 日付リストは現在日～最大過去30日
     *
     * @access private
     * @return array
     */
    private function generateChangeTargetDates() {
        $results = array();

        // 現在日の時刻部をそぎ落とす
        $cur = strtotime(date('Y-m-d 0:00:00'));

        for($i = 0; $i < $this->limitDaySpan; $i++) {
            $results[] = date('Y-m-d', $cur);
            $cur -= 86400;	// 1日遡る
        }

        return $results;
    }

    /**
     * 処理対象期間のジョブ転送件数集計リストを取得する
     *
     * @access private
     * @return array
     */
    private function getChangeTargetsCount() {
        $results = array();

        // 対象期間すべての日付情報を初期化
        foreach($this->target_dates as $date) {
            $results[$date] = array(
                    'sb' => 0,
                    'all' => 0
            );
        }

        // SQL実行
        $sql = <<<Q_END
SELECT
    h.ClaimDate,
    o.ClaimSendingClass,
    COUNT(*) AS Cnt
FROM
    T_Order o STRAIGHT_JOIN
    T_OrderSummary s ON s.OrderSeq = o.OrderSeq STRAIGHT_JOIN
    T_OrderItems i ON i.OrderItemId = s.OrderItemId STRAIGHT_JOIN
    T_ClaimControl c ON c.OrderSeq = o.OrderSeq STRAIGHT_JOIN
    T_ClaimHistory h ON h.OrderSeq = o.OrderSeq
WHERE
    o.EnterpriseId = :EnterpriseId AND
    o.DataStatus IN (41, 51) AND
    o.Cnl_Status = 0 AND
    (c.ClaimPattern IS NULL OR c.ClaimPattern = 1) AND
    i.Deli_ConfirmArrivalFlg <> 1 AND
    h.ClaimPattern = 1 AND
    h.ClaimDate > DATE_ADD(CURRENT_DATE, INTERVAL -{$this->limitDaySpan} DAY) AND
    h.ClaimDate <= CURRENT_DATE
GROUP BY
    h.ClaimDate,
    o.ClaimSendingClass
ORDER BY
    h.ClaimDate DESC,
    o.ClaimSendingClass
Q_END;

        $stm = $this->dbAdapter->query( $sql );
        $prm = array(
                ':EnterpriseId' => $this->userInfo->EnterpriseId,
        );
        $counts = ResultInterfaceToArray( $stm->execute( $prm ) );

        // DBから取得した情報を日付別/同梱・全件別に再集計
        foreach($counts as $i => $row) {
            $results[$row['ClaimDate']]['all'] += (int)$row['Cnt'];
            if(strlen(nvl($row['ClaimSendingClass'])) > 0 && $row['ClaimSendingClass'] == 11) {
                $results[$row['ClaimDate']]['sb'] += (int)$row['Cnt'];
            }
        }

        return $results;
    }

    /**
     * 詳細表示/CSVダウンロード要求時の指定日付を確定させる。
     *
     * @access private
     * @param string $date yyyy-MM-dd形式の日付文字列
     * @return string|FALSE $dateが処理対象日付内の場合は$dateの値、書式不備や範囲外の場合はFALSE
     */
    private function fixRequestDate($date) {
        if(!preg_match('/^\d{4}-\d{2}-\d{2}$/u', $date)) $date = '1900-01-01';
        if($date < min($this->target_dates) || $date > max($this->target_dates)) {
            return false;
        }
        return $date;
    }

    /**
     * 指定日の対象データ詳細を表示する
     */
    public function detailAction() {
        $this->setPageTitle( '配送伝票番号更新 - 指定日注文詳細' );
        $this->setCommonScripts();
        $params = $this->getParams();
        $pDate = isset($params['date']) ? $params['date'] : '1900-01-01';
        $date = $this->fixRequestDate($pDate);
        $include_all = isset($params['include_all']) ? $params['include_all'] : 0;
        if($date === false) {
            // 日付指定不正時
            $this->setTemplate('detailerr');

            return $this->view;
        } else {
            $this->view->assign('target_date', $date);
            $this->view->assign('include_all', $include_all ? true : false);
            $this->view->assign('list', $this->getTargetsByDate($date));

            return $this->view;
        }
    }

    /**
     * 指定日のジョブ転送データを取得する
     *
     * @access private
     * @param string $date 指定日付
     * @return array
     */
    private function getTargetsByDate($date) {
        $sql = <<<Q_END
SELECT
    o.OrderSeq,
    o.EnterpriseId,
    o.OrderId,
    s.Deli_DeliveryMethodName AS Deli_DeliveryMethod,
    i.Deli_JournalNumber,
    o.ReceiptOrderDate,
    s.NameKj,
    s.Phone,
    o.UseAmount,
    s.DestUnitingAddress AS UnitingAddress,
    o.Ent_OrderId,
    CASE
        WHEN o.ClaimSendingClass = 11 THEN 1
        ELSE 0
    END AS IsSelfBilling
FROM
    T_Order o STRAIGHT_JOIN
    T_OrderSummary s ON s.OrderSeq = o.OrderSeq STRAIGHT_JOIN
    T_OrderItems i ON i.OrderItemId = s.OrderItemId STRAIGHT_JOIN
    T_ClaimControl c ON c.OrderSeq = o.OrderSeq STRAIGHT_JOIN
    T_ClaimHistory h ON h.OrderSeq = o.OrderSeq
WHERE
    o.DataStatus IN (41, 51) AND
    o.Cnl_Status = 0 AND
    (c.ClaimPattern IS NULL OR c.ClaimPattern = 1) AND
    i.Deli_ConfirmArrivalFlg <> 1 AND
    h.ClaimPattern = 1 AND
    o.EnterpriseId = :EnterpriseId AND
    h.ClaimDate = :TargetDate
ORDER BY
    o.ReceiptOrderDate,
    o.OrderSeq
Q_END;

        $stm = $this->dbAdapter->query( $sql );
        $prm = array(
                ':EnterpriseId' => $this->userInfo->EnterpriseId,
                ':TargetDate' => $date
        );
        $datas = ResultInterfaceToArray( $stm->execute( $prm ) );

        return $datas;
    }

    /**
     * 指定日の対象データをテンプレートCSVとしてダウンロードする
     */
    public function downloadDateCsvAction() {
        // POST情報の取得
        $params = $this->getParams();
        $input = $params['form'];
        // 日付指定
        $date = $this->fixRequestDate($input['date']);
        // 全件かどうか
        $include_all = $input['include_all'] ? true : false;

        // 日付指定が不正だった場合は0件クエリにすり替える
        if($date === false) $date = '1900-01-01';

        // データ取得
        $csv_rows = array();
        foreach($this->getTargetsByDate($date) as $row) {
            // 全件でない場合、自社分以外は除く
            if(!$include_all && !$row['IsSelfBilling']) continue;
            $csv_row = array();
            foreach($row as $col => $label) {
                // 自社分の判定を変換
                $csv_row[$col] = $col == 'IsSelfBilling' ? ($row[$col] ? '○' : '') : $row[$col];
            }
            $csv_rows[] = $csv_row;
        }

        $templateId = 'CKA03011_3';    // 一括配送伝票入力雛形CSV兼一覧
        $templateClass = 2;
        $seq = $this->userInfo->EnterpriseId;
        $templatePattern = 0;

        $logicTemplate = new LogicTemplate( $this->dbAdapter );
        $response = $logicTemplate->convertArraytoResponse( $csv_rows, sprintf( 'Shipping_%s.csv', date('YmdHis') ), $templateId, $templateClass, $seq, $templatePattern, $this->getResponse() );

        if( $response == false ) {
            throw new \Exception( $logicTemplate->getErrorMessage() );
        }

        return $response;
    }
}
