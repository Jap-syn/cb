<?php
use member\Application;

$oemId = Application::getInstance()->authManager->getUserInfo()->OemId;
if(($oemId == 0)) {
    $logo = 'Atobarai_logo_3.gif';
    $company = '株式会社キャッチボール';
    $postAddr = '〒140-0002';
    $addr = '東京都品川区東品川2-2-24';
    $subAddr = '天王洲セントラルタワー 12F';
} else {
    $oemInfo = Application::getInstance()->getOemInfo($oemId);
    $logo = 'oemcom.png';
    $company = $oemInfo['OemNameKj'];
    $postAddr = '〒'.$oemInfo['PostalCode'];
    $addr = $oemInfo['PrefectureName'].$oemInfo['City'].$oemInfo['Town'];
    $subAddr = $oemInfo['Building'];
}

$summary_templates = array(
	'UseAmount' => 0,
	'ReceiptAmountTotal' => 0,
	'PayBackAmount' => 0
);

$summaries = array_merge(array(), $summary_templates);

$p = $this->list;
?>
<?php if(empty($this->list)) { ?>
	明細はありません。
<?php } else { ?>
<?php
// ------------------------------------------------------------------------------------------------ loop by site
$is_first = true;
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

    <?php if ($is_first) { ?>
	<div class="print_header">
		<div id="summary_issue_date">発行日：　<?php echo f_df($this->summary['DecisionDate'], 'Y.m.d'); ?></div>
		<div id="billing_address" class="print_header_left">
			<div id="address_label">
				貴社名
				<span class="enterprise_name">
					<?php echo f_e($this->enterprise->EnterpriseNameKj); ?>　様
				</span>
			</div>
			<div id="address_data">
				<?php echo f_e($this->enterprise->PostalCode); ?><br />
				<?php echo f_e(sprintf('%s%s%s%s', $this->enterprise->PrefectureName, $this->enterprise->City, $this->enterprise->Town, $this->enterprise->Building)); ?>
			</div>
		</div>
		<div id="claim_origin" class="print_header_right">
			<div><img src="../images/<?php echo $logo; ?>" style="width: 83px; height: 16px;"/></div>
			<div style="font-size:12px"><?php echo $company; ?></div>
			<div>
				<?php echo $postAddr; ?>　<?php echo $addr; ?><br />
				<?php echo $subAddr; ?>
			</div>
			<img id="stamp_image" src="monthly/stampimage" />
		</div>
		<div id="issue_message">
			平素は後払い決済をご利用いただき、誠にありがとうございます。<br />
			立替締め日：<?php echo f_df($this->fixedDate, 'Y/m/d') . ' (' . f_df($this->summary['ExecScheduleDate'], 'y/m/d') . ' 支払分)'; ?>　のご利用額は以下となります。
		</div>
		<div class="float_clear"></div>
    </div>
	<div>
		お取引件数：<?php echo f_nf($item_count, '#,##0 件'); ?>
	</div>
    <?php   $is_first = false; ?>
    <?php } ?>

    <table class="claim_result charge_list_table" border="1" cellpadding="0" cellspacing="0">
		<thead>
			<tr>
				<td colspan="10">サイト：<?php echo f_e($sitename); ?></td>
			</tr>
			<tr>
				<th class="seq_col col_1">No.</th>
				<th class="id_col col_2">注文ID</th>
				<th class="id_col col_3">任意注文番号</th>
				<th class="col_4">購入者</th>
				<th class="date_col col_5">注文日</th>
				<th class="date_col col_5">伝登日</th>
				<th class="date_col col_5">立替締</th>
				<th class="money_col col_5">請求額</th>
				<th class="money_col col_5">入金額</th>
				<th class="money_col result_col col_10">立替戻し金額</th>
			</tr>
		</thead>
		<tbody>
<?php	foreach($rows as $row) { ?>
			<tr>
				<td class="seq_col col_1"><?php echo (++$i); ?></td>
				<td class="id_col col_2">
					<a href="search/detail?id=<?php echo f_e($row['OrderId']); ?>"><?php echo f_e($row['OrderId']); ?></a>
				</td>
				<td class="col_3"><?php echo f_e(nvl($row['Ent_OrderId'], '-')); ?></td>
				<td class="col_4"><?php echo f_e($row['NameKj']); ?></td>
				<td class="date_col col_5"><?php echo f_dfx($row['ReceiptOrderDate'], 'y/m/d'); ?></td>
				<td class="date_col col_5"><?php echo f_dfx($row['MaxDeliJournalIncDate'], 'y/m/d'); ?></td>
				<td class="date_col col_5"><?php echo is_null($row['FixedDate']) ? "&nbsp;" : f_dfx($row['FixedDate'], 'yy/MM/dd'); ?></td>
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
				<td class="money_col total_col summary_value_col"><?php echo nvl(f_nf(-1 * (int)$this->entpay_list_sub['StampFeeTotal'], '\ #,##0'), '\ 0'); ?></td>
			</tr>
			<tr class="summary">
				<td class="seq_col label_col summary_label_col">月額固定費（<?php echo f_e($this->entpay_list_sub['PricePlanName']); ?> プラン）</td>
				<td class="money_col total_col summary_value_col"><?php echo nvl(f_nf(-1 * (int)$this->entpay_list_sub['MonthlyFee'], '\ #,##0'), '\ 0'); ?></td>
			</tr>
			<tr class="summary">
				<td class="seq_col label_col summary_label_col">前回持越し分</td>
				<td class="money_col total_col summary_value_col"><?php echo nvl(f_nf($this->entpay_list_sub['CarryOver'], '\ #,##0'), '\ 0'); ?></td>
			</tr>
			<tr class="summary">
				<td class="seq_col label_col summary_label_col">キャンセル返金分</td>
				<td class="money_col total_col summary_value_col"><?php echo nvl(f_nf($this->entpay_list_sub['CalcelAmount'], '\ #,##0'), '\ 0'); ?></td>
			</tr>
			<tr class="summary">
				<td class="seq_col label_col summary_label_col">口座振込手数料</td>
				<td class="money_col total_col summary_value_col"><?php echo nvl(f_nf(-1 * (int)$this->entpay_list_sub['TransferCommission'], '\ #,##0'), '\ 0'); ?></td>
			</tr>
			<tr class="summary">
				<td class="seq_col label_col summary_label_col">立替精算戻し額</td>
				<td class="money_col total_col summary_value_col"><?php echo nvl(f_nf($this->entpay_list_sub['PayBackAmount'], '\ #,##0'), '\ 0'); ?></td>
			</tr>
			<tr class="summary fat_border">
				<td class="seq_col label_col summary_label_col">精算調整額</td>
				<td class="money_col total_col summary_value_col"><?php echo nvl(f_nf($this->entpay_list_sub['AdjustmentAmount'], '\ #,##0'), '\ 0'); ?></td>
			</tr>
			<tr class="summary">
				<td class="seq_col label_col summary_label_col">お支払総額</td>
				<td class="money_col total_col summary_value_col"><?php echo nvl(f_nf($this->entpay_list_sub['DecisionPayment'], '\ #,##0'), '\ 0'); ?></td>
			</tr>
		</tbody>
	</table>
	<?php echo $this->render( 'member/information_message.php' ); ?>
</div>

