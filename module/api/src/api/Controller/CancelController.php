<?php
namespace api\Controller;

use api\Application;
use api\classes\Service\Cancel\ServiceCancelConst;
use api\classes\Service\ServiceCancel;
use Coral\Coral\Controller\CoralControllerAction;

/**
 * 注文キャンセルAPIコントローラ
 */
class CancelController extends CoralApiController {
	/**
	 * 初期化処理
	 * @access protected
	 */
	protected function _init() {
		ini_set('display_errors', 1);
    }

	/**
	 * レスポンス送出初期化
	 *
	 * @access protected
	 */
    protected function prepareXmlResponse() {
//        $this->_helper->viewRenderer->setNoRender();
// 		  $this->getResponse()->setHeader( 'Content-Type', 'text/xml; charset=utf-8', true );
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
//             ServiceCancelConst::ENTERPRISE_ID => $this->getParams(ServiceCancelConst::ENTERPRISE_ID),
//             ServiceCancelConst::API_USER_ID   => $this->getParams(ServiceCancelConst::API_USER_ID),
	        ServiceCancelConst::ENTERPRISE_ID => $params[ServiceCancelConst::ENTERPRISE_ID],
		    ServiceCancelConst::API_USER_ID   => $params[ServiceCancelConst::API_USER_ID],
		    ServiceCancelConst::REQUEST_DATA  => array()
        );
		// 要求注文IDリスト構築
// 		$orderIdList = $this->getParams(ServiceCancelConst::ORDER_ID, array());
		$orderIdList = $params[ServiceCancelConst::ORDER_ID];
        if(!is_array($orderIdList)) {
            $orderIdList = array($orderIdList);
        }
        // 受付上限件数は100件
        $orderIdList = array_slice($orderIdList, 0, 100);

        // 要求キャンセル理由リスト構築
        $reasonList = $params[ServiceCancelConst::CANCEL_REASON];
        if(!is_array($reasonList)) {
            $reasonList = array($reasonList);
        }
        // 受付上限件数は100件
        $reasonList = array_slice($reasonList, 0, 100);


        // 注文IDの件数をベースに処理用データを構築
        foreach($orderIdList as $i => $oid) {
            if(strlen(trim((string)$oid))) {

                $data[ServiceCancelConst::REQUEST_DATA][] = array(
                    ServiceCancelConst::ORDER_ID => $oid,
                    ServiceCancelConst::ORDER_ID_BK => $oid,
                    ServiceCancelConst::CANCEL_REASON =>
                        (isset($reasonList[$i]) ? trim((string)$reasonList[$i]) : '')
                );
            }
        }
        // サービスへ処理を委譲
		$service = new ServiceCancel();
		$this->prepareXmlResponse();
		echo $service->invoke( $data );
		return $this->getResponse();
	}

}
