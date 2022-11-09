<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_ManagementCustomer(管理顧客)テーブルへのアダプタ
 */
class TableManagementCustomer
{
    protected $_name = 'T_ManagementCustomer';
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
     * 管理顧客データを取得する
     *
     * @param int $manCustId 管理顧客番号
     * @return ResultInterface
     */
    public function find($manCustId)
    {
        $sql = " SELECT * FROM T_ManagementCustomer WHERE ManCustId = :ManCustId ";

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
        $sql  = " INSERT INTO T_ManagementCustomer (GoodFlg, BlackFlg, ClaimerFlg, RemindStopFlg, IdentityDocumentFlg, NameKj, NameKn, PostalCode, PrefectureCode, PrefectureName, City, Town, Building, UnitingAddress, Phone, MailAddress, Note, RegNameKj, RegUnitingAddress, RegPhone, SearchNameKj, SearchNameKn, SearchPhone, SearchUnitingAddress, IluCustomerId, IluCustomerListFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :GoodFlg ";
        $sql .= " , :BlackFlg ";
        $sql .= " , :ClaimerFlg ";
        $sql .= " , :RemindStopFlg ";
        $sql .= " , :IdentityDocumentFlg ";
        $sql .= " , :NameKj ";
        $sql .= " , :NameKn ";
        $sql .= " , :PostalCode ";
        $sql .= " , :PrefectureCode ";
        $sql .= " , :PrefectureName ";
        $sql .= " , :City ";
        $sql .= " , :Town ";
        $sql .= " , :Building ";
        $sql .= " , :UnitingAddress ";
        $sql .= " , :Phone ";
        $sql .= " , :MailAddress ";
        $sql .= " , :Note ";
        $sql .= " , :RegNameKj ";
        $sql .= " , :RegUnitingAddress ";
        $sql .= " , :RegPhone ";
        $sql .= " , :SearchNameKj ";
        $sql .= " , :SearchNameKn ";
        $sql .= " , :SearchPhone ";
        $sql .= " , :SearchUnitingAddress ";
        $sql .= " , :IluCustomerId ";
        $sql .= " , :IluCustomerListFlg ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':GoodFlg' => isset($data['GoodFlg']) ? $data['GoodFlg'] : 0,
                ':BlackFlg' => isset($data['BlackFlg']) ? $data['BlackFlg'] : 0,
                ':ClaimerFlg' => isset($data['ClaimerFlg']) ? $data['ClaimerFlg'] : 0,
                ':RemindStopFlg' => isset($data['RemindStopFlg']) ? $data['RemindStopFlg'] : 0,
                ':IdentityDocumentFlg' => isset($data['IdentityDocumentFlg']) ? $data['IdentityDocumentFlg'] : 0,
                ':NameKj' => $data['NameKj'],
                ':NameKn' => $data['NameKn'],
                ':PostalCode' => $data['PostalCode'],
                ':PrefectureCode' => $data['PrefectureCode'],
                ':PrefectureName' => $data['PrefectureName'],
                ':City' => $data['City'],
                ':Town' => $data['Town'],
                ':Building' => $data['Building'],
                ':UnitingAddress' => $data['UnitingAddress'],
                ':Phone' => $data['Phone'],
                ':MailAddress' => $data['MailAddress'],
                ':Note' => $data['Note'],
                ':RegNameKj' => $data['RegNameKj'],
                ':RegUnitingAddress' => $data['RegUnitingAddress'],
                ':RegPhone' => $data['RegPhone'],
                ':SearchNameKj' => $data['SearchNameKj'],
                ':SearchNameKn' => $data['SearchNameKn'],
                ':SearchPhone' => $data['SearchPhone'],
                ':SearchUnitingAddress' => $data['SearchUnitingAddress'],
                ':IluCustomerId' => $data['IluCustomerId'],
                ':IluCustomerListFlg' => isset($data['IluCustomerListFlg']) ? $data['IluCustomerListFlg'] : 0,
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

        $sql  = " UPDATE T_ManagementCustomer ";
        $sql .= " SET ";
        $sql .= "     GoodFlg = :GoodFlg ";
        $sql .= " ,   BlackFlg = :BlackFlg ";
        $sql .= " ,   ClaimerFlg = :ClaimerFlg ";
        $sql .= " ,   RemindStopFlg = :RemindStopFlg ";
        $sql .= " ,   IdentityDocumentFlg = :IdentityDocumentFlg ";
        $sql .= " ,   NameKj = :NameKj ";
        $sql .= " ,   NameKn = :NameKn ";
        $sql .= " ,   PostalCode = :PostalCode ";
        $sql .= " ,   PrefectureCode = :PrefectureCode ";
        $sql .= " ,   PrefectureName = :PrefectureName ";
        $sql .= " ,   City = :City ";
        $sql .= " ,   Town = :Town ";
        $sql .= " ,   Building = :Building ";
        $sql .= " ,   UnitingAddress = :UnitingAddress ";
        $sql .= " ,   Phone = :Phone ";
        $sql .= " ,   MailAddress = :MailAddress ";
        $sql .= " ,   Note = :Note ";
        $sql .= " ,   RegNameKj = :RegNameKj ";
        $sql .= " ,   RegUnitingAddress = :RegUnitingAddress ";
        $sql .= " ,   RegPhone = :RegPhone ";
        $sql .= " ,   SearchNameKj = :SearchNameKj ";
        $sql .= " ,   SearchNameKn = :SearchNameKn ";
        $sql .= " ,   SearchPhone = :SearchPhone ";
        $sql .= " ,   SearchUnitingAddress = :SearchUnitingAddress ";
        $sql .= " ,   IluCustomerId = :IluCustomerId ";
        $sql .= " ,   IluCustomerListFlg = :IluCustomerListFlg ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE ManCustId = :ManCustId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':ManCustId' => $manCustId,
                ':GoodFlg' => $row['GoodFlg'],
                ':BlackFlg' => $row['BlackFlg'],
                ':ClaimerFlg' => $row['ClaimerFlg'],
                ':RemindStopFlg' => $row['RemindStopFlg'],
                ':IdentityDocumentFlg' => $row['IdentityDocumentFlg'],
                ':NameKj' => $row['NameKj'],
                ':NameKn' => $row['NameKn'],
                ':PostalCode' => $row['PostalCode'],
                ':PrefectureCode' => $row['PrefectureCode'],
                ':PrefectureName' => $row['PrefectureName'],
                ':City' => $row['City'],
                ':Town' => $row['Town'],
                ':Building' => $row['Building'],
                ':UnitingAddress' => $row['UnitingAddress'],
                ':Phone' => $row['Phone'],
                ':MailAddress' => $row['MailAddress'],
                ':Note' => $row['Note'],
                ':RegNameKj' => $row['RegNameKj'],
                ':RegUnitingAddress' => $row['RegUnitingAddress'],
                ':RegPhone' => $row['RegPhone'],
                ':SearchNameKj' => $row['SearchNameKj'],
                ':SearchNameKn' => $row['SearchNameKn'],
                ':SearchPhone' => $row['SearchPhone'],
                ':SearchUnitingAddress' => $row['SearchUnitingAddress'],
                ':IluCustomerId' => $row['IluCustomerId'],
                ':IluCustomerListFlg' => $row['IluCustomerListFlg'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
    }

    /**
     * 管理顧客の存在チェック
     * (存在する時はMAX値を、存在しない時は-1を戻す)
     *
     * @param int $orderSeq 注文SEQ
     * @return -1 | MAX値
     */
    public function getMaxManCustId($orderSeq)
    {
$sql = <<<EOQ
SELECT IFNULL(MAX(mc.ManCustId), -1) AS MaxManCustId
FROM   T_Customer c
       LEFT OUTER JOIN T_ManagementCustomer mc
                    ON c.RegNameKj         = mc.RegNameKj
                   AND c.RegPhone          = mc.RegPhone
                   AND c.RegUnitingAddress = mc.RegUnitingAddress
WHERE  c.OrderSeq  = :OrderSeq
EOQ;

        $stm = $this->_adapter->query($sql);

        return $stm->execute(array(':OrderSeq' => $orderSeq))->current()['MaxManCustId'];
    }

    /**
     * 管理顧客データを注文Seqから取得する
     *
     * @param int $orderSeq 注文Seq
     */
    public function findByOrderSeq($orderSeq) {
        $sql  = "SELECT MC.* ";
        $sql .= "FROM T_Customer C ";
        $sql .= "INNER JOIN T_EnterpriseCustomer EC ON EC.EntCustSeq = C.EntCustSeq ";
        $sql .= "INNER JOIN T_ManagementCustomer MC ON MC.ManCustId = EC.ManCustId ";
        $sql .= "WHERE C.OrderSeq = :OrderSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $orderSeq,
        );

        return $stm->execute($prm);
    }
}
