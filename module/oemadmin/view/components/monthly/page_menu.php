<?php
use oemadmin\Application;

$action = $this->currentAction;
?>
<!-- navigation start -->
 <div id="navigation">
  <ul>
   <li><a href="." class="tabs" ><span>トップ</span></a></li>|
   <li><a href="monthly/settlement" class="tabs<?php if($action == 'settlement') echo ' current'; ?>" ><span>精算書</span></a></li>|
   <li><a href="monthly/store" class="tabs<?php if($action == 'store' || $action == 'storedetail' || $action == 'trading') echo ' current'; ?>" ><span>店舗別精算明細</span></a></li>|
   <li><a href="monthly/cancel" class="tabs<?php if($action == 'cancel' || $action == 'canceldetail') echo ' current'; ?>" ><span>キャンセル返金明細</span></a></li>
  </ul>
 </div>
 <!-- navigation end -->
