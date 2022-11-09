<?php
namespace models\Logic\RwarvlData\Exporter;

use models\Logic\RwarvlData\Exporter\Formatter\LogicRwarvlDataExporterFormatterCsv;

/**
 * エクスポート形式を管理するユーティリティクラス
 */
class LogicRwarvlDataExporterFormatter {
    /**
     * フォーマット定数：CSV形式
     * @var string
     */
    const FORMAT_CSV = 'csv';

    /**
     * フォーマットラベル定数：CSV形式
     * @var string
     */
    const FORMAT_CSV_LABEL = 'CSVファイル';

    /**
     * 利用可能なフォーマット形式と表示名の情報を取得する
     *
     * @static
     * @return array
     */
    public static function getAvailableFormats() {
        return array(
            self::FORMAT_CSV => self::FORMAT_CSV_LABEL
        );
    }

    /**
     * 指定のフォーマット形式に対応したフォーマッタを生成する
     *
     * @static
     * @param string | null $format フォーマット指定
     * @return LogicRwarvlDataExporterFormatterCsv フォーマッタインスタンス
     */
    public static function factory($format = self::FORMAT_CSV) {
        $formatters = array_keys(self::getAvailableFormats());
        if(!in_array($format, $formatters)) {
            $format = self::FORMAT_CSV;
        }

        switch($format) {
            case self::FORMAT_CSV:
                return new LogicRwarvlDataExporterFormatterCsv();
        }
    }
}
