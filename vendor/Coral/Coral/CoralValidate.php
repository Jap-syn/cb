<?php
namespace Coral\Coral;

use Coral\Coral\Validate\CoralValidateUtility;
use Zend\Validator;

/**
 * Coral Validate Class
 *
 */
class CoralValidate
{
	/**
	 * メールアドレスチェック
	 *
	 * @param string $mailAddress
	 * @return boolean
	 */
	public function isMail($mailAddress)
	{
        $regexValid = new Validator\Regex(CoralValidateUtility::EMAIL_ADDRESS);
        return $regexValid->isValid($mailAddress);
	}

	/**
	 * メールアドレスチェック
	 *
	 * @param string $mailAddress
	 * @return boolean
	 */
	public function isMailPart($mailAddress)
	{
	    $regexValid = new Validator\Regex(CoralValidateUtility::EMAIL_ADDRESS_PART);
	    return $regexValid->isValid($mailAddress);
	}

	/**
	 * 郵便番号チェック
	 *
	 * @param string $postCode
	 * @return boolean
	 */
	public function isPostCode($postCode)
	{
	    $regexValid = new Validator\Regex(CoralValidateUtility::POSTAL_CODE);
	    return $regexValid->isValid($postCode);
	}

	/**
	 * 電話番号チェック
	 *
	 * @param string $phone
	 * @return boolean
	 */
	public function isPhone($phone)
	{
	    $regexValid = new Validator\Regex(CoralValidateUtility::PHONE_NUMBER);
	    return $regexValid->isValid($phone);
	}

	/**
	 * 空チェック
	 *
	 * @param string $data
	 * @return boolean
	 */
	public static function isNotEmpty($data)
	{
	    $notEmptyValid = new Validator\NotEmpty();
	    return $notEmptyValid->isValid($data);
	}

	/**
	 * 日付チェック
	 *
	 * @param string $date 日付
	 * @return boolean
	 */
	public static function isDate($date)
	{
// Mod By Takemasa(NDC) 20151202 Stt 時分秒を許容するﾁｪｯｸに変更
// 	    $dateValid = new Validator\Date();
// 	    return $dateValid->isValid($date);
	    return IsValidDate($date);
// Mod By Takemasa(NDC) 20151202 End 時分秒を許容するﾁｪｯｸに変更
	}

	/**
	 * 数値範囲チェック
	 *
	 * @param int $data　チェックするデータ
	 * @param int $min　最小値
	 * @param int $max　最大値
	 * @return boolean
	 */
	public static function checkBetween($data, $min, $max)
	{
	    $betweenValid = new Validator\Between(array(
    	                                           'min' => $min,
    	                                           'max' => $max
	                                           )
        );
	    return $betweenValid->isValid($data);
	}

	/**
	 * 数値チェック（整数）
	 *
	 * @param string $data　チェックするデータ
	 * @return boolean
	 */
	public static function isInt($data)
	{
        // ZF1のソースから抽出
	    $value = $data;

	    $valueString = (string) $value;

	    $locale = localeconv();

	    $valueFiltered = str_replace($locale['decimal_point'], '.', $valueString);
	    $valueFiltered = str_replace($locale['thousands_sep'], '', $valueFiltered);

	    if (strval(intval($valueFiltered)) != $valueFiltered) {
	        return false;
	    }

	    return true;

	}

	/**
	 * 数値チェック（浮動小数点）
	 *
	 * @param string $data　チェックするデータ
	 * @return boolean
	 */
	public static function isFloat($data)
	{
	    // ZF1のソースから抽出
	    $valueString = (string) $data;

	    $locale = localeconv();

	    $valueFiltered = str_replace($locale['decimal_point'], '.', $valueString);
	    $valueFiltered = str_replace($locale['thousands_sep'], '', $valueFiltered);

	    if (strval(floatval($valueFiltered)) != $valueFiltered) {
	        return false;
	    }

	    return true;
	}
}

