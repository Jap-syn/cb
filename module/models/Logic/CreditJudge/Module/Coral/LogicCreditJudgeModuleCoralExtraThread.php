<?php
namespace models\Logic\CreditJudge\Module\Coral;

use Zend\Config\Reader\Ini;
use Zend\Db\Adapter\Adapter;
use Zend\Log\Logger;
use Coral\Base\BaseLog;
use Coral\Base\BaseDelegate;
use models\Logic\CreditJudge\LogicCreditJudgeAbstract;
use models\Logic\CreditJudge\LogicCreditJudgeLogCache;

/**
 * 後払い.com独自の基準による追加与信モジュール
 * 過去の取引実績によるスコアリングを行う
 */
class LogicCreditJudgeModuleCoralExtraThread {

    // コールバック種別定数：CoralExtraモジュールの処理が開始された
    const CALLBACK_BEGIN_CORALEXTRA = 'beginCoralExtra';

    // コールバック種別定数：CoralExtraモジュールの処理が終了した
    const CALLBACK_END_CORALEXTRA = 'endCoralExtra';

    /**
     * DBアダプタ
     *
     * @access protected
     * @var Adapter
     */
    protected $_adapter = null;

    /**
     * アプリケーション設定
     *
     * @access protected
     * @var array (Zend\Config\Reader\Ini)
     */
    protected $_config;

    /**
     * Coral追加与信モジュール
     *
     * @access protected
     * @var LogicCreditJudgeModuleCoralExtra
     */
    protected $_mod_extra;

    /**
     * ロガーインスタンス
     *
     * @access protected
     * @var BaseLog
     */
    protected $_logger;

    /**
     * ログの内部キャッシュ
     * @access protected
     * @var LogicCreditJudgeLogCache
     */
    protected $_logCache;

    /**
     * 各種処理状況を通知するコールバックを管理する配列
     *
     * @access protected
     * @var array
     */
    protected $_callbacks;

    /**
     * 注文Seq
     *
     * @access protected
     * @var int
     */
    protected $_oseq;

    /**
     * 与信判定基準ID
     *
     * @access protected
     * @var int
     */
    protected $_creditCriterionId;

    /**
     * 自動化期間判定結果
     *
     * @var bool
     */
    protected $_autoFlg;

    /**
     * スコアリング結果
     *
     * @var array
     */
    protected $_scoreResult;

    /**
     * 与信判定結果
     *
     * @var int
     */
    protected $_judgeResult;

    /**
     * DBアダプタを取得する
     *
     * @return Adapter
     */
    public function getAdapter() {
        return $this->_adapter;
    }
    /**
     * DBアダプタを設定する
     *
     * @param Adapter $adapter DBアダプタ
     * @return LogicCreditJudgeModuleCoralExtraThread このインスタンス
     */
    public function setAdapter(Adapter $adapter) {
        $this->_adapter = $adapter;
        return $this;
    }

    /**
     * ロードされているアプリケーション設定を取得する
     *
     * @return array (Zend\Config\Reader\Ini) ロードされているアプリケーション設定
     */
    public function getConfig() {
        if($this->_config == null) return null;

        return $this->_config;
    }
    /**
     * 指定の設定ファイルをロードする
     *
     * @param string $config 設定INIファイルの読み込み情報
     * @return LogicCreditJudgeModuleCoralExtraThread このインスタンス
     */
    public function loadConfig($config) {
        $this->_config = $config;
        return $this;
    }
    /**
     * 与信処理向け設定を取得する
     *
     * @return array
     */
    public function getJudgeConfig() {
        return $this->getConfig()['cj_api'];
    }

    /**
     * このインスタンスで使用するロガーを取得する
     *
     * @return BaseLog
     */
    public function getLogger() {
        return $this->_logger;
    }
    /**
     * このインスタンスで使用するロガーを設定する
     *
     * @param BaseLog $logger
     * @return LogicCreditJudgeModuleCoralExtraThread
     */
    public function setLogger(BaseLog $logger = null) {
        $this->_logger = $logger;
        return $this;
    }

    /**
     * ログの内部キャッシュを取得する
     *
     * @return LogicCreditJudgeLogCache
     */
    public function getLogCache() {
        return $this->_logCache;
    }
    /**
     * ログの内部キャッシュを設定する
     *
     * @return LogicCreditJudgeLogCache
     */
    public function setLogCache($logCache) {
        $this->_logCache = $logCache;
    }
    /**
     * ログキャッシュをクリアする
     *
     * @return LogicCreditJudgeModuleCoralExtraThread
     */
    public function clearLogCache() {
        if($this->_logCache == null) $this->_logCache = new LogicCreditJudgeLogCache();
        $this->_logCache->clearCache();
        return $this;
    }
    /**
     * キャッシュ済みログを取得する
     *
     * @return array
     */
    public function getCachedLog() {
        if($this->_logCache == null) return array();
        return $this->_logCache->getCache();
    }

    /**
     * Coral追加与信モジュールを取得する
     *
     * @return LogicCreditJudgeModuleCoralExtra
     */
    public function getModuleExtra() {
        return $this->_mod_extra;
    }
    /**
     * Coral追加与信モジュールを初期化する
     *
     * @access protected
     * @param Adapter $adapter アダプタ
     */
    protected function initModuleCoralExtra(Adapter $adapter) {
        $config = $this->getConfig(true);
        $entIds = array();
        if(isset($config['credit_judge']) && isset($config['credit_judge']['enterpriseid'])) {
            $entIds = explode(',', $config['credit_judge']['enterpriseid']);
        }

        $this->_mod_extra = new LogicCreditJudgeModuleCoralExtra($this->getAdapter(), $this->getJudgeConfig());
        $this->_mod_extra->setEnterpriseIds($entIds);
        return $this;

        return $this;
    }

    /**
     * 注文Seqを取得する
     *
     * @return int
     */
    public function getOseq() {
        return $this->_oseq;
    }
    /**
     * 注文Seqを設定する
     *
     * @param int $oseq 注文Seq
     * @return LogicCreditJudgeModuleCoralExtraThread このインスタンス
     */
    public function setOseq($oseq) {
        $this->_oseq = $oseq;
        return $this;
    }

    /**
     * 与信判定基準IDを取得する
     *
     * @return number
     */
    public function getCreditCriterionId() {
        return $this->_creditCriterionId;
    }
    /**
     * 与信判定基準IDを設定する
     *
     * @param int $creditCriterionId 与信判定基準ID
     * @return LogicCreditJudgeModuleCoralExtraThread このインスタンス
     */
    public function setCreditCriterionId($creditCriterionId) {
        $this->_creditCriterionId = $creditCriterionId;
        return $this;
    }

    /**
     * 自動化期間判定結果を取得する
     *
     * @return bool
     */
    public function getAutoFlg() {
        return $this->_autoFlg;
    }
    /**
     * 自動化期間判定結果を設定する
     *
     * @param bool $autoFlg 自動化期間判定結果
     * @return LogicCreditJudgeModuleCoralExtraThread このインスタンス
     */
    public function setAutoFlg($autoFlg) {
        $this->_autoFlg = $autoFlg;
        return $this;
    }

    /**
     * スコアリング結果を取得する
     *
     * @return array;
     */
    public function getScoreResult() {
        return $this->_scoreResult;
    }

    /**
     * 与信判定結果を取得する
     *
     * @return int
     */
    public function getJudgeResult() {
        return $this->_judgeResult;
    }

    /**
     * LogicCreditJudgeModuleCoralExtraThreadの新しいインスタンスを初期化する
     *
     * @param $adapter アダプター
     * @param $configPath コンフィグ情報
     * @param LogicCreditJudgeLogCache $logCache ログキャッシュ
     * @param int $oseq 注文Seq
     * @param int $creditCriterionId 与信判定基準ID

     */
    public function __construct($adapter, $config, LogicCreditJudgeLogCache $logCache, $oseq, $creditCriterionId, $autoFlg) {

        $this->setLogCache($logCache);

        if (is_null($adapter)) {
            $adapter = new Adapter($config['database']);
        }

        $this
            // ロギング初期設定
            ->setLogger(LogicCreditJudgeAbstract::getDefaultLogger())

            // DBアダプタ初期化
            ->setAdapter($adapter)

            // 設定のロード
            ->loadConfig($config)

            // Coral追加与信モジュール初期化
            ->initModuleCoralExtra($adapter)

            // 注文Seq初期化
            ->setOseq($oseq)

            // 与信判定基準ID初期化
            ->setCreditCriterionId($creditCriterionId)

            // 自動化期間判定結果初期化
            ->setAutoFlg($autoFlg)
        ;
    }

    /**
     * 指定の注文のスコアリングを行う
     */
    public function run () {
        $start = microtime(true);
        $this->debug(sprintf('[%s] coralextra start', $this->getOseq()));

        // モジュールの初期化
        $module_extra = $this->getModuleExtra();

        // 与信判定基準ID設定
        $module_extra->setCreditCriterionId($this->getCreditCriterionId());

        // 自動化期間判定結果設定
        $module_extra->setAutoFlg($this->getAutoFlg());

        // CoralExtra開始コールバックを実行
        $callback_result = false;
        try {
            $callback_result = $this->execCallback(self::CALLBACK_BEGIN_CORALEXTRA, array($this->getOseq()));
        } catch(\Exception $callbackError) {}
        // コールバックがfalseを返さなかったら追加与信を実行
        if($callback_result !== false) {
            // 追加与信判定
            $result = $module_extra->judge($this->getOseq());
        } else {
            $this->debug(sprintf('[%s] judgeMain.CoralExtra skipped by callback.', $this->getOseq()));
            $result = LogicCreditJudgeAbstract::JUDGE_RESULT_CONTINUE;
        }

        // CoralExtra終了コールバックを実行
        try {
            $this->execCallback(self::CALLBACK_END_CORALEXTRA, array($this->getOseq()));
        } catch(\Exception $callbackError) {}

        // スコアリング結果を保存
        $this->_scoreResult = $module_extra->getResultArray();

        // 与信判定結果を保存
        $this->_judgeResult = $result;

        $this->info(sprintf('[%s] coralextra completed. elapsed time = %s', $this->getOseq(), (microtime(true) - $start)));
    }

    /**
     * 指定の優先度でログメッセージを出力する
     *
     * @param string $message ログメッセージ
     * @param int $priority 優先度
     */
    public function log($priority, $message) {
        $logger = $this->getLogger();
        $message = sprintf('[%s] %s', get_class($this), $message);
        if($logger) {
            $logger->log($priority, $message);
        }
        $map = array(
                'EMERG', 'ALERT', 'CRIT', 'ERR',
                'WARN', 'NOTICE', 'INFO', 'DEBUG'
        );
        $fixed_message = sprintf('%s %s (%s) %s',
        date('Y-m-d H:i:s'),
        $map[$priority],
        $priority,
        $message);
        try {
            if($this->_logCache !== null) {
                $this->_logCache->append($fixed_message);
            }
        } catch(\Exception $err) {
        }
    }

    /**
     * DEBUGレベルでログメッセージを出力する
     *
     * @param string $message ログメッセージ
     */
    public function debug($message) {
        $this->log(Logger::DEBUG, $message);
    }

    /**
     * INFOレベルでログメッセージを出力する
     *
     * @param string $message ログメッセージ
     */
    public function info($message) {
        $this->log(Logger::INFO, $message);
    }

    /**
     * NOTICEレベルでログメッセージを出力する
     *
     * @param string $message ログメッセージ
     */
    public function notice($message) {
        $this->log(Logger::NOTICE, $message);
    }

    /**
     * WARNレベルでログメッセージを出力する
     *
     * @param string $message ログメッセージ
     */
    public function warn($message) {
        $this->log(Logger::WARN, $message);
    }

    /**
     * ERRレベルでログメッセージを出力する
     *
     * @param string $message ログメッセージ
     */
    public function err($message) {
        $this->log(Logger::ERR, $message);
    }

    /**
     * CRITレベルでログメッセージを出力する
     *
     * @param string $message ログメッセージ
     */
    public function crit($message) {
        $this->log(Logger::CRIT, $message);
    }

    /**
     * ALERTレベルでログメッセージを出力する
     *
     * @param string $message ログメッセージ
     */
    public function alert($message) {
        $this->log(Logger::ALERT, $message);
    }

    /**
     * EMERGレベルでログメッセージを出力する
     *
     * @param string $message ログメッセージ
     */
    public function emerg($message) {
        $this->log(Logger::EMERG, $message);
    }

    /**
     * 指定種別のコールバックを取得する
     *
     * @param string $type コールバック種別。このクラスのCALLBACK_で定義される定数値を指定する
     * @return BaseDelegate | null 指定種別に関連付けられているコールバック
     */
    public function getCallback($type) {
        if(!$type) return null;
        if(isset($this->_callbacks[$type]) && ($this->_callbacks[$type] instanceof BaseDelegate)) {
            return $this->_callbacks[$type];
        }
        return null;
    }

    /**
     * 指定種別のコールバックを設定する
     *
     * @param string $type コールバック種別。このクラスのCALLBACK_で定義される定数値を指定する
     * @param BaseDelegate | null コールバックデリゲート
     * @return LogicCreditJudgeSequencer
     */
    public function setCallback($type, BaseDelegate $callback = null) {
        if($type !== null) {
            $this->_callbacks[$type] = $callback;
        }
        return $this;
    }

    /**
     * 指定種別のコールバックを実行する
     *
     * @param string $type コールバック種別。このクラスのCALLBACK_で定義される定数値を指定する
     * @param null | array $args コールバック呼出しに使用するパラメータ配列
     * @return mixed コールバックの実行結果
     */
    public function execCallback($type, array $args = array()) {
        /** @var BaseDelegate */
        $callback = $this->getCallback($type);
        $callback_result = null;
        if($callback) {
            $this->debug(sprintf('execCallback called. type = %s, args = %s', $type, json_encode($args)));
            $this->debug(sprintf('execCallback callback = %s', $callback));
            $params = array_merge(array($this), $args);
            try {
                $callback_result = $callback->invokeByArray($params);
            } catch(\Exception $err) {
                $this->info('execCallback CALLBACK ERROR !!!! error = %s', $err->getMessage());
                throw $err;
            }
            $this->debug(sprintf('execCallback result = %s', $callback_result));
        }
        return $callback_result;
    }
}