<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * M_NationalHoliday(祝日マスター)テーブルへのアダプタ
 */
class TableNationalHoliday
{
    protected $_name = 'M_NationalHoliday';
    protected $_primary = array('BusinessDate');
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
     * 祝日マスターデータを取得する
     *
     * @param string $businessDate 日付 'yyyy-MM-dd'書式で通知
     * @return ResultInterface
     */
    public function find($businessDate)
    {
        $sql = " SELECT * FROM M_NationalHoliday WHERE BusinessDate = :BusinessDate ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':BusinessDate' => $businessDate,
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
        $sql  = " INSERT INTO M_NationalHoliday (BusinessDate, NationalHolidayName, NationalHolidayFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :BusinessDate ";
        $sql .= " , :NationalHolidayName ";
        $sql .= " , :NationalHolidayFlg ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':BusinessDate' => $data['BusinessDate'],
                ':NationalHolidayName' => $data['NationalHolidayName'],
                ':NationalHolidayFlg' => isset($data['NationalHolidayFlg']) ? $data['NationalHolidayFlg'] : 0,
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
     * @param string $businessDate 日付 'yyyy-MM-dd'書式で通知
     * @return ResultInterface
     */
    public function saveUpdate($data, $businessDate)
    {
        $row = $this->find($businessDate)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE M_NationalHoliday ";
        $sql .= " SET ";
        $sql .= "     NationalHolidayName = :NationalHolidayName ";
        $sql .= " ,   NationalHolidayFlg = :NationalHolidayFlg ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE BusinessDate = :BusinessDate ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':BusinessDate' => $businessDate,
                ':NationalHolidayName' => $row['NationalHolidayName'],
                ':NationalHolidayFlg' => $row['NationalHolidayFlg'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
    }
}
