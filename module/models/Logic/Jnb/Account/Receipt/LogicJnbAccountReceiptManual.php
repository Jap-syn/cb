<?php
namespace models\Logic\Jnb\Account\Receipt;

use Zend\Db\Adapter\Driver\ResultInterface;
use models\Logic\Jnb\Account\LogicJnbAccountReceipt;

class LogicJnbAccountReceiptManual extends LogicJnbAccountReceipt {
    /**
     * 注文が特定されている入金保留中の入金通知データをすべて取得する
     *
     * @return ResultInterface
     */
    public function getManualReceiptTargetsWithOrderSeq() {
        $where = 'nf.OrderSeq IS NOT NULL';
        return $this->_getManualTargets($where);
    }

    /**
     * 注文が特定されていない入金保留中の入金通知データをすべて取得する
     *
     * @return ResultInterface
     */
    public function getManualReceiptTargetsWithoutOrderSeq() {
        $where = 'nf.OrderSeq IS NULL';
        return $this->_getManualTargets($where);
    }

    /**
     * 指定入金通知管理データを取得する。指定データが存在しないまたは入金保留中でない場合、
     * このメソッドはnullを返す
     *
     * @param int $nfSeq 入金通知管理SEQ
     * @return array | null
     */
    public function getManualReceiptTargetsByNotificationSeq($nfSeq) {
        $where = (" nf.NotificationSeq = ". $nfSeq);
        $row = $this->_getManualTargets($where)->current();
        return ($row) ? $row : null;
    }

    /**
     * 指定条件の入金保留中の入金通知データをすべて取得する
     *
     * @access protected
     * @param string $where 通知データを取得する条件
     * @return ResultInterface
     */
    protected function _getManualTargets($where) {
        if(!strlen($where)) throw new \Exception('condition must be specified.');
        $q = <<<EOQ
SELECT
    nf.*,
    acc.*,
    jnb.OemId,
    oem.OemNameKj,
    ord.OrderId,
    ord.RegistDate,
    ord.DataStatus,
    ord.Cnl_Status,
    ord.CloseReason,
    ord.EnterpriseId,
    ent.EnterpriseNameKj,
    sum.NameKj,
    IFNULL(cc.ClaimedBalance, 0) AS ClaimAmountTotal
FROM
    T_JnbPaymentNotification nf INNER JOIN
    T_JnbAccount acc ON acc.AccountSeq = nf.AccountSeq INNER JOIN
    T_JnbAccountGroup grp ON grp.AccountGroupId = acc.AccountGroupId INNER JOIN
    T_Jnb jnb ON jnb.JnbId = acc.JnbId INNER JOIN
    (
        SELECT 0 AS OemId, 'キャッチボール' AS OemNameKj UNION ALL
        SELECT OemId, OemNameKj FROM T_Oem
    ) oem ON oem.OemId = jnb.OemId LEFT OUTER JOIN
    T_Order ord ON ord.OrderSeq = nf.OrderSeq LEFT OUTER JOIN
    T_Enterprise ent ON ent.EnterpriseId = ord.EnterpriseId LEFT OUTER JOIN
    T_OrderSummary sum ON sum.OrderSeq = ord.OrderSeq
    LEFT OUTER JOIN T_ClaimControl cc ON (cc.OrderSeq = ord.OrderSeq)
WHERE
    IFNULL(jnb.ValidFlg, 0) = 1 AND
    IFNULL(grp.ReturnedFlg, 0) = 0 AND
    IFNULL(nf.DeleteFlg, 0) = 0 AND
    nf.Status = 3 AND
    %s
ORDER BY
    nf.NotificationSeq ASC
EOQ;
        return $this->_adapter->query(sprintf($q, $where))->execute(null);
    }
}
