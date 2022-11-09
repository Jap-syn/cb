<?php
namespace Coral\Base\IO;

use Coral\Base\Reflection\BaseReflectionUtility;

/**
 * CSVを読み込み配列に展開するクラス。
 * 1行読み込むごとにコールバックメソッドを適用できる。
 *
 */
class BaseIOCsvReader {
	/**
	 * コールバックメソッドが返すコマンド定数。現在行をスキップし、結果に含めないように指示する
	 */
	const COMMAND_SKIP_LINE = 1;

	/**
	 * コールバックメソッドが返すコマンド定数で、読み込み処理を中断するよう指示する。現在行は結果に含まれない。
	 */
	const COMMAND_STOP_READING = 11;

	/**
	 * コールバックメソッドが返すコマンド定数で、現在行を結果に含めた後読み込み処理を中断するように指示する。
	 */
	const COMMAND_STOP_AFTER_READING = 12;

	/**
	 * コールバックメソッド情報
	 *
	 * @var string|array
	 */
	protected $_callback;

	/**
	 * 読み込むCSVファイルのパス
	 *
	 * @var string
	 */
	protected $_path;

	/**
	 * 空行をスキップするかのフラグ
	 *
	 * @var bool
	 */
	protected $_skipEmpty;

	/**
	 * BaseIOCsvReaderの新しいインスタンスを初期化する
	 *
	 * @param string $path 読み込むCSVファイルのパス
	 * @param string|array|null $callback コールバック関数情報。{@link call_user_func}に適用できる形式である必要がある
	 * @param bool|null $skipEmpty 空行をスキップするかのフラグ。省略時はtrue
	 */
	public function __construct($path, $callback = null, $skipEmpty = true) {
		$this->setPath( $path )
			->setCallback( $callback )
			->setSkipEmpty( $skipEmpty );
	}

	/**
	 * 処理対象のCSVファイルのパスを取得する
	 *
	 * @return string
	 */
	public function getPath() {
		return $this->_path;
	}

	/**
	 * 処理対象のCSVファイルのパスを設定する。
	 * コールバックメソッド内から設定を変更しても、元のread()メソッドの挙動には影響を与えない
	 *
	 * @param string $path 処理対象のCSVファイルのパス
	 * @return BaseIOCsvReader
	 */
	public function setPath($path) {
		if( ! BaseIOUtility::pathEquals( $path, $this->_path ) )
			$this->_path = $path;

		return $this;
	}

	/**
	 * 行読み込み時に適用されるコールバックメソッドを取得する。
	 * 戻り値の形式は{@link call_user_func}関数に使用する形式で、
	 * 文字列（コールバック関数）または配列（array(string|object, methodname)）の形式になる
	 *
	 * @return string|array|null
	 */
	public function getCallback() {
		return $this->_callback;
	}

	/**
	 * 行読み込み時に適用されるコールバックメソッドを設定する。
	 * 戻り値の形式は{@link call_user_func}関数に使用する形式で、
	 * 文字列（コールバック関数）または配列（array(string|object, methodname)）の形式になる
	 *
	 * コールバックメソッドは3つの引数（ファイルから読み込まれた1行分の配列、処理行数（0ペース）、BaseIOCsvReaderのインスタンス）を
	 * 受け取ることができ、このクラスで定義されているコマンド引数を返すことで処理を制御（現在行のスキップ、読み込みの中止、等）する
	 * ことができる。
	 * さらにarrayを返すことでread()メソッドが返す結果の配列の要素を加工することができ、例えば先頭行をラベルにした連想配列に
	 * 変換するなどの応用ができる。
	 *
	 * @param string|array|null $callback コールバックメソッド情報。{@link call_user_func}に指定する形式である必要がある
	 * @return BaseIOCsvReader
	 */
	public function setCallback($callback) {
		if( $callback != null ) {
		    if( ! BaseReflectionUtility::isCallback($callback) ) {
				// 形式が不正
				throw new \Exception( 'invalid arguments. please set string or array( string|object, string ).' );
			}
		}

		$this->_callback = $callback;

		return $this;
	}

	/**
	 * 空行をスキップするかのフラグを取得する
	 *
	 * @return bool
	 */
	public function getSkipEmpty() {
		return $this->_skipEmpty ? true : false;
	}

	/**
	 * 空行をスキップするかを設定する。明示的にfalseを設定した場合のみ、空行も処理される
	 *
	 * @param bool $skip 空行をスキップするかのフラグ。省略時はtrueで、空行をスキップする
	 * @return BaseIOCsvReader
	 */
	public function setSkipEmpty($skip = true) {
		$skip = $skip ? true : false;
		$this->_skipEmpty = $skip;

		return $this;
	}

	/**
	 * CSVファイルを読み込み、配列に格納する。
	 * デフォルトの設定のまま実行した場合、空行を除いてファイルの先頭から最後まで{@link fgetcsv}を実行してジャグ配列を作成したものと同じ結果になる。
	 * コールバックメソッドが設定されている場合はコールバックメソッド内で加工した配列を返すことで読み込み結果を加工することができる。
	 *
	 * @return array
	 */
	public function read() {
		$result = array();
		$handle = @fopen( $this->getPath(), "r" );
		if( ! $handle ) throw new \Exception( "file '" . $this->getPath() . "' could not open." );
		$continue = true;
		$line = 0;
		$cb = $this->getCallback();
		$skipEmpty = $this->getSkipEmpty();
		try {
			while( ! feof( $handle ) && $continue ) {
				$row = fgetcsv( $handle, 0, ',', '"' );

				if( $skipEmpty ) {
					if( empty( $row ) ) {
						$row = null;
					} else if( count( $row ) == 1 && empty( $row[0] ) ) {
						$row = null;
					}
				}

				if( $cb != null && ( $row || ! $skipEmpty ) ) {
					// コールバックを適用
 					$cmd = call_user_func_array( $cb, array($row, $line++, $this));

					if( is_array( $cmd ) ) {
						// コールバックが配列を返したら、それを行に置き換える
						$row = $cmd;
					} else {
						// コマンド定数が返されたら処理を制御
						// それ以外の場合は特別な処理はしない
						switch( $cmd ) {
							case self::COMMAND_STOP_READING:
							case self::COMMAND_STOP_AFTER_READING:
								// 停止命令
								$continue = false;
								// COMMAND_STOP_READING は現在行も廃棄する
								if( $cmd == self::COMMAND_STOP_READING ) $row = null;
								break;
							case self::COMMAND_SKIP_LINE:
								// スキップ命令
								$row = null;
								break;
						}
					}
				}

				if( $row != null ) $result[] = $row;
			}
			fclose( $handle );
		} catch(\Exception $err) {
			if( $handle ) {
				fclose( $handle );
			}
			throw $err;
		}
		return $result;
	}

	/**
	 * __toString
	 *
	 * @return string
	 */
	public function __toString() {
		$result = array(
			"path: {$this->getPath()}",
		    'callback: ' . BaseReflectionUtility::getCallbackString($this->getCallback())
 		);

		return join( "\n", $result );
	}
}

