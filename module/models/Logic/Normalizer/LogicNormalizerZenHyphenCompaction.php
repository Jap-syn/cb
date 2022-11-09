<?php
namespace models\Logic\Normalizer;

/**
 * 連続する全角ハイフン記号を1文字に切り詰める正規化フィルタ
 */
class LogicNormalizerZenHyphenCompaction extends LogicNormalizerAbstract {
    /**
     * LogicNormalizerZenHyphenCompactionの新しいインスタンスを
     * 初期化する
     */
    public function __construct() {
        // mb_convert_kana()は使用しない
        $this->_convert_options = array();
    }

	/**
	 * normalize()メソッドのメイン処理終了後に呼び出される終了処理。
	 * LogicNormalizerHyphenCompactionでは、連続する全角ハイフンを
	 * 1文字に統合する
	 *
	 * @access protected
	 * @param string $input 終了処理を適用する文字列
	 * @return string 終了処理適用後の文字列
	 */
    protected function postNormalize($input) {
		mb_regex_encoding(mb_internal_encoding());
        return mb_ereg_replace('‐+', '‐', $input);
    }
}
