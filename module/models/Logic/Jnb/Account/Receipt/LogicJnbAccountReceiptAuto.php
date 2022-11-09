<?php
namespace models\Logic\Jnb\Account\Receipt;

use models\Logic\Jnb\Account\LogicJnbAccountReceipt;
use models\Table\TableUser;

/**
 * JNBバーチャル口座の自動入金入金処理ロジック
 */
class LogicJnbAccountReceiptAuto extends LogicJnbAccountReceipt {
    /**
     * 自動入金処理対象の入金通知管理データに対して自動入金処理を適用する
     *
     * @return array 入金処理結果コードをキーとしてそれぞれの確定件数を格納した連想配列
     */
    public function receiptAll() {
        $this->debug('[receiptAll] method called.');

        $start = microtime(true);
        $results = array(
            self::RCPT_RESULT_EXEMPT => 0,
            self::RCPT_RESULT_PEIDING => 0,
            self::RCPT_RESULT_SUCCESS => 0,
            self::RCPT_RESULT_ERROR => 0
        );

        // ユーザーID
        $mdlu = new TableUser($this->_adapter);
        $userId = $mdlu->getUserId(TableUser::USERCLASS_SYSTEM, TableUser::SEQ_BATCH_USER);

        // 処理対象が0件になるまでループ
        while(($count = $this->getPaymentNotificationTable()->countReceiptTargets()) > 0) {

                $this->debug(sprintf('[receiptAll] target count = %s', $count));

            $has_error = false;
            foreach($this->getPaymentNotificationTable()->fetchReceiptTargets() as $row) {
                $seq = $row['NotificationSeq'];
                $s_start = microtime(true);
                $this->_adapter->getDriver()->getConnection()->beginTransaction();
                try {

                        $this->debug(sprintf('[receiptAll] do single process. seq = %s',$seq ));

                    // 個別処理
                    $code = $this->_receipt($row, $userId);
                    $results[$code]++;
                    $this->_adapter->getDriver()->getConnection()->commit();

                        $this->debug(sprintf(
                                             '[receiptAll] single process done. result = %s, elapsed time = %s',
                                             self::getReceiptResultLabel($code), (microtime(true) - $s_start)));

                } catch(\Exception $err) {

                        $this->info(sprintf(
                                            '[receiptAll] an error has occured. seq = %s, elapsed time = %s, error = %s',
                                            $seq, (microtime(true) - $s_start), $err->getMessage()));

$this->err($err->getTraceAsString());

                    $this->_adapter->getDriver()->getConnection()->rollBack();
                    // 例外が発生したらエラーありでリストループを中断
                    // → receiptメソッド内でトランザクションを形成しているのでここでは単に例外処理のみ行う
                    $results[self::RCPT_RESULT_ERROR]++;
                    $has_error = true;
                    break;
                }
            }
            // エラーありでリストループが終了していたら残件数に関わらず中断
            if($has_error) break;
        }

            $this->debug(sprintf(
                                 '[receiptAll] process completed. elapsed time = %s, success = %s, pending = %s, exempt = %s, error = %s',
                                 (microtime(true) - $start),
                                 $results[self::RCPT_RESULT_SUCCESS],
                                 $results[self::RCPT_RESULT_PEIDING],
                                 $results[self::RCPT_RESULT_EXEMPT],
                                 $results[self::RCPT_RESULT_ERROR]));

        return $results;
    }

    /**
     * 指定の入金通知管理データに対して自動入金処理を実行する
     *
     * @param int $seq 入金通知管理SEQ
     * @return int 入金処理結果
     */
    public function receipt($seq) {
        $this->debug(sprintf('[receipt:%s] method called.', $seq));

        $start = microtime(true);

        // ユーザーID
        $mdlu = new TableUser($this->_adapter);
        $userId = $mdlu->getUserId(TableUser::USERCLASS_SYSTEM, TableUser::SEQ_BATCH_USER);

        $this->_adapter->getDriver()->getConnection()->beginTransaction();
        try {
            $nfData = $this->getPaymentNotificationTable()->find($seq)->current();
            if(!$nfData || $nfData['Status'] != 2) throw new \Exception('invalid notification seq specified.');
            $code = $this->_receipt($nfData, $userId);

            $this->debug(sprintf('[receipt:%s] process done. result = %s, elapsed time = %s', $seq, self::getReceiptResultLabel($code), (microtime(true) - $start)));

            $this->_adapter->getDriver()->getConnection()->commit();
            return $code;
        } catch(\Exception $err) {

                $this->info(sprintf('[receipt:%s] an error has occured. elapsed time = %s, error = %s',
                                    $seq, (microtime(true) - $start), $err->getMessage()));

            $this->_adapter->getDriver()->getConnection()->rollBack();
            return self::RCPT_RESULT_ERROR;
        }
    }

    /**
     * 自動入金処理を実行する
     *
     * @access protected
     * @param array $nfData 入金通知管理データ
     * @param int $userId ユーザーID
     * @return int 入金処理結果
     */
    protected function _receipt(array $nfData, $userId) {
        /** 入金通知管理SEQ @var int */
        $seq = $nfData['NotificationSeq'];
        /** 入金額 @var int */
        $amount = $nfData['ReceiptAmount'];
        /** JNB口座SEQ @var int */
        $accSeq = null;
        /** 注文SEQ @var int */
        $oseq = null;

        $this->debug(sprintf(
                             '[_receipt:%s] ReqBranchCode = %s, ReqAccountNumber = %s, ReceiptAmount = %s',
                             $seq,
                             $nfData['ReqBranchCode'], $nfData['ReqAccountNumber'], $amount));

        // STEP1:口座取得
        /** JNB口座テーブル */
        $accTable = $this->getJnbAccountTable();
        /** JNB口座データ */
        $account = $accTable->findAccount($nfData['ReqBranchCode'], $nfData['ReqAccountNumber']);
        if(!$account) {
            // 口座が見つからない場合
            return $this->doneUnreceipt($seq, self::RESULT_TYPE_1_ACCOUNT_NOT_FOUND, $oseq, $accSeq);
        }
        // 上位JNB契約の有効チェック
        if(!$this->getJnbTable()->find($account['JnbId'])->current()['ValidFlg']) {
            // 無効なJNB IDの場合
            return $this->doneUnreceipt($seq, self::RESULT_TYPE_10_DISABLED_JNB_ID, $oseq, $accSeq);
        }
        // 上位グループの返却状態をチェック
        if($this->getJnbGroupTable()->find($account['AccountGroupId'])->current()['ReturnedFlg']) {
            // 返却済み口座の場合
            return $this->doneUnreceipt($seq, self::RESULT_TYPE_9_RETURNED_ACCOUNT, $oseq, $accSeq);
        }

        // -------------------------------------------------------------------------------- 口座確定
        $accSeq = $account['AccountSeq'];

        $this->debug(sprintf('[_receipt:%s] AccountSeq = %s', $seq, $accSeq));

        // STEP2:履歴件数取得
        if(!$this->getAccountUsageHistoryTable()->countHistoriesByAccountSeq($accSeq)) {
            // 利用実績なし
            return $this->doneUnreceipt($seq, self::RESULT_TYPE_2_HISTORY_NOT_FOUND, $oseq, $accSeq);
        }

        // STEP3:口座状態をチェック
        if($account['Status'] != 1) {
            // 口座が請求中ではない
            return $this->doneReceiptPending($seq, self::RESULT_TYPE_3_EXEMPT_ACCOUNT, $oseq, $accSeq);
        }

        // STEP4:関連注文取得
        /** 入金対象注文データ */
        $order = $this->getAccountLogic()->findCurrentOrder($accSeq, false);
        if(!$order) {
            // 関連注文が見つからない
            return $this->doneReceiptPending($seq, self::RESULT_TYPE_4_ORDER_NOT_FOUND, $oseq, $accSeq);
        }

        // -----------------------------------------------------------------------------　対象注文確定
        $oseq = $order['OrderSeq'];

        $this->debug(sprintf('[_receipt:%s] OrderSeq = %s', $seq, $oseq));

        // STEP5:注文状態チェック
        if($order['Cnl_Status'] != 0 || $order['DataStatus'] != 51) {
            // 入金待ち状態ではない
            return $this->doneReceiptPending($seq, self::RESULT_TYPE_5_EXEMPT_ORDER, $oseq, $accSeq);
        }

        // STEP6:分割支払済み金額のチェック
        if($this->getInstallmentPlanAmount($order['OrderSeq']) > 0) {
            // 分割支払済み入金がある
            return $this->doneReceiptPending($seq, self::RESULT_TYPE_7_SPLIT_PAID, $oseq, $accSeq);
        }

        // STEP7:入金額チェック
        $amount_range = $this->getValidAmountRange($oseq);
        if($amount < $amount_range['min']) {
            // 入金許容範囲下限以下
            return $this->doneReceiptPending($seq, self::RESULT_TYPE_6_EXEMPT_AMOUNT, $oseq, $accSeq,
                                             sprintf('不足入金 (%s 未満)', f_nf($amount_range['min'], '#,##0')));
        }
        if($amount > $amount_range['max']) {
            // 入金許容範囲上限以上
            return $this->doneReceiptPending($seq, self::RESULT_TYPE_6_EXEMPT_AMOUNT, $oseq, $accSeq,
                                             sprintf('過剰入金 (%s 超)', f_nf($amount_range['max'], '#,##0')));
        }

        // ----------------------------------------------------------------------------- 入金実行確定

        // 入金確定処理実行
        return $this->doneReceipt($seq, $oseq, $accSeq, $userId);
    }

    /**
     * 指定注文の有効入金額範囲を取得する。
     * 戻り値の連想配列はキー'min'に下限金額、'max'に上限金額を格納する。
     * 上限と下限の定義は以下の通り。
     *
     *   上限：最終請求の請求金額
     *   下限：
     *       初回～再3まで発行済み → 初回請求金額
     *       再4発行済み → 最も古い再請求の請求金額
     *       再5以上発行済み → 最も古い再3以上の再請求金額
     *
     * @param int $oseq 注文SEQ
     * @return array
     */
    public function getValidAmountRange($oseq) {
        $results = array(
            'min' => 0,     // 下限額
            'max' => 0      // 上限額
        );
        $hisarr = array();
        $hisTable = $this->getClaimHistoryTable();
        $histories = $hisTable->findClaimHistory(array('OrderSeq' => $oseq), true);
        if($histories->count() == 0) {

                $this->info(sprintf('[getValidAmountRange:%s] claim history not found', $oseq));

            // 請求履歴がない異常事態
            throw new \Exception(sprintf('claim history not found. oseq = %s', $oseq));
        }

        // 作業データの初期化
        $max_clm_ptn = 0;           // 最大請求パターン
        $max_clm_amount = 0;        // 最大請求金額

        $amount_cache = array();    // 請求金額キャッシュ
        $th_seqs = array(           // 下限金額パターン
            'A' => null,                // A：初回請求金額
            'B' => null,                // B：最も古い再請求の請求金額
            'C' => null                 // C：最も古い再3以降の再請求金額
        );

        // 下限用の履歴をパターンごとに抽出
        foreach($histories as $h) {
            $ptn = $h['ClaimPattern'];

            $hisarr[] = $h;

            if($ptn < 1 || $ptn > 9) {

                    $this->info(sprintf(
                                        '[getValidAmountRange:%s] invalid claim pattan included. claim pattern = %s',
                                        $oseq, $ptn));

                // 未定義の請求パターンは無視
                continue;
            }

            // 最大請求パターンを更新
            if($ptn > $max_clm_ptn) $max_clm_ptn = $ptn;

            // 請求金額をキャッシュ
            $amount = $amount_cache[$h['Seq']] = $hisTable->getClaimAmount($h['Seq']);

            // 最大請求金額を更新
            if($amount > $max_clm_amount) $max_clm_amount = $amount;

            if($ptn == 1 && !isset($th_seqs['A'])) {
                // 下限基準A：最初の初回請求
                $th_seqs['A'] = $h['Seq'];
            }
            if($ptn > 1 && !isset($th_seqs['B'])) {
                // 下限基準B：最も古い再請求
                $th_seqs['B'] = $h['Seq'];
            }
            if($ptn > 3 && !isset($th_seqs['C'])) {
                // 下限基準C：最も古い再3以上の再請求
                $th_seqs['C'] = $h['Seq'];
            }
        }

            $this->debug(sprintf('[getValidAmountRange:%s] max claim pattern = %s', $oseq, $max_clm_ptn));
            $this->debug(sprintf('[getValidAmountRange:%s] fixed thresholds = A:%s(%s), B:%s(%s), C:%s(%s)',
                                 $oseq,
                                 $th_seqs['A'], $amount_cache[$th_seqs['A']],
                                 $th_seqs['B'], $amount_cache[$th_seqs['B']],
                                 $th_seqs['C'], $amount_cache[$th_seqs['C']]));

        // 最大請求パターンに応じて下限基準額を確定
        if($max_clm_ptn >= 7) {
            // 再5以上発行済み → 基準C
            $level = 'C';
        } else
        if($max_clm_ptn == 6) {
            // 再4発行済み → 基準B
            $level = 'B';
        } else {
            // それ以外（＝再3以下） → 基準A
            $level = 'A';
        }

            $this->debug(sprintf('[getValidAmountRange:%s] lower amount pattern = %s(%s), amount = %s',
                                 $oseq, $level, $th_seqs[$level], $amount_cache[$th_seqs[$level]]));

        $results['min'] = $amount_cache[$th_seqs[$level]];

        // 上限基準額確定
        // ※上限基準額が下限基準額を下回っても特になにもしない → このパターンの注文は入金保留となる
        $hisarrCount = 0;
        if(!empty($hisarr)) {
            $hisarrCount = count($hisarr);
        }
        $max_seq = $hisarr[$hisarrCount - 1]['Seq'];
        $results['max'] = $amount_cache[$max_seq];

            $this->debug(sprintf(
                                 '[getValidAmountRange:%s] upper amount seqs = %s, amount = %s',
                                 $oseq, $max_seq, $results['max']));
            $this->debug(sprintf(
                                 '[getValidAmountRange:%s] fixed range = %s to %s',
                                 $oseq, $results['min'], $results['max']));

        return $results;
    }

    /**
     * 指定された注文の入金額を取得する
     * @param int $orderseq
     */
    private function getInstallmentPlanAmount($orderseq) {
        // SQL作成
        $sql = " SELECT ReceiptAmountTotal AS cnt FROM T_ClaimControl WHERE OrderSeq = :OrderSeq ";

        // パラメーター設定
        $prm = array(
            ':OrderSeq' => $orderseq,
        );

        // SQL実行
        return $this->_adapter->query($sql)->execute($prm)->current()['cnt'];

    }


}
