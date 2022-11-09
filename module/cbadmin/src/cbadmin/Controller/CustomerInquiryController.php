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
use models\Table\TableEnterpriseCustomer;
use models\Table\TableManagementCustomer;

class CustomerInquiryController extends CoralControllerAction
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
	 * 検索フォームの表示
	 */
	public function formAction()
	{
        return $this->view;
	}

	/**
	 * 検索実行
	 */
	public function searchAction()
	{
        $params = $this->getParams();

        // 検索実施には最低限１つ以上の[検索条件]を求める
        if (!$this->isHaveSearchCondition($params)) {
            // 条件未指定
            $this->view->assign('noParamsError', 1);
            $this->setTemplate('form');
            return $this->view;
        }

        $sql = "";

        if(!(!isset($params['Management']) && isset($params['Enterprise']) == 1)){
            $sql .= " SELECT ";
            $sql .= "     '管理顧客' as  class ";
            $sql .= " ,   '' AS EnterpriseId ";
            $sql .= " ,   '' AS EntCustSeq";
            $sql .= " ,   mc.ManCustId ";
            $sql .= " ,   mc.NameKj ";
            $sql .= " ,   mc.PostalCode ";
            $sql .= " ,   mc.UnitingAddress ";
            $sql .= " ,   mc.Phone ";
            $sql .= " ,   mc.MailAddress ";
            $sql .= " FROM ";
            $sql .= "     T_ManagementCustomer mc ";
            $sql .= "     LEFT OUTER JOIN T_EnterpriseCustomer ec ON (mc.ManCustId = ec.ManCustId) ";
            $sql .= "     LEFT OUTER JOIN T_ClaimControl cc ON (ec.EntCustSeq = cc.EntCustSeq) ";
            $sql .= "     LEFT OUTER JOIN MPV_MypageCustomer vmc ON (mc.ManCustId = vmc.ManCustId) ";
            $sql .= " WHERE ";
            $sql .= "     1 = 1 ";
            $sql .= " AND mc.ValidFlg = 1 ";

            //顧客名検索
            if(strlen($params['SearchNameKj']) != 0) {
                $sql .= " AND mc.SearchNameKj like " . $this->app->dbAdapter->getPlatform()->quoteValue("%" . mb_ereg_replace('[ 　\r\n\t\v]', '', $params['SearchNameKj']) . "%") ;
            }
            //顧客名カナ検索
            if(strlen($params['SearchNameKn']) != 0) {
                $sql .= " AND mc.SearchNameKn like " . $this->app->dbAdapter->getPlatform()->quoteValue("%" . mb_ereg_replace('[ 　\r\n\t\v]', '', $params['SearchNameKn']) . "%") ;
            }
            //郵便番号検索
            if(strlen($params['PostalCode']) != 0) {
                $sql .= " AND mc.PostalCode like " . $this->app->dbAdapter->getPlatform()->quoteValue("%" . $params['PostalCode'] . "%") ;
            }
            //住所検索
            if(strlen($params['SearchUnitingAddress']) != 0) {
                $sql .= " AND mc.SearchUnitingAddress like " . $this->app->dbAdapter->getPlatform()->quoteValue("%" . $params['SearchUnitingAddress'] . "%") ;
            }
            //電話番号検索
            if(strlen($params['SearchPhone']) != 0) {
                $sql .= " AND mc.SearchPhone like " . $this->app->dbAdapter->getPlatform()->quoteValue("%" . $params['SearchPhone'] . "%") ;
            }
            //メールアドレス検索
            if(strlen($params['MailAddress']) != 0) {
                $sql .= " AND mc.MailAddress like " . $this->app->dbAdapter->getPlatform()->quoteValue("%" . $params['MailAddress'] . "%") ;
            }
            //メモ
            if(strlen($params['Note']) != 0) {
               $sql .= " AND mc.Note like " . $this->app->dbAdapter->getPlatform()->quoteValue("%" . $params['Note'] . "%") ;
            }
            //ブラック顧客
            if(strlen($params['BlackFlg']) != 0) {
                $sql .= " AND mc.BlackFlg = " . $params['BlackFlg'] ;
            }
            //優良顧客
            if(strlen($params['GoodFlg']) != 0) {
                $sql .= " AND mc.GoodFlg = " . $params['GoodFlg'] ;
            }
            //クレーマー
            if(strlen($params['ClaimerFlg']) != 0) {
                $sql .= " AND mc.ClaimerFlg = " . $params['ClaimerFlg'] ;
            }
            //督促ストップ
            if(strlen($params['RemindStopFlg']) != 0) {
                $sql .= " AND mc.RemindStopFlg = " . $params['RemindStopFlg'] ;
            }
            //身分証アップロード
            if(strlen($params['IdentityDocumentFlg']) != 0) {
                $sql .= " AND mc.IdentityDocumentFlg = " . $params['IdentityDocumentFlg'] ;
            }
            //マイページ最終ログイン日
            $wLastLoginDate = BaseGeneralUtils::makeWhereDateTime(
                'vmc.LastLoginDate',
                BaseGeneralUtils::convertWideToNarrow($params['LastLoginDateF']),
                BaseGeneralUtils::convertWideToNarrow($params['LastLoginDateT'])
            );
            if ($wLastLoginDate != '') {
                $sql .= " AND " . $wLastLoginDate;
            }
            //GROUP BY追加
            $sql .= " GROUP BY ";
            $sql .= " mc.ManCustId, mc.NameKj, mc.PostalCode, mc.UnitingAddress, mc.Phone, mc.MailAddress ";

            //HAVING条件追加
            if($params['ClaimedBalance'] != 0 && $params['nonClaimedBalance'] == 0) {
                $sql .= " HAVING ";
                $sql .= " SUM(cc.ClaimedBalance) > 0";
            }elseif($params['ClaimedBalance'] == 0 && $params['nonClaimedBalance'] != 0){
                $sql .= " HAVING ";
                $sql .= " SUM(cc.ClaimedBalance) <= 0 ";
            }
        }

        if(!(isset($params['Management']) == 1 && !isset($params['Enterprise']))){

            if ($sql != "") {
                $sql .= " UNION ALL ";// T_ManagementCustomerからの抽出SQLが有効になっている場合は"UNION ALL"する
            }

            $sql .= " SELECT ";
            $sql .= "     '加盟店顧客' as  class ";
            $sql .= " ,   ec.EnterpriseId ";
            $sql .= " ,   ec.EntCustSeq";
            $sql .= " ,   ec.ManCustId ";
            $sql .= " ,   ec.NameKj ";
            $sql .= " ,   ec.PostalCode ";
            $sql .= " ,   ec.UnitingAddress ";
            $sql .= " ,   ec.Phone ";
            $sql .= " ,   ec.MailAddress ";
            $sql .= " FROM ";
            $sql .= "    T_EnterpriseCustomer ec ";
            $sql .= "    INNER JOIN T_ManagementCustomer mc ON (ec.ManCustId =  mc.ManCustId)";
            $sql .= "    LEFT OUTER JOIN T_ClaimControl cc ON (ec.EntCustSeq =  cc.EntCustSeq)";
            $sql .= "    LEFT OUTER JOIN MPV_MypageCustomer vmc ON (mc.ManCustId = vmc.ManCustId) ";
            $sql .= " WHERE ";
            $sql .= "     1 = 1 ";
            $sql .= " AND ec.ValidFlg = 1 ";

            // WHERE句の追加

            //顧客名検索
            if(strlen($params['SearchNameKj']) != 0) {
                $sql .= " AND ec.SearchNameKj like " . $this->app->dbAdapter->getPlatform()->quoteValue("%" . mb_ereg_replace('[ 　\r\n\t\v]', '', $params['SearchNameKj']) . "%") ;
            }
            //顧客名カナ検索
            if(strlen($params['SearchNameKn']) != 0) {
                $sql .= " AND ec.SearchNameKn like " . $this->app->dbAdapter->getPlatform()->quoteValue("%" . mb_ereg_replace('[ 　\r\n\t\v]', '', $params['SearchNameKn']) . "%") ;
            }
            //郵便番号検索
            if(strlen($params['PostalCode']) != 0) {
                $sql .= " AND ec.PostalCode like " . $this->app->dbAdapter->getPlatform()->quoteValue("%" . $params['PostalCode'] . "%") ;
            }
            //住所検索
            if(strlen($params['SearchUnitingAddress']) != 0) {
                $sql .= " AND ec.SearchUnitingAddress like " . $this->app->dbAdapter->getPlatform()->quoteValue("%" . $params['SearchUnitingAddress'] . "%") ;
            }
            //電話番号検索
            if(strlen($params['SearchPhone']) != 0) {
                $sql .= " AND ec.SearchPhone like " . $this->app->dbAdapter->getPlatform()->quoteValue("%" . $params['SearchPhone'] . "%") ;
            }
            //メールアドレス検索
            if(strlen($params['MailAddress']) != 0) {
                $sql .= " AND ec.MailAddress like " . $this->app->dbAdapter->getPlatform()->quoteValue("%" . $params['MailAddress'] . "%") ;
            }
            //メモ
            if(strlen($params['Note']) != 0) {
                $sql .= " AND ec.Note like " . $this->app->dbAdapter->getPlatform()->quoteValue("%" . $params['Note'] . "%") ;
            }
            //ブラック顧客
            if(strlen($params['BlackFlg']) != 0) {
                $sql .= " AND mc.BlackFlg =" . $params['BlackFlg'] ;
            }
            //優良顧客
            if(strlen($params['GoodFlg']) != 0) {
                $sql .= " AND mc.GoodFlg = " . $params['GoodFlg'] ;
            }
            //クレーマー
            if(strlen($params['ClaimerFlg']) != 0) {
                $sql .= " AND mc.ClaimerFlg = " . $params['ClaimerFlg'] ;
            }
            //督促ストップ
            if(strlen($params['RemindStopFlg']) != 0) {
                $sql .= " AND mc.RemindStopFlg = " . $params['RemindStopFlg'] ;
            }
            //身分証アップロード
            if(strlen($params['IdentityDocumentFlg']) != 0) {
                $sql .= " AND mc.IdentityDocumentFlg = " . $params['IdentityDocumentFlg'] ;
            }
            //マイページ最終ログイン日
            $wLastLoginDate = BaseGeneralUtils::makeWhereDateTime(
                'vmc.LastLoginDate',
                BaseGeneralUtils::convertWideToNarrow($params['LastLoginDateF']),
                BaseGeneralUtils::convertWideToNarrow($params['LastLoginDateT'])
            );
            if ($wLastLoginDate != '') {
                $sql .= " AND " . $wLastLoginDate;
            }
            //口座振替利用(加盟店顧客のみ)
            if ((strlen($params['RequestStatus']) != 0) && (strlen($params['nonRequestStatus']) != 0)) {
                ; //(処理なしの明示)利用する･利用しない、の何れのﾁｪｯｸもONの時は、絞込み条件不要
            }
            else {
                //(利用する)
                if(strlen($params['RequestStatus']) != 0) {
                    $sql .= " AND ec.RequestStatus IN (1, 2, 3) " ;
                }
                //(利用しない)
                if(strlen($params['nonRequestStatus']) != 0) {
                    $sql .= " AND (ec.RequestStatus IS NULL OR ec.RequestStatus IN (0, 9)) " ;
                }
            }
            //GROUP BY追加
            $sql .= " GROUP BY ";
            $sql .= " ec.EnterpriseId, ec.EntCustSeq, ec.NameKj, ec.PostalCode, ec.UnitingAddress, ec.Phone, ec.MailAddress ";
            //HAVING条件追加
            if($params['ClaimedBalance'] != 0 && $params['nonClaimedBalance'] == 0) {
                $sql .= " HAVING ";
                $sql .= " SUM(cc.ClaimedBalance) > 0";
            }elseif($params['ClaimedBalance'] == 0 && $params['nonClaimedBalance'] != 0){
                $sql .= " HAVING ";
                $sql .= " SUM(cc.ClaimedBalance) <= 0";
            }
        }

        // クエリー実行
        $list = $this->app->dbAdapter->query($sql)->execute(null);

        unset($params['controller']);
        unset($params['action']);
        unset($params['__NAMESPACE__']);
        unset($params['__CONTROLLER__']);
        $this->view->assign('srchparams', serialize($params));
        $this->view->assign('list', $list);
        return $this->view;
	}

    /**
     * 検索条件を(最低でも1つ)保持しているか？
     *
     * @param array $params 画面項目
     * @return boolean
     */
	protected function isHaveSearchCondition($params)
	{
        if ((strlen($params['SearchNameKj']) == 0) &&
            (strlen($params['SearchNameKn']) == 0) &&
            (strlen($params['PostalCode']) == 0) &&
            (strlen($params['SearchUnitingAddress']) == 0) &&
            /* 以下、連絡先情報 */
            (strlen($params['SearchPhone']) == 0) &&
            (strlen($params['MailAddress']) == 0) &&
            /* 以下、請求情報 */
            (!isset($params['ClaimedBalance'])) &&
            (!isset($params['nonClaimedBalance'])) &&
            /* 以下、その他 */
            (!isset($params['BlackFlg'])) &&
            (!isset($params['GoodFlg'])) &&
            (!isset($params['ClaimerFlg'])) &&
            (!isset($params['RemindStopFlg'])) &&
            (!isset($params['IdentityDocumentFlg'])) &&
            (strlen($params['Note']) == 0) &&
            (!isset($params['Management'])) &&
            (!isset($params['Enterprise'])) &&
            (strlen($params['LastLoginDateF']) == 0) &&
            (strlen($params['LastLoginDateT']) == 0)
        ) {
            return false;
        }

        return true;
	}

    /**
     * detailAction
     * 情報詳細表示
    */
    public function detailAction()
    {
        $params = $this->getParams();

        if (isset($params['srchparams'])) {
            $srchparams = unserialize($params['srchparams']);

            if (!$srchparams) {
                $this->view->assign('noParamsError', 1);
                $this->setTemplate('form');
                return $this->view;
            }

            $this->view->assign( 'search_data', $srchparams );
            unset($params['srchparams']);
            $params = array_merge($params, $srchparams);
        }

        if(isset($params['mcid'])){
            $cid = $params['mcid'];
        }elseif(isset($params['ecid'])){
            $cid = $params['ecid'];
        }

        $custid = $params['custid'];

        $mancust = new TableManagementCustomer($this->app->dbAdapter);
        $entcust = new TableEnterpriseCustomer($this->app->dbAdapter);

        if(!isset($cid) && isset($custid)){
            $sql  = " SELECT ";
            $sql .= "     ec.ManCustId ";
            $sql .= " FROM ";
            $sql .= "     T_Customer c ";
            $sql .= "     INNER JOIN T_EnterpriseCustomer ec ";
            $sql .= "       ON c.EntCustSeq = ec.EntCustSeq ";
            $sql .= " WHERE ";
            $sql .= "       c.CustomerId = :CustomerId ";
            $prm = array( ':CustomerId' => $custid );
            $ri = $this->app->dbAdapter->query( $sql )->execute( $prm );
            $mcid = $ri->current();
            $cid = $mcid['ManCustId'];
        }


        //事業者顧客か加盟店顧客か
        $sql  = " SELECT ";
        $sql .= "     mc.ManCustId ";
        $sql .= " ,   mc.NameKj ";
        $sql .= " ,   mc.NameKn ";
        $sql .= " ,   mc.PostalCode ";
        $sql .= " ,   mc.UnitingAddress ";
        $sql .= " ,   mc.Phone ";
        $sql .= " ,   mc.MailAddress ";
        $sql .= " ,   IFNULL( SUM( CASE WHEN IFNULL(tbl.CNT, 0) > 0 THEN cc.ClaimedBalance ELSE 0 END ), 0 ) AS ClaimedBalance ";
        $sql .= " ,   mc.BlackFlg ";
        $sql .= " ,   mc.GoodFlg ";
        $sql .= " ,   mc.ClaimerFlg ";
        $sql .= " ,   mc.RemindStopFlg ";
        $sql .= " ,   mc.IdentityDocumentFlg ";
        $sql .= " ,   mc.Note ";
        $sql .= " ,   MAX(ec.EntCustSeq) AS EntCustSeq ";
        $sql .= " FROM ";
        $sql .= "     T_ManagementCustomer mc ";
        $sql .= "     LEFT OUTER JOIN T_EnterpriseCustomer ec ON (mc.ManCustId = ec.ManCustId) ";
        $sql .= "     LEFT OUTER JOIN T_ClaimControl cc ON (ec.EntCustSeq = cc.EntCustSeq) ";
        $sql .= "     LEFT OUTER JOIN (SELECT o2.P_OrderSeq ";
        $sql .= "                           , COUNT(*) CNT ";
        $sql .= "                        FROM T_Order o2 ";
        $sql .= "                             INNER JOIN T_Customer c2 ON ( c2.OrderSeq = o2.OrderSeq ) ";
        $sql .= "                             INNER JOIN T_EnterpriseCustomer ec2 ON ( ec2.EntCustSeq = c2.EntCustSeq ) ";
        $sql .= "                             INNER JOIN T_ManagementCustomer mc2 ON ( mc2.ManCustId = ec2.ManCustId ) ";
        $sql .= "                       WHERE mc2.ManCustId = :ManCustId ";
        $sql .= "                         AND o2.DataStatus <> 91 ";
        $sql .= "                       GROUP BY o2.P_OrderSeq ";
        $sql .= "                     ) tbl ON tbl.P_OrderSeq = cc.OrderSeq ";
        $sql .= " WHERE ";
        $sql .= "     mc.ManCustId = :ManCustId ";

        //GROUP BY追加
        $sql .= " GROUP BY ";
        $sql .= " mc.ManCustId, mc.NameKj, mc.NameKn, mc.PostalCode, mc.UnitingAddress, mc.Phone, mc.MailAddress,";
//        $sql .= " cc.BalanceClaimAmount, mc.BlackFlg, mc.GoodFlg, mc.ClaimerFlg, mc.RemindStopFlg, mc.IdentityDocumentFlg, mc.Note";
        $sql .= " mc.BlackFlg, mc.GoodFlg, mc.ClaimerFlg, mc.RemindStopFlg, mc.IdentityDocumentFlg, mc.Note";

        $prm = array( ':ManCustId' => $cid );
        $ri = $this->app->dbAdapter->query( $sql )->execute( $prm );
        $mandatas = ResultInterfaceToArray( $ri );

        $this->view->assign('mandata', $mandatas);


        $sql  = " SELECT ";
        $sql .= "     ec.EntCustSeq";
        $sql .= " ,   ec.EnterpriseId ";
        $sql .= " ,   ec.NameKj ";
        $sql .= " ,   ec.NameKn ";
        $sql .= " ,   ec.PostalCode ";
        $sql .= " ,   ec.UnitingAddress ";
        $sql .= " ,   ec.Phone ";
        $sql .= " ,   ec.MailAddress ";
        $sql .= " ,   IFNULL( SUM( CASE WHEN IFNULL(tbl.CNT, 0) > 0 THEN cc.ClaimedBalance ELSE 0 END ), 0 ) AS ClaimedBalance ";
        $sql .= " ,   ec.ManCustId ";
        $sql .= " FROM ";
        $sql .= "     T_EnterpriseCustomer ec ";
        $sql .= "     LEFT OUTER JOIN T_ClaimControl cc ON (ec.EntCustSeq =  cc.EntCustSeq)";
        $sql .= "     LEFT OUTER JOIN (SELECT o2.P_OrderSeq ";
        $sql .= "                           , COUNT(*) CNT ";
        $sql .= "                        FROM T_Order o2 ";
        $sql .= "                             INNER JOIN T_Customer c2 ON ( c2.OrderSeq = o2.OrderSeq ) ";
        $sql .= "                             INNER JOIN T_EnterpriseCustomer ec2 ON ( ec2.EntCustSeq = c2.EntCustSeq ) ";
        $sql .= "                             INNER JOIN T_ManagementCustomer mc2 ON ( mc2.ManCustId = ec2.ManCustId ) ";
        $sql .= "                       WHERE mc2.ManCustId = :ManCustId ";
        $sql .= "                         AND o2.DataStatus <> 91 ";
        $sql .= "                       GROUP BY o2.P_OrderSeq ";
        $sql .= "                     ) tbl ON tbl.P_OrderSeq = cc.OrderSeq ";
        $sql .= " WHERE ";
        $sql .= "     ec.ManCustId = :ManCustId ";

        //GROUP BY追加
        $sql .= " GROUP BY ";
        $sql .= " ec.EntCustSeq, ec.NameKj, ec.NameKn, ec.PostalCode, ec.UnitingAddress, ec.Phone, ec.MailAddress ,";
//        $sql .= " cc.BalanceClaimAmount, ec.ManCustId, ec.ValidFlg";
        $sql .= " ec.ManCustId";

        //ORDER BY追加
        $sql .= " ORDER BY ";
        $sql .= " ec.EntCustSeq ASC";

        $prm = array( ':ManCustId' => $cid );
        $ri = $this->app->dbAdapter->query( $sql )->execute( $prm );
        $entdatas = ResultInterfaceToArray( $ri );

        $this->view->assign('entdata', $entdatas);
        return $this->view;
    }

}