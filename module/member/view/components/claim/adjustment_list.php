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

$rows = $this->list;            // リスト
$totalAdjustmentAmount = 0;     // 総調整額
?>
<?php if (empty($rows)) { ?>
	明細はありません。
<?php } else { ?>
<div class="layout_container">
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
		お取引件数：<?php echo f_nf(count($rows), '#,##0 件'); ?>
	</div><br />
    <table class="claim_result stamp_list_table" border="1" cellpadding="0" cellspacing="0">
		<thead>
			<tr>
				<th class="seq_col col_1">No.</th>
				<th class="date_col col_2" style="width: 160px">立替締め日</th>
				<th class="id_col col_3">注文ID</th>
				<th class="id_col col_4">任意注文番号</th>
				<th class="col_5" style="width: 160px">購入者</th>
				<th class="date_col col_6" style="width: 160px">注文日</th>
				<th class="col_7" style="width: 500px">科目</th>
				<th class="money_col col_8">精算調整額</th>
			</tr>
		</thead>
		<tbody>
            <?php
            $i = 0;
            for ($j=0; $j<count($rows); $j++) {
                $row = $rows[$j];
            ?>
            <tr>
			    <td class="seq_col col_1"><?php echo (++$i); ?></td>
			    <td class="date_col col_2"><?php echo is_null($row['FixedDate']) ? "&nbsp;" :  f_dfx($row['FixedDate'], 'yy/MM/dd'); ?></td>
			    <td class="id_col col_3">
					<a href="search/detail?id=<?php echo $row['OrderId']; ?>"><?php echo $row['OrderId']; ?></a>
				</td>
				<td class="col_4"><?php echo $row['Ent_OrderId']; ?></td>
				<td class="col_5"><?php echo $row['NameKj']; ?></td>
				<td class="date_col col_6"><?php  if (!is_null($row['ReceiptOrderDate'])) { echo f_dfx($row['ReceiptOrderDate'], 'yy/MM/dd'); } else { echo ''; } ?></td>
				<td class="col_7"><?php echo $row['kamoku']; ?></td>
				<td class="money_col col_8"><?php echo f_nf($row['AdjustmentAmount'], '\ #,##0'); ?></td>
			</tr>
			<?php
              $totalAdjustmentAmount += (int)$row['AdjustmentAmount'];
            }
            ?>
			<tr class="summary">
				<td class="seq_col summary_label_col" colspan="7">合計</td>
				<td class="money_col total_col col_8"><?php echo f_nf((int)$totalAdjustmentAmount, '\ #,##0'); ?></td>
			</tr>
		</tbody>
	</table>
</div>
<?php } ?>