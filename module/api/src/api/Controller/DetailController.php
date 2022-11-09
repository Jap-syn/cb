<?php
namespace api\Controller;

use Coral\Coral\Controller\CoralControllerAction;
use api\classes\Service\ServiceDetail;
use api\classes\Service\Detail\ServiceDetailConst;
use api\Application;

/**
 * 注文状況取得APIコントローラ
 */
class DetailController extends CoralApiController {
	/**
	 * 初期化処理
	 * @access protected
	 */
	protected function _init() {
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

        // 入力パラメータ整備
        $data = array(
            ServiceDetailConst::ENTERPRISE_ID   => $params[ServiceDetailConst::ENTERPRISE_ID],
            ServiceDetailConst::API_USER_ID     => $params[ServiceDetailConst::API_USER_ID],
            ServiceDetailConst::ORDER_ID      => array(),
            ServiceDetailConst::ENT_ORDER_ID  => array()
        );

		// 要求OrderIdリスト構築
        $orderIdList = $params[ServiceDetailConst::ORDER_ID];
        if(!is_array($orderIdList)) {
            $orderIdList = array($orderIdList);
        }
        // OrderId受付上限数は100
        $orderIdList = array_slice($orderIdList, 0, 100);

        foreach($orderIdList as $orderId) {
            if(strlen(trim($orderId))) {
                $data[ServiceDetailConst::ORDER_ID][] = strtoupper(trim($orderId));
            }
        }

        // 要求Ent_OrderIdリスト構築
        $entOrderIdList = $params[ServiceDetailConst::ENT_ORDER_ID];

        if(!is_array($entOrderIdList)) {
            $entOrderIdList = array($entOrderIdList);
        }
        // OrderId受付上限数は100
        $entOrderIdList = array_slice($entOrderIdList, 0, 100);

        foreach($entOrderIdList as $orderId) {
            if(strlen(trim($orderId))) {
                $data[ServiceDetailConst::ENT_ORDER_ID][] = trim($orderId);
            }
        }

        // サービスへ処理を委譲
        $service = new ServiceDetail();

		$this->prepareXmlResponse();

		echo $service->invoke( $data );

		try {
		    $app = Application::getInstance();
		    if ( $app->dbAdapter->getDriver()->getConnection()->isConnected() ) {
		        $app->dbAdapter->getDriver()->getConnection()->disconnect();
		    }
		} catch (\Exception $e) {
		}
		return $this->getResponse();
	}

//	/**
//	 * 存在しないアクションメソッドが指定された場合
//	 * @param $method
//	 * @param $args
//	 */
//	public function __call($method, $args) {
//		throw new Exception(sprintf("method '%s' not implemented.", $method));
//	}
}