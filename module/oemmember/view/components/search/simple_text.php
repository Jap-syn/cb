<?php
$condition = $this->currentCondition;
$id = $condition[ 'column' ];
?>
<input type="text" name="<?php echo f_e($id); ?>" id="<?php echo f_e($id); ?>" value="<?php echo (isset($this->searchValues[$id])) ? f_e($this->searchValues[ $id ]) : null; ?>" size="25" />