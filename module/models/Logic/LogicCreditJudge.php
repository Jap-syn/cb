<?php
namespace models\Logic;

use Zend\Db\Adapter\Adapter;
use models\Table\TableCreditPoint;
use models\Table\TableCreditCondition;
use models\Table\TableCreditConditionAddress;
use models\Table\TableCreditConditionName;
use models\Table\TableCreditConditionItem;
use models\Table\TableCreditConditionDomain;
use models\Table\TableCreditConditionEnterprise;
use models\Table\TableCreditConditionPhone;
use models\Table\TableSite;
use models\Table\TablePostalCode;
use models\Table\TableOrder;
use models\Table\TableCjResult;
use models\Table\TableEnterprise;
use models\Table\TableCustomer;
use models\Table\TableDeliveryDestination;
use models\Table\TableOrderItems;
use models\Table\TableUser;
use models\View\ViewOrderCustomer;
use models\View\ViewDelivery;
use Coral\Coral\Mail\CoralMail;
use models\Table\TableAddCreditCondition;
use models\Table\TableOrderSummary;

/**
 * 社内与信クラス
 */
class LogicCreditJudge
{
	/**
	 * アダプタ
	 *
	 * @var Adapter
	 */
	protected $_adapter = null;

	/**
	 * TableCreditPointインスタンス
	 *
	 * @var TableCreditPoint
	 */
	private $mdlcp;

	/**
	 * TableCreditConditionインスタンス
	 *
	 * @var TableCreditCondition
	 */
	private $mdlcc;

	/**
	 * 対象となった与信条件
	 *
	 * @var array
	 */
	private $creditConditionMatchData;

	/**
	 * 対象となった与信条件SEQ
	 *
	 * @var array
	 */
	private $ConditionSeqs;

	// 与信ターゲットを正規化する
    private $map = array(
           '1' => LogicNormalizer::FILTER_FOR_ADDRESS,
           '2' => LogicNormalizer::FILTER_FOR_NAME,
           '3' => LogicNormalizer::FILTER_FOR_ITEM_NAME,
           '4' => LogicNormalizer::FILTER_FOR_MAIL,
	       '5' => LogicNormalizer::FILTER_FOR_ID,
         //'6' => LogicNormalizer::FILTER_FOR_ADDRESS,
         //'7' => LogicNormalizer::FILTER_FOR_ID,
           '8' => LogicNormalizer::FILTER_FOR_TEL,
           '9' => LogicNormalizer::FILTER_FOR_MONEY,
    );

	// smtp
	private $smtp;

	// entIds
	private $entIds;

	// realTimeCjFlg
	private $realTimeCjFlg = false;


	/**
	 * コンストラクタ
	 *
	 * @param Adapter $adapter アダプタ
	 */
	function __construct(Adapter $adapter)
	{
        $this->_adapter = $adapter;
        $this->mdlcp = new TableCreditPoint($this->_adapter);
        $this->mdlcc = new TableCreditCondition($this->_adapter);
        $this->creditConditionMatchData = array();
        $this->ConditionSeqs = array();
	}

	/**
	 * 社内与信定期実行用メソッド
	 *
	 */
	public function doneFixedTermJudge()
	{
        $mdloc = new ViewOrderCustomer($this->_adapter);
        $targetOrders = $mdloc->findByDs11();

        $mdlo = new TableOrder($this->_adapter);

        // ユーザーID
        $mdlu = new TableUser($this->_adapter);
        $userId = $mdlu->getUserId(TableUser::USERCLASS_SYSTEM, TableUser::SEQ_BATCH_USER);

        $log = sprintf("START:%s\r\n\r\n", date('Y-m-d H:i:s'));
        $log .= sprintf("count : %s\r\n", $targetOrders->count());

        // 自動与信時にメール送信を行うためのDecSeqId
        $decSeqId = date("YmdHis") . '-' . "autoCreditJudge" . '-m';

        foreach ($targetOrders as $targetOrder)
        {
            $log .= $this->doneAutoCreditJudge($targetOrder['OrderSeq']);
            $log .= "\r\n";

            // メール送信のために注文情報取得
            $newTargetOrder = $mdlo->find($targetOrder['OrderSeq'])->current();

            // 自動与信ＯＫ/ＮＧの場合にはメール送信のために$dmiSeqIdを設定する
            // ＮＧの場合にはクローズ理由も確認する（キャンセル等のため）
            if ($newTargetOrder &&
                    (($newTargetOrder['DataStatus'] == 31 || ($newTargetOrder['DataStatus'] == 91 && $newTargetOrder['CloseReason'] == 3))) ) {
                // UPDATE T_Order
                $sql = " UPDATE T_Order SET Dmi_DecSeqId = :Dmi_DecSeqId WHERE OrderSeq = :OrderSeq ";

                $stm = $this->_adapter->query($sql);

                $prm = array(
                        ':Dmi_DecSeqId' => $decSeqId,
                        ':OrderSeq' => $newTargetOrder['OrderSeq'],
                );

                $stm->execute($prm);
            }

            // 20130913 新与信システム暫定対応
            // T_CjResultに対象
            $mdlcjr = new TableCjResult($this->_adapter);
            unset($cjrdata);

            // 20131025 請求取りまとめ設定
            $this->setCombinedClaimStatusSet($targetOrder);

            // 20140120 補償外設定
            $this->setOutOfAmendsSet($targetOrder);

            $cjrdata['OrderSeq'] = $targetOrder['OrderSeq'];
            $cjrdata['OrderId'] = $targetOrder['OrderId'];
            // 未送信
            $cjrdata['Status'] = 0;

            // 新規作成
            $mdlcjr->saveNew($cjrdata);
        }

        // 対象の事業者ＩＤ、サイトＩＤを取得する
        $targetSite = $mdlo->getEnterpriseSiteByDmiSeqId($decSeqId);

        // 自動与信ＯＫ/ＮＧ対象にメールを送信する
        try {
            foreach ($targetSite as $site) {
                // メール送信
                $mail = new CoralMail($this->_adapter, $this->getSmtp());
                $mail->SendCreditFinishEachEnt($site['EnterpriseId'], $site['SiteId'], $decSeqId, $userId);
            }
        }
        catch (CoralMailException $cme) {
            $log .= sprintf("Send Mail Missed:%s:".$site['EnterpriseId']."-".$site['SiteId']."-".$decSeqId."\r\n\r\n", date('Y-m-d H:i:s'));
        }

        $log .= sprintf("END:%s\r\n\r\n", date('Y-m-d H:i:s'));

        echo $log;
	}

	/**
	 * 社内与信自動実行
	 *
	 * @param int $orderSeq 注文Seq
	 */
	public function doneAutoCreditJudge($orderSeq)
	{
        $log = sprintf("01.start:%s\r\n", date('Y-m-d H:i:s'));

        $scoreTotal = 0;
        $mdlo = new TableOrder($this->_adapter);				// 注文データ
        $mdlc = new TableCustomer($this->_adapter);				// 購入者データ
        $mdld = new TableDeliveryDestination($this->_adapter);	// 配送先データ
        $mdli = new TableOrderItems($this->_adapter);			// 注文商品データ
        $mdle = new TableEnterprise($this->_adapter);			// 事業者情報
        $mdloc = new ViewOrderCustomer($this->_adapter);		// View : 注文・購入者
        $mdlde = new ViewDelivery($this->_adapter);				// View : 注文商品・配送先

        // 注文データ取得
        $order = $mdlo->find($orderSeq)->current();

        // 注文・購入者情報取得
        $log .= sprintf("02.ordercustomer:%s\r\n", date('Y-m-d H:i:s'));
        $orderCustomer = $mdloc->findOrderCustomerByOrderSeq($orderSeq)->current();

        // 注文商品・配送先情報の取得
        $log .= sprintf("03.itemdeli:%s\r\n", date('Y-m-d H:i:s'));
        $itemDelis = $mdlde->findByOrderSeq($orderSeq);
        $log .= sprintf("04.enterprise:%s\r\n", date('Y-m-d H:i:s'));
        $enterpriseData = $mdle->findEnterprise($orderCustomer['EnterpriseId'])->current();

        /*
         * 購入者情報（T_Customer）に関する与信
        */
        unset($eData);

        // リアル電話チェック
        $eData["RealCallScore"] = 0;

        // リアルメール送信チェック
        $eData["RealSendMailScore"] = 0;

        $log .= sprintf("05.name:%s\r\n", date('Y-m-d H:i:s'));

        // 氏名
        $res = $this->doneName($orderSeq, $orderCustomer['NameKj']);
        $scoreTotal += $res->Score;
        $eData["Incre_ArName"] = $res->Class;
        $eData["Incre_NameScore"] = $res->Score;
        $eData["Incre_NameNote"] = $res->Note;

        $log .= sprintf("06.address:%s\r\n", date('Y-m-d H:i:s'));

        // 住所
        $res = $this->doneAddress($orderSeq, $orderCustomer['UnitingAddress']);
        $scoreTotal += $res->Score;
        $eData["Incre_ArAddr"] = $res->Class;
        $eData["Incre_AddressScore"] = $res->Score;
        $eData["Incre_AddressNote"] = $res->Note;

        // 配送先住所

        $log .= sprintf("07.domain:%s\r\n", date('Y-m-d H:i:s'));

        // メールアドレス（ドメイン）
        $res = $this->doneMailDomain($orderSeq, $orderCustomer['MailAddress']);
        $scoreTotal += $res->Score;
        $eData["Incre_MailDomainScore"] = $res->Score;
        $eData["Incre_MailDomainNote"] = $res->Note;

        $log .= sprintf("08.phone:%s\r\n", date('Y-m-d H:i:s'));

        // 電話番号
        $res = $this->doneTel($orderSeq, $orderCustomer['Phone']);
        $scoreTotal += $res->Score;
        $eData["Incre_ArTel"] = $res->Class;
        $eData["Incre_TelScore"] = $res->Score;
        $eData["Incre_TelNote"] = $res->Note;

        // 金額
        $res = $this->doneMoney($orderSeq, $order['UseAmount']);
        $scoreTotal += $res->Score;
        $eData["Incre_MoneyScore"] = $res->Score;
        $eData["Incre_MoneyNote"] = $res->Note;

        $log .= sprintf("09.postalcode:%s\r\n", date('Y-m-d H:i:s'));

        // 郵便番号整合性
        $res = $this->donePostalCode($orderCustomer['PostalCode'], $orderCustomer['UnitingAddress']);
        $scoreTotal += $res->Score;
        $eData["Incre_PostalCodeScore"] = $res->Score;
        $eData["Incre_PostalCodeNote"] = $res->Note;

        $log .= sprintf("10.reg:%s\r\n", date('Y-m-d H:i:s'));

        // T_Customer更新
        $mdlc->saveUpdate($eData, $orderCustomer['CustomerId']);

        // 2012.12.17 tkaki OrderSummary:updateSummaryに移譲（氏名、住所、電話番号の正規化）

        $log .= sprintf("11.orderItem:%s\r\n", date('Y-m-d H:i:s'));


        $itemCount = 0;
        foreach ($itemDelis as $itemDeli)
        {
            /*
             * 商品（T_OrderItems）に関する与信
             */
            unset($eData);

            // 商品名
            $res = $this->doneItemName($orderSeq, $itemDeli['ItemNameKj']);
            $scoreTotal += $res->Score;
            $eData["Incre_Score"] = $res->Score;
            $eData["Incre_Note"] = $res->Note;

            // T_OrderItems更新
            $mdli->saveUpdate($eData, $itemDeli['OrderItemId']);

            /*
             * 配送先（T_DeliveryDestination）に関する与信
             */
            unset($eData);

            if ($orderCustomer['AnotherDeliFlg'] == 1)
            {
                // 別配送先の場合のみ配送先の与信を行う。

                // 配送先氏名
                $res = $this->doneName($orderSeq, $itemDeli['DestNameKj']);
                $eData["Incre_ArName"] = $res->Class;
                $eData["Incre_NameScore"] = $res->Score;
                $eData["Incre_NameNote"] = $res->Note;
                if ($itemCount == 0)
                {
                    // 複数配送先対応ではないため、一つの配送先分のみを合計に加算。
                    $scoreTotal += $res->Score;
                }

                // 配送先住所
                $res = $this->doneAddress($orderSeq, $itemDeli['UnitingAddress'], 'deli');	// 2008.07.18 deliという文字列を追加するようにする。
                $eData["Incre_ArAddr"] = $res->Class;
                $eData["Incre_AddressScore"] = $res->Score;
                $eData["Incre_AddressNote"] = $res->Note;
                if ($itemCount == 0)
                {
                    // 複数配送先対応ではないため、一つの配送先分のみを合計に加算。
                    $scoreTotal += $res->Score;
                }

                // 配送先電話番号
                $res = $this->doneTel($orderSeq, $itemDeli['Phone']);
                $eData["Incre_ArTel"] = $res->Class;
                $eData["Incre_TelScore"] = $res->Score;
                $eData["Incre_TelNote"] = $res->Note;
                if ($itemCount == 0)
                {
                    // 複数配送先対応ではないため、一つの配送先分のみを合計に加算。
                    $scoreTotal += $res->Score;
                }

                // 住所相違
                $res = $this->doneDiffAddress($orderCustomer['NameKj'], $orderCustomer['UnitingAddress'], $itemDeli['DestNameKj'], $itemDeli['UnitingAddress']);
                $eData["Incre_SameCnAndAddrScore"] = $res->Score;
                $eData["Incre_SameCnAndAddrNote"] = $res->Note;
                if ($itemCount == 0)
                {
                    // 複数配送先対応ではないため、一つの配送先分のみを合計に加算。
                    $scoreTotal += $res->Score;
                }

                // 配送先郵便番号整合性
                $res = $this->donePostalCode($itemDeli['PostalCode'], $itemDeli['UnitingAddress']);
                $eData["Incre_PostalCodeScore"] = $res->Score;
                $eData["Incre_PostalCodeNote"] = $res->Note;
                if ($itemCount == 0)
                {
                    // 複数配送先対応ではないため、一つの配送先分のみを合計に加算。
                    $scoreTotal += $res->Score;
                }
            }
            else
            {
                $eData["Incre_NameScore"] = 0;
                $eData["Incre_AddressScore"] = 0;
                $eData["Incre_SameCnAndAddrScore"] = 0;
                $eData["Incre_PostalCodeScore"] = 0;
            }


            // T_DeliveryDestination更新
            $mdld->saveUpdate($eData, $itemDeli['DeliDestId']);

            $itemCount++;
        }

        $log .= sprintf("12.enterLoginId:%s\r\n", date('Y-m-d H:i:s'));

        /*
         * 注文情報（T_Order）に関する与信
        */
        unset($eData);

        // 特定事業者
        $res = $this->doneEntLoginId($orderSeq, $orderCustomer['EnterpriseLoginId']);
        $scoreTotal += $res->Score;
        $eData["Incre_AtnEnterpriseScore"] = $res->Score;
        $eData["Incre_AtnEnterpriseNote"] = $res->Note;

        $log .= sprintf("13.border:%s\r\n", date('Y-m-d H:i:s'));

        // 利用額（ボーダー）
        $res = $this->doneUseAmountBorder($orderCustomer['UseAmount']);
        $scoreTotal += $res->Score;
        $eData["Incre_BorderScore"] = $res->Score;
        $eData["Incre_BorderNote"] = $res->Note;

        $log .= sprintf("14.limit:%s\r\n", date('Y-m-d H:i:s'));

        // キャンペーン期間中はキャンペーン情報を取得する
        // 加盟店に紐づくサイト情報を取得
        $mdls = new TableSite($this->_adapter);
        $sid = $mdls->getValidAll($enterpriseData['EnterpriseId'])->current()['SiteId'];
        // 詳細情報取得
        $logic = new LogicCampaign($this->_adapter);
        $campaign = $logic->getCampaignInfo($enterpriseData['EnterpriseId'], $sid);

        // 配列をマージする
        $enterpriseData = array_merge($enterpriseData, $campaign);

        // 利用額（限度額）
        $res = $this->doneLimitCheck(
            $orderCustomer['UnitingAddress'],
            $orderCustomer['UseAmount'],
            $enterpriseData['SettlementAmountLimit'],
            $enterpriseData['EnterpriseId'],
            $orderCustomer['OrderSeq']
        );

        $scoreTotal += $res->Score;
        $eData["Incre_LimitCheckScore"] = $res->Score;
        $eData["Incre_LimitCheckNote"] = $res->Note;

        // スコア合計
        $eData["Incre_ScoreTotal"] = $scoreTotal;

        // 与信条件にマッチしたデータ(JSON形式)
        $eData["CreditConditionMatchData"] = json_encode($this->creditConditionMatchData);

        // T_Order更新
        $mdlo->saveUpdate($eData, $orderSeq);

        $log .= sprintf("15.update:%s\r\n", date('Y-m-d H:i:s'));

        // 自動与信判定
        $cjbyaj = new LogicCreditJudgeByAutoJudge($this->_adapter);
        // 加算点を加える事業者一覧の設定
        $cjbyaj->setEnterpriseIds($this->getEnterpriseIds());
        // リアルタイム与信を行うかの設定
        $cjbyaj->setRealTimeCjFlg($this->realTimeCjFlg);
        $log .= $cjbyaj->doneAutoCreditJudge($enterpriseData['AutoCreditJudgeMode'], $orderSeq, $scoreTotal);

        $log .= sprintf("16.end:%s\r\n", date('Y-m-d H:i:s'));

        return $log;
	}

	/**
	 * 部分一致あるいは完全一致による与信
	 *
	 * @param boolean $isMatchesIn true:部分一致 false:完全一致
	 * @param int $category カテゴリ
	 * @param string $target 与信対象
	 * @param int $oseq 注文Seq
	 * @return CreditJudgeResult
	 */
	public function doneJudgeByMatches($isMatchesIn, $category, $target, $oseq)
	{
	    $mdlvd = new ViewDelivery($this->_adapter);
	    $mdlacc = new TableAddCreditCondition($this->_adapter);
	    $mdlos = new TableOrderSummary($this->_adapter);
	    $mdloc = new ViewOrderCustomer($this->_adapter);
	    $mdlcca = new TableCreditConditionAddress($this->_adapter);
	    $mdlccn = new TableCreditConditionName($this->_adapter);
	    $mdlcci = new TableCreditConditionItem($this->_adapter);
	    $mdlccd = new TableCreditConditionDomain($this->_adapter);
	    $mdlcce = new TableCreditConditionEnterprise($this->_adapter);
	    $mdlccp = new TableCreditConditionPhone($this->_adapter);
	    $mdlccm = new TableCreditConditionMoney($this->_adapter);

		// 対象の正規化
        $regtarget = LogicNormalizer::create($this->map[$category])->normalize($target);

        if (!empty($this->ConditionSeqs)) {
            $ConditionSeq = implode(',', $this->ConditionSeqs);
        } else {
            $ConditionSeq = null;
        }
	        switch($category){
            case 1:
                // マッチする与信条件を取得する。
                $matchDatas = $mdlcca->judge($category, $isMatchesIn, $regtarget, $ConditionSeq);
                break;
            case 2:
                // マッチする与信条件を取得する。
                $matchDatas = $mdlccn->judge($category, $isMatchesIn, $regtarget, $ConditionSeq);
                break;
            case 3:
                // マッチする与信条件を取得する。
                $matchDatas = $mdlcci->judge($category, $isMatchesIn, $regtarget, $ConditionSeq);
                break;
            case 4:
                // マッチする与信条件を取得する。
                $matchDatas = $mdlccd->judge($category, $isMatchesIn, $regtarget, $ConditionSeq);
                break;
            case 5:
                // マッチする与信条件を取得する。
                $matchDatas = $mdlcce->judge($category, $isMatchesIn, $regtarget, $ConditionSeq);
                break;
            case 8:
                // マッチする与信条件を取得する。
                $matchDatas = $mdlccp->judge($category, $isMatchesIn, $regtarget, $ConditionSeq);
                break;
            case 9:
                // マッチする与信条件を取得する。
                $matchDatas = $mdlccp->judge($category, $regtarget, $ConditionSeq);
                break;
            default:
                break;
        }


		$result = new CreditJudgeResult();

		$point = 0;
		$comment = "";
		foreach($matchDatas as $matchData)
		{
		    $sPattern = $matchData['SearchPattern'];
		    $regcString = $matchData['RegCstring'];
		    $P_cstring = $matchData['Cstring'];

		    if($matchData['Category'] != 9 ){
		        // 検索方法チェック
		        if (!$isMatchesIn || $sPattern == 0 ||
		        ($sPattern == 1 && strpos($regtarget, $regcString) === 0) ||
		        ($sPattern == 2 && strpos(strrev($regtarget), strrev($regcString)) === 0) ||
		        ($sPattern == 3 && $target == $P_cstring)) {
		        } else {
		            continue;
		        }
		    }else{
		        //金額だけは値で比較する
		        if (($sPattern == 0 && $regtarget == $regcString) ||
		        ($sPattern == 1 && $regtarget <= $regcString) ||
		        ($sPattern == 2 && $regtarget >= $regcString) ||
		        ($sPattern == 3 && $regtarget == $regcString)){
		        } else {
		            continue;
		        }
		    }

		    if ($matchData['AddConditionCount'] > 0) {

		        $itemDeli = ResultInterfaceToArray($mdlvd->findByOrderSeq($oseq));
		        $OrderSum = $mdlos->findByOrderSeq($oseq)->current();
		        $OrderCust = $mdloc->find($oseq)->current();
		        $truecnt = 0;

		        $datas = $mdlacc->findAddConditionValid($matchData['Seq'],$category);

		        foreach($datas as $data) {
		            $cstring = $data['Cstring'];
		            $pattern = $data['SearchPattern'];
		            $regCstring = $data['RegCstring'];

		            switch ($data['Category']) {
		                case 1: // 住所
		                    $addjudge = $this->doneJudgeByAddMatches($pattern, $cstring, $OrderSum['UnitingAddress'], $OrderSum['DestUnitingAddress']);
		                    break;
		                case 2: // 氏名
		                    $addjudge = $this->doneJudgeByAddMatches($pattern, $cstring, $OrderSum['NameKj'], $OrderSum['DestNameKj']);
		                    break;
		                case 3: // 商品名
		                    foreach ($itemDeli as $item) {
		                        $addjudge = $this->doneJudgeByAddMatches($pattern, $cstring, $item['ItemNameKj'], null);
		                        if ($addjudge) {
		                            break;
		                        }
		                    }
		                    break;
		                case 4: // ドメイン
		                    $addjudge = $this->doneJudgeByAddMatches($pattern, $cstring, $OrderSum['MailAddress'], null);
		                    break;
		                case 5: // 加盟店ID
		                    if ($data['RegCstringHash'] == MD5($OrderCust['EnterpriseLoginId'])) {
		                        $addjudge = true;
		                    } else {
		                        $addjudge = false;
		                    }
		                    break;
		                case 8: // 電話番号
		                    if ($pattern == 3) {
		                        $addjudge = $this->doneJudgeByAddMatches($pattern, $cstring, $OrderSum['Phone'], $OrderSum['DestPhone']);
		                    } else {
		                        $addjudge = $this->doneJudgeByAddMatches($pattern, $regCstring, $OrderSum['RegPhone'], $OrderSum['RegDestPhone']);
		                    }
		                    break;
		                case 9: // 金額
		                    $addjudge = $this->doneJudgeByAddMatchesInt($pattern, $cstring, $OrderSum['UseAmount']);
		                    break;
		            }
		            if ($addjudge) {
		                $truecnt += 1;
		            }
		        }

		        if ($truecnt != $matchData['AddConditionCount']) {
		            continue;
		        }

		    }

			$this->ConditionSeqs[] = $matchData['Seq'];

			// T_Order設定用
			array_push($this->creditConditionMatchData, $matchData);
			// ポイントの合算
			$point += $matchData['Point'];

			// コメントの合算
			$comment .= $matchData['Comment'] ." ". $matchData['Point'] ."/";
		}

		$result->Score = $point;
		$result->Note = $comment;

		return $result;
	}

	/**
	 * 自動登録された与信条件から完全一致で取得する
	 *
	 * @param int $category カテゴリ
	 * @param string $target 与信対象
	 * @param string $class 属性（ブラック、優良、リピート等）
	 * @return array
	 */
	public function doneJudgeByAutoCreditConditonCnt($category, $target, $class)
	{
		// 対象の正規化
        $regtarget = LogicNormalizer::create($this->map[$category])->normalize($target);

		// マッチする与信条件を取得する。
		$matchCount = $this->_findCreditConditionCnt($category, $regtarget, $class);

		return $matchCount;
	}

	/**
	 * 自動登録された与信条件から指定の条件に一致するデータの件数を取得する。
	 * このメソッドは doneJudgeByAutoCreditConditionCnt メソッドからのみ呼び出され、
	 * 内容は TableCreditCondition::findCreditConditionCnt の代替となっている。
	 *
	 * @access private
	 * @param int $category カテゴリ
	 * @param string $regtarget 正規化済みの与信対象データ
	 * @param string $class 属性クラス（ブラック、優良、リピート等）
	 * @return int
	 */
	private function _findCreditConditionCnt($category, $regtarget, $class)
	{
        $sql  = " SELECT COUNT(*) AS Cnt ";
        $sql .= " FROM   T_CreditCondition ";
        $sql .= " WHERE  1 = 1 ";
        $sql .= " AND    ValidFlg   = 1 ";
        $sql .= " AND    Category   = :Category ";
        $sql .= " AND    Class      = :Class ";
        $sql .= " AND    RegCstring = :RegCstring ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Category' => $category,
                ':Class' => $class,
                ':RegCstring' => $regtarget,
        );

        return (int)$stm->execute($prm)->current()['Cnt'];
	}

	/**
	 * 対象カテゴリの各クラスの件数から設定する属性を返す
	 * 住所、氏名、電話番号から呼ばれる
	 * @param $creditResult 管理画面から設定された情報とマッチした場合に設定される
	 * @param $orderSeq orderSeq
	 * @param $target 対象条件文字列（非正規化）
	 * @param $category カテゴリ
	 * @param $deli 配送先住所の場合のみdeliという文字列を入れている
	 */
	public function judgeClass($creditResult, $orderSeq, $target, $category, $deli = null) {

	    // ブラック、再々請求、再請求、優良、リピートで設定された完全一致の件数を取得
		// 再々請求と再請求は情報としてないので常に0を返す対応
		// ブラック
		$blackCount = $this->doneJudgeByAutoCreditConditonCnt($category, $target, 5);
		// 再々請求
		//$oftenClaimCount = $this->doneJudgeByAutoCreditConditonCnt($category, $target, 4);
		$oftenClaimCount = 0;
		// 再請求
		//$reClaimCount = $this->doneJudgeByAutoCreditConditonCnt($category, $target, 3);
		$reClaimCount = 0;
		// 優良
		$goodCount = $this->doneJudgeByAutoCreditConditonCnt($category, $target, 2);
		// リピート
		$repeatCount = $this->doneJudgeByAutoCreditConditonCnt($category, $target, 1);

		// 新規フラグ
		$isNew = false;

		// 上記の件数が0であった場合=新規の場合は与信条件にリピートとして登録
		if($blackCount + $goodCount + $oftenClaimCount + $reClaimCount + $repeatCount == 0) {
			// array
			$data = array(
	            'Seq' => -1,                    // dummy
	            'OrderSeq' => $orderSeq,		// OrderSeq
	            'Category' => $category, 		// カテゴリ
	            'RegistDate' => date('Y-m-d'),  // 登録日
	            'Class' => 1,      				// リピート
	            'ValidFlg' => 1,                // 有効
	            'Cstring' => $target,			// 条件文字列
	            'CstringHash' => $deli          // deli文字列
			);

	        // 正規化を適用
	        // 対象はRegCstringのみ
	        $data = $this->mdlcc->fixDataArrayOrg($data, false);
			// 永続化実行
			$savedRow = $this->mdlcc->saveFromArray($data);

			$isNew = true;
		}

		// クラスの設定 　
		if(!is_null($creditResult->Point)) {
			// 手動設定:-1or1
			$isNew ? $creditResult->Class = -1 : $creditResult->Class = 1;
		}
		if($repeatCount > 0) {
			// リピート:1
			$creditResult->Class = 1;
		}
		if($goodCount > 0) {
			// 優良:2
			$creditResult->Class = 2;
		}
		if($reClaimCount > 0) {
			// 再請求:3
			$creditResult->Class = 3;
		}
		if($oftenClaimCount > 0) {
			// 再々請求:4
			$creditResult->Class = 4;
		}
		if($blackCount > 0) {
			// ブラック:5
			$creditResult->Class = 5;
		}

		return $creditResult;
	}

	/**
	 * 住所による与信審査
	 *
	 * @param int $orderSeq 注文Seq
	 * @param string $address 住所
	 * @param string $deli 配送先住所の場合にdeliという文字列
	 * @return CreditJudgeResult
	 */
	public function doneAddress($orderSeq, $address, $deli = null)
	{
	    // 管理画面から設定された部分一致の電話番号を取得
		$creditResult =  $this->doneJudgeByMatches(true, 1, $address, $orderSeq);

		// クラスの判定
		return $this->judgeClass($creditResult, $orderSeq, $address, 1, $deli);
	}

	/**
	 * 氏名による与信審査
	 *
	 * @param int $orderSeq 注文Seq
	 * @param string $name 氏名
	 * @return CreditJudgeResult
	 */
	public function doneName($orderSeq, $name)
	{
	    // 管理画面から設定された部分一致の氏名を取得
		$creditResult =  $this->doneJudgeByMatches(true, 2, $name, $orderSeq);

		// クラスの判定
		return $this->judgeClass($creditResult, $orderSeq, $name, 2);
	}

	/**
	 * 商品名による与信審査
	 *
	 * @param int $orderSeq 注文Seq
	 * @param string $itemName 商品名
	 * @return CreditJudgeResult
	 */
	public function doneItemName($orderSeq, $itemName)
	{
	    return $this->doneJudgeByMatches(true, 3, $itemName, $orderSeq);
	}

	/**
	 * メールアドレス（ドメイン）による与信審査
	 * メールアドレスには＠が一つだけ含まれていること。
	 *
	 * @param int $orderSeq 注文Seq
	 * @param string $mailAddress メールアドレス
	 * @return CreditJudgeResult
	 */
	public function doneMailDomain($orderSeq, $mailAddress)
	{
	    return $this->doneJudgeByMatches(true, 4, $mailAddress, $orderSeq);
	}

	/**
	 * 事業者ログインIDによる与信審査
	 *
	 * @param int $orderSeq 注文Seq
	 * @param string $entLoginId 事業者ログインID
	 * @return CreditJudgeResult
	 */
	public function doneEntLoginId($orderSeq, $entLoginId)
	{
	    return $this->doneJudgeByMatches(false, 5, $entLoginId, $orderSeq);
	}

	/**
	 * 電話番号による与信審査
	 *
	 * @param string $itemName 電話番号
	 * @return CreditJudgeResult
	 */
	public function doneTel($orderSeq, $tel)
	{
	    // 管理画面から設定された完全一致の電話番号を取得
		$creditResult =  $this->doneJudgeByMatches(true, 8, $tel, $orderSeq);

		// クラスの判定
		return $this->judgeClass($creditResult, $orderSeq, $tel, 8);
	}

	/**
	 * 金額による与信審査
	 *
	 * @param int $orderSeq 注文Seq
	 * @param string $money 金額
	 * @return CreditJudgeResult
	 */
	public function doneMoney($orderSeq, $money)
	{
	    return $this->doneJudgeByMatches(true, 9, $money, $orderSeq);

	}

	/**
	 * 郵便番号と住所の整合性による与信審査
	 *
	 * @param string $postalCode 郵便番号
	 * @param string $address 住所
	 * @return CreditJudgeResult
	 */
	public function donePostalCode($postalCode, $address)
	{
	    $result = new CreditJudgeResult();
		$address = mb_ereg_replace("'", "’", $address);

		//
		$regAddress = LogicNormalizer::create($this->map[1])->normalize($address);

		$mdlpc = new TablePostalCode($this->_adapter);

		$datas = $mdlpc->findPostalCode7($postalCode);

		if (empty($datas))
		{
			// 郵便番号該当なし
		}
		else
		{
			for ($i = 0 ; $i < count($datas) ; $i++)
			{
				$kanjiAddress = $datas[$i]->getKanjiAddress();

				//
				$regKanjiAddress = LogicNormalizer::create($this->map[1])->normalize($kanjiAddress);

				$reg = '^' . $regKanjiAddress . '.*$';
			    mb_regex_encoding("UTF-8");
			    mb_ereg_search_init($regAddress, $reg);
			    if (mb_ereg_search())
			    {
			    	return $result;
			    }
			}
		}

		$pdata = $this->mdlcp->findCreditPoint(10)->current();
		$result->Score = $pdata['Point'];
		$result->Note = $pdata['Message'];

		return $result;
	}

	/**
	 * 住所相違による与信審査
	 *
	 * @param string $cusName 購入者氏名
	 * @param string $cusAddress 購入者住所
	 * @param string $deliName 配送先氏名
	 * @param string $deliAddress 配送先住所
	 * @return CreditJudgeResult
	 */
	public function doneDiffAddress($cusName, $cusAddress, $deliName, $deliAddress)
	{
	    $result = new CreditJudgeResult();

		if ($cusName == $deliName && $cusAddress != $deliAddress)
		{
			// 氏名が同一で、住所が違う。
			$pdata = $this->mdlcp->findCreditPoint(9)->current();
			$result->Score = $pdata['Point'];
			$result->Note = $pdata['Message'];
		}

		return $result;
	}

	/**
	 * 利用総額ボーダーによる与信審査
	 *
	 * @param int $useAmount 利用額
	 * @return CreditJudgeResult
	 */
	public function doneUseAmountBorder($useAmount)
	{
	    $result = new CreditJudgeResult();

		$border1 = $this->mdlcp->findCreditPoint(20)->current();		// 総額ボーダー１
		$border2 = $this->mdlcp->findCreditPoint(21)->current();		// 総額ボーダー２

		if ($useAmount >= $border1['GeneralProp'])
		{
			// 設定額①以上の時
			$result->Score = $border1['Point'];
			$result->Note = $border1['Message'];
		}

		if ($useAmount <= $border2['GeneralProp'])
		{
			// 設定額②以下の時
			$result->Score = $border2['Point'];
			$result->Note = $border2['Message'];
		}

		return $result;
	}

	/**
	 * 限度額による与信審査
	 *
	 * @param string $address 住所（同一顧客判断の根拠）
	 * @param int $useAmount 利用額
	 * @param int $limitAmount 限度額
	 * @param int $enterpriseId 事業者ID
	 * @param int $orderSeq 除外する注文Seq
	 * @return CreditJudgeResult
	 */
	public function doneLimitCheck($address, $useAmount, $limitAmount, $enterpriseId, $orderSeq)
	{
        $mdloc = new ViewOrderCustomer($this->_adapter);
        $result = new CreditJudgeResult();

        $address = mb_ereg_replace("'", "’", $address);
        $regAddress = LogicNormalizer::create($this->map[1])->normalize($address);

        $where  = "     DataStatus        < 90 ";
        $where .= " AND RegUnitingAddress = " . CoatStr($regAddress);
        $where .= " AND EnterpriseId      = " . $enterpriseId;
        $where .= " AND OrderSeq         != " . $orderSeq;

        $datas = $mdloc->findOrderCustomerByWhereStr($where, "OrderSeq ASC");

        $totalUseAmount = $useAmount;
        foreach ($datas as $data) {
            $totalUseAmount += $data['UseAmount'];
        }

        if ($totalUseAmount > $limitAmount) {
            // 限度額超過の場合
            $pdata = $this->mdlcp->findCreditPoint(11)->current();
            $result->Score = $pdata['Point'];
            $result->Note = $pdata['Message'];
        }

        return $result;
	}

	/**
	 * 請求取りまとめステータスを設定する
	 * @param Order
	 */
	public function setCombinedClaimStatusSet($order) {

        // 事業者情報を取得
        $mdle = new TableEnterprise($this->_adapter);
        $ri = $mdle->findEnterprise($order['EnterpriseId']);

        $combinedClaimTargetStatus = 0;
        if ($ri->count() > 0) {
            $enterprise = $ri->current();

            // 請求取りまとめモードを確認
            if($enterprise['CombinedClaimMode'] == 1) {
                // 事業者毎
                $combinedClaimTargetStatus = 1;
            }
            else if ($enterprise['CombinedClaimMode'] == 2) {
                // サイト情報を取得
                $mdls = new TableSite($this->_adapter);
                $site = $mdls->findSite($order['SiteId']);
                if ($site->count() > 0) {
                    $site = $site->current();
                    if($site['CombinedClaimFlg'] == 1) {
                        // サイト毎
                        $combinedClaimTargetStatus = 2;
                    }
                }
            }
        }


        $sql = " UPDATE T_Order SET CombinedClaimTargetStatus = :CombinedClaimTargetStatus WHERE OrderSeq = :OrderSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':CombinedClaimTargetStatus' => $combinedClaimTargetStatus,
                ':OrderSeq' => $order['OrderSeq'],
        );

        $stm->execute($prm);
	}

	/**
	 * 補償外を設定する
	 * @param Order
	 */
	public function setOutOfAmendsSet($order) {

        // サイト情報を取得
        $mdls = new TableSite($this->_adapter);
        $ri = $mdls->findSite($order['SiteId']);
        $outOfAmends = 0;// 1:サイト毎 , 0:なし
        if ($ri->count() > 0) {
            $outOfAmends = ($ri->current()['OutOfAmendsFlg'] == 1) ? 1 : 0;
        }

        // T_Order更新
        $sql = " UPDATE T_Order SET OutOfAmends = :OutOfAmends WHERE OrderSeq = :OrderSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OutOfAmends' => $outOfAmends,
                ':OrderSeq' => $order['OrderSeq'],
        );

        $stm->execute($prm);
	}

	/**
	 * setter
	 */
	public function setSmtp($smtp) {
	    $this->smtp = $smtp;
	}

	/**
	 * getter
	 */
	public function getSmtp() {
	    return $this->smtp;
	}

	/**
	 * setter
	 */
	public function setEnterpriseIds($entIds) {
	    // split
		$entIdList = explode(",", $entIds);

		$this->entIds = $entIdList;
	}

	/**
	 * getter
	 */
	public function getEnterpriseIds() {
	    return $this->entIds;
	}

	/**
	 * setter
	 */
	public function setRealTimeCjFlg($realTimeCjFlg) {
	    $this->realTimeCjFlg = $realTimeCjFlg;
	}

	/**
	 * getter
	 */
	public function getRealTimeCjFlg() {
	    return $this->realTimeCjFlg;
	}


	/**
	 * 追加与信条件の判定
	 *
	 * @param int $pattern 検索方法（0:部分一致、1:前方一致、2:後方一致、3:完全一致）
	 * @param int $cstring 追加与信条件 条件文字列
	 * @param int $string1 比較文字列１
	 * @param int $string2 比較文字列２
	 * @return true/false 判定結果
	 */
	public function doneJudgeByAddMatches($pattern, $cstring, $string1, $string2) {

	    if ($string2 == null) {
	        if ($pattern == 0 && strpos($string1, $cstring) !== false) {
	            return true;
	        }

	        if ($pattern == 1 && strpos($string1, $cstring) === 0) {
	            return true;
	        }

	        if ($pattern == 2 && strpos(strrev($string1), strrev($cstring)) === 0) {
	            return true;
	        }

	        if ($pattern == 3 && $string1 == $cstring) {
	            return true;
	        }
	    } else {
	        if ($pattern == 0 && (strpos($string1, $cstring) !== false || strpos($string2, $cstring) !== false)) {
	            return true;
	        }

	        if ($pattern == 1 && (strpos($string1, $cstring) === 0 || strpos($string2, $cstring) === 0)) {
	            return true;
	        }

	        if ($pattern == 2 && (strpos(strrev($string1), strrev($cstring)) === 0 || strpos(strrev($string2), strrev($cstring)) === 0)) {
	            return true;
	        }

	        if ($pattern == 3 && ($string1 == $cstring || $string2 == $cstring)) {
	            return true;
	        }
	    }

	    return false;
	}

	/**
	 * 追加与信条件の判定(金額用)
	 *
	 * @param int $pattern 検索方法（0:部分一致、1:前方一致、2:後方一致、3:完全一致）
	 * @param int $cstring 追加与信条件 条件文字列
	 * @param int $int 比較用の数値
	 * @return true/false 判定結果
	 */
	public function doneJudgeByAddMatchesInt($pattern, $cstring, $int) {

	        if ($pattern == 0 && $int == $cstring) {
	            return true;
	        }

	        if ($pattern == 1 && $int <= $cstring) {
	            return true;
	        }

	        if ($pattern == 2 && $int >= $cstring) {
	            return true;
	        }

	        if ($pattern == 3 && $int == $cstring) {
	            return true;
	        }

	    return false;
	}
}

/**
 * 社内与信結果
 *
 */
class CreditJudgeResult
{
	/**
	 * 分類
	 *
	 * @var int
	 */
	public $Class = -1;

	/**
	 * スコア
	 *
	 * @var int
	 */
	public $Score = 0;

	/**
	 * 備考（メッセージ）
	 *
	 * @var string
	 */
	public $Note = "";
}

