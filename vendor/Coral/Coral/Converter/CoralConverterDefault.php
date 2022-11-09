<?php
namespace Coral\Coral\Converter;

/**
 * 入力データを文字列として、前後の空白除去のみ適用する{@link CoralConverterInterface}実装クラス
 */
class CoralConverterDefault implements CoralConverterInterface {
	/**
	 * {@link CoralConverterDefault}の新しいインスタンスを生成する
	 * @static
	 * @return CoralConverterInterface このクラスの新しいインスタンス
	 */
	public static function create() {
		return new self();
	}

	/**
	 * {@link CoralConverterDefault}の新しいインスタンスを初期化する
	 */
	public function __construct() {
	}

	/**
	 * 入力値を変換します。
	 * このクラスでは、入力を文字列と見なし、前後の空白除去のみ適用します
	 * @param mixed $value 入力値
	 * @return string $valueを変換した文字列
	 */
	public function convert($value) {
		$value = (string)$value;
		return trim($value);
	}
}
