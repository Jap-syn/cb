<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_DeliveryDestinationのアダプタクラス
 */
class TableDeliveryDestination
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
	const ADDR_BDL = 'Building';

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
	protected $_name = 'T_DeliveryDestination';

	/**
	 * プライマリキー
	 *
	 * @var string
	 */
	protected $_primary = array('DeliDestId');

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
	 * 初期データの連想配列を指定して、レコード挿入する
	 *
	 * @param array $data 作成する行の初期データとなる連想配列
	 * @return プライマリキーのバリュー
	 */
	public function newRow($data = array()) {
	    if( ! is_array($data) ) $data = array();

		// 住所文字列のトリミングを行う
		$data = array_merge( $data, array(
			self::ADDR_PREF_NAME => trim( $data[ self::ADDR_PREF_NAME ] ),
			self::ADDR_CITY => trim( $data[ self::ADDR_CITY ] ),
			self::ADDR_TOWN => trim( $data[ self::ADDR_TOWN ] ),
			self::ADDR_BDL => trim( $data[ self::ADDR_BDL ] )
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
					$data[ self::ADDR_BDL ]
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
	 * 指定されたレコードを更新する。
	 *
	 * @param array $data 更新内容
	 * @param int $deliDestId 更新するDeliDestId
	 */
	public function saveUpdate($data, $deliDestId)
	{
        $sql = " SELECT * FROM T_DeliveryDestination WHERE DeliDestId = :DeliDestId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':DeliDestId' => $deliDestId,
        );

        $row = $stm->execute($prm)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_DeliveryDestination ";
        $sql .= " SET ";
        $sql .= "     DestNameKj = :DestNameKj ";
        $sql .= " ,   DestNameKn = :DestNameKn ";
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
        $sql .= " ,   Incre_ArName = :Incre_ArName ";
        $sql .= " ,   Incre_NameScore = :Incre_NameScore ";
        $sql .= " ,   Incre_NameNote = :Incre_NameNote ";
        $sql .= " ,   Incre_ArAddr = :Incre_ArAddr ";
        $sql .= " ,   Incre_AddressScore = :Incre_AddressScore ";
        $sql .= " ,   Incre_AddressNote = :Incre_AddressNote ";
        $sql .= " ,   Incre_SameCnAndAddrScore = :Incre_SameCnAndAddrScore ";
        $sql .= " ,   Incre_SameCnAndAddrNote = :Incre_SameCnAndAddrNote ";
        $sql .= " ,   Incre_PostalCodeScore = :Incre_PostalCodeScore ";
        $sql .= " ,   Incre_PostalCodeNote = :Incre_PostalCodeNote ";
        $sql .= " ,   Incre_ScoreTotal = :Incre_ScoreTotal ";
        $sql .= " ,   SearchDestNameKj = :SearchDestNameKj ";
        $sql .= " ,   SearchDestNameKn = :SearchDestNameKn ";
        $sql .= " ,   SearchPhone = :SearchPhone ";
        $sql .= " ,   SearchUnitingAddress = :SearchUnitingAddress ";
        $sql .= " ,   Incre_ArTel = :Incre_ArTel ";
        $sql .= " ,   Incre_TelScore = :Incre_TelScore ";
        $sql .= " ,   Incre_TelNote = :Incre_TelNote ";
        $sql .= " ,   RegDestNameKj = :RegDestNameKj ";
        $sql .= " ,   RegUnitingAddress = :RegUnitingAddress ";
        $sql .= " ,   RegPhone = :RegPhone ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE DeliDestId = :DeliDestId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':DeliDestId' => $deliDestId,
                ':DestNameKj' => $row['DestNameKj'],
                ':DestNameKn' => $row['DestNameKn'],
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
                ':Incre_ArName' => $row['Incre_ArName'],
                ':Incre_NameScore' => $row['Incre_NameScore'],
                ':Incre_NameNote' => $row['Incre_NameNote'],
                ':Incre_ArAddr' => $row['Incre_ArAddr'],
                ':Incre_AddressScore' => $row['Incre_AddressScore'],
                ':Incre_AddressNote' => $row['Incre_AddressNote'],
                ':Incre_SameCnAndAddrScore' => $row['Incre_SameCnAndAddrScore'],
                ':Incre_SameCnAndAddrNote' => $row['Incre_SameCnAndAddrNote'],
                ':Incre_PostalCodeScore' => $row['Incre_PostalCodeScore'],
                ':Incre_PostalCodeNote' => $row['Incre_PostalCodeNote'],
                ':Incre_ScoreTotal' => $row['Incre_ScoreTotal'],
                ':SearchDestNameKj' => $row['SearchDestNameKj'],
                ':SearchDestNameKn' => $row['SearchDestNameKn'],
                ':SearchPhone' => $row['SearchPhone'],
                ':SearchUnitingAddress' => $row['SearchUnitingAddress'],
                ':Incre_ArTel' => $row['Incre_ArTel'],
                ':Incre_TelScore' => $row['Incre_TelScore'],
                ':Incre_TelNote' => $row['Incre_TelNote'],
                ':RegDestNameKj' => $row['RegDestNameKj'],
                ':RegUnitingAddress' => $row['RegUnitingAddress'],
                ':RegPhone' => $row['RegPhone'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
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
        $sql  = " INSERT INTO T_DeliveryDestination (DestNameKj, DestNameKn, PostalCode, PrefectureCode, PrefectureName, City, Town, Building, UnitingAddress, Hash_Name, Hash_Address, Phone, Incre_ArName, Incre_NameScore, Incre_NameNote, Incre_ArAddr, Incre_AddressScore, Incre_AddressNote, Incre_SameCnAndAddrScore, Incre_SameCnAndAddrNote, Incre_PostalCodeScore, Incre_PostalCodeNote, Incre_ScoreTotal, SearchDestNameKj, SearchDestNameKn, SearchPhone, SearchUnitingAddress, Incre_ArTel, Incre_TelScore, Incre_TelNote, RegDestNameKj, RegUnitingAddress, RegPhone, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :DestNameKj ";
        $sql .= " , :DestNameKn ";
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
        $sql .= " , :Incre_ArName ";
        $sql .= " , :Incre_NameScore ";
        $sql .= " , :Incre_NameNote ";
        $sql .= " , :Incre_ArAddr ";
        $sql .= " , :Incre_AddressScore ";
        $sql .= " , :Incre_AddressNote ";
        $sql .= " , :Incre_SameCnAndAddrScore ";
        $sql .= " , :Incre_SameCnAndAddrNote ";
        $sql .= " , :Incre_PostalCodeScore ";
        $sql .= " , :Incre_PostalCodeNote ";
        $sql .= " , :Incre_ScoreTotal ";
        $sql .= " , :SearchDestNameKj ";
        $sql .= " , :SearchDestNameKn ";
        $sql .= " , :SearchPhone ";
        $sql .= " , :SearchUnitingAddress ";
        $sql .= " , :Incre_ArTel ";
        $sql .= " , :Incre_TelScore ";
        $sql .= " , :Incre_TelNote ";
        $sql .= " , :RegDestNameKj ";
        $sql .= " , :RegUnitingAddress ";
        $sql .= " , :RegPhone ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':DestNameKj' => $data['DestNameKj'],
                ':DestNameKn' => $data['DestNameKn'],
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
                ':Incre_ArName' => $data['Incre_ArName'],
                ':Incre_NameScore' => $data['Incre_NameScore'],
                ':Incre_NameNote' => $data['Incre_NameNote'],
                ':Incre_ArAddr' => $data['Incre_ArAddr'],
                ':Incre_AddressScore' => $data['Incre_AddressScore'],
                ':Incre_AddressNote' => $data['Incre_AddressNote'],
                ':Incre_SameCnAndAddrScore' => $data['Incre_SameCnAndAddrScore'],
                ':Incre_SameCnAndAddrNote' => $data['Incre_SameCnAndAddrNote'],
                ':Incre_PostalCodeScore' => $data['Incre_PostalCodeScore'],
                ':Incre_PostalCodeNote' => $data['Incre_PostalCodeNote'],
                ':Incre_ScoreTotal' => $data['Incre_ScoreTotal'],
                ':SearchDestNameKj' => $data['SearchDestNameKj'],
                ':SearchDestNameKn' => $data['SearchDestNameKn'],
                ':SearchPhone' => $data['SearchPhone'],
                ':SearchUnitingAddress' => $data['SearchUnitingAddress'],
                ':Incre_ArTel' => $data['Incre_ArTel'],
                ':Incre_TelScore' => $data['Incre_TelScore'],
                ':Incre_TelNote' => $data['Incre_TelNote'],
                ':RegDestNameKj' => $data['RegDestNameKj'],
                ':RegUnitingAddress' => $data['RegUnitingAddress'],
                ':RegPhone' => $data['RegPhone'],
                ':RegistDate' => date('Y-m-d H:i:s'),
                ':RegistId' => $data['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $data['UpdateId'],
                ':ValidFlg' => isset($data['ValidFlg']) ? $data['ValidFlg'] : 1,
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
	}
}
