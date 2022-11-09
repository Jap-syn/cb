 <!-- navigation start -->
 <div id="navigation">
  <ul>
   <li><a href="." class="tabs" ><span>トップ</span></a></li>
   <li><a href="enterpriseoperator/list/eid/<?php echo f_e($this->eid); ?>" class="tabs" ><span>加盟店オペレーター一覧</span></a></li>
   <li><a href="enterpriseoperator/form/eid/<?php echo f_e($this->eid); ?>" class="tabs" ><span>加盟店オペレーター登録</span></a></li>
  </ul>
  <?php echo $this->render('cbadmin/id_search_form.php'); ?>
 </div>
 <!-- navigation end -->
