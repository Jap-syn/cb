<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_EnterpriseCampaign(加盟店別キャンペーンマスター)テーブルへのアダプタ
 */
class TableEnterpriseCampaign
{
    protected $_name = 'T_EnterpriseCampaign';
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
     * 加盟店別キャンペーンマスターデータを取得する(有効フラグ＝有効データに限る)
     *
     * @param int $seq シーケンス
     * @return ResultInterface
     */
    public function find($seq)
    {
        $sql = " SELECT * FROM T_EnterpriseCampaign WHERE ValidFlg = 1 AND Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
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
        $sql  = " INSERT INTO T_EnterpriseCampaign (EnterpriseId, SiteId, DateFrom, DateTo, MonthlyFee, AppPlan, OemMonthlyFee, OemSettlementFeeRate, OemClaimFee, PayingCycleId, LimitDatePattern, LimitDay, Salesman, SettlementAmountLimit, SettlementFeeRate, ClaimFeeDK, ClaimFeeBS, ReClaimFee, SystemFee, SelfBillingOemClaimFee, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :EnterpriseId ";
        $sql .= " , :SiteId ";
        $sql .= " , :DateFrom ";
        $sql .= " , :DateTo ";
        $sql .= " , :MonthlyFee ";
        $sql .= " , :AppPlan ";
        $sql .= " , :OemMonthlyFee ";
        $sql .= " , :OemSettlementFeeRate ";
        $sql .= " , :OemClaimFee ";
        $sql .= " , :PayingCycleId ";
        $sql .= " , :LimitDatePattern ";
        $sql .= " , :LimitDay ";
        $sql .= " , :Salesman ";
        $sql .= " , :SettlementAmountLimit ";
        $sql .= " , :SettlementFeeRate ";
        $sql .= " , :ClaimFeeDK ";
        $sql .= " , :ClaimFeeBS ";
        $sql .= " , :ReClaimFee ";
        $sql .= " , :SystemFee ";
        $sql .= " , :SelfBillingOemClaimFee ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EnterpriseId' => $data['EnterpriseId'],
                ':SiteId' => $data['SiteId'],
                ':DateFrom' => $data['DateFrom'],
                ':DateTo' => $data['DateTo'],
                ':MonthlyFee' => isset($data['MonthlyFee']) ? $data['MonthlyFee'] : 0,
                ':AppPlan' => $data['AppPlan'],
                ':OemMonthlyFee' => isset($data['OemMonthlyFee']) ? $data['OemMonthlyFee'] : 0,
                ':OemSettlementFeeRate' => isset($data['OemSettlementFeeRate']) ? $data['OemSettlementFeeRate'] : 0,
                ':OemClaimFee' => isset($data['OemClaimFee']) ? $data['OemClaimFee'] : 0,
                ':PayingCycleId' => $data['PayingCycleId'],
                ':LimitDatePattern' => isset($data['LimitDatePattern']) ? $data['LimitDatePattern'] : 0,
                ':LimitDay' => $data['LimitDay'],
                ':Salesman' => $data['Salesman'],
                ':SettlementAmountLimit' => isset($data['SettlementAmountLimit']) ? $data['SettlementAmountLimit'] : 0,
                ':SettlementFeeRate' => isset($data['SettlementFeeRate']) ? $data['SettlementFeeRate'] : 0,
                ':ClaimFeeDK' => isset($data['ClaimFeeDK']) ? $data['ClaimFeeDK'] : 0,
                ':ClaimFeeBS' => isset($data['ClaimFeeBS']) ? $data['ClaimFeeBS'] : 0,
                ':ReClaimFee' => isset($data['ReClaimFee']) ? $data['ReClaimFee'] : 0,
                ':SystemFee' => isset($data['SystemFee']) ? $data['SystemFee'] : 0,
                ':SelfBillingOemClaimFee' => isset($data['SelfBillingOemClaimFee']) ? $data['SelfBillingOemClaimFee'] : 0,
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
     * @param int $seq シーケンス
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

        $sql  = " UPDATE T_EnterpriseCampaign ";
        $sql .= " SET ";
        $sql .= "     EnterpriseId = :EnterpriseId ";
        $sql .= " ,   SiteId = :SiteId ";
        $sql .= " ,   DateFrom = :DateFrom ";
        $sql .= " ,   DateTo = :DateTo ";
        $sql .= " ,   MonthlyFee = :MonthlyFee ";
        $sql .= " ,   AppPlan = :AppPlan ";
        $sql .= " ,   OemMonthlyFee = :OemMonthlyFee ";
        $sql .= " ,   OemSettlementFeeRate = :OemSettlementFeeRate ";
        $sql .= " ,   OemClaimFee = :OemClaimFee ";
        $sql .= " ,   PayingCycleId = :PayingCycleId ";
        $sql .= " ,   LimitDatePattern = :LimitDatePattern ";
        $sql .= " ,   LimitDay = :LimitDay ";
        $sql .= " ,   Salesman = :Salesman ";
        $sql .= " ,   SettlementAmountLimit = :SettlementAmountLimit ";
        $sql .= " ,   SettlementFeeRate = :SettlementFeeRate ";
        $sql .= " ,   ClaimFeeDK = :ClaimFeeDK ";
        $sql .= " ,   ClaimFeeBS = :ClaimFeeBS ";
        $sql .= " ,   ReClaimFee = :ReClaimFee ";
        $sql .= " ,   SystemFee = :SystemFee ";
        $sql .= " ,   SelfBillingOemClaimFee = :SelfBillingOemClaimFee ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
                ':EnterpriseId' => $row['EnterpriseId'],
                ':SiteId' => $row['SiteId'],
                ':DateFrom' => $row['DateFrom'],
                ':DateTo' => $row['DateTo'],
                ':MonthlyFee' => $row['MonthlyFee'],
                ':AppPlan' => $row['AppPlan'],
                ':OemMonthlyFee' => $row['OemMonthlyFee'],
                ':OemSettlementFeeRate' => $row['OemSettlementFeeRate'],
                ':OemClaimFee' => $row['OemClaimFee'],
                ':PayingCycleId' => $row['PayingCycleId'],
                ':LimitDatePattern' => $row['LimitDatePattern'],
                ':LimitDay' => $row['LimitDay'],
                ':Salesman' => $row['Salesman'],
                ':SettlementAmountLimit' => $row['SettlementAmountLimit'],
                ':SettlementFeeRate' => $row['SettlementFeeRate'],
                ':ClaimFeeDK' => $row['ClaimFeeDK'],
                ':ClaimFeeBS' => $row['ClaimFeeBS'],
                ':ReClaimFee' => $row['ReClaimFee'],
                ':SystemFee' => $row['SystemFee'],
                ':SelfBillingOemClaimFee' => $row['SelfBillingOemClaimFee'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
    }
}
