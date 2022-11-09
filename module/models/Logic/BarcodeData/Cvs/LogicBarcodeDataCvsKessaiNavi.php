<?php
namespace models\Logic\BarcodeData\Cvs;

/**
 * ＠ペイメント収容向けコンビニ入金用バーコードデータ生成クラス
 */
class LogicBarcodeDataCvsKessaiNavi extends LogicBarcodeDataCvsAbstract {
	/**
	 * メーカーコードを指定してLogicBarcodeDataCvsAtPaymentの新しいインスタンスを
	 * 初期化する
	 *
	 * @param string $makerCode メーカーコード
	 */
	public function __construct($makerCode) {
		parent::__construct($makerCode);
	}

	/**
	 * 4桁の加入者コードを取得する
	 * @return string 加入者コード
	 */
	public function getCorporateCode() {
		return parent::getCorporateCode();
	}
	/**
	 * 4桁の加入者コードを設定する
	 * @param string $corpCode 加入者コード
	 * @return LogicBarcodeDataCvsAtPayment このインスタンス
	 */
	public function setCorporateCode($corpCode) {
		if(!preg_match('/^\d{4}$/', $corpCode)) {
			throw new \Exception('加入者コードは4桁の数字で指定する必要があります');
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
	 * @return LogicBarcodeDataCvsAtPayment このインスタンス
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
				sprintf('(%s) %s%s-%s%017d%s',
						$this->getAiCode(),         // 2
						$this->getCountryCode(),    // 1
						$this->getMakerCode(),      // 5
						$this->getCorporateCode(),  // 4
						$this->getUniqueSequence(), // 17
						$this->getReIssueCount()),  // 1

				sprintf('%s-%s-%06d-%s',
                        $this->getLimitDate(),                                                          // 6
						$this->getPaymentMoney() >= $this->getStampFlagThresholdPrice() ? '1' : '0',    // 1
						$this->getPaymentMoney(),                                                       // 6
						$this->_calcMod10() )                                                           // 1
		);
	}

	/**
	 * チェックディジットを除いた43桁のバーコードデータを生成する
	 * @access protected
	 * @return string チェックディジットを含まない43ケタのEAN-128バーコードデータ文字列
	 */
	protected function _generateRawValue() {
		$buf = array(
				$this->getAiCode(),                                                             // 2
				$this->getCountryCode(),                                                        // 1
				$this->getMakerCode(),                                                          // 5
				$this->getCorporateCode(),                                                      // 4
				sprintf('%017s', $this->getUniqueSequence()),                                   // 17
				$this->getReIssueCount(),                                                       // 1
                $this->getLimitDate(),                                                          // 6
				$this->getPaymentMoney() >= $this->getStampFlagThresholdPrice() ? '1' : '0',    // 1
				sprintf('%06d', $this->getPaymentMoney())                                       // 6
		);
		return join('', $buf);
	}
}
