<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;
use Coral\Base\BaseGeneralUtils;

/**
 * T_BusinessCalendarテーブルへのアダプタ
 */
class TableBusinessCalendar
{
	protected $_name = 'T_BusinessCalendar';
	protected $_primary = array('BusinessDate');
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
	 * 指定年月のカレンダーを取得するデータを取得する。
	 * 対象月のデータがなかった場合は、デフォルト値をインサート後、
	 * 当該データを返す。
	 *
	 * @param int $year 年
	 * @param int $month 月
	 * @return ResultInterface
	 */
	public function getMonthCalendar($year, $month)
	{
        $date1 = sprintf("%04d", $year) . "-" . sprintf("%02d", $month) . "-01";    // 月初
        $date2 = date('Y-m-d', strtotime("last day of " . $date1));                 // 月末

        $sql = " SELECT * FROM T_BusinessCalendar WHERE BusinessDate BETWEEN :date1 AND :date2 ORDER BY BusinessDate ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':date1' => $date1,
                ':date2' => $date2,

        );

        $ri = $stm->execute($prm);

//         if ($ri->count() == 0) {

//             $days = BaseGeneralUtils::CalcSpanDays($date1, $date2) + 1;
//             for ($i = 0 ; $i < $days; $i++) {
//                 $businessdate = date("Y-m-d", strtotime($date1 . " +" . $i . " day"));
//                 $weekday = date("w", strtotime($date1 . " +" . $i . " day"));
//                 $businessflg = ($weekday == 0 || $weekday == 6) ? 0 : 1;

//                 $newDate = array();
//                 $newDate['BusinessDate'] = $businessdate;
//                 $newDate['BusinessFlg'] = $businessflg;
//                 $newDate['WeekDay'] = $weekday;

//                 // 新しいレコードをインサートする
//                 $this->saveNew($newDate);
//             }

//             // 再帰呼び出し
//             return $this->getMonthCalendar($year, $month);

//         }
//         else {
//             return $ri;
//         }
        return $ri;
	}

	/**
	 * 新しいレコードをインサートする。
	 *
	 * @param array $data インサートする連想配列
	 * @return プライマリキーのバリュー
	 */
	public function saveNew($data)
	{
        $sql  = " INSERT INTO T_BusinessCalendar (BusinessDate, BusinessFlg, WeekDay, Label, Note, ToyoBusinessFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :BusinessDate ";
        $sql .= " , :BusinessFlg ";
        $sql .= " , :WeekDay ";
        $sql .= " , :Label ";
        $sql .= " , :Note ";
        $sql .= " , :ToyoBusinessFlg ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':BusinessDate' => isset($data['BusinessDate']) ? $data['BusinessDate'] : "0000-00-00",
                ':BusinessFlg' => $data['BusinessFlg'],
                ':WeekDay' => $data['WeekDay'],
                ':Label' => $data['Label'],
                ':Note' => $data['Note'],
                ':ToyoBusinessFlg' => $data['ToyoBusinessFlg'],
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
	 * @param string $date 更新する日付 'yyyy-MM-dd'書式で通知
	 */
	public function saveUpdate($data, $date)
	{
        $sql = " SELECT * FROM T_BusinessCalendar WHERE BusinessDate = :BusinessDate ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':BusinessDate' => $date,
        );

        $row = $stm->execute($prm)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_BusinessCalendar ";
        $sql .= " SET ";
        $sql .= "     BusinessFlg = :BusinessFlg ";
        $sql .= " ,   WeekDay = :WeekDay ";
        $sql .= " ,   Label = :Label ";
        $sql .= " ,   Note = :Note ";
        $sql .= " ,   ToyoBusinessFlg = :ToyoBusinessFlg ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE BusinessDate = :BusinessDate ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':BusinessDate' => $date,
                ':BusinessFlg' => $row['BusinessFlg'],
                ':WeekDay' => $row['WeekDay'],
                ':Label' => $row['Label'],
                ':Note' => $row['Note'],
                ':ToyoBusinessFlg' => $row['ToyoBusinessFlg'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
	}

	/**
	 * 指定年のデータ存在チェック
	 * @param string $date
	 * @return boolean
	 */
	public function  isYearDataExisted($year)
	{
        $sql = <<<EOQ
SELECT 	COUNT(BusinessDate) cnt
FROM	T_BusinessCalendar
WHERE	ValidFlg = 1
AND		BusinessDate >= :from
AND		BusinessDate <= :to;
EOQ;

        $dateFrom = $year. '-01-01';
        $dateTo = $year. '-12-31';

        $stm = $this->_adapter->query($sql);
        $cnt = $stm->execute(array(
                ':from' => $dateFrom,
                ':to' => $dateTo
        ))->current()['cnt'];

        return $cnt > 0;
	}

	/**
	 * 指定年のデータ削除処理
	 * @param string $year
	 * @param string $fromDate(m-d形式)
	 * @return \Zend\Db\Adapter\Driver\ResultInterface　削除処理結果
	 */
	public function deleteInvalidDataOfYear($year, $fromDate = '01-01')
	{
        $sql = <<<EOQ
DELETE FROM
        T_BusinessCalendar
WHERE   ValidFlg = 0
AND		BusinessDate >= :from
AND		BusinessDate <= :to;
EOQ;

        $dateFrom = $year. '-'. $fromDate;
        $dateTo = $year. '-12-31';

        return $this->_adapter->query($sql)->execute(array(
                ':from' => $dateFrom,
                ':to' => $dateTo
        ));
    }

    /**
     * 指定範囲のカレンダー備考をクリア
     * @param $conditon 更新条件
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function clearNote($condition)
    {
        $sql = <<<EOQ
UPDATE  T_BusinessCalendar
SET     Note = null,
        UpdateDate = :updateDate,
        UpdateId = :userId
WHERE   BusinessDate >= :from
EOQ;
        return $this->_adapter->query($sql)->execute(array(
                ':updateDate' => date('Y-m-d H:i:s'),
                ':userId' => $condition['userId'],
                ':from' => $condition['from'],
        ));
    }

    /**
     * 指定しているメッセージを備考に設定
     * @param unknown $condition
     */
    public function setNote($condition)
    {
        $sql = <<<EOQ
UPDATE  T_BusinessCalendar
SET     Note = CONCAT(IFNULL(Note, ''), :message, '　'),
        UpdateDate = :updateDate,
        UpdateId = :userId
WHERE   BusinessDate = :date
EOQ;
        return $this->_adapter->query($sql)->execute(array(
                ':message' => $condition['message'],
                ':updateDate' => date('Y-m-d H:i:s'),
                ':userId' => $condition['userId'],
                ':date' => $condition['date']
        ));
    }

    /**
     * 曜日より、指定範囲内の日付を取得
     * @param string $weekDay 曜日
     * @param string $fromDate From日
     * @param string $toDate To日
     * @return array 日付リスト
     */
    public function getDateByWeekDay($weekDay, $fromDate, $toDate)
    {
        $sql = <<<EOQ
SELECT 	BusinessDate
FROM	T_BusinessCalendar
WHERE	ValidFlg = 1
AND		BusinessDate >= :from
AND		BusinessDate <= :to
AND     WeekDay = :weekDay;
EOQ;
        $ri = $this-> _adapter->query($sql)->execute(array(
                ':from' => $fromDate,
                ':to' => $toDate,
                ':weekDay' => $weekDay
        ));
        $businessDateList = array();
        foreach ($ri as $row)
        {
            $businessDateList[] = $row['BusinessDate'];
        }

        return $businessDateList;
    }

    /**
     * 営業日か判定
     *
     * @param date $date 判定日
     * @return boolean 判定結果
     */
    function isBusinessDate($date) {
        // 未設定の場合FALSE
        if (is_null($date)) {
            return false;
        }

        $sql  = "SELECT BusinessFlg ";
        $sql .= "  FROM T_BusinessCalendar ";
        $sql .= " WHERE BusinessDate = :BusinessDate ";

        $params[":BusinessDate"] = $date;

        $stm = $this-> _adapter->query($sql);

        $bc = $stm->execute($params)->current();

        // データを取得でき、営業日フラグが1の場合のみTRUE
        if ($bc == false) {
            return false;
        }
        elseif($bc["BusinessFlg"] == 1) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * 指定のFROM～TOのカレンダーを取得するデータを取得する。
     *
     * @param int $fromDate 開始日(Y-m-d形式)
     * @param int $toDate 終了日(Y-m-d形式)
     * @return ResultInterface
     */
    public function getCalendar($fromDate, $toDate)
    {
        $sql = " SELECT * FROM T_BusinessCalendar WHERE BusinessDate BETWEEN :date1 AND :date2 ORDER BY BusinessDate ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':date1' => $fromDate,
                ':date2' => $toDate,
        );

        $ri = $stm->execute($prm);

        return $ri;
    }

    /**
     * 指定の日付を含む、以降の稼働日を取得する
     * @param unknown $date
     */
    public function getNextBusinessDate($date) {
        $sql = " SELECT MIN(BusinessDate) AS BusinessDate FROM T_BusinessCalendar WHERE BusinessDate >= :BusinessDate AND BusinessFlg = 1 ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':BusinessDate' => $date,
        );

        $ri = $stm->execute($prm);

        return $ri->current()['BusinessDate'];
    }
    /**
     * 指定の日付を含む、以前の稼働日を取得する
     * @param unknown $date
     */
    public function getPrevBusinessDate($date) {
        $sql = " SELECT MAX(BusinessDate) AS BusinessDate FROM T_BusinessCalendar WHERE BusinessDate <= :BusinessDate AND BusinessFlg = 1 ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':BusinessDate' => $date,
        );

        $ri = $stm->execute($prm);

        return $ri->current()['BusinessDate'];
    }

    /**
     * 指定の日付を含まない、以降の稼働日を取得する
     * @param unknown $date
     */
    public function getNextBusinessDateNonInclude($date) {
        $sql = " SELECT MIN(BusinessDate) AS BusinessDate FROM T_BusinessCalendar WHERE BusinessDate > :BusinessDate AND BusinessFlg = 1 ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':BusinessDate' => $date,
        );

        $ri = $stm->execute($prm);

        return $ri->current()['BusinessDate'];
    }

    /**
     * 指定の日付を含まない、○営業日後の稼働日を取得する
     * @param unknown $date
     */
    public function getNextBusinessDateNonIncludeByDays($date, $days) {

        $sql = " SELECT MAX(BusinessDate) AS BusinessDate FROM ( SELECT BusinessDate AS BusinessDate FROM T_BusinessCalendar WHERE BusinessDate > :BusinessDate AND BusinessFlg = 1 ORDER BY BusinessDate LIMIT $days ) tmp ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':BusinessDate' => $date,
        );

        $ri = $stm->execute($prm);

        return $ri->current()['BusinessDate'];
    }

    /**
     * 東洋紙業の営業日を判定
     *
     * @param date $date 判定日
     * @return boolean 判定結果
     */
    function isToyoBusinessDate($date) {
        // 未設定の場合FALSE
        if (is_null($date)) {
            return false;
        }

        $sql  = "SELECT ToyoBusinessFlg ";
        $sql .= "  FROM T_BusinessCalendar ";
        $sql .= " WHERE BusinessDate = :BusinessDate ";

        $params[":BusinessDate"] = $date;

        $stm = $this-> _adapter->query($sql);

        $bc = $stm->execute($params)->current();

        // データを取得でき、営業日フラグが1の場合のみTRUE
        if ($bc == false) {
            return false;
        }
        elseif($bc["ToyoBusinessFlg"] == 1) {
            return true;
        }
        else {
            return false;
        }
    }

}
