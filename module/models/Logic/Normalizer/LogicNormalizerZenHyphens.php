<?php
namespace models\Logic\Normalizer;

/**
 * 長音記号や全角ダッシュなどの文字を全角ハイフンに統一する
 * 正規化フィルタ
 */
class LogicNormalizerZenHyphens extends LogicNormalizerAbstract {
    /**
     * LogicNormalizerZenHyphensの新しいインスタンスを初期化する
     */
    public function __construct() {
        // mb_convert_kana()は使用しない
        $this->_convert_options = array();
    }

	/**
	 * normalize()メソッドのメイン処理終了後に呼び出される終了処理。
	 * LogicNormalizerHyphensHanToZenでは、全角長音記号、全角ダッシュ、
	 * 半角長音記号、半角マイナスをすべて全角ハイフンに統一する
	 *
	 * @access protected
	 * @param string $input 終了処理を適用する文字列
	 * @return string 終了処理適用後の文字列
	 */
    protected function postNormalize($input) {
		mb_regex_encoding(mb_internal_encoding());
        return mb_ereg_replace('[－ー―ｰ-]', '‐', $input);
    }
}
