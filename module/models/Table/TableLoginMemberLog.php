<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;

/**
 * T_LoginMemberLog(ログインユーザーログ)テーブルへのアダプタ
 */
class TableLoginMemberLog
{


    protected $_name = 'T_LoginMemberLog';
    protected $_primary = array('LogId');
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
     * 新しいレコードをインサートする。
     *
     * @param array $data インサートする連想配列
     * @return プライマリキーのバリュー
     */
    public function saveNew($EnterpriseData,$OpData,$userAgent)
    {
        $sql  = " INSERT INTO T_LoginMemberLog (EnterpriseId, EnterpriseLoginId, OpId, OpLoginId, UserAgent, RegistDate) VALUES (";
        $sql .= "   :EnterpriseId ";
        $sql .= " , :EnterpriseLoginId ";
        $sql .= " , :OpId ";
        $sql .= " , :OpLoginId ";
        $sql .= " , :UserAgent ";
        $sql .= " , :RegistDate ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
        		':EnterpriseId' => $EnterpriseData->EnterpriseId,
        		':EnterpriseLoginId' => $EnterpriseData->LoginId,
        		':OpId' => $OpData->OpId,
        		':OpLoginId' => $OpData->LoginId,
        		':UserAgent' => $userAgent,
                ':RegistDate' => date('Y-m-d H:i:s'),
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
    }

}

