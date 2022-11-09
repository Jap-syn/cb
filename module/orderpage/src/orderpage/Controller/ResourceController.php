<?php
namespace orderpage\Controller;

use orderpage\Application;
use Coral\Coral\Controller\CoralControllerOemResourceAction;
use models\View\MypageViewSystemProperty;
use models\View\MypageViewOem;

/**
 * OEM固有リソースを取得するためのコントローラ
 */
class ResourceController extends CoralControllerOemResourceAction {
    /**
     * IndexControllerを初期化する
     */
    public function _init() {
        $this->app = Application::getInstance();

        // アクションごとに認証なしでのアクセスを禁止するリソース名を定義
        $this->_need_auth_resources = array(
                'image' => array('Imprint')
        );
    }

    /**
     * いま現在認証済みであるかを判断する
     * @return boolean
     */
    public function isAuthenticated() {
        return $this->app->authManager->isAuthenticated();
    }

    /**
     *  cssリソース取得アクション
     */
    public function cssAction(){

        //OEMの情報取得
        $info = $this->_getOemInfo();

        $sysDataValue = '';

        if(isset($info)) {
            //T_SystemPropertyからデータ取得
            $sysProps = new MypageViewSystemProperty($this->app->dbAdapter);
            $sysDataValue = $sysProps->getMemberRulesByOemId($info['OemId']);
        }

       $res = $this->getResponse();
       $res->getHeaders()->addHeaderLine( 'Content-Type', 'text/css' );
       $res->getHeaders()->addHeaderLine( 'Content-Length', strlen($sysDataValue) );
       echo $sysDataValue;

       return $this->getResponse();
    }

    /**
     * (Override)現在ログイン中アカウントに関連付けられたOEM情報を取得する
     *
     * @access protected
     * @return array
     */
    protected function _getOemInfo() {
        $accId = $this->app->getOemAccessId();
        $table = new MypageViewOem($this->app->dbAdapter);
        $row = $table->findByAccessId($accId)->current();
        return ($row) ? $row : array();
    }
}
