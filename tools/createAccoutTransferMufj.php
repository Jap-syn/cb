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
use models\Logic\LogicCreditTransfer;

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

$this->logger->info('createAccoutTransferMufj.php start');

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

            // ファイル名生成
            $fileName = ('MUFJ_FURIKAE_' . date('YmdHis'));

            // 口座振替用請求ファイル(T_ClaimAccountTransferFile)登録
            $mdlcatf = new \models\Table\TableClaimAccountTransferFile($this->dbAdapter);
            $savedata = array (
                    'CreditTransferFlg' => 2,
                    'FileName'      => $fileName,
                    'Status'        => 0,                   // 0:作成中
                    'RegistDate'    => date('Y-m-d H:i:s'),
                    'RegistId'      => $userId,
            );
            $seq = $mdlcatf->saveNew($savedata); // (新規登録PK(seq)保管)

            // 口座振替用請求ファイル作成
            $errorCode = -1;
            $isSuccess = $this->createAccoutTransfer($fileName, $seq, $userId, $errorCode);

            // 口座振替用請求ファイル(T_ClaimAccountTransferFile)更新
            $updprm = array ();
            if ($isSuccess                         ) { $updprm['Status'] = 1; } // 1:作成済(正常)
            else if (!$isSuccess && $errorCode == 1) { $updprm['Status'] = 3; } // 3:請求対象なし
            else                                     { $updprm['Status'] = 2; } // 2:作成エラー
            $updprm['UpdateDate']   = date('Y-m-d H:i:s');
            $updprm['UpdateId']     = $userId;
            $mdlcatf->saveUpdate($updprm, $seq);

$this->logger->info('createAccoutTransferMufj.php end');
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
     * 口座振替用請求ファイル作成
     *
     * @param string $fileName ファイル名
     * @param int $seq T_ClaimAccountTransferFileのSEQ
     * @param int $userId ユーザID
     * @param int $errorCode エラーコード (1:対象データなし)
     * @return boolean true:成功／false:失敗
     */
    protected function createAccoutTransfer($fileName, $seq, $userId, &$errorCode) {

        $mdlec = new \models\Table\TableEnterpriseCustomer($this->dbAdapter);
        $mdlsp = new \models\Table\TableSystemProperty($this->dbAdapter);
        $mdlch = new \models\Table\TableClaimHistory($this->dbAdapter);
        $lgc = new LogicCreditTransfer($this->dbAdapter);

        $handle = null;
        try {
            // トランザクション開始
            $this->dbAdapter->getDriver()->getConnection()->beginTransaction();

            $today = date('Y-m-d');

            // 抽出範囲
            $tarm = $lgc->getAllInfo();
//            $fr_date = date('Y-m-21', strtotime($today . ' -1 month'));
//
//            // 出力対象は処理月の前月21日～当月20日(1日～19日に実行された場合、前月21日～処理日時。基準は、注文サマリー.配送－伝票番号入力日)
//            if (date('Y-m-d') < date('Y-m-20'))
//            {
//                $to_date = date('Y-m-d', strtotime($today. ' +1 day'));
//            } else {
//                $to_date = date('Y-m-21', strtotime($today));
//            }

            // 1.対象データ取得
            $sql  = " SELECT ch.Seq ";
            $sql .= " ,      ch.OrderSeq ";
            $sql .= " ,      ec.EntCustSeq ";
            $sql .= " ,      ch.ClaimAmount ";
            $sql .= " ,      LPAD(ec.FfCode, 4, '0') AS FfBankCode ";
            $sql .= " ,      LPAD(ec.FfBranchCode, 3, '0') AS FfBranchCode ";
            $sql .= " ,      ec.FfAccountClass AS FfAccountClass ";
            $sql .= " ,      LPAD(ec.FfAccountNumber, 7, '0') AS FfAccountNumber ";
            $sql .= " ,      RPAD(ec.FfAccountName, 30, ' ') AS FfAccountName ";
            $sql .= " FROM   T_ClaimHistory ch ";
            $sql .= "        INNER JOIN T_Order o ON (o.OrderSeq = ch.OrderSeq) ";
            $sql .= "        INNER JOIN T_OrderSummary os ON (os.OrderSeq = o.OrderSeq) ";
            $sql .= "        INNER JOIN T_Customer c ON (c.OrderSeq = o.OrderSeq) ";
            $sql .= "        INNER JOIN T_EnterpriseCustomer ec ON (ec.EntCustSeq = c.EntCustSeq) ";
            $sql .= "        INNER JOIN T_Enterprise e ON (e.EnterpriseId = ec.EnterpriseId) ";
            $sql .= " WHERE  1 = 1 ";
            $sql .= " AND    e.CreditTransferFlg IN (2) ";
            $sql .= " AND    ec.RequestStatus = 2 ";
            $sql .= " AND    ch.CreditTransferRequestStatus = 2 ";
            $sql .= " AND    o.DataStatus = 51 ";
            $sql .= " AND    o.Cnl_Status = 0 ";
            $sql .= " AND    ch.ClaimPattern = 1 ";
            $sql .= " AND    ch.ClaimFileOutputClass = 0 ";
            $sql .= " AND    (os.Deli_JournalIncDate >= :fr_date AND os.Deli_JournalIncDate <= :to_date) ";
            $sql .= " AND    1 = (SELECT COUNT(1) FROM T_ClaimHistory WHERE OrderSeq = ch.OrderSeq) ";
            $sql .= " ORDER BY EntCustSeq, OrderSeq ";

            $ri = $this->dbAdapter->query($sql)->execute(array(':fr_date' => $tarm[2]['SpanFrom'].' 00:00:00', ':to_date' => $tarm[2]['SpanTo'].' 23:59:59'));
            if (!($ri->count() > 0)) {
                // 対象データなし時は、[$errorCode = 1]とし、falseを戻す
                $errorCode = 1;
                $this->dbAdapter->getDriver()->getConnection()->rollback();
                return false;
            }

            // ZIPファイル作成
            $zip = new \ZipArchive();

            // TEMP領域作成
            $tmpFilePath = tempnam( sys_get_temp_dir(), 'tmp' );

            // ZIPファイルオープン
            $zip->open( $tmpFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE );

            $unlinkList = array();

            // ファイルを開く
            $tmpFileName1 = $tmpFilePath . $fileName;
            $handle = fopen($tmpFileName1, "w");

            //-----------------------------------
            // (ヘッダー生成)
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

            $h_typeCode         = '91';
            $h_codeDivision     = '0';
//            $h_multiCode        = '00';
            $h_consignorCode    = $mdlsp->getValue(\models\Table\TableSystemProperty::DEFAULT_MODULE, 'systeminfo', 'CATSConsignorCodeMUFJ');
            $h_consignorName    = $mdlsp->getValue(\models\Table\TableSystemProperty::DEFAULT_MODULE, 'systeminfo', 'CATSConsignorNameMUFJ');
//            $h_transderDate     = date('m05', strtotime($today . ' +1 month')); // 処理月の次月5日
            $transderDate = $lgc->getTransderDate(2);
            $h_transderDate     = substr($transderDate, 5, 2) . substr($transderDate, 8, 2);
            $h_bankCode         = $mdlsp->getValue(\models\Table\TableSystemProperty::DEFAULT_MODULE, 'systeminfo', 'CATSBankCodeMufj');
            $h_bankName         = '               ';
            $h_branchCode       = $mdlsp->getValue(\models\Table\TableSystemProperty::DEFAULT_MODULE, 'systeminfo', 'CATSBranchCodeMufj');
            $h_branchName       = '               ';
            $h_depositType      = $mdlsp->getValue(\models\Table\TableSystemProperty::DEFAULT_MODULE, 'systeminfo', 'CATSDepositTypeMufj');
            $h_accountNumber    = $mdlsp->getValue(\models\Table\TableSystemProperty::DEFAULT_MODULE, 'systeminfo', 'CATSAccountNumberMufj');
            $h_reserve          = '                 ';

            $wstream = ('1' . $h_typeCode . $h_codeDivision . $h_multiCode . $h_consignorCode . $h_consignorName . $h_transderDate . $h_bankCode . $h_bankName . $h_branchCode . $h_branchName . $h_depositType . $h_accountNumber . $h_reserve);
            fwrite($handle, mb_convert_encoding($wstream, 'sjis-win') . "\r\n");

            //-----------------------------------
            // (データ生成)
            $claimCnt = 0;
            $claimAmount = 0;

            $claimCnt = $ri->count();

            foreach ($ri as $row) {
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

                $d_bankCode         = $row['FfBankCode'];
                $d_bankName         = '               ';
                $d_branchCode       = $row['FfBranchCode'];
                $d_branchName       = '               ';
                $d_dummy            = '    ';
                $d_depositType      = $row['FfAccountClass'];
                $d_accountNumber    = $row['FfAccountNumber'];
                $d_depositorName    = $row['FfAccountName'];
                $d_claimAmount      = sprintf('%010d', $row['ClaimAmount']);
                $d_newCode          = '1'; // [0:2回目以降の請求の場合]に固定
                $work = $mdlsp->getValue(\models\Table\TableSystemProperty::DEFAULT_MODULE, 'systeminfo', 'CATSConsignorCodeMUFJ');
                $d_consignorCode    = substr($work, -5).'000';
                $d_entCustSeq       = sprintf('%012d', $row['EntCustSeq']);
                $d_resultCode       = '0';
                $d_reserve          = '        ';

                // 郵貯対応
                if ($row['FfBankCode'] == 9900) {
                    $d_bankName = 'ﾕｳﾁｮ           ';
                    $d_depositType = ' ';
                }

                $wstream = ('2' . $d_bankCode . $d_bankName . $d_branchCode . $d_branchName . $d_dummy . $d_depositType . $d_accountNumber . $d_depositorName . $d_claimAmount . $d_newCode . $d_consignorCode . $d_entCustSeq . $d_resultCode . $d_reserve);
                fwrite($handle, mb_convert_encoding($wstream, 'sjis-win') . "\r\n");

                $claimAmount += $row['ClaimAmount'];

                // T_ClaimHistory更新(T_ClaimHistory行単位)
                $mdlch->saveUpdate(array('ClaimFileOutputClass' => 1, 'UpdateDate' => date('Y-m-d H:i:s'), 'UpdateId' => $userId), $row['Seq']);
            }

            //-----------------------------------
            // (トレーラ生成)
            $t_claimCnt         = ''; // (トレーラ)請求合計件数
            $t_claimSum         = ''; // (トレーラ)請求合計金額
            $t_transferredCnt   = ''; // (トレーラ)振替済合計件数
            $t_transferredSum   = ''; // (トレーラ)振替済合計金額
            $t_impossibleCnt    = ''; // (トレーラ)振替不能合計件数
            $t_impossibleSum    = ''; // (トレーラ)振替不能合計金額
            $t_reserve          = ''; // (トレーラ)予備

            $t_claimCnt         = sprintf('%06d' , $claimCnt);
            $t_claimSum         = sprintf('%012d', $claimAmount);
            $t_transferredCnt   = '000000';
            $t_transferredSum   = '000000000000';
            $t_impossibleCnt    = '000000';
            $t_impossibleSum    = '000000000000';
            $t_reserve          = '                                                                 ';

            $wstream = ('8' . $t_claimCnt . $t_claimSum . $t_transferredCnt . $t_transferredSum . $t_impossibleCnt . $t_impossibleSum . $t_reserve);
            fwrite($handle, mb_convert_encoding($wstream, 'sjis-win') . "\r\n");

            //-----------------------------------
            // (エンド生成)
            $e_reserve          = ''; // (エンド)予備

            $e_reserve          = '                                                                                                                       ';

            $wstream = ('9' . $e_reserve);
            fwrite($handle, mb_convert_encoding($wstream, 'sjis-win') . "\r\n");

            // ファイルを閉じる
            fclose($handle);

            // ファイル追加
            $unlinkList[] = $tmpFileName1;
            $addFilePath = file_get_contents( $tmpFileName1 );
            $zip->addFromString( $fileName, $addFilePath );

            // ZIPファイルクローズ
            $zip->close();

            // BLOB登録(UPDATE)
            $fp = fopen($tmpFilePath, "rb");
            $obj_file = fread($fp, filesize($tmpFilePath));
            fclose($fp);
            $this->dbAdapter->query(" UPDATE T_ClaimAccountTransferFile SET ClaimFile = :ClaimFile WHERE Seq = :Seq ")->execute(array(':Seq' => $seq, ':ClaimFile' => $obj_file));

            // TEMP領域削除
            unlink( $unlinkList[0] );
            unlink( $tmpFilePath );

            // コミット
            $this->dbAdapter->getDriver()->getConnection()->commit();

            return true;

        } catch(\Exception $e) {
            // ロールバック
            $this->dbAdapter->getDriver()->getConnection()->rollback();
$this->logger->err($e->getMessage());
            if ($handle) { fclose($handle); }
            return false;
        }
    }
}

Application::getInstance()->run();
