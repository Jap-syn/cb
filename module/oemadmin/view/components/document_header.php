<?php
//--------------------------------
// HTMLドキュメントの共通ヘッダ
// include_once 'functions.php';
// $oem_info = Application::getInstance()->getCurrentOemData();
$oem_info = isset($this->oemInfo) ? $this->oemInfo : array();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="ja-JP">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="Content-Style-Type" content="text/css" />
	<meta http-equiv="Content-Script-Type" content="text/javascript" />
	<meta name="robots" content="noindex" />
	<base href="<?php echo $this->baseUrl; ?>/" />
	<title><?php echo $this->pageTitle; ?></title>
<?php foreach( $this->styleSheets as $path ) { ?>
	<link rel="styleSheet" href="<?php echo $path; ?>" />
<?php } ?>
<?php foreach( $this->javaScripts as $path ) { ?>
	<script src="<?php echo $path; ?>"></script>
<?php } ?>
<?php if(is_array($oem_info) && $oem_info['FavIconType'] == 'url') { ?>
	<link rel="shortcut icon" href="<?php echo f_e($oem_info['FavIcon']); ?>" />
<?php } else { ?>
	<link rel="shortcut icon" href="<?php echo f_e($this->baseUrl); ?>/resource/favicon" />
<?php } ?>
