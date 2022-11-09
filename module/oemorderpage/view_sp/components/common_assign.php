<?php
use oemorderpage\Application;

$this->currentOemInfo = $oemInfo = Application::getInstance()->getCurrentOemData();
$this->smallLogoTSite = Application::getInstance()->getSmallLogoBySiteId();
$this->specificTransUrlNew = Application::getInstance()->getSpecificTransUrl();
$this->flagHasSiteTodo = Application::getInstance()->getFlagPaymentAfterArrivalFlg();
//$this->applicationTitle = sprintf('%s後払い決済管理システム', nvl($oemInfo['ServiceName']));
?>