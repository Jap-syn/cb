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
use models\Table\ATablePayingAndSales;
use models\Table\ATableReceiptControl;
use models\Table\TableOrder;
use models\Table\TablePayingAndSales;
use models\Table\TableReceiptControl;
use models\Table\TableStagnationAlert;
use models\Table\TableSundryControl;
use models\Table\TableUser;
use Coral\Coral\History\CoralHistoryOrder;
use Coral\Base\Application\BaseApplicationAbstract;
use Coral\Base\BaseLog;
use Zend\Db\Adapter\Adapter;
use Zend\Config\Reader\Ini;
use Zend\I18n\Translator\LoaderPluginManager;
use Zend\Db\Sql\Ddl\Column\Decimal;

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

$this->logger->info('importPaymentAfterArrival.php start');

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

            // T_ImportedPaymentAfterArrival登録(または更新)
            $seq = -1;
            $mdlinst = new \models\Table\TableImportedPaymentAfterArrival($this->dbAdapter);
            $row = $this->dbAdapter->query(" SELECT Seq FROM T_ImportedPaymentAfterArrival WHERE FileName = :FileName ")->execute(array(':FileName' => $_SERVER['argv'][1]))->current();
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

            // 届いてから決済結果ファイル取込
            $errormessage = "";
            $isSuccess = $this->importPaymentAfterArrival($pathFileName, $seq, $userId, $errormessage);

            // ファイル削除
            unlink($pathFileName);

            // T_ImportedPaymentAfterArrival更新
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

$this->logger->info('importPaymentAfterArrival.php end');
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
     * 届いてから決済結果ファイル取込
     *
     * @param string $fileName ファイル名
     * @param int $seq T_ImportedNttSmartTradeのSEQ
     * @param int $userId ユーザID
     * @return boolean true:成功／false:失敗
     */
    protected function importPaymentAfterArrival($fileName, $seq, $userId, &$errormessage) {

        $handle = null;
        try {
            // トランザクション開始
            $this->dbAdapter->getDriver()->getConnection()->beginTransaction();

            $stm = $this->dbAdapter->query($this->getBaseP_ReceiptControl());

            // SQL実行結果取得用のSQL
            $getretvalsql = " SELECT @po_ret_sts AS po_ret_sts, @po_ret_errcd AS po_ret_errcd, @po_ret_sqlcd AS po_ret_sqlcd, @po_ret_msg AS po_ret_msg ";

            $today = date('Y-m-d');

            $handle = fopen($fileName, "r");

            $d_recType          = ''; // レコード区分
            $d_payType          = ''; // 支払方法区分
            $d_dataType         = ''; // データ種別
            $d_getType          = ''; // 回収区分
            $d_buyType          = ''; // 決済区分
            $d_keyInfo          = ''; // キイ情報
            $d_optionFlg        = ''; // 符号
            $d_buyAmount        = ''; // 入金金額
            $d_custInDate       = ''; // 顧客入金日付
            $d_accDate          = ''; // 口座入金日付
            $d_payingCompany    = ''; // 入金会社コード
            $d_payingShop       = ''; // 入金店舗コード

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

            $kakuOrder = array();   // 確定配列変数

            // 入金ループ
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {

                // チェック
                if (mb_strlen($data[0], 'sjis-win') != 97) {
                    throw new \Exception('レコード形式が不正です');
                }

                // レコード区分
                $recordCode = (int)substr($data[0], 0, 1);

                if ($recordCode == 1) {
                    //--------------------------------------------
                    // (ヘッダ)
                }
                else if ($recordCode == 2) {
                    //--------------------------------------------
                    // (ボディ)
                    $d_recType        = substr($data[0],  0,  1); // レコード区分
                    $d_payType        = substr($data[0],  1,  1); // 支払方法区分
                    $d_dataType       = substr($data[0],  2,  1); // データ種別
                    $d_getType        = substr($data[0],  3,  1); // 回収区分
                    $d_buyType        = substr($data[0],  4,  2); // 決済区分
                    $d_keyInfo        = substr($data[0],  6, 53); // キイ情報
                    $d_optionFlg      = substr($data[0], 59,  1); // 符号
                    $d_buyAmount      = substr($data[0], 60,  9); // 入金金額
                    $d_custInDate     = substr($data[0], 69,  8); // 顧客入金日付
                    $d_accDate        = substr($data[0], 77,  8); // 口座入金日付
                    $d_payingCompany  = substr($data[0], 85,  5); // 入金会社コード
                    $d_payingShop     = substr($data[0], 90,  7); // 入金店舗コード

                    $order = array(
                            'keyInfo'       => $d_keyInfo,
                            'payType'       => $d_payType,
                            'dataType'      => $d_dataType,
                            'getType'       => $d_getType,
                            'buyType'       => $d_buyType,
                            'keyInfo'       => $d_keyInfo,
                            'optionFlg'     => $d_optionFlg,
                            'buyAmount'     => $d_buyAmount,
                            'custInDate'    => $d_custInDate,
                            'accDate'       => $d_accDate,
                            'payingCompany' => $d_payingCompany,
                            'payingShop'    => $d_payingShop,
                            'receiptDate'   => substr($d_custInDate, 0, 4) . '-' . substr($d_custInDate, 4, 2) . '-' . substr($d_custInDate, 6, 2),
                            'depositDate'   => substr($d_accDate, 0, 4) . '-' . substr($d_accDate, 4, 2) . '-' . substr($d_accDate, 6, 2),
                            'paymentAmount' => ($d_optionFlg == '-') ? ($d_buyAmount * -1) : $d_buyAmount
                    );

                    // (確報)
                    if ($d_dataType == 2 ) {
                        $kakuOrder[] = $order;
                    } else {
                        continue;
                    }
                }
            }

            $rdata = array();   // 表示用(返品)データ
            $edata = array();   // 表示用(エラー)データ

            // 確報ループ
            foreach ($kakuOrder as $line) {

                // (入金日)
                $receiptDate = str_replace( '/', '-', $line['receiptDate'] );
                $depositDate = str_replace( '/', '-', $line['depositDate'] );

                //
                $tmpDisplay = array();

                // トラッキングID
                $trackingId = mb_substr( $line['keyInfo'], 0, 14 );

                if ($line['payType'] != '7') {
                    // (表示用(エラー)データ登録)
                    $tmpDisplay = array(
                            'syunoKbnCode'    => $line['payType'],
                            'OrderId'         => '',
                            'OrderSeq'        => 0,
                            'paymentAmount'   => $line['paymentAmount'],
                            'receiptDate'     => $receiptDate,
                            'Error'           => '支払方法区分が「SBペイメント」のデータではありません。トラッキングＩＤ：' . $trackingId,
                    );
                    if ( $line['getType'] == 3 ) {
                        $rdata[] = $tmpDisplay;
                    } else {
                        $edata[] = $tmpDisplay;
                    }
                    continue;
                }

                // トラッキングＩＤに該当する注文の有無チェック
                $sql = ' SELECT ao.OrderSeq, o.OrderId';
                $sql .= ' FROM AT_Order AS ao ';
                $sql .= ' INNER JOIN T_Order AS o ON o.OrderSeq = ao.OrderSeq';
                $sql .= ' WHERE ao.ExtraPayType = 1';
                $sql .= ' AND ao.ExtraPayKey = :ExtraPayKey';
                $prm = array( ':ExtraPayKey' => $trackingId );
                $row_order = $this->dbAdapter->query( $sql )->execute( $prm )->current();
                if ( empty($row_order) ) {
                    // (表示用(エラー)データ登録)
                    $tmpDisplay = array(
                            'syunoKbnCode'    => $line['payType'],
                            'OrderId'         => '',
                            'OrderSeq'        => 0,
                            'paymentAmount'   => $line['paymentAmount'],
                            'receiptDate'     => $receiptDate,
                            'Error'           => '注文が特定できない。トラッキングＩＤ：' . $trackingId,
                    );
                    // 返品の場合
                    if ( $line['getType'] == 3 ) {
                        $rdata[] = $tmpDisplay;
                    } else {
                        $edata[] = $tmpDisplay;
                    }
                    continue;
                }

                $orderSeq = $row_order['OrderSeq'];
                $orderId = $row_order['OrderId'];
                $prm = array( ':OrderSeq' => $orderSeq );

                // 返品の場合のみ
                if ( $line['getType'] != 3 ) {
                    // 処理しようとしている注文データが入金待ち、一部入金、入金済みクローズであるかのチェック
                    $sql = 'SELECT COUNT(*) AS cnt FROM T_Order WHERE (DataStatus IN (51, 61) OR (DataStatus = 91 AND CloseReason = 1)) AND Cnl_Status = 0 AND OrderSeq = :OrderSeq';
                    $checkCount = $this->dbAdapter->query( $sql )->execute( $prm )->current()['cnt'];

                    if ( $checkCount == 0 ) {
                        // (注文有無)
                        $sql = 'SELECT * FROM T_Order WHERE OrderSeq = :OrderSeq';
                        $orderData = $this->dbAdapter->query( $sql )->execute( $prm )->current();

                        // (表示用データ登録)
                        $edata[] = array(
                                'syunoKbnCode'    => $line['payType'],
                                'OrderId'         => $orderId,
                                'OrderSeq'        => $orderSeq,
                                'paymentAmount'   => $line['paymentAmount'],
                                'receiptDate'     => $line['receiptDate'],
                                'Error'           => ( $line['getType'] == 3 ) ? 'キャンセル済みクローズではありません' : '入金待ちではありません',
                        );
                        continue;
                    }
                }

                // 本段階で入金可能な注文SEQ通知が行われた

                // 入金前データステータスの取得
                $sql = 'SELECT * FROM T_Order WHERE OrderSeq = :OrderSeq';
                $orderRow = $this->dbAdapter->query( $sql )->execute( $prm )->current();

                // 請求残高の取得
                $sql = 'SELECT cc.ClaimedBalance FROM T_Order o INNER JOIN T_ClaimControl cc ON (cc.OrderSeq = o.OrderSeq) WHERE o.OrderSeq = :OrderSeq';
                $claimedBalance = (int)$this->dbAdapter->query( $sql )->execute( $prm )->current()['ClaimedBalance'];

                // 入金プロシージャー(P_ReceiptControl)呼び出し
                $prm2 = array(
                        ':pi_receipt_amount'   => (int)$line['paymentAmount'],
                        ':pi_order_seq'        => $orderSeq,
                        ':pi_receipt_date'     => $receiptDate,
                        ':pi_receipt_class'    => 5,
                        ':pi_branch_bank_id'   => null,
                        ':pi_receipt_agent_id' => null,
                        ':pi_deposit_date'     => $depositDate,
                        ':pi_user_id'          => $userId,
                        ':pi_receipt_note'     => null,
                );

                try {
                    $ri = $stm->execute($prm2);

                    // SQL実行例外なしもエラー戻り値の時は例外をｽﾛｰ
                    $retval = $this->dbAdapter->query( $getretvalsql )->execute( null )->current();
                    if ($retval['po_ret_sts'] != 0) {
                    // (表示用データ登録)
                    $tmpDisplay = array(
                            'syunoKbnCode'    => $line['payType'],
                            'OrderId'         => $orderId,
                            'OrderSeq'        => $orderSeq,
                            'paymentAmount'   => $line['paymentAmount'],
                            'receiptDate'     => $line['receiptDate'],
                            'Error'           => $retval['po_ret_msg'],
                    );
                    // 返品の場合
                    if ( $line['getType'] == 3 ) {
                        $rdata[] = $tmpDisplay;
                    } else {
                        $edata[] = $tmpDisplay;
                    }
                    continue;
                    //throw new \Exception( $retval['po_ret_msg'] );
                    }
                }
                catch(\Exception $e) { throw $e;
                }

                // 未印刷の請求書印刷予約データを削除
                $mdlch = new models\Table\TableClaimHistory( $this->dbAdapter );
                $mdlch->deleteReserved( $orderSeq, $userId );

                // 注文履歴の登録
                $history = new \Coral\Coral\History\CoralHistoryOrder( $this->dbAdapter );
                $history->InsOrderHistory( $orderSeq, 61, $userId );

                // AT_ReceiptControl登録
                $sql = "SELECT MAX(ReceiptSeq) AS ReceiptSeq FROM T_ReceiptControl WHERE OrderSeq = :OrderSeq";
                $receiptSeq = $this->dbAdapter->query( $sql )->execute( $prm )->current()['ReceiptSeq'];

                $sql = "SELECT ClearConditionForCharge, ClearConditionDate FROM T_PayingAndSales WHERE OrderSeq = :OrderSeq";
                $rowATReceiptControl = $this->dbAdapter->query( $sql )->execute( $prm )->current();
                $clearConditionForCharge = $rowATReceiptControl['ClearConditionForCharge'];
                $clearConditionDate = $rowATReceiptControl['ClearConditionDate'];

                // 入金取消前立替処理－ステータス、入金取消前配送－着荷確認
                $sql = "SELECT Chg_Status, Deli_ConfirmArrivalFlg FROM T_Order WHERE OrderSeq = :OrderSeq";
                $ri = $this->dbAdapter->query( $sql )->execute( $prm );

                $chgStatus = $ri->current()['Chg_Status'];
                $deliConfirmArrivalFlg = $ri->current()['Deli_ConfirmArrivalFlg'];

                $atdata = array(
                        'ReceiptSeq'                     => $receiptSeq,
                        'AccountNumber'                  => null,
                        'ClassDetails'                   => null,
                        'BankFlg'                        => 2,
                        'Before_ClearConditionForCharge' => $clearConditionForCharge,
                        'Before_ClearConditionDate'      => $clearConditionDate,
                        'Before_Chg_Status'              => $chgStatus,
                        'Before_Deli_ConfirmArrivalFlg'  => $deliConfirmArrivalFlg
                );

                $mdl_atrc = new \models\Table\ATableReceiptControl( $this->dbAdapter );
                $mdl_atrc->saveNew($atdata);

                // サマリー
                $summary[3]['recordCount'] += 1;
                $summary[3]['paymentAmount'] += $line['paymentAmount'];
                $summary[3]['claimAmount'] += $claimedBalance;
                $summary[3]['sagakuAmount'] += ($claimedBalance - $line['paymentAmount']);

                // 表示用退避（返品）
                if ($line['getType'] == 3) {
                    $rdata[] = array(
                            'syunoKbnCode'    => $line['payType'],
                            'OrderId'         => $orderId,
                            'OrderSeq'        => $orderSeq,
                            'paymentAmount'   => (int)$line['paymentAmount'],
                            'receiptDate'     => $line['receiptDate'],
                            'Error'           => ( ( is_null($orderData['DepositDate'] ) ) ? '' : '入金日(' . $orderData['DepositDate'] . ')' ),
                    );
                }
            }

            fclose($handle);

            // 表示用（サマリー）
            $receiptresult = array();
            $receiptresult['summary'] = $summary;

            // 表示用（エラー）
            if (count($edata) > 0) {
                $receiptresult['edata'] = $edata;
            }

            // 表示用（返品）
            if (count($rdata) > 0) {
                $receiptresult['rdata'] = $rdata;
            }

            $sql = 'UPDATE T_ImportedPaymentAfterArrival SET ReceiptResult = :ReceiptResult WHERE Seq = :Seq';
            $prm = array(
                    ':Seq' => $seq,
                    ':ReceiptResult' => \Zend\Json\Json::encode( $receiptresult )
            );
            $this->dbAdapter->query( $sql )->execute( $prm );

            // コミット
            $this->dbAdapter->getDriver()->getConnection()->commit();

            return true;

        } catch(\Exception $e) {
            // ロールバック
            $this->dbAdapter->getDriver()->getConnection()->rollback();
            if ($handle) { fclose($handle); }
            $errormessage = $e->getMessage();

$this->logger->info('importPaymentAfterArrival.php err：'. $errormessage);
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
