<?php
namespace cbadmin\Controller;

use Zend\Db\ResultSet\ResultSet;
use Coral\Coral\Controller\CoralControllerAction;
use Coral\Coral\Mail\CoralMail;
use Coral\Coral\Validate\CoralValidateUtility;
use Coral\Coral\CoralOrderUtility;
use cbadmin\Application;
use models\Table\TableOrder;
use models\Table\TableEnterprise;
use models\Table\TableClaimHistory;
use models\Table\TableGeneralPurpose;
use models\Table\TableCreditPoint;
use models\Table\TableCustomer;
use models\View\ViewOrderCustomer;
use models\Logic\LogicNormalizer;
use Coral\Coral\History\CoralHistoryOrder;
use models\Table\TableCreditOkTicket;

class GeneralsvcController extends CoralControllerAction
{
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
	}

	/**
	 * 備考を更新する。
	 */
	public function unoteAction()
	{
        try
        {
            $params = $this->getParams();

            $oseq = isset($params['oseq']) ? $params['oseq'] : 0;
            $udata['Incre_Note'] = isset($params['note']) ? $params['note'] : '';

            $mdlo = new TableOrder($this->app->dbAdapter);
            $mdlo->saveUpdate($udata, $oseq);
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
	 * ブラック登録を行う。
	 */
	public function registblkAction()
	{
        try
        {
            $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();

            // ユーザーIDの取得
            $obj = new \models\Table\TableUser($this->app->dbAdapter);
            $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

            $params = $this->getParams();

            // 社内与信条件更新
            $oseq = isset($params['oseq']) ? $params['oseq'] : 0;
            $sql = " UPDATE T_CreditCondition SET Class = 5, UpdateId = :UpdateId, UpdateDate = :UpdateDate WHERE OrderSeq = :OrderSeq ";
            $stm = $this->app->dbAdapter->query($sql);
            $stm->execute(array(':OrderSeq' => $oseq, ':UpdateId' => $userId, ':UpdateDate' => date('Y-m-d H:i:s')));

            // 管理顧客更新
            $mancustid = isset($params['mancustid']) ? $params['mancustid'] : 0;
            $sql = " UPDATE T_ManagementCustomer SET BlackFlg = 1, UpdateId = :UpdateId, UpdateDate = :UpdateDate WHERE ManCustId = :ManCustId ";
            $stm = $this->app->dbAdapter->query($sql);
            $stm->execute(array(':ManCustId' => $mancustid, ':UpdateId' => $userId, ':UpdateDate' => date('Y-m-d H:i:s')));

            // 成功指示
            $msg = '1';

            $this->app->dbAdapter->getDriver()->getConnection()->commit();
        }
        catch(\Exception $e)
        {
            $this->app->dbAdapter->getDriver()->getConnection()->rollback();
            $msg = $e->getMessage();
        }

        echo \Zend\Json\Json::encode(array('status' => $msg));
        return $this->response;
	}

	/**
	 * 優良顧客登録を行う。
	 */
	public function registexcAction()
	{
        try
        {
            $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();

            // ユーザーIDの取得
            $obj = new \models\Table\TableUser($this->app->dbAdapter);
            $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

            $params = $this->getParams();

            // 社内与信条件更新
            $oseq = isset($params['oseq']) ? $params['oseq'] : 0;
             $sql = " UPDATE T_CreditCondition SET Class = 2, UpdateId = :UpdateId, UpdateDate = :UpdateDate WHERE OrderSeq = :OrderSeq ";
             $stm = $this->app->dbAdapter->query($sql);
             $stm->execute(array(':OrderSeq' => $oseq, ':UpdateId' => $userId, ':UpdateDate' => date('Y-m-d H:i:s')));

             // 管理顧客更新
             $mancustid = isset($params['mancustid']) ? $params['mancustid'] : 0;
             $sql = " UPDATE T_ManagementCustomer SET GoodFlg = 1, UpdateId = :UpdateId, UpdateDate = :UpdateDate WHERE ManCustId = :ManCustId ";
             $stm = $this->app->dbAdapter->query($sql);
             $stm->execute(array(':ManCustId' => $mancustid, ':UpdateId' => $userId, ':UpdateDate' => date('Y-m-d H:i:s')));

            // 成功指示
            $msg = '1';

            $this->app->dbAdapter->getDriver()->getConnection()->commit();
        }
        catch(\Exception $e)
        {
            $this->app->dbAdapter->getDriver()->getConnection()->rollback();
            $msg = $e->getMessage();
        }

        echo \Zend\Json\Json::encode(array('status' => $msg));
        return $this->response;
	}

	/**
	 * 着荷確認取消を行う。
	 */
	public function registcancelconfirmarrivalAction()
	{
        try
        {
            $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();

            // ユーザーIDの取得
            $obj = new \models\Table\TableUser($this->app->dbAdapter);
            $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

            $params = $this->getParams();

            // 注文商品更新
            $oseq = isset($params['oseq']) ? $params['oseq'] : 0;
            $sql = " UPDATE T_OrderItems SET Deli_ConfirmArrivalFlg = 0, Deli_ConfirmArrivalDate = NULL, Deli_ConfirmArrivalOpId = NULL, UpdateId = :UpdateId, UpdateDate = :UpdateDate WHERE OrderSeq = :OrderSeq ";
            $stm = $this->app->dbAdapter->query($sql);
            $stm->execute(array(':OrderSeq' => $oseq, ':UpdateId' => $userId, ':UpdateDate' => date('Y-m-d H:i:s')));

            // 【請求管理.入金額合計（T_ClaimControl.ReceiptAmountTotal）が 0円 の場合、立替・売上管理テーブルを更新する】
            $sql = " SELECT IFNULL(MAX(cc.ReceiptAmountTotal), 0) AS ReceiptAmountTotal FROM T_Order o LEFT OUTER JOIN T_ClaimControl cc ON (cc.OrderSeq = o.P_OrderSeq) WHERE o.OrderSeq = :OrderSeq ";
            $stm = $this->app->dbAdapter->query($sql);
            $receiptAmountTotal = $stm->execute(array(':OrderSeq' => $oseq))->current()['ReceiptAmountTotal'];

            if ($receiptAmountTotal == 0) {
                // 立替・売上管理更新
                $sql = " UPDATE T_PayingAndSales SET ClearConditionForCharge = 0, ClearConditionDate = NULL, UpdateId = :UpdateId, UpdateDate = :UpdateDate WHERE OrderSeq = :OrderSeq ";
                $stm = $this->app->dbAdapter->query($sql);
                $stm->execute(array(':OrderSeq' => $oseq, ':UpdateId' => $userId, ':UpdateDate' => date('Y-m-d H:i:s')));

                // 立替・売上管理_会計更新(売上ﾀｲﾌﾟ、売上日の初期化)
                $row_pas = $this->app->dbAdapter->query(" SELECT Seq FROM T_PayingAndSales WHERE OrderSeq = :OrderSeq ")->execute(array(':OrderSeq' => $oseq))->current();
                $mdlapas = new \models\Table\ATablePayingAndSales($this->app->dbAdapter);
                $mdlapas->saveUpdate(array('ATUriType' => 99, 'ATUriDay' => '99999999'), $row_pas['Seq']);
            }

            // 注文更新
            $sql = " UPDATE T_Order SET Deli_ConfirmArrivalFlg = 0, Deli_ConfirmArrivalDate = NULL, UpdateId = :UpdateId, UpdateDate = :UpdateDate WHERE OrderSeq = :OrderSeq ";
            $stm = $this->app->dbAdapter->query($sql);
            $stm->execute(array(':OrderSeq' => $oseq, ':UpdateId' => $userId, ':UpdateDate' => date('Y-m-d H:i:s')));

            // 注文履歴へ登録
            $history = new CoralHistoryOrder($this->app->dbAdapter);
            $history->InsOrderHistory($oseq, 52, $userId);

            // 成功指示
            $msg = '1';

            $this->app->dbAdapter->getDriver()->getConnection()->commit();
        }
        catch(\Exception $e)
        {
            $this->app->dbAdapter->getDriver()->getConnection()->rollback();
            $msg = $e->getMessage();
        }

        echo \Zend\Json\Json::encode(array('status' => $msg));
        return $this->response;
	}

	/**
	 * 与信チェック用メールを送信する。
	 */
	public function sendcheckAction()
	{
        try
        {
            $params = $this->getParams();

            $oseq = isset($params['oseq']) ? $params['oseq'] : 0;

            // ユーザーIDの取得
            $obj = new \models\Table\TableUser($this->app->dbAdapter);
            $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

            $mail = new CoralMail($this->app->dbAdapter, $this->app->mail['smtp']);
            $mail->SendCheckMail($oseq, $userId);
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
	 * 事業者審査結果メールを送信する。
	 */
	public function sendexamAction()
	{
        try
        {
            // ユーザーIDの取得
            $obj = new \models\Table\TableUser($this->app->dbAdapter);
            $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

            $params = $this->getParams();

            $eid = isset($params['eid']) ? $params['eid'] : 0;

            $mail = new CoralMail($this->app->dbAdapter, $this->app->mail['smtp']);
            $mail->SendExamMail($eid, $userId);

            $mdle = new TableEnterprise($this->app->dbAdapter);
            $mdle->serviceIn($eid, $userId); // サービス開始日を記録する。

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
	 * 事業者宛テストメールを送信する。
	 */
	public function sendtestAction()
	{
        try
        {
            $params = $this->getParams();

            $eid = isset($params['eid']) ? $params['eid'] : 0;

            // ユーザーIDの取得
            $obj = new \models\Table\TableUser($this->app->dbAdapter);
            $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

            $mail = new CoralMail($this->app->dbAdapter, $this->app->mail['smtp']);
            $mail->SendTestMailForEnterprise($eid, $userId);

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
	 * 請求書不達メールを送信する。
	 */
	public function sendreturnbillAction()
	{
        try
        {
            $params = $this->getParams();

            $oseq = isset($params['oseq']) ? $params['oseq'] : 0;

            $orders = new TableOrder( $this->app->dbAdapter );
            $histories = new TableClaimHistory( $this->app->dbAdapter );

            // ユーザーIDの取得
            $obj = new \models\Table\TableUser($this->app->dbAdapter);
            $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

            // 注文情報取得
            $order = $orders->find( $oseq )->current();
          if( ! $order ) throw new \Exception( "sequence '" . $oseq ."' not found." );

            // 請求履歴件数取得
            $his_count = $histories->getReservedCount($oseq);

            //送信前チェック
            if ($order['Cnl_Status'] > 0) {
                throw new \Exception('この注文情報は既にキャンセルされています。');
            } elseif($his_count > 0) {
                throw new \Exception('印刷待ちのデータが存在しています。');
            }

            $mail = new CoralMail($this->app->dbAdapter, $this->app->mail['smtp']);
            $mail->SendReturnBillMail($oseq, $userId);
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
	 * 電話結果クレジットポイントの取得
	 */
	public function cprcAction()
	{
        $params = $this->getParams();

        $oseq = isset($params['oseq']) ? $params['oseq'] : 0;
        $realCallResultCode = isset($params['rcrc']) ? $params['rcrc'] : 0;

        echo $this->getCreditPointForRealCall($orderSeq, $realCallResultCode);
        return $this->response;
	}

	/**
	 * メール送信チェック結果クレジットポイントの取得
	 */
	public function cprsmAction()
	{
        $params = $this->getParams();

        $oseq = isset($params['oseq']) ? $params['oseq'] : 0;
        $realSendMailResultCode = isset($params['rsmrc']) ? $params['rsmrc'] : 0;

        echo $this->getCreditPointForRealSendMail($orderSeq, $realSendMailResultCode);
        return $this->response;
	}

	/**
	 * 電話結果に関する与信ポイントの取得。
	 *
	 * @param $orderSeq 注文Seq
	 * @param $realCallResultCode 電話結果コード
	 * @return string json文字列
	 */
	private function getCreditPointForRealCall($orderSeq, $realCallResultCode)
	{
        $mdloc = new ViewOrderCustomer($this->app->dbAdapter);
        $mdlgp = new TableGeneralPurpose($this->app->dbAdapter);
        $mdlcc = new TableCreditPoint($this->app->dbAdapter);

        $orderCustomer = $mdloc->find($orderSeq)->current();

        if ($realCallResultCode > 0)
        {
            $cpid = $mdlgp->getMasterAssCode(7, $realCallResultCode);
            $realCallPoint = $mdlcc->findCreditPoint($cpid)->current()['Point'];
        }
        else
        {
            $realCallPoint = 0;
        }

        $basePoint = $orderCustomer['Incre_ScoreTotal'] - $orderCustomer['RealCallScore'];

        $result['ScoreTotal'] = $basePoint + $realCallPoint;
        $result['RealCallScore'] = $realCallPoint;

        // T_Customerへ書き込み
        $mdlCustomer = new TableCustomer($this->app->dbAdapter);
        $mdlCustomer->saveUpdate(array('RealCallResult' => $realCallResultCode, 'RealCallScore' => $realCallPoint), $orderCustomer['CustomerId']);

        // T_Orderへ書き込み
        $mdlOrder = new TableOrder($this->app->dbAdapter);
        $mdlOrder->saveUpdate(array('Incre_ScoreTotal' => $result['ScoreTotal']), $orderSeq);

        return \Zend\Json\Json::encode($result);
	}

	/**
	 * メール送信チェック結果に関する与信ポイントの取得。
	 *
	 * @param $orderSeq 注文Seq
	 * @param $realSendMailResultCode メール送信結果コード
	 * @return string json文字列
	 */
	public function getCreditPointForRealSendMail($orderSeq, $realSendMailResultCode)
	{
        $mdloc = new ViewOrderCustomer($this->app->dbAdapter);
        $mdlgp = new TableGeneralPurpose($this->app->dbAdapter);
        $mdlcc = new TableCreditPoint($this->app->dbAdapter);

        $orderCustomer = $mdloc->find($orderSeq)->current();

        if ($realSendMailResultCode > 0)
        {
            $cpid = $mdlgp->getMasterAssCode(8, $realSendMailResultCode);
            $realSendMailPoint = $mdlcc->findCreditPoint($cpid)->current()['Point'];
        }
        else
        {
            $realSendMailPoint = 0;
        }

        $basePoint = $orderCustomer['Incre_ScoreTotal'] - $orderCustomer['RealSendMailScore'];

        $result['ScoreTotal'] = $basePoint + $realSendMailPoint;
        $result['RealSendMailScore'] = $realSendMailPoint;

        // T_Customerへ書き込み
        $mdlCustomer = new TableCustomer($this->app->dbAdapter);
        $mdlCustomer->saveUpdate(array('RealSendMailResult' => $realSendMailResultCode, 'RealSendMailScore' => $realSendMailPoint), $orderCustomer['CustomerId']);

        // T_Orderへ書き込み
        $mdlOrder = new TableOrder($this->app->dbAdapter);
        $mdlOrder->saveUpdate(array('Incre_ScoreTotal' => $result['ScoreTotal']), $orderSeq);

        return \Zend\Json\Json::encode($result);
	}

	/**
	 * 類似住所/電話番号検索
	 * 08.7.11 受け取りパラメータと検索ロジックを刷新
	 * 09.6.26 必須パラメータ変更、検索ロジック刷新
	 * 13.1.24 請求先電話番号を追加
	 */
	public function chkaddressAction() {

        $req = $this->getRequest();

        $params = $this->getParams();

        $oseq = isset($params['oseq']) ? $params['oseq'] : false;

        try {
            // 注文シーケンス必須
            if( $oseq === false ) throw new \Exception('シーケンスが指定されていません。');

            // 注文情報取得
            $order = $this->getOrderFromOrderSearch($oseq);
            if( $order == null ) throw new \Exception('指定の注文情報が見つかりません。');

            // 郵便番号抽出
            $pcode = CoralValidateUtility::fixPostalCode($order['PostalCode']);
            $pcode2 = CoralValidateUtility::fixPostalCode($order['DestPostalCode']);

            // 電話番号抽出、あわせて正規化
            $phone = LogicNormalizer::create(LogicNormalizer::FILTER_FOR_TEL)->normalize($order['Phone']);
            $phone2 = LogicNormalizer::create(LogicNormalizer::FILTER_FOR_TEL)->normalize($order['DestPhone']);

            // 郵便番号もしくは電話番号が設定の場合は例外
            if( empty($pcode) && empty($pcode2) && empty($phone) && empty($phone2)) throw new \Exception('郵便番号及び電話番号が設定されていません。');

            // ----------------------------------------------------------------------------------------- Phase7 #12 test code
            // 郵便番号と電話番号が出そろったので新アルゴリズムに処理を移譲
            $this->checkAddressInternal($oseq, $pcode, $pcode2, $phone, $phone2);
            return $this->response;
            // ----------------------------------------------------------------------------------------- Phase7 #12 test code
        }
        catch (\Exception $e) {
            $resultHtml = '例外発生<br />Where=' . $where . ', Err=';
            $resultHtml .= $e->getMessage();
        }

        echo $resultHtml;
	}

	protected function getOrderFromOrderSearch($seq) {

        if( $seq !== 0 && $seq !== '0' && empty($seq) ) return null;

        $sql=<<<EOQ
SELECT
    O.OrderSeq AS OrderSeq,
    O.ReceiptOrderDate AS ReceiptOrderDate,
    O.DataStatus AS DataStatus,
    O.EnterpriseId AS EnterpriseId,
    O.SiteId AS SiteId,
    O.OrderId AS OrderId,
    O.Ent_OrderId AS Ent_OrderId,
    O.Ent_Note AS Ent_Note,
    O.UseAmount AS UseAmount,
    O.RegistDate AS RegistDate,
    IFNULL(O.OutOfAmends, 0) AS OutOfAmends,
    (CASE
        WHEN O.Incre_Status = 1 THEN 1
        WHEN O.Incre_Status = - 1 THEN - 1
        ELSE 0
    END) AS IncreStatus,
    S.CarriageFee AS CarriageFee,
    S.ChargeFee AS ChargeFee,
    O.Chg_ExecDate AS Chg_ExecDate,
    O.Cnl_CantCancelFlg AS Cnl_CantCancelFlg,
    O.Cnl_Status AS Cnl_Status,
    O.AnotherDeliFlg AS AnotherDeliFlg,
    O.CombinedClaimTargetStatus AS CombinedClaimTargetStatus,
    O.P_OrderSeq,
    O.CombinedClaimParentFlg AS CombinedClaimParentFlg,
    O.ClaimSendingClass AS ClaimSendingClass,
    O.ServiceExpectedDate AS ServiceExpectedDate,
    C.CustomerId AS CustomerId,
    C.NameKj AS NameKj,
    C.NameKn AS NameKn,
    C.PostalCode AS PostalCode,
    C.UnitingAddress AS UnitingAddress,
    C.Phone AS Phone,
    C.MailAddress AS MailAddress,
    C.EntCustId AS EntCustId,
    S.DestNameKj AS DestNameKj,
    S.DestNameKn AS DestNameKn,
    S.DestPostalCode AS DestPostalCode,
    S.DestUnitingAddress AS DestUnitingAddress,
    S.DestPhone AS DestPhone,
    S.OrderItemId AS OrderItemId,
    S.OrderItemNames AS OrderItemNames,
    S.ItemNameKj AS ItemNameKj,
    S.ItemCount AS ItemCount,
    S.Deli_JournalIncDate AS Deli_JournalIncDate,
    S.Deli_DeliveryMethod AS Deli_DeliveryMethod,
    S.Deli_DeliveryMethodName AS Deli_DeliveryMethodName,
    S.Deli_JournalNumber AS Deli_JournalNumber,
    L.CancelDate AS CancelDate,
    L.CancelReason AS CancelReason,
    L.ApprovalDate AS ApprovalDate,
    L.CancelReasonCode AS CancelReasonCode,
    P.ExecScheduleDate AS ExecScheduleDate,
    CL.ClaimDate AS ClaimDate,
    (CASE
        WHEN ISNULL(O.Cnl_ReturnSaikenCancelFlg) THEN 0
        ELSE O.Cnl_ReturnSaikenCancelFlg
    END) AS Cnl_ReturnSaikenCancelFlg,
    (CASE
        WHEN (O.Cnl_Status = 0) THEN 0
        WHEN
            ((O.Cnl_Status = 1)
                AND (IFNULL(O.Cnl_ReturnSaikenCancelFlg, 0) = 0))
        THEN
            1
        WHEN
            ((O.Cnl_Status = 2)
                AND (IFNULL(O.Cnl_ReturnSaikenCancelFlg, 0) = 0))
        THEN
            2
        WHEN
            ((O.Cnl_Status = 1)
                AND (O.Cnl_ReturnSaikenCancelFlg = 1))
        THEN
            11
        WHEN
            ((O.Cnl_Status = 2)
                AND (O.Cnl_ReturnSaikenCancelFlg = 1))
        THEN
            12
    END) AS RealCancelStatus,
    (CASE
        WHEN
            (SELECT MAX(AlertSign) FROM T_StagnationAlert WHERE AlertClass = 0 AND OrderSeq = O.OrderSeq)
        THEN
            1
        ELSE 0
    END) AS Deli_JournalNumberAlert,
    (CASE
        WHEN
            (SELECT MAX(AlertSign) FROM T_StagnationAlert WHERE AlertClass = 1 AND OrderSeq = O.OrderSeq)
        THEN
            1
        ELSE 0
    END) AS ArrivalConfirmAlert

FROM
    T_Order O
    INNER JOIN T_Customer C
            ON C.OrderSeq = O.OrderSeq
    INNER JOIN T_OrderSummary S
            ON S.OrderSeq = O.OrderSeq
    LEFT  JOIN T_Cancel L
            ON L.OrderSeq = O.OrderSeq
           AND L.ValidFlg = 1
    LEFT  JOIN T_PayingControl P
            ON P.Seq = O.Chg_Seq
    LEFT  JOIN T_ClaimControl CL
            ON CL.OrderSeq = O.P_OrderSeq
WHERE O.OrderSeq = :OrderSeq
EOQ;
        $stm = $this->app->dbAdapter->query($sql);

        return $stm->execute(array(':OrderSeq' => $seq))->current();
	}

	/**
	 * chkaddressAction の高速版。T_OrderSummaryが拡張されていることが必要
	 *
	 * @param int $oseq 検索の起点となる注文のSeq
	 * @param string $pcode 郵便番号1
	 * @param string $pcode2 郵便番号2
	 * @param string $phone 電話番号
	 * @param string $phone2 電話番号2
	 */
	protected function checkAddressInternal($oseq, $pcode, $pcode2, $phone, $phone2) {

        try {
            $params = $this->getParams();

            // 注文シーケンス必須
            if( $oseq === false ) throw new \Exception('シーケンスが指定されていません。');

            // 注文情報取得
            $order = $this->getOrderFromOrderSearch($oseq);
            if( !$order ) throw new Exception('指定の注文情報が見つかりません。');

            // 町域一致向けの部分住所を生成
            $search_addresses = array();
            foreach(array('UnitingAddress', 'DestUnitingAddress') as $key) {
                $source = $order[$key];
                preg_match('/^[^\d]+\d+/', $source, $matches);
                $search_addresses[$key] = $matches[0];
            }

            // SELECTクエリの組み立て
            $sql =<<< EOQ
SELECT s.*
,      o.OrderId
,      o.ReceiptOrderDate
,      o.UseAmount
,      o.DataStatus
,      o.CloseReason
,      cc.F_LimitDate
,      o.Rct_Status
,      rc.ReceiptDate
,      o.Cnl_Status
,      o.Cnl_ReturnSaikenCancelFlg
,      c.CustomerId
,      vcr.ReceiptDate AS CloseReceiptDate
FROM   T_OrderSummary s
       INNER JOIN T_Order o ON (o.OrderSeq = s.OrderSeq)
       INNER JOIN T_Customer c ON (c.OrderSeq = s.OrderSeq)
       LEFT OUTER JOIN T_ClaimControl cc ON (cc.OrderSeq = o.P_OrderSeq)
       LEFT OUTER JOIN T_ReceiptControl rc ON (rc.ReceiptSeq = cc.LastReceiptSeq)
       LEFT OUTER JOIN V_CloseReceiptControl vcr ON (vcr.OrderSeq = cc.OrderSeq)
WHERE  1 = 1
AND    (o.Rct_Status = 1 AND cc.F_LimitDate >= vcr.ReceiptDate AND o.RegistDate < :registdate ) = 0
AND    (
            (s.PostalCode = :pcode  OR s.DestPostalCode = :pcode )
         OR (s.PostalCode = :pcode2 OR s.DestPostalCode = :pcode2)
         OR (s.RegPhone = :phone )
         OR (s.RegPhone = :phone2)
       )
ORDER BY o.OrderSeq DESC
EOQ;

            $stm = $this->app->dbAdapter->query($sql);

            $prm = array(
                    ':pcode'  => $pcode,
                    ':pcode2' => $pcode2,
                    ':phone'  => $phone,
                    ':phone2' => $phone2,
                    ':registdate' => date('Y-m-d',strtotime('-2 year')),// 期限内支払済及び2年以前は表示しない(条件指定の最適化（≒悪あがき） 2013.1.30 eda)
            );

            $ri = $stm->execute($prm);

            $rs = new ResultSet();
            $rs->initialize($ri);
            $datas = $rs->toArray();
            // count関数対策
            if (!empty($datas)) {
                // 検索に使用された郵便番号の情報を付加する
                $pcodes = ( empty($pcode2) || $pcode == $pcode2 ) ? array( $pcode ) :
                ( empty($pcode) ? array($pcode2) : array( $pcode, $pcode2 ) );

                // 検索に使用された電話番号の情報を付加する
                $phones = ( empty($phone2) || $phone == $phone2 ) ? array( $order['Phone'] ) :
                ( empty($phone) ? array($order['DestPhone']) : array( $order['Phone'], $order['DestPhone'] ) );

                $resultHtml = '
                    類似住所 (' . join( ', ' , $pcodes ) . ')/一致電話番号 (' . join( ', ' , $phones ) . ')：計 ${TOTAL_RESULTS_COUNT} 件' . '
                    <table style="width: 862px;" class="ddtable" cellpadding="1" cellspacing="1" border="0">
                      <tr>
                        <th>注文ID</th>
                        <th>注文日</th>
                        <th>名前</th>
                        <th>住所</th>
                        <th>電話番号</th>
                        <th>利用額</th>
                        <th>遅れ</th>
                      </tr>
                    ';

                $count = 0; // 出力件数合計

                // ステータスとツールチップテキストのマップ配列
                $class_captions = CoralOrderUtility::getStatusCaptions();

                foreach ($datas as $data) {
                    // OrderSeqが検索元と一致する行は無視
                    if($data['OrderSeq'] == $oseq) continue;

                    // ステータスによって行のCSSクラスを変更する 08.03.04 追加 by eda → Coral_OrderUtilityに判断を委譲
                    $orderInfo = CoralOrderUtility::getOrderInfo($data);
                    $class = $orderInfo['caption'];

                    // クラス'receipted_normal'（＝期限内支払済）は～丁目まで一致した場合と*電話番号*が一致した場合に加える(2013.01.24)
                    $target_class = 'receipted_normal';
                    if( $class == $target_class ) {
                        $matched = false;

                        // 取得した正規化請求先電話番号と対象注文の正規化請求先電話番号もしくは配送先電話番号が一致した場合は加える
                        if(in_array($data['RegPhone'], array($phone, $phone2))) {
                            $matched = true;
                        }

                        foreach($search_addresses as $key => $search_address) {
                            if(
                            strpos($data['UnitingAddress'], $search_address) === 0 ||
                            strpos($data['DestUnitingAddress'], $search_address) === 0 ) {
                                // 請求先か配送先が～丁目まで一致していたらOK
                                $matched = true;
                            }

                        }
                        // 一致がないので結果に追加せず次のデータへ
                        if( ! $matched ) continue;
                    }

                    // 一致データの状況毎にアイコンを付記する
                    $addr_classes = array( 'l_data' );
                    $addrtitle = '';
                    // 既存データの請求先住所が完全一致する場合は緑のホームアイコンを付加
                    if ($order['UnitingAddress'] == $data['UnitingAddress']) {
                        $addr_classes[] = 'full_address_match';
                        $addrtitle = '請求先住所が一致';
                    }
                    // 既存データの配送先住所が、請求先住所に一致する場合は緑のホームアイコンを付加
                    else if ($order['UnitingAddress'] == $data['DestUnitingAddress']) {
                        $addr_classes[] = 'full_address_match';
                        $addrtitle = '請求先住所が一致';
                    }
                    // 既存データの請求先住所が、配送先住所に一致する場合は橙のホームアイコンを付加
                     else if ($order['DestUnitingAddress'] == $data['UnitingAddress']) {
                         $addr_classes[] = 'full_dest_address_match';
                         $addrtitle = '配送先住所が一致';
                     }
                    // 既存データの配送先住所が完全一致する場合は橙のホームアイコンを付加
                     else if ($order['DestUnitingAddress'] == $data['DestUnitingAddress']) {
                         $addr_classes[] = 'full_dest_address_match';
                         $addrtitle = '配送先住所が一致';
                     }
//                     else if(
//                     // 既存データの請求先が一致する場合は青のホームアイコンを付加
//                     in_array( $data['PostalCode'], $pcodes )
//                     )
//                     {
//                         $addr_classes[] = 'address_match';
//                         $addrtitle = '請求先住所が一致';
//                     }
//                     else if(
//                     // 既存データの配送先が一致し且つ請求先が一致しない場合は赤のホームアイコンを付加
//                     in_array( $data['DestPostalCode'], $pcodes ) && ! in_array( $data['PostalCode'], $pcodes )
//                     ) {
//                         $addr_classes[] = 'dest_address_match';
//                         $addrtitle = '配送先住所が一致';
//                     }

                    // 検索に使用された電話番号の情報を付加する
                    $phones2 = ( empty($phone2) || $phone == $phone2 ) ? array( $phone ) :
                    ( empty($phone) ? array($phone2) : array( $phone, $phone2 ) );

                    $phone_classes = array( 'l_data' );
                    $phonetitle = '';

                    if(
                    // 検索された電話番号が結果の請求先電話番号にあった場合はアイコンを付加
                    in_array( $data['RegPhone'], $phones2) || in_array( $data['RegDestPhone'], $phones2)
                    ) {
                        $phone_classes[] = 'phone_match';
                        $phonetitle = '請求先電話番号が一致';
                    }

                    $caption = $class_captions[ $class ];
                    $resultHtml .= "<tr class='chkaddr_results orderstatus_$class' title='$caption'>" .
                    sprintf('<td class="l_data"><a target="_blank" href="rworder/detail/oseq/%d">%s</a></td>', $data['OrderSeq'], $data['OrderId']) .
                    sprintf('<td class="l_data">%s</td>', $data['ReceiptOrderDate']) .
                    sprintf('<td class="l_data"><a target="_blank" href="customerinquiry/detail/custid/%d">%s</a></td>', $data['CustomerId'], $data['NameKj']) .
                    sprintf('<td class="%s" title="%s">%s</td>', join(' ', $addr_classes), $addrtitle, $data['UnitingAddress']) .
                    sprintf('<td class="%s" title="%s">%s</td>', join(' ', $phone_classes), $phonetitle, $data['Phone']) .
                    sprintf('<td class="r_data">%s</td>', f_nf($data['UseAmount'], '#,##0')) .
                    sprintf('<td class="r_data">%s</td>', f_e($orderInfo['delaydate'])) .
                    '</tr>';
                    $count++;
                }

                $resultHtml .= '</table>';
                // 出力件数を確定して返却内容確定
                if( $count > 0 ) {
                    $resultHtml = preg_replace('/\$\{TOTAL_RESULTS_COUNT\}/', $count, $resultHtml);
                } else {
                    $resultHtml = '<span style="color: #ff6666;">類似する住所及び一致する電話番号の注文はありませんでした。</span>';
                }
            } else {
                $resultHtml = '<span style="color: #ff6666;">類似する住所及び一致する電話番号の注文はありませんでした。</span>';
            }
        }
        catch (\Exception $e) {
            $resultHtml = '例外発生<br />Where=' . $where . ', Err=';
            $resultHtml .= $e->getMessage();
        }

        echo $resultHtml;
	}

	/**
	 * checkAddressInternal()内のメインクエリの行に対して不足するT_Orderの列を追加する
	 * （現状不要）
	 *
	 * @param array $data 類似住所ベース検索の結果行
	 * @return array $dataに不足するT_Orderの情報をマージした連想配列
	 */
	protected function mergeOrderForChkAddress($data) {

        $orders = new TableOrder($this->app->dbAdapter);

        $row = $orders->find($data['OrderSeq'])->current();

        return $row ? array_merge($data, $row) : $data;
	}

    /**
     * 郵便番号を検索する。
     */
	public function searchzipAction() {
        try
        {
            $query = " SELECT MPOS.*, MPRE.PrefectureCode FROM M_PostalCode MPOS, M_Prefecture MPRE WHERE MPOS.PrefectureKanji = MPRE.PrefectureName AND MPOS.PostalCode7 = :PostalCode7 ";
            $stm = $this->app->dbAdapter->query($query);

            $postalCode7 = mb_ereg_replace("[^0-9０-９]", "", $_GET["zc"]);
            $postalCode7 = mb_convert_kana($postalCode7, "n", "UTF-8");

            $prm = array(
               ':PostalCode7' => $postalCode7,
            );

            $msg = $stm->execute($prm)->current();
            if (!$msg) {
                $msg['PrefectureCode'] = '';
                $msg['CityKanji'] = '';
                $msg['TownKanji'] = '';
            }
            else {
                echo \Zend\Json\Json::encode($msg);
            }
        }
        catch(\Exception $e)
        {
            $msg['PrefectureCode'] = '';
            $msg['CityKanji'] = '';
            $msg['TownKanji'] = '';
        }

        return $this->response;
    }
    
	/**
	 * CB_B2C_DEV-14
	 * 入力された収納代行会社IDと加入者固有コードを元に加入者固有コード管理マスタ検索を実施
	 * 存在した場合、T_Site.ReceiptAgentId=収納代行会社ID、T_Site.SubscriberCode=加入者固有コードの件数を取得して、加入者固有名称と件数を画面上に表示
	 */
	public function searchsubscriberAction() {
		try
		{
			//
			$resData = array();
			$query = " SELECT SubscriberName,LinePayUseFlg,LineApplyDate,LineUseStartDate,RakutenBankUseFlg,FamiPayUseFlg FROM M_SubscriberCode WHERE ValidFlg= 1 AND ReceiptAgentId = :ReceiptAgentId AND SubscriberCode = :SubscriberCode ";
			$stm = $this->app->dbAdapter->query($query);

			$prm = array(
					':ReceiptAgentId' => $_GET["ra"],
					':SubscriberCode' => $_GET["sc"],
			);

			$tmp = $stm->execute($prm)->current();
			if (!$tmp) {
				$resData['SubscriberName'] = '対象データが存在しませんでした。';
				$resData['SiteCnt'] = '-';
			}else{
				//
				$resData['SubscriberName'] = $tmp['SubscriberName'];
                $resData['LinePayUseFlg'] = $tmp['LinePayUseFlg'];
                $resData['LineApplyDate'] = $tmp['LineApplyDate'];
                $resData['LineUseStartDate'] = $tmp['LineUseStartDate'];
                $resData['RakutenBankUseFlg'] = $tmp['RakutenBankUseFlg'];
                $resData['FamiPayUseFlg'] = $tmp['FamiPayUseFlg'];
				// 何件データが存在しているか確認
				$query = " SELECT count(*) as siteCnt FROM T_Site WHERE ReceiptAgentId = :ReceiptAgentId AND SubscriberCode = :SubscriberCode ";
				$stm = $this->app->dbAdapter->query($query);
				$resData['SiteCnt'] = $stm->execute($prm)->current()['siteCnt'];
			}

			echo \Zend\Json\Json::encode($resData);

		}
		catch(\Exception $e)
		{
			$resData['SubscriberName'] = '対象データが存在しませんでした。';
			$resData['SiteCnt'] = '-';
			echo \Zend\Json\Json::encode($resData);
		}

		return $this->response;
	}
// Del By Takemasa(NDC) 20151226 Stt 関数checkAddressInternalより呼出さない方式実装より
// 	/**
// 	 * 引数に対して全てOrで連結してwhere句を組み立てる
// 	 * @param $conditionArray array
// 	 * @return String
// 	 */
// 	protected function addWhereQuery($conditionArray) {
// 	    $where = "";
// 		$or = "";
//
// 		foreach ($conditionArray as $condition)
// 		{
// 			foreach ($condition as $key => $value)
// 			{
// 				if($key == "s.RegPhone" && is_numeric($value)) {
// 					$where .= sprintf("$or $key = '%s' ", $value);
// 				}
// 				else {
// 					$where .= $this->app->dbAdapter->quoteInto("$or $key = ? ", $value);
// 				}
// 				$or = "OR";
// 			}
// 		}
// 		return $where;
// 	}
// Del By Takemasa(NDC) 20151226 End 関数checkAddressInternalより呼出さない方式実装より

    /**
     * OKチケット発行処理を行う
     */
    public function registokticketAction()
    {
        $mdlCot = new TableCreditOkTicket($this->app->dbAdapter);
        $mdlOrder = new TableOrder($this->app->dbAdapter);

        try
        {
            $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();

            $params = $this->getParams();
            $oseq = isset($params['oseq']) ? $params['oseq'] : 0;
            $row = $mdlOrder->find($oseq)->current();

            $data = array(
                'Status' => '0',  // 発行中
                'EnterpriseId' => $row['EnterpriseId'],
                'OrderSeq' => $oseq,
                'RegistDate' => date('Y-m-d H:i:s'),
                'RegistOpId' => $this->app->authManagerAdmin->getUserInfo()->OpId,
                'ValidToDate' => date('Y-m-d H:i:s', strtotime("+ 12 hour")),
                'ReleaseDate' => null,
                'ReleaseOpId' => null,
                'UseOrderSeq' => null,
                'UseDate' => null,
            );
            $mdlCot->saveNew($data);

            // 成功指示
            $msg = '1';

            $this->app->dbAdapter->getDriver()->getConnection()->commit();
        }
        catch(\Exception $e)
        {
            $this->app->dbAdapter->getDriver()->getConnection()->rollback();
            $msg = $e->getMessage();
        }

        echo \Zend\Json\Json::encode(array('status' => $msg));
        return $this->response;
    }

    /**
     * OKチケット削除処理を行う
     */
    public function deleteokticketAction()
    {
        $mdlCot = new TableCreditOkTicket($this->app->dbAdapter);

        try
        {
            $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();

            $params = $this->getParams();
            $oseq = isset($params['oseq']) ? $params['oseq'] : 0;

            // 発行中のチケットデータを取得する
            $sql = ' SELECT * FROM T_CreditOkTicket WHERE OrderSeq = :OrderSeq AND Status = 0 ORDER BY Seq DESC LIMIT 1 ';
            $prm = array(
                ':OrderSeq' => $oseq,
            );
            $row = $this->app->dbAdapter->query($sql)->execute($prm)->current();

            if (!$row) {
                // データが取れなかった場合は終了
                throw new \Exception('既にチケットが使用されています。');
            }

            $data = array(
                    'Status' => '9',  // キャンセル
                    'ReleaseDate' => date('Y-m-d H:i:s'),
                    'ReleaseOpId' => $this->app->authManagerAdmin->getUserInfo()->OpId,
            );
            $mdlCot->saveUpdate($data, $row['Seq']);

            // 成功指示
            $msg = '1';

            $this->app->dbAdapter->getDriver()->getConnection()->commit();
        }
        catch(\Exception $e)
        {
            $this->app->dbAdapter->getDriver()->getConnection()->rollback();
            $msg = $e->getMessage();
        }

        echo \Zend\Json\Json::encode(array('status' => $msg));
        return $this->response;
    }
}

