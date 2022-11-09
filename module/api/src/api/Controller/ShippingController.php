<?php
namespace api\Controller;

use api\Application;
use api\classes\Service\ServiceShipping;
use api\classes\Service\Shipping\ServiceShippingConst;
use Coral\Coral\Controller\CoralControllerAction;

/**
 * 伝票番号登録APIコントローラ
 */
class ShippingController extends CoralApiController {
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
            ServiceShippingConst::ENTERPRISE_ID   => $params[ServiceShippingConst::ENTERPRISE_ID],
            ServiceShippingConst::API_USER_ID     => $params[ServiceShippingConst::API_USER_ID],
            ServiceShippingConst::ORDER_ID        => $params[ServiceShippingConst::ORDER_ID],
            ServiceShippingConst::DELIV_ID        => $params[ServiceShippingConst::DELIV_ID],
            ServiceShippingConst::JOURNAL_NUMBER  => $params[ServiceShippingConst::JOURNAL_NUMBER]
        );
        if(isset($data[ServiceShippingConst::ORDER_ID]) && strlen(trim($data[ServiceShippingConst::ORDER_ID])) > 0) {
            $data[ServiceShippingConst::ORDER_ID] = strtoupper(trim($data[ServiceShippingConst::ORDER_ID]));
        }

        // 伝票番号登録API実行ログ
        $insertSql = <<<EOQ
INSERT INTO T_ShippingApiExecLog(OrderId,EnterpriseId,RegistDate)
        VALUES(:OrderId, :EnterpriseId, :RegistDate)
EOQ;
        $ri = Application::getInstance()->dbAdapter->query($insertSql)->execute(array(':OrderId' => $data[ServiceShippingConst::ORDER_ID],':EnterpriseId' => $data[ServiceShippingConst::ENTERPRISE_ID],':RegistDate' => date('Y-m-d H:i:s')));

        // 伝票登録済み(但しサイト.自動伝票番号登録フラグ＝1：与信OK確定で自動登録、に限定)の場合は、更新処理を呼出す(エラーメッセージ体系は[E04:登録])
        $sql = <<<EOQ
SELECT o.OrderSeq
FROM   T_Order o
       INNER JOIN T_PayingAndSales pas ON (pas.OrderSeq = o.OrderSeq)
       INNER JOIN T_Site sit ON (sit.SiteId = o.SiteId)
WHERE  (sit.AutoJournalIncMode = 1 OR sit.ClaimAutoJournalIncMode = 1)
AND    o.OrderId = :OrderId
EOQ;
        $ri = Application::getInstance()->dbAdapter->query($sql)->execute(array(':OrderId' => $data[ServiceShippingConst::ORDER_ID]));
        if ($ri->count() > 0) {
            $service = new \api\classes\Service\ServiceJnummod2 ();
            $this->prepareXmlResponse();
            echo $service->invoke( $data );
            return $this->getResponse();
        }

        // サービスへ処理を委譲
        $service = new ServiceShipping();

        $this->prepareXmlResponse();
        echo $service->invoke( $data );

        return $this->getResponse();
    }

// Del By Yanase 20150223 マジックメソッド廃止
//     /**
//      * 存在しないアクションメソッドが指定された場合
//      * @param $method
//      * @param $args
//      */
//     public function __call($method, $args) {
//         throw new Exception(sprintf("method '%s' not implemented.", $method));
//     }
// Del By Yanase 20150223 マジックメソッド廃止
}