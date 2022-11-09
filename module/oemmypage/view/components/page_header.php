<?php
//--------------------------------
// コンテンツの共通ヘッダ部分
$oemInfo = $this->currentOemInfo;
?>

<!-- Header start -->
<div id="header">
  <div id="titleimg"><img src="resource/image/type/logo-l" alt="<?php echo f_e($oemInfo['ServiceName']); ?>" width="138" height="25" /></div>
    <div id="loginstatus01">
        <div id="user_info" class="l_float"><?php echo $this->userInfo; ?></div>
        <div id="logout_btn" class="r_float"><a href="login/logout">ログアウト</a></div>
    </div>
</div>
<!-- Header end -->
