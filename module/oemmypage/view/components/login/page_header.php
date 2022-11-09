<?php
//--------------------------------
// コンテンツの共通ヘッダ部分
$oemInfo = $this->currentOemInfo;
?>
<style>
img#app_logo {
    height: 30px;
    left: 5px;
    top: 12px;
    border: none 0px;
}
</style>
<!-- Header start -->
<div id="header">
  <div id="titleimg"><img id="app_logo" src="resource/image/type/logo-l" alt="<?php echo f_e($oemInfo['ServiceName']); ?>" /></div>
</div>
<!-- Header end -->
