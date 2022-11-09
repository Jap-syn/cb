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
use models\Table\TableSystemProperty;
use models\Table\TableClaimControl;
use Coral\Base\Auth\BaseAuthUtility;
use Coral\Base\BaseGeneralUtils;
use models\Logic\Exception\LogicClaimException;
use models\Table\TableOrder;

class Application extends BaseApplicationAbstract {
    protected $_application_id = 'tools';

    private $checkcsv;

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
        $isBeginTran = false;

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
$this->logger->info('billingAgentReissue.php start');

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

            // 初回請求書再発行指示
            $this->runMain();

$this->logger->info('billingAgentReissue.php end');
            $exitCode = 0; // 正常終了

        } catch( \Exception $e ) {
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
     * 初回請求書再発行指示
     */
    protected function runMain() {
        $mdlsys = new TableSystemProperty($this->dbAdapter);
        $mdlcc  = new TableClaimControl($this->dbAdapter);

        // ユーザーIDの取得
        $obj = new \models\Table\TableUser($this->dbAdapter);
        $userId = $obj->getUserId(TableUser::USERCLASS_SYSTEM, TableUser::SEQ_BATCH_USER);

        // 督促支払期限有効日数を取得
        $validLimitDays2 = $mdlsys->getValue(TableSystemProperty::DEFAULT_MODULE, 'systeminfo', 'ValidLimitDays2');

        // 請求日を取得
        $claimDate = $this->getClaimDate();

        // a. ジョブ転送が必要な対象を抽出
        $sql = <<<EOQ
SELECT CLM.*, S.ReissueCount AS Site_ReissueCount
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
       INNER JOIN T_Enterprise E ON E.EnterpriseId = ORD.EnterpriseId
 WHERE 1 = 1
   AND (po.LetterClaimStopFlg = 0 AND IFNULL(MC.RemindStopFlg, 0) = 0)
   AND ( SELECT COUNT(1) FROM T_ClaimHistory WHERE OrderSeq = ORD.OrderSeq AND PrintedFlg = 0 AND ValidFlg = 1 ) = 0
   AND IFNULL(AO.ExtraPayType, 0) <> 1
   AND CLM.ReissueClass = 0
   AND cc.ClaimedBalance > 0
   AND cc.LimitDate <= :ClaimPatterCondition1
   AND cc.MypageReissueClass IN ( 0, 91, 92 )
   AND ( SELECT MAX(ClaimPattern) FROM T_ClaimHistory tmpch WHERE OrderSeq = ORD.OrderSeq AND PrintedFlg = 1 AND ValidFlg = 1 ) = 1
   AND ( SELECT COUNT(1) FROM T_ReclaimIndicate WHERE OrderSeq = ORD.ORDERseq AND IndicatedFlg = 0 AND ValidFlg = 1 ) = 0
   AND S.RemindStopClass = 0
   AND E.BillingAgentFlg = 1
   AND S.ReissueCount >= CLM.ReissueCount
EOQ;

        $row_mc = $this->dbAdapter->query(" SELECT Class1, Class2 FROM M_Code WHERE CodeId = 183 AND KeyCode = :KeyCode "
        )->execute(array(':KeyCode' => 2))->current();

        $prm = array(
                ':ClaimPatterCondition1' => date('Y-m-d', strtotime($claimDate . ' -' . $row_mc['Class1'] . 'day')),
        );

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

            // 紙請求ストップフラグの判定
            if ($row['ReissueCount'] == $row['Site_ReissueCount']){
                // 注文データの更新
                $sql  = " UPDATE T_Order ";
                $sql .= " SET    LetterClaimStopFlg = :LetterClaimStopFlg ";
                $sql .= " ,      MailClaimStopFlg   = 0 ";
                $sql .= " ,      UpdateId           = :UpdateId ";
                $sql .= " ,      UpdateDate         = :UpdateDate ";
                $sql .= " WHERE  P_OrderSeq = :OrderSeq ";

                $prm = array(
                        ':LetterClaimStopFlg'   => 1,
                        ':UpdateId'             => $userId,
                        ':UpdateDate'           => date('Y-m-d H:i:s'),
                        ':OrderSeq'             => $row['OrderSeq'],
                );
                $this->dbAdapter->query($sql)->execute($prm);
                continue;
            }

            // SMBCバーチャル口座オープン用にロック獲得を試行
            $mdlo = new TableOrder($this->dbAdapter);
            $lockItem = $this->getLockItemForSmbcpaAccount($mdlo->find($oseq)->current());

            try {

                //トランザクション開始
                $this->dbAdapter->getDriver()->getConnection()->beginTransaction();

                $row['ReissueCount'] += 1;

                // 請求管理更新
                $mdlcc->saveUpdate(array('ReissueClass' => 1, 'ReissueCount' => $row['ReissueCount'], 'UpdateId' => $userId), $row['ClaimId']);

                // 請求履歴更新
                $sql = " UPDATE T_ClaimHistory SET MailFlg = 1 WHERE OrderSeq = :OrderSeq AND MailFlg = 0 AND ValidFlg = 1 ";
                $stm = $this->dbAdapter->query($sql);
                $stm->execute(array(':OrderSeq' => $row['OrderSeq']));

                $this->dbAdapter->getDriver()->getConnection()->commit();
            }
            catch (\Exception $e) {

                $this->dbAdapter->getDriver()->getConnection()->rollback();

                // ロックを獲得していたら開放
                try {
                    if($lockItem) {
                        $lockItem->terminate();
                    }
                }
                catch (\Exception $e) { ; }
            }

        }
        // ロックを獲得していたら開放
        try {
            if($lockItem) {
                $lockItem->terminate();
            }
        }
        catch (\Exception $e) { ; }
        return;
    }

    /**
     * SMBCバーチャル口座オープン用のロックアイテムを獲得する
     *
     * @access protected
     * @param array 対象注文の行オブジェクト
     * @return \models\Logic\ThreadPool\LogicThreadPoolItem | null
     */
    protected function getLockItemForSmbcpaAccount($orderRow = null)
    {
        if(!$orderRow) return null;

        $smbcpaTable = new \models\Table\TableSmbcpa($this->dbAdapter);
        $smbcpa = $smbcpaTable->findByOemId((int)$orderRow['OemId'])->current();
        if(!$smbcpa) return null;

        $pool = \models\Logic\LogicThreadPool::getPoolForSmbcpaAccountOpen($smbcpa['SmbcpaId'], $this->dbAdapter);
        return $pool->openAsSingleton($orderRow['OrderSeq']);
    }

}

Application::getInstance()->run();
