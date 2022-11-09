<?php
namespace models\Logic\Haipon;

use models\Logic\LogicHaipon;
use Coral\Base\IO\BaseIOCsvReader;
use Zend\Json\Json;
use models\Table\TableOrder;
use models\Table\TableOrderItems;
use models\Table\TablePayingAndSales;
use models\Table\TableUser;

/**
 * 配送でポン連携ロジック用インポートエンジン
 */
class LogicHaiponImporter {
    /**
     * 配ポンCSV見出し定数：Noカラム
     * @var string
     */
    const COL_NO = 'No';
    /**
     * 配ポンCSV見出し定数：Deliveryカラム
     * @var string
     */
    const COL_DELIVERY = 'Delivery';
    /**
     * 配ポンCSV見出し定数：STATUSカラム
     * @var string
     */
    const COL_STATUS = 'STATUS';
    /**
     * 配ポンCSV見出し定数：ProcFlgカラム
     * @var string
     */
    const COL_PROCFLG = 'ProcFlg';

    /**
     * 配ポンCSV見出し位置定数：Noカラム
     * @var string
     */
    const COL_POS_NO = 0;
    /**
     * 配ポンCSV見出し位置定数：Deliveryカラム
     * @var string
     */
    const COL_POS_DELIVERY = 1;
    /**
     * 配ポンCSV見出し位置定数：STATUSカラム
     * @var string
     */
    const COL_POS_STATUS = 2;
    /**
     * 配ポンCSV見出し位置定数：ProcFlgカラム
     * @var string
     */
    const COL_POS_PROCFLG = 3;

    /**
     * 注文逆引きエラー定数：該当なし
     * @var string
     */
    const INVALID_NO_ORDER = '該当注文なし';
    /**
     * 注文逆引きエラー定数：複数該当
     * @var string
     */
    const INVALID_ORDER_DUP = '複数注文該当';

    /**
     * 行バッファの既定サイズ
     * @var int
     */
    const ROW_HANDLE_BUFFER_SIZE = 250;

    /**
	 * 親ロジック
	 *
     * @access protected
     * @var LogicHaipon
     */
    protected $_logic;

    /**
     * readメソッドの処理で使用する行バッファ
     *
     * @access protected
     * @var array
     */
    protected $_handleBuffer;

    /**
     * readメソッドの処理で使用する読み取り結果
     *
     * @access protected
     * @var array
     */
    protected $_handleResults;

	/**
	 * 親の配ポン連携ロジックを指定してLogicHaiponImporterの
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
     * 指定CSVファイルを読み取って連想配列に展開する。
     * 戻り値の連想配列はキー'valid'、'invalid'とそれに対応する配列を保持し、
     * 配列の各要素は以下のキーを持つ連想配列となる。
     *   is_valid: インポート適合かを示すbool値
     *   invalid_reason: is_validがfalseの場合の事由テキスト
     *   line_number: 元ファイルの対応する行番号
     *   journal_number: 伝票番号
     *   status: 配ポンで確認した結果の状態メッセージ
     *   delived: 着荷済みかを示すbool値
     *   orders: 伝票番号に一致する、着荷確認可能な注文データの配列。各要素は以下のデータを持つ連想配列
     *     OrderSeq: 注文SEQ
     *     OrderId: 注文ID
     *     NameKj: 請求先氏名
     *     DestNameKj: 配送先氏名
     *     Deli_JournalNumber: 伝票番号
     *     Deli_DeliveryMethodName: 配送方法名称
     *   なお、is_validがtrueに設定された要素のordersは常に長さ1の配列となる
     *
     * @param string $file CSVファイルパス
     * @return array
     */
    public function parse($file) {
        // 行ハンドラで使用する配列を初期化しておく
        $this->_handleBuffer = array();
        $this->_handleResults = array();

        $reader = new BaseIOCsvReader($file, array($this, 'handleReadLine'));
        $reader->read();
        if(!empty(array_keys($this->_handleBuffer))) {
            // 未処理バッファに残がある場合は処理する
            $this->_flushHandleBuffer();
        }

        $results = array(
            'valid' => array(),
            'invalid' => array()
        );
        foreach($this->_handleResults as $row) {
            if($row['is_valid']) {
                $results['valid'][] = $row;
            } else {
                $results['invalid'][] = $row;
            }
        }

        // 行ハンドラ用配列を解放
        $this->_handleBuffer = null;
        $this->_handleResults = null;

        return $results;
    }

    /**
     * 読み取ったCSV行を処理するハンドラで、readメソッド内のBaseIOCsvReaderから呼び出される。
     * アクセス権はパブリックに設定されているが、外部から呼び出した場合の動作は保証されない
     *
     * @param array $row
     * @param int $lineNum
     * @param BaseIOCsvReader $reader
     * @return array | int
     */
    public function handleReadLine($row, $lineNum, $reader) {
        // ヘッダ行はスキップ
        if($this->_isHeaderRow($row)) return BaseIOCsvReader::COMMAND_SKIP_LINE;

        // 規定行数分処理したらバッファをフラッシュ
        $handleBufferCount = 0;
        if(!empty(array_keys($this->_handleBuffer))) {
            $handleBufferCount = count(array_keys($this->_handleBuffer));
        }
        if($handleBufferCount >= self::ROW_HANDLE_BUFFER_SIZE) {
            $this->_flushHandleBuffer();
        }

        // 伝票番号をキーに行バッファへ読み取った行を追加
        $journal_number = $row[self::COL_POS_NO];
        $this->_handleBuffer[$journal_number] = array(
            'is_valid' => false,
            'invalid_reason' => '',
            'line_number' => $lineNum + 1,
            'journal_number' => $journal_number,
            'status' => $row[self::COL_POS_STATUS],
            'delived' => $row[self::COL_POS_PROCFLG] ? true : false,
            'orders' => array()
        );

        // CSVリーダーには元データをそのまま返す
        return $row;
    }

    /**
     * インポート用データのインポートを実行する。
     *
     * @param array $data インポート用データ。parseメソッドで取得した連想配列のうち、キー'valid'のものを渡す必要がある
     * @param null | string | date $arrDate 着荷確認日付。省略時は現在日時が採用される
     * @param int | null $opId インポート作業を実行したオペレータID。省略時は-1が割り当てられる
     * @return int インポート成功件数
     */
    public function import(array $data, $arrDate = null, $opId = -1) {
        $count = 0;
        foreach($data as $row) {
            $oseq = $row['orders'][0]['OrderSeq'];  // 注文SEQ
            $arrFlg = 1;                            // 着荷確認フラグ
            $noArrReason = 0;                       // 未確認理由
            if($this->doArrivalConfirm($oseq, $opId, $arrFlg, $noArrReason, $arrDate)) {
                // 更新に成功したので件数に計上
                $count++;
            }
        }
        return $count;
    }

    /**
     * 着荷確認状態を更新する
     *
     * @param int $oseq 注文SEQ
     * @param int | null $opId 着荷確認担当のオペレータID
     * @param int | null $arrFlg 着荷確認フラグ。省略時は1
     * @param int | null $noArrReason 未着荷確認理由コード。省略時は0
     * @param null | string | date $arrCfmDate 着荷確認日付。省略時は現在日時
     */
    public function doArrivalConfirm($oseq, $opId = -1, $arrFlg = 1, $noArrReason = 0, $arrCfmDate = null) {
        $db = $this->getParentLogic()->getAdapter();
        $orders = new TableOrder($db);
        $orderItems = new TableOrderItems($db);
        $pasTable = new TablePayingAndSales($db);

        // 注文状態の確認
        $order = $orders->find($oseq)->current();
        if(!$order) return false;                               // 注文が存在しない
        if($orders->isCanceled($oseq)) return false;            // キャンセル済みは無視
        // 既に着荷確認済みで且つ状態の変更がない場合は無視
        if($order->Deli_ConfirmArrivalFlg == 1 && $order->Deli_ConfirmArrivalFlg != $arrFlg) {
            return false;
        }

        // 入力パラメータの補正
        $opId = (int)$opId;
        $arrFlg = (int)$arrFlg;
        if(!in_array($arrFlg, array(-1, 0, 1))) {
            throw new LogicHaiponException('着荷確認フラグの指定が不正です');
        }
        if($noArrReason !== null) $noArrReason = (int)$noArrReason;
        if($arrCfmDate == null) $arrCfmDate = date('Y-m-d H:i:s');
//         if($arrCfmDate instanceof Zend_Date) {
//             $arrCfmDate = $arrCfmDate->toString('yyyy-MM-dd HH:mm:ss');
//         }
        // 現在日時より未来が指定された場合は現在日時に丸める
        if($arrCfmDate > date('Y-m-d H:i:s')) $arrCfmDate = date('Y-m-d H:i:s');

        $obj = new TableUser( $db );
        $userId = $obj->getUserId( 0, $opId );

        //$this->_dump($oseq, 'BEFORE UPDATE ==================================================');
        // T_Order更新
        $orders->saveUpdate(array(
            'Deli_ConfirmArrivalFlg' => $arrFlg,
            'Deli_ConfirmArrivalDate' => $arrCfmDate,
            'UpdateId' => $userId
        ), $oseq);

        // T_OrderItems更新
        $udata = array(
            'Deli_ConfirmArrivalFlg' => $arrFlg,
            'Deli_ConfirmArrivalOpId' => $opId,
            'Deli_ConfirmArrivalDate' => $arrCfmDate,
            'UpdateId' => $userId
        );
        // 未着荷確認理由の更新はnull以外の指定があった場合のみ
        if($noArrReason !== null) $udata['Deli_ConfirmNoArrivalReason'] = $noArrReason;
        foreach($orderItems->findByOrderSeq($oseq) as $oItem) {
            $orderItems->saveUpdate($udata, $oItem->OrderItemId);
        }

        // 立替条件クリアの条件が着荷確認なら立替条件をクリアする
        if($this->_checkForClearCondition($oseq)) {
            $cad = $this->_calcCAD($arrCfmDate);
            $pasTable->clearConditionForCharge($oseq, $cad, $userId);
        }

        //$this->_dump($oseq, 'AFTER UPDATE ===================================================');
        return true;
    }

    /**
     * TablePayingAndSales::clearConditionForCharge向けに、
     * 指定日付と現在日の相対日数を算出する。
     * 戻り値は現在日を1とし、1日前→2、2日前→3...と遡るごとに増加していく。
     * ただしマイナス値は返さず、現在日よりも未来日を指定した場合は現在日として扱われ結果1となる。
     *
     * @access protected
     * @param string | date $date 算出対象の日付
     * @return int
     */
    protected function _calcCAD($date) {
        $base = date('Y-m-d');
//        if($date instanceof Zend_Date) $date = $date->toString('yyyy-MM-dd');

        // 本日またはそれより未来の場合は1確定
        if($date >= $base) return 1;

        // それ以外はstrtotimeを経由して日数算出
        return ((strtotime($base) - strtotime($date)) / 86400) + 1;
    }

    /**
     * 指定の注文の立替条件クリアの条件が着荷確認であるかを判断する
     *
     * @access protected
     * @param int $oseq 注文SEQ
     * @return boolean 指定注文が非メール便ではなく且つ保証案件の場合はtrue、それ以外はfalse
     */
    protected function _checkForClearCondition($oseq) {
        $q = <<<EOQ
SELECT
	ORD.OrderSeq,
	CASE
		WHEN DELI.PayChgCondition = 2 OR IFNULL(ORD.OutOfAmends, 0) = 1 THEN 2
		ELSE 1
	END AS PayChgCondition
FROM
	T_Order ORD INNER JOIN
	T_OrderSummary SUM ON SUM.OrderSeq = ORD.OrderSeq INNER JOIN
	M_DeliveryMethod DELI ON DELI.DeliMethodId = SUM.Deli_DeliveryMethod
WHERE
	ORD.OrderSeq = ?
EOQ;
        $db = $this->getParentLogic()->getAdapter();
        foreach($db->fetchAll($q, $oseq) as $row) {
            return $row['PayChgCondition'] == 1 ? true : false;
        }
        return false;
    }

    /**
     * CSV処理行バッファをフラッシュして読み取り結果に追加する
     *
     * @access protected
     */
    protected function _flushHandleBuffer() {
        // バッファ内の伝票番号をすべて取り出す
        $journal_numbers = array_keys($this->_handleBuffer);

        // バッファ内の伝票番号に一致する注文をすべて取得し、
        // 伝票番号をキーとしたキャッシュを構築する
        $orders = array();
        foreach($this->_findOrderByJournalNumbers($journal_numbers) as $row) {
            if(!is_array($orders[$row['Deli_JournalNumber']])) {
                $orders[$row['Deli_JournalNumber']] = array();
            }
            $orders[$row['Deli_JournalNumber']][] = $row;
        }

        // バッファ内の読み取り行データと対応する注文データをマージする
        foreach($this->_handleBuffer as $journal_number => &$row) {
            $order_list = isset($orders[$journal_number]) ?
                $orders[$journal_number] : array();
            $row['orders'] = $order_list;
            $orderlistCount = 0;
            if(!empty($order_list)) {
                $orderlistCount = count($order_list);
            }
            $row['is_valid'] = $row['delived'] && $orderlistCount == 1;
            if(!$row['is_valid']) {
                if(empty($order_list)) {
                    $row['invalid_reason'] = self::INVALID_NO_ORDER;
                } else
                if($orderlistCount > 1) {
                    $row['invalid_reason'] = self::INVALID_ORDER_DUP;
                }
            }
        }

        // 読取結果にマージしてバッファをクリア
        $this->_handleResults = array_merge($this->_handleResults, array_values($this->_handleBuffer));
        $this->_handleBuffer = array();
    }

    /**
     * 指定のCSV行データがヘッダ行であるかを判断する
     *
     * @access protected
     * @param array $row 検査対象の行データ
     * @return boolean
     */
    protected function _isHeaderRow($row) {
        $header_def = array(
            self::COL_NO,
            self::COL_DELIVERY,
            self::COL_STATUS,
            self::COL_PROCFLG
        );
        return Json::encode($row) == Json::encode($header_def);
    }

    /**
     * 指定の伝票番号に一致する注文データを検索する
     *
     * @access protected
     * @param array $journal_numbers 伝票番号が格納された配列
     * @return array
     */
    protected function _findOrderByJournalNumbers(array $journal_numbers) {
        $q = <<<EOQ
SELECT
	ORD.OrderSeq,
	ORD.OrderId,
	SUM.NameKj,
	SUM.DestNameKj,
	SUM.Deli_JournalNumber,
	SUM.Deli_DeliveryMethodName
FROM
	T_OrderSummary SUM INNER JOIN
	T_Order ORD ON ORD.OrderSeq = SUM.OrderSeq INNER JOIN
	T_PayingAndSales PAS ON PAS.OrderSeq = ORD.OrderSeq
WHERE
	IFNULL(ORD.Deli_ConfirmArrivalFlg, 0) IN (-1, 0) AND
	ORD.Cnl_Status = 0 AND
    (
        ORD.DataStatus IN (41, 51) OR
        (
            ORD.DataStatus = 91 AND
            IFNULL(ORD.CloseReason, 0) IN (0, 1)
        )
    ) AND
	IFNULL(ORD.OutOfAmends, 0) = 0 AND
	IFNULL(ORD.CombinedClaimTargetStatus, 0) IN (0, 91, 92) AND
	SUM.Deli_JournalNumber IN (:Deli_JournalNumbers)
ORDER BY
    ORD.OrderSeq
EOQ;
        /** @var Adapter */
        $db = $this->getParentLogic()->getAdapter();
        // IN に使用できるように要素をクォートしておく
//         foreach($journal_numbers as &$jn) {
//             $jn = is_numeric($jn) ? sprintf("'%s'", $jn) : $db->quoteInto('?', $jn);
//         }
//        return $db->fetchAll(sprintf($q, join(',', $journal_numbers)));
        $stm = $db->query( $q );
        $prm = array(
            ':Deli_JournalNumbers' => join(',', $journal_numbers),
        );
        return ResultInterfaceToArray( $stm->execute( $prm ) );
    }

    /**
     * デバッグ用のダンプ出力を実行する。
     * ダンプの内容は、引数で渡された注文SEQを持つT_OrderとT_OrderItems、およびT_PayingAndSalesの内容。
     *
     * @access protected
     * @param int $oseq 注文SEQ
     * @param null | string $msg ダンプ出力前にログに出力するメッセージ
     */
    protected function _dump($oseq, $msg = null) {
        $db = $this->getParentLogic()->getAdapter();
        $orders = new TableOrder($db);
        $orderItems = new TableOrderItems($db);
        $pasTable = new TablePayingAndSales($db);

        $logger = Application::getInstance()->logger;
        if($msg != null) $logger->debug(sprintf('[LogicHaiponImporter::_dump] %s', $msg));
        $logger->debug(sprintf("[LogicHaiponImporter::_dump]\nT_Order:\n%s", var_export($orders->find($oseq)->toArray(), true)));
        $logger->debug(sprintf("\nT_OrderItems:\n%s", var_export($orderItems->findByOrderSeq($oseq)->toArray(), true)));
        $logger->debug(sprintf("\nT_PayingAndSales:\n%s", var_export($pasTable->findPayingAndSales(array('OrderSeq' => $oseq))->toArray(), true)));
    }
}
