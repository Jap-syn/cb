 <!-- navigation start -->
 <div id="navigation">
<?php if( $this->current_page == 'settlement' || $this->current_page == 'settlementlist' || $this->current_page == '' ) { ?>
  <ul>
   <li><a href="." class="tabs" ><span>トップ</span></a></li>
   <li><a href="oemclosing/index" class="tabs" ><span>OEM明細確認</span></a></li>
   <li><a href="oemmonthly/settlement/oemid/<?php echo f_e($this->oemInfo['OemId']);?>/fd/<?php echo f_e($this->fd);?>" class="tabs<?php if( $this->current_page == 'settlement') { echo " current"; } ?>"><span>OEM精算書</span></a></li>
   <li><a href="oemmonthly/settlementlist/oemid/<?php echo f_e($this->oemInfo['OemId']);?>/fd/<?php echo f_e($this->fd);?>" class="tabs<?php if( $this->current_page == 'settlementlist') { echo " current"; } ?>"><span>OEM精算明細一覧</span></a></li>
   </ul>
  <?php echo $this->render('cbadmin/id_search_form.php'); ?>
  <?php } else { ?>
   <?php echo $this->render('cbadmin/id_search_form.php'); ?>
   <br />
  <ul>
   <li><a href="." class="tabs" ><span>トップ</span></a></li>
   <li><a href="oemmonthly/summary/oemid/<?php echo $this->oemInfo['OemId'];?>/eid/<?php echo $this->entInfo['EnterpriseId'];?>/fd/<?php echo f_e($this->fd);?>" class="tabs<?php if( $this->current_page == 'summary') { echo " current"; } ?>"><span>OEM請求書兼納品書</span></a></li>
   <li><a href="oemmonthly/chargelist/oemid/<?php echo $this->oemInfo['OemId'];?>/eid/<?php echo $this->entInfo['EnterpriseId'];?>/fd/<?php echo f_e($this->fd);?>" class="tabs<?php if( $this->current_page == 'chargelist') { echo " current"; } ?>"><span>OEMお取引明細</span></a></li>
   <li><a href="oemmonthly/stamplist/oemid/<?php echo $this->oemInfo['OemId'];?>/eid/<?php echo $this->entInfo['EnterpriseId'];?>/fd/<?php echo f_e($this->fd);?>" class="tabs<?php if( $this->current_page == 'stamplist') { echo " current"; } ?>"><span>OEM印紙代明細</span></a></li>
   <li><a href="oemmonthly/cancellist/oemid/<?php echo $this->oemInfo['OemId'];?>/eid/<?php echo $this->entInfo['EnterpriseId'];?>/fd/<?php echo f_e($this->fd);?>" class="tabs<?php if( $this->current_page == 'cancellist') { echo " current"; } ?>"><span>OEMｷｬﾝｾﾙ返金明細</span></a></li>
   <li><a href="oemmonthly/adjustmentlist/oemid/<?php echo $this->oemInfo['OemId'];?>/eid/<?php echo $this->entInfo['EnterpriseId'];?>/fd/<?php echo f_e($this->fd);?>" class="tabs<?php if( $this->current_page == 'adjustmentlist') { echo " current"; } ?>"><span>OEM調整金明細</span></a></li>
   <li><a href="oemmonthly/paybacklist/oemid/<?php echo $this->oemInfo['OemId'];?>/eid/<?php echo $this->entInfo['EnterpriseId'];?>/fd/<?php echo f_e($this->fd);?>" class="tabs<?php if( $this->current_page == 'paybacklist') { echo " current"; } ?>"><span>OEM立替精算戻し明細</span></a></li>
   </ul>
  <?php } ?>
 </div>
 <!-- navigation end -->
