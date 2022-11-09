<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_Customerテーブルへのアダプタ
 */
class TableCustomer
{
	/**
	 * 郵便番号のカラム名
	 *
	 * @var string
	 */
	const ADDR_POSTAL_CODE = 'PostalCode';

	/**
	 * 都道府県コードのカラム名
	 *
	 * @var string
	 */
	const ADDR_PREF_CODE = 'PrefectureCode';

	/**
	 * 都道府県名のカラム名
	 *
	 * @var string
	 */
	const ADDR_PREF_NAME = 'PrefectureName';

	/**
	 * 市区郡のカラム名
	 *
	 * @var string
	 */
	const ADDR_CITY = 'City';

	/**
	 * 町名のカラム名
	 *
	 * @var string
	 */
	const ADDR_TOWN = 'Town';

	/**
	 * ビル名のカラム名
	 *
	 * @var string
	 */
	const ADDR_BLD = 'Building';

	/**
	 * 結合住所のカラム名
	 *
	 * @var string
	 */
	const ADDR_ADDRESS = 'UnitingAddress';

	/**
	 * 結合住所を構成する各パートを結合する文字列
	 *
	 * @var string
	 */
	const ADDR_UNIT_SEPARATOR = '';

	/**
	 * テーブル名
	 *
	 * @var string
	 */
	protected $_name = 'T_Customer';

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
	 * 指定条件（AND）の購入者データを取得する。
	 *
	 * @param array $conditionArray 検索条件を格納した連想配列
	 * @param boolean $isAsc プライマリキーのオーダー
	 * @return ResultInterface
	 */
	public function findCustomer($conditionArray, $isAsc = false)
	{
        $prm = array();
        $sql  = " SELECT * FROM T_Customer WHERE 1 = 1 ";
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
        $sql  = " INSERT INTO T_Customer (OrderSeq, NameKj, NameKn, PostalCode, PrefectureCode, PrefectureName, City, Town, Building, UnitingAddress, Hash_Name, Hash_Address, Phone, RealCallStatus, RealCallResult, RealCallScore, MailAddress, RealSendMailStatus, RealSendMailResult, RealSendMailScore, Occupation, Incre_ArName, Incre_NameScore, Incre_NameNote, Incre_ArAddr, Incre_AddressScore, Incre_AddressNote, Incre_MailDomainScore, Incre_MailDomainNote, Incre_PostalCodeScore, Incre_PostalCodeNote, Incre_MoneyScore, Incre_MoneyNote, Incre_ScoreTotal, eDen, PhoneHistory, Carrier, ValidTel, ValidMail, ValidAddress, ResidentCard, Cinfo1, CinfoNote1, CinfoStatus1, Cinfo2, CinfoNote2, CinfoStatus2, Cinfo3, CinfoNote3, CinfoStatus3, SearchNameKj, SearchNameKn, SearchPhone, SearchUnitingAddress, RegNameKj, RegUnitingAddress, RegPhone, Incre_ArTel, Incre_TelScore, Incre_TelNote, CorporateName, DivisionName, CpNameKj, EntCustSeq, AddressKn, RemindResult, EntCustId, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :OrderSeq ";
        $sql .= " , :NameKj ";
        $sql .= " , :NameKn ";
        $sql .= " , :PostalCode ";
        $sql .= " , :PrefectureCode ";
        $sql .= " , :PrefectureName ";
        $sql .= " , :City ";
        $sql .= " , :Town ";
        $sql .= " , :Building ";
        $sql .= " , :UnitingAddress ";
        $sql .= " , :Hash_Name ";
        $sql .= " , :Hash_Address ";
        $sql .= " , :Phone ";
        $sql .= " , :RealCallStatus ";
        $sql .= " , :RealCallResult ";
        $sql .= " , :RealCallScore ";
        $sql .= " , :MailAddress ";
        $sql .= " , :RealSendMailStatus ";
        $sql .= " , :RealSendMailResult ";
        $sql .= " , :RealSendMailScore ";
        $sql .= " , :Occupation ";
        $sql .= " , :Incre_ArName ";
        $sql .= " , :Incre_NameScore ";
        $sql .= " , :Incre_NameNote ";
        $sql .= " , :Incre_ArAddr ";
        $sql .= " , :Incre_AddressScore ";
        $sql .= " , :Incre_AddressNote ";
        $sql .= " , :Incre_MailDomainScore ";
        $sql .= " , :Incre_MailDomainNote ";
        $sql .= " , :Incre_PostalCodeScore ";
        $sql .= " , :Incre_PostalCodeNote ";
        $sql .= " , :Incre_MoneyScore ";
        $sql .= " , :Incre_MoneyNote ";
        $sql .= " , :Incre_ScoreTotal ";
        $sql .= " , :eDen ";
        $sql .= " , :PhoneHistory ";
        $sql .= " , :Carrier ";
        $sql .= " , :ValidTel ";
        $sql .= " , :ValidMail ";
        $sql .= " , :ValidAddress ";
        $sql .= " , :ResidentCard ";
        $sql .= " , :Cinfo1 ";
        $sql .= " , :CinfoNote1 ";
        $sql .= " , :CinfoStatus1 ";
        $sql .= " , :Cinfo2 ";
        $sql .= " , :CinfoNote2 ";
        $sql .= " , :CinfoStatus2 ";
        $sql .= " , :Cinfo3 ";
        $sql .= " , :CinfoNote3 ";
        $sql .= " , :CinfoStatus3 ";
        $sql .= " , :SearchNameKj ";
        $sql .= " , :SearchNameKn ";
        $sql .= " , :SearchPhone ";
        $sql .= " , :SearchUnitingAddress ";
        $sql .= " , :RegNameKj ";
        $sql .= " , :RegUnitingAddress ";
        $sql .= " , :RegPhone ";
        $sql .= " , :Incre_ArTel ";
        $sql .= " , :Incre_TelScore ";
        $sql .= " , :Incre_TelNote ";
        $sql .= " , :CorporateName ";
        $sql .= " , :DivisionName ";
        $sql .= " , :CpNameKj ";
        $sql .= " , :EntCustSeq ";
        $sql .= " , :AddressKn ";
        $sql .= " , :RemindResult ";
        $sql .= " , :EntCustId ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $data['OrderSeq'],
                ':NameKj' => $data['NameKj'],
                ':NameKn' => $data['NameKn'],
                ':PostalCode' => $data['PostalCode'],
                ':PrefectureCode' => $data['PrefectureCode'],
                ':PrefectureName' => $data['PrefectureName'],
                ':City' => $data['City'],
                ':Town' => $data['Town'],
                ':Building' => $data['Building'],
                ':UnitingAddress' => $data['UnitingAddress'],
                ':Hash_Name' => $data['Hash_Name'],
                ':Hash_Address' => $data['Hash_Address'],
                ':Phone' => $data['Phone'],
                ':RealCallStatus' => $data['RealCallStatus'],
                ':RealCallResult' => $data['RealCallResult'],
                ':RealCallScore' => $data['RealCallScore'],
                ':MailAddress' => $data['MailAddress'],
                ':RealSendMailStatus' => $data['RealSendMailStatus'],
                ':RealSendMailResult' => $data['RealSendMailResult'],
                ':RealSendMailScore' => $data['RealSendMailScore'],
                ':Occupation' => $data['Occupation'],
                ':Incre_ArName' => $data['Incre_ArName'],
                ':Incre_NameScore' => $data['Incre_NameScore'],
                ':Incre_NameNote' => $data['Incre_NameNote'],
                ':Incre_ArAddr' => $data['Incre_ArAddr'],
                ':Incre_AddressScore' => $data['Incre_AddressScore'],
                ':Incre_AddressNote' => $data['Incre_AddressNote'],
                ':Incre_MailDomainScore' => $data['Incre_MailDomainScore'],
                ':Incre_MailDomainNote' => $data['Incre_MailDomainNote'],
                ':Incre_PostalCodeScore' => $data['Incre_PostalCodeScore'],
                ':Incre_PostalCodeNote' => $data['Incre_PostalCodeNote'],
                ':Incre_MoneyScore' => $data['Incre_MoneyScore'],
                ':Incre_MoneyNote' => $data['Incre_MoneyNote'],
                ':Incre_ScoreTotal' => $data['Incre_ScoreTotal'],
                ':eDen' => $data['eDen'],
                ':PhoneHistory' => $data['PhoneHistory'],
                ':Carrier' => $data['Carrier'],
                ':ValidTel' => $data['ValidTel'],
                ':ValidMail' => $data['ValidMail'],
                ':ValidAddress' => $data['ValidAddress'],
                ':ResidentCard' => $data['ResidentCard'],
                ':Cinfo1' => $data['Cinfo1'],
                ':CinfoNote1' => $data['CinfoNote1'],
                ':CinfoStatus1' => $data['CinfoStatus1'],
                ':Cinfo2' => $data['Cinfo2'],
                ':CinfoNote2' => $data['CinfoNote2'],
                ':CinfoStatus2' => $data['CinfoStatus2'],
                ':Cinfo3' => $data['Cinfo3'],
                ':CinfoNote3' => $data['CinfoNote3'],
                ':CinfoStatus3' => $data['CinfoStatus3'],
                ':SearchNameKj' => $data['SearchNameKj'],
                ':SearchNameKn' => $data['SearchNameKn'],
                ':SearchPhone' => $data['SearchPhone'],
                ':SearchUnitingAddress' => $data['SearchUnitingAddress'],
                ':RegNameKj' => $data['RegNameKj'],
                ':RegUnitingAddress' => $data['RegUnitingAddress'],
                ':RegPhone' => $data['RegPhone'],
                ':Incre_ArTel' => $data['Incre_ArTel'],
                ':Incre_TelScore' => $data['Incre_TelScore'],
                ':Incre_TelNote' => $data['Incre_TelNote'],
                ':CorporateName' => $data['CorporateName'],
                ':DivisionName' => $data['DivisionName'],
                ':CpNameKj' => $data['CpNameKj'],
                ':EntCustSeq' => $data['EntCustSeq'],
                ':AddressKn' => $data['AddressKn'],
                ':RemindResult' => $data['RemindResult'],
                ':EntCustId' => $data['EntCustId'],
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
	 * @param int $customerId 更新するcustomerId
	 */
	public function saveUpdate($data, $customerId)
	{
        $sql = " SELECT * FROM T_Customer WHERE CustomerId = :CustomerId ";

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

        $sql  = " UPDATE T_Customer ";
        $sql .= " SET ";
        $sql .= "     OrderSeq = :OrderSeq ";
        $sql .= " ,   NameKj = :NameKj ";
        $sql .= " ,   NameKn = :NameKn ";
        $sql .= " ,   PostalCode = :PostalCode ";
        $sql .= " ,   PrefectureCode = :PrefectureCode ";
        $sql .= " ,   PrefectureName = :PrefectureName ";
        $sql .= " ,   City = :City ";
        $sql .= " ,   Town = :Town ";
        $sql .= " ,   Building = :Building ";
        $sql .= " ,   UnitingAddress = :UnitingAddress ";
        $sql .= " ,   Hash_Name = :Hash_Name ";
        $sql .= " ,   Hash_Address = :Hash_Address ";
        $sql .= " ,   Phone = :Phone ";
        $sql .= " ,   RealCallStatus = :RealCallStatus ";
        $sql .= " ,   RealCallResult = :RealCallResult ";
        $sql .= " ,   RealCallScore = :RealCallScore ";
        $sql .= " ,   MailAddress = :MailAddress ";
        $sql .= " ,   RealSendMailStatus = :RealSendMailStatus ";
        $sql .= " ,   RealSendMailResult = :RealSendMailResult ";
        $sql .= " ,   RealSendMailScore = :RealSendMailScore ";
        $sql .= " ,   Occupation = :Occupation ";
        $sql .= " ,   Incre_ArName = :Incre_ArName ";
        $sql .= " ,   Incre_NameScore = :Incre_NameScore ";
        $sql .= " ,   Incre_NameNote = :Incre_NameNote ";
        $sql .= " ,   Incre_ArAddr = :Incre_ArAddr ";
        $sql .= " ,   Incre_AddressScore = :Incre_AddressScore ";
        $sql .= " ,   Incre_AddressNote = :Incre_AddressNote ";
        $sql .= " ,   Incre_MailDomainScore = :Incre_MailDomainScore ";
        $sql .= " ,   Incre_MailDomainNote = :Incre_MailDomainNote ";
        $sql .= " ,   Incre_PostalCodeScore = :Incre_PostalCodeScore ";
        $sql .= " ,   Incre_PostalCodeNote = :Incre_PostalCodeNote ";
        $sql .= " ,   Incre_MoneyScore = :Incre_MoneyScore ";
        $sql .= " ,   Incre_MoneyNote = :Incre_MoneyNote ";
        $sql .= " ,   Incre_ScoreTotal = :Incre_ScoreTotal ";
        $sql .= " ,   eDen = :eDen ";
        $sql .= " ,   PhoneHistory = :PhoneHistory ";
        $sql .= " ,   Carrier = :Carrier ";
        $sql .= " ,   ValidTel = :ValidTel ";
        $sql .= " ,   ValidMail = :ValidMail ";
        $sql .= " ,   ValidAddress = :ValidAddress ";
        $sql .= " ,   ResidentCard = :ResidentCard ";
        $sql .= " ,   Cinfo1 = :Cinfo1 ";
        $sql .= " ,   CinfoNote1 = :CinfoNote1 ";
        $sql .= " ,   CinfoStatus1 = :CinfoStatus1 ";
        $sql .= " ,   Cinfo2 = :Cinfo2 ";
        $sql .= " ,   CinfoNote2 = :CinfoNote2 ";
        $sql .= " ,   CinfoStatus2 = :CinfoStatus2 ";
        $sql .= " ,   Cinfo3 = :Cinfo3 ";
        $sql .= " ,   CinfoNote3 = :CinfoNote3 ";
        $sql .= " ,   CinfoStatus3 = :CinfoStatus3 ";
        $sql .= " ,   SearchNameKj = :SearchNameKj ";
        $sql .= " ,   SearchNameKn = :SearchNameKn ";
        $sql .= " ,   SearchPhone = :SearchPhone ";
        $sql .= " ,   SearchUnitingAddress = :SearchUnitingAddress ";
        $sql .= " ,   RegNameKj = :RegNameKj ";
        $sql .= " ,   RegUnitingAddress = :RegUnitingAddress ";
        $sql .= " ,   RegPhone = :RegPhone ";
        $sql .= " ,   Incre_ArTel = :Incre_ArTel ";
        $sql .= " ,   Incre_TelScore = :Incre_TelScore ";
        $sql .= " ,   Incre_TelNote = :Incre_TelNote ";
        $sql .= " ,   CorporateName = :CorporateName ";
        $sql .= " ,   DivisionName = :DivisionName ";
        $sql .= " ,   CpNameKj = :CpNameKj ";
        $sql .= " ,   EntCustSeq = :EntCustSeq ";
        $sql .= " ,   AddressKn = :AddressKn ";
        $sql .= " ,   RemindResult = :RemindResult ";
        $sql .= " ,   EntCustId = :EntCustId ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE CustomerId = :CustomerId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':CustomerId' => $customerId,
                ':OrderSeq' => $row['OrderSeq'],
                ':NameKj' => $row['NameKj'],
                ':NameKn' => $row['NameKn'],
                ':PostalCode' => $row['PostalCode'],
                ':PrefectureCode' => $row['PrefectureCode'],
                ':PrefectureName' => $row['PrefectureName'],
                ':City' => $row['City'],
                ':Town' => $row['Town'],
                ':Building' => $row['Building'],
                ':UnitingAddress' => $row['UnitingAddress'],
                ':Hash_Name' => $row['Hash_Name'],
                ':Hash_Address' => $row['Hash_Address'],
                ':Phone' => $row['Phone'],
                ':RealCallStatus' => $row['RealCallStatus'],
                ':RealCallResult' => $row['RealCallResult'],
                ':RealCallScore' => $row['RealCallScore'],
                ':MailAddress' => $row['MailAddress'],
                ':RealSendMailStatus' => $row['RealSendMailStatus'],
                ':RealSendMailResult' => $row['RealSendMailResult'],
                ':RealSendMailScore' => $row['RealSendMailScore'],
                ':Occupation' => $row['Occupation'],
                ':Incre_ArName' => $row['Incre_ArName'],
                ':Incre_NameScore' => $row['Incre_NameScore'],
                ':Incre_NameNote' => $row['Incre_NameNote'],
                ':Incre_ArAddr' => $row['Incre_ArAddr'],
                ':Incre_AddressScore' => $row['Incre_AddressScore'],
                ':Incre_AddressNote' => $row['Incre_AddressNote'],
                ':Incre_MailDomainScore' => $row['Incre_MailDomainScore'],
                ':Incre_MailDomainNote' => $row['Incre_MailDomainNote'],
                ':Incre_PostalCodeScore' => $row['Incre_PostalCodeScore'],
                ':Incre_PostalCodeNote' => $row['Incre_PostalCodeNote'],
                ':Incre_MoneyScore' => $row['Incre_MoneyScore'],
                ':Incre_MoneyNote' => $row['Incre_MoneyNote'],
                ':Incre_ScoreTotal' => $row['Incre_ScoreTotal'],
                ':eDen' => $row['eDen'],
                ':PhoneHistory' => $row['PhoneHistory'],
                ':Carrier' => $row['Carrier'],
                ':ValidTel' => $row['ValidTel'],
                ':ValidMail' => $row['ValidMail'],
                ':ValidAddress' => $row['ValidAddress'],
                ':ResidentCard' => $row['ResidentCard'],
                ':Cinfo1' => $row['Cinfo1'],
                ':CinfoNote1' => $row['CinfoNote1'],
                ':CinfoStatus1' => $row['CinfoStatus1'],
                ':Cinfo2' => $row['Cinfo2'],
                ':CinfoNote2' => $row['CinfoNote2'],
                ':CinfoStatus2' => $row['CinfoStatus2'],
                ':Cinfo3' => $row['Cinfo3'],
                ':CinfoNote3' => $row['CinfoNote3'],
                ':CinfoStatus3' => $row['CinfoStatus3'],
                ':SearchNameKj' => $row['SearchNameKj'],
                ':SearchNameKn' => $row['SearchNameKn'],
                ':SearchPhone' => $row['SearchPhone'],
                ':SearchUnitingAddress' => $row['SearchUnitingAddress'],
                ':RegNameKj' => $row['RegNameKj'],
                ':RegUnitingAddress' => $row['RegUnitingAddress'],
                ':RegPhone' => $row['RegPhone'],
                ':Incre_ArTel' => $row['Incre_ArTel'],
                ':Incre_TelScore' => $row['Incre_TelScore'],
                ':Incre_TelNote' => $row['Incre_TelNote'],
                ':CorporateName' => $row['CorporateName'],
                ':DivisionName' => $row['DivisionName'],
                ':CpNameKj' => $row['CpNameKj'],
                ':EntCustSeq' => $row['EntCustSeq'],
                ':AddressKn' => $row['AddressKn'],
                ':RemindResult' => $row['RemindResult'],
                ':EntCustId' => $row['EntCustId'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
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
        $sql  = " SELECT * FROM T_Customer WHERE 1 = 1 ";
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
	 * 関連する注文データの注文シーケンスと初期データを指定して、
	 * 新しいCustomerの行データを作成する
	 *
	 * @param int $orderSeq 作成する顧客データに関連付けられた注文データの注文シーケンス
	 * @param array $data その他の初期データを示す連想配列
	 * @return プライマリキーのバリュー
	 */
	public function newRow($orderSeq, $data = array())
	{
		if( ((int)$orderSeq) < 1 ) {
			throw new \Exception( 'invalid order sequence' );
		}
		if( ! is_array( $data ) ) $data = array();

		// 必須パラメータと住所文字列のトリミングを行う
		$data = array_merge( $data, array(
			'OrderSeq' => $orderSeq,
			'RealCallStatus' => -2,
			'RealSendMailStatus' => -2,
			self::ADDR_PREF_NAME => trim( $data[ self::ADDR_PREF_NAME ] ),
			self::ADDR_CITY => trim( $data[ self::ADDR_CITY ] ),
			self::ADDR_TOWN => trim( $data[ self::ADDR_TOWN ] ),
			self::ADDR_BLD => trim( $data[ self::ADDR_BLD ] )
		) );
		// トリミング済みの住所情報から結合情報を作成する（ない場合のみ）
		// ※：isset()による項目設定の有無からnullチェックへ変更 (2008.02.20 eda)
		$addr = $data[ self::ADDR_ADDRESS ];
		if( strlen( trim("$addr") ) == 0 ) {
			$data = array_merge( $data, array(
				self::ADDR_ADDRESS => join( self::ADDR_UNIT_SEPARATOR, array(
					$data[ self::ADDR_PREF_NAME ],
					$data[ self::ADDR_CITY ],
					$data[ self::ADDR_TOWN ],
					$data[ self::ADDR_BLD ]
				) )
			) );
		}

		// Mod By Takemasa(NDC) 20150105 Stt 関数saveNewを呼出すよう変更
		//// 新しいZend_Db_Table_Rowを作成
		//$result = $this->createRow( $data );
		$result = $this->saveNew( $data );
		// Mod By Takemasa(NDC) 20150105 End 関数saveNewを呼出すよう変更

		return $result;
	}

	/**
	 * 正規化された電話番号と住所から対象者の過去注文を取得する
	 * @param regphone 正規化された電話番号
	 * @param regaddress 正規化された住所
	 * @return ResultInterface
	 */
	public function getPastOrderSeqs($regphone, $regaddress)
	{
        $sql = " SELECT OrderSeq FROM T_Customer WHERE RegUnitingAddress = :RegUnitingAddress OR RegPhone = :RegPhone ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':RegUnitingAddress' => $regaddress,
                ':RegPhone' => $regphone,
        );

        return $stm->execute($prm);
	}

	/**
	 * コンバート時のデータ取得に利用する。RegNameKjがNULLの一覧を返す
	 * @return array
	 */
	public function getRegNameKjIsNull()
	{
		throw new \Exception('this method call is invalid');
	}
}
