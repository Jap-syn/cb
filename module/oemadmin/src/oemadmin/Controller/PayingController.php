<?php
namespace oemadmin\Controller;

use Coral\Coral\Controller\CoralControllerAction;
use Coral\Coral\CoralCodeMaster;
use Coral\Base\BaseHtmlUtils;
use oemadmin\Application;
use Coral\Base\BaseGeneralUtils;
use models\Table\TableOem;
use models\View\ViewChargeConfirm;
use models\View\ViewChargeFix;
use models\View\ViewChargeCancel;
use models\View\ViewChargeStampFee;
use models\Table\TablePayingControl;
use models\Table\TableUser;
use models\Table\TableSystemProperty;
use models\Table\TableEnterprise;

class PayingController extends CoralControllerAction
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

		$this->addStyleSheet($this->app->getOemCss())
		->addJavaScript('../../js/prototype.js')
		->addJavaScript('../../js/corelib.js');

		$this->setPageTitle($this->app->getOemServiceName()." - 立替確認");

		// $this->view->assign( 'current_action', $this->getCurrentAction() );
	}

    /**
     * 立替確認リストを表示する。
     */
    public function listAction()
    {
        // 締め日パターンマスターを取得
        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
        $caption = $codeMaster->getFixPatternMaster(/* $isDummyOn =  */false);

        $mdlvc = new ViewChargeConfirm($this->app->dbAdapter);
        $numSimePtn = 0;

        $oemId = $this->app->authManagerAdmin->getUserInfo()->OemId;
        $datas = ResultInterfaceToArray($mdlvc->getConfirmList2(0, '', '', false, $numSimePtn, $oemId, -1, false, 0));

        $advancesFl = $this->checkOemAdvances($oemId);
        $this->view->assign('advancesFl', $advancesFl);

        $captionCount = 0;
        if(!empty($caption)){
            $captionCount = count($caption);
        }

        $this->view->assign('genzai', BaseGeneralUtils::getDateString(date('Y-m-d')));
        $this->view->assign('list', $datas);
        $this->view->assign('count', $captionCount);
        $this->view->assign('current_action', 'paying/list');

        return $this->view;
    }

    /**
     * 立替予測を表示する。 その２
     */
    public function forecast2Action()
    {
        $mdlvc = new ViewChargeConfirm($this->app->dbAdapter);

        $oemId = $this->app->authManagerAdmin->getUserInfo()->OemId;

        // 立替条件クリア日は無条件に今日現在
        $cdlz = date('Y-m-d');

        $numSimePtn = 0;// 有効締め日パターン数
        $datas = $mdlvc->getForecastList2($oemId, $numSimePtn);

        $this->view->assign('genzai', BaseGeneralUtils::getDateString(date('Y-m-d')));
        $this->view->assign('list', $datas);
        $this->view->assign('numSimePtn', $numSimePtn);     // 追加項目
        $this->view->assign('current_action', 'paying/forecast2');

        return $this->view;
    }

    /**
     * 立替済みリストを表示する。
     */
    public function elistAction()
    {
        $numSimePtn = 0;

        $from = $this->getParams()['f'];
        $to = $this->getParams()['t'];

        if ($from == "" && $to == "")
        {
            $from = date ( 'Y-m-d', strtotime ( "-1 month" ) );
        }

        $mdlvc = new ViewChargeConfirm($this->app->dbAdapter);

        $oemId = $this->app->authManagerAdmin->getUserInfo()->OemId;
        $datas = ResultInterfaceToArray($mdlvc->getConfirmList2(1, $from, $to, false, $numSimePtn, $oemId, -1, false, null));

        $advancesFl = $this->checkOemAdvances($oemId);
        $this->view->assign('advancesFl', $advancesFl);

        $this->view->assign('genzai', BaseGeneralUtils::getDateString(date('Y-m-d')));
        $this->view->assign('list', $datas);
        $this->view->assign('f', $from);
        $this->view->assign('t', $to);
        $this->view->assign('numSimePtn', $numSimePtn);     // 追加項目
        $this->view->assign( 'current_action', 'paying/elist');

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

        $userInfo = $this->app->authManagerAdmin->getUserInfo();

        $oemInfo = $this->app->getOemInfo();
        $payingMethod = $oemInfo['PayingMethod'];

        //OEMID取得
        $oemId = empty($userInfo->OemId) ? null : $userInfo->OemId;

        $params = $this->getParams();

        // 立替確定日
        $decisionDateStr = isset($params['d']) ? $params['d'] : date('Y-m-d');

        // 立替実行（予定）日
        $execDateStr = isset($params['e']) ? $params['e'] : date('Y-m-d');

        $mdlcc = new ViewChargeConfirm($this->app->dbAdapter);

        // 指定確定日、指定締めパターンの立替詳細データを取得
        $i = 0;
        foreach ($caption as $key => $value) {
            $list[$i] = ResultInterfaceToArray($mdlcc->getConfirmDetailList($decisionDateStr, $execDateStr, $key, $oemId, 0, 1));
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
        $total['PayBackAmount'] = 0;
        $total['AdjustmentAmount'] = 0;

        $captionCount = 0;
        if(!empty($caption)){
            $captionCount = count($caption);
        }
        for ($listi = 0 ; $listi < $captionCount ; $listi++)
        {
            $listCount = 0;
            if(!empty($list[$listi])){
                $listCount = count($list[$listi]);
            }
            for ($i = 0 ; $i < $listCount ; $i++)
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
                $total['PayBackAmount'] += $list[$listi][$i]['PayBackAmount'];
                $total['AdjustmentAmount'] += $list[$listi][$i]['AdjustmentAmount'];
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
        $this->view->assign('decisiondatestr',$decisionDateStr);
        $this->view->assign('execdatestr',$execDateStr);
        $this->view->assign('oemid',$oemId);
        $this->view->assign('current_action', 'paying/dlist2');

        return $this->view;
    }

    /**
     * 立替詳細画面の表示　その３
     * @see 調整額を入力できるようにしたもの
     */
    public function dlist3Action()
    {
        $payingMethod = 0;

        // 締め日パターンマスターを取得
        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
        $caption = $codeMaster->getFixPatternMaster(/* $isDummyOn =  */false);

        $oemInfo = $this->app->getOemInfo();
        $payingMethod = $oemInfo['PayingMethod'];

        $params = $this->getParams();

        // 立替確定日
        $decisionDateStr = isset($params['d']) ? $params['d'] : date('Y-m-d');

        // 立替実行（予定）日
        $execDateStr = isset($params['e']) ? $params['e'] : date('Y-m-d');

        $mdlcc = new ViewChargeConfirm($this->app->dbAdapter);

        $oemId = $this->app->authManagerAdmin->getUserInfo()->OemId;

            // 指定確定日、指定締めパターンの立替詳細データを取得
        $i = 0;
        foreach ($caption as $key => $value) {
            $list[$i] = ResultInterfaceToArray($mdlcc->getConfirmDetailList($decisionDateStr, $execDateStr, $key, $oemId, 0, 0));
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

        $captionCount = 0;
        if(!empty($caption)){
            $captionCount = count($caption);
        }
        for ($listi = 0 ; $listi < $captionCount ; $listi++)
        {
            $listCount = 0;
            if(!empty($list[$listi])){
                $listCount = count($list[$listi]);
            }
            for ($i = 0 ; $i < $listCount ; $i++)
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
                $total['PayBackAmount'] += $list[$listi][$i]['PayBackAmount'];
                $total['AdjustmentAmount'] += $list[$listi][$i]['AdjustmentAmount'];
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
        $this->view->assign('decisiondatestr',$decisionDateStr);
        $this->view->assign('execdatestr',$execDateStr);
        $this->view->assign('oemid',$oemId);
        $this->view->assign('current_action', 'paying/dlist3');

        return $this->view;
    }

    /**
     * 総合振り込みデータダウンロード
     */
    public function transdatadlAction()
    {
        // パラメータ取得
        $params = $this->getParams();

        // 立替確定日
        $decisionDate = empty($params['d']) ? date('Y-m-d') : $params['d'];
        // 立替予定日
        $execScheduleDate = empty($params['e']) ? date('Y-m-d') : $params['e'];

        // OEMID
        $oemId = $this->app->authManagerAdmin->getUserInfo()->OemId;

        // OEM情報
        $mdlOem = new TableOem($this->app->dbAdapter);
        $oem = $mdlOem->findOem2($oemId)->current();

        $mdlsp = new TableSystemProperty($this->app->dbAdapter);
        $category = 'systeminfo';
        $name = 'TempFileDir';
        $transCsvDir = $mdlsp->getValue(TableSystemProperty::DEFAULT_MODULE, $category, $name);

        // 立替振込管理テーブルを更新
        $mdlpc = new TablePayingControl($this->app->dbAdapter);

        // ユーザーIDの取得
        $obj = new TableUser( $this->app->dbAdapter );
        $userId = $obj->getUserId( 1, $this->app->authManagerAdmin->getUserInfo()->OemOpId );
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
// ------------------------------------------------------------------
        // OEMID、確定日、予定日ごとにCSV作成

        // OEM情報
        $mdlOem = new TableOem($this->app->dbAdapter);
        $oem = $mdlOem->findOem2($value['OemId'])->current();

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
;
EOQ;

            $ri = $this->app->dbAdapter->query($sql)->execute(null);
            $csvData = ResultInterfaceToArray($ri);

            // 合計の算出
            $totalCnt = 0;
            $totalDecisionPayment = 0;
            $maxSeq = 0;

            if (! empty($csvData)) {
                // 拡張子
                $ext = 'csv';
                if ($oem != false && $oem['PayingMethod'] == 1 && $oem['FixedLengthFlg'] == 1) {
                    // OEM立替の固定長の場合、テキスト形式
                    $ext = 'txt';
                }
                // ファイル名
                $transCsvFileName = sprintf("TransferData_%s_%s.%s", date("YmdHis"), $csvData[0]['FixedDate'], $ext);

                // ファイルフルパス
                $transCsvFullFileName = $transCsvDir . '/' . $transCsvFileName;
                // すでにファイルが作成されていたら削除
                if ( file_exists($transCsvFullFileName)) {
                    unlink($transCsvFullFileName);
                }

                // -----------------------------------------------
                // ヘッダーレコード
                // -----------------------------------------------
                if ($oem != false && $oem['PayingMethod'] == 1) {
                    //OEM立替のOEM
                    if ($oem['FixedLengthFlg'] == 1) {
                        // 固定長
                        $headerRecord = sprintf(
                            "1210%s%s%02d%02d%04d%s%03d%s%01d%07d%s\r\n",
                            $oem['ConsignorCode'],                                                                                                                          // 委託者コード
                            BaseGeneralUtils::rpad(BaseGeneralUtils::convertWideToNarrowEx(BaseGeneralUtils::deleteMultiByte($oem['ConsignorName'])), ' ', 40, true),       // 委託者名
                            date('m', strtotime($csvData[0]['ExecScheduleDate'])),
                            date('d', strtotime($csvData[0]['ExecScheduleDate'])),
                            $oem['RemittingBankCode'],                                                                                                                      // 仕向金融機関番号
                            BaseGeneralUtils::rpad(BaseGeneralUtils::convertWideToNarrowEx(BaseGeneralUtils::deleteMultiByte($oem['RemittingBankName'])), ' ', 15, true),   // 仕向金融機関名
                            $oem['RemittingBranchCode'],                                                                                                                    // 仕向支店番号
                            BaseGeneralUtils::rpad(BaseGeneralUtils::convertWideToNarrowEx(BaseGeneralUtils::deleteMultiByte($oem['RemittingBranchName'])), ' ', 15, true), // 仕向支店名
                            $oem['AccountClass'],                                                                                                                           // 依頼人預金種別
                            $oem['AccountNumber'],                                                                                                                          // 依頼人口座番号
                            BaseGeneralUtils::rpad("", ' ', 17)
                        );
                    }
                    else {
                        // CSV
                        $headerRecord = sprintf(
                            "1,21,0,%s,%s,%02d%02d,%04d,%s,%03d,%s,%d,%07d,\r\n",
                            $oem['ConsignorCode'],                                                                                      // 委託者コード
                            BaseGeneralUtils::convertWideToNarrowEx(BaseGeneralUtils::deleteMultiByte($oem['ConsignorName'])),          // 委託者名
                            date('m', strtotime($csvData[0]['ExecScheduleDate'])),
                            date('d', strtotime($csvData[0]['ExecScheduleDate'])),
                            $oem['RemittingBankCode'],                                                                                  // 仕向金融機関番号
                            BaseGeneralUtils::convertWideToNarrowEx(BaseGeneralUtils::deleteMultiByte($oem['RemittingBankName'])),      // 仕向金融機関名
                            $oem['RemittingBranchCode'],                                                                                // 仕向支店番号
                            BaseGeneralUtils::convertWideToNarrowEx(BaseGeneralUtils::deleteMultiByte($oem['RemittingBranchName'])),    // 仕向支店名
                            $oem['AccountClass'],                                                                                       // 依頼人預金種別
                            $oem['AccountNumber']                                                                                       // 依頼人口座番号
                        );
                    }
                }
                else {
                    // CB or CB立替のOEM
                    $headerRecord = sprintf(
                        "1,21,0,1848513200,ｶ)ｷｬｯﾁﾎﾞｰﾙ,%02d%02d,0033,,002,,1,3804573,\r\n",
                        date('m', strtotime($csvData[0]['ExecScheduleDate'])),
                        date('d', strtotime($csvData[0]['ExecScheduleDate']))
                    );
                }
                $headerRecord = mb_convert_encoding($headerRecord, "SJIS", "UTF-8");

                // -----------------------------------------------
                // データレコード
                // -----------------------------------------------
                $dataRecords = "";
                $csvDataCount = 0;
                if(!empty($csvData)){
                    $csvDataCount = count($csvData);
                }
                for ($i = 0 ; $i < $csvDataCount ; $i++) {

                    $totalCnt++;
                    $totalDecisionPayment += $csvData[$i]['DecisionPayment'];

                    if ($oem != false && $oem['PayingMethod'] == 1 && $oem['FixedLengthFlg'] == 1) {
                        //OEM 固定長
                        $dataRecord = sprintf(
                            "2%04d%s%03d%s%s%01d%07d%s%s0%s7%s\r\n",
                            $csvData[$i]['FfCode'],                                                                                                                             // 銀行コード
                            BaseGeneralUtils::rpad(BaseGeneralUtils::convertWideToNarrowEx(BaseGeneralUtils::deleteMultiByte($csvData[$i]['FfName'])), ' ', 15, true),          // 銀行名
                            $csvData[$i]['FfBranchCode'],                                                                                                                       // 支店コード
                            BaseGeneralUtils::rpad(BaseGeneralUtils::convertWideToNarrowEx(BaseGeneralUtils::deleteMultiByte($csvData[$i]['FfBranchName'])), ' ', 15, true),    // 支店名
                            BaseGeneralUtils::rpad("",	' ', 4, true),                                                                                                          // 未使用
                            $csvData[$i]['FfAccountClass'],                                                                                                                     // 科目
                            $csvData[$i]['FfAccountNumber'],                                                                                                                    // 口座番号
                            BaseGeneralUtils::rpad(BaseGeneralUtils::convertWideToNarrowEx(BaseGeneralUtils::deleteMultiByte(($csvData[$i]['FfAccountName']))), ' ', 30),       // 受取人
                            BaseGeneralUtils::lpad($csvData[$i]['DecisionPayment'], '0', 10),                                                                                   // 金額
                            BaseGeneralUtils::rpad("", ' ', 20),
                            BaseGeneralUtils::rpad("", ' ', 8)
                        );
                    }
                    else {
                        // CSV
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
                    }
                    $dataRecords .= mb_convert_encoding($dataRecord, "SJIS", "UTF-8");

                }

                // -----------------------------------------------
                // トレーラレコード
                // -----------------------------------------------
                if ($oem != false && $oem['PayingMethod'] == 1 && $oem['FixedLengthFlg'] == 1) {
                    //OEM 固定長
                    $trailerRecord = sprintf(
                        "8%s%s%s\r\n",
                        BaseGeneralUtils::lpad($totalCnt, '0', 6),
                        BaseGeneralUtils::lpad($totalDecisionPayment, '0', 12),
                        BaseGeneralUtils::lpad("", ' ', 101)
                    );
                }
                else {
                    // CSV
                    $trailerRecord = sprintf(
                        "8,%d,%d,\r\n",
                        $totalCnt,
                        $totalDecisionPayment
                    );
                }
                $trailerRecord = mb_convert_encoding($trailerRecord, "SJIS", "UTF-8");

                // -----------------------------------------------
                // エンドレコード
                // -----------------------------------------------
                if ($oem != false && $oem['PayingMethod'] == 1 && $oem['FixedLengthFlg'] == 1) {
                    //OEM 固定長
                    $endRecord = BaseGeneralUtils::rpad("9", ' ', 120, true) . "\r\n";
                }
                else {
                    $endRecord = "9,\r\n";
                }
                $endRecord = mb_convert_encoding($endRecord, "SJIS", "UTF-8");

                // 作成したデータを結合
                $contents = $headerRecord . $dataRecords . $trailerRecord . $endRecord;
                // ファイルに保存
                file_put_contents($transCsvFullFileName, $contents);

            }
// -------------------------------------------------------------------------------

        // 立替確定日と立替予定日が一致するデータを抜き出す。
        $sql = <<<EOQ
SELECT  *
FROM    T_PayingControl
WHERE   DecisionDate = :DecisionDate
AND     ExecScheduleDate = :ExecScheduleDate
AND     IFNULL(OemId, 0) = :OemId
EOQ;

        $datas = $this->app->dbAdapter->query($sql)->execute(array( ':DecisionDate' => $decisionDate, ':ExecScheduleDate' => $execScheduleDate,':OemId' => $oemId ));

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
     * 注文一覧表示
     */
    public function trnlistAction()
    {
        $params = $this->getParams();

        $pcseq = isset($params['pcseq']) ? $params['pcseq'] : 0;

        $mdlvcc = new ViewChargeConfirm($this->app->dbAdapter);
        $mdlvcf = new ViewChargeFix($this->app->dbAdapter);

        $oemId = $this->app->authManagerAdmin->getUserInfo()->OemId;

        // 指定された立替振込管理データを取得する。
        $pcData = $mdlvcc->findChargeConfirm(array('Seq' => $pcseq), 'Seq', $oemId)->current();

        // 指定された立替振込管理Seqにぶら下がる注文データを取得する。
        $datas = ResultInterfaceToArray($mdlvcf->getFixList($pcseq, $oemId));

        $tUseAmount = 0;
        $tSettlementFee = 0;
        $tClaimFee = 0;
        $tChg_ChargeAmount = 0;

        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);

        $datasCount = 0;
        if(!empty($datas)){
            $datasCount = count($datas);
        }
        for ($i = 0 ; $i < $datasCount ; $i++)
        {
            // 入金方法
            $datas[$i]['Rct_ReceiptMethod'] = $codeMaster->getReceiptMethodCaption($datas[$i]['ReceiptClass']);

            $tUseAmount += (int)$datas[$i]['UseAmount'];
            $tSettlementFee += (int)$datas[$i]['SettlementFee'];
            $tClaimFee += (int)$datas[$i]['ClaimFee'];

            $tChg_ChargeAmount += (int)$datas[$i]['Chg_ChargeAmount'];
        }

        $this->view->assign('pcseq', $pcseq);
        $this->view->assign('codeMaster', new CoralCodeMaster($this->app->dbAdapter));
        $this->view->assign('pcData', $pcData);
        $this->view->assign('list', $datas);
        $this->view->assign('tUseAmount', $tUseAmount);
        $this->view->assign('tSettlementFee', $tSettlementFee);
        $this->view->assign('tClaimFee', $tClaimFee);
        $this->view->assign('tChg_ChargeAmount', $tChg_ChargeAmount);
        $this->view->assign('current_action', 'paying/trnlist');

        return $this->view;
    }

    /**
     * キャンセル明細
     */
    public function cnllistAction()
    {
        $param = $this->getParams();
        $pcseq = isset($param['pcseq']) ? $param['pcseq'] : 0;

        $mdlvcc = new ViewChargeConfirm($this->app->dbAdapter);
        $mdlvcnl = new ViewChargeCancel($this->app->dbAdapter);

        $oemId = $this->app->authManagerAdmin->getUserInfo()->OemId;

        // 指定された立替振込管理データを取得する。
        $pcData = $mdlvcc->findChargeConfirm(array('Seq' => $pcseq), 'Seq', $oemId)->current();

        // 指定された立替振込管理Seqにぶら下がるキャンセルデータを取得する。
        $datas = ResultInterfaceToArray($mdlvcnl->getCancelData($pcseq, $oemId));

        // キャンセル区分
        $datasCount = 0;
        if(!empty($datas)){
            $datasCount = count($datas);
        }
        for ($i = 0 ; $i < $datasCount ; $i++)
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
        $this->view->assign('current_action', 'paying/cnllist');

        return $this->view;
    }

    /**
     * 印紙代明細
     */
    public function stamplistAction()
    {
        $param = $this->getParams();
        $pcseq = isset($param['pcseq']) ? $param['pcseq'] : 0;

        $mdlvcc = new ViewChargeConfirm($this->app->dbAdapter);
        $mdlvcsf = new ViewChargeStampFee($this->app->dbAdapter);

        $oemId = $this->app->authManagerAdmin->getUserInfo()->OemId;

        // 指定された立替振込管理データを取得する。
        $pcData = $mdlvcc->findChargeConfirm(array('Seq' => $pcseq), 'Seq', $oemId)->current();

        // 指定された立替振込管理Seqにぶら下がる印紙代データを取得する。
        $datas = ResultInterfaceToArray($mdlvcsf->getStampFeeData($pcseq, $oemId));

        $this->view->assign('pcseq', $pcseq);
        $this->view->assign('codeMaster', new CoralCodeMaster($this->app->dbAdapter));
        $this->view->assign('pcData', $pcData);
        $this->view->assign('list', $datas);
        $this->view->assign('current_action', 'paying/stamplist');

        return $this->view;
    }

	/**
	 * OEM立替方法取得
	 */
	private function checkOemAdvances($oemId) {

		$oemInfo = $this->app->getOemInfo();
        if($oemInfo['PayingMethod']){
			$advancesFl = true;
		}else{
			$advancesFl = false;
		}

		return $advancesFl;
	}

    /**
     * 立替精算戻し明細
     */
    public function paybacklistAction()
    {
        $params = $this->getParams();

        $pcseq = isset($params['pcseq']) ? $params['pcseq'] : 0;

        $mdlvcc = new ViewChargeConfirm($this->app->dbAdapter);

        $oemId = $this->app->authManagerAdmin->getUserInfo()->OemId;

        // 指定された立替振込管理データを取得する。
        $pcData = $mdlvcc->findChargeConfirm(array('Seq' => $pcseq), 'Seq', $oemId)->current();

        // 指定された立替振込管理Seqをもつ立替精算戻しデータを取得する。
        $sql .= " SELECT odr.OrderSeq ";
        $sql .= " ,      odr.OrderId ";
        $sql .= " ,      odr.ReceiptOrderDate ";
        $sql .= " ,      cus.NameKj ";
        $sql .= " ,      cus.CustomerId ";
        $sql .= " ,      odr.SiteId ";
        $sql .= " ,      odr.UseAmount ";
        $sql .= " ,      pas.SettlementFee ";
        $sql .= " ,      pas.ClaimFee ";
        $sql .= " ,      (SELECT SUM(StampFee) FROM T_StampFee WHERE OrderSeq = odr.OrderSeq) AS StampFee ";
        $sql .= " ,      CASE odr.Rct_Status ";
        $sql .= "          WHEN '0' THEN '未入金' ";
        $sql .= "          WHEN '1' THEN ";
        $sql .= "              CASE (SELECT MAX(pc.ReceiptClass) AS ReceiptClass FROM T_ReceiptControl pc WHERE odr.P_OrderSeq = pc.OrderSeq) ";
        $sql .= "              WHEN 1 THEN 'コンビニ' WHEN 2 THEN '郵便局' WHEN 3 THEN '銀行' ELSE '未入金' END ";
        $sql .= "          ELSE '' ";
        $sql .= "        END AS RctStatusStr ";
        $sql .= " ,      pbc.PayBackAmount ";
        $sql .= " ,      pbc.RegistDate AS PaybackRegistDate ";
        $sql .= " ,      F_GetLoginUserName(pbc.RegistId) AS PaybackRegistName ";
        $sql .= " FROM   T_PayingBackControl pbc ";
        $sql .= "        INNER JOIN T_Order odr ON pbc.OrderSeq = odr.OrderSeq ";
        $sql .= "        INNER JOIN T_Customer cus ON odr.OrderSeq = cus.OrderSeq ";
        $sql .= "        INNER JOIN T_PayingAndSales pas ON odr.OrderSeq = pas.OrderSeq ";
        $sql .= " WHERE  1 = 1 ";
        $sql .= " AND    pbc.PayingControlSeq = :PayingControlSeq ";
        $sql .= " ORDER BY OrderSeq ";

        // SQL実行
        $ri = $this->app->dbAdapter->query($sql)->execute(array(':PayingControlSeq' => $pcseq));

        $datas = ResultInterfaceToArray($ri);

        // 集計行
        $datasCount = 0;
        if(!empty($datas)){
            $datasCount = count($datas);
        }
        for ($i = 0 ; $i < $datasCount ; $i++)
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
        $this->view->assign('tUseAmount', $tUseAmount);$this->view->assign('tSettlementFee', $tSettlementFee);
        $this->view->assign('tClaimFee', $tClaimFee);
        $this->view->assign('tStampFee', $tStampFee);
        $this->view->assign('tPayBackAmount', $tPayBackAmount);

        $this->view->assign('current_action', 'paying/paybacklist');

        return $this->view;
    }
}

