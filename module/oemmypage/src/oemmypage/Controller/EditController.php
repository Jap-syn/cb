<?php
namespace oemmypage\Controller;

use oemmypage\Application;
use Coral\Coral\Controller\CoralControllerAction;
use Coral\Coral\CoralValidate;
use Coral\Coral\CoralCodeMaster;
use Coral\Coral\Mail\CoralMail;
use Coral\Coral\Mail\CoralMailException;
use models\Table\TableMypageCustomer;
use models\Table\TableMypagePasswordHistory;
use models\Table\TableSystemProperty;
use models\Logic\LogicMail;
use models\View\MypageViewCustomer;
use Zend\Validator;
use models\Logic\LogicNormalizer;

class EditController extends CoralControllerAction {
    protected $_componentRoot = './application/views/components';

    /**
     * @var Application
    */
    private $app;

    /**
     * @var array
    */
    private $prefecture;

    /**
     * @var string
    */
    private $title;

    /**
     * 初期化処理
    */
    protected function _init() {
        $this->app = Application::getInstance();

        // ページタイトルとスタイルシート、JavaScriptを設定
        if ($this->is_mobile_request())
        {
            $this->addStyleSheet( '../../oemmypage/css_sp/mypage_index.css' );
        }
        else
        {
            $this->addStyleSheet( '../../oemmypage/css/mypage_index.css' );
        }
        $this->addJavaScript( '../../js/prototype.js' )
            ->addJavaScript( '../../js/bytefx.js' )
            ->addJavaScript( '../../js/json+.js' )
            ->addJavaScript( '../../js/corelib.js' )
            ->addJavaScript( '../../js/base.ui.js' );

        $this->userInfo = $this->app->authManager->getUserInfo();

        $this->altUserInfo = $this->app->authManager->getAlternativeUserInfo();

        // 県コードマスター作成
        $codeMaster = new CoralCodeMaster( $this->app->dbAdapter );
        $this->prefecture = $codeMaster->getMyPagePrefectureMaster();

        // タイトル文字取得
        $this->title = $this->altUserInfo ?
           sprintf( '%s %s(%s)　様', $this->userInfo->NameSeiKj, $this->userInfo->NameMeiKj, $this->altUserInfo->NameKj ) :
           sprintf( '%s%s　様', $this->userInfo->NameSeiKj , $this->userInfo->NameMeiKj );
    }

    /**
     * 会員情報変更
    */
    public function indexAction() {
        // マイページ情報取得
        $sql = ' SELECT * FROM T_MypageCustomer WHERE CustomerId = :CustomerId ';
        $data = $this->app->dbAdapter->query( $sql )->execute( array( ':CustomerId' => $this->userInfo->CustomerId ) )->current();

        // Birthday は年月日に分割
        $birthday = explode( '-', $data['Birthday'] );
        $data['Birthday_y'] = $birthday[0];
        $data['Birthday_m'] = $birthday[1];
        $data['Birthday_d'] = $birthday[2];

        // PostalCode は上3桁、4桁に分割
        $data['PostalCode_1'] = substr( $data['PostalCode'], 0, 3 );
        $data['PostalCode_2'] = substr( $data['PostalCode'], 3 );

        // Phone と MobilePhone は3つに分割
        $phone = explode( '-', $data['Phone'] );
        $data['Phone_1'] = $phone[0];
        $data['Phone_2'] = $phone[1];
        $data['Phone_3'] = $phone[2];
        $mobilephone = explode( '-', $data['MobilePhone'] );
        $data['MobilePhone_1'] = $mobilephone[0];
        $data['MobilePhone_2'] = $mobilephone[1];
        $data['MobilePhone_3'] = $mobilephone[2];

        // LoginPasswdは文字数
        $data['LoginPasswd'] = strlen( $data['LoginPasswd'] );

        $this->setPageTitle( '会員登録情報編集' );
        $this->view->assign( 'userInfo', $this->title );
        $this->view->assign( 'data', $data );
        $this->view->assign( 'prefecture', $this->prefecture );

        return $this->view;
    }

    /**
     * 登録内容の確認
    */
    public function confirmAction() {
        $data = $this->getParams()['form'];

        // 分割されたカラムを結合
        $data['Birthday'] = $data['Birthday_y'] . '-' . $data['Birthday_m'] . '-' . $data['Birthday_d'];
        $data['PostalCode'] = $data['PostalCode_1'] . $data['PostalCode_2'];

        $phone = null;
        if( !empty( $data['Phone_1'] ) ) $phone = $data['Phone_1'];
        if( !empty( $data['Phone_2'] ) ) $phone = !empty( $phone ) ? $phone . '-' . $data['Phone_2'] : $data['Phone_2'];
        if( !empty( $data['Phone_3'] ) ) $phone = !empty( $phone ) ? $phone . '-' . $data['Phone_3'] : $data['Phone_3'];
        $data['Phone'] = $phone;

        $mobilePhone = null;
        if( !empty( $data['MobilePhone_1'] ) ) $mobilePhone = $data['MobilePhone_1'];
        if( !empty( $data['MobilePhone_2'] ) ) $mobilePhone = !empty( $mobilePhone ) ? $mobilePhone . '-' . $data['MobilePhone_2'] : $data['MobilePhone_2'];
        if( !empty( $data['MobilePhone_3'] ) ) $mobilePhone = !empty( $mobilePhone ) ? $mobilePhone . '-' . $data['MobilePhone_3'] : $data['MobilePhone_3'];
        $data['MobilePhone'] = $mobilePhone;

        // 都道府県名を展開
        $codeMaster = new CoralCodeMaster( $this->app->dbAdapter );
        $data['PrefectureName'] = $codeMaster->getMyPagePrefectureName( $data['PrefectureCode'] );

        $errors = $this->validate( $data );

        // 検証エラーがあった場合は入力画面を表示
        if( !empty( $errors ) ) {
            $this->setPageTitle( '会員登録情報編集' );
            $this->view->assign( 'userInfo', $this->title );
            $this->view->assign( 'prefecture', $this->prefecture );
            $this->view->assign( 'data', $data );
            $this->view->assign( 'error', $errors );

            $this->setTemplate( 'index' );

            return $this->view;
        }

        // フォームデータ自身をエンコード
        $formData = base64_encode( serialize( $data ) );

        $this->setPageTitle( '会員情報変更確認' );
        $this->view->assign( 'userInfo', $this->title );
        $this->view->assign( 'prefecture', $this->prefecture );
        $this->view->assign( 'data', $data );
        $this->view->assign( 'encoded_data', $formData );

        return $this->view;
    }

    /**
     * 登録を実行
    */
    public function saveAction() {
        // エンコード済みのPOSTデータを復元する
        $data = unserialize( base64_decode( $this->params()->fromPost( 'hash' ) ) );

        $data['UnitingAddress'] = $data['PrefectureName'] . $data['Address'] . $data['Building'];

        // 正規化された情報作成
        $data['RegNameKj'] = LogicNormalizer::create( LogicNormalizer::FILTER_FOR_NAME ) ->normalize( $data['NameSeiKj'] . $data['NameMeiKj'] );
        $data['RegUnitingAddress'] = LogicNormalizer::create( LogicNormalizer::FILTER_FOR_ADDRESS ) ->normalize( $data['PrefectureName'] . $data['Address'] . $data['Building'] );
        if ( strlen( $data['Phone'] ) > 0 )
        {
            $data['RegPhone'] = LogicNormalizer::create( LogicNormalizer::FILTER_FOR_TEL ) ->normalize( $data['Phone'] );
        }
        if (strlen( $data['MobilePhone'] ) > 0 )
        {
            $data['RegMobilePhone'] = LogicNormalizer::create( LogicNormalizer::FILTER_FOR_TEL ) ->normalize( $data['MobilePhone'] );
        }

        // トランザクション開始
        $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();

        try {

            $mypageCustomer = new TableMypageCustomer($this->app->dbAdapter);
            // 更新前会員情報
            $mailData = $mypageCustomer->find( $this->userInfo->CustomerId )->current();
            // 会員情報更新
            $mypageCustomer->saveUpdate( $data, $this->userInfo->CustomerId );

            // 会員情報編集完了メール送信
            $data['OemId'] = $this->userInfo->OemId;
            $lcmail = new LogicMail( $this->app->dbAdapter, $this->app->smtpServer );
            $lcmail->SendRegistMail( $data, $this->is_mobile_request() );

            $this->app->dbAdapter->getDriver()->getConnection()->commit();
        } catch( \Exception $err ) {
            $this->app->dbAdapter->getDriver()->getConnection()->rollback();
            throw $err;
        }

        $this->app->authManager->resetLoginState();
        $this->_redirect( 'edit/completion' );
    }

    /**
     * 確認画面からの戻り処理
    */
    public function backAction() {
        // エンコード済みのPOSTデータを復元する
        $data = unserialize( base64_decode( $this->params()->fromPost( 'hash' ) ) );

        $this->setPageTitle( '会員登録情報編集' );
        $this->view->assign( 'userInfo', $this->title );
        $this->view->assign( 'prefecture', $this->prefecture );
        $this->view->assign( 'data', $data );

        $this->setTemplate( 'index' );

        return $this->view;
    }

    /**
     * 登録完了画面の表示
    */
    public function completionAction() {
        $this->setPageTitle( '会員登録情報変更完了' );
        $this->view->assign( 'userInfo', $this->title );

        return $this->view;
    }

    /**
     * パスワード変更
    */
    public function passchgAction() {
        $sql = ' SELECT MailAddress FROM T_MypageCustomer WHERE CustomerId = :CustomerId ';
        $data = $this->app->dbAdapter->query( $sql )->execute( array( ':CustomerId' => $this->userInfo->CustomerId ) )->current();

        // パスワード文字数(桁)の取得
        $mdlsp = new TableSystemProperty( $this->app->dbAdapter );
        $passwdCount = $mdlsp->getValue( '[DEFAULT]', 'systeminfo', 'PasswdCount' );

        $this->setPageTitle( 'パスワード変更' );
        $this->view->assign( 'userInfo', $this->title );
        $this->view->assign( 'data', $data );
        $this->view->assign( 'passwdCount', $passwdCount );

        return $this->view;
    }

    /**
     * パスワード変更の確認
    */
    public function passchgconfirmAction() {
        $data = $this->getParams()['form'];

        // 認証ユーティリティ取得
        $authUtil = $this->app->getAuthUtility();

        // 現在のパスワード取得
        $authUtil->setHashDisabled( $this->userInfo->Hashed ? false : true );   // 現在のパスワードの突合用にハッシュ利用状況を設定
        $loginPasswd = $authUtil->generatePasswordHash( $this->userInfo->OemId. "@". $this->userInfo->MailAddress, $data['LoginPasswd'] );

        $newPass1Hashed =$authUtil->generatePasswordHash( $this->userInfo->OemId. "@". $data['MailAddress'], $data['NewLoginPasswd'] );

        // パスワード文字数(桁)の取得
        $mdlsp = new TableSystemProperty( $this->app->dbAdapter );
        $passwdCount = $mdlsp->getValue( '[DEFAULT]', 'systeminfo', 'PasswdCount' );
        // 過去パスワード使用回数(回)の取得
        $passTimeLimit = $mdlsp->getValue( '[DEFAULT]', 'systeminfo', 'UsePasswdTimes' );
        // パスワード期限切れ日数(日)の取得
        $passwdLimitDay = $mdlsp->getValue( '[DEFAULT]', 'systeminfo', 'PasswdLimitDay' );
        //パスワード文字数
        $pass_len = strlen($data['NewLoginPasswd']);
        //過去4回分のパスワードを取得
        $sql = " SELECT LoginPasswd FROM T_MypagePasswordHistory WHERE Category = 6 AND LoginId = :LoginId ORDER BY Seq DESC LIMIT " . $passTimeLimit;
        $LastPass = ResultInterfaceToArray($this->app->dbAdapter->query($sql)->execute(array(':LoginId' => $this->userInfo->LoginId)));

        $error = array();
        if( empty( $data['LoginPasswd'] ) ) {
            $error[] = '現在のパスワードが未入力です。';
        }
        if( $loginPasswd != $this->userInfo->LoginPasswd ) {
            $error[] = '現在のパスワードに誤りがあります。';
        }
        if( empty( $data['NewLoginPasswd'] ) ) {
            $error[] = '新しいパスワードが未入力です。';
        }
        if( empty( $data['NewLoginPasswd2'] ) ) {
            $error[] = '新しいパスワード（確認）が未入力です。';
        }
        if( $data['NewLoginPasswd'] != $data['NewLoginPasswd2'] ) {
            $error[] = '新しいパスワードと新しいパスワード（確認）が一致しません。';
        }
        if ( empty( $error ) && (!preg_match('/[a-z]/', $data['NewLoginPasswd']) || !preg_match('/[A-Z]/', $data['NewLoginPasswd']) || !preg_match('/[0-9]/', $data['NewLoginPasswd'])
        || !preg_match('/[ -\/:-@\[-`\{-\~]/', $data['NewLoginPasswd']) || $pass_len < $passwdCount) )
        {
            $error[] = 'パスワードは英大文字、英小文字、数字、記号のすべてを含む' . $passwdCount . '文字以上でご入力ください。';
        }
        foreach ($LastPass as $pass)
        {
            if ($newPass1Hashed == $pass['LoginPasswd'])
            {
                $error[] = '過去' .$passTimeLimit. '回に使用したパスワードは利用できません。';
            }
        }

        if( !empty( $error ) ) {
            $this->setPageTitle( 'パスワード変更' );
            $this->view->assign( 'userInfo', $this->title );
            $this->view->assign( 'data', $data );
            $this->view->assign( 'error', $error );
            $this->view->assign( 'passwdCount', $passwdCount );

            $this->setTemplate( 'passchg' );

            return $this->view;
        }

        // ハッシュ済みのログインパスワードを設定
        $saveData = array(
            'LoginPasswd' => $newPass1Hashed,
            'Hashed' => 1,
        );

        // トランザクション開始
        $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();

        try
        {
            // パスワード更新処理
            $mypageCustomer = new TableMypageCustomer($this->app->dbAdapter);
            $mypageCustomer->saveUpdate( $saveData, $this->userInfo->CustomerId );

            // パスワード履歴テーブルに１件追加
            $historyData = array(
                     'Category'       => 6
                    ,'LoginId'        => $this->userInfo->LoginId
                    ,'LoginPasswd'    => $saveData['LoginPasswd']
                    ,'PasswdStartDay' => date('Y-m-d')
                    ,'PasswdLimitDay' => date('Y-m-d', strtotime("$passwdLimitDay day"))
                    ,'Hashed'         => 1
                    ,'ValidFlg'       => 1
            );
            $mdlmph = new TableMypagePasswordHistory($this->app->dbAdapter);
            $mdlmph->saveNew($historyData);

            // パスワード更新完了メールの送信
            $customerId = $this->userInfo->CustomerId;           // 顧客ＩＤ
            $lcmail = new LogicMail( $this->app->dbAdapter, $this->app->smtpServer );
            $lcmail->SendChangeMypagePwd( $customerId, $this->is_mobile_request() );

            $this->app->dbAdapter->getDriver()->getConnection()->commit();
        } catch( \Exception $err ) {
            $this->app->dbAdapter->getDriver()->getConnection()->rollback();
            throw $err;
        }

        $this->app->authManager->resetLoginState();
        $this->_redirect( 'edit/completion' );
    }

    /**
     * 退会申請
    */
    public function withdrawAction() {
        $this->setPageTitle( '退会申請' );
        $this->view->assign( 'userInfo', $this->title );

        return $this->view;
    }

    /**
     * 退会申請実行
    */
    public function withdrawexecAction() {
        // トランザクション開始
        $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();

        try {
            // 退会完了メールの送信
            // 物理削除するため先に送信
            $customerId = $this->userInfo->CustomerId;           // 顧客ＩＤ
            $lcmail = new LogicMail( $this->app->dbAdapter, $this->app->smtpServer );
            $lcmail->SendWithdrawMail( $customerId, $this->is_mobile_request() );

            $mypageCustomer = new TableMypageCustomer($this->app->dbAdapter);
            $mypageCustomer->delete( $this->userInfo->CustomerId );

            $this->app->dbAdapter->getDriver()->getConnection()->commit();
        } catch( CoralMailException $me ) {
            // メールの例外は破棄
        } catch( \Exception $err ) {
            $this->app->dbAdapter->getDriver()->getConnection()->rollback();
            throw $err;
        }

        $this->_redirect( 'edit/wdcompletion' );
    }

    /**
     * 退会申請完了画面の表示
    */
    public function wdcompletionAction() {
        $this->setPageTitle( '退会申請完了' );
        $this->view->assign( 'userInfo', $this->title );
        $this->app->authManager->logout();

        return $this->view;
    }

    /**
     * 郵便番号を検索する。
    */
	public function searchzipAction() {
        try
        {
            $coralV = new CoralValidate();
            if (empty($_GET["zc"])) {
                $msg['PrefectureCode'] = '';
                $msg['CityKanji'] = '';
                $msg['TownKanji'] = '';
                $msg['ErrorMessage'] = '郵便番号が未入力です。';
            }
            else if (!$coralV->isPostCode( $_GET["zc"] )) {
                $msg['PrefectureCode'] = '';
                $msg['CityKanji'] = '';
                $msg['TownKanji'] = '';
                $msg['ErrorMessage'] = '郵便番号の形式が不正です。';
            }
            else {
                $query = " SELECT MPOS.*, MPRE.PrefectureCode FROM MV_PostalCode MPOS, MV_Prefecture MPRE WHERE MPOS.PrefectureKanji = MPRE.PrefectureName AND MPOS.PostalCode7 = :PostalCode7 ";
                $stm = $this->app->dbAdapter->query($query);

                $postalCode7 = mb_ereg_replace( "[^0-9０-９]", "", $_GET["zc"] );
                $postalCode7 = mb_convert_kana( $postalCode7, "n", "UTF-8" );

                $prm = array(
                   ':PostalCode7' => $postalCode7,
                );

                $msg = $stm->execute( $prm )->current();
                if ( !$msg ) {
                    $msg['PrefectureCode'] = '';
                    $msg['CityKanji'] = '';
                    $msg['TownKanji'] = '';
                    $msg['ErrorMessage'] = '存在しない郵便番号です。';
                }
                else {
                    $msg['ErrorMessage'] = '';
                }
            }
        }
        catch( \Exception $e )
        {
            $msg['PrefectureCode'] = '';
            $msg['CityKanji'] = '';
            $msg['TownKanji'] = '';
            $msg['ErrorMessage'] = '存在しない郵便番号です。';
        }

        echo \Zend\Json\Json::encode( $msg );

        return $this->response;
    }

    /**
     * 入力検証処理
     *
     * @access protected
     * @param array $data
     * @return array
    */
    private function validate( $data = array() ) {
        $errors = array();

        // NameSeiKj: 姓
        $key = 'NameSeiKj';
        if( !isset( $errors[$key] ) && strlen( $data[$key] ) == 0 ) {
            $errors[$key] = "名前が未入力です。";
        }
        if( !isset( $errors[$key] ) && preg_match( '/(?:\xEF\xBD[\xA1-\xBF]|\xEF\xBE[\x80-\x9F])|[\x20-\x7F]/', $data[$key] ) ) {
            $errors[$key] = "名前は全角で入力してください。";
        }

        // NameSeiKj: 名
        $key = 'NameMeiKj';
        if( !isset( $errors['NameSeiKj'] ) && !isset( $errors[$key] ) && strlen( $data[$key] ) == 0 ) {
            $errors[$key] = "名前が未入力です。";
        }
        if( !isset( $errors['NameSeiKj'] ) && !isset( $errors[$key] ) && preg_match( '/(?:\xEF\xBD[\xA1-\xBF]|\xEF\xBE[\x80-\x9F])|[\x20-\x7F]/', $data[$key] ) ) {
            $errors[$key] = "'名前は全角で入力してください。";
        }

        // NameSeiKn: セイ
        $key = 'NameSeiKn';
        if( !isset( $errors[$key] ) && strlen( $data[$key] ) == 0 ) {
            $errors[$key] = "フリガナが未入力です。";
        }
        if( !isset( $errors[$key] ) && preg_match( '/(?:\xEF\xBD[\xA1-\xBF]|\xEF\xBE[\x80-\x9F])|[\x20-\x7F]/', $data[$key] ) ) {
            $errors[$key]= "フリガナは全角で入力してください。";
        }
        if(!isset( $errors[$key] ) && !preg_match( '/^[ァ-ヾ]+$/u', preg_replace( '/(\s|　)/', '', $data[$key] ) ) ) {
            $errors[$key] = "フリガナはカタカナで入力してください。";
        }

        // NameMeiKn: メイ
        $key = 'NameMeiKn';
        if( !isset( $errors['NameSeiKn'] ) && !isset( $errors[$key] ) && strlen( $data[$key] ) == 0 ) {
            $errors[$key] = "フリガナが未入力です。";
        }
        if( !isset( $errors['NameSeiKn'] ) && !isset( $errors[$key] ) && preg_match( '/(?:\xEF\xBD[\xA1-\xBF]|\xEF\xBE[\x80-\x9F])|[\x20-\x7F]/', $data[$key] ) ) {
            $errors[$key]= "フリガナは全角で入力してください。";
        }
        if( !isset( $errors['NameSeiKn'] ) && !isset( $errors[$key] ) && !preg_match( '/^[ァ-ヾ]+$/u', preg_replace( '/(\s|　)/', '', $data[$key] ) ) ) {
            $errors[$key] = "フリガナはカタカナで入力してください。";
        }

        // Sex: 性別
        $key = 'Sex';
        if( !isset( $errors[$key] ) && strlen( $data[$key] ) == 0 ) {
            $errors[$key] = "性別が選択されていません。";
        }

        // Birthday: 生年月日
        $key = 'Birthday';
        if( !isset( $errors[$key] ) && !checkdate( $data['Birthday_m'], $data['Birthday_d'], $data['Birthday_y'] ) ) {
            $errors[$key] = "生年月日の入力に誤りがあります。";
        }
        if( !isset( $errors[$key] ) && strtotime( $data[$key] ) > strtotime( date( 'Y-m-d' ) ) ) {
            $errors[$key] = "生年月日の入力に誤りがあります。";
        }


        $coralV = new CoralValidate();
        // PostalCode: 郵便番号
        $key = 'PostalCode';
        if( !isset( $errors[$key] ) && strlen( $data[$key] ) == 0 ) {
            $errors[$key] = "郵便番号が未入力です。";
        }
        if( !isset( $errors[$key] ) && !$coralV->isPostCode( $data[$key] ) ) {
            $errors[$key] = "郵便番号の形式が不正です。";
        }
        $sql = " SELECT * FROM MV_PostalCode WHERE PostalCode7 = :PostalCode7 ";
        $cntPostalCode = $this->app->dbAdapter->query( $sql )->execute( array( ':PostalCode7' => str_replace( '-', '', $data[$key] ) ) )->count();
        if( !isset( $errors[$key] ) && $cntPostalCode == 0 ) {
            $errors[$key] = "存在しない郵便番号です。";
        }

        // PrefectureCode: 都道府県
        $key = 'PrefectureCode';
        if( !isset( $errors[$key] ) && $data[$key] == 0 ) {
            $errors[$key] = "都道府県が未選択です。";
        }

        // Address: 住所
        $key = 'Address';
        if( !isset( $errors[$key] ) && strlen( $data[$key] ) == 0 ) {
            $errors[$key] = "住所が未入力です。";
        }
        if( !isset( $errors[$key] ) && preg_match( '/(?:\xEF\xBD[\xA1-\xBF]|\xEF\xBE[\x80-\x9F])|[\x20-\x7F]/', $data[$key] ) ) {
            $errors[$key] = "住所は全角で入力してください。";
        }

        // Building: 建物名・部屋番号
        $key = 'Building';
        if(!isset( $errors[$key] ) && strlen( $data[$key] ) > 0 && preg_match( '/(?:\xEF\xBD[\xA1-\xBF]|\xEF\xBE[\x80-\x9F])|[\x20-\x7F]/', $data[$key] ) ) {
            $errors[$key] = "建物名・部屋番号は全角で入力してください。";
        }

        // Phone: 電話番号
        $key = 'Phone';
        if( !isset( $errors[$key] ) && strlen( $data[$key] ) > 0 && !(preg_match( '/^[- 0-9]+$/', $data[$key] )) ) {
            $errors[$key] = "電話番号は半角数値で入力してください。";
        }
        if( !isset( $errors[$key] ) && strlen( $data[$key] ) > 0 && ( strlen( str_replace( '-', '', $data[$key] ) ) < 10 || strlen( str_replace( '-', '', $data[$key] ) ) > 11 ) ) {
            $errors[$key] = "電話番号の桁数に誤りがあります。";
        }

        // MobilePhone: 電話番号(携帯）
        $key = 'MobilePhone';
        if( !isset( $errors[$key] ) && strlen( $data[$key] ) > 0 && !(preg_match( '/^[- 0-9]+$/', $data[$key] )) ) {
            $errors[$key] = "電話番号は半角数値で入力してください。";

        }
        if( !isset( $errors[$key] ) && strlen( $data[$key] ) > 0 && ( strlen( str_replace( '-', '', $data[$key] ) ) < 10 || strlen( str_replace( '-', '', $data[$key] ) ) > 11 ) ) {
                $errors[$key] = "電話番号の桁数に誤りがあります。";
        }

        // 電話番号・電話番号(携帯）共に未入力
        if( !isset( $errors['Phone'] ) && !isset( $errors[$key] ) && strlen( $data['Phone'] ) == 0 && strlen( $data[$key] ) == 0 ) {
            $errors[$key] = "電話番号、電話番号（携帯）のいずれかは入力してください。";
        }

        return $errors;
    }

    /**
     * パスワード変更
     */
    public function chgpwAction()
    {
        unset($error);
        $cmd = $this->params()->fromRoute('cmd', 'none');

        $this
        ->addJavaScript( '../js/prototype.js' )
        ->addJavaScript( '../js/corelib.js' );

        // 認証ユーティリティ取得
        $authUtil = $this->app->getAuthUtility();

        $mdlsp = new TableSystemProperty($this->app->dbAdapter);
        $mdlph = new TableMypagePasswordHistory($this->app->dbAdapter);

        $PassHistCnt = $mdlph->findnew(6, $this->userInfo->LoginId);
        $PassHist = $mdlph->findnew(6, $this->userInfo->LoginId)->current();

        $passCnt = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'PasswdCount');
        $passAlert = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'PasswdLimitAlertDay');
        $sysTimeLimit = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'PasswdLimitDay');
        $loginmode = 0;

        if ($PassHist['PasswdLimitDay'] >= date('Y-m-d') && $PassHist['PasswdLimitDay'] <= date('Y-m-d', strtotime($passAlert ." day")) ) {
            $msg = 'パスワードの有効期限が近づいています。新しいパスワードを設定してください。';
            $loginmode = 2;
        } elseif ($PassHist['PasswdLimitDay'] < date('Y-m-d')) {
            $msg = 'パスワードの有効期限が切れています。新しいパスワードを設定してください。';
            $loginmode = 3;
        }

        if($loginmode == 0) {
            return $this->_redirect('index/index');
        }

        $this->view->assign('userInfo', $this->title);
        $this->view->assign('mode', $loginmode);
        $this->view->assign('msg', $msg);
        $this->view->assign('mailAddress', $this->userInfo->MailAddress);
        $this->view->assign('passwdCount', $passCnt);
        $this->view->assign('billreissFlg', 1);

        if ($cmd == 'e') {
            $ar = $this->getRequest()->getPost()->toArray();
            $form = $ar['form'];

            // パスワード変更実行
            $oldPass  = $form['LoginPasswd'];
            $newPass1 = $form['NewLoginPasswd'];
            $newPass2 = $form['NewLoginPasswd2'];

            $authUtil->setHashDisabled($this->userInfo->Hashed ? false : true);// 古いパスワードの突合用にハッシュ利用状況を設定
            $oldPassHashed = $authUtil->generatePasswordHash($this->userInfo->LoginId, $oldPass);
            $newPass1Hashed = $authUtil->generatePasswordHash($this->userInfo->LoginId, $newPass1);

            //パスワード文字数
            $pass_len = strlen($newPass1);
            //過去パスワードの使用不可回数
            $passTimeLimit = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'UsePasswdTimes');
            //過去4回分のパスワードを取得
            $sql = " SELECT LoginPasswd FROM T_MypagePasswordHistory WHERE Category = 6 AND LoginId = :LoginId ORDER BY Seq DESC LIMIT " . $passTimeLimit;
            $LastPass = ResultInterfaceToArray($this->app->dbAdapter->query($sql)->execute(array(':LoginId' => $this->userInfo->LoginId)));

            if ( empty($oldPass) )
            {
                $error[] = '現在のパスワードが未入力です。';
            }

            if( !(empty($oldPass)) && $oldPassHashed != $this->userInfo->LoginPasswd )
            {
                $error[] = '現在のパスワードに誤りがあります。';
            }

            if ( empty($newPass1) )
            {
                $error[] = '新しいパスワードが未入力です。';
            }

            if ( empty($newPass2) )
            {
                $error[] = '新しいパスワード（確認）が未入力です。';
            }

            if ( !(empty($newPass1)) && !(empty($newPass2)) && $newPass1 != $newPass2 )
            {
                $error[] = '新しいパスワードと新しいパスワード（確認）が一致しません。';
            }

            if ( empty( $error ) && (!preg_match('/[a-z]/', $newPass1) || !preg_match('/[A-Z]/', $newPass1) || !preg_match('/[0-9]/', $newPass1)
            || !preg_match('/[ -\/:-@\[-`\{-\~]/', $newPass1) || $pass_len < $passCnt) )
            {
                $error[] = 'パスワードは英大文字、英小文字、数字、記号のすべてを含む' .$passCnt. '文字以上でご入力ください。';
            }

            foreach ($LastPass as $pass)
            {
                if ($newPass1Hashed == $pass['LoginPasswd'])
                {
                    $error[] = '過去' .$passTimeLimit. '回に使用したパスワードは利用できません。';
                }
            }

            if (isset($error))
            {
                $this->view->assign('error', $error);
                $this->setTemplate('chgpw_f');
                return $this->view;
            }
            else
            {
                // パスワード更新処理
                $saveData = array(
                        'LoginPasswd' => $newPass1Hashed,
                        'Hashed' => 1,
                );

                $mypageCustomer = new TableMypageCustomer($this->app->dbAdapter);
                $mypageCustomer->saveUpdate( $saveData, $this->userInfo->CustomerId );

                $passChgData = array(
                        'Category' => 6,
                        'LoginId' => $this->userInfo->LoginId,
                        'LoginPasswd' => $newPass1Hashed,
                        'PasswdStartDay' => date('Y-m-d'),
                        'PasswdLimitDay' => date('Y-m-d', strtotime("+$sysTimeLimit days")),
                        'Hashed' => 1,
                        'ValidFlg' => 1);

                //パスワード履歴テーブルに1件追加する
                $mdlph->saveNew($passChgData);

                // ログイン状態を更新
                $this->app->authManager->resetLoginState();
                $this->setTemplate('chgpw_e');
                return $this->view;
            }
        }
        else {
            // パスワード変更フォーム表示
            $this->setTemplate('chgpw_f');
            return $this->view;
        }
    }
}
