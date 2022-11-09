<?php
namespace cbadmin\Controller;

use Coral\Base\BaseHtmlUtils;
use Coral\Coral\Controller\CoralControllerAction;
use cbadmin\Application;
use models\Table\TableSystemProperty;
use models\Logic\LogicTemplate;
use Coral\Coral\History\CoralHistoryOrder;

class CreditlossController extends CoralControllerAction
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

        $this->addStyleSheet('../css/default02.css');
        $this->addJavaScript( '../js/json+.js' );
        $this->addJavaScript( '../js/prototype.js' );
        $this->addJavaScript( '../js/corelib.js' );
        $this->addJavaScript( '../js/base.ui.js');

        $this->setPageTitle("後払い.com - 貸し倒れ処理");
	}

	/**
	 * 貸し倒れ一覧
	 */
	public function listAction()
	{
        $params = $this->getParams();

        // (検索条件)
        $oem = (isset($params['oemList'])) ? $params['oemList'] : 0;
        // システムプロパティから督促日数取得
        $mdlsp = new TableSystemProperty($this->app->dbAdapter);
        $remindDays = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'RemindDays');
        $course = (isset($params['course'])) ? $params['course'] : $remindDays;
        // (登録条件)
        $kamoku = (isset($params['kamokuList'])) ? $params['kamokuList'] : -1;
        $returnCmnt = (isset($params['returnCmnt'])) ? $params['returnCmnt'] : '';

        // 検索
        $sql = $this->getCreditlossBaseSql('Class2');
        $where = "";
        if ($oem > 0) {
            // (OEM)
            $where .= (" AND o.OemId = " . $oem);
        }

        // (経過日数) 未入力の場合は0とする。
        $course = (empty($course)) ? 730 : $course;
        $cmpdt = date('Y-m-d', strtotime(" -" . $course . " day "));
        $where .= (" AND cc.F_LimitDate <> '0000-00-00' AND cc.F_LimitDate <= '" . $cmpdt . "' ");
        $where .= (" AND CASE WHEN (SELECT SUM(IFNULL(ReceiptAmount, 0)) FROM T_ReceiptControl WHERE o.P_OrderSeq = OrderSeq GROUP BY OrderSeq) > 0 THEN (SELECT MAX(ReceiptDate) FROM T_ReceiptControl WHERE OrderSeq = o.P_OrderSeq) ELSE '1970-01-01' END <= '" . $cmpdt . "' ");

        $sql = sprintf($sql, $where);

        $data = ResultInterfaceToArray($this->app->dbAdapter->query($sql)->execute(null));

        // OEMIDと名前のリスト取得
        $mdlOem = new \models\Table\TableOem($this->app->dbAdapter);
        $oemList = $mdlOem->getOemIdList();

        // 科目
        $ri = $this->app->dbAdapter->query(" SELECT KeyCode, KeyContent FROM M_Code WHERE Validflg = 1 AND CodeId = 96 AND Class3 = 1 ")->execute(null);
        $kamokuList = array();
        foreach ($ri as $row) {
            $kamokuList[$row['KeyCode']] = $row['KeyContent'];
        }

        // (検索条件)
        $this->view->assign('oemListTag',BaseHtmlUtils::SelectTag("oemList", $oemList, $oem));
        $this->view->assign('course',$course);
        // (登録条件)
        $this->view->assign('kamokuListTag',BaseHtmlUtils::SelectTag("kamokuList", $kamokuList, $kamoku));
        $this->view->assign('returnCmnt',$returnCmnt);
        // (リスト)
        $this->view->assign('list',$data);
        $this->view->assign('oem', $oem);

        return $this->view;
	}

	/**
	 * 貸し倒れ処理検索ベースSQLの取得
	 *
	 * @return string ベースSQL
	 */
	protected function getCreditlossBaseSql($content)
	{
	    $sql =<<<EOQ
SELECT  MAX(tbl.OemId) AS OemId                                     /* OEMID（非表示項目） */
,       MAX(tbl.OemNameKj) AS OemNameKj                             /* OEM先名 */
,       o.OrderSeq                                                  /* 注文Seq（非表示項目） */
,       MAX(o.OrderId) AS OrderId                                   /* 注文ID */
,       MAX(c.CustomerId) AS CustomerId                             /* 購入者ID（非表示項目） */
,       MAX(tbl.F_LimitDate) AS F_LimitDate                         /* 初回期限 */
,       MAX(tbl.ClaimDate) AS ClaimDate                             /* 最終請求 */
,       MAX(tbl.LimitDate) AS LimitDate                             /* 支払期限 */
,       MAX(tbl.IncreCaption) AS IncreCaption                       /* 請求 */
,       MAX(mc.$content) AS IncreLogCaption                         /* 属性 */
,       MAX(c.NameKj) AS NameKj                                     /* 請求先氏名 */
,       MAX(tbl.ClaimAmount) AS ClaimAmount                         /* 請求額 */
,       MAX(tbl.ReceiptAmount) AS ReceiptAmount                     /* 残高 */
,       GROUP_CONCAT(tbl.OrderSeq SEPARATOR ',') AS OrderSeqList    /* 実際の貸し倒れ対象 */
FROM
T_Order o
INNER JOIN T_Customer c ON (o.OrderSeq = c.OrderSeq)
LEFT OUTER JOIN M_Code mc ON (mc.KeyCode = CASE
                                                WHEN c.Incre_ArTel = 5 OR c.Incre_ArAddr = 5 THEN 5
                                                WHEN c.Incre_ArTel = 4 OR c.Incre_ArAddr = 4 THEN 4
                                                WHEN c.Incre_ArTel = 3 OR c.Incre_ArAddr = 3 THEN 3
                                                WHEN c.Incre_ArTel = 2 OR c.Incre_ArAddr = 2 THEN 2
                                                WHEN c.Incre_ArTel = 1 OR c.Incre_ArAddr = 1 THEN 1
                                                ELSE -1
                                           END
                              AND mc.CodeId = 4)

INNER JOIN
(
SELECT  o.OemId                                         /* OEMID（非表示項目） */
    ,   MAX(oem.OemNameKj) AS OemNameKj                 /* OEM先名 */
    ,   o.OrderSeq                                      /* 注文Seq（非表示項目） */
    ,   o.OrderId                                       /* 注文ID（非表示 項目 */
    ,   MAX(cc.F_LimitDate) AS F_LimitDate              /* 初回期限 */
    ,   MAX(cc.ClaimDate) AS ClaimDate                  /* 最終請求 */
    ,   MAX(cc.LimitDate) AS LimitDate                  /* 支払期限 */
    /* 請求 */
    ,   (SELECT $content
         FROM   M_Code
         WHERE  CodeId  =   12
         AND    KeyCode =   MAX(cc.ClaimPattern)
        ) AS IncreCaption
    ,   MAX(cc.ClaimAmount) AS ClaimAmount              /* 請求額 */
    /* 残高 */
    ,   MAX(IFNULL(cc.ClaimAmount,0)) - SUM(IFNULL(rc.ReceiptAmount,0)) AS ReceiptAmount
    ,   o.P_OrderSeq                                    /* 親注文Seq */
FROM    T_Order o
        LEFT OUTER JOIN T_Oem oem ON (o.OemId = oem.OemId)
        INNER JOIN T_ClaimControl cc ON (o.P_OrderSeq = cc.OrderSeq)
        LEFT OUTER JOIN T_ReceiptControl rc ON (o.P_OrderSeq = rc.OrderSeq)
WHERE   o.DataStatus IN (51, 61)
AND     o.Cnl_Status = 0
%s
GROUP BY
        o.OrderSeq
    ,   o.OemId
    ,   o.OrderId
) tbl ON o.OrderSeq = tbl.P_OrderSeq
GROUP BY OrderSeq
ORDER BY OrderSeq
EOQ;
	    return $sql;
	}

	/**
	 * 貸し倒れ画面(登録処理)
	 */
	public function saveAction()
	{
        $params = $this->getParams();

        // 更新処理実施
        $errorCount = 0;
        $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
        try
        {
            // ユーザIDの取得
            $userTable = new \models\Table\TableUser($this->app->dbAdapter);
            $userId = $userTable->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

            // 貸し倒れ関連処理SQL
            $stm = $this->app->dbAdapter->query($this->getBaseP_LoanLossControl());

            // SQL実行結果取得用のSQL
            $getretvalsql = " SELECT @po_ret_sts AS po_ret_sts, @po_ret_errcd AS po_ret_errcd, @po_ret_sqlcd AS po_ret_sqlcd, @po_ret_msg AS po_ret_msg ";

            $i = 0;

            while (isset($params['OrderSeqList' . $i]))
            {
                if (!isset($params['chkDecision' . $i])) { $i++; continue; }

                // プロシージャのコールは親注文Seqで！！！ → 注文単位ではなく、請求単位での貸し倒れになるため
                $prm = array(
                        ':pi_order_seq'     => $params['OrderSeq' . $i],
                        ':pi_sundry_class'  => $params['kamokuList'],
                        ':pi_note'          => $params['returnCmnt'],
                        ':pi_user_id'       => $userId,
                );

                $ri = $stm->execute($prm);

                // SQL実行例外なしもエラー戻り値の時は例外をｽﾛｰ
                $retval = $this->app->dbAdapter->query($getretvalsql)->execute(null)->current();
                if ($retval['po_ret_sts'] != 0) {
                    throw new \Exception($retval['po_ret_msg']);
                }

                $orderSeqList = explode(',', $params['OrderSeqList' . $i]);

                foreach($orderSeqList as $orderSeq) {

                    // 注文履歴へ登録（注文履歴は子注文単位で！！！）
                    $history = new CoralHistoryOrder($this->app->dbAdapter);
                    $history->InsOrderHistory($orderSeq, 91, $userId);
                }

                $i++;
            }

            $this->app->dbAdapter->getDriver()->getConnection()->commit();
        }
        catch(\Exception $e)
        {
            $this->app->dbAdapter->getDriver()->getConnection()->rollBack();

            $errorCount = 1;
        }

        $this->view->assign('errorCount', $errorCount);

        return $this->view;
	}

	/**
	 * 貸し倒れ関連処理ファンクションの基礎SQL取得。
	 *
	 * @return 貸し倒れ関連処理ファンクションの基礎SQL
	 */
	protected function getBaseP_LoanLossControl() {
	    return <<<EOQ
CALL P_LoanLossControl(
    :pi_order_seq
,   :pi_sundry_class
,   :pi_note
,   :pi_user_id
,   @po_ret_sts
,   @po_ret_errcd
,   @po_ret_sqlcd
,   @po_ret_msg
    )
EOQ;
	}

	/**
	 * 貸し倒れ処理一覧のCSVダウンロード
	 */
	public function dcsvAction()
	{
        $params  = $this->getParams();

        // (検索条件)
        $oem = ( isset($params['oem'] ) ) ? $params['oem'] : 0;
        $course = ( isset($params['course'] ) ) ? $params['course'] : 0;

        // 検索
        $sql = $this->getCreditlossBaseSql('KeyContent');
        $where = "";
        if( $oem > 0 ) {
            // (OEM)
            $where .= ( " AND o.OemId = " . $oem );
        }
        if( $course != '' && is_numeric( $course ) && $course > 0 ) {
            // (経過日数)
            $cmpdt = date( 'Y-m-d', strtotime( " -" . $course . " day " ) );
            $where .= ( " AND o.FinalityRemindDate < '" . $cmpdt . "' " );
        }

        $sql = sprintf( $sql, $where );

        $datas = ResultInterfaceToArray( $this->app->dbAdapter->query( $sql )->execute( null ) );

        $templateId = 'CKI08077_1'; // 貸し倒れ一覧データ
        $templateClass = 0;
        $seq = 0;
        $templatePattern = 0;

        $logicTemplate = new LogicTemplate( $this->app->dbAdapter );
        $response = $logicTemplate->convertArraytoResponse( $datas, sprintf( 'Kashidaore_%s.csv', date('YmdHis') ), $templateId, $templateClass, $seq, $templatePattern, $this->getResponse() );

        if( $response == false ) {
            throw new \Exception( $logicTemplate->getErrorMessage() );
        }

        return $response;
	}
}

