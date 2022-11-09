<?php
namespace Coral\Base\IO;
use Zend\Http\Response;
use Zend\Stdlib\ResponseInterface;
use Coral\Base\BaseUtility;

class BaseIOCsvWriter {
	/**
	 * 改行文字'\r'を示す定数値
	 *
	 */
	const LINE_DELIMITER_CR = "\r";

	/**
	 * 改行文字'\n'を示す定数値
	 *
	 */
	const LINE_DELIMITER_LF = "\n";

	/**
	 * 改行文字列'\r\n'を示す定数値
	 *
	 */
	const LINE_DELIMITER_CRLF = "\r\n";

	/**
	 * ヘッダ行を指定する定数値
	 *
	 */
	const PARAMS_COLUMN_HEADER = 'columnHeader';

	/**
	 * 単独のデータ行を指定する定数値
	 *
	 */
	const PARAMS_ROW = 'row';

	/**
	 * 複数のデータ行を指定する定数値
	 *
	 */
	const PARAMS_ROWS = 'rows';

	/**
	 * 文字エンコードを指定する定数値
	 *
	 */
	const PARAMS_ENCODING = 'encoding';

	/**
	 * コンテンツタイプを指定する定数値
	 *
	 */
	const PARAMS_CONTENT_TYPE = 'contentType';

	/**
	 * 文字クォートを指定する定数値
	 *
	 */
	const PARAMS_QUOTE = 'quote';

	/**
	 * 改行文字を指定する定数値
	 *
	 */
	const PARAMS_LINE_DELIMITER = 'lineDelimiter';

	/**
	 * Content-Dispositionにinlineを使用するかを指定する定数値
	 *
	 */
	const PARAMS_USE_INLINE = 'useInline';

	/**
	 * 文字コード変換の際に指定する
	 *
	 */
	const SJIS_CODE = 'SJIS';

	/**
	 * 文字コード変換の際に指定する
	 *
	 */
	const UTF_CODE = 'UTF-8';

	/**
	 * 連想配列から作成したカラムヘッダとデータ行を持つBaseIOCsvWriterの新しいインスタンスを作成する
	 *
	 * @param array $array 元データとなる連想配列。先頭データのキーがカラムヘッダに割り当てられる
	 * @return BaseIOCsvWriter
	 */
	public static function createFromArray($array) {
		if( ! is_array( $array ) ) $array = array();
		$header = ( count($array) > 0 )? array_keys( $array[0] ) : array();
		return new BaseIOCsvWriter(array(
		    BaseIOCsvWriter::PARAMS_COLUMN_HEADER => $header,
		    BaseIOCsvWriter::PARAMS_ROWS => $array
		));
	}

	/**
	 * 出力するCSVのヘッダ行
	 *
	 * @var array
	 */
	protected $_header;

	/**
	 * 出力するデータ行群
	 *
	 * @var array
	 */
	protected $_rows;

	/**
	 * その他のプロパティ値を保持する連想配列
	 *
	 * @var array
	 */
	protected $_params;

	/**
	 * BaseIOCsvWriterの新しいインスタンスを初期化する
	 *
	 * @param array $config オプションパラメータの連想配列。キーはこのクラスのPARAMS_*で定義される定数値に一致する必要がある
	 */
	public function __construct($config = array()) {
		$this->_params = array();

		if( ! is_array( $config ) ) $config = array();

		// 各プロパティの初期値と$configをマージ
		$config = array_merge( array(
			self::PARAMS_CONTENT_TYPE => null,
			self::PARAMS_ENCODING => null,
			self::PARAMS_COLUMN_HEADER => null,
			self::PARAMS_LINE_DELIMITER => null,
			self::PARAMS_QUOTE => null,
			self::PARAMS_ROWS => null,
			self::PARAMS_USE_INLINE => false
		), $config );

		foreach( $config as $key => $value ) {
			switch($key) {
				case self::PARAMS_CONTENT_TYPE:
					// コンテンツタイプ
					$this->setContentType( $value );
					break;
				case self::PARAMS_ENCODING:
					// 文字エンコード
					$this->setEncoding( $value );
					break;
				case self::PARAMS_COLUMN_HEADER:
					// 行ヘッダ
					$this->setColumnHeader( $value );
					break;
				case self::PARAMS_LINE_DELIMITER:
					// 改行文字
					$this->setLineDelimiter( $value );
					break;
				case self::PARAMS_QUOTE:
					// 文字列をクォートするか
					$this->setColumnQuote( $value );
					break;
				case self::PARAMS_USE_INLINE:
					// Content-Dispositionにinlineを指定するか
					$this->setUseInline( $value );
					break;
				case self::PARAMS_ROW:
				case self::PARAMS_ROWS:
					// データ行
					if( $key === self::PARAMS_ROW ) $value = array( $value );
					$this->addRows( $value );
					break;
			}
		}
	}

	/**
	 * 出力時のコンテンツタイプを取得する
	 *
	 * @return string
	 */
	public function getContentType() {
		return $this->_params[ self::PARAMS_CONTENT_TYPE ];
	}

	/**
	 * 出力時のコンテンツタイプを設定する
	 *
	 * @param string $contentType Content-Type
	 * @return BaseIOCsvWriter
	 */
	public function setContentType($contentType = 'application/octet-stream') {
		if( empty($contentType) ) $contentType = 'application/octet-stream';
		$this->_params[ self::PARAMS_CONTENT_TYPE ] = (string)$contentType;

		return $this;
	}

	/**
	 * 出力時の文字エンコードを取得する
	 *
	 * @return string
	 */
	public function getEncoding() {
		return $this->_params[ self::PARAMS_ENCODING ];
	}

	/**
	 * 出力時の文字エンコードを設定する
	 *
	 * @param string $encoding 文字エンコード
	 * @return BaseIOCsvWriter
	 */
	public function setEncoding($encoding = BaseIOUtility::ENCODING_SJIS) {//変更箇所
		if( empty( $encoding ) ) $encoding = BaseIOUtility::ENCODING_SJIS;//変更箇所
		$this->_params[ self::PARAMS_ENCODING ] = (string)$encoding;

		return $this;
	}

	/**
	 * 行ヘッダデータを取得する
	 *
	 * @return array
	 */
	public function getColumnHeader() {
		return $this->_header;
	}

	/**
	 * 行ヘッダデータを設定する
	 *
	 * @param array $header 行ヘッダデータとなる配列
	 * @return BaseIOCsvWriter
	 */
	public function setColumnHeader($header = array()) {
		if( ! is_array( $header ) ) $header = array();
		$this->_header = $header;

		return $this;
	}

	/**
	 * 改行文字を取得する
	 *
	 * @return string
	 */
	public function getLineDelimiter() {
		return $this->_params[ self::PARAMS_LINE_DELIMITER ];
	}

	/**
	 * 改行文字を設定する
	 *
	 * @param string $lineDelimiter 改行文字。このクラスのLINE_DELIMITER_*で定義される定数に一致する必要がある
	 * @return BaseIOCsvWriter
	 */
	public function setLineDelimiter($lineDelimiter = self::LINE_DELIMITER_CRLF) {
		switch($lineDelimiter) {
			case self::LINE_DELIMITER_CR:
			case self::LINE_DELIMITER_LF:
				break;
			default:
				$lineDelimiter = self::LINE_DELIMITER_CRLF;
				break;
		}
		$this->_params[ self::PARAMS_LINE_DELIMITER ] = $lineDelimiter;

		return $this;
	}

	/**
	 * 出力時に文字列をクォートするかのフラグを取得する
	 *
	 * @return bool
	 */
	public function getColumnQuote() {
		return $this->_params[ self::PARAMS_QUOTE ];
	}

	/**
	 * 出力時に文字列をクォートするかのフラグを設定する
	 *
	 * @param bool $quote 文字列をクォートするかのフラグ
	 * @return BaseIOCsvWriter
	 */
	public function setColumnQuote($quote = true) {
		$quote = ( $quote === false ) ? false : true;
		$this->_params[ self::PARAMS_QUOTE ] = $quote;

		return $this;
	}

	/**
	 * Content-Dispositionヘッダにinlineを指定するかのフラグを取得する
	 *
	 * @return bool
	 */
	public function getUseInline() {
		return $this->_params[ self::PARAMS_USE_INLINE ];

	}

	/**
	 * Content-Dispositionヘッダにinlineを指定するかのフラグを設定する。
	 * $useInlineにtrueを指定した場合、クライアントに送信されるContent-Dispositionヘッダには
	 * 値'inline'が指定される。falseの場合は'attachment'が指定される。
	 *
	 * @param bool $useInline Content-Dispositionにinlineを指定するかのフラグ
	 * @return BaseIOCsvWriter
	 */
	public function setUseInline($useInline = false) {
		$useInline = ( $useInline === false ) ? false : true;
		$this->_params[ self::PARAMS_USE_INLINE ] = $useInline;

		return $this;
	}

	/**
	 * データ行を1行追加する
	 *
	 * @param array $row 追加するデータ行
	 * @return BaseIOCsvWriter
	 */
	public function addRow($row = array()) {
		if( ! is_array($row) ) $row = array();

		if( ! empty($row) ) $this->_rows[] = $row;

		return $this;
	}

	/**
	 * すべてのデータ行を取得する
	 *
	 * @return array
	 */
	public function getRows() {
		return $this->_rows;
	}

	/**
	 * 複数のデータ行をまとめて追加する
	 *
	 * @param array $rows 追加するデータ行の配列
	 * @return BaseIOCsvWriter
	 */
	public function addRows($rows = array()) {
		if( ! is_array($rows) ) $rows = array();

		foreach($rows as $row) $this->addRow($row);

		return $this;
	}

	/**
	 * 現在の設定でCSVデータを出力する
	 *
	 * @param string $fileName 出力データに設定するファイル名。ローカルパスを指定した場合はそのファイルパスに出力される。
	 * @param null|ResponseInterface $response 出力オブジェクト。nullまたはResponseInterfaceを指定できる
	 * @return BaseIOCsvWriter
	 */
	public function write($fileName, $response = null) {
		// 出力開始
		$this->beginWrite($fileName, $response);

		// データ行を出力
		$rows = $this->getRows();
		if( is_array( $rows ) ) {
			foreach($rows as $row) {
				$this->writeRow($row);
			}
		}

		return $this;
	}

	/**
	 * 現在の設定でCSV出力を開始する
	 *
	 * @param string $fileName 出力データに設定するファイル名。ローカルパスを指定した場合はそのファイルパスに出力される。
	 * @param null|ResponseInterface $response 出力オブジェクト。nullまたはResponseInterfaceを指定できる
	 * @return BaseIOCsvWriter
	 */
 	public function beginWrite($fileName, $response = null) {
 	    $is_match = preg_match( '/(.*[\\\\\\/])?([^\\\\\\/]+)$/', $fileName, $matches );
		if( $is_match ) $fileName = $matches[2];

 		if( ! ( $response instanceof ResponseInterface ) ) $response = null;

		$fileName = urlencode( $fileName );

		// Content-Dispositionの値確定
		$dispValue = $this->_params[ self::PARAMS_USE_INLINE ] ? 'inline' : 'attachment';

		// レスポンスヘッダの出力
		$contentType = $this->getContentType();
 		if( $response ) {
 		    $response->getHeaders()->addHeaderLine( 'Content-Type', $contentType )
 		                           ->addHeaderLine( 'Content-Disposition' , "$dispValue; filename=$fileName" );
 		} else {
			header( "Content-Type: $contentType" );
			header( "Content-Disposition: $dispValue; filename=$fileName" );
 		}

		// 設定されていたらカラムヘッダを最初に出力
		$header = $this->getColumnHeader();
		if( ! empty( $header ) ) {
			echo $this->encodeRow( $header ) . $this->getLineDelimiter();
		}

		return $this;
	}

	/**
	 * 指定のデータ行を直接出力する
	 *
	 * @param array $row データ行
	 * @return BaseIOCsvWriter
	 */
	public function writeRow(array $row = array()) {
		if(count($row)) {
			echo $this->encodeRow($row) . $this->getLineDelimiter();
		}
		return $this;
	}

	/**
	 * 現在の設定でCSVデータをファイルへ出力する
	 *
	 * @param string $path 出力先のファイルパス
	 * @return BaseIOCsvWriter
	 */
	public function writeToFile($path) {
		$handle = @fopen($path, 'w');
		if( ! $handle ) {
			throw new \Exception( "cannot open file ($path)" );
		}
		try {
			$header = $this->getColumnHeader();
			if( ! empty( $header ) ) {
				fwrite( $handle, $this->encodeRow( $header ) . $this->getLineDelimiter() );
			}
			foreach( $this->getRows() as $row ) {
				fwrite( $handle, $this->encodeRow( $row ) . $this->getLineDelimiter() );
			}
			fclose( $handle );
		} catch(\Exception $err) {
			if( $handle ) fclose( $handle );
			throw $err;
		}

		return $this;
	}

	/**
	 * 単純な一次配列を、設定された文字エンコードでカンマ区切りの行文字列に変換する
	 *
	 * @param array $row
	 * @return string
	 */
	protected function encodeRow($row) {
		$result = array();
		foreach($row as $col) {
			$d = (string)$col;
			if( ! $this->getColumnQuote() || preg_match( '/^-?(\d|([1-9]\d+))(\.\d+)?$/', $d ) ) {
				$result[] = $d;
			} else {
				$result[] = '"' . str_replace("\\'", "'", $this->_escape($d)) . '"';
			}
		}
		return mb_convert_encoding( join(',', $result), BaseIOCsvWriter::SJIS_CODE,BaseIOCsvWriter::UTF_CODE );
	}

	private function _escape($s) {
		$s = preg_replace('/"/', '""', $s);
		return $s;
	}
}