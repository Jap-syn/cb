<?php

namespace models\Logic;

use Coral\Base\BaseLog;
use models\Table\TableCreditTransfer;
use Zend\Db\Adapter\Adapter;
use models\Table\TableBusinessCalendar;
use models\Table\TableCreditTransferCalendar;
use Zend\Log\Logger;

/**
 * 口座振替ユーティリティ.
 */
class LogicCreditTransfer
{
    protected $_adapter = null;
    protected $_logger = null;

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
     * 直近の振替日を取得する
     */
    public function getTransderDate($creditTransferFlg)
    {
        $today = date('Y-m-d');
        $sql = " SELECT MIN(BusinessDate) AS BusinessDate FROM T_CreditTransferCalendar WHERE BusinessDate >= :BusinessDate AND CreditTransferFlg = :CreditTransferFlg AND DataType = 1 AND ExecFlg = 1 ";
        $stm = $this->_adapter->query($sql);
        $prm = array(
            ':BusinessDate' => $today,
            ':CreditTransferFlg' => $creditTransferFlg,
        );
        $ri = $stm->execute($prm);
        return $ri->current()['BusinessDate'];
    }

    /**
     * 振込日を取得する
     */
    public function getTransderCommitDate($businessDate, $creditTransferFlg)
    {
        $sql = " SELECT MIN(BusinessDate) AS BusinessDate FROM T_CreditTransferCalendar WHERE BusinessDate >= :BusinessDate AND CreditTransferFlg = :CreditTransferFlg AND DataType = 2 AND ExecFlg = 1 ";
        $stm = $this->_adapter->query($sql);
        $prm = array(
            ':BusinessDate' => $businessDate,
            ':CreditTransferFlg' => $creditTransferFlg,
        );
        $ri = $stm->execute($prm);
        return $ri->current()['BusinessDate'];
    }

    /**
     * 支払期限日より振込日を算出する
     *
     * @param $businessDate
     * @param $creditTransferFlg
     * @return mixed
     */
    public function getTransderCommitDate4LimitDate($businessDate, $creditTransferFlg)
    {
        $sql = " SELECT MAX(BusinessDate) AS BusinessDate FROM T_CreditTransferCalendar WHERE BusinessDate <= :BusinessDate AND CreditTransferFlg = :CreditTransferFlg AND DataType = 1 AND ExecFlg = 1 ";
        $stm = $this->_adapter->query($sql);
        $prm = array(
            ':BusinessDate' => $businessDate,
            ':CreditTransferFlg' => $creditTransferFlg,
        );
        $ri = $stm->execute($prm);
        return $ri->current()['BusinessDate'];
    }

    public function getAllTargetTerm($base_date)
    {
        $result = array();
        $mdlct = new TableCreditTransfer($this->_adapter);
        $datas = ResultInterfaceToArray($mdlct->getAll());

        $base_day = substr($base_date, 8, 2);
        foreach ($datas as $data) {
            if ($data['CreditTransferDay'] < (int)$base_day) {
                $credit_transfer_date = substr($base_date, 0, 8).substr('0'.$data['CreditTransferDay'], -2);
            } else {
                $credit_transfer_date = $base_date;
            }
            $result[$data['CreditTransferId']] = $this->calcDate($credit_transfer_date, $data);
        }
        return $result;
    }


    public function getAllInfo()
    {
        $result = array();
        $now = date('Y-m-d');
        $mdlct = new TableCreditTransfer($this->_adapter);
        $datas = ResultInterfaceToArray($mdlct->getAll());
        foreach ($datas as $data) {
            $result[$data['CreditTransferId']] = $this->calcDate($now, $data);
        }
        return $result;
    }

    private function calcDate($base_date, $credit_transfer_data)
    {
        $base_year = substr($base_date, 0, 4);
        $base_month = substr($base_date, 5, 2);

        $from_month = $base_month + $credit_transfer_data['CreditTransferSpanFromMonth'];
        $to_month = $base_month + $credit_transfer_data['CreditTransferSpanToTypeMonth'];

        // 月末指定の場合は、対象月の月末日に変換
        $start_day_work = $this->changeDay($base_year, $from_month, $credit_transfer_data['CreditTransferSpanFromDay']);
        $end_day_work = $this->changeDay($base_year, $to_month, $credit_transfer_data['CreditTransferSpanToDay']);

        // 対象日付が当日より未来かの判断
        $now_target = date('Ymd',mktime(0, 0, 0, $base_month, substr($base_date, 8, 2), $base_year));
        $start_target = date('Ymd',mktime(0, 0, 0, $from_month, $start_day_work, $base_year));
        $end_target = date('Ymd',mktime(0, 0, 0, $to_month, $end_day_work, $base_year));
        if (($start_target <= $now_target) && ($now_target <= $end_target)) {
            $add = -1;
        } elseif ($now_target < $start_target) {
            $add = -2;
        } else {
            $start_day_work = $this->changeDay($base_year, $from_month + 1, $credit_transfer_data['CreditTransferSpanFromDay']);
            $end_day_work = $this->changeDay($base_year, $to_month + 1, $credit_transfer_data['CreditTransferSpanToDay']);
            $start_target = date('Ymd',mktime(0, 0, 0, $from_month + 1, $start_day_work, $base_year));
            $end_target = date('Ymd',mktime(0, 0, 0, $to_month + 1, $end_day_work, $base_year));
            if (($start_target <= $now_target) && ($now_target <= $end_target)) {
                $add = 0;
            } else {
                $add = 1;
            }
        }

        $start_day_work = $this->changeDay($base_year, $from_month + $add, $credit_transfer_data['CreditTransferSpanFromDay']);
        $end_day_work   = $this->changeDay($base_year, $to_month   + $add, $credit_transfer_data['CreditTransferSpanToDay']);
        $start_target = date('Y-m-d',mktime(0, 0, 0, $from_month + $add, $start_day_work, $base_year));
        $end_target   = date('Y-m-d',mktime(0, 0, 0, $to_month   + $add, $end_day_work,   $base_year));
        return array('SpanFrom' => $start_target, 'SpanTo' => $end_target);
    }


    /**
     * 口座振替別支払期限取得
     *
     * @param $base_date string 基準日（yyyy-mm-dd形式）
     * @return array 支払期限情報
     */
    public function getCreditTransferLimitDay($base_date) {
        $result = array();
        $mdlct = new TableCreditTransfer($this->_adapter);
        $datas = ResultInterfaceToArray($mdlct->getAll());
        foreach ($datas as $data) {
            $result[$data['CreditTransferId']] = $this->calcLimitDateMain($base_date, $data);
        }
        return $result;
    }

    /**
     * 支払期限算出（メイン）
     *
     * @param $base_date string 基準日（yyyy-mm-dd形式）
     * @param $credit_transfer_data Object 口座振替マスタ情報
     * @return string 支払期限(yyyy-mm-dd形式)
     */
    private function calcLimitDateMain($base_date, $credit_transfer_data)
    {
        $add_month = $this->calcAddMonth($base_date, $credit_transfer_data);
        return $this->calcLimitDate($base_date, $credit_transfer_data, $add_month);
    }

    /**
     * 加算月計算
     *
     * @param $base_date string 基準日（yyyy-mm-dd形式）
     * @param $credit_transfer_data Object 口座振替マスタ情報
     * @return int 加算月数（基準日をベースに何カ月後の振替日、支払期限日の算出を行うか）
     */
    private function calcAddMonth($base_date, $credit_transfer_data)
    {
        // 月末指定の場合は、対象月の月末日に変換
        $start_day_work = $this->changeDay(substr($base_date, 0, 4), substr($base_date, 5, 2) + $credit_transfer_data['CreditTransferSpanFromMonth'], $credit_transfer_data['CreditTransferSpanFromDay']);
        $end_day_work = $this->changeDay(substr($base_date, 0, 4), substr($base_date, 5, 2) + $credit_transfer_data['CreditTransferSpanToTypeMonth'], $credit_transfer_data['CreditTransferSpanToDay']);

        // 対象日付が当日より未来かの判断
        $now_target = date('Ymd',mktime(0, 0, 0, substr($base_date, 5, 2), substr($base_date, 8, 2), substr($base_date, 0, 4)));
        $start_target = date('Ymd',mktime(0, 0, 0, substr($base_date, 5, 2) + $credit_transfer_data['CreditTransferSpanFromMonth'], $start_day_work, substr($base_date, 0, 4)));
        $end_target = date('Ymd',mktime(0, 0, 0, substr($base_date, 5, 2) + $credit_transfer_data['CreditTransferSpanToTypeMonth'], $end_day_work, substr($base_date, 0, 4)));
        if (($start_target <= $now_target) && ($now_target <= $end_target)) {
            return 0;
        } elseif ($now_target < $start_target) {
            return -1;
        } else {
            $start_day_work = $this->changeDay(substr($base_date, 0, 4), substr($base_date, 5, 2) + $credit_transfer_data['CreditTransferSpanFromMonth'] + 1, $credit_transfer_data['CreditTransferSpanFromDay']);
            $end_day_work = $this->changeDay(substr($base_date, 0, 4), substr($base_date, 5, 2) + $credit_transfer_data['CreditTransferSpanToTypeMonth'] + 1, $credit_transfer_data['CreditTransferSpanToDay']);
            $start_target = date('Ymd',mktime(0, 0, 0, substr($base_date, 5, 2) + $credit_transfer_data['CreditTransferSpanFromMonth'] + 1, $start_day_work, substr($base_date, 0, 4)));
            $end_target = date('Ymd',mktime(0, 0, 0, substr($base_date, 5, 2) + $credit_transfer_data['CreditTransferSpanToTypeMonth'] + 1, $end_day_work, substr($base_date, 0, 4)));
            if (($start_target <= $now_target) && ($now_target <= $end_target)) {
                return 1;
            } else {
                return 2;
            }
        }
    }

    /**
     * 支払期限算出
     *
     * @param $base_date string 基準日（yyyy-mm-dd形式）
     * @param $credit_transfer_data Object 口座振替マスタ情報
     * @param $add_month int 加算月数（基準日をベースに何カ月後の振替日、支払期限日の算出を行うか）
     * @return string 支払期限(yyyy-mm-dd形式)
     */
    private function calcLimitDate($base_date, $credit_transfer_data, $add_month)
    {
        // 振替日算出
        $day_work = $this->changeDay(substr($base_date, 0, 4), substr($base_date, 5, 2) + $add_month, $credit_transfer_data['CreditTransferDay']);
        $transferDay = date('Y-m-d',mktime(0, 0, 0, substr($base_date, 5, 2) + $add_month, $day_work, substr($base_date, 0, 4)));

        return $this->calcLimitDateSub($transferDay, $credit_transfer_data['CreditTransferLimitDayType'], $credit_transfer_data['CreditTransferAfterLimitDayType'], $credit_transfer_data['CreditTransferAfterLimitDay']);
    }

    /**
     * 支払期限算出（サブ）
     *
     * @param $transfer_date string 振替日（yyyy-mm-dd形式）
     * @param $limitDayType string 口座振替支払期限条件種別
     * @param $afterLimitDayType int 口座振替支払期限種別
     * @param $afterLimitDay int 口座振替支払期限日
     * @return string 支払期限(yyyy-mm-dd形式)
     */
    private function calcLimitDateSub($transfer_date, $limitDayType, $afterLimitDayType, $afterLimitDay)
    {
        if ($limitDayType == 1) {
            $sql  = "SELECT BusinessDate ";
            $sql .= "  FROM T_BusinessCalendar ";
            $sql .= " WHERE BusinessDate > :BusinessDate ";
            $sql .= "   AND BusinessFlg  = 1 ";
            $sql .= " ORDER BY BusinessDate ";
            $sql .= " LIMIT " . $afterLimitDay . " ";
            $ri = $this->_adapter->query($sql)->execute(array(":BusinessDate" => $transfer_date));
            $i = 1;
            foreach ($ri as $row) {
                if ($i == $afterLimitDay) {
                    return substr($row['BusinessDate'], 0, 4).'-'.substr($row['BusinessDate'], 5, 2).'-'.substr($row['BusinessDate'], 8, 2);
                }
                $i++;
            }
        } else {
            // 対象日付の月末日を算出
            $day_work = $this->changeDay(substr($transfer_date, 0, 4), substr($transfer_date, 5, 2) + $afterLimitDayType, $afterLimitDay);
            return date('Y-m-d', mktime(0,0,0, substr($transfer_date, 5, 2) + $afterLimitDayType, $day_work, substr($transfer_date, 0, 4)));
        }
    }

    /**
     * 指定日付が月末日より大きい場合は、日付を月末日に変換する
     *
     * @param $year int 年
     * @param $month int 月
     * @param $day int 日
     * @return int 日付
     */
    private function changeDay($year, $month, $day)
    {
        $work = date('t',mktime(0,0,0, $month, 1, $year));
        // 月末日の上書き（31指定の6月だった場合は、30に上書きする）
        if ($day > $work) {
            return $work;
        }
        return $day;
    }

    public function getCreditTransferMethod($orderSeq)
    {
        $sql  = " SELECT o.OrderId,ao.CreditTransferRequestFlg,e.CreditTransferFlg,e.AppFormIssueCond,ec.RequestStatus,cc.ClaimPattern ";
        $sql .= " FROM T_Order o ";
        $sql .= " INNER JOIN AT_Order ao ON o.OrderSeq=ao.OrderSeq ";
        $sql .= " INNER JOIN T_Enterprise e ON o.EnterpriseId=e.EnterpriseId ";
        $sql .= " INNER JOIN T_Customer c ON o.OrderSeq=c.OrderSeq ";
        $sql .= " INNER JOIN T_ClaimControl cc ON o.OrderSeq=cc.OrderSeq ";
        $sql .= " INNER JOIN T_EnterpriseCustomer ec ON c.EntCustSeq=ec.EntCustSeq ";
        $sql .= " WHERE o.OrderSeq=:OrderSeq ";
        $stm = $this->_adapter->query($sql);
        $prm = array(
            ':OrderSeq' => $orderSeq,
        );
        $data = $stm->execute($prm)->current();

        if (is_null($data['OrderId'])) {
            $sql  = " SELECT o.OrderId,ao.CreditTransferRequestFlg,e.CreditTransferFlg,e.AppFormIssueCond,ec.RequestStatus ";
            $sql .= " FROM T_Order o ";
            $sql .= " INNER JOIN AT_Order ao ON o.OrderSeq=ao.OrderSeq ";
            $sql .= " INNER JOIN T_Enterprise e ON o.EnterpriseId=e.EnterpriseId ";
            $sql .= " INNER JOIN T_Customer c ON o.OrderSeq=c.OrderSeq ";
            $sql .= " INNER JOIN T_EnterpriseCustomer ec ON c.EntCustSeq=ec.EntCustSeq ";
            $sql .= " WHERE o.OrderSeq=:OrderSeq ";
            $stm = $this->_adapter->query($sql);
            $prm = array(
                ':OrderSeq' => $orderSeq,
            );
            $data = $stm->execute($prm)->current();
            $data['ClaimPattern'] = 1;
        }

        $sql  = " SELECT SUM(UseAmount) AS UseAmount FROM T_Order WHERE Cnl_Status = 0 AND P_OrderSeq = :OrderSeq ";
        $useAmount = $this->_adapter->query($sql)->execute(array(':OrderSeq' => $orderSeq))->current()['UseAmount'];

        // 初回請求でない場合
        if ($data['ClaimPattern'] != 1) {
            return 0; //払込票発行
        }

        // 口振しない加盟店、もしくは、口振加盟店だが注文登録時に利用しない場合
        if (($data['CreditTransferFlg'] == 0) || ($data['CreditTransferRequestFlg'] == 0)) {
            return 0; //払込票発行
        }

        // 申込用紙発行条件=発行しない、且つ、請求金額０円の場合
        if (($data['AppFormIssueCond'] == 0) && ($useAmount == 0)) {
            if ($data['CreditTransferRequestFlg'] == 1) {
                return 2; //初回WEB申込み　※請求書は出力しないが
            } else {
                return 1; //申込み用紙発行　※請求書は出力しないが
            }
        }

        // 申込用紙発行条件=請求金額0円時、且つ、請求金額０円の場合
        if (($data['AppFormIssueCond'] == 2) && ($useAmount == 0)) {
            if ($data['CreditTransferRequestFlg'] == 1) {
                return 2; //初回WEB申込み　※請求書は出力しないが
            } else {
                return 1; //申込み用紙発行
            }
        }

        // 申込用紙発行条件=初回注文時、且つ、申込ステータスが未設定か中止の場合
        if (($data['AppFormIssueCond'] == 1) && (is_null($data['RequestStatus']) || ($data['RequestStatus'] == 9))) {
            if ($data['CreditTransferRequestFlg'] == 1) {
                return 2; //初回WEB申込み
            } else {
                return 1; //申込み用紙発行
            }
        }

        // 申込ステータスが完了の場合
        if ($data['RequestStatus'] == 2) {
            return 3; //口座引落
        }

        return 0; //払込票発行
    }
}