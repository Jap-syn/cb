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
use models\Logic\Smbcpa\Account\Receipt\LogicSmbcpaAccountReceiptAuto;
use models\Table\ATablePayingAndSales;
use models\Table\ATableReceiptControl;
use models\Table\TableOrder;
use models\Table\TablePayingAndSales;
use models\Table\TableReceiptControl;
use models\Table\TableStagnationAlert;
use models\Table\TableSundryControl;
use models\Table\TableUser;
use models\Table\TableSmbcpa;
use models\Table\TableSmbcpaAccount;
use models\Table\TableSmbcpaAccountGroup;
use models\Table\TableSmbcpaPaymentNotification;
use models\Table\TableSmbcpaAccountUsageHistory;
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

$this->logger->info('importSmbcPerfect.php start');

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
            // 引数０：実行ファイル名
            // 引数１：取込ファイル名
            // 引数２：自動実行元バッチ名
            if ($_SERVER['argc'] < 2) {
                exit(0);
            }

            //
            $ImportedFlg = false;
            if ($_SERVER['argc'] > 2) {
                $ImportedFlg = (($_SERVER['argv'][2] == "importSmbcSakuraKCS") ? true : false);
            }

$this->logger->info('importSmbcPerfect.php ImportedFile='. $_SERVER['argv'][1]);

            // T_ImportedSmbcPerfect登録(または更新)
            $seq = -1;
            $mdlinst = new \models\Table\TableImportedSmbcPerfect($this->dbAdapter);
            $row = $this->dbAdapter->query(" SELECT Seq FROM T_ImportedSmbcPerfect WHERE FileName = :FileName ")->execute(array(':FileName' => $_SERVER['argv'][1]))->current();
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

            // SMBCパーフェクト口座結果ファイル取込
            $errormessage = "";
            $isSuccess = $this->importSmbcPerfect($pathFileName, $seq, $userId, $errormessage, $ImportedFlg);

            // T_ImportedSmbcPerfect更新
            $updprm = array ();
            $updprm['Status']       = ($isSuccess) ? 1 : 2;
            $updprm['UpdateDate']   = date('Y-m-d H:i:s');
            $updprm['UpdateId']     = $userId;
            if (!$isSuccess) {
                $errordata = array();
                $errordata[] = $errormessage;
                $receiptresult = array('errordata' => $errordata);
                $updprm['ReceiptResult'] = \Zend\Json\Json::encode($receiptresult);
            } else {
                // ファイル削除
                unlink($pathFileName);
            }
            $mdlinst->saveUpdate($updprm, $seq);

$this->logger->info('importSmbcPerfect.php end');
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
     * SMBCパーフェクト口座ファイル取込
     *
     * @param string $fileName ファイル名
     * @param int $seq T_ImportedSmbcPerfectのSEQ
     * @param int $userId ユーザID
     * @return boolean true:成功／false:失敗
     */
    protected function importSmbcPerfect($fileName, $seq, $userId, &$errormessage, $ImportedFlg = false) {

        $handle = null;
        try {
            // トランザクション開始
            $this->dbAdapter->getDriver()->getConnection()->beginTransaction();

            $stm = $this->dbAdapter->query($this->getBaseP_ReceiptControl());

            // SQL実行結果取得用のSQL
            $getretvalsql = " SELECT @po_ret_sts AS po_ret_sts, @po_ret_errcd AS po_ret_errcd, @po_ret_sqlcd AS po_ret_sqlcd, @po_ret_msg AS po_ret_msg ";

            $dateTime = new DateTime();
            $today = $dateTime->format('Y-m-d');
            $sysYear = $dateTime->format('Y');

            $handle = fopen($fileName, "r");

            $h_01 = ''; //  1 データ区分
            $h_02 = ''; //  2 種別コード
            $h_03 = ''; //  1 コード区分
            $h_04 = ''; //  6 作成日
            $h_05 = ''; //  6 勘定日（自）
            $h_06 = ''; //  6 勘定日（至）
            $h_07 = ''; //  4 銀行コード
            $h_08 = ''; // 15 銀行名
            $h_09 = ''; //  3 支店コード
            $h_10 = ''; // 15 支店名
            $h_11 = ''; //  3 ダミー
            $h_12 = ''; //  1 預金種別（1：普通、2：当座、5：通知、6：定期、7：積立）
            $h_13 = ''; // 10 口座番号
            $h_14 = ''; // 40 口座名
            $h_15 = ''; //  1 貸越区分
            $h_16 = ''; //  1 通帳・証券区分
            $h_17 = ''; // 14 取引前残高
            $h_18 = ''; // 71 ダミー

            $d_01 = ''; //  1 データ区分
            $d_02 = ''; //  8 識別番号（右寄せゼロ詰め）
            $d_03 = ''; //  6 勘定日
            $d_04 = ''; //  6 預入・払出日（起算日）
            $d_05 = ''; //  1 入払区分（1：入金、2：出金）
            $d_06 = ''; //  2 取引区分（10：現金、11：振込、12：他店券入金、13：交換、14：振替、15：継続、18：その他、19：訂正）
            $d_07 = ''; // 12 取引金額
            $d_08 = ''; // 12 うち他店券金額
            $d_09 = ''; //  6 交換呈示日
            $d_10 = ''; //  6 不渡返還日
            $d_11 = ''; //  1 手形・小切手区分
            $d_12 = ''; //  7 手形・小切手番号
            $d_13 = ''; //  3 僚店番号
            $d_14 = '';
            $d_15 = '';
            $d_16 = '';
            $d_17 = '';
            $d_18 = '';
            $d_19 = '';
            $d_20 = '';
            $d_21 = '';
            $d_22 = '';
            $d_23 = '';
            $d_24 = '';
            $d_25 = '';
            $d_26 = '';
            $d_27 = '';
            $d_28 = '';
            $d_29 = '';
            $d_30 = '';

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

            $kakuOrder = array();   //

            $mdlS = new TableSmbcpa($this->dbAdapter);
            $mdlSA = new TableSmbcpaAccount($this->dbAdapter);
            $mdlSAG = new TableSmbcpaAccountGroup($this->dbAdapter);
            $mdlSAUH = new TableSmbcpaAccountUsageHistory($this->dbAdapter);
            $mdlSPN = new TableSmbcpaPaymentNotification($this->dbAdapter);

            $lgcSARA = new LogicSmbcpaAccountReceiptAuto($this->dbAdapter);

            // 入金ループ
            while (($data = fgets($handle, 1000)) !== false) {
                // 改行除去
                $data =rtrim(rtrim(rtrim($data, "\n"), "\r"), "\r\n");
                // チェック
                if (mb_strlen($data, 'sjis-win') != 200) {
                    throw new \Exception('レコード形式が不正です');
                }

                // レコード区分
                $recordCode = (int)substr($data, 0, 1);

                // 日付系項目は全て和暦のYYMMDD
                if ($recordCode == 1) {
                    //--------------------------------------------
                    // (ヘッダ)
                    $h_01 = substr($data,   0,  1); // データ区分
                    $h_02 = substr($data,   1,  2); // 種別コード
                    $h_03 = substr($data,   3,  1); // コード区分
                    $h_04 = substr($data,   4,  6); // 作成日
                    $h_05 = substr($data,  10,  6); // 勘定日（自）
                    $h_06 = substr($data,  16,  6); // 勘定日（至）
                    $h_07 = substr($data,  22,  4); // 銀行コード
                    $h_08 = substr($data,  26, 15); // 銀行名
                    $h_09 = substr($data,  41,  3); // 支店コード
                    $h_10 = substr($data,  44, 15); // 支店名
                    $h_11 = substr($data,  59,  3); // ダミー
                    $h_12 = substr($data,  62,  1); // 預金種別（1：普通、2：当座、5：通知、6：定期、7：積立）
                    $h_13 = substr($data,  63, 10); // 口座番号
                    $h_14 = substr($data,  73, 40); // 口座名
                    $h_15 = substr($data, 113,  1); // 貸越区分
                    $h_16 = substr($data, 114,  1); // 通帳・証券区分
                    $h_17 = substr($data, 115, 14); // 取引前残高
                    $h_18 = substr($data, 129, 71); // ダミー

                } else if ($recordCode == 2) {
                    //--------------------------------------------
                    // (ボディ)
                    $d_01 = substr($data,   0,  1); // データ区分
                    $d_02 = substr($data,   1,  8); // 照会番号（右寄せゼロ詰め）
                    $d_03 = substr($data,   9,  6); // 勘定日
                    $d_04 = substr($data,  15,  6); // 預入・払出日（起算日）
                    $d_05 = substr($data,  21,  1); // 入払区分（1：入金、2：出金）
                    $d_06 = substr($data,  22,  2); // 取引区分（10：現金、11：振込、12：他店券入金、13：交換、14：振替、15：継続、18：その他、19：訂正）
                    $d_07 = substr($data,  24, 12); // 取引金額
                    $d_08 = substr($data,  36, 12); // うち他店券金額
                    $d_09 = substr($data,  48,  6); // 交換呈示日
                    $d_10 = substr($data,  54,  6); // 不渡返還日
                    $d_11 = substr($data,  60,  1); // 手形・小切手区分
                    $d_12 = substr($data,  61,  7); // 手形・小切手番号
                    $d_13 = substr($data,  68,  3); // 僚店番号

                    // 普通口座・当座口座の場合（※他はこないけど分ける）
                    if ( ($h_12 == 1) || ($h_12 == 2) ) {
                        $d_14 = substr($data,  71, 10); // 振込依頼人コード
                        $d_15 = substr($data,  81, 48); // 振込依頼人名又は契約者番号
                        $d_16 = substr($data, 129, 15); // 仕向銀行名
                        $d_17 = substr($data, 144, 15); // 仕向店舗
                        $d_18 = substr($data, 159, 20); // 概要内容
                        $d_19 = substr($data, 179, 21); // ダミー

                    } else {
                        $d_14 = substr($data,  71,  6); // 当初預入日
                        $d_15 = substr($data,  77,  6); // 利率
                        $d_16 = substr($data,  83,  6); // 満期日
                        $d_17 = substr($data,  89,  7); // 期間
                        $d_18 = substr($data,  96, 11); // 期間利息
                        $d_19 = substr($data, 107,  6); // 中間払利率
                        $d_20 = substr($data, 113,  1); // 中間払区分
                        $d_21 = substr($data, 114,  4); // 期後期間
                        $d_22 = substr($data, 118,  6); // 期後利率
                        $d_23 = substr($data, 124,  9); // 期後利息
                        $d_24 = substr($data, 133, 11); // 合計利息
                        $d_25 = substr($data, 144,  1); // 税区分
                        $d_26 = substr($data, 145,  4); // 税率
                        $d_27 = substr($data, 149, 10); // 税額
                        $d_28 = substr($data, 159, 11); // 税引後利息
                        $d_29 = substr($data, 170, 20); // 摘要内容
                        $d_30 = substr($data, 190, 10); // ダミー

                    }

                    // 西暦年割出（簡易※年号とかしらね）
                    $targetYear = substr($d_03, 0, 2);
                    $checkYear = ( 2018 + (int)$targetYear );
                    $receiptDate =  $checkYear. '-'. substr($d_03, 2, 2). '-'. substr($d_03, 4, 2);

                    $targetYear = substr($d_04, 0, 2);
                    $checkYear = ( 2018 + (int)$targetYear );
                    $depositDate =  $checkYear. '-'. substr($d_04, 2, 2). '-'. substr($d_04, 4, 2);

                    if ($ImportedFlg) {
                        $ReceivedRawData = array(
                                'FileName'        => basename( $fileName ),
                                'OutputName'      => rtrim(mb_convert_encoding($d_15, 'UTF-8', 'sjis-win')),
                                'RmtBankName'     => rtrim(mb_convert_encoding($d_16, 'UTF-8', 'sjis-win')),
                                'RmtBrName'       => rtrim(mb_convert_encoding($d_17, 'UTF-8', 'sjis-win')),
                                'ReceivedRawData' => mb_convert_encoding($data, 'UTF-8', 'sjis-win'),
                        );

                        // 入金通知管理テーブルの登録
                        $savePrm = array(
                            'TransactionId'    => $d_14. $dateTime->format('YmdHis'),
                            'Status'           => TableSmbcpaPaymentNotification::STATUS_RESPONSED,
                            'ReqBranchCode'    => substr($d_14, 0, 3),
                            'ReqAccountNumber' => substr($d_14, 3, 7),
                            'ReceiptAmount'    => ($d_05 == '2') ? ((int)$d_07 * -1) : (int)$d_07,
                            'ReceivedDate'     => $dateTime->format('Y-m-d H:i:s'),
                            'ReceiptDate'      => $receiptDate,
                            'DeleteFlg'        => 0,
                            'ReceivedRawData'  => \Zend\Json\Json::encode( $ReceivedRawData ),
                        );
                        $spnSeq = $mdlSPN->saveNew($savePrm);
                    }

                    $order = array(
                            'spnSeq'          => $spnSeq     ,
                            'dataType'        => $d_01       ,
                            'targetNumber'    => $d_14       ,
                            'getType'         => $d_05       ,
                            'payType'         => $d_06       ,
                            'receiptDate'     => $receiptDate,
                            'depositDate'     => $depositDate,
                            'paymentAmount'   => ($d_05 == '2') ? ((int)$d_07 * -1) : (int)$d_07
                    );

                    //
                    $kakuOrder[] = $order;

                }
            }

            $rdata = array();   // 表示用(返品)データ
            $edata = array();   // 表示用(エラー)データ

            // 確報ループ
            foreach ($kakuOrder as $line) {

                // (入金日)
                $receiptDate = str_replace( '/', '-', $line['receiptDate'] );
                $depositDate = str_replace( '/', '-', $line['depositDate'] );

                // 入金金額
                $paymentAmount = $line['paymentAmount'];

                //
                $tmpDisplay = array();

                // 注文データとの紐づけは、項番14「振込依頼人コード」になります。
                // 項番14「振込依頼人コード」は、支店番号３桁＋バーチャル口座番号７桁になります。
                $targetNumber = (string)$line['targetNumber'];
                $branchBankCode = substr($targetNumber, 0, 3);
                $accountNumberVR = substr($targetNumber, 3, 7);

                // 入金通知管理番号
                $spnSeq = $line['spnSeq'];

                // 口座情報の取得
                $rowSmbcpaAccount = $mdlSA->findAccount($branchBankCode, $accountNumberVR);

                // 口座情報が見つからない場合
                if ( !$rowSmbcpaAccount ) {
                    // (表示用データ登録)
                    $tmpDisplay = array(
                            'syunoKbnCode'    => 3,
                            'OrderId'         => null,
                            'OrderSeq'        => null,
                            'paymentAmount'   => $paymentAmount,
                            'receiptDate'     => $receiptDate,
                            'Error'           => '該当口座なし、振込依頼人コード：' . $targetNumber,
                    );

                    // 出金の場合
                    if ( $line['getType'] == 2 ) {
                        $rdata[] = $tmpDisplay;
                    } else {
                        $edata[] = $tmpDisplay;
                    }

                    // 自動入金バッチ（さくらケーシーエス）から起動した場合
                    if ($ImportedFlg) {
                        $lgcSARA->doneUnreceipt( $spnSeq, $lgcSARA::RESULT_TYPE_1_ACCOUNT_NOT_FOUND, null, null );
                    }
                    continue;
                }

                // 上位グループの返却状態をチェック
                if ( $mdlSAG->find($rowSmbcpaAccount['AccountGroupId'])->current()['ReturnedFlg'] ) {
                    // (表示用データ登録)
                    $tmpDisplay = array(
                            'syunoKbnCode'    => 3,
                            'OrderId'         => null,
                            'OrderSeq'        => null,
                            'paymentAmount'   => $paymentAmount,
                            'receiptDate'     => $receiptDate,
                            'Error'           => '返却済み口座、振込依頼人コード：' . $targetNumber,
                    );

                    // 出金の場合
                    if ( $line['getType'] == 2 ) {
                        $rdata[] = $tmpDisplay;
                    } else {
                        $edata[] = $tmpDisplay;
                    }

                    // 自動入金バッチ（さくらケーシーエス）から起動した場合
                    if ($ImportedFlg) {
                        $rtn = $lgcSARA->doneUnreceipt( $spnSeq, $lgcSARA::RESULT_TYPE_9_RETURNED_ACCOUNT, null, null );
                    }
                    continue;
                }

                // SMBCバーチャル口座SEQ
                $accountSeq = $rowSmbcpaAccount['AccountSeq'];

                // 履歴件数取得
                if ( !$mdlSAUH->countHistoriesByAccountSeq( $accountSeq ) ) {
                    // 利用実績なし
                    // (表示用データ登録)
                    $tmpDisplay = array(
                            'syunoKbnCode'    => 3,
                            'OrderId'         => null,
                            'OrderSeq'        => null,
                            'paymentAmount'   => $paymentAmount,
                            'receiptDate'     => $receiptDate,
                            'Error'           => '口座利用実績なし、振込依頼人コード：' . $targetNumber,
                    );

                    // 出金の場合
                    if ( $line['getType'] == 2 ) {
                        $rdata[] = $tmpDisplay;
                    } else {
                        $edata[] = $tmpDisplay;
                    }

                    // 自動入金バッチ（さくらケーシーエス）から起動した場合
                    if ($ImportedFlg) {
                        $rtn = $lgcSARA->doneUnreceipt( $spnSeq, $lgcSARA::RESULT_TYPE_2_HISTORY_NOT_FOUND, null, $accountSeq );
                    }
                    continue;
                }

                // 口座状態をチェック
                if ( $rowSmbcpaAccount['Status'] != 1 ) {
                    // 口座が請求中ではない
                    // (表示用データ登録)
                    $tmpDisplay = array(
                            'syunoKbnCode'    => 3,
                            'OrderId'         => null,
                            'OrderSeq'        => null,
                            'paymentAmount'   => $paymentAmount,
                            'receiptDate'     => $receiptDate,
                            'Error'           => '請求中でない口座、振込依頼人コード：' . $targetNumber,
                    );

                    // 出金の場合
                    if ( $line['getType'] == 2 ) {
                        $rdata[] = $tmpDisplay;
                    } else {
                        $edata[] = $tmpDisplay;
                    }

                    // 自動入金バッチ（さくらケーシーエス）から起動した場合
                    if ($ImportedFlg) {
                        $rtn = $lgcSARA->doneReceiptPending( $spnSeq, $lgcSARA::RESULT_TYPE_3_EXEMPT_ACCOUNT, null, $accountSeq );
                    }
                    continue;
                }

                //注文情報の確認
                $sql = ' SELECT o.OrderSeq, o.OrderId';
                $sql .= ' FROM T_Order AS o';
                $sql .= ' INNER JOIN T_SmbcpaAccountUsageHistory AS sauh ON sauh.OrderSeq = o.OrderSeq';
                $sql .= ' WHERE sauh.AccountSeq = :AccountSeq';
                $sql .= ' AND IFNULL(sauh.DeleteFlg, 0) = 0';
                $sql .= ' AND sauh.MostRecent = 1';

                $prm = array( ':AccountSeq' => $accountSeq );
                $row_order = $this->dbAdapter->query( $sql )->execute( $prm )->current();
                if ( empty($row_order) ) {
                    // (表示用(エラー)データ登録)
                    $tmpDisplay = array(
                            'syunoKbnCode'    => $line['payType'],
                            'OrderId'         => '',
                            'OrderSeq'        => 0,
                            'paymentAmount'   => $line['paymentAmount'],
                            'receiptDate'     => $receiptDate,
                            'Error'           => '注文が特定できない。振込依頼人コード：' . $targetNumber,
                    );

                    // 出金の場合
                    if ( $line['getType'] == 2 ) {
                        $rdata[] = $tmpDisplay;
                    } else {
                        $edata[] = $tmpDisplay;
                    }

                    // 自動入金バッチ（さくらケーシーエス）から起動した場合
                    if ($ImportedFlg) {
                        $rtn = $lgcSARA->doneReceiptPending( $spnSeq, $lgcSARA::RESULT_TYPE_4_ORDER_NOT_FOUND, null, $accountSeq );
                    }
                    continue;
                }

                $orderSeq = $row_order['OrderSeq'];
                $orderId = $row_order['OrderId'];
                $prm = array( ':OrderSeq' => $orderSeq );

                // 処理しようとしている注文データが入金待ち、一部入金、入金済みクローズであるかのチェック
                $sql = 'SELECT COUNT(*) AS cnt FROM T_Order WHERE (DataStatus IN (51, 61) OR (DataStatus = 91 AND CloseReason = 1)) AND Cnl_Status = 0 AND OrderSeq = :OrderSeq';
                $checkCount = $this->dbAdapter->query( $sql )->execute( $prm )->current()['cnt'];

                if ( $checkCount == 0 ) {
                    // (注文有無)
                    $sql = 'SELECT * FROM T_Order WHERE OrderSeq = :OrderSeq';
                    $orderData = $this->dbAdapter->query( $sql )->execute( $prm )->current();

                    // (表示用データ登録)
                    $tmpDisplay = array(
                            'syunoKbnCode'    => $line['payType'],
                            'OrderId'         => $orderId,
                            'OrderSeq'        => $orderSeq,
                            'paymentAmount'   => $line['paymentAmount'],
                            'receiptDate'     => $line['receiptDate'],
                            'Error'           => '入金待ちではありません',
                    );

                    // 出金の場合
                    if ( $line['getType'] == 2 ) {
                        $rdata[] = $tmpDisplay;
                    } else {
                        $edata[] = $tmpDisplay;
                    }

                    // 自動入金バッチ（さくらケーシーエス）から起動した場合
                    if ($ImportedFlg) {
                        $rtn = $lgcSARA->doneReceiptPending( $spnSeq, $lgcSARA::RESULT_TYPE_5_EXEMPT_ORDER, $orderSeq, $accountSeq );
                    }
                    continue;
                }
                
                //CB_B2C_DEV-377 ☆クレカ決済後の入金を、入金エラー対象にする改修 Start
                //クレジットカードで支払った注文のチェック
                $sqlCredit = "SELECT COUNT(*) AS cnt FROM AT_Order WHERE ExtraPayType = 1 AND ExtraPayKey IS NOT NULL AND OrderSeq = :OrderSeq";
                $checkCountCredit = $this->dbAdapter->query($sqlCredit)->execute( $prm )->current()['cnt'];
                if ( $checkCountCredit >= 1 ) {
                    // (注文有無)
                    $sql = 'SELECT * FROM T_Order WHERE OrderSeq = :OrderSeq';
                    $orderData = $this->dbAdapter->query( $sql )->execute( $prm )->current();
                    
                    // (表示用データ登録)
                    $tmpDisplay = array(
                        'syunoKbnCode'    => $line['payType'],
                        'OrderId'         => $orderId,
                        'OrderSeq'        => $orderSeq,
                        'paymentAmount'   => $line['paymentAmount'],
                        'receiptDate'     => $line['receiptDate'],
                        'Error'           => 'クレジットカードで支払った注文',
                    );
                    
                    // 出金の場合
                    if ( $line['getType'] == 2 ) {
                        $rdata[] = $tmpDisplay;
                    } else {
                        $edata[] = $tmpDisplay;
                    }
                    
                    // 自動入金バッチ（さくらケーシーエス）から起動した場合
                    if ($ImportedFlg) {
                        $rtn = $lgcSARA->doneReceiptPending( $spnSeq, $lgcSARA::RESULT_TYPE_5_EXEMPT_ORDER, $orderSeq, $accountSeq );
                    }
                    continue;
                }

                // 分割支払済み金額のチェック
                $sql = " SELECT ReceiptAmountTotal AS cnt FROM T_ClaimControl WHERE OrderSeq = :OrderSeq ";
                $receiptAmountTotal = (int)$this->dbAdapter->query( $sql )->execute( $prm )->current()['cnt'];
                if ( $receiptAmountTotal > 0 ) {
                    // (表示用データ登録)
                    $tmpDisplay = array(
                            'syunoKbnCode'    => $line['payType'],
                            'OrderId'         => $orderId,
                            'OrderSeq'        => $orderSeq,
                            'paymentAmount'   => $line['paymentAmount'],
                            'receiptDate'     => $line['receiptDate'],
                            'Error'           => '分割支払済み',
                    );

                    // 出金の場合
                    if ( $line['getType'] == 2 ) {
                        $rdata[] = $tmpDisplay;
                    } else {
                        $edata[] = $tmpDisplay;
                    }

                    // 自動入金バッチ（さくらケーシーエス）から起動した場合
                    if ($ImportedFlg) {
                        $rtn = $lgcSARA->doneReceiptPending( $spnSeq, $lgcSARA::RESULT_TYPE_7_SPLIT_PAID, $orderSeq, $accountSeq );
                    }
                    continue;
                }

                // 入金額チェック
                $amount_range = $lgcSARA->getValidAmountRange( $orderSeq );
                if ( $paymentAmount < $amount_range['min'] ) {
                    // (表示用データ登録)
                    $tmpDisplay = array(
                            'syunoKbnCode'    => $line['payType'],
                            'OrderId'         => $orderId,
                            'OrderSeq'        => $orderSeq,
                            'paymentAmount'   => $line['paymentAmount'],
                            'receiptDate'     => $line['receiptDate'],
                            'Error'           => '金額差異あり：'. sprintf('不足入金 (%s 未満)', f_nf($amount_range['min'], '#,##0')),
                    );

                    // 出金の場合
                    if ( $line['getType'] == 2 ) {
                        $rdata[] = $tmpDisplay;
                    } else {
                        $edata[] = $tmpDisplay;
                    }

                    // 自動入金バッチ（さくらケーシーエス）から起動した場合
                    if ($ImportedFlg) {
                        $rtn = $lgcSARA->doneReceiptPending( $spnSeq, $lgcSARA::RESULT_TYPE_6_EXEMPT_AMOUNT, $orderSeq, $accountSeq, sprintf('不足入金 (%s 未満)', f_nf($amount_range['min'], '#,##0')) );
                    }
                    continue;
                }

                if ( $paymentAmount > $amount_range['max'] ) {
                    // (表示用データ登録)
                    $tmpDisplay = array(
                            'syunoKbnCode'    => $line['payType'],
                            'OrderId'         => $orderId,
                            'OrderSeq'        => $orderSeq,
                            'paymentAmount'   => $line['paymentAmount'],
                            'receiptDate'     => $line['receiptDate'],
                            'Error'           => '金額差異あり：'. sprintf('過剰入金 (%s 超)', f_nf($amount_range['max'], '#,##0')),
                    );

                    // 出金の場合
                    if ( $line['getType'] == 2 ) {
                        $rdata[] = $tmpDisplay;
                    } else {
                        $edata[] = $tmpDisplay;
                    }

                    // 自動入金バッチ（さくらケーシーエス）から起動した場合
                    if ($ImportedFlg) {
                        $rtn = $lgcSARA->doneReceiptPending( $spnSeq, $lgcSARA::RESULT_TYPE_6_EXEMPT_AMOUNT, $orderSeq, $accountSeq, sprintf('過剰入金 (%s 超)', f_nf($amount_range['max'], '#,##0')) );
                    }
                    continue;
                }

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
                        ':pi_receipt_class'    => 3,
                        ':pi_branch_bank_id'   => null,
                        ':pi_receipt_agent_id' => null,
                        ':pi_deposit_date'     => $depositDate,
                        ':pi_user_id'          => $userId,
                         ':pi_receipt_note'    => null,
                );

                try {
                    $ri = $stm->execute($prm2);

                    // SQL実行例外なしもエラー戻り値の時は例外をｽﾛｰ
                    $retval = $this->dbAdapter->query( $getretvalsql )->execute( null )->current();
                    if ($retval['po_ret_sts'] != 0) {
                        throw new \Exception( $retval['po_ret_msg'] );
                    }
                }
                catch(\Exception $e) {
                    throw $e;
                }

                // 自動入金バッチ（さくらケーシーエス）から起動した場合
                if ($ImportedFlg) {
                    // 入金通知管理を「入金処理済み」に更新
                    $updateData = array(
                        'OrderSeq' => $orderSeq,
                        'AccountSeq' => $accountSeq,
                        'ReceiptProcessDate' => $dateTime->format('Y-m-d H:i:s'),
                        'Status' => 9,
                        'RejectReason' => null,
                        'LastProcessDate' => $dateTime->format('Y-m-d H:i:s')
                    );
                    $lgcSARA->getPaymentNotificationTable()->saveUpdate( $updateData, $spnSeq );
                }

                // SMBCヴァーチャル口座をクローズ
                if ( ( $rowSmbcpaAccount['Status'] == 1 )
                  && ( ( ( $claimedBalance - $line['paymentAmount'] ) ) == 0 )
                   )
                {
                    $lgcSARA->getAccountLogic()->closeAccount( $orderSeq );
                }

                // ②未印刷の請求書印刷予約データを削除
                $mdlch = new models\Table\TableClaimHistory($this->dbAdapter);
                $mdlch->deleteReserved($orderSeq, $userId);

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
                    $isAlreadyClearCondition = $mdlpas->IsAlreadyClearCondition( $orderSeq );

                    $mdlpas->clearConditionForCharge($orderSeq, 1, $userId);

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
                if ($line['getType'] == 2) {
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

            $sql = 'UPDATE T_ImportedSmbcPerfect SET ReceiptResult = :ReceiptResult WHERE Seq = :Seq';
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

$this->logger->info('importSmbcPerfect.php err：'. $errormessage);
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
