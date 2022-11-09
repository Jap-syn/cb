<?php
namespace models\Logic\Validation;
/**
 * LogicValidationフレームワークによる入力検証の結果を格納するクラス。
 * getOriginalData()で検証対象となったデータを取得することができ、
 * エラー内容についてはgetErrors(), getInvalidKeys()でエラーメッセージとエラーとなった
 * 項目のキーを配列形式で取得することもできる。
 * また、isInvalidKey()で、指定の項目がエラーであるかを問い合わせることもできる
 */
class LogicValidationResult {
    /**
     * 発生した検証エラーメッセージの配列
     * @var array
     */
    protected $_errors;

    /**
     * 検証対象となった入力連想配列
     * @var array
     */
    protected $_originalData;

    /**
     * 検証処理後にフィルタが適用されたデータの連想配列
     * @var array
     */
    protected $_filteredData;

    /**
     * 検証エラーが発生した入力キーの配列
     * @var array
     */
    protected $_invalidKeys;

    /**
     * LogicValidationResultの新しいインスタンスを初期化する
     */
    public function __construct() {
        $this->_originalData = null;
        $this->_errors = array();
        $this->_filteredData = null;
        $this->_invalidKeys = array();
    }

    /**
     * 入力検証が正常であったかを判断する
     *
     * @return bool 入力検証が正常だった場合はtrue、それ以外はfalse
     */
    public function isValid() {
        return $this->errorCount() == 0;
    }

    /**
     * 発生した検証エラーの数を取得する
     *
     * @return int
     */
    public function errorCount() {
        return ! is_array($this->_errors) ? 0 : count($this->_errors);
    }

    /**
     * 検証対象となった入力データの連想配列を取得する
     *
     * @return array
     */
    public function getOriginalData() {
        return $this->_originalData;
    }
    /**
     * 検証対象となった入力データの連想配列を設定する
     *
     * @param array $original_data 検証対象となった入力データ
     * @return LogicValidationResult このインスタンス自身
     */
    public function setOriginalData(array $original_data) {
        if($original_data == null) $original_data = array();
        if(! is_array($original_data)) $original_data = array($original_data);
        $this->_originalData = $original_data;
        return $this;
    }

    /**
     * 検証時にフィルタが適用された状態の入力データを連想配列で取得する
     *
     * @return array
     */
    public function getFilteredData() {
        return $this->_filteredData;
    }
    /**
     * 検証時にフィルタが適用された状態の入力データの連想配列を設定する
     *
     * @param array $filtered_data フィルタ後の入力データ
     * @return LogicValidationResult このインスタンス自身
     */
    public function setFilteredData(array $filtered_data) {
        if($filtered_data == null) $filtered_data = array();
        if(! is_array($filtered_data)) $filtered_data = array($filtered_data);
        $this->_filteredData = $filtered_data;
        return $this;
    }

    /**
     * 発生した検証エラーメッセージをすべて取得する
     *
     * @return array
     */
    public function getErrors() {
        return $this->_errors;
    }
    /**
     * 発生した検証エラーメッセージの配列を設定する
     *
     * @param array $errors 発生した検証エラーメッセージ
     * @return LogicValidationResult このインスタンス自身
     */
    public function setErrors(array $errors) {
        if($errors == null) $errors = array();
        if(! is_array($errors)) $errors = array($errors);
        $this->_errors = array();
        return $this->addError($errors);
    }
    /**
     * 発生した検証エラーメッセージを追加する
     *
     * @param string $error エラーメッセージ
     * @return LogicValidationResult このインスタンス自身
     */
    public function addError($error) {
        if(! is_array($this->_errors)) $this->_errors = array();
        if($error != null) {
            if(! is_array($error)) $error = array($error);
            foreach($error as $key => $value) {
                $this->_errors[] = $value;
            }
        }
        return $this;
    }

    /**
     * 指定の入力キーでエラーが発生したかを判断する
     *
     * @param string $key 入力キー
     * @return bool
     */
    public function isErrorKey($key) {
        return in_array($key, $this->getInvalidKeys());
    }

    /**
     * 検証エラーが発生したデータの入力キーをすべて取得する
     *
     * @return array
     */
    public function getInvalidKeys() {
        return $this->_invalidKeys;
    }
    /**
     * 検証エラーが発生したデータの入力キー配列を設定する
     *
     * @param array $invalid_keys 検証エラーが発生した入力キーの配列
     * @return LogicValidationResult
     */
    public function setInvalidKeys($invalid_keys) {
        if($invalid_keys == null) $invalid_keys = array();
        if(! is_array($invalid_keys)) $invalid_keys = array($invalid_keys);
        $this->_invalidKeys = array();
        return $this->addInvalidKey($invalid_keys);
    }
    /**
     * 検証エラーが発生したデータの入力キーを追加する
     *
     * @param string $invalid_key 検証エラーが発生した入力キー
     * @return LogicValidationResult このインスタンス自身
     */
    public function addInvalidKey($invalid_key) {
        if(! is_array($this->_invalidKeys)) $this->_invalidKeys = array();
        if($invalid_key != null) {
            if(! is_array($invalid_key)) $invalid_key = array($invalid_key);
            foreach($invalid_key as $key => $value) {
                $this->_invalidKeys[] = $value;
            }
        }
        return $this;
    }
}
