<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_Menu(メニュー)テーブルへのアダプタ
 */
class TableMenu
{
    protected $_name = 'T_Menu';
    protected $_primary = array('MenuSeq');
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
     * メニューデータを取得する
     *
     * @param int $menuSeq メニューSEQ
     * @return ResultInterface
     */
    public function find($menuSeq)
    {
        $sql  = " SELECT * FROM T_Menu WHERE MenuSeq = :MenuSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':MenuSeq' => $menuSeq,
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
        $sql  = " INSERT INTO T_Menu (Module, Class, Id, Ordinal, Href, Title, Text, Desc, New, Image_Url, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :Module ";
        $sql .= " , :Class ";
        $sql .= " , :Id ";
        $sql .= " , :Ordinal ";
        $sql .= " , :Href ";
        $sql .= " , :Title ";
        $sql .= " , :Text ";
        $sql .= " , :Desc ";
        $sql .= " , :New ";
        $sql .= " , :Image_Url ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Module' => $data['Module'],
                ':Class' => $data['Class'],
                ':Id' => $data['Id'],
                ':Ordinal' => $data['Ordinal'],
                ':Href' => $data['Href'],
                ':Title' => $data['Title'],
                ':Text' => $data['Text'],
                ':Desc' => $data['Desc'],
                ':New' => $data['New'],
                ':Image_Url' => $data['Image_Url'],
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
     * @param int $menuSeq メニューSEQ
     * @return ResultInterface
     */
    public function saveUpdate($data, $menuSeq)
    {
        $row = $this->find($menuSeq)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_Menu ";
        $sql .= " SET ";
        $sql .= "     Module = :Module ";
        $sql .= " ,   Class = :Class ";
        $sql .= " ,   Id = :Id ";
        $sql .= " ,   Ordinal = :Ordinal ";
        $sql .= " ,   Href = :Href ";
        $sql .= " ,   Title = :Title ";
        $sql .= " ,   Text = :Text ";
        $sql .= " ,   Desc = :Desc ";
        $sql .= " ,   New = :New ";
        $sql .= " ,   Image_Url = :Image_Url ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE MenuSeq = :MenuSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':MenuSeq' => $menuSeq,
                ':Module' => $row['Module'],
                ':Class' => $row['Class'],
                ':Id' => $row['Id'],
                ':Ordinal' => $row['Ordinal'],
                ':Href' => $row['Href'],
                ':Title' => $row['Title'],
                ':Text' => $row['Text'],
                ':Desc' => $row['Desc'],
                ':New' => $row['New'],
                ':Image_Url' => $row['Image_Url'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
    }

}
