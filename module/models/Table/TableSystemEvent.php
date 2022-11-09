<?php
//require_once 'functions.php';
//require_once 'Zend/Db/Table/Abstract.php';
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * 指定日時におけるイベント種別を管理するM_SystemEventテーブルへのアダプタ
 */
class TableSystemEvent
{
	protected $_name = 'M_SystemEvent';
	protected $_primary = array('SystemEventId');
    protected $_sequence = true;                // AUTO_INCREMENTは使わない

    /**
     * 新しい行オブジェクトを作成する
     *
     * @param null | array $data 初期データの連想配列
     * @return Zend_Db_Table_Row_Abstract このテーブルクラスのスキーマを持つ新しい行オブジェクト
     */
    public function createRow(array $data = array())
    {
        $tmpl = array(
            'ValidFlg' => 1
        );
        $data = array_merge($tmpl, $data);
        return parent::createRow($data);
    }

    /**
	 * 必要なパラメータを指定して新しい行を挿入する
	 *
	 * @param int $eventId システムイベントID
	 * @param string $eventName イベント表示名
	 * @param int $opId 登録担当者のオペレータID
	 * @param null | boolean $isValid 有効フラグ。省略時はtrue
	 * @return プライマリキー値
	 */
	public function saveNew($eventId, $eventName, $opId, $isValid = true)
	{
        $data = array(
            'SystemEventId' => (int)$eventId,
            'SystemEventName' => nvl($eventName),
            'RegistDate' => date('Y-m-d H:i:s'),
            'RegistOpId' => (int)$opId,
            'ValidFlg' => $isValid ? 1 : 0
        );

		$row = $this->createRow($data);
        return $row->save();
	}

	/**
	 * 指定されたレコードを更新する。更新可能なカラムは
	 * SystemEventName（システムイベント名）、RegistOpId（登録担当オペレータID）および
	 * ValidFlg（有効フラグ）のみである
	 *
	 * @param array $data 更新内容
	 * @param int $eventId 更新するシステムイベントID
	 */
	public function saveUpdate($data, $eventId)
	{
        $data['ModifiedDate'] = date('Y-m-d H:i:s');
		$row = $this->find($seq)->current();

        $targetColumns = array('SystemEventName', 'RegistOpId', 'ValidFlg');
		foreach ($data as $key => $value)
		{
			if (in_array($key, $targetColumns) && isset($row->$key))
			{
				$row->$key = $value;
			}
		}

		$row->save();
	}

}
