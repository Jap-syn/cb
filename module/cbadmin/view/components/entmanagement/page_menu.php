 <!-- navigation start -->
 <div id="navigation">
  <ul>
   <li><a href="." class="tabs" ><span>トップ</span></a></li>
   <li><a href="entmanagement/index" class="tabs<?php if($this->current_action == 'index') echo ' current'; ?>" ><span>事業者管理統計</span></a></li>
  </ul>
  <?php echo $this->render('cbadmin/id_search_form.php'); ?>
 </div>
 <!-- navigation end -->
