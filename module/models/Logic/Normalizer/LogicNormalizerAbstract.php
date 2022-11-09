<?php
namespace models\Logic\Normalizer;

/**
 * 正規化フィルタのための基底抽象クラス
 *
 * @abstract
 */
abstract class LogicNormalizerAbstract implements LogicNormalizerInterface {
	/**
	 * このクラスの正規化処理で使用する、mb_convert_kana()向けのオプションを配列で
	 * 保持する
	 *
	 * @access protected
	 * @var array
	 */
	protected $_convert_options = array();

	/**
	 * 入力文字に対して正規化処理を適用する
	 *
	 * @param string $input 入力文字列
	 * @return string 正規化適用後の文字列
	 */
	public function normalize($input) {
		$input = $this->preNormalize($input);

		if(is_array($this->_convert_options) && !empty($this->_convert_options)) {
			foreach($this->_convert_options as $option) {
				// 空のオプションの場合はなにも処理しない
				if(strlen(trim(nvl($option, ''))) == 0) continue;
				$input = mb_convert_kana($input, $option, mb_internal_encoding());
			}
		}

		return $this->postNormalize($input);
	}

	/**
	 * normalize()メソッド適用直前に呼び出される準備処理。
	 * LogicNormalizerAbstractでは、文字列へのキャストとトリミングを行っている。
	 * 必要であれば派生クラスで適切にオーバーライドすること
	 *
	 * @access protected
	 * @param string $input 準備処理を適用する文字列
	 * @return string 準備処理適用後の文字列
	 */
	protected function preNormalize($input) {
		$input = nvl($input, '');
		return trim($input);
	}

	/**
	 * normalize()メソッドのメイン処理終了後に呼び出される終了処理。
	 * LogicNormalizerAbstractでは、入力文字列をそのまま返す実装になっているため、
	 * 必要であれば派生クラスで適切にオーバーライドすること
	 *
	 * @access protected
	 * @param string $input 終了処理を適用する文字列
	 * @return string 終了処理適用後の文字列
	 */
	protected function postNormalize($input) {
		return $input;
	}
}
