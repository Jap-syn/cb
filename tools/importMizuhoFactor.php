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

$this->logger->info('importMizuhoFactor.php start');

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

            // T_ImportedMizuhoFactor登録(または更新)
            $seq = -1;
            $mdlinst = new \models\Table\TableImportedMizuhoFactor($this->dbAdapter);
            $row = $this->dbAdapter->query(" SELECT Seq FROM T_ImportedMizuhoFactor WHERE FileName = :FileName ")->execute(array(':FileName' => $_SERVER['argv'][1]))->current();
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
            $isSuccess = $this->importMizuhoFactor($pathFileName, $seq, $userId, $errormessage);

            // ファイル削除
            unlink($pathFileName);

            // T_ImportedMizuhoFactor更新
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

$this->logger->info('importMizuhoFactor.php end');
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
    protected function importMizuhoFactor($fileName, $seq, $userId, &$errormessage) {

        $handle = null;
        try {
            // トランザクション開始
            $this->dbAdapter->getDriver()->getConnection()->beginTransaction();

            $stm = $this->dbAdapter->query($this->getBaseP_ReceiptControl());

            // SQL実行結果取得用のSQL
            $getretvalsql = " SELECT @po_ret_sts AS po_ret_sts, @po_ret_errcd AS po_ret_errcd, @po_ret_sqlcd AS po_ret_sqlcd, @po_ret_msg AS po_ret_msg ";

            $today = date('Y-m-d');

            $handle = fopen($fileName, "r");

            $d_dataType         = ''; // 種別
            $d_shopDate         = ''; // 店舗収納日
            $d_shopTime         = ''; // 店舗収納時間
            $d_barcodeType      = ''; // バーコード種別
            $d_barcodeData      = ''; // バーコード情報
            $d_dummy            = ''; // ダミー
            $d_shopCode         = ''; // 収納店舗コード
            $d_customerData     = ''; // 客層データ
            $d_inputDate        = ''; // データ取得年月日
            $d_paymentDate      = ''; // 振込予定日
            $d_claimDate        = ''; // 経理処理年月日
            $d_cvsData          = ''; // CVSコード
            $d_free             = ''; // 予備

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
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {

                // チェック
                if (mb_strlen($data[0], 'sjis-win') != 120) {
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
                    $d_dataType         = substr($data[0],   1,  2); // 種別
                    $d_shopDate         = substr($data[0],   3,  8); // 店舗収納日
                    $d_shopTime         = substr($data[0],  11,  4); // 店舗収納時間
                    $d_barcodeType      = substr($data[0],  15,  1); // バーコード種別
                    $d_barcodeData      = substr($data[0],  16, 44); // バーコード情報
                    $d_dummy            = substr($data[0],  60,  3); // ダミー
                    $d_shopCode         = substr($data[0],  63,  7); // 収納店舗コード
                    $d_customerData     = substr($data[0],  70,  2); // 客層データ
                    $d_inputDate        = substr($data[0],  72,  8); // データ取得年月日
                    $d_paymentDate      = substr($data[0],  80,  8); // 振込予定日
                    $d_claimDate        = substr($data[0],  88,  8); // 経理処理年月日
                    $d_cvsData          = substr($data[0],  96,  6); // CVSコード
                    $d_free             = substr($data[0], 102, 18); // 予備

                    $order = array(
                            'dataType'        => $d_dataType     ,
                            'shopDate'        => $d_shopDate     ,
                            'shopTime'        => $d_shopTime     ,
                            'barcodeType'     => $d_barcodeType  ,
                            'barcodeData'     => $d_barcodeData  ,
                            'dummy'           => $d_dummy        ,
                            'shopCode'        => $d_shopCode     ,
                            'customerData'    => $d_customerData ,
                            'inputDate'       => $d_inputDate    ,
                            'paymentDate'     => $d_paymentDate  ,
                            'claimDate'       => $d_claimDate    ,
                            'cvsData'         => $d_cvsData      ,
                            'receiptDate'     => substr($d_shopDate, 0, 4) . '-' . substr($d_shopDate, 4, 2) . '-' . substr($d_shopDate, 6, 2),
                            'depositDate'     => substr($d_inputDate, 0, 4) . '-' . substr($d_inputDate, 4, 2) . '-' . substr($d_inputDate, 6, 2),
                            'paymentAmount'   => substr($d_barcodeData, 37, 6)
                    );

                    // 種別毎に格納
                    if ($d_dataType == '01'     ) {
                        // (速報)
                        $sokuOrder[] = $order;
                    }
                    else if ($d_dataType == '02') {
                        // (確報)
                        $kakuOrder[] = $order;
                    }
                    else if ($d_dataType == '03') {
                        // (速報取消)
                        $toriOrder[] = $order;
                    }

                }
            }

            $edata = array();   // 表示用(エラー)データ
            $cdata = array();   // 表示用(取消)データ
            $adata = array();   // 表示用(その他(確報))データ

            // 速報ループ(入金処理)
            foreach ($sokuOrder as $line) {
                // バーコードの自由使用欄からキー情報を取得する
                $orderSeq = (int)substr( $line['barcodeData'], 12, 17);
                $oseq = $orderSeq;

                $prm = array(
                        ':OrderSeq' => $orderSeq,
                );

                // 処理しようとしている注文データが入金待ち、一部入金、入金済みクローズであるかのチェック
                $sql  = "SELECT COUNT(*) AS cnt";
                $sql .= " FROM T_Order";
                $sql .= " WHERE (DataStatus IN (51, 61) OR (DataStatus = 91 AND CloseReason = 1))";
                $sql .= " AND Cnl_Status = 0";
                $sql .= " AND OrderSeq = :OrderSeq";
                $checkCount = $this->dbAdapter->query( $sql )->execute( $prm )->current()['cnt'];
                if ($checkCount == 0) {
                    // (注文有無)
                    $sql  = "SELECT";
                    $sql .= " OrderSeq";
                    $sql .= ", OrderId";
                    $sql .= " FROM T_Order";
                    $sql .= " WHERE OrderSeq = :OrderSeq";
                    $orderData = $this->dbAdapter->query( $sql )->execute( $prm )->current();

                    // (表示用(エラー)データ登録)
                    $edata[] = array(
                            'syunoKbnCode'  => 3,
                            'orderSeq'      => ($orderData) ? $orderData['OrderSeq'] : 0,
                            'orderId'       => ($orderData) ? $orderData['OrderId'] : '',
                            'paymentAmount' => $line['paymentAmount'],
                            'note'          => ($orderData) ? '入金待ちでない' : ('特定できない注文SEQ('. $orderSeq. ')'),
                    );
                    continue;
                }

                //CB_B2C_DEV-377 ☆クレカ決済後の入金を、入金エラー対象にする改修 Start
                //クレジットカードで支払った注文のチェック
                $sqlCredit = "SELECT COUNT(*) AS cnt FROM AT_Order WHERE ExtraPayType = 1 AND ExtraPayKey IS NOT NULL AND OrderSeq = :OrderSeq";
                $checkCountCredit = $this->dbAdapter->query($sqlCredit)->execute( $prm )->current()['cnt'];
                if ($checkCountCredit >= 1) {
                    // (注文有無)
                    $sql  = "SELECT";
                    $sql .= " OrderSeq";
                    $sql .= ", OrderId";
                    $sql .= " FROM T_Order";
                    $sql .= " WHERE OrderSeq = :OrderSeq";
                    $orderData = $this->dbAdapter->query( $sql )->execute( $prm )->current();
                    
                    // (表示用(エラー)データ登録)
                    $edata[] = array(
                        'syunoKbnCode'  => 3,
                        'orderSeq'      => ($orderData) ? $orderData['OrderSeq'] : 0,
                        'orderId'       => ($orderData) ? $orderData['OrderId'] : '',
                        'paymentAmount' => $line['paymentAmount'],
                        'note'          => ($orderData) ? 'クレジットカードで支払った注文' : ('特定できない注文SEQ('. $orderSeq. ')'),
                    );
                    continue;
                }
                //CB_B2C_DEV-377 ☆クレカ決済後の入金を、入金エラー対象にする改修 End

                // 本段階で入金可能な注文SEQ通知が行われた

                // 7-1. 入金前データステータスの取得
                $sql  = "SELECT *";
                $sql .= " FROM T_Order";
                $sql .= " WHERE OrderSeq = :OrderSeq";
                $orderRow = $this->dbAdapter->query( $sql )->execute( $prm )->current();

                // 請求残高の取得
                $sql  = "SELECT cc.ClaimedBalance";
                $sql .= " FROM T_Order AS o";
                $sql .= " INNER JOIN T_ClaimControl AS cc ON (cc.OrderSeq = o.OrderSeq)";
                $sql .= " WHERE o.OrderSeq = :OrderSeq";
                $claimedBalance = (int)$this->dbAdapter->query( $sql )->execute( $prm )->current()['ClaimedBalance'];

                // (入金日)
                $receiptDate = str_replace('/', '-', $line['receiptDate']);
                $depositDate = str_replace('/', '-', $line['depositDate']);

                // ①入金プロシージャー(P_ReceiptControl)呼び出し
                $prmP = array(
                        ':pi_receipt_amount'   => (int)$line['paymentAmount'],
                        ':pi_order_seq'        => $oseq,
                        ':pi_receipt_date'     => $receiptDate,
                        ':pi_receipt_class'    => 1, // 1:コンビニ
                        ':pi_branch_bank_id'   => null,
                        ':pi_receipt_agent_id' => null,
                        ':pi_deposit_date'     => $depositDate,
                        ':pi_user_id'          => $userId,
                        ':pi_receipt_note'     => null
                );

                try {
                    $ri = $stm->execute( $prmP );

                    // SQL実行例外なしもエラー戻り値の時は例外をｽﾛｰ
                    $retval = $this->dbAdapter->query( $getretvalsql )->execute(null)->current();
                    if ($retval['po_ret_sts'] != 0) {
                        throw new \Exception($retval['po_ret_msg']);
                    }
                }
                catch(\Exception $e) { throw $e; }

                // ②未印刷の請求書印刷予約データを削除
                $mdlch = new models\Table\TableClaimHistory($this->dbAdapter);
                $mdlch->deleteReserved($oseq, $userId);

                // ③立替・売上管理データ更新
                $sql  = "SELECT";
                $sql .= " o.DataStatus";
                $sql .= ", o.CloseReason";
                $sql .= ", cc.ClaimedBalance";
                $sql .= " FROM T_Order AS o";
                $sql .= " INNER JOIN T_ClaimControl AS cc ON (cc.OrderSeq = o.OrderSeq)";
                $sql .= " WHERE o.OrderSeq = :OrderSeq";
                $row = $this->dbAdapter->query( $sql )->execute( $prm )->current();

                if ($row['DataStatus'] == 91 AND $row['CloseReason'] == 1) {
                    // (①の処理後、注文が入金済み正常クローズ（DataStatus=91、CloseReason=1）となった場合)
                    $mdlpas = new \models\Table\TablePayingAndSales($this->dbAdapter);

                    $mdlapas = new \models\Table\ATablePayingAndSales($this->dbAdapter);
                    // 既に[立替条件クリアフラグ]が[1：条件をクリアしている]か？の取得
                    $isAlreadyClearCondition = $mdlpas->IsAlreadyClearCondition( $oseq );

                    $mdlpas->clearConditionForCharge($oseq, 1, $userId);

                    if (!$isAlreadyClearCondition) {
                        $sql  = "SELECT Seq";
                        $sql .= " FROM T_PayingAndSales";
                        $sql .= " WHERE OrderSeq = :OrderSeq";
                        $row_pas = $this->dbAdapter->query( $sql )->execute( $prm )->current();

                        // 入金により立替条件クリアフラグが１化されるとき => '2:入金'として更新(顧客入金日をセットする)
                        $prmS = array(
                                'ATUriType' => 2,
                                'ATUriDay' => str_replace('-', '', $receiptDate)
                        );
                        $mdlapas->saveUpdate( $prmS , $row_pas['Seq'] );
                    }
                }

                // ④入金確認メールの送信（送信エラーは無視して以降の処理を継続する）
                $sql  = "SELECT";
                $sql .= " MAX(ReceiptSeq) AS ReceiptSeq";
                $sql .= " FROM T_ReceiptControl";
                $sql .= " WHERE OrderSeq = :OrderSeq";
                $receiptSeq = $this->dbAdapter->query( $sql )->execute( $prm )->current()['ReceiptSeq'];
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

                // 印紙代発生の有無
                // 2022.02.02　印紙税発生の条件:バーコードに含まれるフラッグに依存
                if (substr( $line['barcodeData'], 36, 1)== 1) {
                    // 支払方法区分２取得
                    $sql = "SELECT cd.Class2 as Class2
                                FROM T_ReceiptControl as rc
                                LEFT JOIN M_Code as cd ON cd.CodeId = 198 AND cd.KeyCode = rc.ReceiptClass
                                WHERE rc.OrderSeq = :OrderSeq
                                ORDER BY rc.ReceiptSeq DESC LIMIT 1;";
                    $Class2 = $this->dbAdapter->query($sql)->execute( $prm )->current()['Class2'];
                    //支払方法区分2が0:印紙代対象
                    if($Class2==0){
                        //
                        $mdlstmp = new models\Table\TableStampFee($this->dbAdapter);
                        $stampFee['OrderSeq']       = $orderData['OrderSeq'];
                        $stampFee['DecisionDate']   = date('Y-m-d');
                        $stampFee['StampFee']       = 200;
                        $stampFee['ClearFlg']       = 0;
                        $stampFee['CancelFlg']      = 0;
                        $mdlstmp->saveNew($stampFee);
                    }
                }
                
                // ⑤注文履歴の登録
                $history = new \Coral\Coral\History\CoralHistoryOrder($this->dbAdapter);
                $history->InsOrderHistory($oseq, 61, $userId);

                // AT_ReceiptControl登録
                $mdl_atrc = new \models\Table\ATableReceiptControl($this->dbAdapter);
                $rowATReceiptControl = $this->dbAdapter->query( $sql )->execute( $prm )->current();
                $clearConditionForCharge = $rowATReceiptControl['ClearConditionForCharge'];
                $clearConditionDate = $rowATReceiptControl['ClearConditionDate'];

                // 入金取消前立替処理－ステータス、入金取消前配送－着荷確認
                $sql  = "SELECT";
                $sql .= " Chg_Status";
                $sql .= ", Deli_ConfirmArrivalFlg";
                $sql .= " FROM T_Order";
                $sql .= " WHERE OrderSeq = :OrderSeq";
                $ri = $this->dbAdapter->query( $sql )->execute( $prm );
                $chgStatus = $ri->current()['Chg_Status'];
                $deliConfirmArrivalFlg = $ri->current()['Deli_ConfirmArrivalFlg'];
                $atdata = array(
                        'ReceiptSeq'                     => $receiptSeq,
                        'AccountNumber'                  => null,
                        'ClassDetails'                   => null,
                        'BankFlg'                        => 2, // 2：直接振込
                        'Before_ClearConditionForCharge' => $clearConditionForCharge,
                        'Before_ClearConditionDate'      => $clearConditionDate,
                        'Before_Chg_Status'              => $chgStatus,
                        'Before_Deli_ConfirmArrivalFlg'  => $deliConfirmArrivalFlg
                );

                $mdl_atrc->saveNew($atdata);

                // サマリー
                $summary[3]['recordCount']   += 1;
                $summary[3]['paymentAmount'] += (int)$line['paymentAmount'];
                $summary[3]['claimAmount']   += $claimedBalance;
                $summary[3]['sagakuAmount']  += ($claimedBalance - (int)$line['paymentAmount']);
            }

            // 取消ループ
            foreach ($toriOrder as $line) {
                // バーコードの自由使用欄からキー情報を取得する
                $orderSeq = (int)substr( $line['barcodeData'], 12, 17);

                $prm = array(
                        ':OrderSeq' => $orderSeq,
                );

                // (注文検索)
                $sql  = "SELECT";
                $sql .= " o.OrderSeq";
                $sql .= ", o.OrderId ";
                $sql .= ", o.EnterpriseId ";
                $sql .= ", rc.DepositDate ";
                $sql .= " FROM T_Order AS o ";
                $sql .= " LEFT OUTER JOIN T_ReceiptControl AS rc ON (rc.OrderSeq = o.OrderSeq)";
                $sql .= " WHERE  o.OrderSeq = :OrderSeq";
                $orderData = $this->dbAdapter->query( $sql )->execute( $prm )->current();

                // (表示用(取消)データ登録)
                $cdata[] = array(
                        'syunoKbnCode'  => 3,
                        'orderSeq'      => ($orderData) ? $orderData['OrderSeq'] : 0,
                        'orderId'       => ($orderData) ? $orderData['OrderId'] : '',
                        'paymentAmount' => $line['paymentAmount'],
                        'note'          => ($orderData) ? ((is_null($orderData['DepositDate'])) ? '' : '入金日('. $orderData['DepositDate']. ')') : ('特定できない注文SEQ('. $orderSeq. ')'),
                );

                // (停滞アラート新規登録)
                if ($orderData) {
                    $mdlsa = new \models\Table\TableStagnationAlert($this->dbAdapter);
                    $udata = array(
                            'AlertClass'     => 3,                            // 停滞アラート区分(3：入金取消)※ 仮の区分
                            'AlertSign'      => 1,                            // アラートサイン(1：アラート)
                            'OrderSeq'       => $orderData['OrderSeq'],       // 注文SEQ
                            'StagnationDays' => null,                         // 停滞期間日数
                            'EnterpriseId'   => $orderData['EnterpriseId'],   // 加盟店ID
                            'AlertJudgDate'  => date('Y-m-d H:i:s'),          // アラート抽出日時
                            'RegistId'       => $userId,                      // 登録者
                            'UpdateId'       => $userId,                      // 更新者
                            'ValidFlg'       => 1,                            // 有効フラグ
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
                $adata[] = array(
                        'syunoKbnCode'  => 3,
                        'recordCount'   => $aRecordCount,
                        'paymentAmount' => $aPaymentAmount
                );
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

            $sql = 'UPDATE T_ImportedMizuhoFactor SET ReceiptResult = :ReceiptResult WHERE Seq = :Seq';
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

$this->logger->info('importMizuhoFactor.php err：'. $errormessage);
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
