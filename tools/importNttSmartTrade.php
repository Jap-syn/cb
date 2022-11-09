<?php
chdir(dirname(__DIR__));

// Setup autoloading
require 'init_autoloader.php';

setlocale( LC_ALL, 'ja_JP.UTF-8' );
ini_set( 'default_charset', 'UTF-8' );
mb_internal_encoding( 'UTF-8' );
mb_http_output('UTF-8');
mb_regex_encoding( 'UTF-8' );

ini_set( 'max_execution_time', 0 );

/**
 * アプリケーションクラスです。
 *
 */
use Coral\Base\Application\BaseApplicationAbstract;
use Coral\Base\BaseLog;
use Zend\Db\Adapter\Adapter;
use Zend\Config\Reader\Ini;
use models\Table\TableUser;

class Application extends BaseApplicationAbstract {
    protected $_application_id = 'tools';

    /**
     * Application の唯一のインスタンスを取得します。
     *
     * @static
     * @access public
     * @return Application
     */
    public static function getInstance() {
        if( self::$_instance === null ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Application の新しいインスタンスを初期化します。
     *
     * @ignore
     * @access private
     */
    private function __construct() {
        parent::init();
    }

    /**
     * @var Adapter
     */
    public $dbAdapter;

    /**
     * @var Log
     */
    public $logger;

    /**
     * @var メール環境
     */
    public $mail;

    /**
     * アプリケーションを実行します。
     *
     * @access public
     */
    public function run() {
        $exitCode = 1;

        try {

            $configPath = __DIR__ . '/../module/cbadmin/config/config.ini';
            // データベースアダプタをiniファイルから初期化します
            $data = array();
            if (file_exists($configPath))
            {
                $reader = new Ini();
                $data = $reader->fromFile($configPath);
            }

            $this->dbAdapter = new Adapter($data['database']);

            // ログ設定の読み込み
            $logConfig = $data['log'];
            // 標準ログクラス初期化
            $this->logger = BaseLog::createFromArray( $logConfig );

$this->logger->info('importNttSmartTrade.php start');

            $globalConfig = include __DIR__ . '/../config/autoload/global.php';
            // 接続時間を設定する
            $rds_session_timezone = $globalConfig['RDS_SESSION_TIMEZONE'];
            if (isset($rds_session_timezone)) {
                $this->dbAdapter->query('SET SESSION time_zone = :time_zone')->execute(array(':time_zone'=>$rds_session_timezone));
            }

            // 設定をシステムプロパティテーブルから読み込み
            $apinfo = $this->getApplicationiInfo($this->dbAdapter, 'cbadmin');
            // iniファイルの内容をマージ
            $data = array_merge($data, $apinfo);

            // メールに絡む属性
            $this->mail = $data['mail'];

            // ユーザーID取得
            $mdlu = new TableUser($this->dbAdapter);
            $userId = $mdlu->getUserId(TableUser::USERCLASS_SYSTEM, TableUser::SEQ_BATCH_USER);

            // 引数が取得できないときは処理続行不可
            if ($_SERVER['argc'] != 2) {
                exit(0);
            }

            // T_ImportedNttSmartTrade登録(または更新)
            $seq = -1;
            $mdlinst = new \models\Table\TableImportedNttSmartTrade($this->dbAdapter);
            $row = $this->dbAdapter->query(" SELECT Seq FROM T_ImportedNttSmartTrade WHERE FileName = :FileName ")->execute(array(':FileName' => $_SERVER['argv'][1]))->current();
            if ($row) {
                // (更新 ※インポートエラーからの再処理)
                $seq = $row['Seq'];
                $upddata = array (
                        'Status'        => 0,                   // 0:処理中
                        'RegistDate'    => date('Y-m-d H:i:s'), // ※再処理時は[登録]扱い
                        'RegistId'      => $userId,             // ※再処理時は[登録]扱い
                );
                $mdlinst->saveUpdate($upddata, $seq);
            }
            else {
                // (新規)
                $savedata = array (
                        'FileName'      => $_SERVER['argv'][1], // バッチ引数の1つ目
                        'Status'        => 0,                   // 0:処理中
                        'RegistDate'    => date('Y-m-d H:i:s'),
                        'RegistId'      => $userId,
                );
                $seq = $mdlinst->saveNew($savedata);
            }

            // 読込みファイル特定
            $mdlsp = new \models\Table\TableSystemProperty($this->dbAdapter);
            $transDir = $mdlsp->getValue(\models\Table\TableSystemProperty::DEFAULT_MODULE, 'systeminfo', 'TempFileDir');
            $pathFileName = $transDir . '/' . $_SERVER['argv'][1];

            // NTTスマートトレード結果ファイル取込
            $errormessage = "";
            $isSuccess = $this->importNttSmartTrade($pathFileName, $seq, $userId, $errormessage);

            // ファイル削除
            unlink($pathFileName);

            // T_ImportedNttSmartTrade更新
            $updprm = array ();
            $updprm['Status']       = ($isSuccess) ? 1 : 2;
            $updprm['UpdateDate']   = date('Y-m-d H:i:s');
            $updprm['UpdateId']     = $userId;
            if (!$isSuccess) {
                $errordata = array();
                $errordata[] = $errormessage;
                $receiptresult = array('errordata' => $errordata);
                $updprm['ReceiptResult'] = \Zend\Json\Json::encode($receiptresult);
            }
            $mdlinst->saveUpdate($updprm, $seq);

$this->logger->info('importNttSmartTrade.php end');
            $exitCode = 0;

        } catch( \Exception $e ) {
            // エラーログを出力
            if ( isset($this->logger) ) {
$this->logger->err($e->getMessage());
            }
        }

        // 終了コードを指定して処理終了
        exit($exitCode);

    }

    /**
     * NTTスマートトレード結果ファイル取込
     *
     * @param string $fileName ファイル名
     * @param int $seq T_ImportedNttSmartTradeのSEQ
     * @param int $userId ユーザID
     * @return boolean true:成功／false:失敗
     */
    protected function importNttSmartTrade($fileName, $seq, $userId, &$errormessage) {

        $handle = null;
        try {
            // トランザクション開始
            $this->dbAdapter->getDriver()->getConnection()->beginTransaction();

            $stm = $this->dbAdapter->query($this->getBaseP_ReceiptControl());

            // SQL実行結果取得用のSQL
            $getretvalsql = " SELECT @po_ret_sts AS po_ret_sts, @po_ret_errcd AS po_ret_errcd, @po_ret_sqlcd AS po_ret_sqlcd, @po_ret_msg AS po_ret_msg ";

            $today = date('Y-m-d');

            $handle = fopen($fileName, "r");

            $h_itakuCode        = ''; // (ヘッダ)委託者コード
            $h_syunoKbnCode     = ''; // (ヘッダ)収納区分コード
            $h_sumCnt           = ''; // (ヘッダ)合計件数

            $d_itakuCode        = ''; // (データ)委託者コード
            $d_storageDate      = ''; // (データ)収納日
            $d_nyukinYoteiDate  = ''; // (データ)入金予定日
            $d_seikyuNo         = ''; // (データ)請求番号
            $d_syunoKbnCode     = ''; // (データ)収納区分コード
            $d_amount           = ''; // (データ)収納金額
            $d_ngFlg            = ''; // (データ)NGフラグ
            $d_ngReasonCode     = ''; // (データ)ＮＧ理由コード
            $d_bankName         = ''; // (データ)振込元金融機関名（半角カナ）
            $d_branchName       = ''; // (データ)振込元金融機関支店名（半角カナ）
            $d_iraisya          = ''; // (データ)振込依頼者名（半角カナ）
            $d_sokukakuKbn      = ''; // (データ)速報・確報区分[1：速報／2：確報／9：速報取消]
            $d_bankCode         = ''; // (データ)収納金融機関コード
            $d_branchCode       = ''; // (データ)収納金融機関支店コード
            $d_yokinTypeCode    = ''; // (データ)収納金融機関預金種目コード
            $d_bankAccount      = ''; // (データ)収納金融機関口座番号

            // 表示用(サマリー)データ
            $classSummary = array (
                    'recordCount'   => 0,   // 取込件数
                    'paymentAmount' => 0,   // 支払金額総額
                    'claimAmount'   => 0,   // 請求金額総額
                    'sagakuAmount'  => 0,   // 差額金額総額
            );
            $summary = array(
                    3 => $classSummary,     // 3:コンビニ払い
            );

            $sokuOrder = array();   // 速報配列変数
            $kakuOrder = array();   // 確定配列変数
            $toriOrder = array();   // 取消配列変数

            // 入金ループ
            while (($line = fgetcsv($handle, 1000, ",")) !== false) {

                $data = str_replace('"', '', $line);

                if ($data[0] == '1') {
                    //--------------------------------------------
                    // (ヘッダ)
                    $h_itakuCode        = $data[1];
                    $h_syunoKbnCode     = $data[2];
                    $h_sumCnt           = $data[3];

                    if ($h_syunoKbnCode != '3') {
                        throw new \Exception('収納区分が「コンビニ払い」のデータではありません。');
                    }
                }
                else if ($data[0] == '2') {
                    //--------------------------------------------
                    // (データ)
                    $d_itakuCode        = $data[1];
                    $d_storageDate      = $data[2];
                    $d_nyukinYoteiDate  = $data[3];
                    $d_seikyuNo         = $data[4];
                    $d_syunoKbnCode     = $data[5];
                    $d_amount           = $data[6];
                    $d_ngFlg            = $data[7];
                    $d_ngReasonCode     = $data[8];
                    $d_bankName         = $data[9];
                    $d_branchName       = $data[10];
                    $d_iraisya          = $data[11];
                    $d_sokukakuKbn      = $data[12];
                    $d_bankCode         = $data[13];
                    $d_branchCode       = $data[14];
                    $d_yokinTypeCode    = $data[15];
                    $d_bankAccount      = $data[16];

                    if ($d_syunoKbnCode != '3') {
                        throw new \Exception('収納区分が「コンビニ払い」のデータではありません。');
                    }

                    if ($d_sokukakuKbn == 1     ) {// (速報)
                        $sokuOrder[] = array('orderSeq' => (int)$d_seikyuNo, 'paymentAmount' => $d_amount, 'storageDate' => $d_storageDate);
                    }
                    else if ($d_sokukakuKbn == 2) {// (確報)
                        $kakuOrder[] = array('orderSeq' => (int)$d_seikyuNo, 'paymentAmount' => $d_amount);
                    }
                    else if ($d_sokukakuKbn == 9) {// (速報取消)
                        $toriOrder[] = array('orderSeq' => (int)$d_seikyuNo, 'paymentAmount' => $d_amount);
                    }
                }
            }

            $edata = array();   // 表示用(エラー)データ
            $cdata = array();   // 表示用(取消)データ
            $adata = array();   // 表示用(その他(確報))データ

            // 速報ループ(入金処理)
            foreach ($sokuOrder as $line) {

                // 処理しようとしている注文データが入金待ち、一部入金、入金済みクローズであるかのチェック
                $checkCount = $this->dbAdapter->query("SELECT COUNT(*) AS cnt FROM T_Order WHERE (DataStatus IN (51, 61) OR (DataStatus = 91 AND CloseReason = 1)) AND Cnl_Status = 0 AND OrderSeq = :OrderSeq "
                        )->execute(array(':OrderSeq' => $line['orderSeq']))->current()['cnt'];
                if ($checkCount == 0) {
                    // (注文有無)
                    $orderData = $this->dbAdapter->query(" SELECT * FROM T_Order WHERE OrderSeq = :OrderSeq ")->execute(array(':OrderSeq' => $line['orderSeq']))->current();

                    // (表示用(エラー)データ登録)
                    $edata[] = array(   'syunoKbnCode'  => 3
                                    ,   'orderSeq'      => ($orderData) ? $orderData['OrderSeq'] : 0
                                    ,   'orderId'       => ($orderData) ? $orderData['OrderId'] : ''
                                    ,   'paymentAmount' => $line['paymentAmount']
                                    ,   'note'          => ($orderData) ? '入金待ちでない' : ('特定できない注文SEQ(' . $line['orderSeq'] . ')')
                    );
                    continue;
                }
                
                //CB_B2C_DEV-377 ☆クレカ決済後の入金を、入金エラー対象にする改修 Start
                //クレジットカードで支払った注文のチェック
                $checkCountCredit = $this->dbAdapter->query("SELECT COUNT(*) AS cnt FROM AT_Order WHERE ExtraPayType = 1 AND ExtraPayKey IS NOT NULL AND OrderSeq = :OrderSeq "
                    )->execute(array(':OrderSeq' => $line['orderSeq']))->current()['cnt'];
                    if ($checkCountCredit >= 1) {
                        // (注文有無)
                        $orderData = $this->dbAdapter->query(" SELECT * FROM T_Order WHERE OrderSeq = :OrderSeq ")->execute(array(':OrderSeq' => $line['orderSeq']))->current();
                        
                        // (表示用(エラー)データ登録)
                        $edata[] = array(   'syunoKbnCode'  => 3
                            ,   'orderSeq'      => ($orderData) ? $orderData['OrderSeq'] : 0
                            ,   'orderId'       => ($orderData) ? $orderData['OrderId'] : ''
                            ,   'paymentAmount' => $line['paymentAmount']
                            ,   'note'          => ($orderData) ? 'クレジットカードで支払った注文' : ('特定できない注文SEQ(' . $line['orderSeq'] . ')')
                        );
                        continue;
                    }
                //CB_B2C_DEV-377 ☆クレカ決済後の入金を、入金エラー対象にする改修 End

                $oseq = $line['orderSeq'];

                // 本段階で入金可能な注文SEQ通知が行われた

                // 7-1. 入金前データステータスの取得
                $orderRow = $this->dbAdapter->query(" SELECT * FROM T_Order WHERE OrderSeq = :OrderSeq ")->execute(array(':OrderSeq' => $oseq))->current();

                // 請求残高の取得
                $sql = " SELECT cc.ClaimedBalance FROM T_Order o INNER JOIN T_ClaimControl cc ON (cc.OrderSeq = o.OrderSeq) WHERE o.OrderSeq = :OrderSeq ";
                $claimedBalance = (int)$this->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current()['ClaimedBalance'];

                // (入金日)
                $receiptDate = str_replace('/', '-', $line['storageDate']);

                // ①入金プロシージャー(P_ReceiptControl)呼び出し
                $prm = array(
                        ':pi_receipt_amount'   => (int)$line['paymentAmount'],
                        ':pi_order_seq'        => $oseq,
                        ':pi_receipt_date'     => $receiptDate,
                        ':pi_receipt_class'    => 1, // 1:コンビニ
                        ':pi_branch_bank_id'   => null,
                        ':pi_receipt_agent_id' => 5, // 5:NTTスマートトレード
                        ':pi_deposit_date'     => $receiptDate,
                        ':pi_user_id'          => $userId,
                        ':pi_receipt_note'     => null,
                );

                try {
                    $ri = $stm->execute($prm);

                    // SQL実行例外なしもエラー戻り値の時は例外をｽﾛｰ
                    $retval = $this->dbAdapter->query($getretvalsql)->execute(null)->current();
                    if ($retval['po_ret_sts'] != 0) {
                        throw new \Exception($retval['po_ret_msg']);
                    }
                }
                catch(\Exception $e) { throw $e; }

                // ②未印刷の請求書印刷予約データを削除
                $mdlch = new models\Table\TableClaimHistory($this->dbAdapter);
                $mdlch->deleteReserved($oseq, $userId);

                // ③立替・売上管理データ更新
                $sql = " SELECT o.DataStatus, o.CloseReason, cc.ClaimedBalance FROM T_Order o INNER JOIN T_ClaimControl cc ON (cc.OrderSeq = o.OrderSeq) WHERE o.OrderSeq = :OrderSeq ";
                $row = $this->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current();

                if ($row['DataStatus'] == 91 AND $row['CloseReason'] == 1) {
                    // (①の処理後、注文が入金済み正常クローズ（DataStatus=91、CloseReason=1）となった場合)
                    $mdlpas = new \models\Table\TablePayingAndSales($this->dbAdapter);

                    $mdlapas = new \models\Table\ATablePayingAndSales($this->dbAdapter);
                    // 既に[立替条件クリアフラグ]が[1：条件をクリアしている]か？の取得
                    $isAlreadyClearCondition = $mdlpas->IsAlreadyClearCondition($oseq);

                    $mdlpas->clearConditionForCharge($oseq, 1, $userId);

                    if (!$isAlreadyClearCondition) {
                        $row_pas = $this->dbAdapter->query(" SELECT Seq FROM T_PayingAndSales WHERE OrderSeq = :OrderSeq "
                        )->execute(array(':OrderSeq' => $oseq))->current();

                        // 入金により立替条件クリアフラグが１化されるとき => '2:入金'として更新(顧客入金日をセットする)
                        $mdlapas->saveUpdate(array('ATUriType' => 2, 'ATUriDay' => str_replace('-', '', $receiptDate)), $row_pas['Seq']);
                    }
                }

                // ④入金確認メールの送信（送信エラーは無視して以降の処理を継続する）
                $sql = " SELECT MAX(ReceiptSeq) AS ReceiptSeq FROM T_ReceiptControl WHERE OrderSeq = :OrderSeq ";
                $receiptSeq = $this->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current()['ReceiptSeq'];
                $sendMailError = '';
                if ($orderRow['DataStatus'] != 91 && $row['DataStatus'] == 91) {// [91：クローズ]からの入金はメール対象から除外
                    try {
                        $mail = new \Coral\Coral\Mail\CoralMail($this->dbAdapter, $this->mail['smtp']);
                        $mail->SendRcptConfirmMail($receiptSeq, $userId);

                    } catch(\Exception $e) {
                        // エラーメッセージを入れておく。
                        $sendMailError = 'メール送信NG';
                    }
                }

                // ⑤注文履歴の登録
                $history = new \Coral\Coral\History\CoralHistoryOrder($this->dbAdapter);
                $history->InsOrderHistory($oseq, 61, $userId);

                // AT_ReceiptControl登録
                $mdl_atrc = new \models\Table\ATableReceiptControl($this->dbAdapter);
                $rowATReceiptControl = $this->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current();
                $clearConditionForCharge = $rowATReceiptControl['ClearConditionForCharge'];
                $clearConditionDate = $rowATReceiptControl['ClearConditionDate'];

                // 入金取消前立替処理－ステータス、入金取消前配送－着荷確認
                $sql = "SELECT Chg_Status, Deli_ConfirmArrivalFlg FROM T_Order WHERE OrderSeq = :OrderSeq";
                $ri = $this->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq));
                $chgStatus = $ri->current()['Chg_Status'];
                $deliConfirmArrivalFlg = $ri->current()['Deli_ConfirmArrivalFlg'];
                $atdata = array(
                        'ReceiptSeq' => $receiptSeq,
                        'AccountNumber' => null,
                        'ClassDetails' => null,
                        'BankFlg' => 2, // 2：直接振込
                        'Before_ClearConditionForCharge' => $clearConditionForCharge,
                        'Before_ClearConditionDate' => $clearConditionDate,
                        'Before_Chg_Status' => $chgStatus,
                        'Before_Deli_ConfirmArrivalFlg' => $deliConfirmArrivalFlg
                );

                $mdl_atrc->saveNew($atdata);

                // サマリー
                $summary[3]['recordCount'] += 1;
                $summary[3]['paymentAmount'] += (int)$line['paymentAmount'];
                $summary[3]['claimAmount'] += $claimedBalance;
                $summary[3]['sagakuAmount'] += ($claimedBalance - (int)$line['paymentAmount']);
            }

            // 取消ループ
            foreach ($toriOrder as $line) {
                // (注文検索)
                $sql  = " SELECT o.OrderSeq ";
                $sql .= " ,      o.OrderId ";
                $sql .= " ,      o.EnterpriseId ";
                $sql .= " ,      rc.DepositDate ";
                $sql .= " FROM   T_Order o ";
                $sql .= "        LEFT OUTER JOIN T_ReceiptControl rc ON (rc.OrderSeq = o.OrderSeq) ";
                $sql .= " WHERE  o.OrderSeq = :OrderSeq ";
                $orderData = $this->dbAdapter->query($sql)->execute(array(':OrderSeq' => $line['orderSeq']))->current();

                // (表示用(取消)データ登録)
                $cdata[] = array(   'syunoKbnCode'  => 3
                                ,   'orderSeq'      => ($orderData) ? $orderData['OrderSeq'] : 0
                                ,   'orderId'       => ($orderData) ? $orderData['OrderId'] : ''
                                ,   'paymentAmount' => $line['paymentAmount']
                                ,   'note'          => ($orderData) ? ((is_null($orderData['DepositDate'])) ? '' : '入金日(' . $orderData['DepositDate'] . ')') : ('特定できない注文SEQ(' . $line['orderSeq'] . ')')
                );

                // (停滞アラート新規登録)
                if ($orderData) {
                    $mdlsa = new \models\Table\TableStagnationAlert($this->dbAdapter);
                    $udata = array(
                            'AlertClass' => 3,                                      // 停滞アラート区分(3：入金取消)※ 仮の区分
                            'AlertSign' => 1,                                       // アラートサイン(1：アラート)
                            'OrderSeq' => $orderData['OrderSeq'],                   // 注文SEQ
                            'StagnationDays' => NULL,                               // 停滞期間日数
                            'EnterpriseId' => $orderData['EnterpriseId'],           // 加盟店ID
                            'AlertJudgDate' => date('Y-m-d H:i:s'),                 // アラート抽出日時
                            'RegistId' => $userId,                                  // 登録者
                            'UpdateId' => $userId,                                  // 更新者
                            'ValidFlg' => 1,                                        // 有効フラグ
                    );
                    $mdlsa->saveNew($udata);
                }
            }

            // 確報ループ
            $aRecordCount = count($kakuOrder);
            $aPaymentAmount = 0;
            foreach ($kakuOrder as $line) {
                $aPaymentAmount += (int)$line['paymentAmount'];
            }
            if ($aRecordCount > 0) {
                $adata[] = array('syunoKbnCode' => 3, 'recordCount' => $aRecordCount, 'paymentAmount' => $aPaymentAmount);
            }

            fclose($handle);

            $receiptresult = array();
            $receiptresult['summary'] = $summary;
            if (count($edata) > 0) {
                $receiptresult['edata'] = $edata;
            }
            if (count($cdata) > 0) {
                $receiptresult['cdata'] = $cdata;
            }
            if (count($adata) > 0) {
                $receiptresult['adata'] = $adata;
            }
            $this->dbAdapter->query(" UPDATE T_ImportedNttSmartTrade SET ReceiptResult = :ReceiptResult WHERE Seq = :Seq "
                    )->execute(array(':Seq' => $seq, ':ReceiptResult' => \Zend\Json\Json::encode($receiptresult)));

            // コミット
            $this->dbAdapter->getDriver()->getConnection()->commit();

            return true;

        } catch(\Exception $e) {
            // ロールバック
            $this->dbAdapter->getDriver()->getConnection()->rollback();
            if ($handle) { fclose($handle); }
            $errormessage = $e->getMessage();
            return false;
        }
    }

    /**
     * 入金関連処理ファンクションの基礎SQL取得。
     *
     * @return 入金関連処理ファンクションの基礎SQL
     */
    protected function getBaseP_ReceiptControl() {
        return <<<EOQ
CALL P_ReceiptControl(
    :pi_receipt_amount
,   :pi_order_seq
,   :pi_receipt_date
,   :pi_receipt_class
,   :pi_branch_bank_id
,   :pi_receipt_agent_id
,   :pi_deposit_date
,   :pi_user_id
,   :pi_receipt_note
,   @po_ret_sts
,   @po_ret_errcd
,   @po_ret_sqlcd
,   @po_ret_msg
    )
EOQ;
    }
}

Application::getInstance()->run();
