<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_MypageTempRegist(マイページ仮登録)テーブルへのアダプタ
 */
class TableMypageTempRegist
{
    protected $_name = 'T_MypageTempRegist';
    protected $_primary = array('TempRegistId');
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
     * マイページ仮登録データを取得する
     *
     * @param int $tempRegistId マイページ仮登録ID
     * @return ResultInterface
     */
    public function find($tempRegistId)
    {
        $sql = " SELECT * FROM T_MypageTempRegist WHERE TempRegistId = :TempRegistId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':TempRegistId' => $tempRegistId,
        );

        return $stm->execute($prm);
    }

    /**
     * 指定条件（AND）のマイページ仮登録データを取得する。
     *
     * @param array $conditionArray 検索条件を格納した連想配列
     * @param boolean $isAsc プライマリキーのオーダー
     * @return ResultInterface
     */
    public function findTempRegist($conditionArray, $isAsc = false)
    {
        $prm = array();
        $sql  = " SELECT * FROM T_MypageTempRegist WHERE 1 = 1 ";
        foreach ($conditionArray as $key => $value) {
            $sql .= (" AND " . $key . " = :" . $key);
            $prm += array(':' . $key => $value);
        }
        $sql .= " ORDER BY TempRegistId " . ($isAsc ? "asc" : "desc");

        $stm = $this->_adapter->query($sql);

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
        $sql  = " INSERT INTO T_MypageTempRegist (OemId, MailAddress, UrlParameter, CreateDate, ValidDate, OrderSeq, RegistDate, UpdateDate, ValidFlg) VALUES (";
        $sql .= "   :OemId ";
        $sql .= " , :MailAddress ";
        $sql .= " , :UrlParameter ";
        $sql .= " , :CreateDate ";
        $sql .= " , :ValidDate ";
        $sql .= " , :OrderSeq ";
        $sql .= " , :RegistDate ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OemId' => $data['OemId'],
                ':MailAddress' => $data['MailAddress'],
                ':UrlParameter' => $data['UrlParameter'],
                ':CreateDate' => $data['CreateDate'],
                ':ValidDate' => $data['ValidDate'],
                ':OrderSeq' => $data['OrderSeq'],
                ':RegistDate' => date('Y-m-d H:i:s'),
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':ValidFlg' => isset($data['ValidFlg']) ? $data['ValidFlg'] : 1,
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
    }

    /**
     * 指定されたレコードを更新する。
     *
     * @param array $data 更新内容
     * @param int $tempRegistId マイページ仮登録ID
     * @return ResultInterface
     */
    public function saveUpdate($data, $tempRegistId)
    {
        $row = $this->find($userId)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_MypageTempRegist ";
        $sql .= " SET ";
        $sql .= "     OemId = :OemId ";
        $sql .= " ,   MailAddress = :MailAddress ";
        $sql .= " ,   UrlParameter = :UrlParameter ";
        $sql .= " ,   CreateDate = :CreateDate ";
        $sql .= " ,   ValidDate = :ValidDate ";
        $sql .= " ,   OrderSeq = :OrderSeq ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE TempRegistId = :TempRegistId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':TempRegistId' => $tempRegistId,
                ':OemId' => $row['OemId'],
                ':MailAddress' => $row['MailAddress'],
                ':UrlParameter' => $row['UrlParameter'],
                ':CreateDate' => $row['CreateDate'],
                ':ValidDate' => $row['ValidDate'],
                ':OrderSeq' => $row['OrderSeq'],
                ':RegistDate' => $row['RegistDate'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
    }

    /**
     * 指定された条件でレコードを更新する。
     *
     * @param array $data 更新内容
     * @param array $conditionArray
     */
    public function saveUpdateWhere($data, $conditionArray)
    {
        $prm = array();
        $sql  = " SELECT * FROM T_MypageTempRegist WHERE 1 = 1 ";
        foreach ($conditionArray as $key => $value) {
            $sql .= (" AND " . $key . " = :" . $key);
            $prm += array(':' . $key => $value);
        }

        $stm = $this->_adapter->query($sql);

        $ri = $stm->execute($prm);

        foreach ($ri AS $row) {
            foreach ($data as $key => $value) {
                if (array_key_exists($key, $row)) {
                    $row[$key] = $value;
                }
            }

            // 指定されたレコードを更新する
            $this->saveUpdate($row, $row['TempRegistId']);
        }
    }
}
