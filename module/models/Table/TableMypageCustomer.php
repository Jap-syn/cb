<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_MypageCustomerテーブルへのアダプタ
 */
class TableMypageCustomer
{
	/**
	 * テーブル名
	 *
	 * @var string
	 */
	protected $_name = 'T_MypageCustomer';

	/**
	 * プライマリキー
	 *
	 * @var string
	 */
	protected $_primary = array('CustomerId');

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
	 * マイページ顧客データを取得する
	 *
	 * @param int $customerId
	 * @return ResultInterface
	 */
	public function find($customerId)
	{
	    $sql  = " SELECT * FROM T_MypageCustomer WHERE CustomerId = :CustomerId ";

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':CustomerId' => $customerId,
	    );

	    return $stm->execute($prm);
	}

	/**
	 * 指定条件（AND）のマイページ顧客データを取得する。
	 *
	 * @param array $conditionArray 検索条件を格納した連想配列
	 * @param boolean $isAsc プライマリキーのオーダー
	 * @return ResultInterface
	 */
	public function findCustomer($conditionArray, $isAsc = false)
	{
        $prm = array();
        $sql  = " SELECT * FROM T_MypageCustomer WHERE 1 = 1 ";
        foreach ($conditionArray as $key => $value) {
            $sql .= (" AND " . $key . " = :" . $key);
            $prm += array(':' . $key => $value);
        }
        $sql .= " ORDER BY CustomerId " . ($isAsc ? "asc" : "desc");

        $stm = $this->_adapter->query($sql);

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
        $sql  = " INSERT INTO T_MypageCustomer (OemId, ManCustId, MailAddress, LoginId, LoginPasswd, Hashed, LastLoginDate, NameSeiKj, NameMeiKj, NameSeiKn, NameMeiKn, Sex, Birthday, PostalCode, PrefectureCode, PrefectureName, Address, Building, UnitingAddress, Phone, MobilePhone, IdentityDocumentClass, RegNameKj, RegUnitingAddress, RegPhone, RegMobilePhone, MailSubject, NgAccessCount, NgAccessReferenceDate, AccessKey, AccessKeyValidToDate, Reserve, RegistDate, UpdateDate, ValidFlg) VALUES (";
        $sql .= "   :OemId ";
        $sql .= " , :ManCustId ";
        $sql .= " , :MailAddress ";
        $sql .= " , :LoginId ";
        $sql .= " , :LoginPasswd ";
        $sql .= " , :Hashed ";
        $sql .= " , :LastLoginDate ";
        $sql .= " , :NameSeiKj ";
        $sql .= " , :NameMeiKj ";
        $sql .= " , :NameSeiKn ";
        $sql .= " , :NameMeiKn ";
        $sql .= " , :Sex ";
        $sql .= " , :Birthday ";
        $sql .= " , :PostalCode ";
        $sql .= " , :PrefectureCode ";
        $sql .= " , :PrefectureName ";
        $sql .= " , :Address ";
        $sql .= " , :Building ";
        $sql .= " , :UnitingAddress ";
        $sql .= " , :Phone ";
        $sql .= " , :MobilePhone ";
        $sql .= " , :IdentityDocumentClass ";
        $sql .= " , :RegNameKj ";
        $sql .= " , :RegUnitingAddress ";
        $sql .= " , :RegPhone ";
        $sql .= " , :RegMobilePhone ";
        $sql .= " , :MailSubject ";
        $sql .= " , :NgAccessCount ";
        $sql .= " , :NgAccessReferenceDate ";
        $sql .= " , :AccessKey ";
        $sql .= " , :AccessKeyValidToDate ";
        $sql .= " , :Reserve ";
        $sql .= " , :RegistDate ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OemId' => $data['OemId'],
                ':ManCustId' => $data['ManCustId'],
                ':MailAddress' => $data['MailAddress'],
                ':LoginId' => $data['LoginId'],
                ':LoginPasswd' => $data['LoginPasswd'],
                ':Hashed' => $data['Hashed'],
                ':LastLoginDate' => $data['LastLoginDate'],
                ':NameSeiKj' => $data['NameSeiKj'],
                ':NameMeiKj' => $data['NameMeiKj'],
                ':NameSeiKn' => $data['NameSeiKn'],
                ':NameMeiKn' => $data['NameMeiKn'],
                ':Sex' => $data['Sex'],
                ':Birthday' => $data['Birthday'],
                ':PostalCode' => $data['PostalCode'],
                ':PrefectureCode' => $data['PrefectureCode'],
                ':PrefectureName' => $data['PrefectureName'],
                ':Address' => $data['Address'],
                ':Building' => $data['Building'],
                ':UnitingAddress' => $data['UnitingAddress'],
                ':Phone' => $data['Phone'],
                ':MobilePhone' => $data['MobilePhone'],
                ':IdentityDocumentClass' => $data['IdentityDocumentClass'],
                ':RegNameKj' => $data['RegNameKj'],
                ':RegUnitingAddress' => $data['RegUnitingAddress'],
                ':RegPhone' => $data['RegPhone'],
                ':RegMobilePhone' => $data['RegMobilePhone'],
                ':MailSubject' => $data['MailSubject'],
                ':NgAccessCount' => isset($data['NgAccessCount']) ? $data['NgAccessCount'] : 0,
                ':NgAccessReferenceDate' => $data['NgAccessReferenceDate'],
                ':AccessKey' => $data['AccessKey'],
                ':AccessKeyValidToDate' => $data['AccessKeyValidToDate'],
                ':Reserve' => $data['Reserve'],
                ':RegistDate' => date('Y-m-d H:i:s'),
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':ValidFlg' => isset($data['ValidFlg']) ? $data['ValidFlg'] : 1,
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
	}

	/**
	 * 指定されたレコードを更新する。
	 *
	 * @param array $data 更新内容
	 * @param int $customerId 更新するcustomerId
	 */
	public function saveUpdate($data, $customerId)
	{
        $sql = " SELECT * FROM T_MypageCustomer WHERE CustomerId = :CustomerId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':CustomerId' => $customerId,
        );

        $row = $stm->execute($prm)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_MypageCustomer ";
        $sql .= " SET ";
        $sql .= "     OemId = :OemId ";
        $sql .= " ,   ManCustId = :ManCustId ";
        $sql .= " ,   MailAddress = :MailAddress ";
        $sql .= " ,   LoginId = :LoginId ";
        $sql .= " ,   LoginPasswd = :LoginPasswd ";
        $sql .= " ,   Hashed = :Hashed ";
        $sql .= " ,   LastLoginDate = :LastLoginDate ";
        $sql .= " ,   NameSeiKj = :NameSeiKj ";
        $sql .= " ,   NameMeiKj = :NameMeiKj ";
        $sql .= " ,   NameSeiKn = :NameSeiKn ";
        $sql .= " ,   NameMeiKn = :NameMeiKn ";
        $sql .= " ,   Sex = :Sex ";
        $sql .= " ,   Birthday = :Birthday ";
        $sql .= " ,   PostalCode = :PostalCode ";
        $sql .= " ,   PrefectureCode = :PrefectureCode ";
        $sql .= " ,   PrefectureName = :PrefectureName ";
        $sql .= " ,   Address = :Address ";
        $sql .= " ,   Building = :Building ";
        $sql .= " ,   UnitingAddress = :UnitingAddress ";
        $sql .= " ,   Phone = :Phone ";
        $sql .= " ,   MobilePhone = :MobilePhone ";
        $sql .= " ,   IdentityDocumentClass = :IdentityDocumentClass ";
        $sql .= " ,   RegNameKj = :RegNameKj ";
        $sql .= " ,   RegUnitingAddress = :RegUnitingAddress ";
        $sql .= " ,   RegPhone = :RegPhone ";
        $sql .= " ,   RegMobilePhone = :RegMobilePhone ";
        $sql .= " ,   MailSubject = :MailSubject ";
        $sql .= " ,   NgAccessCount = :NgAccessCount ";
        $sql .= " ,   NgAccessReferenceDate = :NgAccessReferenceDate ";
        $sql .= " ,   AccessKey = :AccessKey ";
        $sql .= " ,   AccessKeyValidToDate = :AccessKeyValidToDate ";
        $sql .= " ,   Reserve = :Reserve ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE CustomerId = :CustomerId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':CustomerId' => $customerId,
                ':OemId' => $row['OemId'],
                ':ManCustId' => $row['ManCustId'],
                ':MailAddress' => $row['MailAddress'],
                ':LoginId' => $row['LoginId'],
                ':LoginPasswd' => $row['LoginPasswd'],
                ':Hashed' => $row['Hashed'],
                ':LastLoginDate' => $row['LastLoginDate'],
                ':NameSeiKj' => $row['NameSeiKj'],
                ':NameMeiKj' => $row['NameMeiKj'],
                ':NameSeiKn' => $row['NameSeiKn'],
                ':NameMeiKn' => $row['NameMeiKn'],
                ':Sex' => $row['Sex'],
                ':Birthday' => $row['Birthday'],
                ':PostalCode' => $row['PostalCode'],
                ':PrefectureCode' => $row['PrefectureCode'],
                ':PrefectureName' => $row['PrefectureName'],
                ':Address' => $row['Address'],
                ':Building' => $row['Building'],
                ':UnitingAddress' => $row['UnitingAddress'],
                ':Phone' => $row['Phone'],
                ':MobilePhone' => $row['MobilePhone'],
                ':IdentityDocumentClass' => $row['IdentityDocumentClass'],
                ':RegNameKj' => $row['RegNameKj'],
                ':RegUnitingAddress' => $row['RegUnitingAddress'],
                ':RegPhone' => $row['RegPhone'],
                ':RegMobilePhone' => $row['RegMobilePhone'],
                ':MailSubject' => $row['MailSubject'],
                ':NgAccessCount' => $row['NgAccessCount'],
                ':NgAccessReferenceDate' => $row['NgAccessReferenceDate'],
                ':AccessKey' => $row['AccessKey'],
                ':AccessKeyValidToDate' => $row['AccessKeyValidToDate'],
                ':Reserve' => $row['Reserve'],
                ':RegistDate' => $row['RegistDate'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
	}

	/**
	 * 指定された条件でレコードを更新する。
	 *
	 * @param array $data 更新内容
	 * @param array $conditionArray
	 */
	public function saveUpdateWhere($data, $conditionArray)
	{
        $prm = array();
        $sql  = " SELECT * FROM T_MypageCustomer WHERE 1 = 1 ";
        foreach ($conditionArray as $key => $value) {
            $sql .= (" AND " . $key . " = :" . $key);
            $prm += array(':' . $key => $value);
        }

        $stm = $this->_adapter->query($sql);

        $ri = $stm->execute($prm);

        foreach ($ri AS $row) {
            foreach ($data as $key => $value) {
                if (array_key_exists($key, $row)) {
                    $row[$key] = $value;
                }
            }

            // 指定されたレコードを更新する
            $this->saveUpdate($row, $row['CustomerId']);
        }
	}

    /**
     * 指定されたレコードを削除する。
     * @param int $customerId 顧客ID
     */
    public function delete($customerId)
    {
        $sql = " DELETE FROM T_MypageCustomer WHERE CustomerId = :CustomerId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
            ':CustomerId' => $customerId,
        );

        return $stm->execute($prm);
    }

    /**
     * 指定ログインIDのマイページ顧客データを取得する。
     *
     * @param string $loginId ログインID
     * @return ResultInterface
     */
    public function findLoginId($loginId)
    {
       return $this->_adapter->query(" SELECT * FROM T_MypageCustomer WHERE LoginId = :LoginId "
            )->execute(array(':LoginId' => $loginId));
    }

    /**
     * 指定のｱｸｾｽURLの件数を取得する
     * @param string $accessKey
     * @return int 件数
     */
    public function countByAccessKey($accessKey)
    {
        $sql = " SELECT COUNT(1) AS cnt FROM T_MypageCustomer WHERE AccessKey = :AccessKey ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':AccessKey' => $accessKey,
        );

        return $stm->execute($prm)->current()['cnt'];
    }
}
