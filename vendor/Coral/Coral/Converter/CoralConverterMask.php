<?php
namespace Coral\Coral\Converter;

/**
 * 入力値を指定のマスク文字でマスクする{@link CoralConverterInterface}実装クラス
 */
class CoralConverterMask implements CoralConverterInterface {
	/**
	 * デフォルトのマスク文字
	 * @var string
	 */
	const MASK_CHAR_DEFAULT = '*';

	/**
	 * マスク文字を指定して、{@link CoralConverterMask}の新しい
	 * インスタンスを生成する
	 * @static
	 * @param null|string $maskChar マスク文字
	 * @return CoralConverterInterface このクラスの新しいインスタンス
	 */
	public static function create($maskChar = self::MASK_CHAR_DEFAULT) {
		return new self($maskChar);
	}

	/**
	 * マスク文字
	 * @var string
	 */
	protected $maskChar;

	/**
	 * マスク文字を指定して、{@link CoralConverterMask}の新しい
	 * インスタンスを初期化する
	 * @param null|string $maskChar マスク文字
	 */
	public function __construct($maskChar = self::MASK_CHAR_DEFAULT) {
		if( empty($maskChar) ) $maskChar = self::MASK_CHAR_DEFAULT;
		$this->maskChar = $maskChar;
	}

	/**
	 * 入力値を変換します。
	 * このクラスでは、入力文字を指定のマスク文字でマスクします
	 * @param mixed $value 入力値
	 * @return string $valueを変換した文字列
	 */
	public function convert($value) {
		$buf = array();
		$len = strlen((string)$value);
		for($i = 0; $i < $len; $i++) {
			$buf[] = $this->maskChar;
		}
		return join('', $buf);
	}
}
