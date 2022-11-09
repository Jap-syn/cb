<?php
namespace oemmypage\Controller;

use oemmypage\Application;
use Coral\Coral\Controller\CoralControllerAction;
use Coral\Coral\Mail\CoralMail;
use Coral\Coral\Mail\CoralMailException;
use models\View\MypageViewTmpImage;
use Zend\Session\Container;
use models\Table\TableMypageCustomer;
use models\Logic\LogicMail;
use models\View\MypageViewSystemProperty;

class UploadController extends CoralControllerAction {
    protected $_componentRoot = './application/views/components';

    /**
     * @var Application
   */
    private $app;

    /**
     * 初期化処理
    */
//    protected function _init() {
//
//        $this->app = Application::getInstance();
//
//        // ページタイトルとスタイルシート、JavaScriptを設定
//        if ($this->is_mobile_request())
//        {
//            $this->addStyleSheet( '../../oemmypage/css_sp/mypage_index.css' );
//        }
//        else
//        {
//            $this->addStyleSheet( '../../oemmypage/css/mypage_index.css' );
//        }
//        $this->addJavaScript( '../../js/prototype.js' )
//            ->addJavaScript( '../../js/bytefx.js' )
//            ->addJavaScript( '../../js/json+.js' )
//            ->addJavaScript( '../../js/corelib.js' )
//            ->addJavaScript( '../../js/base.ui.js' );
//
//        $this->userInfo = $this->app->authManager->getUserInfo();
//        $this->altUserInfo = $this->app->authManager->getAlternativeUserInfo();
//
//        // タイトル文字取得
//        $this->title = $this->altUserInfo ?
//           sprintf( '%s %s(%s)　様', $this->userInfo->NameSeiKj, $this->userInfo->NameMeiKj, $this->altUserInfo->NameKj ) :
//           sprintf( '%s%s　様', $this->userInfo->NameSeiKj , $this->userInfo->NameMeiKj );
//    }

    /**
     * 身分証明書登録
    */
//    public function indexAction() {
//        $this->setPageTitle( '身分証明書ご登録' );
//        $this->view->assign( 'userInfo', $this->title );
//
//        return $this->view;
//    }

    /**
     * アップロード完了画面の表示
    */
//    public function completionAction() {
//        // ファイル取得
//        $frontimg = $_FILES['frontimg'];
//        $backimg = $_FILES['backimg'];
//
//        $errors = array();
//        if( empty( $frontimg['name'] ) ) {
//            $errors[] = '<p>表面ファイルのアップロードは必須です。</p>';
//        }
//        if( ( !empty( $frontimg['type'] ) && $frontimg['type'] != 'image/jpeg' && $frontimg['type'] != 'image/bmp' )
//         || ( !empty( $backimg['type'] ) && $backimg['type'] != 'image/jpeg' && $backimg['type'] != 'image/bmp' ) ) {
//             $errors[] = '<p>指定のファイル形式でのアップロードをお願いします。</p>';
//        }
//        if( !empty( $errors ) ) {
//            $this->setPageTitle( 'アップロードエラー' );
//            $this->view->assign( 'userInfo', $this->title );
//            $this->view->assign( 'error', $errors );
//
//            $this->setTemplate( 'index' );
//
//            return $this->view;
//        }
//
//        // トランザクション開始
//        $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
//
//        try {
//            // 身分証明書アップロード申請メールの送信
//            $maildata = array(
//                    'MailAddress' => $this->userInfo->MailAddress,
//                    'Name' => $this->userInfo->NameSeiKj . $this->userInfo->NameMeiKj,
//            );
//
//            // システムプロパティからハッシュキー取得
//            $mdlsp = new MypageViewSystemProperty( $this->app->dbAdapter );
//            $pcmailaddress = $mdlsp->getValue( 'mypage', 'identification', 'mailaddress' );
//
//            // 身分証明書アップロード申請メールの送信
//            $mailImgdata = array(
//                    'CustomerId' => $this->userInfo->CustomerId,
//                    'ManCustId' => $this->userInfo->ManCustId,
//                    'MailAddress' => $pcmailaddress,
//                    'NameSei' => $this->userInfo->NameSeiKj,
//                    'NameMei' => $this->userInfo->NameMeiKj,
//                    'FrontImage' => $frontimg,
//                    'BackImage' => $backimg
//            );
//
//            $lcmail = new LogicMail($this->app->dbAdapter, $this->app->smtpServer);
//            // 身分証明書アップロード申請メールの送信
//            $lcmail->SendIDUploadMail($maildata, $this->is_mobile_request());
//            // 身分証画像ファイルはメールで身分証明書管理用PCのメールアドレスに送信する
//            $subject = $lcmail->SendIdPCUploadImgMail($mailImgdata);
//
//            // マイページ顧客更新
//            $saveData = array(
//                    'IdentityDocumentClass' => 1,
//                    'MailSubject' => $subject,
//            );
//            $mdlc = new TableMypageCustomer( $this->app->dbAdapter );
//            $mdlc->saveUpdate($saveData, $this->userInfo->CustomerId );
//
//            $this->app->dbAdapter->getDriver()->getConnection()->commit();
//        } catch( CoralMailException $me ) {
//            // メールの例外は破棄
//        } catch( \Exception $err ) {
//            $this->app->dbAdapter->getDriver()->getConnection()->rollback();
//            throw $err;
//        }
//
//        $this->setPageTitle( 'アップロード完了' );
//        $this->view->assign( 'userInfo', $this->title );
//
//        return $this->view;
//    }

}
