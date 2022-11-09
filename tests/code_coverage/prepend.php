<?php
require_once('my_code_coverage.php');

// 実行を行うか否かはファイルの存在可否で設定
$file = __DIR__ . DIRECTORY_SEPARATOR . "iscoverage.txt";
if (file_exists($file)){
    // ガバレッジ計測開始
    $cc = new MyCodeCoverage();
    function my_xdebug_shutdown() {
        global $cc;
        $cc->stopAndRecord();
    }
    register_shutdown_function('my_xdebug_shutdown');
    $cc->start();
}
