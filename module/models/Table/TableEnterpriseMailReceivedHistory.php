<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_EnterpriseMailReceivedHistory(加盟店登録メール受信履歴)テーブルへのアダプタ
 */
class TableEnterpriseMailReceivedHistory
{
    protected $_name = 'T_EnterpriseMailReceivedHistory';
    protected $_primary = array('EntMailRcvdSeq');
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
     * 加盟店登録メール受信履歴データを取得する(有効フラグ＝有効データに限る)
     *
     * @param int $entMailRcvdSeq 履歴SEQ
     * @return ResultInterface
     */
    public function find($entMailRcvdSeq)
    {
        $sql = " SELECT * FROM T_EnterpriseMailReceivedHistory WHERE ValidFlg = 1 AND EntMailRcvdSeq = :EntMailRcvdSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EntMailRcvdSeq' => $entMailRcvdSeq,
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
        $sql  = " INSERT INTO T_EnterpriseMailReceivedHistory (EntMailRcvdSeq, ReceivedDate, GetDate, FromAddress, Subject, Body, ProcessClass, ErrorClass, EnterpriseId, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :EntMailRcvdSeq ";
        $sql .= " , :ReceivedDate ";
        $sql .= " , :GetDate ";
        $sql .= " , :FromAddress ";
        $sql .= " , :Subject ";
        $sql .= " , :Body ";
        $sql .= " , :ProcessClass ";
        $sql .= " , :ErrorClass ";
        $sql .= " , :EnterpriseId ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EntMailRcvdSeq' => $data['EntMailRcvdSeq'],
                ':ReceivedDate' => $data['ReceivedDate'],
                ':GetDate' => $data['GetDate'],
                ':FromAddress' => $data['FromAddress'],
                ':Subject' => $data['Subject'],
                ':Body' => $data['Body'],
                ':ProcessClass' => isset($data['ProcessClass']) ? $data['ProcessClass'] : 0,
                ':ErrorClass' => isset($data['ErrorClass']) ? $data['ErrorClass'] : 0,
                ':EnterpriseId' => $data['EnterpriseId'],
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
     * @param int $entMailRcvdSeq 履歴SEQ
     * @return ResultInterface
     */
    public function saveUpdate($data, $entMailRcvdSeq)
    {
        $row = $this->find($entMailRcvdSeq)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_EnterpriseMailReceivedHistory ";
        $sql .= " SET ";
        $sql .= "     ReceivedDate = :ReceivedDate ";
        $sql .= " ,   GetDate = :GetDate ";
        $sql .= " ,   FromAddress = :FromAddress ";
        $sql .= " ,   Subject = :Subject ";
        $sql .= " ,   Body = :Body ";
        $sql .= " ,   ProcessClass = :ProcessClass ";
        $sql .= " ,   ErrorClass = :ErrorClass ";
        $sql .= " ,   EnterpriseId = :EnterpriseId ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE EntMailRcvdSeq = :EntMailRcvdSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EntMailRcvdSeq' => $entMailRcvdSeq,
                ':ReceivedDate' => $row['ReceivedDate'],
                ':GetDate' => $row['GetDate'],
                ':FromAddress' => $row['FromAddress'],
                ':Subject' => $row['Subject'],
                ':Body' => $row['Body'],
                ':ProcessClass' => $row['ProcessClass'],
                ':ErrorClass' => $row['ErrorClass'],
                ':EnterpriseId' => $row['EnterpriseId'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
    }

    public function countNotProcessedData(){
        $sql = " SELECT COUNT(*) cnt FROM T_EnterpriseMailReceivedHistory WHERE ValidFlg = 1 AND ProcessClass = :ProcessClass ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':ProcessClass' => 0, // 0:未処理
        );

        return $stm->execute($prm)->current()['cnt'];
    }
}
