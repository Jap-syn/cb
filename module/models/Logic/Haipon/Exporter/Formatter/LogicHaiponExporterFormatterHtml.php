<?php
namespace models\Logic\Haipon\Exporter\Formatter;

use models\Logic\Haipon\Exporter\Formatter\LogicHaiponExporterFormatterAbstract;

/**
 * 配送でポン連携用のHTML形式フォーマッタ
 */
class LogicHaiponExporterFormatterHtml extends LogicHaiponExporterFormatterAbstract {

    /**
     * 出力前処理を実行する。
     * このクラスの実装では、データ行部分以前のHTMLフラグメントコードを出力する
     *
     * @access protected
     * @param array $data 出力対象データ
     */
    protected function _preFormat(array $data) {
        // Excelで開いた場合に数値表記を文字列として表示できるように
        // CSSの指定を記述
        $header = <<<EOH
<html>
<head>
<meta http-equiv="Content-type" content="text/html; charset=%s">
<title>配ポン連携データ</title>
<style type="text/css">
th.chars, td.chars {
    mso-number-format: "\@";
}
</style>
</head>
<body>
<table celpadding="0" cellspacing="0" border="1">
<thead>
<tr><th>件数</th><th align="right">%s</th></tr>
%s
</thead>
<tbody>

EOH;
        $dataCount = 0;
        if(!empty($data)) {
            $dataCount = count($data);
        }
        $contents = sprintf($header,
                            $this->getEncoding(),
                            $dataCount,
                            $this->_formatHeaderRow($this->getHeaderItems()));
        $this->_echo($contents);
    }

    /**
     * 出力後処理を実行する。
     * このクラスの実装では、データ行部分以降のHTMLフラグメントコードを出力する
     *
     * @access protected
     * @param array $data 出力対象データ
     */
    protected function _postFormat(array $data) {
        $footer = <<<EOH

</tbody>
</table>
</body>
</html>
EOH;
        $this->_echo($footer);
    }

    /**
     * 出力用の行データを1行分の行文字列に変換する。
     * このクラスの実装ではHTMLテーブルの行に成形する
     *
     * @access protected
     * @param array $row 行データ
     * @return string $rowを出力用文字列に変換した、1行分の文字列
     */
    protected function _formatRow(array $row) {
        // デフォルトの列用タグで変換を実行
        return $this->_formatRowInternal($row);
    }

    /**
     * HTMLテーブルの見出し行出力用に成形する
     *
     * @access protected
     * @param array $row ヘッダ行データ
     * @return string $rowを見出し行用HTMLフラグメントコード
     */
    protected function _formatHeaderRow(array $row) {
        // テーブルヘッダ向けの列用タグを指定して変換を実行
        return $this->_formatRowInternal($row, 'th');
    }

    /**
     * HTMLテーブル行用整形の本処理。
     * 列用タグを指定可能。
     *
     * @access protected
     * @param array $row 行データ
     * @param null | string $col_tag 列用タグ名。省略時は'td'が採用される
     * @return string $rowを変換したHTMLフラグメントコード
     */
    protected function _formatRowInternal(array $row, $col_tag = 'td') {
        $buf = array('<tr>');
        foreach($row as $col) {
            $buf[] = sprintf('<%s class="chars">%s</%s>', $col_tag, f_e($col), $col_tag);
        }
        $buf[] = '</tr>';
        return join('', $buf);
    }
}
