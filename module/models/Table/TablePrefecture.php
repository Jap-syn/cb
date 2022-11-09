<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * M_Prefectureテーブルへのアダプタ
 */
class TablePrefecture
{
	protected $_name = 'M_Prefecture';
	protected $_primary = array('PrefectureCode');
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
	 * すべての都道府県データを取得する
	 *
	 * @return ResultInterface
	 */
	public function getAll()
	{
        $sql = " SELECT * FROM M_Prefecture WHERE ValidFlg = 1 ORDER BY PrefectureCode ASC ";
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
        $sql = " SELECT PrefectureName FROM M_Prefecture WHERE ValidFlg = 1 AND PrefectureCode = :PrefectureCode ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':PrefectureCode' => $prefectureCode,
        );

        return $stm->execute($prm)->current()['PrefectureName'];
	}

	/**
	 * 指定都道府県名の都道府県コードを取得する。
	 *
	 * @param string $prefectureName 都道府県名
	 * @param boolean $isShortName 略称か否か
	 * @return int 都道府県コード
	 */
	public function getPrefectureCode($prefectureName, $isShortName = false)
	{
        $findCol = ($isShortName) ? "PrefectureShortName" : "PrefectureName";
        $sql = " SELECT PrefectureCode FROM M_Prefecture WHERE :FIND_COLNAME = :PrefectureName ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':FIND_COLNAME' => $findCol,
                ':PrefectureName' => $prefectureName,
        );

        return $stm->execute($prm)->current()['PrefectureCode'];
	}
}
