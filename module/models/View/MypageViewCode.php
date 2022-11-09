<?php
namespace models\View;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * MV_Code(コードマスター)テーブルへのアダプタ
 */
class MypageViewCode
{
    protected $_name = 'MV_Code';
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
     * コードマスターデータを取得する
     *
     * @param int $codeId コード識別ID
     * @param int $keyCode KEYコード
     * @return ResultInterface
     */
    public function find($codeId, $keyCode)
    {
        $sql = " SELECT * FROM MV_Code WHERE CodeId = :CodeId AND KeyCode = :KeyCode ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':CodeId' => $codeId,
                ':KeyCode' => $keyCode,
        );

        return $stm->execute($prm);
    }
    
    public function getClass1ByClass3($codeId, $class3)
    {
        $sql = ' SELECT Class1 FROM MV_Code WHERE CodeId = :CodeId AND Class3 LIKE "%' . $class3 . '%" AND ValidFlg=1';
        
        $stm = $this->_adapter->query($sql);
        
        $prm = array(
            ':CodeId' => $codeId,
        );
        
        return $stm->execute($prm);
    }
}
