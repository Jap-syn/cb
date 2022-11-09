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

        try {

            // 実行確認
            echo "Run the Approval batch. Is it OK?(Y/N)";
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
$this->logger->info('_data_patch_20160725_1200_ApprovalRegister.php start');

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
            // 対象の定義
            $target = array(
                'SE25742178',
                'SE25742189',
                'SE25742196',
                'SE25742204',
                'SE25742218',
                'SE25742229',
                'SE25742241',
                'SE25742243',
                'SE25742257',
                'SE25742292',
                'SE25742313',
                'SE25742314',
                'SE25742320',
                'SE25742324',
                'SE25742328',
                'SE25742330',
                'SE25742331',
                'SE25742334',
                'SE25742336',
                'SE25742346',
                'SE25742348',
                'SE25742354',
                'SE25742357',
                'SE25742366',
                'SE25742374',
                'SE25742380',
                'SE25742383',
                'SE25742392',
                'SE25742397',
                'SE25742400',
                'SE25742409',
                'SE25742414',
                'SE25742429',
                'SE25742431',
                'SE25742432',
                'SE25742441',
                'SE25742446',
                'SE25742457',
                'SE25742493',
                'SE25742500',
                'SE25742511',
                'SE25742522',
                'SE25742523',
                'SE25742527',
                'SE25742528',
                'SE25742535',
                'SE25742543',
                'SE25742547',
                'SE25742556',
                'SE25742560',
                'SE25742564',
                'SE25742568',
                'SE25742574',
                'SE25742577',
                'SE25742582',
                'SE25742598',
                'SE25742599',
                'SE25742601',
                'SE25742608',
                'SE25742623',
                'SE25742625',
                'SE25742627',
                'SE25742636',
                'SE25742660',
                'SE25742680',
                'SE25742688',
                'SE25742692',
                'SE25742777',
                'SE25742778',
                'SE25742779',
                'SE25743157',
                'SE25743207',
                'SE25743219',
                'SE25743227',
                'SE25743234',
                'SE25743241',
                'SE25743257',
                'SE25743343',
                'SE25743349',
                'SE25743351',
                'SE25743365',
                'SE25743369',
                'SE25743371',
                'SE25743386',
                'SE25743395',
                'SE25743405',
                'SE25743408',
                'SE25743412',
                'SE25743468',
                'SE25743489',
                'SE25743495',
                'SE25743594',
                'SE25743615',
                'SE25743661',
                'SE25743667',
                'SE25743670',
                'SE25743693',
                'SE25743695',
                'SE25743717',
                'SE25743729',
                'SE25743732',
                'SE25743738',
                'SE25743749',
                'SE25743783',
                'SE25743844',
                'SE25743848',
                'SE25743857',
                'SE25743867',
                'SE25743873',
                'SE25743884',
                'SE25743888',
                'SE25743896',
                'SE25743897',
                'SE25743898',
                'SE25743904',
                'SE25743906',
                'SE25743917',
                'SE25743918',
                'SE25743937',
                'SE25743941',
                'SE25743947',
                'SE25743948',
                'SE25743951',
                'SE25743954',
                'SE25743960',
                'SE25743970',
                'SE25743985',
                'SE25743986',
                'SE25743997',
                'SE25743999',
                'SE25744003',
                'SE25744004',
                'SE25744029',
                'SE25744031',
                'SE25744036',
                'SE25744048',
                'SE25744058',
                'SE25744061',
                'SE25744070',
                'SE25744072',
                'SE25744084',
                'SE25744091',
                'SE25744093',
                'SE25744123',
                'SE25744134',
                'SE25744135',
                'SE25744139',
                'SE25744147',
                'SE25744163',
                'SE25744175',
                'SE25744176',
                'SE25744177',
                'SE25744187',
                'SE25744194',
                'SE25744197',
                'SE25744206',
                'SE25744322',
                'SE25744325',
                'SE25744367',
                'SE25744368',
                'SE25744380',
                'SE25744409',
                'SE25744450',
                'SE25744499',
                'SE25744500',
                'SE25744516',
                'SE25744518',
                'SE25744519',
                'SE25744520',
                'SE25744522',
                'SE25744523',
                'SE25744526',
                'SE25744528',
                'SE25744531',
                'SE25744533',
                'SE25744535',
                'SE25744538',
                'SE25744540',
                'SE25744542',
                'SE25744543',
                'SE25744546',
                'SE25744548',
                'SE25744563',
                'SE25744572',
                'SE25744581',
                'SE25744582',
                'SE25744589',
                'SE25744597',
                'SE25744653',
                'SE25744673',
                'SE25744678',
                'SE25744683',
                'SE25744686',
                'SE25744688',
                'SE25744704',
                'SE25744707',
                'SE25744708',
                'SE25744713',
                'SE25744715',
                'SE25744737',
                'SE25744739',
                'SE25744744',
                'SE25744745',
                'SE25744750',
                'SE25744751',
                'SE25744770',
                'SE25744772',
                'SE25744775',
                'SE25744781',
                'SE25744782',
                'SE25744809',
                'SE25744813',
                'SE25744816',
                'SE25744841',
                'SE25744848',
                'SE25744881',
                'SE25744885',
                'SE25744898',
                'SE25744955',
                'SE25744980',
                'SE25745022',
                'SE25745035',
                'SE25745042',
                'SE25745043',
                'SE25745048',
                'SE25745067',
                'SE25745072',
                'SE25745090',
                'SE25745102',
                'SE25745118',
            );
            // <-------------------------------------------------------------------------

            $this->approvalRun($target, $userId);

            $exitCode = 0; // 正常終了
$this->logger->info('_data_patch_20160725_1200_ApprovalRegister.php end');

        } catch( \Exception $e ) {
            // エラーログを出力
            if ( isset($this->logger) ) {
$this->logger->err('<ApprovalRegister> ' . $e->getMessage());
$this->logger->err('<ApprovalRegister> ' . $e->getTraceAsString());
            }
        }

        // 終了コードを指定して処理終了
        exit($exitCode);

    }

    private function approvalRun($targets, $userId) {
        $mdlo = new TableOrder($this->dbAdapter);
        $mdlc = new TableCancel($this->dbAdapter);
        $mdlps = new TablePayingAndSales($this->dbAdapter);
        $mdlsf = new TableStampFee($this->dbAdapter);
        $mdlosf = new TableOemSettlementFee($this->dbAdapter);
        $mdlocf = new TableOemClaimFee($this->dbAdapter);
        $mdlcc = new TableClaimControl($this->dbAdapter);
        $mail = new CoralMail($this->dbAdapter, $this->mail['smtp']);
        $logicCancel = new LogicCancel($this->dbAdapter);
        $history = new CoralHistoryOrder($this->dbAdapter);

        $j = 0;
        $params = array();
        foreach ($targets as $orderId) {
            $sql = ' SELECT o.OrderSeq, o.P_OrderSeq, can.CancelPhase FROM T_Order o , T_Cancel can WHERE o.OrderSeq = can.OrderSeq AND o.OrderId = :OrderId ';
            $prm = array(
                ':OrderId' => $orderId,
            );
            $row = $this->dbAdapter->query($sql)->execute($prm)->current();

            if (!$row) {
                $this->logger->warn('<ApprovalRegister> [' . $orderId . '] OrderId Is Not Found!!');
                continue;
            }

            $params['ApprovalFlg' . $j] = 'on';
            $params['OrderSeq' . $j] = $row['OrderSeq'];
            $params['phase' . $j] = intval($row['CancelPhase']) - 1;
            $params['P_OrderSeq' . $j] = $row['P_OrderSeq'];
            $params['OrderId' . $j] = $orderId;

            $j++;
        }


        $i = 0;

        // ユーザーIDの取得
        $obj = new TableUser($this->dbAdapter);
        $opId = 74; // 360test

        while(isset($params['OrderSeq' . $i]))
        {
            if (array_key_exists("ApprovalFlg" . $i, $params) && $params['ApprovalFlg' . $i] == "on")  {

                $oseq = $params['OrderSeq' . $i];
                $phase = $params['phase' . $i];
                $p_oseq = $params['P_OrderSeq' . $i];
                $hisOrderseq = array(); // 注文履歴登録用の注文SEQ保持配列
                $orderId = $params['OrderId' . $i];

                try
                {
                    // トランザクション開始
                    $this->dbAdapter->getDriver()->getConnection()->beginTransaction();

                    // 申請中でない場合（キャンセル取消）はスキップ
                    $sql = ' SELECT COUNT(*) CNT FROM T_Order o WHERE o.OrderSeq = :OrderSeq AND Cnl_Status = 1 ';
                    $prm = array(
                            ':OrderSeq' => $oseq,
                    );
                    $cnt = $this->dbAdapter->query($sql)->execute($prm)->current()['CNT'];
                    if ( $cnt == 0 ) {
                        $this->dbAdapter->getDriver()->getConnection()->rollback();
                        continue;
                    }

                    // キャンセルフェーズ単位で処理を切り分け
                    switch ($phase) {
                        case 0: // 未立替・未入金
                            // 2015/10/29 取りまとめ注文の一部キャンセルは認めない → 以下ﾛｼﾞｯｸはｺﾒﾝﾄｱｳﾄ（suzuki_y）
                            //                             // キャンセル承認
                            //                             $this->approval($oseq, $opId, $userId);

                            //                             // 他の未キャンセル注文が存在する場合、請求金額の再計算を行う
                            //                             // →一部キャンセル後の入金判定の為の帳尻あわせ
                            //                             $sql  = ' SELECT cc.ClaimId ';
                            //                             $sql .= '       ,cc.UseAmountTotal ';
                            //                             $sql .= '       ,cc.ClaimAmount ';
                            //                             $sql .= '       ,cc.ClaimedBalance ';
                            //                             $sql .= '       ,cc.MinClaimAmount ';
                            //                             $sql .= '       ,cc.MinUseAmount ';
                            //                             $sql .= '       ,cc.BalanceClaimAmount ';
                            //                             $sql .= '       ,cc.BalanceUseAmount ';
                            //                             $sql .= '  FROM T_ClaimControl cc ';
                            //                             $sql .= ' WHERE cc.OrderSeq = :OrderSeq ';
                            //                             $sql .= '   AND EXISTS( SELECT * FROM T_Order o WHERE o.P_OrderSeq = cc.OrderSeq AND o.Cnl_Status = 0 ) ';

                            //                             $row = $this->dbAdapter->query($sql)->execute(array(':OrderSeq' => $p_oseq))->current();
                            //                             if ($row) {
                            //                                 // 利用額を取得
                            //                                 $sql = ' SELECT UseAmount FROM T_Order WHERE OrderSeq = :OrderSeq ';
                            //                                 $useAmount = $this->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current()['UseAmount'];

                            //                                 // 請求管理更新
                            //                                 $mdlcc->saveUpdate(
                            //                                     array(
                            //                                             'UseAmountTotal'        => ($row['UseAmountTotal']     - $useAmount),
                            //                                             'ClaimAmount'           => ($row['ClaimAmount']        - $useAmount),
                            //                                             'ClaimedBalance'        => ($row['ClaimedBalance']     - $useAmount),
                            //                                             'MinClaimAmount'        => ($row['MinClaimAmount']     - $useAmount),
                            //                                             'MinUseAmount'          => ($row['MinUseAmount']       - $useAmount),
                            //                                             'BalanceClaimAmount'    => ($row['BalanceClaimAmount'] - $useAmount),
                            //                                             'BalanceUseAmount'      => ($row['BalanceUseAmount']   - $useAmount),
                            //                                             'UpdateId'              => $userId,
                            //                                     ),
                            //                                     $row['ClaimId']
                            //                                 );


                            // // TODO:初回請求書再発行処理を行うか否か→袖山さん確認中(20150814_2308_suzuki_h)

                            //                             }
                            //                             $hisOrderseq[] = $oseq;

                            //                             break;
                            // 2015/10/29 取りまとめ注文の一部キャンセルは認めない → 上記ﾛｼﾞｯｸはｺﾒﾝﾄｱｳﾄ（suzuki_y）
                                case 1: // 立替済・未入金
                                case 2: // 立替済・入金済
                                case 3: // 未立替・入金済
                                    // 代表注文単位の承認処理
                                    $sql = ' SELECT OrderSeq FROM T_Order WHERE P_OrderSeq = :P_OrderSeq AND Cnl_Status = 1 ';
                                    $ri = $this->dbAdapter->query($sql)->execute(array(':P_OrderSeq' => $p_oseq));
                                    foreach ( $ri as $row ) {
                                        // キャンセル承認
                                        $this->approval($row['OrderSeq'], $opId, $userId);

                                        $hisOrderseq[] = $row['OrderSeq'];
                                    }
                                    break;
                                default:
                            }

                            // すべての注文がキャンセルされているか確認する
                            $sql = ' SELECT COUNT(1) CNT FROM T_Order WHERE P_OrderSeq = :P_OrderSeq AND Cnl_Status <> 2 ';
                            $orderCnt = $this->dbAdapter->query($sql)->execute(array(':P_OrderSeq' => $p_oseq))->current()['CNT'];

                            // SMBC決済ステーションへ取り消し依頼をする（20150813_1704_suzuki_h）
                            if(in_array($phase, array(0, 1))) {
                                // すべての注文がキャンセルされている場合
                                if ( $orderCnt == 0 ) {
                                    // 入金前キャンセルかつ、全キャンセル状態ならSMBC決済ステーションに取消依頼を送信
                                    $this->requestCancelToSmbc($p_oseq);

                                    // 請求中のJNB口座もクローズする
                                    $reason = $mdlc->findCancel(array('OrderSeq' => $oseq))->current()['CancelReason'];
                                    $jnbAccLogic = new LogicJnbAccount($this->dbAdapter);
                                    $jnbAccLogic->closeAccount($p_oseq, LogicJnbAccount::CLOSE_BY_CANCEL, sprintf("キャンセル理由 '%s' によってキャンセル", $reason));
                                }
                            }

                            foreach ( $hisOrderseq as $val ) {
                                // 注文履歴へ登録
                                $history->InsOrderHistory($val, 72, $userId);
                            }

                            // キャンセルメールの送信
                            try {
                                // 事業者へ送るメールなので、画面上の表示単位で送信することとする
                                // $mail->SendCancelMail($oseq, $userId);
                            }
                            catch(\Exception $e) { ; }

                            // 未入金で、請求済みで、すべての注文がキャンセルされている場合
                            $sql = ' SELECT COUNT(1) CNT FROM T_ClaimControl WHERE OrderSeq = :OrderSeq ';
                            $claimCnt = $this->dbAdapter->query($sql)->execute(array(':OrderSeq' => $p_oseq))->current()['CNT'];
                            if (in_array($phase, array(0, 1))) {
                                if ($claimCnt > 0 && $orderCnt == 0) {
                                    try {
                                        // 購入者へ送るメールなので、請求単位で送信する
                                        // $mail->SendDisposeBillMail($p_oseq, $userId);
                                    }
                                    catch(\Exception $e) { ; }
                                }
                            }

                            $this->dbAdapter->getDriver()->getConnection()->commit();

                            $this->logger->info('<ApprovalRegister> [' . $orderId . '] Complete!! ');

                    }
                    catch(\Exception $e)
                    {
                        $this->dbAdapter->getDriver()->getConnection()->rollBack();
                        $this->logger->warn('<ApprovalRegister> [' . $orderId . '] Order Is Not Approval Message = ' . $e->getMessage());
                        $this->logger->warn('<ApprovalRegister> [' . $orderId . '] ' . $e->getTraceAsString());

                        throw $e;
                    }
                }

                $i++;
            }
    }

    /**
     * 一注文に対するキャンセル承認処理を行う。
     * 取りまとめ注文に対する考慮は、呼び出し元で行う
     * @param int $oseq
     * @param int $opId
     * @param int $userId
     */
    protected function approval($oseq, $opId, $userId) {

        $mdlo = new TableOrder($this->dbAdapter);
        $mdlc = new TableCancel($this->dbAdapter);
        $mdlps = new TablePayingAndSales($this->dbAdapter);
        $mdlsf = new TableStampFee($this->dbAdapter);
        $mdlosf = new TableOemSettlementFee($this->dbAdapter);
        $mdlocf = new TableOemClaimFee($this->dbAdapter);

        // 立替・売上データをキャンセル済みにする。
        $mdlps->toCanceled( $oseq, $userId );

        // 印紙代データをキャンセル済みにする。
        $mdlsf->toCanceled( $oseq, $userId );

        // キャンセル管理データを承認済みにする。
        $mdlc->approve( $oseq, $opId, $userId );

        // 注文データのキャンセル関連のステータスを更新する。
        $mdlo->saveUpdate(
        array(
                'Cnl_Status' => 2,      // キャンセル済み
                'DataStatus' => 91,     // クローズ
                'CloseReason' => 2,     // キャンセルクローズ
                'UpdateId' => $userId   // 更新者
        ),
        $oseq
        );

        // OEMID取得
        $oem_id = $mdlo->getOemId( $oseq );

        // OEM判定
        if(!is_null($oem_id) && $oem_id != 0){
            // OEM決済手数料データをキャンセル済みにする
            $mdlosf->toCanceled( $oseq, $userId );
            // OEM請求手数料データをキャンセル済みにする
            $mdlocf->toCanceled( $oseq, $userId );
        }

    }


    /**
     * SMBC決済ステーションに登録されている請求情報の取り消しを試行する
     *
     * @param int $oseq 注文SEQ
     */
    protected function requestCancelToSmbc($oseq)
    {
        $logger = null;
        try {
            $logger = $this->logger;
            if($logger) {
                $logger->debug(sprintf('[Logic_Cancel::requestCancelToSmbc] oseq = %s', $oseq));
            }
        } catch(\Exception $err) {}
        $cnlLogic = LogicSmbcRelation::openCancelService($this->dbAdapter, $logger);
        try {
            $cnlLogic->execCancelByOrderSeq($oseq);
        } catch(\Exception $err) {
            try {
                if($logger) {
                    $logger->warn(sprintf('[requestCancelToSmbc] an error has occured. oseq = %s, err = %s (%s)', $oseq, $err->getMessage(), get_class($err)));
                }
            } catch(\Exception $innerError) {
            }
        }
    }
}

Application::getInstance()->run();
