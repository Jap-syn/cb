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
use Coral\Base\Auth\BaseAuthUtility;
use Coral\Base\BaseGeneralUtils;
use Coral\Base\BaseLog;

use Zend\Config\Reader\Ini;
use Zend\Db\Adapter\Adapter;
use Zend\Json\Json;

use models\Logic\Exception\LogicClaimException;
use models\Logic\LogicTemplate;

use models\Table\TableSystemProperty;
use models\Table\TablePayingAndSales;
use models\Table\TableUser;
use models\Table\TableOemClaimFee;

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
     * アプリケーションを実行します。
     *
     * @access public
     */
    public function run() {

        $exitCode = 1;
        $isBeginTran = false;

        try {

            // データベースアダプタをiniファイルから初期化します
            $configPath = __DIR__ . '/../module/cbadmin/config/config.ini';
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

$this->logger->info('credittransfer_update.php start');

            // 設定をシステムプロパティテーブルから読み込み
            $apinfo = $this->getApplicationiInfo($this->dbAdapter, 'cbadmin');

            // ユーザーID取得
            $mdlu = new TableUser($this->dbAdapter);
            $userId = $mdlu->getUserId(TableUser::USERCLASS_SYSTEM, TableUser::SEQ_BATCH_USER);

            // メイン処理
            $this->credit_transfer_update($userId);

$this->logger->info('credittransfer_update.php end');

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
     * 請求手数料を口座振替用の値に更新する。
     *
     */
    protected function credit_transfer_update($userId) {

        // 処理対象抽出SQL
        $sqlMain =<<<EOQ
SELECT pas.Seq
     , pas.OrderSeq
     , IFNULL(pas.ClaimFee, 0) AS ClaimFee
     , IFNULL(pas.ChargeAmount, 0) AS ChargeAmount
     , ao.CreditTransferRequestFlg
     , DATE_FORMAT(pas.RegistDate, '%Y-%m-%d') AS RegistDate
FROM T_PayingAndSales pas
     INNER JOIN T_Order o
             ON pas.OrderSeq = o.OrderSeq
     INNER JOIN AT_Order ao
             ON ao.OrderSeq = o.OrderSeq
WHERE 1 = 1
  AND pas.CreditTransferUpdFlg = 0
  AND ao.CreditTransferRequestFlg > 0
  AND o.DataStatus >= 51
ORDER BY
  pas.OrderSeq
, pas.Seq
EOQ;

        //
        $sqlSub01 =<<<EOQ
SELECT ec.RequestStatus
     , ec.ClaimFeeFlg
     , ec.EntCustSeq
     , IFNULL(sit.FirstCreditTransferClaimFee, 0) AS FirstCreditTransferClaimFee
     , IFNULL(sit.FirstCreditTransferClaimFeeWeb, 0) AS FirstCreditTransferClaimFeeWeb
     , IFNULL(sit.CreditTransferClaimFee, 0) AS CreditTransferClaimFee
     , IFNULL(sit.OemFirstCreditTransferClaimFee, 0) AS OemFirstCreditTransferClaimFee
     , IFNULL(sit.OemFirstCreditTransferClaimFeeWeb, 0) AS OemFirstCreditTransferClaimFeeWeb
     , IFNULL(sit.OemCreditTransferClaimFee, 0) AS OemCreditTransferClaimFee
     , e.OemId
FROM T_Order o
     INNER JOIN AT_Order ao
             ON ao.OrderSeq = o.OrderSeq
     INNER JOIN T_Customer c
             ON c.OrderSeq = o.OrderSeq
     INNER JOIN T_EnterpriseCustomer ec
             ON ec.EntCustSeq = c.EntCustSeq
     INNER JOIN T_Site sit
             ON sit.SiteId = o.SiteId
     INNER JOIN T_Enterprise e
             ON e.EnterpriseId = sit.EnterpriseId
WHERE o.OrderSeq = :OrderSeq
ORDER BY
  o.OrderSeq
EOQ;

        // 処理結果反映SQL
        $sqlUpdate =<<<EOQ
UPDATE T_PayingAndSales
SET CreditTransferUpdFlg = 1
  , ClaimFee = :ClaimFee
  , ChargeAmount = :ChargeAmount
  , UpdateId = :UpdateId
  , UpdateDate = :UpdateDate
WHERE  Seq = :Seq
EOQ;

        // 処理結果反映SQL(加盟店顧客)
        $sqlUpdateTec =<<<EOQ
UPDATE T_EnterpriseCustomer
SET ClaimFeeFlg = 1
  , UpdateId = :UpdateId
  , UpdateDate = :UpdateDate
WHERE  EntCustSeq in ( :EntCustSeq )
EOQ;

        // SQL実行
        $sql =$sqlMain;
        $ri = $this->dbAdapter->query($sql)->execute(null);
        $mainDatas = ResultInterfaceToArray($ri);

        $entCustSeq = [];
        $TecUpdateFlg = 0;
        $claimFeeFlg = 0;
        //---------------------------------------
        // 2-1. 対象の注文を検索し、全件ループ
        foreach ($mainDatas as $mData) {

            $claimFee = 0;
            $chargeAmount = 0;
            $creditTransferRequestFlg = $mData['CreditTransferRequestFlg'];
            $registDate = $mData['RegistDate'];

            // 2-1-1. 注文に紐づく手数料を取得する。
            $sql =$sqlSub01;
            $subDatas = $this->dbAdapter->query($sql)->execute(
                array(
                  ':OrderSeq' => $mData['OrderSeq']
                )
            )->current();
            $requestStatus = $subDatas['RequestStatus'];
            $claimFeeFlg = $subDatas['ClaimFeeFlg'];

            // 初回請求情報を取得する
            $sql = "SELECT * FROM T_ClaimHistory WHERE OrderSeq = :OrderSeq AND ClaimPattern = 1 AND ValidFlg = 1 ";
            $clmhis = $this->dbAdapter->query($sql)->execute(
                array(
                    ':OrderSeq' => $mData['OrderSeq']
                )
            )->current();

            //OEM口振紙初回登録手数料（税抜）,OEM口振WEB初回登録手数料（税抜）,OEM口振引落手数料：（税抜）対応
          if($subDatas['OemId'] !== "0" && !is_null($subDatas['OemId'])){
             $oemdata = new TableOemClaimFee($this->dbAdapter);
             $oemprocess = $oemdata->saveOemClaimFee2($mData['OrderSeq'],$registDate,$clmhis['CreditTransferMethod'],$subDatas,$mData['ClaimFee']);
           }

            $mdlSysP = new TableSystemProperty($this->dbAdapter);
            if ($clmhis['CreditTransferMethod'] == 2) {
//            if ($creditTransferRequestFlg == '1' && is_null($claimFeeFlg)) {

                // 2-1-1-1.口座振替申込み区分=1 の注文 かつ 加盟店顧客.請求手数料フラグ=NULLの場合
                $claimFee = (int)$subDatas['FirstCreditTransferClaimFeeWeb'];
                $claimFee = $mdlSysP->getIncludeTaxAmount($registDate, $claimFee);
                $TecUpdateFlg = 1;
                $entCustSeq[] = $subDatas['EntCustSeq'];
            } elseif ($clmhis['CreditTransferMethod'] == 1) {
//            } elseif ($creditTransferRequestFlg == '2' && is_null($claimFeeFlg)) {

                // 2-1-1-2.口座振替申込み区分=2 の注文 かつ 加盟店顧客.請求手数料フラグ=NULLの場合
                $claimFee = (int)$subDatas['FirstCreditTransferClaimFee'];
                $claimFee = $mdlSysP->getIncludeTaxAmount($registDate, $claimFee);
                $TecUpdateFlg = 1;

                $entCustSeq[] = $subDatas['EntCustSeq'];
            } elseif ($clmhis['CreditTransferMethod'] == 3) {
//            } elseif ($creditTransferRequestFlg > '0' && $requestStatus == '2') {

                // 2-1-1-3.口座振替申込み区分 > 0 の注文 かつ 加盟店顧客.申込みステータス=2（完了）の場合
                $claimFee = (int)$subDatas['CreditTransferClaimFee'];
                $claimFee = $mdlSysP->getIncludeTaxAmount($registDate, $claimFee);

            } else {

                // 2-1-1-4.上記以外の場合
                $claimFee = $mData['ClaimFee'];

            }

            // 立替金額 = 2-1.で取得した立替金額 - 変数.請求手数料 + 2-1.で取得した請求手数料
            $chargeAmount = $mData['ChargeAmount'] - $claimFee + $mData['ClaimFee'];

            // 2-1-2. 立替・売上管理の更新処理を行う
            $sql  = $sqlUpdate;
            $this->dbAdapter->query($sql)->execute(
                array(
                  ':ClaimFee' => $claimFee
                , ':ChargeAmount' => $chargeAmount
                , ':UpdateId' => $userId
                , ':UpdateDate' => date('Y-m-d H:i:s')
                , ':Seq' => $mData['Seq']
                )
            );
        }

        if($TecUpdateFlg == 1){
            // 2-1-3. 2-1-1-1. または 2-1-1-2. で請求手数料の設定を行った場合、加盟店顧客の更新処理を行う
            $sql  = $sqlUpdateTec;
            foreach ($entCustSeq as $data){
            $this->dbAdapter->query($sql)->execute(
            array(
                    ':UpdateId' => $userId
                    , ':UpdateDate' => date('Y-m-d H:i:s')
                    , ':EntCustSeq' => $data
                )
            );
            }
        }

        return;
    }
}

Application::getInstance()->run();
