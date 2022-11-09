<?php
namespace mypage\Controller;

use mypage\Application;
use models\View\MypageViewCustomer;
use models\View\MypageViewSystemProperty;
use Coral\Base\BaseGeneralUtils;
use Coral\Coral\Controller\CoralControllerAction;
use Coral\Coral\CoralValidate;
use Coral\Coral\CoralCodeMaster;
use Coral\Coral\Mail\CoralMail;
use Coral\Coral\Mail\CoralMailException;
use models\Logic\LogicMail;
use models\Table\TableMypageCustomer;
use models\Table\TableMypageTempRegist;
use models\Table\TableMypagePasswordHistory;
use models\Table\TableSystemProperty;
use models\Table\TableUser;
use models\Logic\LogicNormalizer;
use models\View\MypageViewOrder;

class RegistController extends CoralControllerAction {
    protected $_componentRoot = './application/views/components';

    /**
     * @var Application
    */
    private $app;

    /**
     * 初期化処理
    */
    protected function _init() {
        $this->app = Application::getInstance();

        // ページタイトルとスタイルシート、JavaScriptを設定
        if ($this->is_mobile_request())
        {
            $this->addStyleSheet( './css_sp/mypage.css' )
                 ->addStyleSheet( './css_sp/mypage_regist.css' );
        }
        else
        {
            $this->addStyleSheet( './css/mypage.css' )
                 ->addStyleSheet( './css/mypage_regist.css' );
        }
        $this->setPageTitle( '新規会員登録' )
            ->addJavaScript( '../js/prototype.js' )
            ->addJavaScript( '../js/bytefx.js' )
            ->addJavaScript( '../js/json+.js' )
            ->addJavaScript( '../js/corelib.js' )
            ->addJavaScript( '../js/base.ui.js' );

        // 県コードマスター作成
        $codeMaster = new CoralCodeMaster( $this->app->dbAdapter );
        $this->prefecture = $codeMaster->getMyPagePrefectureMaster();
    }

    /**
     * 仮登録
    */
    public function preregistAction() {
        $params = $this->getParams();

        // OemId取得
        if (!isset($params['orderseq'])) {
            $oemId = 0;
        } else {
            $mdlo = new MypageViewOrder( $this->app->dbAdapter );
            $order = $mdlo->find( $params['orderseq'] );

            if ($order->count() == 0) {
                $this->app->return404Error();
            }

            $oemId = $order->current()['OemId'];
        }

        $this->view->assign( 'oemid', $oemId );
        $this->view->assign( 'orderseq', $params['orderseq'] );

        $coralv = new CoralValidate();

        if( !isset( $params['mailaddress'] ) ) {
            $this->view->assign( 'current', 'mail' );
        }
        else {
            $error = '';
            $orderseq = $params['orderseq'];
            $mailAddress = $params['mailaddress'];
            if( empty( $mailAddress ) ) {
                $error = 'メールアドレスが未入力です。';
            }
            else if( !$coralv->isMail( $mailAddress ) ) {
                $error = 'メールアドレスに誤りがあります。';
            }
            if( $error == '' ) {
                $sql = ' SELECT * FROM T_MypageCustomer WHERE MailAddress = :MailAddress AND IFNULL( OemId, 0 ) = :OemId AND ValidFlg = 1 ';
                $prm = array(
                    ':MailAddress' => $mailAddress,
                    ':OemId' => $oemId
                );

                if( $this->app->dbAdapter->query( $sql )->execute( $prm )->current() ) {
                    $error = '登録済みのメールアドレスです。';
                }
            }

            if( $error != '' ) {
                $this->view->assign( 'current', 'mail' );
                $this->view->assign( 'error', $error );

                return $this->view;
            }

            try {
                $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();

                // システムプロパティからハッシュキー取得
                $mdlsp = new MypageViewSystemProperty( $this->app->dbAdapter );
                $hash_salt = $mdlsp->getValue( 'mypage', 'url', 'hash_salt' );

                // 作成日時
                $createDate = date( 'Y-m-d H:i:s' );
                // 有効日時
                $validDate = date( 'Y-m-d H:i:s', strtotime( '+ 1 day' ) );

                $prm = array(
                    'OemId' => $oemId,
                    'MailAddress' => $mailAddress,
                    'UrlParameter' => hash( 'sha256', sprintf( '%s%s%s', $mailAddress, $createDate, $hash_salt ) ),
                    'CreateDate' => $createDate,
                    'ValidDate' => $validDate,
                    'OrderSeq' => $orderseq,
                    'ValidFlg' => 1,
                );

                $mypageTempRegist = new TableMypageTempRegist($this->app->dbAdapter);
                $mypageTempRegist->saveNew($prm);

                $baseUrl = $this->app->dbAdapter->query(" SELECT KeyContent FROM MV_Code WHERE CodeId = 107 AND KeyCode = :KeyCode "
                    )->execute(array(':KeyCode' => $oemId))->current()['KeyContent'];

                // 仮登録完了メールの送信
                $lcmail = new LogicMail($this->app->dbAdapter, $this->app->smtpServer);
                $lcmail->SendPreregistMail($prm, $baseUrl);

                $this->app->dbAdapter->getDriver()->getConnection()->commit();

            } catch( CoralMailException $me ) {
                // メールの例外は破棄する。
            } catch( \Exception $e ) {
                $this->app->dbAdapter->getDriver()->getConnection()->rollback();
            }
            $this->view->assign( 'current', 'precomp' );
        }

        return $this->view;
    }

    /**
     * 本登録
     */
    public function registAction() {
        $param = $this->getParams();

        $sql = ' SELECT * FROM T_MypageTempRegist WHERE UrlParameter = :UrlParameter AND IFNULL(OemId,0) = :OemId ';
        $mypageRegist = $this->app->dbAdapter->query( $sql )->execute( array( ':UrlParameter' => $param['token'], ':OemId' => 0 ) )->current();

        if ($mypageRegist != false) {
            // 会員登録済みか判定
            $sql = ' SELECT * FROM T_MypageCustomer WHERE MailAddress = :MailAddress AND IFNULL( OemId, 0 ) = :OemId AND ValidFlg = 1 ';
            $prm = array(
                    ':MailAddress' => $mypageRegist['MailAddress'],
                    ':OemId' => $mypageRegist['OemId'],
            );
            if( $this->app->dbAdapter->query( $sql )->execute( $prm )->current() ) {
                // 会員登録済みの場合、エラー画面へ遷移
                $this->_redirect( 'regist/error' );
            }
        }

        if( $mypageRegist == false || strtotime( date( 'Y-m-d H:i:s' ) ) > strtotime( $mypageRegist['ValidDate'] ) || $mypageRegist['ValidFlg'] == 0 ) {
            $this->app->return404Error();
        }

        // 注文マイページからのリンクの場合
        if( !empty( $mypageRegist['OrderSeq'] ) ) {
            $mdlc = new MypageViewCustomer( $this->app->dbAdapter );
            $customerInfo = $mdlc->findCustomer( array( 'OrderSeq' => $mypageRegist['OrderSeq'] ) )->current();

            // ManCustId取得
            $sql  = ' SELECT mc.ManCustId ';
            $sql .= ' FROM   MV_Customer c LEFT OUTER JOIN ';
            $sql .= '        MV_EnterpriseCustomer ec ON ( ec.EntCustSeq = c.EntCustSeq ) LEFT OUTER JOIN ';
            $sql .= '        MV_ManagementCustomer mc ON ( mc.ManCustId = ec.ManCustId ) ';
            $sql .= ' WHERE  c.EntCustSeq = :EntCustSeq ';
            $manCustId = $this->app->dbAdapter->query( $sql )->execute( array( ':EntCustSeq' => $customerInfo['EntCustSeq'] ) )->current()['ManCustId'];

            // 郵便番号分割
            $postalCode = str_replace( '-', '', $customerInfo['PostalCode'] );
            $mypageRegist['PostalCode_1'] = substr( $postalCode, 0, 3 );
            $mypageRegist['PostalCode_2'] = substr( $postalCode, 3 );

            // 都道府県コード
            $mypageRegist['PrefectureCode'] = $customerInfo['PrefectureCode'];
            // 都道府県
            $mypageRegist['PrefectureName'] = $customerInfo['PrefectureName'];
            // 市区
            $mypageRegist['City'] = BaseGeneralUtils::convertNarrowToWide($customerInfo['City']);
            // 町村
            $mypageRegist['Town'] = BaseGeneralUtils::convertNarrowToWide($customerInfo['Town']);
            // 住所
            $mypageRegist['Address'] = $mypageRegist['City'] . $mypageRegist['Town'];
            // 建物
            $mypageRegist['Building'] = BaseGeneralUtils::convertNarrowToWide($customerInfo['Building']);
            if (empty($customerInfo['City'])) {
                // 住所－市区郡が未設定（CSV、APIからの登録）の場合、結合住所から設定
                $mypageRegist['Address'] = BaseGeneralUtils::convertNarrowToWide(str_replace($customerInfo['PrefectureName'], '', $customerInfo['UnitingAddress']));
                $mypageRegist['Building'] = '';
            }
            // ManCustId
            $mypageRegist['ManCustId'] = $manCustId;
            // 電話番号分割
            $phoneAr = explode('-', $customerInfo['Phone']);

            $phoneArCount = 0;
            if(!empty($phoneAr)) {
                $phoneArCount = count($phoneAr);
            }
            if ($phoneArCount == 3) {
                // ハイフンで三分割できた場合、そのまま使用
                $mypageRegist['Phone_1'] = $phoneAr[0];
                $mypageRegist['Phone_2'] = $phoneAr[1];
                $mypageRegist['Phone_3'] = $phoneAr[2];
            }
            else {
                // ハイフンで三分割できない場合、固定長で三分割
                $phone = str_replace( '-', '', $customerInfo['Phone'] );
                if(strlen($phone) == 10){
                    $mypageRegist['Phone_1'] = substr($phone, 0, 3 );
                    $mypageRegist['Phone_2'] = substr($phone, 3, 3 );
                    $mypageRegist['Phone_3'] = substr($phone, 6 );
                }else if(strlen($phone) == 11){
                    $mypageRegist['MobilePhone_1'] = substr($phone, 0, 3 );
                    $mypageRegist['MobilePhone_2'] = substr($phone, 3, 4 );
                    $mypageRegist['MobilePhone_3'] = substr($phone, 7 );
                }
            }
        }

        $mypageRegist['Birthday_y'] = date('Y');
        $sysPro = new MypageViewSystemProperty( $this->app->dbAdapter );
        $mypageRegist['MembershipAgreement'] = $sysPro->getValue('[DEFAULT]', 'systeminfo', 'MembershipAgreement');
        $mdlsp = new TableSystemProperty($this->app->dbAdapter);
        $mypageRegist['PasswdCount'] = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'PasswdCount');

        $this->view->assign( 'current', 'edit' );
        $this->view->assign( 'prefecture', $this->prefecture );
        $this->view->assign( 'data', $mypageRegist );

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

        $this->view->assign( 'prefecture', $this->prefecture );
        $sysPro = new MypageViewSystemProperty( $this->app->dbAdapter );
        $data['MembershipAgreement'] = $sysPro->getValue('[DEFAULT]', 'systeminfo', 'MembershipAgreement');
        $mdlsp = new TableSystemProperty($this->app->dbAdapter);
        $data['PasswdCount'] = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'PasswdCount');
        $this->view->assign( 'data', $data );

        // 検証エラーがあった場合は入力画面を表示
        if( !empty($errors) ) {
            $this->view->assign( 'current', 'edit' );
            $this->view->assign( 'error', $errors );

            $this->setTemplate( 'regist' );

            return $this->view;
        }

        $this->view->assign( 'current', 'conf' );

        // フォームデータ自身をエンコード
        $formData = base64_encode( serialize( $data ) );
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

        $sql = ' SELECT * FROM T_MypageCustomer WHERE MailAddress = :MailAddress AND IFNULL( OemId, 0 ) = :OemId AND ValidFlg = 1 ';
        $prm = array(
                ':MailAddress' => $data['MailAddress'],
                ':OemId' => nvl($data['OemId'], 0),
        );

        if( $this->app->dbAdapter->query( $sql )->execute( $prm )->current() ) {
            $this->_redirect( 'regist/error' );
        }
        else {
            // 認証ユーティリティ取得
            $authUtil = $this->app->getAuthUtility();

            // ハッシュ済みのログインパスワードを設定
            $data['LoginId'] = nvl($data['OemId'], 0). "@". $data['MailAddress'];
            $data['LoginPasswd'] = $authUtil->generatePasswordHash( $data['LoginId'], $data['LoginPasswd'] );
            $data['Hashed'] = 1;
            $data['LastLoginDate'] = null;
            $data['IdentityDocumentClass'] = 0;
            $data['RegNameKj'] = LogicNormalizer::create(LogicNormalizer::FILTER_FOR_NAME)->normalize( $data['NameSeiKj'] . $data['NameMeiKj']);
            $data['RegUnitingAddress'] = LogicNormalizer::create(LogicNormalizer::FILTER_FOR_ADDRESS)->normalize($data['UnitingAddress']);
            $data['RegPhone'] = LogicNormalizer::create(LogicNormalizer::FILTER_FOR_TEL)->normalize($data['Phone']);
            $data['RegMobilePhone'] = LogicNormalizer::create(LogicNormalizer::FILTER_FOR_TEL)->normalize($data['MobilePhone']);

            // トランザクション開始
            $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();

            try {
                $mypageCustomer = new TableMypageCustomer($this->app->dbAdapter);
                $mypageCustomer->saveNew( $data );

                // パスワード期限切れ日数(日)の取得
                $mdlsp = new TableSystemProperty($this->app->dbAdapter);
                $propValue = $mdlsp->getValue("[DEFAULT]", "systeminfo", "PasswdLimitDay");

                // パスワード履歴テーブルに１件追加
                $pwdata = array(
                         'Category'       => 5
                        ,'LoginId'        => $data['LoginId']
                        ,'LoginPasswd'    => $data['LoginPasswd']
                        ,'PasswdStartDay' => date('Y-m-d')
                        ,'PasswdLimitDay' => date('Y-m-d', strtotime("$propValue day"))
                        ,'Hashed'         => 1
                        ,'ValidFlg'       => 1
                );
                $mdlmph = new TableMypagePasswordHistory($this->app->dbAdapter);
                $mdlmph->saveNew($pwdata);

                // 登録完了メールの送信
                $lcmail = new LogicMail($this->app->dbAdapter, $this->app->smtpServer);
                $lcmail->SendRegistMail($data, $this->is_mobile_request());

                $this->app->dbAdapter->getDriver()->getConnection()->commit();
            } catch( \Exception $err ) {
                $this->app->dbAdapter->getDriver()->getConnection()->rollback();
                throw $err;
            }

            $this->_redirect( 'regist/completion' );
        }
    }

    /**
     * 確認画面からの戻り処理
     */
    public function backAction() {
        // エンコード済みのPOSTデータを復元する
        $data = unserialize( base64_decode( $this->params()->fromPost( 'hash' ) ) );

        $this->view->assign( 'current', 'edit' );
        $this->view->assign( 'prefecture', $this->prefecture );
        $this->view->assign( 'data', $data );

        $this->setTemplate( 'regist' );

        return $this->view;
    }

    /**
     * 登録完了画面の表示
     */
    public function completionAction() {
        $this->view->assign( 'current', 'comp' );

        return $this->view;
    }

    /**
     * 登録エラー画面の表示
     */
    public function errorAction() {
        $this->view->assign( 'current', 'comp' );

        return $this->view;
    }

    /**
     * 郵便番号を検索する。
     */
    public function searchzipAction() {
        try
        {
            $coralv = new CoralValidate();

            if (empty($_GET["zc"])) {
                $msg['PrefectureCode'] = '';
                $msg['CityKanji'] = '';
                $msg['TownKanji'] = '';
                $msg['ErrorMessage'] = '郵便番号が未入力です。';
            }
            else if (!$coralv->isPostCode( $_GET["zc"] )) {
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
            $errors[$key] = "名前は全角で入力してください。";
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

        // PostalCode: 郵便番号
        $key = 'PostalCode';
        $coralv = new CoralValidate();
        if( !isset( $errors[$key] ) && strlen( $data[$key] ) == 0 ) {
            $errors[$key] = "郵便番号が未入力です。";
        }
        if( !isset( $errors[$key] ) && !$coralv->isPostCode( $data[$key] ) ) {
            $errors[$key] = "郵便番号の形式が不正です。";
        }
        $sql = " SELECT * FROM MV_PostalCode WHERE PostalCode7 = :PostalCode7 ";
        if( !isset( $errors[$key] ) && !$this->app->dbAdapter->query( $sql )->execute( array( ':PostalCode7' => str_replace( '-', '', $data[$key] ) ) ) ) {
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
        if( !isset( $errors[$key] ) && strlen( $data[$key] ) > 0  && !(preg_match( '/^[- 0-9]+$/', $data[$key] )) ) {
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

        // LoginPasswd: パスワード
        $key = 'LoginPasswd';
        if( !isset( $errors[$key] ) && empty( $data[$key] ) ) {
            $errors[$key] = 'パスワードが未入力です。';
        }
        if( !isset( $errors[$key] ) && empty( $data['LoginPasswd2'] ) ) {
            $errors[$key] = 'パスワード（確認）が未入力です。';
        }
        if( !isset( $errors[$key] ) && $data['LoginPasswd'] != $data['LoginPasswd2'] ) {
            $errors[$key] = 'パスワードとパスワード（確認）が一致しません';
        }

        // パスワード文字数(桁)
        $mdlsp = new TableSystemProperty($this->app->dbAdapter);
        $passCnt = $mdlsp->getValue("[DEFAULT]", "systeminfo", "PasswdCount");

        //パスワード文字数
        $pass_len = strlen($data[$key]);

        if ( empty( $error ) && (!preg_match('/[a-z]/', $data[$key]) || !preg_match('/[A-Z]/', $data[$key]) || !preg_match('/[0-9]/', $data[$key])
        || !preg_match('/[ -\/:-@\[-`\{-\~]/', $data[$key]) || $pass_len < $passCnt) )
        {
            $errors[$key] = 'パスワードは英大文字、英小文字、数字、記号のすべてを含む' . $passCnt . '文字以上でご入力ください。';
        }

        // LoginPasswd2: パスワード（確認）　※スマホ用画面のみ
        $key = 'LoginPasswd2';
        if( !isset( $errors[$key] ) && empty( $data[$key] ) ) {
            $errors[$key] = 'パスワード（確認）が未入力です。';
        }

        if ($this->is_mobile_request())
        {
            $key = 'agree';
            if (empty( $data[$key] ) ||  $data[$key] != 1 ){
                $errors[$key] = '登録には、規約への同意が必要です。';
            }
        }

        return $errors;
    }

}
