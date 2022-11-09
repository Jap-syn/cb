<?php
namespace oemmember\Controller;

use oemmember\Application;
use Coral\Coral\Controller\CoralControllerAction;
use Coral\Base\Auth\BaseAuthManager;
use Zend\Config\Reader\Ini;
use models\Table\TableOem;

class IndexController extends CoralControllerAction {
    protected $_componentRoot = './application/views/components';

    protected function _init() {
        $app = Application::getInstance();

        $db = $app->dbAdapter;

        $this->addStyleSheet( '../../oemmember/css/members.css' )
            ->addStyleSheet( '../../oemmember/css/index.css' )
            ->addJavaScript( '../../js/prototype.js' )
            ->addJavaScript( '../../js/bytefx.js' )
            ->addJavaScript( '../../js/json+.js' )
            ->addJavaScript( '../../js/corelib.js' )
            ->addJavaScript( '../../js/base.ui.js' );

        // メニュー情報をマスタから取得
        $menu_info = $app->getMenuInfo();

        foreach( $menu_info as $key => $info ) {
            $this->view->assign( $key, $info );
       }

        $this->userInfo = Application::getInstance()->authManager->getUserInfo();
        $this->altUserInfo = Application::getInstance()->authManager->getAlternativeUserInfo();

        if(is_null($this->userInfo->OemId) || $this->userInfo->OemId == 0){
            $obj = new \models\Table\TableCode( $app->dbAdapter );
            $info_row = $obj->getMasterDescription(5, 2);
        	$notice = $info_row['Description'];
		}else{
			$oemMdl = new TableOem( $app->dbAdapter );
			$info_row = $oemMdl->find( $this->userInfo->OemId )->current();
			$notice = $info_row['Notice'];
		}

        $this->view->assign( 'general_information', $notice );
        $this->view->assign( 'show_submessage', true );
        $this->view->assign( 'cssName', "index" );
    }

    public function indexAction() {
        $userInfo = $this->altUserInfo ?
            sprintf('%s (%s)　様', $this->userInfo->EnterpriseNameKj, $this->altUserInfo->NameKj) :
            sprintf('%s　様', $this->userInfo->EnterpriseNameKj);
        $this->setPageTitle($userInfo);

        // ログイン直後か判断するためのセッションを取得
        $afterLogin = isset($_SESSION['SESS_AFTER_LOGIN']) ? $_SESSION['SESS_AFTER_LOGIN'] : 0;
        unset($_SESSION['SESS_AFTER_LOGIN']);

        // アラート注文リストが存在するか確認
        $sql = ' SELECT COUNT(1) AS cnt FROM T_Order o, AT_Order ao WHERE o.OrderSeq = ao.OrderSeq AND ao.DefectFlg = 1 AND ao.DefectInvisibleFlg = 0 AND o.Cnl_Status = 0 AND o.DataStatus < 31 AND o.EnterpriseId = :EnterpriseId ';
        $defectCnt = Application::getInstance()->dbAdapter->query($sql)->execute(array(':EnterpriseId' => $this->userInfo->EnterpriseId))->current()['cnt'];

        $this->view->assign('afterlogin', $afterLogin);
        $this->view->assign('defectcnt', $defectCnt);
        return $this->view;
    }

    public function downloadAction() {
        $this->view->assign( 'show_submessage', false );
        $this->setPageTitle( 'サンプルファイルのダウンロード' );

        return $this->view;
    }
}
