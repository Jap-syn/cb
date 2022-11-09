<?php
//--------------------------------
// HTMLドキュメントの共通ヘッダ
use mypage\Application;

$app_global = Application::getInstance()->appGlobalConfig;
if( isset( $app_global['page_title_prefix'] ) ) {
    $page_title_prefix = $app_global['page_title_prefix'] . ( ( isset( $app_global['page_title_separaotor'] )) ?
        $app_global['page_title_separator'] : ' : ' );
} else {
    $page_title_prefix = '';
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="ja-JP">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="no">
    <meta name="format-detection" content="telephone=no">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="Content-Style-Type" content="text/css" />
    <meta http-equiv="Content-Script-Type" content="text/javascript" />
    <base href="<?php echo $this->baseUrl; ?>/" />
    <title><?php echo $page_title_prefix . $this->pageTitle; ?></title>
<?php foreach( $this->styleSheets as $path ) { ?>
    <link rel="styleSheet" href="<?php echo $path; ?>" />
<?php } ?>
    <script>
    // acceleration for IE ( via: http://d.hatena.ne.jp/amachang/20071010/1192012056 )
    // fixed for ie9 by eda in 2012-10-12.
    /*@cc_on
    if(!( ('performance' in window)||('msPerformance' in window) )) {
        eval( ( function(props) {
            var code = [];
            for ( var i = 0, l = props.length; i<l; i++ ){
                var prop = props[i];
                window['_'+prop]=window[prop];
                code.push(prop+'=_'+prop)
            }
            return 'var '+code.join(',');
        })('document self top parent alert setInterval clearInterval setTimeout clearTimeout'.split(' ')));
    }
    @*/
    </script>
<?php foreach( $this->javaScripts as $path ) { ?>
    <script src="<?php echo $path; ?>"></script>
<?php } ?>
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-177563793-2"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'UA-177563793-2');
    </script>