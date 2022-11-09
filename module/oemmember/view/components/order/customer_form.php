		<h3>
			【ご注文者（請求先）情報】
			<span style="font-size: 9pt;">⇒与信・当社から注文者様への請求書・検索に使用されます。</span>
		</h3>
		<table class="order_items" id="customer_items" border="yes" cellpadding="0" cellspacing="0">
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
							<input type="text" name="C_PostalCode" class="must" id="c_postalcode" size="10" />
							<button type="button" id="address_from_postalcode_c">住所を自動入力</button>
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
							都道府県<input type="text" name="C_PrefectureName" class="must" id="c_prefecture_name" size="8" />
						</div>
						<div>
							市区郡<input type="text" name="C_City" class="must" id="c_city" size="20" />
						</div>
						<div>
							町名<input type="text" name="C_Town" class="must" id="c_town" size="30" />
						</div>
						<div>
							ビル名等<input type="text" name="C_Building" id="c_building" size="30" />
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
						<input type="text" name="C_NameKj" class="must" id="c_name_kn" size="30" />
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
						<input type="text" name="C_NameKn" id="c_name_kn" size="30" />
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
						<input type="text" name="C_Phone" class="must" id="c_phone" size="20" />
					</td>
					<td class="item_help">
						市外局番、局番などをハイフン（「-」）で区切り、半角数字で入力してください。<br/>
						例）03-3333-3333
					</td>
				</tr>

				<!-- メールアドレス -->
				<tr>
					<td class="item_name">メールアドレス※</td>
					<td class="item_value">
						<input type="text" name="C_MailAddress"<?php if($this->is_must_mailaddress) { ?> class="must"<?php } ?>
						 id="c_mailaddress" size="40" />
					</td>
					<td class="item_help">
						半角英数で入力してください。<br/>
						例）yoshimura@example.com
					</td>
				</tr>
			</tbody>
		</table>
