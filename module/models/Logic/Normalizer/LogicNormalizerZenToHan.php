<?php
namespace models\Logic\Normalizer;

/**
 * 全角の英数字・記号および空白文字を半角に変換する正規化フィルタ
 */
class LogicNormalizerZenToHan extends LogicNormalizerAbstract {
	/**
	 * LogicNormalizerZenToHanの新しいインスタンスを初期化する
	 */
	public function __construct() {
		// mb_convert_kanaのみで処理する
		$this->_convert_options = array(
			'askh'
		);
	}
}
