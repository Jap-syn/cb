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
use models\Table\TableBatchLock;
use models\Table\TableCode;
use models\Table\TableClaimHistory;
use models\Table\TablePayeasyReceived;
use models\Table\TablePayeasyError;
use models\Table\TablePayingAndSales;
use models\Table\TableReceiptControl;
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
     * バッチID
     *
     * @var unknown
     */
    const EXECUTE_BATCH_ID = 5;

    /**
     * Application の唯一のインスタンスを取得します。
     *
     * @static
     * @access public
     * @return Application
     */
    public static function getInstance() {
        if ( self::$_instance === null ) {
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

$this->logger->info('importPayeasy.php start');

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

            // システムステータス
            $mdlbl = new TableBatchLock ( $this->dbAdapter );
            $BatchLock = $mdlbl->getLock( $this::EXECUTE_BATCH_ID );

            // 起動チェック
            if ($BatchLock > 0) {
                // Payeasy入金データ取込
                $errormessage = "";
                $isSuccess = $this->importPayeasy($pathFileName, $seq, $userId, $errormessage, $ImportedFlg);

                // ロック解除
                $mdlbl->releaseLock( $this::EXECUTE_BATCH_ID );

            } else {
$this->logger->alert("Can't execute by Locking.\r\n");

            }

$this->logger->info('importPayeasy.php end');

            $exitCode = 0;

        } catch( \Exception $e ) {
            // エラーログを出力
            if ( isset($this->logger) ) {
$this->logger->err($e->getMessage());

            }

            if (! empty ( $BatchLock )) {
                if ($BatchLock > 0) {
                    // ロック解除
                    $mdlbl->releaseLock( $this::EXECUTE_BATCH_ID );

                }

            }
        }

        // 終了コードを指定して処理終了
        exit($exitCode);

    }

    /**
     * Payeasy入金データ取込
     *
     * @param int $userId ユーザID
     * @return boolean true:成功／false:失敗
     */
    protected function importPayeasy($seq, $userId, &$errormessage) {

        $handle = null;
        try {
            // トランザクション開始
            $this->dbAdapter->getDriver()->getConnection()->beginTransaction();

            $stm = $this->dbAdapter->query($this->getBaseP_ReceiptControl());

            // SQL実行結果取得用のSQL
            $getretvalsql = " SELECT @po_ret_sts AS po_ret_sts, @po_ret_errcd AS po_ret_errcd, @po_ret_sqlcd AS po_ret_sqlcd, @po_ret_msg AS po_ret_msg ";

            // テーブルモジュールの使用宣言
            $mdlperr = new TablePayeasyError($this->dbAdapter);
            $mdlprd = new TablePayeasyReceived($this->dbAdapter);
            $mdlc = new TableCode($this->dbAdapter);

            // ハッシュ用パスワード
            $hashKey = $mdlc->getMasterDescription(205, 6);

            // システム日付の取得
            $dateTime = new DateTime();
            $today = $dateTime->format('Y-m-d');
            $sysYear = $dateTime->format('Y');

            // 処理対象データの抽出
            $sql  = "SELECT";
            $sql .= " Seq";
            $sql .= ", bktrans AS targetNumber";
            $sql .= ", amount AS paymentAmount";
            $sql .= ", tdate AS receiptDate";
            $sql .= ", ddate AS depositDate";
            $sql .= ", rchksum AS CheckSum";
            $sql .= ", rsltcd AS ResultCd";
            $sql .= ", CONCAT(p_ver, stdate, stran, bkcode, shopid, cshopid, amount, mbtran, bktrans, tranid, ddate, tdate, rsltcd) AS HashCodeBase";
            $sql .= ", p_ver, stdate, stran, bkcode, shopid, cshopid, amount, mbtran, bktrans, tranid, ddate, tdate, rsltcd, rchksum";
            $sql .= " FROM T_PayeasyReceived";
            $sql .= " WHERE 1 = 1";
            $sql .= " AND ProcessedFlg = '0'";
            $sql .= " ORDER BY";
            $sql .= " Seq";
            $rowPayeasys = $this->dbAdapter->query( $sql )->execute();

            $chkValues = array();
            foreach ($rowPayeasys as $keys => $values) {
                $chkValue = array();
                foreach ($values as $key => $value) {
                    $chkValue[$key] = $value;
                }
                $chkValues[$keys] = $chkValue;
            }

            // 確報ループ
            foreach ($chkValues as $line) {

                // 入金通知管理番号
                $payeasySeq = $line['Seq'];

                // エラーテーブル登録用
                $tmpDisplay = array();

                // 各項目正規表現チェック
                $errors = array();
                $errors = $this->validate( $line );
                if ( !empty( $errors ) ) {
                    // (表示用(エラー)データ登録)
                    $tmpDisplay = array(
                            'OrderSeq'      => null,
                            'PaymentAmount' => null,
                            'ErrorCode'     => '1',
                            'ErrorMsg'      => 'Payeasy連携エラー[Seq:'. $payeasySeq. ']、通知データの不備：validation errors = '. var_export($errors, true),
                    );
                    $mdlperr->saveNew($tmpDisplay);
                    $mdlprd->saveUpdate(array( 'ProcessedFlg' => '1' ), $payeasySeq);
                    continue;

                }

                // (入金日)
                $receiptDate = substr($line['receiptDate'], 0, 4). '-'. substr($line['receiptDate'], 4, 2). '-'. substr($line['receiptDate'], 6, 2);
                $depositDate = $today;

                // 入金金額
                $paymentAmount = $line['paymentAmount'];

                // 注文特定用キー情報
                $targetNumber = $line['targetNumber'];

                // ハッシュチェック用データ生成
                $hashCodeBase = $line['HashCodeBase']. $hashKey;          // 連携情報＋ハッシュキー
                $hashCodeBase = mb_convert_encoding($hashCodeBase, 'sjis-win', 'UTF-8');
                $hashCode = md5($hashCodeBase);                    // MD5変換
                $checkSum = htmlspecialchars($hashCode); // HTMLEncode UTF-8

                // ハッシュチェック
                if ( !empty($line['CheckSum']) && $checkSum <> $line['CheckSum']) {
                    // (表示用(エラー)データ登録)
                    $tmpDisplay = array(
                            'OrderSeq'      => null,
                            'PaymentAmount' => $paymentAmount,
                            'ErrorCode'     => '1',
                            'ErrorMsg'      => 'Payeasy連携エラー[Seq:'. $payeasySeq. ']、データのハッシュ値不整合。ハッシュコード'. $line['CheckSum'],
                    );
                    $mdlperr->saveNew($tmpDisplay);
                    $mdlprd->saveUpdate(array( 'ProcessedFlg' => '1' ), $payeasySeq);
                    continue;

                }

                //注文情報の特定
                $sql = ' SELECT o.OrderSeq, o.OrderId';
                $sql .= ' FROM T_Order AS o';
                $sql .= ' INNER JOIN T_OemClaimAccountInfo oca ON oca.OrderSeq = o.OrderSeq';
                $sql .= ' WHERE oca.CustomerNumber = :CustomerNumber';

                $prm = array( ':CustomerNumber' => $targetNumber );
                $row_order = $this->dbAdapter->query( $sql )->execute( $prm )->current();
                if ( empty($row_order) ) {
                    // (表示用(エラー)データ登録)
                    $tmpDisplay = array(
                            'OrderSeq'      => null,
                            'PaymentAmount' => $paymentAmount,
                            'ErrorCode'     => '2',
                            'ErrorMsg'      => '注文情報エラー[Seq:'. $payeasySeq. ']、注文が特定できない。収納機関受付番号 ：' . $targetNumber,
                    );
                    $mdlperr->saveNew($tmpDisplay);
                    $mdlprd->saveUpdate(array( 'ProcessedFlg' => '1' ), $payeasySeq);
                    continue;

                }

                $orderSeq = $row_order['OrderSeq'];
                $orderId = $row_order['OrderId'];
                $prm = array( ':OrderSeq' => $orderSeq );

                // 結果コードチェック
                if ( $line['ResultCd'] <> '0000000000000' ) {
                    $errCodeM = substr($line['ResultCd'], 3, 5);
                    $errCodeL = substr($line['ResultCd'], 8, 5);

                    // 中間コードチェック
                    $errMsg = null;
                    if (strpos($errCodeM, '2100') === 0 ) {
                        $errMsg .= '収納機関でのその他エラー';

                    } else if ($errCodeM <> '00000') {
                        $errMsg .= $mdlc->getMasterCaption(206, $errCodeM);

                    }

                    // 下位コードチェック
                    if ($errCodeL <> '00000') {
                        $errMsg .= (is_null($errMsg) ? '' : '：'). $mdlc->getMasterCaption(206, $errCodeL);

                    }

                    if ( is_null( $errMsg ) && (($errCodeM <> '00000') || ($errCodeL <> '00000'))) {
                        $errMsg = '予期せぬエラーが発生しています。';

                    }

                    if ( !is_null( $errMsg ) ) {
                        // (表示用(エラー)データ登録)
                        $tmpDisplay = array(
                                'OrderSeq'      => $orderSeq,
                                'PaymentAmount' => $paymentAmount,
                                'ErrorCode'     => '1',
                                'ErrorMsg'      => 'Payeasy連携エラー[Seq:'. $payeasySeq. ']。結果コード：'. $line['ResultCd']. '：'. $errMsg,
                        );
                        $mdlperr->saveNew($tmpDisplay);
                        $mdlprd->saveUpdate(array( 'ProcessedFlg' => '1' ), $payeasySeq);
                        continue;

                    }

                }

                // 処理しようとしている注文データが入金待ち、一部入金、入金済みクローズであるかのチェック
                $sql = 'SELECT COUNT(*) AS cnt FROM T_Order WHERE (DataStatus IN (51, 61) OR (DataStatus = 91 AND CloseReason = 1)) AND Cnl_Status = 0 AND OrderSeq = :OrderSeq';
                $checkCount = $this->dbAdapter->query( $sql )->execute( $prm )->current()['cnt'];

                if ( $checkCount == 0 ) {
                    // (注文有無)
                    $sql = 'SELECT * FROM T_Order WHERE OrderSeq = :OrderSeq';
                    $orderData = $this->dbAdapter->query( $sql )->execute( $prm )->current();

                    // (表示用データ登録)
                    $tmpDisplay = array(
                            'OrderSeq'      => $orderSeq,
                            'PaymentAmount' => $paymentAmount,
                            'ErrorCode'     => '2',
                            'ErrorMsg'      => '注文情報エラー[Seq:'. $payeasySeq. ']、入金待ちではありません',
                    );
                    $mdlperr->saveNew($tmpDisplay);
                    $mdlprd->saveUpdate(array( 'ProcessedFlg' => '1' ), $payeasySeq);
                    continue;

                }

                // 分割支払済み金額のチェック
                $sql = " SELECT ReceiptAmountTotal AS cnt FROM T_ClaimControl WHERE OrderSeq = :OrderSeq ";
                $receiptAmountTotal = (int)$this->dbAdapter->query( $sql )->execute( $prm )->current()['cnt'];
                if ( $receiptAmountTotal > 0 ) {
                    // (表示用データ登録)
                    $tmpDisplay = array(
                            'OrderSeq'      => $orderSeq,
                            'PaymentAmount' => $paymentAmount,
                            'ErrorCode'     => '3',
                            'ErrorMsg'      => '入金金額エラー[Seq:'. $payeasySeq. ']、分割支払済み',
                    );
                    $mdlperr->saveNew($tmpDisplay);
                    $mdlprd->saveUpdate(array( 'ProcessedFlg' => '1' ), $payeasySeq);
                    continue;

                }

                // 入金額チェック
                $amount_range = $this->getValidAmountRange( $orderSeq );
                // CB_B2C_DEV-62
                $sql = 'SELECT PayeasyFee FROM T_ClaimHistory WHERE OrderSeq = :OrderSeq ORDER BY UpdateDate DESC LIMIT 1';
                $payeasyfee= $this->dbAdapter->query( $sql )->execute( $prm )->current()['PayeasyFee'];
                if(empty($payeasyfee)){
                	$payeasyfee = 0;
                }
                if ( ($paymentAmount - $payeasyfee) < $amount_range['min'] ) {
                    // (表示用データ登録)
                    $tmpDisplay = array(
                            'OrderSeq'      => $orderSeq,
                            'PaymentAmount' => $paymentAmount,
                            'ErrorCode'     => '3',
                            'ErrorMsg'      => '入金金額エラー[Seq:'. $payeasySeq. ']、金額差異あり：'. sprintf('不足入金 (%s 未満)', f_nf($amount_range['min'], '#,##0')),
                    );
                    $mdlperr->saveNew($tmpDisplay);
                    $mdlprd->saveUpdate(array( 'ProcessedFlg' => '1' ), $payeasySeq);
                    continue;

                }
                if ( ($paymentAmount - $payeasyfee) > $amount_range['max'] ) {
                    // (表示用データ登録)
                    $tmpDisplay = array(
                            'OrderSeq'      => $orderSeq,
                            'PaymentAmount' => $paymentAmount,
                            'ErrorCode'     => '3',
                            'ErrorMsg'      => '入金金額エラー[Seq:'. $payeasySeq. ']、金額差異あり：'. sprintf('過剰入金 (%s 超)', f_nf($amount_range['max'], '#,##0')),
                    );
                    $mdlperr->saveNew($tmpDisplay);
                    $mdlprd->saveUpdate(array( 'ProcessedFlg' => '1' ), $payeasySeq);
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
                        ':pi_receipt_amount'   => (int)$paymentAmount - (int)$payeasyfee,
                        ':pi_order_seq'        => $orderSeq,
                        ':pi_receipt_date'     => $receiptDate,
                        ':pi_receipt_class'    => 8,
                        ':pi_branch_bank_id'   => null,
                        ':pi_receipt_agent_id' => null,
                        ':pi_deposit_date'     => $receiptDate,
                        ':pi_user_id'          => $userId,
                        ':pi_receipt_note'     => null
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

                //
                $mdlprd->saveUpdate(array( 'ProcessedFlg' => '1' ), $payeasySeq);

            }

            // コミット
            $this->dbAdapter->getDriver()->getConnection()->commit();

             return true;

        } catch(\Exception $e) {
            // ロールバック
            $this->dbAdapter->getDriver()->getConnection()->rollback();
            $errormessage = $e->getMessage();

$this->logger->info('importPayeasy.php err：'. $errormessage);
            return false;
        }
    }

    /**
     * 指定注文の有効入金額範囲を取得する。
     * 戻り値の連想配列はキー'min'に下限金額、'max'に上限金額を格納する。
     * 上限と下限の定義は以下の通り。
     *
     *   上限：最終請求の請求金額
     *   下限：
     *       初回～再3まで発行済み → 初回請求金額
     *       再4発行済み → 最も古い再請求の請求金額
     *       再5以上発行済み → 最も古い再3以上の再請求金額
     *
     * @param int $oseq 注文SEQ
     * @return array
     */
    public function getValidAmountRange($oseq) {
        $results = array(
            'min' => 0,     // 下限額
            'max' => 0      // 上限額
        );

        $hisarr = array();
        $hisTable = new TableClaimHistory($this->dbAdapter);
        $histories = $hisTable->findClaimHistory(array('OrderSeq' => $oseq), true);
        if ($histories->count() == 0) {
$this->logger->info(sprintf('[getValidAmountRange:%s] claim history not found', $oseq));

            // 請求履歴がない異常事態
            throw new \Exception(sprintf('claim history not found. oseq = %s', $oseq));

        }

        // 作業データの初期化
        $max_clm_ptn = 0;           // 最大請求パターン
        $max_clm_amount = 0;        // 最大請求金額

        $amount_cache = array();    // 請求金額キャッシュ
        $th_seqs = array(           // 下限金額パターン
            'A' => null,                // A：初回請求金額
            'B' => null,                // B：最も古い再請求の請求金額
            'C' => null                 // C：最も古い再3以降の再請求金額
        );

        // 下限用の履歴をパターンごとに抽出
        foreach($histories as $h) {
            $ptn = $h['ClaimPattern'];

            $hisarr[] = $h;

            if ($ptn < 1 || $ptn > 9) {
$this->logger->info(sprintf('[getValidAmountRange:%s] invalid claim pattan included. claim pattern = %s', $oseq, $ptn));

                // 未定義の請求パターンは無視
                continue;

            }

            // 最大請求パターンを更新
            if ($ptn > $max_clm_ptn) $max_clm_ptn = $ptn;

            // 請求金額をキャッシュ
            $amount = $amount_cache[$h['Seq']] = $hisTable->getClaimAmount($h['Seq']);

            // 最大請求金額を更新
            if ($amount > $max_clm_amount) $max_clm_amount = $amount;

            if ($ptn == 1 && !isset($th_seqs['A'])) {
                // 下限基準A：最初の初回請求
                $th_seqs['A'] = $h['Seq'];

            }

            if ($ptn > 1 && !isset($th_seqs['B'])) {
                // 下限基準B：最も古い再請求
                $th_seqs['B'] = $h['Seq'];

            }

            if ($ptn > 3 && !isset($th_seqs['C'])) {
                // 下限基準C：最も古い再3以上の再請求
                $th_seqs['C'] = $h['Seq'];

            }
        }

$this->logger->debug(sprintf('[getValidAmountRange:%s] max claim pattern = %s', $oseq, $max_clm_ptn));
$this->logger->debug(sprintf('[getValidAmountRange:%s] fixed thresholds = A:%s(%s), B:%s(%s), C:%s(%s)', $oseq,$th_seqs['A'], $amount_cache[$th_seqs['A']], $th_seqs['B'], $amount_cache[$th_seqs['B']], $th_seqs['C'], $amount_cache[$th_seqs['C']]));

        // 最大請求パターンに応じて下限基準額を確定
        if ($max_clm_ptn >= 7) {
            // 再5以上発行済み → 基準C
            $level = 'C';

        } else if ($max_clm_ptn == 6) {
            // 再4発行済み → 基準B
            $level = 'B';

        } else {
            // それ以外（＝再3以下） → 基準A
            $level = 'A';

        }

$this->logger->debug(sprintf('[getValidAmountRange:%s] lower amount pattern = %s(%s), amount = %s', $oseq, $level, $th_seqs[$level], $amount_cache[$th_seqs[$level]]));

        $results['min'] = $amount_cache[$th_seqs[$level]];

        // 上限基準額確定
        // ※上限基準額が下限基準額を下回っても特になにもしない → このパターンの注文は入金保留となる
        $hisarrCount = 0;
        if (!empty($hisarr)) {
            $hisarrCount = count($hisarr);

        }
        $max_seq = $hisarr[$hisarrCount - 1]['Seq'];
        $results['max'] = $amount_cache[$max_seq];

$this->logger->debug(sprintf('[getValidAmountRange:%s] upper amount seqs = %s, amount = %s', $oseq, $max_seq, $results['max']));
$this->logger->debug(sprintf('[getValidAmountRange:%s] fixed range = %s to %s', $oseq, $results['min'], $results['max']));

        return $results;
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

            //  1 プロトコルバージョン
        $key = 'p_ver';
        if (!((mb_strlen($data[$key]) == 4) && preg_match('/^\d{4}$/', $data[$key]))) {
            $errors[] = $key;
        }

        //  2 データ作成日
        $key = 'stdate';
        if (!((mb_strlen($data[$key]) == 8) && preg_match('/^\d{8}$/', $data[$key]))) {
            $errors[] = $key;
        }

        //  3 加盟店取引番号
        $key = 'stran';
        if (!((mb_strlen($data[$key]) == 6) && preg_match('/^\d{6}$/', $data[$key]))) {
            $errors[] = $key;
        }

        //  4 収納機関コード
        $key = 'bkcode';
        if (!((mb_strlen($data[$key]) == 4) && preg_match('/^[a-zA-Z0-9]{4}$/', $data[$key]))) {
            $errors[] = $key;
        }

        //  5 加盟店コード
        $key = 'shopid';
        if (!((mb_strlen($data[$key]) == 6) && preg_match('/^\d{6}$/', $data[$key]))) {
            $errors[] = $key;
        }

        //  6 加盟店サブコード
        $key = 'cshopid';
        if (!((mb_strlen($data[$key]) == 5) && preg_match('/^\d{5}$/', $data[$key]))) {
            $errors[] = $key;
        }

        //  7 取引金額
        $key = 'amount';
        if (!((mb_strlen($data[$key]) <= 10) && preg_match('/^\d{1,10}$/', $data[$key]))) {
            $errors[] = $key;
        }

        //  8 取引番号
        $key = 'mbtran';
        if (!((mb_strlen($data[$key]) == 25) && preg_match('/^[a-zA-Z0-9]{25}$/', $data[$key]))) {
            $errors[] = $key;
        }

        //  9 収納機関受付番号
        $key = 'bktrans';
        if (mb_strlen($data[$key]) > 0) {
            if (!((mb_strlen($data[$key]) <= 24) && preg_match('/^([a-zA-Z0-9]{1,24})?$/', $data[$key]))) {
                $errors[] = $key;
            }
        }

        // 10 消込識別情報
        $key = 'tranid';
        if (mb_strlen($data[$key]) > 0) {
            if ( (mb_strlen($data[$key]) > 110) ) {
                $errors[] = $key;
            }
        }

        // 11 処理日付
        $key = 'ddate';
        if (mb_strlen($data[$key]) > 0) {
            if (!((mb_strlen($data[$key]) == 8) && preg_match('/^(\d{8})?$/', $data[$key]))) {
                $errors[] = $key;
            }
        }

        // 12 振込日(入金日)
        $key = 'tdate';
        if (mb_strlen($data[$key]) > 0) {
            if (!((mb_strlen($data[$key]) == 8) && preg_match('/^([a-zA-Z0-9]{8})?$/', $data[$key]))) {
                $errors[] = $key;
            }
        }

        // 13 結果コード
        $key = 'rsltcd';
        if (!((mb_strlen($data[$key]) == 13) && preg_match('/^[a-zA-Z0-9]{13}$/', $data[$key]))) {
            $errors[] = $key;
        }

        // 14 ハッシュ値
        $key = 'rchksum';
        if (mb_strlen($data[$key]) > 0) {
            if (!((mb_strlen($data[$key]) == 32) && preg_match('/^([a-zA-Z0-9]{32})?$/', $data[$key]))) {
                $errors[] = $key;
            }
        }

		return $errors;
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
