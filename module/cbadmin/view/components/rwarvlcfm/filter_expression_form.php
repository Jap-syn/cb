<?php
function show_error_message($messages) {
	if( empty($messages) ) return;
	if( ! is_array($messages) ) $messages = array($messages);
	$list = array();
	foreach($messages as $msg) $list[] = f_e($msg);
	echo '<div class="input_error_message">';
	echo join('<br />', $list);
	echo '</div>';
}
?>
    <h3 class="contents_title">一括着荷確認対象検索</h3>
	<form id="filter_form" action="rwarvlcfm/filter" method="post" style="margin: 15px 0px 40px 0px;">
		<div class="page_nav" style="border-width: 1px; padding-bottom: 20px; padding-top: 20px;">
			<div class="form_item">
				<label for="journalDate">
					伝票登録日：
					<input id="journalDate" name="exp[journalDate]" class="date_type" size="15" value="<?php if($this->exp['journalDate']) echo f_df($this->exp['journalDate'], 'Y-m-d'); ?>" />
				</label>
                <a href="javascript:void(0)" id="journalDate_choose" title="日付を選択" onclick="return false;">
                  <img src="./../images/icon_date_s.gif" alt="日付を選択" />
                </a>
				以前
				<?php show_error_message(isset($this->error['journalDate']) ? $this->error['journalDate'] : null); ?>
			</div>

			<div class="form_item">
				<label for="receiptDate">
					入金日：
					<input id="receiptDate" name="exp[receiptDate]" class="date_type" size="15" value="<?php if($this->exp['receiptDate']) echo f_df($this->exp['receiptDate'], 'Y-m-d'); ?>" />
				</label>
                <a href="javascript:void(0)" id="receiptDate_choose" title="日付を選択" onclick="return false;">
                  <img src="./../images/icon_date_s.gif" alt="日付を選択" />
                </a>
				以前
				<?php show_error_message(isset($this->error['receiptDate']) ? $this->error['receiptDate'] : null); ?>
			</div>

			<div class="form_item">
			     締めパターン：<?php echo $this->fixPatternTag; ?>
			</div>

			<div class="form_item">
                OEM：<?php echo $this->oemTag; ?>
                </p>
                加盟店ID：<input style="text-align: left;" type="text" size="10" name="entid" id="endid" value="<?php echo f_e($this->entid); ?>" />
                加盟店名：<input style="text-align: left;" type="text" size="20" name="entnm" id="endnm" value="<?php echo f_e($this->entnm); ?>" />
                配送業者：<?php echo $this->deliMethodTag; ?>
			</div>

			<div style="float: none; clear: both; font-size:1px; maring: 0px; padding: 0px;">&nbsp;</div>
		</div>

		<div class="page_nav" style="border-width: 1px; border-top-width: 0px; margin-bottom: 20px; padding: 8px; text-align: center;">
			<input type="submit" id="exec_filter" value="　　検　　索　　" />
		</div>
	</form>
	<script>
	Event.observe(window, "load", function() {
		// カレンダーコントロール初期化
		new base.UI.DatePicker(
			'cdpJournalDate',
			$('journalDate'),
			$('journalDate_choose')
		)
		.format="yyyy-MM-dd";

		new base.UI.DatePicker(
			'cdpReceiptDate',
			$('receiptDate'),
			$('receiptDate_choose')
		)
		.format="yyyy-MM-dd";

		Event.observe($("filter_form"), "submit", function() {
			$("exec_filter").disabled = ($("exec_submit") || { disabled : false } ). disabled = true;
			setTimeout( function() {
				$("filter_form").submit();
			}, 0);
			return false;
		});
	} );
	</script>