<?php
namespace models\Logic\CreditJudge\Connector;

use Zend\Config\Reader\Ini;
use models\Logic\CreditJudge\LogicCreditJudgeOptions;

/**
 * 与信関連の外部システムとの接続を担当する抽象コネクタクラス
 */
abstract class LogicCreditJudgeConnectorAbstract {
    /**
     * オプション
     *
     * @access protected
     * @var LogicCreditJudgeOptions
     */
    protected $_options;

    /**
     * 送信キャッシュデータ
     *
     * @access protected
     * @var string
     */
    protected $_sentData;

    /**
     * 受信キャッシュデータ
     *
     * @access protected
     * @var string
     */
    protected $_receivedData;

    /**
     * オプションを指定してLogicCreditJudgeConnectorAbstractの
     * 新しいインスタンスを初期化する
     *
     * @param LogicCreditJudgeOptions | array | Zend\Config\Reader\Ini $options オプション
     */
    protected function __construct($options) {

        if(!($options instanceof LogicCreditJudgeOptions)) {
            // LogicCreditJudgeOptionsでない場合
            if($options instanceof Ini) {
                // Zend\Config\Reader\Iniの場合は配列に展開
                $options = new LogicCreditJudgeOptions($options->toArray());
            } else {
                // 配列以外の型の場合はオプション指定なしとして扱う
                $options = new LogicCreditJudgeOptions(is_array($options) ? $options : array());
            }
        }
        $this->setOptions($options);
    }

    /**
     * 現在のオプションを取得する
     *
     * @return LogicCreditJudgeOptions
     */
    public function getOptions() {
        return $this->_options;
    }
    /**
     * オプションを設定する
     *
     * @param LogicCreditJudgeOptions オプション
     * @return LogicCreditJudgeConnectorAbstract このインスタンス
     */
    public function setOptions(LogicCreditJudgeOptions $options) {
        $this->_options = $options;
    }

    /**
     * 送信データを指定して外部サービスに接続し、受信データを取得する
     *
     * @param array $params 送信データ（連想配列）
     * @return array 受信データを展開した連想配列
     */
    public function connect(array $params = array()) {
        // 送受信データキャッシュをクリアしておく
        $this->_sentData = null;
        $this->_receivedData = null;

        // 接続先の要求に合わせて送信データをエンコードする
        $send_data = $this->_encodeParams($params);

        // 接続先からの受信データを取得する
        $raw_response = $this->_options->getDebugMode() ?
            // デバッグモード時はローカルデータを取得
            $this->_connectDummy($send_data) :
            // 外部サービスからデータを取得
            $this->_connect($send_data);

        // エンコード済み送信データをキャッシュする
        $this->_sentData = $send_data;
        // デコード前の受信データをキャッシュする
        $this->_receivedData = $raw_response;

        // 受信データをデコードして返す
        return $this->_decodeResponse($raw_response);
    }

    /**
     * クラス固有の外部サービスに接続し、レスポンスデータを返す。
     * このメソッドは抽象メソッドなので、派生クラスで固有実装をする必要がある
     *
     * @abstract
     * @access protected
     * @param string $params 接続先が要求するフォーマットに適合した接続パラメータ
     * @return string 接続先から受信した生のレスポンスデータ
     */
    abstract protected function _connect($params);

    /**
     * クラス固有のデバッグデータファイルを読み込み、ダミーのレスポンスデータとして返す。
     * このメソッドは抽象メソッドなので、派生クラスで固有実装をする必要がある。
     * 基本的にはloadLocalDataまたはLoadLocalDataを呼び出す固有デバッグファイル読み込みメソッドへ
     * ディスパッチすればOK
     *
     * @abstract
     * @access protected
     * @param string $params 接続先が要求するフォーマットに適合した接続パラメータ
     * @return string ダミーレスポンスデータ
     */
    abstract protected function _connectDummy($params);

    /**
     * 送信データを接続先固有のフォーマットにエンコードする。
     * このメソッドは抽象メソッドなので、派生クラスで固有実装をする必要がある。
     *
     * @abstract
     * @access protected
     * @param array $params 連想配列形式の送信データ
     * @return string クラス固有の接続先が要求するフォーマットにエンコードされた送信用データ
     */
    abstract protected function _encodeParams(array $params);

    /**
     * 接続先から受信した生のレスポンスデータを連想配列にデコードする。
     * このメソッドは抽象メソッドなので、派生クラスで固有実装をする必要がある。
     *
     * @abstract
     * @access protected
     * @param string $response クラス固有の接続先から受信したレスポンスデータ
     * @return array レスポンスデータをデコードした連想配列
     */
    abstract protected function _decodeResponse($response);

    /**
     * 直前の接続時に送信したキャッシュデータを取得する。
     * このメソッドが返すキャッシュデータはconnectメソッド実行時に
     * 外部接続先へ実際に送信したフォーマットのデータである。
     *
     * @return stirng
     */
    public function getSentData() {
        return $this->_sentData;
    }

    /**
     * 直前の接続で受信したキャッシュデータを取得する。
     * このメソッドが返すキャッシュデータはconnectメソッド実行時に
     * 外部接続先から実際に受信したレスポンスデータである。
     *
     * @return string
     */
    public function getReceivedData() {
        return $this->_receivedData;
    }

    /**
     * 指定種別向けのデバッグ用ローカルファイルを読み込む
     *
     * @param string $type 種別。LogicCreditJudgeOptions定義されるKEY_*定数に一致する必要がある
     * @return string 指定種別のデバッグファイルの内容
     */
    public function loadLocalData($type) {
        if(!$this->_options->getDebugMode()) {
            throw new \Exception('oops !!! not in debug mode currently');
        }

        $dir = realpath($this->_options->getDebugDataDir());
        if(!is_dir($dir)) {
            throw new \Exception('invalid directory specified');
        }

        $path = f_path($dir, $this->_options->getDebugFileName($type), DIRECTORY_SEPARATOR);
        if(!is_file($path)) {
            throw new \Exception('file not found');
        }

        return file_get_contents($path);
    }

    /**
     * ILU審査システム用デバッグファイルを読み込む
     *
     * @return string
     */
    public function loadLocalIluData() {
        return $this->loadLocalData(LogicCreditJudgeOptions::KEY_ILU);
    }

    /**
     * ILU審査システムのパターンマスター用デバッグファイルを読み込む
     *
     * @return string
     */
    public function loadLocalIluPatternMasterData() {
        return $this->loadLocalData(LogicCreditJudgeOptions::KEY_ILU_PAT_MASTER);
    }

    /**
     * ジンテックAPI用のデバッグファイルを読み込む
     *
     * @return string
     */
    public function loadLocalJintecData() {
        return $this->loadLocalData(LogicCreditJudgeOptions::KEY_JINTEC);
    }

}
