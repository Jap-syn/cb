<?php
namespace cbadmin\Controller;

use Coral\Coral\Controller\CoralControllerAction;
use cbadmin\Application;
use models\Logic\Smbcpa\LogicSmbcpaConfig;
use models\Logic\Smbcpa\Account\Receipt\LogicSmbcpaAccountReceiptManual;
use models\Table\TableUser;

/**
 * SMBCバーチャル口座手動入金コントローラ
 */
class SmbcparcptController extends CoralControllerAction {
    protected $_componentRoot = './application/views/components';
    /**
     * アプリケーションインスタンス
     *
     * @access protected
     * @var Application
     */
    protected $app;

    /**
     * SMBCバーチャル口座手動入金ロジック
     *
     * @access protected
     * @var LogicSmbcpaAccountReceiptManual
     */
    protected $_logic;

    /**
     * SMBCバーチャル口座設定
     *
     * @access protected
     * @var LogicSmbcpaConfig
     */
    protected $smbcpaConfig;

    /**
     * コントローラ初期化
     */
    protected function _init()
    {
        $this->app = Application::getInstance();

        $this->addStyleSheet('../css/default02.css');
        $this->addStyleSheet('../css/cbadmin/smbcpa/main.css');
        $this->addJavaScript('../js/prototype.js');
        $this->addJavaScript('../js/json.js');
        $this->addJavaScript('../js/bytefx.js');
        $this->addJavaScript('../js/corelib.js');

        $this->setPageTitle('後払い.com - SMBCバーチャル口座手動入金処理');

        $this->view->assign('current_action', $this->getActionName());
        $this->view->assign('userInfo', $this->app->authManagerAdmin->getUserInfo());
        $this->_logic = new LogicSmbcpaAccountReceiptManual($this->app->dbAdapter);
        $this->messenger = $this->flashMessenger();
    }

    /**
     * indexAction
     * SMBCバーチャル口座手動入金対象一覧画面
     */
    public function indexAction()
    {
        // 注文特定済み入金対象リスト
        $ri = $this->_logic->getManualReceiptTargetsWithOrderSeq();
        $this->view->assign('list1', ResultInterfaceToArray($ri));

        // 注文未特定入金対象リスト
        $ri = $this->_logic->getManualReceiptTargetsWithoutOrderSeq();
        $this->view->assign('list2', ResultInterfaceToArray($ri));

        $this->view->assign('systemMessages', $this->messenger->getMessages());

        return $this->view;
    }

    /**
     * execAction
     * 通知金額での入金確定処理を実行
     */
    public function execAction()
    {
        $params = $this->getParams();

        $nfSeq = (isset($params['nfseq']) && is_numeric($params['nfseq'])) ? $params['nfseq'] : -1;
        $data = $this->_logic->getManualReceiptTargetsByNotificationSeq($nfSeq);

        // ユーザーIDの取得
        $obj = new TableUser($this->app->dbAdapter);
        $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

        try{
            //NULLの場合はエラー
            if( is_null($data) ){
                throw new \Exception(sprintf("口座SEQ '%s' は不正な指定です", f_e($params['nfseq'])));
            }

            $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
            try {
                //手動入金確定
                $this->_logic->doneReceipt($nfSeq, $data['OrderSeq'], $data['AccountSeq'], $userId);
                $this->app->dbAdapter->getDriver()->getConnection()->commit();
            } catch(\Exception $err) {
                $this->app->dbAdapter->getDriver()->getConnection()->rollBack();
                throw $err;
            }
        }
        catch(\Exception $msg) {
            //message取得
            $error_msg = $msg->getMessage();
            //エラーメッセージセット
            $this->messenger->addMessage( $error_msg );

        }
        //リダイレクト
        return $this->_redirect( "smbcparcpt/index" );
    }

    /**
     * execbyAction
     * 指定通知の注文に対する入金画面を表示
     */
    public function execbyAction()
    {
        $params = $this->getParams();
        $nfSeq = (isset($params['nfseq']) && is_numeric($params['nfseq'])) ? $params['nfseq'] : -1;
        $data = $this->_logic->getManualReceiptTargetsByNotificationSeq($nfSeq);

        try{
            //NULLの場合はエラー
            if( is_null($data) ){
                throw new \Exception(sprintf("口座SEQ '%s' は不正な指定です", f_e($params['nfseq'])));
            }
        }
        catch(\Exception $msg) {
            //message取得
            $error_msg = $msg->getMessage();
            //エラーメッセージセット
            $this->messenger->addMessage( $error_msg );
            return $this->_redirect( "smbcparcpt/index" );
        }

        $this->view->assign('data', $data);

        return $this->view;
    }
    /**
     * execIndividualAction
     * 指定通知の注文に対する入金処理
     */
     public function execIndividualAction()
     {
         $params = $this->getParams();
         $nfSeq = (isset($params['nfseq']) && is_numeric($params['nfseq'])) ? $params['nfseq'] : -1;
         $amount = (isset($params['amount'])) ? $params['amount'] : -1;
         $data = $this->_logic->getManualReceiptTargetsByNotificationSeq($nfSeq);

         // ユーザーIDの取得
         $obj = new TableUser($this->app->dbAdapter);
         $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

         try{
            //NULLの場合はエラー
            if( is_null($data) ){
                throw new \Exception(sprintf("口座SEQ '%s' は不正な指定です", f_e($params['nfseq'])));
            }

            $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
            try {
                //手動入金確定
                $this->_logic->doneReceipt($nfSeq, $data['OrderSeq'], $data['AccountSeq'], $userId, $amount);
                $this->app->dbAdapter->getDriver()->getConnection()->commit();
            }
            catch(\Exception $err) {
                $this->app->dbAdapter->getDriver()->getConnection()->rollBack();
                throw $err;
            }
         }
         catch(\Exception $msg) {
             //message取得
             $error_msg = $msg->getMessage();
             //エラーメッセージセット
             $this->messenger->addMessage( $error_msg );
         }

         //リダイレクト
         return $this->_redirect( "smbcparcpt/index" );
     }

    /**
     * disposeAction
     * 指定通知を破棄する
     */
    public function disposeAction()
    {
        $params = $this->getParams();

        $nfSeq = (isset($params['nfseq']) && is_numeric($params['nfseq'])) ? $params['nfseq'] : -1;
        $data = $this->_logic->getManualReceiptTargetsByNotificationSeq($nfSeq);

        try{
            //NULLの場合はエラー
            if( is_null($data) ){
                throw new \Exception(sprintf("口座SEQ '%s' は不正な指定です", f_e($params['nfseq'])));
            }

            $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
            try {
                //手動不能処理
                $this->_logic->doneUnreceipt($nfSeq, LogicSmbcpaAccountReceiptManual::RESULT_TYPE_8_DISPOSE_NOTIFY, $data['OrderSeq'], $data['AccountSeq']);
                $this->app->dbAdapter->getDriver()->getConnection()->commit();
            }
            catch(\Exception $err) {
                $this->app->dbAdapter->getDriver()->getConnection()->rollBack();
                throw $err;
            }
        }
        catch(\Exception $msg) {
            //message取得
            $error_msg = $msg->getMessage();
            //エラーメッセージセット
            $this->messenger->addMessage( $error_msg );
        }

        return $this->_redirect( "smbcparcpt/index" );
    }
}
