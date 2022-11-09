<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;
use models\Logic\LogicOemClaimAccount;
use cbadmin\Application;

/**
 * T_ClaimHistoryテーブルへのアダプタ
 */
class TableClaimHistory
{
	protected $_name = 'T_ClaimHistory';
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
	 * 請求履歴データを取得する
	 *
	 * @param int $seq
	 * @return ResultInterface
	 */
	public function find($seq)
	{
        $sql  = " SELECT * FROM T_ClaimHistory WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
        );

        return $stm->execute($prm);
	}

	/**
	 * 指定条件（AND）の請求履歴データを取得する。
	 *
	 * @param array $conditionArray 検索条件を格納した連想配列
	 * @param boolean $isAsc プライマリキーのオーダー
	 * @return ResultInterface
	 */
	public function findClaimHistory($conditionArray, $isAsc = false)
	{
        $prm = array();
        $sql  = " SELECT * FROM T_ClaimHistory WHERE 1 = 1 ";
        foreach ($conditionArray as $key => $value) {
            $sql .= (" AND " . $key . " = :" . $key);
            $prm += array(':' . $key => $value);
        }
        $sql .= " ORDER BY Seq " . ($isAsc ? "asc" : "desc");

        $stm = $this->_adapter->query($sql);

        return $stm->execute($prm);
	}

	/**
	 * 未印刷の請求履歴データを削除する。
	 *
	 * @param int $oseq
	 */
	public function deleteReserved($oseq, $userId)
	{
	    // 指定注文の確定待ちフラグを落とす
	    $mdlo = new TableOrder($this->_adapter);
	    $mdlo->updateClaimUnissued($oseq, $userId);

        $sql  = " UPDATE T_ClaimHistory ";
        $sql .= "    SET UpdateId = :UpdateId ";
        $sql .= "       ,UpdateDate = NOW() ";
        $sql .= "       ,ValidFlg = 0 ";
        $sql .= "  WHERE Seq = :Seq ";
        $stm = $this->_adapter->query($sql);

        $ri = $this->_adapter->query(" SELECT Seq FROM T_ClaimHistory WHERE PrintedFlg = 0 AND ValidFlg = 1 AND OrderSeq = ( SELECT MAX(o.P_OrderSeq) FROM T_Order o WHERE o.OrderSeq = :OrderSeq ) "
            )->execute(array(':OrderSeq' => $oseq));
        foreach ($ri as $row) {
            $prm = array(
                    ':Seq' => $row['Seq'],
                    ':UpdateId' => $userId,
            );
            $stm->execute($prm);
        }

        // 指定注文の再請求指示データが存在すれば、フラグを落とす
        $sql  = " UPDATE T_ReclaimIndicate ";
        $sql .= "    SET UpdateId = :UpdateId ";
        $sql .= "       ,UpdateDate = NOW() ";
        $sql .= "       ,ValidFlg = 0 ";
        $sql .= "  WHERE Seq = :Seq ";
        $stm = $this->_adapter->query($sql);

        $ri = $this->_adapter->query(" SELECT Seq FROM T_ReclaimIndicate WHERE IndicatedFlg = 0 AND ValidFlg = 1 AND OrderSeq = ( SELECT MAX(o.P_OrderSeq) FROM T_Order o WHERE o.OrderSeq = :OrderSeq ) "
        )->execute(array(':OrderSeq' => $oseq));
        foreach ($ri as $row) {
            $prm = array(
                    ':Seq' => $row['Seq'],
                    ':UpdateId' => $userId,
            );
            $stm->execute($prm);
        }

        return;
	}

// 2015/07/23 Del Y.Suzuki 新システムでは廃止 Stt
//     /**
//      * 指定された注文Seqのすべての請求履歴を削除する。
//      *
//      * @param int $oseq 注文Seq
//      */
//     public function deleteAllClaimHistory($oseq)
// 2015/07/23 Del Y.Suzuki 新システムでは廃止 End

	/**
	 * 未印刷（印刷予約状態）の請求履歴データ数を取得する。
	 */
	public function getReservedCount($oseq)
	{
        $sql = " SELECT COUNT(1) AS cnt FROM T_ClaimHistory WHERE PrintedFlg = 0 AND OrderSeq = :OrderSeq AND ValidFlg = 1 ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $oseq,
        );

        return (int)$stm->execute($prm)->current()['cnt'];
	}

	/**
	 * 印刷済みの再請求履歴の数を取得する。
	 */
	public function getReClaimCount($oseq)
	{
        $sql = " SELECT COUNT(1) AS cnt FROM T_ClaimHistory WHERE PrintedFlg = 1 AND ClaimPattern IN (2,3,4) AND OrderSeq = :OrderSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $oseq,
        );

        return (int)$stm->execute($prm)->current()['cnt'];
	}

    /**
     * 新しいレコードをインサートする。
     *
     * @param int $orderSeq インサートする注文ID
     * @param array $data インサートする連想配列
     * @return プライマリキーのバリュー
     */
    public function saveNew($orderSeq, $data)
    {

        $data["OrderSeq"] = $orderSeq;

        // キケンだけど、、、とりあえずこんな方法で。
        $ri = $this->findClaimHistory(array('OrderSeq' => $orderSeq));
        $data["ClaimSeq"] = $ri->count() + 1;

        $sql  = " INSERT INTO T_ClaimHistory (OrderSeq, ClaimSeq, ClaimDate, ClaimCpId, ClaimPattern, LimitDate, DamageDays, DamageBaseDate, DamageInterestAmount, ClaimFee, AdditionalClaimFee, PrintedFlg, PrintedDate, MailFlg, CreditTransferMailFlg, ClaimFileOutputClass, EnterpriseBillingCode, ClaimAmount, ClaimId, ReissueClass, ReissueRequestDate, PrintedStatus, MailRetryCount, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :OrderSeq ";
        $sql .= " , :ClaimSeq ";
        $sql .= " , :ClaimDate ";
        $sql .= " , :ClaimCpId ";
        $sql .= " , :ClaimPattern ";
        $sql .= " , :LimitDate ";
        $sql .= " , :DamageDays ";
        $sql .= " , :DamageBaseDate ";
        $sql .= " , :DamageInterestAmount ";
        $sql .= " , :ClaimFee ";
        $sql .= " , :AdditionalClaimFee ";
        $sql .= " , :PrintedFlg ";
        $sql .= " , :PrintedDate ";
        $sql .= " , :MailFlg ";
        $sql .= " , :CreditTransferMailFlg ";
        $sql .= " , :ClaimFileOutputClass ";
        $sql .= " , :EnterpriseBillingCode ";
        $sql .= " , :ClaimAmount ";
        $sql .= " , :ClaimId ";
        $sql .= " , :ReissueClass ";
        $sql .= " , :ReissueRequestDate ";
        $sql .= " , :PrintedStatus ";
        $sql .= " , :MailRetryCount ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $data['OrderSeq'],
                ':ClaimSeq' => $data['ClaimSeq'],
                ':ClaimDate' => $data['ClaimDate'],
                ':ClaimCpId' => $data['ClaimCpId'],
                ':ClaimPattern' => $data['ClaimPattern'],
                ':LimitDate' => $data['LimitDate'],
                ':DamageDays' => $data['DamageDays'],
                ':DamageBaseDate' => $data['DamageBaseDate'],
                ':DamageInterestAmount' => $data['DamageInterestAmount'],
                ':ClaimFee' => $data['ClaimFee'],
                ':AdditionalClaimFee' => $data['AdditionalClaimFee'],
                ':PrintedFlg' => $data['PrintedFlg'],
                ':PrintedDate' => $data['PrintedDate'],
                ':MailFlg' => $data['MailFlg'],
                ':CreditTransferMailFlg' => $data['CreditTransferMailFlg'],
                ':ClaimFileOutputClass' => $data['ClaimFileOutputClass'],
                ':EnterpriseBillingCode' => $data['EnterpriseBillingCode'],
                ':ClaimAmount' => $data['ClaimAmount'],
                ':ClaimId' => $data['ClaimId'],
                ':ReissueClass' => isset($data['ReissueClass']) ? $data['ReissueClass'] : 0,
                ':ReissueRequestDate' => $data['ReissueRequestDate'],
                ':PrintedStatus' => isset($data['PrintedStatus']) ? $data['PrintedStatus'] : 0,
                ':MailRetryCount' => isset($data['MailRetryCount']) ? $data['MailRetryCount'] : 0,
                ':RegistDate' => date('Y-m-d H:i:s'),
                ':RegistId' => $data['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $data['UpdateId'],
                ':ValidFlg' => isset($data['ValidFlg']) ? $data['ValidFlg'] : 1,
        );

        $ri = $stm->execute($prm);
        $seq = $ri->getGeneratedValue();// 新規登録したPK値

        // 請求口座データ登録
        $this->addClaimAccountInfo($seq);

        return $seq;// 新規登録したPK値を戻す
	}

	/**
	 * 新しいレコードをインサートする。(2019-10-01 以降)
	 *
	 * @param int $orderSeq インサートする注文ID
	 * @param array $data インサートする連想配列
	 * @return プライマリキーのバリュー
	 */
	public function saveNew2($orderSeq, $data)
	{

	    $data["OrderSeq"] = $orderSeq;

	    // キケンだけど、、、とりあえずこんな方法で。
	    $ri = $this->findClaimHistory(array('OrderSeq' => $orderSeq));
	    $data["ClaimSeq"] = $ri->count() + 1;

	    $sql  = " INSERT INTO T_ClaimHistory (OrderSeq, ClaimSeq, ClaimDate, ClaimCpId, ClaimPattern, LimitDate, DamageDays, DamageBaseDate, DamageInterestAmount, ClaimFee, AdditionalClaimFee, PrintedFlg, PrintedDate, MailFlg, CreditTransferMailFlg, ClaimFileOutputClass, EnterpriseBillingCode, ClaimAmount, ClaimId, ReissueClass, ReissueRequestDate, PrintedStatus, MailRetryCount, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
	    $sql .= "   :OrderSeq ";
	    $sql .= " , :ClaimSeq ";
	    $sql .= " , :ClaimDate ";
	    $sql .= " , :ClaimCpId ";
	    $sql .= " , :ClaimPattern ";
	    $sql .= " , :LimitDate ";
	    $sql .= " , :DamageDays ";
	    $sql .= " , :DamageBaseDate ";
	    $sql .= " , :DamageInterestAmount ";
	    $sql .= " , :ClaimFee ";
	    $sql .= " , :AdditionalClaimFee ";
	    $sql .= " , :PrintedFlg ";
	    $sql .= " , :PrintedDate ";
	    $sql .= " , :MailFlg ";
	    $sql .= " , :CreditTransferMailFlg ";
	    $sql .= " , :ClaimFileOutputClass ";
	    $sql .= " , :EnterpriseBillingCode ";
	    $sql .= " , :ClaimAmount ";
	    $sql .= " , :ClaimId ";
	    $sql .= " , :ReissueClass ";
	    $sql .= " , :ReissueRequestDate ";
	    $sql .= " , :PrintedStatus ";
	    $sql .= " , :MailRetryCount ";
	    $sql .= " , :RegistDate ";
	    $sql .= " , :RegistId ";
	    $sql .= " , :UpdateDate ";
	    $sql .= " , :UpdateId ";
	    $sql .= " , :ValidFlg ";
	    $sql .= " )";

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':OrderSeq' => $data['OrderSeq'],
	            ':ClaimSeq' => $data['ClaimSeq'],
	            ':ClaimDate' => $data['ClaimDate'],
	            ':ClaimCpId' => $data['ClaimCpId'],
	            ':ClaimPattern' => $data['ClaimPattern'],
	            ':LimitDate' => $data['LimitDate'],
	            ':DamageDays' => $data['DamageDays'],
	            ':DamageBaseDate' => $data['DamageBaseDate'],
	            ':DamageInterestAmount' => $data['DamageInterestAmount'],
	            ':ClaimFee' => $data['ClaimFee'],
	            ':AdditionalClaimFee' => $data['AdditionalClaimFee'],
	            ':PrintedFlg' => $data['PrintedFlg'],
	            ':PrintedDate' => $data['PrintedDate'],
	            ':MailFlg' => $data['MailFlg'],
	            ':CreditTransferMailFlg' => $data['CreditTransferMailFlg'],
	            ':ClaimFileOutputClass' => $data['ClaimFileOutputClass'],
	            ':EnterpriseBillingCode' => $data['EnterpriseBillingCode'],
	            ':ClaimAmount' => $data['ClaimAmount'],
	            ':ClaimId' => $data['ClaimId'],
	            ':ReissueClass' => isset($data['ReissueClass']) ? $data['ReissueClass'] : 0,
	            ':ReissueRequestDate' => $data['ReissueRequestDate'],
	            ':PrintedStatus' => isset($data['PrintedStatus']) ? $data['PrintedStatus'] : 0,
	            ':MailRetryCount' => isset($data['MailRetryCount']) ? $data['MailRetryCount'] : 0,
	            ':RegistDate' => date('Y-m-d H:i:s'),
	            ':RegistId' => $data['RegistId'],
	            ':UpdateDate' => date('Y-m-d H:i:s'),
	            ':UpdateId' => $data['UpdateId'],
	            ':ValidFlg' => isset($data['ValidFlg']) ? $data['ValidFlg'] : 1,
	    );

	    $ri = $stm->execute($prm);
	    $seq = $ri->getGeneratedValue();// 新規登録したPK値

	    // 請求口座データ登録
	    $this->addClaimAccountInfo2($seq, array('ClaimPattern' => $data['ClaimPattern']));

	    return $seq;// 新規登録したPK値を戻す
	}

	/**
	 * 新しいレコードをインサートする。
	 *
	 * @param int $orderSeq インサートする注文ID
	 * @param array $data インサートする連想配列
	 * @param $logger
	 * @return プライマリキーのバリュー
	 */
	public function saveNewForBatch($orderSeq, $data, $logger)
	{

	    $data["OrderSeq"] = $orderSeq;

	    // キケンだけど、、、とりあえずこんな方法で。
	    $ri = $this->findClaimHistory(array('OrderSeq' => $orderSeq));
	    $data["ClaimSeq"] = $ri->count() + 1;

	    $sql  = " INSERT INTO T_ClaimHistory (OrderSeq, ClaimSeq, ClaimDate, ClaimCpId, ClaimPattern, LimitDate, DamageDays, DamageBaseDate, DamageInterestAmount, ClaimFee, AdditionalClaimFee, PrintedFlg, PrintedDate, MailFlg, CreditTransferMailFlg, ClaimFileOutputClass, EnterpriseBillingCode, ClaimAmount, ClaimId, ReissueClass, ReissueRequestDate, PrintedStatus, MailRetryCount, CreditTransferRequestStatus, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
	    $sql .= "   :OrderSeq ";
	    $sql .= " , :ClaimSeq ";
	    $sql .= " , :ClaimDate ";
	    $sql .= " , :ClaimCpId ";
	    $sql .= " , :ClaimPattern ";
	    $sql .= " , :LimitDate ";
	    $sql .= " , :DamageDays ";
	    $sql .= " , :DamageBaseDate ";
	    $sql .= " , :DamageInterestAmount ";
	    $sql .= " , :ClaimFee ";
	    $sql .= " , :AdditionalClaimFee ";
	    $sql .= " , :PrintedFlg ";
	    $sql .= " , :PrintedDate ";
	    $sql .= " , :MailFlg ";
	    $sql .= " , :CreditTransferMailFlg ";
	    $sql .= " , :ClaimFileOutputClass ";
	    $sql .= " , :EnterpriseBillingCode ";
	    $sql .= " , :ClaimAmount ";
	    $sql .= " , :ClaimId ";
	    $sql .= " , :ReissueClass ";
	    $sql .= " , :ReissueRequestDate ";
	    $sql .= " , :PrintedStatus ";
	    $sql .= " , :MailRetryCount ";
	    $sql .= " , :CreditTransferRequestStatus ";
	    $sql .= " , :RegistDate ";
	    $sql .= " , :RegistId ";
	    $sql .= " , :UpdateDate ";
	    $sql .= " , :UpdateId ";
	    $sql .= " , :ValidFlg ";
	    $sql .= " )";

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':OrderSeq' => $data['OrderSeq'],
	            ':ClaimSeq' => $data['ClaimSeq'],
	            ':ClaimDate' => $data['ClaimDate'],
	            ':ClaimCpId' => $data['ClaimCpId'],
	            ':ClaimPattern' => $data['ClaimPattern'],
	            ':LimitDate' => $data['LimitDate'],
	            ':DamageDays' => $data['DamageDays'],
	            ':DamageBaseDate' => $data['DamageBaseDate'],
	            ':DamageInterestAmount' => $data['DamageInterestAmount'],
	            ':ClaimFee' => $data['ClaimFee'],
	            ':AdditionalClaimFee' => $data['AdditionalClaimFee'],
	            ':PrintedFlg' => $data['PrintedFlg'],
	            ':PrintedDate' => $data['PrintedDate'],
	            ':MailFlg' => $data['MailFlg'],
	            ':CreditTransferMailFlg' => $data['CreditTransferMailFlg'],
	            ':ClaimFileOutputClass' => $data['ClaimFileOutputClass'],
	            ':EnterpriseBillingCode' => $data['EnterpriseBillingCode'],
	            ':ClaimAmount' => $data['ClaimAmount'],
	            ':ClaimId' => $data['ClaimId'],
	            ':ReissueClass' => isset($data['ReissueClass']) ? $data['ReissueClass'] : 0,
	            ':ReissueRequestDate' => $data['ReissueRequestDate'],
	            ':PrintedStatus' => isset($data['PrintedStatus']) ? $data['PrintedStatus'] : 0,
	            ':MailRetryCount' => isset($data['MailRetryCount']) ? $data['MailRetryCount'] : 0,
	            ':CreditTransferRequestStatus' => $data['CreditTransferRequestStatus'],
	            ':RegistDate' => date('Y-m-d H:i:s'),
	            ':RegistId' => $data['RegistId'],
	            ':UpdateDate' => date('Y-m-d H:i:s'),
	            ':UpdateId' => $data['UpdateId'],
	            ':ValidFlg' => isset($data['ValidFlg']) ? $data['ValidFlg'] : 1,
	    );

	    $ri = $stm->execute($prm);
	    $seq = $ri->getGeneratedValue();// 新規登録したPK値

	    // 請求口座データ登録
	    $this->addClaimAccountInfoForBatch($seq, $logger);

	    return $seq;// 新規登録したPK値を戻す
	}

	/**
	 * 新しいレコードをインサートする。(2019-10-01 以降)
	 *
	 * @param int $orderSeq インサートする注文ID
	 * @param array $data インサートする連想配列
	 * @param $logger
	 * @param array $option addClaimAccountInfoForBatch2に渡すデータを格納
	 * @return プライマリキーのバリュー
	 */
	public function saveNewForBatch2($orderSeq, $data, $logger, $option = null)
	{

	    $data["OrderSeq"] = $orderSeq;

	    // キケンだけど、、、とりあえずこんな方法で。
	    $ri = $this->findClaimHistory(array('OrderSeq' => $orderSeq));
	    $data["ClaimSeq"] = $ri->count() + 1;

	    $sql  = " INSERT INTO T_ClaimHistory (OrderSeq, ClaimSeq, ClaimDate, ClaimCpId, ClaimPattern, LimitDate, DamageDays, DamageBaseDate, DamageInterestAmount, ClaimFee, AdditionalClaimFee, PrintedFlg, PrintedDate, MailFlg, CreditTransferMailFlg, ClaimFileOutputClass, EnterpriseBillingCode, ClaimAmount, ClaimId, ReissueClass, ReissueRequestDate, PrintedStatus, MailRetryCount, CreditTransferRequestStatus, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
	    $sql .= "   :OrderSeq ";
	    $sql .= " , :ClaimSeq ";
	    $sql .= " , :ClaimDate ";
	    $sql .= " , :ClaimCpId ";
	    $sql .= " , :ClaimPattern ";
	    $sql .= " , :LimitDate ";
	    $sql .= " , :DamageDays ";
	    $sql .= " , :DamageBaseDate ";
	    $sql .= " , :DamageInterestAmount ";
	    $sql .= " , :ClaimFee ";
	    $sql .= " , :AdditionalClaimFee ";
	    $sql .= " , :PrintedFlg ";
	    $sql .= " , :PrintedDate ";
	    $sql .= " , :MailFlg ";
	    $sql .= " , :CreditTransferMailFlg ";
	    $sql .= " , :ClaimFileOutputClass ";
	    $sql .= " , :EnterpriseBillingCode ";
	    $sql .= " , :ClaimAmount ";
	    $sql .= " , :ClaimId ";
	    $sql .= " , :ReissueClass ";
	    $sql .= " , :ReissueRequestDate ";
	    $sql .= " , :PrintedStatus ";
	    $sql .= " , :MailRetryCount ";
	    $sql .= " , :CreditTransferRequestStatus ";
	    $sql .= " , :RegistDate ";
	    $sql .= " , :RegistId ";
	    $sql .= " , :UpdateDate ";
	    $sql .= " , :UpdateId ";
	    $sql .= " , :ValidFlg ";
	    $sql .= " )";

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':OrderSeq' => $data['OrderSeq'],
	            ':ClaimSeq' => $data['ClaimSeq'],
	            ':ClaimDate' => $data['ClaimDate'],
	            ':ClaimCpId' => $data['ClaimCpId'],
	            ':ClaimPattern' => $data['ClaimPattern'],
	            ':LimitDate' => $data['LimitDate'],
	            ':DamageDays' => $data['DamageDays'],
	            ':DamageBaseDate' => $data['DamageBaseDate'],
	            ':DamageInterestAmount' => $data['DamageInterestAmount'],
	            ':ClaimFee' => $data['ClaimFee'],
	            ':AdditionalClaimFee' => $data['AdditionalClaimFee'],
	            ':PrintedFlg' => $data['PrintedFlg'],
	            ':PrintedDate' => $data['PrintedDate'],
	            ':MailFlg' => $data['MailFlg'],
	            ':CreditTransferMailFlg' => $data['CreditTransferMailFlg'],
	            ':ClaimFileOutputClass' => $data['ClaimFileOutputClass'],
	            ':EnterpriseBillingCode' => $data['EnterpriseBillingCode'],
	            ':ClaimAmount' => $data['ClaimAmount'],
	            ':ClaimId' => $data['ClaimId'],
	            ':ReissueClass' => isset($data['ReissueClass']) ? $data['ReissueClass'] : 0,
	            ':ReissueRequestDate' => $data['ReissueRequestDate'],
	            ':PrintedStatus' => isset($data['PrintedStatus']) ? $data['PrintedStatus'] : 0,
	            ':MailRetryCount' => isset($data['MailRetryCount']) ? $data['MailRetryCount'] : 0,
	            ':CreditTransferRequestStatus' => $data['CreditTransferRequestStatus'],
	            ':RegistDate' => date('Y-m-d H:i:s'),
	            ':RegistId' => $data['RegistId'],
	            ':UpdateDate' => date('Y-m-d H:i:s'),
	            ':UpdateId' => $data['UpdateId'],
	            ':ValidFlg' => isset($data['ValidFlg']) ? $data['ValidFlg'] : 1,
	    );

	    $ri = $stm->execute($prm);
	    $seq = $ri->getGeneratedValue();// 新規登録したPK値

	    // 請求口座データ登録
	    $this->addClaimAccountInfoForBatch2($seq, $logger, $option);

	    return $seq;// 新規登録したPK値を戻す
	}

	/**
	 * 指定請求履歴に関連付けられたOEM請求口座データを生成する
	 *
	 * @access protected
	 * @param int $seq 請求履歴SEQ
	 * @return int OEM請求口座SEQ
	 */
	protected function addClaimAccountInfo($seq)
	{
        // モデルクラスからのロジック呼び出しはちょっとアレだが…
        $accountLogic = new LogicOemClaimAccount($this->_adapter);
        $accountLogic->setLogger(Application::getInstance()->logger);
        $accountLogic->insertClaimAccountInfo($seq);
	}

	/**
	 * 指定請求履歴に関連付けられたOEM請求口座データを生成する(2019-10-01 以降)
	 *
	 * @access protected
	 * @param int $seq 請求履歴SEQ
	 * @return int OEM請求口座SEQ
	 */
	protected function addClaimAccountInfo2($seq, $option = null)
	{
	    // モデルクラスからのロジック呼び出しはちょっとアレだが…
	    $accountLogic = new LogicOemClaimAccount($this->_adapter);
	    $accountLogic->setLogger(Application::getInstance()->logger);
	    $accountLogic->insertClaimAccountInfo2($seq, true, $option);
	}

	/**
	 * 指定請求履歴に関連付けられたOEM請求口座データを生成する
	 *
	 * @access protected
	 * @param int $seq 請求履歴SEQ
	 * @return int OEM請求口座SEQ
	 */
	protected function addClaimAccountInfoForBatch($seq, $logger)
	{
	    // モデルクラスからのロジック呼び出しはちょっとアレだが…
	    $accountLogic = new LogicOemClaimAccount($this->_adapter);
	    $accountLogic->setLogger($logger);
	    $accountLogic->insertClaimAccountInfo($seq);
	}

	/**
	 * 指定請求履歴に関連付けられたOEM請求口座データを生成する(2019-10-01 以降)
	 *
	 * @access protected
	 * @param int $seq 請求履歴SEQ
	 * @param $logger
	 * @param array $option insertClaimAccountInfo2に渡すデータを格納
	 * @return int OEM請求口座SEQ
	 */
	protected function addClaimAccountInfoForBatch2($seq, $logger, $option = null)
	{
	    // モデルクラスからのロジック呼び出しはちょっとアレだが…
	    $accountLogic = new LogicOemClaimAccount($this->_adapter);
	    $accountLogic->setLogger($logger);
	    $accountLogic->insertClaimAccountInfo2($seq, true, $option);
	}
	/**
	 * 指定されたレコードを更新する。
	 *
	 * @param array $data 更新内容
	 * @param int $seq 更新するSeq
	 */
	public function saveUpdate($data, $seq)
	{
        $sql = " SELECT * FROM T_ClaimHistory WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
        );

        $row = $stm->execute($prm)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_ClaimHistory ";
        $sql .= " SET ";
        $sql .= "     OrderSeq = :OrderSeq ";
        $sql .= " ,   ClaimSeq = :ClaimSeq ";
        $sql .= " ,   ClaimDate = :ClaimDate ";
        $sql .= " ,   ClaimCpId = :ClaimCpId ";
        $sql .= " ,   ClaimPattern = :ClaimPattern ";
        $sql .= " ,   LimitDate = :LimitDate ";
        $sql .= " ,   DamageDays = :DamageDays ";
        $sql .= " ,   DamageBaseDate = :DamageBaseDate ";
        $sql .= " ,   DamageInterestAmount = :DamageInterestAmount ";
        $sql .= " ,   ClaimFee = :ClaimFee ";
        $sql .= " ,   AdditionalClaimFee = :AdditionalClaimFee ";
        $sql .= " ,   PrintedFlg = :PrintedFlg ";
        $sql .= " ,   PrintedDate = :PrintedDate ";
        $sql .= " ,   MailFlg = :MailFlg ";
        $sql .= " ,   CreditTransferMailFlg = :CreditTransferMailFlg ";
        $sql .= " ,   ClaimFileOutputClass = :ClaimFileOutputClass ";
        $sql .= " ,   EnterpriseBillingCode = :EnterpriseBillingCode ";
        $sql .= " ,   ClaimAmount = :ClaimAmount ";
        $sql .= " ,   ClaimId = :ClaimId ";
        $sql .= " ,   ReissueClass = :ReissueClass ";
        $sql .= " ,   ReissueRequestDate = :ReissueRequestDate ";
        $sql .= " ,   PrintedStatus = :PrintedStatus ";
        $sql .= " ,   MailRetryCount = :MailRetryCount ";
        $sql .= " ,   PayeasyFee = :PayeasyFee ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
                ':OrderSeq' => $row['OrderSeq'],
                ':ClaimSeq' => $row['ClaimSeq'],
                ':ClaimDate' => $row['ClaimDate'],
                ':ClaimCpId' => $row['ClaimCpId'],
                ':ClaimPattern' => $row['ClaimPattern'],
                ':LimitDate' => $row['LimitDate'],
                ':DamageDays' => $row['DamageDays'],
                ':DamageBaseDate' => $row['DamageBaseDate'],
                ':DamageInterestAmount' => $row['DamageInterestAmount'],
                ':ClaimFee' => $row['ClaimFee'],
                ':AdditionalClaimFee' => $row['AdditionalClaimFee'],
                ':PrintedFlg' => $row['PrintedFlg'],
                ':PrintedDate' => $row['PrintedDate'],
                ':MailFlg' => $row['MailFlg'],
                ':CreditTransferMailFlg' => $row['CreditTransferMailFlg'],
                ':ClaimFileOutputClass' => $row['ClaimFileOutputClass'],
                ':EnterpriseBillingCode' => $row['EnterpriseBillingCode'],
                ':ClaimAmount' => $row['ClaimAmount'],
                ':ClaimId' => $row['ClaimId'],
                ':ReissueClass' => $row['ReissueClass'],
                ':ReissueRequestDate' => $row['ReissueRequestDate'],
                ':PrintedStatus' => $row['PrintedStatus'],
                ':MailRetryCount' => $row['MailRetryCount'],
                ':PayeasyFee' => $row['PayeasyFee'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
	}

	/**
	 * 指定されたレコードを更新する。(印刷-処理フラグ)
	 *
	 * @param array $data 更新内容
	 * @param int $seq 更新するSeq
	 */
	public function saveUpdatePrintedFlg($hisSeq)
	{
	    $sql  = " UPDATE T_ClaimHistory ";
	    $sql .= " SET ";
	    $sql .= "     PrintedFlg = :PrintedFlg ";
	    $sql .= " WHERE Seq = :Seq ";

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':Seq' => $hisSeq,
	            ':PrintedFlg' => 2,
	    );

	    return $stm->execute($prm);
	}

	/**
	 * 指定された注文Seq、請求パターンの最終の請求履歴を取得する。
	 *
	 * @param int $oseq 注文Seq
	 * @param int $claimPattern 請求パターン
	 * @return array
	 */
	public function getLatestClaimHistory($oseq, $claimPattern)
	{
        $query = "
			SELECT
			    *
			FROM
			    T_ClaimHistory
			WHERE
			    Seq = (SELECT
			                max(Seq)
			            FROM
			                T_ClaimHistory
			            WHERE
			                OrderSeq = :OrderSeq AND
			                ClaimPattern = :ClaimPattern AND
			                PrintedFlg = 1
			            )

			";

        $stm = $this->_adapter->query($query);

        $prm = array(
                ':OrderSeq' => $oseq,
                ':ClaimPattern' => $claimPattern,
        );

        $ri = $stm->execute($prm);
        if ($ri->count() > 0) {
            $row = $ri->current();

            $result['ClaimDate'] = $row['ClaimDate'];
            $result['LimitDate'] = $row['LimitDate'];
            $result['Additional'] = $row['DamageInterestAmount'] + $row['ClaimFee'] + $row['AdditionalClaimFee'];
        }
        else {
            $result['ClaimDate'] = '';
            $result['LimitDate'] = '';
            $result['Additional'] = '';
        }

        return $result;
	}

	/**
	 * 指定された注文Seqの再請求履歴を取得する。
	 *
	 * @param int $oseq 注文Seq
	 * @return ResultInterface
	 */
	public function getReClaimHistory($oseq)
	{
        $query = "
			SELECT
			    *
			FROM
			    T_ClaimHistory
			WHERE
			    OrderSeq = :OrderSeq AND
			    ClaimPattern > 1 AND
			    PrintedFlg = 1
			ORDER BY
			    Seq
			";

        $stm = $this->_adapter->query($query);

        $prm = array(
                ':OrderSeq' => $oseq,
        );

        return $stm->execute($prm);
	}

	/**
	 * 請求書発行メールの送信対象を取得する。
     *
     * @return ResultInterface
     * @see 未送信且つﾒｰﾙﾘﾄﾗｲ回数が5未満であれば抽出対象とする(20160108)
	 */
	public function getMailTargets()
	{
	    $sql = <<<EOQ
SELECT ch.*
FROM   T_ClaimHistory ch
       INNER JOIN T_Order o ON (o.OrderSeq = ch.OrderSeq)
       INNER JOIN T_Customer C ON C.OrderSeq = o.OrderSeq
	   INNER JOIN T_EnterpriseCustomer EC ON EC.EntCustSeq = C.EntCustSeq
       INNER JOIN T_ManagementCustomer MC ON MC.ManCustId = EC.ManCustId
WHERE  1 = 1
AND    ch.PrintedFlg = 1
AND    (ch.MailFlg = 0 AND ch.MailRetryCount < 5)
AND    ch.ValidFlg = 1
AND    o.ValidFlg = 1
AND    (IFNULL(o.MailClaimStopFlg, 0) = 0 AND IFNULL(MC.RemindStopFlg, 0) = 0)
AND    o.DataStatus < 91
EOQ;
        return $this->_adapter->query($sql)->execute(null);
	}

	/**
	 * 口振請求書発行案内メールの送信対象を取得する。
	 *
	 * @return ResultInterface
	 * @see 未送信且つﾒｰﾙﾘﾄﾗｲ回数が5未満であれば抽出対象とする
	 */
	public function getMailTargetsCreditTransferInfo()
	{
	    $sql = <<<EOQ
SELECT ch.*
FROM   T_ClaimHistory ch
	    INNER JOIN T_Order o ON (o.OrderSeq = ch.OrderSeq)
WHERE  1 = 1
	    AND    (ch.CreditTransferMailFlg = 0 AND ch.CreditMailRetryCount < 5)
	    AND    ch.ValidFlg = 1
	    AND    o.ValidFlg = 1
	    AND    o.DataStatus < 91
UNION
SELECT ch.*
FROM   T_ClaimHistory ch
	    INNER JOIN T_Order o ON (o.OrderSeq = ch.OrderSeq)
WHERE  1 = 1
	    AND    (ch.ZeroAmountClaimMailFlg = 0 AND ch.CreditMailRetryCount < 5)
	    AND    ch.ValidFlg = 1
	    AND    o.ValidFlg = 1
	    AND    o.DataStatus = 91
EOQ;
	    return $this->_adapter->query($sql)->execute(null);
	}

	/**
	 * 指定の注文Seqの請求書発行メール送信対象データを取得する
	 *
	 * @param int $oseq 注文Seq
	 * @return ResultInterface
	 */
	public function getMailTagetByOrderSeq($oseq)
	{
        $sql = " SELECT * FROM T_ClaimHistory WHERE PrintedFlg = 1 AND MailFlg = 0 AND OrderSeq = :OrderSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $oseq,
        );

        return $stm->execute($prm);
	}

	/**
	 * 指定の注文Seqの口振請求書発行案内メール送信対象データを取得する
	 *
	 * @param int $oseq
	 * @return ResultInterface
	 */
	public function findDataInfoMail($oseq)
	{
	    $sql = " SELECT * FROM T_ClaimHistory WHERE CreditTransferMailFlg = 0 AND OrderSeq = :OrderSeq UNION SELECT * FROM T_ClaimHistory WHERE ZeroAmountClaimMailFlg = 0 AND OrderSeq = :OrderSeq ";
	    $stm = $this->_adapter->query($sql);
	    $prm = array(
	            ':OrderSeq' => $oseq,
	    );

	    return $stm->execute($prm);
	}

	/**
	 * 請求書発行メールを送信済みにする。
	 *
	 * @param int $seq 請求履歴Seq
	 * @param $opId 担当者
	 */
	public function setMailed($seq, $opId)
	{
 		$this->saveUpdate(array('MailFlg' => 1, 'UpdateId' => $opId), $seq);
	}

	/**
	 * 指定の注文Seqの請求書発行済みメール送信対象データを取得する
	 *
	 * @param int $oseq 注文Seq
	 * @return ResultInterface
	 */
	public function getReturnMailTagetByOrderSeq($oseq)
	{
        $query = "
			SELECT
			    *
			FROM
			    T_ClaimHistory
			WHERE
				OrderSeq = :OrderSeq AND
			    ClaimSeq = (SELECT
			                MAX(ClaimSeq)
			            FROM
			                T_ClaimHistory
			            WHERE
			                OrderSeq = :OrderSeq AND
			                PrintedFlg = 1
			            )

			";
        $stm = $this->_adapter->query($query);

        $prm = array(
                ':OrderSeq' => $oseq,
        );

        return $stm->execute($prm);
	}

	/**
	 * 指定の同梱ツール利用事業者の未印刷データをすべて取得する
	 *
	 * @param mixed $enterprise_id 事業者ID
	 * @return ResultInterface
	 */
	public function findForSelfBillingByEnterprise($enterprise_id)
	{
	    $q = <<<EOQ
SELECT H.*
FROM
	T_ClaimHistory H INNER JOIN
	T_Order O ON O.OrderSeq = H.OrderSeq INNER JOIN
	T_Enterprise E ON E.EnterpriseId = O.EnterpriseId
WHERE
	E.EnterpriseId = :enterprise_id AND
	E.SelfBillingMode > 0 AND
	O.Cnl_Status = 0 AND
	H.ClaimPattern = 1 AND
	H.PrintedFlg = 0 AND
	H.EnterpriseBillingCode IS NOT NULL
ORDER BY
	H.OrderSeq
EOQ;
		$stmt = $this->_adapter->query($q);
        $ri = $stmt->execute(array('enterprise_id' => $enterprise_id));
        return $ri;
	}

	/**
	 * 指定の同梱ツール利用事業者の、指定アクセスキーに一致する未印刷データを取得する
	 *
	 * @param mixed $enterprise_id 事業者ID
	 * @param string $access_key 同梱ツールアクセスキー
	 * @return ResultInterface
	 */
	public function findForSelfBillingByEnterpriseIdAndAccessKey($enterprise_id, $access_key)
	{
	    $q = <<<EOQ
SELECT H.*
FROM
	T_ClaimHistory H INNER JOIN
	T_Order O ON O.OrderSeq = H.OrderSeq INNER JOIN
	T_Enterprise E ON E.EnterpriseId = O.EnterpriseId
WHERE
	E.EnterpriseId = :enterprise_id AND
	E.SelfBillingMode <> 0 AND
	O.Cnl_Status = 0 AND
	H.ClaimPattern = 1 AND
	H.PrintedFlg = 0 AND
	H.EnterpriseBillingCode = :access_key
ORDER BY
	H.OrderSeq
EOQ;
        $stmt = $this->_adapter->query($q);
        $ri = $stmt->execute(array('enterprise_id' => $enterprise_id, 'access_key' => $access_key));
        return $ri;
	}

	/**
	 * 指定の注文に関連付けられた、同梱ツール経由の請求履歴を取得する
	 *
	 * @param int $oseq 注文SEQ
	 * @return ResultInterface
	 */
	public function findForSelfBillingByOrderSeq($oseq)
	{
	    $q = <<<EOQ
SELECT
	*
FROM
	T_ClaimHistory
WHERE
	OrderSeq = :OrderSeq AND
	EnterpriseBillingCode IS NOT NULL
ORDER BY
	Seq
EOQ;
        return $this->_adapter->query($q)->execute(array(':OrderSeq' => $oseq));
	}

	/**
	 * 指定請求履歴の消費税額計算に必要な商品購入代金を取得する
	 *
	 * @param int $seq 請求履歴SEQ
	 * @return int 商品購入代金。注文利用額から送料・店舗手数料を差し引いた額
	 */
	public function getTotalPrice($seq) {
	    $q = <<<EOQ
SELECT
	(
	 SELECT SUM(IFNULL(SumMoney, 0)) FROM T_OrderItems
	 WHERE OrderSeq = h.OrderSeq AND DataClass = 1
	) AS TotalPrice
FROM
	T_ClaimHistory h
WHERE
	h.Seq = :Seq
EOQ;
        return (int)$this->_adapter->query($q)->execute(array(':Seq' => $seq))->current()['TotalPrice'];
	}

	/**
	 * 指定請求履歴の請求金額合計を取得する
	 *
	 * @param int $seq 請求履歴SEQ
	 * @return int 請求金額合計（＝利用額＋遅延損害金＋請求手数料）
	 */
	public function getClaimAmount($seq) {
	    $q = <<<EOQ
SELECT
	ClaimAmount
FROM
	T_ClaimHistory h
WHERE
	h.Seq = :Seq
EOQ;
        return (int)$this->_adapter->query($q)->execute(array(':Seq' => $seq))->current()['ClaimAmount'];
	}

	/**
	 * 指定注文で、期限日から指定日数以上経過している再請求7の履歴を検索する
	 *
	 * @param int $oseq 注文SEQ
	 * @param null | int $expire_days 超過基準日数。省略時は65
	 * @return array | null 指定注文の指定日数以上延滞している再請求7の請求履歴のリスト
	 */
	public function findExpiredClaimPattern9($oseq, $expire_days = 65)
	{
        $sql = " SELECT * FROM T_ClaimHistory WHERE OrderSeq = :OrderSeq AND ClaimPattern = 9 AND PrintedFlg = 1 AND LimitDate < (CURDATE() - INTERVAL :Interval DAY) ";
        $ri = $this->_adapter->query($sql)->execute(array(':OrderSeq' => $oseq, ':Interval' => (int)$expire_days));

        return ($ri->count() > 0) ? ResultInterfaceToArray($ri) : array();
	}

	/**
	 * 指定された注文Seq、請求履歴の最も古いレコードを取得する。
	 *
	 * @param int $oseq 注文Seq
	 * @param int $claimPattern 請求パターン
	 * @return array
	 */
	public function getOldestClaimHistory($oseq)
	{
	    $query = "
			SELECT
			    *
			FROM
			    T_ClaimHistory
			WHERE
			    ClaimDate = (SELECT
			                MIN(ClaimDate)
			            FROM
			                T_ClaimHistory
			            WHERE
			                OrderSeq = :OrderSeq AND
			                ClaimPattern = 1
			            )
              AND OrderSeq = :BaseOrderSeq
			ORDER BY
			    Seq
			";

	    $stm = $this->_adapter->query($query);

	    $prm = array(
	            ':OrderSeq' => $oseq,
	    		':BaseOrderSeq' => $oseq,
	    );

	    $result = $stm->execute($prm)->current();

	    return $result;
	}

    /**
     * 新しいレコードをインサートする(基本形)
     *
     * @param array $data インサートする連想配列
     * @return プライマリキーのバリュー
     */
    public function saveNewEx($data)
    {
        $sql  = " INSERT INTO T_ClaimHistory (OrderSeq, ClaimSeq, ClaimDate, ClaimCpId, ClaimPattern, LimitDate, DamageDays, DamageBaseDate, DamageInterestAmount, ClaimFee, AdditionalClaimFee, PrintedFlg, PrintedDate, MailFlg, CreditTransferMailFlg, ClaimFileOutputClass, EnterpriseBillingCode, ClaimAmount, ClaimId, ReissueClass, ReissueRequestDate, PrintedStatus, MailRetryCount, CreditTransferRequestStatus, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :OrderSeq ";
        $sql .= " , :ClaimSeq ";
        $sql .= " , :ClaimDate ";
        $sql .= " , :ClaimCpId ";
        $sql .= " , :ClaimPattern ";
        $sql .= " , :LimitDate ";
        $sql .= " , :DamageDays ";
        $sql .= " , :DamageBaseDate ";
        $sql .= " , :DamageInterestAmount ";
        $sql .= " , :ClaimFee ";
        $sql .= " , :AdditionalClaimFee ";
        $sql .= " , :PrintedFlg ";
        $sql .= " , :PrintedDate ";
        $sql .= " , :MailFlg ";
        $sql .= " , :CreditTransferMailFlg ";
        $sql .= " , :ClaimFileOutputClass ";
        $sql .= " , :EnterpriseBillingCode ";
        $sql .= " , :ClaimAmount ";
        $sql .= " , :ClaimId ";
        $sql .= " , :ReissueClass ";
        $sql .= " , :ReissueRequestDate ";
        $sql .= " , :PrintedStatus ";
        $sql .= " , :MailRetryCount ";
        $sql .= " , :CreditTransferRequestStatus ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $data['OrderSeq'],
                ':ClaimSeq' => $data['ClaimSeq'],
                ':ClaimDate' => $data['ClaimDate'],
                ':ClaimCpId' => $data['ClaimCpId'],
                ':ClaimPattern' => $data['ClaimPattern'],
                ':LimitDate' => $data['LimitDate'],
                ':DamageDays' => $data['DamageDays'],
                ':DamageBaseDate' => $data['DamageBaseDate'],
                ':DamageInterestAmount' => $data['DamageInterestAmount'],
                ':ClaimFee' => $data['ClaimFee'],
                ':AdditionalClaimFee' => $data['AdditionalClaimFee'],
                ':PrintedFlg' => $data['PrintedFlg'],
                ':PrintedDate' => $data['PrintedDate'],
                ':MailFlg' => $data['MailFlg'],
                ':CreditTransferMailFlg' => $data['CreditTransferMailFlg'],
                ':ClaimFileOutputClass' => $data['ClaimFileOutputClass'],
                ':EnterpriseBillingCode' => $data['EnterpriseBillingCode'],
                ':ClaimAmount' => $data['ClaimAmount'],
                ':ClaimId' => $data['ClaimId'],
                ':ReissueClass' => isset($data['ReissueClass']) ? $data['ReissueClass'] : 0,
                ':ReissueRequestDate' => $data['ReissueRequestDate'],
                ':PrintedStatus' => isset($data['PrintedStatus']) ? $data['PrintedStatus'] : 0,
                ':MailRetryCount' => isset($data['MailRetryCount']) ? $data['MailRetryCount'] : 0,
                ':CreditTransferRequestStatus' => $data['CreditTransferRequestStatus'],
                ':RegistDate' => date('Y-m-d H:i:s'),
                ':RegistId' => $data['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $data['UpdateId'],
                ':ValidFlg' => isset($data['ValidFlg']) ? $data['ValidFlg'] : 1,
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
    }
}
