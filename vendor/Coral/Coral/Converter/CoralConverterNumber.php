<?php
namespace Coral\Coral\Converter;

/**
 * 入力値を数値と見なし、数値フォーマットを適用する{@link CoralConverterInterface}実装クラス
 */
class CoralConverterNumber implements CoralConverterInterface {
	/**
	 * デフォルトのフォーマット
	 * @var string
	 */
	const FORMAT_DEFAULT = '##0';

	/**
	 * 数値フォーマットを指定して、{@link CoralConverterNumber}の
	 * 新しいインスタンスを生成する
	 * @static
	 * @param null|string $format 数値フォーマット。デフォルトは'##0'
	 * @return CoralConverterInterface このクラスの新しいインスタンス
	 */
	public static function create($format = self::FORMAT_DEFAULT) {
		return new self($format);
	}

	/**
	 * フォーマット
	 * @var string
	 */
	protected $format;

	/**
	 * 数値フォーマットを指定して、{@link CoralConverterNumber}の
	 * 新しいインスタンスを初期化する
	 * @param null|string $format 数値フォーマット。デフォルトは'##0'
	 */
	public function __construct($format = self::FORMAT_DEFAULT) {
		if( empty($format) ) $format = self::FORMAT_DEFAULT;
		$this->format = $format;
	}

	/**
	 * 入力値を変換します。
	 * このクラスでは、入力値に指定の数値フォーマットを適用します
	 * @param mixed $value 入力値
	 * @return string $valueを変換した文字列
	 */
	public function convert($value) {
		$value = (int)$value;
		return empty($value) ? '' : f_nf($value, $this->format);
	}
}
