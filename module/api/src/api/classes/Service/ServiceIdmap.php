<?php
namespace api\classes\Service;

use api\Application;
use api\classes\Service\ServiceAbstract;
use api\classes\Service\Idmap\ServiceIdmapConst;
use api\classes\Service\Response\ServiceResponseIdmap;
use Zend\Db\ResultSet\ResultSet;

/**
 * 注文ID変換サービスクラス
 */
class ServiceIdmap extends ServiceAbstract {
    /**
     * 注文ID変換APIのサービスID
     * @var string
     */
    protected $_serviceId = "03";

    /**
     * 要求Ent_OrderIdリスト
     *
     * @access protected
     * @var array
     */
    protected $_entOrderIdList = array();

    /**
     * 初期化処理
     *
     * @access protected
     */
    protected function init() {
        // サイトIDチェックは行わない
        $this->_checkSiteId = false;

        // レスポンスを初期化
        $this->_response = new ServiceResponseIdmap();

        // 認証用
        $this->_apiUserId    = $this->_data[ServiceIdmapConst::API_USER_ID];
        $this->_enterpriseId = $this->_data[ServiceIdmapConst::ENTERPRISE_ID];

        // 要求Ent_OrderIdリスト
        $this->_entOrderIdList = $this->_data[ServiceIdmapConst::ENT_ORDER_ID];

        // ログ出力
        Application::getInstance()->logger->info(
            get_class($this) . '#init() ' .
            join(', ', array(
                sprintf('%s: %s', ServiceIdmapConst::ENTERPRISE_ID, $this->_enterpriseId),
                sprintf('%s: %s', ServiceIdmapConst::API_USER_ID, $this->_apiUserId),
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

        // Ent_OrderId指定が0件の場合は問い合わせるまでもなくエラー
        if(!is_array($this->_entOrderIdList) || empty($this->_entOrderIdList)) {
            $this->_response->addMessage(sprintf('E%s201', $this->_serviceId), '任意注文番号の指定は必須です');
            return $result;
        }

        $db = $this->_db;

        // 要求Ent_OrderIdリストに対応するT_Order行を取得 → Ent_OrderIdをキーとした連想配列に詰め替える
        $orderMap = array();
//        $wheres = array(
//            $db->quoteInto('EnterpriseId = ?', $this->_enterpriseId),
//            $db->quoteInto('Ent_OrderId IN (?)', $this->_entOrderIdList),
//            'DataStatus = 31',
//            'Cnl_Status = 0'
//        );
//        $query = sprintf('SELECT * FROM T_Order WHERE %s', join(' AND ', $wheres));
//         Application::getInstance()->logger->debug(sprintf('query: %s', $query));
//         foreach($db->fetchAll($query) as $orderRow) {
//             $entId = $orderRow[ServiceIdmapConst::ENT_ORDER_ID];
//             if(isset($orderMap[$entId]) && is_array($orderMap[$entId])) {
//                 $orderMap[$entId][] = $orderRow;
//             } else {
//                 $orderMap[$entId] = array($orderRow);
//             }
//         }
        $entOrderIdList = array();
        foreach($this->_entOrderIdList as $entOrderId) {
            $entOrderIdList[] = $this->_db->getPlatform()->quoteValue($entOrderId);
        }
        $query = ' SELECT * FROM T_Order WHERE EnterpriseId = :EnterpriseId AND Ent_OrderId IN ( ' . implode(' ,', $entOrderIdList) . ' ) AND DataStatus = 31 AND Cnl_Status = 0 AND ValidFlg = 1';
        $wheres = array(
            ':EnterpriseId' =>  $this->_enterpriseId,
        );
        Application::getInstance()->logger->debug(sprintf('query: %s', $query));
        $stm = $db->query($query);
        $rs = new ResultSet();
        $rows = $rs->initialize($stm->execute($wheres))->toArray();

        foreach($rows as $orderRow) {

            $entId = $orderRow[ServiceIdmapConst::ENT_ORDER_ID];
            if(isset($orderMap[$entId]) && is_array($orderMap[$entId])) {
                $orderMap[$entId][] = $orderRow;
            } else {
                $orderMap[$entId] = array($orderRow);
            }
        }

        $rowCount = 0;
        foreach($this->_entOrderIdList as $entId) {
            // 1件ヒット（＝変換OK）分のみカウント
            $orderMapCount = 0;
            if (!empty($orderMap[$entId])) {
                $orderMapCount = count($orderMap[$entId]);
            }
            if(isset($orderMap[$entId]) && is_array($orderMap[$entId]) && $orderMapCount == 1) $rowCount++;
            $this->_response->addResult($entId, isset($orderMap[$entId]) ? $orderMap[$entId] : null);
        }
        if($rowCount) {
            // 1件でも要求Ent_OrderIdに一致するデータがあればOK
            $result = true;
        } else {
            // 有効Ent_OrderIdの指定なし
            $result = false;
            $this->_response->addMessage(sprintf('E%s202', $this->_serviceId), '有効な任意注文番号が指定されていません');
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