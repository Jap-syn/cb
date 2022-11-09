<?php
//--------------------------------
// コンテンツの共通フッタ
use oemadmin\Application;

$oemInfo = Application::getInstance()->getOemInfo();
?>
<div id="footer">
	<?php echo $oemInfo['Copyright'];  ?>
</div>