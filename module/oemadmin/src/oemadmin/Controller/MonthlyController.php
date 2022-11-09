<?php
namespace oemadmin\Controller;

use Coral\Coral\Controller\CoralControllerAction;
use oemadmin\Application;
use Coral\Base\BaseHtmlUtils;
use DOMPDFModule\View\Model\PdfModel;
use models\Table\TableOem;
use models\Table\TableOemClaimed;
use models\Table\TableOemEnterpriseClaimed;
use models\Table\TableSystemProperty;
use models\Logic\LogicOemTradingSettlement;
use models\Logic\LogicTemplate;
use Zend\Db\Adapter\Driver\ResultInterface;

class MonthlyController extends CoralControllerAction
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

        $this->addStyleSheet($this->app->getOemCss())
            ->addStyleSheet('../../oemadmin/css/monthly.css')
            ->addJavaScript('../../js/prototype.js');

        $this->setPageTitle($this->app->getOemServiceName()." - 月次明細");
    }

    /**
     * 精算書閲覧
     */
    public function settlementAction()
    {
        $fixedDate = empty($this->getParams()['fd']) ? -1 : $this->getParams()['fd'];

        $cb['logo'] = 'Atobarai_logo_3.gif';
        $cb['company'] = '株式会社キャッチボール';
        $cb['postAddr'] = '〒140-0002';
        $cb['addr'] = '東京都品川区東品川2-2-24';
        $cb['subAddr'] = '天王洲セントラルタワー 12F';
        $this->view->assign('cb', $cb);

        $oemInfo = Application::getInstance()->getOemInfo();

        //セレクトボックス作成
        $date_list = $this->_createMonthList($oemInfo['OemId']);
        $fixedMonthTag = BaseHtmlUtils::SelectTag(
            'fd',
            $date_list,
            $fixedDate
        );
        $this->view->assign('fixedMonthTag', $fixedMonthTag);

        $mdlOem = new TableOem($this->app->dbAdapter);
        $oem = $mdlOem->findOem2($oemInfo['OemId'])->current();

        $mdloc = new TableOemClaimed($this->app->dbAdapter);

        $data_list = array();

        //fdがなければ取得した最初のデータのみ
        if($fixedDate == -1){
            $data_list = $mdloc->findOemClaimed($oemInfo['OemId'])->current();
            reset($date_list);
            $fd = key($date_list);

        }else{
            //fdがあればそれを用いてデータ取得

            //$fdをFromとToに分解
            $search_range = explode("_", $fixedDate);

            $data_list = $mdloc->findOemClaimed($oemInfo['OemId'],isset($search_range[0])?$search_range[0]:null,
                                                isset($search_range[1])?$search_range[1]:null)->current();
            $fd = $fixedDate;

        }

        //データ取得に失敗していた時用に初期化
        if(empty($data_list)){
            $data_list = array(
                "SpanFrom" => "",
                "SpanTo" => "",
                "UseAmount" => "",
                "PC_DecisionPayment" => "",
                "FixedTransferAmount" => "",
                "OM_TotalProfit" => "",
                "SettlePlanDate" => "",
                "CB_MonthlyFee" => "",
                "OM_ShopTotal" => "",
                "OM_SettleShopTotal" => "",
                "EntMonthlyFeeTotal" => "",
                "CB_EntMonthlyFee" => "",
                "OM_EntMonthlyFee" => "",
                "OrderCount" => "",
                "SettlementFeeTotal" => "",
                "CB_SettlementFee" => "",
                "OM_SettlementFee" => "",
                "ClaimFeeBSTotal" => "",
                "CB_ClaimFeeBS" => "",
                "OM_ClaimFeeBS" => "",
                "ClaimFeeDKTotal" => "",
                "CB_ClaimFeeDK" => "",
                "OM_ClaimFeeDK" => "",
                "CR_TotalAmount" => "",
                "CR_OemAmount" => "",
                "CR_EntAmount" => "",
                "OM_AdjustmentAmount" => "",
                "PC_TransferCommission" => "",
                "AgencyFee" => ""
            );
        }

        // 消費税算出　（短命につきハードコーディング）
        $mdlsp = new TableSystemProperty($this->app->dbAdapter);
        $taxRate = $mdlsp->getTaxRateAt($data_list['ProcessDate']) * 0.01;
        $data_list['TotalProfitTax'] = floor(($data_list['OM_TotalProfit'] > 0 ? $data_list['OM_TotalProfit'] : 0) * $taxRate);
        $data_list['DspTaxFlg'] = ($oem['DspTaxFlg'] == 1);

        // 調整額一覧
        $logicots = new LogicOemTradingSettlement($this->app->dbAdapter);
        $adjustment_list = $logicots->getOemAdjustmentAmount(empty($data_list)?null:$data_list['OemClaimedSeq']);

        $this->view->assign('oemInfo', $oemInfo);
        $this->view->assign('settlement', $data_list);
        $this->view->assign('adjustment_list', $adjustment_list);
        $this->view->assign('fd',$fd);

        return $this->view;
    }

    /**
     * 店舗別精算一覧閲覧
     */
    public function storeAction() {

        $fixedDate = empty($this->getParams()['fd']) ? -1 : $this->getParams()['fd'];

        $oemInfo = Application::getInstance()->getOemInfo();

        $mdloc = new TableOemClaimed($this->app->dbAdapter);
        $mdloec = new TableOemEnterpriseClaimed($this->app->dbAdapter);
        $enterprise_claimed_data = array();

        //fdがなければ取得した最初のデータのみ
        if($fixedDate == -1){
            //OEM請求データ取得
            $claimed_data = $mdloc->findOemClaimed($oemInfo['OemId'])->current();
        }else{

            //$fdをFromとToに分解
            $search_range = explode("_", $fixedDate);

            //OEM請求データ取得
            $claimed_data = $mdloc->findOemClaimed($oemInfo['OemId'],
                                                   isset($search_range[0])?$search_range[0]:null,
                                                   isset($search_range[1])?$search_range[1]:null)->current();
        }

        //OEM請求データが取れていれば
        if(!empty($claimed_data)){
            //店舗ごとデータ取得
            $enterprise_claimed_data = $mdloec->findOemEnterpriseClaimed($oemInfo['OemId'],
                                                                         $claimed_data['SpanFrom'],
                                                                         $claimed_data['SpanTo']);
        }else{
            $claimed_data = array();
        }

        // OEM立替かつ振込手数料加算か否か
        $addTcFlg = (($oemInfo['PayingMethod'] + $oemInfo['AddTcClass']) > 1) ? true : false;

        $date_list = $this->_createMonthList($oemInfo['OemId']);
        $fixedMonthTag = BaseHtmlUtils::SelectTag(
            'fd',
            $date_list,
            $fixedDate
        );
        $this->view->assign('fixedMonthTag', $fixedMonthTag);

        //店舗別精算明細取得
        $this->view->assign('settlement',$claimed_data );
        $this->view->assign('enterprise_settlement', $enterprise_claimed_data);
        $this->view->assign('addtcflg', $addTcFlg);

        return $this->view;
    }

    /**
     * 店舗別精算明細閲覧
     */
    public function storedetailAction() {

        //対象期間と事業者ID取得
        $fixedDate = empty($this->getParams()['fd']) ? -1 : $this->getParams()['fd'];

        $eid = empty($this->getParams()['eid']) ? -1 : $this->getParams()['eid'];

        $oemInfo = Application::getInstance()->getOemInfo();

        // OEM立替かつ振込手数料加算か否か
        $addTcFlg = (($oemInfo['PayingMethod'] + $oemInfo['AddTcClass']) > 1) ? true : false;

        //店舗ごとデータ取得
        $mdloec = new TableOemEnterpriseClaimed($this->app->dbAdapter);

        $enterprise_claimed_data = array();

        if($fixedDate != -1 && $eid != -1){

            //$fdをFromとToに分解
            $search_range = explode("_", $fixedDate);

            $enterprise_claimed_data = $mdloec->findOemEnterpriseClaimed($oemInfo['OemId'],
                                                                         isset($search_range[0])?$search_range[0]:null,
                                                                         isset($search_range[1])?$search_range[1]:null,
                                                                         $eid)->current();
            $from = date('Y年m月d日', strtotime($search_range[0]));
            $to   = date('Y年m月d日', strtotime($search_range[1]));

        }

        //データがない場合
        if(empty($enterprise_claimed_data)){
            //初期化
            $enterprise_claimed_data = array(
                "EnterpriseNameKj" => "",
                "UseAmount" => "",
                "SpanFrom" => "",
                "SpanTo" => "",
                "PC_DecisionPayment" => "",
                "FixedTransferAmount" => "",
                "OM_TotalProfit" => "",
                "CB_EntMonthlyFee" => "",
                "OM_EntMonthlyFee" => "",
                "OrderCount" => "",
                "SettlementFeeRate" => "",
                "OemSettlementFeeRate" => "",
                "CB_SettlementFee" => "",
                "OM_SettlementFee" => "",
                "CB_ClaimFeeBS" => "",
                "OM_ClaimFeeBS" => "",
                "CB_ClaimFeeDK" => "",
                "OM_ClaimFeeDK" => "",
                "OM_ClaimFeeDK" => "",
                "CR_TotalAmount" => "",
                "CR_OemAmount" => "",
                "CR_EntAmount" => "",
                "PC_CarryOver" => "",
                "PC_StampFeeTotal" => "",
                "PC_TransferCommission" => "",
                "PC_AdjustmentAmount" => "",
                "PayBackAmount" => ""
            );
            $from = "";
            $to   = "";
        }

        $this->view->assign('enterprise_settlement', $enterprise_claimed_data);
        $this->view->assign('from', $from);
        $this->view->assign('to', $to);
        $this->view->assign('addtcflg', $addTcFlg);

        return $this->view;
    }

    /**
     * 取引別精算明細閲覧
     */
    public function tradingAction() {
        //月度・事業者ID取得
        $fixedDate = empty($this->getParams()['fd']) ? -1 : $this->getParams()['fd'];
        $eid = empty($this->getParams()['eid']) ? -1 : $this->getParams()['eid'];

        $oemInfo = Application::getInstance()->getOemInfo();
        $oem_trading_settlement = array();

        if($eid != -1 && $fixedDate != -1){

            $mdloc = new TableOemClaimed($this->app->dbAdapter);
            $mdloec = new TableOemEnterpriseClaimed($this->app->dbAdapter);
            $logicots = new LogicOemTradingSettlement($this->app->dbAdapter);

            //$fdをFromとToに分解
            $search_range = explode("_", $fixedDate);

            //OEM請求データ取得
            $claimed_data = $mdloc->findOemClaimed($oemInfo['OemId'],
                                                   isset($search_range[0])?$search_range[0]:null,
                                                   isset($search_range[1])?$search_range[1]:null)->current();

            //事業者OEM請求データ取得
            $enterprise_claimed_data = $mdloec->findOemEnterpriseClaimed($oemInfo['OemId'],
                                                                         isset($search_range[0])?$search_range[0]:null,
                                                                         isset($search_range[1])?$search_range[1]:null,
                                                                         $eid)->current();
            //注文データ取得
            $sql = <<<EOQ
SELECT  ORD.OrderId AS OrderId
    ,   ORD.Ent_OrderId AS Ent_OrderId
    ,   CUS.NameKj AS NameKj
    ,   ORD.ReceiptOrderDate AS ReceiptOrderDate
    ,   ITM.Deli_JournalIncDate AS Deli_JournalIncDate
    ,   ORD.Chg_FixedDate AS Chg_FixedDate
    ,   ORD.UseAmount /*+ ORD.Clm_L_DamageInterestAmount + ORD.Clm_L_ClaimFee*/ AS UseAmount
    ,   PAS.SettlementFee AS SettlementFeeTotal
    ,   PAS.SettlementFee - OSF.SettlementFee AS SettlementFeeCB
    ,   OSF.SettlementFee AS SettlementFeeOEM
    ,   CASE
            WHEN IFNULL(OCF.ClaimFeeType,1) = 1 THEN PAS.ClaimFee
            ELSE 0
        END AS ClaimFeeTypeTotal
    ,   CASE
            WHEN IFNULL(OCF.ClaimFeeType,1) = 1 THEN PAS.ClaimFee - IFNULL(OCF.ClaimFee,0)
            ELSE 0
        END AS ClaimFeeTypeCB
    ,   CASE
            WHEN IFNULL(OCF.ClaimFeeType,1) = 1 THEN IFNULL(OCF.ClaimFee,0)
            ELSE 0
        END AS ClaimFeeTypeOEM
    ,   CASE
            WHEN IFNULL(OCF.ClaimFeeType,1) = 2 THEN PAS.ClaimFee
            ELSE 0
        END AS ClaimFeeType2Total
    ,   CASE
            WHEN IFNULL(OCF.ClaimFeeType,1) = 2 THEN PAS.ClaimFee - IFNULL(OCF.ClaimFee,0)
            ELSE 0
        END AS ClaimFeeType2CB
    ,   CASE
            WHEN IFNULL(OCF.ClaimFeeType,1) = 2 THEN IFNULL(OCF.ClaimFee,0)
            ELSE 0
        END AS ClaimFeeType2OEM
FROM    T_Order ORD
        INNER JOIN T_Customer CUS ON (ORD.OrderSeq = CUS.OrderSeq)
        INNER JOIN T_OrderItems ITM ON (ORD.OrderSeq = ITM.OrderSeq AND ITM.DataClass = 3)
        INNER JOIN T_PayingAndSales PAS ON (ORD.OrderSeq = PAS.OrderSeq)
        INNER JOIN T_PayingControl PC ON (PAS.PayingControlSeq = PC.Seq)
        INNER JOIN T_OemEnterpriseClaimed OEC ON (PC.OemClaimedSeq = OEC.OemClaimedSeq AND PC.EnterpriseId = OEC.EnterpriseId)
        INNER JOIN T_OemSettlementFee OSF ON (ORD.OrderSeq = OSF.OrderSeq)
        LEFT OUTER JOIN T_OemClaimFee OCF ON (ORD.OrderSeq = OCF.OrderSeq)
WHERE   OEC.OemClaimedSeq = :OemClaimedSeq
AND     OEC.EnterpriseId = :EnterpriseId
EOQ;

            $ri = $this->app->dbAdapter->query($sql)->execute(array(':OemClaimedSeq' => $claimed_data['OemClaimedSeq'], ':EnterpriseId' => $eid));
            $oem_trading_settlement = ResultInterfaceToArray($ri);

            // OEM精算仮締め対象外有無確認
            $sql = "SELECT Class1 FROM M_Code WHERE CodeId = 221 AND KeyCode = :OemId";
            $class1 = $this->app->dbAdapter->query($sql)->execute(array(':OemId'=>$oemInfo['OemId']))->current()["Class1"];

            if($class1 == 0) {
                foreach ($oem_trading_settlement as $k => $v) {
                    $sql = "SELECT ao.ExtraPayType FROM T_Order o INNER JOIN AT_Order ao ON (ao.OrderSeq = o.OrderSeq) WHERE o.OrderId=:OrderId";
                    $extraPayType = $this->app->dbAdapter->query($sql)->execute(array(':OrderId'=>$v['OrderId']))->current()['ExtraPayType'];
                    if ((!is_null($extraPayType)) && ($extraPayType == 1)) {
                        unset($oem_trading_settlement[$k]);
                    }
                }
            }

            $from = date('Y年m月d日', strtotime($search_range[0]));
            $to   = date('Y年m月d日', strtotime($search_range[1]));


        }
        //データがない場合初期化
        if(empty($enterprise_claimed_data)){
            //初期化
            $enterprise_claimed_data = array(
                "EnterpriseNameKj" => "",
                "UseAmount" => "",
                "PC_DecisionPayment" => "",
                "FixedTransferAmount" => "",
                "OM_TotalProfit" => ""
            );
            $from = "";
            $to   = "";
        }

        $this->view->assign('enterprise_settlement', $enterprise_claimed_data);
        $this->view->assign('oem_trading_settlement', $oem_trading_settlement);
        $this->view->assign('from', $from);
        $this->view->assign('to', $to);

        return $this->view;
    }

    /**
     * キャンセル返金一覧閲覧
     */
    public function cancelAction() {

        $fixedDate = empty($this->getParams()['fd']) ? -1 : $this->getParams()['fd'];

        $oemInfo = Application::getInstance()->getOemInfo();

        $date_list = $this->_createMonthList($oemInfo['OemId'], $fixedDate);
        $fixedMonthTag = BaseHtmlUtils::SelectTag(
            'fd',
            $date_list,
            $fixedDate
        );
        $this->view->assign('fixedMonthTag', $fixedMonthTag);

        //初回アクセスの場合最新の日付の情報取得
        if($fixedDate == -1){
            reset($date_list);
            $fixedDate = key($date_list);
        }

        $cancel_data = array();

        //Oem情報があれば精算データ取得
        if(isset($oemInfo['OemId'])){
            $logicots = new LogicOemTradingSettlement($this->app->dbAdapter);

            $search_range = explode("_", $fixedDate);
            $cancel_data = $logicots->getOemCancel($oemInfo['OemId'], isset($search_range[0]) ? $search_range[0] : null,
                                                   isset($search_range[1]) ? $search_range[1] : null);

            $mdloc = new TableOemClaimed($this->app->dbAdapter);
            $claimed_data = $mdloc->findOemClaimed($oemInfo['OemId'],
                                                   isset($search_range[0]) ? $search_range[0] : null,
                                                   isset($search_range[1]) ? $search_range[1] : null);

        }

        $this->view->assign('claimed_data', empty($claimed_data) ? array() : $claimed_data->current());
        $this->view->assign('from', isset($search_range[0]) ? $search_range[0] : "");
        $this->view->assign('to', isset($search_range[1]) ? $search_range[1] : "");
        $this->view->assign('cancel', empty($cancel_data) ? array() : $cancel_data);

        return $this->view;
    }

    /**
     * キャンセル返金明細閲覧
     */
    public function canceldetailAction() {

        $params = $this->getParams();

        //月度・事業者ID取得
        $fixedDate = empty($params['fd']) ? -1 : $params['fd'];
        $eid = empty($params['eid']) ? -1 : $params['eid'];

        $oemInfo = Application::getInstance()->getOemInfo();
        $cancel_data = array();
        $cancel_detaildata = array();

        if($eid != -1 && $fixedDate != -1){

            $logicots = new LogicOemTradingSettlement($this->app->dbAdapter);

            //$fdをFromとToに分解
            $search_range = explode("_", $fixedDate);

            //キャンセルデータ事業者まとめデータ取得
            $cancel_data = $logicots->getOemCancel($oemInfo['OemId'], isset($search_range[0]) ? $search_range[0] : null,
                                                   isset($search_range[1]) ? $search_range[1] : null, $eid);

            //キャンセル明細データ取得
            $cancel_detaildata = $logicots->getOemCancelDetail($oemInfo['OemId'], $eid,
                                                               isset($search_range[0]) ? $search_range[0] : null,
                                                               isset($search_range[1]) ? $search_range[1] : null);

            // OEM精算仮締め対象外有無確認
            $sql = "SELECT Class1 FROM M_Code WHERE CodeId = 221 AND KeyCode = :OemId";
            $class1 = $this->app->dbAdapter->query($sql)->execute(array(':OemId'=>$oemInfo['OemId']))->current()["Class1"];

            if($class1 == 0) {
                foreach ($cancel_detaildata as $k => $v) {
                    $sql = "SELECT ao.ExtraPayType FROM T_Order o INNER JOIN AT_Order ao ON (ao.OrderSeq = o.OrderSeq) WHERE o.OrderId=:OrderId";
                    $extraPayType = $this->app->dbAdapter->query($sql)->execute(array(':OrderId'=>$v['OrderId']))->current()['ExtraPayType'];
                    if ((!is_null($extraPayType)) && ($extraPayType == 1)) {
                        unset($cancel_detaildata[$k]);
                    }
                }
            }

            $from = date('Y年m月d日', strtotime($search_range[0]));
            $to   = date('Y年m月d日', strtotime($search_range[1]));

        }

        $this->view->assign('from', $from);
        $this->view->assign('to', $to);
        $this->view->assign('cancel_data', empty($cancel_data) ? array():$cancel_data[0]);
        $this->view->assign('cancel_detail_data', empty($cancel_detaildata) ? array() : $cancel_detaildata);

        return $this->view;
    }

    /**
     * 年月選択リスト作成
     */
    protected function _createMonthList($oemId) {

        $mdloc = new TableOemClaimed($this->app->dbAdapter);

        $date_list = array();

        //OEM請求データ取得
        $oem_claimed = $mdloc->findOemClaimed($oemId);

        //年月作成
        foreach($oem_claimed as $value){
            if ($value['PayingControlStatus'] == 0) { continue; }// ドロップダウンリストの表示対象を、本締め済みのみとする
            $from = date('Y年m月d日', strtotime($value['SpanFrom']));
            $to   = date('Y年m月d日', strtotime($value['SpanTo']));
            $date_list += array($value['SpanFrom']."_".$value['SpanTo'] => date('Y年m月度　'.$from."～".$to, strtotime($value['FixedMonth'])));
        }

        return $date_list;
    }

    /**
     * スタンプ画像出力
     */
    public function stampimageAction() {
        echo file_get_contents('./public/oemadmin/images/cb_stamp');
    }

    /**
     * 精算データダウンロード
     */
    public function payingdatadlAction() {

        //取得範囲取得
        $fixedDate = empty($this->getParams()['fd']) ? -1 : $this->getParams()['fd'];;

        // ファイル名
        $fileName = sprintf("payingdata_%s.csv", date("YmdHis"));

        $search_range = explode("_", $fixedDate);

        // OEMID取得
        $oemId = $this->app->authManagerAdmin->getUserInfo()->OemId;

        $logicots = new LogicOemTradingSettlement($this->app->dbAdapter);
        $settlement_data = $logicots->getOemTradingSettlementCsv($oemId,$search_range[0],$search_range[1]);

        // OEM精算仮締め対象外有無確認
        $sql = "SELECT Class1 FROM M_Code WHERE CodeId = 221 AND KeyCode = :OemId";
        $class1 = $this->app->dbAdapter->query($sql)->execute(array(':OemId'=>$oemId))->current()["Class1"];

        foreach($settlement_data as $value){

            //入金方法を文字に変換
            switch($value['ReceiptClass']){
                //コンビニ
                case 1:
                    $value['ReceiptMethod'] = "コンビニ";
                    break;
                //郵便局
                case 2:
                    $value['ReceiptMethod'] = "郵便局";
                    break;
                //銀行
                case 3:
                    $value['ReceiptMethod'] = "銀行";
                    break;
                //LINE Pay
                case 4:
                    $value['ReceiptMethod'] = "LINE Pay";
                    break;
                //クレジット決済(VISA/MASTER）
                case 5:
                    $value['ReceiptMethod'] = "クレジット決済(VISA/MASTER）";
                    break;
                // PayPay （オンライン決済）
                case 15:
                    $value['ReceiptMethod'] = "PayPay（オンライン決済）";
                    break;
                //クレジット決済(JCB/AMEX）
                case 21:
                    $value['ReceiptMethod'] = "クレジット決済(JCB/AMEX）";
                    break;
                //クレジット決済(Dinars）
                case 22:
                    $value['ReceiptMethod'] = "クレジット決済(Dinars）";
                    break;
                default:
                    $value['ReceiptMethod'] = "";
                    break;

            }

            //キャンセルステータス設定
            switch($value['Cnl_Status']){
                //キャンセル済みではない
                case 0:
                    $value['CancelState'] = 0;
                    break;
                //キャンセル済み もしくはキャンセル申請
                case 1:
                case 2:
                    $value['CancelState'] = 1;
                    break;
            }

            //プラン設定
            switch($value['Plan']){
                //リスクフリー
                case 11:
                    $value['Plan'] = 0;
                    break;
                //スタンダート
                case 21:
                    $value['Plan'] = 1;
                    break;
                //エキスパート
                case 31:
                    $value['Plan'] = 2;
                    break;
                //スペシャル
                case 41:
                    $value['Plan'] = 3;
                    break;
            }

            //差引合計計算
            $value['Total'] = intval($value['UseAmount']) - intval($value['SettlementFee']) - intval($value['ClaimFee']);

            if($class1 == 0) {
                $sql = "SELECT ao.ExtraPayType FROM T_Order o INNER JOIN AT_Order ao ON (ao.OrderSeq = o.OrderSeq) WHERE o.OrderId=:OrderId";
                $extraPayType = $this->app->dbAdapter->query($sql)->execute(array(':OrderId'=>$value['OrderId']))->current()['ExtraPayType'];
                if ((!is_null($extraPayType)) && ($extraPayType == 1)) {
                    continue;
                }
            }

            $datas[] = $value;
        }

        $templateId = 'COEM008'; // OEM精算書CSV
        $templateClass = 1;
        $seq = $oemId;
        $templatePattern = 0;

        $logicTemplate = new LogicTemplate( $this->app->dbAdapter );
        $response = $logicTemplate->convertArraytoResponse( $datas, $fileName, $templateId, $templateClass, $seq, $templatePattern, $this->getResponse() );

        if( $response == false ) {
            throw new \Exception( $logicTemplate->getErrorMessage() );
        }

        return $response;
    }

    /* 以下、OEM精算対応(015-PG-001) */
    /**
     * OEM精算データダウンロード
     */
    public function settlementZipAction() {

        $prm = $this->getParams();

        // ZIPファイル作成
        $zip = new \ZipArchive();

        $oemId = $this->app->getOemInfo()['OemId'];
        $spanFrom = substr($prm['fd'],  0, 10);
        $spanTo   = substr($prm['fd'], 11, 10);
        $suffix = date('Ym', strtotime(date('Y-m-01', strtotime($spanFrom))));
        $suffix2 = (date('d', strtotime($spanFrom)) == 1) ? '_1' : '_2';    // 上下旬サフィックス

        // 出力ファイル名
        $outFileName= ('OEM精算書_' . $suffix . $suffix2 . '.zip');

        // TEMP領域作成
        $tmpFilePath = tempnam( sys_get_temp_dir(), 'tmp' );

        // ZIPファイルオープン
        $zip->open( $tmpFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE );

        $unlinkList = array();

        // 1.後払い利用契約
        $filename = $this->_settlementZipFile1($oemId, $spanFrom, $spanTo, $suffix, $tmpFilePath);
        $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
        $addFilePath = file_get_contents( $filename );
        $zip->addFromString( mb_convert_encoding($pathcutfilename, 'sjis-win'), $addFilePath );
        $unlinkList[] = $filename;

        // 2.後払い利用明細
        $summary2 = array('CNT_Row' => 0, 'SUM_UseAmount' => 0, 'SUM_SettlementFee' => 0, 'SUM_ClaimFeeWithoutTax' => 0, 'SUM_ClaimFeeTax' => 0);
        $filename = $this->_settlementZipFile2($tmpFilePath, $summary2);
        $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
        $addFilePath = file_get_contents( $filename );
        $zip->addFromString( mb_convert_encoding($pathcutfilename, 'sjis-win'), $addFilePath );
        $unlinkList[] = $filename;

        // 3.後払い利用明細（印紙代）
        $filename = $this->_settlementZipFile3($oemId, $spanFrom, $spanTo, ($suffix . $suffix2), $tmpFilePath);
        $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
        $addFilePath = file_get_contents( $filename );
        $zip->addFromString( mb_convert_encoding($pathcutfilename, 'sjis-win'), $addFilePath );
        $unlinkList[] = $filename;

        // 4.キャンセル返金明細
        $filename = $this->_settlementZipFile4($oemId, $spanFrom, $spanTo, ($suffix . $suffix2), $tmpFilePath);
        $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
        $addFilePath = file_get_contents( $filename );
        $zip->addFromString( mb_convert_encoding($pathcutfilename, 'sjis-win'), $addFilePath );
        $unlinkList[] = $filename;

        // 5.調整額明細
        $filename = $this->_settlementZipFile5($oemId, $spanFrom, $spanTo, ($suffix . $suffix2), $tmpFilePath);
        $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
        $addFilePath = file_get_contents( $filename );
        $zip->addFromString( mb_convert_encoding($pathcutfilename, 'sjis-win'), $addFilePath );
        $unlinkList[] = $filename;

        // 6.精算までのキャンセル返金明細
        $filename = $this->_settlementZipFile6($oemId, $spanFrom, $spanTo, ($suffix . $suffix2), $tmpFilePath);
        $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
        $addFilePath = file_get_contents( $filename );
        $zip->addFromString( mb_convert_encoding($pathcutfilename, 'sjis-win'), $addFilePath );
        $unlinkList[] = $filename;

        // 7.収納金計算書(PDF)
        $filename = $this->_settlementZipFile7($oemId, $spanFrom, $spanTo, $suffix, $tmpFilePath, $summary2);
        $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
        $addFilePath = file_get_contents( $filename );
        $zip->addFromString( mb_convert_encoding($pathcutfilename, 'sjis-win'), $addFilePath );
        $unlinkList[] = $filename;

        // 8.立替マイナス利用契約
        if (date('d', strtotime($spanFrom)) == 1) {
            $filename = $this->_settlementZipFile8($oemId, $spanFrom, $spanTo, $suffix, $tmpFilePath);
            $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
            $addFilePath = file_get_contents( $filename );
            $zip->addFromString( mb_convert_encoding($pathcutfilename, 'sjis-win'), $addFilePath );
            $unlinkList[] = $filename;
        }

        // ZIPファイルクローズ
        $zip->close();

        // ヘッダ
        header( 'Content-Type: application/octet-stream; name="' . $outFileName . '"' );
        header( 'Content-Disposition: attachment; filename="' . $outFileName . '"' );
        header( 'Content-Length: ' . filesize( $tmpFilePath ) );

        // 出力
        echo readfile( $tmpFilePath );

        // TEMP領域削除
        $unlinkListCount = 0;
        if(!empty($unlinkList)){
            $unlinkListCount = count($unlinkList);
        }
        for ($i=0; $i<$unlinkListCount; $i++) {
            unlink( $unlinkList[$i] );
        }
        unlink( $tmpFilePath );

        return $this->response;
    }

    /**
     * OEM精算データファイル取得【1.後払い利用契約】
     *
     * @param int $oemId OemID
     * @param string $spanFrom 対象期間From
     * @param string $spanTo 対象期間To
     * @param string $suffix ファイル名サフィックス
     * @param string $tmpFilePath TEMP領域
     *
     * @return string ファイル名
     */
    protected function _settlementZipFile1($oemId, $spanFrom, $spanTo, $suffix, $tmpFilePath) {

        $sql = $this->_getBaseSqlSettlementZipFile1();
        $ri = $this->app->dbAdapter->query($sql)->execute(array(':OemId' => $oemId, ':SpanFrom' => date('Y-m-01', strtotime($spanFrom))));
        $datas = ResultInterfaceToArray($ri);

        $templateId = 'COEM030_1';
        $templateClass = 0;
        $seq = 0;
        $templatePattern = 0;
        $tmpFileName = $tmpFilePath . 'ato_riyou_keiyaku_' . $suffix . '.csv';

        $logicTemplate = new LogicTemplate($this->app->dbAdapter);
        $result = $logicTemplate->convertArraytoFile( $datas, $tmpFileName, $templateId, $templateClass, $seq, $templatePattern );

        return $tmpFileName;
    }

    /**
     * (ベースSQLの取得)OEM精算データファイル取得【1.後払い利用契約】
     *
     * @return string SQL文字列
     */
    protected function _getBaseSqlSettlementZipFile1() {
        return <<<EOQ
SELECT DATE_FORMAT(oec.FixedMonth - INTERVAL 1 MONTH, '%Y%m') AS ChargeMonth
,      oec.EnterpriseId
,      (CASE IFNULL((SELECT MAX(aem.AppPlan) FROM AT_EnterpriseMonthlyClosingInfo aem WHERE aem.PayingControlSeq = pc.Seq), oec.AppPlan) WHEN 11 THEN '0' WHEN 21 THEN '1' WHEN 31 THEN '2' WHEN 41 THEN '3' ELSE '' END) AS PlanId
,      DATE_FORMAT(e.ServiceInDate, '%Y%m%d') AS ServiceInDate
,      SUM(apc.OemMonthlyFeeWithoutTax
         + apc.OemIncludeMonthlyFee
         + apc.OemApiMonthlyFee
         + apc.OemCreditNoticeMonthlyFee
         + apc.OemNCreditNoticeMonthlyFee
         + apc.OemReserveMonthlyFee) AS ChargeAmount
,      SUM(apc.OemMonthlyFeeWithoutTax
         + apc.OemIncludeMonthlyFee
         + apc.OemApiMonthlyFee
         + apc.OemCreditNoticeMonthlyFee
         + apc.OemNCreditNoticeMonthlyFee
         + apc.OemReserveMonthlyFee) AS OemMonthlyFee
,      SUM(apc.OemMonthlyFeeTax
         + apc.OemIncludeMonthlyFeeTax
         + apc.OemApiMonthlyFeeTax
         + apc.OemCreditNoticeMonthlyFeeTax
         + apc.OemNCreditNoticeMonthlyFeeTax
         + apc.OemReserveMonthlyFeeTax) AS OemMonthlyFeeTax
FROM   T_OemClaimed oc
       INNER JOIN T_PayingControl pc ON (pc.OemClaimedSeq = oc.OemClaimedSeq)
       INNER JOIN T_OemEnterpriseClaimed oec ON (pc.OemClaimedSeq = oec.OemClaimedSeq AND pc.OemId = oec.OemId AND pc.EnterpriseId = oec.EnterpriseId)
       INNER JOIN T_Enterprise e ON (e.EnterpriseId = oec.EnterpriseId)
       INNER JOIN AT_PayingControl apc ON (apc.Seq = pc.Seq)
WHERE  oec.OemId = :OemId
AND    e.ServiceInDate IS NOT NULL
AND    DATE_FORMAT(e.ServiceInDate, '%Y%m') <> DATE_FORMAT(oec.FixedMonth, '%Y%m')
AND    oec.SpanFrom = :SpanFrom
AND    e.ValidFlg = 1
GROUP BY DATE_FORMAT(oec.FixedMonth - INTERVAL 1 MONTH, '%Y%m')
,      oec.EnterpriseId
HAVING SUM(oec.FixedTransferAmount) >= 0  /* NOTE:精算総額が0以上に限定 */
EOQ;
    }

    /**
     * OEM精算データファイル取得【2.後払い利用明細】
     *
     * @param string $tmpFilePath TEMP領域
     * @param array $summary2 集計結果
     *
     * @return string ファイル名
     */
    protected function _settlementZipFile2($tmpFilePath, &$summary2) {

        // 請求手数料パターン取得
        $mapClaimPtn = array();
        $ri = $this->app->dbAdapter->query(" SELECT KeyCode, Class1, Class2 FROM M_Code WHERE CodeId = 184 ")->execute(null);
        foreach ($ri as $row) {
            $mapClaimPtn[$row['KeyCode']] = array('ClaimFeeWithoutTax' => $row['Class1'], 'ClaimFeeTax' => $row['Class2']);
        }

        /* NOTE : 関数[payingdatadlAction]のコピー */
        //取得範囲取得
        $fixedDate = empty($this->getParams()['fd']) ? -1 : $this->getParams()['fd'];;

        // ファイル名
        $fileName = sprintf("payingdata_%s.csv", date("YmdHis"));

        $search_range = explode("_", $fixedDate);

        // OEMID取得
        $oemId = $this->app->authManagerAdmin->getUserInfo()->OemId;

        $logicots = new LogicOemTradingSettlement($this->app->dbAdapter);

        $settlement_data = $logicots->getOemTradingSettlementCsv($oemId,$search_range[0],$search_range[1]);

        // OEM精算仮締め対象外有無確認
        $sql = "SELECT Class1 FROM M_Code WHERE CodeId = 221 AND KeyCode = :OemId";
        $class1 = $this->app->dbAdapter->query($sql)->execute(array(':OemId'=>$oemId))->current()["Class1"];
        foreach($settlement_data as $value){

            //入金方法を文字に変換
            switch($value['ReceiptClass']){
                //コンビニ
                case 1:
                    $value['ReceiptMethod'] = "コンビニ";
                    break;
                //郵便局
                case 2:
                    $value['ReceiptMethod'] = "郵便局";
                    break;
                //銀行
                case 3:
                    $value['ReceiptMethod'] = "銀行";
                    break;
                //LINE Pay
                case 4:
                    $value['ReceiptMethod'] = "LINE Pay";
                    break;
                //クレジット決済(VISA/MASTER）
                case 5:
                    $value['ReceiptMethod'] = "クレジット決済(VISA/MASTER）";
                    break;
                // PayPay （オンライン決済）
                case 15:
                    $value['ReceiptMethod'] = "PayPay（オンライン決済）";
                    break;
                //クレジット決済(JCB/AMEX）
                case 21:
                    $value['ReceiptMethod'] = "クレジット決済(JCB/AMEX）";
                    break;
                //クレジット決済(Dinars）
                case 22:
                    $value['ReceiptMethod'] = "クレジット決済(Dinars）";
                    break;
                default:
                    $value['ReceiptMethod'] = "";
                    break;

            }

            //キャンセルステータス設定
            switch($value['Cnl_Status']){
                //キャンセル済みではない
                case 0:
                    $value['CancelState'] = 0;
                    break;
                //キャンセル済み もしくはキャンセル申請
                case 1:
                case 2:
                    $value['CancelState'] = 1;
                    break;
            }

            //プラン設定
            switch($value['Plan']){
                //リスクフリー
                case 11:
                    $value['Plan'] = 0;
                    break;
                //スタンダート
                case 21:
                    $value['Plan'] = 1;
                    break;
                //エキスパート
                case 31:
                    $value['Plan'] = 2;
                    break;
                //スペシャル
                case 41:
                    $value['Plan'] = 3;
                    break;
            }

            //差引合計計算
            $value['Total'] = intval($value['UseAmount']) - intval($value['SettlementFee']) - intval($value['ClaimFee']);

            if($class1 == 0) {
                $sql = "SELECT ao.ExtraPayType FROM T_Order o INNER JOIN AT_Order ao ON (ao.OrderSeq = o.OrderSeq) WHERE o.OrderId=:OrderId";
                $extraPayType = $this->app->dbAdapter->query($sql)->execute(array(':OrderId'=>$value['OrderId']))->current()['ExtraPayType'];
                if ((!is_null($extraPayType)) && ($extraPayType == 1)) {
                    continue;
                }
            }

            $datas[] = $value;

            // 集計配列変数更新
            $summary2['CNT_Row'] += 1;
            $summary2['SUM_UseAmount'] += $value['UseAmount'];
            $summary2['SUM_SettlementFee'] += $value['SettlementFee'];
            $summary2['SUM_ClaimFeeWithoutTax'] += nvl($mapClaimPtn[$value['ClaimFee']]['ClaimFeeWithoutTax'],0);
            $summary2['SUM_ClaimFeeTax'] += nvl($mapClaimPtn[$value['ClaimFee']]['ClaimFeeTax'],0);
        }

        $templateId = 'COEM008'; // OEM精算書CSV
        $templateClass = 1;
        $seq = $oemId;
        $templatePattern = 0;
        $tmpFileName = $tmpFilePath . $fileName;

        $logicTemplate = new LogicTemplate( $this->app->dbAdapter );
        $result = $logicTemplate->convertArraytoFile( $datas, $tmpFileName, $templateId, $templateClass, $seq, $templatePattern );

        return $tmpFileName;
    }

    /**
     * OEM精算データファイル取得【3.後払い利用明細（印紙代）】
     *
     * @param int $oemId OemID
     * @param string $spanFrom 対象期間From
     * @param string $spanTo 対象期間To
     * @param string $suffix ファイル名サフィックス
     * @param string $tmpFilePath TEMP領域
     *
     * @return string ファイル名
     */
    protected function _settlementZipFile3($oemId, $spanFrom, $spanTo, $suffix, $tmpFilePath) {

        $sql = $this->_getBaseSqlSettlementZipFile3();
        $ri = $this->app->dbAdapter->query($sql)->execute(array(':OemId' => $oemId, ':SpanFrom' => $spanFrom, ':SpanTo' => $spanTo));
        $datas = ResultInterfaceToArray($ri);

        $templateId = 'COEM030_3';
        $templateClass = 0;
        $seq = 0;
        $templatePattern = 0;
        $tmpFileName = $tmpFilePath . 'ato_riyou_meisai_insi_' . $suffix . '.csv';

        $logicTemplate = new LogicTemplate($this->app->dbAdapter);
        $result = $logicTemplate->convertArraytoFile( $datas, $tmpFileName, $templateId, $templateClass, $seq, $templatePattern );

        return $tmpFileName;
    }

    /**
     * (ベースSQLの取得)OEM精算データファイル取得【3.後払い利用明細（印紙代）】
     *
     * @return string SQL文字列
     */
    protected function _getBaseSqlSettlementZipFile3() {
        return <<<EOQ
SELECT o.EnterpriseId
,      o.SiteId
,      o.OrderId
,      DATE_FORMAT(rc.ReceiptProcessDate, '%Y%m%d') AS ReceiptProcessDate
,      sf.StampFee
,      o.UseAmount
FROM   T_OemClaimed oc
       INNER JOIN T_PayingControl pc ON (pc.OemClaimedSeq = oc.OemClaimedSeq)
       INNER JOIN T_StampFee sf ON (sf.PayingControlSeq = pc.Seq)
       INNER JOIN T_Order o ON (o.OrderSeq = sf.OrderSeq)
       INNER JOIN T_ClaimControl cc ON (cc.OrderSeq = o.OrderSeq)
       INNER JOIN T_ReceiptControl rc ON (rc.ReceiptSeq = cc.LastReceiptSeq)
WHERE  oc.OemId = :OemId
AND    oc.SpanFrom = :SpanFrom
AND    oc.SpanTo = :SpanTo
EOQ;
    }

    /**
     * OEM精算データファイル取得【4.キャンセル返金明細】
     *
     * @param int $oemId OemID
     * @param string $spanFrom 対象期間From
     * @param string $spanTo 対象期間To
     * @param string $suffix ファイル名サフィックス
     * @param string $tmpFilePath TEMP領域
     *
     * @return string ファイル名
     */
    protected function _settlementZipFile4($oemId, $spanFrom, $spanTo, $suffix, $tmpFilePath) {

        $this->app->dbAdapter->query(" SET @row_num = 0 ")->execute(null);  // 内部変数0セット

        $sql = $this->_getBaseSqlSettlementZipFile4();
        $ri = $this->app->dbAdapter->query($sql)->execute(array(':OemId' => $oemId, ':SpanFrom' => $spanFrom, ':SpanTo' => $spanTo));
        $datas = ResultInterfaceToArray($ri);

        $templateId = 'COEM030_4';
        $templateClass = 0;
        $seq = 0;
        $templatePattern = 0;
        $tmpFileName = $tmpFilePath . 'ato_cancel_meisai_' . $suffix . '.csv';

        $logicTemplate = new LogicTemplate($this->app->dbAdapter);
        $result = $logicTemplate->convertArraytoFile( $datas, $tmpFileName, $templateId, $templateClass, $seq, $templatePattern );

        return $tmpFileName;
    }

    /**
     * (ベースSQLの取得)OEM精算データファイル取得【4.キャンセル返金明細】
     *
     * @return string SQL文字列
     */
    protected function _getBaseSqlSettlementZipFile4() {
        return <<<EOQ
SELECT (@row_num := @row_num + 1) AS RowNumber
,      o.OrderId
,      o.Ent_OrderId
,      c.NameKj
,      REPLACE(o.ReceiptOrderDate,'-','/') AS OrderDate
,      REPLACE(pc2.FixedDate,'-','/') AS FixedDate
,      REPLACE(DATE(cncl.ApprovalDate),'-','/') AS ApprovalDate
,      o.UseAmount AS ClaimAmount
,      (o.UseAmount - osf.SettlementFee - ocf.ClaimFee) AS RepayTotal
FROM   T_OemClaimed oc
       INNER JOIN T_PayingControl pc ON (pc.OemClaimedSeq = oc.OemClaimedSeq)
       INNER JOIN T_Cancel cncl ON (cncl.PayingControlSeq = pc.Seq)
       INNER JOIN T_Order o ON (o.OrderSeq = cncl.OrderSeq)
       INNER JOIN T_OemSettlementFee osf ON (osf.OrderSeq = o.OrderSeq)
       INNER JOIN T_OemClaimFee ocf ON (ocf.OrderSeq = o.OrderSeq)
       INNER JOIN T_Customer c ON (c.OrderSeq = o.OrderSeq)
       INNER JOIN T_ClaimControl cc ON (cc.OrderSeq = o.OrderSeq)
       LEFT OUTER JOIN T_PayingAndSales pas ON (pas.OrderSeq = cc.OrderSeq)
       LEFT OUTER JOIN T_PayingControl pc2 ON (pc2.Seq = pas.PayingControlSeq)
WHERE  oc.OemId = :OemId
AND    oc.SpanFrom = :SpanFrom
AND    oc.SpanTo = :SpanTo
AND    cncl.CancelPhase IN (2,3)    /* NOTE:立替実行後、に限定 */
EOQ;
    }

    /**
     * OEM精算データファイル取得【5.調整額明細】
     *
     * @param int $oemId OemID
     * @param string $spanFrom 対象期間From
     * @param string $spanTo 対象期間To
     * @param string $suffix ファイル名サフィックス
     * @param string $tmpFilePath TEMP領域
     *
     * @return string ファイル名
     */
    protected function _settlementZipFile5($oemId, $spanFrom, $spanTo, $suffix, $tmpFilePath) {

        $sql = $this->_getBaseSqlSettlementZipFile5();
        $ri = $this->app->dbAdapter->query($sql)->execute(array(':OemId' => $oemId, ':SpanFrom' => $spanFrom, ':SpanTo' => $spanTo));
        $datas = ResultInterfaceToArray($ri);

        $templateId = 'COEM030_5';
        $templateClass = 0;
        $seq = 0;
        $templatePattern = 0;
        $tmpFileName = $tmpFilePath . 'ato_chousei_meisai_' . $suffix . '.csv';

        $logicTemplate = new LogicTemplate($this->app->dbAdapter);
        $result = $logicTemplate->convertArraytoFile( $datas, $tmpFileName, $templateId, $templateClass, $seq, $templatePattern );

        return $tmpFileName;
    }

    /**
     * (ベースSQLの取得)OEM精算データファイル取得【5.調整額明細】
     *
     * @return string SQL文字列
     */
    protected function _getBaseSqlSettlementZipFile5() {
        return <<<EOQ
SELECT e.EnterpriseNameKj
,      o.OrderId
,      aa.AdjustmentAmount
,      (SELECT KeyContent FROM M_Code WHERE CodeId = 89 AND KeyCode = aa.ItemCode) AS Kamoku
FROM   T_OemClaimed oc
       INNER JOIN T_PayingControl pc ON (pc.OemClaimedSeq = oc.OemClaimedSeq)
       INNER JOIN T_AdjustmentAmount aa ON (aa.PayingControlSeq = pc.Seq)
       LEFT OUTER JOIN T_Order o ON (o.OrderSeq = aa.OrderSeq)
       INNER JOIN T_Enterprise e ON (e.EnterpriseId = pc.EnterpriseId)
WHERE  oc.OemId = :OemId
AND    oc.SpanFrom = :SpanFrom
AND    oc.SpanTo = :SpanTo
EOQ;
    }

    /**
     * OEM精算データファイル取得【6.精算までのキャンセル返金明細】
     *
     * @param int $oemId OemID
     * @param string $spanFrom 対象期間From
     * @param string $spanTo 対象期間To
     * @param string $suffix ファイル名サフィックス
     * @param string $tmpFilePath TEMP領域
     *
     * @return string ファイル名
     */
    protected function _settlementZipFile6($oemId, $spanFrom, $spanTo, $suffix, $tmpFilePath) {

        // T_Cancel.ApprovalDateの有効範囲取得(ここから)
        $sql = <<<EOQ
SELECT MAX(pc.DecisionDate) AS MAX_DecisionDate
,      MIN(pc.FixedDate) AS MIN_FixedDate
FROM   T_OemClaimed oc
	   INNER JOIN T_PayingControl pc ON (oc.OemClaimedSeq = pc.OemClaimedSeq)
WHERE  oc.OemId = :OemId
AND    oc.SpanFrom = :SpanFrom
AND    oc.SpanTo = :SpanTo
EOQ;
        $row = $this->app->dbAdapter->query($sql)->execute(array(':OemId' => $oemId, ':SpanFrom' => $spanFrom, ':SpanTo' => $spanTo))->current();
        // T_Cancel.ApprovalDateの有効範囲取得(ここまで)

        $sql = $this->_getBaseSqlSettlementZipFile6();
        $ri  = $this->app->dbAdapter->query($sql)->execute(
            array(':OemId' => $oemId, ':SpanFrom' => $spanFrom, ':SpanTo' => $spanTo, ':ApprovalDateFrom' => $row['MIN_FixedDate'], ':ApprovalDateTo' => $row['MAX_DecisionDate']));
        $datas = ResultInterfaceToArray($ri);

        $templateId = 'COEM030_6';
        $templateClass = 0;
        $seq = 0;
        $templatePattern = 0;
        $tmpFileName = $tmpFilePath . '精算までのキャンセル_' . $suffix . '.csv';

        $logicTemplate = new LogicTemplate($this->app->dbAdapter);
        $result = $logicTemplate->convertArraytoFile( $datas, $tmpFileName, $templateId, $templateClass, $seq, $templatePattern );

        return $tmpFileName;
    }

    /**
     * (ベースSQLの取得)OEM精算データファイル取得【6.精算までのキャンセル返金明細】
     *
     * @return string SQL文字列
     */
    protected function _getBaseSqlSettlementZipFile6() {
        return <<<EOQ
SELECT DISTINCT DATE_FORMAT(o.ReceiptOrderDate,'%Y/%m/%d') AS OrderDate
,      o.OrderId
,      o.Ent_OrderId
,      c.NameKj
,      o.UseAmount
,      osf.SettlementFee
,      ocf.ClaimFee
,      (o.UseAmount - osf.SettlementFee - ocf.ClaimFee) AS DiffSum
,      e.EnterpriseNameKj
,      sit.SiteNameKj
,      o.SiteId
,      (CASE rc.ReceiptClass WHEN 1 THEN 'コンビニ' WHEN 2 THEN '郵便局' WHEN 3 THEN '銀行' WHEN 4 THEN 'LINE Pay' ELSE '' END) AS ReceiptClass
,      e.EnterpriseId
,      (CASE oec.AppPlan WHEN 11 THEN '0' WHEN 21 THEN '1' WHEN 31 THEN '2' WHEN 41 THEN '3' ELSE '' END) AS PlanId
,      osf.AppSettlementFeeRate
FROM   T_OemClaimed oc
       INNER JOIN T_PayingControl pc ON (oc.OemClaimedSeq = pc.OemClaimedSeq)
       INNER JOIN T_OemEnterpriseClaimed oec ON (pc.OemClaimedSeq = oec.OemClaimedSeq AND pc.OemId = oec.OemId AND pc.EnterpriseId = oec.EnterpriseId)
	   INNER JOIN T_Order o ON (pc.EnterpriseId = o.EnterpriseId)
       INNER JOIN T_Customer c ON (c.OrderSeq = o.OrderSeq)
       INNER JOIN T_Enterprise e ON (e.EnterpriseId = o.EnterpriseId)
       INNER JOIN T_Site sit ON (sit.SiteId = o.SiteId)
       LEFT OUTER JOIN T_ClaimControl cc ON (cc.OrderSeq = o.P_OrderSeq)
       LEFT OUTER JOIN T_ReceiptControl rc ON (rc.ReceiptSeq = cc.LastReceiptSeq)
	   INNER JOIN T_PayingAndSales pas ON (o.OrderSeq = pas.OrderSeq)
       STRAIGHT_JOIN T_OemSettlementFee osf ON (osf.OrderSeq = pas.OrderSeq)
       STRAIGHT_JOIN T_OemClaimFee ocf ON (ocf.OrderSeq = osf.OrderSeq)
       INNER JOIN (SELECT * FROM T_Cancel WHERE CancelDate > :ApprovalDateFrom AND CancelDate < :ApprovalDateTo) can ON (pas.OrderSeq = can.OrderSeq)
WHERE  DATE(pas.ClearConditionDate) <= pc.FixedDate
AND    DATE(pas.ClearConditionDate) > IFNULL((SELECT MAX(FixedDate) FROM T_PayingControl WHERE Seq < pc.Seq AND EnterpriseId = pc.EnterpriseId), '1970-01-01')
AND    (pc.FixedDate < DATE(can.CancelDate) AND can.CancelDate < pc.DecisionDate)
AND    oc.OemId = :OemId
AND    oc.SpanFrom = :SpanFrom
AND    oc.SpanTo = :SpanTo
EOQ;
    }

    /**
     * OEM精算データファイル取得【7.収納金計算書(PDF)】
     *
     * @param int $oemId OemID
     * @param string $spanFrom 対象期間From
     * @param string $spanTo 対象期間To
     * @param string $suffix ファイル名サフィックス ※NOTE:本関数では内部で置換が発生する
     * @param string $tmpFilePath TEMP領域
     * @param array $summary2 [2.後払い利用明細]の集計結果
     *
     * @return string ファイル名
     */
    protected function _settlementZipFile7($oemId, $spanFrom, $spanTo, $suffix, $tmpFilePath, $summary2) {

        // ファイル名サフィックス(OEM締め処理日)
        $suffix = $this->app->dbAdapter->query(" SELECT DATE_FORMAT(ProcessDate,'%Y%m%d') AS ProcessDate FROM T_OemClaimed WHERE OemId = :OemId AND SpanFrom = :SpanFrom AND SpanTo = :SpanTo "
        )->execute(array(':OemId' => $oemId, ':SpanFrom' => $spanFrom, ':SpanTo' => $spanTo))->current()['ProcessDate'];

        $fileName  = ('収納金計算書' . $suffix . '.pdf');
        $tmpFileName = $tmpFilePath . $fileName;

        $this->setTemplate('keisansyo');
        $this->view->assign( 'documentRoot', $_SERVER['DOCUMENT_ROOT'] );
        $this->view->assign( 'title', $fileName );

        $fileName  = ('SyunokinCalc' . $suffix . '.pdf');

        $sql = <<<EOQ
SELECT oc.*
FROM   T_OemClaimed oc
WHERE  oc.OemId = :OemId
AND    oc.SpanFrom = :SpanFrom
AND    oc.SpanTo = :SpanTo
EOQ;
        $row_oemclaimed = $this->app->dbAdapter->query($sql)->execute(array(':OemId' => $oemId, ':SpanFrom' => $spanFrom, ':SpanTo' => $spanTo))->current();

        // 支払日再計算(精算予定日起算)
        $row_bc = $this->app->dbAdapter->query(" SELECT MIN(BusinessDate) AS SettlePlanDate FROM T_BusinessCalendar WHERE BusinessDate >= :BusinessDate AND BusinessFlg = 1 ")->execute(
            array(':BusinessDate' => $row_oemclaimed['SettlePlanDate']))->current();
        $row_oemclaimed['SettlePlanDate'] = $row_bc['SettlePlanDate'];

        $row_oem = $this->app->dbAdapter->query( " SELECT * FROM T_Oem WHERE OemId = :OemId ")->execute(array(':OemId' => $oemId))->current();

        $this->view->assign('row_oemclaimed', $row_oemclaimed);
        $this->view->assign('row_oem', $row_oem);

        // 帳票出力値は基本抽出結果をベースとする
        // (1)
        $sql = " SELECT COUNT(1) AS CNT_Row, SUM(tmp.ChargeAmount) AS SUM_ChargeAmount, SUM(tmp.OemMonthlyFee) AS SUM_OemMonthlyFee, SUM(tmp.OemMonthlyFeeTax) AS SUM_OemMonthlyFeeTax FROM (" . $this->_getBaseSqlSettlementZipFile1() . " ) tmp WHERE tmp.ChargeAmount > 0 ";
        $row_oemclaimed_sub1 = $this->app->dbAdapter->query($sql)->execute(array(':OemId' => $oemId, ':SpanFrom' => date('Y-m-01', strtotime($spanFrom))))->current();
        if (date('d', strtotime($spanFrom)) != 1) {
            $row_oemclaimed_sub1['CNT_Row']             = 0;
            $row_oemclaimed_sub1['SUM_ChargeAmount']    = 0;
            $row_oemclaimed_sub1['SUM_OemMonthlyFee']   = 0;
            $row_oemclaimed_sub1['SUM_OemMonthlyFeeTax']= 0;
        }
        $this->view->assign('row_oemclaimed_sub1', $row_oemclaimed_sub1);
        // (2)
        $this->view->assign('row_oemclaimed_sub2', $summary2);
        // (3)
        $sql = " SELECT COUNT(1) AS CNT_Row, SUM(tmp.StampFee) AS SUM_StampFee FROM (" . $this->_getBaseSqlSettlementZipFile3() . " ) tmp ";
        $row_oemclaimed_sub3 = $this->app->dbAdapter->query($sql)->execute(array(':OemId' => $oemId, ':SpanFrom' => $spanFrom, ':SpanTo' => $spanTo))->current();
        $this->view->assign('row_oemclaimed_sub3', $row_oemclaimed_sub3);
        // (4)
        $sql = " SELECT COUNT(1) AS CNT_Row, SUM(tmp.RepayTotal) * -1 AS SUM_RepayTotal FROM (" . $this->_getBaseSqlSettlementZipFile4() . " ) tmp ";
        $row_oemclaimed_sub4 = $this->app->dbAdapter->query($sql)->execute(array(':OemId' => $oemId, ':SpanFrom' => $spanFrom, ':SpanTo' => $spanTo))->current();
        $this->view->assign('row_oemclaimed_sub4', $row_oemclaimed_sub4);
        // (5)
        $sql = " SELECT COUNT(1) AS CNT_Row, SUM(tmp.AdjustmentAmount) AS SUM_AdjustmentAmount FROM (" . $this->_getBaseSqlSettlementZipFile5() . " ) tmp ";
        $row_oemclaimed_sub5 = $this->app->dbAdapter->query($sql)->execute(array(':OemId' => $oemId, ':SpanFrom' => $spanFrom, ':SpanTo' => $spanTo))->current();
        $this->view->assign('row_oemclaimed_sub5', $row_oemclaimed_sub5);
        // (8)
        $sql = " SELECT COUNT(1) AS CNT_Row, SUM(tmp.ChargeAmount) AS SUM_ChargeAmount, SUM(tmp.OemMonthlyFee) + SUM(tmp.OemMonthlyFeeTax) AS SUM_OemMonthlyFee, SUM(tmp.OemMonthlyFeeTax) AS SUM_OemMonthlyFeeTax FROM (" . $this->_getBaseSqlSettlementZipFile8() . " ) tmp WHERE tmp.ChargeAmount > 0 ";
        $row_oemclaimed_sub8 = $this->app->dbAdapter->query($sql)->execute(array(':OemId' => $oemId, ':SpanFrom' => (date('d', strtotime($spanFrom)) == 1 ? $spanFrom : '9999-99-99')))->current();
        $this->view->assign('row_oemclaimed_sub8', $row_oemclaimed_sub8);

        $codeMaster = new \Coral\Coral\CoralCodeMaster($this->app->dbAdapter);
        $masters = array(
            'FfAccountClass' => $codeMaster->getAccountClassMaster(),
        );
        $this->view->assign('masters', $masters);

        // HTML作成
        $viewRender = $this->getServiceLocator()->get('ViewRenderer');
        $html = $viewRender->render($this->view);

        // 一時ファイルの保存先
        $mdlsp = new \models\Table\TableSystemProperty($this->app->dbAdapter);
        $tempDir = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'TempFileDir');
        $tempDir = realpath($tempDir);

        // 中間ファイル名
        $fname_html = ($tempDir . '/__tmp_' . $fileName . '__.html');
        $fname_pdf  = ($tempDir . '/__tmp_' . $fileName . '__.pdf');

        // HTML出力
        file_put_contents($fname_html, $html);

        // PDF変換(外部プログラム起動)
        $ename = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'wkhtmltopdf');
        $option = " --page-size A4 --orientation landscape --margin-left 0 --margin-right 0 --margin-top 0 --margin-bottom 0 ";
        exec($ename . $option . $fname_html . ' ' . $fname_pdf);

        // ファイルの読み込み
        $pdf = file_get_contents($fname_pdf);

        // ファイルに保存
        file_put_contents($tmpFileName, $pdf);

        unlink( $fname_html );
        unlink( $fname_pdf );

        return $tmpFileName;
    }

    /**
     * OEM精算データファイル取得【8.立替マイナス利用契約】
     *
     * @param int $oemId OemID
     * @param string $spanFrom 対象期間From
     * @param string $spanTo 対象期間To
     * @param string $suffix ファイル名サフィックス
     * @param string $tmpFilePath TEMP領域
     *
     * @return string ファイル名
     */
    protected function _settlementZipFile8($oemId, $spanFrom, $spanTo, $suffix, $tmpFilePath) {

        $sql = $this->_getBaseSqlSettlementZipFile8();
        $ri = $this->app->dbAdapter->query($sql)->execute(array(':OemId' => $oemId, ':SpanFrom' => (date('d', strtotime($spanFrom)) == 1 ? $spanFrom : '9999-99-99')));
        $datas = ResultInterfaceToArray($ri);

        $templateId = 'COEM030_1';  // テンプレートは【1.後払い利用契約】に同じ
        $templateClass = 0;
        $seq = 0;
        $templatePattern = 0;
        $tmpFileName = $tmpFilePath . '立替マイナス_利用契約_' . $suffix . '.csv';

        $logicTemplate = new LogicTemplate($this->app->dbAdapter);
        $result = $logicTemplate->convertArraytoFile( $datas, $tmpFileName, $templateId, $templateClass, $seq, $templatePattern );

        return $tmpFileName;
    }

    /**
     * (ベースSQLの取得)OEM精算データファイル取得【8.立替マイナス利用契約】
     *
     * @return string SQL文字列
     */
    protected function _getBaseSqlSettlementZipFile8() {
        return <<<EOQ
SELECT DATE_FORMAT(oec.FixedMonth - INTERVAL 1 MONTH, '%Y%m') AS ChargeMonth
,      oec.EnterpriseId
,      (CASE IFNULL((SELECT MAX(aem.AppPlan) FROM AT_EnterpriseMonthlyClosingInfo aem WHERE aem.PayingControlSeq = pc.Seq), oec.AppPlan) WHEN 11 THEN '0' WHEN 21 THEN '1' WHEN 31 THEN '2' WHEN 41 THEN '3' ELSE '' END) AS PlanId
,      DATE_FORMAT(e.ServiceInDate, '%Y%m%d') AS ServiceInDate
,      SUM(apc.OemMonthlyFeeWithoutTax
         + apc.OemIncludeMonthlyFee
         + apc.OemApiMonthlyFee
         + apc.OemCreditNoticeMonthlyFee
         + apc.OemNCreditNoticeMonthlyFee
         + apc.OemReserveMonthlyFee) AS ChargeAmount
,      SUM(apc.OemMonthlyFeeWithoutTax
         + apc.OemIncludeMonthlyFee
         + apc.OemApiMonthlyFee
         + apc.OemCreditNoticeMonthlyFee
         + apc.OemNCreditNoticeMonthlyFee
         + apc.OemReserveMonthlyFee) AS OemMonthlyFee
,      SUM(apc.OemMonthlyFeeTax
         + apc.OemIncludeMonthlyFeeTax
         + apc.OemApiMonthlyFeeTax
         + apc.OemCreditNoticeMonthlyFeeTax
         + apc.OemNCreditNoticeMonthlyFeeTax
         + apc.OemReserveMonthlyFeeTax) AS OemMonthlyFeeTax
FROM   T_OemClaimed oc
       INNER JOIN T_PayingControl pc ON (pc.OemClaimedSeq = oc.OemClaimedSeq)
       INNER JOIN T_OemEnterpriseClaimed oec ON (pc.OemClaimedSeq = oec.OemClaimedSeq AND pc.OemId = oec.OemId AND pc.EnterpriseId = oec.EnterpriseId)
       INNER JOIN T_Enterprise e ON (e.EnterpriseId = oec.EnterpriseId)
       INNER JOIN AT_PayingControl apc ON (apc.Seq = pc.Seq)
WHERE  oec.OemId = :OemId
AND    e.ServiceInDate IS NOT NULL
AND    DATE_FORMAT(e.ServiceInDate, '%Y%m') <> DATE_FORMAT(oec.FixedMonth, '%Y%m')
AND    oec.SpanFrom = :SpanFrom
AND    e.ValidFlg = 1
GROUP BY DATE_FORMAT(oec.FixedMonth - INTERVAL 1 MONTH, '%Y%m')
,      oec.EnterpriseId
HAVING SUM(oec.FixedTransferAmount) < 0  /* NOTE:精算総額が0未満に限定 */
AND    SUM(apc.OemMonthlyFeeWithoutTax
         + apc.OemIncludeMonthlyFee
         + apc.OemApiMonthlyFee
         + apc.OemCreditNoticeMonthlyFee
         + apc.OemNCreditNoticeMonthlyFee
         + apc.OemReserveMonthlyFee) > 0
EOQ;
    }
}

