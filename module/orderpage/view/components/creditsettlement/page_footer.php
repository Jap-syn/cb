<?php if (!empty($this->specificTransUrlNew)) { ?>
  <div id="footer" style="height: 64px">
  <?php echo f_e($this->entsiteName); ?></br>
  <?php
  if(strpos( $this->specificTransUrlNew, "http://") !== false or strpos( $this->specificTransUrlNew, "https://") !== false) {?>
      <a href="<?php echo f_e($this->specificTransUrlNew); ?>" style="color: #009DE4; text-decoration: underline;" target="_blank">＞特定商取引に関する法律に基づく表示</a>
  <?php } else {?>
      <a href="//<?php echo f_e($this->specificTransUrlNew); ?>" style="color: #009DE4; text-decoration: underline;" target="_blank">＞特定商取引に関する法律に基づく表示</a>
  <?php }
  ?>
  <div style="text-align: right">powered by CatchBall, Inc.</div>
  </div>
<?php } else { ?>
  <div id="footer" style="height: 43px">
  <?php echo f_e($this->entsiteName); ?></br>
  <div style="text-align: right">powered by CatchBall, Inc.</div>
  </div>
<?php } ?>