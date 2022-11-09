<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_AuthenticationLog(認証ログ情報)テーブルへのアダプタ
 */
class TableAuthenticationLog
{
    protected $_name = 'T_AuthenticationLog';
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
     * 認証ログ情報データを取得する
     *
     * @param int $seq SEQ
     * @return ResultInterface
     */
    public function find($seq)
    {
        $sql  = " SELECT * FROM T_AuthenticationLog WHERE Seq = :Seq ";

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
        $sql  = " INSERT INTO T_AuthenticationLog (LogType, TargetApp, LoginId, AltLoginId, IpAddress, ClientHash, Result, LogTime, DeleteFlg, OemAccessId) VALUES (";
        $sql .= "   :LogType ";
        $sql .= " , :TargetApp ";
        $sql .= " , :LoginId ";
        $sql .= " , :AltLoginId ";
        $sql .= " , :IpAddress ";
        $sql .= " , :ClientHash ";
        $sql .= " , :Result ";
        $sql .= " , :LogTime ";
        $sql .= " , :DeleteFlg ";
        $sql .= " , :OemAccessId ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':LogType' => isset($data['LogType']) ? $data['LogType'] : 0,
                ':TargetApp' => $data['TargetApp'],
                ':LoginId' => $data['LoginId'],
                ':AltLoginId' => $data['AltLoginId'],
                ':IpAddress' => $data['IpAddress'],
                ':ClientHash' => $data['ClientHash'],
                ':Result' => $data['Result'],
                ':LogTime' => $data['LogTime'],
                ':DeleteFlg' => isset($data['DeleteFlg']) ? $data['DeleteFlg'] : 0,
                ':OemAccessId' => $data['OemAccessId'],
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

        $sql  = " UPDATE T_AuthenticationLog ";
        $sql .= " SET ";
        $sql .= "     LogType = :LogType ";
        $sql .= " ,   TargetApp = :TargetApp ";
        $sql .= " ,   LoginId = :LoginId ";
        $sql .= " ,   AltLoginId = :AltLoginId ";
        $sql .= " ,   IpAddress = :IpAddress ";
        $sql .= " ,   ClientHash = :ClientHash ";
        $sql .= " ,   Result = :Result ";
        $sql .= " ,   LogTime = :LogTime ";
        $sql .= " ,   DeleteFlg = :DeleteFlg ";
        $sql .= " ,   OemAccessId = :OemAccessId ";
        $sql .= " WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
                ':LogType' => $row['LogType'],
                ':TargetApp' => $row['TargetApp'],
                ':LoginId' => $row['LoginId'],
                ':AltLoginId' => $row['AltLoginId'],
                ':IpAddress' => $row['IpAddress'],
                ':ClientHash' => $row['ClientHash'],
                ':Result' => $row['Result'],
                ':LogTime' => $row['LogTime'],
                ':DeleteFlg' => $row['DeleteFlg'],
                ':OemAccessId' => $row['OemAccessId'],
        );

        return $stm->execute($prm);
    }

}
