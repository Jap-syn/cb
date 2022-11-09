<?php
use orderpage\Application;
//--------------------------------
// コンテンツの共通ヘッダ部分
$oemInfo = $this->currentOemInfo;
$app = Application::getInstance();
$this->render('orderpage/common_assign.php');
?>

<!-- Header start -->
<div id="header">
    <?php if (!$app->isOemActive()) { ?>

      <?php if ($this->flagHasSiteTodo) { ?>
        <div id="titleimg"><img src="../images/logo_ato_todo_color.png" alt="届いてから払い" width="150" height="auto" /></div>
      <?php } else { ?>
        <div id="titleimg"><img src="../images/Atobarai_logo_4.png" alt="後払いドットコム" width="138" height="25" /></div>
      <?php } ?>

    <?php } else { ?>
      <div id="titleimg"><img src="resource/image/type/logo-l" alt="<?php echo f_e($oemInfo['ServiceName']); ?>" width="138" height="25" /></div>
    <?php } ?>

    <?php if ($app->authManager->isAuthenticated()) { ?>
        <div id="loginstatus01">
            <div id="user_info" class="l_float"><?php echo $this->userInfo; ?></div>
            <div id="logout_btn" class="r_float"><a href="login/logout?oseq=<?php echo $this->OrderSeq; ?>">ログアウト</a></div>
        </div>
    <?php } ?>
</div>

<!-- Header end -->
