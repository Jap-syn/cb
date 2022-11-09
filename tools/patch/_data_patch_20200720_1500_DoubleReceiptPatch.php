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
use Coral\Base\BaseLog;
use Zend\Db\Adapter\Adapter;
use Zend\Config\Reader\Ini;
use models\Table\TableUser;
use models\Table\TableOrder;
use models\Table\TableCancel;
use models\Table\TablePayingAndSales;
use models\Table\TableStampFee;
use models\Table\TableOemSettlementFee;
use models\Table\TableOemClaimFee;
use models\Table\TableClaimControl;
use Coral\Coral\Mail\CoralMail;
use models\Logic\LogicCancel;
use Coral\Coral\History\CoralHistoryOrder;
use models\Logic\LogicSmbcRelation;
use models\Logic\Jnb\LogicJnbAccount;
use models\Table\TableReceiptControl;
use models\Table\ATableReceiptControl;
use models\Table\TableSundryControl;
use models\Table\TableStagnationAlert;


/**
 * アプリケーションクラスです。
 *
 */
class Application extends BaseApplicationAbstract {
    protected $_application_id = 'tools-CancelRegister-batch';

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
     * @var BaseLog
     */
    public $logger;

    /**
     * メール環境
     */
    public $mail;

    /**
     * アプリケーションを実行します。
     *
     * @access public
     */
    public function run() {
        $exitCode = 1;

        error_reporting(0);

        try {

            // 実行確認
            echo "Run the Double Receipt Patch. Is it OK?(Y/N)";
            $yn = trim(fgets(STDIN));
            if (strtoupper($yn) != 'Y') {
                echo "It has stopped the execution. ";
                exit(0);
            }

            $start = microtime(true);

            // iniファイルから設定を取得
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
$this->logger->info('_data_patch_20200720_1500_DoubleReceiptPatch.php start');

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

            // ------------------------------------------------------------------------->
            // 注文SEQのリスト
            $arrOseq = array(
                    '48140800',
                    '48038362',
                    '48074632',
                    '47983665',
                    '47016906',
                    '48074376',
                    '47917721',
                    '48140397',
                    '47872950',
                    '47912981',
                    '47695205',
                    '47712797',
                    '47879266',
                    '47818922',
                    '48163914',
                    '47784117',
                    '48136830',
                    '47738345',
                    '48199706',
                    '48093463',
                    '47751681',
                    '48190513',
                    '48134730',
                    '47894449',
                    '48128166',
                    '48140749',
                    '48164221',
                    '46461620',
                    '46461658',
                    '47726512',
                    '47985104',
                    '47827823',
                    '46230039',
                    '48183893',
                    '47725849',
                    '46629832',
                    '48173597',
                    '48146408',
                    '48073944',
                    '47922503',
                    '48185278',
                    '48188722',
                    '47392535',
                    '48070517',
                    '48071300',
                    '47612395',
                    '47316795',
                    '48200817',
                    '47859234',
                    '48093954',
                    '44808301',
                    '48166979',
                    '47739286',
                    '47921758',
                    '47888500',
                    '48173843',
                    '47791515',
                    '47837512',
                    '48099485',
                    '48095351',
                    '48008258',
                    '48071553',
                    '48134056',
                    '48087920',
                    '47691506',
                    '47838169',
                    '47683673',
                    '47889948',
                    '47890617',
                    '45162041',
                    '48135069',
                    '47913074',
                    '48106889',
                    '47799108',
                    '48092108',
                    '47875457',
                    '48111234',
                    '47792693',
                    '47727363',
                    '47501230',
                    '48178362',
                    '48144097',
                    '47839475',
                    '47918414',
                    '47741487',
                    '47890037',
                    '47808197',
                    '47876607',
                    '47983907',
                    '47948256',
                    '47975281',
                    '47983879',
                    '47969340',
                    '47809348',
                    '47812296',
                    '48073912',
                    '48118170',
                    '47405777',
                    '47760786',
                    '47864418',
                    '47974148',
                    '47918408',
                    '48059157',
                    '47812209',
                    '47921280',
                    '47773010',
                    '48194352',
                    '47809343',
                    '47801435',
                    '48177360',
                    '46534207',
                    '46579789',
                    '48064213',
                    '47755880',
                    '48026022',
                    '44726270',
                    '48037183',
                    '47799456',
                    '47896309',
                    '47906584',
            );

            // <-------------------------------------------------------------------------

            $this->rcptcancelRun($arrOseq, $userId);

            $end = microtime(true);

echo ($end - $start);

            $exitCode = 0; // 正常終了
$this->logger->info('_data_patch_20200720_1500_DoubleReceiptPatch.php end');

        } catch( \Exception $e ) {
            // エラーログを出力
            if ( isset($this->logger) ) {
$this->logger->err('<DoubleReceiptPatch> ' . $e->getMessage());
$this->logger->err('<DoubleReceiptPatch> ' . $e->getTraceAsString());
            }
        }

        // 終了コードを指定して処理終了
        exit($exitCode);

    }

    /**
     * 入金取消メイン処理
     */
    private function rcptcancelRun($arrOseq, $userId) {

        $mdlo = new TableOrder($this->dbAdapter);

        // [一括コミット]とする
$this->dbAdapter->getDriver()->getConnection()->beginTransaction();

        // 注文SEQのリストを１件ずつ入金取消し
        foreach($arrOseq as $key) {
            // 入力チェック
            if ($this->isReceiptCancel($key) == false) {
                continue;
            }

            // 入金取消しを行う
            $this->rcptcancelAction($key, $userId);

            $this->logger->info('[DoubleReceipt]' . "\t" . $key . "\t" . 'complete!!');

        }

$this->dbAdapter->getDriver()->getConnection()->commit();

    }

    /**
     * 入金取消し可能か否か
     * @param unknown $key
     * @param unknown $value
     * @return boolean
     */
    public function isReceiptCancel($key) {

        $mdlo = new TableOrder($this->dbAdapter);

        // 注文SEQが存在すること
        $row = $mdlo->find($key)->current();
        if (!$row) {
            // データが取得出来ない場合
            $this->logger->alert('[DoubleReceipt]' . "\t" . $key . "\t" . 'OrderSeq Is Not Found');
            return false;
        }

        // キャンセルされていないこと
        if ($mdlo->isCanceled($key)) {
            $this->logger->alert('[DoubleReceipt]' . "\t" . $key . "\t" . 'Is Cancel');
            return false;
        }

        // 一部入金もしくは、入金クローズではない
        if ( !(($row['DataStatus'] == 61) || ($row['DataStatus'] == 91 && $row['CloseReason'] == 1)) ) {
            $this->logger->alert('[DoubleReceipt]' . "\t" . $key . "\t" . 'DataStatus Is InValid[DataStatus=' . $row['DataStatus'] . ',CloseReason=' . $row['CloseReason'] . ']');
            return false;
        }


        // 返金されていないこと
        $sql = ' SELECT COUNT(1) AS CNT FROM T_ClaimControl cc,T_RepaymentControl rc WHERE cc.ClaimId = rc.ClaimId AND cc.OrderSeq = :OrderSeq AND rc.RepayStatus IN (0, 1) ';
        $row = $this->dbAdapter->query($sql)->execute(array(':OrderSeq' => $key))->current();
        $cnt = (int)$row['CNT'];
        if ($cnt > 0) {
            // 返金済み
            $this->logger->alert('[DoubleReceipt]' . "\t" . $key . "\t" . 'Is Repayment Input');
            return false;
        }

        // 手動の雑損失、雑収入がないこと
        $sql = ' SELECT COUNT(1) AS CNT FROM T_SundryControl sc WHERE sc.OrderSeq = :OrderSeq AND sc.SundryClass <> 99 ';
        $row = $this->dbAdapter->query($sql)->execute(array(':OrderSeq' => $key))->current();
        $cnt = (int)$row['CNT'];
        if ($cnt > 0) {
            // 雑損失または雑収入の入力あり
            $this->logger->alert('[DoubleReceipt]' . "\t" . $key . "\t" . 'Is Sundry Input');
            return false;
        }

        return true;
    }

    /**
     * (ajax)入金取消処理
     */
    public function rcptcancelAction($oseq, $userId)
    {
        // $params = $this->getParams();

        // $oseq = isset($params['oseq']) ? $params['oseq'] : 0;

        // 更新処理を行う。
        // $this->dbAdapter->getDriver()->getConnection()->beginTransaction();
        try {
            // ユーザーIDの取得
            // $obj = new \models\Table\TableUser($this->dbAdapter);
            // $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

            // -------------------------
            // エラーチェック
            // -------------------------
            // 注文データを取得
            $sql = "SELECT COUNT(*) AS cnt FROM T_Order WHERE OrderSeq = :OrderSeq AND Cnl_Status = 0";
            $cnt = $this->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current()['cnt'];
            // 未キャンセル以外の場合エラー（未キャンセルのデータが存在していれば処理が流れる）
            if ($cnt == 0) {
                $msg = 'キャンセル申請中、もしくはキャンセル済みの注文のため、取消できません。';
                // ロールバック
                // $this->dbAdapter->getDriver()->getConnection()->rollback();
            } else {
                // -------------------------
                // 入金データを取得
                // -------------------------
                $mdlrc = new TableReceiptControl($this->dbAdapter);

                // 直近の1件を取得(登録日で降順ソートしたLIMIT1を取得)
                $data = $this->dbAdapter->query("SELECT * FROM T_ReceiptControl WHERE OrderSeq = :OrderSeq ORDER BY RegistDate DESC LIMIT 1")->execute(array(':OrderSeq' => $oseq))->current();

                // 注文Seqでｻﾏﾘして金額項目を取得
                $sql = <<<EOQ
SELECT  OrderSeq
    ,   SUM(ReceiptAmount) AS ReceiptAmount
    ,   SUM(CheckingUseAmount) AS CheckingUseAmount
    ,   SUM(CheckingClaimFee) AS CheckingClaimFee
    ,   SUM(CheckingDamageInterestAmount) AS CheckingDamageInterestAmount
    ,   SUM(CheckingAdditionalClaimFee) AS CheckingAdditionalClaimFee
FROM    T_ReceiptControl
WHERE   OrderSeq = :OrderSeq
GROUP BY
        OrderSeq
;
EOQ;

                $amountData = $this->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current();

                // 金額項目のみ -1 を掛け、入金処理日はシステム日時。
                $amount = array(
                        'ReceiptProcessDate' => date('Y-m-d H:i:s'),
                        'ReceiptAmount' => $amountData['ReceiptAmount'] * -1,
                        'CheckingUseAmount' => $amountData['CheckingUseAmount'] * -1,
                        'CheckingClaimFee' => $amountData['CheckingClaimFee'] * -1,
                        'CheckingDamageInterestAmount' => $amountData['CheckingDamageInterestAmount'] * -1,
                        'CheckingAdditionalClaimFee' => $amountData['CheckingAdditionalClaimFee'] * -1,
                        'RegistId' => $userId,
                        'UpdateId' => $userId,
                );
                // 取得データに金額項目をマージして新規登録
                $rcptSeq = $mdlrc->saveNew(array_merge($data, $amount));        // 2015/11/16 Y.Suzuki 会計対応 Mod

                // 2015/11/16 Y.Suzuki Add 会計対応 Stt
                $mdlatrc = new ATableReceiptControl($this->dbAdapter);
                // 入金取消した会計用のデータを取得
                $atdata = $mdlatrc->find($data['ReceiptSeq'])->current();

                // 2016/01/05 Y.Suzuki Add 入金取消前のデータを取得 Stt
                // 入金取消前立替クリアフラグ、入金取消前立替クリア日
                $sql = "SELECT ClearConditionForCharge, ClearConditionDate FROM T_PayingAndSales WHERE OrderSeq = :OrderSeq";
                $ri = $this->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq));
                $clearConditionForCharge = $ri->current()['ClearConditionForCharge'];
                $clearConditionDate = $ri->current()['ClearConditionDate'];
                // 入金取消前立替処理－ステータス、入金取消前配送－着荷確認
                $sql = "SELECT Cnl_Status, Deli_ConfirmArrivalFlg FROM T_Order WHERE OrderSeq = :OrderSeq";
                $ri = $this->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq));
                $cnlStatus = $ri->current()['Cnl_Status'];
                $deliConfirmArrivalFlg = $ri->current()['Deli_ConfirmArrivalFlg'];
                $candata = array(
                        'ReceiptSeq' => $rcptSeq,
                        'Rct_CancelFlg' => 1,
                        'Before_ClearConditionForCharge' => $clearConditionForCharge,
                        'Before_ClearConditionDate' => $clearConditionDate,
                        'Before_Cnl_Status' => $cnlStatus,
                        'Before_Deli_ConfirmArrivalFlg' => $deliConfirmArrivalFlg
                );
                // 2016/01/05 Y.Suzuki Add 入金取消前のデータを取得 End

                // 取得データに入金管理Seqをマージして新規登録
                $mdlatrc->saveNew(array_merge($atdata, $candata));      // 2016/01/05 Y.Suzuki 会計関連_入金取消対応 Mod
                // 2015/11/16 Y.Suzuki Add 会計対応 End

                // -------------------------
                // 雑損失データを取得
                // -------------------------
                $mdlsc = new TableSundryControl($this->dbAdapter);

                // 会計対象外データを取得
                // 直近の1件を取得(登録日で降順ソートしたLIMIT1を取得)
                $sql = "SELECT * FROM T_SundryControl WHERE SundryType = 1 AND SundryClass = 99 AND OrderSeq = :OrderSeq ORDER BY RegistDate DESC LIMIT 1";
                $data = $this->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current();

                // データが取得出来た場合のみ、以下処理を行う。
                if (! empty($data)) {
                    // 注文Seqでｻﾏﾘして金額項目を取得
                    $sql = <<<EOQ
SELECT  OrderSeq
    ,   SUM(SundryAmount) AS SundryAmount
    ,   SUM(CheckingUseAmount) AS CheckingUseAmount
    ,   SUM(CheckingClaimFee) AS CheckingClaimFee
    ,   SUM(CheckingDamageInterestAmount) AS CheckingDamageInterestAmount
    ,   SUM(CheckingAdditionalClaimFee) AS CheckingAdditionalClaimFee
FROM    T_SundryControl
WHERE   SundryType = 1
AND     SundryClass = 99
AND     OrderSeq = :OrderSeq
GROUP BY
        OrderSeq
;
EOQ;

                    $amountData = $this->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current();

                    // 取得データのSundryAmount が 0 の場合は処理しない
                    if ($amountData['SundryAmount'] > 0) {
                        // 金額項目のみ -1 を掛け、発生日はシステム日時
                        $amount = array(
                                'ProcessDate' => date('Y-m-d H:i:s'),
                                'SundryAmount' => $amountData['SundryAmount'] * -1,
                                'CheckingUseAmount' => $amountData['CheckingUseAmount'] * -1,
                                'CheckingClaimFee' => $amountData['CheckingClaimFee'] * -1,
                                'CheckingDamageInterestAmount' => $amountData['CheckingDamageInterestAmount'] * -1,
                                'CheckingAdditionalClaimFee' => $amountData['CheckingAdditionalClaimFee'] * -1,
                                'RegistId' => $userId,
                                'UpdateId' => $userId,
                        );
                        // 取得データに金額項目をマージして新規登録
                        $mdlsc->saveNew(array_merge($data, $amount));
                    }
                }

                // 会計対象データを取得
                // 直近の1件を取得(登録日で降順ソートしたLIMIT1を取得)
                $sql = "SELECT * FROM T_SundryControl WHERE SundryType = 1 AND SundryClass <> 99 AND OrderSeq = :OrderSeq ORDER BY RegistDate DESC LIMIT 1";
                $data = $this->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current();

                // データが取得できた場合のみ、以下処理を行う。
                if (! empty($data)) {
                    // 注文Seqでｻﾏﾘして金額項目を取得
                    $sql = <<<EOQ
SELECT  OrderSeq
    ,   SUM(SundryAmount) AS SundryAmount
    ,   SUM(CheckingUseAmount) AS CheckingUseAmount
    ,   SUM(CheckingClaimFee) AS CheckingClaimFee
    ,   SUM(CheckingDamageInterestAmount) AS CheckingDamageInterestAmount
    ,   SUM(CheckingAdditionalClaimFee) AS CheckingAdditionalClaimFee
FROM    T_SundryControl
WHERE   SundryType = 1
AND     SundryClass <> 99
AND     OrderSeq = :OrderSeq
GROUP BY
        OrderSeq
;
EOQ;

                    $amountData = $this->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current();

                    // 取得データのSundryAmount が 0 の場合は処理しない
                    if ($amountData['SundryAmount'] > 0) {
                        // 金額項目のみ -1 を掛け、発生日はシステム日時
                        $amount = array(
                                'ProcessDate' => date('Y-m-d H:i:s'),
                                'SundryAmount' => $amountData['SundryAmount'] * -1,
                                'CheckingUseAmount' => $amountData['CheckingUseAmount'] * -1,
                                'CheckingClaimFee' => $amountData['CheckingClaimFee'] * -1,
                                'CheckingDamageInterestAmount' => $amountData['CheckingDamageInterestAmount'] * -1,
                                'CheckingAdditionalClaimFee' => $amountData['CheckingAdditionalClaimFee'] * -1,
                                'RegistId' => $userId,
                                'UpdateId' => $userId,
                        );
                        // 取得データに金額項目をマージして新規登録
                        $mdlsc->saveNew(array_merge($data, $amount));
                    }
                }

                // -------------------------
                // 雑収入データを取得
                // -------------------------
                // 直近の1件を取得(登録日で降順ソートしたLIMIT1を取得)
                $sql = "SELECT * FROM T_SundryControl WHERE SundryType = 0 AND OrderSeq = :OrderSeq ORDER BY RegistDate DESC LIMIT 1";
                $data = $this->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current();

                // データが取得できた場合のみ、以下処理を行う。
                if (! empty($data)) {
                    // 注文Seqでｻﾏﾘして金額項目を取得
                    $sql = <<<EOQ
SELECT  OrderSeq
    ,   SUM(SundryAmount) AS SundryAmount
    ,   SUM(CheckingUseAmount) AS CheckingUseAmount
    ,   SUM(CheckingClaimFee) AS CheckingClaimFee
    ,   SUM(CheckingDamageInterestAmount) AS CheckingDamageInterestAmount
    ,   SUM(CheckingAdditionalClaimFee) AS CheckingAdditionalClaimFee
FROM    T_SundryControl
WHERE   SundryType = 0
AND     OrderSeq = :OrderSeq
GROUP BY
        OrderSeq
;
EOQ;

                    $amountData = $this->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current();

                    // 取得データのSundryAmount が 0 の場合は処理しない
                    if ($amountData['SundryAmount'] > 0) {
                        // 金額項目のみ -1 を掛け、発生日はシステム日時
                        $amount = array(
                                'ProcessDate' => date('Y-m-d H:i:s'),
                                'SundryAmount' => $amountData['SundryAmount'] * -1,
                                'CheckingUseAmount' => $amountData['CheckingUseAmount'] * -1,
                                'CheckingClaimFee' => $amountData['CheckingClaimFee'] * -1,
                                'CheckingDamageInterestAmount' => $amountData['CheckingDamageInterestAmount'] * -1,
                                'CheckingAdditionalClaimFee' => $amountData['CheckingAdditionalClaimFee'] * -1,
                                'RegistId' => $userId,
                                'UpdateId' => $userId,
                        );
                        // 取得データに金額項目をマージして新規登録
                        $mdlsc->saveNew(array_merge($data, $amount));
                    }
                }

                // -------------------------
                // 印紙代データを取得
                // -------------------------
                // 直近の1件を取得(登録日で降順ソートしたLIMIT1を取得)
                $sql = "SELECT * FROM T_StampFee WHERE OrderSeq = :OrderSeq ORDER BY RegistDate DESC LIMIT 1";
                $data = $this->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current();

                // データが取得できた場合のみ、以下処理を行う。
                if (! empty($data)) {
                    // 注文Seqでｻﾏﾘして金額項目を取得
                    $sql = "SELECT OrderSeq , SUM(StampFee) AS StampFee FROM T_StampFee WHERE OrderSeq = :OrderSeq GROUP BY OrderSeq";
                    $amountData = $this->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current();

                    // 取得データのStampFee が 0 の場合は処理しない
                    if ($amountData['StampFee'] > 0) {
                        // 金額項目のみ -1 を掛け、発生確定日はシステム日時
                        $amount = array(
                                'DecisionDate' => date('Y-m-d H:i:s'),
                                'StampFee' => $amountData['StampFee'] * -1,
                                'RegistId' => $userId,
                                'UpdateId' => $userId,
                        );
                        // 取得データに金額項目をマージして新規登録
                        $mdlsf = new TableStampFee($this->dbAdapter);
                        $mdlsf->saveNew(array_merge($data, $amount));
                    }
                }

                // 注文データを更新
                $mdlo = new TableOrder($this->dbAdapter);
                $mdlo->saveUpdateWhere(array('DataStatus' => 61, 'CloseReason' => 0, 'UpdateId' => $userId), array('P_OrderSeq' => $oseq));

                // 注文データを取得
                $orderData = ResultInterfaceToArray($mdlo->findOrder(array('P_OrderSeq' => $oseq)));

                // 取得件数分、ループする
                foreach ($orderData as $key => $value) {
                    // 立替・売上管理データを取得
                    $mdlpas = new TablePayingAndSales($this->dbAdapter);
                    $pasData = $this->dbAdapter->query("SELECT * FROM T_PayingAndSales WHERE OrderSeq = :OrderSeq")->execute(array(':OrderSeq' => $value['OrderSeq']))->current();

                    // 立替クリアフラグが上がっており、未立替　かつ　着荷確認済みでない　場合は、立替クリアフラグを落とす
                    if ($pasData['ClearConditionForCharge'] == 1 && $pasData['PayingControlStatus'] == 0 && $value['Deli_ConfirmArrivalFlg'] <> 1) {
                        // 立替・売上管理データを更新
                        $mdlpas->saveUpdate(array('ClearConditionForCharge' => 0, 'ClearConditionDate' => null, 'UpdateId' => $userId), $pasData['Seq']);

                        // 立替・売上管理_会計更新(売上ﾀｲﾌﾟ、売上日の初期化)
                        $row_pas = $this->dbAdapter->query(" SELECT Seq FROM T_PayingAndSales WHERE OrderSeq = :OrderSeq ")->execute(array(':OrderSeq' => $value['OrderSeq']))->current();
                        $mdlapas = new \models\Table\ATablePayingAndSales($this->dbAdapter);
                        $mdlapas->saveUpdate(array('ATUriType' => 99, 'ATUriDay' => '99999999'), $row_pas['Seq']);
                    }
                }

                // 請求管理更新
                // 請求額 = 請求残高へ更新する。
                $sql = <<<EOQ
UPDATE  T_ClaimControl
SET     ClaimedBalance = ClaimAmount
    ,   ReceiptAmountTotal = 0
    ,   SundryLossTotal = 0
    ,   SundryIncomeTotal = 0
    ,   CheckingClaimAmount = 0
    ,   CheckingUseAmount = 0
    ,   CheckingClaimFee = 0
    ,   CheckingDamageInterestAmount = 0
    ,   CheckingAdditionalClaimFee = 0
    ,   BalanceClaimAmount = ClaimAmount
    ,   BalanceUseAmount = UseAmountTotal
    ,   BalanceClaimFee = ClaimFee
    ,   BalanceDamageInterestAmount = DamageInterestAmount
    ,   BalanceAdditionalClaimFee = AdditionalClaimFee
    ,   UpdateDate = :UpdateDate
    ,   UpdateId = :UpdateId
    ,   LastReceiptSeq = :LastReceiptSeq
WHERE   OrderSeq = :OrderSeq
;
EOQ;

                // 更新実行
                $this->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq, ':UpdateId' => $userId, ':UpdateDate' => date('Y-m-d H:i:s'), ':LastReceiptSeq' => $rcptSeq));

                // 停滞アラートを更新
                $mdlsa = new TableStagnationAlert($this->dbAdapter);
                $mdlsa->saveUpdateWhere(array('AlertSign' => 0, 'UpdateId' => $userId), array('OrderSeq' => $oseq));

                try
                {
                    // 入金未確認ﾒｰﾙを送信する。
                    // 詳細が決定するまで保留。
                }
                catch(\Exception $e) {  }

                // 注文履歴登録用に親注文Seqから子注文Seqを再取得する。
                $sql = <<<EOQ
SELECT  OrderSeq
FROM    T_Order
WHERE   P_OrderSeq = :P_OrderSeq
AND     Cnl_Status = 0
;
EOQ;

                $ri = $this->dbAdapter->query($sql)->execute(array(':P_OrderSeq' => $oseq));
                $rows = ResultInterfaceToArray($ri);

                // 注文履歴へ登録
                $history = new CoralHistoryOrder($this->dbAdapter);
                // 親注文Seqに紐づく子注文分、ループする。
                foreach ($rows as $row) {
                    // 注文履歴登録
                    $history->InsOrderHistory($row['OrderSeq'], 65, $userId);
                }

                // コミット
                // $this->dbAdapter->getDriver()->getConnection()->commit();
                // 成功指示
                $msg = '1';
            }
        } catch (\Exception $e) {
            throw $e;
            // ロールバック
            // $this->dbAdapter->getDriver()->getConnection()->rollback();
            // エラー内容吐き出し
            // $msg = $e->getMessage();
        }

//         echo \Zend\Json\Json::encode(array('status' => $msg));
//         return $this->response;
    }

}

Application::getInstance()->run();
