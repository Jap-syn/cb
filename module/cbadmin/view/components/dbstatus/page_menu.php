 <!-- navigation start -->
 <div id="navigation">
  <ul>
   <li><a href="." class="tabs" ><span>トップ</span></a></li>
  <?php if(!isset($this->ignore_other_menu) || !$this->ignore_other_menu) { ?>
   <li><a href="dbstatus" class="tabs<?php if($this->currentAction == 'dbstatus/index') echo ' current'; ?>"><span>DBの状態</span></a></li>
   <li><a href="dbstatus/order" class="tabs<?php if($this->currentAction == 'dbstatus/order') echo ' current'; ?>"><span>本日の注文登録状況</span></a></li>
   <li><a href="dbstatus/orderh" class="tabs<?php if($this->currentAction == 'dbstatus/orderh') echo ' current'; ?>"><span>本日の時間帯別登録状況</span></a></li>
   <li><a href="dbstatus/waitorders" class="tabs<?php if($this->currentAction == 'dbstatus/waitorders') echo ' current'; ?>"><span>社内与信待ち状況</span></a></li>
  <?php } ?>
  </ul>
  <?php echo $this->render('cbadmin/id_search_form.php'); ?>
 </div>
 <!-- navigation end -->
