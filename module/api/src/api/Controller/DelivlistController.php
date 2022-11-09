<?php
namespace api\Controller;

use api\Application;
use api\classes\Service\Delivlist\ServiceDelivlistConst;
use api\classes\Service\ServiceDelivlist;
use Coral\Coral\Controller\CoralControllerAction;

/**
 * 配送会社一覧取得APIコントローラ
 */
class DelivlistController extends CoralApiController {
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
            ServiceDelivlistConst::ENTERPRISE_ID => $params[ServiceDelivlistConst::ENTERPRISE_ID],
            ServiceDelivlistConst::API_USER_ID   => $params[ServiceDelivlistConst::API_USER_ID]
        );

        // サービスへ処理を委譲
        $service = new ServiceDelivlist();

        $this->prepareXmlResponse();
        echo $service->invoke( $data );

        return $this->getResponse();
    }

// Del By Yanase 20150218 マジックメソッド廃止
//    /**
//     * 存在しないアクションメソッドが指定された場合
//     * @param $method
//      * @param $args
//      */
//     public function __call($method, $args) {
//         throw new Exception(sprintf("method '%s' not implemented.", $method));
//     }
// Del By Yanase 20150218 マジックメソッド廃止
}
