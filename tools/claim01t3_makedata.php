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
use models\Table\TableClaimBatchControl;
use models\Table\TableCode;
use models\Table\TableClaimThreadManage;
use models\Table\TableOrderAddInfo;
use models\Logic\LogicTemplate;
use models\Table\TableSiteFreeItems;
use models\Table\TableClaimPrintPattern;
use models\Table\TablePaymentCheck;
use models\Table\TableClaimPrintCheck;
use models\Logic\LogicPayeasy;
use models\Table\TableClaimError;
use models\Logic\LogicClaimPrint;
use models\Table\TableSiteSbpsPayment;

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

            $this->logger->info('claim01t3_makedata.php start');

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

            // 主処理
            if ($this->csvoutput()) {
                // (請求バッチ管理(T_ClaimBatchControl)更新)
                $this->updClaimBatchControl();

                // (東洋紙業のSFTPサーバーへ送信処理スレッドスタート(非同期にて))
                if (\Coral\Base\BaseProcessInfo::isWin()) {
                    $fp = popen('start php ' . __DIR__ . '/claim02t_senddata.php', 'r');
                    pclose($fp);
                }
                else {
                    exec('php ' . __DIR__ . '/claim02t_senddata.php > /dev/null &');
                }
            }

            $this->logger->info('claim01t3_makedata.php end');
            $this->logger->info('claim01_makedata.php end');
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
     * CSV出力処理
     */
    protected function csvoutput()
    {
        // 初回請求データ抽出の基本SQL取得
        $searchsql = TableClaimThreadManage::getBaseQueryClaim();

        // SQL実行
        $ri = $this->dbAdapter->query($searchsql . " ORDER BY c.PostalCode, o.OrderId ")->execute(null);
        $datas = ResultInterfaceToArray($ri);

        $list = array();
        foreach ($datas as $data) {
            $work = $this->edit($data['OrderSeq']);
            if ($work !== false) {
                $list[] = $work;
            }
        }

        // SQL実行
        $sql  = " SELECT DISTINCT ch.OrderSeq ";
        $sql .= " FROM   T_ClaimHistory ch ";
        $sql .= "        INNER JOIN T_Order o ON (o.OrderSeq = ch.OrderSeq) ";
        $sql .= "        INNER JOIN T_Customer cus ON (cus.OrderSeq = ch.OrderSeq) ";
        $sql .= " WHERE  1 = 1 ";
        $sql .= " AND    ch.PrintedFlg = 0 ";
        $sql .= " AND    ch.PrintedStatus IN (1, 2) ";
        $sql .= " AND    ch.ValidFlg = 1 ";
        $sql .= " AND    (o.Cnl_Status IS NULL OR o.Cnl_Status = 0) ";
        $sql .= " AND    (o.LetterClaimStopFlg IS NULL OR o.LetterClaimStopFlg = 0) ";
        $sql .= " AND    ch.ClaimPattern in (9, 8, 7, 6, 4, 2) ";
        $sql .= " AND    ch.ClaimAmount > 0 ";
        $sql .= " ORDER BY cus.PostalCode, o.OrderId ";
        $ri = $this->dbAdapter->query($sql)->execute(null);
        $datas = ResultInterfaceToArray($ri);
        foreach ($datas as $data) {
            $work = $this->edit($data['OrderSeq']);
            if ($work !== false) {
                $list[] = $work;
            }
        }


        $FilePath = __DIR__ . '/../data/sftp/';

        if (file_exists($FilePath . 'Claim7.zip')) {
            unlink($FilePath . 'Claim7.zip');
        }
        if (file_exists($FilePath . 'Claim6.zip')) {
            rename($FilePath . 'Claim6.zip', $FilePath . 'Claim7.zip');
        }
        if (file_exists($FilePath . 'Claim5.zip')) {
            rename($FilePath . 'Claim5.zip', $FilePath . 'Claim6.zip');
        }
        if (file_exists($FilePath . 'Claim4.zip')) {
            rename($FilePath . 'Claim4.zip', $FilePath . 'Claim5.zip');
        }
        if (file_exists($FilePath . 'Claim3.zip')) {
            rename($FilePath . 'Claim3.zip', $FilePath . 'Claim4.zip');
        }
        if (file_exists($FilePath . 'Claim2.zip')) {
            rename($FilePath . 'Claim2.zip', $FilePath . 'Claim3.zip');
        }
        if (file_exists($FilePath . 'Claim1.zip')) {
            rename($FilePath . 'Claim1.zip', $FilePath . 'Claim2.zip');
        }

        // ZIPファイル作成
        $zip = new \ZipArchive();

        // 出力時刻
        $formatNowStr = date('YmdHis');

        // 出力ファイル名
        $outFileName= ('Claim1.zip');

        // TEMP領域作成
        $FilePathname = __DIR__ . '/../data/sftp/' . $outFileName;

        // ZIPファイルオープン
        $zip->open( $FilePathname, \ZipArchive::CREATE | \ZipArchive::OVERWRITE );

        $filename = 'B2C_Claim_'.date('YmdHis').'.csv';
        $tmpFileName = $FilePath . $filename;

        $logicTemplate = new LogicTemplate( $this->dbAdapter );
        $result = $logicTemplate->convertArraytoFile( $list, $tmpFileName, 'CKI04050_1', 0, 0, 0 );
        if( $result == false ) {
            throw new \Exception( $logicTemplate->getErrorMessage() );
        }

        $pathcutfilename = str_replace( $FilePath, '', $filename );
        $addFilePath = file_get_contents( $tmpFileName );
        $zip->addFromString($pathcutfilename , $addFilePath );

        // ZIPファイルクローズ
        $zip->close();

        // TEMP領域削除
        unlink( $tmpFileName );

        return true;
    }

    /**
     * 請求バッチ管理(T_ClaimBatchControl)のMakeFlgを、[1:完了]に更新する
     */
    protected function updClaimBatchControl() {

        // 請求バッチ管理テーブル 最大Seq取得
        $sql = "SELECT MAX(Seq) AS Seq FROM T_ClaimBatchControl";
        $maxseq = $this->dbAdapter->query($sql)->execute()->current()['Seq'];

        $mdlcb = new TableClaimBatchControl($this->dbAdapter);

        // データ更新
        $data = array(
            'MakeFlg' => 1,
        );
        $mdlcb->saveUpdate($data, $maxseq);
    }

    protected function edit($orderSeq) {

        // ユーザーIDの取得
        $obj = new TableUser($this->dbAdapter);
        $userId = $obj->getUserId(TableUser::USERCLASS_SYSTEM, TableUser::SEQ_BATCH_USER);

        $mdlCode = new TableCode($this->dbAdapter);
        $logic = new LogicClaimPrint($this->dbAdapter);

        //---------------------------------------
        $prm = array(':OrderSeq' => $orderSeq);

        // 主データ取得
        $sql  = ' SELECT IFNULL(o.OemId, 0) AS OemId ';
        $sql .= ' ,      ch.ClaimPattern ';
        $sql .= ' ,      c.PostalCode ';
        $sql .= ' ,      c.UnitingAddress ';
        $sql .= ' ,      c.NameKj ';
        $sql .= ' ,      o.OrderId ';
        $sql .= ' ,      (CASE WHEN e.ClaimOrderDateFormat = 1 THEN DATE_FORMAT(o.ReceiptOrderDate, \'%Y/%m\') ';
        $sql .= '              ELSE DATE_FORMAT(o.ReceiptOrderDate, \'%Y/%m/%d\') ';
        $sql .= '         END) AS ReceiptOrderDate ';
        $sql .= ' ,      s.ClaimOriginalFormat ';
        $sql .= ' ,      s.SiteNameKj ';
        $sql .= ' ,      s.Url ';
        $sql .= ' ,      e.ContactPhoneNumber AS SiteContactPhoneNumber ';
        $sql .= ' ,      ch.ClaimAmount ';
        $sql .= ' ,      IFNULL(cc.Clm_Count, 1) AS Clm_Count ';
        $sql .= ' ,      DATE_FORMAT(ch.LimitDate, \'%Y/%m/%d\') AS LimitDate ';
        $sql .= ' ,      (CASE WHEN LENGTH(ca.Cv_BarcodeData) > 43 THEN SUBSTRING(ca.Cv_BarcodeData, 1, 43) ';
        $sql .= '              ELSE ca.Cv_BarcodeData ';
        $sql .= '         END) AS Cv_BarcodeData2 ';
        $sql .= ' ,      ch.ClaimFee ';
        $sql .= ' ,      ch.DamageInterestAmount ';
        $sql .= ' ,      IFNULL(cc.ReceiptAmountTotal, 0) * -1 AS ReceiptAmountTotal ';
        $sql .= ' ,      (CASE WHEN e.PrintEntOrderIdOnClaimFlg = 0 THEN \'\' ';
        $sql .= '              ELSE o.Ent_OrderId ';
        $sql .= '         END) AS Ent_OrderId ';
        $sql .= ' ,      ca.TaxAmount ';
        $sql .= ' ,      ca.Cv_ReceiptAgentName ';
        $sql .= ' ,      ca.Cv_SubscriberName ';
        $sql .= ' ,      ca.Cv_BarcodeData ';
        $sql .= ' ,      ca.Cv_BarcodeString1 ';
        $sql .= ' ,      ca.Cv_BarcodeString2 ';
        $sql .= ' ,      ca.Bk_BankCode ';
        $sql .= ' ,      ca.Bk_BranchCode ';
        $sql .= ' ,      ca.Bk_BankName ';
        $sql .= ' ,      ca.Bk_BranchName ';
        $sql .= ' ,      ca.Bk_DepositClass ';
        $sql .= ' ,      ca.Bk_AccountNumber ';
        $sql .= ' ,      ca.Bk_AccountHolder ';
        $sql .= ' ,      ca.Bk_AccountHolderKn ';
        $sql .= ' ,      ya.AccountNumber ';
        $sql .= ' ,      ya.SubscriberName ';
        $sql .= ' ,      ca.Yu_SubscriberName ';
        $sql .= ' ,      ca.Yu_AccountNumber ';
        $sql .= ' ,      ca.Yu_ChargeClass ';
        $sql .= ' ,      ca.Yu_MtOcrCode1 ';
        $sql .= ' ,      ca.Yu_MtOcrCode2 ';
        $sql .= ' ,      s.PaymentAfterArrivalFlg ';
        $sql .= ' ,      s.ClaimMypagePrint ';
        $sql .= ' ,      ca.SubUseAmount_1 ';
        $sql .= ' ,      ca.SubTaxAmount_1 ';
        $sql .= ' ,      ca.SubUseAmount_2 ';
        $sql .= ' ,      ca.SubTaxAmount_2 ';
        $sql .= ' ,      DATE_FORMAT(ch.ClaimDate, \'%Y/%m/%d\') AS ClaimDate ';
        $sql .= ' ,      c.CorporateName ';
        $sql .= ' ,      c.DivisionName ';
        $sql .= ' ,      c.CpNameKj ';
        $sql .= ' ,      e.ClaimEntCustIdDisplayName ';
        $sql .= ' ,      c.EntCustId ';
        $sql .= ' ,      e.ForceCancelClaimPattern ';
        $sql .= ' ,      e.ForceCancelDatePrintFlg ';
        $sql .= ' ,      o.Ent_Note ';
        $sql .= ' ,      e.EnterpriseId ';
        $sql .= ' ,      s.SiteId ';
        $sql .= ' ,      e.BillingAgentFlg ';
        $sql .= ' ,      e.EnterpriseNameKj ';
        $sql .= ' ,      e.PostalCode AS EntPostalCode ';
        $sql .= ' ,      e.PrefectureName ';
        $sql .= ' ,      e.City ';
        $sql .= ' ,      e.Town ';
        $sql .= ' ,      e.Building ';
        $sql .= ' ,      e.ContactPhoneNumber AS EntContactPhoneNumber ';
        $sql .= ' ,      oem.OemNameKn ';
        $sql .= ' ,      ca.CustomerNumber ';
        $sql .= ' ,      ca.ConfirmNumber ';
        $sql .= ' ,      s.FirstClaimLayoutMode ';

        $sql .= ' ,      e.AppFormIssueCond ';
        $sql .= ' ,      e.CreditTransferFlg ';
        $sql .= ' ,      e.ClaimIssueStopFlg ';
        $sql .= ' ,      cc.ClaimId ';
        $sql .= ' ,      ch.PrintedFlg ';
        $sql .= ' ,      ao.CreditTransferRequestFlg ';
        $sql .= ' FROM   T_Order o INNER JOIN ';
        $sql .= '        T_Customer c ON ( o.OrderSeq = c.OrderSeq ) INNER JOIN ';
        $sql .= '        T_Enterprise e ON ( o.EnterpriseId = e.EnterpriseId ) INNER JOIN ';
        $sql .= '        T_Site s ON ( o.SiteId = s.SiteId ) LEFT OUTER JOIN ';
        $sql .= '        T_ClaimControl cc ON o.OrderSeq = cc.OrderSeq LEFT OUTER JOIN ';
        $sql .= '        T_ClaimHistory ch ON ( o.OrderSeq = ch.OrderSeq AND ch.PrintedFlg = 0 AND ch.ValidFlg = 1 ) LEFT OUTER JOIN ';
        $sql .= '        T_OemClaimAccountInfo ca ON( ch.Seq = ca.ClaimHistorySeq AND ca.Status = 1 ) INNER JOIN ';
        $sql .= '        T_OemYuchoAccount ya ON( IFNULL(o.OemId, 0) = ya.OemId ) LEFT OUTER JOIN ';
        $sql .= '        T_Oem oem ON( o.OemId = oem.OemId ) INNER JOIN ';
        $sql .= '        AT_Order ao ON (o.OrderSeq=ao.OrderSeq) ';
        $sql .= ' WHERE  o.OrderSeq = :OrderSeq ';
        $sql .= '   AND  EXISTS (SELECT * FROM T_Order t WHERE t.P_OrderSeq = o.OrderSeq AND t.Cnl_Status = 0) ';
        $sql .= '   AND  IFNULL(cc.ClaimedBalance, 1) > 0 ';

        $data = $this->dbAdapter->query( $sql )->execute( $prm )->current();
        if (!$data) { return false; }

        // 口振は処理対象外
        if (is_null($data['ClaimPattern'])) {
            $this->logger->info('口座振替のため処理対象外 OrderSeq='.$orderSeq);
            return false;
        }

        // サイトマスタのオリジナル帳票操作
        if ($data['ClaimOriginalFormat'] == 1) {
            $data['SiteNameKj'] = '';
            $data['Url'] = '';
            $data['SiteContactPhoneNumber'] = '';
        }

        // 再６と再７請求書操作
        if ($data['ClaimPattern'] >= 8) {
            $data['Url'] = '';
        }

        // 請求金額が0円以下の場合は出力を行わない(特に、T_ClaimHistoryが取得出来ない場合を想定)
        if ( $data['ClaimAmount'] <= 0 ) {
            // 口座振替利用しない、または、口座振替利用する＆申込用紙発行条件≠2：請求金額0円時の場合はエラー
            if (($data['CreditTransferFlg'] == 0) || ( $data['AppFormIssueCond'] != 2 )) {
                $this->logger->info('請求金額が0円以下 OrderSeq='.$orderSeq);
                return false;
            }
        }

        // 請求金額が30万円以上だった場合
        if( $data['ClaimAmount'] >= 300000 ) {
            $data['Cv_BarcodeData'] = $data['Cv_BarcodeData2'] = '収納代行の規約によりコンビニエンスストアで30万円以上のお支払はできません';
            $data['Cv_BarcodeString1'] = '';
            $data['Cv_BarcodeString2'] = '';
        }

        // 初回はブランク
        if ($data['ClaimPattern'] == 1) {
            $data['ClaimFee'] = '';
        }

        //
        $data['PrintIssueCountCd'] = $logic->createPrintIssueCountCdReal($orderSeq, $data['ClaimPattern']);

        // 口座振替の初回請求時の設定にて、WEB申込みの注文は処理対象外
        if (($data['PrintIssueCountCd'] == '00') && ($data['ClaimPattern'] == 1) && ($data['ClaimAmount'] == 0) && ($data['CreditTransferRequestFlg'] == 1)) {
            $this->logger->info('口座振替の初回請求時のWEB申込みのため処理対象外 OrderSeq='.$orderSeq);
            return false;
        }

        // 請求金額
        if (intval($data['PrintIssueCountCd']) > 1) {
            $data['ClaimAmount'] = nvl( $data['ClaimAmount'], 0 ) + nvl( $data['ReceiptAmountTotal'], 0 );
        }


        $mdlCpp = new TableClaimPrintPattern($this->dbAdapter);
        $stmtCpp = $mdlCpp->find($data['EnterpriseId'], $data['SiteId'], $data['PrintIssueCountCd']);
        $dataCpp = $stmtCpp->current();
        $data['PrintPatternCd'] = $dataCpp['PrintPatternCd'];
        $data['PrintFormCd'] = $dataCpp['PrintFormCd'];
        $data['PrintTypeCd'] = $dataCpp['PrintTypeCd'];
        $data['EnclosedSpecCd'] = $dataCpp['EnclosedSpecCd'];
        $data['PrintIssueCd'] = $dataCpp['PrintIssueCd'];
        $data['SpPaymentCd'] = $dataCpp['SpPaymentCd'];
        $data['AdCd'] = $dataCpp['AdCd'];
        $data['EnclosedAdCd'] = $dataCpp['EnclosedAdCd'];

        $mdlCpc = new TableClaimPrintCheck($this->dbAdapter);
        $stmtCpc = $mdlCpc->find($data['PrintFormCd'], $data['PrintTypeCd'], $data['PrintIssueCd'], $data['PrintIssueCountCd']);
        $dataCpc = $stmtCpc->current();
        $data['ClaimPrintCheckSeq'] = $dataCpc['ClaimPrintCheckSeq'];
        $data['ClaimPrintCheckName'] = $dataCpc['ClaimPrintCheckName'];

        $check = $logic->paymentCheck($data['PrintPatternCd'], $data['SpPaymentCd']);
        $data['PaymentCheckSeq'] = substr('00000'.$check, -5);
        $mdlPc = new TablePaymentCheck($this->dbAdapter);
        $data['ImageName'] = $mdlPc->primary($check)->current()['ImageName'];

        // 未使用
        $data['DamageInterestAmount'] = '';


        //---------------------------------------
        $maxItemRow = 19;
        if ($data['OemId'] > 0) {
            // 出力する明細数制御
            if ( $data['FirstClaimLayoutMode'] == 1 && $this->isItemIncOem($data['OemId'])  ) {
                //封書かつ明細数増加対象OEMの場合 明細３０
                $maxItemRow = 30;
            }
        }

        // 注文商品
        $sql  = ' SELECT itm.ItemNameKj ';
        $sql .= ' ,      itm.ItemNum ';
        $sql .= ' ,      itm.UnitPrice ';
        $sql .= ' ,      itm.SumMoney ';
        $sql .= ' ,      e.DispDecimalPoint ';  /* 表示用小数点桁数 */
        $sql .= ' FROM   T_Order o INNER JOIN ';
        $sql .= '        T_OrderItems itm ON ( o.OrderSeq = itm.OrderSeq ) ';
        $sql .= '        INNER JOIN T_Enterprise e ON ( e.EnterpriseId = o.EnterpriseId ) ';
        $sql .= ' WHERE  itm.DataClass = 1 AND ';
        $sql .= '        o.P_OrderSeq = :OrderSeq ';
        $sql .= ' AND    o.Cnl_Status = 0 ';
        $sql .= ' AND    itm.ValidFlg = 1 ';
        $sql .= ' ORDER BY OrderItemId ';
        $items = ResultInterfaceToArray( $this->dbAdapter->query( $sql )->execute( $prm ) );
        $item_cnt = 0;
        for( $j = 1; $j <= 30; $j++ ) {
            $data['ItemNameKj_' . $j] = isset( $items[$j - 1]['ItemNameKj'] ) ? $items[$j - 1]['ItemNameKj'] : '';
            $data['ItemNum_' . $j] = isset( $items[$j - 1]['ItemNum'] ) ? $items[$j - 1]['ItemNum'] : '';
            if ($data['ItemNum_' . $j] != '') {
                // [表示用小数点桁数]考慮
                $data['ItemNum_' . $j] = number_format($data['ItemNum_' . $j], $items[$j - 1]['DispDecimalPoint'], '.', '');
            }
            $data['UnitPrice_' . $j] = isset( $items[$j - 1]['UnitPrice'] ) ? $items[$j - 1]['UnitPrice'] : '';

            if ($data['UnitPrice_' . $j] != '') {
                $item_cnt++;
            }

            if ($data['UnitPrice_' . $j] == 0) {
                $data['ItemNum_' . $j] = '';
                $data['UnitPrice_' . $j] = '';
            }

            // 空情報上書き
            if ($j > $maxItemRow) {
                $data['ItemNameKj_' . $j] = '';
                $data['ItemNum_' . $j] = '';
                $data['UnitPrice_' . $j] = '';
            }
        }

        // 送料
        $sql  = ' SELECT SUM( itm.SumMoney ) AS CarriageFee ';
        $sql .= ' FROM   T_Order o INNER JOIN ';
        $sql .= '        T_OrderItems itm ON ( o.OrderSeq = itm.OrderSeq ) ';
        $sql .= ' WHERE  itm.DataClass = 2 AND ';
        $sql .= '        o.P_OrderSeq = :OrderSeq ';
        $sql .= ' AND    o.Cnl_Status = 0 ';
        $data = array_merge( $data, $this->dbAdapter->query( $sql )->execute( $prm )->current() );

        // 決済手数料
        $sql  = ' SELECT SUM( itm.SumMoney ) AS ChargeFee ';
        $sql .= ' FROM   T_Order o INNER JOIN ';
        $sql .= '        T_OrderItems itm ON ( o.OrderSeq = itm.OrderSeq ) ';
        $sql .= ' WHERE  itm.DataClass = 3 AND ';
        $sql .= '        o.P_OrderSeq = :OrderSeq ';
        $sql .= ' AND    o.Cnl_Status = 0 ';
        $data = array_merge( $data, $this->dbAdapter->query( $sql )->execute( $prm )->current() );

        // 消費税(外税額レコード確認)
        $sql  = ' SELECT COUNT(itm.OrderItemId) AS cnt ';
        $sql .= ' FROM   T_Order o INNER JOIN ';
        $sql .= '        T_OrderItems itm ON ( o.OrderSeq = itm.OrderSeq ) ';
        $sql .= ' WHERE  itm.DataClass = 4 AND ';
        $sql .= '        o.P_OrderSeq = :OrderSeq ';
        $sql .= ' AND    o.Cnl_Status = 0 ';
        $data['TaxClass'] = ((int)$this->dbAdapter->query( $sql )->execute( $prm )->current()['cnt'] > 0) ? 1 : 0;

        foreach ($items as $row) {
            $data['TotalItemPrice'] += $row['SumMoney'];
        }

        // 入金済額
        if ($data['ReceiptAmountTotal'] == 0) {
            $data['ReceiptAmountTotal'] = '';
        }

        // 固定ゆうちょ銀行　記号・番号
        $data_code = $mdlCode->find(217, $data['OemId'])->current();
        $field = 'KeyContent';
        if (intval($data['PrintIssueCountCd']) > 0) {
            $field = 'Class'.intval($data['PrintIssueCountCd']);
        }
        $data['AccountNumber'] = $data_code[$field];

        // 固定ゆうちょ銀行　口座名義
        $data_code = $mdlCode->find(218, $data['OemId'])->current();
        $field = 'KeyContent';
        if (intval($data['PrintIssueCountCd']) > 0) {
            $field = 'Class'.intval($data['PrintIssueCountCd']);
        }
        $data['SubscriberName'] = $data_code[$field];

        // マイページURL
        $data_code = $mdlCode->find(105, $data['OemId'])->current();
        $data['MyPageUrl'] = $data_code['KeyContent'];
        if ($data['PaymentAfterArrivalFlg'] == 1){
            $data['MyPageUrl'] .= $data_code['Class2'];
        }

        // マイページログインパスワード
        $row_mypageorder = $this->dbAdapter->query(" SELECT Token FROM T_MypageOrder WHERE OrderSeq = :OrderSeq ORDER BY Seq DESC LIMIT 1 "
        )->execute(array(':OrderSeq' => $orderSeq))->current();
        $data['MypageToken'] = ($row_mypageorder) ? $row_mypageorder['Token'] : '';

        // マイページURL、マイページログインパスワードの出力有無は最終的にサイトマスタの設定に依存
        if ($data['ClaimMypagePrint'] == 0) {
            $data['MyPageUrl'] = '';
            $data['MypageToken'] = '';
        }

        // 商品合計数
        $data['ItemsCount'] = $item_cnt;

        // 法人名が入力されており、担当者名がブランクの場合は、「担当者名」へ購入者名を出力する
        if ((nvl($data['CorporateName'],'') != '') && nvl($data['CpNameKj'],'') == '') {
            $data['CpNameKj'] = $data['NameKj'];
        }
        // 法人名が入力されている場合、「顧客氏名」は出力しない
        if ((nvl($data['CorporateName'],'') != '')) {
            $data['NameKj'] = '';
        }

        // クレジット決済利用期限日
        if ($data['PaymentAfterArrivalFlg'] == 1) {
            // クレジット決済利用期限日 = 請求履歴.請求日 + コードマスタ.クレジット決済利用期間
            $sql = "SELECT MIN(ClaimDate) AS MinClaimDate FROM T_ClaimHistory WHERE OrderSeq = :OrderSeq";
            $minClaimDate = $this->dbAdapter->query($sql)->execute(array(':OrderSeq'=>$orderSeq))->current()['MinClaimDate'];

            $mdlSitePayment = new TableSiteSbpsPayment($this->dbAdapter);

            $data['CreditSettlementDecisionDate'] = "";
            if (!empty($minClaimDate)) {
                $maxNumUseDay = $mdlSitePayment->getMaxNumUseDay($data['SiteId'], $minClaimDate);
                if (!empty($maxNumUseDay)) {
                    $creditSettlementDays = $maxNumUseDay;
                    //請求履歴.請求日 + 届いてから決済のサイト別の支払可能種類.Max(利用期間)
                    $data['CreditSettlementDecisionDate'] = date('Y/m/d', strtotime($minClaimDate. '+'. $creditSettlementDays. ' days') );
                }
            }
        }

        // 加盟店顧客番号
        if (nvl($data['ClaimEntCustIdDisplayName'],'') == '') {
            $data['EntCustId'] = '';
        } else {
            if (nvl($data['EntCustId'],'') == '') {
                $data['EntCustId'] = '*****';
            }
        }

        // 強制解約日出力
        $cancelNoticePrint = false;
        if (($data['ForceCancelDatePrintFlg'] == 1) && ($data['ForceCancelClaimPattern'] == $data['ClaimPattern'])) {
            if (preg_match("/^強制解約日=[0-9]{4}\/[0-9]{2}\/[0-9]{2}$/u", $data['Ent_Note'])) {
                $data['Ent_Note'] = str_replace("強制解約日=", "", $data['Ent_Note']);
                $cancelNoticePrint = true;
            } else {
                $data['Ent_Note'] = '';
            }
        } else {
            $data['Ent_Note'] = '';
        }

        // 事業者．請求代行プラン＝利用する
        if ($data['BillingAgentFlg'] == 1) {
            // 自由項目の取得
            $mdloai = new TableOrderAddInfo($this->dbAdapter);
            $mdlsfi = new TableSiteFreeItems($this->dbAdapter);
            $OdrAddInfData = $mdloai->find($orderSeq)->current();
            $freeColumns = $mdlsfi->find($data['SiteId'])->current();

            // 設定状況の確認
            for( $k = 1; $k <= 20; $k++ ){
                // 注文時に指定されている場合
                if ( isset( $OdrAddInfData['Free' . $k] ) && strlen( $OdrAddInfData['Free' . $k] ) > 0 ) {
                    // 注文時の設定を使用する
                    $freeColumns = $OdrAddInfData;
                    break;
                }
            }

            // Csv項目用に詰め替える
            for ( $k = 1; $k <= 20; $k++ ) {
                $data['Free' . $k] = $freeColumns['Free' . $k];
            }

            $data['EntNameKj']     = $data['EnterpriseNameKj'];
            $postalcode = str_replace('-', '', $data['EntPostalCode']);
            $data['EntPostalCode'] = substr($postalcode,0,3) . '-' . substr($postalcode,3,4);
            $data['EntAddress1']   = $data['PrefectureName'] . $data['City'] . $data['Town'];
            $data['EntAddress2']   = $data['Building'];
        } else {
            // Csv項目用に詰め替える
            for ( $k = 1; $k <= 20; $k++ ) {
                $data['FreeColumn' . $k] = '';
            }
            $data['EntNameKj']     = $this->getEntNameKj($data['OemId'], $data['PrintIssueCountCd']);
            $data['EntPostalCode'] = '';
            $data['EntAddress1']   = '';
            $data['EntAddress2']   = '';
            $data['EntContactPhoneNumber'] = '';
        }

        // 発行元電話番号
        if ($data['ClaimOriginalFormat'] == 1) {
            $data['PublicPhoneNumber'] = '';
        } else {
            $data['PublicPhoneNumber'] = $this->getPublicPhoneNumber($data['OemId'], $data['PrintIssueCountCd']);
        }

        // インボイス用
        $data['InvoicePhoneNumber'] = '';
        $data['InvoiceContactPhoneNumber'] = '';

        // ペイジーロジック
        $logicpayeasy = new LogicPayeasy($this->dbAdapter, $this->logger);
        if ($logicpayeasy->isPayeasyOem($data['OemId'])) {
            //ペイジー収納機関番号取得
            $data['PayeasyNote'] = $mdlCode->find(LogicPayeasy::PAYEASY_CODEID, LogicPayeasy::BK_NUMBER_KEYCODE)->current()['Note'];
        } else {
            $data['CustomerNumber'] = '';
            $data['ConfirmNumber'] = '';
            $data['PayeasyNote'] = '';
        }

        // 請求書CSV対応
        // ・二重引用符全角の二重引用符に置換
        // ・改行記号（CRFL、CR、LF）は半角スペースに置換
        // ・フォームフィード文字および垂直タブ文字（ASCII：0x0B）は除去
        // ・タブ文字は半角スペースに置換
        $search  = array('"'    , "\r\n"   , "\r"  , "\n"  , "\f"  , "\v" , "\t");
        $replace = array('”'   , ' '      , ' '   , ' '   , ''    , ''   , ' ');
        $data = str_replace($search, $replace, $data);

        // 文字列カット
        $data['PostalCode'] = $this->mb_strimwidth2($data['PostalCode'],0,8);
        $data['UnitingAddress'] = $this->mb_strimwidth2($data['UnitingAddress'],0,200);
        $data['NameKj'] = $this->mb_strimwidth2($data['NameKj'],0,200);
        $data['SiteNameKj'] = $this->mb_strimwidth2($data['SiteNameKj'],0,100);
        $data['Url'] = $this->mb_strimwidth2($data['Url'],0,100);
        $data['SiteContactPhoneNumber'] = $this->mb_strimwidth2($data['SiteContactPhoneNumber'],0,15);
        $data['ItemNameKj_1'] = $this->mb_strimwidth2($data['ItemNameKj_1'],0,60);
        $data['ItemNameKj_2'] = $this->mb_strimwidth2($data['ItemNameKj_2'],0,60);
        $data['ItemNameKj_3'] = $this->mb_strimwidth2($data['ItemNameKj_3'],0,60);
        $data['ItemNameKj_4'] = $this->mb_strimwidth2($data['ItemNameKj_4'],0,60);
        $data['ItemNameKj_5'] = $this->mb_strimwidth2($data['ItemNameKj_5'],0,60);
        $data['ItemNameKj_6'] = $this->mb_strimwidth2($data['ItemNameKj_6'],0,60);
        $data['ItemNameKj_7'] = $this->mb_strimwidth2($data['ItemNameKj_7'],0,60);
        $data['ItemNameKj_8'] = $this->mb_strimwidth2($data['ItemNameKj_8'],0,60);
        $data['ItemNameKj_9'] = $this->mb_strimwidth2($data['ItemNameKj_9'],0,60);
        $data['ItemNameKj_10'] = $this->mb_strimwidth2($data['ItemNameKj_10'],0,60);
        $data['ItemNameKj_11'] = $this->mb_strimwidth2($data['ItemNameKj_11'],0,60);
        $data['ItemNameKj_12'] = $this->mb_strimwidth2($data['ItemNameKj_12'],0,60);
        $data['ItemNameKj_13'] = $this->mb_strimwidth2($data['ItemNameKj_13'],0,60);
        $data['ItemNameKj_14'] = $this->mb_strimwidth2($data['ItemNameKj_14'],0,60);
        $data['ItemNameKj_15'] = $this->mb_strimwidth2($data['ItemNameKj_15'],0,60);
        $data['ItemNameKj_16'] = $this->mb_strimwidth2($data['ItemNameKj_16'],0,60);
        $data['ItemNameKj_17'] = $this->mb_strimwidth2($data['ItemNameKj_17'],0,60);
        $data['ItemNameKj_18'] = $this->mb_strimwidth2($data['ItemNameKj_18'],0,60);
        $data['ItemNameKj_19'] = $this->mb_strimwidth2($data['ItemNameKj_19'],0,60);
        $data['Ent_OrderId'] = $this->mb_strimwidth2($data['Ent_OrderId'],0,150);
        $data['CorporateName'] = $this->mb_strimwidth2($data['CorporateName'],0,140);
        $data['DivisionName'] = $this->mb_strimwidth2($data['DivisionName'],0,140);
        $data['CpNameKj'] = $this->mb_strimwidth2($data['CpNameKj'],0,65);
        $data['EntCustId'] = $this->mb_strimwidth2($data['EntCustId'],0,30);
        $data['PublicPhoneNumber'] = $this->mb_strimwidth2($data['PublicPhoneNumber'],0,15);
        $data['EntNameKj'] = $this->mb_strimwidth2($data['EntNameKj'],0,70);
        $data['EntPostalCode'] = $this->mb_strimwidth2($data['EntPostalCode'],0,8);
        $data['EntAddress1'] = $this->mb_strimwidth2($data['EntAddress1'],0,40);
        $data['EntAddress2'] = $this->mb_strimwidth2($data['EntAddress2'],0,40);
        $data['EntContactPhoneNumber'] = $this->mb_strimwidth2($data['EntContactPhoneNumber'],0,15);
        $data['ItemNameKj_20'] = $this->mb_strimwidth2($data['ItemNameKj_20'],0,60);
        $data['ItemNameKj_21'] = $this->mb_strimwidth2($data['ItemNameKj_21'],0,60);
        $data['ItemNameKj_22'] = $this->mb_strimwidth2($data['ItemNameKj_22'],0,60);
        $data['ItemNameKj_23'] = $this->mb_strimwidth2($data['ItemNameKj_23'],0,60);
        $data['ItemNameKj_24'] = $this->mb_strimwidth2($data['ItemNameKj_24'],0,60);
        $data['ItemNameKj_25'] = $this->mb_strimwidth2($data['ItemNameKj_25'],0,60);
        $data['ItemNameKj_26'] = $this->mb_strimwidth2($data['ItemNameKj_26'],0,60);
        $data['ItemNameKj_27'] = $this->mb_strimwidth2($data['ItemNameKj_27'],0,60);
        $data['ItemNameKj_28'] = $this->mb_strimwidth2($data['ItemNameKj_28'],0,60);
        $data['ItemNameKj_29'] = $this->mb_strimwidth2($data['ItemNameKj_29'],0,60);
        $data['ItemNameKj_30'] = $this->mb_strimwidth2($data['ItemNameKj_30'],0,60);

        // 出力した請求履歴データに対する更新処理
        $sql  = " UPDATE T_ClaimHistory ";
        $sql .= " SET    PrintedStatus = 2 ";
        $sql .= " ,      UpdateId = :UpdateId ";
        $sql .= " ,      UpdateDate = :UpdateDate ";
        $sql .= " WHERE  OrderSeq = :OrderSeq ";
        $sql .= " AND    PrintedFlg = 0 ";
        if ($data['ClaimPattern'] != 1) {
            $sql .= " AND    PrintedStatus = 1 ";
        }
        $sql .= " AND    ValidFlg = 1 ";

        $this->dbAdapter->query($sql)->execute(array(
                                                   ':UpdateId' => $userId,
                                                   ':UpdateDate' => date('Y-m-d H:i:s'),
                                                   ':OrderSeq' => $orderSeq
                                               ));
        if ($data['ClaimPattern'] != 1) {
            // 強制解約通知出力日保持
            if ($cancelNoticePrint) {
                $sql = " UPDATE T_ClaimControl ";
                $sql .= " SET    CancelNoticePrintDate = :CancelNoticePrintDate ";
                $sql .= " ,      UpdateId = :UpdateId ";
                $sql .= " ,      UpdateDate = :UpdateDate ";
                $sql .= " WHERE  ClaimId = :ClaimId ";

                $this->dbAdapter->query($sql)->execute(
                    array(
                        ':CancelNoticePrintDate' => $data['ClaimDate'],
                        ':UpdateId' => $userId,
                        ':UpdateDate' => date('Y-m-d H:i:s'),
                        ':ClaimId' => $data['ClaimId']
                    )
                );

                // 請求書発行停止制御
                if ($data['ClaimIssueStopFlg'] == 1) {
                    $sql = " UPDATE T_ClaimControl ";
                    $sql .= " SET    CancelNoticePrintStopStatus = 0 ";
                    $sql .= " ,      UpdateId = :UpdateId ";
                    $sql .= " ,      UpdateDate = :UpdateDate ";
                    $sql .= " WHERE  ClaimId = :ClaimId ";
                    $this->dbAdapter->query($sql)->execute(
                        array(
                            ':UpdateId' => $userId,
                            ':UpdateDate' => date('Y-m-d H:i:s'),
                            ':ClaimId' => $data['ClaimId']
                        )
                    );
                }
            }
        }

        return $data;
    }

    /**
     * コメント設定の「請求書CSVの商品明細数増加対象OEM」に含まれるOEMか
     * @param int $oemId 判定対象
     * @return boolean 含まれる場合true 含まれない場合false
     */
    protected function isItemIncOem($oemId){
        $mdlCode = new TableCode($this->dbAdapter);
        $oemListStr = $mdlCode->find( 207, 1)->current()['Note'];

        $oemList = str_replace(array(' ','　'), '', $oemListStr); //空白除去

        //入力なしの場合は対象なし
        if($oemList == "" || $oemList == null){
            return false;
        }

        $oemList = explode(',', $oemList);

        //値が入っていない要素を削除
        foreach($oemList as $key => $val){
            if($val == ""){
                unset($oemList[$key]);
            }
        }

        //0を含む場合はnullも対象にする
        if(in_array(0, $oemList)){
            $oemList[] = null;
        }

        return in_array($oemId, $oemList);
    }

    private function getEntNameKj($oid, $printIssueCountCd)
    {
        $mdlCode = new TableCode($this->dbAdapter);
        $data_code = $mdlCode->find(215, $oid)->current();

        if ($printIssueCountCd == '00') {
            return $data_code['KeyContent'];
        }
        return $data_code['Class'.intval($printIssueCountCd)];
    }

    private function getPublicPhoneNumber($oid, $printIssueCountCd)
    {
        $mdlCode = new TableCode($this->dbAdapter);
        $data_code = $mdlCode->find(108, $oid)->current();

        if ($printIssueCountCd == '00') {
            return $data_code['KeyContent'];
        }
        return $data_code['Class'.intval($printIssueCountCd)];
    }

    private function mb_strimwidth2($s, $b, $w, $t = null, $e = null)
    {
        if (is_null($e)) {
            $e = mb_internal_encoding();
        }
        return mb_convert_encoding(mb_strcut(mb_convert_encoding($s, 'SJIS-win', $e), $b, $w, 'SJIS-win'), $e, 'SJIS-win');
    }
}

Application::getInstance()->run();
