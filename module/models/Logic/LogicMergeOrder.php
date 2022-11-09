<?php
namespace models\Logic;

use Zend\Db\Adapter\Adapter;
use models\Table\TableOrder;
use models\Table\TableEnterprise;
use models\Table\TableCustomer;
use models\Table\TableOrderItems;
use models\Table\TableDeliveryDestination;
use models\Table\TableOrderSummary;
use models\Table\TableCancel;
use models\Table\TableStampFee;
use models\Logic\classes\LogicclassesOrderInputInfo;
use Coral\Coral\Validate\CoralValidateUtility;
use Coral\Base\BaseGeneralUtils;
use member\classes\OrderInputInfo;
use models\Table\TableCombinedDictate;

/**
 * 注文まとめクラス
 *
 * @author kashira
 *
 */
class LogicMergeOrder
{
    /**
     * アダプタ
     *
     * @var Adapter
     */
    protected $_adapter = null;
    /**
     * コンストラクタ
     *
     * @param Adapter $adapter アダプタ
     */
    function __construct(Adapter $adapter)
    {
        $this->_adapter = $adapter;
    }

    /**
     * 注文をまとめる
     *
     * @param array $arrOrderSeqs まとめ対象の注文Seq配列
     * @return int まとめた結果の注文Seq
     */
    public function merge($arrOrderSeqs, $opId, $entId, $nextval)
    {
        $newOrderSeq = -1;
        $newOrderSeq = -1;
        $arrOrderSeqsCount = 0;
        if (!empty($arrOrderSeqs)) {
            $arrOrderSeqsCount = count($arrOrderSeqs);
        }
        $cnt = $arrOrderSeqsCount;

        if ($cnt > 1000)
        {
            throw new \Exception("取りまとめ注文数の上限は1000件です。", 1);
        }

        if ($cnt > 1)
        {
            $newOrderSeq = $this->mergeMulti($arrOrderSeqs, $opId, $entId, $nextval);
        }
        else if($cnt == 1)
        {
            $newOrderSeq = $this->mergeSingle($arrOrderSeqs[0], $opId, $entId, $nextval);
        }
        else {
            // 何もしない
        }

        return $newOrderSeq;
    }
    /**
     * まとめ注文キャンセル
     *
     * @param array $arrOrderSeqs まとめ対象の注文Seq配列
     * @param int $userId ログインID
     * @return int まとめた結果の注文Seq
     */
    public function mergecancel($arrOrderSeqs, $userId)
    {
        $newOrderSeq = -1;
        $arrOrderSeqsCount = 0;
        if (!empty($arrOrderSeqs)) {
            $arrOrderSeqsCount = count($arrOrderSeqs);
        }
        $cnt = $arrOrderSeqsCount;

        if ($cnt > 0)
        {
            $newOrderSeq = $this->cnclmerge($arrOrderSeqs, $userId);
        }
        else {
            // 何もしない
        }

        return $newOrderSeq;
    }

    /**
     * まとめる注文のサマリーを取得する
     *
     * @param array $arrOrderSeqs まとめ対象の注文Seq配列
     * @return string サマリー
     */
    public function getMergeSummary($arrOrderSeqs)
    {
        $summary = "【まとめようとした注文】\n";
        $mdlo = new TableOrder($this->_adapter);

        if (!empty($arrOrderSeqs))
        {
            foreach($arrOrderSeqs as $oSeq)
            {
                $target = $mdlo->find($oSeq)->current();
                $summary .= sprintf("注文ID=%s,OrderSeq=%d\n", $target['OrderId'], $target['OrderSeq']);
            }
        }

        return $summary;
    }

    /**
     * 一つの注文をまとめる
     *
     * @param int $orderSeq まとめ対象の注文Seq
     * @param int $opId ログインID
     * @param int $entId 加盟店ID
     * @param int $nextval 汎用シーケンス
     * @return int まとめた結果の注文Seq
     */
    private function mergeSingle($orderSeq, $opId, $entId, $nextval)
    {
        $mdlo = new TableOrder($this->_adapter);
        $mdle = new TableEnterprise($this->_adapter);
        $mdlcd = new TableCombinedDictate($this->_adapter);

        // 対象注文の取得
        $cData = $mdlo->find($orderSeq)->current();

        // 請求ストップフラグが立っていた場合にはエラー
        if($cData['LetterClaimStopFlg']) throw new \Exception(sprintf("請求ストップ,OrderSeq=%d", $orderSeq));

        $sql  = " UPDATE T_Order ";
        $sql .= " SET ";
        $sql .= "       CombinedClaimTargetStatus    = :CombinedClaimTargetStatus ";  // 取りまとめ対象注文ステータス(まとめ指示)
        $sql .= " ,     UpdateDate    = now() ";  // 更新日時
        $sql .= " ,     UpdateId    = :UpdateId ";  // 更新者
        $sql .= " WHERE OrderSeq  = :OrderSeq ";

        $prm = array(
                ':CombinedClaimTargetStatus' => $cData['CombinedClaimTargetStatus'] + 10,
                ':OrderSeq' => $orderSeq,
                ':UpdateId' => $opId,
        );
        $ri = $this->_adapter->query($sql)->execute($prm);

        $date = date("Y-m-d H:i:s");
        // 取りまとめ指示データの生成
        // (array生成)
        $data = array(
                'CombinedDictateSeq' => NULL,
                'CombinedDictateGroup' => $nextval,
                'OrderSeq' => $orderSeq,
                'CombinedStatus' => 0,
                'IndicationDate' => $date,
                'ExecDate' => NULL,
                'CancelDate' => NULL,
                'EnterpriseId' => $entId,
                'RegistDate' => $date,
                'RegistId' => $opId,
                'UpdateDate' => $date,
                'UpdateId' => $opId,
                'ValidFlg' => 1,
        );
        $mdlcd->saveNew($data);
        return $orderSeq;
    }

    /**
     * 複数の注文をまとめる
     *
     * @param array $arrOrderSeqs まとめ対象の注文Seq配列
     * @param int $opId ログインID
     * @param int $entId 加盟店ID
     * @param int $nextval 汎用シーケンス
     * @return int まとめた結果の注文Seq
     */
    private function mergeMulti($arrOrderSeqs, $opId, $entId, $nextval)
    {
        $mdlo = new TableOrder($this->_adapter);
        $mdlcd = new TableCombinedDictate($this->_adapter);

        $arrOrderSeqsCount = 0;
        if (!empty($arrOrderSeqs)) {
            $arrOrderSeqsCount = count($arrOrderSeqs);
        }
        for($i=0; $i<$arrOrderSeqsCount; $i++)
        {
            // 対象注文の取得
            $cData = $mdlo->find($arrOrderSeqs[$i])->current();

            $sql  = " UPDATE T_Order ";
            $sql .= " SET ";
            $sql .= "       CombinedClaimTargetStatus    = :CombinedClaimTargetStatus ";  // 取りまとめ対象注文ステータス(まとめ指示)
            $sql .= " ,     UpdateDate    = now() ";  // 更新日時
            $sql .= " ,     UpdateId    = :UpdateId ";  // 更新者
            $sql .= " WHERE OrderSeq  = :OrderSeq ";

            $prm = array(
                ':CombinedClaimTargetStatus' => $cData['CombinedClaimTargetStatus'] + 10,
                ':OrderSeq' => $arrOrderSeqs[$i],
                ':UpdateId' => $opId,
            );
            $this->_adapter->query($sql)->execute($prm);

            $date = date("Y-m-d H:i:s");
            // 取りまとめ指示データの生成
            // (array生成)
            $data = array(
                    'CombinedDictateSeq' => NULL,
                    'CombinedDictateGroup' => $nextval,
                    'OrderSeq' => $arrOrderSeqs[$i],
                    'CombinedStatus' => 0,
                    'IndicationDate' => $date,
                    'ExecDate' => NULL,
                    'CancelDate' => NULL,
                    'EnterpriseId' => $entId,
                    'RegistDate' => $date,
                    'RegistId' => $opId,
                    'UpdateDate' => $date,
                    'UpdateId' => $opId,
                    'ValidFlg' => 1,
            );
            $mdlcd->saveNew($data);
        }
        return $newOrderSeq;
    }

    /**
     *  まとめ注文をキャンセルする
     *
     * @param array $arrOrderSeqs まとめ対象の注文Seq配列
     * @return int まとめた結果の注文Seq
     */
    private function cnclmerge($arrOrderSeqs, $userId)
    {
        $mdlo = new TableOrder($this->_adapter);

        $arrOrderSeqsCount = 0;
        if (!empty($arrOrderSeqs)) {
            $arrOrderSeqsCount = count($arrOrderSeqs);
        }
        for($i=0; $i<$arrOrderSeqsCount; $i++){
            // 対象注文の取得
            $cData = $mdlo->find($arrOrderSeqs[$i])->current();

            // 注文情報の更新
            $sql = "";
            $sql .= " UPDATE    T_Order";
            $sql .= " SET       CombinedClaimTargetStatus   = :CombinedClaimTargetStatus";      // 取りまとめ注文ステータス
            $sql .= "       ,   UpdateDate                  = now()";                           // 更新日時
            $sql .= "       ,   UpdateId                    = :UpdateId";                       // 更新者
            $sql .= " WHERE     OrderSeq                    = :OrderSeq";

            $stm = $this->_adapter->query($sql);

            $prm = array(
                    ':CombinedClaimTargetStatus' => $cData['CombinedClaimTargetStatus'] - 10,
                    ':OrderSeq' => $arrOrderSeqs[$i],
                    ':UpdateId' => $userId,
            );

            $ri = $stm->execute($prm);

            // 取りまとめ指示情報の更新
            $sql2 = "";      // 変数初期化
            $sql2 .= " UPDATE    T_CombinedDictate";
            $sql2 .= " SET       CombinedStatus  = 9";           // ステータス(キャンセル:9)
            $sql2 .= "       ,   CancelDate      = now()";       // キャンセル日時
            $sql2 .= "       ,   UpdateDate      = now()";       // 更新日時
            $sql2 .= "       ,   UpdateId        = :UpdateId";   // 更新者
            $sql2 .= "       ,   ValidFlg        = 0";           // 有効フラグ(無効:0)
            $sql2 .= " WHERE     OrderSeq        = :OrderSeq ";
            $sql2 .= " AND       ValidFlg        = 1";

            $stm2 = $this->_adapter->query($sql2);

            $prm2 = array(
                    ':OrderSeq' => $arrOrderSeqs[$i],
                    ':UpdateId' => $userId,
            );

            $ri2 = $stm2->execute($prm2);
        }
        return $orderSeq;
    }
}
