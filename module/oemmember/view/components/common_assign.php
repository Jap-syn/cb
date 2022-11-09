<?php
use oemmember\Application;

$this->currentOemInfo = $oemInfo = Application::getInstance()->getCurrentOemData();
$this->applicationTitle = sprintf('%s後払い決済管理システム', nvl($oemInfo['ServiceName']));
?>