<?php
namespace models\Logic\RwarvlData;

use Zend\Db\Adapter\Adapter;
use models\Logic\LogicRwarvlData;
use models\Logic\RwarvlData\Exporter\LogicRwarvlDataExporterFormatter;

/**
 * 着荷確認データ連携ロジック用エクスポートエンジン
 */
class LogicRwarvlDataExporter {
	/**
	 * 親ロジック
	 *
	 * @access protected
	 * @var LogicRwarvlData
	 */
	protected $_logic;

	/**
	 * 親の着荷確認データ連携ロジックを指定してLogicRwarvlDataExporterの
	 * 新しいインスタンスを初期化する
	 *
	 * @param LogicRwarvlData $logic 親ロジック
	 */
	public function __construct(LogicRwarvlData $logic) {
		$this->_logic = $logic;
	}

	/**
	 * 親の着荷確認データ連携ロジックを取得する
	 *
	 * @return LogicRwarvlData
	 */
	public function getParentLogic() {
		return $this->_logic;
	}

	/**
	 * エクスポートデータを生成する
	 *
	* @return array エクスポート用データ
	*/
	public function getExportList() {
		/** @var Adapter */
		$db = $this->getParentLogic()->getAdapter();

		// データ取得実行
		$list = array();
		$ri = $db->query($this->_getExportQuery())->execute();
		foreach($ri as $row) {
		    // 除外データの取得
		    $sql = " SELECT * FROM T_CombinedArrival WHERE OrderSeq = :OrderSeq ORDER BY Seq DESC LIMIT 1 ";
		    $stm = $db->query($sql);
		    $prm = array(
		            ':OrderSeq' => $row['OrderSeq'],
		    );
		    $excld = $stm->execute($prm)->current();
		    if($excld['Deli_JournalNumber'] == $row['Deli_JournalNumber']) {
		        // 取りまとめ着荷確認にデータがあり、かつ伝票番号が同じ場合、処理対象外
				continue;
		    }
			// 末尾にStatusCodeとStatusを追加。ただしどちらも空。
			$row = array_merge($row, array('StatusCode' => '', 'Status' => ''));

			array_push($list, $row);
		}
	        return $list;
	}

	/**
	 * 指定のエクスポート用データを指定フォーマットで出力する
	 *
	 * @param array $data エクスポート用データ
	 */
	public function export(array $data) {
		/** @var LogicRwarvlDataExporterFormatter */
		$formatter = LogicRwarvlDataExporterFormatter::factory(/* $format */);
		$formatter->format($data);
	}

  	/**
	 * エクスポート用データを抽出するベースのSQLを取得する
	 *
	 * @access protected
	 * @return string
	 */
    protected function _getExportQuery() {
        return <<<EOQ
SELECT
	ORD.OrderSeq,
	ORD.OrderId,
	SUM.Deli_JournalNumber,
	SUM.Deli_DeliveryMethodName
FROM
	T_Order ORD INNER JOIN
	T_OrderSummary SUM ON ORD.OrderSeq = SUM.OrderSeq INNER JOIN
	AT_Order AO ON AO.OrderSeq = ORD.OrderSeq INNER JOIN
	T_Site SIT ON SIT.SiteId = ORD.SiteId LEFT JOIN
	T_ClaimControl CC ON CC.OrderSeq = ORD.OrderSeq INNER JOIN
	(SELECT *
	 FROM M_DeliveryMethod
	 WHERE ArrivalConfirmUrl LIKE 'http%'
	) DELI ON DELI.DeliMethodId = SUM.Deli_DeliveryMethod
WHERE
	IFNULL(ORD.Deli_ConfirmArrivalFlg, 0) IN (-1, 0) AND
	ORD.Cnl_Status = 0 AND
	ORD.DataStatus > 31 AND
	IFNULL(ORD.CloseReason, 0) IN (0, 1) AND
	( IFNULL(ORD.CombinedClaimTargetStatus, 0) IN (0, 91, 92) OR
	( IFNULL(ORD.OemId, 0) IN (0, 1, 3, 4) AND IFNULL(ORD.CombinedClaimTargetStatus, 0) IN (1, 2, 11, 12) ) ) AND

	IFNULL(ORD.OutOfAmends, 0) = 0 AND
	DELI.PayChgCondition = 1 AND
	SUM.Deli_JournalNumber NOT LIKE 'TMP______-%'

    AND ( AO.CreditTransferRequestFlg <> '0' OR (
    IFNULL(AO.ExtraPayType, '0') <> '1'
    AND NOT (SIT.PaymentAfterArrivalFlg = 1
         AND DATE_ADD( CC.F_ClaimDate, INTERVAL (SELECT MAX(sbps.NumUseDay) AS MaxNumUseDay FROM T_SiteSbpsPayment sbps WHERE sbps.SiteId = ORD.SiteId AND sbps.ValidFlg = 1) DAY ) >= CURRENT_DATE()
         AND NOT EXISTS (SELECT 1 FROM T_ReceiptControl AS rc WHERE rc.OrderSeq = ORD.P_OrderSeq)
        )))

ORDER BY
	ORD.OrderSeq

EOQ;
    }

}
