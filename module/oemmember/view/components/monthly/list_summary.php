<div class="print_header">
	<div class="print_header_left">
		貴社名：　
		<span class="enterprise_name"><?php echo f_e($this->enterprise->EnterpriseNameKj); ?>　様</span>
	</div>
	<div class="print_header_right">
		<div><img src="resource/image/type/logo-s" style="height: 16px" /></div>
		<div>
			平素は後払い決済をご利用いただき、誠にありがとうございます。<br />
			<?php echo f_df($this->fixedMonth, 'Y年 n月'); ?>のご利用明細は以下となります。
		</div>
	</div>
	<div class="float_clear" style="margin-bottom: 20px"></div>

	<div class="print_header_left" style="font-size: 12pt">
		<?php echo f_df($this->fixedMonth, 'Y年 n月'); ?> お取引件数：
		<?php echo nvl(f_nf($this->current_list_count, '#,##0 件'), '0 件'); ?>
	</div>
	<div class="print_header_right" style="font-size: 12pt">
		明細発行日：<?php echo f_df($this->issueDate, 'Y/m/d'); ?>
	</div>
	<div class="float_clear" style="margin-bottom: 10px"></div>
</div>
