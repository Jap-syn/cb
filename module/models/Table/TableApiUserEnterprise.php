<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_ApiUserEnterpriseテーブルへのアダプタ
 */
class TableApiUserEnterprise
{
	protected $_name = 'T_ApiUserEnterprise';
	protected $_primary = array('ApiUserId', 'SiteId');
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
	 *
	 * @param $apiUserId APIユーザーID
	 * @param $siteId サイトID
	 * @return bool
	 */
	public function isExistsRelation($apiUserId, $siteId)
	{
        $sql = " SELECT COUNT(1) AS cnt FROM T_ApiUserEnterprise WHERE ApiUserId = :ApiUserId AND SiteId = :SiteId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':ApiUserId' => $apiUserId,
                ':SiteId' => $siteId,
        );

        $ri = $stm->execute($prm);
        return ((int)$ri->current()['cnt'] > 0) ? true : false;
    }

	/**
	 * 指定のAPIユーザIDに関連するすべてのT_Siteの情報を取得する
	 * @param int $apiUserId APIユーザID
	 * @return ResultInterface
	 */
	public function findRelatedEnterprises($apiUserId)
	{
        $ids = array();

        $sql = " SELECT * FROM T_ApiUserEnterprise WHERE ApiUserId = :ApiUserId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':ApiUserId' => $apiUserId,
        );

        $ri = $stm->execute($prm);

        foreach($ri as $row) {
            $ids[] = $row['SiteId'];
        }

        if( empty($ids) ) return null;

        $sql = " SELECT * FROM T_Site WHERE SiteId IN (" . implode(",", $ids) . ") ";
        return $this->_adapter->query($sql)->execute(null);
	}

	/**
	 * 指定のサイトIDに関連するすべてのT_ApiUserの情報を取得する
	 * @param int $siteId サイトID
	 * @return ResultInterface
	 */
	public function findRelatedApiUsers($siteId)
	{
	    $ids = array();

        $sql = " SELECT * FROM T_ApiUserEnterprise WHERE SiteId = :SiteId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':SiteId' => $siteId,
        );

        $ri = $stm->execute($prm);

        foreach($ri as $row) {
            $ids[] = $row['ApiUserId'];
        }

        if( empty($ids) ) return null;

        $sql = " SELECT * FROM T_ApiUser WHERE ApiUserId IN (" . implode(",", $ids) . ") ";
        return $this->_adapter->query($sql)->execute(null);
	}

	/**
	 * 新しいレコードをインサートする。
	 *
	 * @param array $data インサートする連想配列
	 * @return プライマリキーのバリュー
	 */
	public function saveNew($data)
	{
        $sql  = " INSERT INTO T_ApiUserEnterprise (ApiUserId, SiteId, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :ApiUserId ";
        $sql .= " , :SiteId ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':ApiUserId' => $data['ApiUserId'],
                ':SiteId' => $data['SiteId'],
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
	 * 指定されたAPIユーザIDのすべてのレコードを削除する。
	 *
	 * @param int $apiUserId APIユーザID
	 */
	public function deleteByApiUserId($apiUserId)
	{
        $sql = " DELETE FROM T_ApiUserEnterprise WHERE ApiUserId = :ApiUserId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':ApiUserId' => $apiUserId,
        );

        return $stm->execute($prm);
	}

	/**
	 * 指定されたサイトIDのすべてのレコードを削除する。
	 *
	 * @param int $siteId サイトID
	 */
	public function deleteBySiteId($siteId)
	{
	    $sql = " DELETE FROM T_ApiUserEnterprise WHERE SiteId = :SiteId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':SiteId' => $siteId,
        );

        return $stm->execute($prm);
	}

	/**
	 * 指定の加盟店IDに関連するすべてのT_ApiUserの情報を取得する
	 * @param int $siteId サイトID
	 * @return ResultInterface
	 */
	public function findApiUsersByEid($enterpriseId)
	{

	    $sql  = " SELECT au.* ";
	    $sql .= " FROM T_ApiUser au ";
	    $sql .= " INNER JOIN T_ApiUserEnterprise aue ON aue.ApiUserId = au.ApiUserId ";
	    $sql .= " INNER JOIN T_Site s ON s.SiteId = aue.SiteId ";
	    $sql .= " WHERE s.EnterpriseId = :EnterpriseId ";
	    $sql .= " AND   s.ValidFlg = 1 ";

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':EnterpriseId' => $enterpriseId,
	    );

	    return $stm->execute($prm);
	}
}
