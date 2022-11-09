<?php
//--------------------------------
// コンテンツの共通ヘッダ部分
use oemmember\Application;

//$oemInfo = Application::getInstance()->authManager->getUserInfo();
$oemInfo = $this->currentOemInfo;

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
			<img id="app_logo" src="resource/image/type/logo-l" alt="<?php echo f_e($oemInfo['ServiceName']); ?>" title="トップページへ" />
		</a>
	</span>
	<a id="mailto" href="mailto:<?php echo $oemInfo['SupportMail']; ?>" title="お問い合わせはこちらまで"><?php echo $oemInfo['SupportMail']; ?></a>
	<img id="secure_seal" src="../../images/rapidssl_ssl_certificate.gif" />
	<?php // echo $this->valueFormat($this->applicationTitle); ?>
	<?php echo $this->applicationTitle; ?>
	<?php if(!is_null($oemInfo['ServicePhone']) || !is_null($oemInfo['SupportTime'])){ ?>
	<div style="border: 1px lightgray solid; padding: 3px; position: absolute; right: 96px; font-size: 9pt; top: 3px; background-color: lightyellow;">
		<?php echo $oemInfo['ServicePhone']; ?>
		<?php if(!is_null($oemInfo['ServicePhone'])) echo '<br />'; ?>
		<?php echo $oemInfo['SupportTime']; ?>
	</div>
	<?php } ?>
	</h1>
<noscript>
<div class="no_script_message">
本サイトをご利用するには、JavaScriptが ON になっている必要があります。
</div>
</noscript>
