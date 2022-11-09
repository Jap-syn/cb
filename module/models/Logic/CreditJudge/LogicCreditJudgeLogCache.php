<?php
namespace models\Logic\CreditJudge;
/**
 * 外部ログ出力の内容をオンメモリで保持するキャッシュ
 */
class LogicCreditJudgeLogCache {
    /**
     * キャッシュ領域
     *
     * @access protected
     * @var array
     */
    protected $_cache;

    /**
     * LogicCreditJudgeLogCacheの新しいインスタンスを
     * 初期化する
     */
    public function __construct() {
        $this->clearCache();
    }

    /**
     * キャッシュデータを取得する
     *
     * @return array
     */
    public function getCache() {
        return $this->_cache;
    }

    /**
     * キャッシュをクリアする
     *
     * @return LogicCreditJudgeLogCache
     */
    public function clearCache() {
        $this->_cache = array();
        return $this;
    }

    /**
     * ログメッセージをキャッシュする
     *
     * @param string $message ログメッセージ
     * @return LogicCreditJudgeLogCache
     */
    public function append($message) {
        $this->_cache[] = $message;
        return $this;
    }
}
