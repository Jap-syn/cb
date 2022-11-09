<?php
use orderpage\Application;
//--------------------------------
// コンテンツの共通ヘッダ部分
$oemInfo = $this->currentOemInfo;
$smallLogoTSite = $this->smallLogoTSite;
$app = Application::getInstance();
?>

<!-- Header start -->
<div id="header" style="width: 1010px;">
    <?php
      if(strpos($_SERVER['REQUEST_URI'], 'receiptpreview') !== false){
        $check = true;
      } else{
        $check = false;
      }
    ?>
    <?php if (!$app->isOemActive()) { ?>
      <?php if ($check) { ?>
        <?php if (!is_null($smallLogoTSite)) { ?>
          <div id="titleimg"><img src="data:image/jpeg;base64,<?php echo f_e($smallLogoTSite); ?>" alt="後払いドットコム" width="150" height="auto" /></div>
        <?php } ?>
      <?php } else { ?>
        <div id="titleimg"><img src="../images/logo_ato_todo_color.png" alt="届いてから払い" width="150" height="auto" /></div>
      <?php } ?>
    <?php } else { ?>
      <div id="titleimg"><img src="resource/image/type/logo-l" alt="<?php echo f_e($oemInfo['ServiceName']); ?>" width="138" height="25" /></div>
    <?php } ?>

    <div id="loginstatus01">
        <div id="user_info" class="l_float"><?php echo $this->userInfo; ?></div>
        <div id="logout_btn" class="r_float"><a href="index/index">戻る</a></div>
        <div id="logout_btn" class="r_float"><a href="receiptpdfoutput/print">印刷</a></div>
    </div>
</div>

<!-- Header end -->
