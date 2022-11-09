<?php
namespace models\Logic\CreditJudge;
// require_once 'Logic/CreditJudge.php';
// require_once 'Logic/CreditJudge/Abstract.php';

/**
 * 6末までの限定で暫定的な与信処理を実行する
 */
class LogicCreditJudgeTemporary extends LogicCreditJudgeAbstract {

	/**
	 * メール送信用SMTP
	 */
	private $smtp;

	/**
	 * 加算用事業者一覧
	 */
	private $entIds;

	/**
	 * 与信実行処理
	 * protected docreditjudgeを呼び出す＆トランザクションの管理
	 * @param $oseq 注文seq
	 * @param array 与信結果
	 */
	public function judge($oseq) {
		throw new Exception('method not implemented');
	}

	public function docreditjudge($oseq) {
		// トランザクションの生成
		$this->_db->beginTransaction();

		try {
			$result = $this->_docreditjudge($oseq);
			$this->_db->commit();

			return $result;
		}
		catch(Exception $err) {
			$this->_db->rollBack();

			//
			unset($updateOrder);

			// ステータスを11に戻す
			$updateOrder['DataStatus'] = 11;

			$mdlo = new Table_Order($this->_db);				// 注文データ
			// 注文情報を更新
			$mdlo->saveUpdateWhere($updateOrder, array('OrderSeq' => $oseq, 'DataStatus' => 12));

			return array('rtcode' => 9, 'decSeqId' => '');
		}
	}


	/**
	 * 与信実行処理
	 * @param $oseq 注文Seq
	 * @return array 与信結果
	 */
	protected function _docreditjudge($oseq) {


		// 他判定を行う
		// 注文情報を取得する
		$mdlo = new Table_Order($this->_db);				// 注文データ
		$beforeCjOrder = $mdlo->find($oseq)->current();

		// 与信手段がリアルタイム与信手段の場合にのみ行う。
		// それ以外はreturn 9 を返却する
		$mdle = new Table_Enterprise($this->_db);			// 事業者情報
		$targetentprise = $mdle->findEnterprise2($beforeCjOrder->EnterpriseId);

		if($targetentprise['AutoCreditJudgeMode'] != 3) {
			// エラーへ
			throw new Exception('リアルタイム与信対象ではありません');
		}

		// 与信OKフラグ
		$cjokflg = true;

		// 既存与信処理を呼び出す
		$cj = new Logic_CreditJudge($this->_db);

		// SMTPとentIdsとrealTimeCjFlgの設定
		$cj->setSmtp($this->getSmtp());
		$cj->setEnterpriseIds($this->getEnterpriseIds());
		$cj->setRealTimeCjFlg(true);

		// 与信実行
		$log = $cj->doneAutoCreditJudge($oseq);

		// 与信後のOrderを再取得する
		$targetorder = $mdlo->find($oseq)->current();

		// 事業者与信限度額と注文額を比較し、注文額のほうが大きい場合には与信保留
		if(!empty($targetentprise['UseAmountLimitForCreditJudge']) && $targetorder->UseAmount > $targetentprise['UseAmountLimitForCreditJudge']) {
			$cjokflg = false;
		}

		// TEL番号が10,11桁以外については与信保留
		$mdloc = new View_OrderCustomer($this->_db);
		$targetcustomer = $mdloc->findOrderCustomerByOrderSeq($oseq);

		// 電話番号の桁数は正規化電話番号で比較。先頭0とハイフンが削除されているので9,10桁で判定。
		if(strlen($targetcustomer->RegPhone) != 9 && strlen($targetcustomer->RegPhone) != 10) {
			$cjokflg = false;
		}

		// ブラックの場合には-1100
		if($targetcustomer->Incre_ArName == 5 || $targetcustomer->Incre_ArAddr == 5 || $targetcustomer->Incre_ArTel == 5 ) {
			$targetorder->Incre_ScoreTotal = $targetorder->Incre_ScoreTotal - 1100;
		}

		// 過去注文取得
		$mdlc = new Table_Customer($this->_db);
		$seqs = $mdlc->getPastOrderSeqs($targetcustomer->RegPhone, $targetcustomer->RegUnitingAddress);

		// 取得した過去のOrderSeqから対象のOrderSeqを外す
		$pastOrders = '';
		foreach ($seqs as $key => $value){
			foreach ($value as $key2 => $value2)
			if($value2 != $targetOrderSeq) {
				$pastOrders .= $value2.',';
			}
		}
		$pastOrders = substr($pastOrders, 0, strlen($pastOrders) - 1);

		// 過去45日以上の遅れがあった場合には-1100
		if($pastOrders != '' && $mdlo->findOrderCustomerByLateRecCnt45($pastOrders) > 0) {
			$targetorder->Incre_ScoreTotal = $targetorder->Incre_ScoreTotal - 1100;
		}

		$cjMailOccReason = 0;

		// APIへのリターンコード
		$returncode = 0;

		// 自動与信時にメール送信を行うためのDecSeqId
		$decSeqId = getFormatDateTimeMillisecond() . '-' . "apiTemporaryCreditJudge". '-' . $oseq . '-m';

		// 与信結果判定
		if($targetorder->Incre_ScoreTotal <= -1000) {
			// 与信自動ＮＧ
			$updateOrder['Incre_Note'] = sprintf("(APIリアルタイム与信自動ＮＧ[%s])\n----\n%s", Zend_Date::now()->toString('yyyy-MM-dd'), $increNote);

			$updateOrder['Incre_Status'] = -1;
			$updateOrder['Incre_DecisionDate'] = date("Y-m-d");
			$updateOrder['Incre_DecisionOpId'] = 0;
			$updateOrder['Dmi_Status'] = -1;
			$updateOrder['DataStatus'] = 91;
			$updateOrder['CloseReason'] = 3;
			$updateOrder['Dmi_DecSeqId'] = $decSeqId;

			$updateOrder['Incre_ScoreTotal'] = $targetorder->Incre_ScoreTotal;

			// Insert to T_CjMailHistory
			$cjMailOccReason = 2;

			// APIへリターンコードNG
			$returncode = 2;
		}
		else if($cjokflg && $targetorder->Incre_ScoreTotal >= -49) {
			// 与信自動ＯＫ
			$updateOrder['Incre_Note'] = sprintf("(APIリアルタイム与信自動ＯＫ[%s])\n----\n%s", Zend_Date::now()->toString('yyyy-MM-dd'), $increNote);

			$updateOrder['Incre_Status'] = 1;
			$updateOrder['Incre_DecisionDate'] = date("Y-m-d");
			$updateOrder['Incre_DecisionOpId'] = 0;
			$updateOrder['Dmi_Status'] = 1;
			$updateOrder['DataStatus'] = 31;
			$updateOrder['Dmi_DecSeqId'] = $decSeqId;

			$updateOrder['Incre_ScoreTotal'] = $targetorder->Incre_ScoreTotal;

			// Insert to T_CjMailHistory
			$cjMailOccReason = 1;

			// APIへのリターンコードOK
			$returncode = 1;
		}
		else {
			// 手動与信へ
			$updateOrder['DataStatus'] = 15;

			$updateOrder['Incre_ScoreTotal'] = $targetorder->Incre_ScoreTotal;

			// APIへのリターンコード保留
			$returncode = 0;
		}

		if ($mdlo->isCanceled($oseq))
		{
			// キャンセルされていれば結果を反映しない
		}
		else
		{
			// メール送信を行う
			if ($cjMailOccReason > 0)
			{
				// ユーザへの送信
				$mdlCjMail = new Table_CjMailHistory($this->_db);
				$mdlCjMail->rsvCjMail($oseq, $cjMailOccReason);
			}

			// 注文情報を更新
			$mdlo->saveUpdateWhere($updateOrder, array('OrderSeq' => $oseq, 'DataStatus' => 12));
		}

		// 20130913 新与信システム暫定対応
		// T_CjResultに対象をセット
		$mdlcjr = new Table_CjResult($this->_db);
		unset($cjrdata);

		// 注文情報関連
		$cjrdata['OrderSeq'] = $oseq;
		$cjrdata['OrderId'] = $targetorder->OrderId;
		// 未送信
		$cjrdata['Status'] = 0;

		// 新規作成
		$mdlcjr->saveNew($cjrdata);
		// ここまで新与信システム暫定対応

		// 20131025 請求取りまとめ設定
		$cj->setCombinedClaimStatusSet($targetorder);

		// 20140120 補償外設定
		$cj->setOutOfAmendsSet($targetorder);

		return array('rtcode' => $returncode, 'decSeqId' => $decSeqId);
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
		$this->entIds = $entIds;
	}

	/**
	 * getter
	 */
	public function getEnterpriseIds() {
		return $this->entIds;
	}

}
