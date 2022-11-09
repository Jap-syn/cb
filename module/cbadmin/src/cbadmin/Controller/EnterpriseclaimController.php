<?php
namespace cbadmin\Controller;

use Coral\Base\BaseHtmlUtils;
use Coral\Base\BaseUtility;
use Coral\Base\BaseGeneralUtils;
use Coral\Coral\Controller\CoralControllerAction;
use Coral\Coral\CoralCodeMaster;
use cbadmin\Application;
use models\Table\TableOem;
use Zend\Db\Adapter\Driver\ResultInterface;
use models\Table\ATableEnterpriseReceiptHistory;

class EnterpriseclaimController extends CoralControllerAction
{
	protected $_componentRoot = './application/views/components';

	/**
	 * アプリケーションオブジェクト
	 * @var Application
	 */
	private $app;

	/**
	 * Controllerを初期化する
	 */
	public function _init()
	{
        $this->app = Application::getInstance();
        $this->view->assign('userInfo', $this->app->authManagerAdmin->getUserInfo());

        $this->addStyleSheet('../css/default02.css');
        $this->addJavaScript( '../js/json+.js' );
        $this->addJavaScript( '../js/prototype.js' );
        $this->addJavaScript( '../js/corelib.js' );
        $this->addJavaScript( '../js/base.ui.js');

        $this->setPageTitle("後払い.com - 加盟店請求残高検索");
	}

	/**
	 * 検索フォームの表示
	 */
	public function formAction()
	{
        $params = $this->getParams();

        $mdlOem = new TableOem($this->app->dbAdapter);

        $oem_list = $mdlOem->getOemIdList();

        //OEM先リストSELECTタグ
        $this->view->assign('oemTag',BaseHtmlUtils::SelectTag("Oem",$oem_list));

        return $this->view;
	}

	/**
	 * 検索実行
	 */
	public function listAction()
	{
        $params = $this->getParams();

        // 検索SQL
        $sql  = " SELECT  e.EnterpriseId                      ";// 加盟店ID
        $sql .= "     ,   e.EnterpriseNameKj                  ";// 加盟店名
        $sql .= "     ,   e.EnterpriseNameKn                  ";// 加盟店名かな
        $sql .= "     ,   e.RepNameKj                         ";// 代表者氏名
        $sql .= "     ,   e.RepNameKn                         ";// 代表者氏名かな
        $sql .= "     ,   e.UnitingAddress                    ";// 住所
        $sql .= "     ,   e.ContactPhoneNumber                ";// 電話番号
        $sql .= "     ,   e.MailAddress                       ";// メールアドレス
        $sql .= "     ,   e.OemId                             ";// OEMID
        $sql .= "     ,   MAX(ech.ClaimDate) AS ClaimDate     ";// 請求日
        $sql .= "     ,   et.ClaimAmountTotal                 ";// 請求金額合計
        $sql .= "     ,   MAX(erh.ReceiptDate) AS ReceiptDate ";// 入金日
        $sql .= "     ,   et.ReceiptAmountTotal               ";// 入金額合計
        $sql .= "     ,   et.ClaimedBalance                   ";// 請求残高
        $sql .= "     ,   e.ClaimClass                        ";// 加盟店請求区分
        $sql .= " FROM    T_Enterprise e ";
        $sql .= "         INNER JOIN T_EnterpriseTotal et ON (e.EnterpriseId = et.EnterpriseId) ";
        $sql .= "         LEFT OUTER JOIN T_EnterpriseClaimHistory ech ON (e.EnterpriseId = ech.EnterpriseId) ";
        $sql .= "         LEFT OUTER JOIN T_EnterpriseReceiptHistory erh ON (e.EnterpriseId = erh.EnterpriseId) ";
        $sql .= " WHERE   1 = 1 ";

        // WHERE句の追加

        // OEM先
        if ($params['Oem'] > 0) {
            $sql .= " AND e.OemId = " . $params['Oem'];
        }
        // 加盟店ID
        if ($params['LoginId'] != '') {
            $sql .= " AND e.LoginId like '%" . BaseUtility::escapeWildcard($params['LoginId']) . "' ";
        }
        // 加盟店名
        if ($params['EnterpriseNameKj'] != '') {
            $sql .= " AND e.EnterpriseNameKj like '%" . BaseUtility::escapeWildcard($params['EnterpriseNameKj']) . "%' ";
        }
        // 加盟店名カナ
        if ($params['EnterpriseNameKn'] != '') {
            $sql .= " AND e.EnterpriseNameKn like '%" . BaseUtility::escapeWildcard($params['EnterpriseNameKn']) . "%' ";
        }
        // 請求日
        // Fromのみが入力されている時は、日付の一致検索化(20150526_1230)
        if ($params['ClaimDateF'] != '' && $params['ClaimDateT'] == '') {
            $sql .= " AND ech.ClaimDate = '" . BaseUtility::escapeWildcard($params['ClaimDateF']) . "' ";
        }
        else {
            $wClaimDate = BaseGeneralUtils::makeWhereDate('ech.ClaimDate', $params['ClaimDateF'], $params['ClaimDateT']);
            if ($wClaimDate != '') {
                $sql .= " AND " . $wClaimDate;
            }
        }
        // 請求残高
        if      ($params['ClaimedBalanceType'] == 0) { ; }  // なにもしないの明示
        else if ($params['ClaimedBalanceType'] == 1) {
            $sql .= " AND et.ClaimedBalance <> 0 ";
        }
        else if ($params['ClaimedBalanceType'] == 2) {
            // (請求残が残っている、MINの請求日付と加盟店請求長期繰越日数閾値を比較する)
            $sql .= " AND DATEDIFF(NOW(), (SELECT MIN(ClaimDate) AS ClaimDate ";
            $sql .= "                      FROM   T_EnterpriseClaimHistory ";
            $sql .= "                      WHERE  ClaimedBalance > 0 ";
            $sql .= "                      AND    EnterpriseId = e.EnterpriseId)) > (SELECT (CASE WHEN ISEMPTY(IFNULL(PropValue, '')) THEN 60 ELSE PropValue END) FROM T_SystemProperty WHERE Module = '[DEFAULT]' AND Category = 'systeminfo' AND Name = 'LongEnterpriseClaimedDays' ) ";
        }
        // 所在地
        if ($params['UnitingAddress'] != '') {
            $sql .= " AND e.UnitingAddress like '%" . BaseUtility::escapeWildcard($params['UnitingAddress']) . "%' ";
        }
        // 代表者氏名
        if ($params['RepNameKj'] != '') {
            $sql .= " AND e.RepNameKj like '%" . BaseUtility::escapeWildcard($params['RepNameKj']) . "%' ";
        }
        // 代表者氏名カナ
        if ($params['RepNameKn'] != '') {
            $sql .= " AND e.RepNameKn like '%" . BaseUtility::escapeWildcard($params['RepNameKn']) . "%' ";
        }
        // 電話番号
        if ($params['ContactPhoneNumber'] != '') {
            $sql .= " AND e.ContactPhoneNumber like '%" . BaseUtility::escapeWildcard($params['ContactPhoneNumber']) . "%' ";
        }
        // メールアドレス
        if ($params['MailAddress'] != '') {
            $sql .= " AND e.MailAddress like '%" . BaseUtility::escapeWildcard($params['MailAddress']) . "%' ";
        }

        $sql .= " GROUP BY  ";
        $sql .= "         e.EnterpriseId ";
        $sql .= "     ,   e.EnterpriseNameKj ";
        $sql .= "     ,   e.EnterpriseNameKn ";
        $sql .= "     ,   e.RepNameKj ";
        $sql .= "     ,   e.RepNameKn ";
        $sql .= "     ,   e.UnitingAddress ";
        $sql .= "     ,   e.ContactPhoneNumber ";
        $sql .= "     ,   e.MailAddress ";
        $sql .= "     ,   e.OemId ";
        $sql .= "     ,   et.ClaimAmountTotal ";
        $sql .= "     ,   et.ReceiptAmountTotal ";
        $sql .= "     ,   et.ClaimedBalance ";

        // クエリー実行
        $ri = $this->app->dbAdapter->query($sql)->execute(null);

        $datas = ResultInterfaceToArray($ri);

        $this->view->assign('claimedBalanceType', $params['ClaimedBalanceType']);
        $this->view->assign('list', $datas);

        return $this->view;
	}

	/**
	 * 事業者別入金明細検索
	 */
	public function detailAction()
	{
        $params = $this->getParams();

        $eid = (isset($params['eid'])) ? $params['eid'] : -1;

        // 請求残高一覧取得
        $sql =<<<EOQ
SELECT  e.EnterpriseId
    ,   e.EnterpriseNameKj
    ,   MAX(ech.ClaimDate) AS ClaimDate
    ,   IFNULL(et.ClaimAmountTotal, 0) AS ClaimAmountTotal
    ,   MAX(erh.ReceiptDate) AS ReceiptDate
    ,   IFNULL(et.ReceiptAmountTotal, 0) AS ReceiptAmountTotal
    ,   IFNULL(et.ClaimedBalance, 0) AS ClaimedBalance
FROM    T_Enterprise e
        INNER JOIN T_EnterpriseTotal et ON (e.EnterpriseId = et.EnterpriseId)
        LEFT OUTER JOIN T_EnterpriseClaimHistory ech ON (e.EnterpriseId = ech.EnterpriseId)
        LEFT OUTER JOIN T_EnterpriseReceiptHistory erh ON (e.EnterpriseId = erh.EnterpriseId)
WHERE   e.EnterpriseId = :EnterpriseId
EOQ;
        $list =$this->app->dbAdapter->query($sql)->execute(array(':EnterpriseId' => $eid))->current();

        // 入金明細取得
        $sql =<<<EOQ
SELECT  ReceiptDate
    ,   ReceiptAmount
    ,   Note
FROM    T_EnterpriseReceiptHistory
WHERE   ReceiptClass = 1
AND     EnterpriseId = :EnterpriseId
EOQ;
        $receiptList = ResultInterfaceToArray($this->app->dbAdapter->query($sql)->execute(array(':EnterpriseId' => $eid)));

        $this->view->assign('list', $list);
        $this->view->assign('receiptList', $receiptList);

        return $this->view;
	}

	/**
	 * 事業者別請求残高入金
	 */
	public function editAction()
	{
        $params = $this->getParams();

        $eid = (isset($params['eid'])) ? $params['eid'] : -1;

        $this->view->assign('list', $this->getEnterpriseClaimReceiptList($eid)->current());
        $this->view->assign('receiptDate', '');
        $this->view->assign('receiptAmount', '');
        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
        $this->view->assign("receiptClassTag" ,BaseHtmlUtils::SelectTag('receiptClass' ,$codeMaster->getReceiptClassMaster(),-1,'onChange="javascript:onChangeReceiptClass(); "'));       // 2015/09/18 Y.Suzuki 会計対応 Mod
        $this->view->assign('note', '');
        // 2015/09/24 Y.Suzuki Add 会計対応 Stt
        $this->view->assign('receiptAmountRece', '');
        $this->view->assign('receiptAmountDue', '');
        $this->view->assign("receiptAmountSourceTag", BaseHtmlUtils::SelectTag('receiptAmountSource' ,$codeMaster->getMasterCodes(167, array(-1 => '-----')), -1, ''));
        // 2015/09/24 Y.Suzuki Add 会計対応 End


        return $this->view;
	}

	/**
	 * 事業者別請求残高入金データの取得
	 *
	 * @param int $eid 加盟店ID
	 * @return ResultInterface SQL実行結果
	 */
	protected function getEnterpriseClaimReceiptList($eid)
	{
        $sql =<<<EOQ
SELECT  e.ClaimClass
    ,   e.EnterpriseId
    ,   e.EnterpriseNameKj
    ,   MAX(ech.ClaimDate) AS ClaimDate
    ,   IFNULL(et.ClaimedBalance, 0) AS ClaimedBalance
    ,   et.ReceiptAmountTotal
FROM    T_Enterprise e
        INNER JOIN T_EnterpriseTotal et ON (e.EnterpriseId = et.EnterpriseId)
        LEFT OUTER JOIN T_EnterpriseClaimHistory ech ON (e.EnterpriseId = ech.EnterpriseId)
WHERE   e.EnterpriseId = :EnterpriseId
EOQ;
        return $this->app->dbAdapter->query($sql)->execute(array(':EnterpriseId' => $eid));
	}

	/**
	 * 事業者別請求残高入金(更新処理)
	 */
	public function saveAction()
	{
        $params = $this->getParams();

        $eid = (isset($params['eid'])) ? $params['eid'] : -1;

        $receiptDate = (isset($params['receiptDate'])) ? $params['receiptDate'] : '';
//         $receiptAmount = (isset($params['receiptAmount'])) ? $params['receiptAmount'] : '';     // 2015/09/24 Y.Suzuki 会計対応 Del
        $receiptClass = (isset($params['receiptClass'])) ? $params['receiptClass'] : -1;
        $note = (isset($params['note'])) ? $params['note'] : '';
        // 2015/09/24 Y.Suzuki Add 会計対応 Stt
        $receiptAmountRece = (isset($params['receiptAmountRece'])) ? $params['receiptAmountRece'] : '';
        $receiptAmountDue = (isset($params['receiptAmountDue'])) ? $params['receiptAmountDue'] : '';
        $receiptAmountSource = (isset($params['receiptAmountSource'])) ? $params['receiptAmountSource'] : -1;
        $receiptAmount = "";
        if (strlen($receiptAmountRece) != 0 && (strlen($receiptAmountDue) != 0)) {
            if (is_numeric($receiptAmountRece) && is_numeric($receiptAmountDue)) {
                $receiptAmount = $receiptAmountRece + $receiptAmountDue;    // 入金額(売掛金) + 入金額(未収金) が入金額
            }
        }
        // 2015/09/24 Y.Suzuki Add 会計対応 End

        $list = $this->getEnterpriseClaimReceiptList($eid)->current();

        $input = array();
        $input['receiptDate'] = $receiptDate;
        $input['receiptAmount'] = $receiptAmount;
        $input['receiptClass'] = $receiptClass;
        // 2015/09/24 Y.Suzuki Add 会計対応 Stt
        $input['receiptAmountRece'] = $receiptAmountRece;
        $input['receiptAmountDue'] = $receiptAmountDue;
        $input['receiptAmountSource'] = $receiptAmountSource;
        // 2015/09/24 Y.Suzuki Add 会計対応 End

        $errors = $this->validate($input, $list['ClaimedBalance']);
        // count関数対策
        if (!empty($errors)) {
            // エラーがあればエラーメッセージをセット
            $this->view->assign('error', $errors);

            $this->view->assign('list', $list);
            $this->view->assign('receiptDate', $receiptDate);
            $this->view->assign('receiptAmount', $receiptAmount);
            $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
            $this->view->assign("receiptClassTag" ,BaseHtmlUtils::SelectTag('receiptClass' ,$codeMaster->getReceiptClassMaster(),$receiptClass,'onChange="javascript:onChangeReceiptClass(); "'));  // 2015/09/24 Y.Suzuki 会計対応 Mod
            $this->view->assign('note', $note);
            // 2015/09/24 Y.Suzuki Add 会計対応 Stt
            $this->view->assign('receiptAmountRece', $receiptAmountRece);
            $this->view->assign('receiptAmountDue', $receiptAmountDue);
            $this->view->assign("receiptAmountSourceTag", BaseHtmlUtils::SelectTag('receiptAmountSource' ,$codeMaster->getMasterCodes(167, array(-1 => '-----')), $receiptAmountSource, ''));
            // 2015/09/24 Y.Suzuki Add 会計対応 End

            $this->setTemplate('edit');
            return $this->view;
        }

        // 更新処理実施
        $errorCount = 0;
        $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
        try {
            // ユーザIDの取得
            $userTable = new \models\Table\TableUser($this->app->dbAdapter);
            $userID = $userTable->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

            // 1.加盟店入金履歴への登録
            $mdlerh = new \models\Table\TableEnterpriseReceiptHistory($this->app->dbAdapter);
            $data = array(
                'EnterpriseId' => $list['EnterpriseId'],
                'ReceiptDate' => $receiptDate,
                'ReceiptProcessDate' => date('Y-m-d H:i:s'),
                'ReceiptAmount' => $receiptAmount,
                'ReceiptClass' => $receiptClass,
                'Note' => $note,
                'RegistId' => $userID,
                'UpdateId' => $userID,
            );
            $newSeq = $mdlerh->saveNew($data);      // 2015/09/24 Y.Suzuki 会計対応 Mod

            // 2015/09/24 Y.Suzuki Add 会計対応 Stt
            // 加盟店入金履歴_会計への登録
            $mdlaterh = new ATableEnterpriseReceiptHistory($this->app->dbAdapter);
            $atdata = array(
                'EntRcptSeq' => $newSeq,
                'ReceiptAmountRece' => $receiptAmountRece,
                'ReceiptAmountDue' => $receiptAmountDue,
                'ReceiptAmountSource' => $receiptAmountSource,
                'DailySummaryFlg' => 0,
            );

            $mdlaterh->saveNew($atdata);
            // 2015/09/24 Y.Suzuki Add 会計対応 End

            // 2.加盟店請求履歴の更新
            $mdlech = new \models\Table\TableEnterpriseClaimHistory($this->app->dbAdapter);

            $stm = $this->app->dbAdapter->query(" SELECT * FROM T_EnterpriseClaimHistory WHERE PaymentAllocatedFlg <> 1 AND EnterpriseId = :EnterpriseId ORDER BY ClaimDate ");
            $ri = $stm->execute(array(':EnterpriseId' => $list['EnterpriseId']));

            $nyukingaku = $receiptAmount;// 保管用入金額
            foreach ($ri as $row) {

                if ($nyukingaku <= 0) { break; }

                // 請求金額－入金額を求める
                $sa = ($row['ClaimAmount'] - $row['PaymentAllocatedAmount']);

                if ($sa <= $nyukingaku) {
                    // a. カレント行が入金済みになるケース
                    // (入金額を更新[入金済み])
                    $data = array(
                        'PaymentAllocatedAmount' => $row['ClaimAmount'],
                        'PaymentAllocatedFlg' => 1,
                        'UpdateId' => $userID,
                    );
                    $mdlech->saveUpdate($data, $row['EntClaimSeq']);

                    // (保管用入金額を$sa分減算)
                    $nyukingaku -= $sa;
                }
                else {
                    // b. カレント行が入金済みにならないケース
                    // (入金額を更新)
                    $data = array(
                        'PaymentAllocatedAmount' => ($row['PaymentAllocatedAmount'] + $nyukingaku),
                        'UpdateId' => $userID,
                    );

                    $mdlech->saveUpdate($data, $row['EntClaimSeq']);

                    // (ループ処理を抜ける)
                    break;
                }
            }

            // 3.加盟店別集計を更新
            $mdlet = new \models\Table\TableEnterpriseTotal($this->app->dbAdapter);
            $data = array(
                'ReceiptAmountTotal' => ($list['ReceiptAmountTotal'] + $receiptAmount),
                'ClaimedBalance' => ($list['ClaimedBalance'] - $receiptAmount),
                'UpdateId' => $userID,
            );
            $mdlet->saveUpdate($data, $eid);

            $this->app->dbAdapter->getDriver()->getConnection()->commit();
        }
        catch(\Exception $err) {
            $this->app->dbAdapter->getDriver()->getConnection()->rollBack();
            $errorCount = 1;
        }

        $this->view->assign('errorCount', $errorCount);
        $this->view->assign('link', $this->getBaseUrl() . '/enterpriseclaim/detail/eid/' . $eid);

        return $this->view;
	}

	/**
	 * 登録フォームの内容を検証する
	 * @param array $input 登録フォームデータ
	 * @param int $claimedBalance 請求残高
	 * @return array エラーメッセージの配列
	 */
	protected function validate(array $input, $claimedBalance)
	{
        $errors = array();

        // receiptDate: 入金日付
        $key = 'receiptDate';
        if (!isset($errors[$key]) && !(strlen($input[$key]) > 0)) {
            $errors[$key] = array("'入金日付'を入力してください");
        }
        if (!isset($errors[$key]) && !IsValidFormatDate($input[$key])) {
            $errors[$key] = array("'入金日付'の形式が不正です");
        }

        // 2015/09/24 Y.Suzuki Del 会計対応 Stt
        // 売掛金と未収金に分割されるので、削除
//         // receiptAmount: 入金額
//         $key = 'receiptAmount';
//         if (!isset($errors[$key]) && !(strlen($input[$key]) > 0)) {
//             $errors[$key] = array("'入金額'が指定されていません");
//         }
//         if (!isset($errors[$key]) && !is_numeric($input[$key])) {
//             $errors[$key] = array("'入金額'の指定が不正です");
//         }
//         if (!isset($errors[$key]) && !($input[$key] <= $claimedBalance)) {
//             $errors[$key] = array("'入金額'は請求残高以下で指定してください");
//         }
        // 2015/09/24 Y.Suzuki Del 会計対応 End

        // receiptClass : 科目
        $key = 'receiptClass';
        if (!isset($errors[$key]) && !($input[$key] > 0)) {
            $errors[$key] = array("'科目'を指定してください");
        }

        // 2015/09/24 Y.Suzuki Add 会計対応 Stt
        // receiptAmountRece: 入金額(売掛金)
        $key = 'receiptAmountRece';
        if (!isset($errors[$key]) && !(strlen($input[$key]) > 0)) {
            $errors[$key] = array("'入金額(売掛金)'が指定されていません");
        }
        if (!isset($errors[$key]) && !is_numeric($input[$key])) {
            $errors[$key] = array("'入金額(売掛金)'の指定が不正です");
        }

        // receiptAmountDue: 入金額(未収金)
        $key = 'receiptAmountDue';
        if (!isset($errors[$key]) && !(strlen($input[$key]) > 0)) {
            $errors[$key] = array("'入金額(未収金)'が指定されていません");
        }
        if (!isset($errors[$key]) && !is_numeric($input[$key])) {
            $errors[$key] = array("'入金額(未収金)'の指定が不正です");
        }

        // receiptAmount: 入金額
        $key = 'receiptAmount';
        if (!isset($errors[$key]) && !($input[$key] <= $claimedBalance)) {
            $errors[$key] = array("'入金額(売掛金 + 未収金)'は請求残高以下で指定してください");
        }

        // receiptAmountSource : 入金元
        $key = 'receiptAmountSource';
        if ($input['receiptClass'] <> 4 && (!isset($errors[$key]) && !($input[$key] > 0))) {
            $errors[$key] = array("'入金元'を指定してください");
        }
        // 2015/09/24 Y.Suzuki Add 会計対応 End

        return $errors;
	}
}

