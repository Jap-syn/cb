<script>
var Controls = {
	dateControls : [],
	changeControlDisabled : function(target, disabled) {
		target.disabled = disabled;
		if( disabled ) {
			target.pre_value = target.value;
			target.value = "";
			new Element.ClassNames( target ).add( "disabled" );
		} else {
			target.value = target.pre_value || "";
			target.pre_value = "";
			new Element.ClassNames( target ).remove( "disabled" );
		}
//		target.readonly = disabled;
	}
}
</script>
