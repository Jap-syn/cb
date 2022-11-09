<?php
namespace Coral\Coral\Auth;

use Coral\Base\Auth\BaseAuthManager;
use Coral\Base\Auth\BaseAuthException;
use Coral\Base\Auth\BaseAuthUtility;
use Coral\Base\BaseLog;
use Coral\Base\BaseDelegate;
use models\Table\TableSystemProperty;
use Zend\Authentication\Result;
use Zend\Log\Logger;
use Zend\Session\Container;

/**
 * ハッシュ化パスワードおよび代理認証機能を拡張した認証マネージャ
 */
class CoralAuthManager extends BaseAuthManager {
	const ALT_USERINFO_SESS_KEY = 'altUserInfo';

	// 代理認証IDセパレータ種別：シングルコロン
    const ALT_TABLE_SINGLECOLON = 'singlecolon';

	// 代理認証IDセパレータ種別：ダブルコロン
    const ALT_TABLE_DOUBLECOLON = 'doublecolon';

	// 代理認証設定キー：認証テーブル
	const ALT_SETTING_KEY_TABLE = 'table';

	// 代理認証設定キー：IDカラム
	const ALT_SETTING_KEY_IDCOL = 'id_col';

	// 代理認証設定キー：パスワードカラム
	const ALT_SETTING_KEY_PSWCOL = 'psw_col';

	/**
	 * 指定の値が代理認証IDセパレータ種別として適切かを判断する
	 *
	 * @static
	 * @param string $mode 検証する値
	 * @return boolean
	 */
    public static function isValidAlternativeTableSettingMode($mode) {
       return in_array($mode, array(self::ALT_TABLE_SINGLECOLON, self::ALT_TABLE_DOUBLECOLON));
    }

	/**
	 * 代理認証設定向けの有効なキーを配列で取得する
	 *
	 * @static
	 * @return array
	 */
	public static function getValidSettingsKeys() {
		return array(
			self::ALT_SETTING_KEY_TABLE,
			self::ALT_SETTING_KEY_IDCOL,
			self::ALT_SETTING_KEY_PSWCOL
		);
	}

	/**
	 * 代理認証向け設定
	 *
	 * @access protected
	 * @var array
	 */
    protected $_altAuthTableSettings = array();

	/**
	 * 最後に使用したZend\Authentication\Adapter\DbTable
	 *
	 * @access protected
	 * @var Zend\Authentication\Adapter\DbTable
	 */
	protected $_lastAuthAdapter = null;

	/**
	 * 内部で使用するロガー
	 *
	 * @access protected
	 * @var BaseLog
	 */
	protected $_logger = null;

	/**
	 * 実認証処理前に呼び出すコールバック
	 *
	 * @access protected
	 * @var BaseDelegate
	 */
	protected $_beforeAuthenticateCallback;

	/**
	 * 実認証処理後に呼び出すコールバック
	 *
	 * @access protected
	 * @var BaseDelegate
	 */
	protected $_afterAuthenticateCallback;

	/**
	 * 代理認証設定を取得する
	 *
	 * @param string $mode 認証IDセパレータ種別。ALT_TABLE_SINGLECOLONまたはALT_TABLE_DOUBLECOLONを指定
	 * @return $modeと関連付けられた設定連想配列。キー'tbl'、'idCol'、'pswCol'を含む
	 */
    public function getAlternativeAuthTableSetting($mode) {
        if(!self::isValidAlternativeTableSettingMode($mode)) {
            throw new BaseAuthException('invalid mode specified');
        }
        return isset($this->_altAuthTableSettings[$mode]) ? $this->_altAuthTableSettings[$mode] : null;
    }
	/**
	 * 代理認証設定を設定する
	 *
	 * @param string $mode 認証IDセパレータ種別。ALT_TABLE_SINGLECOLONまたはALT_TABLE_DOUBLECOLONを指定
	 * @param array $modeと関連付けられた設定連想配列。キー'tbl'、'idCol'、'pswCol'に設定値を関連付けている必要がある
	 * @return CoralAuthManager このインスタンス
	 */
    public function setAlternativeAuthTableSetting($mode, array $setting) {
        if(!self::isValidAlternativeTableSettingMode($mode)) {
            throw new BaseAuthException('invalid mode specified');
        }

        // Key-Valueを整備
		$fixed_setting = array();
		$has_invalid_key = false;
		foreach(self::getValidSettingsKeys() as $valid_key) {
			if(!isset($setting[$valid_key])) {
				$has_invalid_key = true;
			} else {
				$fixed_setting[$valid_key] = $setting[$valid_key];
			}
		}

		// キーがすべてそろっていた場合のみ設定を実行
		if(!$has_invalid_key) {
			$this->_altAuthTableSettings[$mode] = $fixed_setting;
		} else {
			$this->_altAuthTableSettings[$mode] = null;
		}

        return $this;
    }
	/**
	 * 代理認証設定が設定されているかを判断する
	 *
	 * @return boolean
	 */
    public function hasAlternativeTableSetting() {
        $settings = $this->_altAuthTableSettings;
        if(
           isset($setting[self::ALT_TABLE_SINGLECOLON]) &&
           is_array($setting[self::ALT_TABLE_SINGLECOLON])) {

            return true;
        }
        if(
           isset($setting[self::ALT_TABLE_DOUBLECOLON]) &&
           is_array($setting[self::ALT_TABLE_DOUBLECOLON])) {

            return true;
        }
        return false;
    }

	/**
	 * 最後に代理認証を実行した際のユーザデータを取得する
	 *
	 * @return stdClass
	 */
	public function getAlternativeUserInfo() {
		$sess = new Container($this->getSessNameSpaceForAltUserInfo());
		return $sess->{self::ALT_USERINFO_SESS_KEY};
	}

	/**
	 * このインスタンスが使用するロガーを取得する
	 *
	 * @return BaseLog
	 */
	public function getLogger() {
		return $this->_logger;
	}
	/**
	 * このインスタンスが使用するロガーを設定する
	 *
	 * @param BaseLog $logger
	 * @return CoralAuthManager このインスタンス
	 */
	public function setLogger(BaseLog $logger) {
		$this->_logger = $logger;
		return $this;
	}

	/**
	 * 代理認証時の代理ログインユーザ情報を取得します
	 *
	 * @access public
	 * @return mixed
	 */
	public function getSessNameSpaceForAltUserInfo() {
		return sprintf('%s_AltUserInfo', $this->getSessionNamespace());
	}

	/**
	 * 実認証処理前に呼び出されるコールバックを取得する
	 * コールバックは以下のパラメータを受け取ることが可能
	 * - $login_id 認証に使用されるログインID
	 * - $alt_login_id 代理認証に使用される代理ログインID
	 * コールバックからの戻り値は常に無視されるため、呼出し元に対するフィードバックは例外を通じて行う必要がある
	 *
	 * @return BaseDelegate
	 */
	public function getBeforeAuthenticateCallback() {
	    return $this->_beforeAuthenticateCallback;
	}
	/**
	 * 実認証処理前に呼び出されるコールバックを設定する。
	 * コールバックは以下のパラメータを受け取ることが可能
	 * - $login_id 認証に使用されるログインID
	 * - $alt_login_id 代理認証に使用される代理ログインID
	 * コールバックからの戻り値は常に無視されるため、呼出し元に対するフィードバックは例外を通じて行う必要がある
	 *
	 * @param BaseDelegate $callback コールバック
	 * @return CoralAuthManager このインスタンス
	 */
	public function setBeforeAuthenticateCallback(BaseDelegate $callback) {
	    $this->_beforeAuthenticateCallback = $callback;
	    return $this;
	}

	/**
	 * 実認証処理後に呼び出されるコールバックを取得する
	 * このコールバックは、メインの認証処理および登録されているすべての追加認証処理が完了した後に呼び出され、
	 * 以下のパラメータを受け取ることが可能
	 * - $login_id 認証に使用されたログインID
	 * - $alt_login_id 代理認証に使用された代理ログインID
	 * - $auth_result 認証結果を示すbool値
	 * コールバックからの戻り値は常に無視されるため、呼出し元に対するフィードバックは例外を通じて行う必要がある
	 *
	 * @return BaseDelegate
	 */
	public function getAfterAuthenticateCallback() {
	    return $this->_afterAuthenticateCallback;
	}
	/**
	 * 実認証処理後に呼び出されるコールバックを設定する。
	 * このコールバックは、メインの認証処理および登録されているすべての追加認証処理が完了した後に呼び出され、
	 * 以下のパラメータを受け取ることが可能
	 * - $login_id 認証に使用されたログインID
	 * - $alt_login_id 代理認証に使用された代理ログインID
	 * - $auth_result 認証結果を示すbool値
	 * コールバックからの戻り値は常に無視されるため、呼出し元に対するフィードバックは例外を通じて行う必要がある
	 *
	 * @param BaseDelegate $callback コールバック
	 * @return CoralAuthManager このインスタンス
	 */
	public function setAfterAuthenticateCallback(BaseDelegate $callback) {
	    $this->_afterAuthenticateCallback = $callback;
	    return $this;
	}

	/**
	 * IDとパスワードを指定してユーザ認証処理を行います。
	 *
	 * @access public
	 * @param $userId string 認証ID
	 * @param $password string 認証パスワード
	 * @return ZendAuthResult 認証結果オブジェクト
	 */
	public function login($userId, $password) {
		// 代理認証結果をクリア
		$sess = new Container($this->getSessNameSpaceForAltUserInfo());
		unset($sess->{self::ALT_USERINFO_SESS_KEY});

        // userIdを分離して代理認証を適用するかを判断
        if(preg_match('/^([^:]+)(::?)(.+)$/', $userId, $userIdInfo)) {
            // 代理認証
            $userId = $userIdInfo[1];
            $altAuthKey = $userIdInfo[2] == '::' ?
                self::ALT_TABLE_DOUBLECOLON : self::ALT_TABLE_SINGLECOLON;
            $altUserId = $userIdInfo[3];
            $altPassword = $password;
            if(!$this->getAlternativeAuthTableSetting($altAuthKey)) {
                $altAuthKey = null;
                $altUserId = null;
                $altPassword = null;
            }
        } else {
            // 通常認証
            $altAuthKey = null;
            $altUserId = null;
            $altPassword = null;
        }

        // 認証前コールバックを呼び出す
        $callback = $this->getBeforeAuthenticateCallback();
        if($callback) $callback->invoke($userId, $altUserId);

        // 認証実処理を実行
        $result = $this->autenticate($userId, $password, $altAuthKey, $altUserId, $altPassword);

        // 認証後コールバックを呼び出す
        $callback = $this->getAfterAuthenticateCallback();
        try {
            if($callback) $callback->invoke($userId, $altUserId, $result->isValid());
        } catch(\Exception $err) {
            // 認証後コールバックで例外が発生したらログアウト処理後に例外を上位へスロー
            $this->logout();
            throw $err;
        }

        // 認証結果を返す
        return $result;
    }

    /**
     * ログインID、パスワード、代理認証キー、代理認証ID、代理認証パスワードで認証実処理を実行する
     *
     * @access protected
     * @param string $userId ログインID
     * @param string $password ログインパスワード
     * @param string | null $altAuthKey 代理認証キー。ALT_TABLE_SINGLECOLON、ALT_TABLE_DOUBLECOLONまたはnullを指定する
     * @param string | null $altUserId 代理認証で使用するログインID
     * @param string | null $altPassword 代理認証で使用するパスワード
     * @return ZendAuthResult 認証結果オブジェクト
     */
    protected function autenticate($userId, $password, $altAuthKey, $altUserId, $altPassword) {
        // 2015.4.8 loginメソッドから実処理部分を分離（eda）

        $altUserInfo = null;
        if($altAuthKey) {
            // 先行して代理認証を実行
            $preAuthResult = $this->_login($altAuthKey, $altUserId, $altPassword);
            // 代理認証NGなら即時結果返却
            if(!$preAuthResult->isValid()) return $preAuthResult;

            // 認証結果から得られたユーザデータを退避
            $altUserInfo = $this->_lastAuthAdapter->getResultRowObject();

            // 本認証向けにユーザ情報を取得
            $userRow = $this->_getTargetUserRow( $this->getAuthTableName(), $this->getAuthIdColumn(), $userId );
            if(count($userRow) == 1) {
                $userRow = $userRow->current();
            } else {
                $userRow = array();
            }
            // 得られた対象ユーザ情報を使用して本認証実行
            $result = $this->_login(
                                    // 本認証用テーブルを指定
                                    null,
                                    // userRowから得たIDとパスワードを使用
                                    $userRow[$this->getAuthIdColumn()],
                                    $userRow[$this->getAuthPasswordColumn()],
                                    // ハッシュなし
                                    true
                                    );
        } else {
            // 本認証実行
            $result = $this->_login(null, $userId, $password);
        }

        // 認証成功時は追加認証処理を適用
        if($result->isValid()) {
            $userInfo = $this->_lastAuthAdapter->getResultRowObject();

            try {
                if(!$this->execAdditionalAuthentications($userId, $password, $userInfo, $altUserInfo)) {
                    // 認証結果をクリア
                    $this->logout();
                    // 追加認証でNGになったら認証失敗
                    $result = new Result( Result::FAILURE_IDENTITY_NOT_FOUND, $userId, array('identity not found') );
                } else {
                    $sess = new Container($this->getSessNameSpaceForAltUserInfo());
                    // 認証成功が確定したのでストレージへ永続化
                    $this->_auth->getStorage()->write($userInfo);
                    // 代理認証情報を永続化
                    $sess->{self::ALT_USERINFO_SESS_KEY} = $altUserInfo;
                }
            } catch(\Exception $err) {
                // 追加認証処理内で例外が発生したらログアウト処理後に例外を上位へスロー
                $this->logout();
                throw $err;
            }
        } else {
            $this->logout();
            $result = new Result( Result::FAILURE_IDENTITY_NOT_FOUND, $userId, array('identity not found') );
        }

        // 最終結果を返却
        return $result;
    }

	/**
	 * ログイン状態をリセットし、内部ユーザデータを最新状態に更新する
	 */
	public function resetLoginState() {
		// 認証済み状態の場合のみ実行
		if($this->isAuthenticated()) {
			$tbl = $this->getAuthTableName();
			$idCol = $this->getAuthIdColumn();
			$pswCol = $this->getAuthPasswordColumn();

			$altUserInfo = $this->getAlternativeUserInfo();

			// 現在のユーザデータを取得する
			$userRow = $this->_getTargetUserRow($tbl, $idCol, $this->getUserInfo()->$idCol)->current();

			// 一旦ログアウト
			$this->logout();

			// 取得済みのユーザデータからハッシュ処理なしでログイン
			$result = $this->_login(null, $userRow[$idCol], $userRow[$pswCol], true);

			// ユーザデータの永続化
 			$this->_auth->getStorage()->write($this->_lastAuthAdapter->getResultRowObject());
            $sess = new Container($this->getSessNameSpaceForAltUserInfo());
 			$sess->{self::ALT_USERINFO_SESS_KEY} = $altUserInfo;
		}
	}

	/**
	 * 認証に使用するテーブル種別とユーザID、パスワードを指定して認証処理を実行する
	 *
	 * @param string | null $authTarget 代理認証IDセパレータ種別またはnull。nullは本認証テーブル使用を指定している
	 * @param string $userId 認証用ID
	 * @param string $password 認証パスワード
	 * @param null | boolean $forceHashDisabled パスワードハッシュ未使用を強制するかのフラグ。省略時はfalse（＝自動）
	 * @return ZendAuthResult 認証結果
	 */
    protected function _login($authTarget, $userId, $rawPassword, $forceHashDisabled = false) {
        // 認証アダプタ初期化情報の現在値をバックアップ
        $settingBackup = array(
            self::ALT_SETTING_KEY_TABLE => $this->getAuthTableName(),
            self::ALT_SETTING_KEY_IDCOL => $this->getAuthIdColumn(),
            self::ALT_SETTING_KEY_PSWCOL => $this->getAuthPasswordColumn()
        );

        // このメソッドで取り扱う認証テーブル関連情報をセットアップ
        switch($authTarget) {
            case self::ALT_TABLE_SINGLECOLON:
            case self::ALT_TABLE_DOUBLECOLON:
                $info = $this->getAlternativeAuthTableSetting($authTarget);
                $tbl = $info[self::ALT_SETTING_KEY_TABLE];
                $idCol = $info[self::ALT_SETTING_KEY_IDCOL];
                $pswCol = $info[self::ALT_SETTING_KEY_PSWCOL];
                break;
            default:
                $tbl = $settingBackup[self::ALT_SETTING_KEY_TABLE];
                $idCol = $settingBackup[self::ALT_SETTING_KEY_IDCOL];
                $pswCol = $settingBackup[self::ALT_SETTING_KEY_PSWCOL];
                break;
        }
        $targetRow = $this->_getTargetUserRow($tbl, $idCol, $userId);
        // 対象ユーザ行が見つからないか複数該当の場合は即時認証失敗
        if(count($targetRow) != 1) {
            return new Result(
                Result::FAILURE_IDENTITY_NOT_FOUND,
                $userId,
                array('identity not found') );
        }
        $targetRow = $targetRow->current();

        $result = null;
        $error = null;
        $db = $this->getDbAdapter();
        // 認証アダプタをクリア
        $this->_lastAuthAdapter = null;
        try {
            // システムプロパティからハッシュ用SALTを取得し認証ユーティリティを初期化
            $sysProps = new TableSystemProperty($this->_dbAdapter);
            $authUtil = new BaseAuthUtility($sysProps->getHashSalt());

            // ハッシュロジックを停止するかの設定
            if($forceHashDisabled || !isset($targetRow['Hashed']) || !$targetRow['Hashed']) {
                // 強制ハッシュ化停止またはアカウントがハッシュ済みでない場合はハッシュロジックを使用しない
                $authUtil->setHashDisabled(true);
            }
            $loginInfoArray = explode("@", $userId);
            if (count($loginInfoArray) >= 3 && ! empty($_COOKIE[$loginInfoArray[0]. "@". "autologin"])) {
                $password = $rawPassword;
            }
            else
            {
                $password = $authUtil->generatePasswordHash($userId, $rawPassword);
            }

            // 認証アダプタ情報を一時的に変更
            $this
                ->setAuthTableName($tbl)
                ->setAuthIdColumn($idCol)
                ->setAuthPasswordColumn($pswCol);

            $adp = $this->_lastAuthAdapter =
                        $this->getAuthAdapter()->setIdentity($userId)->setCredential($password);

            // 認証実行
            $result = $this->_auth->authenticate($adp);

        } catch(Exception $err) {
            $error = $err;
        }

		// 認証アダプタ情報を復元
        $this
            ->setAuthTableName($settingBackup[self::ALT_SETTING_KEY_TABLE])
            ->setAuthIdColumn($settingBackup[self::ALT_SETTING_KEY_IDCOL])
            ->setAuthPasswordColumn($settingBackup[self::ALT_SETTING_KEY_PSWCOL]);

        if($error) {
            // 例外発生時は認証失敗
            return new Result(
                Result::FAILURE_UNCATEGORIZED,
                $userId,
                array(sprintf('an error has occured. error = %s', $error->getMessage())) );
        } else {
            // 認証結果をそのまま返す
            return $result;
        }
    }

	/**
	 * オーバーライド。このセッションのユーザをログアウトします。
	 *
	 * @access public
	 */
	public function logout() {
		parent::logout();

		// 代理認証情報をクリア
		$sess = new Container($this->getSessNameSpaceForAltUserInfo());
		unset($sess->{self::ALT_USERINFO_SESS_KEY});
	}

	/**
	 * 指定認証テーブルからIDが一致する行データを抽出する
	 *
	 * @access protected
	 * @param string $tbl テーブル名
	 * @param string $idCol 認証IDカラム名
	 * @param string $matchId 抽出に使用する認証ID
	 * @return array
	 */
	protected function _getTargetUserRow($tbl, $idCol, $matchId) {
        // SQLインジェクション対策
        $q = "select * from " . $tbl . " where " . $idCol . " = :matchId ";
        return  $this->getDbAdapter()->query($q)->execute(array(':matchId' => $matchId));
	}

    /**
     * 指定の優先度でログメッセージを出力する
     *
     * @param string $message ログメッセージ
     * @param int $priority 優先度
     */
    public function log($message, $priority) {
		$logger = $this->getLogger();
        $message = sprintf('[%s] %s', get_class($this), $message);
		if($logger) {
			$logger->log($priority, $message);
		}
	}

    /**
     * DEBUGレベルでログメッセージを出力する
     *
     * @param string $message ログメッセージ
     */
    public function debug($message) {
		$this->log($message, Logger::DEBUG);
	}

    /**
     * INFOレベルでログメッセージを出力する
     *
     * @param string $message ログメッセージ
     */
    public function info($message) {
		$this->log($message, Logger::INFO);
	}

    /**
     * NOTICEレベルでログメッセージを出力する
     *
     * @param string $message ログメッセージ
     */
    public function notice($message) {
		$this->log($message, Logger::NOTICE);
	}

    /**
     * WARNレベルでログメッセージを出力する
     *
     * @param string $message ログメッセージ
     */
    public function warn($message) {
		$this->log($message, Logger::WARN);
	}

    /**
     * ERRレベルでログメッセージを出力する
     *
     * @param string $message ログメッセージ
     */
    public function err($message) {
		$this->log($message, Logger::ERR);
	}

    /**
     * CRITレベルでログメッセージを出力する
     *
     * @param string $message ログメッセージ
     */
    public function crit($message) {
		$this->log($message, Logger::CRIT);
	}

    /**
     * ALERTレベルでログメッセージを出力する
     *
     * @param string $message ログメッセージ
     */
    public function alert($message) {
		$this->log($message, Logger::ALERT);
	}

    /**
     * EMERGレベルでログメッセージを出力する
     *
     * @param string $message ログメッセージ
     */
    public function emerg($message) {
        $this->log($message, Logger::EMERG);
	}
}
