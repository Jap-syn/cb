<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * AT_Enterprise(加盟店_会計)テーブルへのアダプタ
 */
class ATableEnterprise
{
    protected $_name = 'AT_Enterprise';
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
     * 加盟店_会計データを取得する
     *
     * @param int $enterpriseId 加盟店ID
     * @return ResultInterface
     */
    public function find($enterpriseId)
    {
        $sql = " SELECT * FROM AT_Enterprise WHERE EnterpriseId = :EnterpriseId ";

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
        $sql  = " INSERT INTO AT_Enterprise (EnterpriseId, IncludeMonthlyFee, ApiMonthlyFee, CreditNoticeMonthlyFee, NCreditNoticeMonthlyFee, ReserveMonthlyFee, N_IncludeMonthlyFee, N_ApiMonthlyFee, N_CreditNoticeMonthlyFee, N_NCreditNoticeMonthlyFee, N_ReserveMonthlyFee, OemIncludeMonthlyFee, OemApiMonthlyFee, OemCreditNoticeMonthlyFee, OemNCreditNoticeMonthlyFee, OemReserveMonthlyFee, N_OemIncludeMonthlyFee, N_OemApiMonthlyFee, N_OemCreditNoticeMonthlyFee, N_OemNCreditNoticeMonthlyFee,N_OemReserveMonthlyFee) VALUES (";
        $sql .= "   :EnterpriseId ";
        $sql .= " , :IncludeMonthlyFee ";
        $sql .= " , :ApiMonthlyFee ";
        $sql .= " , :CreditNoticeMonthlyFee ";
        $sql .= " , :NCreditNoticeMonthlyFee ";
        $sql .= " , :ReserveMonthlyFee ";
        $sql .= " , :N_IncludeMonthlyFee ";
        $sql .= " , :N_ApiMonthlyFee ";
        $sql .= " , :N_CreditNoticeMonthlyFee ";
        $sql .= " , :N_NCreditNoticeMonthlyFee ";
        $sql .= " , :N_ReserveMonthlyFee ";
        $sql .= " , :OemIncludeMonthlyFee ";
        $sql .= " , :OemApiMonthlyFee ";
        $sql .= " , :OemCreditNoticeMonthlyFee ";
        $sql .= " , :OemNCreditNoticeMonthlyFee ";
        $sql .= " , :OemReserveMonthlyFee ";
        $sql .= " , :N_OemIncludeMonthlyFee ";
        $sql .= " , :N_OemApiMonthlyFee ";
        $sql .= " , :N_OemCreditNoticeMonthlyFee ";
        $sql .= " , :N_OemNCreditNoticeMonthlyFee ";
        $sql .= " , :N_OemReserveMonthlyFee ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EnterpriseId' => $data['EnterpriseId'],
                ':IncludeMonthlyFee' => isset($data['IncludeMonthlyFee']) ? $data['IncludeMonthlyFee'] : 0,
                ':ApiMonthlyFee' => isset($data['ApiMonthlyFee']) ? $data['ApiMonthlyFee'] : 0,
                ':CreditNoticeMonthlyFee' => isset($data['CreditNoticeMonthlyFee']) ? $data['CreditNoticeMonthlyFee'] : 0,
                ':NCreditNoticeMonthlyFee' => isset($data['NCreditNoticeMonthlyFee']) ? $data['NCreditNoticeMonthlyFee'] : 0,
                ':ReserveMonthlyFee' => isset($data['ReserveMonthlyFee']) ? $data['ReserveMonthlyFee'] : 0,
                ':N_IncludeMonthlyFee' => isset($data['N_IncludeMonthlyFee']) ? $data['N_IncludeMonthlyFee'] : 0,
                ':N_ApiMonthlyFee' => isset($data['N_ApiMonthlyFee']) ? $data['N_ApiMonthlyFee'] : 0,
                ':N_CreditNoticeMonthlyFee' => isset($data['N_CreditNoticeMonthlyFee']) ? $data['N_CreditNoticeMonthlyFee'] : 0,
                ':N_NCreditNoticeMonthlyFee' => isset($data['N_NCreditNoticeMonthlyFee']) ? $data['N_NCreditNoticeMonthlyFee'] : 0,
                ':N_ReserveMonthlyFee' => isset($data['N_ReserveMonthlyFee']) ? $data['N_ReserveMonthlyFee'] : 0,
                ':OemIncludeMonthlyFee' => isset($data['OemIncludeMonthlyFee']) ? $data['OemIncludeMonthlyFee'] : 0,
                ':OemApiMonthlyFee' => isset($data['OemApiMonthlyFee']) ? $data['OemApiMonthlyFee'] : 0,
                ':OemCreditNoticeMonthlyFee' => isset($data['OemCreditNoticeMonthlyFee']) ? $data['OemCreditNoticeMonthlyFee'] : 0,
                ':OemNCreditNoticeMonthlyFee' => isset($data['OemNCreditNoticeMonthlyFee']) ? $data['OemNCreditNoticeMonthlyFee'] : 0,
                ':OemReserveMonthlyFee' => isset($data['OemReserveMonthlyFee']) ? $data['OemReserveMonthlyFee'] : 0,
                ':N_OemIncludeMonthlyFee' => isset($data['N_OemIncludeMonthlyFee']) ? $data['N_OemIncludeMonthlyFee'] : 0,
                ':N_OemApiMonthlyFee' => isset($data['N_OemApiMonthlyFee']) ? $data['N_OemApiMonthlyFee'] : 0,
                ':N_OemCreditNoticeMonthlyFee' => isset($data['N_OemCreditNoticeMonthlyFee']) ? $data['N_OemCreditNoticeMonthlyFee'] : 0,
                ':N_OemNCreditNoticeMonthlyFee' => isset($data['N_OemNCreditNoticeMonthlyFee']) ? $data['N_OemNCreditNoticeMonthlyFee'] : 0,
                ':N_OemReserveMonthlyFee' => isset($data['N_OemReserveMonthlyFee']) ? $data['N_OemReserveMonthlyFee'] : 0,
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

        $sql  = " UPDATE AT_Enterprise ";
        $sql .= " SET ";
        $sql .= "     IncludeMonthlyFee = :IncludeMonthlyFee ";
        $sql .= " ,   ApiMonthlyFee = :ApiMonthlyFee ";
        $sql .= " ,   CreditNoticeMonthlyFee = :CreditNoticeMonthlyFee ";
        $sql .= " ,   NCreditNoticeMonthlyFee = :NCreditNoticeMonthlyFee ";
        $sql .= " ,   ReserveMonthlyFee = :ReserveMonthlyFee ";
        $sql .= " ,   N_IncludeMonthlyFee = :N_IncludeMonthlyFee ";
        $sql .= " ,   N_ApiMonthlyFee = :N_ApiMonthlyFee ";
        $sql .= " ,   N_CreditNoticeMonthlyFee = :N_CreditNoticeMonthlyFee ";
        $sql .= " ,   N_NCreditNoticeMonthlyFee = :N_NCreditNoticeMonthlyFee ";
        $sql .= " ,   N_ReserveMonthlyFee = :N_ReserveMonthlyFee ";
        $sql .= " ,   OemIncludeMonthlyFee = :OemIncludeMonthlyFee ";
        $sql .= " ,   OemApiMonthlyFee = :OemApiMonthlyFee ";
        $sql .= " ,   OemCreditNoticeMonthlyFee = :OemCreditNoticeMonthlyFee ";
        $sql .= " ,   OemNCreditNoticeMonthlyFee = :OemNCreditNoticeMonthlyFee ";
        $sql .= " ,   OemReserveMonthlyFee = :OemReserveMonthlyFee ";
        $sql .= " ,   N_OemIncludeMonthlyFee = :N_OemIncludeMonthlyFee ";
        $sql .= " ,   N_OemApiMonthlyFee = :N_OemApiMonthlyFee ";
        $sql .= " ,   N_OemCreditNoticeMonthlyFee = :N_OemCreditNoticeMonthlyFee ";
        $sql .= " ,   N_OemNCreditNoticeMonthlyFee = :N_OemNCreditNoticeMonthlyFee ";
        $sql .= " ,   N_OemReserveMonthlyFee = :N_OemReserveMonthlyFee ";
        $sql .= " WHERE EnterpriseId = :EnterpriseId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EnterpriseId' => $enterpriseId,
                ':IncludeMonthlyFee' => $row['IncludeMonthlyFee'],
                ':ApiMonthlyFee' => $row['ApiMonthlyFee'],
                ':CreditNoticeMonthlyFee' => $row['CreditNoticeMonthlyFee'],
                ':NCreditNoticeMonthlyFee' => $row['NCreditNoticeMonthlyFee'],
                ':ReserveMonthlyFee' => $row['ReserveMonthlyFee'],
                ':N_IncludeMonthlyFee' => $row['N_IncludeMonthlyFee'],
                ':N_ApiMonthlyFee' => $row['N_ApiMonthlyFee'],
                ':N_CreditNoticeMonthlyFee' => $row['N_CreditNoticeMonthlyFee'],
                ':N_NCreditNoticeMonthlyFee' => $row['N_NCreditNoticeMonthlyFee'],
                ':N_ReserveMonthlyFee' => $row['N_ReserveMonthlyFee'],
                ':OemIncludeMonthlyFee' => $row['OemIncludeMonthlyFee'],
                ':OemApiMonthlyFee' => $row['OemApiMonthlyFee'],
                ':OemCreditNoticeMonthlyFee' => $row['OemCreditNoticeMonthlyFee'],
                ':OemNCreditNoticeMonthlyFee' => $row['OemNCreditNoticeMonthlyFee'],
                ':OemReserveMonthlyFee' => $row['OemReserveMonthlyFee'],
                ':N_OemIncludeMonthlyFee' => $row['N_OemIncludeMonthlyFee'],
                ':N_OemApiMonthlyFee' => $row['N_OemApiMonthlyFee'],
                ':N_OemCreditNoticeMonthlyFee' => $row['N_OemCreditNoticeMonthlyFee'],
                ':N_OemNCreditNoticeMonthlyFee' => $row['N_OemNCreditNoticeMonthlyFee'],
                ':N_OemReserveMonthlyFee' => $row['N_OemReserveMonthlyFee'],
        );

        return $stm->execute($prm);
    }
}
