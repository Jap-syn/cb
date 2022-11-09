<?php
//--------------------------------
// コンテンツの共通ヘッダ部分
use member\Application;

$oemInfo = Application::getInstance()->authManager->getUserInfo();
// TODO 2015/01/13 suzuki エラーになるようなので変更
// $oemId = $oemInfo ? $oemInfo->OemId : null;
$oemId = isset($oemInfo) ? $oemInfo->OemId : null;
$mailBase = Application::getInstance()->getMail(0);
$mailTodo = Application::getInstance()->getMail(1);

if($oemId == 0 || $oemId == NULL) {
    $logo = 'Atobarai_logo_2.gif';
    $mail = $mailBase;
    $sup = 'support_info_2.gif';
    if (!empty($oemInfo)) {
        $enterpriseId = $oemInfo->EnterpriseId;
        if (Application::getInstance()->hasPaymentAfterArrivalFlg($enterpriseId)) {
            $mail = $mailTodo;
        }
    }
} else {
    $oemInfo = Application::getInstance()->getOemInfo($oemId);
    $logo = 'oemcom.png';
    $mail = $oemInfo['MailAddress'];
    $sup = 'support_info_oem.gif';
}
?>

<h1 class="application_header">
	<span>
		<a href="index/index">
			<img id="app_logo" src="../images/<?php echo $logo; ?>" title="トップページへ"/>
		</a>
	</span>
	<img id="support_info" src="../images/<?php echo $sup; ?>" />
    <?php
    if (!empty($oemInfo)) {?>
        <a id="mailto" href="mailto:<?php echo $mail; ?>" title="お問い合わせはこちらまで"><?php echo $mail; ?></a>
    <?php }
    ?>
	<img id="secure_seal" src="../images/rapidssl_ssl_certificate.gif" />
	<?php // echo $this->valueFormat($this->applicationTitle); ?>
	<?php echo $this->applicationTitle; ?>
	</h1>
<noscript>
<div class="no_script_message">
本サイトをご利用するには、JavaScriptが ON になっている必要があります。
</div>
</noscript>
