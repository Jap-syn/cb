<?php
namespace member\classes;

/**
 * 履歴検索の検索条件を示すクラス
 *
 */
class SearchExpressionInfo {
	/**
	 * 検索条件式。SQLのWHERE句に使用する
	 *
	 * @var string
	 */
	protected $_expression;

	/**
	 * 人間が理解できる表現の検索条件
	 *
	 * @var string
	 */
	protected $_information;

	/**
	 * 検索条件式に使用するパラメータ
	 *
	 * @var array
	 */
	protected $_parameter;

	/**
	 * SearchExpressionInfoの新しいインスタンスを初期化する
	 *
	 * @param string $expression WHERE句に使用する検索条件式
	 * @param string $information 人間が理解できる表現形式の検索条件
	 * @param array $parameter 検索条件式に使用するパラメータ
	 */
	public function __construct($expression, $information, array $parameter,$asNumeric = false) {
		$this->_expression = $expression;
//		$this->_information = $information;
		$this->_information = $asNumeric ? sprintf('%s (数値として検索)', $information) : $information;
		$this->_parameter = $parameter;
	}

	/**
	 * 検索条件式を取得する
	 *
	 * @return string
	 */
	public function getExpression() {
		return $this->_expression;
	}

	/**
	 * 人間が理解できる表現形式の検索条件を取得する
	 *
	 * @return unknown
	 */
	public function getInformation() {
		return $this->_information;
	}

	/**
	 * 検索条件式に使用するパラメータを取得する
	 *
	 * @return string
	 */
	public function getParameter() {
	    return $this->_parameter;
	}

	/**
	 * __toString
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->getInformation();
	}
}