<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_NgAccessMypageOrder(不正アクセス注文マイページ)テーブルへのアダプタ
 */
class TableNgAccessMypageOrder
{
    protected $_name = 'T_NgAccessMypageOrder';
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
     * 不正アクセス注文マイページ情報データを取得する
     *
     * @param int $seq SEQ
     * @return ResultInterface
     */
    public function find($seq)
    {
        $sql  = " SELECT * FROM T_NgAccessMypageOrder WHERE Seq = :Seq ";

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
        $sql  = " INSERT INTO T_NgAccessMypageOrder (Phone, OemId, NgAccessCount, NgAccessReferenceDate, UpdateDate) VALUES (";
        $sql .= "   :Phone ";
        $sql .= " , :OemId ";
        $sql .= " , :NgAccessCount ";
        $sql .= " , :NgAccessReferenceDate ";
        $sql .= " , :UpdateDate ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Phone' => $data['Phone'],
                ':OemId' => $data['OemId'],
                ':NgAccessCount' => isset($data['NgAccessCount']) ? $data['NgAccessCount'] : 0,
                ':NgAccessReferenceDate' => $data['NgAccessReferenceDate'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
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

        $sql  = " UPDATE T_NgAccessMypageOrder ";
        $sql .= " SET ";
        $sql .= "     Phone = :Phone ";
        $sql .= " ,   OemId = :OemId ";
        $sql .= " ,   NgAccessCount = :NgAccessCount ";
        $sql .= " ,   NgAccessReferenceDate = :NgAccessReferenceDate ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
                ':Phone' => $row['Phone'],
                ':OemId' => $row['OemId'],
                ':NgAccessCount' => $row['NgAccessCount'],
                ':NgAccessReferenceDate' => $row['NgAccessReferenceDate'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
        );

        return $stm->execute($prm);
    }

    /**
     * 指定のOemID＋電話番号のレコードを取得する
     *
     * @param int $oemid OemId
     * @param string $phone 電話番号
     * @return ResultInterface
     */
    public function findOemPhone($oemid, $phone)
    {
        return $this->_adapter->query(" SELECT * FROM T_NgAccessMypageOrder WHERE OemId = :OemId AND Phone = :Phone "
            )->execute(array(':OemId' => $oemid, ':Phone' => $phone));
    }
}
