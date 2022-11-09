<?php
namespace api\Controller;

use api\Application;
use Coral\Coral\Controller\CoralControllerAction;
use Zend\Http\Client;
use Zend\Http\Request;
use api\classes\Service\Modify\ServiceModifyConst;
use api\classes\Service\ServiceModify;
use api\classes\Service\Modify\ServiceParameterUtility;

/**
 * 注文修正APIコントローラ
 */
class ModifyController extends CoralApiController {
	/**
	 * 初期化処理
	 * @access protected
	 */
	protected function _init() {
Application::getInstance()->logger->debug(sprintf('ModifyController initialized'));
    }

	/**
	 * レスポンス送出初期化
	 *
	 * @access protected
	 */
    protected function prepareXmlResponse() {
        $res = $this->getResponse();
        $res->getHeaders()->addHeaderLine( 'Content-Type', 'text/xml; charset=utf-8' );
    }

	/**
	 * Rest受け付け用IF
	 */
	public function restAction() {
        $params = $this->getParams();

        if (isset($params['token'])) {
            if (parent::decodeToken($params['token'], $ret, $ret2)) {
                unset($params['token']);
                $params = array_merge($params, $ret);
            }
        }
        else {
            $redirectUrl = parent::getRedirectUrl('/' . $this->getControllerName() . '/' . $this->getActionName(), $this->getPureParams());
            if ($redirectUrl != null) {
                return $this->redirect()->toUrl($redirectUrl);
            }
        }

		$util = new ServiceParameterUtility();

        // 入力パラメータ整備
        $data = array(
            ServiceModifyConst::ENTERPRISE_ID	=> $params[ServiceModifyConst::ENTERPRISE_ID],
            ServiceModifyConst::API_USER_ID		=> $params[ServiceModifyConst::API_USER_ID],
            ServiceModifyConst::ORDER_ID			=> $params[ServiceModifyConst::ORDER_ID],
			ServiceModifyConst::REQ_PARAMS		=> $util->parseAll($params)
        );
		if(isset($data[ServiceModifyConst::ORDER_ID]) && strlen(trim($data[ServiceModifyConst::ORDER_ID])) > 0) {
			$data[ServiceModifyConst::ORDER_ID] = strtoupper($data[ServiceModifyConst::ORDER_ID]);
		}

        // サービスへ処理を委譲
        $service = new ServiceModify();

		$this->prepareXmlResponse();
		echo $service->invoke( $data );

		return $this->getResponse();
	}

// 	/**
// 	 * 存在しないアクションメソッドが指定された場合
// 	 * @param $method
// 	 * @param $args
// 	 */
// 	public function __call($method, $args) {
// 		throw new Exception(sprintf("method '%s' not implemented.", $method));
// 	}
}
