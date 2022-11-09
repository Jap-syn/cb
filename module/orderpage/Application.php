<?php
namespace orderpage;

use Coral\Base\Application\BaseApplicationAbstract;

use models\Logic\LogicSbps;
use Zend\Db\Adapter\Adapter;
use Zend\Config\Reader\Ini;
use Zend\Loader\StandardAutoloader;

use Coral\Base\Auth\BaseAuthUtility;
use Coral\Base\BaseLog;
use Coral\Base\BaseGeneralUtils;
use Coral\Coral\Auth\CoralAuthManager;
use Coral\Coral\CoralValidate;

use models\Sequence\SequenceGeneral;
use models\View\MypageViewSystemProperty;
use models\Logic\LogicOemClaimAccount;
use models\View\MypageViewEnterprise;
use Coral\Base\BaseDelegate;
use Coral\Base\Application\BaseApplicationOem;
use models\View\MypageViewOem;
use models\View\MypageViewOrder;
use models\View\MypageViewSite;


/**
 * アプリケーションクラスです。
 *
 */
class Application extends BaseApplicationOem {
    protected $_application_id = 'OrderpageApplication';

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
     * @var CoralAuthManager
     */
    public $authManager;

    /**
     * @var BaseControllerPluginAuthentication
     */
    public $authPlugin;

    /**
     * アプリケーションのグローバル設定
     *
     * @var array
     */
    public $appGlobalConfig;

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
     * 標準ログクラス
     *
     * @var BaseLog
     */
    public $logger;

    // mypage
    /**
     * 標準ログクラス
     *
     * @var BaseLog
     */
    public $sbpsLogger;

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


        // [orderpage\config\config.ini]の値の取得
        $this->configRoot = __DIR__ . '/config';

        $inidata = array();
        $file = $this->configRoot . '/config.ini';
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

        // 設定を変数に保存
        $this->config = $data;

        // ログ設定の読み込み
        $logConfig = $data['log'];
        $this->logger = BaseLog::createFromArray( $logConfig );

        // mypage
        // ログ設定の読み込み
        $logConfig = $data['sbpslog'];
        $this->sbpsLogger = BaseLog::createFromArray( $logConfig );

        // OEM関連初期設定
        $this->initOemSettings($data['oem_directory_settings']);

        // 認証マネージャを初期化します
        $array_auth = $data['auth_manager'];
        $this->authManager = new CoralAuthManager(
            $this->dbAdapter,
            $array_auth['tablename'],
            $array_auth['id_column'],
            $array_auth['password_column'],
            __NAMESPACE__ . \Zend\Authentication\Storage\Session::NAMESPACE_DEFAULT
        );
        if (key_exists('log_enabled', $array_auth) && $array_auth['log_enabled']) {
            $this->authManager->setLogger($this->logger);
        }

        // 代理認証設定を適用
        $this->authManager = $this->setupAlternativeAuthSetting($this->authManager, $data['alt_auth_settings']);

        // 追加の認証チェックを認証マネージャに登録
        $this->authManager->addAuthentication( new BaseDelegate( $this, 'checkAccount' ) );

        // アプリケーションのグローバル設定をロード
        $this->appGlobalConfig = $data['application_global'];
        ini_set( 'display_errors', $this->appGlobalConfig['php_display_errors'] );

        // SMTPサーバ情報を初期化
        $this->smtpServer = empty( $this->appGlobalConfig['smtp_server'] ) ? 'localhost' : $this->appGlobalConfig['smtp_server'];

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

        // アカウント無効ならNG
        if(!$rowObj->ValidFlg) {
            return false;
        }

        // 有効期限外ならNG
        if (strtotime( date( 'Y-m-d H:i:s' ) ) > strtotime( $rowObj->ValidToDate)) {
            return false;
        }

        if ( !$this->isOemActive() ) {
            // CBの場合
            if ( $rowObj->OemId ) {
                // OEMIDが有効な場合はNG
                return false;
            }
        } else {
            // OEMの場合

            // 非OEM配下の事業者アカウントはNG
            if(!$rowObj->OemId) {
                return false;
            }

            // 自OEM用パス以外のアクセスはNG
            $oemTable = new MypageViewOem($this->dbAdapter);
            $dbRow = $oemTable->findByAccessId($this->getOemAccessId())->current();
            if(!$dbRow || $dbRow['OemId'] != $rowObj->OemId) {
                return false;
            }

            // OEMアカウントでの代理認証で、自OEM以外の場合はNG
            if($altRowObj) {
                if(isset($altRowObj->OemId)) {
                    if($altRowObj->OemId != $rowObj->OemId) {
                        return false;
                    }
                }
            }
        }

        // ここまで到達したらOK
        return true;
    }

    /**
     * 指定事業者IDのデータを連想配列で取得
     *
     * @param int|string $enterprise_id 事業者ID
     * @return array 当該事業者データ
     */
    public function getEnterpriseData($enterprise_id) {
        $ent = new MypageViewEnterprise($this->dbAdapter);
        return $ent->findEnterprise2($enterprise_id)->current();
    }

    /**
     * 現在のアカウントの事業者データを連想配列で取得
     *
     * @return array 現在のアカウントの事業者データ
     */
    public function getCurrentEnterpriseData() {
        return $this
        ->getEnterpriseData($this->authManager->getUserInfo()->EnterpriseId);
    }

    /**
     * 現在の事業者のOEM情報を連想配列で取得
     *
     * @return array 現在の事業者のOEM情報
     */
    public function getOemInfo($oemId) {
        $oem = new MypageViewOem($this->dbAdapter);
        return $oem->find($oemId);
    }

    /**
     * 認証マネージャのインスタンスに代理認証設定を適用する
     *
     * @access protected
     * @param CoralAuthManager $mgr 設定を適用する認証マネージャ
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
        } catch(\Exception $err) {
        }
        return $mgr;
    }

    /**
     * 認証ユーティリティを取得する
     *
     * @return BaseAuthUtility
     */
    public function getAuthUtility() {
        $sysProps = new MypageViewSystemProperty($this->dbAdapter);
        return new BaseAuthUtility($sysProps->getHashSalt());
    }

    /**
     * OEM機能に関する初期化処理を実行する
     *
     * @param array $config アプリケーション設定を展開した連想配列
     */
    public function initOemSettings(array $config = array()) {
        $cb_deny = 'deny_api_dir';
        $oem_deny = 'deny_oem_dir';

        if(isset($config[$cb_deny]) && isset($config[$oem_deny])) {
            // 両方とも明示的に値が設定されている場合
            if($config[$cb_deny] == $config[$oem_deny]) {
                // 同一値の設定はシステム的に禁止
                // TODO: システムエラーレスポンスを返す
                die('invalid system configuration !!!');
            } else {
                $this->setOemActive($config[$cb_deny] ? true : false);
            }
        } else
            if(empty($config[$cb_deny]) && empty($config[$oem_deny])) {
                // 両方とも未設定の場合
                // → 有効なOEMアクセスIDが取得可能かで判断
                $this->setOemActive($this->getOemAccessId() == 'orderpage' ? false : true);
            } else {
                if(isset($config[$cb_deny])) {
                    // APIアクセス禁止フラグが設定されている場合
                    $this->setOemActive($config[$cb_deny] ? true : false);
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
            $this->setSubDirectoryName('orderpage');
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
     * 現在アクセスしているURLのOEM情報を連想配列で取得
     *
     * @return array
     */
    public function getCurrentOemData() {
        $oem = new MypageViewOem($this->dbAdapter);
        $row = $oem->findByAccessId($this->getOemAccessId())->current();
        $ret = $row ? $row : array();

        $mime = "image/jpeg";

        $ret['LargeLogo'] = is_null($ret['LargeLogo']) ? null : 'data:'.$mime.';base64,'.$ret['LargeLogo'];
        $ret['SmallLogo'] = is_null($ret['SmallLogo']) ? null : 'data:'.$mime.';base64,'.$ret['SmallLogo'];
        $ret['Imprint'] = is_null($ret['Imprint']) ? null : 'data:'.$mime.';base64,'.$ret['Imprint'];

        return $ret;
    }

    /**
     * getSmallLogoBySiteId
     *
     * @return string
     */
    public function getSmallLogoBySiteId() {
        $site = new MypageViewSite($this->dbAdapter);
        return $site->find($this->getSiteIdByOrderSeq())->current()['SmallLogo'];
    }

    /**
     * getSiteIdByOrderSeq
     *
     * @return int
     */
    protected function getSiteIdByOrderSeq() {
        $order = new MypageViewOrder($this->dbAdapter);
        return $order->find($this->getOrderSeqData())->current()['SiteId'];
    }

    /**
     * getOrderSeqData
     *
     * @return int OrderSeq
     */
    protected function getOrderSeqData() {
        return $this->authManager->getUserInfo()->OrderSeq;
    }

    /**
     * getSpecificTransUrl
     *
     * @return string
     */
    public function getSpecificTransUrl() {
        $site = new MypageViewSite($this->dbAdapter);
        return $site->find($this->getSiteIdByOrderSeq())->current()['SpecificTransUrl'];
    }

    public function getFlagPaymentAfterArrivalFlg() {
        $order = new MypageViewOrder($this->dbAdapter);
        $enterpriseId =  $order->find($this->getOrderSeqData())->current()['EnterpriseId'];
        if (!empty($enterpriseId)) {
            $logicSbps = new LogicSbps($this->dbAdapter);
            return $logicSbps->checkHasPaymentAfterArrivalFlg($enterpriseId, 'MV_Site');
        }

        return 0;
    }
}
