<?php
namespace models\Logic\Normalizer;

/**
 * カタカナ以外の文字を除去する正規化フィルタ。
 * 事前にひらがな→カタカナ変換と半角→全角変換を行った後に除外処理を適用する
 */
class LogicNormalizerDeleteNoKatakana extends LogicNormalizerAbstract {
	/**
	 * LogicNormalizerDeleteNoAsciiCharsの新しい
	 * インスタンスを初期化する
	 */
	public function __construct() {
        // 全角ひらがなと半角カタカナを全角カタカナへ変換
		$this->_convert_options = array(
            'CKV'
        );
	}

	/**
	 * normalize()メソッドのメイン処理終了後に呼び出される終了処理。
	 * LogicNormalizerDeleteNoAsciiCharsでは、印字可能ASCII文字以外の
	 * 文字をすべて除去する
	 *
	 * @access protected
	 * @param string $input 終了処理を適用する文字列
	 * @return string 終了処理適用後の文字列
	 */
	protected function postNormalize($input) {
		mb_regex_encoding(mb_internal_encoding());
        // マイナスやハイフンをすべて長音記号へ置き換える
        $input = mb_ereg_replace('[-―－‐ｰ]', 'ー', $input);
        // UTF前提なのがちょっとアレだが、カタカナ以外をすべて除去
		$input = mb_ereg_replace('[^ァ-ヾ]', '', $input);
        // 中黒を除去
        $input = mb_ereg_replace('・', '', $input);

        return $input;
	}
}
