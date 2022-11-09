<?php
namespace models\Logic\RwarvlData\Exporter\Formatter;

use Coral\Base\IO\BaseIOUtility;

/**
 * 配送でポン連携用ファイルの基本抽象フォーマッタ
 * @abstract
 */
abstract class LogicRwarvlDataExporterFormatterAbstract implements LogicRwarvlDataExporterFormatterInterface {

    /**
     * 改行文字定数：CR
     * @var string
     */
    const LDELIM_CR = "\r";

    /**
     * 改行文字定数：LF
     * @var string
     */
    const LDELIM_LF = "\n";

    /**
     * 改行文字定数：CR + LF
     * @var string
     */
    const LDELIM_CRLF = "\r\n";

    /**
     * 文字エンコーディング定数：CP932
     * @var string
     */
    const ENCODING_SJIS = BaseIOUtility::ENCODING_WIN_SJIS;

    /**
     * 文字エンコーディング定数：UTF-8
     * @var string
     */
    const ENCODING_UTF8 = BaseIOUtility::ENCODING_UTF8;

    /**
     * 文字エンコーディング定数：EUC-JP
     * @var string
     */
    const ENCODING_EUC = BaseIOUtility::ENCODING_WIN_EUC_JP;

    /**
     * 連続出力される行ブロックサイズのデフォルト値
     * @var int
     */
    const DEFAULT_BLOCK_SIZE = 1000;

    /**
     * 連続出力される行ブロックサイズの設定可能最大値
     * @var int
     */
    const MAX_BLOCK_SIZE = 2000;

    /**
     * 行ブロックサイズ設定
     *
     * @static
     * @access protected
     * @var int
     */
    protected static $__block_size = self::DEFAULT_BLOCK_SIZE;

    /**
     * 行ブロックサイズの設定を取得する
     *
     * @static
     * @return int
     */
    public static function getBlockSize() {
        return self::$__block_size;
    }
    /**
     * 行ブロックサイズを設定する。
     *
     * @static
     * @param int $size 行ブロックサイズ。1以下は規定値が採用され、最大値を超えた場合は最大値に丸められる
     */
    public static function setBlockSize($size) {
        $size = (int)$size;
        if($size < 1) $size = self::DEFAULT_BLOCK_SIZE;
        if($size > self::MAX_BLOCK_SIZE) $size = self::MAX_BLOCK_SIZE;
        self::$__block_size = $size;
    }

    /**
     * 現在の文字エンコーディング
     * @access protected
     * @var string
     */
    protected $_encoding;

    /**
     * 現在の改行文字
     * @access protected
     * @var string
     */
    protected $_lineDelimiter;

    /**
     * LogicRwarvlDataExporterFormatterAbstractの新しいインスタンスを
     * 初期化する
     */
    public function __construct() {
        $this->setEncoding($this->getDefaultEncoding());
        $this->setLineDelimiter($this->getDefaultLineDelimiter());
        $this->_init();
    }

    protected function _init() {
        // nop
    }

    /**
     * 現在の文字エンコーディング設定を取得する
     *
     * @return string 現在の文字エンコーディング
     */
    public function getEncoding() {
        return $this->_encoding;
    }
    /**
     * 文字エンコーディング設定を変更する
     *
     * @param string | null $encoding 変更する文字エンコーディング。省略時は規定値が採用される
     * @return LogicRwarvlDataExporterFormatterAbstract このインスタンス
     */
    public function setEncoding($encoding = null) {
        if($encoding == null) $encoding = $this->getDefaultEncoding();
        $this->_encoding = $encoding;
        return $this;
    }
    /**
     * 規定の文字エンコーディング設定を取得する
     * このクラスでは常にUTF-8を返す。
     * 派生クラスで必要に応じてオーバーライド可能
     *
     * @return string 規定の文字エンコーディング
     */
    public function getDefaultEncoding() {
        return self::ENCODING_UTF8;
    }

    /**
     * 現在の改行文字設定を取得する
     *
     * @return string 現在の改行文字
     */
    public function getLineDelimiter() {
        return $this->_lineDelimiter;
    }
    /**
     * 改行文字設定を変更する
     *
     * @param string | null $delimiter 変更する改行文字。省略時は規定値が採用される
     * @return LogicRwarvlDataExporterFormatterAbstract このインスタンス
     */
    public function setLineDelimiter($delimiter = null) {
        $delim_list = array(
            self::LDELIM_CR,
            self::LDELIM_LF,
            self::LDELIM_CRLF
        );
        // 指定可能な改行コードリストにマッチしない場合は規定値に読み替える
        if(!in_array($delimiter, $delim_list)) {
            $delimiter = $this->getDefaultLineDelimiter();
        }
        $this->_lineDelimiter = $delimiter;
        return $this;
    }
    /**
     * 規定の改行文字設定を取得する
     * このクラスでは常にLFを返す。
     * 派生クラスで必要に応じてオーバーライド可能。
     *
     * @return string 規定の改行文字
     */
    public function getDefaultLineDelimiter() {
        return self::LDELIM_LF;
    }

    /**
     * 指定データをフォーマット出力する
     * @param array $data 出力対象データ
     */
    public function format(array $data) {
        $this->_preFormat($data);

        foreach($data as $index => $row) {
            $this->_echo($this->_formatRow($row));
        }

        $this->_postFormat($data);
    }

    /**
     * 出力後処理を実行する。
     * 派生クラスでオーバーライドした場合、リスト本体出力後に必要な
     * 出力処理を行うことができる。
     * このクラスの実装ではなにもしない。
     *
     * @access protected
     * @param array $data 出力対象データ
     */
    protected function _postFormat(array $data) {
        // nop
    }

    /**
     * 出力用の行データを1行分の行文字列に変換する。
     * 派生クラスでカスタマイズ可能。
     * このクラスの実装では単純にカンマ区切りで結合する
     *
     * @access protected
     * @param array $row 行データ
     * @return string $rowを出力用文字列に変換した、1行分の文字列
     */
    protected function _formatRow(array $row) {
        return join(',', $row);
    }

    /**
     * 指定文字列の文字エンコーディングを現在の出力設定用に変換する
     *
     * @access protected
     * @param string $str 変換する文字列
     * @return string 変換済み文字列
     */
    protected function _convertEncoding($str) {
        // 入力文字列の文字エンコーディングを検出
        $current_encoding = BaseIOUtility::detectEncoding($str);

        // 変換結果を返す
        return mb_convert_encoding($str, $this->getEncoding(), $current_encoding);
    }

     /**
     * ヘッダ出力用の行配列を取得する
     * @access protected
     * @return array
     */
    protected function getHeaderItems() {
        return array('注文Seq', '注文ID', '伝票番号', '配送業者', 'ステータスコード', 'ステータス');
    }

    /**
     * 出力前処理を実行する。
     * 派生クラスでオーバーライドした場合、見出しなどリスト本体前に必要な
     * 出力処理を行う事ができる。
     * このクラスの実装では、ヘッダ情報をカンマ区切りで出力する
     *
     * @access protected
     * @param array $data 出力対象データ
     */
    protected function _preFormat(array $data) {
        $this->_echo($this->_formatRow($this->getHeaderItems()));
    }

    /**
     * 指定文字列を出力する
     *
     * @access protected
     * @param string $str 出力する文字列
     * @param null | boolean $suspend_delimiter 改行文字出力を抑止するかの指定。省略時は抑止しない
     */
    protected function _echo($str, $suspend_delimiter = false) {
        // エンコーディング変換をして出力
        echo $this->_convertEncoding($str);
        if(!$suspend_delimiter) {
            // 改行抑止指定がない場合は改行文字を出力
            $this->_echo($this->getLineDelimiter(), true);
        }
    }

}
