<?php
namespace api\classes\Service;

use api\Application;
use api\classes\Service\ServiceAbstract;
use api\classes\Service\Delivlist\ServiceDelivlistConst;
use api\classes\Service\Response\ServiceResponseDelivlist;
use models\Table\TableDeliMethod;
use Zend\Db\ResultSet\ResultSet;
use models\Logic\LogicDeliveryMethod;
use models\Table\TableEnterprise;

/**
 * 配送会社一覧取得サービスクラス
 */
class ServiceDelivlist extends ServiceAbstract {
    /**
     * 配送会社一覧取得APIのサービスID
     * @var string
    */
    protected $_serviceId = "02";

    /**
     * 初期化処理
     *
     * @access protected
     */
    protected function init() {
        // サイトIDチェックは行わない
        $this->_checkSiteId = false;

        // レスポンスを初期化
        $this->_response = new ServiceResponseDelivlist();

        // 認証用
        $this->_apiUserId    = $this->_data[ServiceDelivlistConst::API_USER_ID];
        $this->_enterpriseId = $this->_data[ServiceDelivlistConst::ENTERPRISE_ID];

        // ログ出力
        Application::getInstance()->logger->info(
            get_class($this) . '#init() ' .
            join(', ', array(
            sprintf('%s: %s', ServiceDelivlistConst::ENTERPRISE_ID, $this->_enterpriseId),
            sprintf('%s: %s', ServiceDelivlistConst::API_USER_ID, $this->_apiUserId),
            sprintf('RemoteAddr: %s', f_get_client_address())      // 2015/09/23 Y.Suzuki Mod f_get_client_address をｺｰﾙするように変更
        )) );
    }

    /**
     * 入力に対する検証を行う
     *
     * @access protected
     * @return boolean 検証結果
     */
    protected function check() {
        $result = true;

        // このサービスでは単独入力の検証は行わない

        return $result;
    }

    /**
     * サービスを実行する
     *
     * @access protected
     * @return boolean サービス実行結果
     */
    protected function exec() {
//         $master = new TableDeliMethod($this->_db);
//         $rs = new ResultSet();
//         $rows = $rs->initialize($master->getValidAll())->toArray();

//         // 配送会社一覧SQLを実行し結果に追加する
//         foreach($rows as $row) {
//             $this->_response->addResult($row);
//         }
        $delilogic = new LogicDeliveryMethod($this->_db);
        $enterprise = new TableEnterprise($this->_db);

        // OEMIDを取得
        $data = $enterprise->findEnterprise2($this->_enterpriseId)->current();

        // 配送会社一覧SQLを実行し結果に追加する
        foreach($delilogic->getDeliMethodList($data['OemId']) as $row) {
            $this->_response->addResult($row);
        }

        // DBアクセス例外以外に論理エラーがないため、ここまで処理できたら実行OK
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