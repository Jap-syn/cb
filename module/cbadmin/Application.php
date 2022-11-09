<?php
namespace cbadmin;

use Zend\Config\Reader\Ini;
use Zend\Db\Adapter\Adapter;

use Coral\Base;
use Coral\Base\Application\BaseApplicationAbstract;
use Coral\Base\BaseLog;
use Coral\Base\BaseGeneralUtils;

use Coral\Coral;
use Coral\Coral\Auth\CoralAuthManager;
use Coral\Coral\CoralValidate;


use models;
use models\Table;
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

/**
 * アプリケーションクラスです。
 *
 */
class Application extends BaseApplicationAbstract {
    protected $_application_id = 'CbAdminApplication';

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
     * 標準ログクラス
     *
     * @var BaseLog
     */
    public $sbpsLogger;

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

        // ログ設定の読み込み
        $logConfig = $data['log'];

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

        // ログ設定の読み込み
        $logConfig = $data['sbpslog'];
        $this->sbpsLogger = BaseLog::createFromArray( $logConfig );

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

        //         $this->logger->EMERG( 'EMERG_test' );
        //         $this->logger->ALERT( 'ALERT_test' );
        //         $this->logger->CRIT( 'CRIT_test' );
        //         $this->logger->ERR( 'ERR_test' );
        //         $this->logger->WARN( 'WARN_test' );
        //         $this->logger->NOTICE( 'NOTICE_test' );
        //         $this->logger->INFO( 'INFO_test' );
        //         $this->logger->DEBUG( 'DEBUG_test' );

        //         $start = '1990/8/13';
        //         $end = '2020/8/13';
        //         $this->logger = BaseGeneralUtils::CalcSpanDays2($start, $end);

        //         $len = '2';
        //         $d = '0';
        //         $s= '3';
        //         $this->logger = CoralValidate::checkBetween($len,$d,$s);
        //         echo "<br>"; var_dump($this->logger);

        // アップロードマネージャの初期化（08.04.10追加）
        //$data['upload']['root_directory']
        //         $upload  = $data['upload'];
        // var_dump($upload);
        //         $root_dir = key_exists('root_directory', $upload) ? $upload['root_directory'] : 'areare';

        // var_dump($root_dir);


        //         if (is_null($upload)) {
        //             var_dump($upload['root_directory']);
        //             echo "<br>";
        //         }

        //         try {
        //             $this->addClass( 'Coral_UploadManager' );
        //             $all_config = new Zend_Config_Ini($configPath, null);
        //             Coral_UploadManager::setRootDirectory( $all_config->upload->root_directory );
        //         } catch(Exception $err) {
        //             $this->logger->err( $err->getMessage() );
        //         }


        //
        // *** 以下、認証関連 ***
        //
        //        //認証マネージャと認証管理プラグインのクラス定義をロードします
        //         $this->addClass('Coral_Auth_Manager')
        //         ->addClass('NetB_Controller_Plugin_Authentication');

        // 認証マネージャを初期化します

        $array_auth = $data['auth_manager'];
        $this->authManagerAdmin = new CoralAuthManager(
            $this->dbAdapter,
            $array_auth['tablename'],
            $array_auth['id_column'],
            $array_auth['password_column'],
            __NAMESPACE__ . \Zend\Authentication\Storage\Session::NAMESPACE_DEFAULT
        );
        if (key_exists('log_enabled', $array_auth) && $array_auth['log_enabled']) {
            $this->authManagerAdmin->setLogger($this->logger);
        }

        // 追加認証処理を登録
        $this->authManagerAdmin->addAuthentication(new BaseDelegate($this, 'checkAccount'));

        // 認証処理前後のコールバックを登録する
        $this->authManagerAdmin
        ->setBeforeAuthenticateCallback(new BaseDelegate($this, 'onBeforeAuthentication'))
        ->setAfterAuthenticateCallback(new BaseDelegate($this, 'onAfterAuthentication'));

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
		// アカウントが無効な場合はNG
		if(!$rowObj->ValidFlg) {
			return false;
		}

		// ここまで到達したら認証OK
		return true;
    }

    /**
     * LogicStampFee向けの設定データをロードする
     * (設定JSONを廃止し、T_SystemProperty[Category=taxconf]から取得するよう変更（2014.9.12 eda）)
     *
     * @return array Logic_StampFeeを初期化する設定データ
     */
    protected function loadStampFeeConfig() {
        $sysProps = new models\Table\TableSystemProperty($this->dbAdapter);
        return $sysProps->getStampFeeSettings();
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
     * サイトごとの初回請求用紙レイアウト指定を許可するかの設定を取得する
     *
     * @return boolean
     */
    public function allowFirstClaimLayoutSetting() {
        // config.iniのprinting.allow_fc_layout_settingの設定を返す（2014.11.07）
        // TODO: 決済ステーション完全対応版リリース時は常にtrueを返すようにする
        try {
            $printConfig = $this->config['printing'];

            if($printConfig && isset($printConfig['allow_fc_layout_setting'])) {
                return $printConfig['allow_fc_layout_setting'] ? true : false;
            } else {
                return false;
            }
        } catch(\Exception $err) {}
        return false;
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
            $logic->appendInvalidAuthenticationLog($loginId, $altLoginId, f_get_client_address(), $_SERVER['HTTP_USER_AGENT'], true);       // 2015/09/23 Y.Suzuki Mod f_get_client_address をｺｰﾙするように変更
            CoralAuthException::throwIdLockedException(sprintf("ログインID '%s' はロックされています。サポートまでご連絡ください", $loginId));
        }
        if($logic->judgeLockedByClientInfo($loginId, $altLoginId, f_get_client_address(), $_SERVER['HTTP_USER_AGENT'])) {       // 2015/09/23 Y.Suzuki Mod f_get_client_address をｺｰﾙするように変更
            // クライアントレベルロックアウト
            $logic->appendInvalidAuthenticationLog($loginId, $altLoginId, f_get_client_address(), $_SERVER['HTTP_USER_AGENT'], false);      // 2015/09/23 Y.Suzuki Mod f_get_client_address をｺｰﾙするように変更
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
        $logic->appendAuthenticationLog($loginId, $altLoginId, f_get_client_address(), $_SERVER['HTTP_USER_AGENT'], $authResult);       // 2015/09/23 Y.Suzuki Mod f_get_client_address をｺｰﾙするように変更

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
            $this->_attemptJudge = new CoralAuthAttemptJudge($this->dbAdapter, CoralAuthAttemptJudge::APP_CBADMIN);

            $this->_attemptJudge->setClientLevelLockoutSetting(CoralAuthAttemptJudgeSetting::createClientLevelSetting($this->dbAdapter));
            $this->_attemptJudge->setIdLevelLockoutSetting(CoralAuthAttemptJudgeSetting::createIdLevelSetting($this->dbAdapter));
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
        $appName = LogicAccountValidity::APPNAME_CBADMIN;

        // 有効期限切れ時は例外をスロー
        if($logic->passwordIsExpired($appName, $loginId)) {
            throw new LogicAccountValidityException('このアカウントはパスワードの有効期限を過ぎています。システム管理者までご連絡ください');
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
        $appName = LogicAccountValidity::APPNAME_CBADMIN;
        try {
            $msg_logic = new LogicImportantMessages();
            if($logic->needAlertForAccount($appName, $loginId)) {
                // 有効期限間近なら期限日とアラート表示フラグを設定し、重要メッセージを登録する
                $pswExpireDate = $logic->getExpireDate($appName, $loginId);
                $msg_logic->addMessage(sprintf('お使いのパスワードは%sで有効期限切れとなりますので、早めにパスワードを変更してください',
                    date('Y年n月j日', strtotime($pswExpireDate))));
            }
            else {
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
