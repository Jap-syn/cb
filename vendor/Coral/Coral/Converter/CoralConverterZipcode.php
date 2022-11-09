<?php
namespace Coral\Coral\Converter;

use Coral\Base\BaseGeneralUtils;

/**
 * 郵便番号文字列の書式適用を行う{@link CoralConverterInterface}実装クラス
 */
class CoralConverterZipcode extends CoralConverterDefault {
	/**
	 * デフォルトの区切り文字
	 * @var string
	 */
	const SEPARATOR_DEFAULT = '-';

	/**
	 * 区切り文字を指定して、{@link CoralConverterZipcode}の
	 * 新しいインスタンスを生成する
	 * @static
	 * @param null|string $separator 区切り文字。デフォルトは'-'
	 * @return CoralConverterInterface このクラスの新しいインスタンス
	 */
	public static function create($separator = self::SEPARATOR_DEFAULT) {
		return new self($separator);
	}

	/**
	 * 区切り文字
	 * @var string
	 */
	protected $separator;

	/**
	 * 区切り文字を指定して、{@link CoralConverterZipcode}の
	 * 新しいインスタンスを初期化する
	 * @param null|string $separator 区切り文字。デフォルトは'-'
	 */
	public function __construct($separator = self::SEPARATOR_DEFAULT) {
		if( empty($separator) ) $separator = self::SEPARATOR_DEFAULT;
		$this->separator = $separator;
	}

	/**
	 * 入力値を変換します。
	 * このクラスでは、入力値を郵便番号へ整形します
	 * @param mixed $value 入力値
	 * @return string $valueを変換した文字列
	 */
	public function convert($value) {
		$value = BaseGeneralUtils::convertWideToNarrow($value);
		$value = preg_replace('/[^0-9]/', '', parent::convert($value));

		if( strlen($value) < 4 ) return $value;
		return join(
			$this->separator,
			array(
				substr($value, 0, 3),
				substr($value, 3 )
			)
		);
	}
}
