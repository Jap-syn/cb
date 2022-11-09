<?php
//--------------------------------
// コンテンツの共通ヘッダ部分
use oemadmin\Application;

$oemInfo = Application::getInstance()->getCurrentOemData();
?>
<!-- Header start -->
<script type="text/javascript">
Event.observe(window, 'load', function() {
	Event.observe($('current-user-info'), 'mousedown', function() {
		new Element.ClassNames($('current-user-info')).add('rollovered');
	});
	Event.observe($('current-user-info'), 'mouseup', function() {
		new Element.ClassNames($('current-user-info')).remove('rollovered');
	});
});
</script>
            <div id="header">
                <div id="header_left">
                    <div id="header_logo">
						<a href=".">
                            <img src="resource/image/type/logo-l" alt="<?php echo f_e($oemInfo['ServiceName']); ?>" />
						</a>
					</div>
                    <p>オペレータ管理画面[<?php echo f_e(Application::getInstance()->getOemAccessId()); ?>]</p>
                </div>
                <div id="header_right">
                    <span id="current-user-info">ログイン中：<?php echo $this->userInfo->NameKj; ?></span>
                    <a href="login/logout">ログアウト</a>
                    <form action="searcho/qsearch" method="post">
                        <label>ID検索：</label>
                        <input type="text" name="order_id" id="search_form_ipt" />
                        <input value="検索" type="submit" id="search_form_smt">
                    </form>
                </div>
            </div>
			<div><?php echo $this->render('oemadmin/important_messages.php'); ?></div>
<!-- Header end -->
