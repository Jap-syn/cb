<?php
namespace Coral\Coral\CsvHandler;

/**
 * CoralCsvHandlerAbstractまたは派生クラスで、CSV行データを処理した結果を
 * 格納するクラス
 */
class CoralCsvHandlerLine {
	/**
	 * 通常のデータ行であることを示すデータ種別定数
	 *
	 */
	const TYPE_DATA = 'data';

	/**
	 * ヘッダ行であることを示すデータ種別定数
	 *
	 */
	const TYPE_HEADER = 'header';

	/**
	 * 警告データであることを示すデータ種別定数
	 *
	 */
	const TYPE_WARNING = 'warning';

	/**
	 * エラーデータであることを示すデータ種別定数
	 *
	 */
	const TYPE_ERROR = 'error';

	/**
	 * このインスタンスのデータ種別
	 *
	 * @var string
	 */
	protected $_type;

	/**
	 * 処理対象となったデータ
	 *
	 * @var mixed
	 */
	protected $_data;

	/**
	 * CSVファイル上での行位置
	 *
	 * @var int
	 */
	protected $_lineNumber;

	/**
	 * エラー情報(インスタンス生成時は空アレイ)
	 *
	 * @var array
	 */
	protected $_errors = array();

	/**
	 * エラー情報を取得する
	 *
	 * @return array
	 */
	public function getErrors() {
	    return $this->_errors;
	}

	/**
	 * エラー情報を設定する
	 *
	 * @param array $errors エラー情報
	 * @return CoralCsvHandlerLine
	 */
	public function setErrors($errors) {
	    $this->_errors = $errors;
	    return $this;
	}

	/**
	 * CoralCsvHandlerLineの新しいインスタンスを初期化する
	 *
	 * @param mixed $data 処理対象となったデータ
	 * @param int $line このデータの行位置。ファイルの先頭は0と考える
	 * @param string $type このデータのデータ種別
	 */
	public function __construct($data, $line, $type = self::TYPE_DATA) {
		if( ! is_int($line) ) throw new \Exception( 'please set line number.' );

		switch($type) {
			case self::TYPE_HEADER:
				// ヘッダ行指定があるが先頭行でない場合はデータ行として扱う
				if( $line != 0 ) $type = self::TYPE_DATA;
			case self::TYPE_ERROR:
			case self::TYPE_WARNING:
				$this->_type = $type;
				break;
			default:
				$this->_type = self::TYPE_DATA;
				break;
		}
		$this->_lineNumber = $line;
		$this->setData($data);
	}

	/**
	 * このインスタンスのデータ種別を取得する
	 *
	 * @return string
	 */
	public function getType() {
		return $this->_type;
	}

	/**
	 * このデータのファイル上での行位置を取得する。
	 * 戻り値は0ペースのインデックスで、ファイルの先頭行の場合は0を返す
	 *
	 * @return int
	 */
	public function getLineNumber() {
		return $this->_lineNumber;
	}

	/**
	 * 処理対象となった実データを取得する
	 *
	 * @return mixed
	 */
	public function getData() {
		return $this->_data;
	}

	/**
	 * 処理対象となった実データを設定する
	 *
	 * @param mixed $data 処理対象となった実データ。通常は検証後のCSV行データであるarrayを設定する
	 * @return CoralCsvHandlerLine
	 */
	public function setData($data) {
		$this->_data = $data;

		return $this;
	}
}