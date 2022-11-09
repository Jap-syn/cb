		<h3>
			【基本情報】
		</h3>
		<table class="order_items" id="basic_items" border="yes" cellpadding="0" cellspacing="0">
			<thead>
				<tr>
					<th class="item_name">項目名</th>
					<th class="item_value">入力</th>
					<th class="item_help">ヘルプ</th>
				</tr>
			</thead>
			<tbody>
				<!-- 注文日 -->
				<tr>
					<td class="item_name">注文日※</td>
					<td class="item_value">
						<input type="text" name="O_ReceiptOrderDate" class="must"
						 id="o_receipt_order_date" size="15" value="<?php echo f_e(date('Y/m/d')); ?>" />
						<button id="selectDate" type="button">カレンダーで選択</button>
						<button id="setToday" type="button">今日</button>
						<div id="datePicker1" style="margin-bottom: 20px; position: absolute; left: 90px; top: 80px;"></div>
					</td>
					<td class="item_help">
						キーボードから入力する場合は「2007/08/06」の書式で、半角の英数字でご入力ください。<br/>
						例）2007/08/06
					</td>
				</tr>

				<!-- サイト名 -->
				<tr>
					<td class="item_name">受付サイト※</td>
					<td class="item_value">
						<select name="O_SiteId" id="o_siteid" class="must">
<?php foreach($this->site_list as $site) { ?>
							<option value="<?php echo f_e($site['SiteId']); ?>" text="<?php echo f_e($site['SiteNameKj']); ?>"><?php echo f_e( $site['SiteNameKj'] ); ?></option>
<?php } ?>
						</select>
<?php
if( empty($this->site_list) ) {
	$this->assign( 'noSiteError', true );
?>
						<div class="warning">
							サイトが登録されていないため、注文登録ができません。お手数ですがサポートセンターへお問い合わせ下さい。
						</div>
<?php
}
?>
					</td>
					<td class="item_help">
						この注文を受け付けたサイトを選択してください。
					</td>
				</tr>

				<!-- 任意注文番号 -->
				<tr>
					<td class="item_name">任意注文番号</td>
					<td class="item_value">
						<input type="text" name="O_Ent_OrderId" id="o_ent_orderid" size="60" />
					</td>
					<td class="item_help">
						この注文を識別する任意の文字や番号を任意で入力してください。
					</td>
				</tr>

				<!-- 備考 -->
				<tr>
					<td class="item_name">備考（メモ）</td>
					<td class="item_value">
						<textarea name="O_Ent_Note" id="o_ent_note" rows="3" cols="30"></textarea>
					</td>
					<td class="item_help">
						この注文情報の備考をメモできます。
					</td>
				</tr>
			</tbody>
		</table>
<script>
$("setToday").onclick = function() {
	$("o_receipt_order_date").value = new Date().format( "yyyy/MM/dd" );
};

new DatePicker(
	"datePicker1",
	"o_receipt_order_date",
	"selectDate",
	{
		format : function(d) {
			return d.format( "yyyy/MM/dd" );
		}
	}
);
(function() {
	var ref = $("o_receipt_order_date");
	var pos = Position.cumulativeOffset( ref );
	Object.extend( $("datePicker1").style, {
		left : "{0}px".format( pos[0] ),
		top : "{0}px".format( pos[1] + ( ref.clientHeight || ref.offsetLeft || 15 ) )
	} );
})();
</script>
