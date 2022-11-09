<?php
namespace Coral\Coral\Converter;

/**
 * 入力値に日付フォーマットを適用する{@link CoralConverterInterface}実装クラス
 */
class CoralConverterDate extends CoralConverterDefault {
	/**
	 * デフォルトの日付フォーマット
	 * @var string
	 */
	const FORMAT_DEFAULT = 'yyyy/MM/dd';

	/**
	 * 日付フォーマットを指定して、{@link CoralConverterDate}の新しい
	 * インスタンスを生成する
	 * @static
	 * @param null|string $format 日付フォーマット。デフォルトは'yyyy/MM/dd'
	 * @return CoralConverterInterface このクラスの新しいインスタンス
	 */
	public static function create($format = self::FORMAT_DEFAULT) {
		return new self($format);
	}

	/**
	 * 日付フォーマット
	 * @var string
	 */
	protected $format;

	/**
	 * 日付フォーマットを指定して、{@link CoralConverterDate}の新しい
	 * インスタンスを初期化する
	 * @param null|string $format 日付フォーマット。デフォルトは'yyyy/MM/dd'
	 */
	public function __construct($format = self::FORMAT_DEFAULT) {
		if( empty($format) ) $format  = self::FORMAT_DEFAULT;
		$this->format = $format;
	}

	/**
	 * 入力値を変換します。
	 * このクラスでは、入力値に対して日付フォーマットを適用します
	 * @param mixed $value 入力値
	 * @return string $valueを変換した文字列
	 */
	public function convert($value) {
		$value = parent::convert($value);
		return empty($value) ? '' : f_df($value, $this->format);
	}
}
