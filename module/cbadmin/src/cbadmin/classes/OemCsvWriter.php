<?php
namespace cbadmin\classes;

use cbadmin\Application;
use cbadmin\classes\OemCsvSettings;
use Coral\Base\IO\BaseIOCsvWriter;

/**
 * 事業者情報のCSVダウンロード向け{@link BaseIOCsvWriter}派生クラス。
 * {@link OemCsvSettings}による変換設定で事業者テーブルのデータを変換出力する
 */
class OemCsvWriter extends BaseIOCsvWriter {
    /**
     * 変換設定
     * @access protected
     * @var OemCsvSettings
     */
    protected $_settings;

    /**
     * ヘッダ行出力済みフラグ
     * @var boolean
     */
    protected $_headerWrited;

    /**
     * 変換設定を指定して、{@link OemCsvWriter}の新しいインスタンスを
     * 初期化する。
     * カラムヘッダの設定や列数、値の変換方法などはすべて$settingsで定義する
     * @param OemCsvSettings $settings 変換設定を定義する{@link OemCsvSettings}
     * @param null|array $data 変換対象データ
     */
    public function __construct(OemCsvSettings $settings, $data = array()) {
        $this->_settings = $settings;
        parent::__construct( array(
            BaseIOCsvWriter::PARAMS_COLUMN_HEADER => $this->_settings->getTitles(),
            BaseIOCsvWriter::PARAMS_ROWS => $data
        ) );
    }

    /**
     * オーバーライド。現在の設定でCSV出力を実行する
     * @param string $fileName 出力ファイル名
     * @param null|Zend_Controller_Response_Http $response 出力オブジェクト。nullまたはZend_Controller_Response_Abstractを指定できる
     * @return BaseIOCsvWriter
     */
    public function write($fileName, $response = null) {
        // ヘッダ出力済みフラグを初期化
        $this->_headerWrited = false;

        // 処理そのものは親クラスに委譲
        return parent::write($fileName, $response);
    }

    /**
     * オーバーライド。引数の単一行相当の配列を現在の設定でCSVエンコードする。
     * ヘッダ行以外は内部の{@link OemCsvSettings}の設定に従い変換出力される
     *
     * @param array $row
     * @return string
     */
    protected function encodeRow($row) {
        // 未出力の場合のみヘッダ行判断を行う
        if( ! $this->_headerWrited ) {
            // ヘッダ行はオリジナルのencodeRowを使用
            if( $row == $this->_header ) return parent::encodeRow($row);

            // ヘッダ行判断は最初の1回のみ
            $this->_headerWrited = true;
Application::getInstance()->logger->debug( '[OemCsvWriter::encodeRow] header judged.' );
        }

        $newRow = array();
        foreach($this->_settings->getFields() as $key) {
            $converter = $this->_settings->getConverter($key);
            $newRow[] = $converter->convert($row[$key]);
        }
        return parent::encodeRow($newRow);
    }
}
