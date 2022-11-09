<!-- navigation start -->
 <div id="navigation">
  <?php echo $this->render('cbadmin/id_search_form.php'); ?>
 <ul>

   <li><a href="." class="tabs" ><span>トップ</span></a></li>
   <li><a href="<?php echo f_e($this->link['summary']); ?>" class="tabs" ><span>請求書兼納品書</span></a></li>
   <li><a href="<?php echo f_e($this->link['chargelist']); ?>" class="tabs" ><span>お取引明細</span></a></li>
   <li><a href="<?php echo f_e($this->link['stamplist']); ?>" class="tabs" ><span>印紙代明細</span></a></li>
   <li><a href="<?php echo f_e($this->link['cancellist']); ?>" class="tabs" ><span>ｷｬﾝｾﾙ返金明細</span></a></li>
   <li><a href="<?php echo f_e($this->link['adjustmentlist']); ?>" class="tabs" ><span>調整額明細</span></a></li>
   <li><a href="<?php echo f_e($this->link['paybacklist']); ?>" class="tabs" ><span>立替精算戻し明細</span></a></li>
  </ul>
 </div>
 <!-- navigation end -->
