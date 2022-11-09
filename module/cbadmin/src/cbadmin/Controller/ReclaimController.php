<?php
namespace cbadmin\Controller;

use Zend\Db\ResultSet\ResultSet;
use Zend\Json\Json;
use Coral\Base\BaseGeneralUtils;
use Coral\Base\BaseHtmlUtils;
use Coral\Base\BaseUtility;
use Coral\Coral\Controller\CoralControllerAction;
use Coral\Coral\CoralCodeMaster;
use Coral\Coral\CoralValidate;
use Coral\Coral\History\CoralHistoryOrder;
use cbadmin\Application;
use models\Table\TableClaimHistory;
use models\Table\TableOrder;
use models\Table\TableOem;
use models\Table\TableSite;
use models\Logic\LogicTemplate;
use models\View\ViewOrderCustomer;
use models\View\ViewDelivery;
use models\Table\TableClaimControl;
use models\Table\TableSystemProperty;
use DOMPDFModule\View\Model\PdfModel;
use models\Logic\LogicNormalizer;
use models\Table\TableReclaimIndicate;
use models\Table\TableClaimError;
use models\Logic\Exception\LogicClaimException;

class ReclaimController extends CoralControllerAction
{
	protected $_componentRoot = './application/views/components';

	const SESSION_JOB_PARAMS = 'CBRECLAIM_JOB_PARAMS';

	/**
	 * アプリケーションオブジェクト
	 * @var Application
	 */
	private $app;

	/**
	 * 再請求1への遅延損害金適用を無視するかのフラグ
	 * @access protected
	 * @var bool
	 */
	protected $_ignoreDamageAmoutnForReclaim1 = false;

	/**
	 * Controllerを初期化する
	 */
	public function _init()
	{
	    ini_set('max_execution_time', 0);		// 実行タイムアウトを無効にしておく（2015.6.9 eda）

        $this->app = Application::getInstance();
        $this->view->assign('userInfo', $this->app->authManagerAdmin->getUserInfo());

        $this->addStyleSheet('../css/default02.css')->addJavaScript('../js/prototype.js');

        $this->setPageTitle("後払い.com - 再請求");

        // 再請求1へ遅延損害金を適用するかの基本ルールを設定から反映
        $this->_ignoreDamageAmoutnForReclaim1 =
            isset($this->app->business['pay']) && isset($this->app->business['pay']['ignore_damage_for_reclaim1']) ?
                $this->app->business['pay']['ignore_damage_for_reclaim1'] : false;
        $this->view->assign('ignoreDamageAmountForReclaim1', $this->_ignoreDamageAmoutnForReclaim1);
	}

	/**
	 * 再請求対象のリストを表示する。
	 */
	public function listAction()
	{
        $params = $this->getParams();

        if (!isset($params['sld']) &&
            !isset($params['eld']) &&
            !isset($params['spanprop']) &&
            !isset($params['tdaysprop']) &&
            !isset($params['claimpattern']) &&
            !isset($params['isdone']) &&
            !isset($params['oem']) &&
            !isset($params['entid']) &&
            !isset($params['odrid'])) {
            // デフォルト検索条件「再請求7」「発行している」
            $params['claimpattern'] = 9;
            $params['isdone'] = 1;
        }

        $sld = isset($params['sld']) ? $params['sld'] : 8;                              // デフォルトは８日
        $eld = isset($params['eld']) ? $params['eld'] : 8;                              // デフォルトは８日
        $spanprop = isset($params['spanprop']) ? $params['spanprop'] : 1;               // デフォルトは「以上」
        $tdaysprop = isset($params['tdaysprop']) ? $params['tdaysprop'] : 1;            // デフォルトは「経過日数」
        $claimpattern = isset($params['claimpattern']) ? $params['claimpattern'] : 0;   // 請求パターン
        $isdone = isset($params['isdone']) ? $params['isdone'] : 1;                     // 1：発行している／2：発行していない
        $oem = isset($params['oem']) ? $params['oem'] : 0;// OEM
        $entid = isset($params['entid']) ? $params['entid'] : '';// 加盟店ID
        $odrid = isset($params['odrid']) ? $params['odrid'] : '';// 注文ID

        if (!CoralValidate::isInt($sld)) {
            $sld = 0;
        }

        $sDate = date('Y-m-d', strtotime(' -' . $sld . ' day'));
        $eDate = date('Y-m-d', strtotime(' -' . $eld . ' day'));

        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
        $mdloc = new ViewOrderCustomer($this->app->dbAdapter);
        $mdlch = new TableClaimHistory($this->app->dbAdapter);
        $mdlSysP = new TableSystemProperty($this->app->dbAdapter);

        // SQL(基本)
        // ※V_OrderCustomerの駆動表をT_ClaimControlに変更したものをVOCとする(20150718_1025)
        // VOC副問い合わせを直接参照する
        // 抽出する項目は対象となる注文の親注文の情報とする
        $sql =<<<EOQ
  SELECT ORD.OrderSeq                                                               AS OrderSeq
        ,CLM.Clm_Count                                                              AS Clm_Count
        ,CLM.LimitDate                                                              AS Clm_L_LimitDate
        ,CLM.DamageBaseDate                                                         AS ClmExt_DamageBaseDate
        ,F_GetCampaignVal(ETP.EnterpriseId, SIT.SiteId, DATE(NOW()), 'ReClaimFee')  AS ReClaimFee
        ,CLM.F_ClaimAmount                                                          AS UseAmount
        ,po.UseAmountTotal                                                          AS UseAmountTotal
        ,CUS.RealCallResult                                                         AS RealCallResult
        ,CUS.UnitingAddress                                                         AS UnitingAddress
        ,ORD.OrderId                                                                AS OrderId
        ,MCD.Class2                                                                 AS IncreArCaption
        ,CUS.CustomerId                                                             AS CustomerId
        ,CUS.NameKj                                                                 AS NameKj
        ,po.ConfirmWaitingFlg                                                       AS ConfirmWaitingFlg
        ,ORD.Incre_ScoreTotal                                                       AS Incre_ScoreTotal
        ,CLM.MypageReissueClass                                                     AS MypageReissueClass
        ,(SELECT COUNT(*) FROM T_Order WHERE P_OrderSeq = ORD.OrderSeq AND Cnl_Status = 1 AND IFNULL(ORD.CombinedClaimTargetStatus, 0) IN (91, 92) ) AS CombinedCnlCnt /* キャンセル未承認の取りまとめ注文 */
        ,CLM.MypageReissueReClaimFee                                                AS MypageReissueReClaimFee
        ,IFNULL(CH.ClaimFee, RI.ClaimFee)                                           AS InputReClaimFee
        ,IFNULL(ORD.OemId, 0)                                                       AS OemId
        ,CLM.F_ClaimAmount - CLM.ReceiptAmountTotal                                 AS ClaimedBalance
        ,CLM.ClaimFee                                                               AS ClaimFee
        ,SIT.RemindStopClass                                                        AS RemindStopClass
        ,ETP.ReClaimIssueCtlFlg                                                     AS ReClaimIssueCtlFlg
        ,ETP.ForceCancelClaimPattern                                                AS ForceCancelClaimPattern
    FROM T_ClaimControl CLM
         INNER JOIN T_Order ORD ON (ORD.OrderSeq = CLM.OrderSeq)
         INNER JOIN ( SELECT  t.P_OrderSeq
                             ,MIN(t.DataStatus)                      AS DataStatus                -- データステータス
                             ,MAX(IFNULL(t.LetterClaimStopFlg, 0))   AS LetterClaimStopFlg        -- 最大のストップフラグが1の場合＝ストップしたい注文あり
                             ,MAX(t.ConfirmWaitingFlg)               AS ConfirmWaitingFlg         -- 最大の確定待ちフラグが1の場合＝確定待ち注文あり
                             ,SUM(t.UseAmount)                       AS UseAmountTotal

                        FROM T_Order t
                       WHERE t.Cnl_Status = 0
                         AND t.DataStatus IN (51, 61)
                       GROUP BY t.P_OrderSeq
                    ) po
                 ON po.P_OrderSeq = ORD.OrderSeq
         INNER JOIN T_Enterprise ETP ON (ORD.EnterpriseId = ETP.EnterpriseId)
         INNER JOIN T_Site SIT ON (ORD.SiteId = SIT.SiteId)
         INNER JOIN T_Customer CUS ON (CUS.OrderSeq = ORD.OrderSeq)
         LEFT OUTER JOIN T_ClaimHistory CH ON (CH.OrderSeq = CLM.OrderSeq AND CH.PrintedFlg = 0 AND CH.ValidFlg = 1)
         LEFT OUTER JOIN T_ReclaimIndicate RI ON (RI.OrderSeq = CLM.OrderSeq AND RI.IndicatedFlg = 0 AND RI.ValidFlg = 1)
         LEFT OUTER JOIN M_Code MCD ON (MCD.CodeId = 4 AND MCD.KeyCode =  IF(CUS.Incre_ArTel > CUS.Incre_ArAddr, CUS.Incre_ArTel, CUS.Incre_ArAddr))
	     INNER JOIN T_EnterpriseCustomer EC ON EC.EntCustSeq = CUS.EntCustSeq
         INNER JOIN T_ManagementCustomer MC ON MC.ManCustId = EC.ManCustId

WHERE  1 = 1
AND    (po.LetterClaimStopFlg = 0 AND IFNULL(MC.RemindStopFlg, 0) = 0)
AND    CLM.ReissueClass = 0
EOQ;

        // SQL(各種考慮:経過日数)
        if ($spanprop == 0) {// --------
            ;// 処理無し
        }
        else {
            // (検索対象フィールドの確定)
            $tdaysField = ($tdaysprop == 1) ? 'CLM.LimitDate' : 'CLM.F_LimitDate';

            if      ($spanprop == 1) {// 以上
                $sql .= (" AND " . $tdaysField . " <= " . CoatStr($sDate));
            }
            else if ($spanprop == 2) {// 以下
                $sql .= (" AND " . $tdaysField . " >= " . CoatStr($sDate));
            }
            else if ($spanprop == 3) {// から
                $sql .= (" AND " . $tdaysField . " BETWEEN " . CoatStr($eDate) . ' AND ' . CoatStr($sDate));
            }
            else if ($spanprop == 4) {// より大
                $sql .= (" AND " . $tdaysField . " < " . CoatStr($sDate));
            }
            else if ($spanprop == 5) {// 未満
                $sql .= (" AND " . $tdaysField . " > " . CoatStr($sDate));
            }
        }

        // SQL(各種考慮:請求パターン)
        if ($claimpattern > 0) {
            $where = " AND (SELECT COUNT(*) FROM T_ClaimHistory WHERE OrderSeq = ORD.OrderSeq AND PrintedFlg = 1 AND ValidFlg = 1 AND ClaimPattern = %d ) %s 0 ";
            $where = sprintf($where, $claimpattern, ($isdone == 1) ? '>' : '=');
            $sql .= $where;
        }

        // SQL(各種考慮:OEM)
        if ($oem > 0) {
            $sql .= " AND ORD.OemId = " . $oem;
        }

        // SQL(各種考慮:加盟店ID)
        if ($entid != '') {
            $sql .= " AND ETP.LoginId like '%" . BaseUtility::escapeWildcard($entid) . "' ";
        }

        // SQL(各種考慮:注文ID)
        if ($odrid != '') {
            $sql .= " AND ORD.OrderId like '%" . BaseUtility::escapeWildcard($odrid) . "' ";
        }

        // SQL(マイページ再発行区分)
        // マイページ再発行申請分を含まない
        $sql .= " AND MypageReissueClass IN ( 0, 91, 92 ) ";

        $sql .= " AND CLM.ClaimedBalance > 0 ";     // 2015/12/04 Y.Suzuki Add 暫定対応（残高が0のﾃﾞｰﾀは表示しない）

        $sql .= " ORDER BY CUS.PostalCode, OrderId ";

        // SQL実行
        $ri = $this->app->dbAdapter->query($sql)->execute(null);

        $datas = ResultInterfaceToArray($ri);

        $combinedCnlFlg = false;

        // count関数対策
        $dataCnt = 0;
        if(!empty($datas)) {
            $dataCnt = count($datas);
        }

        for ($i = 0 ; $i < $dataCnt ; $i++)
        {
            // 再請求回数取得
            $reclaimCount = $datas[$i]["Clm_Count"] - 1;    // 初回請求を含んでいる回数なので、再請求の回数としてはマイナス１
            if ($reclaimCount < 0) {
                $reclaimCount = 0;
            }

            // 経過日数算出
            $passageDays = BaseGeneralUtils::CalcSpanDaysFromString($datas[$i]["Clm_L_LimitDate"], date('Y-m-d'));
            if ($passageDays < 0) {
                $passageDays = 0;
            }

            // 遅延日数算出
            //　2013.11.21 kashira 延滞起算日定義変更対応
            // 起算日がNULL（すなわち延滞計算の必要がない）の場合は０日が返される
            $damageDays = BaseGeneralUtils::CalcSpanDaysFromString($datas[$i]["ClmExt_DamageBaseDate"], date('Y-m-d'));
            if ($damageDays < 0) {
                $damageDays = 0;
            }

            // 再請求手数料
            if (in_array($datas[$i]['MypageReissueClass'], array(1, 2))) {
                // マイページ請求書再発行の場合、マイページ請求書再発行手数料を使用
                $datas[$i]["ReClaimFee"] = $datas[$i]["MypageReissueReClaimFee"];
            }
            else {
                // 税込み金額に変換
                $datas[$i]["ReClaimFee"] = $mdlSysP->getIncludeTaxAmount(date('Y-m-d'), $datas[$i]["ReClaimFee"]);
                // 通常は再発行手数料×再請求回数
                //$datas[$i]["ReClaimFee"] = ($datas[$i]["ReClaimFee"] * ($reclaimCount + 1));
                // 前回出力時の再請求手数料 + 再請求手数料
                $datas[$i]["ReClaimFee"] = $datas[$i]["ClaimFee"] + $datas[$i]["ReClaimFee"];
            }

            // 遅延損害金
            $damageInterest = BaseGeneralUtils::CalcInterest($datas[$i]["UseAmountTotal"], $this->app->business['pay']['damagerate'], $damageDays);
            $datas[$i]["damageInterest"] = $damageInterest;

            // 再請求回数
            $datas[$i]["reclaimCount"] = $reclaimCount;

            // 今回請求額と遅延損害金適用チェックボックスの初期値
            // 再請求時は必ず遅延損害金を適用する。
            $datas[$i]["claimDamageCheck"] = "checked";
            $datas[$i]["tClaimAmount"] = $datas[$i]['ClaimedBalance'] + $damageInterest +
                ((!is_null($datas[$i]['InputReClaimFee'])) ? $datas[$i]['InputReClaimFee'] : $datas[$i]['ReClaimFee']);


            // 経過日数
            $datas[$i]["passageDays"] = $passageDays;

            // 遅延日数
            $datas[$i]["damageDays"] = $damageDays;

            // 電話結果
            $datas[$i]["RealCallResult"] = $codeMaster->getCallResultCaption($datas[$i]["RealCallResult"]);

            // マイページ再発行で配送先に請求した場合
            if ($datas[$i]['MypageReissueClass'] == 2) {
                $sql = <<<EOQ
SELECT dd.DestNameKj
     , dd.UnitingAddress
FROM   T_OrderItems oi
       INNER JOIN T_DeliveryDestination dd ON oi.DeliDestId = dd.DeliDestId
WHERE  oi.OrderSeq = :OrderSeq
AND    oi.ValidFlg = 1
AND    dd.ValidFlg = 1
ORDER BY oi.OrderItemId
EOQ;
                $prm = array(
                    ':OrderSeq' => $datas[$i]['OrderSeq'],
                );
                // 1行目を使用
                $deli = $this->app->dbAdapter->query($sql)->execute($prm)->current();

                // 請求先氏名、住所を配送先に変更
                $datas[$i]["NameKj"] = $deli['DestNameKj'];
                $datas[$i]["UnitingAddress"] = $deli['UnitingAddress'];
            }

            // 住所は先頭8文字までを表示
            $datas[$i]["UnitingAddress"] = mb_substr($datas[$i]["UnitingAddress"], 0, 8, 'UTF-8');

            // キャンセル未承認の取りまとめデータの有無フラグ
            if (intval($datas[$i]["CombinedCnlCnt"]) > 0) {
                $combinedCnlFlg = true;
            }

            // 再請求書発行制御パターン
            if (($datas[$i]["ReClaimIssueCtlFlg"] == 1) && (!is_null($datas[$i]["ForceCancelClaimPattern"]))) {
                $datas[$i]["ReClaimIssueCtlPattern"] = $datas[$i]["ForceCancelClaimPattern"];
            } else {
                $datas[$i]["ReClaimIssueCtlPattern"] = 0;
            }
        }

        $this->view->assign("list", $datas);
        $this->view->assign("cnt", $i);
        $this->view->assign("sld", $sld);
        $this->view->assign("eld", $eld);
        $this->view->assign("tdaysSelectTag",BaseHtmlUtils::SelectTag('tdaysprop',$codeMaster->getMasterCodes(81),$tdaysprop));
        $this->view->assign("spanSelectTag" ,BaseHtmlUtils::SelectTag('spanprop' ,$codeMaster->getMasterCodes(82),$spanprop,'onChange="javascript:controlSpan(); "'));
        $this->view->assign("claimPatternSearchTag",BaseHtmlUtils::SelectTag('claimpattern',$codeMaster->getMasterCodes(12,  array(0 => '----------')),$claimpattern,'onChange="javascript:changeCp(); "'));
        $cdmst12 = $codeMaster->getMasterCodes(12);
        unset($cdmst12['1']);
        $this->view->assign("claimPatternPrintTag",BaseHtmlUtils::SelectTag('ClaimPattern',$cdmst12));
        $this->view->assign("isDoneTag",BaseHtmlUtils::SelectTag('isdone',array(1 => '発行している', 0 => '発行していない'),$isdone));
        $mdloem = new TableOem($this->app->dbAdapter);
        $this->view->assign("oemTag",BaseHtmlUtils::SelectTag('oem', $mdloem->getOemIdList(), $oem));
        $this->view->assign("entid", $entid);
        $this->view->assign("odrid", $odrid);
        $this->view->assign("combinedCnlFlg", $combinedCnlFlg);

        return $this->view;
	}

// Del By Takemasa(NDC) 20150501 Stt 廃止(listActionを代替使用のこと)
// 	/**
// 	 * 再請求個別登録用リスト
// 	 *
// 	 */
// 	public function listoneAction()
// Del By Takemasa(NDC) 20150501 End 廃止(listActionを代替使用のこと)

	/**
	 * 再請求実行
	 */
	public function doneAction()
	{
        ini_set('max_execution_time', 0);		// 実行タイムアウトを無効にしておく（2015.6.9 eda）

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

                /*
                 　1：初回請求
                　2：再請求１
                　3：再請求２
                　4：再請求３
                　5：内容証明
                　6：再請求４
                　7：再請求５
                　8：再請求６
                　9：再請求７
                */
                // 2010.5.17 紙請求ストップとメール請求ストップの制御
                // 2014.4.15 依頼書により仕様を変更
                // 2014.8.11 依頼書により仕様を変更
                // 立替確定条件が顧客入金であるか否か。
                // $isPayChgConEq2 = $mdldeli->isPayChgConditionEq2($oseq);
                $isPayChgConEq2 = false;
                $order = $mdlo->find($oseq)->current();
                $outOfAmends = $order['OutOfAmends'];

                $letterClaimStopFlg = 0;
                $mailClaimStopFlg = 0;

                if ($outOfAmends == 1 && $isPayChgConEq2 && $data['ClaimPattern'] >= 3)
                {
                    // 状態１
                    $letterClaimStopFlg = 1;
                    $mailClaimStopFlg = 0;
                }
                else if ($outOfAmends == 1 && !$isPayChgConEq2 && $data['ClaimPattern'] >= 3)
                {
                    // 状態２
                    $letterClaimStopFlg = 1;
                    $mailClaimStopFlg = 0;
                }
                else if ($outOfAmends != 1 && $isPayChgConEq2 && $data['ClaimPattern'] >= 9)
                {
                    // 状態３
                    // 2014.4.15 状態3は全てｏｆｆ
                    // 2014.8.11 状態3は再請求7の場合に紙請求ストップ
                    $letterClaimStopFlg = 1;
                    $mailClaimStopFlg = 0;
                }
                else if ($outOfAmends != 1 && !$isPayChgConEq2 && $data['ClaimPattern'] >= 9)
                {
                    // 状態４
                    // 2014.4.15 状態4は全てｏｆｆ
                    // 2014.8.11 状態4は際請求7の場合に紙請求ストップ
                    $letterClaimStopFlg = 1;
                    $mailClaimStopFlg = 0;
                }
                else if ($isPayChgConEq2 && $data['ClaimPattern'] >= 3)
                {
                    // 状態５
                    $letterClaimStopFlg = 1;
                    $mailClaimStopFlg = 0;
                }
                else if ($outOfAmends == 1 && $data['ClaimPattern'] >= 3)
                {
                    // 状態６
                    $letterClaimStopFlg = 1;
                    $mailClaimStopFlg = 0;
                }
                else
                {
                    // 再請求を行った場合、無条件に紙請求ストップ、メール請求ストップを解除する。
                    $letterClaimStopFlg = 0;
                    $mailClaimStopFlg = 0;
                }

                // 注文の確定待ちフラグをアップ
                $uOrder = array(
                        'LetterClaimStopFlg' => $letterClaimStopFlg,
                        'MailClaimStopFlg'   => $mailClaimStopFlg,
                        'UpdateId'           => $userId,
                );

                $mdlo->saveUpdateWhere($uOrder, array('P_OrderSeq' => $oseq));

                // 履歴登録用理由コードを設定
                if ($data['ClaimPattern'] == 2) {
                    $reasonCode = 43;       // 再請求書発行（再１）
                } else if ($data['ClaimPattern'] == 4) {
                    $reasonCode = 44;       // 再請求書発行（再３）
                } else if ($data['ClaimPattern'] == 6) {
                    $reasonCode = 45;       // 再請求書発行（再４）
                } else if ($data['ClaimPattern'] == 7) {
                    $reasonCode = 46;       // 再請求書発行（再５）
                } else if ($data['ClaimPattern'] == 8) {
                    $reasonCode = 47;       // 再請求書発行（再６）
                } else if ($data['ClaimPattern'] == 9) {
                    $reasonCode = 48;       // 再請求書発行（再７）
                }

                // 注文履歴登録用に親注文Seqから子注文Seqを再取得する。
                $sql = <<<EOQ
                    SELECT  OrderSeq
                    FROM    T_Order
                    WHERE   P_OrderSeq = :P_OrderSeq
                    AND     Cnl_Status = 0
                    ;
EOQ;

                $ri = $this->app->dbAdapter->query($sql)->execute(array(':P_OrderSeq' => $params['OrderSeq' . $i]));
                $rows = ResultInterfaceToArray($ri);

                // 注文履歴へ登録
                $history = new CoralHistoryOrder($this->app->dbAdapter);
                // 取得できた件数分ループする
                foreach ($rows as $row) {
                    // 備考に保存
                    $mdlo->appendPrintedInfoToOemNote($row["OrderSeq"]);
                    // 注文履歴登録
                    $history->InsOrderHistory($row["OrderSeq"], $reasonCode, $userId);
                }

                $transferCount++;

                // 請求履歴．印刷ステータス(PrintedStatus)を"9"(印刷済み)に更新する
                $this->app->dbAdapter->query(" UPDATE T_ClaimHistory SET PrintedStatus = 9 WHERE Seq = :Seq ")->execute(array(':Seq' => $data['Seq']));

                $this->app->dbAdapter->getDriver()->getConnection()->commit();
            }
            catch (\Exception $e) {
                $this->app->dbAdapter->getDriver()->getConnection()->rollback();
$this->app->logger->err($e->getMessage());
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
     */
    public function jobparamsetAction()
    {
        // セッションに情報をセットする
        unset($_SESSION[self::SESSION_JOB_PARAMS]);
        $_SESSION[self::SESSION_JOB_PARAMS] = $this->getParams();

        return;
    }

    /**
     * 印刷指示処理
     */
    public function csvoutputAction()
    {

        // 印刷指示はジョブ転送はせずに再請求指示のみ行う。
        //// ジョブ転送
        //$this->jobTransfer('chkCsv');
        //// 印刷指示処理
        //$csvData = $this->csvDownload();

        // 再請求指示処理
        $this->reclaimIndicate();

        $msg = '1';

        // セッションクリア
        unset($_SESSION[self::SESSION_JOB_PARAMS]);

        echo \Zend\Json\Json::encode(array('status' => $msg));
        return $this->response;
    }

    /**
     * 印刷処理
     */
    public function printAction()
    {
        // ジョブ転送
        $this->jobTransfer('chkPrint');

        // PDF出力
        $pdf = $this->pdfDownload();

        // セッションクリア
        unset($_SESSION[self::SESSION_JOB_PARAMS]);

        return $pdf;
    }

	/**
     * (Ajax)紙STOPに更新処理
     */
    public function upstopclaimAction()
    {
        try
        {
            $params = $this->getParams();

            // ユーザーIDの取得
            $obj = new \models\Table\TableUser($this->app->dbAdapter);
            $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

            $mdlo = new TableOrder($this->app->dbAdapter);

            $i = 0;

            while (isset($params['OrderSeq' . $i]))
            {
                if (!isset($params['chkStopClaim' . $i])) { $i++; continue; }

                // 取りまとめ注文を全て更新する
                $sql = <<<EOQ
                    SELECT  OrderSeq
                    FROM    T_Order
                    WHERE   P_OrderSeq = :P_OrderSeq
                    AND     Cnl_Status = 0
                    ;
EOQ;
                $ri = $this->app->dbAdapter->query($sql)->execute(array(':P_OrderSeq' => $params['OrderSeq' . $i]));
                $oseqs = ResultInterfaceToArray($ri);

                // 取得できた件数分ループする
                foreach ($oseqs as $oseq) {
                    $mdlo->saveUpdate(array('LetterClaimStopFlg' => 1, 'UpdateId' => $userId), $oseq["OrderSeq"]);
                }

                $i++;
            }

            // 成功指示
            $msg = '1';
        }
        catch(\Exception $e)
        {
            $msg = $e->getMessage();
        }

        echo \Zend\Json\Json::encode(array('status' => $msg));
        return $this->response;
    }

    /**
     * 印刷指示処理
     */
    protected function csvDownload() {
        $params = $_SESSION[self::SESSION_JOB_PARAMS];

        // ジョブ転送処理後、請求履歴．印刷ステータス(PrintedStatus)を"1"(CSV印刷指示)に更新する
        $sql = " UPDATE T_ClaimHistory SET PrintedStatus = 1 WHERE OrderSeq = :OrderSeq AND PrintedFlg = 0 AND ValidFlg = 1 AND PrintedStatus = 0 ";
        $stm = $this->app->dbAdapter->query($sql);

        $i = 0;
        while( isset( $params['OrderSeq' . $i] ) ) {
            if( !isset( $params['chkCsv' . $i ] ) ) {
                $i++;
                continue;
            }

            $stm->execute(array(':OrderSeq' => $params['OrderSeq' . $i]));
            $i++;
        }
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

    /**
     * JNB口座オープン用のロックアイテムを獲得する
     *
     * @access protected
     * @param array 対象注文の行オブジェクト
     * @return \models\Logic\ThreadPool\LogicThreadPoolItem | null
     */
    protected function getLockItemForJnbAccount($orderRow = null)
    {
        if(!$orderRow) return null;

        $jnbTable = new \models\Table\TableJnb($this->app->dbAdapter);
        $jnb = $jnbTable->findByOemId((int)$orderRow['OemId'])->current();
        if(!$jnb) return null;

        $pool = \models\Logic\LogicThreadPool::getPoolForJnbAccountOpen($jnb['JnbId'], $this->app->dbAdapter);
        return $pool->openAsSingleton($orderRow['OrderSeq']);
    }

    /**
     * ジョブ転送を行う
     */
    protected function jobTransfer($checkClass) {
        try
        {
            $params = $_SESSION[self::SESSION_JOB_PARAMS];

            $mdlch = new TableClaimHistory($this->app->dbAdapter);
            $mdls = new TableSite($this->app->dbAdapter);
            $mdlo = new TableOrder($this->app->dbAdapter);
            $mdloem = new TableOem($this->app->dbAdapter);

            $mdldeli = new ViewDelivery($this->app->dbAdapter);
            $mdlcc = new TableClaimControl($this->app->dbAdapter);

            $mdlri = new TableReclaimIndicate($this->app->dbAdapter);

            // ユーザーIDの取得
            $obj = new \models\Table\TableUser($this->app->dbAdapter);
            $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

            $mailFlg = 0;
            // checkClass=chkCsvの流入経路は廃止、reclaimIndicateに変更
            // 印刷指示分のmailflg制御は後述
            //if ($checkClass == 'chkCsv') {
            //    $mailFlg = 1;
            //}

            $i = 0;
            $transferCount = 0;
            $errorCount = 0;

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

                // 請求履歴が有効かどうか判定
                if ($mdlch->getReservedCount($oseq) > 0) {
                    // ジョブ転送中のデータがいる場合はスキップ
                    $i++;
                    continue;
                }

                // 指示中であれば指示データ取得
                $indicate = $mdlri->getIndicate($oseq)->current();

                // ----------------------------------------
                // ジョブ転送処理
                // ----------------------------------------
                // JNB口座オープン用にロック獲得を試行
                $order = $mdlo->find($oseq)->current();
                $lockItem = $this->getLockItemForJnbAccount($order);

                try {
                    //トランザクション開始
                    $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();

                    // 請求管理データ取得
                    $claimRow = $mdlcc->findClaim(array('OrderSeq' => $oseq))->current();

                    if (isset($indicate['Seq'])){
                        // 指示中データの場合は指示時の情報を元に請求履歴作成処理

                        // 請求担当者
                        $ClaimCpId = $indicate['ClaimCpId'];

                        // 遅延日数
                        $damageDays = $indicate['DamageDays'];
                        // 遅延日数算出基準日
                        $strDamageBaseDate = $indicate['DamageBaseDate'];
                        // 遅延損害金
                        $damageInterest = $indicate['DamageInterestAmount'];
                        // 請求手数料
                        $reClaimFee = $indicate['ClaimFee'];
                        // 請求追加手数料
                        $AdditionalClaimFee = $indicate['AdditionalClaimFee'];

                        // 請求金額
                        $claimAmount = $indicate['ClaimAmount'];

                        // 請求書発行通知メール
                        $mailFlg = 1;

                    }else{
                        // 請求担当者
                        $ClaimCpId = $this->app->authManagerAdmin->getUserInfo()->OpId;

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

                        // 遅延日数算出
                        $damageDays = BaseGeneralUtils::CalcSpanDaysFromString($claimRow['DamageBaseDate'], date('Y-m-d'));
                        if ($damageDays < 0)
                        {
                            $damageDays = 0;
                        }

                        // 遅延損害金算出
                        // 2015/12/03 Y.Suzuki Mod Stt
                        // 新システム・旧システムに関わらず、遅延損害金は一切とらない
                        $damageInterest = 0;
                        //                     if ($order['NewSystemFlg'] == 0) {
                        //                         // 旧システムのデータは遅延損害金は変更なし
                        //                         $damageInterest = $claimRow['DamageInterestAmount'];
                        //                     }
                        //                     else {
                        //                         // 新システムのデータは再計算する
                        //                         $damageInterest = BaseGeneralUtils::CalcInterest($useAmount, $this->app->business['pay']['damagerate'], $damageDays);
                        //                         // 遅延損害金を適用する。ただし、再請求1の場合は遅延損害金を適用しない
                        //                         if($this->_ignoreDamageAmoutnForReclaim1) {
                        //                             if($params['ClaimPattern'] == 2) {
                        //                                 $damageInterest = 0;
                        //                             }
                        //                         }
                        //                     }
                        // 2015/12/03 Y.Suzuki Mod End

                        // 請求額算出
                        $reClaimFee = $params['ReClaimFee' . $i];
                        $claimAmount = $useAmount + $reClaimFee + $damageInterest;

                        $strDamageBaseDate = ($damageDays > 0 ? $claimRow['DamageBaseDate'] : null);

                        // 請求追加手数料
                        $AdditionalClaimFee = 0;

                        // 請求書発行通知メール
                        $mailFlg = 0;
                    }

                    // 期限日までの日数
                    $ldays = $this->app->business['pay']['limitdays2'];

                    // 遅延起算日日数を期限日までの日数に加算する。
                    $ldays += $mdls->findSite($order['SiteId'])->current()['KisanbiDelayDays'];

                    // 支払期限日算出
                    $limitDate = date('Y-m-d', strtotime('+' . $ldays . 'day'));

                    // 延滞起算日がNULLの場合は今回の支払期限を次回からの延滞起算日にするためDBへ書き込み
                    if (strlen($claimRow['DamageBaseDate']) == 0) {
                        $mdlcc->saveUpdate(array('DamageBaseDate' => $limitDate, 'UpdateId' => $userId), $claimRow['ClaimId']);
                    }

                    // 請求データ取得
                    $claimData = $this->app->dbAdapter->query(" SELECT ClaimId, ClaimedBalance FROM T_ClaimControl WHERE OrderSeq = :OrderSeq; ")->execute(array( ':OrderSeq' => $oseq ))->current();

                    // 請求履歴の作成
                    $data = array(
                            'OrderSeq' => $oseq,                                // 注文Seq
                            'ClaimDate' => date('Y-m-d'),                       // 請求日
                            'ClaimCpId' => $ClaimCpId,                          // 請求担当者
                            'ClaimPattern' => $params['ClaimPattern'],          // 請求パターン
                            'LimitDate' => $limitDate,                          // 支払期限
                            'DamageDays' => $damageDays,                        // 遅延日数
                            'DamageBaseDate' => $strDamageBaseDate,             // 遅延日数算出基準日
                            'DamageInterestAmount' => $damageInterest,          // 遅延損害金
                            'ClaimFee' => $reClaimFee,                          // 請求手数料
                            'AdditionalClaimFee' => $AdditionalClaimFee,        // 請求追加手数料
                            'PrintedFlg' => 0,                                  // 印刷－処理フラグ
                            'MailFlg' => $mailFlg,                              // 請求書発行通知メール
                            'EnterpriseBillingCode' => null,                    // 同梱ツールアクセスキー
                            'ClaimAmount' => $claimAmount,                      // 請求金額
                            'ClaimId' => $claimData['ClaimId'],                 // 請求ID
                            'RegistId' => $userId,                              // 登録者
                            'UpdateId' => $userId,                              // 更新者
                    );

                    $hisSeq = $mdlch->saveNew($oseq, $data);

                    $uOrder = array(
                            'ConfirmWaitingFlg'  => '1',
                            'UpdateId'           => $userId,
                    );

                    $mdlo->saveUpdateWhere($uOrder, array('P_OrderSeq' => $oseq));

                    if (isset($indicate['Seq'])){
                        // 指示中データを処理した場合は、指示データを処理済に変更
                        $mdlri->updateIndicated($oseq,$userId,$hisSeq);
                    }

                    $transferCount++;

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
        catch(\Exception $e)
        {
            throw $e;
        }
    }

    /**
     * 再請求指示処理を行う
     */
    protected function reclaimIndicate() {
        try
        {
            $params = $_SESSION[self::SESSION_JOB_PARAMS];

            $mdlri = new TableReclaimIndicate($this->app->dbAdapter);
            $mdlch = new TableClaimHistory($this->app->dbAdapter);
            $mdlo = new TableOrder($this->app->dbAdapter);

            $mdlcc = new TableClaimControl($this->app->dbAdapter);

            // ユーザーIDの取得
            $obj = new \models\Table\TableUser($this->app->dbAdapter);
            $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

            $i = 0;

            while (isset($params['OrderSeq' . $i])) {
                if (!isset($params['chkCsv' . $i])) { $i++; continue; }
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

                // 請求履歴が有効かどうか判定
                if ($mdlch->getReservedCount($oseq) > 0) {
                    // ジョブ転送中のデータがいる場合はスキップ
                    $i++;
                    continue;
                }

                // 再請求指示が有効かどうか判定
                if ($mdlri->getIndicateCount($oseq) > 0) {
                    // 指示中のデータがいる場合はスキップ
                    $i++;
                    continue;
                }

                // ----------------------------------------
                //再請求指示処理
                // ----------------------------------------
                try {
                    //トランザクション開始
                    $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();

                    // 請求管理データ取得
                    $claimRow = $mdlcc->findClaim(array('OrderSeq' => $oseq))->current();

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

                    // 遅延日数算出
                    $damageDays = BaseGeneralUtils::CalcSpanDaysFromString($claimRow['DamageBaseDate'], date('Y-m-d'));
                    if ($damageDays < 0)
                    {
                        $damageDays = 0;
                    }

                    // 遅延損害金算出
                    // 新システム・旧システムに関わらず、遅延損害金は一切とらない
                    $damageInterest = 0;
                    //                     if ($order['NewSystemFlg'] == 0) {
                    //                         // 旧システムのデータは遅延損害金は変更なし
                    //                         $damageInterest = $claimRow['DamageInterestAmount'];
                    //                     }
                    //                     else {
                    //                         // 新システムのデータは再計算する
                    //                         $damageInterest = BaseGeneralUtils::CalcInterest($useAmount, $this->app->business['pay']['damagerate'], $damageDays);
                    //                         // 遅延損害金を適用する。ただし、再請求1の場合は遅延損害金を適用しない
                    //                         if($this->_ignoreDamageAmoutnForReclaim1) {
                    //                             if($params['ClaimPattern'] == 2) {
                    //                                 $damageInterest = 0;
                    //                             }
                    //                         }
                    //                     }

                    // 遅延日数算出基準日
                    $strDamageBaseDate = ($damageDays > 0 ? $claimRow['DamageBaseDate'] : null);

                    // 請求額算出
                    $claimAmount = $useAmount + $params['ReClaimFee' . $i] + $damageInterest;

                    // 請求データ取得
                    // 既に上で取得しているので再取得は不要
                    $claimData = $this->app->dbAdapter->query(" SELECT ClaimId, ClaimedBalance FROM T_ClaimControl WHERE OrderSeq = :OrderSeq; ")->execute(array( ':OrderSeq' => $oseq ))->current();

                    // 再請求指示の作成
                    $data = array(
                            'OrderSeq' => $oseq,                                // 注文Seq
                            'ClaimCpId' => $this->app->authManagerAdmin->getUserInfo()->OpId,  // 請求担当者
                            'ClaimPattern' => $params['ClaimPattern'],          // 請求パターン
                            'DamageDays' => $damageDays,                        // 遅延日数
                            'DamageBaseDate' => $strDamageBaseDate,             // 遅延日数算出基準日
                            'DamageInterestAmount' => $damageInterest,          // 遅延損害金
                            'ClaimFee' => $params['ReClaimFee' . $i],           // 請求手数料
                            'AdditionalClaimFee' => 0,                          // 請求追加手数料
                            'ClaimAmount' => $claimAmount,                      // 請求金額
                            'ClaimId' => $claimRow['ClaimId'],                  // 請求ID
                            'IndicateDate' => date('Y-m-d'),                    // 指示日
                            'IndicatedFlg' => 0,                                // 指示－処理フラグ
                            'RegistId' => $userId,                              // 登録者
                            'UpdateId' => $userId,                              // 更新者
                    );
                    $riSeq = $mdlri->saveNew($oseq, $data);

                    $this->app->dbAdapter->getDriver()->getConnection()->commit();

                } catch (\Exception $e) {
                    $this->app->dbAdapter->getDriver()->getConnection()->rollback();
                    // 処理失敗
                    throw $e;
                }
                $i++;
            }
        }
        catch(\Exception $e)
        {
            throw $e;
        }
    }

    /**
     * pdfダウンロード
     */
    protected function pdfDownload() {
        $params = $_SESSION[self::SESSION_JOB_PARAMS];

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

        $oemid = 0;

        $datas = array();
        $i = 0;
        while( isset( $params['OrderSeq' . $i] ) ) {
            if( !isset( $params['chkPrint' . $i ] ) ) {
                $i++;
                continue;
            }
            $data = array();
            $prm = array( ':OrderSeq' => $params['OrderSeq' . $i] );

            $oemid = $params['OemId' . $i];

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
            $sql .= ' ,      o.Ent_OrderId ';
            $sql .= ' ,      ca.TaxAmount ';
            $sql .= ' ,      ca.Cv_BarcodeData ';
            $sql .= ' ,      ca.Cv_BarcodeString1 ';
            $sql .= ' ,      ca.Cv_BarcodeString2 ';
            $sql .= ' ,      ca.Bk_BankName ';
            $sql .= ' ,      ca.Bk_BranchName ';
            $sql .= ' ,      ca.Bk_DepositClass ';
            $sql .= ' ,      ca.Bk_AccountNumber ';
            $sql .= ' ,      ca.Bk_AccountHolderKn ';
            $sql .= ' ,      ca.Yu_SubscriberName ';
            $sql .= ' ,      ca.Yu_AccountNumber ';
            $sql .= ' ,      ca.Yu_MtOcrCode1 ';
            $sql .= ' ,      ca.Yu_MtOcrCode2 ';
            $sql .= ' ,      IFNULL(o.OemId, 0) AS OemId ';
            $sql .= ' ,      oem.PostalCode AS OemPostalCode ';
            $sql .= ' ,      oem.PrefectureName AS OemPrefectureName ';
            $sql .= ' ,      oem.City AS OemCity ';
            $sql .= ' ,      oem.Town AS OemTown ';
            $sql .= ' ,      oem.Building AS OemBuilding ';
            $sql .= ' ,      oem.OemNameKj ';
            $sql .= ' ,      oem.ContactPhoneNumber AS OemContactPhoneNumber ';
            $sql .= ' ,      cd105.KeyContent AS MypageUrl ';
            $sql .= ' ,      cc.MypageReissueClass ';
            $sql .= ' ,      cd108.KeyContent AS PrintContactPhoneNumber ';
            $sql .= ' ,      cc.ReceiptAmountTotal ';
            $sql .= ' FROM   T_Order o INNER JOIN ';
            $sql .= '        T_Customer c ON ( o.OrderSeq = c.OrderSeq ) INNER JOIN ';
            $sql .= '        T_Enterprise e ON ( o.EnterpriseId = e.EnterpriseId ) INNER JOIN ';
            $sql .= '        T_Site s ON ( o.SiteId = s.SiteId ) INNER JOIN ';
            $sql .= '        T_ClaimControl cc ON o.OrderSeq = cc.OrderSeq LEFT OUTER JOIN ';
            $sql .= '        T_ClaimHistory ch ON ( o.OrderSeq = ch.OrderSeq AND ch.PrintedFlg = 0 AND ch.ValidFlg = 1 ) LEFT OUTER JOIN ';
            $sql .= '        T_OemClaimAccountInfo ca ON( ch.Seq = ca.ClaimHistorySeq AND ca.Status = 1 ) LEFT OUTER JOIN ';
            $sql .= '        T_Oem oem ON( o.OemId = oem.OemId ) LEFT OUTER JOIN ';
            $sql .= '        M_Code cd105 ON( cd105.CodeId = 105 AND IFNULL(o.OemId, 0) = cd105.KeyCode) LEFT OUTER JOIN  ';
            $sql .= '        M_Code cd108 ON( cd108.CodeId = 108 AND IFNULL(o.OemId, 0) = cd108.KeyCode) ';
            $sql .= ' WHERE  o.OrderSeq = :OrderSeq ';
            $sql .= '   AND  EXISTS (SELECT * FROM T_Order t WHERE t.P_OrderSeq = o.OrderSeq AND t.Cnl_Status = 0) ';
            $sql .= "   AND  cc.ClaimedBalance > 0 ";       // 2015/12/04 Y.Suzuki Add 暫定対応（残高が0のﾃﾞｰﾀは出力しない）
            $sql .= "   AND  ch.ClaimAmount > 0 ";

            $data = $this->app->dbAdapter->query( $sql )->execute( $prm )->current();

            if (!$data) {
                // 有効な注文データがない場合はスキップ
                $i++;
                continue;
            }

            // 請求金額
            $data['BilledAmt'] = nvl( $data['ClaimAmount'], 0 ) - nvl( $data['ReceiptAmountTotal'], 0 );

            // 請求金額が30万円以上だった場合
            if( $data['BilledAmt'] >= 300000 ) {
                $data['Cv_BarcodeData'] = '収納代行の規約によりコンビニエンスストアで30万円以上のお支払はできません';
                $data['Cv_BarcodeString1'] = '';
                $data['Cv_BarcodeString2'] = '';
            }

            // 任意注文番号非表示の加盟店
            if ($data['PrintEntOrderIdOnClaimFlg'] == 0) {
                $data['Ent_OrderId'] = '';
            }

            // 注文商品＋配送先
            $sql  = ' SELECT itm.ItemNameKj ';
            $sql .= ' ,      itm.ItemNum ';
            $sql .= ' ,      itm.UnitPrice ';
            $sql .= ' ,      itm.SumMoney ';
            $sql .= ' ,      e.DispDecimalPoint ';  /* 表示用小数点桁数 */
            $sql .= ' ,      dd.PostalCode ';       /* 配送先郵便番号 */
            $sql .= ' ,      dd.UnitingAddress ';   /* 配送先住所 */
            $sql .= ' ,      dd.DestNameKj ';       /* 配送先氏名 */
            $sql .= ' FROM   T_Order o INNER JOIN ';
            $sql .= '        T_OrderItems itm ON ( o.OrderSeq = itm.OrderSeq ) ';
            $sql .= '        INNER JOIN T_Enterprise e ON ( e.EnterpriseId = o.EnterpriseId ) ';
            $sql .= '        INNER JOIN T_DeliveryDestination dd ON ( itm.DeliDestId = dd.DeliDestId ) ';
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
                    $data['ItemNum_' . $j] = number_format($data['ItemNum_' . $j], $items[$j - 1]['DispDecimalPoint'], ',', '');
                }
                $data['UnitPrice_' . $j] = isset( $items[$j - 1]['UnitPrice'] ) ? $items[$j - 1]['UnitPrice'] : '';
                $data['SumMoney_' . $j] = isset( $items[$j - 1]['SumMoney'] ) ? $items[$j - 1]['SumMoney'] : '';
            }

            // 入金情報
            if ( nvl( $data['ReceiptAmountTotal'], 0 ) > 0 ) {
                // 入金されている場合、第15明細に情報を設定する
                $data['ItemNameKj_15'] = '入金済額';
                $data['ItemNum_15'] = '1';
                $data['UnitPrice_15'] = $data['ReceiptAmountTotal'] * -1;
                $data['SumMoney_15'] = $data['ReceiptAmountTotal'] * -1;
            }

            // 小計
            $data['TotalItemPrice'] = 0;
            foreach ($items as $row) {
                $data['TotalItemPrice'] += $row['SumMoney'];
            }

            // マイページ再発行で配送先に請求した場合
            if ($data['MypageReissueClass'] == 2) {
                // 請求先情報を配送先情報（第一明細を使用）に変更
                $data['PostalCode'] = $items[0]['PostalCode'];
                $data['UnitingAddress'] = $items[0]['UnitingAddress'];
                $data['NameKj'] = $items[0]['DestNameKj'];
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
            $sql  = ' SELECT COUNT(*) AS ReIssueCount ';
            $sql .= ' FROM   T_ClaimHistory ';
            $sql .= ' WHERE  PrintedFlg <> 0 ';
            $sql .= ' AND    ValidFlg = 1 ';
            $sql .= ' AND    OrderSeq = :OrderSeq ';
            $data = array_merge( $data, $this->app->dbAdapter->query( $sql )->execute( $prm )->current() );
            $data['ReIssueCount'] += 1;

            // マイページログインパスワード
            $row_mypageorder = $this->app->dbAdapter->query(" SELECT Token FROM T_MypageOrder WHERE OrderSeq = :OrderSeq ORDER BY Seq DESC LIMIT 1 "
            )->execute(array(':OrderSeq' => $params['OrderSeq' . $i]))->current();
            $data['MypageToken'] = ($row_mypageorder) ? $row_mypageorder['Token'] : '';

            // 商品合計数
            $data['ItemsCount'] = 0;

            // count関数対策
            if(!empty($items)) {
                $data['ItemsCount'] = count($items);
            }

            // CB情報
            $data['CbPost'] = $cbPost;
            $data['CbAddress'] = $cbAddress;
            $data['CbName'] = $cbName;
            $data['CbTel'] = $cbTel;

            // 払込取扱表番号
            $paymentNumber = '3';

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
                $accept = $data['CbName'];

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
                    $accept = $data['OemNameKj'];
                }
                else {
                    // SMBCの場合、CB情報
                    $accept1 = $data['CbName'];
                    $accept2 = 'TEL:' . $data['CbTel'];
                    $accept = $data['CbName'];
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
            $data['PaymentNumber'] = $paymentNumber;
            $data['Accept'] = $accept;

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
            if( $data['BilledAmt'] < 300000 ) {
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

        // 用紙(コードID = 182、Keyコード = ((OEMID(CBの場合は0) * 10) + 請求パターン)　で取得した区分３)
        $keycode = ((int)$oemid * 10 + (int)$params['ClaimPattern']);
        $paperType = $this->app->dbAdapter->query(" SELECT Class3 FROM M_Code WHERE CodeId = 182 AND KeyCode = :KeyCode "
            )->execute(array(':KeyCode' => $keycode))->current()['Class3'];

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
        elseif ($paperType == 2) {
            // 督促用A4
            $fileName = sprintf( 'Tokusoku_%s.pdf', date( "YmdHis" ) );

            $this->setTemplate('billedtokusoku');

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
            $option = " --page-size A4 --orientation Portrait --margin-left 0 --margin-right 0 --margin-top 0 --margin-bottom 0 ";
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
     * 再請求書発行待ちリスト(CSV一括出力)
     */
    public function simplelistAction()
    {
        $params = $this->getParams();

        $this->addJavaScript('../js/corelib.js');
        $this->addStyleSheet('../css/base.ui.customlist.css');
        $this->addJavaScript('../js/base.ui.js');
        $this->addJavaScript('../js/base.ui.customlist.js');

        // 確定待ち件数の取得
        $sql  = " SELECT COUNT(1) AS cnt ";
        $sql .= " FROM   T_ClaimHistory ch ";
        $sql .= "        INNER JOIN T_Order o ON (o.OrderSeq = ch.OrderSeq) ";
        $sql .= " WHERE  1 = 1 ";
        $sql .= " AND    ch.ClaimPattern = :ClaimPattern ";
        $sql .= " AND    ch.PrintedStatus IN (2, 3) ";
        $sql .= " AND    ch.PrintedFlg = 0 ";
        $sql .= " AND    ch.ValidFlg = 1 ";
        $sql .= " AND    (o.Cnl_Status IS NULL OR o.Cnl_Status = 0) ";
        $sql .= " AND    (o.LetterClaimStopFlg IS NULL OR o.LetterClaimStopFlg = 0) ";

        $stm = $this->app->dbAdapter->query($sql);

        // 各再請求確定待ち件数
        $this->view->assign('CntWaitReclaim7', $stm->execute(array(':ClaimPattern' => 9))->current()['cnt']);// 再請求７確定待ち
        $this->view->assign('CntWaitReclaim6', $stm->execute(array(':ClaimPattern' => 8))->current()['cnt']);// 再請求６確定待ち
        $this->view->assign('CntWaitReclaim5', $stm->execute(array(':ClaimPattern' => 7))->current()['cnt']);// 再請求５確定待ち
        $this->view->assign('CntWaitReclaim4', $stm->execute(array(':ClaimPattern' => 6))->current()['cnt']);// 再請求４確定待ち
        $this->view->assign('CntWaitReclaim3', $stm->execute(array(':ClaimPattern' => 4))->current()['cnt']);// 再請求３確定待ち
        $this->view->assign('CntWaitReclaim1', $stm->execute(array(':ClaimPattern' => 2))->current()['cnt']);// 再請求１確定待ち

        // 再請求出力設定
        $sql = " SELECT Class3, Class1 FROM M_Code WHERE CodeId = 183 AND KeyCode = :KeyCode ";
        $stm = $this->app->dbAdapter->query($sql);
        $this->view->assign('EstReclaim7', $stm->execute(array(':KeyCode' => 9))->current());// 再請求７
        $this->view->assign('EstReclaim6', $stm->execute(array(':KeyCode' => 8))->current());// 再請求６
        $this->view->assign('EstReclaim5', $stm->execute(array(':KeyCode' => 7))->current());// 再請求５
        $this->view->assign('EstReclaim4', $stm->execute(array(':KeyCode' => 6))->current());// 再請求４
        $this->view->assign('EstReclaim3', $stm->execute(array(':KeyCode' => 4))->current());// 再請求３
        $this->view->assign('EstReclaim1', $stm->execute(array(':KeyCode' => 2))->current());// 再請求１

        return $this->view;
    }

    /**
     * (Ajax)ジョブ転送を行う
     */
    public function simplecsv1Action()
    {
        $params = $this->getParams();
        $status = 1;
        $message = "";

        try {
            // １．ジョブ転送処理を実施
            $ceSeqs = array();

            // (画面．再請求７出力にチェックがある場合、再７のジョブ転送を行う)
            if (isset($params['ReClaim7'])) {
                $this->jobTransferForSimple(9, false, $ceSeqs);
            }
            // (画面．再請求６出力にチェックがある場合、再６のジョブ転送を行う)
            if (isset($params['ReClaim6'])) {
                $this->jobTransferForSimple(8, false, $ceSeqs);
            }
            // (画面．再請求５出力にチェックがある場合、再５のジョブ転送を行う)
            if (isset($params['ReClaim5'])) {
                $this->jobTransferForSimple(7, false, $ceSeqs);
            }
            // (画面．再請求４出力にチェックがある場合、再４のジョブ転送を行う)
            if (isset($params['ReClaim4'])) {
                $this->jobTransferForSimple(6, false, $ceSeqs);
            }
            // (画面．再請求３出力にチェックがある場合、再３のジョブ転送を行う)
            if (isset($params['ReClaim3'])) {
                $this->jobTransferForSimple(4, false, $ceSeqs);
            }
            // (画面．再請求１出力にチェックがある場合、再１のジョブ転送を行う)
            if (isset($params['ReClaim1'])) {
                $this->jobTransferForSimple(2, false, $ceSeqs);
                $this->jobTransferForSimple(2, true, $ceSeqs);
            }

            if (!empty($ceSeqs)) {
                // 請求エラーがある場合
                $status = 2;
                $message = $this->getStatusCaption($ceSeqs);
            }

        }
        catch(\Exception $e) {
            $status = 9;
            $message = $e->getMessage() . "\n";
            $message .= $e->getTraceAsString() . "\n";
        }

        echo \Zend\Json\Json::encode(array('status' => $status, 'message' => $message));

        return $this->response;

    }

    /**
     * ジョブ転送を行う(for Simple)
     *
     * @param int $claimPattern 請求パターン
     * @param boolean $isMypage マーページか？
     * @param array $ceSeqs (return)請求エラーリストのSEQリスト
     */
    protected function jobTransferForSimple($claimPattern, $isMypage, &$ceSeqs) {
        $mdlo = new TableOrder($this->app->dbAdapter);
        $mdls = new TableSite($this->app->dbAdapter);
        $mdlcc = new TableClaimControl($this->app->dbAdapter);
        $mdlch = new TableClaimHistory($this->app->dbAdapter);
        $mdlSysP = new TableSystemProperty($this->app->dbAdapter);
        $mdlri = new TableReclaimIndicate($this->app->dbAdapter);
        $mdlce = new TableClaimError($this->app->dbAdapter);

        // ユーザーIDの取得
        $obj = new \models\Table\TableUser($this->app->dbAdapter);
        $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

        // 督促支払期限有効日数を取得
        $validLimitDays2 = $mdlSysP->getValue(TableSystemProperty::DEFAULT_MODULE, 'systeminfo', 'ValidLimitDays2');

        // a. ジョブ転送が必要な対象を抽出
        $sql = <<<EOQ
SELECT CLM.*, ORD.NewSystemFlg, ORD.SiteId
     , F_GetCampaignVal(ORD.EnterpriseId, ORD.SiteId, DATE(NOW()), 'ReClaimFee')  AS ReClaimFee
     , NULL As RiSeq
     , NULL As RiClaimCpId
     , NULL As RiDamageDays
     , NULL As RiDamageBaseDate
     , NULL As RiDamageInterestAmount
     , NULL As RiClaimFee
     , NULL As RiAdditionalClaimFee
     , NULL As RiClaimAmount
  FROM T_ClaimControl CLM
       INNER JOIN T_Order ORD
               ON ORD.OrderSeq = CLM.OrderSeq
       INNER JOIN ( SELECT  t.P_OrderSeq
                           ,MAX(IFNULL(t.LetterClaimStopFlg, 0))   AS LetterClaimStopFlg        -- 最大のストップフラグが1の場合＝ストップしたい注文あり
                      FROM T_Order t
                     WHERE t.Cnl_Status = 0
                       AND t.DataStatus IN (51, 61)
                     GROUP BY t.P_OrderSeq
                  ) po
               ON po.P_OrderSeq = ORD.OrderSeq
       INNER JOIN T_ClaimControl cc
               ON ORD.OrderSeq = cc.OrderSeq
       INNER JOIN T_Customer C ON C.OrderSeq = ORD.OrderSeq
	   INNER JOIN T_EnterpriseCustomer EC ON EC.EntCustSeq = C.EntCustSeq
       INNER JOIN T_ManagementCustomer MC ON MC.ManCustId = EC.ManCustId
       INNER JOIN T_Site s ON s.SiteId = ORD.SiteId
 WHERE 1 = 1
   AND (po.LetterClaimStopFlg = 0 AND IFNULL(MC.RemindStopFlg, 0) = 0)
   AND ( SELECT COUNT(1) FROM T_ClaimHistory WHERE OrderSeq = ORD.OrderSeq AND PrintedFlg = 0 AND ValidFlg = 1 ) = 0
   AND CLM.ReissueClass = 0
EOQ;

        $sql .= "   AND cc.ClaimedBalance > 0 ";        // 2015/12/04 Y.Suzuki Add 暫定対応（残高が0のﾃﾞｰﾀは出力しない）

        // $isMypageによる分岐
        if ($isMypage) {
            $sql .= "   AND cc.MypageReissueClass IN ( 1, 2 ) ";
            $prm = array();
        }
        else {
            $sql .= "    AND cc.LimitDate <= :ClaimPatterCondition1 ";
            $sql .= "    AND cc.MypageReissueClass IN ( 0, 91, 92 )  ";
            $sql .= "    AND ( SELECT MAX(ClaimPattern) FROM T_ClaimHistory tmpch WHERE OrderSeq = ORD.OrderSeq AND PrintedFlg = 1 AND ValidFlg = 1 ) = :ClaimPattern ";

            // 再請求指示分は含めない
            $sql .= "    AND ( SELECT COUNT(1) FROM T_ReclaimIndicate WHERE OrderSeq = ORD.ORDERseq AND IndicatedFlg = 0 AND ValidFlg = 1 ) = 0 ";

            // 督促停止する対象を除く
            $sql .= "    AND s.RemindStopClass = 0 ";

            // union all で再請求指示データ基点のJOB転送対象データも取得する
            $sql .= <<<EOQ
UNION ALL
SELECT CLM.*, ORD.NewSystemFlg, ORD.SiteId
     , NULL  AS ReClaimFee
     , RI.Seq As RiSeq
     , RI.ClaimCpId As RiClaimCpId
     , RI.DamageDays As RiDamageDays
     , RI.DamageBaseDate As RiDamageBaseDate
     , RI.DamageInterestAmount As RiDamageInterestAmount
     , RI.ClaimFee As RiClaimFee
     , RI.AdditionalClaimFee As RiAdditionalClaimFee
     , RI.ClaimAmount As RiClaimAmount
  FROM T_ReclaimIndicate RI
       INNER JOIN T_ClaimControl CLM
               ON CLM.OrderSeq  = RI.OrderSeq
       INNER JOIN T_Order ORD
               ON ORD.OrderSeq = CLM.OrderSeq
       INNER JOIN ( SELECT  t.P_OrderSeq
                           ,MAX(IFNULL(t.LetterClaimStopFlg, 0))   AS LetterClaimStopFlg
                      FROM T_Order t
                     WHERE t.Cnl_Status = 0
                       AND t.DataStatus IN (51, 61)
                     GROUP BY t.P_OrderSeq
                  ) po
               ON po.P_OrderSeq = ORD.OrderSeq
       INNER JOIN T_ClaimControl cc
               ON ORD.OrderSeq = cc.OrderSeq
       INNER JOIN T_Customer C ON C.OrderSeq = ORD.OrderSeq
	   INNER JOIN T_EnterpriseCustomer EC ON EC.EntCustSeq = C.EntCustSeq
       INNER JOIN T_ManagementCustomer MC ON MC.ManCustId = EC.ManCustId
 WHERE 1 = 1
   AND (po.LetterClaimStopFlg = 0 AND IFNULL(MC.RemindStopFlg, 0) = 0)
   AND ( SELECT COUNT(1) FROM T_ClaimHistory WHERE OrderSeq = ORD.OrderSeq AND PrintedFlg = 0 AND ValidFlg = 1 ) = 0
   AND CLM.ReissueClass = 0
   AND cc.ClaimedBalance > 0
   AND RI.ValidFlg = 1
EOQ;

            $sql .= "   AND RI.IndicatedFlg = 0 ";
            $sql .= "   AND RI.ClaimPattern = :RiClaimPattern ";


            $row_mc = $this->app->dbAdapter->query(" SELECT Class1, Class2 FROM M_Code WHERE CodeId = 183 AND KeyCode = :KeyCode "
                )->execute(array(':KeyCode' => $claimPattern))->current();

            $picup_pattern = 0;
            if      ($claimPattern == 9) { $picup_pattern = 8; }  // 再請求７出力チェック
            else if ($claimPattern == 8) { $picup_pattern = 7; }  // 再請求６出力チェック
            else if ($claimPattern == 7) { $picup_pattern = 6; }  // 再請求５出力チェック
            else if ($claimPattern == 6) { $picup_pattern = 4; }  // 再請求４出力チェック
            else if ($claimPattern == 4) { $picup_pattern = 2; }  // 再請求３出力チェック
            else if ($claimPattern == 2) { $picup_pattern = 1; }  // 再請求１出力チェック

            $prm = array(
                ':ClaimPatterCondition1' => date('Y-m-d', strtotime('-' . $row_mc['Class1'] . 'day')),
                ':ClaimPattern' => $picup_pattern,
                ':RiClaimPattern' => $claimPattern,
            );
        }

        $ri = $this->app->dbAdapter->query($sql)->execute($prm);

        $executed_oseqs = array();// Add By Takemasa(NDC) 20151211 重複注文SEQﾌﾞﾛｯｸ(緊急対応)
        foreach ($ri as $row) {
            // Add By Takemasa(NDC) 20151211 Stt 重複注文SEQﾌﾞﾛｯｸ(緊急対応)
            if (in_array($row['OrderSeq'], $executed_oseqs)) {
                $this->app->logger->err('Critical-Warning : ' . $row['OrderSeq']);
                continue;
            }
            else {
                $executed_oseqs[] = $row['OrderSeq'];
            }
            // Add By Takemasa(NDC) 20151211 End 重複注文SEQﾌﾞﾛｯｸ(緊急対応)

            // ----------------------------------------
            // b. ジョブ転送処理を実施
            // ----------------------------------------
            // JNB口座オープン用にロック獲得を試行
            $order = $mdlo->find($row['OrderSeq'])->current();
            $lockItem = $this->getLockItemForJnbAccount($order);

            // 請求履歴が有効かどうか判定
            if ($mdlch->getReservedCount($row['OrderSeq']) > 0) {
                try {
                    if($lockItem) {
                        $lockItem->terminate();
                    }
                } catch (\Exception $err) { ; }
                // ジョブ転送中のデータがいる場合はスキップ
                continue;
            }

            try {
                //トランザクション開始
                $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();

                // 請求管理データ取得(取得済み)

                if (isset($row['RiSeq'])){
                    //再請求指示が基点のデータの場合
                    // 請求担当者
                    $ClaimCpId = $row['RiClaimCpId'];

                    // 遅延日数
                    $damageDays = $row['RiDamageDays'];
                    // 遅延日数算出基準日
                    $strDamageBaseDate = $row['RiDamageBaseDate'];
                    // 遅延損害金
                    $damageInterest = $row['RiDamageInterestAmount'];
                    // 請求手数料
                    $reClaimFee = $row['RiClaimFee'];
                    // 請求追加手数料
                    $AdditionalClaimFee = $row['RiAdditionalClaimFee'];

                    // 請求金額
                    $claimAmount = $row['RiClaimAmount'];

                    // 請求書発行通知メール
                    $MailFlg = 1;


                }else{
                    // 請求担当者
                    $ClaimCpId = $this->app->authManagerAdmin->getUserInfo()->OpId;

                    // 請求金額の再取得
                    // 原則画面と同じになるが、一部キャンセルされた場合を想定
                    $useAmount = $this->app->dbAdapter->query(" SELECT SUM(UseAmount) AS UseAmount FROM T_Order o WHERE o.Cnl_Status = 0 AND o.P_OrderSeq = :OrderSeq "
                    )->execute(array(':OrderSeq' => $row['OrderSeq']))->current()['UseAmount'];

                    // 遅延日数算出
                    $damageDays = BaseGeneralUtils::CalcSpanDaysFromString($row['DamageBaseDate'], date('Y-m-d'));
                    if ($damageDays < 0) {
                        $damageDays = 0;
                    }

                    // 遅延損害金算出
                    // 2015/12/03 Y.Suzuki Mod Stt
                    $damageInterest = 0;
                    //                 if ($row['NewSystemFlg'] == 0) {
                    //                     // 旧システムのデータは遅延損害金は変更なし
                    //                     $damageInterest = $row['DamageInterestAmount'];
                    //                 }
                    //                 else {
                    //                     // 新システムのデータは再計算する
                    //                     $damageInterest = BaseGeneralUtils::CalcInterest($useAmount, $this->app->business['pay']['damagerate'], $damageDays);
                    //                     // 遅延損害金を適用する。ただし、再請求1の場合は遅延損害金を適用しない
                    //                     if($this->_ignoreDamageAmoutnForReclaim1) {
                    //                         if ($claimPattern == 2) {
                    //                             $damageInterest = 0;
                    //                         }
                    //                     }
                    //                 }
                    // 2015/12/03 Y.Suzuki Mod End

                    // 再請求手数料を税込み金額に変換
                    // サイト/キャンペーンから取得した金額のみ変換
                    $row['ReClaimFee'] = $mdlSysP->getIncludeTaxAmount(date('Y-m-d'), $row['ReClaimFee']);

                    // 請求額算出
                    $reClaimFee = ($isMypage) ? $row['MypageReissueReClaimFee'] : ($row['ReClaimFee'] + $row['ClaimFee']);
                    $claimAmount = $useAmount + $reClaimFee + $damageInterest;

                    $strDamageBaseDate = ($damageDays > 0 ? $row['DamageBaseDate'] : null);

                    // 請求追加手数料
                    $AdditionalClaimFee = 0;

                    // 請求書発行通知メール
                    $MailFlg = ($isMypage) ? 1 : 0;

                }

                // サイト情報の取得
                $row_site = $mdls->findSite($row['SiteId'])->current();

                // 期限日までの日数
                $ldays = $this->app->business['pay']['limitdays2'];

                // 遅延起算日日数を期限日までの日数に加算する。
                $ldays += $row_site['KisanbiDelayDays'];

                // 支払期限日算出
                $limitDate = date('Y-m-d', strtotime('+' . $ldays . 'day'));

                // 有効期限日数を算出
                $validLimitDate = date('Y-m-d', strtotime("$validLimitDays2 day"));
                if (strtotime($limitDate) < strtotime($validLimitDate)) {
                    $this->app->dbAdapter->getDriver()->getConnection()->rollback();
                    // 支払期限日数が有効期限未満の場合は請求エラーとする
                    $ceSeqs[] = $mdlce->saveNew(array('OrderSeq' => $row['OrderSeq'], 'ErrorCode' => LogicClaimException::ERR_CODE_LIMIT_DAY, 'ErrorMsg' => $validLimitDays2));
                    try { if($lockItem) { $lockItem->terminate(); } } catch (\Exception $err) { ; }
                    continue;
                }

                // 延滞起算日がNULLの場合は今回の支払期限を次回からの延滞起算日にするためDBへ書き込み
                if (strlen($row['DamageBaseDate']) == 0) {
                    $mdlcc->saveUpdate(array('DamageBaseDate' => $limitDate, 'UpdateId' => $userId), $row['ClaimId']);
                }

                // 請求履歴の作成
                $data = array(
                        'OrderSeq' => $row['OrderSeq'],                     // 注文Seq
                        'ClaimDate' => date('Y-m-d'),                       // 請求日
                        'ClaimCpId' => $ClaimCpId,                          // 請求担当者
                        'ClaimPattern' => $claimPattern,                    // 請求パターン
                        'LimitDate' => $limitDate,                          // 支払期限
                        'DamageDays' => $damageDays,                        // 遅延日数
                        'DamageBaseDate' => $strDamageBaseDate,             // 遅延日数算出基準日
                        'DamageInterestAmount' => $damageInterest,          // 遅延損害金
                        'ClaimFee' => $reClaimFee,                          // 請求手数料
                        'AdditionalClaimFee' => $AdditionalClaimFee,        // 請求追加手数料
                        'PrintedFlg' => 0,                                  // 印刷－処理フラグ
                        'MailFlg' => $MailFlg,                              // 請求書発行通知メール
                        'EnterpriseBillingCode' => null,                    // 同梱ツールアクセスキー
                        'ClaimAmount' => $claimAmount,                      // 請求金額
                        'ClaimId' => $row['ClaimId'],                       // 請求ID
                        'RegistId' => $userId,                              // 登録者
                        'UpdateId' => $userId,                              // 更新者
                );

                try {
                    $hisSeq = $mdlch->saveNew($row['OrderSeq'], $data);
                } catch(LogicClaimException $e) {
                    $this->app->dbAdapter->getDriver()->getConnection()->rollback();
                    // SMBC連携エラーの場合は請求エラーとする
                    $ceSeqs[] = $mdlce->saveNew(array('OrderSeq' => $row['OrderSeq'], 'ErrorCode' => $e->getCode(), 'ErrorMsg' => $e->getMessage()));
                    try { if($lockItem) { $lockItem->terminate(); } } catch (\Exception $err) { ; }
                    continue;
                } catch(\Exception $e) {
                    throw $e;
                }

                $uOrder = array(
                        'ConfirmWaitingFlg'  => '1',
                        'UpdateId'           => $userId,
                );

                $mdlo->saveUpdateWhere($uOrder, array('P_OrderSeq' => $row['OrderSeq']));

                // c. ジョブ転送を行った請求履歴に対して、更新を行う
                $sql  = " UPDATE T_ClaimHistory ";
                $sql .= " SET    PrintedStatus = 1 ";
                $sql .= " ,      UpdateId = :UpdateId ";
                $sql .= " ,      UpdateDate = :UpdateDate ";
                $sql .= " WHERE  OrderSeq = :OrderSeq ";
                $sql .= " AND    PrintedFlg = 0 ";
                $sql .= " AND    ValidFlg = 1 ";

                $this->app->dbAdapter->query($sql)->execute(array(
                        ':OrderSeq' => $row['OrderSeq'],
                        ':UpdateId' => $userId,
                        ':UpdateDate' => date('Y-m-d H:i:s')));

                if (isset($row['RiSeq'])){
                    // 再請求指示データ経由のデータ対象に処理した場合は再請求指示データも更新
                    $mdlri->updateIndicated($row['OrderSeq'],$userId,$hisSeq);
                }

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

            // ロックを獲得していたら開放
            try {
                if($lockItem) {
                    $lockItem->terminate();
                }
            } catch (\Exception $e) { ; }
        }
        return;
    }

    /**
     * (Ajax)ＣＳＶ出力
     * @see 本関数が呼出される場合ビュー側で最低１つ以上のチェックがあることは確定している
     */
    public function simplecsv2Action()
    {
        $params = $this->getParams();

        // ZIPファイル作成
        $zip = new \ZipArchive();

        // 出力時刻
        $formatNowStr = date('YmdHis');

        // 出力ファイル名
        $outFileName= ('ReClaim_' . $formatNowStr . '.zip');

        // TEMP領域作成
        $tmpFilePath = tempnam( sys_get_temp_dir(), 'tmp' );

        // ZIPファイルオープン
        $zip->open( $tmpFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE );

        // 有効なOEM(含むCB)取得
        $ri_oem = $this->app->dbAdapter->query(" SELECT 0 AS OemId UNION ALL SELECT OemId FROM T_Oem WHERE ValidFlg = 1 ")->execute(null);

        // 個別出力の加盟店取得
        $ri_cio = $this->app->dbAdapter->query(" SELECT e.OemId, e.EnterpriseId FROM T_Enterprise e LEFT JOIN T_Oem o ON e.OemId = o.OemId WHERE e.ClaimIndividualOutputFlg = 1 AND (e.OemId = 0 OR o.ValidFlg = 1) AND e.EnterpriseId != (SELECT PropValue FROM T_SystemProperty WHERE Module = '[DEFAULT]' AND Category = 'systeminfo' AND Name = 'NTTFEnterpriseId') ")->execute(null);

        $unlinkList = array();

        // ============ OEM別出力 ============
        foreach ($ri_oem as $row_oem) {

            // (画面．再請求７出力にチェックがある場合)
            if (isset($params['ReClaim7'])) {
                $filename = $this->csvDownloadForSimple($row_oem['OemId'], 9, $formatNowStr, $tmpFilePath);
                if ($filename != '' ) {
                    $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
                    $addFilePath = file_get_contents( $filename );
                    $zip->addFromString( $pathcutfilename, $addFilePath );
                    $unlinkList[] = $filename;
                }
            }
            // (画面．再請求６出力にチェックがある場合)
            if (isset($params['ReClaim6'])) {
                $filename = $this->csvDownloadForSimple($row_oem['OemId'], 8, $formatNowStr, $tmpFilePath);
                if ($filename != '' ) {
                    $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
                    $addFilePath = file_get_contents( $filename );
                    $zip->addFromString( $pathcutfilename, $addFilePath );
                    $unlinkList[] = $filename;
                }
            }
            // (画面．再請求５出力にチェックがある場合)
            if (isset($params['ReClaim5'])) {
                $filename = $this->csvDownloadForSimple($row_oem['OemId'], 7, $formatNowStr, $tmpFilePath);
                if ($filename != '' ) {
                    $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
                    $addFilePath = file_get_contents( $filename );
                    $zip->addFromString( $pathcutfilename, $addFilePath );
                    $unlinkList[] = $filename;
                }
            }
            // (画面．再請求４出力にチェックがある場合)
            if (isset($params['ReClaim4'])) {
                $filename = $this->csvDownloadForSimple($row_oem['OemId'], 6, $formatNowStr, $tmpFilePath);
                if ($filename != '' ) {
                    $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
                    $addFilePath = file_get_contents( $filename );
                    $zip->addFromString( $pathcutfilename, $addFilePath );
                    $unlinkList[] = $filename;
                }
            }
            // (画面．再請求３出力にチェックがある場合)
            if (isset($params['ReClaim3'])) {
                $filename = $this->csvDownloadForSimple($row_oem['OemId'], 4, $formatNowStr, $tmpFilePath);
                if ($filename != '' ) {
                    $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
                    $addFilePath = file_get_contents( $filename );
                    $zip->addFromString( $pathcutfilename, $addFilePath );
                    $unlinkList[] = $filename;
                }
            }
            // (画面．再請求１出力にチェックがある場合)
            if (isset($params['ReClaim1'])) {
                $filename = $this->csvDownloadForSimple($row_oem['OemId'], 2, $formatNowStr, $tmpFilePath);
                if ($filename != '' ) {
                    $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
                    $addFilePath = file_get_contents( $filename );
                    $zip->addFromString( $pathcutfilename, $addFilePath );
                    $unlinkList[] = $filename;
                }
            }
        }

        // ============== 個別出力 ==============
        foreach ($ri_cio as $row_cio) {

            // (画面．再請求７出力にチェックがある場合)
            if (isset($params['ReClaim7'])) {
                $filename = $this->csvDownloadForCio($row_cio['OemId'], $row_cio['EnterpriseId'], 9, $formatNowStr, $tmpFilePath);
                if ($filename != '' ) {
                    $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
                    $addFilePath = file_get_contents( $filename );
                    $zip->addFromString( $pathcutfilename, $addFilePath );
                    $unlinkList[] = $filename;
                }
            }
            // (画面．再請求６出力にチェックがある場合)
            if (isset($params['ReClaim6'])) {
                $filename = $this->csvDownloadForCio($row_cio['OemId'], $row_cio['EnterpriseId'], 8, $formatNowStr, $tmpFilePath);
                if ($filename != '' ) {
                    $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
                    $addFilePath = file_get_contents( $filename );
                    $zip->addFromString( $pathcutfilename, $addFilePath );
                    $unlinkList[] = $filename;
                }
            }
            // (画面．再請求５出力にチェックがある場合)
            if (isset($params['ReClaim5'])) {
                $filename = $this->csvDownloadForCio($row_cio['OemId'], $row_cio['EnterpriseId'], 7, $formatNowStr, $tmpFilePath);
                if ($filename != '' ) {
                    $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
                    $addFilePath = file_get_contents( $filename );
                    $zip->addFromString( $pathcutfilename, $addFilePath );
                    $unlinkList[] = $filename;
                }
            }
            // (画面．再請求４出力にチェックがある場合)
            if (isset($params['ReClaim4'])) {
                $filename = $this->csvDownloadForCio($row_cio['OemId'], $row_cio['EnterpriseId'], 6, $formatNowStr, $tmpFilePath);
                if ($filename != '' ) {
                    $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
                    $addFilePath = file_get_contents( $filename );
                    $zip->addFromString( $pathcutfilename, $addFilePath );
                    $unlinkList[] = $filename;
                }
            }
            // (画面．再請求３出力にチェックがある場合)
            if (isset($params['ReClaim3'])) {
                $filename = $this->csvDownloadForCio($row_cio['OemId'], $row_cio['EnterpriseId'], 4, $formatNowStr, $tmpFilePath);
                if ($filename != '' ) {
                    $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
                    $addFilePath = file_get_contents( $filename );
                    $zip->addFromString( $pathcutfilename, $addFilePath );
                    $unlinkList[] = $filename;
                }
            }
            // (画面．再請求１出力にチェックがある場合)
            if (isset($params['ReClaim1'])) {
                $filename = $this->csvDownloadForCio($row_cio['OemId'], $row_cio['EnterpriseId'], 2, $formatNowStr, $tmpFilePath);
                if ($filename != '' ) {
                    $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
                    $addFilePath = file_get_contents( $filename );
                    $zip->addFromString( $pathcutfilename, $addFilePath );
                    $unlinkList[] = $filename;
                }
            }
        }

        // ZIPファイルクローズ
        $zip->close();

        // ヘッダ
        header( 'Content-Type: application/octet-stream; name="' . $outFileName . '"' );
        header( 'Content-Disposition: attachment; filename="' . $outFileName . '"' );
        header( 'Content-Length: ' . filesize( $tmpFilePath ) );

        // 出力
        echo readfile( $tmpFilePath );

        // TEMP領域削除
        // count関数対策
        $unlinkListLen = 0;
        if(!empty($unlinkList)) {
            $unlinkListLen = count($unlinkList);
        }

        for ($i=0; $i<$unlinkListLen; $i++) {
            unlink( $unlinkList[$i] );
        }
        unlink( $tmpFilePath );
        die();
    }

    /**
     * CSV出力を行う(for Simple)
     *
     * @param int $oemId OemID
     * @param int $claimPattern 請求パターン
     * @param string $formatNowStr 書式化年月日時分秒
     * @param string $tmpFilePath TEMP領域
     * @return ファイル名 ※出力が行われなかった場合は''を戻す
     */
    protected function csvDownloadForSimple($oemId, $claimPattern, $formatNowStr, $tmpFilePath) {

        // ユーザーIDの取得
        $obj = new \models\Table\TableUser($this->app->dbAdapter);
        $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);


        $mdlsp = new TableSystemProperty($this->app->dbAdapter);
        $ntteid = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'NTTFEnterpriseId');

        //---------------------------------------
        // 出力対象の存在チェック(OemID＋請求ﾊﾟﾀｰﾝ)
        // Mod By Takemasa(NDC) 20151211 Stt 重複注文SEQﾌﾞﾛｯｸ(緊急対応)
        //$sql  = " SELECT ch.OrderSeq ";
        $sql  = " SELECT DISTINCT ch.OrderSeq ";
        // Mod By Takemasa(NDC) 20151211 Stt 重複注文SEQﾌﾞﾛｯｸ(緊急対応)
        $sql .= " FROM   T_ClaimHistory ch ";
        $sql .= "        INNER JOIN T_Order o ON (o.OrderSeq = ch.OrderSeq) ";
        $sql .= "        INNER JOIN T_Customer cus ON (cus.OrderSeq = ch.OrderSeq) ";
        $sql .= "        INNER JOIN T_Enterprise e ON (e.EnterpriseId = o.EnterpriseId) ";
        $sql .= " WHERE  1 = 1 ";
        $sql .= " AND    ch.PrintedFlg = 0 ";
        $sql .= " AND    ch.PrintedStatus IN (1, 2) ";
        $sql .= " AND    ch.ValidFlg = 1 ";
        $sql .= " AND    (o.Cnl_Status IS NULL OR o.Cnl_Status = 0) ";
        $sql .= " AND    (o.LetterClaimStopFlg IS NULL OR o.LetterClaimStopFlg = 0) ";
        $sql .= " AND    IFNULL(o.OemId, 0) = :OemId ";
        $sql .= " AND    ch.ClaimPattern = :ClaimPattern ";
        $sql .= " AND    ch.ClaimAmount > 0 ";
        // 個別出力しない加盟店のみ
        $sql .= " AND    (e.ClaimIndividualOutputFlg = 0 ";
        $sql .= " OR      e.EnterpriseId = " . $ntteid . " ) ";
        $sql .= " AND    ch.PrintedFlg = 0 AND ch.ValidFlg = 1 ";
        $sql .= " ORDER BY cus.PostalCode, o.OrderId ";

        $ri = $this->app->dbAdapter->query($sql)->execute(array(':OemId' => $oemId, ':ClaimPattern' => $claimPattern));
        if (!($ri->count() > 0)) {
            return ''; // 出力対象件数が0の場合は以降処理不要
        }

        //---------------------------------------
        // 出力ファイル名生成
        $keycode = ((int)$oemId * 10 + (int)$claimPattern);
        // (プレフィックス1)
        $sql  = " SELECT Class1 ";
        $sql .= " FROM   M_Code ";
        $sql .= " WHERE  CodeId = 181 ";
        $sql .= " AND    KeyCode = (SELECT Class1 FROM M_Code WHERE CodeId = 182 AND KeyCode = :KeyCode) ";
        $prefix1 = $this->app->dbAdapter->query($sql)->execute(array(':KeyCode' => $keycode))->current()['Class1'];
        // (プレフィックス2)
        $sql  = " SELECT Class2 FROM M_Code WHERE CodeId = 182 AND KeyCode = :KeyCode ";
        $prefix2 = $this->app->dbAdapter->query($sql)->execute(array(':KeyCode' => $keycode))->current()['Class2'];
        // (ファイル名生成)
        $fileName = ($prefix1 . '_' . $prefix2 . '_' . $formatNowStr . '.csv');

        //---------------------------------------
        // Ｅストア考慮(現行互換ＩＦ) ※再請求１出力時限定
        $estoreFlg = false;
        $estoreItemsCnt = 12;
        if ($claimPattern == 2 && $oemId > 0) {
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
            $sql .= ' ,      cc.MypageReissueClass ';
            $sql .= ' ,      e.PrintEntOrderIdOnClaimFlg ';
            $sql .= ' ,      cc.ReceiptAmountTotal ';
            $sql .= ' FROM   T_Order o INNER JOIN ';
            $sql .= '        T_Customer c ON ( o.OrderSeq = c.OrderSeq ) INNER JOIN ';
            $sql .= '        T_Enterprise e ON ( o.EnterpriseId = e.EnterpriseId ) INNER JOIN ';
            $sql .= '        T_Site s ON ( o.SiteId = s.SiteId ) INNER JOIN ';
            $sql .= '        T_ClaimControl cc ON o.OrderSeq = cc.OrderSeq LEFT OUTER JOIN ';
            $sql .= '        T_ClaimHistory ch ON ( o.OrderSeq = ch.OrderSeq AND ch.PrintedFlg = 0 AND ch.ValidFlg = 1 ) LEFT OUTER JOIN ';
            $sql .= '        T_OemClaimAccountInfo ca ON( ch.Seq = ca.ClaimHistorySeq AND ca.Status = 1 ) ';
            $sql .= ' WHERE  o.OrderSeq = :OrderSeq ';

            $sql .= " AND    cc.ClaimedBalance > 0 ";       // 2015/12/04 Y.Suzuki Add 暫定対応（残高が0のﾃﾞｰﾀは出力しない）

            $data = $this->app->dbAdapter->query( $sql )->execute( $prm )->current();

            // 請求金額
            $data['ClaimAmount'] = nvl( $data['ClaimAmount'], 0 ) - nvl( $data['ReceiptAmountTotal'], 0 );

            // 請求金額が0円以下の場合は出力を行わない(特に、T_ClaimHistoryが取得出来ない場合を想定)
            if ( $data['ClaimAmount'] <= 0 ) {
                continue;
            }

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

            // 注文商品＋配送先
            $sql  = ' SELECT itm.ItemNameKj ';
            $sql .= ' ,      itm.ItemNum ';
            $sql .= ' ,      itm.UnitPrice ';
            $sql .= ' ,      itm.SumMoney ';
            $sql .= ' ,      e.DispDecimalPoint ';  /* 表示用小数点桁数 */
            $sql .= ' ,      dd.PostalCode ';       /* 配送先郵便番号 */
            $sql .= ' ,      dd.UnitingAddress ';   /* 配送先住所 */
            $sql .= ' ,      dd.DestNameKj ';       /* 配送先氏名 */
            $sql .= ' FROM   T_Order o INNER JOIN ';
            $sql .= '        T_OrderItems itm ON ( o.OrderSeq = itm.OrderSeq ) ';
            $sql .= '        INNER JOIN T_Enterprise e ON ( e.EnterpriseId = o.EnterpriseId ) ';
            $sql .= '        INNER JOIN T_DeliveryDestination dd ON ( itm.DeliDestId = dd.DeliDestId ) ';
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

            // 入金情報
            if ( nvl( $data['ReceiptAmountTotal'], 0 ) > 0 ) {
                // 入金されている場合、第15明細に情報を設定する
                $data['ItemNameKj_15'] = '入金済額';
                $data['ItemNum_15'] = '1';
                $data['UnitPrice_15'] = $data['ReceiptAmountTotal'] * -1;
                $data['SumMoney_15'] = $data['ReceiptAmountTotal'] * -1;
            }

            // 小計
            $data['TotalItemPrice'] = 0;
            foreach ($items as $row) {
                $data['TotalItemPrice'] += $row['SumMoney'];
            }

            // マイページ再発行で配送先に請求した場合
            if ($data['MypageReissueClass'] == 2) {
                // 請求先情報を配送先情報（第一明細を使用）に変更
                $data['PostalCode'] = $items[0]['PostalCode'];
                $data['UnitingAddress'] = $items[0]['UnitingAddress'];
                $data['NameKj'] = $items[0]['DestNameKj'];
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
            $sql  = ' SELECT COUNT(*) AS ReIssueCount ';
            $sql .= ' FROM   T_ClaimHistory ';
            $sql .= ' WHERE  PrintedFlg <> 0 ';
            $sql .= ' AND    ValidFlg = 1 ';
            $sql .= ' AND    OrderSeq = :OrderSeq ';
            $data = array_merge( $data, $this->app->dbAdapter->query( $sql )->execute( $prm )->current() );
            $data['ReIssueCount'] += 1;

            // マイページログインパスワード
            $row_mypageorder = $this->app->dbAdapter->query(" SELECT Token FROM T_MypageOrder WHERE OrderSeq = :OrderSeq ORDER BY Seq DESC LIMIT 1 "
            )->execute(array(':OrderSeq' => $ri_row['OrderSeq']))->current();
            $data['MypageToken'] = ($row_mypageorder) ? $row_mypageorder['Token'] : '';

            // 商品合計数
            $data['ItemsCount'] = 0;

            // count関数対策
            if(!empty($items)) {
                $data['ItemsCount'] = count($items);
            }

            // Eストアの場合のみ
            if ($estoreFlg) {
                if ($data['ItemsCount'] > $estoreItemsCnt) {
                    // 商品明細数が12を超えている場合、12明細目の内容を変更
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
                if ( nvl( $data['ReceiptAmountTotal'], 0 ) > 0 ) {
                    // 入金されている場合、第15明細に情報を設定する
                    $data['ItemNameKj_13'] = '入金済額';
                    $data['ItemNum_13'] = '1';
                    $data['UnitPrice_13'] = $data['ReceiptAmountTotal'] * -1;
                    $data['SumMoney_13'] = $data['ReceiptAmountTotal'] * -1;
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

            $datas[] = $data;

            // 出力した請求履歴データに対する更新処理
            $sql  = " UPDATE T_ClaimHistory ";
            $sql .= " SET    PrintedStatus = 2 ";
            $sql .= " ,      UpdateId = :UpdateId ";
            $sql .= " ,      UpdateDate = :UpdateDate ";
            $sql .= " WHERE  OrderSeq = :OrderSeq ";
            $sql .= " AND    PrintedFlg = 0 ";
            $sql .= " AND    PrintedStatus = 1 ";
            $sql .= " AND    ValidFlg = 1 ";

            $this->app->dbAdapter->query($sql)->execute(array(
                    ':UpdateId' => $userId,
                    ':UpdateDate' => date('Y-m-d H:i:s', strtotime($formatNowStr)),
                    ':OrderSeq' => $ri_row['OrderSeq']
            ));
        }

        //---------------------------------------
        $templateId = 'CKI04043_1'; // 請求書発行（再請求）
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
     * CSV出力を行う(個別出力加盟店単位)
     *
     * @param int $oemId OemID
     * @param int $enterpriseId EnterpriseID
     * @param int $claimPattern 請求パターン
     * @param string $formatNowStr 書式化年月日時分秒
     * @param string $tmpFilePath TEMP領域
     * @return ファイル名 ※出力が行われなかった場合は''を戻す
     */
    protected function csvDownloadForCio($oemId, $enterpriseId, $claimPattern, $formatNowStr, $tmpFilePath) {

        // ユーザーIDの取得
        $obj = new \models\Table\TableUser($this->app->dbAdapter);
        $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

        //---------------------------------------
        // 出力対象の存在チェック(OemID＋請求ﾊﾟﾀｰﾝ)
        // Mod By Takemasa(NDC) 20151211 Stt 重複注文SEQﾌﾞﾛｯｸ(緊急対応)
        //$sql  = " SELECT ch.OrderSeq ";
        $sql  = " SELECT DISTINCT ch.OrderSeq ";
        // Mod By Takemasa(NDC) 20151211 Stt 重複注文SEQﾌﾞﾛｯｸ(緊急対応)
        $sql .= " FROM   T_ClaimHistory ch ";
        $sql .= "        INNER JOIN T_Order o ON (o.OrderSeq = ch.OrderSeq) ";
        $sql .= "        INNER JOIN T_Customer cus ON (cus.OrderSeq = ch.OrderSeq) ";
        $sql .= " WHERE  1 = 1 ";
        $sql .= " AND    ch.PrintedFlg = 0 ";
        $sql .= " AND    ch.PrintedStatus IN (1, 2) ";
        $sql .= " AND    ch.ValidFlg = 1 ";
        $sql .= " AND    (o.Cnl_Status IS NULL OR o.Cnl_Status = 0) ";
        $sql .= " AND    (o.LetterClaimStopFlg IS NULL OR o.LetterClaimStopFlg = 0) ";
        $sql .= " AND    IFNULL(o.OemId, 0) = :OemId ";
        $sql .= " AND    ch.ClaimPattern = :ClaimPattern ";
        $sql .= " AND    ch.ClaimAmount > 0 ";
        $sql .= " AND    o.EnterpriseId = " . $enterpriseId . " ";
        $sql .= " ORDER BY cus.PostalCode, o.OrderId ";

        $ri = $this->app->dbAdapter->query($sql)->execute(array(':OemId' => $oemId, ':ClaimPattern' => $claimPattern));
        if (!($ri->count() > 0)) {
            return ''; // 出力対象件数が0の場合は以降処理不要
        }

        //---------------------------------------
        // 出力ファイル名生成
        $keycode = ((int)$oemId * 10 + (int)$claimPattern);
        // (プレフィックス1)
        $sql  = " SELECT Class1 ";
        $sql .= " FROM   M_Code ";
        $sql .= " WHERE  CodeId = 181 ";
        $sql .= " AND    KeyCode = (SELECT Class1 FROM M_Code WHERE CodeId = 182 AND KeyCode = :KeyCode) ";
        $prefix1 = $this->app->dbAdapter->query($sql)->execute(array(':KeyCode' => $keycode))->current()['Class1'];
        // (プレフィックス2)
        $sql  = " SELECT Class2 FROM M_Code WHERE CodeId = 182 AND KeyCode = :KeyCode ";
        $prefix2 = $this->app->dbAdapter->query($sql)->execute(array(':KeyCode' => $keycode))->current()['Class2'];
        // (ファイル名生成)
        $fileName = ($prefix1 . '_' . $prefix2 . '_' . $enterpriseId . '_' . $formatNowStr . '.csv');

        //---------------------------------------
        // Ｅストア考慮(現行互換ＩＦ) ※再請求１出力時限定
        $estoreFlg = false;
        $estoreItemsCnt = 12;
        if ($claimPattern == 2 && $oemId > 0) {
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
            $sql .= ' ,      cc.MypageReissueClass ';
            $sql .= ' ,      e.PrintEntOrderIdOnClaimFlg ';
            $sql .= ' ,      cc.ReceiptAmountTotal ';
            $sql .= ' ,      o.Ent_Note ';
            $sql .= ' FROM   T_Order o INNER JOIN ';
            $sql .= '        T_Customer c ON ( o.OrderSeq = c.OrderSeq ) INNER JOIN ';
            $sql .= '        T_Enterprise e ON ( o.EnterpriseId = e.EnterpriseId ) INNER JOIN ';
            $sql .= '        T_Site s ON ( o.SiteId = s.SiteId ) INNER JOIN ';
            $sql .= '        T_ClaimControl cc ON o.OrderSeq = cc.OrderSeq LEFT OUTER JOIN ';
            $sql .= '        T_ClaimHistory ch ON ( o.OrderSeq = ch.OrderSeq AND ch.PrintedFlg = 0 AND ch.ValidFlg = 1 ) LEFT OUTER JOIN ';
            $sql .= '        T_OemClaimAccountInfo ca ON( ch.Seq = ca.ClaimHistorySeq AND ca.Status = 1 ) ';
            $sql .= ' WHERE  o.OrderSeq = :OrderSeq ';

            $sql .= " AND    cc.ClaimedBalance > 0 ";       // 2015/12/04 Y.Suzuki Add 暫定対応（残高が0のﾃﾞｰﾀは出力しない）

            $data = $this->app->dbAdapter->query( $sql )->execute( $prm )->current();

            // 請求金額
            $data['ClaimAmount'] = nvl( $data['ClaimAmount'], 0 ) - nvl( $data['ReceiptAmountTotal'], 0 );

            // 請求金額が0円以下の場合は出力を行わない(特に、T_ClaimHistoryが取得出来ない場合を想定)
            if ( $data['ClaimAmount'] <= 0 ) {
                continue;
            }

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

            // 注文商品＋配送先
            $sql  = ' SELECT itm.ItemNameKj ';
            $sql .= ' ,      itm.ItemNum ';
            $sql .= ' ,      itm.UnitPrice ';
            $sql .= ' ,      itm.SumMoney ';
            $sql .= ' ,      e.DispDecimalPoint ';  /* 表示用小数点桁数 */
            $sql .= ' ,      dd.PostalCode ';       /* 配送先郵便番号 */
            $sql .= ' ,      dd.UnitingAddress ';   /* 配送先住所 */
            $sql .= ' ,      dd.DestNameKj ';       /* 配送先氏名 */
            $sql .= ' FROM   T_Order o INNER JOIN ';
            $sql .= '        T_OrderItems itm ON ( o.OrderSeq = itm.OrderSeq ) ';
            $sql .= '        INNER JOIN T_Enterprise e ON ( e.EnterpriseId = o.EnterpriseId ) ';
            $sql .= '        INNER JOIN T_DeliveryDestination dd ON ( itm.DeliDestId = dd.DeliDestId ) ';
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

            // 入金情報
            if ( nvl( $data['ReceiptAmountTotal'], 0 ) > 0 ) {
                // 入金されている場合、第15明細に情報を設定する
                $data['ItemNameKj_15'] = '入金済額';
                $data['ItemNum_15'] = '1';
                $data['UnitPrice_15'] = $data['ReceiptAmountTotal'] * -1;
                $data['SumMoney_15'] = $data['ReceiptAmountTotal'] * -1;
            }

            // 小計
            $data['TotalItemPrice'] = 0;
            foreach ($items as $row) {
                $data['TotalItemPrice'] += $row['SumMoney'];
            }

            // マイページ再発行で配送先に請求した場合
            if ($data['MypageReissueClass'] == 2) {
                // 請求先情報を配送先情報（第一明細を使用）に変更
                $data['PostalCode'] = $items[0]['PostalCode'];
                $data['UnitingAddress'] = $items[0]['UnitingAddress'];
                $data['NameKj'] = $items[0]['DestNameKj'];
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
            $sql  = ' SELECT COUNT(*) AS ReIssueCount ';
            $sql .= ' FROM   T_ClaimHistory ';
            $sql .= ' WHERE  PrintedFlg <> 0 ';
            $sql .= ' AND    ValidFlg = 1 ';
            $sql .= ' AND    OrderSeq = :OrderSeq ';
            $data = array_merge( $data, $this->app->dbAdapter->query( $sql )->execute( $prm )->current() );
            $data['ReIssueCount'] += 1;

            // マイページログインパスワード
            $row_mypageorder = $this->app->dbAdapter->query(" SELECT Token FROM T_MypageOrder WHERE OrderSeq = :OrderSeq ORDER BY Seq DESC LIMIT 1 "
            )->execute(array(':OrderSeq' => $ri_row['OrderSeq']))->current();
            $data['MypageToken'] = ($row_mypageorder) ? $row_mypageorder['Token'] : '';

            // 商品合計数
            $data['ItemsCount'] = 0;

            // count関数対策
            if(!empty($items)) {
                $data['ItemsCount'] = count($items);
            }

            // Eストアの場合のみ
            if ($estoreFlg) {
                if ($data['ItemsCount'] > $estoreItemsCnt) {
                    // 商品明細数が12を超えている場合、12明細目の内容を変更
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
                if ( nvl( $data['ReceiptAmountTotal'], 0 ) > 0 ) {
                    // 入金されている場合、第15明細に情報を設定する
                    $data['ItemNameKj_13'] = '入金済額';
                    $data['ItemNum_13'] = '1';
                    $data['UnitPrice_13'] = $data['ReceiptAmountTotal'] * -1;
                    $data['SumMoney_13'] = $data['ReceiptAmountTotal'] * -1;
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
            $sql .= " AND    PrintedStatus = 1 ";
            $sql .= " AND    ValidFlg = 1 ";

            $this->app->dbAdapter->query($sql)->execute(array(
                    ':UpdateId' => $userId,
                    ':UpdateDate' => date('Y-m-d H:i:s', strtotime($formatNowStr)),
                    ':OrderSeq' => $ri_row['OrderSeq']
            ));
        }

        //---------------------------------------
        $templateId = 'CKI04046_1'; // 請求書発行（再請求）
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
     * (Ajax)印刷済みに更新
     */
    public function simpleupAction()
    {
        $params = $this->getParams();

        // ユーザーIDの取得
        $obj = new \models\Table\TableUser($this->app->dbAdapter);
        $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

        try {
            //トランザクション開始
            $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();

// Del By Takemasa(NDC) 20151202 Stt 「印刷指示」中のデータがいても処理可能とする(ｴﾗｰとしない)
//             // １．チェック処理(CSV印刷指示待ちのデータが存在するか確認)
//             $sql  = " SELECT COUNT(1) AS cnt ";
//             $sql .= " FROM   T_ClaimHistory ch ";
//             $sql .= "        INNER JOIN T_Order o ON (o.OrderSeq = ch.OrderSeq) ";
//             $sql .= " WHERE  1 = 1 ";
//             $sql .= " AND    ch.ClaimPattern >= 2 ";
//             $sql .= " AND    ch.PrintedStatus = 1 ";
//             $sql .= " AND    ch.PrintedFlg = 0 ";
//             $sql .= " AND    ch.ValidFlg = 1 ";
//             $sql .= " AND    (o.Cnl_Status IS NULL OR o.Cnl_Status = 0) ";
//             $sql .= " AND    (o.LetterClaimStopFlg IS NULL OR o.LetterClaimStopFlg = 0) ";
//
//             $count = (int)$this->app->dbAdapter->query($sql)->execute(null)->current()['cnt'];
//             if ($count > 0) {
//                 throw new \Exception('エラー：印刷指示後、未出力のCSVデータが存在します。');
//             }
// Del By Takemasa(NDC) 20151202 End 「印刷指示」中のデータがいても処理可能とする(ｴﾗｰとしない)

            // ２．対象の抽出、１件ずつループ
            $sql  = " SELECT o.OutOfAmends, ch.* ";
            $sql .= " FROM   T_ClaimHistory ch ";
            $sql .= "        INNER JOIN T_Order o ON (o.OrderSeq = ch.OrderSeq) ";
            $sql .= " WHERE  1 = 1 ";
            $sql .= " AND    ch.ClaimPattern >= 2 ";
            $sql .= " AND    ch.PrintedStatus IN (2, 3) ";
            $sql .= " AND    ch.PrintedFlg = 0 ";
            $sql .= " AND    ch.ValidFlg = 1 ";
            $sql .= " AND    (o.Cnl_Status IS NULL OR o.Cnl_Status = 0) ";
            $sql .= " AND    (o.LetterClaimStopFlg IS NULL OR o.LetterClaimStopFlg = 0) ";

            $ri = $this->app->dbAdapter->query($sql)->execute(null);

            // 請求関連処理SQL
            $stm = $this->app->dbAdapter->query($this->getBaseP_ClaimControl());

            // SQL実行結果取得用のSQL
            $getretvalsql = " SELECT @po_ret_sts AS po_ret_sts, @po_ret_errcd AS po_ret_errcd, @po_ret_sqlcd AS po_ret_sqlcd, @po_ret_msg AS po_ret_msg ";

            $history = new CoralHistoryOrder($this->app->dbAdapter);
            $mdlo = new TableOrder($this->app->dbAdapter);

            foreach ($ri as $row) {

                // 2-1. チェック処理
                // (a. 有効な注文か否かチェックする)
                $sql  = " SELECT COUNT(*) AS cnt ";
                $sql .= " FROM   T_Order o ";
                $sql .= " WHERE  EXISTS(SELECT * FROM T_Order t WHERE t.P_OrderSeq = o.OrderSeq AND t.Cnl_Status = 0) ";
                $sql .= " AND    o.OrderSeq = :OrderSeq ";
                if ((int)$this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $row['OrderSeq']))->current()['cnt'] == 0) {
                    continue;// ０件の場合、次の注文へ
                }

                // (b. 請求履歴が有効か否かチェックする（ジョブ転送中でないことを確認する）)
                $sql = " SELECT COUNT(1) AS cnt FROM T_ClaimHistory WHERE PrintedFlg = 0 AND OrderSeq = :OrderSeq AND ValidFlg = 1 ";
                if ((int)$this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $row['OrderSeq']))->current()['cnt'] <= 0) {
                    continue;// ０件以下の場合、次の注文へ
                }

                // 2-2. 請求管理データの更新(P_ClaimControl CALL)
                $stm->execute(array(
                    ':pi_history_seq'   => $row['Seq'],
                    ':pi_button_flg'    => 1,
                    ':pi_user_id'       => $userId,
                ));

                // 2-3. 紙請求ストップフラグの判定
                $letterClaimStopFlg = 0;
                if (($row['OutOfAmends'] == 0 && $row['ClaimPattern'] >= 9) ||
                    ($row['OutOfAmends'] == 1 && ($row['ClaimPattern'] >= 3 && $row['ClaimPattern'] < 9))) {
                    $letterClaimStopFlg = 1;
                }

                // 2-4. 注文データの更新
                $sql  = " UPDATE T_Order ";
                $sql .= " SET    LetterClaimStopFlg = :LetterClaimStopFlg ";
                $sql .= " ,      MailClaimStopFlg = 0 ";
                $sql .= " ,      UpdateId = :UpdateId ";
                $sql .= " ,      UpdateDate = :UpdateDate ";
                $sql .= " WHERE  P_OrderSeq = :OrderSeq ";

                $prm = array(
                    ':LetterClaimStopFlg'   => $letterClaimStopFlg,
                    ':UpdateId'             => $userId,
                    ':UpdateDate'           => date('Y-m-d H:i:s'),
                    ':OrderSeq'             => $row['OrderSeq'],
                );
                $this->app->dbAdapter->query($sql)->execute($prm);

                // 2-5. 親注文、子注文両方に対して、OEM先備考欄に請求書発行情報を印字する
                // 2-6. 親注文、子注文両方に対して、注文履歴登録SPをコールする
                // 履歴登録用理由コードを設定
                if ($row['ClaimPattern'] == 2) {
                    $reasonCode = 43;       // 再請求書発行（再１）
                } else if ($row['ClaimPattern'] == 4) {
                    $reasonCode = 44;       // 再請求書発行（再３）
                } else if ($row['ClaimPattern'] == 6) {
                    $reasonCode = 45;       // 再請求書発行（再４）
                } else if ($row['ClaimPattern'] == 7) {
                    $reasonCode = 46;       // 再請求書発行（再５）
                } else if ($row['ClaimPattern'] == 8) {
                    $reasonCode = 47;       // 再請求書発行（再６）
                } else if ($row['ClaimPattern'] == 9) {
                    $reasonCode = 48;       // 再請求書発行（再７）
                }

                // 注文履歴登録用に親注文Seqから子注文Seqを再取得する。
                $ri2 = $this->app->dbAdapter->query(" SELECT OrderSeq FROM T_Order WHERE P_OrderSeq = :P_OrderSeq AND Cnl_Status = 0 "
                    )->execute(array(':P_OrderSeq' => $row['OrderSeq']));

                // 注文履歴へ登録(取得できた件数分ループする)
                foreach ($ri2 as $row2) {
                    // 備考に保存
                    $mdlo->appendPrintedInfoToOemNote($row2["OrderSeq"]);
                    // 注文履歴登録
                    $history->InsOrderHistory($row2["OrderSeq"], $reasonCode, $userId);
                }

                // 出力した請求履歴データに対する更新処理
                $sql  = " UPDATE T_ClaimHistory ";
                $sql .= " SET    PrintedStatus = 9 ";
                $sql .= " ,      UpdateId = :UpdateId ";
                $sql .= " ,      UpdateDate = :UpdateDate ";
                $sql .= " WHERE  Seq = :Seq ";

                $this->app->dbAdapter->query($sql)->execute(array(
                        ':UpdateId' => $userId,
                        ':UpdateDate' => date('Y-m-d H:i:s'),
                        ':Seq' => $row['Seq']
                ));
            }

            $this->app->dbAdapter->getDriver()->getConnection()->commit();

            $msg = '1';
        }
        catch(\Exception $e) {
            $this->app->dbAdapter->getDriver()->getConnection()->rollback();
            $msg = $e->getMessage();
        }

        echo \Zend\Json\Json::encode(array('status' => $msg));
        return $this->response;
    }

    //-------------------------------------------------------------------------
    // 【個別出力】個別指定再請求書発行待ちリスト(個別指定CSV一括出力)
    //-------------------------------------------------------------------------
    /**
     * 【個別出力】再請求対象のリストを表示する。
     */
    public function list2Action()
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
        $oem = isset($params['oem']) ? $params['oem'] : -1;// OEM
        $claimpattern = isset($params['claimpattern']) ? $params['claimpattern'] : 2;// 請求パターン(初期値は再請求１)
        $entid = isset($params['entid']) ? $params['entid'] : '';// 加盟店ID
        $entnm = isset($params['entnm']) ? $params['entnm'] : '';// 加盟店名
        $odrid = isset($params['odrid']) ? $params['odrid'] : '';// 注文ID
        $siteid = isset($params['siteid']) ? $params['siteid'] : '';// サイトID

        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);

        // 画面の検索条件に従った抽出SQL取得
        $sql = $this->getList2Query($billIssueState, $oem, $entid, $entnm, $odrid, $siteid, $claimpattern, $prm);
        $ri = $this->app->dbAdapter->query($sql)->execute($prm);
        $datas = ResultInterfaceToArray($ri);

        // count関数対策
        $datasCount = 0;
        if(!empty($datas)) {
            $datasCount = count($datas);
        }

        // [paging] ページャ初期化
        $pager = new \Coral\Coral\CoralPager($datasCount, $ipp);
        // [paging] 指定ページを補正
        if( $current_page > $pager->getTotalPage() ) $current_page = $pager->getTotalPage();
        // [paging] 対象リストをページング情報に基づいて対象リストをスライス
        if($datasCount > 0) $datas = array_slice( $datas, $pager->getStartIndex( $current_page ), $ipp );
        // [paging] ページングナビゲーション情報
        $page_links = array( 'base' => "reclaim/list2/billIssueState/" . f_e($billIssueState) . '/claimpattern/' . f_e($claimpattern)
                . '/oem/' . f_e($oem) . '/entid/' . $entid . '/entnm/' . $entnm . '/odrid/' . $odrid . '/siteid/' . $siteid . '/page' );
        $page_links['prev'] = $page_links['base'] . '/' . ( $current_page - 1 );
        $page_links['next'] = $page_links['base'] . '/' . ( $current_page + 1 );
        // [paging] ページング関連の情報をビューへアサイン
        $this->view->assign( 'current_page', $current_page );
        $this->view->assign( 'pager', $pager );
        $this->view->assign( 'page_links', $page_links );

        $this->view->assign("list", $datas);
        $this->view->assign("cnt", $ri->count());
        $this->view->assign("billIssueStateTag",BaseHtmlUtils::SelectTag('billIssueState',array(-1 => '----------', 0 => '未発行', 1 => '指示済み', 2 => '確定待ち'),$billIssueState));
        $aryClaimpattern = $codeMaster->getMasterCodes(12);
        unset($aryClaimpattern['1']);
        $this->view->assign("claimPatternSearchTag",BaseHtmlUtils::SelectTag('claimpattern',$aryClaimpattern,$claimpattern,'onChange="javascript:changeCp(); "'));
        $aryOem = array(-1 => '----------', 0 => 'キャッチボール', 99 => 'ＯＥＭすべて');
        $ri = $this->app->dbAdapter->query(" SELECT OemId, OemNameKj FROM T_Oem WHERE ValidFlg = 1 ORDER BY OemId ")->execute(null);
        foreach ($ri as $row) {
            $aryOem[$row['OemId']] = $row['OemNameKj'];
        }
        $this->view->assign("oemTag",BaseHtmlUtils::SelectTag('oem', $aryOem, $oem));
        $this->view->assign("entid", $entid);
        $this->view->assign("entnm", $entnm);
        $this->view->assign("odrid", $odrid);
        $this->view->assign("siteid", $siteid);

        return $this->view;
    }

    /**
     * 【個別出力】画面の検索条件に従った抽出SQLを戻す
     *
     * @return string 抽出SQL
     * @see バインド変数は戻り引数[$prm]へ設定される
     */
    protected function getList2Query($billIssueState, $oem, $entid, $entnm, $odrid, $siteid, $claimpattern, &$prm) {
        $sql = $this->getList2BaseQuery();

        $whereCondition = " AND 1 = 1 ";
        // SQL(各種考慮:請求書)
        if ($billIssueState !=-1) {
            $cws = "";
            if      ($billIssueState == 0) { $cws = "未発行"; }
            else if ($billIssueState == 1) { $cws = "指示済み"; }
            else if ($billIssueState == 2) { $cws = "確定待ち"; }
            $sql = " SELECT * FROM ( " . $sql . " ) tmp WHERE tmp.ConfirmWaitingStr = '" . $cws . "' ";
        }
        // SQL(各種考慮:OEM)
        if ($oem == 0) {
            // (キャッチボール)
            $whereCondition .= " AND (ETP.OemId = 0 OR ETP.OemId IS NULL) ";
        }
        else if ($oem == 99) {
            // (ＯＥＭすべて)
            $whereCondition .= " AND ETP.OemId > 0 ";
        }
        else if ($oem != -1) {
            // (OEM個別指定)
            $whereCondition .= " AND ETP.OemId = " . $oem;
        }
        // SQL(各種考慮:加盟店ID)
        if ($entid != '') {
            $whereCondition .= " AND ETP.LoginId like '%" . BaseUtility::escapeWildcard($entid) . "' ";
        }
        // SQL(各種考慮:加盟店名)
        if ($entnm != '') {
            $whereCondition .= " AND ETP.EnterpriseNameKj like '%" . BaseUtility::escapeWildcard($entnm) . "%' ";
        }
        // SQL(各種考慮:注文ID)
        if ($odrid != '') {
            $whereCondition .= " AND ORD.OrderId like '%" . BaseUtility::escapeWildcard($odrid) . "' ";
        }
        // SQL(各種考慮:サイトID)
        if ($siteid != '') {
            $whereCondition .= " AND ORD.SiteId =" . intval($siteid);
        }

        $sql = str_replace( '[WHERECONDITION]', $whereCondition, $sql );

        // SQL(各種考慮:バインド変数)
        $row_mc = $this->app->dbAdapter->query(" SELECT Class1, Class2 FROM M_Code WHERE CodeId = 183 AND KeyCode = :KeyCode "
            )->execute(array(':KeyCode' => $claimpattern))->current();

        $picup_pattern = 0;
        if      ($claimpattern == 9) { $picup_pattern = 8; }  // 再請求７出力チェック
        else if ($claimpattern == 8) { $picup_pattern = 7; }  // 再請求６出力チェック
        else if ($claimpattern == 7) { $picup_pattern = 6; }  // 再請求５出力チェック
        else if ($claimpattern == 6) { $picup_pattern = 4; }  // 再請求４出力チェック
        else if ($claimpattern == 4) { $picup_pattern = 2; }  // 再請求３出力チェック
        else if ($claimpattern == 2) { $picup_pattern = 1; }  // 再請求１出力チェック

        $prm = array(
                ':ClaimPatterCondition1' => date('Y-m-d', strtotime('-' . $row_mc['Class1'] . 'day')),
                ':ClaimPattern' => $picup_pattern,
                ':RiClaimPattern' => $claimpattern,
        );

        return $sql;
    }

    /**
     * 【個別出力】個別指定再請求書発行待ちリスト(個別指定CSV一括出力)用、基本SQL取得
     *
     * @return string 基本抽出SQL
     */
    protected function getList2BaseQuery() {
        return <<<EOQ
-- 自動出力のデータを取得
  SELECT CASE WHEN po.ConfirmWaitingFlg = 0 THEN '未発行'
              ELSE '確定待ち'
         END AS ConfirmWaitingStr
        ,ORD.OrderSeq AS OrderSeq
        ,ORD.OrderId AS OrderId
        ,ORD.RegistDate AS RegistDate
        ,voc.IncreArCaption
        ,ETP.EnterpriseNameKj
        ,CUS.NameKj
        ,CUS.UnitingAddress
        ,po.UseAmountTotal
        ,CASE ( SELECT MAX(ClaimPattern) FROM T_ClaimHistory tmpch WHERE OrderSeq = ORD.OrderSeq AND PrintedFlg = 1 AND ValidFlg = 1 )
             WHEN 1 THEN '初回請求'
             WHEN 2 THEN '再請求１'
             WHEN 4 THEN '再請求３'
             WHEN 6 THEN '再請求４'
             WHEN 7 THEN '再請求５'
             WHEN 8 THEN '再請求６'
             WHEN 9 THEN '再請求７'
             ELSE ''
         END AS ClaimStr
        ,CUS.CustomerId
        ,po.ConfirmWaitingFlg
        ,CLM.MypageReissueClass
        ,IFNULL(ETP.OemId,0) AS OemId
        ,ETP.EnterpriseId
        ,ETP.ClaimIndividualOutputFlg
    FROM T_ClaimControl CLM
         INNER JOIN T_Order ORD ON (ORD.OrderSeq = CLM.OrderSeq)
         INNER JOIN ( SELECT  t.P_OrderSeq
                             ,MIN(t.DataStatus)                      AS DataStatus                -- データステータス
                             ,MAX(IFNULL(t.LetterClaimStopFlg, 0))   AS LetterClaimStopFlg        -- 最大のストップフラグが1の場合＝ストップしたい注文あり
                             ,MAX(t.ConfirmWaitingFlg)               AS ConfirmWaitingFlg         -- 最大の確定待ちフラグが1の場合＝確定待ち注文あり
                             ,SUM(t.UseAmount)                       AS UseAmountTotal

                        FROM T_Order t
                       WHERE t.Cnl_Status = 0
                         AND t.DataStatus IN (51, 61)
                       GROUP BY t.P_OrderSeq
                    ) po
                 ON po.P_OrderSeq = ORD.OrderSeq
         INNER JOIN T_Enterprise ETP ON (ORD.EnterpriseId = ETP.EnterpriseId)
         INNER JOIN T_Site SIT ON (ORD.SiteId = SIT.SiteId)
         INNER JOIN T_Customer CUS ON (CUS.OrderSeq = ORD.OrderSeq)
         INNER JOIN T_EnterpriseCustomer EC ON EC.EntCustSeq = CUS.EntCustSeq
         INNER JOIN T_ManagementCustomer MC ON MC.ManCustId = EC.ManCustId
         INNER JOIN V_OrderCustomer voc ON voc.OrderSeq = ORD.OrderSeq

WHERE  1 = 1
AND    (po.LetterClaimStopFlg = 0 AND IFNULL(MC.RemindStopFlg, 0) = 0)
AND    CLM.ReissueClass = 0
AND    CLM.ClaimedBalance > 0
AND (
        (
                CLM.LimitDate <= :ClaimPatterCondition1 -- 今日 - 8日(CodeId=183、KeyCode=請求パターンのClass2)
            AND CLM.MypageReissueClass IN ( 0, 91, 92 )
            AND ( SELECT MAX(ClaimPattern) FROM T_ClaimHistory tmpch WHERE OrderSeq = ORD.OrderSeq AND PrintedFlg = 1 AND ValidFlg = 1 ) = :ClaimPattern
            AND ( SELECT COUNT(1) FROM T_ReclaimIndicate WHERE OrderSeq = ORD.ORDERseq AND IndicatedFlg = 0 AND ValidFlg = 1 ) = 0 -- 印刷指示が存在しないこと
        )
            OR  ( CLM.MypageReissueClass IN ( 1, 2 ) AND :RiClaimPattern = 2 ) -- 再１の場合のみ
            OR  ( SELECT MAX(ClaimPattern) FROM T_ClaimHistory tmpch WHERE OrderSeq = ORD.OrderSeq AND PrintedFlg = 0 AND ValidFlg = 1 ) = :RiClaimPattern
    )
[WHERECONDITION]
-- 印刷指示のデータを取得
UNION ALL
SELECT   '指示済み'
        ,ORD.OrderSeq
        ,ORD.OrderId
        ,ORD.RegistDate
        ,voc.IncreArCaption
        ,ETP.EnterpriseNameKj
        ,CUS.NameKj
        ,CUS.UnitingAddress
        ,po.UseAmountTotal
        ,CASE ( SELECT MAX(ClaimPattern) FROM T_ClaimHistory tmpch WHERE OrderSeq = ORD.OrderSeq AND PrintedFlg = 1 AND ValidFlg = 1 )
             WHEN 1 THEN '初回請求'
             WHEN 2 THEN '再請求１'
             WHEN 4 THEN '再請求３'
             WHEN 6 THEN '再請求４'
             WHEN 7 THEN '再請求５'
             WHEN 8 THEN '再請求６'
             WHEN 9 THEN '再請求７'
             ELSE ''
         END
        ,CUS.CustomerId
        ,0
        ,0
        ,IFNULL(ETP.OemId,0) AS OemId
        ,ETP.EnterpriseId
        ,ETP.ClaimIndividualOutputFlg
  FROM T_ReclaimIndicate RI
       INNER JOIN T_ClaimControl CLM
               ON CLM.OrderSeq  = RI.OrderSeq
       INNER JOIN T_Order ORD
               ON ORD.OrderSeq = CLM.OrderSeq
       INNER JOIN ( SELECT  t.P_OrderSeq
                           ,MAX(IFNULL(t.LetterClaimStopFlg, 0))   AS LetterClaimStopFlg
                           ,SUM(t.UseAmount)                       AS UseAmountTotal
                      FROM T_Order t
                     WHERE t.Cnl_Status = 0
                       AND t.DataStatus IN (51, 61)
                     GROUP BY t.P_OrderSeq
                  ) po
               ON po.P_OrderSeq = ORD.OrderSeq
       INNER JOIN T_Customer CUS ON CUS.OrderSeq = ORD.OrderSeq
       INNER JOIN T_EnterpriseCustomer EC ON EC.EntCustSeq = CUS.EntCustSeq
       INNER JOIN T_ManagementCustomer MC ON MC.ManCustId = EC.ManCustId
       INNER JOIN V_OrderCustomer voc ON voc.OrderSeq = ORD.OrderSeq
       INNER JOIN T_Enterprise ETP ON (ORD.EnterpriseId = ETP.EnterpriseId)
 WHERE 1 = 1
   AND (po.LetterClaimStopFlg = 0 AND IFNULL(MC.RemindStopFlg, 0) = 0)
   AND CLM.ReissueClass = 0
   AND CLM.ClaimedBalance > 0
   AND RI.ValidFlg = 1
   AND RI.IndicatedFlg = 0
   AND RI.ClaimPattern = :RiClaimPattern -- 印刷指示中の請求パターンを指定
[WHERECONDITION]
EOQ;
    }

    /**
     * 【個別出力】(Ajax)ジョブ転送を行う
     */
    public function list2csv1Action()
    {
        $params = $this->getParams();
        $this->app = Application::getInstance();

        $billIssueState = isset($params['billIssueState']) ? $params['billIssueState'] : -1;// 請求書
        $oem = isset($params['oem']) ? $params['oem'] : -1;// OEM
        $claimpattern = isset($params['claimpattern']) ? $params['claimpattern'] : 2;// 請求パターン(初期値は再請求１)
        $entid = isset($params['entid']) ? $params['entid'] : '';// 加盟店ID
        $entnm = isset($params['entnm']) ? $params['entnm'] : '';// 加盟店名
        $odrid = isset($params['odrid']) ? $params['odrid'] : '';// 注文ID
        $siteid = isset($params['siteid']) ? $params['siteid'] : '';// サイトID
        $status = 1;
        $message = "";

        try {
            // 対象注文SeqのIN句相当生成
            $sql = $this->getList2Query($billIssueState, $oem, $entid, $entnm, $odrid, $siteid, $claimpattern, $prm);
            $sql = " SELECT DISTINCT tmp.OrderSeq FROM ( " . $sql . " ) tmp ";
            $ri = $this->app->dbAdapter->query($sql)->execute($prm);
            if ($ri->count() > 0) {
                $aryOseq = array();
                foreach ($ri as $row) {
                    $aryOseq[] = $row['OrderSeq'];
                }
                $oseqs = implode(',', $aryOseq);

                // ジョブ転送を行う(for List2)
                $ceSeqs = array();

                $this->jobTransferForList2($claimpattern, $oseqs, $ceSeqs);

                if (!empty($ceSeqs)) {
                    // 請求エラーがある場合
                    $status = 2;
                    $message = $this->getStatusCaption($ceSeqs);
                }

            }
            $msg = '1';
        }
        catch(\Exception $e) {
            $status = 9;
            $message = $e->getMessage() . "\n";
            $message .= $e->getTraceAsString() . "\n";
            $this->app->logger->notice('【個別指定再請求書発行待ちリスト】'.$message);
        }

        echo \Zend\Json\Json::encode(array('status' => $status, 'message' => $message));

        return $this->response;

    }

    /**
     * 【個別出力】ジョブ転送を行う(for List2)
     *
     * @param Integer $claimPattern 請求パターン
     * @param String $oseqs 注文SEQのIN句
     * @param array $ceSeqs (return)請求エラーリストのSEQリスト
     */
    protected function jobTransferForList2($claimPattern, $oseqs, &$ceSeqs) {
        $mdlo = new TableOrder($this->app->dbAdapter);
        $mdls = new TableSite($this->app->dbAdapter);
        $mdlcc = new TableClaimControl($this->app->dbAdapter);
        $mdlch = new TableClaimHistory($this->app->dbAdapter);
        $mdlSysP = new TableSystemProperty($this->app->dbAdapter);
        $mdlri = new TableReclaimIndicate($this->app->dbAdapter);
        $mdlce = new TableClaimError($this->app->dbAdapter);

        $ceSeqs = array();

        // ユーザーIDの取得
        $obj = new \models\Table\TableUser($this->app->dbAdapter);
        $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

        // 督促支払期限有効日数を取得
        $validLimitDays2 = $mdlSysP->getValue(TableSystemProperty::DEFAULT_MODULE, 'systeminfo', 'ValidLimitDays2');

        // a. ジョブ転送が必要な対象を抽出
        $sql = <<<EOQ
SELECT CLM.*, ORD.NewSystemFlg, ORD.SiteId
     , CASE WHEN RI.Seq IS NULL THEN F_GetCampaignVal(ORD.EnterpriseId, ORD.SiteId, DATE(NOW()), 'ReClaimFee') ELSE NULL END AS ReClaimFee
     , RI.Seq As RiSeq
     , RI.ClaimCpId As RiClaimCpId
     , RI.DamageDays As RiDamageDays
     , RI.DamageBaseDate As RiDamageBaseDate
     , RI.DamageInterestAmount As RiDamageInterestAmount
     , RI.ClaimFee As RiClaimFee
     , RI.AdditionalClaimFee As RiAdditionalClaimFee
     , RI.ClaimAmount As RiClaimAmount
     ,SIT.RemindStopClass As RemindStopClass
  FROM T_ClaimControl CLM
       INNER JOIN T_Order ORD ON (ORD.OrderSeq = CLM.OrderSeq)
       INNER JOIN T_Site SIT ON (SIT.SiteId = ORD.SiteId)
       LEFT OUTER JOIN T_ReclaimIndicate RI ON (RI.OrderSeq  = CLM.OrderSeq AND RI.IndicatedFlg = 0 AND RI.ValidFlg = 1)
EOQ;
        $sql .= " WHERE ORD.OrderSeq IN (" . $oseqs . ") ";
        try {
        $ri = $this->app->dbAdapter->query($sql)->execute(null);
        } catch (\Exception $e) {
            $message = $e->getMessage() . "\n";
            $message .= $e->getTraceAsString() . "\n";
            $this->app->logger->notice('【個別指定再請求書発行待ちリスト:jobTransferForList2_1】'.$message);
            throw $e;
        }

        foreach ($ri as $row) {
            // ----------------------------------------
            // b. ジョブ転送処理を実施
            // ----------------------------------------
            // JNB口座オープン用にロック獲得を試行
            $order = $mdlo->find($row['OrderSeq'])->current();
            try {
            $lockItem = $this->getLockItemForJnbAccount($order);
            } catch (\Exception $e) {
                $message = $e->getMessage() . "\n";
                $message .= $e->getTraceAsString() . "\n";
                $this->app->logger->notice('【個別指定再請求書発行待ちリスト:jobTransferForList2_2】'.$message);
                throw $e;
            }

            // 請求履歴が有効かどうか判定
            if ($mdlch->getReservedCount($row['OrderSeq']) > 0) {
                try {
                    if($lockItem) {
                        $lockItem->terminate();
                    }
                } catch (\Exception $err) { ; }
                // ジョブ転送中のデータがいる場合はスキップ
                continue;
            }

            try {
                $debug = '3';
                //トランザクション開始
                $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();

                // 請求管理データ取得(取得済み)

                if (isset($row['RiSeq'])){
                    $debug = '3-1';
                    //再請求指示が基点のデータの場合
                    // 請求担当者
                    $ClaimCpId = $row['RiClaimCpId'];

                    // 遅延日数
                    $damageDays = $row['RiDamageDays'];
                    // 遅延日数算出基準日
                    $strDamageBaseDate = $row['RiDamageBaseDate'];
                    // 遅延損害金
                    $damageInterest = $row['RiDamageInterestAmount'];
                    // 請求手数料
                    $reClaimFee = $row['RiClaimFee'];
                    // 請求追加手数料
                    $AdditionalClaimFee = $row['RiAdditionalClaimFee'];

                    // 請求金額
                    $claimAmount = $row['RiClaimAmount'];

                    // 請求書発行通知メール
                    $MailFlg = 1;


                }else{
                    $debug = '3-2';
                    // 督促停止する対象を省く
                    if ($row['RemindStopClass'] == 1) {
                        $this->app->dbAdapter->getDriver()->getConnection()->rollback();
                        try { if($lockItem) { $lockItem->terminate(); } } catch (\Exception $err) { ; }
                        continue;
                    }

                    // 請求担当者
                    $ClaimCpId = $this->app->authManagerAdmin->getUserInfo()->OpId;

                    // 請求金額の再取得
                    // 原則画面と同じになるが、一部キャンセルされた場合を想定
                    $debug = '3-2-1';
                    $useAmount = $this->app->dbAdapter->query(" SELECT SUM(UseAmount) AS UseAmount FROM T_Order o WHERE o.Cnl_Status = 0 AND o.P_OrderSeq = :OrderSeq "
                        )->execute(array(':OrderSeq' => $row['OrderSeq']))->current()['UseAmount'];

                    // 遅延日数算出
                    $debug = '3-2-2';
                    $damageDays = BaseGeneralUtils::CalcSpanDaysFromString($row['DamageBaseDate'], date('Y-m-d'));
                    if ($damageDays < 0) {
                        $damageDays = 0;
                    }

                    // 遅延損害金算出
                    $damageInterest = 0;

                    // 再請求手数料を税込み金額に変換
                    // サイト/キャンペーンから取得した金額のみ変換
                    $debug = '3-2-3';
                    $row['ReClaimFee'] = $mdlSysP->getIncludeTaxAmount(date('Y-m-d'), $row['ReClaimFee']);

                    // 請求額算出
                    $isMypage = ($claimPattern == 2 && ($row['MypageReissueClass'] == 1 || $row['MypageReissueClass'] == 2)) ? True : False;

                    $reClaimFee = ($isMypage) ? $row['MypageReissueReClaimFee'] : ($row['ReClaimFee'] + $row['ClaimFee']);
                    $claimAmount = $useAmount + $reClaimFee + $damageInterest;

                    $strDamageBaseDate = ($damageDays > 0 ? $row['DamageBaseDate'] : null);

                    // 請求追加手数料
                    $AdditionalClaimFee = 0;

                    // 請求書発行通知メール
                    $MailFlg = ($isMypage) ? 1 : 0;

                }

                // サイト情報の取得
                $debug = '4';
                $row_site = $mdls->findSite($row['SiteId'])->current();

                // 期限日までの日数
                $ldays = $this->app->business['pay']['limitdays2'];

                // 遅延起算日日数を期限日までの日数に加算する。
                $ldays += $row_site['KisanbiDelayDays'];

                // 支払期限日算出
                $limitDate = date('Y-m-d', strtotime('+' . $ldays . 'day'));

                // 有効期限日数を算出
                $debug = '5';
                $validLimitDate = date('Y-m-d', strtotime("$validLimitDays2 day"));
                if (strtotime($limitDate) < strtotime($validLimitDate)) {
                    $debug = '5-1';
                    $this->app->dbAdapter->getDriver()->getConnection()->rollback();
                    // 支払期限日数が有効期限未満の場合は請求エラーとする
                    $ceSeqs[] = $mdlce->saveNew(array('OrderSeq' => $row['OrderSeq'], 'ErrorCode' => LogicClaimException::ERR_CODE_LIMIT_DAY, 'ErrorMsg' => $validLimitDays2));
                    try { if($lockItem) { $lockItem->terminate(); } } catch (\Exception $err) { ; }
                    continue;
                }

                // 延滞起算日がNULLの場合は今回の支払期限を次回からの延滞起算日にするためDBへ書き込み
                $debug = '6';
                if (strlen($row['DamageBaseDate']) == 0) {
                    $debug = '6-1';
                    $mdlcc->saveUpdate(array('DamageBaseDate' => $limitDate, 'UpdateId' => $userId), $row['ClaimId']);
                }

                // 請求履歴の作成
                $debug = '7';
                $data = array(
                        'OrderSeq' => $row['OrderSeq'],                     // 注文Seq
                        'ClaimDate' => date('Y-m-d'),                       // 請求日
                        'ClaimCpId' => $ClaimCpId,                          // 請求担当者
                        'ClaimPattern' => $claimPattern,                    // 請求パターン
                        'LimitDate' => $limitDate,                          // 支払期限
                        'DamageDays' => $damageDays,                        // 遅延日数
                        'DamageBaseDate' => $strDamageBaseDate,             // 遅延日数算出基準日
                        'DamageInterestAmount' => $damageInterest,          // 遅延損害金
                        'ClaimFee' => $reClaimFee,                          // 請求手数料
                        'AdditionalClaimFee' => $AdditionalClaimFee,        // 請求追加手数料
                        'PrintedFlg' => 0,                                  // 印刷－処理フラグ
                        'MailFlg' => $MailFlg,                              // 請求書発行通知メール
                        'EnterpriseBillingCode' => null,                    // 同梱ツールアクセスキー
                        'ClaimAmount' => $claimAmount,                      // 請求金額
                        'ClaimId' => $row['ClaimId'],                       // 請求ID
                        'RegistId' => $userId,                              // 登録者
                        'UpdateId' => $userId,                              // 更新者
                );

                try {
                    $hisSeq = $mdlch->saveNew($row['OrderSeq'], $data);
                } catch(LogicClaimException $e) {
                    $this->app->dbAdapter->getDriver()->getConnection()->rollback();
                    // SMBC連携エラーの場合は請求エラーとする
                    $ceSeqs[] = $mdlce->saveNew(array('OrderSeq' => $row['OrderSeq'], 'ErrorCode' => $e->getCode(), 'ErrorMsg' => $e->getMessage()));
                    try { if($lockItem) { $lockItem->terminate(); } } catch (\Exception $err) { ; }
                    continue;
                } catch(\Exception $e) {
                    $message = $e->getMessage() . "\n";
                    $message .= $e->getTraceAsString() . "\n";
                    $this->app->logger->notice('【個別指定再請求書発行待ちリスト:jobTransferForList2_'.$debug.'】'.$message);
                    throw $e;
                }

                $debug = '8';
                $uOrder = array(
                        'ConfirmWaitingFlg'  => '1',
                        'UpdateId'           => $userId,
                );

                $mdlo->saveUpdateWhere($uOrder, array('P_OrderSeq' => $row['OrderSeq']));

                // c. ジョブ転送を行った請求履歴に対して、更新を行う
                $debug = '9';
                $sql  = " UPDATE T_ClaimHistory ";
                $sql .= " SET    PrintedStatus = 1 ";
                $sql .= " ,      UpdateId = :UpdateId ";
                $sql .= " ,      UpdateDate = :UpdateDate ";
                $sql .= " WHERE  OrderSeq = :OrderSeq ";
                $sql .= " AND    PrintedFlg = 0 ";
                $sql .= " AND    ValidFlg = 1 ";

                $this->app->dbAdapter->query($sql)->execute(array(
                        ':OrderSeq' => $row['OrderSeq'],
                        ':UpdateId' => $userId,
                        ':UpdateDate' => date('Y-m-d H:i:s')));

                if (isset($row['RiSeq'])){
                    $debug = '9-1';
                    // 再請求指示データ経由のデータ対象に処理した場合は再請求指示データも更新
                    $mdlri->updateIndicated($row['OrderSeq'],$userId,$hisSeq);
                }

                $this->app->dbAdapter->getDriver()->getConnection()->commit();

            } catch (\Exception $e) {
                $this->app->dbAdapter->getDriver()->getConnection()->rollback();

                // ロックを獲得していたら開放
                try {
                    if($lockItem) {
                        $lockItem->terminate();
                    }
                } catch (\Exception $err) { ; }

                $message = $e->getMessage() . "\n";
                $message .= $e->getTraceAsString() . "\n";
                $this->app->logger->notice('【個別指定再請求書発行待ちリスト:jobTransferForList2_'.$debug.'】'.$message);
                // 処理失敗
                throw $e;
            }

            // ロックを獲得していたら開放
            try {
                if($lockItem) {
                    $lockItem->terminate();
                }
            } catch (\Exception $e) { ; }
        }
    }

    /**
     * 【個別出力】ＣＳＶ出力
     */
    public function list2csv2Action()
    {
        $params = $this->getParams();

        // ZIPファイル作成
        $zip = new \ZipArchive();

        // 出力時刻
        $formatNowStr = date('YmdHis');

        // 出力ファイル名
        $outFileName= ('ReClaim_' . $formatNowStr . '.zip');

        // TEMP領域作成
        $tmpFilePath = tempnam( sys_get_temp_dir(), 'tmp' );

        // ZIPファイルオープン
        $zip->open( $tmpFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE );

        // 抽出対象のOEM取得
        $sql = "";
        if ($params['oem'] == -1) {
            $sql = " SELECT 0 AS OemId UNION ALL SELECT OemId FROM T_Oem WHERE ValidFlg = 1 ";
        }
        else if ($params['oem'] == 0) {
            $sql = " SELECT 0 AS OemId ";
        }
        else if ($params['oem'] == 99) {
            $sql = " SELECT OemId FROM T_Oem WHERE ValidFlg = 1 ";
        }
        else {
            $sql = " SELECT " . $params['oem'] . " AS OemId ";
        }
        $ri_oem = $this->app->dbAdapter->query($sql)->execute(null);

        // 個別出力の加盟店取得
        $ri_cio = $this->app->dbAdapter->query(" SELECT e.OemId, e.EnterpriseId FROM T_Enterprise e LEFT JOIN T_Oem o ON e.OemId = o.OemId WHERE e.ClaimIndividualOutputFlg = 1 AND (e.OemId = 0 OR o.ValidFlg = 1) AND e.EnterpriseId != (SELECT PropValue FROM T_SystemProperty WHERE Module = '[DEFAULT]' AND Category = 'systeminfo' AND Name = 'NTTFEnterpriseId') ")->execute(null);

        $unlinkList = array();

        // ================= OEM別出力 =================
        foreach ($ri_oem as $row_oem) {
            $filename = $this->csvDownloadForList2($row_oem['OemId'], $params['claimpattern'], $formatNowStr, $tmpFilePath, $params);
            if ($filename != '' ) {
                $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
                $addFilePath = file_get_contents( $filename );
                $zip->addFromString( $pathcutfilename, $addFilePath );
                $unlinkList[] = $filename;
            }
        }

        // ================= 個別出力 =================
        foreach ($ri_cio as $row_cio) {
            $filename = $this->csvDownloadForCioList2($row_cio['OemId'], $row_cio['EnterpriseId'], $params['claimpattern'], $formatNowStr, $tmpFilePath, $params);
            if ($filename != '' ) {
                $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
                $addFilePath = file_get_contents( $filename );
                $zip->addFromString( $pathcutfilename, $addFilePath );
                $unlinkList[] = $filename;
            }
        }

        // ZIPファイルクローズ
        $zip->close();

        // ヘッダ
        header( 'Content-Type: application/octet-stream; name="' . $outFileName . '"' );
        header( 'Content-Disposition: attachment; filename="' . $outFileName . '"' );
        header( 'Content-Length: ' . filesize( $tmpFilePath ) );

        // 出力
        echo readfile( $tmpFilePath );

        // TEMP領域削除
        // count関数対策
        $unlinkListLen = 0;
        if(!empty($unlinkList)) {
            $unlinkListLen = count($unlinkList);
        }

        for ($i=0; $i<$unlinkListLen; $i++) {
            unlink( $unlinkList[$i] );
        }
        unlink( $tmpFilePath );
        die();
    }

    /**
     * 【個別出力】CSV出力を行う(for List2)
     *
     * @param int $oemId OemID
     * @param int $claimPattern 請求パターン
     * @param string $formatNowStr 書式化年月日時分秒
     * @param string $tmpFilePath TEMP領域
     * @param array list2csv2Actionでの[getParams()]値
     * @return ファイル名 ※出力が行われなかった場合は''を戻す
     */
    protected function csvDownloadForList2($oemId, $claimPattern, $formatNowStr, $tmpFilePath, $params) {

        // ユーザーIDの取得
        $obj = new \models\Table\TableUser($this->app->dbAdapter);
        $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

        $mdlsp = new TableSystemProperty($this->app->dbAdapter);
        $ntteid = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'NTTFEnterpriseId');

        // 画面検索条件による抽出
        // NOTE. OEMID／請求パターンは、[$params]ではなく引数の[$oemId][$claimPattern]を使う
        $billIssueState = isset($params['billIssueState']) ? $params['billIssueState'] : -1;// 請求書
        $entid = isset($params['entid']) ? $params['entid'] : '';// 加盟店ID
        $entnm = isset($params['entnm']) ? $params['entnm'] : '';// 加盟店名
        $odrid = isset($params['odrid']) ? $params['odrid'] : '';// 注文ID
        $siteid = isset($params['siteid']) ? $params['siteid'] : '';// サイトID
        $basesql = $this->getList2Query($billIssueState, $oemId, $entid, $entnm, $odrid, $siteid, $claimPattern, $prm);
        $sql = " SELECT DISTINCT tmp.OrderSeq FROM ( " . $basesql . " ) tmp ";
        // 個別出力しない加盟店のみ
        $sql .= " WHERE  1 = 1 ";
        $sql .= " AND    (tmp.ClaimIndividualOutputFlg = 0 ";
        $sql .= " OR      tmp.EnterpriseId = " . $ntteid . " ) ";
        $ri = $this->app->dbAdapter->query($sql)->execute($prm);
        if (!($ri->count() > 0)) {
            return ''; // 出力対象件数が0の場合は以降処理不要
        }

        //---------------------------------------
        // 出力ファイル名生成
        $keycode = ((int)$oemId * 10 + (int)$claimPattern);
        // (プレフィックス1)
        $sql  = " SELECT Class1 ";
        $sql .= " FROM   M_Code ";
        $sql .= " WHERE  CodeId = 181 ";
        $sql .= " AND    KeyCode = (SELECT Class1 FROM M_Code WHERE CodeId = 182 AND KeyCode = :KeyCode) ";
        $prefix1 = $this->app->dbAdapter->query($sql)->execute(array(':KeyCode' => $keycode))->current()['Class1'];
        // (プレフィックス2)
        $sql  = " SELECT Class2 FROM M_Code WHERE CodeId = 182 AND KeyCode = :KeyCode ";
        $prefix2 = $this->app->dbAdapter->query($sql)->execute(array(':KeyCode' => $keycode))->current()['Class2'];
        // (ファイル名生成)
        $fileName = ($prefix1 . '_' . $prefix2 . '_' . $formatNowStr . '.csv');
        $ri_enterprise = $this->app->dbAdapter->query(" SELECT DISTINCT tmp.EnterpriseId FROM ( " . $basesql . " ) tmp ")->execute($prm);
        if ($ri_enterprise->count() == 1) {
            // (単一加盟店のみの時は、加盟店IDを付与する形式に変更)
            $enterpriseId = $ri_enterprise->current()['EnterpriseId'];
            $fileName = ($prefix1 . '_' . $prefix2 . '_' . $enterpriseId . '_' . $formatNowStr . '.csv');
        }

        //---------------------------------------
        // Ｅストア考慮(現行互換ＩＦ) ※再請求１出力時限定
        $estoreFlg = false;
        $estoreItemsCnt = 12;
        if ($claimPattern == 2 && $oemId > 0) {
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
            $sql .= ' ,      cc.MypageReissueClass ';
            $sql .= ' ,      e.PrintEntOrderIdOnClaimFlg ';
            $sql .= ' ,      cc.ReceiptAmountTotal ';
            $sql .= ' FROM   T_Order o INNER JOIN ';
            $sql .= '        T_Customer c ON ( o.OrderSeq = c.OrderSeq ) INNER JOIN ';
            $sql .= '        T_Enterprise e ON ( o.EnterpriseId = e.EnterpriseId ) INNER JOIN ';
            $sql .= '        T_Site s ON ( o.SiteId = s.SiteId ) INNER JOIN ';
            $sql .= '        T_ClaimControl cc ON o.OrderSeq = cc.OrderSeq LEFT OUTER JOIN ';
            $sql .= '        T_ClaimHistory ch ON ( o.OrderSeq = ch.OrderSeq AND ch.PrintedFlg = 0 AND ch.ValidFlg = 1 ) LEFT OUTER JOIN ';
            $sql .= '        T_OemClaimAccountInfo ca ON( ch.Seq = ca.ClaimHistorySeq AND ca.Status = 1 ) ';
            $sql .= ' WHERE  o.OrderSeq = :OrderSeq ';

            $sql .= " AND    cc.ClaimedBalance > 0 ";       // 2015/12/04 Y.Suzuki Add 暫定対応（残高が0のﾃﾞｰﾀは出力しない）

            $data = $this->app->dbAdapter->query( $sql )->execute( $prm )->current();

            // 請求金額
            $data['ClaimAmount'] = nvl( $data['ClaimAmount'], 0 ) - nvl( $data['ReceiptAmountTotal'], 0 );

            // 請求金額が0円以下の場合は出力を行わない(特に、T_ClaimHistoryが取得出来ない場合を想定)
            if ( $data['ClaimAmount'] <= 0 ) {
                continue;
            }

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

            // 注文商品＋配送先
            $sql  = ' SELECT itm.ItemNameKj ';
            $sql .= ' ,      itm.ItemNum ';
            $sql .= ' ,      itm.UnitPrice ';
            $sql .= ' ,      itm.SumMoney ';
            $sql .= ' ,      e.DispDecimalPoint ';  /* 表示用小数点桁数 */
            $sql .= ' ,      dd.PostalCode ';       /* 配送先郵便番号 */
            $sql .= ' ,      dd.UnitingAddress ';   /* 配送先住所 */
            $sql .= ' ,      dd.DestNameKj ';       /* 配送先氏名 */
            $sql .= ' FROM   T_Order o INNER JOIN ';
            $sql .= '        T_OrderItems itm ON ( o.OrderSeq = itm.OrderSeq ) ';
            $sql .= '        INNER JOIN T_Enterprise e ON ( e.EnterpriseId = o.EnterpriseId ) ';
            $sql .= '        INNER JOIN T_DeliveryDestination dd ON ( itm.DeliDestId = dd.DeliDestId ) ';
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

            // 入金情報
            if ( nvl( $data['ReceiptAmountTotal'], 0 ) > 0 ) {
                // 入金されている場合、第15明細に情報を設定する
                $data['ItemNameKj_15'] = '入金済額';
                $data['ItemNum_15'] = '1';
                $data['UnitPrice_15'] = $data['ReceiptAmountTotal'] * -1;
                $data['SumMoney_15'] = $data['ReceiptAmountTotal'] * -1;
            }

            // 小計
            $data['TotalItemPrice'] = 0;
            foreach ($items as $row) {
                $data['TotalItemPrice'] += $row['SumMoney'];
            }

            // マイページ再発行で配送先に請求した場合
            if ($data['MypageReissueClass'] == 2) {
                // 請求先情報を配送先情報（第一明細を使用）に変更
                $data['PostalCode'] = $items[0]['PostalCode'];
                $data['UnitingAddress'] = $items[0]['UnitingAddress'];
                $data['NameKj'] = $items[0]['DestNameKj'];
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
            $sql  = ' SELECT COUNT(*) AS ReIssueCount ';
            $sql .= ' FROM   T_ClaimHistory ';
            $sql .= ' WHERE  PrintedFlg <> 0 ';
            $sql .= ' AND    ValidFlg = 1 ';
            $sql .= ' AND    OrderSeq = :OrderSeq ';
            $data = array_merge( $data, $this->app->dbAdapter->query( $sql )->execute( $prm )->current() );
            $data['ReIssueCount'] += 1;

            // マイページログインパスワード
            $row_mypageorder = $this->app->dbAdapter->query(" SELECT Token FROM T_MypageOrder WHERE OrderSeq = :OrderSeq ORDER BY Seq DESC LIMIT 1 "
            )->execute(array(':OrderSeq' => $ri_row['OrderSeq']))->current();
            $data['MypageToken'] = ($row_mypageorder) ? $row_mypageorder['Token'] : '';

            // 商品合計数
            $data['ItemsCount'] = 0;

            // count関数対策
            if(!empty($items)) {
                $data['ItemsCount'] = count($items);
            }

            // Eストアの場合のみ
            if ($estoreFlg) {
                if ($data['ItemsCount'] > $estoreItemsCnt) {
                    // 商品明細数が12を超えている場合、12明細目の内容を変更
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
                if ( nvl( $data['ReceiptAmountTotal'], 0 ) > 0 ) {
                    // 入金されている場合、第13明細に情報を設定する
                    $data['ItemNameKj_13'] = '入金済額';
                    $data['ItemNum_13'] = '1';
                    $data['UnitPrice_13'] = $data['ReceiptAmountTotal'] * -1;
                    $data['SumMoney_13'] = $data['ReceiptAmountTotal'] * -1;
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

            $datas[] = $data;

            // 出力した請求履歴データに対する更新処理
            $sql  = " UPDATE T_ClaimHistory ";
            $sql .= " SET    PrintedStatus = 2 ";
            $sql .= " ,      UpdateId = :UpdateId ";
            $sql .= " ,      UpdateDate = :UpdateDate ";
            $sql .= " WHERE  OrderSeq = :OrderSeq ";
            $sql .= " AND    PrintedFlg = 0 ";
            $sql .= " AND    PrintedStatus = 1 ";
            $sql .= " AND    ValidFlg = 1 ";

            $this->app->dbAdapter->query($sql)->execute(array(
                    ':UpdateId' => $userId,
                    ':UpdateDate' => date('Y-m-d H:i:s', strtotime($formatNowStr)),
                    ':OrderSeq' => $ri_row['OrderSeq']
            ));
        }

        //---------------------------------------
        $templateId = 'CKI04043_1'; // 請求書発行（再請求）
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

    protected function csvDownloadForCioList2($oemId, $enterpriseId, $claimPattern, $formatNowStr, $tmpFilePath, $params) {

        // ユーザーIDの取得
        $obj = new \models\Table\TableUser($this->app->dbAdapter);
        $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

        // 画面検索条件による抽出
        // NOTE. OEMID／請求パターンは、[$params]ではなく引数の[$oemId][$claimPattern]を使う
        $billIssueState = isset($params['billIssueState']) ? $params['billIssueState'] : -1;// 請求書
        $entid = isset($params['entid']) ? $params['entid'] : '';// 加盟店ID
        $entnm = isset($params['entnm']) ? $params['entnm'] : '';// 加盟店名
        $odrid = isset($params['odrid']) ? $params['odrid'] : '';// 注文ID
        $siteid = isset($params['siteid']) ? $params['siteid'] : '';// サイトID
        $basesql = $this->getList2Query($billIssueState, $oemId, $entid, $entnm, $odrid, $siteid, $claimPattern, $prm);
        $sql = " SELECT DISTINCT tmp.OrderSeq FROM ( " . $basesql . " ) tmp ";
        //個別出力する加盟店のみ
        $sql .= " WHERE  1 = 1 ";
        $sql .= " AND    tmp.EnterpriseId = " . $enterpriseId . " ";
        $ri = $this->app->dbAdapter->query($sql)->execute($prm);
        if (!($ri->count() > 0)) {
            return ''; // 出力対象件数が0の場合は以降処理不要
        }

        //---------------------------------------
        // 出力ファイル名生成
        $keycode = ((int)$oemId * 10 + (int)$claimPattern);
        // (プレフィックス1)
        $sql  = " SELECT Class1 ";
        $sql .= " FROM   M_Code ";
        $sql .= " WHERE  CodeId = 181 ";
        $sql .= " AND    KeyCode = (SELECT Class1 FROM M_Code WHERE CodeId = 182 AND KeyCode = :KeyCode) ";
        $prefix1 = $this->app->dbAdapter->query($sql)->execute(array(':KeyCode' => $keycode))->current()['Class1'];
        // (プレフィックス2)
        $sql  = " SELECT Class2 FROM M_Code WHERE CodeId = 182 AND KeyCode = :KeyCode ";
        $prefix2 = $this->app->dbAdapter->query($sql)->execute(array(':KeyCode' => $keycode))->current()['Class2'];
        // (ファイル名生成)
        $fileName = ($prefix1 . '_' . $prefix2 . '_' . $enterpriseId . '_' . $formatNowStr . '.csv');
        $ri_enterprise = $this->app->dbAdapter->query(" SELECT DISTINCT tmp.EnterpriseId FROM ( " . $basesql . " ) tmp ")->execute($prm);
        if ($ri_enterprise->count() == 1) {
            // (単一加盟店のみの時は、加盟店IDを付与する形式に変更)
            $enterpriseId = $ri_enterprise->current()['EnterpriseId'];
            $fileName = ($prefix1 . '_' . $prefix2 . '_' . $enterpriseId . '_' . $formatNowStr . '.csv');
        }

        //---------------------------------------
        // Ｅストア考慮(現行互換ＩＦ) ※再請求１出力時限定
        $estoreFlg = false;
        $estoreItemsCnt = 12;
        if ($claimPattern == 2 && $oemId > 0) {
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
            $sql .= ' ,      cc.MypageReissueClass ';
            $sql .= ' ,      e.PrintEntOrderIdOnClaimFlg ';
            $sql .= ' ,      cc.ReceiptAmountTotal ';
            $sql .= ' ,      o.Ent_Note ';
            $sql .= ' FROM   T_Order o INNER JOIN ';
            $sql .= '        T_Customer c ON ( o.OrderSeq = c.OrderSeq ) INNER JOIN ';
            $sql .= '        T_Enterprise e ON ( o.EnterpriseId = e.EnterpriseId ) INNER JOIN ';
            $sql .= '        T_Site s ON ( o.SiteId = s.SiteId ) INNER JOIN ';
            $sql .= '        T_ClaimControl cc ON o.OrderSeq = cc.OrderSeq LEFT OUTER JOIN ';
            $sql .= '        T_ClaimHistory ch ON ( o.OrderSeq = ch.OrderSeq AND ch.PrintedFlg = 0 AND ch.ValidFlg = 1 ) LEFT OUTER JOIN ';
            $sql .= '        T_OemClaimAccountInfo ca ON( ch.Seq = ca.ClaimHistorySeq AND ca.Status = 1 ) ';
            $sql .= ' WHERE  o.OrderSeq = :OrderSeq ';

            $sql .= " AND    cc.ClaimedBalance > 0 ";       // 2015/12/04 Y.Suzuki Add 暫定対応（残高が0のﾃﾞｰﾀは出力しない）

            $data = $this->app->dbAdapter->query( $sql )->execute( $prm )->current();

            // 請求金額
            $data['ClaimAmount'] = nvl( $data['ClaimAmount'], 0 ) - nvl( $data['ReceiptAmountTotal'], 0 );

            // 請求金額が0円以下の場合は出力を行わない(特に、T_ClaimHistoryが取得出来ない場合を想定)
            if ( $data['ClaimAmount'] <= 0 ) {
                continue;
            }

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

            // 注文商品＋配送先
            $sql  = ' SELECT itm.ItemNameKj ';
            $sql .= ' ,      itm.ItemNum ';
            $sql .= ' ,      itm.UnitPrice ';
            $sql .= ' ,      itm.SumMoney ';
            $sql .= ' ,      e.DispDecimalPoint ';  /* 表示用小数点桁数 */
            $sql .= ' ,      dd.PostalCode ';       /* 配送先郵便番号 */
            $sql .= ' ,      dd.UnitingAddress ';   /* 配送先住所 */
            $sql .= ' ,      dd.DestNameKj ';       /* 配送先氏名 */
            $sql .= ' FROM   T_Order o INNER JOIN ';
            $sql .= '        T_OrderItems itm ON ( o.OrderSeq = itm.OrderSeq ) ';
            $sql .= '        INNER JOIN T_Enterprise e ON ( e.EnterpriseId = o.EnterpriseId ) ';
            $sql .= '        INNER JOIN T_DeliveryDestination dd ON ( itm.DeliDestId = dd.DeliDestId ) ';
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

            // 入金情報
            if ( nvl( $data['ReceiptAmountTotal'], 0 ) > 0 ) {
                // 入金されている場合、第15明細に情報を設定する
                $data['ItemNameKj_15'] = '入金済額';
                $data['ItemNum_15'] = '1';
                $data['UnitPrice_15'] = $data['ReceiptAmountTotal'] * -1;
                $data['SumMoney_15'] = $data['ReceiptAmountTotal'] * -1;
            }

            // 小計
            $data['TotalItemPrice'] = 0;
            foreach ($items as $row) {
                $data['TotalItemPrice'] += $row['SumMoney'];
            }

            // マイページ再発行で配送先に請求した場合
            if ($data['MypageReissueClass'] == 2) {
                // 請求先情報を配送先情報（第一明細を使用）に変更
                $data['PostalCode'] = $items[0]['PostalCode'];
                $data['UnitingAddress'] = $items[0]['UnitingAddress'];
                $data['NameKj'] = $items[0]['DestNameKj'];
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
            $sql  = ' SELECT COUNT(*) AS ReIssueCount ';
            $sql .= ' FROM   T_ClaimHistory ';
            $sql .= ' WHERE  PrintedFlg <> 0 ';
            $sql .= ' AND    ValidFlg = 1 ';
            $sql .= ' AND    OrderSeq = :OrderSeq ';
            $data = array_merge( $data, $this->app->dbAdapter->query( $sql )->execute( $prm )->current() );
            $data['ReIssueCount'] += 1;

            // マイページログインパスワード
            $row_mypageorder = $this->app->dbAdapter->query(" SELECT Token FROM T_MypageOrder WHERE OrderSeq = :OrderSeq ORDER BY Seq DESC LIMIT 1 "
            )->execute(array(':OrderSeq' => $ri_row['OrderSeq']))->current();
            $data['MypageToken'] = ($row_mypageorder) ? $row_mypageorder['Token'] : '';

            // 商品合計数
            $data['ItemsCount'] = 0;

            // count関数対策
            if(!empty($items)) {
                $data['ItemsCount'] = count($items);
            }

            // Eストアの場合のみ
            if ($estoreFlg) {
                if ($data['ItemsCount'] > $estoreItemsCnt) {
                    // 商品明細数が12を超えている場合、12明細目の内容を変更
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
                if ( nvl( $data['ReceiptAmountTotal'], 0 ) > 0 ) {
                    // 入金されている場合、第13明細に情報を設定する
                    $data['ItemNameKj_13'] = '入金済額';
                    $data['ItemNum_13'] = '1';
                    $data['UnitPrice_13'] = $data['ReceiptAmountTotal'] * -1;
                    $data['SumMoney_13'] = $data['ReceiptAmountTotal'] * -1;
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
            $sql .= " AND    PrintedStatus = 1 ";
            $sql .= " AND    ValidFlg = 1 ";

            $this->app->dbAdapter->query($sql)->execute(array(
                    ':UpdateId' => $userId,
                    ':UpdateDate' => date('Y-m-d H:i:s', strtotime($formatNowStr)),
                    ':OrderSeq' => $ri_row['OrderSeq']
            ));
        }

        //---------------------------------------
        $templateId = 'CKI04046_1'; // 請求書発行（再請求）
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
     * 【個別出力】(Ajax)印刷済みに更新
     */
    public function list2upAction()
    {
        $params = $this->getParams();

        // ユーザーIDの取得
        $obj = new \models\Table\TableUser($this->app->dbAdapter);
        $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

        try {
            //トランザクション開始
            $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();

            // 画面上チェックオンの注文SEQ一覧生成
            $aryOseq = array();
            $i = 0;
            while (isset($params['OrderSeq' . $i])) {
                if (!isset($params['chkWaitDecision' . $i])) { $i++; continue; }
                if (!is_numeric($params['OrderSeq' . $i])) { $i++; continue; }
                $aryOseq[] = $params['OrderSeq' . $i];
                $i++;
            }

            // 件数が0の時は例外とする
            // count関数対策
            if (empty($aryOseq)) {
                throw new \Exception('更新対象がありません。');
            }

            // 注文SEQのIN句生成
            $oseqs = implode(',', $aryOseq);

            // ２．対象の抽出、１件ずつループ
            $sql  = " SELECT o.OutOfAmends, ch.* ";
            $sql .= " FROM   T_ClaimHistory ch ";
            $sql .= "        INNER JOIN T_Order o ON (o.OrderSeq = ch.OrderSeq) ";
            $sql .= " WHERE  1 = 1 ";
            $sql .= " AND    ch.ClaimPattern >= 2 ";
            $sql .= " AND    ch.PrintedStatus IN (2, 3) ";
            $sql .= " AND    ch.PrintedFlg = 0 ";
            $sql .= " AND    ch.ValidFlg = 1 ";
            $sql .= " AND    (o.Cnl_Status IS NULL OR o.Cnl_Status = 0) ";
            $sql .= " AND    (o.LetterClaimStopFlg IS NULL OR o.LetterClaimStopFlg = 0) ";
            $sql .= " AND    o.OrderSeq IN (" . $oseqs . ") ";

            $ri = $this->app->dbAdapter->query($sql)->execute(null);

            // 請求関連処理SQL
            $stm = $this->app->dbAdapter->query($this->getBaseP_ClaimControl());

            // SQL実行結果取得用のSQL
            $getretvalsql = " SELECT @po_ret_sts AS po_ret_sts, @po_ret_errcd AS po_ret_errcd, @po_ret_sqlcd AS po_ret_sqlcd, @po_ret_msg AS po_ret_msg ";

            $history = new CoralHistoryOrder($this->app->dbAdapter);
            $mdlo = new TableOrder($this->app->dbAdapter);

            foreach ($ri as $row) {

                // 2-1. チェック処理
                // (a. 有効な注文か否かチェックする)
                $sql  = " SELECT COUNT(*) AS cnt ";
                $sql .= " FROM   T_Order o ";
                $sql .= " WHERE  EXISTS(SELECT * FROM T_Order t WHERE t.P_OrderSeq = o.OrderSeq AND t.Cnl_Status = 0) ";
                $sql .= " AND    o.OrderSeq = :OrderSeq ";
                if ((int)$this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $row['OrderSeq']))->current()['cnt'] == 0) {
                    continue;// ０件の場合、次の注文へ
                }

                // (b. 請求履歴が有効か否かチェックする（ジョブ転送中でないことを確認する）)
                $sql = " SELECT COUNT(1) AS cnt FROM T_ClaimHistory WHERE PrintedFlg = 0 AND OrderSeq = :OrderSeq AND ValidFlg = 1 ";
                if ((int)$this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $row['OrderSeq']))->current()['cnt'] <= 0) {
                    continue;// ０件以下の場合、次の注文へ
                }

                // 2-2. 請求管理データの更新(P_ClaimControl CALL)
                $stm->execute(array(
                        ':pi_history_seq'   => $row['Seq'],
                        ':pi_button_flg'    => 1,
                        ':pi_user_id'       => $userId,
                ));

                // 2-3. 紙請求ストップフラグの判定
                $letterClaimStopFlg = 0;
                if (($row['OutOfAmends'] == 0 && $row['ClaimPattern'] >= 9) ||
                ($row['OutOfAmends'] == 1 && ($row['ClaimPattern'] >= 3 && $row['ClaimPattern'] < 9))) {
                    $letterClaimStopFlg = 1;
                }

                // 2-4. 注文データの更新
                $sql  = " UPDATE T_Order ";
                $sql .= " SET    LetterClaimStopFlg = :LetterClaimStopFlg ";
                $sql .= " ,      MailClaimStopFlg = 0 ";
                $sql .= " ,      UpdateId = :UpdateId ";
                $sql .= " ,      UpdateDate = :UpdateDate ";
                $sql .= " WHERE  P_OrderSeq = :OrderSeq ";

                $prm = array(
                        ':LetterClaimStopFlg'   => $letterClaimStopFlg,
                        ':UpdateId'             => $userId,
                        ':UpdateDate'           => date('Y-m-d H:i:s'),
                        ':OrderSeq'             => $row['OrderSeq'],
                );
                $this->app->dbAdapter->query($sql)->execute($prm);

                // 2-5. 親注文、子注文両方に対して、OEM先備考欄に請求書発行情報を印字する
                // 2-6. 親注文、子注文両方に対して、注文履歴登録SPをコールする
                // 履歴登録用理由コードを設定
                if ($row['ClaimPattern'] == 2) {
                    $reasonCode = 43;       // 再請求書発行（再１）
                } else if ($row['ClaimPattern'] == 4) {
                    $reasonCode = 44;       // 再請求書発行（再３）
                } else if ($row['ClaimPattern'] == 6) {
                    $reasonCode = 45;       // 再請求書発行（再４）
                } else if ($row['ClaimPattern'] == 7) {
                    $reasonCode = 46;       // 再請求書発行（再５）
                } else if ($row['ClaimPattern'] == 8) {
                    $reasonCode = 47;       // 再請求書発行（再６）
                } else if ($row['ClaimPattern'] == 9) {
                    $reasonCode = 48;       // 再請求書発行（再７）
                }

                // 注文履歴登録用に親注文Seqから子注文Seqを再取得する。
                $ri2 = $this->app->dbAdapter->query(" SELECT OrderSeq FROM T_Order WHERE P_OrderSeq = :P_OrderSeq AND Cnl_Status = 0 "
                )->execute(array(':P_OrderSeq' => $row['OrderSeq']));

                // 注文履歴へ登録(取得できた件数分ループする)
                foreach ($ri2 as $row2) {
                    // 備考に保存
                    $mdlo->appendPrintedInfoToOemNote($row2["OrderSeq"]);
                    // 注文履歴登録
                    $history->InsOrderHistory($row2["OrderSeq"], $reasonCode, $userId);
                }

                // 出力した請求履歴データに対する更新処理
                $sql  = " UPDATE T_ClaimHistory ";
                $sql .= " SET    PrintedStatus = 9 ";
                $sql .= " ,      UpdateId = :UpdateId ";
                $sql .= " ,      UpdateDate = :UpdateDate ";
                $sql .= " WHERE  Seq = :Seq ";

                $this->app->dbAdapter->query($sql)->execute(array(
                        ':UpdateId' => $userId,
                        ':UpdateDate' => date('Y-m-d H:i:s'),
                        ':Seq' => $row['Seq']
                ));
            }

            $this->app->dbAdapter->getDriver()->getConnection()->commit();

            $msg = '1';
        }
        catch(\Exception $e) {
            $this->app->dbAdapter->getDriver()->getConnection()->rollback();
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

            } elseif($row['ErrorCode'] == LogicClaimException::ERR_CODE_LIMIT_DAY) {
                $retFnc .= sprintf("%s 支払期限が%s日未満となるため、請求データが作成されませんでした。\n", $row['OrderId'], $row['ErrorMsg']);

            }
        }

        return $retFnc;
    }

}

