<?php
namespace cbadmin\Controller;

use Coral\Base\BaseHtmlUtils;
use Coral\Base\BaseUtility;
use Coral\Base\BaseGeneralUtils;
use Coral\Coral\Controller\CoralControllerAction;
use Coral\Coral\CoralCodeMaster;
use Coral\Coral\CoralValidate;
use Coral\Coral\CoralPager;
use cbadmin\Application;
use models\Table\TableOem;
use models\Table\TableEnterprise;
use models\Table\TableSite;
use models\Table\TableSystemProperty;
use models\Logic\LogicTemplate;

class RetentionAlertController extends CoralControllerAction
{
	protected $_componentRoot = './application/views/components';

	/**
	 * アプリケーションオブジェクト
	 * @var Application
	 */
	private $app;


	/**
	 * １ページ最大表示件数
	 *
	 * @var int
	 */
	const PAGE_LINE_MAX = 1000;

	/**

	/**
	 * Controllerを初期化する
	 */
	public function _init()
	{
        $this->app = Application::getInstance();
        $this->view->assign('userInfo', $this->app->authManagerAdmin->getUserInfo());

        $this->addStyleSheet('../css/default02.css');
        $this->addStyleSheet('../css/base.ui.tableex.css');
        $this->addJavaScript('../js/json+.js');
        $this->addJavaScript('../js/prototype.js');
        $this->addJavaScript('../js/corelib.js');
        $this->addJavaScript('../js/base.ui.js');
        $this->addJavaScript('../js/base.ui.tableex.js');
        $this->addJavaScript('../js/base.ui.datepicker.js');
        $this->addJavaScript('../js/base.ui.customlist.js');

        $this->setPageTitle("後払い.com - 滞留アラート");

	}

	/**
	 * 検索実行
	 */
	public function searchAction()
	{
        $params = $this->getParams();

        // [paging] 1ページあたりの項目数
        $ipp = self::PAGE_LINE_MAX;
        // [paging] 指定ページを取得
        $current_page = (isset($params['page'])) ? (int)$params['page'] : 1;
        if ($current_page < 1) $current_page = 1;

        $oem = new TableOem($this->app->dbAdapter);
        $syspro = new TableSystemProperty($this->app->dbAdapter);

        //Oem先取得
        $oemdata = $oem->getAllOem('desc');
        $oemdata = ResultInterfaceToArray($oemdata);
        $this->view->assign('oem', $oemdata);

        //選択したOemId取得
        $oemid = $params['oemid'];

        if(isset($params['oid'])){
            $oemid = $params['oid'];
        }

        if(!isset($oemid)){
            $oemid = 0;
        }

        $this->view->assign('selectoem', $oemid);
//         //LongJournalDays
//         $lonjou =$syspro->find(16)->current();
//         //LongConfirmArrivalDays
//         $loncon =$syspro->find(17)->current();
//         //LongLoginDays
//         $lonlog =$syspro->find(18)->current();
        //LongJournalDays
        $lonjou = $syspro->getValue('[DEFAULT]', 'systeminfo', 'LongJournalDays');

        //LongConfirmArrivalDays
        $loncon = $syspro->getValue('[DEFAULT]', 'systeminfo', 'LongConfirmArrivalDays');

        //LongLoginDays
        $lonlog = $syspro->getValue('[DEFAULT]', 'systeminfo', 'LongLoginDays');

        $sql  = " SELECT  oem.OemNameKj ";
        $sql .= "     ,   e.EnterpriseId ";
        $sql .= "     ,   e.EnterpriseNameKj ";
        $sql .= "     ,   s.SiteNameKj ";
        $sql .= "     ,   CASE ";
        $sql .= "             WHEN sa.AlertClass = 0 THEN '伝票登録待ち' ";
        $sql .= "             WHEN sa.AlertClass = 1 THEN '着荷確認待ち' ";
        $sql .= "             WHEN sa.AlertClass = 2 THEN 'ログインなし' ";
        $sql .= "             ELSE '入金取消' ";
        $sql .= "         END AS AlertClasses ";
        $sql .= "     ,   sa.AlertClass ";
        $sql .= "     ,   CONCAT(sa.StagnationDays, '日') AS StagnationDays ";
        $sql .= "     ,   o.OrderSeq ";
        $sql .= "     ,   o.OrderId ";
        $sql .= "     ,   o.ReceiptOrderDate ";
        $sql .= "     ,   c.NameKj ";
        $sql .= "     ,   c.UnitingAddress ";
        $sql .= "     ,   c.Phone ";
        $sql .= "     ,   c.MailAddress ";
        $sql .= "     ,   o.UseAmount ";
        $sql .= "     ,   c.CustomerId ";
        $sql .= " FROM    T_StagnationAlert sa ";
        $sql .= "             LEFT OUTER JOIN T_Enterprise e ON (sa.EnterpriseId = e.EnterpriseId) ";
        $sql .= "             LEFT OUTER JOIN T_Order o ON (sa.OrderSeq = o.OrderSeq) ";
        $sql .= "             LEFT OUTER JOIN T_Site s ON (o.SiteId = s.SiteId) ";
        $sql .= "             LEFT OUTER JOIN T_Customer c ON (o.OrderSeq = c.OrderSeq) ";
        $sql .= "             LEFT OUTER JOIN T_Oem oem ON (e.OemId = oem.OemId) ";
        $sql .= " WHERE   CASE ";
        $sql .= "             WHEN sa.AlertClass = 0 THEN sa.StagnationDays >= (:LongJournalDays) ";
        $sql .= "             WHEN sa.AlertClass = 1 THEN sa.StagnationDays >= (:LongConfirmArrivalDays) ";
        $sql .= "             WHEN sa.AlertClass = 2 THEN sa.StagnationDays >= (:LongLoginDays) ";
        $sql .= "             ELSE sa.StagnationDays IS NULL ";
        $sql .= "         END ";
        $sql .= " AND     sa.AlertSign = 1 ";
        $sql .= " AND     sa.ValidFlg = 1 ";

        if($oemid !=0){
            $sql .= " AND     e.OemId = :OemId ";
        }
        $sql .= " ORDER BY ";
        $sql .= "         sa.StagnationDays DESC ";


        if($oemid !=0){
            $prm = array(
                     ':OemId' => $oemid,
                     ':LongJournalDays' => $lonjou,
                     ':LongConfirmArrivalDays' => $loncon,
                     ':LongLoginDays' => $lonlog,
            );
        }else{
            $prm = array(
                    ':LongJournalDays' => $lonjou,
                    ':LongConfirmArrivalDays' => $loncon,
                    ':LongLoginDays' => $lonlog,
            );
        }

        // クエリー実行
        $ri = $this->app->dbAdapter->query($sql)->execute($prm);
        $list = ResultInterfaceToArray($ri);

        // count関数対策
        $listLen = 0;
        if(!empty($list)) {
            $listLen = count($list);
        }

        // [paging] ページャ初期化
        $pager = new CoralPager($listLen, $ipp);
        // [paging] 指定ページを補正
        if( $current_page > $pager->getTotalPage() ) $current_page = $pager->getTotalPage();
        // [paging] 対象リストをページング情報に基づいて対象リストをスライス
        if($listLen > 0) $list = array_slice( $list, $pager->getStartIndex( $current_page ), $ipp );
        // [paging] ページングナビゲーション情報
        $page_links = array( 'base' => "retentionalert/search/page" );
        $page_links['prev'] = $page_links['base'] . '/' . ( $current_page - 1 ).'/oid/'.f_e($oemid);
        $page_links['next'] = $page_links['base'] . '/' . ( $current_page + 1 ).'/oid/'.f_e($oemid);
        // [paging] ページング関連の情報をビューへアサイン

        $this->view->assign('list', $list);
        $this->view->assign( 'current_page', $current_page );
        $this->view->assign( 'pager', $pager );
        $this->view->assign( 'page_links', $page_links );

        return $this->view;
    }

    /**
     * CSVダウンロード実行
     */
    public function dcsvAction()
    {
        // パラメータ取得
        $params = $this->getParams();
        $oemid = $params['oemid'];

        $syspro = new TableSystemProperty($this->app->dbAdapter);

        //LongJournalDays
        $lonjou = $syspro->getValue('[DEFAULT]', 'systeminfo', 'LongJournalDays');

        //LongConfirmArrivalDays
        $loncon = $syspro->getValue('[DEFAULT]', 'systeminfo', 'LongConfirmArrivalDays');

        //LongLoginDays
        $lonlog = $syspro->getValue('[DEFAULT]', 'systeminfo', 'LongLoginDays');


        $sql  = " SELECT  oem.OemId ";
        $sql .= "     ,   oem.OemNameKj ";
        $sql .= "     ,   e.EnterpriseId ";
        $sql .= "     ,   e.LoginId ";
        $sql .= "     ,   e.EnterpriseNameKj ";
        $sql .= "     ,   s.SiteId ";
        $sql .= "     ,   s.SiteNameKj ";
        $sql .= "     ,   sa.AlertClass ";
        $sql .= "     ,   CASE ";
        $sql .= "             WHEN sa.AlertClass = 0 THEN '伝票登録待ち' ";
        $sql .= "             WHEN sa.AlertClass = 1 THEN '着荷確認待ち' ";
        $sql .= "             ELSE 'ログインなし' ";
        $sql .= "         END AS AlertNaiyo ";
        $sql .= "     ,   CONCAT(sa.StagnationDays, '日') AS StagnationDays ";
        $sql .= "     ,   o.OrderSeq ";
        $sql .= "     ,   o.OrderId ";
        $sql .= "     ,   o.ReceiptOrderDate ";
        $sql .= "     ,   c.CustomerId ";
        $sql .= "     ,   c.NameKj ";
        $sql .= "     ,   c.UnitingAddress ";
        $sql .= "     ,   c.Phone ";
        $sql .= "     ,   c.MailAddress ";
        $sql .= "     ,   o.UseAmount ";
        $sql .= " FROM    T_StagnationAlert sa ";
        $sql .= "             LEFT OUTER JOIN T_Enterprise e ON (sa.EnterpriseId = e.EnterpriseId) ";
        $sql .= "             LEFT OUTER JOIN T_Order o ON (sa.OrderSeq = o.OrderSeq) ";
        $sql .= "             LEFT OUTER JOIN T_Site s ON (o.SiteId = s.SiteId) ";
        $sql .= "             LEFT OUTER JOIN T_Customer c ON (o.OrderSeq = c.OrderSeq) ";
        $sql .= "             LEFT OUTER JOIN T_Oem oem ON (e.OemId = oem.OemId) ";
        $sql .= " WHERE   CASE ";
        $sql .= "             WHEN sa.AlertClass = 0 THEN sa.StagnationDays >= (:LongJournalDays) ";
        $sql .= "             WHEN sa.AlertClass = 1 THEN sa.StagnationDays >= (:LongConfirmArrivalDays) ";
        $sql .= "             ELSE sa.StagnationDays >= (:LongLoginDays) ";
        $sql .= "         END ";
        $sql .= " AND     sa.AlertSign = 1 ";
        $sql .= " AND     sa.ValidFlg = 1 ";

        if($oemid !=0){
            $sql .= " AND     e.OemId = :OemId ";
        }
        $sql .= " ORDER BY ";
        $sql .= "         sa.StagnationDays DESC ";


        if($oemid !=0){
            $prm = array(
                    ':OemId' => $oemid,
                    ':LongJournalDays' => $lonjou,
                    ':LongConfirmArrivalDays' => $loncon,
                    ':LongLoginDays' => $lonlog,
            );
        }else{
            $prm = array(
                    ':LongJournalDays' => $lonjou,
                    ':LongConfirmArrivalDays' => $loncon,
                    ':LongLoginDays' => $lonlog,
            );
        }

        // クエリー実行
        $ri = $this->app->dbAdapter->query($sql)->execute($prm);
        $list = ResultInterfaceToArray($ri);

        // テンプレートヘッダー/フィールドマスタを使用して、CSVをResposeに入れる
        $templateId = 'CKI11083_1';     // テンプレートID       滞留アラート
        $templateClass = 0;             // 区分                 CB
        $seq = 0;                       // シーケンス
        $templatePattern = 0;           // テンプレートパターン

        $logicTemplate = new LogicTemplate( $this->app->dbAdapter );
        $response = $logicTemplate->convertArraytoResponse( $list, sprintf( 'Alert_%s.csv', date('YmdHis') ), $templateId, $templateClass, $seq, $templatePattern, $this->getResponse() );

        if( $response == false ) {
            throw new \Exception( $logicTemplate->getErrorMessage() );
        }

        return $response;
    }
}

