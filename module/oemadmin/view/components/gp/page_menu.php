<?php
$action = $this->currentAction;
?>
<!-- navigation start -->
 <div id="navigation">
  <ul>
   <li><a href="." class="tabs" ><span>トップ</span></a></li>|
   <li><a href="gp/notice" class="tabs<?php if($action == 'notice') echo ' current'; ?>" ><span>お知らせ</span></a></li>|
   <li><a href="gp/mailtf" class="tabs<?php if($action == 'mailtf') echo ' current'; ?>" ><span>メール</span></a></li>
  </ul>
 </div>
 <!-- navigation end -->
