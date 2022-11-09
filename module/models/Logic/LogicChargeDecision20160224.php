<?php
namespace models\Logic;

use Zend\Db\Adapter\Adapter;
use Zend\Stdlib\ArrayObject;
use Zend\Text\Table\Table;
use Coral\Base\BaseGeneralUtils;
use Coral\Coral\History\CoralHistoryOrder;
use models\Logic\LogicFfTcFee;
use models\Table\TableAdjustmentAmount;
use models\Table\TableBusinessCalendar;
use models\Table\TableCancel;
use models\Table\TableCode;
use models\Table\TableEnterprise;
use models\Table\TableOrder;
use models\Table\TablePayingAndSales;
use models\Table\TablePayingControl;
use models\Table\TablePayingBackControl;
use models\Table\TableStampFee;
use models\Table\TableUser;
use models\View\ViewChargeCancel;
use models\View\ViewChargeConfirm;
use models\View\ViewChargeOrder;
use models\View\ViewChargeStampFee;
use oemmember\Controller\AccountController;
use models\Table\TablePayingCycle;
use models\Table\TableEnterpriseClaimHistory;
use models\Table\TableEnterpriseTotal;
use models\Table\ATablePayingControl;
use models\Table\ATableEnterprise;
use models\Table\TableSystemProperty;
use models\Table\ATableAdjustmentAmount;
use Coral\Base\BaseLog;

/**
 * 立替確定クラス
 */
class LogicChargeDecision20160224
{
    /**
     * アダプタ
     *
     * @var Adapter
     */
    protected $_adapter = null;

    /**
     * 調整額管理の配列
     *
     * @var ArrayObject
     */
    private $_arrAdjustAmount = null;

    /**
     * コンストラクタ
     *
     * @param Adapter $adapter アダプタ
     */
    public function __construct(Adapter $adapter)
    {
        $this->_adapter = $adapter;
    }

    private $_logger = null;
    public function setLogger(BaseLog $logger) {
        $this->_logger = $logger;
    }

    /**
     * 立替確定処理
     *
     * @param int $oemId OEMID
     * @param array $transCsvList 振込データCSVファイルパスの配列
     * @param array $claimPdfList 都度請求PDFファイルパスのリスト
     * @param int $userId ユーザID
     */
    public function decision($oemId, $transCsvList, $claimPdfList, $userId)
    {
        $mdlvc = new ViewChargeConfirm($this->_adapter);
        $mdlpc = new TablePayingControl($this->_adapter);
        $mdlpas = new TablePayingAndSales($this->_adapter);
        $mdlc = new TableCancel($this->_adapter);
        $mdlsf = new TableStampFee($this->_adapter);
        $mdlpbc = new TablePayingBackControl($this->_adapter);
        $mdle = new TableEnterprise($this->_adapter);
        $mdlo = new TableOrder($this->_adapter);
        $mdlech = new TableEnterpriseClaimHistory($this->_adapter);

        if(intval($oemId) != -1){
            $oem = $oemId;
        }
        $numSimePtn = 0;// 有効締め日パターン数
        $datas = $mdlvc->getConfirmList(0, "", "", false, $numSimePtn, $oem, -1, true, 0);

        // 取得データから更新対象の立替振込管理Seqを取得する。
        $payingSeqList = array();
        $list = array();
        foreach ($datas as $value) {
            // 取得データの文字列を分割して配列に格納
            $list = explode(',', $value["SeqList"]);
            // 格納した配列をマージする
            $payingSeqList = array_merge_recursive($payingSeqList, $list);
        }

        // 更新処理
        // 更新対象の立替管理Seq分、処理する。
        foreach ($payingSeqList as $payingSeq) {
            // 立替振込管理から該当データを取得する
            $data = $this->_adapter->query( "SELECT * FROM T_PayingControl WHERE Seq = :Seq" )->execute(array( ':Seq' => $payingSeq ))->current();
            $enterpriseId = $data['EnterpriseId'];
            $chargeMonthlyFeeFlg = $data['ChargeMonthlyFeeFlg'];

            // -------------------------
            // 立替振込管理の更新
            // -------------------------
            // (CSVファイル)
            $obj_csv = null;
            $filename = isset($transCsvList[$payingSeq]) ? $transCsvList[$payingSeq] : null;
            if (!is_null($filename)) {
                $fp = fopen($filename, "rb");
                $obj_csv = fread($fp, filesize($filename));
                if (!$obj_csv) {
                    throw new \Exception('振込ファイルの作成に失敗しました。');
                }
                fclose($fp);
                unlink($filename);
            }

            // (PDFファイル)
            $obj_pdf = null;
            $filename = isset($claimPdfList[$payingSeq]) ? $claimPdfList[$payingSeq] : null;
            if (!is_null($filename)) {
                $fp = fopen($filename, "rb");
                $obj_pdf = fread($fp, filesize($filename));
                if (!$obj_pdf) {
                    throw new \Exception('都度請求ファイルの作成に失敗しました。');
                }
                fclose($fp);
                unlink($filename);
            }

            // 振込確定金額 < 0 OR (振込確定金額 = 0 && 振込手数料 > 0) のとき PayBackTC に振込手数料を設定
            if ($data['DecisionPayment'] < 0 || ($data['DecisionPayment'] == 0 && $data['TransferCommission'] > 0)) {
                $payBackTc = $data['TransferCommission'];
            } else {
                $payBackTc = $data['PayBackTC'];
            }

            $pcdata = array(
                    'DecisionDate' => date('Y-m-d'),                    // 立替確定日
                    'PayingControlStatus' => 1,                         // 本締め／仮締め区分
                    'ExecFlg' => $data['DecisionPayment'] == 0 ? -1 : $data['ExecFlg'] ,    // 立替実行済みフラグ
                    'PayBackTC' => $payBackTc,                          // 振込確定金額が振込手数料でマイナスになった場合に振込手数料を格納
                    'PayingDataDownloadFlg' => 0,                       // 振込データDLフラグ
                    'PayingDataFilePath' => $obj_csv,                   // (ﾊﾞｲﾅﾘﾃﾞｰﾀ)振込データCSV
                    'ClaimPdfFilePath' => $obj_pdf,                     // (ﾊﾞｲﾅﾘﾃﾞｰﾀ)都度請求PDF
                    'UpdateId' => $userId,                              // 更新者
            );

            // 更新
            $mdlpc->saveUpdate($pcdata, $payingSeq);

            // 都度請求が発生した場合は、請求データを作成する
            // 事業者情報取得
            $edata = $mdle->find($enterpriseId)->current();
            // 都度請求が発生しているかどうかはT_Enterprise.ClaimClass と T_PayingControll.DecisionPaymentで判断する。
            if ($edata['ClaimClass'] == 0 && $data['DecisionPayment'] < 0) {
                // 都度請求データの作成
                $echdata = array(
                        'EnterpriseId' => $enterpriseId,
                        'PayingControlSeq' => $payingSeq,
                        'ClaimDate' => date('Y-m-d'),
                        'ClaimAmount' => $data['DecisionPayment'] * -1 ,
                        'PaymentAllocatedAmount' => 0,
                        'PaymentAllocatedFlg' => 0,
                        'RegistId' => $userId,
                        'UpdateId' => $userId,
                        'ValidFlg' => 1,
                );
                $mdlech->saveNew($echdata);

                // 加盟店集計テーブルの更新
$sql = <<<EOQ
UPDATE T_EnterpriseTotal
SET    ClaimAmountTotal = ClaimAmountTotal + :ClaimAmountTotal
      ,ClaimedBalance   = ClaimedBalance   + :ClaimedBalance
      ,UpdateId         = :UpdateId
      ,UpdateDate       = :UpdateDate
WHERE  EnterpriseId     = :EnterpriseId
EOQ;

                $prm = array(
                    ':ClaimAmountTotal' => $data['DecisionPayment'] * -1,
                    ':ClaimedBalance' => $data['DecisionPayment'] * -1,
                    ':EnterpriseId' => $enterpriseId,
                    ':UpdateId' => $userId,
                    ':UpdateDate' => date('Y-m-d H:i:s'),
                );

                $this->_adapter->query($sql)->execute($prm);

            }

            // 取得した加盟店IDが同じ立替振込管理データの立替実行フラグが 10 のデータを 11 UPDATE する
            //  → 繰越すべき立替振込管理データをすべて繰越済みにする。
            $mdlpc->carryOverSummingUp($enterpriseId, $userId);

            // -------------------------
            // 立替・売上管理の更新
            // -------------------------
            $pasdata = array(
                    'ChargeDecisionFlg' => 1,                           // 立替確定フラグ
                    'ChargeDecisionDate' => date('Y-m-d'),              // 立替確定日
                    'PayingControlStatus' => 1,                         // 本締め／仮締め区分
                    'UpdateId' => $userId,                              // 更新者
            );

            // 立替振込管理Seqで更新
            $conditionArray = array('PayingControlSeq' => $payingSeq);
            $mdlpas->saveUpdateWhere($pasdata, $conditionArray);

            // -------------------------
            // キャンセル管理の更新
            // -------------------------
            $cdata = array(
                    'KeepAnAccurateFlg' => 1,                           // 精算フラグ
                    'KeepAnAccurateDate' => date('Y-m-d'),              // 精算日
                    'PayingControlStatus' => 1,                         // 本締め／仮締め区分
                    'UpdateId' => $userId,                              // 更新者
            );

            // 立替振込管理Seqで更新
            $conditionArray = array('PayingControlSeq' => $payingSeq);
            $mdlc->saveUpdateWhere($cdata, $conditionArray);

            // -------------------------
            // 印紙代管理の更新
            // -------------------------
            $sfdata = array(
                    'ClearFlg' => 1,                                    // 印紙代精算フラグ
                    'ClearDate' => date('Y-m-d'),                       // 印紙代精算日
                    'PayingControlStatus' => 1,                         // 本締め／仮締め区分
                    'UpdateId' => $userId,                              // 更新者
            );

            // 立替振込管理Seqで更新
            $conditionArray = array('PayingControlSeq' => $payingSeq);
            $mdlsf->saveUpdateWhere($sfdata, $conditionArray);

            // -------------------------
            // 注文データの更新
            // -------------------------
            // 注文データ更新用に注文Seqを取得する。
            $odatas = $this->_adapter->query( "SELECT * FROM T_PayingBackControl WHERE PayingControlSeq = :PayingControlSeq" )->execute(array( ':PayingControlSeq' => $payingSeq ));
            // 取得した件数分、処理する
            foreach ($odatas as $odata) {
                // 更新処理
                $udata = array(
                    'DataStatus' => 91,                                 // データステータス
                    'CloseReason' => 6,                                 // 立替精算戻しクローズ
                    'UpdateId' => $userId,                              // 更新者
                );
                $mdlo->saveUpdate($udata, $odata['OrderSeq']);
            }

            // -------------------------
            // 立替精算戻し管理の更新
            // -------------------------
            $pbcdata = array(
                    'PayDecisionFlg' => 1,                             // 立替確定フラグ
                    'PayDecisionDate' => date('Y-m-d'),                // 立替確定日
                    'PayingControlStatus' => 1,                        // 本締め／仮締め区分
                    'UpdateId' => $userId,                             // 更新者
            );

            // 立替振込管理Seqで更新
            $conditionArray = array('PayingControlSeq' => $payingSeq);
            $mdlpbc->saveUpdateWhere($pbcdata, $conditionArray);

            // 立替サイクルの変更予約があれば、立替関連日付更新前に変更を完了する。
            $mdle->changePayingCycle($enterpriseId);

            // 次回立替実行日等の加盟店の情報を更新する
            $mdle->ChargeFixed($enterpriseId, $chargeMonthlyFeeFlg, $userId);

            // 注文履歴登録用に注文Seqを取得する。
            // 立替振込管理Seqで検索
            $conditionArray = array('PayingControlSeq' => $payingSeq);
            $histDatas = $mdlpas->findPayingAndSales($conditionArray);
            foreach ($histDatas as $histData) {
                // 注文履歴へ登録
                $history = new CoralHistoryOrder($this->_adapter);
                $history->InsOrderHistory($histData["OrderSeq"], 82, $userId);
            }

            // 立替精算戻し分 立替振込管理Seqで検索
            $conditionArray = array('PayingControlSeq' => $payingSeq);
            $histDatas = $mdlpbc->findPayingBackControl($conditionArray);
            foreach ($histDatas as $histData) {
                // 注文履歴へ登録
                $history = new CoralHistoryOrder($this->_adapter);
                $history->InsOrderHistory($histData["OrderSeq"], 85, $userId);
            }
        }

        return true;
    }

    /**
     * 立替精算仮締め
     *
     * @return bool
     */
	public function payTempFixed(){

	    $mdlpc = new TablePayingControl($this->_adapter);                   // 立替振込管理
	    $mdle = new TableEnterprise($this->_adapter);                       // 加盟店
	    $mdlo = new TableOrder($this->_adapter);                            // 注文
	    $mdlu = new TableUser($this->_adapter);                             // ユーザー
	    $mdlbc = new TableBusinessCalendar($this->_adapter);
        $mdlcycle = new TablePayingCycle($this->_adapter);
        // 2015/10/09 Y.Suzuki Add 会計対応 Stt
        $mdlatpc = new ATablePayingControl($this->_adapter);
        $mdlate = new ATableEnterprise($this->_adapter);
        $mdlsys = new TableSystemProperty($this->_adapter);
        // 2015/10/09 Y.Suzuki Add 会計対応 End

        $this->_arrAdjustAmount = array();

        try {

            /////////////////// 初期処理 ///////////////////////////

            $opId = $mdlu->getUserId(99, 1);                                // ユーザーID

//             // 指定する仮締めより、立替振込情報を取得
//             $pcDatas = $mdlpc->findPayingControl(
//                 array(
//                         'PayingControlStatus' => '0'                        // 本締め／仮締め区分(0:仮締め)
//                 )
//             );

            $sql = " SELECT * FROM T_PayingControl WHERE EnterpriseId IN ( 94,2146,2853,3853,3931,4000,4219,4258,5143,5226,5448,5646,5798,9421 ) AND PayingControlStatus = 0 ";
            $pcDatas = $this->_adapter->query($sql)->execute();

            //$fixedDateList = array();                                       // 初期化前の仮立替振込データの加盟店IDと立替締め日保存用

            // トランザクション開始
            $this->_adapter->getDriver()->getConnection()->beginTransaction();

            // 仮締めを計算し直す立替振込管理より各ﾃｰﾌﾞﾙの立替振込管理Seqなどをｸﾘｱし初期化する
            foreach ($pcDatas as $pcData){

                // 立替振込管理の管理Seq
                $pc_seq = $pcData['Seq'];

                // 立替・売上管理で立替未確定にする
                $this->setChargeDecisionInit($pc_seq, $opId);

                // 注文で未立替にする
                $this->setChgStatusInit($pc_seq, $opId);

                // キャンセル管理で未精算みにする
                $this->setKeepAnAccurateInit($pc_seq, $opId);

                // 印紙代管理で未精算にする
                $this->setClearInit($pc_seq, $opId);

                // 立替精算戻り管理で未精算にする
                $this->setPayDecisionInit($pc_seq, $opId);

                // 調整額管理情報を保存・削除する
                $this->storageAndDelAdjustmentAmount(
                        $pc_seq, $pcData['EnterpriseId'], $pcData['FixedDate'], $pcData['DecisionDate']);

                //$fixedDateList[$pcData['EnterpriseId']] = $pcData['FixedDate'];

            }

            // AT_PayingControl削除
//             $this->_adapter->query(" DELETE FROM AT_PayingControl WHERE Seq IN (SELECT Seq FROM T_PayingControl WHERE PayingControlStatus = 0) ")->execute(null);
            $this->_adapter->query(" DELETE FROM AT_PayingControl WHERE Seq IN (SELECT Seq FROM T_PayingControl WHERE PayingControlStatus = 0 AND EnterpriseId IN ( 94,2146,2853,3853,3931,4000,4219,4258,5143,5226,5448,5646,5798,9421 ) ) ")->execute(null);

//             // 立替振込管理の初期化(0：仮締めのデータを削除)
//             $mdlpc->deletePayingControl(
//                 array(
//                         'PayingControlStatus' => '0'
//                 )
//             );
            $this->_adapter->query(" DELETE FROM T_PayingControl WHERE PayingControlStatus = 0 AND EnterpriseId IN ( 94,2146,2853,3853,3931,4000,4219,4258,5143,5226,5448,5646,5798,9421 ) ")->execute(null);


            // コミット
            $this->_adapter->getDriver()->getConnection()->commit();

            /////////////////// 仮締め処理 ///////////////////////////

            // 加盟店データ取得
            // N_ChargeFixedDateは、サービスインすると設定される
//             $sql = " SELECT * FROM T_Enterprise WHERE N_ChargeFixedDate IS NOT NULL ORDER BY EnterpriseId ";
            $sql = " SELECT * FROM T_Enterprise WHERE N_ChargeFixedDate IS NOT NULL AND EnterpriseId IN ( 94,2146,2853,3853,3931,4000,4219,4258,5143,5226,5448,5646,5798,9421 ) ORDER BY EnterpriseId ";

            $eDatas = $this->_adapter->query($sql)->execute(null);

            foreach ($eDatas as $eData){

                // トランザクション開始
                $this->_adapter->getDriver()->getConnection()->beginTransaction();

                $enterpriseId = $eData['EnterpriseId'];                          // 加盟店ID
                $nChargeFixedDate = $eData['N_ChargeFixedDate'];                 // 次回立替締め日
                //$fixedDate = $fixedDateList[$enterpriseId];                      // 立替締め日
                $addUpFixedMonth =date('Y-m-01', strtotime($nChargeFixedDate));  // 月次計上月度
                $execScheduleDate = $mdlcycle->getNextTransferDate($enterpriseId, $nChargeFixedDate);
                $serviceInDate = $eData['ServiceInDate'];                        // サービス開始日
                // 2015/10/19 Y.Suzuki Mod 会計対応 Stt
                $closingMonthly = date('Y-m-01' , strtotime($addUpFixedMonth . " -1 month"));
                // 2015/10/19 Y.Suzuki Mod 会計対応 End

                // 締め日が月末日の場合、締め日の当月を取得
                if ($nChargeFixedDate == date('Y-m-t', strtotime($addUpFixedMonth))) {
                    $closingMonthly = date('Y-m-01' , strtotime($addUpFixedMonth));
                }

                // 前回繰越データの取得
                $befCarryOverDate = $mdlpc->findPayingControl(
                    array(
                            'ExecFlg' => 10 ,                                    // 10:振込確定金額マイナス（未繰越）
                            'EnterpriseId' => $enterpriseId ,
                    )
                )->current();

                // 注文データの取得
                $orderData = $this->getOrderData($enterpriseId, $nChargeFixedDate)->current();

                // キャンセルデータの取得
                $cancelData = $this->getCancelData($enterpriseId, $nChargeFixedDate)->current();

                // 印紙代データの取得
                $stampFeeData =$this->getStampFeeData($enterpriseId, $nChargeFixedDate)->current();

                // 立替精算戻りデータの取得
                $payBackControlData = $this->getPayBackControlData($enterpriseId, $nChargeFixedDate)->current();

                // 今月分の月額固定費が計上されているか
                $cnt = $this->getCountMonthlyFee($enterpriseId, $addUpFixedMonth);

                // 月額固定費配列の初期化
                $monthlyFee = 0;
                $monthlyClosingInfoData = array();
                $monthlyClosingInfoData['MonthlyFee']                   = 0;  // 月額固定費(税抜)
                $monthlyClosingInfoData['MonthlyFeeTax']                = 0;  // 月額固定費消費税
                $monthlyClosingInfoData['IncludeMonthlyFee']            = 0;  // 同梱月額固定費(税抜)
                $monthlyClosingInfoData['IncludeMonthlyFeeTax']         = 0;  // 同梱月額固定費消費税
                $monthlyClosingInfoData['ApiMonthlyFee']                = 0;  // API月額固定費(税抜)
                $monthlyClosingInfoData['ApiMonthlyFeeTax']             = 0;  // API月額固定費消費税
                $monthlyClosingInfoData['CreditNoticeMonthlyFee']       = 0;  // 与信結果通知サービス月額固定費(税抜)
                $monthlyClosingInfoData['CreditNoticeMonthlyFeeTax']    = 0;  // 与信結果通知サービス月額固定費消費税
                $monthlyClosingInfoData['NCreditNoticeMonthlyFee']      = 0;  // 次回与信結果通知サービス月額固定費(税抜)
                $monthlyClosingInfoData['NCreditNoticeMonthlyFeeTax']   = 0;  // 次回与信結果通知サービス月額固定費消費税
                $monthlyClosingInfoData['ReserveMonthlyFee']            = 0;  // 月額固定費予備(税抜)
                $monthlyClosingInfoData['ReserveMonthlyFeeTax']         = 0;  // 月額固定費予備消費税
                $monthlyClosingInfoData['OemMonthlyFee']                = 0;  // OEM月額固定費(税抜)
                $monthlyClosingInfoData['OemMonthlyFeeTax']             = 0;  // OEM月額固定費消費税
                $monthlyClosingInfoData['OemIncludeMonthlyFee']         = 0;  // OEM同梱月額固定費(税抜)
                $monthlyClosingInfoData['OemIncludeMonthlyFeeTax']      = 0;  // OEM同梱月額固定費消費税
                $monthlyClosingInfoData['OemApiMonthlyFee']             = 0;  // OEMAPI月額固定費(税抜)
                $monthlyClosingInfoData['OemApiMonthlyFeeTax']          = 0;  // OEMAPI月額固定費消費税
                $monthlyClosingInfoData['OemCreditNoticeMonthlyFee']    = 0;  // OEM与信結果通知サービス月額固定費(税抜)
                $monthlyClosingInfoData['OemCreditNoticeMonthlyFeeTax'] = 0;  // OEM与信結果通知サービス月額固定費消費税
                $monthlyClosingInfoData['OemNCreditNoticeMonthlyFee']   = 0;  // OEM次回請求与信結果通知サービス月額固定費(税抜)
                $monthlyClosingInfoData['OemNCreditNoticeMonthlyFeeTax'] = 0; // OEM次回請求与信結果通知サービス月額固定費消費税
                $monthlyClosingInfoData['OemReserveMonthlyFee']         = 0;  // OEM月額固定費予備(税抜)
                $monthlyClosingInfoData['OemReserveMonthlyFeeTax']      = 0;  // OEM月額固定費予備消費税

                if ($cnt <= 0) {    // 今月すでに計上されていない場合のみ
                    // 2015/10/19 Y.Suzuki Mod 会計対応 Stt
                    /* ************************************************************************************************
                     * 2015/10/19_memo
                     * 加盟店月締め情報を作成するのは毎月1日（月末日 = 業務日付の夜に前月分の月締め情報を作成する）
                     * この時点でサービス開始日（T_Enterprise.ServiceInDate）がNULLの場合は作成されない。
                     * （↑加盟店月締め情報作成の条件に該当しないため。）
                     * → つまり、該当データは取得できない。
                     * 　→ よって、月額固定費は 0 で立替振込管理、立替振込管理_会計 を作成！！！
                     * ************************************************************************************************ */
                    // 加盟店月締め情報テーブルから計上月時点の月額固定費を取得する。
                    $sql = "SELECT * FROM AT_EnterpriseMonthlyClosingInfo WHERE EnterpriseId = :EnterpriseId AND ClosingMonthly = :ClosingMonthly";
                    $ri = $this->_adapter->query($sql)->execute(array(':EnterpriseId' => $enterpriseId, ':ClosingMonthly' => $closingMonthly));

                    // データが取得できた場合のみセット
                    if ($ri->count() > 0) {
                        // データ行を取得
                        $monthlyClosingInfoData = $ri->current();
                        // サマリーを算出
                        $monthlyFee = $monthlyClosingInfoData['MonthlyFee']
                                    + $monthlyClosingInfoData['MonthlyFeeTax']
                                    + $monthlyClosingInfoData['IncludeMonthlyFee']
                                    + $monthlyClosingInfoData['IncludeMonthlyFeeTax']
                                    + $monthlyClosingInfoData['ApiMonthlyFee']
                                    + $monthlyClosingInfoData['ApiMonthlyFeeTax']
                                    + $monthlyClosingInfoData['CreditNoticeMonthlyFee']
                                    + $monthlyClosingInfoData['CreditNoticeMonthlyFeeTax']
                                    + $monthlyClosingInfoData['NCreditNoticeMonthlyFee']
                                    + $monthlyClosingInfoData['NCreditNoticeMonthlyFeeTax']
                                    + $monthlyClosingInfoData['ReserveMonthlyFee']
                                    + $monthlyClosingInfoData['ReserveMonthlyFeeTax'];
                    }
                    // 2015/10/19 Y.Suzuki Mod 会計対応 End
                    // 締め日の当月 かつ データが取得できない場合、加盟店マスタの次回月額固定費を取得する。
                    else if ($ri->count() == 0 && $closingMonthly == date('Y-m-01' , strtotime($addUpFixedMonth))) {
                        // マスタ設定値は税抜金額なので、消費税額を算出（立替振込管理_会計作成用）
                        // 締め日時点の消費税率を取得
                        $taxRate = ($mdlsys->getTaxRateAt($nChargeFixedDate) / 100);

                        // 加盟店マスタから次回月額固定費を取得
                        $sql = "SELECT IFNULL(N_MonthlyFee, 0) AS N_MonthlyFee, IFNULL(N_OemMonthlyFee, 0) AS N_OemMonthlyFee FROM T_Enterprise WHERE EnterpriseId = :EnterpriseId";
                        $entData = $this->_adapter->query($sql)->execute(array(':EnterpriseId' => $enterpriseId))->current();

                        $cbMonthlyFee = $entData['N_MonthlyFee'];                           // 月額固定費（税抜）
                        $cbMonthlyFeeTax = floor($entData['N_MonthlyFee'] * $taxRate);      // 月額固定費消費税
                        // OEM分
                        $oemMonthlyFee = $entData['N_OemMonthlyFee'];                       // OEM月額固定費（税抜）
                        $oemMonthlyFeeTax = floor($entData['N_OemMonthlyFee'] * $taxRate);  // OEM月額固定費消費税

                        // 加盟店_会計からその他月額固定費の次回請求分を取得する。（小数点以下は切捨て！！！）
                        $sql = "SELECT * FROM AT_Enterprise WHERE EnterpriseId = :EnterpriseId";
                        $atEntData = $this->_adapter->query($sql)->execute(array(':EnterpriseId' => $enterpriseId))->current();

                        $cbIncludeMonthlyFee = $atEntData['N_IncludeMonthlyFee'];                                       // 同梱月額固定費（税抜）
                        $cbIncludeMonthlyFeeTax = floor($atEntData['N_IncludeMonthlyFee'] * $taxRate);                  // 同梱月額固定費消費税
                        $cbApiMonthlyFee = $atEntData['N_ApiMonthlyFee'];                                               // API月額固定費（税抜）
                        $cbApiMonthlyFeeTax = floor($atEntData['N_ApiMonthlyFee'] * $taxRate);                          // API月額固定費消費税
                        $cbCreditNoticeMonthlyFee = $atEntData['N_CreditNoticeMonthlyFee'];                             // 与信結果通知サービス月額固定費（税抜）
                        $cbCreditNoticeMonthlyFeeTax = floor($atEntData['N_CreditNoticeMonthlyFee'] * $taxRate);        // 与信結果通知サービス月額固定費消費税
                        $cbNCreditNoticeMonthlyFee = $atEntData['N_NCreditNoticeMonthlyFee'];                           // 次回請求与信結果通知サービス月額固定費（税抜）
                        $cbNCreditNoticeMonthlyFeeTax = floor($atEntData['N_NCreditNoticeMonthlyFee'] * $taxRate);      // 次回請求与信結果通知サービス月額固定費消費税
                        $cbReserveMonthlyFee = $atEntData['N_ReserveMonthlyFee'];                                       // 月額固定費予備（税抜）
                        $cbReserveMonthlyFeeTax = floor($atEntData['N_ReserveMonthlyFee'] * $taxRate);                  // 月額固定費消費税
                        // OEM分
                        $oemIncludeMonthlyFee = $atEntData['N_OemIncludeMonthlyFee'];                                   // OEM同梱月額固定費（税抜）
                        $oemIncludeMonthlyFeeTax = floor($atEntData['N_OemIncludeMonthlyFee'] * $taxRate);              // OEM同梱月額固定費消費税
                        $oemApiMonthlyFee = $atEntData['N_OemApiMonthlyFee'];                                           // OEMAPI月額固定費（税抜）
                        $oemApiMonthlyFeeTax = floor($atEntData['N_OemApiMonthlyFee'] * $taxRate);                      // OEMAPI月額固定費消費税
                        $oemCreditNoticeMonthlyFee = $atEntData['N_OemCreditNoticeMonthlyFee'];                         // OEM与信結果通知サービス月額固定費（税抜）
                        $oemCreditNoticeMonthlyFeeTax = floor($atEntData['N_OemCreditNoticeMonthlyFee'] * $taxRate);    // OEM与信結果通知サービス月額固定費消費税
                        $oemNCreditNoticeMonthlyFee = $atEntData['N_OemNCreditNoticeMonthlyFee'];                       // OEM次回請求与信結果通知サービス月額固定費（税抜）
                        $oemNCreditNoticeMonthlyFeeTax = floor($atEntData['N_OemNCreditNoticeMonthlyFee'] * $taxRate);  // OEM次回請求与信結果通知サービス月額固定費消費税
                        $oemReserveMonthlyFee = $atEntData['N_OemReserveMonthlyFee'];                                   // OEM月額固定費予備（税抜）
                        $oemReserveMonthlyFeeTax = floor($atEntData['N_OemReserveMonthlyFee'] * $taxRate);              // OEM月額固定費消費税

                        $monthlyClosingInfoData = array(
                            'MonthlyFee' => $cbMonthlyFee,
                            'MonthlyFeeTax' => $cbMonthlyFeeTax,
                            'IncludeMonthlyFee' => $cbIncludeMonthlyFee,
                            'IncludeMonthlyFeeTax' => $cbIncludeMonthlyFeeTax,
                            'ApiMonthlyFee' => $cbApiMonthlyFee,
                            'ApiMonthlyFeeTax' => $cbApiMonthlyFeeTax,
                            'CreditNoticeMonthlyFee' => $cbCreditNoticeMonthlyFee,
                            'CreditNoticeMonthlyFeeTax' => $cbCreditNoticeMonthlyFeeTax,
                            'NCreditNoticeMonthlyFee' => $cbNCreditNoticeMonthlyFee,
                            'NCreditNoticeMonthlyFeeTax' => $cbNCreditNoticeMonthlyFeeTax,
                            'ReserveMonthlyFee' => $cbReserveMonthlyFee,
                            'ReserveMonthlyFeeTax' => $cbReserveMonthlyFeeTax,
                            'OemMonthlyFee' => $oemMonthlyFee,
                            'OemMonthlyFeeTax' => $oemMonthlyFeeTax,
                            'OemIncludeMonthlyFee' => $oemIncludeMonthlyFee,
                            'OemIncludeMonthlyFeeTax' => $oemIncludeMonthlyFeeTax,
                            'OemApiMonthlyFee' => $oemApiMonthlyFee,
                            'OemApiMonthlyFeeTax' => $oemApiMonthlyFeeTax,
                            'OemCreditNoticeMonthlyFee' => $oemCreditNoticeMonthlyFee,
                            'OemCreditNoticeMonthlyFeeTax' => $oemCreditNoticeMonthlyFeeTax,
                            'OemNCreditNoticeMonthlyFee' => $oemNCreditNoticeMonthlyFee,
                            'OemNCreditNoticeMonthlyFeeTax' => $oemNCreditNoticeMonthlyFeeTax,
                            'OemReserveMonthlyFee' => $oemReserveMonthlyFee,
                            'OemReserveMonthlyFeeTax' => $oemReserveMonthlyFeeTax,
                        );

                        // サマリを算出
                        $monthlyFee = $cbMonthlyFee + $cbMonthlyFeeTax
                                    + $cbIncludeMonthlyFee + $cbIncludeMonthlyFeeTax
                                    + $cbApiMonthlyFee + $cbApiMonthlyFeeTax
                                    + $cbCreditNoticeMonthlyFee + $cbCreditNoticeMonthlyFeeTax
                                    + $cbNCreditNoticeMonthlyFee + $cbNCreditNoticeMonthlyFeeTax
                                    + $cbReserveMonthlyFee + $cbReserveMonthlyFeeTax;

                        // 月額固定費の消費税額を算出した際、小数点以下切捨て（FLOOR）により型が INT → DOUBLE に変わってしまったため、$monthlyFeeをINTへｷｬｽﾄする。
                        $monthlyFee = (int)$monthlyFee;
                    }
                }

                // 加盟店.次回－立替締め日 ≦　加盟店.サービス開始日の月の月末日
                // の場合、月額固定費は0
                $lastDay = date('Y-m-d', strtotime("last day of " . $serviceInDate));
                if ($nChargeFixedDate <= $lastDay) {
                    $monthlyFee = 0;
                    $monthlyClosingInfoData = array();
                }

                // 調整額
                $adjustmentAmount = $this->getAdjustmentAmount($enterpriseId);

                // 振込額
                $transferAmount = $orderData['SUM_UseAmount'] + $cancelData['RepayTotal']
                        - $stampFeeData['StampFee'] + $payBackControlData['PayBackAmount']
                        + ( !$befCarryOverDate ? 0 : $befCarryOverDate['DecisionPayment'] + $befCarryOverDate['PayBackTC'])
                        - $monthlyFee
                        + $adjustmentAmount
                ;

                // 振込手数料の計算
                $ffTcFee = new LogicFfTcFee($this->_adapter);
                $calculateTransferFee = $ffTcFee->getTransferCommission($eData['TcClass'], $transferAmount, $eData['OemId']);

                // 振込確定金額
                $decisionPayment = $transferAmount - $calculateTransferFee;

                // payBackTCの設定
                $payBackTC = $decisionPayment < 0 ? $calculateTransferFee : 0;

                // 立替振込管理の出力
                $payingControlSeq = $mdlpc->saveNew(
                    array(
                        'EnterpriseId' => $enterpriseId,                                // 加盟店ID
                        'FixedDate' => $nChargeFixedDate,                               // 立替締め日
                        'DecisionDate' => NULL,                                         // 立替確定日
                        'ExecDate' => NULL,                                             // 立替実行日
                        'ExecFlg' => 0,                                                 // 立替実行フラグ
                        'ExecCpId' => $opId,                                            // 立替実行担当者
                        'ChargeCount' => $orderData['Cnt'],                             // 立替注文件数
                        'ChargeAmount' => $orderData['SUM_UseAmount'],                  // 立替金額
                        'CancelCount' => $cancelData['Cnt'],                            // キャンセル件数
                        'CalcelAmount' => $cancelData['RepayTotal'],                    // キャンセル精算金額
                        'StampFeeCount' => $stampFeeData['Cnt'],                        // 印紙代発生件数
                        'StampFeeTotal' => $stampFeeData['StampFee'],                   // 印紙代精算金額
                        'MonthlyFee' => $monthlyFee,                                    // 月額固定費
                        'DecisionPayment' => $decisionPayment,                          // 振込確定金額
                        'AddUpFlg' => 0,                                                // 月次計上フラグ
                        'AddUpFixedMonth' => $addUpFixedMonth ,                         // 月次計上月度
                        'SettlementFee' => $orderData['SUM_SettlementFee'],             // 決済手数料
                        'ClaimFee' => $orderData['SUM_ClaimFee'],                       // 請求手数料
                        'CarryOver' => ( !$befCarryOverDate ? 0 : $befCarryOverDate['DecisionPayment'] + $befCarryOverDate['PayBackTC'] ),    // 繰越
                        'TransferCommission' => $calculateTransferFee,                  // 振込手数料
                        'ExecScheduleDate' => $execScheduleDate ,                       // 立替実行予定日
                        'AdjustmentAmount' => $adjustmentAmount,                        // 調整額
                        'PayBackTC' => $payBackTC,             // 振込確定金額が振込手数料でマイナスになった場合に
                        'CarryOverTC' => ( !$befCarryOverDate ? 0 : $befCarryOverDate['PayBackTC'] ), // PayBackTCがプラスになった場合に設定する
                        'OemId' => $eData['OemId'],                                     // OEMID
                        'OemClaimedSeq' => NULL,                                        // OEM請求データシーケンス
                        'OemClaimedAddUpFlg' => 0,                                      // OEM請求計上フラグ
                        'ChargeMonthlyFeeFlg' => ($monthlyFee == 0 ? 0 : 1),            // 月額固定費課金
                        'PayBackCount' => $payBackControlData['CNT'],                   // 立替精算戻し件数
                        'PayBackAmount' => $payBackControlData['PayBackAmount'],        // 立替精算戻し金額
                        'PayingControlStatus' => 0,                                     // 本締め／仮締め区分
                        'SpecialPayingFlg' => 0,                                        // 臨時加盟店立替フラグ
                        'PayingDataDownloadFlg' => 0,                                   // 振込データDLフラグ
                        'PayingDataFilePath' => NULL,                                   // 振込データCSVファイルパス
                        'ClaimPdfFilePath' => NULL,                                     // 都度請求PDFファイルパス
                        'AdjustmentDecisionFlg' => 0,                                   // 調整額確定フラグ
                        'AdjustmentDecisionDate' => NULL,                               // 調整額確定日付
                        'AdjustmentCount' => 0,                                         // 調整額件数
                        'RegistId' => $opId,                                            // 登録者
                        'UpdateId' => $opId,                                            // 更新者
                        'ValidFlg' => 1,                                                // 有効フラグ
                    )
                );

                // 2015/10/19 Y.Suzuki Add 会計対応 Stt
                // 加盟店月締め情報のデータが取得できた場合、立替振込管理_会計テーブルにINSERTする
                // 20151124_H.Suzuki 立替振込管理_会計は必ず１：１で作成する
                // 会計用項目のINSERT
                $data = array(
                        'Seq' => $payingControlSeq,                                                                             // 管理Seq
                        'MonthlyFeeWithoutTax'          => nvl($monthlyClosingInfoData['MonthlyFee'], 0),                       // 月額固定費(税抜)
                        'MonthlyFeeTax'                 => nvl($monthlyClosingInfoData['MonthlyFeeTax'] , 0),                   // 月額固定費消費税
                        'IncludeMonthlyFee'             => nvl($monthlyClosingInfoData['IncludeMonthlyFee'] ,0),                // 同梱月額固定費(税抜)
                        'IncludeMonthlyFeeTax'          => nvl($monthlyClosingInfoData['IncludeMonthlyFeeTax'], 0),             // 同梱月額固定費消費税
                        'ApiMonthlyFee'                 => nvl($monthlyClosingInfoData['ApiMonthlyFee'], 0),                    // API月額固定費(税抜)
                        'ApiMonthlyFeeTax'              => nvl($monthlyClosingInfoData['ApiMonthlyFeeTax'], 0),                 // API月額固定費消費税
                        'CreditNoticeMonthlyFee'        => nvl($monthlyClosingInfoData['CreditNoticeMonthlyFee'], 0),           // 与信結果通知サービス月額固定費(税抜)
                        'CreditNoticeMonthlyFeeTax'     => nvl($monthlyClosingInfoData['CreditNoticeMonthlyFeeTax'], 0),        // 与信結果通知サービス月額固定費消費税
                        'NCreditNoticeMonthlyFee'       => nvl($monthlyClosingInfoData['NCreditNoticeMonthlyFee'], 0),          // 次回与信結果通知サービス月額固定費(税抜)
                        'NCreditNoticeMonthlyFeeTax'    => nvl($monthlyClosingInfoData['NCreditNoticeMonthlyFeeTax'], 0),       // 次回与信結果通知サービス月額固定費消費税
                        'ReserveMonthlyFee'             => nvl($monthlyClosingInfoData['ReserveMonthlyFee'], 0),                // 月額固定費予備(税抜)
                        'ReserveMonthlyFeeTax'          => nvl($monthlyClosingInfoData['ReserveMonthlyFeeTax'], 0),             // 月額固定費予備消費税
                        'OemMonthlyFeeWithoutTax'       => nvl($monthlyClosingInfoData['OemMonthlyFee'], 0),                    // OEM月額固定費(税抜)
                        'OemMonthlyFeeTax'              => nvl($monthlyClosingInfoData['OemMonthlyFeeTax'], 0),                 // OEM月額固定費消費税
                        'OemIncludeMonthlyFee'          => nvl($monthlyClosingInfoData['OemIncludeMonthlyFee'], 0),             // OEM同梱月額固定費(税抜)
                        'OemIncludeMonthlyFeeTax'       => nvl($monthlyClosingInfoData['OemIncludeMonthlyFeeTax'], 0),          // OEM同梱月額固定費消費税
                        'OemApiMonthlyFee'              => nvl($monthlyClosingInfoData['OemApiMonthlyFee'], 0),                 // OEMAPI月額固定費(税抜)
                        'OemApiMonthlyFeeTax'           => nvl($monthlyClosingInfoData['OemApiMonthlyFeeTax'], 0),              // OEMAPI月額固定費消費税
                        'OemCreditNoticeMonthlyFee'     => nvl($monthlyClosingInfoData['OemCreditNoticeMonthlyFee'], 0),        // OEM与信結果通知サービス月額固定費(税抜)
                        'OemCreditNoticeMonthlyFeeTax'  => nvl($monthlyClosingInfoData['OemCreditNoticeMonthlyFeeTax'], 0),     // OEM与信結果通知サービス月額固定費消費税
                        'OemNCreditNoticeMonthlyFee'    => nvl($monthlyClosingInfoData['OemNCreditNoticeMonthlyFee'], 0),       // OEM次回請求与信結果通知サービス月額固定費(税抜)
                        'OemNCreditNoticeMonthlyFeeTax' => nvl($monthlyClosingInfoData['OemNCreditNoticeMonthlyFeeTax'], 0),    // OEM次回請求与信結果通知サービス月額固定費消費税
                        'OemReserveMonthlyFee'          => nvl($monthlyClosingInfoData['OemReserveMonthlyFee'], 0),             // OEM月額固定費予備(税抜)
                        'OemReserveMonthlyFeeTax'       => nvl($monthlyClosingInfoData['OemReserveMonthlyFeeTax'], 0),          // OEM月額固定費予備消費税
                );

                $mdlatpc->saveNew($data);
                // 2015/10/19 Y.Suzuki Add 会計対応 End

                // 注文で立替済みにする
                $this->setChgStatus($payingControlSeq, $nChargeFixedDate, $enterpriseId, $opId);

                // 立替・売上で立替確認にする
                $this->setChargeDecision($payingControlSeq, $enterpriseId, $opId, $nChargeFixedDate);

                // キャンセル管理で精算済みにする
                $this->setKeepAnAccurate($payingControlSeq, $enterpriseId, $opId, $nChargeFixedDate);

                // 印紙代管理で精算済みにする
                $this->setClear($payingControlSeq, $enterpriseId, $opId, $nChargeFixedDate);

                // 立替精算戻し管理で精算済みにする
                $this->setPayDecision($payingControlSeq, $enterpriseId, $opId, $nChargeFixedDate);

                // 調整額管理の保存配列より調整額管理を追加する
                $this->setAdjustmentAmount($payingControlSeq, $enterpriseId, $opId);

                // コミット
                $this->_adapter->getDriver()->getConnection()->commit();
            }

        } catch (\Exception $e) {
            try {
                // 復旧用のため、調整額の配列をバックアップする
                if (isset($this->_arrAdjustAmount) && is_array($this->_arrAdjustAmount)) {
                    $jsnAdjustAmount = json_encode($this->_arrAdjustAmount);
                    $this->_logger->info('[payingtempfixed] BackUp AdjustAmount：' . "\r\n" . $jsnAdjustAmount);
                }
                // ロールバック
                $this->_adapter->getDriver()->getConnection()->rollBack();
            } catch ( \Exception $err ) { }
            throw $e;
        }
	}

    /**
	 * 立替・売上管理で立替未確定にする
	 *
	 * @param int $pcSeq 立替振込管理Seq
	 * @param int $opId 担当者
	 */
	private function setChargeDecisionInit($payingControlSeq, $opId)
	{
	    $mdlpas = new TablePayingAndSales($this->_adapter);                    // 立替・売上管理

	    $data = array(
	            'ChargeDecisionFlg' => 0,                                      // 立替確定フラグ(0：立替未確定)
	            'ChargeDecisionDate' => NULL,                                  // 立替確定日
	            'PayingControlSeq' => NULL,                                    // 立替振込管理Seq
	            'UpdateDate' => date('Y-m-d H:i:s'),
	            'UpdateId' => $opId,
	    );

	    $condition = array(
	            'PayingControlSeq' => $payingControlSeq,
	            'PayingControlStatus' => 0,                                    // 本締め／仮締め区分(0:仮締め)
	    );

	    return $mdlpas->saveUpdateWhere($data, $condition);
	}

	/**
	 * 注文で未立替にする
	 *
	 * @param int $payingControlSeq 立替振込管理Seq
	 * @param int $opId 担当者
	 */
	private function setChgStatusInit($payingControlSeq, $opId)
	{
	    $mdlo = new TableOrder($this->_adapter);                               // 注文

	    $data = array(
	            'Chg_Status' => 0,                                             // 立替処理－ステータス(0：未立替)
	            'Chg_FixedDate' => NULL,                                       // 立替処理－立替締め日
	            'Chg_DecisionDate' => NULL,                                    // 注文.立替処理－立替確定
	            'Chg_ChargeAmount' => NULL,                                    // 立替処理－立替金額
	            'Chg_Seq' => NULL,
	            'UpdateDate' => date('Y-m-d H:i:s'),
	            'UpdateId' => $opId,
	    );

	    $condition = array(
	            'Chg_Seq' => $payingControlSeq,                                // 立替処理－立替振込管理Seq
	    );

	    return $mdlo->saveUpdateWhere($data, $condition);
	}

	/**
	 * キャンセル管理で未精算みにする
	 *
	 * @param int $payingControlSeq 立替振込管理Seq
	 * @param int $opId 担当者
	 */
	private function setKeepAnAccurateInit($payingControlSeq, $opId)
	{
	    $mdlc = new TableCancel($this->_adapter);                              // キャンセル管理

	    $data = array(
	            'KeepAnAccurateFlg' => 0,                                      // 精算フラグ(0：未精算)
	            'KeepAnAccurateDate' => NULL,                                  // 精算日
	            'PayingControlSeq' => NULL,                                    // 立替振込管理Seq
	            'UpdateDate' => date('Y-m-d H:i:s'),
	            'UpdateId' => $opId,
	    );

	    $condition = array(
	            'PayingControlSeq' => $payingControlSeq,                       // 立替振込管理Seq
	            'PayingControlStatus' => 0,                                    // 本締め／仮締め区分(0:仮締め)
	            'ValidFlg' => 1,
	    );

	    return $mdlc->saveUpdateWhere($data, $condition);
	}

	/**
	 * 印紙代管理で未精算にする
	 *
	 * @param int $payingControlSeq 立替振込管理Seq
	 * @param int $opId 担当者
	 */
	private function setClearInit($payingControlSeq, $opId)
	{
	    $mdlsf = new TableStampFee($this->_adapter);                           // 印紙代管理

	    $data = array(
	            'ClearFlg' => 0,                                               // 印紙代精算フラグ(0：未精算)
	            'ClearDate' => NULL,                                           // 印紙代精算日
	            'PayingControlSeq' => NULL,                                    // 立替振込管理Seq
	            'UpdateDate' => date('Y-m-d H:i:s'),
	            'UpdateId' => $opId,
	    );

	    $condition = array(
	            'PayingControlSeq' => $payingControlSeq,                       // 立替振込管理Seq
	            'PayingControlStatus' => 0,                                    // 本締め／仮締め区分(0:仮締め)
	    );

	    return $mdlsf->saveUpdateWhere($data, $condition);
	}

	/**
	 * 立替精算戻し管理で未精算にする
	 *
	 * @param int $payingControlSeq 立替振込管理Seq
	 * @param int $opId 担当者
	 */
	private function setPayDecisionInit($payingControlSeq, $opId)
	{
	    $mdlpbc = new TablePayingBackControl($this->_adapter);                 // 立替精算戻し管理

	    $data = array(
	            'PayDecisionFlg' => 0,                                         // 立替確定フラグ(0：指示)
	            'PayDecisionDate' => NULL,                                     // 立替確定日
	            'PayingControlSeq' => NULL,                                    // 立替振込管理Seq
	            'UpdateDate' => date('Y-m-d H:i:s'),
	            'UpdateId' => $opId,
	    );

	    $condition = array(
	            'PayingControlSeq' => $payingControlSeq,                       // 立替振込管理Seq
	            'PayingControlStatus' => 0,                                    // 本締め／仮締め区分(0:仮締め)
	    );

	    return $mdlpbc->saveUpdateWhere($data, $condition);
	}

	/**
	 * 指定条件の調整額管理情報を保存・削除する
	 *
	 * @param int $payingControlSeq 立替振込管理Seq
	 * @param int $fixedDate 立替締め日
	 * @param int $decisionDate 立替確認日
	 * @param int $opId 担当者
	 */
	private function storageAndDelAdjustmentAmount($payingControlSeq, $enterpriseId, $fixedDate, $decisionDate)
	{
	    $mdlaa = new TableAdjustmentAmount($this->_adapter);                           // 調整額管理

	    $tempArrAdjustAmount = array();

        $i = 0;

	    $condition = array(
	            'PayingControlSeq' => $payingControlSeq,                               // 立替振込管理SEQ
        );

        $aaDatas = $mdlaa->findAdjustmentAmount($condition, true);

        foreach ($aaDatas as $aaData){
            $tempArrAdjustAmount[$i]['EnterpriseId'] = $enterpriseId;                       // 加盟店ID
            $tempArrAdjustAmount[$i]['FixedDate'] = $fixedDate;                             // 旧立替締め日
            $tempArrAdjustAmount[$i]['DecisionDate'] = $decisionDate;                       // 立替確定日
            $tempArrAdjustAmount[$i]['PayingControlSeq'] = $aaData['PayingControlSeq'];     // 立替振込管理SEQ
            $tempArrAdjustAmount[$i]['SerialNumber'] = $aaData['SerialNumber'];             // 旧連番
            $tempArrAdjustAmount[$i]['OrderId'] = $aaData['OrderId'];                       // 注文ID
            $tempArrAdjustAmount[$i]['OrderSeq'] = $aaData['OrderSeq'];                     // 注文SEQ
            $tempArrAdjustAmount[$i]['ItemCode'] = $aaData['ItemCode'];                     // 科目コード
            $tempArrAdjustAmount[$i]['AdjustmentAmount'] = $aaData['AdjustmentAmount'];     // 調整額
            $tempArrAdjustAmount[$i]['RegistDate'] = $aaData['RegistDate'];                 // 登録日時
            $tempArrAdjustAmount[$i]['RegistId'] = $aaData['RegistId'];                     // 登録者

            $i = $i + 1;
        }

        $this->_arrAdjustAmount = array_merge($this->_arrAdjustAmount, $tempArrAdjustAmount);

        $mdlaa->deleteAdjustmentAmount($condition);

        // 2015/11/18 Y.Suzuki Add 会計対応 Stt
        // 会計用項目のDELETE
        $sql = "DELETE FROM AT_AdjustmentAmount WHERE PayingControlSeq = " . $payingControlSeq;
        $this->_adapter->query($sql)->execute(null);
        // 2015/11/18 Y.Suzuki Add 会計対応 End
    }

	/**
	 * 注文データの取得
	 * @param int $enterpriseId 加盟店ID
	 * @param string $fixedDate 立替締め日
	 * @return \Zend\Db\Adapter\Driver\ResultInterface
	 */
	private function getOrderData($enterpriseId, $fixedDate)
	{
	    $sql  = " SELECT ";
	    $sql .= "       COUNT(*) as Cnt ";                                             // 立替注文件数
	    $sql .= " ,     IFNULL( SUM(T_PayingAndSales.UseAmount ";
	    $sql .= "           - T_PayingAndSales.SettlementFee ";
	    $sql .= "           - T_PayingAndSales.ClaimFee), 0) as SUM_UseAmount ";           // 立替金額
	    $sql .= " ,     IFNULL( SUM(T_PayingAndSales.SettlementFee), 0) as SUM_SettlementFee ";    // 決済手数料
	    $sql .= " ,     IFNULL( SUM(T_PayingAndSales.ClaimFee), 0) as SUM_ClaimFee ";              // 請求手数料
	    $sql .= " ,     T_Order.OemId as OemId ";                                      // OEMID
	    $sql .= " ,     T_Oem.PayingMethod as PayingMethod ";                          // 立替区分
	    $sql .= " FROM T_PayingAndSales ";
	    $sql .= " INNER JOIN T_Order ";
	    $sql .= " ON    T_Order.OrderSeq = T_PayingAndSales.OrderSeq ";
	    $sql .= " LEFT OUTER JOIN T_Oem ";
	    $sql .= " ON    T_Oem.OemId = T_Order.OemId ";
	    $sql .= " WHERE T_PayingAndSales.ClearConditionForCharge = 1 ";                // 1:条件をクリアしている
	    $sql .= " AND   T_PayingAndSales.ChargeDecisionFlg = 0 ";                      // 0:立替未確定
	    $sql .= " AND   T_PayingAndSales.CancelFlg = 0 ";                              // 0:未キャンセル
	    $sql .= " AND   T_Order.EnterpriseId = :EnterpriseId ";
        $sql .= " AND   T_PayingAndSales.ClearConditionDate <= :ClearConditionDate ";  // 締め日以前に条件クリアしたデータが対象
        $sql .= " AND   T_Order.Chg_NonChargeFlg = 0 ";                                // 0:立替対象

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
                ':EnterpriseId' => $enterpriseId,
	            ':ClearConditionDate' => $fixedDate,
        );

	    return $stm->execute($prm);
	}

	/**
	 * キャンセルデータの取得
	 * @param int $enterpriseId 加盟店ID
	 * @param string $fixedDate 立替締め日
	 * @return \Zend\Db\Adapter\Driver\ResultInterface
	 */
	private function getCancelData($enterpriseId, $fixedDate)
	{
        $sql  = " SELECT ";
        $sql .= "       COUNT(T_Cancel.Seq) as Cnt ";                       // キャンセル件数
        $sql .= " ,     IFNULL( SUM(T_Cancel.RepayTotal) * -1, 0) as RepayTotal";            // キャンセル精算金額
        $sql .= " FROM  T_Cancel ";
        $sql .= " INNER JOIN T_Order ";
        $sql .= " ON    T_Order.OrderSeq = T_Cancel.OrderSeq ";
        $sql .= " WHERE T_Cancel.ApproveFlg = 1 ";                          // 1:キャンセル承認
        $sql .= " AND   T_Cancel.KeepAnAccurateFlg = 0 ";                   // 0：未精算
        $sql .= " AND   T_Order.EnterpriseId = :EnterpriseId ";
        $sql .= " AND   T_Cancel.ValidFlg = 1 ";                            // 1:有効
        $sql .= " AND   DATE(T_Cancel.ApprovalDate) <= :ApprovalDate ";           // 承認日が締め日以前のデータが対象

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':EnterpriseId' => $enterpriseId,
	            ':ApprovalDate' => $fixedDate,
	    );

	    return $stm->execute($prm);
	}

	/**
	 * 印紙代データの取得
	 * @param int $enterpriseId 加盟店ID
	 * @param string $fixedDate 立替締め日
	 * @return \Zend\Db\Adapter\Driver\ResultInterface
	 */
	private function getStampFeeData($enterpriseId, $fixedDate)
	{
        $sql  = " SELECT ";
        $sql .= "       COUNT(T_StampFee.Seq) as Cnt ";                     // 印紙代件数
        $sql .= " ,     IFNULL( SUM(T_StampFee.StampFee), 0) as StampFee ";             // 印紙代精算金額
        $sql .= " FROM T_StampFee ";
        $sql .= " INNER JOIN T_Order ";
        $sql .= " ON    T_Order.OrderSeq = T_StampFee.OrderSeq ";
        $sql .= " WHERE T_StampFee.ClearFlg = 0 ";							// 0：未精算
        $sql .= " AND   T_Order.EnterpriseId = :EnterpriseId ";
        $sql .= " AND   T_StampFee.DecisionDate <= :DecisionDate ";         // 締め日以前のデータが対象

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':EnterpriseId' => $enterpriseId,
	            ':DecisionDate' => $fixedDate,
	    );

	    return $stm->execute($prm);
	}

	/**
	 * 立替精算戻りデータの取得
	 * @param int $enterpriseId 加盟店ID
	 * @param string $fixedDate 立替締め日
	 * @return \Zend\Db\Adapter\Driver\ResultInterface
	 */
	private function getPayBackControlData($enterpriseId, $fixedDate)
	{
	    $sql  = " SELECT ";
	    $sql .= "       COUNT(T_PayingBackControl.PayingBackSeq) as CNT ";         // 立替精算戻し件数
	    $sql .= " ,     IFNULL( SUM(T_PayingBackControl.PayBackAmount), 0) as PayBackAmount";  // 立替精算戻し金額
	    $sql .= " FROM T_PayingBackControl ";
	    $sql .= " INNER JOIN T_Order ";
	    $sql .= " ON T_Order.OrderSeq = T_PayingBackControl.OrderSeq ";
	    $sql .= " WHERE T_PayingBackControl.PayDecisionFlg = 0 ";                  // 0：指示
	    $sql .= " AND   T_PayingBackControl.ValidFlg = 1 ";                        // 1：有効
	    $sql .= " AND   T_Order.EnterpriseId = :EnterpriseId ";
	    $sql .= " AND   DATE(T_PayingBackControl.PayBackIndicationDate) <= :PayBackIndicationDate "; // 締め日以前のデータが対象

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':EnterpriseId' => $enterpriseId,
	            ':PayBackIndicationDate' => $fixedDate,
	    );

	    return $stm->execute($prm);
	}

// 2015/10/19 Y.Suzuki Del 会計対応 Stt
// 会計対応で月額固定費の情報は加盟店月締め情報テーブルから取得するため、削除
//     /**
//      * 月額固定費加算条件の取得
//      *
//      * @param int $enterpriseId 加盟店ID
//      * @param date $nMonthlyFee 加盟店.次回－課金月額固定費
//      * @param date $addUpFixedMonth 立替締め日
//      *
//      * @see ｷｬﾝﾍﾟｰﾝ期間中であれば、ｷｬﾝﾍﾟｰﾝの月額固定費を取得する。
//      */
//     private function getMonthlyFeeData($enterpriseId, $nMonthlyFee, $addUpFixedMonth)
// 2015/10/19 Y.Suzuki Del 会計対応 End

    /**
     * 月額固定費加算条件の取得
     *
     * @param int $enterpriseId 加盟店ID
     * @param date $nMonthlyFee 加盟店.次回－課金月額固定費
     * @param date $addUpFixedMonth 立替締め日
     *
     * @see ｷｬﾝﾍﾟｰﾝ期間中であれば、ｷｬﾝﾍﾟｰﾝの月額固定費を取得する。
     */
    private function getCountMonthlyFee($enterpriseId, $addUpFixedMonth)
    {

        $sql  = ' SELECT COUNT(*)  as CNT ';
        $sql .= '   FROM T_PayingControl pc ';
        $sql .= '  WHERE 1 = 1 ';
        $sql .= '    AND pc.AddUpFixedMonth = :AddUpFixedMonth ';
        $sql .= '    AND pc.EnterpriseId    = :EnterpriseId ';

        $prm = array(
                    ':AddUpFixedMonth' => $addUpFixedMonth,
                    ':EnterpriseId' => $enterpriseId,
        );

        $cnt = $this->_adapter->query($sql)->execute($prm)->current()['CNT'];

        return $cnt;
    }

	/**
	 * 振込手数料の計算
	 *
	 * @param int $payingMethod 立替区分
	 * @param int $tcClass 同行他行区分
	 * @param int $transferAmount 振込額
	 *
	 */
	private function calculateTransferFee($payingMethod, $tcClass, $transferAmount)
	{
	    $rtnTransferFee = 0;

	    $mdlc = new TableCode($this->_adapter);                           // コードマスター

	    if ($payingMethod == 1){// OEM立替

	        $rtnTransferFee = 0;

	    }else{

            $sql  = " SELECT ";
            $sql .= "       Note ";
            $sql .= " FROM  M_Code ";
            $sql .= " WHERE CodeId = 93 ";
            $sql .= " AND   Class1 = :TcClass ";
            $sql .= " AND   Class2 <= :TransferAmount < Class3 ";

            $stm = $this->_adapter->query($sql);

            $prm = array(
                    ':TcClass' => $tcClass,
                    ':TransferAmount' => $transferAmount,
            );

            $row = $stm->execute($prm)->current();

            $rtnTransferFee = isset($row) ? $row['Note'] : 0;
	    }

	    return $rtnTransferFee;
	}

	/**
	 * 注文で立替済にする
	 *
	 * @param int $pcSeq 立替振込管理Seq
	 * @param int $fixedDate 立替処理－立替締め日
	 * @param int $enterpriseId 加盟店ID
	 * @param int $opId 担当者
	 */
	private function setChgStatus($payingControlSeq, $fixedDate, $enterpriseId, $opId)
	{

        // SQL構築
$sql = <<<EOQ
UPDATE T_Order o
SET    o.Chg_Status             = 1
      ,o.Chg_FixedDate          = :Chg_FixedDate
      ,o.Chg_DecisionDate       = :Chg_DecisionDate
      ,o.Chg_ChargeAmount       = ( SELECT MAX(t.ChargeAmount) FROM T_PayingAndSales t WHERE t.OrderSeq = o.OrderSeq )
      ,o.Chg_Seq                = :Chg_Seq
      ,o.UpdateDate             = :UpdateDate
      ,o.UpdateId               = :UpdateId
WHERE 1 = 1
AND   o.EnterpriseId = :EnterpriseId
AND   o.Chg_NonChargeFlg = 0
AND   EXISTS( SELECT *
                FROM T_PayingAndSales pas
               WHERE o.OrderSeq = pas.OrderSeq
                 AND pas.ClearConditionForCharge = 1
                 AND pas.ChargeDecisionFlg = 0
                 AND pas.CancelFlg = 0
                 AND pas.ClearConditionDate <= :ClearConditionDate
            )
EOQ;

        // パラメーター設定
        $prm = array(
            ':Chg_FixedDate'    => $fixedDate,
            ':Chg_DecisionDate' => date('Y-m-d'),
            ':Chg_Seq'          => $payingControlSeq,
            ':UpdateDate'       => date('Y-m-d H:i:s'),
            ':UpdateId'         => $opId,
            ':EnterpriseId'     => $enterpriseId,
            ':ClearConditionDate' => $fixedDate,
        );

        // SQL実行
        $this->_adapter->query($sql)->execute($prm);

	}

	/**
	 * 立替確認にする
	 * @param int $payingControlSeq 立替振込管理Seq
	 * @param int $enterpriseId 加盟店ID
	 * @param int $opId 担当者
	 * @param date $fixedDate
	 */
	private function setChargeDecision($payingControlSeq, $enterpriseId, $opId, $fixedDate)
	{

	    // SQL構築
$sql = <<<EOQ
UPDATE T_PayingAndSales pas, T_Order o
SET    pas.ChargeDecisionFlg    = 1
      ,pas.ChargeDecisionDate   = :ChargeDecisionDate
      ,pas.PayingControlSeq     = :PayingControlSeq
      ,pas.UpdateDate           = :UpdateDate
      ,pas.UpdateId             = :UpdateId
WHERE 1 = 1
AND   pas.ClearConditionForCharge = 1
AND   pas.ChargeDecisionFlg = 0
AND   pas.CancelFlg = 0
AND   pas.ClearConditionDate <= :ClearConditionDate
AND   pas.OrderSeq = o.OrderSeq
AND   o.EnterpriseId = :EnterpriseId
AND   o.Chg_NonChargeFlg = 0
EOQ;

        // パラメーター設定
        $prm = array(
            ':ChargeDecisionDate'   => date('Y-m-d'),
            ':PayingControlSeq'     => $payingControlSeq,
            ':UpdateDate'           => date('Y-m-d H:i:s'),
            ':UpdateId'             => $opId,
            ':ClearConditionDate'   => $fixedDate,
            ':EnterpriseId'         => $enterpriseId,
        );

	    // SQL実行
	    $this->_adapter->query($sql)->execute($prm);

	}

	/**
	 * キャンセル管理で精算済みにする
	 *
	 * @param int $payingControlSeq 立替振込管理Seq
	 * @param int $enterpriseId 加盟店ID
	 * @param int $opId 担当者
	 */
	private function setKeepAnAccurate($payingControlSeq, $enterpriseId, $opId, $fixedDate)
	{

        // SQL構築
$sql = <<<EOQ
UPDATE T_Cancel c
SET    c.KeepAnAccurateFlg  = 1
      ,c.KeepAnAccurateDate = :KeepAnAccurateDate
      ,c.PayingControlSeq   = :PayingControlSeq
      ,c.UpdateDate         = :UpdateDate
      ,c.UpdateId           = :UpdateId
WHERE 1 = 1
AND   c.ApproveFlg = 1
AND   c.KeepAnAccurateFlg = 0
AND   c.ValidFlg = 1
AND   DATE(c.ApprovalDate) <= :ApprovalDate
AND   EXISTS ( SELECT *
                 FROM T_Order o
                WHERE o.OrderSeq = c.OrderSeq
                  AND o.EnterpriseId = :EnterpriseId
             )
EOQ;

        // パラメーター設定
        $prm = array(
            ':KeepAnAccurateDate'   => date('Y-m-d'),
            ':PayingControlSeq'     => $payingControlSeq,
            ':UpdateDate'           => date('Y-m-d H:i:s'),
            ':UpdateId'             => $opId,
            ':ApprovalDate'         => $fixedDate,
            ':EnterpriseId'         => $enterpriseId,
        );

        // SQL実行
        $this->_adapter->query($sql)->execute($prm);

	}

	/**
	 * 印紙代管理で精算済にする
	 * @param int $payingControlSeq
	 * @param int $enterpriseId
	 * @param int $opId
	 * @param date $fixedDate
	 */
	private function setClear($payingControlSeq, $enterpriseId, $opId, $fixedDate)
	{

	    // SQL構築
	    $sql = <<<EOQ
UPDATE T_StampFee s
SET    s.ClearFlg           = 1
      ,s.ClearDate          = :ClearDate
      ,s.PayingControlSeq   = :PayingControlSeq
      ,s.UpdateDate         = :UpdateDate
      ,s.UpdateId           = :UpdateId
WHERE 1 = 1
AND   s.ClearFlg = 0
AND   s.DecisionDate <= :DecisionDate
AND   EXISTS ( SELECT *
                 FROM T_Order o
                WHERE o.OrderSeq = s.OrderSeq
                  AND o.EnterpriseId = :EnterpriseId
             )
EOQ;

	    // パラメーター設定
	    $prm = array(
                ':ClearDate'        => date('Y-m-d'),
                ':PayingControlSeq' => $payingControlSeq,
                ':UpdateDate'       => date('Y-m-d H:i:s'),
                ':UpdateId'         => $opId,
                ':DecisionDate'     => $fixedDate,
                ':EnterpriseId'     => $enterpriseId,
        );

        // SQL実行
        $this->_adapter->query($sql)->execute($prm);

	}

	/**
	 * 立替精算戻し管理で精算済にする
	 * @param int $payingControlSeq 立替振込管理Seq
	 * @param int $enterpriseId 加盟店ID
	 * @param int $opId 担当者
	 * @param int $fixedDate 立替締め日
	 */
	private function setPayDecision($payingControlSeq, $enterpriseId, $opId, $fixedDate)
	{

	    // SQL構築
$sql = <<<EOQ
UPDATE T_PayingBackControl pbc
SET    pbc.PayDecisionFlg   = 0
      ,pbc.PayDecisionDate  = :PayDecisionDate
      ,pbc.PayingControlSeq = :PayingControlSeq
      ,pbc.UpdateDate       = :UpdateDate
      ,pbc.UpdateId         = :UpdateId
WHERE 1 = 1
AND   pbc.PayDecisionFlg = 0
AND   pbc.ValidFlg = 1
AND   DATE(pbc.PayBackIndicationDate) <= :PayBackIndicationDate
AND   EXISTS ( SELECT *
                 FROM T_Order o
                WHERE o.OrderSeq = pbc.OrderSeq
                  AND o.EnterpriseId = :EnterpriseId
             )
EOQ;

	    // パラメーター設定
	    $prm = array(
	            ':PayDecisionDate'     => date('Y-m-d'),
	            ':PayingControlSeq'    => $payingControlSeq,
	            ':UpdateDate'          => date('Y-m-d H:i:s'),
	            ':UpdateId'            => $opId,
	            ':PayBackIndicationDate' => $fixedDate,
	            ':EnterpriseId'        => $enterpriseId,
	    );

	    // SQL実行
	    $this->_adapter->query($sql)->execute($prm);

	}

	/**
	 * 調整額管理
	 *
	 * @param int $payingControlSeq 立替振込管理Seq
	 * @param int $enterpriseId 加盟店ID
	 * @param int $opId 担当者
	 */
	private function setAdjustmentAmount($payingControlSeq, $enterpriseId, $opId)
	{
	    $mdlaa = new TableAdjustmentAmount($this->_adapter);                       // 調整額管理

	    $serialNumber = 1;

        for ($i = 0; $i < count($this->_arrAdjustAmount); $i++){

            $data = $this->_arrAdjustAmount[$i];

            if ($enterpriseId == $data['EnterpriseId']){

                $mdlaa->saveNew(
	                array(
	                        'PayingControlSeq' => $payingControlSeq,               // 立替振込管理SEQ
	                        'SerialNumber' => $serialNumber,                       // 連番
	                        'OrderId' => $data["OrderId"],                         // 注文ID
	                        'OrderSeq' => $data["OrderSeq"],                       // 注文SEQ
	                        'ItemCode' => $data["ItemCode"] ,                      // 科目コード
	                        'AdjustmentAmount' => $data["AdjustmentAmount"],       // 調整額
	                        'RegistDate' => $data["RegistDate"],                   // 登録日時
	                        'RegistId' => $data["RegistId"],                       // 登録者
	                        'UpdateDate' => date('Y-m-d H:i:s'),                   // 更新日時
	                        'UpdateId' => $opId,                                   // 更新者
	                        'ValidFlg' => 1,                                       // 有効フラグ
                    )
                );

                // 2015/11/18 Y.Suzuki Add 会計対応 Stt
                // 会計用項目のINSERT
                $mdlataa = new ATableAdjustmentAmount($this->_adapter);
                $atdata = array(
                        'PayingControlSeq' => $payingControlSeq,
                        'SerialNumber' => $serialNumber,
                        'DailySummaryFlg' => 0,
                );

                $mdlataa->saveNew($atdata);
                // 2015/11/18 Y.Suzuki Add 会計対応 End

                $serialNumber = $serialNumber + 1;
            }
        }
	}

	/**
	 * 調整額取得
	 *
	 * @param int $enterpriseId 加盟店ID
	 * @return int$rtnAdjustmentAmount 調整額
	 */
	private function getAdjustmentAmount($enterpriseId)
	{
	    $rtnAdjustmentAmount = 0;

	    for ($i = 0; $i < count($this->_arrAdjustAmount); $i++){

	        $data = $this->_arrAdjustAmount[$i];

	        if ($enterpriseId == $data['EnterpriseId']){

	           $rtnAdjustmentAmount = $rtnAdjustmentAmount + $data["AdjustmentAmount"];
	        }
	    }

	    return $rtnAdjustmentAmount;
	}
}

