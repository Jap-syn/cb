<?php
namespace api\classes\Service;

use api\Application;
use api\classes\Service\ServiceAbstract;
use api\classes\Service\ServiceException;
use api\classes\Service\Response\ServiceResponseDetail;
use api\classes\Service\Detail\ServiceDetailConst;
use Zend\Db\ResultSet\ResultSet;
use models\Table\TableEnterprise;

/**
 * 注文状況取得サービスクラス
 */
class ServiceDetail extends ServiceAbstract {
	/**
	 * 検証対象の入力パラメータとラベルのペアを格納したスキーマを取得する
	 *
	 * @static
	 * @access protected
	 * @return array
	 */
	protected static function __getParamsSchema() {
		return array(
			array('key' => ServiceDetailConst::ORDER_ID, 'label' => '注文ID'),
			array('key' => ServiceDetailConst::ENT_ORDER_ID, 'label' => '任意注文番号')
		);
	}

	/**
	 * 伝票番号登録APIのサービスID
	 * @var string
	 */
	protected $_serviceId = "09";

    /**
     * 事業者ID
     *
     * @var array
     */
    protected $_entrpriseId = 0;

	/**
	 * 注文ID
	 *
	 * @var array
	 */
    protected $_orderIds = array();

    /**
     * 任意注文番号
     *
     * @var array
     */
    protected $_entOrderIds = array();

	/**
	 * 初期化処理
	 *
	 * @access protected
	 */
	protected function init() {

        // サイトIDチェックは行わない
        $this->_checkSiteId = false;

		// レスポンスを初期化
        $this->_response = new ServiceResponseDetail();

		// 認証用
        $this->_apiUserId = $this->_data[ServiceDetailConst::API_USER_ID];
        $this->_enterpriseId = $this->_data[ServiceDetailConst::ENTERPRISE_ID];

		// 登録向けデータ
        $this->_orderIds = $this->_data[ServiceDetailConst::ORDER_ID];
        $this->_entOrderIds = $this->_data[ServiceDetailConst::ENT_ORDER_ID];
		// 登録データをレスポンスへ反映
		$this->_response->_orderIdList = $this->_orderIds;
		$this->_response->_entOrderIdList = $this->_entOrderIds;

		// ログ出力
		Application::getInstance()->logger->info(
			get_class($this) . '#init() ' .
			join(', ', array(
				sprintf('%s: %s', ServiceDetailConst::ENTERPRISE_ID, $this->_enterpriseId),
				sprintf('%s: %s', ServiceDetailConst::API_USER_ID, $this->_apiUserId),
				sprintf('RemoteAddr: %s', f_get_client_address())       // 2015/09/23 Y.Suzuki Mod f_get_client_address をｺｰﾙするように変更
			)) );
	}

	/**
	 * 入力に対する検証を行う
	 *
	 * @access protected
	 * @return boolean 検証結果
	 */
	protected function check() {

		$props = array(
			array('key' => ServiceDetailConst::ORDER_ID, 'label' => '注文ID'),
			array('key' => ServiceDetailConst::ENT_ORDER_ID, 'label' => '任意注文番号')
		);
        $orderFlg = 1;
        try {
            // 注文ID、任意注文番号のnull/ブランクチェック
            // OrderId指定が0件 EntOrderIdも0件の場合は問い合わせるまでもなくエラー
            if(!is_array($this->_orderIds) || empty($this->_orderIds)) {
                $orderFlg = 0;

            }
			if(!is_array($this->_entOrderIds) || empty($this->_entOrderIds) && $orderFlg == 0) {
                $this->_response->addMessage(sprintf('E%s201', $this->_serviceId), '注文IDもしくは任意注文番号の指定は必須です');
                return $result;
            }

        } catch(ServiceException $svcErr) {
			// 検証エラー
			$this->_response->addMessage(
				$svcErr->getFormattedErrorCode(),
				$svcErr->getMessage() );
        } catch(\Exception $err) {
			// その他のエラー
			Application::getInstance()->logger->info(sprintf('%s#check() ERROR: %s', get_class($this), $err->getMessage()));
			$this->errorHandle($err);
        }

        return !empty($this->_response->messages) ? false : true;
	}

	/**
	 * サービスを実行する
	 *
	 * @access protected
	 * @return boolean サービス実行結果
	 */
	protected function exec() {

        $db = $this->_db;
        $ent = $this->getEnterpriseData();

        $orderMap = array();

        //任意注文番号をOrderIdに変換s
        $this->mergeEntOrderId();

        // 対象の注文データを取得
        $datas = $this->getOrderStatus();

        foreach($datas as $orderRow) {
            $orderMap[$orderRow[ServiceResponseDetail::RESULT_KEY_ORDER_ID]] = $orderRow;
        }

        $rowCount = 0;

        foreach($this->_orderIds as $orderId) {
            if(isset($orderMap[$orderId])) $rowCount++;
            $this->_response->addResult($orderId, isset($orderMap[$orderId]) ? $orderMap[$orderId] : null, $ent['ForceCancelClaimPattern'], $this->getCancelNoticePrintDate($orderId)['CancelNoticePrintDate']);
            $entOrderIds[] = isset($orderMap[$orderId]['entOrderId']) ? $orderMap[$orderId]['entOrderId'] : null;
        }

        //任意注文番号⇒注文番号に変換できなかったデータ保管
        foreach($this->_entOrderIds as $entOrderId){
            if(array_search($entOrderId,$entOrderIds) === false){
                $entOrderDatas['Ent_OrderId'] = $entOrderId;
                $this->_response->addResult("",$entOrderDatas, $ent['ForceCancelClaimPattern'], null);
            }
        }

        if($rowCount) {
            // 1件でも要求OrderIdに一致するデータがあればOK
            $result = true;
        } else {
            // 有効OrderIdの指定なし
            $result = false;
            $this->_response->addMessage(sprintf('E%s202', $this->_serviceId), '有効な注文IDが指定されていません');
        }

        return $result;

	}

	/**
	 * 処理結果を文字列として返却する
	 *
	 * @access protected
	 * @return string 処理結果
	 */
	protected function returnResponse() {
		return $this->_response->serialize();
	}

	/**
	 * 入力パラメータの必須検証を行う。
	 * 検証エラーが発生した場合は{@link ServiceException}がスローされる
	 *
	 * @access protected
	 * @return bool
	 */
	protected function checkRequireParams(array $data) {
		$schema = self::__getParamsSchema();
		foreach($schema as $param) {
			$val = trim((string)$data[$param['key']]);
			if(!strlen($val)) {
				throw new ServiceException(
					sprintf('%s : データを0または空にすることはできません', $param['label']), $this->_serviceId, '201' );
			}
		}
		return true;
	}

    /**
     * 指定の注文状況取得
     *
     * @return orderStatus 対象の注文情報
     */
    protected function getOrderStatus() {

        $wheres = array();

        //注文番号が存在しない場合は空を返す
        if( empty($this->_orderIds) ){
            return array();
        }

        /*注文ID、任意注文番号、注文受日、注文登録日時、処理ステータスコード、処理ステータス名
          利用額、購入サイトID、購入サイト名、購入サイトURL、キャンセル状況、キャンセル日
          キャンセル理由、伝票番号登録状況、伝票番号登録日、配送会社ID、配送会社名
          伝票番号、請求書発行状況、請求日、請求フォーマット、請求期限、もうすぐお支払メール送信日時
          支払期限経過メール送信日、着荷確認状況、着荷確認日時
          入金確認状況、入金確認日、立替支払状況、立替日*/

        $q = <<<EOQ
SELECT
    o.OrderId as orderId,
    o.Ent_OrderId as entOrderId,
    o.ReceiptOrderDate as orderDate,
    o.RegistDate as orderDateTime,
    case
        when o.DataStatus = 91 && CloseReason = 3 && o.Incre_Status = -1 then 99
        when c.ApproveFlg = 0 and c.ValidFlg = 1 then 10
        when c.ApproveFlg = 1 then 11
        when o.DataStatus in(11,12,15,21,25) then 0
        when o.DataStatus = 31 then 1
        when o.DataStatus = 41 then 2
        when o.DataStatus = 51 then 3
        when o.DataStatus = 61 and e.DetailApiOrderStatusClass = 0 then 3
        when o.DataStatus = 61 and e.DetailApiOrderStatusClass = 1 then 4
        when o.DataStatus = 91 then 9
        else -1
    end as orderStatus,
    o.UseAmount as payment,
    s.SiteId as siteId,
    s.SiteNameKj as siteName,
    s.Url as siteUrl,
    case
        when c.CancelDate IS NULL then 0
        when c.CancelDate IS NOT NULL then 1
    end as isCanceled,c.CancelDate as cancelDate,
    c.CancelReason as cancelReason,
    case
        when os.Deli_JournalIncDate IS NULL then 0
        when os.Deli_JournalIncDate IS NOT NULL then 1
    end as isShipped,
    os.Deli_JournalIncDate as shippingDate,
    os.Deli_DeliveryMethod as deliveryId,
    os.Deli_DeliveryMethodName as deliveryName,
    os.Deli_JournalNumber as journalNumber,
    case
        when cc.ClaimDate IS NULL then 0
        when cc.ClaimDate IS NOT NULL then 1
    end as isClaimed,
    cc.F_ClaimDate as claimDate,
    case
		when s.FirstClaimLayoutMode = 1 and o.ClaimSendingClass = 11 then 2
        else
			case
				when o.ClaimSendingClass = 12 or o.ClaimSendingClass = 21 then 0
                when o.ClaimSendingClass = 11 then 1
                else null
			end
	end as claimFormat,
    cc.LimitDate as claimLimitDate,
    o.MailPaymentSoonDate as MailPaymentSoonDate,
    o.MailLimitPassageDate as MailLimitPassageDate,
    /* 2015.11.24 現行バグ：着荷確認済か否かの判断を修正
    case
        when o.Deli_ConfirmArrivalDate IS NULL then 0
        when o.Deli_ConfirmArrivalDate IS NOT NULL  then 1
    end as isArrivalConfirmed,
    */
    CASE WHEN o.Deli_ConfirmArrivalFlg = 1 THEN 1 ELSE 0 END AS isArrivalConfirmed,
    o.Deli_ConfirmArrivalFlg,
    o.Deli_ConfirmArrivalDate as arrivalConfirmedDate,
    case
        when rc.ReceiptDate IS NULL then 0
        when rc.ReceiptDate IS NOT NULL then 1
    end as isReceiptConfirmed,
    rc.ReceiptDate as receiptDate,
    case
        when o.Chg_ExecDate IS NULL  then 0
        when o.Chg_ExecDate IS NOT NULL  then 1
    end as isReimbursed,o.Chg_ExecDate as reimbursedDate
, ao.ExtraPayKey AS trackingId
, s.PaymentAfterArrivalFlg
FROM
    T_Order as o
    inner join T_Site as s on o.SiteId = s.SiteId
    inner join T_Enterprise e on o.EnterpriseId = e.EnterpriseId
    left join T_Cancel as c on o.OrderSeq = c.OrderSeq
    left join T_OrderSummary as os on o.OrderSeq = os.OrderSeq
    left join T_ClaimControl as cc on o.P_OrderSeq = cc.OrderSeq
    left join T_ReceiptControl as rc on o.P_OrderSeq = rc.OrderSeq
    INNER JOIN AT_Order AS ao ON ao.OrderSeq = o.OrderSeq
WHERE
    o.EnterpriseId = :enterpriseId
AND o.OrderId IN (%s)
EOQ;
        $orderIds = array();
        foreach ($this->_orderIds as $orderId)
            $orderIds[] = $this->_db->getPlatform()->quoteValue($orderId);

        $sql = sprintf($q, implode(', ', $orderIds));

        $datas = $this->_db->query($sql)->execute(array(
            ':enterpriseId' => $this->_enterpriseId
        ));

        return $datas;

    }
    /**
     * 任意注文番号を注文IDに変換し注文番号にマージする
     *
     * @return orderStatus 対象の注文情報
     */
    protected function mergeEntOrderId() {

        $entOrderDatas = array();

        if(!empty($this->_entOrderIds)){
            $entOrderDatas = $this->changeOrderData();
        }

        //パラメータの任意注文番号の注文IDとパラメータの注文IDマージ
        $mergeOrderId = array_merge($this->_orderIds,$entOrderDatas);

        //重複ID削除
        $mergeOrderId = array_unique($mergeOrderId);

        $this->_orderIds = $mergeOrderId;

    }
    /**
     * 任意注文番号から注文ID取得
     */
    protected function changeOrderData(){
        $entOrderDatas = array();
        /*****
        /// 検索で使用する任意注文番号を詰め替える（2015.4.14 eda）
		// → 指定の任意注文番号がis_numeric()でtrueを返すケースではcast()を通して明確に
		//   文字列として比較するため
		$search_ent_order_ids = array();
		foreach($this->_entOrderIds as $entOrderId) {
			$search_ent_order_ids[] = $this->_db->quoteInto(is_numeric($entOrderId) ? 'CAST(? AS CHAR)' : '?', $entOrderId);
		}
        $wheres = array(
            $this->_db->quoteInto('EnterpriseId = ?', $this->_enterpriseId),
            sprintf('Ent_OrderId IN (%s)', join(', ', $search_ent_order_ids)),
        );
        //$wheres = array(
        //    $this->_db->quoteInto('EnterpriseId = ?', $this->_enterpriseId),
        //    $this->_db->quoteInto('Ent_OrderId IN (?)', $this->_entOrderIds),
        //);

        $query = sprintf('SELECT OrderId FROM T_Order WHERE %s', join(' AND ', $wheres));
        *****/
        $search_ent_order_ids = array();
        foreach($this->_entOrderIds as $entOrderId) {
            $search_ent_order_ids[] = $this->_db->getPlatform()->quoteValue($entOrderId);
        }

        $query = ' SELECT OrderId FROM T_Order WHERE EnterpriseId = :EnterpriseId AND Ent_OrderId IN ( ' . implode(' ,', $search_ent_order_ids) . ' ) ';
        $wheres = array(
                ':EnterpriseId' =>  $this->_enterpriseId,
        );

        $stm =  $this->_db->query($query);
        $rs = new ResultSet();
        $rows = $rs->initialize($stm->execute($wheres))->toArray();

        foreach($rows as $orderData) {
            $entOrderDatas[] = $orderData['OrderId'];
        }

        return $entOrderDatas;
    }

    /**
     * 現在の要求対象の事業者データを取得する
     *
     * @access protected
     * @return array | null
     */
    protected function getEnterpriseData() {
        $tbl = new TableEnterprise($this->_db);
        return $tbl->find($this->_enterpriseId)->current();
    }

    /**
     * 強制解約通知出力日取得
     *
     * @param $orderId
     * @return mixed
     */
    protected function getCancelNoticePrintDate($orderId) {
        $q = <<<EOQ
SELECT c.CancelNoticePrintDate
  FROM T_Order o
 INNER JOIN T_ClaimControl c 
    ON o.OrderSeq = c.OrderSeq
 WHERE o.OrderId = :OrderId
EOQ;
        return $this->_db->query($q)->execute(array(':OrderId' => $orderId))->current();
    }
}
