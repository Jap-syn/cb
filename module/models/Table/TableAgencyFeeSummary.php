<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_AgencyFeeSummary(代理店手数料サマリー)テーブルへのアダプタ
 */
class TableAgencyFeeSummary
{
    protected $_name = 'T_AgencyFeeSummary';
    protected $_primary = array ('AgencyId', 'TargetMonth');
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
     * 代理店手数料サマリーデータを取得する(有効フラグ＝有効データに限る)
     *
     * @param int $agencyId 代理店ID
     * @param string $targetMonth 対象年月(※日付は１日を指定) 'yyyy-MM-01'書式で通知
     * @return ResultInterface
     */
    public function find($agencyId, $targetMonth)
    {
        $sql = " SELECT * FROM T_AgencyFeeSummary WHERE ValidFlg = 1 AND AgencyId = :AgencyId AND TargetMonth = :TargetMonth ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':AgencyId' => $agencyId,
                ':TargetMonth' => $targetMonth,
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
        $sql  = " INSERT INTO T_AgencyFeeSummary (AgencyId, TargetMonth, EnterpriseCount, EnterpriseSalesAmount, AgencyFee, TransferCommission, CarryOverTC, SubTotal, PaymentAmount, PaymentTargetClass, MonthlyFee, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :AgencyId ";
        $sql .= " , :TargetMonth ";
        $sql .= " , :EnterpriseCount ";
        $sql .= " , :EnterpriseSalesAmount ";
        $sql .= " , :AgencyFee ";
        $sql .= " , :TransferCommission ";
        $sql .= " , :CarryOverTC ";
        $sql .= " , :SubTotal ";
        $sql .= " , :PaymentAmount ";
        $sql .= " , :PaymentTargetClass ";
        $sql .= " , :MonthlyFee ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':AgencyId' => $data['AgencyId'],
                ':TargetMonth' => $data['TargetMonth'],
                ':EnterpriseCount' => $data['EnterpriseCount'],
                ':EnterpriseSalesAmount' => $data['EnterpriseSalesAmount'],
                ':AgencyFee' => $data['AgencyFee'],
                ':TransferCommission' => $data['TransferCommission'],
                ':CarryOverTC' => $data['CarryOverTC'],
                ':SubTotal' => $data['SubTotal'],
                ':PaymentAmount' => $data['PaymentAmount'],
                ':PaymentTargetClass' => isset($data['PaymentTargetClass']) ? $data['PaymentTargetClass'] : 0,
                ':MonthlyFee' => $data['MonthlyFee'],
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
     * @param int $agencyId 代理店ID
     * @param string $targetMonth 対象年月(※日付は１日を指定) 'yyyy-MM-01'書式で通知
     * @return ResultInterface
     */
    public function saveUpdate($data, $agencyId, $targetMonth)
    {
        $row = $this->find($agencyId, $targetMonth)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_AgencyFeeSummary ";
        $sql .= " SET ";
        $sql .= "     EnterpriseCount = :EnterpriseCount ";
        $sql .= " ,   EnterpriseSalesAmount = :EnterpriseSalesAmount ";
        $sql .= " ,   AgencyFee = :AgencyFee ";
        $sql .= " ,   TransferCommission = :TransferCommission ";
        $sql .= " ,   CarryOverTC = :CarryOverTC ";
        $sql .= " ,   SubTotal = :SubTotal ";
        $sql .= " ,   PaymentAmount = :PaymentAmount ";
        $sql .= " ,   PaymentTargetClass = :PaymentTargetClass ";
        $sql .= " ,   MonthlyFee = :MonthlyFee ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE AgencyId = :AgencyId ";
        $sql .= " AND   TargetMonth = :TargetMonth ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':AgencyId' => $agencyId,
                ':TargetMonth' => $targetMonth,
                ':EnterpriseCount' => $row['EnterpriseCount'],
                ':EnterpriseSalesAmount' => $row['EnterpriseSalesAmount'],
                ':AgencyFee' => $row['AgencyFee'],
                ':TransferCommission' => $row['TransferCommission'],
                ':CarryOverTC' => $row['CarryOverTC'],
                ':SubTotal' => $row['SubTotal'],
                ':PaymentAmount' => $row['PaymentAmount'],
                ':PaymentTargetClass' => $row['PaymentTargetClass'],
                ':MonthlyFee' => $row['MonthlyFee'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
    }


    /**
     * 指定されたレコードを削除する。
     *
     * @param unknown $conditionArray
     *　@return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function deleteAgencyFeeSummary($conditionArray)
    {
        $prm = array();

        $sql  = " DELETE FROM T_AgencyFeeSummary WHERE 1 = 1 ";

        foreach ($conditionArray as $key => $value) {
            $sql .= (" AND " . $key . " = :" . $key);
            $prm += array(':' . $key => $value);
        }

        $stm = $this->_adapter->query($sql);

        return $stm->execute($prm);
    }

}
