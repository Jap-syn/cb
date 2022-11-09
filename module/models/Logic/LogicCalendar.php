<?php

namespace models\Logic;

use Zend\Db\Adapter\Adapter;
use Zend\Text\Table\Table;
use models\Table\TableUser;
use models\Table\TableBusinessCalendar;
use models\Table\TableCode;
use models\Table\TableSystemProperty;
use models\Table\TableCreditTransferCalendar;
use models\Table\TableCreditTransfer;

/**
 * カレンダー処理ロジッククラス
 */
class LogicCalendar {
    /**
     * アダプタ
     *
     * @var Adapter
     */
    protected $_adapter = null;

    /**
     * コンストラクタ
     *
     * @param Adapter $adapter
     *            アダプタ
     */
    public function __construct(Adapter $adapter) {
        $this->_adapter = $adapter;
    }

    /**
     * カレンダー自動作成
     *
     * @return string '':成功 ''以外:失敗
     */
    public function autoCreate() {
        try {
            $this->_adapter->getDriver ()->getConnection ()->beginTransaction ();

            $mdlu = new TableUser ( $this->_adapter );
            $mdlbc = new TableBusinessCalendar ( $this->_adapter );
            $mdlc = new TableCode ( $this->_adapter );

            // 処理用ユーザIDを取得
            $userId = $mdlu->getUserId ( TableUser::USERCLASS_SYSTEM, TableUser::SEQ_BATCH_USER );

            // システムプロパティより業務日付取得
            $mdlsp = new TableSystemProperty($this -> _adapter);
            $today = $mdlsp -> getValue($mdlsp::TAXCONF_DEFAULT_KEY, 'systeminfo', 'BusinessDate');
            if ($today == null || strlen($today) == 0) {
                // 設定がない場合はシステム日付
                $today = date('Y-m-d');
            }

            // 今年を取得
            $nowYear = date ( 'Y', strtotime($today));

            // 翌年を取得
            $nextYear = date ( 'Y', strtotime($today)) + 1;

            // 無効な今年かつ当日以降のデータを物理削除
            $mdlbc->deleteInvalidDataOfYear($nowYear, date('m-d', strtotime($today)));

            // 無効な翌年データを物理削除
            $mdlbc->deleteInvalidDataOfYear($nextYear);

            // 国民の祝日を抽出
            $holidayInfoList = $mdlc->getMasterByClass ( 99 );

            $holidayList = array ();

            foreach ( $holidayInfoList as $holidayInfo ) {
                //
                if ($holidayInfo ['Class1'] == 1) {
                    $date = null;
                    switch ($holidayInfo ['Class3']) {
                        case 2 :
                            $date = date ( 'Y-m-d', strtotime ( 'second mon of ' . $nextYear . '-' . $holidayInfo ['Class2'] ) );
                            break;
                        case 3 :
                            $date = date ( 'Y-m-d', strtotime ( 'third mon of ' . $nextYear . '-' . $holidayInfo ['Class2'] ) );
                            ;
                            break;
                        default :
                            $date = null;
                            break;
                    }
                    $holidayList [$date] = $holidayInfo ['KeyContent'];
                    //日曜日の場合、振替休日を設定
                    if (date ( 'w', strtotime ( $date ) ) == 0)
                        $holidayList [date ( 'Y-m-d', strtotime ( $date . ' +1 day' ) )] = '';
                } else {
                    $date = date ( 'Y-m-d', mktime ( 0, 0, 0, $holidayInfo ['Class2'], $holidayInfo ['Class3'], $nextYear ) );
                    $holidayList [$date] = $holidayInfo ['KeyContent'];
                    //連休以外
                    if ($holidayInfo ['Class2'] != 5) {
                        //日曜日の場合、振替休日を設定
                        if (date ( 'w', strtotime ( $date ) ) == 0)
                            $holidayList [date ( 'Y-m-d', strtotime ( $date . ' +1 day' ) )] = '';
                    //連休の場合
                    } elseif ($holidayInfo ['Class3'] == 5) {
                            //こどもの日＜水曜日の場合、振替休日を設定
                            if (date ( 'w', strtotime ( $date ) ) < 3)
                                $holidayList [date ( 'Y-m-d', strtotime ( $date . ' +1 day' ) )] = '';
                    }
                }
            }

            //ループSTART日
            $firstDay = $today;
            //ループEND日
            $endDay = date('Y-m-d', mktime(0, 0, 0, 12, 31, $nextYear));
            //既存データ
            $existCalendar = ResultInterfaceToArray($mdlbc->getCalendar($firstDay, $endDay));
            $skipData = array();
            foreach ($existCalendar as $row) {
                // データ整形
                $skipData[$row['BusinessDate']] = $row;
            }

            $wkDay = $firstDay;
            while ($wkDay <= $endDay) {
                // 曜日を取得
                $wkWeekDay = date('w', strtotime($wkDay));

                $wkBusinessFlg = 1;

                // 曜日=月 or 土の場合、営業日フラグを0に設定
                if ($wkWeekDay == 0 || $wkWeekDay == 6) {
                    $wkBusinessFlg = 0;
                }
                $wkLabel = '';
                // WK対象日が休日の場合、営業日フラグを0に設定
                if (isset($holidayList[$wkDay])) {
                    $wkLabel = $holidayList[$wkDay];
                    $wkBusinessFlg = 0;
                }

                $pWkDay = date('Y-m-d', strtotime($wkDay . ' -1 day'));
                $pWeekDay = date('w', strtotime($pWkDay));
                $nWkDay = date('Y-m-d', strtotime($wkDay . ' +1 day'));
                $nWeekDay = date('w', strtotime($nWkDay));

                // WK対象日の前日と翌日ともに休日の場合、営業日フラグを0に設定
                if ((isset($holidayList[$pWkDay]) || $pWeekDay == 0 || $pWeekDay == 6)
                     && (isset($holidayList[$nWkDay]) || $nWeekDay == 0 || $nWeekDay == 6)) {
                    $wkBusinessFlg = 0;
                }

                // 既存データはスキップ
                if (!isset($skipData[$wkDay])) {
                    $mdlbc->saveNew(array(
                    'BusinessDate' => $wkDay,
                    'BusinessFlg' => $wkBusinessFlg,
                    'WeekDay' => $wkWeekDay,
                    'Label' => $wkLabel,
                    'Note' => null,
                    'ToyoBusinessFlg' => $wkBusinessFlg,    // BusinessFlgと同値をセット
                    'RegistId' => $userId,
                    'UpdateId' => $userId
                    ));
                }

                $wkDay = $nWkDay;
            }

            $this->_adapter->getDriver ()->getConnection ()->commit ();
            return "";
        } catch ( \Exception $e ) {
            $this->_adapter->getDriver ()->getConnection ()->rollBack ();
            throw $e;
        }
    }

    public function autoCreditTransferCalendarCreate() {
        try {
            $this->_adapter->getDriver ()->getConnection ()->beginTransaction ();

            $mdlu = new TableUser ( $this->_adapter );
            $mdlctc = new TableCreditTransferCalendar ( $this->_adapter );
            $mdlct = new TableCreditTransfer ( $this->_adapter );

            // 処理用ユーザIDを取得
            $userId = $mdlu->getUserId ( TableUser::USERCLASS_SYSTEM, TableUser::SEQ_BATCH_USER );

            // システムプロパティより業務日付取得
            $mdlsp = new TableSystemProperty($this -> _adapter);
            $today = $mdlsp -> getValue($mdlsp::TAXCONF_DEFAULT_KEY, 'systeminfo', 'BusinessDate');
            if ($today == null || strlen($today) == 0) {
                // 設定がない場合はシステム日付
                $today = date('Y-m-d');
            }

            // 今年を取得
            $nowYear = date ( 'Y', strtotime($today));

            // 翌年を取得
            $nextYear = date ( 'Y', strtotime($today)) + 1;

            // 無効な今年かつ当日以降のデータを物理削除
            $mdlctc->deleteInvalidDataOfYear($nowYear, date('m-d', strtotime($today)));

            // 無効な翌年データを物理削除
            $mdlctc->deleteInvalidDataOfYear($nextYear);

            //ループSTART日
            $firstDay = $today;
            //ループEND日
            $endDay = date('Y-m-d', mktime(0, 0, 0, 12, 31, $nextYear));

            $ct_datas = ResultInterfaceToArray($mdlct->getAll());
            foreach ($ct_datas as $ct_data) {
                for ($i=1; $i<=2; $i++) {
                    //既存データ
                    $existCalendar = ResultInterfaceToArray($mdlctc->getCalendar($firstDay, $endDay, $ct_data['CreditTransferId'], $i));

                    $skipData = array();
                    foreach ($existCalendar as $row) {
                        // データ整形
                        $skipData[$row['BusinessDate']] = $row;
                    }

                    $wkDay = $firstDay;
                    while ($wkDay <= $endDay) {
                        $nWkDay = date('Y-m-d', strtotime($wkDay . ' +1 day'));
                        // 既存データはスキップ
                        if (!isset($skipData[$wkDay])) {
                            $mdlctc->saveNew(array(
                                                 'BusinessDate' => $wkDay,
                                                 'CreditTransferFlg' => $ct_data['CreditTransferId'],
                                                 'DataType' => 1,
                                                 'RegistId' => $userId,
                                                 'UpdateId' => $userId
                                             ));
                            $mdlctc->saveNew(array(
                                                 'BusinessDate' => $wkDay,
                                                 'CreditTransferFlg' => $ct_data['CreditTransferId'],
                                                 'DataType' => 2,
                                                 'RegistId' => $userId,
                                                 'UpdateId' => $userId
                                             ));
                        }
                        $wkDay = $nWkDay;
                    }
                }
            }

            $this->_adapter->getDriver ()->getConnection ()->commit ();
            return "";
        } catch ( \Exception $e ) {
            $this->_adapter->getDriver ()->getConnection ()->rollBack ();
            throw $e;
        }
    }

    /**
     * カレンダー加盟店締め日更新
     *
     * @return string '':成功 ''以外:失敗
     */
    public function updateFixedDate(){
        try {
            // 1日しか動かさないようにする
            if (date('d') != '01') {
                return "";
            }

            $this->_adapter->getDriver ()->getConnection ()->beginTransaction ();

            $mdlu = new TableUser ( $this->_adapter );
            $mdlbc = new TableBusinessCalendar ( $this->_adapter );
            $mdlsp = new TableSystemProperty($this -> _adapter);

            // 処理用ユーザIDを取得
            $userId = $mdlu->getUserId ( TableUser::USERCLASS_SYSTEM, TableUser::SEQ_BATCH_USER );

            // 処理対象期間を取得
            $topOfDay =date('Y-m-01');
            $wkDayF = date('Y-m-01', strtotime($topOfDay . ' +2 months'));
            $wkDayT = date ('Y-m-t', strtotime($topOfDay . ' +2 months'));
            $daysOfMonth = date('t', strtotime($topOfDay . ' +2 months'));    // 該当月の日数

            // -----------------------------------------------------------------
            // CB月次締め日を取得
            // -----------------------------------------------------------------
            $wkMonthlyTightenDay = $mdlsp -> getValue($mdlsp::TAXCONF_DEFAULT_KEY, 'systeminfo', 'cb_monthly_tighten_day');
            if (isset($wkMonthlyTightenDay) && $wkMonthlyTightenDay > 0)
            {
                $wkMonthlyTightenDate = date('Y-m-d', strtotime(sprintf($wkDayF. ' +%d days',$wkMonthlyTightenDay - 1)));
                // カレンダーの備考に「CB月次締め日」を設定
                $mdlbc->setNote(array(
                        'message' => 'CB月次締め日',
                        'userId' => $userId,
                        'date' => $wkMonthlyTightenDate
                ));
            }
            // -----------------------------------------------------------------
            // CB会計締め日を取得
            // -----------------------------------------------------------------
            $wkAccountingTightenDay = $mdlsp -> getValue($mdlsp::TAXCONF_DEFAULT_KEY, 'systeminfo', 'AccountingDay');       // 2015/11/10 Y.Suzuki 会計対応 Mod 第3引数を変更
            if (isset($wkAccountingTightenDay) && $wkAccountingTightenDay > 0)
            {
                $wkAccountingTightenDate = $mdlbc->getNextBusinessDate($wkDayF);
                for ( $i = 1; $i <= $wkAccountingTightenDay - 1; $i++ ) {
                    $wkAccountingTightenDate = $mdlbc->getNextBusinessDateNonInclude($wkAccountingTightenDate);
                }

                // カレンダーの備考に「CB会計締め日」を設定
                $mdlbc->setNote(array(
                        'message' => 'CB会計締め日',
                        'userId' => $userId,
                        'date' => $wkAccountingTightenDate
                ));
            }
            // -----------------------------------------------------------------
            // OEM締め日を抽出
            // -----------------------------------------------------------------
            $sql = <<<EOQ
SELECT  DISTINCT OemFixedDay
FROM
(SELECT	OemFixedDay1 OemFixedDay
FROM	T_Oem
WHERE	OemFixedDay1 is not NULL
AND		ValidFlg = 1
UNION ALL
SELECT  OemFixedDay2 OemFixedDay
FROM	T_Oem
WHERE	OemFixedDay2 is not NULL
AND		ValidFlg = 1
UNION ALL
SELECT  OemFixedDay3 OemFixedDay
FROM	T_Oem
WHERE	OemFixedDay3 is not NULL
AND		ValidFlg = 1) as T
GROUP BY OemFixedDay
ORDER BY OemFixedDay;
EOQ;
            $ri = $this->_adapter->query($sql)->execute(null);

            foreach($ri as $row)
            {
                $oemFixedDate = null;
                $oemFixedDay = $row['OemFixedDay'];
                if (isset($oemFixedDay) && $oemFixedDay > 0)
                {
                    if (($oemFixedDay == 99) || ($daysOfMonth < $oemFixedDay))
                    {
                        //OEM締め日=99の場合、月末日を設定
                        $oemFixedDate = $wkDayT;
                    }
                    else
                    {
                        //上記以外の場合、開始日の年月+OEM締め日
                        $oemFixedDate = date('Y-m-d', strtotime(sprintf($wkDayF. ' +%d days',$oemFixedDay - 1)));
                    }

                    // カレンダーの備考に「OEM締め日」を設定
                    $mdlbc -> setNote(array(
                        'message' => 'OEM締め日',
                            'userId' => $userId,
                            'date' => $oemFixedDate
                    ));
                }
            }
$end_stack = array();
            // -----------------------------------------------------------------
            // OEM精算日を抽出
            // -----------------------------------------------------------------
            $sql = <<<EOQ
SELECT  DISTINCT SettlementDay
FROM
(SELECT SettlementDay1 SettlementDay
FROM	T_Oem
WHERE	SettlementDay1 is not NULL
AND		ValidFlg = 1
UNION ALL
SELECT  SettlementDay2 SettlementDay
FROM	T_Oem
WHERE	SettlementDay2 is not NULL
AND		ValidFlg = 1
UNION ALL
SELECT  SettlementDay3 SettlementDay
FROM	T_Oem
WHERE	SettlementDay3 is not NULL
AND		ValidFlg = 1) as T
GROUP BY SettlementDay
ORDER BY SettlementDay;
EOQ;
            $ri = $this->_adapter->query($sql)->execute(null);

            foreach($ri as $row)
            {
                $settlementDate = null;
                $settlementDay = $row['SettlementDay'];
                if (isset($settlementDay) && $settlementDay > 0)
                {
                    if($settlementDay > 40 && $settlementDay < 46){
                    }else if (($oemFixedDay == 99) || ($daysOfMonth < $settlementDay))
                    {
                        //OEM精算日=99の場合、月末日を設定
                        $settlementDate = $wkDayT;
                        $settlementDate = $mdlbc->getNextBusinessDate($settlementDate);
                    }
                    else
                    {
                        //上記以外の場合、開始日の年月+OEM精算日
                        $settlementDate = date('Y-m-d', strtotime(sprintf($wkDayF. ' +%d days',$settlementDay - 1)));
                        $settlementDate = $mdlbc->getNextBusinessDate($settlementDate);
                    }
					array_push($end_stack, $settlementDate);

                    // カレンダーの備考に「OEM精算日」を設定
                    $mdlbc -> setNote(array(
                            'message' => 'OEM精算日',
                            'userId' => $userId,
                            'date' => $settlementDate
                    ));
                }
            }
            // -----------------------------------------------------------------
            // OEM精算日を抽出 2(精算日が41～45)
            // -----------------------------------------------------------------
            $sql = <<<EOQ
SELECT SettlementDay1, SettlementDay2, SettlementDay3, OemFixedDay1, OemFixedDay2, OemFixedDay3
FROM T_Oem
WHERE SettlementDay1 > 40 AND SettlementDay1 < 46
GROUP BY SettlementDay1, SettlementDay2, SettlementDay3, OemFixedDay1, OemFixedDay2, OemFixedDay3
EOQ;
            $ri = $this->_adapter->query($sql)->execute(null);


            foreach($ri as $row)
            {
                $settlementDate = null;
                $settlementDay1 = $row['SettlementDay1'];
                $settlementDay2 = $row['SettlementDay2'];
                $settlementDay3 = $row['SettlementDay3'];
                $oemFixedDay1 = $row['OemFixedDay1'];
                $oemFixedDay2 = $row['OemFixedDay2'];
                $oemFixedDay3 = $row['OemFixedDay3'];

                if (isset($settlementDay1) && $settlementDay1 > 0)
                {
                    $codetoday = '';
                    if($settlementDay1==41)$codetoday = "Monday";
                    if($settlementDay1==42)$codetoday = "Tuesday";
                    if($settlementDay1==43)$codetoday = "Wednesday";
                    if($settlementDay1==44)$codetoday = "Thursday";
                    if($settlementDay1==45)$codetoday = "Friday";
                    $settlementDate = date('Y-m-d',strtotime( $codetoday . ' next week' . date('Y-m-d', strtotime(sprintf($wkDayF. ' +%d days',$oemFixedDay1 - 1)))));

                    $settlementDate = $mdlbc->getPrevBusinessDate($settlementDate);

                    if(in_array($settlementDate, $end_stack)){
                        //skip

                    }else{
                        // カレンダーの備考に「OEM精算日」を設定
                        $mdlbc -> setNote(array(
                            'message' => 'OEM精算日',
                            'userId' => $userId,
                            'date' => $settlementDate
                        ));
                        array_push($end_stack, $settlementDate);
                    }
                }
                if (isset($settlementDay2) && $settlementDay2 > 0)
                {
                    $codetoday = '';
                    if($settlementDay2==41)$codetoday = "Monday";
                    if($settlementDay2==42)$codetoday = "Tuesday";
                    if($settlementDay2==43)$codetoday = "Wednesday";
                    if($settlementDay2==44)$codetoday = "Thursday";
                    if($settlementDay2==45)$codetoday = "Friday";
                    $settlementDate = date('Y-m-d',strtotime( $codetoday . ' next week' . date('Y-m-d', strtotime(sprintf($wkDayF. ' +%d days',$oemFixedDay2 - 1)))));

                    $settlementDate = $mdlbc->getPrevBusinessDate($settlementDate);

                    if(in_array($settlementDate, $end_stack)){
                        //skip
                    }else{
                        // カレンダーの備考に「OEM精算日」を設定
                        $mdlbc -> setNote(array(
                            'message' => 'OEM精算日',
                            'userId' => $userId,
                            'date' => $settlementDate
                        ));
                        array_push($end_stack, $settlementDate);
                    }
                }
                if (isset($settlementDay3) && $settlementDay3 > 0)
                {
                    $codetoday = '';
                    if($settlementDay3==41)$codetoday = "Monday";
                    if($settlementDay3==42)$codetoday = "Tuesday";
                    if($settlementDay3==43)$codetoday = "Wednesday";
                    if($settlementDay3==44)$codetoday = "Thursday";
                    if($settlementDay3==45)$codetoday = "Friday";
                    $settlementDate = date('Y-m-d',strtotime( $codetoday . ' next week' . date('Y-m-d', strtotime(sprintf($wkDayF. ' +%d days',$oemFixedDay3 - 1)))));

                    $settlementDate = $mdlbc->getPrevBusinessDate($settlementDate);

                    if(in_array($settlementDate, $end_stack)){
                        //skip
                    }else{
                        // カレンダーの備考に「OEM精算日」を設定
                        $mdlbc -> setNote(array(
                            'message' => 'OEM精算日',
                            'userId' => $userId,
                            'date' => $settlementDate
                        ));
                        array_push($end_stack, $settlementDate);
                    }
                }
            }

            // -----------------------------------------------------------------
            // 加盟店精算日(曜日指定)を抽出
            // -----------------------------------------------------------------
            $sql = <<<EOQ
SELECT	DISTINCT PayingDay
FROM	M_PayingCycle
WHERE	PayingDecisionClass = 0
AND		ValidFlg = 1
GROUP BY PayingDay
ORDER BY PayingDay;
EOQ;
            $ri = $this-> _adapter->query($sql)->execute(null);

            $weekDayJ = array(
                    0 => '日',
                    1 => '月',
                    2 => '火',
                    3 => '水',
                    4 => '木',
                    5 => '金',
                    6 => '土'
            );

            foreach($ri as $row)
            {
                $payingDay = $row['PayingDay'];
                if (isset($payingDay) && $payingDay >= 0 && $payingDay <=  6)
                {
                    $wkMessage = '加盟店精算日(毎週'. (isset($weekDayJ[$payingDay]) ? $weekDayJ[$payingDay] : '　'). '曜日)';
                    foreach($mdlbc-> getDateByWeekDay($payingDay, $wkDayF, $wkDayT) as $wkBusinessDate)
                    {
                        // 稼働日判定
                        $wkBusinessDate = $mdlbc->getNextBusinessDate($wkBusinessDate);

                        // カレンダーの備考に「加盟店精算日(毎週○曜日)」を設定
                        $mdlbc -> setNote(array(
                                'message' => $wkMessage,
                                'userId' => $userId,
                                'date' => $wkBusinessDate
                        ));
                    }
                }
            }

            // -----------------------------------------------------------------
            // 加盟店精算日(日付指定)を抽出
            // -----------------------------------------------------------------
            $sql =<<<EOQ
SELECT  DISTINCT PayingDecisionDate
FROM
(SELECT PayingDecisionDate1 PayingDecisionDate
FROM	M_PayingCycle
WHERE	PayingDecisionDate1 is not NULL
AND		ValidFlg = 1
UNION ALL
SELECT  PayingDecisionDate2 PayingDecisionDate
FROM	M_PayingCycle
WHERE	PayingDecisionDate2 is not NULL
AND		ValidFlg = 1
UNION ALL
SELECT  PayingDecisionDate3 PayingDecisionDate
FROM	M_PayingCycle
WHERE	PayingDecisionDate3 is not NULL
AND		ValidFlg = 1) as T
GROUP BY PayingDecisionDate
ORDER BY PayingDecisionDate;
EOQ;

            $ri = $this->_adapter->query($sql)->execute(null);

            foreach($ri as $row)
            {
                $payingDecisionDate = null;
                $payingDecisionDay = $row['PayingDecisionDate'];
                if (isset($payingDecisionDay) && $payingDecisionDay > 0)
                {
                    if (($payingDecisionDay == 99) || ($daysOfMonth < $payingDecisionDay))
                    {
                        //加盟店精算日=99の場合、月末日を設定
                        $payingDecisionDate = $wkDayT;
                    }
                    else
                    {
                        //上記以外の場合、開始日の年月+加盟店精算日
                        $payingDecisionDate = date('Y-m-d', strtotime(sprintf($wkDayF. ' +%d days',$payingDecisionDay - 1)));
                    }
                    $wkMessage = '加盟店精算日(毎月'. sprintf('%02d', $payingDecisionDay) . '日)';

                    // 稼働日判定
                    $payingDecisionDate = $mdlbc->getNextBusinessDate($payingDecisionDate);

                    // カレンダーの備考に「OEM精算日」を設定
                    $mdlbc -> setNote(array(
                            'message' => $wkMessage,
                            'userId' => $userId,
                            'date' => $payingDecisionDate
                    ));
                }
            }

            $this->_adapter->getDriver ()->getConnection ()->commit ();
            return "";
        } catch ( \Exception $e ) {
            $this->_adapter->getDriver ()->getConnection ()->rollBack ();
            throw $e;
        }
    }
}
