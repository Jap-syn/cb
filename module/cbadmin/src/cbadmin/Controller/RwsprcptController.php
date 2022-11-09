<?php
namespace cbadmin\Controller;

use Coral\Base\BaseHtmlUtils;
use Coral\Base\BaseGeneralUtils;
use Coral\Coral\Controller\CoralControllerAction;
use Coral\Coral\CoralCodeMaster;
use Coral\Coral\CoralPager;
use cbadmin\Application;
use models\Logic\LogicTemplate;
use Coral\Coral\History\CoralHistoryOrder;
use models\Table\TableOrder;
use models\Table\TableOem;
use models\Table\TableSystemProperty;
use models\Table\TableOutputFile;
use models\Table\TableUser;
use models\Table\TableRepaymentControl;
use models\Table\ATableRepaymentControl;
use models\Table\ATableOrder;
use models\Table\TableClaimControl;
use models\Table\TableCustomer;

class RwsprcptController extends CoralControllerAction
{
	protected $_componentRoot = './application/views/components';

	const SESS_SEARCH_INFO = 'SESS_SEARCH_INFO';
	const SESS_NET_DATA = 'SESS_NET_DATA';
	const SESS_NET_HAGAKI = 'SESS_NET_HAGAKI';

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
	 * Controllerを初期化する
	 */
	public function _init()
	{
        $this->app = Application::getInstance();
        $this->view->assign('userInfo', $this->app->authManagerAdmin->getUserInfo());

        $this->addStyleSheet('../css/default02.css');
        $this->addJavaScript( '../js/json+.js' );
        $this->addJavaScript( '../js/prototype.js' );
        $this->addJavaScript( '../js/corelib.js' );
        $this->addJavaScript( '../js/base.ui.js');
        // [paging] CoralPagerのロードと必要なCSS/JSのアサイン
        $this->addStyleSheet('../css/base.ui.customlist.css');
        $this->addJavaScript('../js/base.ui.customlist.js');

        $this->setPageTitle("後払い.com - その他経理");
	}

	/**
	 * 過不足入金一覧
	 */
	public function lacklistAction()
	{
        $params = $this->getParams();

        // [paging] 1ページあたりの項目数
        $ipp = self::PAGE_LINE_MAX;
        // [paging] 指定ページを取得
        $current_page = (isset($params['page'])) ? (int)$params['page'] : 1;
        if ($current_page < 1) $current_page = 1;

        $lacktype = (isset($params['lacktype'])) ? $params['lacktype'] : 0;
        $oemid = (isset($params['oem'])) ? $params['oem'] : -1;
        $balanceF = (isset($params['ClaimedBalanceF'])) ? $params['ClaimedBalanceF'] : null;
        $balanceT = (isset($params['ClaimedBalanceT'])) ? $params['ClaimedBalanceT'] : null;
        $transcom = (isset($params['transcom'])) ? $params['transcom'] : -1;
        $netstatus = (isset($params['netstatus'])) ? $params['netstatus'] : -1;

        // 検索SQL
        $sql =<<<EOQ
SELECT  o.OrderSeq                                  /* 注文Seq（非表示項目） */
    ,   o.OrderId                                   /* 注文ID */
    ,   MAX(cc.F_LimitDate) AS F_LimitDate          /* 初回請求 */
    ,   MAX(cc.ClaimDate) AS ClaimDate              /* 最終請求 */
    ,   MAX(cc.LimitDate) AS LimitDate              /* 支払期限 */
    ,   (SELECT Class2 FROM M_Code WHERE CodeId = 12 AND KeyCode = cc.ClaimPattern) AS IncreCaption      /* 請求 */
    ,   MAX(mc.Class2) AS IncreLogCaption           /* 属性 */
    ,   MAX(c.NameKj) AS NameKj                     /* 請求先氏名 */
    ,   MAX(cc.ClaimAmount) AS ClaimAmount          /* 請求金額 */
    ,   (SELECT KeyContent FROM M_Code WHERE CodeId = 198 AND KeyCode = MAX(rc.ReceiptClass)) AS ReceiptClass /* 入金形態 */
    ,   MAX(rc.ReceiptDate) AS ReceiptDate          /* 顧客入金日 */
    ,   MAX(rc.ReceiptProcessDate) AS ReceiptProcessDate  /* 入金処理日 */
    ,   SUM(rc.ReceiptAmount) AS ReceiptAmount      /* 実入金額 */
    ,   CASE
            WHEN cc.ClaimPattern IN (1, 2, 3, 4) THEN '初回'
            WHEN cc.ClaimPattern IN (5, 6) THEN '再１'
            WHEN cc.ClaimPattern IN (7, 8, 9) THEN '再３'
            ELSE ''
        END AS ClaimPattern /* 最低請求状態 */
    ,   MAX(cc.MinClaimAmount) AS MinClaimAmount    /* 最低請求金額 */
    ,   MAX(cc.ClaimedBalance) AS ClaimedBalance    /* 過不足金 */
    ,   c.CustomerId
    ,   (SELECT MAX(RepayTCFlg) FROM T_Order o, AT_Order ao WHERE o.OrderSeq = ao.OrderSeq AND o.P_OrderSeq = cc.OrderSeq) AS RepayTCFlg
    ,   (SELECT MAX(RepayPendingFlg) FROM T_Order o, AT_Order ao WHERE o.OrderSeq = ao.OrderSeq AND o.P_OrderSeq = cc.OrderSeq) AS RepayPendingFlg
    ,   (SELECT COUNT(1) FROM T_RepaymentControl WHERE ClaimId = cc.ClaimId AND RepayStatus = 0 ) AS CntNet
    ,   (SELECT SUM(TransferCommission) FROM T_RepaymentControl WHERE ClaimId = cc.ClaimId AND RepayStatus = 0 ) AS TransferCommission
    ,   cc.ClaimId
FROM    T_ClaimControl cc
	    STRAIGHT_JOIN T_Order o ON (o.OrderSeq = cc.OrderSeq)
        STRAIGHT_JOIN T_Customer c ON (o.OrderSeq = c.OrderSeq)
        LEFT OUTER JOIN T_ReceiptControl rc ON (o.OrderSeq = rc.OrderSeq)
        LEFT OUTER JOIN M_Code mc ON (mc.KeyCode = CASE
                                                        WHEN c.Incre_ArTel = 5 OR c.Incre_ArAddr = 5 THEN 5
                                                        WHEN c.Incre_ArTel = 4 OR c.Incre_ArAddr = 4 THEN 4
                                                        WHEN c.Incre_ArTel = 3 OR c.Incre_ArAddr = 3 THEN 3
                                                        WHEN c.Incre_ArTel = 2 OR c.Incre_ArAddr = 2 THEN 2
                                                        WHEN c.Incre_ArTel = 1 OR c.Incre_ArAddr = 1 THEN 1
                                                        ELSE -1
                                                   END
                                      AND mc.CodeId = 4)
WHERE cc.ClaimedBalance <> 0
AND   o.OrderSeq = o.P_OrderSeq
AND   o.Cnl_Status = 0
AND   o.Rct_Status = 1
AND   ( o.DataStatus = 61 OR ( o.DataStatus = 91 AND o.CloseReason = 1 ) )
%s
GROUP BY o.OrderSeq, o.OrderId
ORDER BY ReceiptProcessDate
EOQ;
        // 検索条件の考慮
        $where  = "";
        if      ($lacktype == 1) { $where = " AND cc.ClaimedBalance < 0 "; } // 過剰
        else if ($lacktype == 2) { $where = " AND cc.ClaimedBalance > 0 "; } // 不足

        // OEM先
        if ($oemid >= 0) {
            $where .= " AND IFNULL(o.OemId, 0) = $oemid ";
        }

        // 過不足金
        $wClaimAmount = BaseGeneralUtils::makeWhereInt(
            '(cc.ClaimedBalance * -1)',
            BaseGeneralUtils::convertWideToNarrow($balanceF),
            BaseGeneralUtils::convertWideToNarrow($balanceT)
        );
        if ($wClaimAmount != '') {
            $where .= " AND " . $wClaimAmount;
        }

        // ﾈｯﾄDE受取手数料
        if ($transcom == 1) {
            $where .= " AND EXISTS (SELECT * FROM T_Order o, AT_Order ao WHERE o.OrderSeq = ao.OrderSeq AND o.P_OrderSeq = cc.OrderSeq AND ao.RepayTCFlg = 1 ) ";
        } elseif($transcom == 0) {
            $where .= " AND NOT EXISTS (SELECT * FROM T_Order o, AT_Order ao WHERE o.OrderSeq = ao.OrderSeq AND o.P_OrderSeq = cc.OrderSeq AND ao.RepayTCFlg = 1 ) ";
        }

        // ﾈｯﾄDE受取ｽﾃｰﾀｽ
        if ($netstatus == 0) {
            // ｽﾃｰﾀｽが指示中かつ、ﾈｯﾄDE受取ｽﾃｰﾀｽが未指示以外のデータが取得できないこと (つまり、指示済みのデータがないこと！)
            $where .= " AND (SELECT COUNT(1) FROM T_RepaymentControl WHERE ClaimId = cc.ClaimId AND RepayStatus = 0 AND ProcessClass = 3 AND NetStatus > 0) = 0 ";
        } elseif ($netstatus > 0) {
            $where .= " AND (SELECT COUNT(1) FROM T_RepaymentControl WHERE ClaimId = cc.ClaimId AND RepayStatus IN (0, 1) AND ProcessClass = 3 AND NetStatus = $netstatus) > 0 ";
        }

        $sql = sprintf($sql, $where);
        // SQL実行
        $datas = ResultInterfaceToArray($this->app->dbAdapter->query($sql)->execute(null));

        // count関数対策
        $datasLen = 0;
        if(!empty($datas)) {
            $datasLen = count($datas);
		}

        // [paging] ページャ初期化
        $pager = new CoralPager($datasLen, $ipp);
        // [paging] 指定ページを補正
        if( $current_page > $pager->getTotalPage() ) $current_page = $pager->getTotalPage();
        // [paging] 対象リストをページング情報に基づいて対象リストをスライス
        if( $datasLen > 0 ) $datas = array_slice( $datas, $pager->getStartIndex( $current_page ), $ipp );
        // [paging] ページングナビゲーション情報
        $page_links = array( 'base' => "rwsprcpt/lacklist/lacktype/" . f_e($lacktype) . "/oem/" . f_e($oemid) . "/ClaimedBalanceF/" . f_e($balanceF) . "/ClaimedBalanceT/" . f_e($balanceT) . "/transcom/" . f_e($transcom) . "/netstatus/" . f_e($netstatus) . "/page" );
        $page_links['prev'] = $page_links['base'] . '/' . ( $current_page - 1 );
        $page_links['next'] = $page_links['base'] . '/' . ( $current_page + 1 );
        // [paging] ページング関連の情報をビューへアサイン
        $this->view->assign( 'current_page', $current_page );
        $this->view->assign( 'pager', $pager );
        $this->view->assign( 'page_links', $page_links );

        $this->view->assign('lacktypeTag',BaseHtmlUtils::SelectTag("lacktype", array (0 => '--過不足入金全て', 1 => '過剰入金のみ', 2 => '不足入金のみ'), $lacktype));
        $this->view->assign('list', $datas);
        $this->view->assign('lacktype', $lacktype);

        // OEMリストタグ
        $aryOem = array(-1 => '----------', 0 => 'キャッチボール');
        $ri = $this->app->dbAdapter->query(" SELECT OemId, OemNameKj FROM T_Oem WHERE ValidFlg = 1 ORDER BY OemId ")->execute(null);
        foreach ($ri as $row) {
            $aryOem[$row['OemId']] = $row['OemNameKj'];
        }
        $this->view->assign("oemTag",BaseHtmlUtils::SelectTag('oem', $aryOem, $oemid));

        // 過不足金
        $this->view->assign("ClaimedBalanceF",$balanceF);
        $this->view->assign("ClaimedBalanceT",$balanceT);

        // ネットDE受取手数料
        $this->view->assign("transTag",BaseHtmlUtils::SelectTag('transcom', array(-1 => '全て', 1 => '不要', 0 => '必要'), $transcom));

        // ネットDE受取ステータス
        $ccm = new CoralCodeMaster($this->app->dbAdapter);
        $this->view->assign('statusTag',BaseHtmlUtils::SelectTag("netstatus", $ccm->getNetStatusMaster(), $netstatus));

        // ﾈｯﾄDE受付手数料
        $mdlSysp = new TableSystemProperty($this->app->dbAdapter); // NetTransferCommission
        $ntc = $mdlSysp->getValue(TableSystemProperty::DEFAULT_MODULE, 'systeminfo', 'NetTransferCommission');
        $this->view->assign('NetTransferCommission', $ntc);

        // ダウンロードリンク
        unset($params['controller']);
        unset($params['action']);
        unset($params['module']);
        $this->view->assign('durl', 'rwsprcpt/dlacklistcsv?' . http_build_query($params));

        // 過不足入金一覧更新リンク
        $this->view->assign('surl', 'rwsprcpt/lacksave?' . http_build_query($params));

        return $this->view;
	}


	/**
	 * 過不足入金一覧
	 */
	public function lacksaveAction()
	{
	    $mdlSysp = new TableSystemProperty($this->app->dbAdapter);
	    $mdlao = new ATableOrder($this->app->dbAdapter);
        $mdlRp = new TableRepaymentControl($this->app->dbAdapter);
        $mdlARp = new ATableRepaymentControl($this->app->dbAdapter);
        $history = new CoralHistoryOrder($this->app->dbAdapter);


	    $params = $this->getParams();

	    $lacktype = (isset($params['lacktype'])) ? $params['lacktype'] : 0;
	    $oemid = (isset($params['oem'])) ? $params['oem'] : -1;
	    $balanceF = (isset($params['ClaimedBalanceF'])) ? $params['ClaimedBalanceF'] : null;
	    $balanceT = (isset($params['ClaimedBalanceT'])) ? $params['ClaimedBalanceT'] : null;
	    $transcom = (isset($params['transcom'])) ? $params['transcom'] : -1;
	    $netstatus = (isset($params['netstatus'])) ? $params['netstatus'] : -1;

	    // 更新処理
	    $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
	    try {
	        $i = 0;

	        // ユーザIDの取得
            $userTable = new \models\Table\TableUser($this->app->dbAdapter);
            $userID = $userTable->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

	        // 返金予定日、送金期限の算出
	        $netCoTranLimitDays = intval($mdlSysp->getValue(TableSystemProperty::DEFAULT_MODULE, 'systeminfo', 'NetCoTranLimitDays'));
	        $netCoTranLimitDays = $netCoTranLimitDays >= 0 ? $netCoTranLimitDays : 90; // 過去日は認めない
	        $repayExpectedDate = date('Y-m-d', strtotime(" +$netCoTranLimitDays day"));
	        $coTranLimit = date('Ymd0000', strtotime(" +$netCoTranLimitDays day"));

	        while (isset($params['OrderSeq' . $i])) {
	            $oseq = $params['OrderSeq' . $i]; // 注文SEQ
	            $claimId = $params['ClaimId' . $i]; // 請求ID
	            $oid = $params['OrderId' . $i]; // 注文ID
	            $transFee = intval($params['TransFee' . $i]); // 振込手数料
	            $claimedBalance = intval($params['ClaimedBalance' . $i]); // 過不足金
                $cnkPnd = isset($params['chkPnd' . $i]) ? 1 : 0;

                $rowao = $mdlao->find($oseq)->current();

	            // 返金保留の更新
	            $mdlao->saveUpdate(array('RepayPendingFlg' => isset($params['chkPnd' . $i]) ? 1 : 0), $oseq);

	            // 注文履歴の登録
	            if ($rowao['RepayPendingFlg'] == 1 && $cnkPnd == 0) {
	                // 返金保留⇒保留解除
	                $history->InsOrderHistory($oseq, 116, $userID);

	            }elseif ($rowao['RepayPendingFlg'] == 0 && $cnkPnd == 1) {
	                // 保留解除⇒返金保留
	                $history->InsOrderHistory($oseq, 115, $userID);

	            } else {
	                // 何もしない
	            }

	            // 返金指示の更新
	            if (isset($params['chInd' . $i])) {

	                // 返金管理テーブル登録
	                $data = array(
                        'IndicationDate' => date('Y-m-d H:i:s'),
                        'DecisionDate' => null,
                        'ProcessClass' => 3,
                        'BankName' => '',
                        'FfCode' => 0,
                        'BranchName' => '',
                        'FfBranchCode' => 0,
                        'FfAccountClass' => 1,
                        'AccountNumber' => '',
                        'AccountHolder' => '',
                        'TransferCommission' => $transFee,
                        'TransferAmount' => $claimedBalance - $transFee,
                        'RepayAmount' => $claimedBalance,
                        'RepayExpectedDate' => $repayExpectedDate,
                        'ClaimId' => $claimId,
                        'CheckingUseAmount' => $claimedBalance,
                        'CheckingClaimFee' => 0,
                        'CheckingDamageInterestAmount' => 0,
                        'CheckingAdditionalClaimFee' => 0,
                        'OutputFileSeq' => null,
                        'NetStatus' => 0,
                        'CoRecvNum' => $oid,
                        'CoYoyakuNum' => null,
                        'CoTranLimit' => $coTranLimit,
                        'CoWcosId' => $oid,
                        'CoWcosPassword' => null,
                        'CoWcosUrl' => null,
                        'CoTranReqDate' => null,
                        'CoTranProcDate' => null,
                        'MailFlg' => 9,
                        'MailRetryCount' => 0,
                        'RegistId' => $userID,
                        'UpdateId' => $userID,
                        'ValidFlg' => 1,
                    );
	                $repaySeq = $mdlRp->saveNew($data);

	                // 返金管理_会計の登録
	                $data = array(
                        'RepaySeq' => $repaySeq,
                        'DailySummaryFlg' => 0,
	                );
	                $mdlARp->saveNew($data);

	            }

	            $i++;
	        }
	        $this->app->dbAdapter->getDriver()->getConnection()->commit();
	    } catch(\Exception $e) {
	        $this->app->dbAdapter->getDriver()->getConnection()->rollback();
	        throw $e;
	    }

	    return $this->_redirect("rwsprcpt/lacklist/lacktype/$lacktype/oem/$oemid/ClaimedBalanceF/$balanceF/ClaimedBalanceT/$balanceT/transcom/$transcom/netstatus/$netstatus");
	}

	/**
	 * 返金指示入力画面(編集処理)
	 */
	public function repayeditAction()
	{
        $params = $this->getParams();

        $oseq = (isset($params['oseq'])) ? $params['oseq'] : 0;

        // 返金指示ベースデータの取得
        $row = $this->getRepayeditBaseData($oseq)->current();
        // 返金指示入金口座・返金情報ベースデータの取得
        $item = $this->getRepaymentControlBaseData($row["OrderId"])->current();

        // コードマスターから事業者情報向けのマスター連想配列を作成し、ビューへアサインしておく
        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
        $masters = array(
                'FfAccountClass' => $codeMaster->getAccountClassMaster(),
                'TcClass' => $codeMaster->getMasterCodes(104, array(0 => '-----')),
                'RcptMethod' => array(0 => '銀行振込', 1 => '書留郵便', 2 => '雑収入処理', 3 => 'ネットDE受取' ),
        );
        $numbers = $item['TransferCommission'];
        if($numbers === "0"){
            $target = sprintf('なし（%s円）',$numbers);
        }else{
            $target = sprintf('あり（%s円）',$numbers);
        }
        $key = array_keys($masters['TcClass'],$target);
        $tcClass = $key[0];
        $this->view->assign('master_map', $masters);

        if($item === false){
            $data = array(
                'oseq' => $oseq,
                'FfAccountClass' => key(array_slice($masters['FfAccountClass'], 0, 1, true)),
                'RcptMethod' => 0,  // 処理区分初期値：銀行振込
            );
        }else{
            $data = array(
                'oseq' => $oseq,
                'RcptMethod' => $item['ProcessClass'],
                'FfName' => $item['BankName'],
                'FfCode' => $item['FfCode'],
                'FfBranchName' => $item['BranchName'],
                'FfBranchCode' => $item['FfBranchCode'],
                'FfAccountClass' => $item['FfAccountClass'],
                'FfAccountNumber' => $item['AccountNumber'],
                'FfAccountName' => $item['AccountHolder'],
                'TcClass' => $tcClass,
                'paymentAmount' => $item['TransferAmount'],
                'repayExpectedDate' => $item['RepayExpectedDate'],
                'RcptMethod' => $item['ProcessClass'],
            );
        }
        $this->view->assign('data', $data);

        $this->view->assign('row', $row);

        return $this->view;
	}

	/**
	 * 返金指示ベースデータの取得
	 *
	 * @param int $oseq 注文SEQ
	 * @return ResultInterface SQL実行結果
	 */
	protected function getRepayeditBaseData($oseq)
	{
	    $sql =<<<EOQ
SELECT  o.OrderSeq                                          /* 注文Seq（非表示項目） */
    ,   o.OrderId                                           /* 注文ID */
    ,   DATE(cc.F_LimitDate) AS F_LimitDate                 /* 初回期限 */
    ,   DATE(cc.ClaimDate) AS ClaimDate                     /* 最終請求 */
    ,   DATE(cc.LimitDate) AS LimitDate                     /* 支払期限 */
        /* 請求回 */
    ,   (SELECT Class2
         FROM   M_Code
         WHERE  CodeId  =   12
         AND    KeyCode =   cc.ClaimPattern
        ) AS ClaimCaption
    ,   CONCAT(mc.KeyContent, '顧客') AS IncreCaption       /* 属性 */
    ,   c.NameKj                                            /* 請求先氏名 */
    ,   c.UnitingAddress                                    /* 請求先住所 */
    ,   cc.UseAmountTotal AS UseAmount                      /* 元金額 */
    ,   cc.ClaimAmount                                      /* 請求額 */
        /* 入金形態 */
    ,   (SELECT KeyContent FROM M_Code WHERE CodeId = 198 AND KeyCode = MAX(rc.ReceiptClass)) AS ReceiptClass /* 入金形態 */
    ,   MAX(DATE(rc.ReceiptDate)) AS ReceiptDate            /* 入金日 */
    ,   SUM(rc.ReceiptAmount) AS ReceiptAmount              /* 実入金額 */
        /* 比較回数 */
    ,   CASE
            WHEN cc.ClaimPattern IN (1, 2, 3, 4) THEN '初回'
            WHEN cc.ClaimPattern IN (5, 6) THEN '再１'
            WHEN cc.ClaimPattern IN (7, 8, 9) THEN '再３'
            ELSE ''
        END AS ClaimPattern
    ,   cc.MinClaimAmount                                   /* 比較金額 */
    ,   cc.ClaimedBalance                                   /* 過不足金 */
    ,   cc.ClaimId                                          /* 請求ID（非表示項目） */
FROM    T_Order o
        INNER JOIN T_Customer c ON (o.OrderSeq = c.OrderSeq)
        INNER JOIN T_ClaimControl cc ON (o.P_OrderSeq = cc.OrderSeq)
        INNER JOIN T_ReceiptControl rc ON (o.P_OrderSeq = rc.OrderSeq)
        LEFT OUTER JOIN M_Code mc ON (mc.KeyCode = CASE
                                                        WHEN c.Incre_ArTel = 5 OR c.Incre_ArAddr = 5 THEN 5
                                                        WHEN c.Incre_ArTel = 4 OR c.Incre_ArAddr = 4 THEN 4
                                                        WHEN c.Incre_ArTel = 3 OR c.Incre_ArAddr = 3 THEN 3
                                                        WHEN c.Incre_ArTel = 2 OR c.Incre_ArAddr = 2 THEN 2
                                                        WHEN c.Incre_ArTel = 1 OR c.Incre_ArAddr = 1 THEN 1
                                                        ELSE -1
                                                   END
                                      AND mc.CodeId = 4
                                     )
WHERE    o.OrderSeq = :OrderSeq
GROUP BY o.OrderSeq
EOQ;
	    return $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq));
	}

    /**
	 * 返金指示入金口座・返金情報ベースデータの取得
	 *
	 * @param int $oseq 注文SEQ
	 * @return ResultInterface SQL実行結果
	 */
	protected function getRepaymentControlBaseData($oseq)
	{
	    $sql =<<<EOQ
SELECT  o.BankName                                       /* 銀行名 */
    ,   o.FfCode                                         /* 銀行番号 */
    ,   o.BranchName                                     /* 支店名 */
    ,   o.FfBranchCode                                   /* 支店番号 */
    ,   o.FfAccountClass                                 /* 口座種別 */
    ,   o.AccountNumber                                  /* 口座番号 */
    ,   o.AccountHolder                                  /* 口座名義 */
    ,   o.TransferCommission                             /* 振込手数料 */
    ,   o.TransferAmount                                 /* 振込金額 */
    ,   o.RepayExpectedDate                              /* 返金予定日 */
    ,   o.ProcessClass                                   /* 処理方法 */
FROM    T_RepaymentControl o
WHERE    o.CoRecvNum = :OrderSeq
ORDER BY RepaySeq DESC
EOQ;
	    return $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq));
	}

	/**
	 * 返金指示入力画面(登録処理)
	 */
	public function repaysaveAction()
	{
        $params = $this->getParams();

        $data = $params['form'];

        // 返金指示ベースデータの取得
        $row = $this->getRepayeditBaseData($data['oseq'])->current();

        $errors = $this->validate($data);

        // count関数対策
        if (!empty($errors)) {
            // エラーがあればエラーメッセージをセット
            $this->view->assign('error', $errors);

            $this->view->assign('row', $row);
            $this->view->assign('data', $data);

            // コードマスターから事業者情報向けのマスター連想配列を作成し、ビューへアサインしておく
            $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
            $masters = array(
                    'FfAccountClass' => $codeMaster->getAccountClassMaster(),
                    'TcClass' => $codeMaster->getMasterCodes(104, array(0 => '-----')),
                    'RcptMethod' => array(0 => '銀行振込', 1 => '書留郵便', 2 => '雑収入処理', 3 => 'ネットDE受取' ),
            );
            $this->view->assign('master_map', $masters);

            $this->setTemplate('repayedit');
            return $this->view;
        }

        // 更新処理実施
        $errorCount = 0;
        $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
        try {
            // ユーザIDの取得
            $userTable = new \models\Table\TableUser($this->app->dbAdapter);
            $userID = $userTable->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

            // 振込手数料判定（2015/08/25 関数のコールを廃止／コードマスタから取得する。）
//             $transferCommission = BaseGeneralUtils::getTransferCommission($data["TcClass"], $data["paymentAmount"], false);
            $sql = "SELECT Class1 FROM M_Code WHERE CodeId = 104 AND KeyCode = :KeyCode";
            $transferCommission = $this->app->dbAdapter->query($sql)->execute(array(':KeyCode' => $data["TcClass"]))->current()['Class1'];

            // 1.返金管理テーブルへの登録
            $mdlpc = new \models\Table\TableRepaymentControl($this->app->dbAdapter);
            $savedata = array(
                    'RepayStatus'        => 0,
                    'IndicationDate'     => date('Y-m-d H:i:s'),
                    'ProcessClass'       => $data['RcptMethod'],
                    'BankName'           => $data['FfName'],
                    'FfCode'             => $data['FfCode'],
                    'BranchName'         => $data['FfBranchName'],
                    'FfBranchCode'       => $data['FfBranchCode'],
                    'FfAccountClass'     => $data['FfAccountClass'],
                    'AccountNumber'      => $data['FfAccountNumber'],
                    'AccountHolder'      => $data['FfAccountName'],
                    'TransferCommission' => $transferCommission,
                    'TransferAmount'     => $data['paymentAmount'],
                    'RepayAmount'        => $data['paymentAmount'] + $transferCommission,
                    'RepayExpectedDate'  => $data['repayExpectedDate'],
                    'ClaimId'            => $row['ClaimId'],
                    'CheckingUseAmount'  => $data['paymentAmount'],
                    'NetStatus'          => 0,
                    'CoRecvNum'          => $row['OrderId'],
                    'CoYoyakuNum'        => null,
                    'CoTranLimit'        => date('Ymd0000', strtotime($data['repayExpectedDate'])),
                    'CoWcosId'           => $row['OrderId'],
                    'CoWcosPassword'     => null,
                    'CoWcosUrl'          => null,
                    'CoTranReqDate'      => null,
                    'CoTranProcDate'     => null,
                    'MailFlg'            => 9,
                    'MailRetryCount'     => 0,
                    'RegistId'           => $userID,
                    'UpdateId'           => $userID,
            );

            $repaySeq = $mdlpc->saveNew($savedata);     // 2015/10/06 Y.Suzuki 会計対応 Mod

            // 2015/10/06 Y.Suzuki Add 会計対応 Stt
            $mdlatrc = new ATableRepaymentControl($this->app->dbAdapter);
            $mdlatrc->saveNew(array('RepaySeq' => $repaySeq));
            // 2015/10/06 Y.Suzuki Add 会計対応 End

            $this->app->dbAdapter->getDriver()->getConnection()->commit();
        }
        catch(\Exception $err) {
            $this->app->dbAdapter->getDriver()->getConnection()->rollBack();
            $errorCount = 1;
        }

        $this->view->assign('errorCount', $errorCount);
        $this->view->assign('link', $this->getBaseUrl() . '/rworder/detail/oseq/' . $data['oseq']);

        return $this->view;
	}

	/**
	 * 登録フォームの内容を検証する
	 * @param array $data 登録フォームデータ
	 * @return array エラーメッセージの配列
	 */
	protected function validate(array $data)
	{
        $errors = array();

        if ($data['RcptMethod'] == 0) {

            // 金融機関:銀行名
            $key = 'FfName';
            if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
                $errors[$key] = array("'銀行名'は必須です");
            }

            // 金融機関:銀行番号
            $key = 'FfCode';
            if (!isset($errors[$key]) && (strlen($data[$key]) > 0) && !(is_numeric($data[$key]))) {
                $errors[$key] = array("'銀行番号'の形式が不正です");
            }
            if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
                $errors[$key] = array("'銀行番号'は必須です");
            }

            // 金融機関:支店名
            $key = 'FfBranchName';
            if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
                $errors[$key] = array("'支店名'は必須です");
            }

            // 金融機関:支店番号
            $key = 'FfBranchCode';
            if (!isset($errors[$key]) && (strlen($data[$key]) > 0) && !(is_numeric($data[$key]))) {
                $errors[$key] = array("'支店番号'の形式が不正です");
            }
            if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
                $errors[$key] = array("'支店番号'は必須です");
            }

            // 金融機関:口座種別（初期値を与える仕様としており、必須条件は必ず満たす故チェック不要）
            // 口座種別CSVインポート機能追加の為
            $key = 'FfAccountClass';
            if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
                $errors[$key] = array("'口座種別'は必須です");
            }

            // 金融機関:口座番号
            $key = 'FfAccountNumber';
            if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
                $errors[$key] = array("'口座番号'は必須です");
            }

            // 金融機関:口座名義
            $key = 'FfAccountName';
            if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
                $errors[$key] = array("'口座名義'は必須です");
            }

            // 金融機関:振込手数料
            $key = 'TcClass';
            if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
                $errors[$key] = array("'振込手数料'を指定してください");
            }
            if (!isset($errors[$key]) && !(is_numeric($data[$key]))) {
                $errors[$key] = array("'振込手数料'の指定が不正です");
            }
            if (!isset($errors[$key]) && !((int)$data[$key] > 0)) {
                $errors[$key] = array("'振込手数料'の指定が不正です");
            }
        }

        // 返金情報:振込金額
        $key = 'paymentAmount';
        if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
            $errors[$key] = array("'振込金額'が指定されていません");
        }
        if (!isset($errors[$key]) && !is_numeric($data[$key])) {
            $errors[$key] = array("'振込金額'の指定が不正です");
        }

        // 返金情報:振込金額 + 振込手数料が過剰金を超える場合にはエラー
        // 振込手数料 取得
        $sql = "SELECT Class1 FROM M_Code WHERE CodeId = 104 AND KeyCode = :KeyCode";
        $transferCommission = $this->app->dbAdapter->query($sql)->execute(array(':KeyCode' => $data["TcClass"]))->current()['Class1'];
        // 過剰金 取得
        $sql = "SELECT (ClaimedBalance * -1) AS ClaimedBalance FROM T_ClaimControl WHERE OrderSeq = :OrderSeq ";
        $claimedBalance = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $data["oseq"]))->current()['ClaimedBalance'];
        if (!isset($errors[$key]) && ((int)$data[$key] + (int)$transferCommission > (int)$claimedBalance)) {
            $errors[$key] = array("'振込金額'は過剰金を超えて入力できません");
        }

        // 返金情報:返金予定日
        $key = 'repayExpectedDate';
        if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
            $errors[$key] = array("'返金予定日'を入力してください");
        }
        if (!isset($errors[$key]) && !IsValidFormatDate($data[$key])) {
            $errors[$key] = array("'返金予定日'の形式が不正です");
        }

        /* ネットDE受取を選択したとき */
        if ($data['RcptMethod'] == 3) {
            // 金融機関:振込手数料
            $key = 'TcClass';
            if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
                $errors[$key] = array("'振込手数料'を指定してください");
            }
            if (!isset($errors[$key]) && !(is_numeric($data[$key]))) {
                $errors[$key] = array("'振込手数料'の指定が不正です");
            }
            if (!isset($errors[$key]) && !((int)$data[$key] > 0)) {
                $errors[$key] = array("'振込手数料'の指定が不正です");
            }
        }

        return $errors;
	}

    /**
     * 過不足入金CSVインポートフォームの表示
     */
    public function lackformAction()
    {
        return $this->view;
    }

    /**
     * 過不足入金CSVインポートフォームの登録
     */
    public function lackAction()
    {
        $tmpName = $_FILES["cres"]["tmp_name"];
        $handle = null;
        $items[] = null;

        try{
            // ユーザーIDの取得
            $obj = new \models\Table\TableUser($this->app->dbAdapter);
            $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

            if ($tmpName == '') {
                $this->view->assign('message', '<span style="font-size: 18px; color: red;">CSVファイルのオープンに失敗しました。<br />ファイルを選択してください。</span>');
                $this->view->assign('items', $items);
                return $this->view;
            }
            $handle = fopen($tmpName, "r");

            if (!$handle) {
                // ファイルオープン失敗
                $message = '<span style="font-size: 18px; color: red;">CSVファイルのオープンに失敗しました。<br />再試行してください。</span>';
            }
            else {
                $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
                
                while (($data = $this->fgetcsv_reg($handle, 200, ",")) !== false) {
                    // 現在行をカウント(1からスタート！)
                    $cntLine += 1;

                    // 1行目のヘッダ行はスキップ
                    if ($cntLine <= 1) {
                        continue;
                    }

                    // --------------------------------
                    // 行データ取得
                    // --------------------------------
                    $data = mb_convert_encoding($data, 'UTF-8', 'sjis-win');
                    $repayExpectedDate = trim($data[0]);    // 振込日
                    if($repayExpectedDate !==""){
                    $repayExpectedDate = date("Y-m-d",strtotime($repayExpectedDate));
                    }
                    $OemId             = trim($data[1]);    // 注文ID
                    $FfName            = trim($data[2]);    // 銀行名
                    $FfBranchName      = trim($data[3]);    // 支店名
                    $FfCode            = trim($data[4]);    // 銀行番号
                    $FfBranchCode      = trim($data[5]);    // 支店番号
                    $FfAccountClass    = trim($data[6]);    // 口座種別
                    $FfAccountNumber   = trim($data[7]);    // 口座番号
                    $FfAccountName     = trim($data[8]);    // 口座名義
                    $paymentAmount     = trim($data[9]);    // 振込額
                    $TcClass           = trim($data[10]);   // 振込手数料
                    $RcptMethod        = 0;                 // 処理区分初期値：銀行振込
                    $row = $this->getcsvRepayeditBaseData($OemId)->current();
                    $oseq = trim($row["OrderSeq"]);
                    $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
                    $masters = array(
                        'TcClass' => $codeMaster->getMasterCodes(104),
                    );
                    $numbers = $TcClass;
                    if($numbers === "0"){
                          $target = sprintf('なし（%s円）',$numbers);
                    }else{
                         $target = sprintf('あり（%s円）',$numbers);
                    }
                    $key = array_keys($masters['TcClass'],$target);
                    $tcClasscheck = $key[0];
                    $items[] = array(
                        'RcptMethod'        => $RcptMethod,
                        'OemId'             => $OemId,
                        'FfName'            => $FfName,
                        'FfCode'            => $FfCode,
                        'FfBranchName'      => $FfBranchName,
                        'FfBranchCode'      => $FfBranchCode,
                        'FfAccountClass'    => $FfAccountClass,
                        'FfAccountNumber'   => $FfAccountNumber,
                        'FfAccountName'     => $FfAccountName,
                        'TcClass'           => $TcClass,
                        'paymentAmount'     => $paymentAmount,
                        'repayExpectedDate' => $repayExpectedDate,
                    );
                    $check = array(
                        'RcptMethod'        => $RcptMethod,
                        'OemId'             => $OemId,
                        'FfName'            => $FfName,
                        'FfCode'            => $FfCode,
                        'FfBranchName'      => $FfBranchName,
                        'FfBranchCode'      => $FfBranchCode,
                        'FfAccountClass'    => $FfAccountClass,
                        'FfAccountNumber'   => $FfAccountNumber,
                        'FfAccountName'     => $FfAccountName,
                        'TcClass'           => $tcClasscheck,
                        'paymentAmount'     => $paymentAmount,
                        'repayExpectedDate' => $repayExpectedDate,
                        'oseq'              => $oseq,
                        'csv'               => 1,
                    );
                    $errors = $this->validate($check);
                    // count関数対策
                    if (!empty($errors)) {
                      // エラーがあればエラーメッセージをセット
                      $message = sprintf('<span style="font-size: 18px; color: red;">CSVファイルの%s行目にエラーがあります。<br />ファイルを修正してください。</span>',$cntLine);
                      $this->view->assign('message', $message);
                      $this->view->assign('items', $items);
                      return $this->view;
                    }
                    if($cntLine > 201){
                        $message = '<span style="font-size: 18px; color: red;">CSV件数が200件を超えています。<br />ファイルを修正してください。</span>';
                        $this->view->assign('message', $message);
                        $this->view->assign('items', $items);
                        return $this->view;
                    }
                }
                fclose($handle);
                // 更新処理実施
                $errorCount = 0;
                // $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
        
                // ユーザIDの取得
                $userTable = new \models\Table\TableUser($this->app->dbAdapter);
                $userID = $userTable->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

                // 振込手数料判定（2015/08/25 関数のコールを廃止／コードマスタから取得する。）
                // $transferCommission = BaseGeneralUtils::getTransferCommission($data["TcClass"], $data["paymentAmount"], false);
                $sql = "SELECT Class1 FROM M_Code WHERE CodeId = 104 AND KeyCode = :KeyCode";
                // $transferCommission = $this->app->dbAdapter->query($sql)->execute(array(':KeyCode' => $TcClass))->current()['Class1'];

                // 1.返金管理テーブルへの登録
                $mdlpc = new \models\Table\TableRepaymentControl($this->app->dbAdapter);
                foreach($items as $item){
                $savedata[] = array(
                    'RepayStatus'        => 0,
                    'IndicationDate'     => date('Y-m-d H:i:s'),
                    'ProcessClass'       => $item['RcptMethod'],
                    'BankName'           => $item['FfName'],
                    'FfCode'             => $item['FfCode'],
                    'BranchName'         => $item['FfBranchName'],
                    'FfBranchCode'       => $item['FfBranchCode'],
                    'FfAccountClass'     => $item['FfAccountClass'],
                    'AccountNumber'      => $item['FfAccountNumber'],
                    'AccountHolder'      => $item['FfAccountName'],
                    'TransferCommission' => $item['TcClass'],
                    'TransferAmount'     => $item['paymentAmount'],
                    'RepayAmount'        => $item['paymentAmount'] + $item['TcClass'],
                    'RepayExpectedDate'  => $item['repayExpectedDate'],
                    'ClaimId'            => $row['ClaimId'],
                    'CheckingUseAmount'  => $item['paymentAmount'],
                    'NetStatus'          => 0,
                    'CoRecvNum'          => $item['OemId'],
                    'CoYoyakuNum'        => null,
                    'CoTranLimit'        => date('Ymd0000', strtotime($item['repayExpectedDate'])),
                    'CoWcosId'           => $item['OemId'],
                    'CoWcosPassword'     => null,
                    'CoWcosUrl'          => null,
                    'CoTranReqDate'      => null,
                    'CoTranProcDate'     => null,
                    'MailFlg'            => 9,
                    'MailRetryCount'     => 0,
                    'RegistId'           => $userID,
                    'UpdateId'           => $userID,
                );
                }
                unset($savedata[0]);
                foreach($savedata as $save){

                $repaySeq = $mdlpc->saveNew($save);     // 2015/10/06 Y.Suzuki 会計対応 Mod

                // 2015/10/06 Y.Suzuki Add 会計対応 Stt
                $mdlatrc = new ATableRepaymentControl($this->app->dbAdapter);
                $mdlatrc->saveNew(array('RepaySeq' => $repaySeq));
                // 2015/10/06 Y.Suzuki Add 会計対応 End
                }
                $this->app->dbAdapter->getDriver()->getConnection()->commit();

                $message = sprintf("「%s」　をインポートしました。", f_e($_FILES["cres"]["name"]));
                $this->app->logger->info(' impneturlAction completed(' . $message . ') ');
            }
        }catch(\Exception $e) {
           $message = $e->getMessage();
           $this->app->dbAdapter->getDriver()->getConnection()->rollBack();
           $this->app->logger->info(' impneturlAction error(' . $message . ') ');
           // (初期化処理)
           if ($handle) { fclose($handle); }
           $listNa = array();
        }

        $this->view->assign('message', $message);
        $this->view->assign('items', $items);
        return $this->view;
    }

    /**
	 * 返金指示ベースデータの取得(注文IDから)
	 *
	 * @param int $oseq 注文ID
	 * @return ResultInterface SQL実行結果
	 */
	protected function getcsvRepayeditBaseData($oseq)
	{
	    $sql =<<<EOQ
SELECT  o.OrderSeq                                          /* 注文Seq（非表示項目） */
    ,   o.OrderId                                           /* 注文ID */
    ,   DATE(cc.F_LimitDate) AS F_LimitDate                 /* 初回期限 */
    ,   DATE(cc.ClaimDate) AS ClaimDate                     /* 最終請求 */
    ,   DATE(cc.LimitDate) AS LimitDate                     /* 支払期限 */
        /* 請求回 */
    ,   (SELECT Class2
         FROM   M_Code
         WHERE  CodeId  =   12
         AND    KeyCode =   cc.ClaimPattern
        ) AS ClaimCaption
    ,   CONCAT(mc.KeyContent, '顧客') AS IncreCaption       /* 属性 */
    ,   c.NameKj                                            /* 請求先氏名 */
    ,   c.UnitingAddress                                    /* 請求先住所 */
    ,   cc.UseAmountTotal AS UseAmount                      /* 元金額 */
    ,   cc.ClaimAmount                                      /* 請求額 */
        /* 入金形態 */
    ,   (SELECT KeyContent FROM M_Code WHERE CodeId = 198 AND KeyCode = MAX(rc.ReceiptClass)) AS ReceiptClass /* 入金形態 */
    ,   MAX(DATE(rc.ReceiptDate)) AS ReceiptDate            /* 入金日 */
    ,   SUM(rc.ReceiptAmount) AS ReceiptAmount              /* 実入金額 */
        /* 比較回数 */
    ,   CASE
            WHEN cc.ClaimPattern IN (1, 2, 3, 4) THEN '初回'
            WHEN cc.ClaimPattern IN (5, 6) THEN '再１'
            WHEN cc.ClaimPattern IN (7, 8, 9) THEN '再３'
            ELSE ''
        END AS ClaimPattern
    ,   cc.MinClaimAmount                                   /* 比較金額 */
    ,   cc.ClaimedBalance                                   /* 過不足金 */
    ,   cc.ClaimId                                          /* 請求ID（非表示項目） */
FROM    T_Order o
        INNER JOIN T_Customer c ON (o.OrderSeq = c.OrderSeq)
        INNER JOIN T_ClaimControl cc ON (o.P_OrderSeq = cc.OrderSeq)
        INNER JOIN T_ReceiptControl rc ON (o.P_OrderSeq = rc.OrderSeq)
        LEFT OUTER JOIN M_Code mc ON (mc.KeyCode = CASE
                                                        WHEN c.Incre_ArTel = 5 OR c.Incre_ArAddr = 5 THEN 5
                                                        WHEN c.Incre_ArTel = 4 OR c.Incre_ArAddr = 4 THEN 4
                                                        WHEN c.Incre_ArTel = 3 OR c.Incre_ArAddr = 3 THEN 3
                                                        WHEN c.Incre_ArTel = 2 OR c.Incre_ArAddr = 2 THEN 2
                                                        WHEN c.Incre_ArTel = 1 OR c.Incre_ArAddr = 1 THEN 1
                                                        ELSE -1
                                                   END
                                      AND mc.CodeId = 4
                                     )
WHERE    o.OrderId = :OrderSeq
GROUP BY o.OrderSeq
EOQ;
	    return $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq));
	}

	/**
	 * 返金指示確定待ち・履歴一覧
	 */
	public function histlistAction()
    {
        $params = $this->getParams();

        // [paging] 1ページあたりの項目数
        $ipp = 50;
        // [paging] 指定ページを取得
        $current_page = (isset($params['page'])) ? (int)$params['page'] : 1;
        if ($current_page < 1) $current_page = 1;

        $srchtype = (isset($params['srchtype'])) ? $params['srchtype'] : 1;
        $output = (isset($params['output'])) ? $params['output'] : 0;
        $oemId = (isset($params['oemId'])) ? $params['oemId'] : -1;
        $procclass = (isset($params['procclass'])) ? $params['procclass'] : -1;
        $netstatus = (isset($params['netstatus'])) ? $params['netstatus'] : -1;
        $indicationDateF = (isset($params['IndicationDateF'])) ? $params['IndicationDateF'] : null;
        $indicationDateT = (isset($params['IndicationDateT'])) ? $params['IndicationDateT'] : null;

        // 返金確定履歴の場合、以下項目を設定する。
        if ($srchtype == 2) {
            $df = (! empty($params['DecisionDateF'])) ? $params['DecisionDateF'] : date('Y-m-d');
            // 翌営業日を取得する。
            $sql = "SELECT MIN(BusinessDate) AS BusinessDate FROM T_BusinessCalendar WHERE BusinessDate > :BusinessDate AND BusinessFlg = 1";
            $ndate = $this->app->dbAdapter->query($sql)->execute(array(':BusinessDate' => date('Y-m-d')))->current()['BusinessDate'];
            $dt = (! empty($params['DecisionDateT'])) ? $params['DecisionDateT'] : $ndate;
        }

        // ベースクエリの取得
        $sql =<<<EOQ
SELECT  o.OrderSeq                                                  /* 注文Seq（非表示項目） */
    ,   o.OrderId                                                   /* 注文ID */
    ,   DATE_FORMAT(cc.F_LimitDate, '%m/%d') AS F_LimitDate         /* 初回期限 */
    ,   DATE_FORMAT(cc.ClaimDate, '%m/%d') AS ClaimDate             /* 最終請求 */
    ,   DATE_FORMAT(cc.LimitDate, '%m/%d') AS LimitDate             /* 支払期限 */
    /* 請求 */
    ,   (SELECT Class2
         FROM   M_Code
         WHERE  CodeId = 12
         AND    KeyCode = cc.ClaimPattern) AS ClaimCaption
    ,   mc.Class2 AS IncreCaption                                   /* 属性 */
    ,   c.NameKj                                                    /* 請求先氏名 */
    ,   cc.ClaimAmount                                              /* 請求金額 */
    /* 入金形態 */
    ,   (SELECT KeyContent FROM M_Code WHERE CodeId = 198 AND KeyCode = MAX(rc.ReceiptClass)) AS ReceiptClass /* 入金形態 */
    ,   MAX(DATE(rc.ReceiptDate)) AS ReceiptDate                    /* 入金日 */
    ,   SUM(rc.ReceiptAmount) AS ReceiptAmount                      /* 実入金額 */
    ,   (SUM(rc.ReceiptAmount) - cc.ClaimAmount) AS SurplusAmount   /* 過剰金 */
    ,   rpc.RepayAmount                                             /* 返金金額 */
    ,   DATE(rpc.RepayExpectedDate) AS RepayExpectedDate            /* 返金予定日 */
    ,   rpc.RepaySeq                                                /* 返金Seq（非表示項目） */
    ,   cc.ClaimId                                                  /* 請求ID（非表示項目） */
    ,   c.CustomerId
    ,   mc188.KeyContent AS NetStatusCaption
FROM    T_Order o
        INNER JOIN T_Customer c ON (o.OrderSeq = c.OrderSeq)
        INNER JOIN T_ClaimControl cc ON (o.OrderSeq = cc.OrderSeq)
        INNER JOIN T_ReceiptControl rc ON (o.OrderSeq = rc.OrderSeq)
        INNER JOIN T_RepaymentControl rpc ON (rc.ClaimId = rpc.ClaimId)
        LEFT OUTER JOIN M_Code mc ON (mc.KeyCode = CASE
                                                        WHEN c.Incre_ArTel = 5 OR c.Incre_ArAddr = 5 THEN 5
                                                        WHEN c.Incre_ArTel = 4 OR c.Incre_ArAddr = 4 THEN 4
                                                        WHEN c.Incre_ArTel = 3 OR c.Incre_ArAddr = 3 THEN 3
                                                        WHEN c.Incre_ArTel = 2 OR c.Incre_ArAddr = 2 THEN 2
                                                        WHEN c.Incre_ArTel = 1 OR c.Incre_ArAddr = 1 THEN 1
                                                        ELSE -1
                                                   END
                                      AND mc.CodeId = 4
                                     )
        LEFT OUTER JOIN M_Code mc188 ON (mc188.CodeId = 188 AND mc188.KeyCode = rpc.NetStatus AND rpc.ProcessClass = 3)
WHERE 1 = 1
EOQ;

        // 検索条件の考慮
        $where = " ";
        if ($oemId != -1) {
            // OEMが選択されている場合
            $where .= " AND IFNULL(o.OemId, 0) = " . $oemId;
        }

        // 処理方法
        if ($procclass >= 0) {
            $where .= " AND rpc.ProcessClass = " . $procclass;
        }

        // ﾈｯﾄDE受取ｽﾃｰﾀｽ
        if ($netstatus >= 0) {
            $where .= " AND rpc.ProcessClass = 3 AND rpc.NetStatus = " . $netstatus;
        }

        // 返金指示日
        $wIndicationDate = BaseGeneralUtils::makeWhereDateTime(
            'rpc.IndicationDate',
            BaseGeneralUtils::convertWideToNarrow($indicationDateF),
            BaseGeneralUtils::convertWideToNarrow($indicationDateT)
        );
        if ($wIndicationDate != '') {
            $where .= " AND " . $wIndicationDate;
        }

        if ($srchtype == 2) {
            // 返金確定履歴の検索
            $where .= " AND rpc.RepayStatus = 1 ";

            // 確定日時
            $wDecisionDate = BaseGeneralUtils::makeWhereDateTime(
                'rpc.DecisionDate',
                BaseGeneralUtils::convertWideToNarrow($df),
                BaseGeneralUtils::convertWideToNarrow($dt)
            );
            if ($wDecisionDate != '') {
                $where .= " AND " . $wDecisionDate;
            }
            // 振込データDL状態
            if ($output <> 0) {
                // 「全て」以外の場合、処理方法が「銀行振込」データを検索
                $where .= " AND rpc.ProcessClass = 0";
                if ($output == 1) {
                    // 振込データがダウンロード済みのデータを検索
                    $where .= " AND rpc.OutputFileSeq IS NOT NULL";
                } elseif ($output == 2) {
                    // 振込データが未ダウンロードのデータを検索
                    $where .= " AND rpc.OutputFileSeq IS NULL";
                }
            }
        }
        else {
            // 返金確定待ちの検索
            $where .= " AND rpc.RepayStatus = 0 ";
        }

        // 検索条件保存
        $_SESSION[self::SESS_SEARCH_INFO] = $where;

        $sql .= $where;

        $sql .= " GROUP BY rpc.RepaySeq ";

        // 検索
        $data= ResultInterfaceToArray($this->app->dbAdapter->query($sql)->execute(null));

        // count関数対策
        $dataLen = 0;
        if(!empty($data)) {
            $dataLen = count($data);
		}

        // 返金管理SEQのリスト作成
        $arrRepaySeqs = array();
        for ($i = 0 ; $i < $dataLen ; $i++) {
            $arrRepaySeqs[] = $data[$i]['RepaySeq'];
        }
        $repaySeqs = join(',', $arrRepaySeqs);

        // [paging] ページャ初期化
        $pager = new CoralPager($dataLen, $ipp);
        // [paging] 指定ページを補正
        if( $current_page > $pager->getTotalPage() ) $current_page = $pager->getTotalPage();
        // [paging] 対象リストをページング情報に基づいて対象リストをスライス
        if( $dataLen > 0 ) $data = array_slice( $data, $pager->getStartIndex( $current_page ), $ipp );
        // [paging] ページングナビゲーション情報
        $page_links = array( 'base' => "rwsprcpt/histlist/page" );

        $page_links = array( 'base' => "rwsprcpt/histlist/oemId/" . $oemId
                . '/output/' . $output
                . '/srchtype/' . $srchtype
                . '/DecisionDateF/' . $df
                . '/DecisionDateT/' . $dt
                . '/procclass/' . $procclass
                . '/netstatus/' . $netstatus
                . '/IndicationDateF/' . $indicationDateF
                . '/IndicationDateT/' . $indicationDateT
                . '/page' );
        $page_links['prev'] = $page_links['base'] . '/' . ( $current_page - 1 );
        $page_links['next'] = $page_links['base'] . '/' . ( $current_page + 1 );
        // [paging] ページング関連の情報をビューへアサイン
        $this->view->assign( 'current_page', $current_page );
        $this->view->assign( 'pager', $pager );
        $this->view->assign( 'page_links', $page_links );

        $this->view->assign('srchtypeTag',BaseHtmlUtils::SelectTag("srchtype", array (1 => '返金確定待ち', 2 => '返金確定履歴'), $srchtype, 'onChange="javascript:changeSrchtype(); "'));
        $this->view->assign('DecisionDateF', $df);
        $this->view->assign('DecisionDateT', $dt);
        $this->view->assign('list', $data);
        $this->view->assign('srchtype', $srchtype);
        // OEMIDと名前のリスト取得
        $ri = $this->app->dbAdapter->query("SELECT 0 AS OemId, 'キャッチボール' AS OemNameKj UNION ALL SELECT OemId, OemNameKj FROM T_Oem ORDER BY OemId")->execute(null);
        $oem = ResultInterfaceToArray($ri);
        foreach ($oem as $key => $value) {
            $oemList[$value['OemId']] = $value['OemNameKj'];
        }

        $this->view->assign('oemList', $oemList);
        $this->view->assign('outputTag',BaseHtmlUtils::SelectTag("output", array (0 => '全て', 1 => '出力済み', 2 => '未出力'), $output, 'onChange="javascript:changeOutput(); "'));
        $this->view->assign('output', $output);
        $this->view->assign('selectOemId', $oemId);

        // 処理方法
        $this->view->assign('procclassTag',BaseHtmlUtils::SelectTag("procclass", array(-1 => '全て', 0 => '銀行振込', 1 => '書留郵便', 2 => '雑収入処理', 3 => 'ネットDE受取' ), $procclass));

        // ネットDE受取ステータス
        $ccm = new CoralCodeMaster($this->app->dbAdapter);
        $this->view->assign('statusTag',BaseHtmlUtils::SelectTag("netstatus", $ccm->getNetStatusMaster(), $netstatus));

        // 返金指示日
        $this->view->assign('IndicationDateF',$indicationDateF);
        $this->view->assign('IndicationDateT',$indicationDateT);

        // 返金管理SEQ全ページ分
        $this->view->assign('RepaySeqs',$repaySeqs);

        $disable = 0;
        // 振込データダウンロードボタンの有効無効を設定。
        // 返金確定履歴 かつ 未出力 かつ 検索結果 が 空でない場合、振込データＤＬボタンは有効とする。
        if ($srchtype == 2 && $output == 2 && (! empty($data))) {
            $disable = 1;
        }
        $this->view->assign('disable', $disable);

        return $this->view;
    }

    /**
     * (Ajax)返金指示キャンセル
     */
    public function repaycancelAction()
    {
        $updatecount = 0;   // 更新件数

        try
        {
            $params = $this->getParams();

            // ユーザーIDの取得
            $obj = new \models\Table\TableUser($this->app->dbAdapter);
            $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

            $mdlrc = new \models\Table\TableRepaymentControl($this->app->dbAdapter);

            $i = 0;

            while (isset($params['RepaySeq' . $i]))
            {
                if (!isset($params['chkCancel' . $i])) { $i++; continue; }

                $mdlrc->saveUpdate(array('RepayStatus' => 9, 'UpdateId' => $userId), $params['RepaySeq' . $i]);

                $i++;

                $updatecount++;
            }

            // 成功指示
            $msg = '1';
        }
        catch(\Exception $e)
        {
            $msg = $e->getMessage();
        }

        echo \Zend\Json\Json::encode(array('status' => $msg, 'updatecount' => $updatecount));
        return $this->response;
    }

    /**
     * (Ajax)返金指示確定
     */
    public function repaydecisionAction()
    {
        $updatecount = 0;   // 更新件数

        $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
        try
        {
            $params = $this->getParams();

            // ユーザーIDの取得
            $obj = new \models\Table\TableUser($this->app->dbAdapter);
            $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

            $mdlrc = new \models\Table\TableRepaymentControl($this->app->dbAdapter);
            $mdlcc = new \models\Table\TableClaimControl($this->app->dbAdapter);

            $i = 0;

            while (isset($params['RepaySeq' . $i]))
            {
                if (!isset($params['chkDecision' . $i])) { $i++; continue; }

                $ccrow = $mdlcc->find($params['ClaimId' . $i])->current();

                // 返金管理更新
                $mdlrc->saveUpdate(array('RepayStatus' => 1, 'DecisionDate' =>  date('Y-m-d H:i:s'), 'UpdateId' => $userId), $params['RepaySeq' . $i]);

                // 請求管理更新
                $ccrow = $mdlcc->find($params['ClaimId' . $i])->current();
                $saveccdata = array(
                    'ClaimedBalance' => ($ccrow['ClaimedBalance'] + $params['RepayAmount' . $i]),
                    'RepayAmountTotal' => ($ccrow['RepayAmountTotal'] + $params['RepayAmount' . $i]),
                    'CheckingClaimAmount' => ($ccrow['CheckingClaimAmount'] - $params['RepayAmount' . $i]),
                    'CheckingUseAmount' => ($ccrow['CheckingUseAmount'] - $params['RepayAmount' . $i]),
                    'BalanceClaimAmount' => ($ccrow['BalanceClaimAmount'] + $params['RepayAmount' . $i]),
                    'BalanceUseAmount' => ($ccrow['BalanceUseAmount'] + $params['RepayAmount' . $i]),
                    'UpdateId' => $userId,
                );
                $mdlcc->saveUpdate($saveccdata, $params['ClaimId' . $i]);

                // 注文情報を取得する（請求に紐づく注文の件数、履歴登録する）
                $mdlo = new TableOrder($this->app->dbAdapter);
                $datas = $mdlo->findOrder(array('P_OrderSeq' => $ccrow["OrderSeq"], 'Cnl_Status' => 0));
                foreach ($datas as $data) {
                    // 注文履歴へ登録
                    $history = new CoralHistoryOrder($this->app->dbAdapter);
                    $history->InsOrderHistory($data["OrderSeq"], 62, $userId);
                }

                $i++;

                $updatecount++;
            }

            $this->app->dbAdapter->getDriver()->getConnection()->commit();

            // 成功指示
            $msg = '1';
        }
        catch(\Exception $e)
        {
            $this->app->dbAdapter->getDriver()->getConnection()->rollBack();
            $msg = $e->getMessage();
        }

        echo \Zend\Json\Json::encode(array('status' => $msg, 'updatecount' => $updatecount));
        return $this->response;
    }

    /**
     * ネットDE受取URL・PW取得 フォームの表示
     */
    public function impneturlformAction()
    {
        return $this->view;
    }

    /**
     * ネットDE受取URL・PW取得
     */
    public function impneturlAction()
    {
        $mdlrc = new TableRepaymentControl($this->app->dbAdapter);
        $history = new CoralHistoryOrder($this->app->dbAdapter);

        $tmpName = $_FILES["cres"]["tmp_name"];

        $listNa = array();  // 対象外リスト
        $cntSumLine = 0;    // 総件数(ヘッダ込みなので-1)
        $cntErrLine = 0;    // エラー件数
        $cntNrmLine = 0;    // 総件数 - エラー件数 = 正常件数

        $handle = null;
        try {
            // ユーザーIDの取得
            $obj = new \models\Table\TableUser($this->app->dbAdapter);
            $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

            if ($tmpName == '') {
                $this->view->assign('message', '<span style="font-size: 18px; color: red;">CSVファイルのオープンに失敗しました。<br />ファイルを選択してください。</span>');
                $this->view->assign('listNa', $listNa);
                return $this->view;
            }
            $handle = fopen($tmpName, "r");

            if (!$handle) {
                // ファイルオープン失敗
                $message = '<span style="font-size: 18px; color: red;">CSVファイルのオープンに失敗しました。<br />再試行してください。</span>';
            }
            else {
                $this->app->logger->info(' impneturlAction start(' . $_FILES["cres"]["name"] . ' / filesize : ' . filesize($tmpName) . ') ');
                $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();

                $cntLine = 0;

                // 入金ループ
                while (($data = $this->fgetcsv_reg($handle, 1000, ",")) !== false) {
                    // 現在行をカウント(1からスタート！)
                    $cntLine += 1;

                    // 1行目のヘッダ行はスキップ
                    if ($cntLine <= 1) {
                        continue;
                    }

                    // --------------------------------
                    // 行データ取得
                    // --------------------------------
                    $result = trim($data[0]);           // 処理結果
                    $dataSyubetsu = trim($data[1]);     // データ種別
                    $coYoyakuNum = trim($data[2]);      // 受取番号
                    $coRecvNum = trim($data[3]);        // お客様番号
                    $coWcosId = trim($data[4]);         // WCOS ID
                    $coWcosPassword = trim($data[5]);   // WCOSパスワード
                    $coUrl = trim($data[6]);            // WCOS URL

                    // --------------------------------
                    // チェック処理
                    // --------------------------------
                    // 取込対象チェック
                    $sql = <<<EOQ
SELECT COUNT(rc.RepaySeq) AS CNT
     , MAX(IFNULL(o.Cnl_Status, 0)) AS Cnl_Status
     , MAX(rc.RepaySeq) AS RepaySeq
     , MAX(o.OrderSeq) AS OrderSeq
  FROM T_RepaymentControl rc
       INNER JOIN T_ClaimControl cc
               ON rc.ClaimId = cc.ClaimId
       INNER JOIN T_Order o
               ON cc.OrderSeq = o.OrderSeq
 WHERE 1 = 1
   AND rc.CoRecvNum = :CoRecvNum
   AND rc.RepayStatus = 0
   AND rc.NetStatus = 1
EOQ;
                    $row = $this->app->dbAdapter->query($sql)->execute(array(':CoRecvNum' => $coRecvNum))->current();
                    $cntCheck = (int)$row['CNT'];
                    $cnlStatus = $row['Cnl_Status'];
                    $repaySeq = $row['RepaySeq'];
                    $oseq = $row['OrderSeq'];

                    if ($cntCheck == 0) {
                        // 指示済みデータが存在しない
                        $listNa[] = array(
                                'RowNo'   => $cntLine,
                                'CoYoyakuNum' => $coYoyakuNum,
                                'CoRecvNum' => $coRecvNum,
                                'CoWcosId' => $coWcosId,
                                'Error'     => "指示済のデータが存在しません",
                        );
                        continue;

                    } elseif ($cntCheck > 1) {
                        // 指示済データが複数存在する
                        $listNa[] = array(
                                'RowNo'   => $cntLine,
                                'CoYoyakuNum' => $coYoyakuNum,
                                'CoRecvNum' => $coRecvNum,
                                'CoWcosId' => $coWcosId,
                                'Error'     => "指示済データが複数存在しているため、更新対象を特定出来ません",
                        );
                        continue;

                    }

                    // 注文のキャンセルチェック
                    if ($cnlStatus != 0) {
                        $listNa[] = array(
                                'RowNo'   => $cntLine,
                                'CoYoyakuNum' => $coYoyakuNum,
                                'CoRecvNum' => $coRecvNum,
                                'CoWcosId' => $coWcosId,
                                'Error'     => "注文が既にキャンセルされています",
                        );
                        continue;

                    }

                    // --------------------------------
                    // 更新処理
                    // --------------------------------
                    // 返金管理を更新する
                    $udata = array(
                        'NetStatus' => 2,
                        'CoYoyakuNum' => $coYoyakuNum,
                        'CoWcosUrl' => $coUrl,
                        'MailFlg' => 0,
                        'UpdateId' => $userId,
                    );
                    $mdlrc->saveUpdate($udata, $repaySeq);

                    // 注文履歴へ登録
                    $sql = 'SELECT OrderSeq FROM T_Order WHERE P_OrderSeq = :P_OrderSeq AND Cnl_Status = 0';
                    $ri = $this->app->dbAdapter->query($sql)->execute(array(':P_OrderSeq' => $oseq));
                    foreach ($ri as $row) {
                        $history->InsOrderHistory($row["OrderSeq"], 112, $userId);
                    }
                }

                fclose($handle);

                $this->app->dbAdapter->getDriver()->getConnection()->commit();

                // --------------------------------
                // 更新処理
                // --------------------------------
                $cntSumLine = $cntLine - 1;  // 総件数(ヘッダ込みなので-1)

                // count関数対策
                if(!empty($listNa)) {
                    $cntErrLine = count($listNa); // エラー件数
                }

                $cntNrmLine = $cntSumLine - $cntErrLine; // 総件数 - エラー件数 = 正常件数

                $message = sprintf("ネットDE受取URL・PWファイル　「%s」　をインポートしました。<BR>（対象件数：%d件　取込件数：%d件　エラー件数：%d件）", f_e($_FILES["cres"]["name"]), $cntSumLine, $cntNrmLine, $cntErrLine);
                $this->app->logger->info(' impneturlAction completed(' . $message . ') ');
            }
        } catch(\Exception $e) {
            $message = $e->getMessage();
            $this->app->dbAdapter->getDriver()->getConnection()->rollBack();
            $this->app->logger->info(' impneturlAction error(' . $message . ') ');
            // (初期化処理)
            if ($handle) { fclose($handle); }
            $listNa = array();
        }

        $this->view->assign('message', $message);
        $this->view->assign('listNa', $listNa);

        return $this->view;
    }

    /**
     * ネットDE受取返金結果反映 フォームの表示
     */
    public function impnetresformAction()
    {
        return $this->view;
    }

    /**
     * ネットDE受取返金結果反映
     */
    public function impnetresAction()
    {
        $mdlrc = new TableRepaymentControl($this->app->dbAdapter);
        $mdlcc = new TableClaimControl($this->app->dbAdapter);
        $history = new CoralHistoryOrder($this->app->dbAdapter);

        $tmpName = $_FILES["cres"]["tmp_name"];

        $listNa = array();  // 対象外リスト
        $cntSumLine = 0;    // 総件数(ヘッダ込みなので-1)
        $cntErrLine = 0;    // エラー件数
        $cntNrmLine = 0;    // 総件数 - エラー件数 = 正常件数

        $handle = null;
        try {
            // ユーザーIDの取得
            $obj = new \models\Table\TableUser($this->app->dbAdapter);
            $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

            if ($tmpName == '') {
                $this->view->assign('message', '<span style="font-size: 18px; color: red;">CSVファイルのオープンに失敗しました。<br />ファイルを選択してください。</span>');
                $this->view->assign('listNa', $listNa);
                return $this->view;
            }
            $handle = fopen($tmpName, "r");

            if (!$handle) {
                // ファイルオープン失敗
                $message = '<span style="font-size: 18px; color: red;">CSVファイルのオープンに失敗しました。<br />再試行してください。</span>';
            }
            else {
                $this->app->logger->info(' impnetresAction start(' . $_FILES["cres"]["name"] . ' / filesize : ' . filesize($tmpName) . ') ');
                $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();

                $cntLine = 0;

                // 入金ループ
                setlocale(LC_ALL,'ja_JP.UTF-8');
                while (($data = $this->fgetcsv_reg($handle, 1000, ",")) !== false) {
                    // 現在行をカウント(1からスタート！)
                    $cntLine += 1;

                    // 1行目のヘッダ行はスキップ
                    if ($cntLine <= 1) {
                        continue;
                    }

                    // --------------------------------
                    // 行データ取得
                    // --------------------------------
                    $coRecvNum = $data[0];      // お客様番号
                    $coYoyakuNum = $data[1];    // 受取番号
                    $coWcosId = $data[2];       // WCOS　ID
                    $coNameKanji = mb_convert_encoding($data[3], "UTF-8", "SJIS-win"); // お客様氏名
                    $coTranAmount = $data[4];   // 送金金額
                    $coTranLimit = $data[5];    // 送金期限
                    $coTranMethod = $data[6];   // 送金方法
                    $coFree2 = $data[7];        // 金融機関コード
                    $coFree3 = $data[8];        // 支店コード
                    $coFree4 = $data[9];        // 口座種別
                    $coFree5 = $data[10];       // 口座番号
                    $coFree6 = mb_convert_encoding($data[11], "UTF-8", "SJIS-win"); // 口座名義人名
                    $coTranReq = $data[12];     // 送金依頼日
                    $coTranProc = $data[13];    // 送金処理日
                    $coTranResult = $data[14];  // 送金結果

                    // 日時項目の先頭シングルクォーテーションを除外
                    $coTranLimit = substr($coTranLimit, 1);     // 送金期限
                    $coTranReq = substr($coTranReq, 1);         // 送金依頼日
                    $coTranProc = substr($coTranProc, 1);       // 送金処理日

                    // --------------------------------
                    // チェック処理
                    // --------------------------------
                    // 送金結果チェック
                    if ($coTranResult != 100) {
                        // 指示済みデータが存在しない
                        $listNa[] = array(
                                'RowNo'   => $cntLine,
                                'CoYoyakuNum' => $coYoyakuNum,
                                'CoRecvNum' => $coRecvNum,
                                'CoWcosId' => $coWcosId,
                                'CoNameKanji' => $coNameKanji,
                                'CoTranResult' => $coTranResult,
                                'Error'     => "送金結果=100：正常完了ではありません",
                        );
                        continue;
                    }


                    // 取込対象チェック
                    $sql = <<<EOQ
SELECT COUNT(rc.RepaySeq) AS CNT
     , MAX(IFNULL(o.Cnl_Status, 0)) AS Cnl_Status
     , MAX(rc.RepaySeq) AS RepaySeq
     , MAX(o.OrderSeq) AS OrderSeq
     , MAX(rc.ClaimId) AS ClaimId
  FROM T_RepaymentControl rc
       INNER JOIN T_ClaimControl cc
               ON rc.ClaimId = cc.ClaimId
       INNER JOIN T_Order o
               ON cc.OrderSeq = o.OrderSeq
 WHERE 1 = 1
   AND rc.CoRecvNum = :CoRecvNum
   AND rc.RepayStatus = 0
   AND rc.NetStatus = 3
EOQ;
                    $row = $this->app->dbAdapter->query($sql)->execute(array(':CoRecvNum' => $coRecvNum))->current();
                    $cntCheck = (int)$row['CNT'];
                    $cnlStatus = $row['Cnl_Status'];
                    $repaySeq = $row['RepaySeq'];
                    $oseq = $row['OrderSeq'];
                    $claimId = $row['ClaimId'];

                    if ($cntCheck == 0) {
                        // 指示済みデータが存在しない
                        $listNa[] = array(
                                'RowNo'   => $cntLine,
                                'CoYoyakuNum' => $coYoyakuNum,
                                'CoRecvNum' => $coRecvNum,
                                'CoWcosId' => $coWcosId,
                                'CoNameKanji' => $coNameKanji,
                                'CoTranResult' => $coTranResult,
                                'Error'     => "ハガキ出力済のデータが存在しません",
                        );
                        continue;

                    } elseif ($cntCheck > 1) {
                        // 指示済データが複数存在する
                        $listNa[] = array(
                                'RowNo'   => $cntLine,
                                'CoYoyakuNum' => $coYoyakuNum,
                                'CoRecvNum' => $coRecvNum,
                                'CoWcosId' => $coWcosId,
                                'CoNameKanji' => $coNameKanji,
                                'CoTranResult' => $coTranResult,
                                'Error'     => "ハガキ出力済データが複数存在しているため、更新対象を特定出来ません",
                        );
                        continue;

                    }

                    // 注文のキャンセルチェック
                    if ($cnlStatus != 0) {
                        $listNa[] = array(
                                'RowNo'   => $cntLine,
                                'CoYoyakuNum' => $coYoyakuNum,
                                'CoRecvNum' => $coRecvNum,
                                'CoWcosId' => $coWcosId,
                                'CoNameKanji' => $coNameKanji,
                                'CoTranResult' => $coTranResult,
                                'Error'     => "注文が既にキャンセルされています",
                        );
                        continue;

                    }

                    // --------------------------------
                    // 更新処理
                    // --------------------------------
                    // 返金管理を更新する
                    $udata = array(
                            'RepayStatus' => 1,
                            'DecisionDate' => date('Y-m-d H:i:s', strtotime($coTranProc)),
                            'FfCode' => $coFree2,               // 金融機関番号
                            'FfBranchCode' => $coFree3,         // 支店番号
                            'FfAccountClass' => $coFree4,       // 口座種別
                            'AccountNumber' => $coFree5,        // 口座番号
                            'AccountHolder' => $coFree6,        // 口座名義
                            'NetStatus' => 4,                   // ネットDE受取ステータス
                            'CoTranReqDate' => $coTranReq,      // 送金依頼日
                            'CoTranProcDate' => $coTranProc,    // 送金処理日
                            'UpdateId' => $userId,
                    );
                    $mdlrc->saveUpdate($udata, $repaySeq);

                    // 請求管理を更新する
                    $repayAmount = (int)$mdlrc->find($repaySeq)->current()['RepayAmount'];
                    $rowCc = $mdlcc->find($claimId)->current();
                    $udata = array(
                            'ClaimedBalance' => $rowCc['ClaimedBalance'] + $repayAmount, // 請求残高
                            'RepayAmountTotal' => $rowCc['RepayAmountTotal'] + $repayAmount, // 返金額合計
                            'CheckingClaimAmount' => $rowCc['CheckingClaimAmount'] - $repayAmount, // 消込情報－消込金額合計
                            'CheckingUseAmount' => $rowCc['CheckingUseAmount'] - $repayAmount, // 消込情報－利用額
                            'BalanceClaimAmount' => $rowCc['BalanceClaimAmount'] + $repayAmount, // 残高情報－残高合計
                            'BalanceUseAmount' => $rowCc['BalanceUseAmount'] + $repayAmount, // 残高情報－利用額
                            'UpdateId' => $userId,
                    );
                    $mdlcc->saveUpdate($udata, $claimId);


                    // 注文履歴へ登録
                    // 親子全注文に対し登録を実施
                    $sql = 'SELECT OrderSeq FROM T_Order WHERE P_OrderSeq = :P_OrderSeq AND Cnl_Status = 0';
                    $ri = $this->app->dbAdapter->query($sql)->execute(array(':P_OrderSeq' => $oseq));
                    foreach ($ri as $row) {
                        $history->InsOrderHistory($row["OrderSeq"], 114, $userId);
                    }
                }

                fclose($handle);

                $this->app->dbAdapter->getDriver()->getConnection()->commit();

                // --------------------------------
                // 更新処理
                // --------------------------------
                $cntSumLine = $cntLine - 1;  // 総件数(ヘッダ込みなので-1)

                // count関数対策
                if(!empty($listNa)) {
                    $cntErrLine = count($listNa); // エラー件数
                }

                $cntNrmLine = $cntSumLine - $cntErrLine; // 総件数 - エラー件数 = 正常件数

                $message = sprintf("ネットDE受取返金結果ファイル　「%s」　をインポートしました。<BR>（対象件数：%d件　取込件数：%d件　エラー件数：%d件）", f_e($_FILES["cres"]["name"]), $cntSumLine, $cntNrmLine, $cntErrLine);
                $this->app->logger->info(' impnetresAction completed(' . $message . ') ');
            }
        } catch(\Exception $e) {
            $message = $e->getMessage();
            $this->app->dbAdapter->getDriver()->getConnection()->rollBack();
            $this->app->logger->info(' impnetresAction error(' . $message . ') ');
            // (初期化処理)
            if ($handle) { fclose($handle); }
            $listNa = array();
        }

        $this->view->assign('message', $message);
        $this->view->assign('listNa', $listNa);

        return $this->view;
    }

    /**
     * 雑損失・雑収入一覧
     */
    public function losslistAction()
    {
        $params = $this->getParams();

        // [paging] 1ページあたりの項目数
        $ipp = self::PAGE_LINE_MAX;
        // [paging] 指定ページを取得
        $current_page = (isset($params['page'])) ? (int)$params['page'] : 1;
        if ($current_page < 1) $current_page = 1;

        $losstype = (isset($params['losstype'])) ? $params['losstype'] : 0;

        // 検索SQL
        $sql =<<<EOQ
SELECT  sc.SundrySeq                                /* 返金Seq（非表示項目） */
    /* 種類 */
    ,   CASE
            WHEN sc.SundryType = 0 THEN '雑収入'
            ELSE '雑損失'
        END AS SundryType
    ,   DATE(sc.ProcessDate) AS ProcessDate         /* 発生日時 */
    ,   sc.SundryAmount                             /* 金額 */
    ,   o.OrderId                                   /* 注文ID */
    ,   c.NameKj                                    /* 請求先氏名 */
    /* 発生起因（科目） */
    ,   (SELECT KeyContent
         FROM   M_Code
         WHERE  CodeId = 96
         AND    KeyCode = sc.SundryClass) AS SundryClass
    ,   c.CustomerId
    ,   o.OrderSeq
FROM    T_SundryControl sc
        LEFT OUTER JOIN T_Order o ON (sc.OrderSeq = o.OrderSeq)
        LEFT OUTER JOIN T_Customer c ON (o.OrderSeq = c.OrderSeq)
WHERE   1 = 1
AND     sc.SundryClass <> 99
EOQ;

        // 検索条件の考慮
        if      ($losstype == 1) { $sql .= " AND sc.SundryType = 1 "; } // 雑損失
        else if ($losstype == 2) { $sql .= " AND sc.SundryType = 0 "; } // 雑収入
        else /* if ($losstype == 0) */ { ; } // 処理無しの明示

        // 発生日
        $wProcessDate = BaseGeneralUtils::makeWhereDate(
            'sc.ProcessDate',
            BaseGeneralUtils::convertWideToNarrow($params['ProcessDateF']),
            BaseGeneralUtils::convertWideToNarrow($params['ProcessDateT'])
        );
        if ($wProcessDate != '') {
            $sql .= " AND " . $wProcessDate;
        }

        // SQL実行
        $datas = ResultInterfaceToArray($this->app->dbAdapter->query($sql)->execute(null));

        // count関数対策
        $datasLen = 0;
        if(!empty($datas)) {
            $datasLen = count($datas);
        }

        // [paging] ページャ初期化
        $pager = new CoralPager($datasLen, $ipp);
        // [paging] 指定ページを補正
        if( $current_page > $pager->getTotalPage() ) $current_page = $pager->getTotalPage();
        // [paging] 対象リストをページング情報に基づいて対象リストをスライス
        if( $datasLen > 0 ) $datas = array_slice( $datas, $pager->getStartIndex( $current_page ), $ipp );
        // [paging] ページングナビゲーション情報
        $page_links = array( 'base' => "rwsprcpt/losslist/losstype/" . f_e($losstype)
                . '/ProcessDateF/' . f_e($params['ProcessDateF'])
                . '/ProcessDateT/' . f_e($params['ProcessDateT'])
                . '/page' );
        $page_links['prev'] = $page_links['base'] . '/' . ( $current_page - 1 );
        $page_links['next'] = $page_links['base'] . '/' . ( $current_page + 1 );
        // [paging] ページング関連の情報をビューへアサイン
        $this->view->assign( 'current_page', $current_page );
        $this->view->assign( 'pager', $pager );
        $this->view->assign( 'page_links', $page_links );

        $this->view->assign('losstypeTag',BaseHtmlUtils::SelectTag("losstype", array (0 => '雑損失・雑収入全て', 1 => '雑損失のみ', 2 => '雑収入のみ'), $losstype));
        $this->view->assign('ProcessDateF', $params['ProcessDateF']);
        $this->view->assign('ProcessDateT', $params['ProcessDateT']);
        $this->view->assign('list', $datas);
        $this->view->assign('losstype', $losstype);

        // CSVダウンロードURL
        $urlprm = array();
        $urlprm['losstype'] = (isset($params['losstype'])) ? $params['losstype'] : 0;
        $urlprm['ProcessDateF'] = (isset($params['ProcessDateF'])) ? $params['ProcessDateF'] : '';
        $urlprm['ProcessDateT'] = (isset($params['ProcessDateT'])) ? $params['ProcessDateT'] : '';
        $this->view->assign('durl', 'rwsprcpt/dlosslistcsv?' . http_build_query($urlprm));

        return $this->view;
    }

    /**
     * 雑損失・雑収入等登録(編集処理)
     */
    public function losseditAction()
    {
        $params = $this->getParams();

        $sseq = (isset($params['sseq'])) ? $params['sseq'] : 0;

        // 雑収入・雑損失管理SEQ通知時
        if ($sseq > 0) {
            $scrow = $this->app->dbAdapter->query(" SELECT * FROM T_SundryControl WHERE SundrySeq = :SundrySeq ")->execute(array(':SundrySeq' => $sseq))->current();

            $losstype = $scrow['SundryType'];
            $processDate = '';
            $sundryAmount = $scrow['SundryAmount'];
            $sundrytype = ($scrow['SundryType'] == 0) ? 1 : 5;
            $orderId = $scrow['OrderId'];
        }
        else if (isset($params['oid']) && isset($params['claimedbalance'])) {
            $losstype = 1;
            $processDate = date('Y-m-d');
            $sundryAmount = $params['claimedbalance'];
            $sundrytype = 5;
            $orderId = $params['oid'];
        }
        else if (isset($params['oid']) && isset($params['claimtotalamount'])) {
            // 入金確認画面からの遷移(rwrcptcfm/list)
            $losstype = 1;
            $processDate = '';
            $sundryAmount = $params['claimtotalamount'];
            $sundrytype = 5;
            $orderId = $params['oid'];
        }
        else {
            $losstype = -1;
            $processDate = '';
            $sundryAmount = '';
            $sundrytype = -1;
            $orderId = '';
        }

        $this->view->assign('losstypeTag',BaseHtmlUtils::SelectTag("losstype", array (-1 => '---', 0 => '雑収入', 1 => '雑損失'), $losstype));
        $this->view->assign('ProcessDate', $processDate);
        $this->view->assign('SundryAmount', $sundryAmount);
        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
        $this->view->assign('sundrytypeTag',BaseHtmlUtils::SelectTag("sundrytype", $codeMaster->getSundryClassMaster(), $sundrytype));
        $this->view->assign('OrderId', $orderId);

        return $this->view;
    }

    /**
     * 雑損失・雑収入等登録(登録処理)
     */
    public function losssaveAction()
    {
        $params = $this->getParams();

        $data['losstype'] = (isset($params['losstype'])) ? $params['losstype'] : -1;
        $data['ProcessDate'] = $params['ProcessDate'];
        $data['SundryAmount'] = $params['SundryAmount'];
        $data['sundrytype'] = (isset($params['sundrytype'])) ? $params['sundrytype'] : -1;
        $data['OrderId'] = (isset($params['OrderId'])) ? $params['OrderId'] : -1;

        $errors = $this->validateLossEdit($data);

        // count関数対策
        if (!empty($errors)) {
            // エラーがあればエラーメッセージをセット
            $this->view->assign('error', $errors);

            $this->view->assign('losstypeTag',BaseHtmlUtils::SelectTag("losstype", array (-1 => '---', 0 => '雑収入', 1 => '雑損失'), $data['losstype']));
            $this->view->assign('ProcessDate', $data['ProcessDate']);
            $this->view->assign('SundryAmount', $data['SundryAmount']);
            $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
            $this->view->assign('sundrytypeTag',BaseHtmlUtils::SelectTag("sundrytype", $codeMaster->getSundryClassMaster(), $data['sundrytype']));

            $this->view->assign('OrderId', $params['OrderId']);

            $this->setTemplate('lossedit');
            return $this->view;
        }

        // 更新処理実施
        $errorCount = 0;
        $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
        try {
            // ユーザIDの取得
            $userTable = new \models\Table\TableUser($this->app->dbAdapter);
            $userId = $userTable->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

            // 雑収入・雑損失関連処理SQL
            $stm = $this->app->dbAdapter->query($this->getBaseP_SundryControl());

            // SQL実行結果取得用のSQL
            $getretvalsql = " SELECT @po_ret_sts AS po_ret_sts, @po_ret_errcd AS po_ret_errcd, @po_ret_sqlcd AS po_ret_sqlcd, @po_ret_msg AS po_ret_msg ";

            $prm = array(
                ':pi_sundry_type'   => $data['losstype'],
                ':pi_sundry_amount' => $data['SundryAmount'],
                ':pi_process_date'  => $data['ProcessDate'],
                ':pi_sundry_class'  => $data['sundrytype'],
                ':pi_order_id'      => empty($params['OrderId']) ? null : $params['OrderId'],
                ':pi_user_id'       => $userId,
            );

            $ri = $stm->execute($prm);

            // SQL実行例外なしもエラー戻り値の時は例外をｽﾛｰ
            $retval = $this->app->dbAdapter->query($getretvalsql)->execute(null)->current();
            if ($retval['po_ret_sts'] != 0) {
                throw new \Exception($retval['po_ret_msg']);
            }

            // 履歴登録用に注文Seqを取得する。
            // 雑収入
            if ($data['losstype'] == 0) {
                if (empty($params['OrderId'])) {
                    // 注文IDがNULLの場合は履歴登録はしない
                } else {
                    // 履歴登録用に親注文Seqを取得する。
                    $mdlo = new TableOrder($this->app->dbAdapter);
                    $data = $mdlo->findOrder(array('OrderId' => $params['OrderId']))->current();

                    // 取得した親注文Seqに紐づく子注文の注文Seqを取得
                    $sql = <<<EOQ
SELECT  OrderSeq
FROM    T_Order
WHERE   P_OrderSeq = :P_OrderSeq
AND     Cnl_Status = 0
;
EOQ;

                    $ri = $this->app->dbAdapter->query($sql)->execute(array(':P_OrderSeq' => $data['P_OrderSeq']));
                    $rows = ResultInterfaceToArray($ri);

                    // 注文履歴へ登録
                    $history = new CoralHistoryOrder($this->app->dbAdapter);
                    // 取得できた件数分ループする
                    foreach ($rows as $row) {
                        // 注文履歴登録
                        $history->InsOrderHistory($row["OrderSeq"], 64, $userId);
                    }
                }
            // 雑損失
            } else {
                // 履歴登録用に注文Seqを取得する。
                $mdlo = new TableOrder($this->app->dbAdapter);
                $data = $mdlo->findOrder(array('OrderId' => $params['OrderId']))->current();

                // 取得した親注文Seqに紐づく子注文の注文Seqを取得
                $sql = <<<EOQ
SELECT  OrderSeq
FROM    T_Order
WHERE   P_OrderSeq = :P_OrderSeq
AND     Cnl_Status = 0
;
EOQ;

                $ri = $this->app->dbAdapter->query($sql)->execute(array(':P_OrderSeq' => $data['P_OrderSeq']));
                $rows = ResultInterfaceToArray($ri);

                // 注文履歴へ登録
                $history = new CoralHistoryOrder($this->app->dbAdapter);
                // 取得できた件数分ループする
                foreach ($rows as $row) {
                    // 注文履歴登録
                    $history->InsOrderHistory($row["OrderSeq"], 63, $userId);
                }
            }

            $this->app->dbAdapter->getDriver()->getConnection()->commit();
        }
        catch(\Exception $err) {
            $this->app->dbAdapter->getDriver()->getConnection()->rollBack();
            $errorCount = 1;
        }

        $this->view->assign('errorCount', $errorCount);

        return $this->view;
    }

    /**
     * 過不足入金一覧をCSVダウンロード
     */
    public function dlacklistcsvAction() {
        $params = $this->getParams();

        $lacktype = ( isset( $params['lacktype'] ) ) ? $params['lacktype'] : 0;
        $oemid = (isset($params['oem'])) ? $params['oem'] : -1;
        $balanceF = (isset($params['ClaimedBalanceF'])) ? $params['ClaimedBalanceF'] : null;
        $balanceT = (isset($params['ClaimedBalanceT'])) ? $params['ClaimedBalanceT'] : null;
        $transcom = (isset($params['transcom'])) ? $params['transcom'] : -1;
        $netstatus = (isset($params['netstatus'])) ? $params['netstatus'] : -1;

        $sql =<<<EOQ
SELECT  o.OrderSeq                                  /* 注文Seq（非表示項目） */
    ,   o.OrderId                                   /* 注文ID */
    ,   MAX(cc.F_LimitDate) AS F_LimitDate          /* 初回請求 */
    ,   MAX(cc.ClaimDate) AS ClaimDate              /* 最終請求 */
    ,   MAX(cc.LimitDate) AS LimitDate              /* 支払期限 */
    ,   (SELECT KeyContent FROM M_Code WHERE CodeId = 12 AND KeyCode = cc.ClaimPattern) AS IncreCaption      /* 請求 */
    ,   MAX(mc.KeyContent) AS IncreLogCaption       /* 属性 */
    ,   MAX(c.NameKj) AS NameKj                     /* 請求先氏名 */
    ,   MAX(cc.ClaimAmount) AS ClaimAmount          /* 請求金額 */
    ,   (SELECT KeyContent FROM M_Code WHERE CodeId = 198 AND KeyCode = MAX(rc.ReceiptClass)) AS ReceiptClass /* 入金形態 */
    ,   MAX(rc.ReceiptDate) AS ReceiptDate          /* 入金日 */
    ,   DATE_FORMAT(MAX(rc.ReceiptProcessDate), '%Y-%m-%d') AS ReceiptProcessDate/* 入金日処理 */
    ,   SUM(rc.ReceiptAmount) AS ReceiptAmount      /* 実入金額 */
    ,   CASE
            WHEN cc.ClaimPattern IN (1, 2, 3, 4) THEN '初回'
            WHEN cc.ClaimPattern IN (5, 6) THEN '再１'
            WHEN cc.ClaimPattern IN (7, 8, 9) THEN '再３'
            ELSE ''
        END AS ClaimPattern /* 最低請求状態 */
    ,   MAX(cc.MinClaimAmount) AS MinClaimAmount    /* 最低請求金額 */
    ,   MAX(cc.ClaimedBalance) * (-1) AS ClaimedBalance    /* 過不足金 */
    ,   c.CustomerId
FROM    T_ClaimControl cc
	    STRAIGHT_JOIN T_Order o ON (o.OrderSeq = cc.OrderSeq)
        STRAIGHT_JOIN T_Customer c ON (o.OrderSeq = c.OrderSeq)
        LEFT OUTER JOIN T_ReceiptControl rc ON (o.OrderSeq = rc.OrderSeq)
        LEFT OUTER JOIN M_Code mc ON (mc.KeyCode = CASE
                                                        WHEN c.Incre_ArTel = 5 OR c.Incre_ArAddr = 5 THEN 5
                                                        WHEN c.Incre_ArTel = 4 OR c.Incre_ArAddr = 4 THEN 4
                                                        WHEN c.Incre_ArTel = 3 OR c.Incre_ArAddr = 3 THEN 3
                                                        WHEN c.Incre_ArTel = 2 OR c.Incre_ArAddr = 2 THEN 2
                                                        WHEN c.Incre_ArTel = 1 OR c.Incre_ArAddr = 1 THEN 1
                                                        ELSE -1
                                                   END
                                      AND mc.CodeId = 4)
WHERE cc.ClaimedBalance <> 0
AND   o.OrderSeq = o.P_OrderSeq
AND   o.Cnl_Status = 0
AND   o.Rct_Status = 1
AND   ( o.DataStatus = 61 OR ( o.DataStatus = 91 AND o.CloseReason = 1 ) )
EOQ;
        // 検索条件の考慮
        // 過剰
        if( $lacktype == 1 ) {
            $sql .= " AND cc.ClaimedBalance < 0 ";
        }
        // 不足
        else if( $lacktype == 2 ) {
            $sql .= " AND cc.ClaimedBalance > 0 ";
        }

        // OEM先
        if ($oemid >= 0) {
            $sql .= " AND IFNULL(o.OemId, 0) = $oemid ";
        }

        // 過不足金
        $wClaimAmount = BaseGeneralUtils::makeWhereInt(
            '(cc.ClaimedBalance * -1)',
            BaseGeneralUtils::convertWideToNarrow($balanceF),
            BaseGeneralUtils::convertWideToNarrow($balanceT)
        );
        if ($wClaimAmount != '') {
            $sql .= " AND " . $wClaimAmount;
        }

        // ﾈｯﾄDE受取手数料
        if ($transcom == 1) {
            $sql .= " AND EXISTS (SELECT * FROM T_Order o, AT_Order ao WHERE o.OrderSeq = ao.OrderSeq AND o.P_OrderSeq = cc.OrderSeq AND ao.RepayTCFlg = 1 ) ";
        } elseif($transcom == 0) {
            $sql .= " AND NOT EXISTS (SELECT * FROM T_Order o, AT_Order ao WHERE o.OrderSeq = ao.OrderSeq AND o.P_OrderSeq = cc.OrderSeq AND ao.RepayTCFlg = 1 ) ";
        }

        // ﾈｯﾄDE受取ｽﾃｰﾀｽ
        if ($netstatus == 0) {
            // ｽﾃｰﾀｽが指示中かつ、ﾈｯﾄDE受取ｽﾃｰﾀｽが未指示以外のデータが取得できないこと (つまり、指示済みのデータがないこと！)
            $sql .= " AND (SELECT COUNT(1) FROM T_RepaymentControl WHERE ClaimId = cc.ClaimId AND RepayStatus = 0 AND ProcessClass = 3 AND NetStatus > 0) = 0 ";
        } elseif ($netstatus > 0) {
            $sql .= " AND (SELECT COUNT(1) FROM T_RepaymentControl WHERE ClaimId = cc.ClaimId AND RepayStatus IN (0, 1) AND ProcessClass = 3 AND NetStatus = $netstatus) > 0 ";
        }

        $sql .= " GROUP BY o.OrderSeq, o.OrderId ";

        // SQL実行
        $datas = ResultInterfaceToArray( $this->app->dbAdapter->query( $sql )->execute( null ) );

        $templateId = 'CKI08068_1';    // 過不足入金一覧CSV
        $templateClass = 0;
        $seq = 0;
        $templatePattern = 0;

        $logicTemplate = new LogicTemplate( $this->app->dbAdapter );
        $response = $logicTemplate->convertArraytoResponse( $datas, sprintf( 'Kafusoku_%s.csv', date('YmdHis') ), $templateId, $templateClass, $seq, $templatePattern, $this->getResponse() );

        if( $response == false ) {
            throw new \Exception( $logicTemplate->getErrorMessage() );
        }

        return $response;
    }

    /**
     * 返金用振込データダウンロード
     */
    public function dhistlistcsvAction() {
        $params = $this->getParams();

        $mdlsp = new TableSystemProperty($this->app->dbAdapter);

        // 一時保存用ファイルディレクトリ取得
        $module = '[DEFAULT]';
        $category = 'systeminfo';
        $name = 'TempFileDir';

        $transCsvDir = $mdlsp->getValue($module, $category, $name);

        // OEMID、確定期間取得
        $oemId = (isset($params['oemId']) && (! empty($params['oemId']))) ? $params['oemId'] : -1;
        $dt = (isset($params['DecisionDateT'])) ? $params['DecisionDateT'] : date('Y-m-d');
        $df = (isset($params['DecisionDateF'])) ? $params['DecisionDateF'] : date('Y-m-d');

        // 振込用データの取得
        $sql = <<<EOQ
SELECT  rc.BankName
    ,   rc.FfCode
    ,   rc.BranchName
    ,   rc.FfBranchCode
    ,   rc.FfAccountClass
    ,   rc.AccountNumber
    ,   rc.AccountHolder
    ,   rc.TransferAmount
    ,   rc.RepaySeq
FROM    T_RepaymentControl rc
        INNER JOIN T_ClaimControl cc ON (cc.ClaimId = rc.ClaimId)
        INNER JOIN T_Order o ON (o.OrderSeq = cc.OrderSeq)
WHERE   rc.RepayStatus = 1          /* 確定済み */
AND     rc.ProcessClass = 0         /* 銀行振込 */
AND     rc.OutputFileSeq IS NULL    /* 振込データをダウンロードしていないもの */
EOQ;
        // 検索条件の付加
        // OEMID
        if ($oemId != -1) {
            $sql .= " AND IFNULL(o.OemId, 0) = " . $oemId;
        }

        // 返金確定期間
        $wdate = "";
        $wdate = BaseGeneralUtils::makeWhereDateTime(
            'rc.DecisionDate',
            BaseGeneralUtils::convertWideToNarrow($df),
            BaseGeneralUtils::convertWideToNarrow($dt)
        );
        if ($wdate != '') {
            $sql .= " AND " . $wdate;
        }

        $ri = $this->app->dbAdapter->query($sql)->execute(null);
        $csvData = ResultInterfaceToArray($ri);

        // 合計の算出
        $totalCnt = 0;
        $totalRepayAmount = 0;

        // ファイル名]
        $strYmdHis = date("YmdHis");
        $fileName = sprintf("TransferData_Henkin_%s_%s_%s_%s.csv", $strYmdHis, $oemId, $df, $dt);

        // ファイルフルパス
        $transCsvFullFileName = $transCsvDir . '/' . $fileName;
        // すでにファイルが作成されていたら削除
        if ( file_exists($transCsvFullFileName)) {
            unlink($transCsvFullFileName);
        }

        // ----------------------------------
        // 振込データ作成
        // ----------------------------------
        // ヘッダーレコード
        $headerRecord = sprintf(
            "1,21,0,1848513200,ｶ)ｷｬｯﾁﾎﾞｰﾙ,%02d%02d,0033,,002,,1,3804573,\r\n",
            date('m', strtotime($strYmdHis)),
            date('d', strtotime($strYmdHis))
        );
        $headerRecord = mb_convert_encoding($headerRecord, "SJIS", "UTF-8");

        // データレコード
        $dataRecords = "";

        // count関数対策
		$csvDataLen = 0;
        if(!empty($csvData)) {
            $csvDataLen = count($csvData);
        }

        for ($i = 0 ; $i < $csvDataLen; $i++) {

            $totalCnt++;
            $totalRepayAmount += $csvData[$i]['TransferAmount'];

            $dataRecord = sprintf(
                "2,%d,%s,%d,%s,,%d,%d,%s,%d,0,,, , , \r\n",
                $csvData[$i]['FfCode'],                 // 銀行コード
                $csvData[$i]['BankName'],               // 銀行名
                $csvData[$i]['FfBranchCode'],           // 支店コード
                $csvData[$i]['BranchName'],             // 支店名
                $csvData[$i]['FfAccountClass'],         // 科目
                $csvData[$i]['AccountNumber'],          // 口座番号
                $csvData[$i]['AccountHolder'],          // 受取人
                $csvData[$i]['TransferAmount']          // 振込金額
            );

            $dataRecords .= mb_convert_encoding($dataRecord, "SJIS", "UTF-8");
        }

        // トレーラレコード
        $trailerRecord = sprintf(
            "8,%d,%d,\r\n",
            $totalCnt,
            $totalRepayAmount
        );
        $trailerRecord = mb_convert_encoding($trailerRecord, "SJIS", "UTF-8");

        // エンドレコード
        $endRecord = "9,\r\n";
        $endRecord = mb_convert_encoding($endRecord, "SJIS", "UTF-8");

        // 作成したデータを結合
        $contents = $headerRecord . $dataRecords . $trailerRecord . $endRecord;
        // ファイルに保存
        file_put_contents($transCsvFullFileName, $contents);

        // ----------------------------------
        // 更新処理
        // ----------------------------------
        // トランザクション開始
        $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
        try {
            // CSVファイル
            $obj_csv = null;
            $filename = isset($transCsvFullFileName) ? $transCsvFullFileName : null;
            if (!is_null($filename)) {
                $fp = fopen($filename, "rb");
                $obj_csv = fread($fp, filesize($filename));
                if (!$obj_csv) {
                    throw new \Exception('振込ファイルの作成に失敗しました。');
                }
                fclose($fp);
                unlink($filename);
            }

            if (! empty($obj_csv)) {
                // ユーザーIDの取得
                $obj = new TableUser($this->app->dbAdapter);
                $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

                // 出力ファイル管理テーブルにINSERTする。
                $data = array(
                        'OutputFile' => $obj_csv,
                        'RegistId' => $userId,
                );
                $mdlof = new TableOutputFile($this->app->dbAdapter);
                $seq = $mdlof->saveNew($data);

                // 返金管理を更新
                $mdlrc = new TableRepaymentControl($this->app->dbAdapter);
                // データ作成行数分、ループする。
                foreach ($csvData as $key => $value) {
                    $mdlrc->saveUpdate(array('OutputFileSeq' => $seq), $value['RepaySeq']);
                }
            }

            // コミット
            $this->app->dbAdapter->getDriver()->getConnection()->commit();

        } catch (\Exception $e) {
            // ロールバック
            $this->app->dbAdapter->getDriver()->getConnection()->rollback();
        }

        if (! empty($seq)) {
            // ----------------------------------
            // ファイルをダウンロード
            // ----------------------------------
            // 再検索
            $ofdata = $mdlof->find($seq)->current();

            // ファイルフルパス
            $transCsvFullFileName = $transCsvDir . '/' . $fileName;

            // 同名ファイルがある場合はファイル削除
            if (file_exists($transCsvFullFileName)) {
                unlink($transCsvFullFileName);
            }

            // ファイルに保存
            file_put_contents($transCsvFullFileName, $ofdata['OutputFile']);

            // レスポンスヘッダの出力
            header("Content-Type: application/octet-stream");
            header("Content-Disposition: attachment; filename=$fileName");

            // データ出力
            readfile($transCsvFullFileName);

            $response = $this->response;
        } else {
            $response = false;
        }

        return $response;
    }

    /**
     * 雑損失・雑収入一覧データをCSVダウンロード
     */
    public function dlosslistcsvAction() {
        $params = $this->getParams();

        $losstype = ( isset( $params['losstype'] ) ) ? $params['losstype'] : 0;

        // 検索SQL
        $sql =<<<EOQ
SELECT  sc.SundrySeq                                /* 返金Seq（非表示項目） */
    /* 種類 */
    ,   CASE
            WHEN sc.SundryType = 0 THEN '雑収入'
            ELSE '雑損失'
        END AS SundryType
    ,   DATE(sc.ProcessDate) AS ProcessDate         /* 発生日時 */
    ,   sc.SundryAmount                             /* 金額 */
    ,   o.OrderId                                   /* 注文ID */
    ,   c.NameKj                                    /* 請求先氏名 */
    /* 発生起因（科目） */
    ,   (SELECT KeyContent
         FROM   M_Code
         WHERE  CodeId = 96
         AND    KeyCode = sc.SundryClass) AS SundryClass
    ,   c.CustomerId
    ,   o.OrderSeq
FROM    T_SundryControl sc
        LEFT OUTER JOIN T_Order o ON (sc.OrderSeq = o.OrderSeq)
        LEFT OUTER JOIN T_Customer c ON (o.OrderSeq = c.OrderSeq)
WHERE   1 = 1
AND     sc.SundryClass <> 99
EOQ;

        // 検索条件の考慮
        // 雑損失
        if( $losstype == 1 ) {
            $sql .= " AND sc.SundryType = 1 ";
        }
        // 雑収入
        else if( $losstype == 2 ) {
            $sql .= " AND sc.SundryType = 0 ";
        }

        // 発生日
        $wProcessDate = BaseGeneralUtils::makeWhereDate(
            'sc.ProcessDate',
            BaseGeneralUtils::convertWideToNarrow($params['ProcessDateF']),
            BaseGeneralUtils::convertWideToNarrow($params['ProcessDateT'])
        );
        if( $wProcessDate != '' ) {
            $sql .= " AND " . $wProcessDate;
        }

        // SQL実行
        $datas = ResultInterfaceToArray($this->app->dbAdapter->query($sql)->execute(null));

        $templateId = 'CKI08071_1';    // 雑損失・雑収入一覧データ
        $templateClass = 0;
        $seq = 0;
        $templatePattern = 0;

        $logicTemplate = new LogicTemplate( $this->app->dbAdapter );
        $response = $logicTemplate->convertArraytoResponse( $datas, sprintf( 'Zassonshitu_%s.csv', date('YmdHis') ), $templateId, $templateClass, $seq, $templatePattern, $this->getResponse() );

        if( $response == false ) {
            throw new \Exception( $logicTemplate->getErrorMessage() );
        }

        return $response;
    }

    /**
     * ﾈｯﾄDE受取ﾃﾞｰﾀダウンロード用画面情報をセッションに保存
     */
    public function dlnetdatasetAction() {
        // セッションに情報をセットする
        unset($_SESSION[self::SESS_NET_DATA]);
        $_SESSION[self::SESS_NET_DATA] = $this->getParams();

        return true;
    }

    /**
     * ﾈｯﾄDE受取ﾃﾞｰﾀをCSVダウンロード
     */
    public function dlnetdataAction() {
        $mdlSysp = new TableSystemProperty($this->app->dbAdapter);
        $mdlRep = new TableRepaymentControl($this->app->dbAdapter);
        $mdlCc = new TableClaimControl($this->app->dbAdapter);
        $history = new CoralHistoryOrder($this->app->dbAdapter);
        $mdlcus = new TableCustomer($this->app->dbAdapter);

        // ユーザIDの取得
        $userTable = new \models\Table\TableUser($this->app->dbAdapter);
        $userID = $userTable->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

        // 事業者コード取得
        $corpCode = $mdlSysp->getValue(TableSystemProperty::DEFAULT_MODULE, 'systeminfo', 'NetCoCorpCode');

        // セッションからパラメーター復元
        $params = $_SESSION[self::SESS_NET_DATA];

        // 返金管理SEQのカンマ区切りリスト作成
        $strRepaySeqs = $params['RepaySeqs'];

        $sql =<<<EOQ
SELECT  '7'             AS DataSyubetsu     /* データ種別       */
,       '0000000000'    AS CoPayCode        /* 支払コード       */
,       rc.CoRecvNum    AS CoRecvNum        /* お客様番号       */
,       '000'           AS CoJigyosyaNo     /* 事業者番号       */
,       '00000'         AS CoAnkenNo        /* 契約案件番号     */
,       c.SearchPhone   AS CoWcosPassword   /* WCOSパスワード   */
,       'I'             AS CoOpCode         /* データ区分       */
,       '$corpCode'     AS CoCorpCode       /* 事業者コード     */
,       c.SearchPhone   AS CoTel            /* 電話番号         */
,       c.NameKj        AS CoNameKanji      /* お客様氏名       */
,       rc.CoTranLimit  AS CoTranLimit      /* 送金期限         */
,       rc.TransferAmount AS CoTranAmount   /* 送金金額         */
,       ''              AS CoReserveNum     /* 予約番号         */
,       ''              AS CoMemberNum      /* 会員番号         */
,       ''              AS CoNameKana       /* お客様氏名（フリガナ）*/
,       rc.CoWcosId     AS CoFree1          /* WCOS ID          */
,       ''              AS CoFree2          /* 金融機関コード   */
,       ''              AS CoFree3          /* 支店コード       */
,       ''              AS CoFree4          /* 口座種別         */
,       ''              AS CoFree5          /* 口座番号         */
,       ''              AS CoFree6          /* 口座名義人名     */
,       ''              AS CoFree7          /* メールアドレス１ */
,       ''              AS CoFree8          /* メールアドレス２ */
,       ''              AS CoCFree1         /* フリースペース１ */
,       ''              AS CoCFree2         /* フリースペース２ */
,       ''              AS CoCFree3         /* フリースペース３ */
,       ''              AS CoCFree4         /* フリースペース４ */
,       ''              AS CoCFree5         /* フリースペース５ */
,       ''              AS CoCFree6         /* フリースペース６ */
,       ''              AS CoCFree7         /* フリースペース７ */
,       ''              AS CoCFree8         /* フリースペース８ */
FROM    T_RepaymentControl rc
        INNER JOIN T_ClaimControl cc ON (rc.ClaimId = cc.ClaimId)
        INNER JOIN T_Order o ON (cc.OrderSeq = o.OrderSeq)
        INNER JOIN T_Customer c ON (o.OrderSeq = c.OrderSeq)
WHERE   1 = 1
AND     rc.RepaySeq IN ($strRepaySeqs)
EOQ;

        // SQL実行
        $datas = ResultInterfaceToArray($this->app->dbAdapter->query($sql)->execute(null));

        $templateId = 'CKI08070_2';    // ﾈｯﾄDE受取ﾃﾞｰﾀ
        $templateClass = 0;
        $seq = 0;
        $templatePattern = 0;

        $logicTemplate = new LogicTemplate( $this->app->dbAdapter );
        $response = $logicTemplate->convertArraytoResponse( $datas, sprintf( 'NetData_%s.csv', date('YmdHis') ), $templateId, $templateClass, $seq, $templatePattern, $this->getResponse() );

        if( $response == false ) {
            throw new \Exception( $logicTemplate->getErrorMessage() );
        }

        // 更新処理(未指示を指示済みに更新する)
        $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
        try {
            // 1返金ずつ処理を行う
            $i = 0;
            $arrRepaySeq = explode(',', $strRepaySeqs);
            while (isset($arrRepaySeq[$i])) {
                $row = $mdlRep->find($arrRepaySeq[$i])->current();
                $rowCc = $mdlCc->find($row['ClaimId'])->current();
                $rowcus = $mdlcus->findCustomer(array('OrderSeq' => $rowCc['OrderSeq']))->current();

                // 未指示を指示済みに更新
                if ($row['NetStatus'] == 0) {
                    $sql = ' UPDATE T_RepaymentControl SET NetStatus = :NetStatus, CoWcosPassword = :CoWcosPassword, UpdateId = :UpdateId , UpdateDate = :UpdateDate WHERE RepaySeq = :RepaySeq ';
                    $prm = array(
                        ':NetStatus' => 1,
                        ':CoWcosPassword' => $rowcus['SearchPhone'],
                        ':UpdateId' => $userID,
                        ':UpdateDate' => date('Y-m-d H:i:s'),
                        ':RepaySeq' => $row['RepaySeq'],
                    );
                    $this->app->dbAdapter->query($sql)->execute($prm);
                }

                // 注文履歴へ登録
                $ccRow = $mdlCc->find($row['ClaimId'])->current();
                $sql = 'SELECT OrderSeq FROM T_Order WHERE P_OrderSeq = :P_OrderSeq AND Cnl_Status = 0';
                $ri = $this->app->dbAdapter->query($sql)->execute(array(':P_OrderSeq' => $rowCc['OrderSeq']));
                foreach ($ri as $row) {
                    $history->InsOrderHistory($row["OrderSeq"], 111, $userID);
                }

                $i++;
            }

            // コミット
            $this->app->dbAdapter->getDriver()->getConnection()->commit();

        } catch(\Exception $e) {
            $this->app->dbAdapter->getDriver()->getConnection()->rollback();
            throw $e;
        }

        return $response;
    }

    /**
     * ﾈｯﾄDE受取ﾊｶﾞｷﾃﾞｰﾀダウンロード用画面情報をセッションに保存
     */
    public function dlnethagakisetAction() {
        // セッションに情報をセットする
        unset($_SESSION[self::SESS_NET_HAGAKI]);
        $_SESSION[self::SESS_NET_HAGAKI] = $this->getParams();

        return true;
    }

    /**
     * ﾈｯﾄDE受取ﾊｶﾞｷﾃﾞｰﾀをCSVダウンロード
     */
    public function dlnethagakiAction() {
        $mdlRep = new TableRepaymentControl($this->app->dbAdapter);
        $mdlCc = new TableClaimControl($this->app->dbAdapter);
        $history = new CoralHistoryOrder($this->app->dbAdapter);

        // ユーザIDの取得
        $userTable = new \models\Table\TableUser($this->app->dbAdapter);
        $userID = $userTable->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

        // セッションからパラメーター復元
        $params = $_SESSION[self::SESS_NET_HAGAKI];

        // 返金管理SEQのカンマ区切りリスト作成
        $strRepaySeqs = $params['RepaySeqs'];

        // SQL実行
        $sql =<<<EOQ
SELECT  DISTINCT rc.RepaySeq, cc.OrderSeq
FROM    T_RepaymentControl rc
        INNER JOIN T_ClaimControl cc ON (rc.ClaimId = cc.ClaimId)
WHERE   1 = 1
AND     rc.RepaySeq IN ($strRepaySeqs)
EOQ;
        $ri = $this->app->dbAdapter->query($sql)->execute(null);

        $datas = array();
        foreach ($ri as $ri_row) {
            $data = array();

            // 初回請求書情報 --------------------------------------------------------------------------------->
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
            $sql .= '        T_ClaimHistory ch ON ( o.OrderSeq = ch.OrderSeq AND ch.PrintedFlg = 1 AND ch.ValidFlg = 1 ) LEFT OUTER JOIN ';
            $sql .= '        T_OemClaimAccountInfo ca ON( ch.Seq = ca.ClaimHistorySeq ) ';
            $sql .= ' WHERE  o.OrderSeq = :OrderSeq ';
            $sql .= '   AND  EXISTS (SELECT * FROM T_Order t WHERE t.P_OrderSeq = o.OrderSeq AND t.Cnl_Status = 0) ';
            $sql .= ' ORDER BY ch.Seq DESC LIMIT 1 '; // 請求履歴は最新のみ

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

            // ネットDE受取専用情報 --------------------------------------------------------------------------------->
            $row_repay = $mdlRep->find($ri_row['RepaySeq'])->current();
            $data['CoWcosId']           = $row_repay['CoWcosId'];
            $data['CoWcosPassword']     = $row_repay['CoWcosPassword'];
            $data['CoWcosUrl']          = $row_repay['CoWcosUrl'];
            $data['RepayAmount']        = $row_repay['RepayAmount'];
            $data['TransferCommission'] = $row_repay['TransferCommission'];
            $data['CoTranLimit']        = $row_repay['CoTranLimit'];

            $datas[] = $data;

        }

        $templateId = 'CKI08070_3';    // ﾈｯﾄDE受取ﾃﾞｰﾀ
        $templateClass = 0;
        $seq = 0;
        $templatePattern = 0;

        $logicTemplate = new LogicTemplate( $this->app->dbAdapter );
        $response = $logicTemplate->convertArraytoResponse( $datas, sprintf( 'HagakiData_%s.csv', date('YmdHis') ), $templateId, $templateClass, $seq, $templatePattern, $this->getResponse() );

        if( $response == false ) {
            throw new \Exception( $logicTemplate->getErrorMessage() );
        }

        // 更新処理(承認済みをハガキ出力済みに更新する)
        $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
        try {
            // 1返金ずつ処理を行う
            $i = 0;
            $arrRepaySeq = explode(',', $strRepaySeqs);
            while (isset($arrRepaySeq[$i])) {
                $row = $mdlRep->find($arrRepaySeq[$i])->current();

                // 承認済みをハガキ出力済みに更新
                if ($row['NetStatus'] == 2) {
                    $sql = ' UPDATE T_RepaymentControl SET NetStatus = :NetStatus , UpdateId = :UpdateId , UpdateDate = :UpdateDate WHERE RepaySeq = :RepaySeq ';
                    $prm = array(
                            ':NetStatus' => 3,
                            ':UpdateId' => $userID,
                            ':UpdateDate' => date('Y-m-d H:i:s'),
                            ':RepaySeq' => $row['RepaySeq'],
                    );
                    $this->app->dbAdapter->query($sql)->execute($prm);
                }

                // 注文履歴へ登録
                $ccRow = $mdlCc->find($row['ClaimId'])->current();
                $sql = 'SELECT OrderSeq FROM T_Order WHERE P_OrderSeq = :P_OrderSeq AND Cnl_Status = 0';
                $ri = $this->app->dbAdapter->query($sql)->execute(array(':P_OrderSeq' => $ccRow['OrderSeq']));
                foreach ($ri as $row) {
                    $history->InsOrderHistory($row["OrderSeq"], 113, $userID);
                }

                $i++;
            }

            // コミット
            $this->app->dbAdapter->getDriver()->getConnection()->commit();

        } catch(\Exception $e) {
            $this->app->dbAdapter->getDriver()->getConnection()->rollback();
            throw $e;
        }

        return $response;
    }

    /**
     * 雑収入・雑損失関連処理ファンクションの基礎SQL取得。
     *
     * @return 雑収入・雑損失関連処理ファンクションの基礎SQL
     */
    protected function getBaseP_SundryControl() {
        return <<<EOQ
CALL P_SundryControl(
    :pi_sundry_type
,   :pi_sundry_amount
,   :pi_process_date
,   :pi_sundry_class
,   :pi_order_id
,   :pi_user_id
,   @po_ret_sts
,   @po_ret_errcd
,   @po_ret_sqlcd
,   @po_ret_msg
    )
EOQ;
    }

    /**
     * 雑損失・雑収入等登録フォームの内容を検証する
     * @param array $data 雑損失・雑収入等登録フォームデータ
     * @return array エラーメッセージの配列
     */
    protected function validateLossEdit(array $data)
    {
        $errors = array();

        // 過不足状況:種類
        $key = 'losstype';
        if (!isset($errors[$key]) && !($data[$key] != -1)) {
            $errors[$key] = array("'種類'を指定してください");
        }

        // 過不足状況:発生日時
        $key = 'ProcessDate';
        if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
            $errors[$key] = array("'発生日時'を入力してください");
        }
        if (!isset($errors[$key]) && !IsValidFormatDate($data[$key])) {
            $errors[$key] = array("'発生日時'の形式が不正です");
        }

        // 過不足状況:金額
        $key = 'SundryAmount';
        if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
            $errors[$key] = array("'金額'を入力してください");
        }
        if (!isset($errors[$key]) && !(is_numeric($data[$key]))) {
            $errors[$key] = array("'金額'の指定が不正です");
        }
// 2015/08/11 Y.Suzuki ﾏｲﾅｽ入金も可能とするため、Validateから削除 Stt
//         if (!isset($errors[$key]) && !((int)$data[$key] > 0)) {
//             $errors[$key] = array("'金額'の指定が不正です");
//         }
// 2015/08/11 Y.Suzuki ﾏｲﾅｽ入金も可能とするため、Validateから削除 End

// 2015/08/27 Y.Suzuki 金額に対するValidateを追加 Stt
        // 雑収入の場合
        if (isset($data['losstype']) && $data['losstype'] == '0') {
            // 注文IDが入っている場合
            if (isset($data['OrderId']) && strlen($data['OrderId']) > 0) {
                // 注文IDから紐づく注文/請求データを取得
                $sql = "SELECT o.OrderSeq, o.P_OrderSeq, o.Cnl_Status, cc.ClaimedBalance FROM T_Order o INNER JOIN T_ClaimControl cc ON (cc.OrderSeq = o.P_OrderSeq) WHERE o.OrderId = :OrderId";
                $odata = $this->app->dbAdapter->query($sql)->execute(array(':OrderId' => $data['OrderId']))->current();

                // 未キャンセルでない場合エラー
                if (!isset($errors[$key]) && $odata['Cnl_Status'] <> 0) {
                    $errors[$key] = array("キャンセル中の注文に対する雑収入の登録は出来ません。");
                }
                // プラス入金の場合
                if ((int)$data[$key] > 0) {
                    // 請求残高がマイナスでない場合は過剰入金ではないため、エラー
                    if (!isset($errors[$key]) && !((int)$odata['ClaimedBalance'] < 0)) {
                        $errors[$key] = array("この注文に対する雑収入の登録は出来ません");
                    }
                    // 請求残高を超える入金はエラー
                    if (!isset($errors[$key]) && (int)$odata['ClaimedBalance'] * -1 < $data[$key]) {
                        $errors[$key] = array("雑収入の'金額'は過剰入金額より多い金額で入力出来ません");
                    }
                }
                // マイナス入金の場合
                if ((int)$data[key] < 0) {
                    // 注文IDから紐づく雑収入データを取得
                    $sql = "SELECT  OrderSeq, IFNULL(SUM(SundryAmount), 0) AS SundryAmount FROM T_SundryControl WHERE OrderId = :OrderId AND SundryType = 0 GROUP BY OrderSeq";
                    $sdata = $this->app->dbAdapter->query($sql)->execute(array(':OrderId' => $data['OrderId']))->current();

                    // 取得できない場合エラー
                    if (!isset($errors[$key]) && empty($sdata)) {
                        $errors[$key] = array("雑収入のない注文に対するマイナス入金は出来ません");
                    }
                    // 雑収入登録時の金額を超える場合エラー
                    if (!isset($errors[$key]) && (int)$sdata['SundryAmount'] < (int)$data[$key] * -1) {
                        $errors[$key] = array("すでに登録済みの雑収入額より多い金額でマイナス入金は出来ません");
                    }
                }
            }
        }

        // 雑損失の場合
        if (isset($data['losstype']) && $data['losstype'] == '1') {
            // 注文IDから紐づく注文/請求データを取得
            $sql = "SELECT o.OrderSeq, o.P_OrderSeq, o.Cnl_Status, cc.ClaimedBalance FROM T_Order o INNER JOIN T_ClaimControl cc ON (cc.OrderSeq = o.P_OrderSeq) WHERE o.OrderId = :OrderId";
            $odata = $this->app->dbAdapter->query($sql)->execute(array(':OrderId' => $data['OrderId']))->current();

            // 未キャンセルでない場合エラー
            if (!isset($errors[$key]) && $odata['Cnl_Status'] <> 0) {
                $errors[$key] = array("キャンセル中の注文に対する雑損失の登録は出来ません。");
            }
            // プラス入金の場合
            if ((int)$data[$key] > 0) {
                // 請求残が存在しない場合はエラー
                if (!isset($errors[$key]) && !((int)$odata['ClaimedBalance'] > 0)) {
                    $errors[$key] = array("請求残高が 0 のため、この注文に対する雑損失の登録は出来ません");
                }
                // 請求残高を超える入金はエラー
                if (!isset($errors[$key]) && (int)$odata['ClaimedBalance'] < $data[$key]) {
                    $errors[$key] = array("雑損失の'金額'は請求残高より多い金額で入力できません");
                }
            }
            // マイナス入金の場合
            if ((int)$data[$key] < 0) {
                // 注文IDから紐づく会計対象雑損失データを取得
                $sql = "SELECT OrderSeq, IFNULL(SUM(SundryAmount), 0) AS SundryAmount FROM T_SundryControl WHERE OrderId = :OrderId AND SundryType = 1 AND SundryClass <> 99 GROUP BY OrderSeq";
                $sdata = $this->app->dbAdapter->query($sql)->execute(array(':OrderId' => $data['OrderId']))->current();

                // 取得できない場合エラー
                if (!isset($errors[$key]) && empty($sdata)) {
                    $errors[$key] = array("雑損失のない注文に対するマイナス入金は出来ません");
                }
                // 雑損失登録時の金額を超える場合エラー
                if (!isset($errors[$key]) && (int)$sdata['SundryAmount'] < (int)$data[$key] * -1) {
                    $errors[$key] = array("すでに登録済みの雑損失額より多い金額でマイナス入金は出来ません");
                }
            }
        }

// 2015/08/27 Y.Suzuki 金額に対するValidateを追加 End

        // 雑損失の場合以下処理を行う。
        if (isset($data['losstype']) && $data['losstype'] == '1') {
            // 注文状況:注文ID
            $key = 'OrderId';
            if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
                $errors[$key] = array("'注文ID'を指定してください");
            }
        }

        // 過不足状況:科目
        $key = 'sundrytype';
        if (!isset($errors[$key]) && !($data[$key] != -1)) {
            $errors[$key] = array("'科目'を指定してください");
        }
        if (!isset($errors['losstype']) && !isset($errors[$key])) {

            $class1 = $this->app->dbAdapter->query(" SELECT Class1 FROM M_Code WHERE CodeId = 96 AND KeyCode = :KeyCode "
            )->execute(array(':KeyCode' => $data[$key]))->current()['Class1'];
            if ($class1 != $data['losstype']) {
                $errors[$key] = array("種類に対する'科目'は選択できません");
            }
        }

        return $errors;
    }

    /**
     * 20181029 ADD fgetcsvにバグがあるため、代替関数追加
     * ファイルポインタから行を取得し、CSVフィールドを処理する
     * @param resource handle
     * @param int length
     * @param string delimiter
     * @param string enclosure
     * @return ファイルの終端に達した場合を含み、エラー時にFALSEを返します。
     */
    function fgetcsv_reg(&$handle,$length=NULL,$d=',',$e='"'){
        $d=preg_quote($d);
        $e=preg_quote($e);
        $_line="";
        $eof=false;
        while(($eof!=true) && (!feof($handle))){
            $_line.=(empty($length) ? fgets($handle) : fgets($handle,$length));
            $itemcnt=preg_match_all('/'.$e.'/',$_line,$dummy);
            if($itemcnt%2==0){
                $eof=true;
            }
        }

        $_csv_line=preg_replace('/(?:\\r\\n|[\\r\\n])?$/',$d,trim($_line));
        $_csv_pattern='/('.$e.'[^'.$e.']*(?:'.$e.$e.'[^'.$e.']*)*'.$e.'|[^'.$d.']*)'.$d.'/';
        preg_match_all($_csv_pattern,$_csv_line,$_csv_matches);
        $_csv_data=$_csv_matches[1];
        for($_csv_i=0;$_csv_i<count($_csv_data);$_csv_i++){
            $_csv_data[$_csv_i]=preg_replace('/^'.$e.'(.*)'.$e.'$/s','$1',$_csv_data[$_csv_i]);
            $_csv_data[$_csv_i]=str_replace($e.$e, $e, $_csv_data[$_csv_i]);
        }
        return empty($_line) ? false : $_csv_data;
    }
}

