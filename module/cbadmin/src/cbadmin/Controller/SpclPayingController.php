<?php
namespace cbadmin\Controller;

use Coral\Base\BaseHtmlUtils;
use Coral\Base\BaseGeneralUtils;
use Coral\Coral\Controller\CoralControllerAction;
use Coral\Coral\CoralCodeMaster;
use Coral\Coral\CoralValidate;
use cbadmin\Application;
use models\Table\TableEnterprise;
use models\Table\TablePayingControl;
use models\Table\TablePayingAndSales;
use models\Table\TableBusinessCalendar;
use models\Table\TableUser;
use models\Table\TableCancel;
use models\Table\TableSystemProperty;
use Coral\Coral\History\CoralHistoryOrder;
use models\Table\TableOrder;
use models\Table\ATablePayingControl;
use models\Table\TableAdjustmentAmount;

class SpclPayingController extends CoralControllerAction
{
    protected $_componentRoot = './application/views/components';

    const SESS_PROCESS_ID = 'SESS_PROCESS_ID';

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
        $this->addJavaScript('../js/prototype.js');
        $this->addJavaScript('../js/corelib.js');
        $this->addJavaScript('../js/base.ui.js');

        $this->setPageTitle("後払い.com - 臨時加盟店立替精算");
    }

    /**
     * 臨時加盟店立替精算を表示する。
     */
    public function listAction()
    {
        // 画面情報取得
        $params = $this->getParams();

        // 立替締め日
        $payingControlDate = date("Y-m-d");

        $this->view->assign("PayingControlDate", $payingControlDate);

        if (isset($params['btnBack']))
        {
            $this->processAction();
        }

        return $this->view;
    }

    /**
     * 処理の振り分け
     */
    public function processAction()
    {
        // 画面情報取得
        $params = $this->getParams();

        // 呼び出し元判定
        if (isset($params['search_button']))
        {
            // 検索処理
            return $this->search_action();
        }
        elseif (isset($params['calc_button']))
        {
            // 立替計算処理
            return $this->calc_action();
        }
        elseif (isset($params['save_button']))
        {
            // セッションのアクション設定と同じ処理の場合
            // F5とみなし、立替確定処理を行わない
            if (isset($_SESSION[self::SESS_PROCESS_ID]) && $_SESSION[self::SESS_PROCESS_ID] == "save") {
                // 検索処理でリセット
                return $this->search_action();
            }

            // 立替確定処理
            return $this->save_action();
        }
        elseif (isset($params['update_button']))
        {
            // 支払完了処理 ボタン名からSeqを取得
            $seq = key($params['update_button']);

            // セッションのアクション設定と同じ処理の場合
            // F5とみなし、支払完了処理を行わない
            if (isset($_SESSION[self::SESS_PROCESS_ID]) && $_SESSION[self::SESS_PROCESS_ID] == "update".$seq) {
                // 検索処理でリセット
                return $this->search_action();
            }

            return $this->update_action($seq);
        }
    }

    /**
     * 加盟店情報覧を表示する
     */
    public function search_action($entId = null, $flg = null)
    {
        // 画面情報取得
        $params = $this->getParams();

        // 加盟店ID
        $searchEnterpriseId = $params["SearchEnterpriseId"];
        // 立替締め日
        $payingControlDate = $params["PayingControlDate"];
        if (!isset($payingControlDate)) {
            $payingControlDate = date("Y-m-d");
        }
        // 立替予定日
        $payingPlanDate = $params["PayingPlanDate"];

        $mdlE = new TableEnterprise($this->app->dbAdapter);

        // 加盟店名取得
        $enterpriseName = "";
        if (isset($searchEnterpriseId)) {
            $enterprise = $this->getEnterpriseByLoginId($searchEnterpriseId);
            if ($enterprise != false) {
                $enterpriseName = $enterprise["EnterpriseNameKj"];
                $enterpriseId = $enterprise["EnterpriseId"];
                $searchEnterpriseId = $enterprise["LoginId"];
            }
            else {
                // 加盟店が取得できない場合
                $errorMessage = "加盟店が存在しません。";
            }
        }
        elseif (isset($entId)) {
            $enterprise = $mdlE->find($entId)->current();
            if ($enterprise != false) {
                $enterpriseName = $enterprise["EnterpriseNameKj"];
                $enterpriseId = $enterprise["EnterpriseId"];
                $searchEnterpriseId = $enterprise["LoginId"];
            }
            else {
                // 加盟店が取得できない場合
                $errorMessage = "加盟店が存在しません。";
            }
        }

        // 一覧データ取得
        if (!isset($errorMessage)) {
            $pcList = $this->getPayingControlList($enterpriseId);
        }
        else {
            $pcList = array();
        }

        // フラグ維持
        $csvDownloadFlg = isset($params["csvDownloadFlg"]) ? $params["csvDownloadFlg"] : $flg;

        // 一覧画面を表示
        $this->setTemplate("list");
        $this->view->assign("SearchEnterpriseId", $searchEnterpriseId);
        $this->view->assign("EnterpriseId", $enterpriseId);
        $this->view->assign("EnterpriseName", $enterpriseName);
        $this->view->assign("PayingControlDate", $payingControlDate);
        $this->view->assign("PayingPlanDate", $payingPlanDate);
        $this->view->assign("ErrorMessage", $errorMessage);
        $this->view->assign("PcList", $pcList);
        $this->view->assign("csvDownloadFlg", $csvDownloadFlg);

        // セッションにアクション設定
        $_SESSION[self::SESS_PROCESS_ID] = "search";

        return $this->view;
    }

    /**
     * 加盟店の情報をログインIDで取得する
     *
     * @param string $loginId ログインID
     */
    public function getEnterpriseByLoginId($loginId)
    {
        $sql = " SELECT * FROM T_Enterprise WHERE LoginId = :LoginId ";

        $stm = $this->app->dbAdapter->query($sql);

        $prm = array(
            ':LoginId' => $loginId,
        );

        return $stm->execute($prm)->current();
    }

    /**
     * 既に臨時立替精算済みのデータを取得
     *
     * @param int $enterpriseId 加盟店ID
     * @return array 既に臨時立替精算済みのデータ
     */
    public function getPayingControlList($enterpriseId)
    {
        // 既に臨時立替精算済みのデータ
        $params = array();

        $sql  = "SELECT PC.ChargeCount ";
        $sql .= "     , PC.ChargeAmount ";
        $sql .= "     , PC.SettlementFee ";
        $sql .= "     , PC.ClaimFee ";
        $sql .= "     , 0 AS StampFeeTotal ";
        $sql .= "     , 0 AS MonthlyFee ";
        $sql .= "     , PC.CarryOver ";
        $sql .= "     , 0 AS CalcelAmount ";
        $sql .= "     , PC.TransferCommission ";
        $sql .= "     , PC.AdjustmentAmount ";
        $sql .= "     , 0 AS PayBackAmount ";
        $sql .= "     , PC.DecisionPayment ";
        $sql .= "     , PC.Seq ";
        $sql .= "     , PC.PayingDataDownloadFlg ";
        $sql .= "  FROM T_PayingControl PC ";
        $sql .= " WHERE PC.SpecialPayingFlg = 1 ";
        $sql .= "   AND PC.ExecFlg = 0 ";

        // 加盟店ID
        if (isset($enterpriseId) && strlen($enterpriseId) > 0) {
            $sql .= " AND PC.EnterpriseId = :EnterpriseId ";
            $params[":EnterpriseId"] = $enterpriseId;
        }

        $sql .= " ORDER BY PC.Seq DESC ";

        $stm = $this->app->dbAdapter->query($sql);

        $pcList = ResultInterfaceToArray($stm->execute($params));

        return $pcList;
    }

    /**
     * 立替計算を行い、一覧を表示する
     */
    public function calc_action()
    {
        // 画面情報取得
        $params = $this->getParams();

        // 加盟店ID
        $searchEnterpriseId = $params["SearchEnterpriseId"];
        // 立替締め日
        $payingControlDate = $params["PayingControlDate"];
        // 立替予定日
        $payingPlanDate = $params["PayingPlanDate"];

        $mdlE = new TableEnterprise($this->app->dbAdapter);

        // 加盟店名取得
        $enterpriseName = "";
        if (isset($searchEnterpriseId)) {
            $enterprise = $this->getEnterpriseByLoginId($searchEnterpriseId);
            if ($enterprise != false) {
                $enterpriseName = $enterprise["EnterpriseNameKj"];
                $enterpriseId = $enterprise["EnterpriseId"];
                $searchEnterpriseId = $enterprise["LoginId"];
            }
            else {
                // 加盟店が取得できない場合
                $errorMessage = "加盟店が存在しません。";
            }
        }
        elseif (isset($entId)) {
            $enterprise = $mdlE->find($entId)->current();
            if ($enterprise != false) {
                $enterpriseName = $enterprise["EnterpriseNameKj"];
                $enterpriseId = $enterprise["EnterpriseId"];
                $searchEnterpriseId = $enterprise["LoginId"];
            }
            else {
                // 加盟店が取得できない場合
                $errorMessage = "加盟店が存在しません。";
            }
        }

        // 一覧データ取得
        if (!isset($errorMessage)) {
            $simuList = $this->getSimulatePayingList($enterpriseId, $payingControlDate);
            $pcList = $this->getPayingControlList($enterpriseId);
        }
        else {
            $simuList =array();
            $pcList = array();
        }

        // フラグ維持
        $csvDownloadFlg = isset($params["csvDownloadFlg"]) ? $params["csvDownloadFlg"] : "0";

        // 一覧画面を表示
        $this->setTemplate("list");
        $this->view->assign("SearchEnterpriseId", $searchEnterpriseId);
        $this->view->assign("EnterpriseId", $enterpriseId);
        $this->view->assign("EnterpriseName", $enterpriseName);
        $this->view->assign("PayingControlDate", $payingControlDate);
        $this->view->assign("PayingPlanDate", $payingPlanDate);
        $this->view->assign("ErrorMessage", $errorMessage);
        $this->view->assign("SimuList", $simuList);
        $this->view->assign("PcList", $pcList);
        $this->view->assign("csvDownloadFlg", $csvDownloadFlg);

        // セッションにアクション設定
        $_SESSION[self::SESS_PROCESS_ID] = "calc";

        return $this->view;
    }

    /**
     * シミュレーション分のデータを取得
     *
     * @param int $enterpriseId 加盟店ID
     * @param string $payingControlDate 立替締め日
     * @return array シミュレーション分のデータ
     */
    public function getSimulatePayingList($enterpriseId, $payingControlDate)
    {
        // シミュレーション分のデータ

        // --------------------
        // 立替・売上管理のデータ + 0固定値
        // --------------------
        $params = array();
        $sql  = "SELECT COUNT(PAS.Seq) AS ChargeCount ";
        $sql .= "     , SUM(PAS.UseAmount - PAS.SettlementFee - PAS.ClaimFee) AS ChargeAmount ";        // 仮締めバッチとの整合性を合わせる
        $sql .= "     , SUM(PAS.SettlementFee) AS SettlementFee ";
        $sql .= "     , SUM(PAS.ClaimFee) AS ClaimFee ";
        $sql .= "     , 0 AS StampFeeTotal ";
        $sql .= "     , 0 AS MonthlyFee ";
        $sql .= "     , 0 AS CalcelAmount ";
        $sql .= "     , IFNULL((SELECT SUM(AdjustmentAmount) FROM W_AdjustmentAmount WHERE EnterpriseId = '$enterpriseId'), 0) AS AdjustmentAmount ";
        $sql .= "     , 0 AS PayBackAmount ";
        $sql .= "     , GROUP_CONCAT(PAS.OrderSeq SEPARATOR ',') AS OrderSeqList ";
        $sql .= "  FROM T_PayingAndSales PAS ";
        $sql .= "       INNER JOIN T_Order O ON O.OrderSeq = PAS.OrderSeq ";
        $sql .= " WHERE PAS.ClearConditionForCharge = 1 ";
        $sql .= "   AND (   PAS.PayingControlStatus = 0 ";
        $sql .= "        OR PAS.PayingControlStatus IS NULL ";
        $sql .= "       ) ";
        $sql .= "   AND PAS.CancelFlg = 0 ";
        $sql .= "   AND PAS.SpecialPayingDate IS NOT NULL ";

        // 加盟店ID
        if (isset($enterpriseId) && strlen($enterpriseId) > 0) {
            $sql .= "   AND O.EnterpriseId = :EnterpriseId ";
            $params[":EnterpriseId"] = $enterpriseId;
        }

        // 立替締め日
        if (isset($payingControlDate) && strlen($payingControlDate) > 0) {
            $sql .= "   AND PAS.ClearConditionDate <= :ClearConditionDate ";
            $params[":ClearConditionDate"] = date("Y-m-d", strtotime($payingControlDate));
        }

        $stm = $this->app->dbAdapter->query($sql);

        $simuList = ResultInterfaceToArray($stm->execute($params));

        // お取引件数が0の場合は対象なし
        if ($simuList[0]["ChargeCount"] == 0) {
            return array();
        }

        // --------------------
        // 前回持越（固定費）
        // --------------------
        $params = array();
        $sql  = "SELECT SUM(DecisionPayment) AS CarryOver ";
        $sql .= "  FROM T_PayingControl ";
        $sql .= " WHERE ExecFlg = 10 ";

        // 加盟店ID
        if (isset($enterpriseId) && strlen($enterpriseId) > 0) {
            $sql .= "   AND EnterpriseId = :EnterpriseId ";
            $params[":EnterpriseId"] = $enterpriseId;
        }

        $carryOverDb = $this->app->dbAdapter->query($sql)->execute($params)->current();

        if ($carryOverDb == false) {
            $carryOver = 0;
        }
        else {
            $carryOver = intval($carryOverDb["CarryOver"]);
        }

        $simuList[0]["CarryOver"] = $carryOver;

        // --------------------
        // 振込手数料
        // --------------------
        $sql  = "SELECT C.Note AS TransferCommission ";
        $sql .= "  FROM T_Enterprise E ";
        $sql .= "       INNER JOIN M_Code C ON C.CodeId = 93 AND C.Class1 = E.TcClass ";
        $sql .= " WHERE CAST(C.Class2 AS SIGNED) <= :JudgePayment ";
        $sql .= "   AND CAST(C.Class3 AS SIGNED) >  :JudgePayment ";

        // 加盟店ID
        if (isset($enterpriseId) && strlen($enterpriseId) > 0) {
            $sql .= "   AND E.EnterpriseId = :EnterpriseId ";
            $params[":EnterpriseId"] = $enterpriseId;
        }

        // 判定金額
        $judgePayment = $carryOver + intval($simuList[0]["ChargeAmount"]);      // 初期のデータ取得時に 決済手数料と請求手数料 を 立替金額から減算するようにしたので処理方法を変更
        $params[":JudgePayment"] = $judgePayment;

        $transferCommissionDb = $this->app->dbAdapter->query($sql)->execute($params)->current();

        if ($transferCommissionDb == false) {
            $transferCommission = 0;
        }
        else {
            $transferCommission = intval($transferCommissionDb["TransferCommission"]);
        }

        $simuList[0]["TransferCommission"] = $transferCommission;

        // --------------------
        // 支払額
        // --------------------
        $decusuibPaymentHid = $judgePayment - $transferCommission;
        $decisionPayment = $decusuibPaymentHid + $simuList[0]["AdjustmentAmount"];

        $simuList[0]["DecisionPayment"] = $decisionPayment;
        $simuList[0]["DecisionPaymentHid"] = $decusuibPaymentHid;

        return $simuList;
    }

    /**
     * 立替確定処理
     */
    public function save_action()
    {
        // 画面情報取得
        $params = $this->getParams();

        // 加盟店ID(入力)
        $searchEnterpriseId = $params["SearchEnterpriseId"];
        // 加盟店ID
        $enterpriseId = $params["EnterpriseId"];
        // 加盟店名
        $enterpriseName = $params["EnterpriseName"];
        // 立替締め日
        $payingControlDate = $params["PayingControlDate"];
        // 立替予定日
        $payingPlanDate = $params["PayingPlanDate"];

        // 立替予定日形式チェック
        if (strlen($payingPlanDate) == 0) {
            $errorMessage = "立替予定日は必須です。";
        }
        elseif (strlen($payingPlanDate) > 0 && !IsValidFormatDate($payingPlanDate)) {
            $errorMessage = "立替予定日はYYYY-MM-DDで入力してください。";
        }
        else {
            $mdlBc = new TableBusinessCalendar($this->app->dbAdapter);
            if (!$mdlBc->isBusinessDate(date("Y-m-d", strtotime($payingPlanDate)))) {
                $errorMessage = "立替予定日は営業日を入力してください。";
            }
        }

        if (!isset($errorMessage)) {
            // シミュレーション行を登録する
            $obj = new TableUser($this->app->dbAdapter);
            $mdlPc = new TablePayingControl($this->app->dbAdapter);
            $mdpPas = new TablePayingAndSales($this->app->dbAdapter);
            $mdlC = new TableCancel($this->app->dbAdapter);
            $history = new CoralHistoryOrder($this->app->dbAdapter);
            $mdlo = new TableOrder($this->app->dbAdapter);
            $mdlE = new TableEnterprise($this->app->dbAdapter);
            $mdlatpc = new ATablePayingControl($this->app->dbAdapter);      // 2015/11/19 Y.Suzuki 会計対応 Add

            // 画面データ
            $simuListData = $params["SimuList"];
            // ユーザID
            $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

            // トランザクション開始
            $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
            try {
                $ent = $mdlE->find($enterpriseId)->current();
                $oemId = $ent['OemId'];

                // ----------------------
                // 立替振込管理
                // ----------------------
                // 登録データ作成
                $data = $this->getPcSaveData($enterpriseId, $oemId, $payingControlDate, $payingPlanDate, $simuListData, $userId);

                // 登録処理
                $newSeq = $mdlPc->saveNew($data);

                // 2015/11/19 Y.Suzuki Add 会計対応 Stt
                // 会計用項目のINSERT（全て0でINSERT）
                $mdlatpc->saveNew(array('Seq' => $newSeq));
                // 2015/11/19 Y.Suzuki Add 会計対応 End

                // ----------------------
                // 注文更新
                // ----------------------
                // 更新処理
                $mdlo->updateSpecialPayingOrder($newSeq, $userId, $enterpriseId, $payingControlDate);

                // ----------------------
                // 立替・売上管理
                // ----------------------
                // 更新処理
                $mdpPas->updateSpecialPaying($newSeq, $userId, $enterpriseId, $payingControlDate);

                // ----------------------
                // CSV作成
                // ----------------------
                $csvResult = $this->saveCsvFile($newSeq);
                if ($csvResult == false) {
                    throw new \Exception("振込データCSVの作成に失敗しました。");
                }

                // ----------------------
                // 立替振込管理
                // ----------------------
                // 更新処理
                $obj_csv = null;
                $fp = fopen($csvResult, "rb");
                $obj_csv = fread($fp, filesize($csvResult));
                fclose($fp);
                unlink($csvResult);
                $data = array(
                    "PayingDataFilePath" => $obj_csv,
                    "UpdateId" => $userId,
                );
                $mdlPc->saveUpdate($data, $newSeq);

                // ----------------------
                // 注文履歴へ登録
                // ----------------------
                $oseqs = explode(',', $simuListData['OrderSeqList']);
                foreach($oseqs as $oseq) {
                    $history->InsOrderHistory($oseq, 81, $userId);
                }

                // ----------------------
                // 調整額管理
                // ----------------------
                // 登録処理
                $this->getAASaveData($enterpriseId, $newSeq);

                // コミット
                $this->app->dbAdapter->getDriver()->getConnection()->commit();

            } catch (\Exception $ex) {
                // ロールバック
                $this->app->dbAdapter->getDriver()->getConnection()->rollBack();

                // エラーメッセージ設定
                $errorMessage = $ex->getMessage();
            }

            $simuList = array();
        }
        else {
            // 画面復元
            $simuList = $this->getSimulatePayingList($enterpriseId, $payingControlDate);
        }

        // 一覧データ取得
        $pcList = $this->getPayingControlList($enterpriseId);

        // フラグ維持
        $csvDownloadFlg = isset($params["csvDownloadFlg"]) ? $params["csvDownloadFlg"] : "0";

        // 一覧画面を表示
        $this->setTemplate("list");
        $this->view->assign("SearchEnterpriseId", $searchEnterpriseId);
        $this->view->assign("EnterpriseId", $enterpriseId);
        $this->view->assign("EnterpriseName", $enterpriseName);
        $this->view->assign("PayingControlDate", $payingControlDate);
        $this->view->assign("PayingPlanDate", $payingPlanDate);
        $this->view->assign("ErrorMessage", $errorMessage);
        $this->view->assign("SimuList", $simuList);
        $this->view->assign("PcList", $pcList);
        $this->view->assign("csvDownloadFlg", $csvDownloadFlg);

        // セッションにアクション設定　正常時のみ
        if (!isset($errorMessage)) {
            $this->view->assign("CompleteMessage", sprintf('<font color="red"><b>登録しました。　%s</b></font>', date("Y-m-d H:i:s")));
            $_SESSION[self::SESS_PROCESS_ID] = "save";
        }

        return $this->view;
    }

    /**
     * 立替振込管理 登録用データの作成
     *
     * @param int $enterpriseId 加盟店ID
     * @param int $oemId OEMID
     * @param string $payingControlDate 立替締め日
     * @param string $payingPlanDate 立替予定日
     * @param array $list 画面データ
     * @param int $userId ユーザID
     * @return array 登録データ:
     */
    function getPcSaveData($enterpriseId, $oemId, $payingControlDate, $payingPlanDate, $list, $userId)
    {
        // 加盟店ID
        $data["EnterpriseId"] = $enterpriseId;

        // 立替締め日
        $data["FixedDate"] = date("Y-m-d", strtotime($payingControlDate));

        // 立替確定日
        $data["DecisionDate"] = date("Y-m-d");

        // 立替実行フラグ
        $data["ExecFlg"] = 0;

        // 立替実行担当者
        $data["ExecCpId"] = $userId;

        // 立替注文件数
        $data["ChargeCount"] = $list["ChargeCount"];

        // 立替金額
        $data["ChargeAmount"] = $list["ChargeAmount"];

        // キャンセル件数
        $data["CancelCount"] = 0;

        // キャンセル精算金額
        $data["CalcelAmount"] = 0;

        // 印紙代発生件数
        $data["StampFeeCount"] = 0;

        // 印紙代精算金額
        $data["StampFeeTotal"] = 0;

        // 月額固定費
        $data["MonthlyFee"] = 0;

        // 振込確定金額
        $data["DecisionPayment"] = $list["DecisionPayment"];

        // 月次計上フラグ
        $data["AddUpFlg"] = 0;

        // 月次計上月度
        $data["AddUpFixedMonth"] = date("Y-m-1", strtotime($payingControlDate));

        // 決済手数料
        $data["SettlementFee"] = $list["SettlementFee"];

        // 請求手数料
        $data["ClaimFee"] = $list["ClaimFee"];

        // 繰越
        $data["CarryOver"] = $list["CarryOver"];

        // 振込手数料
        $data["TransferCommission"] = $list["TransferCommission"];

        // 立替実行予定日
        $data["ExecScheduleDate"] = date("Y-m-d", strtotime($payingPlanDate));

        // 調整額
        $data["AdjustmentAmount"] = $list['AdjustmentAmount'];

        // マイナス振込手数料
        if (intval($list["DecisionPaymentHid"]) < 0 && intval($list["DecisionPaymentHid"]) * -1 < $list["TransferCommission"]) {
            $data["PayBackTC"] = $list["TransferCommission"];
        }
        else {
            $data["PayBackTC"] = null;
        }

        // PayBackTCがプラスになった場合に設定する
        if (intval($data["PayBackTC"]) > 0) {
            $data["CarryOverTC"] = $data["PayBackTC"];
        }

        // OEMID
        $data["OemId"] = $oemId;

        // OEM請求データシーケンス
        $data["OemClaimedSeq"] = null;

        // OEM請求計上フラグ
        $data["OemClaimedAddUpFlg"] = 0;

        // 月額固定費課金
        $data["ChargeMonthlyFeeFlg"] = 0;

        // 立替精算戻し件数
        $data["PayBackCount"] = 0;

        // 立替精算戻し金額
        $data["PayBackAmount"] = 0;

        // 本締め／仮締め区分
        $data["PayingControlStatus"] = 1;

        // 臨時加盟店立替フラグ
        $data["SpecialPayingFlg"] = 1;

        // 振込データDLフラグ
        $data["PayingDataDownloadFlg"] = 0;

        // 振込データCSVファイルパス
        $data["PayingDataFilePath"] = null;

        // 都度請求PDFファイルパス
        $data["ClaimPdfFilePath"] = null;

        $sql = "SELECT COUNT(*) AS CNT FROM W_AdjustmentAmount WHERE EnterpriseId = :EnterpriseId";
        $cnt = $this->app->dbAdapter->query($sql)->execute(array (':EnterpriseId' => $enterpriseId))->current()['CNT'];

        if ($cnt > 0) {
            // 調整額確定フラグ
            $data["AdjustmentDecisionFlg"] = 1;

            // 調整額確定日付
            $data["AdjustmentDecisionDate"] = date("Y-m-d");

            // 調整額件数
            $data["AdjustmentCount"] = $cnt;

        } else {
            // 調整額確定フラグ
            $data["AdjustmentDecisionFlg"] = 0;

            // 調整額確定日付
            $data["AdjustmentDecisionDate"] = null;

            // 調整額件数
            $data["AdjustmentCount"] = null;

        }

        // 登録者
        $data["RegistId"] = $userId;

        // 更新者
        $data["UpdateId"] = $userId;

        // 有効フラグ
        $data["ValidFlg"] = 1;

        return $data;
    }

    /**
     * 振込データCSV保存
     *
     * @param int $seq 立替振込管理Seq
     */
    public function saveCsvFile($seq)
    {
        // 1) CSVデータの作成
        // 保存用ディレクトリの取得
        $mdlsp = new TableSystemProperty($this->app->dbAdapter);

        $module = '[DEFAULT]';
        $category = 'systeminfo';
        $name = 'TempFileDir';

        $payingDataDir = $mdlsp->getValue($module, $category, $name);

        // データの取得
        $sql = <<<EOQ
            SELECT  pc.ExecDate
                ,   e.FfCode
                ,   e.FfName
                ,   e.FfBranchCode
                ,   e.FfBranchName
                ,   e.FfAccountClass
                ,   e.FfAccountNumber
                ,   e.FfAccountName
                ,   pc.DecisionPayment
                ,   pc.ExecScheduleDate
            FROM    T_Enterprise e
                    INNER JOIN T_PayingControl pc ON (pc.EnterpriseId = e.EnterpriseId)
            WHERE   pc.Seq = :Seq
            ;
EOQ;

        $ri = $this->app->dbAdapter->query($sql)->execute(array(':Seq' => $seq));
        $csvData = ResultInterfaceToArray($ri);

        // 合計の算出
        $totalCnt = 0;
        $totalDecisionPayment = 0;

        // ファイル名
        $payingDataFileName = sprintf("SpecialTransferData_%s.csv", date("YmdHis"));

        // ファイルフルパス
        $payingDataFullFileName = $payingDataDir . '/' . $payingDataFileName;

        // ヘッダーレコード
        $headerRecord = sprintf(
        "1,21,0,1848513200,ｶ)ｷｬｯﾁﾎﾞｰﾙ,%02d%02d,0009,,661,,1,8047001,\r\n",
        date('m', strtotime($csvData[0]['ExecScheduleDate'])),
        date('d', strtotime($csvData[0]['ExecScheduleDate']))
        );
        $headerRecord = mb_convert_encoding($headerRecord, "SJIS", "UTF-8");

        // データレコード
        $dataRecords = "";

        // count関数対策
        $csvDataLen = 0;
        if(!empty($csvData)) {
            $csvDataLen = count($csvData);
        }

        for ($i = 0 ; $i < $csvDataLen ; $i++) {

            $totalCnt++;
            $totalDecisionPayment += $csvData[$i]['DecisionPayment'];

            $dataRecord = sprintf(
            "2,%d,%s,%d,%s,,%d,%d,%s,%d,0,,,,,\r\n",
            $csvData[$i]['FfCode'],             // 銀行コード
            $csvData[$i]['FfName'],             // 銀行名
            $csvData[$i]['FfBranchCode'],       // 支店コード
            $csvData[$i]['FfBranchName'],       // 支店名
            $csvData[$i]['FfAccountClass'],     // 科目
            $csvData[$i]['FfAccountNumber'],    // 口座番号
            $csvData[$i]['FfAccountName'],      // 受取人
            $csvData[$i]['DecisionPayment']     // 金額
            );

            $dataRecords .= mb_convert_encoding($dataRecord, "SJIS", "UTF-8");
        }

        // トレーラレコード
        $trailerRecord = sprintf(
        "8,%d,%d,\r\n",
        $totalCnt,
        $totalDecisionPayment
        );
        $trailerRecord = mb_convert_encoding($trailerRecord, "SJIS", "UTF-8");

        // エンドレコード
        $endRecord = "9,\r\n";
        $endRecord = mb_convert_encoding($endRecord, "SJIS", "UTF-8");

        // ファイル保存
        if (!file_exists($payingDataFullFileName)) {
            $contents = $headerRecord . $dataRecords . $trailerRecord . $endRecord;
            $result = file_put_contents($payingDataFullFileName, $contents);

            if ($result == false)
            {
                // 処理なし
            }
            else {
                // ファイルのフルパスを返す
                $result = $payingDataFullFileName;
            }
        }
        else {
            // 同名ファイルがある場合は失敗
            $result = false;
        }

        return $result;
    }

    /**
     * 支払完了処理
     */
    public function update_action($seq)
    {
        // 画面情報取得
        $params = $this->getParams();

        // 加盟店ID(入力)
        $searchEnterpriseId = $params["SearchEnterpriseId"];
        // 加盟店ID
        $enterpriseId = $params["EnterpriseId"];
        // 加盟店名
        $enterpriseName = $params["EnterpriseName"];
        // 立替締め日
        $payingControlDate = $params["PayingControlDate"];
        // 立替予定日
        $payingPlanDate = $params["PayingPlanDate"];

        // 支払額
        $pcInfo = $params["PcList"];
        $decitionPayment = 0;
        if (isset($pcInfo)) {
            $decitionPayment = $pcInfo[$seq]["DecisionPayment"];
        }

        // 立替振込管理の更新
        $obj = new TableUser($this->app->dbAdapter);
        $mdlPc = new TablePayingControl($this->app->dbAdapter);

        // ユーザID
        $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);
        // 更新処理
        $data = array(
            "ExecFlg" => ($decitionPayment >= 0) ? 1 : 10,
            "ExecDate" => date("Y-m-d"),
            "UpdateId" => $userId,
        );
        $mdlPc->saveUpdate($data, $seq);

        // 一覧データ取得
        $pcList = $this->getPayingControlList($enterpriseId);

        $simuListData = $params["SimuList"];
        if (isset($simuListData)) {
            $simuList = $this->getSimulatePayingList($enterpriseId, $payingControlDate);
        }
        else {
            $simuList = array();
        }

        // フラグ維持
        $csvDownloadFlg = isset($params["csvDownloadFlg"]) ? $params["csvDownloadFlg"] : "0";

        // 立替完了メール送信
        $oemId = (int)$this->app->dbAdapter->query(" SELECT IFNULL(OemId, 0) AS OemId FROM T_Enterprise WHERE EnterpriseId = :EnterpriseId "
            )->execute(array(':EnterpriseId' => $enterpriseId))->current()['OemId'];
        if ($oemId > 0) {
           ;// OEMの場合はメール送信しない
        } else {
            // OEMではない場合はメールを送信する
            $mail = new \Coral\Coral\Mail\CoralMail($this->app->dbAdapter, $this->app->mail['smtp']);
            $mail->SendExecChargeMail($seq, $userId);
        }

        // 一覧画面を表示
        $this->setTemplate("list");
        $this->view->assign("SearchEnterpriseId", $searchEnterpriseId);
        $this->view->assign("EnterpriseId", $enterpriseId);
        $this->view->assign("EnterpriseName", $enterpriseName);
        $this->view->assign("PayingControlDate", $payingControlDate);
        $this->view->assign("PayingPlanDate", $payingPlanDate);
        $this->view->assign("SimuList", $simuList);
        $this->view->assign("PcList", $pcList);
        $this->view->assign("csvDownloadFlg", $csvDownloadFlg);

        $this->view->assign("CompleteMessage", sprintf('<font color="red"><b>更新しました。　%s</b></font>', date("Y-m-d H:i:s")));

        // セッションにアクション設定
        $_SESSION[self::SESS_PROCESS_ID] = "update".$seq;

        return $this->view;
    }

    /**
     * CSVダウンロード事前処理
     */
    function dcsvreserveAction()
    {
        // パラメータ取得
        $params = $this->getParams();

        // 加盟店ID取得
        $entId = $params['entId'];

        // 管理Seq取得
        $pcseq = $params['pcseq'];

        // CSVダウンロードフラグ
        $flg = $params['flg'];

        // ---------------------------
        // ダウンロードファイルの存在チェック
        // ---------------------------
        // 立替振込管理の取得
        $mdlpc = new TablePayingControl($this->app->dbAdapter);
        $pc = $mdlpc->find($pcseq);
        if ($pc->count() == 0) {
            // 該当がない場合終了　加盟店検索後の表示にする
            $res = $this->search_action($entId, $flg);
            $this->view->assign("ErrorMessage", "ダウンロードするCSVが存在しません。");
            return $res;
        }
        $pc = $pc->current();

        // CSVのファイルパス
        $payingDataFilePath = $pc["PayingDataFilePath"];
        // ファイルパスがない場合終了　加盟店検索後の表示にする
        if (is_null($payingDataFilePath)) {
            $res = $this->search_action($entId, $flg);
            $this->view->assign("ErrorMessage", "ダウンロードするCSVが存在しません。");
            return $res;
        }

        // ---------------------------
        // 立替振込管理テーブルを更新
        // ---------------------------
        // ユーザーIDの取得
        $obj = new TableUser( $this->app->dbAdapter );
        $userId = $obj->getUserId( 0, $this->app->authManagerAdmin->getUserInfo()->OpId );

        $data = array();
        $data['PayingDataDownloadFlg'] = 1;
        $data['UpdateId'] = $userId;

        // 更新処理
        $mdlpc->saveUpdate($data, $pcseq);

        if ($flg == "1") {
            // 検索処理
            $res = $this->search_action($entId, $flg);

            $this->view->assign("entId", $entId);
            $this->view->assign("pcseq", $pcseq);
            $this->view->assign("csvDownloadFlg", $flg);

            return $res;
        }
        else {
            // javascript無効時はメッセージなしでダウンロード
            return $this->dcsvAction();
        }
    }

    /**
     * CSVダウンロード
     */
    function dcsvAction()
    {
        // パラメータ取得
        $params = $this->getParams();

        // 加盟店ID取得
        $entId = $params['entId'];

        // 管理Seq取得
        $pcSeq = $params['pcseq'];

        // ---------------------------
        // ダウンロードファイルの存在チェック
        // ---------------------------
        // 立替振込管理の取得
        $mdlpc = new TablePayingControl($this->app->dbAdapter);
        $pc = $mdlpc->find($pcSeq)->current();

        // ファイル名
        $fileName = sprintf("SpecialTransferData_%s.csv", date("YmdHis"));

        // ---------------------------
        // ファイルダウンロード
        // ---------------------------
        $response = $this->getResponse();

        $dispValue = 'attachment';

        // レスポンスヘッダの出力
        $contentType = 'application/octet-stream';
        $response->getHeaders()->addHeaderLine( 'Content-Type', $contentType )
        ->addHeaderLine( 'Content-Disposition' , "$dispValue; filename=$fileName" );

        // データ出力
        echo $pc["PayingDataFilePath"];

        return $response;
    }

    /**
     * 調整金を表示する。
     */
    public function adjustAction()
    {
        $params = $this->getParams();

        $eid = isset($params['eid']) ? $params['eid'] : -1;
        $pdate = isset($params['pdate']) ? $params['pdate'] : 0;
        $loginid = isset($params['loginid']) ? $params['loginid'] : -1;
        $ppdate = isset($params['ppdate']) ? $params['ppdate'] : null;

        // 加盟店名取得
        $enterprise = $this->getEnterpriseByLoginId($loginid);
        $ename = $enterprise["EnterpriseNameKj"];

        // 立替締め日
        $payingControlDate = date("Y-m-d");

        // 調整額データ取得
        $sql = <<<EOQ
            SELECT  aa.EnterpriseId
                ,   aa.SerialNumber
                ,   aa.OrderId
                ,   c.NameKj
                ,   aa.OrderSeq
                ,   aa.ItemCode
                ,   aa.AdjustmentAmount
                ,   aa.RegistDate
                ,   F_GetLoginUserName( aa.RegistId ) AS RegistName
            FROM    W_AdjustmentAmount aa
                    LEFT OUTER JOIN T_Order o ON (o.OrderSeq = aa.OrderSeq)
                    LEFT OUTER JOIN T_Customer c ON (c.OrderSeq = o.OrderSeq)
            WHERE   aa.EnterpriseId = :EnterpriseId
            ;
EOQ;
        $ri = $this->app->dbAdapter->query($sql)->execute(array( ':EnterpriseId' => $eid ));
        $data = ResultInterfaceToArray($ri);

        // 調整金科目
        $ri = $this->app->dbAdapter->query(" SELECT KeyCode, KeyContent FROM M_Code WHERE Validflg = 1 AND CodeId = 89 ORDER BY KeyCode ")->execute(null);
        $kamokuList = ResultInterfaceToArray($ri);

        $this->view->assign('eid', $eid);
        $this->view->assign('pdate', $pdate);
        $this->view->assign('loginid', $loginid);
        $this->view->assign('ppdate', $ppdate);
        $this->view->assign('ename', $ename);
        $this->view->assign('adjlist', $data);
        $this->view->assign('kamokuList', $kamokuList);


        return $this->view;
    }

    /**
     * 更新処理
     */
    public function confirmAction()
    {
        $updatecount = 0;       // 更新件数カウント用
        $deletecount = 0;       // 削除件数カウント用

        // トランザクション開始
        $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
        try {
            // パラメータ取得
            $params = $this->getParams();

            $delSerialNumbers = array();
            $orderIds = array();
            $itemCodes = array();
            $adjustmentAmounts = array();

            foreach( $params as $key => $param ) {
                // $key から SerialNumber 取得
                if( strstr( $key, 'item_delete_chk_' ) != false ) {
                    $delSerialNumbers[] = str_replace( 'item_delete_chk_', '', $key );
                    // 注文ID
                } else if( strstr( $key, 'orderid_' ) != false ) {
                    $orderIds[str_replace( 'orderid_', '', $key )] = $param;
                    // 科目
                } else if( strstr( $key, 'itemcode_' ) != false ) {
                    $itemCodes[str_replace( 'itemcode_', '', $key )] = $param;
                    // 調整額
                } else if( strstr( $key, 'adjamount_' ) != false ) {
                    $adjustmentAmounts[str_replace( 'adjamount_', '', $key )] = $param;
                    // 加盟店ID
                } else if( $key == 'eid' ) {
                    $eid = $param;
                }
            }

            // ユーザーIDの取得
            $obj = new TableUser( $this->app->dbAdapter );
            $userId = $obj->getUserId( 0, $this->app->authManagerAdmin->getUserInfo()->OpId );

            // 調整額の削除
            foreach( $delSerialNumbers as $serialNumber ) {

                // 調整額管理の物理削除
                $sql = " DELETE FROM W_AdjustmentAmount WHERE EnterpriseId = :EnterpriseId AND SerialNumber = :SerialNumber ";
                $this->app->dbAdapter->query($sql)->execute(array('EnterpriseId' => $eid, 'SerialNumber' => $serialNumber));

                // 削除件数カウントアップ
                $deletecount++;
            }

            // 調整額の更新
            foreach ($adjustmentAmounts as $key => $adjustmentAmount) {
                // 調整額が入力されている場合、処理を行う。→ 調整額が入力されていなければ処理しない
                if (! empty($adjustmentAmount)) {
                    // 新規追加／更新用データ配列
                    $data = array();
                    // 注文ID取得
                    $orderId = $orderIds[$key];

                    $orderSeq = null;// 注文SEQのNULL初期化

                    // 注文IDが入力されている場合は以下判定を行う。
                    if (! empty($orderId)) {
                        // 入力された注文IDで注文情報を取得する。
                        $sql = "SELECT EnterpriseId, IFNULL(OemId, 0) AS OemId, OrderSeq FROM T_Order WHERE OrderId = :OrderId";
                        $odata = $this->app->dbAdapter->query($sql)->execute(array(':OrderId' => $orderId))->current();
                        $orderSeq = $odata['OrderSeq'];

                        // OEMIDの取得
                        $oemId = (int)$this->app->dbAdapter->query(" SELECT IFNULL(OemId, 0) AS OemId FROM T_Enterprise WHERE EnterpriseId = :EnterpriseId "
                        )->execute(array(':EnterpriseId' => $eid))->current()['OemId'];

                        // 現在の加盟店IDとOEMIDを取得した情報と比較する。
                        if(! ($odata['EnterpriseId'] == $eid && $odata['OemId'] == $oemId)) {
                            $msg = '入力された注文IDは現在の加盟店ID、OEMIDが違います。';
                            // ロールバック
                            $this->app->dbAdapter->getDriver()->getConnection()->rollback();
                            // 処理を中止
                            break;
                        }
                    }

                    // 新規行判定用 → 新規行の場合は $value に値が入る
                    $value = strstr($key, 'new');
                    // 新規行は新規追加
                    if (! empty($value)) {
                        // 立替振込管理Seqに対する連番のMAX値を取得する。
                        $sql = "SELECT MAX(SerialNumber) AS SerialNumber FROM W_AdjustmentAmount WHERE EnterpriseId = :EnterpriseId";
                        $maxNum = $this->app->dbAdapter->query($sql)->execute(array( ':EnterpriseId' => $eid ))->current();

                        // データ行を新規作成
                        $data = array(
                                ':EnterpriseId' => $eid,                                 // 加盟店ID
                                ':SerialNumber' => nvl($maxNum['SerialNumber'], 0) + 1,  // 連番
                                ':OrderId' => $orderId,                                  // 注文ID
                                ':OrderSeq' => $orderSeq,                                // 注文Seq
                                ':ItemCode' => $itemCodes[$key],                         // 科目
                                ':AdjustmentAmount' => $adjustmentAmount,                // 調整額
                                ':RegistDate' => date('Y-m-d H:i:s'),                    // 登録日時
                                ':RegistId' => $userId,                                  // 登録者
                                ':UpdateDate' => date('Y-m-d H:i:s'),                    // 更新日時
                                ':UpdateId' => $userId,                                  // 更新者
                                ':ValidFlg' => '1',
                        );

                        $sql  = "INSERT INTO W_AdjustmentAmount ";
                        $sql .= " (EnterpriseId, SerialNumber, OrderId, OrderSeq, ItemCode, AdjustmentAmount, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES ";
                        $sql .= " (:EnterpriseId, :SerialNumber, :OrderId, :OrderSeq, :ItemCode, :AdjustmentAmount, :RegistDate, :RegistId, :UpdateDate, :UpdateId, :ValidFlg)";
                        // 新規追加
                        $this->app->dbAdapter->query($sql)->execute($data);

                        // 更新件数カウントアップ
                        $updatecount++;
                    }
                    // 既存行 かつ 削除対象でない場合
                    if (empty($value) && ! in_array($key, $delSerialNumbers)) {
                        // 登録済みのデータを取得
                        $sql = " SELECT * FROM W_AdjustmentAmount WHERE EnterpriseId = :EnterpriseId AND SerialNumber = :SerialNumber ";
                        $aadata = $this->app->dbAdapter->query($sql)->execute(array( ':EnterpriseId' => $eid, ':SerialNumber' => $key ))->current();
                        // 注文ID、調整科目、調整額が違う場合、更新する。
                        if ($orderId != $aadata['OrderId'] || $itemCodes[$key] != $aadata['ItemCode'] || $adjustmentAmount != $aadata['AdjustmentAmount']) {
                            // 更新用データを生成
                            $data = array(
                                    ':OrderId' => $orderId,                          // 注文ID
                                    ':OrderSeq' => $orderSeq,                        // 注文Seq
                                    ':ItemCode' => $itemCodes[$key],                 // 科目
                                    ':AdjustmentAmount' => $adjustmentAmount,        // 調整額
                                    ':UpdateDate' => date('Y-m-d H:i:s'),            // 更新日時
                                    ':UpdateId' => $userId,                          // 更新者
                                    ':EnterpriseId' => $eid,
                                    ':SerialNumber' => $key,
                            );

                            $sql  = "UPDATE W_AdjustmentAmount ";
                            $sql .= "SET OrderId = :OrderId, OrderSeq = :OrderSeq, ItemCode = :ItemCode, AdjustmentAmount = :AdjustmentAmount, UpdateDate =:UpdateDate, UpdateId = :UpdateId ";
                            $sql .= " WHERE EnterpriseId = :EnterpriseId AND SerialNumber = :SerialNumber ";
                            // 更新処理
                            $this->app->dbAdapter->query($sql)->execute($data);

                            // 更新件数カウントアップ
                            $updatecount++;
                        }
                    }
                }
            }
            if (! isset($msg)) {
                // コミット
                $this->app->dbAdapter->getDriver()->getConnection()->commit();
            }

            if (! isset($msg)){
                // 成功指示
                $msg = '1';
            }
        } catch (\Exception $e) {
            // ロールバック
            $this->app->dbAdapter->getDriver()->getConnection()->rollback();
            $msg = $e->getMessage();
        }

        echo \Zend\Json\Json::encode(array('status' => $msg, 'updatecount' => $updatecount, 'deletecount' => $deletecount));
        return $this->response;
    }

    /**
     * 調整額管理 登録用データの作成
     *
     * @param int $enterpriseId 加盟店ID
     * @param int $seq 立替振込管理SEQ
     * @return true:
     */
    function getAASaveData($enterpriseId, $seq)
    {
        $mdlaa = new TableAdjustmentAmount($this->app->dbAdapter);

        $sql = "SELECT * FROM W_AdjustmentAmount WHERE EnterpriseId = :EnterpriseId";
        $datas = ResultInterfaceToArray($this->app->dbAdapter->query($sql)->execute(array (':EnterpriseId' => $enterpriseId)));

        // count関数対策
        if (!empty($datas)){
            $datasLen = count($datas);
            for ($i = 0 ; $i < $datasLen ; $i++) {
                // 立替振込管理SEQ
                $data['PayingControlSeq'] = $seq;
                //連番
                $data['SerialNumber'] = $datas[$i]['SerialNumber'];
                //注文ID
                $data['OrderId'] = $datas[$i]['OrderId'];
                //注文SEQ
                $data['OrderSeq'] = $datas[$i]['OrderSeq'];
                //科目コード
                $data['ItemCode'] = $datas[$i]['ItemCode'];
                //調整額
                $data['AdjustmentAmount'] = $datas[$i]['AdjustmentAmount'];
                //登録者
                $data['RegistId'] = $datas[$i]['RegistId'];
                //更新者
                $data['UpdateId'] = $datas[$i]['UpdateId'];
                //有効フラグ
                $data['ValidFlg'] = 1;
                // 登録処理
                $mdlaa->saveNew($data);
            }
            // 調整額管理ワーク 削除
            $sql = " DELETE FROM W_AdjustmentAmount WHERE EnterpriseId = :EnterpriseId ";
            $this->app->dbAdapter->query($sql)->execute(array(':EnterpriseId' => $enterpriseId));
        }

        return true;
    }
}
