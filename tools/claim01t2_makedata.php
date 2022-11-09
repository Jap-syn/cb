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
use Coral\Base\BaseGeneralUtils;
use Coral\Base\Auth\BaseAuthUtility;
use Coral\Coral\CoralValidate;
use Zend\Db\Adapter\Adapter;
use Zend\Config\Reader\Ini;
use models\Table\TableUser;
use models\Table\TableOrder;
use models\Table\TableEnterprise;
use models\Table\TableSite;
use models\Table\TableClaimControl;
use models\Table\TableClaimHistory;
use models\Table\TableSystemProperty;
use models\Table\TableReclaimIndicate;
use models\Table\TableClaimError;
use models\Table\TableOrderItems;
use models\Table\TableBusinessCalendar;
use models\Table\TableOemClaimFee;
use models\Table\TableClaimThreadManage;
use models\Table\TableSmbcpaAccount;
use models\Logic\Exception\LogicClaimException;
use models\Logic\LogicMypageOrder;
use models\Logic\LogicOemClaimAccount2;
use models\Table\TableClaimBatchControl;
use models\Logic\LogicSmbcRelation;
use models\Logic\LogicCreditTransfer;
use models\Logic\LogicClaimPrint;
use models\Table\TableClaimPrintPattern;
use models\Table\TableClaimPrintCheck;

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

            // 引数が取得できないときは処理続行不可
            if ($_SERVER['argc'] != 2) {
                exit(0);
            }

            $fldName = $_SERVER['argv'][1];

$this->logger->info(sprintf("claim01t2_makedata.php start [%s]",$fldName));

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

            // SMBC決済ステーション連携ロジックのサービス設定をロード
            LogicSmbcRelation::loadServiceConfig($data['smbc_relation']);

            // 主処理(CSV＆ZIP化及び送信には、東洋紙業稼働日非稼働日を問う)
            $isBusiness = $this->isBatchStart();

            $this->main($fldName, $isFinishAll, $isBusiness);

$this->logger->info(sprintf("claim01t2_makedata.php end [%s]", $fldName));

            if ($isFinishAll && $isBusiness) {
                // 全処理完了時は[claim01t3(CSV＆ZIP化)]ﾊﾞｯﾁを非同期起動
                if (\Coral\Base\BaseProcessInfo::isWin()) {
                    $fp = popen('start php ' . __DIR__ . '/claim01t3_makedata.php', 'r');
                    pclose($fp);
                }
                else {
                    exec('php ' . __DIR__ . '/claim01t3_makedata.php > /dev/null &');
                }
            }

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
     * 主処理
     *
     * @param string $fldName フィールド名 例)"Rw1"
     * @param boolean $isFinishAll (戻り引数)全処理完了か？ true:完了／false:未完了
     * @param boolean $isBusiness 東洋紙業稼働日か？ true:稼働日／false:非稼働日
     * @return null
     */
    protected function main($fldName, &$isFinishAll, $isBusiness)
    {
        $isSuccess = false;
        if ($fldName != "Re") {
            // (初回請求分)
            try {
                $this->jobparamsetcsv($fldName);
                $isSuccess = true;
            } catch( \Exception $e) {
                $this->logger->info('初回請求のジョブ転送に失敗しました');
                $this->logger->info($e->getMessage());
                $this->logger->info($e->getTraceAsString());
            }
        }
        else if($isBusiness){
            // (再請求分)

            // 再請求パターン
            $reclaimpattern = array(9, 8, 7, 6, 4, 2);

            // 再請求ジョブ転送
            try {
                foreach ($reclaimpattern as $pattern) {
                    $this->jobTransferTokusoku($pattern, false);
                }
                $this->jobTransferTokusoku(2, true);

                $isSuccess = true;
            } catch( \Exception $e) {
                $this->logger->info('督促請求のジョブ転送に失敗しました');
                $this->logger->info($e->getMessage());
                $this->logger->info($e->getTraceAsString());
            }
        }

        // スレッドステータス更新
        $mdlctm = new \models\Table\TableClaimThreadManage($this->dbAdapter);
        $isNextIsSmbc = false;
        $isNextIsRe = false;
        $isFinishAll = $mdlctm->updateStatus($fldName, ($isSuccess) ? 1 : 9, $isNextIsSmbc, $isNextIsRe);

        // (Rw1～0が完了したﾀｲﾐﾝｸﾞで、[初回SMBC]⇒[再請求]処理へ)
        if (!$isFinishAll && $isNextIsSmbc) {
            if (\Coral\Base\BaseProcessInfo::isWin()) {
                $fp = popen('start php ' . __DIR__ . '/claim01t2_makedata.php Smbc', 'r');
                pclose($fp);
            }
            else {
                exec('php ' . __DIR__ . '/claim01t2_makedata.php Smbc > /dev/null &');
            }
        }
        else if (!$isFinishAll && $isNextIsRe) {
            if (\Coral\Base\BaseProcessInfo::isWin()) {
                $fp = popen('start php ' . __DIR__ . '/claim01t2_makedata.php Re', 'r');
                pclose($fp);
            }
            else {
                exec('php ' . __DIR__ . '/claim01t2_makedata.php Re > /dev/null &');
            }

        }
        else { ; }// (処理なしの明示)

        if (!$isBusiness && $isNextIsRe) {
            $this->logger->info('claim01_makedata.php end');
        }

        return;
    }

    /**
     * 本バッチ実行の起動確認を行い、請求バッチ管理テーブルの登録を行う
     *
     * @return boolean true:東洋紙業稼働日、false:東洋紙業非稼働日
     */
    protected function isBatchStart() {

        $mdlbc = new TableBusinessCalendar($this->dbAdapter);

        // 今日 + 1 を取得
        $tmrw = date("Y-m-d", strtotime("+1 day"));

        // 稼働日か判定
        $isBusiness = $mdlbc->isToyoBusinessDate($tmrw);

        // 請求バッチ管理テーブル登録処理
        $mdlcbc = new TableClaimBatchControl($this->dbAdapter);
        // データ登録
        $data = array(
                'ClaimDate' => date('Y-m-d'),
        );
        $mdlcbc->saveNew($data);

        return $isBusiness;
    }

    /**
     * 請求日の取得
     * (本日以降の[ToyoBusinessFlg＝1]の、最小BusinessDateを取得し戻す)
     *
     * @return string 請求日
     */
    protected function getClaimDate() {
        return $this->dbAdapter->query(" SELECT MIN(BusinessDate) AS minBusinessDate FROM T_BusinessCalendar WHERE BusinessDate > :BusinessDate AND ToyoBusinessFlg = 1 "
        )->execute(array(':BusinessDate' => date('Y-m-d')))->current()['minBusinessDate'];
    }

    /**
     * 初回請求処理
     *
     * @param string $fldName フィールド名 例)"Rw1"
     * @return (なし)
     */
    protected function jobparamsetcsv($fldName)
    {
        // 初回請求データ抽出の基本SQL取得
        $sql = TableClaimThreadManage::getBaseQueryClaim();

        // 引数フィールド名毎の条件付加
        if      ($fldName == "Rw1" ) { $sql .= " AND    (ifnull(o.OemId, 0) <> 2 AND o.ReverseOrderId LIKE '1%') "; }
        else if ($fldName == "Rw2" ) { $sql .= " AND    (ifnull(o.OemId, 0) <> 2 AND o.ReverseOrderId LIKE '2%') "; }
        else if ($fldName == "Rw3" ) { $sql .= " AND    (ifnull(o.OemId, 0) <> 2 AND o.ReverseOrderId LIKE '3%') "; }
        else if ($fldName == "Rw4" ) { $sql .= " AND    (ifnull(o.OemId, 0) <> 2 AND o.ReverseOrderId LIKE '4%') "; }
        else if ($fldName == "Rw5" ) { $sql .= " AND    (ifnull(o.OemId, 0) <> 2 AND o.ReverseOrderId LIKE '5%') "; }
        else if ($fldName == "Rw6" ) { $sql .= " AND    (ifnull(o.OemId, 0) <> 2 AND o.ReverseOrderId LIKE '6%') "; }
        else if ($fldName == "Rw7" ) { $sql .= " AND    (ifnull(o.OemId, 0) <> 2 AND o.ReverseOrderId LIKE '7%') "; }
        else if ($fldName == "Rw8" ) { $sql .= " AND    (ifnull(o.OemId, 0) <> 2 AND o.ReverseOrderId LIKE '8%') "; }
        else if ($fldName == "Rw9" ) { $sql .= " AND    (ifnull(o.OemId, 0) <> 2 AND o.ReverseOrderId LIKE '9%') "; }
        else if ($fldName == "Rw0" ) { $sql .= " AND    (ifnull(o.OemId, 0) <> 2 AND o.ReverseOrderId LIKE '0%') "; }
        else if ($fldName == "Smbc") { $sql .= " AND    (o.OemId = 2) "; }

        $sql .= " ORDER BY c.PostalCode, o.OrderId ";

        // SQL実行
        $ri = $this->dbAdapter->query($sql)->execute(null);

        $datas = ResultInterfaceToArray($ri);

        // (PK予約目的)SMBCバーチャル口座を後方より、且つ、住み分けて取得
        $smbcpaAccSeqs = $this->getReserveSmbcpaAccountSeq($fldName, $datas);

        $logicmo = new LogicMypageOrder($this->dbAdapter);
        $mdlsa = new TableSmbcpaAccount($this->dbAdapter);

        // 結果をARRAY化
        $idxAccSeqs = 0;
        $i = 0;
        $list = array();
        $exparams = array();
        foreach ($datas as $data) {
            $list['OrderSeq'.$i] = $data['OrderSeq'];
            $list['SiteId'.$i] = $data['SiteId'];
            $list['BillingAgentFlg'.$i] = $data['BillingAgentFlg'];
            $list['chkCsv'.$i] = 1;

            // EXパラメタ(ここから)
            $exparams[$i]['cntClaimHistory'] = $data['cntClaimHistory'];
            $exparams[$i]['cntClaimHistory2'] = $data['cntClaimHistory2'];
            $exparams[$i]['ocaiNextInnerSeq'] = $data['ocaiNextInnerSeq'];
            $exparams[$i]['updTaxTrgtOrderItemId'] = $data['updTaxTrgtOrderItemId'];
            $exparams[$i]['updTaxTrgtOrderItemId2'] = $data['updTaxTrgtOrderItemId2'];
            $exparams[$i]['updTrgtClaimHistorySeq'] = $data['updTrgtClaimHistorySeq'];
            $exparams[$i]['sumUseAmount'] = $data['sumUseAmount'];
            $exparams[$i]['updTrgtConfirmWaitingFlgOrderSeq'] = $data['updTrgtConfirmWaitingFlgOrderSeq'];
            $exparams[$i]['cntReissueClassNot0'] = $data['cntReissueClassNot0'];

            // (以下、OrderItems関連)
//            $sql  = " SELECT oi.* ";
//            $sql .= " FROM   T_Order o ";
//            $sql .= "        INNER JOIN T_OrderItems oi ON (oi.OrderSeq = o.OrderSeq) ";
//            $sql .= " WHERE  o.P_OrderSeq  = :OrderSeq ";
//            $sql .= " AND    o.Cnl_Status  = 0 ";
//            $sql .= " AND    oi.DataClass != 4 ";
//            $sql .= " AND    oi.ValidFlg   = 1 ";
//            $exparams[$i]['oiLists'] = ResultInterfaceToArray($this->dbAdapter->query($sql)->execute(array(':OrderSeq' => $data['OrderSeq'])));
            $exparams[$i]['oiLists'] = $this->getOrderItemsNotTaxdata($data['OrderSeq']);

//            $sql  = " SELECT COUNT(oi.OrderItemId) AS cnt, SUM(oi.SumMoney) AS SumMoney FROM T_Order o ";
//            $sql .= "        INNER JOIN T_OrderItems oi ON (oi.OrderSeq = o.OrderSeq) ";
//            $sql .= " WHERE  o.P_OrderSeq = :OrderSeq ";
//            $sql .= " AND    o.Cnl_Status = 0 ";
//            $sql .= " AND    oi.DataClass = 4 ";
//            $sql .= " AND    oi.ValidFlg  = 1 ";
//            $exparams[$i]['oiTaxrow'] = $this->dbAdapter->query($sql)->execute(array(':OrderSeq' => $data['OrderSeq']))->current();
            $exparams[$i]['oiTaxrow'] = $this->getOrderItemsTaxdata($data['OrderSeq']);

            // (以下、MypageOrder関連)
            $exparams[$i]['maxMypageOrderSeq'] = $data['maxMypageOrderSeq'];
            $exparams[$i]['updTrgtMypageOrderSeq'] = $data['updTrgtMypageOrderSeq'];
            $exparams[$i]['accessKey'] = "";
            $exparams[$i]['random'] = "";
            $exparams[$i]['phone'] = "";
            $exparams[$i]['loginId'] = "";
            if ($data['maxMypageOrderSeq'] == -1) {
                $makeInfo = array();
                $isSuccess = $logicmo->makeCreateInfo($data['OrderSeq'], $data['custSearchPhone'], $makeInfo);
                if ($isSuccess) {
                    $exparams[$i]['accessKey']  = $makeInfo['accessKey'];
                    $exparams[$i]['random']     = $makeInfo['random'];
                    $exparams[$i]['phone']      = $makeInfo['phone'];
                    $exparams[$i]['loginId']    = $makeInfo['loginId'];
                }
            }

            // (以下、Smbcpa関連) ●NOTE. SMBCﾊﾞｰﾁｬﾙ口座は、Cb[直営]時のみ確保する。また、算出した{$cntLimit}分の確保が必ず成功すると仮定している
            $exparams[$i]['reserveSmbcpaAccountSeq'] = -1;
            $exparams[$i]['usageHistorySeqs'] = "";
            $exparams[$i]['prevSmbcpaAccountSeq'] = -1;
            if (0 == (int)$data['OemId']) {
                $exparams[$i]['reserveSmbcpaAccountSeq'] = $smbcpaAccSeqs[$idxAccSeqs];
                $exparams[$i]['usageHistorySeqs'] = $this->dbAdapter->query(" SELECT IFNULL(GROUP_CONCAT(UsageHistorySeq), '') AS usageHistorySeqs FROM T_SmbcpaAccountUsageHistory WHERE AccountSeq = :AccountSeq "
                        )->execute(array(':AccountSeq' => $exparams[$i]['reserveSmbcpaAccountSeq']))->current()['usageHistorySeqs'];
                $rowsa = $mdlsa->fetchClaimingAccountByOrderSeq($data['OrderSeq']);
                $exparams[$i]['prevSmbcpaAccountSeq'] = ($rowsa) ? $rowsa['AccountSeq'] : -1;

                $idxAccSeqs++;
            }
            // EXパラメタ(ここまで)

            $i++;
        }

        // ジョブ転送
        $this->jobTransfer('chkCsv', $list, $fldName, $exparams);

        return;
    }

    /**
     * (PK予約目的)SMBCバーチャル口座を後方より、且つ、住み分けて取得する
     *
     * @param string $fldName フィールド名 例)"Rw1"
     * @param array $datas 主SQL抽出結果
     * @return array SMBCバーチャル口座一覧
     * @see (20201029_1350現在)Cb[直営]時のみ確保する。また、算出した{$cntLimit}分の確保が必ず成功すると仮定している
     */
    protected function getReserveSmbcpaAccountSeq($fldName, $datas)
    {
        // (取得件数算出(※Cb[直営]のみ件数加算対象))
        $cntLimit = 0;
        foreach ($datas as $data) {
            if (0 == (int)$data['OemId']) {
                $cntLimit++;
            }
        }

        // (取得と戻り設定)
        $sql  = " SELECT acc.AccountSeq ";
        $sql .= " FROM   T_SmbcpaAccount acc FORCE INDEX (Idx_T_SmbcpaAccount03) ";
        $sql .= "        INNER JOIN T_SmbcpaAccountGroup grp ON (grp.AccountGroupId = acc.AccountGroupId) ";
        $sql .= " WHERE  IFNULL(grp.ReturnedFlg, 0) = 0 ";
        $sql .= " AND    acc.Status = 0 ";
        if      ($fldName == "Rw1" ) { $sql .= " AND    REVERSE(acc.AccountSeq) LIKE '1%' "; }
        else if ($fldName == "Rw2" ) { $sql .= " AND    REVERSE(acc.AccountSeq) LIKE '2%' "; }
        else if ($fldName == "Rw3" ) { $sql .= " AND    REVERSE(acc.AccountSeq) LIKE '3%' "; }
        else if ($fldName == "Rw4" ) { $sql .= " AND    REVERSE(acc.AccountSeq) LIKE '4%' "; }
        else if ($fldName == "Rw5" ) { $sql .= " AND    REVERSE(acc.AccountSeq) LIKE '5%' "; }
        else if ($fldName == "Rw6" ) { $sql .= " AND    REVERSE(acc.AccountSeq) LIKE '6%' "; }
        else if ($fldName == "Rw7" ) { $sql .= " AND    REVERSE(acc.AccountSeq) LIKE '7%' "; }
        else if ($fldName == "Rw8" ) { $sql .= " AND    REVERSE(acc.AccountSeq) LIKE '8%' "; }
        else if ($fldName == "Rw9" ) { $sql .= " AND    REVERSE(acc.AccountSeq) LIKE '9%' "; }
        else if ($fldName == "Rw0" ) { $sql .= " AND    REVERSE(acc.AccountSeq) LIKE '0%' "; }
        $sql .= " ORDER BY acc.AccountSeq DESC LIMIT " . $cntLimit;

        $ri = $this->dbAdapter->query($sql)->execute(null);

        $i = 0;
        $retval = array();
        foreach ($ri as $row) {
            $retval[$i] = $row['AccountSeq'];
            $i++;
        }

        return $retval;
    }

    /**
     * ジョブ転送処理を行う
     *
     * @param string $checkClass チェック種類
     * @param array $joblist
     * @param string $fldName フィールド名 例)"Rw1"
     * @param array $exparams EXパラメタ ※主に事前用意の要素など
     * @return boolean
     */
    protected function jobTransfer($checkClass, $joblist, $fldName, $exparams) {

        $params = $joblist;

        $mdlch = new TableClaimHistory($this->dbAdapter);
        $mdls = new TableSite($this->dbAdapter);
        $mdlo = new TableOrder($this->dbAdapter);
        $logicmo = new LogicMypageOrder($this->dbAdapter);
        $mdlce = new TableClaimError($this->dbAdapter);
        $mdloi = new TableOrderItems($this->dbAdapter);
        $mdlbc = new TableBusinessCalendar($this->dbAdapter);
        $lgcOca2 = new LogicOemClaimAccount2($this->dbAdapter, $this->logger);
        $mdlcc = new TableClaimControl($this->dbAdapter);
        $mdle = new TableEnterprise($this->dbAdapter);

        // 認証関連
        $mdlsys = new TableSystemProperty($this->dbAdapter);
        $authUtil = new BaseAuthUtility($mdlsys->getHashSalt());

        // ユーザーIDの取得
        $obj = new \models\Table\TableUser($this->dbAdapter);
        $userId = $obj->getUserId(TableUser::USERCLASS_SYSTEM, TableUser::SEQ_BATCH_USER);

        // 請求関連処理SQL
        $stm = $this->dbAdapter->query($this->getBaseP_ClaimControl());

        // 初回支払期限有効日数を取得
        $validLimitDays1 = $mdlsys->getValue(TableSystemProperty::DEFAULT_MODULE, 'systeminfo', 'ValidLimitDays1');

        // 請求日を取得
        $claimDate = $this->getClaimDate();

        $i = 0;
        $transferCount = 0;
        $errorCount = 0;

        while (isset($params['OrderSeq' . $i])) {

            if (!isset($params[$checkClass . $i])) { $i++; continue; }
            $oseq = $params['OrderSeq' . $i];

            // ----------------------------------------
            // チェック処理
            // ----------------------------------------
            // 有効な注文か
            $sql = <<<EOQ
SELECT COUNT(*) AS cnt
  FROM T_Order o
 WHERE EXISTS(SELECT * FROM T_Order t WHERE t.P_OrderSeq = o.OrderSeq AND t.Cnl_Status = 0)
   AND o.OrderSeq = :OrderSeq
EOQ;
            $prm = array(
                    ':OrderSeq' => $oseq,
            );
            $ret = $this->dbAdapter->query($sql)->execute($prm)->current()['cnt'];
            if ($ret == 0) {
                // 有効な注文がいない場合はスキップ
                $i++;
                continue;
            }

            // 請求金額が０円以下か
            $sql = <<<EOQ
SELECT SUM(UseAmount) as amt
  FROM T_Order o
 WHERE o.Cnl_Status = 0
   AND o.P_OrderSeq = :OrderSeq
EOQ;
            $prm = array(
                    ':OrderSeq' => $oseq,
            );
            $amt = $this->dbAdapter->query($sql)->execute($prm)->current()['amt'];
            if ($amt <= 0) {
                $sql = ' SELECT e.CreditTransferFlg,e.AppFormIssueCond,ao.CreditTransferRequestFlg FROM T_Order o LEFT JOIN T_Enterprise e ON o.EnterpriseId=e.EnterpriseId LEFT JOIN AT_Order ao ON o.OrderSeq=ao.OrderSeq WHERE o.OrderSeq = :OrderSeq ';
                $ent = $this->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current();
                if ((($ent['CreditTransferFlg'] == 1) || ($ent['CreditTransferFlg'] == 2) || ($ent['CreditTransferFlg'] == 3)) && (($ent['AppFormIssueCond'] == 0) || ($ent['AppFormIssueCond'] == 2))) {
                    ;
                } else {
                    // ０円以下の場合は請求エラーとする
                    $mdlce->saveNew(array('OrderSeq' => $oseq, 'ErrorCode' => LogicClaimException::ERR_CODE_0YEN, 'ErrorMsg' => null));
                    $i++;
                    continue;
                }
            }

            $sql = ' SELECT o.EnterpriseId,o.SiteId FROM T_Order o WHERE o.OrderSeq = :OrderSeq ';
            $ent = $this->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current();
            $logic = new LogicClaimPrint($this->dbAdapter);
            $PrintIssueCountCd = $logic->createPrintIssueCountCdReal($oseq, 1);
//                $PrintIssueCountCd = $logic->changePrintIssueCountCd($data['ClaimPattern']);
            $mdlCpp = new TableClaimPrintPattern($this->dbAdapter);
            $stmtCpp = $mdlCpp->find($ent['EnterpriseId'], $ent['SiteId'], $PrintIssueCountCd);
            if ($stmtCpp->count() == 0) {
                $mdlce->saveNew(array('OrderSeq' => $oseq, 'ErrorCode' => LogicClaimException::ERR_CODE_PRINT_PATTERN, 'ErrorMsg' => '印刷パターンテーブル未存在'));
                $i++;
                continue;
            }
            $dataCpp = $stmtCpp->current();
            $mdlCpc = new TableClaimPrintCheck($this->dbAdapter);
            $stmtCpc = $mdlCpc->find($dataCpp['PrintFormCd'], $dataCpp['PrintTypeCd'], $dataCpp['PrintIssueCd'], $dataCpp['PrintIssueCountCd']);
            if ($stmtCpc->count() == 0) {
                $error_msg = '印刷帳票:'.$dataCpp['PrintFormCd']
                    .', 版下:'.$dataCpp['PrintTypeCd']
                    .', 発行元:'.$dataCpp['PrintIssueCd']
                    .', 発行回数:'.$dataCpp['PrintIssueCountCd']
                ;
                $mdlce->saveNew(array('OrderSeq' => $oseq, 'ErrorCode' => LogicClaimException::ERR_CODE_PRINT_PATTERN, 'ErrorMsg' => '印刷パターンマスタに存在しないデータです'."\n".$error_msg));
                $i++;
                continue;
            }
            $check = $logic->paymentCheck($dataCpp['PrintPatternCd'], $dataCpp['SpPaymentCd']);
            if ($check === false) {
                $error_msg ='印字パターン:'.$dataCpp['PrintPatternCd']
                    .', 支払方法:'.$dataCpp['SpPaymentCd']
                ;
                $mdlce->saveNew(array('OrderSeq' => $oseq, 'ErrorCode' => LogicClaimException::ERR_CODE_PAYMENT_CHECK, 'ErrorMsg' => '支払方法チェックマスタに存在しないデータです'."\n".$error_msg));
                $i++;
                continue;
            }

            // ----------------------------------------
            // ジョブ転送処理
            // ----------------------------------------
            // SMBCバーチャル口座オープン用にロック獲得を試行
            $lockItem = $this->getLockItemForSmbcpaAccountEx($mdlo->find($oseq)->current(), $fldName);

            // ジョブ転送中か
            if ($mdlch->getReservedCount($oseq) > 0) {
                try {
                    if($lockItem) {
                        $lockItem->terminate();
                    }
                } catch (\Exception $err) { ; }
                // ジョブ転送中のデータがいる場合はスキップ
                $i++;
                continue;
            }

            // ジョブ転送中か(口座振替用)
            $sql = <<<EOQ
 SELECT COUNT(1) AS cnt
 FROM T_ClaimHistory ch
   INNER JOIN T_Order o
     ON (o.OrderSeq = ch.OrderSeq)
   INNER JOIN AT_Order ao
     ON (o.OrderSeq = ao.OrderSeq)
   INNER JOIN T_Customer c
     ON (o.OrderSeq = c.OrderSeq)
   INNER JOIN T_EnterpriseCustomer ec
     ON (ec.EntCustSeq = c.EntCustSeq)
 WHERE o.OrderSeq = :OrderSeq
   AND o.ValidFlg = 1
   AND ao.CreditTransferRequestFlg > 0
   AND ec.RequestStatus = 2
EOQ;
            $cnt = $this->dbAdapter->query($sql)->execute(array(':OrderSeq'=>$oseq))->current()['cnt'];
            if (!empty($cnt) && $cnt > 0) {
                try {
                    if($lockItem) {
                        $lockItem->terminate();
                    }
                } catch (\Exception $err) { ; }
                // ジョブ転送中のデータがいる場合はスキップ
                $i++;
                continue;
            }

            try {
                //トランザクション開始
                $this->dbAdapter->getDriver()->getConnection()->beginTransaction();

                $taxRate = $mdlsys->getTaxRateAt(date('Y-m-d'));

                 // 注文商品の更新
                // (注文SEQ目線＆PK更新)
                if ($exparams[$i]['updTaxTrgtOrderItemId'] != "") {
                    $this->dbAdapter->query(" UPDATE T_OrderItems SET TaxRate = :TaxRate, UpdateId = :UpdateId, UpdateDate = :UpdateDate WHERE OrderItemId IN ( " . $exparams[$i]['updTaxTrgtOrderItemId'] . " ) "
                        )->execute(array(':TaxRate' => $taxRate, ':UpdateId' => $userId, ':UpdateDate' => date('Y-m-d H:i:s')));
                    $exparams[$i]['oiLists'] = $this->getOrderItemsNotTaxdata($oseq);
                    $exparams[$i]['oiTaxrow'] = $this->getOrderItemsTaxdata($oseq);
                }
                // (親注文SEQ目線＆PK更新 ※ただしP_OrderSeq＝OrderSeqは除く)
                if ($exparams[$i]['updTaxTrgtOrderItemId2'] != "") {
                    $this->dbAdapter->query(" UPDATE T_OrderItems SET TaxRate = :TaxRate, UpdateId = :UpdateId, UpdateDate = :UpdateDate WHERE OrderItemId IN ( " . $exparams[$i]['updTaxTrgtOrderItemId2'] . " ) "
                    )->execute(array(':TaxRate' => $taxRate, ':UpdateId' => $userId, ':UpdateDate' => date('Y-m-d H:i:s')));
                    $exparams[$i]['oiLists'] = $this->getOrderItemsNotTaxdata($oseq);
                    $exparams[$i]['oiTaxrow'] = $this->getOrderItemsTaxdata($oseq);
                }

                // 請求履歴の論理削除（初回請求書再発行のときのみ有効）
                if ($exparams[$i]['updTrgtClaimHistorySeq'] != "") {
                    $this->dbAdapter->query(" UPDATE T_ClaimHistory SET ValidFlg = 0 , UpdateId = :UpdateId , UpdateDate = :UpdateDate WHERE Seq IN ( " . $exparams[$i]['updTrgtClaimHistorySeq'] . " ) "
                        )->execute(array(':UpdateId' => $userId, ':UpdateDate' => date('Y-m-d H:i:s')));
                }

                $useAmount = $exparams[$i]['sumUseAmount'];// 請求金額

                // 支払期限
                $ldays = $mdlsys->getValue('cbadmin', 'business', 'pay.limitdays');

                $limitDate = $mdls->getLimitDateForBatch($params['SiteId' . $i], $ldays, $claimDate);
                $list = $this->listGetFromDB ( $oseq );
                $ccCnt = $mdlcc->findClaim(array('OrderSeq' => $oseq))->current();
                $order = $mdlo->find($oseq)->current();
                $ent = $mdle->find($order['EnterpriseId'])->current();

                // 口座振替申込み区分>0 の注文 かつ 加盟店顧客.申込みステータス=2（完了）の注文の場合
                if (($list['CreditTransferRequestFlg'] > 0) && ($list['RequestStatus'] == 2)) {
                    $mdle = new TableEnterprise($this->dbAdapter);
                    $lgc = new LogicCreditTransfer($this->dbAdapter);
                    $order = $mdlo->find($oseq)->current();
                    $ent = $mdle->find($order['EnterpriseId'])->current();
                    $limitDates = $lgc->getCreditTransferLimitDay($list['Deli_JournalIncDate']);
                    $limitDate = $limitDates[$ent['CreditTransferFlg']];
                }

                // 初回再発行、且つ、加盟店.初回再発行の支払期限がONの場合は初回請求書の期限日を設定する
                if (($ent['FirstReClaimLmitDateFlg'] == 1) && (!empty($ccCnt)) ) {
                    $limitDate = $ccCnt['F_LimitDate'];
                }

                // 有効期限日数を算出
                $validLimitDate = date ( 'Y-m-d', strtotime ( $claimDate . " +" . "$validLimitDays1 day"));
                if ((strtotime($limitDate) < strtotime($validLimitDate)) && ($useAmount > 0)) {
                    if (($ent['FirstReClaimLmitDateFlg'] == 1) && (!empty($ccCnt)) ) {
                        // 初回再発行、且つ、加盟店.初回再発行の支払期限がONの場合は請求書を発行する
                        $limitDate = $ccCnt['F_LimitDate'];
                    } else {
                        $this->dbAdapter->getDriver()->getConnection()->rollback();
                        // 支払期限日数が有効期限未満の場合は請求エラーとする
                        $mdlce->saveNew(array('OrderSeq' => $oseq, 'ErrorCode' => LogicClaimException::ERR_CODE_LIMIT_DAY, 'ErrorMsg' => $validLimitDays1));
                        try { if($lockItem) { $lockItem->terminate(); } } catch (\Exception $err) { ; }
                        $i++;
                        continue;
                    }
                }

                if ($params['BillingAgentFlg' . $i] == 1) {
                    if (!empty($ccCnt) && $ccCnt['ReissueCount'] > 0) {
                        // サイト情報の取得
                        $row_site = $mdls->findSite($params['SiteId' . $i])->current();

                        // 期限日までの日数
                        $ldays = $mdlsys->getValue('cbadmin', 'business', 'pay.limitdays2');

                        // 遅延起算日日数を期限日までの日数に加算する。
                        $ldays += $row_site['KisanbiDelayDays'];

                        // 支払期限日算出
                        if ($ent['FirstReClaimLmitDateFlg'] == 1) {
                            $limitDate = $ccCnt['F_LimitDate'];
                        } else {
                            $limitDate = date('Y-m-d', strtotime($claimDate .' +' . $ldays . 'day'));
                        }
                    }
                }

                // 請求履歴データの設定
                $MailFlg = 0;
                $PrintedFlg = 0;
                $CreditTransferMailFlg = 0;
                $ClaimFileOutputClass = 0;
                $ClaimFileOutputClass = 0;

                if (($list ['CreditTransferRequestFlg'] > 0) && ($list ['RequestStatus'] == 2)) {
                    $PrintedFlg = 1;
                }

                // 請求書発行通知メール&請求ファイル出力区分の更新
                if ( ($list['CreditTransferRequestFlg'] > 0) && ($list['RequestStatus'] == 2) ) {
                    $MailFlg = 1;
                } else {
                    $ClaimFileOutputClass = 1;
                }

                //口振請求書通知メールの更新
                if ( ($list['CreditTransferRequestFlg'] == 1) && ($list['RequestStatus'] == null || $list['RequestStatus'] == 9) ) {
                    $ent = $mdle->find($list['EnterpriseId'])->current();
                    $CreditTransferMailFlg = 0;
                    if (($ent['AppFormIssueCond'] == 0) || ($ent['AppFormIssueCond'] == 2)) {
                        // 申込用紙発行条件が「0：発行しない」、「2：請求金額0円時」の場合はメール送信しない
                        $CreditTransferMailFlg = 1;
                    }
                } else {
                    $CreditTransferMailFlg = 1;
                }

                //口座振替申請ステータス
                $CreditTransferRequestStatus = null;
                if ($list['CreditTransferRequestFlg'] > 0) {
                    $CreditTransferRequestStatus = $list['RequestStatus'];
                }

                // 請求履歴の作成
                $data = array(
                        'OrderSeq' => $oseq,                                                                                    // 注文Seq
                        'ClaimDate' => $claimDate,                                                                              // 請求日
                        'ClaimCpId' => TableUser::SEQ_BATCH_USER,                                                               // 請求担当者
                        'ClaimPattern' => 1,                                                                                    // 請求パターン（初回請求）
                        'LimitDate' => $limitDate,                                                                              // 支払期限
                        'DamageDays' => 0,                                                                                      // 遅延日数
                        'DamageInterestAmount' => 0,                                                                            // 遅延損害金
                        'ClaimFee' => 0,                                                                                        // 請求手数料
                        'AdditionalClaimFee' => 0,                                                                              // 請求追加手数料
                        'PrintedFlg' => 0,                                                                                      // 印刷－処理フラグ
                        'MailFlg' => $MailFlg,                                                                                  // 請求書発行通知メール
                        'CreditTransferMailFlg' => $CreditTransferMailFlg,                                                      // 口振請求書通知メール
                        'ClaimFileOutputClass' => $ClaimFileOutputClass,                                                        // 請求ファイル出力区分
                        'EnterpriseBillingCode' => null,                                                                        // 同梱ツールアクセスキー
                        'ClaimAmount' => $useAmount,                                                                            // 請求金額
                        'CreditTransferRequestStatus' => $CreditTransferRequestStatus,                                          // 口座振替申請ステータス
                        'RegistId' => $userId,                                                                                  // 登録者
                        'UpdateId' => $userId,                                                                                  // 更新者
                        'ValidFlg' => 1,                                                                                        // 有効ﾌﾗｸﾞ
                );

                $exparams[$i]['ClaimPattern'] = 1;
                try {
                    $hisSeq = $lgcOca2->SaveNewForBatch2($data, $exparams[$i]);
                    // 印刷－処理フラグの更新
                    if ($PrintedFlg == 1) {
                        $chdata = $mdlch->find($hisSeq)->current();
                        if (($list['AppFormIssueCond'] == 2) && ($list['CreditTransferRequestFlg'] == 2) && ($chdata['ClaimAmount'] == 0)) {
                            // 加盟店顧客マスタの申込ステ-タスが完了でも、請求金額０円時＆利用する（紙面）＆請求額０円の場合は請求書を出力する
                            $PrintedFlg = 0;
                        } else {
                            // 請求履歴の更新
                            $hisSeq = $mdlch->saveUpdatePrintedFlg ( $hisSeq );
                        }
                    }
                } catch ( LogicClaimException $e ) {
                    $this->dbAdapter->getDriver ()->getConnection ()->rollback ();
                    // SMBC連携エラーの場合は請求エラーとする
                    $mdlce->saveNew ( array (
                            'OrderSeq' => $oseq,
                            'ErrorCode' => $e->getCode (),
                            'ErrorMsg' => $e->getMessage ()
                    ) );
                    try {
                        if ($lockItem) {
                            $lockItem->terminate ();
                        }
                    } catch ( \Exception $err ) {
                        ;
                    }
                    $i ++;
                    continue;
                } catch ( \Exception $e ) {
                    throw $e;
                }

                //OEMID取得
                $oem_id = $mdlo->getOemId($oseq);

                // OEM請求手数料は初回のみ取る
                if(!is_null($oem_id) && $oem_id != 0 && $exparams[$i]['cntReissueClassNot0'] == 0){
                    (new TableOemClaimFee($this->dbAdapter))->saveOemClaimFee($oseq, $userId);// (OEM請求手数料書き込み)
                }

                // 注文の確定待ちフラグをアップ
                $this->dbAdapter->query(" UPDATE T_Order SET ConfirmWaitingFlg = 1, UpdateId = :UpdateId, UpdateDate = :UpdateDate WHERE OrderSeq IN ( " . $exparams[$i]['updTrgtConfirmWaitingFlgOrderSeq'] . " ) "
                    )->execute(array(':UpdateId' => $userId, ':UpdateDate' => date('Y-m-d H:i:s')));

                // 注文マイページを作成する
                $makeInfo = array('accessKey' => $exparams[$i]['accessKey'], 'random' => $exparams[$i]['random'], 'phone' => $exparams[$i]['phone'], 'loginId' => $exparams[$i]['loginId']);
                $logicmo->createMypageOrderEx($oseq, $limitDate, $oem_id, $userId, $authUtil, $exparams[$i]['maxMypageOrderSeq'], $exparams[$i]['updTrgtMypageOrderSeq'], $makeInfo);

                $this->dbAdapter->getDriver()->getConnection()->commit();
            } catch (\Exception $e) {
                $this->dbAdapter->getDriver()->getConnection()->rollback();

                // ロックを獲得していたら開放
                try {
                    if($lockItem) {
                        $lockItem->terminate();
                    }
                } catch (\Exception $err) { ; }

                // 処理失敗
                throw $e;

            }

            $i++;
            // ロックを獲得していたら開放
            try {
                if($lockItem) {
                    $lockItem->terminate();
                }
            } catch (\Exception $e) { ; }
        }
    }

    /**
     * 口座振替申込み区分, 加盟店顧客.申込みステータスを取得する
     */
    protected function listGetFromDB($oseq) {
        $sql = <<<EOQ
SELECT  ao.CreditTransferRequestFlg     /* 口座振替申込み区分 */
,       ec.RequestStatus                /* 申込みステータス */
,       sit.FirstCreditTransferClaimFee /* 口振用初回請求手数料 */
,       sit.CreditTransferClaimFee      /* 口振用請求手数料 */
,       os.Deli_JournalIncDate          /* 伝票番号入力日 */
,       e.AppFormIssueCond              /* 申込用紙発行条件 */
FROM    T_Order o
        INNER JOIN T_OrderSummary os ON (os.OrderSeq = o.OrderSeq)
        INNER JOIN AT_Order ao ON (ao.OrderSeq = o.OrderSeq)
        INNER JOIN T_Customer c ON (c.OrderSeq = o.OrderSeq)
        INNER JOIN T_EnterpriseCustomer ec ON (ec.EntCustSeq = c.EntCustSeq)
        INNER JOIN T_Site sit ON( sit.SiteId = o.SiteId)
        INNER JOIN T_Enterprise e ON( o.EnterpriseId = e.EnterpriseId )
WHERE   o.OrderSeq = :OrderSeq
EOQ;
        $prm = array(
                ':OrderSeq' => $oseq,
        );
        $list = $this->dbAdapter->query($sql)->execute($prm)->current();

        return $list;
    }

    /**
     * ジョブ転送を行う(督促)
     *
     * @param int $claimPattern 請求パターン
     * @param boolean $isMypage マイページか？
     */
    protected function jobTransferTokusoku($claimPattern, $isMypage) {
        $mdlo = new TableOrder($this->dbAdapter);
        $mdls = new TableSite($this->dbAdapter);
        $mdlcc = new TableClaimControl($this->dbAdapter);
        $mdlch = new TableClaimHistory($this->dbAdapter);
        $mdlsys = new TableSystemProperty($this->dbAdapter);
        $mdlri = new TableReclaimIndicate($this->dbAdapter);
        $mdlce = new TableClaimError($this->dbAdapter);
        $mdle = new TableEnterprise($this->dbAdapter);

        // ユーザーIDの取得
        $obj = new \models\Table\TableUser($this->dbAdapter);
        $userId = $obj->getUserId(TableUser::USERCLASS_SYSTEM, TableUser::SEQ_BATCH_USER);

        // 督促支払期限有効日数を取得
        $validLimitDays2 = $mdlsys->getValue(TableSystemProperty::DEFAULT_MODULE, 'systeminfo', 'ValidLimitDays2');

        // 請求日を取得
        $claimDate = $this->getClaimDate();

        // a. ジョブ転送が必要な対象を抽出
        $sql = <<<EOQ
SELECT CLM.*, ORD.NewSystemFlg, ORD.SiteId
     , F_GetCampaignVal(ORD.EnterpriseId, ORD.SiteId, DATE(NOW()), 'ReClaimFee')  AS ReClaimFee
     , NULL As RiSeq
     , NULL As RiClaimCpId
     , NULL As RiDamageDays
     , NULL As RiDamageBaseDate
     , NULL As RiDamageInterestAmount
     , NULL As RiClaimFee
     , NULL As RiAdditionalClaimFee
     , NULL As RiClaimAmount
     , IFNULL((SELECT COUNT(*) FROM T_EnterpriseCampaign AS TEC WHERE TEC.EnterpriseId = ORD.EnterpriseId AND TEC.SiteId = ORD.SiteId AND DATE(NOW()) BETWEEN TEC.DateFrom AND TEC.DateTo), 0) AS StartCampaignCount
     , S.ReClaimFeeSetting
     , S.ReClaimFee1, S.ReClaimFee3, S.ReClaimFee4, S.ReClaimFee5, S.ReClaimFee6, S.ReClaimFee7
     , (CASE WHEN S.ReClaimFeeStartRegistDate <= ORD.RegistDate AND S.ReClaimFeeStartDate <= ORD.ReceiptOrderDate
             THEN 1
             WHEN S.ReClaimFeeStartRegistDate IS NULL AND S.ReClaimFeeStartDate <= ORD.ReceiptOrderDate
             THEN 1
             WHEN S.ReClaimFeeStartRegistDate <= ORD.RegistDate AND S.ReClaimFeeStartDate IS NULL
             THEN 1
             WHEN S.ReClaimFeeStartRegistDate IS NULL AND S.ReClaimFeeStartDate IS NULL
             THEN 1
        ELSE 0
        END) AS ReClaimFeeStartFlg
     , ORD.EnterpriseId
  FROM T_ClaimControl CLM
       INNER JOIN T_Order ORD
               ON ORD.OrderSeq = CLM.OrderSeq
       INNER JOIN ( SELECT  t.P_OrderSeq
                           ,MAX(IFNULL(t.LetterClaimStopFlg, 0))   AS LetterClaimStopFlg        -- 最大のストップフラグが1の場合＝ストップしたい注文あり
                      FROM T_Order t
                     WHERE t.Cnl_Status = 0
                       AND t.DataStatus IN (51, 61)
                     GROUP BY t.P_OrderSeq
                  ) po
               ON po.P_OrderSeq = ORD.OrderSeq
       INNER JOIN T_ClaimControl cc
               ON ORD.OrderSeq = cc.OrderSeq
       INNER JOIN T_Customer C ON C.OrderSeq = ORD.OrderSeq
       INNER JOIN T_EnterpriseCustomer EC ON EC.EntCustSeq = C.EntCustSeq
       INNER JOIN T_ManagementCustomer MC ON MC.ManCustId = EC.ManCustId
       INNER JOIN T_Site S ON S.SiteId = ORD.SiteId
       INNER JOIN AT_Order AO ON AO.OrderSeq = ORD.OrderSeq
 WHERE 1 = 1
   AND (po.LetterClaimStopFlg = 0 AND IFNULL(MC.RemindStopFlg, 0) = 0)
   AND ( SELECT COUNT(1) FROM T_ClaimHistory WHERE OrderSeq = ORD.OrderSeq AND PrintedFlg = 0 AND ValidFlg = 1 ) = 0
   AND IFNULL(AO.ExtraPayType, 0) <> 1
   AND CLM.ReissueClass = 0
EOQ;

        $sql .= "   AND cc.ClaimedBalance > 0 ";        // 2015/12/04 Y.Suzuki Add 暫定対応（残高が0のﾃﾞｰﾀは出力しない）

        // $isMypageによる分岐
        if ($isMypage) {
            $sql .= "   AND cc.MypageReissueClass IN ( 1, 2 ) ";
            $prm = array();
        }
        else {
            $sql .= "    AND cc.LimitDate <= :ClaimPatterCondition1 ";
            $sql .= "    AND cc.MypageReissueClass IN ( 0, 91, 92 )  ";
            $sql .= "    AND ( SELECT MAX(ClaimPattern) FROM T_ClaimHistory tmpch WHERE OrderSeq = ORD.OrderSeq AND PrintedFlg = 1 AND ValidFlg = 1 ) = :ClaimPattern ";

            // 再請求指示分は含めない
            $sql .= "    AND ( SELECT COUNT(1) FROM T_ReclaimIndicate WHERE OrderSeq = ORD.ORDERseq AND IndicatedFlg = 0 AND ValidFlg = 1 ) = 0 ";
            // 督促停止区分がTRUEの場合、対象としない
            $sql .= "    AND S.RemindStopClass = 0 ";
            // union all で再請求指示データ基点のJOB転送対象データも取得する
            $sql .= <<<EOQ
UNION ALL
SELECT CLM.*, ORD.NewSystemFlg, ORD.SiteId
     , NULL  AS ReClaimFee
     , RI.Seq As RiSeq
     , RI.ClaimCpId As RiClaimCpId
     , RI.DamageDays As RiDamageDays
     , RI.DamageBaseDate As RiDamageBaseDate
     , RI.DamageInterestAmount As RiDamageInterestAmount
     , RI.ClaimFee As RiClaimFee
     , RI.AdditionalClaimFee As RiAdditionalClaimFee
     , RI.ClaimAmount As RiClaimAmount
     , IFNULL((SELECT COUNT(*) FROM T_EnterpriseCampaign AS TEC WHERE TEC.EnterpriseId = ORD.EnterpriseId AND TEC.SiteId = ORD.SiteId AND DATE(NOW()) BETWEEN TEC.DateFrom AND TEC.DateTo), 0) AS StartCampaignCount
     , S.ReClaimFeeSetting
     , S.ReClaimFee1, S.ReClaimFee3, S.ReClaimFee4, S.ReClaimFee5, S.ReClaimFee6, S.ReClaimFee7
     , (CASE WHEN S.ReClaimFeeStartRegistDate <= ORD.RegistDate AND S.ReClaimFeeStartDate <= ORD.ReceiptOrderDate
             THEN 1
             WHEN S.ReClaimFeeStartRegistDate IS NULL AND S.ReClaimFeeStartDate <= ORD.ReceiptOrderDate
             THEN 1
             WHEN S.ReClaimFeeStartRegistDate <= ORD.RegistDate AND S.ReClaimFeeStartDate IS NULL
             THEN 1
             WHEN S.ReClaimFeeStartRegistDate IS NULL AND S.ReClaimFeeStartDate IS NULL
             THEN 1
        ELSE 0
        END) AS ReClaimFeeStartFlg
     , ORD.EnterpriseId
              FROM T_ReclaimIndicate RI
       INNER JOIN T_ClaimControl CLM
               ON CLM.OrderSeq  = RI.OrderSeq
       INNER JOIN T_Order ORD
               ON ORD.OrderSeq = CLM.OrderSeq
       INNER JOIN ( SELECT  t.P_OrderSeq
                           ,MAX(IFNULL(t.LetterClaimStopFlg, 0))   AS LetterClaimStopFlg
                      FROM T_Order t
                     WHERE t.Cnl_Status = 0
                       AND t.DataStatus IN (51, 61)
                     GROUP BY t.P_OrderSeq
                  ) po
               ON po.P_OrderSeq = ORD.OrderSeq
       INNER JOIN T_ClaimControl cc
               ON ORD.OrderSeq = cc.OrderSeq
       INNER JOIN T_Customer C ON C.OrderSeq = ORD.OrderSeq
       INNER JOIN T_EnterpriseCustomer EC ON EC.EntCustSeq = C.EntCustSeq
       INNER JOIN T_ManagementCustomer MC ON MC.ManCustId = EC.ManCustId
       INNER JOIN T_Site S ON S.SiteId = ORD.SiteId
       INNER JOIN AT_Order AO ON AO.OrderSeq = ORD.OrderSeq
 WHERE 1 = 1
   AND (po.LetterClaimStopFlg = 0 AND IFNULL(MC.RemindStopFlg, 0) = 0)
   AND ( SELECT COUNT(1) FROM T_ClaimHistory WHERE OrderSeq = ORD.OrderSeq AND PrintedFlg = 0 AND ValidFlg = 1 ) = 0
   AND IFNULL(AO.ExtraPayType, 0) <> 1
   AND CLM.ReissueClass = 0
   AND cc.ClaimedBalance > 0
   AND RI.ValidFlg = 1
EOQ;

            $sql .= "   AND RI.IndicatedFlg = 0 ";
            $sql .= "   AND RI.ClaimPattern = :RiClaimPattern ";


            $row_mc = $this->dbAdapter->query(" SELECT Class1, Class2 FROM M_Code WHERE CodeId = 183 AND KeyCode = :KeyCode "
            )->execute(array(':KeyCode' => $claimPattern))->current();

            $picup_pattern = 0;
            if      ($claimPattern == 9) { $picup_pattern = 8; }  // 再請求７出力チェック
            else if ($claimPattern == 8) { $picup_pattern = 7; }  // 再請求６出力チェック
            else if ($claimPattern == 7) { $picup_pattern = 6; }  // 再請求５出力チェック
            else if ($claimPattern == 6) { $picup_pattern = 4; }  // 再請求４出力チェック
            else if ($claimPattern == 4) { $picup_pattern = 2; }  // 再請求３出力チェック
            else if ($claimPattern == 2) { $picup_pattern = 1; }  // 再請求１出力チェック

            $prm = array(
                    ':ClaimPatterCondition1' => date('Y-m-d', strtotime($claimDate . ' -' . $row_mc['Class1'] . 'day')),
                    ':ClaimPattern' => $picup_pattern,
                    ':RiClaimPattern' => $claimPattern,
            );
        }

        $ri = $this->dbAdapter->query($sql)->execute($prm);

        $executed_oseqs = array();
        foreach ($ri as $row) {

            if (in_array($row['OrderSeq'], $executed_oseqs)) {
                $this->logger->err('Critical-Warning : ' . $row['OrderSeq']);
                continue;
            }
            else {
                $executed_oseqs[] = $row['OrderSeq'];
            }

            // ----------------------------------------
            // b. ジョブ転送処理を実施
            // ----------------------------------------
            // SMBCバーチャル口座オープン用にロック獲得を試行
            $order = $mdlo->find($row['OrderSeq'])->current();

            $lockItem = $this->getLockItemForSmbcpaAccountEx($order, 'Re');

            // 請求履歴が有効かどうか判定
            if ($mdlch->getReservedCount($row['OrderSeq']) > 0) {
                try {
                    if($lockItem) {
                        $lockItem->terminate();
                    }
                } catch (\Exception $err) { ; }
                // ジョブ転送中のデータがいる場合はスキップ
                continue;
            }

            try {
                //トランザクション開始
                $this->dbAdapter->getDriver()->getConnection()->beginTransaction();

                // 請求管理データ取得(取得済み)

                if (isset($row['RiSeq'])){
                    //再請求指示が基点のデータの場合
                    // 請求担当者
                    $ClaimCpId = $row['RiClaimCpId'];

                    // 遅延日数
                    $damageDays = $row['RiDamageDays'];
                    // 遅延日数算出基準日
                    $strDamageBaseDate = $row['RiDamageBaseDate'];
                    // 遅延損害金
                    $damageInterest = $row['RiDamageInterestAmount'];
                    // 請求手数料
                    $reClaimFee = $row['RiClaimFee'];
                    // 請求追加手数料
                    $AdditionalClaimFee = $row['RiAdditionalClaimFee'];

                    // 請求金額
                    $claimAmount = $row['RiClaimAmount'];

                    // 請求書発行通知メール
                    $MailFlg = 1;


                }else{
                    // 請求担当者
                    $ClaimCpId = TableUser::SEQ_BATCH_USER;

                    // 請求金額の再取得
                    // 原則画面と同じになるが、一部キャンセルされた場合を想定
                    $useAmount = $this->dbAdapter->query(" SELECT SUM(UseAmount) AS UseAmount FROM T_Order o WHERE o.Cnl_Status = 0 AND o.P_OrderSeq = :OrderSeq "
                    )->execute(array(':OrderSeq' => $row['OrderSeq']))->current()['UseAmount'];

                    // 遅延日数算出
                    $damageDays = BaseGeneralUtils::CalcSpanDaysFromString($row['DamageBaseDate'], date('Y-m-d'));
                    if ($damageDays < 0) {
                        $damageDays = 0;
                    }

                    // 遅延損害金算出
                    $damageInterest = 0;

                    // 請求手数料
                    $reClaimFee = 0;

                    // 請求管理の【マイページ請求書再発行区分】が 1 又は 2 の場合
                    if ($isMypage) {
                        // マイページ請求書再発行手数料
                        $reClaimFee = $row['MypageReissueReClaimFee'];

                    } else {
                        // 開催中のキャンペーンがない
                        // 個別指定再請求手数料の反映開始日を越えている
                        // 再請求手数料が個別指定
                        if (($row['StartCampaignCount'] == 0)
                        && ($row['ReClaimFeeStartFlg'] == 1)
                        && ($row['ReClaimFeeSetting'] == 1)
                        ) {
                            switch ($claimPattern) {
                                case 2: // 再請求１
                                    $reClaimFee = $row['ReClaimFee1'];
                                    break;
                                case 4: // 再請求３
                                    $reClaimFee = $row['ReClaimFee3'];
                                    break;
                                case 6: // 再請求４
                                    $reClaimFee = $row['ReClaimFee4'];
                                    break;
                                case 7: // 再請求５
                                    $reClaimFee = $row['ReClaimFee5'];
                                    break;
                                case 8: // 再請求６
                                    $reClaimFee = $row['ReClaimFee6'];
                                    break;
                                case 9: // 再請求７
                                    $reClaimFee = $row['ReClaimFee7'];
                                    break;
                                default: // その他
                                    $reClaimFee = $row['ReClaimFee'];
                                    break;
                            }
                        } else {
                            $reClaimFee = $row['ReClaimFee'];
                        }

                        // 再請求手数料を税込み金額に変換
                        // サイト/キャンペーンから取得した金額のみ変換
                        $reClaimFee = $mdlsys->getIncludeTaxAmount(date('Y-m-d'), $reClaimFee);

                        // 再請求手数料＋請求手数料
                        $reClaimFee += $row['ClaimFee'];
                    }

                    // 請求額算出
                    $claimAmount = $useAmount + $reClaimFee + $damageInterest;

                    $strDamageBaseDate = ($damageDays > 0 ? $row['DamageBaseDate'] : null);

                    // 請求追加手数料
                    $AdditionalClaimFee = 0;

                    // 請求書発行通知メール
                    $MailFlg = ($isMypage) ? 1 : 0;

                }

                // サイト情報の取得
                $row_site = $mdls->findSite($row['SiteId'])->current();

                // 期限日までの日数
                $ldays = $mdlsys->getValue('cbadmin', 'business', 'pay.limitdays2');

                // 遅延起算日日数を期限日までの日数に加算する。
                $ldays += $row_site['KisanbiDelayDays'];

                // 支払期限日算出
                $limitDate = date('Y-m-d', strtotime($claimDate .' +' . $ldays . 'day'));

                // 有効期限日数を算出
                $validLimitDate = date('Y-m-d', strtotime($claimDate . " +" . "$validLimitDays2 day"));
                if (strtotime($limitDate) < strtotime($validLimitDate)) {
                    $this->dbAdapter->getDriver()->getConnection()->rollback();
                    // 支払期限日数が有効期限未満の場合は請求エラーとする
                    $mdlce->saveNew(array('OrderSeq' => $row['OrderSeq'], 'ErrorCode' => LogicClaimException::ERR_CODE_LIMIT_DAY, 'ErrorMsg' => $validLimitDays2));
                    try { if($lockItem) { $lockItem->terminate(); } } catch (\Exception $err) { ; }
                    continue;
                }

                // 強制解約日出力
                $ent = $mdle->find($row['EnterpriseId'])->current();
                $cancelNoticePrint = false;
                if (($ent['ForceCancelDatePrintFlg'] == 1) && ($ent['ForceCancelClaimPattern'] == $claimPattern)) {
                    if (preg_match("/^強制解約日=[0-9]{4}\/[0-9]{2}\/[0-9]{2}$/u", $order['Ent_Note'])) {
                        $work = str_replace("強制解約日=", "", $order['Ent_Note']);
                        if (!CoralValidate::isDate($work)) {
                            $this->dbAdapter->getDriver()->getConnection()->rollback();
                            // 支払期限日数が有効期限未満の場合は請求エラーとする
                            $mdlce->saveNew(array('OrderSeq' => $row['OrderSeq'], 'ErrorCode' => LogicClaimException::ERR_CODE_FORCE_CANCEL_DATE, 'ErrorMsg' => $work));
                            try { if($lockItem) { $lockItem->terminate(); } } catch (\Exception $err) { ; }
                            continue;
                        }
                    }
                }

                // 延滞起算日がNULLの場合は今回の支払期限を次回からの延滞起算日にするためDBへ書き込み
                if (strlen($row['DamageBaseDate']) == 0) {
                    $mdlcc->saveUpdate(array('DamageBaseDate' => $limitDate, 'UpdateId' => $userId), $row['ClaimId']);
                }

                //
                $logic = new LogicClaimPrint($this->dbAdapter);
                $PrintIssueCountCd = $logic->createPrintIssueCountCdReal($row['OrderSeq'], $claimPattern);
//                $PrintIssueCountCd = $logic->changePrintIssueCountCd($data['ClaimPattern']);
                $mdlCpp = new TableClaimPrintPattern($this->dbAdapter);
                $stmtCpp = $mdlCpp->find($row['EnterpriseId'], $row['SiteId'], $PrintIssueCountCd);
                if ($stmtCpp->count() == 0) {
                    $this->dbAdapter->getDriver()->getConnection()->rollback();
                    $mdlce->saveNew(array('OrderSeq' => $row['OrderSeq'], 'ErrorCode' => LogicClaimException::ERR_CODE_PRINT_PATTERN, 'ErrorMsg' => '印刷パターンテーブル未存在'));
                    try { if($lockItem) { $lockItem->terminate(); } } catch (\Exception $err) { ; }
                    continue;
                }
                $dataCpp = $stmtCpp->current();
                $mdlCpc = new TableClaimPrintCheck($this->dbAdapter);
                $stmtCpc = $mdlCpc->find($dataCpp['PrintFormCd'], $dataCpp['PrintTypeCd'], $dataCpp['PrintIssueCd'], $dataCpp['PrintIssueCountCd']);
                if ($stmtCpc->count() == 0) {
                    $this->dbAdapter->getDriver()->getConnection()->rollback();
                    $error_msg = '印刷帳票:'.$dataCpp['PrintFormCd']
                        .', 版下:'.$dataCpp['PrintTypeCd']
                        .', 発行元:'.$dataCpp['PrintIssueCd']
                        .', 発行回数:'.$dataCpp['PrintIssueCountCd']
                    ;
                    $mdlce->saveNew(array('OrderSeq' => $row['OrderSeq'], 'ErrorCode' => LogicClaimException::ERR_CODE_PRINT_PATTERN, 'ErrorMsg' => '印刷パターンマスタに存在しないデータです'."\n".$error_msg));
                    try { if($lockItem) { $lockItem->terminate(); } } catch (\Exception $err) { ; }
                    continue;
                }
                $check = $logic->paymentCheck($dataCpp['PrintPatternCd'], $dataCpp['SpPaymentCd']);
                if ($check === false) {
                    $this->dbAdapter->getDriver()->getConnection()->rollback();
                    $error_msg ='印字パターン:'.$dataCpp['PrintPatternCd']
                        .', 支払方法:'.$dataCpp['SpPaymentCd']
                    ;
                    $mdlce->saveNew(array('OrderSeq' => $row['OrderSeq'], 'ErrorCode' => LogicClaimException::ERR_CODE_PAYMENT_CHECK, 'ErrorMsg' => '支払方法チェックマスタに存在しないデータです'."\n".$error_msg));
                    try { if($lockItem) { $lockItem->terminate(); } } catch (\Exception $err) { ; }
                    continue;
                }


                // 請求履歴の作成
                $data = array(
                        'OrderSeq' => $row['OrderSeq'],                                // 注文Seq
                        'ClaimDate' => $claimDate,                                     // 請求日
                        'ClaimCpId' => $ClaimCpId,                                     // 請求担当者
                        'ClaimPattern' => $claimPattern,                               // 請求パターン
                        'LimitDate' => $limitDate,                                     // 支払期限
                        'DamageDays' => $damageDays,                                   // 遅延日数
                        'DamageBaseDate' => $strDamageBaseDate,                        // 遅延日数算出基準日
                        'DamageInterestAmount' => $damageInterest,                     // 遅延損害金
                        'ClaimFee' => $reClaimFee,                                     // 請求手数料
                        'AdditionalClaimFee' => $AdditionalClaimFee,                   // 請求追加手数料
                        'PrintedFlg' => 0,                                             // 印刷－処理フラグ
                        'MailFlg' => $MailFlg,                                         // 請求書発行通知メール
                        'EnterpriseBillingCode' => null,                               // 同梱ツールアクセスキー
                        'ClaimAmount' => $claimAmount,                                 // 請求金額
                        'ClaimId' => $row['ClaimId'],                                  // 請求ID
                        'CreditTransferRequestStatus' => null,                         // 口座振替申請ステータス
                        'RegistId' => $userId,                                         // 登録者
                        'UpdateId' => $userId,                                         // 更新者
                );

                try {
                    if(date('Y-m-d') > '2019-09-30'){
                        $hisSeq = $mdlch->saveNewForBatch2($row['OrderSeq'], $data, $this->logger, array('ClaimPattern' => $claimPattern));
                    }else{
                        $hisSeq = $mdlch->saveNewForBatch($row['OrderSeq'], $data, $this->logger);
                    }

                } catch(LogicClaimException $e) {
                    $this->dbAdapter->getDriver()->getConnection()->rollback();
                    // SMBC連携エラーの場合は請求エラーとする
                    $mdlce->saveNew(array('OrderSeq' => $row['OrderSeq'], 'ErrorCode' => $e->getCode(), 'ErrorMsg' => $e->getMessage()));
                    try { if($lockItem) { $lockItem->terminate(); } } catch (\Exception $err) { ; }
                    continue;
                } catch(\Exception $e) {
                    throw $e;
                }

                $uOrder = array(
                        'ConfirmWaitingFlg'  => '1',
                        'UpdateId'           => $userId,
                );

                $mdlo->saveUpdateWhere($uOrder, array('P_OrderSeq' => $row['OrderSeq']));

                // c. ジョブ転送を行った請求履歴に対して、更新を行う
                $sql  = " UPDATE T_ClaimHistory ";
                $sql .= " SET    PrintedStatus = 1 ";
                $sql .= " ,      UpdateId = :UpdateId ";
                $sql .= " ,      UpdateDate = :UpdateDate ";
                $sql .= " WHERE  OrderSeq = :OrderSeq ";
                $sql .= " AND    PrintedFlg = 0 ";
                $sql .= " AND    ValidFlg = 1 ";

                $this->dbAdapter->query($sql)->execute(array(
                        ':OrderSeq' => $row['OrderSeq'],
                        ':UpdateId' => $userId,
                        ':UpdateDate' => date('Y-m-d H:i:s')));

                if (isset($row['RiSeq'])){
                    // 再請求指示データ経由のデータ対象に処理した場合は再請求指示データも更新
                    $mdlri->updateIndicated($row['OrderSeq'],$userId,$hisSeq);
                }

                $this->dbAdapter->getDriver()->getConnection()->commit();

            } catch (\Exception $e) {
                $this->dbAdapter->getDriver()->getConnection()->rollback();

                // ロックを獲得していたら開放
                try {
                    if($lockItem) {
                        $lockItem->terminate();
                    }
                } catch (\Exception $err) { ; }

                // 処理失敗
                throw $e;
            }

            // ロックを獲得していたら開放
            try {
                if($lockItem) {
                    $lockItem->terminate();
                }
            } catch (\Exception $e) { ; }
        }
        return;
    }

    /**
     * SMBCバーチャル口座オープン用のロックアイテムを獲得する
     *
     * @access protected
     * @param array 対象注文の行オブジェクト
     * @return \models\Logic\ThreadPool\LogicThreadPoolItem | null
     */
    protected function getLockItemForSmbcpaAccountEx($orderRow, $fldName)
    {
        if(!$orderRow) return null;

        $smbcpaTable = new \models\Table\TableSmbcpa($this->dbAdapter);
        $smbcpa = $smbcpaTable->findByOemId((int)$orderRow['OemId'])->current();
        if(!$smbcpa) return null;

        $pool = \models\Logic\LogicThreadPool::getPoolForSmbcpaAccountOpen($fldName, $this->dbAdapter);
        return $pool->openAsSingleton($orderRow['OrderSeq']);
    }

    /**
     * 請求関連処理ファンクションの基礎SQL取得。
     *
     * @return 請求関連処理ファンクションの基礎SQL
     */
    protected function getBaseP_ClaimControl() {
        return <<<EOQ
CALL P_ClaimControl(
    :pi_history_seq
,   :pi_button_flg
,   :pi_user_id
,   @po_ret_sts
,   @po_ret_errcd
,   @po_ret_sqlcd
,   @po_ret_msg
    )
EOQ;
    }

    /**
     * 指定注文SEQの注文商品の税額以外のデータを取得する.
     */
    private function getOrderItemsNotTaxdata($orderSeq) {
        $sql  = " SELECT oi.* ";
        $sql .= " FROM   T_Order o ";
        $sql .= "        INNER JOIN T_OrderItems oi ON (oi.OrderSeq = o.OrderSeq) ";
        $sql .= " WHERE  o.P_OrderSeq  = :OrderSeq ";
        $sql .= " AND    o.Cnl_Status  = 0 ";
        $sql .= " AND    oi.DataClass != 4 ";
        $sql .= " AND    oi.ValidFlg   = 1 ";
        return ResultInterfaceToArray($this->dbAdapter->query($sql)->execute(array(':OrderSeq' => $orderSeq)));
    }

    /**
     * 指定注文SEQの注文商品の税額データを取得する.
     */
    private function getOrderItemsTaxdata($orderSeq) {
        $sql  = " SELECT COUNT(oi.OrderItemId) AS cnt, SUM(oi.SumMoney) AS SumMoney FROM T_Order o ";
        $sql .= "        INNER JOIN T_OrderItems oi ON (oi.OrderSeq = o.OrderSeq) ";
        $sql .= " WHERE  o.P_OrderSeq = :OrderSeq ";
        $sql .= " AND    o.Cnl_Status = 0 ";
        $sql .= " AND    oi.DataClass = 4 ";
        $sql .= " AND    oi.ValidFlg  = 1 ";
        return $this->dbAdapter->query($sql)->execute(array(':OrderSeq' => $orderSeq))->current();
    }
}

Application::getInstance()->run();
