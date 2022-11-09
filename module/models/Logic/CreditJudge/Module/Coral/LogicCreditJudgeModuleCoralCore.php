<?php
namespace models\Logic\CreditJudge\Module\Coral;

use Zend\Db\Adapter\Adapter;
use models\Logic\CreditJudge\LogicCreditJudgeAbstract;
use models\Table\TableCreditPoint;
use models\Table\TableCreditCondition;
use models\Table\TableCreditConditionAddress;
use models\Table\TableCreditConditionName;
use models\Table\TableCreditConditionItem;
use models\Table\TableCreditConditionDomain;
use models\Table\TableCreditConditionEnterprise;
use models\Table\TableCreditConditionPhone;
use models\Table\TableCreditConditionMoney;
use models\Table\TableCustomer;
use models\Table\TableDeliveryDestination;
use models\Table\TableOrder;
use models\Table\TablePostalCode;
use models\Table\TableManagementCustomer;
use models\View\ViewOrderCustomer;
use models\Logic\CreditJudge\LogicCreditJudgeLocalScore;
use models\Logic\LogicNormalizer;
use Zend\Db\ResultSet\ResultSet;
use models\Table\TableAddCreditCondition;
use models\Table\TableOrderSummary;
use models\View\ViewDelivery;

/**
 * 後払い.com独自の基準による基本与信モジュール
 * 注文内容を元に基本的なスコアリングを実施する
 * judgeメソッドが返す可能性がある値：
 * 		JUDGE_RESULT_CONTINUE：審査継続
 */
class LogicCreditJudgeModuleCoralCore extends LogicCreditJudgeAbstract {
	/**
	 * 社内与信ポイントマスター
	 * @access protected
	 * @var TableCreditPoint
	 */
	protected $mdlcp;

	/**
	 * 社内与信条件(アドレス)
	 * @access protected
	 * @var TableCreditCondition
	 */
	protected $mdlcca;

	/**
	 * 社内与信条件(名前)
	 * @access protected
	 * @var TableCreditCondition
	 */
	protected $mdlccn;
	/**
	 * 社内与信条件(商品名)
	 * @access protected
	 * @var TableCreditCondition
	 */
	protected $mdlcci;
	/**
	 * 社内与信条件(ドメイン)
	 * @access protected
	 * @var TableCreditCondition
	 */
	protected $mdlccd;
	/**
	 * 社内与信条件(加盟店ID)
	 * @access protected
	 * @var TableCreditCondition
	 */
	protected $mdlccep;
	/**
	 * 社内与信条件(電話番号)
	 * @access protected
	 * @var TableCreditCondition
	 */
	protected $mdlcc;
	/**
	 * 社内与信条件(金額)
	 * @access protected
	 * @var TableCreditCondition
	 */
	protected $mdlccm;

	/**
	 * 対象となった与信条件項目
	 * @access protected
	 * @var array
	 */
	protected $creditConditionMatchData;

	/**
	 * 対象となった与信条件項目SEQS
	 * @var array
	 */
	protected $judConditionSeqs;

	/**
	 * 与信対象項目の正規化設定
	 * @access protected
	 * @var array
	 */
    protected $map = array(
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

    /**
     * 与信判定基準ID
     *
     * @access protected
     * @var int
     */
    protected $_creditCriterionId;

    /**
     * スコアリング結果
     */
    protected $_resultArray;

    /**
     * 事業者ID
     * @var int
     */
    protected $eid;

    /**
     * 対象となった与信条件項目SEQS
     * @var array
     */
    protected $ConditionSeqs;

	/**
	 * LogicCreditJudgeModuleCoralCoreの新しいインスタンスを初期化する
	 *
	 * @param Adapter $adapter アダプタ
	 * @param null | array $options 追加オプション
	 */
	public function __construct(Adapter $adapter, array $options = array()) {
	    parent::__construct($adapter, $options);
		$this->mdlcp = new TableCreditPoint($this->_adapter);
		$this->mdlcca = new TableCreditConditionAddress($this->_adapter);
		$this->mdlccn = new TableCreditConditionName($this->_adapter);
		$this->mdlcci = new TableCreditConditionItem($this->_adapter);
		$this->mdlccd = new TableCreditConditionDomain($this->_adapter);
		$this->mdlcce = new TableCreditConditionEnterprise($this->_adapter);
		$this->mdlccp = new TableCreditConditionPhone($this->_adapter);
		$this->mdlccm = new TableCreditConditionMoney($this->_adapter);
		$this->creditConditionMatchData = array();
		$this->judConditionSeqs = array();
		$this->getDbCache()->prepareCache();
		$this->_resultArray = array();
		$this->ConditionSeqs = array();

	}

    /**
     * 指定の注文の審査を実行し、判定結果を返す。
     * 判定結果は以下の定数値のいずれかを返す。
     * JUDGE_RESULT_NG：与信NG確定
     * JUDGE_RESULT_OK：与信OK確定
     * JUDGE_RESULT_PENDING：与信保留確定（＝手動与信対象）
     * JUDGE_RESULT_CONTINUE：審査継続
     *
     * @param int $oseq 注文SEQ
     * @return int 判定結果
     */
	public function judge($oseq) {
        $scoreTotal = 0;

        $GLOBALS['CreditLog']['Incre_SnapShot'] = array();  // 与信結果スナップショットを配列定義＆初期化

        // DBキャッシュを作業用にクリア
        $this->getDbCache()->prepareCache();

        // メンバ変数に事業者IDを保存
        $this->eid = $this->getDbCache()->fetchOrderCustomer($oseq)->current()['EnterpriseId'];

        // 請求先に対するスコアリングを実施
        $this->judConditionSeqs = array();
        $scoreTotal += $this->scoreForCustomer($oseq);
        $GLOBALS['CreditLog']['Jud_CustomerSeqs'] = implode(',', $this->judConditionSeqs);

        // 注文商品に対するスコアリングを実施
        $this->judConditionSeqs = array();
        $scoreTotal += $this->scoreForOrderItems($oseq);
        $GLOBALS['CreditLog']['Jud_OrderItemsSeqs'] = implode(',', $this->judConditionSeqs);

        // 配送先に対するスコアリングを実施
        $this->judConditionSeqs = array();
        $scoreTotal += $this->scoreForDeliveryDestination($oseq);
        $GLOBALS['CreditLog']['Jud_DeliveryDestinationSeqs'] = implode(',', $this->judConditionSeqs);

        // 注文情報に対するスコアリングを実施
        $scoreTotal += $this->scoreForOrder($oseq);

        // 与信結果スナップショット生成(arrayからJSON変換)
        $arySnapShot = array();
        foreach ($GLOBALS['CreditLog']['Incre_SnapShot'] as $row) {
            $aryOne = array('Category' => $row['Category'] , 'Cstring' => $row['Cstring'] , 'Point' => $row['Point'] , 'Comment' => $row['Comment']);
            if (!in_array($aryOne, $arySnapShot)) {
                $arySnapShot[] = $aryOne;
            }
        }
        if (!empty($arySnapShot)) {
            $GLOBALS['CreditLog']['Incre_SnapShot'] = \Zend\Json\Json::encode($arySnapShot);
        }
        else {
            $GLOBALS['CreditLog']['Incre_SnapShot'] = '';
        }

        // スコアリング結果
        $this->_resultArray['TotalScore'] = $scoreTotal;
        $this->_resultArray['CreditConditionMatchData'] = json_encode($this->creditConditionMatchData);

        $this->debug(sprintf('[%s] Total Score = %s', $oseq, $scoreTotal));

        // このモジュールは常に審査継続を返す
        return LogicCreditJudgeAbstract::JUDGE_RESULT_CONTINUE;
	}

	/**
	 * 請求先に対するスコアリングを実施する
	 *
	 * @access protected
	 * @param int $oseq 注文SEQ
	 * @return int 請求先に対して実施したスコアリング結果の合計
	 */
	protected function scoreForCustomer($oseq) {
	    $eData = array(
			'RealCallScore' => 0,
			'RealSendMailScore' => 0
		);
		$score = 0;
		/** @var LogicCreditJudgeLocalScore */
		$res = null;

		//顧客情報取得
		$orderCustomer = $this->getDbCache()->fetchOrderCustomer($oseq)->current();
		$mdlmc = new TableManagementCustomer($this->getAdapter());
		$mc = $mdlmc->findByOrderSeq($oseq)->current();

		/** @var TableCustomer */
		$table = new TableCustomer($this->getAdapter());
		$sql = ' SELECT MailAddress FROM T_Customer WHERE OrderSeq = :OrderSeq ';
		$cust = $this->getAdapter()->query($sql)->execute(array(':OrderSeq' => $oseq))->current();

		/** @var TableOrder */
		$table = new TableOrder($this->getAdapter());
		$sql = ' SELECT UseAmount FROM T_Order WHERE OrderSeq = :OrderSeq ';
		$order = $this->getAdapter()->query($sql)->execute(array(':OrderSeq' => $oseq))->current();

		// 氏名に対するスコアを算出
		$res = $this->doneName($oseq, $mc['NameKj']);
		$score += $res->Score;
		$eData['Incre_NameScore'] = $res->Score;
		$eData['Incre_NameNote'] = $res->Note;

		// 住所に対するスコアを算出
		$res = $this->doneAddress($oseq, $mc['UnitingAddress']);
		$score += $res->Score;
		$eData['Incre_AddressScore'] = $res->Score;
		$eData['Incre_AddressNote'] = $res->Note;

		// メールアドレスのドメインに対するスコアを算出
		if (isset($cust['MailAddress'])) {
            $res = $this->doneMailDomain($oseq, $cust['MailAddress']);
            $score += $res->Score;
            $eData['Incre_MailDomainScore'] = $res->Score;
            $eData['Incre_MailDomainNote'] = $res->Note;
		}

		// 電話番号に対するスコアを算出
		$res = $this->doneTel($oseq, $mc['Phone']);
		$score += $res->Score;
		$eData['Incre_TelScore'] = $res->Score;
		$eData['Incre_TelNote'] = $res->Note;

		// 金額に対するスコアを算出
		$res = $this->doneMoney($oseq, $order['UseAmount']);
		$score += $res->Score;
		$eData['Incre_MoneyScore'] = $res->Score;
		$eData['Incre_MoneyNote'] = $res->Note;

		// 郵便番号整合性によるスコアを算出
		$res = $this->donePostalCode($mc['PostalCode'], $mc['UnitingAddress']);
		$score += $res->Score;
		$eData['Incre_PostalCodeScore'] = $res->Score;
		$eData['Incre_PostalCodeNote'] = $res->Note;

		// 重みづけ
		$score = $this->scoreWeighting($score, $this->getCreditCriterionId(), 401);

		// スコアリング結果
		$this->_resultArray['Customer_ScoreTotal'] = $score;
		$this->_resultArray['Customer_ScoreDetail'] = $eData;
		$this->_resultArray['Customer_CustomerId'] = $orderCustomer['CustomerId'];
		$GLOBALS['CreditLog']['Jud_CustomerScore'] = $score;

		// 請求先スコアの合計を返す
		return $score;
	}

	/**
	 * 注文商品に対するスコアリングを実施する
	 *
	 * @access protected
	 * @param int $oseq 注文SEQ
	 * @return int 注文商品に対して実施したスコアリング結果の合計
	 */
	protected function scoreForOrderItems($oseq) {
	    $score = 0;
		/** @var LogicCreditJudgeLocalScore */
		$res = null;
		$resAll = array();

		$orderCustomer = $this->getDbCache()->fetchOrderCustomer($oseq)->current();
		$itemDelis = $this->getDbCache()->fetchItemDelivery($oseq);

		foreach($itemDelis as $itemDeli) {
			// 商品名に対するスコアを算出
			$res = $this->doneItemName($oseq, $itemDeli['ItemNameKj']);
			$score += $res->Score;

            $resAry = array(
                "Incre_Score" => $res->Score,
                "Incre_Note" => $res->Note,
            );
            $resAll[$itemDeli['OrderItemId']] = $resAry;
		}

		// 重みづけ
		$score = $this->scoreWeighting($score, $this->getCreditCriterionId(), 402);

		// スコアリング結果
		$this->_resultArray['OrderItems_ScoreTotal'] = $score;
		$this->_resultArray['OrderItems_ScoreDetail'] = $resAll;
		$GLOBALS['CreditLog']['Jud_OrderItemsScore'] = $score;

		// 注文商品スコアの合計を返す
		return $score;
	}

	/**
	 * 配送先に対するスコアリングを実施する
	 *
	 * @access protected
	 * @param int $oseq 注文SEQ
	 * @return int 配送先に対して実施したスコアリング結果の合計
	 */
	protected function scoreForDeliveryDestination($oseq) {
	    $score = 0;
		/** @var LogicCreditJudgeLocalScore */
		$res = null;
		$resAll = array();

		$orderCustomer = $this->getDbCache()->fetchOrderCustomer($oseq)->current();
		$itemDelis = $this->getDbCache()->fetchItemDelivery($oseq);

		//顧客情報取得
		$mdlmc = new TableManagementCustomer($this->getAdapter());
		$managementCustomer = $mdlmc->findByOrderSeq($oseq)->current();

		$i = 0;
		foreach($itemDelis as $itemDeli) {
			$eData = array();

			if($orderCustomer['AnotherDeliFlg'] == 1 && $i == 0) {	// 別配送先指定時のみ、各項目のスコアリンゴを実施
				// 配送先氏名に対するスコアを算出
				$res = $this->doneName($oseq, $itemDeli['DestNameKj']);
				if($i == 0 ) $score += $res->Score;		// スコア加算は先頭の1アイテムのみ
				$eData['Incre_NameScore'] = $res->Score;
				$eData['Incre_NameNote'] = $res->Note;

				// 配送先住所に対するスコアを算出
				$res = $this->doneAddress($oseq, $itemDeli['UnitingAddress'], 'deli');
				if($i == 0 ) $score += $res->Score;		// スコア加算は先頭の1アイテムのみ
				$eData['Incre_AddressScore'] = $res->Score;
				$eData['Incre_AddressNote'] = $res->Note;

				// 配送先電話番号に対するスコアを算出
				$res = $this->doneTel($oseq, $itemDeli['Phone']);
				if($i == 0 ) $score += $res->Score;		// スコア加算は先頭の1アイテムのみ
				$eData['Incre_TelScore'] = $res->Score;
				$eData['Incre_TelNote'] = $res->Note;

				// 配送先郵便番号整合性によるスコアを算出
				$res = $this->donePostalCode($itemDeli['PostalCode'], $itemDeli['UnitingAddress']);
				if($i == 0 ) $score += $res->Score;		// スコア加算は先頭の1アイテムのみ
				$eData['Incre_PostalCodeScore'] = $res->Score;
				$eData['Incre_PostalCodeNote'] = $res->Note;

				// 住所相違によるスコアを算出
				$res = $this->doneDiffAddress(
					$managementCustomer['NameKj'], $managementCustomer['UnitingAddress'],
					$itemDeli['DestNameKj'], $itemDeli['UnitingAddress']);
				if($i == 0 ) $score += $res->Score;		// スコア加算は先頭の1アイテムのみ
				$eData['Incre_SameCnAndAddrScore'] = $res->Score;
				$eData['Incre_SameCnAndAddrNote'] = $res->Note;
			} else {
				// 別配送先指定がない場合は主要項目を0で計上
				$eData = array(
					'Incre_NameScore' => 0,
					'Incre_AddressScore' => 0,
				    'Incre_TelScore' => 0,
					'Incre_SameCnAndAddrScore' => 0,
					'Incre_PostalCodeScore' => 0
				);
			}

			$resAll[$itemDeli['DeliDestId']] = $eData;

			$i++;
		}

		// 重みづけ
		$score = $this->scoreWeighting($score, $this->getCreditCriterionId(), 403);

		// スコアリング結果
		$this->_resultArray['DeliveryDestination_ScoreTotal'] = $score;
		$this->_resultArray['DeliveryDestination_ScoreDetail'] = $resAll;
		$GLOBALS['CreditLog']['Jud_DeliveryDestinationScore'] = $score;

		// 配送先先スコアの合計を返す
		return $score;
	}

	/**
	 * 注文情報に対するスコアリングを実施する
	 *
	 * @access protected
	 * @param int $oseq 注文SEQ
	 * @return int 注文情報に対して実施したスコアリング結果の合計
	 */
	protected function scoreForOrder($oseq) {
	    $eData = array();
		$score = 0;
		/** @var LogicCreditJudgeLocalScore */
		$res = null;

		$orderCustomer = $this->getDbCache()->fetchOrderCustomer($oseq)->current();

		/** @var TableOrder */
		$table = new TableOrder($this->getAdapter());

		// 特定事業者によるスコアを算出
		$this->judConditionSeqs = array();
		$res = $this->doneEntLoginId($oseq, $orderCustomer['EnterpriseLoginId']);
		$score += $res->Score;
		$eData['Incre_AtnEnterpriseScore'] = $res->Score;
		$eData['Incre_AtnEnterpriseNote'] = $res->Note;
		$GLOBALS['CreditLog']['Jud_EnterpriseScore'] = $res->Score;
		$GLOBALS['CreditLog']['Jud_EnterpriseSeqs'] = implode(',', $this->judConditionSeqs);

		// 利用額ボーダーによるスコアを算出
        // 請求金額０円でも注文受付するため、請求額０円の時は無視する
        $execflg = true;
        $sql = ' SELECT e.CreditTransferFlg,e.AppFormIssueCond,ao.CreditTransferRequestFlg FROM T_Order o LEFT JOIN T_Enterprise e ON o.EnterpriseId=e.EnterpriseId LEFT JOIN AT_Order ao ON o.OrderSeq=ao.OrderSeq WHERE o.OrderSeq = :OrderSeq ';
        $ent = $this->getAdapter()->query($sql)->execute(array(':OrderSeq' => $oseq))->current();
        if ((($ent['CreditTransferFlg'] == 1) || ($ent['CreditTransferFlg'] == 2) || ($ent['CreditTransferFlg'] == 3)) && (($ent['AppFormIssueCond'] == 2) || ($ent['AppFormIssueCond'] == 0)) && ($orderCustomer['UseAmount'] == 0) && (($ent['CreditTransferRequestFlg'] == 1) || ($ent['CreditTransferRequestFlg'] == 2))) {
            $execflg = false;
        }
        if ($execflg) {
            $res = $this->doneUseAmountBorder($orderCustomer['UseAmount']);
        } else {
            $res = new LogicCreditJudgeLocalScore();
        }
        $score += $res->Score;
        $eData['Incre_BorderScore'] = $res->Score;
        $eData['Incre_BorderNote'] = $res->Note;
        $GLOBALS['CreditLog']['Jud_UseAmountScore'] = $res->Score;

		// スコアリング結果
		$this->_resultArray['Order_ScoreTotal'] = $score;
		$this->_resultArray['Order_ScoreDetail'] = $eData;

		// 注文情報スコアの合計を返す
		return $score;
	}

	/**
	 * 部分一致あるいは完全一致による与信
	 *
	 * @param boolean $isMatchesIn true:部分一致 false:完全一致
	 * @param int $category カテゴリ
	 * @param string $target 与信対象
	 * @param int $oseq 注文Seq
	 * @return LogicCreditJudgeLocalScore この与信によるスコアリング結果
	 */
	public function doneJudgeByMatches($isMatchesIn, $category, $target, $oseq) {
	    $mdlvd = new ViewDelivery($this->getAdapter());
	    $mdlacc = new TableAddCreditCondition($this->getAdapter());
	    $mdlos = new TableOrderSummary($this->getAdapter());
	    $mdloc = new ViewOrderCustomer($this->getAdapter());


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
                $matchDatas = $this->mdlcca->judge($category, $isMatchesIn, $regtarget, $ConditionSeq);
                break;
            case 2:
                // マッチする与信条件を取得する。
                $matchDatas = $this->mdlccn->judge($category, $isMatchesIn, $regtarget, $ConditionSeq);
                break;
            case 3:
                // マッチする与信条件を取得する。
                $matchDatas = $this->mdlcci->judge($category, $isMatchesIn, $regtarget, $ConditionSeq);
                break;
            case 4:
                // マッチする与信条件を取得する。
                $matchDatas = $this->mdlccd->judge($category, $isMatchesIn, $regtarget, $ConditionSeq);
                break;
            case 5:
                // マッチする与信条件を取得する。
                $matchDatas = $this->mdlcce->judge($category, $isMatchesIn, $regtarget, $ConditionSeq);
                break;
            case 8:
                // マッチする与信条件を取得する。
                $matchDatas = $this->mdlccp->judge($category, $isMatchesIn, $regtarget, $ConditionSeq);
                break;
            case 9:
                // マッチする与信条件を取得する。
                $matchDatas = $this->mdlccm->judge($category, $regtarget, $ConditionSeq);
                break;
            default:
                break;
        }

        $result = new LogicCreditJudgeLocalScore();

        $point = 0;
        $comment = "";
        foreach($matchDatas as $matchData) {
            $sPattern = $matchData['SearchPattern'];
            $regcString = $matchData['RegCstring'];
            $P_cstring = $matchData['Cstring'];

            if($matchData['Category'] != 9 ){
                // 検索方法チェック
                if (!$isMatchesIn || $sPattern == 0 ||
                   ($sPattern == 1 && strpos($regtarget, $regcString) === 0) ||
                   ($sPattern == 2 && strpos(strrev($regtarget), strrev($regcString)) === 0) ||
                   ($sPattern == 3 && $regtarget == $regcString)) {
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
                            $addjudge = $this->doneJudgeByAddMatches($pattern, $regCstring, $OrderSum['RegUnitingAddress'], $OrderSum['RegDestUnitingAddress']);
                            break;
                        case 2: // 氏名
                            $addjudge = $this->doneJudgeByAddMatches($pattern, $regCstring, $OrderSum['RegNameKj'], $OrderSum['RegDestNameKj']);
                            break;
                        case 3: // 商品名
                            foreach ($itemDeli as $item) {
                                $addjudge = $this->doneJudgeByAddMatches($pattern, $regCstring, LogicNormalizer::create(LogicNormalizer::FILTER_FOR_ITEM_NAME)->normalize($item['ItemNameKj']), null);
                                if ($addjudge) {
                                    break;
                                }
                            }
                            break;
                        case 4: // ドメイン
                            $addjudge = $this->doneJudgeByAddMatches($pattern, $regCstring, LogicNormalizer::create(LogicNormalizer::FILTER_FOR_MAIL)->normalize($OrderSum['MailAddress']), null);
                            break;
                        case 5: // 加盟店ID
                            $addjudge = ($regCstring == LogicNormalizer::create(LogicNormalizer::FILTER_FOR_ID)->normalize($OrderCust['EnterpriseLoginId'])) ? true : false;
                            break;
                        case 8: // 電話番号
                            $addjudge = $this->doneJudgeByAddMatches($pattern, $regCstring, $OrderSum['RegPhone'], $OrderSum['RegDestPhone']);
                            break;
                        case 9: // 金額
                            $addjudge = $this->doneJudgeByAddMatchesInt($pattern, $regCstring, $OrderCust['UseAmount']);
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

            // 加盟店独自の設定確認
            $cceid = nvl($matchData['EnterpriseId'], -1);
            if ($cceid <> -1 && $cceid <> $this->eid) {
                continue;
            }

            // T_Order設定用
            array_push($this->creditConditionMatchData, $matchData);
            // ポイントの合算
            $point += $matchData['Point'];

            // コメントの合算
            $comment .= $matchData['Comment'] ." ". $matchData['Point'] ."/";

            // 与信結果ログ用
            $this->judConditionSeqs[] = $matchData['Seq'];

            $this->ConditionSeqs[] = $matchData['Seq'];

            // 与信結果スナップショット配列へ積上げ
            $GLOBALS['CreditLog']['Incre_SnapShot'][] = $matchData;

            if ($matchData['AddConditionCount'] > 0) {
                $datas = $mdlacc->findAddConditionValid($matchData['Seq'],$category);

                if ($truecnt > 0) {
                    foreach($datas as $data) {
                        $GLOBALS['CreditLog']['Incre_SnapShot'][] = $data;
                    }
                }

            }
        }

        $result->Score = $point;
        $result->Note = $comment;

        return $result;
	}

	/**
	 * 住所による与信審査
	 *
	 * @param int $oseq 注文Seq
	 * @param string $address 住所
	 * @param string $deli 配送先住所の場合にdeliという文字列
	 * @return LogicCreditJudgeLocalScore この与信によるスコアリング結果
	 */
	public function doneAddress($oseq, $address, $deli = null) {
	    // 管理画面から設定された部分一致の電話番号を取得
		$creditResult =  $this->doneJudgeByMatches(true, 1, $address, $oseq);

		return $creditResult;
	}

	/**
	 * 氏名による与信審査
	 *
	 * @param int $oseq 注文Seq
	 * @param string $name 氏名
	 * @return LogicCreditJudgeLocalScore この与信によるスコアリング結果
	 */
	public function doneName($oseq, $name) {
	    // 管理画面から設定された部分一致の氏名を取得
		$creditResult =  $this->doneJudgeByMatches(true, 2, $name, $oseq);

        return $creditResult;
	}

	/**
	 * 商品名による与信審査
	 *
	 * @param int $oseq 注文Seq
	 * @param string $itemName 商品名
	 * @return LogicCreditJudgeLocalScore この与信によるスコアリング結果
	 */
	public function doneItemName($oseq, $itemName) {
	    return $this->doneJudgeByMatches(true, 3, $itemName, $oseq);
	}

	/**
	 * メールアドレス（ドメイン）による与信審査
	 * メールアドレスには＠が一つだけ含まれていること。
	 *
	 * @param int $oseq 注文Seq
	 * @param string $mailAddress メールアドレス
	 * @return LogicCreditJudgeLocalScore この与信によるスコアリング結果
	 */
	public function doneMailDomain($oseq, $mailAddress) {
	    return $this->doneJudgeByMatches(true, 4, $mailAddress, $oseq);
	}

	/**
	 * 事業者ログインIDによる与信審査
	 *
	 * @param int $oseq 注文Seq
	 * @param string $entLoginId 事業者ログインID
	 * @return LogicCreditJudgeLocalScore この与信によるスコアリング結果
	 */
	public function doneEntLoginId($oseq, $entLoginId) {
	    return $this->doneJudgeByMatches(false, 5, $entLoginId, $oseq);
	}

	/**
	 * 電話番号による与信審査
	 *
	 * @param string $itemName 電話番号
	 * @return LogicCreditJudgeLocalScore この与信によるスコアリング結果
	 */
	public function doneTel($oseq, $tel) {
	    // 管理画面から設定された完全一致の電話番号を取得
		$creditResult =  $this->doneJudgeByMatches(true, 8, $tel, $oseq);

		return $creditResult;
	}

	/**
	 * 金額による与信審査
	 *
	 * @param string $itemName 電話番号
	 * @return LogicCreditJudgeLocalScore この与信によるスコアリング結果
	 */
	public function doneMoney($oseq, $money) {
	    return  $this->doneJudgeByMatches(true, 9, $money, $oseq);

	}

	/**
	 * 郵便番号と住所の整合性による与信審査
	 *
	 * @param string $postalCode 郵便番号
	 * @param string $address 住所
	 * @return LogicCreditJudgeLocalScore この与信によるスコアリング結果
	 */
	public function donePostalCode($postalCode, $address) {

        $result = new LogicCreditJudgeLocalScore();
        $address = mb_ereg_replace("'", "’", $address);

        $regAddress = LogicNormalizer::create($this->map[1])->normalize($address);

        $mdlpc = new TablePostalCode($this->getAdapter());

        $datas = $mdlpc->findPostalCode7($postalCode);

        if(empty($datas)) {
            // 郵便番号該当なし
        }
        else {
            $datasCount = 0;
            if(!empty($datas)) {
                $datasCount = count($datas);
            }
            for ($i = 0 ; $i < $datasCount ; $i++) {
                $kanjiAddress = $datas[$i]->getKanjiAddress();

                $regKanjiAddress = LogicNormalizer::create($this->map[1])->normalize($kanjiAddress);

                $reg = '^' . $regKanjiAddress . '.*$';
                mb_regex_encoding("UTF-8");
                mb_ereg_search_init($regAddress, $reg);
                if(mb_ereg_search()) {
                    return $result;
                }
            }
        }

        $pdata = $this->mdlcp->findCreditPoint($this->getCreditCriterionId(), 106)->current();
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
	 * @return LogicCreditJudgeLocalScore この与信によるスコアリング結果
	 */
	public function doneDiffAddress($cusName, $cusAddress, $deliName, $deliAddress) {
	    $result = new LogicCreditJudgeLocalScore();

		if($cusName == $deliName && $cusAddress != $deliAddress) {
			// 氏名が同一で、住所が違う。
			$pdata = $this->mdlcp->findCreditPoint($this->getCreditCriterionId(), 105)->current();
			$result->Score = $pdata['Point'];
			$result->Note = $pdata['Message'];
		}

		return $result;
	}

	/**
	 * 利用総額ボーダーによる与信審査
	 *
	 * @param int $useAmount 利用額
	 * @return LogicCreditJudgeLocalScore この与信によるスコアリング結果
	 */
	public function doneUseAmountBorder($useAmount) {
	    $result = new LogicCreditJudgeLocalScore();

		$border1 = $this->mdlcp->findCreditPoint($this->getCreditCriterionId(), 301)->current();		// 総額ボーダー１
		$border2 = $this->mdlcp->findCreditPoint($this->getCreditCriterionId(), 302)->current();		// 総額ボーダー２

		if($useAmount >= $border1['GeneralProp']) {
			// 設定額①以上の時
			$result->Score = $border1['Point'];
			$result->Note = $border1['Message'];
		}

		if($useAmount <= $border2['GeneralProp']) {
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
	 * @param int $oseq 除外する注文Seq
	 * @return LogicCreditJudgeLocalScore この与信によるスコアリング結果
	 */
	public function doneLimitCheck($address, $useAmount, $limitAmount, $enterpriseId, $oseq) {

        $mdloc = new ViewOrderCustomer($this->_adapter);
        $result = new LogicCreditJudgeLocalScore();

        $address = mb_ereg_replace("'", "’", $address);
        $regAddress = LogicNormalizer::create($this->map[1])->normalize($address);

        $where  = "     DataStatus        < 90 ";
        $where .= " AND RegUnitingAddress = " . CoatStr($regAddress);
        $where .= " AND EnterpriseId      = " . $enterpriseId;
        $where .= " AND OrderSeq         != " . $oseq;

        $datas = $mdloc->findOrderCustomerByWhereStr($where, "OrderSeq ASC");

        $totalUseAmount = $useAmount;
        foreach ($datas as $data) {
            $totalUseAmount += $data['UseAmount'];
        }

        if($totalUseAmount > $limitAmount) {
            // 限度額超過の場合
            $pdata = $this->mdlcp->findCreditPoint(11)->current();
            $result->Score = $pdata['Point'];
            $result->Note = $pdata['Message'];
        }

        return $result;
	}

	/**
	 * 与信判定基準IDを取得する
	 *
	 * @return number
	 */
	public function getCreditCriterionId() {
	    return $this->_creditCriterionId;
	}
	/**
	 * 与信判定基準IDを設定する
	 *
	 * @param int $creditCriterionId 与信判定基準ID
	 * @return LogicCreditJudgeModuleCoralCoreThread このインスタンス
	 */
	public function setCreditCriterionId($creditCriterionId) {
	    $this->_creditCriterionId = $creditCriterionId;
	}

    /**
     * スコアリング結果取得
     *
     * @return array スコアリング結果
     */
    public function getResultArray() {
        return $this->_resultArray;
    }

    /**
     * スコア重みづけ
     *
     * @param int $score 重みづけするスコア
     * @param int $creditCriterionId 与信判定基準ID
     * @param int $cpId 与信ポイントID
     */
    protected function scoreWeighting($score, $creditCriterionId, $cpId) {
        // 社内与信ポイントマスタ
        $pdata = $this->mdlcp->findCreditPoint($creditCriterionId, $cpId)->current();

        // 社内与信ポイントマスタ.倍率取得
        $rate = 1;
        if ($pdata !== false && !empty($pdata)) {
            $rate = $pdata['Rate'];

            if (!is_null($rate) && strlen($rate) > 0) {
                $rate = floatval($rate);
            }
        }

        // 重みづけ
        $scoreWeighting = $score * $rate;

        return $scoreWeighting;
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
