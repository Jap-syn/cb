<?php
namespace Coral\Coral\History;

use Zend\Db\Adapter\Adapter;
use models\Table\TableUser;

/**
 * 注文履歴登録
 */
class CoralHistoryOrder
{
    /**
     * DBアクセスオブジェクト
     * @var Adapter
     */
    protected  $_adapter = null;

    /**
     * コンストラクタ
     *
     * @param Adapter $adapter アダプタ
     */
    public function __construct(Adapter $adapter)
    {
        $this->_adapter = $adapter;
    }

    /**
     * 注文履歴登録処理
     * ※トランザクションに関しては呼び出し元で正しく管理すること
     *
     * @param int $pi_order_seq 注文SEQ
     * @param int $pi_history_reason_cd 理由コード
     * @param int $pi_user_id ユーザID
     * @see 理由コードはコードマスタ（識別ID：97）で紐付けられる。
     */
    public function InsOrderHistory ($pi_order_seq, $pi_history_reason_cd, $pi_user_id) {

        try {
            // 注文履歴登録処理SQL
            $stm = $this->_adapter->query($this->getBaseP_OrderHistory());

            // SQL実行結果取得用SQL
            $getRetValSql = "SELECT @po_ret_sts AS po_ret_sts, @po_ret_errcd AS po_ret_errcd, @po_ret_sqlcd AS po_ret_sqlcd, @po_ret_msg AS po_ret_msg";

            $ri = $stm->execute(array(
                    ':pi_order_seq' => $pi_order_seq,
                    ':pi_history_reason_cd' => $pi_history_reason_cd,
                    ':pi_user_id' => $pi_user_id
                    ));

            // SQL実行例外なしもエラー戻り値の時は例外をｽﾛｰ
            $retval = $this->_adapter->query($getRetValSql)->execute(null)->current();
            if ($retval['po_ret_sts'] != 0) {
                throw new \Exception($retval['po_ret_msg']);
            }
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * 注文履歴登録処理ファンクションの基礎SQL取得。
     *
     * @return 注文履歴登録処理ファンクションの基礎SQL
     */
    protected function getBaseP_OrderHistory() {
        return <<<EOQ
                CALL P_OrderHistory(
                    :pi_order_seq
                ,   :pi_history_reason_cd
                ,   :pi_user_id
                ,   @po_ret_sts
                ,   @po_ret_errcd
                ,   @po_ret_sqlcd
                ,   @po_ret_msg
                    )
EOQ;
    }
}