<?php
namespace Coral\Coral\Converter;

use Coral\Base\Reflection\BaseReflectionUtility;

/**
 * 入力値に対して10を底とする指数と出力時の桁数を指定して、パーセント相当の実数に変換する
 * {@link CoralConverterInterface}実装クラス
 */
class CoralConverterPercent implements CoralConverterInterface {
	/**
	 * デフォルトの補正指数
	 * @var int
	 */
	const EXP_DEFAULT = 5;

	/**
	 * デフォルトの桁数
	 * @var int
	 */
	const DIGIT_DEFAULT = 5;

	/**
	 * 10を底とした補正指数と桁数を指定して、{@link CoralConverterPercent}の新しい
	 * インスタンスを生成する。
	 * 入力値は指定指数分だけ10^-1され、結果を小数点以下が指定桁数の実数表記で返す。
	 * ex.$exp = 5、$digit = 5、$value = 9300500 → 93.005
	 * ex.$exp = 2、$digit = 3、$value = 12341 → 1.234
	 * @param null|int $exp 入力桁の補正に使用する指数。デフォルトは5
	 * @param null|int $digit 桁数。デフォルトは5
	 * @return CoralConverterInterface このクラスの新しいインスタンス
	 */
	public static function create($exp = self::EXP_DEFAULT, $digit = self::DIGIT_DEFAULT) {
		return new self($exp, $digit);
	}

	/**
	 */
	protected $exp;

	/**
	 * 桁数
	 * @var int
	 */
	protected $digit;

	/**
	 * 10を底とした補正指数と桁数を指定して、{@link CoralConverterPercent}の新しい
	 * インスタンスを初期化する
	 * 入力値は指定指数分だけ10^-1され、結果を小数点以下が指定桁数の実数表記で返す。
	 * ex.$exp = 5、$digit = 5、$value = 9300500 → 93.005
	 * ex.$exp = 2、$digit = 3、$value = 12341 → 1.234
	 * @param null|int $exp 入力桁の補正に使用する指数。デフォルトは5
	 * @param null|int $digit 桁数。デフォルトは5
	 * @param null|int $digit 桁数。デフォルトは5
	 */
	public function __construct($exp = self::EXP_DEFAULT, $digit = self::DIGIT_DEFAULT) {
		if( ! BaseReflectionUtility::isPositiveInteger($exp) ) {
			$exp = self::EXP_DEFAULT;
		}
		if( ! BaseReflectionUtility::isPositiveInteger($digit) ) {
			$digit = self::DIGIT_DEFAULT;
		}
		$this->exp = $exp;
		$this->digit = $digit;
	}

	/**
	 * 入力値を変換します。
	 * このクラスでは、入力値を設定指数に応じて10^-1し、小数点以下が指定桁数の実数に変換します
	 * @param mixed $value 入力値
	 * @return string $valueを変換した文字列
	 */
	public function convert($value) {
		$value = ((int)$value) * pow(10, -1 * $this->exp);
		$format = '%01.' . ($this->digit) . 'f';
		return sprintf($format, $value);
	}
}
