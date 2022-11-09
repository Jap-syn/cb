<?php
namespace Coral\Base;

/**
 * ユーティリティクラス
 */
class BaseUtility {
	/**
	 * ランダムなファイル名に安全に含めることができる文字の集合
	 */
	const FILENAME_SEED_CHARS = '0123456789abcdefghijklmnopqrstuvwxyz-_';

	/**
	 * 指定桁数のランダムな文字列を作成する。作成される文字列は
	 * 0～9、a～zおよびハイフンとアンダースコアのみで構成され、ファイル名として安全に使用できる
	 *
	 * @param int $len 作成する文字列の長さ。省略時は8文字で作成される
	 * @return string 長さが$lenに一致するランダムな文字列
	 */
	public static function createRandomString($len = 8) {
		$buf = array();
		$seed_len = strlen( self::FILENAME_SEED_CHARS ) - 1;

		$len = (int)$len;
		if( $len <= 0 ) $len = 8;

		for($i = 0; $i < $len; $i++) {
			$buf[$i] = substr( self::FILENAME_SEED_CHARS, rand(0, $seed_len), 1 );
		}

		return join( '', $buf );
	}

	/**
	 * 指定の文字列をバイト配列に変換する
	 *
	 * @param string $s 変換する文字列
	 * @return array $sを変換した配列。各要素はバイトコードを示す数値が格納される
	 */
	public static function stringToBytes($s) {
		$result = array();
		$arr = str_split( "$s" );
		for($i = 0, $l = count($arr); $i < $l; $i++) {
			$result[] = ord( $arr[ $i ] );
		}
		return $result;
	}

	/**
	 * MySQLでLIKEを発行できるよう入力文字列をエスケープする
	 * エスケープする内容は通常のZend_Db_Adapter_Abstract::quote()とは以下の点が異なる。
	 * ・ワイルドカード文字（%および_）もバックスラッシュエスケープする
	 * ・バックスラッシュ自体は通常の2重バックスラッシュではなく4重バックスラッシュにエスケープする
	 * ・（quoteではないので）前後に引用符は付加しない
	 * @param string $s
	 * @return string
	 */
	public static function escapeWildcard($s) {
		// 事前にバックスラッシュを2重化してからaddcslashesを行う
		// → addcslashesのパラメータはZend_Db_Adapter_Abstract::quote()と同一
		return addcslashes(str_replace("\\", "\\\\", $s), "\000\r\n\\'\"\032%_");
	}
}
