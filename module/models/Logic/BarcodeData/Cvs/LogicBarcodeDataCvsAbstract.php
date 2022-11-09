<?php
namespace models\Logic\BarcodeData\Cvs;

use models\Logic\BarcodeData\LogicBarcodeDataInterface;
use models\Logic\BarcodeData\Cvs\LogicBarcodeDataCvsInterface;

/**
 * コンビニ入金用バーコードデータ生成のための基本抽象クラス
 */
abstract class LogicBarcodeDataCvsAbstract implements
    LogicBarcodeDataInterface, LogicBarcodeDataCvsInterface {

	// 印紙代適用閾金額のデフォルト値
	const STAMPFLG_THRESHOLD = 31500;

	// UCC/EAN-128におけるCVS支払用のAI(Application Identity)コード
	const AI_DATA = '91';

	// UCC/EAN-128における国コード下一桁
	const COUNTRY_CODE = '9';

	/**
	 * UCC/EAN-128におけるCVS支払用AI(Application Identity)コード。91固定
	 * @access protected
	 * @var string
	 */
	protected $_ai = self::AI_DATA;

	/**
	 * UCC/EAN-128における国コード下一桁
	 * @access protected
	 * @var string
	 */
	protected $_countryCode = self::COUNTRY_CODE;

	/**
	 * 国コード1桁＋個別メーカーコード5桁で構成されるメーカーコード
	 * @access protected
	 * @var string
	 */
	protected $_makerCode;

	/**
	 * 6桁の加入者コード
	 * @access protected
	 * @return string
	 */
	protected $_corpCode;

	/**
	 * CB店子管理コード
	 * @access protected
	 * @var string
	 */
	protected $_siteId;

	/**
	 * 注文を一意に識別するユニークシーケンス値
	 * @access protected
	 * @var string
	 */
	protected $_uniqueSeq;

	/**
	 * 印紙代適用閾金額
	 * @access protected
	 * @var int
	 */
	protected $_stampFeeThreshold = self::STAMPFLG_THRESHOLD;

	/**
	 * 再発行回数
	 * @access protected
	 * @var int
	 */
	protected $_reIssue = 0;

	/**
	 * 支払期限日
	 * @access protected
	 * @var string
	 */
	protected $_limitDate;

	/**
	 * 支払金額
	 * @access protected
	 * @var int
	 */
	protected $_payment;

	/**
	 * メーカーコードを指定してLogicBarcodeDataCvsAbstractの
	 * 新しいインスタンスを初期化する
	 *
	 * @access protected
	 * @param string $makerCode メーカーコード
	 */
	protected function __construct($makerCode) {
		$this
			->setMakerCode($makerCode);
	}

	/**
	 * 印紙税適用判断の閾値となる金額を取得する
	 * @return int
	 */
	public function getStampFlagThresholdPrice() {
		return $this->_stampFeeThreshold;
	}
	/**
	 * 印紙税適用判断の閾値となる金額を設定する
	 * @param int $thresholdPrice 閾金額
	 * @return LogicBarcodeDataCvsAbstract このインターフェイスインスタンス
	 */
	public function setStampFlagThresholdPrice($thresholdPrice) {
		$thresholdPrice = (int)$thresholdPrice;
		$this->_stampFeeThreshold = $thresholdPrice;
		return $this;
	}

	/**
	 * UCC/EAN-128向けAIコードを取得する
	 * @return string
	 */
	public function getAiCode() {
		return $this->_ai;
	}

	/**
	 * UCC/EAN-128向け国コード（1桁）を取得する
	 * @return string
	 */
	public function getCountryCode() {
		return $this->_countryCode;
	}

	/**
	 * 5桁のメーカーコードを取得する
	 * @return string メーカーコード。5桁で構成される
	 */
	public function getMakerCode() {
		return $this->_makerCode;
	}
	/**
	 * 5桁のメーカーコードを設定する
	 * @param string $makerCode メーカーコード。5桁で構成されている必要がある
	 * @return LogicBarcodeDataCvsAbstract このインスタンス
	 */
	public function setMakerCode($makerCode) {
		if(!preg_match('/^\d{5}$/', $makerCode)) {
			throw new \Exception('メーカーコードは5桁の数字で指定する必要があります');
		}
		$this->_makerCode = $makerCode;
		return $this;
	}

	/**
	 * 加入者コードを取得する
	 * @return string 加入者コード
	 */
	public function getCorporateCode() {
		return $this->_corpCode;
	}
	/**
	 * 加入者コードを設定する
	 * @param string $corpCode 加入者コード
	 * @return LogicBarcodeDataCvsAbstract このインスタンス
	 */
	public function setCorporateCode($corpCode) {
		if(!preg_match('/^\d{1,20}$/', $corpCode)) {
			throw new \Exception('加入者コードは1～20桁の数字で指定する必要があります');
		}
		$this->_corpCode = $corpCode;
		return $this;
	}

	/**
	 * CB店子管理コードを取得する
	 * @return string 加入者コード
	 */
	public function getSiteId() {
		return $this->_siteId;
	}
	/**
	 * CB店子管理コードを設定する
	 * @param string $corpCode CB店子管理コード
	 * @return LogicBarcodeDataCvsAbstract このインスタンス
	 */
	public function setSiteId($siteId) {
		$this->_siteId = $siteId;
		return $this;
	}

	/**
	 * 注文を一意に識別するための、15桁の自由欄向けシーケンス値を取得する
	 * @return string 数値形式のユニークシーケンス
	 */
	public function getUniqueSequence() {
		return $this->_uniqueSeq;
	}
	/**
	 * 注文を一意に識別するための、15桁の自由欄向けシーケンス値を設定する
	 * @param string $uniquieSeq 注文を一意に識別するための数値形式のユニークシーケンス
	 * @return LogicBarcodeDataCvsAbstract このインスタンス
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
	 * 再発行回数を取得する
	 * @return int 再発行回数。0～9。
	 */
	public function getReIssueCount() {
		return $this->_reIssue;
	}
	/**
	 * 再発行回数を設定する
	 * @param int $reIssueCode 再発行回数。0～9が設定可能で、範囲外指定時は範囲内に丸められる
	 * @return LogicBarcodeDataCvsAbstract このインスタンス
	 */
	public function setReIssueCount($reIssueCode) {
		$reIssueCode = (int)$reIssueCode;
		if($reIssueCode < 0) $reIssueCode = 0;
		if($reIssueCode > 9) $reIssueCode = 9;
		$this->_reIssue = $reIssueCode;
		return $this;
	}

	/**
	 * 支払期限日を取得する
	 * @return string 支払期限日 'yyyy-MM-dd'書式で通知
	 */
	public function getLimitDate() {
		return $this->_limitDate;
	}
	/**
	 * 支払期限日を設定する
	 * @param string $limitDate 支払期限日 'yyyy-MM-dd'書式で通知
	 * @return LogicBarcodeDataCvsAbstract このインスタンス
	 */
	public function setLimitDate($limitDate) {
        if(!preg_match('/^\d{6}$/', $limitDate)) {
            throw new \Exception('支払期限日は数字6桁で指定する必要があります');
        }
        $this->_limitDate = $limitDate;
        return $this;
	}

	/**
	 * 支払金額を取得する
	 * @return int 支払金額
	 */
	public function getPaymentMoney() {
		return $this->_payment;
	}
	/**
	 * 支払金額を設定する
	 * @param int $paymentMoney 支払金額
	 * @return LogicBarcodeDataCvsAbstract このインスタンス
	 */
	public function setPaymentMoney($paymentMoney) {
		$this->_payment = (int)$paymentMoney;
		return $this;
	}

	/**
	 * バーコードデータを生成する
	 * @return string EAN-128バーコードデータ文字列
	 */
	public function generate() {
		return $this->_generateRawValue() . $this->_calcMod10();
	}

	/**
	 * 表示用バーコードデータ文字列を生成する
	 * @return array 最初の要素に上段用、最後の要素に下段用のEAN-128バーコードデータ文字列を格納した配列
	 */
	public function generateString() {
		return array(
			sprintf('(%s)%s%s-%s%015d%s',
					$this->getAiCode(),
					$this->getCountryCode(),
					$this->getMakerCode(),
					$this->getCorporateCode(),
					$this->getUniqueSequence(),
					$this->getReIssueCount()),

			sprintf('%s-%s-%06d-%s',
					$this->getLimitDate(),
					$this->getPaymentMoney() >= $this->getStampFlagThresholdPrice() ? '1' : '0',
					$this->getPaymentMoney(),
					$this->_calcMod10() )
		);
	}

	/**
	 * チェックディジットを除いた43桁のバーコードデータを生成する
	 * @access protected
	 * @return string チェックディジットを含まない43ケタのEAN-128バーコードデータ文字列
	 */
	protected function _generateRawValue() {
		$buf = array(
			$this->getAiCode(),
			$this->getCountryCode(),
			$this->getMakerCode(),
			$this->getCorporateCode(),
			sprintf('%015s', $this->getUniqueSequence()),
			$this->getReIssueCount(),
            $this->getLimitDate(),
			$this->getPaymentMoney() >= $this->getStampFlagThresholdPrice() ? 1 : 0,
			sprintf('%06d', $this->getPaymentMoney())
		);
		return join('', $buf);
	}

	/**
	 * 現在の設定で出力されるバーコードデータ向けのモジュラス10チェックディジットを算出する
	 * @access protected
	 * @return string 現在の設定で生成される43桁バーコードデータから算出されるモジュラス10チェックディジット
	 */
	protected function _calcMod10() {
		$oddValue = 0;
		$evenValue = 0;

		$data = $this->_generateRawValue();
		for($i = 0; $i < 43; $i++) {
			$v = substr($data, $i, 1);
			if($i % 2 == 0) {
				$oddValue += (int)$v;
			} else {
				$evenValue += (int)$v;
			}
		}
		return (10 - ((($oddValue * 3) + $evenValue) % 10)) %10;
	}
}

