<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;
use Coral\Base\BaseGeneralUtils;

/**
 * T_BusinessCalendarテーブルへのアダプタ
 */
class TableCreditTransferCalendar
{
	protected $_name = 'T_CreditTransferCalendar';
	protected $_primary = array('BusinessDate', 'CreditTransferFlg', 'DataType');
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
	 * 指定年月のカレンダーを取得するデータを取得する。
	 * 対象月のデータがなかった場合は、デフォルト値をインサート後、
	 * 当該データを返す。
	 *
	 * @param int $year 年
	 * @param int $month 月
	 * @return ResultInterface
	 */
	public function getMonthCalendar($year, $month, $creditTransferFlg, $dataType)
	{
        $date1 = sprintf("%04d", $year) . "-" . sprintf("%02d", $month) . "-01";    // 月初
        $date2 = date('Y-m-d', strtotime("last day of " . $date1));                 // 月末

        $sql = " SELECT * FROM T_CreditTransferCalendar WHERE BusinessDate BETWEEN :date1 AND :date2 AND CreditTransferFlg = :CreditTransferFlg AND DataType = :DataType ORDER BY BusinessDate ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':date1' => $date1,
                ':date2' => $date2,
                ':CreditTransferFlg' => $creditTransferFlg,
                ':DataType' => $dataType,
        );

        $ri = $stm->execute($prm);
        return $ri;
	}

	/**
	 * 新しいレコードをインサートする。
	 *
	 * @param array $data インサートする連想配列
	 * @return プライマリキーのバリュー
	 */
	public function saveNew($data)
	{
        $sql  = " INSERT INTO T_CreditTransferCalendar (BusinessDate, CreditTransferFlg, DataType, ExecFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :BusinessDate ";
        $sql .= " , :CreditTransferFlg ";
        $sql .= " , :DataType ";
        $sql .= " , :ExecFlg ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':BusinessDate' => isset($data['BusinessDate']) ? $data['BusinessDate'] : "0000-00-00",
                ':CreditTransferFlg' => $data['CreditTransferFlg'],
                ':DataType' => $data['DataType'],
                ':ExecFlg' => isset($data['ExecFlg']) ? $data['ExecFlg'] : 0,
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
	 * @param string $date 更新する日付 'yyyy-MM-dd'書式で通知
	 */
	public function saveUpdate($data, $date, $creditTransferFlg, $dataType)
	{
        $sql = " SELECT * FROM T_CreditTransferCalendar WHERE BusinessDate = :BusinessDate AND CreditTransferFlg = :CreditTransferFlg AND DataType = :DataType ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':BusinessDate' => $date,
                ':CreditTransferFlg' => $creditTransferFlg,
                ':DataType' => $dataType,
        );

        $row = $stm->execute($prm)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_CreditTransferCalendar ";
        $sql .= " SET ";
        $sql .= "     ExecFlg = :ExecFlg ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE BusinessDate = :BusinessDate ";
        $sql .= " AND   CreditTransferFlg = :CreditTransferFlg ";
        $sql .= " AND   DataType = :DataType ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':BusinessDate' => $date,
                ':CreditTransferFlg' => $creditTransferFlg,
                ':DataType' => $dataType,
                ':ExecFlg' => $row['ExecFlg'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
	}

	/**
	 * 指定年のデータ削除処理
	 * @param string $year
	 * @param string $fromDate(m-d形式)
	 * @return \Zend\Db\Adapter\Driver\ResultInterface　削除処理結果
	 */
	public function deleteInvalidDataOfYear($year, $fromDate = '01-01')
	{
        $sql = <<<EOQ
DELETE FROM
        T_CreditTransferCalendar
WHERE   ValidFlg = 0
AND		BusinessDate >= :from
AND		BusinessDate <= :to;
EOQ;

        $dateFrom = $year. '-'. $fromDate;
        $dateTo = $year. '-12-31';

        return $this->_adapter->query($sql)->execute(array(
                ':from' => $dateFrom,
                ':to' => $dateTo
        ));
    }

    /**
     * 指定のFROM～TOのカレンダーを取得するデータを取得する。
     *
     * @param int $fromDate 開始日(Y-m-d形式)
     * @param int $toDate 終了日(Y-m-d形式)
     * @return ResultInterface
     */
    public function getCalendar($fromDate, $toDate, $creditTransferFlg, $dataType)
    {
        $sql = " SELECT * FROM T_CreditTransferCalendar WHERE BusinessDate BETWEEN :date1 AND :date2 AND CreditTransferFlg=:CreditTransferFlg AND DataType=:DataType ORDER BY BusinessDate ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
            ':date1' => $fromDate,
            ':date2' => $toDate,
            ':CreditTransferFlg' => $creditTransferFlg,
            ':DataType' => $dataType,
        );

        $ri = $stm->execute($prm);

        return $ri;
    }
}
