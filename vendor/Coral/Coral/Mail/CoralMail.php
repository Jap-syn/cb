<?php
namespace Coral\Coral\Mail;

use Coral\Base\BaseGeneralUtils;
use Coral\Coral\Mail\CoralMailException;
use Coral\Coral\CoralValidate;
use Coral\Coral\CoralCodeMaster;
use Coral\Coral\Validate\CoralValidateMultiMail;
use models\Logic\LogicSbps;
use Zend\Db\Adapter\Adapter;
use Zend\Log\Writer\Mail;
use Zend\Mail\Transport\Smtp;
use Zend\Mail\Transport\SmtpOptions;
use Zend\Mail\Message;
use Zend\Mime\Mime;
use Zend\Mail\Transport\Sendmail;
use cbadmin\Application;
use models\Table\TableEnterprise;
use models\Table\TableMailTemplate;
use models\Table\TableCancel;
use models\Table\TableCode;
use models\Table\TableOrder;
use models\Table\TableOrderItems;
use models\Table\TableClaimHistory;
use models\Table\TableMailSendHistory;
use models\Table\TablePayingControl;
use models\Table\TableSite;
use models\View\ViewOrderCustomer;
use models\View\ViewArrivalConfirm;
use models\View\MypageViewMailSendHistory;
use models\View\MypageViewMailTemplate;
use models\Table\TableMypageCustomer;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;
use models\Table\TableMypageOrder;
use models\Table\ATableOrder;
use models\Logic\LogicPayeasy;
use Coral\Coral\CoralOrderUtility;
use Zend\Json\Json;
use models\Table\TableSystemProperty;

setlocale( LC_ALL, 'ja_JP.UTF-8' );
ini_set( 'default_charset', 'UTF-8' );
mb_internal_encoding( 'UTF-8' );
mb_http_output('UTF-8');
mb_regex_encoding( 'UTF-8' );

class CoralMail
{
	/**
	 * 指定のSMTPサーバを利用してメールを送信するCoralMailのインスタンスを
	 * 作成するファクトリメソッド。
	 * このメソッドはCoralMailクラスのコンストラクタへの純粋なプロキシとして
	 * 動作する
	 *
     * @param Adapter $adapter
	 * @param string $smtpServer SMTPサーバのホスト名またはIPアドレス
	 * @return CoralMail
	 */
	public static function create(Adapter $adapter, $smtpServer) {
		return new CoralMail( $adapter, $smtpServer );
	}

	/**
	 * DBアクセスオブジェクト
	 * @var Adapter
	 */
	private $adapter;

	/**
	 * SMTP
	 * @var Smtp
	 */
	private $smtp;

	/**
	 * キャラクターセット
	 */
	private $charset;

	/**
	 * メール送信履歴作成用
	 */
	private $history;

	/**
	 * コンストラクタ
	 *
     * @param Adapter $adapter
	 * @param string $smtpServer SMTPサーバー
	 * @param string $charset キャラクターセット
     * @throws CoralMailException
	 */
	public function __construct($adapter, $smtpServer = "localhost", $charset = "ISO-2022-JP")
	{
	   try {
            $this->adapter = $adapter;

            $smtpOptions = new SmtpOptions(array(
                'host' => $smtpServer,
            ));
            $this->smtp = new Smtp($smtpOptions );
			$this->charset = $charset;
			$this->history = new TableMailSendHistory($this->adapter);
		} catch(\Exception $e) {
		    throw new CoralMailException( 'cannot initialized.', 0, $e );
		}
	}

	/**
	 * メール送信を実行する。
	 *
	 * @param string $fromName 送信者名
	 * @param string $fromAddress 送信者アドレス
	 * @param string $toName 受取人名
	 * @param string $toAddress 受取人アドレス
	 * @param string $subject 件名
	 * @param string $body 本文
	 */
	public function sendDone($fromName, $fromAddress, $toName, $toAddress, $subject, $body)
	{
	    // ↓↓↓運用テスト向けメール送信した体へのチェック処理(20150905_1430_suzuki_h)
	    if ($toAddress == '*****') {
	        return;
	    }
	    // ↑↑↑運用テスト向けメール送信した体へのチェック処理(20150905_1430_suzuki_h)

	    // メール送信先が空の場合は終了
	    if (!isset($toAddress)) {
	        return;
	    }

	    // メールの形式が不正な場合は送信しない
	    $validator = new CoralValidateMultiMail();
	    if ( !$validator->isValid($toAddress) ) {
            return;
	    }

	    $mail = new Message();
	    $mail->setEncoding('ASCII');

	    $headers = new \Zend\Mail\Headers();
	    $ary_hdr = array();
	    $ary_hdr[] = "Content-Type: text/plain; charset=ISO-2022-JP\n";
	    $headers->addHeaders($ary_hdr);
	    $mail->setHeaders($headers);

	    // 送信元
        $mail->setFrom($fromAddress, mb_encode_mimeheader($fromName, 'ISO-2022-JP', 'Q'));//メール文字化け対応(20150209_1700)

		// 送信先
		$toAddresses = explode(",", $toAddress);
		for ($i = 0 ; $i < count($toAddresses) ; $i++)
		{
		    $mail->addTo($toAddresses[$i], mb_encode_mimeheader($toName, 'ISO-2022-JP', 'Q'));//メール文字化け対応(20150209_1700)
		}

		// 件名
		$mail->setSubject(mb_encode_mimeheader($subject, 'ISO-2022-JP', 'Q'));//メール文字化け対応(20150209_1700)

		// 本文
		$mail->setBody(
    		$this->toMailChar(mb_convert_kana($body, 'K', 'UTF-8')),
    		null,
    		Mime::ENCODING_7BIT
		);

		// 送信
        $this->smtp->send($mail);
	}

	/**
	 * 事業者登録完了（サービス開始）メールを送信する。
	 *
	 * @param int $eid 事業者ID
	 * @param int $userId ユーザーID
	 */
	public function SendExamMail($eid, $userId)
	{
	    try {
			// 事業者情報の取得
			$mdle = new TableEnterprise($this->adapter);
			$edata = $mdle->findEnterprise($eid)->current();

			// メールテンプレートの取得
			$mdlmt = new TableMailTemplate($this->adapter);
			$template = $mdlmt->findMailTemplate(1, $edata['OemId'])->current();

			// メールの構築
			$body = mb_ereg_replace('{EnterpriseNameKj}', $edata['EnterpriseNameKj'], $template['Body']);
			$body = mb_ereg_replace('{LoginId}', $edata['LoginId'], $body);
			$body = mb_ereg_replace('{LoginPasswd}', $edata['LoginPasswd'], $body);

			$this->insertInfoParamServer($body, $eid, $edata['OemId']);
            $this->insertInfoParamServer($template['FromTitle'], $eid, $edata['OemId']);
            $this->insertInfoParamServer($template['FromAddress'], $eid, $edata['OemId']);
            $this->insertInfoParamServer($template['Subject'], $eid, $edata['OemId']);

			// メール送信履歴登録
			$mailSendSeq = $this->history->saveNew(array(
			        'MailTemplateId' => 1,
			        'OrderSeq' => null,
			        'EnterpriseId' => $eid,
			        'ManCustId' => null,
			        'ToAddress' => $edata['MailAddress'],
			        'CcAddress' => null,
			        'BccAddress' => null,
			        'Subject' => $template['Subject'],
			        'Body' => $body,
			        'MailSendDate' => date('Y-m-d H:i:s'),
			        'ErrFlg' => 0,
			        'ErrReason' => null,
			        'RegistId' => $userId,
			        'UpdateId' => $userId,
			        'ValidFlg' => 1,
			));
			$this->sendDone(
				$template['FromTitle'],
				$template['FromAddress'],
				$edata['EnterpriseNameKj'],
				$edata['MailAddress'],
				$template['Subject'],
				$body
			);

		} catch(\Exception $e) {
		    if (isset($mailSendSeq)) {
		        // メール送信履歴を登録した場合、エラー理由を更新
		        $this->history->saveUpdate(array(
		                'ErrFlg' => 1,
		                'ErrReason' => $e->getMessage(),
		                'UpdateId' => $userId,
		        ), $mailSendSeq);
		    }
			throw new CoralMailException( 'cannot sent registration completed mail.', 0, $e );
		}
	}

	/**
	 * 事業者パスワード情報通知メールを送信する
	 *
	 * @param int $eid 事業者ID
	 * @param string $passwd 通知するパスワード
	 * @param int $userId ユーザーID
	 */
	public function SendPasswdInfoMail($eid, $passwd, $userId)
	{
		$passwd = trim(nvl($passwd));
		try {
			// 事業者情報の取得
			$mdle = new TableEnterprise($this->adapter);
			$edata = $mdle->findEnterprise($eid)->current();

			// メールテンプレートの取得
			$mdlmt = new TableMailTemplate($this->adapter);
			$template = $mdlmt->findMailTemplate(23, $edata['OemId'])->current();

			if(!strlen($passwd)) {
				throw new \Exception('パスワード情報が設定されていません');
			}

			// メールの構築
			$body = mb_ereg_replace('{EnterpriseNameKj}', $edata['EnterpriseNameKj'], $template['Body']);
			$body = mb_ereg_replace('{GeneratedPassword}', $passwd, $body);
            $this->insertInfoParamServer($body, $eid, $edata['OemId']);
            $this->insertInfoParamServer($template['FromTitle'], $eid, $edata['OemId']);
            $this->insertInfoParamServer($template['FromAddress'], $eid, $edata['OemId']);
            $this->insertInfoParamServer($template['Subject'], $eid, $edata['OemId']);


			// メール送信履歴登録
			$mailSendSeq = $this->history->saveNew(array(
			        'MailTemplateId' => 23,
			        'OrderSeq' => null,
			        'EnterpriseId' => $eid,
			        'ManCustId' => null,
			        'ToAddress' => $edata['MailAddress'],
			        'CcAddress' => null,
			        'BccAddress' => null,
			        'Subject' => $template['Subject'],
			        'Body' => $body,
			        'MailSendDate' => date('Y-m-d H:i:s'),
			        'ErrFlg' => 0,
			        'ErrReason' => null,
			        'RegistId' => $userId,
			        'UpdateId' => $userId,
			        'ValidFlg' => 1,
			));
			$this->sendDone(
				$template['FromTitle'],
				$template['FromAddress'],
				$edata['EnterpriseNameKj'],
				$edata['MailAddress'],
				$template['Subject'],
				$body
			);

		} catch(\Exception $e) {
		    if (isset($mailSendSeq)) {
		        // メール送信履歴を登録した場合、エラー理由を更新
		        $this->history->saveUpdate(array(
		                'ErrFlg' => 1,
		                'ErrReason' => $e->getMessage(),
		                'UpdateId' => $userId,
		        ), $mailSendSeq);
		    }
			throw new CoralMailException( 'cannot sent password information mail.', 0, $e );
		}
	}


	/**
	 * 事業者宛テストメール送信
	 *
	 * @param int $eid 事業者ID
	 * @param int $userId ユーザーID
	 */
	public function SendTestMailForEnterprise($eid, $userId)
	{
	    try {
			// 事業者情報の取得
			$mdle = new TableEnterprise($this->adapter);
			$edata = $mdle->findEnterprise($eid)->current();
			
			// メールテンプレートの取得
			$mdlmt = new TableMailTemplate($this->adapter);
			$template = $mdlmt->findMailTemplate(126, $edata['OemId'])->current();

			// メールの構築
			$body = mb_ereg_replace('{EnterpriseNameKj}', $edata['EnterpriseNameKj'], $template['Body']);
			$body = mb_ereg_replace('{LoginId}', $edata['LoginId'], $body);
            $this->insertInfoParamServer($body, $eid, $edata['OemId']);
            $this->insertInfoParamServer($template['FromTitle'], $eid, $edata['OemId']);
            $this->insertInfoParamServer($template['FromAddress'], $eid, $edata['OemId']);
            $this->insertInfoParamServer($template['Subject'], $eid, $edata['OemId']);

			// メール送信履歴登録
			$mailSendSeq = $this->history->saveNew(array(
			        'MailTemplateId' => 126,
			        'OrderSeq' => null,
			        'EnterpriseId' => $eid,
			        'ManCustId' => null,
			        'ToAddress' => $edata['MailAddress'],
			        'CcAddress' => null,
			        'BccAddress' => null,
			        'Subject' => $template['Subject'],
			        'Body' => $body,
			        'MailSendDate' => date('Y-m-d H:i:s'),
			        'ErrFlg' => 0,
			        'ErrReason' => null,
			        'RegistId' => $userId,
			        'UpdateId' => $userId,
			        'ValidFlg' => 1,
			));
			$this->sendDone(
			    $template['FromTitle'],
			    $template['FromAddress'],
			    $edata['EnterpriseNameKj'],
			    $edata['MailAddress'],
			    $template['Subject'],
			    $body
			);

		} catch(\Exception $e) {
		    if (isset($mailSendSeq)) {
		        // メール送信履歴を登録した場合、エラー理由を更新
		        $this->history->saveUpdate(array(
		                'ErrFlg' => 1,
		                'ErrReason' => $e->getMessage(),
		                'UpdateId' => $userId,
		        ), $mailSendSeq);
		    }
			throw new CoralMailException( 'cannot sent test mail to enterprise.', 0, $e );
		}
	}

	/**
	 * 注文登録（与信開始）メールを送信する
	 *
	 * @param int $enterpriseId 事業者ID
	 * @param array $orderSeqs OrderSeqを格納した配列
	 * @param int $userId ユーザーID
	 */
	public function SendOrderedMail($enterpriseId, $orderSeqs, $userId)
	{
	    try {
			$seqs = '0';
			foreach($orderSeqs as $orderSeq)
			{
				$seqs .= ',' . $orderSeq;
			}

			// 事業者情報の取得
			$mdle = new TableEnterprise($this->adapter);
			$edata = $mdle->findEnterprise($enterpriseId)->current();

			// 注文情報の取得
			$mdloc = new ViewOrderCustomer($this->adapter);
			$orders = $mdloc->findOrderCustomerByWhereStr(sprintf("OrderSeq IN (%s)", $seqs), "OrderSeq ASC");

			$mailOrderCount = 0;// 受付受注件数(メール送信サイト)
			// オーダーサマリーの作成
			$mdlsite = new TableSite($this->adapter);
			$orderSummary = "";
			foreach($orders as $order)
			{
			    // サイト.与信開始メール=0(送信しない)の注文は除く
			    $siteId = $order['SiteId'];
			    $site = $mdlsite->findSite($siteId)->current();
			    if ($site['CreaditStartMail'] == 0) {
			        continue;
			    }
				$orderIdInfo = isset($order['Ent_OrderId']) && strlen($order['Ent_OrderId']) ?
					sprintf('%s / %s', $order['OrderId'], $order['Ent_OrderId']) :
					$order['OrderId'];
				$orderSummary .= sprintf("[%s] %s様　(%s円)\r\n", $orderIdInfo, $order['NameKj'], $order['UseAmount']);

				$mailOrderCount++;
			}
			if ($orderSummary == "") {
			    // 全注文が送信しないだった場合、終了
			    return;
			}

			// メールテンプレートの取得
			$mdlmt = new TableMailTemplate($this->adapter);
			$template = $mdlmt->findMailTemplate(2, $edata['OemId'])->current();

			// メールの構築
			$body = mb_ereg_replace('{EnterpriseNameKj}', $edata['EnterpriseNameKj'], $template['Body']);
			$body = mb_ereg_replace('{OrderCount}', $mailOrderCount, $body);
			$body = mb_ereg_replace('{OrderSummary}', $orderSummary, $body);

            $this->insertInfoParamServer($body, $enterpriseId, $edata['OemId']);
            $this->insertInfoParamServer($template['FromTitle'], $enterpriseId, $edata['OemId']);
            $this->insertInfoParamServer($template['FromAddress'], $enterpriseId, $edata['OemId']);
            $this->insertInfoParamServer($template['Subject'], $enterpriseId, $edata['OemId']);

			// メール送信履歴登録
			$mailSendSeq = $this->history->saveNew(array(
			        'MailTemplateId' => 2,
			        'OrderSeq' => null,
			        'EnterpriseId' => $enterpriseId,
			        'ManCustId' => null,
			        'ToAddress' => $edata['MailAddress'],
			        'CcAddress' => null,
			        'BccAddress' => null,
			        'Subject' => mb_ereg_replace('{OrderCount}', $mailOrderCount, $template['Subject']),
			        'Body' => $body,
			        'MailSendDate' => date('Y-m-d H:i:s'),
			        'ErrFlg' => 0,
			        'ErrReason' => null,
			        'RegistId' => $userId,
			        'UpdateId' => $userId,
			        'ValidFlg' => 1,
			));
			$this->sendDone(
				$template['FromTitle'],
				$template['FromAddress'],
				$edata['EnterpriseNameKj'],
				$edata['MailAddress'],
				mb_ereg_replace('{OrderCount}', $mailOrderCount, $template['Subject']),
				$body
			);

		} catch(\Exception $e) {
		    if (isset($mailSendSeq)) {
		        // メール送信履歴を登録した場合、エラー理由を更新
		        $this->history->saveUpdate(array(
		                'ErrFlg' => 1,
		                'ErrReason' => $e->getMessage(),
		                'UpdateId' => $userId,
		        ), $mailSendSeq);
		    }
			throw new CoralMailException( 'cannot sent order accepted mail.', 0, $e );
		}
	}

	/**
	 * 与信完了メールを送信する
	 *
	 * @param string $decSeqId 送信対象となる与信確定識別ID
	 * @param int $userId ユーザーID
	 */
	public function SendCreditFinishMail($decSeqId, $userId)
	{
	    try {
			$query = "
				SELECT
				    O.EnterpriseId,
				    O.SiteId,
				    COUNT(*) AS CNT
				FROM
				    T_Order O
			        INNER JOIN T_Site S ON S.SiteId = O.SiteId
				WHERE
				    O.Dmi_DecSeqId = '%s'
			    AND S.CreaditCompMail = 1
				GROUP BY
				    O.EnterpriseId,
				    O.SiteId
				ORDER BY
				    O.EnterpriseId,
				    O.SiteId
	 		";

			$targetDatas = $this->adapter->query(sprintf($query, $decSeqId))->execute(null);

			foreach($targetDatas as $tData)
			{
				$this->SendCreditFinishEachEnt($tData['EnterpriseId'], $tData['SiteId'], $decSeqId, $userId);
			}
		} catch(\Exception $e) {
			throw new CoralMailException( 'cannot sent examination completed mail.', 0, $e );
		}
	}

	/**
	 * 事業者／サイト別に与信完了メールを送信する。
	 *
	 * @param int $enterpriseId 事業者ID
	 * @param int $siteId サイトID
	 * @param string $decSeqId 送信対象となる与信確定識別ID
	 * @param int $userId ユーザーID
	 */
	public function SendCreditFinishEachEnt($enterpriseId, $siteId, $decSeqId, $userId)
	{
	    try {
			// 事業者情報の取得
			$mdle = new TableEnterprise($this->adapter);
			$edata = $mdle->findEnterprise($enterpriseId)->current();

			// サイト情報の取得
			$mdls = new TableSite($this->adapter);
			$siteData = $mdls->findSite($siteId)->current();

			if ($siteData['CreaditCompMail'] == 0) {
			    return;
			}

			// 無保証変更可能期間の取得
			$sql = "SELECT MuhoshoChangeDays FROM T_Site WHERE SiteId = :SiteId ";
			$days = $this->adapter->query($sql)->execute(array(':SiteId' => $siteId))->current()['MuhoshoChangeDays'];

			// 注文情報の取得
			$mdloc = new ViewOrderCustomer($this->adapter);
			$orders = $mdloc->findOrderCustomerByWhereStr(sprintf("EnterpriseId = %d AND SiteId = %d AND Dmi_DecSeqId = '%s'", $enterpriseId, $siteId, $decSeqId), "OrderSeq ASC");

			$orderSummaryHeader = '登録日     注文ID     注文者名               購入金額 都道府県 任意注文番号';

			if ($siteData['ShowNgReason'] == 1) {
				$orderSummaryHeaderNG = $orderSummaryHeader . ' NG理由';
			} else {
				$orderSummaryHeaderNG = $orderSummaryHeader;
			}

			$sub_orders = array(
				'OK' => array(),
				'NG' => array()
			);
			foreach($orders as $order) {
				// 注文登録日
				$registDate = date('Y/m/d', strtotime($order['RegistDate']));

				// 与信結果
				switch($order['Dmi_Status'])
				{
					case -1:
						$dmiStatus = 'NG';
						break;
					case 1:
						$dmiStatus = 'OK';
						break;
					default:
						$dmiStatus = 'NG';		// 社内与信NGの場合
						break;
				}

				// NG理由の取得
				if ($siteData['ShowNgReason'] == 1) {
					$sql = 'SELECT C.Note FROM AT_Order AOD LEFT OUTER JOIN M_Code C ON (C.CodeId = 191 AND C.KeyCode = AOD.AutoJudgeNgReasonCode) WHERE AOD.OrderSeq = :OrderSeq ';
					$NgReason = $this->adapter->query($sql)->execute(array(':OrderSeq' => $order['OrderSeq']))->current()['Note'];
				} else {
					$NgReason = null;
				}

				$sub_orders[$dmiStatus][] = sprintf('%s  %s %s %s %s %s %s',
					$registDate,
				    $order['OrderId'],
					BaseGeneralUtils::rpad($order['NameKj'] . '様', '　', 11),
					BaseGeneralUtils::lpad($order['UseAmount'] . '円', ' ', 8),
					$order['PrefectureName'],
					BaseGeneralUtils::rpad($order['Ent_OrderId'], ' ', 12),
					$NgReason);
			}
			$ngCount = count($sub_orders['NG']);

			$sub_orders['OK'] = array_merge(
				array(sprintf('■ 与信 OK (%d 件)', count($sub_orders['OK'])), $orderSummaryHeader),
				$sub_orders['OK'] );
			$sub_orders['OK'][] = '';

			$sub_orders['NG'] = array_merge(
				array(sprintf('■ 与信 NG (%d 件)', $ngCount), $orderSummaryHeaderNG),
				$sub_orders['NG'] );
			$sub_orders['NG'][] = '';

			$orderSummary = join("\r\n", array_merge($sub_orders['OK'], $sub_orders['NG']));

			// メールテンプレートの取得
			$mdlmt = new TableMailTemplate($this->adapter);
			$template = $mdlmt->findMailTemplate(3, $edata['OemId'])->current();

			// メールの構築
			$subject = mb_ereg_replace('{CreditCount}', $orders->count(), $template['Subject']);
			$subject = mb_ereg_replace('{NgCount}', $ngCount, $subject);

			$body = mb_ereg_replace('{EnterpriseNameKj}', $edata['EnterpriseNameKj'], $template['Body']);
			$body = mb_ereg_replace('{CreditCount}', $orders->count(), $body);
			$body = mb_ereg_replace('{SiteUrl}', $siteData['Url'], $body);
			$body = mb_ereg_replace('{Orders}', $orderSummary, $body);
			$body = mb_ereg_replace('{OutOfAmendsDays}', $days, $body);

            $this->insertInfoParamServer($body, $enterpriseId, $edata['OemId']);
            $this->insertInfoParamServer($template['FromTitle'], $enterpriseId, $edata['OemId']);
            $this->insertInfoParamServer($template['FromAddress'], $enterpriseId, $edata['OemId']);
            $this->insertInfoParamServer($subject, $enterpriseId, $edata['OemId']);

			// TODO: kashira - 与信結果アタッチ

			// メール送信履歴登録
			$mailSendSeq = $this->history->saveNew(array(
			        'MailTemplateId' => 3,
			        'OrderSeq' => null,
			        'EnterpriseId' => $enterpriseId,
			        'ManCustId' => null,
			        'ToAddress' => $edata['MailAddress'],
			        'CcAddress' => null,
			        'BccAddress' => null,
			        'Subject' => $subject,
			        'Body' => $body,
			        'MailSendDate' => date('Y-m-d H:i:s'),
			        'ErrFlg' => 0,
			        'ErrReason' => null,
			        'RegistId' => $userId,
			        'UpdateId' => $userId,
			        'ValidFlg' => 1,
			));
			$this->sendDone(
				$template['FromTitle'],
				$template['FromAddress'],
				$edata['EnterpriseNameKj'],
				$edata['MailAddress'],
				$subject,
				$body
			);

		} catch(\Exception $e) {
		    if (isset($mailSendSeq)) {
		        // メール送信履歴を登録した場合、エラー理由を更新
		        $this->history->saveUpdate(array(
		                'ErrFlg' => 1,
		                'ErrReason' => $e->getMessage(),
		                'UpdateId' => $userId,
		        ), $mailSendSeq);
		    }
			throw new CoralMailException( 'cannot sent examination completed mail to each subscriber.', 0, $e );
		}
	}

	/**
	 * 事業者／サイト別に与信完了メールを送信する(社内与信画面より)。
	 *
	 * @param array $fact (送信)要素
	 * @param int $userId ユーザーID
	 */
	public function SendCreditFinishEachEnt2($fact, $userId)
	{
	    try {
	        $enterpriseId = $fact['EnterpriseId'];
	        $siteId = $fact['SiteId'];

	        // 事業者情報の取得
	        $mdle = new TableEnterprise($this->adapter);
	        $edata = $mdle->findEnterprise($enterpriseId)->current();

	        // サイト情報の取得
	        $mdls = new TableSite($this->adapter);
	        $siteData = $mdls->findSite($siteId)->current();

	        // 無保証変更可能期間の取得
	        $sql = "SELECT MuhoshoChangeDays FROM T_Site WHERE SiteId = :SiteId ";
	        $days = $this->adapter->query($sql)->execute(array(':SiteId' => $siteId))->current()['MuhoshoChangeDays'];

	        if ($siteData['CreaditCompMail'] == 0) {
	            return;
	        }

	        // 注文情報の取得(取得済み)
	        $orderSummaryHeader = '登録日     注文ID     注文者名               購入金額 都道府県 任意注文番号';

	        if ($siteData['ShowNgReason'] == 1) {
	            $orderSummaryHeaderNG = $orderSummaryHeader . ' NG理由';
	        } else {
	            $orderSummaryHeaderNG = $orderSummaryHeader;
	        }

	        $orders_ok = array_merge(
	        array(sprintf('■ 与信 OK (%d 件)', $fact['OKCount']), $orderSummaryHeader),
	        (!empty($fact['sub_orders_ok'])) ? $fact['sub_orders_ok'] : array() );
	        $orders_ok[] = '';
	        $orders_ng = array_merge(
	        array(sprintf('■ 与信 NG (%d 件)', $fact['NGCount']), $orderSummaryHeaderNG),
	        (!empty($fact['sub_orders_ng'])) ? $fact['sub_orders_ng'] : array() );
	        $orders_ng[] = '';
	        $orderSummary = join("\r\n", array_merge($orders_ok, $orders_ng));

	        // メールテンプレートの取得
	        $mdlmt = new TableMailTemplate($this->adapter);
	        $template = $mdlmt->findMailTemplate(3, $edata['OemId'])->current();

	        // メールの構築
	        $subject = mb_ereg_replace('{CreditCount}', ($fact['OKCount'] + $fact['NGCount']), $template['Subject']);
	        $subject = mb_ereg_replace('{NgCount}', $fact['NGCount'], $subject);

	        $body = mb_ereg_replace('{EnterpriseNameKj}', $edata['EnterpriseNameKj'], $template['Body']);
	        $body = mb_ereg_replace('{CreditCount}', ($fact['OKCount'] + $fact['NGCount']), $body);
	        $body = mb_ereg_replace('{SiteUrl}', $siteData['Url'], $body);
	        $body = mb_ereg_replace('{Orders}', $orderSummary, $body);
	        $body = mb_ereg_replace('{OutOfAmendsDays}', $days, $body);

            $this->insertInfoParamServer($body, $enterpriseId, $edata['OemId']);
            $this->insertInfoParamServer($template['FromTitle'], $enterpriseId, $edata['OemId']);
            $this->insertInfoParamServer($template['FromAddress'], $enterpriseId, $edata['OemId']);
            $this->insertInfoParamServer($subject, $enterpriseId, $edata['OemId']);

	        // メール送信履歴登録
	        $mailSendSeq = $this->history->saveNew(array(
	                'MailTemplateId' => 3,
	                'OrderSeq' => null,
	                'EnterpriseId' => $enterpriseId,
	                'ManCustId' => null,
	                'ToAddress' => $edata['MailAddress'],
	                'CcAddress' => null,
	                'BccAddress' => null,
	                'Subject' => $subject,
	                'Body' => $body,
	                'MailSendDate' => date('Y-m-d H:i:s'),
	                'ErrFlg' => 0,
	                'ErrReason' => null,
	                'RegistId' => $userId,
	                'UpdateId' => $userId,
	                'ValidFlg' => 1,
	        ));
	        $this->sendDone(
	        $template['FromTitle'],
	        $template['FromAddress'],
	        $edata['EnterpriseNameKj'],
	        $edata['MailAddress'],
	        $subject,
	        $body
	        );

	    } catch(\Exception $e) {
	        if (isset($mailSendSeq)) {
	            // メール送信履歴を登録した場合、エラー理由を更新
	            $this->history->saveUpdate(array(
	                    'ErrFlg' => 1,
	                    'ErrReason' => $e->getMessage(),
	                    'UpdateId' => $userId,
	            ), $mailSendSeq);
	        }
	        throw new CoralMailException( 'cannot sent examination completed mail to each subscriber.', 0, $e );
	    }
	}

	/**
	 * 請求書発行メールを送信する。
	 *
	 * @param int $oseq
	 * @param int $userId ユーザーID
	 */
	public function SendIssueBillMail($oseq, $userId)
	{
	    try {
	        // 顧客情報の取得
	        $mdloc = new ViewOrderCustomer($this->adapter);
	        $oc = $mdloc->find($oseq)->current();
	        
	        // サイト.請求書発行メール=1(送信する) or 2(初回のみ) 以外の注文は送信しない
	        $mdlsite = new TableSite($this->adapter);
	        $siteId = $oc['SiteId'];
	        $site = $mdlsite->findSite($siteId)->current();
	        if (!($site['ClaimMail'] == 1 || $site['ClaimMail'] == 2)) {
	            return;
	        }
	        
	        // メール送信履歴にメールテンプレート4,5,17,18,104,105,108,109のデータが存在する場合、送信済みとしてスキップする
	        if ($this->history->findSendMail($oseq, array(4,5,17,18,104,105,108,109))) {
	            return;
	        }
	        
	        // メールテンプレートの取得
	        // → 先行してテーブルモデルを初期化するよう変更（2013.2.13 eda）
	        $mdlmt = new TableMailTemplate($this->adapter);
	        
	        if (CoralValidate::isNotEmpty($oc['MailAddress']))
	        {
	            // 請求情報の取得（2010.9.24 eda）
	            // → メールテンプレート選択向けに、他のデータより先行して取得するよう変更（2013.2.13 eda）
	            $mdlhis = new TableClaimHistory($this->adapter);
	            $his = $mdlhis->getMailTagetByOrderSeq($oc['OrderSeq'])->current();
	            if (!$his)
	            {
	                throw new \Exception(sprintf('cannot get claim-history. OrderSeq = %s', $oc['OrderSeq']));
	            }
	            
	            // 事業者情報の取得
	            $mdle = new TableEnterprise($this->adapter);
	            $edata = $mdle->findEnterprise($oc['EnterpriseId'])->current();
	            
	            // 注文会計情報の取得
	            $mdlao = new ATableOrder($this->adapter);
	            $aodata = $mdlao->find($oc['OrderSeq'])->current();
	            
	            // 注文情報の取得
	            $mdlo = new TableOrder($this->adapter);
	            $odata = $mdlo->find($oseq)->current();
	            
	            //届いてから払いの利用期間を取得
	            $now = date('Y-m-d H:i:s');
	            $sqlForLimitDate = <<<EOQ
SELECT  MAX(NumUseDay) as MaxNumUseDay, COUNT(*) as cnt
FROM    T_SiteSbpsPayment tsb
WHERE   tsb.SiteId = :SiteId
AND     tsb.UseStartDate <= :Now
AND     tsb.ValidFlg = 1
EOQ;
	            $creditSettlementDays = $this->adapter->query($sqlForLimitDate)->execute(array(':SiteId' => $siteId, ':Now' => $now))->current();
	            
	            //使用すべきテンプレートの判定処理を行う、（変数）テンプレートIDを設定する。
	            if (
	                   $site['PaymentAfterArrivalFlg'] == 1 
	                   && preg_match ("/^[0]{1}/", $aodata['CreditTransferRequestFlg'])
	                   && $creditSettlementDays['cnt'] > 0 
	                   && $odata['Deli_ConfirmArrivalFlg'] != 1
	                )
	            {
	                
	                if ($odata ['ClaimSendingClass'] == 12 || $odata ['ClaimSendingClass'] == 21) {
	                    //別送の場合
	                    if ($this->isCelAddress ( $oc ['MailAddress'] )) {
	                        // メールアドレスが携帯アドレスの場合
	                        $tmpNumber = 105;
	                    } else {
	                        // メールアドレスが携帯アドレス以外の場合
	                        $tmpNumber = 104;
	                    }
	                }else{
	                    //別送以外の場合(同梱)
	                    if ($this->isCelAddress ( $oc ['MailAddress'] )) {
	                        // メールアドレスが携帯アドレスの場合
	                        $tmpNumber = 109;
	                    } else {
	                        // メールアドレスが携帯アドレス以外の場合
	                        $tmpNumber = 108;
	                    }
	                }
	            }
	            // 使用すべきテンプレートの判定
	            else if ($his['EnterpriseBillingCode'] != null)
	            {
	                // 同梱ツールからの出力の場合
	                if ($this->isCelAddress($oc['MailAddress']))
	                {
	                    // 先行してテンプレート取得
	                    $tmpNumber = 18;
	                    $template = $mdlmt->findMailTemplate(18, $edata['OemId'])->current();
	                    // テンプレート未登録の場合は従来の携帯向けテンプレートを使用
	                    if (!$template) $tmpNumber = 5;
	                }
	                else
	                {
	                    $tmpNumber = 17;
	                    $template = $mdlmt->findMailTemplate(17, $edata['OemId'])->current();
	                    if (!$template) $tmpNumber = 4;
	                }
	            }
	            else
	            {
	                // 印刷ツールからの出力の場合
	                if ($this->isCelAddress($oc['MailAddress']))
	                {
	                    $tmpNumber = 5;
	                }
	                else
	                {
	                    $tmpNumber = 4;
	                }
	            }
	            
	            // 注文商品情報の取得
	            $mdloi = new TableOrderItems($this->adapter);
	            $items = $mdloi->findByP_OrderSeq($oseq);
	            $oneitem = $mdloi->getOneItemName($oseq);
	            
	            if (! isset($template))
	            {
	                // テンプレート未取得の場合（＝従来のテンプレート使用の場合）はここで取得
	                $template = $mdlmt->findMailTemplate($tmpNumber, $edata['OemId'])->current();
	            }
	            
	            // メールの構築
	            $subject = mb_ereg_replace('{OrderId}', $oc['OrderId'], $template['Subject']);
	            
	            $body = mb_ereg_replace('{EnterpriseNameKj}', $edata['EnterpriseNameKj'], $template['Body']);
	            $body = mb_ereg_replace('{OrderId}', $oc['OrderId'], $body);
	            $body = mb_ereg_replace('{SiteNameKj}', $oc['SiteNameKj'], $body);
	            $body = mb_ereg_replace('{Phone}', $edata['ContactPhoneNumber'], $body);
	            $body = mb_ereg_replace('{CustomerNameKj}', $oc['NameKj'], $body);
	            $body = mb_ereg_replace('{OrderDate}', date('n月j日', strtotime($oc['ReceiptOrderDate'])), $body);// 注文日
	            $useAmountTotal = $this->adapter->query(" SELECT UseAmountTotal FROM T_ClaimControl WHERE OrderSeq = :OrderSeq ")->execute(array(':OrderSeq' => $oseq))->current()['UseAmountTotal'];
	            $body = mb_ereg_replace('{UseAmount}', $useAmountTotal . '円', $body);
	            $body = mb_ereg_replace('{LimitDate}', f_df($his['LimitDate'], 'n月j日'), $body);
	            $body = mb_ereg_replace('{OneOrderItem}', $oneitem, $body);
	            //insert {ServiceName} and {ServiceMail}
	            $this->insertInfoParamServer($body, $oc['EnterpriseId'], $oc['OemId']);
	            $this->insertInfoParamServer($template['FromTitle'], $oc['EnterpriseId'], $oc['OemId']);
	            $this->insertInfoParamServer($template['FromAddress'], $oc['EnterpriseId'], $oc['OemId']);
	            $this->insertInfoParamServer($subject, $oc['EnterpriseId'], $oc['OemId']);
	            //insert {OrderId}
	            $orderId = $oc['OrderId'];
	            $this->insertOrderId($body, $orderId);
	            
	            // 注文商品リストの作成
	            $orders = "";
	            $deliveryFee = 0;
	            $settlementFee = 0;
	            $tax = 0;
	            foreach ($items as $item)
	            {
	                switch($item['DataClass'])
	                {
	                    case 2:
	                        $deliveryFee += $item['SumMoney'];
	                        break;
	                    case 3:
	                        $settlementFee += $item['SumMoney'];
	                        break;
	                    case 4:
	                        $tax += $item['SumMoney'];
	                        break;
	                    default:
	                        $orders .= BaseGeneralUtils::rpad(sprintf("%s　（数量：% 2d）", $item['ItemNameKj'], $item['ItemNum']), '　', 21) . $item['SumMoney'] . "円\r\n";
	                        break;
	                }
	            }
	            
	            //本文作成
	            $mdlcd = new TableCode($this->adapter);
	            $mdlmo = new TableMypageOrder($this->adapter);
	            $body = mb_ereg_replace('{OrderItems}', $orders, $body);
	            $body = mb_ereg_replace('{SettlementFee}', $settlementFee, $body);
	            $body = mb_ereg_replace('{DeliveryFee}', $deliveryFee, $body);
	            $body = mb_ereg_replace ( '{Tax}', $tax, $body );
	            if ($tmpNumber == 104 || $tmpNumber == 105 || $tmpNumber == 108 || $tmpNumber == 109) {
	                //請求履歴の最も古いレコードを取得する。
	                $mdlch = new TableClaimHistory($this->adapter);
	                $claimHistoryData = $mdlch->getOldestClaimHistory($oc['OrderSeq']);
	                if (!empty($claimHistoryData)) {
	                    $minClaimDate = $claimHistoryData['ClaimDate'];
	                }
	                else {
	                    $minClaimDate = 0;
	                }
	                
	                //請求履歴.請求日 + 届いてから払いの利用期間
	                $creditLimitDate = date('Y/m/d', strtotime($minClaimDate. '+'. $creditSettlementDays['MaxNumUseDay']. ' days') );
	                $passWord = $mdlmo->findByOrderSeq($oseq)->current ()['Token']; // ﾄｰｸﾝ
	                $body = mb_ereg_replace ( '{PassWord}', $passWord, $body );
	                $body = mb_ereg_replace ( '{CreditLimitDate}', $creditLimitDate, $body );
	                //支払可能種類を取得
	                $sqlForPaymentMethod = <<<EOQ
SELECT  GROUP_CONCAT(DISTINCT ms.MailParameterNameKj ORDER BY ms.SortId ASC) AS MailParameterNameKj
FROM    M_SbpsPayment ms
INNER JOIN T_SiteSbpsPayment ts ON (ts.PaymentId = ms.SbpsPaymentId)
WHERE   ts.SiteId = :SiteId
AND     ts.UseStartDate <= :Now
AND     ts.ValidFlg = 1
EOQ;
	                $sp = $this->adapter->query($sqlForPaymentMethod)->execute(array(':SiteId' => $siteId, ':Now' => $now))->current();
	                if ($sp['MailParameterNameKj']) {
	                    $paymentMethod = $sp['MailParameterNameKj'];
	                } else {
	                    $paymentMethod = false;
	                }
	                $body = mb_ereg_replace('{PaymentMethod}', $paymentMethod, $body);
	            }
	            
	            // ｱｸｾｽ用URLの構築
	            $accessKey = $mdlmo->findByOrderSeq($oseq)->current()['AccessKey'];  // ｱｸｾｽKey
	            $orderpageUrl = $mdlcd->getMasterCaption(105, nvl($oc['OemId'], 0)); // OEM別の注文ﾏｲﾍﾟｰｼﾞURL
	            $orderpageUrl = rtrim($orderpageUrl, '/'); // 念のため、最後の"/"は除く
	            $logicSbps = new LogicSbps($this->adapter);
	            $flag = $logicSbps->checkHasPaymentAfterArrivalFlg($oc['EnterpriseId'], 'T_Site');
	            if ($flag) {
	                $spapp2 = $mdlcd->getMasterAssCodeTodo(105, nvl($oc['OemId'], 0));
	                if (is_null($spapp2)) {
                        $orderPageAccessUrl = $orderpageUrl . '/login/login/accessid/' . $accessKey;  // https://www.atobarai.jp/orderpage/login/login/accessid/ﾗﾝﾀﾞﾑ文字列
                    } else {
                        $orderPageAccessUrl = $orderpageUrl . '/login/login' . $mdlcd->getMasterAssCodeTodo(105, nvl($oc['OemId'], 0)) .'&accessid=' . $accessKey;  // https://www.atobarai.jp/orderpage/login/login?spapp2&accessid=ﾗﾝﾀﾞﾑ文字列
                    }
	                $body = mb_ereg_replace('{OrderPageAccessUrl}', $orderPageAccessUrl, $body);
	            } else {
	                $orderPageAccessUrl = $orderpageUrl . '/login/login/accessid/' . $accessKey;  // https://www.atobarai.jp/orderpage/login/login/accessid/ﾗﾝﾀﾞﾑ文字列
	                $body = mb_ereg_replace('{OrderPageAccessUrl}', $orderPageAccessUrl, $body);
	            }
	            $body = mb_ereg_replace('{OrderPageUrl}', $orderpageUrl, $body);
	            
	            // メール送信履歴登録
	            $mailSendSeq = $this->history->saveNew(array(
	                'MailTemplateId' => $tmpNumber,
	                'OrderSeq' => $oseq,
	                'EnterpriseId' => null,
	                'ManCustId' => null,
	                'ToAddress' => $oc['MailAddress'],
	                'CcAddress' => null,
	                'BccAddress' => null,
	                'Subject' => $subject,
	                'Body' => $body,
	                'MailSendDate' => date('Y-m-d H:i:s'),
	                'ErrFlg' => 0,
	                'ErrReason' => null,
	                'RegistId' => $userId,
	                'UpdateId' => $userId,
	                'ValidFlg' => 1,
	            ));
	            
	            $this->sendDone(
	                $template['FromTitle'],
	                $template['FromAddress'],
	                $oc['NameKj'] . '　様',
	                $oc['MailAddress'],
	                $subject,
	                $body
	                );
	            
	        }
	    } catch(\Exception $e) {
	        if (isset($mailSendSeq)) {
	            // メール送信履歴を登録した場合、エラー理由を更新
	            $this->history->saveUpdate(array(
	                'ErrFlg' => 1,
	                'ErrReason' => $e->getMessage(),
	                'UpdateId' => $userId,
	            ), $mailSendSeq);
	        }
	        throw new CoralMailException(sprintf('cannot sent mail about bill issued. %s', $e->getMessage()), 0, $e );
	    }
	}

	/**
	 * 請求書発行メールを送信する(再請求版)。
	 *
	 * @param int $oseq
	 * @param int $userId ユーザーID
	 */
	public function SendIssueBillMail2($oseq, $userId)
	{
        try {
            // 注文状況取得
            $sql  = " SELECT o.ReceiptOrderDate ";
            $sql .= " ,      o.OrderId ";
            $sql .= " ,      c.NameKj ";
            $sql .= " ,      e.ContactPhoneNumber ";
            $sql .= " ,      s.SiteNameKj ";
            $sql .= " ,      (SELECT ItemNameKj FROM T_OrderItems WHERE OrderSeq = o.OrderSeq AND DataClass = 1 AND ValidFlg = 1 ORDER BY OrderItemId LIMIT 1) AS ItemNameKj ";
            $sql .= " ,      cc.F_ClaimDate ";
            $sql .= " ,      (SELECT ClaimDate FROM T_ClaimHistory WHERE OrderSeq = o.OrderSeq AND Seq < ch.Seq AND ValidFlg = 1 ORDER BY Seq DESC LIMIT 1) AS ClaimDate ";
            $sql .= " ,      cc.ClaimedBalance ";
            $sql .= " ,      (cc.ClaimFee + cc.AdditionalClaimFee) AS ReClaimFee ";
            $sql .= " ,      cc.DamageInterestAmount ";
            $sql .= " ,      ca.Bk_BankName ";
            $sql .= " ,      ca.Bk_BranchName ";
            $sql .= " ,      ca.Bk_AccountNumber ";
            $sql .= " ,      ca.Bk_AccountHolderKn ";
            $sql .= " ,      ca.ConfirmNumber ";
            $sql .= " ,      ca.CustomerNumber ";
            //(以下、ﾒｰﾙ本文ｱｻｲﾝ対象外の要素)
            $sql .= " ,      s.ClaimMail ";
            $sql .= " ,      c.MailAddress ";
            $sql .= " ,      ch.ClaimPattern ";
            $sql .= " ,      e.OemId ";
            $sql .= " ,      e.EnterpriseId ";
            $sql .= " ,      cc.CreditTransferFlg ";
            $sql .= " ,      cc.F_CreditTransferDate ";
            $sql .= " FROM   T_Order o ";
            $sql .= "        INNER JOIN T_Customer c ON (c.OrderSeq = o.OrderSeq) ";
            $sql .= "        INNER JOIN T_Enterprise e ON (e.EnterpriseId = o.EnterpriseId) ";
            $sql .= "        INNER JOIN T_Site s ON (s.SiteId = o.SiteId) ";
            $sql .= "        INNER JOIN T_ClaimControl cc ON (cc.OrderSeq = o.OrderSeq) ";
            $sql .= "        INNER JOIN T_ClaimHistory ch ON (o.OrderSeq = ch.OrderSeq AND ch.PrintedFlg = 1 AND ch.MailFlg = 0 AND ch.ValidFlg = 1) ";
            $sql .= "        INNER JOIN T_OemClaimAccountInfo ca ON(ch.Seq = ca.ClaimHistorySeq) ";
            $sql .= " WHERE  1 = 1 ";
            $sql .= " AND    o.OrderSeq = :OrderSeq ";

            $row = $this->adapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current();

            // サイト.請求書発行メール=1(送信する) or 3(督促のみ) 以外の注文は送信しない
            if (!($row['ClaimMail'] == 1 || $row['ClaimMail'] == 3)) {
                return;
            }

            // メールアドレスが有効でない場合は送信しない
            if (!CoralValidate::isNotEmpty($row['MailAddress'])) {
                return;
            }

            $mdlmt = new TableMailTemplate($this->adapter);

            if (!$this->isCelAddress($row['MailAddress'])) {
                if      ($row['ClaimPattern'] == 2) { $tmpNumber = 39; } // 再請求１
                else if ($row['ClaimPattern'] == 4) { $tmpNumber = 41; } // 再請求３
                else if ($row['ClaimPattern'] == 6) { $tmpNumber = 43; } // 再請求４
                else if ($row['ClaimPattern'] == 7) { $tmpNumber = 45; } // 再請求５
                else if ($row['ClaimPattern'] == 8) { $tmpNumber = 47; } // 再請求６
                else if ($row['ClaimPattern'] == 9) { $tmpNumber = 49; } // 再請求７
            }
            else {
                if      ($row['ClaimPattern'] == 2) { $tmpNumber = 40; } // 再請求１
                else if ($row['ClaimPattern'] == 4) { $tmpNumber = 42; } // 再請求３
                else if ($row['ClaimPattern'] == 6) { $tmpNumber = 44; } // 再請求４
                else if ($row['ClaimPattern'] == 7) { $tmpNumber = 46; } // 再請求５
                else if ($row['ClaimPattern'] == 8) { $tmpNumber = 48; } // 再請求６
                else if ($row['ClaimPattern'] == 9) { $tmpNumber = 50; } // 再請求７
            }

            // 口振の場合のテンプレート設定
            if (($row['CreditTransferFlg'] != 0) && ($row['ClaimPattern'] == 2)) {
                if (!$this->isCelAddress($row['MailAddress'])) {
                    $tmpNumber = 119;
                } else {
                    $tmpNumber = 120;
                }
            }

            // メールテンプレートの取得(取得できない時は送信しない)
            $template = $mdlmt->findMailTemplate($tmpNumber, $row['OemId'])->current();
            if (!$template) {
                return;
            }

            //ペイジー収納機関番号取得
            $mdlCode = new TableCode($this->adapter);
            $bk_number = $mdlCode->find(LogicPayeasy::PAYEASY_CODEID, LogicPayeasy::BK_NUMBER_KEYCODE)->current()['Note'];

            // メールの構築
            $subject = $template['Subject'];
            $subject = mb_ereg_replace('{OrderDate}', date('n月j日', strtotime($row['ReceiptOrderDate'])), $subject);
            $subject = mb_ereg_replace('{SiteNameKj}', $row['SiteNameKj'], $subject);
            $subject = mb_ereg_replace('{OrderId}', $row['OrderId'], $subject);

            $body = $template['Body'];
            $body = mb_ereg_replace('{OrderId}', $row['OrderId'], $body);
            $body = mb_ereg_replace('{OrderDate}', date('n月j日', strtotime($row['ReceiptOrderDate'])), $body);
            $body = mb_ereg_replace('{SiteNameKj}', $row['SiteNameKj'], $body);
            $body = mb_ereg_replace('{CustomerNameKj}', $row['NameKj'], $body);
            $body = mb_ereg_replace('{IssueDate}', date('n月j日', strtotime($row['F_ClaimDate'])), $body);
            $body = mb_ereg_replace('{ClaimDate}', date('n月j日', strtotime($row['ClaimDate'])), $body);
            $body = mb_ereg_replace('{OneOrderItem}', $row['ItemNameKj'], $body);
            $body = mb_ereg_replace('{TotalAmount}', $row['ClaimedBalance'] . '円', $body);
            $body = mb_ereg_replace('{Bk_BankName}', $row['Bk_BankName'], $body);
            $body = mb_ereg_replace('{Bk_BranchName}', $row['Bk_BranchName'], $body);
            $body = mb_ereg_replace('{Bk_AccountNumber}', $row['Bk_AccountNumber'], $body);
            $body = mb_ereg_replace('{Bk_AccountHolderKn}', $row['Bk_AccountHolderKn'], $body);
            $body = mb_ereg_replace('{Phone}', $row['ContactPhoneNumber'], $body);
            $body = mb_ereg_replace('{ReClaimFee}', $row['ReClaimFee'] . '円', $body);
            $body = mb_ereg_replace('{DamageInterest}', $row['DamageInterestAmount'] . '円', $body);
            $body = mb_ereg_replace('{CustomerNumber}', $row['CustomerNumber'] , $body);
            $body = mb_ereg_replace('{ConfirmNumber}', $row['ConfirmNumber'] , $body);
            $body = mb_ereg_replace('{Bk_Number}', $bk_number , $body);
            $body = mb_ereg_replace('{CreditTransferDate}', date('Y年n月j日', strtotime($row['F_CreditTransferDate'])) , $body);

            $this->insertInfoParamServer($body, $row['EnterpriseId'], $row['OemId']);
            $this->insertInfoParamServer($template['FromTitle'], $row['EnterpriseId'], $row['OemId']);
            $this->insertInfoParamServer($template['FromAddress'], $row['EnterpriseId'], $row['OemId']);
            $this->insertInfoParamServer($subject, $row['EnterpriseId'], $row['OemId']);

            // メール送信履歴登録
            $mailSendSeq = $this->history->saveNew(array(
                    'MailTemplateId' => $tmpNumber,
                    'OrderSeq' => $oseq,
                    'EnterpriseId' => null,
                    'ManCustId' => null,
                    'ToAddress' => $row['MailAddress'],
                    'CcAddress' => null,
                    'BccAddress' => null,
                    'Subject' => $subject,
                    'Body' => $body,
                    'MailSendDate' => date('Y-m-d H:i:s'),
                    'ErrFlg' => 0,
                    'ErrReason' => null,
                    'RegistId' => $userId,
                    'UpdateId' => $userId,
                    'ValidFlg' => 1,
            ));
            $this->sendDone(
                    $template['FromTitle'],
                    $template['FromAddress'],
                    $row['NameKj'] . '　様',
                    $row['MailAddress'],
                    $subject,
                    $body
            );

        } catch(\Exception $e) {
            if (isset($mailSendSeq)) {
                // メール送信履歴を登録した場合、エラー理由を更新
                $this->history->saveUpdate(array(
                        'ErrFlg' => 1,
                        'ErrReason' => $e->getMessage(),
                        'UpdateId' => $userId,
                ), $mailSendSeq);
            }
            throw new CoralMailException(sprintf('cannot sent mail about bill issued. %s', $e->getMessage()), 0, $e );
        }
	}

	/**
	 * 口振請求書発行案内メール送信処理。
	 *
	 * @param int $oseq
	 * @param int $userId ユーザーID
	 */
	public function SendCreditTransferInfoMail($oseq, $userId)
	{
	    try {
	        // 顧客情報の取得
	        $mdloc = new ViewOrderCustomer($this->adapter);
	        $oc = $mdloc->find($oseq)->current();

	        // 顧客情報．メールアドレスが非空の場合
	        if (CoralValidate::isNotEmpty($oc['MailAddress'])) {

	            // 使用するテンプレートの判定
	            $tmpNumber = $this->isCelAddress($oc['MailAddress']) ? 101 : 100;

	            // 指定の注文Seqの口振請求書発行案内メール送信対象データを取得する
	            $mdlhis = new TableClaimHistory($this->adapter);
	            $his = $mdlhis->findDataInfoMail($oc['OrderSeq'])->current();

	            if (!$his)
	            {
	                throw new \Exception(sprintf('cannot get claim-history. OrderSeq = %s', $oc['OrderSeq']));
	            }

	            // 注文商品情報の取得
	            $mdloi = new TableOrderItems($this->adapter);
	            $items = $mdloi->findByP_OrderSeq($oseq);
	            $oneitem = $mdloi->getOneItemName($oseq);

	            // 事業者情報取得処理を行う
	            $mdle = new TableEnterprise($this->adapter);
	            $edata = $mdle->findEnterprise($oc['EnterpriseId'])->current();

	            // 使用すべきテンプレートの判定処理を行う
	            $mdlmt = new TableMailTemplate($this->adapter);
	            $template = $mdlmt->findMailTemplate($tmpNumber, $edata['OemId'])->current();
	            if(!$template) throw new \Exception('メールテンプレートが存在しません');

	            // メールの構築
	            $subject = mb_ereg_replace('{OrderId}', $oc['OrderId'], $template['Subject']);
	            $body = mb_ereg_replace('{EnterpriseNameKj}', $edata['EnterpriseNameKj'], $template['Body']);          // 事業者名
	            $body = mb_ereg_replace('{OrderId}', $oc['OrderId'], $body);   // 注文ID
	            $body = mb_ereg_replace('{SiteNameKj}', $oc['SiteNameKj'], $body);
	            $body = mb_ereg_replace('{Phone}', $edata['ContactPhoneNumber'], $body);
	            $body = mb_ereg_replace('{CustomerNameKj}', $oc['NameKj'], $body);
	            $body = mb_ereg_replace('{OrderDate}', date('n月j日', strtotime($oc['ReceiptOrderDate'])), $body);   // 注文日
	            $body = mb_ereg_replace('{UseAmount}', $oc['UseAmount'] . '円', $body);
	            $body = mb_ereg_replace('{LimitDate}', f_df($his['LimitDate'], 'n月j日'), $body);
	            $body = mb_ereg_replace('{OneOrderItem}', $oneitem, $body);

                $this->insertInfoParamServer($body, $oc['EnterpriseId'], $oc['OemId']);
                $this->insertInfoParamServer($template['FromTitle'], $oc['EnterpriseId'], $oc['OemId']);
                $this->insertInfoParamServer($template['FromAddress'], $oc['EnterpriseId'], $oc['OemId']);
                $this->insertInfoParamServer($subject, $oc['EnterpriseId'], $oc['OemId']);
                $orderId = $oc['OrderId'];
                $this->insertOrderId($body, $orderId);

	            // 注文商品リストの作成
	            $orders = "";
	            $deliveryFee = 0;
	            $settlementFee = 0;
	            $tax = 0;
	            foreach ($items as $item)
	            {
	                switch($item['DataClass'])
	                {
	                    case 2:
	                        $deliveryFee += $item['SumMoney'];
	                        break;
	                    case 3:
	                        $settlementFee += $item['SumMoney'];
	                        break;
	                    case 4:
	                        $tax += $item['SumMoney'];
	                        break;
	                    default:
	                        $orders .= BaseGeneralUtils::rpad(sprintf("%s　（数量：% 2d）", $item['ItemNameKj'], $item['ItemNum']), '　', 21) . $item['SumMoney'] . "円\r\n";
	                        break;
	                }
	            }

	            $body = mb_ereg_replace('{OrderItems}', $orders, $body);
	            $body = mb_ereg_replace('{SettlementFee}', $settlementFee, $body);
	            $body = mb_ereg_replace('{DeliveryFee}', $deliveryFee, $body);
	            $body = mb_ereg_replace('{Tax}', $tax, $body);

	            // ｱｸｾｽ用URLの構築
	            $mdlmo = new TableMypageOrder($this->adapter);
	            $mdlcd = new TableCode($this->adapter);

	            // コードマスター．キャプション + "/login/login/accessid/" + 注文マイページ．アクセス用URLキー
                $accessKey = $mdlmo->findByOrderSeq($oseq)->current()['AccessKey'];  // ｱｸｾｽKey
                $orderpageUrl = $mdlcd->getMasterCaption(105, nvl($oc['OemId'], 0)); // OEM別の注文ﾏｲﾍﾟｰｼﾞURL
                $orderpageUrl = rtrim($orderpageUrl, '/'); // 念のため、最後の"/"は除く
                $logicSbps = new LogicSbps($this->adapter);
                $flag = $logicSbps->checkHasPaymentAfterArrivalFlg($oc['EnterpriseId'], 'T_Site');
                if ($flag) {
                    $spapp2 = $mdlcd->getMasterAssCodeTodo(105, nvl($oc['OemId'], 0));
                    if (is_null($spapp2)) {
                        $orderPageAccessUrl = $orderpageUrl . '/login/login/accessid/' . $accessKey;  // https://www.atobarai.jp/orderpage/login/login/accessid/ﾗﾝﾀﾞﾑ文字列
                    } else {
                        $orderPageAccessUrl = $orderpageUrl . '/login/login' . $mdlcd->getMasterAssCodeTodo(105, nvl($oc['OemId'], 0)) .'&accessid=' . $accessKey;  // https://www.atobarai.jp/orderpage/login/login?spapp2&accessid=ﾗﾝﾀﾞﾑ文字列
                    }
                    $body = mb_ereg_replace('{OrderPageAccessUrl}', $orderPageAccessUrl, $body);
                } else {
                    $orderPageAccessUrl = $orderpageUrl . '/login/login/accessid/' . $accessKey;
                    $body = mb_ereg_replace('{OrderPageAccessUrl}', $orderPageAccessUrl, $body);
                }
	            // メール送信履歴登録
	            $mailSendSeq = $this->history->saveNew(array(
	                    'MailTemplateId' => $tmpNumber,
	                    'OrderSeq' => $oseq,
	                    'EnterpriseId' => null,
	                    'ManCustId' => null,
	                    'ToAddress' => $oc['MailAddress'],
	                    'CcAddress' => null,
	                    'BccAddress' => null,
	                    'Subject' => $subject,
	                    'Body' => $body,
	                    'MailSendDate' => date('Y-m-d H:i:s'),
	                    'ErrFlg' => 0,
	                    'ErrReason' => null,
	                    'RegistId' => $userId,
	                    'UpdateId' => $userId,
	                    'ValidFlg' => 1,
	            ));
	            $this->sendDone(
	            $template['FromTitle'],
	            $template['FromAddress'],
	            $oc['NameKj'] . '　様',
	            $oc['MailAddress'],
	            $subject,
	            $body
	            );
	        }
	    } catch (\Exception $e) {
	        if (isset($mailSendSeq)) {
	            // メール送信履歴を登録した場合、エラー理由を更新
	            $this->history->saveUpdate(array(
	                    'ErrFlg' => 1,
	                    'ErrReason' => $e->getMessage(),
	                    'UpdateId' => $userId,
	            ), $mailSendSeq);
	        }
	        throw new CoralMailException( 'cannot sent mail about bill issued. ' . $e->getMessage(), 0, $e );
	    }
	}

    /**
     * 請求書破棄メールを送信する。
     *
     * @param int $oseq
     * @param int $userId ユーザーID
     */
    public function SendDisposeBillMail($oseq, $userId)
    {
        try {
            // 顧客情報の取得
            $mdloc = new ViewOrderCustomer($this->adapter);
            $oc = $mdloc->find($oseq)->current();

            // サイト.請求書破棄メール=0(送信しない)の注文は送信しない
            $mdlsite = new TableSite($this->adapter);
            $siteId = $oc['SiteId'];
            $site = $mdlsite->findSite($siteId)->current();
            if ($site['ClaimDisposeMail'] == 0) {
                return;
            }

            // メールテンプレートの取得
            $mdlmt = new TableMailTemplate($this->adapter);

            if (CoralValidate::isNotEmpty($oc['MailAddress']))
            {
                if ($this->isCelAddress($oc['MailAddress']))
                {
                    // 携帯向けテンプレートを使用
                    $tmpNumber = 33;
                }
                else
                {
                    // PC向けテンプレートを使用
                    $tmpNumber = 32;
                }

                // テンプレート取得
                $template = $mdlmt->findMailTemplate($tmpNumber, $oc['OemId'])->current();

                // メールの構築
                $subject = $template['Subject'];
                $subject = mb_ereg_replace('{OrderId}', $oc['OrderId'], $subject);

                $body = $template['Body'];

                $sql = " SELECT c.NameKj, DATE_FORMAT(cncl.CancelDate, '%c月%e日') AS CancelDate FROM T_Customer c LEFT OUTER JOIN T_Cancel cncl ON (c.OrderSeq = cncl.OrderSeq) WHERE c.OrderSeq = :OrderSeq ";
                $row = $this->adapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current();

                $sql = " SELECT ContactPhoneNumber FROM T_Enterprise WHERE EnterpriseId = :EnterpriseId ";
                $row_ent = $this->adapter->query($sql)->execute(array(':EnterpriseId' => $oc['EnterpriseId']))->current();

                $body = mb_ereg_replace('{CustomerNameKj}', $row['NameKj'], $body);
                $body = mb_ereg_replace('{CancelDate}', $row['CancelDate'], $body);
                $body = mb_ereg_replace('{SiteNameKj}', $site['SiteNameKj'], $body);
                $body = mb_ereg_replace('{Phone}', $row_ent['ContactPhoneNumber'], $body);

                $this->insertInfoParamServer($body, $oc['EnterpriseId'], $oc['OemId']);
                $this->insertInfoParamServer($template['FromTitle'], $oc['EnterpriseId'], $oc['OemId']);
                $this->insertInfoParamServer($template['FromAddress'], $oc['EnterpriseId'], $oc['OemId']);
                $this->insertInfoParamServer($subject, $oc['EnterpriseId'], $oc['OemId']);

                $orderId = $oc['OrderId'];
                $this->insertOrderId($body, $orderId);

                // メール送信履歴登録
                $mailSendSeq = $this->history->saveNew(array(
                        'MailTemplateId' => $tmpNumber,
                        'OrderSeq' => $oseq,
                        'EnterpriseId' => null,
                        'ManCustId' => null,
                        'ToAddress' => $oc['MailAddress'],
                        'CcAddress' => null,
                        'BccAddress' => null,
                        'Subject' => $subject,
                        'Body' => $body,
                        'MailSendDate' => date('Y-m-d H:i:s'),
                        'ErrFlg' => 0,
                        'ErrReason' => null,
                        'RegistId' => $userId,
                        'UpdateId' => $userId,
                        'ValidFlg' => 1,
                ));
                $this->sendDone(
                $template['FromTitle'],
                $template['FromAddress'],
                $oc['NameKj'] . '　様',
                $oc['MailAddress'],
                $subject,
                $body
                );

            }
        } catch(\Exception $e) {
            if (isset($mailSendSeq)) {
                // メール送信履歴を登録した場合、エラー理由を更新
                $this->history->saveUpdate(array(
                        'ErrFlg' => 1,
                        'ErrReason' => $e->getMessage(),
                        'UpdateId' => $userId,
                ), $mailSendSeq);
            }
            throw new CoralMailException(sprintf('cannot sent mail about bill disposed. %s', $e->getMessage()), 0, $e );
        }
    }

	/**
	 * 入金確認メールを送信する。
	 *
	 * @param int $rcptseq 入金Seq
	 * @param int $userId ユーザーID
	 */
	public function SendRcptConfirmMail($rcptseq, $userId)
	{
	    try {
			// V_OrderCustomer に T_ReceiptControl を結合して顧客情報を取得する。
			$sql = <<<EOQ
SELECT  voc.*
    ,   rc.ReceiptProcessDate AS Rct_ReceiptProcessDate
    ,   rc.ReceiptAmount AS Rct_ReceiptAmount
FROM    V_OrderCustomer voc
        INNER JOIN T_ReceiptControl rc ON (rc.OrderSeq = voc.OrderSeq)
WHERE   rc.ReceiptSeq = :ReceiptSeq
;
EOQ;

            // 顧客情報の取得
            $oc = $this->adapter->query($sql)->execute(array(':ReceiptSeq' => $rcptseq))->current();

			// サイト.入金確認メール=0(送信しない)の注文は送信しない
			$mdlsite = new TableSite($this->adapter);
			$siteId = $oc['SiteId'];
			$site = $mdlsite->findSite($siteId)->current();
			if ($site['ReceiptMail'] == 0) {
			    return;
			}

			if (CoralValidate::isNotEmpty($oc['MailAddress']))
			{

				// 使用すべきテンプレートの判定
				if ($this->isCelAddress($oc['MailAddress']))
				{
					$tmpNumber = 7;
				}
				else
				{
					$tmpNumber = 6;
				}

				// 事業者情報の取得
				$mdle = new TableEnterprise($this->adapter);
				$edata = $mdle->findEnterprise($oc['EnterpriseId'])->current();

				// 注文商品情報の取得
				$mdloi = new TableOrderItems($this->adapter);
				$items = $mdloi->findByP_OrderSeq($oc['OrderSeq']);
				$oneitem = $mdloi->getOneItemName($oc['OrderSeq']);

				// メールテンプレートの取得
				$mdlmt = new TableMailTemplate($this->adapter);
				$template = $mdlmt->findMailTemplate($tmpNumber, $edata['OemId'])->current();

				// メールの構築
				$receiptProcessDate = date('n月j日', strtotime($oc['Rct_ReceiptProcessDate']));   // 入金確認日
				$orderDate          = date('n月j日', strtotime($oc['ReceiptOrderDate']));         // 注文日

				$body = mb_ereg_replace('{EnterpriseNameKj}', $edata['EnterpriseNameKj'], $template['Body']);
				$body = mb_ereg_replace('{SiteNameKj}', $oc['SiteNameKj'], $body);
				$body = mb_ereg_replace('{Phone}', $edata['ContactPhoneNumber'], $body);
				$body = mb_ereg_replace('{Address}', $edata['PrefectureName']. $edata['City'] . $edata['Town'] . $edata['Building'], $body);
				$body = mb_ereg_replace('{CustomerNameKj}', $oc['NameKj'], $body);
				$body = mb_ereg_replace('{ReceiptDate}', $receiptProcessDate, $body);
				$body = mb_ereg_replace('{OrderDate}', $orderDate, $body);
				$body = mb_ereg_replace('{UseAmount}', $oc['Rct_ReceiptAmount'] . '円', $body);
				$body = mb_ereg_replace('{SiteUrl}', $site['Url'], $body);
                $this->insertInfoParamServer($body, $oc['EnterpriseId'], $oc['OemId']);
                $this->insertInfoParamServer($template['FromTitle'], $oc['EnterpriseId'], $oc['OemId']);
                $this->insertInfoParamServer($template['FromAddress'], $oc['EnterpriseId'], $oc['OemId']);
                $this->insertInfoParamServer($template['Subject'], $oc['EnterpriseId'], $oc['OemId']);

                $orderId = $oc['OrderId'];
                $this->insertOrderId($body, $orderId);
                $this->insertOrderId($template['Subject'], $orderId);

				// 注文商品リストの作成
				$orders = "";
				$deliveryFee = 0;
				$settlementFee = 0;
				$tax = 0;
				foreach ($items as $item)
				{
					switch($item['DataClass'])
					{
						case 2:
							$deliveryFee += $item['SumMoney'];
							break;
						case 3:
							$settlementFee += $item['SumMoney'];
							break;
						case 4:
						    $tax += $item['SumMoney'];
						    break;
						default:
							$orders .= BaseGeneralUtils::rpad(sprintf("%s　（数量：% 2d）", $item['ItemNameKj'], $item['ItemNum']), '　', 21) . $item['SumMoney'] . "円\r\n";
							break;
					}
				}

				$body = mb_ereg_replace('{OrderItems}', $orders, $body);
				$body = mb_ereg_replace('{SettlementFee}', $settlementFee, $body);
				$body = mb_ereg_replace('{DeliveryFee}', $deliveryFee, $body);
				$body = mb_ereg_replace('{Tax}', $tax, $body);
				$body = mb_ereg_replace('{OneOrderItem}', $oneitem, $body);

				// メール送信履歴登録
				$mailSendSeq = $this->history->saveNew(array(
				        'MailTemplateId' => $tmpNumber,
				        'OrderSeq' => $oc['OrderSeq'],
				        'EnterpriseId' => null,
				        'ManCustId' => null,
				        'ToAddress' => $oc['MailAddress'],
				        'CcAddress' => null,
				        'BccAddress' => null,
				        'Subject' => $template['Subject'],
				        'Body' => $body,
				        'MailSendDate' => date('Y-m-d H:i:s'),
				        'ErrFlg' => 0,
				        'ErrReason' => null,
				        'RegistId' => $userId,
				        'UpdateId' => $userId,
				        'ValidFlg' => 1,
				));
				$this->sendDone(
					$template['FromTitle'],
					$template['FromAddress'],
					$oc['NameKj'] . '　様',
					$oc['MailAddress'],
					$template['Subject'],
					$body
				);
			}
		} catch(\Exception $e) {
		    if (isset($mailSendSeq)) {
		        // メール送信履歴を登録した場合、エラー理由を更新
		        $this->history->saveUpdate(array(
		                'ErrFlg' => 1,
		                'ErrReason' => $e->getMessage(),
		                'UpdateId' => $userId,
		        ), $mailSendSeq);
		    }
			throw new CoralMailException( 'cannot sent receipt confirmed mail.' . $e->getMessage(), 0, $e );
		}
	}

    public function SendCreditTransferConfirmMail($rcptseq, $userId)
    {
        try {
            // V_OrderCustomer に T_ReceiptControl を結合して顧客情報を取得する。
            $sql = <<<EOQ
SELECT  voc.*
    ,   rc.ReceiptProcessDate AS Rct_ReceiptProcessDate
    ,   rc.ReceiptAmount AS Rct_ReceiptAmount
FROM    V_OrderCustomer voc
        INNER JOIN T_ReceiptControl rc ON (rc.OrderSeq = voc.OrderSeq)
WHERE   rc.ReceiptSeq = :ReceiptSeq
;
EOQ;

            // 顧客情報の取得
            $oc = $this->adapter->query($sql)->execute(array(':ReceiptSeq' => $rcptseq))->current();

            // サイト.入金確認メール=0(送信しない)の注文は送信しない
            $mdlsite = new TableSite($this->adapter);
            $siteId = $oc['SiteId'];
            $site = $mdlsite->findSite($siteId)->current();
            if ($site['ReceiptMail'] == 0) {
                return;
            }

            if (CoralValidate::isNotEmpty($oc['MailAddress']))
            {

                // 使用すべきテンプレートの判定
                if ($this->isCelAddress($oc['MailAddress']))
                {
                    $tmpNumber = 118;
                }
                else
                {
                    $tmpNumber = 117;
                }

                // 事業者情報の取得
                $mdle = new TableEnterprise($this->adapter);
                $edata = $mdle->findEnterprise($oc['EnterpriseId'])->current();

                // 注文商品情報の取得
                $mdloi = new TableOrderItems($this->adapter);
                $items = $mdloi->findByP_OrderSeq($oc['OrderSeq']);
                $oneitem = $mdloi->getOneItemName($oc['OrderSeq']);

                // メールテンプレートの取得
                $mdlmt = new TableMailTemplate($this->adapter);
                $template = $mdlmt->findMailTemplate($tmpNumber, $edata['OemId'])->current();

                // メールの構築
                $receiptProcessDate = date('n月j日', strtotime($oc['Rct_ReceiptProcessDate']));   // 入金確認日
                $orderDate          = date('n月j日', strtotime($oc['ReceiptOrderDate']));         // 注文日

                $body = mb_ereg_replace('{EnterpriseNameKj}', $edata['EnterpriseNameKj'], $template['Body']);
                $body = mb_ereg_replace('{SiteNameKj}', $oc['SiteNameKj'], $body);
                $body = mb_ereg_replace('{Phone}', $edata['ContactPhoneNumber'], $body);
                $body = mb_ereg_replace('{Address}', $edata['PrefectureName']. $edata['City'] . $edata['Town'] . $edata['Building'], $body);
                $body = mb_ereg_replace('{CustomerNameKj}', $oc['NameKj'], $body);
                $body = mb_ereg_replace('{ReceiptDate}', $receiptProcessDate, $body);
                $body = mb_ereg_replace('{OrderDate}', $orderDate, $body);
                $body = mb_ereg_replace('{UseAmount}', $oc['Rct_ReceiptAmount'] . '円', $body);
                $body = mb_ereg_replace('{SiteUrl}', $site['Url'], $body);

                $this->insertInfoParamServer($body, $oc['EnterpriseId'], $oc['OemId']);
                $this->insertInfoParamServer($template['FromTitle'], $oc['EnterpriseId'], $oc['OemId']);
                $this->insertInfoParamServer($template['FromAddress'], $oc['EnterpriseId'], $oc['OemId']);
                $this->insertInfoParamServer($template['Subject'], $oc['EnterpriseId'], $oc['OemId']);

                $orderId = $oc['OrderId'];
                $this->insertOrderId($body, $orderId);
                $this->insertOrderId($template['Subject'], $orderId);

                // 注文商品リストの作成
                $orders = "";
                $deliveryFee = 0;
                $settlementFee = 0;
                $tax = 0;
                foreach ($items as $item)
                {
                    switch($item['DataClass'])
                    {
                        case 2:
                            $deliveryFee += $item['SumMoney'];
                            break;
                        case 3:
                            $settlementFee += $item['SumMoney'];
                            break;
                        case 4:
                            $tax += $item['SumMoney'];
                            break;
                        default:
                            $orders .= BaseGeneralUtils::rpad(sprintf("%s　（数量：% 2d）", $item['ItemNameKj'], $item['ItemNum']), '　', 21) . $item['SumMoney'] . "円\r\n";
                            break;
                    }
                }

                $body = mb_ereg_replace('{OrderItems}', $orders, $body);
                $body = mb_ereg_replace('{SettlementFee}', $settlementFee, $body);
                $body = mb_ereg_replace('{DeliveryFee}', $deliveryFee, $body);
                $body = mb_ereg_replace('{Tax}', $tax, $body);
                $body = mb_ereg_replace('{OneOrderItem}', $oneitem, $body);

                $creditTransferDate = $this->adapter->query(" SELECT F_CreditTransferDate FROM T_ClaimControl WHERE OrderSeq = :OrderSeq ")->execute(array(':OrderSeq' => $oc['OrderSeq']))->current()['F_CreditTransferDate'];
                $body = mb_ereg_replace('{CreditTransferDate}', date('Y年n月j日', strtotime($creditTransferDate)) , $body);
                $body = mb_ereg_replace('{MhfCreditTransferDisplayName}', $edata['MhfCreditTransferDisplayName'], $body);

                // メール送信履歴登録
                $mailSendSeq = $this->history->saveNew(array(
                                                           'MailTemplateId' => $tmpNumber,
                                                           'OrderSeq' => $oc['OrderSeq'],
                                                           'EnterpriseId' => null,
                                                           'ManCustId' => null,
                                                           'ToAddress' => $oc['MailAddress'],
                                                           'CcAddress' => null,
                                                           'BccAddress' => null,
                                                           'Subject' => $template['Subject'],
                                                           'Body' => $body,
                                                           'MailSendDate' => date('Y-m-d H:i:s'),
                                                           'ErrFlg' => 0,
                                                           'ErrReason' => null,
                                                           'RegistId' => $userId,
                                                           'UpdateId' => $userId,
                                                           'ValidFlg' => 1,
                                                       ));
                $this->sendDone(
                    $template['FromTitle'],
                    $template['FromAddress'],
                    $oc['NameKj'] . '　様',
                    $oc['MailAddress'],
                    $template['Subject'],
                    $body
                );
            }
        } catch(\Exception $e) {
            if (isset($mailSendSeq)) {
                // メール送信履歴を登録した場合、エラー理由を更新
                $this->history->saveUpdate(array(
                                               'ErrFlg' => 1,
                                               'ErrReason' => $e->getMessage(),
                                               'UpdateId' => $userId,
                                           ), $mailSendSeq);
            }
            throw new CoralMailException( 'cannot sent receipt confirmed mail.' . $e->getMessage(), 0, $e );
        }
    }

    /**
	 * 立替完了メールを送信する。
	 *
	 * @param int $payingControlSeq 立替振込管理データSeq
	 * @param int $userId ユーザーID
	 */
	public function SendExecChargeMail($payingControlSeq, $userId)
	{
	    try {
			// 立替振込管理データの取得
			$mdlpc = new TablePayingControl($this->adapter);
			$pdata = $mdlpc->find($payingControlSeq)->current();

			// 事業者情報の取得
			$mdle = new TableEnterprise($this->adapter);
			$edata = $mdle->findEnterprise($pdata['EnterpriseId'])->current();

			// 事業者.立替完了メール=0(送信しない)の事業者は送信しない
			if ($edata['PayingMail'] == 0) {
			    return;
			}

			// メールテンプレートの取得
			$mdlmt = new TableMailTemplate($this->adapter);
			$template = $mdlmt->findMailTemplate(8, $edata['OemId'])->current();

			// メールの構築
			$codeMaster = new CoralCodeMaster($this->adapter);
			$fixedDate = date('Y年m月d日', strtotime($pdata['FixedDate'])); // 立替締め日
			$execDate  = date('Y年m月d日', strtotime($pdata['ExecDate'] )); // 立替実行日

			$body = mb_ereg_replace('{EnterpriseNameKj}', $edata['EnterpriseNameKj'], $template['Body']);
			$body = mb_ereg_replace('{FixedPattern}', $codeMaster->getFixPatternCaption($edata['FixPattern']), $body);
			$body = mb_ereg_replace('{FixedDate}', $fixedDate, $body);
			$body = mb_ereg_replace('{ExecDate}', $execDate, $body);
			$body = mb_ereg_replace('{DecisionPayment}', $pdata['DecisionPayment'], $body);
			$body = mb_ereg_replace('{SettlementFee}', $pdata['SettlementFee'], $body);
			$body = mb_ereg_replace('{ClaimFee}', $pdata['ClaimFee'], $body);
			$body = mb_ereg_replace('{StampFee}', $pdata['StampFeeTotal'], $body);
			$body = mb_ereg_replace('{CancelAmount}', $pdata['CalcelAmount'], $body);
			$body = mb_ereg_replace('{MonthlyFee}', $pdata['MonthlyFee'], $body);
			$body = mb_ereg_replace('{TransferCommission}', $pdata['TransferCommission'], $body);

            $this->insertInfoParamServer($body, $edata['EnterpriseId'], $edata['OemId']);
            $this->insertInfoParamServer($template['FromTitle'], $edata['EnterpriseId'], $edata['OemId']);
            $this->insertInfoParamServer($template['FromAddress'], $edata['EnterpriseId'], $edata['OemId']);
            $this->insertInfoParamServer($template['Subject'], $edata['EnterpriseId'], $edata['OemId']);

			// メール送信履歴登録
			$mailSendSeq = $this->history->saveNew(array(
			        'MailTemplateId' => 8,
			        'OrderSeq' => null,
			        'EnterpriseId' => $pdata['EnterpriseId'],
			        'ManCustId' => null,
			        'ToAddress' => $edata['MailAddress'],
			        'CcAddress' => null,
			        'BccAddress' => null,
			        'Subject' => $template['Subject'],
			        'Body' => $body,
			        'MailSendDate' => date('Y-m-d H:i:s'),
			        'ErrFlg' => 0,
			        'ErrReason' => null,
			        'RegistId' => $userId,
			        'UpdateId' => $userId,
			        'ValidFlg' => 1,
			));
			$this->sendDone(
				$template['FromTitle'],
				$template['FromAddress'],
				$edata['EnterpriseNameKj'],
				$edata['MailAddress'],
				$template['Subject'],
				$body
			);

		} catch(\Exception $e) {
		    if (isset($mailSendSeq)) {
		        // メール送信履歴を登録した場合、エラー理由を更新
		        $this->history->saveUpdate(array(
		                'ErrFlg' => 1,
		                'ErrReason' => $e->getMessage(),
		                'UpdateId' => $userId,
		        ), $mailSendSeq);
		    }
			throw new CoralMailException( sprintf('cannot sent charge executed mail. (%s)', $edata['LoginId']), 0, $e );
		}
	}

	/**
	 * キャンセル確認メールを送信する。
	 *
	 * @param int $oseq 注文Seq
	 * @param int $userId ユーザーID
	 */
	public function SendCancelMail($oseq, $userId)
	{
	    try {
			// 注文情報の取得
			$mdloc = new ViewOrderCustomer($this->adapter);
			$oc = $mdloc->find($oseq)->current();

			// サイト.キャンセル確認メール=0(送信しない)の注文は送信しない
			$mdlsite = new TableSite($this->adapter);
			$siteId = $oc['SiteId'];
			$site = $mdlsite->findSite($siteId)->current();
			if ($site['CancelMail'] == 0) {
			    return;
			}

			// 事業者情報の取得
			$mdle = new TableEnterprise($this->adapter);
			$edata = $mdle->findEnterprise($oc['EnterpriseId'])->current();

			// キャンセル情報の取得
			$mdlcnl = new TableCancel($this->adapter);
			$cnlData = $mdlcnl->findCancel(array('OrderSeq' => $oseq))->current();

			// メールテンプレートの取得
			$mdlmt = new TableMailTemplate($this->adapter);
			$template = $mdlmt->findMailTemplate(9, $edata['OemId'])->current();

			// メールの構築

			// キャンセルフェイズの確認
			switch ($cnlData['CancelPhase'])
			{
				case 2:
					$cancelPhase = "立替後／顧客入金前";
					break;
				case 3:
					$cancelPhase = "立替後／顧客入金後";
					break;
				case 4:
					$cancelPhase = "立替前／顧客入金後";
					break;
				default:
					$cancelPhase = "立替前／顧客入金前";
					break;
			}

			$body = mb_ereg_replace('{EnterpriseNameKj}', $edata['EnterpriseNameKj'], $template['Body']);
			$body = mb_ereg_replace('{CancelPhase}', $cancelPhase, $body);
			$body = mb_ereg_replace('{OrderId}', $oc['OrderId'], $body);
			$body = mb_ereg_replace('{CustomerNameKj}', $oc['NameKj'], $body);
			$body = mb_ereg_replace('{UseAmount}', $oc['UseAmount'] . '円', $body);
			$body = mb_ereg_replace('{OrderDate}', date('n月j日', strtotime($oc['ReceiptOrderDate'])), $body);
			$body = mb_ereg_replace('{Ent_OrderId}', $oc['Ent_OrderId'], $body);

            $this->insertInfoParamServer($body, $oc['EnterpriseId'], $oc['OemId']);
            $this->insertInfoParamServer($template['FromTitle'], $oc['EnterpriseId'], $oc['OemId']);
            $this->insertInfoParamServer($template['FromAddress'], $oc['EnterpriseId'], $oc['OemId']);
            $this->insertInfoParamServer($template['Subject'], $oc['EnterpriseId'], $oc['OemId']);

            $orderId = $oc['OrderId'];
            $this->insertOrderId($body, $orderId);

			// メール送信履歴登録
			$mailSendSeq = $this->history->saveNew(array(
			        'MailTemplateId' => 9,
			        'OrderSeq' => null,
			        'EnterpriseId' => $oc['EnterpriseId'],
			        'ManCustId' => null,
			        'ToAddress' => $edata['MailAddress'],
			        'CcAddress' => null,
			        'BccAddress' => null,
			        'Subject' => mb_ereg_replace('{OrderId}', $oc['OrderId'], $template['Subject']),
			        'Body' => $body,
			        'MailSendDate' => date('Y-m-d H:i:s'),
			        'ErrFlg' => 0,
			        'ErrReason' => null,
			        'RegistId' => $userId,
			        'UpdateId' => $userId,
			        'ValidFlg' => 1,
			));
			$this->sendDone(
				$template['FromTitle'],
				$template['FromAddress'],
				$edata['EnterpriseNameKj'],
				$edata['MailAddress'],
				mb_ereg_replace('{OrderId}', $oc['OrderId'], $template['Subject']),
				$body
			);

		} catch(\Exception $e) {
		    if (isset($mailSendSeq)) {
		        // メール送信履歴を登録した場合、エラー理由を更新
		        $this->history->saveUpdate(array(
		                'ErrFlg' => 1,
		                'ErrReason' => $e->getMessage(),
		                'UpdateId' => $userId,
		        ), $mailSendSeq);
		    }
			throw new CoralMailException( 'cannot sent cancel accepted mail.', 0, $e );
		}
	}

	/**
	 * 与信用チェックメールを送信する。
	 *
	 * @param int $oseq
	 * @param int $userId ユーザーID
	 */
	public function SendCheckMail($oseq, $userId)
	{
	    try {
			// 顧客情報の取得
			$mdloc = new ViewOrderCustomer($this->adapter);
			$oc = $mdloc->find($oseq)->current();

			if (CoralValidate::isNotEmpty($oc['MailAddress']))
			{
				// 使用すべきテンプレートの判定
				if ($this->isCelAddress($oc['MailAddress']))
				{
					$tmpNumber = 10;
				}
				else
				{
					$tmpNumber = 10;
				}

				// 事業者情報の取得
				$mdle = new TableEnterprise($this->adapter);
				$edata = $mdle->findEnterprise($oc['EnterpriseId'])->current();

				// 注文商品情報の取得
				$mdloi = new TableOrderItems($this->adapter);
				$items = $mdloi->findByOrderSeq($oseq);

				// メールテンプレートの取得
				$mdlmt = new TableMailTemplate($this->adapter);
				$template = $mdlmt->findMailTemplate($tmpNumber, $edata['OemId'])->current();

				// メールの構築
				$subject = mb_ereg_replace('{OrderId}', $oc['OrderId'], $template['Subject']);

				$body = mb_ereg_replace('{EnterpriseNameKj}', $edata['EnterpriseNameKj'], $template['Body']);
				$body = mb_ereg_replace('{SiteNameKj}', $oc['SiteNameKj'], $body);
				$body = mb_ereg_replace('{CustomerNameKj}', $oc['NameKj'], $body);
				$body = mb_ereg_replace('{OrderDate}', date('n月j日', strtotime($oc['ReceiptOrderDate'])), $body);// 注文日
				$body = mb_ereg_replace('{UseAmount}', "\\" . $oc['UseAmount'], $body);

                $this->insertInfoParamServer($body, $oc['EnterpriseId'], $oc['OemId']);
                $this->insertInfoParamServer($template['FromTitle'], $oc['EnterpriseId'], $oc['OemId']);
                $this->insertInfoParamServer($template['FromAddress'], $oc['EnterpriseId'], $oc['OemId']);
                $this->insertInfoParamServer($subject, $oc['EnterpriseId'], $oc['OemId']);

                $orderId = $oc['OrderId'];
                $this->insertOrderId($body, $orderId);

				// 注文商品リストの作成
				$orders = "";
				$deliveryFee = 0;
				$settlementFee = 0;
				foreach ($items as $item)
				{
					switch($item['DataClass'])
					{
						case 2:
							$deliveryFee = $item['SumMoney'];
							break;
						case 3:
							$settlementFee = $item['SumMoney'];
							break;
						default:
							$orders .= BaseGeneralUtils::rpad(sprintf("%s　（数量：% 2d）", $item['ItemNameKj'], $item['ItemNum']), '　', 21) . "\\" . $item['SumMoney'] . "\r\n";
							break;
					}
				}

				$body = mb_ereg_replace('{OrderItems}', $orders, $body);
				$body = mb_ereg_replace('{SettlementFee}', $settlementFee, $body);
				$body = mb_ereg_replace('{DeliveryFee}', $deliveryFee, $body);

				// メール送信履歴登録
				$mailSendSeq = $this->history->saveNew(array(
				        'MailTemplateId' => $tmpNumber,
				        'OrderSeq' => $oseq,
				        'EnterpriseId' => null,
				        'ManCustId' => null,
				        'ToAddress' => $oc['MailAddress'],
				        'CcAddress' => null,
				        'BccAddress' => null,
				        'Subject' => $subject,
				        'Body' => $body,
				        'MailSendDate' => date('Y-m-d H:i:s'),
				        'ErrFlg' => 0,
				        'ErrReason' => null,
				        'RegistId' => $userId,
				        'UpdateId' => $userId,
				        'ValidFlg' => 1,
				));
				$this->sendDone(
					$template['FromTitle'],
					$template['FromAddress'],
					$oc['NameKj'] . '　様',
					$oc['MailAddress'],
					$subject,
					$body
				);

			}
		} catch(\Exception $e) {
		    if (isset($mailSendSeq)) {
		        // メール送信履歴を登録した場合、エラー理由を更新
		        $this->history->saveUpdate(array(
		                'ErrFlg' => 1,
		                'ErrReason' => $e->getMessage(),
		                'UpdateId' => $userId,
		        ), $mailSendSeq);
		    }
			throw new CoralMailException( 'cannot sent check mail.', 0, $e );
		}
	}

	/**
	 * 事業者情報編集メール
	 *
	 * @param int $enterpriseId 事業者ID
	 * @param int $userId ユーザーID
	 * @param string $toAddress 送信先メールアドレス
	 */
	public function SendModifyEntMail($enterpriseId, $userId, $toAddress = 'customer@ato-barai.com')
	{
	    try {
			// 事業者情報の取得
			$mdle = new TableEnterprise($this->adapter);
			$edata = $mdle->findEnterprise($enterpriseId)->current();

			// メールの構築
			$body = "以下の事業者の情報が編集されました。\r\n\r\n"
				. sprintf("%s　（%s）\r\n", $edata['EnterpriseNameKj'], $edata['LoginId']);

            $this->insertInfoParamServer($body, $edata['EnterpriseId'], $edata['OemId']);


			$subject = '[通知] 事業者情報編集';

			// メール送信履歴登録
			$mailSendSeq = $this->history->saveNew(array(
			        'MailTemplateId' => null,
			        'OrderSeq' => null,
			        'EnterpriseId' => $enterpriseId,
			        'ManCustId' => null,
			        'ToAddress' => $toAddress,
			        'CcAddress' => null,
			        'BccAddress' => null,
			        'Subject' => $subject,
			        'Body' => $body,
			        'MailSendDate' => date('Y-m-d H:i:s'),
			        'ErrFlg' => 0,
			        'ErrReason' => null,
			        'RegistId' => $userId,
			        'UpdateId' => $userId,
			        'ValidFlg' => 1,
			));
			$this->sendDone(
				'Coral System',
				'customer@ato-barai.com',
				'カスタマーセンター',
				$toAddress,
				$subject,
				$body
			);

		} catch(\Exception $e) {
		    if (isset($mailSendSeq)) {
		        // メール送信履歴を登録した場合、エラー理由を更新
		        $this->history->saveUpdate(array(
		                'ErrFlg' => 1,
		                'ErrReason' => $e->getMessage(),
		                'UpdateId' => $userId,
		        ), $mailSendSeq);
		    }
			throw new CoralMailException( 'cannot sent subscriber information modified mail.', 0, $e );
		}
	}

	/**
	 * もうすぐお支払メール
	 *
	 * @param int $oseq 注文Seq
	 * @param int $userId ユーザーID
	 * @return boolean 送信したか否か
	 */
	public function SendPaymentSoonMail($oseq, $userId)
	{
	    try
		{
			// 顧客情報の取得
			$mdloc = new ViewOrderCustomer($this->adapter);
			$oc = $mdloc->find($oseq)->current();
			$oc['UseAmount'] = $this->adapter->query(" SELECT UseAmountTotal FROM T_ClaimControl WHERE OrderSeq = :OrderSeq ")->execute(array(':OrderSeq' => $oseq))->current()['UseAmountTotal'];

			// サイト.もうすぐお支払メール=0(送信しない)の注文は送信しない
			$mdlsite = new TableSite($this->adapter);
			$siteId = $oc['SiteId'];
			$site = $mdlsite->findSite($siteId)->current();
			if ($site['SoonPaymentMail'] == 0) {
			    return false;
			}

			if (CoralValidate::isNotEmpty($oc['MailAddress']))
			{
			    // CB_B2C_DEV-237 注文状態＝クレジット決済の場合はメール送信しない
                // 注文会計情報の取得
                $mdlao = new ATableOrder($this->adapter);
                $aodata = $mdlao->find($oseq)->current();
                $oc['ExtraPayType'] = $aodata['ExtraPayType'];
                $rowClass = CoralOrderUtility::getOrderRowClass( $oc , $oc['Cnl_ReturnSaikenCancelFlg']);
                if ($rowClass == 'credit') {
                    return false;
                }

                // 使用すべきテンプレートの判定
				if ($this->isCelAddress($oc['MailAddress']))
				{
					$tmpNumber = 12;
				}
				else
				{
					$tmpNumber = 11;
				}
				// 事業者情報の取得
				$mdle = new TableEnterprise($this->adapter);
				$edata = $mdle->findEnterprise($oc['EnterpriseId'])->current();

				// 注文商品情報の取得
				$mdloi = new TableOrderItems($this->adapter);
				$item = $mdloi->getOneItemName2($oc['OrderSeq']);

				// メールテンプレートの取得
				$mdlmt = new TableMailTemplate($this->adapter);
				$template = $mdlmt->findMailTemplate($tmpNumber, $edata['OemId'])->current();

				// メールの構築
				$orderDate = date('Y年n月j日', strtotime($oc['ReceiptOrderDate']));// 注文日
				$issueDate = date('Y年n月j日', strtotime($oc['Clm_F_ClaimDate'])); // 請求日
				$limitDate = date('Y年n月j日', strtotime($oc['Clm_F_LimitDate'])); // 支払期限

				$subject = mb_ereg_replace('{OrderId}', $oc['OrderId'], $template['Subject']);

				$body = mb_ereg_replace('{SiteNameKj}', $oc['SiteNameKj'], $template['Body']);
				$body = mb_ereg_replace('{CustomerNameKj}', $oc['NameKj'], $body);
				$body = mb_ereg_replace('{OrderDate}', $orderDate, $body);
				$body = mb_ereg_replace('{UseAmount}', $oc['UseAmount'] . '円', $body);

				$body = mb_ereg_replace('{OneOrderItem}', $item, $body);
				$body = mb_ereg_replace('{IssueDate}', $issueDate, $body);
				$body = mb_ereg_replace('{LimitDate}', $limitDate, $body);

				$body = mb_ereg_replace('{Phone}', $edata['ContactPhoneNumber'], $body);

                $this->insertInfoParamServer($body, $edata['EnterpriseId'], $edata['OemId']);
                $this->insertInfoParamServer($template['FromTitle'], $edata['EnterpriseId'], $edata['OemId']);
                $this->insertInfoParamServer($template['FromAddress'], $edata['EnterpriseId'], $edata['OemId']);
                $this->insertInfoParamServer($subject, $edata['EnterpriseId'], $edata['OemId']);

                $orderId = $oc['OrderId'];
                $this->insertOrderId($body, $orderId);

				// 最終請求時の請求口座情報を展開
				$body = $this->applyClaimAccounts($body, $oseq);

				// メール送信履歴登録
				$mailSendSeq = $this->history->saveNew(array(
				        'MailTemplateId' => $tmpNumber,
				        'OrderSeq' => $oseq,
				        'EnterpriseId' => null,
				        'ManCustId' => null,
				        'ToAddress' => $oc['MailAddress'],
				        'CcAddress' => null,
				        'BccAddress' => null,
				        'Subject' => $subject,
				        'Body' => $body,
				        'MailSendDate' => date('Y-m-d H:i:s'),
				        'ErrFlg' => 0,
				        'ErrReason' => null,
				        'RegistId' => $userId,
				        'UpdateId' => $userId,
				        'ValidFlg' => 1,
				));
				$this->sendDone(
					$template['FromTitle'],
					$template['FromAddress'],
					$oc['NameKj'] . '　様',
					$oc['MailAddress'],
					$subject,
					$body
				);
				return true;
			}
			else
			{
				return false;
			}
		} catch(\Exception $e) {
		    if (isset($mailSendSeq)) {
		        // メール送信履歴を登録した場合、エラー理由を更新
		        $this->history->saveUpdate(array(
		                'ErrFlg' => 1,
		                'ErrReason' => $e->getMessage(),
		                'UpdateId' => $userId,
		        ), $mailSendSeq);
		    }
			throw new CoralMailException( 'cannot sent receipt confirmed mail.', 0, $e );
		}
	}

    /**
     * もうすぐ口座振替メール
     *
     * @param int $oseq 注文Seq
     * @param int $userId ユーザーID
     * @return boolean 送信したか否か
     */
    public function SendCreditTransferSoonMail($oseq, $userId)
    {
        try
        {
            // 顧客情報の取得
            $mdloc = new ViewOrderCustomer($this->adapter);
            $oc = $mdloc->find($oseq)->current();
            $oc['UseAmount'] = $this->adapter->query(" SELECT UseAmountTotal FROM T_ClaimControl WHERE OrderSeq = :OrderSeq ")->execute(array(':OrderSeq' => $oseq))->current()['UseAmountTotal'];

            // サイト.もうすぐお支払メール=0(送信しない)の注文は送信しない
            $mdlsite = new TableSite($this->adapter);
            $siteId = $oc['SiteId'];
            $site = $mdlsite->findSite($siteId)->current();
            if ($site['SoonPaymentMail'] == 0) {
                return false;
            }

            if (CoralValidate::isNotEmpty($oc['MailAddress']))
            {
                // CB_B2C_DEV-237 注文状態＝クレジット決済の場合はメール送信しない
                // 注文会計情報の取得
                $mdlao = new ATableOrder($this->adapter);
                $aodata = $mdlao->find($oseq)->current();
                $oc['ExtraPayType'] = $aodata['ExtraPayType'];
                $rowClass = CoralOrderUtility::getOrderRowClass( $oc , $oc['Cnl_ReturnSaikenCancelFlg']);
                if ($rowClass == 'credit') {
                    return false;
                }

                // 使用すべきテンプレートの判定
                if ($this->isCelAddress($oc['MailAddress']))
                {
                    $tmpNumber = 116;
                }
                else
                {
                    $tmpNumber = 115;
                }
                // 事業者情報の取得
                $mdle = new TableEnterprise($this->adapter);
                $edata = $mdle->findEnterprise($oc['EnterpriseId'])->current();

                // 注文商品情報の取得
                $mdloi = new TableOrderItems($this->adapter);
                $item = $mdloi->getOneItemName2($oc['OrderSeq']);

                // メールテンプレートの取得
                $mdlmt = new TableMailTemplate($this->adapter);
                $template = $mdlmt->findMailTemplate($tmpNumber, $edata['OemId'])->current();

                // メールの構築
                $orderDate = date('Y年n月j日', strtotime($oc['ReceiptOrderDate']));// 注文日
                $issueDate = date('Y年n月j日', strtotime($oc['Clm_F_ClaimDate'])); // 請求日
                $limitDate = date('Y年n月j日', strtotime($oc['Clm_F_LimitDate'])); // 支払期限

                $subject = mb_ereg_replace('{OrderId}', $oc['OrderId'], $template['Subject']);

                $body = mb_ereg_replace('{SiteNameKj}', $oc['SiteNameKj'], $template['Body']);
                $body = mb_ereg_replace('{CustomerNameKj}', $oc['NameKj'], $body);
                $body = mb_ereg_replace('{OrderDate}', $orderDate, $body);
                $body = mb_ereg_replace('{UseAmount}', $oc['UseAmount'] . '円', $body);

                $body = mb_ereg_replace('{OneOrderItem}', $item, $body);
                $body = mb_ereg_replace('{IssueDate}', $issueDate, $body);
                $body = mb_ereg_replace('{LimitDate}', $limitDate, $body);

                $body = mb_ereg_replace('{Phone}', $edata['ContactPhoneNumber'], $body);
                $creditTransferDate = $this->adapter->query(" SELECT F_CreditTransferDate FROM T_ClaimControl WHERE OrderSeq = :OrderSeq ")->execute(array(':OrderSeq' => $oseq))->current()['F_CreditTransferDate'];
                $body = mb_ereg_replace('{CreditTransferDate}', date('Y年n月j日', strtotime($creditTransferDate)) , $body);
                $body = mb_ereg_replace('{MhfCreditTransferDisplayName}', $edata['MhfCreditTransferDisplayName'], $body);

                $this->insertInfoParamServer($body, $oc['EnterpriseId'], $oc['OemId']);
                $this->insertInfoParamServer($template['FromTitle'], $oc['EnterpriseId'], $oc['OemId']);
                $this->insertInfoParamServer($template['FromAddress'], $oc['EnterpriseId'], $oc['OemId']);
                $this->insertInfoParamServer($subject, $oc['EnterpriseId'], $oc['OemId']);

                $orderId = $oc['OrderId'];
                $this->insertOrderId($body, $orderId);

                // 最終請求時の請求口座情報を展開
                $body = $this->applyClaimAccounts($body, $oseq);

                // メール送信履歴登録
                $mailSendSeq = $this->history->saveNew(array(
                                                           'MailTemplateId' => $tmpNumber,
                                                           'OrderSeq' => $oseq,
                                                           'EnterpriseId' => null,
                                                           'ManCustId' => null,
                                                           'ToAddress' => $oc['MailAddress'],
                                                           'CcAddress' => null,
                                                           'BccAddress' => null,
                                                           'Subject' => $subject,
                                                           'Body' => $body,
                                                           'MailSendDate' => date('Y-m-d H:i:s'),
                                                           'ErrFlg' => 0,
                                                           'ErrReason' => null,
                                                           'RegistId' => $userId,
                                                           'UpdateId' => $userId,
                                                           'ValidFlg' => 1,
                                                       ));

                $this->sendDone(
                    $template['FromTitle'],
                    $template['FromAddress'],
                    $oc['NameKj'] . '　様',
                    $oc['MailAddress'],
                    $subject,
                    $body
                );
                return true;
            }
            else
            {
                return false;
            }
        } catch(\Exception $e) {
            if (isset($mailSendSeq)) {
                // メール送信履歴を登録した場合、エラー理由を更新
                $this->history->saveUpdate(array(
                                               'ErrFlg' => 1,
                                               'ErrReason' => $e->getMessage(),
                                               'UpdateId' => $userId,
                                           ), $mailSendSeq);
            }
            throw new CoralMailException( 'cannot sent receipt confirmed mail.', 0, $e );
        }
    }

    /**
	 * お支払未確認メール
	 *
	 * @param int $oseq 注文Seq
	 * @param int $userId ユーザーID
	 * @return boolean 送信したか否か
	 */
	public function SendLimitPassageMail($oseq, $userId)
	{
	    try
		{
			// 顧客情報の取得
			$mdloc = new ViewOrderCustomer($this->adapter);
			$oc = $mdloc->find($oseq)->current();
			$oc['UseAmount'] = $this->adapter->query(" SELECT UseAmountTotal FROM T_ClaimControl WHERE OrderSeq = :OrderSeq ")->execute(array(':OrderSeq' => $oseq))->current()['UseAmountTotal'];

			// サイト.お支払未確認メール=0(送信しない)の注文は送信しない
			$mdlsite = new TableSite($this->adapter);
			$siteId = $oc['SiteId'];
			$site = $mdlsite->findSite($siteId)->current();
			if ($site['NotPaymentConfMail'] == 0) {
			    return false;
			}

			if (CoralValidate::isNotEmpty($oc['MailAddress']))
			{
				// 使用すべきテンプレートの判定
				if ($this->isCelAddress($oc['MailAddress']))
				{
					$tmpNumber = 14;
				}
				else
				{
					$tmpNumber = 13;
				}

				// 事業者情報の取得
				$mdle = new TableEnterprise($this->adapter);
				$edata = $mdle->findEnterprise($oc['EnterpriseId'])->current();

				// 注文商品情報の取得
				$mdloi = new TableOrderItems($this->adapter);
				$item = $mdloi->getOneItemName($oc['OrderSeq']);

				// メールテンプレートの取得
				$mdlmt = new TableMailTemplate($this->adapter);
				$template = $mdlmt->findMailTemplate($tmpNumber, $edata['OemId'])->current();

				if(!$template) {
				    throw new CoralMailException('メールテンプレートが存在しません。設定を見直してください');
				}

				// メールの構築
				$orderDate = date('Y年n月j日', strtotime($oc['ReceiptOrderDate']));// 注文日
				$issueDate = date('Y年n月j日', strtotime($oc['Clm_F_ClaimDate'])); // 請求日
				$limitDate = date('Y年n月j日', strtotime($oc['Clm_F_LimitDate'])); // 支払期限

				$subject = mb_ereg_replace('{OrderDate}', $orderDate, $template['Subject']);
				$subject = mb_ereg_replace('{SiteNameKj}', $oc['SiteNameKj'], $subject);
				$subject = mb_ereg_replace('{OrderId}', $oc['OrderId'], $subject);

				$body = mb_ereg_replace('{SiteNameKj}', $oc['SiteNameKj'], $template['Body']);
				$body = mb_ereg_replace('{CustomerNameKj}', $oc['NameKj'], $body);
				$body = mb_ereg_replace('{OrderDate}', $orderDate, $body);
				$body = mb_ereg_replace('{UseAmount}', $oc['UseAmount'], $body);

				$body = mb_ereg_replace('{OneOrderItem}', $item, $body);
				$body = mb_ereg_replace('{IssueDate}', $issueDate, $body);
				$body = mb_ereg_replace('{LimitDate}', $limitDate, $body);

				$body = mb_ereg_replace('{DamageInterest}', $oc['Clm_L_DamageInterestAmount'], $body);
				$body = mb_ereg_replace('{ReClaimFee}', $oc['Clm_L_ClaimFee'] + $oc['Clm_L_AdditionalClaimFee'], $body);
				$body = mb_ereg_replace('{TotalAmount}', $oc['UseAmount'] + $oc['Clm_L_DamageInterestAmount'] + $oc['Clm_L_ClaimFee'] + $oc['Clm_L_AdditionalClaimFee'], $body);

				$body = mb_ereg_replace('{Phone}', $edata['ContactPhoneNumber'], $body);

				// 2009.6.1 支払い済み金額の反映
				// 2009.8.13 支払い済み金額のマイナス表示対応
				if ($oc['InstallmentPlanAmount'] == null || $oc['InstallmentPlanAmount'] == 0)
				{
					$body = mb_ereg_replace('{InstPlanAmount}', 0, $body);
				}
				else
				{
					$body = mb_ereg_replace('{InstPlanAmount}', $oc['InstallmentPlanAmount'] * -1, $body);
				}

				$body = mb_ereg_replace('{TotalAmount2}', $oc['UseAmount'] + $oc['Clm_L_DamageInterestAmount'] + $oc['Clm_L_ClaimFee'] + $oc['Clm_L_AdditionalClaimFee'] - $oc['InstallmentPlanAmount'], $body);

                $this->insertInfoParamServer($body, $oc['EnterpriseId'], $oc['OemId']);
                $this->insertInfoParamServer($template['FromTitle'], $oc['EnterpriseId'], $oc['OemId']);
                $this->insertInfoParamServer($template['FromAddress'], $oc['EnterpriseId'], $oc['OemId']);
                $this->insertInfoParamServer($subject, $oc['EnterpriseId'], $oc['OemId']);

                $orderId = $oc['OrderId'];
                $this->insertOrderId($body, $orderId);

				// 最終請求時の請求口座情報を展開
				$body = $this->applyClaimAccounts($body, $oseq);

				// メール送信履歴登録
				$mailSendSeq = $this->history->saveNew(array(
				        'MailTemplateId' => $tmpNumber,
				        'OrderSeq' => $oseq,
				        'EnterpriseId' => null,
				        'ManCustId' => null,
				        'ToAddress' => $oc['MailAddress'],
				        'CcAddress' => null,
				        'BccAddress' => null,
				        'Subject' => $subject,
				        'Body' => $body,
				        'MailSendDate' => date('Y-m-d H:i:s'),
				        'ErrFlg' => 0,
				        'ErrReason' => null,
				        'RegistId' => $userId,
				        'UpdateId' => $userId,
				        'ValidFlg' => 1,
				));
				$this->sendDone(
					$template['FromTitle'],
					$template['FromAddress'],
					$oc['NameKj'] . '　様',
					$oc['MailAddress'],
					$subject,
					$body
				);

				return true;
			}
			else
			{
				return false;
			}
		} catch(\Exception $e) {
		    if (isset($mailSendSeq)) {
		        // メール送信履歴を登録した場合、エラー理由を更新
		        $this->history->saveUpdate(array(
		                'ErrFlg' => 1,
		                'ErrReason' => $e->getMessage(),
		                'UpdateId' => $userId,
		        ), $mailSendSeq);
		    }
			throw new CoralMailException( 'cannot sent limit passage.', 0, $e );
		}
	}

	/**
	 * 配送伝票番号相違通知メールを送信
	 *
	 * @param int $oseq 注文シーケンス
	 * @param int $userId ユーザーID
	 */
	public function SendSlipNumberDifference($oseq, $userId) {
		// メールテンプレートID
		$template_number = 15;

		// テンプレートビューを初期化
		$view = new ViewArrivalConfirm($this->adapter);

		try {
            // 注文情報の取得
            $mdlo = new TableOrder($this->adapter);
            $odata = $mdlo->find($oseq)->current();

			// メールテンプレートの取得
			$mdlmt = new TableMailTemplate($this->adapter);
			$template = $mdlmt->findMailTemplate($template_number, $odata['OemId'])->current();

			if(!$template) {
				throw new CoralMailException('メールテンプレートが存在しません。設定を見直してください');
			}

			// 件名と本文テンプレート
			$subject = $template['Subject'];
			$body = $template['Body'];

			// 変数展開に必要なデータを取得
			$row = $view->getArrivalConfirmDetailForDiffMail($oseq);
			if(!$row) throw new CoralMailException(sprintf('シーケンス %s の注文情報が見つかりません', $oseq));

			// 取得した行データの検証
			$errors = $this->validateForSlipNumberDiffMail($row);
			if(count($errors)) {
				// 検証エラーはそのまま例外としてthrow
				$messages = array();
				foreach($errors as $err) {

					$messages[] = $err;
				}
				throw new CoralMailException(join("\n", $messages));
			}

			// 日付データに書式を適用
			foreach(array('ReceiptOrderDate', 'Deli_JournalIncDate') as $key) {
				$row[$key] = f_df($row[$key], 'Y年m月d日');
			}

			// テンプレート変数を置換
			foreach(array_keys($row) as $key) {
				$body = mb_ereg_replace(sprintf('{%s}', $key), $row[$key], $body);
			}

            $this->insertInfoParamServer($body, $row['EnterpriseId'], $odata['OemId']);
            $this->insertInfoParamServer($template['FromTitle'], $row['EnterpriseId'], $row['OemId']);
            $this->insertInfoParamServer($template['FromAddress'], $row['EnterpriseId'], $row['OemId']);
            $this->insertInfoParamServer($subject, $row['EnterpriseId'], $row['OemId']);

            $orderId = $odata['OrderId'];
            $this->insertOrderId($body, $orderId);
            $this->insertOrderId($subject, $orderId);

			// メール送信履歴登録
			$mailSendSeq = $this->history->saveNew(array(
			        'MailTemplateId' => $template_number,
			        'OrderSeq' => null,
			        'EnterpriseId' => $row['EnterpriseId'],
			        'ManCustId' => null,
			        'ToAddress' => $row['MailAddress'],
			        'CcAddress' => null,
			        'BccAddress' => null,
			        'Subject' => $subject,
			        'Body' => $body,
			        'MailSendDate' => date('Y-m-d H:i:s'),
			        'ErrFlg' => 0,
			        'ErrReason' => null,
			        'RegistId' => $userId,
			        'UpdateId' => $userId,
			        'ValidFlg' => 1,
			));
			// メール送信実行
			$this->sendDone(
				$template['FromTitle'],
				$template['FromAddress'],
				sprintf('%s　様', $row['CpNameKj']),
				$row['MailAddress'],
				$subject,
				$body
			);

		} catch(\Exception $err) {
		    if (isset($mailSendSeq)) {
		        // メール送信履歴を登録した場合、エラー理由を更新
		        $this->history->saveUpdate(array(
		                'ErrFlg' => 1,
		                'ErrReason' => $e->getMessage(),
		                'UpdateId' => $userId,
		        ), $mailSendSeq);
		    }
			// 例外はそのまま上位レイヤーへthrow
			throw $err;
		}
	}

	/**
	 * 請求書不達メールを送信する
	 *
	 * @param int $oseq
	 * @param int $userId ユーザーID
	 */
	public function SendReturnBillMail($oseq, $userId)
	{
	    try {
			// 顧客情報の取得
			$mdloc = new ViewOrderCustomer($this->adapter);
			$oc = $mdloc->find($oseq)->current();

			if (CoralValidate::isNotEmpty($oc['MailAddress']))
			{
				// メールテンプレートID
				$tmpNumber = 16;

				// 事業者情報の取得
				$mdle = new TableEnterprise($this->adapter);
				$edata = $mdle->findEnterprise($oc['EnterpriseId'])->current();

				// 注文商品情報の取得
				$mdloi = new TableOrderItems($this->adapter);
				$items = $mdloi->findByP_OrderSeq($oseq);

				// 請求情報の取得
				$mdlhis = new TableClaimHistory($this->adapter);
				$his = $mdlhis->getReturnMailTagetByOrderSeq($oc['OrderSeq'])->current();
				if(!$his) {
					throw new \Exception(sprintf('cannot get claim-history. OrderSeq = %s', $oc['OrderSeq']));
				}

				// メールテンプレートの取得
				$mdlmt = new TableMailTemplate($this->adapter);
				$template = $mdlmt->findMailTemplate($tmpNumber, $edata['OemId'])->current();
				if(!$template) throw new \Exception('メールテンプレートが存在しません');

				// メールの構築
				$body = mb_ereg_replace('{EnterpriseNameKj}', $edata['EnterpriseNameKj'], $template['Body']);//事業者名
				$body = mb_ereg_replace('{SiteNameKj}', $oc['SiteNameKj'], $body);//サイト名
				$body = mb_ereg_replace('{CustomerNameKj}', $oc['NameKj'], $body);//購入者氏名
				$body = mb_ereg_replace('{OrderDate}', date('n月j日', strtotime($oc['ReceiptOrderDate'])), $body);// 注文日
				$body = mb_ereg_replace('{UnitingAddress}', $oc['UnitingAddress'], $body);//購入者住所
				$body = mb_ereg_replace('{ClaimDate}', f_df($oc['Clm_L_ClaimDate'], 'n月j日'), $body);//請求日
				$body = mb_ereg_replace('{Phone}', $edata['ContactPhoneNumber'], $body);// 電話番号

                $this->insertInfoParamServer($body, $oc['EnterpriseId'], $oc['OemId']);
                $this->insertInfoParamServer($template['FromTitle'], $oc['EnterpriseId'], $oc['OemId']);
                $this->insertInfoParamServer($template['FromAddress'], $oc['EnterpriseId'], $oc['OemId']);
                $this->insertInfoParamServer($template['Subject'], $oc['EnterpriseId'], $oc['OemId']);

                $orderId = $oc['OrderId'];
                $this->insertOrderId($body, $orderId);
                $this->insertOrderId($template['Subject'], $orderId);

				// 注文商品リストの作成
				$deliveryFee = 0;
				$settlementFee = 0;
				$tax = 0;
				$otherItemCount = -1;
				foreach ($items as $item)
				{
					switch($item['DataClass'])
					{
						case 2:
							$deliveryFee += $item['SumMoney'];
							break;
						case 3:
							$settlementFee += $item['SumMoney'];
							break;
						case 4:
						    $tax += $item['SumMoney'];
						    break;
						default:
							if(empty($itemName)) {
								$itemName = $item['ItemNameKj'];
							}
							$otherItemCount++;
							$itemSum = $itemSum + $item['SumMoney'];
							break;
					}
				}

				if(!empty($otherItemCount)) {
					$itemName = $itemName . '(他'.$otherItemCount.'点)';
				}

				$body = mb_ereg_replace('{ItemNameKj}', $itemName, $body);
				$body = mb_ereg_replace('{SettlementFee}', $settlementFee , $body);
				$body = mb_ereg_replace('{DeliveryFee}', $deliveryFee, $body);
				$body = mb_ereg_replace('{ItemAmount}', $itemSum, $body);
				$body = mb_ereg_replace('{Tax}', $tax, $body);

				//追加料金設定
				$damageInterestAmount = 0;
				$claimFee = 0;
				$additionalClaimFee = 0;
				$optionMessage = "";

				if('1' !== $his['ClaimPattern']) {

					if(!empty($his['DamageInterestAmount'])) {
						$optionMessage .= "遅延損害金：" . $his['DamageInterestAmount'] . "円\n";
						$damageInterestAmount = $his['DamageInterestAmount'];
					}

					if(!empty($his['ClaimFee'])) {
						$optionMessage .= "請求手数料：" . $his['ClaimFee'] . "円\n";
						$claimFee = $his['ClaimFee'];
					}

					if(!empty($his['AdditionalClaimFee'])) {
						$optionMessage .= "追加手数料：" . $his['AdditionalClaimFee'] . "円\n";
						$additionalClaimFee = $his['AdditionalClaimFee'];
					}
				}

				$body = mb_ereg_replace('{OptionFee}', $optionMessage, $body);

				//合計金額
				$totalAmount = $this->adapter->query(" SELECT UseAmountTotal FROM T_ClaimControl WHERE OrderSeq = :OrderSeq ")->execute(array(':OrderSeq' => $oseq))->current()['UseAmountTotal'];
				$body = mb_ereg_replace('{UseAmount}', $totalAmount, $body);

				// 最終請求時の請求口座情報を展開
				$body = $this->applyClaimAccounts($body, $oseq);

				// メール送信履歴登録
				$mailSendSeq = $this->history->saveNew(array(
				        'MailTemplateId' => $tmpNumber,
				        'OrderSeq' => $oseq,
				        'EnterpriseId' => null,
				        'ManCustId' => null,
				        'ToAddress' => $oc['MailAddress'],
				        'CcAddress' => null,
				        'BccAddress' => null,
				        'Subject' => $template['Subject'],
				        'Body' => $body,
				        'MailSendDate' => date('Y-m-d H:i:s'),
				        'ErrFlg' => 0,
				        'ErrReason' => null,
				        'RegistId' => $userId,
				        'UpdateId' => $userId,
				        'ValidFlg' => 1,
				));
				$this->sendDone(
						$template['FromTitle'],
						$template['FromAddress'],
						$oc['NameKj'] . '様',
						$oc['MailAddress'],
						$template['Subject'],
						$body
				);

			}
		} catch(\Exception $e) {
		    if (isset($mailSendSeq)) {
		        // メール送信履歴を登録した場合、エラー理由を更新
		        $this->history->saveUpdate(array(
		                'ErrFlg' => 1,
		                'ErrReason' => $e->getMessage(),
		                'UpdateId' => $userId,
		        ), $mailSendSeq);
		    }
			throw new CoralMailException( 'cannot sent mail about return bill. ' . $e->getMessage(), 0, $e );
		}
	}

	/**
	 * 与信結果メールを送信する
	 *
	 * @param int $oseq
	 * @param boolean $occReason
	 * @param int $userId ユーザーID
	 * @return array
	 */
	public function SendCjMail($oseq, $occReason, $userId)
	{
	    $resultData["MailAddress"] = "";              // メール送信フラグ
		$resultData["SendMailFlg"] = -1;              // 送信先メールアドレス
		$resultData["EnterpriseId"] = "";             // 事業者ＩＤ
		$resultData["OemId"] = "";                    // 事業者_OemID
		$resultData["CpNameKj"] = "";                 // 事業者_担当者名
		$resultData["eMailAddress"] = "";             // 事業者_メールアドレス
		$resultData["OrderId"] = "";                  // 注文ＩＤ

		try {
			// 注文情報の取得
			$mdlo = new TableOrder($this->adapter);
			$o = $mdlo->find($oseq)->current();

			// 送信可能時間か否か
			$canSend = false;

			//本番用
			$hour = (int)date('G');

			if ($o['OrderRegisterMethod'] == 1 || ($hour >= 8 && $hour < 20))
			{
				$canSend = true;
			}

			// 顧客情報の取得
			$mdloc = new ViewOrderCustomer($this->adapter);
			$oc = $mdloc->find($oseq)->current();

			// 事業者情報の取得
			$mdle = new TableEnterprise($this->adapter);
			$edata = $mdle->findEnterprise($oc['EnterpriseId'])->current();

			// サイト情報の取得
			$mdls = new TableSite($this->adapter);
			$sdata = $mdls->findSite($oc['SiteId'])->current();

			// 送信先のメールアドレス
			$resultData["MailAddress"] = $oc['MailAddress'];

			$chk = $mdlo->isCanceled($oseq);
			if ($chk)
			{
				// キャンセルされているので送信しない
				$resultData["SendMailFlg"] = 3;
			}
			else if (!CoralValidate::isNotEmpty($oc['MailAddress']))
			{
				// メールアドレスが無いので送信しない
				$resultData["SendMailFlg"] = 4;
			}
			else if (!$this->canSendCjMail($occReason, $sdata['CreditResultMail']))
			{
				// 送信対象では無いので送信しない
				$resultData["SendMailFlg"] = 2;
			}
			else if (!$canSend)
			{
				// 送信可能時間では無いため保留
				$resultData["SendMailFlg"] = 0;
				$resultData["MailAddress"] = "SYSTEM HOLD";
			}
			else
			{
				// 送信実行
				$resultData["SendMailFlg"] = 1;
				$resultData["EnterpriseId"] = $edata['EnterpriseId'];
				$resultData["OemId"] = $edata['OemId'];
				$resultData["CpNameKj"] = $edata['CpNameKj'];
				$resultData["eMailAddress"] = $edata['MailAddress'];
				$resultData["OrderId"] = $o['OrderId'];

				// メールテンプレートID
				if ($occReason == 6)
				{
				    // コードマスターの区分１（PC）/区分２（携帯）
				    $mdlCode = new TableCode($this->adapter);
				    $code = $mdlCode->find(92, $o['PendingReasonCode'])->current();
				    if($code !== false) {
				        $tmpNumber = $this->isCelAddress($oc['MailAddress']) ? $code['Class2'] : $code['Class1'];
				    }
				    else {
				        $tmpNumber = 0;
				    }
				}
				elseif ($occReason == 1 || $occReason == 4)
				{
					$tmpNumber = $this->isCelAddress($oc['MailAddress']) ? 20 : 19;
				}
				else
				{
					$tmpNumber = $this->isCelAddress($oc['MailAddress']) ? 22 : 21;
				}

				// メールテンプレートの取得
				$mdlmt = new TableMailTemplate($this->adapter);
				$template = $mdlmt->findMailTemplate($tmpNumber, $edata['OemId'])->current();

				if(!$template) throw new \Exception('メールテンプレートが存在しません');

				// 注文商品情報の取得
				$mdloi = new TableOrderItems($this->adapter);
				$item = $mdloi->getOneItemName($oseq);

				// ショップメールアドレス
				$toAddresses = explode(",", $edata['MailAddress']);

				// メールの構築
				$body = mb_ereg_replace('{SiteNameKj}', $oc['SiteNameKj'], $template['Body']);//サイト名
				$body = mb_ereg_replace('{CustomerNameKj}', $oc['NameKj'], $body);//購入者氏名
				$body = mb_ereg_replace('{OneOrderItem}', $item, $body);
				$body = mb_ereg_replace('{ContactPhoneNumber}', $edata['ContactPhoneNumber'], $body);
				$body = mb_ereg_replace('{MailAddress}', $toAddresses[0], $body);

                $this->insertInfoParamServer($body, $oc['EnterpriseId'], $oc['OemId']);
                $this->insertInfoParamServer($template['FromTitle'], $oc['EnterpriseId'], $oc['OemId']);
                $this->insertInfoParamServer($template['FromAddress'], $oc['EnterpriseId'], $oc['OemId']);
                $this->insertInfoParamServer($template['Subject'], $oc['EnterpriseId'], $oc['OemId']);

                $orderId = $oc['OrderId'];
                $this->insertOrderId($body, $orderId);
                $this->insertOrderId($template['Subject'], $orderId);

				// メール送信履歴登録
				$mailSendSeq = $this->history->saveNew(array(
				        'MailTemplateId' => $tmpNumber,
				        'OrderSeq' => $oseq,
				        'EnterpriseId' => null,
				        'ManCustId' => null,
				        'ToAddress' => $oc['MailAddress'],
				        'CcAddress' => null,
				        'BccAddress' => null,
				        'Subject' => $template['Subject'],
				        'Body' => $body,
				        'MailSendDate' => date('Y-m-d H:i:s'),
				        'ErrFlg' => 0,
				        'ErrReason' => null,
				        'RegistId' => $userId,
				        'UpdateId' => $userId,
				        'ValidFlg' => 1,
				));
				$this->sendDone(
						$template['FromTitle'],
						$template['FromAddress'],
						$oc['NameKj'] . '様',
						$oc['MailAddress'],
						$template['Subject'],
						$body
				);
			}
		} catch(\Exception $e) {
		    if (isset($mailSendSeq)) {
		        // メール送信履歴を登録した場合、エラー理由を更新
		        $this->history->saveUpdate(array(
		                'ErrFlg' => 1,
		                'ErrReason' => $e->getMessage(),
		                'UpdateId' => $userId,
		        ), $mailSendSeq);
		    }
			throw new CoralMailException( 'cannot sent mail about Credit Judge Result.' . $e->getMessage(), 0, $e );
		}

		return $resultData;
	}

	/**
	 * 与信待ちメールを送信する
	 *
	 * @param array $orderInfo
	 * @param int $userId ユーザーID
	 * @return array
	 */
	public function SendWaitingCjMail($orderIdArray, $userId)
	{
	    try {
	        // 送信先アドレスをシステムプロパティから取得
	        $mdlcm = new TableCode($this->adapter);
	        $toMailAddress = $mdlcm->find(209, 1)->current()['Note'];

	        //空白除去
	        $toMailAddress = str_replace(array(' ','　'), '', $toMailAddress);

	        // 配列に変換
	        $toMailAddress = explode(',', $toMailAddress);

	        //空の値を除外
	        foreach ($toMailAddress as $key => $val){
	            if(empty($val)){
	                unset($toMailAddress[$key]);
	            }
	        }

	        // 文字列に変換
	        $toMailAddress = implode(',', $toMailAddress);

	        //送信先メールアドレスが空なら終了
          if(empty($toMailAddress)) {
              echo ('empty toMailAddress'.PHP_EOL);
              return;
          }

          $toName = ''; //名称なし

          // メールテンプレートの取得
          $mdlmt = new TableMailTemplate($this->adapter);
          $template = $mdlmt->findMailTemplate(110, 0)->current();

          if(!$template) throw new \Exception('メールテンプレートが存在しません');

          //取得した注文をメールの文言に変換
          $orderStr = implode(PHP_EOL, $orderIdArray);

          // メールの構築
          $body = mb_ereg_replace('{OrderList}', $orderStr, $template['Body']);

          // メール送信履歴登録
          $mailSendSeq = $this->history->saveNew(array(
                  'MailTemplateId' => $tmpNumber,
                  'OrderSeq' => null,
                  'EnterpriseId' => null,
                  'ManCustId' => null,
                  'ToAddress' => $toMailAddress,
                  'CcAddress' => null,
                  'BccAddress' => null,
                  'Subject' => $template['Subject'],
                  'Body' => $body,
                  'MailSendDate' => date('Y-m-d H:i:s'),
                  'ErrFlg' => 0,
                  'ErrReason' => null,
                  'RegistId' => $userId,
                  'UpdateId' => $userId,
                  'ValidFlg' => 1,
          ));
          //メール送信実行
          $this->sendDone(
              $template['FromTitle'],
              $template['FromAddress'],
              $toName,
              $toMailAddress,
              $template['Subject'],
              $body
          );
	    } catch(\Exception $e) {
	        if (isset($mailSendSeq)) {
	            // メール送信履歴を登録した場合、エラー理由を更新
	            $this->history->saveUpdate(array(
	                    'ErrFlg' => 1,
	                    'ErrReason' => $e->getMessage(),
	                    'UpdateId' => $userId,
	            ), $mailSendSeq);
	        }
	        throw new CoralMailException( 'cannot sent mail about waiting order.' . $e->getMessage(), 0, $e );
	    }

	}

	/**
	 * 顧客マイページ退会完了
	 *
	 * @param int $customerId
	 * @param int $userId
	 * @param bool $ismobile
	 * @throws \Exception
	 * @throws CoralMailException
	 */
	public function SendWithdrawMail( $customerId, $userId, $ismobile )
	{
	    try
	    {
	        $myPageHistory = new MypageViewMailSendHistory($this->adapter);
	        $mvmc = new TableMypageCustomer( $this->adapter );

	        //顧客情報取得
	        $customerInfo = $mvmc->find( $customerId )->current();

	        // メールテンプレートの取得
	        $mdlmt = new MypageViewMailTemplate($this->adapter);
	        if ( $this->isCelAddress($customerInfo['MailAddress']) )
	        {
	            $templateId = 60;
	        } else {
	            $templateId = 59;
	        }
	        $template = $mdlmt->findMailTemplate($templateId, $customerInfo['OemId'])->current();

	        if(!$template) throw new \Exception('メールテンプレートが存在しません');

	        // メールの構築
	        $body = $template['Body'];
	        $body = mb_ereg_replace('{MyPageNameKj}', ($customerInfo['NameSeiKj'] . '　' .  $customerInfo['NameMeiKj']), $body);
	        // メール送信履歴登録
	        $mailSendSeq = $myPageHistory->saveNew(array(
	                'MailTemplateId' => $templateId,
	                'OrderSeq' => null,
	                'EnterpriseId' => null,
	                'ToAddress' => $customerInfo['MailAddress'],
	                'CcAddress' => null,
	                'BccAddress' => null,
	                'Subject' => $template['Subject'],
	                'Body' => $body,
	                'MailSendDate' => date('Y-m-d H:i:s'),
	                'ErrFlg' => 0,
	                'ErrReason' => null,
	                'RegistId' => $userId,
	                'UpdateId' => $userId,
	                'ValidFlg' => 1,
	        ));
	        $this->sendDone(
	        $template['FromTitle'],
	        $template['FromAddress'],
	        $customerInfo['NameSeiKj']. $customerInfo['NameMeiKj'] . '様',
	        $customerInfo['MailAddress'],
	        $template['Subject'],
	        $body
	        );

	    } catch(\Exception $e) {
	        if (isset($mailSendSeq)) {
	            // メール送信履歴を登録した場合、エラー理由を更新
	            $myPageHistory->saveUpdate(array(
	                    'ErrFlg' => 1,
	                    'ErrReason' => $e->getMessage(),
	                    'UpdateId' => $userId,
	            ), $mailSendSeq);
	        };
	        throw new CoralMailException( 'cannot sent mail for customer about withdraw.' . $e->getMessage(), 0, $e );
	    }
	}

	/**
	 * 身分証明書アップロード申請メールの送信
	 *
	 * @param array $maildata
	 * @param int $userId
	 * @return boolean
	 */
	public function SendIDUploadMail( $maildata, $userId, $ismobile )
	{
	    try
	    {
	        $myPageHistory = new MypageViewMailSendHistory($this->adapter);

	        if($this->isCelAddress($maildata['MailAddress'])){
	            $class = 85;
	        }else{
	            $class = 84;
	        }

	        // メールテンプレートの取得
	        $mdlmt = new MypageViewMailTemplate($this->adapter);
	        $template = $mdlmt->findMailTemplate($class, 0)->current();

	        if(!$template) throw new \Exception('メールテンプレートが存在しません');

	        // メールの構築
	        $body = $template['Body'];

	        // メール送信履歴登録
	        $mailSendSeq = $myPageHistory->saveNew(array(
	                'MailTemplateId' => $class,
	                'OrderSeq' => null,
	                'EnterpriseId' => null,
	                'ToAddress' => $maildata['MailAddress'],
	                'CcAddress' => null,
	                'BccAddress' => null,
	                'Subject' => $template['Subject'],
	                'Body' => $body,
	                'MailSendDate' => date('Y-m-d H:i:s'),
	                'ErrFlg' => 0,
	                'ErrReason' => null,
	                'RegistId' => $userId,
	                'UpdateId' => $userId,
	                'ValidFlg' => 1,
	        ));

	        $this->sendDone(
	        $template['FromTitle'],
	        $template['FromAddress'],
	        $maildata['Name'] . '様',
	        $maildata['MailAddress'],
	        $template['Subject'],
	        $body
	        );
	    } catch(\Exception $e) {
	        if (isset($mailSendSeq)) {
	            // メール送信履歴を登録した場合、エラー理由を更新
	            $myPageHistory->saveUpdate(array(
	                    'ErrFlg' => 1,
	                    'ErrReason' => $e->getMessage(),
	                    'UpdateId' => $userId,
	            ), $mailSendSeq);
	        };
	        throw new CoralMailException( 'cannot sent mail for customer about ID upload.' . $e->getMessage(), 0, $e );
	    }
	}

	/**
	 * 過剰入金メールを送信する。
	 *
	 * @param int $oseq 注文SEQ
	 * @param int $userId ユーザーID
	 */
	public function SendOverPaymentMail($oseq, $userId)
	{
	    try {
	        // V_OrderCustomer に T_ReceiptControl を結合して顧客情報を取得する。
	        $sql = <<<EOQ
SELECT  voc.*
FROM    V_OrderCustomer voc
WHERE   voc.OrderSeq = :OrderSeq
;
EOQ;

	        // 顧客情報の取得
	        $oc = $this->adapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current();

	        if (CoralValidate::isNotEmpty($oc['MailAddress']))
	        {

	            // 使用すべきテンプレートの判定
	            if ($this->isCelAddress($oc['MailAddress']))
	            {
	                $tmpNumber = 35;
	            }
	            else
	            {
	                $tmpNumber = 34;
	            }

	            // メールテンプレートの取得
	            $mdlmt = new TableMailTemplate($this->adapter);
	            $template = $mdlmt->findMailTemplate($tmpNumber, $oc['OemId'])->current();

	            $body = $template['Body'];

                $sql  = " SELECT c.NameKj ";
                $sql .= " ,      DATE_FORMAT(rc.ReceiptProcessDate, '%c月%e日') AS ReceiptProcessDate ";
                $sql .= " ,      (CASE rc.ReceiptClass WHEN 1 THEN 'コンビニ' WHEN 2 THEN '郵便局' WHEN 3 THEN '銀行' WHEN 4 THEN 'LINE Pay' ELSE '' END) AS ReceiptClass ";
                $sql .= " ,      (cc.ReceiptAmountTotal - cc.ClaimAmount) AS OverReceiptAmount ";
                $sql .= " ,      ent.ContactPhoneNumber ";
                $sql .= " FROM   T_Order o ";
                $sql .= "        INNER JOIN T_Customer c ON (c.OrderSeq = o.OrderSeq) ";
                $sql .= "        INNER JOIN T_ClaimControl cc ON (cc.OrderSeq = o.P_OrderSeq) ";
                $sql .= "        INNER JOIN T_ReceiptControl rc ON (rc.ReceiptSeq = cc.LastReceiptSeq) ";
                $sql .= "        INNER JOIN T_Enterprise ent ON (ent.EnterpriseId = o.EnterpriseId) ";
                $sql .= " WHERE  o.OrderSeq = :OrderSeq ";
                $row = $this->adapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current();

                $body = mb_ereg_replace('{CustomerNameKj}', $row['NameKj'], $body);
                $body = mb_ereg_replace('{SiteNameKj}', $oc['SiteNameKj'], $body);
                $body = mb_ereg_replace('{ReceiptDate}', $row['ReceiptProcessDate'], $body);
                $body = mb_ereg_replace('{ReceiptClass}', $row['ReceiptClass'], $body);
                $body = mb_ereg_replace('{OverReceiptAmount}', $row['OverReceiptAmount'], $body);
                $body = mb_ereg_replace('{Phone}', $row['ContactPhoneNumber'], $body);

                $this->insertInfoParamServer($body, $oc['EnterpriseId'], $oc['OemId']);
                $this->insertInfoParamServer($template['FromTitle'], $oc['EnterpriseId'], $oc['OemId']);
                $this->insertInfoParamServer($template['FromAddress'], $oc['EnterpriseId'], $oc['OemId']);
                $this->insertInfoParamServer($template['Subject'], $oc['EnterpriseId'], $oc['OemId']);

                $orderId = $oc['OrderId'];
                $this->insertOrderId($body, $orderId);
                $this->insertOrderId($template['Subject'], $orderId);

	            // メール送信履歴登録
	            $mailSendSeq = $this->history->saveNew(array(
	                    'MailTemplateId' => $tmpNumber,
	                    'OrderSeq' => $oc['OrderSeq'],
	                    'EnterpriseId' => null,
	                    'ManCustId' => null,
	                    'ToAddress' => $oc['MailAddress'],
	                    'CcAddress' => null,
	                    'BccAddress' => null,
	                    'Subject' => $template['Subject'],
	                    'Body' => $body,
	                    'MailSendDate' => date('Y-m-d H:i:s'),
	                    'ErrFlg' => 0,
	                    'ErrReason' => null,
	                    'RegistId' => $userId,
	                    'UpdateId' => $userId,
	                    'ValidFlg' => 1,
	            ));
	            $this->sendDone(
                    $template['FromTitle'],
                    $template['FromAddress'],
                    $oc['NameKj'] . '　様',
                    $oc['MailAddress'],
                    $template['Subject'],
                    $body
	            );
	        }
	    } catch(\Exception $e) {
	        if (isset($mailSendSeq)) {
	            // メール送信履歴を登録した場合、エラー理由を更新
	            $this->history->saveUpdate(array(
	                    'ErrFlg' => 1,
	                    'ErrReason' => $e->getMessage(),
	                    'UpdateId' => $userId,
	            ), $mailSendSeq);
	        }
	        throw new CoralMailException( 'cannot sent receipt confirmed mail.' . $e->getMessage(), 0, $e );
	    }
	}

	/**
	 * 与信結果メール送信の是非確認
	 * @param int $occReason
	 * @param int $entCjMailMode
	 * @return boolean
	 */
	private function canSendCjMail($occReason, $entCjMailMode)
	{
	    $d[0][1] = false;
		$d[0][2] = false;
		$d[0][3] = false;
		$d[0][4] = false;
		$d[0][5] = false;
		$d[0][6] = false;
		$d[1][1] = true;
		$d[1][2] = false;
		$d[1][3] = false;
		$d[1][4] = true;
		$d[1][5] = false;
		$d[1][6] = false;
		$d[2][1] = false;
		$d[2][2] = true;
		$d[2][3] = true;
		$d[2][4] = false;
		$d[2][5] = true;
		$d[2][6] = false;
		$d[3][1] = true;
		$d[3][2] = true;
		$d[3][3] = true;
		$d[3][4] = true;
		$d[3][5] = true;
		$d[3][6] = true;

		return $d[$entCjMailMode][$occReason];
	}

	/**
	 * キャラクターセットをメール用に変更する。
	 */
	private function toMailChar($str)
	{
		return mb_convert_encoding($str, 'ISO-2022-JP', 'UTF-8');
	}

	/**
	 * MIMEエンコード
	 *
	 * @param unknown_type $str
	 * @return unknown
	 */
	private function toMailCharMime($str)
	{
	    return mb_encode_mimeheader($this->toMailChar($str));
	}

	/**
	 * 携帯アドレスか否かをチェックする
	 *
	 * @param string $celAddress メールアドレス
	 * @return boolean
	 */
	private function isCelAddress($celAddress)
	{
	    $celAddresses = array('docomo.ne.jp','vodafone.ne.jp','ezweb.ne.jp','softbank.ne.jp','pdx.ne.jp','fishbone.tv');

		return BaseGeneralUtils::isMatchAddress($celAddress, $celAddresses);
	}

	/**
	 * 配送伝票番号相違通知メールの元データを検証する
	 *
	 * @param array $data
	 * @return array
	 */
	private function validateForSlipNumberDiffMail($data) {
        $errors = array();

        // OrderSeq
        $key = 'OrderSeq';
        if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
            $errors[$key] = "シーケンス値が取得できませんでした";
        }
        if (!isset($errors[$key]) && !is_numeric($data[$key])) {
            $errors[$key] = "シーケンス値が不正です";
        }

        // OrderId
        $key = 'OrderId';
        if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
            $errors[$key] = "注文IDが不正です";
        }
        if (!isset($errors[$key]) && !(strlen($data[$key]) <= 50)) {
            $errors[$key] = "注文IDが不正です";
        }

        // ReceiptOrderDate
        $key = 'ReceiptOrderDate';
        if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
            $errors[$key] = "注文日を取得できませんでした";
        }
        if (!isset($errors[$key]) && !IsValidFormatDate($data[$key])) {
            $errors[$key] = "注文日の形式が不正です";
        }

        // EnterpriseId
        $key = 'EnterpriseId';
        if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
            $errors[$key] = "事業者IDが取得できませんでした";
        }
        if (!isset($errors[$key]) && !is_numeric($data[$key])) {
            $errors[$key] = "事業者IDが不正です";
        }

        // EnterpriseNameKj
        $key = 'EnterpriseNameKj';
        if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
            $errors[$key] = "事業者名が不正です";
        }
        if (!isset($errors[$key]) && !(strlen($data[$key]) <= 160)) {
            $errors[$key] = "事業者名が不正です";
        }

        // CpNameKj
        $key = 'CpNameKj';
        if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
            $errors[$key] = "担当者名が不正です";
        }
        if (!isset($errors[$key]) && !(strlen($data[$key]) <= 160)) {
            $errors[$key] = "担当者名が不正です";
        }

        // MailAddress
        $key = 'MailAddress';
        if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
            $errors[$key] = "メールアドレスが取得できませんでした";
        }
        $cvmm = new CoralValidateMultiMail();
        if (!isset($errors[$key]) && !$cvmm->isValid($data[$key])) {
            $errors[$key] = "メールアドレスが不正な形式です";
        }

        // NameKj（検証しない）

        // Deli_JournalIncDate
        $key = 'Deli_JournalIncDate';
        if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
            $errors[$key] = "伝票登録日を取得できませんでした";
        }
        if (!isset($errors[$key]) && !IsValidFormatDate($data[$key])) {
            $errors[$key] = "伝票登録日の形式が不正です";
        }

        // Deli_JournalNumber
        $key = 'Deli_JournalNumber';
        if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
            $errors[$key] = "伝票番号が不正です";
        }
        if (!isset($errors[$key]) && !(strlen($data[$key]) <= 255)) {
            $errors[$key] = "伝票番号が不正です";
        }

        return $errors;
	}

    /**
     * 与信完了メールを加盟店担当者に送信
     *
     * @param array $maildata 加盟店送信情報
     * @param int $userId ユーザーID
     *
     */
    public function SendCjMailToEnt($maildata, $userId) {
        try
        {
            // メールテンプレートの取得
            $mdlmt = new TableMailTemplate($this->adapter);
            $template = $mdlmt->findMailTemplate(3, $maildata['OemId'])->current();

            if(!$template) throw new \Exception('メールテンプレートが存在しません');

            // メールの構築
            $subject = mb_ereg_replace('{CreditCount}', $maildata['OrderCnt'], $template['Subject']);
            $subject = mb_ereg_replace('{NgCount}', $maildata['NgCnt'], $subject);

            $body = mb_ereg_replace('{EnterpriseNameKj}', $maildata['CpNameKj'], $template['Body']);   // 加盟店担当者名
            $body = mb_ereg_replace('{CreditCount}', $maildata['OrderCnt'], $body);                    // 注文数
            $body = mb_ereg_replace('{Orders}', $maildata['OrderList'], $body);                        // 注文ＩＤリスト
            // メール送信履歴登録
            $mailSendSeq = $this->history->saveNew(array(
                    'MailTemplateId' => 3,
                    'OrderSeq' => null,
                    'EnterpriseId' => $maildata['EnterpriseId'],
                    'ManCustId' => null,
                    'ToAddress' => $maildata['MailAddress'],
                    'CcAddress' => null,
                    'BccAddress' => null,
                    'Subject' => $subject,
                    'Body' => $body,
                    'MailSendDate' => date('Y-m-d H:i:s'),
                    'ErrFlg' => 0,
                    'ErrReason' => null,
                    'RegistId' => $userId,
                    'UpdateId' => $userId,
                    'ValidFlg' => 1,
            ));
            $this->sendDone(
                $template['FromTitle'],
                $template['FromAddress'],
                $maildata['CpNameKj'] . '様',
                $maildata['MailAddress'],
                $subject,
                $body
            );
        } catch(\Exception $e) {
            if (isset($mailSendSeq)) {
                // メール送信履歴を登録した場合、エラー理由を更新
                $this->history->saveUpdate(array(
                        'ErrFlg' => 1,
                        'ErrReason' => $e->getMessage(),
                        'UpdateId' => $userId,
                ), $mailSendSeq);
            }
            throw new CoralMailException( 'cannot sent mail about Credit Judge Result.' . $e->getMessage(), 0, $e );
        }
    }

    /**
     * 指定のメールテンプレートに請求口座情報を展開する
     *
     * @access protected
     * @param string $body メールテンプレート本文
     * @param int $oseq 注文SEQ
     * @return string 請求口座情報を展開したメールテンプレート本文
     */
    protected function applyClaimAccounts($body, $oseq)
    {
        // 請求口座情報を取得
        $accounts = $this->getClaimAccountByOrderSeq($oseq);

        // 口座の取得ができなかったら何もしない
        if(!$accounts) return $body;

        // テンプレート変数の展開
        $variables = array(
                'Bk_BankCode',
                'Bk_BranchCode',
                'Bk_BankName',
                'Bk_BranchName',
                'Bk_DepositClass',
                'Bk_AccountNumber',
                'Bk_AccountHolder',
                'Bk_AccountHolderKn',
                'Yu_SubscriberName',
                'Yu_AccountNumber'
        );
        foreach($variables as $var)
        {
            // テンプレート変数'Bk_DepositClass'は口座情報のBk_DepositClassNameに読み替える
            if($var == 'Bk_DepositClass') $var .= 'Name';
            $ptn = sprintf('{%s}', $var);
            if(isset($accounts[$var]))
            {
                try
                {
                    $val = $accounts[$var];
                    if($var == 'Yu_AccountNumber')
                    {
                        // ゆうちょ口座は5桁-1桁-6桁形式でフォーマット
                        $val = sprintf('%s-%s-%s', substr($val, 0, 5), substr($val, 5, 1), substr($val, 6));
                    }
                    $body = mb_ereg_replace($ptn, $val, $body);
                }
                catch(\Exception $err)
                {
                    // 例外は無視
                }
            }
        }
        return $body;
    }

    /**
     * 指定注文の請求口座情報を取得する
     *
     * @access protected
     * @param int $oseq 注文SEQ
     * @return array | null
     */
    protected function getClaimAccountByOrderSeq($oseq)
    {
        $logic = new \models\Logic\LogicOemClaimAccount($this->adapter);

        $oseq = (int)$oseq;

        // 最終請求口座情報の取得を試みる
        $result = $logic->findLastInformedClaimAccountInfo($oseq);
        if($result) return $result;

        // 最終請求口座の取得に失敗したのでOEM向けに設定されている固定口座情報を返す
        return $logic->findFixedClaimAccountIfo($oseq);
    }


    /**
     * 請求取りまとめエラーメールを加盟店に送信する
     *
     * @param array $maildata 加盟店送信情報
     * @param int $userId ユーザーID
     *
     */
    public function SendCombClaimMailToEnt($maildata, $userId) {
        try
        {
            // メールテンプレートの取得
            $mdlmt = new TableMailTemplate($this->adapter);
            $template = $mdlmt->findMailTemplate(52, $maildata['OemId'])->current();

            if(!$template) throw new \Exception('メールテンプレートが存在しません');

            // メールの構築
            $body = mb_ereg_replace('{EnterpriseNameKj}', $maildata['CpNameKj'], $template['Body']);   // 加盟店担当者名
            $body = mb_ereg_replace('{OrderSummary}', $maildata['OrderList'], $body);                  // 注文ＩＤリスト
            $body = mb_ereg_replace('{Error}', $maildata['ErrorMsgs'], $body);                         // 理由


            // メール送信履歴登録
            $mailSendSeq = $this->history->saveNew(array(
                    'MailTemplateId' => 52,
                    'OrderSeq' => null,
                    'EnterpriseId' => $maildata['EnterpriseId'],
                    'ManCustId' => null,
                    'ToAddress' => $maildata['MailAddress'],
                    'CcAddress' => null,
                    'BccAddress' => null,
                    'Subject' => $template['Subject'],
                    'Body' => $body,
                    'MailSendDate' => date('Y-m-d H:i:s'),
                    'ErrFlg' => 0,
                    'ErrReason' => null,
                    'RegistId' => $userId,
                    'UpdateId' => $userId,
                    'ValidFlg' => 1,
            ));
            $this->sendDone(
            $template['FromTitle'],
            $template['FromAddress'],
            $maildata['CpNameKj'] . '様',
            $maildata['MailAddress'],
            $template['Subject'],
            $body
            );
        } catch(\Exception $e) {
            if (isset($mailSendSeq)) {
                // メール送信履歴を登録した場合、エラー理由を更新
                $this->history->saveUpdate(array(
                        'ErrFlg' => 1,
                        'ErrReason' => $e->getMessage(),
                        'UpdateId' => $userId,
                ), $mailSendSeq);
            }
            throw new CoralMailException( 'cannot sent mail for ent about claim combined.' . $e->getMessage(), 0, $e );
        }
    }

    /**
     * 請求取りまとめエラーメールをCBに送信する
     *
     * @param array $maildata 加盟店送信情報
     * @param int $userId ユーザーID
     *
     */
    public function SendCombClaimMailToCb($maildata, $userId) {
        try
        {
            // メールテンプレートの取得
            $mdlmt = new TableMailTemplate($this->adapter);
            $template = $mdlmt->findMailTemplate(51, 0)->current();

            if(!$template) throw new \Exception('メールテンプレートが存在しません');

            // メールの構築
            $body = mb_ereg_replace('{EnterpriseNameKj}', $maildata['CpNameKj'], $template['Body']);   // 加盟店担当者名
            $body = mb_ereg_replace('{OrderSummary}', $maildata['OrderList'], $body);                  // 注文ＩＤリスト
            $body = mb_ereg_replace('{Error}', $maildata['ErrorMsgs'], $body);                         // 理由

            // メール送信履歴登録
            $mailSendSeq = $this->history->saveNew(array(
                    'MailTemplateId' => 51,
                    'OrderSeq' => null,
                    'EnterpriseId' => $maildata['EnterpriseId'],
                    'ManCustId' => null,
                    'ToAddress' => $template['ToAddress'],
                    'CcAddress' => null,
                    'BccAddress' => null,
                    'Subject' => $template['Subject'],
                    'Body' => $body,
                    'MailSendDate' => date('Y-m-d H:i:s'),
                    'ErrFlg' => 0,
                    'ErrReason' => null,
                    'RegistId' => $userId,
                    'UpdateId' => $userId,
                    'ValidFlg' => 1,
            ));
            $this->sendDone(
            $template['FromTitle'],
            $template['FromAddress'],
            $maildata['CpNameKj'] . '様',
            $template['ToAddress'],
            $template['Subject'],
            $body
            );
        } catch(\Exception $e) {
            if (isset($mailSendSeq)) {
                // メール送信履歴を登録した場合、エラー理由を更新
                $this->history->saveUpdate(array(
                        'ErrFlg' => 1,
                        'ErrReason' => $e->getMessage(),
                        'UpdateId' => $userId,
                ), $mailSendSeq);
            };
            throw new CoralMailException( 'cannot sent mail for cb about claim combined.' . $e->getMessage(), 0, $e );
        }
    }

    /**
     * 不足入金連絡メールを送信する
     *
     * @param int $oseq
     * @param int $userId ユーザーID
     */
    public function SendLackOfPay($oseq, $userId)
    {
        try {
            // 顧客情報の取得
            $mdloc = new ViewOrderCustomer($this->adapter);
            $oc = $mdloc->find($oseq)->current();

            if (CoralValidate::isNotEmpty($oc['MailAddress']))
            {
                // 使用すべきテンプレートの判定
                if ($this->isCelAddress($oc['MailAddress']))
                {
                    $tmpNumber = 82;
                }
                else
                {
                    $tmpNumber = 81;
                }

                // 事業者情報の取得
                $mdle = new TableEnterprise($this->adapter);
                $edata = $mdle->findEnterprise($oc['EnterpriseId'])->current();

                // メールテンプレートの取得
                $mdlmt = new TableMailTemplate($this->adapter);
                $template = $mdlmt->findMailTemplate($tmpNumber, $oc['OemId'])->current();

                if(!$template) {
                    throw new CoralMailException('メールテンプレートが存在しません。設定を見直してください');
                }

                // メールの構築
                $body = $template['Body'];

                $sql  = " SELECT c.NameKj ";
                $sql .= " ,      DATE_FORMAT(rc.ReceiptProcessDate, '%c月%e日') AS ReceiptProcessDate ";
                $sql .= " ,      (CASE rc.ReceiptClass WHEN 1 THEN 'コンビニ' WHEN 2 THEN '郵便局' WHEN 3 THEN '銀行' WHEN 4 THEN 'LINE Pay' ELSE '' END) AS ReceiptClass ";
                $sql .= " ,      rc.ReceiptAmount ";
                $sql .= " ,      cc.ClaimedBalance ";
                $sql .= " FROM   T_Order o ";
                $sql .= "        INNER JOIN T_Customer c ON (c.OrderSeq = o.OrderSeq) ";
                $sql .= "        INNER JOIN T_ClaimControl cc ON (cc.OrderSeq = o.P_OrderSeq) ";
                $sql .= "        INNER JOIN T_ReceiptControl rc ON (rc.ReceiptSeq = cc.LastReceiptSeq) ";
                $sql .= " WHERE  o.OrderSeq = :OrderSeq ";
                $row = $this->adapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current();

                $body = mb_ereg_replace('{CustomerNameKj}', $row['NameKj'], $body);
                $body = mb_ereg_replace('{SiteNameKj}', $oc['SiteNameKj'], $body);
                $body = mb_ereg_replace('{ReceiptDate}', $row['ReceiptProcessDate'], $body);
                $body = mb_ereg_replace('{ReceiptClass}', $row['ReceiptClass'], $body);
                $body = mb_ereg_replace('{UseAmount}', $row['ReceiptAmount'], $body);
                $body = mb_ereg_replace('{ShortfallAmount}', $row['ClaimedBalance'], $body);
                $body = mb_ereg_replace('{Phone}', $edata['ContactPhoneNumber'], $body);

                $this->insertInfoParamServer($body, $oc['EnterpriseId'], $oc['OemId']);
                $this->insertInfoParamServer($template['FromTitle'], $oc['EnterpriseId'], $oc['OemId']);
                $this->insertInfoParamServer($template['FromAddress'], $oc['EnterpriseId'], $oc['OemId']);
                $this->insertInfoParamServer($template['Subject'], $oc['EnterpriseId'], $oc['OemId']);

                $orderId = $oc['OrderId'];
                $this->insertOrderId($body, $orderId);
                $this->insertOrderId($template['Subject'], $orderId);

                // メール送信履歴登録
                $mailSendSeq = $this->history->saveNew(array(
                        'MailTemplateId' => $tmpNumber,
                        'OrderSeq' => $oseq,
                        'EnterpriseId' => $oc['EnterpriseId'],
                        'ManCustId' => null,
                        'ToAddress' => $oc['MailAddress'],
                        'CcAddress' => null,
                        'BccAddress' => null,
                        'Subject' => $template['Subject'],
                        'Body' => $body,
                        'MailSendDate' => date('Y-m-d H:i:s'),
                        'ErrFlg' => 0,
                        'ErrReason' => null,
                        'RegistId' => $userId,
                        'UpdateId' => $userId,
                        'ValidFlg' => 1,
                ));
                $this->sendDone(
                        $template['FromTitle'],
                        $template['FromAddress'],
                        $oc['NameKj'] . '　様',
                        $oc['MailAddress'],
                        $template['Subject'],
                        $body
                );
            }
        } catch(\Exception $e) {
            if (isset($mailSendSeq)) {
                // メール送信履歴を登録した場合、エラー理由を更新
                $this->history->saveUpdate(array(
                        'ErrFlg' => 1,
                        'ErrReason' => $e->getMessage(),
                        'UpdateId' => $userId,
                ), $mailSendSeq);
            }
            throw new CoralMailException( 'cannot sent mail about lack of paying.' . $e->getMessage(), 0, $e );
        }
    }

    /**
     * マイページ仮登録完了メールをCBに送信する
     *
     * @param array $mail メール情報
     * @param int $userId ユーザーID
     *
     */
    public function SendPreregistMail($mail, $baseUrl, $userId) {
        try
        {
            $myPageHistory = new MypageViewMailSendHistory($this->adapter);

            // メールテンプレートの取得
            $mdlmt = new MypageViewMailTemplate($this->adapter);
            if($this->isCelAddress($mail['MailAddress'])){
                $class = 54;
            }else{
                $class = 53;
            }
            $template = $mdlmt->findMailTemplate($class, $mail['OemId'])->current();

            if(!$template) throw new \Exception('メールテンプレートが存在しません');

            // メールの構築
            $body = $template['Body'];
            $body = mb_ereg_replace('{MypageRegistUrl}', ($baseUrl . '/regist/regist/token/' . $mail['UrlParameter']), $body);

            // メール送信履歴登録
            $mailSendSeq = $myPageHistory->saveNew(array(
                    'MailTemplateId' => $class,
                    'OrderSeq' => $mail['OrderSeq'],
                    'EnterpriseId' => null,
                    'ToAddress' => $mail['MailAddress'],
                    'CcAddress' => null,
                    'BccAddress' => null,
                    'Subject' => $template['Subject'],
                    'Body' => $body,
                    'MailSendDate' => date('Y-m-d H:i:s'),
                    'ErrFlg' => 0,
                    'ErrReason' => null,
                    'RegistId' => $userId,
                    'UpdateId' => $userId,
                    'ValidFlg' => 1,
            ));
            $this->sendDone(
            $template['FromTitle'],
            $template['FromAddress'],
            $mail['MailAddress'] . '様',
            $mail['MailAddress'],
            $template['Subject'],
            $body
            );
        } catch(\Exception $e) {
            if (isset($mailSendSeq)) {
                // メール送信履歴を登録した場合、エラー理由を更新
                $myPageHistory->saveUpdate(array(
                        'ErrFlg' => 1,
                        'ErrReason' => $e->getMessage(),
                        'UpdateId' => $userId,
                ), $mailSendSeq);
            };
            throw new CoralMailException( 'cannot sent mail for customer about preregist.' . $e->getMessage(), 0, $e );
        }
    }

    /**
     * マイページ本登録完了メールを送信する、会員情報編集完了メールに送信
     *
     * @param array $mail メール情報
     * @param int $userId ユーザーID
     * @param bool $ismobile 端末情報（ＰＣ端、スマートフォン端）
     */
    public function SendRegistMail($mail, $userId, $ismobile) {
        try
        {
            $myPageHistory = new MypageViewMailSendHistory($this->adapter);

            // メールテンプレートの取得
            $mdlmt = new MypageViewMailTemplate($this->adapter);
            if($this->isCelAddress($mail['MailAddress'])){
                $class = 56;
            }else{
                $class = 55;
            }
            $template = $mdlmt->findMailTemplate($class, $mail['OemId'])->current();

            if(!$template) throw new \Exception('メールテンプレートが存在しません');

            // メールの構築
            $body = $template['Body'];

            $body = mb_ereg_replace('{MyPageNameKj}', ($mail['NameSeiKj'] . '　' .  $mail['NameMeiKj']), $body);

            // メール送信履歴登録
            $mailSendSeq = $myPageHistory->saveNew(array(
                    'MailTemplateId' => $class,
                    'OrderSeq' => $mail['OrderSeq'],
                    'EnterpriseId' => null,
                    'ToAddress' => $mail['MailAddress'],
                    'CcAddress' => null,
                    'BccAddress' => null,
                    'Subject' => $template['Subject'],
                    'Body' => $body,
                    'MailSendDate' => date('Y-m-d H:i:s'),
                    'ErrFlg' => 0,
                    'ErrReason' => null,
                    'RegistId' => $userId,
                    'UpdateId' => $userId,
                    'ValidFlg' => 1,
            ));
            $this->sendDone(
            $template['FromTitle'],
            $template['FromAddress'],
            $mail['NameSeiKj'] . $mail['NameMeiKj'] . '様',
            $mail['MailAddress'],
            $template['Subject'],
            $body
            );
        } catch(\Exception $e) {
            if (isset($mailSendSeq)) {
                // メール送信履歴を登録した場合、エラー理由を更新
                $myPageHistory->saveUpdate(array(
                        'ErrFlg' => 1,
                        'ErrReason' => $e->getMessage(),
                        'UpdateId' => $userId,
                ), $mailSendSeq);
            };
            throw new CoralMailException( 'cannot sent mail.' . $e->getMessage(), 0, $e );
        }
    }

    /**
     * 顧客マイページログインパスワード変更完了送信処理
     *
     * @param int $customerId 顧客ＩＤ
     * @param int $userId ユーザ－ＩＤ
     * @param bool $ismobile 端末情報（ＰＣ端、スマートフォン端）
     */
    public function SendChangeMypagePwd ( $customerId, $userId, $ismobile )
    {
        try
        {
            // メールテンプレートの取得
            $mdlmt = new MypageViewMailTemplate( $this->adapter );

            // マイページ顧客情報取得
            $mdlmc = new TableMypageCustomer($this->adapter );
            $customerInfo = $mdlmc->find( $customerId )->current();

            if($this->isCelAddress($customerInfo['MailAddress'])){
                $templateId = 58;
            }else{
                $templateId = 57;
            }
            $template = $mdlmt->findMailTemplate( $templateId, $customerInfo['OemId'] )->current();

            if(!$template) throw new \Exception( 'メールテンプレートが存在しません' );

            // メールの構築
            $body = $template['Body'];

            $body = mb_ereg_replace('{MyPageNameKj}', ($customerInfo['NameSeiKj'] . '　' .  $customerInfo['NameMeiKj']), $body);

            // メール送信履歴登録
            $mdlhis = new MypageViewMailSendHistory( $this->adapter );
            $mailSendSeq = $mdlhis->saveNew( array(
                    'MailTemplateId' => $templateId,
                    'OrderSeq' => null,
                    'EnterpriseId' => null,
                    'ToAddress' => $customerInfo['MailAddress'],
                    'CcAddress' => null,
                    'BccAddress' => null,
                    'Subject' => $template['Subject'],
                    'Body' => $body,
                    'MailSendDate' => date('Y-m-d H:i:s'),
                    'ErrFlg' => 0,
                    'ErrReason' => null,
                    'RegistId' => $userId,
                    'UpdateId' => $userId,
                    'ValidFlg' => 1,
            ));
            $this->sendDone(
            $template['FromTitle'],
            $template['FromAddress'],
            $customerInfo['NameSeiKj']. $customerInfo['NameMeiKj'] . '様',
            $customerInfo['MailAddress'],
            $template['Subject'],
            $body
            );

        } catch ( \Exception $e ) {
            if ( isset( $mailSendSeq ) ) {
                // メール送信履歴を登録した場合、エラー理由を更新
                $mdlhis->saveUpdate( array(
                        'ErrFlg' => 1,
                        'ErrReason' => $e->getMessage(),
                        'UpdateId' => $userId,
                ), $mailSendSeq );
            };
            throw new CoralMailException( 'cannot sent mail for customer about change mypage login password. ' . $e->getMessage(), 0, $e );
        }
    }

    /**
     * 身分証画像ファイルはメールで身分証明書管理用PCのメールアドレスに送信する
     *
     * @param array $maildata
     * @param int $userId
     * @return boolean
     */
    public function SendIdPCUploadImgMail( $maildata, $userId )
    {
        try
        {
            $myPageHistory = new MypageViewMailSendHistory($this->adapter);


            // メールテンプレートの取得
            $mdlmt = new MypageViewMailTemplate($this->adapter);
            $template = $mdlmt->findMailTemplate(83, 0)->current();

            if(!$template) throw new \Exception('メールテンプレートが存在しません');

            // メールの構築
            $nowDate = date('YmdHis');

            // 件名
            $subject = "{date}_{CustomerId}_{NameSei}_{NameMei}";
            $subject = mb_ereg_replace('{date}', $nowDate, $subject);
            $subject = mb_ereg_replace('{CustomerId}', $maildata['CustomerId'], $subject);
            $subject = mb_ereg_replace('{NameSei}', $maildata['NameSei'], $subject);
            $subject = mb_ereg_replace('{NameMei}', $maildata['NameMei'], $subject);

            $bodycontent = $template['Body'];

            // 添付ファイル名称は件名と同期させること
            // 表面画像名
            $FrontFileName = "{date}_{CustomerId}_{NameSei}_{NameMei}_表.{FrontSuffixName}";
            // 裏面画像名
            $BackFileName = "{date}_{CustomerId}_{NameSei}_{NameMei}_裏.{BackSuffixName}";

            // 表面画像名
            $FrontFileName = mb_ereg_replace('{date}', $nowDate, $FrontFileName);
            $FrontFileName = mb_ereg_replace('{CustomerId}', $maildata['CustomerId'], $FrontFileName);
            $FrontFileName = mb_ereg_replace('{NameSei}', $maildata['NameSei'], $FrontFileName);
            $FrontFileName = mb_ereg_replace('{NameMei}', $maildata['NameMei'], $FrontFileName);
            $FrontFileName = mb_ereg_replace('{FrontSuffixName}', pathinfo($maildata['FrontImage']['name'], PATHINFO_EXTENSION), $FrontFileName);

            // 裏面画像名
            $BackFileName = mb_ereg_replace('{date}', $nowDate, $BackFileName);
            $BackFileName = mb_ereg_replace('{CustomerId}', $maildata['CustomerId'], $BackFileName);
            $BackFileName = mb_ereg_replace('{NameSei}', $maildata['NameSei'], $BackFileName);
            $BackFileName = mb_ereg_replace('{NameMei}', $maildata['NameMei'], $BackFileName);
            $BackFileName = mb_ereg_replace('{BackSuffixName}', pathinfo($maildata['BackImage']['name'], PATHINFO_EXTENSION), $BackFileName);

            $text = new MimePart($this->toMailChar(mb_convert_kana($bodycontent, 'K', 'UTF-8')));
            $text->type = "text/plain";


            $fdFront = fopen( $maildata['FrontImage']['tmp_name'],'r');
            $contentsFront = fread($fdFront, filesize($maildata['FrontImage']['tmp_name']));
            fclose($fdFront);
            $image1 = new MimePart($contentsFront);
            $image1->type = $maildata['FrontImage']['type'];
            $image1->filename = mb_encode_mimeheader($FrontFileName, 'ISO-2022-JP', 'Q');
            $image1->encoding = 'base64';
            $image1->disposition = 'attachment';

            if(!empty($maildata['BackImage']['tmp_name'])){
                $fdBack = fopen( $maildata['BackImage']['tmp_name'],'r');
                $contentsBack = fread($fdBack, filesize($maildata['BackImage']['tmp_name']));
                fclose($fdBack);
                $image2 = new MimePart($contentsBack);
                $image2->type = $maildata['BackImage']['type'];
                $image2->filename = mb_encode_mimeheader($BackFileName, 'ISO-2022-JP', 'Q');
                $image2->encoding = 'base64';
                $image2->disposition = 'attachment';
            }

            $body = new MimeMessage();
            if(empty($maildata['BackImage']['tmp_name'])){
                $body->setParts(array( $text, $image1));
            }else{
                $body->setParts(array( $text, $image1, $image2));
            }

            // メール送信履歴登録
            $mailSendSeq = $myPageHistory->saveNew(array(
                    'MailTemplateId' => 83,
                    'OrderSeq' => null,
                    'EnterpriseId' => null,
                    'ToAddress' => $maildata['MailAddress'],
                    'CcAddress' => null,
                    'BccAddress' => null,
                    'Subject' => $subject,
                    'Body' => $bodycontent,
                    'MailSendDate' => date('Y-m-d H:i:s'),
                    'ErrFlg' => 0,
                    'ErrReason' => null,
                    'RegistId' => $userId,
                    'UpdateId' => $userId,
                    'ValidFlg' => 1,
            ));
            $this->sendImageDone(
            $template['FromTitle'],
            $template['FromAddress'],
            $maildata['MailAddress'] . '様',
            $maildata['MailAddress'],
            $subject,
            $body
            );

            return $subject;

        } catch(\Exception $e) {
            if (isset($mailSendSeq)) {
                // メール送信履歴を登録した場合、エラー理由を更新
                $myPageHistory->saveUpdate(array(
                        'ErrFlg' => 1,
                        'ErrReason' => $e->getMessage(),
                        'UpdateId' => $userId,
                ), $mailSendSeq);
            };
            throw new CoralMailException( 'cannot sent mail for customer about ID upload.' . $e->getMessage(), 0, $e );
        }
    }

    /**
     * メール送信を実行する。
     *
     * @param string $fromName 送信者名
     * @param string $fromAddress 送信者アドレス
     * @param string $toName 受取人名
     * @param string $toAddress 受取人アドレス
     * @param string $subject 件名
     * @param string $body 本文(image含む)
     */
    public function sendImageDone($fromName, $fromAddress, $toName, $toAddress, $subject, $body)
    {
        // ↓↓↓運用テスト向けメール送信した体へのチェック処理(20150905_1430_suzuki_h)
        if ($toAddress == '*****') {
            return;
        }
        // ↑↑↑運用テスト向けメール送信した体へのチェック処理(20150905_1430_suzuki_h)

        // メール送信先が空の場合は終了
        if (!isset($toAddress)) {
            return;
        }

        // メールの形式が不正な場合は送信しない
        $validator = new CoralValidateMultiMail();
        if ( !$validator->isValid($toAddress) ) {
            return;
        }

        $mail = new Message();
        $mail->setEncoding('ASCII');

        // 送信元
        $mail->setFrom($fromAddress, mb_encode_mimeheader($fromName, 'ISO-2022-JP', 'Q'));//メール文字化け対応(20150209_1700)

        // 送信先
        $toAddresses = explode(",", $toAddress);
        for ($i = 0 ; $i < count($toAddresses) ; $i++)
        {
        $mail->addTo($toAddresses[$i], mb_encode_mimeheader($toName, 'ISO-2022-JP', 'Q'));//メール文字化け対応(20150209_1700)
        }

        // 件名
        $mail->setSubject(mb_encode_mimeheader($subject, 'ISO-2022-JP', 'Q'));//メール文字化け対応(20150209_1700)

        // 本文
        $mail->setBody($body);

        // 送信
        $this->smtp->send($mail);
    }

    /**
     * 事業者メール登録バッチエラーメール
     *
     * @param string $mailbody メール本文
     * @throws \Exception
     * @throws CoralMailException
     */
    public function SendRegistEnterpriseMailFailMail($mailbody)
    {
        try {
            // メールテンプレートの取得
            $mdlmt = new TableMailTemplate($this->adapter);
            $template = $mdlmt->findMailTemplate(86, 0)->current();
            if(!$template) throw new \Exception('メールテンプレートが存在しません');

            // メールの構築
            $body = $template['Body'];
            $body = mb_ereg_replace('{body}', $mailbody, $body);// {body}置換

            $this->sendDone(
                $template['FromTitle'],
                $template['FromAddress'],
                $template['ToTitle'],
                $template['ToAddress'],
                $template['Subject'],
                $body
            );
        } catch(\Exception $e) {
            throw new CoralMailException( 'cannot sent mail for customer about withdraw.' . $e->getMessage(), 0, $e );
        }
    }

    /**
     * バッチエラーメール送信処理
     *
     * @param string $toAddress 送信先
     * @param string $occDate 発生日時
     * @param string $programName プログラム名称
     * @throws CoralMailException
     */
    public function SendBatchError($toAddress, $occDate, $programName)
    {
        try {
            $fromTitle      = '';
            $fromAddress    = 'customer@ato-barai.com';
            $toTitle        = '';
            $subject        = 'バッチ実行エラーが発生しました(' . $occDate . ')';
            $body           = "[" . $programName . "]にてバッチ実行エラーが発生しました。\r\nシステム担当者へ連絡してください。";

            $this->sendDone(
                $fromTitle,
                $fromAddress,
                $toTitle,
                $toAddress,
                $subject,
                $body
            );

        } catch(\Exception $e) {
            throw new CoralMailException( 'cannot sent mail for customer about withdraw.' . $e->getMessage(), 0, $e );
        }
    }

    /**
     * 返金メールを送信する。
     *
     * @param int $oseq 注文SEQ
     * @param int $userId ユーザーID
     */
    public function SendRepaymentMail($oseq, $userId)
    {
        try {
            // V_OrderCustomer に T_ReceiptControl を結合して顧客情報を取得する。
            $sql = <<<EOQ
SELECT  voc.*
FROM    V_OrderCustomer voc
WHERE   voc.OrderSeq = :OrderSeq
;
EOQ;

            // 顧客情報の取得
            $oc = $this->adapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current();

            if (CoralValidate::isNotEmpty($oc['MailAddress']))
            {

                // 使用すべきテンプレートの判定
                if ($this->isCelAddress($oc['MailAddress']))
                {
                    $tmpNumber = 88;
                }
                else
                {
                    $tmpNumber = 87;
                }

                // メールテンプレートの取得
                $mdlmt = new TableMailTemplate($this->adapter);
                $template = $mdlmt->findMailTemplate($tmpNumber, $oc['OemId'])->current();

                // メールの構築
                $body = $template['Body'];

                $sql  = " SELECT c.NameKj ";
                $sql .= " ,      DATE_FORMAT(rc.ReceiptProcessDate, '%c月%e日') AS ReceiptProcessDate ";
                $sql .= " ,      (CASE rc.ReceiptClass WHEN 1 THEN 'コンビニ' WHEN 2 THEN '郵便局' WHEN 3 THEN '銀行' WHEN 4 THEN 'LINE Pay' ELSE '' END) AS ReceiptClass ";
                $sql .= " ,      ent.ContactPhoneNumber ";
                $sql .= " FROM   T_Order o ";
                $sql .= "        INNER JOIN T_Customer c ON (c.OrderSeq = o.OrderSeq) ";
                $sql .= "        INNER JOIN T_ClaimControl cc ON (cc.OrderSeq = o.P_OrderSeq) ";
                $sql .= "        INNER JOIN T_ReceiptControl rc ON (rc.ReceiptSeq = cc.LastReceiptSeq) ";
                $sql .= "        INNER JOIN T_Enterprise ent ON (ent.EnterpriseId = o.EnterpriseId) ";
                $sql .= " WHERE  o.OrderSeq = :OrderSeq ";
                $row = $this->adapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current();

                $body = mb_ereg_replace('{CustomerNameKj}', $row['NameKj'], $body);
                $body = mb_ereg_replace('{SiteNameKj}', $oc['SiteNameKj'], $body);
                $body = mb_ereg_replace('{ReceiptDate}', $row['ReceiptProcessDate'], $body);
                $body = mb_ereg_replace('{ReceiptClass}', $row['ReceiptClass'], $body);
                $body = mb_ereg_replace('{Phone}', $row['ContactPhoneNumber'], $body);

                $this->insertInfoParamServer($body, $oc['EnterpriseId'], $oc['OemId']);
                $this->insertInfoParamServer($template['FromTitle'], $oc['EnterpriseId'], $oc['OemId']);
                $this->insertInfoParamServer($template['FromAddress'], $oc['EnterpriseId'], $oc['OemId']);
                $this->insertInfoParamServer($template['Subject'], $oc['EnterpriseId'], $oc['OemId']);

                $orderId = $oc['OrderId'];
                $this->insertOrderId($body, $orderId);
                $this->insertOrderId($template['Subject'], $orderId);

                // メール送信履歴登録
                $mailSendSeq = $this->history->saveNew(array(
                        'MailTemplateId' => $tmpNumber,
                        'OrderSeq' => $oc['OrderSeq'],
                        'EnterpriseId' => null,
                        'ManCustId' => null,
                        'ToAddress' => $oc['MailAddress'],
                        'CcAddress' => null,
                        'BccAddress' => null,
                        'Subject' => $template['Subject'],
                        'Body' => $body,
                        'MailSendDate' => date('Y-m-d H:i:s'),
                        'ErrFlg' => 0,
                        'ErrReason' => null,
                        'RegistId' => $userId,
                        'UpdateId' => $userId,
                        'ValidFlg' => 1,
                ));
                $this->sendDone(
                        $template['FromTitle'],
                        $template['FromAddress'],
                        $oc['NameKj'] . '　様',
                        $oc['MailAddress'],
                        $template['Subject'],
                        $body
                );
            }
        } catch(\Exception $e) {
            if (isset($mailSendSeq)) {
                // メール送信履歴を登録した場合、エラー理由を更新
                $this->history->saveUpdate(array(
                        'ErrFlg' => 1,
                        'ErrReason' => $e->getMessage(),
                        'UpdateId' => $userId,
                ), $mailSendSeq);
            }
            throw new CoralMailException( 'cannot sent receipt confirmed mail.' . $e->getMessage(), 0, $e );
        }
    }

    /**
     * 社内与信保留メール
     *
     * @param int $oseq 注文SEQ
     * @param int $userId ユーザーID
     *
     */
    public function SendHoldMailToEnt($oseq, $userId) {
        try
        {
            $sql  = " SELECT e.* ";
            $sql .= " ,      o.OrderId ";
            $sql .= " ,      c.NameKj ";
            $sql .= " ,      code92.Note AS PendingReason ";
            $sql .= " ,      code92.Class1 AS PendingMailSendType ";
            $sql .= " ,      ao.DefectCancelPlanDate ";
            $sql .= " FROM   T_Order o ";
            $sql .= "        INNER JOIN T_Enterprise e ON (e.EnterpriseId = o.EnterpriseId) ";
            $sql .= "        INNER JOIN T_Customer c ON (c.OrderSeq = o.OrderSeq) ";
            $sql .= "        INNER JOIN AT_Order ao ON (ao.OrderSeq = o.OrderSeq) ";
            $sql .= "        LEFT OUTER JOIN M_Code code92 ON code92.CodeId = 92 AND code92.KeyCode = o.PendingReasonCode ";
            $sql .= " WHERE  o.OrderSeq = :OrderSeq ";
            $row = $this->adapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current();

            // メールテンプレートの取得
            $mdlmt = new TableMailTemplate($this->adapter);
            $template = $mdlmt->findMailTemplate(61, $row['OemId'])->current();

            if(!$template) throw new \Exception('メールテンプレートが存在しません');

            // メールの構築
            $body = $template['Body'];

            // 保留理由に設定されているメール送信区分で判定
            if ($row['PendingMailSendType'] != 1) {
                // メール送信しない場合終了
                return;
            }

            $body = mb_ereg_replace('{EnterpriseNameKj}', $row['EnterpriseNameKj'], $body);
            $body = mb_ereg_replace('{OrderId}', $row['OrderId'], $body);
            $body = mb_ereg_replace('{CustomerNameKj}', $row['NameKj'], $body);
            $body = mb_ereg_replace('{PendingReason}', nvl($row['PendingReason'], ''), $body);
            $body = mb_ereg_replace('{PendingDate}', date('m月d日', strtotime($row['DefectCancelPlanDate'])), $body);

            $this->insertInfoParamServer($body, $row['EnterpriseId'], $row['OemId']);
            $this->insertInfoParamServer($template['FromTitle'], $row['EnterpriseId'], $row['OemId']);
            $this->insertInfoParamServer($template['FromAddress'], $row['EnterpriseId'], $row['OemId']);
            $this->insertInfoParamServer($template['Subject'], $row['EnterpriseId'], $row['OemId']);

            $orderId = $row['OrderId'];
            $this->insertOrderId($body, $orderId);
            $this->insertOrderId($template['Subject'], $orderId);

            // メール送信履歴登録
            $mailSendSeq = $this->history->saveNew(array(
                    'MailTemplateId' => 61,
                    'OrderSeq' => $oseq,
                    'EnterpriseId' => $row['EnterpriseId'],
                    'ManCustId' => null,
                    'ToAddress' => $row['MailAddress'],
                    'CcAddress' => null,
                    'BccAddress' => null,
                    'Subject' => $template['Subject'],
                    'Body' => $body,
                    'MailSendDate' => date('Y-m-d H:i:s'),
                    'ErrFlg' => 0,
                    'ErrReason' => null,
                    'RegistId' => $userId,
                    'UpdateId' => $userId,
                    'ValidFlg' => 1,
            ));
            $this->sendDone(
                    $template['FromTitle'],
                    $template['FromAddress'],
                    $row['CpNameKj'] . '様',
                    $row['MailAddress'],
                    $template['Subject'],
                    $body
            );
        } catch(\Exception $e) {
            if (isset($mailSendSeq)) {
                // メール送信履歴を登録した場合、エラー理由を更新
                $this->history->saveUpdate(array(
                        'ErrFlg' => 1,
                        'ErrReason' => $e->getMessage(),
                        'UpdateId' => $userId,
                ), $mailSendSeq);
            }
            throw new CoralMailException( 'cannot sent mail for ent about claim combined.' . $e->getMessage(), 0, $e );
        }
    }

    /**
     * 社内与信保留メール(自動与信バッチでの不備注文用)
     *
     * @param int $oseq 注文SEQ
     * @param int $userId ユーザーID
     *
     */
    public function SendHoldMailToEnt2($oseq, $userId) {
        try
        {
            $sql  = " SELECT e.* ";
            $sql .= " ,      o.OrderId ";
            $sql .= " ,      c.NameKj ";
            $sql .= " ,      ao.DefectNote AS PendingReason ";
            $sql .= " ,      ao.DefectCancelPlanDate ";
            $sql .= " FROM   T_Order o ";
            $sql .= "        INNER JOIN T_Enterprise e ON (e.EnterpriseId = o.EnterpriseId) ";
            $sql .= "        INNER JOIN T_Customer c ON (c.OrderSeq = o.OrderSeq) ";
            $sql .= "        INNER JOIN AT_Order ao ON (ao.OrderSeq = o.OrderSeq) ";
            $sql .= " WHERE  o.OrderSeq = :OrderSeq ";
            $row = $this->adapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current();

            // メールテンプレートの取得
            $mdlmt = new TableMailTemplate($this->adapter);
            $template = $mdlmt->findMailTemplate(61, $row['OemId'])->current();

            if(!$template) throw new \Exception('メールテンプレートが存在しません');

            // メールの構築
            $body = $template['Body'];

            $body = mb_ereg_replace('{EnterpriseNameKj}', $row['EnterpriseNameKj'], $body);
            $body = mb_ereg_replace('{OrderId}', $row['OrderId'], $body);
            $body = mb_ereg_replace('{CustomerNameKj}', $row['NameKj'], $body);
            $arrPendingReason = explode("\n", $row['PendingReason']);
            $body = mb_ereg_replace('{PendingReason}', $arrPendingReason[0], $body);  // メールに記載する保留理由は一つ目に限定

            $body = mb_ereg_replace('{PendingDate}', date('m月d日', strtotime($row['DefectCancelPlanDate'])), $body);

            $this->insertInfoParamServer($body, $row['EnterpriseId'], $row['OemId']);
            $this->insertInfoParamServer($template['FromTitle'], $row['EnterpriseId'], $row['OemId']);
            $this->insertInfoParamServer($template['FromAddress'], $row['EnterpriseId'], $row['OemId']);
            $this->insertInfoParamServer($template['Subject'], $row['EnterpriseId'], $row['OemId']);


            $orderId = $row['OrderId'];
            $this->insertOrderId($template['Subject'], $orderId);

            // メール送信履歴登録
            $mailSendSeq = $this->history->saveNew(array(
                    'MailTemplateId' => 61,
                    'OrderSeq' => $oseq,
                    'EnterpriseId' => $row['EnterpriseId'],
                    'ManCustId' => null,
                    'ToAddress' => $row['MailAddress'],
                    'CcAddress' => null,
                    'BccAddress' => null,
                    'Subject' => $template['Subject'],
                    'Body' => $body,
                    'MailSendDate' => date('Y-m-d H:i:s'),
                    'ErrFlg' => 0,
                    'ErrReason' => null,
                    'RegistId' => $userId,
                    'UpdateId' => $userId,
                    'ValidFlg' => 1,
            ));
            $this->sendDone(
            $template['FromTitle'],
            $template['FromAddress'],
            $row['CpNameKj'] . '様',
            $row['MailAddress'],
            $template['Subject'],
            $body
            );
        } catch(\Exception $e) {
            if (isset($mailSendSeq)) {
                // メール送信履歴を登録した場合、エラー理由を更新
                $this->history->saveUpdate(array(
                        'ErrFlg' => 1,
                        'ErrReason' => $e->getMessage(),
                        'UpdateId' => $userId,
                ), $mailSendSeq);
            }
            throw new CoralMailException( 'cannot sent mail for ent about claim combined.' . $e->getMessage(), 0, $e );
        }
    }

    /**
     * 注文修正完了メール
     *
     * @param int $enterpriseId 事業者ID
     * @param array $orderSeqs OrderSeqを格納した配列
     * @param int $userId ユーザーID
     */
    public function SendOrderModifiedMail($enterpriseId, $orderSeqs, $userId) {
        try
        {
            $row_ent = $this->adapter->query(" SELECT * FROM T_Enterprise WHERE EnterpriseId = :EnterpriseId "
                )->execute(array(':EnterpriseId' => $enterpriseId))->current();

            // メールテンプレートの取得
            $mdlmt = new TableMailTemplate($this->adapter);
            $template = $mdlmt->findMailTemplate(38, $row_ent['OemId'])->current();

            if(!$template) throw new \Exception('メールテンプレートが存在しません');

            // メールの構築
            $body = $template['Body'];

            $sql  = " SELECT CONCAT(o.OrderId, '　', IFNULL(o.Ent_OrderId, ''), '　', c.NameKj) AS OrderLineInfo ";
            $sql .= "      , s.AddressMail ";
            $sql .= " FROM   T_Order o ";
            $sql .= "        INNER JOIN T_Customer c ON (c.OrderSeq = o.OrderSeq) ";
            $sql .= "        INNER JOIN T_Site s ON o.SiteId = s.SiteId ";
            $sql .= " WHERE  o.OrderSeq IN (" . implode(',', $orderSeqs) . ") ";
            $ri = $this->adapter->query($sql)->execute(null);

            $orderSummary = "";
            foreach ($ri as $row) {
                // 注文修正メールを送信するサイトのみ対象
                if ($row['AddressMail'] == 1) {
                    $orderSummary .= ("\r\n" . $row['OrderLineInfo']);
                }
            }
            // 送信対象がない場合終了
            if (strlen($orderSummary) == 0) {
                return;
            }

            $body = mb_ereg_replace('{OrderCount}', count($orderSeqs), $body);
            $body = mb_ereg_replace('{EnterpriseNameKj}', $row_ent['EnterpriseNameKj'], $body);
            $body = mb_ereg_replace('{OrderSummary}', $orderSummary, $body);

            $this->insertInfoParamServer($body, $row_ent['EnterpriseId'], $row_ent['OemId']);
            $this->insertInfoParamServer($template['FromTitle'], $row_ent['EnterpriseId'], $row_ent['OemId']);
            $this->insertInfoParamServer($template['FromAddress'], $row_ent['EnterpriseId'], $row_ent['OemId']);
            $this->insertInfoParamServer($template['Subject'], $row_ent['EnterpriseId'], $row_ent['OemId']);

            $orderId = $row_ent['OrderId'];
            $this->insertOrderId($body, $orderId);
            $this->insertOrderId($template['Subject'], $orderId);

            // メール送信履歴登録
            $mailSendSeq = $this->history->saveNew(array(
                    'MailTemplateId' => 38,
                    'OrderSeq' => null,
                    'EnterpriseId' => $row_ent['EnterpriseId'],
                    'ManCustId' => null,
                    'ToAddress' => $row_ent['MailAddress'],
                    'CcAddress' => null,
                    'BccAddress' => null,
                    'Subject' => $template['Subject'],
                    'Body' => $body,
                    'MailSendDate' => date('Y-m-d H:i:s'),
                    'ErrFlg' => 0,
                    'ErrReason' => null,
                    'RegistId' => $userId,
                    'UpdateId' => $userId,
                    'ValidFlg' => 1,
            ));
            $this->sendDone(
                    $template['FromTitle'],
                    $template['FromAddress'],
                    $row_ent['CpNameKj'] . '様',
                    $row_ent['MailAddress'],
                    mb_ereg_replace('{OrderCount}', count($orderSeqs), $template['Subject']),
                    $body
            );
        } catch(\Exception $e) {
            if (isset($mailSendSeq)) {
                // メール送信履歴を登録した場合、エラー理由を更新
                $this->history->saveUpdate(array(
                        'ErrFlg' => 1,
                        'ErrReason' => $e->getMessage(),
                        'UpdateId' => $userId,
                ), $mailSendSeq);
            }
            throw new CoralMailException( 'cannot sent mail for ent about claim combined.' . $e->getMessage(), 0, $e );
        }
    }

    /**
     *  ０円請求報告メール
     *
     * @param array $data 注文IDの配列
     */
    public function SendOnZeroClaim($data)
    {
        try {
            // メールテンプレートの取得
            $mdlmt = new TableMailTemplate($this->adapter);
            $template = $mdlmt->findMailTemplate(89, 0)->current();
            if(!$template) throw new \Exception('メールテンプレートが存在しません');

            // メールの構築
            $body = $template['Body'];
            $body = mb_ereg_replace('{body}', implode("\r\n", $data), $body);// {body}置換
            $logicSbps = new LogicSbps($this->adapter);
            $mdlcd = new TableCode($this->adapter);
            $this->insertInfoParamServer($body, $data['EnterpriseId'], $data['OemId']);
            $flag = $logicSbps->checkHasPaymentAfterArrivalFlg($data['EnterpriseId'], 'T_Site');
            if ($flag) {
                $toAddress = $mdlcd->getMasterAssCode(213, 1);
            } else {
                $toAddress = $mdlcd->getMasterAssCode(213, 0);
            }
            $this->insertInfoParamServer($template['FromTitle'], $data['EnterpriseId'], $data['OemId']);
            $this->insertInfoParamServer($template['FromAddress'], $data['EnterpriseId'], $data['OemId']);
            $this->insertInfoParamServer($template['Subject'], $data['EnterpriseId'], $data['OemId']);

            $orderId = $data['OrderId'];
            $this->insertOrderId($body, $orderId);
            $this->insertOrderId($template['Subject'], $orderId);

            $this->sendDone(
                $template['FromTitle'],
                $template['FromAddress'],
                $template['ToTitle'],
                $toAddress,
                $template['Subject'],
                $body
            );
        } catch(\Exception $e) {
            throw new CoralMailException( 'cannot sent mail about withdraw.' . $e->getMessage(), 0, $e );
        }
    }

    /**
     *  WCOS URL 通知メール
     *
     * @param array $data 注文IDの配列
     */

    /**
     * WCOS URL 通知メール
     * @param int $repaySeq 返金管理SEQ
     * @param int $userId ユーザーID
     */
    public function SendWcosUrl($repaySeq, $userId)
    {
        try {
            // 返金管理情報を元に、注文SEQを取得する
            $sql = ' SELECT cc.OrderSeq FROM T_RepaymentControl rc, T_ClaimControl cc WHERE rc.ClaimId = cc.ClaimId AND rc.RepaySeq = :RepaySeq ';
            $oseq = $this->adapter->query($sql)->execute(array(':RepaySeq' => $repaySeq))->current()['OrderSeq'];

	        // V_OrderCustomer に T_ReceiptControl を結合して顧客情報を取得する。
	        $sql = <<<EOQ
SELECT  voc.*
FROM    V_OrderCustomer voc
WHERE   voc.OrderSeq = :OrderSeq
;
EOQ;

	        // 顧客情報の取得
	        $oc = $this->adapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current();

	        if (CoralValidate::isNotEmpty($oc['MailAddress']))
	        {

	            // 使用すべきテンプレートの判定
	            if ($this->isCelAddress($oc['MailAddress']))
	            {
	                $tmpNumber = 91;
	            }
	            else
	            {
	                $tmpNumber = 90;
	            }

	            // メールテンプレートの取得
	            $mdlmt = new TableMailTemplate($this->adapter);
	            $template = $mdlmt->findMailTemplate($tmpNumber, $oc['OemId'])->current();
	            if(!$template) throw new \Exception('メールテンプレートが存在しません');

	            $body = $template['Body'];

                $sql  = " SELECT c.NameKj ";
                $sql .= " ,      DATE_FORMAT(rc.ReceiptProcessDate, '%c月%e日') AS ReceiptProcessDate ";
                $sql .= " ,      (SELECT KeyContent FROM M_Code WHERE CodeId = 198 AND KeyCode = rc.ReceiptClass) AS ReceiptClass ";
                $sql .= " ,      (cc.ReceiptAmountTotal - cc.ClaimAmount) AS OverReceiptAmount ";
                $sql .= " ,      ent.ContactPhoneNumber ";
                $sql .= " FROM   T_Order o ";
                $sql .= "        INNER JOIN T_Customer c ON (c.OrderSeq = o.OrderSeq) ";
                $sql .= "        INNER JOIN T_ClaimControl cc ON (cc.OrderSeq = o.P_OrderSeq) ";
                $sql .= "        INNER JOIN T_ReceiptControl rc ON (rc.ReceiptSeq = cc.LastReceiptSeq) ";
                $sql .= "        INNER JOIN T_Enterprise ent ON (ent.EnterpriseId = o.EnterpriseId) ";
                $sql .= " WHERE  o.OrderSeq = :OrderSeq ";
                $row = $this->adapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current();

                // 注文商品情報の取得
                $mdloi = new TableOrderItems($this->adapter);
                $oneitem = $mdloi->getOneItemName($oseq);

                $body = mb_ereg_replace('{CustomerNameKj}', $row['NameKj'], $body);
                $body = mb_ereg_replace('{SiteNameKj}', $oc['SiteNameKj'], $body);
                $body = mb_ereg_replace('{ReceiptDate}', $row['ReceiptProcessDate'], $body);
                $body = mb_ereg_replace('{ReceiptClass}', $row['ReceiptClass'], $body);
                $body = mb_ereg_replace('{OverReceiptAmount}', $row['OverReceiptAmount'], $body);

                $body = mb_ereg_replace('{OrderId}', $oc['OrderId'], $body);
                $body = mb_ereg_replace('{OrderDate}', date('n月j日', strtotime($oc['ReceiptOrderDate'])), $body);// 注文日
                $body = mb_ereg_replace('{OneOrderItem}', $oneitem, $body);
                $useAmountTotal = $this->adapter->query(" SELECT UseAmountTotal FROM T_ClaimControl WHERE OrderSeq = :OrderSeq ")->execute(array(':OrderSeq' => $oseq))->current()['UseAmountTotal'];
                $body = mb_ereg_replace('{UseAmount}', $useAmountTotal . '円', $body);

                $this->insertInfoParamServer($body, $oc['EnterpriseId'], $oc['OemId']);
                $this->insertInfoParamServer($template['FromTitle'], $oc['EnterpriseId'], $oc['OemId']);
                $this->insertInfoParamServer($template['FromAddress'], $oc['EnterpriseId'], $oc['OemId']);
                $this->insertInfoParamServer($template['Subject'], $oc['EnterpriseId'], $oc['OemId']);

                $orderId = $oc['OrderId'];
                $this->insertOrderId($template['Subject'], $orderId);

	            // メール送信履歴登録
	            $mailSendSeq = $this->history->saveNew(array(
	                    'MailTemplateId' => $tmpNumber,
	                    'OrderSeq' => $oc['OrderSeq'],
	                    'EnterpriseId' => null,
	                    'ManCustId' => null,
	                    'ToAddress' => $oc['MailAddress'],
	                    'CcAddress' => null,
	                    'BccAddress' => null,
	                    'Subject' => $template['Subject'],
	                    'Body' => $body,
	                    'MailSendDate' => date('Y-m-d H:i:s'),
	                    'ErrFlg' => 0,
	                    'ErrReason' => null,
	                    'RegistId' => $userId,
	                    'UpdateId' => $userId,
	                    'ValidFlg' => 1,
	            ));
	            $this->sendDone(
                    $template['FromTitle'],
                    $template['FromAddress'],
                    $oc['NameKj'] . '　様',
                    $oc['MailAddress'],
                    $template['Subject'],
                    $body
	            );
	        }
	    } catch(\Exception $e) {
	        if (isset($mailSendSeq)) {
	            // メール送信履歴を登録した場合、エラー理由を更新
	            $this->history->saveUpdate(array(
	                    'ErrFlg' => 1,
	                    'ErrReason' => $e->getMessage(),
	                    'UpdateId' => $userId,
	            ), $mailSendSeq);
	        }
	        throw new CoralMailException( 'cannot sent wcos url mail.' . $e->getMessage(), 0, $e );
	    }
    }

    /**
     * マイページパスワード再発行メールを送信する
     *
     * @param array $mail メール情報
     * @param int $userId ユーザーID
     *
     */
    public function SendPasswordResetMail($mail, $baseUrl, $userId) {
        try
        {
            $myPageHistory = new MypageViewMailSendHistory($this->adapter);
            // メールテンプレートの取得
            $mdlmt = new MypageViewMailTemplate($this->adapter);
            if($this->isCelAddress($mail['MailAddress'])){
                $class = 93;
            }else{
                $class = 92;
            }

            $template = $mdlmt->findMailTemplate($class, $mail['OemId'])->current();

            if(!$template) throw new \Exception('メールテンプレートが存在しません');

            // メールの構築
            $body = $template['Body'];
            $body = mb_ereg_replace('{MypagePasswordResetUrl}', ($baseUrl . '/login/reset/accessid/' . $mail['UrlParameter']), $body);

            // メール送信履歴登録
            $mailSendSeq = $myPageHistory->saveNew(array(
                    'MailTemplateId' => $class,
                    'OrderSeq' => null,
                    'EnterpriseId' => null,
                    'ToAddress' => $mail['MailAddress'],
                    'CcAddress' => null,
                    'BccAddress' => null,
                    'Subject' => $template['Subject'],
                    'Body' => $body,
                    'MailSendDate' => date('Y-m-d H:i:s'),
                    'ErrFlg' => 0,
                    'ErrReason' => null,
                    'RegistId' => $userId,
                    'UpdateId' => $userId,
                    'ValidFlg' => 1,
            ));
            $this->sendDone(
                $template['FromTitle'],
                $template['FromAddress'],
                $mail['MailAddress'] . '様',
                $mail['MailAddress'],
                $template['Subject'],
                $body
            );
        } catch(\Exception $e) {
            echo $e->getTraceAsString();
            if (isset($mailSendSeq)) {
                // メール送信履歴を登録した場合、エラー理由を更新
                $myPageHistory->saveUpdate(array(
                        'ErrFlg' => 1,
                        'ErrReason' => $e->getMessage(),
                        'UpdateId' => $userId,
                ), $mailSendSeq);
            };
            throw new CoralMailException( 'cannot sent mail for customer about preregist.' . $e->getMessage(), 0, $e );
        }
    }

    /**
     * 未返金案件メール
     * @param int $repaySeq 返金管理SEQ
     * @param int $userId ユーザーID
     */
    public function SendRepay($repaySeq, $userId)
    {
        try {
            // 返金管理情報を元に、注文SEQを取得する
            $sql = ' SELECT cc.OrderSeq FROM T_RepaymentControl rc, T_ClaimControl cc WHERE rc.ClaimId = cc.ClaimId AND rc.RepaySeq = :RepaySeq ';
            $oseq = $this->adapter->query($sql)->execute(array(':RepaySeq' => $repaySeq))->current()['OrderSeq'];

            // V_OrderCustomer に T_ReceiptControl を結合して顧客情報を取得する。
            $sql = <<<EOQ
SELECT  voc.*
FROM    V_OrderCustomer voc
WHERE   voc.OrderSeq = :OrderSeq
;
EOQ;

            // 顧客情報の取得
            $oc = $this->adapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current();

            if (CoralValidate::isNotEmpty($oc['MailAddress']))
            {

                // 使用すべきテンプレートの判定
                if ($this->isCelAddress($oc['MailAddress']))
                {
                    $tmpNumber = 103;
                }
                else
                {
                    $tmpNumber = 102;
                }

                // メールテンプレートの取得
                $mdlmt = new TableMailTemplate($this->adapter);
                $template = $mdlmt->findMailTemplate($tmpNumber, $oc['OemId'])->current();
                if(!$template) throw new \Exception('mail template not exist');

                $body = $template['Body'];

                $sql  = " SELECT c.NameKj ";
                $sql .= " ,      DATE_FORMAT(rc.ReceiptProcessDate, '%c月%e日') AS ReceiptProcessDate ";
                $sql .= " ,      (CASE rc.ReceiptClass WHEN 1 THEN 'コンビニ' WHEN 2 THEN '郵便局' WHEN 3 THEN '銀行' WHEN 4 THEN 'LINE Pay' ELSE '' END) AS ReceiptClass ";
                $sql .= " ,      (cc.ReceiptAmountTotal - cc.ClaimAmount) AS OverReceiptAmount ";
                $sql .= " ,      ent.ContactPhoneNumber ";
                $sql .= " FROM   T_Order o ";
                $sql .= "        INNER JOIN T_Customer c ON (c.OrderSeq = o.OrderSeq) ";
                $sql .= "        INNER JOIN T_ClaimControl cc ON (cc.OrderSeq = o.P_OrderSeq) ";
                $sql .= "        INNER JOIN T_ReceiptControl rc ON (rc.ReceiptSeq = cc.LastReceiptSeq) ";
                $sql .= "        INNER JOIN T_Enterprise ent ON (ent.EnterpriseId = o.EnterpriseId) ";
                $sql .= " WHERE  o.OrderSeq = :OrderSeq ";
                $row = $this->adapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current();

                // 注文商品情報の取得
                $mdloi = new TableOrderItems($this->adapter);
                $oneitem = $mdloi->getOneItemName($oseq);

                $body = mb_ereg_replace('{CustomerNameKj}', $row['NameKj'], $body);
                $body = mb_ereg_replace('{SiteNameKj}', $oc['SiteNameKj'], $body);
                $body = mb_ereg_replace('{ReceiptDate}', $row['ReceiptProcessDate'], $body);
                $body = mb_ereg_replace('{ReceiptClass}', $row['ReceiptClass'], $body);
                $body = mb_ereg_replace('{OverReceiptAmount}', $row['OverReceiptAmount'], $body);

                $body = mb_ereg_replace('{OrderId}', $oc['OrderId'], $body);
                $body = mb_ereg_replace('{OrderDate}', date('n月j日', strtotime($oc['ReceiptOrderDate'])), $body);// 注文日
                $body = mb_ereg_replace('{OneOrderItem}', $oneitem, $body);
                $useAmountTotal = $this->adapter->query(" SELECT UseAmountTotal FROM T_ClaimControl WHERE OrderSeq = :OrderSeq ")->execute(array(':OrderSeq' => $oseq))->current()['UseAmountTotal'];
                $body = mb_ereg_replace('{UseAmount}', $useAmountTotal . '円', $body);

                $this->insertInfoParamServer($body, $oc['EnterpriseId'], $oc['OemId']);
                $this->insertInfoParamServer($template['FromTitle'], $oc['EnterpriseId'], $oc['OemId']);
                $this->insertInfoParamServer($template['FromAddress'], $oc['EnterpriseId'], $oc['OemId']);
                $this->insertInfoParamServer($template['Subject'], $oc['EnterpriseId'], $oc['OemId']);

                $orderId = $oc['OrderId'];
                $this->insertOrderId($template['Subject'], $orderId);

                // メール送信履歴登録
                $mailSendSeq = $this->history->saveNew(array(
                        'MailTemplateId' => $tmpNumber,
                        'OrderSeq' => $oc['OrderSeq'],
                        'EnterpriseId' => null,
                        'ManCustId' => null,
                        'ToAddress' => $oc['MailAddress'],
                        'CcAddress' => null,
                        'BccAddress' => null,
                        'Subject' => $template['Subject'],
                        'Body' => $body,
                        'MailSendDate' => date('Y-m-d H:i:s'),
                        'ErrFlg' => 0,
                        'ErrReason' => null,
                        'RegistId' => $userId,
                        'UpdateId' => $userId,
                        'ValidFlg' => 1,
                ));
                $this->sendDone(
                $template['FromTitle'],
                $template['FromAddress'],
                $oc['NameKj'] . '　様',
                $oc['MailAddress'],
                $template['Subject'],
                $body
                );
            }
        } catch(\Exception $e) {
            if (isset($mailSendSeq)) {
                // メール送信履歴を登録した場合、エラー理由を更新
                $this->history->saveUpdate(array(
                        'ErrFlg' => 1,
                        'ErrReason' => $e->getMessage(),
                        'UpdateId' => $userId,
                ), $mailSendSeq);
            }
            throw new CoralMailException( 'cannot sent repay mail. ' . $e->getMessage(), 0, $e );
        }
    }

    /**
     * メール送信を実行する。
     * (添付ファイルあり版限定)
     *
     * @param string $fromName 送信者名
     * @param string $fromAddress 送信者アドレス
     * @param string $toName 受取人名
     * @param string $toAddress 受取人アドレス
     * @param string $subject 件名
     * @param string $body 本文
     * @param string $filePath ファイルパス
     */
    public function sendDone2($fromName, $fromAddress, $toName, $toAddress, $subject, $body, $filePath)
    {
        // ↓↓↓運用テスト向けメール送信した体へのチェック処理(20150905_1430_suzuki_h)
        if ($toAddress == '*****') {
            return;
        }
        // ↑↑↑運用テスト向けメール送信した体へのチェック処理(20150905_1430_suzuki_h)

        // メール送信先が空の場合は終了
        if (!isset($toAddress)) {
            return;
        }

        // メールの形式が不正な場合は送信しない
        $validator = new CoralValidateMultiMail();
        if ( !$validator->isValid($toAddress) ) {
            return;
        }

        $mail = new Message();
        $mail->setEncoding('ASCII');

        $headers = new \Zend\Mail\Headers();
        $ary_hdr = array();
        $ary_hdr[] = "Content-Type: text/plain; charset=ISO-2022-JP\n";
        $headers->addHeaders($ary_hdr);
        $mail->setHeaders($headers);

        // 送信元
        $mail->setFrom($fromAddress, mb_encode_mimeheader($fromName, 'ISO-2022-JP', 'Q'));//メール文字化け対応(20150209_1700)

        // 送信先
        $toAddresses = explode(",", $toAddress);
        for ($i = 0 ; $i < count($toAddresses) ; $i++)
        {
            $mail->addTo($toAddresses[$i], mb_encode_mimeheader($toName, 'ISO-2022-JP', 'Q'));//メール文字化け対応(20150209_1700)
        }

        // 件名
        $mail->setSubject(mb_encode_mimeheader($subject, 'ISO-2022-JP', 'Q'));//メール文字化け対応(20150209_1700)

        $text = new \Zend\Mime\Part($this->toMailChar(mb_convert_kana($body, 'K', 'UTF-8')));
        $text->type = Mime::TYPE_TEXT;
        $text->charset = 'ISO-2022-JP';
        $text->encoding = Mime::ENCODING_7BIT;

        $fileContents = fopen($filePath, 'r');
        $attachment = new \Zend\Mime\Part($fileContents);
        $attachment->type = mime_content_type($filePath);
        $attachment->encoding = Mime::ENCODING_BASE64;
        $attachment->filename = basename($filePath);
        $attachment->disposition = Mime::DISPOSITION_ATTACHMENT;

        $mimeMessage = new \Zend\Mime\Message();
        $mimeMessage->setParts(array($text,$attachment));

        $mail->setBody($mimeMessage);

        // 送信
        $this->smtp->send($mail);
    }

    /**
     * 間違い伝番修正依頼メール
     *
     * @param array $aryEnterprise 加盟店情報
     * @param string $appendFileName 添付ファイル名(フルパス)
     * @param int $userId ユーザーID
     */
    public function SendRequestModifyJournal($aryEnterprise, $appendFileName, $userId)
    {
        try {
            $row_ent = $this->adapter->query(" SELECT * FROM T_Enterprise WHERE LoginId = :LoginId "
                )->execute(array(':LoginId' => $aryEnterprise['LoginId']))->current();

            // メールテンプレートの取得
            $mdlmt = new TableMailTemplate($this->adapter);
            $template = $mdlmt->findMailTemplate(94)->current();

            if(!$template) throw new \Exception('メールテンプレートが存在しません');

            // メールの構築
            $body = $template['Body'];

            $body = mb_ereg_replace('{EnterpriseNameKj}', $row_ent['EnterpriseNameKj'], $body);
            $body = mb_ereg_replace('{ReceiptOrderDate}', $aryEnterprise['ReceiptOrderDate'], $body);
            $body = mb_ereg_replace('{OrderId}', $aryEnterprise['OrderIdStr'], $body);

            $this->insertInfoParamServer($body, $row_ent['EnterpriseId'], $row_ent['OemId']);
            $this->insertInfoParamServer($template['FromTitle'], $row_ent['EnterpriseId'], $row_ent['OemId']);
            $this->insertInfoParamServer($template['FromAddress'], $row_ent['EnterpriseId'], $row_ent['OemId']);
            $this->insertInfoParamServer($template['Subject'], $row_ent['EnterpriseId'], $row_ent['OemId']);

            $orderId = $aryEnterprise['OrderIdStr'];
            $this->insertOrderId($template['Subject'], $orderId);

            // メール送信履歴登録
            $mailSendSeq = $this->history->saveNew(array(
                    'MailTemplateId' => 94,
                    'OrderSeq' => null,
                    'EnterpriseId' => $row_ent['EnterpriseId'],
                    'ManCustId' => null,
                    'ToAddress' => $row_ent['MailAddress'],
                    'CcAddress' => null,
                    'BccAddress' => null,
                    'Subject' => $template['Subject'],
                    'Body' => $body,
                    'MailSendDate' => date('Y-m-d H:i:s'),
                    'ErrFlg' => 0,
                    'ErrReason' => null,
                    'RegistId' => $userId,
                    'UpdateId' => $userId,
                    'ValidFlg' => 1,
            ));
            $this->sendDone2(
                $template['FromTitle'],
                $template['FromAddress'],
                $row_ent['CpNameKj'] . '様',
                $row_ent['MailAddress'],
                $template['Subject'],
                $body,
                $appendFileName
            );
        } catch(\Exception $e) {
            echo $e->getTraceAsString();
            if (isset($mailSendSeq)) {
                // メール送信履歴を登録した場合、エラー理由を更新
                $myPageHistory->saveUpdate(array(
                        'ErrFlg' => 1,
                        'ErrReason' => $e->getMessage(),
                        'UpdateId' => $userId,
                ), $mailSendSeq);
            };
            throw new CoralMailException( 'cannot sent mail for customer about preregist.' . $e->getMessage(), 0, $e );
        }
    }

    /**
     * 解凍パスワード通知メール
     *
     * @param array $aryEnterprise 加盟店情報
     * @param int $userId ユーザーID
     */
    public function SendRequestModifyJournalUnzipPassword($aryEnterprise, $userId)
    {
        try {
            $row_ent = $this->adapter->query(" SELECT * FROM T_Enterprise WHERE LoginId = :LoginId "
                )->execute(array(':LoginId' => $aryEnterprise['LoginId']))->current();

            // メールテンプレートの取得
            $mdlmt = new TableMailTemplate($this->adapter);
            $template = $mdlmt->findMailTemplate(95)->current();

            if(!$template) throw new \Exception('メールテンプレートが存在しません');

            // メールの構築
            $body = $template['Body'];

            $body = mb_ereg_replace('{EnterpriseNameKj}', $row_ent['EnterpriseNameKj'], $body);
            $body = mb_ereg_replace('{FileName}', ($aryEnterprise['LoginId'] . '_' . $aryEnterprise['FileDate'] . '.zip'), $body);
            $body = mb_ereg_replace('{Password}', $aryEnterprise['Password'], $body);

            $this->insertInfoParamServer($body, $row_ent['EnterpriseId'], $row_ent['OemId']);
            $this->insertInfoParamServer($template['FromTitle'], $row_ent['EnterpriseId'], $row_ent['OemId']);
            $this->insertInfoParamServer($template['FromAddress'], $row_ent['EnterpriseId'], $row_ent['OemId']);
            $this->insertInfoParamServer($template['Subject'], $row_ent['EnterpriseId'], $row_ent['OemId']);

            // メール送信履歴登録
            $mailSendSeq = $this->history->saveNew(array(
                    'MailTemplateId' => 95,
                    'OrderSeq' => null,
                    'EnterpriseId' => $row_ent['EnterpriseId'],
                    'ManCustId' => null,
                    'ToAddress' => $row_ent['MailAddress'],
                    'CcAddress' => null,
                    'BccAddress' => null,
                    'Subject' => $template['Subject'],
                    'Body' => $body,
                    'MailSendDate' => date('Y-m-d H:i:s'),
                    'ErrFlg' => 0,
                    'ErrReason' => null,
                    'RegistId' => $userId,
                    'UpdateId' => $userId,
                    'ValidFlg' => 1,
            ));
            $this->sendDone(
                $template['FromTitle'],
                $template['FromAddress'],
                $row_ent['CpNameKj'] . '様',
                $row_ent['MailAddress'],
                $template['Subject'],
                $body
            );
        } catch(\Exception $e) {
            echo $e->getTraceAsString();
            if (isset($mailSendSeq)) {
                // メール送信履歴を登録した場合、エラー理由を更新
                $myPageHistory->saveUpdate(array(
                        'ErrFlg' => 1,
                        'ErrReason' => $e->getMessage(),
                        'UpdateId' => $userId,
                ), $mailSendSeq);
            };
            throw new CoralMailException( 'cannot sent mail for customer about preregist.' . $e->getMessage(), 0, $e );
        }
    }

    /**
     * CB向け無保証変更通知メール
     *
     * @param int $EnterpriseId 加盟店ID
     * @param int $SiteId サイトID
     * @param string $OrderId 注文ID
     * @param int $OrderSeq 注文Seq
     * @param int $userId ユーザーID
     */
    public function SendCbNoGuaranteeChange($EnterpriseId, $SiteId, $OrderId, $OrderSeq, $userId)
    {
        try {

            $row_ent = $this->adapter->query(" SELECT * FROM T_Enterprise WHERE EnterpriseId = :EnterpriseId "
            )->execute(array(':EnterpriseId' => $EnterpriseId))->current();

            // メールテンプレートの取得
            $mdlmt = new TableMailTemplate($this->adapter);
            $template = $mdlmt->findMailTemplate(96)->current();

            if(!$template) throw new \Exception('メールテンプレートが存在しません');

            // 与信NG理由
            $Sitesql = 'SELECT * FROM T_Site WHERE SiteId = :SiteId ';
            $Sitedata = $this->adapter->query($Sitesql)->execute(array(':SiteId' => $SiteId))->current();
            if($Sitedata['ShowNgReason'] == 1){
                $sql = 'SELECT AutoJudgeNgReasonCode, ManualJudgeNgReasonCode FROM AT_Order WHERE OrderSeq = :OrderSeq ';
                $NgReason = $this->adapter->query($sql)->execute(array(':OrderSeq' => $OrderSeq))->current();
                $Reason = null;
                if (nvl($NgReason['AutoJudgeNgReasonCode'], 0) != 0) {
                    $sql = 'SELECT Note FROM M_Code WHERE CodeId = 191 AND KeyCode = :KeyCode ';
                    $Reason = $this->adapter->query($sql)->execute(array(':KeyCode' => $NgReason['AutoJudgeNgReasonCode']))->current()['Note'];
                } elseif (nvl($NgReason['ManualJudgeNgReasonCode'], 0) != 0) {
                    $sql = 'SELECT Note FROM M_Code WHERE CodeId = 190 AND KeyCode = :KeyCode ';
                    $Reason = $this->adapter->query($sql)->execute(array(':KeyCode' => $NgReason['ManualJudgeNgReasonCode']))->current()['Note'];
                }
            }

            // メールの構築
            $subject = $template['Subject'];
            $subject = mb_ereg_replace('{LoginId}', $row_ent['LoginId'], $subject);
            $subject = mb_ereg_replace('{OrderId}', $OrderId, $subject);

            $body = $template['Body'];
            $body = mb_ereg_replace('{LoginId}', $row_ent['LoginId'], $body);
            $body = mb_ereg_replace('{OrderId}', $OrderId, $body);
            $body = mb_ereg_replace('{EnterpriseName}', $row_ent['EnterpriseNameKj'], $body);
            $body = mb_ereg_replace('{NgReason}', $Reason, $body);

            $this->insertInfoParamServer($body, $row_ent['EnterpriseId'], $row_ent['OemId']);
            $this->insertInfoParamServer($template['FromTitle'], $row_ent['EnterpriseId'], $row_ent['OemId']);
            $this->insertInfoParamServer($template['FromAddress'], $row_ent['EnterpriseId'], $row_ent['OemId']);
            $this->insertInfoParamServer($subject, $row_ent['EnterpriseId'], $row_ent['OemId']);

            // メール送信履歴登録
            $mailSendSeq = $this->history->saveNew(array(
                    'MailTemplateId' => 96,
                    'OrderSeq' => null,
                    'EnterpriseId' => $EnterpriseId,
                    'ManCustId' => null,
                    'ToAddress' => $template['ToAddress'],
                    'CcAddress' => null,
                    'BccAddress' => null,
                    'Subject' => $subject,
                    'Body' => $body,
                    'MailSendDate' => date('Y-m-d H:i:s'),
                    'ErrFlg' => 0,
                    'ErrReason' => null,
                    'RegistId' => $userId,
                    'UpdateId' => $userId,
                    'ValidFlg' => 1,
            ));
            $this->sendDone(
            $template['FromTitle'],
            $template['FromAddress'],
            $template['ToTitle'],
            $template['ToAddress'],
            $subject,
            $body
            );
        } catch(\Exception $e) {
            if (isset($mailSendSeq)) {
                // メール送信履歴を登録した場合、エラー理由を更新
                $this->history->saveUpdate(array(
                        'ErrFlg' => 1,
                        'ErrReason' => $e->getMessage(),
                        'UpdateId' => $userId,
                ), $mailSendSeq);
            };
            throw new CoralMailException( 'cannot sent mail for customer about preregist.' . $e->getMessage(), 0, $e );
        }
    }

    /**
     * 加盟店向け無保証変更通知メール
     *
     * @param int $EnterpriseId 加盟店ID
     * @param int $OrderSeq 注文Seq
     * @param string $OrderId 注文ID
     * @param int $userId ユーザーID
     */
    public function SendEntNoGuaranteeChange($EnterpriseId, $OrderSeq, $OrderId, $userId)
    {
        try {
            $row_ent = $this->adapter->query(" SELECT * FROM T_Enterprise WHERE EnterpriseId = :EnterpriseId "
            )->execute(array(':EnterpriseId' => $EnterpriseId))->current();

            $row_cus = $this->adapter->query(" SELECT * FROM T_Customer WHERE OrderSeq = :OrderSeq "
            )->execute(array(':OrderSeq' => $OrderSeq))->current();

            // メールテンプレートの取得
            $mdlmt = new TableMailTemplate($this->adapter);
            $template = $mdlmt->findMailTemplate(97)->current();

            if(!$template) throw new \Exception('メールテンプレートが存在しません');

            // メールの構築
            $body = $template['Body'];
            $body = mb_ereg_replace('{EnterpriseNameKj}', $row_ent['EnterpriseNameKj'], $body);
            $body = mb_ereg_replace('{OrderId}', $OrderId, $body);
            $body = mb_ereg_replace('{CustomerNameKj}', $row_cus['NameKj'], $body);

            $this->insertInfoParamServer($body, $row_ent['EnterpriseId'], $row_ent['OemId']);
            $this->insertInfoParamServer($template['FromTitle'], $row_ent['EnterpriseId'], $row_ent['OemId']);
            $this->insertInfoParamServer($template['FromAddress'], $row_ent['EnterpriseId'], $row_ent['OemId']);
            $this->insertInfoParamServer($template['Subject'], $row_ent['EnterpriseId'], $row_ent['OemId']);

            $orderId = $OrderId;
            $this->insertOrderId($template['Subject'], $orderId);

            // メール送信履歴登録
            $mailSendSeq = $this->history->saveNew(array(
                    'MailTemplateId' => 97,
                    'OrderSeq' => null,
                    'EnterpriseId' => $EnterpriseId,
                    'ManCustId' => null,
                    'ToAddress' => $row_ent['MailAddress'],
                    'CcAddress' => null,
                    'BccAddress' => null,
                    'Subject' => $template['Subject'],
                    'Body' => $body,
                    'MailSendDate' => date('Y-m-d H:i:s'),
                    'ErrFlg' => 0,
                    'ErrReason' => null,
                    'RegistId' => $userId,
                    'UpdateId' => $userId,
                    'ValidFlg' => 1,
            ));
            $this->sendDone(
            $template['FromTitle'],
            $template['FromAddress'],
            $template['ToTitle'],
            $row_ent['MailAddress'],
            $template['Subject'],
            $body
            );
        } catch(\Exception $e) {
            if (isset($mailSendSeq)) {
                // メール送信履歴を登録した場合、エラー理由を更新
                $this->history->saveUpdate(array(
                        'ErrFlg' => 1,
                        'ErrReason' => $e->getMessage(),
                        'UpdateId' => $userId,
                ), $mailSendSeq);
            };
            throw new CoralMailException( 'cannot sent mail for customer about preregist.' . $e->getMessage(), 0, $e );
        }
    }

    /**
     * 請求データ送信バッチエラーメール
     *
     * @param int $userId ユーザーID
     */
    public function SendClaimSendError($userId)
    {
        try {
            // メールテンプレートの取得
            $mdlmt = new TableMailTemplate($this->adapter);
            $template = $mdlmt->findMailTemplate(98)->current();

            if(!$template) throw new \Exception('メールテンプレートが存在しません');

            // メールの構築
            $body = $template['Body'];

            // メール送信履歴登録
            $mailSendSeq = $this->history->saveNew(array(
                    'MailTemplateId' => 98,
                    'OrderSeq' => null,
                    'EnterpriseId' => null,
                    'ManCustId' => null,
                    'ToAddress' => $template['ToAddress'],
                    'CcAddress' => null,
                    'BccAddress' => null,
                    'Subject' => $template['Subject'],
                    'Body' => $body,
                    'MailSendDate' => date('Y-m-d H:i:s'),
                    'ErrFlg' => 0,
                    'ErrReason' => null,
                    'RegistId' => $userId,
                    'UpdateId' => $userId,
                    'ValidFlg' => 1,
            ));
            $this->sendDone(
                $template['FromTitle'],
                $template['FromAddress'],
                $template['ToTitle'],
                $template['ToAddress'],
                $template['Subject'],
                $body
            );
        } catch(\Exception $e) {
            echo $e->getTraceAsString();
            if (isset($mailSendSeq)) {
                // メール送信履歴を登録した場合、エラー理由を更新
                $this->history->saveUpdate(array(
                        'ErrFlg' => 1,
                        'ErrReason' => $e->getMessage(),
                        'UpdateId' => $userId,
                ), $mailSendSeq);
            };
            throw new CoralMailException( 'cannot sent mail for customer about preregist.' . $e->getMessage(), 0, $e );
        }
    }

    /**
     * クレジット決済完了メールを送信する。
     *
     * @param int   $rcptseq 注文Seq
     * @param int   $userId  ユーザーID
     */
    public function SendCreditBuyingCompleteMail($orderSeq, $userId, $registDate)
    {
        try {
            // V_OrderCustomer に T_ReceiptControl を結合して顧客情報を取得する。
            $sql = <<<EOQ
SELECT  voc.*
     ,  (SELECT MIN(ch.ClaimDate) FROM T_ClaimHistory AS ch WHERE ch.ClaimPattern = 1 AND ch.OrderSeq = voc.OrderSeq) AS MinClaimDate
     ,  (SELECT ReceiptClass FROM T_ReceiptControl AS rc WHERE rc.OrderSeq = voc.OrderSeq ORDER BY RegistDate DESC LIMIT 1) AS ReceiptClass
  FROM  V_OrderCustomer voc
 WHERE  voc.OrderSeq = :OrderSeq
;
EOQ;

            // 顧客情報の取得
            $oc = $this->adapter->query($sql)->execute(array(':OrderSeq' => $orderSeq))->current();

            // サイト.入金確認メール=0(送信しない)の注文は送信しない
            $mdlsite = new TableSite($this->adapter);
            $siteId = $oc['SiteId'];
            $site = $mdlsite->findSite($siteId)->current();

            // メールアドレスが設定されている場合
            if (CoralValidate::isNotEmpty($oc['MailAddress']))
            {

                // 使用すべきテンプレートの判定
                if ($this->isCelAddress($oc['MailAddress']))
                {
					if ($site['ReceiptUsedFlg']) {
						$tmpNumber = 122;
					} else {
						$tmpNumber = 124;
					}
                }
                else
                {
					if ($site['ReceiptUsedFlg']) {
						$tmpNumber = 121;
					} else {
						$tmpNumber = 123;
					}
                }

                // 事業者情報の取得
                $mdle = new TableEnterprise($this->adapter);
                $edata = $mdle->findEnterprise($oc['EnterpriseId'])->current();

                // 注文商品情報の取得
                $mdloi = new TableOrderItems($this->adapter);
                $items = $mdloi->findByP_OrderSeq($oc['OrderSeq']);
                $oneitem = $mdloi->getOneItemName($oc['OrderSeq']);

                // メールテンプレートの取得
                $mdlmt = new TableMailTemplate($this->adapter);
                $template = $mdlmt->findMailTemplate($tmpNumber, $edata['OemId'])->current();

                // メールの構築
                $receiptProcessDate = date('n月j日', strtotime($registDate));             // 入金確認日
                $orderDate          = date('n月j日', strtotime($oc['ReceiptOrderDate'])); // 注文日

                $body = mb_ereg_replace('{EnterpriseNameKj}', $edata['EnterpriseNameKj'], $template['Body']);
                $body = mb_ereg_replace('{SiteNameKj}'      , $oc['SiteNameKj'], $body);
                $body = mb_ereg_replace('{Phone}'           , $edata['ContactPhoneNumber'], $body);
                $body = mb_ereg_replace('{Address}'         , $edata['PrefectureName']. $edata['City'] . $edata['Town'] . $edata['Building'], $body);
                $body = mb_ereg_replace('{CustomerNameKj}'  , $oc['NameKj'], $body);
                $body = mb_ereg_replace('{ReceiptDate}'     , $receiptProcessDate, $body);
                $body = mb_ereg_replace('{OrderDate}'       , $orderDate, $body);
                $body = mb_ereg_replace('{UseAmount}'       , $oc['UseAmount'], $body);
                $body = mb_ereg_replace('{SiteUrl}'         , $site['Url'], $body);

                $this->insertInfoParamServer($body, $oc['EnterpriseId'], $oc['OemId']);
                $this->insertInfoParamServer($template['FromTitle'], $oc['EnterpriseId'], $oc['OemId']);
                $this->insertInfoParamServer($template['FromAddress'], $oc['EnterpriseId'], $oc['OemId']);
                $this->insertInfoParamServer($template['Subject'], $oc['EnterpriseId'], $oc['OemId']);

                $orderId = $oc['OrderId'];
                $this->insertOrderId($body, $orderId);
                $this->insertOrderId($template['Subject'], $orderId);

                // 注文商品リストの作成
                $orders = "";
                $deliveryFee = 0;
                $settlementFee = 0;
                $tax = 0;
                foreach ($items as $item)
                {
                    switch($item['DataClass'])
                    {
                        case 2:
                            $deliveryFee += $item['SumMoney'];
                            break;
                        case 3:
                            $settlementFee += $item['SumMoney'];
                            break;
                        case 4:
                            $tax += $item['SumMoney'];
                            break;
                        default:
                            $orders .= BaseGeneralUtils::rpad(sprintf("%s　（数量：% 2d）", $item['ItemNameKj'], $item['ItemNum']), '　', 21) . $item['SumMoney'] . "円\r\n";
                            break;
                    }
                }

                $body = mb_ereg_replace('{OrderItems}', $orders, $body);
                $body = mb_ereg_replace('{SettlementFee}', $settlementFee, $body);
                $body = mb_ereg_replace('{DeliveryFee}', $deliveryFee, $body);
                $body = mb_ereg_replace('{Tax}', $tax, $body);
                $body = mb_ereg_replace('{OneOrderItem}', $oneitem, $body);
                $body = mb_ereg_replace('{OneOrderItems}', $oneitem, $body);
				// 2.6 change mail 17/01/2022
				$mdlcd = new TableCode($this->adapter);
				//支払方法を取得
				$sqlToGetPaymentMethod = <<<EOQ
SELECT MailParameterNameKj
FROM M_SbpsPayment ms
WHERE PaymentName = (SELECT Class1 FROM M_Code
WHERE CodeId=198
AND KeyCode=(SELECT ReceiptClass FROM T_ReceiptControl WHERE OrderSeq=:OrderSeq ORDER BY ReceiptSeq DESC LIMIT 1))
EOQ;
				$paymentMethod = $this->adapter->query($sqlToGetPaymentMethod)->execute(array(':OrderSeq' => $orderSeq))->current();
				$body = mb_ereg_replace('{PaymentMethod}', $paymentMethod['MailParameterNameKj'], $body);
				// orderPageUrl
				if ($template['Class'] == 121 || $template['Class'] == 122) {
                    $logicSbps = new LogicSbps($this->adapter);
                    $flag = $logicSbps->checkHasPaymentAfterArrivalFlg($oc['EnterpriseId'], 'T_Site');
                    if ($flag) {
                        $spapp2 = $mdlcd->getMasterAssCodeTodo(105, nvl($edata['OemId'], 0));
                        if (is_null($spapp2)) {
                            $orderPageUrl = $mdlcd->getMasterCaption(105, $edata['OemId']);
                        } else {
                            $orderPageUrl = $mdlcd->getMasterCaption(105, $edata['OemId']).$mdlcd->getMasterAssCodeTodo(105, $edata['OemId']);
                        }
                    } else {
                        $orderPageUrl = $mdlcd->getMasterCaption(105, $edata['OemId']);
                    }
					$body = mb_ereg_replace('{OrderPageUrl}', $orderPageUrl, $body);
				}

				// メール送信履歴登録
				$mailSendSeq = $this->history->saveNew(array(
					'MailTemplateId' => $tmpNumber,
					'OrderSeq' => $oc['OrderSeq'],
					'EnterpriseId' => null,
					'ManCustId' => null,
					'ToAddress' => $oc['MailAddress'],
					'CcAddress' => null,
					'BccAddress' => null,
				    'Subject' => mb_ereg_replace('{PaymentMethod}', $paymentMethod['MailParameterNameKj'], $template['Subject']),
					'Body' => $body,
					'MailSendDate' => date('Y-m-d H:i:s'),
					'ErrFlg' => 0,
					'ErrReason' => null,
					'RegistId' => $userId,
					'UpdateId' => $userId,
					'ValidFlg' => 1,
				));
				$this->sendDone(
					$template['FromTitle'],
					$template['FromAddress'],
					$oc['NameKj'] . '　様',
					$oc['MailAddress'],
				    mb_ereg_replace('{PaymentMethod}', $paymentMethod['MailParameterNameKj'], $template['Subject']),
					$body
				);
            }
        } catch(\Exception $e) {
            if (isset($mailSendSeq)) {
                // メール送信履歴を登録した場合、エラー理由を更新
                $this->history->saveUpdate(array(
                        'ErrFlg' => 1,
                        'ErrReason' => $e->getMessage(),
                        'UpdateId' => $userId,
                ), $mailSendSeq);
            }
            throw new CoralMailException( 'cannot sent receipt confirmed mail.' . $e->getMessage(), 0, $e );
        }
    }

    private function insertInfoParamServer(&$element, $enterpriseId, $oemId, $table = 'T_Site', $mailId = null)
    {

        $oid = $oemId;
        if (is_null($oemId)) {
          $oid = 0;
        }

        $logicSbps = new LogicSbps($this->adapter);
        $mdlcd = new TableCode($this->adapter);
        $flag = $logicSbps->checkHasPaymentAfterArrivalFlg($enterpriseId, $table);
        if ($flag) {
                $serviceName = $mdlcd->getMasterCaption(213, 2*$oid+1);
                $serviceMail = $mdlcd->getMasterAssCode(213, 2*$oid+1);
            } else {
                $serviceName = $mdlcd->getMasterCaption(213, 2*$oid);
                $serviceMail = $mdlcd->getMasterAssCode(213, 2*$oid);
        }
        $element = mb_ereg_replace('{ServiceName}', $serviceName, $element);
        $element = mb_ereg_replace('{ServiceMail}', $serviceMail, $element);
    }

    private function insertOrderId(&$element, $orderId = null)
    {
        if (!is_null($orderId)) {
            $element = mb_ereg_replace('{OrderId}', $orderId, $element);
        }
    }

    public function ClaimPrintErrorMailToCb($maildata, $userId) {
        try
        {
            $mdlsys = new TableSystemProperty($this->adapter);
            $mailto = $mdlsys->getValue(TableSystemProperty::DEFAULT_MODULE, 'ClaimPrintErrorMail', 'MailTo');

            // メールテンプレートの取得
            $mdlmt = new TableMailTemplate($this->adapter);
            $template = $mdlmt->findMailTemplate(125, 0)->current();

            if(!$template) throw new \Exception('メールテンプレートが存在しません');

            // メールの構築
            $body = mb_ereg_replace('{body}', implode("\n", $maildata), $template['Body']);   // 加盟店担当者名

            // メール送信履歴登録
            $mailSendSeq = $this->history->saveNew(array(
                                                       'MailTemplateId' => 125,
                                                       'OrderSeq' => null,
                                                       'EnterpriseId' => null,
                                                       'ManCustId' => null,
                                                       'ToAddress' => $mailto,
                                                       'CcAddress' => null,
                                                       'BccAddress' => null,
                                                       'Subject' => $template['Subject'],
                                                       'Body' => $body,
                                                       'MailSendDate' => date('Y-m-d H:i:s'),
                                                       'ErrFlg' => 0,
                                                       'ErrReason' => null,
                                                       'RegistId' => $userId,
                                                       'UpdateId' => $userId,
                                                       'ValidFlg' => 1,
                                                   ));

            $this->sendDone(
                $template['FromTitle'],
                $template['FromAddress'],
                '',
                $mailto,
                $template['Subject'],
                $body
            );
        } catch(\Exception $e) {
            if (isset($mailSendSeq)) {
                // メール送信履歴を登録した場合、エラー理由を更新
                $this->history->saveUpdate(array(
                                               'ErrFlg' => 1,
                                               'ErrReason' => $e->getMessage(),
                                               'UpdateId' => $userId,
                                           ), $mailSendSeq);
            };
            throw new CoralMailException( 'cannot sent mail for cb about claim combined.' . $e->getMessage(), 0, $e );
        }
    }
    
    public function MypagetobackextrapayBactchErrorMailToCb($maildata, $userId) {
        try
        {
            $mdlsys = new TableSystemProperty($this->adapter);
            $mailto = $mdlsys->getValue(TableSystemProperty::DEFAULT_MODULE, 'MypagetobackextrapayBactchErrorMail', 'MailTo');
            
            // メールテンプレートの取得
            $mdlmt = new TableMailTemplate($this->adapter);
            $template = $mdlmt->findMailTemplate(127, 0)->current();
            
            if(!$template) throw new \Exception('メールテンプレートが存在しません');
            
            // メールの構築
            $body = mb_ereg_replace('{OrderId}', implode("\n", $maildata), $template['Body']);   // 注文ID
            
            // メール送信履歴登録
            $mailSendSeq = $this->history->saveNew(array(
                'MailTemplateId' => 127,
                'OrderSeq' => null,
                'EnterpriseId' => null,
                'ManCustId' => null,
                'ToAddress' => $mailto,
                'CcAddress' => null,
                'BccAddress' => null,
                'Subject' => $template['Subject'],
                'Body' => $body,
                'MailSendDate' => date('Y-m-d H:i:s'),
                'ErrFlg' => 0,
                'ErrReason' => null,
                'RegistId' => $userId,
                'UpdateId' => $userId,
                'ValidFlg' => 1,
            ));
            
            $this->sendDone(
                $template['FromTitle'],
                $template['FromAddress'],
                '',
                $mailto,
                $template['Subject'],
                $body
                );
        } catch(\Exception $e) {
            if (isset($mailSendSeq)) {
                // メール送信履歴を登録した場合、エラー理由を更新
                $this->history->saveUpdate(array(
                    'ErrFlg' => 1,
                    'ErrReason' => $e->getMessage(),
                    'UpdateId' => $userId,
                ), $mailSendSeq);
            };
            throw new CoralMailException( 'cannot sent mail for cb about mypage to back extrapay.' . $e->getMessage(), 0, $e );
        }
    }
    
    
}

