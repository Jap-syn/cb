<?php
use oemmember\Application;

$oemId = Application::getInstance()->authManager->getUserInfo()->OemId;
$oemInfo = Application::getInstance()->getCurrentOemData();
if(is_null($oemId) || $oemId == 0 || $oemInfo['ChangeIssuerNameFlg']) {
    $logo = '../../images/Atobarai_logo_3.gif';
    $company = '株式会社キャッチボール';
    $postAddr = '〒140-0002';
    $addr = '東京都品川区東品川2-2-24';
    $subAddr = '天王洲セントラルタワー 12F';
    $stamp = 'monthly/stampimage';
} else {
    $logo = 'resource/image/type/logo-s';
    $company = $oemInfo['OemNameKj'];
    $postAddr = '〒'.$oemInfo['PostalCode'];
    $addr = $oemInfo['PrefectureName'].$oemInfo['City'].$oemInfo['Town'];
    $subAddr = $oemInfo['Building'];
    $stamp = $oemInfo['Imprint'];
}
?>

<div class="layout_container">
	<div class="print_header">
		<div id="summary_issue_date">発行日：　<?php echo f_df($this->issueDate, 'Y.m.d'); ?></div>
		<div id="billing_address" class="print_header_left">
			<div id="address_label">
				ご請求先
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
			<div><img src="<?php echo $logo; ?>" style="height: 16px" /></div>
			<div style="font-size:12px"><?php echo $company; ?></div>
			<div>
				<?php echo $postAddr; ?>　<?php echo $addr; ?><br />
				<?php echo $subAddr; ?>
			</div>
			<img id="stamp_image" src="resource/image/type/imprint" />
		</div>
		<div id="issue_message" style="clear: both;">
			平素は後払い決済をご利用いただき、誠にありがとうございます。<br />
			<?php echo f_df($this->spanFrom, 'Y年 n月'); ?> のご利用額は以下の通りです。
		</div>
		<div class="float_clear"></div>
	</div>
	<div class="summary_title">【ご請求内容】</div>
	<table class="claim_result charge_list_table summary_table" border="1" cellpadding="0" cellspacing="0">
		<tbody>

<?php // 印紙代等

$p = $this->summary;
$sb = $this->self_billing_info;
?>
			<tr class="summary fat_border">
				<td class="seq_col label_col summary_label_col">対象期間</td>
				<td class="date_col total_col summary_value_col">
					<?php echo f_df($this->spanFrom, 'Y.m.d'); ?>
					～
					<?php echo f_df($this->spanTo, 'Y.m.d'); ?>
				</td>
			</tr>
		<?php if ($p['ChargeCountExtraPay1DK'] > 0 || $p['ChargeCountExtraPay1BS'] > 0 ) { ?>
			<tr class="summary">
                <td class="seq_col label_col summary_label_col"><div>お取引件数</div>
                <?php if ($p['ChargeCountExtraPay1DK'] > 0) { ?>
                  <div>（内 届いてから払い件数（同梱））</div>
                <?php } ?>
                <?php if ($p['ChargeCountExtraPay1BS'] > 0) { ?>
                  <div>（内 届いてから払い件数（別送））</div>
                <?php } ?>
                </td>
                <td class="money_col total_col summary_value_col"><div><?php echo nvl(f_nf($p['OrderCount'], '#,##0 件'), '0 件'); ?></div>
                <?php if ($p['ChargeCountExtraPay1DK'] > 0) { ?>
                  <div>（<?php echo nvl(f_nf($p['ChargeCountExtraPay1DK'], '#,##0 件'), '0 件'); ?>）</div>
                <?php } ?>
                <?php if ($p['ChargeCountExtraPay1BS'] > 0) { ?>
                  <div>（<?php echo nvl(f_nf($p['ChargeCountExtraPay1BS'], '#,##0 件'), '0 件'); ?>）</div>
                <?php } ?>
                </td>
            </tr>
        <?php } else { ?>
            <tr class="summary">
                <td class="seq_col label_col summary_label_col"><div>お取引件数</div></td>
                <td class="money_col total_col summary_value_col"><div><?php echo nvl(f_nf($p['OrderCount'], '#,##0 件'), '0 件'); ?></div></td>
            </tr>
		<?php } ?>	
			<tr class="summary fat_border">
				<td class="seq_col label_col summary_label_col">ご利用総額</td>
				<td class="money_col total_col summary_value_col"><?php echo nvl(f_nf($p['OrderAmount'], '\ #,##0'), '\ 0'); ?></td>
			</tr>

		<?php if ($p['ChargeCountExtraPay1DK'] > 0 || $p['ChargeCountExtraPay1BS'] > 0 ) { ?>
            <tr class="summary">
                <td class="seq_col label_col summary_label_col"><div>決済手数料合計</div>
                  <div>（内 届いてから払い合計）</div>
                </td>
                <td class="money_col total_col summary_value_col"><div><?php echo nvl(f_nf(-1 * (int)$p['SettlementFee'], '\ #,##0'), '\ 0'); ?></div>
                  <div>（<?php echo nvl(f_nf(-1 * (int)$p['SettlementFeeExtraPay1'], '\ #,##0'), '\ 0'); ?>）</div>
                </td>
            </tr>
        <?php } else { ?>
            <tr class="summary">
                <td class="seq_col label_col summary_label_col"><div>決済手数料合計</div></td>
                <td class="money_col total_col summary_value_col"><div><?php echo nvl(f_nf(-1 * (int)$p['SettlementFee'], '\ #,##0'), '\ 0'); ?></div></td>
            </tr>
        <?php } ?>
		<?php if($sb['HasInfo']) { /* 同梱ツール経由の請求があった場合 */ ?>
			<?php if( $this->PaymentAfterArrivalFlg == 0 ) { /* 届いてから払い:利用しない 場合 */ ?>
            <tr class="summary">
				        <td class="seq_col label_col summary_label_col">
                <div>請求手数料合計</div>
                <div>（内 自社印刷分）</div>
                </td>
        				<td class="money_col total_col summary_value_col">
                    <div><?php echo nvl(f_nf(-1 * (int)$p['ClaimFee'], '\ #,##0'), '\ 0'); ?></div>
                    <div>（<?php echo f_nf(-1 * $sb['ClaimFee'], '\ #,##0'); ?>）</div>
        				</td>
            </tr>
        <?php } else { /* 届いてから払い:利用する 場合 */?>
            <?php
                $ClaimFeeDK = $sb['ClaimFee'] - $p['ClaimFeeExtraPay1DK'];
                $ClaimFeeBS = $p['ClaimFee'] - $sb['ClaimFee'] - $p['ClaimFeeExtraPay1BS'];
            ?>
            <tr class="summary">
                <td class="seq_col label_col summary_label_col"><div>請求手数料合計</div>
                    <?php if ($p['ChargeCountExtraPay1DK'] > 0) { ?>
                        <div>（内 届いてから払い合計（同梱））</div>
                    <?php } ?>
                    <?php if ($p['ChargeCountExtraPay1BS'] > 0) { ?>
                        <div>（内 届いてから払い合計（別送））</div>
                    <?php } ?>
                    <?php //if ($ClaimFeeDK > 0) { ?>
                        <!-- <div>（内 届いてから以外決済合計（同梱））</div> -->
                    <?php //} ?>
                    <?php //if ($ClaimFeeBS > 0) { ?>
                        <!-- <div>（内 届いてから以外決済合計（別送））</div> -->
                    <?php //} ?>
            	</td>
            	<td class="money_col total_col summary_value_col">
                    <div><?php echo nvl(f_nf(-1 * (int)$p['ClaimFee'], '\ #,##0'), '\ 0'); ?></div>
                    <?php if ($p['ChargeCountExtraPay1DK'] > 0) { ?>
                        <div>（<?php echo nvl(f_nf(-1 * (int)$p['ClaimFeeExtraPay1DK'], '\ #,##0'), '\ 0'); ?>）</div>
                    <?php } ?>
                    <?php if ($p['ChargeCountExtraPay1BS'] > 0) { ?>
                        <div>（<?php echo nvl(f_nf(-1 * (int)$p['ClaimFeeExtraPay1BS'], '\ #,##0'), '\ 0'); ?>）</div>
                    <?php } ?>
                    <?php //if ($ClaimFeeDK > 0) { ?>
                        <!-- <div>（<?php //echo nvl(f_nf(-1 * (int)$ClaimFeeDK, '\ #,##0'), '\ 0'); ?>）</div> -->
                    <?php //} ?>
                    <?php //if ($ClaimFeeBS > 0) { ?>
                        <!-- <div>（<?php //echo nvl(f_nf(-1 * (int)$ClaimFeeBS, '\ #,##0'), '\ 0'); ?>）</div> -->
                    <?php //} ?>
            	</td>
            </tr>
        <?php }?>
		<?php } else { ?>
			<tr class="summary">
				<td class="seq_col label_col summary_label_col"><div>請求手数料合計</div>
              <?php if ($p['ChargeCountExtraPay1BS'] > 0) { ?>
                <div>（内 届いてから払い合計（別送））</div>
              <?php } ?>
				</td>
				<td class="money_col total_col summary_value_col"><div><?php echo nvl(f_nf(-1 * (int)$p['ClaimFee'], '\ #,##0'), '\ 0'); ?></div>
              <?php if ($p['ChargeCountExtraPay1BS'] > 0) { ?>
                <div>（<?php echo nvl(f_nf(-1 * (int)$p['ClaimFeeExtraPay1BS'], '\ #,##0'), '\ 0'); ?>）</div>
              <?php } ?>
				</td>
			</tr>
		<?php } ?>
			<tr class="summary">
				<td class="seq_col label_col summary_label_col">印紙代合計<span>(※)</span></td>
				<td class="money_col total_col summary_value_col"><?php echo nvl(f_nf(-1 * (int)$p['StampFee'], '\ #,##0'), '\ 0'); ?></td>
			</tr>
			<tr class="summary">
				<td class="seq_col label_col summary_label_col">月額固定費</td>
				<td class="money_col total_col summary_value_col"><?php echo nvl(f_nf(-1 * (int)$p['MonthlyFee'], '\ #,##0'), '\ 0'); ?></td>
			</tr>
			<tr class="summary">
				<td class="seq_col label_col summary_label_col">前回持越し分（固定費）</td>
				<td class="money_col total_col summary_value_col"><?php echo nvl(f_nf($p['CarryOverMonthlyFee'], '\ #,##0'), '\ 0'); ?></td>
			</tr>
			<tr class="summary">
			<td class="seq_col label_col summary_label_col"><div>キャンセル返金分</div>
				<?php if ($p['CalcelAmountExtraPay1DK'] > 0 && $p['CancelRepaymentAmount'] > 0) { ?>
				<div>（内 届いてから払い合計（同梱））</div>
				<?php } ?>
				<?php if ($p['CalcelAmountExtraPay1BS'] > 0 && $p['CancelRepaymentAmount'] > 0) { ?>
				<div>（内 届いてから払い合計（別送））</div>
				<?php } ?>
				</td>
				<td class="money_col total_col summary_value_col"><div><?php echo nvl(f_nf($p['CancelRepaymentAmount'], '\ #,##0'), '\ 0'); ?></div>
				<?php if ($p['CalcelAmountExtraPay1DK'] > 0 && $p['CancelRepaymentAmount'] > 0) { ?>
				<div>（<?php echo nvl(f_nf($p['CalcelAmountExtraPay1DK'], '\ #,##0'), '\ 0'); ?>）</div>
				<?php } ?>
				<?php if ($p['CalcelAmountExtraPay1BS'] > 0 && $p['CancelRepaymentAmount'] > 0) { ?>
				<div>（<?php echo nvl(f_nf($p['CalcelAmountExtraPay1BS'], '\ #,##0'), '\ 0'); ?>）</div>
				<?php } ?>
				</td>
			</tr>
			<tr class="summary fat_border">
				<td class="seq_col label_col summary_label_col">口座振込手数料</td>
				<td class="money_col total_col summary_value_col"><?php echo nvl(f_nf(-1 * (int)$p['FfTransferFee'], '\ #,##0'), '\ 0'); ?></td>
			</tr>
<?php if (false) { ?>
			<tr class="summary payback">
				<td class="seq_col label_col summary_label_col">立替精算戻し額</td>
				<td class="money_col total_col summary_value_col"><?php echo nvl(f_nf($p['PayBackAmount'], '\ #,##0'), '\ 0'); ?></td>
			</tr>
<?php } ?>
			<tr class="summary double_border">
				<td class="seq_col label_col summary_label_col">精算調整額</td>
				<td class="money_col total_col summary_value_col"><?php echo nvl(f_nf($p['AdjustmentAmount'], '\ #,##0'), '\ 0'); ?></td>
			</tr>
			<tr class="summary">
				<td class="seq_col label_col summary_label_col">ご請求金額</td>
				<td class="money_col total_col summary_value_col"><?php echo nvl(f_nf($p['ClaimAmount'], '\ #,##0'), '\ 0'); ?></td>
			</tr>

			<tr class="summary">
				<td class="seq_col label_col summary_label_col">お支払総額</td>
				<td class="money_col total_col summary_value_col"><?php echo nvl(f_nf($p['PaymentAmount'], '\ #,##0'), '\ 0'); ?></td>
			</tr>
		</tbody>
	</table>
	<?php echo $this->render( 'oemmember/information_message.php' ); ?>
</div>
<div style="font-size: 9pt; margin: 0px 8px; float: none; clear: both; width: 870px">
	※「印紙代合計」とは、購入者様のコンビニ決済によるお支払金額が2014年4月1日から2019年9月30日までに発行された請求書は税込で\54,000以上、2019年10月1日以降に発行された請求書は税抜で\50,000以上の場合に発生する印紙代金の合計金額です。
</div>
