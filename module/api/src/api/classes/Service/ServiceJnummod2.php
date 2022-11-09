<?php
namespace api\classes\Service;

use api\classes\Service\ServiceJnummod;

/**
 * 伝票番号修正サービスクラス(伝票番号登録サービスからの呼出し用)
 */
class ServiceJnummod2 extends ServiceJnummod {
    /**
     * 与信状況問い合わせAPIのサービスID
     * @var string
     */
    protected $_serviceId = "04";

     /**
     * (オーバーライド)
     * 指定注文IDの注文データを取得する。
     * 対象のデータが未キャンセルで伝票番号登録待ちの場合のみデータを返し
     * それ以外は{@link ServiceException}がスローされる
     *
     * @access protected
     * @param string $orderId 注文ID
     * @return 注文情報
     */
    protected function getOrderData($orderId) {

        $sql = " SELECT * FROM T_Order WHERE OrderId = :OrderId AND EnterpriseId = :EnterpriseId AND DataStatus IN (41,51) AND Cnl_Status = 0 ";
        $ri = $this->_db->query($sql)->execute(array(':OrderId' => $orderId, ':EnterpriseId' => $this->_enterpriseId));
        if ($ri->count() != 1) {
            throw new ServiceException('指定の注文は登録されていないか伝票番号登録可能ではありません', $this->_serviceId, '301');
        }
        return $ri->current();
    }
}