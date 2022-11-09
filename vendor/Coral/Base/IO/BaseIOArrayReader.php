<?php
namespace Coral\Base\IO;

use Coral\Base\Reflection\BaseReflectionUtility;

/**
 * 配列を読み取りパススルーで配列に展開するBaseIOCsvReader派生クラス
 *
 */
class BaseIOArrayReader extends BaseIOCsvReader {
	/**
	 * コールバックメソッド情報
	 *
	 * @var string|array
	 */
	protected $_callback;

	/**
	 * 読み込み対象の配列データ
	 *
	 * @var array
	 */
	protected $_array;

	/**
	 * BaseIOArrayReaderの新しいインスタンスを初期化する
	 *
	 * @param array $array_data 読み込む配列
	 * @param string|array|null $callback コールバック関数情報。{@link call_user_func}に適用できる形式である必要がある
	 * @param bool|null $skipEmpty 空行をスキップするかのフラグ。省略時はtrue
	 */
	public function __construct(array $array_data, $callback = null, $skipEmpty = true) {
		$this
			->setBaseArray($array_data)
			->setCallback($callback)
			->setSkipEmpty($skipEmpty);
	}

	/**
	 * 処理対象のCSVファイルのパスを取得する。
	 * このメソッドは互換性維持のために設置されたダミーメソッドである
	 *
	 * @return string
	 */
	public function getPath() {
		return null;
	}

	/**
	 * 処理対象のCSVファイルのパスを設定する。
	 * このメソッドは互換性維持のために設置されたダミーメソッドである
	 *
	 * @param string $path 処理対象のCSVファイルのパス
	 * @return BaseIOArrayReader
	 */
	public function setPath($path) {
		return $this;
	}

	/**
	 * 読取対象として指定された配列データを取得する
	 *
	 * @return array
	 */
	public function getBaseArray() {
		return $this->_array;
	}

	/**
	 * 読取対象の配列データを設定する
	 *
	 * @param array $array_data 配列データ
	 * @return BaseIOArrayReader
	 */
	public function setBaseArray(array $array_data) {
		$this->_array = $array_data;
		return $this;
	}

	/**
	 * 設定された配列データを行単位で読み取り、新たな配列に格納する。
	 * デフォルトの設定のまま実行した場合、空行を除いて元の配列データと同じ内容が格納される
	 * コールバックメソッドが設定されている場合はコールバックメソッド内で加工した配列を返すことで読み込み結果を加工することができる。
	 *
	 * @return array
	 */
	public function read() {
		$result = array();

		$continue = true;
		$cb = $this->getCallback();
		$skipEmpty = $this->getSkipEmpty();

		try {
			foreach($this->getBaseArray() as $line => $row) {
				if($skipEmpty) {
					if(empty($row) || !count($row)) {
						$row = null;
					} else if(count($row) == 1 && empty($row[0])) {
						$row = null;
					}
				}
				if($cb != null && ($row || ! $skipEmpty)) {
					// コールバックを適用
 					$cmd = call_user_func($cb, $row, $line, $this);

					if(is_array($cmd)) {
						// コールバックが配列を返したら、それを行に置き換える
						$row = $cmd;
					} else {
						// コマンド定数が返されたら処理を制御
						// それ以外の場合は特別な処理はしない
						switch($cmd) {
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
		} catch(\Exception $err) {
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
			sprintf('base array: %s', var_export($this->getBaseArray(), true)),
 			'callback: ' . BaseReflectionUtility::getCallbackString( $this->getCallback() )
 		);

		return join( "\n", $result );
	}
}

