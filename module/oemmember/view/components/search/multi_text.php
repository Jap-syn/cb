<?php
$condition = $this->currentCondition;
$id = $conditions[ 'column' ];
?>
<textarea name="<?php echo f_e($id); ?>" id="<?php echo f_e($id); ?>" size="20"><?php echo isset($this->searchValues[ $id ]) ? f_e($this->searchValues[ $id ]) : null; ?>
</textarea>