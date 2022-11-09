<?php
namespace models\Logic\Haipon;

use models\Logic\LogicHaipon;
use models\Logic\Haipon\Exporter\LogicHaiponExporterFormatter;

/**
 * 配送でポン連携ロジック用エクスポートエンジン
 */
class LogicHaiponExporter {
    /**
	 * 親ロジック
	 *
     * @access protected
     * @var LogicHaipon
     */
    protected $_logic;

	/**
	 * 親の配ポン連携ロジックを指定してLogicHaiponExporterの
	 * 新しいインスタンスを初期化する
	 *
	 * @param LogicHaipon $logic 親ロジック
	 */
	public function __construct(LogicHaipon $logic) {
		$this->_logic = $logic;
	}

	/**
	 * 親の配ポン連携ロジックを取得する
	 *
	 * @return LogicHaipon
	 */
	public function getParentLogic() {
		return $this->_logic;
	}

	/**
	 * 配送方法と伝票登録日の上限を指定して、エクスポートデータを生成する
	 *
     * @param array $deli_methods 配送方法IDの配列
     * @param null | string | date $journal_date 伝票登録日。この日以前に伝票登録されたものが対象となる
     * @return array エクスポート用データ
     */
    public function getExportList($deli_methods, $journal_date = null) {
		/** @var Adapter */
		$db = $this->getParentLogic()->getAdapter();

        if(!is_array($deli_methods)) $deli_methods = array($deli_methods);
        if(empty($deli_methods)) {
            throw new LogicHaiponException('配送方法が指定されていません');
        }
		// 名前付きプレースホルダはIN()の配列指定がサポートされていないので
		// 先にquoteしておいて後でjoinして使用する
//		foreach($deli_methods as &$deli_method) {
// 			$deli_method = $db->quoteInto('?', $deli_method);
//		}

        if($journal_date == null) $journal_date = date('Y-m-d');
//         if($journal_date instanceof Zend_Date) $journal_date = $journal_date->toString('yyyy-MM-dd');

		// データ取得実行
//		$q = $db->quoteInto(sprintf($this->_getExportQuery(), join(',', $deli_methods)), $journal_date);
        $q = $this->_getExportQuery();
        $stm =$db->query( $q );
        $prm = array(
            ':DeliMethodIds' => join( ',', $deli_methods ),
            ':Deli_JournalIncDate' => $journal_date,
        );
        $rows = ResultInterfaceToArray( $stm->execute( $prm ) );

		$buf = array();
// 		foreach($db->fetchAll($q) as $row) {
        foreach( $rows as $row ) {
			$key = sprintf('key:%s', $row['Deli_JournalNumber']);
			if(!isset($buf[$key])) {
				$buf[$key] = $row;
			} else {
				// 重複伝票番号は_compDupDataで残す注文を決定
				$buf[$key] = $this->_compDupData($row, $buf[$key]);
			}
		}
		// リスト確定
        $list = array_values($buf);
		// ソート実行
        usort($list, array($this, '_sortExportEntity'));

        return $list;
    }

	/**
	 * 伝票番号重複用の注文比較メソッド。
	 * 伝票登録日、注文登録日の順にもっとも遅いデータを優先させる
	 *
	 * @access protected
	 * @param array $a 比較要素1
	 * @param array $b 比較要素2
	 * @return array $aと$bのうち優先判定されたデータ
	 */
	protected function _compDupData($a, $b) {
		if($a['Deli_JournalIncDate'] != $b['Deli_JournalIncDate']) {
			return $a['Deli_JournalIncDate'] > $b['Deli_JournalIncDate'] ? $a : $b;
		} else {
			return $a['OrderSeq'] > $b['OrderSeq'] ? $a : $b;
		}
	}

	/**
	 * 指定のエクスポート用データを指定フォーマットで出力する
	 *
	 * @param array $data エクスポート用データ
	 * @param null | string $format フォーマット指定
	 */
	public function export(array $data, $format = LogicHaiponExporterFormatter::FORMAT_CSV) {
		/** @var LogicHaiponExporterFormatterInterface */
		$formatter = LogicHaiponExporterFormatter::factory($format);
		$formatter->format($data);
	}

	/**
	 * DBから取得したエクスポート用データをusort()でソートするためのアルゴリズムメソッド
	 *
	 * @access protected
	 * @param array $a 比較要素1
	 * @param array $b 比較要素2
	 * return int
	 */
    protected function _sortExportEntity($a, $b) {
        foreach(array('Deli_JournalNumber' => true, 'Deli_JournalIncDate'=> true, 'OrderSeq' => false) as $key => $as_str) {
            $result = $this->_keyComp($a, $b, $key, $as_str);
            if($result !== 0) return $result;
        }
        return 0;
    }

	/**
	 * 2つの要素を比較して大小判定を行う
	 *
	 * @access protected
	 * @param array $a 比較要素1
	 * @param array $b 比較要素2
	 * @param string $key 実際の比較に使用する、比較要素のキー
	 * @param boolean $as_string 比較値を文字列として比較する場合はtrue、それ以外はfalseを指定する
	 * @return int
	 */
    protected function _keyComp($a, $b, $key, $as_string = false) {
        if($a[$key] == $b[$key]) return 0;
        if($as_string) {
            return strcmp($a[$key], $b[$key]);
        } else {
            return $a[$key] < $b[$key] ? -1 : 1;
        }
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
	SUM.Deli_JournalIncDate,
	SUM.Deli_DeliveryMethod,
	SUM.Deli_DeliveryMethodName,
	ORD.DataStatus,
	ORD.CloseReason,
	ORD.Cnl_Status,
	ORD.CombinedClaimTargetStatus,
	ORD.OutOfAmends,
	ORD.Deli_ConfirmArrivalFlg
FROM
	T_Order ORD INNER JOIN
	T_OrderSummary SUM ON SUM.OrderSeq = ORD.OrderSeq INNER JOIN
	M_DeliveryMethod DELI ON DELI.DeliMethodId = SUM.Deli_DeliveryMethod
WHERE
	IFNULL(ORD.Deli_ConfirmArrivalFlg, 0) IN (-1, 0) AND
	ORD.Cnl_Status = 0 AND
	DELI.DeliMethodId IN (:DeliMethodIds) AND
	ORD.DataStatus > 31 AND
	IFNULL(ORD.CloseReason, 0) IN (0, 1) AND
	IFNULL(ORD.OutOfAmends, 0) = 0 AND
	DATE(SUM.Deli_JournalIncDate) <= :Deli_JournalIncDate AND
	IFNULL(ORD.CombinedClaimTargetStatus, 0) IN (0, 91, 92) AND
	DELI.PayChgCondition = 1 AND
	CHAR_LENGTH(SUM.Deli_JournalNumber) > 5

EOQ;
    }
}
