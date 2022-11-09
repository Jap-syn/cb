<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * M_PostalCodeテーブルへのアダプタ
 */
class TablePostalCode
{
	protected $_name = 'M_PostalCode';
	protected $_primary = array('Seq');
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
	 * 指定7桁郵便番号に一致する住所データの取得
	 *
	 * @param string $postalCode7 7桁郵便番号
	 * @return array 住所データ配列
	 */
	public function findPostalCode7($postalCode7)
	{
        $pc7 = mb_convert_kana($postalCode7, "n", "UTF-8");
        $pc7 = mb_ereg_replace("[^0-9]", "", $pc7);

        $sql = " SELECT * FROM M_PostalCode WHERE PostalCode7 = :PostalCode7 ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':PostalCode7' => $pc7,
        );

        $ri = $stm->execute($prm);

        $i = 0;
        foreach($ri as $data)
        {
            $pcd = new PostalCodeData();
            $pcd->PostalCode7 = $postalCode7;
            $pcd->PrefectureKana = $data['PrefectureKana'];
            $pcd->CityKana = $data['CityKana'];
            $pcd->TownKana = $data['TownKana'];
            $pcd->PrefectureKanji = $data['PrefectureKanji'];
            $pcd->CityKanji = $data['CityKanji'];
            $pcd->TownKanji = $data['TownKanji'];

            $result[$i] = $pcd;
            $i++;
        }

        return $result;
	}

	/**
	 * 新しいレコードをインサートする。
	 *
	 * @param array $data インサートする連想配列
	 * @return プライマリキーのバリュー
	 */
	public function saveNew($data)
	{
        $sql  = " INSERT INTO M_PostalCode (LocalGroupCode, PostalCode5, PostalCode7, PrefectureKana, CityKana, TownKana, PrefectureKanji, CityKanji, TownKanji, OneTownPluralNumberFlg, NumberingEachKoazaFlg, TownIncludeChoumeFlg, OneNumberPluralTownFlg, UpdateFlg, ModifiedReasonCode) VALUES (";
        $sql .= "   :LocalGroupCode ";
        $sql .= " , :PostalCode5 ";
        $sql .= " , :PostalCode7 ";
        $sql .= " , :PrefectureKana ";
        $sql .= " , :CityKana ";
        $sql .= " , :TownKana ";
        $sql .= " , :PrefectureKanji ";
        $sql .= " , :CityKanji ";
        $sql .= " , :TownKanji ";
        $sql .= " , :OneTownPluralNumberFlg ";
        $sql .= " , :NumberingEachKoazaFlg ";
        $sql .= " , :TownIncludeChoumeFlg ";
        $sql .= " , :OneNumberPluralTownFlg ";
        $sql .= " , :UpdateFlg ";
        $sql .= " , :ModifiedReasonCode ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':LocalGroupCode' => $data['LocalGroupCode'],
                ':PostalCode5' => $data['PostalCode5'],
                ':PostalCode7' => $data['PostalCode7'],
                ':PrefectureKana' => $data['PrefectureKana'],
                ':CityKana' => $data['CityKana'],
                ':TownKana' => $data['TownKana'],
                ':PrefectureKanji' => $data['PrefectureKanji'],
                ':CityKanji' => $data['CityKanji'],
                ':TownKanji' => $data['TownKanji'],
                ':OneTownPluralNumberFlg' => $data['OneTownPluralNumberFlg'],
                ':NumberingEachKoazaFlg' => $data['NumberingEachKoazaFlg'],
                ':TownIncludeChoumeFlg' => $data['TownIncludeChoumeFlg'],
                ':OneNumberPluralTownFlg' => $data['OneNumberPluralTownFlg'],
                ':UpdateFlg' => $data['UpdateFlg'],
                ':ModifiedReasonCode' => $data['ModifiedReasonCode'],
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
	}

	/**
	 * 指定されたレコードを更新する。
	 *
	 * @param array $data 更新内容
	 * @param unknown_type $seq 更新するSeq
	 */
	public function saveUpdate($data, $seq)
	{
        $sql = " SELECT * FROM M_PostalCode WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
        );

        $row = $stm->execute($prm)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE M_PostalCode ";
        $sql .= " SET ";
        $sql .= "     LocalGroupCode = :LocalGroupCode ";
        $sql .= " ,   PostalCode5 = :PostalCode5 ";
        $sql .= " ,   PostalCode7 = :PostalCode7 ";
        $sql .= " ,   PrefectureKana = :PrefectureKana ";
        $sql .= " ,   CityKana = :CityKana ";
        $sql .= " ,   TownKana = :TownKana ";
        $sql .= " ,   PrefectureKanji = :PrefectureKanji ";
        $sql .= " ,   CityKanji = :CityKanji ";
        $sql .= " ,   TownKanji = :TownKanji ";
        $sql .= " ,   OneTownPluralNumberFlg = :OneTownPluralNumberFlg ";
        $sql .= " ,   NumberingEachKoazaFlg = :NumberingEachKoazaFlg ";
        $sql .= " ,   TownIncludeChoumeFlg = :TownIncludeChoumeFlg ";
        $sql .= " ,   OneNumberPluralTownFlg = :OneNumberPluralTownFlg ";
        $sql .= " ,   UpdateFlg = :UpdateFlg ";
        $sql .= " ,   ModifiedReasonCode = :ModifiedReasonCode ";
        $sql .= " WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
                ':LocalGroupCode' => $row['LocalGroupCode'],
                ':PostalCode5' => $row['PostalCode5'],
                ':PostalCode7' => $row['PostalCode7'],
                ':PrefectureKana' => $row['PrefectureKana'],
                ':CityKana' => $row['CityKana'],
                ':TownKana' => $row['TownKana'],
                ':PrefectureKanji' => $row['PrefectureKanji'],
                ':CityKanji' => $row['CityKanji'],
                ':TownKanji' => $row['TownKanji'],
                ':OneTownPluralNumberFlg' => $row['OneTownPluralNumberFlg'],
                ':NumberingEachKoazaFlg' => $row['NumberingEachKoazaFlg'],
                ':TownIncludeChoumeFlg' => $row['TownIncludeChoumeFlg'],
                ':OneNumberPluralTownFlg' => $row['OneNumberPluralTownFlg'],
                ':UpdateFlg' => $row['UpdateFlg'],
                ':ModifiedReasonCode' => $row['ModifiedReasonCode'],
        );

        return $stm->execute($prm);
	}

	/**
	 * M_PostalCodeDのすべてのレコードを削除する。
	 *
	 */
	public function deleteByPostalCode()
	{
	    $sql = " TRUNCATE TABLE M_PostalCode ";

	    $stm = $this->_adapter->query($sql);

	    return $stm->execute();
	}

	/**
	 * 7桁郵便番号から連結全角カナ住所文字列を引く
	 *
	 * @param string $postalCode7 7桁郵便番号
	 * @param string 連結住所文字列
	 */
	public function getAddressKanaStr($postalCode7)
	{
        // ハイフンが含まれる場合は除外する
        $postalCode7 = str_replace('-', '', $postalCode7);

        $sql  = " SELECT  concat(PrefectureKana, CityKana, TownKana) AS kanaStr ";
        $sql .= " FROM    M_PostalCode ";
        $sql .= " WHERE   PostalCode7 = :PostalCode7 ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':PostalCode7' => $postalCode7,
        );

        $row = $stm->execute($prm)->current();

        // 全角変換し戻す
        return mb_convert_kana($row['kanaStr'], 'K');
	}

	/**
	 * 正しい郵便番号と住所文字列の組み合わせか？
	 *
	 * @param string $postalCode7 7桁郵便番号
	 * @param string $address 連結住所文字列
	 * @return boolean true:一致／false:不一致
	 */
    public function isValidPostAddressKanji($postalCode7, $address)
    {
        // ハイフンが含まれる場合は除外する
        $postalCode7 = str_replace('-', '', $postalCode7);

        $sql  = " SELECT  concat(PrefectureKanji, CityKanji, TownKanji) AS KanjiStr ";
        $sql .= " FROM    M_PostalCode ";
        $sql .= " WHERE   PostalCode7 = :PostalCode7 ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':PostalCode7' => $postalCode7,
        );

        $kanjiStr = $stm->execute($prm)->current()['KanjiStr'];

        return (strpos($address, $kanjiStr) === false) ? false : true;
    }

    /**
     * 完全一致する郵便番号と住所文字列の組み合わせか？
     *
     * @param string $postalCode7 7桁郵便番号
     * @param string $address 連結住所文字列
     * @return boolean true:完全一致／false:不一致
     */
    public function isPerfectMatchPostAddressKanji($postalCode7, $address)
    {
        // ハイフンが含まれる場合は除外する
        $postalCode7 = str_replace('-', '', $postalCode7);

        $sql  = " SELECT  concat(PrefectureKanji, CityKanji, TownKanji) AS KanjiStr ";
        $sql .= " FROM    M_PostalCode ";
        $sql .= " WHERE   PostalCode7 = :PostalCode7 ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':PostalCode7' => $postalCode7,
        );

        $kanjiStr = $stm->execute($prm)->current()['KanjiStr'];

        return ($address == $kanjiStr) ? true : false;
    }
}

/**
 * 郵便番号データ
 */
class PostalCodeData
{
	/**
	 * 郵便番号7桁
	 */
	public $PostalCode7 = "";
	/**
	 * 都道府県カナ
	 */
	public $PrefectureKana = "";
	/**
	 * 市区町村カナ
	 */
	public $CityKana = "";
	/**
	 * 町域カナ
	 */
	public $TownKana = "";
	/**
	 * 都道府県漢字
	 */
	public $PrefectureKanji = "";
	/**
	 * 市区町村漢字
	 */
	public $CityKanji = "";
	/**
	 * 町域漢字
	 */
	public $TownKanji = "";

	/**
	 * カナ住所
	 */
	public function getKanaAddress()
	{
		return $this->PrefectureKana . $this->CityKana . $this->TownKana;
	}

	/**
	 * 漢字住所
	 */
	public function getKanjiAddress()
	{
		return $this->PrefectureKanji . $this->CityKanji . $this->TownKanji;
	}
}
