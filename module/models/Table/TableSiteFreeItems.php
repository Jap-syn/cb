<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_SiteFreeItems(注文_追加情報)テーブルへのアダプタ
 */
class TableSiteFreeItems
{
    protected $_name = 'T_SiteFreeItems';
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
     * 請求バッチ管理を取得する
     *
     * @param int $seq シーケンス
     * @return ResultInterface
     */
    public function find($sid)
    {
        $sql = " SELECT * FROM T_SiteFreeItems WHERE SiteId = :SiteId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':SiteId' => $sid,
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
        $sql  = " INSERT INTO T_SiteFreeItems (SiteId, Free1, Free2, Free3, Free4, Free5, Free6, Free7, Free8, Free9, Free10, Free11, Free12, Free13, Free14, Free15, Free16, Free17, Free18, Free19, Free20) VALUES (";
        $sql .= "   :SiteId ";
        $sql .= " , :Free1 ";
        $sql .= " , :Free2 ";
        $sql .= " , :Free3 ";
        $sql .= " , :Free4 ";
        $sql .= " , :Free5 ";
        $sql .= " , :Free6 ";
        $sql .= " , :Free7 ";
        $sql .= " , :Free8 ";
        $sql .= " , :Free9 ";
        $sql .= " , :Free10 ";
        $sql .= " , :Free11 ";
        $sql .= " , :Free12 ";
        $sql .= " , :Free13 ";
        $sql .= " , :Free14 ";
        $sql .= " , :Free15 ";
        $sql .= " , :Free16 ";
        $sql .= " , :Free17 ";
        $sql .= " , :Free18 ";
        $sql .= " , :Free19 ";
        $sql .= " , :Free20 ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':SiteId' => $data['SiteId'],
                ':Free1'  => $data['Free1'],
                ':Free2'  => $data['Free2'],
                ':Free3'  => $data['Free3'],
                ':Free4'  => $data['Free4'],
                ':Free5'  => $data['Free5'],
                ':Free6'  => $data['Free6'],
                ':Free7'  => $data['Free7'],
                ':Free8'  => $data['Free8'],
                ':Free9'  => $data['Free9'],
                ':Free10' => $data['Free10'],
                ':Free11' => $data['Free11'],
                ':Free12' => $data['Free12'],
                ':Free13' => $data['Free13'],
                ':Free14' => $data['Free14'],
                ':Free15' => $data['Free15'],
                ':Free16' => $data['Free16'],
                ':Free17' => $data['Free17'],
                ':Free18' => $data['Free18'],
                ':Free19' => $data['Free19'],
                ':Free20' => $data['Free20'],
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
    }

    /**
     * 指定されたレコードを更新する。
     *
     * @param array $data 更新内容
     * @param int $sid 注文SEQ
     * @return ResultInterface
     */
    public function saveUpdate($data, $sid)
    {
        $row = $this->find($sid)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_SiteFreeItems ";
        $sql .= " SET ";
        $sql .= "   Free1  = :Free1 ";
        $sql .= " , Free2  = :Free2 ";
        $sql .= " , Free3  = :Free3 ";
        $sql .= " , Free4  = :Free4 ";
        $sql .= " , Free5  = :Free5 ";
        $sql .= " , Free6  = :Free6 ";
        $sql .= " , Free7  = :Free7 ";
        $sql .= " , Free8  = :Free8 ";
        $sql .= " , Free9  = :Free9 ";
        $sql .= " , Free10 = :Free10 ";
        $sql .= " , Free11 = :Free11 ";
        $sql .= " , Free12 = :Free12 ";
        $sql .= " , Free13 = :Free13 ";
        $sql .= " , Free14 = :Free14 ";
        $sql .= " , Free15 = :Free15 ";
        $sql .= " , Free16 = :Free16 ";
        $sql .= " , Free17 = :Free17 ";
        $sql .= " , Free18 = :Free18 ";
        $sql .= " , Free19 = :Free19 ";
        $sql .= " , Free20 = :Free20 ";
        $sql .= " WHERE SiteId = :SiteId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':SiteId' => $sid,
                ':Free1'  => $row['Free1'],
                ':Free2'  => $row['Free2'],
                ':Free3'  => $row['Free3'],
                ':Free4'  => $row['Free4'],
                ':Free5'  => $row['Free5'],
                ':Free6'  => $row['Free6'],
                ':Free7'  => $row['Free7'],
                ':Free8'  => $row['Free8'],
                ':Free9'  => $row['Free9'],
                ':Free10' => $row['Free10'],
                ':Free11' => $row['Free11'],
                ':Free12' => $row['Free12'],
                ':Free13' => $row['Free13'],
                ':Free14' => $row['Free14'],
                ':Free15' => $row['Free15'],
                ':Free16' => $row['Free16'],
                ':Free17' => $row['Free17'],
                ':Free18' => $row['Free18'],
                ':Free19' => $row['Free19'],
                ':Free20' => $row['Free20'],
        );

        return $stm->execute($prm);
    }
}
