<?php
//--------------------------------
// コンテンツの共通ヘッダ部分
?>

<table width="819" border="0" cellspacing="1" cellpadding="0">
  <tr>
    <td width="145"><img src="../images/Atobarai_logo.gif" alt="後払いドットコム" width="138" height="25" /></td>
    <td width="300" valign="bottom">オペレーションシステム</td>
    <td valign="bottom">
      <a href="login/logout" style="float: right; margin-right: 20px">ログアウト</a>
      ログイン中：<strong><?php echo $this->userInfo->NameKj; ?></strong>
      <div style="float: none; clear: both; font-size: 0px;"></div>
    </td>
  <?php echo $this->render('cbadmin/important_messages.php'); ?>
  </tr>
</table>
<div id="navigation" style="text-align: right;">
  <?php echo $this->render('cbadmin/id_search_form.php');?>
</div>


