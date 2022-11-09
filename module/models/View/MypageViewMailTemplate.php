<?php
namespace models\View;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

class MypageViewMailTemplate
{
	protected $_name = 'MV_MailTemplate';
	protected $_primary = 'Id';
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
	 * メールテンプレートデータを取得する
	 *
	 * @param int $id
	 * @return ResultInterface
	 */
	public function find($id)
	{
	    $sql  = " SELECT * FROM MV_MailTemplate WHERE Id = :Id ";

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':Id' => $id,
	    );

	    return $stm->execute($prm);
	}

	/**
	 * 指定テンプレートクラスのテンプレートデータを取得する。
	 *
	 * @param int $class クラス
	 * @return ResultInterface
	 */
	public function findMailTemplate($class, $oemId = 0)
	{
	    if ($oemId == 0) {
	        $sql = " SELECT * FROM MV_MailTemplate WHERE (OemId = 0 OR OemId IS NULL) AND Class = :Class ";
	        $prm = array(
	                ':Class' => $class,
	        );
	    }
	    else {
	        $sql = " SELECT * FROM MV_MailTemplate WHERE Class = :Class AND OemId = :OemId ";
	        $prm = array(
	                ':Class' => $class,
	                ':OemId' => $oemId,
	        );
	    }

	    $stm = $this->_adapter->query($sql);

	    return $stm->execute($prm);
	}

}
