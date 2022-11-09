<?php
namespace oemmypage;

use Coral\Base\Application\BaseApplicationAbstract;

use Zend\Db\Adapter\Adapter;
use Zend\Config\Reader\Ini;
use Zend\Loader\StandardAutoloader;
use Zend\Json\Json;

use Coral\Base\Auth\BaseAuthUtility;
use Coral\Base\BaseLog;
use Coral\Base\BaseGeneralUtils;
use Coral\Coral\Auth\CoralAuthManager;
use Coral\Coral\CoralValidate;

use models\Sequence\SequenceGeneral;
use models\View\MypageViewSystemProperty;
use models\Logic\LogicOemClaimAccount;
use models\View\MypageViewEnterprise;
use models\View\MypageViewOem;
use models\View\MypageViewCode;
use Coral\Base\BaseDelegate;
use Coral\Base\Application\BaseApplicationOem;

/**
 * アプリケーションクラスです。
 *
 */
class Application extends BaseApplicationOem {
    protected $_application_id = 'OemMypageApplication';

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

        // [oemmypage\config\config.ini]の値の取得
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
        // アカウントが有効なら認証OK
        return ($rowObj->ValidFlg != 0);

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
            } else {
            }
        }
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
        return $oem->findOem2($oemId);
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
     * 指定OEMのスタイルシートを取得
     *
     * @return string
     */
    public function getOemCss($oemId) {
        $oem = new MypageViewOem($this->dbAdapter);
        $code = new MypageViewCode($this->dbAdapter);

        // OEMを取得
        $row = $oem->find($oemId)->current();

        // 指定のCSSを取得
        $css = $code->find('102', $row['StyleSheets'])->current();
        if (!$css) {
            $css = $code->find('102', '0')->current();; // 取得に失敗した場合はデフォルト値
        }

        $cssFileName = Json::decode($css['Note'], Json::TYPE_ARRAY);

        return $cssFileName['mypage'];
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
}

