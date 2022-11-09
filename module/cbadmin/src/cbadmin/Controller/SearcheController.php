<?php
namespace cbadmin\Controller;

use Coral\Base\BaseHtmlUtils;
use Coral\Base\BaseUtility;
use Coral\Base\BaseGeneralUtils;
use Coral\Coral\Controller\CoralControllerAction;
use Coral\Coral\CoralCodeMaster;
use Coral\Coral\CoralValidate;
use cbadmin\Application;
use models\Table\TableOem;
use models\Table\TableEnterprise;
use models\Logic\LogicTemplate;

class SearcheController extends CoralControllerAction
{
	protected $_componentRoot = './application/views/components';

	const SESS_SEARCH_INFO = 'SESS_SEARCH_INFO';

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
            ->addStyleSheet( '../css/base.ui.tableex.css' )
            ->addJavaScript( '../js/json+.js' )
            ->addJavaScript( '../js/prototype.js' )
            ->addJavaScript( '../js/corelib.js' )
            ->addJavaScript( '../js/base.ui.js')
            ->addJavaScript( '../js/base.ui.tableex.js' )
            ->addJavaScript( '../js/base.ui.datepicker.js');

        $this->setPageTitle("後払い.com - 事業者検索");

        // コードマスターから事業者情報向けのマスター連想配列を作成し、ビューへアサインしておく
        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
        $masters = array(
                'AutoCreditJudgeMode' => $codeMaster->getAutoCreditJudgeModeMaster(),
                'CjMailMode' => $codeMaster->getCjMailModeMaster(),
                'CombinedClaimMode' => $codeMaster->getCombinedClaimMode(),
        );

        $this->view->assign('master_map', $masters);
	}

// Del By Takemasa(NDC) 20150227 Stt マジックメソッド廃止
// 	/**
// 	 * 未定義のアクションがコールされた
// 	 */
// 	public function __call($method, $args)
// 	{
// 	    // 無条件にlistへinvoke
// 		$this->_forward('form');
// 	}
// Del By Takemasa(NDC) 20150227 End マジックメソッド廃止

	/**
	 * 検索フォームの表示
	 */
	public function formAction()
	{
        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
        $mdlOem = new TableOem($this->app->dbAdapter);

        $oem_list = $mdlOem->getOemIdList();

        //OEM先リストSELECTタグ
        $this->view->assign('oemTag',BaseHtmlUtils::SelectTag("Oem",$oem_list));

        // 推定月商のSELECTタグ
        $this->view->assign('preSalesTag',BaseHtmlUtils::SelectTag("PreSales",$codeMaster->getPreSalesMaster()));

        // 業種のSELECTタグ
        $this->view->assign('industryTag',BaseHtmlUtils::SelectTag("Industry",$codeMaster->getIndustryMaster()));

        // 利用プランのSELECTタグ
        $this->view->assign('planTag',BaseHtmlUtils::SelectTag("Plan",$codeMaster->getPlanMaster()));

        // 締め日パターンのSELECTタグ
        $this->view->assign('fixPatternTag',BaseHtmlUtils::SelectTag("FixPattern",$codeMaster->getFixPatternMaster()));

        // 振込手数料のSELECTタグ
        $this->view->assign('tcClassTag',BaseHtmlUtils::SelectTag("TcClass",$codeMaster->getTcClassMaster()));

        // 書類審査
        $this->view->assign('docCollectSelectTag',BaseHtmlUtils::SelectTag('DocCollect',$codeMaster->getDocCollectMaster()));

        // 審査結果
        $this->view->assign('examinationResultSelectTag',BaseHtmlUtils::SelectTag('ExaminationResult',$codeMaster->getExaminationResultMaster(true)));

        // 形態
        $this->view->assign('siteFormTag',BaseHtmlUtils::SelectTag('SiteForm',$codeMaster->getSiteFormMaster()));

        // 与信自動判定
        $this->view->assign('autoCreditJudgeModeTag',BaseHtmlUtils::SelectTag('AutoCreditJudgeMode',$codeMaster->getAutoCreditJudgeModeMaster()));

        // 有効設定
        $validFlgTag = array(
            '-1' => '-----',
            '1' => '有効',
            '0' => '無効',
        );
        $this->view->assign('validFlgTag',BaseHtmlUtils::SelectTag('ValidFlg',$validFlgTag));

        return $this->view;
	}

	/**
	 * 検索実行
	 */
	public function searchAction()
	{
        $params = $this->getParams();

        $sql  = " SELECT DISTINCT ";
        $sql .= "     ent.EnterpriseId ";
        $sql .= " ,   ent.LoginId ";
        $sql .= " ,   ent.EnterpriseNameKj ";
        $sql .= " ,   ent.Plan ";
        $sql .= " ,   (SELECT PricePlanName FROM M_PricePlan WHERE ValidFlg = 1 AND PricePlanId = ent.Plan) AS PlanNm ";
        $sql .= " ,   (SELECT KeyContent FROM M_Code WHERE ValidFlg = 1 AND CodeId = 2 AND KeyCode = pay.FixPattern) AS FixPatternNm ";
        $sql .= " ,   ent.CpNameKj ";
        $sql .= " ,   ent.CpNameKn ";
        $sql .= " ,   ent.DivisionName ";
        $sql .= " ,   ent.ContactPhoneNumber ";
        $sql .= " ,   '' AS OptionField ";
        $sql .= " ,   ent.Note ";
        $sql .= " ,   ent.OemId ";
        $sql .= " ,   CASE WHEN TO_DAYS(DATE_FORMAT(NOW(),'%Y-%m-%d')) - TO_DAYS(DATE_FORMAT(IFNULL((SELECT MIN(ReceiptOrderDate) FROM T_Order WHERE EnterpriseId = ent.EnterpriseId), '1970-01-01'),'%Y-%m-%d')) <= 30 THEN 1 ";
        $sql .= "     ELSE 0 ";
        $sql .= "     END AS IsLatest1stOrder ";
        $sql .= " FROM ";
        $sql .= "     T_Enterprise ent ";
        $sql .= "         LEFT OUTER JOIN ";
        $sql .= "     M_PayingCycle pay ON ent.PayingCycleId = pay.PayingCycleId ";
        $sql .= "         LEFT OUTER JOIN ";
        $sql .= "     T_Site sit ON ent.EnterpriseId = sit.EnterpriseId ";
        $sql .= "         LEFT OUTER JOIN ";
        $sql .= "     T_Oem AS oem ON ent.OemId = oem.OemId ";
        $sql .= "         LEFT OUTER JOIN ";
        $sql .= "     T_PayingControl AS pc ON ent.EnterpriseId = pc.EnterpriseId ";
        $sql .= " WHERE ";
        $sql .= "     1 = 1 ";

        $sqlSel = $sql;
        $sql = "";
        // WHERE句の追加

        //OEM検索
        if($params['Oem'] != 0) {
            $sql .= (" AND ent.OemId = " . $params['Oem']);
        }

        // 事業者ID（LoginId）
        if ($params['LoginId'] != '') {
            $sql .= " AND ent.LoginId like '%" . BaseUtility::escapeWildcard($params['LoginId']) . "' ";
        }

        // 事業者名
        if ($params['EnterpriseNameKj'] != '') {
            $sql .= " AND ent.EnterpriseNameKj like '%" . BaseUtility::escapeWildcard($params['EnterpriseNameKj']) . "%' ";
        }

        // 事業者名カナ
        if ($params['EnterpriseNameKn'] != '') {
            $sql .= " AND ent.EnterpriseNameKn like '%" . BaseUtility::escapeWildcard($params['EnterpriseNameKn']) . "%' ";
        }

        // 所在地
        if ($params['UnitingAddress'] != '') {
            $sql .= " AND ent.UnitingAddress like '%" . BaseUtility::escapeWildcard($params['UnitingAddress']) . "%' ";
        }

        // 代表者氏名
        if ($params['RepNameKj'] != '') {
            $sql .= " AND ent.RepNameKj like '%" . BaseUtility::escapeWildcard($params['RepNameKj']) . "%' ";
        }

        // 代表者氏名カナ
        if ($params['RepNameKn'] != '') {
            $sql .= " AND ent.RepNameKn like '%" . BaseUtility::escapeWildcard($params['RepNameKn']) . "%' ";
        }

        // 推定月商
        if ($params['PreSales'] != '0' && $params['PreSales'] != '' && is_numeric($params['PreSales'])) {
            $sql .= (" AND ent.PreSales = " . $params['PreSales']);
        }

        // 業種
        if ($params['Industry'] != '0' && $params['Industry'] != '' && is_numeric($params['Industry'])) {
            $sql .= (" AND ent.Industry = " . $params['Industry']);
        }

        // 自動与信
        if($params['AutoCreditJudgeMode'] != '' && is_numeric($params['AutoCreditJudgeMode'])){
            if ($params['AutoCreditJudgeMode'] == '0' ) {
                $sql .= " AND ent.AutoCreditJudgeMode = 0 ";
            }
            else if ($params['AutoCreditJudgeMode'] == '-1') { ; }
            else {
                $sql .= (" AND ent.AutoCreditJudgeMode = " . $params['AutoCreditJudgeMode']);
            }
        }

        // 担当者氏名
        if ($params['CpNameKj'] != '') {
            $sql .= " AND ent.CpNameKj like '%" . BaseUtility::escapeWildcard($params['CpNameKj']) . "%' ";
        }

        // 担当者氏名カナ
        if ($params['CpNameKn'] != '') {
            $sql .= " AND ent.CpNameKn like '%" . BaseUtility::escapeWildcard($params['CpNameKn']) . "%' ";
        }

        // 部署名
        if ($params['DivisionName'] != '') {
            $sql .= " AND ent.DivisionName like '%" . BaseUtility::escapeWildcard($params['DivisionName']) . "%' ";
        }

        // 電話番号
        if ($params['ContactPhoneNumber'] != '') {
            $sql .= " AND ent.ContactPhoneNumber like '%" . BaseUtility::escapeWildcard($params['ContactPhoneNumber']) . "%' ";
        }

        // メールアドレス
        if ($params['MailAddress'] != '') {
            $sql .= " AND ent.MailAddress like '%" . BaseUtility::escapeWildcard($params['MailAddress']) . "%' ";
        }

        // サイト名
        if ($params['SiteNameKj'] != '') {
            $sql .= " AND sit.SiteNameKj like '%" . BaseUtility::escapeWildcard($params['SiteNameKj']) . "%' ";
        }

        // URL
        if ($params['Url'] != '') {
            $sql .= " AND sit.Url like '%" . BaseUtility::escapeWildcard($params['Url']) . "%' ";
        }

        // サイト形態
        if ($params['SiteForm'] != '0' && $params['SiteForm'] != '' && is_numeric($params['SiteForm'])) {
            $sql .= (" AND sit.SiteForm = " . $params['SiteForm']);
        }

        // お申込み日
        $wApplicationDate = BaseGeneralUtils::makeWhereDate('ent.ApplicationDate', $params['ApplicationDateF'], $params['ApplicationDateT']);
        if ($wApplicationDate != '') {
            $sql .= (" AND " . $wApplicationDate);
        }

        // 書類審査
        if ($params['DocCollect'] == '0') {
            $sql .= " AND (ent.DocCollect IS NULL OR ent.DocCollect = 0) ";
        }
        else if ($params['DocCollect'] == '1') {
            $sql .= " AND ent.DocCollect = 1 ";
        }

        // 審査結果
        if($params['ExaminationResult'] != '' && is_numeric($params['ExaminationResult'])){
            if ($params['ExaminationResult'] == '0') {
                $sql .= " AND (ent.ExaminationResult IS NULL OR ent.ExaminationResult = 0) ";
            }
            else if ($params['ExaminationResult'] == '-1') { ; }
            else {
                $sql .= (" AND ent.ExaminationResult = " . $params['ExaminationResult']);
            }
        }

        // サービス開始日
        $wServiceInDate = BaseGeneralUtils::makeWhereDate('ent.ServiceInDate', $params['ServiceInDateF'], $params['ServiceInDateT']);
        if ($wServiceInDate != '') {
            $sql .= (" AND " . $wServiceInDate);
        }

        // 掲載確認日
        $wPublishingConfirmDate = BaseGeneralUtils::makeWhereDate('ent.PublishingConfirmDate', $params['PublishingConfirmDateF'], $params['PublishingConfirmDateT']);
        if ($wPublishingConfirmDate != '') {
            $sql .= (" AND " . $wPublishingConfirmDate);
        }

        // ご利用プラン
        if ($params['Plan'] != '0' && $params['Plan'] != '' && is_numeric($params['Plan'])) {
            $sql .= (" AND ent.Plan = " . $params['Plan']);
        }

        // 月額固定費
        $wMonthlyFee = BaseGeneralUtils::makeWhereInt('ent.MonthlyFee', $params['MonthlyFeeF'], $params['MonthlyFeeT']);
        if ($wMonthlyFee != '') {
            $sql .= (" AND " . $wMonthlyFee);
        }

        // 決済手数料率
        if (CoralValidate::isFloat($params['SettlementFeeRateF']) && CoralValidate::isFloat($params['SettlementFeeRateT'])) {
            $wSettlementFeeRate = BaseGeneralUtils::makeWhereFloat(
                'SettlementFeeRate',
                $params['SettlementFeeRateF'],
                $params['SettlementFeeRateT']
            );
        }
        else if (CoralValidate::isFloat($params['SettlementFeeRateF'])) {
            $wSettlementFeeRate = BaseGeneralUtils::makeWhereFloat(
                'SettlementFeeRate',
                $params['SettlementFeeRateF'],
                ''
            );
        }
        else if (CoralValidate::isFloat($params['SettlementFeeRateT'])) {
            $wSettlementFeeRate = BaseGeneralUtils::makeWhereFloat(
                'SettlementFeeRate',
                '',
                $params['SettlementFeeRateT']
            );
        }

        if ($wSettlementFeeRate != '') {
            $sql .= (" AND EXISTS (SELECT * FROM T_Site WHERE EnterpriseId = ent.EnterpriseId AND " . $wSettlementFeeRate . ") ");
        }

        // 請求手数料
        $wClaimFeeBS = BaseGeneralUtils::makeWhereInt('ClaimFeeBS', $params['ClaimFeeF'], $params['ClaimFeeT']);
        $wClaimFeeDK = BaseGeneralUtils::makeWhereInt('ClaimFeeDK', $params['ClaimFeeF'], $params['ClaimFeeT']);
        if ($wClaimFeeBS != '' && $wClaimFeeDK != '') {
            $sql .= (" AND EXISTS (SELECT * FROM T_Site WHERE EnterpriseId = ent.EnterpriseId AND (" . $wClaimFeeBS . " OR " . $wClaimFeeDK . ") ) ");
        }

        // OEM月額固定費
        $wOemMonthlyFee = BaseGeneralUtils::makeWhereInt('ent.OemMonthlyFee', $params['OemMonthlyFeeF'], $params['OemMonthlyFeeT']);
        if ($wOemMonthlyFee != '') {
            $sql .= (" AND " . $wOemMonthlyFee);
        }

        // OEM決済手数料率
        if (CoralValidate::isFloat($params['OemSettlementFeeRateF']) && CoralValidate::isFloat($params['OemSettlementFeeRateT'])) {
            $wOemSettlementFeeRate = BaseGeneralUtils::makeWhereFloat(
                'OemSettlementFeeRate',
                $params['OemSettlementFeeRateF'],
                $params['OemSettlementFeeRateT']
            );
        }
        else if (CoralValidate::isFloat($params['OemSettlementFeeRateF'])) {
            $wOemSettlementFeeRate = BaseGeneralUtils::makeWhereFloat(
                'OemSettlementFeeRate',
                $params['OemSettlementFeeRateF'],
                ''
            );
        }
        else if (CoralValidate::isFloat($params['OemSettlementFeeRateT'])) {
            $wOemSettlementFeeRate = BaseGeneralUtils::makeWhereFloat(
                'OemSettlementFeeRate',
                '',
                $params['OemSettlementFeeRateT']
            );
        }

        if ($wOemSettlementFeeRate != '') {
            $sql .= (" AND EXISTS (SELECT * FROM T_Site WHERE EnterpriseId = ent.EnterpriseId AND " . $wOemSettlementFeeRate . ") ");
        }

        // OEM請求手数料
        $wOemClaimFee = BaseGeneralUtils::makeWhereInt('OemClaimFee', $params['OemClaimFeeF'], $params['OemClaimFeeT']);
        if ($wOemClaimFee != '') {
            $sql .= (" AND EXISTS (SELECT * FROM T_Site WHERE EnterpriseId = ent.EnterpriseId AND " . $wOemClaimFee . ") ");
        }

        // 再請求手数料
        $wReClaimFee = BaseGeneralUtils::makeWhereInt('ReClaimFee', $params['ReClaimFeeF'], $params['ReClaimFeeT']);
        if ($wReClaimFee != '') {
            $sql .= (" AND EXISTS (SELECT * FROM T_Site WHERE EnterpriseId = ent.EnterpriseId AND " . $wReClaimFee . ") ");
        }

        // 決済上限額
        $wSettlementAmountLimit = BaseGeneralUtils::makeWhereInt('SettlementAmountLimit', $params['SettlementAmountLimitF'], $params['SettlementAmountLimitT']);
        if ($wSettlementAmountLimit != '') {
            $sql .= (" AND EXISTS (SELECT * FROM T_Site WHERE EnterpriseId = ent.EnterpriseId AND " . $wSettlementAmountLimit . ") ");
        }

        // 締め日パターン
        if ($params['FixPattern'] != '0' && $params['FixPattern'] != '' && is_numeric($params['FixPattern'])) {
            $sql .= (" AND pay.FixPattern = " . $params['FixPattern']);
        }

        // 営業担当
        if ($params['Salesman'] != '') {
            $sql .= " AND ent.Salesman like '%" . BaseUtility::escapeWildcard($params['Salesman']) . "%' ";
        }

        // 金融機関名
        if ($params['FfName'] != '') {
            $sql .= " AND ent.FfName like '%" . BaseUtility::escapeWildcard($params['FfName']) . "%' ";
        }

        // 口座番号
        if ($params['FfAccountNumber'] != '') {
            $sql .= (" AND ent.FfAccountNumber = " . $this->app->dbAdapter->getPlatform()->quoteValue($params['FfAccountNumber']));
        }

        // 振込手数料
        if ($params['TcClass'] != '0' && $params['TcClass'] != '' && is_numeric($params['TcClass'])) {
            $sql .= (" AND ent.TcClass = " . $params['TcClass']);
        }

        // 備考
        if ($params['Note'] != '') {
            $sql .= " AND ent.Note like '%" . BaseUtility::escapeWildcard($params['Note']) . "%' ";
        }

        // 簡易備考
        if ($params['Memo'] != '') {
            $sql .= " AND ent.Memo like '%" . BaseUtility::escapeWildcard($params['Memo']) . "%' ";
        }

        // 登録日
        $wRegistDate = BaseGeneralUtils::makeWhereDateTime('ent.RegistDate', $params['RegistDateF'], $params['RegistDateT']);
        if ($wRegistDate != '') {
            $sql .= (" AND " . $wRegistDate);
        }

        // 繰り越し残
        if ($params['RemCarryOver'] != '' && is_numeric($params['RemCarryOver'])) {
            $sql .= (" AND pc.PayingControlStatus = 1 ");
            $sql .= (" AND pc.ExecFlg = 10 ");
            $sql .= (" AND pc.DecisionPayment <= ( " . $params['RemCarryOver'] . " * -1 ) ");
        }

        // 有効無効
        if (!isset($params['ValidFlg'])) {
            $sql .= " AND ent.ValidFlg = 1 ";
        }
        else {
            if ($params['ValidFlg'] != -1) {
                $sql .= " AND ent.ValidFlg = " . $params['ValidFlg'];
            }
        }

        // 検索条件保存
        $_SESSION[self::SESS_SEARCH_INFO] = $sql;

        $sql = $sqlSel . $sql;
        $sql .= " ORDER BY ent.EnterpriseId ";

        // クエリー実行
        $ri = $this->app->dbAdapter->query($sql)->execute(null);

        $this->view->assign('list', $ri);

        $enterprises = new TableEnterprise($this->app->dbAdapter);
        $this->view->assign('optionMap', $enterprises->getAllEnterpriseOptionInfo());

        return $this->view;
	}

    /**
     * CSVダウンロード
     */
    function downloadAction()
    {
        // CSVデータ取得
        $sql  = "SELECT DISTINCT ";
        $sql .= "       ent.MailAddress ";
        $sql .= "     , ent.EnterpriseNameKj ";
        $sql .= "     , oem.OemNameKj ";
        $sql .= "     , CONCAT(SUBSTRING(REPLACE(ent.PostalCode, '-', ''), 1, 3), '-', SUBSTRING(REPLACE(ent.PostalCode, '-', ''), 4)) AS PostalCode ";
        $sql .= "     , ent.PrefectureName ";
        $sql .= "     , ent.City ";
        $sql .= "     , ent.Town ";
        $sql .= "     , ent.Building ";
        $sql .= "     , ent.ContactPhoneNumber ";
        $sql .= "     , ent.CpNameKj ";
        $sql .= "     , DATE_FORMAT(ent.ApplicationDate, '%Y/%m/%d') AS ApplicationDate ";
        $sql .= "     , DATE_FORMAT(ent.PublishingConfirmDate, '%Y/%m/%d') AS PublishingConfirmDate ";
        $sql .= "     , DATE_FORMAT(ent.ServiceInDate, '%Y/%m/%d') AS ServiceInDate ";
        $sql .= "     , C1.KeyContent AS DocCollect ";
        $sql .= "     , C2.KeyContent AS ExaminationResult ";
        $sql .= "     , DATE_FORMAT(ent.RegistDate, '%Y/%m/%d') AS RegistDate ";
        $sql .= "     , ent.LoginId ";
        $sql .= "     , ent.EnterpriseNameKn ";
        $sql .= "     , ent.RepNameKj ";
        $sql .= "     , ent.RepNameKn ";
        $sql .= "     , ent.Phone ";
        $sql .= "     , ent.Fax ";
        $sql .= "     , C3.KeyContent AS PreSales ";
        $sql .= "     , C4.KeyContent AS Industry ";
        $sql .= "     , PP.PricePlanName AS Plan ";
        $sql .= "     , ent.MonthlyFee ";
        $sql .= "     , ent.OemMonthlyFee ";
        $sql .= "     , C5.KeyContent AS FixPattern ";
        $sql .= "     , ent.Salesman ";
        $sql .= "     , ent.FfName ";
        $sql .= "     , ent.FfCode ";
        $sql .= "     , ent.FfBranchName ";
        $sql .= "     , ent.FfBranchCode ";
        $sql .= "     , ent.FfAccountNumber ";
        $sql .= "     , C6.KeyContent AS FfAccountClass ";
        $sql .= "     , ent.FfAccountName ";
        $sql .= "     , C7.KeyContent AS TcClass ";
        $sql .= "     , ent.CpNameKn ";
        $sql .= "     , ent.DivisionName ";
        $sql .= "     , ent.ContactFaxNumber ";
        $sql .= "     , REPLACE(REPLACE(ent.Note, CHAR(13), ''), CHAR(10), '') AS Note ";
        $sql .= "     , ent.Notice ";
        $sql .= "     , DATE_FORMAT(ent.B_ChargeFixedDate, '%Y/%m/%d') AS B_ChargeFixedDate ";
        $sql .= "     , DATE_FORMAT(ent.B_ChargeDecisionDate, '%Y/%m/%d') AS B_ChargeDecisionDate ";
        $sql .= "     , DATE_FORMAT(ent.B_ChargeExecDate, '%Y/%m/%d') AS B_ChargeExecDate ";
        $sql .= "     , DATE_FORMAT(ent.N_ChargeFixedDate, '%Y/%m/%d') AS N_ChargeFixedDate ";
        $sql .= "     , DATE_FORMAT(ent.N_ChargeDecisionDate, '%Y/%m/%d') AS N_ChargeDecisionDate ";
        $sql .= "     , DATE_FORMAT(ent.N_ChargeExecDate, '%Y/%m/%d') AS N_ChargeExecDate ";
        $sql .= "     , ent.N_MonthlyFee ";
        $sql .= "     , (CASE ent.ValidFlg WHEN 1 THEN '有効' ELSE '無効' END) AS ValidFlg ";
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
        $sql .= "     , ent.EnterpriseId ";
        $sql .= "     , AG.AgencyNameKj ";
        $sql .= "  FROM T_Enterprise ent  ";
        $sql .= "       INNER JOIN T_EnterpriseTotal ET ON ET.EnterpriseId = ent.EnterpriseId ";
        $sql .= "       LEFT JOIN T_Oem oem ON oem.OemId = ent.OemId ";
        $sql .= "       LEFT JOIN M_Code C1 ON C1.CodeId = 84 AND C1.KeyCode = ent.DocCollect ";
        $sql .= "       LEFT JOIN M_Code C2 ON C2.CodeId = 75 AND C2.KeyCode = ent.ExaminationResult ";
        $sql .= "       LEFT JOIN M_Code C3 ON C3.CodeId = 55 AND C3.KeyCode = ent.PreSales ";
        $sql .= "       LEFT JOIN M_Code C4 ON C4.CodeId = 54 AND C4.KeyCode = ent.Industry ";
        $sql .= "       LEFT JOIN M_PricePlan PP ON PP.PricePlanId = ent.Plan ";
        $sql .= "       LEFT JOIN M_PayingCycle pay ON pay.PayingCycleId = ent.PayingCycleId ";
        $sql .= "       LEFT JOIN M_Code C5 ON C5.CodeId = 2 AND C5.KeyCode = pay.FixPattern ";
        $sql .= "       LEFT JOIN M_Code C6 ON C6.CodeId = 51 AND C6.KeyCode = ent.FfAccountClass ";
        $sql .= "       LEFT JOIN M_Code C7 ON C7.CodeId = 56 AND C7.KeyCode = ent.TcClass ";
        $sql .= "       LEFT JOIN T_Site sit ON sit.EnterpriseId = ent.EnterpriseId ";
        $sql .= "       LEFT JOIN T_PayingControl pc ON pc.EnterpriseId = ent.EnterpriseId ";
        $sql .= "       LEFT JOIN (SELECT AGS.EnterpriseId ";
        $sql .= "                       , MIN(AGS.AgencyId) AS AgencyId ";
        $sql .= "                    FROM M_AgencySite AGS ";
        $sql .= "                         INNER JOIN T_Site S ON S.SiteId = AGS.SiteId ";
        $sql .= "                   WHERE S.ValidFlg = 1 ";
        $sql .= "                   GROUP BY AGS.EnterpriseId ";
        $sql .= "                 ) AGS ON AGS.EnterpriseId = ent.EnterpriseId ";
        $sql .= "       LEFT JOIN M_Agency AG ON AG.AgencyId = AGS.AgencyId ";
        $sql .= " WHERE 1 = 1  ";

        if (isset($_SESSION[self::SESS_SEARCH_INFO]) && !is_null($_SESSION[self::SESS_SEARCH_INFO])) {
            $sql .= $_SESSION[self::SESS_SEARCH_INFO];
        }

        $sql .= " ORDER BY ent.EnterpriseId ";

        $ri = $this->app->dbAdapter->query($sql)->execute(null);
        $datas = ResultInterfaceToArray($ri);

        // count関数対策
        $datasLen = 0;
        if(!empty($datas)) {
            $datasLen = count($datas);
		}

        // 不払い率情報追加
        for ($i=0; $i<$datasLen; $i++) {
            $calcnp2 = new \models\Logic\LogicCalcNp2($this->app->dbAdapter);
            $row = $calcnp2->makeEnterpriseNpList($datas[$i]['EnterpriseId']);
            // 不払率(件数)
            $datas[$i]['NpRateCount1'] = ($row['type1']['cnt'] == 0) ? 0 : sprintf('%.3f', $row['type1']['cnt'] / $row['type1']['cntall'] * 100);
            $datas[$i]['NpRateCount2'] = ($row['type2']['cnt'] == 0) ? 0 : sprintf('%.3f', $row['type2']['cnt'] / $row['type2']['cntall'] * 100);
            $datas[$i]['NpRateCount3'] = ($row['type3']['cnt'] == 0) ? 0 : sprintf('%.3f', $row['type3']['cnt'] / $row['type3']['cntall'] * 100);
            $datas[$i]['NpRateCount4'] = ($row['type4']['cnt'] == 0) ? 0 : sprintf('%.3f', $row['type4']['cnt'] / $row['type4']['cntall'] * 100);
            $datas[$i]['NpRateCount5'] = ($row['type5']['cnt'] == 0) ? 0 : sprintf('%.3f', $row['type5']['cnt'] / $row['type5']['cntall'] * 100);
            $datas[$i]['NpRateCount6'] = ($row['type6']['cnt'] == 0) ? 0 : sprintf('%.3f', $row['type6']['cnt'] / $row['type6']['cntall'] * 100);
            // 不払率(金額)
            $datas[$i]['NpRateMoney1'] = ($row['type1']['sum'] == 0) ? 0 : sprintf('%.3f', $row['type1']['sum'] / $row['type1']['sumall'] * 100);
            $datas[$i]['NpRateMoney2'] = ($row['type2']['sum'] == 0) ? 0 : sprintf('%.3f', $row['type2']['sum'] / $row['type2']['sumall'] * 100);
            $datas[$i]['NpRateMoney3'] = ($row['type3']['sum'] == 0) ? 0 : sprintf('%.3f', $row['type3']['sum'] / $row['type3']['sumall'] * 100);
            $datas[$i]['NpRateMoney4'] = ($row['type4']['sum'] == 0) ? 0 : sprintf('%.3f', $row['type4']['sum'] / $row['type4']['sumall'] * 100);
            $datas[$i]['NpRateMoney5'] = ($row['type5']['sum'] == 0) ? 0 : sprintf('%.3f', $row['type5']['sum'] / $row['type5']['sumall'] * 100);
            $datas[$i]['NpRateMoney6'] = ($row['type6']['sum'] == 0) ? 0 : sprintf('%.3f', $row['type6']['sum'] / $row['type6']['sumall'] * 100);
            // サイト収益(過去３ヶ月)
            $datas[$i]['SiteProfitFeeRate'] = ($row['settlementfeerate'] == '-') ? '' : sprintf('%.5f', $row['settlementfeerate']);
            $datas[$i]['SiteProfitRate'] = ($row['profitrate'] == 0) ? 0 : sprintf('%.3f', $row['profitrate']);
            $datas[$i]['SiteProfitAndLoss'] = $row['profitandloss'];
        }

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
     * 一括登録時のファイルフィールドのname属性
     *
     * @var string
     */
    const UPLOAD_FIELD_NAME = 'Csv_File';

    /**
     * uploadアクション
     *
     */
    public function uploadAction()
    {
        // OEMIDと名前のリスト取得
        $mdlOem = new TableOem($this->app->dbAdapter);
        $oemList = $mdlOem->getOemIdList();

        $oemList[0] = 'キャッチボール';
        $this->view->assign('oemList', $oemList);

        return $this->view;
    }

    /**
     * confirmアクション
     *
     */
    public function confirmAction()
    {
        $params = $this->getParams();

        $errors = array();

        // CSVファイル取り込み
        $csv = $_FILES[ self::UPLOAD_FIELD_NAME ]['tmp_name'];

        // 拡張子チェック
        if( strrchr( $_FILES[ self::UPLOAD_FIELD_NAME ]['name'], '.' ) === '.csv' && $csv != "" ) {
            $templateId = 'CKI15107_2'; // 事業者取込CSV取込
            $templateClass = 0;
            $seq = 0;
            $templatePattern = 0;

            // CSV解析実行
            $logicTemplate = new LogicTemplate( $this->app->dbAdapter );
            $rows = $logicTemplate->convertFiletoArray( $csv, $templateId, $templateClass, $seq, $templatePattern );

            // ロジック解析失敗
            if( $rows == false ) {
                $this->view->assign( 'error', $logicTemplate->getErrorMessage() );
                $this->setTemplate( 'error' );
                return $this->view;
            }

            // 加盟店登録クラス
            $lgc = new \models\Logic\LogicEnterpriseRegister($this->app->dbAdapter);

            // ユーザーIDの取得
            $obj = new \models\Table\TableUser( $this->app->dbAdapter );
            $userId = $obj->getUserId( 0, $this->app->authManagerAdmin->getUserInfo()->OpId );

            // 加盟店登録処理
            $returns = $lgc->register($rows, $params['OemId'], $userId);
            $errors = $returns['error'];
            // エラーがあった場合
            if (( is_array($errors) && count( $errors ) > 0 ) || (!is_array($errors) && $errors != '')) {
                $this->view->assign( 'error', $errors );
                $this->setTemplate( 'error' );
                return $this->view;
            }

            // count関数対策
            if(empty($rows)) {
                $this->view->assign( 'registCount', 0);
            } else {
                $this->view->assign('registCount', count($rows));
            }

            $this->setTemplate( 'completion' );
            return $this->view;
        }
        else
        {
            $this->view->assign( 'error', 'ファイル形式が適切ではありません。<br />CSVファイルを選択してください' );
            $this->setTemplate( 'error' );
            return $this->view;
        }
    }

    /**
     * errorアクション
     *
     */
    public function errorAction() {

        return $this->view;
    }

    /**
     * completionアクション
     *
     */
    public function completionAction() {

        return $this->view;
    }
}

