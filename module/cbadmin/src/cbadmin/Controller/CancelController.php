<?php
namespace cbadmin\Controller;

use cbadmin\Application;
use Coral\Coral\Controller\CoralControllerAction;
use Coral\Coral\Mail\CoralMail;
use Coral\Base\BaseGeneralUtils;
use DateTime;
use models\Table\TableOrder;
use models\Table\TableOrderItems;
use models\Table\TableDeliMethod;
use models\Table\TablePayingAndSales;
use models\Table\TableStampFee;
use models\Table\TableCancel;
use models\Table\TableOemClaimFee;
use models\Table\TableOemSettlementFee;
use models\Table\TableEnterpriseCustomer;
use models\Table\TableUser;
use models\Table\ATableOrder;
use models\Table\TableEnterprise;
use models\Table\TableSBPaymentSendResultHistory;
use models\View\ViewDelivery;
use models\View\ViewWaitForCancelConfirm;
use Zend\Db\ResultSet\ResultSet;
use Coral\Coral\Mail\CoralMailException;
use Coral\Coral\History\CoralHistoryOrder;
use models\Table\TableClaimControl;
use models\Logic\LogicCancel;
use models\Logic\LogicSmbcRelation;
use models\Logic\Jnb\LogicJnbAccount;
use models\Logic\Smbcpa\LogicSmbcpaAccount;
use Zend\Json\Json;
use Zend\Http\Client;
use Zend\Http\Response;
use Zend\Http\Client\Adapter;
use Coral\Base\IO\BaseIOUtility;
use models\Table\TableSite;

class CancelController extends CoralControllerAction
{
    protected $_componentRoot = './application/views/components';

    /**
     * アプリケーションオブジェクト
     * @var Application
     */
    private $app;

    /**
     * Controllerを初期化する
     */
    public function _init()
    {
        $this->app = Application::getInstance();
        $this->view->assign('userInfo', $this->app->authManagerAdmin->getUserInfo());

        $this->addStyleSheet('../css/default02.css')
        ->addJavaScript('../js/prototype.js');

        $this->setPageTitle("後払い.com - キャンセル確認");
    }

    /**
     * キャンセル確認待ちのリストを表示する。
     */
    public function listAction()
    {
        $mdlwfcc = new ViewWaitForCancelConfirm($this->app->dbAdapter);
        $mdldeli = new ViewDelivery($this->app->dbAdapter);
        $mdlo = new TableOrder($this->app->dbAdapter);
        $mdlec = new TableEnterpriseCustomer($this->app->dbAdapter);
        $mdlSBPsrh = new TableSBPaymentSendResultHistory( $this->app->dbAdapter );

        $caption = array(
            0 => '【区分１：通常】',
            1 => '【区分２：立替済】 ※代表注文のみ表示されます',
            2 => '【区分３：立替・入金済】 ※代表注文のみ表示されます',
            3 => '【区分４：未立替・入金済】 ※代表注文のみ表示されます'
        );

        for ($phase = 0 ; $phase < 4 ; $phase++)
        {
            $datas = $mdlwfcc->getPhaseByQuery($phase + 1);
            $rs = new ResultSet();
            $datasary = $rs->initialize($datas)->toArray();

            // count関数対策
            $datasary_count = 0;
            if (!empty($datasary)) {
                $datasary_count = count($datasary);
            }

            for ($i = 0 ; $i < $datasary_count ; $i++)
            {
                // 配送関連情報の取得
                $sql  = ' SELECT MAX( Deli_ConfirmArrivalDate ) AS Deli_ConfirmArrivalDate ';
                $sql .= ' ,      MIN( Deli_JournalIncDate ) AS Deli_JournalIncDate ';
                $sql .= ' FROM T_OrderItems WHERE OrderSeq = :OrderSeq AND ValidFlg = 1 ';
                $deli = $this->app->dbAdapter->query( $sql )->execute( array( ':OrderSeq' => $datasary[$i]['OrderSeq'] ) )->current();

                $datasary[$i]['Deli_ConfirmArrivalDate'] = BaseGeneralUtils::toDateStringMMDD($deli['Deli_ConfirmArrivalDate']);
                $datasary[$i]['Deli_JournalIncDate'] = BaseGeneralUtils::toDateStringMMDD($deli['Deli_JournalIncDate']);

                // 注文のキャンセル区分を取得
                $order = $mdlo->find($datasary[$i]['OrderSeq'])->current();
                if(!empty($order['Cnl_ReturnSaikenCancelFlg'])) {
                    $datasary[$i]['Cnl_ReturnSaikenCancelFlg'] = '返却';
                } else {
                    $datasary[$i]['Cnl_ReturnSaikenCancelFlg'] = '通常';
                }

                // 日付関連
                $datasary[$i]['F_ClaimDate'] = BaseGeneralUtils::toDateStringMMDD($datasary[$i]['F_ClaimDate']);
                $datasary[$i]['Chg_ExecDate'] = BaseGeneralUtils::toDateStringMMDD($datasary[$i]['Chg_ExecDate']);
                $datasary[$i]['CancelDate'] = BaseGeneralUtils::toDateStringMMDD($datasary[$i]['CancelDate']);
                $sql = ' SELECT MAX( ReceiptDate ) AS ReceiptDate FROM T_ReceiptControl WHERE OrderSeq = :OrderSeq ';
                $datasary[$i]['ReceiptDate'] = $this->app->dbAdapter->query( $sql )->execute( array( ':OrderSeq' => $datasary[$i]['P_OrderSeq'] ) )->current()['ReceiptDate'];
                $datasary[$i]['ReceiptDate'] = BaseGeneralUtils::toDateStringMMDD( $datasary[$i]['ReceiptDate'] );

                // 加盟店顧客SEQ
                $sql  = ' SELECT EntCustSeq ';
                $sql .= ' FROM T_Customer ';
                $sql .= ' WHERE CustomerId = :CustomerId ';
                $prm = array(
                    ':CustomerId' => $datasary[$i]['CustomerId'],
                );
                $stm = $this->app->dbAdapter->query( $sql );
                $ecseq = $stm->execute( $prm )->current()['EntCustSeq'];
                $datasary[$i]['EntCustSeq'] = $ecseq;

                //キャンセル理由
                $datasary[$i]['CancelReason'] = $datasary[$i]['SelectCancelReason'];
                if(!empty($datasary[$i]['InputCancelReason'])) {
                    $datasary[$i]['CancelReason'] .= '('.$datasary[$i]['InputCancelReason'].')';
                }
            }

            $allPhases[$phase] = $datasary;
        }

        // システム日付を取得する
        $dateTime = new DateTime();
        $sysDate = $dateTime->format('Y-m-d');
        $cancelErrListPrm = "?RegistDateF=". urldecode( $sysDate ). "&RegistDateT=". urldecode( $sysDate );
        $sbpsErrCnt = $mdlSBPsrh->getNgRegistDate($sysDate)->count();

        $this->view->assign('list', $allPhases);
        $this->view->assign('caption', $caption);
        $this->view->assign('cancelErrListPrm', $cancelErrListPrm);
        $this->view->assign('sbpsErrCnt', $sbpsErrCnt);

        return $this->view;
    }

    /**
     * キャンセル確認実行アクション
     */
    public function doneAction()
    {
        $mdlo = new TableOrder($this->app->dbAdapter);
        $mdlc = new TableCancel($this->app->dbAdapter);
        $mdlps = new TablePayingAndSales($this->app->dbAdapter);
        $mdlsf = new TableStampFee($this->app->dbAdapter);
        $mdlosf = new TableOemSettlementFee($this->app->dbAdapter);
        $mdlocf = new TableOemClaimFee($this->app->dbAdapter);
        $mdlcc = new TableClaimControl($this->app->dbAdapter);
        $mail = new CoralMail($this->app->dbAdapter, $this->app->mail['smtp']);
        $mdlSBPsrh = new TableSBPaymentSendResultHistory( $this->app->dbAdapter );

        $logicCancel = new LogicCancel($this->app->dbAdapter);
        $history = new CoralHistoryOrder($this->app->dbAdapter);

        $params = $this->params()->fromPost();
        $i = 0;

        // ユーザーIDの取得
        $obj = new TableUser($this->app->dbAdapter);
        $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);
        $opId = $this->app->authManagerAdmin->getUserInfo()->OpId;

        $errorListSBPayment = array();

        while(isset($params['OrderSeq' . $i]))
        {
            if (array_key_exists("ApprovalFlg" . $i, $params) && $params['ApprovalFlg' . $i] == "on")  {

                $oseq = $params['OrderSeq' . $i];
                $phase = $params['phase' . $i];
                $p_oseq = $params['P_OrderSeq' . $i];
                $hisOrderseq = array(); // 注文履歴登録用の注文SEQ保持配列

                try
                {
                    // トランザクション開始
                    $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();

                    // 申請中でない場合（キャンセル取消）はスキップ
                    $sql = ' SELECT COUNT(*) CNT FROM T_Order o WHERE o.OrderSeq = :OrderSeq AND Cnl_Status = 1 ';
                    $prm = array(
                        ':OrderSeq' => $oseq,
                    );
                    $cnt = $this->app->dbAdapter->query($sql)->execute($prm)->current()['CNT'];
                    if ( $cnt == 0 ) {
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

//                             $row = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $p_oseq))->current();
//                             if ($row) {
//                                 // 利用額を取得
//                                 $sql = ' SELECT UseAmount FROM T_Order WHERE OrderSeq = :OrderSeq ';
//                                 $useAmount = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current()['UseAmount'];

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
                            $ri = $this->app->dbAdapter->query($sql)->execute(array(':P_OrderSeq' => $p_oseq));
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
                    $orderCnt = $this->app->dbAdapter->query($sql)->execute(array(':P_OrderSeq' => $p_oseq))->current()['CNT'];

                    // SMBC決済ステーションへ取り消し依頼をする（20150813_1704_suzuki_h）
                    if(in_array($phase, array(0, 1))) {
                        // すべての注文がキャンセルされている場合
                        if ( $orderCnt == 0 ) {
                            // 入金前キャンセルかつ、全キャンセル状態ならSMBC決済ステーションに取消依頼を送信
                            $this->requestCancelToSmbc($p_oseq);

                            // 請求中のJNB口座もクローズする
                            $reason = $mdlc->findCancel(array('OrderSeq' => $oseq))->current()['CancelReason'];
                            $jnbAccLogic = new LogicJnbAccount($this->app->dbAdapter);
                            $jnbAccLogic->closeAccount($p_oseq, LogicJnbAccount::CLOSE_BY_CANCEL, sprintf("キャンセル理由 '%s' によってキャンセル", $reason));

                            // 請求中のSMBCバーチャル口座もクローズする
                            $reason = $mdlc->findCancel(array('OrderSeq' => $oseq))->current()['CancelReason'];
                            $smbcpaAccLogic = new LogicSmbcpaAccount($this->app->dbAdapter);
                            $smbcpaAccLogic->closeAccount($p_oseq, LogicSmbcpaAccount::CLOSE_BY_CANCEL, sprintf("キャンセル理由 '%s' によってキャンセル", $reason));
                        }
                    }

                    // 注文_会計の取得
                    $mdlao = new ATableOrder( $this->app->dbAdapter );
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
                        $mdlsit = new TableSite($this->app->dbAdapter);
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
                    $claimCnt = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $p_oseq))->current()['CNT'];
                    if (in_array($phase, array(0, 1))) {
                        if ($claimCnt > 0 && $orderCnt == 0) {
                            try {
                                // 購入者へ送るメールなので、請求単位で送信する
                                $mail->SendDisposeBillMail($p_oseq, $userId);
                            }
                            catch(\Exception $e) { ; }
                        }
                    }

                    // 口座振替アラートを論理削除する
                    // $sql = ' UPDATE T_CreditTransferAlert SET UpdateDate=:UpdateDate, UpdateId=:UpdateId, ValidFlg=0 WHERE OrderSeq = :OrderSeq ';
                    // $data = array(
                    //     ':UpdateDate' => date('Y-m-d H:i:s'),
                    //     ':UpdateId' => $userId,
                    //     ':OrderSeq' => $oseq
                    // );
                    // $this->app->dbAdapter->query($sql)->execute($data);

                    // トランザクション終了 commit
                    $this->app->dbAdapter->getDriver()->getConnection()->commit();
                }
                catch(\Exception $e)
                {
                    // トランザクション終了 rollback
                    $this->app->dbAdapter->getDriver()->getConnection()->rollBack();
                    throw $e;
                }
            }

            $i++;
        }

        return $this->_redirect('cancel/list');
    }


    /**
     * 一注文に対するキャンセル承認処理を行う。
     * 取りまとめ注文に対する考慮は、呼び出し元で行う
     * @param int $oseq
     * @param int $opId
     * @param int $userId
     */
    protected function approval($oseq, $opId, $userId) {

        $mdlo = new TableOrder($this->app->dbAdapter);
        $mdlc = new TableCancel($this->app->dbAdapter);
        $mdlps = new TablePayingAndSales($this->app->dbAdapter);
        $mdlsf = new TableStampFee($this->app->dbAdapter);
        $mdlosf = new TableOemSettlementFee($this->app->dbAdapter);
        $mdlocf = new TableOemClaimFee($this->app->dbAdapter);

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
            $logger = $this->app->logger;
            if($logger) {
                $logger->debug(sprintf('[Logic_Cancel::requestCancelToSmbc] oseq = %s', $oseq));
            }
        } catch(\Exception $err) {}
        $cnlLogic = LogicSmbcRelation::openCancelService($this->app->dbAdapter, $logger);
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

    /**
     * 注文情報取得
     *
     * @param int $oseq 注文SEQ
     * @return array
     */
    protected function _getOrderInfo($oseq) {
        $sql = "
SELECT o.OrderId
,      e.BasicId
,      e.BasicPw
FROM   T_Order o
       INNER JOIN T_Enterprise e ON (e.EnterpriseId = o.EnterpriseId)
WHERE  o.OrderSeq = :OrderSeq
        ";

        return $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current();
    }

    /**
     * (SB Payment Service)クレジットカード決済：取消返金要求
     *
     * @param array $params パラメタ
     * @param string $err_code エラーコード(8桁)
     * @param array $errorMessages エラーメッセージ文字列の配列(※通信障害系の内容)
     * @return boolean true;成功／false:失敗
     */
    protected function _SBPaymentCancelRequest($params, &$resSBP, &$err_code, &$errorMessages) {

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
     * (SB Payment Service)リクエスト送信
     *
     * @param string $params オンライン決済ASPに渡すパラメータ
     * @param string $responseBody レスポンスデータ
     * @param array $errorMessage エラーメッセージ文字列の配列
     * @return boolean true:成功／false:失敗
     */
    protected function _SBPaymentSendRequest($orderSeq, $basicId, $basicPw, $params, &$responseBody, &$errorMessages) {

        // オンライン決済URL取得
        $url = $this->app->dbAdapter->query(" SELECT PropValue FROM T_SystemProperty WHERE Module = '[DEFAULT]' AND Category = 'sbpayment' AND Name = 'url' ")->execute(null)->current()['PropValue'];
        $timeout = $this->app->dbAdapter->query(" SELECT PropValue FROM T_SystemProperty WHERE Module = '[DEFAULT]' AND Category = 'sbpayment' AND Name = 'timeout' ")->execute(null)->current()['PropValue'];

        // (オンライン決済URL書式化 : T_Enterpriseに設定される[Basic認証ID][Basic認証PW]でﾌﾟﾚｰｽﾌｫﾙﾀﾞを置き換える)
        $url = sprintf($url, $basicId, $basicPw );

        $option = array(
                'adapter'=> 'Zend\Http\Client\Adapter\Curl', // SSL通信用に差し替え
                'ssltransport' => 'tls',
                'maxredirects' => 1,                         // 試行回数(maxredirects) を 1 に設定
        );

        $client = new Client($url, $option);
        $client->setOptions(array('timeout' => (int)$timeout, 'keepalive' => true, 'maxredirects' => 1));

        try {

            // データ送信を実行する
            $response = $client
            ->setRawBody($params)
            ->setEncType('application/xml; charset=UTF-8', ';')
            ->setMethod('Post')
            ->send();

            // 結果を取得する
            $status = $response->getStatusCode();
            $res_msg = $response->getReasonPhrase();
            $res_msg = mb_convert_encoding($res_msg, mb_internal_encoding(), BaseIOUtility::detectEncoding($res_msg));

            if ($status == 200) {
                $responseBody =  $response->getBody();
                return true;
            }

            $errorMessages = 'オンライン決済通信エラー（';
            $errorMessages .= 'ステイタス : ' . $status;
            $errorMessages .= '、メッセージ : ' . $res_msg;
            $errorMessages .= '）';
            return false;
        }
        catch (\Exception $err) {
            $errorMessages = 'オンライン決済通信エラー（データ送信に失敗しました）';
            $this->app->logger->info($err->getMessage());
            return false;
        }
    }



}
