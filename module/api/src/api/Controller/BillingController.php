<?php
namespace api\Controller;

use api\Application;
use Coral\Coral\Controller\CoralControllerAction;
use api\classes\Service\SelfBilling\ServiceSelfBillingConst;
use api\classes\Service\SelfBilling\ServiceSelfBillingXmlParser;
use api\classes\Service\ServiceSelfBilling;

/**
 * 同梱APIコントローラ
 */
class BillingController extends CoralApiController {
    /**
     * 初期化処理
     * @access protected
     */
    protected function _init()
    {
        ini_set('display_errors', 1);
    }

    /**
     * レスポンス送出初期化
     *
     * @access protected
     */
    protected function prepareXmlResponse()
    {
        $res = $this->getResponse();
        $res->getHeaders()->addHeaderLine( 'Content-Type', 'text/xml; charset=utf-8' );
    }

    /**
     * Rest受け付け用IF
     */
    public function xmlAction()
    {
        $params = $this->getParams();

        $decode_data = array();
        if (isset($params['token'])) {
            if (parent::decodeToken($params['token'], $ret, $ret2)) {
                $decode_data = $ret2;
            }
        }
        else {
            // XMLの取得
            $xml = file_get_contents('php://input');
            // XMLの展開
            try {
                $parser = new ServiceSelfBillingXmlParser($xml);
                $decode_data = $parser->parse();

                $redirectUrl = parent::getRedirectUrlForBilling('/' . $this->getControllerName() . '/' . $this->getActionName(), $decode_data);
                if ($redirectUrl != null) {
                    return $this->redirect()->toUrl($redirectUrl);
                }
            } catch(\Exception $e) {
                Application::getInstance()->logger->info(sprintf('%s#xmlAction() ERROR: %s', get_class($this), $e->getMessage()));
                $authdata = array();

                //
                $keys = array('EnterpriseId', 'ApiUserId', 'AccessToken');
                foreach($keys as $key) {
                    $authdata[$key] = "";
                }
                $decode_data['Auth'] = $authdata;
            }
        }

        // 設定情報のセット
   	    $auth = $decode_data['Auth'];
        $action = $decode_data['Action'];
        $parameters = $decode_data['Parameters'];

        $param = !empty($parameters['Parameter']) ? $parameters['Parameter'] : array();

        $data = array(
            ServiceSelfBillingConst::ENTERPRISE_ID => $auth['EnterpriseId'],
            ServiceSelfBillingConst::API_USER_ID => $auth['ApiUserId'],
            ServiceSelfBillingConst::ACCESS_TOKEN => $auth['AccessToken'],
            ServiceSelfBillingConst::ACTION => $action,
            ServiceSelfBillingConst::PARAM => $param,
        );

        // サービスへ処理を委譲
        $service = new ServiceSelfBilling();
        $this->prepareXmlResponse();
        echo $service->invoke( $data );
        return $this->getResponse();
    }
}