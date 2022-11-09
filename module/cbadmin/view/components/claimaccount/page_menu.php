 <!-- navigation start -->
 <div id="navigation">
  <ul>
   <li><a href="." class="tabs" ><span>トップ</span></a></li>
   <li><a href="claimaccount/list" class="tabs<?php if($this->current_action == 'list') echo ' current'; ?>"><span>請求口座一覧</span></a></li>
   <li><a href="claimaccount/edit" class="tabs<?php if($this->current_action == 'edit') echo ' current'; ?>"><span>請求口座設定</span></a></li>
  </ul>
  <?php echo $this->render('cbadmin/id_search_form.php'); ?>
 </div>
 <!-- navigation end -->
