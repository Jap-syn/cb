<?php
namespace models\Logic\SmbcRelation\Service;

use Zend\Json\Json;
use models\Table\TableSmbcRelationLog;
use models\Logic\SmbcRelation\LogicSmbcRelationException;

class LogicSmbcRelationServiceCancel extends LogicSmbcRelationServiceAbstract {
    /** オプションデフォルト定数：決済ステーション請求取消インターフェイスのエンドポイントパス @var string */
    const DEFAULT_SERVICE_INTERFACE_PATH = 'sf/cd/skuinfokt/skuinfoKakutei.do';

    /**
     * 対象の決済ステーション機能を指定するための識別コードを取得する
     *
     * @return int 機能識別コード
     */
    public function getTargetFunctionCode() {
        return TableSmbcRelationLog::TARGET_FUNC_CANCEL;
    }

    /**
     * 指定された請求履歴のデータを基に、SMBC決済ステーション向け請求情報を構築する
     *
     * @access protected
     * @param int $claimHistroySeq 請求履歴SEQ
     * @return array 請求情報登録用データ
     */
    protected function buildSendParams($claimHistorySeq) {
        // 指定請求履歴に関連した請求情報登録時のログを取得
        $registerLog = $this->getRegisterLog($claimHistorySeq);
        if(!$registerLog) {
            // 登録時のログがない場合は取消できないので例外
            throw new LogicSmbcRelationException('cannot found register log');
        }
        // 当時の送信パラメータを復元
        $sentData = Json::decode($registerLog['SentRawData'], Json::TYPE_ARRAY);

        // DBから主要情報を構築
        $q = <<<EOQ
SELECT
    ORD.OrderSeq,
    OCA.ClaimAccountSeq,
    '219' AS version,
    SRA.BillMethod AS bill_method,
    SRA.KessaiId AS kessai_id,
    SRA.ShopCd AS shop_cd,
    (CASE WHEN IFNULL(ENT.LinePayUseFlg, 0) = 1
          THEN (CASE IFNULL(OCA.ClaimLayoutMode, 0)
                WHEN 1 THEN SyunoCoCd5
                WHEN 2 THEN SyunoCoCd6
                ELSE SyunoCoCd4
                END)
          ELSE (CASE IFNULL(OCA.ClaimLayoutMode, 0)
                WHEN 1 THEN SyunoCoCd2
                WHEN 2 THEN SyunoCoCd3
                ELSE SyunoCoCd1
                END)
     END) AS syuno_co_cd,
    (CASE WHEN IFNULL(ENT.LinePayUseFlg, 0) = 1
          THEN (CASE IFNULL(OCA.ClaimLayoutMode, 0)
                WHEN 1 THEN ShopPwd5
                WHEN 2 THEN ShopPwd6
                ELSE ShopPwd4
                END)
          ELSE (CASE IFNULL(OCA.ClaimLayoutMode, 0)
                WHEN 1 THEN ShopPwd2
                WHEN 2 THEN ShopPwd3
                ELSE ShopPwd1
                END)
    END) AS shop_pwd
FROM
    T_OemClaimAccountInfo OCA INNER JOIN
    T_Order ORD ON ORD.OrderSeq = OCA.OrderSeq INNER JOIN
    T_Customer CUS ON CUS.OrderSeq = ORD.OrderSeq INNER JOIN
    T_Enterprise ENT ON ENT.EnterpriseId = ORD.EnterpriseId INNER JOIN
    T_SmbcRelationAccount SRA ON SRA.OemId = IFNULL(ENT.OemId, 0)
WHERE
    OCA.ClaimHistorySeq = :ClaimHistorySeq
EOQ;
        $data = $this->_adapter->query($q)->execute(array(':ClaimHistorySeq' => $claimHistorySeq))->current();
        if(!$data) {
            throw new LogicSmbcRelationException('cannot build target data');
        }

        // 請求番号と請求金額を登録時の送信パラメータから補充
        foreach(array('shoporder_no', 'seikyuu_kingaku') as $key) {
            $data[$key] = $sentData[$key];
        }

        return $data;
    }

    /**
     * 指定請求履歴に関連付けられた、請求情報登録時のログを取得する
     *
     * @access protected
     * @param int $claimHistorySeq 請求履歴SEQ
     * @return array | null
     */
    protected function getRegisterLog($claimHistorySeq) {
        $q = <<<EOQ
SELECT
    log.*
FROM
    T_SmbcRelationLog log INNER JOIN
    T_OemClaimAccountInfo oca ON (oca.ClaimAccountSeq = log.ClaimAccountSeq AND log.TargetFunction = 1)
WHERE
    oca.ClaimHistorySeq = :ClaimHistorySeq
EOQ;
        $row = $this->_adapter->query($q)->execute(array(':ClaimHistorySeq' => $claimHistorySeq))->current();
        return ($row) ? $row : null;
    }

    /**
     * 指定注文に関する請求取消処理を実行する
     *
     * @param int $oseq 注文SEQ
     */
    public function execCancelByOrderSeq($oseq) {
        $q = <<<EOQ
SELECT
	oca.ClaimHistorySeq
FROM
	T_SmbcRelationLog srl inner join
	T_OemClaimAccountInfo oca on oca.ClaimAccountSeq = srl.ClaimAccountSeq
WHERE
	srl.OrderSeq = :OrderSeq and
	srl.TargetFunction = 1 and
	srl.Status = 2
EOQ;
        $ri = $this->_adapter->query($q)->execute(array(':OrderSeq' => $oseq));
        foreach ($ri as $row) {
            try {
                $this->sendTo($row['ClaimHistorySeq']);
            } catch(\Exception $err) {
                // 決済ステーション側事由で取消できない場合も例外が発生するが
                // sendTo側でロギング等をしているためここでは例外を無視する
            }
        }
    }
}