<?php
namespace models\Logic;

use Zend\Db\Adapter\Adapter;
use Coral\Base\BaseGeneralUtils;
use Coral\Coral\Mail\CoralMail;
use Coral\Coral\History\CoralHistoryOrder;

setlocale( LC_ALL, 'ja_JP.UTF-8' );
ini_set( 'default_charset', 'UTF-8' );
mb_internal_encoding( 'UTF-8' );
mb_http_output('UTF-8');
mb_regex_encoding( 'UTF-8' );

/**
 * 間違い伝票修正依頼メールクラス
 */
class LogicRequestModifyJournal
{
    /**
     * アダプタ
     *
     * @var Adapter
     */
    protected $_adapter = null;

    /**
     * SMTPサーバー
     *
     * @var string
     */
    private $_smtp;

    /**
     * コンストラクタ
     *
     * @param Adapter $adapter アダプタ
     * @param string $smtp SMTPサーバー
     */
    public function __construct(Adapter $adapter, $smtp)
    {
        $this->_adapter = $adapter;
        $this->_smtp = $smtp;
    }

    /**
     * 間違い伝票修正依頼メール処理
     */
    public function exec()
    {
        // 稼働日でない場合は以降処理なし
        $mdlbc = new \models\Table\TableBusinessCalendar($this->_adapter);
        if (!$mdlbc->isBusinessDate(date('Y-m-d'))) {
            return;
        }

        $aryAll = array();
        $aryEnterpriseBase = array(
                'LoginId' => ''
            ,   'ReceiptOrderDate' => ''
            ,   'FileDate' => ''
            ,   'Password' => ''
            ,   'Orders' => array()
            ,   'OrderIdStr' => ''
        );

        // 対象注文の抽出⇒加盟店単位にまとめる
        $ri = $this->_adapter->query($this->getBaseSql())->execute(null);
        if ($ri->count() == 0) {
            // 対象がなければ以降処理なし
            return;
        }

        $prevLoginId = ''; // ログインID(初期値:空欄)
        $aryEnterprise = $aryEnterpriseBase;
        foreach ($ri as $row) {
            if ($row['LoginId'] == $prevLoginId) {
                ;
            }
            else {
                if ($prevLoginId != '') {
                    // １つ前の加盟店IDと違う時 : $aryEnterpriseを$aryAllへ積む
                    $aryAll[] = $aryEnterprise;
                }
                // (共通初期化処理)
                $aryEnterprise = $aryEnterpriseBase;
                $aryEnterprise['LoginId'] = $row['LoginId'];
                $aryEnterprise['ReceiptOrderDate'] = $row['ReceiptOrderDate'];
                $aryEnterprise['FileDate'] = date('Y-m-d_Hi');
                $aryEnterprise['Password'] = BaseGeneralUtils::makeRandStr(8);
            }
            // (共通処理)Ordersへの積上げ
            $aryEnterprise['Orders'][] =
                array('OrderId' => $row['OrderId'], 'NameKj' => $row['NameKj'], 'Ent_OrderId' => $row['Ent_OrderId'], 'CancelReason' => $row['CancelReason'], 'Note' => $row['Note'],
                        'OrderSeq' => $row['OrderSeq'], 'ClaimPattern' => $row['ClaimPattern']);

            $prevLoginId = $row['LoginId'];// 更新処理
        }
        // (最終処理) : $aryEnterpriseを$aryAllへ積む
        $aryAll[] = $aryEnterprise;

        // 加盟店毎処理
        $history = new CoralHistoryOrder($this->_adapter);
        $mdlu = new \models\Table\TableUser($this->_adapter);
        $userId = $mdlu->getUserId(99, 1);
        $mail = new CoralMail($this->_adapter, $this->_smtp);
        foreach ($aryAll as $row) {
            // ZIPファイル作成
            $zipFileFullPath = $this->makeZipFile($row);

            // バインド用変数整形
            $rowCount = 0;
            if(!empty($row['Orders'])) {
                $rowCount = count($row['Orders']);
            }
            if ($rowCount > 1) {
                $row['ReceiptOrderDate'] = sprintf('%s（他%d件）', $row['ReceiptOrderDate'], $rowCount - 1);
            }
            $row['OrderIdStr'] = $this->makeOrderIdStr($row['Orders']);

            // 間違い伝番修正依頼メール送信
            $mail->SendRequestModifyJournal($row, $zipFileFullPath, $userId);

            // 解凍パスワード通知メール送信
            $mail->SendRequestModifyJournalUnzipPassword($row, $userId);

            // ファイル削除(※ZIP)
            unlink( $zipFileFullPath );

            // 注文履歴へ登録(伝票確認メール送信)
            for ($i=0; $i<$rowCount; $i++) {
                $oseq = $row['Orders'][$i]['OrderSeq'];
                $historyreason = ($row['Orders'][$i]['ClaimPattern'] == '再１') ? 35 : 36;
                $history->InsOrderHistory($oseq, $historyreason, $userId);
            }
        }
    }

    /**
     * 間違い伝票修正依頼メール処理
     * (注文単独指定版)
     *
     * @param int $oseq 注文SEQ
     * @param int $userId ユーザID
     */
    public function execOne($oseq, $userId)
    {
        $aryEnterprise = array(
                'LoginId' => ''
            ,   'ReceiptOrderDate' => ''
            ,   'FileDate' => ''
            ,   'Password' => ''
            ,   'Orders' => array()
            ,   'OrderIdStr' => ''
        );

        $sql =<<<EOQ
SELECT o.OrderId
,      os.NameKj
,      o.Ent_OrderId
,      '注文キャンセル' AS CancelReason
,      '長期未着荷状態につき' AS Note
,      e.LoginId
,      o.ReceiptOrderDate
FROM   T_Order o
       INNER JOIN T_OrderSummary os ON (os.OrderSeq = o.OrderSeq)
       INNER JOIN T_Enterprise e ON (e.EnterpriseId = o.EnterpriseId)
WHERE  o.OrderSeq = :OrderSeq
EOQ;
        $row = $this->_adapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current();

        $aryEnterprise['LoginId'] = $row['LoginId'];
        $aryEnterprise['ReceiptOrderDate'] = $row['ReceiptOrderDate'];
        $aryEnterprise['FileDate'] = date('Y-m-d_Hi');
        $aryEnterprise['Password'] = BaseGeneralUtils::makeRandStr(8);
        $aryEnterprise['Orders'][] =
            array('OrderId' => $row['OrderId'], 'NameKj' => $row['NameKj'], 'Ent_OrderId' => $row['Ent_OrderId'], 'CancelReason' => $row['CancelReason'], 'Note' => $row['Note']);
        $aryEnterprise['OrderIdStr'] = $row['OrderId'];

        $mail = new CoralMail($this->_adapter, $this->_smtp);

        // ZIPファイル作成
        $zipFileFullPath = $this->makeZipFile($aryEnterprise);

        // 間違い伝番修正依頼メール送信
        $mail->SendRequestModifyJournal($aryEnterprise, $zipFileFullPath, $userId);

        // 解凍パスワード通知メール送信
        $mail->SendRequestModifyJournalUnzipPassword($aryEnterprise, $userId);

        // ファイル削除(※ZIP)
        unlink( $zipFileFullPath );

        // 注文履歴へ登録(伝票確認メール送信)
        $history = new CoralHistoryOrder($this->_adapter);
        $history->InsOrderHistory($oseq, 37, $userId);
    }

    /**
     * ベースSQL(間違い伝票修正依頼メールバッチ抽出)の取得
     *
     * @return string SQL文字列
     */
    protected function getBaseSql()
    {
        return <<<EOQ
SELECT CASE WHEN ( (SELECT MIN(ch.ClaimDate) FROM T_ClaimHistory ch WHERE ch.OrderSeq = o.P_OrderSeq AND ch.ClaimPattern = 2 AND ch.ValidFlg = 1 AND ch.PrintedFlg = 1) BETWEEN (SELECT MAX(BusinessDate) FROM T_BusinessCalendar WHERE BusinessFlg = 1 AND BusinessDate < DATE(NOW())) AND DATE(NOW()) - INTERVAL 1 DAY ) THEN '再１'
            ELSE '再３'
       END as ClaimPattern
,      o.OrderId
,      os.NameKj
,      o.Ent_OrderId
,      '注文キャンセル' AS CancelReason
,      '長期未着荷状態につき' AS Note
,      e.LoginId
,      o.ReceiptOrderDate
,      o.OrderSeq
FROM   T_Order o
       STRAIGHT_JOIN AT_Order ao ON o.OrderSeq = ao.OrderSeq
       STRAIGHT_JOIN T_OrderSummary os ON o.OrderSeq = os.OrderSeq
       STRAIGHT_JOIN M_DeliveryMethod dm ON os.Deli_DeliveryMethod = dm.DeliMethodId
       STRAIGHT_JOIN T_Enterprise e ON o.EnterpriseId = e.EnterpriseId
WHERE  1 = 1
AND    IFNULL(o.OutOfAmends, 0) = 0
AND    IFNULL(o.Deli_ConfirmArrivalFlg, 0) <> 1
AND    o.DataStatus IN (51, 61)
AND    o.Cnl_Status = 0
AND    (  (SELECT MIN(ch.ClaimDate) FROM T_ClaimHistory ch WHERE ch.OrderSeq = o.P_OrderSeq AND ch.ClaimPattern = 2 AND ch.ValidFlg = 1 AND ch.PrintedFlg = 1) BETWEEN (SELECT MAX(BusinessDate) FROM T_BusinessCalendar WHERE BusinessFlg = 1 AND BusinessDate < DATE(NOW())) AND DATE(NOW()) - INTERVAL 1 DAY
       OR (SELECT MIN(ch.ClaimDate) FROM T_ClaimHistory ch WHERE ch.OrderSeq = o.P_OrderSeq AND ch.ClaimPattern = 4 AND ch.ValidFlg = 1 AND ch.PrintedFlg = 1) BETWEEN (SELECT MAX(BusinessDate) FROM T_BusinessCalendar WHERE BusinessFlg = 1 AND BusinessDate < DATE(NOW())) AND DATE(NOW()) - INTERVAL 1 DAY
       )
AND    dm.SendMailRequestModifyJournalFlg = 1
AND    e.SendMailRequestModifyJournalFlg = 1
AND    ao.StopSendMailConfirmJournalFlg = 0
ORDER BY LoginId, ReceiptOrderDate
EOQ;
    }

    /**
     * ZIPファイル作成(Linuxコマンド版)
     *
     * @param array 加盟店情報
     * @return string Zipファイル名
     */
    protected function makeZipFile($aryEnterprise)
    {
        // CSVテンプレート
        $templateId = 'CKA01009_1';     // テンプレートID
        $templateClass = 0;             // 区分
        $seq = 0;                       // シーケンス
        $templatePattern = 0;           // テンプレートパターン

        // ファイル名生成
        $tmpFileName = tempnam( sys_get_temp_dir(), 'tmp' );    // 例) "C:\Users\ndcuser\AppData\Local\Temp\tmp8FB7.tmp"
        $baseName = basename($tmpFileName);                     // 例) "tmp8FB7.tmp"
        $csvFileName = str_replace($baseName, $aryEnterprise['LoginId'] . '_' . $aryEnterprise['FileDate'] . '.csv', $tmpFileName);
        $zipFileName = str_replace($baseName, $aryEnterprise['LoginId'] . '_' . $aryEnterprise['FileDate'] . '.zip', $tmpFileName);

        // CSVファイル生成
        $logicTemplate = new LogicTemplate($this->_adapter);
        $result = $logicTemplate->convertArraytoFile($aryEnterprise['Orders'], $csvFileName, $templateId, $templateClass, $seq, $templatePattern);
        if( $result == false ) {
            throw new \Exception( $logicTemplate->getErrorMessage() );
        }

        // ZIP化(zipコマンド実行)
        system(sprintf('zip -jP %s %s %s > /dev/null', $aryEnterprise['Password'], $zipFileName, $csvFileName));

        // ファイル削除(※CSV)
        unlink($csvFileName);

        return $zipFileName;
    }

    /**
     * 書式化注文ID文字列の生成
     * (一行を５番号として、それ以上は改行する)
     *
     * @param array 注文情報
     * @return string 書式化注文ID文字列
     */
    protected function makeOrderIdStr($aryOrders)
    {
        $retval = '';

        $aryOrdersCount = 0;
        if(!empty($aryOrders)) {
            $aryOrdersCount = count($aryOrders);
        }
        for ($i=0; $i<$aryOrdersCount; $i++) {
            if ($i == 0) {
                $retval .= $aryOrders[$i]['OrderId'];
            }
            else if ($i % 5 == 0) {
                $retval .= ("\r\n" . $aryOrders[$i]['OrderId']);
            }
            else {
                $retval .= (', ' . $aryOrders[$i]['OrderId']);
            }
        }

        return $retval;
    }
}
