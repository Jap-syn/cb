<?php
namespace cbadmin\Controller;

use cbadmin\Application;
use Coral\Coral\Controller\CoralControllerAction;
use Coral\Base\BaseGeneralUtils;
use Coral\Base\BaseUtility;
use models\Logic\Exception\LogicClaimException;
use Coral\Base\BaseHtmlUtils;

class MufjErrorController extends CoralControllerAction
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

        $this->setPageTitle("後払い.com - MUFJ入金エラーリスト");
    }

    /**
     * 請求バッチエラーリスト画面を表示する。
     */
    public function listAction()
    {
        // パラメーター取得 ------------------------------------------------------------------------------------------>
        $params = $this->getParams();
        // 発生日FROM(未設定の場合は前日)
        $params['RegistDateF'] = isset($params['RegistDateF']) ? $params['RegistDateF'] : date("Y-m-d", strtotime("-1 day"));
        // 発生日FROM(未設定の場合は当日)
        $params['RegistDateT'] = isset($params['RegistDateT']) ? $params['RegistDateT'] : date("Y-m-d");
        // エラー種別
        $params['ErrorCode'] = isset($params['ErrorCode']) ? $params['ErrorCode'] : -1;
        // 注文ID
        $params['OrderId'] = isset($params['OrderId']) ? $params['OrderId'] : '';
        // 事業者ID
        $params['LoginId'] = isset($params['LoginId']) ? $params['LoginId'] : '';

        // SQL構築 --------------------------------------------------------------------------------------------------->
        $sql  = 'SELECT mr.RegistDate ';
        $sql .= ', o.OrderId ';
        $sql .= ', e.EnterpriseNameKj ';
        $sql .= ', c.NameKj ';
        $sql .= ', mr.Note ';
        $sql .= ', mr.OrderSeq ';
        $sql .= ', mr.PaymentAmount ';
        $sql .= ', c.CustomerId ';
        $sql .= 'FROM  T_MufjReceipt AS mr ';
        $sql .= 'LEFT  JOIN T_Order      AS o ON mr.OrderSeq = o.OrderSeq ';
        $sql .= 'LEFT  JOIN T_Customer   AS c ON mr.OrderSeq = c.OrderSeq ';
        $sql .= 'LEFT  JOIN T_Enterprise AS e ON o.EnterpriseId = e.EnterpriseId ';
        $sql .= 'WHERE 1 = 1 ';
        $sql .= 'AND   mr.ValidFlg = 1 ';
        $sql .= 'AND   mr.ProcessClass = 1 ';
        $sql .= 'AND   mr.ErrorFlg = 1 ';

        // 発生日時
        $wRegistDate = BaseGeneralUtils::makeWhereDateTime(
            'mr.RegistDate',
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

        $sql .= ' ORDER BY mr.Seq ASC ';

        // SQL実行
        $datas = ResultInterfaceToArray($this->app->dbAdapter->query($sql)->execute(null));

        // ビューにアサイン ------------------------------------------------------------------------------------------>
        $this->view->assign('list', $datas);
        $this->view->assign('condition', $params);

        return $this->view;
    }
}