<?php
namespace oemadmin\Controller;

use oemadmin\Application;
use Coral\Coral\Controller\CoralControllerOemResourceAction;

/**
 * OEM固有リソースを取得するためのコントローラ
 */
class ResourceController extends CoralControllerOemResourceAction {
    /**
     * Controllerを初期化する
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
        return $this->app->authManagerAdmin->isAuthenticated();
    }
}
