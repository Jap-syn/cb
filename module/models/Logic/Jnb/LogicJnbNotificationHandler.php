<?php
namespace models\Logic\Jnb;

use Zend\Db\Adapter\Adapter;
use Zend\Json\Json;
use models\Logic\Jnb\NotificationHandler\LogicJnbNotificationHandlerException;

/**
 * JNB入金通知処理ロジック。
 * 受信データを入金通知管理テーブルに格納しレスポンスデータを生成する
 */
class LogicJnbNotificationHandler extends LogicJnbCommon {
    /** 電文値定数：レスポンス電文区分 @var string */
    const HD_KEY_MESSAGE_CLASS_RESPONSE = 'RP';

    /** 電文値定数：完了コード - 正常 @var string */
    const HD_KEY_COMPLETE_CODE_NORMALY = '0000';

    /** 電文値定数：完了コード - 異常 @var string */
    const HD_KEY_COMPLETE_CODE_ABNORMALY = '9001';

    /** 電文値定数：完了詳細コード - 正常 @var string */
    const HD_KEY_COMP_DETAIL_CODE_NORMALY = '000000';

    /** 電文値定数：完了詳細コード - データ異常 @var string */
    const HD_KEY_COMP_DETAIL_CODE_INVALID_DATA = '000100';

    /** 電文値定数：完了詳細コード - ハッシュ値異常 @var string*/
    const HD_KEY_COMP_DETAIL_CODE_INVALID_HASH = '000901';

    /**
     * 要求電文を処理して応答データを生成する
     *
     * @param array $data 通知電文データ
     * @return array 応答データ
     */
    public function process(array $data) {
        $result = array();
        $start = microtime(true);

        // 取引ID抽出
        $tranId = $data['HD_TranId'];
$this->debug(sprintf('[process:%s] method called', $tranId));

        $oldRow = null;
        try {
            $oldRow = $this->findExistsTransaction($tranId);
        } catch(LogicJnbNotificationHandlerException $critError) {
            // 既存行取得時の致命的エラーはそのまま上位へ
$this->err(sprintf('[process:%s] an critical error has occured !!! error = %s', $tranId, $critError->getMessage()));
            throw $critError;
        }

        if($oldRow) {
            // 同一取引IDですでに受信済みの場合はその時のレスポンスをそのまま返す
$this->debug(sprintf('[process:%s] transaction already exists.', $tranId));
            $result = Json::decode($oldRow['ResponseRawData'], Json::TYPE_ARRAY);
        } else {
            // 同一取引IDの履歴がないので新規に入金通知管理データを挿入
            $nfSeq = $this->createTransactionRow($tranId);
$this->debug(sprintf('[process:%s] NotificationSeq = %s', $tranId, $nfSeq));

            // レスポンスデータの初期化
            $result = array_merge($data, array(
                'HD_MsgClass' => self::HD_KEY_MESSAGE_CLASS_RESPONSE,
                'HD_CompCode' => self::HD_KEY_COMPLETE_CODE_NORMALY,
                'HD_DetailCode' => self::HD_KEY_COMP_DETAIL_CODE_NORMALY
            ));

            // リクエストデータの検証
            $errors = $this->validate($data);

            // 通知管理データの初期化
            $update_data = array(
                'Status' => \models\Table\TableJnbPaymentNotification::STATUS_RESPONSED,
                'ResponseDate' => date('Y-m-d H:i:s'),
                'ReceivedRawData' => Json::encode($data)
            );
            if(!empty($errors)) {
$this->info(sprintf('[process:%s] validation errors = %s', $tranId, var_export($errors, true)));
                // 受信データの不備
                $result = array_merge($result, array(
                    'HD_CompCode' => self::HD_KEY_COMPLETE_CODE_ABNORMALY,
                    'HD_DetailCode' => self::HD_KEY_COMP_DETAIL_CODE_INVALID_DATA
                ));
                $update_data['Status'] = \models\Table\TableJnbPaymentNotification::STATUS_NOT_AVAILABLE;
                $update_data['RejectReason'] = '通知データの不備';
            } else if(!$this->isValidHash($data)) {
$this->info(sprintf('[process:%s] invalid hash value.', $tranId));
                // ハッシュ不正
                $result = array_merge($result, array(
                    'HD_CompCode' => self::HD_KEY_COMPLETE_CODE_ABNORMALY,
                    'HD_DetailCode' => self::HD_KEY_COMP_DETAIL_CODE_INVALID_HASH
                ));
                $update_data['Status'] = \models\Table\TableJnbPaymentNotification::STATUS_NOT_AVAILABLE;
                $update_data['RejectReason'] = '通知データのハッシュ値不整合';
            } else {
                // エラーなしの場合は入金日の抽出を試みる
                $rcptDate = $this->parseReceiptDate($tranId);
                if($rcptDate) {
                    $update_data['ReceiptDate'] = $rcptDate;
                }
            }

            // 応答時間の構築
            $time_part = explode(' ', microtime());
            $dt = date('YmdHis', $time_part[1]);
            $ms = substr(round($time_part[0], 3).'000000', 2, 3);
            $result['HD_RspDateTime'] = $dt.$ms;

            $update_data['ResponseRawData'] = Json::encode($result);

            // 通知管理データを更新
            $update_data = array_merge($update_data, $this->parsePaymentInfo($data));
            $updatedRow = $this->updateTransactionRow($update_data, $nfSeq);
$this->debug(sprintf('[process:%s] record updated. seq = %s', $tranId, $nfSeq));
        }

$this->debug(sprintf('[process:%s] all process completed. code = %s-%s, elapsed time = %s sec.', $tranId, $result['HD_CompCode'], $result['HD_DetailCode'], (microtime(true) - $start)));
        return $result;
    }

    /**
     * 指定取引IDの管理データを取得する
     *
     * @access protected
     * @param string $tranId 通知電文の取引ID
     * @return array | null 指定取引IDの既存データまたはnull
     */
    protected function findExistsTransaction($tranId) {
        $fixedStatus = array(
            \models\Table\TableJnbPaymentNotification::STATUS_RESPONSED,
            \models\Table\TableJnbPaymentNotification::STATUS_NOT_AVAILABLE
        );
        $repeat = 0;
        $nfTable = $this->getPaymentNotificationTable();
        while(true) {
            // 指定取引IDを持つ既存行を検索
            $row = $nfTable->findByTransactionId($tranId);

            // 行が見つからない場合はnullで即時終了
            if (!$row) return null;

            // 既存行のステータスが応答済みまたは入金不能確定の場合はその行を返す
            if(in_array($row['Status'], $fixedStatus)) {
                return $row;
            }

            // リトライ準備
            $repeat++;
            if($repeat > 120) {
                // リトライが120回を超過（≒30秒経過）した場合は致命的エラーとする
                throw new LogicJnbNotificationHandlerException(sprintf('CRITICAL ERROR !!! database not responsed or proceded process not terminated. TransactionId = %s', $tranId));
            }

            // 250ミリ秒待ち合わせてリトライ
if($repeat % 4 == 0) $this->info(sprintf('[findExistsTransaction:%s] waiting for termination of preceded process. (repeat: %s times)', $tranId, $repeat));
            usleep(250 * 1000);
        }
    }

    /**
     * 指定取引ID向けの入金通知管理データを新規に追加し、結果のプライマリキーを返す
     *
     * @access protected
     * @param string $tranId 取引ID
     * @return int T_JnbPaymentNotificationに挿入された行のプライマリキー
     */
    protected function createTransactionRow($tranId) {
        $savedata = array(
                'TransactionId' => $tranId,
                'Status' => \models\Table\TableJnbPaymentNotification::STATUS_RECEIVED,
                'ReqBranchCode' => '---',
                'ReqAccountNumber' => '-------',
                'ReceiptAmount' => 0,
                'ReceivedDate' => date('Y-m-d H:i:s'),
                'DeleteFlg' => 0,
                );

        return $this->getPaymentNotificationTable()->saveNew($savedata);
    }

    /**
     * 指定の入金通知管理データを更新する
     *
     * @access protected
     * @param array $data 更新内容を含んだデータ
     * @param int $nfSeq 更新データを指定するプライマリキー
     * @return array　更新された入金通知管理データ
     */
    protected function updateTransactionRow(array $data, $nfSeq) {
        $nfTable = $this->getPaymentNotificationTable();
        $nfTable->saveUpdate($data, $nfSeq);
        return $nfTable->find($nfSeq)->current();
    }

    /**
     * 指定の電文のハッシュ値を検証する
     *
     * @access protected
     * @param array $data 通知電文データ
     * @return boolean 通知電文のハッシュ値が正しい場合はtrue、それ以外はfalse
     */
    protected function isValidHash(array $data) {
        $reqHash = strtoupper($data['HD_HashValue']);
        $dataHash = strtoupper(md5($data['Dummy']));    // ハッシュターゲットは「Dummy」
        return $reqHash == $dataHash;
    }

    /**
     * 通知電文データから要求支店コード、要求口座版の具および入金額を抽出する
     *
     * @access protected
     * @param array $data 通知電文データ
     * @return array
     */
    protected function parsePaymentInfo(array $data) {
        $dummy = $data['Dummy'];
        $amount = preg_replace('/^0*/', '', $data['Amount']);
        preg_match('/([^0]\d{2})(\d{7})(\d{12})$/u', $dummy, $matches);
        return array(
            'ReqBranchCode' => nvl($matches[1], '---'),
            'ReqAccountNumber' => nvl($matches[2], '-------'),
            'ReceiptAmount' => (int)$amount
        );
    }

    /**
     * 通知電文の取引IDから入金日付を展開する
     *
     * @access protected
     * @param string $tranId 取引ID
     * @return string | null 取引IDの先頭から抽出した入金日。形式が不正な場合はnullを返す
     */
    protected function parseReceiptDate($tranId) {
        if(!preg_match('/^\d{8}/', $tranId)) return null;
        $dt = date('Y-m-d', strtotime(substr($tranId, 0, 8)));
        return preg_replace('/-/', '', $dt) == substr($tranId, 0, 8) ? $dt : null;
    }

    /**
     * 通知電文データを検証する
     *
     * @access protected
     * @param array $data 通知電文データ
     * @return array 検証エラー情報
     */
    protected function validate(array $data) {

        $errors = array();

        // 1.業務ヘッダ - 版数情報
        $key = 'HD_VL';
        if (!((mb_strlen($data[$key]) == 4) && preg_match('/^\d{4}$/', $data[$key]))) {
            $errors[] = $key;
        }

        // 2.業務ヘッダ - 電文区分
        $key = 'HD_MsgClass';
        if (!((mb_strlen($data[$key]) == 2) && preg_match('/^(RQ|RP)$/', $data[$key]))) {
            $errors[] = $key;
        }

        // 3.業務ヘッダ - 完了コード
        $key = 'HD_CompCode';
        if (mb_strlen($data[$key]) > 0) {
            if (!((mb_strlen($data[$key]) == 4) && preg_match('/^(\d{4})?$/', $data[$key]))) {
                $errors[] = $key;
            }
        }

        // 4.業務ヘッダ - 詳細コード
        $key = 'HD_DetailCode';
        if (mb_strlen($data[$key]) > 0) {
            if (!((mb_strlen($data[$key]) == 6) && preg_match('/^(\d{6})?$/', $data[$key]))) {
                $errors[] = $key;
            }
        }

        // 5.業務ヘッダ - 要求送信日時
        $key = 'HD_ReqDateTime';
        if (!((mb_strlen($data[$key]) == 17) && preg_match('/^\d{17}$/', $data[$key]))) {
            $errors[] = $key;
        }

        // 6.業務ヘッダ - 応答送信日時
        $key = 'HD_RspDateTime';
        if (mb_strlen($data[$key]) > 0) {
            if (!((mb_strlen($data[$key]) == 17) && preg_match('/^(\d{17})?$/', $data[$key]))) {
                $errors[] = $key;
            }
        }

        // 7.業務ヘッダ - 取引ＩＤ
        $key = 'HD_TranId';
        if (!((mb_strlen($data[$key]) == 24) && preg_match('/^\d{24}$/', $data[$key]))) {
            $errors[] = $key;
        }

        // 8.業務ヘッダ - ハッシュ値
        $key = 'HD_HashValue';
        if (!((mb_strlen($data[$key]) == 32) && preg_match('/^[\da-zA-Z]{32}$/', $data[$key]))) {
            $errors[] = $key;
        }

        // 9.業務データ - データ区分
        $key = 'DataKbn';
        if (!((mb_strlen($data[$key]) == 1) && preg_match('/^\d$/', $data[$key]))) {
            $errors[] = $key;
        }

        // 10.業務データ - 照会番号
        $key = 'ShokaiNo';
        if (!((mb_strlen($data[$key]) == 6) && preg_match('/^\d{6}$/', $data[$key]))) {
            $errors[] = $key;
        }

        // 11.業務データ - 勘定日
        $key = 'KanjoDate';
        if (!((mb_strlen($data[$key]) == 6) && preg_match('/^\d{6}$/', $data[$key]))) {
            $errors[] = $key;
        }

        // 12.業務データ - 起算日
        $key = 'KisanDate';
        if (!((mb_strlen($data[$key]) == 6) && preg_match('/^\d{6}$/', $data[$key]))) {
            $errors[] = $key;
        }

        // 13.業務データ - 金額
        $key = 'Amount';
        if (!((mb_strlen($data[$key]) == 10) && preg_match('/^\d{10}$/', $data[$key]))) {
            $errors[] = $key;
        }

        // 14.業務データ - うち他店券金額
        $key = 'AnotherAmount';
        if (!((mb_strlen($data[$key]) == 10) && preg_match('/^.{10}$/', $data[$key]))) {
            $errors[] = $key;
        }

        // 15.業務データ - 振込依頼人コード
        $key = 'OutputCode';
        if (!((mb_strlen($data[$key]) == 10) && preg_match('/^.{10}$/', $data[$key]))) {
            $errors[] = $key;
        }

        // 16.業務データ - 振込依頼人名
        $key = 'OutputName';
        if (!((mb_strlen($data[$key]) == 48) /* && preg_match('/^.{48}$/', $data[$key]) */ )) {
            $errors[] = $key;
        }

        // 17.業務データ - 仕向銀行名
        $key = 'RmtBankName';
        if (!((mb_strlen($data[$key]) == 15) /* && preg_match('/^.{15}$/', $data[$key]) */ )) {
            $errors[] = $key;
        }

        // 18.業務データ - 仕向支店名
        $key = 'RmtBrName';
        if (!((mb_strlen($data[$key]) == 15) /* && preg_match('/^.{15}$/', $data[$key]) */ )) {
            $errors[] = $key;
        }

        // 19.業務データ - 取消区分
        $key = 'CancelKind';
        if (!((mb_strlen($data[$key]) == 1) && preg_match('/^\d$/', $data[$key]))) {
            $errors[] = $key;
        }

        // 20.業務データ - ＥＤＩ情報
        $key = 'EDIInfo';
        if (!((mb_strlen($data[$key]) == 20) && preg_match('/^.{20}$/', $data[$key]))) {
            $errors[] = $key;
        }

        // 21.業務データ - ダミー
        $key = 'Dummy';
        if (!((mb_strlen($data[$key]) == 52) && preg_match('/^\d{52}$/', $data[$key]))) {
            $errors[] = $key;
        }

		return $errors;
    }
}
