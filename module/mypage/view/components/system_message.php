<?php if( !empty( $this->systemMessages ) ) { ?>
<div id="system_messages">
<?php foreach( array_reverse( $this->systemMessages ) as $message ) { ?>
	<div class="system_message"><?php echo $message; ?></div>
<?php } ?>
	<div class="system_message_toolbar">
		<a class="system_message_toolbutton" id="system_message_close" href="#" onclick="return false;">[close]</a>
	</div>
<script>
Event.observe( window, "load", function() {
	Event.observe( $("system_message_close"), "click", function(evt) {
		Element.hide( $("system_messages") );
	}.bindAsEventListener( $("system_message_close") ) );
} );
</script>
</div>
<div />
<?php } ?>
