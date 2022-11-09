<?php
$fixedMonth = $this->fixedMonth;
$summary_templates = array(
	'StampFee' => 0
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
	<table class="claim_result adjust_list_table" border="1" cellpadding="0" cellspacing="0">
		<thead>
			<tr>
				<th class="seq_col col_1">No.</th>
				<th class="date_col col_6" style="width: 70px">立替締め日</th>
				<th class="id_col col_2">注文ID</th>
				<th class="id_col col_3">任意注文番号</th>
				<th class="col_4" style="width: 160px">購入者</th>
				<th class="date_col col_5">注文日</th>
				<th class="money_col col_7" style="width: 500px">科目</th>
				<th class="money_col result_col col_10">精算調整額</th>
			</tr>
		</thead>
		<tbody>
<?php	foreach($rows as $row) { ?>
			<tr>
				<td class="seq_col col_1"><?php echo (++$i); ?></td>
				<td class="date_col col_6"><?php echo f_dfx($row['FixedDate'], 'yy/MM/dd'); ?></td>
				<td class="id_col col_2">
					<a href="search/detail?id=<?php echo f_e($row['OrderId']); ?>" title="取引詳細"><?php echo f_e($row['OrderId']); ?></a>
				</td>
				<td class="col_3"><?php echo f_e(nvl($row['Ent_OrderId'], '-')); ?></td>
				<td class="col_4"><?php echo f_e($row['NameKj']); ?></td>
				<td class="date_col col_5"><?php if (!is_null($row['ReceiptOrderDate'])) { echo f_dfx($row['ReceiptOrderDate'], 'yy/MM/dd'); } else { echo ''; } ?></td>
				<td class="col_7"><?php echo $row['kamoku']; ?></td>
				<td class="money_col col_8"><?php echo f_nf($row['AdjustmentAmount'], '\ #,##0'); ?></td>
			</tr>
			<?php
              $totalAdjustmentAmount += (int)$row['AdjustmentAmount'];
            }
            ?>
			<tr class="summary">
				<td class="seq_col label_col" colspan="7">合計</td>
				<td class="money_col total_col col_8"><?php echo f_nf((int)$totalAdjustmentAmount, '\ #,##0'); ?></td>
			</tr>


		</tbody>
	</table>
</div>
<?php
}
// ------------------------------------------------------------------------------------------------ loop by site
?>
<?php } ?>
