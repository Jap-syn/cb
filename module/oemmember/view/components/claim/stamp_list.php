<?php
use oemmember\Application;

$oemId = Application::getInstance()->authManager->getUserInfo()->OemId;
$oemInfo = Application::getInstance()->getCurrentOemData();
if($oemId == 0 || $oemInfo['ChangeIssuerNameFlg']) {
    $logo = '../../images/Atobarai_logo_3.gif';
    $company = '株式会社キャッチボール';
    $postAddr = '〒140-0002';
    $addr = '東京都品川区東品川2-2-24';
    $subAddr = '天王洲セントラルタワー 12F';
} else {
    $logo = 'resource/image/type/logo-s';
    $company = $oemInfo['OemNameKj'];
    $postAddr = '〒'.$oemInfo['PostalCode'];
    $addr = $oemInfo['PrefectureName'].$oemInfo['City'].$oemInfo['Town'];
    $subAddr = $oemInfo['Building'];
}

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
$is_first = true;
$item_count = 0;
foreach($this->list as $key => $value) {
    $valueCount = 0;
    if (!empty($value)) {
       $valueCount = count($value);
}
	$item_count += $valueCount;
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
			<div><img src="<?php echo $logo; ?>" style="width: 83px; height: 16px;"/></div>
			<div style="font-size:12px"><?php echo $company; ?></div>
			<div>
				<?php echo $postAddr; ?>　<?php echo $addr; ?><br />
				<?php echo $subAddr; ?>
			</div>
			<img id="stamp_image" src="resource/image/type/imprint" />
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

	<table class="claim_result stamp_list_table" border="1" cellpadding="0" cellspacing="0">
		<thead>
			<tr>
				<td colspan="8">サイト：<?php echo f_e($sitename); ?></td>
			</tr>
			<tr>
				<th class="seq_col col_1">No.</th>
				<th class="id_col col_2">注文ID</th>
				<th class="id_col col_3">任意注文番号</th>
				<th class="col_4" style="width: 160px">購入者</th>
				<th class="date_col col_5">注文日</th>
				<th class="date_col col_6" style="width: 70px">立替締め日</th>
				<th class="money_col col_7" style="width: 70px">顧客請求額</th>
				<th class="money_col result_col col_10">印紙代金</th>
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
				<td class="date_col col_5"><?php echo f_dfx($row['ReceiptOrderDate'], 'yy/MM/dd'); ?></td>
				<td class="date_col col_6"><?php echo is_null($row['FixedDate']) ? "&nbsp;" : f_dfx($row['FixedDate'], 'yy/MM/dd'); ?></td>
				<td class="money_col col_7"><?php echo nvl(f_nfx($row['UseAmount'], '\ #,##0'), '&nbsp;'); ?></td>
				<td class="money_col col_10"><?php echo nvl(f_nfx($row['StampFee'], '\ #,##0'), '&nbsp;'); ?></td>
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
				<td class="money_col total_col col_10"<?php echo $style; ?>><?php echo f_nf((int)$_summaries['StampFee'], '\ #,##0'); ?></td>
			</tr>

<?php if( $sitename == $last_sitename ) { ?>
			<tr class="summary">
				<td class="seq_col summary_label_col" colspan="7">合計</td>
				<td class="money_col total_col col_10"><?php echo f_nf((int)$summaries['StampFee'], '\ #,##0'); ?></td>
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
