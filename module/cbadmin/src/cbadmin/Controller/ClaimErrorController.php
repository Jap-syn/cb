<?php
namespace cbadmin\Controller;

use cbadmin\Application;
use Coral\Coral\Controller\CoralControllerAction;
use Coral\Base\BaseGeneralUtils;
use Coral\Base\BaseUtility;
use models\Logic\Exception\LogicClaimException;
use Coral\Base\BaseHtmlUtils;

class ClaimErrorController extends CoralControllerAction
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

        $this->setPageTitle("後払い.com - 請求バッチエラーリスト");
    }

    /**
     * 請求バッチエラーリスト画面を表示する。
     */
    public function listAction()
    {
        // パラメーター取得 ------------------------------------------------------------------------------------------>
        $params = $this->getParams();
        $params['RegistDateF'] = isset($params['RegistDateF']) ? $params['RegistDateF'] : date("Y-m-d", strtotime("-1 day")); // 発生日FROM(未設定の場合は前日)
        $params['RegistDateT'] = isset($params['RegistDateT']) ? $params['RegistDateT'] : date("Y-m-d"); // 発生日FROM(未設定の場合は当日)
        $params['ErrorCode'] = isset($params['ErrorCode']) ? $params['ErrorCode'] : -1; // エラー種別
        $params['OrderId'] = isset($params['OrderId']) ? $params['OrderId'] : ''; // 注文ID
        $params['LoginId'] = isset($params['LoginId']) ? $params['LoginId'] : ''; // 事業者ID

        // SQL構築 --------------------------------------------------------------------------------------------------->
        $sql  = ' SELECT ce.RegistDate ';
        $sql .= '       ,o.OrderId ';
        $sql .= '       ,e.EnterpriseNameKj ';
        $sql .= '       ,c.NameKj ';
        $sql .= '       ,ce.ErrorCode ';
        $sql .= '       ,ce.ErrorMsg ';
        $sql .= '       ,ce.OrderSeq ';
        $sql .= '       ,c.CustomerId ';
        $sql .= ' FROM   T_ClaimError ce ';
        $sql .= '        INNER JOIN T_Order o ';
        $sql .= '                ON ce.OrderSeq = o.OrderSeq ';
        $sql .= '        INNER JOIN T_Customer c ';
        $sql .= '                ON ce.OrderSeq = c.OrderSeq ';
        $sql .= '        INNER JOIN T_Enterprise e ';
        $sql .= '                ON o.EnterpriseId = e.EnterpriseId ';
        $sql .= ' WHERE  1 = 1 ';

        // 発生日時
        $wRegistDate = BaseGeneralUtils::makeWhereDateTime(
            'ce.RegistDate',
            BaseGeneralUtils::convertWideToNarrow($params['RegistDateF']),
            BaseGeneralUtils::convertWideToNarrow($params['RegistDateT'])
        );
        if ($wRegistDate != '')
        {
            $sql .= " AND " . $wRegistDate;
        }

        // エラー種別
        if ((int)$params['ErrorCode'] >= 0) {
            $sql .= " AND ce.ErrorCode = " . (int)$params['ErrorCode'];
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

        $sql .= ' ORDER BY ce.Seq ASC ';

        // SQL実行
        $datas = ResultInterfaceToArray($this->app->dbAdapter->query($sql)->execute(null));

        // 検索結果を画面表示用に調整 -------------------------------------------------------------------------------->
        // count関数対策
        $datasCount = 0;	
        if (!empty($datas)) {
            $datasCount = count($datas);
        }

        for ($i = 0; $i < $datasCount; $i++){
            $datas[$i]['Note'] = '';

            // エラーコードをもとにエラーメッセージを構築する
            $errCode = $datas[$i]['ErrorCode'];
            if ($errCode == LogicClaimException::ERR_CODE_SMBC) {
                // SMBC連携エラー
                $datas[$i]['Note'] = sprintf('SMBC連携エラー(%s)', $datas[$i]['ErrorMsg']);
            } elseif ($errCode == LogicClaimException::ERR_CODE_0YEN) {
                // 利用額が０円エラー
                $datas[$i]['Note'] = '請求額が０円のため、請求データが作成されませんでした。';
            } elseif ($errCode == LogicClaimException::ERR_CODE_LIMIT_DAY) {
                // 支払期限エラー
                $datas[$i]['Note'] = sprintf('支払期限が%s日未満となるため、請求データが作成されませんでした。', $datas[$i]['ErrorMsg']);
            } elseif ($errCode == LogicClaimException::ERR_CODE_FORCE_CANCEL_DATE) {
                // 強制解約日エラー
                $datas[$i]['Note'] = sprintf('強制解約日の日付(%s)が不正のため、請求データが作成されませんでした。', $datas[$i]['ErrorMsg']);
            } else {
                $datas[$i]['Note'] = $datas[$i]['ErrorMsg'];
            }
        }

        // ビューにアサイン ------------------------------------------------------------------------------------------>
        $this->view->assign('list', $datas);
        $this->view->assign('condition', $params);
        $this->view->assign('errorCodeTag',BaseHtmlUtils::SelectTag("ErrorCode", array(-1 => '----------', 1 => 'SMBC連携エラー', 2 => '０円請求エラー', 3 => '支払期限エラー', 4 => 'ペイジー連携エラー', 5 => '強制解約日エラー', 6 => '印刷パターンエラー', 7 => '支払方法チェックエラー'), (int)$params['ErrorCode']));

        return $this->view;
    }
}