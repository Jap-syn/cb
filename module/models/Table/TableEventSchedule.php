<?php
//require_once 'functions.php';
//require_once 'Zend/Db/Table/Abstract.php';
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * 指定日時におけるシステムイベントを管理するT_EventScheduleテーブルへのアダプタ
 */
class TableEventSchedule
{
    // ステータス定数：有効
    const STATUS_VALID = 1;

    // ステータス定数：期間終了
    const STATUS_EXPIRED = 2;

    // ステータス定数：取消
    const STATUS_CANCELED = 9;

	protected $_name = 'T_EventSchedule';
	protected $_primary = array('EventScheduleSeq');

    /**
     * 新しい行オブジェクトを作成する
     *
     * @param null | array $data 初期データの連想配列
     * @return Zend_Db_Table_Row_Abstract このテーブルクラスのスキーマを持つ新しい行オブジェクト
     */
    public function createRow(array $data = array())
    {
        $tmpl = array(
            'EventStatus' => self::STATUS_VALID
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

		$info = $this->info();

		$row = $this->createRow($data);
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
        return $this->fetchAll($where, 'EventScheduleSeq');
    }

}
