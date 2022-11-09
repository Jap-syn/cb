<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Json\Json;
use models\Logic\LogicOemClaimAccount;
use models\Logic\LogicYuchoUtility;
use models\Logic\BarcodeData\LogicBarcodeDataCvs;

/**
 * SMBC決済ステーションとの連携処理ログを管理する
 * T_SmbcRelationLogテーブルへのアダプタ
 */
class TableSmbcRelationLog
{
    // ステータス定数：未送信
    const STATUS_UNSENT             = 0;

    // ステータス定数：送信済み
    const STATUS_SENT               = 1;

    // ステータス定数：受信済み
    const STATUS_RECEIVED           = 2;

    // ステータス定数：受信失敗
    const STATUS_RECEIVE_FAILURE    = 9;

	// 対象機能定数：請求情報登録
	const TARGET_FUNC_REGISTER = 1;

	// 対象機能定数：請求取消
	const TARGET_FUNC_CANCEL = 9;

	/**
	 * 指定の対象機能コードが定義済みであるかを判断する
	 *
	 * @static
	 * @param int $funcCode 対象機能を指定するコード値
	 * @return boolean
	 */
	public static function isDefinedTargetFuncCode($funcCode)
	{
	    return in_array((int)$funcCode, array(self::TARGET_FUNC_REGISTER, self::TARGET_FUNC_CANCEL));
	}

	protected $_name = 'T_SmbcRelationLog';
	protected $_primary = array('Seq');
	protected $_adapter = null;

	/**
	 * コンストラクタ
	 *
	 * @param Adapter $adapter アダプタ
	 */
	public function __construct(Adapter $adapter)
	{
	    $this->_adapter = $adapter;
	}

	/**
	 * 決済ステーション送受信ログデータを取得する
	 *
	 * @param int $seq 連携ログSEQ
	 * @return ResultInterface
	 */
	public function find($seq)
	{
	    $sql  = " SELECT * FROM T_SmbcRelationLog WHERE Seq = :Seq ";

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':Seq' => $seq,
	    );

	    return $stm->execute($prm);
	}

    /**
	 * 新しいレコードをインサートする。
	 *
	 * @param int $claimAccountSeq 関連付けるOEM請求口座SEQ
	 * @param int $targetFunc 対象機能コード
	 *                        TableSmbcRelationLog::TARGET_FUNC_REGISTER または
	 *                        TableSmbcRelationLog::TARGET_FUNC_CANCEL のどちらかを指定
	 * @param array $data インサートする連想配列
	 * @return プライマリキーのバリュー
	 */
	public function saveNew($claimAccountSeq, $targetFunc, array $data = array())
	{
        // 請求口座SEQの正当性をチェック
        $accountRow = $this->findClaimAcountInfo($claimAccountSeq);
        if(!$accountRow)
        {
            throw new \Exception('invalid claim-account-seq specified');
        }
        $claimAccountSeq = $data['ClaimAccountSeq'] = $accountRow['ClaimAccountSeq'];

        // 対象機能指定の正当性をチェック
        if(!self::isDefinedTargetFuncCode($targetFunc))
        {
            throw new \Exception('invalid target-function specified');
        }

        $data = array_merge($data, array(
                'TargetFunction' => $targetFunc,
                'OrderSeq' => $accountRow['OrderSeq'],
                'SentTime' => date('Y-m-d H:i:s'),  // 送信日時（仮）
                'ErrorReason' => null,              // 受信エラー情報
                'Status' => self::STATUS_UNSENT     // 送信ステータス = 未送信
        ));

        return $this->_saveNew($data);
	}

    /**
     * 決済ステーションへ請求情報を送信した状態に更新する
     *
     * @param array $sendData 決済ステーションへ送信した請求情報データ（連想配列）
     * @param int $seq 更新するSEQ
     */
    public function updateBySend(array $sendData, $seq)
    {
        $this->saveUpdate(array(
            'SentTime' => date('Y-m-d H:i:s'),
            'SentRawData' => Json::encode($sendData),
            'ErrorReason' => null,
            'Status' => self::STATUS_SENT               // ステータスを「送信済み」に
        ), $seq);
    }

    /**
     * 決済ステーションから請求情報登録の応答を正常受信した状態に更新する
     *
     * @param array $rcvData 決済ステーションから受信した応答データを展開した連想配列
     * @param int $seq 更新するSEQ
     */
    public function updateByReceive(array $rcvData, $seq)
    {
        $acceptTime = $this->parseAcceptDateTime(nvl($rcvData['kessai_date']), nvl($rcvData['kessai_time']));
		$data = array(
            'ReceivedTime' => date('Y-m-d H:i:s'),
            'ReceivedRawData' => Json::encode($rcvData),
            'ErrorReason' => null,
			'AcceptNumber' => isset($rcvData['kessai_no']) ? $rcvData['kessai_no'] : null,
			'ResponseCode' => isset($rcvData['rescd']) ? $rcvData['rescd'] : null,
			'ResponseMessage' => isset($rcvData['res']) ? $rcvData['res'] : null,
            'Status' => self::STATUS_RECEIVED           // ステータスを「受信済み」に
		);
		if($acceptTime) {
			$data['AcceptTime'] = $acceptTime;
		}
        $this->saveUpdate($data, $seq);
    }

    /**
     * 決済ステーションへの請求情報登録に失敗した状態に更新する
     *
     * @param string $errorInfo エラー情報
     * @param int $seq 更新するSEQ
     * @param null | array $rcvData 受信データ
     */
    public function updateByReceiveFailure($errorInfo, $seq, $rcvData = null)
    {
        if(!is_array($rcvData)) $rcvData = array();
		$acceptTime = $this->parseAcceptDateTime(nvl($rcvData['kessai_date']), nvl($rcvData['kessai_time']));
		$data = array(
            'ReceivedTime' => date('Y-m-d H:i:s'),
            'ReceivedRawData' => Json::encode($rcvData),
            'ErrorReason' => nvl($errorInfo, '不明なエラー'),
			'AcceptNumber' => isset($rcvData['kessai_no']) ? $rcvData['kessai_no'] : null,
			'ResponseCode' => isset($rcvData['rescd']) ? $rcvData['rescd'] : null,
			'ResponseMessage' => isset($rcvData['res']) ? $rcvData['res'] : null,
            'Status' => self::STATUS_RECEIVE_FAILURE    // ステータスを「受信失敗」に
		);
		if($acceptTime) {
			$data['AcceptTime'] = $acceptTime;
		}
        $this->saveUpdate($data, $seq);
    }

	/**
	 * 日付文字列、時刻文字列を整形済みの日付時刻文字列として展開する。
	 * 要求フォーマットはyyyyMMdd形式の日付文字列とHHmmss形式の時刻文字列で、
	 * 日付時刻として解釈できない場合はnullを返す。
	 *
	 * @access protected
	 * @param string $acceptDate yyyyMMdd形式の日付文字列
	 * @param string $acceptTime HHmmss形式の時刻文字列
	 * @return string | null パラメータから展開されたyyyy-MM-dd HH:mm:ss形式の日付時刻文字列またはnull
	 */
	protected function parseAcceptDateTime($acceptDate, $acceptTime)
	{
	    $challenge = preg_replace(
								  '/^(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})$/',
								  '\1-\2-\3 \4:\5:\6',
								  $acceptDate.$acceptTime);
		$filtered = date('Y-m-d H:i:s', strtotime($challenge));
		return $challenge == $filtered ? $filtered : null;
	}

    /**
     * 指定のOEM請求口座情報に関連付けられているレコードを検索する
     *
     * @param int $claimAccountSeq OEM請求口座情報SEQ
     * @param null | int $targetFunc 対象機能。省略時はすべての機能のログが対象となる
     * @return ResultInterface
     */
    public function findByClaimAccountSeq($claimAccountSeq, $targetFunc = null)
    {
        if(!self::isDefinedTargetFuncCode($targetFunc)) $targetFunc = null;
        $claimAccountSeq = (int)$claimAccountSeq;

        $sql = " SELECT * FROM T_SmbcRelationLog WHERE ClaimAccountSeq = :ClaimAccountSeq ";
        $prm = array(':ClaimAccountSeq' => $claimAccountSeq);
        if($targetFunc !== null) {
            $sql .= " AND TargetFunction = :TargetFunction ";
            $prm = array_merge($prm, array(':TargetFunction' => $targetFunc));
        }

        return $this->_adapter->query($sql)->execute($prm);
    }

	/**
	 * 指定SEQを持つOEM請求口座データを取得する
	 *
	 * @param int $claimAccountSeq OEM請求口座SEQ
	 * @return array | null
	 */
	public function findClaimAcountInfo($claimAccountSeq)
	{
        $table = new \models\Table\TableOemClaimAccountInfo($this->_adapter);
        return $table->find((int)$claimAccountSeq)->current();
	}

	/**
	 * 決済受付番号で送受信ログを検索する
	 *
	 * @param string $accNumber 決済ステーションから払い出された決済受付番号
	 * @param null | int $targetFunc 対象機能。省略時はすべての機能のログが対象となる
	 * @return ResultInterface
	 */
	public function findByAcceptNumber($accNumber, $targetFunc = null)
	{
        if(!self::isDefinedTargetFuncCode($targetFunc)) $targetFunc = null;

        $sql = " SELECT * FROM T_SmbcRelationLog WHERE AcceptNumber = :AcceptNumber ";
        $prm = array(':AcceptNumber' => $accNumber);
        if($targetFunc !== null) {
            $sql .= " AND TargetFunction = :TargetFunction ";
            $prm = array_merge($prm, array(':TargetFunction' => $targetFunc));
        }

        return $this->_adapter->query($sql)->execute($prm);
	}

	/**
	 * 指定決済ステーション連携ログの送信データを展開する
	 *
	 * @param int $seq 決済ステーション連携ログSEQ。
	 *                 指定のログが$targetFuncの指定と一致しない場合は同一の請求口座SEQのログの中から
	 *                 指定機能のものに自動的に振り替えられる
	 * @param int | null $targetFunc 対象機能。省略時は請求情報登録
	 * @return array
	 */
	public function extractSentData($seq, $targetFunc = null)
	{
	    return $this->_extractRawData($seq, $targetFunc, 'sent');
	}

	/**
	 * 指定決済ステーション連携ログの受信データを展開する
	 *
	 * @param int $seq 決済ステーション連携ログSEQ。
	 *                 指定のログが$targetFuncの指定と一致しない場合は同一の請求口座SEQのログの中から
	 *                 指定機能のものに自動的に振り替えられる
	 * @param int | null $targetFunc 対象機能。省略時は請求情報登録
	 * @return array
	 */
	public function extractReceivedData($seq, $targetFunc = null)
	{
	    return $this->_extractRawData($seq, $targetFunc, 'received');
	}

	/**
	 * 指定決済ステーション連携ログの指定生データを展開する
	 *
	 * @access protected
	 * @param int $seq 決済ステーション連携ログSEQ。
	 *                 指定のログが$targetFuncの指定と一致しない場合は同一の請求口座SEQのログの中から
	 *                 指定機能のものに自動的に振り替えられる
	 * @param int | null $targetFunc 対象機能。省略時は請求情報登録
	 * @param string $mode 'sent' or 'received'
	 * @return array
	 */
	protected function _extractRawData($seq, $targetFunc, $mode)
	{
	    if(!self::isDefinedTargetFuncCode($targetFunc))
		{
			$targetFunc = self::TARGET_FUNC_REGISTER;
		}

		if(!in_array($mode, array('sent', 'received')))
		{
			$mode = 'received';
		}

		$row = $this->find($seq)->current();
		if($row['TargetFunction'] == $targetFunc)
		{
			if($mode == 'sent')
			{
				return $row['Status'] > self::STATUS_UNSENT ?
					Json::decode($row['SentRawData'], Json::TYPE_ARRAY) : null;
			}
			else {
				return $row['Status'] == self::STATUS_RECEIVED ?
					Json::decode($row['ReceivedRawData'], Json::TYPE_ARRAY) : null;
			}
		}
		else {
//zzz ↓以下の様に移送したが、これでは第一引数がSeqでなく、上手くいかないハズだが？(20150608_1950)
// 			foreach($this->findByClaimAccountSeq($row->ClaimAccountSeq, $targetFunc) as $subRow)
// 			{
// 				return $this->_extractRawData($subRow, $mode);
// 			}
            $row = $this->findByClaimAccountSeq($row['ClaimAccountSeq'], $targetFunc)->current();
            return $this->_extractRawData($row, $mode);
		}
		return null;
	}

	/**
	 * 指定連携ログの受信データから、払い出された銀行口座情報を展開する
	 *
	 * @param int $seq 決済ステーション連携ログSEQ
	 * @return array
	 */
	public function extractBankAccountData($seq)
	{
	    $rcvData = $this->extractReceivedData($seq, self::TARGET_FUNC_REGISTER);
		if($rcvData == null)
		{
			return null;
		}

		$dep_map = array('1' => 0, '2' => 1);
		return array(
			'Bk_BankCode' => $rcvData['bank_cd'],
			'Bk_BranchCode' => $rcvData['branch_cd'],
			'Bk_BankName' => $rcvData['bank_name'],
			'Bk_BranchName' => $rcvData['branch_name'],
			'Bk_DepositClass' => nvl($dep_map[$rcvData['kouza_shubetsu']], 0),
			'Bk_AccountNumber' => $rcvData['kouza_no'],
			'Bk_AccountHolder' => $rcvData['kouza_name'],
			'Bk_AccountHolderKn' => $rcvData['kouza_name']
		);
	}

	/**
	 * 指定請求口座に関連付けられた決済ステーション連携ログの受信データから
	 * 払い出された銀行口座情報を展開する
	 *
	 * @param int $claimAccountSeq 請求口座SEQ
	 * @return array | null
	 */
	public function extractBankAccountDataByClaimAccount($claimAccountSeq)
	{
        $row = $this->findByClaimAccountSeq($claimAccountSeq, self::TARGET_FUNC_REGISTER)->current();
        return ($row) ? $this->extractBankAccountData($row['Seq']) : null;
	}

	/**
	 * 指定連携ログの送受信データから、対応するゆうちょ口座情報を展開する
	 *
	 * @param int $seq 決済ステーション連携ログSEQ
	 * @return array
	 */
	public function extractYuchoData($seq)
	{
        $sentData = $this->extractSentData($seq, self::TARGET_FUNC_REGISTER);
        $rcvData = $this->extractReceivedData($seq, self::TARGET_FUNC_REGISTER);
        $accData = $this->_findRelAccount($seq);
        if(!$sentData || !$rcvData || !$accData)
        {
            return null;
        }

        $barcodeData = $rcvData['haraidashi_no1'];

        // コンビニ限度額オーバーでバーコードデータが返却されなかった場合はMTコードに使用するためだけに独自に生成する
        if(!strlen($barcodeData))
        {
            $barcodeData = $this->generateSmbcBarcodeData($seq);
        }

        $result = array(
                'Yu_SubscriberName' => $accData['Yu_SubscriberName'],
                'Yu_AccountNumber' => $accData['Yu_AccountNumber'],
                'Yu_ChargeClass' => $accData['Yu_ChargeClass'],
                'Yu_SubscriberData' => sprintf('%09s', $sentData['syuno_co_cd'])		// 加入者固有データは送信時に選択した収納企業コード
        );

        $util = new LogicYuchoUtility();

        // 上段データ
        if(strlen($result['Yu_AccountNumber']) != 11) {
            // 登録桁数が11桁でない場合は12桁に補完した上で6桁目を除去
            $accNumber = substr(sprintf('%012d', 0).$result['Yu_AccountNumber'], -12);
            $accNumber = join('', array(substr($accNumber, 0, 5), substr($accNumber, -6)));
        } else {
            // 登録桁数が11桁の場合はそのまま使用
            $accNumber = $result['Yu_AccountNumber'];
        }
        $data1_part = array(
                $accNumber,														// 口座番号：11桁
                sprintf('%011d', (int)$sentData['seikyuu_kingaku']),			// 請求金額ゼロ詰：11桁
                $result['Yu_ChargeClass'],										// 払込負担区分：1桁
                '00000',														// 予備ゼロ詰：5桁
                substr($barcodeData, 0, 8),										// バーコード：8桁（CVSバーコードデータの1～8桁）
                '0'																// 予備ゼロ詰：1桁
        );
        $data1 = join('', $data1_part);

        // 下段データ
        $data2_part = array(
                substr($barcodeData, 8, 36),									// バーコード：36桁（CVSバーコードデータの9～44桁）
                '00000',														// 予備ゼロ詰：5桁
                '2'																// バーコード種別：1桁（「2：EAN-128 1段バーコード」固定）
        );
        $data2 = join('', $data2_part);

        $cd1 = $util->calcMtCode($data1);
        $cd2 = $util->calcMtCode($data2);

        return array_merge(
            $result,
            array(
                'Yu_MtOcrCode1' => sprintf('%s%s   X', $cd1, $data1),   // 上段は末尾に「   X」（4桁）を付与する
                'Yu_MtOcrCode2' => $cd2 . $data2
            ) );
	}

	/**
	 * 指定請求口座に関連付けられた決済ステーション連携ログの送受信データから
	 * 対応するゆうちょ口座の情報を展開する
	 *
	 * @param int $claimAccountSeq 請求口座SEQ
	 * @return array
	 */
	public function extractYuchoDataByClaimAccount($claimAccountSeq)
	{
        $row = $this->findByClaimAccountSeq($claimAccountSeq, self::TARGET_FUNC_REGISTER)->current();
        return ($row) ? $this->extractYuchoData($row['Seq']) : null;
	}

	/**
	 * 指定連携ログの受信データから、払い出されたコンビニ収納情報を展開する
	 *
	 * @param int $seq 決済ステーション連携ログ
	 * @return array
	 */
	public function extractCvsData($seq)
	{
        $rcvData = $this->extractReceivedData($seq, self::TARGET_FUNC_REGISTER);
        $accData = $this->_findRelAccount($seq);
        if(!$rcvData || !$accData)
        {
            return null;
        }

        $s = $rcvData['haraidashi_no1'];
        if(!strlen($s)) {
            // バーコードデータの長さが0だった場合はリミットオーバーと見なす
            $s = LogicOemClaimAccount::CLAIM_AMOUNT_OVER_LIMIT_MESSAGE;
            $barcodeString1 = $barcodeString2 = '';
        } else {
            $barcodeString1 = sprintf('(%s) %s-%s',
                                substr($s, 0, 2),
                                substr($s, 2, 6),
                                substr($s, 8, 22));
            $barcodeString2 = sprintf('%s-%s-%s-%s',
                                substr($s, 30, 6),
                                substr($s, 36, 1),
                                substr($s, 37, 6),
                                substr($s, 43, 1));
        }

        return array(
                'Cv_BarcodeData' => $s,
                'Cv_BarcodeString1' => $barcodeString1,
                'Cv_BarcodeString2' => $barcodeString2,
                'Cv_ReceiptAgentName' => $accData['Cv_ReceiptAgentName'],
                'Cv_SubscriberName' => $accData['Cv_SubscriberName']
        );
	}

	/**
	 * 指定の請求口座に関連付けられた決済ステーション連携ログの受信データから、
	 * 払い出されたコンビニ収納情報を展開する
	 *
	 * @param int $claimAccountSeq 請求口座SEQ
	 * @return array
	 */
	public function extractCvsDataByClaimAccount($claimAccountSeq)
	{
        $row = $this->findByClaimAccountSeq($claimAccountSeq, self::TARGET_FUNC_REGISTER)->current();
        return ($row) ? $this->extractCvsData($row['Seq']) : null;
	}

	/**
	 * 指定の連携ログを所有するOEM先に関連付けられた、SMBC決済ステーションアカウント情報を
	 * 取得する
	 *
	 * @access protected
	 * @param int $seq 決済ステーション連携ログ
	 * @return array | null
	 */
	protected function _findRelAccount($seq)
	{
		$q = <<<EOQ
SELECT
	sra.*
FROM
	T_SmbcRelationLog srl INNER JOIN
	T_Order ord ON ord.OrderSeq = srl.OrderSeq LEFT OUTER JOIN
	T_SmbcRelationAccount sra ON sra.OemId = IFNULL(ord.OemId, 0)
WHERE
	srl.Seq = :Seq
EOQ;
        $row = $this->_adapter->query($q)->execute(array(':Seq' => $seq))->current();
        return ($row) ? $row : null;
	}

	/**
	 * 指定の連携ログに関連する請求履歴データを取得する
	 *
	 * @access protected
	 * @param int $seq 決済ステーション連携ログ
	 * @return array | null
	 */
	protected function _findClaimHistory($seq)
	{
		$q = <<<EOQ
SELECT
	h.*
FROM
	T_SmbcRelationLog srl INNER JOIN
	T_OemClaimAccountInfo oca ON oca.ClaimAccountSeq = srl.ClaimAccountSeq INNER JOIN
	T_ClaimHistory h ON h.Seq = oca.ClaimHistorySeq
WHERE
	srl.Seq = :Seq
EOQ;
        $row = $this->_adapter->query($q)->execute(array(':Seq' => $seq))->current();
        return ($row) ? $row : null;
	}

	/**
	 * 指定連携ログに対応する、SMBCFS用CVSバーコードデータを生成する
	 *
	 * @param int $seq 決済ステーション連携ログSEQ
	 * @return array
	 */
	public function generateSmbcBarcodeData($seq)
	{
        // バーコード生成に必要なデータを取得するため、指定ログの送信データと決済ステーション連携アカウント、
        // 請求履歴データを取得する
        $sentData = $this->extractSentData($seq, self::TARGET_FUNC_REGISTER);
        $accData = $this->_findRelAccount($seq);
        $his = $this->_findClaimHistory($seq);
        if(!$sentData || !$accData || !$his)
        {
            // いずれかのデータが見つからない場合は例外で終了
            throw new \Exception('invalid relation-log seq specified or related data not found !!!');
        }

        // 収納代行会社固有コード（＝ファイナンスコード）を補完する（暫定措置）
        if(!isset($accData['Cv_ReceiptAgentCode']) || !$accData['Cv_ReceiptAgentCode'])
        {
            $accData['Cv_ReceiptAgentCode'] = '08082';
        }

        // 印紙代判断ユーティリティを初期化
        $sysProps = new \models\Table\TableSystemProperty($this->_adapter);
        $stampUtil = LogicBarcodeDataCvs::createStampFeeLogic($sysProps->getStampFeeSettings());
        $logicName = 'LogicBarcodeDataCvsSmbcfs';

        // バーコード生成ロジックを初期化
        /** @var LogicBarcodeDataCvsSmbcfs */
        $barcodeLogic = LogicBarcodeDataCvs::createGenerator($logicName, $accData['Cv_ReceiptAgentCode'], $sentData['syuno_co_cd']);

        $barcodeLogic
            ->setUniqueSequence($sentData['shoporder_no'])
            ->setPaymentMoney($sentData['seikyuu_kingaku'])
            ->setLimitDate(date('ymd', strtotime($sentData['shiharai_date'])))
            ->setStampFlagThresholdPrice($stampUtil->getStampFeeThresholdAt($his['ClaimDate']));

        // 44桁のバーコードデータを生成して返す
        return $barcodeLogic->generate();
	}

    /**
     * 新しいレコードをインサートする。
     *
     * @param array $data インサートする連想配列
     * @return プライマリキーのバリュー
     */
    protected function _saveNew($data)
    {
        $sql  = " INSERT INTO T_SmbcRelationLog (ClaimAccountSeq, TargetFunction, SentTime, OrderSeq, Status, SentRawData, ReceivedTime, ReceivedRawData, ErrorReason, AcceptTime, AcceptNumber, ResponseCode, ResponseMessage, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :ClaimAccountSeq ";
        $sql .= " , :TargetFunction ";
        $sql .= " , :SentTime ";
        $sql .= " , :OrderSeq ";
        $sql .= " , :Status ";
        $sql .= " , :SentRawData ";
        $sql .= " , :ReceivedTime ";
        $sql .= " , :ReceivedRawData ";
        $sql .= " , :ErrorReason ";
        $sql .= " , :AcceptTime ";
        $sql .= " , :AcceptNumber ";
        $sql .= " , :ResponseCode ";
        $sql .= " , :ResponseMessage ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':ClaimAccountSeq' => $data['ClaimAccountSeq'],
                ':TargetFunction' => $data['TargetFunction'],
                ':SentTime' => $data['SentTime'],
                ':OrderSeq' => $data['OrderSeq'],
                ':Status' => isset($data['Status']) ? $data['Status'] : 0,
                ':SentRawData' => $data['SentRawData'],
                ':ReceivedTime' => $data['ReceivedTime'],
                ':ReceivedRawData' => $data['ReceivedRawData'],
                ':ErrorReason' => $data['ErrorReason'],
                ':AcceptTime' => $data['AcceptTime'],
                ':AcceptNumber' => $data['AcceptNumber'],
                ':ResponseCode' => $data['ResponseCode'],
                ':ResponseMessage' => $data['ResponseMessage'],
                ':RegistDate' => date('Y-m-d H:i:s'),
                ':RegistId' => $data['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $data['UpdateId'],
                ':ValidFlg' => isset($data['ValidFlg']) ? $data['ValidFlg'] : 1,
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
    }

    /**
     * 指定されたレコードを更新する。
     *
     * @param array $data 更新内容
     * @param int $seq 連携ログSEQ
     * @return ResultInterface
     */
    public function saveUpdate($data, $seq)
    {
        $row = $this->find($seq)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_SmbcRelationLog ";
        $sql .= " SET ";
        $sql .= "     ClaimAccountSeq = :ClaimAccountSeq ";
        $sql .= " ,   TargetFunction = :TargetFunction ";
        $sql .= " ,   SentTime = :SentTime ";
        $sql .= " ,   OrderSeq = :OrderSeq ";
        $sql .= " ,   Status = :Status ";
        $sql .= " ,   SentRawData = :SentRawData ";
        $sql .= " ,   ReceivedTime = :ReceivedTime ";
        $sql .= " ,   ReceivedRawData = :ReceivedRawData ";
        $sql .= " ,   ErrorReason = :ErrorReason ";
        $sql .= " ,   AcceptTime = :AcceptTime ";
        $sql .= " ,   AcceptNumber = :AcceptNumber ";
        $sql .= " ,   ResponseCode = :ResponseCode ";
        $sql .= " ,   ResponseMessage = :ResponseMessage ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
                ':ClaimAccountSeq' => $row['ClaimAccountSeq'],
                ':TargetFunction' => $row['TargetFunction'],
                ':SentTime' => $row['SentTime'],
                ':OrderSeq' => $row['OrderSeq'],
                ':Status' => $row['Status'],
                ':SentRawData' => $row['SentRawData'],
                ':ReceivedTime' => $row['ReceivedTime'],
                ':ReceivedRawData' => $row['ReceivedRawData'],
                ':ErrorReason' => $row['ErrorReason'],
                ':AcceptTime' => $row['AcceptTime'],
                ':AcceptNumber' => $row['AcceptNumber'],
                ':ResponseCode' => $row['ResponseCode'],
                ':ResponseMessage' => $row['ResponseMessage'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => $row['UpdateDate'],
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
    }
}
