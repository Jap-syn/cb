<?php
namespace models\Logic\Haipon\Exporter;

use models\Logic\Haipon\Exporter\Formatter\LogicHaiponExporterFormatterInterface;
use models\Logic\Haipon\Exporter\Formatter\LogicHaiponExporterFormatterAbstract;
use models\Logic\Haipon\Exporter\Formatter\LogicHaiponExporterFormatterCsv;
use models\Logic\Haipon\Exporter\Formatter\LogicHaiponExporterFormatterHtml;
use models\Logic\Haipon\LogicHaiponException;

/**
 * エクスポート形式を管理するユーティリティクラス
 */
class LogicHaiponExporterFormatter {
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
     * フォーマット定数：HTML形式
     * @var string
     */
    const FORMAT_HTML = 'html';

    /**
     * フォーマットラベル定数：HTML形式
     * @var string
     */
    const FORMAT_HTML_LABEL = 'HTMLファイル';

    /**
     * 利用可能なフォーマット形式と表示名の情報を取得する
     *
     * @static
     * @return array
     */
    public static function getAvailableFormats() {
        return array(
            self::FORMAT_CSV => self::FORMAT_CSV_LABEL,
            self::FORMAT_HTML => self::FORMAT_HTML_LABEL
        );
    }

    /**
     * 指定のフォーマット形式に対応したフォーマッタを生成する
     *
     * @static
     * @param string | null $format フォーマット指定
     * @return LogicHaiponExporterFormatterInterface フォーマッタインスタンス
     */
    public static function factory($format = self::FORMAT_CSV) {
        $formatters = array_keys(self::getAvailableFormats());
        if(!in_array($format, $formatters)) {
            $format = self::FORMAT_CSV;
        }
        switch($format) {
            case self::FORMAT_CSV:
                return new LogicHaiponExporterFormatterCsv();
            case self::FORMAT_HTML:
                return new LogicHaiponExporterFormatterHtml();
        }
        throw new LogicHaiponException('出力フォーマットの指定が不正です');
    }
}
