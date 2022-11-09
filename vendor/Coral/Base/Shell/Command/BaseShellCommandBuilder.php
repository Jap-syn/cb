<?php
namespace Coral\Base\Shell\Command;

require_once 'NetB/Reflection/Utility.php';

/**
 * @class
 *
 * シェルで実行するコマンドを組み立てるビルダークラス
 * 実行コマンド名を与え、引数を追加していく。
 * __toString()をオーバーライドしているため、ダブルクォートなどで
 * 展開するとexec()などで使用するためのコマンドが得られる
 */
class BaseShellCommandBuilder {
	/**
	 * @protected
	 *
	 * 実行コマンド名
	 * @var string
	 */
	protected $_command;

	/**
	 * @protected
	 *
	 * オプション引数の配列
	 * @var array
	 */
	protected $_options;

	/**
	 * @constructor
	 *
	 * NetB_Shell_Command_Builderの新しいインスタンスを初期化する
	 *
	 * @param string $command 実行するコマンドラインのコマンド
	 * @param null|array $options オプション引数
	 */
	public function __construct($command, $options = array()) {
		$this->_options = array();

		// $optionsがarrayでない場合
		if( ! is_array( $options ) ) {
			// $optionsがstringだった場合のみ、単独の引数として扱う
			$options = is_string( $options ) ? array( $options ) : array();
		}

		// コマンド設定
		$this->setCommand( $command );

		// 引数を設定
		foreach( $options as $key => $value ) {
			$this->setOption( $key, $value );
		}
	}

	/**
	 * 設定されている実行コマンドを取得する
	 *
	 * @return string
	 */
	public function getCommand() {
		return $this->_command;
	}
	/**
	 * 実行コマンドを設定する。コマンドにパスが通っていない場所へ
	 * 設置されている場合はフルパスで設定する必要がある。
	 *
	 * @param string $command 設定する実行コマンド
	 * @return NetB_Shell_Command_Builder
	 */
	public function setCommand($command) {
		$this->_command = "$command";

		return $this;
	}

	/**
	 * 設定されているすべての引数を配列で取得する。
	 * このメソッドが返す配列は、インスタンスが内部で保持する
	 * arrayのクローンである点に注意。
	 * 配列は連想配列として扱われ、非数のキーを持つ場合は引数のひとつ（または
	 * スイッチ）として扱われ、数値キーは値のみが引数として扱われる。
	 * 例えば、array( '-v' => 'hogehoge', 'fuga.txt' ) となっている場合は
	 * -v hogehoge fuga.txt という引数に展開される。
	 *
	 * @return array オプション引数配列のクローン
	 */
	public function getOptions() {
		return array_merge( array(), $this->_options );
	}
	/**
	 * 指定の配列をオプション引数の配列として設定する。
	 * このメソッドで配列を与えた場合、それまでに設定・追加された
	 * オプション引数はすべて破棄されるので注意。
	 *
	 * @param array $options 新しく設定するオプション引数配列
	 * @return NetB_Shell_Command_Builder
	 */
	public function setOptions(array $options) {
		$this->_options = $options;

		return $this;
	}

	/**
	 * 指定のキー（スイッチ）に一致する引数の値を取得する
	 *
	 * @param string $key 引数を取得する連想配列のキー
	 * @return $keyのスイッチに対応する引数値
	 */
	public function getOption($key) {
		return $this->_options[$key];
	}
	/**
	 * キーと値を指定してオプション引数を追加または更新する。
	 * 現在のオプション引数に$keyに一致するものが含まれている場合はその値が更新され、
	 * $keyが正の整数で且つそのキーが存在しない場合は値が追加される。
	 *
	 * @param string $key 引数のキー（スイッチ）
	 * @param string $value 引数の値
	 * @return NetB_Shell_Command_Builder
	 */
	public function setOption($key, $value = null) {
		if( $value !== null ) {
			$key = trim("$key");
			if( NetB_Reflection_Utility::isNonNegativeInteger($key) && $this->_options[$key] === null ) {
				$this->_options[] = "$value";
			} else {
				$this->_options[$key] = "$value";
			}
		} else {
			if( is_array( $key ) ) {
				foreach( $key as $k => $v ) {
					$this->setOption($k, $v);
				}
			} else {
				$this->_options[] = trim("$key");
			}
		}

		return $this;
	}

	/**
	 * NetB_Shell_Command_Builderのインスタンスの文字列表現を取得する。
	 * このメソッドが返す文字列はそのままコマンドラインで実行できる引数付のコマンドに
	 * なる。
	 *
	 * @return string
	 */
	public function __toString() {
		$buf = array( escapeshellcmd( $this->getCommand() ) );
		foreach( $this->getOptions() as $key => $value ) {
			$key = trim( "$key" );
			if( NetB_Reflection_Utility::isNonNegativeInteger($key) ) {
				// 数値インデックスなので値のみ採用
				$buf[] = escapeshellarg( trim( "$value" ) );
			} else {
				$buf[] = join( " ", array( escapeshellarg( $key ), escapeshellarg( trim("$value") ) ) );
			}
		}

		return join( " ", $buf );
	}
}

