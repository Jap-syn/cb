<?php
namespace cbadmin\Controller;

use Zend\Config\Reader\Ini;
use Zend\Json\Json;
use Coral\Base\BaseHtmlUtils;
use Coral\Base\BaseGeneralUtils;
use Coral\Base\IO\BaseIOUtility;
use Coral\Coral\Controller\CoralControllerAction;
use Coral\Coral\CoralCodeMaster;
use cbadmin\Application;
use models\Logic\LogicTemplate;

class EntManagementController extends CoralControllerAction {
    protected $_componentRoot = './application/views/components';

    /**
     * @var Application
     */
    protected $app;

    /**
     * Controllerを初期化する
     */
    public function _init() {

        $this->app = Application::getInstance();

        $userInfo = $this->app->authManagerAdmin->getUserInfo();
        $this->view->assign('userInfo', $userInfo );

        $this->addStyleSheet('../css/default02.css');
        $this->addJavaScript('../js/prototype.js');
        $this->addJavaScript('../js/json.js');

        $this->setPageTitle("後払い.com - 事業者管理統計");
        $this->view->assign( 'current_action', $this->getActionName() );
    }

    /**
     * 画面表示
     */
    public function indexAction()
    {
        // 対象年月
        $yearMonthList = $this->getYearMonthList();
        $yearMonthListTag = BaseHtmlUtils::SelectTag('yearMonth', $yearMonthList);

        // ファイル種類
        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
        $fileType = $codeMaster->getEntManagementFileTypeMaster();
        $fileTypeTag = BaseHtmlUtils::SelectTag('fileType', $fileType, 0);

        // ビューへアサイン
        $this->view->assign('yearMonth', $yearMonthListTag);
        $this->view->assign('fileType', $fileTypeTag);

        return $this->view;
    }

    /**
     * 対象年月リストを取得
     *
     * @return array 対象年月リスト
     */
    protected function getYearMonthList()
    {
        // ↓↓↓会計テーブルの名称等が古い情報で作成されたSQLのため、全体的に見直し↓↓↓
        // SQL作成
        $sql = <<<EOQ
SELECT  DATE_FORMAT(AccountDate, '%Y%m01') AS Code
    ,   DATE_FORMAT(AccountDate, '%Y年%m月度') AS Caption
FROM    AT_DailyStatisticsTable
WHERE   DailyMonthlyFlg = 1         /* 月次 */
AND     M_ChargeCount > 0           /* 当月立替件数が 0 より大きいもの */
UNION
SELECT  DATE_FORMAT(AccountDate, '%Y%m01') AS Code
    ,   DATE_FORMAT(AccountDate, '%Y年%m月度') AS Caption
FROM    AT_Oem_DailyStatisticsTable
WHERE   DailyMonthlyFlg = 1         /* 月次 */
AND     M_ChargeCount > 0           /* 当月立替件数が 0 より大きいもの */
ORDER BY
        Code;
EOQ;
        // ↑↑↑会計テーブルの名称等が古い情報で作成されたSQLのため、全体的に見直し↑↑↑

        // SQL実行
        $result = ResultInterfaceToArray($this->app->dbAdapter->query($sql)->execute(null));

        // Code => Caption 形式に変更
        $list = array();
        foreach ($result as $row) {
            $list[$row['Code']] = $row['Caption'];
        }

        return $list;
    }

    /**
     *  CSVダウンロード
     */
    public function dlcsvAction()
    {
        // パラメータ取得
        $params = $this->getParams();
        $yearMonth = $params['yearMonth'];  // 対象年月
        $fileType = $params['fileType'];    // ファイル種類

        // 対象年月がない場合リダイレクト
        if (strlen($yearMonth) == 0) {
            return $this->_redirect('entmanagement/index');
        }

        // ファイル種類
        if ($fileType == 0) {
            // 事業者別売上
            $datas = $this->getEnterpriseManagementStatistics($yearMonth);

            $templateId = 'CKI13082_2';
            $fileName = sprintf( 'EnterpriseManagementStatistics_%s.csv', date('YmdHis'));
        }
        else {
            // 売上統計
            $datas = $this->getTotalSalesStatistics($yearMonth);

            $templateId = 'CKI13082_1';
            $fileName = sprintf( 'TotalSalesStatistics_%s.csv', date('YmdHis'));
        }

        // CSVファイルダウンロード
        $templateClass = 0;
        $seq = 0;
        $templatePattern = 0;

        $logicTemplate = new LogicTemplate( $this->app->dbAdapter );
        $response = $logicTemplate->convertArraytoResponse( $datas, $fileName, $templateId, $templateClass, $seq, $templatePattern, $this->getResponse() );

        if( $response == false ) {
            throw new \Exception( $logicTemplate->getErrorMessage() );
        }

        return $response;
    }

    /**
     * 事業者別売上データ取得
     *
     * @param unknown $yearMonth 対象年月
     *
     * @return array 事業者別売上データ
     */
    protected function getEnterpriseManagementStatistics($yearMonth) {
        // 2015/12/16 Y.Suzuki Del Stt
        // 確定した会計テーブルにYmdで保持している日付項目が存在するため、引数でYmdを渡し、Y、m、d の分割処理は削除
//         // 年月切り出し
//         $year = substr($yearMonth, 0, 4);
//         $month = substr($yearMonth, 4, 2);

//         // 1ヶ月前
//         $sfDate = date('Ym', strtotime($yearMonth.'-01 -1 month'));
//         $sfYear = substr($sfDate, 0, 4);
//         $sfMonth = substr($sfDate, 4, 2);
        // 2015/12/16 Y.Suzuki Del End

        // ↓↓↓会計テーブルの名称等が古い情報で作成されたSQLのため、全体的に見直し↓↓↓
        // SQL作成
        $sql = <<<EOQ
SELECT  CONCAT(DATE_FORMAT(DATE_ADD(:AccountDate, INTERVAL -1 MONTH), '%Y-%m'), '-13T00:00:00Z') AS PublishingDate
    ,   dst.EnterpriseId
    ,   (dst.M_ChargeCount - dst.M_CancelCount) AS OrderCount
    ,   CONCAT(DATE_FORMAT(:AccountDate, '%Y-%m'), '　売上概算　', dst.EnterpriseNameKj) AS SubjectName
    ,   '掲載' AS Yomi
    ,   dst.M_AllTotal AS SettlementFee
    ,   dst.EnterpriseNameKj
    ,   e.PublishingConfirmDate
    ,   dst.M_ChargeCount AS ChargeCount
    ,   dst.M_ChargeAmount AS totalUseAmount
    ,   IFNULL(o.OutstandingCount, 0) AS OutstandingCount
    ,   IFNULL(o.OutstandingAmount, 0) AS OutstandingAmount
    ,   e.Salesman
FROM    AT_DailyStatisticsTable dst
        INNER JOIN T_Enterprise e ON (e.EnterpriseId = dst.EnterpriseId)
        LEFT OUTER JOIN (SELECT EnterpriseId
                            ,   COUNT(*) AS OutstandingCount
                            ,   SUM(ReceivablesRemainingAmount) AS OutstandingAmount
                         FROM   AT_Consumer_AccountsDue
                         WHERE  SalesDefiniteDate BETWEEN DATE_FORMAT(:AccountDate, '%Y-%m-%d') AND LAST_DAY(DATE_FORMAT(:AccountDate, '%Y-%m-%d'))
                         GROUP BY
                                EnterpriseId
                        ) o ON (o.EnterpriseId = dst.EnterpriseId)
WHERE   dst.AccountDate = DATE_FORMAT(:AccountDate, '%Y-%m-%d')
AND     dst.DailyMonthlyFlg = 1
AND     dst.M_ChargeAmount > 0
UNION ALL
SELECT  CONCAT(DATE_FORMAT(DATE_ADD(:AccountDate, INTERVAL -1 MONTH), '%Y-%m'), '-13T00:00:00Z') AS PublishingDate
    ,   dst.EnterpriseId
    ,   (dst.M_ChargeCount - dst.M_CancelCount) AS OrderCount
    ,   CONCAT(DATE_FORMAT(:AccountDate, '%Y-%m'), '　売上概算　', dst.EnterpriseNameKj) AS SubjectName
    ,   '掲載' AS Yomi
    ,   dst.M_AllTotal AS SettlementFee
    ,   dst.EnterpriseNameKj
    ,   e.PublishingConfirmDate
    ,   dst.M_ChargeCount AS ChargeCount
    ,   dst.M_ChargeAmount AS totalUseAmount
    ,   IFNULL(o.OutstandingCount, 0) AS OutstandingCount
    ,   IFNULL(o.OutstandingAmount, 0) AS OutstandingAmount
    ,   e.Salesman
FROM    AT_Oem_DailyStatisticsTable dst
        INNER JOIN T_Enterprise e ON (e.EnterpriseId = dst.EnterpriseId)
        LEFT OUTER JOIN (SELECT EnterpriseId
                            ,   COUNT(*) AS OutstandingCount
                            ,   SUM(OemSuspensePayments) AS OutstandingAmount
                         FROM   AT_Oem_SuspensePayments
                         WHERE  SalesDefiniteDate BETWEEN DATE_FORMAT(:AccountDate, '%Y-%m-%d') AND LAST_DAY(DATE_FORMAT(:AccountDate, '%Y-%m-%d'))
                         GROUP BY
                                EnterpriseId
                        ) o ON (o.EnterpriseId = dst.EnterpriseId)
WHERE   dst.AccountDate = DATE_FORMAT(:AccountDate, '%Y-%m-%d')
AND     dst.DailyMonthlyFlg = 1
AND     dst.M_ChargeAmount > 0
ORDER BY
        EnterpriseId
EOQ;

        // SQL実行
        $result = ResultInterfaceToArray($this->app->dbAdapter->query($sql)->execute(array(':AccountDate' => $yearMonth)));
        // ↑↑↑会計テーブルの名称等が古い情報で作成されたSQLのため、全体的に見直し↑↑↑

        return $result;
    }

    /**
     * 売上統計データ取得
     *
     * @param unknown $yearMonth 対象年月
     *
     * @return array 売上統計データ
     */
    protected function getTotalSalesStatistics($yearMonth) {
        // 2015/12/16 Y.Suzuki Del Stt
        // 確定した会計テーブルにYmdで保持している日付項目が存在するため、引数でYmdを渡し、Y、m、d の分割処理は削除
//         // 年月切り出し
//         $year = substr($yearMonth, 0, 4);
//         $month = substr($yearMonth, 4, 2);
        // 2015/12/16 Y.Suzuki Del End

        // ↓↓↓会計テーブルの名称等が古い情報で作成されたSQLのため、全体的に見直し↓↓↓
        $sql = <<<EOQ
SELECT  DATE_FORMAT(:AccountDate, '%Y-%m') AS TargetMonth
    ,   SUM(M_AllTotal) AS TotalSales
    ,   SUM(M_SettlementFee) AS SettlementFee
    ,   SUM(M_ClaimFee + M_ClaimFeeTax) AS ClaimFee
    ,   SUM(M_MonthlyFee + M_MonthlyFeeTax +
            M_IncludeMonthlyFee + M_IncludeMonthlyFeeTax +
            M_ApiMonthlyFee + M_ApiMonthlyFeeTax +
            M_CreditNoticeMonthlyFee + M_CreditNoticeMonthlyFeeTax +
            M_NCreditNoticeMonthlyFee + M_NCreditNoticeMonthlyFeeTax +
            M_ReserveMonthlyFee + M_ReserveMonthlyFeeTax
           ) AS MonthlyFee
    ,   SUM(M_ChargeCount) AS OrderCount
    ,   SUM(M_ChargeAmount) AS PayingAmount
    ,   SUM(M_ReceiptCount) AS PaymentAllocatedCount
    ,   SUM(M_ReceiptAmount) AS PaymentAllocatedAmount
    ,   SUM(M_CancelCount) AS CancelCount
FROM    AT_DailyStatisticsTable
WHERE   AccountDate = DATE_FORMAT(:AccountDate, '%Y-%m-%d')
AND     DailyMonthlyFlg = 1
UNION ALL
SELECT  DATE_FORMAT(:AccountDate, '%Y-%m') AS TargetMonth
    ,   SUM(M_AllTotal) AS TotalSales
    ,   SUM(M_SettlementFee + M_SettlementFeeTax) AS SettlementFee
    ,   SUM(M_ClaimFee + M_ClaimFeeTax) AS ClaimFee
    ,   SUM(M_MonthlyFee + M_MonthlyFeeTax +
            M_OemIncludeMonthlyFee + M_OemIncludeMonthlyFeeTax +
            M_OemApiMonthlyFee + M_OemApiMonthlyFee +
            M_OemCreditNoticeMonthlyFee + M_OemCreditNoticeMonthlyFeeTax +
            M_OemNCreditNoticeMonthlyFee + M_OemNCreditNoticeMonthlyFeeTax +
            M_OemReserveMonthlyFee + M_OemReserveMonthlyFeeTax
           ) AS MonthlyFee
    ,   SUM(M_ChargeCount) AS OrderCount
    ,   SUM(M_ChargeAmount) AS PayingAmount
    ,   SUM(M_ReceiptCount) AS PaymentAllocatedCount
    ,   SUM(M_ReceiptAmount) AS PaymentAllocatedAmount
    ,   SUM(M_CancelCount) AS CancelCount
FROM    AT_Oem_DailyStatisticsTable
WHERE   AccountDate = DATE_FORMAT(:AccountDate, '%Y-%m-%d')
AND     DailyMonthlyFlg = 1
EOQ;

        $result = ResultInterfaceToArray($this->app->dbAdapter->query($sql)->execute(array(':AccountDate' => $yearMonth)));

        // 未収分
        // 直営とOEMで参照しているテーブルが違うのでUNIONする
        $sql = <<<EOQ
SELECT  COUNT(*) AS OutstandingCount
    ,   SUM(ReceivablesRemainingAmount) AS OutstandingAmount
FROM    AT_Consumer_AccountsDue
WHERE   SalesDefiniteDate BETWEEN DATE_FORMAT(:AccountDate, '%Y-%m-%d') AND LAST_DAY(DATE_FORMAT(:AccountDate, '%Y-%m-%d'))
UNION ALL
SELECT  COUNT(*) AS OutstandingCount
    ,   SUM(OemSuspensePayments) AS OutstandingAmount
FROM    AT_Oem_SuspensePayments
WHERE   SalesDefiniteDate BETWEEN DATE_FORMAT(:AccountDate, '%Y-%m-%d') AND LAST_DAY(DATE_FORMAT(:AccountDate, '%Y-%m-%d'))
EOQ;

        $ri = $this->app->dbAdapter->query($sql)->execute(array(':AccountDate' => $yearMonth));
        // ↑↑↑会計テーブルの名称等が古い情報で作成されたSQLのため、全体的に見直し↑↑↑

        if ($ri->count() > 0) {
            $data = $ri->current();
            $result[0]['OutstandingCount'] = $data['OutstandingCount'];
            $result[0]['OutstandingAmount'] = $data['OutstandingAmount'] == null ? 0 : $data['OutstandingAmount'];
        }
        else {
            $result[0]['OutstandingCount'] = 0;
            $result[0]['OutstandingAmount'] = 0;
        }

        // 再請求
        $sql = <<<EOQ
SELECT  SUM(CASE WHEN ClaimPattern = 2 THEN 1 ELSE 0 END)           AS ReClaimCount
,       SUM(CASE WHEN ClaimPattern = 2 THEN ClaimAmount ELSE 0 END) AS ReClaimAmount
,       SUM(CASE WHEN ClaimPattern = 4 THEN 1 ELSE 0 END)           AS ReReClaimCount
,       SUM(CASE WHEN ClaimPattern = 4 THEN ClaimAmount ELSE 0 END) AS ReReClaimAmount
  FROM  T_ClaimHistory
 WHERE  ClaimDate BETWEEN DATE_FORMAT(:AccountDate, '%Y-%m-%d') AND LAST_DAY(DATE_FORMAT(:AccountDate, '%Y-%m-%d'))     /* YMの分割を廃止したので修正 */
EOQ;

        $ri = $this->app->dbAdapter->query($sql)->execute(array(':AccountDate' => $yearMonth));

        if ($ri->count() > 0) {
            $data = $ri->current();
            $result[0]['ReClaimCount'] = $data['ReClaimCount'];
            $result[0]['ReClaimAmount'] = $data['ReClaimAmount'];
            $result[0]['ReReClaimCount'] = $data['ReReClaimCount'];
            $result[0]['ReReClaimAmount'] = $data['ReReClaimAmount'];
        }
        else {
            $result[0]['ReClaimCount'] = 0;
            $result[0]['ReClaimAmount'] = 0;
            $result[0]['ReReClaimCount'] = 0;
            $result[0]['ReReClaimAmount'] = 0;
        }

        // 振り込み手数料・印紙代
        $sql = <<<EOQ
SELECT  SUM(StampFeeTotal) AS FfTransferFee
,       SUM(TransferCommission) AS StanpFee
  FROM  T_PayingControl
 WHERE  DecisionDate BETWEEN DATE_FORMAT(:AccountDate, '%Y-%m-%d') AND LAST_DAY(DATE_FORMAT(:AccountDate, '%Y-%m-%d'))     /* YMの分割を廃止したので修正 */
EOQ;

        $ri = $this->app->dbAdapter->query($sql)->execute(array(':AccountDate' => $yearMonth));

        if ($ri->count() > 0) {
            $data = $ri->current();
            $result[0]['FfTransferFee'] = $data['FfTransferFee'];
            $result[0]['StanpFee'] = $data['StanpFee'];
        }
        else {
            $result[0]['FfTransferFee'] = 0;
            $result[0]['StanpFee'] = 0;
        }

        // 計算が必要な項目
        $orderCount = $result[0]['OrderCount'] == null ? 0 : $result[0]['OrderCount'];
        if ($orderCount > 0) {
            // 注文平均単価
            $result[0]['AverageUnitPrice'] = (int)($result[0]['TotalSales'] / $orderCount);
            // 入金済入金率(件数）
            $result[0]['PaymentAllocatedRate'] = (int)($result[0]['PaymentAllocatedCount'] / $orderCount * 100);
            // 未収率（件数）
            $result[0]['OutstandingRate'] = (int)($result[0]['OutstandingCount'] / $orderCount * 100);
            // ｷｬﾝｾﾙ率
            $result[0]['CancelRate'] = (int)($result[0]['CancelCount'] / $orderCount * 100);
            // 再請求率（件数）
            $result[0]['ReClaimRate'] = (int)($result[0]['ReClaimCount'] / $orderCount * 100);
            // 再々請求率（件数）
            $result[0]['ReReClaimRate'] = (int)($result[0]['ReReClaimCount'] / $orderCount * 100);
        }
        else {
            // 注文平均単価
            $result[0]['AverageUnitPrice'] = 0;
            // 入金済入金率(件数）
            $result[0]['PaymentAllocatedRate'] = 0;
            // 未収率（件数）
            $result[0]['OutstandingRate'] = 0;
            // ｷｬﾝｾﾙ率
            $result[0]['CancelRate'] = 0;
            // 再請求率（件数）
            $result[0]['ReClaimRate'] = 0;
            // 再々請求率（件数）
            $result[0]['ReReClaimRate'] = 0;
        }

        return $result;
    }
}