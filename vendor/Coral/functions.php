<?php
/**
 * ビュースクリプトからの利用を想定したユーティリティ関数ライブラリ
 */

use Coral\Base\BaseGeneralUtils;
use Coral\Base\IO\BaseIOUtility;
use Coral\Base\Reflection\BaseReflectionUtility;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;

function nvl($value, $if_null = '') {
	$value = (string)$value;
	return ( $value === null || strlen($value) == 0 ) ?
		$if_null : $value;
}

/**
 * 指定の値に対してHTMLエスケープを適用する。
 * この関数は{@link htmlspecialchars}関数へのエイリアスで、
 * 引数も完全に同一のものを受け取る。
 * （参照：http://jp2.php.net/manual/ja/function.htmlspecialchars.php）
 *
 * @param string $string
 * @param int $quote_style = ENT_COMPAT
 * @param string $charset
 * @param bool $double_encode
 * @return $string に対して指定のエスケープを適用した文字列
 */
function f_e() {
	$_args = func_get_args();

	$args = array();
	array_push($args, array_key_exists(0, $_args) ? $_args[0] : '');
	array_push($args, array_key_exists(1, $_args) ? $_args[1] : ENT_QUOTES);
	array_push($args, array_key_exists(2, $_args) ? $_args[2] : mb_internal_encoding());

	return call_user_func_array( 'htmlspecialchars', $args );
}

/**
 * 指定の値に含まれる改行記号をHTMLのBR要素に置換する。
 * 改行置換に先立って、{@link f_e}関数が適用されるため、別途エスケープを
 * 適用する必要はない。
 * @param string $value 改行置換を適用する文字列
 * @param null|bool $useXHTML = true XHTMLスタイルを適用するかのフラグ
 * @return $value をHTMLエスケープした上で改行をBR要素に置き換えた文字列
 */
function f_br($value, $useXHTML = true) {
	$br = $useXHTML ? '<br />' : '<br>';
	return preg_replace('/((\r\n)|[\r\n])/', $br, f_e($value));
}

/**
 * 日付データを指定の書式でフォーマットする
 * @param string $date 日付データ (日付として有効な形式に限る 例:'Y-m-d') // Mod By Takemasa(NDC) 20141127
 * @param null|string $format 適用するフォーマット。省略時は'Y/m/d'
 * @param null|string $input $dateを解析するための入力フォーマットヒント。デフォルトは'Y/m/d H:i:s'
 * @return $dateに$formatの書式を適用した文字列
 */
function f_df($date, $format = 'Y/m/d', $input = 'Y/m/d H:i:s') {
	if( empty($date) ) return '';
	if( empty($format) ) $format = 'Y/m/d';
    return date($format, strtotime($date)); // Mod By Takemasa(NDC) 20141127
}

/**
 * 数値を指定の書式でフォーマットする
 * @param int $value 書式を適用する数値
 * @param null|string $format 適用する書式。省略時は'#,##0'
 * @return $valueに$formatの書式を適用した文字列
 */
function f_nf($value, $format = '#,##0') {
 	if( $value === null || !strlen((string)$value) ) return '';
 	if( empty($format) ) $format = '#,##0';
 	if( ! is_numeric( $value ) ) return $value;

 	$retval = valueFormat($value, 'number', '#,##0');

    return str_replace('#,##0', $retval, $format);
}

/**
 * 2つのパスを合成してパスを生成する。
 * この関数は{@link NetB_IO_Utility}::buildPath()のエイリアスである
 * @param string $path1 基準となるパス文字列
 * @param string $path2 $path1の後ろに結合するパス文字列
 * @param null|string $separator $path1と$path2を結合する区切り文字。省略時は'/'
 * @return $path1と$path2から生成されたパス文字列
 */
function f_path($path1, $path2, $separator = '/') {
    return BaseIOUtility::buildPath($path1, $path2, $separator);
}

/**
 * POSTされたJSONなどの構造化文字列のバックスラッシュエスケープ処理を行う。
 * PHPの設定でmagic_quotes_gpcがOnの場合はstripslashes()を適用、そうでない場合はなにも処理せず
 * 引数を返す
 * @param string $s 処理する文字列
 * @return string
 */
function process_slashes($s) {
	return ini_get('magic_quotes_gpc') ?
		stripslashes($s) : $s;
}

/**
 * 数値以外の文字を除去する。
 * @param string $value 処理する文字列
 * @return string
 */
function f_numeric($value) {
	return preg_replace("/[^0-9]+/", "", GeneralUtils::convertWideToNarrow($value));
}

/**
 * 空白を除去する。
 * @param string $value 処理する文字列
 * @return string
 */
function f_trim($value) {
	// 20150121 suzuki 文字化けするので変更
	//return mb_ereg_replace('[ 　]', '', nvl($value));
	$tmp = nvl($value);
    $tmp = str_replace(' ', '', nvl($tmp));
    $tmp = str_replace('　', '', nvl($tmp));
    return $tmp;
}

/**
 * 指定文字列を指定の文字エンコードに変換した場合のバイト長を計測する
 *
 * @param string $str 計測する文字列
 * @param string $enc 計測時の文字エンコード
 * @param null | string $from_enc $strの文字エンコード。省略時は$strから推測したエンコード
 * @return int $strを$encに変換した場合のバイト長
 */
function bytes_as_spec_enc($str, $enc, $from_enc = null) {
	$from_enc = nvl($from_enc, mb_detect_encoding($str));
	$str = mb_convert_encoding($str, $enc, $from_enc);
	return strlen($str);
}



// Add By Takemasa(NDC) 20141208 Stt
//TODO : functions.phpへ記述しておくと便利なものを、必要に応じてここへ追加する
/**
 * 文字列をコートする。
 * @param string $str コートする文字列
 * @return string
 */
function CoatStr($str) {
    return "'" . $str . "'";
}

/**
 * 配列の中の要素をSQLクエリIN句用に整形する
 * @param string $array 配列
 * @return string
 */
function MakeQueryValStrPhraseIn($array) {

    $retval = "";

    $count = 0;
    foreach ($array AS $row) {
        if ($count == 0) {
            $retval = $row;
        }
        else {
            $retval .= (", " . $row);
        }
        $count++;
    }

    return $retval;
}

/**
 * 配列の中の要素をSQLクエリIN句用に整形する
 * @param string $array 配列
 * @return string
 */
function MakeQueryValStrPhraseInWithCoat($array) {

    $retval = "";

    $count = 0;
    foreach ($array AS $row) {
        if ($count == 0) {
            $retval = CoatStr($row);
        }
        else {
            $retval .= (", " . CoatStr($row));
        }
        $count++;
    }

    return $retval;
}

/**
 * 日付として有効か？
 * @param string $str 検証する文字列
 * @return boolean
 */
function IsDate($str) {
    try {
        new \DateTime($str);
        return true;
    }
    catch(Exception $e) {
        return false;
    }
}

/**
 * 有効な書式化(YYYY-MM-DD形式限定)された日付文字列か？
 *
 * 例)
 * ○ : 2015-01-22
 * × : 2015-1-22            <= 月が2桁でない
 * × : 2015/01/22           <= セパレータが'-'でない
 * × : 20150122             <= セパレータがない
 * ○ : 2015-02-28
 * × : 2015-02-29           <= 日が有効でない(28日までしかない)
 * × : ２０１５-０１-２２   <= 全角文字は認めない
 *
 * @param string $ymd_date 日付文字列
 * @return boolean
 */
function IsValidFormatDate($ymd_date) {
    $pattern = '/^(\d{4})-(\d{2})-(\d{2})$/';

    if (preg_match($pattern, $ymd_date, $match)) {
        if (checkdate($match[2],$match[3],$match[1])) {
            return true;
        }
    }

    return false;
}

/**
 * 有効な日付文字列か？
 * (date_parseによるチェック版)
 *
 * @param string $ymd_date 日付文字列
 * @return boolean
 */
function IsValidDate($ymd_date) {
    $ary = date_parse($ymd_date);
    return ((int)$ary['warning_count'] == 0 && (int)$ary['error_count'] == 0);
}

function valueFormat($value = null, $type = 'string', $format = '', $replaceEmpty = null, $replaceString = null) {
    $obj = new \Coral\Coral\View\Helper\CoralViewHelperValueFormat();
    return $obj->valueFormat($value, $type, $format, $replaceEmpty, $replaceString);
}

function escape($str, $replaceEmpty = null, $replaceString = null) {
    $obj = new \Coral\Coral\View\Helper\CoralViewHelperValueFormat();
    return $obj->escape($str, $replaceEmpty, $replaceString);
}

function setReplaceEmpty($flag = false) {
    $obj = new \Coral\Coral\View\Helper\CoralViewHelperValueFormat();
    return $obj->setReplaceEmpty($flag);
}

/**
 * ResultInterface⇒Array変換
 *
 * @param  ResultInterface $ri
 * @return array|null
 */
function ResultInterfaceToArray($ri) {
    if (!( $ri instanceof ResultInterface )) {
        return array();
    }

    $rs = new ResultSet();
    return $rs->initialize($ri)->toArray();
}

// Add By Takemasa(NDC) 20141208 End


// Add By Isobe(NDC) 20150202 Stt
/**

 * @param string $mail_add メールアドレス
 * @return boolean
 */
function IsValidFormatMail($mail_add) {
    $pattern = '/^[^,]+[@＠][^,]+[.．][^,]+$/';

    if (preg_match($pattern, $mail_add, $match)) {
        return true;
    }
    return false;
}

/**
 *
 * @param string $phon_num 電話番号・FAX番号
 * @return boolean
 */
function IsValidFormatTell($phon_num) {
    $pattern = '/^[- 0-9－　０-９]+$/';

    if (preg_match($pattern, $phon_num, $match)) {
        return true;
    }
    return false;
}

/**
 *
 * @param string $ip_add 接続IPアドレス
 * @return boolean
 */
function IsValidFormatIp($ip_add) {
    $pattern = '/^[0-9\.\*;]*$/';

    if (preg_match($pattern, $ip_add, $match)) {
        return true;
    }
    return false;
}
// Add By Isobe(NDC) 20150202 End
// Add By Isobe(NDC) 20150203 Stt
/**
 *
 * @param string $rec_code 収納代行会社固有コード
 * @return boolean
 */
function IsValidFormatRecCode($rec_code) {
//     $pattern = '/^\d{5}$/u';
    $pattern = '/^[0-9]+$/';

    if (preg_match($pattern, $rec_code, $match)) {
        return true;
    }
    return false;
}

/**
 *
 * @param string $bar_name バーコード生成ロジック名
 * @return boolean
 */
function IsValidFormatBarName($bar_name) {
    $pattern = '/^[a-zA-Z0-9]{1,20}$/u';

    if (preg_match($pattern, $bar_name, $match)) {
        return true;
    }
    return false;
}
// Add By Isobe(NDC) 20150203 End

/**
 * T_User.UserIdの取得(代理認証考慮版)
 *
 * @param member\Application $app
 * @param int $userClass 区分(0：CBオペレーター,1：OEMオペレーター,2：加盟店)
 * @param int $seq シーケンス(区分が0の場合：T_Operator.OpId,区分が1の場合：T_OemOperator.OemOpId,区分が2の場合：T_Enterprise.EnterpriseId)
 */
function getUserInfoForMember($app, &$userClass, &$seq ) {
    $altUserInfo = $app->authManager->getAlternativeUserInfo();
    // 代理認証あり
    if (!is_null($altUserInfo)) {
        // CB
        if (!is_null($altUserInfo->OpId)) {
            $userClass = 0;
            $seq = $altUserInfo->OpId;
        }
        // OEM
        elseif (!is_null($altUserInfo->OemOpId)) {
            $userClass = 1;
            $seq = $altUserInfo->OemOpId;
        }
    }
    // 代理認証なし
    else {
        $userClass = 2;
        $seq = $app->authManager->getUserInfo()->EnterpriseId;
    }
}

/**
 * 指定された秒数を分単位に整形する
 * @param int $sec 整形する秒
 * @return string
 */
function f_sec($sec) {
    $sec = (int)$sec;
    return sprintf('%s:%s', floor($sec / 60), substr('00' . ($sec % 60), -2));
}

/**
 * ロードバランサー、ELB経由を考慮したクライアントIPアドレスを取得する
 * @return string
 */
function f_get_client_address() {
    $result = $_SERVER['REMOTE_ADDR'];

    if ( isset($_SERVER['HTTP_X_FORWARDED_FOR']) ) {
        // ロードバランサー、ELB経由の場合は HTTP_X_FORWARDED_FOR に値がセットされる
        // サーバーを複数経由している場合はカンマ区切りでセットされているので、一番目のIPを取得する
        $ip_array = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $result = $ip_array[0];
    }

    return $result;
}

/**
 * 月額固定費の取得
 * @param array $row
 * @return number
 */
function f_get_monthly_fee($row) {
    $monthlyFee = $row['MonthlyFee']
                + $row['IncludeMonthlyFee']
                + $row['ApiMonthlyFee']
                + $row['CreditNoticeMonthlyFee']
                + $row['NCreditNoticeMonthlyFee']
                + $row['ReserveMonthlyFee'];
    return (int)$monthlyFee;
}

/**
 * 次回請求月額固定費の取得
 * @param array $row
 * @return number
 */
function f_get_n_monthly_fee($row) {
    $monthlyFee = $row['N_MonthlyFee']
                + $row['N_IncludeMonthlyFee']
                + $row['N_ApiMonthlyFee']
                + $row['N_CreditNoticeMonthlyFee']
                + $row['N_NCreditNoticeMonthlyFee']
                + $row['N_ReserveMonthlyFee'];
    return (int)$monthlyFee;
}

/**
 * OEM月額固定費の取得
 * @param array $row
 * @return number
 */
function f_get_oemmonthly_fee($row) {
    $monthlyFee = $row['OemMonthlyFee']
                + $row['OemIncludeMonthlyFee']
                + $row['OemApiMonthlyFee']
                + $row['OemCreditNoticeMonthlyFee']
                + $row['OemNCreditNoticeMonthlyFee']
                + $row['OemReserveMonthlyFee'];
    return (int)$monthlyFee;
}

/**
 * 次回請求OEM月額固定費の取得
 * @param array $row
 * @return number
 */
function f_get_n_oemmonthly_fee($row) {
    $monthlyFee = $row['N_OemMonthlyFee']
                + $row['N_OemIncludeMonthlyFee']
                + $row['N_OemApiMonthlyFee']
                + $row['N_OemCreditNoticeMonthlyFee']
                + $row['N_OemNCreditNoticeMonthlyFee']
                + $row['N_OemReserveMonthlyFee'];
    return (int)$monthlyFee;
}

/**
 * 指定の日付文字列を整形する。
 * 主に区切り文字の統一を行うが、年パートのみ、4桁以下の場合はを2000年ベースの4桁に補正する処理が加わる
 *
 * @param string $date 日付文字列
 * @return 補正された日付文字列
 * @see CoralCsvHandlerOrderBuilderより移植
 */
function fixDateString($date) {
    $date = str_replace('.', '/', $date);
    $date = str_replace('-', '/', $date);
    $parts = explode('/', $date);
    if( count($parts) < 2 ) return $date;
    if( BaseReflectionUtility::isNumeric( $parts[0] ) && ((int)$parts[0]) < 1000 ) {
        // 1000以下（＝1～3桁）の場合は2000年ベースに読み替える
        $parts[0] = 2000 + ((int)$parts[0]);
    }
    return join('/', $parts);
}

/**
 * 書式化された年月日時分秒ミリ秒を取得
 *
 * @return 年月日時分秒ミリ秒文字列 (Ex.. 20151210114949113)
 * @see ミリ秒桁数は３桁
 */
function getFormatDateTimeMillisecond() {
    $date = new \DateTime();
    $time = microtime();
    $time_list = explode(' ', $time);
    $time_micro = explode('.', $time_list[0]);
    $date_str = $date->format('YmdHis').substr($time_micro[1], 0, 3);

    return $date_str;
}

/**
 * 引数文字列の左から検証し、有効指定文字以外検出でブレイクし、それまでに有効判定された文字列を戻す
 *
 * @param string $date 解析対象日付文字列
 * @return string それまでに有効判定された文字列
 */
function parseDateByLeft($date) {

    // 有効文字群
    $haystack = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '-', '/', '.');

    $retval = "";// 戻り値

    $len = mb_strlen($date);
    for ($i=0; $i<$len; $i++) {
        $word = mb_substr($date, $i, 1);
        if (!in_array($word, $haystack)) { break; }

        $retval .= $word;
    }

    return $retval;
}

if(!function_exists('mime_content_type')) {

    function mime_content_type($filename) {

        $mime_types = array(

                'txt' => 'text/plain',
                'htm' => 'text/html',
                'html' => 'text/html',
                'php' => 'text/html',
                'css' => 'text/css',
                'js' => 'application/javascript',
                'json' => 'application/json',
                'xml' => 'application/xml',
                'swf' => 'application/x-shockwave-flash',
                'flv' => 'video/x-flv',

                // images
                'png' => 'image/png',
                'jpe' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'jpg' => 'image/jpeg',
                'gif' => 'image/gif',
                'bmp' => 'image/bmp',
                'ico' => 'image/vnd.microsoft.icon',
                'tiff' => 'image/tiff',
                'tif' => 'image/tiff',
                'svg' => 'image/svg+xml',
                'svgz' => 'image/svg+xml',

                // archives
                'zip' => 'application/zip',
                'rar' => 'application/x-rar-compressed',
                'exe' => 'application/x-msdownload',
                'msi' => 'application/x-msdownload',
                'cab' => 'application/vnd.ms-cab-compressed',

                // audio/video
                'mp3' => 'audio/mpeg',
                'qt' => 'video/quicktime',
                'mov' => 'video/quicktime',

                // adobe
                'pdf' => 'application/pdf',
                'psd' => 'image/vnd.adobe.photoshop',
                'ai' => 'application/postscript',
                'eps' => 'application/postscript',
                'ps' => 'application/postscript',

                // ms office
                'doc' => 'application/msword',
                'rtf' => 'application/rtf',
                'xls' => 'application/vnd.ms-excel',
                'ppt' => 'application/vnd.ms-powerpoint',

                // open office
                'odt' => 'application/vnd.oasis.opendocument.text',
                'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        $ext = strtolower(array_pop(explode('.',$filename)));
        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        }
        elseif (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $filename);
            finfo_close($finfo);
            return $mimetype;
        }
        else {
            return 'application/octet-stream';
        }
    }
}