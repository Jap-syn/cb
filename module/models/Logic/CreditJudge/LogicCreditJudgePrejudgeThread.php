<?php
namespace models\Logic\CreditJudge;

use Zend\Config\Reader\Ini;
use Zend\Db\Adapter\Adapter;
use Zend\Log\Logger;
use Coral\Base\BaseLog;
use Coral\Base\BaseDelegate;
use models\Logic\CreditJudge\LogicCreditJudgeAbstract;
use models\Logic\CreditJudge\LogicCreditJudgeLogCache;
use models\Logic\CreditJudge\LogicCreditJudgeSystemConnect;
use models\Logic\CreditJudge\SystemConnect\LogicCreditJudgeSystemConnectException;
use models\Table\TableCjResult;
use models\Table\TableOrder;

/**
 * 与信前処理
 */
class LogicCreditJudgePrejudgeThread {

    // コールバック種別定数：preJudgeメソッドが開始された
    const CALLBACK_BEGIN_PREJUDGE = 'beginPreJudge';

    // コールバック種別定数：preJudgeメソッドが終了した
    const CALLBACK_END_PREJUDGE = 'endPreJudge';

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
     * ユーザーID
     *
     * @access protected
     * @var int
     */
    protected $_userId;

    /**
     * 与信判定基準ID
     *
     * @access protected
     * @var int
     */
    protected $_creditCriterionId;

    /**
     * ILU審査システム連携をバイパスすべきかを判断する
     *
     * @var bool
     */
    protected $_beBypassIlu;

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
     * @return LogicCreditJudgePrejudgeThread このインスタンス
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
     * @return LogicCreditJudgePrejudgeThread このインスタンス
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
     * @return LogicCreditJudgePrejudgeThread
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
     * @return LogicCreditJudgePrejudgeThread
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
     * @return LogicCreditJudgePrejudgeThread このインスタンス
     */
    public function setOseq($oseq) {
        $this->_oseq = $oseq;
        return $this;
    }

    /**
     * ユーザーIDを取得する
     *
     * @return int
     */
    public function getUserId() {
        return $this->_userId;
    }
    /**
     * ユーザーIDを設定する
     *
     * @param int $userId ユーザーID
     * @return LogicCreditJudgePrejudgeThread このインスタンス
     */
    public function setUserId($userId) {
        $this->_userId = $userId;
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
     * @return LogicCreditJudgePrejudgeThread このインスタンス
     */
    public function setCreditCriterionId($creditCriterionId) {
        $this->_creditCriterionId = $creditCriterionId;
        return $this;
    }

    /**
     * ILU審査システム連携をバイパスすべきかの判断を取得する
     *
     * @return LogicCreditJudgePrejudgeThread このインスタンス
     */
    public function getBeBypassIlu() {
        return $this->_beBypassIlu;
    }

    /**
     * ILU審査システム連携をバイパスすべきかの判断を設定する
     *
     * @param bool $beBypassIlu
     * @return LogicCreditJudgePrejudgeThread このインスタンス
     */
    public function setBeBypassIlu($beBypassIlu) {
        $this->_beBypassIlu = $beBypassIlu;
        return $this;
    }

    /**
     * LogicCreditJudgePrejudgeの新しいインスタンスを初期化する
     *
     * @param $adapter アダプター
     * @param $configPath コンフィグ情報
     * @param LogicCreditJudgeLogCache $logCache ログキャッシュ
     * @param int $oseq 注文Seq
     * @param int $userId ユーザーID
     * @param int $creditCriterionId 与信判定基準ID
     */
    public function __construct($adapter, $config, LogicCreditJudgeLogCache $logCache, $oseq, $userId, $creditCriterionId) {

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

            // 注文Seq初期化
            ->setOseq($oseq)

            // ユーザID初期化
            ->setUserId($userId)

            // 与信判定基準ID初期化
            ->setCreditCriterionId($creditCriterionId)
        ;
    }

    /**
     * 与信前処理として、T_CjResultの初期化とILU審査システムへの登録を実行する
     *
     * @access protected
     */
    public function run () {
        $start = microtime(true);
        $this->debug(sprintf('[%s] preJudge start', $this->getOseq()));
        // preJudge開始コールバックを実行
        $callback_result = false;
        try {
            $callback_result = $this->execCallback(self::CALLBACK_BEGIN_PREJUDGE, array($this->getOseq()));
        } catch(\Exception $callbackError) {
        }

        try {
            $mdlo = new TableOrder($this->getAdapter());
            $order = $mdlo->find($this->getOseq())->current();

            // テスト注文は処理なし
            if ($order['T_OrderClass'] == 1) {
                $GLOBALS['CreditLog']['Jud_judgeTOrderClass'] = 1;
                throw new \Exception(sprintf('[%s] preJudge bypassed (test order).', $this->getOseq()));
            }

             // T_CjResultに新規行追加
            $table = new TableCjResult($this->getAdapter());
            $table->saveNew(array(
                    'OrderSeq' => $this->getOseq(),
                    'OrderId' => $order['OrderId'],
                    'SendDate' => null,
                    'ReceiveDate' => null,
                    'TotalScore' => null,
                    'Result' => null,
                    'TotalScoreWeighting' => null,
                    'Status' => 0,
                    'RegistId' => $this->getUserId(),
                    'UpdateId' => $this->getUserId(),
                    'ValidFlg' => 1,
            ));

            // バイパス指定があったら例外扱い（＝何もしない）
            if($this->toBeBypassIlu()) {
                throw new \Exception(sprintf('[%s] preJudge bypassed.', $this->getOseq()));
                return;
            }

            // preJudge開始コールバックがfalseを返したら例外扱い（＝何もしない）
            if($callback_result === false) {
                throw new \Exception(sprintf('[%s] preJudge bypassed (cannot get thread lock).', $this->getOseq()));
                return;
            }

            // ILU審査システムへ注文登録
            $connector = new LogicCreditJudgeSystemConnect($this->getAdapter(), $this->getJudgeConfig());
            $connector->setCreditCriterionId($this->getCreditCriterionId());
            $connector->setUserId($this->getUserId());
            $retry = 0;
            $retry_max = 2;
            while(++$retry <= $retry_max) {
                try {
                    $connector->sendTo($this->getOseq());
                    break;  // エラーなしなら処理終了
                } catch(LogicCreditJudgeSystemConnectException $connError) {
                    // 接続絡みの例外時は既定回数リトライ
                    $this->debug(sprintf('[%s] SystemConnect::sentTo exception(%s times). -> %s', $this->getOseq(), $retry, $connError->getMessage()));
                    if($retry < $retry_max) {
                        // 既定回数未満の場合は1秒WAITを入れる
                        usleep(1 * 1000000);
                    } else {
                        // 既定回数に達したらエラー
                        throw $connError;
                    }
                } catch(\Exception $err) {
                    // その他の例外は上位へスロー
                    throw $err;
                }
            }
        } catch(\Exception $err) {
            $this->info(sprintf('[%s] preJudge[ERROR] -> %s', $this->getOseq(), $err->getMessage()));
            // 例外時は何もしない
        }

        // preJudge終了コールバックを実行
        try {
            $this->execCallback(self::CALLBACK_END_PREJUDGE, array($this->getOseq()));
        } catch(\Exception $callbackError) {
        }

        $this->info(sprintf('[%s] preJudge completed. elapsed time = %s', $this->getOseq(), (microtime(true) - $start)));
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

    /**
     * ILU審査システム連携をバイパスすべきかを判断する
     *
     * @return boolean
     */
    public function toBeBypassIlu() {
        return $this->getBeBypassIlu();
    }
}