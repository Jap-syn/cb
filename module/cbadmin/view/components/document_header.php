<?php
//--------------------------------
// HTMLドキュメントの共通ヘッダ
// [delete]
//include_once 'functions.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="ja-JP">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="Content-Style-Type" content="text/css" />
	<meta http-equiv="Content-Script-Type" content="text/javascript" />
	<meta name="robots" content="noindex" />
	<base href="<?php echo $this->baseUrl; ?>/" />
	<title><?php echo f_e($this->pageTitle); ?></title>
<?php foreach( $this->styleSheets as $path ) { ?>
	<link rel="styleSheet" href="<?php echo $path; ?>" />
<?php } ?>
<?php foreach( $this->javaScripts as $path ) { ?>
	<script src="<?php echo $path; ?>"></script>
<?php } ?>
