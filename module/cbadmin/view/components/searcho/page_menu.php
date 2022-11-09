<?php
$action = $this->currentAction;
$defaults = array('form', 'search', 'dcsv');
$quicks = array('qform', 'qsearch', 'qdcsv');
$specials = array('sform', 'ssearch', 'sdcsv');
?>
 <!-- navigation start -->
 <div id="navigation">
  <ul>
   <li><a href="." class="tabs" ><span>トップ</span></a></li>
   <li><a href="searcho/qform"/form class="tabs<?php if(in_array($action, $defaults)) echo ' current'; ?>" ><span>検索フォーム</span></a></li>
   <li><a href="searchf/form" class="tabs" ><span>不払検索フォーム</span></a></li>
   <li><a href="searcho/rformcsv" class="tabs" ><span>注文検索結果CSV</span></a></li>
  </ul>
  <?php echo $this->render('cbadmin/id_search_form.php'); ?>
 </div>
 <!-- navigation end -->
