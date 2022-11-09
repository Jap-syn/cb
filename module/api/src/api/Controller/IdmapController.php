<?php
namespace api\Controller;

use api\Application;
use api\classes\Service\ServiceIdmap;
use api\classes\Service\Idmap\ServiceIdmapConst;
use Coral\Coral\Controller\CoralControllerAction;

/**
 * 注文ID変換APIコントローラ
 */
class IdmapController extends CoralApiController {
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
            ServiceIdmapConst::ENTERPRISE_ID    => $params[ServiceIdmapConst::ENTERPRISE_ID],
            ServiceIdmapConst::API_USER_ID    => $params[ServiceIdmapConst::API_USER_ID],
            ServiceIdmapConst::ENT_ORDER_ID    => array()
        );
        // 要求Ent_OrderIdリスト構築
        $entOrderIdList = $params[ServiceIdmapConst::ENT_ORDER_ID];
        if(!is_array($entOrderIdList)) {
            $entOrderIdList = array($entOrderIdList);
        }
        // Ent_OrderId受付上限数は100
        $entOrderIdList = array_slice($entOrderIdList, 0, 100);

        foreach($entOrderIdList as $entOrderId) {
            if(strlen(trim($entOrderId))) {
                $data[ServiceIdmapConst::ENT_ORDER_ID][] = trim($entOrderId);
            }
        }

        // サービスへ処理を委譲
        $service = new ServiceIdmap();

        $this->prepareXmlResponse();
        echo $service->invoke( $data );

        return $this->getResponse();
    }

// Del By Yanase 20150219 マジックメソッド廃止
//     /**
//      * 存在しないアクションメソッドが指定された場合
//      * @param $method
//      * @param $args
//      */
//     public function __call($method, $args) {
//         throw new Exception(sprintf("method '%s' not implemented.", $method));
//     }
// Del By Yanase 20150219 マジックメソッド廃止
}