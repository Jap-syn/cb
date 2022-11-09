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
use models\Logic\LogicCampaign;
use models\Table\TableSystemEvent;
use models\Table\TableSystemProperty;
use Coral\Base\BaseGeneralUtils;
use models\Logic\LogicChargeDecision;
use models\Table\TableUser;
use models\View\ViewChargeConfirm;
use models\Table\TablePayingControl;
use models\Table\TablePayingAndSales;
use models\Table\TableCancel;
use models\Table\TableStampFee;
use models\Table\TablePayingBackControl;
use models\Table\TableEnterprise;
use models\Table\TableOrder;
use models\Table\TableEnterpriseClaimHistory;
use models\Table\TableOem;

/**
 * アプリケーションクラスです。
 * 指定の振込データを作成するバッチ
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

$this->logger->info('_data_patch_20160106_1800.php start');

            $globalConfig = include __DIR__ . '/../config/autoload/global.php';
            // 接続時間を設定する
            $rds_session_timezone = $globalConfig['RDS_SESSION_TIMEZONE'];
            if (isset($rds_session_timezone)) {
                $this->dbAdapter->query('SET SESSION time_zone = :time_zone')->execute(array(':time_zone'=>$rds_session_timezone));
            }

$this->dbAdapter->getDriver()->getConnection()->beginTransaction();

            // --------------------------------
            // 振込データの作成
            // --------------------------------
            $this->fixAction();

$this->dbAdapter->getDriver()->getConnection()->commit();
//$this->dbAdapter->getDriver()->getConnection()->rollback();

$this->logger->info('_data_patch_20160106_1800.php end');
            $exitCode = 0; // 正常終了

        } catch( \Exception $e ) {
            try{
                $this->dbAdapter->getDriver()->getConnection()->rollback();
            } catch ( \Exception $err) { }
            // エラーログを出力
            if ( isset($this->logger) ) {
$this->logger->err($e->getMessage());
$this->logger->err($e->getTraceAsString());
echo $e->getMessage();
echo getTraceAsString();

            }
        }

        // 終了コードを指定して処理終了
        exit($exitCode);

    }

    /**
     * 立替確定処理アクション
     */
    public function fixAction()
    {
        //--- $params = $this->getParams();
        // 1) CSVデータの作成
        // 保存用ディレクトリの取得
        $mdlsp = new TableSystemProperty($this->dbAdapter);

        $module = '[DEFAULT]';
        $category = 'systeminfo';
        $name = 'TempFileDir';

        $transCsvDir = $mdlsp->getValue($module, $category, $name);

        // ファイル名用配列
        $transCsvList = array();

        // OEMID取得
        //--- $oemId = (isset($params['oemid'])) ? $params['oemid'] : null;
        $oemId = null;

        //--- $mdlvc = new ViewChargeConfirm($this->dbAdapter);
        $numSimePtn = 0;// 有効締め日パターン数
        //--- $datas = $mdlvc->getConfirmList(0, "", "", false, $numSimePtn, $oemId, -1, true, 0);
        $datas = $this->getConfirmList(0, "", "", false, $numSimePtn, $oemId, -1, true, 0);

        foreach($datas as $value){
            // OEMID、確定日、予定日ごとにCSV作成

            // OEM情報
            $mdlOem = new TableOem($this->dbAdapter);
            $oem = $mdlOem->findOem2($value['OemId'])->current();

            // 条件指定  OEMID、確定日、予定日ごとの立替振込管理Seqのリスト
            $seqList = explode(',', $value["SeqList"]);
            $seqListHolder = implode(',', array_fill(0, count($seqList), '?'));
            // データの取得
            $sql = <<<EOQ
SELECT  pc.Seq
    ,   pc.FixedDate
    ,   pc.ExecScheduleDate
    ,   e.FfCode
    ,   e.FfName
    ,   e.FfBranchCode
    ,   e.FfBranchName
    ,   e.FfAccountClass
    ,   e.FfAccountNumber
    ,   e.FfAccountName
    ,   pc.DecisionPayment
FROM    T_Enterprise e
        INNER JOIN T_PayingControl pc ON (pc.EnterpriseId = e.EnterpriseId)
WHERE   pc.SpecialPayingFlg = 0         /* 臨時立替以外 */
AND     pc.PayingControlStatus = 1
AND     pc.DecisionPayment > 0
AND     pc.FixedDate IN ('2015-12-31', '2016-01-01')
AND     pc.Seq IN ($seqListHolder)
ORDER BY
        pc.Seq
;
EOQ;

            $ri = $this->dbAdapter->query($sql)->execute($seqList);
            $csvData = ResultInterfaceToArray($ri);

            // 合計の算出
            $totalCnt = 0;
            $totalDecisionPayment = 0;
            $maxSeq = 0;

            if (! empty($csvData)) {
                // 拡張子
                $ext = 'csv';
                if ($oem != false && $oem['PayingMethod'] == 1 && $oem['FixedLengthFlg'] == 1) {
                    // OEM立替の固定長の場合、テキスト形式
                    $ext = 'txt';
                }
                // ファイル名
                $transCsvFileName = sprintf("TransferData_%s_%s_%s_%s.%s", date("YmdHis"), $value['OemId'], $value['DecisionDate'], $value['ExecScheduleDate'], $ext);

                // ファイルフルパス
                $transCsvFullFileName = $transCsvDir . '/' . $transCsvFileName;
                // すでにファイルが作成されていたら削除
                if ( file_exists($transCsvFullFileName)) {
                    unlink($transCsvFullFileName);
                }

                // ヘッダーレコード
                if ($oem != false && $oem['PayingMethod'] == 1) {
                    //OEM立替のOEM
                    if ($oem['FixedLengthFlg'] == 1) {
                        // 固定長
                        $headerRecord = sprintf(
                        "1210%s%s%02d%02d%04d%s%03d%s%01d%07d%s\r\n",
                        $oem['ConsignorCode'],                                                                                                                          // 委託者コード
                        BaseGeneralUtils::rpad(BaseGeneralUtils::convertWideToNarrowEx(BaseGeneralUtils::deleteMultiByte($oem['ConsignorName'])), ' ', 40, true),       // 委託者名
                        date('m', strtotime($csvData[0]['ExecScheduleDate'])),
                        date('d', strtotime($csvData[0]['ExecScheduleDate'])),
                        $oem['RemittingBankCode'],                                                                                                                      // 仕向金融機関番号
                        BaseGeneralUtils::rpad(BaseGeneralUtils::convertWideToNarrowEx(BaseGeneralUtils::deleteMultiByte($oem['RemittingBankName'])), ' ', 15, true),   // 仕向金融機関名
                        $oem['RemittingBranchCode'],                                                                                                                    // 仕向支店番号
                        BaseGeneralUtils::rpad(BaseGeneralUtils::convertWideToNarrowEx(BaseGeneralUtils::deleteMultiByte($oem['RemittingBranchName'])), ' ', 15, true), // 仕向支店名
                        $oem['AccountClass'],                                                                                                                           // 依頼人預金種別
                        $oem['AccountNumber'],                                                                                                                          // 依頼人口座番号
                        BaseGeneralUtils::rpad("", ' ', 17)
                        );
                    }
                    else {
                        // CSV
                        $headerRecord = sprintf(
                        "1,21,0,%s,%s,%02d%02d,%04d,%s,%03d,%s,%d,%07d,\r\n",
                        $oem['ConsignorCode'],                                                                                      // 委託者コード
                        BaseGeneralUtils::convertWideToNarrowEx(BaseGeneralUtils::deleteMultiByte($oem['ConsignorName'])),          // 委託者名
                        date('m', strtotime($csvData[0]['ExecScheduleDate'])),
                        date('d', strtotime($csvData[0]['ExecScheduleDate'])),
                        $oem['RemittingBankCode'],                                                                                  // 仕向金融機関番号
                        BaseGeneralUtils::convertWideToNarrowEx(BaseGeneralUtils::deleteMultiByte($oem['RemittingBankName'])),      // 仕向金融機関名
                        $oem['RemittingBranchCode'],                                                                                // 仕向支店番号
                        BaseGeneralUtils::convertWideToNarrowEx(BaseGeneralUtils::deleteMultiByte($oem['RemittingBranchName'])),    // 仕向支店名
                        $oem['AccountClass'],                                                                                       // 依頼人預金種別
                        $oem['AccountNumber']                                                                                       // 依頼人口座番号
                        );
                    }
                }
                else {
                    // CB or CB立替のOEM
                    $headerRecord = sprintf(
                    "1,21,0,1848513200,ｶ)ｷｬｯﾁﾎﾞｰﾙ,%02d%02d,0033,,002,,1,3804573,\r\n",
                    date('m', strtotime($csvData[0]['ExecScheduleDate'])),
                    date('d', strtotime($csvData[0]['ExecScheduleDate']))
                    );
                }
                $headerRecord = mb_convert_encoding($headerRecord, "SJIS", "UTF-8");

                // データレコード
                $dataRecords = "";
                for ($i = 0 ; $i < count($csvData) ; $i++) {

                    $totalCnt++;
                    $totalDecisionPayment += $csvData[$i]['DecisionPayment'];

                    if ($oem != false && $oem['PayingMethod'] == 1 && $oem['FixedLengthFlg'] == 1) {
                        //OEM 固定長
                        $dataRecord = sprintf(
                        "2%04d%s%03d%s%s%01d%07d%s%s0%s7%s\r\n",
                        $csvData[$i]['FfCode'],                                                                                                                             // 銀行コード
                        BaseGeneralUtils::rpad(BaseGeneralUtils::convertWideToNarrowEx(BaseGeneralUtils::deleteMultiByte($csvData[$i]['FfName'])), ' ', 15, true),          // 銀行名
                        $csvData[$i]['FfBranchCode'],                                                                                                                       // 支店コード
                        BaseGeneralUtils::rpad(BaseGeneralUtils::convertWideToNarrowEx(BaseGeneralUtils::deleteMultiByte($csvData[$i]['FfBranchName'])), ' ', 15, true),    // 支店名
                        BaseGeneralUtils::rpad("",	' ', 4, true),                                                                                                          // 未使用
                        $csvData[$i]['FfAccountClass'],                                                                                                                     // 科目
                        $csvData[$i]['FfAccountNumber'],                                                                                                                    // 口座番号
                        BaseGeneralUtils::rpad(BaseGeneralUtils::convertWideToNarrowEx(BaseGeneralUtils::deleteMultiByte(($csvData[$i]['FfAccountName']))), ' ', 30),       // 受取人
                        BaseGeneralUtils::lpad($csvData[$i]['DecisionPayment'], '0', 10),                                                                                   // 金額
                        BaseGeneralUtils::rpad("", ' ', 20),
                        BaseGeneralUtils::rpad("", ' ', 8)
                        );
                    }
                    else {
                        // CSV
                        $dataRecord = sprintf(
                        "2,%d,%s,%d,%s,,%d,%d,%s,%d,0,,, , , \r\n",
                        $csvData[$i]['FfCode'],                                                                                     // 銀行コード
                        BaseGeneralUtils::convertWideToNarrowEx(BaseGeneralUtils::deleteMultiByte($csvData[$i]['FfName'])),         // 銀行名
                        $csvData[$i]['FfBranchCode'],                                                                               // 支店コード
                        BaseGeneralUtils::convertWideToNarrowEx(BaseGeneralUtils::deleteMultiByte($csvData[$i]['FfBranchName'])),   // 支店名
                        $csvData[$i]['FfAccountClass'],                                                                             // 科目
                        $csvData[$i]['FfAccountNumber'],                                                                            // 口座番号
                        BaseGeneralUtils::convertWideToNarrowEx(BaseGeneralUtils::deleteMultiByte($csvData[$i]['FfAccountName'])),  // 受取人
                        $csvData[$i]['DecisionPayment']                                                                             // 金額
                        );
                    }

                    $dataRecords .= mb_convert_encoding($dataRecord, "SJIS", "UTF-8");

                    // 出力対象の中で最大の立替管理Seqを取得
                    if ($maxSeq < $csvData[$i]['Seq']) {
                        $maxSeq = $csvData[$i]['Seq'];
                    }
                }

                // トレーラレコード
                if ($oem != false && $oem['PayingMethod'] == 1 && $oem['FixedLengthFlg'] == 1) {
                    //OEM 固定長
                    $trailerRecord = sprintf(
                    "8%s%s%s\r\n",
                    BaseGeneralUtils::lpad($totalCnt, '0', 6),
                    BaseGeneralUtils::lpad($totalDecisionPayment, '0', 12),
                    BaseGeneralUtils::lpad("", ' ', 101)
                    );
                }
                else {
                    // CSV
                    $trailerRecord = sprintf(
                    "8,%d,%d,\r\n",
                    $totalCnt,
                    $totalDecisionPayment
                    );
                }
                $trailerRecord = mb_convert_encoding($trailerRecord, "SJIS", "UTF-8");

                // エンドレコード
                if ($oem != false && $oem['PayingMethod'] == 1 && $oem['FixedLengthFlg'] == 1) {
                    //OEM 固定長
                    $endRecord = BaseGeneralUtils::rpad("9", ' ', 120, true) . "\r\n";
                }
                else {
                    $endRecord = "9,\r\n";
                }
                $endRecord = mb_convert_encoding($endRecord, "SJIS", "UTF-8");

                // 作成したデータを結合
                $contents = $headerRecord . $dataRecords . $trailerRecord . $endRecord;
                // ファイルに保存
                file_put_contents($transCsvFullFileName, $contents);

                // ファイル名用配列にファイル名を格納
                $transCsvList[$maxSeq] = $transCsvFullFileName;

            }
        }

        // PDF作成対象データ
        $claimPdfList = array();

        // 3) 更新処理
        $errorFlg = false;
        $errorMsg = "";

        try {
            // パラメータ取得
            //---- $params = $this->getParams();
            // OEMID取得
            //---- $oemId = (isset($params['oemid'])) ? $params['oemid'] : -1;
            $oemId = -1;

            // ユーザーIDの取得
            $userId = 1;

            // 立替確定処理
            $this->decision($oemId, $transCsvList, $claimPdfList, $userId);

        } catch (\Exception $e) {
            $errorFlg = true;
            $errorMsg = $e->getMessage();
            throw $e;
        }

    }

    /**
     * 立替確認データを取得する
     *
     * @param int $isExec 0：未立替え　1：立替済み
     * @param string $fromDecision 開始確定日
     * @param string $toDecision 終了確定日
     * @param int $numSimePtn 有効締め日パターン数
     * @param boolean $isOnlyTudoSeikyu 都度請求のみ表示
     * @param int $eid 加盟店ID
     * @param boolean $updateFlg 更新フラグ
     * @param number $specialPayingFlg 臨時加盟店立替フラグ
     * @return ResultInterface
     */
    public function getConfirmList($isExec = 0, $fromDecision = '', $toDecision = '', $isOnlyTudoSeikyu, &$numSimePtn, $oemId = null, $eid = -1, $updateFlg, $specialPayingFlg = null)
    {
        // 締め日パターン取得
        $sql = " SELECT KeyCode, KeyContent FROM M_Code WHERE CodeId = 2 AND ValidFlg = 1 ORDER BY KeyCode ";

        $ri = $this->dbAdapter->query($sql)->execute(null);
        $numSimePtn = $ri->count();// 戻り引数[有効締め日パターン数]設定

        // SQL文字列パーツ
        $sqlA = "";
        $sqlB = "";
        $sqlC = "";
        $sqlD = "";

        $i = 1;
        foreach ($ri as $row) {
            // sqlA考慮
            $sqlA .= (" ,      " . CoatStr($row['KeyContent']) . " AS P" . $i . "NM ");
            $sqlA .= (" ,      SUM(T.P" . $i . "CNT) AS P" . $i . "CNT ");
            $sqlA .= (" ,      SUM(T.P" . $i . "PAY) AS P" . $i . "PAY ");
            $sqlA .= (" ,      MAX(T.P" . $i . "FD ) AS P" . $i . "FD  ");
            // sqlB考慮
            $sqlB .= (($i > 1) ? (" + T.P" . $i . "CNT") : ("T.P" . $i . "CNT"));
            // sqlC考慮
            $sqlC .= (($i > 1) ? (" + T.P" . $i . "PAY") : ("T.P" . $i . "PAY"));
            // sqlD考慮
            $sqlD .= ("      ,      CASE WHEN V_ChargeConfirm.FixPattern = " . $row['KeyCode'] . " THEN 1 ELSE 0 END AS P" . $i . "CNT ");
            $sqlD .= ("      ,      CASE WHEN V_ChargeConfirm.FixPattern = " . $row['KeyCode'] . " THEN DecisionPayment ELSE 0 END AS P" . $i . "PAY ");
            $sqlD .= ("      ,      CASE WHEN V_ChargeConfirm.FixPattern = " . $row['KeyCode'] . " THEN FixedDate ELSE NULL END AS P" . $i . "FD ");

            // $iインクリメント
            $i++;
        }

        // SQL組立て
        $sql  = " SELECT IFNULL(T.OemId,0) as OemId ";
        $sql .= " ,      T.DecisionDate ";
        $sql .= " ,      T.ExecScheduleDate ";
        $sql .= " ,      MAX(T.Seq) AS Seq ";
        $sql .= " ,      MAX(IFNULL(T.PayingDataDownloadFlg, 0)) as PayingDataDownloadFlg ";
        $sql .= " ,      MAX(LENGTH(IFNULL(T.PayingDataFilePath,''))) AS PayingDataFilePath ";
        $sql .= " ,      MAX(LENGTH(IFNULL(ClaimPdfFilePath,''))) AS ClaimPdfFilePath ";
        $sql .= " ,      GROUP_CONCAT(T.Seq SEPARATOR ',') AS SeqList ";
        $sql .= $sqlA;
        $sql .= " ,      SUM(" . $sqlB . ") AS CTOTAL ";
        $sql .= " ,      SUM(" . $sqlC . ") AS PTOTAL ";
        $sql .= " FROM ";
        $sql .= "     (SELECT V_ChargeConfirm.Seq ";
        $sql .= "      ,      V_ChargeConfirm.DecisionDate ";
        $sql .= "      ,      V_ChargeConfirm.ExecScheduleDate ";
        $sql .= "      ,      V_ChargeConfirm.FixedDate ";
        $sql .= "      ,      V_ChargeConfirm.OemId ";
        $sql .= "      ,      V_ChargeConfirm.ClaimPdfFilePath ";
        $sql .= "      ,      V_ChargeConfirm.PayingDataDownloadFlg ";
        $sql .= "      ,      V_ChargeConfirm.PayingDataFilePath ";
        $sql .= $sqlD;
        $sql .= "      FROM   ( ";
        $sql .= "               SELECT MPC.FixPattern AS FixPattern ";
        $sql .= "               ,      PC.Seq AS Seq ";
        $sql .= "               ,      PC.EnterpriseId AS EnterpriseId ";
        $sql .= "               ,      PC.FixedDate AS FixedDate ";
        $sql .= "               ,      PC.DecisionDate AS DecisionDate ";
        $sql .= "               ,      PC.ExecScheduleDate AS ExecScheduleDate ";
        $sql .= "               ,      PC.DecisionPayment AS DecisionPayment ";
        $sql .= "               ,      PC.ClaimPdfFilePath AS ClaimPdfFilePath ";
        $sql .= "               ,      PC.PayingDataDownloadFlg AS PayingDataDownloadFlg ";
        $sql .= "               ,      PC.PayingDataFilePath AS PayingDataFilePath ";
        $sql .= "               ,      PC.OemId AS OemId ";
        $sql .= "               FROM   T_PayingControl PC ";
        $sql .= "                      INNER JOIN T_Enterprise ENT ON PC.EnterpriseId = ENT.EnterpriseId ";
        $sql .= "                      INNER JOIN M_PayingCycle MPC ON ENT.PayingCycleId = MPC.PayingCycleId ";
        $sql .= "               WHERE  1 = 1 ";
        // 臨時加盟店立替フラグ（0：通常／1：臨時加盟店立替精算）
        // ※ 引数によって抽出条件切り替え。デフォルトは「通常」のみ
        if (!is_null($specialPayingFlg)) {
            $sql .= "           AND    PC.SpecialPayingFlg = " . $specialPayingFlg;
        }
//         // 0：未立替え／1：立替済み
//         $sql .= "               AND    PC.ExecFlg = " . $isExec;
        // 立替確定日範囲
        $where = BaseGeneralUtils::makeWhereDate('PC.DecisionDate', $fromDecision, $toDecision);
        if ($where != '') {
            $sql .= ("          AND    " . $where);
        }
        // OemId
        if (!is_null($oemId)) {
            $sql .= ("          AND    IFNULL(PC.OemId, 0) = ".$oemId);
        }
        // 都度請求のみ表示
        if ($isOnlyTudoSeikyu) {
            $sql .= ("          AND    PC.ExecFlg = 1 ");
            $sql .= ("          AND    PC.DecisionPayment < 0 ");
        }
        // 加盟店ID
        if ($eid != -1) {
            $sql .= ("          AND    PC.EnterpriseId = " . $eid);
        }
        $sql .= ("          AND    PC.FixedDate IN( '2015-12-31', '2016-01-01') ");
//         $sql .= ("          AND    PC.FixedDate <= '2015-11-30' ");
        $sql .= ("          AND    PC.PayingControlStatus = 1 ");
        $sql .= "             ) V_ChargeConfirm ";
        $sql .= "     ) T ";
        $sql .= " GROUP BY ";
        $sql .= "     IFNULL(T.OemId,0) ";
        $sql .= " ,   T.DecisionDate ";
        $sql .= " ,   T.ExecScheduleDate ";
        $sql .= " ORDER BY ";
        $sql .= "     IFNULL(T.OemId,0) ASC ";
        $sql .= " ,   T.DecisionDate DESC ";
        $sql .= " ,   T.ExecScheduleDate DESC ";
        $sql .= " ,   T.Seq ASC ";


$this->logger->info( $sql );

        return $this->dbAdapter->query($sql)->execute(null);
    }

    /**
     * 立替確定処理
     *
     * @param int $oemId OEMID
     * @param array $transCsvList 振込データCSVファイルパスの配列
     * @param array $claimPdfList 都度請求PDFファイルパスのリスト
     * @param int $userId ユーザID
     */
    public function decision($oemId, $transCsvList, $claimPdfList, $userId)
    {
        $mdlvc = new ViewChargeConfirm($this->dbAdapter);
        $mdlpc = new TablePayingControl($this->dbAdapter);
        $mdlpas = new TablePayingAndSales($this->dbAdapter);
        $mdlc = new TableCancel($this->dbAdapter);
        $mdlsf = new TableStampFee($this->dbAdapter);
        $mdlpbc = new TablePayingBackControl($this->dbAdapter);
        $mdle = new TableEnterprise($this->dbAdapter);
        $mdlo = new TableOrder($this->dbAdapter);
        $mdlech = new TableEnterpriseClaimHistory($this->dbAdapter);

        if(intval($oemId) != -1){
            $oem = $oemId;
        }
        $numSimePtn = 0;// 有効締め日パターン数
        $datas = $this->getConfirmList(0, "", "", false, $numSimePtn, $oem, -1, true, 0);

        // 取得データから更新対象の立替振込管理Seqを取得する。
        $payingSeqList = array();
        $list = array();
        foreach ($datas as $value) {
            // 取得データの文字列を分割して配列に格納
            $list = explode(',', $value["SeqList"]);
            // 格納した配列をマージする
            $payingSeqList = array_merge_recursive($payingSeqList, $list);
        }

        // 更新処理
        // 更新対象の立替管理Seq分、処理する。
        foreach ($payingSeqList as $payingSeq) {
            // -------------------------
            // 立替振込管理の更新
            // -------------------------
            // (CSVファイル)
            $obj_csv = null;
            $filename = isset($transCsvList[$payingSeq]) ? $transCsvList[$payingSeq] : null;
            if (!is_null($filename)) {
                $fp = fopen($filename, "rb");
                $obj_csv = fread($fp, filesize($filename));
                if (!$obj_csv) {
                    throw new \Exception('振込ファイルの作成に失敗しました。');
                }
                fclose($fp);
                //unlink($filename);  ***手動削除を想定
            }

            // (PDFファイル)
            $obj_pdf = null;
            $filename = isset($claimPdfList[$payingSeq]) ? $claimPdfList[$payingSeq] : null;
            if (!is_null($filename)) {
                $fp = fopen($filename, "rb");
                $obj_pdf = fread($fp, filesize($filename));
                if (!$obj_pdf) {
                    throw new \Exception('都度請求ファイルの作成に失敗しました。');
                }
                fclose($fp);
                // unlink($filename);
            }

            $pcdata = array(
                    'PayingDataDownloadFlg' => 0,                       // 振込データDLフラグ
                    'PayingDataFilePath' => $obj_csv,                   // (ﾊﾞｲﾅﾘﾃﾞｰﾀ)振込データCSV
                    'ClaimPdfFilePath' => $obj_pdf,                     // (ﾊﾞｲﾅﾘﾃﾞｰﾀ)都度請求PDF
                    'UpdateId' => $userId,                              // 更新者
            );

            // 更新
            $mdlpc->saveUpdate($pcdata, $payingSeq);

        }

        return true;
    }

}

Application::getInstance()->run();
