<?php
namespace models\Logic\Jnb\Account;

use models\Logic\Jnb\LogicJnbCommon;
use models\Logic\Jnb\LogicJnbAccount;
use models\Logic\LogicReceiptOfMoney;

/**
 * JNBバーチャル口座の入金処理抽象ロジック
 */
abstract class LogicJnbAccountReceipt extends LogicJnbCommon {
    /** 入金処理結果定数：入金処理対象外 @var int */
    const RCPT_RESULT_EXEMPT = -1;

    /** 入金処理結果定数：入金保留 @var int */
    const RCPT_RESULT_PEIDING = 0;

    /** 入金処理結果定数：入金処理済み @var int */
    const RCPT_RESULT_SUCCESS = 1;

    /** 入金処理結果定数：エラー @var int */
    const RCPT_RESULT_ERROR = -9;

    /** 入金不能事由定数：入金処理完了 @var int */
    const RESULT_TYPE_0_SUCCESS = 0;

    /** 入金不能事由定数：該当口座なし @var int */
    const RESULT_TYPE_1_ACCOUNT_NOT_FOUND = 1;

    /** 入金不能事由定数：口座利用実績なし @var int */
    const RESULT_TYPE_2_HISTORY_NOT_FOUND = 2;

    /** 入金不能事由定数：請求中でない口座 @var int */
    const RESULT_TYPE_3_EXEMPT_ACCOUNT = 3;

    /** 入金不能事由定数：該当注文なし @var int */
    const RESULT_TYPE_4_ORDER_NOT_FOUND = 4;

    /** 入金不能事由定数：入金待ちでない注文 @var int */
    const RESULT_TYPE_5_EXEMPT_ORDER = 5;

    /** 入金不能事由定数：金額差異あり @var int */
    const RESULT_TYPE_6_EXEMPT_AMOUNT = 6;

    /** 入金不能事由定数：分割支払済み @var int */
    const RESULT_TYPE_7_SPLIT_PAID = 7;

    /** 入金不能事由定数：通知内容破棄 @var int */
    const RESULT_TYPE_8_DISPOSE_NOTIFY = 8;

    /** 入金不能事由定数：返却済み口座 @var int */
    const RESULT_TYPE_9_RETURNED_ACCOUNT = 9;

    /** 入金不能事由定数：無効なJNB ID @var int */
    const RESULT_TYPE_10_DISABLED_JNB_ID = 10;

    /** バッチ入金処理で使用するスレッドプールのスレッドグループ名 @var string */
    const BATCH_THREAD_GROUP_NAME = 'jnb-auto-rcpt-batch';

    /**
     * 入金処理結果定数に対応したテキストを取得する
     *
     * @static
     * @param int $result_code 入金処理結果定数
     * @return string
     */
    public static function getReceiptResultLabel($result_code) {
        $map = self::getReceiptResultMap();
        return $map[$result_code];
    }

    /**
     * 入金処理結果定数値を表示用テキストに関連付けた連想配列を取得する。
     * キーが定数値、値がテキストの構造
     *
     * @static
     * @return array
     */
    public static function getReceiptResultMap() {
        return array(
            self::RCPT_RESULT_EXEMPT    => sprintf('%s：入金処理対象外', self::RCPT_RESULT_EXEMPT),
            self::RCPT_RESULT_PEIDING   => sprintf('%s：入金保留', self::RCPT_RESULT_PEIDING),
            self::RCPT_RESULT_SUCCESS   => sprintf('%s：入金処理済み', self::RCPT_RESULT_SUCCESS),
            self::RCPT_RESULT_ERROR     => sprintf('%s：エラー', self::RCPT_RESULT_ERROR)
        );
    }

    /**
     * 入金不能事由コードに対する設定をマッピングした連想配列を取得する
     *
     * @static
     * @access protected
     * @return array
     */
    protected static function getResultTypeSettings() {
        return array(
            // 1：該当口座なし
            self::RESULT_TYPE_1_ACCOUNT_NOT_FOUND =>
                array('msg' => '該当口座なし', 'result' => self::RCPT_RESULT_EXEMPT, 'status' => -1),

            // 2：口座利用実績なし
            self::RESULT_TYPE_2_HISTORY_NOT_FOUND =>
                array('msg' => '口座利用実績なし', 'result' => self::RCPT_RESULT_EXEMPT, 'status' => -1),

            // 3：請求中でない口座
            self::RESULT_TYPE_3_EXEMPT_ACCOUNT =>
                array('msg' => '請求中でない口座', 'result' => self::RCPT_RESULT_PEIDING, 'status' => 3),

            // 4：該当注文なし
            self::RESULT_TYPE_4_ORDER_NOT_FOUND =>
                array('msg' => '該当注文なし', 'result' => self::RCPT_RESULT_PEIDING, 'status' => 3),

            // 5：入金待ちでない注文
            self::RESULT_TYPE_5_EXEMPT_ORDER =>
                array('msg' => '入金待ちでない注文', 'result' => self::RCPT_RESULT_PEIDING, 'status' => 3),

            // 6：金額差異あり
            self::RESULT_TYPE_6_EXEMPT_AMOUNT =>
                array('msg' => '金額差異あり', 'result' => self::RCPT_RESULT_PEIDING, 'status' => 3),

            // 7：分割支払済み
            self::RESULT_TYPE_7_SPLIT_PAID =>
                array('msg' => '分割支払済み金額あり', 'result' => self::RCPT_RESULT_PEIDING, 'status' => 3),

            // 8：通知内容破棄
            self::RESULT_TYPE_8_DISPOSE_NOTIFY =>
                array('msg' => '入金通知破棄', 'result' => self::RCPT_RESULT_EXEMPT, 'status' => -1),

            // 9：返却済み口座
            self::RESULT_TYPE_9_RETURNED_ACCOUNT =>
                array('msg' => '返却済み口座', 'result' => self::RCPT_RESULT_EXEMPT, 'status' => -1),

            // 10：無効なJNB ID
            self::RESULT_TYPE_10_DISABLED_JNB_ID =>
                array('msg' => '無効なJNB ID', 'result' => self::RCPT_RESULT_EXEMPT, 'status' => -1),

            // 0：入金処理完了
            self::RESULT_TYPE_0_SUCCESS =>
                array('msg' => null, 'result' => self::RCPT_RESULT_SUCCESS, 'status' => 9)

        );
    }

    /**
     * 指定入金不能事由コードの設定を取得する。
     * 戻り値の連想配列は以下の内容が含まれる
     *  msg -> 入金不能事由（テキスト）
     *  result -> 入金処理結果コード
     *  status -> 入金通知管理データに反映するステータス値
     *
     * @static
     * @access protected
     * @param int $resultType 入金不能事由コード
     * @return array
     */
    protected static function getResultTypeSetting($resultType) {
        $map = self::getResultTypeSettings();
        return $map[$resultType];
    }

    /**
     * JNB口座ロジックのインスタンスを取得する
     *
     * @return LogicJnbAccount
     */
    public function getAccountLogic() {
        return new LogicJnbAccount($this->getDbAdapter());
    }

    /**
     * 入金確定処理を実行する。このメソッドで入金確定した場合、入金日は常にJNBから通知された入金日となる
     *
     * @param int $nfSeq 入金通知管理SEQ
     * @param int $oseq 注文SEQ
     * @param int $accSeq JNB口座SEQ
     * @param int $userId ユーザーID
     * @param null | int $amount 入金額。省略時は入金通知で指定された入金額
     * @return int 入金処理結果コード
     */
    public function doneReceipt($nfSeq, $oseq, $accSeq, $userId, $amount = null) {
        $payTable = $this->getPaymentNotificationTable();
        $nfData = $payTable->find($nfSeq)->current();
        if(!$nfData) {
            throw new \Exception('invalid notification seq specified.');
        }

        // 入金額の補正
        if($amount === null) {
            $amount = $nfData['ReceiptAmount'];
        }
        $amount = (int)$amount;

        // 入金通知管理の更新データを整備
        $update_data = array(
            'OrderSeq' => (int)$oseq,
            'AccountSeq' => (int)$accSeq,
            'RejectReason' => null,
            'ReceiptProcessDate' => date('Y-m-d H:i:s')
        );
        // 入金通知管理を「入金処理済み」に更新
        $result = $this->updateNotificationData($nfSeq, $update_data, self::RESULT_TYPE_0_SUCCESS);

        // 口座をクローズ
        $this->getAccountLogic()->closeAccount($oseq);

        // 入金処理を実行
        $claimAount = $this->_adapter->query(" SELECT ClaimAmount FROM T_ClaimControl WHERE OrderSeq = :OrderSeq ")->execute(
            array(':OrderSeq' => $oseq))->current()['ClaimAmount'];// 最終請求金額を取得
        $payLogic = new LogicReceiptOfMoney($this->getDbAdapter(), $this->getSmtpServer(), $this->getMailCharset());
        $payLogic->setUseTransaction(false);    // 入金処理ロジックでトランザクションは使用しない
        $payLogic->payment($oseq,
                           LogicReceiptOfMoney::RCPTMETHOD_BANK,
                           $nfData['ReceiptDate'],
                           $amount,
                           $claimAount,
                           $userId
                          );

        return $result;
    }

    /**
     * 入金不能確定処理を実行する
     *
     * @param int $nfSeq 入金通知管理SEQ
     * @param int $resultType 入金不能事由コード。以下のいずれかのコードのみ許容される
     *                        RESULT_TYPE_1_ACCOUNT_NOT_FOUND（該当口座なし）
     *                        RESULT_TYPE_2_HISTORY_NOT_FOUND（口座利用実績なし）
     *                        RESULT_TYPE_8_DISPOSE_NOTIFY（通知内容破棄）
     *                        RESULT_TYPE_9_RETURNED_ACCOUNT（返却済み口座）
     *                        RESULT_TYPE_10_DISABLED_JNB_ID（無効なJNB ID）
     * @param null | int $oseq 注文SEQ。未確定の場合はnullを指定する
     * @param null | int $accSeq JNB口座SEQ。未確定の場合はnullを指定する
     * @return 入金処理結果コード
     */
    public function doneUnreceipt($nfSeq, $resultType, $oseq = null, $accSeq = null) {
        $payTable = $this->getPaymentNotificationTable();
        $nfData = $payTable->find($nfSeq)->current();
        if(!$nfData) {
            throw new \Exception('invalid notification seq specified.');
        }

        $valid_result_types = array(
            self::RESULT_TYPE_1_ACCOUNT_NOT_FOUND,
            self::RESULT_TYPE_2_HISTORY_NOT_FOUND,
            self::RESULT_TYPE_8_DISPOSE_NOTIFY,
            self::RESULT_TYPE_9_RETURNED_ACCOUNT,
            self::RESULT_TYPE_10_DISABLED_JNB_ID
        );
        if(!in_array($resultType, $valid_result_types)) {
            throw new \Exception('invalid result-type specified');
        }
        $update_data = array();
        if($oseq !== null) $update_data['OrderSeq'] = (int)$oseq;
        if($accSeq !== null) $update_data['AccountSeq'] = (int)$accSeq;
        return $this->updateNotificationData($nfSeq, $update_data, $resultType);
    }

    /**
     * 入金保留確定処理を実行する
     *
     * @param int $nfSeq 入金通知管理SEQ
     * @param int $resultType 入金不能事由コード。以下のいずれかのコードのみ許容される
     *                        RESULT_TYPE_3_EXEMPT_ACCOUNT（請求中でない口座）
     *                        RESULT_TYPE_4_ORDER_NOT_FOUND（該当注文なし）
     *                        RESULT_TYPE_5_EXEMPT_ORDER（入金待ちでない注文）
     *                        RESULT_TYPE_6_EXEMPT_AMOUNT（金額差異あり）
     *                        RESULT_TYPE_7_SPLIT_PAID（分割支払済み）
     *
     * @param null | int $oseq 注文SEQ。未確定の場合はnullを指定する
     * @param null | int $accSeq JNB口座SEQ。未確認の場合はnullを指定する
     * @param null | string $additionalReason 入金不能事由追加テキスト。省略可能
     * @return 入金処理結果コード
     */
    public function doneReceiptPending($nfSeq, $resultType, $oseq = null, $accSeq = null, $additionalReason = null) {
        $payTable = $this->getPaymentNotificationTable();
        $nfData = $payTable->find($nfSeq)->current();
        if(!$nfData) {
            throw new \Exception('invalid notification seq specified.');
        }

        $valid_result_types = array(
            self::RESULT_TYPE_3_EXEMPT_ACCOUNT,
            self::RESULT_TYPE_4_ORDER_NOT_FOUND,
            self::RESULT_TYPE_5_EXEMPT_ORDER,
            self::RESULT_TYPE_6_EXEMPT_AMOUNT,
            self::RESULT_TYPE_7_SPLIT_PAID
        );
        if(!in_array($resultType, $valid_result_types)) {
            throw new \Exception('invalid result-type specified');
        }
        $update_data = array();
        if($oseq !== null) $update_data['OrderSeq'] = (int)$oseq;
        if($accSeq !== null) $update_data['AccountSeq'] = (int)$accSeq;
        return $this->updateNotificationData($nfSeq, $update_data, $resultType, $additionalReason);
    }

    /**
     * 指定の入金通知管理データを更新し、入金処理結果コードを返す
     *
     * @access protected
     * @param int $seq 入金通知管理SEQ
     * @param array $update_data 更新用データ
     * @param int $result_type 入金不能事由コード
     * @param string $additional_text 入金不能事由追加テキスト
     * @return int 入金処理結果コード
     */
    protected function updateNotificationData($seq, $update_data, $result_type, $additional_text = '') {
        $payTable = $this->getPaymentNotificationTable();
        $nfData = $payTable->find($seq)->current();
        if(!$nfData) {
            throw new \Exception('invalid notification seq specified.');
        }

        $conf = self::getResultTypeSetting($result_type);

        $msg = nvl($conf['msg']);
        $additional_text = nvl($additional_text);
        if(strlen($additional_text)) {
            $msg = sprintf('%s：%s', $msg, $additional_text);
        }

        $update_data = array_merge($update_data, array(
            'Status' => $conf['status'],
            'RejectReason' => strlen($msg) ? sprintf('%s：%s', $result_type, $msg) : null,
            'LastProcessDate' => date('Y-m-d H:i:s')
        ));

        $this->getPaymentNotificationTable()->saveUpdate($update_data, $seq);

        return $conf['result'];
    }

}
