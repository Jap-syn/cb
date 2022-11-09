<?php
namespace models\Logic\SmbcRelation\Adapter;

use Zend\Log\Logger;
use Coral\Base\BaseLog;
use models\Logic\SmbcRelation\LogicSmbcRelationAdapter;

/**
 * SMBC決済ステーションへの接続を担う抽象接続アダプタ
 */
abstract class LogicSmbcRelationAdapterAbstract {
    /**
     * 接続先URL
     *
     * @access protected
     * @var string
     */
    protected $_url;

    /**
     * HTTPタイムアウト（秒）
     *
     * @access protected
     * @var int
     */
    protected $_timeout = 15;

    /**
     * 接続リトライ回数
     *
     * @access protected
     * @var int
     */
    protected $_retry = 2;

    /**
     * 送受信テキストエンコード
     *
     * @access protected
     * @var string
     */
    protected $_enc = 'SJIS';

    /**
     * 対象機能
     *
     * @access protected
     * @var string
     */
    protected $_target_func = 0;

    /**
     * ロガーインスタンス
     *
     * @access protected
     * @var BaseLog
     */
    protected $_logger;

    /**
     * 決済ステーション接続アダプタの新しいインスタンスを初期化する
     *
     * @param string $url 接続先URL
     * @param array $options オプション設定
     */
    public function __construct($url, array $options = array()) {
        // URLを設定
        $this->setUrl($url);

        // その他のオプションを設定
        foreach($options as $key => $value) {
            switch($key) {
                case LogicSmbcRelationAdapter::OPT_TIMEOUT :
                    $this->setRequestTimeout($value);
                    break;
                case LogicSmbcRelationAdapter::OPT_RETRY :
                    $this->setRetryCount($value);
                    break;
                case LogicSmbcRelationAdapter::OPT_TEXT_ENC :
                    $this->setTextEncoding($value);
                    break;
                case LogicSmbcRelationAdapter::OPT_TARGET_FUNC :
                    $this->setTargetFunctionCode($value);
                    break;
            }
        }
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
     * @return LogicSmbcRelationAdapterAbstract このインスタンス
     */
    public function setLogger(BaseLog $logger = null) {
        $this->_logger = $logger;
        return $this;
    }

    /**
     * 接続先URLを取得する
     *
     * @return string
     */
    public function getUrl() {
        return $this->_url;
    }
    /**
     * 接続先URLを設定する
     *
     * @param string $url 接続先URL
     * @return LogicSmbcRelationAdapterAbstract このインスタンス
     */
    public function setUrl($url) {
        $this->_url = $url;
        return $this;
    }

    /**
     * 秒単位で設定されているHTTP接続タイムアウト値を取得する
     *
     * @return int
     */
    public function getRequestTimeout() {
        return $this->_timeout;
    }
    /**
     * HTTP接続タイムアウト値を秒単位で設定する
     *
     * @param int $timeOut タイムアウト値（秒）
     * @return LogicSmbcRelationAdapterAbstract このインスタンス
     */
    public function setRequestTimeout($timeOut) {
        // 入力値の整備
        $timeOut = (int)$timeOut;
        if($timeOut < 1) $timeOut = 1;
        if($timeOut > 600) $timeOut = 600;

        $this->_timeout = $timeOut;
        return $this;
    }

    /**
     * 接続リトライ回数を取得する
     *
     * @return int
     */
    public function getRetryCount() {
        return $this->_retry;
    }
    /**
     * 接続リトライ回数を設定する
     *
     * @param int $retry リトライ回数
     * @return LogicSmbcRelationAdapterAbstract このインスタンス
     */
    public function setRetryCount($retry) {
        $retry = (int)$retry;
        if($retry < 1) $retry = 1;
        if($retry > 10) $retry = 10;

        $this->_retry = (int)$retry;
        return $this;
    }

    /**
     * 決済ステーションとの送受信に使用するエンコードを取得する
     *
     * @return string
     */
    public function getTextEncoding() {
        return $this->_enc;
    }
    /**
     * 決済ステーションとの送受信に使用するエンコードを設定する
     *
     * @param string $enc 使用するテキストエンコード
     * @return LogicSmbcRelationAdapterAbstract このインスタンス
     */
    public function setTextEncoding($enc) {
        $this->_enc = $enc;
        return $this;
    }

    /**
     * 対象の決済ステーション機能を指定するための識別コードを取得する
     *
     * @return int 機能識別コード
     */
    public function getTargetFunctionCode() {
        return $this->_target_func;
    }
    /**
     * 対象の決済ステーション機能を指定するための識別コードを設定する
     *
     * @param int $code 機能識別コード
     * @return LogicSmbcRelationAdapterAbstract このインスタンス
     */
    public function setTargetFunctionCode($code) {
        $this->_target_func = (int)$code;
        return $this;
    }

    /**
     * 決済ステーションへ指定データを送信し、受信結果を返す
     *
     * @abstract
     * @param array $data 送信データ
     * @return array 受信データ
     */
    abstract public function send(array $data);

    /**
     * 送信データの連想配列をHTTP送信向けにフォーマットする
     *
     * @abstract
     * @access protected
     * @param array $data 送信データ
     * @return mixed フォーマット済みデータ
     */
    abstract protected function formatParams(array $data);

    /**
     * 受信したコンテンツを受信結果データに展開する
     *
     * @abstract
     * @access protected
     * @param mixed $response 受信したコンテンツ
     * @return array 展開済み受信データ
     */
    abstract protected function parseResponse($response);

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
