<?php
//--------------------------------
// コンテンツの共通ヘッダ部分
?>
<!-- Header start -->
<div id="header">
<!--
  <h1 id="title"><a href="#"></a></h1>
-->
  <div id="titleimg"><a href="."><img src="../images/Atobarai_logo.gif" alt="後払いドットコム" width="138" height="25" /></a></div>
  <div id="loginstatus">ログイン中：<?php echo f_e($this->userInfo->NameKj); ?><a href="login/logout">ログアウト</a></div>
  <?php echo $this->render('cbadmin/important_messages.php'); ?>
</div>
<!-- Header end -->
