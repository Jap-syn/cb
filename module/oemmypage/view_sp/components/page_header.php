<?php
use oemmypage\Application;
//--------------------------------
// コンテンツの共通ヘッダ部分
$oemInfo = $this->currentOemInfo;
$prm = array(':OemId' => $oem_info['OemId']);
$row = Application::getInstance()->dbAdapter->query(" SELECT KeyContent FROM MV_Code WHERE CodeId = 103 AND KeyCode = :OemId ")->execute($prm)->current();
$linkFAQ = ($row) ? $row['KeyContent'] : '';
?>

<!-- Header start -->
<div id="header">
  <div id="titleimg"><img src="resource/image/type/logo-l" alt="<?php echo f_e($oemInfo['ServiceName']); ?>" width="110" height="20" /></div>
    <div id="loginstatus01">
        <div id="user_info" class="l_float"><?php echo $this->userInfo; ?></div>
        <div id="logout_btn" class="r_float"><a href="login/logout">ログアウト</a></div>
    </div>
</div>
<?php if( empty($this->billreissFlg) ) { ?>
<div>
    <table>
        <tr>
            <td><div id="menu_btn_sp"><a href="index/index"><span>ご購入<br/>履歴</span></a></div></td>
            <td><div id="menu_btn_sp"><a href="edit/index"><span>会員情<br/>報編集</span></a></div></td>
            <td><div id="menu_btn_sp"><a href="edit/passchg"><span>ﾊﾟｽﾜｰﾄﾞ<br/>変更</span></a></div></td>
            <?php if ($linkFAQ != '') { ?>
            <td><div id="menu_btn_sp" style="width: 56px"><a href="<?php echo $linkFAQ; ?>"><span>よくある<br/>質問</span></a></div></td>
            <?php } ?>
        </tr>
    </table>
</div>
<?php } ?>
<!-- Header end -->
