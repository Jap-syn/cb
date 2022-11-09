<?php if( isset( $this->information_message ) && strlen( trim($this->information_message) ) ) { ?>
<div class="information_message">
	<div class="message_title">立替用通信欄</div>
	<div class="message_core"><?php echo trim($this->information_message); ?></div>
</div>
<?php } ?>
