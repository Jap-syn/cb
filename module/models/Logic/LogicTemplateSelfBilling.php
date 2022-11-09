<?php
namespace models\Logic;

use models\Table\TableTemplateHeader;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Coral\Base\IO\BaseIOCsvWriter;
use Coral\Base\IO\BaseIOUtility;
use Zend\Json\Json;

/**
* (SelfBilling)テンプレート変換クラス
*/
class LogicTemplateSelfBilling Extends LogicTemplate
{
    /**
     * 表示用小数点桁数
     *
     * @var int
     */
    protected $_dispDecimalPoint = 0;

    /**
     * コンストラクタ
     *
     * @param Adapter $adapter アダプタ
     */
    function __construct( Adapter $adapter, $dispDecimalPoint )
    {
        parent::__construct($adapter);

        // 表示用小数点桁数、の設定
        $this->_dispDecimalPoint = $dispDecimalPoint;
    }

    /**
     * (Override)現在の設定でCSV出力を開始する
     *
     * @param array $header 出力ヘッダ
     * @param array $datas 出力データ
     * @param string $fileName 出力データに設定するファイル名(ローカルパスを指定した場合はそのファイルパスに出力)
     * @param ResponseInterface $response 出力オブジェクト
     */
    protected function writeCsv($header, $datas, $fileName, $response ) {

        $is_match = preg_match( '/(.*[\\\\\\/])?([^\\\\\\/]+)$/', $fileName, $matches );

        if( $is_match ) $fileName = $matches[2];

        if( ! ( $response instanceof ResponseInterface ) ) $response = null;

        $fileName = urlencode( $fileName );

        $dispValue = 'attachment';

        // レスポンスヘッダの出力
        $contentType = 'application/octet-stream';
        if( $response ) {
            $response->getHeaders()->addHeaderLine( 'Content-Type', $contentType )
            ->addHeaderLine( 'Content-Disposition' , "$dispValue; filename=$fileName" );
        } else {
            header( "Content-Type: $contentType" );
            header( "Content-Disposition: $dispValue; filename=$fileName" );
        }

        // データ出力
        if ($this->templateHeader['TitleClass'] > 0) {
            echo $this->encodeRowHeader( $header) . "\r\n";
        }
        foreach( $datas as $data ) {
            echo $this->encodeRow( $data ) . "\r\n";
        }
    }

    /**
     * (Override)現在の設定でCSV出力を開始する
     *
     * @param array $header 出力ヘッダ
     * @param array $datas 出力データ
     * @param string $fileName 出力データに設定するファイル名(ローカルパスを指定した場合はそのファイルパスに出力)
     * @param ResponseInterface $response 出力オブジェクト
     */
    protected function writeCsv2($header, $datas, $fileName, $response ) {

        $is_match = preg_match( '/(.*[\\\\\\/])?([^\\\\\\/]+)$/', $fileName, $matches );

        if( $is_match ) $fileName = $matches[2];

        if( ! ( $response instanceof ResponseInterface ) ) $response = null;

        $fileName = urlencode( $fileName );

        $dispValue = 'attachment';

        // レスポンスヘッダの出力
        $contentType = 'application/octet-stream';
        if( $response ) {
            $response->getHeaders()->addHeaderLine( 'Content-Type', $contentType )
            ->addHeaderLine( 'Content-Disposition' , "$dispValue; filename=$fileName" );
        } else {
            header( "Content-Type: $contentType" );
            header( "Content-Disposition: $dispValue; filename=$fileName" );
        }

        // データ出力
        if ($this->templateHeader['TitleClass'] > 0) {
            echo $this->encodeRowHeader2( $header) . "\r\n";
        }
        foreach( $datas as $data ) {
            echo $this->encodeRow2( $data ) . "\r\n";
        }
    }

    /**
     * (ヘッダ専用)設定された文字エンコードで囲み文字と区切り文字で変換した文字列を返す
     *
     * @param array $row
     * @return string
     */
    protected function encodeRowHeader($row) {
        $result = array();
        if( is_array( $row ) ) {
            $enclose = !empty( $this->templateHeader['EncloseValue'] ) ? $this->templateHeader['EncloseValue'] : '"';

            // 商品明細(OrderItems)のListNumber取得
            $listNumberOfOrderItems = $this->adapter->query(
                " SELECT ListNumber FROM M_TemplateField WHERE TemplateSeq = :TemplateSeq AND PhysicalName = 'OrderItems' ")->execute(
                array(':TemplateSeq' => $this->templateHeader['TemplateSeq']))->current()['ListNumber'];

            foreach($row as $i => $col) {

                // NOTE:[商品明細]時は特別処理を実施
                if ($listNumberOfOrderItems == $i) {
                    $reserve = Json::decode($this->templateHeader['Reserve'], Json::TYPE_ARRAY);
                    for ($i=0; $i<(int)$reserve['items']; $i++) {
                        $noStr = $i + 1;

                        $result[] = $enclose . str_replace("\\'", "'", preg_replace( '/' . $enclose . '/', $enclose . $enclose, ('商品名' . $noStr) ) ) . $enclose;
                        $result[] = $enclose . str_replace("\\'", "'", preg_replace( '/' . $enclose . '/', $enclose . $enclose, ('数量' . $noStr) ) ) . $enclose;
                        $result[] = $enclose . str_replace("\\'", "'", preg_replace( '/' . $enclose . '/', $enclose . $enclose, ('単価' . $noStr) ) ) . $enclose;
                        $result[] = $enclose . str_replace("\\'", "'", preg_replace( '/' . $enclose . '/', $enclose . $enclose, ('金額' . $noStr) ) ) . $enclose;
                    }
                }
                else {
                    $result[] = $enclose . str_replace("\\'", "'", preg_replace( '/' . $enclose . '/', $enclose . $enclose, (string)$col ) ) . $enclose;
                }

            }
            $delimiter = !empty( $this->templateHeader['DelimiterValue'] ) ? $this->templateHeader['DelimiterValue'] : ',';

            return mb_convert_encoding( join( $delimiter, $result ), $this->templateHeader['CharacterCode'] == '*' ? BaseIOUtility::ENCODING_WIN_SJIS : $this->templateHeader['CharacterCode'] );
        }
        return null;
    }

    /**
     * (ヘッダ専用)設定された文字エンコードで囲み文字と区切り文字で変換した文字列を返す
     *
     * @param array $row
     * @return string
     */
    protected function encodeRowHeader2($row) {
        $result = array();
        if( is_array( $row ) ) {
            $enclose = !empty( $this->templateHeader['EncloseValue'] ) ? $this->templateHeader['EncloseValue'] : '"';

            // 商品明細(OrderItems)のListNumber取得
            $listNumberOfOrderItems = $this->adapter->query(
            " SELECT ListNumber FROM M_TemplateField WHERE TemplateSeq = :TemplateSeq AND PhysicalName = 'OrderItems' ")->execute(
            array(':TemplateSeq' => $this->templateHeader['TemplateSeq']))->current()['ListNumber'];

            foreach($row as $i => $col) {

                // NOTE:[商品明細]時は特別処理を実施
                if ($listNumberOfOrderItems == $i) {
                    $reserve = Json::decode($this->templateHeader['Reserve'], Json::TYPE_ARRAY);
                    for ($i=0; $i<(int)$reserve['items']; $i++) {
                        $noStr = $i + 1;
                        $result[] = $enclose . str_replace("\\'", "'", preg_replace( '/' . $enclose . '/', $enclose . $enclose, ('商品名' . $noStr) ) ) . $enclose;
                        $result[] = $enclose . str_replace("\\'", "'", preg_replace( '/' . $enclose . '/', $enclose . $enclose, ('数量' . $noStr) ) ) . $enclose;
                        $result[] = $enclose . str_replace("\\'", "'", preg_replace( '/' . $enclose . '/', $enclose . $enclose, ('単価' . $noStr) ) ) . $enclose;
                        $result[] = $enclose . str_replace("\\'", "'", preg_replace( '/' . $enclose . '/', $enclose . $enclose, ('金額' . $noStr) ) ) . $enclose;
                        $result[] = $enclose . str_replace("\\'", "'", preg_replace( '/' . $enclose . '/', $enclose . $enclose, ('消費税率' . $noStr) ) ) . $enclose;
                    }
                }
                else {
                    $result[] = $enclose . str_replace("\\'", "'", preg_replace( '/' . $enclose . '/', $enclose . $enclose, (string)$col ) ) . $enclose;
                }

            }
            $delimiter = !empty( $this->templateHeader['DelimiterValue'] ) ? $this->templateHeader['DelimiterValue'] : ',';

            return mb_convert_encoding( join( $delimiter, $result ), $this->templateHeader['CharacterCode'] == '*' ? BaseIOUtility::ENCODING_WIN_SJIS : $this->templateHeader['CharacterCode'] );
        }
        return null;
    }

    /**
     * (Override)設定された文字エンコードで囲み文字と区切り文字で変換した文字列を返す
     *
     * @param array $row
     * @return string
     */
    protected function encodeRow($row) {

        $result = array();
        if( is_array( $row ) ) {
            $enclose = !empty( $this->templateHeader['EncloseValue'] ) ? $this->templateHeader['EncloseValue'] : '"';

            // 商品明細(OrderItems)のListNumber取得
            $listNumberOfOrderItems = $this->adapter->query(
                " SELECT ListNumber FROM M_TemplateField WHERE TemplateSeq = :TemplateSeq AND PhysicalName = 'OrderItems' ")->execute(
                array(':TemplateSeq' => $this->templateHeader['TemplateSeq']))->current()['ListNumber'];

            // その他商品点数(OtherItemsCount)のListNumber取得
            $listNumberOfOtherItemsCount = $this->adapter->query(
            " SELECT ListNumber FROM M_TemplateField WHERE TemplateSeq = :TemplateSeq AND PhysicalName = 'OtherItemsCount' ")->execute(
            array(':TemplateSeq' => $this->templateHeader['TemplateSeq']))->current()['ListNumber'];

            // その他合算金額(OtherItemsSummary)のListNumber取得
            $listNumberOfOtherItemsSummary = $this->adapter->query(
            " SELECT ListNumber FROM M_TemplateField WHERE TemplateSeq = :TemplateSeq AND PhysicalName = 'OtherItemsSummary' ")->execute(
            array(':TemplateSeq' => $this->templateHeader['TemplateSeq']))->current()['ListNumber'];

            foreach($row as $i => $col) {

                // NOTE:[商品明細]時は特別処理を実施
                if ($listNumberOfOrderItems == $i) {
                    $reserve = Json::decode($this->templateHeader['Reserve'], Json::TYPE_ARRAY);

                    // 集計列が必要になるかを調査する(ここから)
                    $isNeedSummary = false;
                    $anotherSumMoney = 0;
                    $anotherSumMoney2 = 0;
                    $anotherCount = 0;
                    $anotherCount2 = 0;

                    $colCount = 0;
                    if(!empty($col)) {
                        $colCount = count($col);
                    }
                    if ($colCount > (int)$reserve['items']) {
                        $isNeedSummary = true;

                        $sttIdx = (int)$reserve['items'] - 1;
                        for ($j=$sttIdx; $j<$colCount; $j++) {
                            $anotherSumMoney += $col[$j]['SumMoney'];
                        }
                        for ($j=$sttIdx+1; $j<$colCount; $j++) {
                            $anotherSumMoney2 += $col[$j]['SumMoney'];
                        }

                        $anotherCount = $colCount - (int)$reserve['items'] + 1;
                        $anotherCount2 = $anotherCount - 1;
                    }
                    // 集計列が必要になるかを調査する(ここまで)

                    for ($k=0; $k<(int)$reserve['items']; $k++) {
                        if (isset($col[$k]) && ($k + 1 == (int)$reserve['items']) && $isNeedSummary && (int)$reserve['itemsType'] == 1) {
                            // 商品明細での集計列は旧レイアウトのみ
                            $result[] = $enclose . str_replace("\\'", "'", preg_replace( '/' . $enclose . '/', $enclose . $enclose, $this->makeOutputString('その他　' . $anotherCount . '点') ) ) . $enclose;
                            $result[] = $enclose . str_replace("\\'", "'", preg_replace( '/' . $enclose . '/', $enclose . $enclose, number_format(1, $this->_dispDecimalPoint, '.', '') ) ) . $enclose;
                            $result[] = $enclose . str_replace("\\'", "'", preg_replace( '/' . $enclose . '/', $enclose . $enclose, $this->makeOutputString($anotherSumMoney) ) ) . $enclose;
                            $result[] = $enclose . str_replace("\\'", "'", preg_replace( '/' . $enclose . '/', $enclose . $enclose, $this->makeOutputString($anotherSumMoney) ) ) . $enclose;

                        }
                        else if (isset($col[$k])) {
                            $result[] = $enclose . str_replace("\\'", "'", preg_replace( '/' . $enclose . '/', $enclose . $enclose, $this->makeOutputString($col[$k]['ItemNameKj']) ) ) . $enclose;
                            $result[] = $enclose . str_replace("\\'", "'", preg_replace( '/' . $enclose . '/', $enclose . $enclose, (is_numeric($col[$k]['ItemNum'])) ? number_format($col[$k]['ItemNum'], $this->_dispDecimalPoint, '.', '') : $this->makeOutputString($col[$k]['ItemNum']) ) ) . $enclose;
                            $result[] = $enclose . str_replace("\\'", "'", preg_replace( '/' . $enclose . '/', $enclose . $enclose, $this->makeOutputString($col[$k]['UnitPrice']) ) ) . $enclose;
                            $result[] = $enclose . str_replace("\\'", "'", preg_replace( '/' . $enclose . '/', $enclose . $enclose, $this->makeOutputString($col[$k]['SumMoney']) ) ) . $enclose;
                        }
                        else {
                            $result[] = $enclose . str_replace("\\'", "'", preg_replace( '/' . $enclose . '/', $enclose . $enclose, $this->makeOutputString("") ) ) . $enclose;
                            $result[] = $enclose . str_replace("\\'", "'", preg_replace( '/' . $enclose . '/', $enclose . $enclose, $this->makeOutputString("") ) ) . $enclose;
                            $result[] = $enclose . str_replace("\\'", "'", preg_replace( '/' . $enclose . '/', $enclose . $enclose, $this->makeOutputString("") ) ) . $enclose;
                            $result[] = $enclose . str_replace("\\'", "'", preg_replace( '/' . $enclose . '/', $enclose . $enclose, $this->makeOutputString("") ) ) . $enclose;
                        }
                    }
                }
                // NOTE:[その他商品点数]時は特別処理を実施
                elseif ($listNumberOfOtherItemsCount == $i) {
                    $result[] = $enclose . str_replace("\\'", "'", preg_replace( '/' . $enclose . '/', $enclose . $enclose, $this->makeOutputString($anotherCount2) ) ) . $enclose;
                }
                // NOTE:[その他合算金額]時は特別処理を実施
                elseif ($listNumberOfOtherItemsSummary == $i) {
                    $result[] = $enclose . str_replace("\\'", "'", preg_replace( '/' . $enclose . '/', $enclose . $enclose, $this->makeOutputString($anotherSumMoney2) ) ) . $enclose;
                }
                else {
                    $result[] = $enclose . str_replace("\\'", "'", preg_replace( '/' . $enclose . '/', $enclose . $enclose, $this->makeOutputString($col) ) ) . $enclose;
                }
            }
            $delimiter = !empty( $this->templateHeader['DelimiterValue'] ) ? $this->templateHeader['DelimiterValue'] : ',';

            return mb_convert_encoding( join( $delimiter, $result ), $this->templateHeader['CharacterCode'] == '*' ? BaseIOUtility::ENCODING_WIN_SJIS : $this->templateHeader['CharacterCode'] );
        }
        return null;
    }

    /**
     * (Override)設定された文字エンコードで囲み文字と区切り文字で変換した文字列を返す
     *
     * @param array $row
     * @return string
     */
    protected function encodeRow2($row) {

        $result = array();
        if( is_array( $row ) ) {
            $enclose = !empty( $this->templateHeader['EncloseValue'] ) ? $this->templateHeader['EncloseValue'] : '"';

            // 商品明細(OrderItems)のListNumber取得
            $listNumberOfOrderItems = $this->adapter->query(
            " SELECT ListNumber FROM M_TemplateField WHERE TemplateSeq = :TemplateSeq AND PhysicalName = 'OrderItems' ")->execute(
            array(':TemplateSeq' => $this->templateHeader['TemplateSeq']))->current()['ListNumber'];

            // その他商品点数(OtherItemsCount)のListNumber取得
            $listNumberOfOtherItemsCount = $this->adapter->query(
            " SELECT ListNumber FROM M_TemplateField WHERE TemplateSeq = :TemplateSeq AND PhysicalName = 'OtherItemsCount' ")->execute(
            array(':TemplateSeq' => $this->templateHeader['TemplateSeq']))->current()['ListNumber'];

            // その他合算金額(OtherItemsSummary)のListNumber取得
            $listNumberOfOtherItemsSummary = $this->adapter->query(
            " SELECT ListNumber FROM M_TemplateField WHERE TemplateSeq = :TemplateSeq AND PhysicalName = 'OtherItemsSummary' ")->execute(
            array(':TemplateSeq' => $this->templateHeader['TemplateSeq']))->current()['ListNumber'];

            foreach($row as $i => $col) {

                // NOTE:[商品明細]時は特別処理を実施
                if ($listNumberOfOrderItems == $i) {
                    $reserve = Json::decode($this->templateHeader['Reserve'], Json::TYPE_ARRAY);

                    // 集計列が必要になるかを調査する(ここから)
                    $isNeedSummary = false;
                    $anotherSumMoney = 0;
                    $anotherSumMoney2 = 0;
                    $anotherCount = 0;
                    $anotherCount2 = 0;

                    $colCount = 0;
                    if(!empty($col)) {
                        $colCount = count($col);
                    }
                    if ($colCount > (int)$reserve['items']) {
                        $isNeedSummary = true;

                        $sttIdx = (int)$reserve['items'] - 1;
                        for ($j=$sttIdx; $j<$colCount; $j++) {
                            $anotherSumMoney += $col[$j]['SumMoney'];
                        }
                        for ($j=$sttIdx+1; $j<$colCount; $j++) {
                            $anotherSumMoney2 += $col[$j]['SumMoney'];
                        }

                        $anotherCount = $colCount - (int)$reserve['items'] + 1;
                        $anotherCount2 = $anotherCount - 1;
                    }
                    // 集計列が必要になるかを調査する(ここまで)

                    for ($k=0; $k<(int)$reserve['items']; $k++) {
                        if (isset($col[$k]) && ($k + 1 == (int)$reserve['items']) && $isNeedSummary && (int)$reserve['itemsType'] == 1) {
                            // 商品明細での集計列は旧レイアウトのみ
                            $result[] = $enclose . str_replace("\\'", "'", preg_replace( '/' . $enclose . '/', $enclose . $enclose, $this->makeOutputString('その他　' . $anotherCount . '点') ) ) . $enclose;
                            $result[] = $enclose . str_replace("\\'", "'", preg_replace( '/' . $enclose . '/', $enclose . $enclose, number_format(1, $this->_dispDecimalPoint, '.', '') ) ) . $enclose;
                            $result[] = $enclose . str_replace("\\'", "'", preg_replace( '/' . $enclose . '/', $enclose . $enclose, $this->makeOutputString($anotherSumMoney) ) ) . $enclose;
                            $result[] = $enclose . str_replace("\\'", "'", preg_replace( '/' . $enclose . '/', $enclose . $enclose, $this->makeOutputString($anotherSumMoney) ) ) . $enclose;
                            $result[] = $enclose . str_replace("\\'", "'", preg_replace( '/' . $enclose . '/', $enclose . $enclose, $this->makeOutputString("") ) ) . $enclose;
                        }
                        else if (isset($col[$k])) {
                            $result[] = $enclose . str_replace("\\'", "'", preg_replace( '/' . $enclose . '/', $enclose . $enclose, $this->makeOutputString($col[$k]['ItemNameKj']) ) ) . $enclose;
                            $result[] = $enclose . str_replace("\\'", "'", preg_replace( '/' . $enclose . '/', $enclose . $enclose, (is_numeric($col[$k]['ItemNum'])) ? number_format($col[$k]['ItemNum'], $this->_dispDecimalPoint, '.', '') : $this->makeOutputString($col[$k]['ItemNum']) ) ) . $enclose;
                            $result[] = $enclose . str_replace("\\'", "'", preg_replace( '/' . $enclose . '/', $enclose . $enclose, $this->makeOutputString($col[$k]['UnitPrice']) ) ) . $enclose;
                            $result[] = $enclose . str_replace("\\'", "'", preg_replace( '/' . $enclose . '/', $enclose . $enclose, $this->makeOutputString($col[$k]['SumMoney']) ) ) . $enclose;
                            $result[] = $enclose . str_replace("\\'", "'", preg_replace( '/' . $enclose . '/', $enclose . $enclose, $this->makeOutputString($col[$k]['TaxRate']) ) ) . $enclose;
                        }
                        else {
                            $result[] = $enclose . str_replace("\\'", "'", preg_replace( '/' . $enclose . '/', $enclose . $enclose, $this->makeOutputString("") ) ) . $enclose;
                            $result[] = $enclose . str_replace("\\'", "'", preg_replace( '/' . $enclose . '/', $enclose . $enclose, $this->makeOutputString("") ) ) . $enclose;
                            $result[] = $enclose . str_replace("\\'", "'", preg_replace( '/' . $enclose . '/', $enclose . $enclose, $this->makeOutputString("") ) ) . $enclose;
                            $result[] = $enclose . str_replace("\\'", "'", preg_replace( '/' . $enclose . '/', $enclose . $enclose, $this->makeOutputString("") ) ) . $enclose;
                            $result[] = $enclose . str_replace("\\'", "'", preg_replace( '/' . $enclose . '/', $enclose . $enclose, $this->makeOutputString("") ) ) . $enclose;
                        }
                    }
                }
                // NOTE:[その他商品点数]時は特別処理を実施
                elseif ($listNumberOfOtherItemsCount == $i) {
                    $result[] = $enclose . str_replace("\\'", "'", preg_replace( '/' . $enclose . '/', $enclose . $enclose, $this->makeOutputString($anotherCount2) ) ) . $enclose;
                }
                // NOTE:[その他合算金額]時は特別処理を実施
                elseif ($listNumberOfOtherItemsSummary == $i) {
                    $result[] = $enclose . str_replace("\\'", "'", preg_replace( '/' . $enclose . '/', $enclose . $enclose, $this->makeOutputString($anotherSumMoney2) ) ) . $enclose;
                }
                else {
                    $result[] = $enclose . str_replace("\\'", "'", preg_replace( '/' . $enclose . '/', $enclose . $enclose, $this->makeOutputString($col) ) ) . $enclose;
                }
            }
            $delimiter = !empty( $this->templateHeader['DelimiterValue'] ) ? $this->templateHeader['DelimiterValue'] : ',';

            return mb_convert_encoding( join( $delimiter, $result ), $this->templateHeader['CharacterCode'] == '*' ? BaseIOUtility::ENCODING_WIN_SJIS : $this->templateHeader['CharacterCode'] );
        }
        return null;
    }

    protected function makeOutputString($val) {
        $ret = (string)$val;

        // データがないフィールドを半角スペースに置き換える
        if( $this->templateHeader['NoDataFieldSettingFlg'] == 1 && strlen( $ret ) == 0 ) {
            $ret = ' ';
        }

        return $ret;
    }
}
