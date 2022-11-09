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
use Coral\Base\BaseGeneralUtils;
use models\Table\TableClaimHistory;
use models\Table\ATableReceiptControl;
use models\Table\TableMailSendHistory;
use models\Table\TableMailTemplate;
use models\Table\TablePayingAndSales;
use models\Table\TableOrder;
use Coral\Coral\History\CoralHistoryOrder;
use Coral\Coral\Mail\CoralMail;
use Coral\Coral\Mail\CoralMailException;

/**
 * アプリケーションクラスです。
 * 与信完了のメール再送信処理を行うパッチです。
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
     * @var smtp
     */
    public $smtp;

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
            // smtpの設定
            $this->smtp = $data['mail']['smtp'];
            // ログ設定の読み込み
            $logConfig = $data['log'];
            // 標準ログクラス初期化
            $this->logger = BaseLog::createFromArray( $logConfig );

$this->logger->info('_data_patch_20181219_1400.php start');
            $globalConfig = include __DIR__ . '/../config/autoload/global.php';
            // 接続時間を設定する
            $rds_session_timezone = $globalConfig['RDS_SESSION_TIMEZONE'];
            if (isset($rds_session_timezone)) {
                $this->dbAdapter->query('SET SESSION time_zone = :time_zone')->execute(array(':time_zone'=>$rds_session_timezone));
            }

$this->dbAdapter->getDriver()->getConnection()->beginTransaction();

            // 本処理
            $this->_exec();

$this->dbAdapter->getDriver()->getConnection()->commit();
//$this->dbAdapter->getDriver()->getConnection()->rollback();

$this->logger->info('_data_patch_20181219_1400.php end');
            $exitCode = 0; // 正常終了
        } catch( \Exception $e ) {
            try{
                $this->dbAdapter->getDriver()->getConnection()->rollback();
            } catch ( \Exception $err) { }
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
     * 本処理
     */
    public function _exec()
    {
        $data = array(
            '38931228'
           ,'38931309'
           ,'38931381'
           ,'38931382'
           ,'38931454'
           ,'38931473'
           ,'38931478'
           ,'38931510'
           ,'38931519'
           ,'38931625'
           ,'38932024'
           ,'38932050'
           ,'38932051'
           ,'38932053'
           ,'38932054'
           ,'38932138'
           ,'38932139'
           ,'38932142'
           ,'38932143'
           ,'38932145'
           ,'38932146'
           ,'38932148'
           ,'38932192'
           ,'38932193'
           ,'38932199'
           ,'38932200'
           ,'38932234'
           ,'38932235'
           ,'38932244'
           ,'38932245'
           ,'38932246'
           ,'38932247'
           ,'38932248'
           ,'38932249'
           ,'38932250'
           ,'38932256'
           ,'38932326'
           ,'38932327'
           ,'38932328'
           ,'38932329'
           ,'38932330'
           ,'38932331'
           ,'38932401'
           ,'38932402'
           ,'38932620'
           ,'38932621'
           ,'38932622'
           ,'38932623'
           ,'38932624'
           ,'38932674'
           ,'38932675'
           ,'38932676'
           ,'38932677'
           ,'38932678'
           ,'38932692'
           ,'38932693'
           ,'38932694'
           ,'38932702'
           ,'38932707'
           ,'38933570'
           ,'38933677'
           ,'38933678'
           ,'38933679'
           ,'38933680'
           ,'38933805'
           ,'38933806'
           ,'38933807'
           ,'38933808'
           ,'38933809'
           ,'38934151'
           ,'38934152'
           ,'38934153'
           ,'38934154'
           ,'38934155'
           ,'38934156'
           ,'38934182'
           ,'38934183'
           ,'38934184'
           ,'38934220'
           ,'38934221'
           ,'38934235'
           ,'38934236'
           ,'38934237'
           ,'38934442'
           ,'38934443'
           ,'38934444'
           ,'38934445'
           ,'38934540'
           ,'38934832'
           ,'38934833'
           ,'38934834'
           ,'38934849'
           ,'38934850'
           ,'38934851'
           ,'38934857'
           ,'38935039'
           ,'38935040'
           ,'38935041'
           ,'38935042'
           ,'38935044'
           ,'38935045'
           ,'38935046'
           ,'38935047'
           ,'38935132'
           ,'38935133'
           ,'38935134'
           ,'38935135'
           ,'38935147'
           ,'38935148'
           ,'38935149'
           ,'38935150'
           ,'38935241'
           ,'38935242'
           ,'38935243'
           ,'38935271'
           ,'38935272'
           ,'38935273'
           ,'38935274'
           ,'38935275'
           ,'38935276'
           ,'38935285'
           ,'38935330'
           ,'38935331'
           ,'38935563'
           ,'38935564'
           ,'38935565'
           ,'38935566'
           ,'38935607'
           ,'38935608'
           ,'38935609'
           ,'38935610'
           ,'38935611'
           ,'38935617'
           ,'38935618'
           ,'38935779'
           ,'38935781'
           ,'38935830'
           ,'38935831'
           ,'38935832'
           ,'38935833'
           ,'38935834'
           ,'38935835'
           ,'38935900'
           ,'38935901'
           ,'38935902'
           ,'38935903'
           ,'38935914'
           ,'38935915'
           ,'38935920'
           ,'38935922'
           ,'38935925'
           ,'38935939'
           ,'38935940'
           ,'38935986'
           ,'38935989'
           ,'38936011'
           ,'38936157'
           ,'38936158'
           ,'38936160'
           ,'38936161'
           ,'38936162'
           ,'38936180'
           ,'38936181'
           ,'38936182'
           ,'38936183'
           ,'38936184'
           ,'38936185'
           ,'38936186'
           ,'38936232'
           ,'38936235'
           ,'38936290'
           ,'38936291'
           ,'38936292'
           ,'38936293'
           ,'38936442'
           ,'38936443'
           ,'38936515'
           ,'38936516'
           ,'38936517'
           ,'38936657'
           ,'38936658'
           ,'38936659'
           ,'38936714'
           ,'38936715'
           ,'38936716'
           ,'38936718'
           ,'38936719'
           ,'38936720'
           ,'38936721'
           ,'38936729'
           ,'38936730'
           ,'38936731'
           ,'38936732'
           ,'38936733'
           ,'38936739'
           ,'38936743'
           ,'38936931'
           ,'38936992'
           ,'38936993'
           ,'38936994'
           ,'38936995'
           ,'38936996'
           ,'38936999'
           ,'38937048'
           ,'38937106'
           ,'38937107'
           ,'38937111'
           ,'38937112'
           ,'38937131'
           ,'38937132'
           ,'38937133'
           ,'38937134'
           ,'38937135'
           ,'38937136'
           ,'38937137'
           ,'38937138'
           ,'38937139'
           ,'38937173'
           ,'38937174'
           ,'38937175'
           ,'38937176'
           ,'38937177'
           ,'38937181'
           ,'38937243'
           ,'38937244'
           ,'38937245'
           ,'38937246'
           ,'38937247'
           ,'38937248'
           ,'38937258'
           ,'38937291'
           ,'38937311'
           ,'38937312'
           ,'38937330'
           ,'38937331'
           ,'38938276'
           ,'38938277'
           ,'38938279'
           ,'38938280'
           ,'38941452'
           ,'38942845'
           ,'38942847'
           ,'38942849'
           ,'38943925'
           ,'38943927'
           ,'38944060'
           ,'38945918'
           ,'38948722'
           ,'38948724'
           ,'38948725'
           ,'38948777'
           ,'38948991'
           ,'38950040'
           ,'38950041'
           ,'38951248'
           ,'38951249'
           ,'38951250'
           ,'38951252'
           ,'38951253'
           ,'38953262'
           ,'38953263'
           ,'38953265'
           ,'38953266'
           ,'38953269'
           ,'38953359'
           ,'38954117'
           ,'38955081'
           ,'38955082'
           ,'38955083'
           ,'38955084'
           ,'38955305'
           ,'38955306'
           ,'38955359'
           ,'38955360'
           ,'38955361'
           ,'38955362'
           ,'38955367'
           ,'38955368'
           ,'38955371'
           ,'38955372'
           ,'38955373'
           ,'38955447'
           ,'38955448'
           ,'38955449'
           ,'38955450'
           ,'38955476'
           ,'38955479'
           ,'38955480'
           ,'38955482'
           ,'38955524'
           ,'38955525'
           ,'38955662'
           ,'38955663'
           ,'38955664'
           ,'38955667'
           ,'38955668'
           ,'38955679'
           ,'38955720'
           ,'38955737'
           ,'38955738'
           ,'38955774'
           ,'38955801'
           ,'38955802'
           ,'38955803'
           ,'38955882'
           ,'38956063'
           ,'38956067'
           ,'38956115'
           ,'38956291'
           ,'38956292'
           ,'38956306'
           ,'38956307'
           ,'38956308'
           ,'38956309'
           ,'38956310'
           ,'38956311'
           ,'38956312'
           ,'38956313'
           ,'38956314'
           ,'38956315'
           ,'38956316'
           ,'38956317'
           ,'38956338'
           ,'38956339'
           ,'38956340'
           ,'38956341'
           ,'38956489'
           ,'38956490'
           ,'38956491'
           ,'38956492'
           ,'38956494'
           ,'38956502'
           ,'38956503'
           ,'38956670'
           ,'38956680'
           ,'38956681'
           ,'38956697'
           ,'38956711'
           ,'38956712'
           ,'38956721'
           ,'38956750'
           ,'38956751'
           ,'38956752'
           ,'38956753'
           ,'38956754'
           ,'38956806'
           ,'38956807'
           ,'38956808'
           ,'38956824'
           ,'38956835'
           ,'38956836'
           ,'38956870'
           ,'38956876'
           ,'38956877'
           ,'38956878'
           ,'38956879'
        );

        foreach($data as $key) {

            $mailSendSeq = null;

            $mdlmsh = new TableMailSendHistory($this->dbAdapter);
            $seq = (int)$key;

            // ユーザID
            $userId = 1;

            try {
                // 再送信用のデータをメール送信履歴からデータを取得
                // 【注意】対象データ作成時に再送信先が明確になっている前提
                $msql = "SELECT * FROM T_MailSendHistory WHERE MailSendSeq = :Seq LIMIT 1";
                $mri = $this->dbAdapter->query($msql)->execute(array(':Seq' => $seq));
                $mData = $mri->current();

                // 取得したデータからOemIdを取得
                $esql = "SELECT * FROM T_Enterprise WHERE EnterpriseId = :Eid LIMIT 1";
                $eri = $this->dbAdapter->query($esql)->execute(array(':Eid' => $mData['EnterpriseId']));
                $eData = $eri->current();

                // OemIdからメールテンプレートの取得
                $mdlmt = new TableMailTemplate($this->dbAdapter);
                $template = $mdlmt->findMailTemplate(3, $eData['OemId'])->current();

                // メール本文の作成
                $body = ' **************************************************************************************' . "\r\n" . ' 【お詫び】' . "\r\n" . '    12/19(水) 5:30～14:43 の間、「与信完了のお知らせ」メールが配信されない不具合が発生しておりました。' . "\r\n" . '    配信されなかった内容を本メールにてお知らせ致しますので、ご確認の程お願いします。' . "\r\n" . '    この度はご迷惑をお掛けし申し訳ございませんでした。' . "\r\n" . ' **************************************************************************************' . "\r\n\r\n" . $mData['Body'];

                // メール送信履歴登録
                // 取得したデータをそのまま入れる(日付、エラーフラグ以外)
                $mailSendSeq = $mdlmsh->saveNew(array(
                        'MailTemplateId' => $mData['MailTemplateId'],
                        'OrderSeq' => $mData['OrderSeq'],
                        'EnterpriseId' => $mData['EnterpriseId'],
                        'ManCustId' => $mData['ManCustId'],
                        'ToAddress' => $mData['ToAddress'],
                        'CcAddress' => $mData['CcAddress'],
                        'BccAddress' => $mData['BccAddress'],
                        'Subject' => $mData['Subject'],
                        'Body' => $body,
                        'MailSendDate' => date('Y-m-d H:i:s'),
                        'ErrFlg' => 0,
                        'ErrReason' => null,
                        'RegistId' => $userId,
                        'UpdateId' => $userId,
                        'ValidFlg' => 1,
                ));

                $mail = new CoralMail($this->dbAdapter, $this->smtp);

                $mail->sendDone(
                $template['FromTitle'],
                $template['FromAddress'],
                $eData['EnterpriseNameKj'],
                $mData['ToAddress'],
                $mData['Subject'],
                $body
                );

            } catch(\Exception $e) {
                if (isset($mailSendSeq)) {
                    // メール送信履歴を登録した場合、エラー理由を更新
                    $mdlmsh->saveUpdate(array(
                            'ErrFlg' => 1,
                            'ErrReason' => $e->getMessage(),
                            'UpdateId' => $userId,
                    ), $mailSendSeq);
                }
                throw new CoralMailException( 'cannot sent examination completed mail to each subscriber.', 0, $e );
            }

        }

        return;
    }
}

Application::getInstance()->run();
