<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_MypageOrder(注文マイページ)テーブルへのアダプタ
 */
class TableMypageOrder
{
    protected $_name = 'T_MypageOrder';
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
     * 注文マイページデータを取得する(有効フラグ＝有効データに限る)
     *
     * @param int $seq 注文マイページSEQ
     * @return ResultInterface
     */
    public function find($seq)
    {
        $sql = " SELECT * FROM T_MypageOrder WHERE ValidFlg = 1 AND Seq = :Seq ";

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
        $sql  = " INSERT INTO T_MypageOrder (OrderSeq, Token, Phone, LoginId, LoginPasswd, Hashed, ValidToDate, OemId, AccessKey, AccessKeyValidToDate, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :OrderSeq ";
        $sql .= " , :Token ";
        $sql .= " , :Phone ";
        $sql .= " , :LoginId ";
        $sql .= " , :LoginPasswd ";
        $sql .= " , :Hashed ";
        $sql .= " , :ValidToDate ";
        $sql .= " , :OemId ";
        $sql .= " , :AccessKey ";
        $sql .= " , :AccessKeyValidToDate ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $data['OrderSeq'],
                ':Token' => $data['Token'],
                ':Phone' => $data['Phone'],
                ':LoginId' => $data['LoginId'],
                ':LoginPasswd' => $data['LoginPasswd'],
                ':Hashed' => isset($data['Hashed']) ? $data['Hashed'] : 0,
                ':ValidToDate' => $data['ValidToDate'],
                ':OemId' => $data['OemId'],
                ':AccessKey' => $data['AccessKey'],
                ':AccessKeyValidToDate' => $data['AccessKeyValidToDate'],
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
     * @param int $seq 注文マイページSEQ
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

        $sql  = " UPDATE T_MypageOrder ";
        $sql .= " SET ";
        $sql .= "     OrderSeq = :OrderSeq ";
        $sql .= " ,   Token = :Token ";
        $sql .= " ,   Phone = :Phone ";
        $sql .= " ,   LoginId = :LoginId ";
        $sql .= " ,   LoginPasswd = :LoginPasswd ";
        $sql .= " ,   Hashed = :Hashed ";
        $sql .= " ,   ValidToDate = :ValidToDate ";
        $sql .= " ,   OemId = :OemId ";
        $sql .= " ,   AccessKey = :AccessKey ";
        $sql .= " ,   AccessKeyValidToDate = :AccessKeyValidToDate ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
                ':OrderSeq' => $row['OrderSeq'],
                ':Token' => $row['Token'],
                ':Phone' => $row['Phone'],
                ':LoginId' => $row['LoginId'],
                ':LoginPasswd' => $row['LoginPasswd'],
                ':Hashed' => $row['Hashed'],
                ':ValidToDate' => $row['ValidToDate'],
                ':OemId' => $row['OemId'],
                ':AccessKey' => $row['AccessKey'],
                ':AccessKeyValidToDate' => $row['AccessKeyValidToDate'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
    }

    /**
     * 指定のログインIDの件数を取得する
     * @param int $token
     */
    public function countByLoginId($loginId){
        $sql = " SELECT COUNT(*) cnt FROM T_MypageOrder WHERE LoginId = :LoginId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':LoginId' => $loginId,
        );

        return $stm->execute($prm)->current()['cnt'];
    }

    /**
     * 指定注文SEQのデータを論理削除する
     * @param int $oseq
     * @param int $userId
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function deleteByOrderSeq($oseq, $userId) {
        $sql  = " UPDATE T_MypageOrder ";
        $sql .= " SET ";
        $sql .= "     UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE OrderSeq = :OrderSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $oseq,
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $userId,
                ':ValidFlg' => 0,
        );

        return $stm->execute($prm);
    }

    /**
     * 指定のｱｸｾｽURLの件数を取得する
     * @param string $accessKey
     */
    public function countByAccessKey($accessKey){
        $sql = " SELECT COUNT(*) cnt FROM T_MypageOrder WHERE AccessKey = :AccessKey ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':AccessKey' => $accessKey,
        );

        return $stm->execute($prm)->current()['cnt'];
    }

    /**
     * 注文マイページデータを取得する(有効フラグ＝有効データに限る)
     *
     * @param int $orderseq 注文SEQ
     * @return ResultInterface
     */
    public function findByOrderSeq($orderseq)
    {
        $sql = " SELECT * FROM T_MypageOrder WHERE ValidFlg = 1 AND OrderSeq = :OrderSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $orderseq,
        );

        return $stm->execute($prm);
    }

}
