<?php
namespace models\Logic\Normalizer;

/**
 * 入力文字列中のひらがなを全角カタカナへ変換する正規化フィルタ
 */
class LogicNormalizerHiraToKata extends LogicNormalizerAbstract {
    /**
     * LogicNormalizerHiraToKataの新しいインスタンスを初期化する
     */
    public function __construct() {
		// mb_convert_kanaのみで処理する
		$this->_convert_options = array(
			'C'
		);
    }
}
