<?php
namespace Coral\Coral\CsvHandler;

use Coral\Base\IO\BaseIOCsvReader;

/**
 * BaseIOCsvReaderを使用して、CSVファイルの読み取りと検証、および任意のデータ構築を行う為の抽象クラス。
 * 基本的に{@link validateLine}抽象メソッドをオーバーライドするが、
 * 正常に処理できたデータは{@link addResult}メソッドを利用して登録する必要がある。
 *
 */
abstract class CoralCsvHandlerAbstract {
	/**
	 * @var BaseIOCsvReader
	 */
	protected $_reader;

	/**
	 * 処理済みの全データを格納する配列。各要素はCoralCsvHandlerLineのみ
	 *
	 * @var array
	 */
	protected $_results;

	/**
	 * CSVファイルを読み取るBaseIOCsvReaderを指定して、CoralCsvHandlerAbstractの新しいインスタンスを初期化する
	 *
	 * @param BaseIOCsvReader $reader CSV読み取りを行うリーダー
	 */
	public function __construct(BaseIOCsvReader $reader, array $options = array()) {
		$this->_results = array();

		$this
			->setReader( $reader )
			->init( $options );
	}

	/**
	 * CSVデータを読み取るBaseIOCsvReaderを取得する
	 *
	 * @return BaseIOCsvReader
	 */
	public function getReader() {
		return $this->_reader;
	}

	/**
	 * CSVデータを読み取るBaseIOCsvReaderを設定する
	 *
	 * @param BaseIOCsvReader $reader
	 * @return CoralCsvHandlerAbstract
	 */
	public function setReader(BaseIOCsvReader $reader) {
		$this->_reader = $reader;

		$this->_reader->setCallback( array( $this, 'validateLine' ) );

		return $this;
	}

	/**
	 * BaseIOCsvReaderの行読み取りをハンドルするコールバックメソッド
	 *
	 * @param array $rwo ファイルから読み取られた1行分のデータの配列
	 * @param int $line 0から始まる処理行数カウンタ。ファイルの先頭の行は0になる
	 * @param BaseIOCsvReader $reader CSVファイルを読み取り処理しているBaseIOCsvReader
	 * @return array 処理済みの行データ。_preValidateおよび_postValidateで処理された実データを返す
	 */
	public final function validateLine(array $row, $line, BaseIOCsvReader $reader) {

		// 行の前処理
		$row = $this->_preValidate( $row, $line );

		// 検証処理
		$result = $this->_validate( $row, $line );

		// 検証処理の戻りはCoralCsvHandlerLine以外受け付けない
		if( ! ( $result instanceof CoralCsvHandlerLine ) ) {
		    throw new \Exception( "invalid implementation: method '_validate' must return 'CoralCsvHandlerLine'." );
		}

		// 後処理
		$result = $this->_postValidate( $result );
		if( ! ( $result instanceof CoralCsvHandlerLine ) ) {
			throw new \Exception( "invalid implementation: method '_postValidate' must return 'CoralCsvHandlerLine'." );
		}

		// 結果をスタック
		$this->_results[] = $result;

		// 呼び出し元のBaseIOCsvReaderへ行データを返す
		return $result->getData();
	}

	/**
	 * CSV行データを検証・加工する。派生クラスは必ずこのメソッドをオーバーライドする必要がある。
	 * 派生クラスでは、オーバーライドしたこのメソッド内でデータのスキーマや設定値の検証を行い、
	 * その結果が正常であるか・エラーであるかなどの情報を含むCoralCsvHandlerLineにラップして返す必要がある。
	 *
	 * @param array $row ファイルから読み取られた1行分のデータの配列
	 * @param int $line 処理行数。0から開始される
	 * @return CoralCsvHandlerLine 1行の処理結果を格納したCoralCsvHandlerLine
	 */
	abstract protected function _validate(array $row, $line);

	/**
	 * 派生クラスでオーバーライドすると、CSV行データに対して検証前処理を行うことができる。
	 * このメソッドの戻り値がそのまま_validateメソッドの第一引数に使用される。
	 *
	 * @param array $row 処理するCSV行データ
	 * @param int $line 処理行カウンタ。0ペース。
	 * @return array 下処理を施したCSV行データを示すarray
	 */
	protected function _preValidate(array $row, $line) {
		return $row;
	}

	/**
	 * 派生クラスでオーバーライドすると、_validateで作成されたCoralCsvHandlerLineに対して
	 * データの置換処理などを行うことができる。データの置き換えなどが不要な場合はオーバーライドせず
	 * この実装をそのまま採用する必要がある。
	 *
	 * @param CoralCsvHandlerLine $lineData _validateメソッドで生成された、検証済みのデータを含むCoralCsvHandlerLine
	 * @return CoralCsvHandlerLine
	 */
	protected function _postValidate(CoralCsvHandlerLine $lineData) {
		return $lineData;
	}

	/**
	 * 派生クラスでオーバーライドすると、クラス固有の初期化処理を行うことができる
	 */
	protected function init(array $options) {
		// TODO: 固有の初期化処理を実装してください
	}

	/**
	 * 現在の設定でCSV読み取りを開始し、データを構築する
	 *
	 * @return bool エラー・警告ともに0件の場合はtrue、それ以外はfalse
	 */
	public function exec() {
		$this->_results = array();
		$this->begin();
		$result = $this->getReader()->read();
		$this->end( $result );
		return ( ( count( $this->getExceptions() ) == 0 ) && ( count( $this->getWarnings() ) == 0 ) ) ? true : false;
	}

	/**
	 * 処理済みのすべてのデータを取得する。
	 * 戻り値の各要素はすべてCoralCsvHandlerLineになっている
	 *
	 * @return array
	 */
	public function getAllResults() {
		if( ! is_array( $this->_results ) ) return array();
		return $this->_results;
	}

	/**
	 * 処理結果にヘッダ行が含まれているかを判断する
	 *
	 * @return bool 処理結果にヘッダ行が含まれている場合はtrue、それ以外はfalse
	 */
	public function hasHeader() {
		return ( $this->getHeader() != null ) ? true : false;
	}
	/**
	 * 処理結果からヘッダ行を取得する
	 *
	 * @return CoralCsvHandlerLine|null
	 */
	public function getHeader() {
		$rows = $this->getAllResults();
		$line = $rows[0];
		if(isset($line) && $line->getType() == CoralCsvHandlerLine::TYPE_HEADER ) return $line;

		return null;
	}

	/**
	 * 処理中に発生したエラー情報を取得する
	 *
	 * @return array
	 */
	public function getExceptions() {
		$result = array();
		foreach( $this->getAllResults() as $line ) {
		    if( $line->getType() == CoralCsvHandlerLine::TYPE_ERROR ) $result[] = $line;
		}
		return $result;
	}

	/**
	 * 処理中に発生した警告情報を取得する
	 *
	 * @return array
	 */
	public function getWarnings() {
		$result = array();
		foreach( $this->getAllResults() as $line ) {
		    if( $line->getType() == CoralCsvHandlerLine::TYPE_WARNING ) $result[] = $line;
		}
		return $result;
	}

	/**
	 * 正常に処理されたデータを取得する。戻り値にヘッダ行や警告行およびエラー行は含まれない
	 *
	 * @return array
	 */
	public function getResults() {
		$result = array();
		foreach( $this->getAllResults() as $line ) {
		    if( $line->getType() == CoralCsvHandlerLine::TYPE_DATA ) $result[] = $line;
		}
		return $result;
	}

	/**
	 * 派生クラスでオーバーライドすると、CSV処理の開始直前にカスタム処理を実装できる
	 *
	 */
	protected function begin() {
		// TODO: CSV処理前のカスタム処理を実装してください
	}

	/**
	 * 派生クラスでオーバーライドすると、CSV処理終了後にカスタム処理を実装できる
	 *
	 * @param array $result BaseIOCsvReader->read()メソッドの戻り値
	 */
	protected function end(array $result) {
		// TODO: CSV処理終了後のカスタム処理を実装してください
	}
}