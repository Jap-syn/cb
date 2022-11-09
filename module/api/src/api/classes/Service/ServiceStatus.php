<?php
namespace api\classes\Service;

use api\Application;
use api\classes\Service\ServiceAbstract;
use api\classes\Service\Status\ServiceStatusConst;
use api\classes\Service\Response\ServiceResponseStatus;
use zend\Db\ResultSet\ResultSet;

/**
 * 与信状況問い合わせサービスクラス
 */
class ServiceStatus extends ServiceAbstract {
    /**
     * 与信状況問い合わせAPIのサービスID
     * @var string
     */
    protected $_serviceId = "01";

    /**
     * 要求OrderIdリスト
     *
     * @access protected
     * @var array
     */
    protected $_orderIdList = array();

    /**
     * 初期化処理
     *
     * @access protected
     */
    protected function init() {
        // サイトIDチェックは行わない
        $this->_checkSiteId = false;

        // レスポンスを初期化
        $this->_response = new ServiceResponseStatus();

        // 認証用
        $this->_apiUserId    = $this->_data[ServiceStatusConst::API_USER_ID];
        $this->_enterpriseId = $this->_data[ServiceStatusConst::ENTERPRISE_ID];

        // 要求OrderIdリスト
        $this->_orderIdList = $this->_data[ServiceStatusConst::ORDER_ID];

        // ログ出力
        Application::getInstance()->logger->info(
            get_class($this) . '#init() ' .
            join(', ', array(
                sprintf('%s: %s', ServiceStatusConst::ENTERPRISE_ID, $this->_enterpriseId),
                sprintf('%s: %s', ServiceStatusConst::API_USER_ID, $this->_apiUserId),
                sprintf('RemoteAddr: %s', f_get_client_address())       // 2015/09/23 Y.Suzuki Mod f_get_client_address をｺｰﾙするように変更
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
        $result = false;

        // OrderId指定が0件の場合は問い合わせるまでもなくエラー
        if(!is_array($this->_orderIdList) || empty($this->_orderIdList)) {
            $this->_response->addMessage(sprintf('E%s201', $this->_serviceId), '注文IDの指定は必須です');
            return $result;
        }

        $db = $this->_db;

        // 要求OrderIdリストに対応するT_Order行を取得 → OrderIdをキーとした連想配列に詰め替える
        $orderMap = array();
//        $wheres = array(
//            $db->quoteInto('EnterpriseId = ?', $this->_enterpriseId),
//            $db->quoteInto('OrderId IN (?)', $this->_orderIdList)
//        );
//        $query = sprintf('SELECT * FROM T_Order WHERE %s', join(' AND ', $wheres));
//        Application::getInstance()->logger->debug(sprintf('query: %s', $query));
//        foreach($db->fetchAll($query) as $orderRow) {
//            $orderMap[$orderRow[ServiceStatusConst::ORDER_ID]] = $orderRow;
//        }
        $orderIdList = array();
        foreach($this->_orderIdList as $orderId) {
            $orderIdList[] = $this->_db->getPlatform()->quoteValue($orderId);
        }
        $query = ' SELECT * FROM T_Order WHERE EnterpriseId = :EnterpriseId AND OrderId IN ( ' . implode(' ,', $orderIdList) . ' ) ';
        $wheres = array(
                ':EnterpriseId' =>  $this->_enterpriseId,
        );
        Application::getInstance()->logger->debug(sprintf('query: %s', $query));
        $stm = $db->query($query);
        $rs = new ResultSet();
        $rows = $rs->initialize($stm->execute($wheres))->toArray();
        Application::getInstance()->logger->debug(sprintf('rows: %s', $rows));

        foreach($rows as $orderRow) {
            $orderMap[$orderRow[ServiceStatusConst::ORDER_ID]] = $orderRow;
        }

        $rowCount = 0;
        foreach($this->_orderIdList as $orderId) {
            if(isset($orderMap[$orderId])) $rowCount++;
            $this->_response->addResult($orderId, isset($orderMap[$orderId]) ? $orderMap[$orderId] : null);
        }
        if($rowCount) {
            // 1件でも要求OrderIdに一致するデータがあればOK
            $result = true;
        } else {
            // 有効OrderIdの指定なし
            $result = false;
            $this->_response->addMessage(sprintf('E%s202', $this->_serviceId), '有効な注文IDが指定されていません');
        }

        return $result;
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