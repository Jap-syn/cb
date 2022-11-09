<?php
namespace models\Logic\Normalizer;
/**
 * 入力データの正規化を行うためのインターフェイス
 */
interface LogicNormalizerInterface {
	/**
	 * 入力文字に対して正規化処理を適用する
	 *
	 * @param string $input 入力文字列
	 * @return string 正規化適用後の文字列
	 */
	function normalize($input);
}
