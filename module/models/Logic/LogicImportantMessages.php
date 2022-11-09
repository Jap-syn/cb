<?php
namespace models\Logic;

use Zend\Session\Container;

/**
 * 既読管理が可能なセッションレベルのメッセージ管理ロジック
 */
class LogicImportantMessages {
    /**
     * デフォルトインスタンスが使用するセッション名前空間の初期値
     * @static
     * @access protected
     * @var string
     */
    protected static $_defaultNamespace = 'LogicImportantMessages';

    /**
     * デフォルトのセッション名前空間を取得する。
     * この値は、パラメータなしのコンストラクタで初期化されたインスタンスで使用される
     *
     * @static
     * @return string
     */
    public static function getDefaultNamespace() {
        return self::$_defaultNamespace;
    }
    /**
     * デフォルトの名前空間を設定する。
     * ここで設定した名前空間は、パラメータなしのコンストラクタで初期化されたインスタンスで使用される
     *
     * @static
     * @param string $namespace デフォルトとして設定するのセッション名前空間
     */
    public static function setDefaultNamespace($namespace) {
        //$namespace = nvl($namespace, get_class($this));
        $namespace = nvl($namespace, "models\Logic");
        self::$_defaultNamespace = $namespace;
    }

    /**
     * セッションストレージ
     *
     * @access protected
     * @var Container
     */
    protected $_storage;

    /**
     * 使用するセッション名前空間を指定して、LogicImportantMessagesの
     * 新しいインスタンスを初期化する
     *
     * @param string | null $namespace このインスタンスで使用するセッション名前空間
     *                                 省略時はデフォルトの名前空間が使用される
     */
    public function __construct($namespace = null) {
        $namespace = nvl($namespace, self::getDefaultNamespace());
        $this->_storage = new Container($namespace);
    }

    /**
     * ストレージの内容を適切に初期化する
     *
     * @access protected
     * @return LogicImportantMessages このインスタンス
     */
    protected function _initStorage() {
        // 内容未設定時のみ初期化
        if(!isset($this->_storage->messages) || !is_array($this->_storage->messages)) {
            $this->_storage->messages = array();
        }
        return $this;
    }

    /**
     * 登録済みメッセージの件数を取得する
     *
     * @return int
     */
    public function countAllMessages() {
        $messages = $this->getMessages();
        $unreadedCount = 0;
        if (!empty($messages['unreaded'])) {
            $unreadedCount = count($messages['unreaded']);
        }
        $readedCount = 0;
        if (!empty($messages['readed'])) {
            $readedCount = count($messages['readed']);
        }
        $count = $unreadedCount + $readedCount;
        return $count;
    }

    /**
     * 未読メッセージの件数を取得する
     *
     * @return int
     */
    public function countUnreadedMessages() {
        $this->_initStorage();
        $count = 0;
        foreach($this->_storage->messages as $message_data) {
            if(!$message_data['state']) $count++;
        }
        return $count;
    }

    /**
     * 既読メッセージの件数を取得する
     *
     * @return int
     */
    public function countReadedMessages() {
        $this->_initStorage();
        $count = 0;
        foreach($this->_storage->messages as $message_data) {
            if($message_data['state']) $count++;
        }
        return $count;
    }

    /**
     * すべてのメッセージを取得し、未読メッセージをすべて既読にする。
     * 戻り値の配列は、キー'unreaded'にすべての未読メッセージ、'readed'に
     * すべての既読メッセージが格納される。
     *
     * @return array
     */
    public function getMessages() {
        $this->_initStorage();
        $result = array(
            'unreaded' => array(),
            'readed' => array()
        );
        foreach($this->_storage->messages as &$message_data) {
            $key = $message_data['state'] ? 'readed' : 'unreaded';
            $result[$key][] = $message_data['message'];
            if(!$message_data['state']) $message_data['state'] = 1;
        }
        return $result;
    }

    /**
     * 新しいメッセージを追加する
     *
     * @param string $message 追加するメッセージ
     * @return LogicImportantMessages このインスタンス
     */
    public function addMessage($message) {
        $message = nvl($message);
        if(strlen($message)) {
            $this->_initStorage();
            $cur_messages = array();
            foreach($this->_storage->messages as $msg) {
                $cur_messages[] = $msg['message'];
            }
            // 既存メッセージと重複していない場合のみ追加
            if(!in_array($message, $cur_messages)) {
                $this->_storage->messages[] = array(
                    'message' => $message,
                    'state' => 0
                );
            }
        }
        return $this;
    }

    /**
     * すべてのメッセージを破棄する
     *
     * @return LogicImportantMessages このインスタンス
     */
    public function clearMessages() {
        $this->_initStorage();
        $this->_storage->messages = array();
        return $this;
    }

}
