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
use models\Table\TableSite;
use models\Logic\LogicTemplate;

class EnterpriseContractController extends CoralControllerAction
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

	/**
	 * 検索実行
	 */
	public function listAction()
	{
        $params = $this->getParams();

        $sql  = " SELECT DISTINCT ";
        $sql .= "     ent.EnterpriseId ";
        $sql .= " ,   ent.LoginId ";
        $sql .= " ,   ent.EnterpriseNameKj ";
        // 加盟店ｷｬﾝﾍﾟｰﾝ対応_ｷｬﾝﾍﾟｰﾝ期間中は加盟店ｷｬﾝﾍﾟｰﾝの情報を表示するﾌｧﾝｸｼｮﾝをｺｰﾙする↓↓↓
        $sql .= " ,   F_GetCampaignVal(ent.EnterpriseId, sit.SiteId, DATE(NOW()), 'AppPlan') AS Plan ";
        $sql .= " ,   (SELECT PricePlanName FROM M_PricePlan WHERE ValidFlg = 1 AND PricePlanId = F_GetCampaignVal(ent.EnterpriseId, sit.SiteId, DATE(NOW()), 'AppPlan')) AS PlanNm ";
        // 加盟店ｷｬﾝﾍﾟｰﾝ対応_ｷｬﾝﾍﾟｰﾝ期間中は加盟店ｷｬﾝﾍﾟｰﾝの情報を表示するﾌｧﾝｸｼｮﾝをｺｰﾙする↑↑↑
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
        $sql .= " ,   sit.SiteId ";
        $sql .= " ,   sit.SiteNameKj ";
        $sql .= " ,   sit.SiteNameKn ";
        $sql .= " ,   sit.RegistDate ";
        $sql .= " ,   sit.Url ";
        $sql .= " ,   (SELECT COUNT(1) FROM T_Site WHERE EnterpriseId = ent.EnterpriseId) AS SiteCount ";
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
        $sql .= " ORDER BY ent.EnterpriseId DESC ";

        // クエリー実行
        $ri = $this->app->dbAdapter->query($sql)->execute(null);

        $this->view->assign('list', $ri);

        $enterprises = new TableEnterprise($this->app->dbAdapter);
        $this->view->assign('optionMap', $enterprises->getAllEnterpriseOptionInfo());

        return $this->view;
    }

    /**
     * CSVダウンロード実行
     */
    public function downloadAction()
    {
        // データ取得SQL
        $sql  = "SELECT  e.EnterpriseId ";
        $sql .= "    ,   e.LoginId ";
        $sql .= "    ,   e.EnterpriseNameKj ";
        $sql .= "    ,   e.EnterpriseNameKn ";
        $sql .= "    ,   e.RepNameKj ";
        $sql .= "    ,   e.RepNameKn ";
        $sql .= "    ,   e.UnitingAddress ";
        $sql .= "    ,   e.Phone ";
        $sql .= "    ,   e.Fax ";
        $sql .= "    ,   (SELECT pp.PricePlanName FROM M_PricePlan pp WHERE pp.PricePlanId = e.Plan) AS PricePlan ";
        $sql .= "    ,   e.MonthlyFee ";
        $sql .= "    ,   (SELECT c.KeyContent FROM M_Code c, M_PayingCycle pc WHERE  c.KeyCode = pc.FixPattern AND pc.PayingCycleId = e.PayingCycleId AND c.CodeId = 2) AS PayingCycle ";
        $sql .= "    ,   e.CpNameKj ";
        $sql .= "    ,   e.CpNameKn ";
        $sql .= "    ,   e.DivisionName ";
        $sql .= "    ,   e.MailAddress ";
        $sql .= "    ,   e.ContactPhoneNumber ";
        $sql .= "    ,   e.ContactFaxNumber ";
        $sql .= "    ,   e.PreSales ";
        $sql .= "    ,   DATE(e.ApplicationDate) AS ApplicationDate ";
        $sql .= "    ,   DATE(e.ServiceInDate) AS ServiceInDate ";
        $sql .= "    ,   e.OemId ";
        $sql .= "    ,   e.Note ";
        $sql .= "    ,   s.SiteId ";
        $sql .= "    ,   DATE(s.RegistDate) AS RegistDate ";
        $sql .= "    ,   s.SiteNameKj ";
        $sql .= "    ,   s.SiteNameKn ";
        $sql .= "    ,   (SELECT KeyContent FROM M_Code WHERE KeyCode = s.SiteForm AND CodeId = 10) AS SiteForm ";
        $sql .= "    ,   s.Url ";
        $sql .= "    ,   CASE WHEN s.FirstClaimLayoutMode = 0 THEN '通常（圧着）' ELSE '封書用紙' END AS FirstClaimLayoutMode ";
        $sql .= "    ,   CASE WHEN s.OutOfAmendsFlg = 1 THEN '全案件補償外' ELSE '補償外対象ではない' END AS OutOfAmends ";
//        $sql .= "    ,   s.FirstClaimLimitDays ";
        $sql .= "    ,   s.BarcodeLimitDays ";
// 請求書発行ルールは廃止(20150822_1006_suzuki_h)
//        $sql .= "    ,   CASE WHEN s.ClaimJournalClass = 0 THEN '伝票登録不要' ELSE '伝票登録必要' END AS ClaimJournalClass ";
        $sql .= "    ,   CASE WHEN s.ValidFlg = 0 THEN '無効' ELSE '有効' END AS ValidFlg ";
        $sql .= "    ,   CASE WHEN s.ServiceTargetClass = 0 THEN '通常' ELSE '役務' END AS ServiceTargetClass ";
        $sql .= "    ,   CASE WHEN s.T_OrderClass = 0 THEN '不可' ELSE '可能' END AS T_OrderClass ";
        $sql .= "    ,   s.YuchoMT ";
        $sql .= "    ,   s.SettlementAmountLimit ";
        $sql .= "    ,   s.SettlementFeeRate ";
        $sql .= "    ,   s.ClaimFeeBS ";
        $sql .= "    ,   s.ClaimFeeDK ";
        $sql .= "    ,   s.ReClaimFee ";
        $sql .= "    ,   s.OemSettlementFeeRate ";
        $sql .= "    ,   s.OemClaimFee ";
        $sql .= "    ,   DATE(s.SiteConfDate) AS SiteConfDate ";
        $sql .= "    ,   CASE WHEN s.SitClass = 0 THEN '一般' ELSE '法人' END AS SitClass ";
        $sql .= "    ,   CASE WHEN s.PayingBackFlg = 0 THEN '行わない' ELSE '行う' END AS PayingBack ";
        $sql .= "    ,   s.PayingBackDays ";
        $sql .= "    ,   (SELECT KeyContent FROM M_Code WHERE KeyCode = s.PrintFormDK AND CodeId = 79) AS PrintFormDK ";
        $sql .= "    ,   (SELECT KeyContent FROM M_Code WHERE KeyCode = s.PrintFormBS AND CodeId = 79) AS PrintFormBS ";
        $sql .= "    ,   s.KisanbiDelayDays ";
        $sql .= "FROM    T_Enterprise e ";
        $sql .= "    ,   T_Site s ";
        $sql .= "WHERE   e.EnterpriseId = s.EnterpriseId ";
        $sql .= "ORDER BY ";
        $sql .= "        e.EnterpriseId DESC ";

        $stm = $this->app->dbAdapter->query($sql);

        $ar = ResultInterfaceToArray($stm->execute(null));

        // テンプレートヘッダー/フィールドマスタを使用して、CSVをResposeに入れる
        $templateId = 'CKI15120_1';     // テンプレートID       加盟店サイト契約情報一覧
        $templateClass = 0;             // 区分                 CB
        $seq = 0;                       // シーケンス           区分CBのため0
        $templatePattern = 0;           // テンプレートパターン 区分CBのため0

        $logicTemplate = new LogicTemplate( $this->app->dbAdapter );
        $response = $logicTemplate->convertArraytoResponse( $ar, sprintf( 'Enterprise_%s.csv', date('YmdHis') ), $templateId, $templateClass, $seq, $templatePattern, $this->getResponse() );

        if( $response == false ) {
            throw new \Exception( $logicTemplate->getErrorMessage() );
        }

        return $response;
    }
}

