<?php
//require_once 'functions.php';
//require_once 'Zend/Db/Table/Abstract.php';
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * 繰り返し発生するシステムイベントのテンプレートを管理するM_RepeatableEventテーブルへのアダプタ
 */
class TableRepeatableEvent
{
    // 繰り返しパターン定数：毎日
    const PTN_EVERY_DAY = 1;

    // 繰り返しパターン定数：毎週
    const PTN_EVERY_WEEK = 2;

    // 繰り返しパターン定数：毎月
    const PTN_EVERY_MONTH = 3;

    // 繰り返しパターン定数：毎年
    const PTN_EVERY_YEAR = 4;

    /**
     * 定義されているすべての繰り返しパターン値を配列で取得する
     *
     * @static
     * @return array
     */
    public static function getAllRepeatablePatterns()
    {
        return array(
            self::PTN_EVERY_DAY,
            self::PTN_EVERY_WEEK,
            self::PTN_EVERY_MONTH,
            self::PTN_EVERY_YEAR
        );
    }

    /**
     * 指定の値が繰り返しパターン値として有効であるかを判断する
     *
     * @static
     * @param int $ptn 繰り返しパターン値
     * @return boolean
     */
    public static function isValidRepeatablePattern($ptn)
    {
        $ptn = (int)$ptn;
        return in_array($ptn, self::getAllRepeatablePatterns());
    }

	protected $_name = 'M_RepeatableEvent';
	protected $_primary = array('RepeatableEventSeq');

    /**
     * 新しい行オブジェクトを作成する
     *
     * @param null | array $data 初期データの連想配列
     * @return Zend_Db_Table_Row_Abstract このテーブルクラスのスキーマを持つ新しい行オブジェクト
     */
    public function createRow(array $data = array())
    {
        $tmpl = array(
            // TODO: 新規行データの初期値はここで定義する
        );
        $data = array_merge($tmpl, $data);
        return parent::createRow($data);
    }

    /**
	 * 関連するシステムイベントIDと初期データを指定して新しい行データを挿入する
	 *
	 * @param int $eventId システムイベントID
	 * @param array $data その他の初期データ
	 * @return プライマリキー値
	 */
	public function saveNew($eventId, array $data)
	{
        $eventId = (int)$eventId;
        $data['SystemEventId'] = $eventId;
        $data['RegistDate'] = date('Y-m-d H:i:s');

		$info = $this->info();

		$row = $this->createRow();
		$hasChanged = false;
		foreach ($info["cols"] as $colName)
		{
			if (isset($data[$colName]) && !$this->isPrimaryKey($colName))
			{
				$row->$colName = $data[$colName];
				$hasChanged = true;
			}
		}

		if ($hasChanged)
		{
			return $row->save();
		}
		else
		{
			return 0;
		}
	}

    /**
     * 指定カラムがプライマリキーを構成するカラムであるかを判断する
     *
     * @access protected
     * @param string $colName カラム名
     * @return boolean
     */
	protected function isPrimaryKey($colName) {
		$primaries = $this->_primary;
		if(is_array($primaries)) {
			return in_array($colName, $primaries);
		} else {
			return $colName == $primaries;
		}
	}

	/**
	 * 指定されたレコードを更新する。
	 *
	 * @param array $data 更新内容
	 * @param int $seq 更新するSeq
	 */
	public function saveUpdate(array $data, $seq)
	{
        $data['RegistDate'] = date('Y-m-d H:i:s');
		$row = $this->find($seq)->current();

		foreach ($data as $key => $value)
		{
			if (isset($row->$key) && !$this->isPrimaryKey($key))
			{
				$row->$key = $value;
			}
		}

		$row->save();
	}

    /**
     * 指定のシステムイベントに関連付けられたデータを検索する
     *
     * @param int $eventId システムイベントID
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function findBySystemEventId($eventId)
    {
        $eventId = (int)$eventId;
        $where = $this->_db->quoteInto('SystemEventId = ?', $eventId);
        return $this->fetchAll($where, 'RepeatableEventSeq');
    }

}
