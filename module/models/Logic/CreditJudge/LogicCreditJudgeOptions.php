<?php
namespace models\Logic\CreditJudge;
/**
 * 与信関連モジュール向けのオプション設定を管理するクラス
 */
class LogicCreditJudgeOptions {
    // オプションキー定数：受信ファイル保存ディレクトリを指定するキー
    const SAVE_DIR = 'save_dir';

    // オプションキー定数：与信時平均単価倍率初期値を指定するキー
    const DEFAULT_AVG_UNIT_PRICE_RATE = 'default_average_unit_price_rate';

    // オプションキー定数：通信タイムアウト設定を指定するキー
    const TIMEOUT_TIME = 'timeout_time';

    // オプションキー定数：デバッグモードを指定するキー
    const DEBUG_MODE = 'debug_mode';

    // オプションキー定数：デバッグデータ格納ディレクトリを指定するキー
    const DEBUG_DATA_DIR = 'debug_data_dir';

    // オプションサブキー定数：URL関連設定を指定するサブキー
    const SUBKEY_URL = 'url';

    // オプションサブキー定数：デバッグファイル関連設定を指定するサブキー
    const SUBKEY_DEBUGFILE = 'debug_file';

    // 種別指定キー定数：ILU審査システムを指定する種別キー
    const KEY_ILU = 'ilu';

    // 種別指定キー定数：ILU審査システムのパターンマスターを指定する種別キー
    const KEY_ILU_PAT_MASTER = 'ilu_pattern_master';

    // 種別指定キー定数：ジンテックAPIを指定する種別キー
    const KEY_JINTEC = 'jintec';

	// オプションキー定数：ジンテックスCid
    const JINTEC_CID = 'jintec_cid';

	// オプションキー定数：ジンテックスID
    const JINTEC_ID  = 'jintec_id';

	// オプションキー定数：ジンテックスPassword
    const JINTEC_PASSWORD = 'jintec_pass';

    //注文利用額対応フラグ
    const USER_AMOUNT_OVER = 'user_amount_over';

    // オプションキー定数：ILU審査システム システムID
    const ILU_ID = 'ilu_id';

    /**
     * オプション設定の連想配列
     * @access protected
     * @var array
     */
    protected $_options = array();

    /**
     * オプション設定の連想配列を指定して、LogicCreditJudgeOptionsの
     * 新しいインスタンスを初期化する。
     * 派生クラスでは必ずこのコンストラクタを呼び出すように実装する必要がある
     *
     * @access protected
     * @param array $options オプション設定
     */
    public function __construct(array $options = array()) {
        $this->setOptions($options);
    }

    /**
     * URL設定およびデバッグファイル設定で利用可能な種別キーすべてを配列で取得する
     *
     * @return array
     */
    public function getValidTypeKeys() {
        return array(
            self::KEY_ILU,
            self::KEY_ILU_PAT_MASTER,
            self::KEY_JINTEC
        );
    }

    /**
     * 指定パラメータが、URL設定やデバッグファイル設定で指定可能な
     * キーであるかを検査する
     *
     * @param string $type 種別キー
     * @param null | boolean $strictCheck trueを指定した場合、種別キーが無効な場合に例外をスローする。省略時はfalse
     * @return boolean $typeが有効ならtrue、それ以外はfalse
     */
    public function checkTypeKey($type, $strictCheck = false) {
        $valid_keys = $this->getValidTypeKeys();
        if(!in_array($type, $valid_keys)) {
            if($strictCheck) {
                throw new \Exception('invalid type specified');
            } else {
                return false;
            }
        }
        return true;
    }

    /**
     * 現在のオプション設定を取得する
     *
     * @return array
     */
    public function getOptions() {
        return $this->_options;
    }
    /**
     * オプション設定を上書き設定する
     *
     * @param array $optinos オプション設定
     * @return LogicCreditJudgeOptions このインスタンス
     */
    public function setOptions(array $options = array()) {
        $this->_options = array();
        return
            $this
                // URLサブキーの内容をクリア
                ->setUrlOptions()
                // デバッグファイルサブキーの内容をクリア
                ->setDebugFileOptions()
                // 初期化済み配列にマージ
                ->mergeOptions($options);
    }
    /**
     * オプション設定を現在の設定にマージする
     *
     * @param array $options オプション設定
     * @return LogicCreditJudgeOptions このインスタンス
     */
    public function mergeOptions(array $options = array()) {
        $subKeyUrl = self::SUBKEY_URL;
        $subKeyDF = self::SUBKEY_DEBUGFILE;

        foreach($options as $key => $value) {
            switch($key) {
                case $subKeyUrl:    // URLサブキーは個別処理
                    $this->mergeUrlOptions($value);
                    break;
                case $subKeyDF:     // デバッグファイルサブキーは個別処理
                    $this->mergeDebugFileOptions($value);
                    break;
                default:            // その他はそのまま格納
                    $this->_options[$key] = $value;
                    break;
            }
        }
        return $this;
    }

    /**
     * URL関連のオプション設定を取得する。
     *
     * @return array URL関連のオプション設定
     */
    public function getUrlOptions() {
        $subKey = self::SUBKEY_URL;

        return $this->_options[$subKey];
    }
    /**
     * URL関連のオプション設定を上書きする
     *
     * @param array $options URL関連のオプション設定
     * @return LogicCreditJudgeOptions このインスタンス
     */
    public function setUrlOptions(array $options = array()) {
        $subKey = self::SUBKEY_URL;

        $this->_options[$subKey] = array();
        return $this->mergeUrlOptions($options);
    }
    /**
     * URL関連のオプション設定を現在の設定にマージする
     *
     * @param array $options URL関連のオプション設定
     * @return LogicCreditJudgeOptions このインスタンス
     */
    public function mergeUrlOptions(array $options = array()) {
        $subKey = self::SUBKEY_URL;

        if(!is_array($this->_options[$subKey])) $this->_options[$subKey] = array();
        $this->_options[$subKey] = array_merge($this->_options[$subKey], $options);
        return $this;
    }

    /**
     * デバッグファイル関連のオプション設定を取得する
     *
     * @return array デバッグファイル関連のオプション設定
     */
    public function getDebugFileOptions() {
        $subKey = self::SUBKEY_DEBUGFILE;

        return $this->_options[$subKey];
    }
    /**
     * デバッグファイル関連のオプション設定を上書きする
     *
     * @param array $options デバッグファイル関連のオプション設定
     * @return LogicCreditJudgeOptions このインスタンス
     */
    public function setDebugFileOptions(array $options = array()) {
        $subKey = self::SUBKEY_DEBUGFILE;

        $this->_options[$subKey] = array();
        return $this->mergeDebugFileOptions($options);
    }
    /**
     * デバッグファイル関連のオプション設定を現在の設定にマージする
     *
     * @param array $options デバッグファイル関連のオプション設定
     * @return LogicCreditJudgeOptions このインスタンス
     */
    public function mergeDebugFileOptions(array $options = array()) {
        $subKey = self::SUBKEY_DEBUGFILE;

        if(!is_array($this->_options[$subKey])) $this->_options[$subKey] = array();
        $this->_options[$subKey] = array_merge($this->_options[$subKey], $options);
        return $this;
    }

    /**
     * 受信ファイル保存ディレクトリを取得する
     *
     * @return string
     */
    public function getSaveDir() {
        return $this->_options[self::SAVE_DIR];
    }
    /**
     * 受信ファイル保存ディレクトリを設定する
     *
     * @param string $dir 受信ファイル保存ディレクトリ
     * @return LogicCreditJudgeOptions このインスタンス
     */
    public function setSaveDir($dir) {
        $this->_options[self::SAVE_DIR] = $dir;
        return $this;
    }

    /**
     * 与信時平均単価倍率設定初期値を取得する
     *
     * @return float
     */
    public function getDefaultAverageUnitPriceRate() {
        return $this->_options[self::DEFAULT_AVG_UNIT_PRICE_RATE];
    }
    /**
     * 与信時平均単価倍率設定初期値を設定する
     *
     * @param float $rate
     * @return LogicCreditJudgeOptions このインスタンス
     */
    public function setDefaultAverageUnitPriceRate($rate) {
        $rate = (float)$rate;
        $this->_options[self::DEFAULT_AVG_UNIT_PRICE_RATE] = $rate;
        return $this;
    }

    /**
     * 通信タイムアウト時間を取得する
     *
     * @return int
     */
    public function getTimeoutTime() {
        return $this->_options[self::TIMEOUT_TIME];
    }
    /**
     * 通信タイムアウト時間を設定する
     *
     * @param int $time
     * @return LogicCreditJudgeOptions このインスタンス
     */
    public function setTimeoutTime($time) {
        $time = (int)$time;
        $this->_options[self::TIMEOUT_TIME] = $time;
        return $this;
    }

    /**
     * デバッグモードを設定する
     *
     * @return boolean デバッグモードの場合はtrue、それ以外はfalse
     */
    public function getDebugMode() {
        return $this->_options[self::DEBUG_MODE] ? true : false;
    }
    /**
     * デバッグモードを設定する
     *
     * @param boolean $mode デバッグモード
     * @return LogicCreditJudgeOptions このインスタンス
     */
    public function setDebugMode($mode) {
        $this->_options[self::DEBUG_MODE] = $mode ? true : false;
        return $this;
    }

    /**
     * デバッグ時のローカルデータファイルが格納されているディレクトリパスを取得する
     *
     * @return string デバッグ時に使用されるローカルデータファイルが格納されているディレクトリパス
     */
    public function getDebugDataDir() {
        return $this->_options[self::DEBUG_DATA_DIR];
    }
    /**
     * デバッグ時のローカルデータファイルが格納されているディレクトリパスを設定する
     *
     * @param string $dir デバッグ時に使用されるローカルデータファイルが格納されているディレクトリパス
     * @return LogicCreditJudgeOptions このインスタンス
     */
    public function setDebugDataDir($dir) {
        $this->_options[self::DEBUG_DATA_DIR] = $dir;
        return $this;
    }

    /**
     * 指定種別の外部接続用URLを取得する。
     *
     * @param string $type 種別。このクラスで定義されるKEY_*定数に一致する必要がある
     * @return string
     */
    public function getUrl($type) {
        $this->checkTypeKey($type, true);
        $subKey = self::SUBKEY_URL;
        return $this->_options[$subKey][$type];
    }
    /**
     * 指定種別の外部接続用URLを設定する
     *
     * @param string $type 種別。このクラスで定義されるKEY_*定数に一致する必要がある
     * @param string $url
     * @return LogicCreditJudgeOptions このインスタンス
     */
    public function setUrl($type, $url) {
        $this->checkTypeKey($type, true);
        $subKey = self::SUBKEY_URL;
        $this->_options[$subKey][$type] = $url;
        return $this;
    }

    /**
     * ILU審査システムの接続URLを取得する
     *
     * @return string
     */
    public function getIluUrl() {
        $key = self::KEY_ILU;
        return $this->getUrl($key);
    }
    /**
     * ILU審査システムの接続URLを設定する
     *
     * @param string $url
     * @return LogicCreditJudgeOptions このインスタンス
     */
    public function setIluUrl($url) {
        $key = self::KEY_ILU;
        return $this->setUrl($key, $url);
    }

    /**
     * ILU審査システムのパターンマスターデータ取得URLを取得する
     *
     * @return string
     */
    public function getIluPatternMasterUrl() {
        $key = self::KEY_ILU_PAT_MASTER;
        return $this->getUrl($key);
    }
    /**
     * ILU審査システムのパターンマスターデータ取得URLを設定する
     *
     * @param string $url
     * @return LogicCreditJudgeOptions このインスタンス
     */
    public function setIluPatternMasterUrl($url) {
        $key = self::KEY_ILU_PAT_MASTER;
        return $this->setUrl($key, $url);
    }

    /**
     * ジンテックAPIの接続URLを取得する
     *
     * @return string
     */
    public function getJintecUrl() {
        $key = self::KEY_JINTEC;
        return $this->getUrl($key);
    }
    /**
     * ジンテックAPIの接続URLを設定する
     *
     * @param string $url
     * @return LogicCreditJudgeOptions このインスタンス
     */
    public function setJintecUrl($url) {
        $key = self::KEY_JINTEC;
        return $this->setUrl($key, $url);
    }

    /**
     * 指定種別のデバッグファイル名を取得する
     *
     * @param string $type 種別。このクラスで定義されるKEY_*定数に一致する必要がある
     * @return string
     */
    public function getDebugFileName($type) {
        $this->checkTypeKey($type, true);
        $subKey = self::SUBKEY_DEBUGFILE;
        return $this->_options[$subKey][$type];
    }
    /**
     * 指定種別のデバッグファイル名を設定する
     *
     * @param string $type 種別。このクラスで定義されるKEY_*定数に一致する必要がある
     * @param string $fileName ファイル名
     * @return LogicCreditJudgeOptions このインスタンス
     */
    public function setDebugFileName($type, $fileName) {
        $this->checkTypeKey($type, true);
        $subKey = self::SUBKEY_DEBUGFILE;
        $this->_options[$subKey][$type] = $fileName;
    }

    /**
     * ILU審査システム用のデバッグファイル名を取得する
     *
     * @return string
     */
    public function getIluDebugFileName() {
        $key = self::KEY_ILU;
        return $this->getDebugFileName($key);
    }
    /**
     * ILU審査システム用のデバッグファイル名を設定する
     *
     * @param string $fileName ファイル名
     * @return LogicCreditJudgeOptions このインスタンス
     */
    public function setIluDebugFileName($fileName) {
        $key = self::KEY_ILU;
        return $this->setDebugFileName($key, $fileName);
    }

    /**
     * ILU審査システムのパターンマスター用デバッグファイル名を取得する
     *
     * @return string
     */
    public function getIluPatternMasterDebugFileName() {
        $key = self::KEY_ILU_PAT_MASTER;
        return $this->getDebugFileName($key);
    }
    /**
     * ILU審査システムのパターンマスター用デバッグファイル名を設定する
     *
     * @param string $fileName ファイル名
     * @return LogicCreditJudgeOptions このインスタンス
     */
    public function setIluPatternmasterDebugFileName($fileName) {
        $key = self::KEY_ILU_PAT_MASTER;
        return $this->setDebugFileName($key, $fileName);
    }

    /**
     * ジンテック用デバッグファイル名を取得する
     *
     * @return string
     */
    public function getJintecDebugFileName() {
        $key = self::KEY_JINTEC;
        return $this->getDebugFileName($key);
    }
    /**
     * ジンテック用デバッグファイル名を設定する
     *
     * @param string $fileName ファイル名
     * @return LogicCreditJudgeOptions このインスタンス
     */
    public function setJintecDebugFileName($fileName) {
        $key = self::KEY_JINTEC;
        return $this->setDebugFileName($key, $fileName);
    }

    /**
     * ジンテックのCid取得
     *
     * @return string
     */
    public function getJintecCid() {
        return $this->_options[self::JINTEC_CID];
    }
    /**
     * ジンテックのCidを設定する
     *
     * @param string $cid ジンテックCID
     * @return LogicCreditJudgeOptions このインスタンス
     */
    public function setJintecCid($cid) {
        $this->_options[self::JINTEC_CID] = $dir;
        return $this;
    }

	/**
     * ジンテックのID取得
     *
     * @return string
     */
    public function getJintecId() {
        return $this->_options[self::JINTEC_ID];
    }
    /**
     * ジンテックのIDを設定する
     *
     * @param string $id  ジンテックID
     * @return LogicCreditJudgeOptions このインスタンス
     */
    public function setJintecId($id) {
        $this->_options[self::JINTEC_ID] = id;
        return $this;
    }

	/**
     * ジンテックのパスワード取得
     *
     * @return string
     */
    public function getJintecPassword() {
        return $this->_options[self::JINTEC_PASSWORD];
    }
    /**
     * ジンテックのパスワードを設定する
     *
     * @param string $pass パスワード
     * @return LogicCreditJudgeOptions このインスタンス
     */
    public function setJintecPassword($password) {
        $this->_options[self::JINTEC_PASSWORD] = $password;
        return $this;
    }
    /**
     * 注文利用額取得
     *
     * @return string
     */
    public function getUserAmountOver() {
        return $this->_options[self::USER_AMOUNT_OVER];
    }

    /**
     * ILU審査システムのシステムID取得
     *
     * @return string
     */
    public function getIluId() {
        return $this->_options[self::ILU_ID];
    }
    /**
     * ILU審査システムのシステムIDを設定する
     *
     * @param string $url
     * @return LogicCreditJudgeOptions このインスタンス
     */
    public function setIluId($id) {
        $this->_options[self::ILU_ID] = $dir;
        return $this;
    }
}
