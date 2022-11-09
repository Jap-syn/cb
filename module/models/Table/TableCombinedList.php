<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;

/**
 * T_CombinedList(名寄せリスト)テーブルへのアダプタ
 */
class TableCombinedList
{
    protected $_name = 'T_CombinedList';
    protected $_primary = array ('CombinedListId', 'ManCustId');
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
     * 名寄せリストデータを取得する(有効フラグ＝有効データに限る)
     *
     * @param int $combinedListId 名寄せリストID
     * @param int $manCustId 管理顧客番号
     * @return ResultInterface
     */
    public function find($combinedListId, $manCustId)
    {
        $sql = " SELECT * FROM T_CombinedList WHERE ValidFlg = 1 AND CombinedListId = :CombinedListId AND ManCustId = :ManCustId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':CombinedListId' => $combinedListId,
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
        $sql  = " INSERT INTO T_CombinedList (CombinedListId, ManCustId, LikenessFlg, CombinedDictateFlg, CombinedDictateDate, CombinedDate, AggregationLevel, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :CombinedListId ";
        $sql .= " , :ManCustId ";
        $sql .= " , :LikenessFlg ";
        $sql .= " , :CombinedDictateFlg ";
        $sql .= " , :CombinedDictateDate ";
        $sql .= " , :CombinedDate ";
        $sql .= " , :AggregationLevel ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':CombinedListId' => $data['CombinedListId'],
                ':ManCustId' => $data['ManCustId'],
                ':LikenessFlg' => isset($data['LikenessFlg']) ? $data['LikenessFlg'] : 0,
                ':CombinedDictateFlg' => isset($data['CombinedDictateFlg']) ? $data['CombinedDictateFlg'] : 0,
                ':CombinedDictateDate' => $data['CombinedDictateDate'],
                ':CombinedDate' => $data['CombinedDate'],
                ':AggregationLevel' => $data['AggregationLevel'],
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
     * @param int $combinedListId 名寄せリストID
     * @param int $manCustId 管理顧客番号
     * @return ResultInterface
     */
    public function saveUpdate($data, $combinedListId, $manCustId)
    {
        $row = $this->find($combinedListId, $manCustId)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_CombinedList ";
        $sql .= " SET ";
        $sql .= "     LikenessFlg = :LikenessFlg ";
        $sql .= " ,   CombinedDictateFlg = :CombinedDictateFlg ";
        $sql .= " ,   CombinedDictateDate = :CombinedDictateDate ";
        $sql .= " ,   CombinedDate = :CombinedDate ";
        $sql .= " ,   AggregationLevel = :AggregationLevel ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE CombinedListId = :CombinedListId ";
        $sql .= " AND   ManCustId = :ManCustId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':CombinedListId' => $combinedListId,
                ':ManCustId' => $manCustId,
                ':LikenessFlg' => $row['LikenessFlg'],
                ':CombinedDictateFlg' => $row['CombinedDictateFlg'],
                ':CombinedDictateDate' => $row['CombinedDictateDate'],
                ':CombinedDate' => $row['CombinedDate'],
                ':AggregationLevel' => $row['AggregationLevel'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
    }

    /**
     * 顧客統合対象リストを取得する
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function getCustomerCombinedList()
    {
        $sql = <<<EOQ
SELECT 	CL.CombinedListId,
		SUM(MC.GoodFlg) Good,
        SUM(MC.BlackFlg) Black,
        SUM(MC.ClaimerFlg) Claimer,
        SUM(MC.RemindStopFlg) RemindStop,
        SUM(MC.IdentityDocumentFlg) IdentityDocument
FROM	T_CombinedList CL
INNER JOIN
		T_ManagementCustomer MC
ON		CL.ManCustId = MC.ManCustId
WHERE	CL.ValidFlg = 1
AND		CL.CombinedDictateFlg = 1
AND		MC.ValidFlg = 1
GROUP BY
		CL.CombinedListId
EOQ;
        return $this->_adapter->query($sql)->execute(null);
    }

    /**
     * 指定した名寄せリストIDで取得した最小の管理顧客IDを取得
     * @param int $combinedListId
     * @return int 管理顧客ID
     */
    public function getMinManCustId($combinedListId)
    {
        $sql = <<<EOQ
SELECT	MIN(CL.ManCustId) ID
FROM 	T_CombinedList CL
INNER JOIN
		T_ManagementCustomer MC
ON		CL.ManCustId = MC.ManCustId
WHERE	CL.CombinedListId = :CombinedListId
AND		CL.CombinedDictateFlg = 1
AND		MC.ValidFlg = 1
AND		CL.ValidFlg = 1
EOQ;
        $ri =  $this->_adapter->query($sql)->execute(array(
            ':CombinedListId' => $combinedListId
        ));
        $rs = new ResultSet();
        $minManCustId = $rs->initialize($ri)->toArray();
        return $minManCustId[0]['ID'];
    }

    /**
     * 指定した名寄せリストIDで取得した最大の管理顧客IDを取得
     * @param int $combinedListId
     * @return int 管理顧客ID
     */
    public function getMaxManCustId($combinedListId)
    {
        $sql = <<<EOQ
SELECT	MAX(CL.ManCustId) ID
FROM 	T_CombinedList CL
INNER JOIN
		T_ManagementCustomer MC
ON		CL.ManCustId = MC.ManCustId
WHERE	CL.CombinedListId = :CombinedListId
AND		CL.CombinedDictateFlg = 1
AND		MC.ValidFlg = 1
AND		CL.ValidFlg = 1
AND 	CL.LikenessFlg = 0
EOQ;
        $ri =  $this->_adapter->query($sql)->execute(array(
                ':CombinedListId' => $combinedListId
        ));
        $rs = new ResultSet();
        $maxManCustId = $rs->initialize($ri)->toArray();
        return $maxManCustId[0]['ID'];
    }

    /**
     * 名寄せリストIDに対する管理顧客IDを取得
     * @param int $combinedListId
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function getManCustList($combinedListId)
    {
        $sql = <<<EOQ
SELECT  MC.*
FROM	T_CombinedList CL
INNER JOIN
		T_ManagementCustomer MC
ON		CL.ManCustId = MC.ManCustId
WHERE	CL.ValidFlg = 1
AND		CL.CombinedDictateFlg = 1
AND		MC.ValidFlg = 1
AND     CL.CombinedListId = :CombinedListId
EOQ;
         $ri = $this->_adapter->query($sql)->execute(array(
            ':CombinedListId' => $combinedListId
        ));
         $rs = new ResultSet();
         $manCustIdList = $rs->initialize($ri)->toArray();
         return $manCustIdList;
    }

    /**
     * 指定されたレコードを更新する。
     *
     * @param array $data 更新内容
     * @param int $combinedListId 名寄せリストID
     * @param int $manCustId 管理顧客番号
     * @return ResultInterface
     */
    public function updateCombinedDictateFlg($data)
    {
        $sql  = " UPDATE T_CombinedList ";
        $sql .= " SET ";
        $sql .= "     CombinedDictateFlg = :CombinedDictateFlg ";
        $sql .= " ,   CombinedDate = :CombinedDate ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " WHERE CombinedListId = :CombinedListId ";
        $sql .= " AND   CombinedDictateFlg = 1 ";
        $sql .= " AND   ValidFlg = 1 ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':CombinedDictateFlg' => $data['CombinedDictateFlg'],
                ':CombinedDate' => $data['CombinedDate'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $data['UpdateId'],
                ':CombinedListId' => $data['CombinedListId'],
        );

        return $stm->execute($prm);
    }

}
