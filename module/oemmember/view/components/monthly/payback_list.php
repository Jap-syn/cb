<?php
$fixedMonth = $this->fixedMonth;
$summary_templates = array(
	'UseAmount' => 0,
	'ReceiptAmountTotal' => 0,
	'PayBackAmount' => 0
);

$summaries = array_merge(array(), $summary_templates);

$p = $this->summary;
?>
<?php if(empty($this->list)) { ?>
	明細はありません。
<?php } else { ?>
<?php
// ------------------------------------------------------------------------------------------------ loop by site
$last_sitename = array_pop(array_keys($this->list));
$i = 0;
foreach($this->list as $sitename => $rows) {
	$_summaries = array_merge(array(), $summary_templates);
?>
<div class="layout_container">
	<?php
	if( $i == 0 ) {
		$this->current_list_count = $p['OrderCount'];
	}
	?>
	<table class="claim_result" border="1" cellpadding="0" cellspacing="0">
		<thead>
			<tr>
				<td colspan="10">サイト：<?php echo f_e($sitename); ?></td>
			</tr>
			<tr>
				<th class="seq_col col_1">No.</th>
				<th class="id_col col_2 text">注文ID </th>
				<th class="id_col col_3 number"> 任意注文番号 </th>
				<th class="col_4 text"> 購入者 </th>
				<th class="date_col col_5 text"> 注文日 </th>
				<th class="date_col col_5 text"> 伝登日 </th>
				<th class="date_col col_5 text"> 立替締 </th>
				<th class="money_col col_5 number"> 請求額 </th>
				<th class="money_col col_5 number">入金額</th>
				<th class="money_col result_col col_10 number"> 立替戻し金額</th>
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
				<td class="date_col col_5"><?php echo f_dfx($row['MaxDeliJournalIncDate'], 'y/m/d'); ?></td>
				<td class="date_col col_5"><?php echo f_dfx($row['FixedDate'], 'yy/MM/dd'); ?></td>
				<td class="money_col col_5"><?php echo nvl(f_nfx($row['UseAmount'], '\ #,##0'), '&nbsp;'); ?></td>
				<td class="money_col col_5"><?php echo nvl(f_nfx($row['ReceiptAmountTotal'], '\ #,##0'), '&nbsp;'); ?></td>
				<td class="money_col col_10"><?php echo nvl(f_nfx($row['PayBackAmount'], '\ #,##0'), '&nbsp;'); ?></td>
			</tr>
<?php
	foreach( array_keys( $summaries ) as $key ) {
		$summaries[$key] = ((int)$summaries[$key]) + ((int)$row[$key]);
		$_summaries[$key] = ((int)$_summaries[$key]) + ((int)$row[$key]);
	}
}
$style = $sitename == $last_sitename ? ' style="border-bottom-style: double; border-bottom-width: 3px;"' : '';
?>
			<tr class="summary">
				<td class="seq_col label_col" colspan="7"<?php echo $style; ?>>サイト計</td>
<?php
	foreach( array_keys( $_summaries ) as $key ) {
		$value = ((int)$_summaries[ $key ]);
		$classes = array('money_col');
		if( $key == 'PayBackAmount' ) {
			$classes[] = 'total_col';
			$classes[] = 'col_10';
		} else {
			$classes[] = 'col_6';
		}
?>
				<td class="<?php echo join(' ', $classes); ?>"<?php echo $style; ?>><?php echo f_nf($value, '\ #,##0'); ?></td>
<?php
	}
?>
			</tr>
<?php if( $sitename == $last_sitename ) { ?>
			<tr class="summary">
				<td class="seq_col summary_label_col" colspan="7">お取引合計</td>
<?php
	foreach( array_keys( $summaries ) as $key ) {
		$value = ((int)$summaries[ $key ]);
		$classes = array('money_col');
		if( $key == 'PayBackAmount' ) {
			$classes[] = 'total_col';
			$classes[] = 'col_10';
		} else {
			$classes[] = 'col_6';
		}
?>
				<td class="<?php echo join(' ', $classes); ?>"><?php echo f_nf($value, '\ #,##0'); ?></td>
<?php
	}
?>
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

<div class="layout_container">
	<table class="claim_result charge_list_table summary_table" border="1" cellpadding="0" cellspacing="0">
		<tbody>

			<tr class="summary">
				<td class="seq_col label_col summary_label_col">印紙代合計（過去取引分含む）　※</td>
				<td class="money_col total_col summary_value_col"><?php echo nvl(f_nf(-1 * (int)$p['StampFee'], '\ #,##0'), '\ 0'); ?></td>
			</tr>
			<tr class="summary">
				<td class="seq_col label_col summary_label_col">月額固定費（<?php echo f_e($this->planName); ?> プラン）</td>
				<td class="money_col total_col summary_value_col"><?php echo nvl(f_nf(-1 * (int)$p['MonthlyFee'], '\ #,##0'), '\ 0'); ?></td>
			</tr>
			<tr class="summary">
				<td class="seq_col label_col summary_label_col">前回持越し分</td>
				<td class="money_col total_col summary_value_col"><?php echo nvl(f_nf($p['CarryOverMonthlyFee'], '\ #,##0'), '\ 0'); ?></td>
			</tr>
			<tr class="summary">
				<td class="seq_col label_col summary_label_col">キャンセル返金分</td>
				<td class="money_col total_col summary_value_col"><?php echo nvl(f_nf($p['CancelRepaymentAmount'], '\ #,##0'), '\ 0'); ?></td>
			</tr>
			<tr class="summary">
				<td class="seq_col label_col summary_label_col">口座振込手数料</td>
				<td class="money_col total_col summary_value_col"><?php echo nvl(f_nf(-1 * (int)$p['FfTransferFee'], '\ #,##0'), '\ 0'); ?></td>
			</tr>
			<tr class="summary ">
				<td class="seq_col label_col summary_label_col">立替精算戻し額</td>
				<td class="money_col total_col summary_value_col"><?php echo nvl(f_nf($p['PayBackAmount'], '\ #,##0'), '\ 0'); ?></td>
			</tr>
			<tr class="summary fat_border">
				<td class="seq_col label_col summary_label_col">精算調整額</td>
				<td class="money_col total_col summary_value_col"><?php echo nvl(f_nf($p['AdjustmentAmount'], '\ #,##0'), '\ 0'); ?></td>
			</tr>

			<tr class="summary">
				<td class="seq_col label_col summary_label_col">立替総額</td>
				<td class="money_col total_col summary_value_col"><?php echo nvl(f_nf($p['PaymentAmount'], '\ #,##0'), '\ 0'); ?></td>
			</tr>
		</tbody>
	</table>
	<?php echo $this->render( 'oemmember/information_message.php' ); ?>
</div>

