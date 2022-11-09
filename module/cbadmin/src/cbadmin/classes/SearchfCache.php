<?php
namespace cbadmin\classes;

use cbadmin\Application;
use Zend\Session\Container;
use Zend\Db\Adapter\Adapter;
use Coral\Base\BaseGeneralUtils;
use Coral\Base\Reflection\BaseReflectionUtility;
use Coral\Coral\CoralCodeMaster;
use Coral\Coral\CoralValidate;
use models\Table\TableOperator;
use models\Table\TableClaimHistory;
use models\Table\TableOrderItems;
use models\Table\TableCjResult;
use models\Table\TableCancel;
use models\Table\TableCode;

class SearchfCache {
    /**
     * セッション名前空間
     *
     * @var string
     */
    const SESSION_STORAGE_NAME = 'SearchResult_SessionStorage';

    /**
     * 氏名・氏名かなの検索データを作成するための不要文字抽出用正規表現
     *
     * @static
     * @var string
     */
    const REGEXP_TRIM_NAME = '[ 　\r\n\t\v]';

    /**
     * 電話番号の検索データを作成するための不要文字抽出用正規表現
     *
     * @static
     * @var string
     */
    const REGEXP_TRIM_PHONE = '[^0-9０-９]';

    /**
     * 不払い検索用クエリの基本部分
     *
     * @static
     * @var string
     */
    const BASE_QUERY = "
/* (cbadmin)不払い検索基礎.SQL */
SELECT DISTINCT
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
,      P_CUS.EntCustId
,      P_MC.GoodFlg
,      P_MC.BlackFlg
,      (SELECT MAX(Deli_JournalIncDate) FROM T_OrderItems WHERE OrderSeq = P_ORD.OrderSeq AND ValidFlg = 1) AS Deli_JournalIncDate
,      P_MDM.DeliMethodName
,      P_ITM.Deli_JournalNumber
,      CC.F_ClaimDate
,      CC.F_LimitDate
,      P_ORD.UseAmount
,      RC.ReceiptDate
,      RC.ReceiptAmount
,      RC.ReceiptClass
,      P_ORD.Incre_Note
,      P_ORD.MailLimitPassageDate
,      P_CUS.eDen
,      P_CUS.PhoneHistory
,      P_ORD.Incre_DecisionOpId
,      RC.ReceiptClass
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
";

    /**
     * 初回支払期限指定によるフィルタを指定するキー定数
     *
     * @static
     * @var string
     */
    const FILTER_TARGET_MONTH = 'filter_month';

    /**
     * 督促分類によるフィルタを指定するキー定数
     *
     * @static
     * @var string
     */
    const FILTER_TARGET_REMIND_CLASS = 'filter_class';

    /**
     * 再検索指定に使用する追加条件定数。
     *
     * @static
     * @var string
     */
    const REDO_EXPRESSIONS_ALL = 'all';

    /**
     * '支払意思ありのみ'での再検索指定に使用する追加条件定数
     *
     * @static
     * @var string
     */
    const REDO_EXPRESSIONS_INCLUDE = 'include';

    /**
     * '支払意思あり除く'での再検索指定に使用する追加条件定数
     *
     * @static
     * @var string
     */
    const REDO_EXPRESSIONS_EXCLUDE = 'exclude';

    /**
     * 該当件数のサマリキー定数
     *
     * @static
     * @var string
     */
    const SUMMARY_KEY_TOTALCOUNT = '該当件数';

    /**
     * 合計利用額のサマリキー定数
     *
     * @static
     * @var string
     */
    const SUMMARY_KEY_TOTALUSEAMOUNT = '合計利用額';

    /**
     * 入金額合計のサマリキー定数
     *
     * @static
     * @var string
     */
    const SUMMARY_KEY_TOTALRECEIPTAMOUNT = '入金額合計';

    /**
     * 未入金額合計のサマリキー定数
     *
     * @static
     * @var string
     */
    const SUMMARY_KEY_TOTALNORECEIPTAMOUNT = '未入金額合計';

    /**
     * 現在請求額合計のサマリキー定数
     *
     * @static
     * @var string
     */
    const SUMMARY_KEY_TOTALAMOUNT = '現在請求額合計';

    /**
     * 担当者IDと担当者名のペアデータのキャッシュ
     *
     * @static
     * @access private
     * @var array
     */
    private static $_operator_cache = array();

    /**
     * オペレータ検索用のTableOperator
     *
     * @static
     * @access private
     * @var TableOperator
     */
    private static $_operator_table = null;

    /**
     * 不払い検索関連のデータを格納するためのセッションストレージを取得する。
     * 名前空間はSearchfCache::SESSION_STORAGE_NAME定数で定義されている固定の名前となり、
     * キャッシュされている場合はSearchfCache::SESSION_CACHE_NAMEでSessionfCacheのインスタンスにアクセスできる
     *
     * @static
     * @return Container
     */
    public static function getSessionStorage() {
        return new Container( self::SESSION_STORAGE_NAME );
    }

    /**
     * このクラスのセッション名前空間に格納されているSearchfCacheのインスタンスを取得する。
     * セッションストレージにインスタンスが格納されていない場合はnullを返す。
     *
     * @static
     * @return SearchfCache|null
     */
    public static function getInstanceFromStorage() {
        $storage = self::getSessionStorage();
        $instance = isset($storage->cacheInstance) ? unserialize($storage->cacheInstance) : null;
        return $instance;
    }
    /**
     * 指定のSearchfCacheインスタンスをこのクラスのセッション名前空間に格納する
     *
     * @param SearchfCache $instance セッションストレージに格納するSearchfCacheインスタンス
     */
    public static function setInstanceToStorage(SearchfCache $instance) {
        $storage = self::getSessionStorage();

        // シリアライズ向けにデータベースアダプタを削除する
        $db = $instance->_db;
        unset( $instance->_db );

        $storage->cacheInstance = serialize($instance);
        // データベースアダプタを復旧
        $instance->_db = $db;
    }
    /**
     * このクラスのセッション名前空間に格納されているSearchfCacheのインスタンスを
     * ストレージから削除する
     *
     * @static
     */
    public static function clearInstanceFromStorage() {
        $storage = self::getSessionStorage();
        if( isset( $storage->cacheInstance ) ) {
            $instance = self::getInstanceFromStorage();
            $id = ($instance instanceof self) ? $instance->getCacheId() : '';
            unset( $storage->cacheInstance );
        }
    }

    /**
     * 現在月から指定した月数まで以前の年月のリストを生成する。
     * リストの各項目はキー'label'にyyyy年 MM月、キー'value'にyyyy-MM-01が格納された連想配列になる。
     *
     * @param null|integer $months 月数。0以下や整数以外または省略時は12として解釈される
     * @return array
     */
    public static function generateMonthList($months = 12) {
        $months = BaseReflectionUtility::isPositiveInteger($months) ? $months : 12;
        $results = array();
        $d = date('Y-m-d');

        for($i = 0; $i < $months; $i++) {
            $results[] = array(
                'label' => date( 'Y年 m月', strtotime($d) ),
                'value' => date( 'Y-m-01', strtotime($d) )
            );
            $d = date( 'Y-m-01', strtotime($d . " -1 month"));
        }
        return $results;
    }
    /**
     * 担当者情報を検索するTableOperatorを取得する
     *
     * @static
     * @access private
     * @return TableOperator
     */
    private static function _getOperatorTable() {
        if( self::$_operator_table == null ) {
            self::$_operator_table = new TableOperator( Application::getInstance()->dbAdapter );
        }
        return self::$_operator_table;
    }
    /**
     * 指定のIDに一致するオペレータの氏名を取得する
     *
     * @static
     * @access private
     * @return string
     */
    private static function _getOperatorName($id) {
        if($id == null){
            return "";
        }
        if( self::$_operator_cache == null ) {
            self::$_operator_cache = array();
        }
        if( ! isset( self::$_operator_cache[$id] ) ) {
            // キャッシュされていない場合のみDB検索を行う
            $table = self::_getOperatorTable();
            self::$_operator_cache[$id] = $table->findOperator($id)->current()['NameKj'];
        } else {
        }
        return self::$_operator_cache[$id];
    }

    /**
     * 検索フォームで指定された検索条件
     *
     * @access protected
     * @var array
     */
    protected $_expressions = array();

    /**
     * 再検索（支払意思の有無）条件
     *
     * @access protected
     * @var string
     */
    protected $_redo_expression = self::REDO_EXPRESSIONS_ALL;

    /**
     * 絞込条件（追加検索キー）
     *
     * @access protected
     * @var array
     */
    protected $_filters = array();

    /**
     * ORDER BYに適用するソート情報の配列
     *
     * @access protected
     * @var array
     */
    protected $_sortKeys = array();

    /**
     * データベースアダプタ
     *
     * @access protected
     * @var Adapter $adapter アダプタ
     */
    protected $_db;

    /**
     * 検索フォームからの初期検索時に集計したサマリ情報
     *
     * @access protected
     * @var array
     */
    protected $_summaries = array(
        self::SUMMARY_KEY_TOTALCOUNT        => 0,
        self::SUMMARY_KEY_TOTALUSEAMOUNT    => 0,
        self::SUMMARY_KEY_TOTALRECEIPTAMOUNT     => 0,
        self::SUMMARY_KEY_TOTALNORECEIPTAMOUNT     => 0,
        self::SUMMARY_KEY_TOTALAMOUNT       => 0
    );

    /**
     * 最後に実行した検索結果
     *
     * @access protected
     * @var array
     */
    protected $_search_results = array();

    /**
     * 初期条件検索の集計結果を保持しているかのフラグ
     *
     * @access protected
     * @var boolean
     */
    protected $_has_summaries = false;

    /**
     * インスタンス生成時に割り当てられるユニークID
     *
     * @var string
     */
    protected $_cashe_id;

    /**
     * 検索フォームで指定された検索条件に対するバインド値
     *
     * @access protected
     * @var array
     */
    protected $_bindparams = array();

    /**
     * 検索条件のOEMID
     *
     * @var int
     */
    public $oem_id;

    /**
     * SearchfCacheの新しいインスタンスを初期化する
     *
     */
    public function __construct() {
        // ユニークIDを時分秒＋1/10ミリ秒のシリアル値から生成する
        $mt = explode(' ', microtime());
        $this->_cashe_id = dechex( date('His') . substr($mt[0], 2, 3) );

        // ソートキーを初期化
        $this->clearSortKeys();
    }

    /**
     * このインスタンスが生成された時点で割り振られたユニークなIDを
     * 取得する。
     * 値のユニークネスは24時間以内の同一セッション内でのみ保証される
     *
     * @return string
     */
    public function getCacheId() {
        return $this->_cashe_id;
    }

    /**
     * データベース接続のためのAdapterを取得する
     *
     * @return Adapter $adapter アダプタ
     */
    public function getDbAdapter() {
        return $this->_db;
    }
    /**
     * データベース接続のためのAdapterを設定する
     *
     * @param Abstract|null $db
     * @return SearchfCache
     */
    public function setDbAdapter($db) {
        $this->_db = $db;

        return $this;
    }

    /**
     * 検索結果を保持しているかを取得する
     *
     * @return boolean 有効な検索結果を保持している場合はtrue、それ以外はfalse
     */
    public function hasResults() {
        // count関数対策
        $searchResultsCount = 0;
        if (!empty($this->_search_results)) {
            // count関数対策
            $searchResultsCount = count($this->_search_results);
        }
        return $searchResultsCount > 1;
    }

    /**
     * 現在の検索結果を取得する。有効な検索結果がキャッシュされていない場合、
     * 現在の検索条件で再検索を行う。
     *
     * @return array 検索結果を示す連想配列
     */
    public function getResults() {
        // キャッシュがなければ再検索
        if( ! $this->hasResults() ) {
            $this->_execute();
        }

        return $this->_search_results;
    }

    /**
     * 件数や金額の集計結果を取得する
     *
     * @return array
     */
    public function getSummaries() {
        return $this->_summaries;
    }
    /**
     * 集計結果を初期化する
     *
     * @return SearchfCache このインスタンス自身
     */
    public function initSummaries() {
        $this->_summaries = array(
            self::SUMMARY_KEY_TOTALCOUNT        => 0,
            self::SUMMARY_KEY_TOTALUSEAMOUNT    => 0,
            self::SUMMARY_KEY_TOTALRECEIPTAMOUNT     => 0,
            self::SUMMARY_KEY_TOTALNORECEIPTAMOUNT     => 0,
            self::SUMMARY_KEY_TOTALAMOUNT       => 0
        );

        return $this;
    }

    /**
     * 初期検索条件のリストを取得する。
     *
     * @return array 各要素に検索条件式を格納した配列
     */
    public function getExpressions() {
        return $this->_expressions;
    }
    /**
     * 検索フォームから受け取ったパラメータを元に検索条件を構築し、初期検索条件として設定する
     * このメソッドを実行すると、検索キャッシュはクリアされ、集計結果およびソート順が初期化される
     *
     * @param array $params 検索フォームから受け取ったGET/POSTパラメータの連想配列
     * @return SearchfCache このインスタンス自身
     */
    public function buildExpressions($params) {

        $codeMaster = new CoralCodeMaster($this->_db);

        // 検索結果をクリア
        $this->_search_results = array();

        // サマリ再集計フラグをクリア
        $this->_has_summaries = false;

        $expressions = array();
        $bindparams = array();

        // ソート順を初期化
        $this->clearSortKeys();

        //---------------------------------
        // 以下、各種条件の対応
        //---------------------------------
        // 入金済みか否か
        if (isset($params['IsReceipt'])) {
            $expressions[] = " (ORD.DataStatus = 51 OR ORD.DataStatus = 61 OR (ORD.DataStatus = 91 AND ORD.CloseReason = 1)) ";
        } else {
            $expressions[] = " (ORD.DataStatus = 51 OR ORD.DataStatus = 61) ";
        }

        // 注文ID
        if ($params['OrderId'] != '') {
            $expressions[] = " ORD.OrderId like :OrderId ";
            $bindparams += array(':OrderId' => '%' . mb_convert_kana($params['OrderId'], 'a', 'UTF-8'));
        }

        // 注文日
        $wReceiptOrderDate = BaseGeneralUtils::makeWhereDate(
            'ORD.ReceiptOrderDate',
            BaseGeneralUtils::convertWideToNarrow($params['OrderDateF']),
            BaseGeneralUtils::convertWideToNarrow($params['OrderDateT'])
        );
        if ($wReceiptOrderDate != '') {
            $expressions[] = $wReceiptOrderDate;
        }

        // 注文登録日
        $wRegistDate = BaseGeneralUtils::makeWhereDate(
            'DATE(ORD.RegistDate)',   /* 比較対象のDATETIME⇒DATE変換化(20150526_1255) */
            BaseGeneralUtils::convertWideToNarrow($params['RegistDateF']),
            BaseGeneralUtils::convertWideToNarrow($params['RegistDateT'])
        );
        if ($wRegistDate != '') {
            $expressions[] = $wRegistDate;
        }

        // 役務提供予定日
        $wServiceExpectedDate = BaseGeneralUtils::makeWhereDate(
            'ORD.ServiceExpectedDate',
            BaseGeneralUtils::convertWideToNarrow($params['ServiceExpectedDateF']),
            BaseGeneralUtils::convertWideToNarrow($params['ServiceExpectedDateT'])
        );
        if ($wServiceExpectedDate != '') {
            $expressions[] = $wServiceExpectedDate;
        }

        // 請求先氏名・配送先氏名
        if ($params['NameKj'] != '') {
            $expressions[] = " (CUS.SearchNameKj like :SearchNameKj OR DELI.SearchDestNameKj like :SearchNameKj) ";
            $bindparams += array(':SearchNameKj' => '%' . mb_ereg_replace(self::REGEXP_TRIM_NAME, '', $params['NameKj']) . '%');
        }

        // 請求先カナ氏名・配送先カナ氏名
        if ($params['NameKn'] != '') {
            $expressions[] = " (CUS.SearchNameKn like :SearchNameKn OR DELI.SearchDestNameKn like :SearchNameKn) ";
            $bindparams += array(':SearchNameKn' => '%' . mb_ereg_replace(self::REGEXP_TRIM_NAME, '', $params['NameKn']) . '%');
        }

        // 請求先住所・配送先住所
        if ($params['Address'] != '') {
            $expressions[] = " (CUS.UnitingAddress like :UnitingAddress OR DELI.UnitingAddress like :UnitingAddress) ";
            $bindparams += array(':UnitingAddress' => '%' . BaseGeneralUtils::convertNumberWideToNarrow($params['Address']) . '%');
        }

        // 請求先電話番号・配送先電話番号
        if ($params['Phone'] != '') {
            $expressions[] = " (CUS.SearchPhone like :SearchPhone OR DELI.SearchPhone like :SearchPhone) ";
            $bindparams += array(':SearchPhone' => '%' . BaseGeneralUtils::convertWideToNarrow(mb_ereg_replace(self::REGEXP_TRIM_PHONE, '', $params['Phone'])) . '%');
        }

        // 請求先メールアドレス
        if ($params['MailAddress'] != '') {
            $expressions[] = " CUS.MailAddress like :MailAddress ";
            $bindparams += array(':MailAddress' => '%' . BaseGeneralUtils::convertWideToNarrow($params['MailAddress']) . '%');
        }

        // 加盟店顧客番号
        if ($params['EntCustId'] != '') {
            $expressions[] = " CUS.EntCustId like :EntCustId ";
            $bindparams += array(':EntCustId' => '%' . $params['EntCustId'] . '%');
        }

        // 顧客ステータス
        if ($params['custSts'] != '' && $params['custSts'] == 1) {      // ブラック
            $expressions[] = " MC.BlackFlg = 1 ";
        }
        else if ($params['custSts'] != '' && $params['custSts'] == 2) { // 優良
            $expressions[] = " MC.GoodFlg = 1 ";
        }

        // 利用金額
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
            $expressions[] = $wUseAmount;
        }

        // OEMID
        if ($params['Oem'] != '' && $params['Oem'] != 0) {
            $expressions[] = " ENT.OemId = :OemId ";
            $bindparams += array(':OemId' => mb_convert_kana($params['Oem'], 'a', 'UTF-8'));
        }


        // 事業者名
        if ($params['EnterpriseNameKj'] != '') {
            $expressions[] = " ENT.EnterpriseNameKj like :EnterpriseNameKj ";
            $bindparams += array(':EnterpriseNameKj' => '%' . $params['EnterpriseNameKj'] . '%');
        }

        // 事業者ID
        if ($params['LoginId'] != '') {
            $expressions[] = " ENT.LoginId = :LoginId ";
            $bindparams += array(':LoginId' => $params['LoginId']);
        }

        // サイト名
        if ($params['SiteName'] != '') {
            $expressions[] = " SITE.SiteNameKj like :SiteNameKj ";
            $bindparams += array(':SiteNameKj' => '%' . $params['SiteName'] . '%');
        }

        // サイトID
        if ($params['SiteId'] != '') {
            $expressions[] = " SITE.SiteId = :SiteId ";
            $bindparams += array(':SiteId' => $params['SiteId']);
        }

        // 請求代行プラン        
        switch($params['BillingAgentSts'])
        {
            case '0':
                $expressions[] = " ENT.BillingAgentFlg = 0 ";
                break;
            case '2':
                $expressions[] = " ENT.BillingAgentFlg = 1 ";
                break;
            default:
                break;
        }
            
        // 初回支払期限
        $wLimitDate = BaseGeneralUtils::makeWhereDate(
            'CC.F_LimitDate',
            BaseGeneralUtils::convertWideToNarrow($params['LimitDateF']),
            BaseGeneralUtils::convertWideToNarrow($params['LimitDateT'])
        );
        if ($wLimitDate != '') {
            $expressions[] = $wLimitDate;
        }

        // 入金日
        $wReceiptDate = BaseGeneralUtils::makeWhereDate(
            'RC.ReceiptDate',
            BaseGeneralUtils::convertWideToNarrow($params['ReceiptDateF']),
            BaseGeneralUtils::convertWideToNarrow($params['ReceiptDateT'])
        );
        if ($wReceiptDate != '') {
            // 遅れ入金のみ対象
            $expressions[] = ' DATEDIFF(RC.ReceiptDate, CC.F_LimitDate) > 0 ';
            $expressions[] = $wReceiptDate;
        }

        // 入金遅れ日数
        if (CoralValidate::isInt($params['ReceiptPastDaysF']) && CoralValidate::isInt($params['ReceiptPastDaysT'])) {
            $expressions[] = " DATEDIFF(VCR.ReceiptDate, CC.F_LimitDate) BETWEEN :ReceiptPastDaysF AND :ReceiptPastDaysT ";
            $bindparams += array(':ReceiptPastDaysF' => $params['ReceiptPastDaysF']);
            $bindparams += array(':ReceiptPastDaysT' => $params['ReceiptPastDaysT']);
        }
        else if (CoralValidate::isInt($params['ReceiptPastDaysF'])) {
            $expressions[] = " DATEDIFF(VCR.ReceiptDate, CC.F_LimitDate) >= :ReceiptPastDaysF ";
            $bindparams += array(':ReceiptPastDaysF' => $params['ReceiptPastDaysF']);
        }
        else if (CoralValidate::isInt($params['ReceiptPastDaysT'])) {
            $expressions[] = " DATEDIFF(VCR.ReceiptDate, CC.F_LimitDate) <= :ReceiptPastDaysT ";
            $bindparams += array(':ReceiptPastDaysT' => $params['ReceiptPastDaysT']);
        }

        // 超過日数
        if (CoralValidate::isInt($params['PastDaysF']) && CoralValidate::isInt($params['PastDaysT'])) {
            $expressions[] = " DATEDIFF(CURDATE(), CC.F_LimitDate) BETWEEN :PastDaysF AND :PastDaysT ";
            $bindparams += array(':PastDaysF' => $params['PastDaysF']);
            $bindparams += array(':PastDaysT' => $params['PastDaysT']);
        }
        else if (CoralValidate::isInt($params['PastDaysF'])) {
            $expressions[] = " DATEDIFF(CURDATE(), CC.F_LimitDate) >= :PastDaysF ";
            $bindparams += array(':PastDaysF' => $params['PastDaysF']);
                    }
        else if (CoralValidate::isInt($params['PastDaysT'])) {
            $expressions[] = " DATEDIFF(CURDATE(), CC.F_LimitDate) <= :PastDaysT ";
            $bindparams += array(':PastDaysT' => $params['PastDaysT']);
        }

        // 督促分類
        $wRemindClass = $this->makeWhereFromCheckboxes('ORD.RemindClass', 'RemindClass_', $params, $codeMaster->getRemindClassMaster());
        if ($wRemindClass != '') {
            $expressions[] = $wRemindClass;
        }

        // 支払約束日
        $wPromPayDate = BaseGeneralUtils::makeWhereDate(
            'ORD.PromPayDate',
            BaseGeneralUtils::convertWideToNarrow($params['PromPayDateF']),
            BaseGeneralUtils::convertWideToNarrow($params['PromPayDateT'])
        );
        if( $wPromPayDate != '' ) {
            $expressions[] = $wPromPayDate;
        }

        // TEL有効
        $wValidTel = $this->makeWhereFromCheckboxes('CUS.ValidTel', 'ValidTel_', $params, $codeMaster->getValidTelMaster());
        if ($wValidTel != '') {
            $expressions[] = $wValidTel;
        }

        // 住所有効
        $wValidAddress = $this->makeWhereFromCheckboxes('CUS.ValidAddress', 'ValidAddress_', $params, $codeMaster->getValidAddressMaster());
        if ($wValidAddress != '') {
            $expressions[] = $wValidAddress;
        }

        // メール有効
        $wValidMail = $this->makeWhereFromCheckboxes('CUS.ValidMail', 'ValidMail_', $params, $codeMaster->getValidMailMaster());
        if ($wValidTel != '') {
            $expressions[] = $wValidMail;
        }

        // 請求ストップ
        $wClaimStop = '';
        if (isset($params['ClaimStop'])) {
            switch($params['ClaimStop']) {
                case 1:
                    // 全ストップ
                    $wClaimStop = 'ORD.LetterClaimStopFlg = 1 AND ORD.MailClaimStopFlg = 1';
                    break;
                case 2:
                    // 紙ストップ
                    $wClaimStop = 'ORD.LetterClaimStopFlg = 1';
                    break;
                case 3:
                    // メールストップ
                    $wClaimStop = 'ORD.MailClaimStopFlg = 1';
                    break;
                default;
                break;
            }
        }
        if ($wClaimStop != '') {
            $expressions[] = $wClaimStop;
        }

        // 訪問済み
        $wVisitFlg = '';
        if (isset($params['VisitFlg'])) {
            switch($params['VisitFlg']) {
                case 1:
                    // 訪問済み
                    $wVisitFlg = 'ORD.VisitFlg = 1';
                    break;
                case 2:
                    // 未訪問
                    $wVisitFlg = '(ORD.VisitFlg IS NULL OR ORD.VisitFlg = 0)';
                    break;
                default:
                    break;
            }
        }
        if ($wVisitFlg != '') {
            $expressions[] = $wVisitFlg;
        }

        // 架電
        if (isset($params['Called_0']) || isset($params['Called_1'])) {
            if (isset($params['Called_0']) && isset($params['Called_1'])) {
                $expressions[] = ' ORD.Tel30DaysFlg IN (0, 1) ';
            }
            else if (isset($params['Called_0']) && !isset($params['Called_1'])) {
                $expressions[] = ' ORD.Tel30DaysFlg IN (0) ';
            }
            else if (!isset($params['Called_0']) && isset($params['Called_1'])) {
                $expressions[] = ' ORD.Tel30DaysFlg IN (1) ';
            }
        }
        if (isset($params['Called_2']) || isset($params['Called_3'])) {
            if (isset($params['Called_2']) && isset($params['Called_3'])) {
                $expressions[] = ' ORD.Tel90DaysFlg IN (0, 1) ';
            }
            else if (isset($params['Called_2']) && !isset($params['Called_3'])) {
                $expressions[] = ' ORD.Tel90DaysFlg IN (0) ';
            }
            else if (!isset($params['Called_2']) && isset($params['Called_3'])) {
                $expressions[] = ' ORD.Tel90DaysFlg IN (1) ';
            }
        }

        // 最終回収手段
        $wFinalityCollectionMean = $this->makeWhereFromCheckboxes(
            'ORD.FinalityCollectionMean',
            'FinalityCollectionMeanTag_',
            $params, $codeMaster->getFinalityCollectionMeanMaster()
        );
        if ($wFinalityCollectionMean != '') {
            $expressions[] = $wFinalityCollectionMean;
        }

        // 備考
        if ($params['Note'] != '') {
            $expressions[] = " ORD.Incre_Note like :Note ";
            $bindparams += array(':Note' => '%' . $params['Note'] . '%');
        }

        // 基本検索条件確定
        $this->_expressions = $expressions;
        $this->_bindparams = $bindparams;

        // 検索条件のOEMIDを保持（CSV用）
        $this->oem_id = $params['Oem'];

        return $this;
    }

    /**
     * 現在のソートキーリストを取得する
     *
     * @return array ソート順を示す配列。各要素はカラム名を示すkeyと、降順であるかを示すisDescを保持する連想配列となる
     */
    public function getSortKeys() {
        return $this->_sortKeys;
    }

    /**
     * ソートキーリストを初期化する。
     * 初期値は 超過日数（降順）→督促分類→金額（降順）→郵便番号→店舗名になる。
     * このメソッドを実行すると、検索キャッシュはクリアされる
     *
     * @return SearchfCache このインスタンス自身
     */
    public function clearSortKeys() {
        $this->_sortKeys = array(
            array( 'key' => 'PastDays',     'label' => '超過日数',    'isDesc' => true ),
            array( 'key' => 'RemindClass',  'label' => '分類' ),
            array( 'key' => 'UseAmount',    'label' => '金額',        'isDesc' => true ),
            array( 'key' => 'PostalCode',   'label' => '〒' ),
            array( 'key' => 'SiteNameKj',   'label' => '店舗' )
        );

        // 検索結果をクリアする
        $this->_search_results = array();

        return $this;
    }

    /**
     * 指定のソートキーを最優先に設定する。それ以外のキーは現在の順序を保つ。
     * このメソッドを実行すると、検索キャッシュはクリアされる
     *
     * @param string $sortKey 最優先に設定するソートキー
     * @return SearchfCache このインスタンス自身
     */
    public function addSortKey($sortKey) {
        $primaries = array();
        $others = array();
        foreach( $this->_sortKeys as $keyInfo ) {
            if( $keyInfo['key'] == $sortKey ) {
                $primaries[] = $keyInfo;
            } else {
                $others[] = $keyInfo;
            }
        }
        $this->_sortKeys = array_merge( $primaries, $others );

        // 検索結果をクリアする
        $this->_search_results = array();

        return $this;
    }

    /**
     * 現在のフィルタ設定を取得する
     *
     * @return array フィルタ設定を示す配列。各要素はフィルタ対象のカラム名をキー、フィルタ指定値を値とした連想配列である
     */
    public function getFilters() {
        return $this->_filters;
    }
    /**
     * フィルタ設定を指定する。
     * フィルタ設定はFILTER_TARGET_*で定義されるフィルタ定数をキー、フィルタ値を値とした連想配列の配列で指定する。
     * 引数を省略した場合、フィルタ設定はクリアされる。
     * このメソッドを実行すると検索キャッシュはクリアされる
     *
     * @param array|null $filter_names フィルタ設定
     * @return SearchfCache このインスタンス自身
     */
    public function setFilters($filter_names = array()) {
        $filters = array();
        if( isset( $filter_names[self::FILTER_TARGET_MONTH] ) ) {
            $filters[self::FILTER_TARGET_MONTH] = $filter_names[self::FILTER_TARGET_MONTH];
        }
        if( isset( $filter_names[self::FILTER_TARGET_REMIND_CLASS] ) ) {
            $filters[self::FILTER_TARGET_REMIND_CLASS] = $filter_names[self::FILTER_TARGET_REMIND_CLASS];
        }
        $this->_filters = $filters;

        // 検索結果をクリアする
        $this->_search_results = array();

        return $this;
    }
    /**
     * 現在のフィルタ設定をクリアする。
     * このメソッドを実行すると検索キャッシュはクリアされる
     *
     * @return SearchfCache このインスタンス自身
     */
    public function clearFilters() {
        return $this->setFilters();
    }

    /**
     * 支払意思の有無による追加検索条件を取得する
     *
     * @return string
     */
    public function getRedoExression() {
        return $this->_redo_expression;
    }
    /**
     * 支払意思の有無による追加検索条件を設定する。
     * 設定可能な値はこのクラスのREDO_EXPRESSIONS_*で定義される定数値のみである。
     * このメソッドを実行すると検索キャッシュはクリアされる
     *
     * @param string|null $expression 追加条件指定文字列
     * @return SearchfCache このインスタンス自身
     */
    public function setRedoExression($expression = self::REDO_EXPRESSIONS_ALL) {
        if( in_array( $expression, array( self::REDO_EXPRESSIONS_ALL, self::REDO_EXPRESSIONS_INCLUDE, self::REDO_EXPRESSIONS_EXCLUDE ) ) ) {
            $this->_redo_expression = $expression;
        }

        // 検索結果をクリアする
        $this->_search_results = array();

        return $this;
    }
    /**
     * 支払意思の有無による追加検索条件をクリアする。
     * このメソッドを実行すると検索キャッシュはクリアされる
     *
     * @return SearchfCache このインスタンス自身
     */
    public function clearRedoExpression() {
        return $this->setRedoExression();
    }

    /**
     * 指定の検索結果レコードを結果一覧ページ向けに加工する
     *
     * @param array $row 検索結果の1レコード分のデータ
     * @return array $rowを一覧表示向けに加工したデータ
     */
    public function applyViewData($row, $forListing = true) {
        $master = new CoralCodeMaster($this->_db);

        $row['Incre_ArAddr'] = $master->getCreditClassShortCaption($row['Incre_ArAddr']);
        $row['Incre_ArTel'] = $master->getCreditClassShortCaption($row['Incre_ArTel']);
        $row['eDen'] = $master->getEDenCaption($row['eDen']);
        $row['PhoneHistory'] = $master->getPhoneHistoryCaption($row['PhoneHistory']);

        $row['Incre_DecisionOpId'] = self::_getOperatorName( $row['Incre_DecisionOpId'] );

        switch($row['RealSendMailResult'])
        {
            case 1:
                $row['RealSendMailResult'] = 'OK';
                break;
            case 2:
                $row['RealSendMailResult'] = 'NG';
                break;
            default:
                $row['RealSendMailResult'] = '';
                break;
        }

        if ($row['Bekkan'] == '' || $row['Bekkan'] == '0')
        {
            $row['Bekkan'] = '通常';
        }
        else
        {
            $row['Bekkan'] = '別管';
        }

        /* ---------------------------------------------------------------- 長い文字列の省略はCSSのスタイル指定で対応するので加工を廃止（2015.3.6 eda）
        //if (mb_strlen($row['NameKj']) >= 12 && $forListing)
        //{
        //    $row['NameKj'] = mb_substr($row["NameKj"], 0, 12, 'UTF-8') . '...';
        //}

        //if (mb_strlen($row['SiteNameKj']) >= 8 && $forListing)
        //{
        //    $row['SiteNameKj'] = mb_substr($row["SiteNameKj"], 0, 8, 'UTF-8') . '...';
        //}

        //if ($forListing)
        //{
        //    $row['UnitingAddress'] = mb_substr($row["UnitingAddress"], 0, 8, 'UTF-8') . '...';
        //}
        ------------------------------------------------------------------ */

        $row['RemindClass'] = $master->getRemindClassCaption($row['RemindClass']);
        $row['TouchHistoryFlg'] = $row['TouchHistoryFlg'] == 1 ? '有' : '';
        $row['ValidTel'] = $master->getValidTelCaption($row['ValidTel']);
        $row['ValidAddress'] = $master->getValidAddressCaption($row['ValidAddress']);
        $row['ValidMail'] = $master->getValidMailCaption($row['ValidMail']);
        $row['VisitFlg'] = $row['VisitFlg'] == 1 ? '済' : '';
        $row['ResidentCard'] = $master->getResidentCardCaption($row['ResidentCard']);

        // 入金方法
        if (! empty ( $row ['ReceiptClass'] )) {
            // コードマスターから入金方法のコメントを取得
            $mdlc = new TableCode($this->_db);
            $ReceiptMethod = $mdlc->find ( 198, $row ['ReceiptClass'] )->current ();
            $row['ReceiptClass'] = $ReceiptMethod['KeyContent'];
        }

        return $row;
    }

    /**
     * インスタンスの内容を示す文字列を取得する
     *
     * @return string
     */
    public function __toString() {
        return $this->_buildQuery();
    }

    /**
     * 現在の検索条件で検索を実行し、結果をキャッシュする
     *
     * @access protected
     */
    protected function _execute() {

        Application::getInstance()->logger->debug( '[SearchfCache#_execute] query executing !!' );
        // サマリ作成済みフラグがfalseの場合はサマリを初期化
        // ※：フラグ設定は検索実施後
        if( ! $this->_has_summaries ) {
            $this->initSummaries();
            Application::getInstance()->logger->debug( '[SearchfCache#_execute] summaries initialized.' );
        }

        // 実行するクエリを構築
        $query = $this->_buildQuery();
        $ents = $this->_db->query($query)->execute($this->_bindparams);

        $results = array();
        foreach( $ents as $row ) {
        // 取得した行をそのままキャッシュに追加
            $results[] = $row;

            // サマリ集計不要
            if( $this->_has_summaries ) continue;

            // 集計
            $this->_summaries[ self::SUMMARY_KEY_TOTALCOUNT ]++;
            $this->_summaries[ self::SUMMARY_KEY_TOTALUSEAMOUNT ] += $row['P_UseAmount'];
            $this->_summaries[ self::SUMMARY_KEY_TOTALRECEIPTAMOUNT ] += $row['CalcReceitAmount'];
            if((nvl($row['Cnl_Status'], 0) != 0) || ($row['DataStatus'] == 91 && $row['CloseReason'] == 4)) {
                $this->_summaries[ self::SUMMARY_KEY_TOTALNORECEIPTAMOUNT ] += 0;
            } elseif($row['ClaimAmount'] <= 0) {
                $this->_summaries[ self::SUMMARY_KEY_TOTALNORECEIPTAMOUNT ] += 0;
            } else {
                $this->_summaries[ self::SUMMARY_KEY_TOTALNORECEIPTAMOUNT ] += ($row['P_UseAmount'] - $row['CalcReceitAmount']);
            }
            $this->_summaries[ self::SUMMARY_KEY_TOTALAMOUNT ] += $row['TotalAmount'];
        }

        // 結果確定
        $this->_search_results = $results;
    }

    /**
     * 現在の指定条件すべてを満たすクエリを構築する
     *
     * @access protected
     * @return string
     */
    protected function _buildQuery() {

        // ベースクエリ＋初期検索条件
        $queries = array_merge(
            array( self::BASE_QUERY ),
            $this->getExpressions()
        );

        // 再検索条件指定
        switch( $this->_redo_expression ) {
            case self::REDO_EXPRESSIONS_EXCLUDE:
                // 支払意思あり除く
                $queries[] = "(ORD.PromPayDate IS NULL OR ORD.PromPayDate = '0000-00-00')";
                break;
            case self::REDO_EXPRESSIONS_INCLUDE:
                // 支払意思ありのみ
                $queries[] = "ORD.PromPayDate IS NOT NULL AND ORD.PromPayDate != '0000-00-00'";
                break;
            // REDO_EXPRESSIONS_ALLまたは未指定の場合はなにも条件を加えない
        }

        // 絞込み設定
        foreach( $this->_filters as $key => $value ) {
            // 値が空の場合は処理しない
            if( $value === '' || $value === null ) continue;

            switch( $key ) {
                case self::FILTER_TARGET_MONTH:
                    // 初回支払期限。パラメータが属する月の月初～月末が検索範囲
                    $date = date_parse_from_format( "Y-m-d", $value );
                    if( checkdate($date['month'], $date['day'], $date['year']) ) {
                        $start_date = date( 'Y-m-01', strtotime($value) );
                        $end_date = date( 'Y-m-t', strtotime($value) );

                        $queries[] = BaseGeneralUtils::makeWhereDate( 'CC.F_LimitDate', $start_date, $end_date );
                    }
                    break;
                case self::FILTER_TARGET_REMIND_CLASS:
                    // 督促分類。初期検索条件と違い単独値指定
                    if(is_numeric($value)) {
                    $queries[] = $value == 0 ?
                        '(ORD.RemindClass = ' . $value . ' OR ORD.RemindClass IS NULL)':
                        'ORD.RemindClass = ' . $value;
                    }
                    break;
            }
        }

        // 条件指定済みのクエリを構築
        $query = join( " AND ", $queries );

        // ソートキーの追加
        $keys = array();
        foreach( $this->_sortKeys as $keyInfo ) {
            $keys[] = $keyInfo['key'] . ( $keyInfo['isDesc'] ? ' DESC' : '' );
        }
        $query = $query . "\nORDER BY ". join(',', $keys);

        return $query;
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
     * 現在の検索条件でCSVデータを作成する
     *
     * @access public
     * @return array CSVデータ
     */
    public function getCsv() {

        Application::getInstance()->logger->debug( '[SearchfCache#_execute] csv executing !!' );

        $mdlCh = new TableClaimHistory($this->_db); // 再請求情報
        $mdlOi = new TableOrderItems($this->_db);   // 商品情報

        // 対象データ取得　キャッシュがあればそれが使用される
        $search_query = $this->getResults();

        // CSV用に変換
        $results = array();
        foreach( $search_query as $row ) {
            // 取得したデータをCSVに整形
            $rec = array();
            $row = $this->applyViewData($row, false);

            // 注文ID
            $rec['OrderId'] = $row['OrderId'];

            // 任意注文番号
            $rec['Ent_OrderId'] = $row['Ent_OrderId'];

            // OEM先任意番号
            $rec['Oem_OrderId'] = $row['Oem_OrderId'];

            // 注文日
            $rec['ReceiptOrderDate'] = $row['ReceiptOrderDate'];

            // OEM先名
            $rec['OemNameKj'] = $row['OemNameKj'];

            // サイト名
            $rec['SiteNameKj'] = $row['SiteNameKj'];

            // 加盟店名
            $rec['EnterpriseNameKj'] = $row['EnterpriseNameKj'];

            // 備考
            $rec['Incre_Note'] = $row['Incre_Note'];

            // OEM先備考
            $rec['Oem_Note'] = $row['Oem_Note'];

            // 注文者名
            $rec['NameKj'] = $row['NameKj'];

            // 注文者カナ
            $rec['NameKn'] = $row['NameKn'];

            // 注文者TEL
            $rec['Phone'] = $row['Phone'];

            // 注文者メアド
            $rec['MailAddress'] = $row['MailAddress'];

            // 注文者郵便番号
            $rec['PostalCode'] = $row['PostalCode'];

            // 注文者住所
            $rec['UnitingAddress'] = $row['UnitingAddress'];

            // 督促メール日
            $rec['MailDate'] = $row['MailLimitPassageDate'];

            // 配送先氏名
            $rec['DestNameKj'] = $row['DestNameKj'];

            // 配送先カナ
            $rec['DestNameKn'] = $row['DestNameKn'];

            // 配送先TEL
            $rec['DestPhone'] = $row['DestPhone'];

            // 配送先郵便番号
            $rec['DestPostalCode'] = $row['DestPostalCode'];

            // 配送先住所
            $rec['DestUnitingAddress'] = $row['DestUnitingAddress'];

            // 伝票登録日
            $rec['Deli_JournalIncDate'] = $row['Deli_JournalIncDate'];

            // 運送会社
            $rec['DeliMethodName'] = $row['DeliMethodName'];

            // 伝票番号
            $rec['Deli_JournalNumber'] = $row['Deli_JournalNumber'];

            // 着荷確認日
            $rec['Deli_ConfirmArrivalDate'] = $row['Deli_ConfirmArrivalDate'];

            // 立替予定日
            $rec['ExecScheduleDate'] = $row['ExecScheduleDate'];

            // 初回請求日
            $rec['F_ClaimDate'] = $row['F_ClaimDate'];

            // 初回支払期限
            $rec['F_LimitDate'] = $row['F_LimitDate'];

            // E電話結果
            $rec['eDen'] = $row['eDen'];

            // ドッグベル
            $rec['ValidTel'] = $row['ValidTel'];

            // メール有効
            $rec['ValidMail'] = $row['ValidMail'];

            // 与信担当者
            $rec['Incre_DecisionOpNameKj'] = $row['Incre_DecisionOpId'];

            // 社内与信スコア
            $rec['Incre_ScoreTotal'] = $row['Incre_ScoreTotal'];

            // 審査システムスコア
            // 与信審査結果から取得する
            $cRes = new TableCjResult($this->_db);
            $cResRec = $cRes->findByOrderSeq($row['OrderSeq'])->current();
            $rec['TotalScore'] = $cResRec['TotalScore'];

            // 営業担当者
            $rec['Salesman'] = $row['Salesman'];

            // 再請求の情報
            // 請求履歴から取得する
            // 再請求日の取得 1回目～6回目
            $cHis = ResultInterfaceToArray($mdlCh->getReClaimHistory($row['OrderSeq']));
            for($i = 0; $i < 6; $i++) {
                $rec["ClaimDate" . ($i + 1)] = isset( $cHis[$i] ) ? $cHis[$i]['ClaimDate'] : '';
            }

            // 再請求額の取得 1回目～6回目
            $latestAmount = 0;
            for($i = 0; $i < 6; $i++) {
                if( isset($cHis[$i]) ) {
                    // データがある場合
                    // → 前回の請求額との差額をその回の再請求額とみなす
                    $rec["ClaimAmount" . ($i + 1)] = $cHis[$i]['ClaimFee'] - $latestAmount;
                    $latestAmount = $cHis[$i]['ClaimFee'];
                } else {
                    // データがない場合
                    $rec["ClaimAmount" . ($i + 1)] = '';
                }
            }

            // 入金日
            $rec['ReceiptProcessDate'] = $row['ReceiptProcessDate'];

            // 入金額
            $rec['ReceiptAmount'] = $row['ReceiptAmount'];

            // 入金形態
            $rec['ReceiptClass'] = $row['ReceiptClass'];

            // 住所与信クラス
            $rec['Incre_ArAddr'] = $row['Incre_ArAddr'];

            // TEL与信クラス
            $rec['Incre_ArTel'] = $row['Incre_ArTel'];

            // キャンセル情報
            // キャンセル管理から取得する
            $can = new TableCancel($this->_db);
            $canRec = $can->findCancel(array('ValidFlg' => 1, 'OrderSeq' => $row['OrderSeq']))->current();
            // キャンセル日
            $rec['CancelDate'] = $canRec['CancelDate'];
            // キャンセル状態
            $rec['ApproveFlg'] = $canRec['ApproveFlg'];

            // 商品情報の取得
            // 注文商品から取得する
            // 商品情報取得の下準備
            $oi = ResultInterfaceToArray($mdlOi->findByP_OrderSeq($row['OrderSeq']));
            $items = array();       // 商品データ用配列
            $deliFee = 0;           // 送料
            $settlementFee = 0;     // 決済手数料
            $totalSumMoney = 0;     // 利用額

            // 商品情報をループして送料・手数料・利用額と商品データに分離
            foreach( $oi as $itemRow ) {
                switch( $itemRow['DataClass'] ) {
                    case 2:
                        // 送料
                        $deliFee += $itemRow['SumMoney'];
                        break;
                    case 3:
                        // 手数料
                        $settlementFee += $itemRow['SumMoney'];
                        break;
                    case 4:
                        // 税額
                        break;
                    default:
                        // 商品
                        $items[] = $itemRow;
                }

                // 利用額
                $totalSumMoney +=$itemRow['SumMoney'];
            }

            // 送料・
            $rec['DeliveryFee'] = $deliFee;

            // 決済手数料
            $rec['SettlementFee'] = $settlementFee;

            // 利用額
            $rec['TotalSumMoney'] = $totalSumMoney;

            // 商品データ 商品１～商品３０
            for($i = 0; $i < 30; $i++) {
                $idx = sprintf("%02d", ($i + 1));
                if( isset( $items[$i] ) ) {
                    // 名前
                    $rec["ItemNameKj" . $idx] = $items[$i]['ItemNameKj'];

                    // 単価
                    $rec["UnitPrice" . $idx] = $items[$i]['UnitPrice'];

                    // 数量
                    $rec["ItemNum" . $idx] = $items[$i]['ItemNum'];
                } else {
                    // 名前
                    $rec["ItemNameKj" . $idx] = '';

                    // 単価
                    $rec["UnitPrice" . $idx] = '';

                    // 数量
                    $rec["ItemNum" . $idx] = '';
                                    }
            }

            $results[] = $rec;
        }

        // 結果確定
        return $results;
    }

    /**
     * 現在の検索条件でエクスポートデータを作成する
     *
     * @access public
     * @return array エクスポートデータ
     */
    public function getExport() {

        Application::getInstance()->logger->debug( '[SearchfCache#_execute] export executing !!' );

        $mdlCh = new TableClaimHistory($this->_db); // 再請求情報
        $mdlOi = new TableOrderItems($this->_db);   // 商品情報

        // 対象データ取得　キャッシュがあればそれが使用される
        $search_query = $this->getResults();

        $aryOseqs = array();
        foreach( $search_query as $row ) {
            $aryOseqs[] = $row['OrderSeq'];
        }
        $phraseIn = '0';
        // count関数対策
        if (!empty($aryOseqs)) {
            $phraseIn = implode(',', $aryOseqs);
        }
        $sql = <<<EOQ
SELECT (SELECT OrderId FROM T_Order WHERE OrderSeq = o.P_OrderSeq) AS OrderId
,      c.SearchPhone
FROM   T_Order o
       INNER JOIN T_Customer c ON (c.OrderSeq = o.OrderSeq)
WHERE  o.P_OrderSeq IN ($phraseIn)
GROUP BY o.P_OrderSeq, c.SearchPhone
EOQ;
        $ri = $this->_db->query($sql)->execute(null);

        // CSV用に変換
        $results = array();
        foreach( $ri as $row ) {
            // 取得したデータをCSVに整形
            $rec = array();

            // 発信先電話番号１
            $rec['Phone1'] = $row['SearchPhone'];

            // 発信先電話番号２
            $rec['Phone2'] = $row['SearchPhone'];

            // 発信先電話番号３
            $rec['Phone3'] = $row['SearchPhone'];

            // 付加情報
            $rec['AddInfo'] = $row['OrderId'];

            $results[] = $rec;
        }

        // 結果確定
        return $results;
    }

    /**
     * 画面の検索条件で督促データを作成する
     *
     * @access public
     * @return array 督促データ
     */
    public function getRemind() {

        // 対象データ取得　キャッシュがあればそれが使用される
        $search_query = $this->getResults();

        // CSV用に変換
        $results = array();
        foreach( $search_query as $row ) {
            // 取得したデータをCSVに整形
            $rec = array();

            // 携帯電話番号(P_CUS.Phone)
            $rec['PhoneNumber'] = $row['Phone'];

            // キャリアID(空欄)
            $rec['CaririerId'] = '';

            // メッセージ番号(1固定)
            $rec['MessageNumber'] = '1';

            // メッセージ(空白固定)
            $rec['Message'] = '';

            // 登録日(出力当日の日付)
            $rec['ReferenceDate'] = date('Y-m-d');

            $results[] = $rec;
        }

        // 結果確定
        return $results;
    }
}
