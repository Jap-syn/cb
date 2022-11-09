		<h3>【別配送先情報】<span style="font-size: 9pt;">⇒与信・当社から注文者様への請求書・検索に使用されます。</span></h3>
		<div><input type="checkbox" id="use_delivery_info">別配送先を指定する</div>
		<table class="order_items" id="delivery_items" border="yes" cellpadding="0" cellspacing="0">
			<thead>
				<tr>
					<th class="item_name">項目名</th>
					<th class="item_value">入力</th>
					<th class="item_help">ヘルプ</th>
				</tr>
			</thead>
			<tbody>
				<!-- 郵便番号 -->
				<tr>
					<td class="item_name">郵便番号※</td>
					<td class="item_value">
						<div>
							<input type="text" name="D_PostalCode" class="must_if_enabled" id="d_postalcode" size="10" disabled="disabled"/>
							<button type="button" id="address_from_postalcode_d" disabled="disabled">住所を自動入力</button>
						</div>
						<div style="font-weight: bold">
							※郵便番号を入れ「住所を自動入力」を押すと町名までの住所が自動的に挿入されます。
						</div>
					</td>
					<td class="item_help">
						半角ででご入力ください。<br/>
						例）1600023、160-0023
					</td>
				</tr>
				
				<!-- 住所 -->
				<tr>
					<td class="item_name">住所※</td>
					<td class="item_value">
						<div>
							都道府県<input type="text" name="D_PrefectureName" class="must_if_enabled" id="d_prefecture_name" size="8" disabled="disabled" />
						</div>
						<div>
							市区郡<input type="text" name="D_City" class="must_if_enabled" id="d_city" size="20" disabled="disabled" />
						</div>
						<div>
							町名<input type="text" name="D_Town" class="must_if_enabled" id="d_town" size="30" disabled="disabled" />
						</div>
						<div>
							ビル名等<input type="text" name="D_Building" id="d_building" size="30" disabled="disabled" />
						</div>
					</td>
					<td class="item_help">
						全角／半角のどちらでも可です。<br/>
						例）<br/>
						都道府県 ⇒ 東京都<br/>
						市区郡 ⇒ 港区<br/>
						町名 ⇒ 芝1-2-4<br/>
						ビル名等 ⇒ ○○ビル
					</td>
				</tr>
				
				<!-- 氏名 -->
				<tr>
					<td class="item_name">氏名※</td>
					<td class="item_value">
						<input type="text" name="D_DestNameKj" class="must_if_enabled" id="d_name_kn" size="30" disabled="disabled" />
					</td>
					<td class="item_help">
						全角／半角のどちらでも可です。姓と名の間をスペースで区切ってください。<br/>
						例）吉村　一郎
					</td>
				</tr>
				
				<!-- 氏名かな -->
				<tr>
					<td class="item_name">氏名かな</td>
					<td class="item_value">
						<input type="text" name="D_DestNameKn" id="d_name_kn" size="30" disabled="disabled" />
					</td>
					<td class="item_help">
						全角／半角のどちらでも可です。姓と名の間をスペースで区切ってください。<br/>
						例）よしむら　いちろう
					</td>
				</tr>
				
				<!-- 電話番号 -->
				<tr>
					<td class="item_name">お電話番号<br/>（携帯電話番号可）※</td>
					<td class="item_value">
						<input type="text" name="D_Phone" class="must_if_enabled" id="d_phone" size="20" disabled="disabled" />
					</td>
					<td class="item_help">
						市外局番、局番などをハイフン（「-」）で区切り、半角数字で入力してください。<br/>
						例）03-3333-3333
					</td>
				</tr>
			</tbody>
		</table>
<script>
alert("0");
$("use_delivery_info").onclick = function() {
	var container = $("delivery_items");
	var items = [
		$A( container.getElementsByTagName("input") ),
		$A( container.getElementsByTagName("button") ),
		$A( container.getElementsByTagName("select") ),
		$A( container.getElementsByTagName("textarea") )
	].flatten();
	
	var disabled = ! this.checked;
	items.each( function(item) {
		if( disabled ) {
			item.disabled = "disabled";
			new Element.ClassNames( item ).add( "disabled" );
		} else {
			item.removeAttribute("disabled");
			new Element.ClassNames( item ).remove( "disabled" );
		}
	} );
}.bindAsEventListener( $("use_delivery_info") );
</script>
