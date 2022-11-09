<form id="order_search_form" method="post" action="<?php echo $this->postTarget; ?>">
<table id="order_search_form_table" class="search_items" border="1" cellpadding="0" cellspacing="0">
	<tbody>
<?php
$lastGroup = '';
foreach( $this->defaultConditions as $defaultCondition ) {
	$condition = null;
	foreach( $this->searchConditions as $cond ) {
		if( $cond['column'] == $defaultCondition['column'] ) {
			$condition = $cond;
			break;
		}
	}

	if( $condition == null ) continue;

	if ($condition['column'] == "ClaimDate") continue;

	// OEM毎のｻﾝﾌﾟﾙ注文ID設定(json内固定文字列の置換)
    if ($condition['column'] == 'OrderId' || $condition['column'] == 'Ent_OrderId') {
        $condition['help'] = str_replace('ak10000000', $this->sampleOemOrderId, $condition['help']);
    }

	if( $condition['group'] != $lastGroup ) {
?>
		<tr>
			<th colspan="3" class="group_header"><?php echo valueFormat( $this->groupMap[ $condition['group'] ]); ?>による検索</th>
		</tr>
<?php
		$lastGroup = $condition['group'];
	}


	$this->currentCondition = $condition;

		$captionClasses = array(
			'search_item',
			'caption',
			$condition['column']
		);

//		if( $condition['hidden'] ) $captionClasses[] = 'hidden';
//		if( $condition['required'] ) $captionClasses[] = 'required';

?>
		<tr>
			<td class="<?php echo join( ' ', $captionClasses ); ?>"><?php echo valueFormat( $condition['caption'] ); ?></td>
			<td class="search_item field"><?php echo $this->render( 'oemmember/search/' . "{$condition['control']}.php" ); ?></td>
			<td class="search_item help"><?php echo $condition['help']; ?></td>
		</tr>
<?php
}
?>
<?php
	if(!empty($this->defaultConditions)) { ?>
		<tr>
			<th colspan="3" class="group_header">表示順</th>
		</tr>
		<tr>
			<td colspan="3" class="display_order_item">
			<label for = d_order1 class="label"><input type="radio" name="display_order" class="radio" id="d_order1" value="0" <?php if(empty($this->searchValues["display_order"])) echo ' checked="checked"';?> />新しい注文日</label>
			<input type="text" name="write_close" style="visibility:hidden" value="" />
			<label for = d_order2 class="label"><input type="radio" name="display_order" class="radio" id="d_order2" value="1" <?php if(!empty($this->searchValues["display_order"]) && $this->searchValues["display_order"] == 1) echo ' checked="checked"';?> />古い注文日</label>
			<input type="text" name="write_close" style="visibility:hidden" value="" />
			<label for = n_order1 class="label"><input type="radio" name="display_order" class="radio" id="n_order1" value="2" <?php if(!empty($this->searchValues["display_order"]) && $this->searchValues["display_order"] == 2) echo ' checked="checked"';?> />新しい注文番号</label>
			<input type="text" name="write_close" style="visibility:hidden" value="" />
			<label for = n_order2 class="label"><input type="radio" name="display_order" class="radio" id="n_order2" value="3" <?php if(!empty($this->searchValues["display_order"]) && $this->searchValues["display_order"] == 3) echo ' checked="checked"';?> />古い注文番号</label></td>
		</tr>
		<tr>
			<td class="search_item submit" colspan="3">
				<button type="submit" id="submit_button">検索</button>
			</td>
		</tr>
<?php
	}
?>
	</tbody>
</table>
</form>
<script>
Event.observe( $("order_search_form"), "submit", function(evt) {
	Event.stop( evt );
	evt.returnValue = false;

	if( field_focus.currentElement ) field_focus.currentElement.blur();

	setTimeout( function() {
		$("order_search_form").submit();
	}, 0 );
}.bindAsEventListener( $("order_search_form") ) );
with( {
	blurHandler : function(evt) {
		var transTargets = {
			"ReceiptOrderDate" : "date",
			"ReceiptOrderDate_2" : "date",
			"OrderId" : "id",
			"OrderId_2" : "id",
			"Deli_JournalIncDate" : "date",
			"Deli_JournalIncDate_2" : "date",
			"Chg_ExecDate" : "date",
			"Chg_ExecDate_2" : "date",
			"ApprovalDate" : "date",
			"ApprovalDate_2" : "date"
		};
		var transFunctions  = {
			date : function(v) {
				v = ( v || "" )
					.toNarrowChar()
					.replace( /[-.]/g, "/")
					.replace( /[^\d\/]/g, "" )
					.split("/");

				if( v.length < 3 && v.length > 1 ) {
					v.unshift( new Date().getFullYear() );
				}

				return v.map( function(part, i) {
						if( ! /^\d+$/.test( part ) ) return part;
						var v = Number(part);
						if( i == 0 && v < 100 ) v += ( v < 90 ? 2000 : 1900 );
						return i == 0 ? ( "0000" + v ).slice(-4) : ( "00" + v ).slice(-2);
					} ).join("/");
			},
			id : function(v) {
				return ( v || "" ).toNarrowChar().toUpperCase()
			}
		};

		this.value = ( this.value || "" ).trim();
		var conf = transTargets[ this.id ];
		if( conf ) this.value = transFunctions[ conf ]( this.value );
	}
} ) {
	Form.getElements($("order_search_form" )).each( function(field) {
		Event.observe( field, "blur", blurHandler.bindAsEventListener( field ) );
	} );
}

function changeDeliConditions() {
	var deliConditions = document.getElementsByName("Deli_Conditions");

	for( i= 0; i < deliConditions .length; i++ ) {
		if( deliConditions[i].checked ) {
			if( deliConditions[i].value == "1") {
				document.getElementById("Deli_JournalNumber").disabled = false;
			}
			else {
				document.getElementById("Deli_JournalNumber").disabled = true;
			}
		}
	}
}
</script>
