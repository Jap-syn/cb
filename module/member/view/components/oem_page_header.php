<?php
//--------------------------------
// コンテンツの共通ヘッダ部分
$oemId = Application::getInstance()->authManager->getUserInfo()->OemId;
if($oemId == 0) { 
    $logo = 'Atobarai_logo_2.gif';
    $mail = 'customer@ato-barai.com';
    $sup = 'support_info_2.gif';
} else {
    $oemInfo = Application::getInstance()->getOemInfo($oemId);
    $logo = 'oemcom.png';
    $mail = $oemInfo['MailAddress'];
    $sup = 'support_info_oem.gif';
}
?>

<h1 class="application_header">

	<?php echo $this->valueFormat($this->applicationTitle); ?>
</h1>
<noscript>
<div class="no_script_message">
本サイトをご利用するには、JavaScriptが ON になっている必要があります。
</div>
</noscript>
