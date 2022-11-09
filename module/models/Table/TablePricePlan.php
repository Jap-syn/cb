<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * M_PricePlan(料金プランマスター)テーブルへのアダプタ
 */
class TablePricePlan
{
    protected $_name = 'M_PricePlan';
    protected $_primary = array('PricePlanId');
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
     * 料金プランマスターデータを取得する
     *
     * @param int $pricePlanId 料金プランID
     * @return ResultInterface
     */
    public function find($pricePlanId)
    {
        $sql = " SELECT * FROM M_PricePlan WHERE PricePlanId = :PricePlanId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':PricePlanId' => $pricePlanId,
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
        $sql  = " INSERT INTO M_PricePlan (PricePlanName, MonthlyFee, SettlementAmountLimit, SettlementFeeRate, ClaimFeeBS, ClaimFeeDK, ReClaimFee, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :PricePlanName ";
        $sql .= " , :MonthlyFee ";
        $sql .= " , :SettlementAmountLimit ";
        $sql .= " , :SettlementFeeRate ";
        $sql .= " , :ClaimFeeBS ";
        $sql .= " , :ClaimFeeDK ";
        $sql .= " , :ReClaimFee ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':PricePlanName' => $data['PricePlanName'],
                ':MonthlyFee' => $data['MonthlyFee'],
                ':SettlementAmountLimit' => $data['SettlementAmountLimit'],
                ':SettlementFeeRate' => $data['SettlementFeeRate'],
                ':ClaimFeeBS' => $data['ClaimFeeBS'],
                ':ClaimFeeDK' => $data['ClaimFeeDK'],
                ':ReClaimFee' => $data['ReClaimFee'],
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
     * @param int $pricePlanId 料金プランID
     * @return ResultInterface
     */
    public function saveUpdate($data, $pricePlanId)
    {
        $row = $this->find($pricePlanId)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE M_PricePlan ";
        $sql .= " SET ";
        $sql .= "     PricePlanName = :PricePlanName ";
        $sql .= " ,   MonthlyFee = :MonthlyFee ";
        $sql .= " ,   SettlementAmountLimit = :SettlementAmountLimit ";
        $sql .= " ,   SettlementFeeRate = :SettlementFeeRate ";
        $sql .= " ,   ClaimFeeBS = :ClaimFeeBS ";
        $sql .= " ,   ClaimFeeDK = :ClaimFeeDK ";
        $sql .= " ,   ReClaimFee = :ReClaimFee ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE PricePlanId = :PricePlanId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':PricePlanId' => $pricePlanId,
                ':PricePlanName' => $row['PricePlanName'],
                ':MonthlyFee' => $row['MonthlyFee'],
                ':SettlementAmountLimit' => $row['SettlementAmountLimit'],
                ':SettlementFeeRate' => $row['SettlementFeeRate'],
                ':ClaimFeeBS' => $row['ClaimFeeBS'],
                ':ClaimFeeDK' => $row['ClaimFeeDK'],
                ':ReClaimFee' => $row['ReClaimFee'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
    }

    /**
     * すべての料金プランマスターデータを取得する
     *
     * @return ResultInterface
     */
    public function getAll()
    {
        $sql = " SELECT * FROM M_PricePlan ORDER BY PricePlanId ";
        return $this->_adapter->query($sql)->execute(null);
    }
}
