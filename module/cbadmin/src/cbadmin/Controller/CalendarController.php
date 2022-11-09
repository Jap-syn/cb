<?php
namespace cbadmin\Controller;

use Coral\Coral\Controller\CoralControllerAction;
use Coral\Coral\CoralCodeMaster;
use cbadmin\Application;
use models\Table\TableBusinessCalendar;
use models\Table\TableCreditTransfer;
use models\Table\TableCreditTransferCalendar;
use models\Table\TableUser;

class CalendarController extends CoralControllerAction
{
	protected $_componentRoot = './application/views/components';

	/**
	 * アプリケーションオブジェクト
	 * @var Application
	 */
	private $app;

	/**
	 * Controllerを初期化する
	 */
	public function _init()
	{
        $this->app = Application::getInstance();

        $userInfo = $this->app->authManagerAdmin->getUserInfo();
        $this->view->assign('userInfo', $userInfo );

        $this->addStyleSheet('../css/default02.css');

        $this->setPageTitle("後払い.com - カレンダーメンテナンス");
	}

	/*
	 * 指定年月のカレンダーフォームを表示する。
	 */
	public function indexAction()
	{
        $mdl = new TableBusinessCalendar($this->app->dbAdapter);
        $mdlctc = new TableCreditTransferCalendar ( $this->app->dbAdapter );
        $mdlct = new TableCreditTransfer ( $this->app->dbAdapter );

        // 更新パラメーターがセットされていれば更新する。
        $upDatas = $this->getRequest()->getPost()->toArray();

        // ユーザーIDの取得
        $mdluser = new TableUser($this->app->dbAdapter);
        $updateId = $mdluser->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

        $i = 1;
        while(isset($upDatas['BusinessDate' . $i]))
        {
            if (array_key_exists("BusinessFlg" . $i, $upDatas) && $upDatas["BusinessFlg" . $i] == "on")
            {
                $up['BusinessFlg'] = 1;
            }
            else
            {
                $up['BusinessFlg'] = 0;
            }
            if (array_key_exists("ToyoBusinessFlg" . $i, $upDatas) && $upDatas["ToyoBusinessFlg" . $i] == "on")
            {
                $up['ToyoBusinessFlg'] = 1;
            }
            else
            {
                $up['ToyoBusinessFlg'] = 0;
            }
            $up['Label'] = $upDatas['Label' . $i];
            $up['UpdateId'] = $updateId;
            $mdl->saveUpdate($up, $upDatas['BusinessDate' . $i]);

            $i++;
        }

        // 口座振替カレンダ登録
        $ct_datas = ResultInterfaceToArray($mdlct->getAll());
        $i = 1;
        while(isset($upDatas['BusinessDate' . $i]))
        {
            foreach($ct_datas as $val) {
                if (array_key_exists("ExecFlg_" . $val['CreditTransferId'] . '_1_' . $i, $upDatas) && $upDatas["ExecFlg_" . $val['CreditTransferId'] . '_1_' . $i] == "on")
                {
                    $up['ExecFlg'] = 1;
                }
                else
                {
                    $up['ExecFlg'] = 0;
                }
                $up['UpdateId'] = $updateId;
                $mdlctc->saveUpdate($up, $upDatas['BusinessDate' . $i], $val['CreditTransferId'], 1);
                if (array_key_exists("ExecFlg_" . $val['CreditTransferId'] . '_2_' . $i, $upDatas) && $upDatas["ExecFlg_" . $val['CreditTransferId'] . '_2_' . $i] == "on")
                {
                    $up['ExecFlg'] = 1;
                }
                else
                {
                    $up['ExecFlg'] = 0;
                }
                $up['UpdateId'] = $updateId;
                $mdlctc->saveUpdate($up, $upDatas['BusinessDate' . $i], $val['CreditTransferId'], 2);
            }
            $i++;
        }

        $prm_get = $this->params()->fromRoute();
        $year  = (isset($prm_get['y'])) ? $prm_get['y'] : date('Y');
        $month = (isset($prm_get['m'])) ? $prm_get['m'] : date('n');

        $ym = $year.'-'.$month;
        $date = date('Y-n', strtotime($ym));
        $bfrmonths = date('Y-n', strtotime($ym . " -1 Month"));
        $nxtmonths = date('Y-n', strtotime($ym . " +1 Month"));
        $bfryear = date('Y', strtotime($bfrmonths));
        $bfrmonth = date('n', strtotime($bfrmonths));
        $nxtyear = date('Y', strtotime($nxtmonths));
        $nxtmonth = date('n', strtotime($nxtmonths));

        //表示する月のデータを取得する。
        $datas = $mdl->getMonthCalendar($year, $month);

        //先々月・再来月のデータ数を取得する。
        $bfrdatas = $mdl->getMonthCalendar($bfryear, $bfrmonth)->count();
        $nxtdatas = $mdl->getMonthCalendar($nxtyear, $nxtmonth)->count();

        $prevDate = date("$year-$month-1");
        $prevDate = date("Y-n-1", strtotime($prevDate . " -1 Month"));
        $nextDate = date("$year-$month-1");
        $nextDate = date("Y-n-1", strtotime($nextDate . " +1 Month"));

        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);

        // 口座振替カレンダ取得＆表示設定
        $ct_cal = array();
        foreach ($ct_datas as $ct_data) {
            $key = $ct_data['CreditTransferId'];
            $ct_cal[$key] = array();
            $ct_cal[$key][1] = ResultInterfaceToArray($mdlctc->getMonthCalendar($year, $month, $key, 1));
            $ct_cal[$key][2] = ResultInterfaceToArray($mdlctc->getMonthCalendar($year, $month, $key, 2));
            $ct_cal[$key]['name'] = $ct_data['CreditTransferName'];
        }

        $this->view->assign("nextpage", sprintf("calendar/index/y/%d/m/%d", date("Y", strtotime($nextDate)), date("n", strtotime($nextDate))));
        $this->view->assign("prevpage", sprintf("calendar/index/y/%d/m/%d", date("Y", strtotime($prevDate)), date("n", strtotime($prevDate))));
        $this->view->assign("year", $year);
        $this->view->assign("month", $month);
        $this->view->assign("codeMaster", $codeMaster);
        $this->view->assign("datas", $datas);
        $this->view->assign("bfrdatas", $bfrdatas);
        $this->view->assign("nxtdatas", $nxtdatas);
        $this->view->assign("ct_cals", $ct_cal);

        return $this->view;
	}
}

