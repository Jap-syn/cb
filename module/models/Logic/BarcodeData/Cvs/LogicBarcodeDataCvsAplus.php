<?php
namespace models\Logic\BarcodeData\Cvs;

/**
 * アプラス収容向けコンビニ入金用バーコードデータ生成クラス
 */
class LogicBarcodeDataCvsAplus extends LogicBarcodeDataCvsAbstract {
	/**
	 * 作成日
	 * @access protected
	 * @var string
	 */
	protected $_publishedDate;

	/**
	 * メーカーコードと作成日を指定してLogicBarcodeDataCvsAplusの新しいインスタンスを
	 * 初期化する
	 *
	 * @param string $makerCode 6桁のメーカーコード
	 * @param string $corpCode 4桁の加入者コード
	 * @param null | string $publishedDate 作成日 'yyyy-MM-dd'書式で通知
	 */
	public function __construct($makerCode, $publishedDate = null) {
		parent::__construct($makerCode);
		if($publishedDate == null) {
			$publishedDate = date('Y-m-d');
		}
		$this->setPublishedDate($publishedDate);
	}

	/**
	 * 作成日を取得する
	 *
	 * @return string
	 */
	public function getPublishedDate() {
		return $this->_publishedDate;
	}
	/**
	 * 作成日を設定する
	 *
	 * @param string $publishedDate 作成日 'yyyy-MM-dd'書式で通知
	 * @return LogicBarcodeDataCvsAplus このインスタンス
	 */
	public function setPublishedDate($publishedDate) {
        if(!preg_match('/^\d{4}-\d{2}-\d{2}$/', $publishedDate)) {
            throw new \Exception('作成日はyyyy-MM-dd形式で指定する必要があります');
        }
        if($publishedDate != date('Y-m-d', strtotime($publishedDate))) {
            throw new \Exception('作成日の設定値は有効な日付ではありません');
        }

        $this->_publishedDate = date('Y-m-d', strtotime($publishedDate));
        return $this;
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
	 * @return LogicBarcodeDataCvsAplus このインスタンス
	 */
	public function setCorporateCode($corpCode) {
		if(!preg_match('/^\d{4}$/', $corpCode)) {
			throw new \Exception('加入者コードは4桁の数字で指定する必要があります');
		}
		$this->_corpCode = $corpCode;
		return $this;
	}

	/**
	 * 注文を一意に識別するための、13桁の自由欄向けシーケンス値を取得する
	 * @return string 数値形式のユニークシーケンス
	 */
	public function getUniqueSequence() {
		return parent::getUniqueSequence();
	}
	/**
	 * 注文を一意に識別するための、13桁の自由欄向けシーケンス値を設定する
	 * @param string $uniquieSeq 注文を一意に識別するための数値形式のユニークシーケンス
	 * @return LogicBarcodeDataCvsAplus このインスタンス
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
			sprintf('(%s) %s%s-%013d%s%s%s',
					$this->getAiCode(),									// 2
					$this->getCountryCode(),							// 1
					$this->getMakerCode(),								// 5
					$this->getUniqueSequence(),							// 13
					date('ym', strtotime($this->getPublishedDate())),	// 4
					$this->getCorporateCode(),							// 4
					$this->getReIssueCount()),							// 1

			sprintf('%s-%s-%06d-%s',
                    $this->getLimitDate(),                                                          // 6
					$this->getPaymentMoney() >= $this->getStampFlagThresholdPrice() ? '1' : '0',	// 1
					$this->getPaymentMoney(),														// 6
					$this->_calcMod10() )															// 1
		);
	}

	/**
	 * チェックディジットを除いた43桁のバーコードデータを生成する
	 * @access protected
	 * @return string チェックディジットを含まない43ケタのEAN-128バーコードデータ文字列
	 */
	protected function _generateRawValue() {
		$buf = array(
			$this->getAiCode(),																// 2
			$this->getCountryCode(),														// 1
			$this->getMakerCode(),															// 5
			sprintf('%013s', $this->getUniqueSequence()),									// 13
			date('ym', strtotime($this->getPublishedDate())),								// 4
			$this->getCorporateCode(),														// 4
			$this->getReIssueCount(),														// 1
            $this->getLimitDate(),							                                // 6
			$this->getPaymentMoney() >= $this->getStampFlagThresholdPrice() ? '1' : '0',	// 1
			sprintf('%06d', $this->getPaymentMoney())										// 6
		);
		return join('', $buf);
	}

}
