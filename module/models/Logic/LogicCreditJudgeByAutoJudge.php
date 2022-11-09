<?php
namespace models\Logic;

use Zend\Db\Adapter\Adapter;
use models\Table\TableOrder;
use models\Table\TableCjMailHistory;
use models\Table\TableCustomer;
use models\View\ViewOrderCustomer;

/**
 * 社内与信自動判定クラス
 */
class LogicCreditJudgeByAutoJudge
{
	/**
	 * アダプタ
	 *
	 * @var Adapter
	 */
	protected $_adapter = null;

	private $plusScore = 0;        // プラススコア
	private $minusScore = 0;       // マイナススコア
	private $plusLimitScore = 0;   // 事業者自動判定スコア限界 999
	private $minusLimitScore = 0;  // 事業者自動判定スコア限界 -999
	private $mdlo;                 // T_Order
	private $mdloc;                // V_OrderCustomer
	private $mdlCjMail;            // T_CjMailHistory
	private $log= '';              // ログ
	private $entIds;
	private $realTimeCjFlg = false;

	/**
	 * コンストラクタ
	 *
	 * @param Adapter $adapter アダプタ
	 *
	 */
	function __construct(Adapter $adapter)
	{
        $this->_adapter = $adapter;

        $this->plusScore = 1100;
        $this->minusScore = -5000;

        $this->plusLimitScore = 1000;
        $this->minusLimitScore = -1000;

        $this->mdlo      = new TableOrder($this->_adapter);
        $this->mdloc     = new ViewOrderCustomer($this->_adapter);
        $this->mdlCjMail = new TableCjMailHistory($this->_adapter);
	}

	/**
	 * 対象注文の自動判定
	 *
	 * @param int $autoCreditJudgeMode 自動与信判定
	 * @param int $orederSeq 注文Seq
	 * @param int $score ここまでの総スコア
     * @return ログ($this->log)
	 */
	public function doneAutoCreditJudge($autoCreditJudgeMode, $orderSeq, $score)
	{
$this->log = sprintf("AutoJudge 01.START:%s\r\n", date('Y-m-d H:i:s'));

        $orderCustomer = $this->mdloc->findOrderCustomerByOrderSeq($orderSeq)->current();

        // 加算点対応
        // 加算点を加える事業所一覧を取得する
        $entIdList = $this->getEnterpriseIds();

        // 対象事業者の場合には加算点をスコア合計に
        if(in_array($orderCustomer['EnterpriseId'], $entIdList)) {
            // 注文情報から加算点の取得
            preg_match("/^\[加算点:[-]?[0-9]+(¥.[0-9]+)?\]/", $orderCustomer['Ent_Note'], $m);
            preg_match("/[-]?[0-9]+(¥.[0-9]+)?/", $m[0], $match);

            // 加算点があった場合にはスコアに加算する
            $score += is_numeric($match[0]) ? $match[0] : 0;
        }

        // APIリアルタイム与信事業者については常に自動与信事業者として扱う
        $autoCreditJudgeMode = ($autoCreditJudgeMode == 3) ? 0 : $autoCreditJudgeMode;

        // 自動与信の場合に行う
        // 自動与信の場合に行う
        if($autoCreditJudgeMode == 0) {
$this->log .= sprintf("AutoJudge 02.Get OrderCustomer:%s\r\n", date('Y-m-d H:i:s'));
            //
            // プラススコアを足しこむかの判定
            $addScore = true;

            // 与信属性がリピートであるかどうか
            if($orderCustomer['Incre_ArTel'] != 1 && $orderCustomer['Incre_ArAddr'] != 1) {
                unset($addScore);
$this->log .= sprintf("AutoJudge 03.repeat:%s\r\n", date('Y-m-d H:i:s'));
            }
            // 再与信対象かどうか
            if($addScore && preg_match("/^事業者側修正により再与信戻し/", $orderCustomer['Incre_Note'])) {
                unset($addScore);
$this->log .= sprintf("AutoJudge 04.reCreditJudge:%s\r\n", date('Y-m-d H:i:s'));
            }

            // 正規化された住所と電話番号の取得
            $regPhone = $orderCustomer['RegPhone'];
            $regUnitingAddress = $orderCustomer['RegUnitingAddress'];

            // 対象のOrderSeq
            $targetOrderSeq = $orderCustomer['OrderSeq'];

            // 正規化された住所と電話番号をもとに、T_Customerから2年以内の対象のOrderSeqの配列を取得する
            $mdlc = new TableCustomer($this->_adapter); // Table: 購入者
            $seqs = $mdlc->getPastOrderSeqs($regPhone, $regUnitingAddress);

            // 取得した過去のOrderSeqから対象のOrderSeqを外す
            $pastOrders = '';
            foreach ($seqs as $key => $value){
                foreach ($value as $key2 => $value2)
                    if($value2 != $targetOrderSeq) {
                        $pastOrders .= $value2.',';
                    }
            }
            $pastOrders = substr($pastOrders, 0, strlen($pastOrders) - 1);

            // 念のためにINのデータをチェック
            // 空の場合には以降の処理を行わない
            if($pastOrders == '') unset($addScore);

            // 過去二年間に注文があったかどうか？
            if($addScore && $this->judgeTwoYearsOrder($pastOrders))  {
                unset($addScore);
$this->log .= sprintf("AutoJudge 05.not order before tow years:%s\r\n", date('Y-m-d H:i:s'));
            }

            // 過去二年間に不払もしくは債権返却キャンセルがないこと
            if($addScore && $this->judgeSaiken($pastOrders)) {
                unset($addScore);
                // マイナススコアを足す
                $score = $score + $this->minusScore;
$this->log .= sprintf("AutoJudge 06.not noreceipt_damaged & not saikenCacel:%s\r\n", date('Y-m-d H:i:s'));
            }

            // 注文合計金額が5万以下
            if($addScore && $orderCustomer['UseAmount'] > 50000) {
                unset($addScore);
$this->log .= sprintf("AutoJudge 07.useAmount under 50k:%s\r\n", date('Y-m-d H:i:s'));
            }

            // 対象注文の履歴チェック
            if($addScore && $this->judgeOrderHistory($pastOrders, $orderCustomer['UseAmount'])) {
                unset($addScore);
            }
            // ここまで全て通った場合にはスコアにプラススコアを足しこむ
            if(isset($addScore)) $score = $score + $this->plusScore;

            // 総点数を更新
            unset($updateData);
            $updateData['Incre_ScoreTotal'] = $score;
$this->log .= sprintf("AutoJudge 13.score is ".$score." :%s\r\n", date('Y-m-d H:i:s'));

            // T_Order更新
            $this->mdlo->saveUpdate($updateData, $orderSeq);
        }

        // 事業者の自動与信モードが0：自動与信対象、1：与信全部OK、2：自動与信対象外のいずれにも
        // 該当しない場合は0に読み替える（2014.3.6 eda）
        if(!in_array(((int)$autoCreditJudgeMode), array(0, 1, 2))) {
            $autoCreditJudgeMode = 0;
        }

        // 点数と自動与信判定による処理(リアルタイム与信以外の場合に行う)
        if(!$this->realTimeCjFlg) {
            $this->autoJudge($score, $autoCreditJudgeMode, $orderSeq, $orderCustomer['Incre_Note']);
        }

$this->log .= sprintf("AutoJudge 14.END:%s\r\n", date('Y-m-d H:i:s'));

        return $this->log;
    }

	/**
	 * 点数と自動与信判定により、メールを送信する
	 * @param $score 与信点数+自動与信点数
	 * @param $orderSeq
	 */
	private function autoJudge($score, $autoCreditJudgeMode, $orderSeq, $increNote) {

        $updateOrder = array();
        $cjMailOccReason = 0;

        // 事業者毎の与信判定により自動判定
        if($autoCreditJudgeMode == 1 || ($autoCreditJudgeMode == 0 && $score >= $this->plusLimitScore)) {
            // 与信自動ＯＫ
            $updateOrder['Incre_Note'] = sprintf("(与信自動ＯＫ[%s])\n----\n%s", date('Y-m-d'), $increNote);

            $updateOrder['Incre_Status'] = 1;
            $updateOrder['Incre_DecisionDate'] = date("Y-m-d");
            $updateOrder['Incre_DecisionOpId'] = 0;
            $updateOrder['Dmi_Status'] = 1;
            $updateOrder['DataStatus'] = 31;

            // Insert to T_CjMailHistory 2013.8.7 kashira
            $cjMailOccReason = 1;
        }
        if(($autoCreditJudgeMode == 0 && $score <= $this->minusLimitScore)) {
            // 与信自動ＮＧ
            $updateOrder['Incre_Note'] = sprintf("(与信自動ＮＧ[%s])\n----\n%s", date('Y-m-d'), $increNote);

            $updateOrder['Incre_Status'] = -1;
            $updateOrder['Incre_DecisionDate'] = date("Y-m-d");
            $updateOrder['Incre_DecisionOpId'] = 0;
            $updateOrder['Dmi_Status'] = -1;
            $updateOrder['DataStatus'] = 91;
            $updateOrder['CloseReason'] = 3;

            // Insert to T_CjMailHistory 2013.8.7 kashira
            $cjMailOccReason = 2;
        }
        else if ($autoCreditJudgeMode == 2 || (($autoCreditJudgeMode == 0 && ($this->minusLimitScore < $score &&  $score < $this->plusLimitScore)))) {
            // 手動与信へ
            $updateOrder['DataStatus'] = 15;
        }
        else {
            // ありえない
        }

        if ($this->mdlo->isCanceled($orderSeq))
        {
            // キャンセルされていれば結果を反映しない
            // キャンセル判断は2013.8.14に追加
        }
        else
        {
            // Insert to T_CjMailHistory 2013.8.14 kashira
            if ($cjMailOccReason > 0)
            {
                $this->mdlCjMail->rsvCjMail($orderSeq, $cjMailOccReason);
            }

            // 注文情報を更新
            $this->mdlo->saveUpdateWhere($updateOrder, array('OrderSeq' => $orderSeq, 'DataStatus' => 11));
        }
	}

	/**
	 * 過去二年間に注文があったかのチェック
	 * @param $pastOrders 対象者の過去注文OrderSeq
	 * @return boolean true:存在する false:存在しない
	 */
	private function judgeTwoYearsOrder($pastOrders) {
		return $this->mdlo->findOrderCustomerByTwoYearsOrderCnt($pastOrders) == 0
					? true : false;
	}

	/**
	 * 過去二年間に債権返却キャンセルもしくは不払があったかのチェック
	 * @param $pastOrders 対象者の過去注文OrderSeq
	 * @return boolean true:存在する false:存在しない
	 */
	private function judgeSaiken($pastOrders) {
		return $this->mdlo->findOrderCustomerBySaikenCancelCnt($pastOrders) > 0 ||
				$this->mdlo->findOrderCustomerByNoRecDamagedCnt($pastOrders) > 0
					? true : false;
	}

	/**
	 * 過去二年間の対象履歴の個別判定
	 * @param $pastOrders 対象者の過去注文OrderSeq
	 * @param $userAmount 合計額
	 * @return boolean true:存在する false:存在しない
	 */
	private function judgeOrderHistory($pastOrders, $useAmonut) {

		// 与信NGがある
		if($this->mdlo->findOrderCustomerByNgCnt($pastOrders) > 0)  {
$this->log .= sprintf("AutoJudge 08.NG Order:%s\r\n", date('Y-m-d H:i:s'));
			return true;
		}

		// クローズしていない注文がある
		if($this->mdlo->findOrderCustomerByNoCloseCnt($pastOrders) > 0)  {
$this->log .= sprintf("AutoJudge 09.Not Close Order:%s\r\n", date('Y-m-d H:i:s'));
			return true;
		}

		// 支払遅れが5日以上ある
		if($this->mdlo->findOrderCustomerByLateRecCnt($pastOrders) > 0 )  {
$this->log .= sprintf("AutoJudge 10.Over five days:%s\r\n", date('Y-m-d H:i:s'));
			return true;
		}

		// 過去支払最大額以上である
		// 過去支払最大額取得
		$pastMaxAmount = $this->mdlo->findOrderCustomerByMaxUseAmountCnt($pastOrders);

		// 過去支払額の設定
		// +1000円か1.3倍の大きい方を取得する
		$maxAmount = $pastMaxAmount + 1000 >= $pastMaxAmount * 1.3 ? $pastMaxAmount + 1000 : $pastMaxAmount * 1.3;
		if($maxAmount < $useAmonut) {
$this->log .= sprintf("AutoJudge 11.Over past useramount :%s\r\n", date('Y-m-d H:i:s'));
			return true;
		}

		// 直前注文がキャンセル
		if($this->mdlo->findOrderCustomerByOneBeforeCnlCnt($pastOrders) > 0 )  {
$this->log .= sprintf("AutoJudge 12.Cancel Close :%s\r\n", date('Y-m-d H:i:s'));
			return true;
		}

		return false;
	}

	/**
	 * setter
	 */
	public function setEnterpriseIds($entIds) {
		$this->entIds = $entIds;
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
}

