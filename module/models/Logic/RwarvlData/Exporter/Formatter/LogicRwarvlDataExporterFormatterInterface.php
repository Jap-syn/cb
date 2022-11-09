<?php
namespace models\Logic\RwarvlData\Exporter\Formatter;

/**
 * 配送でポン連携用ファイルのフォーマットインターフェイス。
 * 行連想配列を受け取り出力先へフォーマット済みの状態で出力するための
 * 動作を規定する
 */
interface LogicRwarvlDataExporterFormatterInterface {
    /**
     * 現在の文字エンコーディング設定を取得する
     * @return string 現在の文字エンコーディング
     */
    public function getEncoding();

    /**
     * 文字エンコーディング設定を変更する
     * @param string | null $encoding 変更する文字エンコーディング。省略時は規定値が採用される
     * @return LogicRwarvlDataExporterFormatterInterface このインスタンス
     */
    public function setEncoding($encoding = null);

    /**
     * 規定の文字エンコーディング設定を取得する
     * @return string 規定の文字エンコーディング
     */
    public function getDefaultEncoding();

    /**
     * 現在の改行文字設定を取得する
     * @return string 現在の改行文字
     */
    public function getLineDelimiter();

    /**
     * 改行文字設定を変更する
     * @param string | null $delimiter 変更する改行文字。省略時は規定値が採用される
     * @return LogicRwarvlDataExporterFormatterInterface このインスタンス
     */
    public function setLineDelimiter($delimiter = null);

    /**
     * 規定の改行文字設定を取得する
     * @return string 規定の改行文字
     */
    public function getDefaultLineDelimiter();

    /**
     * 指定データをフォーマット出力する
     * @param array $data 出力対象データ
     */
    public function format(array $data);

}
