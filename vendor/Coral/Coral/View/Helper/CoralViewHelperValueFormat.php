<?php
namespace Coral\Coral\View\Helper;

/**
 * 整形出力を行うViewヘルパー。
 * 既定メソッド「valueFormat」は値、データ型、書式指定および空文字を全角空白に置換するかのフラグを
 * 指定し、書式設定を反映させた上でhtmlspecialchars()を適用した文字列を返す。
 * データ型には'string'（文字列・既定）、'number'（数値）、および'date'（日付）を指定できる
 *
 */
class CoralViewHelperValueFormat {
	/**
	 * valueFormatメソッドの$typeと実行するメソッドのマップ
	 * @static
	 * @var array
	 */
	private static $_methodMap = array(
		'string' => 'formatString',
	    'date' => 'formatDate',
		'int' => 'formatNumber',
		'float' => 'formatNumber',
		'real' => 'formatNumber',
		'single' => 'formatNumber',
		'double' => 'formatNumber',
		'decimal' => 'formatNumber',
		'number' => 'formatNumber'
	);

	/**
	 * valueFormat/espaceメソッドで$replaceEmptyを省略した場合に採用されるフラグ値
	 *
	 * @static
	 * @var bool
	 */
	private static $__replaceEmpty = false;

	/**
	 * 空データを置換する場合の置換文字列
	 *
	 * @static
	 * @var string
	 */
	private static $__replaceString = '&nbsp;';

	/**
	 * 日付型のフォーマット結果のキャッシュ
	 *
	 * @static
	 * @var array
	 */
	private static $__dateCache = array();

	/**
	 * 数値型のフォーマット結果のキャッシュ
	 *
	 * @static
	 * @var array
	 */
	private static $__numberCache = array();

	/**
	 * 指定の値を指定のデータ型として、書式を反映させた文字列として取得する。
	 * 第四引数には、空文字（またはnull）が指定された場合に全角空白に置換するかを指定できる。
	 *
	 * @param mixed $value 出力する値
	 * @param string|null $type データ型の指定。'string'、'number'、'date'を指定できる。
	 * @param string|null $format 書式指定文字列。$typeが'number'の場合はformatNumberを呼び、'date'の場合はdate、
	 *                       それ以外の場合はsprintfに使用する書式指定子を指定できる
	 * @param bool|null $replaceEmpty 空のデータ（長さ0の文字列またはnull）が指定された場合に置換を行うかの指定
	 * @param string|null $replaceString 空データを置換する場合に使用される置換文字列
	 * @return 書式指定を反映させた文字列。常にhtmlspecialchars()でエスケープされる。
	 */
	public function valueFormat($value = null, $type = 'string', $format = '', $replaceEmpty = null, $replaceString = null) {
	    $value = "$value";
		$type = strtolower( "$type" );
		$format = "$format";

		$method = isset(self::$_methodMap[ $type ]) ? self::$_methodMap[ $type ] : null;
		if( $method == null ) $method = 'formatString';

		return $this->escape(
			call_user_func( array( $this, $method ), $value, $format ),
			$replaceEmpty,
			$replaceString
		);
	}

	/**
	 * valueFormat()、escape()メソッドで引数'$replaceEmpty'を省略した場合の
	 * デフォルト値を取得する
	 *
	 * @return bool $replaceEmptyの省略時値
	 */
	public function getReplaceEmpty() {
		return self::$__replaceEmpty ? true : false;
	}

	/**
	 * valueFormat()、escape()メソッドで引数'$replaceEmpty'を省略した場合の
	 * デフォルト値を設定する
	 *
	 * @param bool|null $flag 空文字置換フラグ。省略時はfalse
	 * @return CoralViewHelperValueFormat
	 */
	public function setReplaceEmpty($flag = false) {
		self::$__replaceEmpty = $flag ? true : false;

		return $this;
	}

	/**
	 * 空のデータを置換する場合に使用される置換文字列を取得する
	 *
	 * @return string
	 */
	public function getReplaceString() {
		$result = self::$__replaceString;
		return "$result";
	}

	/**
	 * 空のデータを置換する場合に使用する置換文字列を設定する
	 *
	 * @param string $replaceString 設定する置換文字列
	 * @return CoralViewHelperValueFormat
	 */
	public function setReplaceString($replaceString) {
		self::$__replaceString = "$replaceString";

		return $this;
	}

	/**
	 * 指定の値を日付フォーマットの文字列に変換する
	 *
	 * @param mixed $value 日付データ
	 * @param string|null $format 書式指定文字列
	 * @return string 日付フォーマットされた文字列
	 */
	public function formatDate($value, $format) {
		if( empty($format) ) $format = 'Y/m/d';

		if( preg_match('/^\d+([-\/]\d+){2}([\sT]\d{1,2}(.\d{1,2}){0,2})?$/', $value ) ) {
			// キャッシュキーの作成
			$key = "$value$format";
			if( empty( self::$__dateCache[ $key ] ) ) {
				// キャッシュがない場合は作成
			    $value = date('Y/m/d', strtotime($value));
				self::$__dateCache[ $key ] = $value;
			} else {
				// キャッシュ値を採用
				$value = self::$__dateCache[ $key ];
			}
			return $value;
		}

		return $value;
	}

	/**
	 * 指定の値を数値フォーマットの文字列に変換する
	 *
	 * @param mixed $value 数値データ
	 * @param string|null $format 書式指定文字列。省略時は'#'が使用される。
	 * @return string 数値フォーマットされた文字列
	 */
	public function formatNumber($value, $format = '#') {
		if( empty($format) ) $format = '#';
		if( ! is_numeric( $value ) ) return $value;

		// キャッシュキーの作成
		$key = "$value$format";
		if( empty( self::$__numberCache[ $key ] ) ) {
			// キャッシュがない場合は作成
            if ($format == '#,##0') {
                $value = number_format($value);
            }
            else if ($format == '\ #,##0') {
                $value = ('\ ' . number_format($value));
            }
            else {
                $value = number_format($value);
            }
			self::$__numberCache[ $key ] = $value;
		} else {
			// キャッシュ値の採用
			$value = self::$__numberCache[ $key ];
		}
		return $value;
	}

	/**
	 * 指定の文字列をsprintfでフォーマットする。
	 *
	 * @param mixed $value フォーマットするデータ
	 * @param string|null $format 書式指定文字列。sprintfに使用するものを指定する
	 * @return $valueに$formatをsprintfで適用した文字列
	 */
	public function formatString($value, $format) {
		return empty( $format ) ? "$value" : sprintf( "$value", $format );
	}

	/**
	 * 指定の文字列をHTML特殊文字をエスケープして返す。
	 * 第二引数にtrueを指定すると、空文字やnullを全角の空白文字に置き換える
	 *
	 * @param mixed $str エスケープするデータ
	 * @param bool $replaceEmpty 空のデータを全角空白に置換するかを指定する
	 * @return HTML特殊文字がエスケープされた文字列
	 */
	public function escape($str, $replaceEmpty = null, $replaceString = null) {
		if( empty($replaceEmpty) && $replaceEmpty !== false ) {
			$replaceEmpty = $this->getReplaceEmpty();
		} else {
			$replaceEmpty= $replaceEmpty ? true : false;
		}

		$replaceString = "$replaceString";
		if( empty($replaceString) ) $replaceString = $this->getReplaceString();

		$result = htmlspecialchars( "$str" );
		if( strlen( $result ) == 0 && $replaceEmpty ) return $replaceString;
		return $result;
	}
}
