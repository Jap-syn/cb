<?php
namespace Coral\Coral\Converter;


/**
 * 単純な入力→出力変換を規定するインターフェイス。
 * インターフェイスによる規定はないが、実装クラスはコンストラクタと同一の
 * パラメータで定義された静的メソッド「create」を実装する必要がある
 */
interface CoralConverterInterface {
	/**
	 * 入力値を変換します
	 * @param mixed $value 入力値
	 * @return string $valueを変換した文字列
	 */
	function convert($value);
}
