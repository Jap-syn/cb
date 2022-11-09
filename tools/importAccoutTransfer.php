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
use models\Table\TableCode;
use models\Logic\LogicCreditTransfer;
use models\Logic\LogicTemplate;

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

$this->logger->info('importAccoutTransfer.php start');

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

            // T_ImportedAccountTransferFile登録(または更新)
            $seq = -1;
            $mdliatf = new \models\Table\TableImportedAccountTransferFile($this->dbAdapter);
            $row = $this->dbAdapter->query(" SELECT Seq FROM T_ImportedAccountTransferFile WHERE FileName = :FileName AND CreditTransferFlg=1 ")->execute(array(':FileName' => $_SERVER['argv'][1]))->current();
            if ($row) {
                // (更新 ※インポートエラーからの再処理)
                $seq = $row['Seq'];
                $upddata = array (
                        'Status'        => 0,                   // 0:処理中
                        'RegistDate'    => date('Y-m-d H:i:s'), // ※再処理時は[登録]扱い
                        'RegistId'      => $userId,             // ※再処理時は[登録]扱い
                );
                $mdliatf->saveUpdate($upddata, $seq);
            }
            else {
                // (新規)
                $savedata = array (
                    'CreditTransferFlg' => 1,
                    'FileName'      => $_SERVER['argv'][1], // バッチ引数の1つ目
                    'Status'        => 0,                   // 0:処理中
                    'RegistDate'    => date('Y-m-d H:i:s'),
                    'RegistId'      => $userId,
                );
                $seq = $mdliatf->saveNew($savedata);
            }

            // 読込みファイル特定
            $mdlsp = new \models\Table\TableSystemProperty($this->dbAdapter);
            $transDir = $mdlsp->getValue(\models\Table\TableSystemProperty::DEFAULT_MODULE, 'systeminfo', 'TempFileDir');
            $pathFileName = $transDir . '/' . $_SERVER['argv'][1];

            // 取込済み口座振替入金ファイル取込
            $errormessage = "";
            $isSuccess = $this->importAccoutTransfer($pathFileName, $seq, $userId, $errormessage);

            // ファイル削除
            unlink($pathFileName);

            // T_ImportedAccountTransferFile更新
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
            $mdliatf->saveUpdate($updprm, $seq);

            $seqcsv = $seq;
            $row = $this->dbAdapter->query(" SELECT ReceiptResult FROM T_ImportedAccountTransferFile WHERE Seq = :Seq AND CreditTransferFlg=1 ")->execute(array(':Seq' => $seq))->current();
            $receiptresult = \Zend\Json\Json::decode($row['ReceiptResult'], \Zend\Json\Json::TYPE_ARRAY);
            foreach($receiptresult['infodata'] as $data){
            $subdata = $this->dbAdapter->query(" SELECT e.EnterpriseId, e.EnterpriseNameKj FROM T_Enterprise e INNER JOIN T_EnterpriseCustomer c ON e.EnterpriseId = c.EnterpriseId WHERE c.EntCustSeq = :EntCustSeq ")->execute(array(':EntCustSeq' => $data['EntCustSeq']))->current();
            $EnterpriseId = $subdata['EnterpriseId'];
            $EnterpriseNameKj = $subdata['EnterpriseNameKj'];
            $EntCustSeq = $data['EntCustSeq'];
            $CustomerName = $data['CustomerName'];
            $ClaimAmount = $data['ClaimAmount'];
            switch($data['ResCode']) {
                case '1':
                    $ResCode = '1：資金不足';
                    break;
                case '2':
                    $ResCode = '2：取引なし';
                    break;
                case '3':
                    $ResCode = '3：預金者都合';
                    break;
                case '4':
                    $ResCode = '4：依頼書なし';
                    break;
                case '8':
                    $ResCode = '8：委託者都合';
                    break;
                case 'E':
                    $ResCode = 'E：データエラー';
                    break;
                case 'N':
                    $ResCode = 'N：振替結果未着';
                    break;
                case '9':
                    $ResCode = '9：その他';
                    break;
            }
            if($data['OrderSeq'] === -1){
                $OrderId = '注文未特定';
            }else{
                $OrderId = $data['OrderId'];
            }
            
            //備考に追記
            if($data['OrderId'] !== ""){
                $mdlc = new TableCode($this->dbAdapter);
                $addition = $mdlc->find2(219, $data['ResCode'])->current()['Class1'];
                $row = $this->dbAdapter->query(" SELECT Incre_Note FROM T_Order WHERE OrderId=:OrderId ")->execute(array(':OrderId' => $data['OrderId']))->current();
                $row2 = $addition.$row['Incre_Note'];
                $this->dbAdapter->query("UPDATE T_Order SET Incre_Note = :row2 WHERE OrderId=:OrderId ")->execute(array(':row2' => $row2,':OrderId' => $data['OrderId']));
            }

            $completedata[] = array (
                'ResCode'           => $ResCode,           // 振替結果
                'OrderId'           => $OrderId,           // 注文ID
                'EntCustSeq'        => $EntCustSeq,        // 顧客番号
                'EnterpriseId'      => $EnterpriseId,      // 事業者ID
                'EnterpriseNameKj'  => $EnterpriseNameKj,  // 事業者名
                'CustomerName'      => $CustomerName,      // 加盟店顧客名
                'ClaimAmount'       => $ClaimAmount,       // 請求金額
            );

            }
            //CSV作成
            $templateId = 'FUR00000_1';
            $templateClass = 0;
            $seq = 0;
            $templatePattern = 0;
            $tmpFileName = $transDir . '/' . $_SERVER['argv'][1];
            $logicTemplate = new LogicTemplate( $this->dbAdapter );
            $result = $logicTemplate->convertArraytoFile( $completedata, $tmpFileName, $templateId, $templateClass, $seq, $templatePattern );

            //DBに保存
            $obj_csv = null;
            $filename = isset($tmpFileName) ? $tmpFileName : null;
            if (!is_null($filename)) {

                $fp = fopen($filename, "rb");
                $obj_csv = fread($fp, filesize($filename));
                if (!$obj_csv) {
                    throw new \Exception('振替結果ファイルの作成に失敗しました。');
                }
                fclose($fp);
                unlink($filename);
            }

            if (! empty($obj_csv)) {
                // T_ImportedAccountTransferFileのcsvカラムに保存
                $data = array(
                        'csv' => $obj_csv,
                );
                $mdliatf->saveUpdate($data, $seqcsv);
            }
        

$this->logger->info('importAccoutTransfer.php end');
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
     * 取込済み口座振替入金ファイル取込
     *
     * @param string $fileName ファイル名
     * @param int $seq T_ImportedAccountTransferFileのSEQ
     * @param int $userId ユーザID
     * @return boolean true:成功／false:失敗
     */
    protected function importAccoutTransfer($fileName, $seq, $userId, &$errormessage) {

        $handle = null;
        try {
            // トランザクション開始
            $this->dbAdapter->getDriver()->getConnection()->beginTransaction();

            $infodata = array();
            $infodata2 = array();

            $stm = $this->dbAdapter->query($this->getBaseP_ReceiptControl());
            $mdlc = new TableCode($this->dbAdapter);
            $lgc = new LogicCreditTransfer($this->dbAdapter);

            // SQL実行結果取得用のSQL
            $getretvalsql = " SELECT @po_ret_sts AS po_ret_sts, @po_ret_errcd AS po_ret_errcd, @po_ret_sqlcd AS po_ret_sqlcd, @po_ret_msg AS po_ret_msg ";

            $today = date('Y-m-d');

            // 抽出範囲
            $fr_date = date('Y-m-21', strtotime($today . ' -2 month'));
            $to_date = date('Y-m-21', strtotime($today . ' -1 month'));

            $handle = fopen($fileName, "r");

            $h_typeCode         = ''; // (ヘッダ)種別コード
            $h_codeDivision     = ''; // (ヘッダ)コード区分
            $h_multiCode        = ''; // (ヘッダ)マルチコード
            $h_consignorCode    = ''; // (ヘッダ)委託者コード
            $h_consignorName    = ''; // (ヘッダ)委託者名
            $h_transderDate     = ''; // (ヘッダ)振替日
            $h_bankCode         = ''; // (ヘッダ)取引銀行番号
            $h_bankName         = ''; // (ヘッダ)取引銀行名
            $h_branchCode       = ''; // (ヘッダ)取引支店番号
            $h_branchName       = ''; // (ヘッダ)取引支店名
            $h_depositType      = ''; // (ヘッダ)預金種別
            $h_accountNumber    = ''; // (ヘッダ)預金口座番号
            $h_reserve          = ''; // (ヘッダ)予備

            $d_bankCode         = ''; // (データ)引落銀行番号
            $d_bankName         = ''; // (データ)引落銀行名
            $d_branchCode       = ''; // (データ)引落支店番号
            $d_branchName       = ''; // (データ)引落支店名
            $d_dummy            = ''; // (データ)ダミー
            $d_depositType      = ''; // (データ)預金種別
            $d_accountNumber    = ''; // (データ)口座番号
            $d_depositorName    = ''; // (データ)預金者名
            $d_claimAmount      = ''; // (データ)請求金額
            $d_newCode          = ''; // (データ)新規コード
            $d_consignorCode    = ''; // (データ)委託者コード
            $d_entCustSeq       = ''; // (データ)顧客番号
            $d_resultCode       = ''; // (データ)振替結果コード
            $d_reserve          = ''; // (データ)予備

            $t_claimCnt         = ''; // (トレーラ)請求合計件数
            $t_claimSum         = ''; // (トレーラ)請求合計金額
            $t_transferredCnt   = ''; // (トレーラ)振替済合計件数
            $t_transferredSum   = ''; // (トレーラ)振替済合計金額
            $t_impossibleCnt    = ''; // (トレーラ)振替不能合計件数
            $t_impossibleSum    = ''; // (トレーラ)振替不能合計金額
            $t_reserve          = ''; // (トレーラ)予備

            $e_reserve          = ''; // (エンド)予備
            $tarm = array();

            // 入金ループ
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {

                // レコード区分
                $recordCode = (int)substr($data[0], 0, 1);

                if ($recordCode == 1) {
                    //--------------------------------------------
                    // (ヘッダ)
                    $h_typeCode         = substr($data[0],   1,  2);
                    $h_codeDivision     = substr($data[0],   3,  1);
                    $h_multiCode        = substr($data[0],   4,  2);
                    $h_consignorCode    = substr($data[0],   6,  8);
                    $h_consignorName    = substr($data[0],  14, 40);
                    $h_transderDate     = substr($data[0],  54,  4);
                    $h_bankCode         = substr($data[0],  58,  4);
                    $h_bankName         = substr($data[0],  62, 15);
                    $h_branchCode       = substr($data[0],  77,  3);
                    $h_branchName       = substr($data[0],  80, 15);
                    $h_depositType      = substr($data[0],  95,  1);
                    $h_accountNumber    = substr($data[0],  96,  7);
                    $h_reserve          = substr($data[0], 103, 17);

                    $year = date('Y');
                    if (substr($h_transderDate, 0, 2) > date('m')) {
                        $year--;
                    }
                    $tarm = $lgc->getAllTargetTerm($year.'-'.substr($h_transderDate, 0, 2).'-'.substr($h_transderDate, 2, 2));
                }
                else if ($recordCode == 2) {
                    //--------------------------------------------
                    // (データ)
                    $d_bankCode         = substr($data[0],   1,  4);
                    $d_bankName         = substr($data[0],   5, 15);
                    $d_branchCode       = substr($data[0],  20,  3);
                    $d_branchName       = substr($data[0],  23, 15);
                    $d_dummy            = substr($data[0],  38,  4);
                    $d_depositType      = substr($data[0],  42,  1);
                    $d_accountNumber    = substr($data[0],  43,  7);
                    $d_depositorName    = substr($data[0],  50, 30);
                    $d_claimAmount      = substr($data[0],  80, 10);
                    $d_newCode          = substr($data[0],  90,  1);
                    $d_consignorCode    = substr($data[0],  91,  8);
                    $d_entCustSeq       = substr($data[0],  99, 12);
                    $d_resultCode       = substr($data[0], 111,  1);
                    $d_reserve          = substr($data[0], 112,  8);

                    // 6-1. 入金対象注文の特定
                    $sql  = " SELECT ch.Seq ";
                    $sql .= " ,      ch.OrderSeq ";
                    $sql .= " ,      ec.EntCustSeq ";
                    $sql .= " ,      ch.ClaimAmount ";
                    $sql .= " ,      o.OrderId ";
                    $sql .= " ,      ec.RequestStatus ";
                    $sql .= " FROM   T_ClaimHistory ch ";
                    $sql .= "        INNER JOIN T_Order o ON (o.OrderSeq = ch.OrderSeq) ";
                    $sql .= "        INNER JOIN AT_Order ao ON (ao.OrderSeq = ch.OrderSeq) ";
                    $sql .= "        INNER JOIN T_OrderSummary os ON (os.OrderSeq = o.OrderSeq) ";
                    $sql .= "        INNER JOIN T_Customer c ON (c.OrderSeq = o.OrderSeq) ";
                    $sql .= "        INNER JOIN T_EnterpriseCustomer ec ON (ec.EntCustSeq = c.EntCustSeq) ";
                    $sql .= "        INNER JOIN T_Enterprise e ON (e.EnterpriseId = ec.EnterpriseId) ";
                    $sql .= " WHERE  1 = 1 ";
                    $sql .= " AND    e.CreditTransferFlg IN (1) ";
//                    $sql .= " AND    ec.RequestStatus = 2 ";
//                    $sql .= " AND    ch.CreditTransferRequestStatus = 2 ";
                    $sql .= " AND    o.DataStatus = 51 ";
                    $sql .= " AND    o.Cnl_Status = 0 ";
                    $sql .= " AND    ao.CreditTransferRequestFlg > 0 " ;
                    $sql .= " AND    ch.ClaimPattern = 1 ";
                    $sql .= " AND    ch.ClaimFileOutputClass = 1 ";
                    $sql .= " AND    (os.Deli_JournalIncDate >= :fr_date AND os.Deli_JournalIncDate <= :to_date) ";
                    $sql .= " AND    1 = (SELECT COUNT(1) FROM T_ClaimHistory WHERE OrderSeq = ch.OrderSeq) ";
                    $sql .= " AND    ec.EntCustSeq = :EntCustSeq ";
                    $sql .= " AND    ch.ClaimAmount = :ClaimAmount ";
                    $sql .= " AND    ao.ExtraPayType IS NULL ";
                    $sql .= " AND    ao.ExtraPayKey IS NULL ";
                    $sql .= " ORDER BY OrderSeq ";
                    $sql .= " LIMIT 1 ";

                    $row = $this->dbAdapter->query($sql)->execute(array(':fr_date' => $tarm[1]['SpanFrom'].' 00:00:00', ':to_date' => $tarm[1]['SpanTo'].' 23:59:59', ':EntCustSeq' => (int)$d_entCustSeq, ':ClaimAmount' => (int)$d_claimAmount))->current();

                    if ($d_resultCode != '0') {
                        // 振替結果コード＝[0:正常に振替済]以外時
                        // 1:資金不足
                        // 2:取引なし
                        // 3:預金者都合
                        // 4:依頼書なし
                        // 8:委託者都合
                        // E:データエラー
                        // N:振替結果未着
                        // 9:その他
                        $infodata[] = array(
                            'ResCode'       => $d_resultCode,
                            'EntCustSeq'    => $d_entCustSeq,
                            'CustomerName'  => $this->dbAdapter->query(" SELECT * FROM T_EnterpriseCustomer WHERE EntCustSeq = :EntCustSeq "
                                    )->execute(array(':EntCustSeq' => (int)$d_entCustSeq))->current()['NameKj'],
                            'ClaimAmount'   => (int)$d_claimAmount,
                            'OrderSeq'      => ($row) ? $row['OrderSeq'] : -1,
                            'OrderId'       => ($row) ? $row['OrderId'] : '',
                        );

                        continue;
                    }
                    else if (!$row) {
                        // 注文が特定されなかった時 
                        // キャンセルステータスを再確認
                        // 6-1. 入金対象注文の特定
                        $sql  = " SELECT ch.Seq ";
                        $sql .= " ,      ch.OrderSeq ";
                        $sql .= " ,      ec.EntCustSeq ";
                        $sql .= " ,      ch.ClaimAmount ";
                        $sql .= " ,      o.OrderId ";
                        $sql .= " ,      ec.RequestStatus ";
                        $sql .= " ,      o.Cnl_Status ";
                        $sql .= " FROM   T_ClaimHistory ch ";
                        $sql .= "        INNER JOIN T_Order o ON (o.OrderSeq = ch.OrderSeq) ";
                        $sql .= "        INNER JOIN AT_Order ao ON (ao.OrderSeq = ch.OrderSeq) ";
                        $sql .= "        INNER JOIN T_OrderSummary os ON (os.OrderSeq = o.OrderSeq) ";
                        $sql .= "        INNER JOIN T_Customer c ON (c.OrderSeq = o.OrderSeq) ";
                        $sql .= "        INNER JOIN T_EnterpriseCustomer ec ON (ec.EntCustSeq = c.EntCustSeq) ";
                        $sql .= "        INNER JOIN T_Enterprise e ON (e.EnterpriseId = ec.EnterpriseId) ";
                        $sql .= " WHERE  1 = 1 ";
                        $sql .= " AND    e.CreditTransferFlg IN (1) ";
                        //                    $sql .= " AND    ec.RequestStatus = 2 ";
                        //                    $sql .= " AND    ch.CreditTransferRequestStatus = 2 ";
                        $sql .= " AND (  (o.DataStatus = 51 AND o.Cnl_Status = 1 ) ";
                        $sql .= "     OR (o.DataStatus = 91 AND o.Cnl_Status = 2 ) ) ";
                        $sql .= " AND    ao.CreditTransferRequestFlg > 0 " ;
                        $sql .= " AND    ch.ClaimPattern = 1 ";
                        $sql .= " AND    ch.ClaimFileOutputClass = 1 ";
                        $sql .= " AND    (os.Deli_JournalIncDate >= :fr_date AND os.Deli_JournalIncDate <= :to_date) ";
                        $sql .= " AND    1 = (SELECT COUNT(1) FROM T_ClaimHistory WHERE OrderSeq = ch.OrderSeq) ";
                        $sql .= " AND    ec.EntCustSeq = :EntCustSeq ";
                        $sql .= " AND    ch.ClaimAmount = :ClaimAmount ";
                        $sql .= " AND    ao.ExtraPayType IS NULL ";
                        $sql .= " AND    ao.ExtraPayKey IS NULL ";
                        $sql .= " ORDER BY OrderSeq ";
                        $sql .= " LIMIT 1 ";

                        $checkRow = $this->dbAdapter->query($sql)->execute(array(':fr_date' => $tarm[1]['SpanFrom'].' 00:00:00', ':to_date' => $tarm[1]['SpanTo'].' 23:59:59', ':EntCustSeq' => (int)$d_entCustSeq, ':ClaimAmount' => (int)$d_claimAmount))->current();
                        //
                        if (!empty($checkRow)) {
                            //
                            $infodata2[] = array(
                                    'EntCustSeq'    => $d_entCustSeq,
                                    'CustomerName'  => $this->dbAdapter->query(" SELECT * FROM T_EnterpriseCustomer WHERE EntCustSeq = :EntCustSeq "
                                            )->execute(array(':EntCustSeq' => (int)$d_entCustSeq))->current()['NameKj'],
                                    'ClaimAmountF' => (int)$d_claimAmount,  // CATS上の請求(引落)金額
                                    'ClaimAmountD' => 0,                    // 特定注文の請求金額合計
                                    'Reason' => 'キャンセル後入金のため',
                            );
                        }else{
                            //
                            $infodata2[] = array(
                                'EntCustSeq'    => $d_entCustSeq,
                                'CustomerName'  => $this->dbAdapter->query(" SELECT * FROM T_EnterpriseCustomer WHERE EntCustSeq = :EntCustSeq "
                                        )->execute(array(':EntCustSeq' => (int)$d_entCustSeq))->current()['NameKj'],
                                'ClaimAmountF' => (int)$d_claimAmount,  // CATS上の請求(引落)金額
                                'ClaimAmountD' => 0,                    // 特定注文の請求金額合計
                            );
                        }

                        continue;
                    }

                    $oseq = $row['OrderSeq'];

                    // 本段階で入金可能な注文SEQ通知が行われた

                    // 申込ステータスが2：完了でない場合、注文未特定リストに表示
                    if ($row['RequestStatus'] != 2) {
                        $status_name = $mdlc->find(196, $row['RequestStatus'])->current()['KeyContent'];
                        if ($status_name == '') {
                            $status_name = '---';
                        }
                        $infodata2[] = array(
                            'EntCustSeq'    => $d_entCustSeq,
                            'CustomerName'  => $this->dbAdapter->query(" SELECT * FROM T_EnterpriseCustomer WHERE EntCustSeq = :EntCustSeq "
                            )->execute(array(':EntCustSeq' => (int)$d_entCustSeq))->current()['NameKj'],
                            'ClaimAmountF' => (int)$d_claimAmount,  // CATS上の請求(引落)金額
                            'ClaimAmountD' => (int)$d_claimAmount,  // 特定注文の請求金額合計
                            'Reason' => 'ステータスが'.$status_name.'のため',
                        );
                    }

                    // 7-1. 入金前データステータスの取得
                    $orderRow = $this->dbAdapter->query(" SELECT * FROM T_Order WHERE OrderSeq = :OrderSeq ")->execute(array(':OrderSeq' => $oseq))->current();

                    // 請求残高の取得
                    $sql = " SELECT cc.ClaimedBalance FROM T_Order o INNER JOIN T_ClaimControl cc ON (cc.OrderSeq = o.OrderSeq) WHERE o.OrderSeq = :OrderSeq ";
                    $claimedBalance = (int)$this->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current()['ClaimedBalance'];

                    // (入金日)
                    $year = date('Y');
                    if (substr($h_transderDate, 0, 2) > date('m')) {
                        $year--;
                    }
                    $receiptDate = ($year . '-' . substr($h_transderDate, 0, 2) . '-' . substr($h_transderDate, 2, 2));

                    // ①入金プロシージャー(P_ReceiptControl)呼び出し
                    $prm = array(
                        ':pi_receipt_amount'   => (int)$orderRow['UseAmount'],
                        ':pi_order_seq'        => $oseq,
                        ':pi_receipt_date'     => $receiptDate,
                        ':pi_receipt_class'    => 13, // 3:銀行
                        ':pi_branch_bank_id'   => (int)$h_branchCode,
                        ':pi_receipt_agent_id' => 3,
                        ':pi_deposit_date'     => $lgc->getTransderCommitDate($receiptDate, 1),
                        ':pi_user_id'          => $userId,
                        ':pi_receipt_note'    => null,                        
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
                    $this->dbAdapter->query("UPDATE T_ReceiptControl SET MailFlg=0 WHERE ReceiptSeq=:ReceiptSeq ")->execute(array(':ReceiptSeq' => $receiptSeq));
//                    $sendMailError = '';
//                    if ($orderRow['DataStatus'] != 91 && $row['DataStatus'] == 91) {// [91：クローズ]からの入金はメール対象から除外
//                        try {
//                            $mail = new \Coral\Coral\Mail\CoralMail($this->dbAdapter, $this->mail['smtp']);
//                            $mail->SendRcptConfirmMail($receiptSeq, $userId);
//
//                        } catch(\Exception $e) {
//                            // エラーメッセージを入れておく。
//                            $sendMailError = 'メール送信NG';
//                        }
//                    }

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
                            'AccountNumber' => (int)$h_accountNumber,
                            'BankFlg' => 2, // 2：直接振込
                            'Before_ClearConditionForCharge' => $clearConditionForCharge,
                            'Before_ClearConditionDate' => $clearConditionDate,
                            'Before_Chg_Status' => $chgStatus,
                            'Before_Deli_ConfirmArrivalFlg' => $deliConfirmArrivalFlg
                    );

                    $mdl_atrc->saveNew($atdata);
                }
                else if ($recordCode == 8) {
                    //--------------------------------------------
                    // (トレーラ)
                    $t_claimCnt         = substr($data[0],   1,  6);
                    $t_claimSum         = substr($data[0],   7, 12);
                    $t_transferredCnt   = substr($data[0],  19,  6);
                    $t_transferredSum   = substr($data[0],  25, 12);
                    $t_impossibleCnt    = substr($data[0],  37,  6);
                    $t_impossibleSum    = substr($data[0],  43, 12);
                    $t_reserve          = substr($data[0],  55, 65);
                }
                else if ($recordCode == 9) {
                    //--------------------------------------------
                    // (エンド)
                    $h_reserve          = substr($data[0],  1, 119);
                }
            }

            fclose($handle);

            // ReceiptResultに[summary][infodata(振替結果ｺｰﾄﾞに0以外がある場合)]情報を設定
            $summary = array(
                    'claimCnt'       => (int)$t_claimCnt,
                    'claimSum'       => (int)$t_claimSum,
                    'transferredCnt' => (int)$t_transferredCnt,
                    'transferredSum' => (int)$t_transferredSum,
                    'impossibleCnt'  => (int)$t_impossibleCnt,
                    'impossibleSum'  => (int)$t_impossibleSum,
            );
            $receiptresult = array();
            $receiptresult['summary'] = $summary;
            if (count($infodata) > 0) {
                $receiptresult['infodata'] = $infodata;
            }
            if (count($infodata2) > 0) {
                $receiptresult['infodata2'] = $infodata2;
            }
            $this->dbAdapter->query(" UPDATE T_ImportedAccountTransferFile SET ReceiptResult = :ReceiptResult WHERE Seq = :Seq "
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
