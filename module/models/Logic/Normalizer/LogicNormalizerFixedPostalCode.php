<?php
namespace models\Logic\Normalizer;

/**
 * 入力文字を郵便番号向けに正規化するための正規化フィルタ。
 * 全角→半角変換を適用し、数字以外を除去した上でゼロプレフィックスで7桁に
 * 整形する
 */
class LogicNormalizerFixedPostalCode extends LogicNormalizerDeleteNoNumeric {
	/**
	 * normalize()メソッドのメイン処理終了後に呼び出される終了処理。
	 * LogicNormalizerFixedPostalCodeでは、半角数字以外のすべての文字を
	 * 除去した上でゼロプレフィックスによる7桁整形を行う
	 *
	 * @access protected
	 * @param string $input 終了処理を適用する文字列
	 * @return string 終了処理適用後の文字列
	 */
	protected function postNormalize($input) {
        return sprintf('%07d', parent::postNormalize($input));
	}
}
