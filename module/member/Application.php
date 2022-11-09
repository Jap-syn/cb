<?php
namespace member;

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
use models\Table\TableSystemProperty;
use models\Logic\LogicOemClaimAccount;
use models\Table\TableEnterprise;
use models\Table\TableOem;
use models\Logic\LogicSelfBilling;
use Coral\Base\BaseDelegate;

use Coral\Coral\Auth\CoralAuthAttemptJudge;
use Coral\Coral\Auth\AttemptJudge\CoralAuthAttemptJudgeSetting;
use Coral\Coral\Auth\CoralAuthException;
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
    protected $_application_id = 'MemberApplication';

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
     * @var Zend_Config_Ini
     */
    public $paging_conf;

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
     * 請求書同梱ツール設定
     *
     * @var array
     */
    public $selfBillingConfig;

    /**
     * 請求取りまとめ設定
     *
     * @var array
     */
    public $combinedClaimConfig;

    /**
     * LogicStampFeeの初期化設定データ
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
     * メニュー情報を格納する
     * @var array
     */
    private $menuInfo;

    /**
     * アプリケーションを実行します。
     *
     * @access public
     */
    public function run() {


        // [member\config\config.ini]の値の取得
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

        // 汎用シーケンスを別接続で使用する
        SequenceGeneral::setDbConnectionParams( $array_con );

        // 設定を変数に保存
        $this->config = $data;

        // ログ設定の読み込み
        $logConfig = $data['log'];

        // ビジネスモデルに絡む属性
        $this->business = $data['business'];

        // ログ設定の読み込み
        //$logConfig = $data['log'];
        $this->logger = BaseLog::createFromArray( $logConfig );

        // ログ設定の読み込み
        $logConfig = $data['sbpslog'];
        $this->sbpsLogger = BaseLog::createFromArray( $logConfig );

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
        // 認証処理前後のコールバックを登録する
        $this->authManager
        ->setBeforeAuthenticateCallback(new BaseDelegate($this, 'onBeforeAuthentication'))
        ->setAfterAuthenticateCallback(new BaseDelegate($this, 'onAfterAuthentication'));

        // アプリケーションのグローバル設定をロード
        $this->appGlobalConfig = $data['application_global'];
        ini_set( 'display_errors', $this->appGlobalConfig['php_display_errors'] );

        // SMTPサーバ情報を初期化
        $this->smtpServer = empty( $this->appGlobalConfig['smtp_server'] ) ? 'localhost' : $this->appGlobalConfig['smtp_server'];

        // 注文登録設定をロード
        $this->orderItemConfig = $data['order_item'];

        // ページング設定（08.04.03追加）
        $this->paging_conf = $data['paging'];

        // 請求書同梱ツール設定の構築（13.01.10追加）
        $default_sbconfig = array(		// デフォルト設定
                'use_selfbilling' => false,
                'payment_limit_days' => 14,
                'threshold_version' => null,
                'target_list_limit' => 250,
                'shipping_sp_count' => 30
        );

        $runtime_sbconfig = array();
        if (isset($data['selfbilling'])){
            // 設定が有効になっている場合はiniファイルの設定を使用する
            $runtime_sbconfig = $data['selfbilling'];
            // use_selfbillingが文字列になってしまっているため修正
            $runtime_sbconfig['use_selfbilling'] = $runtime_sbconfig['use_selfbilling'] == 'true' ? true : false;
        }

        // デフォルト設定をランタイム設定で上書きしてZend_Configを初期化
        $this->selfBillingConfig = array_merge($default_sbconfig, $runtime_sbconfig);

        // 請求取りまとめ設定の構築
        $this->combinedClaimConfig = $data['combinedclaim'];

        // Logic_StampFee向け設定データをロード
        $this->stampFeeLogicSettings = $this->loadStampFeeConfig();

        // TODO: OEM請求講座ロジック 20141224 suzuki
        // // OEM請求口座ロジックにデフォルトのロガーを設定
        // LogicOemClaimAccount::setDefaultLogger($this->logger);
        // TODO: OEM請求講座ロジック 20141224 suzuki


        // SMBC決済ステーション連携ロジックのサービス設定をロード
        $cbdata = $this->getApplicationiInfo($this->dbAdapter, 'cbadmin');
        LogicSmbcRelation::loadServiceConfig($cbdata['smbc_relation']);

        // JNB共通抽象ロジックにデフォルトロガーとメールサーバ情報を設定
        LogicJnbCommon::setDefaultLogger($this->logger);
        LogicJnbCommon::setDefaultSmtpServer($this->smtpServer);

        // SMBCバーチャル口座共通抽象ロジックにデフォルトロガーとメールサーバ情報を設定
        LogicSmbcpaCommon::setDefaultLogger($this->logger);
        LogicSmbcpaCommon::setDefaultSmtpServer($this->smtpServer);

        // 重要メッセージコンテナを初期化
        LogicImportantMessages::setDefaultNamespace($this->getApplicationId() . '_important_messages');

        // 認証済みなら重要メッセージを構築
        if($this->authManager->isAuthenticated()) {
            $this->initPasswordAlert($this->authManager->getUserInfo()->LoginId);
        }

        // パスワード検証ロジックを初期化する
        $this->initPasswordValidator();

        // メニュー情報を初期化
        $this->menuInfo = null;

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

		// OEM配下のアカウントはNG
		if($rowObj->OemId) {
			return false;
		}

		// 代理アカウントデータがあり、且つ無効な場合はNG
		if (!is_null($altRowObj) && !$altRowObj->ValidFlg) {
		    return false;
		}

		// ここまで到達したら認証OK
		return true;
    }

    /**
     * 指定事業者IDのデータを連想配列で取得
     *
     * @param int|string $enterprise_id 事業者ID
     * @return array 当該事業者データ
     */
    public function getEnterpriseData($enterprise_id) {
        $ent = new TableEnterprise($this->dbAdapter);
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
        $oem = new TableOem($this->dbAdapter);
        return $oem->findOem2($oemId);
    }

    /**
     * 請求書同梱ツールの利用が可能な状態かを判断する。
     * このメソッドがtrueを返すには、システム設定で同梱ツール利用可能且つ現在のアカウントで許可されている場合のみ
     *
     * @return boolean 現在このアカウントで同梱ツールの利用ができるかを示すbool値
     */
    public function getSelfBillingEnabled() {
        try {
            // $this->addClass('Logic_SelfBilling');

            $ent = $this->getCurrentEnterpriseData();
            $logic = new LogicSelfBilling($this->dbAdapter, $ent['EnterpriseId'], -1, $this->getAuthUtility());
            $sys_enabled = $logic->
            setSystemSelfBillingEnabled($this->selfBillingConfig['use_selfbilling'])
            ->isSelfBillingEnabled();
            return $sys_enabled && $ent['SelfBillingMode'] && $ent['SelfBillingMode'] > 0;
        } catch(Exception $err) {
            return false;
        }
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
        $sysProps = new TableSystemProperty($this->dbAdapter);
        return new BaseAuthUtility($sysProps->getHashSalt());
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
                f_get_client_address(),         // 2015/09/23 Y.Suzuki Mod f_get_client_address をｺｰﾙするように変更
                $_SERVER['HTTP_USER_AGENT'],
                true);
            CoralAuthException::throwIdLockedException(sprintf("ログインID '%s' はロックされています。サポートまでご連絡ください", $loginId));
        }
        if($logic->judgeLockedByClientInfo($loginId, $altLoginId, f_get_client_address(), $_SERVER['HTTP_USER_AGENT'])) {   // 2015/09/23 Y.Suzuki Mod f_get_client_address をｺｰﾙするように変更
            // クライアントレベルロックアウト
            $logic->appendInvalidAuthenticationLog($loginId,
                $altLoginId,
                f_get_client_address(),     // 2015/09/23 Y.Suzuki Mod f_get_client_address をｺｰﾙするように変更
                $_SERVER['HTTP_USER_AGENT'],
                false);
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
            $authResult);

        // 認証成功なのでここで処理を抜ける
        if($authResult) return;

        if($logic->judgeLockedByLoginId($loginId)) {
            // IDレベルロックアウト
            CoralAuthException::throwIdLockedException(sprintf("ログインID '%s' はロックされています。サポートまでご連絡ください", $loginId));
        }
        if($logic->judgeLockedByClientInfo($loginId, $altLoginId, f_get_client_address(), $_SERVER['HTTP_USER_AGENT'])) {   // 2015/09/23 Y.Suzuki Mod f_get_client_address をｺｰﾙするように変更
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
            $this->_attemptJudge = new CoralAuthAttemptJudge($this->dbAdapter, CoralAuthAttemptJudge::APP_MEMBER);
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
        $appName = LogicAccountValidity::APPNAME_MEMBER;

        // 有効期限切れ時は例外をスロー
        if($logic->passwordIsExpired($appName, $loginId)) {
            throw new LogicAccountValidityException('お使いのアカウントはパスワードの有効期限を過ぎています。詳しくはサポートまでご連絡ください');
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
        $appName = LogicAccountValidity::APPNAME_MEMBER;

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

    /**
     * 権限に相当するメニュー情報を取得する
     * @return array メニュー情報
     */
    public function getMenuInfo() {
        // すでに検索済みだったら変数の内容を返す
        if (isset($this->menuInfo) ) {
            return $this->menuInfo;
        }

        $db = $this->dbAdapter;

        $rolecode = $this->authManager->getUserInfo()->RoleCode;

        // --------------------------
        // まずは、menuLinksを構成
        // --------------------------
        // SQL作成（変名で小文字にしているのは、従来のメニュー構築処理に載せるため）
$sql = <<<EOQ
        SELECT m.Id            AS id
              ,m.Ordinal       AS ordinal
              ,m.Href          AS href
              ,m.Title         AS title
              ,m.Text          AS text
              ,m.`Desc`        AS `desc`
              ,m.New           AS new
          FROM T_Menu m
              INNER JOIN T_MenuAuthority ma
                       ON m.MenuSeq = ma.MenuSeq
                      AND ma.ValidFlg = 1
         WHERE m.Module     = :Module
           AND m.Class      = :Class
           AND m.ValidFlg   = 1
           AND ma.RoleCode  = :RoleCode
EOQ;

        // パラメーター設定
        $param = array(
                ':Module'   => __NAMESPACE__,
                ':Class'    => 'menuLinks',
                ':RoleCode' => $rolecode,
        );

        // SQL実行
        $ri = $db->query($sql)->execute($param);
        $arrMenuLinks = ResultInterfaceToArray($ri);

        $menuLinks = array();
        foreach ($arrMenuLinks as $key => $info) {

            // サブメニューの構成 -------------------------->
$sql = <<<EOQ
            SELECT m.Ordinal    AS ordinal
                  ,m.Href       AS href
                  ,m.Title      AS title
                  ,m.Text       AS text
                  ,m.`Desc`     AS `desc`
              FROM T_Menu m
                   INNER JOIN T_MenuAuthority ma
                           ON m.MenuSeq     = ma.MenuSeq
                          AND ma.ValidFlg   = 1
             WHERE m.Module     = :Module
               AND m.Class      = :Class
               AND m.Id         = :Id
               AND m.ValidFlg   = 1
               AND ma.RoleCode  = :RoleCode

EOQ;

            $param = array(
                    ':Module'   => __NAMESPACE__,
                    ':Class'    => 'submenus',
                    ':Id'       => $info['id'],
                    ':RoleCode' => $rolecode,
            );
            $ri = $db -> query($sql)->execute($param);
            $subMenus = ResultInterfaceToArray($ri);

            $sm = array();
            foreach ($subMenus as $subKey => $subInfo) {
                // 同梱請求書メニューの表示制御
                if ($subInfo['href'] == 'rwclaim/list') {
                    // 同梱加盟店か否か判定する
                    $sbMode = !isset($this->getInstance()->authManager->getUserInfo()->SelfBillingMode) ? 0 : $this->getInstance()->authManager->getUserInfo()->SelfBillingMode;
                    if ( $sbMode <= 0 ) {
                        // 別送加盟店の場合は、同梱請求書発行画面を表示しない
                        continue;
                    }
                }

                // 取りまとめメニューの表示制御
                if ($subInfo['href'] == 'merge/list') {
                    // 取りまとめ可能加盟店か判断する
                    $ccMode = !isset($this->getInstance()->authManager->getUserInfo()->CombinedClaimMode) ? 0 : $this->getInstance()->authManager->getUserInfo()->CombinedClaimMode;
                    if ( $ccMode <= 0 ) {
                        // 取りまとめ対象外加盟店の場合は、取りまとめメニューを表示しない
                        continue;
                    }
                }

                // 入力不備リストメニューの表示制御
                if ($subInfo['href'] == 'order/defectlist') {
                    // 不備リスト利用加盟店か否か判定する
                    $sql = 'SELECT HoldBoxFlg FROM T_Enterprise WHERE EnterpriseId = :EnterpriseId ';
                    $row = $db->query($sql)->execute(array(':EnterpriseId' => $this->getInstance()->authManager->getUserInfo()->EnterpriseId))->current();
                    $holdBoxFlg = !isset($row['HoldBoxFlg']) ? 0 : $row['HoldBoxFlg'];
                    if ( $holdBoxFlg <= 0 ) {
                        // 別送加盟店の場合は、入力不備リスト画面を表示しない
                        continue;
                    }
                }

                $sm = array_merge($sm, array($subInfo['ordinal'] => $subInfo));
            }
            // サブメニューの構成 <--------------------------

            // menulinksを構築
            $info['submenus'] = $sm;
            $menuLinks = array_merge($menuLinks, array($info['ordinal'] => $info));

        }

        // --------------------------
        // 次に、largeMenusを構成
        // --------------------------
        // SQL作成
$sql = <<<EOQ
        SELECT m.Ordinal    AS ordinal
              ,m.Href       AS href
              ,m.Title      AS title
              ,m.Text       AS text
              ,m.`Desc`     AS `desc`
              ,m.Image_Url  AS image_url
          FROM T_Menu m
               INNER JOIN T_MenuAuthority ma
                       ON m.MenuSeq     = ma.MenuSeq
                      AND ma.ValidFlg   = 1
         WHERE m.Module     = :Module
           AND m.Class      = :Class
           AND m.ValidFlg   = 1
           AND ma.RoleCode  = :RoleCode
EOQ;

        // パラメーター設定
        $param = array(
                ':Module'   => __NAMESPACE__,
                ':Class'    => 'largeMenus',
                ':RoleCode' => $rolecode,
        );

        // 実行
        $ri = $db->query($sql)->execute($param);
        $arrLargeMenus = ResultInterfaceToArray($ri);

        $largeMenus = array();
        foreach ($arrLargeMenus as $key => $info) {

            // largeMenusを構築
            $largeMenus = array_merge($largeMenus, array($info['ordinal'] => $info));

        }

        // --------------------------
        // 次に、downloadsを構成
        // --------------------------
        // SQL作成
$sql = <<<EOQ
        SELECT m.Ordinal    AS ordinal
              ,m.Href       AS href
              ,m.Title      AS title
              ,m.Text       AS text
              ,m.`Desc`     AS `desc`
          FROM T_Menu m
               INNER JOIN T_MenuAuthority ma
                       ON m.MenuSeq     = ma.MenuSeq
                      AND ma.ValidFlg   = 1
         WHERE m.Module     = :Module
           AND m.Class      = :Class
           AND m.ValidFlg   = 1
           AND ma.RoleCode  = :RoleCode
EOQ;

        // パラメーター設定
        $param = array(
                ':Module'   => __NAMESPACE__,
                ':Class'    => 'downloads',
                ':RoleCode' => $rolecode,
        );

        // 実行
        $ri = $db->query($sql)->execute($param);
        $arrDownloads = ResultInterfaceToArray($ri);

        $downloads = array();
        foreach ($arrDownloads as $key => $info) {

            // largeMenusを構築
            $downloads = array_merge($downloads, array($info['ordinal'] => $info));

        }

        // --------------------------
        // 最後に、全体をマージ
        // --------------------------
        $this->menuInfo = array(
                'menuLinks'     => $menuLinks,
                'largeMenus'    => $largeMenus,
                'downloads'     => $downloads,
        );

        return $this->menuInfo;
    }

    public function getMail($keyCode) {
        $sql = " SELECT Class1 FROM M_Code WHERE KeyCode = :KeyCode AND CodeId=213 AND ValidFlg=1 ";

        $stm = $this->dbAdapter->query($sql);

        $prm = array(
            ':KeyCode' => $keyCode,
        );

        return $stm->execute($prm)->current()['Class1'];
    }

    public function getLinkHelp($keyCode) {
        $sql = " SELECT Class2 FROM M_Code WHERE KeyCode = :KeyCode AND CodeId=213 AND ValidFlg=1 ";

        $stm = $this->dbAdapter->query($sql);

        $prm = array(
            ':KeyCode' => $keyCode,
        );

        return $stm->execute($prm)->current()['Class2'];
    }

    public function hasPaymentAfterArrivalFlg($enterpriseId) {
        $logicSbps = new LogicSbps($this->dbAdapter);
        return $logicSbps->checkHasPaymentAfterArrivalFlg($enterpriseId, 'T_Site');
    }
}



