<?php
namespace models\Logic\SmbcRelation\Service;

class LogicSmbcRelationServiceTestRegister extends LogicSmbcRelationServiceRegister {
    /**
     * SMBC決済ステーションへのデータ送信を準備する。
     * このクラスの実装では実際にT_SmbcRelationLogへの出力は行わず、ダミーシーケンスを返す
     *
     * @access protected
     * @param int $claimAccountSeq 請求口座SEQ
     * @param array $params 決済ステーション向け送信データ
     * @return 決済ステーション送受信ログSEQ
     */
    protected function preparseSend($claimAccountSeq, array $params) {
        return 0;
    }

    /**
     * SMBC決済ステーションからの受信を完了する。
     * このクラスの実装では実際にT_SmbcRelationLogへの出力は行わず、元の受信データを
     * そのまま返す
     *
     * @access protected
     * @param int $smbcRelSeq 決済ステーション送受信ログSEQ
     * @param array $rcvData 受信データ
     * @return array 受信データ
     */
    protected function sent($smbcRelSeq, array $rcvData) {
        return $rcvData;
    }

    /**
     * SMBC決済ステーションへのデータ送信の失敗をログに反映する
     * このクラスの実装では実際にT_SmbcRelationLogへの出力は行わない
     *
     * @access protected
     * @param int $smbcRelSeq 決済ステーション送受信ログSEQ
     * @param \Exception $err 例外
     */
    protected function failure($smbcRelSeq, \Exception $err) {
        // nop
        // TODO: ロギングを検討
    }

    protected function _sendTo(array $data) {
        $result = parent::_sendTo($data);
        echo var_export($result, true);
        return $result;
    }
}
