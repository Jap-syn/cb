<?php
namespace models\Logic\Smbcpa;

use Zend\Db\Adapter\Driver\ResultInterface;
use models\Logic\Smbcpa\Account\LogicSmbcpaAccountException;

/**
 * SMBCバーチャル口座取り扱いロジック
 */
class LogicSmbcpaAccount extends LogicSmbcpaCommon {
    /** クローズ事由指定定数：SMBCバーチャル口座入金 @var int */
    const CLOSE_BY_SMBCPA_RCPT = 9;

    /** クローズ事由指定定数：注文キャンセル @var int */
    const CLOSE_BY_CANCEL = 5;

    /** クローズ事由指定定数：コンビニ入金 @var int */
    const CLOSE_BY_CVS_RCPT = 1;

    /** クローズ事由指定定数：ゆうちょ入金 @var int */
    const CLOSE_BY_YUCHO_RCPT = 2;

    /** クローズ事由指定定数：銀行入金（SMBCバーチャル口座以外） @var int */
    const CLOSE_BY_BANK_RCPT = 3;

    /**
     * 指定SMBCバーチャル口座データをOEM請求口座更新向けに整備した連想配列を取得する
     *
     * @param int $accSeq SMBCバーチャル口座SEQ
     * @return array
     */
    public function getAccountDataForOemClaimAccountInfo($accSeq) {
        $q = <<<EOQ
SELECT
    smbcpa.BankCode      AS Bk_BankCode,
    acc.BranchCode       AS Bk_BranchCode,
    smbcpa.BankName      AS Bk_BankName,
    mst.SmbcpaBranchName AS Bk_BranchName,
    grp.DepositClass     AS BK_DepositClass,
    acc.AccountNumber    AS Bk_AccountNumber,
    acc.AccountHolder    AS Bk_AccountHolder,
    acc.AccountHolder    AS Bk_AccountHolderKn
FROM
    (SELECT * FROM T_SmbcpaAccount WHERE AccountSeq = :AccountSeq) acc INNER JOIN
    T_SmbcpaAccountGroup grp ON grp.AccountGroupId = acc.AccountGroupId INNER JOIN
    T_Smbcpa smbcpa ON smbcpa.SmbcpaId = grp.SmbcpaId LEFT OUTER JOIN
    M_SmbcpaBranch mst ON mst.SmbcpaBranchCode = acc.BranchCode
EOQ;
        $row = $this->_adapter->query($q)->execute(array(':AccountSeq' => (int)$accSeq))->current();
        return ($row) ? $row : null;
    }

    /**
     * 指定SMBCバーチャル口座に現在関連付けられている注文データを取得する
     *
     * @param int $accSeq SMBCバーチャル口座SEQ
     * @param null | boolean $raise_exception SMBCバーチャル口座や注文が見つからない場合に例外をスローするかのフラグ。
     *                                        省略時はtrueで、LogicSmbcpaAccountExceptionがスローされる
     * @return array | null
     */
    public function findCurrentOrder($accSeq, $raise_exception = true) {
        $account = $this->getSmbcpaAccountTable()->find($accSeq)->current();
        if(!$account) {
            // SMBCバーチャル口座見つからず
            if($raise_exception) {
                throw new LogicSmbcpaAccountException("SMBCバーチャル口座SEQ '%s' が見つかりませんでした", $accSeq);
            }
            return null;
        }

        $his =
            $this->getAccountUsageHistoryTable()->findRecentHistoryByAccountSeq($accSeq);
        if(!$his) {
            // 履歴見つからず → エラーにはしない
            return null;
        }

        return $this->fetchOrderData($his['OrderSeq'], $raise_exception);
    }

    /**
     * 指定注文向けにSMBCバーチャル口座を払い出す
     *
     * @param int $oseq 注文SEQ
     * @param null | boolean $history_check 強制口座解放履歴の有無チェックを行うかのフラグ。省略時はtrue（チェックする）。
     *                                      このパラメータにtrueが指定され、対象の注文に強制解放履歴があった場合、このメソッドは
     *                                      例外をスローする
     * @return array 払い出されたSMBCバーチャル口座データ
     */
    public function openAccount($oseq, $history_check = true) {
$this->debug(sprintf('[openAccount] method called. oseq = %s, history_check = %s', $oseq, $history_check ? 'true' : 'false'));
        $order = $this->fetchOrderData($oseq, true);
        $oseq = $order['OrderSeq'];
        $oemId = (int)$order['OemId'];

        $accTable = $this->getSmbcpaAccountTable();
        $hisTable = $this->getAccountUsageHistoryTable();

        if($history_check) {
            if($hisTable->countForceReleasedHistoryByOrderSeq($oseq) > 0) {
$this->info(sprintf('[openAccount:%s] cannot open account. (over limit history exists)', $oseq));
                // 期限超過による強制口座解放の履歴ありの場合
                throw new LogicSmbcpaAccountException(sprintf("注文SEQ '%s' は期限切れから2ヶ月以上経過している再請求7の履歴があります", $oseq));
            }

            if(!empty($this->findExpiredClaimPattern9($oseq))) {
$this->info(sprintf('[openAccount:%s] cannot open account. (over limit claim history exists)', $oseq));
                // 強制口座開放対象の請求履歴ありの場合
                // → バッチ処理による強制口座開放の実施の有無にかかわらず、強制開放対象の条件を満たしている場合は常に
                //   SMBCバーチャル口座を使用しないよう仕様変更（2015.3.30 eda）
                throw new LogicSmbcpaAccountException(sprintf("注文SEQ '%s' は期限切れから2ヶ月以上経過している再請求7の履歴があります", $oseq));
            }
        }

        // 既存請求中口座の抽出
        $accData = $accTable->fetchClaimingAccountByOrderSeq($oseq);
        $updateData = array('LastStatusChanged' => date('Y-m-d H:i:s'));
        if(!$accData) {
            // 既存請求中口座がない場合は新規に口座払出
            $accData = $accTable->fetchNextAccountByOemId($oemId);
            $updateData['Status'] = 1;
        }

        // 新規払出に失敗
        if(!$accData) {
$this->info(sprintf('[openAccount:%s] cannot open account.', $oseq));
            throw new LogicSmbcpaAccountException(sprintf("注文SEQ '%s' 向けにSMBCバーチャル口座を払い出せませんでした", $oseq));
        }

        $hisSeq = null;
        try {
            // 口座使用の履歴を追加
            $hisSeq = $hisTable->addHistory(\models\Table\TableSmbcpaAccountUsageHistory::HISTORY_TYPE_OPEN, $accData['AccountSeq'], $oseq);
            // 履歴挿入に成功したのでSMBCバーチャル口座を更新
            $accTable->saveUpdate($updateData, $accData['AccountSeq']);
        } catch(\Exception $err) {
$this->info(sprintf('[openAccount:%s] cannot open account or insert history. error = %s', $oseq, $err->getMessage()));
            if($hisSeq) {
                // 利用履歴を挿入していたらロールバックを試みる
                try {
                    $prevHisSeq = $hisTable->rollbackHistory($accData['AccountSeq']);
$this->debug(sprintf('[openAccount:%s] history rollbacked. current recent history = %s', $oseq, $prevHisSeq));
                } catch(\Exception $deleteError) {
$this->info(sprintf('[openAccount:%s] cannot delete history. hisSeq = error = %s', $oseq, $hisSeq, $deleteError->getMessage()));
                }
            }
            throw $err;
        }

$this->debug(sprintf('[openAccount:%s] account opened normally.', $oseq));
        // 更新されたSMBCバーチャル口座データを返却
        return $accTable->find($accData['AccountSeq'])->current();
    }

    /**
     * 指定注文向けにSMBCバーチャル口座を払い出す
     *
     * @param int $oseq 注文SEQ
     * @param array $exparams
     * @return array 払い出されたSMBCバーチャル口座データ
     */
    public function openAccountEx($oseq, $exparams) {
        $accTable = new \models\Table\TableSmbcpaAccount($this->_adapter);

        //NOTE. ｵﾘｼﾞﾅﾙ実装にある[history_check]は不要。※手動初回請求書再発行ができなくなります(20201027_1050)

        // SMBCﾊﾞｰﾁｬﾙ口座払出し対象外時は直ちに例外ｽﾛｰ
        if ((int)$exparams['reserveSmbcpaAccountSeq'] == -1) {
            throw new \Exception('invalid SmbcpaId specified.');
        }

        // 既存請求中口座が有効であればそれを、そうでなければ、予約した請求中口座を使用する
        $prevAccountSeq = (int)$exparams['prevSmbcpaAccountSeq'];
        $accountSeq = ($prevAccountSeq <> -1) ? $prevAccountSeq : (int)$exparams['reserveSmbcpaAccountSeq'];

        try {
            // 口座使用の履歴を追加
            $mdlsauh = new \models\Table\TableSmbcpaAccountUsageHistory($this->_adapter);
            // (当該Smbcpa口座の既存履歴の、MostRecentをすべて0に)
            if ($exparams['usageHistorySeqs'] != "") {
                $this->_adapter->query(" UPDATE T_SmbcpaAccountUsageHistory SET MostRecent = 0 WHERE UsageHistorySeq IN ( " . $exparams['usageHistorySeqs'] . " ) ")->execute(null);
            }
            // (口座使用履歴追加)
            $data = array(
                    'AccountSeq'    => $accountSeq,
                    'UsedDate'      => date('Y-m-d H:i:s'),
                    'MostRecent'    => 1,
                    'Type'          => \models\Table\TableSmbcpaAccountUsageHistory::HISTORY_TYPE_OPEN,
                    'OrderSeq'      => $oseq,
            );
            $mdlsauh->saveNew($data);

            // 履歴挿入に成功したのでSMBCバーチャル口座を更新
            $this->_adapter->query(" UPDATE T_SmbcpaAccount SET LastStatusChanged = :LastStatusChanged " . (($prevAccountSeq == -1) ? ", Status = 1 " : "") . " WHERE AccountSeq = :AccountSeq "
                    )->execute(array(':LastStatusChanged' => date('Y-m-d H:i:s'), ':AccountSeq' => $accountSeq));
        } catch(\Exception $err) {
            //NOTE. ｵﾘｼﾞﾅﾙ実装にある[履歴ﾛｰﾙﾊﾞｯｸ処理]は不要。※TableSmbcpaAccount::saveUpdateでの例外などﾐｽｺｰﾃﾞｨﾝｸﾞを除いてありえない為(20201027_1050)
            throw $err;
        }

        // 更新されたSMBCバーチャル口座データを返却
        return $accTable->find($accountSeq)->current();
    }

    /**
     * 指定注文向けの請求中SMBCバーチャル口座をクローズにする。
     * 指定注文でSMBCバーチャル口座請求を行っていない場合、このメソッドはnullを返す
     *
     * @param int $oseq 注文SEQ
     * @param null | int $closeReason クローズ事由を示す整数値。省略時はCLOSE_BY_SMBCPA_RCPT
     * @param null | string $closeMemo クローズ時の任意メモ
     * @return array | null クローズされたSMBCバーチャル口座データ
     */
    public function closeAccount($oseq, $closeReason = self::CLOSE_BY_SMBCPA_RCPT, $closeMemo = null) {
$this->debug(sprintf('[closeAccount] method called. oseq = %s, closeReason = %s, closeMemo = %s', $oseq, $closeReason, $closeMemo));
        $order = $this->fetchOrderData($oseq, true);
        $oseq = $order['OrderSeq'];

        $accTable = $this->getSmbcpaAccountTable();
        $hisTable = $this->getAccountUsageHistoryTable();

        // 既存請求中口座の抽出
        $accData = $accTable->fetchClaimingAccountByOrderSeq($oseq);
        if($accData) {
            // 請求中口座が見つかった場合のみ
            $accSeq = $accData['AccountSeq'];
            $updateData = array(
                'LastStatusChanged' => date('Y-m-d H:i:s'),
                'Status' => -1  // 口座クローズに設定
            );

            // クローズ事由を整備
            $closeReason = $closeReason === null ? self::CLOSE_BY_SMBCPA_RCPT : ((int)$closeReason);

            $hisSeq = null;
            try {
                // 口座クローズの履歴を追加
                $hisSeq = $hisTable->addHistory(\models\Table\TableSmbcpaAccountUsageHistory::HISTORY_TYPE_PAY, $accSeq, $oseq,
                                                $closeReason, $closeMemo);
                // 履歴挿入に成功したのでSMBCバーチャル口座のステータスを更新
                $accTable->saveUpdate($updateData, $accSeq);

                // 同一注文・同一口座の保留中入金通知の削除を試みる
                try {
                    $this->getPaymentNotificationTable()
                        ->deletePendingNotifications($oseq,$accSeq,sprintf('[口座クローズにより削除]%s', strlen($closeMemo) ? sprintf('[理由：%s]', $closeMemo) : ''));
                } catch(\Exception $sweepError) {
                    // ロギングのみで例外を無視
$this->info(sprintf('[closeAccount:%s] cannot clear pending notifications, but closing process successful. error = %s', $oseq, $sweepError->getMessage()));
                }
            } catch(\Exception $err) {
$this->info(sprintf('[closeAccount:%s] cannot close account or insert history. error = %s', $oseq, $err->getMessage()));
                if($hisSeq) {
                    // 利用履歴を挿入していたらロールバックを試みる
                    try {
                        $prevHisSeq = $hisTable->rollbackHistory($accData['AccountSeq']);
$this->debug(sprintf('[closeAccount:%s] history rollbacked. current recent history = %s', $oseq, $prevHisSeq));
                    } catch(\Exception $deleteError) {
$this->info(sprintf('[closeAccount:%s] cannot delete history. hisSeq = error = %s', $oseq, $hisSeq, $deleteError->getMessage()));
                    }
                }
                throw $err;
            }
$this->debug(sprintf('[closeAccount:%s] account closed normally.', $oseq));
            // 更新されたSMBCバーチャル口座データを返却
            return $accTable->find($accSeq)->current();
        }
        // 該当する口座データが見つからない場合はnullを返す
        return null;
    }

    /**
     * 指定注文向けでクローズになっているSMBCバーチャル口座を解放する。
     * 指定注文でSMBCバーチャル口座がクローズになっていない場合、このメソッドはnullを返す
     *
     * @param int $oseq 注文SEQ
     * @return array | null 開放されたSMBCバーチャル口座データ
     */
    public function releaseAccount($oseq) {
$this->debug(sprintf('[releaseAccount] method called. oseq = %s', $oseq));
        $order = $this->fetchOrderData($oseq, true);
        $oseq = $order['OrderSeq'];

        $accTable = $this->getSmbcpaAccountTable();
        $hisTable = $this->getAccountUsageHistoryTable();

        // 既存クローズ済み口座の抽出
        $accData = $accTable->fetchClosedAccountByOrderSeq($oseq);
        if($accData) {
            // クローズ済み口座が見つかった場合のみ
            $accSeq = $accData['AccountSeq'];
            $updateData = array(
                'LastStatusChanged' => date('Y-m-d H:i:s'),
                'Status' => 0   // 利用可能に設定
            );
            $hisSeq = null;
            try {
                // 口座解放の履歴を追加
                $hisSeq = $hisTable->addHistory(\models\Table\TableSmbcpaAccountUsageHistory::HISTORY_TYPE_RELEASE, $accSeq, $oseq);
                // 履歴挿入に成功したのでSMBCバーチャル口座のステータスを更新
                $accTable->saveUpdate($updateData, $accSeq);
            } catch(\Exception $err) {
$this->info(sprintf('[releaseAccount:%s] cannot release account or insert history. error = %s', $oseq, $err->getMessage()));
                if($hisSeq) {
                    // 利用履歴を挿入していたらロールバックを試みる
                    try {
                        $prevHisSeq = $hisTable->rollbackHistory($accData['AccountSeq']);
$this->debug(sprintf('[releaseAccount:%s] history rollbacked. current recent history = %s', $oseq, $prevHisSeq));
                    } catch(\Exception $deleteError) {
$this->info(sprintf('[releaseAccount:%s] cannot delete history. hisSeq = error = %s', $oseq, $hisSeq, $deleteError->getMessage()));
                    }
                }
                throw $err;
            }
$this->debug(sprintf('[releaseAccount:%s] account released normally.', $oseq));
            // 更新されたSMBCバーチャル口座データを返却
            return $accTable->find($accSeq)->current();
        }
        // 該当する口座データが見つからない場合はnullを返す
        return null;
    }

    /**
     * 指定注文向けで空き状態になっていない口座を強制解放する
     * 指定注文でSMBCバーチャル口座の状態が強制解放可能な状態になっていない場合、このメソッドはnullを返す
     *
     * @param int $oseq 注文SEQ
     * @return array | null 強制開放されたSMBCバーチャル口座データ
     */
    public function forceReleaseAccount($oseq) {
$this->debug(sprintf('[forceReleaseAccount] method called. oseq = %s', $oseq));
        $order = $this->fetchOrderData($oseq, true);
        $oseq = $order['OrderSeq'];

        $accTable = $this->getSmbcpaAccountTable();
        $hisTable = $this->getAccountUsageHistoryTable();

        // 請求中またはクローズ済みの口座を抽出
        $accData = $accTable->fetchNonBlankAccountByOrderSeq($oseq);
        if($accData) {
            // 該当する口座が見つかった場合のみ
            $accSeq = $accData['AccountSeq'];
            $updateData = array(
                'LastStatusChanged' => date('Y-m-d H:i:s'),
                'Status' => 0   // 利用可能に設定
            );
            $hisSeq = null;
            try {
                // 強制解放の履歴を追加
                $hisSeq = $hisTable->addHistory(\models\Table\TableSmbcpaAccountUsageHistory::HISTORY_TYPE_FORCE_RELEASE, $accSeq, $oseq);
                // 履歴挿入に成功したのでSMBCバーチャル口座のステータスを更新
                $accTable->saveUpdate($updateData, $accSeq);
            } catch(\Exception $err) {
$this->info(sprintf('[forceReleaseAccount:%s] cannot release account or insert history. error = %s', $oseq, $err->getMessage()));
                if($hisSeq) {
                    // 利用履歴を挿入していたらロールバックを試みる
                    try {
                        $prevHisSeq = $hisTable->rollbackHistory($accData['AccountSeq']);
$this->debug(sprintf('[forceReleaseAccount:%s] history rollbacked. current recent history = %s', $oseq, $prevHisSeq));
                    } catch(\Exception $deleteError) {
$this->info(sprintf('[forceReleaseAccount:%s] cannot delete history. hisSeq = error = %s', $oseq, $hisSeq, $deleteError->getMessage()));
                    }
                }
                throw $err;
            }
$this->debug(sprintf('[forceReleaseAccount:%s] account released normally.', $oseq));
            // 更新されたSMBCバーチャル口座データを返却
            return $accTable->find($accSeq)->current();
        }
        // 該当する口座データが見つからない場合はnullを返す
        return null;
    }

    /**
     * 注文データを取得する
     *
     * @access protected
     * @param int $oseq 注文SEQ
     * @param null | bool $raise_exception 注文データが見つからない場合に例外をスローするかのフラグ。省略時はfalse
     * @return array | null
     */
    protected function fetchOrderData($oseq, $raise_exception = false) {
        $order = $this->getOrderTable()->find($oseq)->current();
        if($raise_exception && !$order) {
            // 指定注文が見つからない
            throw new LogicSmbcpaAccountException(sprintf("注文SEQ '%s' の注文が見つかりません", $oseq));
        }

        return $order;
    }

    /**
     * 請求待ちでない注文に関連付けられている請求中口座の件数をカウントする
     *
     * @return int
     */
    public function countContradictedAccounts() {
        $q = <<<EOQ
SELECT
    COUNT(*) AS cnt
FROM
    T_SmbcpaAccount acc INNER JOIN
    T_SmbcpaAccountGroup grp ON grp.AccountGroupId = acc.AccountGroupId INNER JOIN
    T_Smbcpa smbcpa ON smbcpa.SmbcpaId = acc.SmbcpaId INNER JOIN
    (SELECT 0 AS OemId, 'キャッチボール' AS OemNameKj UNION ALL
     SELECT OemId, OemNameKj FROM T_Oem) oem ON oem.OemId = smbcpa.OemId INNER JOIN
    T_SmbcpaAccountUsageHistory his ON his.AccountSeq = acc.AccountSeq INNER JOIN
    T_Order ord ON ord.OrderSeq = his.OrderSeq
WHERE
    his.MostRecent = 1 AND
    ord.Cnl_Status = 0 AND
    ord.Rct_Status = 1 AND
    acc.Status = 1
EOQ;
        $row = $this->_adapter->query($q)->execute(null)->current();
        return ($row) ? $row['cnt'] : 0;
    }

    /**
     * 請求待ちでない注文に関連付けられている請求中口座の情報をすべて取得する
     *
     * @return ResultInterface
     */
    public function fetchContradictedAccounts() {
        $q = <<<EOQ
SELECT
    acc.AccountSeq,
    acc.BranchCode,
    acc.AccountNumber,
    acc.AccountHolder,
    acc.Status,
    acc.LastStatusChanged,
    his.Type,
    his.OrderSeq,
    ord.OrderId,
    ord.DataStatus,
    ord.Cnl_Status,
    ord.CloseReason,
    ord.Rct_Status,
   rc.ReceiptDate AS Rct_ReceiptDate,
   rc.ReceiptProcessDate AS Rct_ReceiptProcessDate,
   rc.ReceiptClass AS Rct_ReceiptMethod
FROM
    T_SmbcpaAccount acc INNER JOIN
    T_SmbcpaAccountGroup grp ON grp.AccountGroupId = acc.AccountGroupId INNER JOIN
    T_Smbcpa smbcpa ON smbcpa.SmbcpaId = acc.SmbcpaId INNER JOIN
    (SELECT 0 AS OemId, 'キャッチボール' AS OemNameKj UNION ALL
     SELECT OemId, OemNameKj FROM T_Oem) oem ON oem.OemId = smbcpa.OemId INNER JOIN
    T_SmbcpaAccountUsageHistory his ON his.AccountSeq = acc.AccountSeq INNER JOIN
    T_Order ord ON ord.OrderSeq = his.OrderSeq INNER JOIN
    T_ClaimControl cc ON ord.OrderSeq = cc.OrderSeq INNER JOIN
    T_ReceiptControl rc ON cc.LastReceiptSeq = rc.ReceiptSeq
WHERE
    his.MostRecent = 1 AND
    ord.Cnl_Status = 0 AND
    ord.Rct_Status = 1 AND
    acc.Status = 1 AND
    cc.ClaimedBalance <= 0
ORDER BY
    acc.AccountSeq
EOQ;
        return $this->_adapter->query($q)->execute(null);
    }

    /**
     * 請求中でない注文に関連付けられている口座をクリーンナップする
     *
     * @param null | bool $useTransaction メソッド内部でトランザクションを利用するかのフラグ。省略時はtrue。
     *                                    呼出し元またはその上位のコードでトランザクションを使用している場合はfalseを指定する
     */
    public function sweepContradictedAccounts($useTransaction = true) {
$this->debug('[sweepContradictedAccounts] method called.');
        if(!$this->countContradictedAccounts()) {
$this->debug('[sweepContradictedAccounts] no data.');
            return;
        }

        $count = 0;
        $methMap = array(
            '1' => 'コンビニ',
            '2' => 'ゆうちょ',
            '3' => '銀行'
        );
        $ri = $this->fetchContradictedAccounts();
        foreach($ri as $row) {
            $method = isset($methMap[$row['Rct_ReceiptMethod']]) ?
                $methMap[$row['Rct_ReceiptMethod']] :
                sprintf('不明(%s)', $row['Rct_ReceiptMethod']);
            if($useTransaction) $this->_adapter->getDriver()->getConnection()->beginTransaction();
            try {
                // 口座をクローズする
$this->debug(sprintf('[sweepContradictedAccounts:%s] closing account for %s (ReceiptMethod = %s)', $row['AccountSeq'], $row['OrderSeq'], $method));
                $this->closeAccount($row['OrderSeq'], $row['Rct_ReceiptMethod'], sprintf('%s入金によりクローズ', $method));
$this->debug(sprintf('[sweepContradictedAccounts:%s] close done.', $row['AccountSeq']));

                $count++;
                if($useTransaction) $this->_adapter->getDriver()->getConnection()->commit();
            } catch(\Exception $err) {
$this->info(sprintf('[sweepContradictedAccounts:%s] an error has occured. error = %s', $err->getMessage()));
                if($useTransaction) $this->_adapter->getDriver()->getConnection()->rollBack();
                throw $err;
            }
        }
$this->debug(sprintf('[sweepContradictedAccounts] completed normally. count = %s', $count));
    }

    /**
     * 指定注文に対して入金保留になっているSMBCバーチャル口座を検索する。
     * 戻り値はT_SmbcpaAccountのすべての情報に加え、入金通知の入金額／受信日時／最終処理日時が含まれる。
     * 指定注文に対する請求中口座がない場合はnullを返す
     *
     * @param int $oseq 注文SEQ
     * @return array | null
     */
    public function findPendingAccountByOrderSeq($oseq) {
        $q = <<<EOQ
SELECT
    acc.*,
    nf.NotificationSeq,
    nf.ReceiptAmount,
    nf.ReceivedDate,
    nf.LastProcessDate
FROM
    T_SmbcpaAccount acc INNER JOIN
    T_SmbcpaAccountUsageHistory his ON (his.AccountSeq = acc.AccountSeq AND his.MostRecent = 1) INNER JOIN
    T_SmbcpaPaymentNotification nf ON nf.AccountSeq = acc.AccountSeq
WHERE
    IFNULL(nf.DeleteFlg, 0) = 0 AND
    nf.Status = 3 AND
    acc.Status = 1 AND
    nf.OrderSeq = :OrderSeq
ORDER BY
    nf.ReceivedDate DESC
EOQ;
        $row = $this->_adapter->query($q)->execute(array(':OrderSeq' => $oseq))->current();
        return ($row) ? $row : null;
    }

    /**
     * 指定日よりも前にクローズ状態になったSMBCバーチャル口座を取得する
     *
     * @param string $date 基準日付。yyyy-MM-dd形式
     * @return ResultInterface
     * @see 戻り値が[ResultInterface]であることに注意する
     */
    public function findAccountsForReleaseByDate($date) {
        $q = <<<EOQ
SELECT
    MAX(ord.OrderSeq) as OrderSeq,
    ord.OrderId,
    ord.DataStatus,
    ord.Cnl_Status,
    ord.CloseReason,
    ord.Rct_Status,
    acc.*,
    his.*
FROM
    T_SmbcpaAccount acc INNER JOIN
    T_SmbcpaAccountUsageHistory his ON (his.AccountSeq = acc.AccountSeq AND his.MostRecent = 1) INNER JOIN
    T_Order ord ON ord.OrderSeq = his.OrderSeq LEFT OUTER JOIN
    T_ReceiptControl rc ON rc.OrderSeq = ord.OrderSeq
WHERE
    acc.Status = :acc_status AND
    his.Type = :his_type AND
    DATE_FORMAT(acc.LastStatusChanged, '%Y-%m-%d') < :date
GROUP BY
    ord.OrderSeq
ORDER BY
    rc.ReceiptProcessDate,
    acc.LastStatusChanged,
    his.UsageHistorySeq
EOQ;
        $params = array(
            'acc_status' => \models\Table\TableSmbcpaAccount::ACCOUNT_STATUS_CLOSED,
            'his_type' => \models\Table\TableSmbcpaAccountUsageHistory::HISTORY_TYPE_PAY,
            'date' => $date
        );
        return $this->_adapter->query($q)->execute($params);
    }

    /**
     * 複数の注文に対して口座オープンされた履歴があるSMBCバーチャル口座情報を取得する。
     * 戻り値はSMBCバーチャル口座SEQをキーとして、サブキー'count'に重複件数、'list'に重複情報のリストを格納する。
     * 重複情報リストは以下の値を持つ。
     *  - AccountSeq : SMBCバーチャル口座SEQ
     *  - OrderSeq : 注文SEQ
     *  - SmbcpaBankAccount : 当該SMBCバーチャル口座の支店コード-口座番号形式の口座情報
     *  - ClaimedBankAccount : 当該注文の最終請求に使用されている銀行口座の支店コード-口座番号形式の情報
     *  - ClaimedBankName : 当該注文の最終請求に使用されている銀行名
     *  - ClaimedBranchName : 当該注文の最終請求に使用されている支店名
     *  - UsedDate : 口座オープン日時
     *  - MostRecent : このSMBCバーチャル口座の最新履歴であるか
     *  - Type : 口座履歴種別。1のみ含まれる
     *  - DeleteFlg : SMBCバーチャル口座の削除フラグ。0のみ含まれる
     *  - OrderId : 当該注文の注文ID
     *  - NameKj : 当該注文の請求先氏名
     *  - DataStatus : 当該注文のDataStatus
     *  - Cnl_Status : 当該注文のキャンセル状況
     *  - CloseReason : 当該注文のクローズ理由
     *
     * 抽出条件は以下の通り。
     *  - 重複候補履歴があるSMBCバーチャル口座。重複候補履歴は以下の条件で抽出される
     *    - 同一口座で未キャンセル・請求書印刷待ちまたは入金待ちの注文に対して口座オープンされている削除されていない履歴
     *    - 同一注文内での最新の利用履歴
     *  - 当該注文内での最新の請求履歴で使用されている銀行口座が当該SMBCバーチャル口座と同一
     *
     * @return array
     */
    public function findMultiOpenedAccounts($exclude_single_order = false) {
        $q = <<<EOQ
SELECT
    auh.AccountSeq,
    sum.OrderSeq,
    (auh.UsageHistorySeq + 0) AS UsageHistorySeq,
    CONCAT(
        CAST(acc.BranchCode AS CHAR), '-',
        CAST(acc.AccountNumber AS CHAR)
    ) AS SmbcpaBankAccount,
    CONCAT(
        CAST(oca.Bk_BranchCode AS CHAR), '-',
        CAST(oca.Bk_AccountNumber AS CHAR)
    ) AS ClaimedBankAccount,
    oca.Bk_BankName AS ClaimedBankName,
    oca.Bk_BranchName AS ClaimedBranchName,
    auh.UsedDate,
    auh.MostRecent,
    auh.Type,
    auh.DeleteFlg,
    ord.OrderId,
    sum.NameKj,
    ord.DataStatus,
    ord.Cnl_Status,
    ord.CloseReason,
    (CASE
        WHEN his.EnterpriseBillingCode IS NULL THEN 0
        ELSE 1
    END) AS IsSelfBilling
FROM
    T_SmbcpaAccount acc INNER JOIN
    T_SmbcpaAccountUsageHistory auh ON auh.AccountSeq = acc.AccountSeq INNER JOIN
    T_Order ord ON ord.OrderSeq = auh.OrderSeq INNER JOIN
    T_ClaimHistory his ON his.OrderSeq = ord.OrderSeq INNER JOIN
    T_OemClaimAccountInfo oca ON oca.ClaimHistorySeq = his.Seq INNER JOIN
    (
        SELECT
            his.AccountSeq,
            COUNT(*) AS HisCount,
            GROUP_CONCAT(DISTINCT CAST(his.OrderSeq AS CHAR)) AS OrderSeqs
        FROM
            T_SmbcpaAccountUsageHistory his INNER JOIN
            T_Order ord2 ON ord2.OrderSeq = his.OrderSeq
        WHERE
            his.Type = 1 AND
            his.DeleteFlg = 0 AND
            ord2.Cnl_Status = 0 AND
            ord2.DataStatus IN (41, 51)
        GROUP BY
            his.AccountSeq
        HAVING
            INSTR(CAST(OrderSeqs AS CHAR), ',') > 0
    ) dup ON dup.AccountSeq = acc.AccountSeq INNER JOIN
    T_OrderSummary sum ON sum.OrderSeq = ord.OrderSeq
WHERE
    his.Seq = (SELECT MAX(Seq) FROM T_ClaimHistory WHERE OrderSeq = ord.OrderSeq) AND
    auh.UsageHistorySeq = (SELECT MAX(UsageHistorySeq) FROM T_SmbcpaAccountUsageHistory WHERE OrderSeq = ord.OrderSeq)
HAVING
    SmbcpaBankAccount = ClaimedBankAccount
ORDER BY
    CONCAT(
        CAST(acc.BranchCode AS CHAR), '-',
        CAST(acc.AccountNumber AS CHAR)
    ),
    auh.UsageHistorySeq DESC
EOQ;
        $list = array();
        $ri = $this->_adapter->query($q)->execute(null);
        foreach($ri as $row) {
            $key = $row['AccountSeq'];
            if(!isset($list[$key])) {
                $list[$key] = array(
                    'account_number' => null,
                    'count' => 0,
                    'list' => array()
                );
            }
            $list[$key]['account_number'] = $row['SmbcpaBankAccount'];
            $list[$key]['list'][] = $row;
            $list[$key]['count']++;
        }
        if(!$exclude_single_order) {
            return $list;
        }
        $results = array();
        foreach($list as $key => $info) {
            if($info['count'] > 1) {
                $results[$key] = $info;
            }
        }
        return $results;
    }

    /**
     * 指定注文の、請求期限から一定日数以上延滞している再請求7の請求履歴を取得する。
     * 「一定日数」はシステムプロパティに設定されている「再請求7期限超過後の強制口座開放までの猶予日数」で、
     * 標準では65日となる。
     *
     * @access protected
     * @param int $oseq 注文SEQ
     * @return array
     */
    protected function findExpiredClaimPattern9($oseq) {
        $config = new LogicSmbcpaConfig($this->getDbAdapter());

        $oseq = (int)$oseq;

        $expire_days = $config->getForceReleaseOverReclaim7LimitInterval();
        return $this->getClaimHistoryTable()->findExpiredClaimPattern9($oseq, $expire_days);
    }
}
