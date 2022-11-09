<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_GeneralPurposeテーブルへのアダプタ
 */
class TableGeneralPurpose
{
	protected $_name = 'M_GeneralPurpose';
	protected $_primary = array ('Class', 'Code');
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

// 以下の関数は廃止(TableCodeへ移設済み20150317)
// 	/**
// 	 * すべてのマスターデータを取得する
// 	 *
// 	 * @return ResultInterface
// 	 */
// 	public function getAll()
// 	{
// 	    $sql = " SELECT * FROM M_GeneralPurpose ORDER BY Class, Code ";
//         return $this->_adapter->query($sql)->execute(null);
// 	}

// 以下の関数は廃止(TableCodeへ移設済み20150317)
// 	/**
// 	 * 指定マスタークラスIDのマスターデータを取得する。
// 	 *
// 	 * @param string $class マスタークラス
// 	 * @return ResultInterface
// 	 */
// 	public function getMasterByClass($class)
// 	{
//         $sql = " SELECT * FROM M_GeneralPurpose WHERE ValidFlg = 1 AND Class = :Class ORDER BY Code ";
//
//         $stm = $this->_adapter->query($sql);
//
//         $prm = array(
//                 ':Class' => $class,
//         );
//
//         return $stm->execute($prm);
// 	}

// 以下の関数は廃止(TableCodeへ移設済み20150317)
// 	/**
// 	 * 指定マスタークラスIDのマスターデータを取得する。(無効レコード含む）
// 	 *
// 	 * @param string $class マスタークラス
// 	 * @return ResultInterface
// 	 */
// 	public function getMasterByClassAll($class)
// 	{
//         $sql = " SELECT * FROM M_GeneralPurpose WHERE Class = :Class ORDER BY Code ";
//
//         $stm = $this->_adapter->query($sql);
//
//         $prm = array(
//                 ':Class' => $class,
//         );
//
//         return $stm->execute($prm);
// 	}

// 以下の関数は廃止(getMasterByClassに等価なのでそちらを呼ぶ20150317)
// 	/**
// 	 * 指定マスタークラスIDのマスターデータを取得する。
// 	 *
// 	 * @param string $enterpriseId 事業者ID
// 	 * @return ResultInterface
// 	 */
// 	public function getMasterByClass2($class)
// 	{
// 	    return $this->getMasterByClass($class);
// 	}

// 以下の関数は廃止(TableCodeへ移設済み20150317)
// 	/**
// 	 * 指定クラス・コードのキャプションを取得する。
// 	 *
// 	 * @param int $class クラス
// 	 * @param int $code コード
// 	 * @return string キャプション
// 	 */
// 	public function getMasterCaption($class, $code)
// 	{
//         $sql = " SELECT Caption FROM M_GeneralPurpose WHERE Class = :Class AND Code = :Code ";
//
//         $stm = $this->_adapter->query($sql);
//
//         $prm = array(
//                 ':Class' => $class,
//                 ':Code' => $code,
//         );
//
//         return $stm->execute($prm)->current()['Caption'];
// 	}

// 以下の関数は廃止(TableCodeへ移設済み20150317)
// 	/**
// 	 * 指定クラス・コードのショートキャプションを取得する。
// 	 *
// 	 * @param int $class クラス
// 	 * @param int $code コード
// 	 * @return string ショートキャプション
// 	 */
// 	public function getMasterShortCaption($class, $code)
// 	{
//         $sql = " SELECT ShortCaption FROM M_GeneralPurpose WHERE Class = :Class AND Code = :Code ";
//
//         $stm = $this->_adapter->query($sql);
//
//         $prm = array(
//                 ':Class' => $class,
//                 ':Code' => $code,
//         );
//
//         return $stm->execute($prm)->current()['ShortCaption'];
// 	}

// 以下の関数は廃止(TableCodeへ移設済み20150317)
// 	/**
// 	 * 指定クラス・コードの説明を取得する。
// 	 *
// 	 * @param int $class クラス
// 	 * @param int $code コード
// 	 * @return string 説明
// 	 */
// 	public function getMasterDescription($class, $code)
// 	{
//         $sql = " SELECT Description FROM M_GeneralPurpose WHERE Class = :Class AND Code = :Code ";
//
//         $stm = $this->_adapter->query($sql);
//
//         $prm = array(
//                 ':Class' => $class,
//                 ':Code' => $code,
//         );
//
//         return $stm->execute($prm)->current()['Description'];
// 	}

// 以下の関数は廃止(TableCodeへ移設済み20150317)
// 	/**
// 	 * 指定クラス・コードの補助コードを取得する。
// 	 *
// 	 * @param int $class クラス
// 	 * @param int $code コード
// 	 * @return int 補助コード
// 	 */
// 	public function getMasterAssCode($class, $code)
// 	{
//         $sql = " SELECT AssCode FROM M_GeneralPurpose WHERE Class = :Class AND Code = :Code ";
//
//         $stm = $this->_adapter->query($sql);
//
//         $prm = array(
//                 ':Class' => $class,
//                 ':Code' => $code,
//         );
//
//         return $stm->execute($prm)->current()['AssCode'];
// 	}

// 以下の関数は廃止(TableCodeへ移設済み20150317)
// 	/**
// 	 * 新しいレコードをインサートする。
// 	 *
// 	 * @param array $data インサートする連想配列
// 	 * @return プライマリキーのバリュー？
// 	 */
// 	public function saveNew($data)
// 	{
//         $sql  = " INSERT INTO M_GeneralPurpose (Class, Code, Caption, ShortCaption, Description, AssCode, ValidFlg) VALUES (";
//         $sql .= "   :Class ";
//         $sql .= " , :Code ";
//         $sql .= " , :Caption ";
//         $sql .= " , :ShortCaption ";
//         $sql .= " , :Description ";
//         $sql .= " , :AssCode ";
//         $sql .= " , :ValidFlg ";
//         $sql .= " )";
//
//         $stm = $this->_adapter->query($sql);
//
//         $prm = array(
//                 ':Class' => $data['Class'],
//                 ':Code' => $data['Code'],
//                 ':Caption' => $data['Caption'],
//                 ':ShortCaption' => $data['ShortCaption'],
//                 ':Description' => $data['Description'],
//                 ':AssCode' => $data['AssCode'],
//                 ':ValidFlg' => $data['ValidFlg'],
//         );
//
//         $ri = $stm->execute($prm);
//
//         return $ri->getGeneratedValue();// 新規登録したPK値を戻す
// 	}

// 以下の関数は廃止(TableCodeへ移設済み20150317)
// 	/**
// 	 * 指定されたレコードを更新する。
// 	 *
// 	 * @param array $data 更新内容
// 	 * @param int $class 更新するClassキー
// 	 * @param int $code 更新するCodeキー
// 	 */
// 	public function saveUpdate($data, $class, $code)
// 	{
//         $sql = " SELECT * FROM M_GeneralPurpose WHERE Class = :Class AND Code = :Code ";
//
//         $stm = $this->_adapter->query($sql);
//
//         $prm = array(
//                 ':Class' => $class,
//                 ':Code' => $code,
//         );
//
//         $row = $stm->execute($prm)->current();
//
//         foreach ($data as $key => $value)
//         {
//             if (array_key_exists($key, $row))
//             {
//                 $row[$key] = $value;
//             }
//         }
//
//         $sql  = " UPDATE M_GeneralPurpose ";
//         $sql .= " SET ";
//         $sql .= "     Caption = :Caption ";
//         $sql .= " ,   ShortCaption = :ShortCaption ";
//         $sql .= " ,   Description = :Description ";
//         $sql .= " ,   AssCode = :AssCode ";
//         $sql .= " ,   ValidFlg = :ValidFlg ";
//         $sql .= " WHERE Class = :Class ";
//         $sql .= " AND   Code = :Code ";
//
//         $stm = $this->_adapter->query($sql);
//
//         $prm = array(
//                 ':Class' => $class,
//                 ':Code' => $code,
//                 ':Caption' => $row['Caption'],
//                 ':ShortCaption' => $row['ShortCaption'],
//                 ':Description' => $row['Description'],
//                 ':AssCode' => $row['AssCode'],
//                 ':ValidFlg' => $row['ValidFlg'],
//         );
//
//         return $stm->execute($prm);
// 	}
}
