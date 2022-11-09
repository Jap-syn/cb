<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_ApiUserテーブルへのアダプタ
 */
class TableApiUser
{
	protected $_name = 'T_ApiUser';
	protected $_primary = array('ApiUserId');
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
	 * 指定APIユーザーIDのAPIユーザーデータを取得する。
	 *
	 * @param string $apiUserId APIユーザーID
	 * @return ResultInterface
	 */
	public function findApiUser($apiUserId)
	{
        $sql = " SELECT * FROM T_ApiUser WHERE ApiUserId = :ApiUserId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':ApiUserId' => $apiUserId,
        );

        return $stm->execute($prm);
	}

	/**
	 * 指定APIユーザーIDの有効なAPIユーザーデータを取得する。
	 *
	 * @param $apiUserId APIユーザーID
	 * @return ResultInterface
	 */
	public function getValidApiUser($apiUserId)
	{
        $sql = " SELECT * FROM T_ApiUser WHERE ValidFlg = 1 AND ApiUserId = :ApiUserId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':ApiUserId' => $apiUserId,
        );

        return $stm->execute($prm);
	}

    /**
     * 指定OEMに紐づくAPIユーザ取得
     *
     * @param $oem_id OEMID
     * @return ResultInterface
     */
	public function getAllValidApiUsers($oem_id = 0)
	{
        $sql = " SELECT * FROM T_ApiUser WHERE ValidFlg = 1 AND IFNULL(OemId, 0) = :OemId ORDER BY ApiUserId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OemId' => $oem_id,
        );

        return $stm->execute($prm);
	}

	/**
	 * APIユーザーデータを取得する
	 * @param string $whereCondition
	 * @param string $orderCondition
	 * @return ResultInterface
	 */
	public function fetchAll($whereCondition = "", $orderCondition = "")
	{
	    $sql  = " SELECT * FROM T_ApiUser ";
		    if ( strlen($whereCondition) > 0 ) {
	        $sql .= (" WHERE " . $whereCondition);
	    }
	    if ( strlen($orderCondition) > 0 ) {
	        $sql .= (" ORDER BY " . $orderCondition);
	    }
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
        $sql  = " INSERT INTO T_ApiUser (ApiUserNameKj, ApiUserNameKn, RegistDate, ServiceInDate, AuthenticationKey, PostalCode, PrefectureCode, PrefectureName, City, Town, Building, RepNameKj, RepNameKn, Phone, Fax, CpNameKj, CpNameKn, DivisionName, MailAddress, ContactPhoneNumber, ContactFaxNumber, ValidFlg, InvalidatedDate, InvalidatedReason, ConnectIpAddressList, Note, OemId, RegistId, UpdateDate, UpdateId) VALUES (";
        $sql .= "   :ApiUserNameKj ";
        $sql .= " , :ApiUserNameKn ";
        $sql .= " , :RegistDate ";
        $sql .= " , :ServiceInDate ";
        $sql .= " , :AuthenticationKey ";
        $sql .= " , :PostalCode ";
        $sql .= " , :PrefectureCode ";
        $sql .= " , :PrefectureName ";
        $sql .= " , :City ";
        $sql .= " , :Town ";
        $sql .= " , :Building ";
        $sql .= " , :RepNameKj ";
        $sql .= " , :RepNameKn ";
        $sql .= " , :Phone ";
        $sql .= " , :Fax ";
        $sql .= " , :CpNameKj ";
        $sql .= " , :CpNameKn ";
        $sql .= " , :DivisionName ";
        $sql .= " , :MailAddress ";
        $sql .= " , :ContactPhoneNumber ";
        $sql .= " , :ContactFaxNumber ";
        $sql .= " , :ValidFlg ";
        $sql .= " , :InvalidatedDate ";
        $sql .= " , :InvalidatedReason ";
        $sql .= " , :ConnectIpAddressList ";
        $sql .= " , :Note ";
        $sql .= " , :OemId ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':ApiUserNameKj' => $data['ApiUserNameKj'],
                ':ApiUserNameKn' => $data['ApiUserNameKn'],
                ':RegistDate' => date('Y-m-d H:i:s'),
                ':ServiceInDate' => $data['ServiceInDate'],
                ':AuthenticationKey' => $data['AuthenticationKey'],
                ':PostalCode' => $data['PostalCode'],
                ':PrefectureCode' => $data['PrefectureCode'],
                ':PrefectureName' => $data['PrefectureName'],
                ':City' => $data['City'],
                ':Town' => $data['Town'],
                ':Building' => $data['Building'],
                ':RepNameKj' => $data['RepNameKj'],
                ':RepNameKn' => $data['RepNameKn'],
                ':Phone' => $data['Phone'],
                ':Fax' => $data['Fax'],
                ':CpNameKj' => $data['CpNameKj'],
                ':CpNameKn' => $data['CpNameKn'],
                ':DivisionName' => $data['DivisionName'],
                ':MailAddress' => $data['MailAddress'],
                ':ContactPhoneNumber' => $data['ContactPhoneNumber'],
                ':ContactFaxNumber' => $data['ContactFaxNumber'],
                ':ValidFlg' => isset($data['ValidFlg']) ? $data['ValidFlg'] : 1,
                ':InvalidatedDate' => $data['InvalidatedDate'],
                ':InvalidatedReason' => $data['InvalidatedReason'],
                ':ConnectIpAddressList' => $data['ConnectIpAddressList'],
                ':Note' => $data['Note'],
                ':OemId' => $data['OemId'],
                ':RegistId' => $data['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $data['UpdateId'],


        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
	}

	/**
	 * 指定されたレコードを更新する。
	 *
	 * @param array $data 更新内容
	 * @param string $apiuserid 更新するAPIユーザーID
	 */
	public function saveUpdate($data, $apiuserid)
	{
        $sql = " SELECT * FROM T_ApiUser WHERE ApiUserId = :ApiUserId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':ApiUserId' => $apiuserid,
        );

        $row = $stm->execute($prm)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_ApiUser ";
        $sql .= " SET ";
        $sql .= "     ApiUserNameKj = :ApiUserNameKj ";
        $sql .= " ,   ApiUserNameKn = :ApiUserNameKn ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   ServiceInDate = :ServiceInDate ";
        $sql .= " ,   AuthenticationKey = :AuthenticationKey ";
        $sql .= " ,   PostalCode = :PostalCode ";
        $sql .= " ,   PrefectureCode = :PrefectureCode ";
        $sql .= " ,   PrefectureName = :PrefectureName ";
        $sql .= " ,   City = :City ";
        $sql .= " ,   Town = :Town ";
        $sql .= " ,   Building = :Building ";
        $sql .= " ,   RepNameKj = :RepNameKj ";
        $sql .= " ,   RepNameKn = :RepNameKn ";
        $sql .= " ,   Phone = :Phone ";
        $sql .= " ,   Fax = :Fax ";
        $sql .= " ,   CpNameKj = :CpNameKj ";
        $sql .= " ,   CpNameKn = :CpNameKn ";
        $sql .= " ,   DivisionName = :DivisionName ";
        $sql .= " ,   MailAddress = :MailAddress ";
        $sql .= " ,   ContactPhoneNumber = :ContactPhoneNumber ";
        $sql .= " ,   ContactFaxNumber = :ContactFaxNumber ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " ,   InvalidatedDate = :InvalidatedDate ";
        $sql .= " ,   InvalidatedReason = :InvalidatedReason ";
        $sql .= " ,   ConnectIpAddressList = :ConnectIpAddressList ";
        $sql .= " ,   Note = :Note ";
        $sql .= " ,   OemId = :OemId ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " WHERE ApiUserId = :ApiUserId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':ApiUserId' => $apiuserid,
                ':ApiUserNameKj' => $row['ApiUserNameKj'],
                ':ApiUserNameKn' => $row['ApiUserNameKn'],
                ':RegistDate' => $row['RegistDate'],
                ':ServiceInDate' => $row['ServiceInDate'],
                ':AuthenticationKey' => $row['AuthenticationKey'],
                ':PostalCode' => $row['PostalCode'],
                ':PrefectureCode' => $row['PrefectureCode'],
                ':PrefectureName' => $row['PrefectureName'],
                ':City' => $row['City'],
                ':Town' => $row['Town'],
                ':Building' => $row['Building'],
                ':RepNameKj' => $row['RepNameKj'],
                ':RepNameKn' => $row['RepNameKn'],
                ':Phone' => $row['Phone'],
                ':Fax' => $row['Fax'],
                ':CpNameKj' => $row['CpNameKj'],
                ':CpNameKn' => $row['CpNameKn'],
                ':DivisionName' => $row['DivisionName'],
                ':MailAddress' => $row['MailAddress'],
                ':ContactPhoneNumber' => $row['ContactPhoneNumber'],
                ':ContactFaxNumber' => $row['ContactFaxNumber'],
                ':ValidFlg' => $row['ValidFlg'],
                ':InvalidatedDate' => $row['InvalidatedDate'],
                ':InvalidatedReason' => $row['InvalidatedReason'],
                ':ConnectIpAddressList' => $row['ConnectIpAddressList'],
                ':Note' => $row['Note'],
                ':OemId' => $row['OemId'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
        );

        return $stm->execute($prm);
    }
}
