<?php
namespace cbadmin\Controller;

use cbadmin\Application;
use Zend\Config\Reader\Ini;
use Coral\Coral\Controller\CoralControllerAction;
use Coral\Base\BaseLog;
use models\Table\TableUser;
use models\Logic\CreditJudge\LogicCreditJudgeSequencer;
use models\Logic\CreditJudge\LogicCreditJudgeAbstract;

class TestController extends CoralControllerAction
{
    protected $_componentRoot = './application/views/components';

    /**
     * アプリケーションオブジェクト
     * @var Application
     */
    private $app;

    /**
     * Controllerを初期化する。
     */
    protected function _init()
    {
        $this->app = Application::getInstance();

        $this->addStyleSheet('../css/default02.css');

        $this->setPageTitle("テストフォーム");
    }

    /**
     * テストフォーム
     *
     */
    public function testformAction()
    {
        return $this->view;
    }

    /**
     * 与信バッチテスト実行
     */
    public function creditAction()
    {
        $configPath = 'C:/pleiades/xampp/htdocs/cb/module/cbadmin/config/config.ini';
        if (file_exists($configPath))
        {
            $reader = new Ini();
            $data = $reader->fromFile($configPath);
        }

        // 設定をシステムプロパティテーブルから読み込み
        $apinfo = $this->app->getApplicationiInfo($this->app->dbAdapter, 'cbadmin');
        // iniファイルの内容をマージ
        $data = array_merge($data, $apinfo);

        // ログ設定の読み込み
        $logConfig = $data['log'];
        // 標準ログクラス初期化
        $logger = BaseLog::createFromArray( $logConfig );

        $mdluser = new TableUser($this->app->dbAdapter);
        $userId = $mdluser->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

        $params = $this->getParams();

        $errors = array();

        LogicCreditJudgeAbstract::getDefaultLogger($logger);
        LogicCreditJudgeSequencer::setDefaultConfig($data);
        LogicCreditJudgeSequencer::setUserId($userId);

        if (isset($params['OrderSeq']) && (! empty($params['OrderSeq']))) {
            $logic = new LogicCreditJudgeSequencer($this->app->dbAdapter);
            // 該当の注文を与信へかける
            $result = $logic->doJudgementForApi($params['OrderSeq']);
        } else {
            $errors['OrderSeq'] = '注文Seqを入力してください';
        }

        $this->view->assign('result', $result);
        $this->view->assign('error', $errors);

        $this->setTemplate('testform');
        return $this->view;
    }

    /**
     * 請求SPテスト実行
     */
    public function claimAction()
    {
        $params = $this->getParams();

        // 未入力の項目がないか確認
        if ( ! (isset($params['OrderSeq']) && (! empty($params['OrderSeq']))) ) {
            $errors['OrderSeq'] = '注文Seqを入力してください';
        }

        if ( ! (isset($params['ClaimAmount']) && (! empty($params['ClaimAmount']))) ) {
            $errors['ClaimAmount'] = '請求金額を入力してください';
        }

        if ( ! (isset($params['ReClaimFee']) && (! empty($params['ReClaimFee']))) ) {
            $errors['ReClaimFee'] = '再請求手数料を入力してください';
        }
        // エラーの場合
        if (isset($errors) && (! empty($errors))) {
            $this->view->assign('error', $errors);

            $this->setTemplate('testform');
            return $this->view;
        }

        // ユーザーIDの取得
        $obj = new \models\Table\TableUser($this->app->dbAdapter);
        $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

        // 請求関連処理SQL
        $stm = $this->app->dbAdapter->query($this->getBaseP_ClaimControl());

        // SQL実行結果取得用のSQL
        $getretvalsql = " SELECT @po_ret_sts AS po_ret_sts, @po_ret_errcd AS po_ret_errcd, @po_ret_sqlcd AS po_ret_sqlcd, @po_ret_msg AS po_ret_msg ";

        $prm = array(
                ':pi_use_amount'       => (int)$params['ClaimAmount'],
                ':pi_order_seq'        => $params['OrderSeq'],
                ':pi_limit_day'        => date('Y-m-d'),
                ':pi_limit_claim_day1' => 10,
                ':pi_limit_claim_day2' => 14,
                ':pi_button_flg'       => 1,
                ':pi_damage_days'      => (int)$params['DamageDays'],
                ':pi_reclaim_fee'      => (int)$params['ReClaimFee'],
                ':pi_claim_pattern'    => $params['ClaimPattern'],
                ':pi_damage_amount'    => (int)$params['DamageAmount'],
                ':pi_user_id'          => $userId,
        );

        try {
            // トランザクション開始
            $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
            $ri = $stm->execute($prm);

            // SQL実行例外なしもエラー戻り値の時は例外をｽﾛｰ
            $retval = $this->app->dbAdapter->query($getretvalsql)->execute(null)->current();
            if ($retval['po_ret_sts'] != 0) {
                throw new \Exception($retval['po_ret_msg']);
            }

            $result2 = '正常終了：' . $retval['po_ret_msg'];
            $this->app->dbAdapter->getDriver()->getConnection()->rollback();

        } catch (\Exception $e) {
            $this->app->dbAdapter->getDriver()->getConnection()->rollback();
            $result2 = '異常終了：' . $e;
        }


        $this->view->assign('result2', $result2);

        $this->setTemplate('testform');
        return $this->view;
    }

    /**
     * 請求関連処理ファンクションの基礎SQL取得。
     *
     * @return 請求関連処理ファンクションの基礎SQL
     */
    protected function getBaseP_ClaimControl() {
        return <<<EOQ
            CALL P_ClaimControl(
                :pi_use_amount
            ,   :pi_order_seq
            ,   :pi_limit_day
            ,   :pi_limit_claim_day1
            ,   :pi_limit_claim_day2
            ,   :pi_button_flg
            ,   :pi_damage_days
            ,   :pi_reclaim_fee
            ,   :pi_claim_pattern
            ,   :pi_damage_amount
            ,   :pi_user_id
            ,   @po_ret_sts
            ,   @po_ret_errcd
            ,   @po_ret_sqlcd
            ,   @po_ret_msg
                )
EOQ;
    }
}
