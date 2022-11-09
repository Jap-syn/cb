<?php
namespace orderpage\Controller;

use orderpage\Application;
use models\Table\TableMypageToBackIF;
use models\Table\TableReceiptIssueHistory;
use models\Table\TableSystemProperty;
use Coral\Coral\Controller\CoralControllerAction;
use Zend\Db\Adapter\Adapter;

class ReceiptPdfOutputController extends CoralControllerAction {
    protected $_componentRoot = './application/views/components';

    /**
     * @var Application
    */
    private $app;

    /**
     * @var string
    */
    private $title;

    /** */
	const SESSION_JOB_PARAMS = 'MYORDER_JOB_PARAMS';

    /**
     * 初期化処理
    */
    protected function _init() {
        $this->app = Application::getInstance();

        $this->userInfo = Application::getInstance()->authManager->getUserInfo();
        $this->altUserInfo = Application::getInstance()->authManager->getAlternativeUserInfo();

        // ページタイトルとスタイルシート、JavaScriptを設定
        $this->addJavaScript( '../js/prototype.js' )
            ->addJavaScript( '../js/bytefx.js' )
            ->addJavaScript( '../js/json+.js' )
            ->addJavaScript( '../js/corelib.js' )
            ->addJavaScript( '../js/base.ui.js' );

        if ($this->is_mobile_request()) {
            $this->addStyleSheet( './css_sp/orderpage.css' )
                 ->addStyleSheet( './css_sp/orderpage_index.css' );
        } else {
            $this->addStyleSheet( './css/orderpage_receiptpdfoutput.css' )
                 ->addStyleSheet( './css/orderpage_index.css' );
        }
    }

    /**
     * 画面情報をセッションに保存
     */
    public function jobparamsetAction()
    {
        // セッションに情報をセットする
        unset($_SESSION[self::SESSION_JOB_PARAMS]);
        $_SESSION[self::SESSION_JOB_PARAMS] = $this->getParams();

        return;
    }

    /**
     * 印刷処理（PC）
     */
    public function printAction()
    {
        // PDF出力
        $pdf = $this->pdfDownload(True);

        return $pdf;
    }

    /**
     * 印刷処理（スマホ）
     */
    public function printSpAction()
    {
        // PDF出力
        $pdf = $this->pdfDownload(False);

        return $pdf;
    }

    /**
     * PDFダウンロード
     */
    public function pdfDownload($pcFlag)
    {
        $params = $_SESSION[self::SESSION_JOB_PARAMS];

        $orderSeqA = $params['OrderSeq'];
        $orderSeqB = $this->userInfo->OrderSeq;

        if ( empty( $orderSeqA )
          || empty( $orderSeqB )
          || ( $orderSeqA <> $orderSeqB )
        ) {
            $this->_redirect( 'login/login' );
            return;
        }

        //注文SEQ
        $orderSeq = $params['OrderSeq'];

        $errors = array();
        // 注文SEQが指定されていなかった場合
        if( !isset( $orderSeq ) ) {
            $this->setTemplate( 'error' );

            return $this->view;
        }

        //SQL用パラメータ
        $prm = array(
                ':OrderSeq' => $orderSeq
        );

        // PDF用データ取得
        // 注文＋請求管理情報
        $sql = <<<Q_END
SELECT
  ord.OrderSeq
, ord.P_OrderSeq
, ord.OrderId
, cc.CreditSettlementDecisionDate
, cmr.NameKj
, ord.EnterpriseId
, st.ReceiptIssueProviso
, st.SiteNameKj
, CONCAT("※",sbps.MailParameterNameKj,"にてお支払い") as "Payment"
, rc.ReceiptDate
  FROM MV_Order AS ord
 INNER JOIN MV_ClaimControl AS cc ON (cc.OrderSeq = ord.P_OrderSeq)
 INNER JOIN MV_Customer AS cmr ON (cmr.OrderSeq = ord.P_OrderSeq)
 INNER JOIN MV_Site AS st ON (st.SiteId = ord.SiteId)
 INNER JOIN T_SbpsReceiptControl AS rc ON (rc.OrderSeq = ord.OrderSeq)
 INNER JOIN MV_SbpsPayment AS sbps ON (sbps.PaymentName = rc.PaymentName AND sbps.OemId=ord.OemId)
 WHERE ord.OrderSeq = :OrderSeq
 AND rc.PayType = 1
 AND rc.ValidFlg = 1
Q_END;
        $orderClaim = $this->app->dbAdapter->query( $sql )->execute( $prm )->current();

        // 請求履歴（初回請求）
        $sql = <<<Q_END
SELECT
  ch.ClaimAmount
, oc.TaxAmount
 FROM MV_ClaimHistory ch
 INNER JOIN MV_OemClaimAccountInfo oc ON (oc.ClaimHistorySeq = ch.Seq)
 WHERE ch.OrderSeq = :OrderSeq
 AND ch.ClaimSeq = 1
 AND ch.ClaimPattern = 1
Q_END;
        $claimHistory = $this->app->dbAdapter->query( $sql )->execute( $prm )->current();

        // 領収書発行件数
        if ($pcFlag) {
            $receiptIssueHistoryCount = $params['ReceiptIssueHistoryCount'];
        } else {
            $sql = 'SELECT count(*) AS hisCnt FROM T_ReceiptIssueHistory rih WHERE rih.OrderSeq = :OrderSeq AND ValidFlg=1';
            $receiptIssueHistoryCount = $this->app->dbAdapter->query( $sql )->execute( $prm )->current()['hisCnt'];
        }

        // 伝票番号登録日
        $sql = <<<Q_END
SELECT MAX(IFNULL(oi.Deli_JournalIncDate, '0000-00-00 00:00:00')) AS Deli_JournalIncDate
 FROM MV_OrderItems AS oi
 WHERE oi.OrderSeq = :OrderSeq
 GROUP BY oi.OrderSeq
Q_END;
        $deliJournalIncDate = $this->app->dbAdapter->query( $sql )->execute( $prm )->current()['Deli_JournalIncDate'];

        // 加盟店情報取得
        $sql = <<<Q_END
SELECT
  ent.EnterpriseId
, ent.EnterpriseNameKj
, ent.PostalCode
, ent.PrefectureName
, ent.City
, ent.Town
, ent.Building
, ent.ContactPhoneNumber AS Phone
, ent.ContactFaxNumber AS Fax
, ent.CpNameKj AS RepNameKj
 FROM MV_Enterprise AS ent
 WHERE ent.EnterpriseId = :EnterpriseId
Q_END;
        $prm = array( ':EnterpriseId' => $orderClaim['EnterpriseId'] );
        $enterprise = $this->app->dbAdapter->query( $sql )->execute( $prm )->current();

        //
        $mdlMyToBack = new TableMypageToBackIF($this->app->dbAdapter);
        $prmMTB = array(
                'OrderSeq' => $orderClaim['OrderSeq'],
                'IFClass' => '4',
                'ValidFlg' => '1',
        );
        $myToBack = $mdlMyToBack->findMypageToBackIF($prmMTB, false)->current();
        if (!empty($myToBack)) {
            $orderClaim['CreditSettlementDecisionDate'] = $myToBack['RegistDate'];
        }

        // 領収書履歴登録
        if ( !$pcFlag ) {
            $mdlrih = new TableReceiptIssueHistory($this->app->dbAdapter);
            $dataRIH = array(
                    'OrderSeq' => $params['OrderSeq'],
                    'ReceiptIssueDate' => date('Y-m-d H:i:s'),
                    'RegistDate' => date('Y-m-d H:i:s'),
                    'RegistId' => '99',
            );
            $reId = $mdlrih->saveNew($dataRIH);
        }

        // PDF印刷
        $fileName = sprintf( 'Ryousyu_%s_%s.pdf', date( "YmdHis" ), $orderSeq );

        $this->view->assign( 'ReceiptIssueHistoryCount', $receiptIssueHistoryCount );
        $this->view->assign( 'Deli_JournalIncDate', $deliJournalIncDate );
        $this->view->assign( 'orderClaim', $orderClaim );
        $this->view->assign( 'enterprise', $enterprise );
        $this->view->assign( 'claimHistory', $claimHistory );
        $this->view->assign( 'documentRoot', $_SERVER['DOCUMENT_ROOT'] );
        $this->view->assign( 'title', $fileName );

        $this->setTemplate( 'receipt' );

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

        //unlink($fname_html);

        if ($pcFlag) {
            header( 'Content-Type: application/octet-stream; name="' . $outFileName . '"' );
            header( 'Content-Disposition: attachment; filename="' . $outFileName . '"' );
            header( 'Content-Length: ' . filesize( $fname_pdf ) );
        } else {
            header( 'Content-Type: application/pdf; name="' . $outFileName . '"' );
            header( 'Content-Disposition: inline; filename="' . $outFileName . '"' );
            header( 'Content-Length: ' . filesize( $fname_pdf ) );
        }

        // 出力
        echo readfile( $fname_pdf );

        unlink( $fname_pdf );
        die();
    }

    /**
     * 印刷処理（PC）
     */
    public function receiptpreviewAction()
    {
        return $this->pdfPreview(True);
    }

    /**
     * 印刷処理（スマホ）
     */
    public function receipt_pr_spAction()
    {
        return $this->pdfPreview(False);
    }


    /**
     * プレビュー表示
     */
    public function pdfPreview($pcFlag)
    {
        $params = $_SESSION[self::SESSION_JOB_PARAMS];

        $orderSeqA = $params['OrderSeq'];
        $orderSeqB = $this->userInfo->OrderSeq;

        if ( empty( $orderSeqA )
          || empty( $orderSeqB )
          || ( $orderSeqA <> $orderSeqB )
        ) {
            $this->_redirect( 'login/login' );
            return;
        }

        //注文SEQ
        $orderSeq = $params['OrderSeq'];

        $errors = array();
        // 注文SEQが指定されていなかった場合
        if( !isset( $orderSeq ) ) {
            $this->setTemplate( 'error' );

            return $this->view;
        }

        //SQL用パラメータ
        $prm = array(
                ':OrderSeq' => $orderSeq
        );

        // PDF用データ取得
        // 注文＋請求管理情報
        $sql = <<<Q_END
SELECT
  ord.OrderSeq
, ord.P_OrderSeq
, ord.OrderId
, cc.CreditSettlementDecisionDate
, cmr.NameKj
, ord.EnterpriseId
, st.ReceiptIssueProviso
, st.SiteNameKj
, CONCAT("※",sbps.MailParameterNameKj,"にてお支払い") as "Payment"
, rc.ReceiptDate
  FROM MV_Order AS ord
 INNER JOIN MV_ClaimControl AS cc ON (cc.OrderSeq = ord.P_OrderSeq)
 INNER JOIN MV_Customer AS cmr ON (cmr.OrderSeq = ord.P_OrderSeq)
 INNER JOIN MV_Site AS st ON (st.SiteId = ord.SiteId)
 INNER JOIN T_SbpsReceiptControl AS rc ON (rc.OrderSeq = ord.OrderSeq)
 INNER JOIN MV_SbpsPayment AS sbps ON (sbps.PaymentName = rc.PaymentName AND sbps.OemId=ord.OemId)
 WHERE ord.OrderSeq = :OrderSeq
 AND rc.PayType = 1
 AND rc.ValidFlg = 1
Q_END;
        $orderClaim = $this->app->dbAdapter->query( $sql )->execute( $prm )->current();
        $this->view->assign( 'paymentMethodName', $orderClaim['Payment'] );

        // 請求履歴（初回請求）
        $sql = <<<Q_END
SELECT
  ch.ClaimAmount
, oc.TaxAmount
 FROM MV_ClaimHistory ch
 INNER JOIN MV_OemClaimAccountInfo oc ON (oc.ClaimHistorySeq = ch.Seq)
 WHERE ch.OrderSeq = :OrderSeq
 AND ch.ClaimSeq = 1
 AND ch.ClaimPattern = 1
Q_END;
        $claimHistory = $this->app->dbAdapter->query( $sql )->execute( $prm )->current();

        // 領収書発行件数
        $sql = <<<Q_END
SELECT
  count(*) AS hisCnt
 FROM T_ReceiptIssueHistory rih
 WHERE rih.OrderSeq = :OrderSeq
AND rih.ValidFlg = 1
Q_END;
        $receiptIssueHistoryCount = $this->app->dbAdapter->query( $sql )->execute( $prm )->current()['hisCnt'];

        // 伝票番号登録日
        $sql = <<<Q_END
SELECT MAX(IFNULL(oi.Deli_JournalIncDate, '0000-00-00 00:00:00')) AS Deli_JournalIncDate
 FROM MV_OrderItems AS oi
 WHERE oi.OrderSeq = :OrderSeq
 GROUP BY oi.OrderSeq
Q_END;
        $deliJournalIncDate = $this->app->dbAdapter->query( $sql )->execute( $prm )->current()['Deli_JournalIncDate'];

        // 加盟店情報取得
        $sql = <<<Q_END
SELECT
  ent.EnterpriseId
, ent.EnterpriseNameKj
, ent.PostalCode
, ent.PrefectureName
, ent.City
, ent.Town
, ent.Building
, ent.ContactPhoneNumber AS Phone
, ent.ContactFaxNumber AS Fax
, ent.CpNameKj AS RepNameKj
 FROM MV_Enterprise AS ent
 WHERE ent.EnterpriseId = :EnterpriseId
Q_END;
        $prm = array( ':EnterpriseId' => $orderClaim['EnterpriseId'] );
        $enterprise = $this->app->dbAdapter->query( $sql )->execute( $prm )->current();

        //
        $mdlMyToBack = new TableMypageToBackIF($this->app->dbAdapter);
        $prmMTB = array(
                'OrderSeq' => $orderClaim['OrderSeq'],
                'IFClass' => '4',
                'ValidFlg' => '1',
        );
        $myToBack = $mdlMyToBack->findMypageToBackIF($prmMTB, false)->current();
        if (!empty($myToBack)) {
            $orderClaim['CreditSettlementDecisionDate'] = $myToBack['RegistDate'];
        }

        // 領収書履歴登録
        $mdlrih = new TableReceiptIssueHistory($this->app->dbAdapter);
        $dataRIH = array(
                'OrderSeq' => $params['OrderSeq'],
                'ReceiptIssueDate' => date('Y-m-d H:i:s'),
                'RegistDate' => date('Y-m-d H:i:s'),
                'RegistId' => '99',
        );
        $reId = $mdlrih->saveNew($dataRIH);

        $this->view->assign( 'ReceiptIssueHistoryCount', $receiptIssueHistoryCount );
        $this->view->assign( 'Deli_JournalIncDate', $deliJournalIncDate );
        $this->view->assign( 'orderClaim', $orderClaim );
        $this->view->assign( 'enterprise', $enterprise );
        $this->view->assign( 'claimHistory', $claimHistory );
        $this->view->assign( 'documentRoot', $_SERVER['DOCUMENT_ROOT'] );
        $this->setPageTitle( '領収書プレビュー' );

        $prm = array(
                'ReceiptIssueHistoryCount' => $receiptIssueHistoryCount,
                'OrderSeq'                 => $orderSeq,
        );

        $_SESSION[self::SESSION_JOB_PARAMS] = $prm;

        return $this->view;
    }
}
