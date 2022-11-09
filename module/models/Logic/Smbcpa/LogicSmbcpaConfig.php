<?php
namespace models\Logic\Smbcpa;

use Zend\Db\Adapter\Adapter;
use models\Table\TableSystemProperty;

/**
 * SMBCバーチャル口座関連のシステム設定を管理するためのロジッククラス
 */
class LogicSmbcpaConfig extends LogicSmbcpaCommon {
    /** プロパティ名定数：デフォルトの銀行名 @var string */
    const PROP_DEFAULT_BANK_NAME = 'DefaultBankName';

    /** プロパティ名定数：デフォルトの金融機関コード @var string */
    const PROP_DEFAULT_BANK_CODE = 'DefaultBankCode';

    /** プロパティ名定数：入金済み口座の開放待ち期間 @var string */
    const PROP_REL_INTERVAL = 'ReleaseAfterReceiptInterval';

    /** プロパティ名定数：再請求7期限超過後の強制開放猶予期間 @var string */
    const PROP_FORCE_REL_INTERVAL = 'ForceReleaseOverReclaim7LimitInterval';

    /** プロパティ名定数：返却済み口座復活機能許可フラグ @var string */
    const PROP_ALLOW_RESTORE_RET_ACC = 'AllowRestoreReturnedAccounts';

    /** プロパティ名定数：デバッグ用入金通知フォーム利用許可フラグ @var string */
    const PROP_ALLOW_DEBUG_RCPT_FORM = 'AllowDebugReceiptForm';

    /** プロパティ名定数：デバッグ用入金通知フォームの送信先にフルURLを許可するかのフラグ @var string */
    const PROP_ALLOW_FULL_URL_TO_DBG_RCPT_FORM = 'AllowFullUrlToDebugReceiptForm';

    /** プロパティ型定数：文字列型 @var string */
    const PROP_TYPE_STRING = 'string';

    /** プロパティ型定数：整数型 @var string */
    const PROP_TYPE_INT = 'int';

    /** プロパティ型定数：真偽値型 @var string */
    const PROP_TYPE_BOOL = 'bool';

    /**
     * @access protected
     * @var TableSystemProperty
     */
    protected $_sysProps;

    /**
     * DBアダプタを指定してLogicSmbcpaConfigの新しいインスタンスを初期化する
     *
     * @param Adapter $adapter アダプタ
     */
    public function __construct(Adapter $adapter) {
        parent::__construct($adapter);

        // 設定情報を初期化
        $this->initConfig();
    }

    /**
     * システムプロパティテーブルを取得する
     *
     * @return TableSystemProperty
     */
    public function getSystemPropertyTable() {
        if($this->_sysProps == null) {
            $this->_sysProps = new TableSystemProperty($this->_adapter);
        }
        if(!$this->_sysProps->isFiltered()) {
            $this->_sysProps->setFilterCategory($this->getTargetCategory());
        }
        return $this->_sysProps;
    }

    /**
     * このクラスで使用するシステムプロパティのカテゴリを取得する
     *
     * @return string
     */
    public function getTargetCategory() {
        return TableSystemProperty::FIX_CATEGORY_SMBCPACONF;
    }

    /**
     * デフォルトのSMBCバーチャル口座銀行名を取得する
     *
     * @return string
     */
    public function getDefaultBankName() {
        return $this->_getValue(self::PROP_DEFAULT_BANK_NAME);
    }

    /**
     * デフォルトのSMBCバーチャル口座金融機関コードを取得する
     *
     * @return string
     */
    public function getDefaultBankCode() {
        return $this->_getValue(self::PROP_DEFAULT_BANK_CODE);
    }

    /**
     * 入金済み口座の開放待ち日数を取得する
     *
     * @return int
     */
    public function getReleaseAfterReceiptInterval() {
        return $this->_getValue(self::PROP_REL_INTERVAL);
    }

    /**
     * 再請求7期限超過後の強制口座解放までの猶予日数を取得する
     *
     * @return int
     */
    public function getForceReleaseOverReclaim7LimitInterval() {
        return $this->_getValue(self::PROP_FORCE_REL_INTERVAL);
    }

    /**
     * 返却済み口座復活機能の利用を許可するかの設定を取得する
     *
     * @return boolean
     */
    public function getAllowRestoreReturnedAccounts() {
        return $this->_getValue(self::PROP_ALLOW_RESTORE_RET_ACC);
    }

    /**
     * デバッグ用入金通知シミュレーターの利用を許可するかの設定を取得する
     *
     * @return boolean
     */
    public function getAllowDebugReceiptForm() {
        return $this->_getValue(self::PROP_ALLOW_DEBUG_RCPT_FORM);
    }

    /**
     * 入金通知シミュレーターの送信先URLにフルURLを許容するかの設定を取得する
     *
     * @return boolean
     */
    public function getAllowFullUrlToDebugReceiptForm() {
        return $this->_getValue(self::PROP_ALLOW_FULL_URL_TO_DBG_RCPT_FORM);
    }

    /**
     * すべての設定内容を連想配列で取得する
     *
     * @return array
     */
    public function export() {
        $results = array();
        foreach($this->_getDefaultConfig() as $prop => $value) {
            $results[$prop] = $this->_getValue($prop);
        }
        return $results;
    }

    /**
     * 設定情報を初期化する
     *
     * @access protected
     */
    protected function initConfig() {
        $sysProp = $this->getSystemPropertyTable();

        // DBに永続化されていないキーがあったらDBへデフォルト値を永続化する
        foreach($this->_getDefaultConfig() as $prop => $value) {
            if(!$sysProp->propNameExists($prop, $this->getTargetCategory())) {
                $data = array(
                    'Category' => $this->getTargetCategory(),
                    'Name' => $prop,
                    'PropValue' => $value,
                    'Description' => sprintf('SMBCバーチャル口座設定：%s', $this->_getPropDescription($prop))
                );
                $sysProp->saveNew($data);
            }
        }
    }

    /**
     * SMBCバーチャル口座設定で使用するプロパティ名とそのデフォルト値一式を取得する
     *
     * @access protected
     * @return array
     */
    protected function _getDefaultConfig() {
        return array(
            // デフォルトの銀行名
            self::PROP_DEFAULT_BANK_NAME => '三井住友銀行',

            // デフォルトの金融機関コード
            self::PROP_DEFAULT_BANK_CODE => '0009',

            // 入金済み口座の開放待ち日数
            self::PROP_REL_INTERVAL => 30,

            // 再請求7期限超過後の強制口座解放猶予日数
            self::PROP_FORCE_REL_INTERVAL => 65,

            // 返却済み口座復活機能の利用許可
            self::PROP_ALLOW_RESTORE_RET_ACC => false,

            // デバッグ用入金通知フォームの利用許可
            self::PROP_ALLOW_DEBUG_RCPT_FORM => false,

            // デバッグ用入金通知フォームの送信先URLにフルURLを許可するかのフラグ
            self::PROP_ALLOW_FULL_URL_TO_DBG_RCPT_FORM => false
        );
    }

    /**
     * プロパティ名定数に応じたプロパティ内容の説明を取得する
     *
     * @access protected
     * @param string $propName プロパティ名
     * @return string
     */
    protected function _getPropDescription($propName) {
        switch($propName) {
            case self::PROP_DEFAULT_BANK_NAME:
                return 'デフォルトのSMBCバーチャル口座銀行名';

            case self::PROP_DEFAULT_BANK_CODE:
                return 'デフォルトのSMBCバーチャル口座金融機関コード';

            case self::PROP_REL_INTERVAL:
                return '入金済み口座の開放待ち日数';

            case self::PROP_FORCE_REL_INTERVAL:
                return '再請求7期限超過後の強制口座解放までの猶予日数';

            case self::PROP_ALLOW_RESTORE_RET_ACC:
                return '返却済み口座復活機能の利用許可';

            case self::PROP_ALLOW_DEBUG_RCPT_FORM:
                return 'デバッグ用入金通知シミュレーターの利用許可';

            case self::PROP_ALLOW_FULL_URL_TO_DBG_RCPT_FORM:
                return '入金通知シミュレーターの送信先URLにフルURLを許容するかの設定';
        }
        return null;
    }

    /**
     * 指定のプロパティのデータ型を取得する
     *
     * @access protected
     * @param string $propName プロパティ名
     * @return string プロパティ型
     */
    protected function _getPropType($propName) {
        switch($propName) {
            case self::PROP_DEFAULT_BANK_NAME:
            case self::PROP_DEFAULT_BANK_CODE:
                return self::PROP_TYPE_STRING;

            case self::PROP_REL_INTERVAL:
            case self::PROP_FORCE_REL_INTERVAL:
                return self::PROP_TYPE_INT;

            case self::PROP_ALLOW_RESTORE_RET_ACC:
            case self::PROP_ALLOW_DEBUG_RCPT_FORM:
            case self::PROP_ALLOW_FULL_URL_TO_DBG_RCPT_FORM:
                return self::PROP_TYPE_BOOL;
        }
        return self::PROP_TYPE_STRING;
    }

    /**
     * 指定のプロパティの値を、指定のデータ型で取得する
     *
     * @access protected
     * @param string $propName プロパティ名
     * @return mixed
     */
    protected function _getValue($propName) {
        $type = $this->_getPropType($propName);

        $defaults = $this->_getDefaultConfig();

        $value = trim(nvl($this->getSystemPropertyTable()->getValue(
            \models\Table\TableSystemProperty::DEFAULT_MODULE,
            \models\Table\TableSystemProperty::FIX_CATEGORY_SMBCPACONF,
            $propName
        )));
        // DBの値がブランクやnullの場合はデフォルト値の取得を試みる
        if(!strlen($value)) $value = trim(nvl($defaults[$propName]));

        switch($type) {
            case self::PROP_TYPE_INT:
                return (int)$value;
            case self::PROP_TYPE_BOOL:
                return $this->_changeToBool($value);
        }

        // 未定義のプロパティ型が指定された場合は文字列として扱う
        return $value;
    }

    /**
     * プロパティ値を真偽値に変換する。
     * '1'、'true'、'yes'のみをtrueとし、それ以外はfalseに変換される
     *
     * @access protected
     * @param string $s プロパティ値
     * @return boolean
     */
    protected function _changeToBool($s) {
        return preg_match('/^(1|true|yes)$/i', trim(nvl($s))) ? true : false;
    }
}
