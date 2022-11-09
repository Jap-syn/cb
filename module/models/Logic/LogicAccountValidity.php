<?php
namespace models\Logic;

use Zend\Db\Adapter\Adapter;

/**
 * パスワード有効期限管理ロジック
 */
class LogicAccountValidity {
    /** アプリケーション名定数：アプリケーション名未指定 @var string*/
    const APPNAME_NOT_SPECIFIED = 'default';

    /** アプリケーション名定数：cbadmin @var string*/
    const APPNAME_CBADMIN = 'cbadmin';

    /** アプリケーション名定数：member @var string */
    const APPNAME_MEMBER = 'member';

    /** アプリケーション名定数：oemadmin @var string */
    const APPNAME_OEMADMIN = 'oemadmin';

    /** アプリケーション名定数：oemmember @var string */
    const APPNAME_OEMMEMBER = 'oemmember';

	/**
	 * アダプタ
	 *
	 * @var Adapter
	 */
	protected $_adapter = null;

    /**
     * 規定の設定
     *
     * @access protected
     * @var array
     */
    protected $_defaultSettings;

    /**
     * DBアダプタを指定してLogicAccountValidityの新しい
     * インスタンスを初期化する
     *
     * @param Adapter $adapter アダプタ
     */
    public function __construct(Adapter $adapter) {
        $this->_adapter = $adapter;
        $this ->_initDefaultSettings();
    }

    /**
     * システムプロパティテーブルモデルを取得する
     *
     * @return TableSystemProperty
     */
    public function getSystemPropertyTable() {
        return new \models\Table\TableSystemProperty($this->_adapter);
    }

    /**
     * オペレータテーブルモデルを取得する
     *
     * @return TableOperator
     */
    public function getOperatorTable() {
        return new \models\Table\TableOperator($this->_adapter);
    }

    /**
     * OEMオペレータテーブルモデルを取得する
     *
     * @return TableOemOperator
     */
    public function getOemOperatorTable() {
        return new \models\Table\TableOemOperator($this->_adapter);
    }

    /**
     * 事業者テーブルモデルを取得する
     *
     * @return TableEnterprise
     */
    public function getEnterpriseTable() {
        return new \models\Table\TableEnterprise($this->_adapter);
    }

    /**
     * デフォルト設定を初期化する
     *
     * @access protected
     */
    protected function _initDefaultSettings() {
        $this->_defaultSettings = array(
            // パスワード有効期限制約の有効／無効設定 → 無効
            'enabled' => false,

            // パスワード有効日数設定 → システム共通設定のみ60日に規定
            \models\Table\TableSystemProperty::FIX_CATEGORY_PSW_VALIDITY => array(
                self::APPNAME_NOT_SPECIFIED => 60
            ),

            // パスワード期限切れアラート日数設定 → システム共通設定のみ60日に規定
            \models\Table\TableSystemProperty::FIX_CATEGORY_PSW_LIMIT_ALERT => array(
                self::APPNAME_NOT_SPECIFIED => 7
            )
        );
    }

    /**
     * パスワード有効日数設定に関するシステムプロパティのカテゴリ名を取得する
     *
     * @access protected
     * @return string
     */
    protected function getCategoryNameForValidity() {
        return \models\Table\TableSystemProperty::FIX_CATEGORY_PSW_VALIDITY;
    }

    /**
     * パスワード期限切れアラート設定に関するシステムプロパティのカテゴリ名を取得する
     *
     * @access protected
     * @return string
     */
    protected function getCategoryNameForLimitAlert() {
        return \models\Table\TableSystemProperty::FIX_CATEGORY_PSW_LIMIT_ALERT;
    }

    /**
     * 各種設定向けプロパティ名として有効なアプリケーション名であるかを判断する
     *
     * @access protected
     * @return boolean
     */
    protected function isValidAppName($appName) {
        $names = array(
            self::APPNAME_CBADMIN,
            self::APPNAME_MEMBER,
            self::APPNAME_OEMADMIN,
            self::APPNAME_OEMMEMBER
        );
        return in_array(nvl($appName), $names);
    }

    /**
     * パスワード有効期限制約機能が有効であるかを取得する
     *
     * @return boolean
     */
    public function passwordValidityEnabled() {
        $sysProps = $this->getSystemPropertyTable();
        $cat = $this->getCategoryNameForValidity();
        $name = 'enabled';
        if($sysProps->propNameExists($name, $cat)) {
            return $sysProps->getValue(\models\Table\TableSystemProperty::DEFAULT_MODULE, $cat, $name) ? true : false;
        }
        return $this->_defaultSettings[$name] ? true : false;
    }

    /**
     * システム共通のパスワード有効日数を取得する
     *
     * @return int システム共通のデフォルト値。パスワード有効期限制約機能が無効な場合は-1
     */
    public function getDefaultValidityDays() {
        if(!$this->passwordValidityEnabled()) return -1;

        $sysProps = $this->getSystemPropertyTable();
        $cat = $this->getCategoryNameForValidity();
        $name = self::APPNAME_NOT_SPECIFIED;
        if($sysProps->propNameExists($name, $cat)) {
            return (int)$sysProps->getValue('[DEFAULT]', $cat, $name);
        }
        return $this->_defaultSettings[$cat][$name];
    }

    /**
     * 指定アプリケーション向けのパスワード有効日数を取得する
     *
     * @param string $appName アプリケーション名。cbadmin、member、oemadmin、oemmemberの
     *                        いずれかを指定可能で、これ以外を指定した場合は例外が発生する
     * @return int 指定アプリケーションのパスワード有効日数。パスワード有効期限制約機能が無効な場合は-1
     */
    public function getValidityDays($appName) {
        $appName = nvl($appName);
        if(!$this->isValidAppName($appName)) {
            throw new \Exception('invalid application-name specified');
        }

        if(!$this->passwordValidityEnabled()) return -1;

        $sysProps = $this->getSystemPropertyTable();
        $cat = $this->getCategoryNameForValidity();
        if(!$sysProps->propNameExists($appName, $cat)) {
            // アプリケーション固有値が設定されていない場合はシステム共通値を返す
            return $this->getDefaultValidityDays();
        }
        return (int)$sysProps->getValue('[DEFAULT]', $cat, $appName);
    }

    /**
     * システム共通のパスワード期限切れアラート日数を取得する
     *
     * @return int システム共通のデフォルト値。パスワード有効期限制約機能が無効な場合は-1
     */
    public function getDefaultLimitAlertDays() {
        if(!$this->passwordValidityEnabled()) return -1;

        $sysProps = $this->getSystemPropertyTable();
        $cat = $this->getCategoryNameForLimitAlert();
        $name = self::APPNAME_NOT_SPECIFIED;
        if($sysProps->propNameExists($name, $cat)) {
            return (int)$sysProps->getValue('[DEFAULT]', $cat, $name);
        }
        return $this->_defaultSettings[$cat][$name];
    }

    /**
     * 指定アプリケーション向けのパスワード期限切れアラート日数を取得する
     *
     * @param string $appName アプリケーション名。cbadmin、member、oemadmin、oemmemberの
     *                        いずれかを指定可能で、これ以外を指定した場合は例外が発生する
     * @return int 指定アプリケーションのパスワード期限切れアラート日数。パスワード有効期限制約機能が無効な場合は-1
     */
    public function getLimitAlertDays($appName) {
        $appName = nvl($appName);
        if(!$this->isValidAppName($appName)) {
            throw new \Exception('invalid application-name specified');
        }

        if(!$this->passwordValidityEnabled()) return -1;

        $sysProps = $this->getSystemPropertyTable();
        $cat = $this->getCategoryNameForLimitAlert();
        if(!$sysProps->propNameExists($appName, $cat)) {
            // アプリケーション固有値が設定されていない場合はシステム共通値を返す
            return $this->getDefaultLimitAlertDays();
        }
        return (int)$sysProps->getValue('[DEFAULT]', $cat, $appName);
    }

    /**
     * 指定の日付時刻文字列を日付文字列に変換する
     *
     * @access protected
     * @param string $dateTime 日付時刻文字列
     * @return string 日付文字列
     */
    protected function dateTimeToDate($dateTime) {
        return date('Y-m-d', strtotime($dateTime));
    }

    /**
     * 指定の日付文字列に指定日数を加算した結果を返す
     *
     * @access protected
     * @param string $date 日付文字列
     * @param int $days 加算する日数。正の値を指定した場合は$dateより未来、
     *                  負の値を指定した場合は$dateより過去になる
     * @return string 計算後の日付文字列
     */
    protected function calcDate($date, $days) {
        $days = (int)$days;
        return date('Y-m-d', strtotime($date) + ($days * 86400));
    }

    // ----------------------------------------------------------------- オペレータ関連判断メソッド

    /**
     * 指定オペレータのパスワード期限切れ日を取得する
     *
     * @param int $id オペレータID
     * @return string 期限切れ日。有効期限制約無効時や、パスワード最終更新日未設定時は
     *                nullを返す
     */
    public function getOperatorExpireDate($id) {
        // アカウントデータ取得
        $data = $this->getOperatorTable()->findOperator($id)->current();
        if(!$data) {
            throw new \Exception('account data not found');
        }

        // 有効期限制約無効時はnullを返す
        if(!$this->passwordValidityEnabled()) return null;
        // パスワード最終更新日時未設定時はnullを返す
        if($data['LastPasswordChanged'] == null) return null;

        // 最終更新日時から期限切れ日を算出
        $lastChanged = $this->dateTimeToDate($data['LastPasswordChanged']);
        return $this->calcDate($lastChanged,
                               $this->getValidityDays(self::APPNAME_CBADMIN));
    }

    /**
     * 指定オペレータのパスワードが現在日において期限切れとなっているかを判断する
     *
     * @param int $id オペレータID
     * @return boolean
     */
    public function operatorPasswordIsExpired($id) {
        // 期限切れ日を取得
        $expireDate = $this->getOperatorExpireDate($id);

        // 期限切れ設定がない場合は期限切れでないと判断
        if(!$expireDate) return false;

        // 日付の大小判断結果を返す
        return $expireDate < date('Y-m-d');
    }

    /**
     * 指定オペレータに対してパスワード期限切れ間近のアラートを表示する必要があるかを判断する
     *
     * @param int $id オペレータID
     * @return boolean
     */
    public function needAlertForOperator($id) {
        // 期限切れ日を取得
        $expireDate = $this->getOperatorExpireDate($id);

        // 期限切れ設定がない場合は期限切れでないと判断
        if(!$expireDate) return false;

        // アラート表示開始日を取得
        $limitDays = $this->getLimitAlertDays(self::APPNAME_CBADMIN);
        $alertDate = $this->calcDate($expireDate, -1 * $limitDays);

        // アラート表示開始日が現在日またはそれ以前ならtrue
        return $alertDate <= date('Y-m-d');
    }

    // -------------------------------------------------------------- OEMオペレータ関連判断メソッド

    /**
     * 指定OEMオペレータのパスワード期限切れ日を取得する
     *
     * @param int $id OEMオペレータID
     * @return string 期限切れ日。有効期限制約無効時や、パスワード最終更新日未設定時は
     *                nullを返す
     */
    public function getOemOperatorExireDate($id) {
        // アカウントデータ取得
        $data = $this->getOemOperatorTable()->findOperator($id)->current();
        if(!$data) {
            throw new \Exception('account data not found');
        }

        // 有効期限制約無効時はnullを返す
        if(!$this->passwordValidityEnabled()) return null;
        // パスワード最終更新日時未設定時はnullを返す
        if($data['LastPasswordChanged'] == null) return null;

        // 最終更新日時から期限切れ日を算出
        $lastChanged = $this->dateTimeToDate($data['LastPasswordChanged']);
        return $this->calcDate($lastChanged,
                               $this->getValidityDays(self::APPNAME_OEMADMIN));
    }

    /**
     * 指定OEMオペレータのパスワードが現在日において期限切れとなっているかを判断する
     *
     * @param int $id OEMオペレータID
     * @return boolean
     */
    public function oemOperatorPasswordIsExpired($id) {
        // 期限切れ日を取得
        $expireDate = $this->getOemOperatorExireDate($id);

        // 期限切れ設定がない場合は期限切れでないと判断
        if(!$expireDate) return false;

        // 日付の大小判断結果を返す
        return $expireDate < date('Y-m-d');
    }

    /**
     * 指定OEMオペレータに対してパスワード期限切れ間近のアラートを表示する必要があるかを判断する
     *
     * @param int $id OEMオペレータID
     * @return boolean
     */
    public function needAlertForOemOperator($id) {
        // 期限切れ日を取得
        $expireDate = $this->getOemOperatorExireDate($id);

        // 期限切れ設定がない場合は期限切れでないと判断
        if(!$expireDate) return false;

        // アラート表示開始日を取得
        $limitDays = $this->getLimitAlertDays(self::APPNAME_OEMADMIN);
        $alertDate = $this->calcDate($expireDate, -1 * $limitDays);

        // アラート表示開始日が現在日またはそれ以前ならtrue
        return $alertDate <= date('Y-m-d');
    }

    // --------------------------------------------------------------------- 事業者関連判断メソッド

    /**
     * 指定事業者のパスワード期限切れ日を取得する
     *
     * @param int $id 事業者ID
     * @return string 期限切れ日。有効期限制約無効時や、パスワード最終更新日未設定時は
     *                nullを返す
     */
    public function getEnterpriseExpireDate($id) {
        // アカウントデータ取得
        $data = $this->getEnterpriseTable()->find($id)->current();
        if(!$data) {
            throw new \Exception('account data not found');
        }

        // 有効期限制約無効時はnullを返す
        if(!$this->passwordValidityEnabled()) return null;
        // パスワード最終更新日時未設定時はnullを返す
        if($data['LastPasswordChanged'] == null) return null;

        // アプリケーションを判断
        $appName = ((int)$data['OemId']) != 0 ?
            self::APPNAME_OEMMEMBER : self::APPNAME_MEMBER;

        // 最終更新日時から期限切れ日を算出
        $lastChanged = $this->dateTimeToDate($data['LastPasswordChanged']);
        return $this->calcDate($lastChanged, $this->getValidityDays($appName));
    }

    /**
     * 指定事業者のパスワードが現在日において期限切れとなっているかを判断する
     *
     * @param int $id 事業者ID
     * @return boolean
     */
    public function enterprisePasswordIsExpired($id) {
        // 期限切れ日を取得
        $expireDate = $this->getEnterpriseExpireDate($id);

        // 期限切れ設定がない場合は期限切れでないと判断
        if(!$expireDate) return false;

        // 日付の大小判断結果を返す
        return $expireDate < date('Y-m-d');
    }

    /**
     * 指定事業者に対してパスワード期限切れ間近のアラートを表示する必要があるかを判断する
     *
     * @param int $id 事業者ID
     * @return boolean
     */
    public function needAlertForEnterprise($id) {
        // 期限切れ日を取得
        $expireDate = $this->getEnterpriseExpireDate($id);

        // 期限切れ設定がない場合は期限切れでないと判断
        if(!$expireDate) return false;

        // アプリケーションを判断
        $data = $this->getEnterpriseTable()->find($id)->current();
        $appName = ((int)$data['OemId']) != 0 ?
            self::APPNAME_OEMMEMBER : self::APPNAME_MEMBER;

        // アラート表示開始日を取得
        $limitDays = $this->getLimitAlertDays($appName);
        $alertDate = $this->calcDate($expireDate, -1 * $limitDays);

        // アラート表示開始日が現在日またはそれ以前ならtrue
        return $alertDate <= date('Y-m-d');
    }

    // ----------------------------------------------------- アプリケーション指定による判断メソッド

    /**
     * 指定のアカウントテーブルから、指定ログインIDに一致するアカウントのIDを取得する
     *
     * @access protected
     * @param string $loginId ログインID
     * @param string $appName アプリケーション名
     * @param string $idName アカウントテーブルのIDカラム名
     * @param null | string $loginIdName アカウントテーブルのログインIDカラム名。省略時は'LoginId'
     * @return int
     */
    protected function getIdByLoginId($loginId, $appName, $idName, $loginIdName = 'LoginId') {
        // アプリケーション名毎分岐
        if ($appName == self::APPNAME_CBADMIN) {
            $sql = " SELECT * FROM T_Operator WHERE OpId = :CheckId ";
        }
        else if ($appName == self::APPNAME_MEMBER ||
                 $appName == self::APPNAME_OEMMEMBER) {
            $sql = " SELECT * FROM T_Enterprise WHERE EnterpriseId = :CheckId ";
        }
        else if ($appName == self::APPNAME_OEMADMIN) {
            $sql = " SELECT * FROM T_OemOperator WHERE OemOpId = :CheckId ";
        }

        $row = $this->_adapter->query($sql)->execute(array(':CheckId' => $loginId))->current();
        if(!$row) throw new \Exception('login-id not found');
        return (int)$row[$idName];
    }

    /**
     * 指定アプリケーションにおける指定アカウントのパスワード期限切れ日を取得する
     *
     * @param string $appName アプリケーション名
     * @param int $loginId アカウントのログインID
     * @return string 期限切れ日。有効期限制約無効時や、パスワード最終更新日未設定時は
     *                nullを返す
     */
    public function getExpireDate($appName, $loginId) {
        $appName = nvl($appName);
        if(!$this->isValidAppName($appName)) {
            throw new \Exception('invalid application-name specified');
        }

        switch($appName) {
            case self::APPNAME_CBADMIN:
                $id = $this->getIdByLoginId($loginId, $appName, 'OpId');
                return $this->getOperatorExpireDate($id);
            case self::APPNAME_MEMBER:
            case self::APPNAME_OEMMEMBER:
                $id = $this->getIdByLoginId($loginId, $appName, 'EnterpriseId');
                return $this->getEnterpriseExpireDate($id);
            case self::APPNAME_OEMADMIN:
                $id = $this->getIdByLoginId($loginId, $appName, 'OemOpId');
                return $this->getOemOperatorExireDate($id);
        }
    }

    /**
     * 指定アプリケーションにおける指定アカウントのパスワードが現在日において期限切れとなっているかを判断する
     *
     * @param string $appName アプリケーション名
     * @param string $loginId アカウントのログインID
     * @return boolean
     */
    public function passwordIsExpired($appName, $loginId) {
        $appName = nvl($appName);
        if(!$this->isValidAppName($appName)) {
            throw new \Exception('invalid application-name specified');
        }

        switch($appName) {
            case self::APPNAME_CBADMIN:
                $id = $this->getIdByLoginId($loginId, $appName, 'OpId');
                return $this->operatorPasswordIsExpired($id);
            case self::APPNAME_MEMBER:
            case self::APPNAME_OEMMEMBER:
                $id = $this->getIdByLoginId($loginId, $appName, 'EnterpriseId');
                return $this->enterprisePasswordIsExpired($id);
            case self::APPNAME_OEMADMIN:
                $id = $this->getIdByLoginId($loginId, $appName, 'OemOpId');
                return $this->oemOperatorPasswordIsExpired($id);
        }
    }

    /**
     * 指定アプリケーションにおける指定アカウントに対してパスワード期限切れ間近のアラートを
     * 表示する必要があるかを判断する
     *
     * @param string $appName アプリケーション名
     * @param string $loginId アカウントのログインID
     * @return boolean
     */
    public function needAlertForAccount($appName, $loginId) {
        $appName = nvl($appName);
        if(!$this->isValidAppName($appName)) {
            throw new \Exception('invalid application-name specified');
        }

        switch($appName) {
            case self::APPNAME_CBADMIN:
                $id = $this->getIdByLoginId($loginId, $appName, 'OpId');
                return $this->needAlertForOperator($id);
            case self::APPNAME_MEMBER:
            case self::APPNAME_OEMMEMBER:
                $id = $this->getIdByLoginId($loginId, $appName, 'EnterpriseId');
                return $this->needAlertForEnterprise($id);
            case self::APPNAME_OEMADMIN:
                $id = $this->getIdByLoginId($loginId, $appName, 'OemOpId');
                return $this->needAlertForOemOperator($id);
        }
    }
}
