<?php $no_clear_count = 0; ?>
	<h3 class="contents_title">一括着荷確認　　　着荷確認待ち：　<?php echo $this->cnt; ?></h3>
	<div class="page_nav" style="border-top-width: 1px;">
		確認日一括設定：
		<?php echo $this->gcadTag; ?>
		<button id="apply_gcad" style="padding-left: 16px; padding-right: 16px;">一括変更</button>
		<span style="margin-left: 40px">
			<a id="exec_all_on" class="func_link" href="javascript:void(0)">すべてチェックする</a>
			<a id="exec_all_off" class="func_link" href="javascript:void(0)">すべてチェックをはずす</a>
		</span>
	</div>
    <form id="main_form" name="form" method="post" action="rwarvlcfm/lumpup">
	  <input type="hidden" name="lastexp[journalDate]" value="<?php echo f_df($this->exp['journalDate'], 'Y-m-d'); ?>" />
	  <input type="hidden" name="lastexp[receiptDate]" value="<?php echo f_df($this->exp['receiptDate'], 'Y-m-d'); ?>" />
	  <input type="hidden" name="lastexp[fixPattern]" value="<?php echo f_e($this->exp['fixPattern']); ?>" />
      <table style="clear:both; margin: 0px 0px 0px 25px;" width="858" class="datatable" cellpadding="1" cellspacing="1" border="0">
        <tbody>
          <tr>
            <td class="r_data" style="padding: 8px;"><input id="exec_submit" style="width: 120px;" type="submit" value="着荷確認決定" /></td>
          </tr>
        </tbody>
      </table>
      <div style="margin: 0px 24px 20px 25px; border: none 0px;">
	  <div id="loading_message">now loading...</div>
      <table id="main_table" style="clear:both; margin: 0px; display: none;" width="858" class="datatable" cellpadding="1" cellspacing="1" border="0">
        <thead>
          <tr>
          	<td class="c_label">事業者名</td>
          	<td class="c_label">伝登日</td>
          	<td class="c_label">入金日</td>
          	<td class="c_label">配送先氏名</td>
            <td class="c_label">伝票番号</td>
          	<td class="c_label">締P</td>
          	<td class="c_label">利用額</td>
          	<td class="c_label">確認日</td>
          	<td class="c_label">確認</td>

          </tr>
        </thead>
        <tbody>
		  <?php foreach($this->list as $i => $list_item) { ?>
		  <?php
		  $no_clear_flg = ($list_item['Deli_PayChgCondition'] != 1 || $list_item['OutOfAmends'] == 1) && ! $list_item['ClearConditionForCharge'];
		  $no_clear_count += $no_clear_flg ? 1 : 0;

		  $row_class = array(sprintf('orderstatus_%s_normal', $list_item['is_receipted'] ? 'receipted' : 'noreceipt'));
		  //if($no_clear_flg) $row_class[] = 'not_selected';
		  ?>
          <tr id="row_<?php echo $i; ?>" class="<?php echo join(' ', $row_class); ?>" title="<?php echo $list_item['receipted_info']; ?>">
			<td class="l_data<?php if($list_item['Special01Flg']) echo ' sp_ent_flg'; ?>">
              <input type="hidden" name="Deli_PayChgCondition<?php echo $i; ?>" id="Deli_PayChgCondition<?php echo $i; ?>" value="<?php echo $list_item["Deli_PayChgCondition"]; ?>" />
              <input type="hidden" name="OutOfAmends<?php echo $i; ?>" id="OutOfAmends<?php echo $i; ?>" value="<?php echo $list_item["OutOfAmends"]; ?>" />
			  <input type="hidden" name="Deli_ConfirmNoArrivalReason<?php echo $i; ?>" id="Deli_ConfirmNoArrivalReason<?php echo $i; ?>" value="<?php echo nvl($list_item['Deli_ConfirmNoArrivalReason'], "0"); ?>" />
			  <input type="hidden" name="ClearConditionForCharge<?php echo $i; ?>" id="ClearConditionForCharge<?php echo $i; ?>" value="<?php echo $list_item['ClearConditionForCharge']; ?>" />
			  <?php $link_title = sprintf('事業者ID：%s%s', $list_item['EnterpriseId'], $list_item['Special01Flg'] ? '(特殊店舗)' : ''); ?>
              <a href="enterprise/detail/eid/<?php echo f_e($list_item['EnterpriseId']); ?>" target="_blank" title="<?php echo f_e($link_title); ?>">
			    <?php echo f_e($list_item["EnterpriseNameKj"]); ?>
			  </a>
              <input type="hidden" name="OrderSeq<?php echo $i; ?>" id="OrderSeq<?php echo $i; ?>" value="<?php echo $list_item["OrderSeq"]; ?>" />
            </td>
            <td class="l_data"><?php echo $list_item["Deli_JournalIncDate"]; ?></td>
            <td class="l_data"><?php echo $list_item["ReceiptDate"]; ?></td>
            <td class="l_data"<?php if($no_clear_flg) echo ' style="background-color: pink" title="【注意】配送先情報に不備あり！！"'; ?>>
			  <a target="_blank" href="rworder/detail/oseq/<?php echo $list_item["OrderSeq"]; ?>" title="注文ID：<?php echo f_e($list_item['OrderId']); ?>">
			    <?php echo $list_item["OrderId"]; ?>
			  </a>
			  </br><?php echo $list_item["DestNameKj"]; ?>
			</td>
            <td class="l_data"><?php echo $list_item["Deli_JournalNumber"]; ?></td>
            <td class="l_data"><?php echo $list_item["FixPattern"]; ?></td>
            <td class="r_data"><?php echo number_format($list_item["UseAmount"]); ?></td>
            <td class="c_data date_list_container"><?php echo $list_item["cadTag"]; ?></td>
		    <td class="c_data">
				<input class="deli_check" id="chk_<?php echo $i; ?>" name="Deli_ConfirmArrivalFlg<?php echo $i; ?>" type="checkbox" value="1" checked="checked" onclick="toggleCheck(this)" />
			</td>
          </tr>
          <tr class="border">
            <td colspan="7">
              <img src="../images/spacer_gray.gif" height="1" width="1">
            </td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
      </div>
    </form>
	<script>
	Event.observe(window, "load", function() {
		Element.show($("main_table"));
		setTimeout( function() {
			Element.hide( $("loading_message") );
		}, 0 );

		Event.observe($("main_form"), "submit", function() {
			$("exec_submit").disabled = $("exec_filter").disabled = true;
			setTimeout(function() {
				$("main_form").submit();
			}, 0);
			return false;
		});
	});
	</script>
