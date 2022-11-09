 <!-- navigation start -->
 <div id="navigation">
  <ul>
   <li><a href="." class="tabs" ><span>トップ</span></a></li>
   <?php if( f_e($this->EnterpriseId) == -1 || f_e($this->EnterpriseId) == '' ) { ?>
    <li><a href="credit/pointform" class="tabs" ><span>与信条件設定</span></a></li>
    <li><a href="credit/search" class="tabs" ><span>与信条件検索</span></a></li>
    <li><a href="credit/new" class="tabs" ><span>与信条件新規登録</span></a></li>
   <?php } else { ?>
    <li><a href="credit/search/eid/<?php echo f_e($this->EnterpriseId); ?>" class="tabs" ><span>与信条件検索</span></a></li>
    <li><a href="credit/new/eid/<?php echo f_e($this->EnterpriseId); ?>" class="tabs" ><span>与信条件新規登録</span></a></li>
   <?php } ?>
  </ul>
  <?php echo $this->render('cbadmin/id_search_form.php'); ?>
 </div>
 <!-- navigation end -->
