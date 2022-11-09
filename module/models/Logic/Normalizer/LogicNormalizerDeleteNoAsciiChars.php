<?php
namespace models\Logic\Normalizer;

/**
 * 印字可能ASCII文字（0x20 - 0x7e）以外の文字を除去する正規化フィルタ
 */
class LogicNormalizerDeleteNoAsciiChars extends LogicNormalizerAbstract {
	/**
	 * LogicNormalizerDeleteNoAsciiCharsの新しい
	 * インスタンスを初期化する
	 */
	public function __construct() {
		// mb_convert_kana()は使用しない
		$this->_convert_options = array();
	}

	/**
	 * normalize()メソッドのメイン処理終了後に呼び出される終了処理。
	 * LogicNormalizerDeleteNoAsciiCharsでは、印字可能ASCII文字以外の
	 * 文字をすべて除去する
	 *
	 * @access protected
	 * @param string $input 終了処理を適用する文字列
	 * @return string 終了処理適用後の文字列
	 */
	protected function postNormalize($input) {
		mb_regex_encoding(mb_internal_encoding());
		return mb_ereg_replace('[^\x21-\x7e]', '', $input);
	}
}
