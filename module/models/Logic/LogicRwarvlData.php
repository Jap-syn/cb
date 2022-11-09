<?php
namespace models\Logic;

use Zend\Db\Adapter\Adapter;
use models\Logic\Haipon\LogicHaiponImporter;
use models\Logic\Haipon\LogicHaiponExporter;
use models\Logic\RwarvlData\LogicRwarvlDataExporter;
use models\Logic\RwarvlData\LogicRwarvlDataImporter;

/**
 * 着荷確認データ連携ロジッククラス
 */
class LogicRwarvlData {

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
     * @var LogicRwarvlDataImporter
     */
    protected $_importer;

    /**
     * エクスポートエンジン
     *
     * @access protected
     * @var LogicRwarvlDataExporter
     */
    protected $_exporter;

    /**
     * データベースアダプタを指定して、LogicRwarvlDataの新しいインスタンスを初期化する
     *
     * @param Adapter
     */
    public function __construct(Adapter $db) {
        $this->setAdapter($db);
        $this->_importer = new LogicRwarvlDataImporter($this);
        $this->_exporter = new LogicRwarvlDataExporter($this);
    }

    /**
     * データベースアダプタを取得する
     *
     * @return Adapter
     */
    public function getAdapter() {
        return $this->_db;
    }
    /**
     * データベースアダプタを設定する
     *
     * @param Adapter
     * @return LogicRwarvlData
     */
    public function setAdapter(Adapter $db) {
        $this->_db = $db;
        return $this;
    }

    /**
     * 指定パスの着荷確認データ出力ファイルを読み込み、インポートデータを構築する
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
        return $this->_importer->import($import_data, date('Y-m-d H:i:s'), $opId);
    }

    /**
     * 対象の配送方法と伝票登録日の上限を指定して、エクスポートデータを生成する
     *
     * @param array $deli_methods 配送方法IDの配列
     * @return array エクスポート用データ
     */
    public function createExportData() {
        // エクスポートエンジンに処理を委譲
        return $this->_exporter->getExportList();
    }

    /**
     * エクスポートデータを指定のフォーマットで出力する
     *
     * @param array $export_data エクスポート用データ
     * @param null | string $format フォーマット指定。省略時はCSV形式
     */
    public function execExport(array $export_data) {
        // エクスポートエンジンに処理を委譲
        $this->_exporter->export($export_data);
    }
}
