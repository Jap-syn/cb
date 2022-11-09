<?php
chdir(dirname(__DIR__));

require 'init_autoloader.php';

use Coral\Base\Application\BaseApplicationAbstract;
use Coral\Coral\History\CoralHistoryOrder;
use Coral\Coral\Mail\CoralMail;
use models\Logic\Jnb\LogicJnbAccount;
use models\Logic\LogicSmbcRelation;
use models\Logic\Smbcpa\LogicSmbcpaAccount;
use models\Table\ATableOrder;
use models\Table\TableCancel;
use models\Table\TableOemClaimFee;
use models\Table\TableOemSettlementFee;
use models\Table\TableOrder;
use models\Table\TablePayingAndSales;
use models\Table\TableSBPaymentSendResultHistory;
use models\Table\TableSite;
use models\Table\TableStampFee;
use Zend\Db\Adapter\Adapter;
use Zend\Config\Reader\Ini;
use Coral\Base\BaseLog;
use models\Logic\LogicCancel;
use models\Table\TableUser;
use Zend\Json\Json;

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
	 * ログクラス
	 *
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

$this->logger->info('autocancel.php start');

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
            $ｔhreadNo = (int)$_SERVER['argv'][1];

            // 主処理
            $this->exec($ｔhreadNo);

$this->logger->info('autocancel.php end');
            $exitCode = 0; // 正常終了

    	} catch( \Exception $e ) {
    	    // エラーログを出力
    	    if ( isset($this->logger) ) {
$this->logger->err($e->getMessage());
    	    }
    	}

    	// 終了コードを指定して処理終了
    	exit($exitCode);

	}

	private function exec($threadNo) {
        $sql =<<<EOQ
SELECT o.OrderId, o.OrderSeq
FROM T_Order o
LEFT JOIN T_Enterprise e ON o.EnterpriseId = e.EnterpriseId
WHERE o.DataStatus = 31
AND DATE_ADD(o.Incre_DecisionDate, INTERVAL e.CreditJudgeValidDays DAY) < CURRENT_DATE
AND e.AutoCancelThreadNo = :threadNo
EOQ;
        $ri = $this->dbAdapter->query($sql)->execute(array('threadNo' => $threadNo));
        $datas = ResultInterfaceToArray($ri);

        // ユーザーIDの取得
        $obj = new TableUser($this->dbAdapter);
        $userId = $obj->getUserId(TableUser::USERCLASS_SYSTEM, TableUser::SEQ_BATCH_USER);

        // キャンセル申請
        $cancel_list = array();
        $canceller = new LogicCancel($this->dbAdapter);
        foreach ($datas as $data) {
            try {
                $canceller->applies( (int)$data['OrderSeq'], '伝票番号未登録で与信有効期間切れ', 8, 0, true, $userId );
                $cancel_list[] = $data['OrderSeq'];
            } catch (\Exception $e) {
                $this->logger->warn('キャンセル申請失敗 : ' . $data['OrderSeq'].' メッセージ：'.$e->getMessage());
            }
        }

        // キャンセル申請
        $this->cancelExec($cancel_list, $userId);
    }

    /**
     * キャンセル確認実行アクション
     */
    private function cancelExec($orderSeq_list, $userId)
    {
        $mdlo = new TableOrder($this->dbAdapter);
        $mdlc = new TableCancel($this->dbAdapter);
        $mail = new CoralMail($this->dbAdapter, $this->mail['smtp']);
        $mdlSBPsrh = new TableSBPaymentSendResultHistory( $this->dbAdapter );

        $history = new CoralHistoryOrder($this->dbAdapter);

        $mainSql =<<<EOQ
SELECT o.OrderSeq,o.P_OrderSeq,can.CancelPhase
FROM T_Order o LEFT JOIN T_Cancel can ON o.OrderSeq = can.OrderSeq 
WHERE o.OrderSeq = :OrderSeq
EOQ;

        $errorListSBPayment = array();

        foreach ($orderSeq_list as $oseq) {
            $ri = $this->dbAdapter->query($mainSql)->execute(array('OrderSeq' => $oseq));
            $datas = ResultInterfaceToArray($ri);

            $phase = $datas[0]['CancelPhase'];
            $p_oseq = $datas[0]['P_OrderSeq'];
            $hisOrderseq = array(); // 注文履歴登録用の注文SEQ保持配列

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
                    continue;
                }

                // キャンセルフェーズ単位で処理を切り分け
                switch ($phase) {
                    case 0: // 未立替・未入金
                    case 1: // 立替済・未入金
                    case 2: // 立替済・入金済
                    case 3: // 未立替・入金済
                        // 代表注文単位の承認処理
                        $sql = ' SELECT OrderSeq FROM T_Order WHERE P_OrderSeq = :P_OrderSeq AND Cnl_Status = 1 ';
                        $ri = $this->dbAdapter->query($sql)->execute(array(':P_OrderSeq' => $p_oseq));
                        foreach ( $ri as $row ) {
                            // キャンセル承認
                            $this->approval($row['OrderSeq'], $userId, $userId);

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

                        // 請求中のSMBCバーチャル口座もクローズする
                        $reason = $mdlc->findCancel(array('OrderSeq' => $oseq))->current()['CancelReason'];
                        $smbcpaAccLogic = new LogicSmbcpaAccount($this->dbAdapter);
                        $smbcpaAccLogic->closeAccount($p_oseq, LogicSmbcpaAccount::CLOSE_BY_CANCEL, sprintf("キャンセル理由 '%s' によってキャンセル", $reason));
                    }
                }

                // 注文_会計の取得
                $mdlao = new ATableOrder( $this->dbAdapter );
                $aoInfo = $mdlao->find( $oseq )->current();
                // 届いてから決済のクレジット払いトラッキングIDの取得
                $trackingId = '';
                if ( !empty($aoInfo['ExtraPayType']) && $aoInfo['ExtraPayType'] == '1' ) {
                    $trackingId = $aoInfo['ExtraPayKey'];
                    $extraPayNote = Json::decode( $aoInfo['ExtraPayNote'] );
                    $spsTransactionId = $extraPayNote->sps_transaction_id;
                }

                // クレジットカード決済【取消返金要求】を行う
                if ( !empty($trackingId) ) {
                    // トラッキングIDを保持している場合
                    // 注文情報の取得
                    $orderInfo = $mdlo->find( $oseq )->current();

                    // サイト情報の取得
                    $mdlsit = new TableSite($this->dbAdapter);
                    $siteInfo = $mdlsit->findSite($orderInfo['SiteId'])->current();

                    $params['OrderSeq']    = $oseq;
                    $params['tracking_id'] = $trackingId;
                    $params['BasicId']     = $siteInfo['BasicId'];
                    $params['BasicPw']     = $siteInfo['BasicPw'];
                    $params['sps_transaction_id'] = $spsTransactionId;
                    $params['merchantid']  = $siteInfo['MerchantId']; // マーチャントID
                    $params['serviceid']   = $siteInfo['ServiceId'];  // サービスID
                    $params['hashkey']     = $siteInfo['HashKey'];    // ハッシュキー

                    $rtn = $this->_SBPaymentCancelRequest($params, $resSBP, $err_code, $errorMessages);

                    // 連携履歴の取得
                    $sbpsrHistory = $mdlSBPsrh->findOrderSeq( $oseq )->current();
                    $sbpsrHistoryCnt = $mdlSBPsrh->findOrderSeq( $oseq )->count();
                    // 連携履歴の登録
                    if ( !empty($resSBP) ) {
                        $sbpsrHistory['ResSpsTransactionId'] = (empty($resSBP['res_sps_transaction_id']) ? null : $resSBP['res_sps_transaction_id']);
                        $sbpsrHistory['ResProcessDate'] = (empty($resSBP['res_process_date']) ? null : $resSBP['res_process_date']);
                        $sbpsrHistory['ResErrCode'] = (empty($resSBP['res_err_code']) ? null : $resSBP['res_err_code']);
                        $sbpsrHistory['ResDate']    = $resSBP['res_date'];
                        $sbpsrHistory['ResResult'] = $resSBP['res_result'];
                    } else {
                        $sbpsrHistory['ResSpsTransactionId'] = null;
                        $sbpsrHistory['ResProcessDate'] = null;
                        $sbpsrHistory['ResErrCode'] = null;
                        $sbpsrHistory['ResDate']    = null;
                        $sbpsrHistory['ResResult'] = 'NG';
                    }
                    $sbpsrHistory['ErrorMessage']  = $errorMessages;
                    $sbpsrHistory['UpdateId']  = $userId;

                    if ( $sbpsrHistoryCnt <= 0 ) {
                        $sbpsrHistory['OrderSeq']  = $oseq;
                        $sbpsrHistory['OrderId']   = $orderInfo['OrderId'];
                        $sbpsrHistory['RegistId']  = $userId;
                        $mdlSBPsrh->saveNew( $sbpsrHistory );
                    } else {
                        $sbpsrSeq = $sbpsrHistory['Seq'];
                        $mdlSBPsrh->saveUpdate( $sbpsrHistory, $sbpsrSeq );
                    }
                }

                foreach ( $hisOrderseq as $val ) {
                    // 注文履歴へ登録
                    $history->InsOrderHistory($val, 72, $userId);
                }

                // キャンセルメールの送信
                try {
                    // 事業者へ送るメールなので、画面上の表示単位で送信することとする
                    $mail->SendCancelMail($oseq, $userId);
                }
                catch(\Exception $e) { ; }

                // 未入金で、請求済みで、すべての注文がキャンセルされている場合
                $sql = ' SELECT COUNT(1) CNT FROM T_ClaimControl WHERE OrderSeq = :OrderSeq ';
                $claimCnt = $this->dbAdapter->query($sql)->execute(array(':OrderSeq' => $p_oseq))->current()['CNT'];
                if (in_array($phase, array(0, 1))) {
                    if ($claimCnt > 0 && $orderCnt == 0) {
                        try {
                            // 購入者へ送るメールなので、請求単位で送信する
                            $mail->SendDisposeBillMail($p_oseq, $userId);
                        }
                        catch(\Exception $e) { ; }
                    }
                }
                // トランザクション終了 commit
                $this->dbAdapter->getDriver()->getConnection()->commit();
            }
            catch(\Exception $e)
            {
                // トランザクション終了 rollback
                $this->dbAdapter->getDriver()->getConnection()->rollBack();
                throw $e;
            }
        }
    }

    /**
     * (SB Payment Service)クレジットカード決済：取消返金要求
     *
     * @param array $params パラメタ
     * @param string $err_code エラーコード(8桁)
     * @param array $errorMessages エラーメッセージ文字列の配列(※通信障害系の内容)
     * @return boolean true;成功／false:失敗
     */
    private function _SBPaymentCancelRequest($params, &$resSBP, &$err_code, &$errorMessages) {

        $request_date = date('YmdHis');

        // チェックサム（項目順に結合＋ハッシュキー）
        $sha1str =  $params['merchantid']. $params['serviceid']. $params['sps_transaction_id']. $params['tracking_id']. $request_date. $request_date. '600'. $params['hashkey'];

        $req  = '<?xml version="1.0" encoding="Shift_JIS"?>';
        $req .= '<sps-api-request id="ST02-00303-101">';
        $req .= '<merchant_id>'        . $params['merchantid']        . '</merchant_id>';
        $req .= '<service_id>'         . $params['serviceid']         . '</service_id>';
        $req .= '<sps_transaction_id>' . $params['sps_transaction_id']. '</sps_transaction_id>';
        $req .= '<tracking_id>'        . $params['tracking_id']       . '</tracking_id>';
        $req .= '<processing_datetime>'. $request_date                . '</processing_datetime>';
        $req .= '<request_date>'       . $request_date                . '</request_date>';
        $req .= '<limit_second>600</limit_second>';
        $req .= '<sps_hashcode>'       . sha1($sha1str)               . '</sps_hashcode>'; // 40文字の16進数
        $req .= '</sps-api-request>';

        // リクエスト送信
        $xmlstr = '';
        $orderSeq = $params['OrderSeq'];
        $basicId = $params['BasicId'];
        $basicPw = $params['BasicPw'];
        $isSuccess = $this->_SBPaymentSendRequest($orderSeq, $basicId, $basicPw, $req, $xmlstr, $errorMessages);
        if ($isSuccess == false) {
            return false;
        }
        $xml = simplexml_load_string($xmlstr);
        $json = json_encode($xml);
        $resSBP = json_decode($json, true);

        if ($resSBP['res_result'] == 'NG') {
            $err_code = $resSBP['res_err_code'];
            return false;
        }

        return true;
    }

    /**
     * 一注文に対するキャンセル承認処理を行う。
     * 取りまとめ注文に対する考慮は、呼び出し元で行う
     * @param int $oseq
     * @param int $opId
     * @param int $userId
     */
    private function approval($oseq, $opId, $userId) {

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
    private function requestCancelToSmbc($oseq)
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