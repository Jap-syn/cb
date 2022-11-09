<?php
$condition = $this->currentCondition;
$id = $condition['column'];
$master = isset($this->masters[ $id ]) ? $this->masters[ $id ] : array();
$selectValue = isset($this->searchValues[ $id ]) ? $this->searchValues[ $id ] : null;
if( $selectValue === null || $selectValue == "" ) $selectValue = -99;
?>
<select id="<?php echo f_e($id); ?>" name="<?php echo f_e($id); ?>">

<?php foreach( $master as $key => $value ) { ?>
	<option value="<?php echo f_e($key); ?>" label="<?php echo f_e($value); ?>"<?php if( $key == $selectValue ) { echo ' selected="selected"'; } ?>><?php echo f_e($value); ?></option>
<?php } ?>
</select>

