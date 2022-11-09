<?php
namespace models\Logic\BarcodeData\Cvs;

/**
 * LINE Pay利用（＠ペイメント用）入金用バーコードデータ生成クラス
 */
class LogicBarcodeDataCvsIndividualPay extends LogicBarcodeDataCvsAbstract {
	/**
	 * メーカーコードを指定してLogicBarcodeDataCvsIndividualPayの新しいインスタンスを
	 * 初期化する
	 *
	 * @param string $makerCode メーカーコード
	 */
	public function __construct($makerCode) {
		parent::__construct($makerCode);
	}

	/**
	 * 5桁の加入者コードを取得する
	 * @return string 加入者コード
	 */
	public function getCorporateCode() {
		return parent::getCorporateCode();
	}
	/**
	 * 5桁の加入者コードを設定する
	 * @param string $corpCode 加入者コード
	 * @return LogicBarcodeDataCvsNTTSmartTrade このインスタンス
	 */
	public function setCorporateCode($corpCode) {
		if(!preg_match('/^\d{5}$/', $corpCode)) {
			throw new \Exception('加入者コードは5桁の数字で指定する必要があります');
		}
		$this->_corpCode = $corpCode;
		return $this;
	}

	/**
	 * 注文を一意に識別するための、16桁の自由欄向けシーケンス値を取得する
	 * @return string 数値形式のユニークシーケンス
	 */
	public function getUniqueSequence() {
		return parent::getUniqueSequence();
	}
	/**
	 * 注文を一意に識別するための、16桁の自由欄向けシーケンス値を設定する
	 * @param string $uniquieSeq 注文を一意に識別するための数値形式のユニークシーケンス
	 * @return LogicBarcodeDataCvsIndividualPay このインスタンス
	 */
	public function setUniqueSequence($uniqueSeq) {
		$uniqueSeq = (string)$uniqueSeq;
		if(!preg_match('/^[1-9]\d*$/', $uniqueSeq)) {
			throw new \Exception('ユニークシーケンスは数値形式（正の整数）である必要があります');
		}
		$this->_uniqueSeq = $uniqueSeq;
		return $this;
	}

	/**
	 * 表示用バーコードデータ文字列を生成する
	 * @return array 最初の要素に上段用、最後の要素に下段用のEAN-128バーコードデータ文字列を格納した配列
	 */
	public function generateString() {
		return array(
			sprintf('(%s) %s%s-%s%016d%s',
					$this->getAiCode(),         // 2  AIコード
					$this->getCountryCode(),    // 1  国コード
					$this->getMakerCode(),      // 5  メーカーコード
					$this->getCorporateCode(),  // 5  加入者コード
					$this->getUniqueSequence(), // 16 自由欄向けシーケンス
					$this->getReIssueCount()),  // 1  再発行回数

			sprintf('%s-%s-%06d-%s',
                    $this->getLimitDate(),                                                          // 6  支払期限日
					$this->getPaymentMoney() >= $this->getStampFlagThresholdPrice() ? '1' : '0',    // 1  印紙税
					$this->getPaymentMoney(),                                                       // 6  支払金額
					$this->_calcMod10() )                                                           // 1  モジュラス10チェックディジット
		);
	}

	/**
	 * チェックディジットを除いた43桁のバーコードデータを生成する
	 * @access protected
	 * @return string チェックディジットを含まない43ケタのEAN-128バーコードデータ文字列
	 */
	protected function _generateRawValue() {
		$buf = array(
			$this->getAiCode(),                                                             // 2  AIコード
			$this->getCountryCode(),                                                        // 1  国コード
			$this->getMakerCode(),                                                          // 5  メーカーコード
			$this->getCorporateCode(),                                                      // 5  加入者コード
			sprintf('%016s', $this->getUniqueSequence()),                                   // 16 自由欄向けシーケンス
			$this->getReIssueCount(),                                                       // 1  再発行回数
            $this->getLimitDate(),                                                          // 6  支払期限日
			$this->getPaymentMoney() >= $this->getStampFlagThresholdPrice() ? '1' : '0',    // 1  印紙税
			sprintf('%06d', $this->getPaymentMoney())                                       // 6  支払金額
		);
		return join('', $buf);
	}
}
