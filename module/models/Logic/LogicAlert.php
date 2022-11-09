<?php
namespace models\Logic;

use Zend\Db\Adapter\Adapter;
use Zend\Text\Table\Table;
use models\Table\TableUser;
use models\Table\TableStagnationAlert;
use models\Table\TableSystemProperty;

/**
 * アラームクラス
 */
class LogicAlert
{
	/**
	 * アダプタ
	 *
	 * @var Adapter
	 */
	protected $_adapter = null;

	/**
	 * コンストラクタ
	 *
	 * @param Adapter $adapter アダプタ
	 */
	public function __construct(Adapter $adapter)
	{
	    $this->_adapter = $adapter;
	}

    /**
     * 停滞アラーム処理
     *
     * @return bool
     */
	public function alert(){

	    $mdlsa = new TableStagnationAlert($this->_adapter);                  // 停滞アラート
	    $mdlsp = new TableSystemProperty($this->_adapter);                   // システムプロパティ
	    $mdlu = new TableUser($this->_adapter);                              // ユーザー

        try {

            $this->_adapter->getDriver()->getConnection()->beginTransaction();

            $opId = $mdlu->getUserId(99, 1);                                // ユーザーID

            ////////////////////////停滞アラートの初期化/////////////////////////////
            $data = array(
                    'ValidFlg' => 0,                                        // 無効
                    'UpdateId' => $opId,                                    // 更新者
            );

            $conditionArray = array(
                    'ValidFlg' => 1,                                        // 有効
            );

            $mdlsa->saveUpdateWhere($data, $conditionArray);

            ////////////////////////伝票登録停滞アラーム処理/////////////////////////////
            $longJournalDays = $mdlsp->getValue('[DEFAULT]', 'systeminfo','LongJournalDays');     // ｱﾗｰﾄ対象伝票登録待ち期間

            $journalDatas = $this->getJournalDatas($longJournalDays);   // 伝票登録停滞アラーム対象データ

            foreach ($journalDatas as $journalData){
                $mdlsa->saveNew(
                    array(
                            'AlertClass' => 0,                                      // 停滞アラート区分(0：伝票登録待ち)
                            'AlertSign' => 1,                                       // アラートサイン(1：アラート)
                            'OrderSeq' => $journalData['OrderSeq'],                 // 注文SEQ
                            'StagnationDays' => $journalData['StagnationDays'],     // 停滞期間日数
                            'EnterpriseId' => $journalData['EnterpriseId'],         // 加盟店ID
                            'AlertJudgDate' => $journalData['AlertGetDate'],        // アラート抽出日時
                            'RegistId' => $opId,                                    // 登録者
                            'UpdateId' => $opId,                                    // 更新者
                            'ValidFlg' => 1,                                        // 有効フラグ
                    )
                );
            }

            ////////////////////////着荷登録停滞アラーム処理/////////////////////////////
            $longConfirmArrivalDays = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'LongConfirmArrivalDays');   // ｱﾗｰﾄ対象着荷確認待ち期間

            $confirmArrivalDatas = $this->getConfirmArrivalDatas($longConfirmArrivalDays);

            foreach ($confirmArrivalDatas as $confirmArrivalData){
                $mdlsa->saveNew(
                    array(
                            'AlertClass' => 1,                                      // 停滞アラート区分(1:着荷確認待ち)
                            'AlertSign' => 1,                                       // アラートサイン(1：アラート)
                            'OrderSeq' => $confirmArrivalData['OrderSeq'],                 // 注文SEQ
                            'StagnationDays' => $confirmArrivalData['StagnationDays'],     // 停滞期間日数
                            'EnterpriseId' => $confirmArrivalData['EnterpriseId'],  // 加盟店ID
                            'AlertJudgDate' => $confirmArrivalData['AlertGetDate'], // アラート抽出日時
                            'RegistId' => $opId,                                    // 登録者
                            'UpdateId' => $opId,                                    // 更新者
                            'ValidFlg' => 1,                                        // 有効フラグ
                    )
                );
            }

            ////////////////////////長期ログインがない加盟店/////////////////////////////
            $longLoginDays = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'LongLoginDays');                     // ｱﾗｰﾄ対象ﾛｸﾞｲﾝなし加盟店期間

            $loginDaysDatas = $this->getLoginDaysDatas($longLoginDays);

            foreach ($loginDaysDatas as $loginDaysData){
                $mdlsa->saveNew(
                    array(
                            'AlertClass' => 2,                                      // 停滞アラート区分(2：加盟店での未ログイン)
                            'AlertSign' => 1,                                       // アラートサイン(1：アラート)
                            'OrderSeq' => NULL,                                     // 注文SEQ
                            'StagnationDays' => $loginDaysData['StagnationDays'],   // 停滞期間日数
                            'EnterpriseId' => $loginDaysData['EnterpriseId'],       // 加盟店ID
                            'AlertJudgDate' => $loginDaysData['AlertGetDate'],      // アラート抽出日時
                            'RegistId' => $opId,                                    // 登録者
                            'UpdateId' => $opId,                                    // 更新者
                            'ValidFlg' => 1,                                        // 有効フラグ
                    )
                );
            }

            $this->_adapter->getDriver()->getConnection()->commit();

        } catch (Exception $e) {
            $this->_adapter->getDriver()->getConnection()->rollBack();
            throw $e;
        }
	}

	/**
	 * 伝票登録停滞アラーム対象データの取得
	 * @param int $longJournalDays ｱﾗｰﾄ対象伝票登録待ち期間
	 *
	 */
	private function getJournalDatas($longJournalDays)
	{
        $sql  = " SELECT DISTINCT ";
        $sql .= "       DATEDIFF(CURDATE(), T_Order.ReceiptOrderDate) AS StagnationDays ";   // 停滞期間日数
        $sql .= " ,     T_Order.OrderSeq ";                                                  // 注文SEQ
        $sql .= " ,     T_Order.EnterpriseId ";                                              // 注文SEQ
        $sql .= " ,     CURDATE()                                     AS AlertGetDate ";     // WK.アラート抽出日時
        $sql .= " FROM  T_Order ";
        $sql .= " INNER JOIN T_OrderItems ";
        $sql .= " ON T_OrderItems.OrderSeq = T_Order.OrderSeq ";
        $sql .= " WHERE T_Order.ValidFlg = 1 ";
        $sql .= " AND T_Order.DataStatus = 31 ";                                             // (31：伝票番号入力待ち)
        $sql .= " AND DATEDIFF(CURDATE(), T_Order.ReceiptOrderDate) >= :LongJournalDays ";
        $sql .= " AND T_OrderItems.Deli_JournalIncDate IS NULL ";
        $sql .= " AND T_OrderItems.ValidFlg = 1 ";

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':LongJournalDays' => $longJournalDays,
	    );

	    return $stm->execute($prm);
	}

	/**
	 * 着荷登録停滞アラーム対象データの取得
	 * @param int $longConfirmArrivalDays ｱﾗｰﾄ対象着荷確認待ち期間
	 *
	 */
	private function getConfirmArrivalDatas($longConfirmArrivalDays)
	{
        $sql =<<<EOQ
/* 滞留アラートバッチ抽出SQL */
SELECT
      DATEDIFF(CURDATE(), os.Deli_JournalIncDate) AS StagnationDays   -- 停滞期間日数
,     o.OrderSeq                                                 -- 注文SEQ
,     o.EnterpriseId                                             -- 加盟店ID
,     CURDATE()                            AS AlertGetDate             -- アラート抽出日時
FROM  T_Order o
      INNER JOIN T_OrderSummary os
              ON o.OrderSeq = os.OrderSeq
WHERE 1 = 1
AND   IFNULL(o.OutOfAmends, 0) = 0  /* 保障案件のみ */
AND   IFNULL(o.Deli_ConfirmArrivalFlg, 0) <> 1
AND   o.DataStatus IN (41, 51, 61)
AND   o.Cnl_Status = 0
AND   DATEDIFF(CURDATE(), os.Deli_JournalIncDate) >= :LongConfirmArrivalDays
EOQ;
	    $stm = $this->_adapter->query($sql);

        $prm = array(
                ':LongConfirmArrivalDays' => $longConfirmArrivalDays,
        );

	    return $stm->execute($prm);
	}

	/**
	 * 長期ログインがない加盟店対象データの取得
	 * @param int $longLoginDays ｱﾗｰﾄ対象ﾛｸﾞｲﾝなし加盟店期間
	 *
	 */
	private function getLoginDaysDatas($longLoginDays)
	{
	    $sql  = " SELECT ";
        $sql .= "       DATEDIFF(CURDATE(), T_User.LastLoginDate) AS StagnationDays ";      // 停滞期間日数
        $sql .= " ,     T_Enterprise.EnterpriseId ";                                        // 加盟店ID
        $sql .= " ,     CURDATE()                           AS AlertGetDate ";              // アラート抽出日時
        $sql .= " FROM T_Enterprise ";
        $sql .= " INNER JOIN T_User ";
        $sql .= " ON T_Enterprise.EnterpriseId = T_User.Seq ";
        $sql .= " WHERE T_User.ValidFlg = 1 ";
        $sql .= " AND T_User.UserClass = 2 ";
        $sql .= " AND T_Enterprise.OemId = 0 ";
        $sql .= " AND DATEDIFF(CURDATE(), T_User.LastLoginDate) >= :LongLoginDays ";

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':LongLoginDays' => $longLoginDays,
	    );

	    return $stm->execute($prm);
	}
}
