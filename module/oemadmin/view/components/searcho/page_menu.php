<?php
$action = $this->currentAction;
$defaults = array('form', 'search', 'dcsv');
$quicks = array('qform', 'qsearch', 'qdcsv');
?>
 <!-- navigation start -->
 <div id="navigation">
  <ul>
   <li><a href="." class="tabs" ><span>トップ</span></a></li>|
   <li><a href="searcho/form/searchkbn/0" class="tabs<?php if(in_array($action, $defaults)) echo ' current'; ?>" ><span>検索フォーム</span></a></li>|
   <li><a href="searcho/qform" class="tabs<?php if(in_array($action, $quicks)) echo ' current'; ?>" ><span>簡易検索フォーム</span></a></li>
  </ul>
 </div>
 <!-- navigation end -->
