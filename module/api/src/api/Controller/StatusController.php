<?php
namespace api\Controller;

use api\Application;
use api\classes\Service\ServiceStatus;
use api\classes\Service\Status\ServiceStatusConst;
use Coral\Coral\Controller\CoralControllerAction;

/**
 * 与信状況問い合わせAPIコントローラ
 */
class StatusController extends CoralApiController {
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
//        $this->_helper->viewRenderer->setNoRender();
//        $this->getResponse()->setHeader( 'Content-Type', 'text/xml; charset=utf-8', true );
        $res = $this->getResponse();
        $res->getHeaders()->addHeaderLine( 'Content-Type', 'text/xml; charset=utf-8' );
    }

    /**
     * Rest受け付け用IF
     */
    public function restAction() {
        $prams = $this->getParams();

        if (isset($prams['token'])) {
            if (parent::decodeToken($prams['token'], $ret, $ret2)) {
                unset($prams['token']);
                $prams = array_merge($prams, $ret);
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
            ServiceStatusConst::ENTERPRISE_ID    => $prams[ServiceStatusConst::ENTERPRISE_ID],
            ServiceStatusConst::API_USER_ID    => $prams[ServiceStatusConst::API_USER_ID],
            ServiceStatusConst::ORDER_ID        => array()
        );
        // 要求OrderIdリスト構築
        $orderIdList = $prams[ServiceStatusConst::ORDER_ID];
        if(!is_array($orderIdList)) {
            $orderIdList = array($orderIdList);
        }
        // OrderId受付上限数は100
        $orderIdList = array_slice($orderIdList, 0, 100);

        foreach($orderIdList as $orderId) {
            if(strlen(trim($orderId))) {
                $orderId = trim($orderId);
                $data[ServiceStatusConst::ORDER_ID][] = strtoupper($orderId);
            }
        }

        // サービスへ処理を委譲
        $service = new ServiceStatus();

        $this->prepareXmlResponse();
        echo $service->invoke( $data );

        return $this->getResponse();
    }

// Del By Yanase 20150220 マジックメソッド廃止
//     /**
//      * 存在しないアクションメソッドが指定された場合
//      * @param $method
//      * @param $args
//      */
//     public function __call($method, $args) {
//         throw new Exception(sprintf("method '%s' not implemented.", $method));
//     }
// Del By Yanase 20150220 マジックメソッド廃止
}