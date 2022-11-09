<?php
namespace api\classes\Service\SelfBilling;

use api\classes\Service\ServiceSelfBilling;
use DOMDocument;
use DOMXPath;
use DOMNode;

class ServiceSelfBillingXmlParser {
    /**
     * Parameters要素の子要素を必要とする同梱APIコマンド名を取得する
     *
     * @static
     * @return array
     */
    public static function getParameterNeedCommands() {
        return array(
            ServiceSelfBilling::CMD_CAN_ENQUEUE,
            ServiceSelfBilling::CMD_ENQUEUE,
            ServiceSelfBilling::CMD_IS_TARGET,
            ServiceSelfBilling::CMD_FETCH_TARGET_CONDITIONS,
            ServiceSelfBilling::CMD_PROCESSED
        );
    }

    /**
     * リクエストXMLを処理するためのDOMDocument
     *
     * @access protected
     * @var DOMDocument
     */
	protected $_dom;

    /**
     * パラメータに対応するノードを探索するためのDOMXPath
     *
     * @access protected
     * @var DOMXPath
     */
	protected $_xpath;

    /**
     * リクエストのXMLソースを指定して、ServiceSelfBillingXmlParserの
     * 新しいインスタンスを初期化する
     *
     * @param string $xml_source リクエストされたXMLソース文字列
     */
    public function __construct($xml_source)
    {
        $this->loadXml($xml_source);
    }

    /**
     * 指定のXMLソースを読み込みパラメータ展開の準備を実行する
     *
     * @param string $xml_source XMLソース文字列
     * @return ServiceSelfBilingXmlParser このインスタンス
     */
    public function loadXml($xml_source) {
        $this->_dom = @DOMDocument::loadXML($xml_source);

        // loadに失敗した場合にはfalseを返却
        if($this->_dom == false) throw new \Exception(sprintf('%s#loadXml() ERROR: %s', get_class($this), "XML Parse Error"));

        $this->_xpath = new DOMXPath($this->_dom);

        // Xpathの生成に失敗した場合にはfalseを返却
        if($this->_xpath == false) throw new \Exception(sprintf('%s#loadXml() ERROR: %s', get_class($this), "DOMXPath Error"));

        return $this;
    }

    /**
     * 現在のドキュメントからパラメータの展開を実行する
     *
     * @return array 展開されたパラメータを格納する連想配列
     */
	public function parse() {
		$result = array(
			'Auth' => $this->parseAuth(),
			'Action' => $this->parseAction(),
			'Parameters' => $this->parseParameters()
		);

        // Parametersの子要素を必要としないアクションの場合はParametersを空にする
		if(!in_array($result['Action'], self::getParameterNeedCommands())) {
			$result['Parameters'] = array();
		}
		return $result;
	}

    /**
     * 現在のドキュメントから認証パラメータを抽出する
     *
     * @access protected
     * @return array 認証パラメータを格納した連想配列
     */
	protected function parseAuth() {
		$results = array();

		$path_prefix = '/Billing/Auth';
		$keys = array('EnterpriseId', 'ApiUserId', 'AccessToken');
		foreach($keys as $key) {
			$path = join('/', array($path_prefix, $key));
			foreach($this->_xpath->query($path) as $node) {
				if($node->nodeType == XML_ELEMENT_NODE) {
					$results[$key] = $node->nodeValue;
				}
			}
		}
		return $results;
	}

    /**
     * 現在のドキュメントからアクション種別を抽出する
     *
     * @access protected
     * @return string | null アクション種別
     */
	protected function parseAction() {
		foreach($this->_xpath->query('/Billing/Action') as $node) {
			if($node->nodeType == XML_ELEMENT_NODE) return $node->nodeValue;
		}
		return null;
	}

    /**
     * 現在のドキュメントからパラメータリストを抽出する
     *
     * @access protected
     * @return array
     */
	protected function parseParameters() {
		$results = array(
			'Parameter' => array()
		);
		foreach($this->_xpath->query('/Billing/Parameters/Parameter') as $node) {
			$results['Parameter'][] = $this->parseParameter($node);
		}
		return $results;
	}

    /**
     * 指定のParameter要素から必要なパラメータを抽出する
     *
     * @access protected
     * @param DOMNode $paramsNode /Billing/Parameters/Parameterノード
     * @return array
     */
	protected function parseParameter(DOMNode $paramsNode) {
		$results = array();
		foreach(array('OrderId', 'Mode', 'SmartFlg') as $key) {
			foreach($this->_xpath->query($key, $paramsNode) as $node) {
				if($node->nodeType == XML_ELEMENT_NODE) {
					$results[$key] = $node->nodeValue;
				}
			}
		}
		return $results;
	}
}
