<?php
namespace models\Logic\BarcodeData\Cvs;
/**
 * コンビニ入金用バーコードデータ生成インターフェイス
 */
interface LogicBarcodeDataCvsInterface {
	/**
	 * 印紙税適用判断の閾値となる金額を取得する
	 * @return int
	 */
	public function getStampFlagThresholdPrice();
	/**
	 * 印紙税適用判断の閾値となる金額を設定する
	 * @param int $thresholdPrice 閾金額
	 * @return LogicBarcodeDataCvsInterface このインターフェイスインスタンス
	 */
	public function setStampFlagThresholdPrice($thresholdPrice);

	/**
	 * UCC/EAN-128向けAIコードを取得する
	 * @return string
	 */
	public function getAiCode();

    /**
     * UCC/EAN-128向け国コード（1桁）を取得する
     * @return string
     */
    public function getCountryCode();

	/**
	 * 6桁のメーカーコードを取得する
	 * @return string メーカーコード。国コード1桁＋個別メーカーコード5桁で構成される
	 */
	public function getMakerCode();
	/**
	 * 6桁のメーカーコードを設定する
	 * @param string $makerCode メーカーコード。国コード1桁＋個別メーカーコード5桁で構成されている必要がある
	 * @return LogicBarcodeDataCvsInterface このインターフェイスインスタンス
	 */
	public function setMakerCode($makerCode);

	/**
	 * 6桁の加入者コードを取得する
	 * @return string 加入者コード
	 */
	public function getCorporateCode();
	/**
	 * 6桁の加入者コードを設定する
	 * @param string $corpCode 加入者コード
	 * @return LogicBarcodeDataCvsInterface このインターフェイスインスタンス
	 */
	public function setCorporateCode($corpCode);

	/**
	 * CB店子管理コードを取得する
	 * @return string CB店子管理コード
	 */
	public function getSiteId();
	/**
	 * CB店子管理コードを設定する
	 * @param string $corpCode CB店子管理コード
	 * @return LogicBarcodeDataCvsInterface このインターフェイスインスタンス
	 */
	public function setSiteId($siteId);

	/**
	 * 注文を一意に識別するための、15桁の自由欄向けシーケンス値を取得する
	 * @return string 数値形式のユニークシーケンス
	 */
	public function getUniqueSequence();
	/**
	 * 注文を一意に識別するための、15桁の自由欄向けシーケンス値を設定する
	 * @param string $uniquieSeq 注文を一意に識別するための数値形式のユニークシーケンス
	 * @return LogicBarcodeDataCvsInterface このインターフェイスインスタンス
	 */
	public function setUniqueSequence($uniqueSeq);

	/**
	 * 再発行回数を取得する
	 * @return int 再発行回数。0～9。
	 */
	public function getReIssueCount();
	/**
	 * 再発行回数を設定する
	 * @param int $reIssueCode 再発行回数。0～9が設定可能で、範囲外指定時は範囲内に丸められる
	 * @return LogicBarcodeDataCvsInterface このインターフェイスインスタンス
	 */
	public function setReIssueCount($reIssueCode);

	/**
	 * 支払期限日を取得する
	 * @return string 支払期限日 'yyyy-MM-dd'書式で通知
	 */
	public function getLimitDate();
	/**
	 * 支払期限日を設定する
	 * @param string $limitDate 支払期限日 'yyyy-MM-dd'書式で通知
	 * @return LogicBarcodeDataCvsInterface このインターフェイスインスタンス
	 */
	public function setLimitDate($limitDate);

	/**
	 * 支払金額を取得する
	 * @return int 支払金額
	 */
	public function getPaymentMoney();
	/**
	 * 支払金額を設定する
	 * @param int $paymentMoney 支払金額
	 * @return LogicBarcodeDataCvsInterface このインターフェイスインスタンス
	 */
	public function setPaymentMoney($paymentMoney);

}

