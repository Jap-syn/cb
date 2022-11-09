<?php
namespace api\Controller;

use api\Application;
use api\classes\Service\ServiceSitemod;
use api\classes\Service\Sitemod\ServiceSitemodConst;
use Coral\Coral\Controller\CoralControllerAction;

/**
 * サイト情報更新APIコントローラ
 */
class SitemodController extends CoralApiController {
	/**
	 * 初期化処理
	 * @access protected
	 */
	protected function _init() {
        Application::getInstance()->logger->debug(sprintf('SitemodController initialized'));
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
			ServiceSitemodConst::ENTERPRISE_ID	=> $params[ServiceSitemodConst::ENTERPRISE_ID],
			ServiceSitemodConst::API_USER_ID	=> $params[ServiceSitemodConst::API_USER_ID],
			ServiceSitemodConst::SITE_ID		=> $params[ServiceSitemodConst::SITE_ID]
        );
		// 認証パラメータ、必須パラメータ以外のパラメータを整備
		$other_params = array();
		$other_keys = array(
			ServiceSitemodConst::SITE_NAME,
			ServiceSitemodConst::SITE_URL,
			ServiceSitemodConst::PHONE
		);
		foreach($params as $key => $value) {
			if(in_array($key, $other_keys)) $other_params[$key] = $value;
		}
		$data[ServiceSitemodConst::REQ_PARAMS] = $other_params;

        // サービスへ処理を委譲
        $service = new ServiceSitemod();

		$this->prepareXmlResponse();
		echo $service->invoke( $data );

		return $this->getResponse();
	}
}
