<?php
//--------------------------------
// コンテンツの共通ヘッダ部分
?>

<!-- Header start -->
<div id="header">
  <!-- <div id="titleimg"><img src="../images/Atobarai_logo.gif" alt="後払いドットコム" width="138" height="25" /></div> -->
  <div id="titleimg"><img src="../images/logo_ato_todo_color.png" alt="届いてから払い" width="150" height="auto" /></div>
    <div id="loginstatus01">
        <div id="user_info" class="l_float"><?php echo $this->userInfo; ?></div>
        <div id="logout_btn" class="r_float"><a href="login/logout">ログアウト</a></div>
    </div>
</div>
<!-- Header end -->
