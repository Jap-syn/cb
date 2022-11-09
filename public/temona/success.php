<?php
$urlRedirect = "https://" . $_SERVER['SERVER_NAME'] . '/temona/orderpage/index/index';
$fileName = __DIR__ . '/../../data/log' . '/sbps_log_' . date('Ymd') . '.txt';
$file = fopen($fileName, "a+");
$for_log = mb_convert_encoding($_REQUEST, mb_internal_encoding(), "SJIS");
fwrite($file, date('Y-m-d H:i:s') . "+09:00 INFO (6): 購入結果（画面返却） 成功:" . json_encode($for_log));
header("Location: $urlRedirect");
die();
?>
