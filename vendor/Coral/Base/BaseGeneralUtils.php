<?php
namespace Coral\Base;

use Coral\Coral\CoralValidate;
/**
 * 汎用ユーティリティクラス
 */
class BaseGeneralUtils
{
	/**
	 * パスワード生成
	 *
	 * @param int $len パスワードの長さ
	 * @return string $str パスワード
	 */
	public static function MakePassword($len)
	{
		// 乱数表のシードを決定
		srand((double)microtime() * 98452361);

		// パスワードに使用する文字列の配列
		//$pstrm = "abcdefghkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ12345679!#%;<>";
		$pstrm = "abcdefghijklmnopqrstuvwxyz123456789";
		$pstr = preg_split("//", $pstrm, 0, PREG_SPLIT_NO_EMPTY);

		$password = "";

		for ($i = 0 ; $i < $len ; $i++)
		{
			// パスワード文字列を生成
		    $password .= $pstr[array_rand($pstr, 1)];
		}

		return $password;
	}

	/**
	 * 実利率（浮動小数点）に変換
	 */
	public static function ToRealRate($data)
	{
		return $data / 100000;
	}

	/**
	 * 永続化用利率（整数）に変換
	 */
	public static function ToSaveRate($data)
	{
		$result = $data * 100000;
		return (int)("$result");
	}

	/**
	 * 指定の配列がすべて空であるかを判断する。
	 *
	 * @param array $array 検査する配列
	 * @param bool $strict 「空」を厳密に判断するかのフラグ。長さ0の文字列は空と見なさない場合はtrueを指定する。省略時はfalse
	 * @return bool $arrayのすべての要素が空（nullまたは長さ0のstring）の場合はtrue、それ以外はfalse
	 */
	public static function ArrayIsAllEmpty(array $array, $strict = false) {
		$len = count( $array );
		foreach( $array as $item ) {
			if( $strict ) {
				// strictな判断。nullの場合のみ空と判断する
				if( $item === null ) {
					$len--;
				}
			} else {
				// strictでない判断。長さ0の文字列はempty() = trueとなる
				if( empty( $item ) && "$item" != '0' ) {
					$len--;
				}
			}
		}
		return $len == 0;
	}

	/**
	 * アレイをCSVに変換する
	 *
	 * @param array $array
	 * @return string
	 */
	public static function ArrayToCsv(array $array)
	{
		$csv = "";
		foreach ($array as $v)
		{
			$csv .= $v . ",";
		}

		$csv = substr($csv, 0, -1);

		return $csv;
	}

	/**
	 * 経過日数を計算する。
	 *
	 * @param string $from スタート (日付として有効な形式に限る 例:'Y-m-d') // Mod By Takemasa(NDC) 20141127
	 * @param string $to エンド (日付として有効な形式に限る 例:'Y-m-d') // Mod By Takemasa(NDC) 20141127
	 * @return int 日数
	 */
	public static function CalcSpanDays($from, $to)
	{
        $cnt = 0;
	    if ($from != null && $to != null)
	    {
             $stt = date('Y/m/d', strtotime($from));
             $end = date('Y/m/d', strtotime($to));

             $cnt = (strtotime($end) -strtotime($stt));
	    }
	    return (int)($cnt / 86400);
	}

	/**
	 * 経過日数計算を行う
	 *
	 * @param string $from スタート 有効な日付時刻書式で通知
	 * @param string $to エンド 有効な日付時刻書式で通知
	 * @return int 日数
	 */
	public static function CalcSpanDays2($from, $to) {

        $cnt = 0;

        // 日付をUNIXタイムスタンプに変換
        $timestamp1 = strtotime($from);
        $timestamp2 = strtotime($to);

        // 有効な日付時刻時のみ算出を行う
        if ($timestamp1 && $timestamp2) {
            // 現在のタイムゾーンのGMT時差
            $diff = idate('Z');

            // GMT時差考慮
            $timestamp1 += $diff;
            $timestamp2 += $diff;

            // 1970/01/01以前の補正
            if( $f < 0 && $f % 86400 != 0 ) $f -= 86400;
            if( $t < 0 && $t % 86400 != 0 ) $t -= 86400;

            // 時刻情報をそぎ落とす
            $timestamp1 -= ( $timestamp1 % 86400 );
            $timestamp2 -= ( $timestamp2 % 86400 );

            // 何秒離れているか計算
            $seconddiff = abs($timestamp2 - $timestamp1);

            // 日数に変換
            $cnt = (int)($seconddiff / (60 * 60 * 24));

            $cnt = (($from <= $to) ? $cnt : (-1) * $cnt);
        }

        return $cnt;
	}

	/**
	 * strtotime()でタイムスタンプへ変換可能な日付文字列間の経過日数計算を行う
	 * TODO: 有効範囲の確認をとる
	 *
	 * @param string $from スタート
	 * @param string $to エンド
	 * @return int 日数
	 */
	public static function CalcSpanDaysFromString($from, $to) {
		$from = (string)$from;
		$to = (string)$to;
		if(strlen($from) && strlen($to)) {
			// 現在のタイムゾーンのGMT時差
			$diff = idate('Z');

			// UNIXタイムスタンプに変換（GMT時差考慮）
			$f = strtotime($from) + $diff;
			$t = strtotime($to) + $diff;

			// 1970/01/01以前の補正（※CalcSpanDays2参照）
			if( $f < 0 && $f % 86400 != 0 ) $f -= 86400;
			if( $t < 0 && $t % 86400 != 0 ) $t -= 86400;

			// 時刻情報をそぎ落とす
			$f -= ( $f % 86400 );
			$t -= ( $t % 86400 );

			// 日差算出
			$cnt = floor( ($t - $f) / 86400 );
		} else {
			$cnt = 0;
		}
		return $cnt;
	}

	/**
	 * 利息を計算する。
	 *
	 * @param int $amount 元金
	 * @param float $rate 利率
	 * @param int $passageDays 日数
	 * @return float 利息
	 */
	public static function CalcInterest($amount, $rate, $passageDays)
	{
		$result = ($amount * $rate * $passageDays) / 36500;
		return floor($result);
	}

	/**
	 * MM/DD形式の日付文字列に変更
	 *
	 * @param string $date 入力日付
	 * @return string MM/dd形式日付
	 */
	public static function toDateStringMMDD($date)
	{
		$result = mb_ereg_replace("^[0-9][0-9][0-9][0-9][-/]", "", $date);
		$result = mb_ereg_replace("-", "/", $result);
		$result = mb_ereg_replace(" [0-9][0-9]:[0-9][0-9]:[0-9][0-9]$", "", $result);
		return $result;
	}

	/**
	 * MM/DD形式の日付文字列に変更（高速版）
	 *
	 * @param string $date 入力日付
	 * @return string MM/dd形式日付
	 */
	public static function toDateStringMMDD2($date) {
		return date('m/d', strtotime($date));
	}

	public static function toDateStringYYMMDD($date) {
		return date('y/m/d', strtotime($date));
	}

	/**
	 * 振込手数料を取得する。
	 *
	 * @param int $pattern （1：同行料金　2：他行料金）
	 * @param int $money 振込金額
	 * @param bool $isOemCharge OEM立替か否か
	 * @return int 振込手数料
	 */
	public static function getTransferCommission($pattern, $money, $isOemCharge)
	{
		$result = 0;
		return $result;
	}

	/**
	 * 日付パラメータのWHERE句を作成する。
	 *
	 * @param string $columnName カラム名
	 * @param string $datef 開始日付
	 * @param string $datet 終了日付
	 * @return string WHERE句
	 */
	public static function makeWhereDate($columnName, $datef = '', $datet = '')
	{
		$result = "";

		if (CoralValidate::isDate($datef) && CoralValidate::isDate($datet))
		{
			$result = sprintf("%s BETWEEN '%s' AND '%s' ", $columnName, date('Y-m-d', strtotime($datef)), date('Y-m-d', strtotime($datet)));
		}
		else if (CoralValidate::isDate($datef))
		{
			$result = sprintf("%s >= '%s' ", $columnName, date('Y-m-d', strtotime($datef)));

		}
		else if (CoralValidate::isDate($datet))
		{
			$result = sprintf("%s <= '%s' ", $columnName, date('Y-m-d', strtotime($datet)));

		}

		return $result;
	}

	/**
	 * 日時パラメータのWHERE句を作成する。
	 *
	 * @param string $columnName カラム名
	 * @param string $datef 開始日付
	 * @param string $datet 終了日付
	 * @return string WHERE句
	 */
	public static function makeWhereDateTime($columnName, $datef = '', $datet = '')
	{
		$result = "";

		if (CoralValidate::isDate($datef) && CoralValidate::isDate($datet))
		{
			$result = sprintf("%s BETWEEN '%s' AND '%s 23:59:59' ", $columnName, date('Y-m-d', strtotime($datef)), date('Y-m-d', strtotime($datet)));
		}
		else if (CoralValidate::isDate($datef))
		{
		    $result = sprintf("%s >= '%s' ", $columnName, date('Y-m-d', strtotime($datef)));
		}
		else if (CoralValidate::isDate($datet))
		{
		    $result = sprintf("%s <= '%s 23:59:59' ", $columnName, date('Y-m-d', strtotime($datet)));
		}

		return $result;
	}

	/**
	 * 整数パラメータのWHERE句を作成する。
	 *
	 * @param string $columnName カラム名
	 * @param string $nf 開始値
	 * @param string $nt 終了値
	 * @return string WHERE句
	 */
	public static function makeWhereInt($columnName, $nf = '', $nt = '')
	{
		$result = "";

		if (CoralValidate::isInt($nf) && CoralValidate::isInt($nt))
		{
			$result = sprintf("%s BETWEEN %d AND %d ", $columnName, (int)$nf, (int)$nt);
		}
		else if (CoralValidate::isInt($nf))
		{
			$result = sprintf("%s >= %d ", $columnName, (int)$nf);
		}
		else if (CoralValidate::isInt($nt))
		{
			$result = sprintf("%s <= %d ", $columnName, (int)$nt);
		}

		return $result;
	}

	/**
	 * 浮動小数点パラメータのWHERE句を作成する。
	 *
	 * @param string $columnName カラム名
	 * @param string $nf 開始値
	 * @param string $nt 終了値
	 * @return string WHERE句
	 */
	public static function makeWhereFloat($columnName, $nf = '', $nt = '')
	{
	    $result = "";

	    if (CoralValidate::isFloat($nf) && CoralValidate::isFloat($nt))
	    {
	        $result = sprintf("%s BETWEEN %.5f AND %.5f ", $columnName, (float)$nf, (float)$nt);
	    }
	    else if (CoralValidate::isFloat($nf))
	    {
	        $result = sprintf("%s >= %.5f ", $columnName, (float)$nf);
	    }
	    else if (CoralValidate::isFloat($nt))
	    {
	        $result = sprintf("%s <= %.5f ", $columnName, (float)$nt);
	    }

	    return $result;
	}

	/**
	 * 指定文字列の左側に指定文字（列）を指定数になるまで埋め込む。
	 *
	 * @param string $str 文字列
	 * @param string $char 埋め込む文字列
	 * @param int $len 文字数
	 *
	 * @return string 文字列
	 */
	public static function lpad($str, $char, $len)
	{
		$result = $str;
		while (mb_strlen($result, 'UTF-8') < $len)
		{
			$result = $char . $result;
		}

		return $result;
	}

	/**
	 * 指定文字列の右側に指定文字（列）を指定数になるまで埋め込む。
	 *
	 * @param string $str 文字列
	 * @param string $char 埋め込む文字列
	 * @param int $len 文字数
	 * @param int $doCut 文字数を指定数で切り捨てるか否か
	 * @return string 文字列
	 */
	public static function rpad($str, $char, $len, $doCut = false)
	{
		$result = $str;
		while (mb_strlen($result, 'UTF-8') < $len)
		{
			$result = $result . $char;
		}

		if ($doCut) {
		    $cut = mb_strlen($result, 'UTF-8') - $len;
		    if ($cut > 0) {
		        $result = mb_substr($result , 0 , mb_strlen($result, 'UTF-8') - $cut , 'UTF-8' );
		    }
		}

		return $result;
	}

	/**
	 * 日付文字列 [yyyy年MM月dd日 （曜）]を取得する。
	 *
	 * @param string $date (日付として有効な形式に限る 例:'Y-m-d') // Mod By Takemasa(NDC) 20141127
	 * @return string
	 */
	public static function getDateString($date)
	{
	    $youbi = array(1 => '月', 2 => '火', 3 => '水', 4 => '木', 5 => '金', 6 => '土', 0 => '日');
        return sprintf('%s （%s）', date('Y年n月j日', strtotime($date)), $youbi[date('w', strtotime($date))]);
	}

	/**
	 * 指定文字列中の全角英数字および記号を半角に変換する
	 *
	 * @param string $s 変換する文字列
	 * @return $s中の全角英数字・全角記号を半角に変換した文字列
	 */
	public static function convertWideToNarrow($s) {
		$s = mb_convert_kana( "$s", 'as' );
		foreach( array( 'ー' => '-', '。' => '.', '・' => '/' ) as $reg => $rep ) {
			$s = str_replace( $reg, $rep, $s );
		}

		return $s;
	}

	/**
	 * 指定文字列中の半角英数字および記号を全角に変換する
	 *
	 * @param string $s 変換する文字列
	 * @return $s中の半角英数字・半角記号を全角に変換した文字列
	 */
	public static function convertNarrowToWide($s) {
		return mb_convert_kana( $s, 'AS' );
	}

	/**
	 * 指定文字列中の全角数字のみを半角数字に変換する
	 *
	 * @param string $s 変換する文字列
	 * @return $s中の全角数字を半角に変換した文字列
	 */
	public static function convertNumberWideToNarrow($s) {
		return mb_convert_kana( $s, 'n' );
	}

	/**
	 * 指定文字列中の半角数字のみを全角数字に変換する
	 *
	 * @param string $s 変換する文字列
	 * @return $s中の半角数字を全角に変換した文字列
	 */
	public static function convertNumberNarrowToWide($s) {
		return mb_convert_kana( $s, 'N' );
	}

	/**
	 * 指定文字列中の全角英数字、かな・カナ文字および記号を半角に変換する
	 *
	 * @param string $s 変換する文字列
	 * @return $s中の全角英数字・かな・カナ・全角記号を半角に変換した文字列
	 */
	public static function convertWideToNarrowEx($s) {
	    $s = mb_convert_kana( "$s", 'askh' );
	    foreach( array( 'ー' => '-', '。' => '.', '・' => '/' ) as $reg => $rep ) {
	        $s = str_replace( $reg, $rep, $s );
	    }

	    return $s;
	}

	/**
	 * 指定文字列中の半角英数字、カナ文字および記号を全角に変換する
	 *
	 * @param string $s 変換する文字列
	 * @return $s中の半角英数字・カナ・半角記号を全角に変換した文字列
	 */
	public static function convertNarrowToWideEx($s) {
	    return mb_convert_kana( $s, 'ASKV' );
	}

	/**
	 * 指定アドレスが配列中に部分一致で含まれるか否かチェックする。
	 *
	 * @param string $address メールアドレス
	 * @param array $addresses 比較対象のアドレス（ドメイン）配列
	 * @return boolean
	 */
	public static function isMatchAddress($address, $addresses)
	{
		$celCheck = false;

		$mailAddress = mb_convert_kana($address, "a", "UTF-8");

		for ($i = 0 ; $i < count($addresses) ; $i++)
		{
		    if (strstr($mailAddress, $addresses[$i]))
			{
				$celCheck = true;
			}
		}

		return $celCheck;
	}

	/**
	 * 指定住所が配列中に部分一致で含まれるか否かチェックする。
	 *
	 * @param string $address 住所
	 * @param array $addresses 比較対象の住所配列
	 * @return int マッチした数
	 */
	public static function isMatchUnitingAddress($address, $addresses)
	{
		$cnt = 0;

		for ($i = 0 ; $i < count($addresses) ; $i++)
		{
			if (strstr($address, $addresses[$i]))
			{
				$cnt++;
			}
		}

		return $cnt;
	}

	/**
	 * 指定住所が配列中に部分一致で含まれるか否かチェックする。
	 * また、適用すべき最悪のポイントを同時に算定する。
	 *
	 * @param string $address 住所
	 * @param array $addresses 比較対象の住所とポイントの配列
	 * @return int マッチした数
	 */
	public static function isMatchUnitingAddressWithBaddestPoint($address, $addresses)
	{
		$cnt = 0;
		$point = 0;

		foreach ($addresses as $d)
		{
			if (strstr($address, $d["Cstring"]))
			{
				$cnt++;
				$point += $d["Point"];
			}
		}

		$result["Count"] = $cnt;
		$result["Point"] = $point;

		return $result;
	}

	/**
	 * 月末か否かを調べる
	 * @param string $date
	 * @return boolean
	 */
	public static function isGetsumatsu($date)
	{
		return date("d", strtotime($date) + 86400) == '01';
	}

	/**
	 * 月の最初の立替確定か否かを調べる
	 * @param string $date
	 * @return boolean
	 *
	 * 立替確定が毎週水曜日に実行されることを前提としている
	 */
	public static function isFirstTatekae($date)
	{
		$t = strtotime($date);
		$s = intval(date("t", $t)) - intval(date("d", $t));

		return $s <= 4;
	}

	/**
	 * キャラクターセットをISO-2022-JPに変更する。
	 */
	public static function toMailChar($str)
	{
		return mb_convert_encoding($str, 'ISO-2022-JP', 'UTF-8');
	}

	/**
	 * MIMEエンコード
	 *
	 * @param unknown_type $str
	 * @return unknown
	 */
	public static function toMailCharMime($str)
	{
		return mb_encode_mimeheader(mb_convert_encoding($str, 'JIS', 'UTF-8'));
	}

	public static function escapeDoubleQuate($str) {
	    return '"' . preg_replace('/"/', '""', $str) . '"';
	}

	public static function deleteMultiByte($str) {
	    return preg_replace("/([\x{3005}\x{3007}\x{303b}\x{3400}-\x{9FFF}\x{F900}-\x{FAFF}\x{20000}-\x{2FFFF}])/u", "", $str);
	}

	/**
	 * ランダム文字列生成 (英数字)
	 * $length: 生成する文字数
	 */
	public static function makeRandStr($length) {
	    static $chars = 'abcdefghijkmnpqrstuvwxyzABCDEFGHJLKMNOPQRSTUVWXYZ123456789';
	    $str = '';
	    for ($i = 0; $i < $length; ++$i) {
	        $str .= $chars[mt_rand(0, 57)];
	    }
	    return $str;
	}

	/**
	 * Base64エンコード（URLセーフ）
	 *
	 * @param string $val エンコード対象文字列
	 * @return base64エンコード文字列
	 */
	public static function base64_urlsafe_encode($val) {
	    $val = base64_encode($val);
	    return str_replace(array('+', '/', '='), array('_', '-', '.'), $val);
	}

	/**
	 * Base64デコード（URLセーフ）
	 *
	 * @param string $val デコード対象文字列
	 * @return base64デコード文字列
	 */
	public static function base64_urlsafe_decode($val) {
	    $val = str_replace(array('_', '-', '.'), array('+', '/', '='), $val);
	    return base64_decode($val);
	}
}

