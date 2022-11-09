<?php
namespace cbadmin\Controller;

use models\Table\TableTemplateField;
use Zend\Config\Reader\Ini;
use Zend\Db\ResultSet\ResultSet;
use Zend\Json\Json;
use Zend\Session\Container;
use Coral\Base\BaseHtmlUtils;
use Coral\Base\BaseGeneralUtils;
use Coral\Base\BaseUtility;
use Coral\Base\IO\BaseIOCsvWriter;
use Coral\Base\IO\BaseIOUtility;
use Coral\Coral\Controller\CoralControllerAction;
use Coral\Coral\CoralCodeMaster;
use Coral\Coral\CoralPager;
use cbadmin\Application;
use models\Table\TableAgency;
use models\Table\TableAgencySite;
use models\Table\TableApiUser;
use models\Table\TableCreditPoint;
use models\Table\TableCode;
use models\Table\TableDeliMethod;
use models\Table\TableEnterprise;
use models\Table\TableEnterpriseCampaign;
use models\Table\TableEnterpriseDelivMethod;
use models\Table\TableOem;
use models\Table\TableOrder;
use models\Table\TablePayingCycle;
use models\Table\TablePricePlan;
use models\Table\TableSite;
use models\Table\TableSiteFreeItems;
use models\Table\TableUser;
use models\Table\TableCvsReceiptAgent;
use models\Table\TableSubscriberCode;
use models\Table\TablePayment;
use models\Table\TableSitePayment;
use models\Logic\LogicClaimPrint;
use models\Table\TableClaimPrintPattern;
use models\Table\TableSiteSbpsPayment;
use models\Logic\LogicTemplate;
use models\Table\TableSbpsPayment;
use models\View\MypageViewCode;
use models\Logic\LogicSbps;
use DateTime;

class SiteController extends CoralControllerAction
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

        $this->addStyleSheet('../css/default02.css');
        $this->addJavaScript('../js/prototype.js');
        $this->addJavaScript('../js/json.js');

        $this->setPageTitle("後払い.com - 事業者サイト管理");

        // コードマスターから事業者情報向けのマスター連想配列を作成し、ビューへアサインしておく
        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
        $masters = array(
                'Prefecture' => $codeMaster->getPrefectureMaster(),
                'PreSales' => $codeMaster->getPreSalesMaster(),
                'Industry' => $codeMaster->getIndustryMaster(),
                'Plan' => $codeMaster->getPlanMaster(),
                'FixPattern' => $codeMaster->getFixPatternMaster(),
                'LimitDay' => $codeMaster->getLimitDayMaster(),
                'LimitDatePattern' => $codeMaster->getLimitDatePatternMaster(),
                'FfAccountClass' => $codeMaster->getAccountClassMaster(),
                'TcClass' => $codeMaster->getTcClassMaster(),
                'SiteForm' => $codeMaster->getSiteFormMaster(),
                'DocCollect' => $codeMaster->getDocCollectMaster(),
                'ExaminationResult' => $codeMaster->getExaminationResultMaster(),
                'AutoCreditJudgeMode' => $codeMaster->getAutoCreditJudgeModeMaster(),
                'CjMailMode' => $codeMaster->getCjMailModeMaster(),
                'CombinedClaimMode' => $codeMaster->getCombinedClaimMode(),
                'AutoClaimStopFlg' => $codeMaster->masterToArray($codeMaster->getAutoClaimStopFlgMaster()),
                'PrintEntOrderIdOnClaimFlg' => $codeMaster->masterToArray($codeMaster->getPrintEntOrderIdOnClaimFlgMaster()),

                'PayingCycleId' => $codeMaster->getPayingCycleMaster(),
                'ClaimClass' => array(0 => '都度請求', 1 => '次回繰越'),
                'TaxClass' => array(0 => '内税', 1 => '外税'),
                'JudgeSystemFlg' => array(0 => '行わない', 1 => '行う'),
                'AutoJudgeFlg' => array(0 => '行わない', 1 => '行う'),
                'JintecFlg' => array(0 => '行わない', 1 => '行う'),
                'ManualJudgeFlg' => array(0 => '行わない', 1 => '行う'),
                'CsvRegistClass' => array(0 => 'すべてOKで登録', 1 => 'エラーがあってもOK分のみ登録'),
                'CsvRegistErrorClass' => array(0 => 'エラー分の修正登録を行わない', 1 => 'エラー分の修正登録を行う'),
                'ReceiptStatusSearchClass' => array(0 => '入金ステータス検索不可', 1 => '入金ステータス検索可'),
                'PayBackFlg' => array(0 => '行わない', 1 => '行う'),
                'AutoNoGuaranteeFlg' => array(0 => '自動無保証しない', 1 => '自動無保証する'),
                'DispDecimalPoint' => array(0 => '0', 1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5'),
                'UseAmountFractionClass' => array(0 => '切り捨て', 1 => '四捨五入', 2 => '切り上げ'),
                'CombinedClaimChargeFeeFlg' => array(0 => '全注文に対して店舗手数料を取る', 1 => '代表注文のみ店舗手数料を取る'),
                'JintecManualReqFlg' => array(0 => '強制しない', 1 => '強制する'),
        );

        $configs = $this->app->config['cj_api']; // ←移植が失敗していたので、cj_apiを追記

        $this->view->assign('master_map', $masters);

        // 消費税レート
        $obj = new \models\Table\TableSystemProperty($this->app->dbAdapter);
        $zeiRate = $obj->getTaxRateAt(date('Y-m-d'));
        $zeiRate = 1 + ($zeiRate / 100);
        $this->view->assign('zei_rate', $zeiRate);
    }

    /**
     * サイト一覧を表示
     */
    public function listAction()
    {
        $enterpriseId = $this->params()->fromRoute("eid", -1);

        // set url before going to page setting
        $_SESSION['BEFORE_SETTING_TODO'] = 'site/list/eid/'.$enterpriseId;

        $mdlSite = new TableSite($this->app->dbAdapter);
        $mdlEnterprise = new TableEnterprise($this->app->dbAdapter);

        // 事業者名の取得・アサイン
        $enterpriseData = $mdlEnterprise->findEnterprise($enterpriseId)->current();
        $this->view->assign("EnterpriseNameKj", $enterpriseData['EnterpriseNameKj']);

        // サイトデータの取得
        $datas = array();
        $datas = $mdlSite->getAll($enterpriseId);
        $datas = ResultInterfaceToArray($datas);

        // check whether show link Todo Regist
        $logicSbps = new LogicSbps($this->app->dbAdapter);
        $checkTodo = $logicSbps->checkSettingTodo($enterpriseId);

        // サイトに紐づく代理店の件数を取得
        foreach ($datas as $value) {
            $sql = " SELECT COUNT(AgencyId) AS cnt FROM M_AgencySite WHERE SiteId = :SiteId GROUP BY SiteId ";
            $cnt[$value['SiteId']] = $this->app->dbAdapter->query($sql)->execute( array(':SiteId' => $value['SiteId']) )->current();
        }

        // count関数対策
        $datasLen = 0;
        if(!empty($datas)) {
            $datasLen = count($datas);
        }

        for ($i = 0 ; $i < $datasLen ; $i++) {
            // サイトに紐づくAPIユーザ取得
            $apisql = <<<EOQ
            SELECT  au.ApiUserNameKj
                ,   au.ApiUserNameKn
            FROM    T_ApiUser au
                    INNER JOIN T_ApiUserEnterprise ae ON (au.ApiUserId = ae.ApiUserId)
                    INNER JOIN T_Site s ON (ae.SiteId = s.SiteId)
            WHERE   s.SiteId = :SiteId
            ORDER BY au.ApiUserId
            LIMIT 1
            ;
EOQ;
            $apidata[$i] = $this->app->dbAdapter->query($apisql)->execute(array( ':SiteId' => $datas[$i]['SiteId'] ))->current();
            $cntsql = <<<EOQ
            SELECT COUNT(au.ApiUserId) AS cnt
            FROM T_ApiUser au
                 INNER JOIN T_ApiUserEnterprise ae ON (au.ApiUserId = ae.ApiUserId)
                 INNER JOIN T_Site s ON (ae.SiteId = s.SiteId)
            WHERE   s.SiteId = :SiteId ;
EOQ;
            $apicnt[$i] = $this->app->dbAdapter->query($cntsql)->execute(array( ':SiteId' => $datas[$i]['SiteId'] ))->current();

        }
        $this->view->assign('list', $datas);
        $this->view->assign('cnt', $cnt);
        $this->view->assign('checkTodo', $checkTodo);
        $this->view->assign('EnterpriseId', $enterpriseId);
        $this->view->assign('OemId', $enterpriseData['OemId']);
        $this->view->assign('apilist', $apidata);
        $this->view->assign('apicnt', $apicnt);

        return $this->view;
    }

    /**
     * サイト登録
     */
    public function registAction()
    {
        $params = $this->getParams();

//        $mdlSite = new TableSite($this->app->dbAdapter);
        $mdlEnterprise = new TableEnterprise($this->app->dbAdapter);
        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
        $mdlPricePlan = new TablePricePlan($this->app->dbAdapter);
//        $mdlEntDel = new TableEnterpriseDelivMethod($this->app->dbAdapter);
//        $mdlAgency = new TableAgency($this->app->dbAdapter);
//        $mdlCrdtPnt = new TableCreditPoint($this->app->dbAdapter);
//        $mdlDelMthd = new TableDeliMethod($this->app->dbAdapter);
        $mdlCode = new TableCode($this->app->dbAdapter);
        $mdlOem = new TableOem($this->app->dbAdapter);
        $mdlCvs = new TableCvsReceiptAgent($this->app->dbAdapter);
        $mdlPayment = new TablePayment($this->app->dbAdapter);

        //加盟店ID
        $enterpriseId = $params['eid'];

        // set url before going to page setting
        $_SESSION['BEFORE_SETTING_TODO'] = 'site/regist/eid/'.$enterpriseId;

        // 事業者名の取得
        $enterpriseData = $mdlEnterprise->findEnterprise($enterpriseId)->current();

        //口座振替利用か確認
        $datas['CreditTransferFlg'] = $enterpriseData['CreditTransferFlg'];

        //加盟店OEM先
        $datas['OemId'] = $enterpriseData['OemId'];

        //サイト形態
        $datas['SiteForms'] = $codeMaster->getSiteFormMaster();

        //利用プラン取得
        $pricePlan = $mdlPricePlan->find($enterpriseData['Plan'])->current();
        $datas['PlanName'] = $pricePlan['PricePlanName'];
        //OEM情報取得
        $oem = $mdlOem->find($enterpriseData['OemId'])->current();
        $oemData = array();
        if ($oem) {
            $oemData['ClaimFeeBS'] = $oem['ClaimFeeBS'];
            $oemData['ClaimFeeDK'] = $oem['ClaimFeeDK'];
            $feelist = Json::decode($oem['SettlementFeeRatePlan'], Json::TYPE_ARRAY);
            $oemData['SettlementFeeRate'] = $feelist[$enterpriseData['Plan']];
            $oemData['KisanbiDelayDays'] = $oem['KisanbiDelayDays'];
            $oemData['FirstCreditTransferClaimFeeOem'] = $oem['FirstCreditTransferClaimFeeOem'];
            $oemData['FirstCreditTransferClaimFeeWebOem'] = $oem['FirstCreditTransferClaimFeeWebOem'];
            $oemData['CreditTransferClaimFeeOem'] = $oem['CreditTransferClaimFeeOem'];
        }
        //金額情報
        $reference = array();
        $reference['SettlementAmountLimit'] = $pricePlan['SettlementAmountLimit'];
        $reference['SettlementFeeRate'] = $pricePlan['SettlementFeeRate'];
        $reference['ClaimFeeBS'] = $pricePlan['ClaimFeeBS'];
        $reference['ClaimFeeDK'] = $pricePlan['ClaimFeeDK'];
        $reference['ReClaimFee']  = $pricePlan['ReClaimFee'];
        $reference['OemClaimFeeBS'] = $oemData['ClaimFeeBS'];
        $reference['OemClaimFeeDK'] = $oemData['ClaimFeeDK'];
        $reference['OemSettlementFeeRate'] = $oemData['SettlementFeeRate'];
        $reference['OemKisanbiDelayDays'] = $oemData['KisanbiDelayDays'];
        $reference['FirstCreditTransferClaimFeeOem'] = $oemData['FirstCreditTransferClaimFeeOem'];
        $reference['FirstCreditTransferClaimFeeWebOem'] = $oemData['FirstCreditTransferClaimFeeWebOem'];
        $reference['CreditTransferClaimFeeOem'] = $oemData['CreditTransferClaimFeeOem'];
        $datas['Reference'] = $reference;

        //与信判定基準取得
        $sql  = " SELECT DISTINCT CreditCriterionId, CreditCriterionName  FROM M_CreditPoint ";
        $ri = $this->app->dbAdapter->query($sql)->execute(null);
        $datas['CreditCriterionNames'] = ResultInterfaceToArray($ri);

        //与信判定方法
        $data=array();
        foreach ($mdlCode->getMasterByClass(94) as $row){
            $data[] = $row['KeyContent'];
        }
        $datas['Creditdecision'] = $data;

        //自動伝票番号登録時配送先取得
        $lgc = new \models\Logic\LogicDeliveryMethod($this->app->dbAdapter);
        $datas['DeliMethodName'] = $lgc->getEnterpriseDeliveryMethodList($enterpriseId, false);

        //請求書用紙種類（別送）取得
        $data=array();
        foreach ($mdlCode->getMasterByClass(79) as $row){
            $data[$row['KeyCode']] = $row['KeyContent'];
        }
        $datas['Invoice'] = $data;

        //請求書用紙種類（同梱）取得
        $data=array();
        foreach ($mdlCode->getMasterByClass(106) as $row){
            $data[$row['KeyCode']] = $row['KeyContent'];
        }
        $datas['InvoiceDK'] = $data;

         // CB_B2C_DEV-14
        $datas['ReceiptAgentForms'] = $mdlCvs->getEnterpriseCvsReceiptAgentList($enterpriseId,false);

        $firstClaimLayoutModeCtl = $mdlCode->find(160, $enterpriseData['OemId'])->current()['Class7'];

       // 初期値設定
        $datas['ValidFlg'] = 1;                 // 有効設定
        $datas['ServiceTargetClass'] = 0;       // 役務設定
        $datas['T_OrderClass'] = 0;             // テスト注文可能
        $datas['YuchoMT'] = 1;                  // 郵貯MT
        $datas['AutoJournalIncMode'] = 0;       // 伝票番号不要
        $datas['AutoClaimStopFlg'] = 0;         // 請求自動ストップ
        $datas['SelfBillingFlg'] = 0;           // 請求書同梱
        $datas['SelfBillingFixFlg'] = 0;        // 注文強制同梱化
        $datas['SitClass'] = 0;                 // サイト区分
        $dates['RemindStopClass'] = 0;          //督促停止区分
        $datas['PayingBackFlg'] = 0;            // 立替精算戻し
        $datas['CreaditStartMail'] = 0;         // 与信開始メール
        $datas['CreaditCompMail'] = 0;          // 与信完了メール
        $datas['AddressMail'] = 0;              // 注文修正メール
        $datas['ClaimMail'] = 0;                // 請求書発行メール
        $datas['ReceiptMail'] = 0;              // 入金確認メール
        $datas['CancelMail'] = 0;               // キャンセル確認メール
        $datas['SoonPaymentMail'] = 0;          // もうすぐお支払いメール
        $datas['NotPaymentConfMail'] = 0;       // お支払未確認メール
        $datas['CreditResultMail'] = 0;         // 与信結果メール
        $datas['JintecManualReqFlg'] = 0;         // ジンテック手動与信強制
        $datas['EtcAutoArrivalFlg'] = 0;        // 「その他」着荷を自動で取る
        $datas['NgChangeFlg'] = 0;              // NG無保証変更
        $datas['ShowNgReason'] = 0;             // NG理由表示
        $datas['MuhoshoChangeDays'] = 7;        // 無保証変更可能期間
        $datas['ReClaimFeeSetting'] = 0;        // 再請求手数料設定種別
        $datas['ChatBotFlg'] = 0;               // 連続注文除外
        //請求書マイページ印字
        $datas['ClaimMypagePrint'] = $mdlCode->find(160,$enterpriseData['OemId'])->current()['Class5'];
        // 加盟店ID
        $datas['EnterpriseId'] = $enterpriseId;
        // 申し込み日
        $datas['ApplicationDate'] = $enterpriseData['ApplicationDate'];

        // 自由項目
        $datas['FreeItemsL'] = '設定なし';
        $datas['FreeItemsV'] = '設定する';
        $hashFreeItems = array();
        for ($i = 1; $i <= 20; $i++) {
            $hashFreeItems['Free'. $i] = '';
        }
        // 自由項目をエンコード
        $formData = base64_encode(serialize($hashFreeItems));

        $datas['BillingAgentFlg'] = '0';        // 請求代行プラン
        $datas['OtherSitesAuthCheckFlg'] = '1'; // 他サイトの与信対象外
        $datas['IluCooperationFlg'] = '1';      // 審査システム連携
        $datas['ReceiptUsedFlg'] = '0';         // 領収書利用フラグ

        // セッションデータを破棄
        unset($_SESSION['TMP_IMAGE_SEQ']);

        $datas['isNew'] = true;

        // 全案件補償外ダイアログ対応
        $sql = " SELECT PropValue FROM T_SystemProperty WHERE Module = :Module  AND Category = :Category AND Name = :Name ";
        $prm = array(':Module' => '[DEFAULT]', ':Category' => 'systeminfo', ':Name' => 'AllowOutOfAmendsName');
        $allowOutOfAmendsName =  $this->app->dbAdapter->query($sql)->execute($prm)->current()['PropValue'];

        // 支払方法設定
        if (is_null($datas['OemId'])) {
            $oemId = 0;
        } else {
            $oemId = $datas['OemId'];
        }
        $payments = ResultInterfaceToArray($mdlPayment->fetchAllSubscriberCode($oemId));

        $this->view->assign('list', $datas);
        $this->view->assign('EnterpriseId', $enterpriseId);
        $this->view->assign('SelfBillingMode', $enterpriseData['SelfBillingMode']);
        $this->view->assign('BillingAgentFlg', $enterpriseData['BillingAgentFlg']);
        $this->view->assign('AllowOutOfAmendsName', $allowOutOfAmendsName);
        $this->view->assign('hashFreeItems', $formData);
        $this->view->assign('payments', $payments);
        $this->view->assign('firstClaimLayoutModeCtl', $firstClaimLayoutModeCtl);
        $this->view->assign('defaultClaimMypagePrint', $mdlCode->find(160,$enterpriseData['OemId'])->current()['Class5']);

        $this->setTemplate('edit');

        return $this->view;
	}

    /**
     * Regist Todoitekara
     */
    public function registtodoitekaraAction()
    {
        $params = $this->getParams();

        // get Ent data by ID
        $enterpriseId = $params['eid'];
        $mdlEnterprise = new TableEnterprise($this->app->dbAdapter);
        $entData = $mdlEnterprise->findEnterprise($enterpriseId)->current();
        $iul = new \models\Logic\LogicImageUpLoader($this->app->dbAdapter);
        if ($entData['CombinedClaimMode'] != 0
            || $entData['BillingAgentFlg'] != 0 || $entData['CreditTransferFlg'] != 0 ) {
            $this->view->assign('EnterpriseId', $enterpriseId);
            $this->setTemplate('errortodoitekara');
            return $this->view;
        }
        // get site data by ID
        $siteId = $params['sid'];
        $mdlSite = new TableSite($this->app->dbAdapter);
        $siteData = $mdlSite->findSite($siteId)->current();

        // get payment methods
        $tblSbpsPayment = new TableSbpsPayment($this->app->dbAdapter);
        $payments = array();
        foreach ($tblSbpsPayment->getList($entData['OemId']) as $row){
            $payments[] = array(
                'PaymentId' => $row['SbpsPaymentId'],
                'PaymentName' => $row['PaymentNameKj']
            );
        }

        // get contractors
        $mdlCode = new TableCode($this->app->dbAdapter);
        $contractors = array();
        foreach ($mdlCode->getMasterByClass(212) as $row){
            $contractors[] = array(
                'ContractorId' => $row['KeyCode'],
                'ContractorName' => $row['KeyContent']
            );
        }

        // get site payment by Site Id
        $sitePaymentsData = array();
        $mdlSitePayment = new TableSiteSbpsPayment($this->app->dbAdapter);
        $sitePayments = ResultInterfaceToArray($mdlSitePayment->getAll($siteId, false));
        $flagAction = 0; //create = 0
        if ($sitePayments) {
            foreach ($sitePayments as $sitePayment) {
                $sitePaymentsData[$sitePayment['PaymentId']] = $sitePayment;
                $sitePaymentsData[$sitePayment['PaymentId']]['UseStartDate'] = $sitePayment['UseStartDate'] ? date('Y-m-d H:i', strtotime($sitePayment['UseStartDate'])) : '';
            }
            $flagAction = 1; //edit = 1;
        }

        // get data for dropdown copy
        $sitesSbps = array();
        $sites = ResultInterfaceToArray($mdlSite->getValidAll4Copy($enterpriseId));
        $sitesSbps_ = ResultInterfaceToArray($mdlSitePayment->getAllValidSite());
        if ($sitesSbps_) {
            foreach ($sitesSbps_ as $ss) {
                $sitesSbps[] = $ss['SiteId'];
            }
        }

        // セッションデータを破棄
        unset($_SESSION['TMP_IMAGE_SEQ']);
        // submit form
        $error = array();
        if ($this->params()->fromPost()) {
            $logicSbps = new LogicSbps($this->app->dbAdapter);
            $checkTodo = $logicSbps->checkSettingTodo($enterpriseId);
            if ($checkTodo['disableLink']) {
                $this->view->assign('EnterpriseId', $enterpriseId);
                $this->setTemplate('errortodoitekara');
                return $this->view;
            }
            $error = $this->_confirmTodo($params);
        }
        
        //logo
        if($error['LogoSeq'] || $params['NewLogoSeq']){
            $logo2_data = $iul->getLogo2TmpImage($error['LogoSeq'] ? $error['LogoSeq'] : $params['NewLogoSeq']);
            $siteData['SmallLogo'] = $logo2_data['ImageData'];
            $params['NewLogoSeq'] = $logo2_data['Seq'];
        } 
        $this->view->assign('EnterpriseId', $enterpriseId);
        $this->view->assign('flagAction', $flagAction);
        $this->view->assign('error', $error);
        $this->view->assign('payments', $payments);
        $this->view->assign('contractors', $contractors);
        $this->view->assign('sitePaymentsData', $sitePaymentsData);
        $this->view->assign('sites', $sites);
        $this->view->assign('sitesSbps', $sitesSbps);
        $this->view->assign('data', array_merge($entData, $siteData, array('Payment' => $sitePaymentsData), $params));
        if (isset($_SESSION['BEFORE_SETTING_TODO'])) {
            $this->view->assign('backUrl', $_SESSION['BEFORE_SETTING_TODO']);
        } else {
            $this->view->assign('backUrl', 'site/list/eid/'.$enterpriseId);
        }
        
        $this->setTemplate('edittodoitekara');
        $this->addStyleSheet('../css/cbadmin/site/registtodoitekara.css');
        return $this->view;
	}

    /**
     * Ajax get Sbps payment to copy
     */
    public function getsbpsbysiteidAction() {
        $params = $this->getParams();
        $sid = (isset($params['sid'])) ? $params['sid'] : 0;

        // get site payment by Site Id
        $sitePaymentsData = array();
        $mdlSitePayment = new TableSiteSbpsPayment($this->app->dbAdapter);
        $sitePayments = ResultInterfaceToArray($mdlSitePayment->getAll($sid));
        // get site by Site Id
        $mdlSite = new TableSite($this->app->dbAdapter);
        $siteData = $mdlSite->findSite($sid)->current();
        if ($sitePayments) {
            foreach ($sitePayments as $sitePayment) {
                $sitePaymentsData['SitePayment'][$sitePayment['PaymentId']] = $sitePayment;
                $sitePaymentsData['SitePayment'][$sitePayment['PaymentId']]['UseStartDate'] = $sitePayment['UseStartDate'] ? date('Y-m-d H:i', strtotime($sitePayment['UseStartDate'])) : '';
            }
        }
        $sitePaymentsData['Site'] = $siteData;

        echo \Zend\Json\Json::encode($sitePaymentsData);
        return $this->response;
    }
    
    /**
     * Ajax check validate form setting Todo
     */
    public function validatetodoAction() {
        $params = $this->getParams();
        $error = $this->_validateTodo($params);
        echo \Zend\Json\Json::encode($error);
        return $this->response;
    }

    /**
     * サイト編集
     */
    public function editAction()
    {
        unset($_SESSION['TMP_IMAGE_SEQ']);

        $params = $this->getParams();
        $mdlEnterprise = new TableEnterprise($this->app->dbAdapter);
        $enterpriseId = $params['eid'];
        // 事業者名の取得
        $enterpriseData = $mdlEnterprise->findEnterprise($enterpriseId)->current();
        //口座振替利用か確認
        $datas['CreditTransferFlg'] = $enterpriseData['CreditTransferFlg'];
        $siteId = $params['sid'];
$this->app->logger->debug('editAction:');
$this->app->logger->debug($params);

        $mdlEnterprise = new TableEnterprise($this->app->dbAdapter);
        $mdlSite = new TableSite($this->app->dbAdapter);
        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
//        $mdlDelMthd = new TableDeliMethod($this->app->dbAdapter);
        $mdlCode = new TableCode($this->app->dbAdapter);
        $mdlPricePlan = new TablePricePlan($this->app->dbAdapter);
        $mdlOem = new TableOem($this->app->dbAdapter);
        $mdlCvs = new TableCvsReceiptAgent($this->app->dbAdapter);
        $mdlPayment = new TablePayment($this->app->dbAdapter);
        $mdlSitePayment = new TableSitePayment($this->app->dbAdapter);

        // set url before going to page setting
        $_SESSION['BEFORE_SETTING_TODO'] = 'site/edit/eid/'.$enterpriseId.'/sid/'.$siteId;

        // 事業者名の取得・アサイン
        $enterpriseData = $mdlEnterprise->findEnterprise($enterpriseId)->current();
        $this->view->assign("EnterpriseNameKj", $enterpriseData['EnterpriseNameKj']);

        //加盟店OEM先
        $datas['OemId'] = $enterpriseData['OemId'];
        // 加盟店ID
        $datas['EnterpriseId'] = $enterpriseId;

        //サイトデータ取得
        $site = $mdlSite->findSite($siteId)->current();

        // check whether show link Todo Regist
        $logicSbps = new LogicSbps($this->app->dbAdapter);
        $checkTodo = $logicSbps->checkSettingTodo($enterpriseId);

        //再請求手数料が０円の場合空欄とする、ただし入力出来ない項目とする
        if ($site['ReClaimFeeSetting'] == 0) {
            $site['ReClaimFee1'] = (isset($site['ReClaimFee1']) && $site['ReClaimFee1'] == 0) ? '' : $site['ReClaimFee1'];
            $site['ReClaimFee3'] = (isset($site['ReClaimFee3']) && $site['ReClaimFee3'] == 0) ? '' : $site['ReClaimFee3'];
            $site['ReClaimFee4'] = (isset($site['ReClaimFee4']) && $site['ReClaimFee4'] == 0) ? '' : $site['ReClaimFee4'];
            $site['ReClaimFee5'] = (isset($site['ReClaimFee5']) && $site['ReClaimFee5'] == 0) ? '' : $site['ReClaimFee5'];
            $site['ReClaimFee6'] = (isset($site['ReClaimFee6']) && $site['ReClaimFee6'] == 0) ? '' : $site['ReClaimFee6'];
            $site['ReClaimFee7'] = (isset($site['ReClaimFee7']) && $site['ReClaimFee7'] == 0) ? '' : $site['ReClaimFee7'];
        }

        //サイト形態取得
        $datas['SiteForms'] = $codeMaster->getSiteFormMaster();

        //申込日
        $datas['ApplicationDate'] = $enterpriseData['ApplicationDate'];

        //利用プラン取得
        $pricePlan = $mdlPricePlan->find($enterpriseData['Plan'])->current();
        $datas['PlanName'] = $pricePlan['PricePlanName'];
        //OEM情報取得
        $oem = $mdlOem->find($enterpriseData['OemId'])->current();
        $oemData = array();
        if ($oem) {
            $oemData['ClaimFeeBS'] = $oem['ClaimFeeBS'];
            $oemData['ClaimFeeDK'] = $oem['ClaimFeeDK'];
            $feelist = Json::decode($oem['SettlementFeeRatePlan'], Json::TYPE_ARRAY);
            $oemData['SettlementFeeRate'] = $feelist[$enterpriseData['Plan']];
            $oemData['KisanbiDelayDays'] = $oem['KisanbiDelayDays'];
            $oemData['FirstCreditTransferClaimFeeOem'] = $oem['FirstCreditTransferClaimFeeOem'];
            $oemData['FirstCreditTransferClaimFeeWebOem'] = $oem['FirstCreditTransferClaimFeeWebOem'];
            $oemData['CreditTransferClaimFeeOem'] = $oem['CreditTransferClaimFeeOem'];
        }
        //金額情報
        $reference = array();
        $reference['SettlementAmountLimit'] = $pricePlan['SettlementAmountLimit'];
        $reference['SettlementFeeRate'] = $pricePlan['SettlementFeeRate'];
        $reference['ClaimFeeBS'] = $pricePlan['ClaimFeeBS'];
        $reference['ClaimFeeDK'] = $pricePlan['ClaimFeeDK'];
        $reference['ReClaimFee']  = $pricePlan['ReClaimFee'];
        $reference['OemClaimFeeBS'] = $oemData['ClaimFeeBS'];
        $reference['OemClaimFeeDK'] = $oemData['ClaimFeeDK'];
        $reference['OemSettlementFeeRate'] = $oemData['SettlementFeeRate'];
        $reference['OemKisanbiDelayDays'] = $oemData['KisanbiDelayDays'];
        $reference['FirstCreditTransferClaimFeeOem'] = $oemData['FirstCreditTransferClaimFeeOem'];
        $reference['FirstCreditTransferClaimFeeWebOem'] = $oemData['FirstCreditTransferClaimFeeWebOem'];
        $reference['CreditTransferClaimFeeOem'] = $oemData['CreditTransferClaimFeeOem'];
        $datas['Reference'] = $reference;

        //与信判定基準取得
        $sql  = " SELECT DISTINCT CreditCriterionId, CreditCriterionName  FROM M_CreditPoint ";
        $ri = $this->app->dbAdapter->query($sql)->execute(null);
        $datas['CreditCriterionNames'] = ResultInterfaceToArray($ri);
        $datas['CreditCriterionNum'] = $site['CreditCriterion'];
        //与信判定方法
        $data=array();
        foreach ($mdlCode->getMasterByClass(94) as $row){
            $data[] = $row['KeyContent'];
        }
        $datas['Creditdecision'] = $data;

        //自動伝票番号登録時配送先取得
        $lgc = new \models\Logic\LogicDeliveryMethod($this->app->dbAdapter);
        $datas['DeliMethodName'] = $lgc->getEnterpriseDeliveryMethodList($enterpriseId, false);

        //請求書用紙種類取得
        $data=array();
        foreach ($mdlCode->getMasterByClass(79) as $row){
            $data[$row['KeyCode']] = $row['KeyContent'];
        }
        $datas['Invoice'] = $data;

        //請求書用紙種類取得（同梱）
        $data=array();
        foreach ($mdlCode->getMasterByClass(106) as $row){
            $data[$row['KeyCode']] = $row['KeyContent'];
        }
        $datas['InvoiceDK'] = $data;

         // CB_B2C_DEV-14
        $datas['ReceiptAgentForms'] = $mdlCvs->getEnterpriseCvsReceiptAgentList($enterpriseId,false);

        $firstClaimLayoutModeCtl = $mdlCode->find(160, $enterpriseData['OemId'])->current()['Class7'];
        //
        if(!empty($site['SubscriberCode'])){
	        $datas['SubscriberCodeSearchView'] = $this->searchsubscriberAction($site);
        }
        
       // サイトに紐づくAPIユーザ取得
        $sql = <<<EOQ
            SELECT  au.ApiUserId
                ,   au.ValidFlg
                ,   au.ApiUserNameKj
            FROM    T_ApiUser au
                    INNER JOIN T_ApiUserEnterprise ae ON (au.ApiUserId = ae.ApiUserId)
                    INNER JOIN T_Site s ON (ae.SiteId = s.SiteId)
            WHERE   s.SiteId = :SiteId
            ;
EOQ;
        $ri = $this->app->dbAdapter->query($sql)->execute(array( ':SiteId' => $siteId ));
        $apidata = ResultInterfaceToArray($ri);

        // キャンペーン情報取得
        // サイトIDを条件にキャンペーン情報を取得する。
        $sql = <<<EOQ
            SELECT  ec.Seq
                ,   pp.PricePlanName
                ,   ec.DateFrom
                ,   ec.DateTo
                ,   ec.MonthlyFee
            FROM    T_EnterpriseCampaign ec
                    INNER JOIN M_PricePlan pp ON (pp.PricePlanId = ec.AppPlan)
            WHERE   SiteId = :SiteId
            ;
EOQ;
        $ri = $this->app->dbAdapter->query($sql)->execute(array( ':SiteId' => $siteId ));
        $campaign = ResultInterfaceToArray($ri);

        // 自由項目
        $datas['FreeItemsL'] = '設定なし';
        $datas['FreeItemsV'] = '設定する';
        $mdlsfi = new TableSiteFreeItems( $this->app->dbAdapter );
        $freeItems = $mdlsfi->find( $siteId )->current();
        $hashFreeItems = array();
        for ($i = 1; $i <= 20; $i++) {
            if ( isset($freeItems['Free'. $i]) && !empty($freeItems['Free'. $i]) )  {
                $datas['FreeItemsL'] = '設定あり';
                $datas['FreeItemsV'] = '変更する';
                $hashFreeItems['Free'. $i] = $freeItems['Free'. $i];
            } else {
                $hashFreeItems['Free'. $i] = '';
            }
        }
        // 自由項目をエンコード
        $formData = base64_encode(serialize($hashFreeItems));

        $datas['isNew'] = false;

        // 全案件補償外ダイアログ対応
        $sql = " SELECT PropValue FROM T_SystemProperty WHERE Module = :Module  AND Category = :Category AND Name = :Name ";
        $prm = array(':Module' => '[DEFAULT]', ':Category' => 'systeminfo', ':Name' => 'AllowOutOfAmendsName');
        $allowOutOfAmendsName =  $this->app->dbAdapter->query($sql)->execute($prm)->current()['PropValue'];

        // 支払方法設定
        if (is_null($datas['OemId'])) {
            $oemId = 0;
        } else {
            $oemId = $datas['OemId'];
        }
        $payments = ResultInterfaceToArray($mdlPayment->fetchAllSubscriberCode($oemId));
        $site_datas = ResultInterfaceToArray($mdlSitePayment->getAll($siteId));
        $payment_data = array();
        $payment_data['Payment'] = array();
        // 支払方法を表示用に加工
        foreach ($payments as $payment) {
            $paymentId = $payment['PaymentId'];
            $payment_data['Payment'][$paymentId]['UseFlg'] = 0;
            $payment_data['Payment'][$paymentId]['ApplyDate'] = '';
            $payment_data['Payment'][$paymentId]['UseStartDate'] = '';
            $payment_data['Payment'][$paymentId]['UseStartFixFlg'] = '';
            $payment_data['Payment'][$paymentId]['ProtectFlg'] = '0';
            $payment_data['Payment'][$paymentId]['FixProtectFlg'] = '0';
        }
        foreach ($site_datas as $site_data) {
            $paymentId = $site_data['PaymentId'];
            $payment_data['Payment'][$paymentId]['UseFlg'] = $site_data['UseFlg'];
            $payment_data['Payment'][$paymentId]['ApplyDate'] = $site_data['ApplyDate'];
            $payment_data['Payment'][$paymentId]['UseStartDate'] = $site_data['UseStartDate'];
            $payment_data['Payment'][$paymentId]['UseStartFixFlg'] = $site_data['UseStartFixFlg'];
            if (!empty($site_data['ApplyFinishDate'])) {
                $payment_data['Payment'][$paymentId]['ProtectFlg'] = 1;
            } else {
                $payment_data['Payment'][$paymentId]['ProtectFlg'] = 0;
            }
            if ($site_data['UseStartFixFlg'] == 1) {
                $payment_data['Payment'][$paymentId]['FixProtectFlg'] = 1;
            } else {
                $payment_data['Payment'][$paymentId]['FixProtectFlg'] = 0;
            }
        }

        $this->view->assign('checkTodo', $checkTodo);
        $this->view->assign('list', array_merge($site, $datas, $payment_data));
        $this->view->assign('EnterpriseId', $enterpriseId);
        $this->view->assign('apiuserlist', $apidata);
        $this->view->assign('campaign', $campaign);
        $this->view->assign('SelfBillingMode', $enterpriseData['SelfBillingMode']);
        $this->view->assign('BillingAgentFlg', $enterpriseData['BillingAgentFlg']);
        $this->view->assign('AllowOutOfAmendsName', $allowOutOfAmendsName);
        $this->view->assign('hashFreeItems', $formData);
        $this->view->assign('payments', $payments);
        $this->view->assign('firstClaimLayoutModeCtl', $firstClaimLayoutModeCtl);
        $this->view->assign('defaultClaimMypagePrint', $mdlCode->find(160,$enterpriseData['OemId'])->current()['Class5']);

        return $this->view;
    }

    /**
     * サイト登録・編集確認画面
     */
    public function confirmAction()
    {
        $params = $this->getParams();
        $list = $params['list'];
$this->app->logger->debug('confirmAction:');
$this->app->logger->debug($params);

        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
        $mdlPricePlan = new TablePricePlan($this->app->dbAdapter);
        $mdlCode = new TableCode($this->app->dbAdapter);
//        $mdlDelMthd = new TableDeliMethod($this->app->dbAdapter);
//        $mdlAgency = new TableAgency($this->app->dbAdapter);
        $mdlEnterprise = new TableEnterprise($this->app->dbAdapter);
        $mdlOem = new TableOem($this->app->dbAdapter);
        $mdlCvs = new TableCvsReceiptAgent($this->app->dbAdapter);
        $mdlPayment = new TablePayment($this->app->dbAdapter);
        $mdlSitePayment = new TableSitePayment($this->app->dbAdapter);

        //サイト形態取得コードマスターID:10
        $datas['SiteForms'] = $codeMaster->getSiteFormMaster();

        //加盟店ID
        $enterpriseId = $list['EnterpriseId'];

        $enterpriseData = $mdlEnterprise->findEnterprise($enterpriseId)->current();
        $list['OemId'] = $enterpriseData['OemId'];
        $list['ApplicationDate'] = $enterpriseData['ApplicationDate'];

        //メールアドレス チェックオフの場合は0にする
        if (!isset($list['ReqMailAddrFlg']) && strlen($list['ReqMailAddrFlg']) <= 0) {
            $list['ReqMailAddrFlg'] = 0;
        }

        //初回請求用紙モード チェックオフの場合は0にする
        if (!isset($list['FirstClaimLayoutMode']) && strlen($list['FirstClaimLayoutMode']) <= 0) {
            $list['FirstClaimLayoutMode'] = 0;
        }

        //全案件補償外 チェックオフの場合は0にする
        if (!isset($list['OutOfAmendsFlg']) && strlen($list['OutOfAmendsFlg']) <= 0) {
            $list['OutOfAmendsFlg'] = 0;
        }

        //有効設定 チェックオフの場合は0にする
        if (!isset($list['ValidFlg']) && strlen($list['ValidFlg']) <= 0) {
            $list['ValidFlg'] = 0;
        }

        //NG無保証変更 チェックオフの場合は0にする
        if (!isset($list['NgChangeFlg']) && strlen($list['NgChangeFlg']) <= 0) {
            $list['NgChangeFlg'] = 0;
        }

        //三菱UFバーコード利用フラグ チェックオフの場合は0にする
        if (!isset($list['MufjBarcodeUsedFlg']) && strlen($list['MufjBarcodeUsedFlg']) <= 0) {
            $list['MufjBarcodeUsedFlg'] = 0;
            $list['MufjBarcodeSubscriberCode'] = '';
        }

        //利用プラン取得
        $pricePlan = $mdlPricePlan->find($enterpriseData['Plan'])->current();
        $list['PlanName'] = $pricePlan['PricePlanName'];

        //与信判定基準取得
        $sql  = " SELECT DISTINCT CreditCriterionId, CreditCriterionName FROM M_CreditPoint ";
        $ri = $this->app->dbAdapter->query($sql)->execute(null);
        $datas['CreditCriterionNames'] = ResultInterfaceToArray($ri);
        //与信判定方法
        $data=array();
        foreach ($mdlCode->getMasterByClass(94) as $row){
            $data[] = $row['KeyContent'];
        }
        $datas['Creditdecision'] = $data;

        //自動伝票番号登録時配送先取得
        $lgc = new \models\Logic\LogicDeliveryMethod($this->app->dbAdapter);
        $datas['DeliMethodName'] = $lgc->getEnterpriseDeliveryMethodList($enterpriseId, false);

        //請求書用紙種類取得
        $data=array();
        foreach ($mdlCode->getMasterByClass(79) as $row){
            $data[$row['KeyCode']] = $row['KeyContent'];
        }
        $datas['Invoice'] = $data;

        //請求書用紙種類取得（同梱）
        $data=array();
        foreach ($mdlCode->getMasterByClass(106) as $row){
            $data[$row['KeyCode']] = $row['KeyContent'];
        }
        $datas['InvoiceDK'] = $data;

        // CB_B2C_DEV-14
        $datas['ReceiptAgentForms'] = $mdlCvs->getEnterpriseCvsReceiptAgentList($enterpriseId,false);

        //口座振替利用
        $datas['CreditTransferFlg'] = $enterpriseData['CreditTransferFlg'];
        $list['CreditTransferFlg'] = $enterpriseData['CreditTransferFlg'];

        // ロゴ(小)
        $upload_file = $_FILES['list'];
        $image_uploaded = false;
        if(!empty($upload_file['name']['SmallLogo'])){
            $iul = new \models\Logic\LogicImageUpLoader($this->app->dbAdapter);

            //リロードされないようにセッション管理
            $tis = new Container('TMP_IMAGE_SEQ');

            $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
            try {
                // ユーザIDの取得
                $userTable = new \models\Table\TableUser($this->app->dbAdapter);
                $userID = $userTable->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

                //セッションにデータがある場合はセッションから画像データ取得する
                if(!is_null($tis->logo2)){
                    $logo2_seq = $tis->logo2;
                }
                else{
                    //画像を一時アップロード
                    $logo2_seq = $iul->saveLogo2TmpImage(null,$upload_file['type']['SmallLogo'],$upload_file['name']['SmallLogo'],$upload_file['tmp_name']['SmallLogo'],$userId);
                    $tis->logo2 = $logo2_seq;
                }
                //ロゴ2取得
                $logo2_data = $iul->getLogo2TmpImage($logo2_seq);

                if(!is_null($logo2_data)){
                    $list['SmallLogo']['image'] = $logo2_data['ImageData'];
                    $list['SmallLogo']['seq'] = $logo2_seq;
                    $image_uploaded = true;
                }

                $this->app->dbAdapter->getDriver()->getConnection()->commit();
            }
            catch(\Exception $err) {
                $this->app->dbAdapter->getDriver()->getConnection()->rollBack();
                throw $err;
            }
        }
        else {
            $mdlSite = new TableSite($this->app->dbAdapter);
            $list['SmallLogo']['image'] = $mdlSite->findSite($list['SiteId'])->current()['SmallLogo'];
        }

        // 自由項目
        $list['FreeItemsL'] = '設定なし';
        $list['FreeItemsV'] = '設定する';
        $freeItems = unserialize(base64_decode($params['hashFreeItems']));
        for ($i = 1; $i <= 20; $i++) {
            if ( isset($freeItems['Free'. $i]) && !empty($freeItems['Free'. $i]) )  {
                $list['FreeItemsL'] = '設定あり';
                $list['FreeItemsV'] = '変更する';
                break;
            }
        }
        $list = array_merge($list, $freeItems);

        // 支払方法取得
        if (is_null($enterpriseData['OemId'])) {
            $oemId = 0;
        } else {
            $oemId = $enterpriseData['OemId'];
        }
        $payments = ResultInterfaceToArray($mdlPayment->fetchAllSubscriberCode($oemId));

        // 支払方法（PayB→ゆうちょPay）
        $yucho_pay = 0;
        $payb = 0;
        foreach ($payments as $payment) {
            $paymentId = $payment['PaymentId'];
            switch ($payment['FixedId']) {
                case 1:
                    $payb = $paymentId;
                    break;
                case 6:
                    $yucho_pay = $paymentId;
                    break;
            }
        }
        if (($yucho_pay != 0) && ($payb != 0)) {
            $list['Payment'][$yucho_pay]['UseFlg'] = $list['Payment'][$payb]['UseFlg'];
            $list['Payment'][$yucho_pay]['ApplyDate'] = $list['Payment'][$payb]['ApplyDate'];
            $list['Payment'][$yucho_pay]['UseStartDate'] = $list['Payment'][$payb]['UseStartDate'];
            $list['Payment'][$yucho_pay]['UseStartFixFlg'] = $list['Payment'][$payb]['UseStartFixFlg'];
        }

        // フォームデータ自身をエンコード
        $formData = base64_encode(serialize($list));

        // エラーチェック
        $errors = $this->validate($list, $enterpriseData['BillingAgentFlg'], $payments);

        // count関数対策
        if( !empty($errors) ) {
            // サイトに紐づくAPIユーザ取得
            $sql = <<<EOQ
                SELECT  au.ApiUserId
                    ,   au.ValidFlg
                    ,   au.ApiUserNameKj
                FROM    T_ApiUser au
                        INNER JOIN T_ApiUserEnterprise ae ON (au.ApiUserId = ae.ApiUserId)
                        INNER JOIN T_Site s ON (ae.SiteId = s.SiteId)
                WHERE   s.SiteId = :SiteId
                ;
EOQ;
            $ri = $this->app->dbAdapter->query($sql)->execute(array( ':SiteId' => $list['SiteId'] ));
            $apidata = ResultInterfaceToArray($ri);

            // キャンペーン情報取得
            // サイトIDを条件にキャンペーン情報を取得する。
            $sql = <<<EOQ
                SELECT  ec.Seq
                    ,   pp.PricePlanName
                    ,   ec.DateFrom
                    ,   ec.DateTo
                    ,   ec.MonthlyFee
                FROM    T_EnterpriseCampaign ec
                        INNER JOIN M_PricePlan pp ON (pp.PricePlanId = ec.AppPlan)
                WHERE   SiteId = :SiteId
                ;
EOQ;
            $ri = $this->app->dbAdapter->query($sql)->execute(array( ':SiteId' => $list['SiteId'] ));
            $campaign = ResultInterfaceToArray($ri);

            //OEM情報取得
            $oem = $mdlOem->find($enterpriseData['OemId'])->current();
            $oemData = array();
            if ($oem) {
                $oemData['ClaimFeeBS'] = $oem['ClaimFeeBS'];
                $oemData['ClaimFeeDK'] = $oem['ClaimFeeDK'];
                $feelist = Json::decode($oem['SettlementFeeRatePlan'], Json::TYPE_ARRAY);
                $oemData['SettlementFeeRate'] = $feelist[$enterpriseData['Plan']];
                $oemData['FirstCreditTransferClaimFeeOem'] = $oem['FirstCreditTransferClaimFeeOem'];
                $oemData['FirstCreditTransferClaimFeeWebOem'] = $oem['FirstCreditTransferClaimFeeWebOem'];
                $oemData['CreditTransferClaimFeeOem'] = $oem['CreditTransferClaimFeeOem'];
            }

            //ロゴ(小)
            $mdlSite = new TableSite($this->app->dbAdapter);
            $list['SmallLogo'] = $mdlSite->findSite($list['SiteId'])->current()['SmallLogo'];
            if ($image_uploaded) {
                $errors['SmallLogo'] = '検証エラーが発生したため、アップロードされた画像は破棄されました。もう一度登録してください';
            }

            $firstClaimLayoutModeCtl = $mdlCode->find(160, $enterpriseData['OemId'])->current()['Class7'];

            //利用プラン金額情報
            $reference = array();
            $reference['SettlementAmountLimit'] = $pricePlan['SettlementAmountLimit'];
            $reference['SettlementFeeRate'] = $pricePlan['SettlementFeeRate'];
            $reference['ClaimFeeBS'] = $pricePlan['ClaimFeeBS'];
            $reference['ClaimFeeDK'] = $pricePlan['ClaimFeeDK'];
            $reference['ReClaimFee']  = $pricePlan['ReClaimFee'];
            $reference['OemClaimFeeBS'] = $oemData['ClaimFeeBS'];
            $reference['OemClaimFeeDK'] = $oemData['ClaimFeeDK'];
            $reference['OemSettlementFeeRate'] = $oemData['SettlementFeeRate'];
            $reference['FirstCreditTransferClaimFeeOem'] = $oemData['FirstCreditTransferClaimFeeOem'];
            $reference['FirstCreditTransferClaimFeeWebOem'] = $oemData['FirstCreditTransferClaimFeeWebOem'];
            $reference['CreditTransferClaimFeeOem'] = $oemData['CreditTransferClaimFeeOem'];
            $list['Reference'] = $reference;

            $logicSbps = new LogicSbps($this->app->dbAdapter);
            $checkTodo = $logicSbps->checkSettingTodo($enterpriseId);

            $this->view->assign('checkTodo', $checkTodo);
            $this->view->assign('error', $errors);
            $this->view->assign('EnterpriseId', $enterpriseId);
            $this->view->assign('list', array_merge($list, $datas));
            $this->view->assign('apiuserlist', $apidata);
            $this->view->assign('campaign', $campaign);
            $this->view->assign('SelfBillingMode', $enterpriseData['SelfBillingMode']);
            $this->view->assign('hashFreeItems', $params['hashFreeItems']);
            $this->view->assign('payments', $payments);
            $this->view->assign('BillingAgentFlg', $enterpriseData['BillingAgentFlg']);
            $this->setTemplate('edit');
            $this->view->assign('firstClaimLayoutModeCtl', $firstClaimLayoutModeCtl);
            $this->view->assign('defaultClaimMypagePrint', $mdlCode->find(160,$enterpriseData['OemId'])->current()['Class5']);
            return $this->view;

        }

        // OEM決済手数料率(税込)
        $obj = new \models\Table\TableSystemProperty($this->app->dbAdapter);
        $list['OemSettlementFeeRateZeikomi'] =$obj->getIncludeTaxRate(date('Y-m-d'), nvl($list['OemSettlementFeeRate'], 0), 3, 1);

        $logicSbps = new LogicSbps($this->app->dbAdapter);
        $checkTodo = $logicSbps->checkSettingTodo($enterpriseId);

        $this->view->assign('checkTodo', $checkTodo);

        $this->view->assign('list', $list);
        $this->view->assign('EnterpriseId', $enterpriseId);
        $this->view->assign('pulldownlist', $datas);
        $this->view->assign('encoded_data', $formData);
        $this->view->assign('SelfBillingMode', $enterpriseData['SelfBillingMode']);
        $this->view->assign('imageUploaded', $image_uploaded);
        $this->view->assign('payments', $payments);

        return $this->view;
    }

    protected function _confirmTodo($params)
    {
        $errors = array();
        $iul = new \models\Logic\LogicImageUpLoader($this->app->dbAdapter);
        
        // ロゴ(小)
        $image_uploaded = false;
        if (!empty($_FILES['SmallLogo']['name'])) {
            if ( $_FILES['SmallLogo']['size'] < 50000 ) {
    
                //リロードされないようにセッション管理
                $tis = new Container('TMP_IMAGE_SEQ');
    
                $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
                try {
                    // ユーザIDの取得
                    $userTable = new \models\Table\TableUser($this->app->dbAdapter);
                    $userId = $userTable->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);
    
                    //セッションにデータがある場合はセッションから画像データ取得する
                    if (!is_null($tis->logo2)) {
                        $logo2_seq = $tis->logo2;
                    } else {
                        //画像を一時アップロード
                        $logo2_seq = $iul->saveLogo2TmpImage(
                            $params['OemId'], 
                            $_FILES['SmallLogo']['type'], 
                            $_FILES['SmallLogo']['name'], 
                            $_FILES['SmallLogo']['tmp_name'], 
                            $userId)
                        ;
                        $tis->logo2 = $logo2_seq;
                    }
                    //ロゴ2取得
                    $logo2_data = $iul->getLogo2TmpImage($logo2_seq);
                    if (!is_null($logo2_data)) {
                        $params['SmallLogo']['image'] = $logo2_data['ImageData'];
                        $params['SmallLogo']['seq'] = $logo2_seq;
                        $image_uploaded = true;
                    }
    
                    $this->app->dbAdapter->getDriver()->getConnection()->commit();
                }
                catch(\Exception $err) {
                    $this->app->dbAdapter->getDriver()->getConnection()->rollBack();
                    throw $err;
                }
            } else {
                $errors_smallLogo = '登録した画像は50KB以上なので、破棄されました。もう一度登録してください';
            }
        } else {
            
            if ( $params['NewLogoSeq'] && $params['NewLogoSeq'] != "" ) {
                //ロゴ2取得
                $logo2_data = $iul->getLogo2TmpImage($params['NewLogoSeq']);
                if (!is_null($logo2_data)) {
                    $params['SmallLogo']['image'] = $logo2_data['ImageData'];
                    $params['SmallLogo']['seq'] = $params['NewLogoSeq'];
                }
            } else {
                $mdlSite = new TableSite($this->app->dbAdapter);
                $params['SmallLogo']['image'] = $mdlSite->findSite($params['SiteId'])->current()['SmallLogo'];
            }
        }

        // エラーチェック
        $errors = $this->_validateTodo($params);
        if ($errors) {
            //ロゴ(小)
            $mdlSite = new TableSite($this->app->dbAdapter);
            $params['SmallLogo']['image'] = $mdlSite->findSite($params['SiteId'])->current()['SmallLogo'];
//             if ($image_uploaded) {
//                 $errors['SmallLogo'] = '検証エラーが発生したため、アップロードされた画像は破棄されました。もう一度登録してください';
//             }

            if ( $params['NewLogoSeq'] && $params['NewLogoSeq'] != "" ) {
                //ロゴ2取得
                $logo2_data = $iul->getLogo2TmpImage($params['NewLogoSeq']);
                if (!is_null($logo2_data)) {
                    $params['SmallLogo']['image'] = $logo2_data['ImageData'];
                    $params['SmallLogo']['seq'] = $params['NewLogoSeq'];
                }
            } else {
                $mdlSite = new TableSite($this->app->dbAdapter);
                $params['SmallLogo']['image'] = $mdlSite->findSite($params['SiteId'])->current()['SmallLogo'];
            }
            if ( $errors_smallLogo ) {
                $errors['SmallLogo'] = $errors_smallLogo;
            } else {
                $errors['LogoSeq'] = $logo2_seq;
            }
        } else {
            if ( $params['NewLogoSeq'] && $params['NewLogoSeq'] != "" ) {
                //ロゴ2取得
                $logo2_data = $iul->getLogo2TmpImage($params['NewLogoSeq']);
                if (!is_null($logo2_data)) {
                    $params['SmallLogo']['image'] = $logo2_data['ImageData'];
                    $params['SmallLogo']['seq'] = $params['NewLogoSeq'];
                }
            } else {
                $mdlSite = new TableSite($this->app->dbAdapter);
                $params['SmallLogo']['image'] = $mdlSite->findSite($params['SiteId'])->current()['SmallLogo'];
            }
            if ( $errors_smallLogo ) {
                $errors['SmallLogo'] = $errors_smallLogo;
            }
        }
        if (!$errors && $this->_saveTodo($params)) {
            $this->_redirect('site/list/eid/'.$params['eid']);
        }

        return $errors;
    }

    /**
     * 確認画面からの戻り処理
     */
    public function backAction() {

        $params = $this->getParams();
$this->app->logger->debug('backAction:');
$this->app->logger->debug($params);

        // エンコード済みのPOSTデータを復元する
        $eData = unserialize(base64_decode($params['hash']));

        $enterpriseId = (isset($eData['EnterpriseId'])) ? $eData['EnterpriseId'] : -1;
        $siteId = (isset($eData['SiteId'])) ? $eData['SiteId'] : -1;

        $mdlEnterprise = new TableEnterprise($this->app->dbAdapter);
        $mdlSite = new TableSite($this->app->dbAdapter);
        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
//        $mdlDelMthd = new TableDeliMethod($this->app->dbAdapter);
        $mdlCode = new TableCode($this->app->dbAdapter);
        $mdlPricePlan = new TablePricePlan($this->app->dbAdapter);
        $mdlOem = new TableOem($this->app->dbAdapter);
        $mdlCvs = new TableCvsReceiptAgent($this->app->dbAdapter);
        $mdlPayment = new TablePayment($this->app->dbAdapter);

        // 事業者名の取得・アサイン
        $enterpriseData = $mdlEnterprise->findEnterprise($enterpriseId)->current();
        $this->view->assign("EnterpriseNameKj", $enterpriseData['EnterpriseNameKj']);

        //サイト形態取得
        $datas['SiteForms'] = $codeMaster->getSiteFormMaster();

        //利用プラン取得
        $pricePlan = $mdlPricePlan->find($enterpriseData['Plan'])->current();
        $datas['PlanName'] = $pricePlan['PricePlanName'];
        //OEM情報取得
        $oem = $mdlOem->find($enterpriseData['OemId'])->current();
        $oemData = array();
        if ($oem) {
            $oemData['ClaimFeeBS'] = $oem['ClaimFeeBS'];
            $oemData['ClaimFeeDK'] = $oem['ClaimFeeDK'];
            $feelist = Json::decode($oem['SettlementFeeRatePlan'], Json::TYPE_ARRAY);
            $oemData['SettlementFeeRate'] = $feelist[$enterpriseData['Plan']];
            $oemData['FirstCreditTransferClaimFeeOem'] = $oem['FirstCreditTransferClaimFeeOem'];
            $oemData['FirstCreditTransferClaimFeeWebOem'] = $oem['FirstCreditTransferClaimFeeWebOem'];
            $oemData['CreditTransferClaimFeeOem'] = $oem['CreditTransferClaimFeeOem'];
        }
        //金額情報
        $reference = array();
        $reference['SettlementAmountLimit'] = $pricePlan['SettlementAmountLimit'];
        $reference['SettlementFeeRate'] = $pricePlan['SettlementFeeRate'];
        $reference['ClaimFeeBS'] = $pricePlan['ClaimFeeBS'];
        $reference['ClaimFeeDK'] = $pricePlan['ClaimFeeDK'];
        $reference['ReClaimFee']  = $pricePlan['ReClaimFee'];
        $reference['OemClaimFeeBS'] = $oemData['ClaimFeeBS'];
        $reference['OemClaimFeeDK'] = $oemData['ClaimFeeDK'];
        $reference['OemSettlementFeeRate'] = $oemData['SettlementFeeRate'];
        $reference['FirstCreditTransferClaimFeeOem'] = $oemData['FirstCreditTransferClaimFeeOem'];
        $reference['FirstCreditTransferClaimFeeWebOem'] = $oemData['FirstCreditTransferClaimFeeWebOem'];
        $reference['CreditTransferClaimFeeOem'] = $oemData['CreditTransferClaimFeeOem'];
        $datas['Reference'] = $reference;

        //与信判定基準取得
        $sql  = " SELECT DISTINCT CreditCriterionId, CreditCriterionName  FROM M_CreditPoint ";
        $ri = $this->app->dbAdapter->query($sql)->execute(null);
        $datas['CreditCriterionNames'] = ResultInterfaceToArray($ri);
        $datas['CreditCriterionNum'] = $eData['CreditCriterionNum'];
        //与信判定方法
        $data=array();
        foreach ($mdlCode->getMasterByClass(94) as $row){
            $data[] = $row['KeyContent'];
        }
        $datas['Creditdecision'] = $data;

        //自動伝票番号登録時配送先取得
        $lgc = new \models\Logic\LogicDeliveryMethod($this->app->dbAdapter);
        $datas['DeliMethodName'] = $lgc->getEnterpriseDeliveryMethodList($enterpriseId, false);

        //請求書用紙種類取得
        $data=array();
        foreach ($mdlCode->getMasterByClass(79) as $row){
            $data[$row['KeyCode']] = $row['KeyContent'];
        }
        $datas['Invoice'] = $data;

        //請求書用紙種類取得（同梱）
        $data=array();
        foreach ($mdlCode->getMasterByClass(106) as $row){
            $data[$row['KeyCode']] = $row['KeyContent'];
        }
        $datas['InvoiceDK'] = $data;

        // CB_B2C_DEV-14
        $datas['ReceiptAgentForms'] = $mdlCvs->getEnterpriseCvsReceiptAgentList($enterpriseId,false);
        //
        if(!empty($site['SubscriberCode'])){
        	$datas['SubscriberCodeSearchView'] = $this->searchsubscriberAction($eData);
        }

        $firstClaimLayoutModeCtl = $mdlCode->find(160, $enterpriseData['OemId'])->current()['Class7'];

        // サイトに紐づくAPIユーザ取得
        $sql = <<<EOQ
            SELECT  au.ApiUserId
                ,   au.ValidFlg
                ,   au.ApiUserNameKj
            FROM    T_ApiUser au
                    INNER JOIN T_ApiUserEnterprise ae ON (au.ApiUserId = ae.ApiUserId)
                    INNER JOIN T_Site s ON (ae.SiteId = s.SiteId)
            WHERE   s.SiteId = :SiteId
            ;
EOQ;
        $ri = $this->app->dbAdapter->query($sql)->execute(array( ':SiteId' => $siteId ));
        $apidata = ResultInterfaceToArray($ri);

        // キャンペーン情報取得
        // サイトIDを条件にキャンペーン情報を取得する。
        $sql = <<<EOQ
            SELECT  ec.Seq
                ,   pp.PricePlanName
                ,   ec.DateFrom
                ,   ec.DateTo
                ,   ec.MonthlyFee
            FROM    T_EnterpriseCampaign ec
                    INNER JOIN M_PricePlan pp ON (pp.PricePlanId = ec.AppPlan)
            WHERE   SiteId = :SiteId
            ;
EOQ;
        $ri = $this->app->dbAdapter->query($sql)->execute(array( ':SiteId' => $siteId ));
        $campaign = ResultInterfaceToArray($ri);

        // 全案件補償外ダイアログ対応
        $sql = " SELECT PropValue FROM T_SystemProperty WHERE Module = :Module  AND Category = :Category AND Name = :Name ";
        $prm = array(':Module' => '[DEFAULT]', ':Category' => 'systeminfo', ':Name' => 'AllowOutOfAmendsName');
        $allowOutOfAmendsName =  $this->app->dbAdapter->query($sql)->execute($prm)->current()['PropValue'];

        // ロゴ(小)
        $eData['SmallLogo'] = $mdlSite->findSite($siteId)->current()['SmallLogo'];
        if(isset($params['image_uploaded']) ? $params['image_uploaded'] : false) {
            $errors['SmallLogo'] = 'アップロードされた画像は破棄されました。もう一度選択してください';
            $this->view->assign('error', $errors);
        }

        // 自由項目
        $datas['FreeItemsL'] = '設定なし';
        $datas['FreeItemsV'] = '設定する';
        $hashFreeItems = array();
        for ($i = 1; $i <= 20; $i++) {
            if ( isset($eData['Free'. $i]) && !empty($eData['Free'. $i]) )  {
                $datas['FreeItemsL'] = '設定あり';
                $datas['FreeItemsV'] = '変更する';
                $hashFreeItems['Free'. $i] = $eData['Free'. $i];
            } else {
                $hashFreeItems['Free'. $i] = '';
            }
        }
        // 自由項目をエンコード
        $formData = base64_encode(serialize($hashFreeItems));

        // 支払方法取得
        if (is_null($enterpriseData['OemId'])) {
            $oemId = 0;
        } else {
            $oemId = $enterpriseData['OemId'];
        }
        $payments = ResultInterfaceToArray($mdlPayment->fetchAllSubscriberCode($oemId));

        // セッションデータを破棄
        unset($_SESSION['TMP_IMAGE_SEQ']);

        $this->view->assign('list', array_merge($eData, $datas));
        $this->view->assign('EnterpriseId', $enterpriseId);
        $this->view->assign('eid', $enterpriseId);
        $this->view->assign('apiuserlist', $apidata);
        $this->view->assign('campaign', $campaign);
        $this->view->assign('SelfBillingMode', $enterpriseData['SelfBillingMode']);
        $this->view->assign('BillingAgentFlg', $enterpriseData['BillingAgentFlg']);
        $this->view->assign('AllowOutOfAmendsName', $allowOutOfAmendsName);
        $this->view->assign('hashFreeItems', $formData);
        $this->view->assign('payments', $payments);
        $this->view->assign('firstClaimLayoutModeCtl', $firstClaimLayoutModeCtl);
        $this->view->assign('defaultClaimMypagePrint', $mdlCode->find(160,$enterpriseData['OemId'])->current()['Class5']);

        $this->setTemplate('edit');
        return $this->view;
    }

    /**
     * saveAction
     * 新規・編集の永続化処理
     */
    public function saveAction()
    {
        $params = $this->getParams();

        // エンコード済みのPOSTデータを復元する
        $list = unserialize(base64_decode($params['hash']));

        $site = new TableSite($this->app->dbAdapter);
//        $ent = new TableEnterprise($this->app->dbAdapter);
        $mdlec = new TableEnterpriseCampaign($this->app->dbAdapter);
        $mdlatec = new \models\Table\ATableEnterpriseCampaign($this->app->dbAdapter);
        $mdlsfi = new TableSiteFreeItems( $this->app->dbAdapter );
        $mdlsp = new TableSitePayment($this->app->dbAdapter);
        $mdlPayment = new TablePayment($this->app->dbAdapter);
        $mdlSc = new TableSubscriberCode($this->app->dbAdapter);
        $mdlEnterprise = new TableEnterprise($this->app->dbAdapter);
        $mdlSitePayment = new TableSitePayment($this->app->dbAdapter);

        //加盟店ID
        $eid = $list['EnterpriseId'];
        $enterpriseData = $mdlEnterprise->findEnterprise($eid)->current();

        // ロゴ(小)
        if(!empty($list['SmallLogo']['seq'])){
            $iul = new \models\Logic\LogicImageUpLoader($this->app->dbAdapter);
            $logo2_data = $iul->getLogo2TmpImage($list['SmallLogo']['seq']);

            if(empty($logo2_data)){
                unset($list['SmallLogo']);
            }
            else {
                $list['SmallLogo'] = $logo2_data['ImageData'];
            }
        }
        else{
            unset($list['SmallLogo']);
        }

        if (! is_numeric($list['ReissueCount'])) {
            $list['ReissueCount'] = null;
        }

        //サイトID
        $sid = $list['SiteId'];

        // サイト用更新項目
        $slist = array();
        foreach($list as $key => $value) {
            if ($key == 'Payment') {
                continue;
            }
            // 未入力の項目はnullで登録・更新
            $slist[$key] = strlen($value) == 0 ? null : $value;
            // 再請求手数料は未入力の場合は 0 で更新する
            if (($key == 'ReClaimFee')
             || ($key == 'ReClaimFee1') || ($key == 'ReClaimFee3') || ($key == 'ReClaimFee4')
             || ($key == 'ReClaimFee5') || ($key == 'ReClaimFee6') || ($key == 'ReClaimFee7')
               ) {
                $slist[$key] = strlen($value) == 0 ? 0 : $value;
            }
        }
        $slist = array_merge($slist, array( 'CreditCriterion' => $slist['CreditCriterionNum'] ));

        // 再請求手数料設定種別によって値を消すための要素を設定
        if ($slist['ReClaimFeeSetting'] == 0) {
            $slist['ReClaimFee1']               = 0;
            $slist['ReClaimFee3']               = 0;
            $slist['ReClaimFee4']               = 0;
            $slist['ReClaimFee5']               = 0;
            $slist['ReClaimFee6']               = 0;
            $slist['ReClaimFee7']               = 0;
            $slist['ReClaimFeeStartRegistDate'] = null;
            $slist['ReClaimFeeStartDate']       = null;
        }

        if (! empty($sid)) {
            $sdata = $site->findSite($sid)->current();
            $slist = array_merge($slist, array( 'JintecJudge' => $sdata['JintecJudge'] ));
            $slist = array_merge($slist, array( 'JintecJudge0' => $sdata['JintecJudge0'] ));
            $slist = array_merge($slist, array( 'JintecJudge1' => $sdata['JintecJudge1'] ));
            $slist = array_merge($slist, array( 'JintecJudge2' => $sdata['JintecJudge2'] ));
            $slist = array_merge($slist, array( 'JintecJudge3' => $sdata['JintecJudge3'] ));
            $slist = array_merge($slist, array( 'JintecJudge4' => $sdata['JintecJudge4'] ));
            $slist = array_merge($slist, array( 'JintecJudge5' => $sdata['JintecJudge5'] ));
            $slist = array_merge($slist, array( 'JintecJudge6' => $sdata['JintecJudge6'] ));
            $slist = array_merge($slist, array( 'JintecJudge7' => $sdata['JintecJudge7'] ));
            $slist = array_merge($slist, array( 'JintecJudge8' => $sdata['JintecJudge8'] ));
            $slist = array_merge($slist, array( 'JintecJudge9' => $sdata['JintecJudge9'] ));
            $slist = array_merge($slist, array( 'JintecJudge10' => $sdata['JintecJudge10'] ));
        }

        // トランザクション開始
        $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
        try {
            // ユーザIDの取得
            $obj = new TableUser($this->app->dbAdapter);
            $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

            // 永続化実行
            if (! empty($sid)) {
                $site->saveUpdate(array_merge($slist, array( 'UpdateId' => $userId )), $sid);
            } else {
                $sid = $site->saveNew(array_merge($slist, array('UpdateId' => $userId, 'RegistId' => $userId)));

                // 加盟店キャンペーン登録
                // (有効キャンペーン情報取得)
                $sql = <<<EOQ
SELECT *
FROM   T_EnterpriseCampaign
WHERE  EnterpriseId = :EnterpriseId
AND    DateTo >= :DateTo
AND    SiteId = (SELECT IFNULL(MIN(SiteId),0) FROM T_EnterpriseCampaign WHERE EnterpriseId = :EnterpriseId)
ORDER BY Seq
EOQ;
                $ri = $this->app->dbAdapter->query($sql)->execute(array(':EnterpriseId' => $eid, ':DateTo' => date('Y-m-d')));
                if ($ri->count() > 0) {
                    // サイト情報取得
                    $row_site = $this->app->dbAdapter->query(" SELECT * FROM T_Site WHERE SiteId = :SiteId ")->execute(array(':SiteId' => $sid))->current();

                    $ary_init = array(
                            'SettlementAmountLimit'     => $row_site['SettlementAmountLimit'],
                            'SettlementFeeRate'         => $row_site['SettlementFeeRate'],
                            'ClaimFeeDK'                => $row_site['ClaimFeeDK'],
                            'ClaimFeeBS'                => $row_site['ClaimFeeBS'],
                            'ReClaimFeeSetting'         => $row_site['ReClaimFeeSetting'],
                            'ReClaimFee'                => $row_site['ReClaimFee'],
                            'ReClaimFee1'               => $row_site['ReClaimFee1'],
                            'ReClaimFee3'               => $row_site['ReClaimFee3'],
                            'ReClaimFee4'               => $row_site['ReClaimFee4'],
                            'ReClaimFee5'               => $row_site['ReClaimFee5'],
                            'ReClaimFee6'               => $row_site['ReClaimFee6'],
                            'ReClaimFee7'               => $row_site['ReClaimFee7'],
                            'ReClaimFeeStartRegistDate' => $row_site['ReClaimFeeStartRegistDate'],
                            'ReClaimFeeStartDate'       => $row_site['ReClaimFeeStartDate'],
                            'OemSettlementFeeRate'      => $row_site['OemSettlementFeeRate'],
                            'OemClaimFee'               => $row_site['OemClaimFee'],
                            'SystemFee'                 => $row_site['SystemFee'],
                            'SiteId'                    => $sid,
                            'RegistDate'                => date('Y-m-d H:i:s'),
                            'RegistId'                  => $userId,
                            'UpdateDate'                => date('Y-m-d H:i:s'),
                            'UpdateId'                  => $userId,
                    );

                    foreach ($ri as $row) {
                        // レコードをINSERT
                        $newSeq = $mdlec->saveNew(array_merge($row, $ary_init));
                        // AT_EnterpriseCampaign登録
                        $mdlatec->saveNew(array('Seq' => $newSeq));
                    }
                }
                else { ; }  // 有効なキャンペーンが存在がない場合は登録不要(の明示)

                // 自由項目（新規登録時のみ、更新は編集画面で）
                $list['SiteId'] = $sid;
                $mdlsfi->saveNew($list);
            }

            // 支払方法取得
            if (is_null($enterpriseData['OemId'])) {
                $oemId = 0;
            } else {
                $oemId = $enterpriseData['OemId'];
            }
            $payments = ResultInterfaceToArray($mdlPayment->fetchAllSubscriberCode($oemId));

            // LINE Pay ID取得
            $line_pay = 3;
            foreach ($payments as $payment) {
                $paymentId = $payment['PaymentId'];
                switch ($payment['FixedId']) {
                    case 3:
                        $line_pay = $paymentId;
                        break;
                }
            }

            // 支払方法取得
            $useStartFixFlg = 0;
            $site_datas = ResultInterfaceToArray($mdlSitePayment->getAll($sid));
            foreach ($site_datas as $site_data) {
                if ($site_data['PaymentId'] != $line_pay) {
                    continue;
                }
                $useStartFixFlg = $site_data['UseStartFixFlg'];
            }

            // サイト支払方法の更新
            $mdlsp->save($sid, $userId, $list['Payment']);

            // 加入者固有コード管理マスタ
            foreach ($list['Payment'] as $key => $payment) {
                if ($key != $line_pay) {
                    continue;
                }

                // 本来はLINE使用可否区分が9：未申請の場合のみCallすればよいが、面倒なので全てCallする
                $subscriberCodeData = array(
                    'SubscriberName' => '仮登録中',
                    'LinePayUseFlg' => $payment['UseFlg'],
                    'LineApplyDate' => $payment['ApplyDate'],
                );
                $result = $mdlSc->saveUnappliedSubscriberCode($subscriberCodeData, $userId, $list['ReceiptAgentId'], $list['SubscriberCode']);
                $SubscriberFlg = false;
                if (($result->count() == 0) && ($list['SubscriberFlg'] == 9)) {
                    $SubscriberFlg = true;
                }

                // はじめて確定になったタイミングで加入者固有コード管理マスタとサイト支払方法を更新する
                if (($useStartFixFlg == 0) && ($list['Payment'][$line_pay]['UseStartFixFlg'] == 1)) {
                    $work = $mdlSc->findReceiptAgentIdSubscriberCode($list['ReceiptAgentId'], $list['SubscriberCode']);
                    if (is_null($work['LineUseStartDate'])) {
                        // 入者固有コード管理マスタの更新
                        $subscriberCodeData = array(
                            'LineUseStartDate' => $payment['UseStartDate'],
                        );
                        $mdlSc->saveLineUseStartDate($subscriberCodeData, $userId, $list['ReceiptAgentId'], $list['SubscriberCode']);

                        // サイト支払方法の更新
                        $site_data = array(
                            'UseStartDate' => $payment['UseStartDate'],
                            'PaymentId' => $line_pay,
                            'ReceiptAgentId' => $list['ReceiptAgentId'],
                            'SubscriberCode' => $list['SubscriberCode'],
                        );
                        $mdlsp->saveAnotherSite($sid, $userId, $site_data);
                    }
                }
            }

            // 印刷パターン更新
            $mdlCode = new TableCode($this->app->dbAdapter);
            $is_saved = $mdlCode->find2(214, $eid)->count();
            if ($is_saved == 0) {
                // 印刷パターン更新除外特定加盟店でない場合は更新する
                $claimPrintPattern = new TableClaimPrintPattern($this->app->dbAdapter);
                $claimPrint = new LogicClaimPrint($this->app->dbAdapter);
                $claimPatterns = array(0,1,2,4,6,7,8,9);
                if ($enterpriseData['CreditTransferFlg'] == 0) {
                    $claimPatterns = array(1,2,4,6,7,8,9);
                }
                if ($enterpriseData['BillingAgentFlg'] == 1) {
                    $claimPatterns = array(1);
                }
                foreach ($claimPatterns as $claimPattern) {
                    $data = $claimPrint->create($oemId, $eid, $sid, $claimPattern, $list['PaymentAfterArrivalFlg'], $list['FirstClaimLayoutMode'], $list['MufjBarcodeUsedFlg'], $list['ClaimMypagePrint']);
                    if ($data['ErrorCd'] == 1) {
                        throw new \Exception('印刷パターンの組み合わせが正しくありません。登録することができませんので、システム課にご連絡ください。'."\n".$data['ErrorMsg']);
                    }
                    if ($data['ErrorCd'] == 2) {
                        throw new \Exception('支払方法の組み合わせが正しくありません。登録することができませんので、システム課にご連絡ください。'."\n".$data['ErrorMsg']);
                    }
                    $work = $claimPrintPattern->find($eid, $sid, $data['PrintIssueCountCd']);
                    if ($work->count() > 0) {
                        $pkey = $work->current()['ClaimPrintPatternSeq'];
                        $data['EnterpriseId'] = $eid;
                        $data['SiteId'] = $sid;
                        $data['UpdateId'] = $userId;
                        $claimPrintPattern->saveUpdate($data, $pkey);
                    } else {
                        $data['EnterpriseId'] = $eid;
                        $data['SiteId'] = $sid;
                        $data['RegistId'] = $userId;
                        $claimPrintPattern->saveNew($data);
                    }
                }
            }

            // コミット
            $this->app->dbAdapter->getDriver()->getConnection()->commit();
        } catch (\Exception $e) {
            // ロールバック
            $this->app->dbAdapter->getDriver()->getConnection()->rollback();
            throw $e;
        }

        $this->view->assign('SubscriberFlg', $SubscriberFlg);
        $this->view->assign('list', $slist);
        $this->view->assign('sid', $sid);
        $this->view->assign('EnterpriseId', $eid);

        // 完了画面を表示する
        $this->setTemplate('completion');

        return $this->view;
    }

    protected function _saveTodo($data = array())
    {
        $saveOk = false;
        
        $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
        try {
            // get id of user login
            $obj = new TableUser($this->app->dbAdapter);
            $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

            // process logo
            if (!empty($data['SmallLogo']['seq'])) {
                $iul = new \models\Logic\LogicImageUpLoader($this->app->dbAdapter);
                $logo2_data = $iul->getLogo2TmpImage($data['SmallLogo']['seq']);
                if (empty($logo2_data)) {
                    unset($data['SmallLogo']);
                } else {
                    $data['SmallLogo'] = $logo2_data['ImageData'];
                }
            } else {
                unset($data['SmallLogo']);
            }

            // get site data by id
            $tblSite = new TableSite($this->app->dbAdapter);
            $siteData = $tblSite->findSite($data['SiteId'])->current();

            // update T_Site
            $sql = <<<EOQ
UPDATE T_Site
SET PaymentAfterArrivalFlg = :PaymentAfterArrivalFlg,
    PaymentAfterArrivalName = :PaymentAfterArrivalName,
    SpecificTransUrl = :SpecificTransUrl,
    ReceiptUsedFlg = :ReceiptUsedFlg,
    ReceiptIssueProviso = :ReceiptIssueProviso,
    SmallLogo = :SmallLogo,
    MerchantId = :MerchantId,
    ServiceId = :ServiceId,
    HashKey = :HashKey,
    BasicId = :BasicId,
    BasicPw = :BasicPw,
    UpdateId = :UpdateId
WHERE  SiteId = :SiteId
EOQ;
            $prm = array(
                ':PaymentAfterArrivalFlg' => $data['PaymentAfterArrivalFlg'] ? 1 : 0,
                ':PaymentAfterArrivalName' => $data['PaymentAfterArrivalName'],
                ':SpecificTransUrl' => $data['SpecificTransUrl'],
                ':ReceiptUsedFlg' => $data['ReceiptUsedFlg'],
                ':ReceiptIssueProviso' => $data['ReceiptIssueProviso'],
                ':SmallLogo' => isset($data['SmallLogo']) ? $data['SmallLogo'] : $siteData['SmallLogo'],
                ':MerchantId' => $data['MerchantId'],
                ':ServiceId' => $data['ServiceId'],
                ':HashKey' => $data['HashKey'],
                ':BasicId' => $data['BasicId'],
                ':BasicPw' => $data['BasicPw'],
                ':UpdateId' => $userId,
                ':SiteId' => $data['SiteId'],
            );
            $ri = $this->app->dbAdapter->query($sql)->execute($prm);

            // update T_SiteSbpsPayment
            $tblSPT = new TableSiteSbpsPayment($this->app->dbAdapter);
            $tblSPT->handle($data['SiteId'], $userId, $data['Payment']);

            $flg = $data['PaymentAfterArrivalFlg'] ? 1 : 0;
            if ($flg == 0) {
//                $sites = ResultInterfaceToArray($tblSite->getAll($data['eid']));
//                $temp = false;
//                foreach ($sites as $w) {
//                    if ($w['PaymentAfterArrivalFlg'] == 1) {
//                        $temp = true;
//                        break;
//                    }
//                }
//                if (!$temp) {
                    $mdltf = new TableTemplateField($this->app->dbAdapter);
                    $sql = <<<EOQ
SELECT F.TemplateSeq
,      F.ListNumber
FROM   M_TemplateHeader H
       INNER JOIN M_TemplateField F ON F.TemplateSeq = H.TemplateSeq
WHERE  H.TemplateId    = 'CKA01005_1'
AND    H.TemplateClass = 2
AND    H.Seq           = :EnterpriseId
AND    H.TemplatePattern = :SiteId
AND    (F.PhysicalName = 'ExtraPayKey')
AND    F.ValidFlg      = 1
EOQ;
                    $ri = $this->app->dbAdapter->query($sql)->execute(array( 'EnterpriseId' => $data['eid'], 'SiteId' => $data['SiteId'] ));
                    $fields = ResultInterfaceToArray($ri);
                    foreach($fields as $field) {
                        $mdltf->saveUpdate(array(
                                               'ValidFlg' => 0,
                                               'UpdateId' => $userId,
                                           ), $field['TemplateSeq'], $field['ListNumber']);
                    }
//                }
            }

            $saveOk = true;
            $this->app->dbAdapter->getDriver()->getConnection()->commit();
        } catch (\Exception $e) {
            // ロールバック
            $this->app->dbAdapter->getDriver()->getConnection()->rollback();
            throw $e;
        }
        return $saveOk;
    }

    /**
     * 代理店一覧表示
     */
    public function siteagencyAction()
    {
        $params = $this->getParams();

        $sid = $params['sid'];      // サイトID

        // サイトIDから加盟店IDを取得する。(合わせて[サイト名][加盟店名]を取得)
        $sql = " SELECT s.EnterpriseId, s.SiteNameKj, e.EnterpriseNameKj FROM T_Site s INNER JOIN T_Enterprise e ON (e.EnterpriseId = s.EnterpriseId) WHERE s.SiteId = :SiteId; ";
        $row = $this->app->dbAdapter->query($sql)->execute(array( ':SiteId' => $sid ))->current();
        $eid = $row['EnterpriseId'];
        $siteNameKj = $row['SiteNameKj'];
        $enterpriseNameKj = $row['EnterpriseNameKj'];

        // 代理店リストを取得
        $sql = <<<EOQ
            SELECT  DISTINCT ma.AgencyId
                ,   ma.AgencyNameKj
            FROM    M_Agency ma
                    INNER JOIN T_Enterprise e ON (IFNULL(e.OemId, 0) = ma.OemId)
            WHERE   e.EnterpriseId = :EnterpriseId
            ;
EOQ;

        $ri = $this->app->dbAdapter->query($sql)->execute(array( ':EnterpriseId' => $eid ));
        $agencyList = ResultInterfaceToArray($ri);

        // 選択したサイトに紐づく代理店情報を取得
        $sql = " SELECT * FROM M_AgencySite WHERE SiteId = :SiteId; ";
        $ri = $this->app->dbAdapter->query($sql)->execute(array( ':SiteId' => $sid ));
        $agencySite = ResultInterfaceToArray($ri);

        // ビューへアサイン
        $this->view->assign('agencylist', $agencyList);
        $this->view->assign('list', $agencySite);
        $this->view->assign('sid', $sid);
        $this->view->assign('eid', $eid);
        $this->view->assign('EnterpriseId', $eid);
        $this->view->assign('siteNameKj', $siteNameKj);
        $this->view->assign('enterpriseNameKj', $enterpriseNameKj);

        return $this->view;
    }

    /**
     * 代理店一覧更新処理
     */
    public function agencyconfirmAction()
    {
        $updatecount = 0;       // 更新件数カウント用

        // トランザクション開始
        $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
        try {
            // パラメータ取得
            $params = $this->getPureParams();

            $delAgencyIds = array();
            $agencyIds = array();
            $agencyFeeRates = array();
            $agencyDivideFeeRates = array();
            $monthlyFees = array();

            foreach ($params as $key => $param) {
                // $key から AgencyId を取得
                if (strstr($key, 'item_delete_chk_') != false) {
                    $delAgencyIds[] = str_replace('item_delete_chk_', '', $key);
                // 代理店
                } else if (strstr( $key, 'Agency_' ) != false) {
                    $agencyIds[str_replace('Agency_', '', $key)] = $param;
                // 代理店手数料率
                } else if (strstr( $key, 'AgencyFeeRate_' ) != false) {
                    $agencyFeeRates[str_replace('AgencyFeeRate_', '', $key)] = $param;
                // 代理店手数料按分比率
                } else if (strstr( $key, 'AgencyDivideFeeRate_' ) != false) {
                    $agencyDivideFeeRates[str_replace('AgencyDivideFeeRate_', '', $key)] = $param;
                // 月額固定費
                } else if (strstr( $key, 'MonthlyFee_' ) != false) {
                    $monthlyFees[str_replace('MonthlyFee_', '', $key)] = $param;
                // サイトID
                } else if ($key == 'sid') {
                    $sid = $param;
                // 加盟店ID
                } else if ($key == 'eid') {
                    $eid = $param;
                }
            }

            // ユーザIDの取得
            $obj = new TableUser($this->app->dbAdapter);
            $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

            // 代理店サイト関連マスタはDELETE → INSERT する。
            $mdlmas = new TableAgencySite($this->app->dbAdapter);

            // 代理店サイト関連マスタに該当のサイトIDのデータが存在するか確認
            $sql = " SELECT * FROM M_AgencySite WHERE SiteId = :SiteId ";
            $list = ResultInterfaceToArray($this->app->dbAdapter->query($sql)->execute(array( ':SiteId' => $sid )));
            // 存在していれば、該当のサイトIDを条件に 代理店サイト関連マスタを DELETE する
            // count関数対策
            if (!empty($list)) {
                foreach ($list as $value) {
                    // 加盟店ID、サイトIDを条件にDELETE
                    $mdlmas->delete($value['AgencyId'], $sid);
                }
            }
            // 代理店サイト関連マスタの更新
            foreach ($agencyFeeRates as $key => $agencyFeeRate) {
                if (!empty($key)) {
                    // 更新実行フラグ
                    $updateFlg = true;
                    foreach ($delAgencyIds as $deleteId) {
                        // $delAgencyIds に含まれていなければ更新対象
                        if ($key == $deleteId) {
                            $updateFlg = false;
                            break;
                        }
                    }

                    if ($updateFlg) {
                        $serialNumber = $key;
                        $feeRate = $agencyFeeRates[$serialNumber];
                        $divideFeeRate = $agencyDivideFeeRates[$serialNumber];
                        $agencyId = $agencyIds[$serialNumber];
                        $monthlyFee = $monthlyFees[$serialNumber];
                        // 代理店が選択されていない場合、エラー
                        if ($agencyId < 0) {
                            throw new \Exception('代理店を選択してください。');
                        }
                        // 代理店手数料率が入力されていない場合、エラー
                        if ($feeRate == '') {
                            throw new \Exception('代理店手数料率が入力されていません。');
                        // 入力された値が数値に変換できない場合、エラー
                        } else if (! is_numeric($feeRate)) {
                            throw new \Exception('代理店手数料率は数値で入力してください。');
                        }
                        // 代理店手数料按分比率が入力されていない場合、エラー
                        if ($divideFeeRate == '') {
                            throw new \Exception('代理店手数料按分比率が入力されていません。');
                        // 入力された値が数値に変換できない場合、エラー
                        } else if (! is_numeric($divideFeeRate)) {
                            throw new \Exception('代理店手数料按分比率は数値で入力してください。');
                        }
                        // 月額固定費が入力されていない場合、エラー
                        if ($monthlyFee == '') {
                            throw new \Exception('月額固定費が入力されていません。');
                            // 入力された値が数値に変換できない場合、エラー
                        } else if (! is_numeric($monthlyFee)) {
                            throw new \Exception('月額固定費は数値で入力してください。');
                        }

                        // 削除対象行以外の行を全てINSERTする
                        // INSERT用データ配列作成
                        $data = array(
                            'AgencyId' => $agencyId,                    // 代理店ID
                            'SiteId'  => $sid,                          // サイトID
                            'AgencyFeeRate' => $feeRate,                // 代理店手数料率
                            'AgencyDivideFeeRate' => $divideFeeRate,    // 代理店手数料按分比率
                            'EnterpriseId' => $eid,                     // 加盟店ID
                            'MonthlyFee' => $monthlyFee,                // 月額固定費
                            'RegistId' => $userId,                      // 登録者
                            'UpdateId' => $userId,                      // 更新者
                        );

                        // INSERT
                        $mdlmas->saveNew($data);

                        // 更新件数カウントアップ
                        $updatecount++;
                    }
                }
            }

            // コミット
            $this->app->dbAdapter->getDriver()->getConnection()->commit();

            if (! isset($msg)){
                // 成功指示
                $msg = '1';
            }
        } catch (\Exception $e) {
            // ロールバック
            $this->app->dbAdapter->getDriver()->getConnection()->rollback();
            $msg = $e->getMessage();
            if (false !== strpos($msg, 'Duplicate')) {
                // MySQLからの重複エラーのキャッチ処理
                $msg = '代理店一覧が重複しています。';
            }
        }

        echo \Zend\Json\Json::encode(array('status' => $msg, 'updatecount' => $updatecount));
        return $this->response;
    }

    /**
     * キャンペーン設定一覧
     */
    public function campaignAction()
    {
        $params = $this->getParams();

        $seq = $params['seq'];

        // 選択されたキャンペーン情報を取得する。
        $mdlec = new TableEnterpriseCampaign($this->app->dbAdapter);
        $campaign = $mdlec->find($seq)->current();

        // 料金プランマスタのデータを取得する
        $mdlpp = new TablePricePlan($this->app->dbAdapter);
        $plan = ResultInterfaceToArray($mdlpp->getAll());

        // 立替サイクルマスタのデータを取得する
        $mdlpc = new TablePayingCycle($this->app->dbAdapter);
        $paying = ResultInterfaceToArray($mdlpc->findAll());

        $this->view->assign('seq', $seq);
        $this->view->assign('data', $campaign);
        $this->view->assign('plan', $plan);
        $this->view->assign('paying', $paying);

        return $this->view;
    }

    /**
     * キャンペーン設定更新処理
     */
    public function campaigndoneAction()
    {
        $params = $this->getParams();

        $data = $params['form'];
        $seq = $params['seq'];

        // 選択されたキャンペーン情報を取得する。
        $mdlec = new TableEnterpriseCampaign($this->app->dbAdapter);
        $campaign = $mdlec->find($seq)->current();

        // 料金プランマスタのデータを取得する
        $mdlpp = new TablePricePlan($this->app->dbAdapter);
        $plan = ResultInterfaceToArray($mdlpp->getAll());

        // 立替サイクルマスタのデータを取得する
        $mdlpc = new TablePayingCycle($this->app->dbAdapter);
        $paying = ResultInterfaceToArray($mdlpc->findAll());

        // OEMIDを取得
        $mdle = new TableEnterprise($this->app->dbAdapter);
        $oemId = $mdle->find($campaign['EnterpriseId'])->current()['OemId'];

        // 入力検証
        $errors = $this->validateForCampaign(array_merge($data, array( 'OemId' => $oemId )));

        if (!empty($errors)) {
            $this->view->assign('error', $errors);
            $this->view->assign('plan', $plan);
            $this->view->assign('paying', $paying);
            $this->view->assign('data', array_merge($campaign, $data));
            $this->view->assign('seq', $seq);

            $this->setTemplate('campaign');

            return $this->view;
        }

        // エラーがなかったら後続処理
        $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
        try {
            // ユーザIDの取得
            $obj = new TableUser($this->app->dbAdapter);
            $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

            // 更新用処理
            $mdlec->saveUpdate(array_merge($data, array( 'UpdateId' => $userId )), $seq);

            // コミット
            $this->app->dbAdapter->getDriver()->getConnection()->commit();
        } catch (\Exception $e) {
            // ロールバック
            $this->app->dbAdapter->getDriver()->getConnection()->rollback();
            throw $e;
        }

        // OEM決済手数料率(税込)
        $obj = new \models\Table\TableSystemProperty($this->app->dbAdapter);
        $data['OemSettlementFeeRateZeikomi'] =$obj->getIncludeTaxRate(date('Y-m-d'), nvl($data['OemSettlementFeeRate'], 0), 3, 1);

        $this->view->assign('data', array_merge($campaign, $data));
        $this->view->assign('comp', true);
        $this->view->assign('plan', $plan);
        $this->view->assign('paying', $paying);
        $this->view->assign('compMsg', sprintf('<font color="red"><b>更新しました。　%s</b></font>', date("Y-m-d H:i:s")));
        $this->setTemplate('campaign');

        return $this->view;
    }

    /**
     * キャンペーン設定更新時の入力検証処理
     */
    protected function validateForCampaign($data = array())
    {
        $errors = array();

        // SettlementAmountLimit: 決済上限額
        $key = 'SettlementAmountLimit';
        if (!isset($errors[$key]) && (strlen($data[$key]) < 0)) {
            $errors[$key] = array("'決済上限額'を入力してください");
        }
        if (!isset($errors[$key]) && !(is_numeric($data[$key]))) {
            $errors[$key] = array("'決済上限額'の形式が不正です");
        }

        // SettlementFeeRate: 決済手数料率
        $key = 'SettlementFeeRate';
        if (!isset($errors[$key]) && (strlen($data[$key]) < 0)) {
            $errors[$key] = array("'決済手数料率'を入力してください");
        }
        if (!isset($errors[$key]) && !(is_numeric($data[$key]))) {
            $errors[$key] = array("'決済手数料率'の形式が不正です");
        }

        // ClaimFeeBS: 請求手数料（別送）
        $key = 'ClaimFeeBS';
        if (!isset($errors[$key]) && (strlen($data[$key]) < 0)) {
            $errors[$key] = array("'請求手数料（別送）'を入力してください");
        }
        if (!isset($errors[$key]) && !(is_numeric($data[$key]))) {
            $errors[$key] = array("'請求手数料（別送）'の形式が不正です");
        }

        // ClaimFeeDK: 請求手数料（同梱）
        $key = 'ClaimFeeDK';
        if (!isset($errors[$key]) && (strlen($data[$key]) < 0)) {
            $errors[$key] = array("'請求手数料（同梱）'を入力してください");
        }
        if (!isset($errors[$key]) && !(is_numeric($data[$key]))) {
            $errors[$key] = array("'請求手数料（同梱）'の形式が不正です");
        }

        // ReClaimFee: 再請求手数料
        $key = 'ReClaimFee';
        if (!isset($errors[$key]) && (strlen($data[$key]) < 0)) {
            $errors[$key] = array("'再請求手数料'を入力してください");
        }
        if (!isset($errors[$key]) && !(is_numeric($data[$key]))) {
            $errors[$key] = array("'再請求手数料'の形式が不正です");
        }

        // 加盟店にOEMが設定されている場合（OEMID が 0より大きい場合）は以下処理を行う。
        if ($data['OemId'] > 0) {
            // OemSettlementFeeRate: OEM決済手数料率
            $key = 'OemSettlementFeeRate';
            if (!isset($errors[$key]) && (strlen($data[$key]) < 0)) {
                $errors[$key] = array("'OEM決済手数料率'を入力してください");
            }
            if (!isset($errors[$key]) && !(is_numeric($data[$key]))) {
                $errors[$key] = array("'OEM決済手数料率'の形式が不正です");
            }

            // OemClaimFee: OEM請求手数料
            $key = 'OemClaimFee';
            if (!isset($errors[$key]) && (strlen($data[$key]) < 0)) {
                $errors[$key] = array("'OEM請求手数料'を入力してください");
            }
            if (!isset($errors[$key]) && !(is_numeric($data[$key]))) {
                $errors[$key] = array("'OEM請求手数料'の形式が不正です");
            }

            // SelfBillingOemClaimFee: OEM同梱請求手数料
            $key = 'SelfBillingOemClaimFee';
            if (!isset($errors[$key]) && (strlen($data[$key]) < 0)) {
                $errors[$key] = array("'OEM同梱請求手数料'を入力してください");
            }
            if (!isset($errors[$key]) && !(is_numeric($data[$key]))) {
                $errors[$key] = array("'OEM同梱請求手数料'の形式が不正です");
            }
        }

        // SystemFee: システム手数料
        $key = 'SystemFee';
        if (!isset($errors[$key]) && !(is_numeric($data[$key]))) {
            $errors[$key] = array("'システム手数料'の形式が不正です");
        }

        return $errors;
    }

    /**
     * 請求ストップ解除アクション
     */
    public function resetacsAction() {

        $params = $this->getParams();

        $ent = new TableEnterprise($this->app->dbAdapter);
        $sit = new TableSite($this->app->dbAdapter);

        //サイト情報取得
        $site = $sit->findSite(isset($params['sid']) ? $params['sid'] : -1)->current();
        if (!$site) {
            throw new \Exception(sprintf("サイトID '%s' は不正な指定です", $site['SiteId']));
        }

        $entdata = $ent->findEnterprise($site['EnterpriseId'])->current();

        //Order情報取得
        $sql = <<<EOQ
            SELECT  OrderSeq
            FROM    T_Order
            WHERE   SiteId = :SiteId
            AND     DataStatus = 51
            AND     Cnl_Status = 0
            AND     (LetterClaimStopFlg = 1 OR MailClaimStopFlg = 1)
            ORDER BY
                    OrderSeq
EOQ;

        $ri = $this->app->dbAdapter->query($sql)->execute(array(':SiteId' => $params['sid']));
        $list = ResultInterfaceToArray($ri);

        $this->setPageTitle(sprintf('%s - 請求ストップ解除', $this->getPageTitle()));

        $this->view->assign('entdata', $entdata);
        $this->view->assign('EnterpriseId', $entdata['EnterpriseId']);
        $this->view->assign('SiteId', $site['SiteId']);
        $this->view->assign('data', $site);
        // count関数対策
        $this->view->assign('can_reset', (!empty($list)));
        $this->view->assign('targets', $list);

        return $this->view;
	}

    /**
     * 請求ストップ解除実行アクション
     */
    public function resetacsdoneAction() {

        $params = $this->getParams();

        $site = new TableSite($this->app->dbAdapter);

        $sitedata = $site->findSite( isset($params['sid']) ? $params['sid'] : -1 )->current();
        if (!$sitedata) {
            throw new \Exception(sprintf("サイトID '%s' は不正な指定です", $eid));
        }
        $sid = $sitedata['SiteId'];

        $orders = new TableOrder($this->app->dbAdapter);
        $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
        try {
            $row_tmpl = array(
                    'LetterClaimStopFlg' => 0,
                    'MailClaimStopFlg' => 0
            );
            $list = $this->_getAutoClaimStopResetTargets($sid);
            foreach($list as $item) {
                $orders->saveUpdate($row_tmpl, $item['OrderSeq']);
            }
            $this->app->dbAdapter->getDriver()->getConnection()->commit();
        }
        catch(\Exception $err) {
            $this->app->dbAdapter->getDriver()->getConnection()->rollBack();
            throw $err;
        }

        return $this->_redirect(sprintf('site/resetacs/sid/%s', $sid));
    }

    /**
     * 請求自動ストップ設定単独更新アクション
     * resetacsActionから遷移する
     */
    public function acsupdateAction() {

        $params = $this->getParams();

        $params = (isset($params['form']) ? $params['form'] : array());

        $site = new TableSite($this->app->dbAdapter);

        // 対象サイト抽出
        $sitedata = $site->findSite($params['sid'])->current();
        if (!$sitedata) {
            if(empty($params['sid'])) {
                throw new \Exception('サイトIDが指定されていません');
            }
            else {
                throw new \Exception(sprintf("サイトID '%s' は不正な指定です",$params['sid']));
            }
        }

        // 請求自動ストップ機能のみ更新
        $site->saveUpdate(array('AutoClaimStopFlg' => ((int)$params['AutoClaimStopFlg']) ? 1 : 0), $sitedata['SiteId']);

        // 請求ストップ解除ページへリダイレクト
        return $this->_redirect(sprintf('site/resetacs/sid/%s', $sitedata['SiteId']));
    }

    /**
     * サイト編集
     */
    public function jintecjudgeAction()
    {
        $params = $this->getParams();
        $enterpriseId = $params['eid'];
        $siteId = $params['sid'];

        $mdlEnterprise = new TableEnterprise($this->app->dbAdapter);
        $mdlSite = new TableSite($this->app->dbAdapter);
        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
        $mdlDelMthd = new TableDeliMethod($this->app->dbAdapter);
        $mdlCode = new TableCode($this->app->dbAdapter);
        $mdlPricePlan = new TablePricePlan($this->app->dbAdapter);
        $mdlOem = new TableOem($this->app->dbAdapter);

        // 事業者名の取得・アサイン
        $enterpriseData = $mdlEnterprise->findEnterprise($enterpriseId)->current();
        $this->view->assign("EnterpriseNameKj", $enterpriseData['EnterpriseNameKj']);
        $this->view->assign("EnterpriseId", $enterpriseId);

        //サイトデータ取得・アサイン
        $site = $mdlSite->findSite($siteId)->current();

        // ジンテック判定設定有無が設定なしの場合
        // 設定ありの場合、DBの値のまま表示
        if($site['JintecJudge'] == '0'){
            // 初期値設定
            $site['JintecJudge0'] = '2'; //保留
            $site['JintecJudge1'] = '2'; //保留
            $site['JintecJudge2'] = '1'; //NG
            $site['JintecJudge3'] = '1'; //NG
            $site['JintecJudge4'] = '1'; //NG
            $site['JintecJudge5'] = '0'; //OK
            $site['JintecJudge6'] = '1'; //NG
            $site['JintecJudge7'] = '1'; //NG
            $site['JintecJudge8'] = '0'; //OK
            $site['JintecJudge9'] = '2'; //保留
            $site['JintecJudge10'] = '0'; //OK
        }

        $this->view->assign('list', $site);

        return $this->view;
    }

    /**
     * ジンテック判定ポスト処理画面
     */
    public function jintecjudgepostAction()
    {
        $params = $this->getParams();
        $list = $params['list'];

        $site = new TableSite($this->app->dbAdapter);
        $mdlEnterprise = new TableEnterprise($this->app->dbAdapter);

        //加盟店ID
        $eid = $list['EnterpriseId'];

        //サイトID
        $sid = $list['SiteId'];

        // トランザクション開始
        $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
        try {
            // ユーザIDの取得
            $obj = new TableUser($this->app->dbAdapter);
            $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

            // 押されたボタンによる処理分岐
            if (isset($params['btnConfirm'])) {
                // ジンテック判定有効
                $list['JintecJudge'] = 1;
                $site->saveUpdate(array_merge($list, array( 'UpdateId' => $userId )), $sid);
            }
            // 念のためelseは使わない
            else if (isset($params['btnDel'])) {
                // 初期値で更新（ジンテック判定自体は無効にする）
                $list['JintecJudge'] = 0;
                $list['JintecJudge0'] = '2'; //保留
                $list['JintecJudge1'] = '2'; //保留
                $list['JintecJudge2'] = '1'; //NG
                $list['JintecJudge3'] = '1'; //NG
                $list['JintecJudge4'] = '1'; //NG
                $list['JintecJudge5'] = '0'; //OK
                $list['JintecJudge6'] = '1'; //NG
                $list['JintecJudge7'] = '1'; //NG
                $list['JintecJudge8'] = '0'; //OK
                $list['JintecJudge9'] = '2'; //保留
                $list['JintecJudge10'] = '0'; //OK
                $site->saveUpdate(array_merge($list, array( 'UpdateId' => $userId )), $sid);
            }

            // コミット
            $this->app->dbAdapter->getDriver()->getConnection()->commit();
        } catch (\Exception $e) {
            // ロールバック
            $this->app->dbAdapter->getDriver()->getConnection()->rollback();
            throw $e;
        }

        // ----- 以下、listActionと同様の処理 -----

        // 事業者名の取得・アサイン
        $enterpriseData = $mdlEnterprise->findEnterprise($eid)->current();
        $this->view->assign("EnterpriseNameKj", $enterpriseData['EnterpriseNameKj']);

        // サイトデータの取得
        $datas = array();
        $datas = $site->getAll($eid);
        $datas = ResultInterfaceToArray($datas);

        // サイトに紐づく代理店の件数を取得
        foreach ($datas as $value) {
            $sql = " SELECT COUNT(AgencyId) AS cnt FROM M_AgencySite WHERE SiteId = :SiteId GROUP BY SiteId ";
            $cnt[$value['SiteId']] = $this->app->dbAdapter->query($sql)->execute( array(':SiteId' => $value['SiteId']) )->current();
        }

        // count関数対策
        $datasLen = 0;
        if(!empty($datas)) {
            $datasLen = count($datas);
        }

        for ($i = 0 ; $i < $datasLen ; $i++) {
            // サイトに紐づくAPIユーザ取得
            $apisql = <<<EOQ
            SELECT  au.ApiUserNameKj
                ,   au.ApiUserNameKn
            FROM    T_ApiUser au
                    INNER JOIN T_ApiUserEnterprise ae ON (au.ApiUserId = ae.ApiUserId)
                    INNER JOIN T_Site s ON (ae.SiteId = s.SiteId)
            WHERE   s.SiteId = :SiteId
            ORDER BY au.ApiUserId
            LIMIT 1
            ;
EOQ;
            $apidata[$i] = $this->app->dbAdapter->query($apisql)->execute(array( ':SiteId' => $datas[$i]['SiteId'] ))->current();
            $cntsql = <<<EOQ
            SELECT COUNT(au.ApiUserId) AS cnt
            FROM T_ApiUser au
                 INNER JOIN T_ApiUserEnterprise ae ON (au.ApiUserId = ae.ApiUserId)
                 INNER JOIN T_Site s ON (ae.SiteId = s.SiteId)
            WHERE   s.SiteId = :SiteId ;
EOQ;
            $apicnt[$i] = $this->app->dbAdapter->query($cntsql)->execute(array( ':SiteId' => $datas[$i]['SiteId'] ))->current();

        }
        $this->view->assign('list', $datas);
        $this->view->assign('cnt', $cnt);
        $this->view->assign('EnterpriseId', $eid);
        $this->view->assign('OemId', $enterpriseData['OemId']);
        $this->view->assign('apilist', $apidata);
        $this->view->assign('apicnt', $apicnt);

        // 一覧画面を表示する
        $this->setTemplate('list');

        return $this->view;
    }

    /**
     * サイト登録/編集フォームの内容を検証する
     * @param array $data 登録フォームデータ
     * @param int $billingAgentFlg 加盟店.請求代行プラン
     * @param array $payments 支払方法マスタ情報
     * @return array エラーメッセージの配列
     */
    protected function validate($data = array(), $billingAgentFlg, $payments)
    {
        $errors = array();
        //SiteNameKj:サイト名
        $Key = 'SiteNameKj';
        if(!isset($errors[$Key]) && (strlen ($data[$Key]) <= 0)){
            $errors[$Key] = "サイト名は必須です";
        }

        //サイト名文字数制限 口座振替利用するときのみ
        if (($data['CreditTransferFlg'] == 1) || ($data['CreditTransferFlg'] == 2) || ($data['CreditTransferFlg'] == 3)) {
            if (!isset($errors[$Key]) && !(mb_strlen($data[$Key]) <= 40)) {
                $errors[$Key] = array("サイト名は40文字以下で入力してください。");
            }
        }

        //SiteNameKj:サイト名カナ
        $Key = 'SiteNameKn';
        if(!isset($errors[$Key]) && (strlen ($data[$Key]) <= 0)){
            $errors[$Key] = "サイト名カナは必須です";
        }

        //Url
        $Key = 'Url';
        if(!isset($errors[$Key]) && (strlen ($data[$Key]) <= 0)){
            $errors[$Key] = "URLは必須です";
        }

        //ReqMailAddrFlg:メールアドレス(チェック不要)

        //SiteForms:形態
        $Key = 'SiteForm';
        if(!isset($errors[$Key]) && !((int)$data[$Key] > 0)){
            $errors[$Key] = "形態は必須です";
        }

        //BarcodeLimitDays:バーコード使用期限
        $Key = 'BarcodeLimitDays';
        if(!isset($errors[$Key]) && strlen ($data[$Key]) > 0 && (int)$data[$Key] < 0) {
            $errors[$Key] = "バーコード使用期限に0未満の値を指定することは出来ません";
        }

        //OemId: 加盟店OEM先
        //OemSettlementFeeRate: OEM決済手数料率
        $Key = 'OemSettlementFeeRate';
        if(!isset($errors[$Key]) && (isset($data['OemId']) && $data['OemId'] <> 0) &&  (strlen ($data[$Key]) <= 0)){
            $errors[$Key] = "OEM決済手数料率は必須です";
        }

        //OemClaimFee: OEM請求手数料
        $Key = 'OemClaimFee';
        if(!isset($errors[$Key]) && (isset($data['OemId']) && $data['OemId'] <> 0) &&  (strlen ($data[$Key]) <= 0)){
            $errors[$Key] = "OEM請求手数料は必須です";
        }

        //SelfBillingOemClaimFee: OEM同梱請求手数料
        $Key = 'SelfBillingOemClaimFee';
        if(!isset($errors[$Key]) && (isset($data['OemId']) && $data['OemId'] <> 0) &&  (strlen ($data[$Key]) <= 0)){
            $errors[$Key] = "OEM同梱請求手数料は必須です";
        }

        //OemFirstCreditTransferClaimFee: OEM口振紙初回登録手数料（税抜）
        $Key = 'OemFirstCreditTransferClaimFee';
        if(!isset($errors[$Key]) && (isset($data['OemId']) && $data['OemId'] <> 0) && (isset($data['CreditTransferFlg']) && $data['CreditTransferFlg'] == 1) &&  (strlen ($data[$Key]) <= 0)){
            $errors[$Key] = "OEM口振紙初回登録手数料（税抜）は必須です";
        }
        if(!isset($errors[$Key]) && (isset($data['OemId']) && $data['OemId'] <> 0) && (isset($data['CreditTransferFlg']) && $data['CreditTransferFlg'] == 2) &&  (strlen ($data[$Key]) <= 0)){
            $errors[$Key] = "OEM口振紙初回登録手数料（税抜）は必須です";
        }
        if(!isset($errors[$Key]) && (isset($data['OemId']) && $data['OemId'] <> 0) && (isset($data['CreditTransferFlg']) && $data['CreditTransferFlg'] == 3) &&  (strlen ($data[$Key]) <= 0)){
            $errors[$Key] = "OEM口振紙初回登録手数料（税抜）は必須です";
        }

        //OemFirstCreditTransferClaimFeeWeb: OEM口振WEB初回登録手数料（税抜）
        $Key = 'OemFirstCreditTransferClaimFeeWeb';
        if(!isset($errors[$Key]) && (isset($data['OemId']) && $data['OemId'] <> 0) && (isset($data['CreditTransferFlg']) && $data['CreditTransferFlg'] == 1) &&  (strlen ($data[$Key]) <= 0)){
            $errors[$Key] = "OEM口振WEB初回登録手数料（税抜）は必須です";
        }
        if(!isset($errors[$Key]) && (isset($data['OemId']) && $data['OemId'] <> 0) && (isset($data['CreditTransferFlg']) && $data['CreditTransferFlg'] == 2) &&  (strlen ($data[$Key]) <= 0)){
            $errors[$Key] = "OEM口振WEB初回登録手数料（税抜）は必須です";
        }
        if(!isset($errors[$Key]) && (isset($data['OemId']) && $data['OemId'] <> 0) && (isset($data['CreditTransferFlg']) && $data['CreditTransferFlg'] == 3) &&  (strlen ($data[$Key]) <= 0)){
            $errors[$Key] = "OEM口振WEB初回登録手数料（税抜）は必須です";
        }

        //OemCreditTransferClaimFee: OEM口振引落手数料（税抜）
        $Key = 'OemCreditTransferClaimFee';
        if(!isset($errors[$Key]) && (isset($data['OemId']) && $data['OemId'] <> 0) && (isset($data['CreditTransferFlg']) && $data['CreditTransferFlg'] == 1) &&  (strlen ($data[$Key]) <= 0)){
            $errors[$Key] = "OEM口振引落手数料（税抜）は必須です";
        }
        if(!isset($errors[$Key]) && (isset($data['OemId']) && $data['OemId'] <> 0) && (isset($data['CreditTransferFlg']) && $data['CreditTransferFlg'] == 2) &&  (strlen ($data[$Key]) <= 0)){
            $errors[$Key] = "OEM口振引落手数料（税抜）は必須です";
        }
        if(!isset($errors[$Key]) && (isset($data['OemId']) && $data['OemId'] <> 0) && (isset($data['CreditTransferFlg']) && $data['CreditTransferFlg'] == 3) &&  (strlen ($data[$Key]) <= 0)){
            $errors[$Key] = "OEM口振引落手数料（税抜）は必須です";
        }

        //SettlementAmountLimit:決済上限額
        $Key = 'SettlementAmountLimit';
        if(!isset($errors[$Key]) && (strlen ($data[$Key]) <= 0)){
            $errors[$Key] = "決済上限額は必須です";
        }

        //SettlementFeeRate:決済手数料率
        $Key = 'SettlementFeeRate';
        if(!isset($errors[$Key]) && (strlen ($data[$Key]) <= 0)){
            $errors[$Key] = "決済手数料率は必須です";
        }

        //ClaimFeeBS:請求手数料（別送）
        $Key = 'ClaimFeeBS';
        if(!isset($errors[$Key]) && (strlen ($data[$Key]) <= 0)){
            $errors[$Key] = "請求手数料（別送）は必須です";
        }

        //ClaimFeeDK:形請求手数料（同梱）
        $Key = 'ClaimFeeDK';
        if(!isset($errors[$Key]) && (strlen ($data[$Key]) <= 0)){
            $errors[$Key] = "請求手数料（同梱）は必須です";
        }

        //ReceiptAgentId:収納代行会社ID
        $Key = 'ReceiptAgentId';
        if(!isset($errors[$Key]) && ((int)$data[$Key]<= 0)){
        	$errors[$Key] = "収納代行会社IDは必須です";
        }else{
	        //SubscriberCode:加入者固有コード
	        $Key = 'SubscriberCode';
	        if(!isset($errors[$Key]) && (strlen ($data[$Key]) > 0)){
	        	$mdlSc = new TableSubscriberCode($this->app->dbAdapter);
	        	$codeCnt = $mdlSc->cntReceiptAgentIdSubscriberCode($data['ReceiptAgentId'],$data['SubscriberCode']);
	        	if($codeCnt < 1){
	        		$errors[$Key] = "存在しない加入者固有コードです。";
	        	}
	        }
        }
	    //SubscriberCode:加入者固有コード
        $Key = 'SubscriberCode';
        if(!isset($errors[$Key]) && (strlen ($data[$Key]) <= 0)){
        	$errors[$Key] = "存在しない加入者固有コードです。";
        }

        //ReClaimFee:再請求手数料
        $Key = 'ReClaimFee';
        if(!isset($errors[$Key]) && (strlen ($data[$Key]) <= 0)){
            $errors[$Key] = "再請求手数料は必須です";
        }

        //
        if (!isset($errors['ReClaimFeeSetting']) && ($data['ReClaimFeeSetting'] == 1)) {
                    //ReClaimFee1:再請求１
            $Key = 'ReClaimFee1';
            if(!isset($errors[$Key]) && (strlen ($data[$Key]) <= 0)){
                $errors[$Key] = "再請求１は必須です";
            }
            //ReClaimFee3:再請求３
            $Key = 'ReClaimFee3';
            if(!isset($errors[$Key]) && (strlen ($data[$Key]) <= 0)){
                $errors[$Key] = "再請求３は必須です";
            }
            //ReClaimFee4:再請求４
            $Key = 'ReClaimFee4';
            if(!isset($errors[$Key]) && (strlen ($data[$Key]) <= 0)){
                $errors[$Key] = "再請求４は必須です";
            }
            //ReClaimFee5:再請求５
            $Key = 'ReClaimFee5';
            if(!isset($errors[$Key]) && (strlen ($data[$Key]) <= 0)){
                $errors[$Key] = "再請求５は必須です";
            }
            //ReClaimFee6:再請求６
            $Key = 'ReClaimFee6';
            if(!isset($errors[$Key]) && (strlen ($data[$Key]) <= 0)){
                $errors[$Key] = "再請求６は必須です";
            }
            //ReClaimFee7:再請求７
            $Key = 'ReClaimFee7';
            if(!isset($errors[$Key]) && (strlen ($data[$Key]) <= 0)){
                $errors[$Key] = "再請求７は必須です";
            }
            //ReClaimFeeStartRegistDate:適用開始注文登録日
            $Key = 'ReClaimFeeStartRegistDate';
            if(!isset($errors[$Key]) && strlen ($data[$Key]) > 0 && !IsValidFormatDate($data[$Key])) {
                $errors[$Key] = "適用開始注文登録日はYYYY-MM-DDで入力して下さい";
            }
            //ReClaimFeeStartDate:適用開始注文日
            $Key = 'ReClaimFeeStartDate';
            if(!isset($errors[$Key]) && strlen ($data[$Key]) > 0 && !IsValidFormatDate($data[$Key])) {
                $errors[$Key] = "適用開始注文日はYYYY-MM-DDで入力して下さい";
            }
        }

        //FirstCreditTransferClaimFee:口振紙初回登録手数料（税抜）
        $Key = 'FirstCreditTransferClaimFee';
        if(!isset($errors[$Key]) && (isset($data['CreditTransferFlg']) && $data['CreditTransferFlg'] == 1) && (strlen ($data[$Key]) <= 0)){
            $errors[$Key] = "口振紙初回登録手数料は必須です";
        }
        if(!isset($errors[$Key]) && (isset($data['CreditTransferFlg']) && $data['CreditTransferFlg'] == 2) && (strlen ($data[$Key]) <= 0)){
            $errors[$Key] = "口振紙初回登録手数料は必須です";
        }
        if(!isset($errors[$Key]) && (isset($data['CreditTransferFlg']) && $data['CreditTransferFlg'] == 3) && (strlen ($data[$Key]) <= 0)){
            $errors[$Key] = "口振紙初回登録手数料は必須です";
        }

        //FirstCreditTransferClaimFeeWeb:口振用初回請求手数料(Web)（税抜）
        $Key = 'FirstCreditTransferClaimFeeWeb';
        if(!isset($errors[$Key]) && (isset($data['CreditTransferFlg']) && $data['CreditTransferFlg'] == 1) && (strlen ($data[$Key]) <= 0)){
            $errors[$Key] = "口振用初回請求手数料(Web)は必須です";
        }
        if(!isset($errors[$Key]) && (isset($data['CreditTransferFlg']) && $data['CreditTransferFlg'] == 2) && (strlen ($data[$Key]) <= 0)){
            $errors[$Key] = "口振用初回請求手数料(Web)は必須です";
        }
        if(!isset($errors[$Key]) && (isset($data['CreditTransferFlg']) && $data['CreditTransferFlg'] == 3) && (strlen ($data[$Key]) <= 0)){
            $errors[$Key] = "口振用初回請求手数料(Web)は必須です";
        }

        //CreditTransferClaimFee:口振用請求手数料（税抜）
        $Key = 'CreditTransferClaimFee';
        if(!isset($errors[$Key]) && (isset($data['CreditTransferFlg']) && $data['CreditTransferFlg'] == 1) && (strlen ($data[$Key]) <= 0)){
            $errors[$Key] = "口振引落手数料は必須です";
        }
        if(!isset($errors[$Key]) && (isset($data['CreditTransferFlg']) && $data['CreditTransferFlg'] == 2) && (strlen ($data[$Key]) <= 0)){
            $errors[$Key] = "口振引落手数料は必須です";
        }
        if(!isset($errors[$Key]) && (isset($data['CreditTransferFlg']) && $data['CreditTransferFlg'] == 3) && (strlen ($data[$Key]) <= 0)){
            $errors[$Key] = "口振引落手数料は必須です";
        }

        //AutoCreditDateFrom:与信自動化有効期間
        $Key = 'AutoCreditDateFrom';
        if(!isset($errors[$Key]) && strlen ($data[$Key]) > 0 && !IsValidFormatDate($data[$Key])) {
            $errors[$Key] = "与信自動化有効期間FROMはYYYY-MM-DDで入力して下さい";
        }

        //AutoCreditDateTo:与信自動化有効期間
        $Key = 'AutoCreditDateTo';
        if(!isset($errors[$Key]) && strlen ($data[$Key]) > 0 && !IsValidFormatDate($data[$Key])) {
            $errors[$Key] = "与信自動化有効期間TOはYYYY-MM-DDで入力して下さい";
        }

        //AutoCreditDateFrom＋To:与信自動化有効期間範囲指定
        $Key = 'AutoCreditDateFrom';
        $key1 = 'AutoCreditDateFrom';
        $key2 = 'AutoCreditDateTo';
        if(!isset($errors[$key1]) && !isset($errors[$key2]) && ((strlen ($data[$key1]) > 0 && strlen ($data[$key2]) == 0) || (strlen ($data[$key1]) == 0 && strlen ($data[$key2]) > 0))) {
            $errors[$Key] = "与信自動化有効期間はFROM、TO両方を入力して下さい";
        }

        if(!isset($errors[$key1]) && !isset($errors[$key2]) && strlen ($data[$key1]) > 0 && strlen ($data[$key2]) > 0 && strtotime($data[$key1]) > strtotime($data[$key2])) {
            $errors[$Key] = "与信自動化有効期間の範囲指定が不正です";
        }

        //MultiOrderCount:連続注文回数
        $Key = 'MultiOrderCount';
        if(!isset($errors[$Key]) && strlen ($data[$Key]) > 0 && (int)$data[$Key] <= 1) {
            $errors[$Key] = "連続注文回数に1以下の値を指定することは出来ません";
        }
        if (!isset($errors[$Key]) && strlen($data[$Key]) <= 0 && strlen($data['MultiOrderScore']) > 0 ) {
            $errors[$Key] = "連続注文回数に値が入力されていません";
        }

        //MultiOrderScore:連続注文スコア
        $Key = 'MultiOrderScore';
        if (!isset($errors[$Key]) && strlen($data[$Key]) <= 0 && strlen($data['MultiOrderCount']) > 0 ) {
            $errors[$Key] = "連続注文スコアに値が入力されていません";
        }

        //SiteConfDate:掲載確認日
        $Key = 'SiteConfDate';
        if(!isset($errors[$Key]) && strlen ($data[$Key]) > 0 && !IsValidFormatDate($data[$Key])) {
            $errors[$Key] = "掲載確認日はYYYY-MM-DDで入力して下さい";
        }

        //CoralValidateインスタンス生成
        $CoralValidate = new \Coral\Coral\CoralValidate();

        //ReceiptIssueProviso:領収書但し書き
        $Key = 'ReceiptIssueProviso';
        if (!isset($errors[$Key]) && !(mb_strlen($data[$Key]) <= 255)) {
            $errors[$Key] = "領収書但し書きは255文字以下で入力してください";
        }

        //CSSettlementFeeRate:クレジット支払い決済手数料率
//         $Key = 'CSSettlementFeeRate';
//         if (!isset($errors[$Key]) && $data['PaymentAfterArrivalFlg'] == '1' && strlen ($data[$Key]) <= 0) {
//             $errors[$Key] = "届いてから払い利用時は、クレジット支払い決済手数料率は必須です";
//         }
//         if (!isset($errors[$Key]) && strlen($data[$Key]) > 0 && !$CoralValidate->isFloat($data[$Key])) {
//             $errors[$Key] = "クレジット支払い決済手数料率は数値で入力してください";
//         }

//         //CSSettlementFeeRate:クレジット支払い請求手数料（別送）
//         $Key = 'CSClaimFeeBS';
//         if (!isset($errors[$Key]) && $data['PaymentAfterArrivalFlg'] == '1' && strlen ($data[$Key]) <= 0) {
//             $errors[$Key] = "届いてから払い利用時は、クレジット支払い請求手数料（別送）は必須です";
//         }
//         if (!isset($errors[$Key]) && strlen($data[$Key]) > 0 && !$CoralValidate->isFloat($data[$Key])) {
//             $errors[$Key] = "クレジット支払い請求手数料（別送）は数値で入力してください";
//         }

        //CSSettlementFeeRate:クレジット支払い請求手数料（同梱）
//         $Key = 'CSClaimFeeDK';
//         if (!isset($errors[$Key]) && $data['PaymentAfterArrivalFlg'] == '1' && strlen ($data[$Key]) <= 0) {
//             $errors[$Key] = "届いてから払い利用時は、クレジット支払い請求手数料（同梱）は必須です";
//         }
//         if (!isset($errors[$Key]) && strlen($data[$Key]) > 0 && !$CoralValidate->isFloat($data[$Key])) {
//             $errors[$Key] = "クレジット支払い請求手数料（同梱）は数値で入力してください";
//         }

        //SB Payment項目
//         if( $data['PaymentAfterArrivalFlg'] == '1'){
//             //届いてから払い利用時:必須入力、数値チェック
//             foreach(array('MerchantId' => 'マーチャントID', 'ServiceId' => 'サービスID', 'HashKey' => 'ハッシュキー', 'BasicId' => 'Basic認証ID', 'BasicPw' => 'Basic認証PW') as $key => $val){
//                 //入力がない場合エラー
//                 if (!isset($errors[$key]) && strlen($data[$key]) <= 0) {
//                     $errors[$key] = array("届いてから払い利用時は、 '"."$val"."' は必須です");
//                     continue;
//                 }
//                 //数値でない場合エラー (マーチャントID、サービスIDのみ対象)
//                 if (in_array($key,array('MerchantId', 'ServiceId')) && !isset($errors[$key]) &&  (!preg_match("/^[0-9]+$/", ($data[$key])))) {
//                     $errors[$key] = array("'$val'"." は数値文字で入力してください");
//                 }
//             }
//         }else{
//             //届いてから払い非利用時:数値チェック
//             foreach(array('MerchantId' => 'マーチャントID', 'ServiceId' => 'サービスID') as $key => $val){
//                 //入力ありのとき文字列が全て数値か
//                 if (!isset($errors[$key]) && strlen($data[$key]) > 0 && (!preg_match("/^[0-9]+$/", ($data[$key])))) {
//                     $errors[$key] = array("'$val'"."　は数値文字で入力してください");
//                 }
//             }
//         }

        //請求時伝票番号自動仮登録
        $key = 'ClaimAutoJournalIncMode';
        if (!isset($errors[$key]) &&  $data[$key] == 1 && $data['AutoJournalIncMode'] == 1) {
            $errors[$key] = array("伝票番号自動仮登録をしない場合のみ、請求時伝票番号自動仮登録ができます");
        }

        //支払方法
        $paypay = 0;
        $rakutenBank = 0;
        $famiPay = 0;
        foreach ($payments as $payment) {
            switch ($payment['FixedId']) {
                case 2:
                    $paypay = $payment['PaymentId'];
                    break;
                case 4:
                    $rakutenBank = $payment['PaymentId'];
                    break;
                case 5:
                    $famiPay = $payment['PaymentId'];
                    break;
            }
        }

        foreach($data['Payment'] as $id => $payment) {
            if (($id == $rakutenBank) || ($id == $famiPay)) {
                continue;
            }
            $key = 'Payment_'.$id.'_UseFlg';
            if (!isset($errors[$key]) && ($id == $paypay) && ($payment['UseFlg'] == 1) && ($billingAgentFlg == 0)) {
                $errors[$key] = array("請求代行以外は、PayPayは利用できません");
            }
            $key = 'Payment_'.$id.'_ApplyDate';
            if (!isset($errors[$key]) && ($payment['UseFlg'] == 1) && strlen($payment['ApplyDate']) <= 0) {
                if ($id != $paypay) {
                    $errors[$key] = array("'申込日'は必須です");
                } else {
                    if (!isset($errors['Payment_'.$id.'_UseFlg'])) {
                        $errors[$key] = array("'申込日'は必須です");
                    }
                }
            } else if (!isset($errors[$key]) && ($payment['UseFlg'] == 1) && !IsValidFormatDate($payment['ApplyDate'])) {
                if ($id != $paypay) {
                    $errors[$key] = array("'申込日'の形式が不正です");
                } else {
                    if (!isset($errors['Payment_'.$id.'_UseFlg'])) {
                        $errors[$key] = array("'申込日'の形式が不正です");
                    }
                }
            }
            $key = 'Payment_'.$id.'_UseStartDate';
//            if (!isset($errors[$key]) && ($payment['UseFlg'] == 1) && strlen($payment['UseStartDate']) <= 0) {
//                $errors[$key] = array("'利用開始日'は必須です");
//            } else if (!isset($errors[$key]) && ($payment['UseFlg'] == 1) && !IsValidFormatDate($payment['UseStartDate'])) {
            if (!isset($errors[$key]) && (strlen($payment['UseStartDate']) > 0) && !IsValidFormatDate($payment['UseStartDate'])) {
                if ($id != $paypay) {
                    $errors[$key] = array("'利用開始日'の形式が不正です");
                } else {
                    if (!isset($errors['Payment_'.$id.'_UseFlg'])) {
                        $errors[$key] = array("'利用開始日'の形式が不正です");
                    }
                }
            }
            $key = 'Payment_'.$id.'_ApplyDate';
            if (!isset($errors[$key]) && ($payment['UseFlg'] == 1) && (strlen($payment['ApplyDate']) > 0) && (strlen($payment['UseStartDate']) > 0)) {
                if ($payment['ApplyDate'] > $payment['UseStartDate']) {
                    if ($id != $paypay) {
                        $errors[$key] = array("'申込日'は'利用開始日'より未来は設定できません");
                    } else {
                        if (!isset($errors['Payment_'.$id.'_UseFlg'])) {
                            $errors[$key] = array("'申込日'は'利用開始日'より未来は設定できません");
                        }
                    }
                }
            }
        }

        //コンビニ収納代行情報
        $key = 'MufjBarcodeSubscriberCode';
        if (!isset($errors[$key]) && ($data['MufjBarcodeUsedFlg'] == 1) && (strlen($data[$key]) <= 0)) {
            $errors[$key] = array("三菱UFJをチェックした場合、コンビニ収納代行情報は必須です");
        }
        if (!isset($errors[$key]) && (strlen($data[$key]) > 0) && (strlen($data[$key]) != 5)) {
            $errors[$key] = array("コンビニ収納代行情報の加入者固有コードは数字５桁です");
        }
        if (!isset($errors[$key]) && (strlen($data[$key]) > 0) && (!preg_match("/^[0-9]+$/", ($data[$key])))) {
            $errors[$key] = array("コンビニ収納代行情報の加入者固有コードは数字５桁です");
        }

        //BillingAgentFlg:再請求設定
        $Key = 'ReissueCount';
        if(!isset($errors[$Key]) && ($billingAgentFlg == 1) && (strlen ($data[$Key]) <= 0)){
            $errors[$Key] = "再請求回数は必須です";
        }

        return $errors;
    }




    protected function _validateTodo($data = array())
    {
        $errors = array();
     
        // get ent info
        $mdlEnterprise = new TableEnterprise($this->app->dbAdapter);
        $entData = $mdlEnterprise->findEnterprise($data['EnterpriseId'])->current();

        $Key = 'ReceiptIssueProviso';

        if ($data['ReceiptUsedFlg'] == 1 && strlen($data[$Key]) == '') {
            $errors[$Key] = "領収書発行を利用する際、'領収書但し書き'は必須です";
        }
        if (strlen($data[$Key]) > 255) {
            $errors[$Key] = "領収書但し書きは255文字以下で入力してください";
        }
        $flagCheck = false;
        $Key = 'Payment';
        if (!isset($errors[$Key])) {
            foreach ($data[$Key] as $paymentId => $row) {
                if (isset($row['ValidFlg'])) { // check on (ValidFlg = 1)
                    $flagCheck = true;
                    if ($entData['SelfBillingMode'] == 1) {
                        if (trim($row['ClaimFeeDK']) == '') {
                            $errors[$Key][$paymentId]['ClaimFeeDK'] = "'請求手数料（同梱）' は必須です";
                        } else {
                            if (!isset($errors[$Key][$paymentId]['ClaimFeeDK']) && !is_numeric($row['ClaimFeeDK'])) {
                                $errors[$Key][$paymentId]['ClaimFeeDK'] = "数字で入力してください";
                            }
                        }
                    } else {
                        if (trim($row['ClaimFeeDK']) != '' && !is_numeric($row['ClaimFeeDK'])) {
                            $errors[$Key][$paymentId]['ClaimFeeDK'] = "数字で入力してください";
                        }
                    }

                    if ($row['ContractorId'] == 0) {
                        $errors[$Key][$paymentId]['ContractorId'] = "'契約先' は必須です";
                    }
                    if (trim($row['SettlementFeeRate']) == '') {
                        $errors[$Key][$paymentId]['SettlementFeeRate'] = "'決済手数料率' は必須です";
                    }
                    if (!isset($errors[$Key][$paymentId]['SettlementFeeRate']) && !is_numeric($row['SettlementFeeRate'])) {
                        $errors[$Key][$paymentId]['SettlementFeeRate'] = "数字で入力してください";
                    } else {
                        if ($row['SettlementFeeRate'] > 100) {
                            $errors[$Key][$paymentId]['SettlementFeeRate'] = "決済手数料率は100%以下で入力してください";
                        }
                    }
                    if (trim($row['ClaimFeeBS']) == '') {
                        $errors[$Key][$paymentId]['ClaimFeeBS'] = "'請求手数料（別送）' は必須です";
                    }
                    if (trim($row['NumUseDay']) == '') {
                        $errors[$Key][$paymentId]['NumUseDay'] = "'利用期間' は必須です";
                    }
                    if (trim($row['UseStartDate']) == '') {
                        $errors[$Key][$paymentId]['UseStartDate'] = "'利用開始日時' は必須です";
                    }

                    if (!isset($errors[$Key][$paymentId]['ClaimFeeBS']) && !is_numeric($row['ClaimFeeBS'])) {
                        $errors[$Key][$paymentId]['ClaimFeeBS'] = "数字で入力してください";
                    }

                    if (!isset($errors[$Key][$paymentId]['NumUseDay'])) {
                        if (!is_numeric($row['NumUseDay']) || filter_var($row['NumUseDay'], FILTER_VALIDATE_INT) === false) {
                            $errors[$Key][$paymentId]['NumUseDay'] = "数字で入力してください";
                        } else if ((int) $row['NumUseDay'] > 999) {
                            $errors[$Key][$paymentId]['NumUseDay'] = "3桁以内で入力してください";
                        }
                    }
                    if (!isset($errors[$Key][$paymentId]['UseStartDate']) && !$this->validateDateTime($row['UseStartDate'])) {
                        $errors[$Key][$paymentId]['UseStartDate'] = "形式が違います。[YYYY-MM-DD HH24:MI]で入力してください。";
                    }
                } else {
                    if ($row['SettlementFeeRate'] != '' && !is_numeric($row['SettlementFeeRate'])) {
                        $errors[$Key][$paymentId]['SettlementFeeRate'] = "数字で入力してください";
                    } else {
                        if ($row['SettlementFeeRate'] > 100) {
                            $errors[$Key][$paymentId]['SettlementFeeRate'] = "決済手数料率は100%以下で入力してください";
                        }
                    }
                    $this->app->logger->info($row['ClaimFeeBS']);
                    if ($row['ClaimFeeBS'] != '' && is_numeric($row['ClaimFeeBS']) == false) {
                        $errors[$Key][$paymentId]['ClaimFeeBS'] = "数字で入力してください";
                    }

                    if ($row['ClaimFeeDK'] != '' && !is_numeric($row['ClaimFeeDK'])) {
                        $errors[$Key][$paymentId]['ClaimFeeDK'] = "数字で入力してください";
                    }
                    if ($row['NumUseDay'] != '') {
                        if (!is_numeric($row['NumUseDay']) || filter_var($row['NumUseDay'], FILTER_VALIDATE_INT) === false) {
                            $errors[$Key][$paymentId]['NumUseDay'] = "数字で入力してください";
                        } else if ((int) $row['NumUseDay'] > 999) {
                            $errors[$Key][$paymentId]['NumUseDay'] = "3桁以内で入力してください";
                        }
                    }
                    if ($row['UseStartDate'] != '' && !$this->validateDateTime($row['UseStartDate'])) {
                        $errors[$Key][$paymentId]['UseStartDate'] = "形式が違います。[YYYY-MM-DD HH24:MI]で入力してください。";
                    }
                }
            }
        }

        //SB Payment項目
        if( $data['PaymentAfterArrivalFlg'] == '1'){
            //届いてから払い利用時:必須入力、数値チェック
            foreach(array('MerchantId' => 'マーチャントID', 'ServiceId' => 'サービスID', 'HashKey' => 'ハッシュキー', 'BasicId' => 'Basic認証ID', 'BasicPw' => 'Basic認証PW') as $key => $val){
                //入力がない場合エラー
                if (!isset($errors[$key]) && strlen($data[$key]) <= 0) {
                    $errors[$key] = array("届いてから払い利用時は、 '"."$val"."' は必須です");
                    continue;
                }
                //数値でない場合エラー (マーチャントID、サービスIDのみ対象)
                if (in_array($key,array('MerchantId', 'ServiceId')) && !isset($errors[$key]) &&  (!preg_match("/^[0-9]+$/", ($data[$key])))) {
                    $errors[$key] = array("'$val'"." は数値文字で入力してください");
                }
            }
            if ($flagCheck == false) {
                $errors['PaymentAfterArrivalFlg'] = "有効設定の場合、一つの支払方法を有効が必要です。";
            }

            /*if (strlen($data['SpecificTransUrl']) <= 0) {
                $errors['SpecificTransUrl'] = "有効設定の場合、特定商取引に関するリンク先が必要です。";
            }*/
        }else{
            //届いてから払い非利用時:数値チェック
            foreach(array('MerchantId' => 'マーチャントID', 'ServiceId' => 'サービスID') as $key => $val){
                //入力ありのとき文字列が全て数値か
                if (!isset($errors[$key]) && strlen($data[$key]) > 0 && (!preg_match("/^[0-9]+$/", ($data[$key])))) {
                    $errors[$key] = array("'$val'"."　は数値文字で入力してください");
                }
            }
        }

        $this->app->logger->info($errors);

        return $errors;
    }

    /**
     * 請求ストップ解除対象のリストを取得する
     *
     * @access protected
     * @param int $sid サイトID
     * @return array
     */
    protected function _getAutoClaimStopResetTargets($sid) {

        $q = <<<EOQ
SELECT
	OrderSeq
FROM
	T_Order
WHERE
	SiteId = :SiteId AND
	DataStatus = 51 AND
	Cnl_Status = 0 AND
	(
		LetterClaimStopFlg = 1 OR
		MailClaimStopFlg = 1
	)
ORDER BY
	OrderSeq
EOQ;

        $ri = $this->app->dbAdapter->query($q)->execute(array(':SiteId' => $sid));

        return ResultInterfaceToArray($ri);
    }

    /**
     * 自由項目更新アクション
     */
    public function freeitemsAction() {

        $params = $this->getParams();
        $list = $params['list'];
$this->app->logger->debug('freeitemsAction:');
$this->app->logger->debug($params);

        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
        $mdlPricePlan = new TablePricePlan($this->app->dbAdapter);
        $mdlCode = new TableCode($this->app->dbAdapter);
        $mdlDelMthd = new TableDeliMethod($this->app->dbAdapter);
        $mdlAgency = new TableAgency($this->app->dbAdapter);
        $mdlEnterprise = new TableEnterprise($this->app->dbAdapter);
        $mdlOem = new TableOem($this->app->dbAdapter);
        $mdlEnterprise = new TableEnterprise( $this->app->dbAdapter );
        $mdlSite = new TableSite( $this->app->dbAdapter );
        $mdlSfi = new TableSiteFreeItems( $this->app->dbAdapter );

        //サイト形態取得コードマスターID:10
        $datas['SiteForms'] = $codeMaster->getSiteFormMaster();

        //加盟店ID
        $enterpriseId = $list['EnterpriseId'];

        $enterpriseData = $mdlEnterprise->findEnterprise($enterpriseId)->current();
        $list['OemId'] = $enterpriseData['OemId'];
        $list['ApplicationDate'] = $enterpriseData['ApplicationDate'];

        //メールアドレス チェックオフの場合は0にする
        if (!isset($list['ReqMailAddrFlg']) && strlen($list['ReqMailAddrFlg']) <= 0) {
            $list['ReqMailAddrFlg'] = 0;
        }

        //初回請求用紙モード チェックオフの場合は0にする
        if (!isset($list['FirstClaimLayoutMode']) && strlen($list['FirstClaimLayoutMode']) <= 0) {
            $list['FirstClaimLayoutMode'] = 0;
        }

        //全案件補償外 チェックオフの場合は0にする
        if (!isset($list['OutOfAmendsFlg']) && strlen($list['OutOfAmendsFlg']) <= 0) {
            $list['OutOfAmendsFlg'] = 0;
        }

        //有効設定 チェックオフの場合は0にする
        if (!isset($list['ValidFlg']) && strlen($list['ValidFlg']) <= 0) {
            $list['ValidFlg'] = 0;
        }

        //NG無保証変更 チェックオフの場合は0にする
        if (!isset($list['NgChangeFlg']) && strlen($list['NgChangeFlg']) <= 0) {
            $list['NgChangeFlg'] = 0;
        }

        //利用プラン取得
        $pricePlan = $mdlPricePlan->find($enterpriseData['Plan'])->current();
        $list['PlanName'] = $pricePlan['PricePlanName'];

        //与信判定基準取得
        $sql  = " SELECT DISTINCT CreditCriterionId, CreditCriterionName FROM M_CreditPoint ";
        $ri = $this->app->dbAdapter->query($sql)->execute(null);
        $datas['CreditCriterionNames'] = ResultInterfaceToArray($ri);
        //与信判定方法
        $data=array();
        foreach ($mdlCode->getMasterByClass(94) as $row){
            $data[] = $row['KeyContent'];
        }
        $datas['Creditdecision'] = $data;

        //自動伝票番号登録時配送先取得
        $lgc = new \models\Logic\LogicDeliveryMethod($this->app->dbAdapter);
        $datas['DeliMethodName'] = $lgc->getEnterpriseDeliveryMethodList($enterpriseId, false);

        //請求書用紙種類取得
        $data=array();
        foreach ($mdlCode->getMasterByClass(79) as $row){
            $data[$row['KeyCode']] = $row['KeyContent'];
        }
        $datas['Invoice'] = $data;

        //請求書用紙種類取得（同梱）
        $data=array();
        foreach ($mdlCode->getMasterByClass(106) as $row){
            $data[$row['KeyCode']] = $row['KeyContent'];
        }
        $datas['InvoiceDK'] = $data;
        //口座振替利用
        $datas['CreditTransferFlg'] = $enterpriseData['CreditTransferFlg'];
        $list['CreditTransferFlg'] = $enterpriseData['CreditTransferFlg'];

        // ロゴ(小)
        $upload_file = $_FILES['list'];
        $image_uploaded = false;
        if(!empty($upload_file['name']['SmallLogo'])){
            $iul = new \models\Logic\LogicImageUpLoader($this->app->dbAdapter);

            //リロードされないようにセッション管理
            $tis = new Container('TMP_IMAGE_SEQ');

            $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
            try {
                // ユーザIDの取得
                $userTable = new \models\Table\TableUser($this->app->dbAdapter);
                $userID = $userTable->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

                //セッションにデータがある場合はセッションから画像データ取得する
                if(!is_null($tis->logo2)){
                    $logo2_seq = $tis->logo2;
                }
                else{
                    //画像を一時アップロード
                    $logo2_seq = $iul->saveLogo2TmpImage(null,$upload_file['type']['SmallLogo'],$upload_file['name']['SmallLogo'],$upload_file['tmp_name']['SmallLogo'],$userId);
                    $tis->logo2 = $logo2_seq;
                }
                //ロゴ2取得
                $logo2_data = $iul->getLogo2TmpImage($logo2_seq);

                if(!is_null($logo2_data)){
                    $list['SmallLogo']['image'] = $logo2_data['ImageData'];
                    $list['SmallLogo']['seq'] = $logo2_seq;
                    $image_uploaded = true;
                }

                $this->app->dbAdapter->getDriver()->getConnection()->commit();
            }
            catch(\Exception $err) {
                $this->app->dbAdapter->getDriver()->getConnection()->rollBack();
                throw $err;
            }
        }
        else {
            $mdlSite = new TableSite($this->app->dbAdapter);
            $list['SmallLogo']['image'] = $mdlSite->findSite($list['SiteId'])->current()['SmallLogo'];
        }

        // 自由項目
        $freeItems = unserialize(base64_decode($params['hashFreeItems']));
        if ( empty( $freeItems ) ) {
            for ( $i = 1; $i <= 20; $i++ ) {
                $list['Free'. $i] = '';
            }
        } else {
            if ( !empty($freeItems) ) {
                $list = array_merge( $list, $freeItems );
            }
        }

        // フォームデータ自身をエンコード
        $formData = base64_encode(serialize($list));

        $entdata = $mdlEnterprise->findEnterprise( $list['EnterpriseId'] )->current();

        $this->setPageTitle( sprintf( '%s - 自由項目設定', $this->getPageTitle() ) );

        $this->view->assign( 'encoded_data', $formData );
        $this->view->assign( 'imageUploaded', $image_uploaded );
        $this->view->assign( 'entdata', $entdata );
        $this->view->assign( 'EnterpriseId', $entdata['EnterpriseId'] );
        $this->view->assign( 'SiteId', $list['SiteId'] );
        $this->view->assign( 'sid', $list['SiteId'] );
        $this->view->assign( 'SiteNameKj', $list['SiteNameKj'] );
        $this->view->assign( 'data', $list );

        return $this->view;
    }

    /**
     * 自由項目設定単独更新アクション
     * freeitemsActionから遷移する
     */
    public function freeitemssaveAction() {

        $params = $this->getParams();

        // エンコード済みのPOSTデータを復元する
        $list = unserialize(base64_decode($params['hash']));

        $mdlEnterprise = new TableEnterprise( $this->app->dbAdapter );
        $mdlSite = new TableSite( $this->app->dbAdapter );
        $mdlsfi = new TableSiteFreeItems( $this->app->dbAdapter );

        // 自由入力項目１～２０ 入力桁数チェック
        $errors = array();
        for ( $i = 1; $i <= 20; $i++ ) {
            $chkTxt = mb_convert_encoding( $params['Free'. $i], 'sjis-win', 'UTF-8' );
            if ( strlen( $chkTxt ) > 50 ) {
                $errors['Free'. $i] = '自由項目'. mb_substr('　　'. mb_convert_kana($i, 'N'), -2). 'は半角50文字以下で入力してください。';
            }
        }

        // エラーがある場合は元の画面に戻る
        if ( !empty( $errors ) ) {
            $entdata = $mdlEnterprise->findEnterprise( $list['EnterpriseId'] )->current();
            $this->setPageTitle( sprintf( '%s - 自由項目設定', $this->getPageTitle() ) );
            $this->view->assign( 'encoded_data', $params['hash']);
            $this->view->assign( 'imageUploaded', $params['image_uploaded']);
            $this->view->assign( 'SiteId', $params['SiteId'] );
            $this->view->assign( 'sid', $params['SiteId'] );
            $this->view->assign( 'entdata', $entdata);
            $this->view->assign( 'EnterpriseId', $entdata['EnterpriseId'] );
            $this->view->assign( 'data', $params );
            $this->view->assign( 'errors', $errors );
            $this->setTemplate( 'freeitems' );
            return $this->view;

        }

        // ハッシュ化して退避してある値を更新する
        $freeItems = array();
        for ( $i = 1; $i <= 20; $i++ ) {
            $freeItems['Free'. $i] = $params['Free'. $i];
            $list['Free'. $i] = $params['Free'. $i];

        }

        // フォームデータ自身をエンコード
        $formData = base64_encode( serialize( $list ) );
        $params['hash'] = $formData;

        // 登録済みサイトの場合
        if ( isset( $params['SiteId'] ) && strlen( $params['SiteId'] ) > 0 ) {
            // 自由項目を登録済みの場合
            if ( $mdlsfi->find( $params['SiteId'] )->count() > 0 ) {
                // 入力値で更新する
                $rtn = $mdlsfi->saveUpdate( $list, $params['SiteId'] );

            } else {
                // 入力値で登録する
                $rtn = $mdlsfi->saveNew( $list );

            }

        }

        // リダイレクト
        return $this->_forward( 'back', $params );
    }
    /**
     * CB_B2C_DEV-46
     * 入力された収納代行会社IDと加入者固有コードを元に加入者固有コード管理マスタ検索を実施
     * 存在した場合、T_Site.ReceiptAgentId=収納代行会社ID、T_Site.SubscriberCode=加入者固有コードの件数を取得して、加入者固有名称と件数を画面上に表示
     */
    public function searchsubscriberAction($datas) {
    	try
    	{
    		// $resData = "(13441件) LINEPay利用不可";
    		$resData = "";
    		$query = " SELECT SubscriberName FROM M_SubscriberCode WHERE ValidFlg=1 AND ReceiptAgentId = :ReceiptAgentId AND SubscriberCode = :SubscriberCode ";
    		$stm = $this->app->dbAdapter->query($query);

    		$prm = array(
    				':ReceiptAgentId' => $datas["ReceiptAgentId"],
    				':SubscriberCode' => $datas["SubscriberCode"],
    		);

    		$tmp = $stm->execute($prm)->current();
    		if (!$tmp) {
                ;
    		}else{
    			//
    			$SubscriberName = $tmp['SubscriberName'];
    			// 何件データが存在しているか確認
    			$query = " SELECT count(*) as siteCnt FROM T_Site WHERE ReceiptAgentId = :ReceiptAgentId AND SubscriberCode = :SubscriberCode ";
    			$stm = $this->app->dbAdapter->query($query);
    			$SiteCnt = $stm->execute($prm)->current()['siteCnt'];

    			$resData = "({$SiteCnt}件) {$SubscriberName}";
    		}


    	}
    	catch(\Exception $e)
    	{
    		$resData = "(-件) 対象データが存在しませんでした。";
    	}

    	return $resData;
    }

    /**
     * 収納代行会社IDから加入者固有コード一覧を取得
     */
    public function sscodelistAction() {
		//
		$msg = "";
		$sscDataList = array();

    	//
    	$params = $this->getParams();
    	$ReceiptAgentId= $params['ra'];

    	//
    	$mdlSc = new TableSubscriberCode($this->app->dbAdapter);
    	$sscList = $mdlSc->findReceiptAgentIdSiteCntList($ReceiptAgentId);
    	if(empty($sscList)){
    		$msg = "対象データが存在しませんでした。";
    	}else{
    		$sscDataList = ResultInterfaceToArray($sscList);
    	}
    	//
        $this->setPageTitle("後払い.com - 収納代行会社一覧");
        $this->view->assign('msg', $msg);
    	$this->view->assign('sscDataList', $sscDataList);

    	//
    	return $this->view;
    }

    public function paymentAction() {
        $params = $this->getParams();
        $oid= $params['oid']; // oem id
        $sid= $params['sid']; // site id
        $oseq= $params['oseq']; // order seq

        $mdlPayment = new TablePayment($this->app->dbAdapter);
        $mdlSitePayment = new TableSitePayment($this->app->dbAdapter);
        $mdlSite = new TableSite($this->app->dbAdapter);
        $mdlCvs = new TableCvsReceiptAgent($this->app->dbAdapter);
        $orders = new TableOrder($this->app->dbAdapter);
        
        //order data
        $dataOrder = $orders->find($oseq)->current()['Deli_ConfirmArrivalFlg'];

        // get payment methods
        $tblSbpsPayment = new TableSbpsPayment($this->app->dbAdapter);
        $sbpsPayments = array();
        foreach ($tblSbpsPayment->getList($oid) as $row){
            $sbpsPayments[] = array(
                'PaymentId' => $row['SbpsPaymentId'],
                'PaymentName' => $row['PaymentNameKj']
            );
        }

        // get contractors
        $mdlCode = new TableCode($this->app->dbAdapter);
        $contractors = array();
        foreach ($mdlCode->getMasterByClass(212) as $row){
            $contractors[$row['KeyCode']] = array(
                'ContractorId' => $row['KeyCode'],
                'ContractorName' => $row['KeyContent']
            );
        }

        // get site payment by Site Id
        $sitePaymentsData = array();
        $tblSbpsPayment = new TableSiteSbpsPayment($this->app->dbAdapter);
        $sitePayments = ResultInterfaceToArray($tblSbpsPayment->getAll($sid));
        if ($sitePayments) {
            foreach ($sitePayments as $sitePayment) {
                $sitePaymentsData[$sitePayment['PaymentId']] = $sitePayment;
                $sitePaymentsData[$sitePayment['PaymentId']]['UseStartDate'] = $sitePayment['UseStartDate'] ? date('Y-m-d h:i:s', strtotime($sitePayment['UseStartDate'])) : '';
            }
        }

        // get min claim date
        $sql = "SELECT MIN(ClaimDate) AS MinClaimDate FROM T_ClaimHistory WHERE OrderSeq = :OrderSeq AND ClaimPattern = 1";
        $minClaimDate = $this->app->dbAdapter->query($sql)->execute( array(':OrderSeq' => $oseq) )->current()['MinClaimDate'];

        $site = $mdlSite->findSite($sid)->current();
        $agent = ResultInterfaceToArray($mdlCvs->findReceiptAgentId($site['ReceiptAgentId']));

        $payments = ResultInterfaceToArray($mdlPayment->fetchAllSubscriberCode($oid));
        $site_datas = ResultInterfaceToArray($mdlSitePayment->getAll($sid));
        $datas = array();
        // 支払方法を表示用に加工
        foreach ($payments as $payment) {
            $paymentId = $payment['PaymentId'];
            $datas[$paymentId]['PaymentGroupName'] = $payment['PaymentGroupName'];
            $datas[$paymentId]['PaymentName'] = $payment['PaymentName'];
        }
        foreach ($site_datas as $val) {
            $paymentId = $val['PaymentId'];
            if ($val['UseStartFixFlg'] != 1) {
                unset($datas[$paymentId]);
            }
            if (isset($datas[$paymentId])) {
                $datas[$paymentId]['UseFlg'] = $val['UseFlg'];
                $datas[$paymentId]['UseStartDate'] = $val['UseStartDate'];
            }
        }

        $query = " SELECT SubscriberName,LinePayUseFlg,LineApplyDate,LineUseStartDate,RakutenBankUseFlg,FamiPayUseFlg FROM M_SubscriberCode WHERE ValidFlg= 1 AND ReceiptAgentId = :ReceiptAgentId AND SubscriberCode = :SubscriberCode ";
        $stm = $this->app->dbAdapter->query($query);

        $prm = array(
            ':ReceiptAgentId' => $site['ReceiptAgentId'],
            ':SubscriberCode' => $site['SubscriberCode'],
        );

        $tmp = $stm->execute($prm)->current();
        if (!$tmp) {
            $datas = array();
        }

        $this->setPageTitle("後払い.com - 支払可能種類");
        $this->view->assign('ReceiptAgentName', $agent[0]['ReceiptAgentName']);
        $this->view->assign('payments', $datas);
        $this->view->assign('sbpsPayments', $sbpsPayments);
        $this->view->assign('sitePaymentsData', $sitePaymentsData);
        $this->view->assign('contractors', $contractors);
        $this->view->assign('minClaimDate', $minClaimDate);
        $this->view->assign('PaymentAfterArrivalFlg', $site['PaymentAfterArrivalFlg']);
        $this->view->assign('Deli_ConfirmArrivalFlg', $dataOrder);
        return $this->view;
    }

    /**
     * check format datetime
     */
    private function validateDateTime($date, $format = 'Y-m-d H:i')
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }
}
