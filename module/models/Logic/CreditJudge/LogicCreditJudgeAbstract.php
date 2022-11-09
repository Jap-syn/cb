<?php
namespace models\Logic\CreditJudge;

use Zend\Db\Adapter\Adapter;
use Zend\Log\Logger;
use Coral\Base\BaseLog;

/**
 * 与信実行のの基底抽象クラス
 */
abstract class LogicCreditJudgeAbstract implements LogicCreditJudgeInterface {
	// 判定結果定数：与信NG確定
	const JUDGE_RESULT_NG = -1;

	// 判定結果定数：与信OK確定
	const JUDGE_RESULT_OK = 1;

	// 判定結果定数：与信保留確定
	const JUDGE_RESULT_PENDING = 2;

	// 判定結果定数：審査継続
	const JUDGE_RESULT_CONTINUE = 3;

	/**
	 * デフォルトロガーインスタンス
	 *
	 * @static
	 * @access protected
	 * @var BaseLog
	 */
	protected static $__logger;

	/**
	 * デフォルトログキャッシュ
	 *
	 * @static
	 * @access protected
	 * @var LogicCreditJudgeLogCache
	 */
	protected static $__logCache;

	/**
	 * デフォルトのロガーを取得する
	 *
	 * @static
	 * @return BaseLog
	 */
	public static final function getDefaultLogger() {
		return self::$__logger;
	}
	/**
	 * デフォルトのロガーを設定する
	 *
	 * @static
	 * @param BaseLog ロガー
	 */
	public static final function setDefaultLogger(BaseLog $logger = null) {
		self::$__logger = $logger;
	}

	public static function getDefaultLogCache() {
		return self::$__logCache;
	}
	public static function setDefaultLogCache(LogicCreditJudgeLogCache $cache = null) {
		self::$__logCache = $cache;
	}

	/**
	 * judgeメソッドが返す判定結果値に対応する表示用文言を取得する
	 *
	 * @static
	 * @param int $result 判定結果値。JUDGE_RESULT_*定数を指定する
	 * @return string $resultに対応した表示用の文言
	 */
	public static function getJudgeResultLabel($result) {
		switch($result) {
			case self::JUDGE_RESULT_NG:
				return '与信NG';
			case self::JUDGE_RESULT_OK:
				return '与信OK';
			case self::JUDGE_RESULT_PENDING:
				return '与信保留';
			case self::JUDGE_RESULT_CONTINUE:
				return '審査継続';
		}
		throw new \Exception('未定義の判定結果定数');
	}

	/**
	 * データベースアダプタ
	 * @access protected
	 * @var Adapter
	 */
	protected $_adapter = null;

	/**
	 * ロガーインスタンス
	 *
	 * @access protected
	 * @var BaseLog
	 */
	protected $_logger;

	/**
	 * 与信実行時に使用するDBキャッシュ
	 * @access protected
	 * @var LogicCreditJudgeDbCache
	 */
	protected $_dbCache;

	/**
	 * 追加オプション
	 * @access protected
	 * @var array
	 */
	protected $_options;

	/**
	 * 内部ログキャッシュ
	 * @access protected
	 * @var LogicCreditJudgeLogCache
	 */
	protected $_logCache = null;

	/**
	 * LogicCreaditJudgeAbstract の新しいインスタンスを初期化します。
	 *
	 * @ignore
	 * @access private
	 */
	public function __construct(Adapter $adapter, array $options = array()) {
		$this
			->clearLogCache()
			->setLogCache(self::getDefaultLogCache())
			->setAdapter($adapter)
			->setOptions($options)
			->setLogger(self::getDefaultLogger());

		$this->_dbCache = new LogicCreditJudgeDbCache($this->getAdapter());
	}

    /**
     * データベースアダプタを取得する
     * @return Abstract
     */
	public function getAdapter() {
		return $this->_adapter;
	}
    /**
     * データベースアダプタを設定する
     *
     * @param Adapter データベースアダプタ
	 * @return LogicCreditJudgeAbstract
     */
	public function setAdapter(Adapter $adapter) {
		$this->_adapter = $adapter;
		return $this;
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
	 * @return LogicCreditJudgeAbstract
	 */
	public function setLogger(BaseLog $logger = null) {
		$this->_logger = $logger;
		return $this;
	}

	/**
	 * 追加オプションの配列を取得する
	 *
	 * @return array
	 */
	public function getOptions() {
		return $this->_options;
	}
	/**
	 * 追加オプションの配列を設定する
	 *
	 * @param null | array $options 追加オプション
	 * @return LogicCreditJudgeAbstract
	 */
	public function setOptions(array $options = array()) {
		if(!is_array($options)) $options = array();
		$this->_options = $options;
		return $this;
	}
	/**
	 * 指定キーに一致する追加オプション値を取得する
	 *
	 * @param string $name オプションキー
	 * @return mixed
	 */
	public function getOption($name) {
		return isset($this->_options[$name]) ? $this->_options[$name] : null;
	}
	/**
	 * キーと値を指定してオプション値を設定する
	 *
	 * @param string $name オプションキー
	 * @param mixed $value オプション値
	 * @return LogicCreditJudgeAbstract
	 */
	public function setOption($name, $value) {
		if(!is_array($this->_options)) $this->_options = array();
		$this->_options[$name] = $value;
		return $this;
	}

	/**
	 * DBキャッシュインスタンスを取得する
	 *
	 * @return LogicCreditJudgeDbCache
	 */
	public function getDbCache() {
		return $this->_dbCache;
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
	 * @return LogicCreditJudgeAbstract
	 */
	public function setLogCache(LogicCreditJudgeLogCache $cache = null) {
		$this->_logCache = $cache;
		return $this;
	}
	/**
	 * 内部ログキャッシュをクリアする
	 *
	 * @return LogicCreditJudgeAbstract
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
     * 指定の注文の審査を実行し、判定結果を返す。
     * 判定結果は以下の定数値のいずれかを返す。
     * JUDGE_RESULT_NG：与信NG確定
     * JUDGE_RESULT_OK：与信OK確定
     * JUDGE_RESULT_PENDING：与信保留確定（＝手動与信対象）
     * JUDGE_RESULT_CONTINUE：審査継続
     *
     * @param int $oseq 注文SEQ
     * @return int 判定結果
     */
	//abstract public function judge($oseq);

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

    /**
     * 事業者が保留無し事業者かの判定
     *
     * @param $oseq 注文番号
     * @return boolean
     */
    public function judgeNoPendingEnt($oseq) {
        // 事業者情報取得
        $orderCustomer = $this->getDbCache()->fetchOrderCustomer($oseq)->current();
        $ent = $this->getDbCache()->fetchEnterprise($orderCustomer['EnterpriseId'])->current();
        return $ent['AutoCreditJudgeMode'] == 4 ? true : false;
    }
}
