<?php
namespace oemadmin;

use Zend\Config\Reader\Ini;
use Zend\Db\Adapter\Adapter;

use Coral\Base;
use Coral\Base\Application\BaseApplicationOem;
use Coral\Base\BaseLog;
use Coral\Base\BaseGeneralUtils;

use Coral\Coral;
use Coral\Coral\Auth\CoralAuthManager;
use Coral\Coral\CoralValidate;


use models;
use models\Table;
use models\Table\TableOem;
use models\Sequence\SequenceGeneral;
use Coral\Base\BaseDelegate;

use Coral\Coral\Auth\CoralAuthException;
use Coral\Coral\Auth\CoralAuthAttemptJudge;
use Coral\Coral\Auth\AttemptJudge\CoralAuthAttemptJudgeSetting;
use models\Logic\LogicOemClaimAccount;
use models\Logic\LogicSmbcRelation;
use models\Logic\Jnb\LogicJnbCommon;
use models\Logic\Smbcpa\LogicSmbcpaCommon;
use models\Logic\LogicImportantMessages;
use models\Logic\LogicAccountValidity;
use models\Logic\AccountValidity\LogicAccountValidityPasswordValidator;
use models\Logic\AccountValidity\LogicAccountValidityException;
use models\Table\TableCode;

/**
 * アプリケーションクラスです。
 *
 */
class Application extends BaseApplicationOem {
    protected $_application_id = 'OemAdminApplication';

    // アプリケーションディレクトリにアクセスするURLのサブディレクトリ名
    protected $_subDirName = 'admin';

    /**
     * Application の唯一のインスタンスを取得します。
     *
     * @static
     * @access public
     * @return Application
     */
    public static function getInstance() {
        if( self::$_instance === null ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Application の新しいインスタンスを初期化します。
     *
     * @ignore
     * @access private
     */
    private function __construct() {
        parent::init();
        $this->run();
    }

    /**
     * @var Adapter
     */
    public $dbAdapter;

    /**
     * @var CoralAuthManager
     */
    public $authManagerAdmin;

    /**
     * @var BaseControllerPluginAuthentication
     */
    public $authPlugin;

    /**
     * サービスベースツールのURL
     */
    public $tools;

    /**
     * ビジネスモデルに絡む属性
     */
    public $business;

    /**
     * メール環境
     */
    public $mail;

    /**
     * 標準ログクラス
     *
     * @var BaseLog
     */
    public $logger;

    /**
     * ページング設定
     *
     * @var Zend_Config_Ini
     */
    public $paging_conf;

    /**
     * アプリケーション設定ファイルのパス
     *
     * @var string
     */
    public $configPath;

    /**
     * Logic_StampFeeの初期化設定データ
     *
     * @var array
     */
    public $stampFeeLogicSettings;

    /**
     * 認証ヘルパーロジック
     *
     * @access protected
     * @var CoralAuthAttemptJudge
     */
    protected $_attemptJudge;

    /**
     * モジュールの設定値
     * @var array
     */
    public $config;

    /**
     * アプリケーションを実行します。
     *
     * @access public
     */
    public function run() {
        // アクセスディレクトリが適切かをチェック
        $this->checkInvalidOemId();

        // [cbadmin\config\config.ini]の値の取得
        $inidata = array();
        $file = __DIR__ . '/config/config.ini';
        if (file_exists($file))
        {
            $reader = new Ini();
            $inidata = $reader->fromFile($file);
        }

        // データベースアダプタをiniファイルから初期化します
        $array_con = $inidata['database'];
        $this->dbAdapter = new Adapter($array_con);

        // 設定をシステムプロパティテーブルから読み込み
        $data = $this->getApplicationiInfo($this->dbAdapter, __NAMESPACE__);
        // iniファイルの内容を、DB設定値で上書きマージ（マスタ設定＞iniファイル）
        $data = array_merge($inidata, $data);

        // 値の設定
        // アプリケーション設定ファイルのパス
        $this->configPath = $file;

        // 汎用シーケンスを別接続で使用する
        SequenceGeneral::setDbConnectionParams( $array_con );

        // 設定を変数に保存
        $this->config = $data;

        // サービスベースツールのURL
        $this->tools = $data['tools'];

        // ビジネスモデルに絡む属性
        $this->business = $data['business'];

        // メールに絡む属性
        $this->mail = $data['mail'];

        // ページング設定
        $this->paging_conf = $data['paging'];

        // ログ設定の読み込み
        $logConfig = $data['log'];

        // 標準ログクラス初期化
        $this->logger = BaseLog::createFromArray( $logConfig );

        // アップロードマネージャの初期化（08.04.10追加）
        $upload  = $data['upload'];
        if (!is_null($upload)) {
            $root_dir = key_exists('root_directory', $upload) ? $upload['root_directory'] : null;
            if (!is_null($root_dir)) {
                try {
                    Coral\CoralUploadManager::setRootDirectory( $root_dir );
                } catch(\Exception $err) {
                    $this->logger->err( $err->getMessage() );
                }
            }
        }

        // 認証マネージャを初期化します
        $array_auth = $data['auth_manager'];
        $this->authManagerAdmin = new CoralAuthManager(
            $this->dbAdapter,
            $array_auth['tablename'],
            $array_auth['id_column'],
            $array_auth['password_column'],
            __NAMESPACE__ . $this->getApplicationId() . \Zend\Authentication\Storage\Session::NAMESPACE_DEFAULT
        );
        if (key_exists('log_enabled', $array_auth) && $array_auth['log_enabled']) {
            $this->authManagerAdmin->setLogger($this->logger);
        }

        // 代理認証設定を適用
        $this->authManagerAdmin = $this->setupAlternativeAuthSetting($this->authManagerAdmin, $data['alt_auth_settings']);

        // 追加認証処理を登録
        $this->authManagerAdmin->addAuthentication(new BaseDelegate($this, 'checkAccount'));

//zzz ↓実際の移送にはこれが必要？(20150616_1435)
        // 認証処理前後のコールバックを登録する
        $this->authManagerAdmin
            ->setBeforeAuthenticateCallback(new BaseDelegate($this, 'onBeforeAuthentication'))
            ->setAfterAuthenticateCallback(new BaseDelegate($this, 'onAfterAuthentication'));
//zzz ↑実際の移送にはこれが必要？(20150616_1435)

//      // イベントコールバックプラグインの設定
//      $this->addClass('NetB_Controller_Plugin_Callback');
//      $callbackPlugin = new NetB_Controller_Plugin_Callback();
//      $callbackPlugin
//          ->addPreDispatch( new NetB_Delegate($this, 'onEventRaised') )
//          ->addPostDispatch( new NetB_Delegate($this, 'onEventRaised') )
//          ;
//      $front->registerPlugin( $callbackPlugin );

        // Logic_StampFee向け設定データをロード
        $this->stampFeeLogicSettings = $this->loadStampFeeConfig();

        // OEM請求口座ロジックにデフォルトのロガーを設定
        LogicOemClaimAccount::setDefaultLogger($this->logger);

        // SMBC決済ステーション連携ロジックのサービス設定をロード
        LogicSmbcRelation::loadServiceConfig($data['smbc_relation']);

        // JNB共通抽象ロジックにデフォルトロガーとメールサーバ情報を設定
        LogicJnbCommon::setDefaultLogger($this->logger);
        if(isset($this->mail['smtp'])) {
            LogicJnbCommon::setDefaultSmtpServer($this->mail['smtp']);
        }
        if(isset($this->mail['charset'])) {
            LogicJnbCommon::setDefaultMailCharset($this->mail['charset']);
        }

        // SMBCバーチャル口座共通抽象ロジックにデフォルトロガーとメールサーバ情報を設定
        LogicSmbcpaCommon::setDefaultLogger($this->logger);
        if(isset($this->mail['smtp'])) {
            LogicSmbcpaCommon::setDefaultSmtpServer($this->mail['smtp']);
        }
        if(isset($this->mail['charset'])) {
            LogicSmbcpaCommon::setDefaultMailCharset($this->mail['charset']);
        }

        // 重要メッセージコンテナを初期化
        LogicImportantMessages::setDefaultNamespace($this->getApplicationId() . '_important_messages');

        // 認証済みなら重要メッセージを構築
       	if($this->authManagerAdmin->isAuthenticated()) {
            $this->initPasswordAlert($this->authManagerAdmin->getUserInfo()->LoginId);
        }

        // パスワード検証ロジックを初期化する
        $this->initPasswordValidator();
    }

    /**
     * このアプリケーション固有の処理を行う認証マネージャ向けの追加認証処理
     *
     * @param string $userId 認証ID
     * @param string $password 認証パスワード
     * @param mixed $rowObj 本認証成功で取得したアカウントデータ
     * @param null | mixed $altRowObj 代理認証時に取得した代理アカウントデータ
     * @return boolean 認証成功ならtrue、それ以外はfalse
     */
    public function checkAccount($userId, $password, $rowObj, $altRowObj = null) {
        // アクセスされたURLに含まれるOEM識別IDと認証アカウントのOemIdが一致するかをチェック
        $oemTable = new TableOem($this->dbAdapter);
        $dbRow = $oemTable->findByAccessId($this->getOemAccessId())->current();

        // OEM識別子からOEMが見つからないので認証失敗
        if(!$dbRow) return false;

        // OEM識別IDが一致しないので認証失敗
        if($dbRow['OemId'] != $rowObj->OemId) return false;

        // TODO: アカウントの有効性をチェック

        // ここまで到達したらOK
        return true;
    }

    /**
     * リクエスト内容をログ出力するための汎用ディスパッチイベントハンドラ
     */
    public function onEventRaised() {
//zzz この関数は本当に未使用で良いのかい？(20150616_1340)
        $this->addClass('Zend_Json');

        $loginId = $this->authManagerAdmin->getUserInfo()->LoginId;
        $host = f_get_client_address();     // 2015/09/23 Y.Suzuki Mod f_get_client_address をｺｰﾙするように変更

        $args = func_get_args();
        $eventName = count($args) == 1 ? $args[0] : $args[1];

        $req = $this->_frontController->getRequest();
        $target_path = $req->getControllerName() . '/' . $req->getActionName();
        $params = Zend_Json::encode($this->_fixEventParams($req->getParams()));

        $this->logger->info(sprintf(
            '[dispatch event] %s event raised. addr = %s, user = %s, action = %s, params = %s',
            $eventName, $host, $loginId, $target_path, $params) );
    }

    /**
     * ディスパッチイベントのログ出力向けに入力パラメータ内容を整形する
     *
     * @access protected
     * @var array $params 入力パラメータを格納した連想配列
     * @return array 整形済みパラメータ連想配列
     */
    protected function _fixEventParams($params = array()) {
        $ignore_keys = array('controller', 'action', 'module');
        $mask_targets = array(
                'opw',
                'npw1',
                'npw2',
                'LoginPasswd',
                'op_passwd'
        );

        $results = array();
        foreach($params as $key => $value) {
            if(in_array($key, $ignore_keys)) continue;
            if(is_array($value)) {
                $results[$key] = $this->_fixEventParams($value);
            } else {
                $results[$key] = in_array($key, $mask_targets) ? '*****' : $value;
            }
        }
        return $results;
    }

    /**
     * LogicStampFee向けの設定データをロードする
     * (設定JSONを廃止し、T_SystemProperty[Category=taxconf]から取得するよう変更（2014.9.12 eda）)
     *
     * @access protected
     * @return array Logic_StampFeeを初期化する設定データ
     */
    protected function loadStampFeeConfig() {
        $sysProps = new models\Table\TableSystemProperty($this->dbAdapter);
        return $sysProps->getStampFeeSettings();
    }

    /**
     * OEM向けCSS設定
     */
    public function getOemCss(){
        $oem = new TableOem($this->dbAdapter);
        $code = new TableCode($this->dbAdapter);

        // OEMを取得
        $row = $oem->findByAccessId($this->getOemAccessId())->current();
        // 指定のCSSを取得
        $css = $code->find('102', $row['StyleSheets'])->current();
        if (!$css) {
            $css = $code->find('102', '0')->current();; // 取得に失敗した場合はデフォルト値
        }
        return '../../oemadmin/css/vender/default/' . $css['Class1'];
    }

    /**
     * 現在の事業者のOEM情報を連想配列で取得
     *
     * @return array 現在の事業者のOEM情報
     */
    public function getOemInfo() {
        $oem = new TableOem($this->dbAdapter);
        return $oem->findOem2($this->authManagerAdmin->getUserInfo()->OemId)->current();
    }

    /**
     * 現在アクセスしているURLのOEM情報を連想配列で取得
     *
     * @return array
     */
    public function getCurrentOemData() {
        $oem = new TableOem($this->dbAdapter);
        $row = $oem->findByAccessId($this->getOemAccessId())->current();

        $ret = $row ? $row : array();

        $mime = "image/jpeg";

        $ret['LargeLogo'] = is_null($ret['LargeLogo']) ? null : 'data:'.$mime.';base64,'.$ret['LargeLogo'];
        $ret['SmallLogo'] = is_null($ret['SmallLogo']) ? null : 'data:'.$mime.';base64,'.$ret['SmallLogo'];

        return $ret;
    }

    /**
     * OEMサービス名称取得
     */
    public function getOemServiceName(){
        $oemData = $this->getCurrentOemData();
        return $oemData['ServiceName'];
    }

    /**
     * 認証マネージャのインスタンスに代理認証設定を適用する
     *
     * @access protected
     * @param CoralAuthManager $mgr 設定を適用する認証マネージャ
     * @param array $ini システム設定
     * @return CoralAuthManager 代理認証設定を適用済みの認証マネージャ
     */
    protected function setupAlternativeAuthSetting(CoralAuthManager $mgr, array $ini) {
        try {
            // セクション alt_auth_settings が記述されている場合のみ適用
            $modes = array(
                    CoralAuthManager::ALT_TABLE_SINGLECOLON,
                    CoralAuthManager::ALT_TABLE_DOUBLECOLON
            );
            foreach($modes as $mode) {
                if(isset($ini[$mode])) {
                    $mgr->setAlternativeAuthTableSetting($mode, $ini[$mode]);
                }
            }
        } catch(Exception $err) {
        }
        return $mgr;
    }

    /**
     * 認証ユーティリティを取得する
     *
     * @return BaseAuthUtility
     */
    public function getAuthUtility() {
        $sysProps = new models\Table\TableSystemProperty($this->dbAdapter);
        return new Base\Auth\BaseAuthUtility($sysProps->getHashSalt());
    }

    /**
     * 認証マネージャから認証処理前コールバックが呼び出された
     *
     * @param string $loginId ログインID
     * @param string $altLoginId 代理認証用代理ログインID
     */
    public function onBeforeAuthentication($loginId, $altLoginId) {
        $this->logger->debug(sprintf('[onBeforeAuthentication] loginId = %s, altLoginId = %s', $loginId, $altLoginId));
        $logic = $this->initAttemptJudgeLogic();
        if($logic->judgeLockedByLoginId($loginId)) {
            // IDレベルロックアウト
            $logic->appendInvalidAuthenticationLog($loginId,
                                                   $altLoginId,
                                                   f_get_client_address(),      // 2015/09/23 Y.Suzuki Mod f_get_client_address をｺｰﾙするように変更
                                                   $_SERVER['HTTP_USER_AGENT'],
                                                   true,
                                                   $this->getOemAccessId());
            CoralAuthException::throwIdLockedException(sprintf("ログインID '%s' はロックされています。サポートまでご連絡ください", $loginId));
        }
        if($logic->judgeLockedByClientInfo($loginId,$altLoginId, f_get_client_address(), $_SERVER['HTTP_USER_AGENT'])) {    // 2015/09/23 Y.Suzuki Mod f_get_client_address をｺｰﾙするように変更
            // クライアントレベルロックアウト
            $logic->appendInvalidAuthenticationLog($loginId,
                                                   $altLoginId,
                                                   f_get_client_address(),      // 2015/09/23 Y.Suzuki Mod f_get_client_address をｺｰﾙするように変更
                                                   $_SERVER['HTTP_USER_AGENT'],
                                                   false,
                                                   $this->getOemAccessId());
            CoralAuthException::throwClientLockedException('お使いのアカウントは現在利用を一時停止しています。30分ほど後に再度お試しください');
        }
    }

    /**
     * 認証マネージャから認証処理後コールバックが呼び出された
     *
     * @param string $loginId ログインID
     * @param string $altLoginId 代理認証用代理ログインID
     * @param boolean $authResult 認証成否を示すbool値
     */
    public function onAfterAuthentication($loginId, $altLoginId, $authResult) {
        $this->logger->debug(sprintf('[onAfterAuthentication] result: %s, loginId = %s, altLoginId = %s', $authResult ? 'SUCCESS' : 'FAILURE', $loginId, $altLoginId));
        // ロックアウトチェックの前にパスワード有効期限切れチェックを実施
        try {
            $this->checkPasswordValidity($loginId);
        } catch(LogicAccountValidityException $validityError) {
            // 有効期限切れ例外はそのまま上位へスロー
            throw $validityError;
        } catch(\Exception $err) {
            // その他の例外はアカウントが見つからないケースなのでとりあえず握りつぶす
        }

        $logic = $this->initAttemptJudgeLogic();
        $logic->appendAuthenticationLog($loginId,
                                        $altLoginId,
                                        f_get_client_address(),     // 2015/09/23 Y.Suzuki Mod f_get_client_address をｺｰﾙするように変更
                                        $_SERVER['HTTP_USER_AGENT'],
                                        $authResult,
                                        $this->getOemAccessId());

        // 認証成功なのでここで処理を抜ける
        if($authResult) return;

        if($logic->judgeLockedByLoginId($loginId)) {
            // IDレベルロックアウト
            CoralAuthException::throwIdLockedException(sprintf("ログインID '%s' はロックされています。サポートまでご連絡ください", $loginId));
        }
        if($logic->judgeLockedByClientInfo($loginId, $altLoginId, f_get_client_address(), $_SERVER['HTTP_USER_AGENT'])) {       // 2015/09/23 Y.Suzuki Mod f_get_client_address をｺｰﾙするように変更
            // クライアントレベルロックアウト
            CoralAuthException::throwClientLockedException('お使いのアカウントは現在利用を一時停止しています。30分ほど後に再度お試しください');
        }
    }

    /**
     * 認証ヘルパーロジックを初期化する
     * @access protected
     * @return CoralAuthAttemptJudge
     */
    protected function initAttemptJudgeLogic() {
        if($this->_attemptJudge == null) {
            $this->_attemptJudge = new CoralAuthAttemptJudge($this->dbAdapter, CoralAuthAttemptJudge::APP_OEMADMIN);
            $this->_attemptJudge
                ->setClientLevelLockoutSetting(CoralAuthAttemptJudgeSetting::createClientLevelSetting($this->dbAdapter))
                ->setIdLevelLockoutSetting(CoralAuthAttemptJudgeSetting::createIdLevelSetting($this->dbAdapter));
        }
        return $this->_attemptJudge;
    }

    /**
     * 指定アカウントのパスワード有効期限をチェック。
     * このメソッドは、有効期限切れを検出した場合に例外をスローし、それ以外は値をなにも返さない
     *
     * @access protected
     * @param string $loginId ログインID
     */
    protected function checkPasswordValidity($loginId) {
        $logic = new LogicAccountValidity($this->dbAdapter);
        $appName = LogicAccountValidity::APPNAME_OEMADMIN;

        // 有効期限切れ時は例外をスロー
        if($logic->passwordIsExpired($appName, $loginId)) {
            throw new Logic_AccountValidity_Exception('お使いのアカウントはパスワードの有効期限を過ぎています。詳しくはサポートまでご連絡ください');
        }

        // アラートメッセージを初期化
        $this->initPasswordAlert($loginId);
    }

    /**
     * パスワード期限切れ前のアラートメッセージを初期化する
     *
     * @access protected
     * @param string $loginId ログインID
     */
    protected function initPasswordAlert($loginId) {
        $logic = new LogicAccountValidity($this->dbAdapter);
        $appName = LogicAccountValidity::APPNAME_OEMADMIN;

        try {
            $msg_logic = new LogicImportantMessages();
            if($logic->needAlertForAccount($appName, $loginId)) {
                // 有効期限間近なら期限日とアラート表示フラグを設定し、重要メッセージを登録する
                $pswExpireDate = $logic->getExpireDate($appName, $loginId);
                $msg_logic->addMessage(sprintf('お使いのパスワードは%sで有効期限切れとなりますので、早めにパスワードを変更してください',
                                               date('Y年n月j日', strtotime($pswExpireDate))));
            } else {
                // アラート表示の必要がなければ重要メッセージをクリア
                $msg_logic->clearMessages();
            }
        } catch(\Exception $err) {
            // エラー時はなにもしない
        }
    }

    /**
     * パスワード検証ロジックを初期化する
     *
     * @access protected
     */
    protected function initPasswordValidator() {
        $logic = new LogicAccountValidity($this->dbAdapter);
        LogicAccountValidityPasswordValidator::setValidationEnabled($logic->passwordValidityEnabled());
    }
}
