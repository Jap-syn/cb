<?php
namespace cbadmin\Controller;

use Zend\Config\Reader\Ini;
use Zend\Json\Json;
use Coral\Base\BaseHtmlUtils;
use Coral\Base\BaseGeneralUtils;
use Coral\Base\IO\BaseIOUtility;
use Coral\Coral\Controller\CoralControllerAction;
use Coral\Coral\CoralCodeMaster;
use Coral\Coral\Mail\CoralMail;
use Coral\Coral\CoralValidate;
use Coral\Coral\Validate\CoralValidatePostalCode;
use Coral\Coral\Validate\CoralValidatePhone;
use Coral\Coral\Validate\CoralValidateMultiMail;
use cbadmin\Application;
use cbadmin\classes\EnterpriseCsvWriter;
use cbadmin\classes\EnterpriseCsvSettings;
use models\Table\TableApiUserEnterprise;
use models\Table\TableClaimHistory;
use models\Table\TableEnterprise;
use models\Table\TableEnterpriseCampaign;
use models\Table\TableOem;
use models\Table\TableOrder;
use models\Table\TablePasswordHistory;
use models\Table\TablePayingCycle;
use models\Table\TablePricePlan;
use models\Table\TableSite;
use models\Table\TableSelfBillingProperty;
use models\Table\TableSystemProperty;
use models\Table\TableUser;
use models\Logic\LogicTemplate;
use models\Table\TableTemplateField;
use models\Table\ATableEnterprise;
use models\Table\ATableEnterpriseCampaign;
use models\Table\TableBatchLock;
use models\Logic\LogicSbps;

class EnterpriseController extends CoralControllerAction {
    /**
     * バッチID
     * @var int
     */
    const EXECUTE_BATCH_ID = 3;

    const PARAMSDATA = "paramsdata";

	protected $_componentRoot = './application/views/components';

	/**
	 * @var Application
	 */
	protected $app;

    /**
     *  与信時注文利用額フラグ
     */
    protected $debugUserAmountOver = 0;

	/**
	 * IndexControllerを初期化する
	 */
	public function _init() {

        $this->app = Application::getInstance();

        $this->view->assign('userInfo', $this->app->authManagerAdmin->getUserInfo());
        $protocol = preg_match('/^on$/i', $_SERVER['HTTPS']) ? 'https' : 'http';

        $this->addStyleSheet('../css/default02.css');
        $this->addJavaScript('../js/prototype.js');
        $this->addJavaScript('../js/json.js');

        $this->setPageTitle("後払い.com - 事業者管理");
        $this->view->assign( 'current_action', $this->getActionName() );

        $mdlcjl = new \models\Table\TableCreditJudgeLock($this->app->dbAdapter);

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
                'ClaimClass' => array(1 => '次回繰越', 0 => '都度請求'),
                'TaxClass' => array(0 => '内税', 1 => '外税'),
                'JudgeSystemFlg' => array(0 => '行わない', 1 => '行う'),
                'AutoJudgeFlg' => array(0 => '行わない', 1 => '行う'),
                'JintecFlg' => array(0 => '行わない', 1 => '行う'),
                'ManualJudgeFlg' => array(0 => '行わない', 1 => '行う'),
                'CsvRegistClass' => array(0 => 'すべてOKで登録', 1 => 'エラーがあってもOK分のみ登録'),
                'CsvRegistErrorClass' => array(0 => 'エラー分の修正登録を行わない', 1 => 'エラー分の修正登録を行う'),
                'ReceiptStatusSearchClass' => array(0 => '入金ステータス検索不可', 1 => '入金ステータス検索可'),
                'AutoNoGuaranteeFlg' => array(0 => '自動無保証しない', 1 => '自動無保証する'),
                'DispDecimalPoint' => array(0 => '0', 1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5'),
                'UseAmountFractionClass' => array(0 => '切り捨て', 1 => '四捨五入', 2 => '切り上げ'),
                'CombinedClaimChargeFeeFlg' => array(0 => '全注文に対して店舗手数料を取る', 1 => '代表注文のみ店舗手数料を取る'),
                'PayingMail' => array(0 => '送信しない', 1=> '送信する'),
                'CreditThreadNo' => $mdlcjl->getThreadNoList(),
                'HoldBoxFlg' => array(0 => '利用しない', 1 => '利用する'),
                'SendMailRequestModifyJournalFlg' => array(0 => '送信しない', 1 => '送信する'),
                'ExecStopFlg' => array(0 => '停止しない', 1 => '停止する'),
                'LinePayUseFlg' => array(0 => '利用しない', 1 => '利用する'),
                'ApiOrderRestTimeOutFlg' => array(0 => '利用しない', 1 => '利用する'),
                'CreditTransferFlg' => array(0 => '利用しない', 1 => '利用する（SMBC）', 2 => '利用する(MUFJ)', 3 => '利用する(みずほ)'),
                'ClaimIndividualOutputFlg' => array(0 => '個別出力しない', 1 => '個別出力する'),
                'NTTSmartTradeFlg' =>array(0 => '利用しない', 1 => '利用する'),
                'IndividualSubscriberCodeFlg' => array(0 => '利用しない', 1 => '利用する'),
                'BillingAgentFlg' => array(0 => '利用しない', 1 => '利用する'),
                'IluCooperationFlg' => array(0 => '連携しない', 1 => '連携する'),
                'MoneyCheckFlg' => array(0 => '行わない', 1 => '行う'),
//                'AppFormIssueCond' => array(1 => '初回注文時', 2 => '請求金額0円時', 0 => '発行しない'),
                'AppFormIssueCond' => array(1 => '初回注文時', 2 => '請求金額0円時'),
                'ForceCancelClaimPattern' => array('' => '', 2 => '再請求１', 4 => '再請求３'),
                'ClaimPamphletPut' => array(0 => '利用しない', 1 => '利用する'),
        );

        $configs = $this->app->config['cj_api']; // ←移植が失敗していたので、cj_apiを追記

        //デバッグフラグ取得
        $userAmountOver = isset($configs['user_amount_over']) ? $configs['user_amount_over'] : $this->debugUserAmountOver;

        $this->view->assign('master_map', $masters);
        $this->view->assign('user_amount_over', $userAmountOver);
	}

	/**
	 * 事業者一覧を表示
	 */
	public function listAction() {

        $enterprises = new TableEnterprise($this->app->dbAdapter);

        //パラメータ取得
        $params = $this->getParams();

        $mdlOem = new TableOem($this->app->dbAdapter);

        //全てのOEM情報取得
        foreach($mdlOem->getAllOem(true) as $row) {
            $oemList[] = array(
                    'OemId' => $row['OemId'],
                    'OemNameKj' => $row['OemNameKj']
            );
        }

        //モードがセットされている&OEMモードである場合
        if(isset($params['mode']) && $params['mode'] == 'oem') {
            $this->view->assign('codeMaster', new CoralCodeMaster($this->app->dbAdapter));
            $this->view->assign('mode', 'oem');
            $this->view->assign('list', ResultInterfaceToArray($enterprises->getAllOemEnterprises()));
        } else {
            $oemList = array_merge(array(array('OemId' => 0, 'OemNameKj' => 'キャッチボール')), $oemList);
            $this->view->assign('codeMaster', new CoralCodeMaster($this->app->dbAdapter));
            $this->view->assign('list', ResultInterfaceToArray($enterprises->getAllEnterprises()));
        }
        $oemList = array_merge(array(array('OemId' => -1, 'OemNameKj' => '-----')), $oemList);
        $this->view->assign('oemList', $oemList);
        $this->view->assign('optionMap', $enterprises->getAllEnterpriseOptionInfo());

        return $this->view;
	}

	/**
	 * 事業者情報詳細画面を表示
	 */
	public function detailAction() {

        $req = $this->getParams();

        $eid = (isset($req['eid'])) ? $req['eid'] : -1;

        $enterprises = new TableEnterprise($this->app->dbAdapter);
        $mdlsites = new TableSite($this->app->dbAdapter);
        $oems = new TableOem($this->app->dbAdapter);

        $e = $enterprises->findEnterprise($eid)->current();

        if(!is_null($e['OemId'])) {
            $oemList = $oems->findOem($e['OemId'])->current();

            //OEM情報が取れている場合OEM名をセット
            if ($oemList) {
                $e['OemName'] = $oemList['OemNameKj'];
            }
        }

        // 利率を実数化
        $e['SettlementFeeRate'] = BaseGeneralUtils::ToRealRate($e['SettlementFeeRate']);

        // マスターがらみの項目については、キャプションを求めてセットする。
        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);

        $e['PreSales'] = $codeMaster->getPreSalesCaption((int)$e['PreSales']);
        $e['Industry'] = $codeMaster->getIndustryCaption((int)$e['Industry']);
        $e['Plan'] = $codeMaster->getPlanCaption((int)$e['Plan']);
        $e['FixPattern'] = $codeMaster->getFixPatternCaption((int)$e['FixPattern']);

        if ((int)$e['LimitDatePattern'] == 1) {
            $e['LimitDay'] = sprintf('翌月%s日', $codeMaster->getLimitDayCaption($e['LimitDay']));
        }
        else if ((int)$e['LimitDatePattern'] == 2) {
            $e['LimitDay'] = sprintf('当月%s日', $codeMaster->getLimitDayCaption($e['LimitDay']));
        }
        else {
            $e['LimitDay'] = '';
        }

        $e['LimitDatePattern'] = $codeMaster->getLimitDatePatternCaption((int)$e['LimitDatePattern']);

        $e['FfAccountClass'] = $codeMaster->getAccountClassCaption((int)$e['FfAccountClass']);
        $e['TcClass'] = $codeMaster->getTcClassCaption((int)$e['TcClass']);

        $e['AutoCreditJudgeMode'] = $codeMaster->getAutoCreditJudgeModeCaption((int)$e['AutoCreditJudgeMode']);

        $sites = ResultInterfaceToArray($mdlsites->getValidAll($e['EnterpriseId']));
        //請求取りまとめモードがサイト毎だった場合に対象サイト数を取得(2013.10.23 kaki)
        $num = 0;
        foreach($sites as &$site) {
            $site['SiteForm'] = $codeMaster->getSiteFormCaption($site['SiteForm']);
            $site['ReqMailAddrFlg'] = $site['ReqMailAddrFlg'] == 1 ? '必須' : '';

            if($site['CombinedClaimFlg'] == 1) {
                $num++;
            }
        }

        // 詳細画面からの更新処理で検証エラーが発生していたらその情報をマージする
        $e = array_merge($e, (isset($req['prev_input'])) ? $req['prev_input'] : array());
        $this->view->assign('error', (isset($req['prev_errors'])) ? $req['prev_errors'] : array());
        $backTo = isset($req['prev_backto']) ? $req['prev_backto'] : $_SERVER['HTTP_REFERER'];

        //OEM先判定
        $e['oem'] = isset($e["OemId"]) ? $e["OemId"] : 0;

        $this->view->assign('data', $e);
        $this->view->assign('sites', $sites);
        $this->view->assign('backTo', $backTo);
        $this->view->assign('combinedclaimnum', $num);

        // DocCollect, ExaminationResultについて、リテラルの連想配列を廃止し
        // CodeMasterの拡張を取り込んだ（09.06.08 eda）
        $this->view->assign('docCollectSelectTag',
            BaseHtmlUtils::SelectTag(
                'DocCollect',
                $codeMaster->getDocCollectMaster(),
                $e['DocCollect'],
                'style="width: 80px"'
            )
        );

        $this->view->assign('examinationResultSelectTag',
            BaseHtmlUtils::SelectTag(
                'ExaminationResult',
                $codeMaster->getExaminationResultMaster(),
                $e['ExaminationResult'],
                'style="width: 80px"'
            )
        );

        // 審査結果メール送信URLのアサイン
        $this->view->assign('urlSendExam', $this->app->tools['url']['sendexam']);

        // 送達確認用メール送信URLのアサイン 2014.3.31
        $this->view->assign('urlSendTest', $this->app->tools['url']['sendtest']);

        // 加盟店集計情報のアサイン
        $obj = new \models\Table\TableEnterpriseTotal($this->app->dbAdapter);
        $this->view->assign('enterpriseTotal', $obj->find($eid)->current());

        // 加盟店別配送方法
        $sql = <<<EOQ
SELECT edm.DeliMethodId
,      edm.ListNumber
,      dm.DeliMethodName
FROM   T_EnterpriseDelivMethod edm
       INNER JOIN M_DeliveryMethod dm ON (dm.DeliMethodId = edm.DeliMethodId)
WHERE  edm.EnterpriseId = :EnterpriseId
ORDER BY ListNumber
EOQ;
        $delivs = ResultInterfaceToArray($this->app->dbAdapter->query($sql)->execute(array(':EnterpriseId' => $eid)));
        // count関数対策
        $this->view->assign('delivs', (!empty($delivs)) ? $delivs : array());

        // 伝票番号自動仮登録機能の配送先リストをアサイン
        $shippingLogic = new \models\Logic\LogicShipping($this->app->dbAdapter, 0/* 更新必要性なし故ゼロ */);
        // 画面表示向けなので常に非OEM用の一覧を取得
        $this->view->assign('deliMethodMap', $shippingLogic->getDeliMethodListByOemId());

        // 事業者向け請求書同梱ツールに関する設定をアサイン（13.1.9 eda）
        $this->view->assign('sbsettings', $this->getEntSelfBillingSettings());

        $campaign = array();
        // 加盟店キャンペン情報取得
        // 加盟店IDに紐づくキャンペーン情報を期間でグループ化して取得する
        $sql = <<<EOQ
            SELECT  MIN(ec.Seq) AS Seq
                ,   MIN(ec.SiteId) AS SiteId
                ,   MAX(pp.PricePlanName) AS PricePlanName
                ,   ec.DateFrom
                ,   ec.DateTo
                ,   MAX(ec.MonthlyFee) AS MonthlyFee
            FROM    T_EnterpriseCampaign ec
                    INNER JOIN M_PricePlan pp ON (pp.PricePlanId = ec.AppPlan)
            WHERE   ec.EnterpriseId = :EnterpriseId
            GROUP BY
                    ec.DateFrom
                ,   ec.DateTo
            ;
EOQ;
        $ri = $this->app->dbAdapter->query($sql)->execute(array( 'EnterpriseId' => $eid ));
        $campaign = ResultInterfaceToArray($ri);

        $this->view->assign('campaign', $campaign);

        // 2015/09/18 Y.Suzuki Add 会計対応 Stt
        // 会計用項目を追加で取得
        $mdlae = new ATableEnterprise($this->app->dbAdapter);
        $atedata = $mdlae->find($eid)->current();

        $this->view->assign('atedata', $atedata);
        // 2015/09/18 Y.Suzuki Add 会計対応 End

        // サイト別不払率集計結果のアサイン
        $calcnp2 = new \models\Logic\LogicCalcNp2($this->app->dbAdapter);
        $this->view->assign('sitenplist', $calcnp2->makeEnterpriseNpList($eid));

        // 不払い率背景色しきい値(％)
        $npRateColorThreshold = $this->app->dbAdapter->query(" SELECT PropValue FROM T_SystemProperty WHERE Module = '[DEFAULT]' AND Category = 'systeminfo' AND Name = 'NpRateColorThreshold' ")->execute(null)->current()['PropValue'];
        $this->view->assign('npRateColorThreshold', $npRateColorThreshold);

        return $this->view;
	}

	/**
	 * 事業者登録フォームの表示
	 */
	public function formAction() {

        $params = $this->getParams();

        //パラメータ取得
        $oem = isset($params['oem']) ? $params['oem'] : 0;

        $mdlOem = new TableOem($this->app->dbAdapter);

        //全てのOEM情報取得
        $all_oem_data = $mdlOem->getAllOem();

        $oem_master = array();
        foreach ($all_oem_data as $value){
            //必要なものをOEMIDをキーに取得
            $oem_master[$value['OemId']] = array(
                    "MonthlyFee" => $value['MonthlyFee'],
                    "N_MonthlyFee" => $value['N_MonthlyFee'],
                    )
                    ;
        }

        //料金プラン情報取得
        $mdlPricePlan = new TablePricePlan($this->app->dbAdapter);
        $pricePlan = $mdlPricePlan->getAll();
        $priceplan_master = array();
        foreach ($pricePlan as $value){
            //必要なものをOEMIDをキーに取得
            $priceplan_master[$value['PricePlanId']] = array(
                    "MonthlyFee" => $value['MonthlyFee'],
            );
        }

        //与信時平均単価倍率の初期値取得
        $data = $this->app->config;
        $default_average_price_rate = $data['cj_api'];
        //与信保留要求の初期値
        $defaultPayingJudge = 1;
        //注文登録APIタイムアウト利用の初期値
        $apiOrderRestTimeOutFlg = 1;

        $this->view->assign('mode', '/mode/new');
        $this->view->assign('data', array(
                'isNew' => true,
                'AverageUnitPriceRate' => $default_average_price_rate['default_average_unit_price_rate'],
                'CreditJudgePendingRequest' => $defaultPayingJudge,
                'ApiOrderRestTimeOutFlg' => $apiOrderRestTimeOutFlg,
                'OemId' => $oem,
                'ValidFlg' => 1,
                'CreditThreadNo' => 3,
                'CreditJudgeValidDays' => 30,
                'AppFormIssueCond' => 2,
        ));
        $this->view->assign('selectOem',$oem);
        $this->view->assign('oemList', $mdlOem->getOemIdList());
        $this->view->assign('oem_master',$oem_master);
        $this->view->assign('priceplan_master',$priceplan_master);
        $this->view->assign('error', array());

        return $this->view;
	}

	/**
	 * 事業者編集画面を表示
	 */
	public function editAction() {
	    unset($_SESSION[self::PARAMSDATA]);
        $params = $this->getParams();

        $eid = (isset($params['eid'])) ? $params['eid'] : -1;

        $mdlEnterprise = new TableEnterprise($this->app->dbAdapter);

        // 事業者データを取得し、利率を実数化補正
        $eData = $this->fixSettelementFeeRate($mdlEnterprise->findEnterprise($eid)->current());

        $mdlOem = new TableOem($this->app->dbAdapter);

        if(!is_null($eData['OemId'])) {
            $oemList = $mdlOem->findOem($eData['OemId'])->current();

            //OEM情報が取れている場合OEM名をセット
            if ($oemList) {
                $eData['OemNameKj'] = $oemList['OemNameKj'];
            }
        }

        //全てのOEM情報取得
        $all_oem_data = $mdlOem->getAllOem();

        $oem_master = array();
        foreach ($all_oem_data as $value){
            //必要なものをOEMIDをキーに取得
            $oem_master[$value['OemId']] = array(
                    "MonthlyFee" => $value['MonthlyFee'],
                    "N_MonthlyFee" => $value['N_MonthlyFee'],
                    )
                    ;
        }

        //料金プラン情報取得
        $mdlPricePlan = new TablePricePlan($this->app->dbAdapter);
        $pricePlan = $mdlPricePlan->getAll();
        $priceplan_master = array();
        foreach ($pricePlan as $value){
            //必要なものをOEMIDをキーに取得
            $priceplan_master[$value['PricePlanId']] = array(
                    "MonthlyFee" => $value['MonthlyFee'],
            );
        }

        $campaign = array();
        // 加盟店キャンペン情報取得
        // 加盟店IDに紐づくキャンペーン情報を期間でグループ化して取得する
        $sql = <<<EOQ
            SELECT  MIN(ec.Seq) AS Seq
                ,   MIN(ec.SiteId) AS SiteId
                ,   MAX(pp.PricePlanName) AS PricePlanName
                ,   ec.DateFrom
                ,   ec.DateTo
                ,   MAX(ec.MonthlyFee) AS MonthlyFee
            FROM    T_EnterpriseCampaign ec
                    INNER JOIN M_PricePlan pp ON (pp.PricePlanId = ec.AppPlan)
            WHERE   ec.EnterpriseId = :EnterpriseId
            GROUP BY
                    ec.DateFrom
                ,   ec.DateTo
            ;
EOQ;
        $ri = $this->app->dbAdapter->query($sql)->execute(array( 'EnterpriseId' => $eid ));
        $campaign = ResultInterfaceToArray($ri);

        // 2015/09/18 Y.Suzuki Add 会計対応 Stt
        // 会計用項目を追加で取得
        $mdlae = new ATableEnterprise($this->app->dbAdapter);
        $atedata = $mdlae->find($eid)->current();

        $this->view->assign('atedata', $atedata);
        // 2015/09/18 Y.Suzuki Add 会計対応 End

        $data = array('isNew' => false);

        $this->view->assign('data', array_merge($data, $eData));
        $this->view->assign('error', array());
        $this->view->assign('oem_master',$oem_master);
        $this->view->assign('priceplan_master',$priceplan_master);
        $this->view->assign('CurrentFixPatternMsg', $currentFixPatternMsg);
        $this->view->assign('campaign', $campaign);

        $this->setTemplate('form');
        return $this->view;
    }

    /**
     * 事業者登録内容の確認
     */
    public function confirmAction() {

        $params = $this->getParams();

        //事業者編集のみ $paramsがNULLの場合(ロック中)とisNew:0が事業者編集
       if(empty($params['form']["isNew"])){
           //saveから_redirectしている場合セッションを$paramsに設定
           if(empty($params['form'])){
               if (isset($_SESSION[self::PARAMSDATA])){
                   $params = $_SESSION[self::PARAMSDATA];
               }
           }
            $_SESSION[self::PARAMSDATA] = $params;
        }

        $data = $this->fixInputForm( isset($params['form']) ? $params['form'] : array() );

        // 2015/09/18 Y.Suzuki Add 会計対応 Stt
        $ateData = isset($params['atform']) ? $params['atform'] : array();
        // 2015/09/18 Y.Suzuki Add 会計対応 End

        // PrintEntOrderIdOnClaimFlgの補完
        if (!isset($data['PrintEntOrderIdOnClaimFlg'])) $data['PrintEntOrderIdOnClaimFlg'] = 0;
        // AutoJournalIncModeの補完
        if(!isset($data['AutoJournalIncMode'])) $data['AutoJournalIncMode'] = 0;
        // CreditJudgePendingRequestの補完
        if(!isset($data['CreditJudgePendingRequest'])) $data['CreditJudgePendingRequest'] = 0;

        // AutoJournalDeliMethodIdの補完
        if(!isset($data['AutoJournalDeliMethodId']) || !$data['AutoJournalIncMode']) {
            $data['AutoJournalDeliMethodId'] = 0;
        }

        // OrderRevivalDisabledの補完
        if(!isset($data['OrderRevivalDisabled'])) $data['OrderRevivalDisabled'] = 0;

        // ValidFlgの補完
        if(!isset($data['ValidFlg'])) $data['ValidFlg'] = 0;

        if(!isset($data['ForceCancelDatePrintFlg'])) $data['ForceCancelDatePrintFlg'] = 0;
        if(!isset($data['ClaimIssueStopFlg'])) $data['ClaimIssueStopFlg'] = 0;
        if(!isset($data['FirstClaimIssueCtlFlg'])) $data['FirstClaimIssueCtlFlg'] = 0;
        if(!isset($data['ReClaimIssueCtlFlg'])) $data['ReClaimIssueCtlFlg'] = 0;
        if(!isset($data['FirstReClaimLmitDateFlg'])) $data['FirstReClaimLmitDateFlg'] = 0;
        if(!isset($data['ClaimOrderDateFormat'])) $data['ClaimOrderDateFormat'] = 0;

        $mdlOem = new TableOem($this->app->dbAdapter);
        if($_SESSION[self::PARAMSDATA]['errorFlg'] == 1){
            $errors['BatchLock'] = array("現在、事業者登録処理を行うことができません。しばらくたってから再度実行をお願い致します。");
        }else{
            $errors = $this->validate($data, $data['EnterpriseId']);
        }
        // 2015/09/18 Y.Suzuki Add 会計対応 Stt
        // OemIdのみマージする
        $ateErrors = $this->validateForAt(array_merge($ateData, array('OemId' => $data['OemId'])));
        // 2015/09/18 Y.Suzuki Add 会計対応 End

        // count関数対策
        if(!empty($errors) || !empty($ateErrors)) {           // 2015/09/18 Y.Suzuki 会計対応 Mod 条件を追加
            //全てのOEM情報取得
            $all_oem_data = $mdlOem->getAllOem();

            $oem_master = array();
            foreach ($all_oem_data as $value){
                //必要なものをOEMIDをキーに取得
                $oem_master[$value['OemId']] = array(
                        "MonthlyFee" => $value['MonthlyFee'],
                        "N_MonthlyFee" => $value['N_MonthlyFee'],
                        )
                        ;
            }

            //料金プラン情報取得
            $mdlPricePlan = new TablePricePlan($this->app->dbAdapter);
            $pricePlan = $mdlPricePlan->getAll();
            $priceplan_master = array();
            foreach ($pricePlan as $value){
                //必要なものをOEMIDをキーに取得
                $priceplan_master[$value['PricePlanId']] = array(
                        "MonthlyFee" => $value['MonthlyFee'],
                );
            }

            $campaign = array();
            // 加盟店キャンペン情報取得
            // 加盟店IDに紐づくキャンペーン情報を期間でグループ化して取得する
            $sql = <<<EOQ
            SELECT  MIN(ec.Seq) AS Seq
                ,   MIN(ec.SiteId) AS SiteId
                ,   MAX(pp.PricePlanName) AS PricePlanName
                ,   ec.DateFrom
                ,   ec.DateTo
                ,   MAX(ec.MonthlyFee) AS MonthlyFee
            FROM    T_EnterpriseCampaign ec
                    INNER JOIN M_PricePlan pp ON (pp.PricePlanId = ec.AppPlan)
            WHERE   ec.EnterpriseId = :EnterpriseId
            GROUP BY
                    ec.DateFrom
                ,   ec.DateTo
            ;
EOQ;
            $ri = $this->app->dbAdapter->query($sql)->execute(array( 'EnterpriseId' => $data['EnterpriseId'] ));
            $campaign = ResultInterfaceToArray($ri);


            // 検証エラーは入力画面へ戻す
            $this->view->assign('data', $data);
            $this->view->assign('selectOem',isset($data['OemId'])?$data['OemId']:0);
            $this->view->assign('oemList', $mdlOem->getOemIdList());
            $this->view->assign('oem_master',$oem_master);
            $this->view->assign('priceplan_master',$priceplan_master);
            $this->view->assign('campaign', $campaign);
            $this->view->assign('atedata', $ateData);       // 2015/09/18 Y.Suzuki 会計対応 Add
            $this->view->assign('error', array_merge($errors, $ateErrors));     // 2015/11/23 Y.Suzuki 会計対応 Mod

            $this->setTemplate('form');
            return $this->view;
        }

        // 都道府県名を展開
        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
        $data['PrefectureName'] = $codeMaster->getPrefectureName($data['PrefectureCode']);

        // フォームデータ自身をエンコード
        $formData = base64_encode(serialize($data));

        // 2015/09/18 Y.Suzuki Add 会計対応 Stt
        // 会計用フォームデータをエンコード
        $atformData = base64_encode(serialize($ateData));
        // 2015/09/18 Y.Suzuki Add 会計対応 End

        if(!is_null($data['OemId']) && $data['OemId'] != 0) {
            $mdlOem = new TableOem($this->app->dbAdapter);
            $oemList = $mdlOem->findOem($data['OemId'])->current();

            //OEM情報が取れている場合OEM名をセット
            if ($oemList) {
                $data['OemNameKj'] = $oemList['OemNameKj'];
            }
        }

        $this->view->assign('data', $data);
        $this->view->assign('atedata', $ateData);       // 2015/09/18 Y.Suzuki 会計対応 Add
        $this->view->assign('encoded_data', $formData);
        $this->view->assign('encoded_atedata', $atformData);        // 2015/09/18 Y.Suzuki 会計対応 Add

        // 伝票番号自動仮登録機能の配送先リストをアサイン
        $shippingLogic = new \models\Logic\LogicShipping($this->app->dbAdapter, 0/* 更新必要性なし故ゼロ */);
        // 画面表示向けなので常に非OEM用の一覧を取得
        $this->view->assign('deliMethodMap', $shippingLogic->getDeliMethodListByOemId());

        return $this->view;
    }

    /**
     * 確認画面からの戻り処理
     */
    public function backAction() {

        $params = $this->getParams();

        // エンコード済みのPOSTデータを復元する
        $eData = unserialize(base64_decode($params['hash']));
        $mdlOem = new TableOem($this->app->dbAdapter);

        // 2015/09/18 Y.Suzuki Add 会計対応 Stt
        // 会計用フォームデータを復元する
        $ateData = unserialize(base64_decode($params['hash_atedata']));
        // 2015/09/18 Y.Suzuki Add 会計対応 End

        //全てのOEM情報取得
        $all_oem_data = $mdlOem->getAllOem();

        $oem_master = array();
        foreach ($all_oem_data as $value){
            //必要なものをOEMIDをキーに取得
            $oem_master[$value['OemId']] = array(
                    "MonthlyFee" => $value['MonthlyFee'],
                    "N_MonthlyFee" => $value['N_MonthlyFee'],
                    )
                    ;
        }

        //料金プラン情報取得
        $mdlPricePlan = new TablePricePlan($this->app->dbAdapter);
        $pricePlan = $mdlPricePlan->getAll();
        $priceplan_master = array();
        foreach ($pricePlan as $value){
            //必要なものをOEMIDをキーに取得
            $priceplan_master[$value['PricePlanId']] = array(
                    "MonthlyFee" => $value['MonthlyFee'],
            );
        }

        $campaign = array();
        // 加盟店キャンペン情報取得
        // 加盟店IDに紐づくキャンペーン情報を期間でグループ化して取得する
        $sql = <<<EOQ
            SELECT  MIN(ec.Seq) AS Seq
                ,   MIN(ec.SiteId) AS SiteId
                ,   MAX(pp.PricePlanName) AS PricePlanName
                ,   ec.DateFrom
                ,   ec.DateTo
                ,   MAX(ec.MonthlyFee) AS MonthlyFee
            FROM    T_EnterpriseCampaign ec
                    INNER JOIN M_PricePlan pp ON (pp.PricePlanId = ec.AppPlan)
            WHERE   ec.EnterpriseId = :EnterpriseId
            GROUP BY
                    ec.DateFrom
                ,   ec.DateTo
            ;
EOQ;
        $ri = $this->app->dbAdapter->query($sql)->execute(array( 'EnterpriseId' => $eData['EnterpriseId'] ));
        $campaign = ResultInterfaceToArray($ri);

        $this->view->assign('data', array_merge($eData));
        $this->view->assign('error', array());
        $this->view->assign('oemList', $mdlOem->getOemIdList());
        $this->view->assign('oem_master',$oem_master);
        $this->view->assign('priceplan_master',$priceplan_master);
        $this->view->assign('CurrentFixPatternMsg', $currentFixPatternMsg);
        $this->view->assign('campaign', $campaign);
        $this->view->assign('atedata', $ateData);       // 2015/09/18 Y.Suzuki Add 会計対応

        $this->setTemplate('form');
        return $this->view;
    }

    /**
     * 事業者登録を実行
     */
    public function saveAction() {

        $params = $this->getParams();

        $eData = unserialize(base64_decode($params['hash']));

        // バッチ排他制御
        $mdlbl = new TableBatchLock (Application::getInstance()->dbAdapter);
        $BatchLock = $mdlbl->findBatchId($this::EXECUTE_BATCH_ID)['BatchLock'];
        if ($eData["isNew"] == 0 && $BatchLock > 0) {
            $_SESSION[self::PARAMSDATA]['errorFlg'] = 1;
            return $this->_redirect('enterprise/confirm');
        }
        unset($_SESSION[self::PARAMSDATA]);
        // 2015/09/18 Y.Suzuki Add 会計対応 Stt
        // 会計用フォームデータを復元する
        $ateData = unserialize(base64_decode($params['hash_atedata']));
        $mdlate = new ATableEnterprise($this->app->dbAdapter);
        // 2015/09/18 Y.Suzuki Add 会計対応 End

        $mdlEnterprise = new TableEnterprise($this->app->dbAdapter);

        // 住所を結合
        $eData['UnitingAddress'] = $eData['PrefectureName'] . $eData['City'] . $eData['Town'] . $eData['Building'];

        // 0を設定
        if(!$eData['AutoCreditJudgeMode']) {
            $eData['AutoCreditJudgeMode'] = 0;
        }

        // 伝票番号自動仮登録が無効の場合は自動登録時の配送方法IDを0にする
        if(!$eData['AutoJournalIncMode']) {
            $eData['AutoJournalDeliMethodId'] = 0;
        }

        // 「与信NG復活」の設定値を整備
        $eData['OrderRevivalDisabled'] = $eData['OrderRevivalDisabled'] ? 1 : 0;

        // 与信保留要求
        if(!$eData['CreditJudgePendingRequest']) {
            $eData['CreditJudgePendingRequest'] = 0;
        }

        if(!$eData['MhfCreditTransferDisplayName']) {
            $eData['MhfCreditTransferDisplayName'] = null;
        }

        $eData['ClaimOrderDateFormat'] = $eData['ClaimOrderDateFormat'] ? 1 : 0;

        $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
        try {
            // ユーザIDの取得
            $userTable = new \models\Table\TableUser($this->app->dbAdapter);
            $userID = $userTable->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

            if( ! $eData['isNew'] && isset($eData['EnterpriseId']) ) {
                // 編集モード時
                $eData['UpdateId'] = $userID;
                $mdlEnterprise->saveUpdate($eData, $eData['EnterpriseId']);
                // 2015/09/18 Y.Suzuki Add 会計対応 Stt
                // 会計用項目をUPDATE
                $mdlate->saveUpdate($ateData, $eData['EnterpriseId']);
                // 2015/09/18 Y.Suzuki Add 会計対応 End

                if ($eData['ReceiptStatusSearchClass'] == 0) {
                    // 入金ステータス検索不可の場合、履歴検索のテンプレートフィールドの入金状態、入金日を無効にする
                    $mdltf = new TableTemplateField($this->app->dbAdapter);
                    $sql = <<<EOQ
SELECT F.TemplateSeq
,      F.ListNumber
FROM   M_TemplateHeader H
       INNER JOIN M_TemplateField F ON F.TemplateSeq = H.TemplateSeq
WHERE  H.TemplateId    = 'CKA01005_1'
AND    H.TemplateClass = 2
AND    H.Seq           = :EnterpriseId
AND    (F.PhysicalName = 'IsWaitForReceipt' or
        F.PhysicalName = 'ReceiptDate' or
        F.PhysicalName = 'ReceiptProcessDate' or
        F.PhysicalName = 'ReceiptClass')
AND    F.ValidFlg      = 1
EOQ;
                    $ri = $this->app->dbAdapter->query($sql)->execute(array( 'EnterpriseId' => $eData['EnterpriseId'] ));
                    $fields = ResultInterfaceToArray($ri);
                    foreach($fields as $field) {
                        $mdltf->saveUpdate(array(
                                'ValidFlg' => 0,
                                'UpdateId' => $userID,
                        ), $field['TemplateSeq'], $field['ListNumber']);
                    }
                }
                if ($eData['CreditTransferFlg'] == 0) {
                    $mdltf = new TableTemplateField($this->app->dbAdapter);
                    $sql = <<<EOQ
SELECT F.TemplateSeq
,      F.ListNumber
FROM   M_TemplateHeader H
       INNER JOIN M_TemplateField F ON F.TemplateSeq = H.TemplateSeq
WHERE  H.TemplateId    = 'CKA01005_1'
AND    H.TemplateClass = 2
AND    H.Seq           = :EnterpriseId
AND    (F.PhysicalName = 'CreditTransferRequestFlg' or
        F.PhysicalName = 'RequestStatus' or
        F.PhysicalName = 'RequestSubStatus' or
        F.PhysicalName = 'RequestCompDate' or
        F.PhysicalName = 'CreditTransferMethod1' or
        F.PhysicalName = 'CreditTransferMethod2')
AND    F.ValidFlg      = 1
EOQ;
                    $ri = $this->app->dbAdapter->query($sql)->execute(array( 'EnterpriseId' => $eData['EnterpriseId'] ));
                    $fields = ResultInterfaceToArray($ri);
                    foreach($fields as $field) {
                        $mdltf->saveUpdate(array(
                                               'ValidFlg' => 0,
                                               'UpdateId' => $userID,
                                           ), $field['TemplateSeq'], $field['ListNumber']);
                    }
                }
            }
            else {
                // 新規モード時
                $eData['RegistId'] = $userID;
                $eData['UpdateId'] = $userID;

                $eData['Hashed'] = 1;
                $newId = $mdlEnterprise->saveNew($eData);

                // 2015/09/18 Y.Suzuki Add 会計対応 Stt
                // 会計用項目をINERT
                $ateData['EnterpriseId'] = $newId;
                $mdlate->saveNew($ateData);
                // 2015/09/18 Y.Suzuki Add 会計対応 End

                // ログインID向けプレフィックスを確定
                $entPrefix = nvl($mdlEnterprise->getLoginIdPrefix($newId), 'AT');

                $eData['EnterpriseId'] = $newId;                            // 獲得したプライマリキーをセットしておく
                $eData['LoginId'] = sprintf('%s%08d', $entPrefix, $newId);	// 新しいEnterpriseIdによりログインIDを指定
                $newPassword = $this->generateNewPassword($eData['LoginId']);// パスワードをランダム設定
                $eData['RegistDate'] = date("Y-m-d H:i:s");                 // 登録日時を設定

                // パスワードハッシュ適用
                /** @var BaseAuthUtility */
                $authUtil = $this->app->getAuthUtility();
                $eData['LoginPasswd'] = $authUtil->generatePasswordHash($eData['LoginId'], $newPassword);

                // パスワード更新日時を設定
                $eData['LastPasswordChanged'] = date('Y-m-d H:i:s');

                $mdlEnterprise->saveUpdate($eData, $newId);                 // 更新保存
                $eData['GeneratedPassword'] = $newPassword;

                // (加盟店別集計の登録)
                $obj = new \models\Table\TableEnterpriseTotal($this->app->dbAdapter);
                $obj->saveNew(array('EnterpriseId'=>$newId, 'RegistId'=>$userID, 'UpdateId'=>$userID));

                // T_User新規登録
                $userTable->saveNew(array('UserClass' => 2, 'Seq' => $newId, 'RegistId' => $userID, 'UpdateId' => $userID,));
            }

            $this->app->dbAdapter->getDriver()->getConnection()->commit();
        }
        catch(\Exception $err) {
            $this->app->dbAdapter->getDriver()->getConnection()->rollBack();
            throw $err;
        }

        // 保存済みデータをエンコード
        $data = base64_encode(serialize($eData));

        $this->view->assign('data', $data);

        return $this->view;
    }

    /**
     * 登録完了画面の表示
     */
    public function completionAction() {

        $params  = $this->getParams();

        $data = unserialize(base64_decode($params['hash']));
        if(!$data) {
            return $this->_redirect("enterprise/form");
        }

        if(!is_null($data['OemId']) && $data['OemId'] != 0) {
            $mdlOem = new TableOem($this->app->dbAdapter);
            $oemList = $mdlOem->findOem($data['OemId'])->current();

            //OEM情報が取れている場合OEM名をセット
            if ($oemList) {
                $data['OemNameKj'] = $oemList['OemNameKj'];
            }
        }

        $this->view->assign('eid', $data['EnterpriseId']);
        $this->view->assign('data', $data);

        return $this->view;
    }

    /**
     * 審査状況・備考更新アクション
     */
    public function upAction() {

        $eData = $this->getParams();
        $backTo = $_SERVER['HTTP_REFERER'];

        if( preg_match('/enterprise\/up/', $backTo) ) {
            // リファラがupActionだったらlistActionへ付け替える
            $backTo = f_path($this->getBaseUrl(), 'enterprise/list');
        }

        $mdle = new TableEnterprise($this->app->dbAdapter);

        $currentRow = $mdle->findEnterprise($eData['eid'])->current();
        if($currentRow) {
            // シーケンス指定が正しいので戻り先を再設定
            $backTo = f_path( $this->getBaseUrl(), 'enterprise/detail/eid/'.$currentRow['EnterpriseId'] );

            // T_Enterpriseのカラムに一致するもののみをキーとした連想配列へ詰めなおす
            $data = array();
            $inputKeys = array_keys($eData);
            foreach($currentRow as $key => $value) {
                if( in_array($key, $inputKeys) ) {
                    $data[$key] = $eData[$key];
                }
            }
            // 「特殊店舗」の値補正
            $data['Special01Flg'] = $data['Special01Flg'] ? 1 : 0;

            // 入力検証
            $errors = $this->validateForUp($data);
            // count関数対策
            if(!empty($errors)) {
                // 検証エラーがあったらdetailActionへフォワード
                $params = $this->getPureParams();
                $params['eid'] = $currentRow['EnterpriseId'];
                $params['prev_errors'] = $errors;
                $params['prev_backto'] = $backTo;
                $params['prev_input'] = $data;

                return $this->_forward('detail', $params);
            }

            // バッチ排他制御
            $mdlbl = new TableBatchLock (Application::getInstance()->dbAdapter);
            $BatchLock = $mdlbl->findBatchId($this::EXECUTE_BATCH_ID)['BatchLock'];
            if ( $BatchLock > 0) {
                $params = $this->getPureParams();
                $this->view->assign('message', '現在、事業者情報の更新処理を行うことができません。しばらくたってから再度実行をお願い致します。');
                $params['prev_input'] = $data;
                return $this->_forward('detail', $params);
            }

            // 更新処理
            $mdle->saveUpdate($data, $currentRow['EnterpriseId']);
            if( empty($data['PublishingConfirmDate']) ) {
                // 掲載確認日が空だったらnullを設定しなおす（'0000-00-00'対策）
                $mdle->saveUpdate(array('PublishingConfirmDate' => null), $currentRow['EnterpriseId']);
            }
        }

        return $this->redirect()->toUrl($backTo);
    }

    /**
     * キャンペーン設定一覧
     */
    public function campaignAction()
    {
        $params = $this->getParams();

        $eid = $params['eid'];
        $mode = $params['mode'];

        $campaign = array();

        if ($mode == 'edit') {
            $seq = $params['seq'];

            // 選択されたキャンペーン情報を取得する。
            $mdlec = new TableEnterpriseCampaign($this->app->dbAdapter);
            $campaign = $mdlec->find($seq)->current();
        }

        // サイト存在チェック
        $mdls = new TableSite($this->app->dbAdapter);
        $sites = ResultInterfaceToArray($mdls->getAll($eid));

        // 料金プランマスタのデータを取得する
        $mdlpp = new TablePricePlan($this->app->dbAdapter);
        $plan = ResultInterfaceToArray($mdlpp->getAll());

        // 立替サイクルマスタのデータを取得する
        $mdlpc = new TablePayingCycle($this->app->dbAdapter);
        $paying = ResultInterfaceToArray($mdlpc->findAll());

        // 2015/09/23 Y.Suzuki Add 会計対応 Stt
        // 会計用項目を追加で取得
        $atecdata = array();
        if ($mode == 'edit') {
            $seq = $params['seq'];

            $mdlatec = new ATableEnterpriseCampaign($this->app->dbAdapter);
            $atecdata = $mdlatec->find($seq)->current();
        }

        // count関数対策
        $siteCnt = 0;
        if (!empty($sites)){
            $siteCnt = count($sites);
        }

        $this->view->assign('atecdata', $atecdata);
        // 2015/09/23 Y.Suzuki Add 会計対応 End

        $this->view->assign('eid', $eid);
        $this->view->assign('mode', $mode);
        $this->view->assign('seq', $seq);
        $this->view->assign('data', $campaign);
        $this->view->assign('plan', $plan);
        $this->view->assign('paying', $paying);
        $this->view->assign('siteCnt', $siteCnt);
        // count関数対策
        if ($siteCnt==0) {
            $this->view->assign('compMsg', sprintf('<font color="red"><b>サイト情報が未登録のため、キャンペーン情報は登録できません。</b></font>'));
        }

        return $this->view;
    }

    /**
     * キャンペーン設定更新処理
     */
    public function campaigndoneAction()
    {
        $params = $this->getParams();

        $data = $params['form'];
        $mode = $params['mode'];
        $eid = $data['EnterpriseId'];
        $seq = $params['seq'];
        // 2015/09/23 Y.Suzuki Add 会計対応 Stt
        $atecdata = $params['atform'];
        // 2015/09/23 Y.Suzuki Add 会計対応 End

        // OEMIDを取得
        $mdle = new TableEnterprise($this->app->dbAdapter);
        $oemId = $mdle->find($eid)->current()['OemId'];

        // 立替ｻｲｸﾙIDを取得
        $payingCycleId = $mdle->find($eid)->current()['PayingCycleId'];

        // OEMID、立替ｻｲｸﾙID、ﾓｰﾄﾞをﾏｰｼﾞする。
        $data = array_merge($data, array( 'OemId' => $oemId, 'PayingCycleId' => $payingCycleId, 'isNew' => $mode == 'new' ? 1 :0 ));

        // 入力検証
        $errors = $this->validateForCampaign($data);
        // 2015/09/23 Y.Suzuki Add 会計対応 Stt
        // OemIdのみマージする
        $atecErrors = $this->validateForAtCampaign(array_merge($atecdata, array( 'OemId' => $oemId )));
        // 2015/09/23 Y.Suzuki Add 会計対応 End

        if (!empty($errors) || !empty($atecErrors)) {      // 2015/09/23 Y.Suzuki 会計対応 Mod 条件を追加
            // 料金プランマスタのデータを取得する
            $mdlpp = new TablePricePlan($this->app->dbAdapter);
            $plan = ResultInterfaceToArray($mdlpp->getAll());

            // 立替サイクルマスタのデータを取得する
            $mdlpc = new TablePayingCycle($this->app->dbAdapter);
            $paying = ResultInterfaceToArray($mdlpc->findAll());

            $this->view->assign('error', array_merge($errors, $atecErrors));
            $this->view->assign('eid', $eid);
            $this->view->assign('mode', $mode);
            $this->view->assign('seq', $seq);
            $this->view->assign('data', $data);
            $this->view->assign('plan', $plan);
            $this->view->assign('paying', $paying);
            $this->view->assign('atecdata', $atecdata);     // 2015/09/23 Y.Suzuki 会計対応 Add

            $this->setTemplate('campaign');

            return $this->view;
        }

        // エラーがなかったら後続処理
        $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
        try {
            // ユーザIDの取得
            $obj = new TableUser($this->app->dbAdapter);
            $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

            // 新規登録の場合、加盟店に紐づくサイト分、キャンペーンデータを作成する
            if ($mode == 'new') {
                // 加盟店に紐づくサイト情報を取得する。
                $mdls = new TableSite($this->app->dbAdapter);
                $sites = ResultInterfaceToArray($mdls->getAll($eid));

                // 取得できたサイト数分、キャンペーンデータを作成する。
                foreach ($sites as $site) {
                    $savedata = array(
                            'EnterpriseId' => $eid,                                     // 加盟店ID
                            'SiteId' => $site['SiteId'],                                // サイトID
                            'DateFrom' => $data['DateFrom'],                            // 期間FROM
                            'DateTo' => $data['DateTo'],                                // 期間TO
                            'MonthlyFee' => $data['MonthlyFee'],                        // 月額固定費
                            'AppPlan' => $data['AppPlan'],                              // 利用プラン
                            'OemMonthlyFee' => $data['OemMonthlyFee'],                  // OEM月額固定費
                            'PayingCycleId' => $data['PayingCycleId'],                  // 立替サイクル
                            'OemSettlementFeeRate' => $site['OemSettlementFeeRate'],    // OEM決済手数料率
                            'OemClaimFee' => $site['OemClaimFee'],                      // OEM請求手数料
                            'LimitDatePattern' => 0,                                    // 初回請求支払期限算出方法
                            'LimitDay' => null,                                         // 支払期限算出基準日
                            'Salesman' => null,                                         // 担当営業
                            'SettlementAmountLimit' => $site['SettlementAmountLimit'],  // 決済上限額
                            'SettlementFeeRate' => $site['SettlementFeeRate'],          // 決済手数料率
                            'ClaimFeeDK' => $site['ClaimFeeDK'],                        // 請求手数料（同梱）
                            'ClaimFeeBS' => $site['ClaimFeeBS'],                        // 請求手数料（別送）
                            'ReClaimFee' => $site['ReClaimFee'],                        // 再請求手数料
                            'SystemFee' => $site['SystemFee'],                          // システム手数料
                            'RegistId' => $userId,                                      // 登録者
                            'UpdateId' => $userId,                                      // 更新者
                    );

                    // 新規登録
                    $mdlec = new TableEnterpriseCampaign($this->app->dbAdapter);
                    $newSeq = $mdlec->saveNew($savedata);           // 2015/09/23 Y.Suzuki 会計対応 Add

                    // 2015/09/23 Y.Suzuki Add 会計対応 Stt
                    $mdlatec = new ATableEnterpriseCampaign($this->app->dbAdapter);
                    $mdlatec->saveNew(array_merge($atecdata, array('Seq' => $newSeq)));
                    // 2015/09/23 Y.Suzuki Add 会計対応 End
                }
            } else {
                // 更新前の情報を取得
                $mdlec = new TableEnterpriseCampaign($this->app->dbAdapter);
                $ecdata = $mdlec->find($seq)->current();
                // 更新前の期間を取得
                $dateFrom = $ecdata['DateFrom'];
                $dateTo = $ecdata['DateTo'];

                // -----------------------------
                // 更新対象を取得
                // -----------------------------
                $sql = <<<EOQ
                    SELECT  Seq
                    FROM    T_EnterpriseCampaign
                    WHERE   DateFrom        =   :DateFromBk
                    AND     DateTo          =   :DateToBk
                    AND     EnterpriseId    =   :EnterpriseId
EOQ;

                $prm = array(
                        'DateFromBk' => $dateFrom,                      // 更新条件（期間FROM）
                        'DateToBk' => $dateTo,                          // 更新条件（期間TO）
                        'EnterpriseId' => $eid,                         // 加盟店ID
                );

                $ri = $this->app->dbAdapter->query($sql)->execute($prm);

                // -----------------------------
                // 更新処理
                // -----------------------------
                $mdlatec = new ATableEnterpriseCampaign($this->app->dbAdapter);
                foreach ($ri as $row) {
                    // 更新処理
                    $sql = <<<EOQ
                    UPDATE  T_EnterpriseCampaign
                    SET     DateFrom        =   :DateFrom
                        ,   DateTo          =   :DateTo
                        ,   MonthlyFee      =   :MonthlyFee
                        ,   AppPlan         =   :AppPlan
                        ,   OemMonthlyFee   =   :OemMonthlyFee
                        ,   PayingCycleId   =   :PayingCycleId
                        ,   UpdateDate      =   :UpdateDate
                        ,   UpdateId        =   :UpdateId
                    WHERE   Seq             =   :Seq
                    ;
EOQ;
                    $prm = array(
                            'DateFrom' => $data['DateFrom'],                // 期間FROM
                            'DateTo' => $data['DateTo'],                    // 期間TO
                            'MonthlyFee' => $data['MonthlyFee'],            // 月額固定費
                            'AppPlan' => $data['AppPlan'],                  // 利用プラン
                            'OemMonthlyFee' => $data['OemMonthlyFee'],      // OEM月額固定費
                            'PayingCycleId' => $data['PayingCycleId'],      // 立替サイクル
                            'UpdateDate' => date('Y-m-d H:i:s'),            // 更新時間
                            'UpdateId' => $userId,                          // 更新者
                            'Seq' => $row['Seq'],                           // 更新条件（Seq）
                    );
                    $this->app->dbAdapter->query($sql)->execute($prm);

                    // 2015/09/23 Y.Suzuki Add 会計対応 Stt
                    $mdlatec->saveUpdate($atecdata, $row['Seq']);
                    // 2015/09/23 Y.Suzuki Add 会計対応 End
                }

            }
            // コミット
            $this->app->dbAdapter->getDriver()->getConnection()->commit();
        } catch (\Exception $e) {
            // ロールバック
            $this->app->dbAdapter->getDriver()->getConnection()->rollback();
            throw $e;
        }

        // 料金プランマスタのデータを取得する
        $mdlpp = new TablePricePlan($this->app->dbAdapter);
        $plan = ResultInterfaceToArray($mdlpp->getAll());

        // 立替サイクルマスタのデータを取得する
        $mdlpc = new TablePayingCycle($this->app->dbAdapter);
        $paying = ResultInterfaceToArray($mdlpc->findAll());

        $this->view->assign('data', $data);
        $this->view->assign('comp', true);
        $this->view->assign('plan', $plan);
        $this->view->assign('paying', $paying);
        $this->view->assign('atecdata', $atecdata);     // 2015/09/23 Y.Suzuki 会計対応 Add
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

        // dateForm: 期間FROM
        $key = 'DateFrom';
        if (!isset($errors[$key]) && (strlen($data[$key]) < 0)) {
            $errors[$key] = array("'期間FROM'を入力してください");
        }
        if (!isset($errors[$key]) && !IsValidFormatDate($data[$key])) {
            $errors[$key] = array("'期間FROM'の指定が不正です");
        }

        // dateTo: 期間TO
        $key = 'DateTo';
        if (!isset($errors[$key]) && (strlen($data[$key]) < 0)) {
            $errors[$key] = array("'期間TO'を入力してください");
        }
        if (!isset($errors[$key]) && !IsValidFormatDate($data[$key])) {
            $errors[$key] = array("'期間TO'の指定が不正です");
        }

        // dateFromTo: 範囲
        $key = 'dateFromTo';
        $key1 = 'DateFrom';
        $key2 = 'DateTo';
        // 新規の場合は無条件で以下処理を行う
        if ($data['isNew'] == 1) {
            // 期間FROM、期間TOの範囲内のデータが存在するか確認する。
            $sql = <<<EOQ
                SELECT  *
                FROM    T_EnterpriseCampaign
                WHERE   (DateFrom BETWEEN :dateFrom AND :dateTo
                         OR
                         DateTo BETWEEN :dateFrom AND :dateTo)
                AND     Seq <> :Seq
                AND     EnterpriseId = :EnterpriseId
                ;
EOQ;
            $cnt = $this->app->dbAdapter->query($sql)->execute(array( ':dateFrom' => $data[$key1], ':dateTo' => $data[$key2], ':Seq' => $data['Seq'], ':EnterpriseId' => $data['EnterpriseId'] ))->count();
            if (!isset($errors[$key]) && $cnt > 0) {
                $errors[$key] = array("'期間'が他のキャンペーンと重なっています");
            }
        // 更新の場合、条件によって判定する
        } else {
            // 既存の加盟店キャンペーン情報を取得する。
            $sql = " SELECT * FROM T_EnterpriseCampaign WHERE Seq = :Seq ";
            $campaign = $this->app->dbAdapter->query($sql)->execute(array( ':Seq' => $data['Seq'] ))->current();
            // 期間FROM、期間TOに変更がなければ、期間の範囲チェックは行わない。（期間に変更はないとみなす。）
            if ($data[$key1] == $campaign['DateFrom'] && $data[$key2] == $campaign['DateTo']) {
                // なにもしない
            } else if ($data[$key1] <> $campaign['DateFrom'] || $data[$key2] <> $campaign['DateTo']) {
                // 期間FROM、期間TOの範囲内のデータが存在するか確認する。
                $sql = <<<EOQ
                    SELECT  *
                    FROM    T_EnterpriseCampaign
                    WHERE   (DateFrom BETWEEN :dateFrom AND :dateTo
                             OR
                             DateTo BETWEEN :dateFrom AND :dateTo)
                    AND     Seq <> :Seq
                    AND     EnterpriseId = :EnterpriseId
                    ;
EOQ;
                $cnt = $this->app->dbAdapter->query($sql)->execute(array( ':dateFrom' => $data[$key1], ':dateTo' => $data[$key2], ':Seq' => $data['Seq'], ':EnterpriseId' => $data['EnterpriseId'] ))->count();
                if (!isset($errors[$key]) && $cnt > 0) {
                    $errors[$key] = array("'期間'が他のキャンペーンと重なっています");
                }
            }
        }

        // plan: 利用プラン
        $key = 'AppPlan';
        if (!isset($errors[$key]) && !((int)$data[$key] > 0)) {
            $errors[$key] = array("'利用プラン'を選択してください");
        }

        // monthlyFee: 月額固定費
        $key = 'MonthlyFee';
        if (!isset($errors[$key]) && (strlen($data[$key]) < 0)) {
            $errors[$key] = array("'月額固定費'を入力してください");
        }
        if (!isset($errors[$key]) && !(is_numeric($data[$key]))) {
            $errors[$key] = array("'月額固定費'の形式が不正です");
        }

        // 加盟店にOEMが設定されている場合（OEMID が 0より大きい場合）は以下処理を行う。
        if ($data['OemId'] > 0) {
            // oemMonthlyFee: OEM月額固定費
            $key = 'OemMonthlyFee';
            if (!isset($errors[$key]) && (strlen($data[$key]) < 0)) {
                $errors[$key] = array("'OEM月額固定費'を入力してください");
            }
            if (!isset($errors[$key]) && !(is_numeric($data[$key]))) {
                $errors[$key] = array("'OEM月額固定費'の形式が不正です");
            }
        }

        return $errors;
    }

    /**
     * キャンペーン 会計用項目の入力検証処理
     */
    protected function validateForAtCampaign($data = array())
    {
        $errors = array();

        // IncludeMonthlyFee: 同梱月額固定費
        $key = 'IncludeMonthlyFee';
        if (!isset($errors[$key]) && (strlen($data[$key]) > 0) && !(is_numeric($data[$key]))) {
            $errors[$key] = array("'同梱月額固定費'の形式が不正です");
        }

        // ApiMonthlyFee: API月額固定費
        $key = 'ApiMonthlyFee';
        if (!isset($errors[$key]) && (strlen($data[$key]) > 0) && !(is_numeric($data[$key]))) {
            $errors[$key] = array("'API月額固定費'の形式が不正です");
        }

        // CreditNoticeMonthlyFee: 与信結果通知サービス月額固定費
        $key = 'CreditNoticeMonthlyFee';
        if (!isset($errors[$key]) && (strlen($data[$key]) > 0) && !(is_numeric($data[$key]))) {
            $errors[$key] = array("'与信結果通知サービス月額固定費'の形式が不正です");
        }

        // NCreditNoticeMonthlyFee: 次回請求与信結果通知サービス月額固定費
        $key = 'NCreditNoticeMonthlyFee';
        if (!isset($errors[$key]) && (strlen($data[$key]) > 0) && !(is_numeric($data[$key]))) {
            $errors[$key] = array("'次回請求与信結果通知サービス月額固定費'の形式が不正です");
        }

        // ReserveMonthlyFee: 月額固定費予備
        $key = 'ReserveMonthlyFee';
        if (!isset($errors[$key]) && (strlen($data[$key]) > 0) && !(is_numeric($data[$key]))) {
            $errors[$key] = array("'月額固定費予備'の形式が不正です");
        }

        // 加盟店にOEMが設定されている場合（OEMID が 0より大きい場合）は以下処理を行う。
        if ($data['OemId'] > 0) {
            // OemIncludeMonthlyFee: OEM同梱月額固定費
            $key = 'OemIncludeMonthlyFee';
            if (!isset($errors[$key]) && (strlen($data[$key]) > 0) && !(is_numeric($data[$key]))) {
                $errors[$key] = array("'OEM同梱月額固定費'の形式が不正です");
            }
            // OemApiMonthlyFee: OEMAPI月額固定費
            $key = 'OemApiMonthlyFee';
            if (!isset($errors[$key]) && (strlen($data[$key]) > 0) && !(is_numeric($data[$key]))) {
                $errors[$key] = array("'OEMAPI月額固定費'の形式が不正です");
            }

            // OemCreditNoticeMonthlyFee: OEM与信結果通知サービス月額固定費
            $key = 'OemCreditNoticeMonthlyFee';
            if (!isset($errors[$key]) && (strlen($data[$key]) > 0) && !(is_numeric($data[$key]))) {
                $errors[$key] = array("'OEM与信結果通知サービス月額固定費'の形式が不正です");
            }

            // OemNCreditNoticeMonthlyFee: OEM次回請求与信結果通知サービス月額固定費
            $key = 'OemNCreditNoticeMonthlyFee';
            if (!isset($errors[$key]) && (strlen($data[$key]) > 0) && !(is_numeric($data[$key]))) {
                $errors[$key] = array("'OEM次回請求与信結果通知サービス月額固定費'の形式が不正です");
            }

            // OemReserveMonthlyFee: OEM月額固定費予備
            $key = 'OemReserveMonthlyFee';
            if (!isset($errors[$key]) && (strlen($data[$key]) > 0) && !(is_numeric($data[$key]))) {
                $errors[$key] = array("'OEM月額固定費予備'の形式が不正です");
            }
        }

        return $errors;
    }

    /**
	 * 事業者一覧のCSVダウンロード
	 */
	public function dcsvAction() {

        // CSVデータ取得
        $sql  = "SELECT ENT.MailAddress ";
        $sql .= "     , ENT.EnterpriseNameKj ";
        $sql .= "     , OEM.OemNameKj ";
        $sql .= "     , CONCAT(SUBSTRING(REPLACE(ENT.PostalCode, '-', ''), 1, 3), '-', SUBSTRING(REPLACE(ENT.PostalCode, '-', ''), 4)) AS PostalCode ";
        $sql .= "     , ENT.PrefectureName ";
        $sql .= "     , ENT.City ";
        $sql .= "     , ENT.Town ";
        $sql .= "     , ENT.Building ";
        $sql .= "     , ENT.ContactPhoneNumber ";
        $sql .= "     , ENT.CpNameKj ";
        $sql .= "     , DATE_FORMAT(ENT.ApplicationDate, '%Y/%m/%d') AS ApplicationDate ";
        $sql .= "     , DATE_FORMAT(ENT.PublishingConfirmDate, '%Y/%m/%d') AS PublishingConfirmDate ";
        $sql .= "     , DATE_FORMAT(ENT.ServiceInDate, '%Y/%m/%d') AS ServiceInDate ";
        $sql .= "     , C1.KeyContent AS DocCollect ";
        $sql .= "     , C2.KeyContent AS ExaminationResult ";
        $sql .= "     , DATE_FORMAT(ENT.RegistDate, '%Y/%m/%d') AS RegistDate ";
        $sql .= "     , ENT.LoginId ";
        $sql .= "     , ENT.EnterpriseNameKn ";
        $sql .= "     , ENT.RepNameKj ";
        $sql .= "     , ENT.RepNameKn ";
        $sql .= "     , ENT.Phone ";
        $sql .= "     , ENT.Fax ";
        $sql .= "     , C3.KeyContent AS PreSales ";
        $sql .= "     , C4.KeyContent AS Industry ";
        $sql .= "     , PP.PricePlanName AS Plan ";
        $sql .= "     , ENT.MonthlyFee ";
        $sql .= "     , ENT.OemMonthlyFee ";
        $sql .= "     , C5.KeyContent AS FixPattern ";
        $sql .= "     , ENT.Salesman ";
        $sql .= "     , ENT.FfName ";
        $sql .= "     , ENT.FfCode ";
        $sql .= "     , ENT.FfBranchName ";
        $sql .= "     , ENT.FfBranchCode ";
        $sql .= "     , ENT.FfAccountNumber ";
        $sql .= "     , C6.KeyContent AS FfAccountClass ";
        $sql .= "     , ENT.FfAccountName ";
        $sql .= "     , C7.KeyContent AS TcClass ";
        $sql .= "     , ENT.CpNameKn ";
        $sql .= "     , ENT.DivisionName ";
        $sql .= "     , ENT.ContactFaxNumber ";
        $sql .= "     , REPLACE(REPLACE(ENT.Note, CHAR(13), ''), CHAR(10), '') AS Note ";
        $sql .= "     , ENT.Notice ";
        $sql .= "     , DATE_FORMAT(ENT.B_ChargeFixedDate, '%Y/%m/%d') AS B_ChargeFixedDate ";
        $sql .= "     , DATE_FORMAT(ENT.B_ChargeDecisionDate, '%Y/%m/%d') AS B_ChargeDecisionDate ";
        $sql .= "     , DATE_FORMAT(ENT.B_ChargeExecDate, '%Y/%m/%d') AS B_ChargeExecDate ";
        $sql .= "     , DATE_FORMAT(ENT.N_ChargeFixedDate, '%Y/%m/%d') AS N_ChargeFixedDate ";
        $sql .= "     , DATE_FORMAT(ENT.N_ChargeDecisionDate, '%Y/%m/%d') AS N_ChargeDecisionDate ";
        $sql .= "     , DATE_FORMAT(ENT.N_ChargeExecDate, '%Y/%m/%d') AS N_ChargeExecDate ";
        $sql .= "     , ENT.N_MonthlyFee ";
        $sql .= "     , (CASE ENT.ValidFlg WHEN 1 THEN '有効' ELSE '無効' END) AS ValidFlg ";
        $sql .= "     , DATE_FORMAT(ET.NpCalcDate, '%Y/%m/%d') AS NpCalcDate ";
        $sql .= "     , ET.NpMolecule3 ";
        $sql .= "     , ET.NpGuaranteeMolecule3 ";
        $sql .= "     , ET.NpNoGuaranteeMolecule3 ";
        $sql .= "     , ET.NpDenominator3 ";
        $sql .= "     , ET.NpRate3 ";
        $sql .= "     , ET.NpGuaranteeRate3 ";
        $sql .= "     , ET.NpNoGuaranteeRate3 ";
        $sql .= "     , ET.NpMoleculeAll ";
        $sql .= "     , ET.NpGuaranteeMoleculeAll ";
        $sql .= "     , ET.NpNoGuaranteeMoleculeAll ";
        $sql .= "     , ET.NpDenominatorAll ";
        $sql .= "     , ET.NpRateAll ";
        $sql .= "     , ET.NpGuaranteeRateAll ";
        $sql .= "     , ET.NpNoGuaranteeRateAll ";
        $sql .= "     , ET.NpNgMolecule3 ";
        $sql .= "     , ET.NpNgDenominator3 ";
        $sql .= "     , ET.NpNgRate3 ";
        $sql .= "     , ET.NpNgMoleculeAll ";
        $sql .= "     , ET.NpNgDenominatorAll ";
        $sql .= "     , ET.NpNgRateAll ";
        $sql .= "     , ENT.EnterpriseId ";
        $sql .= "     , AG.AgencyNameKj ";
        $sql .= "  FROM T_Enterprise ENT ";
        $sql .= "       INNER JOIN T_EnterpriseTotal ET ON ET.EnterpriseId = ENT.EnterpriseId ";
        $sql .= "       LEFT JOIN T_Oem OEM ON OEM.OemId = ENT.OemId ";
        $sql .= "       LEFT JOIN M_Code C1 ON C1.CodeId = 84 AND C1.KeyCode = ENT.DocCollect ";
        $sql .= "       LEFT JOIN  C2 ON C2.CodeId = 75 AND C2.KeyCode = ENT.ExaminationResult ";
        $sql .= "       LEFT JOIN M_Code C3 ON C3.CodeId = 55 AND C3.KeyCode = ENT.PreSales ";
        $sql .= "       LEFT JOIN M_Code C4 ON C4.CodeId = 54 AND C4.KeyCode = ENT.Industry ";
        $sql .= "       LEFT JOIN M_PricePlan PP ON PP.PricePlanId = ENT.Plan ";
        $sql .= "       LEFT JOIN M_PayingCycle PC ON PC.PayingCycleId = ENT.PayingCycleId ";
        $sql .= "       LEFT JOIN M_Code C5 ON C5.CodeId = 2 AND C5.KeyCode = PC.FixPattern ";
        $sql .= "       LEFT JOIN M_Code C6 ON C6.CodeId = 51 AND C6.KeyCode = ENT.FfAccountClass ";
        $sql .= "       LEFT JOIN M_Code C7 ON C7.CodeId = 56 AND C7.KeyCode = ENT.TcClass ";
        $sql .= "       LEFT JOIN (SELECT AGS.EnterpriseId ";
        $sql .= "                       , MIN(AGS.AgencyId) AS AgencyId ";
        $sql .= "                    FROM M_AgencySite AGS ";
        $sql .= "                         INNER JOIN T_Site S ON S.SiteId = AGS.SiteId ";
        $sql .= "                   WHERE S.ValidFlg = 1 ";
        $sql .= "                 ) AGS ON AGS.EnterpriseId = ENT.EnterpriseId ";
        $sql .= "       LEFT JOIN M_Agency AG ON AG.AgencyId = AGS.AgencyId ";
        $sql .= " ORDER BY ENT.EnterpriseId DESC ";

        $ri = $this->app->dbAdapter->query($sql)->execute(null);
        $datas = ResultInterfaceToArray($ri);

        // CSV作成
        // テンプレートヘッダー/フィールドマスタを使用して、CSVをResposeに入れる
        $templateId = 'CKI15107_1';     // テンプレートID       事業者一覧CSV
        $templateClass = 0;             // 区分                 CB
        $seq = 0;                       // シーケンス           CB
        $templatePattern = 0;           // テンプレートパターン

        $logicTemplate = new LogicTemplate( $this->app->dbAdapter );
        $response = $logicTemplate->convertArraytoResponse( $datas, sprintf( 'Kameiten_%s.csv', date('YmdHis') ), $templateId, $templateClass, $seq, $templatePattern, $this->getResponse() );

        if( $response == false ) {
            throw new \Exception( $logicTemplate->getErrorMessage() );
        }

        return $response;
	}

	/**
	 * 事業者一覧のCSV取込(登録･修正)
	 */
	public function ucsvAction()
	{
//zzz 20150601_1620_現在アクションのみ用意
        $params = $this->getParams();
var_dump($params);
$hoge = new hoge_ucsvAction();
	}

	/**
	 * 請求書同梱ツール設定アクション
	 */
	public function sbsettingAction() {

        $config = $this->getEntSelfBillingSettings();

        $params = $this->getParams();

        $eid = isset($params['eid']) ? $params['eid'] : -1;

        $enterprises = new TableEnterprise($this->app->dbAdapter);
// 20150723 suzuki_h 削除
//         $sb_properties = new TableSelfBillingProperty($this->app->dbAdapter);
        $claim_histories = new TableClaimHistory($this->app->dbAdapter);

        $data = $enterprises->findEnterprise($eid)->current();
        // config.iniで無効になっているか事業者指定が不正な場合はリダイレクト
        if(!$config['enable_settings'] || $data['EnterpriseId'] != $eid) {
            $url = $data['EnterpriseId'] == $eid ? sprintf('enterprise/detail/eid/%s', $eid) : 'enterprise';
            return $this->_redirect($url);
        }

        //OEMIDがあればOEM同梱請求手数料取得
        if(nvl($data['OemId'],0) != 0){
            $mdlOem = new TableOem($this->app->dbAdapter);
            $oemData = $mdlOem->findOem($data['OemId'])->current();
            $claimFeeDK = ($oemData) ? $oemData['ClaimFeeDK'] : "";
        }

        // サイト情報から、伝票番号自動仮登録が"する"のデータがあるか確認
        $sql = " SELECT COUNT(*) AS CNT FROM T_Site WHERE EnterpriseId = :EnterpriseId AND AutoJournalIncMode = 1 AND ValidFlg = 1 ";
        $siteCnt = $this->app->dbAdapter->query($sql)->execute(array(':EnterpriseId' => $eid))->current();

        $sb_props = array();
// 20150723 suzuki_h 削除
//         $ri = $sb_properties->findByEnterpriseId($eid);
//         foreach($ri as $row) {
//             $sb_props[] = array_merge(
//                 $row,
//                 array('ch_count' => $claim_histories->findForSelfBillingByEnterpriseIdAndAccessKey($eid, $row['AccessKey'])->count())
//             );
//         }
//         $this->view->assign('sbprops', $sb_props);


        $this->view->assign('eid', $data['EnterpriseId']);
        $this->view->assign('data', $data);
// 20150723 suzuki_h 削除
//      $this->view->assign('sbprops', $sb_props);
        $this->view->assign('claimFeeDK',$claimFeeDK);
        $this->view->assign('printable_count', $claim_histories->findForSelfBillingByEnterprise($eid)->count());
        $this->view->assign('config', $config);
        $this->view->assign('siteCnt', $siteCnt['CNT']);

        return $this->view;
	}

	/**
	 * 請求書同梱ツール設定の更新アクション
	 */
	public function sbsettingupAction() {

        $config = $this->getEntSelfBillingSettings();

        $params = $this->getParams();

        $data = isset($params['form']) ? $params['form'] : array('mode' => 0, 'eid' => -1);
        $eid = $data['eid'];

        $enterprises = new TableEnterprise($this->app->dbAdapter);
        $claim_histories = new TableClaimHistory($this->app->dbAdapter);

        $current = $enterprises->findEnterprise($eid)->current();

        // config.iniで無効になっているか事業者指定が不正な場合はリダイレクト
        if(!$config['enable_settings'] || $current['EnterpriseId'] != $eid) {
            $url = $current['EnterpriseId'] == $eid ? sprintf('enterprise/detail/eid/%s', $eid) : 'enterprise';
            return $this->_redirect($url);
        }

        $sb_mode = $data['mode'] ? 1 : 0;
        $mode_pending = $sb_mode && $data['mode_pending'];
        $allow_update_journal = $sb_mode && $data['allow_update_journal'];
        $allow_export = $sb_mode && $data['allow_export'];
        $hide_tocb_button = $data['hide_tocb_button'] ? true : false;
        $charge_class = $data['charge_class'];
        $order_page_use_flg = $data['order_page_use_flg'];
        $self_billing_printed_auto_update_flg = $data['self_billing_printed_auto_update_flg'];
        if ($data['target_list_limit'] == ""){
            $target_list_limit = null;
        }else {
            $target_list_limit = $data['target_list_limit'];
        }

        // 一時保留チェック時
        // → SelfBillingModeを-1
        // → CSV出力を不許可（＝SelfBillingExportAllowを0）に
        if($mode_pending) {
            $sb_mode = -1;
            $allow_export = false;
            $order_page_use_flg = 1;
            // ※：一時保留時は、HideToCbButtonは変更しない
        }

        // 伝票番号更新機能許可時はSelfBillingModeを11に
        if($allow_update_journal && !$mode_pending) $sb_mode = 11;

        // SelfBillingModeが0の場合の処理
        // → キー削除要求・キー生成要求を強制変更
        // → CSV出力不許可
        if(!$sb_mode) {

            $allow_export = false;
            $hide_tocb_button = false;
            $charge_class = 0;
            $target_list_limit = null;
            $order_page_use_flg = 1;
        }

        // データ更新を実行
        $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
        try {
            // 事業者データの更新
            $u_data = array(
                    'SelfBillingMode' => $sb_mode,
                    'SelfBillingExportAllow' => $allow_export ? 1 : 0,
                    'ChargeClass' => $charge_class,
                    'TargetListLimit' => $target_list_limit,
                    'OrderpageUseFlg' => $order_page_use_flg,
                    'SelfBillingPrintedAutoUpdateFlg' => $self_billing_printed_auto_update_flg,
            );
            if(!$mode_pending) {
                // HideToCbButtonの更新は、一時保留でない場合のみ
                $u_data['HideToCbButton'] = $hide_tocb_button ? 1 : 0;
            }

            $enterprises->saveUpdate($u_data, $eid);

            // すべての変更をコミット
            $this->app->dbAdapter->getDriver()->getConnection()->commit();
        }
        catch (\Exception $err) {
            // 変更をロールバック
            $this->app->dbAdapter->getDriver()->getConnection()->rollback();

            // エラーメッセージを伴って同梱ツール設定画面へ遷移
            $this->view->assign('update_error', $err->getMessage());
            $_POST['eid'] = $eid;

            return $this->_forward('sbsetting');
        }

        // 更新が正常終了したので詳細画面へリダイレクト
        return $this->_redirect(sprintf('enterprise/detail/eid/%s', $eid));
	}

	/**
	 * 同梱ツール向けアクセスキー別未印刷件数取得アクション（Ajax）
	 */
	public function sbprintablecountAction() {

        $params = $this->getParams();

        $eid = isset($params['eid']) ? $params['eid'] : -1;

        $claim_histories = new TableClaimHistory($this->app->dbAdapter);

        $result = array(
                'success' => 0,
                'list' => array(),
                'message' => ''
        );

        try {
            $sb_props[] = array(
                    'count' => $claim_histories->findForSelfBillingByEnterprise($eid),
            );

            $result['success'] = 1;
            $result['list'] = $sb_props;
        }
        catch(\Exception $err) {
            $result['success'] = 0;
            $result['message'] = $err->getMessage();
        }

        $res = $this->getResponse();
        $res->getHeaders()->addHeaderLine( 'Content-Type', 'application/json' );
        echo Json::encode($result);

        return $this->getResponse();
	}

	/**
	 * 請求ストップ解除アクション
	 */
	public function resetacsAction() {

        $params = $this->getParams();

        $ent = $this->_getEnterpriseData( isset($params['eid']) ? $params['eid'] : -1 );
        if (!$ent) {
            throw new \Exception(sprintf("事業者ID '%s' は不正な指定です", $eid));
        }

        $list = $this->_getAutoClaimStopResetTargets($ent['EnterpriseId']);

        $this->setPageTitle(sprintf('%s - 請求ストップ解除', $this->getPageTitle()));

        $this->view->assign('data', $ent);
        // count関数対策
        $this->view->assign('can_reset', !empty($list));
        $this->view->assign('targets', $list);

        return $this->view;
	}

	/**
	 * 指定IDの事業者データを配列で取得する
	 *
	 * @access protected
	 * @param int $eid 事業者ID
	 * @return array
	 */
	protected function _getEnterpriseData($eid) {
        $enterprises = new TableEnterprise($this->app->dbAdapter);
        return $enterprises->findEnterprise($eid)->current();
	}

	/**
	 * 請求ストップ解除対象のリストを取得する
	 *
	 * @access protected
	 * @param int $eid 事業者ID
	 * @return array
	 */
	protected function _getAutoClaimStopResetTargets($eid) {

        $q = <<<EOQ
SELECT
	OrderSeq
FROM
	T_Order
WHERE
	EnterpriseId = :EnterpriseId AND
	DataStatus = 51 AND
	Cnl_Status = 0 AND
	(
		LetterClaimStopFlg = 1 OR
		MailClaimStopFlg = 1
	)
ORDER BY
	OrderSeq
EOQ;

        $ri = $this->app->dbAdapter->query($q)->execute(array(':EnterpriseId' => $eid));

        return ResultInterfaceToArray($ri);
	}

	/*
	 * 請求ストップ解除実行アクション
	 */
	public function resetacsdoneAction() {

        $params = $this->getParams();

        $ent = $this->_getEnterpriseData( isset($params['eid']) ? $params['eid'] : -1 );
        if (!$ent) {
            throw new \Exception(sprintf("事業者ID '%s' は不正な指定です", $eid));
        }
        $eid = $ent['EnterpriseId'];

        $orders = new TableOrder($this->app->dbAdapter);
        $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
        try {
            $row_tmpl = array(
                    'LetterClaimStopFlg' => 0,
                    'MailClaimStopFlg' => 0
            );
            $list = $this->_getAutoClaimStopResetTargets($ent['EnterpriseId']);
            foreach($list as $item) {
                $orders->saveUpdate($row_tmpl, $item['OrderSeq']);
            }
            $this->app->dbAdapter->getDriver()->getConnection()->commit();
        }
        catch(\Exception $err) {
            $this->app->dbAdapter->getDriver()->getConnection()->rollBack();
            throw $err;
        }

        return $this->_redirect(sprintf('enterprise/resetacs/eid/%s', $eid));
	}

	/**
	 * 請求自動ストップ設定単独更新アクション
	 * resetacsActionから遷移する
	 */
	public function acsupdateAction() {

        $params = $this->getParams();

        $params = (isset($params['form']) ? $params['form'] : array());

        // 対象事業者抽出
        $ent = $this->_getEnterpriseData($params['eid']);
        if (!$ent) {
            if(empty($params['eid'])) {
                throw new \Exception('事業者IDが指定されていません');
            }
            else {
                throw new \Exception(sprintf("事業者ID '%s' は不正な指定です",$params['eid']));
            }
        }

        $enterprises = new TableEnterprise($this->app->dbAdapter);

        // 請求自動ストップ機能のみ更新
        $enterprises->saveUpdate(array('AutoClaimStopFlg' => ((int)$params['AutoClaimStopFlg']) ? 1 : 0), $ent['EnterpriseId']);

        // 請求ストップ解除ページへリダイレクト
        return $this->_redirect(sprintf('enterprise/resetacs/eid/%s', $ent['EnterpriseId']));
	}

	/**
	 * 事業者パスワードリセットアクション
	 */
	public function resetpswAction() {

        $params = $this->getParams();

        $entId = isset($params['eid']) ? $params['eid'] : -1;

        // ユーザIDの取得
        $userTable = new \models\Table\TableUser($this->app->dbAdapter);
        $userID = $userTable->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

        try {
            $ent = $this->_getEnterpriseData($entId);

            if($ent) {
                // ランダムパスワードを生成
                $newPassword = $this->generateNewPassword($ent['LoginId']);
                $authUtil = $this->app->getAuthUtility();
                $entTable = new TableEnterprise($this->app->dbAdapter);

                // ハッシュ済みパスワードで更新
                $eData = array(
                        'LoginPasswd' => $authUtil->generatePasswordHash($ent['LoginId'], $newPassword),
                        'Hashed' => 1,
                        'LastPasswordChanged' => date('Y-m-d H:i:s')
                );

                $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
                try {
                    // パスワード更新実行
                    $entTable->saveUpdate($eData, $ent['EnterpriseId']);

                    // パスワード通知メールを送信
                    $mail = new CoralMail($this->app->dbAdapter, $this->app->mail['smtp']);
                    $mail->SendPasswdInfoMail($entId, $newPassword, $userID);

                    // DB変更をコミット
                    $this->app->dbAdapter->getDriver()->getConnection()->commit();
                }
                catch(\Exception $innerError) {
                    $this->app->dbAdapter->getDriver()->getConnection()->rollBack();
                    throw $innerError;
                }

                // 生成された生パスワードをセッションに退避
                $_SESSION['ent_resetpsw_newpsw'] = $newPassword;

                $mdlep = new TableEnterprise($this->app->dbAdapter);
                $mdlph = new TablePasswordHistory($this->app->dbAdapter);
                $mdlsp = new TableSystemProperty($this->app->dbAdapter);

                // パスワード期限切れ日数(日)の取得
                $propValue = $mdlsp->getValue("[DEFAULT]", "systeminfo", "PasswdLimitDay");

                $row = $mdlep->findEnterprise($entId)->current();
                $oemId = $row['OemId'];

                if(nvl($oemId, 0) == 0) {
                    $category = 2;
                } else {
                    $category = 3;
                }

                // パスワード履歴テーブルに１件追加
                $data = array(
                     'Category'       => $category
                    ,'LoginId'        => $ent['LoginId']
                    ,'LoginPasswd'    => $authUtil->generatePasswordHash($ent['LoginId'], $newPassword)
                    ,'PasswdStartDay' => date('Y-m-d')
                    ,'PasswdLimitDay' => date('Y-m-d', strtotime("$propValue day"))
                    ,'Hashed'         => 1
                    ,'RegistId'       => $userID
                    ,'UpdateId'       => $userID
                    ,'ValidFlg'       => 0
                );
                $mdlph->saveNew($data);

                // パスワード履歴テーブルの有効フラグを更新
                $mdlph->validflgUpdate($category, $ent['LoginId'], $userID);


                // 完了画面へリダイレクト
                return $this->_redirect(sprintf('enterprise/resetpswdone/eid/%d', $ent['EnterpriseId']));
            }
            else {
                throw new \Exception(sprintf('不正な事業者IDが指定されました。　id: %s は無効です', $entId));
            }
        }
        catch(\Exception $err) {
            // 例外はメッセージ表示を伴ってdetailへforward
            $_POST['eid'] = $entId;
            $_POST['prev_errors'] = array('EnterpriseId' => $err->getMessage());

            return $this->_forward('detail');
        }
	}

	/**
	 * 事業者パスワードリセット完了アクション
	 */
	public function resetpswdoneAction() {

        $params = $this->getParams();

        // セッションデータから生成済み生パスワードを取得
        $sess_key = 'ent_resetpsw_newpsw';
        $newPassword = $_SESSION[$sess_key];
        unset($_SESSION[$sess_key]);

        $data = $this->_getEnterpriseData( isset($params['eid']) ? $params['eid'] : -1 );
        $data['GeneratedPassword'] = $newPassword;

        //Oemの情報があればOEM名取得
        if(!is_null($data['OemId'])) {
            $mdlOem = new TableOem($this->app->dbAdapter);
            $oemList = $mdlOem->findOem($data['OemId'])->current();

            //OEM情報が取れている場合OEM名をセット
            if ($oemList) {
                $data['OemNameKj'] = $oemList['OemNameKj'];
            }
        }

        $this->view->assign('eid', $data['EnterpriseId']);
        $this->view->assign('data', $data);

        // 登録完了画面を流用
        $this->setTemplate('completion');
        return $this->view;
	}

	/**
	 * 事業者データ連送配列の利率を実数に補正する
	 *
	 * @access protected
	 * @param array $data 事業者データの連想配列
	 * @return array 利率が実数に補正された事業者データの連想配列
	 */
	protected function fixSettelementFeeRate($data) {
        // 利率を実数化
        $data["SettlementFeeRate"] = BaseGeneralUtils::ToRealRate($data["SettlementFeeRate"]);

        return $data;
	}

	/**
	 * POSTされた入力フォームに対し、未送信キーを補完する
	 *
	 * @access protected
	 * @param array $data POSTデータ
	 * @return array $dataの未送信キーを補完したデータ
	 */
	protected function fixInputForm(array $data) {
        $defaults = array(
                'FfAccountClass' => -1
        );

        return array_merge($defaults, $data);
	}

	/**
	 * 入力検証処理
	 *
	 * @access protected
	 * @param array $data
	 * @return array
	 */
	protected function validate($data = array(), $eid) {

        $isNew = $data['isNew'] ? true : false;

        $errors = array();

        // EnterpriseNameKj: 事業者名
        $key = 'EnterpriseNameKj';
        if (!isset($errors[$key]) && !(mb_strlen($data[$key]) > 0)) {
            $errors[$key] = array("'事業者名'は必須です");
        }
        if (!isset($errors[$key]) && !(mb_strlen($data[$key]) <= 160)) {
            $errors[$key] = array("'事業者名'は160文字以内で入力してください");
        }

        // EnterpriseNameKn: 事業者名カナ
        $key = 'EnterpriseNameKn';
        if (!isset($errors[$key]) && !(mb_strlen($data[$key]) > 0)) {
            $errors[$key] = array("'事業者名カナ'は必須です");
        }
        if (!isset($errors[$key]) && !(mb_strlen($data[$key]) <= 160)) {
            $errors[$key] = array("'事業者名カナ'は160文字以内で入力してください");
        }

        // PostalCode: 郵便番号
        $key = 'PostalCode';
        if (!isset($errors[$key]) && !(mb_strlen($data[$key]) > 0)) {
            $errors[$key] = array("'郵便番号'は必須です");
        }
        $cvpc = new CoralValidatePostalCode();
        if (!isset($errors[$key]) && !$cvpc->isValid($data[$key])) {
            $errors[$key] = array("'郵便番号'が不正な形式です");
        }

        // PrefectureCode: 都道府県
        $key = 'PrefectureCode';
        if (!isset($errors[$key]) && !(mb_strlen($data[$key]) > 0)) {
            $errors[$key] = array("'都道府県'を指定してください");
        }
        if (!isset($errors[$key]) && !((int)$data[$key] >= 1 && (int)$data[$key] <=47 )) {
            $errors[$key] = array("'都道府県'の指定が不正です");
        }

        // City: 市区郡
        $key = 'City';
        if (!isset($errors[$key]) && !(mb_strlen($data[$key]) > 0)) {
            $errors[$key] = array("'市区郡'は必須です");
        }
        if (!isset($errors[$key]) && !(mb_strlen($data[$key]) <= 255)) {
            $errors[$key] = array("'市区郡'は255文字以内で入力してください");
        }

        // Town: 町域
        $key = 'Town';
        if (!isset($errors[$key]) && !(mb_strlen($data[$key]) > 0)) {
            $errors[$key] = array("'町域'は必須です");
        }
        if (!isset($errors[$key]) && !(mb_strlen($data[$key]) <= 255)) {
            $errors[$key] = array("'町域'は255文字以内で入力してください");
        }

        // Building: ビル名
        $key = 'Building';
        if (!isset($errors[$key]) && !(mb_strlen($data[$key]) <= 255)) {
            $errors[$key] = array("'建物'は255文字以内で入力してください");
        }

        // RepNameKj: 代表者氏名
        $key = 'RepNameKj';
        if (!isset($errors[$key]) && !(mb_strlen($data[$key]) > 0)) {
            $errors[$key] = array("'代表者氏名'は必須です");
        }
        if (!isset($errors[$key]) && !(mb_strlen($data[$key]) <= 160)) {
            $errors[$key] = array("'代表者氏名'は160文字以内で入力してください");
        }

        // RepNameKn: 代表者氏名カナ
        $key = 'RepNameKn';
        if (!isset($errors[$key]) && !(mb_strlen($data[$key]) > 0)) {
            $errors[$key] = array("'代表者氏名カナ'は必須です");
        }
        if (!isset($errors[$key]) && !(mb_strlen($data[$key]) <= 160)) {
            $errors[$key] = array("'代表者氏名カナ'は160文字以内で入力してください");
        }

        // Phone: 電話番号
        $key = 'Phone';
        if (!isset($errors[$key]) && !(mb_strlen($data[$key]) > 0)) {
            $errors[$key] = array("'電話番号'は必須です");
        }
        $cvp = new CoralValidatePhone();
        if (!isset($errors[$key]) && !$cvp->isValid($data[$key])) {
            $errors[$key] = array("'電話番号'が不正な形式です");
        }

        // Fax: FAX番号
        $key = 'Fax';
        if (!isset($errors[$key]) && (mb_strlen($data[$key]) > 0) && !$cvp->isValid($data[$key])) {
            $errors[$key] = array("'FAX番号'が不正な形式です");
        }

        // PreSales: 推定月商
        $key = 'PreSales';
        if (!isset($errors[$key]) && (mb_strlen($data[$key]) > 0) && !(is_numeric($data[$key]))) {
            $errors[$key] = array("'推定月商'の指定が不正です");
        }

        // Industry: 業種
        $key = 'Industry';
        if (!isset($errors[$key]) && (mb_strlen($data[$key]) > 0) && !(is_numeric($data[$key]))) {
            $errors[$key] = array("'業種'の指定が不正です");
        }

        // AutoCreditJudgeMode: 与信
        $key = 'AutoCreditJudgeMode';
        if (!isset($errors[$key]) && (mb_strlen($data[$key]) > 0) && !((int)$data[$key] >= 0)) {
            $errors[$key] = array("'与信'の指定が不正です");
        }

        // CpNameKj: 担当者名
        $key = 'CpNameKj';
        if (!isset($errors[$key]) && !(mb_strlen($data[$key]) > 0)) {
            $errors[$key] = array("'担当者名'は必須です");
        }
        if (!isset($errors[$key]) && !(mb_strlen($data[$key]) <= 160)) {
            $errors[$key] = array("'担当者名'は160文字以内で入力してください");
        }

        // CpNameKn: 担当者名カナ
        $key = 'CpNameKn';
        if (!isset($errors[$key]) && !(mb_strlen($data[$key]) > 0)) {
            $errors[$key] = array("'担当者名カナ'は必須です");
        }
        if (!isset($errors[$key]) && !(mb_strlen($data[$key]) <= 160)) {
            $errors[$key] = array("'担当者名カナ'は160文字以内で入力してください");
        }

        // DivisionName: 部署名
        $key = 'DivisionName';
        if (!isset($errors[$key]) && !(mb_strlen($data[$key]) <= 255)) {
            $errors[$key] = array("'部署名'は255文字以内で入力してください");
        }

        // MailAddress: メールアドレス
        $key = 'MailAddress';
        if (!isset($errors[$key]) && !(mb_strlen($data[$key]) > 0)) {
            $errors[$key] = array("'メールアドレス'は必須です");
        }
        $cvmm = new CoralValidateMultiMail();
        if (!isset($errors[$key]) && !$cvmm->isValid($data[$key])) {
            $errors[$key] = array("'メールアドレス'が不正な形式です");
        }

        // ContactPhoneNumber: 連絡先電話番号
        $key = 'ContactPhoneNumber';
        if (!isset($errors[$key]) && !(mb_strlen($data[$key]) > 0)) {
            $errors[$key] = array("'連絡先電話番号'は必須です");
        }
        if (!isset($errors[$key]) && !$cvp->isValid($data[$key])) {
            $errors[$key] = array("'連絡先電話番号'が不正な形式です");
        }

        // ContactFaxNumber: 連絡先FAX番号
        $key = 'ContactFaxNumber';
        if (!isset($errors[$key]) && (mb_strlen($data[$key]) > 0) && !$cvp->isValid($data[$key])) {
            $errors[$key] = array("'連絡先FAX番号'が不正な形式です");
        }

        // ApplicationDate: 申込日
        $key = 'ApplicationDate';
        if (!isset($errors[$key]) && !(mb_strlen($data[$key]) > 0)) {
            $errors[$key] = array("'申込日'は必須です");
        }
        if (!isset($errors[$key]) && !IsValidFormatDate($data[$key])) {
            $errors[$key] = array("'申込日'の形式が不正です");
        }

        // Plan: 利用プラン
        $key = 'Plan';
        if (!isset($errors[$key]) && !(mb_strlen($data[$key]) > 0)) {
            $errors[$key] = array("'利用プラン'を選択してください");
        }
        if (!isset($errors[$key]) && !(is_numeric($data[$key]))) {
            $errors[$key] = array("'利用プラン'の指定が不正です");
        }
        if (!isset($errors[$key]) && !((int)$data[$key] > 0)) {
            $errors[$key] = array("'利用プラン'の指定が不正です");
        }

        // MonthlyFee: 月額固定費
        $key = 'MonthlyFee';
        if (!isset($errors[$key]) && !(mb_strlen($data[$key]) > 0)) {
            $errors[$key] = array("'月額固定費'は必須です");
        }
        if (!isset($errors[$key]) && !(is_numeric($data[$key]))) {
            $errors[$key] = array("'月額固定費'の指定が不正です");
        }

        // N_MonthlyFee: 次回請求月額固定費
        $key = 'N_MonthlyFee';
        if (!isset($errors[$key]) && !(mb_strlen($data[$key]) > 0)) {
            $errors[$key] = array("'次回請求月額固定費'は必須です");
        }
        if (!isset($errors[$key]) && !(is_numeric($data[$key]))) {
            $errors[$key] = array("'次回請求月額固定費'の指定が不正です");
        }

        if ($isNew) {
            // PayingCycleId: 立替サイクル
            $key = 'PayingCycleId';
            if (!isset($errors[$key]) && !(mb_strlen($data[$key]) > 0)) {
                $errors[$key] = array("'立替サイクル'を選択してください");
            }
            if (!isset($errors[$key]) && !(is_numeric($data[$key]))) {
                $errors[$key] = array("'立替サイクル'の指定が不正です");
            }
            if (!isset($errors[$key]) && !((int)$data[$key] > 0)) {
                $errors[$key] = array("'立替サイクル'の指定が不正です");
            }
        }

        // Salesman: 営業担当
        $key = 'Salesman';
        if (!isset($errors[$key]) && !(mb_strlen($data[$key]) <= 160)) {
            $errors[$key] = array("'営業担当'は160文字以内で入力してください");
        }

        // FfName: 銀行名
        $key = 'FfName';
        if (!isset($errors[$key]) && !(mb_strlen($data[$key]) > 0)) {
            $errors[$key] = array("'銀行名'は必須です");
        }
        if (!isset($errors[$key]) && !(mb_strlen($data[$key]) <= 160)) {
            $errors[$key] = array("'銀行名'は160文字以内で入力してください");
        }

        // FfCode: 銀行番号
        $key = 'FfCode';
        if (!isset($errors[$key]) && (mb_strlen($data[$key]) > 0) && !(is_numeric($data[$key]))) {
            $errors[$key] = array("'銀行番号'の形式が不正です");
        }

        // FfBranchName: 支店名
        $key = 'FfBranchName';
        if (!isset($errors[$key]) && !(mb_strlen($data[$key]) > 0)) {
            $errors[$key] = array("'支店名'は必須です");
        }
        if (!isset($errors[$key]) && !(mb_strlen($data[$key]) <= 160)) {
            $errors[$key] = array("'支店名'は160文字以内で入力してください");
        }

        // FfBranchCode: 支店番号
        $key = 'FfBranchCode';
        if (!isset($errors[$key]) && (mb_strlen($data[$key]) > 0) && !(is_numeric($data[$key]))) {
            $errors[$key] = array("'支店番号'の形式が不正です");
        }

        // FfAccountClass: 口座種別
        $key = 'FfAccountClass';
        if (!isset($errors[$key]) && !(mb_strlen($data[$key]) > 0)) {
            $errors[$key] = array("'口座種別'を選択してください");
        }
        if (!isset($errors[$key]) && !(is_numeric($data[$key]))) {
            $errors[$key] = array("'口座種別'の指定が不正です");
        }
        if (!isset($errors[$key]) && !((int)$data[$key] > 0)) {
            $errors[$key] = array("'口座種別'の指定が不正です");
        }

        // FfAccountNumber: 口座番号
        $key = 'FfAccountNumber';
        if (!isset($errors[$key]) && !(mb_strlen($data[$key]) > 0)) {
            $errors[$key] = array("'口座番号'は必須です");
        }
        if (!isset($errors[$key]) && !(mb_strlen($data[$key]) <= 80)) {
            $errors[$key] = array("'口座番号'は80文字以内で入力してください");
        }

        // FfAccountName: 口座名義
        $key = 'FfAccountName';
        if (!isset($errors[$key]) && !(mb_strlen($data[$key]) > 0)) {
            $errors[$key] = array("'口座名義'は必須です");
        }
        if (!isset($errors[$key]) && !(mb_strlen($data[$key]) <= 255)) {
            $errors[$key] = array("'口座名義'は255文字以内で入力してください");
        }

        // TcClass: 振込手数料
        $key = 'TcClass';
        if (!isset($errors[$key]) && !(mb_strlen($data[$key]) > 0)) {
            $errors[$key] = array("'振込手数料'を指定してください");
        }
        if (!isset($errors[$key]) && !(is_numeric($data[$key]))) {
            $errors[$key] = array("'振込手数料'の指定が不正です");
        }
        if (!isset($errors[$key]) && !((int)$data[$key] > 0)) {
            $errors[$key] = array("'振込手数料'の指定が不正です");
        }

        // FfBranchCode: 与信時注文利用額
        $key = 'FfBranchCode';
        if (!isset($errors[$key]) && (mb_strlen($data[$key]) > 0) && !(is_numeric($data[$key]))) {
            $errors[$key] = array("'与信時注文利用額'の形式が不正です");
        }

        // PrintEntOrderIdOnClaimFlg: 請求書任意注文番号印刷フラグ
        $key = 'PrintEntOrderIdOnClaimFlg';
        if (!isset($errors[$key]) && (mb_strlen($data[$key]) > 0) && !(is_numeric($data[$key]))) {
            $errors[$key] = array("'任意注文番号の印刷（請求書）'の指定が不正です");
        }
        if (!isset($errors[$key]) && (mb_strlen($data[$key]) > 0) && !((int)$data[$key] >= 0 && (int)$data[$key] <=1 )) {
            $errors[$key] = array("'任意注文番号の印刷（請求書）'に未定義の値が指定されました");
        }

        // AutoJournalIncMode: 伝票番号自動仮登録機能
        $key = 'AutoJournalIncMode';
        if (!isset($errors[$key]) && !(mb_strlen($data[$key]) > 0)) {
            $errors[$key] = array("'伝票番号自動仮登録機能'を指定してください");
        }
        if (!isset($errors[$key]) && (mb_strlen($data[$key]) > 0) && !(is_numeric($data[$key]))) {
            $errors[$key] = array("'伝票番号自動仮登録機能'の指定が不正です");
        }
        if (!isset($errors[$key]) && (mb_strlen($data[$key]) > 0) && !((int)$data[$key] >= 0 && (int)$data[$key] <=1 )) {
            $errors[$key] = array("'伝票番号自動仮登録機能'に未定義の値が指定されました");
        }

        // OrderRevivalDisabled: 「与信NG復活」機能（特記不要 20150715_2000）

        // 伝票番号自動仮登録が有効設定の場合は配送方法も検証
        if($data['AutoJournalIncMode']) {
            // AutoJournalDeliMethodId: 伝票番号自動仮登録用配送方法
            $key = 'AutoJournalDeliMethodId';
            if (!isset($errors[$key]) && !(mb_strlen($data[$key]) > 0)) {
                $errors[$key] = array("'伝票番号自動仮登録用配送方法'を指定してください");
            }
            if (!isset($errors[$key]) && (mb_strlen($data[$key]) > 0) && !(is_numeric($data[$key]))) {
                $errors[$key] = array("'伝票番号自動仮登録用配送方法'の指定が不正です");
            }
            if (!isset($errors[$key]) && !((int)$data[$key] > 0)) {
                $errors[$key] = array("'伝票番号自動仮登録用配送方法'を指定してください");
            }
        }

        // CreditNgDispDays: 与信NG表示期間
        $key = 'CreditNgDispDays';
        if (!isset($errors[$key]) && (mb_strlen($data[$key]) > 0) && !(is_numeric($data[$key]))) {
            $errors[$key] = array("'与信NG表示期間'の指定が不正です");
        }

        // CreditJudgeValidDays: 与信有効期間
        $key = 'CreditJudgeValidDays';
        if (!isset($errors[$key]) && !(mb_strlen($data[$key]) > 0)) {
            $errors[$key] = array("'与信有効期間'を指定してください");
        }
        if (!isset($errors[$key]) && (mb_strlen($data[$key]) > 0) && !(is_numeric($data[$key]))) {
            $errors[$key] = array("'与信有効期間'の指定が不正です");
        }
        if (!isset($errors[$key]) && !((int)$data[$key] > 0)) {
            $errors[$key] = array("'与信有効期間'を指定してください");
        }

        // Oem関連バリデーション$validators
        if($data['OemId'] != 0){

            // OemMonthlyFee: OEM月額固定費
            $key = 'OemMonthlyFee';
            if (!isset($errors[$key]) && !(mb_strlen($data[$key]) > 0)) {
                $errors[$key] = array("'OEM月額固定費'は必須です");
            }
            if (!isset($errors[$key]) && !(is_numeric($data[$key]))) {
                $errors[$key] = array("'OEM月額固定費'の指定が不正です");
            }

            // N_OemMonthlyFee: 次回請求OEM月額固定費
            $key = 'N_OemMonthlyFee';
            if (!isset($errors[$key]) && !(mb_strlen($data[$key]) > 0)) {
                $errors[$key] = array("'次回請求OEM月額固定費'は必須です");
            }
            if (!isset($errors[$key]) && !(is_numeric($data[$key]))) {
                $errors[$key] = array("'次回請求OEM月額固定費'の指定が不正です");
            }
        }

        // LINEPayFlg:: LINE Pay利用(SMBC店舗用)
        $key = 'LinePayUseFlg';

        if (($data['OemId'] != 2) && ($data[$key] == '1')) {
            $errors[$key] = array("LINE Pay を利用できない加盟店です");
        }

        // 口座振替利用
        $key = 'CreditTransferFlg';
        if(!$isNew){
            // 新規でない場合のみ、口座振替が利用する→利用しないに変更されていないかチェックする
            if($data[$key] == '0'){
                $mdlEnterprise = new TableEnterprise($this->app->dbAdapter);
                // 事業者データを取得し、登録されている口座振替利用フラグを確認
                $befVal = $mdlEnterprise->findEnterprise($eid)->current()["CreditTransferFlg"];
                if (($befVal == '1') || ($befVal == '2') || ($befVal == '3')) {
                    $errors[$key] = array("口座振替利用を利用しないに変更することはできません");
                }
            } else {
                $logicSbps = new LogicSbps($this->app->dbAdapter);
                $checkTodo = $logicSbps->checkSettingTodo($data['EnterpriseId']);
                if ($checkTodo['isValid']) {
                    $errors[$key] = array("届いてから払いの設定が行われているため、修正できません。届いてから払いを無効にした後、修正してくだい。");
                }
            }
        }

        // 案内用紙の封入
        $key = 'ClaimPamphletPut';
        if ($data['AppFormIssueCond'] == 2) {
            if (!isset($errors[$key])  && $data[$key] != '0') {
                $errors[$key] = array("申込用紙発行条件が「請求金額0円時」の場合は、「利用しない」を選択してください");
            }
        }
        if ($data['AppFormIssueCond'] == 1) {
            if (!isset($errors[$key])  && $data[$key] != '1') {
                $errors[$key] = array("申込用紙発行条件が「初回注文時」の場合は、「利用する」を選択してください");
            }
        }

        // NTTスマートトレード加盟店
        $key = 'NTTSmartTradeFlg';
        if (!isset($errors[$key]) && ($data['OemId'] != '0' && $data['OemId'] != "") && $data[$key] == '1') {
            $errors[$key] = array("NTTスマートトレード加盟店で登録できません");
        }

        // 加入者固有コード
        $key = 'SubscriberCode';
        //LINE Pay利用（＠ペイメント用）が「利用する」で、加入者固有コードが未入力の場合
        if (!isset($errors[$key])  && $data['IndividualSubscriberCodeFlg'] == '1' &&  !(mb_strlen($data[$key]) > 0)) {
            $errors[$key] = array("'加入者固有コード'を入力してください");
        }
        //加入者固有コードが入力されている場合、入力チェック
        if(mb_strlen($data[$key]) > 0)
        {
            // 加入者固有コードに非数値文字が含まれる場合
            if (! isset ($errors[$key] ) && (! preg_match ("/[0-9]+$/", $data[$key]))) {
                $errors[$key] = array ("'加入者固有コード'は数値文字で入力してください");
            }
            // 加入者固有コードが5桁でない場合
            if (! isset($errors[$key]) && ! (mb_strlen ($data[$key]) == 5)) {
                $errors[$key] = array ("'加入者固有コード'は半角数字5桁で入力してください");
            }
        }

        //OEM先がキャッチボール かつ 無保証案件の請求代行プランが「利用する」の場合
        $key = 'BillingAgentFlg';
        if (!isset($errors[$key]) && $data['BillingAgentFlg'] == '1'&& ($data['OemId'] != '0' && $data['OemId'] != "")) {
            $errors[$key] = array("'無保証案件の請求代行プラン'は利用できません");
        }
        if(!$isNew){
            if (!isset($errors[$key]) && $data['BillingAgentFlg'] == '1') {
                $logicSbps = new LogicSbps($this->app->dbAdapter);
                $checkTodo = $logicSbps->checkSettingTodo($data['EnterpriseId']);
                if ($checkTodo['isValid']) {
                    $errors[$key] = array("届いてから払いの設定が行われているため、修正できません。届いてから払いを無効にした後、修正してくだい。");
                }
            }
        }

        //無保証案件の請求代行プランが「利用しない」 かつ 審査システム連携が「連携しない」の場合
        $key = 'IluCooperationFlg';
        if (!isset($errors[$key]) && $data[$key] == '0' && $data['BillingAgentFlg'] == '0') {
            $errors[$key] = array("'審査システム連携'を連携しないに変更できません");
        }

        // 強制解約日
        $key = 'ForceCancelDatePrintFlg';
        //請求書個別出力が「個別出力しない」で、強制解約日がチェックの場合
        if (!isset($errors[$key])  && $data['ClaimIndividualOutputFlg'] == '0' && ($data[$key] == 1)) {
            $errors[$key] = array("'請求書個別出力しない場合は、強制解約日は印字できません");
        }

        // 強制解約通知請求書
        $key = 'ForceCancelClaimPattern';
        if (!isset($errors[$key])  && $data['ReClaimIssueCtlFlg'] == '1' &&  !(mb_strlen($data[$key]) > 0)) {
            $errors[$key] = array("'再請求書発行制御をチェックした場合は、強制解約通知請求書は必須です");
        }

        // 通帳表示名
        $key = 'MhfCreditTransferDisplayName';
        if (($data['CreditTransferFlg'] == 1) || ($data['CreditTransferFlg'] == 3)) {
            if (!isset($errors[$key]) && !(mb_strlen($data[$key]) > 0)) {
                if ($data['CreditTransferFlg'] == 1) {
                    $errors[$key] = array("'口座振替利用'で利用する(SMBC)を選択した場合は、通帳表示名は必須です");
                } else {
                    $errors[$key] = array("'口座振替利用'で利用する(みずほ)を選択した場合は、通帳表示名は必須です");
                }
            }
            if ($data['CreditTransferFlg'] == 1) {
                if (!isset($errors[$key]) && (mb_strlen($data[$key]) > 10)) {
                    $errors[$key] = array("'通帳表示名'は半角英数カナ10文字で入力してください");
                }
            }
            if ($data['CreditTransferFlg'] == 3) {
                if (!isset($errors[$key]) && (mb_strlen($data[$key]) > 8)) {
                    $errors[$key] = array("'通帳表示名'は半角英数カナ8文字で入力してください");
                }
            }
            if (!isset($errors[$key]) && !(mb_ereg("^[0-9A-Zｱ-ﾜﾝﾞﾟ .()-]+$", $data[$key]))) {
                $errors[$key] = array("'通帳表示名'は半角英数カナ文字で入力してください");
            }
        }

        // ClaimEntCustIdDisplayName: 請求書の加盟店顧客番号の印字名
        $key = 'ClaimEntCustIdDisplayName';
        if (! isset($errors[$key])) {
            $work = mb_strimwidth($data[$key],0,20);
            if ($work != $data[$key]) {
                $errors[$key] = array ("'請求書の加盟店顧客番号の印字名'は半角20文字以内または全角10文字以内で入力してください");
            }
        }

        // 編集モード時
        if(!$data["isNew"]){
            // バッチ排他制御
            $mdlbl = new TableBatchLock (Application::getInstance()->dbAdapter);
            $BatchLock = $mdlbl->findBatchId($this::EXECUTE_BATCH_ID)['BatchLock'];

            $key = 'BatchLock';
            if (!isset($errors[$key]) && ($BatchLock > 0)) {
                $errors[$key] = array("現在、事業者登録処理を行うことができません。しばらくたってから再度実行をお願い致します。");
            }
        }
        return $errors;
	}

	/**
     * 会計用項目_入力検証処理
     */
    protected function validateForAt($data = array()) {
        $errors = array();

        // IncludeMonthlyFee: 同梱月額固定費
        $key = 'IncludeMonthlyFee';
        if (!isset($errors[$key]) && (isset($data[$key]) && (strlen($data[$key]) > 0) && !(is_numeric($data[$key])))) {
            $errors[$key] = array("'同梱月額固定費'の指定が不正です");
        }

        // N_IncludeMonthlyFee: 次回請求同梱月額固定費
        $key = 'N_IncludeMonthlyFee';
        if (!isset($errors[$key]) && (isset($data[$key]) && (strlen($data[$key]) > 0) && !(is_numeric($data[$key])))) {
            $errors[$key] = array("'次回請求同梱月額固定費'の指定が不正です");
        }

        // ApiMonthlyFee: API月額固定費
        $key = 'ApiMonthlyFee';
        if (!isset($errors[$key]) && (isset($data[$key]) && (strlen($data[$key]) > 0) && !(is_numeric($data[$key])))) {
            $errors[$key] = array("'API月額固定費'の指定が不正です");
        }

        // N_ApiMonthlyFee: 次回請求API月額固定費
        $key = 'N_ApiMonthlyFee';
        if (!isset($errors[$key]) && (isset($data[$key]) && (strlen($data[$key]) > 0) && !(is_numeric($data[$key])))) {
            $errors[$key] = array("'次回請求API月額固定費'の指定が不正です");
        }

        // CreditNoticeMonthlyFee: 与信結果通知サービス月額固定費
        $key = 'CreditNoticeMonthlyFee';
        if (!isset($errors[$key]) && (isset($data[$key]) && (strlen($data[$key]) > 0) && !(is_numeric($data[$key])))) {
            $errors[$key] = array("'与信結果通知サービス月額固定費'の指定が不正です");
        }

        // N_CreditNoticeMonthlyFee: 次回請求与信結果通知サービス月額固定費
        $key = 'N_CreditNoticeMonthlyFee';
        if (!isset($errors[$key]) && (isset($data[$key]) && (strlen($data[$key]) > 0) && !(is_numeric($data[$key])))) {
            $errors[$key] = array("'次回請求与信結果通知サービス月額固定費'の指定が不正です");
        }

        // NCreditNoticeMonthlyFee: 次回請求与信結果通知サービス月額固定費
        $key = 'NCreditNoticeMonthlyFee';
        if (!isset($errors[$key]) && (isset($data[$key]) && (strlen($data[$key]) > 0) && !(is_numeric($data[$key])))) {
            $errors[$key] = array("'次回請求与信結果通知サービス月額固定費'の指定が不正です");
        }

        // N_NCreditNoticeMonthlyFee: 次回請求次回請求与信結果通知サービス月額固定費
        $key = 'N_NCreditNoticeMonthlyFee';
        if (!isset($errors[$key]) && (isset($data[$key]) && (strlen($data[$key]) > 0) && !(is_numeric($data[$key])))) {
            $errors[$key] = array("'次回請求次回請求与信結果通知サービス月額固定費'の指定が不正です");
        }

        // ReserveMonthlyFee: 月額固定費予備
        $key = 'ReserveMonthlyFee';
        if (!isset($errors[$key]) && (isset($data[$key]) && (strlen($data[$key]) > 0) && !(is_numeric($data[$key])))) {
            $errors[$key] = array("'月額固定費予備'の指定が不正です");
        }

        // N_ReserveMonthlyFee: 次回請求月額固定費予備
        $key = 'N_ReserveMonthlyFee';
        if (!isset($errors[$key]) && (isset($data[$key]) && (strlen($data[$key]) > 0) && !(is_numeric($data[$key])))) {
            $errors[$key] = array("'次回請求月額固定費予備'の指定が不正です");
        }

        // Oem関連バリデーション
        if($data['OemId'] != 0){
            // OemIncludeMonthlyFee: OEM同梱月額固定費
            $key = 'OemIncludeMonthlyFee';
            if (!isset($errors[$key]) && (isset($data[$key]) && (strlen($data[$key]) > 0) && !(is_numeric($data[$key])))) {
                $errors[$key] = array("'OEM同梱月額固定費'の指定が不正です");
            }

            // N_OemIncludeMonthlyFee: 次回請求OEM同梱月額固定費
            $key = 'N_OemIncludeMonthlyFee';
            if (!isset($errors[$key]) && (isset($data[$key]) && (strlen($data[$key]) > 0) && !(is_numeric($data[$key])))) {
                $errors[$key] = array("'次回請求OEM同梱月額固定費'の指定が不正です");
            }

            // OemApiMonthlyFee: OEMAPI月額固定費
            $key = 'OemApiMonthlyFee';
            if (!isset($errors[$key]) && (isset($data[$key]) && (strlen($data[$key]) > 0) && !(is_numeric($data[$key])))) {
                $errors[$key] = array("'OEMAPI月額固定費'の指定が不正です");
            }

            // N_OemApiMonthlyFee: 次回請求OEMAPI月額固定費
            $key = 'N_OemApiMonthlyFee';
            if (!isset($errors[$key]) && (isset($data[$key]) && (strlen($data[$key]) > 0) && !(is_numeric($data[$key])))) {
                $errors[$key] = array("'次回請求OEMAPI月額固定費'の指定が不正です");
            }

            // OemCreditNoticeMonthlyFee: OEM与信結果通知サービス月額固定費
            $key = 'OemCreditNoticeMonthlyFee';
            if (!isset($errors[$key]) && (isset($data[$key]) && (strlen($data[$key]) > 0) && !(is_numeric($data[$key])))) {
                $errors[$key] = array("'OEM与信結果通知サービス月額固定費'の指定が不正です");
            }

            // N_OemCreditNoticeMonthlyFee: 次回請求OEM与信結果通知サービス月額固定費
            $key = 'N_OemCreditNoticeMonthlyFee';
            if (!isset($errors[$key]) && (isset($data[$key]) && (strlen($data[$key]) > 0) && !(is_numeric($data[$key])))) {
                $errors[$key] = array("'次回請求OEM与信結果通知サービス月額固定費'の指定が不正です");
            }

            // OemNCreditNoticeMonthlyFee: OEM次回請求与信結果通知サービス月額固定費
            $key = 'OemNCreditNoticeMonthlyFee';
            if (!isset($errors[$key]) && (isset($data[$key]) && (strlen($data[$key]) > 0) && !(is_numeric($data[$key])))) {
                $errors[$key] = array("'OEM次回請求与信結果通知サービス月額固定費'の指定が不正です");
            }

            // N_OemNCreditNoticeMonthlyFee: 次回請求OEM次回請求与信結果通知サービス月額固定費
            $key = 'N_OemNCreditNoticeMonthlyFee';
            if (!isset($errors[$key]) && (isset($data[$key]) && (strlen($data[$key]) > 0) && !(is_numeric($data[$key])))) {
                $errors[$key] = array("'次回請求OEM次回請求与信結果通知サービス月額固定費'の指定が不正です");
            }

            // OemReserveMonthlyFee: OEM月額固定費予備
            $key = 'OemReserveMonthlyFee';
            if (!isset($errors[$key]) && (isset($data[$key]) && (strlen($data[$key]) > 0) && !(is_numeric($data[$key])))) {
                $errors[$key] = array("'OEM月額固定費予備'の指定が不正です");
            }

            // N_OemReserveMonthlyFee: 次回請求OEM月額固定費予備
            $key = 'N_OemReserveMonthlyFee';
            if (!isset($errors[$key]) && (isset($data[$key]) && (strlen($data[$key]) > 0) && !(is_numeric($data[$key])))) {
                $errors[$key] = array("'次回請求OEM月額固定費予備'の指定が不正です");
            }
        }

        return $errors;
    }

	/**
	 * 詳細画面更新用入力検証処理
	 *
	 * @access protected
	 * @param array $data
	 * @return array
	 */
	protected function validateForUp($data = array()) {

        $errors = array();

        // DocCollect: 書類回収
        $key = 'DocCollect';
        if (!isset($errors[$key]) && (mb_strlen($data[$key]) > 0) && !(is_numeric($data[$key]))) {
            $errors[$key] = array("'書類回収'の指定が不正です");
        }

        // ExaminationResult: 審査結果
        $key = 'ExaminationResult';
        if (!isset($errors[$key]) && (mb_strlen($data[$key]) > 0) && !(is_numeric($data[$key]))) {
            $errors[$key] = array("'審査結果'の指定が不正です");
        }

        // PublishingConfirmDate: 掲載確認日
        $key = 'PublishingConfirmDate';
        if (!isset($errors[$key]) && (mb_strlen($data[$key]) > 0) && !IsValidFormatDate($data[$key])) {
            $errors[$key] = array("'掲載確認日'の形式が不正です");
        }

        // Special01Flg: 特殊店舗
        $key = 'Special01Flg';
        if (!isset($errors[$key]) && (mb_strlen($data[$key]) > 0) && !(is_numeric($data[$key]))) {
            $errors[$key] = array("'特殊店舗'の指定が不正です");
        }

        // Notice: 立替用通信欄
        $key = 'Notice';
        if (!isset($errors[$key]) && !(mb_strlen($data[$key]) <= 4000)) {
            $errors[$key] = array("'立替用通信欄'は4000文字以内で入力してください");
        }

        return $errors;
	}

	/**
	 * 事業者向け請求書同梱ツールに関する設定を取得する
	 *
	 * @access protected
	 * @return array 同梱ツールに関する設定を格納した連想配列
	 */
	protected function getEntSelfBillingSettings() {

        $config = array();
        $default_config = array(
                'enable_settings' => false
        );

        $data = $this->app->config;
        $ent_sbsettings = isset($data['ent_sbsettings']) ? $data['ent_sbsettings'] : array() ;

        return array_merge($default_config, $ent_sbsettings);
	}

    /**
     * 配送方法一覧を返すAjax向けアクション
     */
    public function delimethodlistAction() {

        $params = $this->getParams();

        $oemId = (isset($params['oid'])) ? $params['oid'] : 0;

        $result = array();
        try {
            $logic = new \models\Logic\LogicShipping($this->app->dbAdapter, 0/* 更新必要性なし故ゼロ */);
            // OemIdをキーにしたKey-Valueデータはフロントでデコードした場合に順序が保証されなくなるので
            // リストとして構築する
            foreach($logic->getDeliMethodListByOemId($oemId) as $id => $label) {
                $result[] = array(
                        'id' => $id,
                        'label' => $label
                );
            }
        } catch(\Exception $err) {
        }

        echo \Zend\Json\Json::encode($result);
        return $this->response;
    }

    /**
     * 新しいランダムパスワードを生成する
     *
     * @access protected
     * @param null | string $loginId ログインID
     * @return string
     */
    protected function generateNewPassword($loginId = null)
    {
        $validator = \models\Logic\AccountValidity\LogicAccountValidityPasswordValidator::getDefaultValidator();
        $i = 0;
        while (true) {
            $this->app->logger->debug(sprintf('[EnterpriseController::generateNewPassword] generating new password for %s (total %d times)', $loginId, ++$i));
            $newPassword = BaseGeneralUtils::MakePassword(8);			// パスワードをランダム設定
            if ($validator->isValid($newPassword, $loginId))
            {
                return $newPassword;
            }
        }
    }

    /**
     * キャンペーン一覧の再描画
     */
    public function campaignlistAction() {
        $req = $this->getParams();

        $eid = (isset($req['eid'])) ? $req['eid'] : -1;

        $campaign = array();
        // 加盟店キャンペン情報取得
        // 加盟店IDに紐づくキャンペーン情報を期間でグループ化して取得する
        $sql = <<<EOQ
            SELECT  MIN(ec.Seq) AS Seq
                ,   MIN(ec.SiteId) AS SiteId
                ,   MAX(pp.PricePlanName) AS PricePlanName
                ,   ec.DateFrom
                ,   ec.DateTo
                ,   MAX(ec.MonthlyFee) AS MonthlyFee
            FROM    T_EnterpriseCampaign ec
                    INNER JOIN M_PricePlan pp ON (pp.PricePlanId = ec.AppPlan)
            WHERE   ec.EnterpriseId = :EnterpriseId
            GROUP BY
                    ec.DateFrom
                ,   ec.DateTo
            ;
EOQ;
        $ri = $this->app->dbAdapter->query($sql)->execute(array( 'EnterpriseId' => $eid ));
        $campaign = ResultInterfaceToArray($ri);

        $this->view->assign('campaign', $campaign);
        $this->view->assign('eid', $eid);

        return $this->view;

    }

    /**
     * サイト別不払率一覧
     */
    public function sitenplistAction() {
        $params = $this->getParams();

        $sitenplists = array();

        // 加盟店サイトIDの列挙とアサイン用変数の初期化
        $sql = " SELECT SiteId, SiteNameKj, SettlementFeeRate FROM T_Site WHERE EnterpriseId = :EnterpriseId ORDER BY SiteId ";
        $ri = $this->app->dbAdapter->query($sql)->execute(array( 'EnterpriseId' => $params['eid'] ));
        foreach ($ri as $row) {
            $val = array('cnt' => 0, 'cntall' => 0, 'sum' => 0, 'sumall' => 0, 'settlementfeesum' => 0);
            $sitenplist = array('siteid' => $row['SiteId'], 'sitenamekj' => $row['SiteNameKj'], 'settlementfeerate' => $row['SettlementFeeRate'], 'profitrate' => 0, 'profitandloss' => 0);
            for ($i=0; $i<6; $i++) {
                $sitenplist['type' . ($i + 1)] = $val;
            }
            $sitenplists[] = $sitenplist;
        }

        // サイト別集計取得＆アサイン(上書き)
        $mdlst = new \models\Table\TableSiteTotal($this->app->dbAdapter);

        // count関数対策
        $sitenpListsCount = 0;
        if (!empty($sitenplists)) {
            $sitenpListsCount = count($sitenplists);
        }
        for ($i=0; $i<$sitenpListsCount; $i++) {
            $row_st = $mdlst->find($sitenplists[$i]['siteid'])->current();
            if (!$row_st) {
                continue;
            }

            // 値取得時
            $aryNpTotals = Json::decode($row_st['NpTotal'], Json::TYPE_ARRAY);
            // count関数対策
            $aryNpTotalsCount = 0;
            if (!empty($aryNpTotals)) {
                $aryNpTotalsCount = count($aryNpTotals);
            }
            for ($j=0; $j<$aryNpTotalsCount; $j++) {
                $row = $aryNpTotals[$j];

                if ($row['type'] == 'Summary') {
                    $sitenplists[$i]['settlementfeerate'] = $row['SettlementFeeRate'];
                    $sitenplists[$i]['profitrate'] = $row['ProfitRate'];
                    $sitenplists[$i]['profitandloss'] = $row['ProfitAndLoss'];
                }
                else {
                    $sitenplists[$i]['type' . $row['type']]['cnt'] = $row['cnt'];
                    $sitenplists[$i]['type' . $row['type']]['cntall'] = $row['cntall'];
                    $sitenplists[$i]['type' . $row['type']]['sum'] = $row['sum'];
                    $sitenplists[$i]['type' . $row['type']]['sumall'] = $row['sumall'];
                    $sitenplists[$i]['type' . $row['type']]['settlementfeesum'] = $row['settlementfeesum'];
                }
            }
        }
        $this->view->assign('sitenplists', $sitenplists);

        // 不払い率背景色しきい値(％)
        $npRateColorThreshold = $this->app->dbAdapter->query(" SELECT PropValue FROM T_SystemProperty WHERE Module = '[DEFAULT]' AND Category = 'systeminfo' AND Name = 'NpRateColorThreshold' ")->execute(null)->current()['PropValue'];
        $this->view->assign('npRateColorThreshold', $npRateColorThreshold);

        // 加盟店情報
        $this->view->assign('enterpriseid',$params['eid']);
        $rowent = $this->app->dbAdapter->query(" SELECT EnterpriseNameKj FROM T_Enterprise WHERE EnterpriseId = :EnterpriseId ")->execute(array( 'EnterpriseId' => $params['eid'] ))->current();
        $this->view->assign('enterprisenamekj',($rowent) ? $rowent['EnterpriseNameKj'] : '');

        // サイト指定
        $this->view->assign('emphasissiteid', (isset($params['sid'])) ? $params['sid'] : -1);

        return $this->view;
    }
}
