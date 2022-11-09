<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_EnterpriseTotal(加盟店別集計)テーブルへのアダプタ
 */
class TableEnterpriseTotal
{
    protected $_name = 'T_EnterpriseTotal';
    protected $_primary = array('EnterpriseId');
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
     * 加盟店別集計データを取得する(有効フラグ＝有効データに限る)
     *
     * @param int $enterpriseId 加盟店ID
     * @return ResultInterface
     */
    public function find($enterpriseId)
    {
        $sql = " SELECT * FROM T_EnterpriseTotal WHERE ValidFlg = 1 AND EnterpriseId = :EnterpriseId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EnterpriseId' => $enterpriseId,
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
        $sql  = " INSERT INTO T_EnterpriseTotal (EnterpriseId, NpCalcDate, NpMolecule3, NpDenominator3, NpRate3, NpMoleculeAll, NpDenominatorAll, NpRateAll, NpGuaranteeMolecule3, NpNoGuaranteeMolecule3, NpGuaranteeRate3, NpNoGuaranteeRate3, NpGuaranteeMoleculeAll, NpNoGuaranteeMoleculeAll, NpGuaranteeRateAll, NpNoGuaranteeRateAll, NpNgMolecule3, NpNgDenominator3, NpNgRate3, NpNgMoleculeAll, NpNgDenominatorAll, NpNgRateAll, NpOrderCountTotal, NpOrderCountOk, NpAverageAmountTotal, NpAverageAmountOk, Profitability, ArrivalConfirmCount, CancelRate, TransAmount, ClaimAmountTotal, ReceiptAmountTotal, ClaimedBalance, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :EnterpriseId ";
        $sql .= " , :NpCalcDate ";
        $sql .= " , :NpMolecule3 ";
        $sql .= " , :NpDenominator3 ";
        $sql .= " , :NpRate3 ";
        $sql .= " , :NpMoleculeAll ";
        $sql .= " , :NpDenominatorAll ";
        $sql .= " , :NpRateAll ";
        $sql .= " , :NpGuaranteeMolecule3 ";
        $sql .= " , :NpNoGuaranteeMolecule3 ";
        $sql .= " , :NpGuaranteeRate3 ";
        $sql .= " , :NpNoGuaranteeRate3 ";
        $sql .= " , :NpGuaranteeMoleculeAll ";
        $sql .= " , :NpNoGuaranteeMoleculeAll ";
        $sql .= " , :NpGuaranteeRateAll ";
        $sql .= " , :NpNoGuaranteeRateAll ";
        $sql .= " , :NpNgMolecule3 ";
        $sql .= " , :NpNgDenominator3 ";
        $sql .= " , :NpNgRate3 ";
        $sql .= " , :NpNgMoleculeAll ";
        $sql .= " , :NpNgDenominatorAll ";
        $sql .= " , :NpNgRateAll ";
        $sql .= " , :NpOrderCountTotal ";
        $sql .= " , :NpOrderCountOk ";
        $sql .= " , :NpAverageAmountTotal ";
        $sql .= " , :NpAverageAmountOk ";
        $sql .= " , :Profitability ";
        $sql .= " , :ArrivalConfirmCount ";
        $sql .= " , :CancelRate ";
        $sql .= " , :TransAmount ";
        $sql .= " , :ClaimAmountTotal ";
        $sql .= " , :ReceiptAmountTotal ";
        $sql .= " , :ClaimedBalance ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EnterpriseId' => $data['EnterpriseId'],
                ':NpCalcDate' => $data['NpCalcDate'],
                ':NpMolecule3' => isset($data['NpMolecule3']) ? $data['NpMolecule3'] : 0,
                ':NpDenominator3' => isset($data['NpDenominator3']) ? $data['NpDenominator3'] : 0,
                ':NpRate3' => isset($data['NpRate3']) ? $data['NpRate3'] : 0,
                ':NpMoleculeAll' => isset($data['NpMoleculeAll']) ? $data['NpMoleculeAll'] : 0,
                ':NpDenominatorAll' => isset($data['NpDenominatorAll']) ? $data['NpDenominatorAll'] : 0,
                ':NpRateAll' => isset($data['NpRateAll']) ? $data['NpRateAll'] : 0,
                ':NpGuaranteeMolecule3' => isset($data['NpGuaranteeMolecule3']) ? $data['NpGuaranteeMolecule3'] : 0,
                ':NpNoGuaranteeMolecule3' => isset($data['NpNoGuaranteeMolecule3']) ? $data['NpNoGuaranteeMolecule3'] : 0,
                ':NpGuaranteeRate3' => isset($data['NpGuaranteeRate3']) ? $data['NpGuaranteeRate3'] : 0,
                ':NpNoGuaranteeRate3' => isset($data['NpNoGuaranteeRate3']) ? $data['NpNoGuaranteeRate3'] : 0,
                ':NpGuaranteeMoleculeAll' => isset($data['NpGuaranteeMoleculeAll']) ? $data['NpGuaranteeMoleculeAll'] : 0,
                ':NpNoGuaranteeMoleculeAll' => isset($data['NpNoGuaranteeMoleculeAll']) ? $data['NpNoGuaranteeMoleculeAll'] : 0,
                ':NpGuaranteeRateAll' => isset($data['NpGuaranteeRateAll']) ? $data['NpGuaranteeRateAll'] : 0,
                ':NpNoGuaranteeRateAll' => isset($data['NpNoGuaranteeRateAll']) ? $data['NpNoGuaranteeRateAll'] : 0,
                ':NpNgMolecule3' => isset($data['NpNgMolecule3']) ? $data['NpNgMolecule3'] : 0,
                ':NpNgDenominator3' => isset($data['NpNgDenominator3']) ? $data['NpNgDenominator3'] : 0,
                ':NpNgRate3' => isset($data['NpNgRate3']) ? $data['NpNgRate3'] : 0,
                ':NpNgMoleculeAll' => isset($data['NpNgMoleculeAll']) ? $data['NpNgMoleculeAll'] : 0,
                ':NpNgDenominatorAll' => isset($data['NpNgDenominatorAll']) ? $data['NpNgDenominatorAll'] : 0,
                ':NpNgRateAll' => isset($data['NpNgRateAll']) ? $data['NpNgRateAll'] : 0,
                ':NpOrderCountTotal' => isset($data['NpOrderCountTotal']) ? $data['NpOrderCountTotal'] : 0,
                ':NpOrderCountOk' => isset($data['NpOrderCountOk']) ? $data['NpOrderCountOk'] : 0,
                ':NpAverageAmountTotal' => isset($data['NpAverageAmountTotal']) ? $data['NpAverageAmountTotal'] : 0,
                ':NpAverageAmountOk' => isset($data['NpAverageAmountOk']) ? $data['NpAverageAmountOk'] : 0,
                ':Profitability' => isset($data['Profitability']) ? $data['Profitability'] : 0,
                ':ArrivalConfirmCount' => isset($data['ArrivalConfirmCount']) ? $data['ArrivalConfirmCount'] : 0,
                ':CancelRate' => isset($data['CancelRate']) ? $data['CancelRate'] : 0,
                ':TransAmount' => isset($data['TransAmount']) ? $data['TransAmount'] : 0,
                ':ClaimAmountTotal' => isset($data['ClaimAmountTotal']) ? $data['ClaimAmountTotal'] : 0,
                ':ReceiptAmountTotal' => isset($data['ReceiptAmountTotal']) ? $data['ReceiptAmountTotal'] : 0,
                ':ClaimedBalance' => isset($data['ClaimedBalance']) ? $data['ClaimedBalance'] : 0,
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
     * @param int $enterpriseId 加盟店ID
     * @return ResultInterface
     */
    public function saveUpdate($data, $enterpriseId)
    {
        $row = $this->find($enterpriseId)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_EnterpriseTotal ";
        $sql .= " SET ";
        $sql .= "     NpCalcDate = :NpCalcDate ";
        $sql .= " ,   NpMolecule3 = :NpMolecule3 ";
        $sql .= " ,   NpDenominator3 = :NpDenominator3 ";
        $sql .= " ,   NpRate3 = :NpRate3 ";
        $sql .= " ,   NpMoleculeAll = :NpMoleculeAll ";
        $sql .= " ,   NpDenominatorAll = :NpDenominatorAll ";
        $sql .= " ,   NpRateAll = :NpRateAll ";
        $sql .= " ,   NpGuaranteeMolecule3 = :NpGuaranteeMolecule3 ";
        $sql .= " ,   NpNoGuaranteeMolecule3 = :NpNoGuaranteeMolecule3 ";
        $sql .= " ,   NpGuaranteeRate3 = :NpGuaranteeRate3 ";
        $sql .= " ,   NpNoGuaranteeRate3 = :NpNoGuaranteeRate3 ";
        $sql .= " ,   NpGuaranteeMoleculeAll = :NpGuaranteeMoleculeAll ";
        $sql .= " ,   NpNoGuaranteeMoleculeAll = :NpNoGuaranteeMoleculeAll ";
        $sql .= " ,   NpGuaranteeRateAll = :NpGuaranteeRateAll ";
        $sql .= " ,   NpNoGuaranteeRateAll = :NpNoGuaranteeRateAll ";
        $sql .= " ,   NpNgMolecule3 = :NpNgMolecule3 ";
        $sql .= " ,   NpNgDenominator3 = :NpNgDenominator3 ";
        $sql .= " ,   NpNgRate3 = :NpNgRate3 ";
        $sql .= " ,   NpNgMoleculeAll = :NpNgMoleculeAll ";
        $sql .= " ,   NpNgDenominatorAll = :NpNgDenominatorAll ";
        $sql .= " ,   NpNgRateAll = :NpNgRateAll ";
        $sql .= " ,   NpOrderCountTotal = :NpOrderCountTotal ";
        $sql .= " ,   NpOrderCountOk = :NpOrderCountOk ";
        $sql .= " ,   NpAverageAmountTotal = :NpAverageAmountTotal ";
        $sql .= " ,   NpAverageAmountOk = :NpAverageAmountOk ";
        $sql .= " ,   Profitability = :Profitability ";
        $sql .= " ,   ArrivalConfirmCount = :ArrivalConfirmCount ";
        $sql .= " ,   CancelRate = :CancelRate ";
        $sql .= " ,   TransAmount = :TransAmount ";
        $sql .= " ,   ClaimAmountTotal = :ClaimAmountTotal ";
        $sql .= " ,   ReceiptAmountTotal = :ReceiptAmountTotal ";
        $sql .= " ,   ClaimedBalance = :ClaimedBalance ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE EnterpriseId = :EnterpriseId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EnterpriseId' => $enterpriseId,
                ':NpCalcDate' => $row['NpCalcDate'],
                ':NpMolecule3' => $row['NpMolecule3'],
                ':NpDenominator3' => $row['NpDenominator3'],
                ':NpRate3' => $row['NpRate3'],
                ':NpMoleculeAll' => $row['NpMoleculeAll'],
                ':NpDenominatorAll' => $row['NpDenominatorAll'],
                ':NpRateAll' => $row['NpRateAll'],
                ':NpGuaranteeMolecule3' => $row['NpGuaranteeMolecule3'],
                ':NpNoGuaranteeMolecule3' => $row['NpNoGuaranteeMolecule3'],
                ':NpGuaranteeRate3' => $row['NpGuaranteeRate3'],
                ':NpNoGuaranteeRate3' => $row['NpNoGuaranteeRate3'],
                ':NpGuaranteeMoleculeAll' => $row['NpGuaranteeMoleculeAll'],
                ':NpNoGuaranteeMoleculeAll' => $row['NpNoGuaranteeMoleculeAll'],
                ':NpGuaranteeRateAll' => $row['NpGuaranteeRateAll'],
                ':NpNoGuaranteeRateAll' => $row['NpNoGuaranteeRateAll'],
                ':NpNgMolecule3' => $row['NpNgMolecule3'],
                ':NpNgDenominator3' => $row['NpNgDenominator3'],
                ':NpNgRate3' => $row['NpNgRate3'],
                ':NpNgMoleculeAll' => $row['NpNgMoleculeAll'],
                ':NpNgDenominatorAll' => $row['NpNgDenominatorAll'],
                ':NpNgRateAll' => $row['NpNgRateAll'],
                ':NpOrderCountTotal' => $row['NpOrderCountTotal'],
                ':NpOrderCountOk' => $row['NpOrderCountOk'],
                ':NpAverageAmountTotal' => $row['NpAverageAmountTotal'],
                ':NpAverageAmountOk' => $row['NpAverageAmountOk'],
                ':Profitability' => $row['Profitability'],
                ':ArrivalConfirmCount' => $row['ArrivalConfirmCount'],
                ':CancelRate' => $row['CancelRate'],
                ':TransAmount' => $row['TransAmount'],
                ':ClaimAmountTotal' => $row['ClaimAmountTotal'],
                ':ReceiptAmountTotal' => $row['ReceiptAmountTotal'],
                ':ClaimedBalance' => $row['ClaimedBalance'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
    }

    /**
     * 不払い率を0リセットする。
     *
     * @param ユーザーＩＤ
     */
    public function resetNp($opId)
    {
        $sql = "
            UPDATE
                T_EnterpriseTotal
            SET
                NpCalcDate = :NpCalcDate,
                NpMolecule3 = 0,
                NpDenominator3 = 0,
                NpRate3 = 0,
                NpMoleculeAll = 0,
                NpDenominatorAll = 0,
                NpRateAll = 0,
                UpdateDate = :UpdateDate,
                UpdateId = :UpdateId;
        ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
            ':NpCalcDate' => date('Y-m-d'),
            ':UpdateDate' => date('Y-m-d H:i:s'),
            ':UpdateId' => $opId
        );

        return $stm->execute($prm);
	}
}
