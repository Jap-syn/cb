<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * AT_EnterpriseCampaign(加盟店キャンペーンマスター_会計)テーブルへのアダプタ
 */
class ATableEnterpriseCampaign
{
    protected $_name = 'AT_EnterpriseCampaign';
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
     * 加盟店キャンペーンマスター_会計データを取得する
     *
     * @param int $seq シーケンス
     * @return ResultInterface
     */
    public function find($seq)
    {
        $sql = " SELECT * FROM AT_EnterpriseCampaign WHERE Seq = :Seq ";

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
        $sql  = " INSERT INTO AT_EnterpriseCampaign (Seq, OemClaimFeeDK, IncludeMonthlyFee, ApiMonthlyFee, CreditNoticeMonthlyFee, NCreditNoticeMonthlyFee, ReserveMonthlyFee, OemIncludeMonthlyFee, OemApiMonthlyFee, OemCreditNoticeMonthlyFee, OemNCreditNoticeMonthlyFee, OemReserveMonthlyFee) VALUES (";
        $sql .= "   :Seq ";
        $sql .= " , :OemClaimFeeDK ";
        $sql .= " , :IncludeMonthlyFee ";
        $sql .= " , :ApiMonthlyFee ";
        $sql .= " , :CreditNoticeMonthlyFee ";
        $sql .= " , :NCreditNoticeMonthlyFee ";
        $sql .= " , :ReserveMonthlyFee ";
        $sql .= " , :OemIncludeMonthlyFee ";
        $sql .= " , :OemApiMonthlyFee ";
        $sql .= " , :OemCreditNoticeMonthlyFee ";
        $sql .= " , :OemNCreditNoticeMonthlyFee ";
        $sql .= " , :OemReserveMonthlyFee ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $data['Seq'],
                ':OemClaimFeeDK' => isset($data['OemClaimFeeDK']) ? $data['OemClaimFeeDK'] : 0,
                ':IncludeMonthlyFee' => isset($data['IncludeMonthlyFee']) ? $data['IncludeMonthlyFee'] : 0,
                ':ApiMonthlyFee' => isset($data['ApiMonthlyFee']) ? $data['ApiMonthlyFee'] : 0,
                ':CreditNoticeMonthlyFee' => isset($data['CreditNoticeMonthlyFee']) ? $data['CreditNoticeMonthlyFee'] : 0,
                ':NCreditNoticeMonthlyFee' => isset($data['NCreditNoticeMonthlyFee']) ? $data['NCreditNoticeMonthlyFee'] : 0,
                ':ReserveMonthlyFee' => isset($data['ReserveMonthlyFee']) ? $data['ReserveMonthlyFee'] : 0,
                ':OemIncludeMonthlyFee' => isset($data['OemIncludeMonthlyFee']) ? $data['OemIncludeMonthlyFee'] : 0,
                ':OemApiMonthlyFee' => isset($data['OemApiMonthlyFee']) ? $data['OemApiMonthlyFee'] : 0,
                ':OemCreditNoticeMonthlyFee' => isset($data['OemCreditNoticeMonthlyFee']) ? $data['OemCreditNoticeMonthlyFee'] : 0,
                ':OemNCreditNoticeMonthlyFee' => isset($data['OemNCreditNoticeMonthlyFee']) ? $data['OemNCreditNoticeMonthlyFee'] : 0,
                ':OemReserveMonthlyFee' => isset($data['OemReserveMonthlyFee']) ? $data['OemReserveMonthlyFee'] : 0,
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

        $sql  = " UPDATE AT_EnterpriseCampaign ";
        $sql .= " SET ";
        $sql .= "     OemClaimFeeDK = :OemClaimFeeDK ";
        $sql .= " ,   IncludeMonthlyFee = :IncludeMonthlyFee ";
        $sql .= " ,   ApiMonthlyFee = :ApiMonthlyFee ";
        $sql .= " ,   CreditNoticeMonthlyFee = :CreditNoticeMonthlyFee ";
        $sql .= " ,   NCreditNoticeMonthlyFee = :NCreditNoticeMonthlyFee ";
        $sql .= " ,   ReserveMonthlyFee = :ReserveMonthlyFee ";
        $sql .= " ,   OemIncludeMonthlyFee = :OemIncludeMonthlyFee ";
        $sql .= " ,   OemApiMonthlyFee = :OemApiMonthlyFee ";
        $sql .= " ,   OemCreditNoticeMonthlyFee = :OemCreditNoticeMonthlyFee ";
        $sql .= " ,   OemNCreditNoticeMonthlyFee = :OemNCreditNoticeMonthlyFee ";
        $sql .= " ,   OemReserveMonthlyFee = :OemReserveMonthlyFee ";
        $sql .= " WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
                ':OemClaimFeeDK' => $row['OemClaimFeeDK'],
                ':IncludeMonthlyFee' => $row['IncludeMonthlyFee'],
                ':ApiMonthlyFee' => $row['ApiMonthlyFee'],
                ':CreditNoticeMonthlyFee' => $row['CreditNoticeMonthlyFee'],
                ':NCreditNoticeMonthlyFee' => $row['NCreditNoticeMonthlyFee'],
                ':ReserveMonthlyFee' => $row['ReserveMonthlyFee'],
                ':OemIncludeMonthlyFee' => $row['OemIncludeMonthlyFee'],
                ':OemApiMonthlyFee' => $row['OemApiMonthlyFee'],
                ':OemCreditNoticeMonthlyFee' => $row['OemCreditNoticeMonthlyFee'],
                ':OemNCreditNoticeMonthlyFee' => $row['OemNCreditNoticeMonthlyFee'],
                ':OemReserveMonthlyFee' => $row['OemReserveMonthlyFee'],
        );

        return $stm->execute($prm);
    }
}
