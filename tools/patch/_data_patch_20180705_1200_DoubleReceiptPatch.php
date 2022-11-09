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
$this->logger->info('_data_patch_20160927_0900_DoubleReceiptPatch.php start');

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
                    '32601625',
                    '33499144',
                    '33500911',
                    '33504025',
                    '33775070',
                    '33779365',
                    '33926765',
                    '33927401',
                    '33927656',
                    '33928196',
                    '33928240',
                    '33928256',
                    '33928472',
                    '33929388',
                    '33929865',
                    '33929954',
                    '33930073',
                    '33930158',
                    '33930454',
                    '33931296',
                    '33931738',
                    '33931849',
                    '33931891',
                    '33933220',
                    '33933228',
                    '33964409',
                    '33977131',
                    '34012080',
                    '34012108',
                    '34044195',
                    '34113671',
                    '34119319',
                    '34130317',
                    '34130616',
                    '34171160',
                    '34176710',
                    '34176712',
                    '34188101',
                    '34209078',
                    '34209863',
                    '34209864',
                    '34210755',
                    '34251650',
                    '34252174',
                    '34258831',
                    '34262063',
                    '34263839',
                    '34276804',
                    '34281617',
                    '34307712',
                    '34307821',
                    '34307869',
                    '34310362',
                    '34312228',
                    '34312240',
                    '34312242',
                    '34312244',
                    '34312271',
                    '34312299',
                    '34312328',
                    '34312335',
                    '34312344',
                    '34312369',
                    '34312398',
                    '34312405',
                    '34312434',
                    '34312440',
                    '34312460',
                    '34312466',
                    '34312470',
                    '34312501',
                    '34312518',
                    '34312573',
                    '34312595',
                    '34312609',
                    '34312614',
                    '34312625',
                    '34312637',
                    '34312657',
                    '34312686',
                    '34312704',
                    '34312749',
                    '34312753',
                    '34312786',
                    '34312808',
                    '34312819',
                    '34312970',
                    '34312988',
                    '34313000',
                    '34313021',
                    '34313024',
                    '34313048',
                    '34313057',
                    '34313089',
                    '34313092',
                    '34313116',
                    '34313119',
                    '34313124',
                    '34313129',
                    '34313135',
                    '34313207',
                    '34313209',
                    '34313215',
                    '34313219',
                    '34313223',
                    '34313254',
                    '34313272',
                    '34313287',
                    '34313378',
                    '34313413',
                    '34313434',
                    '34313436',
                    '34313457',
                    '34313473',
                    '34313484',
                    '34313485',
                    '34313496',
                    '34313516',
                    '34313532',
                    '34313545',
                    '34313561',
                    '34313562',
                    '34313578',
                    '34314066',
                    '34314077',
                    '34314085',
                    '34314097',
                    '34314130',
                    '34314143',
                    '34314156',
                    '34314162',
                    '34314196',
                    '34314210',
                    '34314222',
                    '34314226',
                    '34314270',
                    '34314274',
                    '34314279',
                    '34314288',
                    '34314290',
                    '34314300',
                    '34314307',
                    '34314310',
                    '34314326',
                    '34314338',
                    '34314347',
                    '34314375',
                    '34314394',
                    '34314396',
                    '34314415',
                    '34314419',
                    '34314427',
                    '34314455',
                    '34314489',
                    '34314501',
                    '34314533',
                    '34314559',
                    '34314560',
                    '34314565',
                    '34314578',
                    '34314594',
                    '34314600',
                    '34314610',
                    '34314647',
                    '34314698',
                    '34314707',
                    '34314711',
                    '34314722',
                    '34314725',
                    '34314727',
                    '34314757',
                    '34314762',
                    '34314812',
                    '34314845',
                    '34314851',
                    '34314853',
                    '34314887',
                    '34314903',
                    '34314904',
                    '34314905',
                    '34314912',
                    '34314917',
                    '34314929',
                    '34314987',
                    '34315015',
                    '34315037',
                    '34315039',
                    '34315041',
                    '34315063',
                    '34315077',
                    '34315131',
                    '34315146',
                    '34315181',
                    '34315209',
                    '34315213',
                    '34315228',
                    '34315230',
                    '34315238',
                    '34315279',
                    '34315335',
                    '34315347',
                    '34315349',
                    '34315351',
                    '34315360',
                    '34315378',
                    '34315379',
                    '34315385',
                    '34315395',
                    '34315398',
                    '34315429',
                    '34315434',
                    '34315436',
                    '34315445',
                    '34315459',
                    '34315492',
                    '34315519',
                    '34315522',
                    '34315524',
                    '34315548',
                    '34315586',
                    '34315672',
                    '34315696',
                    '34315727',
                    '34315759',
                    '34315778',
                    '34315821',
                    '34315829',
                    '34315843',
                    '34315867',
                    '34315870',
                    '34315899',
                    '34315902',
                    '34315928',
                    '34315937',
                    '34315938',
                    '34315941',
                    '34316077',
                    '34316087',
                    '34316092',
                    '34316095',
                    '34316108',
                    '34316115',
                    '34316119',
                    '34316180',
                    '34316193',
                    '34316205',
                    '34316209',
                    '34316210',
                    '34316212',
                    '34316222',
                    '34316223',
                    '34316252',
                    '34316260',
                    '34316266',
                    '34316271',
                    '34316278',
                    '34316325',
                    '34316395',
                    '34316401',
                    '34316461',
                    '34316474',
                    '34316483',
                    '34316537',
                    '34316544',
                    '34316547',
                    '34316605',
                    '34316630',
                    '34316638',
                    '34316652',
                    '34316662',
                    '34316668',
                    '34316692',
                    '34318198',
                    '34318211',
                    '34318231',
                    '34318260',
                    '34318267',
                    '34318276',
                    '34318307',
                    '34318329',
                    '34318358',
                    '34318429',
                    '34318500',
                    '34318536',
                    '34318539',
                    '34318580',
                    '34318591',
                    '34318664',
                    '34318684',
                    '34318816',
                    '34318820',
                    '34318826',
                    '34318836',
                    '34318856',
                    '34318864',
                    '34318909',
                    '34340692',
                    '34343996',
                    '34344394',
                    '34346765',
                    '34353214',
                    '34358495',
                    '34359598',
                    '34360162',
                    '34366815',
                    '34369304',
                    '34374839',
                    '34374840',
                    '34380216',
                    '34380223',
                    '34380605',
                    '34390641',
                    '34390655',
                    '34391410',
                    '34394219',
                    '34394776',
                    '34396722',
                    '34397959',
                    '34404517',
                    '34404557',
                    '34404584',
                    '34404590',
                    '34404789',
                    '34404800',
                    '34404868',
                    '34404875',
                    '34404888',
                    '34404920',
                    '34406990',
                    '34407153',
                    '34410794',
                    '34410838',
                    '34411902',
                    '34412021',
                    '34412541',
                    '34422507',
                    '34424775',
                    '34425975',
                    '34427120',
                    '34429164',
                    '34436856',
                    '34436881',
                    '34436900',
                    '34441141',
                    '34442141',
                    '34443945',
                    '34444626',
                    '34449696',
                    '34454833',
                    '34455066',
                    '34463271',
                    '34467116',
                    '34471079',
                    '34472083',
                    '34478824',
                    '34478837',
                    '34478844',
                    '34483166',
                    '34485051',
                    '34486240',
                    '34487132',
                    '34488000',
                    '34490468',
                    '34490896',
                    '34491182',
                    '34491317',
                    '34492034',
                    '34492063',
                    '34492375',
                    '34492471',
                    '34493951',
                    '34494567',
                    '34497361',
                    '34497514',
                    '34500919',
                    '34501210',
                    '34501909',
                    '34502713',
                    '34503686',
                    '34504307',
                    '34504321',
                    '34504329',
                    '34504556',
                    '34505448',
                    '34506891',
                    '34507631',
                    '34510666',
                    '34511016',
                    '34512500',
                    '34514606',
                    '34514636',
                    '34514656',
                    '34514675',
                    '34514687',
                    '34514694',
                    '34514764',
                    '34514766',
                    '34514770',
                    '34514781',
                    '34514815',
                    '34514835',
                    '34514856',
                    '34514864',
                    '34514877',
                    '34514933',
                    '34514934',
                    '34514951',
                    '34514958',
                    '34514965',
                    '34514967',
                    '34514979',
                    '34514981',
                    '34515060',
                    '34515070',
                    '34515113',
                    '34515416',
                    '34516792',
                    '34516810',
                    '34517327',
                    '34518448',
                    '34518555',
                    '34519388',
                    '34519660',
                    '34520794',
                    '34521178',
                    '34522160',
                    '34522721',
                    '34523436',
                    '34523438',
                    '34523518',
                    '34523567',
                    '34523671',
                    '34523827',
                    '34528301',
                    '34528420',
                    '34530501',
                    '34531109',
                    '34532447',
                    '34533276',
                    '34533383',
                    '34534644',
                    '34534826',
                    '34534833',
                    '34534939',
                    '34535156',
                    '34535219',
                    '34536086',
                    '34536091',
                    '34538414',
                    '34539339',
                    '34539352',
                    '34539357',
                    '34539362',
                    '34539368',
                    '34539373',
                    '34539381',
                    '34539383',
                    '34539399',
                    '34539419',
                    '34539421',
                    '34539422',
                    '34539434',
                    '34539435',
                    '34541039',
                    '34543614',
                    '34544394',
                    '34545255',
                    '34545621',
                    '34546848',
                    '34547111',
                    '34549946',
                    '34550674',
                    '34550689',
                    '34552739',
                    '34552745',
                    '34553160',
                    '34553533',
                    '34553746',
                    '34554057',
                    '34554376',
                    '34555072',
                    '34556272',
                    '34561401',
                    '34563836',
                    '34564124',
                    '34564488',
                    '34564632',
                    '34565061',
                    '34565476',
                    '34566120',
                    '34566370',
                    '34566485',
                    '34567909',
                    '34567940',
                    '34567972',
                    '34568291',
                    '34568433',
                    '34568898',
                    '34569378',
                    '34569919',
                    '34570016',
                    '34570192',
                    '34570913',
                    '34573384',
                    '34573400',
                    '34576654',
                    '34577423',
                    '34577719',
                    '34578838',
                    '34579883',
                    '34580117',
                    '34580164',
                    '34581058',
                    '34582390',
                    '34582428',
                    '34583267',
                    '34583778',
                    '34584188',
                    '34584236',
                    '34584555',
                    '34584930',
                    '34585319',
                    '34586203',
                    '34586401',
                    '34586747',
                    '34587108',
                    '34589020',
                    '34590389',
                    '34590884',
                    '34591071',
                    '34591438',
                    '34591943',
                    '34591950',
                    '34591952',
                    '34591954',
                    '34593493',
                    '34594632',
                    '34595104',
                    '34595834',
                    '34596111',
                    '34596178',
                    '34596439',
                    '34596514',
                    '34596698',
                    '34596854',
                    '34597283',
                    '34597920',
                    '34598703',
                    '34598833',
                    '34599576',
                    '34600863',
                    '34600866',
                    '34600897',
                    '34600934',
                    '34600943',
                    '34600998',
                    '34601036',
                    '34601073',
                    '34604931',
                    '34605511',
                    '34605862',
                    '34606208',
                    '34606221',
                    '34606222',
                    '34607318',
                    '34607476',
                    '34608288',
                    '34608403',
                    '34609236',
                    '34609368',
                    '34609778',
                    '34609946',
                    '34610027',
                    '34610089',
                    '34610115',
                    '34610329',
                    '34610697',
                    '34610800',
                    '34611729',
                    '34611932',
                    '34612004',
                    '34612499',
                    '34612578',
                    '34612695',
                    '34612920',
                    '34613270',
                    '34614443',
                    '34615818',
                    '34615891',
                    '34618587',
            );

            // <-------------------------------------------------------------------------

            $this->rcptcancelRun($arrOseq, $userId);

            $end = microtime(true);

echo ($end - $start);

            $exitCode = 0; // 正常終了
$this->logger->info('_data_patch_20160927_0900_DoubleReceiptPatch.php end');

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
