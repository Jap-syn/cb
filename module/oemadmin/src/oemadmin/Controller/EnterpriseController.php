<?php

namespace oemadmin\Controller;

use oemadmin\Application;
use Coral\Coral\Controller\CoralControllerAction;
use Coral\Coral\CoralCodeMaster;
use Coral\Base\BaseGeneralUtils;
use Coral\Base\BaseHtmlUtils;
use Coral\Base\IO\BaseIOUtility;
use models\Table\TableEnterprise;
use models\Table\TableSite;
use models\Table\TableApiUserEnterprise;
use models\Table\TableSelfBillingProperty;
use models\Table\TableClaimHistory;
use models\Logic\LogicShipping;
use models\Table\TableUser;
use Zend\Db\ResultSet\ResultSet;
use models\Table\TablePayingCycle;
use Zend\Config\Reader\Ini;
use models\Logic\LogicTemplate;
use models\Table\ATableEnterprise;

class EnterpriseController extends CoralControllerAction {

	protected $_componentRoot = './application/views/components';

	/**
	 * @var Application
	 */
	protected $app;

	/**
	 * IndexControllerを初期化する
	 */
	public function _init() {
		$this->app = Application::getInstance();

		$this
			->addStyleSheet($this->app->getOemCss())
			->addJavaScript('../../js/prototype.js')
			->addJavaScript('../../js/json.js');

		$this->setPageTitle($this->app->getOemServiceName()." - 事業者管理");

		$this->view->assign('userInfo', $this->app->authManagerAdmin->getUserInfo());

		/* 画面機能廃止
		// コードマスターから事業者情報向けのマスター連想配列を作成し、ビューへアサインしておく
 		$codeMaster = new CoralCodeMaster($this->app->dbAdapter);
		$masters = array(
			'Prefecture' => $codeMaster->getPrefectureMaster(),
			'PreSales' => $codeMaster->getPreSalesMaster(),
			'Industry' => $codeMaster->getIndustryMaster(),
			'Plan' => $codeMaster->getPlanMaster(),
			'PaymentSchedule' => $codeMaster->getPaymentSchedule(),
			'FixPattern' => $codeMaster->getFixPatternMaster(),
			'LimitDay' => $codeMaster->getLimitDayMaster(),
			'LimitDatePattern' => $codeMaster->getLimitDatePatternMaster(),
			'FfAccountClass' => $codeMaster->getAccountClassMaster(),
			'TcClass' => $codeMaster->getTcClassMaster(),
			'SiteForm' => $codeMaster->getSiteFormMaster(),
			'DocCollect' => $codeMaster->masterToArray($codeMaster->getDocCollectMaster()),
			'ExaminationResult' => $codeMaster->masterToArray($codeMaster->getExaminationResultMaster()),
			'AutoCreditJudgeMode' => $codeMaster->masterToArray($codeMaster->getAutoCreditJudgeModeMaster()),
			'CjMailMode' => $codeMaster->getCjMailModeMaster(),
			'CombinedClaimMode' => $codeMaster->getCombinedClaimMode(),
			'AutoClaimStopFlg' => $codeMaster->masterToArray($codeMaster->getAutoClaimStopFlgMaster())
		);

		$this->view->assign('master_map', $masters);
		*/
	}

	/**
	 * 事業者一覧を表示
	 */
	public function listAction() {
		$mdle = new TableEnterprise($this->app->dbAdapter);           // 加盟店
		$mdlpc = new TablePayingCycle($this->app->dbAdapter);         // 立替サイクルマスター

		// OEMId
		$oemId = $this->app->authManagerAdmin->getUserInfo()->OemId;

		$ri =$mdle->findOemEnterpriseAndFixPattern($oemId, false, 'EnterpriseId');

		$this->view->assign('list', $ri);
		$this->view->assign('current_action', 'enterprise/list');

		return $this->view;
	}

	/**
	 * 事業者情報詳細画面を表示
	 */
	public function detailAction() {

	    $params = $this->getParams();
		$eid = isset($params['eid']) ? $params['eid'] : -1;

		$mdle = new TableEnterprise($this->app->dbAdapter);
		$mdls = new TableSite($this->app->dbAdapter);
		$mdlat = new ATableEnterprise($this->app->dbAdapter);
		$codeMaster = new CoralCodeMaster($this->app->dbAdapter);

		$e = $mdle->findOemEnterpriseAndFixPattern($eid, true)->current();
		$at_row = $mdlat->find($eid)->current();
		$e = array_merge($e, $at_row);

        if(is_null($e['OemId']) || $e['OemId'] != $this->app->authManagerAdmin->getUserInfo()->OemId)
        {
            // url直指定でOemIDが不一致となった場合、エラーページに飛ばす。
            $this->_redirect('error/nop');
        }

		// マスターがらみの項目については、キャプションを求めてセットする。
		$e['PreSales'] = $codeMaster->getPreSalesCaption((int)$e['PreSales']);        // 推定月商
		$e['Industry'] = $codeMaster->getIndustryCaption((int)$e['Industry']);        // 業種
		$e['Plan'] = $codeMaster->getPlanCaption((int)$e['Plan']);                    // 利用プラン
		$e['FixPattern'] = $codeMaster->getFixPatternCaption((int)$e['FixPattern']);  // 締めパタン

		/* 画面機能廃止
		if ((int)$e['LimitDatePattern'] == 1) {
		   $e['LimitDay'] = sprintf('翌月%s日', $codeMaster->getLimitDayCaption($e['LimitDay']));
		} else if ((int)$e['LimitDatePattern'] == 2) {
		   $e['LimitDay'] = sprintf('当月%s日', $codeMaster->getLimitDayCaption($e['LimitDay']));
		} else {
		  $e['LimitDay'] = '';
		}
		*/

		$e['FfAccountClass'] = $codeMaster->getAccountClassCaption((int)$e['FfAccountClass']);    // 金融機関－口座種別
		$e['TcClass'] = $codeMaster->getTcClassCaption((int)$e['TcClass']);                       // 金融機関－振込手数料

		// サイト情報
		$sites = ResultInterfaceToArray($mdls->getValidAll($e['EnterpriseId']));

		//請求取りまとめモードがサイト毎だった場合に対象サイト数を取得(2013.10.23 kaki)
		$num = 0;
		foreach($sites as &$site) {
		    // サイト形態
			$site['SiteForm'] = $codeMaster->getSiteFormCaption($site['SiteForm']);
			// メアド
			$site['ReqMailAddrFlg'] = $site['ReqMailAddrFlg'] == 1 ? '必須' : '';
			// 初回請求支払期限算出方法
			$site['LimitDatePattern'] = $codeMaster->getLimitDatePatternCaption(is_null($site['LimitDatePattern']) ? 0 : $site['LimitDatePattern']);

			if($site['CombinedClaimFlg'] == 1)  {
				$num++;
			}
		}

		// 詳細画面からの更新処理で検証エラーが発生していたらその情報をマージする
		if (isset($params['prev_input']))
		{
		    $e = array_merge($e, $params['prev_input']);
		}

		$this->view->assign('error', (isset($params['prev_errors']) ? $params['prev_errors'] : null));

		$backTo = isset($params['prev_backto']) ? $params['prev_backto'] : $_SERVER['HTTP_REFERER'];

		$this->view->assign('data', $e);
		$this->view->assign('sites', $sites);
		$this->view->assign('backTo', $backTo);
		$this->view->assign('combinedclaimnum', $num);

		// DocCollect, ExaminationResultについて、リテラルの連想配列を廃止し
		/*
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
		*/

		// APIユーザリレーションのアサイン（09.06.17 eda）
		$mdlApiEnt = new TableApiUserEnterprise($this->app->dbAdapter);
		$apiUsers = ResultInterfaceToArray($mdlApiEnt->findApiUsersByEid($eid));
		$this->view->assign('apiUsers', $apiUsers == null ? array() : $apiUsers);

		// 事業者向け請求書同梱ツールに関する設定をアサイン（13.1.9 eda）
		//$this->view->assign('sbsettings', $this->getEntSelfBillingSettings());

		// 同梱ツール向けの拡張プロパティをアサイン（13.1.18 eda）
		/*
		$mdlSBProps = new TableSelfBillingProperty($this->app->dbAdapter);
		$mdlCHis = new TableClaimHistory($this->app->dbAdapter);

		$sb_props = array();
		foreach($mdlSBProps->findByEnterpriseId($eid)->toArray() as $i => $sb_data) {
			$sb_props[] = array_merge($sb_data, array('ch_count' => count($mdlCHis->findForSelfBillingByEnterpriseIdAndAccessKey($eid, $sb_data['AccessKey']))));
		}

		$this->view->assign('sbprops', $sb_props);
		*/

		$mdlu = new TableUser($this->app->dbAdapter);
		$userClass = 1;       // OEMオペレーター
		$seq = $this->app->authManagerAdmin->getUserInfo()->OemOpId;
		$userId = $mdlu->getUserId($userClass, $seq);

		// 画面表示向けなので常に非OEM用の一覧を取得
		$shippingLogic = new LogicShipping($this->app->dbAdapter, $userId);
		$this->view->assign('deliMethodMap', $shippingLogic->getDeliMethodListByOemId());
		$this->view->assign('current_action', 'enterprise/detail');

		return $this->view;
	}

	/**
	 * 事業者一覧のCSVダウンロード
	 */
	public function dcsvAction() {
	    /*
	    $this->app
	    ->addClass('NetB_IO_Utility')
	    ->addClass('EnterpriseCsvSettings')
	    ->addClass('EnterpriseCsvWriter');

	    // 自動レンダリングをOFF
	    $this->_helper->viewRenderer->setNoRender();

	    // CsvWriter初期化
	    $settingPath = NetB_IO_Utility::buildPath( './application/config', 'enterprise_csv_settings.json' );
	    $writer = new EnterpriseCsvWriter(EnterpriseCsvSettings::fromJsonFile($settingPath));

	    // 出力実行
	    $table = new Table_Enterprise($this->app->dbAdapter);
	    $writer
	    ->addRows($table->fetchAll('OemId = '.$this->app->authManagerAdmin->getUserInfo()->OemId, 'EnterpriseId')->toArray())
	    ->setEncoding('sjis-win')
	    ->write( sprintf('enterprise_%s.csv', date('YmdHis')), $this->getResponse() );
	    */

		$oemId = $this->app->authManagerAdmin->getUserInfo()->OemId;
		// ファイル名
		$fileName = sprintf( "Kameiten_%s.csv", date( "YmdHis" ) );

		// CSVデータ取得
		$sql  = " SELECT ENT.MailAddress ";
		$sql .= "     , ENT.EnterpriseNameKj ";
		$sql .= "     , CONCAT(SUBSTRING(REPLACE(ENT.PostalCode, '-', ''), 1, 3), '-', SUBSTRING(REPLACE(ENT.PostalCode, '-', ''), 4)) AS PostalCode ";
		$sql .= "     , ENT.PrefectureName ";
		$sql .= "     , ENT.City ";
		$sql .= "     , ENT.Town ";
		$sql .= "     , ENT.Building ";
		$sql .= "     , ENT.ContactPhoneNumber ";
		$sql .= "     , ENT.CpNameKj ";
		$sql .= "     , ENT.EnterpriseId ";
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
		$sql .= "     , ENT.MonthlyFee + AENT.IncludeMonthlyFee + AENT.ApiMonthlyFee + AENT.CreditNoticeMonthlyFee + AENT.NCreditNoticeMonthlyFee + AENT.ReserveMonthlyFee AS MonthlyFee ";
		$sql .= "     , ENT.OemMonthlyFee + AENT.OemIncludeMonthlyFee + AENT.OemApiMonthlyFee + AENT.OemCreditNoticeMonthlyFee + AENT.OemNCreditNoticeMonthlyFee + AENT.OemReserveMonthlyFee AS OemMonthlyFee ";
		$sql .= "     , MAX(S.SettlementAmountLimit) AS SettlementAmountLimit ";
		$sql .= "     , MAX(S.SettlementFeeRate) AS SettlementFeeRate ";
		$sql .= "     , MAX(S.OemSettlementFeeRate) AS OemSettlementFeeRate ";
		$sql .= "     , MAX( CASE S.SelfBillingFlg WHEN 1 THEN S.ClaimFeeDK ELSE S.ClaimFeeBS END ) AS ClaimFee";
		$sql .= "     , MAX(S.OemClaimFee) AS OemClaimFee ";
		$sql .= "     , MAX(S.ReClaimFee) AS ReClaimFee ";
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
		$sql .= "     , ENT.N_MonthlyFee + AENT.N_IncludeMonthlyFee + AENT.N_ApiMonthlyFee + AENT.N_CreditNoticeMonthlyFee + AENT.N_NCreditNoticeMonthlyFee + AENT.N_ReserveMonthlyFee AS N_MonthlyFee ";
		$sql .= "     , (CASE ENT.ValidFlg WHEN 1 THEN '有効' ELSE '無効' END) AS ValidFlg ";
		$sql .= "     , PC.PayingCycleName ";
		$sql .= "     , MAX(S.LimitDatePattern) AS LimitDatePattern ";
		$sql .= "     , MAX(S.LimitDay) AS LimitDay ";
		$sql .= "     , C8.KeyContent AS N_FixPattern ";
		$sql .= "     , NPC.PayingCycleName AS N_PayingCycleName ";
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
		$sql .= " FROM T_Enterprise ENT ";
		$sql .= "       INNER JOIN T_EnterpriseTotal ET ON ET.EnterpriseId = ENT.EnterpriseId ";
		$sql .= "       INNER JOIN AT_Enterprise AENT ON ENT.EnterpriseId = AENT.EnterpriseId ";
		$sql .= "       LEFT JOIN T_Oem OEM ON OEM.OemId = ENT.OemId ";
		$sql .= "       LEFT JOIN M_Code C1 ON C1.CodeId = 84 AND C1.KeyCode = ENT.DocCollect ";
		$sql .= "       LEFT JOIN M_Code C2 ON C2.CodeId = 75 AND C2.KeyCode = ENT.ExaminationResult ";
		$sql .= "       LEFT JOIN M_Code C3 ON C3.CodeId = 55 AND C3.KeyCode = ENT.PreSales ";
		$sql .= "       LEFT JOIN M_Code C4 ON C4.CodeId = 54 AND C4.KeyCode = ENT.Industry ";
		$sql .= "       LEFT JOIN M_PricePlan PP ON PP.PricePlanId = ENT.Plan ";
		$sql .= "       LEFT JOIN M_PayingCycle PC ON PC.PayingCycleId = ENT.PayingCycleId ";
		$sql .= "       LEFT JOIN M_PayingCycle NPC ON NPC.PayingCycleId = ENT.N_PayingCycleId ";
		$sql .= "       LEFT JOIN T_Site S ON S.EnterpriseId = ENT.EnterpriseId ";
		$sql .= "       LEFT JOIN M_Code C5 ON C5.CodeId = 2 AND C5.KeyCode = PC.FixPattern ";
		$sql .= "       LEFT JOIN M_Code C6 ON C6.CodeId = 51 AND C6.KeyCode = ENT.FfAccountClass ";
		$sql .= "       LEFT JOIN M_Code C7 ON C7.CodeId = 56 AND C7.KeyCode = ENT.TcClass ";
		$sql .= "       LEFT JOIN M_Code C8 ON C8.CodeId = 2 AND C8.KeyCode = NPC.FixPattern ";
		$sql .= " WHERE ENT.OemId = :OemId ";
		$sql .= " GROUP BY ENT.EnterpriseId ";
		$sql .= " ORDER BY ENT.EnterpriseId ASC ";

		$ri = $this->app->dbAdapter->query($sql)->execute(array(':OemId' => $oemId));
		$datas = ResultInterfaceToArray($ri);

		$templateId = 'COEM022';  // OEM事業者一覧CSV
		$templateClass = 1;       // OEMテンプレート
		$seq = $oemId;            // OemId
		$templatePattern = 0;

		$logicTemplate = new LogicTemplate( $this->app->dbAdapter );
		$response = $logicTemplate->convertArraytoResponse( $datas, $fileName, $templateId, $templateClass, $seq, $templatePattern, $this->getResponse() );

		if( $response == false )
		{
		    throw new \Exception( $logicTemplate->getErrorMessage() );
		}

		return $response;
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
		return $enterprises->findEnterprise2($eid);
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

		try {
		    $reader = new Ini();
		    $data = $reader->fromFile(Application::getInstance()->configPath);
		    $config = $data['ent_sbsettings'];
		} catch(Exception $err) {
			// nop
		}

		return array_merge($default_config, $config);
	}
}