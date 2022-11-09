<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * 請求履歴に各種収納口座情報を関連付けて管理する
 * T_OemClaimAccountInfoテーブルへのアダプタ
 */
class TableOemClaimAccountInfo
{
	protected $_name = 'T_OemClaimAccountInfo';
	protected $_primary = array('ClaimAccountSeq');
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
	 * OEM請求口座データを取得する
	 *
	 * @param int $claimAccountSeq 請求口座SEQ
	 * @return ResultInterface
	 */
	public function find($claimAccountSeq)
	{
	    $sql = " SELECT * FROM T_OemClaimAccountInfo WHERE ClaimAccountSeq = :ClaimAccountSeq ";

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':ClaimAccountSeq' => $claimAccountSeq,
	    );

	    return $stm->execute($prm);
	}

    /**
	 * 新しいレコードをインサートする。
	 *
	 * @param array $data インサートする連想配列
	 * @return プライマリキーのバリュー？
	 */
	public function saveNew($chSeq, $data)
	{
        // 親となる請求履歴を検索する
        $claimHistory = $this->findClaimHistory($chSeq);
        if (!($claimHistory->count() > 0)) {
            // 請求履歴が存在しないのは致命的
            throw new \Exception('ClaimHistory not found !!!');
        }

        $row = $claimHistory->current();
        // データ初期化
        $data['RegistDate'] = date('Y-m-d H:i:s');
        $data['ClaimHistorySeq'] = (int)$row['Seq'];
        $data['OrderSeq'] = (int)$row['OrderSeq'];
        $data['InnerSeq'] = $this->getNextInnerSeqByOrderSeq($data['OrderSeq']);

        $sql  = " INSERT T_OemClaimAccountInfo (ClaimHistorySeq, OrderSeq, InnerSeq, Bk_ServiceKind, Bk_BankCode, Bk_BranchCode, Bk_BankName, Bk_BranchName, Bk_DepositClass, Bk_AccountNumber, Bk_AccountHolder, Yu_SubscriberName, Yu_AccountNumber, Yu_ChargeClass, Yu_SubscriberData, Yu_Option1, Yu_Option2, Yu_Option3, Yu_MtOcrCode1, Yu_MtOcrCode2, Yu_DtCode, Cv_ReceiptAgentName, Cv_ReceiptAgentCode, Cv_BarcodeLogicName, Cv_SubscriberCode, Cv_Option1, Cv_Option2, Cv_Option3, Cv_BarcodeData, Cv_BarcodeString1, Cv_BarcodeString2, RegistDate, Status, TaxAmount, SubUseAmount_1, SubTaxAmount_1, SubUseAmount_2, SubTaxAmount_2, Bk_AccountHolderKn, Cv_SubscriberName, ClaimLayoutMode, ConfirmNumber, CustomerNumber, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :ClaimHistorySeq ";
        $sql .= " , :OrderSeq ";
        $sql .= " , :InnerSeq ";
        $sql .= " , :Bk_ServiceKind ";
        $sql .= " , :Bk_BankCode ";
        $sql .= " , :Bk_BranchCode ";
        $sql .= " , :Bk_BankName ";
        $sql .= " , :Bk_BranchName ";
        $sql .= " , :Bk_DepositClass ";
        $sql .= " , :Bk_AccountNumber ";
        $sql .= " , :Bk_AccountHolder ";
        $sql .= " , :Yu_SubscriberName ";
        $sql .= " , :Yu_AccountNumber ";
        $sql .= " , :Yu_ChargeClass ";
        $sql .= " , :Yu_SubscriberData ";
        $sql .= " , :Yu_Option1 ";
        $sql .= " , :Yu_Option2 ";
        $sql .= " , :Yu_Option3 ";
        $sql .= " , :Yu_MtOcrCode1 ";
        $sql .= " , :Yu_MtOcrCode2 ";
        $sql .= " , :Yu_DtCode ";
        $sql .= " , :Cv_ReceiptAgentName ";
        $sql .= " , :Cv_ReceiptAgentCode ";
        $sql .= " , :Cv_BarcodeLogicName ";
        $sql .= " , :Cv_SubscriberCode ";
        $sql .= " , :Cv_Option1 ";
        $sql .= " , :Cv_Option2 ";
        $sql .= " , :Cv_Option3 ";
        $sql .= " , :Cv_BarcodeData ";
        $sql .= " , :Cv_BarcodeString1 ";
        $sql .= " , :Cv_BarcodeString2 ";
        $sql .= " , :RegistDate ";
        $sql .= " , :Status ";
        $sql .= " , :TaxAmount ";
        $sql .= " , :SubUseAmount_1 ";
        $sql .= " , :SubTaxAmount_1 ";
        $sql .= " , :SubUseAmount_2 ";
        $sql .= " , :SubTaxAmount_2 ";
        $sql .= " , :Bk_AccountHolderKn ";
        $sql .= " , :Cv_SubscriberName ";
        $sql .= " , :ClaimLayoutMode ";
        $sql .= " , :ConfirmNumber ";
        $sql .= " , :CustomerNumber ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':ClaimHistorySeq' => $data['ClaimHistorySeq'],
                ':OrderSeq' => $data['OrderSeq'],
                ':InnerSeq' => isset($data['InnerSeq']) ? $data['InnerSeq'] : 1,
                ':Bk_ServiceKind' => $data['Bk_ServiceKind'],
                ':Bk_BankCode' => $data['Bk_BankCode'],
                ':Bk_BranchCode' => $data['Bk_BranchCode'],
                ':Bk_BankName' => $data['Bk_BankName'],
                ':Bk_BranchName' => $data['Bk_BranchName'],
                ':Bk_DepositClass' => $data['Bk_DepositClass'],
                ':Bk_AccountNumber' => $data['Bk_AccountNumber'],
                ':Bk_AccountHolder' => $data['Bk_AccountHolder'],
                ':Yu_SubscriberName' => $data['Yu_SubscriberName'],
                ':Yu_AccountNumber' => $data['Yu_AccountNumber'],
                ':Yu_ChargeClass' => $data['Yu_ChargeClass'],
                ':Yu_SubscriberData' => $data['Yu_SubscriberData'],
                ':Yu_Option1' => $data['Yu_Option1'],
                ':Yu_Option2' => $data['Yu_Option2'],
                ':Yu_Option3' => $data['Yu_Option3'],
                ':Yu_MtOcrCode1' => $data['Yu_MtOcrCode1'],
                ':Yu_MtOcrCode2' => $data['Yu_MtOcrCode2'],
                ':Yu_DtCode' => $data['Yu_DtCode'],
                ':Cv_ReceiptAgentName' => $data['Cv_ReceiptAgentName'],
                ':Cv_ReceiptAgentCode' => $data['Cv_ReceiptAgentCode'],
                ':Cv_BarcodeLogicName' => $data['Cv_BarcodeLogicName'],
                ':Cv_SubscriberCode' => $data['Cv_SubscriberCode'],
                ':Cv_Option1' => $data['Cv_Option1'],
                ':Cv_Option2' => $data['Cv_Option2'],
                ':Cv_Option3' => $data['Cv_Option3'],
                ':Cv_BarcodeData' => $data['Cv_BarcodeData'],
                ':Cv_BarcodeString1' => $data['Cv_BarcodeString1'],
                ':Cv_BarcodeString2' => $data['Cv_BarcodeString2'],
                ':RegistDate' => date('Y-m-d H:i:s'),
                ':Status' => isset($data['Status']) ? $data['Status'] : 0,
                ':TaxAmount' => isset($data['TaxAmount']) ? $data['TaxAmount'] : 0,
                ':SubUseAmount_1' => isset($data['SubUseAmount_1']) ? $data['SubUseAmount_1'] : 0,
                ':SubTaxAmount_1' => isset($data['SubTaxAmount_1']) ? $data['SubTaxAmount_1'] : 0,
                ':SubUseAmount_2' => isset($data['SubUseAmount_2']) ? $data['SubUseAmount_2'] : 0,
                ':SubTaxAmount_2' => isset($data['SubTaxAmount_2']) ? $data['SubTaxAmount_2'] : 0,
                ':Bk_AccountHolderKn' => $data['Bk_AccountHolderKn'],
                ':Cv_SubscriberName' => $data['Cv_SubscriberName'],
                ':ClaimLayoutMode' => isset($data['ClaimLayoutMode']) ? $data['ClaimLayoutMode'] : 0,
                ':ConfirmNumber' => $data['ConfirmNumber'],
                ':CustomerNumber' => $data['CustomerNumber'],
                ':RegistId' => $data['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $data['UpdateId'],
                ':ValidFlg' => isset($data['ValidFlg']) ? $data['ValidFlg'] : 1,
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
	}

	/**
	 * 新しいレコードをインサートする。
	 *
	 * @param int $chSeq 請求履歴SEQ
	 * @param array $data インサートする連想配列
	 * @param int $orderSeq 注文SEQ
	 * @param int $innerSeq 注文内通番
	 * @return 新規登録したPK値
	 * @see 引数$innerSeq(注文内通番)は、内部関数[getNextInnerSeqByOrderSeq]処理結果相当
	 */
	public function saveNewEx($chSeq, $data, $orderSeq, $innerSeq)
	{
	    // データ初期化
	    $data['ClaimHistorySeq'] = $chSeq;
	    $data['OrderSeq'] = $orderSeq;
	    $data['InnerSeq'] = $innerSeq;

	    $sql  = " INSERT T_OemClaimAccountInfo (ClaimHistorySeq, OrderSeq, InnerSeq, Bk_ServiceKind, Bk_BankCode, Bk_BranchCode, Bk_BankName, Bk_BranchName, Bk_DepositClass, Bk_AccountNumber, Bk_AccountHolder, Yu_SubscriberName, Yu_AccountNumber, Yu_ChargeClass, Yu_SubscriberData, Yu_Option1, Yu_Option2, Yu_Option3, Yu_MtOcrCode1, Yu_MtOcrCode2, Yu_DtCode, Cv_ReceiptAgentName, Cv_ReceiptAgentCode, Cv_BarcodeLogicName, Cv_SubscriberCode, Cv_Option1, Cv_Option2, Cv_Option3, Cv_BarcodeData, Cv_BarcodeString1, Cv_BarcodeString2, RegistDate, Status, TaxAmount, SubUseAmount_1, SubTaxAmount_1, SubUseAmount_2, SubTaxAmount_2, Bk_AccountHolderKn, Cv_SubscriberName, ClaimLayoutMode, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
	    $sql .= "   :ClaimHistorySeq ";
	    $sql .= " , :OrderSeq ";
	    $sql .= " , :InnerSeq ";
	    $sql .= " , :Bk_ServiceKind ";
	    $sql .= " , :Bk_BankCode ";
	    $sql .= " , :Bk_BranchCode ";
	    $sql .= " , :Bk_BankName ";
	    $sql .= " , :Bk_BranchName ";
	    $sql .= " , :Bk_DepositClass ";
	    $sql .= " , :Bk_AccountNumber ";
	    $sql .= " , :Bk_AccountHolder ";
	    $sql .= " , :Yu_SubscriberName ";
	    $sql .= " , :Yu_AccountNumber ";
	    $sql .= " , :Yu_ChargeClass ";
	    $sql .= " , :Yu_SubscriberData ";
	    $sql .= " , :Yu_Option1 ";
	    $sql .= " , :Yu_Option2 ";
	    $sql .= " , :Yu_Option3 ";
	    $sql .= " , :Yu_MtOcrCode1 ";
	    $sql .= " , :Yu_MtOcrCode2 ";
	    $sql .= " , :Yu_DtCode ";
	    $sql .= " , :Cv_ReceiptAgentName ";
	    $sql .= " , :Cv_ReceiptAgentCode ";
	    $sql .= " , :Cv_BarcodeLogicName ";
	    $sql .= " , :Cv_SubscriberCode ";
	    $sql .= " , :Cv_Option1 ";
	    $sql .= " , :Cv_Option2 ";
	    $sql .= " , :Cv_Option3 ";
	    $sql .= " , :Cv_BarcodeData ";
	    $sql .= " , :Cv_BarcodeString1 ";
	    $sql .= " , :Cv_BarcodeString2 ";
	    $sql .= " , :RegistDate ";
	    $sql .= " , :Status ";
	    $sql .= " , :TaxAmount ";
	    $sql .= " , :SubUseAmount_1 ";
	    $sql .= " , :SubTaxAmount_1 ";
	    $sql .= " , :SubUseAmount_2 ";
	    $sql .= " , :SubTaxAmount_2 ";
	    $sql .= " , :Bk_AccountHolderKn ";
	    $sql .= " , :Cv_SubscriberName ";
	    $sql .= " , :ClaimLayoutMode ";
	    $sql .= " , :RegistId ";
	    $sql .= " , :UpdateDate ";
	    $sql .= " , :UpdateId ";
	    $sql .= " , :ValidFlg ";
	    $sql .= " )";

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':ClaimHistorySeq' => $data['ClaimHistorySeq'],
	            ':OrderSeq' => $data['OrderSeq'],
	            ':InnerSeq' => isset($data['InnerSeq']) ? $data['InnerSeq'] : 1,
	            ':Bk_ServiceKind' => $data['Bk_ServiceKind'],
	            ':Bk_BankCode' => $data['Bk_BankCode'],
	            ':Bk_BranchCode' => $data['Bk_BranchCode'],
	            ':Bk_BankName' => $data['Bk_BankName'],
	            ':Bk_BranchName' => $data['Bk_BranchName'],
	            ':Bk_DepositClass' => $data['Bk_DepositClass'],
	            ':Bk_AccountNumber' => $data['Bk_AccountNumber'],
	            ':Bk_AccountHolder' => $data['Bk_AccountHolder'],
	            ':Yu_SubscriberName' => $data['Yu_SubscriberName'],
	            ':Yu_AccountNumber' => $data['Yu_AccountNumber'],
	            ':Yu_ChargeClass' => $data['Yu_ChargeClass'],
	            ':Yu_SubscriberData' => $data['Yu_SubscriberData'],
	            ':Yu_Option1' => $data['Yu_Option1'],
	            ':Yu_Option2' => $data['Yu_Option2'],
	            ':Yu_Option3' => $data['Yu_Option3'],
	            ':Yu_MtOcrCode1' => $data['Yu_MtOcrCode1'],
	            ':Yu_MtOcrCode2' => $data['Yu_MtOcrCode2'],
	            ':Yu_DtCode' => $data['Yu_DtCode'],
	            ':Cv_ReceiptAgentName' => $data['Cv_ReceiptAgentName'],
	            ':Cv_ReceiptAgentCode' => $data['Cv_ReceiptAgentCode'],
	            ':Cv_BarcodeLogicName' => $data['Cv_BarcodeLogicName'],
	            ':Cv_SubscriberCode' => $data['Cv_SubscriberCode'],
	            ':Cv_Option1' => $data['Cv_Option1'],
	            ':Cv_Option2' => $data['Cv_Option2'],
	            ':Cv_Option3' => $data['Cv_Option3'],
	            ':Cv_BarcodeData' => $data['Cv_BarcodeData'],
	            ':Cv_BarcodeString1' => $data['Cv_BarcodeString1'],
	            ':Cv_BarcodeString2' => $data['Cv_BarcodeString2'],
	            ':RegistDate' => date('Y-m-d H:i:s'),
	            ':Status' => isset($data['Status']) ? $data['Status'] : 0,
	            ':TaxAmount' => isset($data['TaxAmount']) ? $data['TaxAmount'] : 0,
	            ':SubUseAmount_1' => isset($data['SubUseAmount_1']) ? $data['SubUseAmount_1'] : 0,
	            ':SubTaxAmount_1' => isset($data['SubTaxAmount_1']) ? $data['SubTaxAmount_1'] : 0,
	            ':SubUseAmount_2' => isset($data['SubUseAmount_2']) ? $data['SubUseAmount_2'] : 0,
	            ':SubTaxAmount_2' => isset($data['SubTaxAmount_2']) ? $data['SubTaxAmount_2'] : 0,
	            ':Bk_AccountHolderKn' => $data['Bk_AccountHolderKn'],
	            ':Cv_SubscriberName' => $data['Cv_SubscriberName'],
	            ':ClaimLayoutMode' => isset($data['ClaimLayoutMode']) ? $data['ClaimLayoutMode'] : 0,
	            ':RegistId' => $data['RegistId'],
	            ':UpdateDate' => date('Y-m-d H:i:s'),
	            ':UpdateId' => $data['UpdateId'],
	            ':ValidFlg' => isset($data['ValidFlg']) ? $data['ValidFlg'] : 1,
	    );

	    $ri = $stm->execute($prm);

	    return $ri->getGeneratedValue();// 新規登録したPK値を戻す
	}

	protected function isPrimaryKey($colName)
	{
		$primaries = $this->_primary;
		if(is_array($primaries)) {
			return in_array($colName, $primaries);
		} else {
			return $colName == $primaries;
		}
	}

    /**
     * 指定SEQを持つ請求履歴を検索する
     *
     * @access protected
     * @param int $chSeq 請求履歴SEQ
     * @return ResultInterface
     */
    protected function findClaimHistory($chSeq)
    {
        $sql = " SELECT * FROM T_ClaimHistory WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $chSeq,
        );

        return $stm->execute($prm);
    }

	/**
	 * 指定されたレコードを更新する。
	 *
	 * @param array $data 更新内容
	 * @param int $seq 更新するSeq
	 */
	public function saveUpdate($data, $seq)
	{
        $sql = " SELECT * FROM T_OemClaimAccountInfo WHERE ClaimAccountSeq = :ClaimAccountSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':ClaimAccountSeq' => $seq,
        );

        $row = $stm->execute($prm)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_OemClaimAccountInfo ";
        $sql .= " SET ";
        $sql .= "     ClaimHistorySeq = :ClaimHistorySeq ";
        $sql .= " ,   OrderSeq = :OrderSeq ";
        $sql .= " ,   InnerSeq = :InnerSeq ";
        $sql .= " ,   Bk_ServiceKind = :Bk_ServiceKind ";
        $sql .= " ,   Bk_BankCode = :Bk_BankCode ";
        $sql .= " ,   Bk_BranchCode = :Bk_BranchCode ";
        $sql .= " ,   Bk_BankName = :Bk_BankName ";
        $sql .= " ,   Bk_BranchName = :Bk_BranchName ";
        $sql .= " ,   Bk_DepositClass = :Bk_DepositClass ";
        $sql .= " ,   Bk_AccountNumber = :Bk_AccountNumber ";
        $sql .= " ,   Bk_AccountHolder = :Bk_AccountHolder ";
        $sql .= " ,   Yu_SubscriberName = :Yu_SubscriberName ";
        $sql .= " ,   Yu_AccountNumber = :Yu_AccountNumber ";
        $sql .= " ,   Yu_ChargeClass = :Yu_ChargeClass ";
        $sql .= " ,   Yu_SubscriberData = :Yu_SubscriberData ";
        $sql .= " ,   Yu_Option1 = :Yu_Option1 ";
        $sql .= " ,   Yu_Option2 = :Yu_Option2 ";
        $sql .= " ,   Yu_Option3 = :Yu_Option3 ";
        $sql .= " ,   Yu_MtOcrCode1 = :Yu_MtOcrCode1 ";
        $sql .= " ,   Yu_MtOcrCode2 = :Yu_MtOcrCode2 ";
        $sql .= " ,   Yu_DtCode = :Yu_DtCode ";
        $sql .= " ,   Cv_ReceiptAgentName = :Cv_ReceiptAgentName ";
        $sql .= " ,   Cv_ReceiptAgentCode = :Cv_ReceiptAgentCode ";
        $sql .= " ,   Cv_BarcodeLogicName = :Cv_BarcodeLogicName ";
        $sql .= " ,   Cv_SubscriberCode = :Cv_SubscriberCode ";
        $sql .= " ,   Cv_Option1 = :Cv_Option1 ";
        $sql .= " ,   Cv_Option2 = :Cv_Option2 ";
        $sql .= " ,   Cv_Option3 = :Cv_Option3 ";
        $sql .= " ,   Cv_BarcodeData = :Cv_BarcodeData ";
        $sql .= " ,   Cv_BarcodeString1 = :Cv_BarcodeString1 ";
        $sql .= " ,   Cv_BarcodeString2 = :Cv_BarcodeString2 ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   Status = :Status ";
        $sql .= " ,   TaxAmount = :TaxAmount ";
        $sql .= " ,   SubUseAmount_1 = :SubUseAmount_1 ";
        $sql .= " ,   SubTaxAmount_1 = :SubTaxAmount_1 ";
        $sql .= " ,   SubUseAmount_2 = :SubUseAmount_2 ";
        $sql .= " ,   SubTaxAmount_2 = :SubTaxAmount_2 ";
        $sql .= " ,   Bk_AccountHolderKn = :Bk_AccountHolderKn ";
        $sql .= " ,   Cv_SubscriberName = :Cv_SubscriberName ";
        $sql .= " ,   ClaimLayoutMode = :ClaimLayoutMode ";
        $sql .= " ,   ConfirmNumber = :ConfirmNumber";
        $sql .= " ,   CustomerNumber = :CustomerNumber";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE ClaimAccountSeq = :ClaimAccountSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':ClaimAccountSeq' => $seq,
                ':ClaimHistorySeq' => $row['ClaimHistorySeq'],
                ':OrderSeq' => $row['OrderSeq'],
                ':InnerSeq' => $row['InnerSeq'],
                ':Bk_ServiceKind' => $row['Bk_ServiceKind'],
                ':Bk_BankCode' => $row['Bk_BankCode'],
                ':Bk_BranchCode' => $row['Bk_BranchCode'],
                ':Bk_BankName' => $row['Bk_BankName'],
                ':Bk_BranchName' => $row['Bk_BranchName'],
                ':Bk_DepositClass' => $row['Bk_DepositClass'],
                ':Bk_AccountNumber' => $row['Bk_AccountNumber'],
                ':Bk_AccountHolder' => $row['Bk_AccountHolder'],
                ':Yu_SubscriberName' => $row['Yu_SubscriberName'],
                ':Yu_AccountNumber' => $row['Yu_AccountNumber'],
                ':Yu_ChargeClass' => $row['Yu_ChargeClass'],
                ':Yu_SubscriberData' => $row['Yu_SubscriberData'],
                ':Yu_Option1' => $row['Yu_Option1'],
                ':Yu_Option2' => $row['Yu_Option2'],
                ':Yu_Option3' => $row['Yu_Option3'],
                ':Yu_MtOcrCode1' => $row['Yu_MtOcrCode1'],
                ':Yu_MtOcrCode2' => $row['Yu_MtOcrCode2'],
                ':Yu_DtCode' => $row['Yu_DtCode'],
                ':Cv_ReceiptAgentName' => $row['Cv_ReceiptAgentName'],
                ':Cv_ReceiptAgentCode' => $row['Cv_ReceiptAgentCode'],
                ':Cv_BarcodeLogicName' => $row['Cv_BarcodeLogicName'],
                ':Cv_SubscriberCode' => $row['Cv_SubscriberCode'],
                ':Cv_Option1' => $row['Cv_Option1'],
                ':Cv_Option2' => $row['Cv_Option2'],
                ':Cv_Option3' => $row['Cv_Option3'],
                ':Cv_BarcodeData' => $row['Cv_BarcodeData'],
                ':Cv_BarcodeString1' => $row['Cv_BarcodeString1'],
                ':Cv_BarcodeString2' => $row['Cv_BarcodeString2'],
                ':RegistDate' => $row['RegistDate'],
                ':Status' => $row['Status'],
                ':TaxAmount' => $row['TaxAmount'],
                ':SubUseAmount_1' => $row['SubUseAmount_1'],
                ':SubTaxAmount_1' => $row['SubTaxAmount_1'],
                ':SubUseAmount_2' => $row['SubUseAmount_2'],
                ':SubTaxAmount_2' => $row['SubTaxAmount_2'],
                ':Bk_AccountHolderKn' => $row['Bk_AccountHolderKn'],
                ':Cv_SubscriberName' => $row['Cv_SubscriberName'],
                ':ClaimLayoutMode' => $row['ClaimLayoutMode'],
                ':ConfirmNumber' => $row['ConfirmNumber'],
                ':CustomerNumber' => $row['CustomerNumber'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
	}

    /**
     * 指定の注文に関連付けられているすべてのOEM請求口座データを取得する
     *
     * @param int $oseq 注文SEQ
     * @param null | string $order ソート順指定。'asc'または'desc'を指定可能。省略時は'desc'
     * @return ResultInterface
     */
    public function findByOrderSeq($oseq, $order = 'desc')
    {
        $order = strtolower((string)$order);
        if(!in_array($order, array('asc', 'desc'))) $order = 'desc';
        $sql = " SELECT * FROM T_OemClaimAccountInfo WHERE OrderSeq = :OrderSeq ORDER BY ClaimAccountSeq " . $order;

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $oseq,
        );

        return $stm->execute($prm);
    }

	/**
	 * 指定の請求履歴に関連付けられているOEM請求口座データを取得する
	 *
	 * @param int $chSeq 請求履歴SEQ
	 * @return ResultInterface
	 */
	public function findByClaimHistorySeq($chSeq)
	{
        $sql = " SELECT * FROM T_OemClaimAccountInfo WHERE ClaimHistorySeq = :ClaimHistorySeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':ClaimHistorySeq' => $chSeq,
        );

        return $stm->execute($prm);
	}

	/**
	 * 指定のコンビニバーコードデータに一致するOEM請求口座データを取得する。
	 * 複数該当した場合は請求履歴SEQ降順、内部SEQ降順でソートされる。
	 *
	 * @param string $barcodeData 44桁のEAN128形式バーコードデータ
	 * @return ResultInterface
	 */
	public function findByCvsBarcodeData($barcodeData)
	{
        $sql = " SELECT * FROM T_OemClaimAccountInfo WHERE Cv_BarcodeData = CAST(:Cv_BarcodeData AS CHAR) ORDER BY ClaimHistorySeq desc, InnerSeq desc ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Cv_BarcodeData' => $barcodeData,
        );

        return $stm->execute($prm);
	}

	/**
	 * 指定のゆうちょMTコードに一致するOEM請求口座データを取得する。
	 * 複数該当した場合は請求履歴SEQ降順、内部SEQ降順でソートされる。
	 *
	 * @param string $mtCode MTコード
	 * @param int | null $target 対象のMTデータを1段目か2段目で指定する。省略時は2（＝2段目）
	 * @return ResultInterface
	 */
	public function findByYuchoMtCode($mtCode, $target = 2)
	{
        $target = (int)$target;
        if(!in_array($target, array(1, 2))) {
            $target = 2;
        }
        $col = ($target == 1) ? 'Yu_MtOcrCode1' : 'Yu_MtOcrCode2';
        $sql = " SELECT * FROM T_OemClaimAccountInfo WHERE :COLNAME = CAST(:MTCODE AS CHAR) ORDER BY ClaimHistorySeq desc, InnerSeq desc ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':COLNAME' => $col,
                ':MTCODE' => $mtCode,
        );

        return $stm->execute($prm);
	}

	/**
	 * 指定のゆうちょDTコードに一致するOEM請求口座データを取得する。
	 * 複数該当した場合は請求履歴SEQ降順、内部SEQ降順でソートされる。
	 *
	 * @param string $dtCode DTコード
	 * @return ResultInterface
	 */
	public function findByYuchoDtCode($dtCode)
	{
        $sql = " SELECT * FROM T_OemClaimAccountInfo WHERE Yu_DtCode = CAST(:Yu_DtCode AS CHAR) ORDER BY ClaimHistorySeq desc, InnerSeq desc ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Yu_DtCode' => $dtCode,
        );

        return $stm->execute($prm);
	}

    /**
     * 指定の注文に関連付けられているすべてのOEM請求口座データから、現在最大の内部SEQを
     * 取得する
     *
     * @param int $oseq 注文SEQ
     * @return int $oseqの注文に関連付けられているOEM請求口座の、最大のOEM請求口座内部SEQ
     */
    public function getMaxInnerSeqByOrderSeq($oseq)
    {
        $sql = " SELECT MAX(InnerSeq) AS maxInnerSeq FROM T_OemClaimAccountInfo WHERE OrderSeq = :OrderSeq GROUP BY OrderSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $oseq,
        );

        return (int)$stm->execute($prm)->current()['maxInnerSeq'];
    }

    /**
     * 指定の注文に対する、次の最大内部SEQを取得する
     *
     * @param int $oseq 注文SEQ
     * @return int
     */
    public function getNextInnerSeqByOrderSeq($oseq)
    {
         return $this->getMaxInnerSeqByOrderSeq($oseq) + 1;
    }

	/**
	 * 使用実績があるコンビニ加入者固有コードをすべて取得する
	 *
	 * @return ResultInterface
	 */
	public function getCvSubscriberCodes()
	{
        $sql = " SELECT Cv_SubscriberCode AS data FROM T_OemClaimAccountInfo GROUP BY Cv_SubscriberCode ";
        return $this->_adapter->query($sql)->execute(null);
	}

	/**
	 * 使用実績があるゆうちょ加入者固有データをすべて取得する
	 *
	 * @return ResultInterface
	 */
	public function getYuSubscriberDatas()
	{
        $sql = " SELECT Yu_SubscriberData AS data FROM T_OemClaimAccountInfo GROUP BY Yu_SubscriberData ";
        return $this->_adapter->query($sql)->execute(null);
	}
}
