<?php
namespace models\Logic\RwarvlData;

use Zend\Json\Json;
use models\Logic\LogicRwarvlData;
use models\Table\TableUser;
use Coral\Base\IO\BaseIOUtility;
use Coral\Base\IO\BaseIOCsvReader;

/**
 * 配送でポン連携ロジック用インポートエンジン
 */
class LogicRwarvlDataImporter {
    /**
     * 着荷確認データCSV見出し位置定数：OrderSeqカラム
     * @var string
     */
    const COL_POS_ORDERSEQ = 0;
    /**
     * 着荷確認データCSV見出し位置定数：OrderIdカラム
     * @var string
     */
    const COL_POS_ORDERID = 1;
    /**
     * 着荷確認データCSV見出し位置定数：Deliveryカラム
     * @var string
     */
    const COL_POS_DELIVERY = 2;
    /**
     * 着荷確認データCSV見出し位置定数：DeliveryNameカラム
     * @var string
     */
    const COL_POS_DELIVERYNAME = 3;
    /**
     * 着荷確認データCSV見出し位置定数：StatusCodeカラム
     * @var string
     */
    const COL_POS_STATUSCODE = 4;
    /**
     * 着荷確認データCSV見出し位置定数：Statusカラム
     * @var string
     */
    const COL_POS_STATUS = 5;

    /**
     * 着荷確認データCSV見出し位置定数：OrderSeqカラム
     * @var string
     */
    const COL_ORDERSEQ = "注文Seq";
    /**
     * 着荷確認データCSV見出し位置定数：OrderIdカラム
     * @var string
     */
    const COL_ORDERID = "注文ID";
    /**
     * 着荷確認データCSV見出し位置定数：Deliveryカラム
     * @var string
     */
    const COL_DELIVERY = "伝票番号";
    /**
     * 着荷確認データCSV見出し位置定数：DeliveryNameカラム
     * @var string
     */
    const COL_DELIVERYNAME = "配送業者";
    /**
     * 着荷確認データCSV見出し位置定数：StatusCodeカラム
     * @var string
     */
    const COL_STATUSCODE = "ステータスコード";
    /**
     * 着荷確認データCSV見出し位置定数：Statusカラム
     * @var string
     */
    const COL_STATUS = "ステータス";


    /**
     * 着荷確認ステータスコード:未登録
     * @var string
     */
    const DELIVERY_STATUS_NOREGIST = 0;
    /**
     * 着荷確認ステータスコード:登録確認
     * @var string
     */
    const DELIVERY_STATUS_REGIST = 1;
    /**
     * 着荷確認ステータスコード:着荷
     * @var string
     */
    const DELIVERY_STATUS_ARRIVAL = 2;
    /**
     * 着荷確認ステータスコード:エラー
     * @var string
     */
    const DELIVERY_STATUS_ERROR = 3;
    /**
     * 着荷確認ステータスコード:返品
     * @var string
     */
    const DELIVERY_STATUS_REFUSAL = 4;
    /**
     * 着荷確認ステータスコード:未登録
     * @var string
     */
    const DELIVERY_NOREGIST = "未登録";
    /**
     * 着荷確認ステータスコード:登録確認
     * @var string
     */
    const DELIVERY_REGIST = "登録確認";
    /**
     * 着荷確認ステータスコード:着荷
     * @var string
     */
    const DELIVERY_ARRIVAL = "着荷";
    /**
     * 着荷確認ステータスコード:エラー
     * @var string
     */
    const DELIVERY_ERROR = "エラー";
    /**
     * 着荷確認ステータスコード:返品
     * @var string
     */
    const DELIVERY_REFUSAL = "返品";

    /**
     * 注文逆引きエラー定数：該当なし
     * @var string
     */
    const INVALID_NO_ORDER = '該当注文なし';
    /**
     * 注文逆引きエラー定数：注文キャンセル済み
     * @var string
     */
    const INVALID_ORDER_CANCELLED = 'キャンセル済';
    /**
     * 注文逆引きエラー定数：着荷確認対象外
     * @var string
     */
    const INVALID_ORDER_OUTOFDELIVERY = '着荷確認対象外';

    /**
     * 行バッファの既定サイズ
     * @var int
     */
    const ROW_HANDLE_BUFFER_SIZE = 250;

    /**
	 * 親ロジック
	 *
     * @access protected
     * @var LogicRwarvlData
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
	 * 親の着荷確認データ連携ロジックを指定してLogicRwarvlDataImporterの
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
     * 指定CSVファイルを読み取って連想配列に展開する。
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

        // 注文番号をキーに行バッファへ読み取った行を追加
        $orderseq = $row[self::COL_POS_ORDERSEQ];
        $orderid = $row[self::COL_POS_ORDERID];

        // ステータスコードの読み替え
        $statuscode = '';
        $is_valid = false;
        switch($row[self::COL_POS_STATUSCODE]) {
            case self::DELIVERY_STATUS_NOREGIST:
                $statuscode = self::DELIVERY_NOREGIST;
                break;
            case self::DELIVERY_STATUS_REGIST:
                $statuscode = self::DELIVERY_REGIST;
                break;
            case self::DELIVERY_STATUS_ARRIVAL:
                $statuscode = self::DELIVERY_ARRIVAL;
                $is_valid = true;
                break;
            case self::DELIVERY_STATUS_ERROR:
                $statuscode = self::DELIVERY_ERROR;
                break;
            case self::DELIVERY_STATUS_REFUSAL:
                $statuscode = self::DELIVERY_REFUSAL;
                break;
        }

        $this->_handleBuffer[$lineNum + 1] = array(
            'orderseqs' => $orderseq,
            'orderid' => $orderid,
            'is_valid' => $is_valid,
            'invalid_reason' => '',
            'line_number' => $lineNum + 1,
            'statuscode' => $statuscode,
            'status' => $row[self::COL_POS_STATUS],
            'orders' => array()
        );

        // CSVリーダーには元データをそのまま返す
        return $row;
    }

    /**
     * インポート用データのインポートを実行する。
     *
     * @param array $data インポート用データ。parseメソッドで取得した連想配列のうち、キー'valid'のものを渡す必要がある
     * @param null | string $arrDate 着荷確認日付。'yyyy-MM-dd'書式で通知。省略時は現在日時が採用される
     * @param int | null $opId インポート作業を実行したオペレータID。省略時は-1が割り当てられる
     * @return int インポート成功件数
     */
    public function import(array $data, $arrDate = null, $opId = -1) {
        $count = 0;
        foreach($data as $row) {
            $oseq = $row;  // 注文SEQ
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
     * @param null | string $arrCfmDate 着荷確認日付。'yyyy-MM-dd'書式で通知。省略時は現在日時
     */
    public function doArrivalConfirm($oseq, $opId = -1, $arrFlg = 1, $noArrReason = 0, $arrCfmDate = null) {

        $db = $this->getParentLogic()->getAdapter();
        $orders = new \models\Table\TableOrder($db);
        $orderItems = new \models\Table\TableOrderItems($db);
        $pasTable = new \models\Table\TablePayingAndSales($db);
        $oemTable = new \models\Table\TableOem($db);
        $mdlapas = new \models\Table\ATablePayingAndSales($db);
        $os = new \models\Table\TableOrderSummary($db);
        $ca = new \models\Table\TableCombinedArrival($db);

        // 注文状態の確認
            $order = $orders->find($oseq)->current();
            if(!$order) return false;                               // 注文が存在しない
            if($orders->isCanceled($oseq)) return false;            // キャンセル済みは無視
            if(!$this->_findExemptByOrderSeq(array($oseq))) return false;  // 着荷確認対象外
            // 既に着荷確認済みで且つ状態の変更がない場合は無視
            if($order['Deli_ConfirmArrivalFlg'] == 1 && $order['Deli_ConfirmArrivalFlg'] != $arrFlg) {
                return false;
            }

            // 注文に紐づくOEMの情報取得
            $oemDatas = $oemTable->find($order['OemId'])->current();

            // 入力パラメータの補正
            $opId = (int)$opId;
            $arrFlg = (int)$arrFlg;
            if(!in_array($arrFlg, array(-1, 0, 1))) {
                throw new LogicRwarvlDataException('着荷確認フラグの指定が不正です');
            }
            if($noArrReason !== null) $noArrReason = (int)$noArrReason;
            if($arrCfmDate == null) $arrCfmDate = date('Y-m-d H:i:s');
            // 現在日時より未来が指定された場合は現在日時に丸める
            if($arrCfmDate > date('Y-m-d H:i:s')) $arrCfmDate = date('Y-m-d H:i:s');
            if( $oemDatas['TimemachineNgFlg'] == 1)$arrCfmDate = date('Y-m-d H:i:s');

        $db->getDriver()->getConnection()->beginTransaction();

        try {
            // 20181204 Add
            // 要取りまとめ注文(1,2)、取りまとめ指示中注文(11,12)の場合、取りまとめ着荷確認テーブルに登録を行う
            // 注文の備考に取込実行の文言を追加
            if(!empty($order['CombinedClaimTargetStatus']) &&
              ($order['CombinedClaimTargetStatus'] == 1 || $order['CombinedClaimTargetStatus'] == 2 ||
              $order['CombinedClaimTargetStatus'] == 11 || $order['CombinedClaimTargetStatus'] == 12)){
                // 伝票番号の取得
                $ordersummary = $os->getSummary($oseq)->current();
                // 取りまとめ着荷確認テーブルに登録
                $ca->saveNew(array(
                    'OrderSeq' => $oseq,
                    'DeliJournalNumber' => $ordersummary['Deli_JournalNumber'],
                ));
                // 注文更新
                $uOrder['Incre_Note'] = date('Y-m-d') . " 取りまとめ前に着荷確認データの取込が実行されました。\n----\n" . $order['Incre_Note'];
                $orders->saveUpdateParts($uOrder, $oseq);
            }
            // 要取りまとめ注文(1,2)、取りまとめ指示中注文(11,12)以外の場合、既存処理
            else {
                $obj = new TableUser( $db );
                $userId = $obj->getUserId( 0, $opId );

                // T_Order更新
                $orders->saveUpdate(array(
                    'Deli_ConfirmArrivalFlg' => $arrFlg,
                    'Deli_ConfirmArrivalDate' => $arrCfmDate,
                    'UpdateId' => $userId,
                ), $oseq);

                // T_OrderItems更新
                $udata = array(
                    'Deli_ConfirmArrivalFlg' => $arrFlg,
                    'Deli_ConfirmArrivalOpId' => $opId,
                    'Deli_ConfirmArrivalDate' => $arrCfmDate,
                    'UpdateId' => $userId,
                );

                // 未着荷確認理由の更新はnull以外の指定があった場合のみ
                if($noArrReason !== null) $udata['Deli_ConfirmNoArrivalReason'] = $noArrReason;
                    $ri = $orderItems->findByOrderSeq($oseq);
                    foreach($ri as $oItem) {
                        $orderItems->saveUpdate($udata, $oItem['OrderItemId']);
                }

                // 既に[立替条件クリアフラグ]が[1：条件をクリアしている]か？の取得
                $isAlreadyClearCondition = $pasTable->IsAlreadyClearCondition($oseq);

                $ret_checkForClearCondition = $this->_checkForClearCondition($oseq);

                // 立替条件クリアの条件が着荷確認なら立替条件をクリアする
                if($this->_checkForClearCondition($oseq)) {
                    $cad = $this->_calcCAD(date('Y-m-d', strtotime($arrCfmDate)));
                    $pasTable->clearConditionForCharge($oseq, $cad, $opId);
                }

                // [着荷入力日時]のセット
                $row_pas = $db->query(" SELECT Seq, ClearConditionDate FROM T_PayingAndSales WHERE OrderSeq = :OrderSeq "
                    )->execute(array(':OrderSeq' => $oseq))->current();
                $mdlapas->saveUpdate(array('Deli_ConfirmArrivalInputDate' => date('Y-m-d H:i:s')), $row_pas['Seq']);

                if ($ret_checkForClearCondition && !$isAlreadyClearCondition) {
                    // 着荷により立替条件クリアフラグが１化されるとき => '1:着荷'として更新
                    $mdlapas->saveUpdate(array('ATUriType' => 1, 'ATUriDay' => date('Ymd', strtotime($row_pas['ClearConditionDate']))), $row_pas['Seq']);
                }

                // 注文履歴へ登録
                $history = new \Coral\Coral\History\CoralHistoryOrder($db);
                $history->InsOrderHistory($oseq, 51, $userId);
            }
            $db->getDriver()->getConnection()->commit();
        }
        catch(\Exception $e) {
            $db->getDriver()->getConnection()->rollBack();
            return false;
        }

        return true;
    }

    /**
     * TablePayingAndSales::clearConditionForCharge向けに、
     * 指定日付と現在日の相対日数を算出する。
     * 戻り値は現在日を1とし、1日前→2、2日前→3...と遡るごとに増加していく。
     * ただしマイナス値は返さず、現在日よりも未来日を指定した場合は現在日として扱われ結果1となる。
     *
     * @access protected
     * @param string $date 算出対象の日付。'yyyy-MM-dd'書式で通知。
     * @return int
     */
    protected function _calcCAD($date) {
        $base = date('Y-m-d');

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
	ORD.OrderSeq = :OrderSeq
EOQ;
        $db = $this->getParentLogic()->getAdapter();
        $ri = $db->query($q)->execute(array(':OrderSeq' => $oseq));
        foreach($ri as $row) {
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
        // バッファ内の注文Seqをすべて取り出す
        $orderseqs = array();
        foreach($this->_handleBuffer as $data) {
            array_push($orderseqs, $data['orderseqs']);
        }
        // 注文Seqから注文情報を取得する
        $orderlist = $this->_findOrderByOrderSeqs($orderseqs);

        // 注文情報の詰め直し
        foreach($this->_handleBuffer as $i => &$row) {
            foreach($orderlist as $order) {
                if($order['OrderSeq'] == $row['orderseqs']) {
                    $row['orders'] = $order;
                    break;
                }
                else {
                    $row['orders'] = array();
                }
            }

            if(empty($row['orders'])) {
                $row['is_valid'] = false;
                $row['invalid_reason'] = self::INVALID_NO_ORDER;
            }
        }

        // キャンセル状態のチェック
        $orderlist = $this->_findCancelldByOrderSeqs($orderseqs);
        foreach($this->_handleBuffer as $i => &$row) {
            foreach($orderlist as $order) {
                if($order['OrderSeq'] == $row['orderseqs'] && $order['Cnl_Status'] <> 0) {
                    $row['is_valid'] = false;
                    $row['invalid_reason'] = self::INVALID_ORDER_CANCELLED;
                    break;
                }
            }
        }

        // 着荷確認対象外のチェック
        $orderlist = $this->_findExemptByOrderSeq($orderseqs);
        foreach($this->_handleBuffer as $i => &$row) {
            if($row['is_valid']) {
            // 既にinvalidの場合にはチェックしない
                $isexit = false;
                foreach($orderlist as $order) {
                    if($order['OrderSeq'] == $row['orderseqs']) {
                        $isexit = true;
                        break;
                    }
                }
                if(!$isexit) {
                // 着荷確認対象外
                    $row['is_valid'] = false;
                    $row['invalid_reason'] = self::INVALID_ORDER_OUTOFDELIVERY;
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
            self::COL_ORDERSEQ,
            self::COL_ORDERID,
            self::COL_DELIVERY,
            self::COL_DELIVERYNAME,
            self::COL_STATUSCODE,
            self::COL_STATUS
        );
        return Json::encode($row) == Json::encode($header_def);
    }

    /**
     * 指定の注文Seqsに一致する注文データを検索する
     *
     * @access protected
     * @param array $orderseqs 注文Seqが格納された配列
     */
    protected function _findOrderByOrderSeqs($orderseqs) {
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
	T_Order ORD ON ORD.OrderSeq = SUM.OrderSeq
WHERE
        ORD.OrderSeq IN (%s)
EOQ;
        $db = $this->getParentLogic()->getAdapter();
        // IN に使用できるように要素をクォートしておく
        foreach($orderseqs as &$orderseq) {
            if (!is_numeric($orderseq)) {
                $orderseq = -1;
            }
        }
        $ri = $db->query(sprintf($q, join(',', $orderseqs)))->execute(null);
        return ResultInterfaceToArray($ri);
    }

    /**
     * 指定の注文Seqsに関するキャンセル状態をチェック
     *
     * @access protected
     * @param array $orderseqs 注文Seqが格納された配列
     */
    protected function _findCancelldByOrderSeqs($orderseqs) {
        $q = <<<EOQ
SELECT
	ORD.OrderSeq,
	ORD.Cnl_Status
FROM
	T_Order ORD
WHERE
        ORD.OrderSeq IN (%s)
EOQ;
        $db = $this->getParentLogic()->getAdapter();
        // IN に使用できるように要素をクォートしておく
        foreach($orderseqs as &$orderseq) {
            if (!is_numeric($orderseq)) {
                $orderseq = -1;
            }
        }
        $ri = $db->query(sprintf($q, join(',', $orderseqs)))->execute(null);
        return ResultInterfaceToArray($ri);
    }

    /**
     * 指定の注文Seqに一致する注文データを検索する
     *
     * @access protected
     * @param array $orderseqs 注文Seqが格納された配列
     * @return array
     */
    protected function _findExemptByOrderSeq(array $orderseqs) {
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
	T_PayingAndSales PAS ON PAS.OrderSeq = ORD.OrderSeq INNER JOIN
    M_DeliveryMethod DELI ON DELI.DeliMethodId = SUM.Deli_DeliveryMethod INNER JOIN									
    T_Site SIT ON SIT.SiteId = ORD.SiteId INNER JOIN									
    AT_Order AO ON AO.OrderSeq = ORD.OrderSeq LEFT JOIN									
    T_ClaimControl CC ON CC.OrderSeq = ORD.OrderSeq
WHERE
	IFNULL(ORD.Deli_ConfirmArrivalFlg, 0) IN (-1, 0) AND
   (
        ORD.DataStatus IN (41, 51, 61) OR
        (
            ORD.DataStatus = 91 AND
            IFNULL(ORD.CloseReason, 0) IN (0, 1)
        )
    ) AND
        IFNULL(ORD.OutOfAmends, 0) = 0 AND
        IFNULL(ORD.CombinedClaimTargetStatus, 0) IN (0, 1, 2, 11, 12, 91, 92) AND
        DELI.PayChgCondition = 1 AND
        ( AO.CreditTransferRequestFlg <> '0' OR (										
        IFNULL(AO.ExtraPayType, 0) <> '1' AND										
        NOT ( SIT.PaymentAfterArrivalFlg = '1' AND										
            DATE_ADD(CC.F_ClaimDate, INTERVAL (SELECT MAX(sbps.NumUseDay) AS MaxNumUseDay FROM T_SiteSbpsPayment sbps WHERE sbps.SiteId = ORD.SiteId AND sbps.ValidFlg = 1) DAY) >= CURDATE() AND										
            (SELECT COUNT(1) FROM T_ReceiptControl RC WHERE RC.OrderSeq = ORD.OrderSeq) = 0))) AND										
	    SUM.OrderSeq IN (%s)
ORDER BY
    ORD.OrderSeq
EOQ;
        /** @var Adapter */
        $db = $this->getParentLogic()->getAdapter();
        // IN に使用できるように要素をクォートしておく
        foreach($orderseqs as &$orderseq) {
            if (!is_numeric($orderseq)) {
                $orderseq = -1;
            }
        }
        $ri = $db->query(sprintf($q, join(',', $orderseqs)))->execute(null);
        return ResultInterfaceToArray($ri);
    }
}
