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
use models\Table\TableSystemStatus;
use models\View\ViewOrderCustomer;
use models\Logic\CreditJudge\LogicCreditJudgeAbstract;
use models\Logic\CreditJudge\LogicCreditJudgeSequencer;
use models\Table\TableCreditJudgeLock;

class Application extends BaseApplicationAbstract {
    protected $_application_id = 'tools-creditjudge-batch';

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
$this->logger->info('creditjudge.php start');

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

            // プログラム引数の件数チェック
            if ($_SERVER['argc'] != 2) {
$this->logger->warn('It does not match the number of arguments. argc=' . $_SERVER['argc']);
                exit(0);
            }

            // プログラム引数の型チェック
            if (!is_numeric($_SERVER['argv'][1])) {
$this->logger->warn('The argument is not a number. argv=' . $_SERVER['argv'][1]);
                exit(0);
            }
            $creditThreadNo = (int)$_SERVER['argv'][1];

            // ユーザーID取得
            $mdlu = new TableUser($this->dbAdapter);
            $userId = $mdlu->getUserId(TableUser::USERCLASS_SYSTEM, TableUser::SEQ_BATCH_USER);

            $lockFile = '.lock_creditjudge';

            // システムステータス
            $mdlcj = new TableCreditJudgeLock($this->dbAdapter);
            try {       // sango環境でシェルスクリプトがsegmentation faultになる怪奇現象の調査コード
                $lockId = $mdlcj->getLock($creditThreadNo);
            } catch(Exception $err) {
                echo $err->getMessage();
                throw $err;		// この段階では上位へ丸投げ。例外メッセージが見れりゃとりあえず御の字
            }

            if($lockId > 0) {
                /** @var Logic_CreditJudge_Sequencer */
                $logic = null;

                // 社内与信実行
                try {
                    LogicCreditJudgeAbstract::setDefaultLogger($this->logger);
                    LogicCreditJudgeSequencer::setDefaultConfig($data);
                    LogicCreditJudgeSequencer::setUserId($userId);

                    while(true) {
                        $logic = new LogicCreditJudgeSequencer($this->dbAdapter);
                        $judge_result = $logic->doJudgementForBatch($creditThreadNo);

                        // 処理結果のログキャッシュ内容をコンソールへ出力
                        echo join(PHP_EOL, $logic->getCachedLog()) . PHP_EOL . PHP_EOL;

                        if($this->hasJudgeTarget($creditThreadNo)) {
                            // 与信対象の残があったら若干ウェイトを入れてから繰り返す
                            sleep(5);
                        } else {
                            // 与信対象残がなければこのバッチは終了
                            break;
                        }
                    }

                    $exitCode = 0; // 正常終了

                } catch(Exception $err) {
                    echo sprintf("an error occurred. message = %s\n", $err->getMessage());
                    // 運が良ければロック解除できる
// エラー発生時のデバッグログを追加
$this->logger->err($err->getMessage());
$this->logger->err($err->getTraceAsString());
                }
                // ロック解除
                $mdlcj->releaseLock($creditThreadNo);

                echo "Executed.\r\n";
            } else {
                $exitCode = 0; // ロックが取れない場合も正常終了とみなす
                echo "Can't execute by Locking.\r\n";
            }

$this->logger->info('creditjudge.php end');

        } catch( \Exception $e ) {
            // エラーログを出力
            if ( isset($this->logger) ) {
$this->logger->err($e->getMessage());
            }
        }

        // 終了コードを指定して処理終了
        exit($exitCode);

    }

    protected function hasJudgeTarget($creditThreadNo) {
$sql = <<<EOQ
SELECT COUNT(1) AS cnt
FROM T_Order o
    ,T_Enterprise e
    ,AT_Order ao
WHERE o.EnterpriseId = e.EnterpriseId
AND o.OrderSeq = ao.OrderSeq
AND o.DataStatus = 11
AND o.Cnl_Status = 0
AND ao.DefectFlg = 0
AND e.CreditThreadNo = :CreditThreadNo
EOQ;
        $prm = array(
            ':CreditThreadNo' => $creditThreadNo,
        );
        $cnt = $this->dbAdapter->query($sql)->execute($prm)->current()['cnt'];
        return $cnt > 0;
    }
}

Application::getInstance()->run();
