<?php
namespace cbadmin\Controller;

use Coral\Coral\Controller\CoralControllerAction;
use models\Table\TableCreditJudgeThreshold;
use Zend\Db\Adapter\Adapter;
use cbadmin\Application;
use Zend\Captcha\Dumb;

class CreditvalueController extends CoralControllerAction{
    protected $_componentRoot = './application/views/components';

    /**
     * アプリケーションオブジェクト
     * @var Application
     */
    private $app;

    /**
     * Controllerを初期化する
     */
    public function _init(){
        $this->app = Application::getInstance();
        $userInfo = $this->app->authManagerAdmin->getUserInfo();
        $this->view->assign('userInfo', $userInfo);

        $this->addStyleSheet('../css/default02.css')
             ->addJavaScript( '../js/prototype.js' );

        $this->setPageTitle("後払い.com - 与信閾値設定");
    }

    /* 与信閾値管理画面
     *
    */
    public function indexAction()
    {
        //与信閾値テーブルからデータ取得
        $credit_judge_threshold = new TableCreditJudgeThreshold($this->app->dbAdapter);

        $datas = $credit_judge_threshold->find()->current();
        $this->view->assign("datas", $datas);
        $this->view->assign("msg","");

        return $this->view;
    }
    /* 与信閾値保存
     *
    */
    public function saveAction(){
        //POSTデータ取得
        $data = $this->getRequest()->getPost()->toArray();

        $credit_judge_threshold = new TableCreditJudgeThreshold($this->app->dbAdapter);
        //チェックモデル
        $check_model = array("int","int","int");

        //エラー時の日本語名
        $string_name = array("注文利用額","審査システム保留上限","審査システム保留下限");

        //入力チェック
        $validation_check = $credit_judge_threshold->validation_check($data,$check_model,$string_name);

        //エラーの場合は再度読み込み
        if(!$validation_check[0]){
            $this->view->assign("msg", $validation_check[1]."が数値ではありません");
        }else{

            //上限値と下限値が逆でないか確認
            if($data['JudgeSystemHoldMAX'] < $data['JudgeSystemHoldMIN']){
                //エラーメッセージ取得
                $this->view->assign("msg", "審査システム保留上限値が審査システム保留下限値より小さくなっています");
            }elseif($data['JudgeSystemHoldMAX'] == $data['JudgeSystemHoldMIN']){

                //上限値と下限値が同じだったらエラー
                $this->view->assign("msg", "審査システム保留上限値と審査システム保留下限値が同じ値になっています");

            }else{
                //データ更新
                $obj = new \models\Table\TableUser($this->app->dbAdapter);
                $data['UpdateId'] = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);
                $credit_judge_threshold->saveUpdate($data, $data['UserAmountOver']);

                //数値型に変換
                $data = array("UserAmountOver" => intval($data['UserAmountOver']),
                        "JudgeSystemHoldMAX" => intval($data['JudgeSystemHoldMAX']),"JudgeSystemHoldMIN" => intval($data['JudgeSystemHoldMIN']));

                $this->view->assign("msg","更新成功");
            }
        }

        $this->view->assign("datas", $data);
        $this->setTemplate('index');
        return $this->view;
    }
}

