<?php
namespace cbadmin\Controller;

use Coral\Base\BaseHtmlUtils;
use Coral\Base\BaseGeneralUtils;
use Coral\Coral\Controller\CoralControllerAction;
use Coral\Coral\CoralCodeMaster;
use Coral\Coral\CoralValidate;
use Coral\Coral\Mail\CoralMail;
use Coral\Coral\Mail\CoralMailException;
use cbadmin\Application;
use models\Table\TableOem;
use models\Table\TablePayingControl;
use models\Table\TableEnterprise;
use models\Table\TableOrder;
use models\View\ViewChargeConfirm;
use models\View\ViewChargeFix;
use models\View\ViewChargeCancel;
use models\View\ViewChargeStampFee;
use models\Logic\LogicChargeDecision;
use models\Table\TableSystemProperty;
use models\Table\TablePayingAndSales;
use models\Table\TableCancel;
use models\Table\TableStampFee;
use models\Table\TablePayingBackControl;
use models\Table\TableAdjustmentAmount;
use models\Table\TableUser;
use DOMPDFModule\View\Model\PdfModel;
use models\Table\ATableAdjustmentAmount;
use models\Table\ATablePayingControl;
use models\Table\TableBatchLock;

class PayingController extends CoralControllerAction
{
    protected $_componentRoot = './application/views/components';

    /**
     * アプリケーションオブジェクト
     * @var Application
     */
    private $app;

    /**
     * バッチID
     * @var int
     */
    const EXECUTE_BATCH_ID = 3;
    /**
     * Controllerを初期化する
     */
    public function _init()
    {
        $this->app = Application::getInstance();

        $this->view->assign('userInfo', $this->app->authManagerAdmin->getUserInfo());

        $this->addStyleSheet('../css/default02.css');
        $this->addStyleSheet('../css/cbadmin/paying/list/default.css');
        $this->addJavaScript('../js/prototype.js');
        $this->addJavaScript('../js/corelib.js');
        $this->addJavaScript( '../js/base.ui.js');

        $this->setPageTitle("後払い.com - 立替確認");
	}

    /**
     * 立替確認リストを表示する。
     */
    public function listAction()
    {
        $mdlvc = new ViewChargeConfirm($this->app->dbAdapter);
        // 締め日パターンマスターを取得
        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
        $caption = $codeMaster->getFixPatternMaster(/* $isDummyOn =  */false);

        $oem = null;

        //OEMID取得
        $params = $this->getParams();
        $oemId = (isset($params['oemid'])) ? $params['oemid'] : -1;

        if(intval($oemId) != -1){
            $oem = $oemId;
        }
        $numSimePtn = 0;// 有効締め日パターン数
        $datas = $mdlvc->getConfirmList2(0,"","",false,$numSimePtn,$oem, -1,false, 0);

        $moem = new TableOem($this->app->dbAdapter);

        $paying_data = array();

        //OEMIDをキーとする
        foreach($datas as $value){
            //$paying_data['OemId]がセットされていなければOEMの情報も取得する
            if(!isset($paying_data[$value['OemId']]) && $value['OemId'] != 0){

                //OEMの情報取得
                $oem_data = $moem->findOem($value['OemId'])->current();

                //OEM情報がなければ飛ばす
                if (!$oem_data) {
                    continue;
                }

                //立替名取得
                if($oem_data['PayingMethod'] == 0){
                    $paying_method_name ="CB立替";
                }else{
                    $paying_method_name ="OEM立替";
                }

                $oem[$oem_data['OemId']] = array("OemNameKj"=>$oem_data['OemNameKj'],
                        "PayingMethod"=>$oem_data['PayingMethod'],
                        "PayingMethodName"=>$paying_method_name);

            }

            $paying_data[$value['OemId']][] = $value;

        }

        ksort($paying_data);

        // count関数対策
        $count = 0;
        if(!empty($caption)) {
            $count = count($caption);
        }

        $this->view->assign('genzai', BaseGeneralUtils::getDateString(date('Y-m-d')));
        $this->view->assign('list', $paying_data);
        $this->view->assign('paying_data', $paying_data);
        $this->view->assign('oem_data', $oem);
        $this->view->assign('count', $count);

        // 次回立替締日が正しく設定されていない加盟店の調査
        $sql  = " SELECT pc.EnterpriseId, count(*) ";
        $sql .= " FROM T_PayingControl pc, ";
        $sql .= "      T_Enterprise e ";
        $sql .= " WHERE pc.EnterpriseId = e.EnterpriseId ";
        $sql .= " AND pc.FixedDate = e.N_ChargeFixedDate ";
        $sql .= " AND pc.PayingControlStatus = 1 ";
        $sql .= " GROUP BY pc.EnterpriseId ";
        $sql .= " HAVING count(*) > 1 ";

        $ri = $this->app->dbAdapter->query($sql)->execute(null);
        if ($ri->count() > 0) {
            $this->view->assign('errSetChargeFixedDate', true);
        }

        // 一括伝票修正にて同梱請求手数料を、別送請求手数料に更新してしまった注文がキャンセルされた場合、アラートを表示する
        $sql  = " SELECT e.LoginId ";
        $sql .= " ,      e.EnterpriseNameKj ";
        $sql .= " ,      o.OrderId ";
        $sql .= " ,      pas.ClaimFee ";
        $sql .= " ,      FLOOR(sit.ClaimFeeDK * 1.08) AS ClaimFeeDK ";
        $sql .= " ,      DATE(cncl.ApprovalDate) AS ApprovalDate ";
        $sql .= " ,      o.OrderSeq ";
        $sql .= " FROM   W_IL323 w ";
        $sql .= "        INNER JOIN T_Cancel cncl ON (cncl.OrderSeq = w.OrderSeq) ";
        $sql .= "        INNER JOIN T_Order o ON (o.OrderSeq = w.OrderSeq) ";
        $sql .= "        INNER JOIN T_Enterprise e ON (e.EnterpriseId = o.EnterpriseId) ";
        $sql .= "        INNER JOIN T_Site sit ON (sit.SiteId = o.SiteId) ";
        $sql .= "        INNER JOIN T_PayingAndSales pas ON (pas.OrderSeq = w.OrderSeq) ";
        $sql .= " WHERE  cncl.CancelPhase IN (2, 3) ";
        $sql .= " AND    cncl.PayingControlStatus = 0 ";
        $sql .= " AND    cncl.ApprovalDate IS NOT NULL ";
        $sql .= " ORDER BY LoginId ";

        $ri = $this->app->dbAdapter->query($sql)->execute(null);
        if ($ri->count() > 0) {
            $this->view->assign('errClaimFeeDK', ResultInterfaceToArray($ri));
        }

        return $this->view;
	}

// Del By Takemasa(NDC) 20150309 Stt 未使用故コメントアウト化
// 	/**
// 	 * 立替予測を表示する。
// 	 *
// 	 */
// 	public function forecastAction()
// 	{
// 		$mdlvc = new View_ChargeConfirm($this->app->dbAdapter);
// 		$datas = $mdlvc->getForecastList();
//
// 		$this->view->assign('genzai', NetB_GeneralUtils::getDateString(new Zend_Date()));
// 		$this->view->assign('list', $datas);
// 	}
// Del By Takemasa(NDC) 20150309 End 未使用故コメントアウト化

// 2015/07/16 Del Y.Suzuki 新システムでは廃止 Stt
//     /**
//      * 立替予測を表示する。 その２
//      *
//      */
//     public function forecast2Action()

//     /**
//      * 立替予測の明細を表示する。 その２
//      *
//      */
//     public function forecast2detailAction()

//     /**
//      * 立替予測データダウンロード
//      */
//     public function forecastdatadlAction()
// 2015/07/16 Del Y.Suzuki 新システムでは廃止 End

	/**
	 * 立替済みリストを表示する。
	 */
	public function elistAction()
	{
        $params = $this->getParams();

        $from = isset($params['f']) ? $params['f'] : '';
        $to   = isset($params['t']) ? $params['t'] : '';

        if ($from == "" && $to == "") {
            $from = date('Y-m-d', strtotime('-1 Month'));
        }

        $oem_id = null;
        //OEMID取得
        $oem_id_param = isset($params['OemId']) ? $params['OemId'] : -1;

        //OEMIDが存在
        if(intval($oem_id_param) != -1){
            $oem_id = $oem_id_param;
        }

        //都度請求のみ表示
        $isOnlyTudoSeikyu = isset($params['isOnlyTudoSeikyu']) ? 1 : 0;

        // EnterpriseclaimControllerからの画面遷移
        $eid = -1;
        if (isset($params['eid'])) {
            $from = '';             // 期間(from)は指定無し
            $to   = date('Y-m-d');  // 期間(to)はシステム日付
            $isOnlyTudoSeikyu = 1;  // 都度請求のみ表示のチェックはオン
            $eid = $params['eid'];  // 通知された加盟店IDを指定
        }

        $numSimePtn = 0;// 有効締め日パターン数
        $mdlvc = new ViewChargeConfirm($this->app->dbAdapter);
        $datas = $mdlvc->getConfirmList2(1, $from, $to, $isOnlyTudoSeikyu, $numSimePtn, $oem_id, $eid, false, null);

        $moem = new TableOem($this->app->dbAdapter);

        $paying_data = array();

        //OEMIDをキーとする
        foreach($datas as $value){
            //$paying_data['OemId]がセットされていなければOEMの情報も取得する
            if(!isset($paying_data[$value['OemId']]) && $value['OemId'] != 0){

                //OEMの情報取得
                $oem_data = $moem->findOem($value['OemId'])->current();

                //OEM情報がなければ飛ばす
                if (!$oem_data) {
                    continue;
                }

                //立替名取得
                if($oem_data['PayingMethod'] == 0){
                    $paying_method_name ="CB立替";
                }else{
                    $paying_method_name ="OEM立替";
                }

                $oem[$oem_data['OemId']] = array("OemNameKj"=>$oem_data['OemNameKj'],
                        "PayingMethod"=>$oem_data['PayingMethod'],
                        "PayingMethodName"=>$paying_method_name);

            }

            $paying_data[$value['OemId']][] = $value;

        }

        $this->view->assign('genzai', BaseGeneralUtils::getDateString(date('Y-m-d')));
        $this->view->assign('list', $paying_data);
        $this->view->assign('oem_data', $oem);
        $this->view->assign('f', $from);
        $this->view->assign('t', $to);
        // 以下、追加項目
        $this->view->assign('numSimePtn', $numSimePtn);
        $this->view->assign('isOnlyTudoSeikyu', ($isOnlyTudoSeikyu) ? 'on' : '');

        return $this->view;
	}

	/**
	 * 立替詳細画面の表示　その２
	 * @see 合計額の表示を追加したもの
	 */
	public function dlist2Action()
	{
	    $payingMethod = 0;

        // 締め日パターンマスターを取得
        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
        $caption = $codeMaster->getFixPatternMaster(/* $isDummyOn =  */false);

        $params = $this->getParams();

        // 立替確定日
        $decisionDateStr = isset($params['d']) ? $params['d'] : date('Y-m-d');

        // 立替実行（予定）日
        $execDateStr = isset($params['e']) ? $params['e'] : date('Y-m-d');

        //OEMID取得
        $oemId = isset($params['oemid']) ? $params['oemid'] : -1;

        // 都度請求のみフラグ
        $tudoFlg = isset($params['tudo']) ? $params['tudo'] : 0;

        // 未立替のみフラグ
        $execFlg = isset($params['execFlg']) ? $params['execFlg'] : 0;

        //OEMIDがキャッチボールの場合キャッチボール設定
        if($oemId == 0){
            $oem = array("OemName"=>"キャッチボール加盟店","PayingMethodName"=>"");
        }
        else {
            $moem = new TableOem($this->app->dbAdapter);

            //OEM情報取得
            $oem_data = $moem->findOem($oemId)->current();
            $payingMethod = $oem_data['PayingMethod'];

            if (!$oem_data) {
                $oem = array("OemName"=>"","PayingMethodName"=>"");
            }
            else {
                //立替方法
                if ($oem_data['PayingMethod'] == 0) {
                    $paying_method_name = "CB立替";
                }else{
                    $paying_method_name = "OEM立替";
                }
                $oem = array("OemName"=>$oem_data['OemNameKj'],"PayingMethodName"=>"/".$paying_method_name);
            }
        }
        $mdlcc = new ViewChargeConfirm($this->app->dbAdapter);

        // 指定確定日、指定締めパターンの立替詳細データを取得
        $i = 0;
        foreach ($caption as $key => $value) {
            $list[$i] = ResultInterfaceToArray($mdlcc->getConfirmDetailList($decisionDateStr, $execDateStr, $key, $oemId, $tudoFlg, $execFlg));

            // count関数対策
            $countListLen = 0;
            if (!empty($list[$i])) {
                $countListLen = count($list[$i]);
            }

            for ($j=0; $j<$countListLen; $j++) {
                $list[$i][$j]['LoginId'] = $this->app->dbAdapter->query(" SELECT LoginId FROM T_Enterprise WHERE EnterpriseId = :EnterpriseId "
                    )->execute(array(':EnterpriseId' => $list[$i][$j]['EnterpriseId']))->current()['LoginId'];
            }

            $i++;
        }
        // 合計の算出
        $total['CarryOver'] = 0;
        $total['DecisionPayment'] = 0;
        $total['SettlementFee'] = 0;
        $total['Uriage'] = 0;
        $total['ClaimFee'] = 0;
        $total['ChargeCount'] = 0;
        $total['CalcelAmount'] = 0;
        $total['UseAmount'] = 0;
        $total['StampFeeTotal'] = 0;
        $total['TransferCommission'] = 0;
        $total['MonthlyFee'] = 0;
        $total['AdjustmentAmount'] = 0;
        $total['PayBackAmount'] = 0;

        // count関数対策
        $captionLen = 0;
        if (!empty($caption)) {
            $captionLen = count($caption);
        }

        for ($listi = 0 ; $listi < $captionLen ; $listi++)
        {
            // count関数対策
            $listLen = 0;
            if (!empty($list[$listi])) {
                $listLen = count($list[$listi]);
            }

            for ($i = 0 ; $i < $listLen; $i++)
            {
                if ($payingMethod == 1 && $list[$listi][$i]['DecisionPayment'] <= 0) {
                    continue;
                }
                $total['CarryOver'] += $list[$listi][$i]['CarryOver'];
                $total['DecisionPayment'] += $list[$listi][$i]['DecisionPayment'];
                $total['SettlementFee'] += $list[$listi][$i]['SettlementFee'];
                $total['Uriage'] += $list[$listi][$i]['Uriage'];
                $total['ClaimFee'] += $list[$listi][$i]['ClaimFee'];
                $total['ChargeCount'] += $list[$listi][$i]['ChargeCount'];
                $total['CalcelAmount'] += $list[$listi][$i]['CalcelAmount'];
                $total['UseAmount'] += $list[$listi][$i]['UseAmount'];
                $total['StampFeeTotal'] += $list[$listi][$i]['StampFeeTotal'];
                $total['TransferCommission'] += $list[$listi][$i]['TransferCommission'];
                $total['MonthlyFee'] += $list[$listi][$i]['MonthlyFee'];
                $total['AdjustmentAmount'] += $list[$listi][$i]['AdjustmentAmount'];
                $total['PayBackAmount'] += $list[$listi][$i]['PayBackAmount'];
            }
        }

        $this->view->assign('codeMaster', new CoralCodeMaster($this->app->dbAdapter));
        $this->view->assign('total', $total);
        $this->view->assign('list', $list);
        $this->view->assign('caption', $caption);
        $captionIndex = array();
        $i = 0;
        foreach ($caption as $key => $value) {
            $captionIndex[$i] = $key;
            $i++;
        }
        $this->view->assign('captionIndex', $captionIndex);
        $this->view->assign('oem',$oem);
        $this->view->assign('decisiondatestr',$decisionDateStr);
        $this->view->assign('execdatestr',$execDateStr);
        $this->view->assign('oemid',$oemId);

        // 調整金科目
        $ri = $this->app->dbAdapter->query(" SELECT KeyCode, KeyContent FROM M_Code WHERE Validflg = 1 AND CodeId = 89 ORDER BY KeyCode ")->execute(null);
        $kamokuList = array();
        foreach ($ri as $row) {
            $kamokuList[$row['KeyCode']] = $row['KeyContent'];
        }
        $this->view->assign('kamokuListTag',BaseHtmlUtils::SelectTag("kamokuList", $kamokuList, 1));


        return $this->view;
	}

    /**
     * 総合振り込みデータダウンロード
     */
    public function transdatadlAction()
    {
        $mdlsp = new TableSystemProperty($this->app->dbAdapter);
        $category = 'systeminfo';
        $name = 'TempFileDir';
        $transCsvDir = $mdlsp->getValue(TableSystemProperty::DEFAULT_MODULE, $category, $name);

        // パラメータ取得
        $params = $this->getParams();

        // 立替確定日
        $decisionDate = $params['d'];
        // 立替予定日
        $execScheduleDate = $params['e'];
        // OEMID
        $oemId = isset($params['OemId']) ? $params['OemId'] : 0;

        // 立替振込管理テーブルを更新
        $mdlpc = new TablePayingControl($this->app->dbAdapter);

        // ユーザーIDの取得
        $obj = new TableUser( $this->app->dbAdapter );
        $userId = $obj->getUserId( 0, $this->app->authManagerAdmin->getUserInfo()->OpId );

        // 立替確定日と立替予定日が一致し、ファイルパスがNULLでないデータを抜き出す。
        $sql = <<<EOQ
SELECT  GROUP_CONCAT(pc.Seq) as SeqList
       ,MAX(pc.DecisionDate) as DecisionDate
       ,MAX(pc.ExecScheduleDate) as ExecScheduleDate
       ,MAX(IFNULL(pc.OemId, 0)) as OemId
FROM    T_PayingControl pc
        INNER JOIN T_Enterprise e ON (pc.EnterpriseId = e.EnterpriseId)
WHERE   pc.DecisionDate = :DecisionDate
AND     pc.ExecScheduleDate = :ExecScheduleDate
AND     IFNULL(pc.OemId, 0) = :OemId
AND     pc.SpecialPayingFlg = 0
AND     pc.DecisionPayment > 0
AND     e.ExecStopFlg = 0
AND     pc.ExecDate IS NULL
EOQ;
        // 立替確定日、立替予定日、OEMIDが一致する場合、ファイルパスがNULLでないデータは1件のみのはず・・・
        $value = $this->app->dbAdapter->query($sql)->execute(array( ':DecisionDate' => $decisionDate, ':ExecScheduleDate' => $execScheduleDate,':OemId' => $oemId ))->current();
        $seqList = $value['SeqList'];
        if (is_null($seqList)) {
            $transCsvFileName = sprintf("TransferData_%s.csv", date("YmdHis"));
            $transCsvFullFileName = $transCsvDir . '/' . $transCsvFileName;
            if ( file_exists($transCsvFullFileName)) {
                unlink($transCsvFullFileName);
            }
            file_put_contents($transCsvFullFileName, "");
            header("Content-Type: application/octet-stream");
            header("Content-Disposition: attachment; filename=$transCsvFileName");
            readfile($transCsvFullFileName);
            if ( file_exists($transCsvFullFileName)) {
                unlink($transCsvFullFileName);
            }
            return $this->response;
        }

        // ----------------------------------------------------------------------------------------------------------->
        // OEMID、確定日、予定日ごとにCSV作成

        // OEM情報
        $mdlOem = new TableOem($this->app->dbAdapter);
        $oem = $mdlOem->findOem2($oemId)->current();

        // 条件指定  OEMID、確定日、予定日ごとの立替振込管理Seqのリスト
        // データの取得
        $sql = <<<EOQ
SELECT  pc.Seq
    ,   pc.FixedDate
    ,   pc.ExecScheduleDate
    ,   e.FfCode
    ,   e.FfName
    ,   e.FfBranchCode
    ,   e.FfBranchName
    ,   e.FfAccountClass
    ,   e.FfAccountNumber
    ,   e.FfAccountName
    ,   pc.DecisionPayment
FROM    T_Enterprise e
        INNER JOIN T_PayingControl pc ON (pc.EnterpriseId = e.EnterpriseId)
WHERE   pc.Seq IN ($seqList)
ORDER BY
        pc.Seq
EOQ;
        $ri = $this->app->dbAdapter->query($sql)->execute(null);
        $csvData = ResultInterfaceToArray($ri);

        // 合計の算出
        $totalCnt = 0;
        $totalDecisionPayment = 0;

        if (! empty($csvData)) {
            // ファイル名
            $transCsvFileName = sprintf("TransferData_%s_%s.csv", date("YmdHis"), $csvData[0]['FixedDate']);

            // ファイルフルパス
            $transCsvFullFileName = $transCsvDir . '/' . $transCsvFileName;
            // すでにファイルが作成されていたら削除
            if ( file_exists($transCsvFullFileName)) {
                unlink($transCsvFullFileName);
            }

            // -----------------------------------------------
            // ヘッダーレコード
            // -----------------------------------------------
            // CB or CB立替のOEM
            $headerRecord = sprintf(
                "1,21,0,1006008699,ｶ)ｷｬｯﾁﾎﾞｰﾙ,%02d%02d,0149,,361,,2,0331751,\r\n",
                date('m', strtotime($csvData[0]['ExecScheduleDate'])),
                date('d', strtotime($csvData[0]['ExecScheduleDate']))
            );
            $headerRecord = mb_convert_encoding($headerRecord, "SJIS", "UTF-8");

            // -----------------------------------------------
            // データレコード
            // -----------------------------------------------
            $dataRecords = "";

            for ($i = 0 ; $i < count($csvData); $i++) {

                $totalCnt++;
                $totalDecisionPayment += $csvData[$i]['DecisionPayment'];

                // CSV(直営のパターン)
                $dataRecord = sprintf(
                    "2,%d,%s,%d,%s,,%d,%d,%s,%d,0,,, , , \r\n",
                    $csvData[$i]['FfCode'],                                                                                     // 銀行コード
                    BaseGeneralUtils::convertWideToNarrowEx(BaseGeneralUtils::deleteMultiByte($csvData[$i]['FfName'])),         // 銀行名
                    $csvData[$i]['FfBranchCode'],                                                                               // 支店コード
                    BaseGeneralUtils::convertWideToNarrowEx(BaseGeneralUtils::deleteMultiByte($csvData[$i]['FfBranchName'])),   // 支店名
                    $csvData[$i]['FfAccountClass'],                                                                             // 科目
                    $csvData[$i]['FfAccountNumber'],                                                                            // 口座番号
                    BaseGeneralUtils::convertWideToNarrowEx(BaseGeneralUtils::deleteMultiByte($csvData[$i]['FfAccountName'])),  // 受取人
                    $csvData[$i]['DecisionPayment']                                                                             // 金額
                );
                $dataRecords .= mb_convert_encoding($dataRecord, "SJIS", "UTF-8");
            }

            // -----------------------------------------------
            // トレーラレコード
            // -----------------------------------------------
            // CSV
            $trailerRecord = sprintf(
                "8,%d,%d,\r\n",
                $totalCnt,
                $totalDecisionPayment
            );
            $trailerRecord = mb_convert_encoding($trailerRecord, "SJIS", "UTF-8");

            // -----------------------------------------------
            // エンドレコード
            // -----------------------------------------------
            $endRecord = "9,\r\n";
            $endRecord = mb_convert_encoding($endRecord, "SJIS", "UTF-8");

            // 作成したデータを結合
            $contents = $headerRecord . $dataRecords . $trailerRecord . $endRecord;
            // ファイルに保存
            file_put_contents($transCsvFullFileName, $contents);

        }
        // <-----------------------------------------------------------------------------------------------------------

        // 立替確定日と立替予定日が一致するデータを抜き出す。
        $sql = <<<EOQ
SELECT  *
FROM    T_PayingControl
WHERE   DecisionDate = :DecisionDate
AND     ExecScheduleDate = :ExecScheduleDate
AND     IFNULL(OemId, 0) = :OemId
EOQ;

        $datas = $this->app->dbAdapter->query($sql)->execute(array( ':DecisionDate' => $decisionDate, ':ExecScheduleDate' => $execScheduleDate,':OemId' => $oemId));

        // 取得できた分処理する
        foreach ($datas as $value) {
            $mdle = new TableEnterprise($this->app->dbAdapter);
            $rowEnt = $mdle->find($value['EnterpriseId'])->current();
            if ($rowEnt['ExecStopFlg'] == 1) {
                continue;
            }

            // 更新処理
            $pcSeq = $value['Seq'];
            $data = array(
                    'PayingDataDownloadFlg' => 1,           // 振込データDLフラグ
                    'UpdateId' => $userId,                  // 更新者
            );

            // 更新処理
            $mdlpc->saveUpdate($data, $pcSeq);
        }

        // レスポンスヘッダの出力
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=$transCsvFileName");

        // データ出力
        readfile($transCsvFullFileName);

        // ごみ掃除
        if ( file_exists($transCsvFullFileName)) {
            unlink($transCsvFullFileName);
        }

        return $this->response;
    }

    /**
     * 都度請求データダウンロード
     */
    public function eachtimebillingdlAction()
    {

        $mdlsp = new TableSystemProperty($this->app->dbAdapter);
        $category = 'systeminfo';
        $name = 'TempFileDir';
        $transCsvDir = $mdlsp->getValue(TableSystemProperty::DEFAULT_MODULE, $category, $name);

        // ------------------------------
        // ZIPファイル作成準備
        // ------------------------------
        // 複数ファイル存在するため、ZIPファイルに圧縮してダウンロードする。
        // ZIPファイル作成
        $zip = new \ZipArchive();

        // 出力ファイル名
        $outFileName = sprintf('%s_%s.zip', date('Ymd'), date('His'));
        // TEMP領域作成
        $tempFilePath = tempnam(sys_get_temp_dir(), 'tmp');
        // ZIPファイルオープン
        $zip->open( $tempFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE );

        // ------------------------------
        // パラメーター取得
        // ------------------------------
        // パラメータ取得
        $params = $this->getParams();

        // 立替確定日
        $decisionDate = $params['d'];
        // 立替予定日
        $execScheduleDate = $params['e'];
        // OEMID
        $oemId = isset($params['OemId']) ? $params['OemId'] : 0;

        // ------------------------------
        // データ検索
        // ------------------------------
        $fileList = array();
        $date = date( "YmdHis" );

        // -------------------------------------------------------------------------------------------------------------
        $sql = <<<EOQ
SELECT GROUP_CONCAT(pc.Seq) AS SeqList
      ,MIN(pc.EnterpriseId) AS EnterpriseId
FROM T_PayingControl pc
WHERE pc.DecisionDate = :DecisionDate
AND pc.ExecScheduleDate = :ExecScheduleDate
AND IFNULL(pc.OemId, 0) = :OemId
AND EXISTS ( SELECT * FROM T_EnterpriseClaimHistory ech WHERE pc.Seq = ech.PayingControlSeq )
EOQ;

        $prm = array(
                ':DecisionDate' => $decisionDate,
                ':ExecScheduleDate' => $execScheduleDate,
                ':OemId' => $oemId,
        );

        $datas = $this->app->dbAdapter->query($sql)->execute($prm);
        $counter = 0;
        foreach($datas as $value){
            if ($counter == 0 && is_null($value['SeqList'])) {
                $claimPdfFileName = sprintf( 'Tsudoseikyu_%s.csv', $date );
                $claimPdfFullFileName = $transCsvDir . '/' . $claimPdfFileName;
                if ( file_exists($claimPdfFullFileName)) {
                    unlink($claimPdfFullFileName);
                }
                file_put_contents($claimPdfFullFileName, "");
                $zip->addFile($claimPdfFullFileName, $claimPdfFileName);
                $fileList[] = $claimPdfFullFileName;

                $zip->close();
                header( 'Content-Type: application/octet-stream; name="' . $outFileName . '"' );
                header( 'Content-Disposition: attachment; filename="' . $outFileName . '"' );
                header( 'Content-Length: ' . filesize( $tempFilePath ) );
                readfile( $tempFilePath );
                unlink( $tempFilePath );
                foreach ($fileList as $file ) {
                    if (file_exists($file)) {
                        unlink($file);
                    }
                }
                return $this->response;
            }
            $counter++;

            // OEMID、確定日、予定日ごとにPDF作成

            // 条件指定  OEMID、確定日、予定日ごとの立替振込管理Seqのリスト
            $seqListHolder = $value["SeqList"];

$sql = <<<Q_END
        SELECT pc.ExecDate
        ,      e.EnterpriseId
        ,      e.EnterpriseNameKj
        ,      e.PostalCode
        ,      CONCAT( IFNULL( e.PrefectureName, '' ), IFNULL( e.City, '' ), IFNULL( e.Town, '' ), IFNULL( e.Building, '' ) ) AS Address
        ,      pc.FixedDate
        ,      pc.ExecScheduleDate
        ,      pc.ChargeCount
        ,      (
                 ( pc.ChargeAmount ) +
                 ( pc.SettlementFee ) +
                 ( pc.ClaimFee )
               ) AS ChargeAmount
        ,      ( -1 * pc.SettlementFee ) AS SettlementFee
        ,      ( -1 * pc.ClaimFee ) AS ClaimFee
        ,      ( -1 * pc.StampFeeTotal ) AS StampFeeTotal
        ,      ( -1 * pc.MonthlyFee ) AS MonthlyFee
        ,      pc.CarryOver
        ,      pc.CalcelAmount
        ,      ( -1 * pc.TransferCommission ) AS TransferCommission
        ,      pc.PayBackAmount
        ,      pc.AdjustmentAmount
        ,      ( -1 * (
                ( -1 * pc.SettlementFee ) +
                ( -1 * pc.ClaimFee ) +
                ( -1 * pc.StampFeeTotal ) +
                ( -1 * pc.MonthlyFee ) +
                ( pc.CarryOver ) +
                ( pc.CalcelAmount ) +
                ( -1 * pc.TransferCommission ) +
                ( pc.PayBackAmount ) +
                ( pc.AdjustmentAmount )
               ) ) AS ClaimAmount
        ,      (
                ( ( pc.ChargeAmount ) +
                  ( pc.SettlementFee ) +
                  ( pc.ClaimFee )
                ) -
                ( -1 * (
                 ( -1 * pc.SettlementFee ) +
                 ( -1 * pc.ClaimFee ) +
                 ( -1 * pc.StampFeeTotal ) +
                 ( -1 * pc.MonthlyFee ) +
                 ( pc.CarryOver ) +
                 ( pc.CalcelAmount ) +
                 ( -1 * pc.TransferCommission ) +
                 ( pc.PayBackAmount )+
                 ( pc.AdjustmentAmount )
               ) ) ) AS TotalAmount
        ,      pc.Seq
        FROM   T_PayingControl pc
               INNER JOIN T_Enterprise e ON pc.EnterpriseId = e.EnterpriseId
               LEFT OUTER JOIN T_Oem oem ON IFNULL(pc.OemId, 0) = oem.OemId
        WHERE  pc.Seq IN ($seqListHolder)
        ORDER BY pc.Seq ASC
Q_END;
            $data = ResultInterfaceToArray($this->app->dbAdapter->query($sql)->execute(null));

                    // 自社印刷分取得
$sql = <<<Q_END
        SELECT pc.Seq
        ,      COUNT( * ) AS HasMine
        ,      -1 * SUM( pas.ClaimFee ) AS ClaimFee
        FROM   T_PayingAndSales pas INNER JOIN
               T_PayingControl pc ON pas.PayingControlSeq = pc.Seq INNER JOIN
               T_Order o ON pas.OrderSeq = o.OrderSeq INNER JOIN
               T_ClaimControl cc ON o.OrderSeq = cc.OrderSeq
        WHERE  pc.Seq IN ($seqListHolder) AND
               o.ClaimSendingClass = 11
        GROUP BY pc.Seq
Q_END;
            $mineData = ResultInterfaceToArray($this->app->dbAdapter->query($sql)->execute(null));

            $mine = array();
            foreach($mineData as $row) {
                $mine[$row['Seq']]['HasMine'] = $row['HasMine'];
                $mine[$row['Seq']]['ClaimFee'] = $row['ClaimFee'];
            }

            // PDF作成対象がある場合のみ
            // count関数対策
            if (!empty($data)) {
                // ファイル名
                $claimPdfFileName = sprintf( 'Tsudoseikyu_%s_%s.pdf', $date, $value['EnterpriseId'] );

                // ファイルフルパス
                $claimPdfFullFileName = $transCsvDir . '/' . $claimPdfFileName;

                // すでにファイルが作成されていたら削除
                if ( file_exists($claimPdfFullFileName)) {
                    unlink($claimPdfFullFileName);
                }

                // ファイルパスリストに追加  出力対象の中で立替振込管理Seqの代表をキーにする
                $maxSeq = 0;
                foreach ($data as $row) {
                    if ($maxSeq < $row['Seq']) {
                        $maxSeq = $row['Seq'];
                    }
                }
                $claimPdfList[$maxSeq] = $claimPdfFullFileName;

                // PDFデータ
                $this->view->assign( 'datas', $data );
                $this->view->assign( 'mines', $mine );
                $this->view->assign( 'decisionDate', $decisionDate );
                $this->view->assign( 'documentRoot', $_SERVER['DOCUMENT_ROOT'] );
                $this->view->assign( 'title', $claimPdfFileName );
                // HTML作成
                $viewRender = $this->getServiceLocator()->get('ViewRenderer');
                $html = $viewRender->render($this->view);

                // 一時ファイルの保存先
                $mdlsp = new \models\Table\TableSystemProperty($this->app->dbAdapter);
                $tempDir = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'TempFileDir');
                $tempDir = realpath($tempDir);

                // 中間ファイル名
                $fname_html = ($tempDir . '/__tmp_' . $claimPdfFileName . '__.html');
                $fname_pdf  = ($tempDir . '/__tmp_' . $claimPdfFileName . '__.pdf');

                // HTML出力
                file_put_contents($fname_html, $html);

                // PDF変換(外部プログラム起動)
                $ename = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'wkhtmltopdf');
                $option = " --page-size A4 --orientation landscape --margin-left 0 --margin-right 0 --margin-top 0 --margin-bottom 0 ";
                exec($ename . $option . $fname_html . ' ' . $fname_pdf);

                // ファイルの読み込み
                $pdf = file_get_contents($fname_pdf);

                // ファイルに保存
                file_put_contents($claimPdfFullFileName, $pdf);

                //$pdfView = new PdfModel();
                /*$pdfView->setTemplate('cbadmin/paying/eachtimebillingdl.phtml');
                $pdfView->setOption( 'filename', $claimPdfFileName );
                $pdfView->setOption( 'paperSize', 'a4' );
                $pdfView->setOption( 'paperOrientation', 'landscape' );   // 横向き
                $pdfView->setOption( 'basePath', $this->getBaseUrl() );

                $pdfView->setVariable( 'datas', $data );
                $pdfView->setVariable( 'mines', $mine );
                $pdfView->setVariable( 'decisionDate', $decisionDate );
                $pdfView->setVariable( 'documentRoot', $_SERVER['DOCUMENT_ROOT'] );
                $pdfView->setVariable( 'title', $claimPdfFileName );

                // HTML作成
                $renderer = $this->getServiceLocator()->get('ViewPdfRenderer');
                $html = $renderer->getHtmlRenderer()->render($pdfView);

                // HTML→PDF
                $dompdf = $this->getServiceLocator()->get('dompdf');
                $dompdf->load_html($html);
                $dompdf->set_paper('a4', 'landscape');  // ファイル保存時は大きさ、向きはここでも指定する
                $dompdf->render();
                $pdfCode = $dompdf->output();

                // ファイルに保存
                file_put_contents($claimPdfFullFileName, $pdfCode);*/

                // ZIPファイルに追加
                $zip->addFile($claimPdfFullFileName, $claimPdfFileName);

                $fileList[] = $claimPdfFullFileName;
            }
        }
        // -------------------------------------------------------------------------------------------------------------


        // ZIPファイルクローズ
        $zip->close();

        // ヘッダ
        header( 'Content-Type: application/octet-stream; name="' . $outFileName . '"' );
        header( 'Content-Disposition: attachment; filename="' . $outFileName . '"' );
        header( 'Content-Length: ' . filesize( $tempFilePath ) );

        // 出力
        readfile( $tempFilePath );

        // TEMP領域削除
        unlink( $tempFilePath );

        // PDFファイル削除
        foreach ($fileList as $file ) {
            if (file_exists($file)) {
                unlink($file);
            }
        }

        return $this->response;
    }

    /**
     * 立替確定処理アクション
     */
    public function fixAction()
    {
        ini_set('max_execution_time', 0);   // 実行タイムアウトを無効にしておく

        $params = $this->getParams();

        // 3) 更新処理
        $errorFlg = false;
        $errorMsg = "";

        $mdlbl = new TableBatchLock ( $this->app->dbAdapter );
        $BatchLock = $mdlbl->getLock( $this::EXECUTE_BATCH_ID );
        if ($BatchLock == 0) {
            $app = Application::getInstance();
            $logger = $app->logger;
            $logger->alert("Can't execute by Locking.\r\n");
            $this->view->assign('errorMsg', "現在、立替確定処理が既に実行されております。");
            $this->setTemplate('fixerror');
            return $this->view;
        }

//         // トランザクション開始
         $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
        try {
            // パラメータ取得
            $params = $this->getParams();
            // OEMID取得
            $oemId = (isset($params['oemid'])) ? $params['oemid'] : -1;

            // ユーザーIDの取得
            $obj = new TableUser( $this->app->dbAdapter );
            $userId = $obj->getUserId( 0, $this->app->authManagerAdmin->getUserInfo()->OpId );

            // 立替確定処理
            $lgcCharge = new LogicChargeDecision($this->app->dbAdapter);
            $lgcCharge->decision($oemId, null, null, $userId);

//             // 立替確定中にキャンセル申請が行われると、立替前キャンセルのままになってしまう為、一旦コミット⇒トランザクション再開
            $this->app->dbAdapter->getDriver()->getConnection()->commit();
            $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();

            //
            // 立替確定時のキャンセル返金仕様(20151221)
            //
            $mdl_cncl = new TableCancel($this->app->dbAdapter);

            // １．立替済み注文　かつ　キャンセルフェーズが立替前　の注文を取得
            $sql  = " SELECT o.OrderSeq ";
            $sql .= " ,      o.Chg_ChargeAmount ";
            $sql .= " ,      o.P_OrderSeq ";
            $sql .= " ,      o.UseAmount ";
            $sql .= " ,      c.Seq ";
            $sql .= " ,      c.CancelPhase ";
            $sql .= " ,      c.RepayChargeAmount ";
            $sql .= " ,      c.RepaySettlementFee ";
            $sql .= " ,      c.RepayClaimFee ";
            $sql .= " ,      c.RepayStampFee ";
            $sql .= " ,      c.RepayDamageInterest ";
            $sql .= " ,      c.RepayReClaimFee ";
            $sql .= " ,      c.RepayDifferentialAmount ";
            $sql .= " ,      c.RepayDepositAmount ";
            $sql .= " ,      c.RepayReceiptAmount ";
            $sql .= " ,      pas.SettlementFee ";
            $sql .= " ,      pas.ClaimFee ";
            $sql .= " ,      cc.DamageInterestAmount ";
            $sql .= " ,      cc.ClaimFee AS ClaimControlClaimFee ";
            $sql .= " ,      cc.AdditionalClaimFee ";
            $sql .= " ,      cc.ReceiptAmountTotal ";
            $sql .= " FROM   T_Order o ";
            $sql .= "        INNER JOIN T_PayingAndSales pas ON (o.OrderSeq = pas.OrderSeq) ";
            $sql .= "        INNER JOIN T_Cancel c ON (o.OrderSeq = c.OrderSeq AND c.ValidFlg = 1) ";
            $sql .= "        LEFT OUTER JOIN T_ClaimControl cc ON (cc.OrderSeq = o.OrderSeq) ";
            $sql .= " WHERE  1 = 1 ";
            $sql .= " AND    pas.PayingControlStatus = 1 ";
            $sql .= " AND    c.CancelPhase IN ( 1, 4 ) ";
            $sql .= " AND    c.PayingControlStatus = 0 ";

            $ri = $this->app->dbAdapter->query($sql)->execute(null);

            foreach ($ri as $row) {

                if ($row['CancelPhase'] == 1) {
                    // ２－１．キャンセルフェーズ＝１（未入金）の場合
                    $data = array(
                        'CancelPhase' => 2,
                        'RepayChargeAmount' => $row['Chg_ChargeAmount'],
                        'RepayTotal' => ($row['Chg_ChargeAmount'] +
                                         $row['RepaySettlementFee'] +
                                         $row['RepayClaimFee'] +
                                         $row['RepayStampFee'] +
                                         $row['RepayDamageInterest'] +
                                         $row['RepayReClaimFee'] +
                                         $row['RepayDifferentialAmount'] +
                                         $row['RepayDepositAmount'] +
                                         $row['RepayReceiptAmount']),
                        'UpdateDate' => date('Y-m-d H:i:s'),
                        'UpdateId' => $userId,
                        'ValidFlg' => 1,
                    );

                    $mdl_cncl->saveUpdate($data, $row['Seq']);
                }
                else if ($row['CancelPhase'] == 4) {
                    // ２－２．キャンセルフェーズ＝４（入金済）の場合

                    $isParent = ($row['OrderSeq'] == $row['P_OrderSeq']) ? true : false;

                    $repaySettlementFee = $row['SettlementFee'] * -1;
                    $repayClaimFee = $row['ClaimFee'] * -1;
                    $repayDamageInterest = ($isParent) ? ($row['DamageInterestAmount'] * -1) : 0;
                    $repayReClaimFee = ($isParent) ? (($row['ClaimControlClaimFee'] + $row['AdditionalClaimFee']) * -1) : 0;
                    $repayDifferentialAmount = ($isParent) ? ($row['UseAmount']+
                                                              $row['DamageInterestAmount']+
                                                              $row['ClaimControlClaimFee'] +
                                                              $row['AdditionalClaimFee'] -
                                                              $row['ReceiptAmountTotal']) : $row['UseAmount'];
                    $repayReceiptAmount = 0;
                    $repayTotal = $row['RepayChargeAmount'] +
                                  $repaySettlementFee +
                                  $repayClaimFee +
                                  $repayDamageInterest +
                                  $repayReClaimFee +
                                  $repayDifferentialAmount +
                                  $repayDepositAmount +
                                  $repayReceiptAmount;

                    $data = array(
                        'CancelPhase' => 3,
                        'RepaySettlementFee' => $repaySettlementFee,
                        'RepayClaimFee' => $repayClaimFee,
                        'RepayDamageInterest' => $repayDamageInterest,
                        'RepayReClaimFee' => $repayReClaimFee,
                        'RepayDifferentialAmount' => $repayDifferentialAmount,
                        'RepayReceiptAmount' => $repayReceiptAmount,
                        'RepayTotal' => $repayTotal,
                        'UpdateDate' => date('Y-m-d H:i:s'),
                        'UpdateId' => $userId,
                        'ValidFlg' => 1,
                    );

                    $mdl_cncl->saveUpdate($data, $row['Seq']);
                }
            }

            // コミット
            $this->app->dbAdapter->getDriver()->getConnection()->commit();

            // ロック解除
            $mdlbl->releaseLock( $this::EXECUTE_BATCH_ID );
        } catch (\Exception $e) {
            // ロールバック
            $this->app->dbAdapter->getDriver()->getConnection()->rollback();
            $errorFlg = true;
            $errorMsg = $e->getMessage();

            if (! empty ( $BatchLock )) {
                if ($BatchLock > 0) {
                    // ロック解除
                    $mdlbl->releaseLock( $this::EXECUTE_BATCH_ID );
                }
            }
        }

        if ($errorFlg) {
            $this->view->assign('errorMsg', $errorMsg);
            $this->setTemplate('fixerror');
            return $this->view;
        } else {
            return $this->_redirect('paying/list');
        }
    }

    /**
     * 支払完了アクション
     */
    public function execchargeAction()
    {
        $errorFlg = false;
        $errorMsg = "";

        $params = $this->getParams();

        $decisionDate = isset($params['DecisionDate']) ? $params['DecisionDate'] : '';
        $execScheduleDate = isset($params['ExecScheduleDate']) ? $params['ExecScheduleDate'] : '';
        $oemId = isset($params['OemId']) ? $params['OemId'] : 0;

        // 確定日が指定されていなければ処理しない。
        if ($decisionDate == '') {
            return $this->_redirect('paying/list');
        }

        // 立替予定日が指定されていなければ処理しない。
        if ($execScheduleDate == '') {
            return $this->_redirect('paying/list');
        }

        $dd = date('Y-m-d', strtotime($decisionDate));      // 立替確定日
        $de = date('Y-m-d', strtotime($execScheduleDate));  // 立替予定日

        $mdlpc = new TablePayingControl($this->app->dbAdapter);
        $mdlo = new TableOrder($this->app->dbAdapter);

        // ユーザーIDの取得
        $obj = new TableUser( $this->app->dbAdapter );
        $userId = $obj->getUserId( 0, $this->app->authManagerAdmin->getUserInfo()->OpId );

        $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
        try {
            // 立替振込管理データを立替実行済みにする
            $pcseqs = $mdlpc->execCharge($dd, $de, $oemId, $userId);

            // count関数対策
            $pcseqsLen = 0;
            if(!empty($pcseqs)) {
                $pcseqsLen = count($pcseqs);
            }

            for ($i = 0 ; $i < $pcseqsLen; $i++) {
                // 注文データを立替実行済みにする。
                $mdlo->execCharge($pcseqs[$i], date('Y-m-d'), $userId);
                // 立替完了メールを送信する
                // OEM導入に伴い立替完了メールは廃止
                // OEMでは無い場合は従前どおりメールを送信する 2014.9.1 kashira
                if ($oemId > 0) {
                   ;// OEMの場合はメール送信しない
                } else {
                    $decisionPayment = (int)$this->app->dbAdapter->query(" SELECT DecisionPayment FROM T_PayingControl WHERE Seq = :Seq "
                        )->execute(array(':Seq' => $pcseqs[$i]))->current()['DecisionPayment'];
                    if ($decisionPayment > 0) {
                    // OEMではない場合はメールを送信する
                    $mail = new CoralMail($this->app->dbAdapter, $this->app->mail['smtp']);
                    $mail->SendExecChargeMail($pcseqs[$i], $userId);
                    }
                }
            }

            // T_PayingControl更新
            $sql = <<<EOQ
UPDATE T_PayingControl
SET ExecDate           = :ExecDate
,   ExecCpId           = :ExecCpId
,   UpdateDate         = :UpdateDate
,   UpdateId           = :UpdateId
WHERE DecisionDate     = :DecisionDate
AND   ExecScheduleDate = :ExecScheduleDate
AND   IFNULL(OemId,0)  = :OemId
AND   ExecFlg          = -1
EOQ;
            $prmupd = array(  ':ExecDate' => date('Y-m-d')
                            , ':ExecCpId' => $userId
                            , ':UpdateDate' => date('Y-m-d H:i:s')
                            , ':UpdateId' => $userId
                            , ':DecisionDate' => $decisionDate
                            , ':ExecScheduleDate' => $execScheduleDate
                            , ':OemId' => $oemId
            );
            $this->app->dbAdapter->query($sql)->execute($prmupd);

            $this->app->dbAdapter->getDriver()->getConnection()->commit();
        } catch (CoralMailException $em) {
            $this->app->dbAdapter->getDriver()->getConnection()->rollBack();
            $errorFlg = true;
            $ex = $em->getInnerException();
            $msg = !is_null($ex) ? $ex->getMessage() : '';
            $errorMsg = $em->getMessage() . '; ' . $msg;
        } catch (\Exception $e) {
            $this->app->dbAdapter->getDriver()->getConnection()->rollBack();

            $errorFlg = true;
            $errorMsg = $e->getMessage();
        }

        if ($errorFlg) {
            $this->view->assign('errorMsg', $errorMsg);
            return $this->view;
        } else {
            return $this->_redirect('paying/list');
        }
    }

    /**
     * 調整額確定
     *
     */
    public function adjustAction()
    {
        // 締め日パターンマスターを取得
        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
        $caption = $codeMaster->getFixPatternMaster(/* $isDummyOn =  */false);

        $params = $this->getParams();

        // 立替確定日
        $decisionDateStr = isset($params['d']) ? $params['d'] : date('Y-m-d');

        // 立替実行（予定）日
        $execDateStr = isset($params['e']) ? $params['e'] : date('Y-m-d');

        // 加盟店ID取得
        $enterpriseId = isset($params['eid']) ? $params['eid'] : -1;

        // OEMID取得
        $oemId = isset($params['oemid']) ? $params['oemid'] : -1;

        // 立替振込管理Seq取得
        $pcSeq = isset($params['pcseq']) ? $params['pcseq'] : -1;

        $mdlcc = new ViewChargeConfirm($this->app->dbAdapter);

        // 指定確定日、指定締めパターンの立替詳細データを取得
        $i = 0;
        foreach ($caption as $key => $value) {
            $list[$i] = ResultInterfaceToArray($mdlcc->getConfirmDetailEnt($decisionDateStr, $execDateStr, $key, $enterpriseId));

            // count関数対策
            $listLen = 0;
            if(!empty($list[$i])) {
                $listLen = count($list[$i]);
            }

            for ($j=0; $j<$listLen; $j++) {
                $list[$i][$j]['LoginId'] = $this->app->dbAdapter->query(" SELECT LoginId FROM T_Enterprise WHERE EnterpriseId = :EnterpriseId "
                    )->execute(array(':EnterpriseId' => $list[$i][$j]['EnterpriseId']))->current()['LoginId'];
            }
            $i++;
        }

        $this->view->assign('codeMaster', new CoralCodeMaster($this->app->dbAdapter));
        $this->view->assign('list', $list);
        $this->view->assign('caption', $caption);
        $this->view->assign('eid', $enterpriseId);
        $this->view->assign('oemid', $oemId);

        $captionIndex = array();
        $i = 0;
        foreach ($caption as $key => $value) {
            $captionIndex[$i] = $key;
            $i++;
        }
        $this->view->assign('captionIndex', $captionIndex);

        // 調整額データ取得
        $sql = <<<EOQ
            SELECT  aa.PayingControlSeq
                ,   aa.SerialNumber
                ,   aa.OrderId
                ,   c.NameKj
                ,   aa.OrderSeq
                ,   aa.ItemCode
                ,   aa.AdjustmentAmount
                ,   aa.RegistDate
                ,   F_GetLoginUserName( aa.RegistId ) AS RegistName
            FROM    T_AdjustmentAmount aa
                    INNER JOIN T_PayingControl pc ON (pc.Seq = aa.PayingControlSeq)
                    LEFT OUTER JOIN T_Order o ON (o.OrderSeq = aa.OrderSeq)
                    LEFT OUTER JOIN T_Customer c ON (c.OrderSeq = o.OrderSeq)
            WHERE   aa.PayingControlSeq = :PayingControlSeq
            ;
EOQ;
        $ri = $this->app->dbAdapter->query($sql)->execute(array( ':PayingControlSeq' => $pcSeq ));
        $data = ResultInterfaceToArray($ri);

        // 本締め／仮締め区分取得
        $sql = <<<EOQ
            SELECT  PayingControlStatus
            FROM    T_PayingControl
            WHERE   Seq = :Seq
            ;
EOQ;
        $pcSts = $this->app->dbAdapter->query($sql)->execute(array( ':Seq' => $pcSeq ))->current();

        // 調整金科目
        $ri = $this->app->dbAdapter->query(" SELECT KeyCode, KeyContent FROM M_Code WHERE Validflg = 1 AND CodeId = 89 ORDER BY KeyCode ")->execute(null);
        $kamokuList = ResultInterfaceToArray($ri);

        $this->view->assign('adjlist', $data);
        $this->view->assign('pcseq', $pcSeq);
        $this->view->assign('kamokuList', $kamokuList);
        $this->view->assign('pcSts', $pcSts);

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
                // OEMID
                } else if( $key == 'oemid' ) {
                    $oemId = $param;
                // 加盟店ID
                } else if( $key == 'eid' ) {
                    $enterpriseId = $param;
                // 立替振込管理Seq
                } else if( $key == 'pcseq' ) {
                    $pcSeq = $param;
                }
            }

            // ユーザーIDの取得
            $obj = new TableUser( $this->app->dbAdapter );
            $userId = $obj->getUserId( 0, $this->app->authManagerAdmin->getUserInfo()->OpId );

            $mdlaa = new TableAdjustmentAmount($this->app->dbAdapter);

            // 調整額の削除
            foreach( $delSerialNumbers as $serialNumber ) {
                // AT_AdjustmentAmount削除
                $this->app->dbAdapter->query(" DELETE FROM AT_AdjustmentAmount WHERE PayingControlSeq = :PayingControlSeq AND SerialNumber = :SerialNumber "
                    )->execute(array(':PayingControlSeq' => $pcSeq, ':SerialNumber' => $serialNumber));

                // 調整額管理の物理削除
                $conditionArray = array('PayingControlSeq' => $pcSeq, 'SerialNumber' => $serialNumber);
                $mdlaa->deleteAdjustmentAmount($conditionArray);

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

                        // 現在の加盟店IDとOEMIDを取得した情報と比較する。
                        if(! ($odata['EnterpriseId'] == $enterpriseId && $odata['OemId'] == $oemId)) {
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
                        $sql = "SELECT MAX(SerialNumber) AS SerialNumber FROM T_AdjustmentAmount WHERE PayingControlSeq = :PayingControlSeq";
                        $maxNum = $this->app->dbAdapter->query($sql)->execute(array( ':PayingControlSeq' => $pcSeq ))->current();

                        // データ行を新規作成
                        $data = array(
                                'PayingControlSeq' => $pcSeq,                   // 立替振込管理Seq
                                'SerialNumber' => $maxNum['SerialNumber'] + 1,  // 連番
                                'OrderId' => $orderId,                          // 注文ID
                                'OrderSeq' => $orderSeq,                        // 注文Seq
                                'ItemCode' => $itemCodes[$key],                 // 科目
                                'AdjustmentAmount' => $adjustmentAmount,        // 調整額
                                'RegistId' => $userId,                          // 登録者
                                'UpdateId' => $userId,                          // 更新者
                        );

                        // 新規追加
                        $mdlaa->saveNew($data);

                        // 2015/10/05 Y.Suzuki Add 会計対応 Stt
                        // 会計用項目のINSERT
                        $mdlataa = new ATableAdjustmentAmount($this->app->dbAdapter);
                        $atdata = array(
                                'PayingControlSeq' => $pcSeq,
                                'SerialNumber' => $maxNum['SerialNumber'] + 1,
                                'DailySummaryFlg' => 0,
                        );

                        $mdlataa->saveNew($atdata);
                        // 2015/10/05 Y.Suzuki Add 会計対応 End

                        // 更新件数カウントアップ
                        $updatecount++;
                    }
                    // 既存行 かつ 削除対象でない場合
                    if (empty($value) && ! in_array($key, $delSerialNumbers)) {
                        // 登録済みのデータを取得
                        $mdlaa = new TableAdjustmentAmount($this->app->dbAdapter);
                        $aadata = $mdlaa->find($pcSeq, $key)->current();
                        // 注文ID、調整科目、調整額が違う場合、更新する。
                        if ($orderId != $aadata['OrderId'] || $itemCodes[$key] != $aadata['ItemCode'] || $adjustmentAmount != $aadata['AdjustmentAmount']) {
                            // 更新用データを生成
                            $data = array(
                                    'OrderId' => $orderId,                          // 注文ID
                                    'OrderSeq' => $orderSeq,                        // 注文Seq
                                    'ItemCode' => $itemCodes[$key],                 // 科目
                                    'AdjustmentAmount' => $adjustmentAmount,        // 調整額
                                    'UpdateId' => $userId,                          // 更新者
                            );

                            // 更新処理
                            $mdlaa->saveUpdate($data, $pcSeq, $key);

                            // 更新件数カウントアップ
                            $updatecount++;
                        }
                    }
                }
            }
            if (! isset($msg)) {
                // 精算調整額取得
                $sql = "SELECT SUM(AdjustmentAmount) AS AdjustmentAmount FROM T_AdjustmentAmount WHERE PayingControlSeq = :PayingControlSeq";
                $adjSum = $this->app->dbAdapter->query($sql)->execute(array( ':PayingControlSeq' => $pcSeq ))->current();

                $mdlpc = new TablePayingControl($this->app->dbAdapter);
                // 現在の生産調整額、振込確定金額を取得
                $pcData = $mdlpc->find($pcSeq)->current();

                $data = array(
                        'AdjustmentDecisionFlg' => 1,                           // 調整額確定フラグ
                        'AdjustmentDecisionDate' => date('Y-m-d'),              // 調整額確定日付
                        'AdjustmentAmount' => $adjSum['AdjustmentAmount'],      // 精算調整額
                        'DecisionPayment' => $adjSum['AdjustmentAmount'] - $pcData['AdjustmentAmount'] + $pcData['DecisionPayment'],    // 振込確定金額
                        'UpdateId' => $userId,                                  // 更新者
                );
                $mdlpc->saveUpdate($data, $pcSeq);

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
     * 注文一覧表示
     */
    public function trnlistAction()
    {
        $params  = $this->getParams();

        $pcseq = isset($params['pcseq']) ? $params['pcseq'] : 0;

        $mdlvcc = new ViewChargeConfirm($this->app->dbAdapter);
        $mdlvcf = new ViewChargeFix($this->app->dbAdapter);

        // 指定された立替振込管理データを取得する。
        $pcData = $mdlvcc->findChargeConfirm(array('Seq' => $pcseq), 'Seq')->current();

        // 指定された立替振込管理Seqにぶら下がる注文データを取得する。
        $datas = ResultInterfaceToArray($mdlvcf->getFixList($pcseq));

        $tUseAmount = 0;
        $tSettlementFee = 0;
        $tClaimFee = 0;
        $tStampFee = 0;
        $tChg_ChargeAmount = 0;

        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);

        // count関数対策
        $datasLen = 0;
        if(!empty($datas)) {
            $datasLen = count($datas);
        }

        for ($i = 0 ; $i < $datasLen; $i++)
        {
            // 入金方法
            if (preg_match( "/^[0]{1}/", $datas [$i] ['Rct_Status'])) {
                $datas [$i] ['ReceiptClass'] = '未入金';
            } else {
                $datas [$i] ['ReceiptClass'] = $codeMaster->getReceiptMethodCaptionM_Code ( $datas [$i] ['ReceiptClass'] );
            }

            $tUseAmount        += (int)$datas[$i]['UseAmount'];
            $tSettlementFee    += (int)$datas[$i]['SettlementFee'];
            $tClaimFee         += (int)$datas[$i]['ClaimFee'];
            $tStampFee         += (int)$datas[$i]['StampFee'];
            $tChg_ChargeAmount += (int)$datas[$i]['Chg_ChargeAmount'];
        }

        $this->view->assign('pcseq', $pcseq);
        $this->view->assign('codeMaster', new CoralCodeMaster($this->app->dbAdapter));
        $this->view->assign('pcData', $pcData);
        $this->view->assign('list', $datas);
        $this->view->assign('tUseAmount', $tUseAmount);
        $this->view->assign('tSettlementFee', $tSettlementFee);
        $this->view->assign('tClaimFee', $tClaimFee);
        $this->view->assign('tStampFee', $tStampFee);
        $this->view->assign('tChg_ChargeAmount', $tChg_ChargeAmount);

        return $this->view;
	}

    /**
     * キャンセル明細
     */
    public function cnllistAction()
    {
        $params = $this->getParams();

        $pcseq = isset($params['pcseq']) ? $params['pcseq'] : 0;

        $mdlvcc = new ViewChargeConfirm($this->app->dbAdapter);
        $mdlvcnl = new ViewChargeCancel($this->app->dbAdapter);

        // 指定された立替振込管理データを取得する。
        $pcData = $mdlvcc->findChargeConfirm(array('Seq' => $pcseq), 'Seq')->current();

        // 指定された立替振込管理Seqにぶら下がるキャンセルデータを取得する。
        $datas = ResultInterfaceToArray($mdlvcnl->getCancelData($pcseq));

        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);

        // count関数対策
        $datasLen = 0;
        if(!empty($datas)) {
            $datasLen = count($datas);
        }

        for ($i = 0 ; $i < $datasLen; $i++)
        {
            switch($datas[$i]['CancelPhase'])
            {
                case 1:
                    $datas[$i]['CancelPhase'] = '通常';
                    break;
                case 2:
                    $datas[$i]['CancelPhase'] = '立替済';
                    break;
                case 3:
                    $datas[$i]['CancelPhase'] = '立替・入金済';
                    break;
                case 4:
                    $datas[$i]['CancelPhase'] = '未立替・入金済';
                    break;
                default:
                    $datas[$i]['CancelPhase'] = '';
                    break;
            }
        }

        $this->view->assign('pcseq', $pcseq);
        $this->view->assign('codeMaster', new CoralCodeMaster($this->app->dbAdapter));
        $this->view->assign('pcData', $pcData);
        $this->view->assign('list', $datas);

        return $this->view;
	}

    /**
     * 印紙代明細
     */
    public function stamplistAction()
    {
        $params = $this->getParams();

        $pcseq = isset($params['pcseq']) ? $params['pcseq'] : 0;

        $mdlvcc = new ViewChargeConfirm($this->app->dbAdapter);
        $mdlvcsf = new ViewChargeStampFee($this->app->dbAdapter);

        // 指定された立替振込管理データを取得する。
        $pcData = $mdlvcc->findChargeConfirm(array('Seq' => $pcseq), 'Seq')->current();

        // 指定された立替振込管理Seqにぶら下がる印紙代データを取得する。
        $datas = ResultInterfaceToArray($mdlvcsf->getStampFeeData($pcseq));

        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);

        $this->view->assign('pcseq', $pcseq);
        $this->view->assign('codeMaster', new CoralCodeMaster($this->app->dbAdapter));
        $this->view->assign('pcData', $pcData);
        $this->view->assign('list', $datas);

        return $this->view;
	}

    /**
     * 立替精算戻し明細
     */
    public function paybacklistAction()
    {
        $params = $this->getParams();

        $pcseq = isset($params['pcseq']) ? $params['pcseq'] : 0;

        $mdlvcc = new ViewChargeConfirm($this->app->dbAdapter);

        // 指定された立替振込管理データを取得する。
        $pcData = $mdlvcc->findChargeConfirm(array('Seq' => $pcseq), 'Seq')->current();

        // 指定された立替振込管理Seqをもつ立替精算戻しデータを取得する。
        $sql .= " SELECT pbc.OrderSeq ";
        $sql .= " ,      odr.OrderId ";
        $sql .= " ,      odr.ReceiptOrderDate ";
        $sql .= " ,      cus.NameKj ";
        $sql .= " ,      cus.CustomerId ";
        $sql .= " ,      odr.SiteId ";
        $sql .= " ,      odr.UseAmount ";
        $sql .= " ,      pas.SettlementFee ";
        $sql .= " ,      pas.ClaimFee ";
        $sql .= " ,      IFNULL( (SELECT SUM(sf.StampFee) FROM T_StampFee sf WHERE odr.OrderSeq = sf.OrderSeq), 0) AS StampFee ";
        $sql .= " ,      CASE odr.Rct_Status ";
        $sql .= "          WHEN '0' THEN '未入金' ";
        $sql .= "          WHEN '1' THEN (CASE pc.ReceiptClass WHEN 1 THEN 'コンビニ' WHEN 2 THEN '郵便局' WHEN 3 THEN '銀行' ELSE '未入金' END)  ";
        $sql .= "          ELSE '' ";
        $sql .= "        END AS RctStatusStr ";
        $sql .= " ,      pbc.PayBackAmount ";
        $sql .= " ,      pbc.RegistDate AS PaybackRegistDate ";
        $sql .= " ,      F_GetLoginUserName(pbc.RegistId) AS PaybackRegistName ";
        $sql .= " FROM   T_PayingBackControl pbc ";
        $sql .= "        INNER JOIN T_Order odr ON pbc.OrderSeq = odr.OrderSeq ";
        $sql .= "        INNER JOIN T_Customer cus ON odr.OrderSeq = cus.OrderSeq ";
        $sql .= "        INNER JOIN T_PayingAndSales pas ON odr.OrderSeq = pas.OrderSeq ";
        $sql .= "        LEFT OUTER JOIN T_ReceiptControl pc ON odr.OrderSeq = pc.OrderSeq ";
        $sql .= " WHERE  1 = 1 ";
        $sql .= " AND    pbc.PayingControlSeq = :PayingControlSeq ";
        $sql .= " GROUP BY pbc.OrderSeq ";
        $sql .= " ORDER BY pbc.OrderSeq ";

        // SQL実行
        $ri = $this->app->dbAdapter->query($sql)->execute(array(':PayingControlSeq' => $pcseq));

        $datas = ResultInterfaceToArray($ri);

        // count関数対策
        $datasLen = 0;
        if(!empty($datas)) {
            $datasLen = count($datas);
        }

        // 集計行
        for ($i = 0 ; $i < $datasLen; $i++)
        {
            $tUseAmount        += (int)$datas[$i]['UseAmount'];
            $tSettlementFee    += (int)$datas[$i]['SettlementFee'];
            $tClaimFee         += (int)$datas[$i]['ClaimFee'];
            $tStampFee         += (int)$datas[$i]['StampFee'];
            $tPayBackAmount    += (int)$datas[$i]['PayBackAmount'];
        }

        $this->view->assign('pcseq', $pcseq);
        $this->view->assign('codeMaster', new CoralCodeMaster($this->app->dbAdapter));
        $this->view->assign('pcData', $pcData);
        $this->view->assign('list', $datas);
        $this->view->assign('tUseAmount', $tUseAmount);
        $this->view->assign('tSettlementFee', $tSettlementFee);
        $this->view->assign('tClaimFee', $tClaimFee);
        $this->view->assign('tStampFee', $tStampFee);
        $this->view->assign('tPayBackAmount', $tPayBackAmount);

        return $this->view;
	}
}

