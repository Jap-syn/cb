<?php
namespace oemmember\Controller;

use Zend\Config\Reader\Ini;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Coral\Base\IO\BaseIOCsvWriter;
use Coral\Coral\Controller\CoralControllerAction;
use Coral\Coral\CoralCodeMaster;
use oemmember\Application;
use models\Table\TableEnterprise;
use models\Table\TableSite;
use models\Logic\LogicCampaign;
use models\Logic\LogicTemplate;
use DOMPDFModule\View\Model\PdfModel;

class MonthlyController extends CoralControllerAction {
	const QUERY_GET_FIXED_MONTHS = 'GetFixedMonths';

	const QUERY_GET_SUMMARY = 'GetSummary';

	const QUERY_GET_CHARGEFEE_LIST = 'GetChargeFeeList';
	const QUERY_GET_CHARGEFEE_LIST2 = 'GetChargeFeeList2';

	const QUERY_GET_STAMPFEE_LIST = 'GetStampFeeeList';
	const QUERY_GET_STAMPFEE_LIST2 = 'GetStampFeeeList2';

	const QUERY_GET_CANCELFEE_LIST = 'GetCancelFeeList';
	const QUERY_GET_CANCELFEE_LIST2 = 'GetCancelFeeList2';

	const QUERY_CALC_SELF_BILLING_CLAIM_FEE = 'CalcSelfBillingClaimFee';

	const QUERY_GET_ADJUSTMENTFEE_LIST = 'GetAdjustmentFeeList';

	const QUERY_GET_PAYBACKFEE_LIST = 'GetPayBackFeeList';

	const RESULT_CSV_PREFIX = 'monthly_meisai';

	protected $_componentRoot = './application/views/components';

	/**
	 * @var Adapter
	 */
	private $dbAdapter;

	/**
	 * 必要なクエリテンプレート
	 *
	 * @var array
	 */
	private $queries;

	// コントローラ共通の初期化処理
	protected function _init() {
// 		$app = Application::getInstance();

// 		$app->addClass('CoralCodeMaster');
	    $this->app = $app = Application::getInstance();

        // メニュー情報をマスタから取得
        $menu_info = $this->app->getMenuInfo();

		foreach( $menu_info as $key => $info ) {
		    $this->view->assign( $key, $info );
		}
		$this->userInfo = $app->authManager->getUserInfo();

		$this->dbAdapter = $app->dbAdapter;

		$this->addStyleSheet( '../../oemmember/css/members.css' )
			->addStyleSheet( '../../css/base.ui.tableex.css' )
			->addStyleSheet( '../../oemmember/css/tab_support.css' )
			->addStyleSheet( '../../oemmember/css/claim.css' )
			->addStyleSheet( '../../oemmember/css/monthly.css' )
			->addJavaScript( '../../js/prototype.js' )
			->addJavaScript( '../../js/bytefx.js' )
			->addJavaScript( '../../js/corelib.js' )
			->addJavaScript( '../../js/base.ui.js' )
			->addJavaScript( '../../js/base.ui.tableex.js' )
			->addJavaScript( '../../js/sortable_ja.js' );

		// クエリテンプレート初期化
		$this->initQueryTemplates();

		Application::getInstance()->addClass('TableEnterprise');
		$entTable = new TableEnterprise($this->app->dbAdapter);
		$row = $entTable->findEnterprise( $this->userInfo->EnterpriseId )->current();
		if( $row ) {
			$this->view->assign( 'information_message', $row->Notice );
		}

		$this->view->assign('cssName', "monthly");
		return $this->view;
	}

	/**
	 * indexアクション
	 */
	public function indexAction() {

		$entId = $this->userInfo->EnterpriseId;

		$dateList = $this->getFixedMonthList( $entId );

		$params = array_merge(
			array(
				'enterprise' => $this->userInfo,			// 事業者情報
				'fixedMonth' => $dateList[0]['FixedMonth'],	// 指定月
				'dateList' => $dateList,					// 締め月のリスト
				'tab' => 0									// 選択中タブのインデックス
			),
			/*NetB_Controller_Utility::getPureParams( $this->getRequest() )*/
			$this->getPureParams()

		);
		$fixedMonth = $params['fixedMonth'];
		$this->setPageTitle( sprintf('ご利用明細（月次） (%s)', f_df($fixedMonth, 'Y年 n月')) );

		// サマリ取得
		$params['summary'] = $this->getSummary($entId, $fixedMonth)->current();

		// 取引明細
		$params['charge_list'] = $this->getChargeFeeData($entId, $fixedMonth);

		// 印紙代明細
		$params['stamp_list'] = $this->getStampFeeData($entId, $fixedMonth);

		// キャンセル明細
		$params['cancel_list'] = $this->getCancelFeeData($entId, $fixedMonth);

		// 同梱ツール関連の追加情報
		$params['self_billing_info'] = $this->calcSelfBillingClaimFee($entId, $fixedMonth);

		//調整額内訳明細//-------------------------------------------------------------
		$params['adjustment_list'] = $this->getAdjustmentFeeData($entId, $fixedMonth);

// 		//立替精算戻し//-------------------------------------------------------------
// 		$params['payback_list'] = $this->GetPayBackFeeList($entId, $fixedMonth);

		foreach( $params as $key => $value ) {
			$this->view->assign( $key, $value );
		}

		// 対象期間と請求書発行日
		$spanFrom = date($fixedMonth);

		$this->view->assign('spanFrom', $spanFrom);

		$spanTo = date('Y-m-d', strtotime($spanFrom. '+1 month -1 day') );
		$this->view->assign('spanTo', $spanTo);

		$issueDate = date('Y-m-d', strtotime($spanTo . '+1 day'));

		$this->view->assign('issueDate', $issueDate);

        // キャンペーン期間中はキャンペーン情報を表示する
        // 加盟店に紐づくサイトを取得
        $mdls = new TableSite($this->app->dbAdapter);
        $sid = $mdls->getValidAll($entId)->current()['SiteId'];
        // キャンペーン OR マスタの情報を取得
        $logic = new LogicCampaign($this->app->dbAdapter);
        $campaign = $logic->getCampaignInfo($entId, $sid);

		$codeMaster = new CoralCodeMaster($this->app->dbAdapter);
        $this->view->assign('planName', $codeMaster->getPlanCaption($campaign['Plan']));

        //届いてから決済を利用するサイトが紐付いているか
        $sql = "SELECT COUNT(1) AS cnt FROM T_Site WHERE EnterpriseId = :EnterpriseId AND PaymentAfterArrivalFlg = 1";
        $cnt = $this->app->dbAdapter->query($sql)->execute(array(':EnterpriseId' => $entId))->current()['cnt'];
        if(isset($cnt)){
            $this->view->assign( 'PaymentAfterArrivalFlg', 0 < $cnt ? 1 : 0);
        }

		return $this->view;
	}

	public function stampimageAction() {

	}

	/**
	 * 明細一覧ダウンロード
	 */
	public function downloadAction() {
	    // 立替締め月
	    $params = $this->getParams();
	    $fixedMonth = $params['fixedMonth'];

	    // ZIPファイル作成
	    $zip = new \ZipArchive();

	    // 出力ファイル名
	    $outFileName= 'MeisaiIchiran.zip';

	    // TEMP領域作成
	    $tmpFilePath = tempnam( sys_get_temp_dir(), 'tmp' );

	    // ZIPファイルオープン
	    $zip->open( $tmpFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE );

	    $unlinkList = array();

	    // 明細1作成(精算書)
	    $tmpFileName1 = $this->createMeisai1( $fixedMonth, $tmpFilePath );
	    $unlinkList[] = $tmpFileName1;
	    $fileName1 = str_replace( $tmpFilePath, '', $tmpFileName1 );

	    // ZIPファイルにファイル追加
	    $addFilePath = file_get_contents( $tmpFileName1 );
	    $zip->addFromString( mb_convert_encoding($fileName1, 'sjis-win'), $addFilePath );

	    // 明細2作成(お取引明細)
	    $tmpFileName2 = $this->createMeisai2( $fixedMonth, $tmpFilePath );
	    $unlinkList[] = $tmpFileName2;
	    $fileName2 = str_replace( $tmpFilePath, '', $tmpFileName2 );

	    // ZIPファイルにファイル追加
	    $addFilePath = file_get_contents( $tmpFileName2 );
	    $zip->addFromString( mb_convert_encoding($fileName2, 'sjis-win'), $addFilePath );

	    // 明細3作成(印紙代明細)
	    $tmpFileName3 = $this->createMeisai3( $fixedMonth, $tmpFilePath );
	    $unlinkList[] = $tmpFileName3;
	    $fileName3 = str_replace( $tmpFilePath, '', $tmpFileName3 );

	    // ZIPファイルにファイル追加
	    $addFilePath = file_get_contents( $tmpFileName3 );
	    $zip->addFromString( mb_convert_encoding($fileName3, 'sjis-win'), $addFilePath );

	    // 明細4作成(キャンセル返金明細)
	    $tmpFileName4 = $this->createMeisai4( $fixedMonth, $tmpFilePath );
	    $unlinkList[] = $tmpFileName4;
	    $fileName4 = str_replace( $tmpFilePath, '', $tmpFileName4 );

	    // ZIPファイルにファイル追加
	    $addFilePath = file_get_contents( $tmpFileName4 );
	    $zip->addFromString( mb_convert_encoding($fileName4, 'sjis-win'), $addFilePath );

	    // 明細5作成(調整額明細)
	    $tmpFileName5 = $this->createMeisai5( $fixedMonth, $tmpFilePath );
	    $unlinkList[] = $tmpFileName5;
	    $fileName5 = str_replace( $tmpFilePath, '', $tmpFileName5 );

	    // ZIPファイルにファイル追加
	    $addFilePath = file_get_contents( $tmpFileName5 );
	    $zip->addFromString( mb_convert_encoding($fileName5, 'sjis-win'), $addFilePath );

// 2015/10/29 Y.Suzuki Del 立替精算戻し明細のダウンロード処理は行わない Stt
// 	    // 明細5作成
// 	    $tmpFileName6 = $this->createMeisai6( $fixedMonth, $tmpFilePath );
//      $unlinkList[] = $tmpFileName6;
// 	    $fileName6 = str_replace( $tmpFilePath, '', $tmpFileName6 );

// 	    // ZIPファイルにファイル追加
// 	    $addFilePath = file_get_contents( $tmpFileName6 );
// 	    $zip->addFromString( $fileName6, $addFilePath );
// 2015/10/29 Y.Suzuki Del 立替精算戻し明細のダウンロード処理は行わない Stt

	    // ZIPファイルクローズ
	    $zip->close();

	    // ヘッダ
	    header( 'Content-Type: application/octet-stream; name="' . $outFileName . '"' );
	    header( 'Content-Disposition: attachment; filename="' . $outFileName . '"' );
	    header( 'Content-Length: ' . filesize( $tmpFilePath ) );

	    // 出力
	    echo readfile( $tmpFilePath );

	    // TEMP領域削除
	    $unlinkListCount = 0;
	    if (!empty($unlinkList)) {
	        $unlinkListCount = count($unlinkList);
	    }
	    for ($i=0; $i<$unlinkListCount; $i++) {
	        unlink( $unlinkList[$i] );
	    }
	    unlink( $tmpFilePath );
	    die();
	}

	/**
	 * 締め月リストを取得する
	 *
	 * @return array
	 */
	private function getFixedMonthList($entId) {
		$result = array();

		$end = date('Y-m-d');
		$start = date('Y-m-d', strtotime($end, '-1 year +1 day'));

		$stmt = $this->dbAdapter->query(
			$this->queries[ self::QUERY_GET_FIXED_MONTHS ]
// 			array( ':entId' => $entId )
		);

		$param = array( ':entId' => $entId );
		$ri = $stmt->execute($param);

		foreach( $ri as $row ) {
			$result[] = $row;
		}

		return $result;
	}

	/**
	 * 事業者IDと締め月を指定して、指定のテンプレートクエリを実行した
	 * 結果を取得する
	 *
	 * @param string $query_name クエリテンプレート名
	 * @param int $entId 事業者ID
	 * @param string $fixedMonth 締め月
	 * @return array
	 */
	private function _execQuery($query_name, $entId, $fixedMonth) {
	    $result = array();

		$stmt = $this->dbAdapter->query(
			$this->queries[ $query_name ]
// 		 array( ':entId' => $entId, ':fixedMonth' => $fixedMonth )
		 );

		$param = array( ':entId' => $entId, ':fixedMonth' => $fixedMonth );
		$result = $stmt->execute($param);

		return $result;
	}

	/**
	 * _execQuery()の結果の配列を、指定のカラム名でグルーピングした
	 * 連想配列として詰めなおす
	 *
	 * @param array $list クエリ実行結果の配列
	 * @param string $key_name グルーピングキーとなる、$listの1要素中のカラム名
	 * @return array
	 */
	private function _grouping($list, $key_name) {
		$results = array();
		foreach($list as $row) {
			$key = $row[$key_name];
			if( is_array($results[$key]) ) {
				$results[$key][] = $row;
			} else {
				$results[$key] = array($row);
			}
		}
		return $results;
	}

	/**
	 * 月次取引サマリを取得する
	 *
	 * @param int $entId 事業者ID
	 * @param string $fixedMonth 締め月
	 * @return array
	 */
	private function getSummary($entId, $fixedMonth) {
		return $this->_execQuery(self::QUERY_GET_SUMMARY, $entId, $fixedMonth);
	}

	/**
	 * 月次取引明細を取得する
	 *
	 * @param int $entId 事業者ID
	 * @param string $fixedMonth 締め月
	 * @return array
	 */
	private function getChargeFeeData($entId, $fixedMonth) {
		return $this->_grouping(
								$this->_execQuery(self::QUERY_GET_CHARGEFEE_LIST, $entId, $fixedMonth),
								'SiteNameKj' );
	}

	/**
	 * 月次印紙代明細を取得する
	 *
	 * @param int $entId 事業者ID
	 * @param string $fixedMonth 締め月
	 * @return array
	 */
	private function getStampFeeData($entId, $fixedMonth) {
		return $this->_grouping(
								$this->_execQuery(self::QUERY_GET_STAMPFEE_LIST, $entId, $fixedMonth),
								'SiteNameKj' );
	}

	/**
	 * 月次キャンセル返金明細を取得する
	 *
	 * @param int $entId 事業者ID
	 * @param string $fixedMonth 締め月
	 * @return array
	 */
	private function getCancelFeeData($entId, $fixedMonth) {
		return $this->_grouping(
								$this->_execQuery(self::QUERY_GET_CANCELFEE_LIST, $entId, $fixedMonth),
								'SiteNameKj' );
	}

	/**
	 * 請求手数料合計の内の、同梱ツール利用分に関する追加情報を取得する。
	 * 戻り値の連想配列はキー'HasInfo'に同梱ツール利用分の有無、キー'ClaimFee'にその分の手数料合計が
	 * 格納される。
	 *
	 * @access private
	 * @param int $entId 事業者ID
	 * @param string $fixedMonth 締め月
	 * @return array
	 */
	private function calcSelfBillingClaimFee($entId, $fixedMonth) {
		$rows = $this->_execQuery(self::QUERY_CALC_SELF_BILLING_CLAIM_FEE, $entId, $fixedMonth);
		$fee_total = 0;
		$has_fee = false;
		foreach($rows as $row) {
			if($row['HasInfo']) $has_fee = true;
			$fee_total += (int)$row['SelfBillingClaimFee'];
		}
		return array(
			'HasInfo' => $has_fee,
			'ClaimFee' => $fee_total
		);
	}

	/**
	 * 調整額内訳明細を取得する
	 *
	 * @param int $entId 事業者ID
	 * @param string $fixedMonth 締め月
	 * @return array
	 */
	private function getAdjustmentFeeData($entId, $fixedMonth) {
	    return $this->_grouping(
	                           $this->_execQuery(self::QUERY_GET_ADJUSTMENTFEE_LIST, $entId, $fixedMonth),
	                           'SiteNameKj' );
	}

	/**
	 * 月次取引明細を取得する
	 *
	 * @param int $entId 事業者ID
	 * @param string $fixedMonth 締め月
	 * @return array
	 */
	private function GetPayBackFeeList($entId, $fixedMonth) {
	    return $this->_grouping(
	    $this->_execQuery(self::QUERY_GET_PAYBACKFEE_LIST, $entId, $fixedMonth),
	    'SiteNameKj' );
	}
	/**
	 * クエリテンプレートを初期化する
	 */
	private function initQueryTemplates() {
		/**
		 * 2010.10.7 明細取得クエリ各種の絞り込み条件に、PayingControlのEnterpriseId指定を追加
		 */

		$this->queries = array();

		// 締め月リスト
		$this->queries[ self::QUERY_GET_FIXED_MONTHS ] = <<<Q_END
SELECT DISTINCT
	FixedMonth,
	SpanFrom,
	SpanTo
FROM
	T_EnterpriseClaimed
WHERE
	EnterpriseId = :entId
ORDER BY
	FixedMonth DESC
LIMIT 24
Q_END;

		// 指定月サマリ取得
		$this->queries[ self::QUERY_GET_SUMMARY ] = <<<Q_END
SELECT
	E.EnterpriseId,
	E.EnterpriseNameKj,
	E.PostalCode,
	E.PrefectureName,
	E.City,
	E.Town,
	E.Building,
	PP.PricePlanName AS PlanName,
	PC.PayingCycleName AS FixPattern,
	DATE_ADD( C.FixedMonth, INTERVAL 0 SECOND ) AS FixedMonth,
	C.ProcessDate,
	C.SpanFrom,
	C.SpanTo,
	C.OrderCount,
	C.OrderAmount,
	C.SettlementFee,
	C.ClaimFee,
	C.StampFee,
	C.MonthlyFee,
	C.CarryOverMonthlyFee,
	C.CancelRepaymentAmount,
	C.FfTransferFee,
	C.PayBackAmount,
	C.AdjustmentAmount,
	C.ClaimAmount,
	C.PaymentAmount
/* 以下、クレジット決済関連 */
,      (SELECT COUNT(ao.OrderSeq)
        FROM   T_PayingControl pc
               INNER JOIN T_PayingAndSales pas ON (pas.PayingControlSeq = pc.Seq)
               INNER JOIN T_Order o ON (o.OrderSeq = pas.OrderSeq)
               INNER JOIN AT_Order ao ON (ao.OrderSeq = o.OrderSeq)
        WHERE  pc.EnterpriseId = E.EnterpriseId
        AND    pc.AddUpFixedMonth = C.FixedMonth
        AND    pas.PayingControlSeq = pc.Seq
        AND    ao.ExtraPayType = 1
        AND    o.ClaimSendingClass = 11
       ) AS ChargeCountExtraPay1DK
,      (SELECT COUNT(ao.OrderSeq)
        FROM   T_PayingControl pc
               INNER JOIN T_PayingAndSales pas ON (pas.PayingControlSeq = pc.Seq)
               INNER JOIN T_Order o ON (o.OrderSeq = pas.OrderSeq)
               INNER JOIN AT_Order ao ON (ao.OrderSeq = o.OrderSeq)
        WHERE  pc.EnterpriseId = E.EnterpriseId
        AND    pc.AddUpFixedMonth = C.FixedMonth
        AND    pas.PayingControlSeq = pc.Seq
        AND    ao.ExtraPayType = 1
        AND    o.ClaimSendingClass <> 11
       ) AS ChargeCountExtraPay1BS
,      (SELECT SUM(pas.SettlementFee)
        FROM   T_PayingControl pc
               INNER JOIN T_PayingAndSales pas ON (pas.PayingControlSeq = pc.Seq)
               INNER JOIN T_Order o ON (o.OrderSeq = pas.OrderSeq)
               INNER JOIN AT_Order ao ON (ao.OrderSeq = o.OrderSeq)
        WHERE  pc.EnterpriseId = E.EnterpriseId
        AND    pc.AddUpFixedMonth = C.FixedMonth
        AND    pas.PayingControlSeq = pc.Seq
        AND    ao.ExtraPayType = 1
       ) AS SettlementFeeExtraPay1
,      (SELECT SUM(pas.ClaimFee)
        FROM   T_PayingControl pc
               INNER JOIN T_PayingAndSales pas ON (pas.PayingControlSeq = pc.Seq)
               INNER JOIN T_Order o ON (o.OrderSeq = pas.OrderSeq)
               INNER JOIN AT_Order ao ON (ao.OrderSeq = o.OrderSeq)
        WHERE  pc.EnterpriseId = E.EnterpriseId
        AND    pc.AddUpFixedMonth = C.FixedMonth
        AND    pas.PayingControlSeq = pc.Seq
        AND    ao.ExtraPayType = 1
        AND    o.ClaimSendingClass = 11
       ) AS ClaimFeeExtraPay1DK
,      (SELECT SUM(pas.ClaimFee)
        FROM   T_PayingControl pc
               INNER JOIN T_PayingAndSales pas ON (pas.PayingControlSeq = pc.Seq)
               INNER JOIN T_Order o ON (o.OrderSeq = pas.OrderSeq)
               INNER JOIN AT_Order ao ON (ao.OrderSeq = o.OrderSeq)
        WHERE  pc.EnterpriseId = E.EnterpriseId
        AND    pc.AddUpFixedMonth = C.FixedMonth
        AND    pas.PayingControlSeq = pc.Seq
        AND    ao.ExtraPayType = 1
        AND    o.ClaimSendingClass <> 11
       ) AS ClaimFeeExtraPay1BS
,      (SELECT SUM(can.RepayTotal)
        FROM   T_PayingControl pc
               INNER JOIN T_PayingAndSales pas ON (pas.PayingControlSeq = pc.Seq)
               INNER JOIN T_Order o ON (o.OrderSeq = pas.OrderSeq)
               INNER JOIN AT_Order ao ON (ao.OrderSeq = o.OrderSeq)
               INNER JOIN T_Cancel can ON (o.OrderSeq = can.OrderSeq)
        WHERE  pc.EnterpriseId = E.EnterpriseId
        AND    pc.AddUpFixedMonth = C.FixedMonth
        AND    pas.PayingControlSeq = pc.Seq
        AND    ao.ExtraPayType = 1
        AND    o.ClaimSendingClass = 11
       ) AS CancelAmountExtraPay1DK
,      (SELECT SUM(can.RepayTotal)
        FROM   T_PayingControl pc
               INNER JOIN T_PayingAndSales pas ON (pas.PayingControlSeq = pc.Seq)
               INNER JOIN T_Order o ON (o.OrderSeq = pas.OrderSeq)
               INNER JOIN AT_Order ao ON (ao.OrderSeq = o.OrderSeq)
               INNER JOIN T_Cancel can ON (o.OrderSeq = can.OrderSeq)
        WHERE  pc.EnterpriseId = E.EnterpriseId
        AND    pc.AddUpFixedMonth = C.FixedMonth
        AND    pas.PayingControlSeq = pc.Seq
        AND    ao.ExtraPayType = 1
        AND    o.ClaimSendingClass <> 11
       ) AS CancelAmountExtraPay1BS
FROM
	T_Enterprise E INNER JOIN
	T_EnterpriseClaimed C ON C.EnterpriseId = E.EnterpriseId JOIN
    T_Site S ON S.EnterpriseId = E.EnterpriseId JOIN
    M_PricePlan PP ON ( PP.PricePlanId  = F_GetCampaignVal(E.EnterpriseId, S.SiteId, DATE_FORMAT(C.FixedMonth, '%Y-%m-01'), 'AppPlan') ) JOIN
	M_PayingCycle PC ON (PC.PayingCycleId = E.PayingCycleId )
WHERE
	E.EnterpriseId = :entId AND
	C.FixedMonth = :fixedMonth AND
	(
		C.OrderCount <> 0 OR
		C.SettlementFee <> 0 OR
		C.ClaimFee <> 0 OR
		C.StampFee <> 0 OR
		C.MonthlyFee <> 0 OR
		C.CarryOverMonthlyFee <> 0 OR
		C.CancelRepaymentAmount <> 0 OR
		C.FfTransferFee <> 0 OR
		C.AdjustmentAmount <> 0
	)
ORDER BY S.SiteId
Q_END;

		// 指定月明細取得
		$this->queries[ self::QUERY_GET_CHARGEFEE_LIST ] = <<<Q_END
SELECT STRAIGHT_JOIN
	O.OrderSeq,
	O.Chg_Seq,
	O.EnterpriseId,
	O.OrderId,
	O.ReceiptOrderDate,
	C.NameKj,
	O.SiteId,
	L.UseAmount,
	(-1 * L.SettlementFee) AS SettlementFee,
	(-1 * L.ClaimFee) AS ClaimFee,
	0 AS StampFee,
	(L.UseAmount
	+ (-1 * L.SettlementFee)
	+ (-1 * L.ClaimFee)
	)
	AS Chg_ChargeAmount,
	O.Ent_OrderId,
	S.SiteNameKj,
	P.FixedDate,
	P.AddUpFixedMonth,
	OS.Deli_JournalIncDate
FROM
	T_PayingControl P JOIN
	T_Order O ON O.Chg_Seq = P.Seq INNER JOIN
	T_PayingAndSales L ON L.OrderSeq = O.OrderSeq INNER JOIN
	T_Customer C ON C.OrderSeq = O.OrderSeq INNER JOIN
	T_Site S ON S.SiteId = O.SiteId INNER JOIN
	T_OrderSummary OS ON OS.OrderSeq = O.OrderSeq LEFT OUTER JOIN
	T_StampFee SF ON SF.OrderSeq = O.OrderSeq AND SF.ClearFlg = 1
WHERE
	P.EnterpriseId = :entId AND
	O.EnterpriseId = :entId AND
	P.ChargeCount > 0 AND
	P.AddUpFlg = 1 AND
	P.AddUpFixedMonth = :fixedMonth
ORDER BY
	O.EnterpriseId,
	O.SiteId,
	O.OrderSeq
Q_END;

		// 指定月印紙代明細取得
		$this->queries[ self::QUERY_GET_STAMPFEE_LIST ] = <<<Q_END
SELECT
	O.OrderSeq,
	O.Chg_Seq,
	O.EnterpriseId,
	O.OrderId,
	O.ReceiptOrderDate,
	C.NameKj,
	O.SiteId,
	O.UseAmount,
	L.SettlementFee,
	L.ClaimFee,
	S.StampFee,
	O.Chg_ChargeAmount,
	O.Ent_OrderId,
	P.FixedDate,
	P.AddUpFixedMonth,
	ST.SiteNameKj,
	S.DecisionDate,
	S.ClearFlg,
	S.ClearDate
FROM
	T_PayingControl P JOIN
	T_StampFee S ON S.PayingControlSeq = P.Seq INNER JOIN
	T_Order O ON O.OrderSeq = S.OrderSeq INNER JOIN
	T_PayingAndSales L ON L.OrderSeq = O.OrderSeq INNER JOIN
	T_Customer C ON C.OrderSeq = O.OrderSeq INNER JOIN
	T_Site ST ON ST.SiteId = O.SiteId
WHERE
	P.EnterpriseId = :entId AND
	O.EnterpriseId = :entId AND
	P.AddUpFlg = 1 AND
	P.AddUpFixedMonth = :fixedMonth AND
	S.ClearFlg = 1
ORDER BY
	O.EnterpriseId,
	O.SiteId,
	O.OrderSeq
Q_END;

		// 指定月キャンセル明細取得
		$this->queries[ self::QUERY_GET_CANCELFEE_LIST ] = <<<Q_END
SELECT
	O.OrderSeq,
	O.Chg_Seq,
	O.EnterpriseId,
	O.OrderId,
	O.ReceiptOrderDate,
	CS.NameKj,
	O.SiteId,
	O.UseAmount,
	O.Cnl_ReturnSaikenCancelFlg,
	L.SettlementFee,
	L.ClaimFee,
	O.Chg_ChargeAmount,
	O.Ent_OrderId,
	P.FixedDate,
	P.AddUpFixedMonth,
	S.SiteNameKj,
	C.CancelDate,
	C.CancelReason,
	C.ApprovalDate,
	C.RepayTotal,
	C.KeepAnAccurateFlg,
	C.KeepAnAccurateDate
FROM
	T_PayingControl P JOIN
	T_Cancel C ON C.PayingControlSeq = P.Seq INNER JOIN
	T_Order O ON O.OrderSeq = C.OrderSeq INNER JOIN
	T_Customer CS ON CS.OrderSeq = O.OrderSeq INNER JOIN
	T_Site S ON S.SiteId = O.SiteId LEFT OUTER JOIN
	T_PayingAndSales L ON L.OrderSeq = O.OrderSeq
WHERE
	P.EnterpriseId = :entId AND
	O.EnterpriseId = :entId AND
	P.AddUpFlg = 1 AND
	P.AddUpFixedMonth = :fixedMonth AND
	C.KeepAnAccurateFlg = 1 AND
	C.RepayTotal <> 0 AND
	C.ValidFlg = 1
ORDER BY
	O.EnterpriseId,
	O.SiteId,
	O.OrderSeq
Q_END;

		// 請求手数料合計内訳取得（指定月サマリ用）
		$this->queries[ self::QUERY_CALC_SELF_BILLING_CLAIM_FEE ] = <<<Q_END
SELECT
	ec.FixedMonth,
	ec.ProcessDate,
	ec.ClaimFee AS FixedClaimFee,
	ec.OrderCount,
	pc.Seq AS PCSeq,
	pc.ClaimFee AS ControlClaimFee,
	pas.Seq AS PASSeq,
	pas.ClaimFee AS SalesClaimFee,
	pas.PayingControlSeq,
	pas.OrderSeq,
	CASE
		WHEN ch.EnterpriseBillingCode IS NULL THEN 0
		ELSE pas.ClaimFee
	END AS SelfBillingClaimFee,
	CASE
		WHEN ch.EnterpriseBillingCode IS NULL THEN 0
		ELSE 1
	END AS HasInfo
FROM
	T_EnterpriseClaimed ec
		STRAIGHT_JOIN
	T_PayingControl pc
		ON (
			pc.EnterpriseId = ec.EnterpriseId AND
			pc.AddUpFixedMonth = ec.FixedMonth AND
			pc.AddUpFlg = 1
		)
		STRAIGHT_JOIN
	T_PayingAndSales pas
		ON pas.PayingControlSeq = pc.Seq
		LEFT OUTER JOIN
	T_Order o
        ON (
            o.OrderSeq = pas.OrderSeq
        )
        LEFT OUTER JOIN
    T_ClaimControl cc
        ON cc.OrderSeq = o.OrderSeq
        LEFT OUTER JOIN
    T_ClaimHistory ch
        ON ch.OrderSeq = o.P_OrderSeq
WHERE
	ec.EnterpriseId = :entId AND
	ec.FixedMonth = :fixedMonth AND
	ch.Seq = (SELECT MIN(Seq) FROM T_ClaimHistory ch2 where ch2.OrderSeq = o.P_OrderSeq)
Q_END;

		// 調整額内訳明細取得
		$this->queries[ self::QUERY_GET_ADJUSTMENTFEE_LIST ] = <<<Q_END
SELECT
	P.FixedDate,
	A.OrderId,
	O.Ent_OrderId,
	C.NameKj,
	O.ReceiptOrderDate,
	A.ItemCode,
    (SELECT KeyContent FROM M_Code WHERE CodeId = 89 AND KeyCode = A.ItemCode) AS kamoku,
	A.AdjustmentAmount,
    A.SerialNumber
FROM
	T_PayingControl P JOIN
	T_EnterpriseClaimed EC ON EC.EnterpriseId = P.EnterpriseId AND EC.FixedMonth = P.AddUpFixedMonth INNER JOIN
    T_AdjustmentAmount A ON A.PayingControlSeq = P.Seq LEFT OUTER JOIN
    T_Order O ON O.OrderSeq = A.OrderSeq LEFT OUTER JOIN
    T_Customer C ON C.OrderSeq = A.OrderSeq
WHERE
	EC.EnterpriseId = :entId AND
	EC.FixedMonth = :fixedMonth
ORDER BY
	SerialNumber
Q_END;

		// 立替戻し明細取得
		$this->queries[ self::QUERY_GET_PAYBACKFEE_LIST ] = <<<Q_END
SELECT
	S.SiteId,
	S.SiteNameKj,
	O.OrderId,
	O.Ent_OrderId,
	C.NameKj,
	O.ReceiptOrderDate,
	(SELECT MAX(Deli_JournalIncDate) FROM T_OrderItems WHERE OrderSeq = O.OrderSeq) AS MaxDeliJournalIncDate,
	P.FixedDate,
	PAS.UseAmount,
	CC.ReceiptAmountTotal,
	PB.PayBackAmount
FROM
		T_PayingControl P JOIN
		T_EnterpriseClaimed EC ON (P.EnterpriseId = EC.EnterpriseId AND P.AddUpFixedMonth = EC.FixedMonth) STRAIGHT_JOIN
		T_PayingBackControl PB ON ( PB.PayingControlSeq = P.Seq ) STRAIGHT_JOIN
 		T_PayingAndSales PAS ON (PAS.OrderSeq = PB.OrderSeq) INNER JOIN
		T_Order O ON O.OrderSeq = PB.OrderSeq INNER JOIN
		T_Customer C ON C.OrderSeq = O.OrderSeq INNER JOIN
		T_Enterprise E ON E.EnterpriseId = P.EnterpriseId INNER JOIN
		T_Site S ON S.SiteId = O.SiteId LEFT OUTER JOIN
        T_ClaimControl CC ON O.OrderSeq = CC.OrderSeq
WHERE
	EC.EnterpriseId = :entId AND
	EC.FixedMonth = :fixedMonth
ORDER BY
	SiteId,
	OrderId
Q_END;
	}

	/**
	 * 明細1CSV作成
	 * @param $fixedMonth 立替締め月
	 * @param $tmpFilePath TEMP領域
	 * @return ファイル名
	 */
	private function createMeisai1( $fixedMonth, $tmpFilePath ) {
	    // 加盟店ID
	    $entId = $this->app->authManager->getUserInfo()->EnterpriseId;

	    // CSVデータ取得
	    $sql = <<<Q_END
SELECT ec.FixedMonth
,      e.EnterpriseId
,      e.EnterpriseNameKj
,      e.PostalCode
,      CONCAT( e.PrefectureName, e.City, e.Town, e.Building ) AS Address
,      ec.SpanFrom
,      ec.SpanTo
,      ec.OrderCount
,      ec.OrderAmount
,      ( -1 * ec.SettlementFee ) AS SettlementFee
,      ( -1 * ec.ClaimFee ) AS ClaimFee
,      ( -1 * ec.StampFee ) AS StampFee
,      ( -1 * ec.MonthlyFee ) AS MonthlyFee
,      ec.CarryOverMonthlyFee
,      ec.CancelRepaymentAmount
,      ( -1 * ec.FfTransferFee ) AS FfTransferFee
,      ec.PayBackAmount
,      ec.AdjustmentAmount
,      ec.ClaimAmount
,      ec.PaymentAmount
FROM T_Enterprise e INNER JOIN
     T_EnterpriseClaimed ec ON ec.EnterpriseId = e.EnterpriseId
WHERE
     e.EnterpriseId = :EnterpriseId AND
     ec.FixedMonth = :FixedMonth AND
     (
        ec.OrderCount <> 0 OR
        ec.SettlementFee <> 0 OR
        ec.ClaimFee <> 0 OR
        ec.StampFee <> 0 OR
        ec.MonthlyFee <> 0 OR
        ec.CarryOverMonthlyFee <> 0 OR
        ec.CancelRepaymentAmount <> 0 OR
        ec.FfTransferFee <> 0 OR
        ec.AdjustmentAmount <> 0
    )
Q_END;

	    // SQL用のパラメータ設定
	    $prm = array( ':EnterpriseId' => $entId, ':FixedMonth' => $fixedMonth );
	    $datas = ResultInterfaceToArray( $this->app->dbAdapter->query( $sql )->execute( $prm ) );

	    // テンプレートヘッダー/フィールドマスタを使用して、CSVをResposeに入れる
	    $templateId = 'CKA11020_1';     // テンプレートID       ご利用明細
	    $templateClass = 2;             // 区分                 加盟店
	    $seq = $entId;                  // シーケンス           加盟店ID
	    $templatePattern = 0;           // テンプレートパターン

	    $logicTemplate = new LogicTemplate( $this->app->dbAdapter );

	    $fileName = sprintf('%s_精算書.csv', date('Ymd'));

	    $tmpFileName = $tmpFilePath . $fileName;

	    $result = $logicTemplate->convertArraytoFile( $datas, $tmpFileName, $templateId, $templateClass, $seq, $templatePattern );

	    if( $result == false ) {
	        throw new \Exception( $logicTemplate->getErrorMessage() );
	    }

	    return $tmpFileName;
	}

	/**
	 * 明細2CSV作成
	 * @param $fixedMonth 立替締め月
	 * @param $tmpFilePath TEMP領域
	 * @return ファイル名
	 */
	private function createMeisai2( $fixedMonth, $tmpFilePath ) {
	    // 加盟店ID
	    $entId = $this->app->authManager->getUserInfo()->EnterpriseId;

	    // CSVデータ取得
	    $sql = <<<Q_END
SELECT ec.FixedMonth
,      e.EnterpriseId
,      e.EnterpriseNameKj
,      ec.SpanFrom
,      ec.SpanTo
,      pc.ChargeCount
,      s.SiteId
,      s.SiteNameKj
,      o.OrderId
,      o.Ent_OrderId
,      c.NameKj
,      o.ReceiptOrderDate
,      ( SELECT MAX( Deli_JournalIncDate ) FROM T_OrderItems WHERE OrderSeq = o.OrderSeq ) AS Deli_JournalIncDate
,      pc.FixedDate
,      pas.UseAmount
,      ( -1 * pas.SettlementFee ) AS SettlementFee
,      ( -1 * pas.ClaimFee ) AS ClaimFee
,      0 AS StampFee
,      (
        ( pas.UseAmount ) +
        ( -1 * pas.SettlementFee ) +
        ( -1 * pas.ClaimFee )
       ) AS ChargeAmount
FROM T_PayingControl pc INNER JOIN
     T_EnterpriseClaimed ec ON ec.EnterpriseId = pc.EnterpriseId AND ec.FixedMonth = pc.AddUpFixedMonth INNER JOIN
     T_PayingAndSales pas ON pas.PayingControlSeq = pc.Seq INNER JOIN
     T_Order o ON o.OrderSeq = pas.OrderSeq LEFT OUTER JOIN
     T_StampFee sf ON sf.OrderSeq = o.OrderSeq INNER JOIN
     T_Customer c ON c.OrderSeq = o.OrderSeq INNER JOIN
     T_Enterprise e ON e.EnterpriseId = pc.EnterpriseId INNER JOIN
     T_Site s ON s.SiteId = o.SiteId
WHERE
     ec.EnterpriseId = :EnterpriseId AND
     ec.FixedMonth = :FixedMonth AND
     pc.ChargeCount > 0 AND
     pc.AddUpFlg = 1
ORDER BY
     o.SiteId, o.OrderId
Q_END;

	    // SQL用のパラメータ設定
	    $prm = array( ':EnterpriseId' => $entId, ':FixedMonth' => $fixedMonth );
	    $datas = ResultInterfaceToArray( $this->app->dbAdapter->query( $sql )->execute( $prm ) );

	    // テンプレートヘッダー/フィールドマスタを使用して、CSVをResposeに入れる
	    $templateId = 'CKA11020_2';     // テンプレートID       ご利用明細
	    $templateClass = 2;             // 区分                 加盟店
	    $seq = $entId;                  // シーケンス           加盟店ID
	    $templatePattern = 0;           // テンプレートパターン

	    $logicTemplate = new LogicTemplate( $this->app->dbAdapter );

	    $fileName = sprintf( '%s_お取引明細.csv', date('Ymd') );

	    $tmpFileName = $tmpFilePath . $fileName;

	    $result = $logicTemplate->convertArraytoFile( $datas, $tmpFileName, $templateId, $templateClass, $seq, $templatePattern );

	    if( $result == false ) {
	        throw new \Exception( $logicTemplate->getErrorMessage() );
	    }

	    return $tmpFileName;
	}

	/**
	 * 明細3CSV作成
	 * @param $fixedMonth 立替締め月
	 * @param $tmpFilePath TEMP領域
	 * @return ファイル名
	 */
	private function createMeisai3( $fixedMonth, $tmpFilePath ) {
	    // 加盟店ID
	    $entId = $this->app->authManager->getUserInfo()->EnterpriseId;

	    // CSVデータ取得
	    $sql = <<<Q_END
SELECT ec.FixedMonth
,      e.EnterpriseId
,      e.EnterpriseNameKj
,      ec.SpanFrom
,      ec.SpanTo
,      pc.ChargeCount
,      s.SiteId
,      s.SiteNameKj
,      o.OrderId
,      o.Ent_OrderId
,      c.NameKj
,      o.ReceiptOrderDate
,      pc2.FixedDate AS FixedDate2
,      o.UseAmount
,      sf.StampFee
FROM T_PayingControl pc INNER JOIN
     T_EnterpriseClaimed ec ON ec.EnterpriseId = pc.EnterpriseId AND ec.FixedMonth = pc.AddUpFixedMonth INNER JOIN
     T_StampFee sf ON sf.PayingControlSeq = pc.Seq INNER JOIN
     T_Order o ON o.OrderSeq = sf.OrderSeq INNER JOIN
     T_Customer c ON c.OrderSeq = o.OrderSeq INNER JOIN
     T_Enterprise e ON e.EnterpriseId = pc.EnterpriseId INNER JOIN
     T_Site s ON s.SiteId = o.SiteId INNER JOIN
     T_PayingAndSales pas ON pas.OrderSeq = o.OrderSeq LEFT OUTER JOIN
     T_PayingControl pc2 ON pc2.Seq = pas.PayingControlSeq
WHERE
     ec.EnterpriseId = :EnterpriseId AND
     ec.FixedMonth = :FixedMonth AND
     pc.AddUpFlg = 1 AND
     sf.ClearFlg = 1
ORDER BY
    o.SiteId, o.OrderId
Q_END;

	    // SQL用のパラメータ設定
	    $prm = array( ':EnterpriseId' => $entId, ':FixedMonth' => $fixedMonth );
	    $datas = ResultInterfaceToArray( $this->app->dbAdapter->query( $sql )->execute( $prm ) );

	    // テンプレートヘッダー/フィールドマスタを使用して、CSVをResposeに入れる
	    $templateId = 'CKA11020_3';     // テンプレートID       ご利用明細
	    $templateClass = 2;             // 区分                 加盟店
	    $seq = $entId;                  // シーケンス           加盟店ID
	    $templatePattern = 0;           // テンプレートパターン

	    $logicTemplate = new LogicTemplate( $this->app->dbAdapter );

	    $fileName = sprintf( '%s_印紙代明細.csv', date('Ymd') );

	    $tmpFileName = $tmpFilePath . $fileName;

	    $result = $logicTemplate->convertArraytoFile( $datas, $tmpFileName, $templateId, $templateClass, $seq, $templatePattern );

	    if( $result == false ) {
	        throw new \Exception( $logicTemplate->getErrorMessage() );
	    }

	    return $tmpFileName;
	}

	/**
	 * 明細4CSV作成
	 * @param $fixedMonth 立替締め月
	 * @param $tmpFilePath TEMP領域
	 * @return ファイル名
	 */
	private function createMeisai4( $fixedMonth, $tmpFilePath ) {
	    // 加盟店ID
	    $entId = $this->app->authManager->getUserInfo()->EnterpriseId;

	    // CSVデータ取得
	    $sql = <<<Q_END
SELECT ec.FixedMonth
,      e.EnterpriseId
,      e.EnterpriseNameKj
,      ec.SpanFrom
,      ec.SpanTo
,      pc.ChargeCount
,      s.SiteId
,      s.SiteNameKj
,      o.OrderId
,      o.Ent_OrderId
,      c.NameKj
,      o.ReceiptOrderDate
,      pc2.FixedDate AS FixedDate2
,      cncl.CancelDate
,      o.UseAmount
,      ( -1 * cncl.RepayTotal ) AS RepayTotal
FROM T_PayingControl pc INNER JOIN
     T_EnterpriseClaimed ec ON ec.EnterpriseId = pc.EnterpriseId AND ec.FixedMonth = pc.AddUpFixedMonth INNER JOIN
     T_Cancel cncl ON cncl.PayingControlSeq = pc.Seq INNER JOIN
     T_Order o ON o.OrderSeq = cncl.OrderSeq INNER JOIN
     T_Customer c ON c.OrderSeq = o.OrderSeq INNER JOIN
     T_Enterprise e ON e.EnterpriseId = pc.EnterpriseId INNER JOIN
     T_Site s ON s.SiteId = o.SiteId INNER JOIN
     T_PayingAndSales pas ON pas.OrderSeq = o.OrderSeq LEFT OUTER JOIN
     T_PayingControl pc2 ON pc2.Seq = pas.PayingControlSeq
WHERE
     ec.EnterpriseId = :EnterpriseId AND
     ec.FixedMonth = :FixedMonth AND
     pc.AddUpFlg = 1 AND
     cncl.KeepAnAccurateFlg = 1 AND
     cncl.RepayTotal <> 0 AND
     cncl.ValidFlg = 1
ORDER BY
     o.SiteId, o.OrderId
Q_END;

	    // SQL用のパラメータ設定
	    $prm = array( ':EnterpriseId' => $entId, ':FixedMonth' => $fixedMonth );
	    $datas = ResultInterfaceToArray( $this->app->dbAdapter->query( $sql )->execute( $prm ) );

	    // テンプレートヘッダー/フィールドマスタを使用して、CSVをResposeに入れる
	    $templateId = 'CKA11020_4';     // テンプレートID       ご利用明細
	    $templateClass = 2;             // 区分                 加盟店
	    $seq = $entId;                  // シーケンス           加盟店ID
	    $templatePattern = 0;           // テンプレートパターン

	    $logicTemplate = new LogicTemplate( $this->app->dbAdapter );

	    $fileName = sprintf( '%s_キャンセル返金明細.csv', date('Ymd') );

	    $tmpFileName = $tmpFilePath . $fileName;

	    $result = $logicTemplate->convertArraytoFile( $datas, $tmpFileName, $templateId, $templateClass, $seq, $templatePattern );

	    if( $result == false ) {
	        throw new \Exception( $logicTemplate->getErrorMessage() );
	    }

	    return $tmpFileName;
	}

	/**
	 * 明細5CSV作成
	 * @param $fixedMonth 立替締め月
	 * @param $tmpFilePath TEMP領域
	 * @return ファイル名
	 */
	private function createMeisai5( $fixedMonth, $tmpFilePath ) {
	    // 加盟店ID
	    $entId = $this->app->authManager->getUserInfo()->EnterpriseId;

	    // CSVデータ取得
	    $sql = <<<Q_END
SELECT ec.FixedMonth
,      e.EnterpriseId
,      e.EnterpriseNameKj
,      ec.SpanFrom
,      ec.SpanTo
,      pc.ChargeCount
,      pc2.FixedDate AS FixedDate2
,      o.OrderId
,      o.Ent_OrderId
,      c.NameKj
,      o.ReceiptOrderDate
,      ( SELECT KeyContent FROM M_Code WHERE CodeId = 89 AND KeyCode = aa.ItemCode ) AS ItemCodeName
,      aa.AdjustmentAmount
FROM T_PayingControl pc INNER JOIN
     T_EnterpriseClaimed ec ON ec.EnterpriseId = pc.EnterpriseId AND ec.FixedMonth = pc.AddUpFixedMonth INNER JOIN
     T_AdjustmentAmount aa ON aa.PayingControlSeq = pc.Seq LEFT OUTER JOIN
     T_Order o ON o.OrderSeq = aa.OrderSeq LEFT OUTER JOIN
     T_Customer c ON c.OrderSeq = aa.OrderSeq INNER JOIN
     T_Enterprise e ON e.EnterpriseId = pc.EnterpriseId LEFT OUTER JOIN
     T_PayingAndSales pas ON pas.OrderSeq = o.OrderSeq LEFT OUTER JOIN
     T_PayingControl pc2 ON pc2.Seq = pas.PayingControlSeq
WHERE
     ec.EnterpriseId = :EnterpriseId AND
     ec.FixedMonth = :FixedMonth
ORDER BY
     aa.SerialNumber
Q_END;

	    // SQL用のパラメータ設定
	    $prm = array( ':EnterpriseId' => $entId, ':FixedMonth' => $fixedMonth );
	    $datas = ResultInterfaceToArray( $this->app->dbAdapter->query( $sql )->execute( $prm ) );

	    // テンプレートヘッダー/フィールドマスタを使用して、CSVをResposeに入れる
	    $templateId = 'CKA11020_5';     // テンプレートID       ご利用明細
	    $templateClass = 2;             // 区分                 加盟店
	    $seq = $entId;                  // シーケンス           加盟店ID
	    $templatePattern = 0;           // テンプレートパターン

	    $logicTemplate = new LogicTemplate( $this->app->dbAdapter );

	    $fileName = sprintf( '%s_調整額明細.csv', date('Ymd') );

	    $tmpFileName = $tmpFilePath . $fileName;

	    $result = $logicTemplate->convertArraytoFile( $datas, $tmpFileName, $templateId, $templateClass, $seq, $templatePattern );

	    if( $result == false ) {
	        throw new \Exception( $logicTemplate->getErrorMessage() );
	    }

	    return $tmpFileName;
	}

	/**
	 * 明細6CSV作成
	 * @param $fixedMonth 立替締め月
	 * @param $tmpFilePath TEMP領域
	 * @return ファイル名
	 */
	private function createMeisai6( $fixedMonth, $tmpFilePath ) {
	    // 加盟店ID
	    $entId = $this->app->authManager->getUserInfo()->EnterpriseId;

	    // CSVデータ取得
	    $sql = <<<Q_END
SELECT ec.FixedMonth
,      e.EnterpriseId
,      e.EnterpriseNameKj
,      ec.SpanFrom
,      ec.SpanTo
,      pc.ChargeCount
,      s.SiteId
,      s.SiteNameKj
,      o.OrderId
,      o.Ent_OrderId
,      c.NameKj
,      o.ReceiptOrderDate
,      ( SELECT MAX( Deli_JournalIncDate ) FROM T_OrderItems WHERE OrderSeq = o.OrderSeq ) AS Deli_JournalIncDate
,      pc2.FixedDate AS FixedDate2
,      pas.UseAmount
,      cc.ReceiptAmountTotal
,      pbc.PayBackAmount
FROM T_PayingControl pc INNER JOIN
     T_EnterpriseClaimed ec ON ec.EnterpriseId = pc.EnterpriseId AND ec.FixedMonth = pc.AddUpFixedMonth INNER JOIN
     T_PayingAndSales pas ON pas.PayingControlSeq = pc.Seq INNER JOIN
     T_PayingBackControl pbc ON pbc.PayingControlSeq = pc.Seq INNER JOIN
     T_Order o ON o.OrderSeq = pbc.OrderSeq INNER JOIN
     T_Customer c ON c.OrderSeq = o.OrderSeq INNER JOIN
     T_Enterprise e ON e.EnterpriseId = pc.EnterpriseId INNER JOIN
     T_Site s On s.SiteId = o.SiteId LEFT OUTER JOIN
     T_ClaimControl cc ON o.OrderSeq = cc.OrderSeq LEFT OUTER JOIN
     T_PayingControl pc2 ON pc2.Seq = pas.PayingControlSeq
WHERE
     ec.EnterpriseId = :EnterpriseId AND
     ec.FixedMonth = :FixedMonth
ORDER BY
     o.SiteId, o.OrderId
Q_END;

	    // SQL用のパラメータ設定
	    $prm = array( ':EnterpriseId' => $entId, ':FixedMonth' => $fixedMonth );
	    $datas = ResultInterfaceToArray( $this->app->dbAdapter->query( $sql )->execute( $prm ) );

	    // テンプレートヘッダー/フィールドマスタを使用して、CSVをResposeに入れる
	    $templateId = 'CKA11020_6';     // テンプレートID       ご利用明細
	    $templateClass = 2;             // 区分                 加盟店
	    $seq = $entId;                  // シーケンス           加盟店ID
	    $templatePattern = 0;           // テンプレートパターン

	    $logicTemplate = new LogicTemplate( $this->app->dbAdapter );

	    $fileName = sprintf( 'Meisai_6_%s.csv', date('YmdHis') );

	    $tmpFileName = $tmpFilePath . $fileName;

	    $result = $logicTemplate->convertArraytoFile( $datas, $tmpFileName, $templateId, $templateClass, $seq, $templatePattern );

	    if( $result == false ) {
	        throw new \Exception( $logicTemplate->getErrorMessage() );
	    }

	    return $tmpFileName;
	}

    /**
     * 請求書発行
     */
    public function billissueAction() {
        // 立替締め月
        $params = $this->getParams();
        $fixedMonth = $params['fixedMonth'];

        // 加盟店ID
        $entId = $this->app->authManager->getUserInfo()->EnterpriseId;

        // SQL用のパラメータ設定
        $prm = array( ':EnterpriseId' => $entId, ':FixedMonth' => $fixedMonth );

        // PDF用データ取得
        // 精算書
        $sql = <<<Q_END
SELECT e.EnterpriseNameKj
,      e.PostalCode
,      CONCAT( e.PrefectureName, e.City, e.Town, e.Building ) AS Address
,      ec.FixedMonth
,      ec.SpanFrom
,      ec.SpanTo
,      ec.OrderCount
,      ec.OrderAmount
,      ( -1 * ec.SettlementFee ) AS SettlementFee
,      ( -1 * ec.ClaimFee ) AS ClaimFee
,      ( -1 * ec.StampFee ) AS StampFee
,      ( -1 * ec.MonthlyFee ) AS MonthlyFee
,      ec.CarryOverMonthlyFee
,      ec.CancelRepaymentAmount
,      ( -1 * ec.FfTransferFee ) AS FfTransferFee
,      ec.PayBackAmount
,      ec.AdjustmentAmount
,      ec.ClaimAmount
,      ec.PaymentAmount
/* 以下、クレジット決済関連 */
,      (SELECT COUNT(ao.OrderSeq)
        FROM   T_PayingControl pc
               INNER JOIN T_PayingAndSales pas ON (pas.PayingControlSeq = pc.Seq)
               INNER JOIN T_Order o ON (o.OrderSeq = pas.OrderSeq)
               INNER JOIN AT_Order ao ON (ao.OrderSeq = o.OrderSeq)
        WHERE  pc.EnterpriseId = e.EnterpriseId
        AND    pc.AddUpFixedMonth = ec.FixedMonth
        AND    pas.PayingControlSeq = pc.Seq
        AND    ao.ExtraPayType = 1
        AND    o.ClaimSendingClass = 11
       ) AS ChargeCountExtraPay1DK
,      (SELECT COUNT(ao.OrderSeq)
        FROM   T_PayingControl pc
               INNER JOIN T_PayingAndSales pas ON (pas.PayingControlSeq = pc.Seq)
               INNER JOIN T_Order o ON (o.OrderSeq = pas.OrderSeq)
               INNER JOIN AT_Order ao ON (ao.OrderSeq = o.OrderSeq)
        WHERE  pc.EnterpriseId = e.EnterpriseId
        AND    pc.AddUpFixedMonth = ec.FixedMonth
        AND    pas.PayingControlSeq = pc.Seq
        AND    ao.ExtraPayType = 1
        AND    o.ClaimSendingClass <> 11
       ) AS ChargeCountExtraPay1BS
,      (-1) * (SELECT SUM(pas.SettlementFee)
        FROM   T_PayingControl pc
               INNER JOIN T_PayingAndSales pas ON (pas.PayingControlSeq = pc.Seq)
               INNER JOIN T_Order o ON (o.OrderSeq = pas.OrderSeq)
               INNER JOIN AT_Order ao ON (ao.OrderSeq = o.OrderSeq)
        WHERE  pc.EnterpriseId = e.EnterpriseId
        AND    pc.AddUpFixedMonth = ec.FixedMonth
        AND    pas.PayingControlSeq = pc.Seq
        AND    ao.ExtraPayType = 1
       ) AS SettlementFeeExtraPay1
,      (-1) * (SELECT SUM(pas.ClaimFee)
        FROM   T_PayingControl pc
               INNER JOIN T_PayingAndSales pas ON (pas.PayingControlSeq = pc.Seq)
               INNER JOIN T_Order o ON (o.OrderSeq = pas.OrderSeq)
               INNER JOIN AT_Order ao ON (ao.OrderSeq = o.OrderSeq)
        WHERE  pc.EnterpriseId = e.EnterpriseId
        AND    pc.AddUpFixedMonth = ec.FixedMonth
        AND    pas.PayingControlSeq = pc.Seq
        AND    ao.ExtraPayType = 1
        AND    o.ClaimSendingClass = 11
       ) AS ClaimFeeExtraPay1DK
,      (-1) * (SELECT SUM(pas.ClaimFee)
        FROM   T_PayingControl pc
               INNER JOIN T_PayingAndSales pas ON (pas.PayingControlSeq = pc.Seq)
               INNER JOIN T_Order o ON (o.OrderSeq = pas.OrderSeq)
               INNER JOIN AT_Order ao ON (ao.OrderSeq = o.OrderSeq)
        WHERE  pc.EnterpriseId = e.EnterpriseId
        AND    pc.AddUpFixedMonth = ec.FixedMonth
        AND    pas.PayingControlSeq = pc.Seq
        AND    ao.ExtraPayType = 1
        AND    o.ClaimSendingClass <> 11
       ) AS ClaimFeeExtraPay1BS
,      (-1) * (SELECT SUM(can.RepayTotal)
        FROM   T_PayingControl pc
               INNER JOIN T_PayingAndSales pas ON (pas.PayingControlSeq = pc.Seq)
               INNER JOIN T_Order o ON (o.OrderSeq = pas.OrderSeq)
               INNER JOIN AT_Order ao ON (ao.OrderSeq = o.OrderSeq)
               INNER JOIN T_Cancel can ON (o.OrderSeq = can.OrderSeq)
        WHERE  pc.EnterpriseId = e.EnterpriseId
        AND    pc.AddUpFixedMonth = ec.FixedMonth
        AND    pas.PayingControlSeq = pc.Seq
        AND    ao.ExtraPayType = 1
        AND    o.ClaimSendingClass = 11
       ) AS CalcelAmountExtraPay1DK
,      (-1) * (SELECT SUM(can.RepayTotal)
        FROM   T_PayingControl pc
               INNER JOIN T_PayingAndSales pas ON (pas.PayingControlSeq = pc.Seq)
               INNER JOIN T_Order o ON (o.OrderSeq = pas.OrderSeq)
               INNER JOIN AT_Order ao ON (ao.OrderSeq = o.OrderSeq)
               INNER JOIN T_Cancel can ON (o.OrderSeq = can.OrderSeq)
        WHERE  pc.EnterpriseId = e.EnterpriseId
        AND    pc.AddUpFixedMonth = ec.FixedMonth
        AND    pas.PayingControlSeq = pc.Seq
        AND    ao.ExtraPayType = 1
        AND    o.ClaimSendingClass <> 11
       ) AS CalcelAmountExtraPay1BS
FROM T_Enterprise e INNER JOIN
     T_EnterpriseClaimed ec ON ec.EnterpriseId = e.EnterpriseId
WHERE
     e.EnterpriseId = :EnterpriseId AND
     ec.FixedMonth = :FixedMonth AND
     (
        ec.OrderCount <> 0 OR
        ec.SettlementFee <> 0 OR
        ec.ClaimFee <> 0 OR
        ec.StampFee <> 0 OR
        ec.MonthlyFee <> 0 OR
        ec.CarryOverMonthlyFee <> 0 OR
        ec.CancelRepaymentAmount <> 0 OR
        ec.FfTransferFee <> 0 OR
        ec.AdjustmentAmount <> 0
    )
Q_END;
        $datas1 = $this->app->dbAdapter->query( $sql )->execute( $prm )->current();
        $issueDate = date('Y-m-d', strtotime($datas1['SpanTo'] . '+1 day'));

        // 請求書手数料合計(内自社印刷分)
        $sql = <<<Q_END
SELECT ec.FixedMonth
,      ec.ProcessDate
,      ec.ClaimFee AS FixedClaimFee
,      ec.OrderCount
,      pc.Seq AS PCSeq
,      pc.ClaimFee AS ControlClaimFee
,      pas.Seq AS PASSeq
,      pas.ClaimFee AS SalesClaimFee
,      pas.PayingControlSeq
,      pas.OrderSeq
,      CASE WHEN ch.EnterpriseBillingCode IS NULL THEN 0
           ELSE pas.ClaimFee
       END AS SelfBillingClaimFee
,      CASE WHEN ch.EnterpriseBillingCode IS NULL THEN 0
            ELSE 1
       END AS HasInfo
FROM   T_EnterpriseClaimed ec
       INNER JOIN T_PayingControl pc ON     pc.EnterpriseId = ec.EnterpriseId
                                        AND pc.AddUpFixedMonth = ec.FixedMonth
                                        AND pc.AddUpFlg = 1
       INNER JOIN T_PayingAndSales pas ON pas.PayingControlSeq = pc.Seq
       LEFT OUTER JOIN T_Order o ON o.OrderSeq = pas.OrderSeq
       LEFT OUTER JOIN T_ClaimControl cc ON cc.OrderSeq = o.OrderSeq
       LEFT OUTER JOIN T_ClaimHistory ch ON ch.OrderSeq = o.P_OrderSeq
WHERE  1 = 1
AND    ec.EnterpriseId = :EnterpriseId
AND    ec.FixedMonth = :FixedMonth
AND    ch.Seq = (SELECT MIN(Seq) FROM T_ClaimHistory ch2 where ch2.OrderSeq = o.P_OrderSeq)
Q_END;
        $ri = $this->app->dbAdapter->query($sql)->execute($prm);
        $rows = ResultInterfaceToArray($ri);
        $fee_total = 0;
        $has_fee = false;
        foreach($rows as $row) {
            if($row['HasInfo']) $has_fee = true;
            $fee_total += (int)$row['SelfBillingClaimFee'];
        }
        $selfBillingInfo = array(
                'HasInfo' => $has_fee,
                'ClaimFee' => $fee_total * -1
        );

        // お取引明細
        $sql = <<<Q_END
SELECT pc.ExecDate
,      e.EnterpriseNameKj
,      ec.FixedMonth
,      pc.ChargeCount
,      s.SiteNameKj
,      o.OrderId
,      o.Ent_OrderId
,      c.NameKj
,      o.ReceiptOrderDate
,      ( SELECT MAX( Deli_JournalIncDate ) FROM T_OrderItems WHERE OrderSeq = o.OrderSeq ) AS Deli_JournalIncDate
,      pc.FixedDate
,      pas.UseAmount
,      ( -1 * pas.SettlementFee ) AS SettlementFee
,      ( -1 * pas.ClaimFee ) AS ClaimFee
,      0 AS StampFee
,      (
        ( pas.UseAmount ) +
        ( -1 * pas.SettlementFee ) +
        ( -1 * pas.ClaimFee )
       ) AS ChargeAmount
FROM T_PayingControl pc INNER JOIN
     T_EnterpriseClaimed ec ON ec.EnterpriseId = pc.EnterpriseId AND ec.FixedMonth = pc.AddUpFixedMonth INNER JOIN
     T_PayingAndSales pas ON pas.PayingControlSeq = pc.Seq INNER JOIN
     T_Order o ON o.OrderSeq = pas.OrderSeq LEFT OUTER JOIN
     T_StampFee sf ON sf.OrderSeq = o.OrderSeq INNER JOIN
     T_Customer c ON c.OrderSeq = o.OrderSeq INNER JOIN
     T_Enterprise e ON e.EnterpriseId = pc.EnterpriseId INNER JOIN
     T_Site s ON s.SiteId = o.SiteId
WHERE
     ec.EnterpriseId = :EnterpriseId AND
     ec.FixedMonth = :FixedMonth AND
     pc.ChargeCount > 0 AND
     pc.AddUpFlg = 1
ORDER BY
     o.SiteId, o.OrderId
Q_END;
        $ri = $this->app->dbAdapter->query( $sql )->execute( $prm );
        $datas2 = $this->_grouping(ResultInterfaceToArray($ri), 'SiteNameKj');

        // プラン名取得
        // キャンペーン期間中はキャンペーン情報を表示する
        // 加盟店に紐づくサイトを取得
        $mdls = new TableSite($this->app->dbAdapter);
        $sid = $mdls->getValidAll($entId)->current()['SiteId'];
        // キャンペーン OR マスタの情報を取得
        $logic = new LogicCampaign($this->app->dbAdapter);
        $campaign = $logic->getCampaignInfo($entId, $sid);

        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
        $planName = $codeMaster->getPlanCaption($campaign['Plan']);

        // 印紙代明細
        $sql = <<<Q_END
SELECT pc.ExecDate
,      e.EnterpriseNameKj
,      ec.FixedMonth
,      pc.StampFeeCount
,      s.SiteNameKj
,      o.OrderId
,      o.Ent_OrderId
,      c.NameKj
,      o.ReceiptOrderDate
,      pc2.FixedDate AS FixedDate2
,      o.UseAmount
,      sf.StampFee
FROM T_PayingControl pc INNER JOIN
     T_EnterpriseClaimed ec ON ec.EnterpriseId = pc.EnterpriseId AND ec.FixedMonth = pc.AddUpFixedMonth INNER JOIN
     T_StampFee sf ON sf.PayingControlSeq = pc.Seq INNER JOIN
     T_Order o ON o.OrderSeq = sf.OrderSeq INNER JOIN
     T_Customer c ON c.OrderSeq = o.OrderSeq INNER JOIN
     T_Enterprise e ON e.EnterpriseId = pc.EnterpriseId INNER JOIN
     T_Site s ON s.SiteId = o.SiteId LEFT OUTER JOIN
     T_PayingAndSales pas ON pas.OrderSeq = o.OrderSeq LEFT OUTER JOIN
     T_PayingControl pc2 ON pc2.Seq = pas.PayingControlSeq
WHERE
     ec.EnterpriseId = :EnterpriseId AND
     ec.FixedMonth = :FixedMonth AND
     pc.AddUpFlg = 1 AND
     sf.ClearFlg = 1
ORDER BY
    o.SiteId, o.OrderId
Q_END;
        $ri = $this->app->dbAdapter->query( $sql )->execute( $prm );
        $datas3 = $this->_grouping(ResultInterfaceToArray($ri), 'SiteNameKj');

        // キャンセル明細
        $sql = <<<Q_END
SELECT pc.ExecDate
,      e.EnterpriseNameKj
,      ec.FixedMonth
,      pc.CancelCount
,      s.SiteNameKj
,      o.OrderId
,      o.Ent_OrderId
,      c.NameKj
,      o.ReceiptOrderDate
,      pc2.FixedDate AS FixedDate2
,      cncl.CancelDate
,      o.UseAmount
,      ( -1 * cncl.RepayTotal ) AS RepayTotal
FROM T_PayingControl pc INNER JOIN
     T_EnterpriseClaimed ec ON ec.EnterpriseId = pc.EnterpriseId AND ec.FixedMonth = pc.AddUpFixedMonth INNER JOIN
     T_Cancel cncl ON cncl.PayingControlSeq = pc.Seq INNER JOIN
     T_Order o ON o.OrderSeq = cncl.OrderSeq INNER JOIN
     T_Customer c ON c.OrderSeq = o.OrderSeq INNER JOIN
     T_Enterprise e ON e.EnterpriseId = pc.EnterpriseId INNER JOIN
     T_Site s ON s.SiteId = o.SiteId LEFT OUTER JOIN
     T_PayingAndSales pas ON pas.OrderSeq = o.OrderSeq LEFT OUTER JOIN
     T_PayingControl pc2 ON pc2.Seq = pas.PayingControlSeq
WHERE
     ec.EnterpriseId = :EnterpriseId AND
     ec.FixedMonth = :FixedMonth AND
     pc.AddUpFlg = 1 AND
     cncl.KeepAnAccurateFlg = 1 AND
     cncl.RepayTotal <> 0 AND
     cncl.ValidFlg = 1
ORDER BY
     o.SiteId, o.OrderId
Q_END;
        $ri = $this->app->dbAdapter->query( $sql )->execute( $prm );
        $datas4 = $this->_grouping(ResultInterfaceToArray($ri), 'SiteNameKj');

        // 調整額内訳明細
        $sql = <<<Q_END
SELECT pc.ExecDate
,      e.EnterpriseNameKj
,      ec.FixedMonth
,      pc.AdjustmentCount
,      o.OrderId
,      o.Ent_OrderId
,      c.NameKj
,      o.ReceiptOrderDate
,      ( SELECT KeyContent FROM M_Code WHERE CodeId = 89 AND KeyCode = aa.ItemCode ) AS ItemCodeName
,      aa.AdjustmentAmount
FROM T_PayingControl pc INNER JOIN
     T_EnterpriseClaimed ec ON ec.EnterpriseId = pc.EnterpriseId AND ec.FixedMonth = pc.AddUpFixedMonth INNER JOIN
     T_AdjustmentAmount aa ON aa.PayingControlSeq = pc.Seq INNER JOIN
     T_Enterprise e ON e.EnterpriseId = pc.EnterpriseId LEFT OUTER JOIN
     T_Order o ON o.OrderSeq = aa.OrderSeq LEFT OUTER JOIN
     T_Customer c ON c.OrderSeq = aa.OrderSeq LEFT OUTER JOIN
     T_PayingAndSales pas ON pas.OrderSeq = o.OrderSeq LEFT OUTER JOIN
     T_PayingControl pc2 ON pc2.Seq = pas.PayingControlSeq
WHERE
     ec.EnterpriseId = :EnterpriseId AND
     ec.FixedMonth = :FixedMonth
ORDER BY
     aa.SerialNumber
Q_END;
        $ri = $this->app->dbAdapter->query($sql)->execute($prm);
        $datas5 = ResultInterfaceToArray($ri);

// 2015/10/29 Y.Suzuki Del 立替精算戻し明細の印刷処理は行わない Stt
//         // 立替精算戻し明細
//         $sql = <<<Q_END
// SELECT pc.ExecDate
// ,      e.EnterpriseNameKj
// ,      ec.FixedMonth
// ,      pc.PayBackCount
// ,      s.SiteNameKj
// ,      o.OrderId
// ,      o.Ent_OrderId
// ,      c.NameKj
// ,      o.ReceiptOrderDate
// ,      ( SELECT MAX( Deli_JournalIncDate ) FROM T_OrderItems WHERE OrderSeq = o.OrderSeq ) AS Deli_JournalIncDate
// ,      pc2.FixedDate AS FixedDate2
// ,      pas.UseAmount
// ,      cc.ReceiptAmountTotal
// ,      pbc.PayBackAmount
// FROM T_PayingControl pc INNER JOIN
//      T_EnterpriseClaimed ec ON ec.EnterpriseId = pc.EnterpriseId AND ec.FixedMonth = pc.AddUpFixedMonth INNER JOIN
//      T_PayingAndSales pas ON pas.PayingControlSeq = pc.Seq INNER JOIN
//      T_PayingBackControl pbc ON pbc.PayingControlSeq = pc.Seq AND pas.OrderSeq = pbc.OrderSeq INNER JOIN
//      T_Order o ON o.OrderSeq = pbc.OrderSeq INNER JOIN
//      T_Customer c ON c.OrderSeq = o.OrderSeq INNER JOIN
//      T_Enterprise e ON e.EnterpriseId = pc.EnterpriseId INNER JOIN
//      T_Site s On s.SiteId = o.SiteId LEFT OUTER JOIN
//      T_ClaimControl cc ON o.OrderSeq = cc.OrderSeq LEFT OUTER JOIN
//      T_PayingControl pc2 ON pc2.Seq = pas.PayingControlSeq
// WHERE
//      ec.EnterpriseId = :EnterpriseId AND
//      ec.FixedMonth = :FixedMonth
// ORDER BY
//      o.SiteId, o.OrderId
// Q_END;
//         $ri = $this->app->dbAdapter->query($sql)->execute($prm);
//         $datas6 = $this->_grouping(ResultInterfaceToArray($ri), 'SiteNameKj');
// 2015/10/29 Y.Suzuki Del 立替精算戻し明細の印刷処理は行わない End

        // OEMデータ
        $oemData = $this->app->getCurrentOemData();

        //届いてから決済を利用するサイトが紐付いているか
        $sql = "SELECT COUNT(1) AS cnt FROM T_Site WHERE EnterpriseId = :EnterpriseId AND PaymentAfterArrivalFlg = 1";
        $cnt = $this->app->dbAdapter->query($sql)->execute(array(':EnterpriseId' => $entId))->current()['cnt'];
        if(isset($cnt)){
            $this->view->assign( 'PaymentAfterArrivalFlg', 0 < $cnt ? 1 : 0);
        }

        // PDF印刷
        $fileName = sprintf( 'Seikyu_%s_%s.pdf', date( "YmdHis" ), $entId );

        $this->view->assign( 'datas', $datas1 );                        // 請求書兼領収書
        $this->view->assign( 'selfBillingInfo', $selfBillingInfo );     // 請求書兼領収書の請求書手数料合計(内自社印刷分)
        $this->view->assign( 'issueDate', $issueDate );                 // 発行日
        $this->view->assign( 'datas2', $datas2 );                       // お取引明細
        $this->view->assign( 'planName', $planName );                   // プラン名
        $this->view->assign( 'datas3', $datas3 );                       // 印紙代明細
        $this->view->assign( 'datas4', $datas4 );                       // キャンセル明細
        $this->view->assign( 'datas5', $datas5 );                       // 調整額内訳明細
        $this->view->assign( 'documentRoot', $_SERVER['DOCUMENT_ROOT'] );
        $this->view->assign( 'logo', $oemData['SmallLogo'] );           // ロゴ小
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
        $fname_html = ($tempDir . '/__tmp_' . $this->app->authManager->getUserInfo()->EnterpriseId . '_' . $this->app->authManager->getUserInfo()->LoginId . '__.html');
        $fname_pdf  = ($tempDir . '/__tmp_' . $this->app->authManager->getUserInfo()->EnterpriseId . '_' . $this->app->authManager->getUserInfo()->LoginId . '__.pdf');

        // HTML出力
        file_put_contents($fname_html, $html);

        // PDF変換(外部プログラム起動)
        $ename = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'wkhtmltopdf');
        $option = " --margin-left 0 --margin-right 0 --margin-top 0 --margin-bottom 0 ";
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
}