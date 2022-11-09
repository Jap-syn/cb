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
use models\Logic\LogicCancel;
use models\Logic\OrderCancelException;


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
     * アプリケーションを実行します。
     *
     * @access public
     */
    public function run() {
        $exitCode = 1;

        try {

            // 実行確認
            echo "Run the Cancel batch. Is it OK?(Y/N)";
            $yn = trim(fgets(STDIN));
            if (strtoupper($yn) != 'Y') {
                echo "It has stopped the execution. ";
                exit(0);
            }

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
$this->logger->info('_data_patch_20200909_1500_CancelRegister.php start');

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

            // ユーザーID取得
            $mdlu = new TableUser($this->dbAdapter);
            $userId = $mdlu->getUserId(TableUser::USERCLASS_SYSTEM, TableUser::SEQ_BATCH_USER);

            $this->dbAdapter->getDriver()->getConnection()->beginTransaction();


            // ------------------------------------------------------------------------->
            // 対象の定義
            $target = array(
'AK46320010',
'AK46320030',
'AK46324065',
'AK46324075',
'AK46274467',
'AK46714339',
'AK46690265',
'AK46530647',
'AK46410516',
'AK46320023',
'AK46770686',
'AK46747122',
'AK46245482',
'AK46426454',
'AK46202510',
'AK46498218',
'AK46333680',
'AK46281371',
'AK46641871',
'AK46403605',
'AK46403611',
'AK46403624',
'AK46363232',
'AK46297855',
'AK46297909',
'AK46297940',
'AK46281253',
'AK46267994',
'AK46253504',
'AK46253550',
'AK46056938',
'AK46401711',
'AK46100343',
'AK46253619',
'AK46641782',
'AK46253551',
'AK46392855',
'AK46268223',
'AK46103985',
'AK46372707',
'AK45432375',
'AK46784669',
'AK46784700',
'AK46784685',
'AK46764526',
'AK46738168',
'AK46625552',
'AK46484594',
'AK46372416',
'AK46739017',
'AK46110424',
'AK46274541',
'AK46900874',
'AK46709191',
'AK46306577',
'AK46914413',
'AK46892238',
'AK46892214',
'AK46892254',
'AK46710353',
'AK46596075',
'AK46536682',
'AK46349568',
'AK46204014',
'AK46692524',
'AK46537159',
'AK46692554',
'AK46217565',
'AK46010596',
'AK46010653',
'AK46146855',
'AK45991129',
'AK46853440',
'AK46853608',
'AK46853427',
'AK46674898',
'AK46674893',
'AK46619954',
'AK46468244',
'AK46437500',
'AK46363041',
'AK46224476',
'AK46224452',
'AK46224425',
'AK46224377',
'AK46224350',
'AK46224337',
'AK46829138',
'AK46674902',
'AK46403413',
'AK46224505',
'AK46191817',
'AK46437537',
'AK46776646',
'AK46362932',
'AK46880111',
'AK46880096',
'AK46880099',
'AK46880104',
'AK46880106',
'AK46880108',
'AK46880113',
'AK46880125',
'AK46880128',
'AK46880129',
'AK46880131',
'AK46880134',
'AK46880136',
'AK46880139',
'AK46880141',
'AK46853321',
'AK46853364',
'AK46853368',
'AK46853386',
'AK46853387',
'AK46853401',
'AK46853425',
'AK46853429',
'AK46853442',
'AK46853523',
'AK46853536',
'AK46853541',
'AK46853568',
'AK46853572',
'AK46853591',
'AK46853593',
'AK46853601',
'AK46853644',
'AK46829128',
'AK46829129',
'AK46829130',
'AK46829132',
'AK46829140',
'AK46829141',
'AK46829147',
'AK46829148',
'AK46829150',
'AK46829151',
'AK46829153',
'AK46829154',
'AK46829159',
'AK46829160',
'AK46829161',
'AK46829162',
'AK46813364',
'AK46813391',
'AK46813337',
'AK46813387',
'AK46813388',
'AK46813392',
'AK46813393',
'AK46813394',
'AK46799163',
'AK46799172',
'AK46799180',
'AK46799188',
'AK46799196',
'AK46799205',
'AK46799218',
'AK46799290',
'AK46799298',
'AK46776660',
'AK46776564',
'AK46776598',
'AK46776636',
'AK46776662',
'AK46776665',
'AK46776672',
'AK46776674',
'AK46730421',
'AK46730413',
'AK46730414',
'AK46730417',
'AK46730418',
'AK46730423',
'AK46730429',
'AK46730434',
'AK46674891',
'AK46674892',
'AK46674901',
'AK46674889',
'AK46567209',
'AK46437479',
'AK46362970',
'AK46363040',
'AK46366020',
'AK46423035',
'AK46403455',
'AK46333371',
'AK46333415',
'AK46314913',
'AK46314917',
'AK46224500',
'AK46224359',
'AK46191709',
'AK46191632',
'AK46191696',
'AK46191733',
'AK46191785',
'AK46191801',
'AK46191811',
'AK46191891',
'AK46191908',
'AK46191917',
'AK46191922',
'AK46174935',
'AK46175017',
'AK46175060',
'AK46175199',
'AK46175213',
'AK46175226',
'AK46175239',
'AK46175253',
'AK46567250',
'AK46566897',
'AK46567116',
'AK46567125',
'AK46567226',
'AK46468249',
'AK46468260',
'AK46468305',
'AK46468310',
'AK46468317',
'AK46468326',
'AK46468362',
'AK46468365',
'AK46468416',
'AK46468431',
'AK46437476',
'AK46437543',
'AK46437548',
'AK46437566',
'AK46362892',
'AK46362980',
'AK46366014',
'AK46366026',
'AK46366039',
'AK46422920',
'AK46423019',
'AK46423022',
'AK46423026',
'AK46423071',
'AK46423101',
'AK46423105',
'AK46423114',
'AK46403212',
'AK46403276',
'AK46403301',
'AK46403329',
'AK46333501',
'AK46333367',
'AK46333485',
'AK46333507',
'AK46333531',
'AK46333568',
'AK46314893',
'AK46314943',
'AK46314990',
'AK46224339',
'AK46224368',
'AK46224335',
'AK46224345',
'AK46224347',
'AK46224354',
'AK46224360',
'AK46224363',
'AK46224374',
'AK46224375',
'AK46224379',
'AK46224385',
'AK46224388',
'AK46224395',
'AK46224397',
'AK46224409',
'AK46224410',
'AK46224417',
'AK46224421',
'AK46224424',
'AK46224433',
'AK46224437',
'AK46224443',
'AK46224444',
'AK46224448',
'AK46224458',
'AK46224459',
'AK46224467',
'AK46224479',
'AK46224483',
'AK46224492',
'AK46224494',
'AK46657591',
'AK46657593',
'AK46657605',
'AK46641521',
'AK46641644',
'AK46641713',
'AK46619931',
'AK46619936',
'AK46619944',
'AK46619959',
'AK46224334',
'AK46224357',
'AK46567100',
'AK46224440',
'AK46776588',
'AK46701352',
'AK46437429',
'AK46362835',
'AK46362889',
'AK46362893',
'AK46813314',
'AK46224501',
'AK46853465',
'AK46333413',
'AK46224487',
'AK46880117',
'AK46853616',
'AK46619940',
'AK46567212',
'AK46224419',
'AK46174988',
'AK46641565',
'AK46224351',
'AK46730415',
'AK46314905',
'AK46641570',
'AK46224481',
'AK46674897',
'AK46366031',
'AK46730438',
'AK46468436',
'AK46224495',
'AK46224463',
'AK46224471',
'AK46567044',
'AK46674894',
'AK46619956',
'AK46641583',
'AK46657617',
'AK46641724',
'AK46619949',
'AK46619953',
'AK46362851',
'AK46224456',
'AK46224488',
'AK46314884',
'AK46567095',
'AK46775649',
'AK46363035',
'AK46218581',
'AK46468401',
'AK46613115',
'AK46642384',
'AK46641949',
'AK46836277',
'AK46875359',
'AK46799515',
'AK46836444',
'AK46813635',
'AK46826489',
'AK46670560',
'AK46753181',
'AK46657915',
'AK46512404',
'AK46750259',
'AK46219191',
'AK46281263',
'AK46280061',
'AK46283875',
'AK46281405',
'AK46253121',
'AK46298065',
'AK46283683',
'AK46251699',
'AK46671166',
'AK46763962',
'AK46219323',
'AK46292773',
'AK46287385',
'AK46276522',
'AK46182613',
'AK46297960',
'AK46298031',
'AK46209100',
'AK46296966',
'AK46343891',
'AK46084991',
'AB46538980',
'AB46535300',
'AB46536590',
'AB46538201',
'AB46538125',
'AB46534469',
'AB46536998',
'AB46537356',
'AB46539001',
'AB46534764',
'AB46535505',
'AB46538503',
'AB46537488',
'AB46533454',
'AB46534118',
'AB46534489',
'AB46537557',
'AB46538209',
'AB46539222',
'AB46539283',
'AB46534063',
'AB46534016',
'AB46536513',
'AB46533986',
'AB46536380',
'AB46535348',
'AB46537315',
'AB46533528',
'AB46533715',
'AB46535441',
'AB46536798',
'AB46533437',
'AB46537502',
'AB46536002',
'AB46536988',
'AB46533447',
'AB46539054',
'AB46537414',
'AB46535692',
'AB46540265',
'AB46540201',
'AB46540416',
'AK46628245',
'AK46761830',
'AK46302413',
'AK46628730',
'AK46628537',
'AK46448451',
'AK46302661',
'AK46761719',
'AK46610281',
'AK46514182',
'AK46550570',
'AK46359095',
'AK46783938',
'AK46342338',
'AK46301775',
'AK46737328',
'AK46627758',
'AK46487513',
'AK46627694',
'AK46255758',
'AK46615134',
'AK46593547',
'AK46670012',
'AK46387992',
'AK46812739',
'AK46185175',
'AK46220132',
'AK46210681',
'AK45630794',
'AK46337969',
'AK46234821',
'AK46541778',
'AK46408369',
'AK46408022',
'AK46408049',
'AK46591300',
'AK46591299',
'AK46799870',
'AK46799884',
'AK46777234',
'AK46642313',
'AK46642316',
'AK46642322',
'AK46799878',
'AK46470413',
'AK46254235',
'AK46220083',
'AK46672310',
'AK46001358',
'AK46613181',
'AK46613186',
'AK46613190',
'AK46875454',
'AK46875457',
'AK46875460',
'AK46423212',
'AK46672306',
'AK46338271',
'AK46875451',
'AK46813538',
'AK46057097',
'AK46701663',
'AK46701664',
'AK46905418',
'AK46813539',
'AK46799524',
'AK46799525',
'AK46776824',
'AK46701654',
'AK46701655',
'AK46672302',
'AK46672304',
'AK46672305',
'AK46613187',
'AK46613189',
'AK46512232',
'AK46423218',
'AK46363134',
'AK46315133',
'AK46281353',
'AK46281360',
'AK46281362',
'AK46268196',
'AK46253461',
'AK46253470',
'AK46191851',
'AK46191854',
'AK46175262',
'AK46147486',
'AK46112846',
'AK46100413',
'AK46085923',
'AK46057091',
'AK46001354',
'AK46112845',
'AK44976184',
'AK45895207',
'AK46337379',
'AK46238974',
'AK46244484',
'AK46244710',
'AK46054134',
'AK46581279',
'AK46457918',
'AK46456404',
'AK46107491',
'AK46883340',
'AK46724426',
'AK46412677',
'AK46410626',
'AK46442776',
'AK46033642',
'AK45991276',
'AK46710408',
'AK46710415',
            );

            // キャンセル理由の定義
            $reason = '';
            $reasonCode = 8;
            // <-------------------------------------------------------------------------

            $logic = new LogicCancel($this->dbAdapter);

            // 対象データ分ループ
            foreach ($target as $orderId) {
                // $this->logger->info('[' . $orderId . '] Start ');

                // 注文SEQを特定する
                $sql = ' SELECT * FROM T_Order WHERE OrderId = :OrderId ';
                $prm = array(
                    ':OrderId' => $orderId,
                );

                $row = $this->dbAdapter->query($sql)->execute($prm)->current();

                if (!$row) {
                    // 特定できない場合はアラート出力⇒次の行へ
                    $this->logger->warn('<CancelRegister> [' . $orderId . '] OrderId Is Not Found!!');
                    continue;
                }

                // 注文SEQ特定
                $oseq = $row['OrderSeq'];

                // キャンセル申請処理を行う
                try {
                    $logic->applies($oseq, $reason, $reasonCode, 1, false, $userId);
                    $this->logger->info('<CancelRegister> [' . $orderId . '] Complete!! ');
                } catch(OrderCancelException $oce) {
                    $this->logger->warn('<CancelRegister> [' . $orderId . '] Order Is Not Cancel Message = ' . $oce->getMessage());
                    $this->logger->warn('<CancelRegister> [' . $orderId . '] ' . $oce->getTraceAsString());
                }

            }

            // $this->dbAdapter->getDriver()->getConnection()->rollback();
            $this->dbAdapter->getDriver()->getConnection()->commit();

            $exitCode = 0; // 正常終了
$this->logger->info('_data_patch_20200909_1500_CancelRegister.php end');

        } catch( \Exception $e ) {
            try{
                $this->dbAdapter->getDriver()->getConnection()->rollback();
            } catch ( \Exception $err) { }

            // エラーログを出力
            if ( isset($this->logger) ) {
$this->logger->err('<CancelRegister> ' . $e->getMessage());
$this->logger->err('<CancelRegister> ' . $e->getTraceAsString());
            }
        }

        // 終了コードを指定して処理終了
        exit($exitCode);

    }
}

Application::getInstance()->run();
