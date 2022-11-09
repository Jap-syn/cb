 <!-- navigation start -->
 <div id="navigation">
  <ul>
   <li><a href="." class="tabs" ><span>トップ</span></a></li>
   <?php if($this->current_page) { ?><li><a href="rwrcptcfm/list/page/<?php echo $this->current_page; ?>" class="tabs"><span>全件</span></a></li><?php } ?>
   <?php if($this->current_page) { ?><li><a href="rwrcptcfm/list/page/<?php echo $this->current_page; ?>/delay/0" class="tabs"><span>300件未満</span></a></li><?php } ?>
   <?php if($this->current_page) { ?><li><a href="rwrcptcfm/list/page/<?php echo $this->current_page; ?>/delay/1" class="tabs"><span>300件以上</span></a></li><?php } ?>
  </ul>
  <?php echo $this->render('cbadmin/id_search_form.php'); ?>
 </div>
 <!-- navigation end -->
