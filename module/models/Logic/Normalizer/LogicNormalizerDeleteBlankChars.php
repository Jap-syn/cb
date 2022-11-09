<?php
namespace models\Logic\Normalizer;

/**
 * 入力文字列中の空白文字（全角/半角問わず）を除去する正規化フィルタ。
 * 空白文字は全半角のスペースに加え、\r、\n、\tも対象となる
 */
class LogicNormalizerDeleteBlankChars extends LogicNormalizerAbstract {
    /**
     * LogicNormalizerDeleteBlankCharsの新しいインスタンスを
     * 初期化する
     */
    public function __construct() {
        // mb_convert_kana()では、全角スペースの半角変換のみ使用
        $this->_convert_options = array(
            's'
        );
    }

	/**
	 * normalize()メソッドのメイン処理終了後に呼び出される終了処理。
	 * LogicNormalizerDeleteBlankCharsでは、全角/半角スペースおよび
	 * タブ記号、改行記号が除去される
	 *
	 * @access protected
	 * @param string $input 終了処理を適用する文字列
	 * @return string 終了処理適用後の文字列
	 */
    protected function postNormalize($input) {
		mb_regex_encoding(mb_internal_encoding());
        return mb_ereg_replace('[\s]', '', $input);
    }
}
