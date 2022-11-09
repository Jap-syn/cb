<?php
use mypage\Application;

// よくある質問リンク
$row = Application::getInstance()->dbAdapter->query(" SELECT KeyContent FROM MV_Code WHERE CodeId = 103 AND KeyCode = 0 ")->execute(null)->current();
$linkFAQ = ($row) ? $row['KeyContent'] : '';
?>
<div id="menu">
    <div id="menu_title"><span>メニュー</span></div>
    <div id="menu_content" ><a href="index/index"><span>ご購入履歴</span></a></div>
    <div id="menu_content" ><a href="edit/index"><span>会員登録情報編集</span></a></div>
    <div id="menu_content" ><a href="edit/passchg"><span>パスワード変更</span></a></div>
    <?php if ($linkFAQ != '') { ?>
    <div id="menu_content" ><a href="<?php echo $linkFAQ; ?>"><span>よくある質問</span></a></div>
    <?php } ?>
    <div id="menu_content" ><a href="edit/withdraw"><span>退会</span></a></div>
</div>
