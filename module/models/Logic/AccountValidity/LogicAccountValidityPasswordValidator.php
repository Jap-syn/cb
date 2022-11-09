<?php
namespace models\Logic\AccountValidity;

/**
 * パスワードが適切であるかを検証する検証ロジック
 */
class LogicAccountValidityPasswordValidator {
    /**
     * ルール情報キー定数：ルール（正規表現）
     * @var string
     */
    const RULEKEY_RULE = 'rule';

    /**
     * ルール情報キー定数：エラーメッセージ
     * @var string
     */
    const RULEKEY_ERRMSG = 'error_message';

    /**
     * パスワード検証処理が有効であるかのフラグ
     *
     * @static
     * @access protected
     * @var boolean
     */
    protected static $__validationEnabled = true;

    /**
     * パスワード検証処理が有効であるかの設定を取得する。
     * このメソッドがfalseを返す（＝検証処理無効）場合、すべてのインスタンスのisValid()メソッドは
     * 常にtrueを返す
     *
     * @static
     * @return boolean
     */
    public static function getValidationEnabled() {
        return self::$__validationEnabled;
    }
    /**
     * パスワード検証処理を有効にするかを設定する。
     * パスワード検証処理を無効に設定した場合、すべてのインスタンスのisValid()メソッドは
     * 常にtrueを返す
     *
     * @static
     * @param boolean $enabled 有効フラグ
     */
    public static function setValidationEnabled($enabled) {
        self::$__validationEnabled = $enabled ? true : false;
    }

    /**
     * システム規定のパスワード検証ルールインスタンスを取得する
     *
     * @static
     * @return LogicAccountValidityPasswordValidator
     */
    public static function getDefaultValidator() {
        $r_key = self::RULEKEY_RULE;
        $e_key = self::RULEKEY_ERRMSG;

        /** @var LogicAccountValidityPasswordValidator */
        $result = new self();
        $result
            ->addRule(array($r_key => '.*[a-zA-Z].*', $e_key => '英字を1文字以上含める必要があります'))
            ->addRule(array($r_key => '.*[0-9].*', $e_key => '数字を1文字以上含める必要があります'))
            ->addRule(array($r_key => '^.{7,}$', $e_key => '7文字以上で登録してください'));

        return $result;
    }

    /**
     * ルールリスト
     *
     * @access protected
     * @var array
     */
    protected $_rules;

    /**
     * 最後に実施した検証処理のエラー
     *
     * @access protected
     * @var array
     */
    protected $_lastErrors;

    /**
     * LogicAccountValidityPasswordValidatorの新しい
     * インスタンスを初期化する
     */
    public function __construct() {
        $this->_rules = array();
        $this->resetLastErrors();
    }

    /**
     * 検証ルールを追加する。
     * ルールは正規表現をベースとし、検証エラーメッセージを追加で登録できる。
     *
     * 例1：数字4文字、カスタムエラーメッセージなしのルール
     *   '^\d{4}$'
     *   ※：このルールに適合しない場合のエラーメッセージは「規則 '^\d{4}$' に一致しません'」となる
     * 例2：英字が含まれているかのルールで、カスタムエラーメッセージは「英字を含める必要があります」としたルール
     *   array('rule' => '[a-zA-Z]', 'error_message' => '英字を含める必要があります')
     *
     * @param string | array $rule 追加する検証ルール
     * @return LogicAccountValidityPasswordValidator このインスタンス
     */
    public function addRule($rule) {
        $this->_rules[] = $this->fixRule($rule);
        return $this;
    }

    /**
     * 指定の値を現在のルールセットで検証する。
     * このメソッドは検証結果のみを返すので、エラーの内容が必要な場合は
     * getLastErrors()でエラーメッセージ一覧を取得する必要がある
     *
     * @param string $value 検証する値
     * @param null | string $userId 検証するパスワードと対となるユーザID。省略可能
     * @return boolean 検証結果
     */
    public function isValid($value, $userId = null) {
        $this->resetLastErrors();

        // パスワード検証処理が有効な場合のみ検証を実施
        if(self::getValidationEnabled()) {
            // ユーザIDが指定されていた場合はユーザIDとの一致確認を行う
            if($userId != null) {
                if(strtoupper($value) == strtoupper($userId)) {
                    $this->_lastErrors[] = 'ログインIDをパスワードに設定することはできません';
                }
            }

            // ユーザIDチェックでエラーでない場合のみルールベース検証を実施
            if(empty($this->_lastErrors)) {
                foreach($this->_rules as $rule) {
                    if(!mb_ereg_match($rule[self::RULEKEY_RULE], $value)) {
                        $this->_lastErrors[] = $rule[self::RULEKEY_ERRMSG];
                    }
                }
            }
        }

        // エラー件数の有無がそのまま検証結果
        return empty($this->_lastErrors);
    }

    /**
     * 最後に実施した検証処理で発生したすべてのエラーメッセージを取得する
     *
     * @return array
     */
    public function getLastErrors() {
        return $this->_lastErrors;
    }

    /**
     * ルールセットに追加する検証ルールを整備する
     *
     * @access protected
     * @var string | array $orgRule 追加しようとしている検証ルール
     * @return array 整備された検証ルール
     */
    protected function fixRule($orgRule) {
        if(!is_array($orgRule)) {
            $orgRule = nvl($orgRule, '.*');
            return array(
                self::RULEKEY_RULE => $orgRule,
                self::RULEKEY_ERRMSG => sprintf("規則 '%s' に一致しません", $orgRule)
            );
        }
        $rule = array();
        foreach($orgRule as $key => $value) {
            switch($key) {
                case self::RULEKEY_RULE:
                case self::RULEKEY_ERRMSG:
                    $rule[$key] = $value;
                    break;
            }
        }
        return $rule;
    }

    /**
     * 最終エラー情報をリセットする
     *
     * @access protected
     */
    protected function resetLastErrors() {
        $this->_lastErrors = array();
    }
}
