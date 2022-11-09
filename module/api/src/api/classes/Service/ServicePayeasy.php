<?php
namespace api\classes\Service;

use api\Application;
use api\classes\Service\ServiceAbstract;
use api\classes\Service\Payeasy\ServicePayeasyConst;
use api\classes\Service\Response\ServiceResponsePayeasy;
use api\classes\Service\ServiceException;
use models\Logic\MergeOrder\LogicMergeOrderHelper;
use zend\Db\ResultSet\ResultSet;
use models\Logic\LogicDeliveryMethod;
use models\Table\TableUser;

/**
 * Payeasy入金登録サービスクラス
 */
class ServicePayeasy extends ServiceAbstract {
    /**
     * Payeasy入金登録APIのサービスID
     * @var string
     */
    protected $_serviceId = "12";

    /**
     * 初期化処理
     *
     * @access protected
     */
    protected function init() {
        // サイトIDチェックは行わない
        $this->_checkSiteId = false;

        // レスポンスを初期化
        $this->_response = new ServiceResponsePayeasy();

        // 登録データをレスポンスへ反映
        $this->_response->orderId = $this->orderId;

        // ログ出力
        Application::getInstance()->logger->info(
            get_class($this) . '#init() ' .
            join(', ', array(
                sprintf('RemoteAddr: %s', f_get_client_address())
            )) );
    }

    /**
     * 入力に対する検証を行う
     *
     * @access protected
     * @return boolean 検証結果
     */
    protected function check() {
        return true;
    }

    /**
     * サービスを実行する
     *
     * @access protected
     * @return boolean サービス実行結果
     */
    protected function exec() {
        return true;
    }

    /**
     * 認証処理
     * @return bool
     */
    protected function auth() {
        return true;
    }

    /**
     * 処理結果を文字列として返却する
     *
     * @access protected
     * @return string 処理結果
     */
    protected function returnResponse() {
        return $this->_response->serialize();
    }
}
