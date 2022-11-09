<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace cbadmin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Coral\Base\BaseGeneralUtils;
use Coral\Coral\Controller\CoralControllerAction;
use cbadmin\Application;
use cbadmin\classes\SearchfCache;
use DateTime;
use models\Table\TableOrder;
use models\Table\TableSystemStatus;
use models\View\ViewOrderCustomer;
use models\View\ViewWaitForCancelConfirm;
use models\View\ViewWaitForFirstClaim;
use models\View\ViewChargeConfirm;

use models\Table\TableThreadPool;
use models\Logic\Jnb\Account\LogicJnbAccountReceipt;
use models\Logic\Jnb\LogicJnbAccount;
use models\Logic\Jnb\Account\Receipt\LogicJnbAccountReceiptManual;
use models\Logic\Smbcpa\Account\LogicSmbcpaAccountReceipt;
use models\Logic\Smbcpa\LogicSmbcpaAccount;
use models\Logic\Smbcpa\Account\Receipt\LogicSmbcpaAccountReceiptManual;
use models\Table\TableEnterpriseMailReceivedHistory;
use models\Table\TableCreditJudgeLock;
use models\Table\TableSBPaymentSendResultHistory;
use models\Table\TablePayeasyError;

class IndexController extends CoralControllerAction
{
    protected $_componentRoot = './application/views/components';

    /**
     * アプリケーションオブジェクト
     * @var Application
     */
    private $app;

    public function _init()
	{
        $this->app = Application::getInstance();

        $this->view->assign('userInfo', $this->app->authManagerAdmin->getUserInfo());

        $this->addStyleSheet('../css/top.css');

        $this->setPageTitle("後払い.com - トップ");
	}

    public function indexAction()
    {
        $mdloc = new ViewOrderCustomer($this->app->dbAdapter);
        $mdlo = new TableOrder($this->app->dbAdapter);
        $mdlss = new TableSystemStatus($this->app->dbAdapter);
        $mdlcj = new TableCreditJudgeLock($this->app->dbAdapter);
        $mdlwfcc = new ViewWaitForCancelConfirm($this->app->dbAdapter);
        $mdlwffc = new ViewWaitForFirstClaim($this->app->dbAdapter);
        $mdlvcc = new ViewChargeConfirm($this->app->dbAdapter);
        $mdlSBPsrh = new TableSBPaymentSendResultHistory( $this->app->dbAdapter );
        $mdlPyE = new TablePayeasyError( $this->app->dbAdapter );

        $today = date('Y-m-d');
        $highlight['Today'] = BaseGeneralUtils::getDateString($today);                  // 今日の日付

        $count_by_ds = $mdlo->getCountDsForTop();

        $count_by_ds[11] = $this->app->dbAdapter->query(" SELECT COUNT(1) AS CNT FROM T_Order o,AT_Order ao WHERE o.OrderSeq = ao.OrderSeq AND o.DataStatus = 11 AND o.Cnl_Status = 0 AND ao.DefectFlg = 0 ")->execute(null)->current()['CNT'];

        $highlight['rw11count'] = $count_by_ds[11];                                     // 社内与信実行待ち件数
        $highlight['rw15count'] = $count_by_ds[15];                                     // 社内与信確定待ち件数
        $highlight['rw21count'] = $count_by_ds[21];                                     // 社内与信保留待ち件数

        $highlight['rw41Acount'] = $mdlwffc->getToPrintCount();                         // 請求書印刷ジョブ転送待ち件数
        $highlight['rw41Bcount'] = $mdlwffc->getPrintedCount();                         // 請求書印刷完了待ち件数

        $sDate = date('Y-m-d', strtotime('-8 day'));

        $highlight['reclaimcount'] = $mdloc->findReClaimTargetCount(null, $sDate, 1);   // 経過日数8日以上の件数

        $highlight['cancelcount'] = $mdlwfcc->getAllCount();                            // キャンセル確認待ち件数

        // 「要立替」の表示制御
        if ($this->isMustCharge()) {
            $highlight['mustcharge'] = '［要立替］';
        }

        $judgeProcessingList = $mdlcj->getProcessing();
        if ($judgeProcessingList != '') {
            $highlight['isJudgeProcessing'] = '自動与信実行中(<font size="2">実行ｽﾚｯﾄﾞ ' . $judgeProcessingList . '</font>)';
        }
        else {
            $highlight['isJudgeProcessing'] = '';
        }

        $this->view->assign('highlight', $highlight);

// ↓↓↓権限はロール単位のメニュー制御に変更する
//         if ($this->app->authManagerAdmin->getUserInfo()->RoleCode > 1)
//         {
//             // 管理者またはスーパーユーザー
//             $this->view->assign('isAdmin', 'yes');
//         }
//         else
//         {
//             // 一般ユーザー
//             $this->view->assign('isAdmin', 'no');
//         }
// ↑↑↑権限はロール単位のメニュー制御に変更する

        // JS割り当て
        $this->addJavaScript( '../js/prototype.js' );
        $this->addJavaScript( '../js/corelib.js' );

        // 不払い債権関連の初期化
        $fsummary = array();
        $confs = array(
                'one_month_ago' => array(
                        'label' => '不払い１ヶ月は',
                        'days' => 30
                ),
                'three_months_ago' => array(
                        'label' => '不払い３ヶ月は',
                        'days' => 90
                )
        );

        foreach($confs as $key => $conf) {
            $summary = array_merge(array(), $conf);

            $d = date('Y-m-d');
            $d = date('Y-m-d', strtotime($d . " -" . $conf['days'] . " day"));

//             $cache = new SearchfCache();
//             $cache->setDbAdapter($this->app->dbAdapter);

//             $cache->buildExpressions(array('LimitDateF' => $d, 'LimitDateT' => $d));
//             $cache->getResults();

//             $map = $cache->getSummaries();
//             $summary['date'] = $d;
//             $summary['count'] = $map[SearchfCache::SUMMARY_KEY_TOTALCOUNT];
//             $summary['amount'] = $map[SearchfCache::SUMMARY_KEY_TOTALUSEAMOUNT];
            $summary['url'] = sprintf('searchf/directsearch/first_limit_date/%s', $d);

            $fsummary[$key] = $summary;
        }
        $this->view->assign('fubarai_summary', $fsummary);

        // JNB手動入金待ち件数
        $jnbManLogic = new LogicJnbAccountReceiptManual($this->app->dbAdapter);
        $count = $jnbManLogic->getManualReceiptTargetsWithOrderSeq()->count();

         $count += $jnbManLogic->getManualReceiptTargetsWithoutOrderSeq()->count();
        $this->view->assign('jnb_rcpt_count', $count);

        // SMBCバーチャル口座手動入金待ち件数
        $smbcpaManLogic = new LogicSmbcpaAccountReceiptManual($this->app->dbAdapter);
        $count = $smbcpaManLogic->getManualReceiptTargetsWithOrderSeq()->count();

        $count += $smbcpaManLogic->getManualReceiptTargetsWithoutOrderSeq()->count();
        $this->view->assign('smbcpa_rcpt_count', $count);

        // 複数注文に不当に割り当てられている可能性があるJNB口座情報をビューに割り当てる
//         $this->view->assign('invalid_jnb_accounts', $this->getMultiOpenedJnbAccounts());
        $sql = "SELECT COUNT(1) AS cnt FROM T_ClaimError ce WHERE 1 = 1";
        // 発生日時
        $wRegistDate = BaseGeneralUtils::makeWhereDateTime(
            'ce.RegistDate',
            BaseGeneralUtils::convertWideToNarrow(date("Y-m-d", strtotime("-1 day"))),
            BaseGeneralUtils::convertWideToNarrow(date("Y-m-d"))
        );
        $sql .= " AND " . $wRegistDate;

        $ceCount = $this->app->dbAdapter->query($sql)->execute(null)->current()['cnt'];
        $this->view->assign('invalid_jnb_accounts', $ceCount);

        // システム日付を取得する
        $dateTime = new DateTime();
        $RegistDateT = $dateTime->format('Y-m-d');
        $dateTime->modify('-1 day');
        $RegistDateF = $dateTime->format('Y-m-d');

        //SB連携エラー件数
        $sbNgCnt = $mdlSBPsrh->getNgRegistDateBetween($RegistDateF, $RegistDateT)->count();
        $this->view->assign('invalid_jnb_SB_accounts', $sbNgCnt);

        //PayEasy連携エラー件数
        $pyeNgCnt = $mdlPyE->getNgCountDateDesignation($RegistDateF, $RegistDateT)->current()['cnt'];
        $this->view->assign('invalid_jnb_PyE_accounts', $pyeNgCnt);

        // JNB自動入金でロック獲得に失敗したスレッド数（→ 多いと実行プロセスが停止の可能性あり）
        $this->view->assign('jnb_rcpt_abend_count', $this->getJnbAutoReceiptAbendThreads());

        // 口座振替アラート
        $sql = " SELECT COUNT(*) cnt FROM (SELECT DISTINCT cta.Seq,o.OrderSeq,o.OrderId,e.EnterpriseNameKj,cta.EntCustSeq,c.EntCustId FROM T_CreditTransferAlert cta INNER JOIN T_Order o ON cta.OrderSeq=o.OrderSeq INNER JOIN T_Enterprise e ON cta.EnterpriseId=e.EnterpriseId INNER JOIN T_Customer c ON cta.EntCustSeq=c.EntCustSeq AND c.EntCustId <> '' WHERE cta.ValidFlg=1) a ";
        $ctCount = $this->app->dbAdapter->query($sql)->execute(null)->current()['cnt'];
        $this->view->assign('credit_transfer_alert_count', $ctCount);

        //MUFJ入金エラー件数件数
        $wRegistDate = BaseGeneralUtils::makeWhereDateTime(
            'mr.RegistDate',
            BaseGeneralUtils::convertWideToNarrow(date('Y-m-d', strtotime("-1 day"))),
            BaseGeneralUtils::convertWideToNarrow(date('Y-m-d'))
        );
        $sql = " SELECT COUNT(*) cnt FROM T_MufjReceipt mr WHERE mr.ValidFlg = 1 AND mr.ProcessClass = 1 AND mr.ErrorFlg = 1 AND ".$wRegistDate;
        $mufj_error_Count = $this->app->dbAdapter->query($sql)->execute(null)->current()['cnt'];
        $this->view->assign('invalid_mufj_accounts', $mufj_error_Count);

// ↓↓↓メニューの権限制御は別途行うため、従来のisAdminは常にyesとする
        $this->view->assign('isAdmin', 'yes');
// ↑↑↑メニューの権限制御は別途行うため、従来のisAdminは常にyesとする

        // 滞留アラートがあるか否か
        // $this->view->assign('isStagnationAlert', $this->isStagnationAlert());

        // 加盟店追加ﾊﾞｯﾁ件数
        $this->view->assign('ent_add_count', $this->getEnterpriseAddBatchCount());

        // メニューの権限情報を取得する
        $this->view->assign('menu_auth_info', $this->getMenuAuthorityInfo());

        // CB向けお知らせ
        $notice = $this->app->dbAdapter->query(" SELECT IFNULL(Note,'') AS Note FROM M_Code WHERE CodeId = 5 AND KeyCode = 0 "
        )->execute(null)->current()['Note'];
        if ($notice != '') {
            $this->view->assign('notice', $notice);
        }

        // バッチの異常終了チェック
        if ($this->isBatchErrorOn()) {
            $this->view->assign('batcherror', true);
        }

        return $this->view;
    }

// Del By Takemasa(NDC) 20150212 Stt 未使用故コメントアウト化
//     /**
//      * highlightアクション
//      */
//     public function highlightAction()
//     {
//         $mdloc = new View_OrderCustomer($this->app->dbAdapter);
//         $mdlo = new Table_Order($this->app->dbAdapter);
//         $mdlpas = new Table_PayingAndSales($this->app->dbAdapter);
//         $mdle = new Table_Enterprise($this->app->dbAdapter);
//         $mdlss = new Table_SystemStatus($this->app->dbAdapter);
//         $mdlac = new View_ArrivalConfirm($this->app->dbAdapter);
//         $mdlwfcc = new View_WaitForCancelConfirm($this->app->dbAdapter);
//         $mdlwffc = new View_WaitForFirstClaim($this->app->dbAdapter);
//         $mdlvcc = new View_ChargeConfirm($this->app->dbAdapter);
//
//         $highlight['Today'] = NetB_GeneralUtils::getDateString(new Zend_Date);                // 今日の日付
//         $highlight['OrderCountToday'] = $mdlo->getOrderCountToday();                        // 本日注文登録数
//         $highlight['OrderCountYesterday'] = $mdlo->getOrderCountYesterday();                // 昨日注文登録数
//         $highlight['UriageToday'] = $mdlpas->getUriageToday();                                // 本日売上金額
//         $highlight['UriageYesterday'] = $mdlpas->getUriageYesterday();                        // 昨日売上金額
//         $highlight['Unpayment'] = $mdlo->getUnpayment();                                    // 未入金額
//         $highlight['EnterpriseCount'] = $mdle->getAllValidEnterprises()->count();            // 事業者数
//         $highlight['CreditCountToday'] = $mdlo->getCreditCountToday();                        // 本日社内与信確定件数
//         $highlight['CreditCountYesterday'] = $mdlo->getCreditCountYesterday();                // 昨日社内与信確定件数
//
//         $highlight['rw31count'] = $mdlo->getCountDs(31);                                    // 伝票入力待ち件数
//         $highlight['rwarvlcount'] = 0;                                                        // 着荷確認待ち件数件数（取得方法変更 10.5.27 eda）
//         $this->app->addClass('Table_DeliMethod');
//         $mdldm = new Table_DeliMethod($this->app->dbAdapter);
//         $dmethods = array();
//         foreach($mdldm->getValidAll() as $row) $dmethods[] = $row->DeliMethodId;
//         foreach($mdlac->getArrivalConfirmCount2() as $method_id => $cnt) {
//             if( ! in_array($method_id, $dmethods) ) continue;
//             // 有効な配送方法のもののみ計上
//             $highlight['rwarvlcount'] += $cnt;
//         }
//         $highlight['rw51count'] = $mdlo->getCountDs(51);                                    // 入金確認待ち件数
//
//         $this->view->assign('highlight', $highlight);
//
//         // JS割り当て
//         $this
//         ->addJavaScript( '../js/prototype.js' )
//         ->addJavaScript( '../js/corelib.js' );
//     }
//
//     /**
//      * 自動与信実行ロックを解除する。
//      */
//     public function unlockAction()
//     {
//         $mdlss = new Table_SystemStatus($this->app->dbAdapter);
//         $mdlss->releaseLock();
//
//         $this->_redirect("index/index");
//     }
// Del By Takemasa(NDC) 20150212 End 未使用故コメントアウト化

    protected function getMultiOpenedJnbAccounts() {
        $logic = new LogicJnbAccount($this->app->dbAdapter);
        // 戻り値は[array]
// ↓↓↓20160628_ﾚｽﾎﾟﾝｽﾀﾞｳﾝによる暫定処理
        return array();
// ↑↑↑20160628_ﾚｽﾎﾟﾝｽﾀﾞｳﾝによる暫定処理
        return $logic->findMultiOpenedAccounts();
    }

    protected function getJnbAutoReceiptAbendThreads() {
        $thTable = new TableThreadPool($this->app->dbAdapter);
        $grp = LogicJnbAccountReceipt::BATCH_THREAD_GROUP_NAME;
        // 戻り値は[int]
        return $thTable->countItemsByStatus($grp, TableThreadPool::STATUS_TERMINATED_ABNORMALLY);
    }

    /**
     * 要立替データがあるか検索する
     * 未立替のデータが１件でもある場合は、要立替とする
     * @return boolean
     */
    protected function isMustCharge() {
        // 未立替のデータがあるか否か
        $sql = "SELECT Seq FROM T_PayingControl WHERE ExecFlg = 0 AND PayingControlStatus = 1  AND ValidFlg = 1 Limit 1 ";

        $ri = $this->app->dbAdapter->query($sql)->execute(null);
        return $ri->count() > 0 ? true : false;
    }

    /**
     * アラートデータがあるか検索する
     * アラートデータが１件でもある場合は、アラートリンクを表示する
     * @return boolean
     */
    protected function isStagnationAlert() {
        // 立替予定日が今日以前で、未立替のデータがあるか否か
        $sql = "SELECT AlertSeq FROM T_StagnationAlert WHERE AlertSign = 1 AND ValidFlg = 1 Limit 1 ";

        $ri = $this->app->dbAdapter->query($sql)->execute(null);
        return $ri->count() > 0 ? true : false;
    }

    /**
     * 加盟店登録メール受信後で、未処理（T_Enterprise未登録）のデータ件数を取得する
     */
    protected function getEnterpriseAddBatchCount() {
        $tbl = new TableEnterpriseMailReceivedHistory($this->app->dbAdapter);
        return $tbl->countNotProcessedData();
    }

    /**
     * 権限に紐づくメニュー情報を取得します。
     * 戻り値のValidFlgが 0 は権限なし、 1 は権限ありと判断します
     * @return array
     */
    protected function getMenuAuthorityInfo() {
        // メニューマスタを駆動表とし、RoleCodeとメニューSEQで、
        // 有効なT_MenuAuthorityと結合できるものは権限あり、
        // 結合できないものは権限なしとみなす
        $sql = <<<EOQ
SELECT  m.Class     AS Class
      , m.Id        AS Id
      , IFNULL(ma.ValidFlg, 0) AS ValidFlg
  FROM  T_Menu m
        LEFT OUTER JOIN T_MenuAuthority ma
                     ON m.MenuSeq   = ma.MenuSeq
	                AND ma.RoleCode = :RoleCode
 WHERE Module = :Module
 ORDER BY m.Class
        , m.Id
EOQ;

        $prm = array(
            ':RoleCode' => $this->app->authManagerAdmin->getUserInfo()->RoleCode,
            ':Module'   => 'cbadmin',
        );

        $ri = $this->app->dbAdapter->query($sql)->execute($prm);


        // array( 'Class' => array('Id' => 'ValidFlg')) という形に加工
        $result = array();
        foreach( $ri as $row ) {
            // データ抽出
            $class = $row['Class'];
            $id = $row['Id'];
            $valid_flg = $row['ValidFlg'];

            // 行データからarray( 'Class' => array('Id' => 'ValidFlg'))という形の配列を作成
            $arrRow = array(
                $class => array(
                    $id => $valid_flg,
                ),
            );

            // arrayをマージして溜め込む
            $result = array_merge_recursive($result, $arrRow);

        }

        return $result;
    }

    /**
     * バッチエラーがあるか検索する(当日に限る)
     * @return boolean true:あり／false:なし
     */
    protected function isBatchErrorOn() {
        $sql = " SELECT COUNT(1) AS cnt FROM T_BatchLog WHERE DATE_FORMAT(OccDate, '%Y-%m-%d') = :OccDate AND ValidFlg = 1 ";
        $errorCount = (int)$this->app->dbAdapter->query($sql)->execute(array(':OccDate' => date('Y-m-d')))->current()['cnt'];
        return ($errorCount > 0) ? true : false;
    }

}
