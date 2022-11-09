<?php
namespace cbadmin\Controller;

use Zend\Db\ResultSet\ResultSet;
use Coral\Base\Reflection\BaseReflectionUtility;
use Coral\Base\BaseHtmlUtils;
use Coral\Coral\Controller\CoralControllerAction;
use Coral\Coral\CoralCodeMaster;
use Coral\Coral\CoralPager;
use Coral\Coral\Validate\CoralValidateUtility;
use cbadmin\Application;
use cbadmin\classes\CachedEnterpriseInfo;
use models\Table\TableCreditPoint;
use models\Table\TableOrderItems;
use models\Table\TableOrder;
use models\Table\TableGeneralPurpose;
use models\Table\TableCjResult;
use models\Table\TableCjResultDetail;
use models\Table\TableSite;
use models\View\ViewOrderCustomer;
use models\View\ViewDelivery;
use models\Table\TableCode;
use models\Table\TableDeliveryDestination;
use models\Table\TablePostalCode;
use models\Logic\MergeOrder\LogicMergeOrderHelper;
use Coral\Coral\History\CoralHistoryOrder;
use Coral\Coral\Mail\CoralMail;
use Coral\Coral\Mail\CoralMailException;
use Coral\Base\BaseGeneralUtils;
use models\Table\TableJtcResult;
use models\Table\TableJtcResultDetail;
use models\Table\TableCustomer;
use models\Table\ATableOrder;
use models\Table\TableBusinessCalendar;
use models\Table\TableEnterprise;
use models\Table\TableSystemProperty;

class RwcreditController extends CoralControllerAction
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
        ->addJavaScript('../js/prototype.js');

        $this->setPageTitle("後払い.com - 社内与信");
	}

	/**
	 * 社内与信実行待ちリストを表示する。
	 */
	public function atlistAction()
	{
        $mdls = new ViewOrderCustomer($this->app->dbAdapter);
        $mdlsDeli = new ViewDelivery($this->app->dbAdapter);
        $mdldd = new TableDeliveryDestination($this->app->dbAdapter);

$sql = <<<EOQ
SELECT voc.*
     , e.DispDecimalPoint
     , e.UseAmountFractionClass
     , o.T_OrderClass
FROM V_OrderCustomer voc
     INNER JOIN T_Enterprise e ON (e.EnterpriseId = voc.EnterpriseId )
     INNER JOIN T_Order o ON (o.OrderSeq = voc.OrderSeq)
     INNER JOIN AT_Order ao ON (o.OrderSeq = ao.OrderSeq)
WHERE voc.DataStatus = 11
AND voc.Cnl_Status = 0
AND ao.DefectFlg = 0
ORDER BY OrderSeq
EOQ;
        $datas = $this->app->dbAdapter->query($sql)->execute(null);
        $rs = new ResultSet();
        $rs->initialize($datas);
        $datas = $rs->toArray();

        // count関数対策
        $datasLen = 0;
        if(!empty($datas)) {
            $datasLen = count($datas);
        }

        // 与信確定待ち件数
        $this->view->assign("listcount", $datasLen);

        for ($i = 0 ; $i < $datasLen ; $i++)
        {
            // 注文SeqをもったHIDDENタグ
            $datas[$i]["OrderSeqHidden"] = '<input type="hidden" name="OrderSeq' . $i . '" id="OrderSeq' . $i . '" value="' . $datas[$i]["OrderSeq"]. '" />';

            // 住所は先頭8文字までを表示
            $datas[$i]["UnitingAddress"] = mb_substr($datas[$i]["UnitingAddress"], 0, 8, 'UTF-8');

            // 注文日時
            $orderDate = (!is_null($datas[$i]["ReceiptOrderDate"])) ?
                date('Y-m-d', strtotime($datas[$i]["ReceiptOrderDate"])) : null;

            // 配送先氏名・住所
//             $destData = $mdlsDeli->findDelivery(array("OrderSeq" => $datas[$i]["OrderSeq"], "DataClass" => 1))->current();
//             $datas[$i]["DestNameKj"] = $destData['DestNameKj'];
//             $datas[$i]["DestUnitingAddress"] = mb_substr($destData['UnitingAddress'], 0, 8, 'UTF-8');

                $sql = <<<EOQ
        SELECT
               dd.DestNameKj
        ,      dd.UnitingAddress
        FROM
               T_DeliveryDestination dd
               INNER JOIN T_OrderItems oi ON (oi.DeliDestId = dd.DeliDestId)
        WHERE  1 = 1
        AND    oi.OrderSeq = :OrderSeq
        AND    oi.DataClass = 1
EOQ;
        // SQL実行
                $ri = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $datas[$i]["OrderSeq"]))->current();
                $datas[$i]["DestNameKj"] = $ri['DestNameKj'];
                $datas[$i]["DestUnitingAddress"] = mb_substr($ri['UnitingAddress'], 0, 8, 'UTF-8');
        }


        $this->view->assign('list', $datas);

        return $this->view;
	}

// Del By Takemasa(NDC) 20150629 Stt 廃止
// 	/**
// 	 * 社内与信実行
// 	 *
// 	 */
// 	public function atdoneAction()
//
// 	/**
// 	 * 社内与信をパラメーターで指定された注文についてのみ実行する。
// 	 *
// 	 * @return string JSONでの処理結果ステータス
// 	 */
// 	public function atdoneonceAction()
// Del By Takemasa(NDC) 20150629 End 廃止

	/**
	 * 確定待ちリストを表示する。
	 */
	public function listAction()
	{
	    $mdlcp = new TableCreditPoint($this->app->dbAdapter);
		$mdls = new ViewOrderCustomer($this->app->dbAdapter);
		$mdlsDeli = new ViewDelivery($this->app->dbAdapter);
		$datas =  ResultInterfaceToArray($mdls->findByDs15());

		$bluePoint = $mdlcp->findCreditPoint(0, 1)->current()->Point;		// 安心ポイント
		$redPoint = $mdlcp->findCreditPoint(0, 2)->current()->Point;		// 心配ポイント

        // 与信確定待ち件数
        // count関数対策
        $datasLen = 0;
        if(!empty($datas)) {
            $datasLen = count($datas);
        }

		$this->view->assign("listcount", $datasLen);

		for ($i = 0 ; $i < $datasLen ; $i++)
		{
			// セル背景色の設定
			$scoreTotal = (int)$datas[$i]["Incre_ScoreTotal"];
			if ($scoreTotal >= $bluePoint)
			{
				$datas[$i]["CelStyle"] = 'style="background-color: #CCFFFF;"';
				$datas[$i]["CelStyleScore"] = 'style="background-color: #CCFFFF; font-size: 18px;"';
			}
			else if ($scoreTotal <= $redPoint)
			{
				$datas[$i]["CelStyle"] = 'style="background-color: #FFCCCC;"';
				$datas[$i]["CelStyleScore"] = 'style="background-color: #FFCCCC; font-size: 18px;"';
			}
			else
			{
				$datas[$i]["CelStyle"] = '';
				$datas[$i]["CelStyleScore"] = 'style="font-size: 18px;"';
			}


			// 注文SeqをもったHIDDENタグ
			$datas[$i]["OrderSeqHidden"] = '<input type="hidden" name="OrderSeq' . $i . '" id="OrderSeq' . $i . '" value="' . $datas[$i]["OrderSeq"]. '" />';

			// 住所は先頭8文字までを表示
			$datas[$i]["UnitingAddress"] = mb_substr($datas[$i]["UnitingAddress"], 0, 8, 'UTF-8');

			// 与信判断
			$datas[$i]["CreditJudgeTag"] = BaseHtmlUtils::SelectTag(
				"Incre_Status" . $i,
				array(0 => '未判断', 1 => 'OK', -1 => 'NG'),
				$datas[$i]["Incre_Status"]);

			// 注文日時
			$orderDate = $datas[$i]["ReceiptOrderDate"];
			$datas[$i]["ReceiptOrderDate"] = date('Y/m/d', $orderDate);

			// 配送先氏名・住所
			//$destData = $mdlsDeli->findByOrderSeq($datas[$i]["OrderSeq"])->current();
			$destData = $mdlsDeli->findDelivery(array("OrderSeq" => $datas[$i]["OrderSeq"], "DataClass" => 1))->current();
			$datas[$i]["DestNameKj"] = $destData->DestNameKj;
			$datas[$i]["DestUnitingAddress"] = mb_substr($destData->UnitingAddress, 0, 8, 'UTF-8');
		}
		$this->view->assign('list', $datas);

		return $this->view;
	}

	/**
	 * 与信確定待ちリスト２を表示する。
	 *
	 */
	public function list2Action()
	{
        $this->addStyleSheet('../css/cbadmin/rwcredit/list2/default.css')
        ->addStyleSheet('../css/cbadmin/rwcredit/list2/tooltip.css');

        $this->addJavaScript('../js/corelib.js')
        ->addJavaScript('../js/bytefx.js')
        ->addJavaScript('../js/tooltip.js');

        // [paging] CoralPagerのロードと必要なCSS/JSのアサイン
        $this->addStyleSheet('../css/base.ui.customlist.css');
        $this->addJavaScript('../js/base.ui.js');
        $this->addJavaScript('../js/base.ui.customlist.js');

        $this->addJavaScript( '../js/fixednote.js' );

        // [paging] 1ページあたりの項目数
        // ※：config.iniからの取得を追加（08.04.03）
        $cn = $this->getControllerName();
        $ipp = (isset($this->app->paging_conf)) ? $this->app->paging_conf[$cn] : 20;
        if( ! BaseReflectionUtility::isPositiveInteger($ipp) ) $ipp = 20;

        // [paging] 指定ページを取得
        $params = $this->getParams();

        $current_page = (isset($params['page'])) ? (int)$params['page'] : 1;
        if( $current_page < 1 ) $current_page = 1;

        // 類似住所検索結果の色分け用CSSのアサイン
        $filename = (isset($this->app->tools['orderstatus']['style'])) ? $this->app->tools['orderstatus']['style'] : 'default';
        $this->addStyleSheet( '../css/cbadmin/orderstatus/' . $filename . '.css');

        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
        $mdlcp = new TableCreditPoint($this->app->dbAdapter);
        $mdloi = new TableOrderItems($this->app->dbAdapter);
        $mdls = new ViewOrderCustomer($this->app->dbAdapter);
        $mdlo = new TableOrder( $this->app->dbAdapter );
        $mdlsDeli = new ViewDelivery($this->app->dbAdapter);
        $mdlc = new TableCode($this->app->dbAdapter);
        $cjResult = new TableCjResult($this->app->dbAdapter);
        $cjResultDetail = new TableCjResultDetail($this->app->dbAdapter);
        $mdljtc = new TableJtcResult($this->app->dbAdapter);
        $mdljtcdtl = new TableJtcResultDetail($this->app->dbAdapter);
        $mdlcl = new \models\Table\TableCreditLog($this->app->dbAdapter);

        // 事業者IDによる絞込み
        if (isset($params['loginid']) && ($params['loginid'] != '')) {
            if ($params['mode'] == 'reserve') {
                $sql = " SELECT voc.*, e.DispDecimalPoint, e.UseAmountFractionClass, o.T_OrderClass FROM V_OrderCustomer voc INNER JOIN T_Enterprise e ON (e.EnterpriseId = voc.EnterpriseId ) INNER JOIN T_Order o ON (o.OrderSeq = voc.OrderSeq) WHERE voc.DataStatus = 21 AND voc.Cnl_Status = 0 AND voc.EnterpriseLoginId like :EnterpriseLoginId ORDER BY OrderSeq ";
            }
            else {
                $sql = " SELECT voc.*, e.DispDecimalPoint, e.UseAmountFractionClass, o.T_OrderClass FROM V_OrderCustomer voc INNER JOIN T_Enterprise e ON (e.EnterpriseId = voc.EnterpriseId ) INNER JOIN T_Order o ON (o.OrderSeq = voc.OrderSeq) WHERE voc.DataStatus = 15 AND voc.Cnl_Status = 0 AND voc.EnterpriseLoginId like :EnterpriseLoginId ORDER BY OrderSeq ";
            }
            $ri = $this->app->dbAdapter->query($sql)->execute(array(':EnterpriseLoginId' => ('%' . $params['loginid'])));
        }
        else {
            if ($params['mode'] == 'reserve') {
                $ri = $mdls->findByDs21();
            }
            else {
                $ri = $mdls->findByDs15();
            }
        }
        $datas = ResultInterfaceToArray($ri);   // 与信確定待ち注文データ

        // サイト情報取得モデルの初期化（2011.6.27 eda）
        $mdlSite = new TableSite($this->app->dbAdapter);

        // 注文経過時間・高額注文リスト
        // パラメータ 設定
        $elTime['time'] = 90;    // 経過時間
        $money['price'] = 20;  // 金額
        $elTime['count'] = 0;
        $money['count'] = 0;

        // count関数対策
        $datasLen = 0;
        if(!empty($datas)) {
            $datasLen = count($datas);
        }

        for ($i = 0 ; $i < $datasLen ; $i++) {
            $TimeSql = " SELECT max(RegistDate) AS RegistDate FROM T_OrderHistory WHERE OrderSeq = :OrderSeq AND HistoryReasonCode IN (11, 12, 13, 14) ";
            $latestTime[$i] = $this->app->dbAdapter->query($TimeSql)->execute(array(':OrderSeq' => $datas[$i]['OrderSeq']))->current();
            $fromSec = strtotime($latestTime[$i]['RegistDate']);
            $toSec = strtotime("now");
            $diff = $toSec - $fromSec;
            // パラメータで設定した経過時間を超過した注文リスト
            if ($diff >= $elTime['time'] * 60){
                $elTime['count'] += 1;
                if ($elTime['count'] <= 12) {
                    if (!isset($elTime['OrderId'])){
                        $elTime['OrderId'] = $datas[$i]['OrderId'];
                    } else {
                        $elTime['OrderId'] = $elTime['OrderId'] . ", " . $datas[$i]['OrderId'];
                    }
                }
            }
            // パラメータで設定した金額を超過した注文リスト
            if ($datas[$i]['UseAmount'] >= $money['price'] * 10000) {
                $money['count'] += 1;
                if ($money['count'] <= 12) {
                    if (!isset($money['OrderId'])){
                        $money['OrderId'] = $datas[$i]['OrderId'];
                    } else {
                    $money['OrderId'] = $money['OrderId'] . ", " . $datas[$i]['OrderId'];
                    }
                }
            }
        }

        // 社内与信保留画面の場合には、リスト表示を行わない
        if ($params['mode'] == 'reserve') {
            $elTime['count'] = 0;
            $money['count'] = 0;
        }

        // 経過時間リスト・高額リスト アサイン
        $this->view->assign('money', $money);
        $this->view->assign('elTime', $elTime);

        // [paging] ページャ初期化
        $pager = new CoralPager( $datasLen, $ipp );
        // [paging] 指定ページを補正
        if( $current_page > $pager->getTotalPage() ) $current_page = $pager->getTotalPage();
        // [paging] 対象リストをページング情報に基づいて対象リストをスライス
        if( $datasLen > 0 ) $datas = array_slice( $datas, $pager->getStartIndex( $current_page ), $ipp );
        // [paging] 経過時間リストをページング情報に基づいて対象リストをスライス
        if( !empty($latestTime) ) $latestTime = array_slice( $latestTime, $pager->getStartIndex( $current_page ), $ipp );
        // [paging] ページングナビゲーション情報
        $page_links = array( 'base' => 'rwcredit/list2/mode/' . f_e($params['mode']) . '/loginid/' . f_e($params['loginid']) . '/page' );
        $page_links['prev'] = $page_links['base'] . '/' . ( $current_page - 1 );
        $page_links['next'] = $page_links['base'] . '/' . ( $current_page + 1 );

        // [paging] ページング関連の情報をビューへアサイン
        $this->view->assign( 'current_page', $current_page );
        $this->view->assign( 'pager', $pager );
        $this->view->assign( 'page_links', $page_links );

        $bluePoint = $mdlcp->findCreditPoint(0, 1)->current()['Point'];    // 安心ポイント
        $redPoint  = $mdlcp->findCreditPoint(0, 2)->current()['Point'];    // 心配ポイント

        // 与信確定待ち件数
        // count関数対策
        $datasLen = 0;
        if(!empty($datas)) {
            $datasLen = count($datas);
        }

        $this->view->assign("listcount", $datasLen);

        // 社内条件クラスの取得
        $captionArray = $mdlc->getMasterByClass(4);
        $rs = new ResultSet();
        $rs->initialize($captionArray);
        $captionArray = $rs->toArray();

        $order_seq_where = "";

        //審査システム用にSeqのみ取り出したい
        $buf = array();
        foreach($datas as $dt) {
            $buf[] = $dt['OrderSeq'];
        }
        $order_seq_where = join(',', $buf);

        //審査システム用データ取得cjResult
        $cjResult_data = array();
        if ($order_seq_where != '') {
            $cjResult_data = ResultInterfaceToArray($cjResult->orderSeqSearch($order_seq_where));
        }

        //OrderSeqをキーとする
        foreach($cjResult_data as $value){
            $cjResult_key_data[$value['OrderSeq']] = $value;
        }

        //審査システム用データ詳細取得cjDetail
        $cjResultDetail_data = array();
        if ($order_seq_where != '') {
            $cjResultDetail_data = ResultInterfaceToArray($cjResultDetail->orderSeqSearch($order_seq_where));
        }

        //OrderSeqをキーとしデータ整形する
        $cjResult_detail_key_data = array();
        foreach($cjResultDetail_data as $value){

            //データがすでにセットされているか？
            if(isset($cjResult_detail_key_data[$value['OrderSeq']])){
                $cjResult_detail_key_data[$value['OrderSeq']] .= "/「".$value['DetectionPatternName']."」".nvl($value['DetectionPatternScoreWeighting'], $value['DetectionPatternScore']);
            }else{
                //セットされていない場合データ整形
                $remarks = "「".$value['DetectionPatternName']."」".nvl($value['DetectionPatternScoreWeighting'], $value['DetectionPatternScore']);
                $cjResult_detail_key_data[$value['OrderSeq']] = $remarks;

            }

        }

        $mdlpostalcode = new TablePostalCode($this->app->dbAdapter);

        $entInfo = new CachedEnterpriseInfo($this->app->dbAdapter);

        // 注文ループ開始
        for ($i = 0 ; $i < $datasLen ; $i++)
        {
            // 注文SeqをもったHIDDENタグ
            $datas[$i]["OrderSeqHidden"] = '<input type="hidden" name="OrderSeq' . $i . '" id="OrderSeq' . $i . '" value="' . $datas[$i]["OrderSeq"]. '" />';

            // （類似住所検索用）住所は先頭8文字までを表示
            $datas[$i]["ShortUnitingAddress"] = mb_substr($datas[$i]["UnitingAddress"], 0, $this->app->tools['prop']['chkaddrstrnum'], 'UTF-8');

            $datas[$i]["CreditJudgeTag"] = BaseHtmlUtils::InputRadioTag(
                "Incre_Status" . $i,
                array(1 => 'OK', 0 => '保留', -1 => 'NG'),
                $datas[$i]["Incre_Status"],
                false,
                true,
                "Rwcredit"
            );

            $orderDate = (!is_null($datas[$i]["ReceiptOrderDate"])) ?
                date('Y-m-d', strtotime($datas[$i]["ReceiptOrderDate"])) : null;
            $datas[$i]["ReceiptOrderDate"] = $orderDate;

            // スコアによる背景色の変更
            $totalScore = (int)$datas[$i]["Incre_ScoreTotal"];
            if ($totalScore >= $bluePoint)
            {
                // 青点
                $datas[$i]['ScoreStyle'] = 'bluescore';
            }
            else if ($totalScore <= $redPoint)
            {
                // 赤点
                $datas[$i]['ScoreStyle'] = 'redscore';
            }
            else
            {
                // ノーマル
                $datas[$i]['ScoreStyle'] = '';
            }

            // 配送先氏名・住所
            $destData = $mdlsDeli->findDelivery(array("OrderSeq" => $datas[$i]["OrderSeq"], "DataClass" => 1))->current();
            $datas[$i]["DestNameKj"] = $destData['DestNameKj'];
            $datas[$i]["DestNameKn"] = $destData['DestNameKn'];
            $datas[$i]["DestUnitingAddress"] = $destData['UnitingAddress'];
            $datas[$i]["DestPostalCode"] = $destData['PostalCode'];
            $datas[$i]["Incre_DestNameScore"] = $destData['Incre_NameScore'];
            $datas[$i]["Incre_DestNameNote"] = $destData['Incre_NameNote'];
            $datas[$i]["Incre_DestAddressScore"] = $destData['Incre_AddressScore'];
            $datas[$i]["Incre_DestAddressNote"] = $destData['Incre_AddressNote'];
            $datas[$i]["Incre_SameCnAndAddrScore"] = $destData['Incre_SameCnAndAddrScore'];
            $datas[$i]["Incre_SameCnAndAddrNote"] = $destData['Incre_SameCnAndAddrNote'];
            $datas[$i]["Incre_DestPostalCodeScore"] = $destData['Incre_PostalCodeScore'];
            $datas[$i]["Incre_DestPostalCodeNote"] = $destData['Incre_PostalCodeNote'];
            $datas[$i]["DestPhone"] = $destData['Phone'];
            $datas[$i]["Incre_DestTelScore"] = $destData['Incre_TelScore'];
            $datas[$i]["Incre_DestTelNote"] = $destData['Incre_TelNote'];

            // サイト情報（2011.6.27 eda）
            $siteInfo = $mdlSite->findSiteBySiteName($datas[$i]['EnterpriseId'], $datas[$i]['SiteNameKj'])->current();
            if($siteInfo) {
                $datas[$i]['SiteUrl'] = $siteInfo['Url'];
            }

            // 注文商品
            unset($items);
            $deliveryFee = 0;
            $settlementFee = 0;
            $exTax = 0;
            $totalSumMoney = 0;
            $itemsNeta = $mdloi->findByOrderSeq($datas[$i]["OrderSeq"]);

            //返却キャンセル数の取得
            $order = $mdlo->searchSaikenCount($datas[$i]["EnterpriseId"]);

            $datas[$i]["saikenCount"] = $order;

            foreach ($itemsNeta as $item)
            {
                switch((int)$item['DataClass'])
                {
                    case 2:	// 送料
                        $deliveryFee = $item['SumMoney'];
                        break;
                    case 3:	// 手数料
                        $settlementFee = $item['SumMoney'];
                        break;
                    case 4:	// 外税額
                        $exTax = $item['SumMoney'];
                        break;
                    default:
                        $items[] = $item;
                        $totalSumMoney += $item['SumMoney'];
                        break;
                }
            }

            // 商品の合計＋送料＋手数料＋外税額
            $totalSumMoney += $deliveryFee + $settlementFee + $exTax;

            $datas[$i]['items'] = $items;                   // 注文商品
            $datas[$i]['deliveryFee'] = $deliveryFee;       // 配送料
            $datas[$i]['settlementFee'] = $settlementFee;   // 手数料
            $datas[$i]['exTax'] = $exTax;                   // 外税額
            $datas[$i]['totalSumMoney'] = $totalSumMoney;   // 注文合計額

            // 電話結果SELECTタグ
            $datas[$i]['realCallResultTag'] = BaseHtmlUtils::SelectTag(
                'RealCallResult' . $i,
                $codeMaster->getCallResultMaster(),
                $datas[$i]['RealCallResult'],
                sprintf('disabled onChange="javascript:getRcPoint(%d);"', $i)
            );

            // メールチェック結果SELECTタグ
            $datas[$i]['realSendMailResultTag'] = BaseHtmlUtils::SelectTag(
                'RealSendMailResult' . $i,
                $codeMaster->getSendMailResultMaster(),
                $datas[$i]['RealSendMailResult'],
                sprintf('disabled onChange="javascript:getRsPoint(%d);"', $i)
            );

            // 電話履歴SELECTタグ
            $datas[$i]['PhoneHistoryTag'] = BaseHtmlUtils::SelectTag(
                'PhoneHistory' . $i,
                $codeMaster->getPhoneHistoryMaster(),
                $datas[$i]['PhoneHistory'],
                'disabled'
            );

            // e電話帳SELECTタグ
            $datas[$i]['eDenTag'] = BaseHtmlUtils::SelectTag(
                'eDen' . $i,
                $codeMaster->getEDenMaster(),
                $datas[$i]['eDen'],
                'disabled'
            );

            // 請求先、配送先のスコア備考は全角20文字まで(2012.11.20 tkaki)
            // 氏名
            $datas[$i]['ShortIncre_NameNote'] = mb_strimwidth($datas[$i]["Incre_NameNote"], 0, 43,"...", 'UTF-8');
            // 住所
            $datas[$i]['ShortIncre_AddressNote'] = mb_strimwidth($datas[$i]["Incre_AddressNote"], 0, 43,"...", 'UTF-8');
            // メールアドレス
            $datas[$i]['ShortIncre_MailDomainNote'] = mb_strimwidth($datas[$i]["Incre_MailDomainNote"], 0, 43,"...", 'UTF-8');
            // TEL
            $datas[$i]['ShortIncre_TelNote'] = mb_strimwidth($datas[$i]["Incre_TelNote"], 0, 43,"...", 'UTF-8');
            // 事業者ID
            $datas[$i]['ShortIncre_AtnEnterpriseNote'] = mb_strimwidth($datas[$i]["Incre_AtnEnterpriseNote"], 0, 43,"...", 'UTF-8');
            // 配送先
            // 氏名
            $datas[$i]["ShortIncre_DestNameNote"] = mb_strimwidth($datas[$i]["Incre_DestNameNote"], 0, 43,"...", 'UTF-8');
            // 住所
            $datas[$i]["ShortIncre_DestAddressNote"] = mb_strimwidth($datas[$i]["Incre_DestAddressNote"], 0, 43,"...", 'UTF-8');
            // TEL
            $datas[$i]["ShortIncre_DestTelNote"] = mb_strimwidth($datas[$i]["Incre_DestTelNote"], 0, 43,"...", 'UTF-8');
            // TELリピート判定
            $datas[$i]["Incre_ArTel_Caption"] = $datas[$i]["Incre_ArTel"] == 1 ? "(R)" : "";

            //　---- 審査システム結果 -----

            //ポイント　NULL空
            $datas[$i]['Judge_System_Point'] = "";
            if ($cjResult_key_data[$datas[$i]["OrderSeq"]] && !is_null($cjResult_key_data[$datas[$i]["OrderSeq"]]['TotalScoreWeighting'])) {
                $datas[$i]['Judge_System_Point'] = $cjResult_key_data[$datas[$i]["OrderSeq"]]['TotalScoreWeighting'];
                $datas[$i]['Judge_System_Point'] += (int)$this->app->dbAdapter->query(' SELECT IFNULL(Incre_JudgeScoreTotal,0) AS Incre_JudgeScoreTotal FROM T_Order WHERE OrderSeq = :OrderSeq '
                    )->execute(array(':OrderSeq' => $datas[$i]["OrderSeq"]))->current()['Incre_JudgeScoreTotal'];
            }
            else if ($cjResult_key_data[$datas[$i]["OrderSeq"]] && !is_null($cjResult_key_data[$datas[$i]["OrderSeq"]]['TotalScore'])) {
                $datas[$i]['Judge_System_Point'] = $cjResult_key_data[$datas[$i]["OrderSeq"]]['TotalScore'];
                $datas[$i]['Judge_System_Point'] += (int)$this->app->dbAdapter->query(' SELECT IFNULL(Incre_JudgeScoreTotal,0) AS Incre_JudgeScoreTotal FROM T_Order WHERE OrderSeq = :OrderSeq '
                    )->execute(array(':OrderSeq' => $datas[$i]["OrderSeq"]))->current()['Incre_JudgeScoreTotal'];
            }

            //審査システム備考 ポイントがNULLなら「審査システム判定不可」
            $datas[$i]['Judge_System_remarks'] = ($datas[$i]['Judge_System_Point'] === "") ?
                "審査システム判定不可" : $cjResult_detail_key_data[$datas[$i]["OrderSeq"]];

            // 郵便番号カナ文字列の取得
            $datas[$i]['PostalCodeKn'] = $mdlpostalcode->getAddressKanaStr($datas[$i]['PostalCode']);
            $datas[$i]['DestPostalCodeKn'] = $mdlpostalcode->getAddressKanaStr($datas[$i]['DestPostalCode']);

            // 利益率（手数料率－不払い率）
            $datas[$i]['Profitability'] = ($datas[$i]['SettlementFeeRate'] - $entInfo->find($datas[$i]["EnterpriseId"])['NpRate3']);

            // (不足データ補完用)
            $extrarow = $this->app->dbAdapter->query(" SELECT PendingReasonCode FROM T_Order WHERE OrderSeq = :OrderSeq "
                )->execute(array(':OrderSeq' => $datas[$i]['OrderSeq']))->current();

            // 保留理由
            $datas[$i]['reserveReasonTag'] = BaseHtmlUtils::SelectTag(
                'reserveReason' . $i,
                $codeMaster->getReserveReasonMaster(),
                $extrarow['PendingReasonCode'],
                'disabled'
            );

            // (不足データ補完用)
            $extrarow2 = $this->app->dbAdapter->query(" SELECT ManualJudgeNgReasonCode FROM AT_Order WHERE OrderSeq = :OrderSeq "
                )->execute(array(':OrderSeq' => $datas[$i]['OrderSeq']))->current();

            // NG理由
            $datas[$i]['NgReasonTag'] = BaseHtmlUtils::SelectTag(
                'NgReason' . $i,
                $codeMaster->getNGReasonMaster(),
                $extrarow2['ManualJudgeNgReasonCode'],
                'disabled'
            );

            $showNgReason = $mdlSite->findSite($datas[$i]['SiteId'])->current()['ShowNgReason'];
            $datas[$i]['ShowNgReason'] = $showNgReason;

            // ジンテック結果
            $jintec = array();
            $datas[$i]['JintecResult'] = "未判定";
            $datas[$i]['JintecManualJudgeFlg'] = "";

            $row = $mdljtc->findByOrderSeq($datas[$i]['OrderSeq'])->current(); // 最新のジンテック結果を取得
            if ( $row ) {

                // ジンテック
                if (!isset($row['Result'])) {
                    // データが取得出来ない場合は未設定のまま

                } elseif ( $row['Result'] == 1) {
                    $datas[$i]['JintecResult'] = "OK";

                } elseif( $row['Result'] == 2) {
                    $datas[$i]['JintecResult'] = "NG";

                } elseif( $row['Result'] == 0 ) {
                    $datas[$i]['JintecResult'] = "保留";

                }

                $ridtl = $mdljtcdtl->findByJtcSeq($row['Seq']);  // ジンテック結果詳細情報を取得
                foreach ($ridtl as $r) {
                    if (isset($r['ItemId'])) {
                        $jintec[$r['ItemId']] = $r['Value'];
                    }
                }
            }
            // 手動与信候補判定結果（指定注文SEQに関連付けられたT_CreditLogの最新行を取得する）
            $rowCreditLog =  $this->app->dbAdapter->query(" SELECT JintecManualJudgeFlg FROM T_CreditLog WHERE OrderSeq = :OrderSeq ORDER BY Seq DESC LIMIT 1 "
                    )->execute(array(':OrderSeq' => $datas[$i]['OrderSeq']))->current();
            if ($rowCreditLog) {
                $datas[$i]['JintecManualJudgeFlg'] = $rowCreditLog['JintecManualJudgeFlg'] == 1 ? "(注意：強制手動化対象)" : "";
            }
            if (isset($jintec['attention'])) {
                // ｺｰﾄﾞﾏｽﾀｰから日本語に変換
                $jintec['attention'] = $mdlc->find(187, $jintec['attention'])->current()['KeyContent'];
            }

            // 未定義の場合は"---"に置き換え
            $jintec['month'] = isset($jintec['month']) ? $jintec['month'] : "---";
            $jintec['movetel'] = isset($jintec['movetel']) ? $jintec['movetel'] : "---";
            $jintec['carrier'] = isset($jintec['carrier']) ? $jintec['carrier'] : "---";
            $jintec['count'] = isset($jintec['count']) ? $jintec['count'] : "---";
            $jintec['attention'] = isset($jintec['attention']) ? $jintec['attention'] : "---";

            $datas[$i]['JintecDetail'] = $jintec;

            // 社内審査結果
            $datas[$i]['IncreSnapShotString'] = $mdlcl->getIncreSnapShotString($datas[$i]['OrderSeq']);

            // 経過時間
            $fromSec = strtotime($latestTime[$i]['RegistDate']);
            $toSec = strtotime("now");
            $diff = $toSec - $fromSec;

            $time[$i]['diff'] = $diff;
        }
        // 注文ループエンド

        $this->view->assign('list', $datas);
        $this->view->assign('time', $time);

        // JSON形式のポイントのアサイン
        $mdlcp = new TableCreditPoint($this->app->dbAdapter);
        $this->view->assign('rcPoints', \Zend\Json\Json::encode($mdlcp->getRealCallPoints()));
        $this->view->assign('rsPoints', \Zend\Json\Json::encode($mdlcp->getRealSendMailPoints()));

        // 安心ポイントと警戒ポイントのアサイン
        $this->view->assign('bluePoint', $bluePoint);
        $this->view->assign('redPoint', $redPoint);

        // チェックメール送信URLのアサイン
        $this->view->assign('urlSendCheck' , $this->getBaseUrl() . $this->app->tools['url']['sendcheck']);
        // 類似住所チェックURLのアサイン
        $this->view->assign('urlChkAddress', $this->getBaseUrl() . $this->app->tools['url']['chkaddress']);

        // キャッシュ可能な事業者情報検索クラスをビューへ割り当てる
        // （事業者備考表示のため追加 @ 09.06.05 eda）
        $this->view->assign('entInfo', $entInfo);

        // 全体備考
        $row = $this->app->dbAdapter->query(" SELECT Note FROM M_Code WHERE CodeId = 85 AND KeyCode = 0 ")->execute(null)->current();
        $this->view->assign('codemstNote85_0', $row['Note']);

        // 事業者ID
        $this->view->assign('loginid', $params['loginid']);

        // 注文SEQ(ｶﾝﾏ区切り連結)
        $osecsar = array();
        for ($i=0; $i<$datasLen; $i++) {
            $osecsar[] = $datas[$i]['OrderSeq'];
        }
        $osecs = implode(',', $osecsar);
        $this->view->assign('osecs', $osecs);

        // 法人情報
        $mdlcus = new TableCustomer($this->app->dbAdapter);
        for ($i=0; $i<$datasLen; $i++) {
            $Customer[$i] = $mdlcus->findCustomer(array('OrderSeq' => $datas[$i]['OrderSeq']))->current();
        }
        $this->view->assign('Customer', $Customer);

        // 社内与信確定ボタンアクション
        $this->view->assign('postaction', ($params['mode'] == 'reserve') ? "rwcredit/list2/mode/reserve" : "rwcredit/list2/mode/waitconfirm");

        // モード毎タイトル
        if ($params['mode'] == 'reserve') {
            $this->view->assign('title', "社内与信保留リスト　　　与信保留：　");
            $this->view->assign('title_notice', "　　　　　※与信保留分のみ表示");
        }
        else {
            $this->view->assign('title', "社内与信確定待ちリスト　　　与信確定待ち：　");
        }

        // 不払い率背景色しきい値(％)
        $npRateColorThreshold = $this->app->dbAdapter->query(" SELECT PropValue FROM T_SystemProperty WHERE Module = '[DEFAULT]' AND Category = 'systeminfo' AND Name = 'NpRateColorThreshold' ")->execute(null)->current()['PropValue'];
        $this->view->assign('npRateColorThreshold', $npRateColorThreshold);

        return $this->view;
	}

	/**
	 * 社内与信確定
	 */
	public function donejudgeAction()
	{
        $params = $this->getParams();

        $osecs = $params['osecs'];
        $osecs = explode(',', $osecs);
        foreach ($osecs as &$oseq) {
            if (!is_numeric($oseq)) {
                $oseq = -1;
            }
        }
        $osecs = implode(',', $osecs);

        // [Yes, I will ALL !!!]ボタンが押されている時は、権限のチェックが必要
        if (isset($params['Iwill0'])) {

            // 通知された注文SEQが、自分以外のｵﾍﾟﾚｰﾀに握られているかの調査
            $sql =<<<EOQ
SELECT o.OrderId
,      ope.NameKj
FROM   T_CreditLock cl
	   INNER JOIN T_Order o ON (o.OrderSeq = cl.OrderSeq)
       INNER JOIN T_Operator ope ON (ope.OpId = cl.OpId)
WHERE  cl.OrderSeq IN ( %s )
AND    cl.OpId <> %d
EOQ;
            $sql = sprintf($sql, $osecs, $this->app->authManagerAdmin->getUserInfo()->OpId);
            $rows = ResultInterfaceToArray($this->app->dbAdapter->query($sql)->execute(null));

            // 判定
            // count関数対策
            if (!empty($rows)) {
                $rowsLen = count($rows);
                for ($i=0; $i<$rowsLen; $i++) {
                    $msg[] = sprintf("注文ID:%s は、ﾕｰｻﾞｰ:%s が与信処理を実施中です。", $rows[$i]['OrderId'], $rows[$i]['NameKj']);
                }

                $this->view->assign('okCnt', 0);
                $this->view->assign('ngCnt', 0);
                $this->view->assign('hoCnt', 0);
                $this->view->assign('error', $msg);

                return $this->view;
            }

            // (ﾌﾟﾗｽﾁｪｯｸ)通知された注文SEQの全てを、自分が握っているかの調査
            $sql =<<<EOQ
SELECT o.OrderId
,      ope.NameKj
FROM   T_CreditLock cl
	   INNER JOIN T_Order o ON (o.OrderSeq = cl.OrderSeq)
       INNER JOIN T_Operator ope ON (ope.OpId = cl.OpId)
WHERE  cl.OrderSeq IN ( %s )
AND    cl.OpId = %d
EOQ;
            $sql = sprintf($sql, $osecs, $this->app->authManagerAdmin->getUserInfo()->OpId);
            $rows = ResultInterfaceToArray($this->app->dbAdapter->query($sql)->execute(null));

            // 判定
            // count関数対策
            $rowLen = 0;
            $osecsLen = 0;
            $osecsData = explode(',', $osecs);
            if(!empty($rows)) {
                $rowLen = count($rows);
            }
            if(!empty($osecsData)) {
                $osecsLen = count($osecsData);
            }

            if ($rowLen != $osecsLen) {

                $msg[] = "ページに関する与信処理権限がありません。";

                $this->view->assign('okCnt', 0);
                $this->view->assign('ngCnt', 0);
                $this->view->assign('hoCnt', 0);
                $this->view->assign('error', $msg);

                return $this->view;
            }
        }

        //
        // 以降、本体処理
        //

        // ユーザーIDの取得
        $obj = new \models\Table\TableUser($this->app->dbAdapter);
        $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

        $mdl = new TableOrder($this->app->dbAdapter);

        // 配送伝票番号登録ロジック･インスタンス生成
        $shippingLogic = new \models\Logic\LogicShipping($this->app->dbAdapter, $userId);

        $okCnt = 0;
        $ngCnt = 0;
        $hoCnt = 0;
        $i = 0;

        // CoralMailのインスタンス生成
        $obj_coralmail = new CoralMail($this->app->dbAdapter, $this->app->mail['smtp']);

        $ary_ents = array();  // 加盟店毎配列

		while (isset($params['OrderSeq' . $i]))
		{
			// 現在のデータステータスを取得（2011.6.27 eda）
			$ds = $mdl->getDataStatus($params['OrderSeq' . $i]);
			// Yes i will 且つデータステータスが社内与信確定待ちの場合のみ処理（2011.6.27 eda）
			if (isset($params['Iwill' . $i]) && ($ds == 15 || $ds == 21))
			{
				unset($data);

				// 電話・メールの与信結果／ポイントとeDenの情報を反映
				// 2008.02.20 電話履歴の追加
				// 2008.02.20 備考
				$this->setRealCsPoint($params['OrderSeq' . $i], $params['RealCallResult' . $i], $params['RealSendMailResult' . $i], $params['eDen' . $i], $params['PhoneHistory' . $i]);

				// 「補償外案件」の反映（09.06.09追加 eda）
				$data['OutOfAmends'] = $params['OutOfAmends' . $i];

				// 補償外案件の状況によって請求取りまとめを更新する
				$mghelper = new LogicMergeOrderHelper($this->app->dbAdapter, $params['OrderSeq' . $i]);
				if($mghelper->chkCcTargetStatusByOutOfAmends($data['OutOfAmends']) != 9) {
					$data['CombinedClaimTargetStatus'] = $mghelper->chkCcTargetStatusByOutOfAmends($data['OutOfAmends']);
				}

				// 無条件に共通で反映させる項目
				$data["Incre_Note"] = $params['Incre_Note' . $i];

				try
				{
// Mod By Takemasa(NDC) 20150923 Stt OK/NG/保留、何れの指定もないときは[「社内与信確定待ち」のまま残す]とする
				    if (!isset($params['Incre_Status' . $i])) {
				        $mdl->saveUpdate(array('Incre_Note' => $data["Incre_Note"]), $params['OrderSeq' . $i]);
                        // (なにもしない)与信排他制御テーブルからの注文SEQのみ削除行う
				        $i++;
				        continue;
				    }
// Mod By Takemasa(NDC) 20150923 End OK/NG/保留、何れの指定もないときは[「社内与信確定待ち」のまま残す]とする

					if (isset($params['Incre_Status' . $i]))
					{
						$status = $params['Incre_Status' . $i];

						switch($status)
						{
							case -1:
								$data["Dmi_DecSeqId"] = '_CREDIT_NG_';		// 与信確定識別シーケンス　（後のDMI与信確定時のメール送信に混ぜ込むために必要）
								$data["DataStatus"] = 91;					// クローズ
								$data["CloseReason"] = 3;					// 与信NGクローズ
								$data["Dmi_Status"] = -1;					// DMI－ステータス(-1:NG)
								$reasonCode = 25;                           // 履歴登録用理由コード（社内与信NG）
								$ngCnt++;
								break;
							case 1:
								$data["DataStatus"] = 31;					// OK => 伝票入力待ち(31)へ
								$data["Dmi_Status"] = 1;					// DMI－ステータス(1:OK)
								$reasonCode = 24;                           // 履歴登録用理由コード（社内与信OK）
								$okCnt++;
								break;
							default:
								$data["DataStatus"] = 21;					// 保留
								$data["PendingReasonCode"] = $params['reserveReason' . $i]; // 保留理由
								$reasonCode = 26;                           // 履歴登録用理由コード（社内与信保留）
								$hoCnt++;
								break;
						}

						if ($status == -1 ) {
							$sql = "SELECT S.NgChangeFlg, S.MuhoshoChangeDays FROM T_Site S INNER JOIN T_Order O ON (O.OrderSeq = :OrderSeq) WHERE S.SiteId = O.SiteId ";
							$sdata = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $params['OrderSeq' . $i]))->current();

							$NgLimitDay = date('Y-m-d', strtotime('+'. $sdata['MuhoshoChangeDays'] . ' day'));
							if ( $sdata['NgChangeFlg'] == 0) {
								$BtnOkFlg = 0;
							} else {

								// 過去二年間の取引の注文SEQを取得
								$csql = " SELECT RegUnitingAddress, RegPhone FROM T_Customer WHERE OrderSeq = :OrderSeq ";
								$row_c = $this->app->dbAdapter->query($csql)->execute(array(':OrderSeq' => $params['OrderSeq' . $i]))->current();

								$seqs = array();
								foreach($mdl->getPastOrderSeqs(nvl($row_c['RegUnitingAddress'],''), nvl($row_c['RegPhone'],'')) as $row) {
									if($row['OrderSeq'] != $params['OrderSeq' . $i]) {
										$seqs[] = (int)$row['OrderSeq'];
									}
								}

								// 過去二年間の未払い件数
								$cnt = 0;
								if(!empty($seqs)) {
									$pastOrders = join(',', $seqs);
									$cnt = $mdl->findOrderCustomerByUnpaidCnt($pastOrders);
								}

								$BtnOkFlg = 0;
								if ($cnt == 0) {
									if ($params['NgReason' . $i] != 0) {
										$csql = "SELECT Class1 FROM M_Code WHERE CodeId = 190 AND KeyCode = :KeyCode ";
										$BtnOkFlg = $this->app->dbAdapter->query($csql)->execute(array(':KeyCode' => $params['NgReason' . $i]))->current()['Class1'];
									}
								}
							}
							$mdlao = new ATableOrder($this->app->dbAdapter);
							$mdlao->saveUpdate(array('ManualJudgeNgReasonCode' => $params['NgReason' . $i], 'NgButtonFlg' => $BtnOkFlg, 'NoGuaranteeChangeLimitDay' => $NgLimitDay), $params['OrderSeq' . $i]);
						}

						// 共通項目
						$data["Incre_Status"] = $status;														// 社内与信ステータス
						$data["Incre_DecisionDate"] = date("Y-m-d");											// 与信確定日
						$data["Incre_DecisionOpId"] = $this->app->authManagerAdmin->getUserInfo()->OpId;		// 与信確定担当者
						$data["UpdateId"] = $userId;                                                            // 更新者
					}

                    $isUpdateDataStatus21On = false;   // [保留]として更新処理が行われたか？

					$this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();

					if ($mdl->isCanceled($params['OrderSeq' . $i]))
					{
						// キャンセルされているのでいじらない。
					}
					else
					{
// Mod By Takemasa(NDC) 20150513 Stt 何れの結果であれメール送信を行う(ただし変更のない保留は例外的に送信なし)
						//// If status is 91 then Insert into T_CjMailHistory 2013.8.7 kashira
						//if ($data["DataStatus"] == 91)
						//{
						//	$mdlCjMail = new \models\Table\TableCjMailHistory($this->app->dbAdapter);
						//	$mdlCjMail->rsvCjMail($params['OrderSeq' . $i], 3, $userId);
						//}
						$mdlCjMail = new \models\Table\TableCjMailHistory($this->app->dbAdapter);
						if      ($data["DataStatus"] == 91) {
                            // NG
                            // メール送信予約(顧客)
                            $mdlCjMail->rsvCjMail($params['OrderSeq' . $i], 3, $userId);

                            // 加盟店毎データ蓄積
                            $fact = array (
                                'SiteId' => $params['SiteId' . $i],
                                'EnterpriseId' => $params['EnterpriseId' . $i],
                                'IsNg' => true,
                                'OrderSeq' => $params['OrderSeq' . $i]
                            );
                            $this->storeArrayByEnterprise($fact, $ary_ents);
						}
						else if ($data["DataStatus"] == 31) {
                            // OK
                            // メール送信予約(顧客)
                            $mdlCjMail->rsvCjMail($params['OrderSeq' . $i], 4, $userId);

                            // 加盟店毎データ蓄積
                            $fact = array (
                                'SiteId' => $params['SiteId' . $i],
                                'EnterpriseId' => $params['EnterpriseId' . $i],
                                'IsNg' => false,
                                'OrderSeq' => $params['OrderSeq' . $i]
                            );
                            $this->storeArrayByEnterprise($fact, $ary_ents);
						}
						else if ($data["DataStatus"] == 21) {
						    // 保留

						    // 注文テーブル上の[データステータス][保留理由]が同一で更新される時は、改めてメールしない
						    $cnt = (int)$this->app->dbAdapter->query(
						        " SELECT COUNT(1) AS cnt FROM T_Order WHERE OrderSeq = :OrderSeq AND DataStatus = 21 AND PendingReasonCode = :PendingReasonCode "
                                )->execute(array(':OrderSeq' => $params['OrderSeq' . $i], ':PendingReasonCode' => $data["PendingReasonCode"]))->current()['cnt'];
                            if ($cnt == 0 && $params['reserveReason' . $i] > 0) {
                                // --------------------------------------------------
                                // 保留理由が新たに設定された もしくは 保留理由が変更された
                                // --------------------------------------------------
                                $isUpdateDataStatus21On = true;   // [保留]として更新処理が行われた

                                // 保留注文リストへの登録
                                $mdle = new TableEnterprise($this->app->dbAdapter);
                                $mdlao = new ATableOrder($this->app->dbAdapter);
                                $mdlbc = new TableBusinessCalendar($this->app->dbAdapter);
                                $mdlsysp = new TableSystemProperty($this->app->dbAdapter);

                                $rowe = $mdle->find($params['EnterpriseId' . $i])->current();

                                $sql = "SELECT Note, Class1 FROM M_Code WHERE CodeId = 92 AND KeyCode = :KeyCode ";
                                $row = $this->app->dbAdapter->query($sql)->execute(array(':KeyCode' => $params['reserveReason' . $i]))->current();


                                // キャンセル予定日を取得
                                $defectCancelPlanDays = intval($mdlsysp->getValue(TableSystemProperty::DEFAULT_MODULE, 'systeminfo', 'DefectCancelPlanDays'));
                                if ($defectCancelPlanDays < 0) {
                                    $defectCancelPlanDays = 0; // マイナスの設定はありえないが、念のため
                                }

                                // 2営業日後を取得
                                $defectCancelPlanDate = $mdlbc->getNextBusinessDateNonIncludeByDays(date('Y-m-d'), $defectCancelPlanDays);

                                $aodata = array(
                                    'DefectFlg' => $rowe['HoldBoxFlg'],
                                    'DefectInvisibleFlg' => 0,
                                    'DefectNote' => $row['Note'],
                                    'DefectCancelPlanDate' => ($defectCancelPlanDate . ' 23:59:59'),
                                );
                                $mdlao->saveUpdate($aodata, $params['OrderSeq' . $i]);

                            } elseif ($params['reserveReason' . $i] <= 0) {
                                // --------------------------------------------------
                                // 保留理由が未選択
                                // --------------------------------------------------
                                $mdlao = new ATableOrder($this->app->dbAdapter);

                                // キャンセルバッチの対象外とするため、日付に大きな値を設定する
                                $defectCancelPlanDate = '2100-12-31 23:59:59';
                                $aodata = array(
                                        'DefectCancelPlanDate' => $defectCancelPlanDate,
                                );
                                $mdlao->saveUpdate($aodata, $params['OrderSeq' . $i]);

                            } else {
                                // --------------------------------------------------
                                // 保留理由が同一のまま
                                // --------------------------------------------------
                                // 何もしない
                            }


						}
// Mod By Takemasa(NDC) 20150513 End 何れの結果であれメール送信を行う(ただし変更のない保留は例外的に送信なし)

						$mdl->saveUpdate($data, $params['OrderSeq' . $i]);
					}

					// 注文履歴へ登録
					$history = new CoralHistoryOrder($this->app->dbAdapter);
					$history->InsOrderHistory($params['OrderSeq' . $i], $reasonCode, $userId);

					// 伝票番号の仮登録実行
					$datastatus = $data['DataStatus'];
					if ($data['DataStatus'] == 31) {
					    $jnResult = $shippingLogic->registerTemporaryJournalNumber($params['OrderSeq' . $i]);
					    $datastatus = ($jnResult) ? 41 : $datastatus;
					}

					// テスト注文時のクローズ処理
					if ($datastatus == 41) {
					   $shippingLogic->closeIfTestOrder($params['OrderSeq' . $i]);
					}

 					$this->app->dbAdapter->getDriver()->getConnection()->commit();

                    // [保留]として更新処理(保留理由が変更されている時に限定)が行われた場合は加盟店へメールを送る
                    if ($isUpdateDataStatus21On) {
                        try {
                            $obj_coralmail->SendHoldMailToEnt( $params['OrderSeq' . $i], $userId );
                        }
                        catch(CoralMailException $exp1) { ;/* 例外発生時の特別処理なしの明示 */ }
                        catch(\Exception $exp2)         { ;/* 例外発生時の特別処理なしの明示 */ }
                    }
				}
				catch(\Exception $e) {
					$this->app->dbAdapter->getDriver()->getConnection()->rollback();
				}
			}

			$i++;
		}

        // 事業者／サイト別に与信完了メールを送信する
        foreach ($ary_ents as $row) {
            try {
                $obj_coralmail->SendCreditFinishEachEnt2( $row, $userId );
            }
            catch(CoralMailException $exp1) { ;/* 例外発生時の特別処理なしの明示 */ }
            catch(\Exception $exp2)         { ;/* 例外発生時の特別処理なしの明示 */ }
        }

		// 与信排他制御テーブルからの削除
		$sql = sprintf(" DELETE FROM T_CreditLock WHERE OrderSeq IN ( %s ) ", $osecs);
		$ri = $this->app->dbAdapter->query($sql)->execute(null);

		$this->view->assign('okCnt', $okCnt);
		$this->view->assign('ngCnt', $ngCnt);
		$this->view->assign('hoCnt', $hoCnt);

        return $this->view;
	}

	/**
	 * 加盟店向け与信結果メール用のデータ蓄積(サイトID単位毎)
	 *
	 * @param array $fact 要素
	 * @param array $ents 蓄積データ
	 */
	protected function storeArrayByEnterprise($fact, &$ents) {

        $isExists = false;
        $index = 0;
        foreach ($ents as $ent) {
            if ($ent['SiteId'] == $fact['SiteId']) {
                $isExists = true;
                break;
            }
            $index++;
        }

        // 未蓄積のサイト時の初期化
        if (!$isExists) {
            // count関数対策
            $index = 0;
            if(!empty($ents)) {
                $index = count($ents);
            }

            $ents[$index]['OKCount'] = 0;
            $ents[$index]['NGCount'] = 0;
        }

        $ents[$index]['SiteId'] = $fact['SiteId'];
        $ents[$index]['EnterpriseId'] = $fact['EnterpriseId'];
        if (!$fact['IsNg']) { $ents[$index]['OKCount']++; }
        if ( $fact['IsNg']) { $ents[$index]['NGCount']++; }

        // (注文情報取得)
        $sql  = " SELECT o.RegistDate ";
        $sql .= " ,      o.OrderId ";
        $sql .= " ,      c.NameKj ";
        $sql .= " ,      o.UseAmount ";
        $sql .= " ,      c.PrefectureName ";
        $sql .= " ,      o.Ent_OrderId ";
        $sql .= " FROM   T_Order o ";
        $sql .= "        INNER JOIN T_Customer c ON (c.OrderSeq = o.OrderSeq) ";
        $sql .= " WHERE  o.OrderSeq = :OrderSeq ";
        $order = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $fact['OrderSeq']))->current();

        // サイト情報の取得
        $mdls = new TableSite($this->app->dbAdapter);
        $siteData = $mdls->findSite($fact['SiteId'])->current();

        // NG理由の取得
        if ($siteData['ShowNgReason'] == 1) {
            $sql = 'SELECT C.Note FROM AT_Order AOD LEFT OUTER JOIN M_Code C ON (C.CodeId = 190 AND C.KeyCode = AOD.ManualJudgeNgReasonCode) WHERE AOD.OrderSeq = :OrderSeq ';
            $NgReason = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $fact['OrderSeq']))->current()['Note'];
        } else {
            $NgReason = null;
        }

        $ents[$index][($fact['IsNg']) ? 'sub_orders_ng' : 'sub_orders_ok'][] = sprintf('%s  %s %s %s %s %s %s',
            date('Y/m/d', strtotime($order['RegistDate'])),
            $order['OrderId'],
            BaseGeneralUtils::rpad($order['NameKj'] . '様', '　', 11),
            BaseGeneralUtils::lpad($order['UseAmount'] . '円', ' ', 8),
            $order['PrefectureName'],
            BaseGeneralUtils::rpad($order['Ent_OrderId'], ' ', 12),
            $NgReason);
    }

// Del By Takemasa(NDC) 20150629 Stt 廃止
// 	/**
// 	 * 社内与信個別画面
// 	 */
// 	public function detailAction()
// Del By Takemasa(NDC) 20150629 End 廃止

	/**
	 * リアル電話・リアルメール結果の反映
	 *
	 * @param int $orderSeq
	 * @param int $realCallResultCode
	 * @param int $realSendMailResultCode
	 * @param int $eDen
	 * @param int $phoneHistory
	 * @return string ログ
	 */
	private function setRealCsPoint($orderSeq, $realCallResultCode, $realSendMailResultCode, $eDen, $phoneHistory)
	{
		$mdloc = new ViewOrderCustomer($this->app->dbAdapter);
		$mdlcd = new TableCode($this->app->dbAdapter);
		$mdlcc = new TableCreditPoint($this->app->dbAdapter);

		// 指定注文データの取得
		$orderCustomer = $mdloc->findOrderCustomerByOrderSeq($orderSeq)->current();
		// 電話結果のポイントの取得
		if ($realCallResultCode > 0)
		{
		    $cpid = $mdlcd->getMasterAssCode(7, $realCallResultCode);
			$realCallPoint = $mdlcc->findCreditPoint($orderCustomer['CreditCriterion'], $cpid)->current()['Point'];
		}
		else
		{
			$realCallPoint = 0;
		}

		// リアルメール送信結果のポイントの取得
		if ($realSendMailResultCode > 0)
		{
			$cpid = $mdlcd->getMasterAssCode(8, $realSendMailResultCode);
			$realSendMailPoint = $mdlcc->findCreditPoint($orderCustomer['CreditCriterion'], $cpid)->current()['Point'];
		}
		else
		{
			$realSendMailPoint = 0;
		}

		// 既存ポイントから電話結果とメール結果を差し引いたポイントの算出
		$basePoint = $orderCustomer['Incre_ScoreTotal'] - $orderCustomer['RealCallScore'] - $orderCustomer['RealSendMailScore'];

		$scoreTotal = $basePoint + $realCallPoint + $realSendMailPoint;		// 新しいトータルポイント

        // ユーザーIDの取得
        $obj = new \models\Table\TableUser($this->app->dbAdapter);
        $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

		// T_Customerへ書き込み
		$mdlCustomer = new \models\Table\TableCustomer($this->app->dbAdapter);
		$mdlCustomer->saveUpdate(
			array(
				'RealCallResult' => $realCallResultCode,				// 電話結果コード
				'RealCallScore' => $realCallPoint,						// 電話結果ポイント
				'RealSendMailResult' => $realSendMailResultCode,		// メール結果コード
				'RealSendMailScore' => $realSendMailPoint,				// メール結果ポイント
				'eDen' => $eDen,										// e電話帳
				'PhoneHistory' => $phoneHistory,						// 電話履歴
				'UpdateId' => $userId,                                  // 更新者
			),
			$orderCustomer['CustomerId']
		);

		// T_Orderへ書き込み
		$mdlOrder = new TableOrder($this->app->dbAdapter);
		$mdlOrder->saveUpdate(array('Incre_ScoreTotal' => $scoreTotal, 'UpdateId' => $userId), $orderSeq);

		return $log;
	}

// Add By Takemasa(NDC) 20150226 Stt 新規実装(ApiController::parsePostalAction()の移植)
    /**
     * 郵便番号から住所を検索する
     */
    public function parsepostalAction() {

        $results = array(
                'result' => 'OK',
                'count' => 0,
                'list' => array()
        );

        try {
            $params = $this->getParams();

            $postal_code = isset($params['postalcode']) ? $params['postalcode'] : '';
            $postal_code = preg_replace( '/-/', '', CoralValidateUtility::fixPostalCode($postal_code));

            $sql  = " SELECT * FROM M_PostalCode ";
            $sql .= " WHERE  PostalCode7 LIKE :PostalCode7 ";
            $sql .= " ORDER BY PostalCode7, PrefectureKana, CityKana, TownKana ";

            $stm = $this->app->dbAdapter->query($sql);

            $ri = $stm->execute(array(':PostalCode7' => $postal_code . '%'));

            $resultsListLen = 0;
            if(!empty($results['list'])) {
                $resultsListLen = count($results['list']);
            }

            foreach($ri as $row) {
                if( $resultsListLen > 20 ) {
                    $results['list'][] = array( 'postal_code' => '---', 'address' => '(一致件数が多いため中断しました)' );
                    $resultsListLen = $resultsListLen + 1;
                    break;
                }
                $results['list'][] = array(
                        'postal_code' => CoralValidateUtility::fixPostalCode($row['PostalCode7']),
                        'address' => join('', array($row['PrefectureKanji'], $row['CityKanji'], $row['TownKanji']))
                );
                $resultsListLen = count($results['list']);
            }

            $results['count'] = $resultsListLen;
            if( $results['count'] < 1 ) {
                $results['list'][] = array( 'postal_code' => '---', 'address' => '(一致する住所はありません)' );
            }
        }
        catch(\Exception $err) {
            $results['result'] = 'NG';
            $results['reason'] = $err->getMessage();
        }

        echo \Zend\Json\Json::encode($results);
        return $this->response;
    }
// Add By Takemasa(NDC) 20150226 End 新規実装(ApiController::parsePostalAction()の移植)

    /**
     * (Ajax)自身が与信可能(排他制御)かの判定を行う。
     */
    public function iscanlockAction()
    {
        try
        {
            $params = $this->getParams();
            $osecs = $params['osecs'];
            $osecs = explode(',', $osecs);
            foreach ($osecs as &$oseq) {
                if (!is_numeric($oseq)) {
                    $oseq = -1;
                }
            }
            $osecs = implode(',', $osecs);

            // 通知された注文SEQが、自分以外のｵﾍﾟﾚｰﾀに握られているかの調査
            $sql =<<<EOQ
SELECT o.OrderId
,      ope.NameKj
FROM   T_CreditLock cl
	   INNER JOIN T_Order o ON (o.OrderSeq = cl.OrderSeq)
       INNER JOIN T_Operator ope ON (ope.OpId = cl.OpId)
WHERE  cl.OrderSeq IN ( %s )
AND    cl.OpId <> %d
EOQ;
            $sql = sprintf($sql, $osecs, $this->app->authManagerAdmin->getUserInfo()->OpId);
            $rows = ResultInterfaceToArray($this->app->dbAdapter->query($sql)->execute(null));

            // count関数対策
            // 判定
            if (empty($rows)) {
                $msg = '1';// 成功指示
            }
            else {
                $rowsLen = count($rows);
                for ($i=0; $i<$rowsLen; $i++) {
                    if ($i > 0) { $msg .= "\n"; }
                    $msg .= sprintf("注文ID:%s は、ﾕｰｻﾞｰ:%s が与信処理を実施中です。", $rows[$i]['OrderId'], $rows[$i]['NameKj']);
                }
                $msg .= "\n\n与信処理を強制実施しますか？";
            }
        }
        catch(\Exception $e)
        {
            $msg = $e->getMessage();
        }

        echo \Zend\Json\Json::encode(array('status' => $msg));
        return $this->response;
    }

    /**
     * (Ajax)与信排他制御テーブルへの登録(与信開始アクション)。
     */
    public function dolockAction()
    {
        try
        {
            $params = $this->getParams();
            $osecs = $params['osecs'];
            $osecs = explode(',', $osecs);
            foreach ($osecs as &$oseq) {
                if (!is_numeric($oseq)) {
                    $oseq = -1;
                }
            }
            $osecs = implode(',', $osecs);

            $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();

            // 与信排他制御テーブルからの削除
            $sql = sprintf(" DELETE FROM T_CreditLock WHERE OrderSeq IN ( %s ) ", $osecs);
            $ri = $this->app->dbAdapter->query($sql)->execute(null);

            // 与信排他制御テーブルへの登録
            $sql = " INSERT INTO T_CreditLock (OrderSeq, OpId) VALUES (:OrderSeq, :OpId) ";
            $stm = $this->app->dbAdapter->query($sql);
            $array_osec = explode(',', $osecs);

            // count関数対策
            $array_osecLen = 0;
            if(!empty($array_osec)) {
                $array_osecLen = count($array_osec);
            }

            for ($i=0; $i<$array_osecLen; $i++) {
                $stm->execute(array(':OrderSeq' => $array_osec[$i], ':OpId' => $this->app->authManagerAdmin->getUserInfo()->OpId));
            }

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

