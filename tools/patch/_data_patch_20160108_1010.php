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

use Coral\Base\Application\BaseApplicationAbstract;
use Zend\Db\Adapter\Adapter;
use Zend\Config\Reader\Ini;
use Coral\Base\BaseLog;
use Coral\Base\BaseGeneralUtils;
use models\Table\TableClaimHistory;
use models\Table\ATableReceiptControl;
use models\Table\TablePayingAndSales;
use models\Table\TableOrder;
use Coral\Coral\History\CoralHistoryOrder;

/**
 * アプリケーションクラスです。
 * 入金取消処理を行うパッチです。
 *
 */
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
     * メール環境
     */
    public $mail;

    /**
     * Logger
     * @var unknown
     */
    public $logger;

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

$this->logger->info('_data_patch_20160108_1010.php start');

            $globalConfig = include __DIR__ . '/../config/autoload/global.php';
            // 接続時間を設定する
            $rds_session_timezone = $globalConfig['RDS_SESSION_TIMEZONE'];
            if (isset($rds_session_timezone)) {
                $this->dbAdapter->query('SET SESSION time_zone = :time_zone')->execute(array(':time_zone'=>$rds_session_timezone));
            }

$this->dbAdapter->getDriver()->getConnection()->beginTransaction();

            // 本処理
            $this->_exec();

$this->dbAdapter->getDriver()->getConnection()->commit();
//$this->dbAdapter->getDriver()->getConnection()->rollback();

$this->logger->info('_data_patch_20160108_1010.php end');
            $exitCode = 0; // 正常終了
        } catch( \Exception $e ) {
            try{
                $this->dbAdapter->getDriver()->getConnection()->rollback();
            } catch ( \Exception $err) { }
            // エラーログを出力
            if ( isset($this->logger) ) {
$this->logger->err($e->getMessage());
$this->logger->err($e->getTraceAsString());
            }
        }

        // 終了コードを指定して処理終了
        exit($exitCode);

    }

    /**
     * 本処理
     */
    public function _exec()
    {
        $data = array(
             '23141013' => 4456
            ,'23244641' => 1536
            ,'23321043' => 16434
            ,'23343294' => 4986
        );

        foreach($data as $key => $val) {

            $oseq = (int)$key;

            // マイナスの入金データを作成する用に入金データを取得
            $sql = "SELECT * FROM T_ReceiptControl WHERE OrderSeq = :OrderSeq AND DATE_FORMAT(RegistDate, '%Y-%m-%d %H:%i') BETWEEN '2016-01-07 09:00' AND '2016-01-07 11:30' LIMIT 1";
            $ri = $this->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq));
            $rData = $ri->current();

            // 取得データをセット
            $data['receiptMethod']   = 1;
            $data['ReceiptDate']     = $rData['ReceiptDate'];
            $data['ReceiptAmount']   = $val * (-1);
            $data['branchBank']      = $rData['branchBank'];
            $data['cvsReceiptAgent'] = $rData['cvsReceiptAgent'];
            $data['receiptClass']    = $rData['receiptClass'];
            $data['DepositDate']     = $rData['DepositDate'];
            $data['classDetails']    = null;
            $data['accountNumber']   = null;
            $data['bankFlg']         = '1';     // 銀行入金区分：1

            // 更新処理実施
            try {
                // ユーザIDの取得
                $userId = 1;

                // コードマスタから銀行支店IDを取得する。
                $sql = "SELECT Class1 FROM M_Code WHERE CodeId = 153 AND KeyCode = :KeyCode";
                $branchBankId = $this->dbAdapter->query($sql)->execute(array(':KeyCode' => $data['branchBank']))->current()['Class1'];

                // 入金関連処理SQL
                $stm = $this->dbAdapter->query($this->getBaseP_ReceiptControl());

                // SQL実行結果取得用のSQL
                $getretvalsql = " SELECT @po_ret_sts AS po_ret_sts, @po_ret_errcd AS po_ret_errcd, @po_ret_sqlcd AS po_ret_sqlcd, @po_ret_msg AS po_ret_msg ";

                $prm = array(
                        ':pi_receipt_amount'   => $data['ReceiptAmount'],
                        ':pi_order_seq'        => $oseq,
                        ':pi_receipt_date'     => $data['ReceiptDate'],
                        ':pi_receipt_class'    => $data['receiptMethod'],
                        ':pi_branch_bank_id'   => (! empty($branchBankId)) ? $branchBankId : null,
                        ':pi_receipt_agent_id' => $data['cvsReceiptAgent'],
                        ':pi_deposit_date'     => $data['DepositDate'],
                        ':pi_user_id'          => $userId,
                );

                $ri = $stm->execute($prm);

                // SQL実行例外なしもエラー戻り値の時は例外をｽﾛｰ
                $retval = $this->dbAdapter->query($getretvalsql)->execute(null)->current();
                if ($retval['po_ret_sts'] != 0) {
                    throw new \Exception($retval['po_ret_msg']);
                }

                // 未印刷の請求書印刷予約データを削除
                $mdlch = new TableClaimHistory($this->dbAdapter);
                $mdlch->deleteReserved($oseq, $userId);

                // 2015/10/05 Y.Suzuki Add 会計対応 Stt
                // 会計用項目をINSERT
                // 入金管理Seqの取得（複数存在する場合を考慮して、MAX値を取得する）
                $sql = "SELECT MAX(ReceiptSeq) AS ReceiptSeq FROM T_ReceiptControl WHERE OrderSeq = :OrderSeq";
                $rcptSeq = $this->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current()['ReceiptSeq'];
                $atdata = array(
                        'ReceiptSeq' => $rcptSeq,
                        'AccountNumber' => $data['accountNumber'],
                        'ClassDetails' => $data['classDetails'],
                        'BankFlg' => $data['bankFlg'],
                );

                $mdlatrc = new ATableReceiptControl($this->dbAdapter);
                $mdlatrc->saveNew($atdata);
                // 2015/10/05 Y.Suzuki Add 会計対応 End

                // 立替・売上管理テーブル更新
                $mdlpas = new TablePayingAndSales($this->dbAdapter);
                $mdlo = new TableOrder($this->dbAdapter);
                // 注文データを取得
                $ri = $mdlo->findOrder(array('P_OrderSeq' => $oseq));
                $order = ResultInterfaceToArray($ri);

                // 取得できた件数分、ループする
                foreach ($order as $key => $value) {
                    // 入金済み正常クローズの場合、無条件に立替対象とする。
                    if ($value['DataStatus'] == 91 && $value['CloseReason'] == 1) {
                        $mdlpas->clearConditionForCharge($value['OrderSeq'], 1, $userId);
                    }
                }

                // 作成した入金管理Seqを取得する（注文Seqに対する入金は複数存在する可能性があるため、MAXの入金Seqを取得する）
                $sql = "SELECT MAX(ReceiptSeq) AS ReceiptSeq FROM T_ReceiptControl WHERE OrderSeq = :OrderSeq GROUP BY OrderSeq";
                $rcptSeq = $this->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current()['ReceiptSeq'];

//メール送信は行わないの明示 Stt(20151224_1955)
//                 try
//                 {
//                     // 入金確認メール送信
//                     $mail = new CoralMail($this->app->dbAdapter, $this->app->mail['smtp']);
//                     $mail->SendRcptConfirmMail($rcptSeq, $userId);
//                 }
//                 catch(\Exception $e) {  }
//メール送信は行わないの明示 End(20151224_1955)

                // 注文履歴登録用に親注文Seqから子注文Seqを再取得する。
                $sql = "SELECT OrderSeq FROM T_Order WHERE P_OrderSeq = :P_OrderSeq AND Cnl_Status = 0";

                $ri = $this->dbAdapter->query($sql)->execute(array(':P_OrderSeq' => $oseq));
                $rows = ResultInterfaceToArray($ri);

                // 注文履歴へ登録
                $history = new CoralHistoryOrder($this->dbAdapter);
                // 取得できた件数分ループする
                foreach ($rows as $row) {
                    // 注文履歴登録
                    $history->InsOrderHistory($row["OrderSeq"], 65, $userId);
                }

            }
            catch(\Exception $err) {
                throw new \Exception('hoge_20160108');
            }

        }

        return;
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
,   @po_ret_sts
,   @po_ret_errcd
,   @po_ret_sqlcd
,   @po_ret_msg
    )
EOQ;
    }
}

Application::getInstance()->run();
