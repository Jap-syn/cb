<?php
namespace cbadmin\Controller;

use cbadmin\Application;
use Coral\Base\BaseGeneralUtils;
use Coral\Base\BaseUtility;
use Coral\Base\BaseHtmlUtils;
use Coral\Coral\Controller\CoralControllerAction;
use DateTime;
use models\Logic\Jnb\LogicJnbAccount;
use models\Logic\LogicCancel;
use models\Table\TableSBPaymentSendResultHistory;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Json\Json;

class SBPSCancelApiErrorController extends CoralControllerAction
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
        $this->view->assign('userInfo', $this->app->authManagerAdmin->getUserInfo());

        $this->addStyleSheet('../css/default02.css')
        ->addJavaScript('../js/prototype.js');

        $this->setPageTitle("後払い.com - SBPSCancelApiエラー確認");
    }

    /**
     * キャンセルエラーリストを表示する。
     */
    public function listAction()
    {
        // システム日付を取得する
        $dateTime = new DateTime();
        $tDate = $dateTime->format('Y-m-d');
        $dateTime->modify('-1 day');
        $fDate = $dateTime->format('Y-m-d');

        // パラメーター取得 ------------------------------------------------------------------------------------------>
        $params = $this->getParams();
        $params['RegistDateF'] = isset($params['RegistDateF']) ? $params['RegistDateF'] : $fDate; // 発生日FROM(未設定の場合は前日)
        $params['RegistDateT'] = isset($params['RegistDateT']) ? $params['RegistDateT'] : $tDate; // 発生日FROM(未設定の場合は当日)
        $params['OrderId'] = isset($params['OrderId']) ? $params['OrderId'] : ''; // 注文ID
        $params['LoginId'] = isset($params['LoginId']) ? $params['LoginId'] : ''; // 事業者ID

        // SQL構築 --------------------------------------------------------------------------------------------------->
        $sql = '';
        $sql .= 'SELECT ';
        $sql .= ' sbp.OrderSeq';
        $sql .= ', sbp.OrderId';
        $sql .= ', sbp.RegistDate AS ResDate';
        $sql .= ', sbp.ResErrCode';
        $sql .= ', sbp.ErrorMessage';
        $sql .= ' FROM T_SBPaymentSendResultHistory AS sbp';
        $sql .= ' INNER JOIN T_Order AS o ON o.OrderSeq = sbp.OrderSeq ';
        $sql .= ' INNER JOIN T_Enterprise AS e ON e.EnterpriseId = o.EnterpriseId ';
        $sql .= " WHERE ResResult = 'NG' ";

        // 発生日時
        $wRegistDate = BaseGeneralUtils::makeWhereDateTime(
            'sbp.RegistDate',
            BaseGeneralUtils::convertWideToNarrow($params['RegistDateF']),
            BaseGeneralUtils::convertWideToNarrow($params['RegistDateT'])
        );
        if ($wRegistDate != '')
        {
            $sql .= " AND " . $wRegistDate;
        }

        // 注文ID
        if ($params['OrderId'] != '')
        {
            $sql .= " AND o.ReverseOrderId LIKE '" . mb_convert_kana(BaseUtility::escapeWildcard(strrev($params['OrderId'])), 'a', 'UTF-8')  . "%' ";// 反転した注文ID、を検索(インデックス検索)
        }

        // 事業者ID
        if ($params['LoginId'] != '')
        {
            $sql .= " AND e.LoginId LIKE '%" . BaseUtility::escapeWildcard($params['LoginId']) . "' ";
        }

        $sql .= ' ORDER BY ';
        $sql .= ' sbp.RegistDate DESC';
        $sql .= ', sbp.OrderSeq';

        $errList = ResultInterfaceToArray($this->app->dbAdapter->query( $sql )->execute());

        $lgCancel = new LogicCancel($this->app->dbAdapter);
        foreach ($errList as $key => $value) {
            if (!empty($value["ResErrCode"])) {
                $temp = $lgCancel->_SBPaymentMakeErrorInfo($value["ResErrCode"]);
                $value["ErrorMessage"] =  str_replace(PHP_EOL, '/', $temp );
                $errList[$key] = $value;
            }
        }
        $this->view->assign('list', $errList);
        $this->view->assign('condition', $params);

        return $this->view;
    }
}
