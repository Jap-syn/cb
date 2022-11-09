<?php
use oemorderpage\Application;
$oem_info = Application::getInstance()->getCurrentOemData();
// よくある質問リンク
$prm = array(':OemId' => $oem_info['OemId']);
$row = Application::getInstance()->dbAdapter->query(" SELECT KeyContent FROM MV_Code WHERE CodeId = 103 AND KeyCode = :OemId ")->execute($prm)->current();
$linkFAQ = ($row) ? $row['KeyContent'] : '';
?>
<?php $url = str_replace( '/orderpage', '/mypage', $this->baseUrl ); ?>

<div id="menu">
    <div id="menu_title"><span>メニュー</span></div>
    <div id="menu_content" ><a href="index/index"><span>ご購入情報</span></a></div>
    <?php if ($oem_info['AccessId'] != 'smbcfs') { /* SMBCの場合は非表示とする */ ?>
    <div id="menu_content"><a href="<?php echo ($this->registFlg == 1 ? $this->baseUrl : $url ) . $this->link; ?>" target="_blank"><span><?php echo $this->linkName; ?></span></a></div>
    <?php } ?>
    <?php if ($linkFAQ != '') { ?>
    <div id="menu_content" ><a href="<?php echo $linkFAQ; ?>"><span>よくある質問</span></a></div>
    <?php } ?>
</div>
