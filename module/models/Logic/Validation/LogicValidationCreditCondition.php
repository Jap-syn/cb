<?php
namespace models\Logic\Validation;

/**
 * TableCreditConditionへの永続化向けデータを検証するための検証ロジック。
 * このロジックで検証される項目は、外的要因でルールが変更されない
 * 'Category', 'Comment', 'Score'の3つのみで、その他の必須項目である
 * 'Cstring'については上位レイヤーで別途検証を行う必要がある
 */
class LogicValidationCreditCondition extends LogicValidationAbstract {
    /**
     * LogicValidationCreditConditionの新しいインスタンスを初期化する
     */
    public function __construct() {
        $this->addConfig(
            LogicValidationConfigBuilder::create('Category', true, '項目種別')
                ->addRule('Int')
                ->addRule(array('Between', 1, 8))
        )->addConfig(
            LogicValidationConfigBuilder::create('Comment', false, 'コメント')
                ->addRule(array('StringLength', 0, 4000))
        )->addConfig(
            LogicValidationConfigBuilder::create('Point', true, 'ポイント')
                ->addRule('Int')
                ->addRule(array('Between', -10000000, 10000000))
        );
    }
    /**
     * 入力検証を実行し結果を返す。
     *
     * @param array $input 入力データ。連想配列を必要とする
     * @return LogicValidationResult
     */
    public function validate(array $input) {
        return parent::validate($input);
    }
}
