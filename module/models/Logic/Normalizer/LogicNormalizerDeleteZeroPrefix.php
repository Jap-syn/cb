<?php
namespace models\Logic\Normalizer;

/**
 * 入力文字の先頭から連続する半角数字のゼロをすべて除去するための正規化フィルタ
 */
class LogicNormalizerDeleteZeroPrefix extends LogicNormalizerAbstract {
    /**
     * LogicNormalizerDeleteZeroPrefixの新しいインスタンスを初期化する
     */
    public function __construct() {
        // mb_convert_kana()は使用しない
        $this->_convert_options = array();
    }

	/**
	 * normalize()メソッドのメイン処理終了後に呼び出される終了処理。
	 * LogicNormalizerDeleteZeroPrefixでは、先頭の半角数字のゼロを
	 * すべて除去する
	 *
	 * @access protected
	 * @param string $input 終了処理を適用する文字列
	 * @return string 終了処理適用後の文字列
	 */
    protected function postNormalize($input) {
		mb_regex_encoding(mb_internal_encoding());
        return mb_ereg_replace('^0+', '', $input);
    }
}
