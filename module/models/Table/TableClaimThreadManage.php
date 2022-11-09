<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_ClaimThreadManage(請求処理スレッド管理)テーブルへのアダプタ
 */
class TableClaimThreadManage
{
    protected $_name = 'T_ClaimThreadManage';
    protected $_primary = array('Seq');
    protected $_adapter = null;

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
     * 請求処理スレッド管理データを取得する
     *
     * @param int $seq SEQ
     * @return ResultInterface
     */
    public function find($seq)
    {
        $sql  = " SELECT * FROM T_ClaimThreadManage WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
        );

        return $stm->execute($prm);
    }

    /**
     * (未使用)新しいレコードをインサートする。
     *
     * @param array $data インサートする連想配列
     * @return プライマリキーのバリュー
     */
    public function saveNew($data)
    {
        $sql  = " INSERT INTO T_ClaimThreadManage (Rw1, Rw2, Rw3, Rw4, Rw5, Rw6, Rw7, Rw8, Rw9, Rw0, Smbc, Re, SttDate, EndDate) VALUES (";
        $sql .= "   :Rw1 ";
        $sql .= " , :Rw2 ";
        $sql .= " , :Rw3 ";
        $sql .= " , :Rw4 ";
        $sql .= " , :Rw5 ";
        $sql .= " , :Rw6 ";
        $sql .= " , :Rw7 ";
        $sql .= " , :Rw8 ";
        $sql .= " , :Rw9 ";
        $sql .= " , :Rw0 ";
        $sql .= " , :Smbc ";
        $sql .= " , :Re ";
        $sql .= " , :SttDate ";
        $sql .= " , :EndDate ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Rw1' => isset($data['Rw1']) ? $data['Rw1'] : 0,
                ':Rw2' => isset($data['Rw2']) ? $data['Rw2'] : 0,
                ':Rw3' => isset($data['Rw3']) ? $data['Rw3'] : 0,
                ':Rw4' => isset($data['Rw4']) ? $data['Rw4'] : 0,
                ':Rw5' => isset($data['Rw5']) ? $data['Rw5'] : 0,
                ':Rw6' => isset($data['Rw6']) ? $data['Rw6'] : 0,
                ':Rw7' => isset($data['Rw7']) ? $data['Rw7'] : 0,
                ':Rw8' => isset($data['Rw8']) ? $data['Rw8'] : 0,
                ':Rw8' => isset($data['Rw9']) ? $data['Rw9'] : 0,
                ':Rw8' => isset($data['Rw0']) ? $data['Rw0'] : 0,
                ':Smbc' => isset($data['Smbc']) ? $data['Smbc'] : 0,
                ':Re' => isset($data['Re']) ? $data['Re'] : 0,
                ':SttDate' => isset($data['SttDate']) ? $data['SttDate'] : NULL,
                ':EndDate' => isset($data['EndDate']) ? $data['EndDate'] : NULL,
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
    }

    /**
     * 指定されたレコードを更新する。
     *
     * @param array $data 更新内容
     * @param int $seq SEQ
     * @return ResultInterface
     */
    public function saveUpdate($data, $seq)
    {
        $row = $this->find($seq)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_ClaimThreadManage ";
        $sql .= " SET ";
        $sql .= "     Rw1 = :Rw1 ";
        $sql .= " ,   Rw2 = :Rw2 ";
        $sql .= " ,   Rw3 = :Rw3 ";
        $sql .= " ,   Rw4 = :Rw4 ";
        $sql .= " ,   Rw5 = :Rw5 ";
        $sql .= " ,   Rw6 = :Rw6 ";
        $sql .= " ,   Rw7 = :Rw7 ";
        $sql .= " ,   Rw8 = :Rw8 ";
        $sql .= " ,   Rw9 = :Rw9 ";
        $sql .= " ,   Rw0 = :Rw0 ";
        $sql .= " ,   Smbc = :Smbc ";
        $sql .= " ,   Re = :Re ";
        $sql .= " ,   SttDate = :SttDate ";
        $sql .= " ,   EndDate = :EndDate ";
        $sql .= " WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
                ':Rw1' => $row['Rw1'],
                ':Rw2' => $row['Rw2'],
                ':Rw3' => $row['Rw3'],
                ':Rw4' => $row['Rw4'],
                ':Rw5' => $row['Rw5'],
                ':Rw6' => $row['Rw6'],
                ':Rw7' => $row['Rw7'],
                ':Rw8' => $row['Rw8'],
                ':Rw9' => $row['Rw9'],
                ':Rw0' => $row['Rw0'],
                ':Smbc' => $row['Smbc'],
                ':Re' => $row['Re'],
                ':SttDate' => $row['SttDate'],
                ':EndDate' => $row['EndDate'],
        );

        return $stm->execute($prm);
    }

    /**
     * 処理開始更新
     *
     * @see 全スレッドステータスの未処理(0)化、及び処理開始時刻の設定(現在時刻)と処理終了時刻のクリアを実施
     */
    public function updateForStart()
    {
        $this->saveUpdate(array ( 'Rw1' => 0, 'Rw2' => 0, 'Rw3' => 0, 'Rw4' => 0, 'Rw5' => 0, 'Rw6' => 0, 'Rw7' => 0, 'Rw8' => 0, 'Rw9' => 0, 'Rw0' => 0, 'Smbc' => 0, 'Re' => 0, 'SttDate' => date('Y-m-d H:i:s'), 'EndDate' => NULL), 1);
    }

    /**
     * スレッドステータス更新
     * (全スレッドが処理完了となる時、処理終了時刻の設定(現在時刻)と、trueを戻す)
     *
     * @param string $fldName フィールド名 例)"Rw1"
     * @param int $status ステータス(1:完了／9:エラー)
     * @param boolean $isNextIsSmbc (戻り引数)次はSmbc処理か？
     * @param boolean $isNextIsRe (戻り引数)次はRe処理か？
     * @return boolean(true:全処理完了／false:全処理未完了)
     */
    public function updateStatus($fldName, $status, &$isNextIsSmbc, &$isNextIsRe)
    {
        $isFinishAll = false;
        // (戻り引数のfalse初期化)
        $isNextIsSmbc = false;
        $isNextIsRe = false;

        $this->_adapter->getDriver()->getConnection()->beginTransaction();
        try {
            $row = $this->_adapter->query(" SELECT * FROM T_ClaimThreadManage WHERE Seq = 1 FOR UPDATE ")->execute(null)->current();

            if ($status == 1) {
                // (現時点の処理完了件数の確認)
                $finCount = 0;

                $finCount += (($row['Rw1'] == 1) ? 1 : 0);
                $finCount += (($row['Rw2'] == 1) ? 1 : 0);
                $finCount += (($row['Rw3'] == 1) ? 1 : 0);
                $finCount += (($row['Rw4'] == 1) ? 1 : 0);
                $finCount += (($row['Rw5'] == 1) ? 1 : 0);
                $finCount += (($row['Rw6'] == 1) ? 1 : 0);
                $finCount += (($row['Rw7'] == 1) ? 1 : 0);
                $finCount += (($row['Rw8'] == 1) ? 1 : 0);
                $finCount += (($row['Rw9'] == 1) ? 1 : 0);
                $finCount += (($row['Rw0'] == 1) ? 1 : 0);
                $finCount += (($row['Smbc'] == 1) ? 1 : 0);
                $finCount += (($row['Re'] == 1) ? 1 : 0);

                // (現時点の処理完了件数毎処理)
                if ($finCount == 11) {
                    // ＋1にて、全処理完了となるパターン
                    $this->saveUpdate(array ($fldName => $status, 'EndDate' => date('Y-m-d H:i:s')), 1);
                    $isFinishAll = true;
                }
                else {
                    // ＋1にて、全処理完了とならないパターン
                    $this->saveUpdate(array ($fldName => $status), 1);

                    $isNextIsSmbc = ($finCount == 9 ) ? true : false;
                    $isNextIsRe   = ($finCount == 10) ? true : false;
                }
            }
            else if ($status == 9) {
                // エラー通知時は、値のみをセット
                $this->saveUpdate(array ($fldName => $status), 1);
            }

            $this->_adapter->getDriver()->getConnection()->commit();
        }
        catch(\Exception $e) {
            $this->_adapter->getDriver()->getConnection()->rollback();
        }

        return $isFinishAll;
    }

    /**
     * 初回請求データ抽出の基本SQL取得
     *
     * @return string 初回請求データ抽出の基本SQL
     */
    public static function getBaseQueryClaim() {
        $sql =<<<EOQ
SELECT DISTINCT o.OrderSeq, o.SiteId, e.CreditTransferFlg, e.BillingAgentFlg, ec.RequestStatus, at.CreditTransferRequestFlg
,      (SELECT COUNT(1) FROM T_ClaimHistory WHERE OrderSeq = o.OrderSeq AND ValidFlg = 1) AS cntClaimHistory
,      (SELECT COUNT(1) FROM T_ClaimHistory WHERE OrderSeq = o.OrderSeq) AS cntClaimHistory2
,      CASE WHEN (SELECT COUNT(1) FROM T_OemClaimAccountInfo WHERE OrderSeq = o.OrderSeq) = 0 THEN 1
            ELSE (SELECT MAX(InnerSeq) + 1 FROM T_OemClaimAccountInfo WHERE OrderSeq = o.OrderSeq GROUP BY OrderSeq)
       END AS ocaiNextInnerSeq
,      (SELECT IFNULL(GROUP_CONCAT(OrderItemId), '') FROM T_OrderItems WHERE OrderSeq = o.OrderSeq AND DataClass = 1 AND TaxrateNotsetFlg = 1 AND TaxRate IS NULL) AS updTaxTrgtOrderItemId
,      (
       SELECT IFNULL(GROUP_CONCAT(oi.OrderItemId), '')
       FROM   T_Order o2
              INNER JOIN T_OrderItems oi ON (oi.OrderSeq = o2.OrderSeq)
       WHERE  o2.P_OrderSeq = o.OrderSeq AND oi.DataClass = 1 AND oi.TaxrateNotsetFlg = 1 AND oi.TaxRate IS NULL AND o2.P_OrderSeq <> o2.OrderSeq AND o2.Cnl_Status = 0
       ) AS updTaxTrgtOrderItemId2
,      (SELECT IFNULL(GROUP_CONCAT(Seq), '') FROM T_ClaimHistory WHERE OrderSeq = o.OrderSeq AND ValidFlg = 1) AS updTrgtClaimHistorySeq
,      (SELECT SUM(UseAmount) FROM T_Order WHERE Cnl_Status = 0 AND P_OrderSeq = o.OrderSeq) AS sumUseAmount
,      (SELECT IFNULL(GROUP_CONCAT(OrderSeq), '') FROM T_Order WHERE P_OrderSeq = o.OrderSeq) AS updTrgtConfirmWaitingFlgOrderSeq
,      (CASE WHEN o.OemId >= 1 THEN (SELECT COUNT(1) FROM T_ClaimControl WHERE ReissueClass <> 0 AND OrderSeq = o.OrderSeq) ELSE 0 END) AS cntReissueClassNot0
,      (SELECT IFNULL(MAX(Seq),-1) FROM T_MypageOrder WHERE OrderSeq = o.OrderSeq) AS maxMypageOrderSeq
,      (SELECT IFNULL(GROUP_CONCAT(Seq), '') FROM T_MypageOrder WHERE OrderSeq = o.OrderSeq) AS updTrgtMypageOrderSeq
,      (SELECT IFNULL(MAX(SearchPhone), '') FROM T_Customer WHERE OrderSeq = o.OrderSeq) AS custSearchPhone
,      IFNULL(o.OemId, 0) AS OemId
FROM   T_Order o
       INNER JOIN T_Enterprise e
               ON e.EnterpriseId = o.EnterpriseId
       INNER JOIN T_Site sit
               ON sit.SiteId = o.SiteId
       INNER JOIN T_Customer c
               ON c.OrderSeq = o.OrderSeq
       INNER JOIN ( SELECT t.P_OrderSeq
                          ,MAX(IFNULL(t.LetterClaimStopFlg, 0))   AS LetterClaimStopFlg        -- 最大のストップフラグが1の場合＝ストップしたい注文あり
                          ,MAX(t.ConfirmWaitingFlg)               AS ConfirmWaitingFlg         -- 最大の確定待ちフラグが1の場合＝確定待ち注文あり
                          ,MAX(p.ClearConditionDate)              AS ClearConditionDate        -- 立替条件クリア日
                          ,MIN(t.DataStatus)                      AS DataStatus                -- データステータス
                          ,SUM(t.UseAmount)                       AS UseAmount                 -- 利用額合計
                      FROM T_Order t
                           INNER JOIN T_PayingAndSales p
                                   ON p.OrderSeq = t.OrderSeq
                     WHERE t.Cnl_Status = 0
                       AND t.DataStatus IN (41, 51)
                    GROUP BY t.P_OrderSeq
                  ) po
               ON po.P_OrderSeq = o.OrderSeq
       LEFT OUTER JOIN T_ClaimControl clm
                    ON clm.OrderSeq = o.OrderSeq
       LEFT OUTER JOIN T_ClaimHistory ch
                    ON (ch.OrderSeq = o.OrderSeq)
       INNER JOIN T_EnterpriseCustomer ec
                    ON ec.EntCustSeq = c.EntCustSeq
       INNER JOIN AT_Order at
                    ON at.OrderSeq = o.OrderSeq
       LEFT JOIN M_SubscriberCode as ssc
                    ON ssc.ReceiptAgentId = sit.ReceiptAgentId AND ssc.SubscriberCode = sit.SubscriberCode
       INNER JOIN T_OemCvsAccount oca
                    ON IFNULL(o.OemId, 0) = oca.OemId
       INNER JOIN M_CvsReceiptAgent cra
                    ON oca.ReceiptAgentId = cra.ReceiptAgentId

WHERE  1 = 1
AND    (
           ( po.DataStatus =  41  AND IFNULL(e.SelfBillingMode, 0) <= 0                                                         ) -- 別送加盟店
        OR ( po.DataStatus =  41  AND IFNULL(e.SelfBillingMode, 0) >  0 AND sit.SelfBillingFlg = 0                              ) -- 同梱加盟店、別送サイト
        OR ( po.DataStatus =  41  AND IFNULL(e.SelfBillingMode, 0) >  0 AND sit.SelfBillingFlg = 1 AND o.ClaimSendingClass = 12 ) -- 同梱加盟店、同梱サイト、別送に送る指示あり
        OR ( po.DataStatus =  51  AND clm.ReissueClass <> 0                               )                                       -- 初回請求書再発行の指示あり
       )
AND    po.LetterClaimStopFlg = 0                                                                                                 -- 紙請求ストップフラグが１件も立っていないもの
AND    IFNULL(o.CombinedClaimTargetStatus, 0) IN (0, 91, 92)                                                                     -- 請求取りまとめ対象外 もしくは 請求取りまとめ済みのもの

EOQ;
        return $sql;
    }
}
