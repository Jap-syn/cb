<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_SkipTarget(スキップ対象者リスト)テーブルへのアダプタ
 */
class TableSkipTarget
{
    protected $_name = 'T_SkipTarget';
    protected $_primary = array('ManCustId');
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
     * スキップ対象者データを取得する
     *
     * @param int $manCustId 管理顧客番号
     * @return ResultInterface
     */
    public function find($manCustId)
    {
        $sql = " SELECT * FROM T_SkipTarget WHERE ManCustId = :ManCustId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':ManCustId' => $manCustId,
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
        $sql  = " INSERT INTO T_SkipTarget (ManCustId, RegNameKj, RegUnitingAddress, RegPhone, MailAddress, LastReceiptDate, LastClaimDate, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :ManCustId ";
        $sql .= " , :RegNameKj ";
        $sql .= " , :RegUnitingAddress ";
        $sql .= " , :RegPhone ";
        $sql .= " , :MailAddress ";
        $sql .= " , :LastReceiptDate ";
        $sql .= " , :LastClaimDate ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':ManCustId' => $data['ManCustId'],
                ':RegNameKj' => $data['RegNameKj'],
                ':RegUnitingAddress' => $data['RegUnitingAddress'],
                ':RegPhone' => $data['RegPhone'],
                ':MailAddress' => $data['MailAddress'],
                ':LastReceiptDate' => $data['LastReceiptDate'],
                ':LastClaimDate' => $data['LastClaimDate'],
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
     * @param int $manCustId 管理顧客番号
     * @return ResultInterface
     */
    public function saveUpdate($data, $manCustId)
    {
        $row = $this->find($manCustId)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_SkipTarget ";
        $sql .= " SET ";
        $sql .= " ,   RegNameKj = :RegNameKj ";
        $sql .= " ,   RegUnitingAddress = :RegUnitingAddress ";
        $sql .= " ,   RegPhone = :RegPhone ";
        $sql .= " ,   MailAddress = :MailAddress ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   LastReceiptDate = :LastReceiptDate ";
        $sql .= " ,   LastClaimDate = :LastClaimDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE ManCustId = :ManCustId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':ManCustId' => $manCustId,
                ':RegNameKj' => $row['RegNameKj'],
                ':RegUnitingAddress' => $row['RegUnitingAddress'],
                ':RegPhone' => $row['RegPhone'],
                ':MailAddress' => $row['MailAddress'],
                ':LastReceiptDate' => $row['LastReceiptDate'],
                ':LastClaimDate' => $row['LastClaimDate'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
    }




}
