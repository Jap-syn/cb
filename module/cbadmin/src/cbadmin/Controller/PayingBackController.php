<?php
namespace cbadmin\Controller;

use Coral\Base\BaseHtmlUtils;
use Coral\Base\BaseGeneralUtils;
use Coral\Coral\Controller\CoralControllerAction;
use Coral\Coral\CoralCodeMaster;
use Coral\Coral\CoralValidate;
use cbadmin\Application;
use models\Table\TableCode;
use models\Table\TableOem;
use models\Table\TablePayingBackControl;
use models\Table\TableUser;
use Coral\Coral\History\CoralHistoryOrder;

class PayingBackController extends CoralControllerAction
{
    protected $_componentRoot = './application/views/components';

    const SESS_SEARCH_INFO = 'SESS_SEARCH_INFO';
    const SESS_UPDATE_INFO = 'SESS_UPDATE_INFO';

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

        $this->setPageTitle("後払い.com - 立替精算戻し指示一覧");
    }

    /**
     * 立替精算戻し指示一覧を表示する。
     */
    public function listAction()
    {
        // ---------------------------
        // 表示条件SELECTタグ表示
        // ---------------------------
        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);

        // 遅延経過日数種類
        $dpdtSelect = 1;
        $damageProgressDaysType = BaseHtmlUtils::SelectTag('DamageProgressDaysType',$codeMaster->getMasterCodes(81), $dpdtSelect, 'style="width: 100px;"');

        // 遅延経過日数FROM
        $damageProgressDaysFrom = 8;

        // 日数比較種類
        $dctList = $this->getDaysCompareType();
        $dctSelect = $dctList["key"];
        $daysCompareType = BaseHtmlUtils::SelectTag('DaysCompareType',$dctList["list"], $dctSelect, 'style="width: 80px;" onchange="daysCompareTypeChange();"');

        // 遅延経過日数TO
        $damageProgressDaysTo = "";

        // 請求パターン
        $cpSelect = -1;
        $claimPattern = BaseHtmlUtils::SelectTag('ClaimPattern',$codeMaster->getMasterCodes(12, array(-1 => "-----")), $cpSelect, 'style="width: 100px;"');

        // 発行状態
        $ptSelect = 0;
        $printType = BaseHtmlUtils::SelectTag('PrintType',$codeMaster->getMasterCodes(83), $ptSelect, 'style="width: 110px;"');

        // ---------------------------
        // 結果表示
        // ---------------------------
        $this->view->assign("DamageProgressDaysType", $damageProgressDaysType);
        $this->view->assign("DamageProgressDaysFrom", $damageProgressDaysFrom);
        $this->view->assign("DaysCompareType", $daysCompareType);
        $this->view->assign("DamageProgressDaysTo", $damageProgressDaysTo);
        $this->view->assign("ClaimPattern", $claimPattern);
        $this->view->assign("PrintType", $printType);

        return $this->view;
    }

    /**
     * 日数比較種類取得
     */
    public function getDaysCompareType()
    {
        $mdlCode = new TableCode($this->app->dbAdapter);

        $dcTypeList = ResultInterfaceToArray($mdlCode->getMasterByClass(82));

        $list = array();
        $defKey = "";
        foreach ($dcTypeList as $row) {
            // KEYコード＋区分１を選択時の値とする
            $value = $row["KeyCode"] . "_" . $row["Class1"];

            // KEYコード内容を表示文字列とする
            $caption = $row["KeyContent"];

            $list[$value] = $caption;

            // デフォルトキー取得
            if ($row["KeyCode"] == 1) {
                $defKey = $value;
            }
        }

        $result["key"] = $defKey;
        $result["list"] = $list;
        return $result;
    }

    /**
     * 表示ボタン処理
     */
    public function searchAction()
    {
        // 画面情報取得
        $params = $this->getParams();

        // 表示ボタンの場合、一覧絞込情報クリア
        if (array_key_exists("show_button", $params)) {
            $params["Oem"] = 0;
            $params["EnterpriseId"] = "";
            $params["EnterpriseName"] = "";
            $params["OrderId"] = "";
        }

        // 更新後のリダイレクトで実行された場合、セッションから取得
        if (isset($_SESSION[self::SESS_UPDATE_INFO])) {
            $params = $_SESSION[self::SESS_SEARCH_INFO];
        }

        // 対象検索
        $payingBackList = $this->getPayingBackList($params);

        // ---------------------------
        // 表示条件SELECTタグ表示
        // ---------------------------
        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
        $mdloem = new TableOem($this->app->dbAdapter);

        // 遅延経過日数種類
        $dpdtSelect = $params["DamageProgressDaysType"];
        $damageProgressDaysType = BaseHtmlUtils::SelectTag('DamageProgressDaysType',$codeMaster->getMasterCodes(81), $dpdtSelect, 'style="width: 100px;"');

        // 遅延経過日数FROM
        $damageProgressDaysFrom = $params["DamageProgressDaysFrom"];

        // 日数比較種類
        $dctList = $this->getDaysCompareType();
        $dctSelect = $params["DaysCompareType"];
        $daysCompareType = BaseHtmlUtils::SelectTag('DaysCompareType',$dctList["list"], $dctSelect, 'style="width: 80px;" onchange="daysCompareTypeChange();"');

        // 遅延経過日数TO
        $damageProgressDaysTo = $params["DamageProgressDaysTo"];

        // 請求パターン
        $cpSelect = $params["ClaimPattern"];
        $claimPattern = BaseHtmlUtils::SelectTag('ClaimPattern',$codeMaster->getMasterCodes(12, array(-1 => "-----")), $cpSelect, 'style="width: 100px;"');

        // 発行状態
        $ptSelect = $params["PrintType"];
        $printType = BaseHtmlUtils::SelectTag('PrintType',$codeMaster->getMasterCodes(83), $ptSelect, 'style="width: 110px;"');

        // OEM
        $oemSelect = $params["Oem"];
        if (is_null($oemSelect)) $oemSelect = 0;
        $oem = BaseHtmlUtils::SelectTag('Oem', $mdloem->getOemIdList(), $oemSelect);

        // count関数対策
        $payingBackListCnt = 0;
        if (!empty($payingBackList)){
            $payingBackListCnt = count($payingBackList);
        }

        // ---------------------------
        // 結果表示
        // ---------------------------
        $this->setTemplate("list");
        $this->view->assign("DamageProgressDaysType", $damageProgressDaysType);
        $this->view->assign("DamageProgressDaysFrom", $damageProgressDaysFrom);
        $this->view->assign("DaysCompareType", $daysCompareType);
        $this->view->assign("DamageProgressDaysTo", $damageProgressDaysTo);
        $this->view->assign("ClaimPattern", $claimPattern);
        $this->view->assign("PrintType", $printType);
        $this->view->assign("Oem", $oem);
        $this->view->assign("EnterpriseId", $params["EnterpriseId"]);
        $this->view->assign("EnterpriseName", $params["EnterpriseName"]);
        $this->view->assign("OrderId", $params["OrderId"]);
        $this->view->assign("PayingBackListCnt", $payingBackListCnt);
        $this->view->assign("PayingBackList", $payingBackList);
        if (isset($_SESSION[self::SESS_UPDATE_INFO])) {
            $this->view->assign("Message", $_SESSION[self::SESS_UPDATE_INFO]);
            unset($_SESSION[self::SESS_UPDATE_INFO]);
        }

        // セッションに検索情報保存
        $searchInfo["DamageProgressDaysType"] = $dpdtSelect;
        $searchInfo["DamageProgressDaysFrom"] = $params["DamageProgressDaysFrom"];
        $searchInfo["DaysCompareType"] = $dctSelect;
        $searchInfo["DamageProgressDaysTo"] = $params["DamageProgressDaysTo"];
        $searchInfo["ClaimPattern"] = $cpSelect;
        $searchInfo["PrintType"] = $ptSelect;
        $searchInfo["Oem"] = $oemSelect;
        $searchInfo["EnterpriseId"] = $params["EnterpriseId"];
        $searchInfo["EnterpriseName"] = $params["EnterpriseName"];
        $searchInfo["OrderId"] = $params["OrderId"];

        $_SESSION[self::SESS_SEARCH_INFO] = $searchInfo;

        return $this->view;
    }

    /**
     * 立替精算戻し指示対象取得
     *
     * @param array $params 画面情報
     * @return array 立替精算戻し指示対象
     */
    public function getPayingBackList($params)
    {
        // 指示済み
        $sqlA  = "SELECT OEM.OemNameKj ";
        $sqlA .= "     , ORD.EnterpriseId ";
        $sqlA .= "     , ENT.EnterpriseNameKj ";
        $sqlA .= "     , ORD.OrderId ";
        $sqlA .= "     , ORD.Ent_OrderId ";
        $sqlA .= "     , CST.NameKj ";
        $sqlA .= "     , ORD.ReceiptOrderDate ";
        $sqlA .= "     , PAS.OccDate ";
        $sqlA .= "     , PC.FixedDate ";
        $sqlA .= "     , PAS.ChargeAmount ";
        $sqlA .= "     , PAS.SettlementFee ";
        $sqlA .= "     , PAS.ClaimFee ";
        $sqlA .= "     , CC.ReceiptAmountTotal ";
        $sqlA .= "     , PBC.PayBackAmount ";
        $sqlA .= "     , PBC.ValidFlg AS instFlg ";
        $sqlA .= "     , PBC.PayingBackSeq ";
        $sqlA .= "     , ORD.OrderSeq ";
        $sqlA .= "     , EC.ManCustId ";
        $sqlA .= "  FROM T_Order ORD ";
        $sqlA .= "       INNER JOIN T_PayingBackControl PBC ON PBC.OrderSeq = ORD.OrderSeq ";
        $sqlA .= "       INNER JOIN T_PayingAndSales PAS ON PAS.OrderSeq = ORD.OrderSeq ";
        $sqlA .= "       INNER JOIN T_PayingControl PC ON PC.Seq = PAS.PayingControlSeq ";
        $sqlA .= "       INNER JOIN T_Enterprise ENT ON ENT.EnterpriseId = ORD.EnterpriseId ";
        $sqlA .= "       INNER JOIN T_Customer CST ON CST.OrderSeq = ORD.OrderSeq ";
        $sqlA .= "       INNER JOIN T_ClaimControl CC ON CC.OrderSeq = ORD.OrderSeq ";
        $sqlA .= "       LEFT JOIN T_Oem OEM ON OEM.OemId = ORD.OemId ";
        $sqlA .= "       LEFT JOIN T_EnterpriseCustomer EC ON EC.EntCustSeq = CC.EntCustSeq ";
        $sqlA .= " WHERE ORD.OutOfAmends = 1 ";
        $sqlA .= "   AND ORD.Chg_ExecDate IS NOT NULL ";
        $sqlA .= "   AND PBC.PayDecisionFlg = 0 ";

        // 未指示
        $sqlB  = "SELECT OEM.OemNameKj ";
        $sqlB .= "     , ORD.EnterpriseId ";
        $sqlB .= "     , ENT.EnterpriseNameKj ";
        $sqlB .= "     , ORD.OrderId ";
        $sqlB .= "     , ORD.Ent_OrderId ";
        $sqlB .= "     , CST.NameKj ";
        $sqlB .= "     , ORD.ReceiptOrderDate ";
        $sqlB .= "     , PAS.OccDate ";
        $sqlB .= "     , PC.FixedDate ";
        $sqlB .= "     , PAS.ChargeAmount ";
        $sqlB .= "     , PAS.SettlementFee ";
        $sqlB .= "     , PAS.ClaimFee ";
        $sqlB .= "     , CC.ReceiptAmountTotal ";
        $sqlB .= "     , (PAS.ChargeAmount + PAS.SettlementFee + PAS.ClaimFee - CC.ReceiptAmountTotal) * -1 AS PayBackAmount ";
        $sqlB .= "     , 0 AS instFlg ";
        $sqlB .= "     , 0 AS PayingBackSeq ";
        $sqlB .= "     , ORD.OrderSeq ";
        $sqlB .= "     , EC.ManCustId ";
        $sqlB .= "  FROM T_Order ORD ";
        $sqlB .= "       INNER JOIN T_PayingAndSales PAS ON PAS.OrderSeq = ORD.OrderSeq ";
        $sqlB .= "       INNER JOIN T_PayingControl PC ON PC.Seq = PAS.PayingControlSeq ";
        $sqlB .= "       INNER JOIN T_Enterprise ENT ON ENT.EnterpriseId = ORD.EnterpriseId ";
        $sqlB .= "       INNER JOIN T_Customer CST ON CST.OrderSeq = ORD.OrderSeq ";
        $sqlB .= "       INNER JOIN T_ClaimControl CC ON CC.OrderSeq = ORD.OrderSeq ";
        $sqlB .= "       LEFT JOIN T_Oem OEM ON OEM.OemId = ORD.OemId ";
        $sqlB .= "       LEFT JOIN T_EnterpriseCustomer EC ON EC.EntCustSeq = CC.EntCustSeq ";
        $sqlB .= " WHERE ORD.OutOfAmends = 1 ";
        $sqlB .= "   AND ORD.Chg_ExecDate IS NOT NULL ";
        $sqlB .= "   AND PAS.UseAmount > CC.ReceiptAmountTotal ";
        $sqlB .= "   AND NOT EXISTS (SELECT 1 ";
        $sqlB .= "                     FROM T_PayingBackControl PBC ";
        $sqlB .= "                    WHERE PBC.OrderSeq = ORD.OrderSeq ";
        $sqlB .= "                  ) ";

        // 画面条件
        $sqlWhere = "";
        $sqlPara = array();

        // 遅延経過日数
        // 日数比較種類判定
        $cond = "";
        $dayFrom = intval($params["DamageProgressDaysFrom"]);
        $dayTo = intval($params["DamageProgressDaysTo"]);
        switch ($params["DaysCompareType"]) {
            case 0: // 未選択は一致で判定
                $cond = " AND *** = :DayFrom ";
                $sqlPara[":DayFrom"] = $dayFrom;
                break;
            case 1: // 以上
                $cond = " AND *** >= :DayFrom ";
                $sqlPara[":DayFrom"] = $dayFrom;
                break;
            case 2: // 以下
                $cond = " AND *** <= :DayFrom ";
                $sqlPara[":DayFrom"] = $dayFrom;
                break;
            case 3: // から
                $cond = " AND *** >= :DayFrom AND *** <= :DayTo ";
                $sqlPara[":DayFrom"] = $dayFrom;
                $sqlPara[":DayTo"] = $dayTo;
                break;
            case 4: // より大
                $cond = " AND *** > :DayFrom ";
                $sqlPara[":DayFrom"] = $dayFrom;
                break;
            case 5: // 未満
                $cond = " AND *** < :DayFrom ";
                $sqlPara[":DayFrom"] = $dayFrom;
                break;
            default:
                break;
        }
        if ($params["DamageProgressDaysType"] == 0) {
            // 未選択の場合は条件なし
            $cond = "";
        }
        elseif ($params["DamageProgressDaysType"] == 1) {
            // 経過日数
            if (strlen($cond) > 0) {
                $cond = str_replace("***", sprintf("DATEDIFF(date('%s'), CC.ClaimDate)", date('Y-m-d')) , $cond);
            }
        }elseif ($params["DamageProgressDaysType"] == 2) {
            // 遅延日数
            if (strlen($cond) > 0) {
                $cond = str_replace("***", sprintf("DATEDIFF(date('%s'), CC.DamageBaseDate)", date('Y-m-d')) , $cond);
            }
        }
        $sqlWhere .= $cond;

        // 請求パターン＋発行状態
        $cond = "";
        if (isset($params["ClaimPattern"]) && $params["ClaimPattern"] != -1) {
            // 選択時のみ条件付与
            if ($params["PrintType"] == 1) {
                // 発行している
                $cond = " AND CC.ClaimPattern >= :ClaimPattern ";
                $sqlPara[":ClaimPattern"] = $params["ClaimPattern"];
            }
            elseif ($params["PrintType"] == 2) {
                // 発行していない
                $cond = " AND CC.ClaimPattern < :ClaimPattern ";
                $sqlPara[":ClaimPattern"] = $params["ClaimPattern"];
            }
            else {
                // 一致
                $cond = " AND CC.ClaimPattern = :ClaimPattern ";
                $sqlPara[":ClaimPattern"] = $params["ClaimPattern"];
            }
        }
        $sqlWhere .= $cond;

        // OEM
        $cond = "";
        if (isset($params["Oem"])) {
            if ($params["Oem"] != 0) {
                // 選択時のみ条件付与
                $cond = " AND ORD.OemId = :OemId ";
                $sqlPara[":OemId"] = $params["Oem"];
            }
        }
        $sqlWhere .= $cond;

        // 加盟店ID
        $cond = "";
        if (isset($params["EnterpriseId"])) {
            if (strlen($params["EnterpriseId"]) > 0) {
                // 入力時のみ条件付与
                $cond = " AND ENT.LoginId LIKE :EnterpriseId ";
                $sqlPara[":EnterpriseId"] = "%" . $params["EnterpriseId"];
            }
        }
        $sqlWhere .= $cond;

        // 加盟店名
        $cond = "";
        if (isset($params["EnterpriseName"])) {
            if (strlen($params["EnterpriseName"]) > 0) {
                // 入力時のみ条件付与
                $cond = " AND ENT.EnterpriseNameKj LIKE :EnterpriseNameKj ";
                $sqlPara[":EnterpriseNameKj"] = "%" . $params["EnterpriseName"] . "%";
            }
        }
        $sqlWhere .= $cond;

        // 注文ID
        $cond = "";
        if (isset($params["OrderId"])) {
            if (strlen($params["OrderId"]) > 0) {
                // 入力時のみ条件付与
                $cond = " AND ORD.OrderId LIKE :OrderId ";
                $sqlPara[":OrderId"] = "%" . $params["OrderId"];
            }
        }
        $sqlWhere .= $cond;

        $sqlOrder = " ORDER BY instFlg DESC, EnterpriseId, OrderSeq ";

        $sql = $sqlA . $sqlWhere . " UNION " . $sqlB . $sqlWhere . $sqlOrder;

        // SQL実行
        $ri = $this->app->dbAdapter->query($sql)->execute($sqlPara);
        $result = ResultInterfaceToArray($ri);

        return $result;
    }

    /**
     * 登録処理
     */
    public function saveAction()
    {
        // 画面情報取得
        $params = $this->getParams();

        // 更新処理
        $saveResult = $this->savePayingBackList($params);

        // 検索条件取得
        $searchInfo = $_SESSION[self::SESS_SEARCH_INFO];

        // 更新後のリスト取得
        $payingBackList = $this->getPayingBackList($searchInfo);

        // 失敗時
        if (!isset($_SESSION[self::SESS_UPDATE_INFO])) {
            // チェック状態の復元
            $formchk = $saveResult["formchk"];
            foreach($payingBackList as $key =>$value) {
                $payingBackList[$key]["instFlg"] = $formchk[$payingBackList[$key]["OrderSeq"]] == "on" ? "1" : "0";
            }
        }

        // ---------------------------
        // 表示条件SELECTタグ表示
        // ---------------------------
        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
        $mdloem = new TableOem($this->app->dbAdapter);

        // 遅延経過日数種類
        $dpdtSelect = $searchInfo["DamageProgressDaysType"];
        $damageProgressDaysType = BaseHtmlUtils::SelectTag('DamageProgressDaysType',$codeMaster->getMasterCodes(81), $dpdtSelect, 'style="width: 100px;"');

        // 遅延経過日数FROM
        $damageProgressDaysFrom = $searchInfo["DamageProgressDaysFrom"];

        // 日数比較種類
        $dctList = $this->getDaysCompareType();
        $dctSelect = $searchInfo["DaysCompareType"];
        $daysCompareType = BaseHtmlUtils::SelectTag('DaysCompareType',$dctList["list"], $dctSelect, 'style="width: 80px;" onchange="daysCompareTypeChange();"');

        // 遅延経過日数TO
        $damageProgressDaysTo = $searchInfo["DamageProgressDaysTo"];

        // 請求パターン
        $cpSelect = $searchInfo["ClaimPattern"];
        $claimPattern = BaseHtmlUtils::SelectTag('ClaimPattern',$codeMaster->getMasterCodes(12, array(-1 => "-----")), $cpSelect, 'style="width: 100px;"');

        // 発行状態
        $ptSelect = $searchInfo["PrintType"];
        $printType = BaseHtmlUtils::SelectTag('PrintType',$codeMaster->getMasterCodes(83), $ptSelect, 'style="width: 110px;"');

        // OEM
        $oemSelect = $searchInfo["Oem"];
        if (is_null($oemSelect)) $oemSelect = 0;
        $oem = BaseHtmlUtils::SelectTag('Oem', $mdloem->getOemIdList(), $oemSelect);

        // count関数対策
        $payingBackListCnt = 0;
        if (!empty($payingBackList)){
            $payingBackListCnt = count($payingBackList);
        }

        // ---------------------------
        // 結果表示
        // ---------------------------
        $this->setTemplate("list");
        $this->view->assign("DamageProgressDaysType", $damageProgressDaysType);
        $this->view->assign("DamageProgressDaysFrom", $damageProgressDaysFrom);
        $this->view->assign("DaysCompareType", $daysCompareType);
        $this->view->assign("DamageProgressDaysTo", $damageProgressDaysTo);
        $this->view->assign("ClaimPattern", $claimPattern);
        $this->view->assign("PrintType", $printType);
        $this->view->assign("Oem", $oem);
        $this->view->assign("EnterpriseId", $searchInfo["EnterpriseId"]);
        $this->view->assign("EnterpriseName", $searchInfo["EnterpriseName"]);
        $this->view->assign("OrderId", $searchInfo["OrderId"]);
        $this->view->assign("PayingBackListCnt", $payingBackListCnt);
        $this->view->assign("PayingBackList", $payingBackList);
        $this->view->assign("Message", $saveResult["message"]);

        // 成功時
        if (isset($_SESSION[self::SESS_UPDATE_INFO])) {
            // 検索処理へリダイレクト
            return $this->_redirect('payingback/search' );
        }

        return $this->view;
    }

    /**
     * 立替精算戻し指示登録
     *
     * @param array $params 画面情報
     * @return mixed メッセージ＋チェック情報
     */
    public function savePayingBackList($params)
    {
        $mdlPbc = new TablePayingBackControl($this->app->dbAdapter);
        $obj = new TableUser($this->app->dbAdapter);
        $history = new CoralHistoryOrder($this->app->dbAdapter);

        // チェックボックスを取得
        $list = $params["list"];

        // トランザクションの開始
        $db = $this->app->dbAdapter;
        try {
            $db->getDriver()->getConnection()->beginTransaction();

            // ユーザID取得
            $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

            // チェック状態保持
            $formchk = array();

            foreach($list as $row) {
                // 立替精算戻しSeq
                $payingBackSeq = $row["PayingBackSeq"];

                // チェック状態
                $chkFlg = $row["chkInst"];

                $formchk[$row["OrderSeq"]] = $chkFlg;

                if ($payingBackSeq == 0) {
                    // 未指示のデータ
                    if ($chkFlg == "on") {
                        // チェックオンの場合、登録
                        $data = array();

                        // 注文SEQ
                        $data["OrderSeq"] = $row["OrderSeq"];
                        // 立替精算戻し金額
                        $data["PayBackAmount"] = $row["PayBackAmount"];
                        // 立替精算戻し指示日
                        $data["PayBackIndicationDate"] = date('Y-m-d H:i:s');
                        // 立替確定フラグ
                        $data["PayDecisionFlg"] = 0;
                        // 立替確定日
                        $data["PayDecisionDate"] = null;
                        // 加盟店ID
                        $data["EnterpriseId"] = $row["EnterpriseId"];
                        // 立替振込管理SEQ
                        $data["PayingControlSeq"] = null;
                        // 本締め／仮締め区分
                        $data["PayingControlStatus"] = null;
                        // 登録者
                        $data["RegistId"] = $userId;
                        // 更新者
                        $data["UpdateId"] = $userId;
                        // 有効フラグ
                        $data["ValidFlg"] = 1;

                        $mdlPbc->saveNew($data);

                        // 注文履歴へ登録
                        $history->InsOrderHistory($row["OrderSeq"], 83, $userId);
                    }
                }
                else {
                    // 元のチェック状態
                    $oldChkFlg = $row["instFlg"] == 1 ? "on" : "off";

                    // チェックが変わった場合のみ更新
                    if ($oldChkFlg != $chkFlg) {
                        // 指示済みのデータ
                        $data = array();

                        // 更新者
                        $data["UpdateId"] = $userId;
                        // 有効フラグ
                        $data["ValidFlg"] = $chkFlg == "off" ? 0 : 1;

                        $mdlPbc->saveUpdate2($data, $payingBackSeq);

                        // 注文履歴へ登録
                        if ($chkFlg == "on") {
                            // チェックオンの場合、指示
                            $reasonCd = 83;
                        }
                        else {
                            // チェックオフの場合、指示取消
                            $reasonCd = 84;
                        }
                        $history->InsOrderHistory($row["OrderSeq"], $reasonCd, $userId);
                    }
                }
            }

            // コミット
            $db->getDriver()->getConnection()->commit();

            $message = sprintf('更新されました。　%s', date("Y-m-d H:i:s"));
            $_SESSION[self::SESS_UPDATE_INFO] = $message;

        } catch (\Exception $ex) {
            // ロールバック
            $db->getDriver()->getConnection()->rollBack();

            $message = "更新に失敗しました。（" . $ex->getMessage() . "）";
        }

        $result["formchk"] = $formchk;
        $result["message"] = $message;

        return $result;
    }
}
