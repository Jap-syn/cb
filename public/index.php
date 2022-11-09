<?php

setlocale( LC_ALL, 'ja_JP.UTF-8' );
ini_set( 'default_charset', 'UTF-8' );
mb_internal_encoding( 'UTF-8' );
mb_http_output('UTF-8');
mb_regex_encoding( 'UTF-8' );

ini_set( 'max_execution_time', 600 );

if (isset($_SERVER['HTTPS'])) { ini_set('session.cookie_secure', 1); }

/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
chdir(dirname(__DIR__));

// ﾁｪｯｸﾌｧｲﾙﾊﾟｽ(固定値)
$chkfile = "/var/www/html/NgAccessIp/";

// $_SERVERへｸﾗｲｱﾝﾄIPｱﾄﾞﾚｽの登録
$_SERVER['NGCHECK_REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'];
if ( isset($_SERVER['HTTP_X_FORWARDED_FOR']) ) {
    $ip_array = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
    $_SERVER['NGCHECK_REMOTE_ADDR'] = $ip_array[0];
}

// $_SERVERへﾁｪｯｸﾌｧｲﾙﾊﾟｽの登録
$_SERVER['NGCHECK_FILE_PATH'] = $chkfile;

// ﾁｪｯｸﾌｧｲﾙが存在していればIPｱﾄﾞﾚｽの登録ﾁｪｯｸをし、登録があればｴﾗｰ出力する(以降の処理を行わない)
if (file_exists($chkfile . $_SERVER['NGCHECK_REMOTE_ADDR'] . '.txt')) {
    // ﾁｪｯｸ対象にﾋｯﾄ
    echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">";
    echo "<html lang=\"ja-JP\">";
    echo "<h1>401 Authorization Required</h1>";
    echo "<br>";

    if (strpos($_SERVER['REQUEST_URI'], '/seino-financial/') === 0) {
        // (セイノーフィナンシャル)
        echo "ログイン エラー回数が規定回数を超えました。 セキュリティ保護のため、ログインを制限しています。<br>";
        echo "制限を解除するためには、お手数ですがサポートセンターまでお電話にてお問い合わせください。<br><br>";
        echo "サポートセンター電話番号： TEL: 03-4326-3610（9:00～18:00）";
    }
    else if (strpos($_SERVER['REQUEST_URI'], '/estore/') === 0) {
        // (Eストアー)
        echo "ログイン エラー回数が規定回数を超えました。 セキュリティ保護のため、ログインを制限しています。<br>";
        echo "制限を解除するためには、お手数ですがサポートセンターまでお電話にてお問い合わせください。<br><br>";
        echo "サポートセンター電話番号： TEL: 0570-05-1210（平日 10:00～18:00）";
    }
    else if (strpos($_SERVER['REQUEST_URI'], '/smbcfs/') === 0) {
        // (SMBC)
        echo "ログイン エラー回数が規定回数を超えました。 セキュリティ保護のため、ログインを制限しています。<br>";
        echo "制限を解除するためには、お手数ですがサポートセンターまでお電話にてお問い合わせください。<br><br>";
        echo "サポートセンター電話番号： TEL: 0120-667-690（10:00 ～ 18:00）";
    }
    else if (strpos($_SERVER['REQUEST_URI'], '/base/') === 0) {
        // (BASE)
        echo "ログイン エラー回数が規定回数を超えました。 セキュリティ保護のため、ログインを制限しています。<br>";
        echo "制限を解除するためには、お手数ですがサポートセンターまでお電話にてお問い合わせください。";
    }
    else if (strpos($_SERVER['REQUEST_URI'], '/temona/') === 0) {
        // (テモナ)
        echo "ログイン エラー回数が規定回数を超えました。 セキュリティ保護のため、ログインを制限しています。<br>";
        echo "制限を解除するためには、お手数ですがサポートセンターまでお電話にてお問い合わせください。";
    }
    else if (strpos($_SERVER['REQUEST_URI'], '/mizuho/') === 0) {
        // (みずほファクター)
        echo "ログイン エラー回数が規定回数を超えました。 セキュリティ保護のため、ログインを制限しています。<br>";
        echo "制限を解除するためには、お手数ですがサポートセンターまでお電話にてお問い合わせください。";
    }
    else {
        // (直営[その他])
        echo "ログイン エラー回数が規定回数を超えました。 セキュリティ保護のため、ログインを制限しています。<br>";
        echo "制限を解除するためには、お手数ですがサポートセンターまでお電話にてお問い合わせください。<br><br>";
        echo "サポートセンター電話番号： TEL: 0120-667-690（10:00 ～ 18:00）";
    }

    echo "<html>";
    return;
}

// Decline static file requests back to the PHP built-in webserver
if (php_sapi_name() === 'cli-server' && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    return false;
}

// Setup autoloading
require 'init_autoloader.php';

// Run the application!
Zend\Mvc\Application::init(require 'config/application.config.php')->run();
