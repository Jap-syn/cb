<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_SiteTotal(サイト別集計)テーブルへのアダプタ
 */
class TableSiteTotal
{
    protected $_name = 'T_SiteTotal';
    protected $_primary = array('SiteId');
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
     * サイト別集計データを取得する
     *
     * @param int $siteId サイトID
     * @return ResultInterface
     */
    public function find($siteId)
    {
        $sql = " SELECT * FROM T_SiteTotal WHERE SiteId = :SiteId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':SiteId' => $siteId,
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
        $sql  = " INSERT INTO T_SiteTotal (SiteId, NpTotal, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :SiteId ";
        $sql .= " , :NpTotal ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':SiteId' => $data['SiteId'],
                ':NpTotal' => $data['NpTotal'],
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
     * @param int $siteId サイトID
     * @return ResultInterface
     */
    public function saveUpdate($data, $siteId)
    {
        $row = $this->find($siteId)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_SiteTotal ";
        $sql .= " SET ";
        $sql .= "     NpTotal = :NpTotal ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE SiteId = :SiteId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':SiteId' => $siteId,
                ':NpTotal' => $row['NpTotal'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
    }
}
