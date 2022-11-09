<?php
namespace Coral\Coral\Auth;

use Zend\Db\Adapter\Adapter;
use Coral\Coral\Auth\AttemptJudge\CoralAuthAttemptJudgeSetting;
use models\Table\TableAuthenticationLog;

/**
 * 認証処理結果の記録に基づいてアカウントのロックコントロールを行う認証ヘルパーロジック
 */
class CoralAuthAttemptJudge {
    /**
     * クライアントハッシュを生成するためのハッシュアルゴリズム：SHA256固定
     * @var string
     */
    const HASH_ALGO = 'sha256';

    /**
     * ログ種別定数： 認証成否ログ
     * @var int
     */
    const LOGTYPE_AUTHED = 0;

    /**
     * ログ種別定数： ロックアウト情報
     * @var int
     */
    const LOGTYPE_LOCKED = 1;

    /**
     * ログ種別定数： ロックアウト後認証試行
     * @var int
     */
    const LOGTYPE_AUTH_AFTER_LOCKED = 2;

    /**
     * 処理結果定数： 認証成否ログ - 認証失敗
     * @var int
     */
    const RESULT_AUTH_FAILURE = 0;

    /**
     * 処理結果定数： 認証成否ログ - 認証成功
     * @var int
     */
    const RESULT_AUTH_SUCCESS = -1;

    /**
     * 処理結果定数： ロックアウト情報 - クライアントレベルロック
     * @var int
     */
    const RESULT_LOCK_CLIENT = 1;

    /**
     * 処理結果定数： ロックアウト情報 - IDレベルロック
     * @var int
     */
    const RESULT_LOCK_ID = 2;

    /**
     * 処理結果定数： ロックアウト後認証試行 - クライアントレベルロック
     * @var int
     */
    const RESULT_AFL_CLIENT = 101;

    /**
     * 処理結果定数： ロックアウト後認証試行 - IDレベルロック
     * @var int
     */
    const RESULT_AFL_ID = 102;

    /**
     * ターゲットアプリケーション定数： CB管理画面
     * @var int
     */
    const APP_CBADMIN = 0;

    /**
     * ターゲットアプリケーション定数： 直収事業者管理画面
     * @var int
     */
    const APP_MEMBER = 1;

    /**
     * ターゲットアプリケーション定数： OEM管理画面
     * @var int
     */
    const APP_OEMADMIN = 2;

    /**
     * ターゲットアプリケーション定数： OEM先事業者管理画面
     * @var int
     */
    const APP_OEMMEMBER = 3;

    /**
     * ターゲットアプリケーションを指定する値が定義済みの正しい値であるかを検査する
     *
     * @static
     * @access protected
     * @param mixed $targetApp
     * @return boolean
     */
    protected static function isValidTargetApp($targetApp) {
        if(!is_numeric($targetApp)) return false;
        $targetApp = (int)$targetApp;
        $valids = array(
            self::APP_CBADMIN,
            self::APP_MEMBER,
            self::APP_OEMADMIN,
            self::APP_OEMMEMBER
        );
        return in_array($targetApp, $valids);
    }

    /**
     * アダプタ
     *
     * @var Adapter
     */
    protected $_adapter = null;

    /**
     * ターゲットアプリケーション
     * @access protected
     * @var int
     */
    protected $_targetApp;

    /**
     * クライアントレベルロックアウトに関する設定
     *
     * @access protected
     * @var CoralAuthAttemptJudgeSetting
     */
    protected $_clientLevelSetting;

    /**
     * IDレベルロックアウトに関する設定
     *
     * @access protected
     * @var CoralAuthAttemptJudgeSetting
     */
    protected $_idLevelSetting;

    /**
     * DBアダプタとターゲットアプリケーションを指定して、CoralAuthAttemptJudgeの
     * 新しいインスタンスを初期化する
     *
     * @param Adapter $adapter アダプタ
     * @param int $targetApp ターゲットアプリケーション。APP_*定数で定義されている値のみ指定可能
     */
    public function __construct(Adapter $adapter, $targetApp) {
        $this->setDbAdapter($adapter);
        $this->setTargetApp($targetApp);

    }

    /**
     * DBアダプタを取得する
     *
     * @return Adapter $adapter アダプタ
     */
    public function getDbAdapter() {
        return $this->_adapter;
    }
    /**
     * DBアダプタを設定する
     *
     * @param Adapter $adapter アダプタ
     * @return CoralAuthAttemptJudge このインスタンス
     */
    public function setDbAdapter(Adapter $adapter) {
        $this->_adapter = $adapter;
        return $this;
    }

    /**
     * 認証ログテーブルモデルを取得する
     *
     * @return TableAuthenticationLog
     */
    public function getAuthLogTable() {
        return new TableAuthenticationLog($this->getDbAdapter());
    }

    /**
     * ターゲットアプリケーションを指定する整数値を取得する
     *
     * @return int
     */
    public function getTargetApp() {
        return $this->_targetApp;
    }
    /**
     * ターゲットアプリケーションを指定する
     *
     * @param int $targetApp ターゲットアプリケーション。APP_*定数で定義されている値のみ指定可能
     * @return CoralAuthAttemptJudge このインスタンス
     */
    public function setTargetApp($targetApp) {
        if(!self::isValidTargetApp($targetApp)) throw new \Exception('invalid parameter specified');
        $this->_targetApp = (int)$targetApp;
        return $this;
    }

    /**
     * クライアントレベルロックアウトが有効であるかを取得する
     *
     * @return boolean
     */
    public function getClientLevelLockoutEnabled() {
        $this->initClientLevelLockoutSetting();
        return $this->_clientLevelSetting->getEnabled();
    }
    /**
     * クライアントレベルロックアウトの有効／無効を設定する
     *
     * @param boolean $enabled 有効にする場合はtrue、無効にする場合はfalse
     * @return CoralAuthAttemptJudge このインスタンス
     */
    public function setClientLevelLockoutEnabled($enabled) {
        $this->initClientLevelLockoutSetting();
        $this->_clientLevelSetting->setEnabled($enabled);
        return $this;
    }
    /**
     * クライアントレベルロックアウトの設定を取得する
     *
     * @return CoralAuthAttemptJudgeSetting
     */
    public function getClientLevelLockoutSetting() {
        $this->initClientLevelLockoutSetting();
        return $this->_clientLevelSetting;
    }
    /**
     * クライアントレベルロックアウトの設定を適用する
     *
     * @param CoralAuthAttemptJudgeSetting $setting ロックアウト設定
     * @return CoralAuthAttemptJudge このインスタンス
     */
    public function setClientLevelLockoutSetting(CoralAuthAttemptJudgeSetting $setting) {
        $this->_clientLevelSetting = $setting;
        $this->initClientLevelLockoutSetting();
        return $this;
    }
    /**
     * クライアントレベルロックアウト向けの、判定時間間隔と試行上限回数およびロックアウト継続時間を設定する
     *
     * @param int $seconds 判定時間間隔（秒単位）
     * @param int $limit 試行上限回数
     * @param int $lockoutTime ロックアウト継続時間（分単位）
     * @return CoralAuthAttemptJudge このインスタンス
     */
    public function updateClientLevelLockoutSetting($seconds, $limit, $lockoutTime) {
        $this->initClientLevelLockoutSetting();
        $this->_clientLevelSetting
            ->setJudgementIntervalTime($seconds)
            ->setLimitForLockout($limit)
            ->setLockoutTime($lockoutTime);
        return $this;
    }
    /**
     * クライアントレベルアウト設定の内部値を初期化する
     *
     * @access protected
     */
    protected function initClientLevelLockoutSetting() {
        // nullが設定されている場合は設定クラスの初期値で初期化する
        if($this->_clientLevelSetting == null) {
            $this->_clientLevelSetting = new CoralAuthAttemptJudgeSetting();
        }
    }

    /**
     * IDレベルロックアウトが有効であるかを取得する
     *
     * @return boolean
     */
    public function getIdLevelLockoutEnabled() {
        $this->initIdLevelLockoutSetting();
        return $this->_idLevelSetting->getEnabled();
    }
    /**
     * IDレベルロックアウトの有効／無効を設定する
     *
     * @param boolean $enabled 有効にする場合はtrue、無効にする場合はfalse
     * @return CoralAuthAttemptJudge このインスタンス
     */
    public function setIdLevelLockoutEnabled($enabled) {
        $this->initIdLevelLockoutSetting();
        $this->_idLevelSetting->setEnabled($enabled);
        return $this;
    }
    /**
     * IDレベルロックアウトの設定を取得する
     *
     * @return CoralAuthAttemptJudgeSetting
     */
    public function getIdLevelLockoutSetting() {
        $this->initIdLevelLockoutSetting();
        return $this->_idLevelSetting;
    }
    /**
     * IDレベルロックアウトの設定を適用する
     *
     * @param CoralAuthAttemptJudgeSetting $setting ロックアウト設定
     * @return CoralAuthAttemptJudge このインスタンス
     */
    public function setIdLevelLockoutSetting(CoralAuthAttemptJudgeSetting $setting) {
        $this->_idLevelSetting = $setting;
        $this->initIdLevelLockoutSetting();
        return $this;
    }
    /**
     * IDレベルロックアウト向けの、判定時間間隔と試行上限回数およびロックアウト継続時間を設定する
     *
     * @param int $seconds 判定時間間隔（秒単位）
     * @param int $limit 試行上限回数
     * @param int $lockoutTime ロックアウト継続時間（分単位）
     * @return CoralAuthAttemptJudge このインスタンス
     */
    public function updateIdLevelLockoutSetting($seconds, $limit, $lockoutTime) {
        $this->initIdLevelLockoutSetting();
        $this->_idLevelSetting
            ->setJudgementIntervalTime($seconds)
            ->setLimitForLockout($limit)
            ->setLockoutTime($lockoutTime);
        return $this;
    }
    /**
     * IDレベルアウト設定の内部値を初期化する
     *
     * @access protected
     */
    protected function initIdLevelLockoutSetting() {
        // nullが設定されている場合は設定クラスの初期値で初期化する
        if($this->_idLevelSetting == null) {
            $this->_idLevelSetting = new CoralAuthAttemptJudgeSetting();
        }
    }

    /**
     * クライアントレベルロックアウト向けのクライアントハッシュ生成にHTTP_USER_AGENTを
     * 含めるかの設定を取得する
     *
     * @return boolean
     */
    public function getIncludeUaInClientHash() {
        $this->initClientLevelLockoutSetting();
        return $this->_clientLevelSetting->getUseUaInHash();
    }
    /**
     * クライアントレベルロックアウト向けのクライアントハッシュ生成にHTTP_USER_AGENTを
     * 含めるかを設定する
     *
     * @param boolean $include ハッシュ生成要素にHTTP_USER_AGENTを含めるかのフラグ
     *                         falseを設定した場合、ハッシュ生成要素はIPアドレスのみとなる
     * @return CoralAuthAttemptJudge このインスタンス
     */
    public function setIncludeUaInClientHash($include) {
        $this->initClientLevelLockoutSetting();
        $this->_clientLevelSetting->setUseUaInHash($include);
        return $this;
    }

    /**
     * 指定のIPアドレスとHTTP_USER_AGENTから、クライアントを特定するためのハッシュ値を生成する
     *
     * @param null | string $ipAddress IPアドレス
     * @param null | string $userAgent HTTP_USER_AGENT
     * @return string 指定要素から算出したSHA256ハッシュ値
     */
    public function calcClientHash($ipAddress = null, $userAgent = null) {
        // ハッシュにUAを含めない設定の場合はUAを明示的にブランクに設定する
        if(!$this->getIncludeUaInClientHash()) $userAgent = '';

        return hash(self::HASH_ALGO, sprintf('%s<>%s',
                                             strtoupper(nvl($ipAddress)),
                                             strtoupper(nvl($userAgent))));
    }

    /**
     * 認証成否ログを認証ログテーブルに追加する
     *
     * @param string $loginId ログインID
     * @param string | null $altLoginId 代理認証ID
     * @param string | null $ipAddress IPアドレス
     * @param string | null $userAgent HTTP_USER_AGENT
     * @param boolean | null $authResult 認証成否
     * @param string | null $oemAccId OEMアクセス識別子
     * @return CoralAuthAttemptJudge このインスタンス
     */
    public function appendAuthenticationLog($loginId, $altLoginId = null, $ipAddress = null,
                                            $userAgent = null, $authResult = false, $oemAccId = null) {
        // ロックアウト設定がどちらも無効な場合はログも出力しない
        if(!$this->getIdLevelLockoutEnabled() && !$this->getClientLevelLockoutEnabled()) return $this;

        $loginId = strtoupper(nvl($loginId));
        $altLoginId = strtoupper(nvl($altLoginId));
        $ipAddress = nvl($ipAddress);
        $hash = $this->calcClientHash($ipAddress, $userAgent);
        $oemAccId = nvl($oemAccId);

        $this->getAuthLogTable()->saveNew(array(
            'LogType' => self::LOGTYPE_AUTHED,
            'TargetApp' => $this->getTargetApp(),
            'LoginId' => $loginId,
            'AltLoginId' => $altLoginId,
            'IpAddress' => $ipAddress,
            'ClientHash' => $hash,
            'Result' => $authResult ? self::RESULT_AUTH_SUCCESS : self::RESULT_AUTH_FAILURE,
            'LogTime' => date('Y-m-d H:i:s'),
            'DeleteFlg' => 0,
            'OemAccessId' => $oemAccId
        ));

        if($authResult) {
            // 認証が成功していたらロックアウトを解除する
            $this->releaseClientLevelLock($loginId, $altLoginId, $ipAddress, $userAgent);
        } else {
            $idFailureCount = count($this->findRejectedLogByLoginId($loginId));
            if($idFailureCount >= $this->getIdLevelLockoutSetting()->getLimitForLockout()) {
                // IDレベルの既定回数に到達したのでロックアウト情報を追加する
                $this->appendIdLevelLockInfo($loginId, $altLoginId, $ipAddress, $userAgent, $oemAccId);
            } else {
                $clFailureCount = count($this->findRejectedLogByClientHash($loginId, $altLoginId, $hash));
                if($clFailureCount >= $this->getClientLevelLockoutSetting()->getLimitForLockout()) {
                    // クライアントレベルの既定回数に到達したのでロックアウト情報を追加する
                    $this->appendClientLevelLockInfo($loginId, $altLoginId, $ipAddress, $userAgent, $oemAccId);
                }
            }
        }
        return $this;
    }

    /**
     * ロックアウト後認証試行ログを認証ログテーブルに追加する
     *
     * @param string $loginId ログインID
     * @param string | null $altLoginId 代理認証ID
     * @param string | null $ipAddress IPアドレス
     * @param string | null $userAgent HTTP_USER_AGENT
     * @param boolean | null $idLevelLocked IDレベルロックアウトに対する認証試行かを指定するフラグ。
     *                                      省略時はfalse（＝クライアントレベルロックアウト後の試行）
     * @param string | null $oemAccId OEMアクセス識別子
     * @return CoralAuthAttemptJudge このインスタンス
     */
    public function appendInvalidAuthenticationLog($loginId, $altLoginId = null, $ipAddress = null,
                                                   $userAgent = null, $idLevelLocked = false, $oemAccId = null) {
        // IDレベルロックアウト設定無効時のIDレベルロックアウト後アクセスログは出力しない
        if($idLevelLocked && !$this->getIdLevelLockoutEnabled()) return $this;

        // クライアントレベルロックアウト設定無効時のクライアントレベルロックアウト後アクセスログは出力しない
        if(!$idLevelLocked && !$this->getClientLevelLockoutEnabled()) return $this;

        $loginId = strtoupper(nvl($loginId));
        $altLoginId = strtoupper(nvl($altLoginId));
        $ipAddress = nvl($ipAddress);
        $hash = $this->calcClientHash($ipAddress, $userAgent);
        $oemAccId = nvl($oemAccId);

        $this->getAuthLogTable()->saveNew(array(
            'LogType' => self::LOGTYPE_AUTH_AFTER_LOCKED,
            'TargetApp' => $this->getTargetApp(),
            'LoginId' => $loginId,
            'AltLoginId' => $altLoginId,
            'IpAddress' => $ipAddress,
            'ClientHash' => $hash,
            'Result' => $idLevelLocked ? self::RESULT_AFL_ID : self::RESULT_AFL_CLIENT,
            'LogTime' => date('Y-m-d H:i:s'),
            'DeleteFlg' => 0,
            'OemAccessId' => $oemAccId
        ));
        return $this;
    }

    /**
     * クライアントレベルロックアウト情報を認証ログテーブルに追加する
     *
     * @param string $loginId ログインID
     * @param string | null $altLoginId 代理認証ID
     * @param string $ipAddress IPアドレス
     * @param string $userAgent HTTP_USER_AGENT
     * @param string | null $oemAccId OEMアクセス識別子
     * @return CoralAuthAttemptJudge このインスタンス
     */
    public function appendClientLevelLockInfo($loginId, $altLoginId, $ipAddress, $userAgent, $oemAccId = null) {
        // クライアントレベルロックアウト設定無効時は無視する
        if(!$this->getClientLevelLockoutEnabled()) return $this;

        $loginId = strtoupper(nvl($loginId));
        $altLoginId = strtoupper(nvl($altLoginId));
        $ipAddress = nvl($ipAddress);
        $oemAccId = nvl($oemAccId);

        // 有効なロックアウト情報がない場合のみ追加する
        if(!$this->judgeLockedByClientInfo($loginId, $altLoginId, $ipAddress, $userAgent)) {
            $hash = $this->calcClientHash($ipAddress, $userAgent);
            $this->getAuthLogTable()->saveNew(array(
                'LogType' => self::LOGTYPE_LOCKED,
                'TargetApp' => $this->getTargetApp(),
                'LoginId' => $loginId,
                'AltLoginId' => $altLoginId,
                'IpAddress' => $ipAddress,
                'ClientHash' => $hash,
                'Result' => self::RESULT_LOCK_CLIENT,
                'LogTime' => date('Y-m-d H:i:s'),
                'DeleteFlg' => 0,
                'OemAccessId' => $oemAccId
            ));
        }
        return $this;
    }

    /**
     * クライアントレベルロックアウト情報を解放する
     *
     * @param string $loginId ログインID
     * @param string | null $altLoginId 代理認証ID
     * @param string $ipAddress IPアドレス
     * @param string $userAgent HTTP_USER_AGENT
     * @return CoralAuthAttemptJudge このインスタンス
     */
    public function releaseClientLevelLock($loginId, $altLoginId, $ipAddress, $userAgent) {
        $loginId = strtoupper(nvl($loginId));
        $altLoginId = strtoupper(nvl($altLoginId));
        $ipAddress = nvl($ipAddress);
        $hash = $this->calcClientHash($ipAddress, $userAgent);

        return $this->releaseClientLevelLockByClientHash($loginId, $altLoginId, $has);
    }

    /**
     * クライアントレベルロックアウト情報をクライアントハッシュ指定で解放する
     *
     * @param string $loginId ログインID
     * @param string | null $altLoginId 代理認証ID
     * @param string $hash クライアントハッシュ
     * @return CoralAuthAttemptJudge このインスタンス
     */
    public function releaseClientLevelLockByClientHash($loginId, $altLoginId, $hash) {
        $loginId = strtoupper(nvl($loginId));
        $altLoginId = strtoupper(nvl($altLoginId));

        // 既存の認証失敗ログをクライアントレベルで削除
        $this->deleteClientLevelFailureLogs($loginId, $altLoginId, $hash);

        $sql  = " UPDATE T_AuthenticationLog SET DeleteFlg = 1 ";
        $sql .= " WHERE  1 = 1 ";
        $sql .= " AND    LogType = :LogType ";
        $sql .= " AND    TargetApp = :TargetApp ";
        $sql .= " AND    LoginId = :LoginId ";
        $sql .= " AND    AltLoginId = :AltLoginId ";
        $sql .= " AND    ClientHash = :ClientHash ";
        $sql .= " AND    Result = :Result ";
        $sql .= " AND    DeleteFlg = 0 ";

        $prm = array(
                ':LogType' => self::LOGTYPE_LOCKED,
                ':TargetApp' => $this->getTargetApp(),
                ':LoginId' => $loginId,
                ':AltLoginId' => $altLoginId,
                ':ClientHash' => $hash,
                ':Result' => self::RESULT_LOCK_CLIENT,
        );

        $this->_adapter->query($sql)->execute($prm);

        return $this;
    }

    /**
     * 既存の認証失敗ログをクライアントレベルで削除する
     *
     * @access protected
     * @param string $loginId ログインID
     * @param string | null $altLoginId 代理認証ID
     * @param string $hash クライアントハッシュ
     */
    protected function deleteClientLevelFailureLogs($loginId, $altLoginId, $hash) {
        $loginId = strtoupper(nvl($loginId));
        $altLoginId = strtoupper(nvl($altLoginId));

        $sql  = " UPDATE T_AuthenticationLog SET DeleteFlg = 1 ";
        $sql .= " WHERE  1 = 1 ";
        $sql .= " AND    LogType = :LogType ";
        $sql .= " AND    TargetApp = :TargetApp ";
        $sql .= " AND    LoginId = :LoginId ";
        $sql .= " AND    AltLoginId = :AltLoginId ";
        $sql .= " AND    ClientHash = :ClientHash ";
        $sql .= " AND    Result = :Result ";
        $sql .= " AND    DeleteFlg = 0 ";

        $prm = array(
                ':LogType' => self::LOGTYPE_AUTHED,
                ':TargetApp' => $this->getTargetApp(),
                ':LoginId' => $loginId,
                ':AltLoginId' => $altLoginId,
                ':ClientHash' => $hash,
                ':Result' => self::RESULT_AUTH_FAILURE,
        );

        $this->_adapter->query($sql)->execute($prm);
    }

    /**
     * IDレベルロックアウト情報を認証ログテーブルに追加する
     *
     * @param string $loginId ログインID
     * @param string | null $altLoginId 代理認証ID
     * @param string $ipAddress IPアドレス
     * @param string $userAgent HTTP_USER_AGENT
     * @param string | null $oemAccId OEMアクセス識別子
     * @return CoralAuthAttemptJudge このインスタンス
     */
    public function appendIdLevelLockInfo($loginId, $altLoginId, $ipAddress, $userAgent, $oemAccId = null) {
        // IDレベルロックアウト設定が無効の場合は無視する
        if(!$this->getIdLevelLockoutEnabled()) return $this;

        $loginId = strtoupper(nvl($loginId));
        $altLoginId = strtoupper(nvl($altLoginId));
        $ipAddress = nvl($ipAddress);
        $oemAccId = nvl($oemAccId);

        // 有効なロックアウト情報がない場合のみ追加する
        if(!$this->judgeLockedByLoginId($loginId)) {
            $hash = $this->calcClientHash($ipAddress, $userAgent);
            $this->getAuthLogTable()->saveNew(array(
                'LogType' => self::LOGTYPE_LOCKED,
                'TargetApp' => $this->getTargetApp(),
                'LoginId' => $loginId,
                'AltLoginId' => $altLoginId,
                'IpAddress' => $ipAddress,
                'ClientHash' => $hash,
                'Result' => self::RESULT_LOCK_ID,
                'LogTime' => date('Y-m-d H:i:s'),
                'DeleteFlg' => 0,
                'OemAccessId' => $oemAccId
            ));
        }
        return $this;
    }

    /**
     * クライアントレベルロックアウト情報を解放する
     *
     * @param string $loginId ログインID
     * @return CoralAuthAttemptJudge このインスタンス
     */
    public function releaseIdLevelLockInfo($loginId) {
        $loginId = strtoupper(nvl($loginId));

        // 認証失敗ログをIDレベルで削除
        $this->deleteIdLevelFailureLogs($loginId);

        $sql  = " UPDATE T_AuthenticationLog SET DeleteFlg = 1 ";
        $sql .= " WHERE  1 = 1 ";
        $sql .= " AND    LogType = :LogType ";
        $sql .= " AND    TargetApp = :TargetApp ";
        $sql .= " AND    LoginId = :LoginId ";
        $sql .= " AND    Result = :Result ";
        $sql .= " AND    DeleteFlg = 0 ";

        $prm = array(
                ':LogType' => self::LOGTYPE_LOCKED,
                ':TargetApp' => $this->getTargetApp(),
                ':LoginId' => $loginId,
                ':Result' => self::RESULT_LOCK_ID,
        );

        $this->_adapter->query($sql)->execute($prm);

        return $this;
    }

    /**
     * 既存の認証失敗ログをIDレベルで削除する
     *
     * @access protected
     * @param string $loginId ログインID
     */
    protected function deleteIdLevelFailureLogs($loginId) {
        $loginId = strtoupper(nvl($loginId));

        $sql  = " UPDATE T_AuthenticationLog SET DeleteFlg = 1 ";
        $sql .= " WHERE  1 = 1 ";
        $sql .= " AND    LogType = :LogType ";
        $sql .= " AND    TargetApp = :TargetApp ";
        $sql .= " AND    LoginId = :LoginId ";
        $sql .= " AND    Result = :Result ";
        $sql .= " AND    DeleteFlg = 0 ";

        $prm = array(
                ':LogType' => self::LOGTYPE_AUTHED,
                ':TargetApp' => $this->getTargetApp(),
                ':LoginId' => $loginId,
                ':Result' => self::RESULT_AUTH_FAILURE,
        );

        $this->_adapter->query($sql)->execute($prm);
    }

    /**
     * 指定ログインIDの過去の認証失敗ログを取得する
     *
     * @param string $loginId ログインID
     * @return array
     */
    public function findRejectedLogByLoginId($loginId) {
        // IDレベルロックアウト設定が無効な場合は常に0件を返す
        if(!$this->getIdLevelLockoutEnabled()) return array();

        $loginId = strtoupper(nvl($loginId));

        $sql  = " SELECT * FROM T_AuthenticationLog ";
        $sql .= " WHERE  1 = 1 ";
        $sql .= " AND    LogType = :LogType ";
        $sql .= " AND    TargetApp = :TargetApp ";
        $sql .= " AND    LoginId = :LoginId ";
        $sql .= " AND    Result = :Result ";
        $sql .= " AND    DeleteFlg = 0 ";

        $prm = array(
            ':LogType' => self::LOGTYPE_AUTHED,
            ':TargetApp' => $this->getTargetApp(),
            ':LoginId' => $loginId,
            ':Result' => self::RESULT_AUTH_FAILURE,
        );

        $setting = $this->getIdLevelLockoutSetting();
        if($setting->isIntervalAvailable()) {
            $sql .= sprintf(" AND    LogTime >= ('%s' - INTERVAL %d SECOND) ", date('Y-m-d H:i:s'), $setting->getJudgementIntervalTime());
        }
        $sql .= " ORDER BY Seq ";

        return ResultInterfaceToArray($this->_adapter->query($sql)->execute($prm));
    }

    /**
     * 指定のログインID、代理認証ID、クライアントハッシュの過去の認証失敗ログを取得する
     *
     * @param string $loginId ログインID
     * @param string | null $altLoginId 代理認証ID
     * @param string | null $hash クライアントを識別するためのハッシュコード
     * @return array
     */
    public function findRejectedLogByClientHash($loginId, $altLoginId = null, $hash = null) {
        // クライアントレベルロックアウト設定が無効な場合は常に0を返す
        if(!$this->getClientLevelLockoutEnabled()) return array();

        $loginId = strtoupper(nvl($loginId));
        $altLoginId = strtoupper(nvl($altLoginId));
        $hash = nvl($hash);

        $sql  = " SELECT * FROM T_AuthenticationLog ";
        $sql .= " WHERE  1 = 1 ";
        $sql .= " AND    LogType = :LogType ";
        $sql .= " AND    TargetApp = :TargetApp ";
        $sql .= " AND    LoginId = :LoginId ";
        $sql .= " AND    Result = :Result ";
        $sql .= " AND    DeleteFlg = 0 ";

        $prm = array(
                ':LogType' => self::LOGTYPE_AUTHED,
                ':TargetApp' => $this->getTargetApp(),
                ':LoginId' => $loginId,
                ':Result' => self::RESULT_AUTH_FAILURE,
        );

        if(strlen($altLoginId)) {
            $sql .= " AND    AltLoginId = :AltLoginId ";
            $prm = array_merge($prm, array(':AltLoginId' => $altLoginId));

        }
        if(strlen($hash)) {
            $sql .= " AND    ClientHash = :ClientHash ";
            $prm = array_merge($prm, array(':ClientHash' => $hash));
        }
        $setting = $this->getClientLevelLockoutSetting();
        if($setting->isIntervalAvailable()) {
            $sql .= sprintf(" AND    LogTime >= ('%s' - INTERVAL %d SECOND) ", date('Y-m-d H:i:s'), $setting->getJudgementIntervalTime());
        }
        $sql .= " ORDER BY Seq ";

        return ResultInterfaceToArray($this->_adapter->query($sql)->execute($prm));
    }

    /**
     * 指定のログインID、代理認証ID、IPアドレス、HTTP_USER_AGENTの過去の認証失敗ログを取得する
     *
     * @param string $loginId ログインID
     * @param string | null $altLoginId 代理認証ID
     * @param string | null $ipAddress IPアドレス
     * @param string | null $userAgent HTTP_USER_AGENT
     * @return array
     */
    public function findRejectedLogByClientInfo($loginId, $altLoginId = null,
                                                $ipAddress = null, $userAgent = null) {
        $hash = $this->calcClientHash($ipAddress, $userAgent);
        return $this->findRejectedLogByClientHash($loginId, $altLoginId, $hash);
    }

    /**
     * 指定のログインIDがロックされているかを判断する
     *
     * @param string $loginId ログインID
     * @return boolean
     */
    public function judgeLockedByLoginId($loginId) {
        0;
        // IDレベルロックアウト設定が無効な場合は常にfalseを返す
        if(!$this->getIdLevelLockoutEnabled()) return false;

        $loginId = strtoupper(nvl($loginId));

        $sql  = " SELECT * FROM T_AuthenticationLog ";
        $sql .= " WHERE  1 = 1 ";
        $sql .= " AND    LogType = :LogType ";
        $sql .= " AND    TargetApp = :TargetApp ";
        $sql .= " AND    LoginId = :LoginId ";
        $sql .= " AND    Result = :Result ";
        $sql .= " AND    DeleteFlg = 0 ";

        $prm = array(
                ':LogType' => self::LOGTYPE_LOCKED,
                ':TargetApp' => $this->getTargetApp(),
                ':LoginId' => $loginId,
                ':Result' => self::RESULT_LOCK_ID,
        );

        $setting = $this->getIdLevelLockoutSetting();
        if($setting->isLockoutTimeAvailable()) {
            $sql .= sprintf(" AND    LogTime >= ('%s' - INTERVAL %d MINUTE) ", date('Y-m-d H:i:s'), $setting->getLockoutTime());
        }
        $sql .= " ORDER BY Seq ";

        $ri = $this->_adapter->query($sql)->execute($prm);

        return $ri->count() > 0;
    }

    /**
     * 指定のログインID、代理認証ID、クライアントハッシュのアカウントがロックされているかを判断する
     *
     * @param string $loginId ログインID
     * @param string | null $altLoginId 代理認証ID
     * @param srintg | null $hash クライアントを識別するためのハッシュコード
     * @return boolean
     */
    public function judgeLockedByClientHash($loginId, $altLoginId = null, $hash = null) {
        // クライアントレベルロックアウト設定が無効な場合は常にfalseを返す
        if(!$this->getClientLevelLockoutEnabled()) return false;

        $loginId = strtoupper(nvl($loginId));
        $altLoginId = strtoupper(nvl($altLoginId));
        $hash = nvl($hash);

        $sql  = " SELECT * FROM T_AuthenticationLog ";
        $sql .= " WHERE  1 = 1 ";
        $sql .= " AND    LogType = :LogType ";
        $sql .= " AND    TargetApp = :TargetApp ";
        $sql .= " AND    LoginId = :LoginId ";
        $sql .= " AND    Result = :Result ";
        $sql .= " AND    DeleteFlg = 0 ";

        $prm = array(
                ':LogType' => self::LOGTYPE_LOCKED,
                ':TargetApp' => $this->getTargetApp(),
                ':LoginId' => $loginId,
                ':Result' => self::RESULT_LOCK_CLIENT,
        );

        if(strlen($altLoginId)) {
            $sql .= " AND    AltLoginId = :AltLoginId ";
            $prm = array_merge($prm, array(':AltLoginId' => $altLoginId));

        }
        if(strlen($hash)) {
            $sql .= " AND    ClientHash = :ClientHash ";
            $prm = array_merge($prm, array(':ClientHash' => $hash));
        }
        $setting = $this->getClientLevelLockoutSetting();
        if($setting->isLockoutTimeAvailable()) {
            $sql .= sprintf(" AND    LogTime >= ('%s' - INTERVAL %d MINUTE) ", date('Y-m-d H:i:s'), $setting->getLockoutTime());
        }
        $sql .= " ORDER BY Seq ";

        $ri = $this->_adapter->query($sql)->execute($prm);

        return $ri->count() > 0;
    }

    /**
     * 指定のログインID、代理認証ID、IPアドレス、HTTP_USER_AGENTのアカウントがロックされているかを判断する
     *
     * @param string $loginId ログインID
     * @param string | null $altLoginId 代理認証ID
     * @param string | null $ipAddress IPアドレス
     * @param string | null $userAgent HTTP_USER_AGENT
     * @return boolean
     */
    public function judgeLockedByClientInfo($loginId, $altLoginId,
                                            $ipAddress, $userAgent) {
        $hash = $this->calcClientHash($ipAddress, $userAgent);
        return $this->judgeLockedByClientHash($loginId, $altLoginId, $hash);
    }

    /**
     * 現在の対象アプリケーションのクライアントレベルロックアウト情報を取得する
     *
     * @param null | boolean $exclude_unknown_account 未登録IDのロックアウト情報を無視するかのフラグ
     *                                                省略時はtrueで、対象アカウントテーブルで未定義のアカウントの情報は含めない
     * @return array
     */
    public function findClientLevelLockoutInfo($exclude_unknown_account = true) {
        // クライアントレベルロック設定が未使用設定なら空配列を返す
        if(!$this->getClientLevelLockoutEnabled()) return array();

        $app = $this->getTargetApp();
        $setting = $this->getClientLevelLockoutSetting();

        $q = <<<EOQ
SELECT
    log.Seq,
    IFNULL(acc.LoginId, log.LoginId) AS LoginId,
    acc.Name,
    acc.ExtraId,
    acc.ExtraInfo,
    log.LogTime,
    log.ClientHash,
    (log.LogTime + INTERVAL :lockout_time MINUTE) AS ReleaseTime,
    log.OemAccessId
FROM
    T_AuthenticationLog log %s JOIN
    (%s) acc ON acc.LoginId = log.LoginId
WHERE
    log.LogType = 1 AND
    log.TargetApp = :app AND
    log.Result = 1 AND
    log.DeleteFlg = 0
%s
ORDER BY
    log.Seq DESC
EOQ;
        $join_type = $exclude_unknown_account ? 'INNER' : 'LEFT OUTER';
        $view = $this->_getInlineViewForLockoutInfo();

        // バインドするパラメータを整備
        $params = array(
                        'lockout_time' => $setting->getLockoutTime(),
                        'app' => $app);

        $conditions = '';
        // 自動解除有効時の追加整備
        if($setting->isLockoutTimeAvailable()) {
            $conditions = 'HAVING ReleaseTime >= :current_time';
            $params['current_time'] = date('Y-m-d H:i:s');
        } else {
            $params['lockout_time'] = 0;
        }

        // ログに結合するインラインビューと結合方法を展開
        $q = sprintf($q, $join_type, $view, $conditions);

        // SQLを実行し結果を返す
        return ResultInterfaceToArray($this->_adapter->query($q)->execute($params));
    }

    /**
     * 現在の対象アプリケーションのIDレベルロックアウト情報を取得する
     *
     * @param null | boolean $exclude_unknown_account 未登録IDのロックアウト情報を無視するかのフラグ
     *                                                省略時はtrueで、対象アカウントテーブルで未定義のアカウントの情報は含めない
     * @return array
     */
    public function findIdLevelLockoutInfo($exclude_unknown_account = true) {
        // IDレベルロック設定が未使用設定なら空配列を返す
        if(!$this->getIdLevelLockoutEnabled()) return array();

        $app = $this->getTargetApp();
        $setting = $this->getIdLevelLockoutSetting();

        $q = <<<EOQ
SELECT
    log.Seq,
    IFNULL(acc.LoginId, log.LoginId) AS LoginId,
    acc.Name,
    acc.ExtraId,
    acc.ExtraInfo,
    log.LogTime,
    log.ClientHash,
    (log.LogTime + INTERVAL :lockout_time MINUTE) AS ReleaseTime,
    log.OemAccessId
FROM
    T_AuthenticationLog log %s JOIN
    (%s) acc ON acc.LoginId = log.LoginId
WHERE
    log.LogType = 1 AND
    log.TargetApp = :app AND
    log.Result = 2 AND
    log.DeleteFlg = 0
%s
ORDER BY
    log.Seq DESC
EOQ;
        $join_type = $exclude_unknown_account ? 'INNER' : 'LEFT OUTER';
        $view = $this->_getInlineViewForLockoutInfo();

        // バインドするパラメータを整備
        $params = array(
                        'lockout_time' => $setting->getLockoutTime(),
                        'app' => $app);

        $conditions = '';
        // 自動解除有効時の追加整備
        if($setting->isLockoutTimeAvailable()) {
            $conditions = 'HAVING ReleaseTime >= :current_time';
            $params['current_time'] = date('Y-m-d H:i:s');
        } else {
            $params['lockout_time'] = 0;
        }

        // ログに結合するインラインビューと結合方法を展開
        $q = sprintf($q, $join_type, $view, $conditions);

        // SQLを実行し結果を返す
        return ResultInterfaceToArray($this->_adapter->query($q)->execute($params));
    }

    /**
     * ロックアウト情報抽出SQLで使用する、各アプリケーション向けのインラインビュー定義を取得する
     *
     * @access protected
     * @return string SQL中に埋め込むアプリケーション固有のインラインビュー定義
     */
    protected function _getInlineViewForLockoutInfo() {
        switch($this->getTargetApp()) {
            // ------------------------------ CBオペレータ向け - T_Operator ＋ M_GeneralPurpose
            case self::APP_CBADMIN:
                return <<<EOV1
SELECT
    op.LoginId,
    op.NameKj AS Name,
    op.RoleCode AS ExtraId,
    gp.Caption AS ExtraInfo
FROM
    T_Operator op LEFT OUTER JOIN
    M_GeneralPurpose gp ON (gp.Class = 52 AND gp.Code = op.RoleCode)
EOV1;
                break;

            // ------------------------------ OEMオペレータ向け - T_Oem ＋ T_OemOperator
            case self::APP_OEMADMIN:
                return <<<EOV2
SELECT
    op.LoginId,
    oem.OemNameKj AS Name,
    oem.OemId AS ExtraId,
    NULL AS ExtraInfo
FROM
    T_OemOperator op INNER JOIN
    T_Oem oem ON oem.OemId = op.OemId
EOV2;
                break;

            // ------------------------------ 事業者向け - T_Enterprise ＋ T_Oem
            case self::APP_MEMBER:
            case self::APP_OEMMEMBER:
                return <<<EOV3
SELECT
    ent.LoginId,
    ent.EnterpriseNameKj AS Name,
    IFNULL(oem.OemId, 0) AS ExtraId,
    IFNULL(oem.OemNameKj, '(キャッチボール)') AS ExtraInfo
FROM
    T_Enterprise ent LEFT OUTER JOIN
    T_Oem oem ON oem.OemId = ent.OemId
EOV3;
                break;
        }
    }

}
