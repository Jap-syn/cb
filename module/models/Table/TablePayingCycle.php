<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * M_PayingCycle(立替サイクルマスター)テーブルへのアダプタ
 */
class TablePayingCycle
{
    protected $_name = 'M_PayingCycle';
    protected $_primary = array('PayingCycleId');
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
     * 立替サイクルマスターデータを取得する
     *
     * @param int $payingCycleId 立替サイクルID
     * @return ResultInterface
     */
    public function find($payingCycleId)
    {
        $sql = " SELECT * FROM M_PayingCycle WHERE PayingCycleId = :PayingCycleId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':PayingCycleId' => $payingCycleId,
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
        $sql  = " INSERT INTO M_PayingCycle (PayingCycleName, ListNumber, PayingDecisionClass, FixPattern, PayingDecisionDay, PayingDecisionDate1, PayingDecisionDate2, PayingDecisionDate3, PayingDecisionDate4, PayingClass, PayingDay, PayingMonth, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :PayingCycleName ";
        $sql .= " , :ListNumber ";
        $sql .= " , :PayingDecisionClass ";
        $sql .= " , :FixPattern ";
        $sql .= " , :PayingDecisionDay ";
        $sql .= " , :PayingDecisionDate1 ";
        $sql .= " , :PayingDecisionDate2 ";
        $sql .= " , :PayingDecisionDate3 ";
        $sql .= " , :PayingDecisionDate4 ";
        $sql .= " , :PayingClass ";
        $sql .= " , :PayingDay ";
        $sql .= " , :PayingMonth ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':PayingCycleName' => $data['PayingCycleName'],
                ':ListNumber' => $data['ListNumber'],
                ':PayingDecisionClass' => $data['PayingDecisionClass'],
                ':FixPattern' => $data['FixPattern'],
                ':PayingDecisionDay' => $data['PayingDecisionDay'],
                ':PayingDecisionDate1' => $data['PayingDecisionDate1'],
                ':PayingDecisionDate2' => $data['PayingDecisionDate2'],
                ':PayingDecisionDate3' => $data['PayingDecisionDate3'],
                ':PayingDecisionDate4' => $data['PayingDecisionDate4'],
                ':PayingClass' => $data['PayingClass'],
                ':PayingDay' => $data['PayingDay'],
                ':PayingMonth' => $data['PayingMonth'],
                ':RegistDate' => date('Y-m-d H:i:s'),
                ':RegistId' => $data['UserId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $data['UserId'],
                ':ValidFlg' => isset($data['ValidFlg']) ? $data['ValidFlg'] : 1,
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
    }

    /**
     * 指定されたレコードを更新する。
     *
     * @param array $data 更新内容
     * @param int $payingCycleId 立替サイクルID
     * @return ResultInterface
     */
    public function saveUpdate($data, $payingCycleId)
    {
        $row = $this->find($payingCycleId)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE M_PayingCycle ";
        $sql .= " SET ";
        $sql .= "     PayingCycleName = :PayingCycleName ";
        $sql .= " ,   ListNumber = :ListNumber ";
        $sql .= " ,   PayingDecisionClass = :PayingDecisionClass ";
        $sql .= " ,   FixPattern = :FixPattern ";
        $sql .= " ,   PayingDecisionDay = :PayingDecisionDay ";
        $sql .= " ,   PayingDecisionDate1 = :PayingDecisionDate1 ";
        $sql .= " ,   PayingDecisionDate2 = :PayingDecisionDate2 ";
        $sql .= " ,   PayingDecisionDate3 = :PayingDecisionDate3 ";
        $sql .= " ,   PayingDecisionDate4 = :PayingDecisionDate4 ";
        $sql .= " ,   PayingClass = :PayingClass ";
        $sql .= " ,   PayingDay = :PayingDay ";
        $sql .= " ,   PayingMonth = :PayingMonth ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE PayingCycleId = :PayingCycleId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':PayingCycleId' => $payingCycleId,
                ':PayingCycleName' => $row['PayingCycleName'],
                ':ListNumber' => $row['ListNumber'],
                ':PayingDecisionClass' => $row['PayingDecisionClass'],
                ':PayingDecisionDay' => $row['PayingDecisionDay'],
                ':FixPattern' => $row['FixPattern'],
                ':PayingDecisionDate1' => $row['PayingDecisionDate1'],
                ':PayingDecisionDate2' => $row['PayingDecisionDate2'],
                ':PayingDecisionDate3' => $row['PayingDecisionDate3'],
                ':PayingDecisionDate4' => $row['PayingDecisionDate4'],
                ':PayingClass' => $row['PayingClass'],
                ':PayingDay' => $row['PayingDay'],
                ':PayingMonth' => $row['PayingMonth'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $data['UserId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
    }

    /*
     * 加盟店立替サイクル一覧データを全取得する
    *
    * @return ResultInterface
    */
    public function findAll()
    {
        $sql = " SELECT * FROM M_PayingCycle ";

        $stm = $this->_adapter->query($sql);

        return $stm->execute($prm);
    }

    // 2015/06/26 Add NDC TableBusinessCalendar から移動 Stt
    /**
     * 次回締め日を取得する。
     *
     * @param int $enterpriseId 加盟店ID
     * @param string $date 基準日 'yyyy-MM-dd'書式で通知
     *
     * @return string 締め日 'yyyy-MM-dd'書式で通知
     */
    public function getNextFixedDate($enterpriseId, $date)
    {
        // 立替サイクル情報を取得する。
        $sql = <<<EOQ
            SELECT  *
            FROM    M_PayingCycle pc
                    INNER JOIN T_Enterprise e ON (e.PayingCycleId = pc.PayingCycleId)
            WHERE   e.EnterpriseId = :EnterpriseId
            ;
EOQ;
        $data = $this->_adapter->query($sql)->execute(array( ':EnterpriseId' => $enterpriseId ))->current();

        // 立替サイクル種別
        $payingDecisionClass = $data['PayingDecisionClass'];
        // 立替確定日ー曜日
        $payingDecisionDay = $data['PayingDecisionDay'];
        // 立替確定日ー日付指定１～４
        $aryPayingDecisionDate = array();
        if (!is_null($data['PayingDecisionDate1']) && ($data['PayingDecisionDate1'] > 0) ) { $aryPayingDecisionDate[] = $data['PayingDecisionDate1']; }
        if (!is_null($data['PayingDecisionDate2']) && ($data['PayingDecisionDate2'] > 0) ) { $aryPayingDecisionDate[] = $data['PayingDecisionDate2']; }
        if (!is_null($data['PayingDecisionDate3']) && ($data['PayingDecisionDate3'] > 0) ) { $aryPayingDecisionDate[] = $data['PayingDecisionDate3']; }
        if (!is_null($data['PayingDecisionDate4']) && ($data['PayingDecisionDate4'] > 0) ) { $aryPayingDecisionDate[] = $data['PayingDecisionDate4']; }
        sort($aryPayingDecisionDate);// 日付の若い順ソート

        // 立替サイクル種別が「0：曜日」の場合
        if ($payingDecisionClass == 0) {
            // 立替確定日ー曜日から締め日を取得する。
            $result = $this->getNextWeekDate($date, $payingDecisionDay);
        // 立替サイクル種別が「1：日付」の場合
        } else if ($payingDecisionClass == 1) {
            // 立替確定日－日付指定１～４から締め日を取得する。
            $result = $this->getNextDays($date, $aryPayingDecisionDate);
        }
        return $result;
    }

    /**
     * 次回立替日（振込日）を取得する
     *
     * @param int $enterpriseId 加盟店ID
     * @param string $date 基準日 'yyyy-MM-dd'書式で通知
     *
     * @return string 立替日 'yyyy-MM-dd'書式で通知
     */
    public function getNextTransferDate($enterpriseId, $date)
    {
        $mdlbc = new TableBusinessCalendar($this->_adapter);

        // 立替サイクル情報を取得する。
        $sql = <<<EOQ
            SELECT  *
            FROM    M_PayingCycle pc
                    INNER JOIN T_Enterprise e ON (e.PayingCycleId = pc.PayingCycleId)
            WHERE   e.EnterpriseId = :EnterpriseId
            ;
EOQ;
        $data = $this->_adapter->query($sql)->execute(array( ':EnterpriseId' => $enterpriseId ))->current();

        // 立替日ー種別
        $payingClass = $data['PayingClass'];
        // 立替日ー曜日
        $payingDay = $data['PayingDay'];
        // 立替日ー翌月
        $payingMonth = $data['PayingMonth'];

        // 立替日ー種別が「0：翌週」の場合
        if ($payingClass == 0) {
            // 立替日ー曜日から立替日を取得する。
            $w = date('w', strtotime($date)); // 基準日の曜日を取得
            $result = $this->getNextWeekDate($date, $payingDay);
            // 基準日曜日 < 指定曜日の場合（火曜日 < 金曜日など）、"今週"の曜日算出で終わってしまうので、、"翌週"を算出するために再呼び出し
            if ($w < $payingDay) {
                $result = $this->getNextWeekDate($result, $payingDay);
            }
            // 立替日ー種別が「1：翌月」の場合
        } else if ($payingClass == 1) {
            // 立替日－翌月から立替日を取得する。
            $aryPayingDecisionDate = array();
            $aryPayingDecisionDate[] = $payingMonth;
            $result = $this->getNextDays(date('Y-m-d', strtotime(date('Y-m-01', strtotime($date)) . ' +1 month')), $aryPayingDecisionDate);
            // 立替日ー種別が「2：翌々月」の場合
        } else if ($payingClass == 2) {
            // 立替日ー翌月から立替日を取得する
            $aryPayingDecisionDate = array();
            $aryPayingDecisionDate[] = $payingMonth;
            $result = $this->getNextDays(date('Y-m-d', strtotime(date('Y-m-01', strtotime($date)) . ' +2 month')), $aryPayingDecisionDate);
        }

        // 翌稼働日を算出する
        $result = $mdlbc->getNextBusinessDate($result);

        return $result;
    }

    /**
     * 基準日から先（未来日）の直近の曜日の締め もしくは 立替の日付を取得する
     *
     * @param string $date 日付（'yyyy-MM-dd'）
     * @param int $day 曜日
     * @return 日付（'yyyy-MM-dd'）
     */
    private function getNextWeekDate($date, $day) {
        // 曜日によって次回締め日 or 立替日を取得する。
        switch ($day) {
            // 日曜日
            case 0:
                $result = date('Y-m-d', strtotime($date . " next Sunday"));
                break;
            // 月曜日
            case 1:
                $result = date('Y-m-d', strtotime($date . " next Monday"));
                break;
            // 火曜日
            case 2:
                $result = date('Y-m-d', strtotime($date . " next Tuesday"));
                break;
            // 水曜日
            case 3:
                $result = date('Y-m-d', strtotime($date . " next Wednesday"));
                break;
            // 木曜日
            case 4:
                $result = date('Y-m-d', strtotime($date . " next Thursday"));
                break;
            // 金曜日
            case 5:
                $result = date('Y-m-d', strtotime($date . " next Friday"));
                break;
            // 土曜日
            case 6:
                $result = date('Y-m-d', strtotime($date . " next Saturday"));
                break;
            // その他
            default:
                $result = $date;
        }
        return $result;
    }

    /**
     * 基準日から先（未来日）の直近の日にちの締め日付を取得する。
     *
     * @param string $date 日付（'yyyy-MM-dd'）
     * @param array $aryPayingDecisionDate 日にち１～４
     * @return 日付（'yyyy-MM-dd'）
     */
    private function getNextDays($date, $aryPayingDecisionDate)
    {
        // 基準日の日にちを取得
        $day = (int)date('d', strtotime($date));

        $decisionDateCount = 0;
        if(!empty($aryPayingDecisionDate)) {
            $decisionDateCount = count($aryPayingDecisionDate);
        }
        // 最大日より今日がそれ以上をさす時
        $maxday = (int)$aryPayingDecisionDate[$decisionDateCount - 1];
        if ($maxday <= $day) {
            $preval = date('Y-m-' . $aryPayingDecisionDate[0], strtotime(date('Y-m-01', strtotime($date)) . ' +1 month'));// 仮日算出
            $nextMonthLastYmd = date('Y-m-t', strtotime(date('Y-m-01', strtotime($date)) . ' +1 month'));// 次月の最終日算出
            return ($preval > $nextMonthLastYmd) ? $nextMonthLastYmd : $preval;
        }

        // 今月内に締め日は確定できるケース
        $tmp = 0;
        for ($i=0; $i<$decisionDateCount; $i++) {
            if ($day <$aryPayingDecisionDate[$i]) {
                $tmp = $aryPayingDecisionDate[$i];
                break;
            }
        }
        $lastday = (int)date('t', strtotime($date));// 今月最終日を取得
        return ($lastday < $tmp) ?date('Y-m-' . $lastday, strtotime($date)) : date('Y-m-' . $tmp, strtotime($date));
    }
    // 2015/06/26 Add NDC TableBusinessCalendar から移動 End
}
