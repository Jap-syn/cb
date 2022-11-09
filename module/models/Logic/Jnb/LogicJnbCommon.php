<?php
namespace models\Logic\Jnb;

use Zend\Db\Adapter\Adapter;
use Zend\Log\Logger;
use Coral\Base\BaseLog;

/**
 * JNB関連ロジックの共通機能を提供する抽象クラス
 */
abstract class LogicJnbCommon {
	/**
	 * デフォルトロガーインスタンス
	 *
	 * @static
	 * @access protected
	 * @var BaseLog
	 */
	protected static $__logger;

	/**
	 * デフォルトのSMTP設定
	 *
	 * @static
	 * @access protected
	 * @var array
	 */
	protected static $__smtp_config = array('smtp' => 'localhost', 'charset' => 'ISO-2022-JP');

	/**
	 * デフォルトのロガーを取得する
	 *
	 * @static
	 * @final
	 * @return BaseLog
	 */
	public static final function getDefaultLogger() {
		return self::$__logger;
	}
	/**
	 * デフォルトのロガーを設定する
	 *
	 * @static
	 * @final
	 * @param BaseLog ロガー
	 */
	public static final function setDefaultLogger(BaseLog $logger = null) {
		self::$__logger = $logger;
	}

	/**
	 * デフォルトで使用するメール送信向けSMTPサーバ情報を取得する
	 *
	 * @static
	 * @final
	 * @return string
	 */
	public static final function getDefaultSmtpServer() {
		return self::$__smtp_config['smtp'];
	}
	/**
	 * デフォルトで使用するメール送信向けSMTPサーバ情報を設定する
	 *
	 * @static
	 * @final
	 * @param string $smtp SMTPサーバ情報
	 */
	public static final function setDefaultSmtpServer($smtp = 'localhost') {
		self::$__smtp_config['smtp'] = $smtp;
	}

	/**
	 * デフォルトで使用するメール送信向け文字コード設定を取得する
	 *
	 * @static
	 * @final
	 * @return string
	 */
	public static final function getDefaultMailCharset() {
		return self::$__smtp_config['charset'];
	}
	/**
	 * デフォルトで使用するメール送信向け文字コードを設定する
	 *
	 * @static
	 * @final
	 * @param string $charset 文字コード
	 */
	public static final function setDefaultMailCharset($charset = 'ISO-2022-JP') {
	    self::$__smtp_config['charset'] = $charset;
	}

	/**
	 * アダプタ
	 *
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
	 * メール送信向けのSMTP設定
	 *
	 * @access protected
	 * @var array
	 */
	protected $_smtp_config;

    /**
     * DBアダプタを指定してLogicJnbCommonの新しいインスタンスを
     * 初期化する。
     *
     * この抽象クラスでは、DBアダプタとロガーのセットアップを行うので、他のコンストラクタオプションを
     * 必要とする派生クラスは固有のコンストラクタコードの先頭でこのコンストラクタを必ず呼び出す
     * 必要がある
     *
     * @param Adapter $adapter アダプタ
     */
    public function __construct(Adapter $adapter) {
        $this->setDbAdapter($adapter);
        $this->setSmtpServer(self::getDefaultSmtpServer());
        $this->setMailCharset(self::getDefaultMailCharset());
        $this->setLogger(self::getDefaultLogger());
    }

    /**
     * DBアダプタを取得する
     *
     * @return Adapter
     */
    public function getDbAdapter() {
        return $this->_adapter;
    }
    /**
     * DBアダプタを設定する
     *
     * @param Adapter $adapter アダプタ
     * @return LogicJnbCommon このインスタンス
     */
    public function setDbAdapter(Adapter $adapter) {
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
     * @return LogicJnbCommon このインスタンス
	 */
	public function setLogger(BaseLog $logger = null) {
	    $this->_logger = $logger;
		return $this;
	}

	/**
	 * メール送信向けのSMTPサーバ情報設定を取得する
	 *
	 * @return string
	 */
	public function getSmtpServer() {
	    return $this->_smtp_config['smtp'];
	}
	/**
	 * メール送信向けのSTMPサーバ情報を設定する
	 *
	 * @param string $smtp SMTPサーバ情報。省略時はlocalhost
     * @return LogicJnbCommon このインスタンス
	 */
	public function setSmtpServer($smtp = 'localhost') {
	    $this->_smtp_config['smtp'] = $smtp;
		return $this;
	}

	/**
	 * メール送信向けの文字コード設定を取得する
	 *
	 * @return string
	 */
	public function getMailCharset() {
	    return $this->_smtp_config['charset'];
	}
	/**
	 * メール送信向けの文字コードを設定する
	 *
	 * @param string $charset 文字コード
     * @return LogicJnbCommon このインスタンス
	 */
	public function setMailCharset($charset = 'ISO-2022-JP') {
	    $this->_smtp_config['charset'] = $charset;
		return $this;
	}

    /**
     * 注文テーブルを取得する
     *
     * @return TableOrder
     */
    public function getOrderTable() {
        return new \models\Table\TableOrder($this->_adapter);
    }

    /**
     * 請求履歴テーブルを取得する
     *
     * @return TableClaimHistory
     */
    public function getClaimHistoryTable() {
        return new \models\Table\TableClaimHistory($this->_adapter);
    }

    /**
     * JNB契約情報テーブルを取得する
     *
     * @return TableJnb
     */
    public function getJnbTable() {
        return new \models\Table\TableJnb($this->_adapter);
    }
    /**
     * JNB口座グループテーブルを取得する
     *
     * @return TableJnbAccountGroup
     */
    public function getJnbGroupTable() {
        return new \models\Table\TableJnbAccountGroup($this->_adapter);
    }
    /**
     * JNB口座テーブルを取得する
     *
     * @return TableJnbAccount
     */
    public function getJnbAccountTable() {
        return new \models\Table\TableJnbAccount($this->_adapter);
    }
    /**
     * JNB口座インポート作業テーブルを取得する
     *
     * @return TableJnbAccountImportWork
     */
    public function getImportWorkTable() {
        return new \models\Table\TableJnbAccountImportWork($this->_adapter);
    }
    /**
     * JNB支店マスターを取得する
     *
     * @return TableJnbBranch
     */
    public function getBranchMaster() {
        return new \models\Table\TableJnbBranch($this->_adapter);
    }
    /**
     * JNB口座利用履歴テーブルを取得する
     *
     * @return TableJnbAccountUsageHistory
     */
    public function getAccountUsageHistoryTable() {
        return new \models\Table\TableJnbAccountUsageHistory($this->_adapter);
    }
    /**
     * JNB入金通知管理テーブルを取得する
     *
     * @return TableJnbPaymentNotification
     */
    public function getPaymentNotificationTable() {
        return new \models\Table\TableJnbPaymentNotification($this->_adapter);
    }

    /**
     * 指定の優先度でログメッセージを出力する
     *
     * @param string $message ログメッセージ
     * @param int $priority 優先度
     */
    public function log($message, $priority) {
        $message = sprintf('[%s] %s', get_class($this), $message);
        if($this->_logger) {
            $this->_logger->log($priority, $message);
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
