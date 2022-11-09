 <!-- navigation start -->
 <?php
 $is_edit_mode = $this->current_action == 'cvsagent/edit';
 $is_edit_or_add = preg_match('/cvsagent\/((add)|(edit))/', $this->current_action);
 ?>
 <div id="navigation">
  <ul>
   <li><a href="." class="tabs" ><span>トップ</span></a></li>
   <li><a href="cvsagent/list" class="tabs<?php if($this->current_action == 'cvsagent/list') echo ' current'; ?>"><span>収納代行会社一覧</span></a></li>
  <?php if($is_edit_mode) { ?>
   <li><a href="cvsagent/add" class="tabs<?php if($is_edit_or_add) echo ' current'; ?>"><span>収納代行会社設定</span></a></li>
  <?php } else { ?>
   <li><a href="cvsagent/add" class="tabs<?php if($is_edit_or_add) echo ' current'; ?>"><span>収納代行会社登録</span></a></li>
  <?php } ?>
  </ul>
  <?php echo $this->render('cbadmin/id_search_form.php'); ?>
 </div>
 <!-- navigation end -->