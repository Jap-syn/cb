<center>
	<form id="current_search_conditions" action="search/search" method="post">
<?php
foreach( $this->searchValues as $name => $value ) {
	if( $name == 'Page' ) continue;
?>
	<?php if(is_array($value)) { ?>
	<?php foreach($value as $key => $child_value) { ?>
	<input type="hidden" name="<?php echo f_e($name); ?>[<?php echo f_e($key); ?>]" value="<?php echo f_e($child_value); ?>" />
	<?php } ?>
	<?php } else { ?>
	<input type="hidden" name="<?php echo f_e($name); ?>" value="<?php echo f_e($value); ?>" />
	<?php } ?>
<?php
}
?>
<input type="hidden" id="Page" name="Page" value="<?php echo f_e($this->current_page); ?>" />
	</form>
	<script>
// 再検索コマンド
["search", "backTo"].each( function(link) {
	if( ! $(link) ) throw $continue;
	Event.observe( $(link), "click", function(evt) {
		try {
			Object.extend( $("current_search_conditions"), {
				action : this.id == "backTo" ? "search/result" : "search/search"
			}).submit();
		} finally {
			evt.returnValue = false;
			Event.stop( evt );
		}
	}.bindAsEventListener($(link)) );
} );
	</script>
</center>
