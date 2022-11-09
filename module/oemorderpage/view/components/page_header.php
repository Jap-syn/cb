<?php
use oemorderpage\Application;
//--------------------------------
// コンテンツの共通ヘッダ部分
$oemInfo = $this->currentOemInfo;
$app = Application::getInstance();
?>

<!-- Header start -->
<div id="header">
    <?php if (!$app->isOemActive()) { ?>
      <div id="titleimg"><img src="../images/Atobarai_logo.gif" alt="後払いドットコム" width="138" height="25" /></div>
    <?php } else { ?>
      <div id="titleimg"><img src="resource/image/type/logo-l" alt="<?php echo f_e($oemInfo['ServiceName']); ?>" width="138" height="25" /></div>
    <?php } ?>

    <?php if ($app->authManager->isAuthenticated()) { ?>
        <div id="loginstatus01">
            <div id="user_info" class="l_float"><?php echo $this->userInfo; ?></div>
            <div id="logout_btn" class="r_float"><a href="login/logout">ログアウト</a></div>
        </div>
    <?php } ?>
</div>

<!-- Header end -->
