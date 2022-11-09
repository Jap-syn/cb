<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_NotificationManage(通知内容管理)テーブルへのアダプタ
 */
class TableNotificationManage {

    protected $_name = 'T_NotificationManage';
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
     * 通知内容管理データを取得する
     *
     * @param int $seq シーケンス
     * @return ResultInterface
     */
    public function find($seq)
    {
        $sql  = " SELECT * FROM T_NotificationManage WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
        );

        return $stm->execute($prm);
    }

    /**
     * 新しいレコードをインサートする
     *
     * @param array $data インサートする連想配列
     * @return プライマリキーのバリュー
     */
    public function saveNew($data)
    {
        $sql  = " INSERT INTO T_NotificationManage (Token, ReceivedData, ReceivedData2) VALUES (";
        $sql .= "   :Token ";
        $sql .= " , :ReceivedData ";
        $sql .= " , :ReceivedData2 ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Token' => $data['Token'],
                ':ReceivedData' => $data['ReceivedData'],
                ':ReceivedData2' => $data['ReceivedData2'],
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

        $sql  = " UPDATE T_NotificationManage ";
        $sql .= " SET ";
        $sql .= "     Token = :Token ";
        $sql .= " ,   ReceivedData = :ReceivedData ";
        $sql .= " ,   ReceivedData2 = :ReceivedData2 ";
        $sql .= " WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
                ':Token' => $row['Token'],
                ':ReceivedData' => $row['ReceivedData'],
                ':ReceivedData2' => $row['ReceivedData2'],
        );

        return $stm->execute($prm);
    }

    /**
     * トークンに関連付けられた通知内容管理データを取得する
     *
     * @param string $token トークン
     * @return ResultInterface
     */
    public function findByToken($token)
    {
        $sql  = " SELECT * FROM T_NotificationManage WHERE Token = :Token ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Token' => $token,
        );

        return $stm->execute($prm);
    }

    /**
     * トークンに関連付けられた通知内容管理データ件数を取得する
     *
     * @param string $token トークン
     * @return int 件数
     */
    public function countByToken($token)
    {
        $sql = " SELECT COUNT(1) AS cnt FROM T_NotificationManage WHERE Token = :Token ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Token' => $token,
        );

        return $stm->execute($prm)->current()['cnt'];
    }

    /**
     * 指定されたレコードを削除する
     *
     * @param int $seq
     * @return ResultInterface
     */
    public function deleteBySeq($seq)
    {
        $sql = " DELETE FROM T_NotificationManage WHERE Seq = :Seq ";

        return $this->_adapter->query($sql)->execute(array(':Seq' => $seq));
    }
}
