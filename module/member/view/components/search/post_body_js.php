<script>
Controls.dateControls.each( function(id) {
	var pos = Position.cumulativeOffset( $(id) );
	var dp = "datePicker_{0}".format(id);
	new base.UI.DatePicker(
		dp,
		$( id ),
		$( "{0}_choose".format( id ) )
	);
} );

$A( document.getElementsByTagName("select") ).each( function(sel) {
	if( ! /_Mode$/.test( sel.id || "" ) ) throw $continue;
	var id = sel.id.replace( /_Mode$/, "" );
	var fid = "{0}_2".format( id );
	$(fid).pre_value = $(fid).value;
	Controls.changeControlDisabled( $(fid), sel.selectedIndex == 0 );

	if(/Ent_OrderId/.test(id)) {
		var
			chk_name = 'Ent_OrderId_SearchAsNumeric',
			chk = Object.extend(document.createElement('input'), {
				type : 'checkbox',
				name : chk_name,
				id : chk_name,
				value : '1'
			}),
			lbl = Object.extend(document.createElement('label'), {
				'for' : chk_name,
				'title' : 'このオプションをONにすると、指定した任意注文番号を数値として見なせる場合は数値として検索します。ON・OFFの違いによって検索結果が異なる場合があります'
			});
		chk.style.width = 'auto';
		chk.checked = <?php echo isset($this->entOrderIdAsNumeric) && $this->entOrderIdAsNumeric ? 'true' : 'false'; ?>;
		lbl.appendChild(chk);
		lbl.appendChild(document.createTextNode('数値として検索'));
		$(sel).parentNode.appendChild(lbl);
	}
} );
</script>
