<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_ClaimBatchControl(請求バッチ管理)テーブルへのアダプタ
 */
class TableClaimBatchControl
{
    protected $_name = 'T_ClaimBatchControl';
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
     * 請求バッチ管理を取得する
     *
     * @param int $seq シーケンス
     * @return ResultInterface
     */
    public function find($seq)
    {
        $sql = " SELECT * FROM T_ClaimBatchControl WHERE Seq = :Seq ";

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
        $sql  = " INSERT INTO T_ClaimBatchControl (ClaimDate, MakeFlg, SendFlg, CompFlg) VALUES (";
        $sql .= "   :ClaimDate ";
        $sql .= " , :MakeFlg ";
        $sql .= " , :SendFlg ";
        $sql .= " , :CompFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':ClaimDate' => $data['ClaimDate'],
                ':MakeFlg' => isset($data['MakeFlg']) ? $data['MakeFlg'] : 0,
                ':SendFlg' => isset($data['SendFlg']) ? $data['SendFlg'] : 0,
                ':CompFlg' => isset($data['CompFlg']) ? $data['CompFlg'] : 0,
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
    }

    /**
     * 指定されたレコードを更新する。
     *
     * @param array $data 更新内容
     * @param int $siteId サイトID
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

        $sql  = " UPDATE T_ClaimBatchControl ";
        $sql .= " SET ";
        $sql .= "     ClaimDate = :ClaimDate ";
        $sql .= " ,   MakeFlg = :MakeFlg ";
        $sql .= " ,   SendFlg = :SendFlg ";
        $sql .= " ,   CompFlg = :CompFlg ";
        $sql .= " WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
                ':ClaimDate' => $row['ClaimDate'],
                ':MakeFlg' => $row['MakeFlg'],
                ':SendFlg' => $row['SendFlg'],
                ':CompFlg' => $row['CompFlg'],
        );

        return $stm->execute($prm);
    }
}
