<?php
$condition = $this->currentCondition;
$id = $condition[ 'column' ];
?>
<input style="width: 10px;" type="radio" name="Deli_Conditions" value="1" onclick="changeDeliConditions();">伝票番号登録済<input style="width: 10px;" type="radio" name="Deli_Conditions" value="2" onclick="changeDeliConditions();">伝票番号未登録<input style="width: 10px;" type="radio" name="Deli_Conditions" value="3" checked="checked" onclick="changeDeliConditions();">全て<br />
<input type="text" name="<?php echo f_e($id); ?>" id="<?php echo f_e($id); ?>" value="<?php echo (isset($this->searchValues[$id])) ? f_e($this->searchValues[ $id ]) : null; ?>" size="25" />