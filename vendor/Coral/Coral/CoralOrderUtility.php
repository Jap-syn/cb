<?php
namespace Coral\Coral;

use Coral\Base\BaseGeneralUtils;

/**
 * 注文データに関するユーティリティクラス
 *
 */
class CoralOrderUtility {
	private static $__statusCaptions;

	public static function getStatusCaptions() {
		if( ! is_array( self::$__statusCaptions ) ) {
			self::$__statusCaptions = array(
				'close_payback' => '立替精算戻し',
		        'close_test' => 'テスト注文',
				'close_damaged' => '貸し倒れ',
				'close_ng' => '与信NG',
				'close_cancel' => 'キャンセル済',
				'request_cancel' => 'キャンセル申請中',
				'close_saiken' => 'キャンセル（返却済み）',
				'request_saiken' => 'キャンセル（返却前）',
				'receipted_normal' => '支払済み(期限内)',
				'receipted_damaged' => '支払済み(期限超過)',
				'noreceipt_normal' => '未入金',
				'noreceipt_damaged' => '不払い',
			    'receipt_part' => '一部入金',// Add By Takemasa(NDC) 20150427 Stt 注文状態項目追加
		        'receipted_normal_excesspayment' => '支払済み(期限内)',      // 過剰入金
		        'receipted_damaged_excesspayment' => '支払済み(期限超過)',   // 過剰入金
		        'credit' => '届いてから払い 決済完了',
			);
		}
		return self::$__statusCaptions;
	}

	/**
	 * 2013.2.22
	 */
	public static function getOrderRowClass($order , $type = null) {
		$orderInfo = self::getOrderInfo($order ,$type);

		return $orderInfo['caption'];
	}

	/**
	 * 2013.2.22
	 *
	 * @param array $order 注文情報 ※内部でToArray変換は行わないので必ずarrayで通知すること(20150115_1635)
	 * @param null|string $type タイプ
	 */
	public static function getOrderInfo($order , $type = null) {

        if (!isset($order['Clm_F_LimitDate']) && isset($order['F_LimitDate'])) { $order['Clm_F_LimitDate'] = $order['F_LimitDate']; }
        if (!isset($order['ReceiptDate']) && isset($order['Rct_ReceiptDate'])) { $order['ReceiptDate'] = $order['Rct_ReceiptDate']; }
        if (isset($order['CloseReceiptDate'])) { $order['ReceiptDate'] = $order['CloseReceiptDate']; }

	    $result = array();

		$caption = '';
		$delaydate = '';

		if( $order['DataStatus'] == 91 && $order['CloseReason'] == 6 ) {
			// 立替精算戻し
			$caption = 'close_payback';
			$delaydate = '***';
		} else if( $order['DataStatus'] == 91 && $order['CloseReason'] == 5 ) {
			// テスト注文
			$caption = 'close_test';
			$delaydate = '***';
		} else if( $order['DataStatus'] == 91 && $order['CloseReason'] == 4 ) {
			// 貸し倒れ
			$caption = 'close_damaged';

			// 請求前
			if(empty( $order['Clm_F_LimitDate'] )) {
				$delaydate = '***';
			}
			// 入金済
			else if($order['Rct_Status'] ) {
				$receiptPastDays = BaseGeneralUtils::CalcSpanDaysFromString($order['Clm_F_LimitDate'], $order['ReceiptDate']);
				$delaydate = sprintf("%d 日", $receiptPastDays);
			}
			// 請求後入金前
			else {
				$pastDays = BaseGeneralUtils::CalcSpanDaysFromString($order['Clm_F_LimitDate'], date('Y-m-d'));
				$delaydate = sprintf("%d 日", $pastDays);
			}
		} else if( $order['DataStatus'] == 91 && $order['CloseReason'] == 3 ) {
			// 与信NGクローズ
			$caption = 'close_ng';
			$delaydate = '***';
		} else if( $order['DataStatus'] == 91 && $order['CloseReason'] == 2) {
			// キャンセルクロース
			$caption = 'close_cancel';

			if(!empty($order['Cnl_ReturnSaikenCancelFlg']) || $type == '1') {
				$caption = 'close_saiken';
			}

			// 請求前
			if(empty( $order['Clm_F_LimitDate'] )) {
				$delaydate = '***';
			}
			// 入金済
			else if($order['Rct_Status'] ) {
				$receiptPastDays = BaseGeneralUtils::CalcSpanDaysFromString($order['Clm_F_LimitDate'], $order['ReceiptDate']);
				$delaydate = sprintf("%d 日", $receiptPastDays);
			}
			// 請求後入金前
			else {
				$pastDays = BaseGeneralUtils::CalcSpanDaysFromString($order['Clm_F_LimitDate'], date('Y-m-d'));
				$delaydate = sprintf("%d 日", $pastDays);
			}
		} else if ( $order['Cnl_Status'] == 1) {
			// キャンセル申請中
			$caption = 'request_cancel';

			if(!empty($order['Cnl_ReturnSaikenCancelFlg']) || $type == '1') {
				$caption = 'request_saiken';
			}

			// 請求前
			if(empty( $order['Clm_F_LimitDate'] )) {
				$delaydate = '***';
			}
			// 入金済
			else if($order['Rct_Status'] ) {
				$receiptPastDays = BaseGeneralUtils::CalcSpanDaysFromString($order['Clm_F_LimitDate'], $order['ReceiptDate']);
				$delaydate = sprintf("%d 日", $receiptPastDays);
			}
			// 請求後入金前
			else {
				$pastDays = BaseGeneralUtils::CalcSpanDaysFromString($order['Clm_F_LimitDate'], date('Y-m-d'));
				$delaydate = sprintf("%d 日", $pastDays);
			}
		} else if( empty( $order['Clm_F_LimitDate'] ) ) {
		    // 請求書未発行 → 支払期限内・未入金扱い
			$caption = 'noreceipt_normal';
			$delaydate = '***';
		} else if( $order['DataStatus'] == 61) {
		    // 一部入金
		    $caption = 'receipt_part';

		    // 遅れ日数の取得
		    $receiptPastDays = BaseGeneralUtils::CalcSpanDaysFromString($order['Clm_F_LimitDate'], $order['ReceiptDate']);
		    $delaydate = sprintf("%d 日", $receiptPastDays);
		} else if( $order['Rct_Status'] ) {
			// 入金済み
			$caption = 'receipted_';
			// 初回請求期限と入金日から延滞状態を判断
			$caption .= BaseGeneralUtils::CalcSpanDaysFromString(
				$order['Clm_F_LimitDate'], $order['ReceiptDate']
			) > 0 ? 'damaged' : 'normal';

			// 遅れ日数の取得
			$receiptPastDays = BaseGeneralUtils::CalcSpanDaysFromString($order['Clm_F_LimitDate'], $order['ReceiptDate']);
			$delaydate = sprintf("%d 日", $receiptPastDays);
		} else {
			// 未入金
			$caption = 'noreceipt_';
			// 初回請求期限と現在日から延滞状態を判断
			$caption .= BaseGeneralUtils::CalcSpanDaysFromString(
				$order['Clm_F_LimitDate'], date('Y-m-d')
			) > 0 ? 'damaged' : 'normal';

			// 超過日数の取得
			$pastDays = BaseGeneralUtils::CalcSpanDaysFromString($order['Clm_F_LimitDate'], date('Y-m-d'));
			$delaydate = sprintf("%d 日", $pastDays);
		}
        // 過剰入金色分けしきい値判定
        if (strpos($caption, 'receipted') === 0 && nvl($order['Rct_DifferentialAmount'],0) < ($order['ExcessPaymentColorThreshold'] * -1)) {
            $caption .= '_excesspayment';
        }
    //クレジット決済
    if(isset($order['ExtraPayType']) && $order['ExtraPayType'] == 1){
        $caption = 'credit';
    }
		$result = array(
			'caption' => $caption,
			'delaydate' => $delaydate,
		);

		return $result;
	}
}

