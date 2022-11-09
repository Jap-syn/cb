<?php
namespace api\Controller;

use api\Application;
use api\classes\Service\ServicePayeasy;
use api\classes\Service\Payeasy\ServicePayeasyConst;
use Coral\Coral\Controller\CoralControllerAction;

/**
 * 伝票番号登録APIコントローラ
 */
class PayeasyController extends CoralApiController {
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

        // 入力パラメータ整備（一旦全項目を保存しておく）
        $data = array(
            ':p_ver'     => $params['p_ver'],
            ':stdate'    => $params['stdate'],
            ':stran'     => $params['stran'],
            ':bkcode'    => $params['bkcode'],
            ':shopid'    => $params['shopid'],
            ':cshopid'   => $params['cshopid'],
            ':amount'    => $params['amount'],
            ':mbtran'    => $params['mbtran'],
            ':bktrans'   => $params['bktrans'],
            ':tranid'    => mb_convert_encoding($params['tranid'], 'UTF-8', 'sjis-win'),
            ':ddate'     => $params['ddate'],
            ':tdate'     => $params['tdate'],
            ':rsltcd'    => $params['rsltcd'],
            ':rchksum'   => $params['rchksum'],
        );

        // 登録API実行
        $insSql  = "INSERT INTO T_PayeasyReceived (";
        $insSql .= "p_ver, stdate, stran, bkcode, shopid, cshopid, amount, mbtran, bktrans, tranid, ddate, tdate, rsltcd, rchksum";
        $insSql .= ") VALUES(";
        $insSql .= ":p_ver, :stdate, :stran, :bkcode, :shopid, :cshopid, :amount, :mbtran, :bktrans, :tranid, :ddate, :tdate, :rsltcd, :rchksum";
        $insSql .= ")";

        $ri = Application::getInstance()->dbAdapter->query( $insSql )->execute( $data );

        // サービスへ処理を委譲
        $service = new ServicePayeasy();

        $this->prepareXmlResponse();
        echo $service->invoke( $data );

        return $this->getResponse();
    }

}