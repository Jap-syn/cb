<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_ClaimError(請求エラーリスト)テーブルへのアダプタ
 */
class TableClaimError
{
    protected $_name = 'T_ClaimError';
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
     * 請求エラーリストを取得する
     *
     * @param int $seq シーケンス
     * @return ResultInterface
     */
    public function find($seq)
    {
        $sql = " SELECT * FROM T_ClaimError WHERE Seq = :Seq ";

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
        $sql  = " INSERT INTO T_ClaimError (OrderSeq, RegistDate, ErrorCode, ErrorMsg) VALUES (";
        $sql .= "   :OrderSeq ";
        $sql .= " , :RegistDate ";
        $sql .= " , :ErrorCode ";
        $sql .= " , :ErrorMsg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $data['OrderSeq'],
                ':RegistDate' => date('Y-m-d H:i:s'),
                ':ErrorCode' => $data['ErrorCode'],
                ':ErrorMsg' => $data['ErrorMsg'],
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

        $sql  = " UPDATE T_ClaimError ";
        $sql .= " SET ";
        $sql .= "     OrderSeq = :OrderSeq ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   ErrorCode = :ErrorCode ";
        $sql .= " ,   ErrorMsg = :ErrorMsg ";
        $sql .= " WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
                ':OrderSeq' => $row['OrderSeq'],
                ':RegistDate' => $row['RegistDate'],
                ':ErrorCode' => $row['ErrorCode'],
                ':ErrorMsg' => $row['ErrorMsg'],
        );

        return $stm->execute($prm);
    }
}
