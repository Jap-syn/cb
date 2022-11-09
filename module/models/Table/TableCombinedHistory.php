<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_CombinedHistory(取りまとめ履歴)テーブルへのアダプタ
 */
class TableCombinedHistory
{
    protected $_name = 'T_CombinedHistory';
    protected $_primary = array ('CombinedHistorySeq', 'ManCustId');
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
     * 取りまとめ履歴データを取得する(有効フラグ＝有効データに限る)
     *
     * @param int $combinedHistorySeq 顧客統合履歴SEQ
     * @param int $manCustId 管理顧客番号
     * @return ResultInterface
     */
    public function find($combinedHistorySeq, $manCustId)
    {
        $sql = " SELECT * FROM T_CombinedHistory WHERE ValidFlg = 1 AND CombinedHistorySeq = :CombinedHistorySeq AND ManCustId = :ManCustId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':CombinedHistorySeq' => $combinedHistorySeq,
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
        $sql  = " INSERT INTO T_CombinedHistory (CombinedHistorySeq, ManCustId, CombinedClass, CombinedListId, GoodFlg, BlackFlg, ClaimerFlg, RemindStopFlg, IdentityDocumentFlg, NameKj, NameKn, PostalCode, PrefectureCode, PrefectureName, City, Town, Building, Phone, MailAddress, Note, IluCustomerListFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :CombinedHistorySeq ";
        $sql .= " , :ManCustId ";
        $sql .= " , :CombinedClass ";
        $sql .= " , :CombinedListId ";
        $sql .= " , :GoodFlg ";
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
        $sql .= " , :Phone ";
        $sql .= " , :MailAddress ";
        $sql .= " , :Note ";
        $sql .= " , :IluCustomerListFlg ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':CombinedHistorySeq' => $data['CombinedHistorySeq'],
                ':ManCustId' => $data['ManCustId'],
                ':CombinedClass' => $data['CombinedClass'],
                ':CombinedListId' => $data['CombinedListId'],
                ':GoodFlg' => $data['GoodFlg'],
                ':BlackFlg' => $data['BlackFlg'],
                ':ClaimerFlg' => $data['ClaimerFlg'],
                ':RemindStopFlg' => $data['RemindStopFlg'],
                ':IdentityDocumentFlg' => $data['IdentityDocumentFlg'],
                ':NameKj' => $data['NameKj'],
                ':NameKn' => $data['NameKn'],
                ':PostalCode' => $data['PostalCode'],
                ':PrefectureCode' => $data['PrefectureCode'],
                ':PrefectureName' => $data['PrefectureName'],
                ':City' => $data['City'],
                ':Town' => $data['Town'],
                ':Building' => $data['Building'],
                ':Phone' => $data['Phone'],
                ':MailAddress' => $data['MailAddress'],
                ':Note' => $data['Note'],
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
     * @param int $combinedHistorySeq 顧客統合履歴SEQ
     * @param int $manCustId 管理顧客番号
     * @return ResultInterface
     */
    public function saveUpdate($data, $combinedHistorySeq, $manCustId)
    {
        $row = $this->find($combinedHistorySeq, $manCustId)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_CombinedHistory ";
        $sql .= " SET ";
        $sql .= "     CombinedClass = :CombinedClass ";
        $sql .= " ,   CombinedListId = :CombinedListId ";
        $sql .= " ,   GoodFlg = :GoodFlg ";
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
        $sql .= " ,   Phone = :Phone ";
        $sql .= " ,   MailAddress = :MailAddress ";
        $sql .= " ,   Note = :Note ";
        $sql .= " ,   IluCustomerListFlg = :IluCustomerListFlg ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE CombinedHistorySeq = :CombinedHistorySeq ";
        $sql .= " AND   ManCustId = :ManCustId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':CombinedHistorySeq' => $combinedHistorySeq,
                ':ManCustId' => $manCustId,
                ':CombinedClass' => $row['CombinedClass'],
                ':CombinedListId' => $row['CombinedListId'],
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
                ':Phone' => $row['Phone'],
                ':MailAddress' => $row['MailAddress'],
                ':Note' => $row['Note'],
                ':IluCustomerListFlg' => $row['IluCustomerListFlg'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
    }
}
