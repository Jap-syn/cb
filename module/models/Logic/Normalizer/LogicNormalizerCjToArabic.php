<?php
namespace models\Logic\Normalizer;

/**
 * 入力文字列に含まれる漢数字を全角のアラビア数字に置き換える正規化フィルタ。
 * 変換精度を高めるために、このフィルタは内部でLogicNormalizerReplaceBanChi、
 * LogicNormalizerHyphensおよびLogicNormalizerHyphenCompactionを適用している
 */
class LogicNormalizerCjToArabic extends LogicNormalizerAbstract {
	/**
	 * LogicNormalizerCjToArabicの新しいインスタンスを
	 * 初期化する
	 */
	public function __construct() {
		// mb_convert_kana()は使用しない
		$this->_convert_options = array();
	}

	/**
	 * normalize()メソッドのメイン処理終了後に呼び出される終了処理。
	 * LogicNormalizerCjToArabicでは、漢数字を全角アラビア数字に
	 * 置き換える
	 *
	 * @access protected
	 * @param string $input 終了処理を適用する文字列
	 * @return string 終了処理適用後の文字列
	 */
	protected function postNormalize($input) {
		mb_regex_encoding(mb_internal_encoding());

		// 先行フィルタの適用
		foreach(array(
			new LogicNormalizerReplaceBanChi(),
			new LogicNormalizerHyphens(),
			new LogicNormalizerHyphenCompaction()) as $filter) {
			$input = $filter->normalize($input);
		}

		// 番地部分に含まれうる「十」を削除
		$input = preg_replace_callback(
			'/([一二三四五六七八九])?十(([一二三四五六七八九－\-])|$)/u',
			array($this, '_replaceHandler'),
			$input );

		// 漢数字の1 on 1置換を適用
		$confs = array(
			'一' => '１',
			'二' => '２',
			'三' => '３',
			'四' => '４',
			'五' => '５',
			'六' => '６',
			'七' => '７',
			'八' => '８',
			'九' => '９',
			'〇' => '０'
		);
		foreach($confs as $pt => $rep) {
			$input = mb_ereg_replace($pt, $rep, $input);
		}
		return $input;
	}

	/**
	 * postNormalize()内のpreg_replace_callbackから呼び出される
	 * 変換処理ハンドラ
	 *
	 * @access protected
	 * @param array $m パターンに一致した情報の配列
	 * @return string 一致パターンを置換する文字列
	 */
	protected function _replaceHandler($m) {
		if(empty($m[1]) && (mb_ereg_match('[－-]', $m[2]) || empty($m[2]))) {
			// 「十」単独のケース
			return sprintf('一〇%s', nvl($m[2], ''));
		}
		// 前か後ろ（または両方）に漢数字が付いているケース
		return sprintf('%s%s', nvl($m[1], '一'), nvl($m[2], ''));
	}
}
