<?php
namespace Coral\Coral\Converter;

/**
 * 改行記号を別の文字へ置換する{@link CoralConverterInterface}実装クラス
 */
class CoralConverterFlatLine extends CoralConverterDefault {
	/**
	 * デフォルトの置換文字
	 * @var string
	 */
	const REPLACEMENT_DEFAULT = ' ';

	/**
	 * 改行を置換する文字を指定して、{@link CoralConverterInterface}の
	 * 新しいインスタンスを生成する
	 * @static
	 * @param null|string $replacement 改行を置換する文字
	 * @return CoralConverterInterface このクラスの新しいインスタンス
	 */
	public static function create($replacement = ' ') {
		return new self($replacement);
	}

	/**
	 * 置換文字
	 * @var string
	 */
	protected $_replacement;

	/**
	 * 改行を置換する文字を指定して、{@link CoralConverterInterface}の
	 * 新しいインスタンスを初期化する
	 * @param null|string $replacement 改行を置換する文字
	 */
	public function __construct($replacement = ' ') {
		$this->_replacement = $replacement;
	}

	/**
	 * 入力値を変換します。
	 * このクラスでは、入力値に含まれる改行記号を、設定した置換文字で置換します
	 * @param mixed $value 入力値
	 * @return string $valueを変換した文字列
	 */
	public function convert($value) {
		$value = preg_replace('/((\r\n)|[\r\n])/', $this->_replacement, $value);
		return parent::convert($value);
	}
}
