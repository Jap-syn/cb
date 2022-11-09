<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_NgAccessClear(不正アクセス解除指示)テーブルへのアダプタ
 */
class TableNgAccessClear
{
    protected $_name = 'T_NgAccessClear';
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
     * 不正アクセス解除指示データを取得する
     *
     * @param int $seq SEQ
     * @return ResultInterface
     */
    public function find($seq)
    {
        $sql  = " SELECT * FROM T_NgAccessClear WHERE Seq = :Seq ";

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
        $sql  = " INSERT INTO T_NgAccessClear (LoginId, Type, Status, ServerStatus, IndicateDate) VALUES (";
        $sql .= "   :LoginId ";
        $sql .= " , :Type ";
        $sql .= " , :Status ";
        $sql .= " , :ServerStatus ";
        $sql .= " , :IndicateDate ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':LoginId' => $data['LoginId'],
                ':Type' => $data['Type'],
                ':Status' => isset($data['Status']) ? $data['Status'] : 1/* 指示済 */,
                ':ServerStatus' => $data['ServerStatus'],
                ':IndicateDate' => isset($data['IndicateDate']) ? $data['IndicateDate'] : date('Y-m-d H:i:s'),
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

        $sql  = " UPDATE T_NgAccessClear ";
        $sql .= " SET ";
        $sql .= "     LoginId = :LoginId ";
        $sql .= " ,   Type = :Type ";
        $sql .= " ,   Status = :Status ";
        $sql .= " ,   ServerStatus = :ServerStatus ";
        $sql .= " ,   IndicateDate = :IndicateDate ";
        $sql .= " WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
                ':LoginId' => $row['LoginId'],
                ':Type' => $row['Type'],
                ':Status' => $row['Status'],
                ':ServerStatus' => $row['ServerStatus'],
                ':IndicateDate' => $row['IndicateDate'],
        );

        return $stm->execute($prm);
    }

    /**
     * 不正アクセス解除指示データを(ログインID,タイプより)取得する
     *
     * @param string $loginid ログインID
     * @param int $type タイプ
     * @return ResultInterface
     */
    public function findLoginidType($loginid, $type)
    {
        return $this->_adapter->query(" SELECT Seq FROM T_NgAccessClear WHERE LoginId = :LoginId AND Type = :Type "
            )->execute(array(':LoginId' => $loginid, ':Type' => $type));
    }
}
