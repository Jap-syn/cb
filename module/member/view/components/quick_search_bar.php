<div class="quick_serach_bar">
<form id="quick_search_form" method="post" action="search/quick">
	注文ID：<input type="text" name="SearchKey" id="quick_search_key" disabled="disabled" value="<?php echo $this->quick_searck_key;  ?>" />
	<button id="quick_search_submit" disabled="disabled" type="submit">検索</button>
</form>
</div>
<script>
Event.observe( $("quick_search_form"), "submit", function(evt) {
	var ret = true;
	$("quick_search_key").value = $("quick_search_key").value.replace( /[^\da-zA-Z]/g, "" );
	if( $("quick_search_key").value.trim().length == 0 ){ $("quick_search_key").value = '0'; }
	try {
		if( $("quick_search_key").value.trim().length == 0 ) ret = false;
	} catch(e) {
		Event.stop( evt );
		ret = false;
		Debug.write( e );
	}
	return ret;
}.bindAsEventListener( $("quick_search_form") ) );
</script>
