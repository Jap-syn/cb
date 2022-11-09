<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_NgAccessIp(不正アクセス)テーブルへのアダプタ
 */
class TableNgAccessIp
{
    protected $_name = 'T_NgAccessIp';
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
     * 不正アクセス情報データを取得する
     *
     * @param int $seq SEQ
     * @return ResultInterface
     */
    public function find($seq)
    {
        $sql  = " SELECT * FROM T_NgAccessIp WHERE Seq = :Seq ";

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
        $sql  = " INSERT INTO T_NgAccessIp (IpAddress, Count, UpdateDate, NgAccessReferenceDate) VALUES (";
        $sql .= "   :IpAddress ";
        $sql .= " , :Count ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :NgAccessReferenceDate ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':IpAddress' => $data['IpAddress'],
                ':Count' => isset($data['Count']) ? $data['Count'] : 0,
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':NgAccessReferenceDate' => $data['NgAccessReferenceDate'],
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
    }

    /**
     * 指定されたレコードを更新する。
     *
     * @param array $data 更新内容
     * @param int $seq SEQ
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

        $sql  = " UPDATE T_NgAccessIp ";
        $sql .= " SET ";
        $sql .= "     IpAddress = :IpAddress ";
        $sql .= " ,   Count = :Count ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   NgAccessReferenceDate = :NgAccessReferenceDate ";
        $sql .= " WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
                ':IpAddress' => $row['IpAddress'],
                ':Count' => $row['Count'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':NgAccessReferenceDate' => $row['NgAccessReferenceDate'],
        );

        return $stm->execute($prm);
    }

    /**
     * 指定のIPアドレスのレコードを取得する
     *
     * @param string $ipaddress IPアドレス
     * @return array|false
     * @see 該当がない時はfalseが戻される
     */
    public function findIpAddress($ipaddress)
    {
        return $this->_adapter->query(" SELECT * FROM T_NgAccessIp WHERE IpAddress = :IpAddress ")->execute(
            array(':IpAddress' => $ipaddress))->current();
    }
}
