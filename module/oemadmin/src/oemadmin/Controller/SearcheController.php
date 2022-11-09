<?php

namespace oemadmin\Controller;

use oemadmin\Application;
use Coral\Coral\Controller\CoralControllerAction;
use Coral\Coral\CoralCodeMaster;
use Coral\Base\BaseHtmlUtils;
use Coral\Base\BaseUtility;
use Coral\Base\BaseGeneralUtils;
use Coral\Coral\CoralValidate;
use models\Table\TableEnterprise;
use models\Table\TableUser;

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

		$this->addStyleSheet($this->app->getOemCss())
			->addStyleSheet( '../../css/base.ui.tableex.css' )
			->addJavaScript( '../../js/json+.js' )
			->addJavaScript( '../../js/prototype.js' )
			->addJavaScript( '../../js/corelib.js' )
			->addJavaScript( '../../js/base.ui.js')
			->addJavaScript( '../../js/base.ui.tableex.js' )
			->addJavaScript( '../../js/base.ui.datepicker.js');

		$this->setPageTitle($this->app->getOemServiceName()." - 事業者検索");
	}

// 	/**
// 	 * 未定義のアクションがコールされた
// 	 */
// 	public function __call($method, $args)
// 	{
// 		// 無条件にlistへinvoke
// 		$this->_forward('form');
// 	}

	/**
	 * 検索フォームの表示
	 */
	public function formAction()
	{
		$codeMaster = new CoralCodeMaster($this->app->dbAdapter);

		// 推定月商のSELECTタグ
		$this->view->assign('preSalesTag',
			BaseHtmlUtils::SelectTag("PreSales",
				$codeMaster->getPreSalesMaster()
			)
		);

		// 業種のSELECTタグ
		$this->view->assign('industryTag',
			BaseHtmlUtils::SelectTag("Industry",
				$codeMaster->getIndustryMaster()
			)
		);

		// 利用プランのSELECTタグ
		$this->view->assign('planTag',
			BaseHtmlUtils::SelectTag("Plan",
				$codeMaster->getPlanMaster()
			)
		);

		// 締め日パターンのSELECTタグ
		$this->view->assign('fixPatternTag',
			BaseHtmlUtils::SelectTag("FixPattern",
				$codeMaster->getFixPatternMaster()
			)
		);

		// 振込手数料のSELECTタグ
		$this->view->assign('tcClassTag',
			BaseHtmlUtils::SelectTag("TcClass",
				$codeMaster->getTcClassMaster()
			)
		);

		// 書類審査
		$this->view->assign('docCollectSelectTag',
			BaseHtmlUtils::SelectTag(
				'DocCollect',
				$codeMaster->getDocCollectMaster()
			)
		);

		// 審査結果
		$this->view->assign('examinationResultSelectTag',
			BaseHtmlUtils::SelectTag(
				'ExaminationResult',
				$codeMaster->getExaminationResultMaster(true)
			)
		);

		// 形態
		$this->view->assign('siteFormTag',
			BaseHtmlUtils::SelectTag(
				'SiteForm',
				$codeMaster->getSiteFormMaster()
			)
		);

		// 与信自動判定
		$this->view->assign('autoCreditJudgeModeTag',
			BaseHtmlUtils::SelectTag(
				'AutoCreditJudgeMode',
				array(-1 => '-----', 0 => '自動与信　　', 1 => '与信全部ＯＫ', 2 => '通常与信')
			)
		);

		$this->view->assign( 'current_action',  'searche/form');
		return $this->view;
	}

	/**
	 * 検索実行
	 */
	public function searchAction()
	{
		$params = $this->getParams();

		$oemId = $this->app->authManagerAdmin->getUserInfo()->OemId;

		$sql  = " SELECT DISTINCT ";
		$sql .= "     ent.EnterpriseId ";
		$sql .= " ,   ent.LoginId ";
		$sql .= " ,   ent.EnterpriseNameKj ";
		$sql .= " ,   ent.RepNameKj ";
		$sql .= " ,   ent.Plan ";
		$sql .= " ,   (SELECT PricePlanName FROM M_PricePlan WHERE ValidFlg = 1 AND PricePlanId = ent.Plan) AS PlanNm ";
		$sql .= " ,   (SELECT KeyContent FROM M_Code WHERE ValidFlg = 1 AND CodeId = 2 AND KeyCode = pay.FixPattern) AS FixPatternNm ";
		$sql .= " ,   ent.CpNameKj ";
		$sql .= " ,   ent.CpNameKn ";
		$sql .= " ,   ent.DivisionName ";
		$sql .= " ,   ent.ContactPhoneNumber ";
		$sql .= " ,   ent.MailAddress ";
		$sql .= " ,   '' AS OptionField ";
		$sql .= " ,   ent.Note ";
		$sql .= " ,   ent.OemId ";
		$sql .= " FROM ";
		$sql .= "     T_Enterprise ent ";
		$sql .= "         LEFT OUTER JOIN ";
		$sql .= "     AT_Enterprise AS at ON ent.EnterpriseId = at.EnterpriseId ";
		$sql .= "         LEFT OUTER JOIN ";
		$sql .= "     M_PayingCycle pay ON ent.PayingCycleId = pay.PayingCycleId ";
		$sql .= "         LEFT OUTER JOIN ";
		$sql .= "     T_Site sit ON ent.EnterpriseId = sit.EnterpriseId ";
		$sql .= "         LEFT OUTER JOIN ";
		$sql .= "     T_Oem AS oem ON ent.OemId = oem.OemId ";
		// 繰り越し残
		if ($params['RemCarryOver'] != '') {
            $sql .= "     LEFT OUTER JOIN ";
            $sql .= " T_PayingControl AS pc ON ent.EnterpriseId = pc.EnterpriseId ";
		}
		$sql .= " WHERE ";
		$sql .= "     1 = 1 ";

		// WHERE句の追加
        //OEM検索
        if (isset($oemId)) {
            $sql .= " AND ent.OemId = " . $oemId ;
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
        $wMonthlyFee = BaseGeneralUtils::makeWhereInt(' ( ent.MonthlyFee + at.IncludeMonthlyFee + at.ApiMonthlyFee + at.CreditNoticeMonthlyFee + at.NCreditNoticeMonthlyFee + at.ReserveMonthlyFee ) ', $params['MonthlyFeeF'], $params['MonthlyFeeT']);
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
        $wOemMonthlyFee = BaseGeneralUtils::makeWhereInt(' ( ent.OemMonthlyFee + at.OemIncludeMonthlyFee + at.OemApiMonthlyFee + at.OemCreditNoticeMonthlyFee + at.OemNCreditNoticeMonthlyFee + at.OemReserveMonthlyFee ) ', $params['OemMonthlyFeeF'], $params['OemMonthlyFeeT']);
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

        // 登録日
        $wRegistDate = BaseGeneralUtils::makeWhereDateTime('ent.RegistDate', $params['RegistDateF'], $params['RegistDateT']);
        if ($wRegistDate != '') {
            $sql .= (" AND " . $wRegistDate);
        }

        // 繰り越し残
        if ($params['RemCarryOver'] != '' && is_numeric($params['RemCarryOver'])) {
            $sql .= (" AND pc.PayingControlStatus = 1 ");
            $sql .= (" AND pc.ExecFlg = 10 ");
            $sql .= (" AND pc.DecisionPayment >= ( " . $params['RemCarryOver'] . " * -1 ) ");
        }

        // 検索条件保存
        $_SESSION[self::SESS_SEARCH_INFO] = $sql;

        $sql = $sqlSel . $sql;
        $sql .= " ORDER BY ent.EnterpriseId ";

        // クエリー実行
        $ri = $this->app->dbAdapter->query($sql)->execute(null);

        $this->view->assign('list', $ri);

        $this->view->assign( 'current_action',  'searche/search');
        return $this->view;
	}
}

