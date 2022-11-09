<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * AT_PayingControl(立替振込管理_会計)テーブルへのアダプタ
 */
class ATablePayingControl
{
    protected $_name = 'AT_PayingControl';
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
     * 立替振込管理_会計データを取得する
     *
     * @param int $seq シーケンス
     * @return ResultInterface
     */
    public function find($seq)
    {
        $sql = " SELECT * FROM AT_PayingControl WHERE Seq = :Seq ";

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
        $sql  = " INSERT INTO AT_PayingControl (Seq, MonthlyFeeWithoutTax, MonthlyFeeTax, IncludeMonthlyFee, IncludeMonthlyFeeTax, ApiMonthlyFee, ApiMonthlyFeeTax, CreditNoticeMonthlyFee, CreditNoticeMonthlyFeeTax, NCreditNoticeMonthlyFee, NCreditNoticeMonthlyFeeTax, ReserveMonthlyFee, ReserveMonthlyFeeTax, OemMonthlyFeeWithoutTax, OemMonthlyFeeTax, OemIncludeMonthlyFee, OemIncludeMonthlyFeeTax, OemApiMonthlyFee, OemApiMonthlyFeeTax, OemCreditNoticeMonthlyFee, OemCreditNoticeMonthlyFeeTax, OemNCreditNoticeMonthlyFee, OemNCreditNoticeMonthlyFeeTax, OemReserveMonthlyFee, OemReserveMonthlyFeeTax, DailySummaryFlg) VALUES (";
        $sql .= "   :Seq ";
        $sql .= " , :MonthlyFeeWithoutTax ";
        $sql .= " , :MonthlyFeeTax ";
        $sql .= " , :IncludeMonthlyFee ";
        $sql .= " , :IncludeMonthlyFeeTax ";
        $sql .= " , :ApiMonthlyFee ";
        $sql .= " , :ApiMonthlyFeeTax ";
        $sql .= " , :CreditNoticeMonthlyFee ";
        $sql .= " , :CreditNoticeMonthlyFeeTax ";
        $sql .= " , :NCreditNoticeMonthlyFee ";
        $sql .= " , :NCreditNoticeMonthlyFeeTax ";
        $sql .= " , :ReserveMonthlyFee ";
        $sql .= " , :ReserveMonthlyFeeTax ";
        $sql .= " , :OemMonthlyFeeWithoutTax ";
        $sql .= " , :OemMonthlyFeeTax ";
        $sql .= " , :OemIncludeMonthlyFee ";
        $sql .= " , :OemIncludeMonthlyFeeTax ";
        $sql .= " , :OemApiMonthlyFee ";
        $sql .= " , :OemApiMonthlyFeeTax ";
        $sql .= " , :OemCreditNoticeMonthlyFee ";
        $sql .= " , :OemCreditNoticeMonthlyFeeTax ";
        $sql .= " , :OemNCreditNoticeMonthlyFee ";
        $sql .= " , :OemNCreditNoticeMonthlyFeeTax ";
        $sql .= " , :OemReserveMonthlyFee ";
        $sql .= " , :OemReserveMonthlyFeeTax ";
        $sql .= " , :DailySummaryFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $data['Seq'],
                ':MonthlyFeeWithoutTax' => isset($data['MonthlyFeeWithoutTax']) ? $data['MonthlyFeeWithoutTax'] : 0,
                ':MonthlyFeeTax' => isset($data['MonthlyFeeTax']) ? $data['MonthlyFeeTax'] : 0,
                ':IncludeMonthlyFee' => isset($data['IncludeMonthlyFee']) ? $data['IncludeMonthlyFee'] : 0,
                ':IncludeMonthlyFeeTax' => isset($data['IncludeMonthlyFeeTax']) ? $data['IncludeMonthlyFeeTax'] : 0,
                ':ApiMonthlyFee' => isset($data['ApiMonthlyFee']) ? $data['ApiMonthlyFee'] : 0,
                ':ApiMonthlyFeeTax' => isset($data['ApiMonthlyFeeTax']) ? $data['ApiMonthlyFeeTax'] : 0,
                ':CreditNoticeMonthlyFee' => isset($data['CreditNoticeMonthlyFee']) ? $data['CreditNoticeMonthlyFee'] : 0,
                ':CreditNoticeMonthlyFeeTax' => isset($data['CreditNoticeMonthlyFeeTax']) ? $data['CreditNoticeMonthlyFeeTax'] : 0,
                ':NCreditNoticeMonthlyFee' => isset($data['NCreditNoticeMonthlyFee']) ? $data['NCreditNoticeMonthlyFee'] : 0,
                ':NCreditNoticeMonthlyFeeTax' => isset($data['NCreditNoticeMonthlyFeeTax']) ? $data['NCreditNoticeMonthlyFeeTax'] : 0,
                ':ReserveMonthlyFee' => isset($data['ReserveMonthlyFee']) ? $data['ReserveMonthlyFee'] : 0,
                ':ReserveMonthlyFeeTax' => isset($data['ReserveMonthlyFeeTax']) ? $data['ReserveMonthlyFeeTax'] : 0,
                ':OemMonthlyFeeWithoutTax' => isset($data['OemMonthlyFeeWithoutTax']) ? $data['OemMonthlyFeeWithoutTax'] : 0,
                ':OemMonthlyFeeTax' => isset($data['OemMonthlyFeeTax']) ? $data['OemMonthlyFeeTax'] : 0,
                ':OemIncludeMonthlyFee' => isset($data['OemIncludeMonthlyFee']) ? $data['OemIncludeMonthlyFee'] : 0,
                ':OemIncludeMonthlyFeeTax' => isset($data['OemIncludeMonthlyFeeTax']) ? $data['OemIncludeMonthlyFeeTax'] : 0,
                ':OemApiMonthlyFee' => isset($data['OemApiMonthlyFee']) ? $data['OemApiMonthlyFee'] : 0,
                ':OemApiMonthlyFeeTax' => isset($data['OemApiMonthlyFeeTax']) ? $data['OemApiMonthlyFeeTax'] : 0,
                ':OemCreditNoticeMonthlyFee' => isset($data['OemCreditNoticeMonthlyFee']) ? $data['OemCreditNoticeMonthlyFee'] : 0,
                ':OemCreditNoticeMonthlyFeeTax' => isset($data['OemCreditNoticeMonthlyFeeTax']) ? $data['OemCreditNoticeMonthlyFeeTax'] : 0,
                ':OemNCreditNoticeMonthlyFee' => isset($data['OemNCreditNoticeMonthlyFee']) ? $data['OemNCreditNoticeMonthlyFee'] : 0,
                ':OemNCreditNoticeMonthlyFeeTax' => isset($data['OemNCreditNoticeMonthlyFeeTax']) ? $data['OemNCreditNoticeMonthlyFeeTax'] : 0,
                ':OemReserveMonthlyFee' => isset($data['OemReserveMonthlyFee']) ? $data['OemReserveMonthlyFee'] : 0,
                ':OemReserveMonthlyFeeTax' => isset($data['OemReserveMonthlyFeeTax']) ? $data['OemReserveMonthlyFeeTax'] : 0,
                ':DailySummaryFlg' => isset($data['DailySummaryFlg']) ? $data['DailySummaryFlg'] : 0,
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

        $sql  = " UPDATE AT_PayingControl ";
        $sql .= " SET ";
        $sql .= "     MonthlyFeeWithoutTax = :MonthlyFeeWithoutTax ";
        $sql .= " ,   MonthlyFeeTax = :MonthlyFeeTax ";
        $sql .= " ,   IncludeMonthlyFee = :IncludeMonthlyFee ";
        $sql .= " ,   IncludeMonthlyFeeTax = :IncludeMonthlyFeeTax ";
        $sql .= " ,   ApiMonthlyFee = :ApiMonthlyFee ";
        $sql .= " ,   ApiMonthlyFeeTax = :ApiMonthlyFeeTax ";
        $sql .= " ,   CreditNoticeMonthlyFee = :CreditNoticeMonthlyFee ";
        $sql .= " ,   CreditNoticeMonthlyFeeTax = :CreditNoticeMonthlyFeeTax ";
        $sql .= " ,   NCreditNoticeMonthlyFee = :NCreditNoticeMonthlyFee ";
        $sql .= " ,   NCreditNoticeMonthlyFeeTax = :NCreditNoticeMonthlyFeeTax ";
        $sql .= " ,   ReserveMonthlyFee = :ReserveMonthlyFee ";
        $sql .= " ,   ReserveMonthlyFeeTax = :ReserveMonthlyFeeTax ";
        $sql .= " ,   OemMonthlyFeeWithoutTax = :OemMonthlyFeeWithoutTax ";
        $sql .= " ,   OemMonthlyFeeTax = :OemMonthlyFeeTax ";
        $sql .= " ,   OemIncludeMonthlyFee = :OemIncludeMonthlyFee ";
        $sql .= " ,   OemIncludeMonthlyFeeTax = :OemIncludeMonthlyFeeTax ";
        $sql .= " ,   OemApiMonthlyFee = :OemApiMonthlyFee ";
        $sql .= " ,   OemApiMonthlyFeeTax = :OemApiMonthlyFeeTax ";
        $sql .= " ,   OemCreditNoticeMonthlyFee = :OemCreditNoticeMonthlyFee ";
        $sql .= " ,   OemCreditNoticeMonthlyFeeTax = :OemCreditNoticeMonthlyFeeTax ";
        $sql .= " ,   OemNCreditNoticeMonthlyFee = :OemNCreditNoticeMonthlyFee ";
        $sql .= " ,   OemNCreditNoticeMonthlyFeeTax = :OemNCreditNoticeMonthlyFeeTax ";
        $sql .= " ,   OemReserveMonthlyFee = :OemReserveMonthlyFee ";
        $sql .= " ,   OemReserveMonthlyFeeTax = :OemReserveMonthlyFeeTax ";
        $sql .= " ,   DailySummaryFlg = :DailySummaryFlg ";
        $sql .= " WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $row['Seq'],
                ':MonthlyFeeWithoutTax' => $row['MonthlyFeeWithoutTax'],
                ':MonthlyFeeTax' => $row['MonthlyFeeTax'],
                ':IncludeMonthlyFee' => $row['IncludeMonthlyFee'],
                ':IncludeMonthlyFeeTax' => $row['IncludeMonthlyFeeTax'],
                ':ApiMonthlyFee' => $row['ApiMonthlyFee'],
                ':ApiMonthlyFeeTax' => $row['ApiMonthlyFeeTax'],
                ':CreditNoticeMonthlyFee' => $row['CreditNoticeMonthlyFee'],
                ':CreditNoticeMonthlyFeeTax' => $row['CreditNoticeMonthlyFeeTax'],
                ':NCreditNoticeMonthlyFee' => $row['NCreditNoticeMonthlyFee'],
                ':NCreditNoticeMonthlyFeeTax' => $row['NCreditNoticeMonthlyFeeTax'],
                ':ReserveMonthlyFee' => $row['ReserveMonthlyFee'],
                ':ReserveMonthlyFeeTax' => $row['ReserveMonthlyFeeTax'],
                ':OemMonthlyFeeWithoutTax' => $row['OemMonthlyFeeWithoutTax'],
                ':OemMonthlyFeeTax' => $row['OemMonthlyFeeTax'],
                ':OemIncludeMonthlyFee' => $row['OemIncludeMonthlyFee'],
                ':OemIncludeMonthlyFeeTax' => $row['OemIncludeMonthlyFeeTax'],
                ':OemApiMonthlyFee' => $row['OemApiMonthlyFee'],
                ':OemApiMonthlyFeeTax' => $row['OemApiMonthlyFeeTax'],
                ':OemCreditNoticeMonthlyFee' => $row['OemCreditNoticeMonthlyFee'],
                ':OemCreditNoticeMonthlyFeeTax' => $row['OemCreditNoticeMonthlyFeeTax'],
                ':OemNCreditNoticeMonthlyFee' => $row['OemNCreditNoticeMonthlyFee'],
                ':OemNCreditNoticeMonthlyFeeTax' => $row['OemNCreditNoticeMonthlyFeeTax'],
                ':OemReserveMonthlyFee' => $row['OemReserveMonthlyFee'],
                ':OemReserveMonthlyFeeTax' => $row['OemReserveMonthlyFeeTax'],
                ':DailySummaryFlg' => $row['DailySummaryFlg'],
        );

        return $stm->execute($prm);
    }
}
