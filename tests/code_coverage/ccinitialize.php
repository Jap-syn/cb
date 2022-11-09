<?php
require_once('my_code_coverage.php');
$cc = new MyCodeCoverage();
$path = __DIR__ . "/../../";
echo $path . "\n";
$cc->initialize($path);
