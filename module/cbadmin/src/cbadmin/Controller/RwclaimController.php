<?php
namespace cbadmin\Controller;

use Zend\Db\Adapter\Driver\ResultInterface;
use Coral\Base\BaseHtmlUtils;
use Coral\Base\BaseUtility;
use Coral\Coral\Controller\CoralControllerAction;
use Coral\Coral\CoralCodeMaster;
use Coral\Coral\CoralPager;
use Coral\Coral\History\CoralHistoryOrder;
use cbadmin\Application;
use models\Table\TableClaimHistory;
use models\Table\TableEnterprise;
use models\Table\TableOrder;
use models\Table\TableOemClaimFee;
use models\Table\TableOem;
use models\Table\TableSite;
use models\Logic\LogicTemplate;
use models\View\ViewWaitForFirstClaim;
use oemmember\Controller\AccountController;
use models\Table\TableMypageOrder;
use Coral\Base\BaseGeneralUtils;
use models\Table\TableOrderSummary;
use models\Table\TableCustomer;
use models\Table\TableSystemProperty;
use Coral\Coral\CoralValidate;
use models\Logic\LogicMypageOrder;
use DOMPDFModule\View\Model\PdfModel;
use models\Logic\LogicNormalizer;
use Zend\Json\Json;
use models\Logic\Exception\LogicClaimException;
use models\Table\TableClaimError;
use models\Table\TableOrderItems;
use models\Table\TableBusinessCalendar;
use models\Table\TableCode;
use models\Table\TableClaimControl;
use models\Logic\LogicCreditTransfer;

class RwclaimController extends CoralControllerAction
{
	protected $_componentRoot = './application/views/components';

	const SESSION_JOB_PARAMS = 'CBRWCLAIM_JOB_PARAMS';
	const SESSION_JOB_PARAMS2 = 'CBRWCLAIM_JOB_PARAMS2';

	/**
	 * アプリケーションオブジェクト
	 * @var Application
	 */
	private $app;

	/**
	 * 請求に関連づいたサイト情報のキャッシュ
	 *
	 * @access protected
	 * @var array
	 */
	protected $_siteInfoCache;

	/**
	 * マイページのランダム生成文字列の試行回数
	 * @var int
	 */
	const MYPAGE_RAND_CHARANGE = 5;

	/**
	 * Controllerを初期化する
	 */
	public function _init()
	{
	    $this->app = Application::getInstance();
        $this->view->assign('userInfo', $this->app->authManagerAdmin->getUserInfo());

        $this->addStyleSheet('../css/default02.css');
        $this->addJavaScript('../js/prototype.js');

        $this->setPageTitle("後払い.com - 請求書発行");
	}

	/**
	 * 初回請求待ち（印刷ジョブ転送待ち）のリストを表示する。
	 */
	public function listAction()
	{
        $params = $this->getParams();

        $this->addJavaScript('../js/corelib.js');

        // [paging] CoralPagerのロードと必要なCSS/JSのアサイン
        $this->addStyleSheet('../css/base.ui.customlist.css');
        $this->addJavaScript('../js/base.ui.js');
        $this->addJavaScript('../js/base.ui.customlist.js');

        // [paging] 1ページあたりの項目数
        $ipp = 1000;
        // [paging] 指定ページを取得
        $current_page = (isset($params['page'])) ? (int)$params['page'] : 1;
        if ($current_page < 1) $current_page = 1;

        $billIssueState = isset($params['billIssueState']) ? $params['billIssueState'] : -1;// 請求書
        $paperType = isset($params['paperType']) ? $params['paperType'] : -1;// 用紙
        $salesDicisionDate = isset($params['salesDicisionDate']) ? $params['salesDicisionDate'] : '';// 売上確定日
        $oem = isset($params['oem']) ? $params['oem'] : 0;// OEM
        $entid = isset($params['entid']) ? $params['entid'] : '';// 加盟店ID
        $entnm = isset($params['entnm']) ? $params['entnm'] : '';// 加盟店名
        $odrid = isset($params['odrid']) ? $params['odrid'] : '';// 注文ID

        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);

        // SQL(基本)
        // ※CSVのSQLと同期をとること！！
        $sql =<<<EOQ
SELECT o.OrderSeq
,      o.OrderId
,      o.ReceiptOrderDate
,      o.RegistDate
,      o.EnterpriseId
,      vo.IncreArCaption AS IncreArCaption
,      s.NameKj
,      s.UnitingAddress
,      po.UseAmount AS UseAmount
,      s.DestNameKj
,      s.DestPostalCode
,      s.DestUnitingAddress
,      s.DestPhone
,      CASE
         WHEN s.RegNameKj <> s.RegDestNameKj THEN 1
         ELSE 0
       END AS IsAnotherDeli
,      e.EnterpriseNameKj
,      cd78.KeyContent AS ConfirmWaitingStr /* 状態 */
,      cd79.KeyContent AS PrintFormat /* 印刷書式 */
,      sit.SiteId
,      (SELECT COUNT(*) FROM T_Order WHERE P_OrderSeq = o.OrderSeq AND Cnl_Status = 1 AND IFNULL(o.CombinedClaimTargetStatus, 0) IN (91, 92) ) AS CombinedCnlCnt /* キャンセル未承認の取りまとめ注文 */
,      c.CustomerId
,      po.ConfirmWaitingFlg
,      IFNULL(o.OemId, 0) AS OemId
FROM   T_Order o
       INNER JOIN T_Enterprise e
               ON e.EnterpriseId = o.EnterpriseId
       INNER JOIN T_Site sit
               ON sit.SiteId = o.SiteId
       INNER JOIN T_OrderSummary s
               ON s.OrderSeq = o.OrderSeq
       INNER JOIN T_Customer c
               ON c.OrderSeq = o.OrderSeq
       INNER JOIN ( SELECT t.P_OrderSeq
                          ,MAX(IFNULL(t.LetterClaimStopFlg, 0))   AS LetterClaimStopFlg        -- 最大のストップフラグが1の場合＝ストップしたい注文あり
                          ,MAX(t.ConfirmWaitingFlg)               AS ConfirmWaitingFlg         -- 最大の確定待ちフラグが1の場合＝確定待ち注文あり
                          ,MAX(p.ClearConditionDate)              AS ClearConditionDate        -- 立替条件クリア日
                          ,MIN(t.DataStatus)                      AS DataStatus                -- データステータス
                          ,SUM(t.UseAmount)                       AS UseAmount                 -- 利用額合計
                      FROM T_Order t
                           INNER JOIN T_PayingAndSales p
                                   ON p.OrderSeq = t.OrderSeq
                     WHERE t.Cnl_Status = 0
                       AND t.DataStatus IN (41, 51)
                    GROUP BY t.P_OrderSeq
                  ) po
               ON po.P_OrderSeq = o.OrderSeq
       INNER JOIN V_OrderCustomer vo
               ON vo.OrderSeq = o.OrderSeq
       LEFT OUTER JOIN T_ClaimControl clm
                    ON clm.OrderSeq = o.OrderSeq
       LEFT OUTER JOIN M_Code cd78
                    ON cd78.CodeId  = 78
                   AND cd78.KeyCode = po.ConfirmWaitingFlg
       LEFT OUTER JOIN M_Code cd79
                    ON cd79.CodeId  = 79
                   AND cd79.KeyCode = sit.PrintFormBS

WHERE  1 = 1
AND    (
           ( po.DataStatus =  41  AND IFNULL(e.SelfBillingMode, 0) <= 0                                                         ) -- 別送加盟店
        OR ( po.DataStatus =  41  AND IFNULL(e.SelfBillingMode, 0) >  0 AND sit.SelfBillingFlg = 0                              ) -- 同梱加盟店、別送サイト
        OR ( po.DataStatus =  41  AND IFNULL(e.SelfBillingMode, 0) >  0 AND sit.SelfBillingFlg = 1 AND o.ClaimSendingClass = 12 ) -- 同梱加盟店、同梱サイト、別送に送る指示あり
        OR ( po.DataStatus = 51  AND clm.ReissueClass <> 0                               )                                       -- 初回請求書再発行の指示あり
       )
AND    po.LetterClaimStopFlg = 0                                                                                                 -- 紙請求ストップフラグが１件も立っていないもの
AND    IFNULL(o.CombinedClaimTargetStatus, 0) IN (0, 91, 92)                                                                     -- 請求取りまとめ対象外 もしくは 請求取りまとめ済みのもの

EOQ;

        // SQL(各種考慮:請求書)
        if ($billIssueState !=-1) {
            $sql .= " AND po.ConfirmWaitingFlg = " . $billIssueState;
        }

        // SQL(各種考慮:用紙)
        if ($paperType != -1) {
            $sql .= " AND sit.PrintFormBS = " . $paperType;
        }

        // SQL(各種考慮:売上確定日)
        if ($salesDicisionDate != ''  && IsValidFormatDate($salesDicisionDate)) {
            $sql .= " AND po.ClearConditionDate <= " . CoatStr($salesDicisionDate);
        }

        // SQL(各種考慮:OEM)
        if ($oem > 0) {
            $sql .= " AND o.OemId = " . $oem;
        }

        // SQL(各種考慮:加盟店ID)
        if ($entid != '') {
            $sql .= " AND e.LoginId like '%" . BaseUtility::escapeWildcard($entid) . "' ";
        }

        // SQL(各種考慮:加盟店名)
        if ($entnm != '') {
            $sql .= " AND e.EnterpriseNameKj like '%" . BaseUtility::escapeWildcard($entnm) . "%' ";
        }

        // SQL(各種考慮:注文ID)
        if ($odrid != '') {
            $sql .= " AND o.OrderId like '%" . BaseUtility::escapeWildcard($odrid) . "' ";
        }

        $sql .= " ORDER BY c.PostalCode, OrderId ";

        // SQL実行
        $ri = $this->app->dbAdapter->query($sql)->execute(null);

        $datas = ResultInterfaceToArray($ri);

        // count関数対策
        $datasCnt = 0;
        if(!empty($datas)) {
            $datasCnt = count($datas);
        }

        $combinedCnlFlg = false;
        $this->initSiteCache();
        for ($i = 0 ; $i < $datasCnt ; $i++) {
            // 住所は先頭8文字までを表示
            $datas[$i]["UnitingAddress"] = mb_substr($datas[$i]["UnitingAddress"], 0, 8, 'UTF-8');
            $datas[$i] = array_merge($datas[$i], $this->getSiteInfo($datas[$i]['SiteId']));
            // キャンセル未承認の取りまとめデータの有無フラグ
            if (intval($datas[$i]["CombinedCnlCnt"]) > 0) {
                $combinedCnlFlg = true;
            }
        }

        // count関数対策
        $datasCnt = 0;
        if(!empty($datas)) {
            $datasCnt = count($datas);
        }

        // [paging] ページャ初期化
        $pager = new CoralPager($datasCnt, $ipp);
        // [paging] 指定ページを補正
        if( $current_page > $pager->getTotalPage() ) $current_page = $pager->getTotalPage();
        // [paging] 対象リストをページング情報に基づいて対象リストをスライス
        if( $datasCnt > 0 ) $datas = array_slice( $datas, $pager->getStartIndex( $current_page ), $ipp );
        // [paging] ページングナビゲーション情報
        $page_links = array( 'base' => "rwclaim/list/billIssueState/" . f_e($billIssueState) . '/paperType/' . f_e($paperType)
         . '/salesDicisionDate/' . f_e($salesDicisionDate) . '/oem/' . f_e($oem) . '/entid/' . $entid . '/entnm/' . $entnm . '/odrid/' . $odrid . '/page' );
        $page_links['prev'] = $page_links['base'] . '/' . ( $current_page - 1 );
        $page_links['next'] = $page_links['base'] . '/' . ( $current_page + 1 );
        // [paging] ページング関連の情報をビューへアサイン
        $this->view->assign( 'current_page', $current_page );
        $this->view->assign( 'pager', $pager );
        $this->view->assign( 'page_links', $page_links );

        $this->view->assign("list", $datas);
        $this->view->assign("cnt", $i);
        $this->view->assign("billIssueStateTag",BaseHtmlUtils::SelectTag('billIssueState',$codeMaster->getMasterCodes(78, array(-1 => '----------')),$billIssueState));
        $this->view->assign("paperTypeTag",BaseHtmlUtils::SelectTag('paperType',$codeMaster->getMasterCodes(79, array(-1 => '----------')),$paperType));
        $this->view->assign("salesDicisionDate", $salesDicisionDate);
        $mdloem = new TableOem($this->app->dbAdapter);
        $this->view->assign("oemTag",BaseHtmlUtils::SelectTag('oem', $mdloem->getOemIdList(), $oem));
        $this->view->assign("entid", $entid);
        $this->view->assign("entnm", $entnm);
        $this->view->assign("odrid", $odrid);
        $this->view->assign("combinedCnlFlg", $combinedCnlFlg);

        // CSVダウンロード用　検索条件をJSON形式で保持
        $searchKey = array (
            'billIssueState' => $billIssueState,
            'paperType' => $paperType,
            'salesDicisionDate' => $salesDicisionDate,
            'oem' => $oem,
            'entid' => $entid,
            'entnm' => $entnm,
            'odrid' => $odrid,
        );
        $searchKeyJson = Json::encode($searchKey);
        $this->view->assign("searchKey", $searchKeyJson);

        return $this->view;
	}

// Del By Takemasa(NDC) 20150326 Stt 機能廃止
// 	/**
// 	 * 初回請求待ち（印刷完了待ち）のリストを表示する。
// 	 */
// 	public function list2Action()
// Del By Takemasa(NDC) 20150326 End 機能廃止

	/**
	 * 更新処理
	 */
	public function upAction()
	{
        ini_set('max_execution_time', 0);		// 実行タイムアウトを無効にしておく（2014.11.13 eda）

        $params = $this->getParams();

        // ユーザーIDの取得
        $obj = new \models\Table\TableUser($this->app->dbAdapter);
        $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

        // 請求関連処理SQL
        $stm = $this->app->dbAdapter->query($this->getBaseP_ClaimControl());

        // SQL実行結果取得用のSQL
        $getretvalsql = " SELECT @po_ret_sts AS po_ret_sts, @po_ret_errcd AS po_ret_errcd, @po_ret_sqlcd AS po_ret_sqlcd, @po_ret_msg AS po_ret_msg ";

        $i = 0;
        $transferCount = 0;
        $errorCount = 0;

        $mdlsit = new \models\Table\TableSite($this->app->dbAdapter);
        $mdlo = new TableOrder($this->app->dbAdapter);
        $mdlch = new TableClaimHistory($this->app->dbAdapter);

        //---------------------------------
        // 印刷済に更新
        //---------------------------------
        while (isset($params['OrderSeq' . $i])) {
            if (!isset($params['chkWaitDecision' . $i])) { $i++; continue; }

            $oseq = $params['OrderSeq' . $i];
            $sql = <<<EOQ
SELECT COUNT(*) AS cnt
  FROM T_Order o
 WHERE EXISTS(SELECT * FROM T_Order t WHERE t.P_OrderSeq = o.OrderSeq AND t.Cnl_Status = 0)
   AND o.OrderSeq = :OrderSeq
EOQ;
            $prm = array(
                    ':OrderSeq' => $oseq,
            );
            $ret = $this->app->dbAdapter->query($sql)->execute($prm)->current()['cnt'];
            if ($ret == 0) {
                // 有効な注文がいない場合はスキップ
                $i++;
                continue;
            }

            // 請求履歴が有効かどうか判定
            if ($mdlch->getReservedCount($oseq) <= 0) {
                // 処理をスキップ
                $i++;
                continue;
            }

            // 請求履歴データを取得
            $data = $mdlch->findClaimHistory(array( 'PrintedFlg' => 0, 'ValidFlg' => 1, 'OrderSeq' => $oseq ))->current();

            // 請求関連処理呼び出し用パラメータの設定
            $prm = array(
                    ':pi_history_seq'   => $data['Seq'],
                    ':pi_button_flg'       => 1,
                    ':pi_user_id'          => $userId,
            );

            try {
                //トランザクション開始
                $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();

                $ri = $stm->execute($prm);

                // SQL実行例外なしもエラー戻り値の時は例外をｽﾛｰ
                $retval = $this->app->dbAdapter->query($getretvalsql)->execute(null)->current();
                if ($retval['po_ret_sts'] != 0) {
                    throw new \Exception($retval['po_ret_msg']);
                }

                // 注文履歴登録用に親注文Seqから子注文Seqを再取得する。
                $sql = <<<EOQ
                    SELECT  OrderSeq
                    FROM    T_Order
                    WHERE   P_OrderSeq = :P_OrderSeq
                    AND     Cnl_Status = 0
                    ;
EOQ;

                $ri = $this->app->dbAdapter->query($sql)->execute(array(':P_OrderSeq' => $oseq));
                $oseqs = ResultInterfaceToArray($ri);

                // 注文履歴へ登録
                $history = new CoralHistoryOrder($this->app->dbAdapter);
                // 取得できた件数分ループする
                foreach ($oseqs as $row) {
                    // 備考に保存
                    $mdlo->appendPrintedInfoToOemNote($row["OrderSeq"]);
                    // 注文履歴登録
                    $history->InsOrderHistory($row["OrderSeq"], 42, $userId);
                }

                $transferCount++;

                // 請求履歴．印刷ステータス(PrintedStatus)を"9"(印刷済み)に更新する
                $this->app->dbAdapter->query(" UPDATE T_ClaimHistory SET PrintedStatus = 9 WHERE Seq = :Seq ")->execute(array(':Seq' => $data['Seq']));

                $this->app->dbAdapter->getDriver()->getConnection()->commit();

            }
            catch (\Exception $e) {
                $this->app->dbAdapter->getDriver()->getConnection()->rollback();
                $errorCount++;
            }

            $i++;
        }

        $this->view->assign('transferCount', $transferCount);
        $this->view->assign('errorCount', $errorCount);

        return $this->view;
	}

    /**
     * 画面情報をセッションに保存
     * 印刷ボタンで使用されているため、今後の改修は不要
     */
    public function jobparamsetAction()
    {
        // セッションに情報をセットする
        unset($_SESSION[self::SESSION_JOB_PARAMS]);
        $_SESSION[self::SESSION_JOB_PARAMS] = $this->getParams();

        // ジョブ転送
        $status = 1;
        $message = "";
        $ceSeqs = array();
        try {
            $this->jobTransfer('chkPrint', $ceSeqs);

            if (!empty($ceSeqs)) {
                // 請求エラーがある場合
                $status = 2;
                $message = $this->getStatusCaption($ceSeqs);
            }
        } catch( \Exception $e) {
            $status = 9;
            $message = $e->getMessage() . "\n";
            $message = $e->getTraceAsString() . "\n";
        }

        echo \Zend\Json\Json::encode(array('status' => $status, 'message' => $message));

        return $this->response;
    }

    /**
     * 画面情報をセッションに保存(CSV)
     */
    public function jobparamsetcsvAction()
    {
        // セッションクリア
        unset($_SESSION[self::SESSION_JOB_PARAMS]);

        // パラメータから検索条件取得
        $params = $this->getParams();
        $searchKeyJson = $params['searchKey'];
        $searchKey = Json::decode($searchKeyJson, Json::TYPE_ARRAY);

        $billIssueState = $searchKey['billIssueState'];
        $paperType = $searchKey['paperType'];
        $salesDicisionDate = $searchKey['salesDicisionDate'];
        $oem = $searchKey['oem'];
        $entid = $searchKey['entid'];
        $entnm = $searchKey['entnm'];
        $odrid = $searchKey['odrid'];

        // SQL(基本)
        // ※検索SQLと同期をとること！！
        //   ただし不要な項目の取得はしないこと
        $sql =<<<EOQ
SELECT DISTINCT o.OrderSeq, o.SiteId
FROM   T_Order o
       INNER JOIN T_Enterprise e
               ON e.EnterpriseId = o.EnterpriseId
       INNER JOIN T_Site sit
               ON sit.SiteId = o.SiteId
       INNER JOIN T_Customer c
               ON c.OrderSeq = o.OrderSeq
       INNER JOIN ( SELECT t.P_OrderSeq
                          ,MAX(IFNULL(t.LetterClaimStopFlg, 0))   AS LetterClaimStopFlg        -- 最大のストップフラグが1の場合＝ストップしたい注文あり
                          ,MAX(t.ConfirmWaitingFlg)               AS ConfirmWaitingFlg         -- 最大の確定待ちフラグが1の場合＝確定待ち注文あり
                          ,MAX(p.ClearConditionDate)              AS ClearConditionDate        -- 立替条件クリア日
                          ,MIN(t.DataStatus)                      AS DataStatus                -- データステータス
                          ,SUM(t.UseAmount)                       AS UseAmount                 -- 利用額合計
                      FROM T_Order t
                           INNER JOIN T_PayingAndSales p
                                   ON p.OrderSeq = t.OrderSeq
                     WHERE t.Cnl_Status = 0
                       AND t.DataStatus IN (41, 51)
                    GROUP BY t.P_OrderSeq
                  ) po
               ON po.P_OrderSeq = o.OrderSeq
       LEFT OUTER JOIN T_ClaimControl clm
                    ON clm.OrderSeq = o.OrderSeq
       LEFT OUTER JOIN T_ClaimHistory ch ON (ch.OrderSeq = o.OrderSeq)

WHERE  1 = 1
AND    (
           ( po.DataStatus =  41  AND IFNULL(e.SelfBillingMode, 0) <= 0                                                         ) -- 別送加盟店
        OR ( po.DataStatus =  41  AND IFNULL(e.SelfBillingMode, 0) >  0 AND sit.SelfBillingFlg = 0                              ) -- 同梱加盟店、別送サイト
        OR ( po.DataStatus =  41  AND IFNULL(e.SelfBillingMode, 0) >  0 AND sit.SelfBillingFlg = 1 AND o.ClaimSendingClass = 12 ) -- 同梱加盟店、同梱サイト、別送に送る指示あり
        OR ( po.DataStatus = 51  AND clm.ReissueClass <> 0                               )                                       -- 初回請求書再発行の指示あり
       )
AND    po.LetterClaimStopFlg = 0                                                                                                 -- 紙請求ストップフラグが１件も立っていないもの
AND    IFNULL(o.CombinedClaimTargetStatus, 0) IN (0, 91, 92)                                                                     -- 請求取りまとめ対象外 もしくは 請求取りまとめ済みのもの

EOQ;

        // SQL(各種考慮:請求書)
        if ($billIssueState !=-1 && is_numeric($billIssueState)) {
            $sql .= " AND po.ConfirmWaitingFlg = " . $billIssueState;
        }

        // SQL(各種考慮:用紙)
        if ($paperType != -1 && is_numeric($paperType)) {
            $sql .= " AND sit.PrintFormBS = " . $paperType;
        }

        // SQL(各種考慮:売上確定日)
        if ($salesDicisionDate != ''  && IsValidFormatDate($salesDicisionDate)) {
            $sql .= " AND po.ClearConditionDate <= " . CoatStr($salesDicisionDate);
        }

        // SQL(各種考慮:OEM)
        if ($oem > 0 && is_numeric($oem)) {
            $sql .= " AND o.OemId = " . $oem;
        }

        // SQL(各種考慮:加盟店ID)
        if ($entid != '') {
            $sql .= " AND e.LoginId like '%" . BaseUtility::escapeWildcard($entid) . "' ";
        }

        // SQL(各種考慮:加盟店名)
        if ($entnm != '') {
            $sql .= " AND e.EnterpriseNameKj like '%" . BaseUtility::escapeWildcard($entnm) . "%' ";
        }

        // SQL(各種考慮:注文ID)
        if ($odrid != '') {
            $sql .= " AND o.OrderId like '%" . BaseUtility::escapeWildcard($odrid) . "' ";
        }

        $_SESSION[self::SESSION_JOB_PARAMS2] =$sql;// CSV出力の為の基礎抽出SQLを保管

        $sql .= " ORDER BY c.PostalCode, o.OrderId ";

        // SQL実行
        $ri = $this->app->dbAdapter->query($sql)->execute(null);

        $datas = ResultInterfaceToArray($ri);

        // 結果をARRAY化
        $i = 0;
        $list = array();
        foreach ($datas as $data) {
            $list['OrderSeq'.$i] = $data['OrderSeq'];
            $list['SiteId'.$i] = $data['SiteId'];
            $list['chkCsv'.$i] = 1;
            $i++;
        }

        // 検索条件
        $list['searchKey'] = $params['searchKey'];

        // セッションに情報をセットする
        $_SESSION[self::SESSION_JOB_PARAMS] = $list;

        // ジョブ転送
        $status = 1;
        $message = "";
        $ceSeqs = array();

        try {
            $this->jobTransfer('chkCsv', $ceSeqs);

            if (!empty($ceSeqs)) {
                // 請求エラーがある場合
                $status = 2;
                $message = $this->getStatusCaption($ceSeqs);
            }
        } catch( \Exception $e) {
            $status = 9;
            $message = $e->getMessage() . "\n";
            $message = $e->getTraceAsString() . "\n";
        }

        echo \Zend\Json\Json::encode(array('status' => $status, 'message' => $message));

        return $this->response;
    }

    /**
     * CSV出力処理
     */
    public function csvoutputAction()
    {
        // ZIPファイル作成
        $zip = new \ZipArchive();

        // 出力時刻
        $formatNowStr = date('YmdHis');

        // 出力ファイル名
        $outFileName= ('Claim_' . $formatNowStr . '.zip');

        // TEMP領域作成
        $tmpFilePath = tempnam( sys_get_temp_dir(), 'tmp' );

        // ZIPファイルオープン
        $zip->open( $tmpFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE );

        $unlinkList = array();

        // 検索条件：OEMを取得
        $searchKeyJson = $_SESSION[self::SESSION_JOB_PARAMS]['searchKey'];
        $searchKey = Json::decode($searchKeyJson, Json::TYPE_ARRAY);
        $oemId = $searchKey['oem'];

        // OrderSeq配列の生成
        $aryOrderSeq = array();
        for ($i=0; $i<1000000; $i++) {
            if (!isset($_SESSION[self::SESSION_JOB_PARAMS]['OrderSeq'.$i])) { break; }

            $aryOrderSeq[] = $_SESSION[self::SESSION_JOB_PARAMS]['OrderSeq'.$i];
        }
        $inphrase_oseqs = (!empty($aryOrderSeq)) ? implode(',', $aryOrderSeq) : -1;

        // 個別出力の加盟店取得
        $ri_cio = $this->app->dbAdapter->query(" SELECT e.OemId, e.EnterpriseId FROM T_Enterprise e LEFT JOIN T_Oem o ON e.OemId = o.OemId WHERE e.ClaimIndividualOutputFlg = 1 AND (e.OemId = 0 OR o.ValidFlg = 1) AND e.EnterpriseId != (SELECT PropValue FROM T_SystemProperty WHERE Module = '[DEFAULT]' AND Category = 'systeminfo' AND Name = 'NTTFEnterpriseId') ")->execute(null);

        // 有効なOEM(含むCB)取得
        $ri_oem = $this->app->dbAdapter->query(" SELECT 0 AS OemId UNION ALL SELECT OemId FROM T_Oem WHERE ValidFlg = 1 ")->execute(null);

        // ================= 個別出力 =================
        foreach ($ri_cio as $row_cio) {

            // 検索条件OEMが有効な時は、対象OEM以外の出力は行わない
            if ($oemId > 0 && $oemId != $row_cio['OemId']) { continue; }

            // 初回請求用紙モード[T_Site.FirstClaimLayoutMode]毎処理(0：通常／1：封書用紙)
            for ($claimLayoutMode=0; $claimLayoutMode<2; $claimLayoutMode++) {

                $filename = $this->csvDownloadByCio($row_cio['OemId'], $row_cio['EnterpriseId'], $claimLayoutMode, $formatNowStr, $tmpFilePath, $inphrase_oseqs);
                if ($filename != '' ) {
                    $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
                    $addFilePath = file_get_contents( $filename );
                    $zip->addFromString( $pathcutfilename, $addFilePath );
                    $unlinkList[] = $filename;
                }
            }
        }

        // ================= OEM別出力 =================
        foreach ($ri_oem as $row_oem) {

            // 検索条件OEMが有効な時は、対象OEM以外の出力は行わない
            if ($oemId > 0 && $oemId != $row_oem['OemId']) { continue; }

            // 初回請求用紙モード[T_Site.FirstClaimLayoutMode]毎処理(0：通常／1：封書用紙)
            for ($claimLayoutMode=0; $claimLayoutMode<2; $claimLayoutMode++) {
                // クレジット決済利用しない
                $filename = $this->csvDownloadByOem($row_oem['OemId'], $claimLayoutMode, $formatNowStr, $tmpFilePath, $inphrase_oseqs, false);
                if ($filename != '' ) {
                    $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
                    $addFilePath = file_get_contents( $filename );
                    $zip->addFromString( $pathcutfilename, $addFilePath );
                    $unlinkList[] = $filename;
                }
                // クレジット決済利用する
                if (is_null ( $row_oem ['OemId'] ) || preg_match ( "/^[0]{1}/", $row_oem ['OemId'] )) {
                    $filename = $this->csvDownloadByOem($row_oem['OemId'], $claimLayoutMode, $formatNowStr, $tmpFilePath, $inphrase_oseqs, true);
                    if ($filename != '' ) {
                        $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
                        $addFilePath = file_get_contents( $filename );
                        $zip->addFromString( $pathcutfilename, $addFilePath );
                        $unlinkList[] = $filename;
                    }
                }
            }
        }

        // セッションクリア
        unset($_SESSION[self::SESSION_JOB_PARAMS]);
        unset($_SESSION[self::SESSION_JOB_PARAMS2]);

        // ZIPファイルクローズ
        $zip->close();

        // ヘッダ
        header( 'Content-Type: application/octet-stream; name="' . $outFileName . '"' );
        header( 'Content-Disposition: attachment; filename="' . $outFileName . '"' );
        header( 'Content-Length: ' . filesize( $tmpFilePath ) );

        // 出力
        echo readfile( $tmpFilePath );

        // count関数対策
        $unlinkListLen = 0;
        if(!empty($unlinkList)) {
            $unlinkListLen = count($unlinkList);
        }

        // TEMP領域削除
        for ($i=0; $i<$unlinkListLen; $i++) {
            unlink( $unlinkList[$i] );
        }
        unlink( $tmpFilePath );
        die();
    }

    /**
     * (Ajax)印刷処理
     */
    public function printAction()
    {
        // PDF出力
        $pdf = $this->pdfDownload();

        // セッションクリア
        unset($_SESSION[self::SESSION_JOB_PARAMS]);

        return $pdf;
    }

    /**
     * CSVダウンロード(個別出力加盟店単位)
     *
     * @param int $oemId OemID
     * @param int $enterpriseId EnterpriseID
     * @param int $claimLayoutMode 請求用紙モード(0：通常／1：封書用紙)
     * @param string $formatNowStr 書式化年月日時分秒
     * @param string $tmpFilePath TEMP領域
     * @param string $inphrase_oseqs 注文SEQのIN句
     * @return ファイル名 ※出力が行われなかった場合は''を戻す
     */
    protected function csvDownloadByCio($oemId, $enterpriseId, $claimLayoutMode, $formatNowStr, $tmpFilePath, $inphrase_oseqs) {

        // ユーザーIDの取得
        $obj = new \models\Table\TableUser($this->app->dbAdapter);
        $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

        //---------------------------------------
        // 出力対象の存在チェック(OemID＋請求用紙モード)
        $sql  = $_SESSION[self::SESSION_JOB_PARAMS2];
        $sql  = str_replace("AND po.ConfirmWaitingFlg = 0", "", $sql); // 検索条件で唯一継承してはならないものの無効化
        $sql .= " AND    o.OrderSeq IN (" . $inphrase_oseqs . ") ";
        $sql .= " AND    IFNULL(o.OemId, 0) = :OemId ";
        $sql .= " AND    sit.FirstClaimLayoutMode = :ClaimLayoutMode ";
        $sql .= " AND    o.EnterpriseId = " . $enterpriseId . " ";
        $sql .= " AND    ch.PrintedFlg = 0 AND ch.ValidFlg = 1 ";
        $sql .= " ORDER BY c.PostalCode, o.OrderId ";

        $ri = $this->app->dbAdapter->query($sql)->execute(array(':OemId' => $oemId, ':ClaimLayoutMode' => $claimLayoutMode));
        if (!($ri->count() > 0)) {
            return ''; // 出力対象件数が0の場合は以降処理不要
        }

        //---------------------------------------
        // 出力ファイル名生成
        // (プレフィックス1)
        $keycode = (($claimLayoutMode == 1) ? 200 : 100) + (int)$oemId;
        $sql  = " SELECT Class1 FROM M_Code WHERE CodeId = 181 AND KeyCode = :KeyCode ";
        $prefix1 = $this->app->dbAdapter->query($sql)->execute(array(':KeyCode' => $keycode))->current()['Class1'];
        // (プレフィックス2)
        $keycode = ((int)$oemId * 10 + 1);
        $sql  = " SELECT Class2 FROM M_Code WHERE CodeId = 182 AND KeyCode = :KeyCode ";
        $prefix2 = $this->app->dbAdapter->query($sql)->execute(array(':KeyCode' => $keycode))->current()['Class2'];
        // (ファイル名生成)
        $fileName = ($prefix1 . '_' . $prefix2 . '_' . $enterpriseId . '_' . $formatNowStr . '.csv');

        //---------------------------------------
        // Ｅストア考慮(現行互換ＩＦ)
        $estoreFlg = false;
        $estoreItemsCnt = 14;
        if ($oemId > 0) {
            // OEM指定がある場合、Eストアか判定
            $mdlOem = new TableOem($this->app->dbAdapter);
            $oem = $mdlOem->find($oemId)->current();

            if ($oem['OrderIdPrefix'] == 'EA') {
                $estoreFlg = true;
            }
        }

        //---------------------------------------
        // データ抽出と蓄積
        $datas = array();
        foreach ($ri as $ri_row) {

            $data = array();
            $prm = array(':OrderSeq' => $ri_row['OrderSeq']);

            $sql  = ' SELECT c.PostalCode ';
            $sql .= ' ,      c.UnitingAddress ';
            $sql .= ' ,      c.NameKj ';
            $sql .= ' ,      o.OrderId ';
            $sql .= ' ,      DATE_FORMAT(o.ReceiptOrderDate, \'%Y/%m/%d\') AS ReceiptOrderDate ';
            $sql .= ' ,      s.SiteNameKj ';
            $sql .= ' ,      s.Url ';
            $sql .= ' ,      e.ContactPhoneNumber ';
            $sql .= ' ,      ch.ClaimAmount ';
            $sql .= ' ,      DATE_FORMAT(ch.LimitDate, \'%Y/%m/%d\') AS LimitDate ';
            $sql .= ' ,      (CASE WHEN LENGTH(ca.Cv_BarcodeData) > 43 THEN SUBSTRING(ca.Cv_BarcodeData, 1, 43) ';
            $sql .= '              ELSE ca.Cv_BarcodeData ';
            $sql .= '         END) AS Cv_BarcodeData2 ';
            $sql .= ' ,      ch.ClaimFee ';
            $sql .= ' ,      ch.DamageInterestAmount ';
            $sql .= ',       o.Ent_OrderId ';
            $sql .= ' ,      ca.TaxAmount ';
            $sql .= ' ,      ca.Cv_ReceiptAgentName ';
            $sql .= ' ,      ca.Cv_SubscriberName ';
            $sql .= ' ,      ca.Cv_BarcodeData ';
            $sql .= ' ,      ca.Cv_BarcodeString1 ';
            $sql .= ' ,      ca.Cv_BarcodeString2 ';
            $sql .= ' ,      ca.Bk_BankCode ';
            $sql .= ' ,      ca.Bk_BranchCode ';
            $sql .= ' ,      ca.Bk_BankName ';
            $sql .= ' ,      ca.Bk_BranchName ';
            $sql .= ' ,      ca.Bk_DepositClass ';
            $sql .= ' ,      ca.Bk_AccountNumber ';
            $sql .= ' ,      ca.Bk_AccountHolder ';
            $sql .= ' ,      ca.Bk_AccountHolderKn ';
            $sql .= ' ,      ca.Yu_SubscriberName ';
            $sql .= ' ,      ca.Yu_AccountNumber ';
            $sql .= ' ,      ca.Yu_ChargeClass ';
            $sql .= ' ,      ca.Yu_AccountNumber ';
            $sql .= ' ,      ca.Yu_MtOcrCode1 ';
            $sql .= ' ,      ca.Yu_MtOcrCode2 ';
            $sql .= ' ,      c.CorporateName ';
            $sql .= ' ,      c.DivisionName ';
            $sql .= ' ,      c.CpNameKj ';
            $sql .= ' ,      e.PrintEntOrderIdOnClaimFlg ';
            $sql .= ' ,      o.Ent_Note ';
            $sql .= ' FROM   T_Order o INNER JOIN ';
            $sql .= '        T_Customer c ON ( o.OrderSeq = c.OrderSeq ) INNER JOIN ';
            $sql .= '        T_Enterprise e ON ( o.EnterpriseId = e.EnterpriseId ) INNER JOIN ';
            $sql .= '        T_Site s ON ( o.SiteId = s.SiteId ) LEFT OUTER JOIN ';
            $sql .= '        T_ClaimHistory ch ON ( o.OrderSeq = ch.OrderSeq AND ch.PrintedFlg = 0 AND ch.ValidFlg = 1 ) LEFT OUTER JOIN ';
            $sql .= '        T_OemClaimAccountInfo ca ON( ch.Seq = ca.ClaimHistorySeq AND ca.Status = 1 ) ';
            $sql .= ' WHERE  o.OrderSeq = :OrderSeq ';
            $sql .= '   AND  EXISTS (SELECT * FROM T_Order t WHERE t.P_OrderSeq = o.OrderSeq AND t.Cnl_Status = 0) ';

            $data = $this->app->dbAdapter->query( $sql )->execute( $prm )->current();
            if (!$data) { continue; }

            // 請求金額が30万円以上だった場合
            if( $data['ClaimAmount'] >= 300000 ) {
                $data['Cv_BarcodeData'] = $data['Cv_BarcodeData2'] = '収納代行の規約によりコンビニエンスストアで30万円以上のお支払はできません';
                $data['Cv_BarcodeString1'] = '';
                $data['Cv_BarcodeString2'] = '';
            }

            // 任意注文番号非表示の加盟店
            if ($data['PrintEntOrderIdOnClaimFlg'] == 0) {
                $data['Ent_OrderId'] = '';
            }

            // 初回はブランク
            $data['ClaimFee'] = '';
            $data['DamageInterestAmount'] = '';

            // 注文商品
            $sql  = ' SELECT itm.ItemNameKj ';
            $sql .= ' ,      itm.ItemNum ';
            $sql .= ' ,      itm.UnitPrice ';
            $sql .= ' ,      itm.SumMoney ';
            $sql .= ' ,      e.DispDecimalPoint ';  /* 表示用小数点桁数 */
            $sql .= ' FROM   T_Order o INNER JOIN ';
            $sql .= '        T_OrderItems itm ON ( o.OrderSeq = itm.OrderSeq ) ';
            $sql .= '        INNER JOIN T_Enterprise e ON ( e.EnterpriseId = o.EnterpriseId ) ';
            $sql .= ' WHERE  itm.DataClass = 1 AND ';
            $sql .= '        o.P_OrderSeq = :OrderSeq ';
            $sql .= ' AND    o.Cnl_Status = 0 ';
            $sql .= ' AND    itm.ValidFlg = 1 ';
            $sql .= ' ORDER BY OrderItemId ';
            $items = ResultInterfaceToArray( $this->app->dbAdapter->query( $sql )->execute( $prm ) );
            for( $j = 1; $j <= 19; $j++ ) {
                $data['ItemNameKj_' . $j] = isset( $items[$j - 1]['ItemNameKj'] ) ? $items[$j - 1]['ItemNameKj'] : '';
                $data['ItemNum_' . $j] = isset( $items[$j - 1]['ItemNum'] ) ? $items[$j - 1]['ItemNum'] : '';
                if ($data['ItemNum_' . $j] != '') {
                    // [表示用小数点桁数]考慮
                    $data['ItemNum_' . $j] = number_format($data['ItemNum_' . $j], $items[$j - 1]['DispDecimalPoint'], '.', '');
                }
                $data['UnitPrice_' . $j] = isset( $items[$j - 1]['UnitPrice'] ) ? $items[$j - 1]['UnitPrice'] : '';
            }

            // 小計
            $data['TotalItemPrice'] = 0;
            foreach ($items as $row) {
                $data['TotalItemPrice'] += $row['SumMoney'];
            }

            // 送料
            $sql  = ' SELECT SUM( itm.SumMoney ) AS CarriageFee ';
            $sql .= ' FROM   T_Order o INNER JOIN ';
            $sql .= '        T_OrderItems itm ON ( o.OrderSeq = itm.OrderSeq ) ';
            $sql .= ' WHERE  itm.DataClass = 2 AND ';
            $sql .= '        o.P_OrderSeq = :OrderSeq ';
            $sql .= ' AND    o.Cnl_Status = 0 ';
            $data = array_merge( $data, $this->app->dbAdapter->query( $sql )->execute( $prm )->current() );

            // 決済手数料
            $sql  = ' SELECT SUM( itm.SumMoney ) AS ChargeFee ';
            $sql .= ' FROM   T_Order o INNER JOIN ';
            $sql .= '        T_OrderItems itm ON ( o.OrderSeq = itm.OrderSeq ) ';
            $sql .= ' WHERE  itm.DataClass = 3 AND ';
            $sql .= '        o.P_OrderSeq = :OrderSeq ';
            $sql .= ' AND    o.Cnl_Status = 0 ';
            $data = array_merge( $data, $this->app->dbAdapter->query( $sql )->execute( $prm )->current() );

            // 消費税(外税額レコード確認)
            $sql  = ' SELECT COUNT(itm.OrderItemId) AS cnt ';
            $sql .= ' FROM   T_Order o INNER JOIN ';
            $sql .= '        T_OrderItems itm ON ( o.OrderSeq = itm.OrderSeq ) ';
            $sql .= ' WHERE  itm.DataClass = 4 AND ';
            $sql .= '        o.P_OrderSeq = :OrderSeq ';
            $sql .= ' AND    o.Cnl_Status = 0 ';
            $data['TaxClass'] = ((int)$this->app->dbAdapter->query( $sql )->execute( $prm )->current()['cnt'] > 0) ? 1 : 0;

            // 請求回数
            $data['ReIssueCount'] = 0;

            // マイページログインパスワード
            $row_mypageorder = $this->app->dbAdapter->query(" SELECT Token FROM T_MypageOrder WHERE OrderSeq = :OrderSeq ORDER BY Seq DESC LIMIT 1 "
            )->execute(array(':OrderSeq' => $ri_row['OrderSeq']))->current();
            $data['MypageToken'] = ($row_mypageorder) ? $row_mypageorder['Token'] : '';

            // 商品合計数
            // count関数対策
            if(empty($items)) {
                $data['ItemsCount'] = 0;
            } else {
                $data['ItemsCount'] = count($items);
            }

            // Eストアの場合のみ
            if ($estoreFlg) {
                if ($data['ItemsCount'] > $estoreItemsCnt) {
                    // 商品明細数が14を超えている場合、14明細目の内容を変更
                    $j = 0;
                    $etcSum = 0;
                    foreach ($items as $row) {
                        $j++;
                        if ($j >= $estoreItemsCnt) {
                            $etcSum += $row['SumMoney'];
                        }
                    }
                    $data['ItemNameKj_' . $estoreItemsCnt] = 'その他' . ($data['ItemsCount'] - $estoreItemsCnt + 1) . '点';
                    $data['ItemNum_' . $estoreItemsCnt] = 1;
                    $data['UnitPrice_' . $estoreItemsCnt] = $etcSum;
                }
                for( $j = ($estoreItemsCnt + 1); $j <= 19; $j++ ) {
                    $data['ItemNameKj_' . $j] = '';
                    $data['ItemNum_' . $j] = '';
                    $data['UnitPrice_' . $j] = '';
                }
            }

            // 個別出力加盟店
            $tempIdPw = explode("ID:",$data['Ent_Note']);
            $tempId = explode("/",$tempIdPw[1]);
            $data['FreeColumn1'] = $tempId[0];
            $tempIdPw = explode("PW:",$data['Ent_Note']);
            $tempPw = explode("/",$tempIdPw[1]);
            $data['FreeColumn2'] = $tempPw[0];

            // 請求書CSV対応
            // ・二重引用符全角の二重引用符に置換
            // ・改行記号（CRFL、CR、LF）は半角スペースに置換
            // ・フォームフィード文字および垂直タブ文字（ASCII：0x0B）は除去
            // ・タブ文字は半角スペースに置換
            $search  = array('"'    , "\r\n"   , "\r"  , "\n"  , "\f"  , "\v" , "\t");
            $replace = array('”'   , ' '      , ' '   , ' '   , ''    , ''   , ' ');
            $data = str_replace($search, $replace, $data);

            // 法人名が入力されており、担当者名がブランクの場合は、「担当者名」へ購入者名を出力する
            if ((nvl($data['CorporateName'],'') != '') && nvl($data['CpNameKj'],'') == '') {
                $data['CpNameKj'] = $data['NameKj'];
            }
            // 法人名が入力されている場合、「顧客氏名」は出力しない
            if ((nvl($data['CorporateName'],'') != '')) {
                $data['NameKj'] = '';
            }

            $datas[] = $data;

            // 出力した請求履歴データに対する更新処理
            $sql  = " UPDATE T_ClaimHistory ";
            $sql .= " SET    PrintedStatus = 2 ";
            $sql .= " ,      UpdateId = :UpdateId ";
            $sql .= " ,      UpdateDate = :UpdateDate ";
            $sql .= " WHERE  OrderSeq = :OrderSeq ";
            $sql .= " AND    PrintedFlg = 0 ";
            $sql .= " AND    ValidFlg = 1 ";

            $this->app->dbAdapter->query($sql)->execute(array(
                    ':UpdateId' => $userId,
                    ':UpdateDate' => date('Y-m-d H:i:s'),
                    ':OrderSeq' => $ri_row['OrderSeq']
            ));
        }

        //---------------------------------------
        // CSV出力処理
        $templateId = 'CKI04045_1'; // 請求書発行（初回）
        $templateClass = 0;
        $seq = 0;
        $templatePattern = 0;
        $tmpFileName = $tmpFilePath . $fileName;

        $logicTemplate = new LogicTemplate( $this->app->dbAdapter );
        $result = $logicTemplate->convertArraytoFile( $datas, $tmpFileName, $templateId, $templateClass, $seq, $templatePattern );
        if( $result == false ) {
            throw new \Exception( $logicTemplate->getErrorMessage() );
        }

        return $tmpFileName;
    }

   /**
     * CSVダウンロード(OEM単位)
     *
     * @param int $oemId OemID
     * @param int $claimLayoutMode 請求用紙モード(0：通常／1：封書用紙)
     * @param string $formatNowStr 書式化年月日時分秒
     * @param string $tmpFilePath TEMP領域
     * @param string $inphrase_oseqs 注文SEQのIN句
     * @param boolean $paymentAfterArrivalFlg 届いてから決済利用フラグ
     * @return ファイル名 ※出力が行われなかった場合は''を戻す
     */
    protected function csvDownloadByOem($oemId, $claimLayoutMode, $formatNowStr, $tmpFilePath, $inphrase_oseqs, $paymentAfterArrivalFlg) {

        // ユーザーIDの取得
        $obj = new \models\Table\TableUser($this->app->dbAdapter);
        $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

        $mdlsp = new TableSystemProperty($this->app->dbAdapter);
        $ntteid = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'NTTFEnterpriseId');

        //---------------------------------------
        // 出力対象の存在チェック(OemID＋請求用紙モード)
        $sql  = $_SESSION[self::SESSION_JOB_PARAMS2];
        $sql  = str_replace("AND po.ConfirmWaitingFlg = 0", "", $sql); // 検索条件で唯一継承してはならないものの無効化
        $sql .= " AND    o.OrderSeq IN (" . $inphrase_oseqs . ") ";
        $sql .= " AND    IFNULL(o.OemId, 0) = :OemId ";
        $sql .= " AND    sit.FirstClaimLayoutMode = :ClaimLayoutMode ";
        // 個別出力しない加盟店のみ
        $sql .= " AND    (e.ClaimIndividualOutputFlg = 0 ";
        $sql .= " OR      e.EnterpriseId = " . $ntteid . " ) ";
        $sql .= " AND    ch.PrintedFlg = 0 AND ch.ValidFlg = 1 ";
        if($paymentAfterArrivalFlg){
            // 届いてから決済利用する
            $sql .= " AND    sit.PaymentAfterArrivalFlg = 1 ";
        } else {
            if (is_null ( $oemId ) || preg_match ( "/^[0]{1}/", $oemId )) {
                // 届いてから決済利用しない
                $sql .= " AND    sit.PaymentAfterArrivalFlg = 0 ";
            }
        }
        $sql .= " ORDER BY c.PostalCode, o.OrderId ";

        $ri = $this->app->dbAdapter->query($sql)->execute(array(':OemId' => $oemId, ':ClaimLayoutMode' => $claimLayoutMode));
        if (!($ri->count() > 0)) {
            return ''; // 出力対象件数が0の場合は以降処理不要
        }

        //---------------------------------------
        // 出力ファイル名生成
        // (プレフィックス1)
        $keycode = (($claimLayoutMode == 1) ? 200 : 100) + (int)$oemId;
        $sql  = " SELECT Class1 FROM M_Code WHERE CodeId = 181 AND KeyCode = :KeyCode ";
        $prefix1 = $this->app->dbAdapter->query($sql)->execute(array(':KeyCode' => $keycode))->current()['Class1'];
        // (プレフィックス2)
        $keycode = ((int)$oemId * 10 + 1);
        $sql  = " SELECT Class2 FROM M_Code WHERE CodeId = 182 AND KeyCode = :KeyCode ";
        $prefix2 = $this->app->dbAdapter->query($sql)->execute(array(':KeyCode' => $keycode))->current()['Class2'];

        // (ファイル名生成)
        if ($paymentAfterArrivalFlg) {
            $fileName = ($prefix1 . '_' . $prefix2 . '_Credit_' . $formatNowStr . '.csv');
        } else {
            $fileName = ($prefix1 . '_' . $prefix2 . '_' . $formatNowStr . '.csv');
        }


        //---------------------------------------
        // Ｅストア考慮(現行互換ＩＦ)
        $estoreFlg = false;
        $estoreItemsCnt = 14;
        if ($oemId > 0) {
            // OEM指定がある場合、Eストアか判定
            $mdlOem = new TableOem($this->app->dbAdapter);
            $oem = $mdlOem->find($oemId)->current();

            if ($oem['OrderIdPrefix'] == 'EA') {
                $estoreFlg = true;
            }
        }

        //---------------------------------------
        // データ抽出と蓄積
        $datas = array();
        foreach ($ri as $ri_row) {

            $data = array();
            $prm = array(':OrderSeq' => $ri_row['OrderSeq']);

            $sql  = ' SELECT c.PostalCode ';
            $sql .= ' ,      c.UnitingAddress ';
            $sql .= ' ,      c.NameKj ';
            $sql .= ' ,      o.OrderId ';
            $sql .= ' ,      DATE_FORMAT(o.ReceiptOrderDate, \'%Y/%m/%d\') AS ReceiptOrderDate ';
            $sql .= ' ,      s.SiteNameKj ';
            $sql .= ' ,      s.Url ';
            $sql .= ' ,      e.ContactPhoneNumber ';
            $sql .= ' ,      ch.ClaimAmount ';
            $sql .= ' ,      DATE_FORMAT(ch.LimitDate, \'%Y/%m/%d\') AS LimitDate ';
            $sql .= ' ,      (CASE WHEN LENGTH(ca.Cv_BarcodeData) > 43 THEN SUBSTRING(ca.Cv_BarcodeData, 1, 43) ';
            $sql .= '              ELSE ca.Cv_BarcodeData ';
            $sql .= '         END) AS Cv_BarcodeData2 ';
            $sql .= ' ,      ch.ClaimFee ';
            $sql .= ' ,      ch.DamageInterestAmount ';
            $sql .= ',       o.Ent_OrderId ';
            $sql .= ' ,      ca.TaxAmount ';
            $sql .= ' ,      ca.Cv_ReceiptAgentName ';
            $sql .= ' ,      ca.Cv_SubscriberName ';
            $sql .= ' ,      ca.Cv_BarcodeData ';
            $sql .= ' ,      ca.Cv_BarcodeString1 ';
            $sql .= ' ,      ca.Cv_BarcodeString2 ';
            $sql .= ' ,      ca.Bk_BankCode ';
            $sql .= ' ,      ca.Bk_BranchCode ';
            $sql .= ' ,      ca.Bk_BankName ';
            $sql .= ' ,      ca.Bk_BranchName ';
            $sql .= ' ,      ca.Bk_DepositClass ';
            $sql .= ' ,      ca.Bk_AccountNumber ';
            $sql .= ' ,      ca.Bk_AccountHolder ';
            $sql .= ' ,      ca.Bk_AccountHolderKn ';
            $sql .= ' ,      ca.Yu_SubscriberName ';
            $sql .= ' ,      ca.Yu_AccountNumber ';
            $sql .= ' ,      ca.Yu_ChargeClass ';
            $sql .= ' ,      ca.Yu_AccountNumber ';
            $sql .= ' ,      ca.Yu_MtOcrCode1 ';
            $sql .= ' ,      ca.Yu_MtOcrCode2 ';
            $sql .= ' ,      c.CorporateName ';
            $sql .= ' ,      c.DivisionName ';
            $sql .= ' ,      c.CpNameKj ';
            $sql .= ' ,      e.PrintEntOrderIdOnClaimFlg ';
            $sql .= ' FROM   T_Order o INNER JOIN ';
            $sql .= '        T_Customer c ON ( o.OrderSeq = c.OrderSeq ) INNER JOIN ';
            $sql .= '        T_Enterprise e ON ( o.EnterpriseId = e.EnterpriseId ) INNER JOIN ';
            $sql .= '        T_Site s ON ( o.SiteId = s.SiteId ) LEFT OUTER JOIN ';
            $sql .= '        T_ClaimHistory ch ON ( o.OrderSeq = ch.OrderSeq AND ch.PrintedFlg = 0 AND ch.ValidFlg = 1 ) LEFT OUTER JOIN ';
            $sql .= '        T_OemClaimAccountInfo ca ON( ch.Seq = ca.ClaimHistorySeq AND ca.Status = 1 ) ';
            $sql .= ' WHERE  o.OrderSeq = :OrderSeq ';
            $sql .= '   AND  EXISTS (SELECT * FROM T_Order t WHERE t.P_OrderSeq = o.OrderSeq AND t.Cnl_Status = 0) ';

            $data = $this->app->dbAdapter->query( $sql )->execute( $prm )->current();
            if (!$data) { continue; }

            // 請求金額が30万円以上だった場合
            if( $data['ClaimAmount'] >= 300000 ) {
                $data['Cv_BarcodeData'] = $data['Cv_BarcodeData2'] = '収納代行の規約によりコンビニエンスストアで30万円以上のお支払はできません';
                $data['Cv_BarcodeString1'] = '';
                $data['Cv_BarcodeString2'] = '';
            }

            // 任意注文番号非表示の加盟店
            if ($data['PrintEntOrderIdOnClaimFlg'] == 0) {
                $data['Ent_OrderId'] = '';
            }

            // 初回はブランク
            $data['ClaimFee'] = '';
            $data['DamageInterestAmount'] = '';

            // 注文商品
            $sql  = ' SELECT itm.ItemNameKj ';
            $sql .= ' ,      itm.ItemNum ';
            $sql .= ' ,      itm.UnitPrice ';
            $sql .= ' ,      itm.SumMoney ';
            $sql .= ' ,      e.DispDecimalPoint ';  /* 表示用小数点桁数 */
            $sql .= ' FROM   T_Order o INNER JOIN ';
            $sql .= '        T_OrderItems itm ON ( o.OrderSeq = itm.OrderSeq ) ';
            $sql .= '        INNER JOIN T_Enterprise e ON ( e.EnterpriseId = o.EnterpriseId ) ';
            $sql .= ' WHERE  itm.DataClass = 1 AND ';
            $sql .= '        o.P_OrderSeq = :OrderSeq ';
            $sql .= ' AND    o.Cnl_Status = 0 ';
            $sql .= ' AND    itm.ValidFlg = 1 ';
            $sql .= ' ORDER BY OrderItemId ';
            $items = ResultInterfaceToArray( $this->app->dbAdapter->query( $sql )->execute( $prm ) );
            for( $j = 1; $j <= 19; $j++ ) {
                $data['ItemNameKj_' . $j] = isset( $items[$j - 1]['ItemNameKj'] ) ? $items[$j - 1]['ItemNameKj'] : '';
                $data['ItemNum_' . $j] = isset( $items[$j - 1]['ItemNum'] ) ? $items[$j - 1]['ItemNum'] : '';
                if ($data['ItemNum_' . $j] != '') {
                    // [表示用小数点桁数]考慮
                    $data['ItemNum_' . $j] = number_format($data['ItemNum_' . $j], $items[$j - 1]['DispDecimalPoint'], '.', '');
                }
                $data['UnitPrice_' . $j] = isset( $items[$j - 1]['UnitPrice'] ) ? $items[$j - 1]['UnitPrice'] : '';
            }

            // 小計
            $data['TotalItemPrice'] = 0;
            foreach ($items as $row) {
                $data['TotalItemPrice'] += $row['SumMoney'];
            }

            // 送料
            $sql  = ' SELECT SUM( itm.SumMoney ) AS CarriageFee ';
            $sql .= ' FROM   T_Order o INNER JOIN ';
            $sql .= '        T_OrderItems itm ON ( o.OrderSeq = itm.OrderSeq ) ';
            $sql .= ' WHERE  itm.DataClass = 2 AND ';
            $sql .= '        o.P_OrderSeq = :OrderSeq ';
            $sql .= ' AND    o.Cnl_Status = 0 ';
            $data = array_merge( $data, $this->app->dbAdapter->query( $sql )->execute( $prm )->current() );

            // 決済手数料
            $sql  = ' SELECT SUM( itm.SumMoney ) AS ChargeFee ';
            $sql .= ' FROM   T_Order o INNER JOIN ';
            $sql .= '        T_OrderItems itm ON ( o.OrderSeq = itm.OrderSeq ) ';
            $sql .= ' WHERE  itm.DataClass = 3 AND ';
            $sql .= '        o.P_OrderSeq = :OrderSeq ';
            $sql .= ' AND    o.Cnl_Status = 0 ';
            $data = array_merge( $data, $this->app->dbAdapter->query( $sql )->execute( $prm )->current() );

            // 消費税(外税額レコード確認)
            $sql  = ' SELECT COUNT(itm.OrderItemId) AS cnt ';
            $sql .= ' FROM   T_Order o INNER JOIN ';
            $sql .= '        T_OrderItems itm ON ( o.OrderSeq = itm.OrderSeq ) ';
            $sql .= ' WHERE  itm.DataClass = 4 AND ';
            $sql .= '        o.P_OrderSeq = :OrderSeq ';
            $sql .= ' AND    o.Cnl_Status = 0 ';
            $data['TaxClass'] = ((int)$this->app->dbAdapter->query( $sql )->execute( $prm )->current()['cnt'] > 0) ? 1 : 0;

            // 請求回数
            $data['ReIssueCount'] = 0;

            // マイページログインパスワード
            $row_mypageorder = $this->app->dbAdapter->query(" SELECT Token FROM T_MypageOrder WHERE OrderSeq = :OrderSeq ORDER BY Seq DESC LIMIT 1 "
            )->execute(array(':OrderSeq' => $ri_row['OrderSeq']))->current();
            $data['MypageToken'] = ($row_mypageorder) ? $row_mypageorder['Token'] : '';

            // 商品合計数
            // count関数対策
            if(empty($items)) {
                $data['ItemsCount'] = 0;
            } else {
                $data['ItemsCount'] = count($items);
            }

            // Eストアの場合のみ
            if ($estoreFlg) {
                if ($data['ItemsCount'] > $estoreItemsCnt) {
                    // 商品明細数が14を超えている場合、14明細目の内容を変更
                    $j = 0;
                    $etcSum = 0;
                    foreach ($items as $row) {
                        $j++;
                        if ($j >= $estoreItemsCnt) {
                            $etcSum += $row['SumMoney'];
                        }
                    }
                    $data['ItemNameKj_' . $estoreItemsCnt] = 'その他' . ($data['ItemsCount'] - $estoreItemsCnt + 1) . '点';
                    $data['ItemNum_' . $estoreItemsCnt] = 1;
                    $data['UnitPrice_' . $estoreItemsCnt] = $etcSum;
                }
                for( $j = ($estoreItemsCnt + 1); $j <= 19; $j++ ) {
                    $data['ItemNameKj_' . $j] = '';
                    $data['ItemNum_' . $j] = '';
                    $data['UnitPrice_' . $j] = '';
                }
            }

            // 請求書CSV対応
            // ・二重引用符全角の二重引用符に置換
            // ・改行記号（CRFL、CR、LF）は半角スペースに置換
            // ・フォームフィード文字および垂直タブ文字（ASCII：0x0B）は除去
            // ・タブ文字は半角スペースに置換
            $search  = array('"'    , "\r\n"   , "\r"  , "\n"  , "\f"  , "\v" , "\t");
            $replace = array('”'   , ' '      , ' '   , ' '   , ''    , ''   , ' ');
            $data = str_replace($search, $replace, $data);

            // 法人名が入力されており、担当者名がブランクの場合は、「担当者名」へ購入者名を出力する
            if ((nvl($data['CorporateName'],'') != '') && nvl($data['CpNameKj'],'') == '') {
                $data['CpNameKj'] = $data['NameKj'];
            }
            // 法人名が入力されている場合、「顧客氏名」は出力しない
            if ((nvl($data['CorporateName'],'') != '')) {
                $data['NameKj'] = '';
            }

            //届いてから決済利用フラグが1:利用するの場合、テンプレート[CKI04047_1]を使用する。
            if ($paymentAfterArrivalFlg) {
                // コードマスターから届いてから決済用にコメントを習得
                $mdlc = new TableCode ( $this->app->dbAdapter );
                $data ['Comment'] = $mdlc->find ( 199, 10 )->current ()['Note'];
            }

            $datas[] = $data;

            // 出力した請求履歴データに対する更新処理
            $sql  = " UPDATE T_ClaimHistory ";
            $sql .= " SET    PrintedStatus = 2 ";
            $sql .= " ,      UpdateId = :UpdateId ";
            $sql .= " ,      UpdateDate = :UpdateDate ";
            $sql .= " WHERE  OrderSeq = :OrderSeq ";
            $sql .= " AND    PrintedFlg = 0 ";
            $sql .= " AND    ValidFlg = 1 ";

            $this->app->dbAdapter->query($sql)->execute(array(
                    ':UpdateId' => $userId,
                    ':UpdateDate' => date('Y-m-d H:i:s'),
                    ':OrderSeq' => $ri_row['OrderSeq']
            ));
        }

        if ($paymentAfterArrivalFlg) {
            $templateId = 'CKI04047_1';
        }else{
            $templateId = "CKI04042_1";
        }

        //---------------------------------------
        // CSV出力処理
        $templateClass = 0;
        $seq = 0;
        $templatePattern = 0;
        $tmpFileName = $tmpFilePath . $fileName;

        $logicTemplate = new LogicTemplate( $this->app->dbAdapter );
        $result = $logicTemplate->convertArraytoFile( $datas, $tmpFileName, $templateId, $templateClass, $seq, $templatePattern );
        if( $result == false ) {
            throw new \Exception( $logicTemplate->getErrorMessage() );
        }

        return $tmpFileName;
    }

    /**
     * 請求関連処理ファンクションの基礎SQL取得。
     *
     * @return 請求関連処理ファンクションの基礎SQL
     */
    protected function getBaseP_ClaimControl() {
        return <<<EOQ
CALL P_ClaimControl(
    :pi_history_seq
,   :pi_button_flg
,   :pi_user_id
,   @po_ret_sts
,   @po_ret_errcd
,   @po_ret_sqlcd
,   @po_ret_msg
    )
EOQ;
    }

    protected function initSiteCache()
    {
        $this->_siteInfoCache = array();
    }

    protected function getSiteInfo($siteId)
    {
        if(!isset($this->_siteInfoCache[$siteId]))
        {
            $siteTable = new \models\Table\TableSite($this->app->dbAdapter);
            $siteInfo = $siteTable->findSite($siteId)->current();
            if($siteInfo)
            {
                $this->_siteInfoCache[$siteId] = array(
                        'SiteNameKj' => $siteInfo['SiteNameKj'],
                        'FirstClaimLayoutMode' => $siteInfo['FirstClaimLayoutMode']
                );
            }
        }
        return $this->_siteInfoCache[$siteId];
    }

    /**
     * SMBCバーチャル口座オープン用のロックアイテムを獲得する
     *
     * @access protected
     * @param array 対象注文の行オブジェクト
     * @return \models\Logic\ThreadPool\LogicThreadPoolItem | null
     */
    protected function getLockItemForSmbcpaAccount($orderRow = null)
    {
        if(!$orderRow) return null;

        $smbcpaTable = new \models\Table\TableSmbcpa($this->app->dbAdapter);
        $smbcpa = $smbcpaTable->findByOemId((int)$orderRow['OemId'])->current();
        if(!$smbcpa) return null;

        $pool = \models\Logic\LogicThreadPool::getPoolForSmbcpaAccountOpen($smbcpa['SmbcpaId'], $this->app->dbAdapter);
        return $pool->openAsSingleton($orderRow['OrderSeq']);
    }

    /**
     * ジョブ転送処理を行う
     * @param string $checkClass チェック種類
     * @return boolean
     */
    protected function jobTransfer($checkClass, &$ceSeqs) {

        $params = $_SESSION[self::SESSION_JOB_PARAMS];

        $mdlch = new TableClaimHistory($this->app->dbAdapter);
        $mdls = new TableSite($this->app->dbAdapter);
        $mdlo = new TableOrder($this->app->dbAdapter);
        $logicmo = new LogicMypageOrder($this->app->dbAdapter);
        $mdlce = new TableClaimError($this->app->dbAdapter);
        $mdlsys = new TableSystemProperty($this->app->dbAdapter);
        $mdloi = new TableOrderItems($this->app->dbAdapter);
        $mdlbc = new TableBusinessCalendar($this->app->dbAdapter);

        // 認証関連
        $authUtil = $this->app->getAuthUtility();

        // ユーザーIDの取得
        $obj = new \models\Table\TableUser($this->app->dbAdapter);
        $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

        // 請求関連処理SQL
        $stm = $this->app->dbAdapter->query($this->getBaseP_ClaimControl());

        // 初回支払期限有効日数を取得
        $validLimitDays1 = $mdlsys->getValue(TableSystemProperty::DEFAULT_MODULE, 'systeminfo', 'ValidLimitDays1');

        $i = 0;
        $transferCount = 0;
        $errorCount = 0;
        $ceSeqs = array();

        while (isset($params['OrderSeq' . $i])) {
            if (!isset($params[$checkClass . $i])) { $i++; continue; }
            $oseq = $params['OrderSeq' . $i];


            // ----------------------------------------
            // チェック処理
            // ----------------------------------------
            // 有効な注文か
$sql = <<<EOQ
SELECT COUNT(*) AS cnt
  FROM T_Order o
 WHERE EXISTS(SELECT * FROM T_Order t WHERE t.P_OrderSeq = o.OrderSeq AND t.Cnl_Status = 0)
   AND o.OrderSeq = :OrderSeq
EOQ;
            $prm = array(
                ':OrderSeq' => $oseq,
            );
            $ret = $this->app->dbAdapter->query($sql)->execute($prm)->current()['cnt'];
            if ($ret == 0) {
                // 有効な注文がいない場合はスキップ
                $i++;
                continue;
            }

            // 請求金額が０円以下か
$sql = <<<EOQ
SELECT SUM(UseAmount) as amt
  FROM T_Order o
 WHERE o.Cnl_Status = 0
   AND o.P_OrderSeq = :OrderSeq
EOQ;
            $prm = array(
                ':OrderSeq' => $oseq,
            );
            $amt = $this->app->dbAdapter->query($sql)->execute($prm)->current()['amt'];
            if ($amt <= 0) {
                $sql = ' SELECT e.CreditTransferFlg,e.AppFormIssueCond,ao.CreditTransferRequestFlg FROM T_Order o LEFT JOIN T_Enterprise e ON o.EnterpriseId=e.EnterpriseId LEFT JOIN AT_Order ao ON o.OrderSeq=ao.OrderSeq WHERE o.OrderSeq = :OrderSeq ';
                $ent = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current();
                if ((($ent['CreditTransferFlg'] == 1) || ($ent['CreditTransferFlg'] == 2) || ($ent['CreditTransferFlg'] == 3)) && (($ent['AppFormIssueCond'] == 0) || ($ent['AppFormIssueCond'] == 2))) {
                    ;
                } else {
                    // ０円以下の場合は請求エラーとする
                    $ceSeqs[] = $mdlce->saveNew(array('OrderSeq' => $oseq, 'ErrorCode' => LogicClaimException::ERR_CODE_0YEN, 'ErrorMsg' => null));
                    $i++;
                    continue;
                }
            }

            // ----------------------------------------
            // ジョブ転送処理
            // ----------------------------------------
            // SMBCバーチャル口座オープン用にロック獲得を試行
            $lockItem = $this->getLockItemForSmbcpaAccount($mdlo->find($oseq)->current());

            // ジョブ転送中か
            if ($mdlch->getReservedCount($oseq) > 0) {
                try {
                    if($lockItem) {
                        $lockItem->terminate();
                    }
                } catch (\Exception $err) { ; }
                // ジョブ転送中のデータがいる場合はスキップ
                $i++;
                continue;
            }

            try {
                //トランザクション開始
                $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();

                $taxRate = $mdlsys->getTaxRateAt(date('Y-m-d'));

                // 注文商品の更新
                $taxrateData = array(
                        'TaxRate' => $taxRate, // 消費税率
                        'UpdateId' => $userId, // 更新者
                );
                if(date('Y-m-d') > '2019-09-30'){
                    $mdloi->updateTaxrate($taxrateData,$oseq);
                } else {
                    $mdloi->updateTaxrateBefore($taxrateData,$oseq);
                }

                // 請求履歴の論理削除（初回請求書再発行のときのみ有効）
                $sql = " UPDATE T_ClaimHistory SET ValidFlg = 0 , UpdateId = :UpdateId , UpdateDate = :UpdateDate WHERE OrderSeq = :OrderSeq AND ValidFlg = 1 ";
                $prm = array(
                        ':OrderSeq' => $oseq,
                        ':UpdateId' => $userId,
                        ':UpdateDate' => date('Y-m-d H:i:s'),
                );
                $this->app->dbAdapter->query($sql)->execute($prm);

                // 請求金額の再取得
                //原則画面と同じになるが、一部キャンセルされた場合を想定
$sql = <<<EOQ
SELECT SUM(UseAmount) AS UseAmount
  FROM T_Order o
 WHERE o.Cnl_Status = 0
   AND o.P_OrderSeq = :OrderSeq
EOQ;
                $prm = array(
                        ':OrderSeq' => $oseq,
                );
                $useAmount = $this->app->dbAdapter->query($sql)->execute($prm)->current()['UseAmount'];


                $list = $this->listGetFromDB($oseq);

                $mdle = new TableEnterprise($this->app->dbAdapter);
                $mdlcc = new TableClaimControl($this->app->dbAdapter);
                $ent = $mdle->find($list['EnterpriseId'])->current();
                $ccCnt = $mdlcc->findClaim(array('OrderSeq' => $oseq))->current();

                // 口座振替申込み区分>0 の注文 かつ 加盟店顧客.申込みステータス=2（完了）の注文の場合
                $limitDate = '';
                if( ($list['CreditTransferRequestFlg'] > 0) && ($list['RequestStatus'] == 2) ) {
                    $mdle = new TableEnterprise($this->app->dbAdapter);
                    $mdlos = new TableOrderSummary($this->app->dbAdapter);
                    $lgc = new LogicCreditTransfer($this->app->dbAdapter);

                    $ent = $mdle->find($list['EnterpriseId'])->current();
                    $summary = $mdlos->findByOrderSeq($oseq)->current();
                    $limitDates = $lgc->getCreditTransferLimitDay($summary['Deli_JournalIncDate']);
                    $limitDate = $limitDates[$ent['CreditTransferFlg']];
                } else {
                    // 支払期限
                    $limitDate = $mdls->getLimitDate($params['SiteId' . $i], $this->app->business['pay']['limitdays']);
                }

                // 初回再発行、且つ、加盟店.初回再発行の支払期限がONの場合は初回請求書の期限日を設定する
                if (($ent['FirstReClaimLmitDateFlg'] == 1) && (!empty($ccCnt)) ) {
                    $limitDate = $ccCnt['F_LimitDate'];
                }

                // 有効期限日数を算出
                $validLimitDate = date('Y-m-d', strtotime("$validLimitDays1 day"));
                if ((strtotime($limitDate) < strtotime($validLimitDate)) && ($useAmount > 0)) {
                    $mdle = new TableEnterprise($this->app->dbAdapter);
                    $mdlcc = new TableClaimControl($this->app->dbAdapter);
                    $ent = $mdle->find($list['EnterpriseId'])->current();
                    $ccCnt = $mdlcc->findClaim(array('OrderSeq' => $oseq))->current();

                    if (($ent['FirstReClaimLmitDateFlg'] == 1) && (!empty($ccCnt))) {
                        // 初回再発行、且つ、加盟店.初回再発行の支払期限がONの場合は請求書を発行する
                        $limitDate = $ccCnt['F_LimitDate'];
                    } else {
                        $this->app->dbAdapter->getDriver()->getConnection()->rollback();
                        // 支払期限日数が有効期限未満の場合は請求エラーとする
                        $ceSeqs[] = $mdlce->saveNew(array('OrderSeq' => $oseq, 'ErrorCode' => LogicClaimException::ERR_CODE_LIMIT_DAY, 'ErrorMsg' => $validLimitDays1));
                        try { if($lockItem) { $lockItem->terminate(); } } catch (\Exception $err) { ; }
                        $i++;
                        continue;
                    }
                }

                // 請求履歴データの設定
                $list = $this->listGetFromDB($oseq);
                $MailFlg = 0;
                $CreditTransferMailFlg = 0;
                $ClaimFileOutputClass = 0;

                // 請求書発行通知メール&請求ファイル出力区分の更新
                if ( ($list['CreditTransferRequestFlg'] > 0) && ($list['RequestStatus'] == 2) ) {
                    $MailFlg = 1;
                } else {
                    $ClaimFileOutputClass = 1;
                }

                //口振請求書通知メールの更新
                if ( ($list['CreditTransferRequestFlg'] == 1) && ( ($list['RequestStatus'] == null) || ($list['RequestStatus'] == 9) ) ) {
                    $ent = $mdle->find($list['EnterpriseId'])->current();
                    $CreditTransferMailFlg = 0;
                    if (($ent['AppFormIssueCond'] == 0) || ($ent['AppFormIssueCond'] == 2)) {
                        // 申込用紙発行条件が「0：発行しない」、「2：請求金額0円時」の場合はメール送信しない
                        $CreditTransferMailFlg = 1;
                    }
                } else {
                    $CreditTransferMailFlg = 1;
                }

                // 請求履歴の作成
                $data = array(
                        'OrderSeq' => $oseq,                                                                                    // 注文Seq
                        'ClaimDate' => date('Y-m-d'),                                                                           // 請求日
                        'ClaimCpId' => $this->app->authManagerAdmin->getUserInfo()->OpId,                                       // 請求担当者
                        'ClaimPattern' => 1,                                                                                    // 請求パターン（初回請求）
                        'LimitDate' => $limitDate,                                                                              // 支払期限
                        'DamageDays' => 0,                                                                                      // 遅延日数
                        'DamageInterestAmount' => 0,                                                                            // 遅延損害金
                        'ClaimFee' => 0,                                                                                // 請求手数料
                        'AdditionalClaimFee' => 0,                                                                              // 請求追加手数料
                        'PrintedFlg' => 0,                                                                                      // 印刷－処理フラグ
                        'MailFlg' => $MailFlg,                                                                                  // 請求書発行通知メール
                        'CreditTransferMailFlg' => $CreditTransferMailFlg,                                                      // 口振請求書通知メール
                        'ClaimFileOutputClass' => $ClaimFileOutputClass,                                                        // 請求ファイル出力区分
                        'EnterpriseBillingCode' => null,                                                                        // 同梱ツールアクセスキー
                        'ClaimAmount' => $useAmount,                                                                            // 請求金額
                        'RegistId' => $userId,                                                                                  // 登録者
                        'UpdateId' => $userId,                                                                                  // 更新者
                );

                try {
                    if(date('Y-m-d') > '2019-09-30'){
                        $hisSeq = $mdlch->saveNew2($oseq, $data);
                    }else{
                        $hisSeq = $mdlch->saveNew($oseq, $data);
                    }
                } catch(LogicClaimException $e) {
                    $this->app->dbAdapter->getDriver()->getConnection()->rollback();
                    // SMBC連携エラーの場合は請求エラーとする
                    $ceSeqs[] = $mdlce->saveNew(array('OrderSeq' => $oseq, 'ErrorCode' => $e->getCode(), 'ErrorMsg' => $e->getMessage()));
                    try { if($lockItem) { $lockItem->terminate(); } } catch (\Exception $err) { ; }
                    $i++;
                    continue;
                } catch(\Exception $e) {
                    throw $e;
                }

                //OEMID取得
                $oem_id = $mdlo->getOemId($oseq);

                //OEM判定
                if(!is_null($oem_id) && $oem_id != 0){

                    // OEM請求手数料は初回のみ取る
                    $cnt = (int)$this->app->dbAdapter->query(" SELECT COUNT(1) AS cnt FROM T_ClaimControl WHERE ReissueClass <> 0 AND OrderSeq = :OrderSeq "
                        )->execute(array(':OrderSeq' => $oseq))->current()['cnt'];
                    if ($cnt == 0) {

                        $mdlocf = new TableOemClaimFee($this->app->dbAdapter);

                        //OEM請求手数料書き込み
                        $mdlocf->saveOemClaimFee($oseq, $userId);
                    }
                }

                // 注文の確定待ちフラグをアップ
                $uOrder = array(
                    'ConfirmWaitingFlg' => '1',
                    'UpdateId'          => $userId,
                );
                $mdlo->saveUpdateWhere($uOrder, array('P_OrderSeq' => $oseq));

                // 注文マイページを作成する
                $logicmo->createMypageOrder($oseq, $limitDate, $oem_id, $userId, $authUtil);

                $this->app->dbAdapter->getDriver()->getConnection()->commit();
            } catch (\Exception $e) {
                $this->app->dbAdapter->getDriver()->getConnection()->rollback();

                // ロックを獲得していたら開放
                try {
                    if($lockItem) {
                        $lockItem->terminate();
                    }
                } catch (\Exception $err) { ; }

                // 処理失敗
                throw $e;

            }

            $i++;
            // ロックを獲得していたら開放
            try {
                if($lockItem) {
                    $lockItem->terminate();
                }
            } catch (\Exception $e) { ; }
        }
    }

    /**
     * 口座振替申込み区分, 加盟店顧客.申込みステータスを取得する
     */
    protected function  listGetFromDB($oseq) {
$sql = <<<EOQ
SELECT  ao.CreditTransferRequestFlg,                    /* 口座振替申込み区分 */
            ec.RequestStatus,                           /* 申込みステータス */
            sit.FirstCreditTransferClaimFee,            /* 口振用初回請求手数料 */
            sit.CreditTransferClaimFee,                 /* 口振用請求手数料 */
            o.EnterpriseId
FROM    T_Order o
            INNER JOIN AT_Order ao
                    ON (ao.OrderSeq = o.OrderSeq)
            INNER JOIN T_Customer c
                    ON (c.OrderSeq = o.OrderSeq)
            INNER JOIN T_EnterpriseCustomer ec
                    ON (ec.EntCustSeq = c.EntCustSeq)
            INNER JOIN T_Site sit
                    ON( sit.SiteId = o.SiteId)
WHERE  o.OrderSeq = :OrderSeq
EOQ;
        $prm = array(
                ':OrderSeq' => $oseq,
        );
        $list = $this->app->dbAdapter->query($sql)->execute($prm)->current();

        return $list;
    }

    /**
     * pdfダウンロード
     */
    protected function pdfDownload() {
        $params = $_SESSION[self::SESSION_JOB_PARAMS];

        // 用紙
        $paperType = isset($params['paperTypeVal']) ? $params['paperTypeVal'] : 1;

        // バーコード、QRコード作成用
        $barcode = Application::getInstance()->config['barcode'];
        set_include_path(get_include_path() . PATH_SEPARATOR . $barcode['barcode_lib']);
        require_once 'YubinCustomer.php';
        require_once 'QR.php';
        require_once 'EAN128.php';
        $yubin = new \YubinCustomer();
        $qrCode = new \SharedQR();
        $qrCode->version = 5;           // バージョン 1～40を指定　デフォルト5
        $qrCode->error_level = 'M';     // エラーレベル　L,M,Q,Hを指定　デフォルトM
        $ean128 = new \EAN128();
        $ean128->TextWrite = false;

        // CB情報
        $sys = new TableSystemProperty($this->app->dbAdapter);
        $cbPost = $sys->getValue('[DEFAULT]', 'systeminfo', 'CbPostalCode');
        $cbAddress = $sys->getValue('[DEFAULT]', 'systeminfo', 'CbUnitingAddress');
        $cbName = $sys->getValue('[DEFAULT]', 'systeminfo', 'CbNameKj');
        $cbTel = $sys->getValue('[DEFAULT]', 'systeminfo', 'CbPhone');

        $datas = array();
        $i = 0;
        while( isset( $params['OrderSeq' . $i] ) ) {
            if( !isset( $params['chkPrint' . $i ] ) ) {
                $i++;
                continue;
            }
            $data = array();
            $prm = array( ':OrderSeq' => $params['OrderSeq' . $i] );

            $sql  = ' SELECT c.PostalCode ';
            $sql .= ' ,      c.UnitingAddress ';
            $sql .= ' ,      c.RegUnitingAddress ';
            $sql .= ' ,      c.NameKj ';
            $sql .= ' ,      o.OrderId ';
            $sql .= ' ,      o.ReceiptOrderDate ';
            $sql .= ' ,      s.SiteNameKj ';
            $sql .= ' ,      e.ContactPhoneNumber ';
            $sql .= ' ,      e.PrintEntComment ';
            $sql .= ' ,      e.PrintEntOrderIdOnClaimFlg ';
            $sql .= ' ,      ch.ClaimAmount ';
            $sql .= ' ,      ch.LimitDate ';
            $sql .= ' ,      ch.ClaimFee ';
            $sql .= ' ,      ch.AdditionalClaimFee ';
            $sql .= ' ,      ch.DamageInterestAmount ';
            $sql .= ' ,      ch.ClaimPattern ';
            $sql .= ',       o.Ent_OrderId ';
            $sql .= ' ,      ca.TaxAmount ';
            $sql .= ' ,      ca.Cv_BarcodeData ';
            $sql .= ' ,      ca.Cv_BarcodeString1 ';
            $sql .= ' ,      ca.Cv_BarcodeString2 ';
            $sql .= ' ,      ca.Bk_BankName ';
            $sql .= ' ,      ca.Bk_BranchName ';
            $sql .= ' ,      ca.Bk_DepositClass ';
            $sql .= ' ,      ca.Bk_AccountNumber ';
            $sql .= ' ,      ca.Bk_AccountHolderKn ';
            $sql .= ' ,      IFNULL(o.OemId, 0) AS OemId ';
            $sql .= ' ,      oem.PostalCode AS OemPostalCode ';
            $sql .= ' ,      oem.PrefectureName AS OemPrefectureName ';
            $sql .= ' ,      oem.City AS OemCity ';
            $sql .= ' ,      oem.Town AS OemTown ';
            $sql .= ' ,      oem.Building AS OemBuilding ';
            $sql .= ' ,      oem.OemNameKj ';
            $sql .= ' ,      oem.ContactPhoneNumber AS OemContactPhoneNumber ';
            $sql .= ' ,      cd105.KeyContent AS MypageUrl ';
            $sql .= ' ,      cd108.KeyContent AS PrintContactPhoneNumber ';
            $sql .= ' FROM   T_Order o INNER JOIN ';
            $sql .= '        T_Customer c ON ( o.OrderSeq = c.OrderSeq ) INNER JOIN ';
            $sql .= '        T_Enterprise e ON ( o.EnterpriseId = e.EnterpriseId ) INNER JOIN ';
            $sql .= '        T_Site s ON ( o.SiteId = s.SiteId ) LEFT OUTER JOIN ';
            $sql .= '        T_ClaimHistory ch ON ( o.OrderSeq = ch.OrderSeq AND ch.PrintedFlg = 0  AND ch.ValidFlg = 1 ) LEFT OUTER JOIN ';
            $sql .= '        T_OemClaimAccountInfo ca ON( ch.Seq = ca.ClaimHistorySeq AND ca.Status = 1 ) LEFT OUTER JOIN ';
            $sql .= '        T_Oem oem ON( o.OemId = oem.OemId ) LEFT OUTER JOIN ';
            $sql .= '        M_Code cd105 ON( cd105.CodeId = 105 AND IFNULL(o.OemId, 0) = cd105.KeyCode) LEFT OUTER JOIN ';
            $sql .= '        M_Code cd108 ON( cd108.CodeId = 108 AND IFNULL(o.OemId, 0) = cd108.KeyCode) ';
            $sql .= ' WHERE  o.OrderSeq = :OrderSeq ';
            $sql .= '   AND  EXISTS (SELECT * FROM T_Order t WHERE t.P_OrderSeq = o.OrderSeq AND t.Cnl_Status = 0) ';
            $data = $this->app->dbAdapter->query( $sql )->execute( $prm )->current();

            if (!$data) {
                // 有効な注文データがない場合はスキップ
                $i++;
                continue;
            }

            // 請求金額が30万円以上だった場合
            if( $data['ClaimAmount'] >= 300000 ) {
                $data['Cv_BarcodeData'] = '収納代行の規約によりコンビニエンスストアで30万円以上のお支払はできません';
                $data['Cv_BarcodeString1'] = '';
                $data['Cv_BarcodeString2'] = '';
            }

            // 任意注文番号非表示の加盟店
            if ($data['PrintEntOrderIdOnClaimFlg'] == 0) {
                $data['Ent_OrderId'] = '';
            }

            // 初回はブランク
            $data['ClaimFee'] = '';
            $data['DamageInterestAmount'] = '';

            // 注文商品
            $sql  = ' SELECT itm.ItemNameKj ';
            $sql .= ' ,      itm.ItemNum ';
            $sql .= ' ,      itm.UnitPrice ';
            $sql .= ' ,      itm.SumMoney ';
            $sql .= ' ,      e.DispDecimalPoint ';  /* 表示用小数点桁数 */
            $sql .= ' FROM   T_Order o INNER JOIN ';
            $sql .= '        T_OrderItems itm ON ( o.OrderSeq = itm.OrderSeq ) ';
            $sql .= '        INNER JOIN T_Enterprise e ON ( e.EnterpriseId = o.EnterpriseId ) ';
            $sql .= ' WHERE  itm.DataClass = 1 AND ';
            $sql .= '        o.P_OrderSeq = :OrderSeq ';
            $sql .= ' AND    o.Cnl_Status = 0 ';
            $sql .= ' AND    itm.ValidFlg = 1 ';
            $sql .= ' ORDER BY OrderItemId ';
            $items = ResultInterfaceToArray( $this->app->dbAdapter->query( $sql )->execute( $prm ) );
            for( $j = 1; $j <= 15; $j++ ) {
                $data['ItemNameKj_' . $j] = isset( $items[$j - 1]['ItemNameKj'] ) ? $items[$j - 1]['ItemNameKj'] : '';
                $data['ItemNum_' . $j] = isset( $items[$j - 1]['ItemNum'] ) ? $items[$j - 1]['ItemNum'] : '';
                if ($data['ItemNum_' . $j] != '') {
                    // [表示用小数点桁数]考慮
                    $data['ItemNum_' . $j] = number_format($data['ItemNum_' . $j], $items[$j - 1]['DispDecimalPoint'], '.', '');
                }
                $data['UnitPrice_' . $j] = isset( $items[$j - 1]['UnitPrice'] ) ? $items[$j - 1]['UnitPrice'] : '';
                $data['SumMoney_' . $j] = isset( $items[$j - 1]['SumMoney'] ) ? $items[$j - 1]['SumMoney'] : '';
            }

            // 小計
            $data['TotalItemPrice'] = 0;
            foreach ($items as $row) {
                $data['TotalItemPrice'] += $row['SumMoney'];
            }

            // 送料
            $sql  = ' SELECT SUM( itm.SumMoney ) AS CarriageFee ';
            $sql .= ' FROM   T_Order o INNER JOIN ';
            $sql .= '        T_OrderItems itm ON ( o.OrderSeq = itm.OrderSeq ) ';
            $sql .= ' WHERE  itm.DataClass = 2 AND ';
            $sql .= '        o.P_OrderSeq = :OrderSeq ';
            $sql .= ' AND    o.Cnl_Status = 0 ';
            $data = array_merge( $data, $this->app->dbAdapter->query( $sql )->execute( $prm )->current() );

            // 決済手数料
            $sql  = ' SELECT SUM( itm.SumMoney ) AS ChargeFee ';
            $sql .= ' FROM   T_Order o INNER JOIN ';
            $sql .= '        T_OrderItems itm ON ( o.OrderSeq = itm.OrderSeq ) ';
            $sql .= ' WHERE  itm.DataClass = 3 AND ';
            $sql .= '        o.P_OrderSeq = :OrderSeq ';
            $sql .= ' AND    o.Cnl_Status = 0 ';
            $data = array_merge( $data, $this->app->dbAdapter->query( $sql )->execute( $prm )->current() );

            // 請求回数
            $data['ReIssueCount'] = 0;

            // マイページログインパスワード
            $row_mypageorder = $this->app->dbAdapter->query(" SELECT Token FROM T_MypageOrder WHERE OrderSeq = :OrderSeq ORDER BY Seq DESC LIMIT 1 "
            )->execute(array(':OrderSeq' => $params['OrderSeq' . $i]))->current();
            $data['MypageToken'] = ($row_mypageorder) ? $row_mypageorder['Token'] : '';

            // 商品合計数
            // count関数対策
            if(empty($items)) {
                $data['ItemsCount'] = 0;
            } else {
                $data['ItemsCount'] = count($items);
            }

            // CB情報
            $data['CbPost'] = $cbPost;
            $data['CbAddress'] = $cbAddress;
            $data['CbName'] = $cbName;
            $data['CbTel'] = $cbTel;

            // 請求金額
            $data['BilledAmt'] = nvl( $data['ClaimAmount'], 0 ) - nvl( $data['ReceiptAmountTotal'], 0 );

            // CB、OEMで切り替えるデータ
            if ($data['OemId'] == 0) {
                // CB

                // 発行元
                $printPost = '〒' . $data['CbPost'];
                $printAddress = $data['CbAddress'];
                $printName = $data['CbName'];
                $printTel = 'お問合せ：' . $data['CbTel'];

                // 請求書についてのお問合せ
                $billInq1 = $data['CbName'];
                $billInq2 = '後払いドットコム事業部';
                $billInq3 = 'TEL:' . $data['CbTel'];

                // 払込受領票－受取人
                $accept1 = $data['CbName'];
                $accept2 = 'TEL:' . $data['CbTel'];

                // マイページ印字なし
                $data['MypageUrl'] = '';
            }
            else {
                //OEM

                // 発行元
                if (substr($data['OrderId'], 0, 2) != 'AB') {
                    // SMBC以外の場合、OEMの情報
                    $printPost = '〒' . $data['OemPostalCode'];
                    $printAddress = $data['OemPrefectureName'] . $data['OemCity'] . $data['OemTown'] . $data['OemBuilding'];
                    $printName = $data['OemNameKj'];
                    $printTel = 'お問合せ：' . $data['PrintContactPhoneNumber'];
                }
                else {
                    // SMBCの場合、CB情報
                    $printPost = '〒' . $data['CbPost'];
                    $printAddress = $data['CbAddress'];
                    $printName = $data['CbName'];
                    $printTel = 'お問合せ：' . $data['CbTel'];
                }

                // 請求書についてのお問合せ
                if (substr($data['OrderId'], 0, 2) != 'AB') {
                    // SMBC以外の場合、OEMの情報
                    $billInq1 = $data['OemNameKj'];
                    $billInq2 = '後払い窓口';
                    $billInq3 = 'TEL:' . $data['PrintContactPhoneNumber'];

                }
                else {
                    // SMBCの場合、CB情報
                    $billInq1 = $data['CbName'];
                    $billInq2 = '後払いドットコム事業部';
                    $billInq3 = 'TEL:' . $data['CbTel'];
                }

                // 払込受領票－受取人
                if (substr($data['OrderId'], 0, 2) != 'AB') {
                    // SMBC以外の場合、OEMの情報
                    $accept1 = $data['OemNameKj'] . '　後払い窓口';
                    $accept2 = 'TEL:' . $data['PrintContactPhoneNumber'];
                }
                else {
                    // SMBCの場合、CB情報
                    $accept1 = $data['CbName'];
                    $accept2 = 'TEL:' . $data['CbTel'];
                }
            }

            $data['PrintPost'] = $printPost;
            $data['PrintAddress'] = $printAddress;
            $data['PrintName'] = $printName;
            $data['PrintTel'] = $printTel;
            $data['BillInq1'] = $billInq1;
            $data['BillInq2'] = $billInq2;
            $data['BillInq3'] = $billInq3;
            $data['Accept1'] = $accept1;
            $data['Accept2'] = $accept2;

            // 郵便カスタマバーコード
            // 郵便番号ハイフン抜き
            $post = str_replace('-', '', $data['PostalCode']);
            // 郵便番号辞書の住所を正規化して取得
            $sql = " SELECT * FROM M_PostalCode WHERE PostalCode7 = :PostalCode7 ";
            $postalAddr = $this->app->dbAdapter->query( $sql )->execute( array(':PostalCode7' => $post) )->current();
            $postalAddrNml = '';
            if ($postalAddr != false) {
                $postalAddrNml = LogicNormalizer::create( LogicNormalizer::FILTER_FOR_ADDRESS )->normalize( $postalAddr['PrefectureKanji'] . $postalAddr['CityKanji'] . $postalAddr['TownKanji'] );
            }
            $matchAddr = str_replace($postalAddrNml, '', $data['RegUnitingAddress']);
            // 英数字の抜き出し（正規化住所から郵便番号辞書の住所を抜いてから抜き出し）
            preg_match_all('/[a-zA-Z0-9]+/', BaseGeneralUtils::convertWideToNarrow($matchAddr), $addr);
            $addrstr = '';
            if (is_array($addr[0]) && (!empty($addr[0]))) {
                $addrstr = implode('-', $addr[0]);
            }
            $code = $post . $addrstr;
            $yubinImg = $yubin->draw($code, 100);
            ob_start();
            imagegif($yubinImg);
            $yubinImgData = ob_get_clean();
            $yubinSrc = sprintf('data:image/gif;base64,%s', base64_encode($yubinImgData));
            $data['Yubin'] = $yubinSrc;

            // QRコード
            if (strlen($data['MypageUrl']) > 0) {
                $qrCodeImg = $qrCode->draw_by_size($data['MypageUrl'], 1);
                ob_start();
                imagegif($qrCodeImg);
                $qrCodeImgData = ob_get_clean();
                $qrCodeSrc = sprintf('data:image/gif;base64,%s', base64_encode($qrCodeImgData));
                $data['QrCode'] = $qrCodeSrc;
            }
            else {
                $data['QrCode'] = '';
            }

            // バーコード
            $data['Ean128'] = '';
            if( $data['ClaimAmount'] < 300000 ) {
                $ean128Img = $ean128->drawConvenience('{FNC1}' . $data['Cv_BarcodeData'], 1, 50);
                ob_start();
                imagegif($ean128Img);
                $ean128ImgData = ob_get_clean();
                $ean128Src = sprintf('data:image/gif;base64,%s', base64_encode($ean128ImgData));
                $data['Ean128'] = $ean128Src;
            }

            // 請求履歴データを取得
            $mdlch = new TableClaimHistory($this->app->dbAdapter);
            $row_ch = $mdlch->findClaimHistory(array( 'PrintedFlg' => 0, 'ValidFlg' => 1, 'OrderSeq' => $params['OrderSeq' . $i] ))->current();
            // 請求履歴．印刷ステータス(PrintedStatus)を"3"(PDF印刷済み)に更新する
            $this->app->dbAdapter->query(" UPDATE T_ClaimHistory SET PrintedStatus = 3 WHERE Seq = :Seq ")->execute(array(':Seq' => $row_ch['Seq']));

            $datas[] = $data;
            $i++;
        }

        if ($paperType == 1) {
            // 圧着ハガキ
           $fileName = sprintf( 'Hagaki_%s.pdf', date( "YmdHis" ) );

            $this->setTemplate('billedhagaki');

            $this->view->assign( 'datas', $datas );
            $this->view->assign( 'documentRoot', $_SERVER['DOCUMENT_ROOT'] );
            $this->view->assign( 'title', $fileName );

            $viewRender = $this->getServiceLocator()->get('ViewRenderer');
            $html = $viewRender->render($this->view);

            // 一時ファイルの保存先
            $mdlsp = new \models\Table\TableSystemProperty($this->app->dbAdapter);
            $tempDir = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'TempFileDir');
            $tempDir = realpath($tempDir);

            // 出力ファイル名
            $outFileName = $fileName;

            // 中間ファイル名
            $fname_html = ($tempDir . '/__tmp_' . $fileName . '__.html');
            $fname_pdf  = ($tempDir . '/__tmp_' . $fileName . '__.pdf');

            // HTML出力
            file_put_contents($fname_html, $html);

            // PDF変換(外部プログラム起動)
            $ename = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'wkhtmltopdf');
            $option = " --page-width 299.5mm --page-height 148.2mm --orientation Portrait --margin-left 0 --margin-right 0 --margin-top 0 --margin-bottom 0 ";
            exec($ename . $option . $fname_html . ' ' . $fname_pdf);

            unlink($fname_html);

            header( 'Content-Type: application/octet-stream; name="' . $outFileName . '"' );
            header( 'Content-Disposition: attachment; filename="' . $outFileName . '"' );
            header( 'Content-Length: ' . filesize( $fname_pdf ) );

            // 出力
            echo readfile( $fname_pdf );

            unlink( $fname_pdf );
            die();
         }
        else {
            // その他 → 空で返す
            return $this->response;
        }
    }

    /**
     * OEMID混在のチェック
     * 印刷ボタンで使用されているため、今後の改修は不要
     */
    public function ismixoemidAction()
    {
        $params = $this->getParams();

        try {
            $i = 0;
            $isFirstOn = true;          // 初回か？
            $compareTargetOemId = -1;   // 比較対象OEMID
            while( isset( $params['OemId' . $i] ) ) {
                if ($isFirstOn) {
                    $compareTargetOemId = $params['OemId' . $i];    // 比較対象OEMIDセット
                    $isFirstOn = false;
                }
                else if ($compareTargetOemId != $params['OemId' . $i]) {
                    throw new \Exception('OEMが混在している為、用紙が特定出来ません。');
                }

                $i++;
            }

            $msg = '1';
        }
        catch(\Exception $e) {
            $msg = $e->getMessage();
        }

        echo \Zend\Json\Json::encode(array('status' => $msg));
        return $this->response;
    }


    /**
     * 請求エラーの文言を変更する
     * @param array $ceSeqs T_ClaimErrorのSeqリスト
     * @return string
     */
    protected function getStatusCaption($ceSeqs) {
        $retFnc = "";

        // 請求エラーがある場合
        $sql  = " SELECT o.OrderId ";
        $sql .= "       ,ce.ErrorCode ";
        $sql .= "       ,ce.ErrorMsg ";
        $sql .= "  FROM T_ClaimError ce ";
        $sql .= "       INNER JOIN T_Order o ";
        $sql .= "               ON ce.OrderSeq = o.OrderSeq ";
        $sql .= " WHERE 1 = 1 ";
        $sql .= "   AND ce.Seq IN (" . implode(",", $ceSeqs) . ")";

        $ri = $this->app->dbAdapter->query($sql)->execute(null);

        foreach ($ri as $row) {
            if ($row['ErrorCode'] == LogicClaimException::ERR_CODE_SMBC) {
                $retFnc .= sprintf("%s SMBC連携エラー(%s)\n", $row['OrderId'], $row['ErrorMsg']);

            } elseif($row['ErrorCode'] == LogicClaimException::ERR_CODE_0YEN) {
                $retFnc .= sprintf("%s 請求額が０円のため、請求データが作成されませんでした。\n", $row['OrderId']);

            } elseif($row['ErrorCode'] == LogicClaimException::ERR_CODE_LIMIT_DAY) {
                $retFnc .= sprintf("%s 支払期限が%s日未満となるため、請求データが作成されませんでした。\n", $row['OrderId'], $row['ErrorMsg']);

            }
        }

        return $retFnc;
    }

}

