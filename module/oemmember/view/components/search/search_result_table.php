<?php
use Coral\Coral\View\Helper\CoralViewHelperValueFormat;

$viewHelper = new CoralViewHelperValueFormat();

$classMap = array(
	'NameKj' => 'name',
	'Ent_Note' => 'note',
	'UnitingAddress' => 'address',
	'DestNameKj' => 'name',
	'DestUnitingAddress' => 'address',
	'UseAmount' => 'amount'
);

$typeMap = array(
	'ReceiptOrderDate' => 'date',
	'UseAmount' => 'number',
	'CarriageFee' => 'number',
	'ChargeFee' => 'number',
	'Chg_ExecDate' => 'date',
//	'Deli_JournalIncDate' => 'date',
//	'CancelDate' => 'date',
//	'ApprovalDate' => 'date',
	'ExecScheduleDate' => 'date',
);

$replaceEmpty = $viewHelper->getReplaceEmpty();
$viewHelper->setReplaceEmpty(true);
if( !empty( $this->searchResult )) {
	// 結果あり
?>
<div class="search_result_container">
<table id="search_result_table" class="search_items" border="1" cellpadding="0" cellspacing="0">
<script>$("search_result_table").style.visibility = "hidden";</script>
	<thead>
		<tr>
			<th class="column_header">No.</th>
			<th class="column_header cancel_cmd">キャンセル</th>
<?php
	foreach( $this->searchConditions as $condition ) {
		if( $condition['hidden'] ) continue;
		if ($condition['column'] == "ClaimDate") continue;
		$col = $condition['column'];
		$classes = array( 'column_header', $classMap[$col] );
?>
			<th class="<?php echo join(' ', $classes); ?>"><?php echo escape( $condition['caption'] ); ?></th>
<?php
	}
?>
			<th class="column_header">請求日</th>
		</tr>
	</thead>
	<tbody>
<?php
	// 行データレンダリング
	foreach( $this->searchResult as $i => $row ) {
		$index_offset = $this->pager->getStartIndex( $this->current_page, true );

		$seq = $row['_OrderSeq'];
		$orderId = $row['_OrderId'];

		$classes = array( 'search_item', 'cancel_cmd' );
		if( $row['_Cnl_CantCancelFlg'] || $row['_IncreStatus'] == -1 ) {
			$classes[] = 'unavailable';
			$inner = '不可';
		} else {
			switch( $row['_Cnl_Status'] ) {
				case 1:
//					$inner = '申請中';
					if( $row['CombinedClaimTargetStatus'] != 11 && $row['CombinedClaimTargetStatus'] != 12 ) {
                        //SBPS-29: check todoItekara to show or disable button
                        if ($row['flagTodoItekara'] == false) {
                            $inner =
                                '<a id="cnl_cnl' . $seq .
                                '" class="command do_cancel_cancel"' .
                                ' href="#"' .
                                ' onclick="return false"' .
                                ' title="この注文のキャンセルを取り消します"' .
                                '>キャンセル取消</a>';
                        } else {
                            $inner =
                                '<a id="todo_cnl' . $seq .
                                ' onclick="return false"' .
                                ' href="search/detail/id/'. $row['_OrderId'] .
                                ' "title="' .$orderId.':'.$row['_NameKj'].
                                ' "style="font-size:11px;color: blue;text-decoration:none;"'.
                                '>済</a>';
                        }
                    }
                    else {
                        $classes[] = 'progress';
                        $inner = '申請中';
                    }
					if($row['Cnl_ReturnSaikenCancelFlg'] == '1') {
                        $classes[] = 'progress';
						$inner = '返却中';
					}
					break;
				case 2:
					$classes[] = 'canceled';
					$inner = '済';
					if($row['Cnl_ReturnSaikenCancelFlg'] == '1') {
						$inner = '返却済';
					}
					break;
				default:
					// 口座振替0円請求に対するキャンセル不可
					// 事業者マスタ：請求金額0円時 & 注文情報：口座振替利用する & 注文ステータス：入金クローズ
					if($row['_AppFormIssueCond'] == 2
					&& $row['_CreditTransferRequestFlg'] != 0
					&& $row['_DataStatus'] == 91
					&& $row['_CloseReason'] == 1
					&& $row['_UseAmount'] == 0) {
						$inner = '<a title="口座振込用紙発行済みの為、キャンセルできません">不可</a>';
					} else {
						if( $row['CombinedClaimTargetStatus'] != 11 && $row['CombinedClaimTargetStatus'] != 12 ) {
							if ($row['flagTodoItekara'] == false) {
								$inner =
									'<a id="cnl_' . $seq .
									'" data-value="' . $row['flagTodoItekara'] .
									'" class="command do_cancel"' .
									' href="#"' .
									' onclick="return false"' .
									' title="この注文をキャンセルします"' .
									'>キャンセル申請</a>';
							} else {
								$inner =
									'<a id="cnl_' . $seq .
									'" data-value="' . $row['flagTodoItekara'] .
									'" class="command do_cancel"' .
									' href="#"' .
									' onclick="return false"' .
									' title="この注文をキャンセルします"' .
									'>キャンセル確定</a>';
							}
						}
						else {
							$classes[] = 'progress';
							$inner = '未キャンセル';
						}
					}
					break;
			}
		}
?>
		<tr id="row_<?php echo $seq; ?>" class="item_row" data-id="<?php echo $orderId;?>" title="<?php echo $orderId . ':' . $row['_NameKj']; ?>" <?php if ($this->alClass[$i]['AlertClass'] == '1') { echo 'style="background:pink;"'; } else if ( $this->searchResult[$i]['IncreStatus'] == -1 ) { echo 'style="background:#3165ff;"'; } ?>>
			<td class="search_item caption">
				<a class="show_detail" href="search/detail/id/<?php echo $row['_OrderId']; ?>" onclick="return showDetail(this);">
					<?php echo ( $i + $index_offset ); ?>
				</a>
			</td>
			<td class="<?php echo join(' ', $classes); ?>">
			    <?php echo $inner; ?>
			    <input type="hidden" id="combined_<?php echo $seq; ?>" value="<?php echo $row['_CombinedClaimTargetStatus']; ?>" />
			</td>
<?php
		$link = sprintf('search/detail/id/%s', $row['_OrderId']);
		foreach( $this->searchConditions as $condition ) {
			if( $condition['hidden'] ) continue;
			if ($condition['column'] == "ClaimDate") continue;
			// カラム名取得
			$col = $condition['column'];
			// マスタ解決
			$val = isset( $this->masters[ $col ] ) ?
				$this->masters[$col][ $row[$col] ] : $row[$col];

			// CSSクラス構築
			$classes = array( 'search_item', $classMap[$col] );
			$NgBtnFlg = 0;
			// 与信結果の場合のみの特殊処理
			if( $col == 'IncreStatus' ) {
				switch( $val ) {
					case 'OK':
						$classes[] = 'incre_ok';
						break;
					case 'NG':
						if ($row['NoGuaranteeFlg'] == 1) {
							$NgBtnFlg = 1;
						} else {
							$classes[] = 'incre_ng';
						}
						break;
					default:
						if(in_array($row['_Cnl_Status'], array(1, 2))) {
							// キャンセルの場合
							$classes[] = 'incre_cancel';
							$val = sprintf('与信中CNL%s',
										   $row['_Cnl_Status'] == 2 ? '済' : '');
						} else {
							$classes[] = 'incre';
						}
						break;
				}
			}
			if( $col == 'ClaimSendingClass' ) {
                if( $row['ClaimSendingClass'] == 12 || $row['ClaimSendingClass'] == 21 ) {
                    $val = '別送';
                }
                else if( $row['ClaimSendingClass'] == 11 ) {
                    $val = '同梱';
                }
            }

			$type = $typeMap[ $col ];
			$format = $type == 'number' ? '#,##0' : ( $type == 'date' ? 'Y/m/d' : null );
?>
			<td class="<?php echo join(' ', $classes); ?>" ><!--
				<?php if($NgBtnFlg == 1) { ?>
				--><a id="cnl_0" class="command do_cancel" href="#" onclick="return NoGuaranteeChange(<?php echo $seq; ?>);" title="この注文を無保証に変更します">無保証に変更</a><!--
				<?php } else { ?>
				--><a class="show_detail" href="<?php echo f_e($link); ?>" onclick="return showDetail(this);" <?php if ( $this->searchResult[$i]['IncreStatus'] == -1 ) { echo 'style="color:white"'; } ?>><?php echo $viewHelper->valueFormat( $val, $type, $format ); ?></a><!--
				<?php } ?>
			--></td>
<?php
		}
?>
			<?php /* 前回請求日追加（10.4.6 eda） */ ?><?php /* rev.465にて、重複表示のバグ修正として削除したが、この項目のCSVへの出力が禁止になったため復帰（10.4.13 eda） */ ?>
			<td class="search_item date"><!--
				--><a class="show_detail" href="<?php echo f_e($link); ?>" onclick="return showDetail(this);"><?php echo $viewHelper->valueFormat($row['_ClaimDate'], 'date', 'yy/MM/dd'); ?></a><!--
			--></td>
		</tr>
<?php
	}
?>
	</tbody>
</table>
</div>
<?php
}
$viewHelper->setReplaceEmpty( $replaceEmpty );
?>
