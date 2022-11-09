<?php
use oemorderpage\Application;
//--------------------------------
// コンテンツの共通フッタ
$oemInfo = $this->currentOemInfo;
$app = Application::getInstance();
?>
<div id="footer">
	<?php if (!$app->isOemActive()) { ?>
	   Copyright(C) 2016 By CatchBall, Inc.
	<?php } else { ?>
    	<?php echo $oemInfo['Copyright']  ?>
	<?php } ?>
</div>
