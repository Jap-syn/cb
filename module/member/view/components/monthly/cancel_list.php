<?php
$fixedMonth = $this->fixedMonth;
$summary_templates = array(
	'RepayTotal' => 0
);

$summaries = array_merge(array(), $summary_templates);

?>
<?php if(empty($this->list)) { ?>
	明細はありません。
<?php } else { ?>
<?php
// ------------------------------------------------------------------------------------------------ loop by site
$item_count = 0;
foreach($this->list as $key => $value) {
	$item_count += count($value);
};

$last_sitename = array_pop(array_keys($this->list));
$i = 0;
foreach($this->list as $sitename => $rows) {
	$_summaries = array_merge(array(), $summary_templates);
?>
<div class="layout_container">
	<?php
	if( $i == 0 ) {
		$this->current_list_count = $item_count;
		echo $this->render('member/monthly/list_summary.php');
	}
	?>
	<table class="claim_result cancel_list_table" border="1" cellpadding="0" cellspacing="0">
		<thead>
			<tr>
				<td colspan="9">サイト：<?php echo f_e($sitename); ?></td>
			</tr>
			<tr>
				<th class="seq_col col_1">No.</th>
				<th class="id_col col_2">注文ID</th>
				<th class="id_col col_3">任意注文番号</th>
				<th class="col_4" style="width:165px">購入者</th>
				<th class="date_ocl col_5">注文日</th>
				<th class="date_col col_6" style="width:70px">立替締め日</th>
				<th class="date_col col_7" style="width:75px">キャンセル日</th>
			<!-- 	<th class="id_col col_9">キャンセル区分</th> -->
				<th class="money_col col_8" style="width:70px">顧客請求額</th>
				<th class="money_col result_col col_10">キャンセル返金額</th>
			</tr>
		</thead>
		<tbody>
<?php	foreach($rows as $row) { ?>
			<tr>
				<td class="seq_col col_1"><?php echo (++$i); ?></td>
				<td class="id_col col_2">
					<a href="search/detail?id=<?php echo f_e($row['OrderId']); ?>" title="取引詳細"><?php echo f_e($row['OrderId']); ?></a>
				</td>
				<td class="col_3"><?php echo f_e(nvl($row['Ent_OrderId'], '-')); ?></td>
				<td class="col_4"><?php echo f_e($row['NameKj']); ?></td>
				<td class="date_col col_5"><?php echo f_dfx($row['ReceiptOrderDate'], 'y/m/d'); ?></td>
				<td class="date_col col_5"><?php echo f_dfx($row['FixedDate'], 'y/m/d'); ?></td>
				<td class="date_col col_6"><?php echo f_dfx($row['ApprovalDate'], 'y/m/d'); ?></td>
				<!--
				<?php /*if(!empty($row['Cnl_ReturnSaikenCancelFlg'])) {
					$row['Cnl_ReturnSaikenCancelFlg'] = "返却";
				} else {
					$row['Cnl_ReturnSaikenCancelFlg'] = "通常";
				}*/?>
				<td class="id_col col_9"><?php echo f_e($row['Cnl_ReturnSaikenCancelFlg']); ?></td>-->
				<td class="money_col col_6"><?php echo nvl(f_nfx($row['UseAmount'], '\ #,##0'), '&nbsp;'); ?></td>
				<td class="money_col col_10"><?php echo nvl(f_nfx(-1 * (int)$row['RepayTotal'], '\ #,##0'), '&nbsp;'); ?></td>
			</tr>
<?php
	foreach( array_keys( $summaries ) as $key ) {
		$summaries[$key] = ((int)$summaries[$key]) - ((int)$row[$key]);
		$_summaries[$key] = ((int)$_summaries[$key]) - ((int)$row[$key]);
	}
}
$style = $sitename == $last_sitename ? ' style="border-bottom-style: double; border-bottom-width: 3px;"' : '';
?>
			<tr class="summary">
				<td class="seq_col label_col" colspan="8"<?php echo $style; ?>>サイト計</td>
				<td class="money_col total_col col_10"<?php echo $style; ?>><?php echo f_nf((int)$_summaries['RepayTotal'], '\ #,##0'); ?></td>
			</tr>
<?php if( $sitename == $last_sitename ) { ?>
			<tr class="summary">
				<td class="seq_col summary_label_col" colspan="8">合計</td>
				<td class="money_col total_col col_10"><?php echo f_nf((int)$summaries['RepayTotal'], '\ #,##0'); ?></td>
			</tr>
<?php } ?>

		</tbody>
	</table>
</div>
<?php
}
// ------------------------------------------------------------------------------------------------ loop by site
?>
<?php } ?>

