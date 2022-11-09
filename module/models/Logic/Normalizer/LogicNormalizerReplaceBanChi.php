<?php
namespace models\Logic\Normalizer;

/**
 * 入力文字列中の「丁目」や「番」、「地」、「号」などの文字を
 * 全角ハイフンに置き換える正規化フィルタ
 */
class LogicNormalizerReplaceBanChi extends LogicNormalizerAbstract {
    /**
     * LogicNormalizerReplaceBanChiの新しいインスタンスを初期化する
     */
    public function __construct() {
        $this->_convert_options = array();
    }

	/**
	 * normalize()メソッドのメイン処理終了後に呼び出される終了処理。
	 * LogicNormalizerReplaceBanChiでは、「丁目」「番」「地」および「号」などの
	 * 文字を全角マイナスに置換する
	 *
	 * @access protected
	 * @param string $input 終了処理を適用する文字列
	 * @return string 終了処理適用後の文字列
	 */
    protected function postNormalize($input) {
		mb_regex_encoding(mb_internal_encoding());
        $input = mb_ereg_replace('丁目', '－', $input);
        $input = mb_ereg_replace('[番地]', '－', $input);
		$input = mb_ereg_replace('号室?', '', $input);
		return $input;
    }
}
