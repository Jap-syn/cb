<div id="header_creditsettlement">
  <!-- <table>
    <tr>
      <td><img src="../images/Atobarai_logo_4.png" alt="後払いドットコム" width="110" height="20" style="margin-left: 5px" /></td>
    </tr>
  </table> -->
  <table>
    <tr>
<?php if (isset($this->siteLogo)) { ?>
      <td width="90px"><?php echo '<img src="data:image/jpeg;base64,' . f_e($this->siteLogo) . '"' .  ' style="width: 80px; height: 50px; margin-left: 5px; border-radius: 0px;" />'; ?></td>
<?php } else { ?>
      <td width="90px" style="height: 50px; margin-left: 5px; border-radius: 0px;"></td>
<?php } ?>
      <td class="l_area"><?php echo f_e($this->entsiteName); ?></td>
    </tr>
  </table>
</div>
