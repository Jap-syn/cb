<?php
namespace models\Logic;

use Zend\Db\Adapter\Adapter;
use models\Logic\Haipon\LogicHaiponExporter;
use models\Logic\Haipon\LogicHaiponImporter;
use models\Logic\Haipon\Exporter\LogicHaiponExporterFormatter;

/**
 * 配送でポン連携ロジッククラス
 */
class LogicHaipon {
    /**
     * 配送方法種別定数：ヤマト運輸
     * @var string
     */
    const DELI_METHODS_YAMATO = 'yamato';
    /**
     * 配送方法種別ラベル定数：ヤマト運輸
     * @var string
     */
    const DELI_METHODS_LABEL_YAMATO = 'ヤマト運輸';
    /**
     * 配送方法種別向けURL一致パターン定数：ヤマト運輸
     * @var string
     */
    const URL_PTN_YAMATO = '%toi.kuronekoyamato.co.jp%';

    /**
     * 配送方法種別定数：佐川急便
     * @var string
     */
    const DELI_METHODS_SAGAWA = 'sagawa';
    /**
     * 配送方法種別ラベル定数：佐川急便
     * @var string
     */
    const DELI_METHODS_LABEL_SAGAWA = '佐川急便';
    /**
     * 配送方法種別向けURL一致パターン定数：佐川急便
     * @var string
     */
    const URL_PTN_SAGAWA = '%sagawa-exp.co.jp%';

    /**
     * 配送方法種別定数：日本郵政
     * @var string
     */
    const DELI_METHODS_YUBIN = 'yubin';
    /**
     * 配送方法種別ラベル定数：日本郵政
     * @var string
     */
    const DELI_METHODS_LABEL_YUBIN = '日本郵政';
    /**
     * 配送方法種別向けURL一致パターン定数：日本郵政
     * @var string
     */
    const URL_PTN_YUBIN = '%trackings.post.japanpost.jp%';

    /**
     * 配送方法種別向けURL一致パターン定数：種別指定不正
     * @var string
     */
    const URL_PTN_INVALID = 'invalid url pattern';

    /**
     * 配送方法マスターを配送方法ごとにグルーピングしたデータのキャッシュ
     *
     * @static
     * @access protected
     * @var array
     */
    protected static $__deliMethodsCache = array();

    /**
     * 取扱い可能な配送方法種別の一覧を取得する
     *
     * @static
     * @return array
     */
    public function getDeliMethodKinds() {
        return array(
            self::DELI_METHODS_YAMATO => self::DELI_METHODS_LABEL_YAMATO,
            self::DELI_METHODS_SAGAWA => self::DELI_METHODS_LABEL_SAGAWA,
            self::DELI_METHODS_YUBIN => self::DELI_METHODS_LABEL_YUBIN
        );
    }

    /**
     * 指定種別の配送方法を抽出するためのURL一致パターンを取得する
     *
     * @static
     * @param string $kind 配送方法種別
     * @return string URL一致パターン（LIKE演算子用）
     */
    public static function getUrlPatternByDeliKind($kind) {
        switch($kind) {
            case self::DELI_METHODS_YAMATO:
                return self::URL_PTN_YAMATO;
            case self::DELI_METHODS_SAGAWA:
                return self::URL_PTN_SAGAWA;
            case self::DELI_METHODS_YUBIN:
                return self::URL_PTN_YUBIN;
            default:
                return self::URL_PTN_INVALID;
        }
    }

    /**
     * データベースアダプタ
     *
     * @access protected
     * @var Adapter
     */
    protected $_db;

    /**
     * インポートエンジン
     *
     * @access protected
     * @var LogicHaiponImporter
     */
    protected $_importer;

    /**
     * エクスポートエンジン
     *
     * @access protected
     * @var LogicHaiponExporter
     */
    protected $_exporter;

    /**
     * データベースアダプタを指定して、LogicHaiponの新しいインスタンスを初期化する
     *
     * @param Adapter
     */
    public function __construct(Adapter $db) {
        $this->setAdapter($db);
        $this->_importer = new LogicHaiponImporter($this);
        $this->_exporter = new LogicHaiponExporter($this);
    }

    /**
     * データベースアダプタを取得する
     *
     * @return Zend_Db_Adapter_Abstract
     */
    public function getAdapter() {
        return $this->_db;
    }
    /**
     * データベースアダプタを設定する
     *
     * @param Adapter
     * @return LogicHaipon
     */
    public function setAdapter(Adapter $db) {
        $this->_db = $db;
        return $this;
    }

    /**
     * 指定種別の配送方法マスターをすべて取得する。
     * 立替確定方法が「顧客入金」の配送方法は常にこのメソッドの結果から除外される
     * @param string $kind 配送方法種別
     * @return array 指定種別に対応するM_DeliveryMethodのリスト
     */
    public function getDeliMethodsByKind($kind = self::DELI_METHODS_YAMATO) {
        $map = self::getDeliMethodKinds();
        $kinds = array_keys($map);
        if(!in_array($kind, $kinds)) {
            $kind = self::DELI_METHODS_YAMATO;
        }

        if(!isset(self::$__deliMethodsCache[$kind])) {
            $db = $this->getAdapter();
            $url_ptn = self::getUrlPatternByDeliKind($kind);
            $q = <<<EOQ
SELECT * FROM M_DeliveryMethod
WHERE
    ValidFlg = 1 AND PayChgCondition = 1 AND ArrivalConfirmUrl LIKE :url_ptn
 ORDER BY DeliMethodId
EOQ;

            $stm = $this->_db->query( $q );
            $prm = array(
                ':url_ptn' => $url_ptn,
            );
            $rows = ResultInterfaceToArray( $stm->execute( $prm ) );
//             foreach($db->fetchAll(sprintf($q, $url_ptn)) as $row) {
            foreach( $rows as $row ) {
                $results[$row['DeliMethodId']] = $row;
            }

            self::$__deliMethodsCache[$kind] = array(
                'label' => $map[$kind],
                'methods' => $results
            );
        }

        return self::$__deliMethodsCache[$kind];
    }

    /**
     * 指定種別の配送方法IDリストを取得する
     *
     * @param string $kind 配送方法種別
     * @return array 指定種別に対応する配送方法IDのリスト
     */
    public function getDeliMethodIdsByKind($kind = self::DELI_METHODS_YAMATO) {
        $details = $this->getDeliMethodsByKind($kind);
        return array_keys($details['methods']);
    }

    /**
     * 指定パスの配送でポン出力ファイルを読み込み、インポートデータを構築する
     * 戻り値のデータは連想配列でキー'valid'、'invalid'を持ち、それぞれが以下の構成の連想配列を要素とする
     * 配列が割り当てられる。
     *   is_valid: インポート適合かを示すbool値
     *   invalid_reason: is_validがfalseの場合の事由テキスト
     *   line_number: 元ファイルの対応する行番号
     *   journal_number: 伝票番号
     *   status: 配ポンで確認した結果の状態メッセージ
     *   delived: 着荷済みかを示すbool値
     *   orders: 伝票番号に一致する、着荷確認可能な注文データの配列。各要素は以下のデータを持つ連想配列
     *     OrderSeq: 注文SEQ
     *     OrderId: 注文ID
     *     NameKj: 請求先氏名
     *     DestNameKj: 配送先氏名
     *     Deli_JournalNumber: 伝票番号
     *     Deli_JournalIncDate: 伝票登録日
     *     Deli_DeliMethodId: 配送方法ID
     *     Deli_DeliMethodName: 配送方法名称
     *     ClearConditionForCharge: 立替条件クリア済みフラグ
     *   なお、is_validがtrueに設定された要素のordersは常に長さ1の配列となる
     *
     * @param string $file インポートファイルパス
     * @return array インポート処理用データ
     */
    public function createImportData($file) {
        return $this->_importer->parse($file);
    }

    /**
     * 指定のインポートデータを取り込み、着荷確認処理を実行する
     *
     * @param array $import_data インポート処理用データ。createImportDataで取得した連想配列のキー'valid'に格納されている配列を渡す
     * @return int インポート成功件数
     */
    public function execImport(array $import_data, $opId) {
        return $this->_importer->import($import_data, date('Y-m-d'), $opId);
    }

    /**
     * 対象の配送方法と伝票登録日の上限を指定して、エクスポートデータを生成する
     *
     * @param array $deli_methods 配送方法IDの配列
     * @param null | string | date $limit_date 伝票登録日。この日以前に伝票登録されたものが対象となる
     * @return array エクスポート用データ
     */
    public function createExportData(array $deli_methods, $limit_date = null) {
        // エクスポートエンジンに処理を委譲
        return $this->_exporter->getExportList($deli_methods, $limit_date);
    }

    /**
     * エクスポートデータを指定のフォーマットで出力する
     *
     * @param array $export_data エクスポート用データ
     * @param null | string $format フォーマット指定。省略時はCSV形式
     */
    public function execExport(array $export_data, $format = LogicHaiponExporterFormatter::FORMAT_CSV) {
        // エクスポートエンジンに処理を委譲
        $this->_exporter->export($export_data, $format);
    }
}
