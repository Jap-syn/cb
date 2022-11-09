<?php
namespace models\Logic;

use Zend\Db\Adapter\Adapter;
use models\Table\TableClaimHistory;
use models\Table\TableOrder;
use models\Table\TableCjMailHistory;
use Coral\Coral\Mail\CoralMail;
use models\Table\TableUser;
use Zend\Db\ResultSet\ResultSet;
use models\View\MypageViewCode;
use models\Table\TableSystemProperty;

/**
 * メール送信クラス
 */
class LogicMail
{
	/**
	 * アダプタ
	 *
	 * @var Adapter
	 */
	protected $_adapter = null;

	/**
	 * SMTPサーバー
	 *
	 * @var string
	 */
	private $smtp;

	/**
	 * コンストラクタ
	 *
	 * @param Adapter $adapter アダプタ
	 * @param string $smtp SMTPサーバー
	 */
	function __construct(Adapter $adapter, $smtp)
	{
		$this->_adapter = $adapter;
		$this->smtp = $smtp;
	}

	/**
	 * 請求書発行メールを送信する。
	 */
	public function SendOnIssueBill()
	{
        $mdlch = new TableClaimHistory($this->_adapter);
        $datas = $mdlch->getMailTargets();

        // ユーザーID
        $mdlu = new TableUser($this->_adapter);
        $userId = $mdlu->getUserId(TableUser::USERCLASS_SYSTEM, TableUser::SEQ_BATCH_USER);

        $executed_oseqs = array();// Add By Takemasa(NDC) 20151211 重複注文SEQﾌﾞﾛｯｸ(緊急対応)
        foreach($datas as $data)
        {
            // 別送かつ、12時～16時台でない場合はスキップ
            $hour = (int)date('G');
            if ($data['EnterpriseBillingCode'] == null && !(12 <= $hour && $hour <= 16)) {
                continue;
            }

            // 別送かつ、請求日当日の場合スキップ
            if ($data['EnterpriseBillingCode'] == null && $data['ClaimDate'] == date('Y-m-d')) {
                continue;
            }

            // Add By Takemasa(NDC) 20151211 Stt 重複注文SEQﾌﾞﾛｯｸ(緊急対応)
            if (in_array($data['OrderSeq'], $executed_oseqs)) {
                $mdlch->setMailed($data['Seq'], $userId);
                continue;
            }
            else {
                $executed_oseqs[] = $data['OrderSeq'];
            }
            // Add By Takemasa(NDC) 20151211 End 重複注文SEQﾌﾞﾛｯｸ(緊急対応)

            $updFlg = true;
            try{
                $coralmail = new CoralMail($this->_adapter, $this->smtp);
                if ($data['ClaimPattern'] > 1) {
                    $coralmail->SendIssueBillMail2($data['OrderSeq'], $userId);
                }
                else {
                    $coralmail->SendIssueBillMail($data['OrderSeq'], $userId);
                }
            }
            catch (\Exception $e) {
                echo $e->getMessage() . "\n";
                $updFlg = false;

                // 請求履歴.ﾒｰﾙﾘﾄﾗｲ回数のｲﾝｸﾘﾒﾝﾄ
                $this->_adapter->query(" UPDATE T_ClaimHistory SET MailRetryCount = MailRetryCount + 1 WHERE Seq = :Seq "
                    )->execute(array(':Seq' => $data['Seq']));
            }

            // メール送信に成功した場合のみ、送信フラグを更新する
            if ($updFlg) {
                $mdlch->setMailed($data['Seq'], $userId);
            }
        }
	}

    /**
	 * 口振請求書発行案内メールを送信する。
	 */
	public function SendOnCreditTransferInfo()
	{
	    $mdlch = new TableClaimHistory($this->_adapter);
	    $datas = $mdlch->getMailTargetsCreditTransferInfo();

	    // ユーザーID
	    $mdlu = new TableUser($this->_adapter);
	    $userId = $mdlu->getUserId(TableUser::USERCLASS_SYSTEM, TableUser::SEQ_BATCH_USER);

	    foreach($datas as $data)
	    {
	        $updFlg = true;

	        try{
	            $coralmail = new CoralMail($this->_adapter, $this->smtp);
	            $coralmail->SendCreditTransferInfoMail($data['OrderSeq'], $userId);
	        }
	        catch (\Exception $e) {
	            echo $e->getMessage() . "\n";
	            $updFlg = false;

	            // 請求履歴.ﾒｰﾙﾘﾄﾗｲ回数のｲﾝｸﾘﾒﾝﾄ
	            $this->_adapter->query(" UPDATE T_ClaimHistory SET CreditMailRetryCount = CreditMailRetryCount + 1 WHERE Seq = :Seq "
	            )->execute(array(':Seq' => $data['Seq']));
	        }

	        // メール送信に成功した場合のみ、送信フラグを更新する
	        if ($updFlg) {
	            if ($data['ZeroAmountClaimMailFlg'] == 0) {
                    $this->_adapter->query(" UPDATE T_ClaimHistory SET ZeroAmountClaimMailFlg = 1 WHERE Seq = :Seq "
                    )->execute(array(':Seq' => $data['Seq']));
                } else {
                    $this->_adapter->query(" UPDATE T_ClaimHistory SET CreditTransferMailFlg = 1 WHERE Seq = :Seq "
                    )->execute(array(':Seq' => $data['Seq']));
                }
	        }
	    }
	}

	/**
	 * もうすぐお支払メール送信
	 */
	public function SendOnPaymentSoon()
	{
        print_r("before get data\r\n");

        $mdlo = new TableOrder($this->_adapter);
        $datas = $mdlo->getPaymentSoonMailTarget();
        print_r("before loop\r\n");

        // ユーザーID
        $mdlu = new TableUser($this->_adapter);
        $userId = $mdlu->getUserId(TableUser::USERCLASS_SYSTEM, TableUser::SEQ_BATCH_USER);

        foreach($datas as $data)
        {
            try
            {
                $coralmail = new CoralMail($this->_adapter, $this->smtp);
                $result = $coralmail->SendPaymentSoonMail($data['OrderSeq'], $userId);

                if ($result) {
                    $sql  = " UPDATE T_Order ";
                    $sql .= " SET ";
                    $sql .= "     MailPaymentSoonDate = :MailPaymentSoonDate ";
                    $sql .= " ,   UpdateDate          = :UpdateDate ";
                    $sql .= " ,   UpdateId            = :UpdateId ";
                    $sql .= " WHERE OrderSeq          = :OrderSeq ";

                    $stm = $this->_adapter->query($sql);

                    $prm = array(
                            ':MailPaymentSoonDate' => date('Y-m-d'),
                            ':UpdateDate' => date('Y-m-d H:i:s'),
                            ':UpdateId' => $userId,
                            ':OrderSeq' => $data['OrderSeq'],
                    );

                    $stm->execute($prm);
                }
            }
            catch(\Exception $e)
            {

            }
        }
	}

    /**
     * もうすぐお支払メール送信
     */
    public function SendOnCreditTransferSoon()
    {
        print_r("before get data\r\n");

        $mdlo = new TableOrder($this->_adapter);
        $datas = $mdlo->getCreditTransferSoonMailTarget();
        print_r("before loop\r\n");

        // ユーザーID
        $mdlu = new TableUser($this->_adapter);
        $userId = $mdlu->getUserId(TableUser::USERCLASS_SYSTEM, TableUser::SEQ_BATCH_USER);

        foreach($datas as $data)
        {
            try
            {
                $coralmail = new CoralMail($this->_adapter, $this->smtp);
                $result = $coralmail->SendCreditTransferSoonMail($data['OrderSeq'], $userId);

                if ($result) {
                    $sql  = " UPDATE T_Order ";
                    $sql .= " SET ";
                    $sql .= "     MailPaymentSoonDate = :MailPaymentSoonDate ";
                    $sql .= " ,   UpdateDate          = :UpdateDate ";
                    $sql .= " ,   UpdateId            = :UpdateId ";
                    $sql .= " WHERE OrderSeq          = :OrderSeq ";

                    $stm = $this->_adapter->query($sql);

                    $prm = array(
                        ':MailPaymentSoonDate' => date('Y-m-d'),
                        ':UpdateDate' => date('Y-m-d H:i:s'),
                        ':UpdateId' => $userId,
                        ':OrderSeq' => $data['OrderSeq'],
                    );

                    $stm->execute($prm);
                }
            }
            catch(\Exception $e)
            {

            }
        }
    }

    /**
	 * 支払期限経過メール送信
	 */
	public function SendOnLimitPassage()
	{
        $mdlo = new TableOrder($this->_adapter);
        $ri = $mdlo->getLimitPassageMailTarget();
        $rs = new ResultSet();
        $datas = $rs->initialize($ri)->toArray();

        // ユーザーID
        $mdlu = new TableUser($this->_adapter);
        $userId = $mdlu->getUserId(TableUser::USERCLASS_SYSTEM, TableUser::SEQ_BATCH_USER);

        foreach($datas as $data)
        {
            try
            {
                $coralmail = new CoralMail($this->_adapter, $this->smtp);
                $result = $coralmail->SendLimitPassageMail($data['P_OrderSeq'], $userId);

                if ($result) {
                    if ((int)$data['MailLimitPassageCount'] > 0) {
                        $cnt = (int)$data['MailLimitPassageCount'] + 1;
                    }
                    else {
                        $cnt = 1;
                    }

                    $sql  = " UPDATE T_Order ";
                    $sql .= " SET ";
                    $sql .= "     MailLimitPassageDate   = :MailLimitPassageDate ";
                    $sql .= " ,   MailLimitPassageCount  = :MailLimitPassageCount ";
                    $sql .= " ,   UpdateDate             = :UpdateDate ";
                    $sql .= " ,   UpdateId               = :UpdateId ";
                    $sql .= " WHERE P_OrderSeq           = :P_OrderSeq ";

                    $stm = $this->_adapter->query($sql);

                    $prm = array(
                            ':MailLimitPassageDate' => date('Y-m-d'),
                            ':MailLimitPassageCount' => $cnt,
                            ':UpdateDate' => date('Y-m-d H:i:s'),
                            ':UpdateId' => $userId,
                            ':P_OrderSeq' => $data['P_OrderSeq'],
                    );

                    $stm->execute($prm);
                }
            }
            catch(\Coral\Coral\Mail\CoralMailException $e)
            {
                echo $e->getMessage() . "\n";
            }
        }

	}

	/**
	 * 与信結果メールを送信する。
	 */
	public function SendOnCj()
	{
        // 送信対象取得
        $mdlcj = new TableCjMailHistory($this->_adapter);
        $datas = $mdlcj->getMailTargets();

        // ユーザーＩＤ取得
        $mdlu = new TableUser($this->_adapter);
        $opId = $mdlu->getUserId(99, 1);

        $maildatas = array();               // 与信結果メール加盟店送信対象情報
        $mkey = 0;
        $ekey = 0;

        foreach( $datas as $data )
        {
            try
            {
                // 与信結果メールを顧客に送信
                $coralmail = new CoralMail($this->_adapter, $this->smtp);
                $result = $coralmail->SendCjMail($data['OrderSeq'], $data['OccReason'], $opId);

                // 与信結果メール履歴更新
                $mdlcj->doneCjMail($data['OrderSeq'], $result["SendMailFlg"], $result["MailAddress"], $opId);

                // 与信結果メール加盟店送信対象取得
                if ( $result["SendMailFlg"] == 1 )
                {
                    $entid = $result['EnterpriseId'];
                    //NG区分
                    $ngType = 0;
                    if ($data['OccReason'] == 2 || $data['OccReason'] == 3) {
                        // 自動NG / 手動NG
                        $ngType = 1;
                    }

                    if ( $ekey == 0 )
                    {
                        $maildatas[$mkey] = array(
                            'EnterpriseId' => $entid,
                            'OemId' => $result['OemId'],
                            'CpNameKj' => $result['CpNameKj'],
                            'MailAddress' => $result['eMailAddress'],
                            'OrderList' => $result['OrderId'],
                            'OrderCnt' => 1,
                            'NgCnt' => ($ngType == 1 ? 1 : 0),
                        );
                    }
                    else
                    {
                        $insertflg = 0;

                        foreach ( $maildatas as $key => $maildata )
                        {
                            if ( $maildata['EnterpriseId'] ==  $entid)
                            {
                                $maildatas[$key]['OrderList'] .= "、". $result['OrderId'];
                                $maildatas[$key]['OrderCnt'] += 1;
                                $maildatas[$key]['NgCnt'] += ($ngType == 1 ? 1 : 0);

                                $insertflg = 1;
                            }
                        }

                        if ( $insertflg == 0 )
                        {
                            $mkey += 1;

                            $maildatas[$mkey] = array(
                                'EnterpriseId' => $entid,
                                'OemId' => $result['OemId'],
                                'CpNameKj' => $result['CpNameKj'],
                                'MailAddress' => $result['eMailAddress'],
                                'OrderList' => $result['OrderId'],
                                'OrderCnt' => 1,
                                'NgCnt' => ($ngType == 1 ? 1 : 0),
                            );
                        }
                    }
                    $ekey += 1;
                }
            }
            catch(\Exception $e)
            {
                echo $e->getMessage() . "\n";
            }
        }
	}

	/**
	 * 与信待ちメールを送信
	 */
	public function SendOnWaitingCj()
	{
      // 与信待ち注文取得クエリ
	    $sql =
<<<EOQ
 SELECT ORD.OrderId AS OrderId
 FROM T_CjBatchControl CBC
 INNER JOIN T_CjBatchOrder CBO ON (CBO.CjBatchSeq = CBC.CjBatchSeq)
 INNER JOIN T_Order ORD ON (ORD.OrderSeq = CBO.OrderSeq)
 WHERE CBC.Status = 1
 AND TIMEDIFF(NOW(), CBC.Status1Date) >= '00:30:00'
EOQ;
	    $ri = $this->_adapter->query($sql)->execute();
	    $row = ResultInterfaceToArray($ri);

	    //注文IDの配列を作成
	    $orderIdArray = array();
	    foreach($row as $orders){
	        $orderIdArray[] = $orders['OrderId'];
	    }

	    // 対象注文なしの場合は処理終了
	    if(empty($orderIdArray)) {
	        echo ('empty waiting order'.PHP_EOL);
	        return;
	    }

	    // ユーザーＩＤ取得
	    $mdlu = new TableUser($this->_adapter);
	    $opId = $mdlu->getUserId(99, 1);
      try
      {
          // 与信待ちメールを送信
          $coralmail = new CoralMail($this->_adapter, $this->smtp);
          $result = $coralmail->SendWaitingCjMail($orderIdArray, $opId);

      }
      catch(\Exception $e)
      {
          echo $e->getMessage() . "\n";
      }

	}

  /**
	 * 不足入金連絡メールを送信する。
	 */
	public function SendLackOfPay()
	{
	    $mdlch = new TableClaimHistory($this->_adapter);
	    $mdlu = new TableUser($this->_adapter);

	    // ユーザーID
	    $userId = $mdlu->getUserId(TableUser::USERCLASS_SYSTEM, TableUser::SEQ_BATCH_USER);

	    // 不足入金連絡メール対象取得処理
        $sql = <<<EOQ
SELECT DISTINCT rc.OrderSeq
FROM   T_ReceiptControl rc
	   INNER JOIN T_Order o ON (o.P_OrderSeq = rc.OrderSeq)
	   INNER JOIN T_Order o2 ON (o2.P_OrderSeq = o.OrderSeq)
       INNER JOIN T_ClaimControl cc ON (cc.ClaimId = rc.ClaimId)
       INNER JOIN T_Customer C ON C.OrderSeq = o.OrderSeq
	   INNER JOIN T_EnterpriseCustomer EC ON EC.EntCustSeq = C.EntCustSeq
       INNER JOIN T_ManagementCustomer MC ON MC.ManCustId = EC.ManCustId
WHERE  1 = 1
AND    o.ValidFlg = 1
AND    cc.ValidFlg = 1
AND    rc.ValidFlg = 1
AND    o2.Cnl_Status = 0
AND    o2.DataStatus < 91
AND    cc.ClaimedBalance > 0
AND    (IFNULL(o.MailClaimStopFlg, 0) = 0 AND IFNULL(MC.RemindStopFlg, 0) = 0)
AND    o.NewSystemFlg = 1
AND    cc.ReceiptAmountTotal > 0
EOQ;
	    $datas = $this->_adapter->query($sql)->execute();

	    foreach($datas as $data)
	    {
	        try
	        {
                $coralmail = new CoralMail($this->_adapter, $this->smtp);
                $coralmail->SendLackOfPay($data['OrderSeq'], $userId);
	        }
	        catch(\Exception $e)
	        {

	        }

	    }
	}

	/**
	 * 顧客マイページ仮登録完了メール送信処理
	 *
	 * @param array $mail
	 * @param string $baseUrl
	 * @param bool $ismobile
	 */
	public function SendPreregistMail($mail, $baseUrl)
	{
	    // ユーザーＩＤ
	    $mvcode = new MypageViewCode($this->_adapter);
	    $userId = $mvcode->find(86, 2)->current()['Class1'];

	    try
	    {
	        $coralmail = new CoralMail($this->_adapter, $this->smtp);
	        $coralmail->SendPreregistMail($mail, $baseUrl, $userId);
	    }
	    catch(\Exception $e)
	    {
	        echo $e->getMessage() . "\n";
	    }
	}

	/**
	 *  顧客マイページ本登録完了メール送信処理
	 *
	 * @param array $mail メール送信情報
	 * @param bool $ismobile 端末情報（ＰＣ端、スマートフォン端）
	 */
	public function SendRegistMail($mail, $ismobile)
	{
	    // ユーザーＩＤ
	    $mvcode = new MypageViewCode($this->_adapter);
	    $userId = $mvcode->find(86, 2)->current()['Class1'];

	    try
	    {
	        $coralmail = new CoralMail($this->_adapter, $this->smtp);
	        $coralmail->SendRegistMail($mail, $userId, $ismobile);
	    }
	    catch(\Exception $e)
	    {
	        echo $e->getMessage() . "\n";
	    }
	}

    /**
	 * 身分証明書アップロード申請メールの送信処理
	 *
	 * @param array $maildata
	 * @param string $baseUrl
	 */
	public function SendIDUploadMail($maildata, $ismobile)
	{
// Del By Takemasa(NDC) 20151215 Stt 廃止
// 	    // ユーザーＩＤ
// 	    $mvcode = new MypageViewCode($this->_adapter);
// 	    $userId = $mvcode->find(86, 2)->current()['Class1'];
//
// 	    try
// 	    {
// 	        $coralmail = new CoralMail($this->_adapter, $this->smtp);
// 	        $coralmail->SendIDUploadMail($maildata, $userId, $ismobile);
// 	    }
// 	    catch(\Exception $e)
// 	    {
// 	        echo $e->getMessage() . "\n";
// 	    }
// Del By Takemasa(NDC) 20151215 End 廃止
	}

	/**
	 * 顧客マイページログインパスワード変更完了送信処理
	 *
	 * @param int $customerId 顧客ＩＤ
	 * @param bool $ismobile 端末情報（ＰＣ端、スマートフォン端）
	 */
	public function SendChangeMypagePwd ( $customerId, $ismobile )
	{
	    // ユーザーＩＤ
	    $mvcode = new MypageViewCode($this->_adapter);
	    $userId = $mvcode->find(86, 2)->current()['Class1'];

	    try
	    {
	        $coralmail = new CoralMail( $this->_adapter, $this->smtp );
	        $coralmail->SendChangeMypagePwd( $customerId, $userId, $ismobile );
	    }
	    catch( \Exception $e )
	    {
	        echo $e->getMessage() . "\n";
	    }
	}

	/**
	 * 顧客マイページ退会完了
	 *
	 * @param int $customerId
	 * @param bool $ismobile
	 */
	public function SendWithdrawMail ( $customerId, $ismobile )
	{
	    // ユーザーＩＤ
	    $mvcode = new MypageViewCode($this->_adapter);
	    $userId = $mvcode->find(86, 2)->current()['Class1'];

	    try
	    {
	        $coralmail = new CoralMail( $this->_adapter, $this->smtp );
	        $coralmail->SendWithdrawMail( $customerId, $userId, $ismobile );
	    }
	    catch( \Exception $e )
	    {
	        echo $e->getMessage() . "\n";
	    }
	}

	/**
	 * 身分証画像ファイルはメールで身分証明書管理用PCのメールアドレスに送信する
	 *
	 * @param array $maildata
	 * @param string $baseUrl
	 */
	public function SendIdPCUploadImgMail($maildata)
	{
	    // ユーザーＩＤ
	    $mvcode = new MypageViewCode($this->_adapter);
	    $userId = $mvcode->find(86, 2)->current()['Class1'];

	    // メールサブジェクト
	    $subject = '';

	    try
	    {
	        $coralmail = new CoralMail($this->_adapter, $this->smtp);
	        $subject = $coralmail->SendIdPCUploadImgMail($maildata, $userId);
	    }
	    catch(\Exception $e)
	    {
	        echo $e->getMessage() . "\n";
	    }

	    return $subject;
	}

	/**
	 * バッチエラーメール送信処理
	 *
     * @param string $toAddress 送信先
     * @param string $occDate 発生日時
     * @param string $programName プログラム名称
	 */
	public function SendBatchError($toAddress, $occDate, $programName)
	{
        try
        {
            $coralmail = new CoralMail($this->_adapter, $this->smtp);
            $coralmail->SendBatchError($toAddress, $occDate, $programName);
        }
        catch(\Exception $e) { ; }
	}

	/**
	 * ０円請求報告メールを送信する。
	 */
	public function SendOnZeroClaim()
	{
        // ０円請求対象注文ID取得
        $sql = <<<EOQ
SELECT o.OrderId,e.CreditTransferFlg,e.AppFormIssueCond,at.CreditTransferRequestFlg, e.EnterpriseId, e.OemId 
FROM   T_Order o
INNER  JOIN AT_Order at
   ON  o.OrderSeq = at.OrderSeq
INNER  JOIN T_Enterprise e
   ON  o.EnterpriseId = e.EnterpriseId
WHERE  o.DataStatus = 41
AND    0 = (SELECT SUM(UseAmount) FROM T_Order WHERE P_OrderSeq = o.P_OrderSeq)
AND    IFNULL(o.CombinedClaimTargetStatus, 0) IN (0, 91, 92)
EOQ;

        $ri = $this->_adapter->query($sql)->execute(null);
        if ($ri->count() == 0) {
            return;// 該当がない場合は直ちに戻る
        }

        $data = array();
        foreach ($ri as $row) {
            // 口振申込の０円請求は除外する
            if (($row['CreditTransferFlg'] != 0) && ($row['AppFormIssueCond'] == 2) && ($row['CreditTransferRequestFlg'] != 0)) {
                continue;
            }
            $data[] = $row['OrderId'];
        }

        try {
            $coralmail = new CoralMail($this->_adapter, $this->smtp);
            $coralmail->SendOnZeroClaim($data);
        }
        catch(\Exception $e) { ; }
	}

	/**
	 * WCOS URL通知メール
	 */
	public function SendWcosUrl()
	{
	    // ユーザーID
	    $mdlu = new TableUser($this->_adapter);
	    $userId = $mdlu->getUserId(TableUser::USERCLASS_SYSTEM, TableUser::SEQ_BATCH_USER);

	    // WCOS URL通知メール送信対象
        $sql = <<<EOQ
SELECT DISTINCT rc.RepaySeq
  FROM T_RepaymentControl rc
       INNER JOIN T_ClaimControl cc
               ON rc.ClaimId = cc.ClaimId
	   INNER JOIN T_Order o
               ON cc.OrderSeq = o.OrderSeq
 WHERE rc.RepayStatus = 0
   AND rc.ProcessClass = 3
   AND rc.NetStatus = 3
   AND rc.MailFlg = 0
   AND rc.MailRetryCount < 3
   AND o.Cnl_Status = 0
EOQ;
	    $ri = $this->_adapter->query($sql)->execute();

	    foreach($ri as $row)
	    {
	        try
	        {
                $coralmail = new CoralMail($this->_adapter, $this->smtp);
                $coralmail->SendWcosUrl($row['RepaySeq'], $userId);

                // メール送信に成功した場合、送信フラグを更新する
                $this->_adapter->query(" UPDATE T_RepaymentControl SET MailFlg = 1 WHERE RepaySeq = :RepaySeq ")->execute(array(':RepaySeq' => $row['RepaySeq']));
	        }
	        catch(\Exception $e)
	        {
	            // 請求履歴.ﾒｰﾙﾘﾄﾗｲ回数のｲﾝｸﾘﾒﾝﾄ
	            $this->_adapter->query(" UPDATE T_RepaymentControl SET MailRetryCount = MailRetryCount + 1 WHERE RepaySeq = :RepaySeq ")->execute(array(':RepaySeq' => $row['RepaySeq']));
	        }
	    }
	}

    /**
     * 顧客マイページパスワード再発行メール送信処理
     *
     * @param array $mail
     * @param string $baseUrl
     * @param bool $ismobile
     */
    public function SendPasswordResetMail($mail, $baseUrl)
    {
        // ユーザーＩＤ
        $mvcode = new MypageViewCode($this->_adapter);
        $userId = $mvcode->find(86, 2)->current()['Class1'];

        try
        {
            $coralmail = new CoralMail($this->_adapter, $this->smtp);
            $coralmail->SendPasswordResetMail($mail, $baseUrl, $userId);
        }
        catch(\Exception $e)
        {
            echo $e->getMessage() . "\n";
        }
    }

    /**
     * 未返金案件メール
     */
    public function SendRepay()
    {
        // ユーザーID
        $mdlu = new TableUser($this->_adapter);
        $userId = $mdlu->getUserId(TableUser::USERCLASS_SYSTEM, TableUser::SEQ_BATCH_USER);

        // システムプロパティから返金メール送信日数取得
        $mdlsp = new TableSystemProperty($this->_adapter);
        $repayDays = $mdlsp->getValue('[DEFAULT]', 'cbadmin', 'RepayMailSendDays');

        // システム日時
        $sysDate = date('Y-m-d');

        // 未返金案件メール送信対象
        $sql = <<<EOQ
SELECT DISTINCT rc.RepaySeq
  FROM T_RepaymentControl rc
       INNER JOIN T_ClaimControl cc
               ON rc.ClaimId = cc.ClaimId
       INNER JOIN T_Order o
               ON cc.OrderSeq = o.OrderSeq
 WHERE rc.RepayStatus = 0
   AND rc.ProcessClass = 3
   AND rc.NetStatus = 3
   AND rc.RepayMailFlg = 0
   AND rc.RepayMailRetryCount < 3
   AND :sysDate > DATE_ADD(rc.UpdateDate, INTERVAL :repayDays DAY )
EOQ;
        $ri = $this->_adapter->query($sql)->execute(array(':sysDate' => $sysDate, ':repayDays' => $repayDays));

        foreach($ri as $row)
        {
            try
            {
                //指定したClaimIdで返金確定のレコード数を取得
        $sql = <<<EOQ
SELECT COUNT(1) as cnt
  FROM T_RepaymentControl
 WHERE RepayStatus = 1
   AND ClaimId = (
       SELECT ClaimId
         FROM T_RepaymentControl
        WHERE RepaySeq = :RepaySeq
       )
EOQ;
                $cnt = $this->_adapter->query($sql)->execute(array(':RepaySeq' => $row['RepaySeq']))->current()['cnt'];
                //1件以上あればスキップ
                if($cnt >= 1) {
                    continue;
                }

                $coralmail = new CoralMail($this->_adapter, $this->smtp);
                $coralmail->SendRepay($row['RepaySeq'], $userId);

                // メール送信に成功した場合、送信フラグを更新する
                $this->_adapter->query(" UPDATE T_RepaymentControl SET RepayMailFlg = 1 WHERE RepaySeq = :RepaySeq ")->execute(array(':RepaySeq' => $row['RepaySeq']));
            }
            catch(\Exception $e)
            {
                echo $e->getMessage() . "\n";
                // 請求履歴.ﾒｰﾙﾘﾄﾗｲ回数のｲﾝｸﾘﾒﾝﾄ
                $this->_adapter->query(" UPDATE T_RepaymentControl SET RepayMailRetryCount = RepayMailRetryCount + 1 WHERE RepaySeq = :RepaySeq ")->execute(array(':RepaySeq' => $row['RepaySeq']));
            }
        }
    }
}

class CoralMailException extends \Exception
{

}

