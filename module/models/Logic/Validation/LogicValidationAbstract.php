<?php
namespace models\Logic\Validation;
// require_once 'Zend/Filter/Input.php';
// require_once 'Logic/Validation/Result.php';
// require_once 'Logic/Validation/ConfigBuilder.php';

use Coral\Coral\CoralValidate;
/**
 * 入力検証ロジックの基底抽象クラス
 */
abstract class LogicValidationAbstract {
    /**
     * Zend_Filter_Inputを初期化するための検証ルールの配列
     *
     * @access protected
     * @var array
     */
    protected $_validators = array();

    /**
     * Zend_Filter_Inputを初期化するためのフィルタルールの配列
     *
     * @access protected
     * @var array
     */
    protected $_filters = array('*' => array('StringTrim'));

    /**
     * バリデータクラスを探索するための、追加のネームスペース
     *
     * @access protected
     * @var array
     */
    protected $_additional_ns = array('NetB_Validate', 'Coral_Validate');

    /**
     * LogicValidationConfigBuilderから検証ルールを追加する
     *
     * @param Logic_Validation_ConfigBuilder $builder 検証ルール設定ビルダ
     * @return Logic_Validation_Abstract このインスタンス自身
     */
    public function addConfig(LogicValidationConfigBuilder $builder) {
        $this->_validators[$builder->getKey()] = $builder->build();
        return $this;
    }

    /**
     * 入力検証を実行し結果を返す。
     *
     * @param array $input 入力データ。連想配列を必要とする
     * @return Logic_Validation_Result
     */
    public function validate(array $input) {
// 		$validate = new Zend_Filter_Input($this->_filters, $this->_validators, $input);
//         // ネームスペースを追加
//         foreach($this->_additional_ns as $ns) {
//             $validate->addNamespace($ns);
//         }
// 		$validate->setOptions(array(
// 			Zend_Filter_Input::MISSING_MESSAGE => "'%rule%' は入力必須です"
// 		));

//         // 検証を実行
//         $isValid = $validate->isValid();

//         // 結果作成開始
//         $result = new Logic_Validation_Result();
//         // オリジナルの入力とフィルタ適用済みデータを格納
//         $result
//             ->setOriginalData($input)
//             ->setFilteredData($validate->getUnescaped());

//         // あとは検証結果ごとのプロパティをセットする
// 		if($isValid) {
//             $result
//                 ->setErrors(array())
//                 ->setInvalidKeys(array());
// 		} else {
// 			$errors = array();
// 			foreach($validate->getMessages() as $key => $value) {
// 				$errors = array_merge($errors, $value);
// 			}
//             $result
//                 ->setErrors($errors)
//                 ->setInvalidKeys(array_keys($validate->getInvalid()));
// 		}

//         return $result;
    }

}
