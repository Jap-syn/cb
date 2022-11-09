<?php
namespace models\Logic\Validation;

use Zend\Validator\StringLength;
use Zend\Validator\Between;
use Zend\Validator\Date;
use Coral\Base\Validate\BaseValidateZipcode;
use Coral\Base\Validate\BaseValidatePhoneNumber;

/**
 * Zend_Filter_Inputによる入力検証を行うためのセットアップ用連想配列を
 * 構築するためのビルダー。
 * 基本的に入力キーと必須であるかのフラグ、入力キーに対応する表示名を以て初期化し、
 * バリデータルール名を追加していくことで利用できる
 */
class LogicValidationConfigBuilder {
    /**
     * LogicValidationConfigBuilderのインスタンスを作成するファクトリメソッド
     *
     * @param string $key 入力キー。このビルダが構築する検証ルールのルール名となる
     * @param null|bool $required このルールが入力必須ルールであるかを設定する
     * @param null|string $friendlyName 入力キーに対応する表示名。エラーメッセージの自動生成時に利用される
     * @return LogicValidationConfigBuilder 新しいLogicValidationConfigBuilderのインスタンス
     */
    public static function create($key, $required = false, $friendlyName = null) {
        return new self($key, $required, $friendlyName);
    }

    /**
     * 入力キー
     *
     * @access protected
     * @var string
     */
    protected $_key;

    /**
     * 入力必須フラグ
     *
     * @access protected
     * @var bool
     */
    protected $_required;

    /**
     * 入力キーと対応する表示名
     *
     * @access protected
     * @var string
     */
    protected $_friendlyName;

    /**
     * このインスタンスに登録されたルール
     *
     * @access protected
     * @var array
     */
    protected $_rules;

    /**
     * 入力キー（検証ルール名）と必須フラグ、オプションの表示名を指定して、
     * LogicValidationConfigBuilderの新しいインスタンスを初期化する
     *
     * @param string $key 入力キー。このビルダが構築する検証ルールのルール名となる
     * @param null|bool $required このルールが入力必須ルールであるかを設定する
     * @param null|string $friendlyName 入力キーに対応する表示名。エラーメッセージの自動生成時に利用される
     * @return LogicValidationConfigBuilder 新しいLogicValidationConfigBuilderのインスタンス
     */
    public function __construct($key, $required = false, $friendlyName = null) {
        $this
            ->setKey($key)
            ->setRequired($required)
            ->setFriendlyName($friendlyName);
    }

    /**
     * 入力キー（検証ルール名）を取得する
     *
     * @return string
     */
    public function getKey() {
        return $this->_key;
    }
    /**
     * 入力キー（検証ルール名）を設定する
     *
     * @param string $key 入力キー
     * @return LogicValidationConfigBuilder このインスタンス自身
     */
    public function setKey($key) {
        $key = trim((string)$key);
        if(! strlen($key)) throw new \Exception('invalid key specified');
        $this->_key = $key;
        return $this;
    }

    /**
     * 入力キーと対応する表示名を取得する。
     * 適切な表示名が設定されていない場合、このメソッドはgetKey()と同じ結果を返す
     *
     * @return string
     */
    public function getFriendlyName() {
        $result = trim((string)($this->_friendlyName));
        return strlen($result) > 0 ? $result : $this->getKey();
    }
    /**
     * 入力キーと対応する表示名を設定する
     *
     * @param string $friendlyName 表示名
     * @return LogicValidationConfigBuilder このインスタンス自身
     */
    public function setFriendlyName($friendlyName) {
        $this->_friendlyName = trim((string)$friendlyName);
        return $this;
    }

    /**
     * このルールが入力必須であるかを取得する
     *
     * @return bool
     */
    public function getRequired() {
        return $this->_required ? true : false;
    }
    /**
     * このルールが入力必須であるかを設定する
     *
     * @param bool $required 入力必須ルールの場合はtrue、それ以外はfalseを指定する
     * @return LogicValidationConfigBuilder このインスタンス自身
     */
    public function setRequired($required) {
        $this->_required = $required ? true : false;
        return $this;
    }

    /**
     * 検証ルールを追加する。
     * 指定するルールは[指定の検証ルール向けにエラーメッセージテンプレートを生成する]に倣い、バリデータクラスから
     * ネームスペースを除いた文字列をベースに指定し、オプションで
     * エラーメッセージ（またはエラーメッセージの連想配列）を指定することができる。
     *
     * @param string|array $rule 検証ルール名またはセットアップパラメータ
     * @param null|string|array $message 検証エラーメッセージのテンプレート。省略時は$ruleの内容に従って自動生成される
     * @return LogicValidationConfigBuilder このインスタンス自身
     */
    public function addRule($rule, $message = null) {
        if($message === null) $message = $this->createMessage($rule);
        $this->_rules[] = array('rule' => $rule, 'message' => $message);
        return $this;
    }

    /**
     * フィルタの初期化に利用できる検証ルールセットアップ連想配列を生成する
     *
     * @return array
     */
    public function build() {
        $config = array(
            'fields' => $this->getKey(),
            'breakChainOnFailure' => true,
            'presence' => $this->getRequired() ? 'required' : 'optional',
            'allowEmpty'=> ! $this->getRequired()
        );

        if($this->getRequired()) {
            $this->_rules = array_merge(
                array(
                    array('rule' => 'Required', 'message' => $this->createMessage('Required'))
                ),
                $this->_rules );
        }

        $messages = array();
        foreach($this->_rules as $rule_row) {
            $config[] = $rule_row['rule'];
            $msg = $rule_row['message'];
            if(! is_array($msg)) {
                $messages[] = preg_replace('/%NAME%/', $this->getFriendlyName(), $msg);
            } else {
                foreach($msg as $key => &$value) {
                    $value = preg_replace('/%NAME%/', $this->getFriendlyName(), $value);
                }
                $messages[] = $msg;
            }
        }
        $config['messages'] = $messages;

        return $config;
    }

    /**
     * 指定の検証ルール向けにエラーメッセージテンプレートを生成する
     *
     * @param string|array 検証ルール
     * @return string|array エラーメッセージテンプレート
     */
    protected function createMessage($rule) {

        $rule_name = is_string($rule) ? $rule : $rule[0];
        $result = "'%NAME%' に誤りがあります";
        switch($rule_name) {
            case 'StringLength':
                $msg = $rule[1] > 0 ?
                sprintf('%s文字以上%s文字以内で入力してください', $rule[1], $rule[2]) :
                sprintf('%s文字以内で入力してください', $rule[2]);
                $result = array(
                        StringLength::TOO_SHORT => "'%NAME%' は" . $msg,
                        StringLength::TOO_LONG => "'%NAME%' は" . $msg );
                break;
            case 'Required':
            case 'NotEmpty':
                $result = "'%NAME%' は入力必須です";
                break;
            case 'Between':
                $result = array(
                    Between::NOT_BETWEEN => "'%NAME%' の入力値が範囲外です"
                );
                break;
            case 'Int':
                $result = "'%NAME%' は整数で入力する必要があります";
                break;
            case 'Regex':
                $result = "'%NAME%' の入力パターンが不正です";
                break;
            case 'Date':
                $result = array(
                    Date::INVALID => "'%NAME%' は日付時刻として正しくありません",
                    Date::FALSEFORMAT => "'%NAME%' は日付時刻として正しくありません" );
                break;
            case 'PhoneNumber':
                $result = array(
                BaseValidatePhoneNumber::INVALID => "'%NAME%' は電話番号の形式として正しくありません",
                BaseValidatePhoneNumber::TOO_SHORT => "'%NAME%' は短すぎます",
                BaseValidatePhoneNumber::TOO_LONG => "'%NAME%' は長すぎます" );
                break;
            case 'ZipCode':
                $result = array(
                    BaseValidateZipcode::INVALID => "'%NAME%' は郵便番号の形式として正しくありません"
                );
                break;
        }
        return $result;
    }
}
