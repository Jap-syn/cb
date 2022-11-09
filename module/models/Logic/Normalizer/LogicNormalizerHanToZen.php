<?php
namespace models\Logic\Normalizer;

/**
 * 入力文字列中の半角の英数字・記号および空白を全角に変換する正規化フィルタ
 */
class LogicNormalizerHanToZen extends LogicNormalizerAbstract {
	/**
	 * LogicNormalizerHanToZenの新しいインスタンスを初期化する
	 */
	public function __construct() {
		// mb_convert_kanaのみで処理する
		$this->_convert_options = array(
			'ASKV'
		);
	}
}
