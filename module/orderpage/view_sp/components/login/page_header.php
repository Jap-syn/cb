<?php
//--------------------------------
// コンテンツの共通ヘッダ部分
?>

<!-- Header start -->
<div id="header">
  <?php
    $check = strpos($_SERVER['QUERY_STRING'], 'spapp2');
  ?>
  <?php
    if ($check === 0) {
  ?>
      <div id="titleimg"><img src="../images/logo_ato_todo_color.png" alt="届いてから払い" width="150" height="auto" /></div>
  <?php
    }
  ?>
  <?php
    if ($check === false) {
  ?>
      <div id="titleimg"><img src="../images/Atobarai_logo_4.png" alt="後払いドットコム" width="138" height="25" /></div>
  <?php
    }
  ?>
</div>
<!-- Header end -->
