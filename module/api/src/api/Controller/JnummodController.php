<?php
namespace api\Controller;

use Coral\Coral\Controller\CoralControllerAction;
use api\classes\Service\Jnummod\ServiceJnummodConst;
use api\classes\Service\ServiceJnummod;

/**
 * 伝票番号修正APIコントローラ
 */
class JnummodController extends CoralApiController {
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
			ServiceJnummodConst::ENTERPRISE_ID	=> $params[ServiceJnummodConst::ENTERPRISE_ID],
			ServiceJnummodConst::API_USER_ID	=> $params[ServiceJnummodConst::API_USER_ID],
			ServiceJnummodConst::ORDER_ID      => $params[ServiceJnummodConst::ORDER_ID],
			ServiceJnummodConst::DELIV_ID		=> $params[ServiceJnummodConst::DELIV_ID],
			ServiceJnummodConst::JOURNAL_NUM      => $params[ServiceJnummodConst::JOURNAL_NUM]
		);

        // サービスへ処理を委譲
        $service = new ServiceJnummod();

		$this->prepareXmlResponse();
		echo $service->invoke( $data );

		return $this->getResponse();
	}
}