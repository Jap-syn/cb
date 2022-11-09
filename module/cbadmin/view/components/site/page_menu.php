 <!-- navigation start -->
 <div id="navigation">
  <ul>
   <li><a href="." class="tabs" ><span>トップ</span></a></li>
   <li><a href="site/list/eid/<?php echo f_e($this->EnterpriseId); ?>"  class="tabs" ><span><?php echo f_e($row); ?>サイト一覧</span></a></li>
   <li><a href="site/regist/eid/<?php echo f_e($this->EnterpriseId); ?>" class="tabs" ><span>サイト登録</span></a></li>
  </ul>
  <?php echo $this->render('cbadmin/id_search_form.php'); ?>
 </div>
 <!-- navigation end -->
