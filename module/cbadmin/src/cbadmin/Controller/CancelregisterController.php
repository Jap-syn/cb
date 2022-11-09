<?php
namespace cbadmin\Controller;

use cbadmin\Application;
use Coral\Coral\Controller\CoralControllerAction;
use Coral\Base\Application\BaseApplicationAbstract;
use Coral\Base\BaseLog;
use Zend\Db\Adapter\Adapter;
use Zend\Config\Reader\Ini;
use models\Table\TableUser;
use models\Logic\LogicCancel;
use models\Logic\OrderCancelException;
/**
 * アプリケーションクラスです。
 *
 */
class CancelregisterController extends CoralControllerAction {
    protected $_componentRoot = './application/views/components';
    protected $_application_id = 'tools-CancelRegister-batch';

    public static function getInstance(){
        if(self::$_instance === null){
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    private $app;

    public function _init()
    {
        $this->app = Application::getInstance();

        $userInfo = $this->app->authManagerAdmin->getUserInfo();
        $this->view->assign('userInfo', $userInfo );

        $this->addStyleSheet('../css/default02.css')->addJavaScript( '../js/prototype.js' );

        $this->setPageTitle("後払い.com - 債権返却一括キャンセル");
    }

    public function CancelregisterformAction()
    {
        return $this->view;
    }

    public function CancelregisterAction()
    {
        try {
            // ユーザーID取得
            $obj = new TableUser($this->app->dbAdapter);
            $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);
            $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
            // ------------------------------------------------------------------------->
            // キャンセル理由の定義
            $prm_post = $this->params()->fromPost();
            $value = $prm_post['OrderId'];
            $value2 = explode("\n",$value);
            $target = preg_replace("/\s+/","",$value2);
            $reason = '';
            $reasonCode = 8;
            // <-------------------------------------------------------------------------
            $logic = new LogicCancel($this->app->dbAdapter);
            // 対象データ分ループ
            foreach ($target as $orderId) {
                // $this->logger->info('[' . $orderId . '] Start ');
                // 注文SEQを特定する
                $sql = ' SELECT * FROM T_Order WHERE OrderId = :OrderId ';
                $prm = array(
                    ':OrderId' => $orderId,
                );
                $row = $this->app->dbAdapter->query($sql)->execute($prm)->current();
                if (!$row) {
                    // 特定できない場合はアラート出力⇒次の行へ
                    $this->app->logger->warn('<CancelRegister> [' . $orderId . '] OrderId Is Not Found!!');
                    $ng = $orderId;
                    $ngtarget[] = $orderId;
                    continue;
                }
                // 注文SEQ特定
                $oseq = $row['OrderSeq'];
                // キャンセル申請処理を行う
                try {
                    $logic->applies($oseq, $reason, $reasonCode, 1, false, $userId);
                    $this->app->logger->info('<CancelRegister> [' . $orderId . '] Complete!! ');
                } catch(OrderCancelException $oce) {
                    $this->app->logger->warn('<CancelRegister> [' . $orderId . '] Order Is Not Cancel Message = ' . $oce->getMessage());
                    $this->app->logger->warn('<CancelRegister> [' . $orderId . '] ' . $oce->getTraceAsString());
                }
            }
            if($value == ""){
                $_SESSION['message'] = '<div align="center"><font color="red">対象IDを入力して下さい。</font></div>';
            }elseif($ng !== null){
                $_SESSION['message'] = sprintf('<div align="center"><font color="red">対象IDに誤りがあります。処理を中止致します。%s<br /></font></div>',date("Y-m-d H:i:s"));
                $_SESSION['ng'] = $ngtarget;
                $this->app->dbAdapter->getDriver()->getConnection()->rollback();
            }else{
                $_SESSION['message'] = sprintf('<div align="center"><font color="red">更新しました。%s<br /></font></div>',date("Y-m-d H:i:s"));
            }
            $this->app->dbAdapter->getDriver()->getConnection()->commit();
            $this->app->logger->info('_data_patch_20210225_1130_CancelRegister.php end');
        } catch( \Exception $e ) {
            try{
                $this->app->dbAdapter->getDriver()->getConnection()->rollback();
            } catch ( \Exception $err) { }
            // エラーログを出力
            if ( isset($this->app->logger) ) {
                $this->app->logger->err('<CancelRegister> ' . $e->getMessage());
                $this->app->logger->err('<CancelRegister> ' . $e->getTraceAsString());
            }
        }
        return $this->_redirect("cancelregister/cancelregisterform");
    }

    public function generallycancelregisterformAction()
    {
        $this->setPageTitle("後払い.com - 通常一括キャンセル");
        return $this->view;
    }

    public function generallycancelregisterAction(){
        try{
            // ユーザーID取得
            $mdlu = new TableUser($this->app->dbAdapter);
            $userId = $mdlu->getUserId(TableUser::USERCLASS_SYSTEM, TableUser::SEQ_BATCH_USER);
            $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();

            // キャンセル理由の定義
            $prm_post = $this->params()->fromPost();
            $value = $prm_post['OrderId'];
            $value2 = explode("\n",$value);
            $target = preg_replace("/\s+/","",$value2);
            $reason = '';
            $reasonCode = 8;
            $logic = new LogicCancel($this->app->dbAdapter);

            // 対象データ分ループ
            foreach ($target as $orderId) {
                // $this->logger->info('[' . $orderId . '] Start ');​
                // 注文SEQを特定する
                $sql = ' SELECT * FROM T_Order WHERE OrderId = :OrderId ';
                $prm = array(
                    ':OrderId' => $orderId,
                );
                $row = $this->app->dbAdapter->query($sql)->execute($prm)->current();
                if(!$row){
                    // 特定できない場合はアラート出力⇒次の行へ
                    $this->app->logger->warn('<CancelRegister> [' . $orderId . '] OrderId Is Not Found!!');
                    $ng = $orderId;
                    $ngtarget[] = $orderId;
                    continue;
                }
                // 注文SEQ特定
                $oseq = $row['OrderSeq'];
                // キャンセル申請処理を行う
                try {
                    $logic->applies($oseq, $reason, $reasonCode, 0, false, $userId);
                    $this->app->logger->info('<CancelRegister> [' . $orderId . '] Complete!! ');
                } catch(OrderCancelException $oce) {
                    $this->app->logger->warn('<CancelRegister> [' . $orderId . '] Order Is Not Cancel Message = ' . $oce->getMessage());
                    $this->app->logger->warn('<CancelRegister> [' . $orderId . '] ' . $oce->getTraceAsString());
                }

            }
            if($value == ""){
                $_SESSION['message'] = '<div align="center"><font color="red">対象IDを入力して下さい。</font></div>';
            }elseif($ng !== null){
                $_SESSION['message'] = sprintf('<div align="center"><font color="red">対象IDに誤りがあります。処理を中止致します。%s<br /></font></div>',date("Y-m-d H:i:s"));
                $_SESSION['ng'] = $ngtarget;
                $this->app->dbAdapter->getDriver()->getConnection()->rollback();
            }else{
                $_SESSION['message'] = sprintf('<div align="center"><font color="red">更新しました。%s<br /></font></div>',date("Y-m-d H:i:s"));
            }

            // $this->dbAdapter->getDriver()->getConnection()->rollback();
            $this->app->dbAdapter->getDriver()->getConnection()->commit();
        } catch( \Exception $e ) {
            try{
                $this->app->dbAdapter->getDriver()->getConnection()->rollback();
            } catch ( \Exception $err) { }
            // エラーログを出力
            if ( isset($this->app->logger) ) {
                $this->app->logger->err('<CancelRegister> ' . $e->getMessage());
                $this->app->logger->err('<CancelRegister> ' . $e->getTraceAsString());
            }
        }
        return $this->_redirect("cancelregister/generallycancelregisterform");

    }

}
