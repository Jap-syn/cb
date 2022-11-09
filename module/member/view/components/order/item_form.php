		<h3>【商品情報（請求書に記載される項目）】<span style="font-size: 9pt;">⇒当社から注文者様への請求書・検索に使用されます。</span></h3>
		<div>
			<b>
				※ポイント等の割引サービスは、「商品名／購入品目」の最終行にマイナス（「-」）を付けてご入力ください。<br/>
				例）ポイント（商品名） -1000（単価） 1（数量）
			</b>
		</div>
		<table class="order_items" id="item_items" border="yes" cellpadding="0" cellspacing="0">
			<thead>
				<tr>
					<th class="item_value">
						項目の削除
					</th>
					<th class="item_value">
						商品名／購入品目<br/>
						※最低一品目の登録が必要です<br/>
					</th>
					<th class="item_value">
						単価<br/>
						※半角数字でご入力ください
					</th>
					<th class="item_value">
						数量<br/>
						※半角数字でご入力ください
					</th>
				</tr>
			</thead>
			<tbody>
				<!-- 商品情報 -->
				<tr class="item_row">
					<td class="item_name">
						<button class="item_delete_button" type="button" style="display:none">商品を削除</button>
					</td>
					<td class="item_value">
						<input type="text" class="i_itemnamekj must" name="I_ItemNameKj_1" size="20" />
						<div class="item_exsample">
							例）「布団セット」、「茶碗」、「ギフト梱包費用」、「お直し代」等
						</div>
					</td>
					<td class="item_value">
						<input type="text" class="i_unitprice must" name="I_UnitPrice_1" size="8" />円（税込）
					</td>
					<td class="item_value">
						<input type="text" class="i_itemnum must" name="I_ItemNum_1" size="5" />個
					</td>
				</tr>
				<!-- 追加ボタン -->
				<tr id="none_item_row">
					<td colspan="4">
						<button id="item_add_button" type="button">商品を追加</button>
					</td>
				</tr>
				<!-- 送料 -->
				<tr class="extra_item_row">
					<td class="item_name" colspan="2">
						商品送料<br/>
						（送料がある場合のみ入力）
						<input type="hidden" class="i_itemnamekj" name="I_ItemNameKj_<?php echo f_e( $this->order_item_carriage__ ); ?>" value="<?php echo f_e( $this->order_item_carriage__ ); ?>" />
					</td>
					<td class="item_value">
						<input type="text" class="i_unitprice" name="I_UnitPrice_<?php echo f_e( $this->order_item_carriage__ ); ?>" size="8" />円（税込）<br/>
						※半角数字でご入力ください
					</td>
					<td class="item_help">
						送料無料の場合などは、未入力でお願いします。
						<input type="hidden" class="i_itemnum" name="I_ItemNum_<?php echo f_e( $this->order_item_carriage__ ); ?>" value="1">
					</td>
				</tr>
				<!-- 手数料 -->
				<tr class="extra_item_row">
					<td class="item_name" colspan="2">
						店舗手数料<br/>
						（決済手数料がある場合のみ入力）
						<input type="hidden" class="i_itemnamekj" name="I_ItemNameKj_<?php echo f_e( $this->order_item_charge__ ); ?>" value="<?php echo f_e( $this->order_item_charge__ ) . '__'; ?>" />
					</td>
					<td class="item_value">
						<input type="text" class="i_unitprice" name="I_UnitPrice_<?php echo f_e( $this->order_item_charge__ ); ?>" size="8" />円（税込）<br/>
						※半角数字でご入力ください
					</td>
					<td class="item_help">
						請求書発行手数料を顧客に請求されている場合は、こちらにご入力ください。
						<input type="hidden" class="i_itemnum" name="I_ItemNum_<?php echo f_e( $this->order_item_charge__ ); ?>" value="1">
					</td>
				</tr>
			</tbody>
		</table>
		<div>
			※通常、上記の金額欄は全て「内税価格」となっております。「外税価格」設定をご希望される場合はサポートセンターまでご連絡ください。
		</div>
		<div>
			※顧客に事前にお知らせしてある請求額と異ならないようにしてください（金額の変更を事前に告知している場合はこの限りではありません）。
		</div>
		<div>
			※商品名欄には、「○○手数料」のように、商品以外の項目名を入力することができます。
		</div>
		<!-- 請求金額合計-->
		<center style="margin-top: 1em">
			<table class="total_receipt" id="item_items" border="yes" cellpadding="0" cellspacing="0">
				<thead>
					<th>
						請求金額合計
					</th>
				</thead>
				<tbody>
					<!-- 請求金額合計 -->
					<tr>
						<td class="item_value">
							<input type="text" id="total_receipt" size="10" readonly="readonly" />円<br/>
							<button id="calc_total_receipt" type="button">再計算</button>
						</td>
					</tr>
					<tr>
						<td class="item_help">
							注文時の請求総額と差異がないかご確認ください。<br/>
						</td>
					</tr>
				</tbody>
			</table>
		</center>
<script>
// 商品行および送料・手数料行の初期化
[ document.getElementsByClassName("item_row")[0], document.getElementsByClassName("extra_item_row") ].flatten().each( function(row) {
	initItemRow( row, false );
} );

// 請求金額計算
$("calc_total_receipt").onclick = function() {
	var fixField = function(field) {
		$A("０１２３４５６７８９").each( function(c, i) {
			field.value = field.value.replace( new RegExp( c, "g" ), i );
		} );
		field.value = parseInt( field.value.replace(/[,、]/g, "") );
		if( isNaN( field.value ) ) field.value = 0;
	};
	
	var tbl = $("item_items");
	var body = tbl.getElementsByTagName("tbody")[0];
	
	var rows = [ document.getElementsByClassName("item_row"), document.getElementsByClassName("extra_item_row") ].flatten();
	var total = rows.inject( 0, function(ttl, row) {
		var priceField = document.getElementsByClassName("i_unitprice", row)[0];
		var numField = document.getElementsByClassName("i_itemnum", row)[0];
		
 		fixField( priceField );
		fixField( numField );
		
		return ttl + ( Number( priceField.value ) * Number( numField.value ) );
	} );
	$("total_receipt").value = total;
}.bindAsEventListener( $("calc_total_receipt") );

// 商品追加
$("item_add_button").onclick = function() {
	var tbl = $("item_items");
	var body = tbl.getElementsByTagName("tbody")[0];
	
	var row = ( document.getElementsByClassName("item_row")[0] ).cloneNode(true);
	initItemRow( row, true );
	body.insertBefore( row, $("none_item_row") );
}.bindAsEventListener( $("item_add_button") );

// 商品項目行初期化処理
function initItemRow(row, isNew) {
	var delButton = document.getElementsByClassName("item_delete_button", row)[0];
	var nameField = document.getElementsByClassName("i_itemnamekj", row)[0];
	var priceField = document.getElementsByClassName("i_unitprice", row)[0];
	var numField = document.getElementsByClassName("i_itemnum", row)[0];
	var expDiv = document.getElementsByClassName("item_exsample", row)[0];
	
	if( isNew ) {
		Element.show( delButton );
		
		[ nameField, priceField, numField ].each( function(field) {
			field.value = "";
			var classNames = new Element.ClassNames( field );
			classNames.remove( "must" );
		} );
		
		expDiv.parentNode.removeChild( expDiv );
		
		delButton.onclick = function() {
			row.parentNode.removeChild( row );
			$("calc_total_receipt").click();
		}.bindAsEventListener( delButton );
	}
	
	priceField.onchange = numField.onchange = function() {
		$("calc_total_receipt").click();
	}
}

</script>
