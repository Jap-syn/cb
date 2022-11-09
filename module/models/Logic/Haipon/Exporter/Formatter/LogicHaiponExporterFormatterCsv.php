<?php
namespace models\Logic\Haipon\Exporter\Formatter;

use models\Logic\Haipon\Exporter\Formatter\LogicHaiponExporterFormatterAbstract;

/**
 * 配送でポン連携用のCSV形式フォーマッタ
 */
class LogicHaiponExporterFormatterCsv extends LogicHaiponExporterFormatterAbstract {

    /**
     * 規定の文字エンコーディング設定を取得する。
     * このクラスでは常に'sjis-win'（CP932）を返す
     *
     * @abstract
     * @return string 規定の文字エンコーディング
     */
    public function getDefaultEncoding() {
        return self::ENCODING_SJIS;
    }

    /**
     * 規定の改行文字設定を取得する。
     * このクラスでは常にCRLFを返す
     *
     * @abstract
     * @return string 規定の改行文字
     */
    public function getDefaultLineDelimiter() {
        return self::LDELIM_CRLF;
    }

    /**
     * 出力用の行データを1行分の行文字列に変換する。
     * このクラスの実装ではCSV行となるようクォート処理を適用しカンマ区切りに成形する
     *
     * @access protected
     * @param array $row 行データ
     * @return string $rowを出力用文字列に変換した、1行分の文字列
     */
    protected function _formatRow(array $row) {
        $buf = array();
        foreach($row as $col) {
            if(preg_match('/^-?(\d|([1-9]\d+))(\.\d+)?$/', $col)) {
                // 数値表記の場合はそのまま出力する
                $buf[] = $col;
            } else {
                // それ以外はクォートする
                $buf[] = sprintf('"%s"', preg_replace('/"/', '""', $col));
            }
        }
        return join(',', $buf);
    }
}
