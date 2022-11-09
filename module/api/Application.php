<?php
namespace api;

use Coral\Base\Application\BaseApplicationAbstract;
use Coral\Base\Application\BaseApplicationOem;
use Zend\Db\Adapter\Adapter;
use Coral\Base\Auth\BaseAuthManager;
use Coral\Base\BaseLog;
use Zend\Config\Reader\Ini;
use models\Sequence\SequenceGeneral;
use models\Logic\CreditJudge\LogicCreditJudgeAbstract;
use models\Logic\CreditJudge\LogicCreditJudgeSequencer;
use models\Logic\LogicOemClaimAccount;
use models\Logic\LogicSmbcRelation;
use models\Logic\Jnb\LogicJnbCommon;
use models\Logic\Smbcpa\LogicSmbcpaCommon;
use models\Table\TableSystemProperty;
use Coral\Base\Auth\BaseAuthUtility;

/**
 * アプリケーションクラスです。
 *
 */
class Application extends BaseApplicationOem {
    const TH_OPTTION_GROUP_NAME = 'group_name';

    const TH_OPTION_THREAD_LIMIT = 'thread_limit';

    const TH_OPTION_LOCKWAIT_TIMEOUT = 'lockwait_timeout';

    const TH_OPTION_LOCK_RETRY_INTERVAL = 'lock_retry_interval';

    protected $_application_id = 'ApiApplication';

    /**
     * OEM対応機能がアクティブであるかのフラグ
     *
     * @access protected
     * @var boolean
     */
    protected $_oemActive = false;

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
     * @var BaseAuthManager
     */
    public $authManager;

//     /**
//      * @var NetB_Controller_Plugin_Authentication
//      */
//     public $authPlugin;

    /**
     * アプリケーションのグローバル設定
     *
     * @var array
     */
    public $appGlobalConfig;

    /**
     * 注文登録設定
     *
     * @var array
     */
    public $orderItemConfig;

    /**
     * 設定ファイルのルートディレクトリパス
     *
     * @var string
     */
    public $configRoot;

    /**
     * STMPサーバのホスト名
     *
     * @var string
     */
    public $smtpServer;

    /**
     * ページング設定
     *
     * @var array
     */
    public $paging_conf;

    /**
     * 事業者加算点一覧
     *
     * @var array
     */
    public $entIds;

	/**
	 * 同梱印刷ツール設定
	 *
	 * @var array
	 */
	public $selfBillingConfig;

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
     * スレッドプール関連オプション
     *
     * @access protected
     * @var array
     */
    protected $_threadOptions;

    /**
     * api\config\config.ini の内容をすべて保持する
     * @var array
     */
    public $config;

    /**
     * スレッド上限使用サーバー
     *
     * @access protected
     * @var boolean
     */
    protected $_nonUseThreadPoolServer;

    /**
     * アプリケーションを実行します。
     *
     * @access public
     */
    public function run() {
//         $this->configRoot = './application/config';

        $data = array();
        $file = __DIR__ . '/config/config.ini';
        if (file_exists($file))
        {
            $reader = new Ini();
            $data = $reader->fromFile($file);
        }
        // データベースアダプタをiniファイルから初期化します
        $configPath = $file;
        $array_con = $data['database'];
        $this->dbAdapter = new Adapter($array_con);

        // 設定をシステムプロパティテーブルから読み込み
        $apInfo = $this->getApplicationiInfo($this->dbAdapter, __NAMESPACE__);
        // iniファイルの内容を、DB設定値で上書きマージ（マスタ設定＞iniファイル）
        $data = array_merge($data, $apInfo);

        $this->config = $data;

        // 汎用シーケンスを別接続で使用する
        SequenceGeneral::setDbConnectionParams( $array_con );

        // アプリケーションのグローバル設定をロード
        $this->appGlobalConfig = $data['application_global'];
        ini_set( 'display_errors', $this->appGlobalConfig['php_display_errors']);

        // SMTPサーバ情報を初期化
        $this->smtpServer = empty( $this->appGlobalConfig['smtp_server'] ) ? 'localhost' : $this->appGlobalConfig['smtp_server'];

        // 注文登録設定をロード
        $this->orderItemConfig = $data['order_item'];

        // ページング設定（08.04.03追加）
        $this->paging_conf = $data['paging'];

        // 加算点一覧設定をロード（cbadminのini)から
        $cbadminConfigRoot = dirname(__DIR__) . '/cbadmin/config';
        $cbConfigPath = $cbadminConfigRoot . '/config.ini';

        $cbdata = array();
        if (file_exists($cbConfigPath))
        {
            $reader = new Ini();
            $cbdata = $reader->fromFile($cbConfigPath);
        }
        $cbdata = array_merge($cbdata, $this->getApplicationiInfo($this->dbAdapter, 'cbadmin'));

        $cj_info = $cbdata['credit_judge'];
        $this->entIds = $cj_info['enterpriseid'];

        // 同梱印刷ツール設定をロード(memberのini)から
        $memberConfigRoot = dirname(__DIR__) . '/member/config';
        $memberConfigPath = $memberConfigRoot. '/config.ini';

        $mdata = array();
        if (file_exists($memberConfigPath))
        {
            $reader = new Ini();
            $mdata = $reader->fromFile($memberConfigPath);
        }
        $mdata = array_merge($mdata, $this->getApplicationiInfo($this->dbAdapter, 'member'));

        // 請求書同梱ツール設定の構築
        $default_sbconfig = array(		// デフォルト設定
                'use_selfbilling' => false,
                'payment_limit_days' => 14,
                'threshold_version' => null,
                'target_list_limit' => 250,
                'shipping_sp_count' => 30
        );

        // Logic_StampFee向け設定データをロード
        $this->stampFeeLogicSettings = $this->loadStampFeeConfig();

        $runtime_sbconfig = array();	// ランタイム設定
        try {
            // ランタイム設定をiniから読み込んで上書き
            $selfBillingConfig = $mdata['selfbilling'];
            $runtime_sbconfig = $selfBillingConfig;
        } catch(Exception $err) {
            // nop
        }
        // デフォルト設定をランタイム設定で上書きして初期化
        if (is_array($runtime_sbconfig)) {
        $this->selfBillingConfig = array_merge($default_sbconfig, $runtime_sbconfig);
        }

        // ログ設定の読み込み
        try {
            $logConfig = $data['log'];
//             $logConfig = $logConfig->toArray();
        } catch(Exception $err) {
            // iniのセクションが存在しない場合はログを使用しない
            $logConfig = array();
        }
        // 標準ログクラス初期化
        $this->logger = BaseLog::createFromArray( $logConfig );

        // ログ設定の読み込み
        $logConfig = $data['sbpslog'];
        $this->sbpsLogger = BaseLog::createFromArray( $logConfig );

        // OEM関連初期設定
        $this->initOemSettings($this->appGlobalConfig);

        // スレッド上限サーバー設定
        $this->initNonUseThreadPoolServer($this->appGlobalConfig);

//         // フロントコントローラを初期化します
//         $front = $this->getFrontController();

// ↓↓↓20150130 suzuki ServiceAbstractにて、IPアドレス、パラメーター内容によるアクセス制御が行われるので、認証は不要OK ↓↓↓
//         // アクセス制御のために、認証マネージャと認証プラグインをダミーDBで初期化（09.06.16 eda）
//         $this
//         ->addClass('NetB_Auth_Manager')
//         ->addClass('NetB_Controller_Plugin_Authentication');
//         $authDbAdapter = Zend_Db::factory('Pdo_Sqlite', array('dbname' => ':memory'));
//         $this->authManager = new NetB_Auth_Manager($authDbAdapter, 'dummy_accounts', 'dummy_login_id', 'dummy_password');
//         // プラグイン登録
//         $test_actions =
//         $this->appGlobalConfig->debug_mode ?
//         array() :
//         array(		// デバッグモードでない場合はテストアクションへのアクセス禁止
//                 'order/test',
//                 'order/testfile'
//         );
//         $this->authPlugin = new NetB_Controller_Plugin_Authentication(
//         $this->authManager,
//         'order/index',				// 認証ページにorder/indexを設定 → 404に飛ばされる
//         true,						// デフォルトはどの機能もアクセスOK
//         array_merge(
//         array(
//                 'order/rpc'			// これはXmlRpc実装時に解除する
//         ),
//         $test_actions
//         ),
//         $this->getApplicationId() . '_Api_Auth'
//         );
//         $front->registerPlugin($this->authPlugin);
// ↑↑↑20150130 suzuki ServiceAbstractにて、IPアドレス、パラメーター内容によるアクセス制御が行われるので、認証は不要OK ↑↑↑


// ↓↓↓20150130 suzuki イベントのタイミングとしては、Module.phpに移植する予定 ↓↓↓
//         // イベントコールバックプラグインの設定
//         $this->addClass('NetB_Controller_Plugin_Callback');
//         $callbackPlugin = new NetB_Controller_Plugin_Callback();
//         $callbackPlugin
//         ->addPreDispatch( new NetB_Delegate($this, 'onEventRaised') )
//         ->addPostDispatch( new NetB_Delegate($this, 'onEventRaised') )
//         ;
//         $front->registerPlugin( $callbackPlugin );
// ↑↑↑20150130 suzuki イベントのタイミングとしては、Module.phpに移植する予定 ↑↑↑

        // 注文登録API向けに与信ロジックの基本設定を適用
        LogicCreditJudgeAbstract::setDefaultLogger($this->logger);
        LogicCreditJudgeSequencer::setDefaultConfig($cbdata);

        // スレッドプールオプションを初期化
        $this->initThreadOptions();

        // OEM請求口座ロジックにデフォルトのロガーを設定
        LogicOemClaimAccount::setDefaultLogger($this->logger);

        // SMBC決済ステーション連携ロジックのサービス設定をロード

        LogicSmbcRelation::loadServiceConfig($cbdata['smbc_relation']);

        // JNB共通抽象ロジックにデフォルトロガーとメールサーバ情報を設定
         LogicJnbCommon::setDefaultLogger($this->logger);
         LogicJnbCommon::setDefaultSmtpServer($this->smtpServer);

        // SMBCバーチャル口座共通抽象ロジックにデフォルトロガーとメールサーバ情報を設定
         LogicSmbcpaCommon::setDefaultLogger($this->logger);
         LogicSmbcpaCommon::setDefaultSmtpServer($this->smtpServer);

//         // リクエストを処理するディスパッチループを開始します
//         $front->dispatch();
    }

    /**
     * リクエスト内容をログ出力するための汎用ディスパッチイベントハンドラ
     */
    public function onEventRaised() {
         $this->addClass('Zend_Json');
        // 2015/09/23 Y.Suzuki Mod f_get_client_address をｺｰﾙするように変更 Stt
        $host = f_get_client_address();
//         $host = $_SERVER['REMOTE_ADDR'];
        // 2015/09/23 Y.Suzuki Mod f_get_client_address をｺｰﾙするように変更 End

        $args = func_get_args();
        $argsCount = 0;
        if (!empty($args)) {
            $argsCount = count($args);
        }
        $eventName = $argsCount == 1 ? $args[0] : $args[1];

        $req = $this->_frontController->getRequest();
        $target_path = $req->getControllerName() . '/' . $req->getActionName();
        $params = Zend_Json::encode($req->getParams());

        $this->logger->info(sprintf(
        '[dispatch event] %s event raised. addr = %s, action = %s, request_base_url = %s, request_req_uri = %s, params = %s,',
        $eventName, $host, $target_path, $req->getBaseUrl(), $req->getRequestUri(), $params) );
    }

    /**
     * スレッドプール上限を設定しないサーバー判定の初期化
     */
    public function initNonUseThreadPoolServer(array $config = array()) {
        // config.iniから設定取得
        $this->_nonUseThreadPoolServer = $config['non_use_thread_pool_server'] ? true : false;
    }

    /**
     * スレッドプール上限を設定しないサーバーかを取得する
     */
    public function nonUseThreadPoolServer() {
        //$threadServer = $this->appGlobalConfig['non_use_thread_pool_server'];
        return $this->_nonUseThreadPoolServer;
    }

    /**
     * スレッドプール関連のオプションを初期化する
     *
     * @access protected
     */
    protected function initThreadOptions() {
        $default_options = array(
                'order' => array(
                        self::TH_OPTTION_GROUP_NAME => 'api-order-rest',
                        self::TH_OPTION_THREAD_LIMIT => 100,
                        self::TH_OPTION_LOCKWAIT_TIMEOUT => 5,
                        self::TH_OPTION_LOCK_RETRY_INTERVAL => 1
                )
        );

        $loaded_options = array();
        try {
            $loaded_options = $this->config['thread_pool'];
        } catch(Exception $err) {}
        $this->_threadOptions = array_merge($default_options, $loaded_options);
    }

    /**
     * 指定アプリケーションのスレッドプールオプションを取得する
     *
     * @param string $kind アプリケーション種別
     * @return array
     */
    public function getThreadOptions($kind) {
        return isset($this->_threadOptions[$kind]) ?
        $this->_threadOptions[$kind] : array();
    }

    /**
     * OEM機能に関する初期化処理を実行する
     *
     * @param array $config アプリケーション設定を展開した連想配列
     */
    public function initOemSettings(array $config = array()) {
        $api_deny = 'deny_api_dir';
        $oem_deny = 'deny_oem_dir';

        if(isset($config[$api_deny]) && isset($config[$oem_deny])) {
            // 両方とも明示的に値が設定されている場合
            if($config[$api_deny] == $config[$oem_deny]) {
                // 同一値の設定はシステム的に禁止
                // TODO: システムエラーレスポンスを返す
                die('invalid system configuration !!!');
            } else {
                $this->setOemActive($config[$api_deny] ? true : false);
            }
        } else
            if(empty($config[$api_deny]) && empty($config[$oem_deny])) {
                // 両方とも未設定の場合
                // → 有効なOEMアクセスIDが取得可能かで判断
                $this->setOemActive($this->getOemAccessId() == 'api' ? false : true);
            } else {
                if(isset($config[$api_deny])) {
                    // APIアクセス禁止フラグが設定されている場合
                    $this->setOemActive($config[$api_deny] ? true : false);
                } else {
                    // OEMアクセス禁止フラグが設定されている場合
                    $this->setOemActive($config[$oem_deny] ? false : true);
                }
            }
    }

    /**
     * OEM機能を有効にするかを設定する
     *
     * @param boolean $active OEM機能を有効にする場合はtrueを指定。省略時はfalse（＝無効）
     * @return Application
     */
    public function setOemActive($active = false) {
        $active = $active ? true : false;
        $this->_oemActive = $active;

        if($active) {
            // OEM機能有効
            $this->setSubDirectoryName('api');
        } else {
            // OEM機能無効
            $this->setSubDirectoryName('');
        }
        return $this;
    }

    /**
     * OEM機能が有効であるかを判断する
     *
     * @return boolean OEM機能が有効な場合はtrue、それ以外はfalse
     */
    public function isOemActive() {
        return $this->_oemActive;
    }

    /**
     * Logic_StampFee向けの設定データをロードする
     *
     * @access protected
     * @return array Logic_StampFeeを初期化する設定データ
     */
    protected function loadStampFeeConfig() {
        // 設定JSONを廃止し、T_SystemProperty[Category=taxconf]から取得するよう変更（2014.9.12 eda）
        $propTable = new TableSystemProperty($this->dbAdapter);
        return $propTable->getStampFeeSettings();
    }

    /**
     * 認証ユーティリティを取得する
     *
     * @return BaseAuthUtility
     */
    public function getAuthUtility() {
        $sysProps = new \models\Table\TableSystemProperty($this->dbAdapter);
        return new BaseAuthUtility($sysProps->getHashSalt());
    }
}


