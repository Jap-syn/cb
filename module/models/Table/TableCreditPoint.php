<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * M_CreditPointテーブルへのアダプタ
 */
class TableCreditPoint
{
	protected $_name = 'M_CreditPoint';
	protected $_primary = array('CreditCriterionId', 'CpId');
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
	 * すべての与信ポイントデータを取得する
	 *
	 * @param bool $asc 昇順ソートの場合はtrueを指定。デフォルトはfalse（＝降順ソート）
	 * @return ResultInterface
	 */
	public function getAll($asc = false)
	{
	    $sql = " SELECT * FROM M_CreditPoint ORDER BY CpId " . ($asc ? "asc" : "desc");
        return $this->_adapter->query($sql)->execute(null);
	}

	/**
	 * 指定された依存種別のすべての与信ポイントデータを取得する
	 *
	 * @param int $dependence 依存種別
	 * @param bool $asc 昇順ソートの場合はtrueを指定。デフォルトはfalse（＝降順ソート）
	 * @return ResultInterface
	 */
	public function getAllByDependence($dependence, $asc = false)
	{
        $sql = " SELECT * FROM M_CreditPoint WHERE Dependence = :Dependence ORDER BY CpId " . ($asc ? "asc" : "desc");

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Dependence' => $dependence,
        );

        return $stm->execute($prm);
	}

	/**
	 * 指定された与信判定基準＋依存種別のすべての与信ポイントデータを取得する
	 *
	 * @param int $creditcriterionid 与信判定基準ID
	 * @param int $dependence 依存種別
	 * @param bool $asc 昇順ソートの場合はtrueを指定。デフォルトはfalse（＝降順ソート）
	 * @return ResultInterface
	 */
	public function getAllByCriterionidDependence($creditcriterionid, $dependence, $asc = false)
	{
	    $sql  = " SELECT * FROM M_CreditPoint ";
	    $sql .= " WHERE CreditCriterionId = :CreditCriterionId AND Dependence = :Dependence AND ValidFlg = 1 ";
	    $sql .= " ORDER BY CpId " . ($asc ? "asc" : "desc");

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':CreditCriterionId' => $creditcriterionid
	          , ':Dependence' => $dependence,
	    );

	    return $stm->execute($prm);
	}

	/**
	 * 電話コール結果ポイントのデータを取得する
	 *
	 * @return array
	 */
	public function getRealCallPoints()
	{
	    $sql = " SELECT MCD.KeyCode, MCP.Point FROM M_Code MCD, M_CreditPoint MCP WHERE MCD.Class1 = MCP.CpId AND MCD.CodeId = 7 ";
        $ri = $this->_adapter->query($sql)->execute(null);

        $result[0] = 0;
        foreach ($ri as $row) {
            $result[$row['KeyCode']] = $row['Point'];
        }

        return $result;
	}

	/**
	 * リアル送信結果ポイントのデータを取得する
	 *
	 * @return array
	 */
	public function getRealSendMailPoints()
	{
	    $sql = " SELECT MCD.KeyCode, MCP.Point FROM M_Code MCD, M_CreditPoint MCP WHERE MCD.Class1 = MCP.CpId AND MCD.CodeId = 8 ";
	    $ri = $this->_adapter->query($sql)->execute(null);

        $result[0] = 0;
        foreach ($ri as $row) {
            $result[$row['KeyCode']] = $row['Point'];
        }

        return $result;
	}

	/**
	 * 指定与信ポイントIDの与信ポイントデータを取得する。
	 *
	 * @param int $creditCriterionId 与信判定基準ID※コードマスターにて管理
	 * @param int $cpid 与信ポイントID
	 * @return ResultInterface
	 */
	public function findCreditPoint($creditCriterionId, $cpid)
	{
        $sql = " SELECT * FROM M_CreditPoint WHERE CreditCriterionId = :CreditCriterionId AND CpId = :CpId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':CreditCriterionId' => $creditCriterionId,
                ':CpId' => $cpid,
        );

        return $stm->execute($prm);
	}

	/**
	 * 指定与信ポイントIDの与信ポイントデータを取得する。
	 *
	 * @param int $creditCriterionId 与信判定基準ID※コードマスターにて管理
	 * @param int $cpid 与信ポイントID
	 * @return ResultInterface
	 */
	public function findCreditPoint2($creditCriterionId, $cpid)
	{
        return $this->findCreditPoint($creditCriterionId, $cpid);
	}

	/**
	 * 新しいレコードをインサートする。
	 *
	 * @param array $data インサートする連想配列
	 * @return プライマリキーのバリュー
	 */
	public function saveNew($data)
	{
        $sql  = " INSERT INTO M_CreditPoint (CreditCriterionId, CpId, Caption, Point, Message, Description, Dependence, GeneralProp, SetCategory, CreditCriterionName, Rate, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :CreditCriterionId ";
        $sql .= " , :CpId ";
        $sql .= " , :Caption ";
        $sql .= " , :Point ";
        $sql .= " , :Message ";
        $sql .= " , :Description ";
        $sql .= " , :Dependence ";
        $sql .= " , :GeneralProp ";
        $sql .= " , :SetCategory ";
        $sql .= " , :CreditCriterionName ";
        $sql .= " , :Rate ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':CreditCriterionId' => $data['CreditCriterionId'],
                ':CpId' => isset($data['CpId']) ? $data['CpId'] : 0,
                ':Caption' => $data['Caption'],
                ':Point' => $data['Point'],
                ':Message' => $data['Message'],
                ':Description' => $data['Description'],
                ':Dependence' => $data['Dependence'],
                ':GeneralProp' => $data['GeneralProp'],
                ':SetCategory' => $data['SetCategory'],
                ':CreditCriterionName' => $data['CreditCriterionName'],
                ':Rate' => isset($data['Rate']) ? $data['Rate'] : 1,
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
	 * @param int $creditCriterionId 与信判定基準ID※コードマスターにて管理
	 * @param int $cpid 与信ポイントID
	 */
	public function saveUpdate($data, $creditCriterionId, $cpid)
	{
        $row = $this->findCreditPoint($creditCriterionId, $cpid)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE M_CreditPoint ";
        $sql .= " SET ";
        $sql .= "     Caption = :Caption ";
        $sql .= " ,   Point = :Point ";
        $sql .= " ,   Message = :Message ";
        $sql .= " ,   Description = :Description ";
        $sql .= " ,   Dependence = :Dependence ";
        $sql .= " ,   GeneralProp = :GeneralProp ";
        $sql .= " ,   SetCategory = :SetCategory ";
        $sql .= " ,   CreditCriterionName = :CreditCriterionName ";
        $sql .= " ,   Rate = :Rate ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE CreditCriterionId = :CreditCriterionId ";
        $sql .= " AND   CpId = :CpId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':CreditCriterionId' => $creditCriterionId,
                ':CpId' => $cpid,
                ':Caption' => $row['Caption'],
                ':Point' => $row['Point'],
                ':Message' => $row['Message'],
                ':Description' => $row['Description'],
                ':Dependence' => $row['Dependence'],
                ':GeneralProp' => $row['GeneralProp'],
                ':SetCategory' => $row['SetCategory'],
                ':CreditCriterionName' => $row['CreditCriterionName'],
                ':Rate' => $row['Rate'],
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
        $sql  = " SELECT * FROM M_CreditPoint WHERE 1 = 1 ";
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
            $this->saveUpdate($row, $row['CreditCriterionId'], $row['CpId']);
        }
	}

	/**
	 * 指定カテゴリの情報を取得する。
	 *
	 * @param string $cpid カテゴリ
	 * @return ResultInterface
	 */
	public function findCreditPointByCategory($category)
	{
        $sql = " SELECT * FROM M_CreditPoint WHERE SetCategory = :SetCategory ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':SetCategory' => $category,
        );

        return $stm->execute($prm);
	}
}
