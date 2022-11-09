<?php
$condition = $this->currentCondition;
$id = $condition['column'];
$master = $this->masters[ $id ];
$selectValues = isset($this->searchValues[ $id ]) ? $this->searchValues[ $id ] : null;
if( $selectValues === null || !is_array($selectValues) ) $selectValues = array();
?>
<?php foreach($master as $key => $value) { ?>
<?php $ele_id = sprintf('%s_val%s', $id, $key); ?>
<label for="<?php echo f_e($ele_id); ?>" style="margin-right: 4px; float: left">
	<input type="checkbox" id="<?php echo f_e($ele_id); ?>" name="<?php echo f_e($id); ?>[]" value="<?php echo f_e($key); ?>" <?php if(in_array($key, $selectValues)) echo f_e('checked="checked" '); ?>style="width: auto; margin: 2px;"/>
	<?php echo f_e($value); ?>
</label>
<?php } ?>
<div style="clear: both; float: none; height: 0; line-height: 0"></div>
