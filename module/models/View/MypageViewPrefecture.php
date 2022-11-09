<?php
namespace models\View;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

class MypageViewPrefecture
{
	protected $_name = 'MV_Prefecture';
	protected $_primary = 'PrefectureCode';
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
	 * 都道府県データを取得する
	 *
	 * @param int $prefectureCode
	 * @return ResultInterface
	 */
	public function find($prefectureCode)
	{
	    $sql  = " SELECT * FROM MV_Prefecture WHERE PrefectureCode = :PrefectureCode ";

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':PrefectureCode' => $prefectureCode,
	    );

	    return $stm->execute($prm);
	}

	/**
	 * すべての都道府県データを取得する
	 *
	 * @return ResultInterface
	 */
	public function getAll()
	{
	    $sql = " SELECT * FROM MV_Prefecture WHERE ValidFlg = 1 ORDER BY PrefectureCode ASC ";
	    return $this->_adapter->query($sql)->execute(null);
	}

	/**
	 * 指定都道府県コードの都道府県名を取得する。
	 *
	 * @param int $prefectureCode 都道府県コード
	 * @return string 都道府県名
	 */
	public function getPrefectureName($prefectureCode)
	{
	    $sql = " SELECT PrefectureName FROM MV_Prefecture WHERE ValidFlg = 1 AND PrefectureCode = :PrefectureCode ";

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':PrefectureCode' => $prefectureCode,
	    );

	    return $stm->execute($prm)->current()['PrefectureName'];
	}

}
