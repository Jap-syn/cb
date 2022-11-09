<?php
$condition = $this->currentCondition;
$id = $condition['column'];
$selectValue = isset($this->searchValues[ "{$id}_Mode" ]) ? $this->searchValues[ "{$id}_Mode" ] : null;
?>
<div>
	<select id="<?php echo "{$id}_Mode"; ?>" name="<?php echo "{$id}_Mode"; ?>" class="default">
		<option value="0" label="直接指定"<?php if( ! $selectValue ) { echo ' selected="selected"'; } ?>>直接指定</option>
		<option value="1" label="範囲指定"<?php if( $selectValue ) { echo ' selected="selected"'; } ?>>範囲指定</option>
	</select>
</div>
<div>
	<input type="text" class="span" name="<?php echo f_e($id); ?>" id="<?php echo f_e($id); ?>" size="20" value="<?php echo isset($this->searchValues[$id]) ? f_e($this->searchValues[$id]) : null; ?>" />
	&nbsp;～&nbsp;
	<input type="text" class="span" name="<?php echo f_e("{$id}_2"); ?>" id="<?php echo f_e("{$id}_2"); ?>" size="20"<?php if( ! $selectValue ) { echo ' disabled="disabled"'; } ?>  value="<?php echo isset($this->searchValues["{$id}_2"]) ? f_e($this->searchValues["{$id}_2"]) : null; ?>"/>
</div>
<script>
with( {
	left_id : "<?php echo $id; ?>",
	right_id : "<?php echo "{$id}_2"; ?>"
} ) {
	Event.observe( $("<?php echo "{$id}_Mode"; ?>"), "change", function(evt) {
		Controls.changeControlDisabled( $(right_id), this.selectedIndex == 0 );
	}.bindAsEventListener( $("<?php echo "{$id}_Mode"; ?>") ), false );
}
</script>