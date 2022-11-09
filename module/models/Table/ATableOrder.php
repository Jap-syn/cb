<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * AT_Order(注文_会計)テーブルへのアダプタ
 */
class ATableOrder
{
    protected $_name = 'AT_Order';
    protected $_primary = array('OrderSeq');
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
     * 注文_会計データを取得する
     *
     * @param int $orderSeq 注文SEQ
     * @return ResultInterface
     */
    public function find($orderSeq)
    {
        $sql = " SELECT * FROM AT_Order WHERE OrderSeq = :OrderSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $orderSeq,
        );

        return $stm->execute($prm);
    }

    /**
     * 新しいレコードをインサートする。
     *
     * @param array $data インサートする連想配列
     * @return プライマリキーのバリュー
     */
    public function saveNew($data)
    {
        $sql  = " INSERT INTO AT_Order (OrderSeq, Dmg_DailySummaryFlg, RepayTCFlg, RepayPendingFlg, DefectFlg, DefectInvisibleFlg, DefectNote, DefectCancelPlanDate, StopSendMailConfirmJournalFlg, AutoJudgeNgReasonCode, ManualJudgeNgReasonCode, NgNoGuaranteeChangeDate, NgButtonFlg, NoGuaranteeChangeLimitDay, ResumeFlg, CreditTransferRequestFlg) VALUES (";
        $sql .= "   :OrderSeq ";
        $sql .= " , :Dmg_DailySummaryFlg ";
        $sql .= " , :RepayTCFlg ";
        $sql .= " , :RepayPendingFlg ";
        $sql .= " , :DefectFlg ";
        $sql .= " , :DefectInvisibleFlg ";
        $sql .= " , :DefectNote ";
        $sql .= " , :DefectCancelPlanDate ";
        $sql .= " , :StopSendMailConfirmJournalFlg ";
        $sql .= " , :AutoJudgeNgReasonCode ";
        $sql .= " , :ManualJudgeNgReasonCode ";
        $sql .= " , :NgNoGuaranteeChangeDate ";
        $sql .= " , :NgButtonFlg";
        $sql .= " , :NoGuaranteeChangeLimitDay";
        $sql .= " , :ResumeFlg";
        $sql .= " , :CreditTransferRequestFlg";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $data['OrderSeq'],
                ':Dmg_DailySummaryFlg' => isset($data['Dmg_DailySummaryFlg']) ? $data['Dmg_DailySummaryFlg'] : 0,
                ':RepayTCFlg' => isset($data['RepayTCFlg']) ? $data['RepayTCFlg'] : 0,
                ':RepayPendingFlg' => isset($data['RepayPendingFlg']) ? $data['RepayPendingFlg'] : 0,
                ':DefectFlg' => isset($data['DefectFlg']) ? $data['DefectFlg'] : 0,
                ':DefectInvisibleFlg' => isset($data['DefectInvisibleFlg']) ? $data['DefectInvisibleFlg'] : 0,
                ':DefectNote' => $data['DefectNote'],
                ':DefectCancelPlanDate' => $data['DefectCancelPlanDate'],
                ':StopSendMailConfirmJournalFlg' => isset($data['StopSendMailConfirmJournalFlg']) ? $data['StopSendMailConfirmJournalFlg'] : 0,
                ':AutoJudgeNgReasonCode' => $data['AutoJudgeNgReasonCode'],
                ':ManualJudgeNgReasonCode' => $data['ManualJudgeNgReasonCode'],
                ':NgNoGuaranteeChangeDate' => $data['NgNoGuaranteeChangeDate'],
                ':NgButtonFlg' => $data['NgButtonFlg'],
                ':NoGuaranteeChangeLimitDay' => $data['NoGuaranteeChangeLimitDay'],
                ':ResumeFlg' => isset($data['ResumeFlg']) ? $data['ResumeFlg'] : 0,
                ':CreditTransferRequestFlg' => isset($data['CreditTransferRequestFlg']) ? $data['CreditTransferRequestFlg'] : 0,
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
    }

    /**
     * 指定されたレコードを更新する。
     *
     * @param array $data 更新内容
     * @param int $orderSeq 注文SEQ
     * @return ResultInterface
     */
    public function saveUpdate($data, $orderSeq)
    {
        $row = $this->find($orderSeq)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE AT_Order ";
        $sql .= " SET ";
        $sql .= "     Dmg_DailySummaryFlg = :Dmg_DailySummaryFlg ";
        $sql .= " ,   RepayTCFlg = :RepayTCFlg ";
        $sql .= " ,   RepayPendingFlg = :RepayPendingFlg ";
        $sql .= " ,   DefectFlg = :DefectFlg ";
        $sql .= " ,   DefectInvisibleFlg = :DefectInvisibleFlg ";
        $sql .= " ,   DefectNote = :DefectNote ";
        $sql .= " ,   DefectCancelPlanDate = :DefectCancelPlanDate ";
        $sql .= " ,   StopSendMailConfirmJournalFlg = :StopSendMailConfirmJournalFlg ";
        $sql .= " ,   AutoJudgeNgReasonCode = :AutoJudgeNgReasonCode ";
        $sql .= " ,   ManualJudgeNgReasonCode = :ManualJudgeNgReasonCode ";
        $sql .= " ,   NgNoGuaranteeChangeDate = :NgNoGuaranteeChangeDate ";
        $sql .= " ,   NgButtonFlg = :NgButtonFlg ";
        $sql .= " ,   NoGuaranteeChangeLimitDay = :NoGuaranteeChangeLimitDay ";
        $sql .= " ,   ResumeFlg = :ResumeFlg ";
        $sql .= " ,   CreditTransferRequestFlg = :CreditTransferRequestFlg ";
        $sql .= " WHERE OrderSeq = :OrderSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $orderSeq,
                ':Dmg_DailySummaryFlg' => $row['Dmg_DailySummaryFlg'],
                ':RepayTCFlg' => $row['RepayTCFlg'],
                ':RepayPendingFlg' => $row['RepayPendingFlg'],
                ':DefectFlg' => $row['DefectFlg'],
                ':DefectInvisibleFlg' => $row['DefectInvisibleFlg'],
                ':DefectNote' => $row['DefectNote'],
                ':DefectCancelPlanDate' => $row['DefectCancelPlanDate'],
                ':StopSendMailConfirmJournalFlg' => $row['StopSendMailConfirmJournalFlg'],
                ':AutoJudgeNgReasonCode' => $row['AutoJudgeNgReasonCode'],
                ':ManualJudgeNgReasonCode' => $row['ManualJudgeNgReasonCode'],
                ':NgNoGuaranteeChangeDate' => $row['NgNoGuaranteeChangeDate'],
                ':NgButtonFlg' => $row['NgButtonFlg'],
                ':NoGuaranteeChangeLimitDay' => $row['NoGuaranteeChangeLimitDay'],
                ':ResumeFlg' => $row['ResumeFlg'],
                ':CreditTransferRequestFlg' => $row['CreditTransferRequestFlg'],
        );

        return $stm->execute($prm);
    }

    /**
     * 指定されたレコードを更新する。
     *
     * @param array $data 更新内容
     * @param int $orderSeq 注文SEQ
     * @return ResultInterface
     */
    public function saveUpdateExtraPay($data, $orderSeq)
    {
        $row = $this->find($orderSeq)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE AT_Order ";
        $sql .= " SET ";
        $sql .= "     ExtraPayType = :ExtraPayType ";
        $sql .= " ,   ExtraPayKey = :ExtraPayKey ";
        $sql .= " ,   ExtraPayNote = :ExtraPayNote ";
        $sql .= " WHERE OrderSeq = :OrderSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $orderSeq,
                ':ExtraPayType' => $row['ExtraPayType'],
                ':ExtraPayKey' => $row['ExtraPayKey'],
                ':ExtraPayNote' => $row['ExtraPayNote'],
        );

        return $stm->execute($prm);
    }


    public function saveUpdateExtraPayNote($data, $orderSeq)
    {
        $row = $this->find($orderSeq)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE AT_Order ";
        $sql .= " SET ";
        $sql .= "     ExtraPayType = :ExtraPayType ";
        $sql .= " ,   ExtraPayNote = :ExtraPayNote ";
        $sql .= " WHERE OrderSeq = :OrderSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
            ':OrderSeq' => $orderSeq,
            ':ExtraPayType' => $row['ExtraPayType'],
            ':ExtraPayNote' => $row['ExtraPayNote'],
        );

        return $stm->execute($prm);
    }
}
