<?php
namespace oemadmin\Controller;

use Coral\Base\BaseGeneralUtils;
use Coral\Base\BaseUtility;
use Coral\Base\IO\BaseIOCsvWriter;
use Coral\Coral\Controller\CoralControllerAction;
use Coral\Coral\CoralPager;
use oemadmin\Application;
use Coral\Base\BaseHtmlUtils;
use Coral\Coral\CoralCodeMaster;
use Zend\Db\ResultSet\ResultSet;
use models\Table\TableOperator;
use models\Logic\LogicNormalizer;
use models\View\ViewDelivery;
use models\Table\TableClaimHistory;
use models\Table\TableCancel;
use models\Table\TableOrderItems;
use models\Table\TableOrder;
use models\Logic\LogicTemplate;
use Zend\Json\Json;
use Coral\Base\Reflection\BaseReflectionUtility;
use Coral\Coral\CoralOrderUtility;


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
     * 定型検索定数：未キャンセル検索
     * 指定月に登録されキャンセルされていない注文を検索
     *
     * @var string
     */
    const SSEARCH_TYPE_NOT_CANCELED = 'not_canceled';

    /**
     * 定型検索定数：25～5日入金検索
     * 指定月の前月25日～指定月の翌月5日に入金確認されているキャンセルされていない注文を検索
     *
     * @var string
     */
    const SSEARCH_TYPE_PAYMENT_25 = 'payment_25';

    /**
     * 定型検索定数：入金状況検索
     * 指定月に入金確認されているキャンセルされていない注文を検索
     *
     * @var string
     */
    const SSEARCH_TYPE_PAYMENT_STATUS = 'payment_status';

    /**
     * 定型検索定数：初回請求発行検索
     * 指定月に初回請求発行されキャンセルされていない注文を検索
     *
     * @var string
     */
    const SSEARCH_TYPE_FIRST_CLAIMED = 'first_claimed';

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
     * １ページ最大表示件数
     *
     * @var int
     */
    const PAGE_LINE_MAX = 1000;

    /**
     * Controllerを初期化する
     */
    public function _init()
    {
        $this->app = Application::getInstance();
        $this->view->assign('userInfo', $this->app->authManagerAdmin->getUserInfo());

        $this->addStyleSheet($this->app->getOemCss())
            ->addStyleSheet( '../../oemadmin/css/searcho.css' )
            ->addStyleSheet( '../../css/base.ui.customlist.css')
            ->addJavaScript( '../../js/prototype.js' )
            ->addJavaScript( '../../js/corelib.js')
            ->addJavaScript( '../../js/bytefx.js')
            ->addJavaScript( '../../js/json+.js' )
            ->addJavaScript( '../../js/base.ui.js')
            ->addJavaScript( '../../js/base.ui.customlist.js')
            ->addJavaScript( '../../js/base.ui.tableex.js' )
            ->addJavaScript( '../../js/base.ui.datepicker.js');

        $this->setPageTitle($this->app->getOemServiceName()." - 注文検索");


        // 締めパターン
        $obj = new CoralCodeMaster($this->app->dbAdapter);
        $this->view->assign('fixPatternTag', BaseHtmlUtils::SelectTag("fixPattern", $obj->getMasterCodes(2, array(0 => '－')), 0));
        // 料金プラン
        $this->view->assign('planTag', BaseHtmlUtils::SelectTag("Plan", $obj->getPlanMaster(), 0));
    }

    /**
     * 検索フォームの表示
     */
    public function formAction()
    {
        $params = $this->getParams();

        // 検索区分が0:通常以外の場合、検索後の表示画面を表示する
        if ($params['searchkbn'] != '0') {
            $this->_redirect('searcho/search/searchkbn/'. $params['searchkbn']);
        }

        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
        // キャンセル理由のSELECTタグ
        $this->view->assign('cancelreasonTag',
        BaseHtmlUtils::SelectTag("cancelreason",
        $codeMaster->getCancelReasonMaster()
        )
        );
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
     * 検索実行
     */
    public function searchAction()
    {
        $params = $this->getParams();
        // $params調整関数呼び出し
        $params = $this->adjustmentParams($params);

        $userInfo = $this->app->authManagerAdmin->getUserInfo();

        // [paging] 1ページあたりの項目数
        // ※：config.iniからの取得を追加
        $cn = $this->getControllerName();
        $ipp = isset( $this->app->paging_conf ) ? $this->app->paging_conf["$cn"] : self::PAGE_LINE_MAX;
        if( ! BaseReflectionUtility::isPositiveInteger($ipp) ) $ipp = self::PAGE_LINE_MAX;

        // [paging] 指定ページを取得
        $current_page = (int)($this->params()->fromRoute('page', 1));
        if( $current_page < 1 ) $current_page = 1;

        try {
            $params['OemId'] = $userInfo->OemId;
            $search_result = $this->getSearchResult($params);
        } catch(SearchoControllerException $err) {
            // 検索条件エラーが発生したのでメッセージをセットして
            // 条件入力画面を表示
            $this->view->assign('SearchExpressionError', $err->getMessage());
            $this->setTemplate('form');
            return $this->view;
        }
        $datas = array();
        foreach($search_result as $row) {
            $datas[] = $row;
        }

        $datasCount = 0;
        if(!empty($datas)){
            $datasCount = count($datas);
        }

        $val_item_count = $datasCount;                                                        // (該当件数)
        $val_total_amount = array_sum($this->collect_field('UseAmount', $datas));               // (利用額合計)

        // 類似住所検索結果の色分け用CSSのアサイン
        $this->addStyleSheet( '../../css/cbadmin/orderstatus/' .
            ( $this->app->tools['orderstatus']['style'] ? $this->app->tools['orderstatus']['style'] : 'default' ) .
            '.css' );

        // [paging] ページャ初期化
        $pager = new CoralPager($datasCount, $ipp);
        // [paging] 指定ページを補正
        if( $current_page > $pager->getTotalPage() ) $current_page = $pager->getTotalPage();
        // [paging] 対象リストをページング情報に基づいて対象リストをスライス
        if( !empty($datas) ) $datas = array_slice( $datas, $pager->getStartIndex( $current_page ), $ipp );
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

        // [paging] 1ページあたりの項目数
        // ※：config.iniからの取得を追加
        $cn = $this->getControllerName();
        $ipp = isset( $this->app->paging_conf ) ? $this->app->paging_conf["$cn"] : self::PAGE_LINE_MAX;
        if( ! BaseReflectionUtility::isPositiveInteger($ipp) ) $ipp = self::PAGE_LINE_MAX;

        // [paging] 指定ページを取得
        $current_page = (int)($this->params()->fromRoute('page', 1));
        if( $current_page < 1 ) $current_page = 1;

        $datas = $this->getQuickSearchResult($params);
        if($datas === false)
        {
            // 条件未指定
            $this->view->assign('noParamsError', 1);
            $this->setTemplate('qform');
            return $this->view;
        }

        $datasCount = 0;
        if(!empty($datas)){
            $datasCount = count($datas);
        }

        $val_item_count = $datasCount;                                                        // (該当件数)
        $val_total_amount = array_sum($this->collect_field('UseAmount', $datas));               // (利用額合計)

        // 類似住所検索結果の色分け用CSSのアサイン
        $this->addStyleSheet( '../../oemadmin/css/cbadmin/orderstatus/' .
            ( $this->app->tools['orderstatus']['style'] ? $this->app->tools['orderstatus']['style'] : 'default' ) .
            '.css' );

        // [paging] ページャ初期化
        $pager = new CoralPager($datasCount, $ipp);
        // [paging] 指定ページを補正
        if( $current_page > $pager->getTotalPage() ) $current_page = $pager->getTotalPage();
        // [paging] 対象リストをページング情報に基づいて対象リストをスライス
        if( !empty($datas) ) $datas = array_slice( $datas, $pager->getStartIndex( $current_page ), $ipp );

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
     * ダウンロード実行
     */
    public function dcsvAction()
    {
$start_time = microtime(true);
        $params = $this->getParams();
        if (isset($params['srchparams'])) {
            $srchparams = unserialize($params['srchparams']);
            unset($params['srchparams']);
            $params = array_merge($params, $srchparams);
        }
        try {
            $datas = $this->getSearchResult($params);
        } catch(SearchoControllerException $err) {
            // 検索条件エラーが発生したので0行として扱う
            $datas = array();
        }
        $fileName = sprintf("result_%s.csv", date("YmdHis"));

        return $this->_execCsvDownload($datas, $fileName);
// $this->app->logger->debug(sprintf('[SeaechoController::dcsvAction] total time = %s', microtime(true) - $start_time));
    }

    /**
     * 簡易検索CSVダウンロード実行
     */
    public function qdcsvAction()
    {
$start_time = microtime(true);
        $params = $this->getParams();
        if (isset($params['srchparams'])) {
            $srchparams = unserialize($params['srchparams']);
            unset($params['srchparams']);
            $params = array_merge($params, $srchparams);
        }
        $datas = $this->getQuickSearchResult($params);
        if($datas === false) $datas = array();
        $fileName = sprintf("q_result_%s.csv", date("YmdHis"));

        return $this->_execCsvDownload($datas, $fileName);
// $this->app->logger->debug(sprintf('[SeaechoController::qdcsvAction] total time = %s', microtime(true) - $start_time));
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

        ini_set('max_execution_time', 0);        // 実行タイムアウトを無効にする

//         $this->app->addClass('NetB_IO_CsvWriter');

//         $this->_helper->viewRenderer->setNoRender(true);        // ビューレンダラーを無効にする。

//         $logger = $this->app->logger;
//         $log_tmpl = '[SearchoController::_execCsvDownload] %s';
//         $start_time = microtime(true);
//         // タイトル行定義
//         $header = array(
//             "注文ID",
//             "任意注文番号",
//             "OEM任意番号",
//             "注文日",
//             "店舗名",
//             "会社名",
//             "備考",
//             "注文者名",
//             "注文者カナ",
//             "注文者TEL",
//             "注文者メアド",
//             "注文者郵便番号",
//             "注文者住所",
//             "督促メール日",
//             "配送先氏名",
//             "配送先カナ",
//             "配送先TEL",
//             "配送先郵便番号",
//             "配送先住所",
//             "伝票登録日",
//             "運送会社",
//             "伝票番号",
//             "着荷確認日",
//             "立替予定日",
//             "初回請求日",
//             "初回支払期限",
//             //"E電話結果",
//             //"ドッグベル",
//             //"メール有効",
//             //"与信担当者",
//             "営業担当者",
//             "1回目再請求日",
//             "2回目再請求日",
//             "3回目再請求日",
//             "4回目再請求日",
//             "5回目再請求日",
//             "6回目再請求日",
//             "1回目再請求額",
//             "2回目再請求額",
//             "3回目再請求額",
//             "4回目再請求額",
//             "5回目再請求額",
//             "6回目再請求額",
//             "入金日",
//             "入金額",
//             "入金形態",
//             //"住所与信クラス",
//             //"TEL与信クラス",
//             "キャンセル日",
//             "キャンセル状態",
//             "送料",
//             "決済手数料",
//             "利用額",

//             "商品１名前",
//             "商品１単価",
//             "商品１数量",
//             "商品２名前",
//             "商品２単価",
//             "商品２数量",
//             "商品３名前",
//             "商品３単価",
//             "商品３数量",
//             "商品４名前",
//             "商品４単価",
//             "商品４数量",
//             "商品５名前",
//             "商品５単価",
//             "商品５数量",
//             "商品６名前",
//             "商品６単価",
//             "商品６数量",
//             "商品７名前",
//             "商品７単価",
//             "商品７数量",
//             "商品８名前",
//             "商品８単価",
//             "商品８数量",
//             "商品９名前",
//             "商品９単価",
//             "商品９数量",
//             "商品１０名前",
//             "商品１０単価",
//             "商品１０数量",
//             "商品１１名前",
//             "商品１１単価",
//             "商品１１数量",
//             "商品１２名前",
//             "商品１２単価",
//             "商品１２数量",
//             "商品１３名前",
//             "商品１３単価",
//             "商品１３数量",
//             "商品１４名前",
//             "商品１４単価",
//             "商品１４数量",
//             "商品１５名前",
//             "商品１５単価",
//             "商品１５数量",
//             "商品１６名前",
//             "商品１６単価",
//             "商品１６数量",
//             "商品１７名前",
//             "商品１７単価",
//             "商品１７数量",
//             "商品１８名前",
//             "商品１８単価",
//             "商品１８数量",
//             "商品１９名前",
//             "商品１９単価",
//             "商品１９数量",
//             "商品２０名前",
//             "商品２０単価",
//             "商品２０数量",
//             "商品２１名前",
//             "商品２１単価",
//             "商品２１数量",
//             "商品２２名前",
//             "商品２２単価",
//             "商品２２数量",
//             "商品２３名前",
//             "商品２３単価",
//             "商品２３数量",
//             "商品２４名前",
//             "商品２４単価",
//             "商品２４数量",
//             "商品２５名前",
//             "商品２５単価",
//             "商品２５数量",
//             "商品２６名前",
//             "商品２６単価",
//             "商品２６数量",
//             "商品２７名前",
//             "商品２７単価",
//             "商品２７数量",
//             "商品２８名前",
//             "商品２８単価",
//             "商品２８数量",
//             "商品２９名前",
//             "商品２９単価",
//             "商品２９数量",
//             "商品３０名前",
//             "商品３０単価",
//             "商品３０数量",

//     //      ↓ --- 20150508_001対応
//             "事業者ID",
//             "適用決済手数料率",
//             "事業者決済手数料",
//             "請求手数料",
//             "OEM適用決済手数料率",
//             "OEM決済手数料",
//             "OEM請求手数料",
//             "立替金額",
//             "着荷確認",
//     //      ↑ --- 20150508_001対応
//             "締日パターン"
//     //      ↑ --- 20150611_001対応

//             );

//         // CsvWriter初期化
//         $writer = new NetB_IO_CsvWriter(array(NetB_IO_CsvWriter::PARAMS_COLUMN_HEADER => $header));

        $mdldeli = new ViewDelivery($this->app->dbAdapter);
        $mdlch = new TableClaimHistory($this->app->dbAdapter);
        $mdlcl = new TableCancel($this->app->dbAdapter);
        $mdloi = new TableOrderItems($this->app->dbAdapter);
        $mdlo = new TableOrder($this->app->dbAdapter);

        // ステータス名称取得用
        $captionMap = CoralOrderUtility::getStatusCaptions();

//         // ダウンロード開始
//         $writer->beginWrite($fileName);
// $logger->debug(sprintf($log_tmpl, sprintf('begin download, count = %d', count($datas))));

        $datasCount = 0;
        if(!empty($datas)){
            $datasCount = count($datas);
        }

        // 個々の注文の情報追加
        for ($i = 0 ; $i < $datasCount ; $i++)
        {
            $item_start = microtime(true);

            // 立替予定日
            $execScheduleDate = $this->getExecScheduleDate($datas[$i]['OrderSeq']);
            // 配送情報
            // 配送情報の取得方法を生SQL実行に変更（2013.3.15 eda）
$query = <<<EOQ
SELECT
    s.DestNameKj,
    s.DestNameKn,
    s.DestPhone AS DestPhone,
    s.DestPostalCode AS DestPostalCode,
    s.DestUnitingAddress AS DestUnitingAddress,
    s.Deli_JournalIncDate,
    m.DeliMethodName AS DeliMethodName,
    s.Deli_JournalNumber,
    i.Deli_ConfirmArrivalDate
FROM
    T_OrderSummary s STRAIGHT_JOIN
    T_OrderItems i ON i.OrderItemId = s.OrderItemId LEFT OUTER JOIN
    M_DeliveryMethod m ON m.DeliMethodId = i.Deli_DeliveryMethod
WHERE
    s.OrderSeq = :OrderSeq
EOQ;
            if($datas[$i]['HasDeliInfo'])
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
                    'DeliMethodName' => $datas[$i]['DeliMethodName'],
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
                'SiteNameKj' => $datas[$i]['SiteNameKj'],
                'EnterpriseNameKj' => $datas[$i]['EnterpriseNameKj'],
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
                'DeliMethodName' => $deli['DeliMethodName'],
                'Deli_JournalNumber' => $deli['Deli_JournalNumber'],
                'Deli_ConfirmArrivalDate' => $deli['Deli_ConfirmArrivalDate'],

                'ExecScheduleDate' => $execScheduleDate,

                'F_ClaimDate' => $datas[$i]['F_ClaimDate'],
                'F_LimitDate' => $datas[$i]['F_LimitDate'],
                //$datas[$i]['eDen'],
                //$datas[$i]['PhoneHistory'],
                //$datas[$i]['RealSendMailResult'],
                //$datas[$i]['Incre_DecisionOpId'],
                'Salesman' => $datas[$i]['Salesman']
            );

            // 再請求日・再請求額を追加する。
            $reclaimHistory = $mdlch->getReClaimHistory($datas[$i]['OrderSeq']);
            $rs = new ResultSet();
            $rs->initialize( $reclaimHistory );
            $reclaimHistory = $rs->toArray();

            $itemMax = 6;

            $rhCount = 0;
            if(!empty($reclaimHistory)){
                $rhCount = count($reclaimHistory);
            }

            $itemCount = $rhCount;
            // 再請求日の部分
//             for ($j = 0 ; ($j < $itemCount) && ($j < $itemMax) ; $j++)
//             {
//                 $record[] = $reclaimHistory[$j]['ClaimDate'];
//             }
//             for ($j = 0 ; $j < ($itemMax - $itemCount) ; $j++)
//             {
//                 $record[] = '';
//             }
            for( $j = 0; $j < $itemMax; $j++ ) {
                $record[$i]['ClaimDate_' . ( $j + 1 ) ] = $reclaimHistory[$j]['ClaimDate'];
                if( empty( $reclaimHistory[$j]['ClaimDate'] ) ) {
                    $record[$i]['ClaimDate_' . ( $j + 1 ) ] = '';
                }
            }

            // 再請求額の部分
            $beforeAmount = 0;
//             for ($j = 0 ; ($j < $itemCount) && ($j < $itemMax) ; $j++)
//             {
//                 $nowAmount = (int)$reclaimHistory[$j]['ClaimFee'];
//                 $record[] = sprintf('%d', $nowAmount - $beforeAmount);
//                 $beforeAmount = $nowAmount;
//             }
//             for ($j = 0 ; $j < ($itemMax - $itemCount) ; $j++)
//             {
//                 $record[] = '';
//             }
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
//             $record = array_merge($record, array(
//                 $datas[$i]['Rct_ReceiptDate'],
//                 $datas[$i]['Rct_ReceiptAmount'],
//                 $datas[$i]['Rct_ReceiptMethodLabel'],
//                 //$datas[$i]['Incre_ArAddr'],
//                 //$datas[$i]['Incre_ArTel']
//             ));
            $record[$i]['ReceiptDate'] = $datas[$i]['ReceiptDate'];
            $record[$i]['ReceiptAmount'] = $datas[$i]['ReceiptAmount'];
            $record[$i]['ReceiptClassName'] = $datas[$i]['ReceiptClassName'];

            // キャンセル日を追加する。
            $cancel = $mdlcl->findCancel(array('OrderSeq' => $datas[$i]['OrderSeq']))->current();
//             if ($cancel != null)
//             {
//                 $record[] = $cancel->CancelDate;
//             }
//             else
//             {
//                 $record[] = '';
//             }
            $record[$i]['CancelDate'] = ( $cancel ) ? $cancel['CancelDate'] : '';

            //キャンセル状態を設定する
            //注文情報
            // 元々のデータに含まれている項目なのでクエリの発行自体を廃止（2013.3.15 eda）
            // クエリ発行中止に伴い、値の参照方法を修正（2013.3.15 eda）
            switch( $datas[$i]['Cnl_Status'] ) {
                case 0:
//                     $record[] = '';
                    $record[$i]['Cnl_Status'] = '';
                    break;
                case 1:
                    if($datas[$i]['Cnl_ReturnSaikenCancelFlg'] == '1') {
//                         $record[] = '返却依頼中';
                        $record[$i]['Cnl_Status'] = '返却依頼中';
                    } else {
//                         $record[] = 'キャンセル依頼中';
                        $record[$i]['Cnl_Status'] = 'キャンセル依頼中';
                    }
                    break;
                case 2:
                    if($datas[$i]['Cnl_ReturnSaikenCancelFlg'] == '1') {
//                         $record[] = '返却済み';
                        $record[$i]['Cnl_Status'] = '返却済み';
                    } else {
//                         $record[] = 'キャンセル済み';
                        $record[$i]['Cnl_Status'] = 'キャンセル済み';
                    }
                    break;
            }

            // 商品情報を追加する。
            $deliveryFee = 0;
            $settlementFee = 0;
            // 取得方法を生SQLに変更（2013.3.15 eda）
//             $itemsNeta = $this->app->dbAdapter->fetchAll('select * from T_OrderItems where OrderSeq = ? order by OrderItemId', $datas[$i]['OrderSeq']);
            $sql = " select * from T_OrderItems where OrderSeq = :OrderSeq and ValidFlg = 1 order by DataClass, OrderItemId ";
            $itemsNeta = $this->app->dbAdapter->query( $sql )->execute( array( ':OrderSeq' => $datas[$i]['OrderSeq'] ) );

            unset($items);
            $items = array();
            // パフォーマンス改善目的で出力行配列への追加方法などを修正（2013.3.15 eda）
//             foreach ($itemsNeta as $item)
//             {
//                 //switch((int)$item->DataClass)
//                 switch((int)$item['DataClass'])
//                 {
//                     case 2:    // 送料
//                         $deliveryFee = $item['SumMoney'];
//                         break;
//                     case 3:    // 手数料
//                         $settlementFee = $item['SumMoney'];
//                         break;
//                     default:
//                         $items = array_merge($items, array($item['ItemNameKj'], $item['UnitPrice'], $item['ItemNum']));
//                         break;
//                 }
//             }
//             $items = array_slice($items, 0, 90);    // 30商品分にトリミング
//             if(count($items) < 90) {
//                 // 30商品に満たない場合は残りをパディング
//                 $items = array_pad($items, 90, '');
//             }

//             $record[] = $deliveryFee;                        // 送料
//             $record[] = $settlementFee;                        // 手数料
//             $record[] = $datas[$i]['UseAmount'];            // 利用額

            $j = 0;
            $deliveryFee = 0;
            $settlementFee = 0;
            $unitpricetax = 0;
            foreach( $itemsNeta as $item ) {
                if( $j == 30 ) { break; }
                switch( (int)$item['DataClass'] ) {
                    case 2: // 送料
                        $deliveryFee += $item['SumMoney'];
                        break;
                    case 3: // 手数料
                        $settlementFee += $item['SumMoney'];
                        break;
                    case 4: // 外税額
                        $unitpricetax += $item['SumMoney'];
                        break;
                    default:
                        $record[$i]['ItemNameKj_' . ( $j + 1)] = $item['ItemNameKj'];
                        $record[$i]['UnitPrice_' . ( $j + 1)] = $item['UnitPrice'];
                        $record[$i]['ItemNum_' . ( $j + 1)] = $item['ItemNum'];
                        break;
                }
                $j++;
            }

            $record[$i]['DeliveryFee'] = $deliveryFee;          // 送料
            $record[$i]['SettlementFee'] = $settlementFee;      // 手数料
            $record[$i]['UnitPriceTax'] = $unitpricetax;        // 外税額
            $record[$i]['UseAmount'] = $datas[$i]['UseAmount']; // 利用額

//             // 商品情報を出力行へマージ
//             $record = array_merge($record, $items);

            // 20150508_001対応
//             $record = array_merge($record, array(
//                 $datas[$i]['EnterpriseId'],                                 // 事業者ID
//                 $this->toRealRate($datas[$i]['AppSettlementFeeRate']),      // 適用決済手数料率
//                 $datas[$i]['SettlementFee'],                                // 決済手数料
//                 $datas[$i]['ClaimFee'],                                     // 請求手数料
//                 $this->toRealRate($datas[$i]['Oem_AppSettlementFeeRate']),  // OEM適用決済手数料率
//                 $datas[$i]['Oem_SettlementFee'],                            // OEM決済手数料
//                 $datas[$i]['Oem_ClaimFee'],                                 // OEM請求手数料
//                 $datas[$i]['ChargeAmount'],                                 // 立替金額
//                 $this->toConfirmArrivalLabel($datas[$i]['Deli_ConfirmArrivalFlg'])  // 着荷確認
//             ));

            $record[$i]['EnterpriseId']             = $datas[$i]['EnterpriseId'];                                          // 事業者ID
            $record[$i]['AppSettlementFeeRate']     = $datas[$i]['AppSettlementFeeRate'];                                  // 適用決済手数料率
            $record[$i]['AppSettlementFee']         = $datas[$i]['SettlementFee'];                                         // 決済手数料
            $record[$i]['ClaimFee']                 = $datas[$i]['ClaimFee'];                                              // 請求手数料
            $record[$i]['Oem_AppSettlementFeeRate'] = $datas[$i]['Oem_AppSettlementFeeRate'];                              // OEM適用決済手数料率
            $record[$i]['Oem_SettlementFee']        = $datas[$i]['Oem_SettlementFee'];                                     // OEM決済手数料
            $record[$i]['Oem_ClaimFee']             = $datas[$i]['Oem_ClaimFee'];                                          // OEM請求手数料
            $record[$i]['ChargeAmount']             = $datas[$i]['ChargeAmount'];                                          // 立替金額
            $record[$i]['Deli_ConfirmArrivalFlg']   = $this->toConfirmArrivalLabel($datas[$i]['Deli_ConfirmArrivalFlg']);  // 着荷確認

            // 20150611_001対応
//             $record = array_merge($record, array(
//                     $datas[$i]['FixPatternName']                                // 締め日パターン
//             ));
            $record[$i]['FixPatternName']           = $datas[$i]['FixPatternName'];                                          // 締め日パターン

            // ステータス
            $rowClass = CoralOrderUtility::getOrderRowClass($datas[$i]);
            $record[$i]['DataStatus'] = $captionMap[$rowClass];

            // データ行を直接出力
//             $writer->writeRow($record);
// if($i % 1000 == 999) $logger->debug(sprintf($log_tmpl, sprintf('item %d completed. time = %s', ($i + 1), microtime(true) - $item_start)));
        }

// $logger->debug(sprintf($log_tmpl, sprintf('end dwonload, total time = %s', microtime(true) - $start_time)));
        $templateId = 'COEM005';    // 注文情報CSV
        $templateClass = 0;
        $seq = 0;
        $templatePattern = 0;

        $logicTemplate = new LogicTemplate($this->app->dbAdapter);
        $response = $logicTemplate->convertArraytoResponse( $record, $fileName, $templateId, $templateClass, $seq, $templatePattern, $this->getResponse() );

        if( $response == false ) {
            throw new \Exception( $logicTemplate->getErrorMessage() );
        }

        return $response;
    }

    /**
     * DB上の手数料率値を実数に変換する（CSVダウンロード向け）
     *
     * @access protected
     * @param mixed $v 手数料率値
     * @return $vを実数に変換した文字列。小数点以下5桁固定表記で、$vがnullの場合はブランクを返す
     */
    protected function toRealRate($v) {
        return is_numeric($v) ? sprintf('%0.05f', BaseGeneralUtils::ToRealRate($v)) : '';
    }

    /**
     * 着荷確認状況の値を表示用ラベルに変換する（CSVダウンロード向け）
     *
     * @access protected
     * @param int $v 着荷確認状況（Deli_ConfirmArrivalFlg）
     * @return string 表示ラベル
     */
    protected function toConfirmArrivalLabel($v) {
        $v = (int)$v;
        if(!in_array($v, array(-1, 0, 1))) $v = 0;
        $map = array(
            '-1' => '未確認',
            '0' => '',
            '1' => '確認済'
        );
        return $map[$v];
    }

    /**
     * 立替予定日を取得する。
     *
     * @param $oseq 注文Seq
     * @return string 立替予定日
     */
    private function getExecScheduleDate($oseq)
    {
        $query = sprintf("
            SELECT
                PC.ExecScheduleDate
            FROM
                T_PayingControl PC,
                T_PayingAndSales PAS
            WHERE
                PC.Seq = PAS.PayingControlSeq AND
                PAS.OrderSeq = %d
            ",
            $oseq
        );

        // クエリー実行
        $ri = $this->app->dbAdapter->query($query)->execute(null);
        $rs = new ResultSet();
        $rs->initialize($ri);
        $datas = $this->_fillSearchResult($rs->toArray());

        $result = "";
        if (!empty($datas))
        {
            $result = $datas[0]["ExecScheduleDate"];
        }

        return $result;
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
                ORD.UseAmount,
                RC.ReceiptDate,
                CC.ReceiptAmountTotal AS ReceiptAmount,
                RC.ReceiptClass,
                CASE ORD.Rct_Status
                   WHEN '0' THEN ''
                   WHEN '1' THEN (CASE RC.ReceiptClass WHEN 1 THEN 'コンビニ' WHEN 2 THEN '郵便局' WHEN 3 THEN '銀行' WHEN 4 THEN 'LINE Pay' ELSE '' END)
                   ELSE ''
                END AS ReceiptClassName,
                ORD.Incre_Note,
                ORD.Oem_Note,
                ORD.MailLimitPassageDate,
                CUS.eDen,
                CUS.PhoneHistory,
                ORD.Incre_DecisionOpId,
                RC.ReceiptDate,
                RC.ReceiptAmount,
                RC.ReceiptClass,
                CUS.Incre_ArAddr,
                CUS.Incre_ArTel,
                CUS.RealSendMailResult,
                ORD.DataStatus,
                ORD.CloseReason,
                ORD.Rct_Status,
                RC.ReceiptDate,
                ENT.Salesman,
                ORD.Cnl_Status,
                ORD.Cnl_ReturnSaikenCancelFlg,
                ENT.EnterpriseId,
                CASE
                    WHEN ORD.DataStatus >= 51 THEN PAS.AppSettlementFeeRate
                    ELSE NULL
                END AS AppSettlementFeeRate,
                CASE
                    WHEN ORD.DataStatus >= 51 THEN PAS.SettlementFee
                    ELSE NULL
                END AS SettlementFee,
                CASE
                    WHEN ORD.DataStatus >= 51 THEN PAS.ClaimFee
                    ELSE NULL
                END AS ClaimFee,
                CASE
                    WHEN ORD.DataStatus >= 51 THEN OSF.AppSettlementFeeRate
                    ELSE NULL
                END AS Oem_AppSettlementFeeRate,
                CASE
                    WHEN ORD.DataStatus >= 51 THEN OSF.SettlementFee
                    ELSE NULL
                END AS Oem_SettlementFee,
                CASE
                    WHEN ORD.DataStatus >= 51 THEN OCF.ClaimFee
                    ELSE NULL
                END AS Oem_ClaimFee,
                CASE
                    WHEN ORD.DataStatus >= 51 THEN PAS.ChargeAmount
                    ELSE NULL
                END AS ChargeAmount,
                ORD.Deli_ConfirmArrivalFlg,
                (SELECT KeyContent FROM M_Code WHERE ValidFlg = 1 AND CodeId = 2 AND KeyCode = MPC.FixPattern) AS FixPatternName,
                VCR.ReceiptDate AS CloseReceiptDate,
                CC.ClaimedBalance AS Rct_DifferentialAmount,
                $excessPaymentColorThreshold AS ExcessPaymentColorThreshold

            FROM
                T_Order ORD
                    LEFT OUTER JOIN T_ClaimControl CC ON CC.OrderSeq = ORD.P_OrderSeq
                    LEFT OUTER JOIN T_ReceiptControl RC ON RC.ReceiptSeq = CC.LastReceiptSeq
                    LEFT OUTER JOIN AT_ReceiptControl ARC ON ARC.ReceiptSeq = CC.LastReceiptSeq
                    LEFT OUTER JOIN T_Cancel CNCL ON CNCL.OrderSeq = ORD.OrderSeq
                    LEFT OUTER JOIN T_PayingBackControl PBC ON PBC.OrderSeq = ORD.OrderSeq
                    LEFT OUTER JOIN T_PayingAndSales PAS ON PAS.OrderSeq = ORD.OrderSeq
                    LEFT OUTER JOIN T_OemSettlementFee OSF ON OSF.OrderSeq = ORD.OrderSeq
                    LEFT OUTER JOIN T_OemClaimFee OCF ON OCF.OrderSeq = ORD.OrderSeq
                    LEFT OUTER JOIN V_CloseReceiptControl VCR ON VCR.OrderSeq = CC.OrderSeq
                     %s,
                T_Customer CUS,
                T_OrderItems ITM LEFT OUTER JOIN M_DeliveryMethod MDM ON (ITM.Deli_DeliveryMethod = MDM.DeliMethodId),
                T_DeliveryDestination DELI,
                T_Enterprise ENT
                    LEFT OUTER JOIN M_PayingCycle MPC ON MPC.PayingCycleId  = ENT.PayingCycleId,
                T_Site SITE
            WHERE
                ORD.OrderSeq = CUS.OrderSeq AND
                ORD.OrderSeq = ITM.OrderSeq AND
                ITM.DeliDestId = DELI.DeliDestId AND ITM.ValidFlg = 1 AND
                ORD.EnterpriseId = ENT.EnterpriseId AND
                ORD.SiteId = SITE.SiteId
                %s
            ";

        $relatePayingControl = "";        // 2013.12.6 kashira T_PayingControl をリレーションする場合にセットされる
        $where = "";
        $whereReceipt = "";

        // WHERE句の追加

        // 注文ID
        if ($params['OrderId'] != '')
        {
            $where .= " AND ORD.ReverseOrderId like '" . mb_convert_kana(BaseUtility::escapeWildcard(strrev($params['OrderId'])), 'a', 'UTF-8')  . "%'";// 反転した注文ID、を検索(インデックス検索)
        }

        // 注文登録日
        $wRegistDate = BaseGeneralUtils::makeWhereDateTime(
            'ORD.RegistDate',
            BaseGeneralUtils::convertWideToNarrow($params['RegistDateF']),
            BaseGeneralUtils::convertWideToNarrow($params['RegistDateT'])
        );

        if ($wRegistDate != '')
        {
            $where .= " AND " . $wRegistDate;
        }

        // 注文日
        $wReceiptOrderDate = BaseGeneralUtils::makeWhereDate(
            'ORD.ReceiptOrderDate',
            BaseGeneralUtils::convertWideToNarrow($params['OrderDateF']),
            BaseGeneralUtils::convertWideToNarrow($params['OrderDateT'])
        );

        if ($wReceiptOrderDate != '')
        {
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

        // 請求先氏名
        if ($params['NameKj'] != '')
        {
            $where .= " AND CUS.SearchNameKj like '%" . mb_ereg_replace(self::REGEXP_TRIM_NAME, '', BaseUtility::escapeWildcard($params['NameKj'])) . "%'";
        }

        // 請求先カナ氏名
        if ($params['NameKn'] != '')
        {
            $where .= " AND CUS.SearchNameKn like '%" . mb_ereg_replace(self::REGEXP_TRIM_NAME, '', BaseUtility::escapeWildcard($params['NameKn'])) . "%'";
        }

        // 請求先郵便番号
        if ($params['PostalCode'] != '')
        {
            $where .= " AND CUS.PostalCode = '" . BaseGeneralUtils::convertNumberWideToNarrow(BaseUtility::escapeWildcard($params['PostalCode'])) . "'";
        }

        // 請求先住所
        if ($params['Address'] != '')
        {
            //$where .= " AND CUS.UnitingAddress like '%" . NetB_GeneralUtils::convertNumberWideToNarrow($params['Address']) . "%'";
            $where .= " AND CUS.SearchUnitingAddress like '%" . mb_ereg_replace(self::REGEXP_TRIM_NAME, '', BaseUtility::escapeWildcard($params['Address'])) . "%'";
        }

        // 請求先電話番号
        if ($params['Phone'] != '')
        {
            $where .= " AND CUS.SearchPhone like '%" . BaseGeneralUtils::convertWideToNarrow(mb_ereg_replace(self::REGEXP_TRIM_PHONE, '', BaseUtility::escapeWildcard($params['Phone']))) . "%'";
        }

        // 請求先メールアドレス
        if ($params['MailAddress'] != '')
        {
            $where .= " AND CUS.MailAddress like '%" . BaseGeneralUtils::convertWideToNarrow(BaseUtility::escapeWildcard($params['MailAddress'])) . "%'";
        }

        // 加盟店顧客番号
        if ($params['EntCustId'] != '')
        {
            $where .= " AND CUS.EntCustId like '%" . BaseGeneralUtils::convertWideToNarrow(BaseUtility::escapeWildcard($params['EntCustId'])) . "%'";
        }

        // 配送先氏名
        if ($params['DeliNameKj'] != '')
        {
            $where .= " AND DELI.SearchDestNameKj like '%" . mb_ereg_replace(self::REGEXP_TRIM_NAME, '', BaseUtility::escapeWildcard($params['DeliNameKj'])) . "%'";
        }

        // 配送先氏名カナ
        if ($params['DeliNameKn'] != '')
        {
            $where .= " AND DELI.SearchDestNameKn like '%" . mb_ereg_replace(self::REGEXP_TRIM_NAME, '', BaseUtility::escapeWildcard($params['DeliNameKn'])) . "%'";
        }

        // 配送先郵便番号
        if ($params['DeliPostalCode'] != '')
        {
            $where .= " AND DELI.PostalCode = '" . BaseGeneralUtils::convertNumberWideToNarrow(BaseUtility::escapeWildcard($params['DeliPostalCode'])) . "'";
        }

        // 配送先住所
        if ($params['DeliAddress'] != '')
        {
            //$where .= " AND DELI.UnitingAddress like '%" . NetB_GeneralUtils::convertNumberWideToNarrow($params['DeliAddress']) . "%'";
            $where .= " AND DELI.SearchUnitingAddress like '%" . mb_ereg_replace(self::REGEXP_TRIM_NAME, '', BaseUtility::escapeWildcard($params['DeliAddress'])) . "%'";
        }

        // 配送先電話
        if ($params['DeliPhone'] != '')
        {
            $where .= " AND DELI.SearchPhone like '%" . BaseGeneralUtils::convertWideToNarrow(mb_ereg_replace(self::REGEXP_TRIM_PHONE, '', BaseUtility::escapeWildcard($params['DeliPhone']))) . "%'";
        }

        // 事業者名
        if ($params['EnterpriseNameKj'] != '')
        {
            $where .= " AND ENT.EnterpriseNameKj like '%" . BaseUtility::escapeWildcard($params['EnterpriseNameKj']) . "%'";
        }

        // 事業者ID
        if ($params['LoginId'] != '')
        {
            // 指定IDに後方一致するすべてのログインIDのバリエーションをリストとして取得
            $ids = $this->_fixEntLoginIdForSearch(BaseGeneralUtils::convertWideToNarrow(BaseUtility::escapeWildcard($params['LoginId'])));
            // 取得できたログインIDが0件の場合は存在しないIDが指定されたものとする（※ロジック上ありえないが…）
            if(empty($ids)) $ids = array('AT00000000');
            // INで条件指定
            //$where .= sprintf(' AND %s', $this->app->dbAdapter->quoteInto('ENT.LoginId IN (?)', $ids));
            //$where .= " AND ENT.LoginId like '%" . NetB_GeneralUtils::convertWideToNarrow($params['LoginId']) . "'";
            $where .= (" AND ENT.LoginId IN (" . MakeQueryValStrPhraseInWithCoat($ids) . ") ");
        }

        // サイト名
        if ($params['SiteName'] != '')
        {
            $where .= " AND SITE.SiteNameKj like '%" . BaseUtility::escapeWildcard($params['SiteName']) . "%'";
        }

        /*
         // 与信点数
        $wCreditScore = NetB_GeneralUtils::makeWhereInt('ORD.Incre_ScoreTotal', $params['CreditScoreF'], $params['CreditScoreT']);
        if ($wCreditScore != '')
        {
        $where .= " AND " . $wCreditScore;
        }
        */

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

        // 伝票番号登録(登録済み／未登録)
        switch($params['RegistJournal'])
        {
            case '1':
//              $select->where("NotIncJournalCount = 0");
                $where .= " AND ITM.DataClass = 1 AND ITM.Deli_JournalIncDate IS NOT NULL";
                break;
            case '2':
//              $select->where("NotIncJournalCount > 0");
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

        // 請求日
        $wClaimDate = BaseGeneralUtils::makeWhereDate(
            'CC.F_ClaimDate',
            BaseGeneralUtils::convertWideToNarrow($params['ClaimDateF']),
            BaseGeneralUtils::convertWideToNarrow($params['ClaimDateT'])
        );

        if ($wClaimDate != '')
        {
            $where .= " AND " . $wClaimDate;
        }

        // 支払期限
        $wLimitDate = BaseGeneralUtils::makeWhereDate(
            'CC.F_LimitDate',
            BaseGeneralUtils::convertWideToNarrow($params['LimitDateF']),
            BaseGeneralUtils::convertWideToNarrow($params['LimitDateT'])
        );

        if ($wLimitDate != '')
        {
            $where .= " AND " . $wLimitDate;
        }

        // 請求金額
        $wClaimAmount = BaseGeneralUtils::makeWhereInt(
            'ORD.UseAmount',
            BaseGeneralUtils::convertWideToNarrow($params['ClaimAmountF']),
            BaseGeneralUtils::convertWideToNarrow($params['ClaimAmountT'])
        );

        if ($wClaimAmount != '')
        {
            $where .= " AND " . $wClaimAmount;
        }

        // 戻り請求書
        if (isset($params['IsReturnClaim']))
        {
            $where .= " AND ORD.ReturnClaimFlg = 1";
        }

        // 同梱/別送
        // (同梱請求書)
        if (isset($params['ClaimSendingClass1']) && !isset($params['ClaimSendingClass2'])) {
            $where .= " AND ORD.ClaimSendingClass = 11 ";
        }
        // (別送請求書)
        if (isset($params['ClaimSendingClass2']) && !isset($params['ClaimSendingClass1'])) {
            $where .= " AND ORD.ClaimSendingClass IN (12, 21) ";
        }

        // 着荷確認
        switch($params['ArrivalConfirm'])
        {
            case '1':
//              $select->where("ArrivalCount > 0");
                $where .= " AND ITM.DataClass = 1 AND ITM.Deli_ConfirmArrivalFlg = 1";
                break;
            case '2':
//              $select->where("ArrivalCount = 0");
                $where .= " AND ITM.DataClass = 1 AND ITM.Deli_ConfirmArrivalFlg < 1";
                break;
            default:
                break;
        }

        // 着荷確認日
        // ↓時分秒に関するバグ修正 2015.5.20 kashira
        //$wDeliConfirmArrivalDate = NetB_GeneralUtils::makeWhereDate(
        $wDeliConfirmArrivalDate = BaseGeneralUtils::makeWhereDateTime(
            'ORD.Deli_ConfirmArrivalDate',
            BaseGeneralUtils::convertWideToNarrow($params['DeliConfirmArrivalDateF']),
            BaseGeneralUtils::convertWideToNarrow($params['DeliConfirmArrivalDateT'])
        );

        if ($wDeliConfirmArrivalDate != '')
        {
            $where .= " AND ORD.Deli_ConfirmArrivalFlg = 1 AND " . $wDeliConfirmArrivalDate;
        }

        // 立替予定日 2013.12.6 kashira
        $wExecScheduleDate = BaseGeneralUtils::makeWhereDate(
            'PC.ExecScheduleDate',
            BaseGeneralUtils::convertWideToNarrow($params['ExecScheduleDateF']),
            BaseGeneralUtils::convertWideToNarrow($params['ExecScheduleDateT'])
        );

        if ($wExecScheduleDate != '')
        {
            $relatePayingControl = "INNER JOIN T_PayingControl PC ON (ORD.Chg_Seq = PC.Seq)";
            $where .= " AND " . $wExecScheduleDate;
        }

        // 立替実行
        // 立替実行は立替確定が行われているか否かの検索条件に変更
        switch($params['ExecCharge'])
        {
            case '1':
                //$where .= " AND ORD.Chg_ExecDate IS NOT NULL";
                $where .= " AND ORD.Chg_Seq IS NOT NULL";
                break;
            case '2':
                //$where .= " AND ORD.Chg_ExecDate IS NULL";
                $where .= " AND ORD.Chg_Seq IS NULL";
                break;
            default:
                break;
        }

        // 臨時立替日
        $wSpecialPayingDate = BaseGeneralUtils::makeWhereDateTime(
           'PAS.SpecialPayingDate',
           BaseGeneralUtils::convertWideToNarrow($params['SpecialPayingDateF']),
           BaseGeneralUtils::convertWideToNarrow($params['SpecialPayingDateT'])
        );
        if ($wSpecialPayingDate != '')
        {
            $where .= " AND " . $wSpecialPayingDate;
        }

        // 立替精算戻し日
        $wPayBackIndicationDate = BaseGeneralUtils::makeWhereDateTime(
           'PBC.PayBackIndicationDate',
           BaseGeneralUtils::convertWideToNarrow($params['PayBackIndicationDateF']),
           BaseGeneralUtils::convertWideToNarrow($params['PayBackIndicationDateT'])
        );
        if ($wPayBackIndicationDate != '')
        {
            $where .= " AND " . $wPayBackIndicationDate;
        }

        // 入金確認日
        $wReceiptConfirm = BaseGeneralUtils::makeWhereDateTime(
            'ReceiptProcessDate',
            BaseGeneralUtils::convertWideToNarrow($params['ReceiptConfirmF']),
            BaseGeneralUtils::convertWideToNarrow($params['ReceiptConfirmT'])
        );

        if ($wReceiptConfirm != '')
        {
            $whereReceipt .= " AND " . $wReceiptConfirm;
        }

        //入金日
        $wReceipt = BaseGeneralUtils::makeWhereDate(
            'ReceiptDate',
            BaseGeneralUtils::convertWideToNarrow($params['ReceiptF']),
            BaseGeneralUtils::convertWideToNarrow($params['ReceiptT'])
        );

        if ( $wReceipt != '' )
        {
            $whereReceipt .= " AND " . $wReceipt;
            $whereReceipt .= " AND ARC.Rct_CancelFlg = 0 "; // 入金取消フラグが[0：通常入金]であること
        }

        // 入金方法
        $wRcpt = "0";
        if (isset($params['rcpt1']))
        {
            $wRcpt .= ",1";
        }

        if (isset($params['rcpt2']))
        {
            $wRcpt .= ",2";
        }

        if (isset($params['rcpt3']))
        {
            $wRcpt .= ",3";
        }

        if ($wRcpt != '0')
        {
            //$where .= sprintf(" AND ORD.Rct_ReceiptMethod IN (%s)", $wRcpt);
            $whereReceipt .= sprintf(" AND ReceiptClass  IN (%s)", $wRcpt);
        }

        // 入金状態
        // (入金待ちである)
        if (isset($params['IsWaitForReceipt']) && !isset($params['IsWaitForReceipt2'])) {
            $where .= " AND ORD.DataStatus IN (51, 61) ";
        }
        // (入金待ちでない)
        if (isset($params['IsWaitForReceipt2']) && !isset($params['IsWaitForReceipt'])) {
            $where .= " AND ORD.DataStatus NOT IN (51, 61) ";
        }

//         // 延滞状態
//         if (isset($params['IsToLateFirst']))
//         {
//             $where .= " AND ORD.DataStatus = 51 AND CC.F_LimitDate < CURDATE()";
//         }

//         if (isset($params['IsToLateLatest']))
//         {
//             $where .= " AND ORD.DataStatus = 51 AND CC.L_LimitDate < CURDATE()";
//         }

        // 一部入金
        if (isset($params['ichibunyukin'])) {
            $where .= " AND ORD.DataStatus = 61 ";
        }

        // 請求ストップ
        /*
         if (isset($params['IsStopClaim']))
         {
        $where .= " AND ORD.StopClaimFlg = 1";
        }
        */

//         // 紙請求ストップ
//         if (isset($params['IsStopLetterClaim']))
//         {
//             $where .= " AND ORD.LetterClaimStopFlg = 1";
//         }

//         // 請求ストップ
//         if (isset($params['IsStopMailClaim']))
//         {
//             $where .= " AND ORD.MailClaimStopFlg = 1";
//         }

//         // 最終請求日
//         $wLatestClaimDate = BaseGeneralUtils::makeWhereDate(
//             'CC.ClaimDate',
//             BaseGeneralUtils::convertWideToNarrow($params['LatestClaimDateF']),
//             BaseGeneralUtils::convertWideToNarrow($params['LatestClaimDateT'])
//         );

//         if ($wLatestClaimDate != '')
//         {
//             $where .= " AND " . $wLatestClaimDate;
//         }

/*
		// 内容証明
		switch($params['NaiyoSyomei'])
		{
			case '1':
//				$select->where("NaiyoCount > 0");
				$where .= " AND (SELECT COUNT(*) FROM T_ClaimHistory WHERE OrderSeq = ORD.OrderSeq AND PrintedFlg = 1 AND ClaimPattern = 5) > 0";
				break;
			case '2':
//				$select->where("NaiyoCount = 0");
				$where .= " AND (SELECT COUNT(*) FROM T_ClaimHistory WHERE OrderSeq = ORD.OrderSeq AND PrintedFlg = 1 AND ClaimPattern = 5) = 0";
				break;
			default:
				break;
		}
*/

        // キャンセル確認日
//      $wCancelConfirmDate = NetB_GeneralUtils::makeWhereDate('CancelConfirmDate', $params['CancelConfirmDateF'], $params['CancelConfirmDateT']);
//      if ($wCancelConfirmDate != '')
//      {
//          $select->where($wCancelConfirmDate);
//      }
        if ($params['CancelConfirmDateF'] != '' && $params['CancelConfirmDateT'] != '')
        {
            $where .= sprintf(" AND
                (SELECT
                    COUNT(*)
                FROM
                    T_Cancel
                WHERE
                    ValidFlg = 1 AND
                    OrderSeq = ORD.OrderSeq AND
                    ApproveFlg = 1 AND
                    ApprovalDate BETWEEN '%s' AND '%s'
                ) > 0",
                BaseGeneralUtils::convertWideToNarrow(BaseUtility::escapeWildcard($params['CancelConfirmDateF'])) . ' 00:00:00',
                BaseGeneralUtils::convertWideToNarrow(BaseUtility::escapeWildcard($params['CancelConfirmDateT'])) . ' 23:59:59'
            );
        }
        else if ($params['CancelConfirmDateF'] != '')
        {
            $where .= sprintf(" AND
                (SELECT
                    COUNT(*)
                FROM
                    T_Cancel
                WHERE
                    ValidFlg = 1 AND
                    OrderSeq = ORD.OrderSeq AND
                    ApproveFlg = 1 AND
                    ApprovalDate >= '%s'
                ) > 0",
                BaseGeneralUtils::convertWideToNarrow(BaseUtility::escapeWildcard($params['CancelConfirmDateF'])) . ' 00:00:00'
            );
        }
        else if ($params['CancelConfirmDateT'] != '')
        {
            $where .= sprintf(" AND
                (SELECT
                    COUNT(*)
                FROM
                    T_Cancel
                WHERE
                    ValidFlg = 1 AND
                    OrderSeq = ORD.OrderSeq AND
                    ApproveFlg = 1 AND
                    ApprovalDate <= '%s'
                ) > 0",
                BaseGeneralUtils::convertWideToNarrow(BaseUtility::escapeWildcard($params['CancelConfirmDateT'])) . ' 23:59:59'
            );
        }

        // キャンセル状態
        /*if (isset($params['IsNotCancel']))
         {
        $where .= " AND ORD.Cnl_Status = 0";
        }*/

        if ($params['IsNotCancel'] == '1')
        {
            $where .= " AND ORD.Cnl_Status = 0";
        } elseif($params['IsNotCancel'] == '2') {
            $where .= " AND (ORD.Cnl_Status = 1 OR ORD.Cnl_Status = 2)";
        }

        //キャンセル区分
        if ($params['classifyCancel'] == '1' && $params['IsNotCancel'] == '2'){
            $where .= " AND (ORD.Cnl_ReturnSaikenCancelFlg = 0 OR ORD.Cnl_ReturnSaikenCancelFlg IS NULL)";
        } elseif($params['classifyCancel'] == '2' && $params['IsNotCancel'] == '2') {
            $where .= " AND ORD.Cnl_ReturnSaikenCancelFlg = 1";
        }

        // キャンセル理由
        if ((int)$params['cancelreason'] > 0) {
            $where .= (" AND CNCL.CancelReasonCode = " . (int)$params['cancelreason']);
        }

        // 備考
        if ($params['Oem_Note'] != '')
        {
            $where .= " AND ORD.Oem_Note like '%" . BaseUtility::escapeWildcard($params['Oem_Note']) . "%'";
        }

        // 事業者用：任意番号
        if ($params['Ent_OrderId'] != '')
        {
            $where .= " AND ORD.Ent_OrderId like '" . BaseUtility::escapeWildcard($params['Ent_OrderId']) . "%'";
        }

        // 2009.05.14 masuyama 検索条件追加
        // 営業担当
        if ($params['Salesman'] != '')
        {
            $where .= " AND ENT.Salesman like '%" . BaseUtility::escapeWildcard($params['Salesman']) . "%'";
        }

        // プラン
        if ($params['Plan'] != 0) {
            $where .= (" AND ENT.Plan = " . $params['Plan']);
        }

        // 締日パターン
        if ($params['fixPattern'] != 0) {
            $where .= (" AND MPC.FixPattern = " . $params['fixPattern']);
        }

//         // 住民票
//         switch($params['ResidentCard'])
//         {
//             case '1':                                    // 手
//                 $where .= " AND CUS.ResidentCard = 1";
//                 break;
//             case '2':                                    // 申
//                 $where .= " AND CUS.ResidentCard = 2";
//                 break;
//             case '3':                                    // ○
//                 $where .= " AND CUS.ResidentCard = 3";
//                 break;
//             case '4':                                    // ×
//                 $where .= " AND CUS.ResidentCard = 4";
//                 break;
//             default:
//                 break;
//         }

        // 事業者任意欄
        if ($params['Ent_Note'] != '')
        {
            $where .= " AND ORD.Ent_Note like '%" . BaseUtility::escapeWildcard($params['Ent_Note']) . "%'";
        }

        // 補償対象外
        if (isset($params['OutOfAmends']))
        {
            $where .= " AND ORD.OutOfAmends = 1";
        }

        // 取りまとめ(取りまとめデータのみ、時条件追加)
        if($params['CombinedClaimTargetStatus'] == '1') {
            $where .= " AND (ORD.CombinedClaimTargetStatus IS NOT NULL AND ORD.CombinedClaimTargetStatus <> 0) ";
        }

        // 取りまとめ代表(代表のみ、時条件追加)
        if($params['CombinedClaimParentFlg'] == '1') {
            $where .= " AND ORD.CombinedClaimParentFlg = 1 ";
        }

        // 代表注文ID
        if ($params['pOrderId'] != '')
        {
            $where .= " AND ORD.P_OrderSeq IN (";
            $where .= " SELECT A.OrderSeq FROM T_Order A WHERE A.OrderId like '%" . BaseUtility::escapeWildcard($params['pOrderId']) . "'";
            $where .= " ) ";
        }

        // OrderSeqリスト指定
        if (!empty($params['seqs']) && is_array($params['seqs']))
        {
            $where .= $this->app->dbAdapter->quoteInto(' AND ORD.OrderSeq IN (?)', $params['seqs']);
        }

        // OEM用任意注文番号
        if ($params['Oem_OrderId'] != '')
        {
            $where .= " AND ORD.Oem_OrderId like '" . BaseUtility::escapeWildcard($params['Oem_OrderId']) . "%'";
        }

        // 入金情報は1注文に複数あるので、該当するデータが含まれる注文が対象
        if (strlen($whereReceipt) > 0) {
            $where .= " AND EXISTS (SELECT * FROM T_ReceiptControl WHERE OrderSeq = ORD.P_OrderSeq " . $whereReceipt . ") ";
        }

        // ここまで条件がなかったら例外
        if(($params['searchkbn'] == "" || $params['searchkbn'] == "0") && !strlen($where)) {
            throw new SearchoControllerException('検索条件が入力されていません');
        }

        $where .= " AND ENT.OemId = ".BaseUtility::escapeWildcard($params['OemId']);
        $where .= " AND ORD.OemId = ".BaseUtility::escapeWildcard($params['OemId']);

        // 検索区分による注文テーブルの検索条件
        switch($params['searchkbn'])
        {
            case '1':    // 与信中
                $where  = " AND ORD.OemId = ".$params['OemId'];
                $where .= " AND ORD.Cnl_Status = 0 ";
                $where .= " AND ORD.DataStatus IN (11, 15, 21) ";
                break;
            case '2':    // 伝票番号登録中
                $where  = " AND ORD.OemId = ".$params['OemId'];
                $where .= " AND ORD.Cnl_Status = 0 ";
                $where .= " AND ORD.DataStatus IN (31) ";
                break;
            case '3':    // 請求書発行中
                $where  = " AND ORD.OemId = ".$params['OemId'];
                $where .= " AND ORD.Cnl_Status = 0 ";
                $where .= " AND ORD.P_OrderSeq = ORD.OrderSeq ";
                $where .= " AND (SELECT COUNT(*) FROM T_ClaimHistory WHERE OrderSeq = ORD.OrderSeq AND PrintedFlg = 0) > 0";
                break;
            case '4':    // 着荷確認中
                $where  = " AND ORD.OemId = ".$params['OemId'];
                $where .= " AND ORD.Cnl_Status = 0 ";
                $where .= " AND ORD.DataStatus IN (51, 61) ";
                $where .= " AND ORD.Deli_ConfirmArrivalFlg <> 1 ";
                break;
            case '5':    // 入金確認中
                $where  = " AND ORD.OemId = ".$params['OemId'];
                $where .= " AND ORD.Cnl_Status = 0 ";
                $where .= " AND ORD.DataStatus IN (51, 61) ";
                break;
            default:
                break;
        }

        // クエリー生成
        $query = sprintf($query, $relatePayingControl, $where);

        //var_dump($query);
//$this->app->logger->debug(sprintf('[SearchoController::getSearchResult] query = %s', $query));

        // クエリー実行
        $ri = $this->app->dbAdapter->query($query)->execute(null);
        $rs = new ResultSet();
        $rs->initialize($ri);
        $datas = $rs->toArray();

        // fetch結果の不足情報の補間は別メソッドに分離（2013.3.29 eda）
        $datas = $this->_fillSearchResult($datas);
$this->app->logger->debug(sprintf('[SearchoController::getSearchResult] total time = %s', microtime(true) - $start_time));
        return $datas;
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
        $op_cache = array();    // Table_Operator::findOperatorの実行結果キャッシュ

        $datasCount = 0;
        if(!empty($datas)){
            $datasCount = count($datas);
        }

        for ($i = 0 ; $i < $datasCount ; $i++)
        {
            $datas[$i]['Incre_ArAddr'] = $codeMaster->getCreditClassShortCaption($datas[$i]['Incre_ArAddr']);
            $datas[$i]['Incre_ArTel'] = $codeMaster->getCreditClassShortCaption($datas[$i]['Incre_ArTel']);
            $datas[$i]['eDen'] = $codeMaster->getEDenCaption($datas[$i]['eDen']);
            $datas[$i]['PhoneHistory'] = $codeMaster->getPhoneHistoryCaption($datas[$i]['PhoneHistory']);

            // 与信担当者名の解決。担当IDが空の場合は放置
            if( ! empty( $datas[$i]['Incre_DecisionOpId'] ) ) {
                // DB問い合わせはキャッシュされていない場合のみ
                if( ! isset( $op_cache[ $datas[$i]['Incre_DecisionOpId'] ] ) ) {
                    $op_cache[ $datas[$i]['Incre_DecisionOpId'] ] = $mdlop->findOperator($datas[$i]['Incre_DecisionOpId'])->current()->NameKj;
                }
                $datas[$i]['Incre_DecisionOpId'] = $op_cache[ $datas[$i]['Incre_DecisionOpId'] ];
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

            if ($datas[$i]['Bekkan'] == '' || $datas[$i]['Bekkan'] == '0')
            {
                $datas[$i]['Bekkan'] = '通常';
            }
            else
            {
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
$start_time = microtime(true);
        $db = $this->app->dbAdapter;

        $userInfo = $this->app->authManagerAdmin->getUserInfo();

        // 指定条件に一致するOrderSeqのみを抽出する予備クエリを組み立てる。
        // 生成されるSQLは
        // SELECT o.OrderSeq FROM T_Order o INNER JOIN T_Customer c ON c.OrderSeq = o.OrderSeq
        // がベースとなる
//         $pre_select = new Zend_Db_Select($db);
//         $pre_select
//             ->from(array('o' => 'T_Order'), 'OrderSeq')
//             ->joinInner(array('c' => 'T_Customer'), 'c.OrderSeq = o.OrderSeq', array());

        // 条件部分の組み立てを実行
        $pre_select  = "SELECT o.OrderSeq ";
        $pre_select .= "FROM   T_Order o ";
        $pre_select .= "       INNER JOIN T_Customer c ON c.OrderSeq = o.OrderSeq ";
        $pre_select .= "WHERE 1 = 1 ";

        $normalizers = array(
            'order_id' =>LogicNormalizer::create(LogicNormalizer::FILTER_FOR_ID),
            'name' => LogicNormalizer::create(LogicNormalizer::FILTER_FOR_NAME),
            'phone' => LogicNormalizer::create(LogicNormalizer::FILTER_FOR_TEL),
            'address' => LogicNormalizer::create(LogicNormalizer::FILTER_FOR_ADDRESS),
            'mail' => LogicNormalizer::create(LogicNormalizer::FILTER_FOR_MAIL)
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
            }
        }

        // 条件が1つも入力されていなかったらFALSEを返して終了
        if(!$has_expression) return false;

        $pre_select .= sprintf(" AND o.OemId = " .$userInfo->OemId);

        // OrderSeqの配列を取得
        $ri = $this->app->dbAdapter->query($pre_select)->execute(null);
        $seqs = array();
        foreach ($ri as $row) {
            $seqs[] = $row['OrderSeq'];
        }

        // ヒットなしの場合は空配列を返す
        if(empty($seqs)) return array();

        // 本検索
        $query = $this->_getQuickOrSpecialSearchBaseQuery();
        $where = (" ORD.OrderSeq IN (" . MakeQueryValStrPhraseIn($seqs) . ") ");

        // 検索結果を返す
        $ri = $this->app->dbAdapter->query(sprintf($query, $where))->execute(null);
        $rs = new ResultSet();
        $rs->initialize($ri);
        $datas = $this->_fillSearchResult($rs->toArray());

$this->app->logger->debug(sprintf('[SearchoController::getQuickSearchResult] total time = %s', microtime(true) - $start_time));
        return $datas;
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
SELECT DISTINCT
    ORD.OrderSeq,
    ORD.OrderId,
    ORD.Ent_OrderId,
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
    SUM.Deli_JournalNumber,
    CC.F_ClaimDate,
    CC.F_LimitDate,
    ORD.UseAmount,
    RC.ReceiptDate,
    CC.ReceiptAmountTotal AS ReceiptAmount,
    RC.ReceiptClass,
	CASE ORD.Rct_Status
       WHEN '0' THEN ''
       WHEN '1' THEN (CASE RC.ReceiptClass WHEN 1 THEN 'コンビニ' WHEN 2 THEN '郵便局' WHEN 3 THEN '銀行' WHEN 4 THEN 'LINE Pay' ELSE '' END)
       ELSE ''
    END AS ReceiptClassName,
    ORD.Incre_Note,
    ORD.MailLimitPassageDate,
    CUS.eDen,
    CUS.PhoneHistory,
    ORD.Incre_DecisionOpId,
    CUS.Incre_ArAddr,
    CUS.Incre_ArTel,
    CUS.RealSendMailResult,
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
    ORD.Oem_Note,
    ENT.EnterpriseId,
    CASE
        WHEN ORD.DataStatus >= 51 THEN PAS.AppSettlementFeeRate
        ELSE NULL
    END AS AppSettlementFeeRate,
    CASE
        WHEN ORD.DataStatus >= 51 THEN PAS.SettlementFee
        ELSE NULL
    END AS SettlementFee,
    CASE
        WHEN ORD.DataStatus >= 51 THEN PAS.ClaimFee
        ELSE NULL
    END AS ClaimFee,
    CASE
        WHEN ORD.DataStatus >= 51 THEN OSF.AppSettlementFeeRate
        ELSE NULL
    END AS Oem_AppSettlementFeeRate,
    CASE
        WHEN ORD.DataStatus >= 51 THEN OSF.SettlementFee
        ELSE NULL
    END AS Oem_SettlementFee,
    CASE
        WHEN ORD.DataStatus >= 51 THEN OCF.ClaimFee
        ELSE NULL
    END AS Oem_ClaimFee,
    CASE
        WHEN ORD.DataStatus >= 51 THEN PAS.ChargeAmount
        ELSE NULL
    END AS ChargeAmount,
    ORD.Deli_ConfirmArrivalFlg,
    (SELECT KeyContent FROM M_Code WHERE ValidFlg = 1 AND CodeId = 2 AND KeyCode = MPC.FixPattern) AS FixPatternName,
    VCR.ReceiptDate AS CloseReceiptDate,
    CC.ClaimedBalance AS Rct_DifferentialAmount,
    $excessPaymentColorThreshold AS ExcessPaymentColorThreshold
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
    T_ClaimControl CC ON CC.OrderSeq = ORD.P_OrderSeq
        LEFT OUTER JOIN
    T_ReceiptControl RC ON RC.ReceiptSeq = CC.LastReceiptSeq
    LEFT OUTER JOIN M_PayingCycle MPC ON MPC.PayingCycleId = ENT.PayingCycleId
    LEFT OUTER JOIN T_PayingAndSales PAS ON PAS.OrderSeq = ORD.OrderSeq
    LEFT OUTER JOIN T_OemSettlementFee OSF ON OSF.OrderSeq = ORD.OrderSeq
    LEFT OUTER JOIN T_OemClaimFee OCF ON OCF.OrderSeq = ORD.OrderSeq
    LEFT OUTER JOIN V_CloseReceiptControl VCR ON VCR.OrderSeq = CC.OrderSeq
WHERE
    %s
ORDER BY
	ORD.OrderSeq
EOQ;
    }

// Del By Takemasa(NDC) 20150917 Stt 機能廃止故コメントアウト化
//     /**
//      * 簡易検索で入力された注文IDの後方パートの不足分を
//      * DBから取得した情報で補って検索用注文IDの配列を生成する。
//      * 入力されたIDの数字部分が8桁以上ある場合は後方8桁分から1件のみ生成する。
//      * 入力桁数が3桁未満の場合は、ID指定がなかったものと見なして空の配列を返す。
//      *
//      * @access private
//      * @param string $id 注文ID。呼出し前に正規化されている必要がある
//      * @return array
//      */
//     private function _fixOrderIdForQuickSearch($id)
// Del By Takemasa(NDC) 20150917 End 機能廃止故コメントアウト化

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
        } else {
            // 足りない桁数分をDBから取得して必要なIDを生成する
            $q = sprintf('SELECT DISTINCT SUBSTRING(LoginId, 1, %d) AS substrloginid FROM T_Enterprise', $dif + 2);
            $oemId = $this->app->authManagerAdmin->getUserInfo()->OemId;

            if(strlen($prefix)) {
                // プレフィックス指定がある場合は一致するプレフィックスのみに絞り込む
                $q .= sprintf(" WHERE LoginId LIKE '%s%%' AND OemId = %d", BaseUtility::escapeWildcard($prefix), $oemId);
            }else{
                $q .= sprintf(" WHERE OemId = %d", $oemId);
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
