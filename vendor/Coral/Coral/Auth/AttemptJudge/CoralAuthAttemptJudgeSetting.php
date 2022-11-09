<?php
namespace Coral\Coral\Auth\AttemptJudge;

use Zend\Db\Adapter\Adapter;
use models\Table\TableSystemProperty;

/**
 * CoralAuthAttemptJudgeの各ロックレベルに関する設定オブジェクト。
 * このクラスは単に設定値を保持するのみで、設定内容によって動作に変化があるわけでは
 * ないので、このクラスを利用するコード側で適宜処理分岐を行う必要がある。
 *
 * デフォルトコンストラクタによる初期値は以下の通り
 * - 有効／無効： 有効
 * - 判定に使用される時間間隔： 300秒 ＝ 5分
 * - ロックアウトまでの試行上限回数： 10回
 * - ロックアウト継続時間： 30分
 * - ハッシュにUAを含めるか否か： 含める
 */
class CoralAuthAttemptJudgeSetting {
    /**
     * システムプロパティの設定値に基づいて初期化されたクライアントレベルロックアウト向けの
     * CoralAuthAttemptJudgeSettingのインスタンスを生成する
     *
     * @static
     * @param Adapter $adapter アダプタ
     * @return CoralAuthAttemptJudgeSetting
     */
    public static function createClientLevelSetting(Adapter $adapter) {
        return self::_initDefaultFromSystemProperty($adapter);
    }

    /**
     * システムプロパティの設定値に基づいて初期化されたIDレベルロックアウト向けの
     * CoralAuthAttemptJudgeSettingのインスタンスを生成する
     *
     * @static
     * @param Adapter $adapter アダプタ
     * @return CoralAuthAttemptJudgeSetting
     */
    public static function createIdLevelSetting(Adapter $adapter) {
        return self::_initDefaultFromSystemProperty($adapter, true);
    }

    /**
     * システムプロパティの設定値に基づいて初期化されたクライアントレベルまたはIDレベルロックアウト向けの
     * CoralAuthAttemptJudgeSettingインスタンスを生成する
     *
     * @static
     * @access protected
     * @param Adapter $adapter アダプタ
     * @param boolean | null $idLevel IDレベルで初期化する場合はtrue、クライアントレベルの場合はfalseを指定する。
     *                                省略時はfalse（＝クライアントレベル）
     * @return CoralAuthAttemptJudgeSetting
     */
    protected static function _initDefaultFromSystemProperty(Adapter $adapter, $idLevel = false) {

        // 対応するカテゴリ名を確定させる
        $category = $idLevel ?
            TableSystemProperty::FIX_CATEGORY_ATM_JUDGE_ID :
            TableSystemProperty::FIX_CATEGORY_ATM_JUDGE_CLIENT;

        $sysProps = new TableSystemProperty($adapter);

        /** @var CoralAuthAttemptJudgeSetting */
        $result = new self();

        // システムプロパティで使用するプロパティ名
        $KEY_ENABLED = 'enabled';
        $KEY_INTERVAL_SECONDS = 'judge-interval-seconds';
        $KEY_LIMIT_COUNT = 'limit-for-lockout';
        $KEY_LOCKOUT_MINUTES = 'lockout-minutes';
        $KEY_INCLUDE_UA = 'include-ua-in-client-hash';

        // プロパティ名をキーに、DB未設定時の初期値と値を反映させるためのメソッド名を
        // 関連付ける
        $defaults = array(
            $KEY_ENABLED =>
                array('value' => 0,
                      'action' => 'setEnabled'),

            $KEY_INTERVAL_SECONDS =>
                array('value' => $idLevel ? 3600 : 300,
                      'action' => 'setJudgementIntervalTime'),

            $KEY_LIMIT_COUNT =>
                array('value' => $idLevel ? 40 : 5,
                      'action' => 'setLimitForLockout'),

            $KEY_LOCKOUT_MINUTES =>
                array('value' => $idLevel ? 0 : 30,
                      'action' => 'setLockoutTime'),

            $KEY_INCLUDE_UA =>
                array('value' => 1,
                      'action' => 'setUseUaInHash')
        );

        foreach($defaults as $key => $conf) {
            $value = $conf['value'];
            $method = $conf['action'];
            if($sysProps->propNameExists($key, $category)) {
                $value = (int)$sysProps->getValue(TableSystemProperty::DEFAULT_MODULE, $category, $key);
            }
            $result->$method($value);
        }
        return $result;
    }

    /**
     * この設定が有効であるかのフラグ
     *
     * @access protected
     * @var boolean
     */
    protected $_enabled = true;

    /**
     * 判定に使用される時間間隔（秒単位）
     *
     * @access protected
     * @var int
     */
    protected $_interval = 300;

    /**
     * ロックアウトまでの試行回数上限
     *
     * @access protected
     * @var int
     */
    protected $_limit = 10;

    /**
     * ロックアウト継続時間（分単位）
     *
     * @access protected
     * @var int
     */
    protected $_lockoutTime = 30;

    /**
     * HTTP_USER_AGENTをハッシュ生成のシードに含めるかの設定
     *
     * @access protected
     * @var boolean
     */
    protected $_useUaInHash = true;

    /**
     * 設定が有効であるかの値を取得する
     *
     * @return boolean
     */
    public function getEnabled() {
        return $this->_enabled;
    }

    /**
     * 設定の有効／無効を設定する
     *
     * @param boolean $enabled 設定を有効にする場合はtrue、無効にする場合はfalse
     * @return CoralAuthAttemptJudgeSetting このインスタンス
     */
    public function setEnabled($enabled) {
        $this->_enabled = $enabled ? true : false;
        return $this;
    }

    /**
     * 判定に使用される秒単位の時間間隔を取得する
     *
     * @return int
     */
    public function getJudgementIntervalTime() {
        return $this->_interval;
    }
    /**
     * 判定に使用される秒単位の時間間隔を設定する。
     *
     *
     * @param int $seconds 設定する秒単位の時間間隔。
     *                     有効な設定値は1以上で0またはマイナス値を指定した場合
     *                     時間間隔設定は無効になる
     * @return CoralAuthAttemptJudgeSetting このインスタンス
     */
    public function setJudgementIntervalTime($seconds) {
        $seconds = (int)$seconds;
        if($seconds < 0) $seconds = 0;
        $this->_interval = $seconds;
        return $this;
    }
    /**
     * 時間間隔の設定が有効であるかを取得する
     *
     * @return boolean
     */
    public function isIntervalAvailable() {
        return $this->_interval > 0;
    }

    /**
     * ロックアウトまでの試行上限回数を取得する
     *
     * @return int
     */
    public function getLimitForLockout() {
        return $this->_limit;
    }
    /**
     * ロックアウトまでの試行上限回数を設定する
     *
     * @param int $limit 試行上限回数
     * @return CoralAuthAttemptJudgeSetting このインスタンス
     */
    public function setLimitForLockout($limit) {
        $limit = (int)$limit;
        if($limit < 1) $limit = 1;
        $this->_limit = $limit;
        return $this;
    }

    /**
     * ロックアウト継続時間を取得する
     *
     * @return int ロックアウト継続時間（分）
     */
    public function getLockoutTime() {
        return $this->_lockoutTime;
    }
    /**
     * ロックアウト継続時間を設定する
     *
     * @param int $minutes 分単位のロックアウト継続時間。0またはマイナス値を
     *                     指定した場合は0と見なされ、ロックアウト継続時間の制限が無効になる
     *                     （＝手動解除するまでロックアウト状態）
     * @return CoralAuthAttemptJudgeSetting このインスタンス
     */
    public function setLockoutTime($minutes) {
        $minutes = (int)$minutes;
        if($minutes < 0) $minutes = 0;
        $this->_lockoutTime = $minutes;
        return $this;
    }
    /**
     * ロックアウト継続時間設定が有効であるかを判断する
     *
     * @return boolean
     */
    public function isLockoutTimeAvailable() {
        return $this->_lockoutTime > 0;
    }

    /**
     * ハッシュ値生成にHTTP_USER_AGENTを使用するかの設定を取得する
     *
     * @return boolean
     */
    public function getUseUaInHash() {
        return $this->_useUaInHash;
    }
    /**
     * ハッシュ値生成にHTTP_USER_AGENTを使用するかを設定する
     *
     * @param boolean $include HTTP_USER_AGENTを含める場合はtrue、含めない場合はfalse
     * @return CoralAuthAttemptJudgeSetting このインスタンス
     */
    public function setUseUaInHash($include) {
        $this->_useUaInHash = $include ? true : false;
        return $this;
    }
}
