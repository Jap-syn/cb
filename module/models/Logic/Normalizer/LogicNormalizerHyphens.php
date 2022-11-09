<?php
namespace models\Logic\Normalizer;

/**
 * 全角の長音記号や全角ダッシュなどの文字を全角マイナスに統一する
 * 正規化フィルタ
 */
class LogicNormalizerHyphens extends LogicNormalizerAbstract {
    /**
     * LogicNormalizerHyphensの新しいインスタンスを初期化する
     */
    public function __construct() {
        // mb_convert_kana()は使用しない
        $this->_convert_options = array();
    }

	/**
	 * normalize()メソッドのメイン処理終了後に呼び出される終了処理。
	 * LogicNormalizerHyphensでは、全角長音記号、全角ダッシュ、
	 * 全角ハイフンをすべて全角マイナスに統一する
	 *
	 * @access protected
	 * @param string $input 終了処理を適用する文字列
	 * @return string 終了処理適用後の文字列
	 */
    protected function postNormalize($input) {
//        $from = '－';  // (FF0D)全角マイナス
        $from = pack('CCC', 0xe3, 0x83, 0xbc); // (30FC)全角長音記号
        $from .= pack('CCC', 0xef, 0xbd, 0xb0); // (FF70)半角長音記号
//        $from .= pack('CCC', 0x00, 0x00, 0x2d); // (002D)半角マイナス・半角ハイフン
        $from .= pack('CCC', 0xe2, 0x80, 0x90); // (2010)全角ハイフン
        $from .= pack('CCC', 0xe2, 0x80, 0x91); // (2011)改行しないハイフン
        $from .= pack('CCC', 0xe2, 0x80, 0x93); // (2013)ENダッシュ
        $from .= pack('CCC', 0xe2, 0x80, 0x94); // (2014)EMダッシュ
        $from .= pack('CCC', 0xe2, 0x80, 0x95); // (2015)全角ダッシュ
        $from .= pack('CCC', 0xe2, 0x88, 0x92); // (2212)マイナス

        mb_regex_encoding(mb_internal_encoding());
        return mb_ereg_replace('[－'.$from.'-]', '－', $input);
    }
}
