<?php
namespace Coral\Coral\Converter;

/**
 * 入力値を真偽値と見なし、true/falseに適用する値へ変換する
 * {@link CoralConverterInterface}実装クラス。
 * 値のマッピングはコンストラクタパラメータでカスタマイズ可能。
 */
class CoralConverterBool implements CoralConverterInterface {
	/**
	 * true/falseにマッピングする値を指定して、{@link CoralConverterBool}の
	 * 新しいインスタンスを生成する
	 * @static
	 * @param null|mixed $true 値TRUEにマッピングする値。デフォルトは'TRUE'
	 * @param null|mixed $false 値FALSEにマッピングする値。デフォルトは'FALSE'
	 * @return CoralConverterInterface このクラスの新しいインスタンス
	 */
	public static function create($true = 'TRUE', $false = 'FALSE') {
		return new self($true, $false);
	}

	/**
	 * 値TRUEにマッピングする値
	 * @var mixed
	 * @access protected
	 */
	protected $true;

	/**
	 * 値FALSEにマッピングする値
	 * @var mixed
	 * @access protected
	 */
	protected $false;

	/**
	 * true/falseにマッピングする値を指定して、{@link CoralConverterBool}の
	 * 新しいインスタンスを初期化する
	 * @param null|mixed $true 値TRUEにマッピングする値。デフォルトは'TRUE'
	 * @param null|mixed $false 値FALSEにマッピングする値。デフォルトは'FALSE'
	 */
	public function __construct($true = 'TRUE', $false = 'FALSE') {
		$this->true = $true;
		$this->false = $false;
	}

	/**
	 * 入力値を変換します。
	 * このクラスでは、入力を真偽値と見なし、初期化時に指定したマッピング値へ変換します
	 * @param mixed $value 入力値
	 * @return string $valueを変換した文字列
	 */
	public function convert($value) {
		return $value ? $this->true : $this->false;
	}
}
