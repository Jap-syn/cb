<?php
namespace models\Logic\BarcodeData\Cvs;

/**
 * SMBC決済ステーション収容向けコンビニ入金用バーコードデータ生成クラス
 */
class LogicBarcodeDataCvsSmbcfs extends LogicBarcodeDataCvsAbstract {
	/**
	 * メーカーコードを指定してLogicBarcodeDataCvsSmbcfsの新しいインスタンスを
	 * 初期化する
	 *
	 * @param string $makerCode メーカーコード
	 */
	public function __construct($makerCode) {
	    parent::__construct($makerCode);
	}

	/**
	 * 5桁の収納企業コード（外部連携コード）を取得する
	 * @return string 収納企業コード（外部連携コード）
	 */
	public function getCorporateCode() {
	    return parent::getCorporateCode();
	}
	/**
	 * 5桁の収納企業コード（外部連携コード）を設定する
	 * @param string $corpCode 収納企業コード（外部連携コード）
	 * @return LogicBarcodeDataCvsSmbcfs このインスタンス
	 */
	public function setCorporateCode($corpCode) {
	    if(!preg_match('/^\d{5}$/', $corpCode)) {
			throw new \Exception('収納企業コードは5桁の数字で指定する必要があります');
		}
		$this->_corpCode = $corpCode;
		return $this;
	}

	/**
	 * 注文を一意に識別するための、17桁の自由欄向けシーケンス値を取得する
	 * @return string 数値形式のユニークシーケンス
	 */
	public function getUniqueSequence() {
	    return parent::getUniqueSequence();
	}
	/**
	 * 注文を一意に識別するための、17桁の自由欄向けシーケンス値を設定する
	 * @param string $uniquieSeq 注文を一意に識別するための数値形式のユニークシーケンス
	 * @return LogicBarcodeDataCvsSmbcfs このインスタンス
	 */
	public function setUniqueSequence($uniqueSeq) {
	    $uniqueSeq = (string)$uniqueSeq;
		if(!preg_match('/^\d{17}$/', $uniqueSeq)) {
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
	    // 利用額は下6桁のみに切り詰める
        $paymentMoney = sprintf('%d', $this->getPaymentMoney());
        if(strlen($paymentMoney) > 6) $paymentMoney = substr($paymentMoney, -6);

		return array(
			sprintf('(%s) %s%s-%s%017d',
					$this->getAiCode(),             // 2
					$this->getCountryCode(),        // 1
					$this->getMakerCode(),          // 5
					$this->getCorporateCode(),      // 5
					$this->getUniqueSequence()),    // 17

			sprintf('%s-%s-%06d-%s',
                    $this->getLimitDate(),                                                          // 6
					$this->getPaymentMoney() >= $this->getStampFlagThresholdPrice() ? '1' : '0',    // 1
					$paymentMoney,                                                                  // 6
					$this->_calcMod10() )                                                           // 1
		);
	}

	/**
	 * チェックディジットを除いた43桁のバーコードデータを生成する
	 * @access protected
	 * @return string チェックディジットを含まない43ケタのEAN-128バーコードデータ文字列
	 */
	protected function _generateRawValue() {
	    // 利用額は下6桁のみに切り詰める
        $paymentMoney = sprintf('%d', $this->getPaymentMoney());
        if(strlen($paymentMoney) > 6) $paymentMoney = substr($paymentMoney, -6);

		$buf = array(
			$this->getAiCode(),                                                             // 2
			$this->getCountryCode(),                                                        // 1
			$this->getMakerCode(),                                                          // 5
			$this->getCorporateCode(),                                                      // 5
			sprintf('%017s', $this->getUniqueSequence()),                                   // 17
            $this->getLimitDate(),                                                          // 6
			$this->getPaymentMoney() >= $this->getStampFlagThresholdPrice() ? '1' : '0',    // 1
			sprintf('%06d', $paymentMoney)                                                  // 6
		);
		return join('', $buf);
	}
}
