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
use Coral\Coral\History\CoralHistoryOrder;
use models\Table\TableSkipTarget;
use models\Table\TableSystemProperty;
use models\Table\TableSkipDeleteList;
use models\Table\TableSkipBatchControl;
use models\Table\TableCreditConditionName;
use models\Table\TableCreditConditionAddress;
use models\Table\TableCreditConditionPhone;
use models\Table\TableCreditConditionDomain;

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
     * @var BaseLog
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

            // 事前処理 ----------------------------------------------------------------------------------------------->
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
$this->logger->info('createskiptarget.php start');

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

            // 主処理 ------------------------------------------------------------------------------------------------->

            $this->makeSkipTarget($userId);


$this->logger->info('createskiptarget.php end');
            $exitCode = 0;

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
     * スキップ対象者マスタの作成を行う
     * @return boolean true:OK、false:NG
     */
    protected function makeSkipTarget($userId) {

        $mdlst  = new TableSkipTarget($this->dbAdapter);
        $mdlsdl = new TableSkipDeleteList($this->dbAdapter);
        $mdlsbc = new TableSkipBatchControl($this->dbAdapter);

        // SQL(基本) スキップ対象者リスト作成対象抽出
        $sql =<<<EOQ
SELECT  MAIN.ManCustId
      , MAIN.RegNameKj
      , MAIN.RegUnitingAddress
      , MAIN.RegPhone
      , MAIN.MailAddress
      , MAX(MAIN.ReceiptDate) as LastReceiptDate
      , MAX(MAIN.ClaimDate) as LastClaimDate
  FROM (
         SELECT mc.ManCustId
              , mc.RegNameKj
              , mc.RegUnitingAddress
              , mc.RegPhone
              , mc.MailAddress
              , rc.ReceiptDate
              , cc.ClaimDate
           FROM T_ClaimControl cc
          INNER JOIN T_ReceiptControl rc
                  ON rc.OrderSeq = cc.OrderSeq
          INNER JOIN T_EnterpriseCustomer ec
                  ON ec.EntCustSeq = cc.EntCustSeq
          INNER JOIN T_ManagementCustomer mc
                  ON ec.ManCustId = mc.ManCustId
          WHERE  1 = 1
            AND  cc.ClaimPattern = 1
            AND  cc.ClaimAmount <= cc.ReceiptAmountTotal
            AND  cc.F_LimitDate >= rc.ReceiptDate
            AND  cc.ClaimDate >= :ClaimDate
            AND  ec.EnterpriseId IN ( :EnterpriseId )
       ) MAIN
  LEFT JOIN (
         SELECT mc.ManCustId
           FROM T_ClaimControl cc
          INNER JOIN T_EnterpriseCustomer ec
             ON ec.EntCustSeq = cc.EntCustSeq
          INNER JOIN T_ManagementCustomer mc
             ON ec.ManCustId = mc.ManCustId
          WHERE 1 = 1
            AND cc.ClaimDate >= :ClaimDate
            AND ((cc.ClaimPattern = 1 AND cc.F_LimitDate < :ToDay AND cc.ClaimAmount > cc.ReceiptAmountTotal)
                     OR (cc.ClaimPattern > 1))
  ) SUB
    ON MAIN.ManCustId = SUB.ManCustId
 WHERE SUB.ManCustId IS NULL
 GROUP BY ManCustId
        , RegNameKj
        , RegUnitingAddress
        , RegPhone
        , MailAddress
EOQ;

        // システムプロパティ
        $mdlsys = new TableSystemProperty($this->dbAdapter);

        // スキップ対象者リスト作成範囲
        $targetyears = $mdlsys->getValue(TableSystemProperty::DEFAULT_MODULE, 'systeminfo', 'targetyear');
        // スキップ対象者リスト作成加盟店
        $stEnt       = $mdlsys->getValue(TableSystemProperty::DEFAULT_MODULE, 'systeminfo', 'SkipTargetEnterprise');

        $prm = array(
                ':ClaimDate' => date("Y-m-d",strtotime("-" . $targetyears . " year")),
                ':ToDay' => date("Y-m-d"),
                ':EnterpriseId' => $stEnt,
        );

        // SQL実行
        $ri = $this->dbAdapter->query($sql)->execute($prm);

        $datas = ResultInterfaceToArray($ri);

        // トランザクション開始
        $this->dbAdapter->getDriver()->getConnection()->beginTransaction();

        // スキップ対象者リストのトランケート
        $this->dbAdapter->query(" TRUNCATE TABLE T_SkipTarget ")->execute(null);

        $this->dbAdapter->getDriver()->getConnection()->commit();
        $this->dbAdapter->getDriver()->getConnection()->beginTransaction();

        $i = 1;

        // スキップ対象者リストの登録
        foreach ($datas as $data) {

            $stsql = " SELECT count(*) AS cnt FROM T_SkipTarget WHERE ManCustId = :ManCustId ";
            $strow = $this->dbAdapter->query($stsql)->execute(array(':ManCustId' => $data['ManCustId']))->current()['cnt'];
            if($strow > 0) {
                continue;
            }

            $sthdata = array(
                    'ManCustId'         => $data['ManCustId'],
                    'RegNameKj'         => $data['RegNameKj'],
                    'RegUnitingAddress' => $data['RegUnitingAddress'],
                    'RegPhone'          => $data['RegPhone'] ,
                    'MailAddress'       => $data['MailAddress'],
                    'LastReceiptDate'   => $data['LastReceiptDate'],
                    'LastClaimDate'     => $data['LastClaimDate'],
                    'RegistId'          => $userId,
                    'UpdateId'          => $userId,
                    'ValidFlg'          => 1,
            );
            $mdlst->saveNew($sthdata);

            if ($i % 10000 == 0) {
                $this->dbAdapter->getDriver()->getConnection()->commit();
                $this->dbAdapter->getDriver()->getConnection()->beginTransaction();
            }

            $i += 1;
        }

        $this->dbAdapter->getDriver()->getConnection()->commit();
        $this->dbAdapter->getDriver()->getConnection()->beginTransaction();

        $this->logger->info('[createskiptarget.php] Step1 end count = ' . $i);


        // スキップ対象者削除リストの作成

        // スキップ対象者リスト作成バッチ 起動履歴の取得
        $sbcsql = "SELECT * FROM T_SkipBatchControl ORDER BY 1 DESC LIMIT 1 ";
        $row = $this->dbAdapter->query($sbcsql)->execute(null)->current();

        // スキップ対象者削除リスト件数取得
        $sdlsql = "SELECT count(*) as cnt FROM T_SkipDeleteList ";
        $delcount = $this->dbAdapter->query($sdlsql)->execute(null)->current()['cnt'];

        if ($delcount == 0 || $row['TargetYears'] < $targetyears){ // 初回起動時 または 作成対象期間が増加した場合
            // スキップ対象者削除リストのトランケート
            $this->dbAdapter->query(" TRUNCATE TABLE T_SkipDeleteList ")->execute(null);

            // スキップ対象者リスト 抽出
            $stsql = "SELECT * FROM T_SkipTarget;";
            $stri = $this->dbAdapter->query($stsql)->execute(null);
            $stdatas = ResultInterfaceToArray($stri);

            $j = 0;

            foreach ($stdatas as $stdata) {

                $delflg = $this->blacklistcheck($stdata['RegNameKj'], $stdata['RegUnitingAddress'], $stdata['RegPhone'], $stdata['MailAddress']);

                if ($delflg) {
                    continue;
                }

                $sdldata = array(
                        'ManCustId'         => $stdata['ManCustId'],
                        'RegNameKj'         => $stdata['RegNameKj'],
                        'RegUnitingAddress' => $stdata['RegUnitingAddress'],
                        'RegPhone'          => $stdata['RegPhone'] ,
                        'MailAddress'       => $stdata['MailAddress'],
                        'LastReceiptDate'   => $stdata['LastReceiptDate'],
                        'LastClaimDate'     => $stdata['LastClaimDate'],
                        'RegistId'          => $userId,
                        'UpdateId'          => $userId,
                        'ValidFlg'          => 1,
                );
                $mdlsdl->saveNew($sdldata);

                if ($j % 1000 == 0) {
                    $this->dbAdapter->getDriver()->getConnection()->commit();
                    $this->dbAdapter->getDriver()->getConnection()->beginTransaction();
                }

                $j += 1;
            }

            $this->dbAdapter->getDriver()->getConnection()->commit();
            $this->dbAdapter->getDriver()->getConnection()->beginTransaction();

            $this->logger->info('[createskiptarget.php] Step2 end count = ' . $j );

        } else { // 前回起動時との差分を作成

            // スキップ対象者削除リストの古いデータを削除する
            $this->dbAdapter->query(" DELETE FROM T_SkipDeleteList WHERE LastClaimDate <= :ClaimDate ")->execute(array(":ClaimDate" => date("Y-m-d",strtotime("-" . $targetyears . " year"))));

            // ① 新規追加分のスキップ対象者リストに対し、ブラックリストを検索

            // 新規追加分スキップ対象者リスト 抽出
            $stsql1 = " SELECT * FROM T_SkipTarget WHERE LastReceiptDate >= :ReceiptDate ";
            $stri1 = $this->dbAdapter->query($stsql1)->execute(array(':ReceiptDate' => $row['ExecDate']))->current();
            $stdatas1 = ResultInterfaceToArray($stri1);

            foreach ($stdatas1 as $stdata) {

                $delflg = $this->blacklistcheck($stdata['RegNameKj'], $stdata['RegUnitingAddress'], $stdata['RegPhone'], $stdata['MailAddress']);
                if ($delflg) { continue; }

                $sdlsql = " SELECT count(*) as cnt FROM T_SkipDeleteList WHERE ManCustId = :ManCustId ";
                $delcount = $this->dbAdapter->query($sdlsql)->execute(array(':ManCustId' => $stdata['ManCustId']))->current()['cnt'];

                if ($delcount > 0) { continue; }

                $sdldata = array(
                        'ManCustId'         => $stdata['ManCustId'],
                        'RegNameKj'         => $stdata['RegNameKj'],
                        'RegUnitingAddress' => $stdata['RegUnitingAddress'],
                        'RegPhone'          => $stdata['RegPhone'] ,
                        'MailAddress'       => $stdata['MailAddress'],
                        'LastReceiptDate'   => $stdata['LastReceiptDate'],
                        'LastClaimDate'     => $stdata['LastClaimDate'],
                        'RegistId'          => $userId,
                        'UpdateId'          => $userId,
                        'ValidFlg'          => 1,
                );
                $mdlsdl->saveNew($sdldata);
            }

            $this->dbAdapter->getDriver()->getConnection()->commit();
            $this->dbAdapter->getDriver()->getConnection()->beginTransaction();

            $this->logger->info('[createskiptarget.php] Step2-1 end');

            // ② 新規追加分のブラックリストに対し、①以外のスキップ対象者リストを検索

            // 新規追加分スキップ対象者リスト 抽出
            $stsql2 = " SELECT * FROM T_SkipTarget WHERE LastReceiptDate < :ReceiptDate ";
            $stri2 = $this->dbAdapter->query($stsql2)->execute(array(':ReceiptDate' => $row['ExecDate']));
            $stdatas2 = ResultInterfaceToArray($stri2);

            foreach ($stdatas2 as $stdata) {

                $delflg = $this->blacklistcheck($stdata['RegNameKj'], $stdata['RegUnitingAddress'], $stdata['RegPhone'], $stdata['MailAddress'], $row['ExecDate']);
                if ($delflg) { continue; }

                $sdlsql = " SELECT count(*) as cnt FROM T_SkipDeleteList WHERE ManCustId = :ManCustId ";
                $delcount = $this->dbAdapter->query($sdlsql)->execute(array(':ManCustId' => $stdata['ManCustId']))->current()['cnt'];

                if ($delcount > 0) { continue; }

                $sdldata = array(
                        'ManCustId'         => $stdata['ManCustId'],
                        'RegNameKj'         => $stdata['RegNameKj'],
                        'RegUnitingAddress' => $stdata['RegUnitingAddress'],
                        'RegPhone'          => $stdata['RegPhone'] ,
                        'MailAddress'       => $stdata['MailAddress'],
                        'LastReceiptDate'   => $stdata['LastReceiptDate'],
                        'LastClaimDate'     => $stdata['LastClaimDate'],
                        'RegistId'          => $userId,
                        'UpdateId'          => $userId,
                        'ValidFlg'          => 1,
                );
                $mdlsdl->saveNew($sdldata);
            }

            $this->dbAdapter->getDriver()->getConnection()->commit();
            $this->dbAdapter->getDriver()->getConnection()->beginTransaction();

            $this->logger->info('[createskiptarget.php] Step2-2 end');

            // ③ 新規更新分のブラックリスト論理削除分をスキップ対象者削除リストから削除

            // 氏名
            $ccnsql = " SELECT * FROM T_CreditConditionName WHERE ValidFlg = 0 AND UpdateDate >= :Date ";
            $ccnri = $this->dbAdapter->query($ccnsql)->execute(array(':Date' => $row['ExecDate']));
            $ccndatas = ResultInterfaceToArray($ccnri);

            foreach ($ccndatas as $ccndata) {
                $this->delSkipDeleteList($ccndata['RegCstring'], 1);
            }

            // 住所
            $ccasql = " SELECT * FROM T_CreditConditionAddress WHERE ValidFlg = 0 AND UpdateDate >= :Date ";
            $ccari = $this->dbAdapter->query($ccasql)->execute(array(':Date' => $row['ExecDate']));
            $ccadatas = ResultInterfaceToArray($ccari);

            foreach ($ccadatas as $ccadata) {
                $this->delSkipDeleteList($ccadata['RegCstring'], 2);
            }

            // 電話番号
            $ccpsql = " SELECT * FROM T_CreditConditionPhone WHERE ValidFlg = 0 AND UpdateDate >= :Date ";
            $ccpri = $this->dbAdapter->query($ccpsql)->execute(array(':Date' => $row['ExecDate']));
            $ccpdatas = ResultInterfaceToArray($ccpri);

            foreach ($ccpdatas as $ccpdata) {
                $this->delSkipDeleteList($ccpdata['RegCstring'], 3);
            }

            // メールアドレス
            $ccdsql = " SELECT * FROM T_CreditConditionDomain WHERE ValidFlg = 0 AND UpdateDate >= :Date ";
            $ccdri = $this->dbAdapter->query($ccdsql)->execute(array(':Date' => $row['ExecDate']));
            $ccddatas = ResultInterfaceToArray($ccdri);

            foreach ($ccddatas as $ccddata) {
                $this->delSkipDeleteList($ccddata['RegCstring'], 4);
            }

            $this->dbAdapter->getDriver()->getConnection()->commit();
            $this->dbAdapter->getDriver()->getConnection()->beginTransaction();

            $this->logger->info('[createskiptarget.php] Step2-3 end');
        }

        // スキップ対象者リストから、スキップ対象者削除リストに登録されているリストを削除する。
        $this->dbAdapter->query(" DELETE FROM T_SkipTarget WHERE ManCustId IN (SELECT ManCustId FROM T_SkipDeleteList) ")->execute(null);

        $this->dbAdapter->getDriver()->getConnection()->commit();
        $this->dbAdapter->getDriver()->getConnection()->beginTransaction();

        //スキップ対象者リスト作成バッチ 起動履歴の登録
        $sbcdata = array(
                'ExecDate'     => date("Y-m-d"),
                'TargetYears'  => $targetyears,
        );
        $mdlsbc->saveNew($sbcdata);

        $this->dbAdapter->getDriver()->getConnection()->commit();

    }

    /**
     * ブラックリストのチェックを行う
     *
     * @param string $name 氏名
     * @param string $address 結合住所
     * @param string $phone 電話番号
     * @param string $mailaddress メールアドレス
     * @param date   $date 検索対象日付(デフォルトNULL)
     * @return boolean true:対象有り、false:対象無し
     */
    protected function blacklistcheck($name, $address, $phone, $mailaddress, $date = NULL) {

        $mdlccn = new TableCreditConditionName($this->dbAdapter);
        $mdlcca = new TableCreditConditionAddress($this->dbAdapter);
        $mdlccp = new TableCreditConditionPhone($this->dbAdapter);
        $mdlccd = new TableCreditConditionDomain($this->dbAdapter);

            $ccacnt = $mdlcca->judgeskip($address, $date);

        if ($ccacnt > 0) {
            return false;
         }

        $ccpcnt = $mdlccp->judgeskip($phone, $date);

        if ($ccpcnt > 0) {
            return false;
        }

        $ccdcnt = $mdlccd->judgeskip($mailaddress, $date);

        if ($ccdcnt > 0) {
            return false;
        }

        $ccncnt = $mdlccn->judgeskip($name, $date);

        if ($ccncnt > 0) {
            return false;
        }

        return true;
    }


    /**
     * T_SkipDeleteList から 論理削除分を削除する
     * @param string $RegCstring 正規化文字列
     * @param int $category カテゴリ
     */
    protected function delSkipDeleteList($RegCstring, $category) {

        $delsdlsql = " DELETE FROM T_SkipDeleteList WHERE 1 = 1 ";

        // テスト注文自動与信審査区分
        switch ($category) {
            case 1:
                // 氏名
                $delsdlsql .= " AND RegNameKj LIKE :RegCstring ";
                break;
            case 2:
                // 住所
                $delsdlsql .= " AND RegUnitingAddress LIKE :RegCstring ";
                break;
            case 3:
                // 電話番号
                $delsdlsql .= " AND RegPhone LIKE :RegCstring ";
                break;
            case 4:
                // メールアドレス
                $delsdlsql .= " AND MailAddress LIKE :RegCstring ";
                break;
            default:
                break;
        }

        $this->dbAdapter->query($delsdlsql)->execute(array(':RegCstring' => '%' . $RegCstring . '%'));

    }
}

Application::getInstance()->run();
