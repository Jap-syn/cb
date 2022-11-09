<?php
namespace models\Logic;

// ゆうちょMTサービスのOCRデータ向けチェックディジットユーティリティ
class LogicYuchoUtility {
	// CD2を算出するPhase
	const MT_PHASE_1 = 'MT phase 1';

	// 確定CDを算出するためのPhase
	const MT_PHASE_2 = 'MT phase 2';

	/**
	 * MT向けの、各Phaseごとに規定されている重みリストを取得する。
	 * 戻り値はキーMT_PHASE_1にPhase1向け重みリスト、キーMT_PHASE_2にPhase2向け重みリストが
	 * 格納される。各重みリストは44桁固定
	 *
	 * @static
	 * @return array
	 */
	public static function getMtWaitValues() {
		return array(
			// Phase1（CD2）算出時の重みリスト
			self::MT_PHASE_1 => array(
				1, 1, 9, 1, 8, 1, 7, 1, 6, 1, 5, 1, 4, 1, 3, 1, 2, 1, 9, 1, 8, 1,
				7, 1, 6, 1, 5, 1, 4, 1, 3, 1, 2, 1, 9, 1, 8, 1, 7, 1, 6, 1, 5, 1

			),
			// Phase2（CD1）算出時の重みリスト
			self::MT_PHASE_2 => array(
				1, 2, 1, 3, 1, 4, 1, 5, 1, 6, 1, 7, 1, 8, 1, 9, 1, 2, 1, 3, 1, 4,
				1, 5, 1, 6, 1, 7, 1, 8, 1, 9, 1, 2, 1, 3, 1, 4, 1, 5, 1, 6, 1, 7
			)
		);
	}

	/**
	 * MT向けコードの許容文字（非数）を数値に変換するマッピング情報を取得する。
	 * 戻り値は、許容文字をキーとし値が対応する数値になった連想配列。
	 *
	 * @static
	 * @return array
	 */
	public static function getMtCharMap() {
		// 許容文字の数値へのマッピング
		return array(
			'A' => 10, 'F' => 15, 'H' => 17, 'J' => 19,
			'K' => 20, 'L' => 21, 'P' => 25, 'T' => 29,
			'V' => 31, 'X' => 33, '*' => 36, '+' => 37,
			'-' => 38, '#' => 39
		);
	}

	/**
	 * MT Phase毎の重みリスト
	 *
	 * @access protected
	 * @var array
	 */
	protected $_mt_waits;

	/**
	 * MT 許容文字→数値のマッピング情報
	 *
	 * @access protected
	 * @var array
	 */
	protected $_mt_valid_chars_map;

	/**
	 * LogicYuchoUtilityの新しいインスタンスを初期化する
	 */
	public function __construct() {
		$this->_mt_waits = self::getMtWaitValues();
		$this->_mt_valid_chars_map = self::getMtCharMap();
	}

	/**
	 * 指定のMT OCRデータのチェックディジットを算出する
	 *
	 * @param string $userValue OCR向けユーザデータ。最長42文字までしか利用されない
	 * @return string $userValueから算出された2桁のチェックディジット
	 */
	public function calcMtCode($userValue) {
		// CD2を得るためPhase1の演算を実施
		$tmpCd = $this->_calcMtCodeInternal(self::MT_PHASE_1, $userValue);

		// CD2を付与してPhase2の演算を実施
		$fixCd = $this->_calcMtCodeInternal(self::MT_PHASE_2, $tmpCd . $userValue);

		// 確定したCDを返す
		return sprintf('%02d', $fixCd);
	}

	/**
	 * MT 指定Phase向けのチェックディジット算出を実行する
	 *
	 * @access protected
	 * @param string $phase 演算対象のPhase
	 * @param string $value OCR向けユーザデータ
	 * @return string $valueから算出されたチェックディジット。Phase1指定時は1桁、Phase2指定時は2桁となる
	 */
	protected function _calcMtCodeInternal($phase = self::MT_PHASE_1, $value) {
		if(!in_array($phase, array(self::MT_PHASE_1, self::MT_PHASE_2))) {
			$phase = self::MT_PHASE_1;
		}

		$waits = $this->_mt_waits[$phase];

		// ループインデックスの補正量
		$correct = $phase == self::MT_PHASE_1 ? 2 : 1;

		// Phase毎の最終除数
		$divisor = $phase == self::MT_PHASE_1 ? 10 : 11;

		$memo = 0;	// 計算メモ
		for($i = 0, $l = min(strlen($value), 44); $i < $l; $i++) {
			$wait = $waits[$i + $correct];				// 対応する桁位置の重みを抽出
			$c = strtoupper(substr($value, $i, 1));		// 処理対象文字を抽出

			if(isset($this->_mt_valid_chars_map[$c])) {
				// 許容文字を対応数値に変換
				$v = $this->_mt_valid_chars_map[$c];
			} else {
				if(!is_numeric($c)) {
					// 許容文字以外の非数文字が含まれていたら例外
					throw new \Exception('invalid character included !!');
				}
				// 数字は単純にintにキャスト
				$v = (int)$c;
			}

			// 重みづけをしてメモへ加算
			$memo += ($v * $wait);
		}

		// メモ結果と最終除数の余りを算出 → 桁溢れ対策で10で剰余を取る
		$cd = ($memo % $divisor) % 10;

		// Phase2演算時は元値の先頭（＝先に計算したCD2）を合わせて2桁に
		if($phase == self::MT_PHASE_2) {
			$cd = ($cd * 10) + ((int)substr($value, 0, 1));
		}

		// CD確定
		return $cd;
	}

}

