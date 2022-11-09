<?php
namespace models\Logic\Normalizer;

/**
 * 入力文字列を全角→半角変換した上で数字以外の文字をすべて除去する
 * 正規化フィルタ
 */
class LogicNormalizerDeleteNoNumeric extends LogicNormalizerAbstract {
	/**
	 * LogicNormalizerDeleteNoNumericの新しいインスタンスを初期化する
	 */
	public function __construct() {
		// 英数・記号・空白を半角変換
		$this->_convert_options = array(
			'as'
		);
	}

	/**
	 * normalize()メソッドのメイン処理終了後に呼び出される終了処理。
	 * LogicNormalizerDeleteNoNumericでは、半角数字以外のすべての文字を
	 * 除去する
	 *
	 * @access protected
	 * @param string $input 終了処理を適用する文字列
	 * @return string 終了処理適用後の文字列
	 */
	protected function postNormalize($input) {
		mb_regex_encoding(mb_internal_encoding());
		return mb_ereg_replace('[^\d]', '', $input);
	}
}
