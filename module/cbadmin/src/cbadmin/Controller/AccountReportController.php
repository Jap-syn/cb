<?php
namespace cbadmin\Controller;

use cbadmin\Application;
use Coral\Coral\Controller\CoralControllerAction;
use Coral\Coral\CoralCodeMaster;
use models\Table\TableOem;
use DOMPDFModule\View\Model\PdfModel;
use Coral\Base\BaseGeneralUtils;
use models\Logic\LogicTemplate;
use Coral\Base\BaseHtmlUtils;
use models\Table\TableSystemProperty;
use oemmember\Controller\AccountController;
use models\Table\ATablePayOffDailyAccount2;

class AccountReportController extends CoralControllerAction {
    protected $_componentRoot = './application/views/components';


    /**
     * @var Application
     */
    protected $app;


    /**
     * コントローラ初期化
     */
    public function _init() {
        $this->app = Application::getInstance();

        // スタイルシートとjavascriptをアサイン
        $this->addStyleSheet('../css/default02.css');
        $this->addJavaScript('../js/prototype.js');
        $this->addJavaScript('../js/json+.js');
        $this->addJavaScript('../js/corelib.js');

        $this->setPageTitle('後払い.com - 会計帳票ダウンロード');

        ini_set('max_execution_time', 0);        // 実行タイムアウトを無効にする
    }

    /**
     * 会計帳票のダウンロードフォームを表示
     */
    public function dlformAction() {
        $params = $this->getParams();

        $outputTarget = (! empty($params['outputTarget'])) ? $params['outputTarget'] : 0;

        $mdloem = new TableOem($this->app->dbAdapter);

        // 日次の場合
        if ($outputTarget == 0) {
            $sql = "SELECT KeyCode, KeyContent FROM M_Code WHERE CodeId = 98 AND Class3 IN (0, 2)";
        }
        // 月次の場合
        else {
            $sql = "SELECT KeyCode, KeyContent FROM M_Code WHERE CodeId = 98 AND Class3 IN (1, 2)";
        }
        $ri = $this->app->dbAdapter->query($sql)->execute(null);
        $datas = ResultInterfaceToArray($ri);
        foreach ($datas as $value) {
            $reportList[$value['KeyCode']] = $value['KeyContent'];
        }

        // ナビゲーションタグを作成
        $reportListTag = BaseHtmlUtils::SelectTag('reportList', $reportList, $datas[0]['KeyCode'], ' onChange="javascript:setReport();"');

        $this->view->assign('reportList', $reportListTag);
        $this->view->assign('oemList', $mdloem->getOemIdList());
        $this->view->assign('oemId', -1);
        $this->view->assign('outputTarget', $outputTarget);
        $this->view->assign('selectCondition', 0);

        return $this->view;
    }

    /**
     * 帳票ダウンロード処理
     */
    public function dlreportAction() {

        ini_set( 'max_execution_time', 0 ); // 実行タイムアウトを無効にしておく(20151124)

        $params = $this->getParams();

        $errors = $this->validate($params);
        if (!empty($errors)) {
            $mdloem = new TableOem($this->app->dbAdapter);

            $this->view->assign('error', $errors);

            // 日次の場合
            if ($params['outputTarget'] == 0) {
                $sql = "SELECT KeyCode, KeyContent FROM M_Code WHERE CodeId = 98 AND Class3 IN (0, 2)";
            }
            // 月次の場合
            else {
                $sql = "SELECT KeyCode, KeyContent FROM M_Code WHERE CodeId = 98 AND Class3 IN (1, 2)";
            }
            $ri = $this->app->dbAdapter->query($sql)->execute(null);
            $datas = ResultInterfaceToArray($ri);
            foreach ($datas as $value) {
                $reportList[$value['KeyCode']] = $value['KeyContent'];
            }

            // ナビゲーションタグを作成
            $reportListTag = BaseHtmlUtils::SelectTag('reportList', $reportList, $params['reportList']);

            $this->view->assign('reportList', $reportListTag);
            $this->view->assign('oemList', $mdloem->getOemIdList());
            $this->view->assign('oemId', $params['oem']);
            $this->view->assign('outputTarget', $params['outputTarget']);
            $this->view->assign('targetPeriodFrom', $params['targetPeriodFrom']);
            $this->view->assign('targetPeriodTo', $params['targetPeriodTo']);
            $this->view->assign('targetMonth', $params['targetMonth']);
            $this->view->assign('selectCondition', $params['selectCondition']);

            $this->setTemplate('dlform');

            return $this->view;
        }

        // 出力対象の帳票がPDFかCSVかを判定
        $sql = "SELECT Class1 FROM M_Code where CodeId = 98 AND KeyCode = :KeyCode";
        $isOutputFlg = $this->app->dbAdapter->query($sql)->execute(array(':KeyCode' => $params['reportList']))->current()['Class1'];

        // PDF出力の場合
        if ($isOutputFlg == 0) {
            $response = $this->pdfDownload($params);
        }
        // CSV出力の場合
        else {
            $response = $this->csvDownload($params);
        }

        return $response;
    }

    /**
     * PDFダウンロード
     */
    protected function pdfDownload($data) {

        $pdf = new PdfModel();

        // どの帳票を出力するかによって処理をわける。
        // 直営日次統計表
        if ($data['reportList'] == 1) {
            // ベースクエリを取得
            $sql = $this->getCBNichijiTokeiBaseQuery();
            /* 検索条件を付加 */
            // 日次 OR 月次
            if ($data['outputTarget'] == 0) {
                $wheres .= " AND DailyMonthlyFlg = 0 ";      // 日次
                // 対象期間FROM～TO
                $wProcessingDate = BaseGeneralUtils::makeWhereDate(
                    'ProcessingDate',
                    BaseGeneralUtils::convertWideToNarrow($data['targetPeriodFrom']),
                    BaseGeneralUtils::convertWideToNarrow($data['targetPeriodTo'])
                );
                if ($wProcessingDate != '') {
                    $wheres .= " AND " . $wProcessingDate;
                }
            } else {
                $wheres .= " AND DailyMonthlyFlg = 1";      // 月次
                // 対象月
                $wheres .= " AND AccountDate = '" . date('Y-m-01', strtotime($data['targetMonth'])) . "'";
            }
            // 加盟店指定の場合
            if ($data['selectCondition'] == 4) {
                $wheres .= " AND EnterpriseId = " . $data['enterpriseId'];
            }

            // 検索条件を基本SQLに付加
            $sql = sprintf($sql, $wheres, $wheres);

            // データを取得
            $ri = $this->app->dbAdapter->query($sql)->execute(null);
            $datas = ResultInterfaceToArray($ri);

            // ファイル名の第二引数
            // 選択条件：[全て]の場合
            if ($data['selectCondition'] == 0) {
                $fileArg = "ALL";
            }
            // 選択条件：[加盟店指定]の場合
            else if ($data['selectCondition'] == 4) {
                $fileArg = $data['enterpriseId'];
            }

            $fileName = sprintf('ChokueiNichijiTokei_%s_%s.pdf', date("YmdHis"), $fileArg);
            $pdf->setTemplate('cbadmin/pdf/atchokueinichijitokei.phtml');
            $pdf->setOption('paperSize', 'a3');
        }
        // OEM日次統計表
        else if ($data['reportList'] == 2) {
            // ベースクエリを取得
            $sql = $this->getOEMNichijiTokeiBaseQuery();
            /* 検索条件を付加 */
            // 日次 OR 月次
            if ($data['outputTarget'] == 0) {
                $wheres .= " AND DailyMonthlyFlg = 0 ";      // 日次
                // 対象期間FROM～TO
                $wProcessingDate = BaseGeneralUtils::makeWhereDate(
                    'ProcessingDate',
                    BaseGeneralUtils::convertWideToNarrow($data['targetPeriodFrom']),
                    BaseGeneralUtils::convertWideToNarrow($data['targetPeriodTo'])
                );
                if ($wProcessingDate != '') {
                    $wheres .= " AND " . $wProcessingDate;
                }
            } else {
                $wheres .= " AND DailyMonthlyFlg = 1";      // 月次
                // 対象月
                $wheres .= " AND AccountDate = '" . date('Y-m-01', strtotime($data['targetMonth'])) . "'";
            }
            // OEM指定の場合
            if ($data['selectCondition'] == 3) {
                $wheres .= " AND IFNULL(OemId, 0) = " . $data['oem'];
            }
            // 加盟店指定の場合
            else if ($data['selectCondition'] == 4) {
                $wheres .= " AND EnterpriseId = " . $data['enterpriseId'];
            }

            // 検索条件を基本SQLに付加
            $sql = sprintf($sql, $wheres, $wheres);
            // データを取得
            $ri = $this->app->dbAdapter->query($sql)->execute(null);
            $dataAry = ResultInterfaceToArray($ri);

            // 取得データをループ → 小計をOEM単位の最終ページに追加
            foreach ($dataAry as $value) {
                if ((! empty($id)) && ($id != $value['OemId'])) {
                    $sql = $this->getOEMNichijiTokeiSubTotalQuery();
                    // 検索条件をOEM総合計用基本SQLに付加
                    $sql = sprintf($sql, $wheres);
                    $oemkei = $this->app->dbAdapter->query($sql)->execute(array(':OemId' => $id))->current();
                    $datas[] = $oemkei;
                }

                $datas[] = $value;      // 取得データ

                // OEMIDをバックアップ
                $id = $value['OemId'];
            }

            // ファイル名の第二、第三引数
            // 選択条件：[全て]の場合
            if ($data['selectCondition'] == 0) {
                $fileArg1 = "ALL";
                $fileArg2 = "ALL";
            }
            // 選択条件：[OEM指定]の場合
            else if ($data['selectCondition'] == 3) {
                $fileArg1 = $data['oem'];
                $fileArg2 = "ALL";
            }
            // 選択条件：[加盟店指定]の場合
            else if ($data['selectCondition'] == 4) {
                // 加盟店IDからOEMIDを取得
                $sql2 = "SELECT IFNULL(OemId, 0) AS OemId FROM T_Enterprise WHERE EnterpriseId = :EnterpriseId";
                $oemId = $this->app->dbAdapter->query($sql2)->execute(array(':EnterpriseId' => $data['enterpriseId']))->current()['OemId'];
                $fileArg1 = $oemId;
                $fileArg2 = $data['enterpriseId'];
            }

            $fileName = sprintf('OemNichijiTokei_%s_%s_%s.pdf', date("YmdHis"), $fileArg1, $fileArg2);
            $pdf->setTemplate('cbadmin/pdf/atoemnichijitokei.phtml');
            $pdf->setOption('paperSize', 'a3');
        }
        // 直営未払金・売掛金・加盟店未収金統計表（月次のみ）
        else if ($data['reportList'] == 3) {
            // ベースクエリを取得
            $sql = $this->getCBMibaraiTokeiBaseQuery();
            /* 検索条件を付加 */
            // 加盟店指定の場合
            if ($data['selectCondition'] == 4) {
                $wheres .= " AND EnterpriseId = " . $data['enterpriseId'];
            }

            // 検索条件を基本SQLに付加
            $sql = sprintf($sql, $wheres, $wheres);

            // データを取得
            $ri = $this->app->dbAdapter->query($sql)->execute(array(':AccountDate' => date('Y-m-01', strtotime($data['targetMonth']))));
            $datas = ResultInterfaceToArray($ri);

            // ファイル名の第二引数
            // 選択条件：[全て]の場合
            if ($data['selectCondition'] == 0) {
                $fileArg = "ALL";
            }
            // 選択条件：[加盟店指定]の場合
            else if ($data['selectCondition'] == 4) {
                $fileArg = $data['enterpriseId'];
            }

            $fileName = sprintf('ChokueiMibaraikin_%s_%s.pdf', date("YmdHis"), $fileArg);
            $pdf->setTemplate('cbadmin/pdf/atchokueimibaraitokei.phtml');
            $pdf->setOption('paperSize', 'a4');
        }
        // OEM未払金・売掛金・OEM未収金統計表（月次のみ）
        else {
            // ベースクエリを取得
            $sql = $this->getOEMMibaraiTokeiBaseQuery();
            /* 検索条件を付加 */
            // OEM指定の場合
            if ($data['selectCondition'] == 3) {
                $wheres .= " AND IFNULL(OemId, 0) = " . $data['oem'];
            }
            // 加盟店指定の場合
            else if ($data['selectCondition'] == 4) {
                $wheres .= " AND EnterpriseId = " . $data['enterpriseId'];
            }

            // 検索条件を基本SQLに付加
            $sql = sprintf($sql, $wheres, $wheres);

            // データを取得
            $ri = $this->app->dbAdapter->query($sql)->execute(array(':AccountDate' => date('Y-m-01', strtotime($data['targetMonth']))));
            $dataAry = ResultInterfaceToArray($ri);

            // 取得データをループ → 小計をOEM単位の最終ページに追加
            foreach ($dataAry as $value) {
                if ((! empty($id)) && ($id != $value['OemId'])) {
                    $sql = $this->getOEMMibaraiTokeiSubTotalQuery();
                    // 検索条件をOEM総合計用基本SQLに付加
                    $sql = sprintf($sql, $wheres);
                    $oemkei = $this->app->dbAdapter->query($sql)->execute(array(':AccountDate' => date('Y-m-01', strtotime($data['targetMonth'])), ':OemId' => $id))->current();
                    $datas[] = $oemkei;
                }

                $datas[] = $value;      // 取得データ

                // OEMIDをバックアップ
                $id = $value['OemId'];
            }

            // ファイル名の第二、第三引数
            // 選択条件：[全て]の場合
            if ($data['selectCondition'] == 0) {
                $fileArg1 = "ALL";
                $fileArg2 = "ALL";
            }
            // 選択条件：[OEM指定]の場合
            else if ($data['selectCondition'] == 3) {
                $fileArg1 = $data['oem'];
                $fileArg2 = "ALL";
            }
            // 選択条件：[加盟店指定]の場合
            else if ($data['selectCondition'] == 4) {
                // 加盟店IDからOEMIDを取得
                $sql2 = "SELECT IFNULL(OemId, 0) AS OemId FROM T_Enterprise WHERE EnterpriseId = :EnterpriseId";
                $oemId = $this->app->dbAdapter->query($sql2)->execute(array(':EnterpriseId' => $data['enterpriseId']))->current()['OemId'];
                $fileArg1 = $oemId;
                $fileArg2 = $data['enterpriseId'];
            }

            $fileName = sprintf('OemMibaraikin_%s_%s_%s.pdf', date("YmdHis"), $fileArg1, $fileArg2);
            $pdf->setTemplate('cbadmin/pdf/atoemmibaraitokei.phtml');
            $pdf->setOption('paperSize', 'a4');
        }

        $pdf->setOption('filename', $fileName);
        $pdf->setOption('paperOrientation', 'landscape');
        $pdf->setOption('basePath', $this->getBaseUrl());

        $pdf->setVariable(datas, $datas);
        $pdf->setVariable(DailyMonthlyFlg, $data['outputTarget']);
        $pdf->setVariable(documentRoot, $_SERVER['DOCUMENT_ROOT']);
        $pdf->setVariable(title, $fileName);

        return $pdf;

    }

    /**
     * CSVダウンロード
     */
    protected function csvDownload($data) {

        // ファイル名の引数を設定
        // 全ての場合
        if ($data['selectCondition'] == 0) {
            $fileArg1 = "ALL";
            $fileArg2 = "ALL";
        }
        // OEM全て
        else if ($data['selectCondition'] == 1) {
            $fileArg1 = "OEM";
            $fileArg2 = "ALL";
        }
        // CB直販全て
        else if ($data['selectCondition'] == 2) {
            $fileArg1 = "0";
            $fileArg2 = "ALL";
        }
        // OEM指定
        else if ($data['selectCondition'] == 3) {
            $fileArg1 = $data['oem'];
            $fileArg2 = "ALL";
        }
        // 加盟店指定
        else if ($data['selectCondition'] == 4) {
            // 加盟店IDからOEMIDを取得
            $sql2 = "SELECT IFNULL(OemId, 0) AS OemId FROM T_Enterprise WHERE EnterpriseId = :EnterpriseId";
            $oemId = $this->app->dbAdapter->query($sql2)->execute(array(':EnterpriseId' => $data['enterpriseId']))->current()['OemId'];
            $fileArg1 = $oemId;
            $fileArg2 = $data['enterpriseId'];
        }

        // どのCSVを出力するかによって処理を分ける
        // 売上明細
        if ($data['reportList'] == 5) {
            // ファイル名
            $fileName = sprintf('UriageMeisai_%s_%s_%s.csv', date("YmdHis"), $fileArg1, $fileArg2);

            // SQLを取得
            $sql = $this->getUriageMeisaiQuery();
            /* 検索条件を付加 */
            // 日次 OR 月次
            if ($data['outputTarget'] == 0) {
                // 対象期間FROM～TO
                $wProcessingDate = BaseGeneralUtils::makeWhereDate(
                'ProcessingDate',
                BaseGeneralUtils::convertWideToNarrow($data['targetPeriodFrom']),
                BaseGeneralUtils::convertWideToNarrow($data['targetPeriodTo'])
                );
                if ($wProcessingDate != '') {
                    $wheres .= " AND " . $wProcessingDate;
                }
            } else {
                // 対象月
                $wheres .= " AND AccountDate = '" . date('Y-m-01', strtotime($data['targetMonth'])) . "'";
            }

            // OEM全ての場合
            if ($data['selectCondition'] == 1) {
                $wheres .= " AND IFNULL(OemId, 0) <> 0";
            }
            // CB直販全ての場合
            else if ($data['selectCondition'] == 2) {
                $wheres .= " AND IFNULL(OemId, 0) = 0";
            }
            // OEM指定の場合
            else if ($data['selectCondition'] == 3) {
                $wheres .= " AND IFNULL(OemId, 0) = " . $data['oem'];
            }
            // 加盟店指定の場合
            else if ($data['selectCondition'] == 4) {
                $wheres .= " AND EnterpriseId = " . $data['enterpriseId'];
            }

            // 検索条件をSQLに付加
            $sql = sprintf($sql, $wheres, $wheres);

            // データを取得
            $ri = $this->app->dbAdapter->query($sql)->execute(null);
            $datas = ResultInterfaceToArray($ri);

            $templateId = 'CKI24174_05'; // 売上明細
        }
        // 消費者未収金明細（月次のみ）
        else if ($data['reportList'] == 6) {
            // ファイル名
            $fileName = sprintf('SyohisyaMisyukin_%s_%s_%s.csv', date("YmdHis"), $fileArg1, $fileArg2);

            // ベースクエリを取得
            $sql = $this->getShohishaMishukinMeisaiQuery();
            /* 検索条件を付加 */
            // 対象月
            $wheres .= " AND AccountDate = '" . date('Y-m-01', strtotime($data['targetMonth'])) . "'";

            // OEM指定の場合
            if ($data['selectCondition'] == 3) {
                $wheres .= " AND IFNULL(OemId, 0) = " . $data['oem'];
            }
            // 加盟店指定の場合
            else if ($data['selectCondition'] == 4) {
                $wheres .= " AND EnterpriseId = " . $data['enterpriseId'];
            }

            // 検索条件をSQLに付加
            $sql = sprintf($sql, $wheres, $wheres);

            // データを取得
            $ri = $this->app->dbAdapter->query($sql)->execute(null);
            $datas = ResultInterfaceToArray($ri);

            $templateId = 'CKI24174_06'; // 消費者未収金明細
        }
        // 収納代行委託先未収金明細（日次のみ）
        else if ($data['reportList'] == 7) {
            // ファイル名
            $fileName = sprintf('DaikoMisyukin_%s_%s.csv', date("YmdHis"), $fileArg2);

            // ベースクエリを取得
            $sql = $this->getShunodaikoMishukinMeisaiQuery();
            /* 検索条件を付加 */
            // 対象期間FROM～TO
            $wProcessingDate = BaseGeneralUtils::makeWhereDate(
            'ProcessingDate',
            BaseGeneralUtils::convertWideToNarrow($data['targetPeriodFrom']),
            BaseGeneralUtils::convertWideToNarrow($data['targetPeriodTo'])
            );
            if ($wProcessingDate != '') {
                $wheres .= " AND " . $wProcessingDate;
            }

            // 加盟店指定の場合
            if ($data['selectCondition'] == 4) {
                $wheres .= " AND EnterpriseId = " . $data['enterpriseId'];
            }

            // 検索条件をSQLに付加
            $sql = sprintf($sql, $wheres, $wheres);

            // データを取得
            $ri = $this->app->dbAdapter->query($sql)->execute(null);
            $datas = ResultInterfaceToArray($ri);

            $templateId = 'CKI24174_07'; // 収納代行委託先未収金明細
        }
        // OEM仮払金明細（月次のみ）
        else if ($data['reportList'] == 8) {
            // ファイル名
            $fileName = sprintf('OemKaribaraikin_%s_%s_%s.csv', date("YmdHis"), $fileArg1, $fileArg2);

            // ベースクエリを取得
            $sql = $this->getOEMKaribaraikinMeisaiQuery();
            /* 検索条件を付加 */
            // 対象月
            $wheres .= " AND AccountDate = '" . date('Y-m-01', strtotime($data['targetMonth'])) . "'";

            // OEM全ての場合
            if ($data['selectCondition'] == 1) {
                $wheres .= " AND IFNULL(OemId, 0) <> 0";
            }
            // CB直販全ての場合
            else if ($data['selectCondition'] == 2) {
                $wheres .= " AND IFNULL(OemId, 0) = 0";
            }
            // OEM指定の場合
            else if ($data['selectCondition'] == 3) {
                $wheres .= " AND IFNULL(OemId, 0) = " . $data['oem'];
            }
            // 加盟店指定の場合
            else if ($data['selectCondition'] == 4) {
                $wheres .= " AND EnterpriseId = " . $data['enterpriseId'];
            }

            // 検索条件をSQLに付加
            $sql = sprintf($sql, $wheres);

            // データを取得
            $ri = $this->app->dbAdapter->query($sql)->execute(null);
            $datas = ResultInterfaceToArray($ri);

            $templateId = 'CKI24174_08'; // OEM仮払金明細
        }
        // 直営未払金・売掛金・加盟店未収金明細（月次のみ）
        else if ($data['reportList'] == 9) {
            // ファイル名
            $fileName = sprintf('ChokueiUrikake_%s_%s.csv', date("YmdHis"), $fileArg2);

            // ベースクエリを取得
            $sql = $this->getChokueiUrikakeMeisaiQuery();
            /* 検索条件を付加 */
            // 対象月
            $wheres .= " AND AccountDate = '" . date('Y-m-01', strtotime($data['targetMonth'])) . "'";

            // 加盟店指定の場合
            if ($data['selectCondition'] == 4) {
                $wheres .= " AND EnterpriseId = " . $data['enterpriseId'];
            }

            // 検索条件をSQLに付加
            $sql = sprintf($sql, $wheres, $wheres);

            // データを取得
            $ri = $this->app->dbAdapter->query($sql)->execute(null);
            $datas = ResultInterfaceToArray($ri);

            $templateId = 'CKI24174_09'; // 直営未払金兼売掛金明細
        }
        // OEM未払金・売掛金・OEM未収金明細（月次のみ）
        else if ($data['reportList'] == 10) {
            // ファイル名
            $fileName = sprintf('OemUrikake_%s_%s_%s.csv', date("YmdHis"), $fileArg1, $fileArg2);

            // ベースクエリを取得
            $sql = $this->getOEMUrikakeMeisaiQurey();
            /* 検索条件を付加 */
            // 対象月
            $wheres .= " AND AccountDate = '" . date('Y-m-01', strtotime($data['targetMonth'])) . "'";

            // OEM全ての場合
            if ($data['selectCondition'] == 1) {
                $wheres .= " AND IFNULL(OemId, 0) <> 0";
            }
            // CB直販全ての場合
            else if ($data['selectCondition'] == 2) {
                $wheres .= " AND IFNULL(OemId, 0) = 0";
            }
            // OEM指定の場合
            else if ($data['selectCondition'] == 3) {
                $wheres .= " AND IFNULL(OemId, 0) = " . $data['oem'];
            }
            // 加盟店指定の場合
            else if ($data['selectCondition'] == 4) {
                $wheres .= " AND EnterpriseId = " . $data['enterpriseId'];
            }

            // 検索条件をSQLに付加
            $sql = sprintf($sql, $wheres, $wheres);

            // データを取得
            $ri = $this->app->dbAdapter->query($sql)->execute(null);
            $datas = ResultInterfaceToArray($ri);

            $templateId = 'CKI24174_10'; // OEM未払金兼売掛金明細
        }
        // 未収金日計
        else if ($data['reportList'] == 11) {
            // ファイル名
            $fileName = sprintf('MisyukinNikkei_%s.csv', date("YmdHis"));

            // SQLを取得
            $sql = $this->getMishukinHikeiQuery();
            /* 検索条件を付加 */
            // 日次 OR 月次
            if ($data['outputTarget'] == 0) {
                // 対象期間FROM～TO
                $wProcessingDate = BaseGeneralUtils::makeWhereDate(
                'ProcessingDate',
                BaseGeneralUtils::convertWideToNarrow($data['targetPeriodFrom']),
                BaseGeneralUtils::convertWideToNarrow($data['targetPeriodTo'])
                );
                if ($wProcessingDate != '') {
                    $wheres .= " AND " . $wProcessingDate;
                }
            } else {
                // 対象月
                $wheres .= " AND AccountDate = '" . date('Y-m-01', strtotime($data['targetMonth'])) . "'";
            }

            // 検索条件をSQLに付加
            $sql = sprintf($sql, $wheres);

            // データを取得
            $ri = $this->app->dbAdapter->query($sql)->execute(null);
            $datas = ResultInterfaceToArray($ri);

            $templateId = 'CKI24174_11'; // 未収金日計
        }
        // 仮払金日計
        else if ($data['reportList'] == 12) {
            // ファイル名
            $fileName = sprintf('KaribaraiNikkei_%s_%s.csv', date("YmdHis"), $fileArg1);

            // SQLを取得
            $sql = $this->getKaribaraikinHikeiQuery();
            /* 検索条件を付加 */
            // 日次 OR 月次
            if ($data['outputTarget'] == 0) {
                // 対象期間FROM～TO
                $wProcessingDate = BaseGeneralUtils::makeWhereDate(
                'ProcessingDate',
                BaseGeneralUtils::convertWideToNarrow($data['targetPeriodFrom']),
                BaseGeneralUtils::convertWideToNarrow($data['targetPeriodTo'])
                );
                if ($wProcessingDate != '') {
                    $wheres .= " AND " . $wProcessingDate;
                }
            } else {
                // 対象月
                $wheres .= " AND AccountDate = '" . date('Y-m-01', strtotime($data['targetMonth'])) . "'";
            }

            // OEM指定の場合
            if ($data['selectCondition'] == 3) {
                $wheres .= " AND IFNULL(OemId, 0) = " . $data['oem'];
            }

            // 検索条件をSQLに付加
            $sql = sprintf($sql, $wheres, $wheres);

            // データを取得
            $ri = $this->app->dbAdapter->query($sql)->execute(null);
            $datas = ResultInterfaceToArray($ri);

            $templateId = 'CKI24174_12'; // 仮払金日計
        }
        // 精算日計（日次のみ）
        else if ($data['reportList'] == 13) {
            // ファイル名
            $fileName = sprintf('SeisanNikkei_%s_%s.csv', date("YmdHis"), $fileArg1);

            // ベースクエリを取得
            $sql = $this->getSeisanHikeiQuery();
            /* 検索条件を付加 */
            // 対象期間FROM～TO
            $wProcessingDate = BaseGeneralUtils::makeWhereDate(
            'ProcessingDate',
            BaseGeneralUtils::convertWideToNarrow($data['targetPeriodFrom']),
            BaseGeneralUtils::convertWideToNarrow($data['targetPeriodTo'])
            );
            if ($wProcessingDate != '') {
                $wheres .= " AND " . $wProcessingDate;
            }

            // OEM全ての場合
            if ($data['selectCondition'] == 1) {
                $wheres .= " AND IFNULL(OemId, 0) <> 0";
            }
            // CB直販全ての場合
            else if ($data['selectCondition'] == 2) {
                $wheres .= " AND IFNULL(OemId, 0) = 0";
            }
            // OEM指定の場合
            else if ($data['selectCondition'] == 3) {
                $wheres .= " AND IFNULL(OemId, 0) = " . $data['oem'];
            }
            // 加盟店指定の場合
            else if ($data['selectCondition'] == 4) {
                $wheres .= " AND EnterpriseId = " . $data['enterpriseId'];
            }

            // 検索条件をSQLに付加
            $sql = sprintf($sql, $wheres, $wheres);

            // データを取得
            $ri = $this->app->dbAdapter->query($sql)->execute(null);
            $datas = ResultInterfaceToArray($ri);

            $templateId = 'CKI24174_13'; // 精算日計
        }
        // 貸倒債権一覧
        else if ($data['reportList'] == 14) {
            // ファイル名
            $fileName = sprintf('KashidaoreSaiken_%s_%s_%s.csv', date("YmdHis"), $fileArg1, $fileArg2);

            // SQLを取得
            $sql = $this->getKashidaoreSaikenIchiranQuery();
            /* 検索条件を付加 */
            // 日次 OR 月次
            if ($data['outputTarget'] == 0) {
                // 対象期間FROM～TO
                $wProcessingDate = BaseGeneralUtils::makeWhereDate(
                'ProcessingDate',
                BaseGeneralUtils::convertWideToNarrow($data['targetPeriodFrom']),
                BaseGeneralUtils::convertWideToNarrow($data['targetPeriodTo'])
                );
                if ($wProcessingDate != '') {
                    $wheres .= " AND " . $wProcessingDate;
                }
            } else {
                // 対象月
                $wheres .= " AND AccountDate = '" . date('Y-m-01', strtotime($data['targetMonth'])) . "'";
            }

            // OEM全ての場合
            if ($data['selectCondition'] == 1) {
                $wheres .= " AND IFNULL(OemId, 0) <> 0";
            }
            // CB直販全ての場合
            else if ($data['selectCondition'] == 2) {
                $wheres .= " AND IFNULL(OemId, 0) = 0";
            }
            // OEM指定の場合
            else if ($data['selectCondition'] == 3) {
                $wheres .= " AND IFNULL(OemId, 0) = " . $data['oem'];
            }
            // 加盟店指定の場合
            else if ($data['selectCondition'] == 4) {
                $wheres .= " AND EnterpriseId = " . $data['enterpriseId'];
            }

            // 検索条件をSQLに付加
            $sql = sprintf($sql, $wheres, $wheres);

            // データを取得
            $ri = $this->app->dbAdapter->query($sql)->execute(null);
            $datas = ResultInterfaceToArray($ri);

            $templateId = 'CKI24174_14'; // 貸倒債権一覧
        }
        // 過剰金一覧
        else if ($data['reportList'] == 15) {
            // ファイル名
            $fileName = sprintf('Kajokin_%s_%s_%s.csv', date("YmdHis"), $fileArg1, $fileArg2);

            // SQLを取得
            $sql = $this->getKajokinIchiranQuery();
            /* 検索条件を付加 */
            // 日次 OR 月次
            if ($data['outputTarget'] == 0) {
                $wheres .= " AND DailyMonthlyFlg = 0 ";      // 日次
                // 対象期間FROM～TO
                $wProcessingDate = BaseGeneralUtils::makeWhereDate(
                'ProcessingDate',
                BaseGeneralUtils::convertWideToNarrow($data['targetPeriodFrom']),
                BaseGeneralUtils::convertWideToNarrow($data['targetPeriodTo'])
                );
                if ($wProcessingDate != '') {
                    $wheres .= " AND " . $wProcessingDate;
                }
            } else {
                $wheres .= " AND DailyMonthlyFlg = 1";      // 月次
                // 対象月
                $wheres .= " AND AccountDate = '" . date('Y-m-01', strtotime($data['targetMonth'])) . "'";
            }

            // OEM全ての場合
            if ($data['selectCondition'] == 1) {
                $wheres .= " AND IFNULL(OemId, 0) <> 0";
            }
            // CB直販全ての場合
            else if ($data['selectCondition'] == 2) {
                $wheres .= " AND IFNULL(OemId, 0) = 0";
            }
            // OEM指定の場合
            else if ($data['selectCondition'] == 3) {
                $wheres .= " AND IFNULL(OemId, 0) = " . $data['oem'];
            }
            // 加盟店指定の場合
            else if ($data['selectCondition'] == 4) {
                $wheres .= " AND EnterpriseId = " . $data['enterpriseId'];
            }

            // 検索条件をSQLに付加
            $sql = sprintf($sql, $wheres, $wheres);

            // データを取得
            $ri = $this->app->dbAdapter->query($sql)->execute(null);
            $datas = ResultInterfaceToArray($ri);

            $templateId = 'CKI24174_15'; // 過剰金一覧
        }
        // 再発行手数料明細
        else if ($data['reportList'] == 16) {
            // ファイル名
            $fileName = sprintf('SaihakkotesuryoMeisai_%s_%s_%s.csv', date("YmdHis"), $fileArg1, $fileArg2);

            // SQLを取得
            $sql = $this->getSaihakkotesuryoMeisaiQuery();
            /* 検索条件を付加 */
            // 日次 OR 月次
            if ($data['outputTarget'] == 0) {
                // 対象期間FROM～TO
                $wProcessingDate = BaseGeneralUtils::makeWhereDate(
                'ProcessingDate',
                BaseGeneralUtils::convertWideToNarrow($data['targetPeriodFrom']),
                BaseGeneralUtils::convertWideToNarrow($data['targetPeriodTo'])
                );
                if ($wProcessingDate != '') {
                    $wheres .= " AND " . $wProcessingDate;
                }
            } else {
                // 対象月
                $wheres .= " AND AccountDate = '" . date('Y-m-01', strtotime($data['targetMonth'])) . "'";
            }

            // OEM全ての場合
            if ($data['selectCondition'] == 1) {
                $wheres .= " AND IFNULL(OemId, 0) <> 0";
            }
            // CB直販全ての場合
            else if ($data['selectCondition'] == 2) {
                $wheres .= " AND IFNULL(OemId, 0) = 0";
            }
            // OEM指定の場合
            else if ($data['selectCondition'] == 3) {
                $wheres .= " AND IFNULL(OemId, 0) = " . $data['oem'];
            }
            // 加盟店指定の場合
            else if ($data['selectCondition'] == 4) {
                $wheres .= " AND EnterpriseId = " . $data['enterpriseId'];
            }

            // 検索条件をSQLに付加
            $sql = sprintf($sql, $wheres, $wheres);

            // データを取得
            $ri = $this->app->dbAdapter->query($sql)->execute(null);
            $datas = ResultInterfaceToArray($ri);

            $templateId = 'CKI24174_16'; // 再発行手数料明細
        }
        // 無保証立替金戻し明細
        else if ($data['reportList'] == 17) {
            // ファイル名
            $fileName = sprintf('TatekaeModoshiMeisai_%s_%s_%s.csv', date("YmdHis"), $fileArg1, $fileArg2);

            // SQLを取得
            $sql = $this->getMuhoshoTatekaekinmodoshiMeisaiQuery();
            /* 検索条件を付加 */
            // 日次 OR 月次
            if ($data['outputTarget'] == 0) {
                $wheres .= " AND DailyMonthlyFlg = 0 ";      // 日次
                // 対象期間FROM～TO
                $wProcessingDate = BaseGeneralUtils::makeWhereDate(
                'ProcessingDate',
                BaseGeneralUtils::convertWideToNarrow($data['targetPeriodFrom']),
                BaseGeneralUtils::convertWideToNarrow($data['targetPeriodTo'])
                );
                if ($wProcessingDate != '') {
                    $wheres .= " AND " . $wProcessingDate;
                }
            } else {
                $wheres .= " AND DailyMonthlyFlg = 1";      // 月次
                // 対象月
                $wheres .= " AND AccountDate = '" . date('Y-m-01', strtotime($data['targetMonth'])) . "'";
            }

            // OEM全ての場合
            if ($data['selectCondition'] == 1) {
                $wheres .= " AND IFNULL(OemId, 0) <> 0";
            }
            // CB直販全ての場合
            else if ($data['selectCondition'] == 2) {
                $wheres .= " AND IFNULL(OemId, 0) = 0";
            }
            // OEM指定の場合
            else if ($data['selectCondition'] == 3) {
                $wheres .= " AND IFNULL(OemId, 0) = " . $data['oem'];
            }
            // 加盟店指定の場合
            else if ($data['selectCondition'] == 4) {
                $wheres .= " AND EnterpriseId = " . $data['enterpriseId'];
            }

            // 検索条件をSQLに付加
            $sql = sprintf($sql, $wheres, $wheres);

            // データを取得
            $ri = $this->app->dbAdapter->query($sql)->execute(null);
            $datas = ResultInterfaceToArray($ri);

            $templateId = 'CKI24174_17'; // 無保証立替金戻し明細
        }
        // OEM移管明細
        else if ($data['reportList'] == 18) {
            // ファイル名
            $fileName = sprintf('OemIkanMeisai_%s_%s_%s.csv', date("YmdHis"), $fileArg1, $fileArg2);

            // SQLを取得
            $sql = $this->getOEMIkanMeisaiQuery();
            /* 検索条件を付加 */
            // 日次 OR 月次
            if ($data['outputTarget'] == 0) {
                // 対象期間FROM～TO
                $wProcessingDate = BaseGeneralUtils::makeWhereDate(
                'ProcessingDate',
                BaseGeneralUtils::convertWideToNarrow($data['targetPeriodFrom']),
                BaseGeneralUtils::convertWideToNarrow($data['targetPeriodTo'])
                );
                if ($wProcessingDate != '') {
                    $wheres .= " AND " . $wProcessingDate;
                }
            } else {
                // 対象月
                $wheres .= " AND AccountDate = '" . date('Y-m-01', strtotime($data['targetMonth'])) . "'";
            }

            // OEM指定の場合
            if ($data['selectCondition'] == 3) {
                $wheres .= " AND IFNULL(OemId, 0) = " . $data['oem'];
            }
            // 加盟店指定の場合
            else if ($data['selectCondition'] == 4) {
                $wheres .= " AND EnterpriseId = " . $data['enterpriseId'];
            }

            // 検索条件をSQLに付加
            $sql = sprintf($sql, $wheres, $wheres);

            // データを取得
            $ri = $this->app->dbAdapter->query($sql)->execute(null);
            $datas = ResultInterfaceToArray($ri);

            $templateId = 'CKI24174_18'; // OEM移管明細
        }
        // 調整金一覧（日次のみ）
        else if ($data['reportList'] == 19) {
            // ファイル名
            $fileName = sprintf('Choseikin_%s_%s_%s.csv', date("YmdHis"), $fileArg1, $fileArg2);

            // SQLを取得
            $sql = $this->getChoseikinIchiranQuery();
            /* 検索条件を付加 */
            $wheres .= " AND DailyMonthlyFlg = 0 ";      // 日次
            // 対象期間FROM～TO
            $wProcessingDate = BaseGeneralUtils::makeWhereDate(
            'ProcessingDate',
            BaseGeneralUtils::convertWideToNarrow($data['targetPeriodFrom']),
            BaseGeneralUtils::convertWideToNarrow($data['targetPeriodTo'])
            );
            if ($wProcessingDate != '') {
                $wheres .= " AND " . $wProcessingDate;
            }

            // OEM全ての場合
            if ($data['selectCondition'] == 1) {
                $wheres .= " AND IFNULL(OemId, 0) <> 0";
            }
            // CB直販全ての場合
            else if ($data['selectCondition'] == 2) {
                $wheres .= " AND IFNULL(OemId, 0) = 0";
            }
            // OEM指定の場合
            else if ($data['selectCondition'] == 3) {
                $wheres .= " AND IFNULL(OemId, 0) = " . $data['oem'];
            }
            // 加盟店指定の場合
            else if ($data['selectCondition'] == 4) {
                $wheres .= " AND EnterpriseId = " . $data['enterpriseId'];
            }

            // 検索条件をSQLに付加
            $sql = sprintf($sql, $wheres, $wheres);

            // データを取得
            $ri = $this->app->dbAdapter->query($sql)->execute(null);
            $datas = ResultInterfaceToArray($ri);

            $templateId = 'CKI24174_19'; // 調整金一覧
        }
        // 入金先トータル
        else if ($data['reportList'] == 20) {
            // ファイル名
            $fileName = sprintf('Nyukinsaki_%s.csv', date("YmdHis"));

            // SQLを取得
            $sql = $this->getNyukinTotalQuery();
            /* 検索条件を付加 */
            $wheres .= " AND DailyMonthlyFlg = 0 ";      // 日次
            // 対象期間FROM～TO
            $wProcessingDate = BaseGeneralUtils::makeWhereDate(
            'ReceiptDate',
            BaseGeneralUtils::convertWideToNarrow($data['targetPeriodFrom']),
            BaseGeneralUtils::convertWideToNarrow($data['targetPeriodTo'])
            );
            if ($wProcessingDate != '') {
                $wheres .= " AND " . $wProcessingDate;
            }

            // 検索条件をSQLに付加
            $sql = sprintf($sql, $wheres, $wheres);

            // データを取得
            $ri = $this->app->dbAdapter->query($sql)->execute(null);
            $datas = ResultInterfaceToArray($ri);

            $templateId = 'CKI24174_20'; // 入金先トータル
        }

        $templateClass = 0;
        $seq = 0;
        $templatePattern = 0;

        $logicTemplate = new LogicTemplate($this->app->dbAdapter);
        $response = $logicTemplate->convertArraytoResponse( $datas, $fileName, $templateId, $templateClass, $seq, $templatePattern, $this->getResponse() );

        if( $response == false ) {
            throw new \Exception( $logicTemplate->getErrorMessage() );
        }

        return $response;
    }

    /**
     * 入力検証処理
     *
     * @access protected
     * @param array $data
     * @return array
     */
    protected function validate($data = array()) {

        $errors = array();

        // 出力対象：日次の場合、以下処理を行う。
        if ($data['outputTarget'] == 0) {
            // TargetPeriodFrom:対象期間FROM
            $key = "targetPeriodFrom";
            if (!isset($errors[$key]) && (strlen($data[$key]) < 0)) {
                $errors[$key] = array("'対象期間FROM'が未入力です");
            }
            if (!isset($errors[$key]) && !IsValidFormatDate($data[$key])) {
                $errors[$key] = array("'対象期間FROM'の指定が不正です");
            }

            // TargetPeriodTo:対象期間TO
            $key = "targetPeriodTo";
            if (!isset($errors[$key]) && (strlen($data[$key]) < 0)) {
                $errors[$key] = array("'対象期間TO'が未入力です");
            }
            if (!isset($errors[$key]) && !IsValidFormatDate($data[$key])) {
                $errors[$key] = array("'対象期間TO'の指定が不正です");
            }

            // TargetPeriodFromTo:対象期間
            $key = "targetPeriodFromTo";
            $dt = date('Y-m-d', strtotime($data['targetPeriodTo']));
            $df = date('Y-m-d', strtotime($data['targetPeriodFrom']));
            if (!isset($errors[$key]) && ($dt < $df)) {
                $errors[$key] = array("'対象期間FromTo'を正しく入力してください");
            }
        }

        // 出力対象：月次の場合、以下処理を行う。
        if ($data['outputTarget'] == 1) {
            // TargetMonth:対象年月
            $key = "targetMonth";
            $d = date('Y-m-01', strtotime($data[$key]));
            if (!isset($errors[$key]) && (strlen($data[$key]) < 0)) {
                $errors[$key] = array("'対象年月'が未入力です");
            }
            if (!isset($errors[$key]) && !IsValidFormatDate($d)) {
                $errors[$key] = array("'対象年月'の指定が不正です");
            }
        }

        // 選択条件：OEM指定の場合、以下処理を行う。
        if ($data['selectCondition'] == 3) {
            // OemId:OEMID
            $key = "oem";
            if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
                $errors[$key] = array("'OEMID'を選択してください");
            }
            if (!isset($errors[$key]) && !(is_numeric($data[$key]))) {
                $errors[$key] = array("'OEMID'の指定が不正です");
            }
            if (!isset($errors[$key]) && !((int)$data[$key] > 0)) {
                $errors[$key] = array("'OEMID'の指定が不正です");
            }
        }


        // 選択条件：加盟店指定の場合、以下処理を行う。
        if ($data['selectCondition'] == 4) {
            // EnterpriseId:加盟店ID
            $key = "enterpriseId";
            if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
             $errors[$key] = array("'加盟店ID'が未入力です");
            }
            if (!isset($errors[$key]) && !(is_numeric($data[$key]))) {
                $errors[$key] = array("'加盟店ID'の指定が不正です");
            }
            if (!isset($errors[$key]) && !((int)$data[$key] > 0)) {
                $errors[$key] = array("'加盟店ID'の指定が不正です");
            }
        }

        return $errors;
    }

    /**
     * 直営日次統計表取得ベースSQL
     *
     * @return string
     */
    protected function getCBNichijiTokeiBaseQuery() {
        return <<<EOQ
SELECT  Seq
    ,   DailyMonthlyFlg
    ,   ProcessingDate
    ,   AccountDate
    ,   EnterpriseId
    ,   EnterpriseNameKj
    /* 当日売上 */
    ,   DB__AccountsReceivableBalance
    ,   D_ChargeCount
    ,   D_ChargeAmount
    ,   D_CancelCount
    ,   D_CancelAmount
    ,   D_SettlementBackCount
    ,   D_SettlementBackAmount
    ,   D_OemTransferCount
    ,   D_OemTransferAmount
    ,   D_ReClaimFeeCount
    ,   D_ReClaimFeeAmount
    ,   D_DamageCount
    ,   D_DamageAmount
    ,   D_ReceiptCount
    ,   D_ReceiptAmount
    ,   D_RepayCount
    ,   D_RepayAmount
    ,   D_BadDebtCount
    ,   D_BadDebtAmount
    ,   D_OtherPaymentCount
    ,   D_OtherPaymentAmount
    ,   D_AccountsReceivableBalance
    ,   D_SettlementFee
    ,   D_ClaimFee
    ,   D_ClaimFeeTax
    ,   D_MonthlyFee
    ,   D_MonthlyFeeTax
    ,   D_IncludeMonthlyFee
    ,   D_IncludeMonthlyFeeTax
    ,   D_ApiMonthlyFee
    ,   D_ApiMonthlyFeeTax
    ,   D_CreditNoticeMonthlyFee
    ,   D_CreditNoticeMonthlyFeeTax
    ,   D_NCreditNoticeMonthlyFee
    ,   D_NCreditNoticeMonthlyFeeTax
    ,   D_AddClaimFee
    ,   D_AddClaimFeeTax
    ,   D_DamageInterestAmount
    ,   D_CanSettlementFee
    ,   D_CanClaimFee
    ,   D_CanClaimFeeTax
    ,   D_SettlementFeeTotal
    ,   D_ClaimFeeTotal
    ,   D_ClaimFeeTaxTotal
    ,   D_MonthlyFeeTotal
    ,   D_MonthlyFeeTaxTotal
    ,   D_IncludeMonthlyFeeTotal
    ,   D_IncludeMonthlyFeeTaxTotal
    ,   D_ApiMonthlyFeeTotal
    ,   D_ApiMonthlyFeeTaxTotal
    ,   D_CreditNoticeMonthlyFeeTotal
    ,   D_CreditNoticeMonthlyFeeTaxTotal
    ,   D_NCreditNoticeMonthlyFeeTotal
    ,   D_NCreditNoticeMonthlyFeeTaxTotal
    ,   D_AddClaimFeeTotal
    ,   D_AddClaimFeeTaxTotal
    ,   D_DamageInterestAmountTotal
    ,   D_AllTotal
    ,   D_SettlementFeeOther
    ,   D_ClaimFeeOther
    ,   D_ClaimFeeTaxOther
    ,   D_MonthlyFeeOther
    ,   D_MonthlyFeeTaxOther
    ,   D_IncludeMonthlyFeeOther
    ,   D_IncludeMonthlyFeeTaxOther
    ,   D_ApiMonthlyFeeOther
    ,   D_ApiMonthlyFeeTaxOther
    ,   D_CreditNoticeMonthlyFeeOther
    ,   D_CreditNoticeMonthlyFeeTaxOther
    ,   D_NCreditNoticeMonthlyFeeOther
    ,   D_NCreditNoticeMonthlyFeeTaxOther
    ,   D_AddClaimFeeOther
    ,   D_AddClaimFeeTaxOther
    ,   D_DamageInterestAmountOther
    ,   D_SettlementFeeDiff
    ,   D_ClaimFeeDiff
    ,   D_ClaimFeeTaxDiff
    ,   D_MonthlyFeeDiff
    ,   D_MonthlyFeeTaxDiff
    ,   D_IncludeMonthlyFeeDiff
    ,   D_IncludeMonthlyFeeTaxDiff
    ,   D_ApiMonthlyFeeDiff
    ,   D_ApiMonthlyFeeTaxDiff
    ,   D_CreditNoticeMonthlyFeeDiff
    ,   D_CreditNoticeMonthlyFeeTaxDiff
    ,   D_NCreditNoticeMonthlyFeeDiff
    ,   D_NCreditNoticeMonthlyFeeTaxDiff
    ,   D_ReserveMonthlyFeeDiff
    ,   D_ReserveMonthlyFeeTaxDiff
    ,   D_AddClaimFeeDiff
    ,   D_AddClaimFeeTaxDiff
    ,   D_DamageInterestAmountDiff
    /* 当月売上 */
    ,   MB__AccountsReceivableBalance
    ,   M_ChargeCount
    ,   M_ChargeAmount
    ,   M_CancelCount
    ,   M_CancelAmount
    ,   M_SettlementBackCount
    ,   M_SettlementBackAmount
    ,   M_TransferCount
    ,   M_TransferAmount
    ,   M_ReClaimFeeCount
    ,   M_ReClaimFeeAmount
    ,   M_DamageCount
    ,   M_DamageAmount
    ,   M_ReceiptCount
    ,   M_ReceiptAmount
    ,   M_RepayCount
    ,   M_RepayAmount
    ,   M_BadDebtCount
    ,   M_BadDebtAmount
    ,   M_OtherPaymentCount
    ,   M_OtherPaymentAmount
    ,   M_AccountsReceivableBalance
    ,   M_SuspensePaymentsAmount
    ,   M_AccountsReceivableBalanceDiff
    ,   M_SettlementFee
    ,   M_ClaimFee
    ,   M_ClaimFeeTax
    ,   M_MonthlyFee
    ,   M_MonthlyFeeTax
    ,   M_IncludeMonthlyFee
    ,   M_IncludeMonthlyFeeTax
    ,   M_ApiMonthlyFee
    ,   M_ApiMonthlyFeeTax
    ,   M_CreditNoticeMonthlyFee
    ,   M_CreditNoticeMonthlyFeeTax
    ,   M_NCreditNoticeMonthlyFee
    ,   M_NCreditNoticeMonthlyFeeTax
    ,   M_AddClaimFee
    ,   M_AddClaimFeeTax
    ,   M_DamageInterestAmount
    ,   M_CanSettlementFee
    ,   M_CanClaimFee
    ,   M_CanClaimFeeTax
    ,   M_SettlementFeeTotal
    ,   M_ClaimFeeTotal
    ,   M_ClaimFeeTaxTotal
    ,   M_MonthlyFeeTotal
    ,   M_MonthlyFeeTaxTotal
    ,   M_IncludeMonthlyFeeTotal
    ,   M_IncludeMonthlyFeeTaxTotal
    ,   M_ApiMonthlyFeeTotal
    ,   M_ApiMonthlyFeeTaxTotal
    ,   M_CreditNoticeMonthlyFeeTotal
    ,   M_CreditNoticeMonthlyFeeTaxTotal
    ,   M_NCreditNoticeMonthlyFeeTotal
    ,   M_NCreditNoticeMonthlyFeeTaxTotal
    ,   M_AddClaimFeeTotal
    ,   M_AddClaimFeeTaxTotal
    ,   M_DamageInterestAmountTotal
    ,   M_AllTotal
    ,   M_SettlementFeeOther
    ,   M_ClaimFeeOther
    ,   M_ClaimFeeTaxOther
    ,   M_MonthlyFeeOther
    ,   M_MonthlyFeeTaxOther
    ,   M_IncludeMonthlyFeeOther
    ,   M_IncludeMonthlyFeeTaxOther
    ,   M_ApiMonthlyFeeOther
    ,   M_ApiMonthlyFeeTaxOther
    ,   M_CreditNoticeMonthlyFeeOther
    ,   M_CreditNoticeMonthlyFeeTaxOther
    ,   M_NCreditNoticeMonthlyFeeOther
    ,   M_NCreditNoticeMonthlyFeeTaxOther
    ,   M_AddClaimFeeOther
    ,   M_AddClaimFeeTaxOther
    ,   M_DamageInterestAmountOther
    ,   M_SettlementFeeDiff
    ,   M_ClaimFeeDiff
    ,   M_ClaimFeeTaxDiff
    ,   M_MonthlyFeeDiff
    ,   M_MonthlyFeeTaxDiff
    ,   M_IncludeMonthlyFeeDiff
    ,   M_IncludeMonthlyFeeTaxDiff
    ,   M_ApiMonthlyFeeDiff
    ,   M_ApiMonthlyFeeTaxDiff
    ,   M_CreditNoticeMonthlyFeeDiff
    ,   M_CreditNoticeMonthlyFeeTaxDiff
    ,   M_NCreditNoticeMonthlyFeeDiff
    ,   M_NCreditNoticeMonthlyFeeTaxDiff
    ,   M_ReserveMonthlyFeeDiff
    ,   M_ReserveMonthlyFeeTaxDiff
    ,   M_AddClaimFeeDiff
    ,   M_AddClaimFeeTaxDiff
    ,   M_DamageInterestAmountDiff
FROM    AT_DailyStatisticsTable
WHERE   1 = 1
%s
ORDER BY
        IFNULL(EnterpriseId, 999999999)
EOQ;
    }

    /**
     * (サマリー限定)直営日次統計表取得ベースSQL
     *
     * @return string
     */
    protected function getCBNichijiTokeiBaseQuery_Summary() {
        return <<<EOQ
SELECT  '' AS Seq
    ,   DailyMonthlyFlg
    ,   MIN(ProcessingDate) AS ProcessingDate
    ,   AccountDate
    ,   NULL AS EnterpriseId
    ,   '総合計' AS EnterpriseNameKj
    /* 当日売上 */
    ,   SUM(DB__AccountsReceivableBalance) AS DB__AccountsReceivableBalance
    ,   SUM(D_ChargeCount) AS D_ChargeCount
    ,   SUM(D_ChargeAmount) AS D_ChargeAmount
    ,   SUM(D_CancelCount) AS D_CancelCount
    ,   SUM(D_CancelAmount) AS D_CancelAmount
    ,   SUM(D_SettlementBackCount) AS D_SettlementBackCount
    ,   SUM(D_SettlementBackAmount) AS D_SettlementBackAmount
    ,   SUM(D_OemTransferCount) AS D_OemTransferCount
    ,   SUM(D_OemTransferAmount) AS D_OemTransferAmount
    ,   SUM(D_ReClaimFeeCount) AS D_ReClaimFeeCount
    ,   SUM(D_ReClaimFeeAmount) AS D_ReClaimFeeAmount
    ,   SUM(D_DamageCount) AS D_DamageCount
    ,   SUM(D_DamageAmount) AS D_DamageAmount
    ,   SUM(D_ReceiptCount) AS D_ReceiptCount
    ,   SUM(D_ReceiptAmount) AS D_ReceiptAmount
    ,   SUM(D_RepayCount) AS D_RepayCount
    ,   SUM(D_RepayAmount) AS D_RepayAmount
    ,   SUM(D_BadDebtCount) AS D_BadDebtCount
    ,   SUM(D_BadDebtAmount) AS D_BadDebtAmount
    ,   SUM(D_OtherPaymentCount) AS D_OtherPaymentCount
    ,   SUM(D_OtherPaymentAmount) AS D_OtherPaymentAmount
    ,   SUM(D_AccountsReceivableBalance) AS D_AccountsReceivableBalance
    ,   SUM(D_SettlementFee) AS D_SettlementFee
    ,   SUM(D_ClaimFee) AS D_ClaimFee
    ,   SUM(D_ClaimFeeTax) AS D_ClaimFeeTax
    ,   SUM(D_MonthlyFee) AS D_MonthlyFee
    ,   SUM(D_MonthlyFeeTax) AS D_MonthlyFeeTax
    ,   SUM(D_IncludeMonthlyFee) AS D_IncludeMonthlyFee
    ,   SUM(D_IncludeMonthlyFeeTax) AS D_IncludeMonthlyFeeTax
    ,   SUM(D_ApiMonthlyFee) AS D_ApiMonthlyFee
    ,   SUM(D_ApiMonthlyFeeTax) AS D_ApiMonthlyFeeTax
    ,   SUM(D_CreditNoticeMonthlyFee) AS D_CreditNoticeMonthlyFee
    ,   SUM(D_CreditNoticeMonthlyFeeTax) AS D_CreditNoticeMonthlyFeeTax
    ,   SUM(D_NCreditNoticeMonthlyFee) AS D_NCreditNoticeMonthlyFee
    ,   SUM(D_NCreditNoticeMonthlyFeeTax) AS D_NCreditNoticeMonthlyFeeTax
    ,   SUM(D_AddClaimFee) AS D_AddClaimFee
    ,   SUM(D_AddClaimFeeTax) AS D_AddClaimFeeTax
    ,   SUM(D_DamageInterestAmount) AS D_DamageInterestAmount
    ,   SUM(D_CanSettlementFee) AS D_CanSettlementFee
    ,   SUM(D_CanClaimFee) AS D_CanClaimFee
    ,   SUM(D_CanClaimFeeTax) AS D_CanClaimFeeTax
    ,   SUM(D_SettlementFeeTotal) AS D_SettlementFeeTotal
    ,   SUM(D_ClaimFeeTotal) AS D_ClaimFeeTotal
    ,   SUM(D_ClaimFeeTaxTotal) AS D_ClaimFeeTaxTotal
    ,   SUM(D_MonthlyFeeTotal) AS D_MonthlyFeeTotal
    ,   SUM(D_MonthlyFeeTaxTotal) AS D_MonthlyFeeTaxTotal
    ,   SUM(D_IncludeMonthlyFeeTotal) AS D_IncludeMonthlyFeeTotal
    ,   SUM(D_IncludeMonthlyFeeTaxTotal) AS D_IncludeMonthlyFeeTaxTotal
    ,   SUM(D_ApiMonthlyFeeTotal) AS D_ApiMonthlyFeeTotal
    ,   SUM(D_ApiMonthlyFeeTaxTotal) AS D_ApiMonthlyFeeTaxTotal
    ,   SUM(D_CreditNoticeMonthlyFeeTotal) AS D_CreditNoticeMonthlyFeeTotal
    ,   SUM(D_CreditNoticeMonthlyFeeTaxTotal) AS D_CreditNoticeMonthlyFeeTaxTotal
    ,   SUM(D_NCreditNoticeMonthlyFeeTotal) AS D_NCreditNoticeMonthlyFeeTotal
    ,   SUM(D_NCreditNoticeMonthlyFeeTaxTotal) AS D_NCreditNoticeMonthlyFeeTaxTotal
    ,   SUM(D_AddClaimFeeTotal) AS D_AddClaimFeeTotal
    ,   SUM(D_AddClaimFeeTaxTotal) AS D_AddClaimFeeTaxTotal
    ,   SUM(D_DamageInterestAmountTotal) AS D_DamageInterestAmountTotal
    ,   SUM(D_AllTotal) AS D_AllTotal
    ,   SUM(D_SettlementFeeOther) AS D_SettlementFeeOther
    ,   SUM(D_ClaimFeeOther) AS D_ClaimFeeOther
    ,   SUM(D_ClaimFeeTaxOther) AS D_ClaimFeeTaxOther
    ,   SUM(D_MonthlyFeeOther) AS D_MonthlyFeeOther
    ,   SUM(D_MonthlyFeeTaxOther) AS D_MonthlyFeeTaxOther
    ,   SUM(D_IncludeMonthlyFeeOther) AS D_IncludeMonthlyFeeOther
    ,   SUM(D_IncludeMonthlyFeeTaxOther) AS D_IncludeMonthlyFeeTaxOther
    ,   SUM(D_ApiMonthlyFeeOther) AS D_ApiMonthlyFeeOther
    ,   SUM(D_ApiMonthlyFeeTaxOther) AS D_ApiMonthlyFeeTaxOther
    ,   SUM(D_CreditNoticeMonthlyFeeOther) AS D_CreditNoticeMonthlyFeeOther
    ,   SUM(D_CreditNoticeMonthlyFeeTaxOther) AS D_CreditNoticeMonthlyFeeTaxOther
    ,   SUM(D_NCreditNoticeMonthlyFeeOther) AS D_NCreditNoticeMonthlyFeeOther
    ,   SUM(D_NCreditNoticeMonthlyFeeTaxOther) AS D_NCreditNoticeMonthlyFeeTaxOther
    ,   SUM(D_AddClaimFeeOther) AS D_AddClaimFeeOther
    ,   SUM(D_AddClaimFeeTaxOther) AS D_AddClaimFeeTaxOther
    ,   SUM(D_DamageInterestAmountOther) AS D_DamageInterestAmountOther
    ,   SUM(D_SettlementFeeDiff) AS D_SettlementFeeDiff
    ,   SUM(D_ClaimFeeDiff) AS D_ClaimFeeDiff
    ,   SUM(D_ClaimFeeTaxDiff) AS D_ClaimFeeTaxDiff
    ,   SUM(D_MonthlyFeeDiff) AS D_MonthlyFeeDiff
    ,   SUM(D_MonthlyFeeTaxDiff) AS D_MonthlyFeeTaxDiff
    ,   SUM(D_IncludeMonthlyFeeDiff) AS D_IncludeMonthlyFeeDiff
    ,   SUM(D_IncludeMonthlyFeeTaxDiff) AS D_IncludeMonthlyFeeTaxDiff
    ,   SUM(D_ApiMonthlyFeeDiff) AS D_ApiMonthlyFeeDiff
    ,   SUM(D_ApiMonthlyFeeTaxDiff) AS D_ApiMonthlyFeeTaxDiff
    ,   SUM(D_CreditNoticeMonthlyFeeDiff) AS D_CreditNoticeMonthlyFeeDiff
    ,   SUM(D_CreditNoticeMonthlyFeeTaxDiff) AS D_CreditNoticeMonthlyFeeTaxDiff
    ,   SUM(D_NCreditNoticeMonthlyFeeDiff) AS D_NCreditNoticeMonthlyFeeDiff
    ,   SUM(D_NCreditNoticeMonthlyFeeTaxDiff) AS D_NCreditNoticeMonthlyFeeTaxDiff
    ,   SUM(D_ReserveMonthlyFeeDiff) AS D_ReserveMonthlyFeeDiff
    ,   SUM(D_ReserveMonthlyFeeTaxDiff) AS D_ReserveMonthlyFeeTaxDiff
    ,   SUM(D_AddClaimFeeDiff) AS D_AddClaimFeeDiff
    ,   SUM(D_AddClaimFeeTaxDiff) AS D_AddClaimFeeTaxDiff
    ,   SUM(D_DamageInterestAmountDiff) AS D_DamageInterestAmountDiff
    /* 当月売上 */
    ,   SUM(MB__AccountsReceivableBalance) AS MB__AccountsReceivableBalance
    ,   SUM(M_ChargeCount) AS M_ChargeCount
    ,   SUM(M_ChargeAmount) AS M_ChargeAmount
    ,   SUM(M_CancelCount) AS M_CancelCount
    ,   SUM(M_CancelAmount) AS M_CancelAmount
    ,   SUM(M_SettlementBackCount) AS M_SettlementBackCount
    ,   SUM(M_SettlementBackAmount) AS M_SettlementBackAmount
    ,   SUM(M_TransferCount) AS M_TransferCount
    ,   SUM(M_TransferAmount) AS M_TransferAmount
    ,   SUM(M_ReClaimFeeCount) AS M_ReClaimFeeCount
    ,   SUM(M_ReClaimFeeAmount) AS M_ReClaimFeeAmount
    ,   SUM(M_DamageCount) AS M_DamageCount
    ,   SUM(M_DamageAmount) AS M_DamageAmount
    ,   SUM(M_ReceiptCount) AS M_ReceiptCount
    ,   SUM(M_ReceiptAmount) AS M_ReceiptAmount
    ,   SUM(M_RepayCount) AS M_RepayCount
    ,   SUM(M_RepayAmount) AS M_RepayAmount
    ,   SUM(M_BadDebtCount) AS M_BadDebtCount
    ,   SUM(M_BadDebtAmount) AS M_BadDebtAmount
    ,   SUM(M_OtherPaymentCount) AS M_OtherPaymentCount
    ,   SUM(M_OtherPaymentAmount) AS M_OtherPaymentAmount
    ,   SUM(M_AccountsReceivableBalance) AS M_AccountsReceivableBalance
    ,   SUM(M_SuspensePaymentsAmount) AS M_SuspensePaymentsAmount
    ,   SUM(M_AccountsReceivableBalanceDiff) AS M_AccountsReceivableBalanceDiff
    ,   SUM(M_SettlementFee) AS M_SettlementFee
    ,   SUM(M_ClaimFee) AS M_ClaimFee
    ,   SUM(M_ClaimFeeTax) AS M_ClaimFeeTax
    ,   SUM(M_MonthlyFee) AS M_MonthlyFee
    ,   SUM(M_MonthlyFeeTax) AS M_MonthlyFeeTax
    ,   SUM(M_IncludeMonthlyFee) AS M_IncludeMonthlyFee
    ,   SUM(M_IncludeMonthlyFeeTax) AS M_IncludeMonthlyFeeTax
    ,   SUM(M_ApiMonthlyFee) AS M_ApiMonthlyFee
    ,   SUM(M_ApiMonthlyFeeTax) AS M_ApiMonthlyFeeTax
    ,   SUM(M_CreditNoticeMonthlyFee) AS M_CreditNoticeMonthlyFee
    ,   SUM(M_CreditNoticeMonthlyFeeTax) AS M_CreditNoticeMonthlyFeeTax
    ,   SUM(M_NCreditNoticeMonthlyFee) AS M_NCreditNoticeMonthlyFee
    ,   SUM(M_NCreditNoticeMonthlyFeeTax) AS M_NCreditNoticeMonthlyFeeTax
    ,   SUM(M_AddClaimFee) AS M_AddClaimFee
    ,   SUM(M_AddClaimFeeTax) AS M_AddClaimFeeTax
    ,   SUM(M_DamageInterestAmount) AS M_DamageInterestAmount
    ,   SUM(M_CanSettlementFee) AS M_CanSettlementFee
    ,   SUM(M_CanClaimFee) AS M_CanClaimFee
    ,   SUM(M_CanClaimFeeTax) AS M_CanClaimFeeTax
    ,   SUM(M_SettlementFeeTotal) AS M_SettlementFeeTotal
    ,   SUM(M_ClaimFeeTotal) AS M_ClaimFeeTotal
    ,   SUM(M_ClaimFeeTaxTotal) AS M_ClaimFeeTaxTotal
    ,   SUM(M_MonthlyFeeTotal) AS M_MonthlyFeeTotal
    ,   SUM(M_MonthlyFeeTaxTotal) AS M_MonthlyFeeTaxTotal
    ,   SUM(M_IncludeMonthlyFeeTotal) AS M_IncludeMonthlyFeeTotal
    ,   SUM(M_IncludeMonthlyFeeTaxTotal) AS M_IncludeMonthlyFeeTaxTotal
    ,   SUM(M_ApiMonthlyFeeTotal) AS M_ApiMonthlyFeeTotal
    ,   SUM(M_ApiMonthlyFeeTaxTotal) AS M_ApiMonthlyFeeTaxTotal
    ,   SUM(M_CreditNoticeMonthlyFeeTotal) AS M_CreditNoticeMonthlyFeeTotal
    ,   SUM(M_CreditNoticeMonthlyFeeTaxTotal) AS M_CreditNoticeMonthlyFeeTaxTotal
    ,   SUM(M_NCreditNoticeMonthlyFeeTotal) AS M_NCreditNoticeMonthlyFeeTotal
    ,   SUM(M_NCreditNoticeMonthlyFeeTaxTotal) AS M_NCreditNoticeMonthlyFeeTaxTotal
    ,   SUM(M_AddClaimFeeTotal) AS M_AddClaimFeeTotal
    ,   SUM(M_AddClaimFeeTaxTotal) AS M_AddClaimFeeTaxTotal
    ,   SUM(M_DamageInterestAmountTotal) AS M_DamageInterestAmountTotal
    ,   SUM(M_AllTotal) AS M_AllTotal
    ,   SUM(M_SettlementFeeOther) AS M_SettlementFeeOther
    ,   SUM(M_ClaimFeeOther) AS M_ClaimFeeOther
    ,   SUM(M_ClaimFeeTaxOther) AS M_ClaimFeeTaxOther
    ,   SUM(M_MonthlyFeeOther) AS M_MonthlyFeeOther
    ,   SUM(M_MonthlyFeeTaxOther) AS M_MonthlyFeeTaxOther
    ,   SUM(M_IncludeMonthlyFeeOther) AS M_IncludeMonthlyFeeOther
    ,   SUM(M_IncludeMonthlyFeeTaxOther) AS M_IncludeMonthlyFeeTaxOther
    ,   SUM(M_ApiMonthlyFeeOther) AS M_ApiMonthlyFeeOther
    ,   SUM(M_ApiMonthlyFeeTaxOther) AS M_ApiMonthlyFeeTaxOther
    ,   SUM(M_CreditNoticeMonthlyFeeOther) AS M_CreditNoticeMonthlyFeeOther
    ,   SUM(M_CreditNoticeMonthlyFeeTaxOther) AS M_CreditNoticeMonthlyFeeTaxOther
    ,   SUM(M_NCreditNoticeMonthlyFeeOther) AS M_NCreditNoticeMonthlyFeeOther
    ,   SUM(M_NCreditNoticeMonthlyFeeTaxOther) AS M_NCreditNoticeMonthlyFeeTaxOther
    ,   SUM(M_AddClaimFeeOther) AS M_AddClaimFeeOther
    ,   SUM(M_AddClaimFeeTaxOther) AS M_AddClaimFeeTaxOther
    ,   SUM(M_DamageInterestAmountOther) AS M_DamageInterestAmountOther
    ,   SUM(M_SettlementFeeDiff) AS M_SettlementFeeDiff
    ,   SUM(M_ClaimFeeDiff) AS M_ClaimFeeDiff
    ,   SUM(M_ClaimFeeTaxDiff) AS M_ClaimFeeTaxDiff
    ,   SUM(M_MonthlyFeeDiff) AS M_MonthlyFeeDiff
    ,   SUM(M_MonthlyFeeTaxDiff) AS M_MonthlyFeeTaxDiff
    ,   SUM(M_IncludeMonthlyFeeDiff) AS M_IncludeMonthlyFeeDiff
    ,   SUM(M_IncludeMonthlyFeeTaxDiff) AS M_IncludeMonthlyFeeTaxDiff
    ,   SUM(M_ApiMonthlyFeeDiff) AS M_ApiMonthlyFeeDiff
    ,   SUM(M_ApiMonthlyFeeTaxDiff) AS M_ApiMonthlyFeeTaxDiff
    ,   SUM(M_CreditNoticeMonthlyFeeDiff) AS M_CreditNoticeMonthlyFeeDiff
    ,   SUM(M_CreditNoticeMonthlyFeeTaxDiff) AS M_CreditNoticeMonthlyFeeTaxDiff
    ,   SUM(M_NCreditNoticeMonthlyFeeDiff) AS M_NCreditNoticeMonthlyFeeDiff
    ,   SUM(M_NCreditNoticeMonthlyFeeTaxDiff) AS M_NCreditNoticeMonthlyFeeTaxDiff
    ,   SUM(M_ReserveMonthlyFeeDiff) AS M_ReserveMonthlyFeeDiff
    ,   SUM(M_ReserveMonthlyFeeTaxDiff) AS M_ReserveMonthlyFeeTaxDiff
    ,   SUM(M_AddClaimFeeDiff) AS M_AddClaimFeeDiff
    ,   SUM(M_AddClaimFeeTaxDiff) AS M_AddClaimFeeTaxDiff
    ,   SUM(M_DamageInterestAmountDiff) AS M_DamageInterestAmountDiff
FROM    AT_DailyStatisticsTable
        LEFT OUTER JOIN T_Enterprise ON (T_Enterprise.EnterpriseId = AT_DailyStatisticsTable.EnterpriseId)
        LEFT OUTER JOIN M_Code ON (M_Code.CodeId = 160 AND M_Code.KeyCode = IFNULL(T_Enterprise.OemId, 0))
WHERE   1 = 1
AND     (M_Code.Class1 = 0 OR AT_DailyStatisticsTable.EnterpriseId = 99999999)
%s
GROUP BY
        DailyMonthlyFlg
,       AccountDate
EOQ;
    }

    /**
     * OEM日次統計表取得ベースSQL
     *
     * @return string
     */
    protected function getOEMNichijiTokeiBaseQuery() {
        return <<<EOQ
SELECT  Seq
    ,   DailyMonthlyFlg
    ,   ProcessingDate
    ,   AccountDate
    ,   OemId
    ,   OemNameKj
    ,   EnterpriseId
    ,   EnterpriseNameKj
        /* 当日売上 */
    ,   DB__AccountsReceivableBalance
    ,   D_ChargeCount
    ,   D_ChargeAmount
    ,   D_CancelCount
    ,   D_CancelAmount
    ,   D_SettlementBackCount
    ,   D_SettlementBackAmount
    ,   D_OemTransferCount
    ,   D_OemTransferAmount
    ,   D_ReClaimFeeCount
    ,   D_ReClaimFeeAmount
    ,   D_DamageCount
    ,   D_DamageAmount
    ,   D_ReceiptCount
    ,   D_ReceiptAmount
    ,   D_RepayCount
    ,   D_RepayAmount
    ,   D_BadDebtCount
    ,   D_BadDebtAmount
    ,   D_OtherPaymentCount
    ,   D_OtherPaymentAmount
    ,   D_AccountsReceivableBalance
    ,   D_SettlementFee
    ,   D_SettlementFeeTax
    ,   D_ClaimFee
    ,   D_ClaimFeeTax
    ,   D_MonthlyFee
    ,   D_MonthlyFeeTax
    ,   D_OemIncludeMonthlyFee
    ,   D_OemIncludeMonthlyFeeTax
    ,   D_OemApiMonthlyFee
    ,   D_OemApiMonthlyFeeTax
    ,   D_OemCreditNoticeMonthlyFee
    ,   D_OemCreditNoticeMonthlyFeeTax
    ,   D_OemNCreditNoticeMonthlyFee
    ,   D_OemNCreditNoticeMonthlyFeeTax
    ,   D_AddClaimFee
    ,   D_AddClaimFeeTax
    ,   D_DamageInterestAmount
    ,   D_CanSettlementFee
    ,   D_CanSettlementFeeTax
    ,   D_CanClaimFee
    ,   D_CanClaimFeeTax
    ,   D_SettlementFeeTotal
    ,   D_SettlementFeeTaxTotal
    ,   D_ClaimFeeTotal
    ,   D_ClaimFeeTaxTotal
    ,   D_MonthlyFeeTotal
    ,   D_MonthlyFeeTaxTotal
    ,   D_OemIncludeMonthlyFeeTotal
    ,   D_OemIncludeMonthlyFeeTaxTotal
    ,   D_OemApiMonthlyFeeTotal
    ,   D_OemApiMonthlyFeeTaxTotal
    ,   D_OemCreditNoticeMonthlyFeeTotal
    ,   D_OemCreditNoticeMonthlyFeeTaxTotal
    ,   D_OemNCreditNoticeMonthlyFeeTotal
    ,   D_OemNCreditNoticeMonthlyFeeTaxTotal
    ,   D_AddClaimFeeTotal
    ,   D_AddClaimFeeTaxTotal
    ,   D_DamageInterestAmountTotal
    ,   D_AllTotal
    ,   D_SettlementFeeOther
    ,   D_SettlementFeeTaxOther
    ,   D_ClaimFeeOther
    ,   D_ClaimFeeTaxOther
    ,   D_MonthlyFeeOther
    ,   D_MonthlyFeeTaxOther
    ,   D_OemIncludeMonthlyFeeOther
    ,   D_OemIncludeMonthlyFeeTaxOther
    ,   D_OemApiMonthlyFeeOther
    ,   D_OemApiMonthlyFeeTaxOther
    ,   D_OemCreditNoticeMonthlyFeeOther
    ,   D_OemCreditNoticeMonthlyFeeTaxOther
    ,   D_OemNCreditNoticeMonthlyFeeOther
    ,   D_OemNCreditNoticeMonthlyFeeTaxOther
    ,   D_AddClaimFeeOther
    ,   D_AddClaimFeeTaxOther
    ,   D_DamageInterestAmountOther
    ,   D_SettlementFeeDiff
    ,   D_SettlementFeeTaxDiff
    ,   D_ClaimFeeDiff
    ,   D_ClaimFeeTaxDiff
    ,   D_MonthlyFeeDiff
    ,   D_MonthlyFeeTaxDiff
    ,   D_OemIncludeMonthlyFeeDiff
    ,   D_OemIncludeMonthlyFeeTaxDiff
    ,   D_OemApiMonthlyFeeDiff
    ,   D_OemApiMonthlyFeeTaxDiff
    ,   D_OemCreditNoticeMonthlyFeeDiff
    ,   D_OemCreditNoticeMonthlyFeeTaxDiff
    ,   D_OemNCreditNoticeMonthlyFeeDiff
    ,   D_OemNCreditNoticeMonthlyFeeTaxDiff
    ,   D_AddClaimFeeDiff
    ,   D_AddClaimFeeTaxDiff
    ,   D_DamageInterestAmountDiff
        /* 当月売上 */
    ,   MB__AccountsReceivableBalance
    ,   M_ChargeCount
    ,   M_ChargeAmount
    ,   M_CancelCount
    ,   M_CancelAmount
    ,   M_SettlementBackCount
    ,   M_SettlementBackAmount
    ,   M_OemTransferCount
    ,   M_OemTransferAmount
    ,   M_ReClaimFeeCount
    ,   M_ReClaimFeeAmount
    ,   M_DamageCount
    ,   M_DamageAmount
    ,   M_ReceiptCount
    ,   M_ReceiptAmount
    ,   M_RepayCount
    ,   M_RepayAmount
    ,   M_BadDebtCount
    ,   M_BadDebtAmount
    ,   M_OtherPaymentCount
    ,   M_OtherPaymentAmount
    ,   M_AccountsReceivableBalance
    ,   M_SuspensePaymentsAmount
    ,   M_AccountsReceivableBalanceDiff
    ,   M_SettlementFee
    ,   M_SettlementFeeTax
    ,   M_ClaimFee
    ,   M_ClaimFeeTax
    ,   M_MonthlyFee
    ,   M_MonthlyFeeTax
    ,   M_OemIncludeMonthlyFee
    ,   M_OemIncludeMonthlyFeeTax
    ,   M_OemApiMonthlyFee
    ,   M_OemApiMonthlyFeeTax
    ,   M_OemCreditNoticeMonthlyFee
    ,   M_OemCreditNoticeMonthlyFeeTax
    ,   M_OemNCreditNoticeMonthlyFee
    ,   M_OemNCreditNoticeMonthlyFeeTax
    ,   M_AddClaimFee
    ,   M_AddClaimFeeTax
    ,   M_DamageInterestAmount
    ,   M_CanSettlementFee
    ,   M_CanSettlementFeeTax
    ,   M_CanClaimFee
    ,   M_CanClaimFeeTax
    ,   M_SettlementFeeTotal
    ,   M_SettlementFeeTaxTotal
    ,   M_ClaimFeeTotal
    ,   M_ClaimFeeTaxTotal
    ,   M_MonthlyFeeTotal
    ,   M_MonthlyFeeTaxTotal
    ,   M_OemIncludeMonthlyFeeTotal
    ,   M_OemIncludeMonthlyFeeTaxTotal
    ,   M_OemApiMonthlyFeeTotal
    ,   M_OemApiMonthlyFeeTaxTotal
    ,   M_OemCreditNoticeMonthlyFeeTotal
    ,   M_OemCreditNoticeMonthlyFeeTaxTotal
    ,   M_OemNCreditNoticeMonthlyFeeTotal
    ,   M_OemNCreditNoticeMonthlyFeeTaxTotal
    ,   M_AddClaimFeeTotal
    ,   M_AddClaimFeeTaxTotal
    ,   M_DamageInterestAmountTotal
    ,   M_AllTotal
    ,   M_SettlementFeeOther
    ,   M_SettlementFeeTaxOther
    ,   M_ClaimFeeOther
    ,   M_ClaimFeeTaxOther
    ,   M_MonthlyFeeOther
    ,   M_MonthlyFeeTaxOther
    ,   M_OemIncludeMonthlyFeeOther
    ,   M_OemIncludeMonthlyFeeTaxOther
    ,   M_OemApiMonthlyFeeOther
    ,   M_OemApiMonthlyFeeTaxOther
    ,   M_OemCreditNoticeMonthlyFeeOther
    ,   M_OemCreditNoticeMonthlyFeeTaxOther
    ,   M_OemNCreditNoticeMonthlyFeeOther
    ,   M_OemNCreditNoticeMonthlyFeeTaxOther
    ,   M_AddClaimFeeOther
    ,   M_AddClaimFeeTaxOther
    ,   M_DamageInterestAmountOther
    ,   M_SettlementFeeDiff
    ,   M_SettlementFeeTaxDiff
    ,   M_ClaimFeeDiff
    ,   M_ClaimFeeTaxDiff
    ,   M_MonthlyFeeDiff
    ,   M_MonthlyFeeTaxDiff
    ,   M_OemIncludeMonthlyFeeDiff
    ,   M_OemIncludeMonthlyFeeTaxDiff
    ,   M_OemApiMonthlyFeeDiff
    ,   M_OemApiMonthlyFeeTaxDiff
    ,   M_OemCreditNoticeMonthlyFeeDiff
    ,   M_OemCreditNoticeMonthlyFeeTaxDiff
    ,   M_OemNCreditNoticeMonthlyFeeDiff
    ,   M_OemNCreditNoticeMonthlyFeeTaxDiff
    ,   M_AddClaimFeeDiff
    ,   M_AddClaimFeeTaxDiff
    ,   M_DamageInterestAmountDiff
FROM    AT_Oem_DailyStatisticsTable
WHERE   1 = 1
%s
ORDER BY
        IFNULL(OemId, 9999999)
    ,   EnterpriseId
EOQ;
    }

    /**
     * (サマリー限定)OEM日次統計表取得ベースSQL
     *
     * @return string
     */
    protected function getOEMNichijiTokeiBaseQuery_Summary() {
        return <<<EOQ
SELECT  '' AS Seq
    ,   DailyMonthlyFlg
    ,   MIN(ProcessingDate) AS ProcessingDate
    ,   AccountDate
    ,   NULL AS OemId
    ,   NULL AS OemNameKj
    ,   NULL AS EnterpriseId
    ,   '総合計' AS EnterpriseNameKj
        /* 当日売上 */
    ,   SUM(DB__AccountsReceivableBalance) AS DB__AccountsReceivableBalance
    ,   SUM(D_ChargeCount) AS D_ChargeCount
    ,   SUM(D_ChargeAmount) AS D_ChargeAmount
    ,   SUM(D_CancelCount) AS D_CancelCount
    ,   SUM(D_CancelAmount) AS D_CancelAmount
    ,   SUM(D_SettlementBackCount) AS D_SettlementBackCount
    ,   SUM(D_SettlementBackAmount) AS D_SettlementBackAmount
    ,   SUM(D_OemTransferCount) AS D_OemTransferCount
    ,   SUM(D_OemTransferAmount) AS D_OemTransferAmount
    ,   SUM(D_ReClaimFeeCount) AS D_ReClaimFeeCount
    ,   SUM(D_ReClaimFeeAmount) AS D_ReClaimFeeAmount
    ,   SUM(D_DamageCount) AS D_DamageCount
    ,   SUM(D_DamageAmount) AS D_DamageAmount
    ,   SUM(D_ReceiptCount) AS D_ReceiptCount
    ,   SUM(D_ReceiptAmount) AS D_ReceiptAmount
    ,   SUM(D_RepayCount) AS D_RepayCount
    ,   SUM(D_RepayAmount) AS D_RepayAmount
    ,   SUM(D_BadDebtCount) AS D_BadDebtCount
    ,   SUM(D_BadDebtAmount) AS D_BadDebtAmount
    ,   SUM(D_OtherPaymentCount) AS D_OtherPaymentCount
    ,   SUM(D_OtherPaymentAmount) AS D_OtherPaymentAmount
    ,   SUM(D_AccountsReceivableBalance) AS D_AccountsReceivableBalance
    ,   SUM(D_SettlementFee) AS D_SettlementFee
    ,   SUM(D_SettlementFeeTax) AS D_SettlementFeeTax
    ,   SUM(D_ClaimFee) AS D_ClaimFee
    ,   SUM(D_ClaimFeeTax) AS D_ClaimFeeTax
    ,   SUM(D_MonthlyFee) AS D_MonthlyFee
    ,   SUM(D_MonthlyFeeTax) AS D_MonthlyFeeTax
    ,   SUM(D_OemIncludeMonthlyFee) AS D_OemIncludeMonthlyFee
    ,   SUM(D_OemIncludeMonthlyFeeTax) AS D_OemIncludeMonthlyFeeTax
    ,   SUM(D_OemApiMonthlyFee) AS D_OemApiMonthlyFee
    ,   SUM(D_OemApiMonthlyFeeTax) AS D_OemApiMonthlyFeeTax
    ,   SUM(D_OemCreditNoticeMonthlyFee) AS D_OemCreditNoticeMonthlyFee
    ,   SUM(D_OemCreditNoticeMonthlyFeeTax) AS D_OemCreditNoticeMonthlyFeeTax
    ,   SUM(D_OemNCreditNoticeMonthlyFee) AS D_OemNCreditNoticeMonthlyFee
    ,   SUM(D_OemNCreditNoticeMonthlyFeeTax) AS D_OemNCreditNoticeMonthlyFeeTax
    ,   SUM(D_AddClaimFee) AS D_AddClaimFee
    ,   SUM(D_AddClaimFeeTax) AS D_AddClaimFeeTax
    ,   SUM(D_DamageInterestAmount) AS D_DamageInterestAmount
    ,   SUM(D_CanSettlementFee) AS D_CanSettlementFee
    ,   SUM(D_CanSettlementFeeTax) AS D_CanSettlementFeeTax
    ,   SUM(D_CanClaimFee) AS D_CanClaimFee
    ,   SUM(D_CanClaimFeeTax) AS D_CanClaimFeeTax
    ,   SUM(D_SettlementFeeTotal) AS D_SettlementFeeTotal
    ,   SUM(D_SettlementFeeTaxTotal) AS D_SettlementFeeTaxTotal
    ,   SUM(D_ClaimFeeTotal) AS D_ClaimFeeTotal
    ,   SUM(D_ClaimFeeTaxTotal) AS D_ClaimFeeTaxTotal
    ,   SUM(D_MonthlyFeeTotal) AS D_MonthlyFeeTotal
    ,   SUM(D_MonthlyFeeTaxTotal) AS D_MonthlyFeeTaxTotal
    ,   SUM(D_OemIncludeMonthlyFeeTotal) AS D_OemIncludeMonthlyFeeTotal
    ,   SUM(D_OemIncludeMonthlyFeeTaxTotal) AS D_OemIncludeMonthlyFeeTaxTotal
    ,   SUM(D_OemApiMonthlyFeeTotal) AS D_OemApiMonthlyFeeTotal
    ,   SUM(D_OemApiMonthlyFeeTaxTotal) AS D_OemApiMonthlyFeeTaxTotal
    ,   SUM(D_OemCreditNoticeMonthlyFeeTotal) AS D_OemCreditNoticeMonthlyFeeTotal
    ,   SUM(D_OemCreditNoticeMonthlyFeeTaxTotal) AS D_OemCreditNoticeMonthlyFeeTaxTotal
    ,   SUM(D_OemNCreditNoticeMonthlyFeeTotal) AS D_OemNCreditNoticeMonthlyFeeTotal
    ,   SUM(D_OemNCreditNoticeMonthlyFeeTaxTotal) AS D_OemNCreditNoticeMonthlyFeeTaxTotal
    ,   SUM(D_AddClaimFeeTotal) AS D_AddClaimFeeTotal
    ,   SUM(D_AddClaimFeeTaxTotal) AS D_AddClaimFeeTaxTotal
    ,   SUM(D_DamageInterestAmountTotal) AS D_DamageInterestAmountTotal
    ,   SUM(D_AllTotal) AS D_AllTotal
    ,   SUM(D_SettlementFeeOther) AS D_SettlementFeeOther
    ,   SUM(D_SettlementFeeTaxOther) AS D_SettlementFeeTaxOther
    ,   SUM(D_ClaimFeeOther) AS D_ClaimFeeOther
    ,   SUM(D_ClaimFeeTaxOther) AS D_ClaimFeeTaxOther
    ,   SUM(D_MonthlyFeeOther) AS D_MonthlyFeeOther
    ,   SUM(D_MonthlyFeeTaxOther) AS D_MonthlyFeeTaxOther
    ,   SUM(D_OemIncludeMonthlyFeeOther) AS D_OemIncludeMonthlyFeeOther
    ,   SUM(D_OemIncludeMonthlyFeeTaxOther) AS D_OemIncludeMonthlyFeeTaxOther
    ,   SUM(D_OemApiMonthlyFeeOther) AS D_OemApiMonthlyFeeOther
    ,   SUM(D_OemApiMonthlyFeeTaxOther) AS D_OemApiMonthlyFeeTaxOther
    ,   SUM(D_OemCreditNoticeMonthlyFeeOther) AS D_OemCreditNoticeMonthlyFeeOther
    ,   SUM(D_OemCreditNoticeMonthlyFeeTaxOther) AS D_OemCreditNoticeMonthlyFeeTaxOther
    ,   SUM(D_OemNCreditNoticeMonthlyFeeOther) AS D_OemNCreditNoticeMonthlyFeeOther
    ,   SUM(D_OemNCreditNoticeMonthlyFeeTaxOther) AS D_OemNCreditNoticeMonthlyFeeTaxOther
    ,   SUM(D_AddClaimFeeOther) AS D_AddClaimFeeOther
    ,   SUM(D_AddClaimFeeTaxOther) AS D_AddClaimFeeTaxOther
    ,   SUM(D_DamageInterestAmountOther) AS D_DamageInterestAmountOther
    ,   SUM(D_SettlementFeeDiff) AS D_SettlementFeeDiff
    ,   SUM(D_SettlementFeeTaxDiff) AS D_SettlementFeeTaxDiff
    ,   SUM(D_ClaimFeeDiff) AS D_ClaimFeeDiff
    ,   SUM(D_ClaimFeeTaxDiff) AS D_ClaimFeeTaxDiff
    ,   SUM(D_MonthlyFeeDiff) AS D_MonthlyFeeDiff
    ,   SUM(D_MonthlyFeeTaxDiff) AS D_MonthlyFeeTaxDiff
    ,   SUM(D_OemIncludeMonthlyFeeDiff) AS D_OemIncludeMonthlyFeeDiff
    ,   SUM(D_OemIncludeMonthlyFeeTaxDiff) AS D_OemIncludeMonthlyFeeTaxDiff
    ,   SUM(D_OemApiMonthlyFeeDiff) AS D_OemApiMonthlyFeeDiff
    ,   SUM(D_OemApiMonthlyFeeTaxDiff) AS D_OemApiMonthlyFeeTaxDiff
    ,   SUM(D_OemCreditNoticeMonthlyFeeDiff) AS D_OemCreditNoticeMonthlyFeeDiff
    ,   SUM(D_OemCreditNoticeMonthlyFeeTaxDiff) AS D_OemCreditNoticeMonthlyFeeTaxDiff
    ,   SUM(D_OemNCreditNoticeMonthlyFeeDiff) AS D_OemNCreditNoticeMonthlyFeeDiff
    ,   SUM(D_OemNCreditNoticeMonthlyFeeTaxDiff) AS D_OemNCreditNoticeMonthlyFeeTaxDiff
    ,   SUM(D_AddClaimFeeDiff) AS D_AddClaimFeeDiff
    ,   SUM(D_AddClaimFeeTaxDiff) AS D_AddClaimFeeTaxDiff
    ,   SUM(D_DamageInterestAmountDiff) AS D_DamageInterestAmountDiff
        /* 当月売上 */
    ,   SUM(MB__AccountsReceivableBalance) AS MB__AccountsReceivableBalance
    ,   SUM(M_ChargeCount) AS M_ChargeCount
    ,   SUM(M_ChargeAmount) AS M_ChargeAmount
    ,   SUM(M_CancelCount) AS M_CancelCount
    ,   SUM(M_CancelAmount) AS M_CancelAmount
    ,   SUM(M_SettlementBackCount) AS M_SettlementBackCount
    ,   SUM(M_SettlementBackAmount) AS M_SettlementBackAmount
    ,   SUM(M_OemTransferCount) AS M_OemTransferCount
    ,   SUM(M_OemTransferAmount) AS M_OemTransferAmount
    ,   SUM(M_ReClaimFeeCount) AS M_ReClaimFeeCount
    ,   SUM(M_ReClaimFeeAmount) AS M_ReClaimFeeAmount
    ,   SUM(M_DamageCount) AS M_DamageCount
    ,   SUM(M_DamageAmount) AS M_DamageAmount
    ,   SUM(M_ReceiptCount) AS M_ReceiptCount
    ,   SUM(M_ReceiptAmount) AS M_ReceiptAmount
    ,   SUM(M_RepayCount) AS M_RepayCount
    ,   SUM(M_RepayAmount) AS M_RepayAmount
    ,   SUM(M_BadDebtCount) AS M_BadDebtCount
    ,   SUM(M_BadDebtAmount) AS M_BadDebtAmount
    ,   SUM(M_OtherPaymentCount) AS M_OtherPaymentCount
    ,   SUM(M_OtherPaymentAmount) AS M_OtherPaymentAmount
    ,   SUM(M_AccountsReceivableBalance) AS M_AccountsReceivableBalance
    ,   SUM(M_SuspensePaymentsAmount) AS M_SuspensePaymentsAmount
    ,   SUM(M_AccountsReceivableBalanceDiff) AS M_AccountsReceivableBalanceDiff
    ,   SUM(M_SettlementFee) AS M_SettlementFee
    ,   SUM(M_SettlementFeeTax) AS M_SettlementFeeTax
    ,   SUM(M_ClaimFee) AS M_ClaimFee
    ,   SUM(M_ClaimFeeTax) AS M_ClaimFeeTax
    ,   SUM(M_MonthlyFee) AS M_MonthlyFee
    ,   SUM(M_MonthlyFeeTax) AS M_MonthlyFeeTax
    ,   SUM(M_OemIncludeMonthlyFee) AS M_OemIncludeMonthlyFee
    ,   SUM(M_OemIncludeMonthlyFeeTax) AS M_OemIncludeMonthlyFeeTax
    ,   SUM(M_OemApiMonthlyFee) AS M_OemApiMonthlyFee
    ,   SUM(M_OemApiMonthlyFeeTax) AS M_OemApiMonthlyFeeTax
    ,   SUM(M_OemCreditNoticeMonthlyFee) AS M_OemCreditNoticeMonthlyFee
    ,   SUM(M_OemCreditNoticeMonthlyFeeTax) AS M_OemCreditNoticeMonthlyFeeTax
    ,   SUM(M_OemNCreditNoticeMonthlyFee) AS M_OemNCreditNoticeMonthlyFee
    ,   SUM(M_OemNCreditNoticeMonthlyFeeTax) AS M_OemNCreditNoticeMonthlyFeeTax
    ,   SUM(M_AddClaimFee) AS M_AddClaimFee
    ,   SUM(M_AddClaimFeeTax) AS M_AddClaimFeeTax
    ,   SUM(M_DamageInterestAmount) AS M_DamageInterestAmount
    ,   SUM(M_CanSettlementFee) AS M_CanSettlementFee
    ,   SUM(M_CanSettlementFeeTax) AS M_CanSettlementFeeTax
    ,   SUM(M_CanClaimFee) AS M_CanClaimFee
    ,   SUM(M_CanClaimFeeTax) AS M_CanClaimFeeTax
    ,   SUM(M_SettlementFeeTotal) AS M_SettlementFeeTotal
    ,   SUM(M_SettlementFeeTaxTotal) AS M_SettlementFeeTaxTotal
    ,   SUM(M_ClaimFeeTotal) AS M_ClaimFeeTotal
    ,   SUM(M_ClaimFeeTaxTotal) AS M_ClaimFeeTaxTotal
    ,   SUM(M_MonthlyFeeTotal) AS M_MonthlyFeeTotal
    ,   SUM(M_MonthlyFeeTaxTotal) AS M_MonthlyFeeTaxTotal
    ,   SUM(M_OemIncludeMonthlyFeeTotal) AS M_OemIncludeMonthlyFeeTotal
    ,   SUM(M_OemIncludeMonthlyFeeTaxTotal) AS M_OemIncludeMonthlyFeeTaxTotal
    ,   SUM(M_OemApiMonthlyFeeTotal) AS M_OemApiMonthlyFeeTotal
    ,   SUM(M_OemApiMonthlyFeeTaxTotal) AS M_OemApiMonthlyFeeTaxTotal
    ,   SUM(M_OemCreditNoticeMonthlyFeeTotal) AS M_OemCreditNoticeMonthlyFeeTotal
    ,   SUM(M_OemCreditNoticeMonthlyFeeTaxTotal) AS M_OemCreditNoticeMonthlyFeeTaxTotal
    ,   SUM(M_OemNCreditNoticeMonthlyFeeTotal) AS M_OemNCreditNoticeMonthlyFeeTotal
    ,   SUM(M_OemNCreditNoticeMonthlyFeeTaxTotal) AS M_OemNCreditNoticeMonthlyFeeTaxTotal
    ,   SUM(M_AddClaimFeeTotal) AS M_AddClaimFeeTotal
    ,   SUM(M_AddClaimFeeTaxTotal) AS M_AddClaimFeeTaxTotal
    ,   SUM(M_DamageInterestAmountTotal) AS M_DamageInterestAmountTotal
    ,   SUM(M_AllTotal) AS M_AllTotal
    ,   SUM(M_SettlementFeeOther) AS M_SettlementFeeOther
    ,   SUM(M_SettlementFeeTaxOther) AS M_SettlementFeeTaxOther
    ,   SUM(M_ClaimFeeOther) AS M_ClaimFeeOther
    ,   SUM(M_ClaimFeeTaxOther) AS M_ClaimFeeTaxOther
    ,   SUM(M_MonthlyFeeOther) AS M_MonthlyFeeOther
    ,   SUM(M_MonthlyFeeTaxOther) AS M_MonthlyFeeTaxOther
    ,   SUM(M_OemIncludeMonthlyFeeOther) AS M_OemIncludeMonthlyFeeOther
    ,   SUM(M_OemIncludeMonthlyFeeTaxOther) AS M_OemIncludeMonthlyFeeTaxOther
    ,   SUM(M_OemApiMonthlyFeeOther) AS M_OemApiMonthlyFeeOther
    ,   SUM(M_OemApiMonthlyFeeTaxOther) AS M_OemApiMonthlyFeeTaxOther
    ,   SUM(M_OemCreditNoticeMonthlyFeeOther) AS M_OemCreditNoticeMonthlyFeeOther
    ,   SUM(M_OemCreditNoticeMonthlyFeeTaxOther) AS M_OemCreditNoticeMonthlyFeeTaxOther
    ,   SUM(M_OemNCreditNoticeMonthlyFeeOther) AS M_OemNCreditNoticeMonthlyFeeOther
    ,   SUM(M_OemNCreditNoticeMonthlyFeeTaxOther) AS M_OemNCreditNoticeMonthlyFeeTaxOther
    ,   SUM(M_AddClaimFeeOther) AS M_AddClaimFeeOther
    ,   SUM(M_AddClaimFeeTaxOther) AS M_AddClaimFeeTaxOther
    ,   SUM(M_DamageInterestAmountOther) AS M_DamageInterestAmountOther
    ,   SUM(M_SettlementFeeDiff) AS M_SettlementFeeDiff
    ,   SUM(M_SettlementFeeTaxDiff) AS M_SettlementFeeTaxDiff
    ,   SUM(M_ClaimFeeDiff) AS M_ClaimFeeDiff
    ,   SUM(M_ClaimFeeTaxDiff) AS M_ClaimFeeTaxDiff
    ,   SUM(M_MonthlyFeeDiff) AS M_MonthlyFeeDiff
    ,   SUM(M_MonthlyFeeTaxDiff) AS M_MonthlyFeeTaxDiff
    ,   SUM(M_OemIncludeMonthlyFeeDiff) AS M_OemIncludeMonthlyFeeDiff
    ,   SUM(M_OemIncludeMonthlyFeeTaxDiff) AS M_OemIncludeMonthlyFeeTaxDiff
    ,   SUM(M_OemApiMonthlyFeeDiff) AS M_OemApiMonthlyFeeDiff
    ,   SUM(M_OemApiMonthlyFeeTaxDiff) AS M_OemApiMonthlyFeeTaxDiff
    ,   SUM(M_OemCreditNoticeMonthlyFeeDiff) AS M_OemCreditNoticeMonthlyFeeDiff
    ,   SUM(M_OemCreditNoticeMonthlyFeeTaxDiff) AS M_OemCreditNoticeMonthlyFeeTaxDiff
    ,   SUM(M_OemNCreditNoticeMonthlyFeeDiff) AS M_OemNCreditNoticeMonthlyFeeDiff
    ,   SUM(M_OemNCreditNoticeMonthlyFeeTaxDiff) AS M_OemNCreditNoticeMonthlyFeeTaxDiff
    ,   SUM(M_AddClaimFeeDiff) AS M_AddClaimFeeDiff
    ,   SUM(M_AddClaimFeeTaxDiff) AS M_AddClaimFeeTaxDiff
    ,   SUM(M_DamageInterestAmountDiff) AS M_DamageInterestAmountDiff
FROM    AT_Oem_DailyStatisticsTable
WHERE   1 = 1
%s
GROUP BY
        DailyMonthlyFlg
,       AccountDate
EOQ;
    }

    /**
     * OEM日次統計表OEM合計取得SQL
     *
     * @return string
     */
    protected function getOEMNichijiTokeiSubTotalQuery() {
        return <<<EOQ
SELECT  DailyMonthlyFlg
    ,   MIN(ProcessingDate) AS ProcessingDate
    ,   AccountDate
    ,   OemId
    ,   MAX(OemNameKj) AS OemNameKj
    ,   'OEM合計' AS EnterpriseNameKj
        /* 当日売上 */
    ,   SUM(DB__AccountsReceivableBalance) AS DB__AccountsReceivableBalance
    ,   SUM(D_ChargeCount) AS D_ChargeCount
    ,   SUM(D_ChargeAmount) AS D_ChargeAmount
    ,   SUM(D_CancelCount) AS D_CancelCount
    ,   SUM(D_CancelAmount) AS D_CancelAmount
    ,   SUM(D_SettlementBackCount) AS D_SettlementBackCount
    ,   SUM(D_SettlementBackAmount) AS D_SettlementBackAmount
    ,   SUM(D_OemTransferCount) AS D_OemTransferCount
    ,   SUM(D_OemTransferAmount) AS D_OemTransferAmount
    ,   SUM(D_ReClaimFeeCount) AS D_ReClaimFeeCount
    ,   SUM(D_ReClaimFeeAmount) AS D_ReClaimFeeAmount
    ,   SUM(D_DamageCount) AS D_DamageCount
    ,   SUM(D_DamageAmount) AS D_DamageAmount
    ,   SUM(D_ReceiptCount) AS D_ReceiptCount
    ,   SUM(D_ReceiptAmount) AS D_ReceiptAmount
    ,   SUM(D_RepayCount) AS D_RepayCount
    ,   SUM(D_RepayAmount) AS D_RepayAmount
    ,   SUM(D_BadDebtCount) AS D_BadDebtCount
    ,   SUM(D_BadDebtAmount) AS D_BadDebtAmount
    ,   SUM(D_OtherPaymentCount) AS D_OtherPaymentCount
    ,   SUM(D_OtherPaymentAmount) AS D_OtherPaymentAmount
    ,   SUM(D_AccountsReceivableBalance) AS D_AccountsReceivableBalance
    ,   SUM(D_SettlementFee) AS D_SettlementFee
    ,   SUM(D_SettlementFeeTax) AS D_SettlementFeeTax
    ,   SUM(D_ClaimFee) AS D_ClaimFee
    ,   SUM(D_ClaimFeeTax) AS D_ClaimFeeTax
    ,   SUM(D_MonthlyFee) AS D_MonthlyFee
    ,   SUM(D_MonthlyFeeTax) AS D_MonthlyFeeTax
    ,   SUM(D_OemIncludeMonthlyFee) AS D_OemIncludeMonthlyFee
    ,   SUM(D_OemIncludeMonthlyFeeTax) AS D_OemIncludeMonthlyFeeTax
    ,   SUM(D_OemApiMonthlyFee) AS D_OemApiMonthlyFee
    ,   SUM(D_OemApiMonthlyFeeTax) AS D_OemApiMonthlyFeeTax
    ,   SUM(D_OemCreditNoticeMonthlyFee) AS D_OemCreditNoticeMonthlyFee
    ,   SUM(D_OemCreditNoticeMonthlyFeeTax) AS D_OemCreditNoticeMonthlyFeeTax
    ,   SUM(D_OemNCreditNoticeMonthlyFee) AS D_OemNCreditNoticeMonthlyFee
    ,   SUM(D_OemNCreditNoticeMonthlyFeeTax) AS D_OemNCreditNoticeMonthlyFeeTax
    ,   SUM(D_AddClaimFee) AS D_AddClaimFee
    ,   SUM(D_AddClaimFeeTax) AS D_AddClaimFeeTax
    ,   SUM(D_DamageInterestAmount) AS D_DamageInterestAmount
    ,   SUM(D_CanSettlementFee) AS D_CanSettlementFee
    ,   SUM(D_CanSettlementFeeTax) AS D_CanSettlementFeeTax
    ,   SUM(D_CanClaimFee) AS D_CanClaimFee
    ,   SUM(D_CanClaimFeeTax) AS D_CanClaimFeeTax
    ,   SUM(D_SettlementFeeTotal) AS D_SettlementFeeTotal
    ,   SUM(D_SettlementFeeTaxTotal) AS D_SettlementFeeTaxTotal
    ,   SUM(D_ClaimFeeTotal) AS D_ClaimFeeTotal
    ,   SUM(D_ClaimFeeTaxTotal) AS D_ClaimFeeTaxTotal
    ,   SUM(D_MonthlyFeeTotal) AS D_MonthlyFeeTotal
    ,   SUM(D_MonthlyFeeTaxTotal) AS D_MonthlyFeeTaxTotal
    ,   SUM(D_OemIncludeMonthlyFeeTotal) AS D_OemIncludeMonthlyFeeTotal
    ,   SUM(D_OemIncludeMonthlyFeeTaxTotal) AS D_OemIncludeMonthlyFeeTaxTotal
    ,   SUM(D_OemApiMonthlyFeeTotal) AS D_OemApiMonthlyFeeTotal
    ,   SUM(D_OemApiMonthlyFeeTaxTotal) AS D_OemApiMonthlyFeeTaxTotal
    ,   SUM(D_OemCreditNoticeMonthlyFeeTotal) AS D_OemCreditNoticeMonthlyFeeTotal
    ,   SUM(D_OemCreditNoticeMonthlyFeeTaxTotal) AS D_OemCreditNoticeMonthlyFeeTaxTotal
    ,   SUM(D_OemNCreditNoticeMonthlyFeeTotal) AS D_OemNCreditNoticeMonthlyFeeTotal
    ,   SUM(D_OemNCreditNoticeMonthlyFeeTaxTotal) AS D_OemNCreditNoticeMonthlyFeeTaxTotal
    ,   SUM(D_AddClaimFeeTotal) AS D_AddClaimFeeTotal
    ,   SUM(D_AddClaimFeeTaxTotal) AS D_AddClaimFeeTaxTotal
    ,   SUM(D_DamageInterestAmountTotal) AS D_DamageInterestAmountTotal
    ,   SUM(D_AllTotal) AS D_AllTotal
    ,   SUM(D_SettlementFeeOther) AS D_SettlementFeeOther
    ,   SUM(D_SettlementFeeTaxOther) AS D_SettlementFeeTaxOther
    ,   SUM(D_ClaimFeeOther) AS D_ClaimFeeOther
    ,   SUM(D_ClaimFeeTaxOther) AS D_ClaimFeeTaxOther
    ,   SUM(D_MonthlyFeeOther) AS D_MonthlyFeeOther
    ,   SUM(D_MonthlyFeeTaxOther) AS D_MonthlyFeeTaxOther
    ,   SUM(D_OemIncludeMonthlyFeeOther) AS D_OemIncludeMonthlyFeeOther
    ,   SUM(D_OemIncludeMonthlyFeeTaxOther) AS D_OemIncludeMonthlyFeeTaxOther
    ,   SUM(D_OemApiMonthlyFeeOther) AS D_OemApiMonthlyFeeOther
    ,   SUM(D_OemApiMonthlyFeeTaxOther) AS D_OemApiMonthlyFeeTaxOther
    ,   SUM(D_OemCreditNoticeMonthlyFeeOther) AS D_OemCreditNoticeMonthlyFeeOther
    ,   SUM(D_OemCreditNoticeMonthlyFeeTaxOther) AS D_OemCreditNoticeMonthlyFeeTaxOther
    ,   SUM(D_OemNCreditNoticeMonthlyFeeOther) AS D_OemNCreditNoticeMonthlyFeeOther
    ,   SUM(D_OemNCreditNoticeMonthlyFeeTaxOther) AS D_OemNCreditNoticeMonthlyFeeTaxOther
    ,   SUM(D_AddClaimFeeOther) AS D_AddClaimFeeOther
    ,   SUM(D_AddClaimFeeTaxOther) AS D_AddClaimFeeTaxOther
    ,   SUM(D_DamageInterestAmountOther) AS D_DamageInterestAmountOther
    ,   SUM(D_SettlementFeeDiff) AS D_SettlementFeeDiff
    ,   SUM(D_SettlementFeeTaxDiff) AS D_SettlementFeeTaxDiff
    ,   SUM(D_ClaimFeeDiff) AS D_ClaimFeeDiff
    ,   SUM(D_ClaimFeeTaxDiff) AS D_ClaimFeeTaxDiff
    ,   SUM(D_MonthlyFeeDiff) AS D_MonthlyFeeDiff
    ,   SUM(D_MonthlyFeeTaxDiff) AS D_MonthlyFeeTaxDiff
    ,   SUM(D_OemIncludeMonthlyFeeDiff) AS D_OemIncludeMonthlyFeeDiff
    ,   SUM(D_OemIncludeMonthlyFeeTaxDiff) AS D_OemIncludeMonthlyFeeTaxDiff
    ,   SUM(D_OemApiMonthlyFeeDiff) AS D_OemApiMonthlyFeeDiff
    ,   SUM(D_OemApiMonthlyFeeTaxDiff) AS D_OemApiMonthlyFeeTaxDiff
    ,   SUM(D_OemCreditNoticeMonthlyFeeDiff) AS D_OemCreditNoticeMonthlyFeeDiff
    ,   SUM(D_OemCreditNoticeMonthlyFeeTaxDiff) AS D_OemCreditNoticeMonthlyFeeTaxDiff
    ,   SUM(D_OemNCreditNoticeMonthlyFeeDiff) AS D_OemNCreditNoticeMonthlyFeeDiff
    ,   SUM(D_OemNCreditNoticeMonthlyFeeTaxDiff) AS D_OemNCreditNoticeMonthlyFeeTaxDiff
    ,   SUM(D_AddClaimFeeDiff) AS D_AddClaimFeeDiff
    ,   SUM(D_AddClaimFeeTaxDiff) AS D_AddClaimFeeTaxDiff
    ,   SUM(D_DamageInterestAmountDiff) AS D_DamageInterestAmountDiff
        /* 当月売上 */
    ,   SUM(MB__AccountsReceivableBalance) AS MB__AccountsReceivableBalance
    ,   SUM(M_ChargeCount) AS M_ChargeCount
    ,   SUM(M_ChargeAmount) AS M_ChargeAmount
    ,   SUM(M_CancelCount) AS M_CancelCount
    ,   SUM(M_CancelAmount) AS M_CancelAmount
    ,   SUM(M_SettlementBackCount) AS M_SettlementBackCount
    ,   SUM(M_SettlementBackAmount) AS M_SettlementBackAmount
    ,   SUM(M_OemTransferCount) AS M_OemTransferCount
    ,   SUM(M_OemTransferAmount) AS M_OemTransferAmount
    ,   SUM(M_ReClaimFeeCount) AS M_ReClaimFeeCount
    ,   SUM(M_ReClaimFeeAmount) AS M_ReClaimFeeAmount
    ,   SUM(M_DamageCount) AS M_DamageCount
    ,   SUM(M_DamageAmount) AS M_DamageAmount
    ,   SUM(M_ReceiptCount) AS M_ReceiptCount
    ,   SUM(M_ReceiptAmount) AS M_ReceiptAmount
    ,   SUM(M_RepayCount) AS M_RepayCount
    ,   SUM(M_RepayAmount) AS M_RepayAmount
    ,   SUM(M_OtherPaymentCount) AS M_OtherPaymentCount
    ,   SUM(M_OtherPaymentAmount) AS M_OtherPaymentAmount
    ,   SUM(M_AccountsReceivableBalance) AS M_AccountsReceivableBalance
    ,   SUM(M_SuspensePaymentsAmount) AS M_SuspensePaymentsAmount
    ,   SUM(M_AccountsReceivableBalanceDiff) AS M_AccountsReceivableBalanceDiff
    ,   SUM(M_SettlementFee) AS M_SettlementFee
    ,   SUM(M_SettlementFeeTax) AS M_SettlementFeeTax
    ,   SUM(M_ClaimFee) AS M_ClaimFee
    ,   SUM(M_ClaimFeeTax) AS M_ClaimFeeTax
    ,   SUM(M_MonthlyFee) AS M_MonthlyFee
    ,   SUM(M_MonthlyFeeTax) AS M_MonthlyFeeTax
    ,   SUM(M_OemIncludeMonthlyFee) AS M_OemIncludeMonthlyFee
    ,   SUM(M_OemIncludeMonthlyFeeTax) AS M_OemIncludeMonthlyFeeTax
    ,   SUM(M_OemApiMonthlyFee) AS M_OemApiMonthlyFee
    ,   SUM(M_OemApiMonthlyFeeTax) AS M_OemApiMonthlyFeeTax
    ,   SUM(M_OemCreditNoticeMonthlyFee) AS M_OemCreditNoticeMonthlyFee
    ,   SUM(M_OemCreditNoticeMonthlyFeeTax) AS M_OemCreditNoticeMonthlyFeeTax
    ,   SUM(M_OemNCreditNoticeMonthlyFee) AS M_OemNCreditNoticeMonthlyFee
    ,   SUM(M_OemNCreditNoticeMonthlyFeeTax) AS M_OemNCreditNoticeMonthlyFeeTax
    ,   SUM(M_AddClaimFee) AS M_AddClaimFee
    ,   SUM(M_AddClaimFeeTax) AS M_AddClaimFeeTax
    ,   SUM(M_DamageInterestAmount) AS M_DamageInterestAmount
    ,   SUM(M_CanSettlementFee) AS M_CanSettlementFee
    ,   SUM(M_CanSettlementFeeTax) AS M_CanSettlementFeeTax
    ,   SUM(M_CanClaimFee) AS M_CanClaimFee
    ,   SUM(M_CanClaimFeeTax) AS M_CanClaimFeeTax
    ,   SUM(M_SettlementFeeTotal) AS M_SettlementFeeTotal
    ,   SUM(M_SettlementFeeTaxTotal) AS M_SettlementFeeTaxTotal
    ,   SUM(M_ClaimFeeTotal) AS M_ClaimFeeTotal
    ,   SUM(M_ClaimFeeTaxTotal) AS M_ClaimFeeTaxTotal
    ,   SUM(M_MonthlyFeeTotal) AS M_MonthlyFeeTotal
    ,   SUM(M_MonthlyFeeTaxTotal) AS M_MonthlyFeeTaxTotal
    ,   SUM(M_OemIncludeMonthlyFeeTotal) AS M_OemIncludeMonthlyFeeTotal
    ,   SUM(M_OemIncludeMonthlyFeeTaxTotal) AS M_OemIncludeMonthlyFeeTaxTotal
    ,   SUM(M_OemApiMonthlyFeeTotal) AS M_OemApiMonthlyFeeTotal
    ,   SUM(M_OemApiMonthlyFeeTaxTotal) AS M_OemApiMonthlyFeeTaxTotal
    ,   SUM(M_OemCreditNoticeMonthlyFeeTotal) AS M_OemCreditNoticeMonthlyFeeTotal
    ,   SUM(M_OemCreditNoticeMonthlyFeeTaxTotal) AS M_OemCreditNoticeMonthlyFeeTaxTotal
    ,   SUM(M_OemNCreditNoticeMonthlyFeeTotal) AS M_OemNCreditNoticeMonthlyFeeTotal
    ,   SUM(M_OemNCreditNoticeMonthlyFeeTaxTotal) AS M_OemNCreditNoticeMonthlyFeeTaxTotal
    ,   SUM(M_AddClaimFeeTotal) AS M_AddClaimFeeTotal
    ,   SUM(M_AddClaimFeeTaxTotal) AS M_AddClaimFeeTaxTotal
    ,   SUM(M_DamageInterestAmountTotal) AS M_DamageInterestAmountTotal
    ,   SUM(M_AllTotal) AS M_AllTotal
    ,   SUM(M_SettlementFeeOther) AS M_SettlementFeeOther
    ,   SUM(M_SettlementFeeTaxOther) AS M_SettlementFeeTaxOther
    ,   SUM(M_ClaimFeeOther) AS M_ClaimFeeOther
    ,   SUM(M_ClaimFeeTaxOther) AS M_ClaimFeeTaxOther
    ,   SUM(M_MonthlyFeeOther) AS M_MonthlyFeeOther
    ,   SUM(M_MonthlyFeeTaxOther) AS M_MonthlyFeeTaxOther
    ,   SUM(M_OemIncludeMonthlyFeeOther) AS M_OemIncludeMonthlyFeeOther
    ,   SUM(M_OemIncludeMonthlyFeeTaxOther) AS M_OemIncludeMonthlyFeeTaxOther
    ,   SUM(M_OemApiMonthlyFeeOther) AS M_OemApiMonthlyFeeOther
    ,   SUM(M_OemApiMonthlyFeeTaxOther) AS M_OemApiMonthlyFeeTaxOther
    ,   SUM(M_OemCreditNoticeMonthlyFeeOther) AS M_OemCreditNoticeMonthlyFeeOther
    ,   SUM(M_OemCreditNoticeMonthlyFeeTaxOther) AS M_OemCreditNoticeMonthlyFeeTaxOther
    ,   SUM(M_OemNCreditNoticeMonthlyFeeOther) AS M_OemNCreditNoticeMonthlyFeeOther
    ,   SUM(M_OemNCreditNoticeMonthlyFeeTaxOther) AS M_OemNCreditNoticeMonthlyFeeTaxOther
    ,   SUM(M_AddClaimFeeOther) AS M_AddClaimFeeOther
    ,   SUM(M_AddClaimFeeTaxOther) AS M_AddClaimFeeTaxOther
    ,   SUM(M_DamageInterestAmountOther) AS M_DamageInterestAmountOther
    ,   SUM(M_SettlementFeeDiff) AS M_SettlementFeeDiff
    ,   SUM(M_SettlementFeeTaxDiff) AS M_SettlementFeeTaxDiff
    ,   SUM(M_ClaimFeeDiff) AS M_ClaimFeeDiff
    ,   SUM(M_ClaimFeeTaxDiff) AS M_ClaimFeeTaxDiff
    ,   SUM(M_MonthlyFeeDiff) AS M_MonthlyFeeDiff
    ,   SUM(M_MonthlyFeeTaxDiff) AS M_MonthlyFeeTaxDiff
    ,   SUM(M_OemIncludeMonthlyFeeDiff) AS M_OemIncludeMonthlyFeeDiff
    ,   SUM(M_OemIncludeMonthlyFeeTaxDiff) AS M_OemIncludeMonthlyFeeTaxDiff
    ,   SUM(M_OemApiMonthlyFeeDiff) AS M_OemApiMonthlyFeeDiff
    ,   SUM(M_OemApiMonthlyFeeTaxDiff) AS M_OemApiMonthlyFeeTaxDiff
    ,   SUM(M_OemCreditNoticeMonthlyFeeDiff) AS M_OemCreditNoticeMonthlyFeeDiff
    ,   SUM(M_OemCreditNoticeMonthlyFeeTaxDiff) AS M_OemCreditNoticeMonthlyFeeTaxDiff
    ,   SUM(M_OemNCreditNoticeMonthlyFeeDiff) AS M_OemNCreditNoticeMonthlyFeeDiff
    ,   SUM(M_OemNCreditNoticeMonthlyFeeTaxDiff) AS M_OemNCreditNoticeMonthlyFeeTaxDiff
    ,   SUM(M_AddClaimFeeDiff) AS M_AddClaimFeeDiff
    ,   SUM(M_AddClaimFeeTaxDiff) AS M_AddClaimFeeTaxDiff
    ,   SUM(M_DamageInterestAmountDiff) AS M_DamageInterestAmountDiff
FROM    AT_Oem_DailyStatisticsTable
WHERE   1 = 1
AND     IFNULL(OemId, 0) = :OemId
%s
GROUP BY
        DailyMonthlyFlg
    ,   AccountDate
    ,   OemId
ORDER BY
        OemId
    ,   AccountDate
EOQ;
    }

    /**
     * 直営未払金・売掛金・加盟店未収金統計表取得ベースSQL
     *
     * @return string
     */
    protected function getCBMibaraiTokeiBaseQuery() {
        return <<<EOQ
SELECT 1 AS DailyMonthlyFlg                                                                        -- 日次･月次区分
,      F_GetSystemProperty('[DEFAULT]', 'systeminfo', 'BusinessDate') AS ProcessingDate            -- 処理日付
,      :AccountDate AS AccountDate                                                                 -- 会計月
,      aw_aps.EnterpriseId AS EnterpriseId                                                         -- 加盟店ID
,      e.EnterpriseNameKj AS EnterpriseNameKj                                                      -- 加盟店名
,      SUM(IFNULL(AP_AccountsPayableBalance, 0)) AS AP_AccountsPayableBalance                      -- 未払金(前月未払金残高)
,      SUM(IFNULL(AP_ChargeCount, 0)) AS AP_ChargeCount                                            -- 未払金(当月発生件数)
,      SUM(IFNULL(AP_ChargeAmount, 0)) AS AP_ChargeAmount                                          -- 未払金(当月発生金額)
,      SUM(IFNULL(AP_CancelCount, 0)) AS AP_CancelCount                                            -- 未払金(当月ｷｬﾝｾﾙ件数)
,      SUM(IFNULL(AP_CalcelAmount, 0)) AS AP_CalcelAmount                                          -- 未払金(当月ｷｬﾝｾﾙ立替金額)
,      SUM(IFNULL(AP_SettlementBackCount, 0)) AS AP_SettlementBackCount                            -- 未払金(当月立替金戻し件数)
,      SUM(IFNULL(AP_SettlementBackAmount, 0)) AS AP_SettlementBackAmount                          -- 未払金(当月立替金戻し金額)
,      SUM(IFNULL(AP_AccountsReceivableOffset, 0)) AS AP_AccountsReceivableOffset                  -- 未払金(売掛金相殺)
,      SUM(IFNULL(AP_AccountsPayableOffset, 0)) AS AP_AccountsPayableOffset                        -- 未払金(未収金相殺)
,      SUM(IFNULL(AP_OtherAccountsPayableOffset, 0)) AS AP_OtherAccountsPayableOffset              -- 未払金(その他未収金相殺)
,      SUM(IFNULL(AP_AccountsPayableTransfer, 0)) AS AP_AccountsPayableTransfer                    -- 未払金(未収金へ振替)
,      SUM(IFNULL(AP_AmountPaymentCount, 0)) AS AP_AmountPaymentCount                              -- 未払金(当月実支払件数)
,      SUM(IFNULL(AP_AmountPayment, 0)) AS AP_AmountPayment                                        -- 未払金(当月実支払額)
,      SUM(IFNULL(AP_BadDebtAmountCount, 0)) AS AP_BadDebtAmountCount                              -- 未払金(貸倒件数)
,      SUM(IFNULL(AP_BadDebtAmount, 0)) AS AP_BadDebtAmount                                        -- 未払金(貸倒金額)
,      SUM(IFNULL(AP_AccountsPayableBalance, 0)) +
         SUM(IFNULL(AP_ChargeAmount, 0)) +
         SUM(IFNULL(AP_CalcelAmount, 0)) +
         SUM(IFNULL(AP_SettlementBackAmount, 0)) +
         SUM(IFNULL(AP_AccountsReceivableOffset, 0)) +
         SUM(IFNULL(AP_AccountsPayableOffset, 0)) +
         SUM(IFNULL(AP_OtherAccountsPayableOffset, 0)) +
         SUM(IFNULL(AP_AccountsPayableTransfer, 0)) +
         SUM(IFNULL(AP_AmountPayment, 0)) +
         SUM(IFNULL(AP_BadDebtAmount, 0)) AS AP_PreAccountsPayableBalance                          -- 未払金(当月未払金残)
,      SUM(IFNULL(AP_UseAmountTotal, 0)) AS AP_UseAmountTotal                                      -- 未払金(商品代金等合計)
,      SUM(IFNULL(AP_AccountsPayableBalance, 0)) +
         SUM(IFNULL(AP_ChargeAmount, 0)) +
         SUM(IFNULL(AP_CalcelAmount, 0)) +
         SUM(IFNULL(AP_SettlementBackAmount, 0)) +
         SUM(IFNULL(AP_AccountsReceivableOffset, 0)) +
         SUM(IFNULL(AP_AccountsPayableOffset, 0)) +
         SUM(IFNULL(AP_OtherAccountsPayableOffset, 0)) +
         SUM(IFNULL(AP_AccountsPayableTransfer, 0)) +
         SUM(IFNULL(AP_AmountPayment, 0)) +
         SUM(IFNULL(AP_BadDebtAmount, 0)) -
         SUM(IFNULL(AP_UseAmountTotal, 0)) AS AP_Difference                                        -- 未払金(差額)
,      SUM(IFNULL(Other_StampFee, 0)) +
         SUM(IFNULL(Other_TransferCommission, 0)) +
         SUM(IFNULL(Other_AdjustmentAmount, 0)) +
         SUM(IFNULL(Other_Refund, 0)) AS Other_AccountsPayableOffset                               -- その他未収金相殺(合計)
,      SUM(CASE WHEN Other_StampFee <> 0 THEN 1 ELSE 0 END) AS Other_StampFeeCount                 -- その他未収金相殺(印紙代件数)
,      SUM(IFNULL(Other_StampFee, 0)) AS Other_StampFee                                            -- その他未収金相殺(印紙代)
,      SUM(CASE WHEN Other_TransferCommission <> 0 THEN 1 ELSE 0 END) AS Other_TransferCommissionCount -- その他未収金相殺(振込手数料件数)
,      SUM(IFNULL(Other_TransferCommission, 0)) AS Other_TransferCommission                        -- その他未収金相殺(振込手数料)
,      SUM(CASE WHEN Other_AdjustmentAmount <> 0 THEN 1 ELSE 0 END) AS Other_AdjustmentAmountCount -- その他未収金相殺(調整金額件数)
,      SUM(IFNULL(Other_AdjustmentAmount, 0)) AS Other_AdjustmentAmount                            -- その他未収金相殺(調整金額)
,      SUM(CASE WHEN Other_Refund <> 0 THEN 1 ELSE 0 END) AS Other_RefundCount                     -- その他未収金相殺(返金件数)
,      SUM(IFNULL(Other_Refund, 0)) AS Other_Refund                                                -- その他未収金相殺(返金)
,      SUM(IFNULL(AR_AccountsReceivableBalance, 0)) AS AR_AccountsReceivableBalance                -- 売掛金(前月売掛金残)
,      SUM(IFNULL(AR_SettlementFeeAmount, 0)) +
         SUM(IFNULL(AR_ClaimFeeAmount, 0)) +
         SUM(IFNULL(AR_MonthlyFee, 0)) +
         SUM(IFNULL(AR_IncludeMonthlyFee, 0)) +
         SUM(IFNULL(AR_ApiMonthlyFee, 0)) +
         SUM(IFNULL(AR_CreditNoticeMonthlyFee, 0)) +
         SUM(IFNULL(AR_NCreditNoticeMonthlyFee, 0)) +
         SUM(IFNULL(AR_ReserveMonthlyFee, 0)) AS AR_AccountsReceivableIncrease                     -- 売掛金(当月売上増加)
,      SUM(IFNULL(AR_SettlementFeeCount, 0)) AS AR_SettlementFeeCount                              -- 売掛金(決済手数料件数)
,      SUM(IFNULL(AR_SettlementFeeAmount, 0)) AS AR_SettlementFeeAmount                            -- 売掛金(決済手数料金額)
,      SUM(IFNULL(AR_ClaimFeeCount, 0)) AS AR_ClaimFeeCount                                        -- 売掛金(請求手数料件数)
,      SUM(IFNULL(AR_ClaimFeeAmount, 0)) AS AR_ClaimFeeAmount                                      -- 売掛金(請求手数料金額)
,      SUM(IFNULL(AR_MonthlyFee, 0)) AS AR_MonthlyFee                                              -- 売掛金(月額固定費)
,      SUM(IFNULL(AR_IncludeMonthlyFee, 0)) AS AR_IncludeMonthlyFee                                -- 売掛金(同梱月額固定費)
,      SUM(IFNULL(AR_ApiMonthlyFee, 0)) AS AR_ApiMonthlyFee                                        -- 売掛金(API月額固定費)
,      SUM(IFNULL(AR_CreditNoticeMonthlyFee, 0)) AS AR_CreditNoticeMonthlyFee                      -- 売掛金(与信結果通知ｻｰﾋﾞｽ月額固定費)
,      SUM(IFNULL(AR_NCreditNoticeMonthlyFee, 0)) AS AR_NCreditNoticeMonthlyFee                    -- 売掛金(次回請求与信結果通知ｻｰﾋﾞｽ月額固定費)
,      SUM(IFNULL(AR_ReserveMonthlyFee, 0)) AS AR_ReserveMonthlyFee                                -- 売掛金(月額固定費予備)
,      SUM(IFNULL(AR_AccountsPayableOffset, 0)) AS AR_AccountsPayableOffset                        -- 売掛金(未払金相殺)
,      SUM(IFNULL(AR_AccountsPayableTransfer, 0)) AS AR_AccountsPayableTransfer                    -- 売掛金(未収金へ振替)
,      SUM(IFNULL(AR_AccountsMonthPaymentCount, 0)) AS AR_AccountsMonthPaymentCount                -- 売掛金(当月入金件数)
,      SUM(IFNULL(AR_AccountsMonthPayment, 0)) AS AR_AccountsMonthPayment                          -- 売掛金(当月入金)
,      SUM(IFNULL(AR_BadDebtAmountCount, 0)) AS AR_BadDebtAmountCount                              -- 売掛金(貸倒件数)
,      SUM(IFNULL(AR_BadDebtAmount, 0)) AS AR_BadDebtAmount                                        -- 売掛金(貸倒金額)
,      SUM(IFNULL(AR_AccountsReceivableBalance, 0)) +
         SUM(IFNULL(AR_SettlementFeeAmount, 0)) +
         SUM(IFNULL(AR_ClaimFeeAmount, 0)) +
         SUM(IFNULL(AR_MonthlyFee, 0)) +
         SUM(IFNULL(AR_IncludeMonthlyFee, 0)) +
         SUM(IFNULL(AR_ApiMonthlyFee, 0)) +
         SUM(IFNULL(AR_CreditNoticeMonthlyFee, 0)) +
         SUM(IFNULL(AR_NCreditNoticeMonthlyFee, 0)) +
         SUM(IFNULL(AR_AccountsPayableOffset, 0)) +
         SUM(IFNULL(AR_AccountsMonthPayment, 0)) +
         SUM(IFNULL(AR_BadDebtAmount, 0)) AS AR_PreAccountsReceivableBalance                       -- 売掛金(当月末売掛金残)
,      SUM(IFNULL(AR_AccountsReceivableToal, 0)) AS AR_AccountsReceivableToal                      -- 売掛金(売掛金合計)
,      SUM(IFNULL(AR_AccountsReceivableBalance, 0)) +
         SUM(IFNULL(AR_SettlementFeeAmount, 0)) +
         SUM(IFNULL(AR_ClaimFeeAmount, 0)) +
         SUM(IFNULL(AR_MonthlyFee, 0)) +
         SUM(IFNULL(AR_IncludeMonthlyFee, 0)) +
         SUM(IFNULL(AR_ApiMonthlyFee, 0)) +
         SUM(IFNULL(AR_CreditNoticeMonthlyFee, 0)) +
         SUM(IFNULL(AR_NCreditNoticeMonthlyFee, 0)) +
         SUM(IFNULL(AR_AccountsPayableOffset, 0)) +
         SUM(IFNULL(AR_AccountsMonthPayment, 0)) +
         SUM(IFNULL(AR_BadDebtAmount, 0)) -
         SUM(IFNULL(AR_AccountsReceivableToal, 0)) AS AR_Difference                                -- 売掛金(差額)
,      SUM(IFNULL(AD_AccountsDueBalance, 0)) AS AD_AccountsDueBalance                              -- 未収金(前月未収金残)
,      SUM(IFNULL(AD_TransferAmountCount, 0)) AS AD_TransferAmountCount                            -- 未収金(移管件数)
,      SUM(IFNULL(AD_TransferAmount, 0)) AS AD_TransferAmount                                      -- 未収金(移管金額)
,      SUM(IFNULL(AD_AccountsPayableOffset, 0)) AS AD_AccountsPayableOffset                        -- 未収金(未払金金相殺)
,      SUM(IFNULL(AD_AccountsMonthCount, 0)) AS AD_AccountsMonthCount                              -- 未収金(当月入金件数)
,      SUM(IFNULL(AD_AccountsMonthPayment, 0)) AS AD_AccountsMonthPayment                          -- 未収金(当月入金)
,      SUM(IFNULL(AD_BadDebtCount, 0)) AS AD_BadDebtCount                                          -- 未収金(貸倒金額)
,      SUM(IFNULL(AD_BadDebtAmount, 0)) AS AD_BadDebtAmount                                        -- 未収金(貸倒金額)
,      SUM(IFNULL(AD_AccountsDueBalance, 0)) +
         SUM(IFNULL(AD_TransferAmount, 0)) +
         SUM(IFNULL(AD_AccountsPayableOffset, 0)) +
         SUM(IFNULL(AD_AccountsMonthPayment, 0)) +
         SUM(IFNULL(AD_BadDebtAmount, 0)) AS AD_PerAccountsDueBalance                              -- 未収金(当月末未収金残)
,      SUM(IFNULL(AD_CurrentAccountsDue, 0)) AS AD_CurrentAccountsDue                              -- 未収金(現在未収金)
,      SUM(IFNULL(AD_AccountsDueBalance, 0)) +
         SUM(IFNULL(AD_TransferAmount, 0)) +
         SUM(IFNULL(AD_AccountsPayableOffset, 0)) +
         SUM(IFNULL(AD_AccountsMonthPayment, 0)) +
         SUM(IFNULL(AD_BadDebtAmount, 0)) -
         SUM(IFNULL(AD_CurrentAccountsDue, 0)) AS AD_Difference                                    -- 未収金(差額)
,      NOW() AS RegistDate                                                                         -- 登録日時
,      1 AS RegistId                                                                               -- 登録者
,      NOW() AS UpdateDate                                                                         -- 更新日時
,      1 AS UpdateId                                                                               -- 更新者
,      1 AS ValidFlg                                                                               -- 有効ﾌﾗｸﾞ
FROM   (
        /* ②精算日計 */
        SELECT at_pda.OemId AS OemId
        ,      at_pda.EnterpriseId AS EnterpriseId
        ,      NULL AS AP_AccountsPayableBalance
        ,      NULL AS AP_ChargeCount
        ,      NULL AS AP_ChargeAmount
        ,      NULL AS AP_CancelCount
        ,      NULL AS AP_CalcelAmount
        ,      NULL AS AP_SettlementBackCount
        ,      NULL AS AP_SettlementBackAmount
        ,      at_pda.AccountsReceivableTotal AS AP_AccountsReceivableOffset
        ,      at_pda.AccountsDueOffsetAmount AS AP_AccountsPayableOffset
        ,      IFNULL(at_pda.StampFee, 0) + IFNULL(at_pda.TransferCommission, 0) + IFNULL(at_pda.AdjustmentAmount, 0) + IFNULL(at_pda.EnterpriseRefund, 0) AS AP_OtherAccountsPayableOffset
        ,      at_pda.EnterpriseAccountsDue AS AP_AccountsPayableTransfer
        ,      CASE WHEN at_pda.AdvancesAmount > 0 THEN 1 ELSE 0 END AS AP_AmountPaymentCount
        ,      (-1) * at_pda.AdvancesAmount AS AP_AmountPayment
        ,      NULL AS AP_BadDebtAmountCount
        ,      NULL AS AP_BadDebtAmount
        ,      NULL AS AP_PreAccountsPayableBalance
        ,      NULL AS AP_UseAmountTotal
        ,      NULL AS AP_Difference
        ,      (-1) * (IFNULL(at_pda.StampFee, 0) + IFNULL(at_pda.TransferCommission, 0) + IFNULL(at_pda.AdjustmentAmount, 0) + IFNULL(at_pda.EnterpriseRefund, 0)) AS Other_AccountsPayableOffset
        ,      (CASE WHEN at_pda.StampFee <> 0 THEN 1 ELSE 0 END) AS Other_StampFeeCount
        ,      (-1) * at_pda.StampFee AS Other_StampFee
        ,      (CASE WHEN at_pda.TransferCommission <> 0 THEN 1 ELSE 0 END) AS Other_TransferCommissionCount
        ,      (-1) * at_pda.TransferCommission AS Other_TransferCommission
        ,      (CASE WHEN at_pda.AdjustmentAmount <> 0 THEN 1 ELSE 0 END) AS Other_AdjustmentAmountCount
        ,      (-1) * at_pda.AdjustmentAmount AS Other_AdjustmentAmount
        ,      (CASE WHEN at_pda.EnterpriseRefund <> 0 THEN 1 ELSE 0 END) AS Other_RefundCount
        ,      (-1) * at_pda.EnterpriseRefund AS Other_Refund
        ,      NULL AS AR_AccountsReceivableBalance
        ,      NULL AS AR_AccountsReceivableIncrease
        ,      NULL AS AR_SettlementFeeCount
        ,      NULL AS AR_SettlementFeeAmount
        ,      NULL AS AR_ClaimFeeCount
        ,      NULL AS AR_ClaimFeeAmount
        ,      NULL AS AR_MonthlyFee
        ,      NULL AS AR_IncludeMonthlyFee
        ,      NULL AS AR_ApiMonthlyFee
        ,      NULL AS AR_CreditNoticeMonthlyFee
        ,      NULL AS AR_NCreditNoticeMonthlyFee
        ,      NULL AS AR_ReserveMonthlyFee
        ,      at_pda.AccountsReceivableTotal AS AR_AccountsPayableOffset
        ,      at_pda.EnterpriseAccountsDue AS AR_AccountsPayableTransfer
        ,      NULL AS AR_AccountsMonthPaymentCount
        ,      NULL AS AR_AccountsMonthPayment
        ,      NULL AS AR_BadDebtAmountCount
        ,      NULL AS AR_BadDebtAmount
        ,      NULL AS AR_PreAccountsReceivableBalance
        ,      NULL AS AR_AccountsReceivableToal
        ,      NULL AS AR_Difference
        ,      NULL AS AD_AccountsDueBalance
        ,      (CASE WHEN EnterpriseAccountsDue <> 0 THEN 1 ELSE 0 END) AS AD_TransferAmountCount
        ,      EnterpriseAccountsDue AS AD_TransferAmount
        ,      at_pda.AccountsDueOffsetAmount AS AD_AccountsPayableOffset
        ,      NULL AS AD_AccountsMonthCount
        ,      NULL AS AD_AccountsMonthPayment
        ,      NULL AS AD_BadDebtCount
        ,      NULL AS AD_BadDebtAmount
        ,      NULL AS AD_PerAccountsDueBalance
        ,      NULL AS AD_CurrentAccountsDue
        FROM   AT_PayOff_DailyAccount at_pda
               INNER JOIN M_Code cod ON (cod.KeyCode = at_pda.OemId)
        WHERE  cod.CodeId = 160
        AND    cod.Class1 = 0
        AND    at_pda.ProcessingDate >= DATE_FORMAT(:AccountDate, '%Y-%m-%d') AND at_pda.ProcessingDate < DATE_FORMAT(:AccountDate + INTERVAL 1 MONTH, '%Y-%m-%d')
        /* ③直営日次統計表(AT_DailyStatisticsTable) */
        UNION ALL
        SELECT 0 -- OemId
        ,      EnterpriseId
        ,      NULL
        ,      M_ChargeCount
        ,      M_ChargeAmount
        ,      M_CancelCount
        ,      M_CancelAmount
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      0
        ,      M_SettlementFeeTotal
        ,      0
        ,      M_ClaimFeeTotal + M_ClaimFeeTaxTotal
        ,      M_MonthlyFeeTotal + M_MonthlyFeeTaxTotal
        ,      M_IncludeMonthlyFeeTotal + M_IncludeMonthlyFeeTaxTotal
        ,      M_ApiMonthlyFeeTotal + M_ApiMonthlyFeeTaxTotal
        ,      M_CreditNoticeMonthlyFeeTotal + M_CreditNoticeMonthlyFeeTaxTotal
        ,      M_NCreditNoticeMonthlyFeeTotal + M_NCreditNoticeMonthlyFeeTaxTotal
        ,      0
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        FROM   AT_DailyStatisticsTable
        WHERE  AccountDate = :AccountDate
        AND    DailyMonthlyFlg = 1
        AND    EnterpriseId <> 99999999
        /* ④無保証立替金戻し明細 */
        UNION ALL
        SELECT at_asb.OemId
        ,      at_asb.EnterpriseId
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      CASE WHEN IFNULL(at_asb.ClaimAmount, 0) <> 0 THEN 1 ELSE 0 END
        ,      at_asb.ClaimAmount
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        FROM   AT_AdvancesSettlementBack at_asb
               INNER JOIN M_Code cod ON (cod.KeyCode = at_asb.OemId)
        WHERE  cod.CodeId = 160
        AND    cod.Class1 = 0
        AND    at_asb.AccountDate = :AccountDate
        /* ⑤貸倒債権一覧(加盟店) */
        UNION ALL
        SELECT at_bdl.OemId
        ,      at_bdl.EnterpriseId
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      1
        ,      (IFNULL(at_bdl.CrediAmountDue, 0)) * -1
        ,      NULL
        ,      NULL
        FROM   AT_BadDebt_List at_bdl
               INNER JOIN M_Code cod ON (cod.KeyCode = at_bdl.OemId)
        WHERE  cod.CodeId = 160
        AND    cod.Class1 = 0
        AND    at_bdl.AccountDate = :AccountDate
        AND    at_bdl.CrediTarget = '加盟店'
        /* ⑥未収金日計明細(加盟店未収金) */
        UNION ALL
        SELECT at_adad.OemId
        ,      at_adad.EnterpriseId
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      1
        ,      at_adad.Amount
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        FROM   AT_AccountsDue_DailyAccountDetails at_adad
               INNER JOIN M_Code cod ON (cod.KeyCode = at_adad.OemId)
        WHERE  cod.CodeId = 160
        AND    cod.Class1 = 0
        AND    at_adad.AccountDate = :AccountDate
        AND    at_adad.PaymentTargetAccountTitle = '加盟店未収金'
        /* ⑧月初残高管理 */
        UNION ALL
        SELECT 0
        ,      EnterpriseId
        ,      AP_PreAccountsPayableBalance
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      AR_PreAccountsReceivableBalance
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      AD_PerAccountsDueBalance
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        FROM   AT_Accounts_PayableStatisticsTable
        WHERE  AccountDate =  DATE_FORMAT(:AccountDate - INTERVAL 1 MONTH, '%Y-%m-01')
        AND    EnterpriseId <> 99999999
        /* ⑨直営未払金･売掛金･加盟店未収金明細 */
        UNION ALL
        SELECT c.Class3
        ,      at_cbapr.EnterpriseId
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      at_cbapr.UseAmount
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      at_cbapr.AccountsReceivableTotal
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      (-1) * at_cbapr.AccountsDue
        FROM   AT_Cb_Accounts_PayableReceivable at_cbapr
               LEFT JOIN T_Enterprise e ON (at_cbapr.EnterpriseId = e.EnterpriseId)
               LEFT JOIN M_Code c ON (c.CodeId = 160 AND c.KeyCode = IFNULL(e.OemId, 0))
        WHERE  at_cbapr.AccountDate = :AccountDate
        AND    c.Class1 = 0
       ) aw_aps
       LEFT JOIN T_Enterprise e ON (aw_aps.EnterpriseId = e.EnterpriseId)
GROUP BY aw_aps.EnterpriseId
EOQ;
    }

    /**
     * (サマリー限定)直営未払金・売掛金・加盟店未収金統計表取得ベースSQL
     *
     * @return string
     */
    protected function getCBMibaraiTokeiBaseQuery_Summary() {
        return <<<EOQ
SELECT  '' AS Seq
    ,   DailyMonthlyFlg
    ,   MIN(ProcessingDate) AS ProcessingDate
    ,   MIN(AccountDate) AS AccountDate
    ,   NULL AS EnterpriseId
    ,   '総合計' AS EnterpriseNameKj
    ,   SUM(IFNULL(AP_AccountsPayableBalance, 0)) AS AP_AccountsPayableBalance
    ,   SUM(IFNULL(AP_ChargeCount, 0)) AS AP_ChargeCount
    ,   SUM(IFNULL(AP_ChargeAmount, 0)) AS AP_ChargeAmount
    ,   SUM(IFNULL(AP_CancelCount, 0)) AS AP_CancelCount
    ,   SUM(IFNULL(AP_CalcelAmount, 0)) AS AP_CalcelAmount
    ,   SUM(IFNULL(AP_SettlementBackCount, 0)) AS AP_SettlementBackCount
    ,   SUM(IFNULL(AP_SettlementBackAmount, 0)) AS AP_SettlementBackAmount
    ,   SUM(IFNULL(AP_AccountsReceivableOffset, 0)) AS AP_AccountsReceivableOffset
    ,   SUM(IFNULL(AP_AccountsPayableOffset, 0)) AS AP_AccountsPayableOffset
    ,   SUM(IFNULL(AP_OtherAccountsPayableOffset, 0)) AS AP_OtherAccountsPayableOffset
    ,   SUM(IFNULL(AP_AccountsPayableTransfer, 0)) AS AP_AccountsPayableTransfer
    ,   SUM(IFNULL(AP_AmountPaymentCount, 0)) AS AP_AmountPaymentCount
    ,   SUM(IFNULL(AP_AmountPayment, 0)) AS AP_AmountPayment
    ,   SUM(IFNULL(AP_BadDebtAmountCount, 0)) AS AP_BadDebtAmountCount
    ,   SUM(IFNULL(AP_BadDebtAmount, 0)) AS AP_BadDebtAmount
    ,   SUM(IFNULL(AP_PreAccountsPayableBalance, 0)) AS AP_PreAccountsPayableBalance
    ,   SUM(IFNULL(AP_UseAmountTotal, 0)) AS AP_UseAmountTotal
    ,   SUM(IFNULL(AP_Difference, 0)) AS AP_Difference
    ,   SUM(IFNULL(Other_AccountsPayableOffset, 0)) AS Other_AccountsPayableOffset
    ,   SUM(IFNULL(Other_StampFeeCount, 0)) Other_StampFeeCount
    ,   SUM(IFNULL(Other_StampFee, 0)) AS Other_StampFee
    ,   SUM(IFNULL(Other_TransferCommissionCount, 0)) AS Other_TransferCommissionCount
    ,   SUM(IFNULL(Other_TransferCommission, 0)) AS Other_TransferCommission
    ,   SUM(IFNULL(Other_AdjustmentAmountCount, 0)) AS Other_AdjustmentAmountCount
    ,   SUM(IFNULL(Other_AdjustmentAmount, 0)) AS Other_AdjustmentAmount
    ,   SUM(IFNULL(Other_RefundCount, 0)) AS Other_RefundCount
    ,   SUM(IFNULL(Other_Refund, 0)) AS Other_Refund
    ,   SUM(IFNULL(AR_AccountsReceivableBalance, 0)) AS AR_AccountsReceivableBalance
    ,   SUM(IFNULL(AR_AccountsReceivableIncrease, 0)) AS AR_AccountsReceivableIncrease
    ,   SUM(IFNULL(AR_SettlementFeeCount, 0)) AS AR_SettlementFeeCount
    ,   SUM(IFNULL(AR_SettlementFeeAmount, 0)) AS AR_SettlementFeeAmount
    ,   SUM(IFNULL(AR_ClaimFeeCount, 0)) AS AR_ClaimFeeCount
    ,   SUM(IFNULL(AR_ClaimFeeAmount, 0)) AS AR_ClaimFeeAmount
    ,   SUM(IFNULL(AR_MonthlyFee, 0)) AS AR_MonthlyFee
    ,   SUM(IFNULL(AR_IncludeMonthlyFee, 0)) AS AR_IncludeMonthlyFee
    ,   SUM(IFNULL(AR_ApiMonthlyFee, 0)) AS AR_ApiMonthlyFee
    ,   SUM(IFNULL(AR_CreditNoticeMonthlyFee, 0)) AS AR_CreditNoticeMonthlyFee
    ,   SUM(IFNULL(AR_NCreditNoticeMonthlyFee, 0)) AS AR_NCreditNoticeMonthlyFee
    ,   SUM(IFNULL(AR_AccountsPayableOffset, 0)) AS AR_AccountsPayableOffset
    ,   SUM(IFNULL(AR_AccountsPayableTransfer, 0)) AS AR_AccountsPayableTransfer
    ,   SUM(IFNULL(AR_AccountsMonthPaymentCount, 0)) AS AR_AccountsMonthPaymentCount
    ,   SUM(IFNULL(AR_AccountsMonthPayment, 0)) AS AR_AccountsMonthPayment
    ,   SUM(IFNULL(AR_BadDebtAmountCount, 0)) AS AR_BadDebtAmountCount
    ,   SUM(IFNULL(AR_BadDebtAmount, 0)) AS AR_BadDebtAmount
    ,   SUM(IFNULL(AR_PreAccountsReceivableBalance, 0)) AS AR_PreAccountsReceivableBalance
    ,   SUM(IFNULL(AR_AccountsReceivableToal, 0)) AS AR_AccountsReceivableToal
    ,   SUM(IFNULL(AR_Difference, 0)) AS AR_Difference
    ,   SUM(IFNULL(AD_AccountsDueBalance, 0)) AS AD_AccountsDueBalance
    ,   SUM(IFNULL(AD_TransferAmountCount, 0)) AS AD_TransferAmountCount
    ,   SUM(IFNULL(AD_TransferAmount, 0)) AS AD_TransferAmount
    ,   SUM(IFNULL(AD_AccountsPayableOffset, 0)) AS AD_AccountsPayableOffset
    ,   SUM(IFNULL(AD_AccountsMonthCount, 0)) AS AD_AccountsMonthCount
    ,   SUM(IFNULL(AD_AccountsMonthPayment, 0)) AS AD_AccountsMonthPayment
    ,   SUM(IFNULL(AD_BadDebtCount, 0)) AS AD_BadDebtCount
    ,   SUM(IFNULL(AD_BadDebtAmount, 0)) AS AD_BadDebtAmount
    ,   SUM(IFNULL(AD_PerAccountsDueBalance, 0)) AS AD_PerAccountsDueBalance
    ,   SUM(IFNULL(AD_CurrentAccountsDue, 0)) AS AD_CurrentAccountsDue
    ,   SUM(IFNULL(AD_Difference, 0)) AS AD_Difference
FROM    AT_Accounts_PayableStatisticsTable
WHERE   DailyMonthlyFlg = 1
AND     AccountDate = :AccountDate
%s
GROUP BY
        DailyMonthlyFlg
EOQ;
    }

    /**
     * OEM未払金・売掛金・OEM未収金統計表取得ベースSQL
     *
     * @return string
     */
    protected function getOEMMibaraiTokeiBaseQuery() {
        return <<<EOQ
SELECT  Seq
    ,   DailyMonthlyFlg
    ,   ProcessingDate
    ,   AccountDate
    ,   OemId
    ,   OemNameKj
    ,   EnterpriseId
    ,   EnterpriseNameKj
    ,   AP_AccountsPayableBalance
    ,   AP_ChargeCount
    ,   AP_ChargeAmount
    ,   AP_CancelCount
    ,   AP_CalcelAmount
    ,   AP_SettlementBackCount
    ,   AP_SettlementBackAmount
    ,   AP_AccountsReceivableOffset
    ,   AP_AccountsPayableOffset
    ,   AP_OtherAccountsPayableOffset
    ,   AP_AccountsPayableTransfer
    ,   AP_AmountPaymentCount
    ,   AP_AmountPayment
    ,   AP_BadDebtAmountCount
    ,   AP_BadDebtAmount
    ,   AP_PreAccountsPayableBalance
    ,   AP_UseAmountTotal
    ,   AP_Difference
    ,   Other_AccountsPayableOffset
    ,   Other_StampFeeCount
    ,   Other_StampFee
    ,   Other_TransferCommissionCount
    ,   Other_TransferCommission
    ,   Other_AdjustmentAmountCount
    ,   Other_AdjustmentAmount
    ,   Other_RefundCount
    ,   Other_Refund
    ,   AR_AccountsReceivableBalance
    ,   AR_AccountsReceivableIncrease
    ,   AR_OemSettlementFeeCount
    ,   AR_OemSettlementFeeAmount
    ,   AR_OemClaimFeeCount
    ,   AR_OemClaimFeeAmount
    ,   AR_OemMonthlyFee
    ,   AR_OemIncludeMonthlyFee
    ,   AR_OemApiMonthlyFee
    ,   AR_OemCreditNoticeMonthlyFee
    ,   AR_OemNCreditNoticeMonthlyFee
    ,   AR_OemReserveMonthlyFee
    ,   AR_AccountsPayableOffset
    ,   AR_AccountsPayableTransfer
    ,   AR_AccountsMonthPayment
    ,   AR_BadDebtAmount
    ,   AR_PreAccountsReceivableBalance
    ,   AR_AccountsReceivableToal
    ,   AR_Difference
    ,   AD_AccountsDueBalance
    ,   AD_TransferAmountCount
    ,   AD_TransferAmount
    ,   AD_AccountsPayableOffset
    ,   AD_AccountsMonthCount
    ,   AD_AccountsMonthPayment
    ,   AD_BadDebtCount
    ,   AD_BadDebtAmount
    ,   AD_PerAccountsDueBalance
    ,   AD_CurrentAccountsDue
    ,   AD_Difference
FROM    AT_OemAccounts_PayableStatisticsTable
WHERE   DailyMonthlyFlg = 1
AND     AccountDate = :AccountDate
%s
ORDER BY
        IFNULL(OemId, 9999999)
    ,   EnterpriseId
EOQ;
    }

    /**
     * OEM未払金・売掛金・OEM未収金統計表取得ベースSQL
     * (Oem単位版)
     *
     * @return string
     */
    protected function getOEMMibaraiTokeiBaseQueryByOem() {
        return <<<EOQ
SELECT 1 AS DailyMonthlyFlg                                                                        -- 日次･月次区分
,      F_GetSystemProperty('[DEFAULT]', 'systeminfo', 'BusinessDate') AS ProcessingDate            -- 処理日付
,      :AccountDate AS AccountDate                                                                 -- 会計月
,      aw_aps.OemId AS OemId                                                                       -- OEMID
,      c.class2 AS OemNameKj                                                                       -- OEM先名
,      aw_aps.EnterpriseId AS EnterpriseId                                                         -- 加盟店ID
,      e.EnterpriseNameKj AS EnterpriseNameKj                                                      -- 加盟店名
,      0 AS AP_AccountsPayableBalance                                                              -- 未払金(前月未払金残高)
,      SUM(IFNULL(AP_ChargeCount, 0)) AS AP_ChargeCount                                            -- 未払金(当月発生件数)
,      SUM(IFNULL(AP_ChargeAmount, 0)) AS AP_ChargeAmount                                          -- 未払金(当月発生金額)
,      SUM(IFNULL(AP_CancelCount, 0)) AS AP_CancelCount                                            -- 未払金(当月ｷｬﾝｾﾙ件数)
,      SUM(IFNULL(AP_CalcelAmount, 0)) AS AP_CalcelAmount                                          -- 未払金(当月ｷｬﾝｾﾙ立替金額)
,      SUM(IFNULL(AP_SettlementBackCount, 0)) AS AP_SettlementBackCount                            -- 未払金(当月立替金戻し件数)
,      SUM(IFNULL(AP_SettlementBackAmount, 0)) AS AP_SettlementBackAmount                          -- 未払金(当月立替金戻し金額)
,      SUM(IFNULL(AP_AccountsReceivableOffset, 0)) AS AP_AccountsReceivableOffset                  -- 未払金(売掛金相殺)
,      0 AS AP_AccountsPayableOffset                                                               -- 未払金(未収金相殺)
,      SUM(IFNULL(AP_OtherAccountsPayableOffset, 0)) AS AP_OtherAccountsPayableOffset              -- 未払金(その他未収金相殺)
,      0 AS AP_AccountsPayableTransfer                                                             -- 未払金(未収金へ振替)
,      0 AS AP_AmountPaymentCount                                                                  -- 未払金(当月実支払件数)
,      0 AS AP_AmountPayment                                                                       -- 未払金(当月実支払額)
,      SUM(IFNULL(AP_BadDebtAmountCount, 0)) AS AP_BadDebtAmountCount                              -- 未払金(貸倒件数)
,      SUM(IFNULL(AP_BadDebtAmount, 0)) AS AP_BadDebtAmount                                        -- 未払金(貸倒金額)
,      0 AS AP_PreAccountsPayableBalance                                                           -- 未払金(当月未払金残)
,      SUM(IFNULL(AP_UseAmountTotal, 0)) AS AP_UseAmountTotal                                      -- 未払金(商品代金等合計)
,      0 AS AP_Difference                                                                          -- 未払金(差額)
,      SUM(IFNULL(Other_StampFee, 0)) +
         SUM(IFNULL(Other_TransferCommission, 0)) +
         SUM(IFNULL(Other_AdjustmentAmount, 0)) +
         SUM(IFNULL(Other_Refund, 0)) AS Other_AccountsPayableOffset                               -- その他未収金相殺(合計)
,      SUM(IFNULL(Other_StampFeeCount, 0)) AS Other_StampFeeCount                                  -- その他未収金相殺(印紙代件数)
,      SUM(IFNULL(Other_StampFee, 0)) AS Other_StampFee                                            -- その他未収金相殺(印紙代)
,      SUM(IFNULL(Other_TransferCommissionCount, 0)) AS Other_TransferCommissionCount              -- その他未収金相殺(振込手数料件数)
,      SUM(IFNULL(Other_TransferCommission, 0)) AS Other_TransferCommission                        -- その他未収金相殺(振込手数料)
,      SUM(IFNULL(Other_AdjustmentAmountCount, 0)) AS Other_AdjustmentAmountCount                  -- その他未収金相殺(調整金額件数)
,      SUM(IFNULL(Other_AdjustmentAmount, 0)) AS Other_AdjustmentAmount                            -- その他未収金相殺(調整金額)
,      SUM(IFNULL(Other_RefundCount, 0)) AS Other_RefundCount                                      -- その他未収金相殺(返金件数)
,      SUM(IFNULL(Other_Refund, 0)) AS Other_Refund                                                -- その他未収金相殺(返金)
,      0 AS AR_AccountsReceivableBalance                                                           -- 売掛金(前月売掛金残)
,      SUM(IFNULL(AR_OemSettlementFeeAmount, 0)) +
         SUM(IFNULL(AR_OemClaimFeeAmount, 0)) +
         SUM(IFNULL(AR_OemMonthlyFee, 0)) +
         SUM(IFNULL(AR_OemIncludeMonthlyFee, 0)) +
         SUM(IFNULL(AR_OemApiMonthlyFee, 0)) +
         SUM(IFNULL(AR_OemCreditNoticeMonthlyFee, 0)) +
         SUM(IFNULL(AR_OemNCreditNoticeMonthlyFee, 0)) +
         SUM(IFNULL(AR_OemReserveMonthlyFee, 0)) AS AR_AccountsReceivableIncrease                  -- 売掛金(当月売上増加)
,      SUM(IFNULL(AR_OemSettlementFeeCount, 0)) AS AR_OemSettlementFeeCount                        -- 売掛金(OEM決済手数料件数)
,      SUM(IFNULL(AR_OemSettlementFeeAmount, 0)) AS AR_OemSettlementFeeAmount                      -- 売掛金(OEM決済手数料金額)
,      SUM(IFNULL(AR_OemClaimFeeCount, 0)) AS AR_OemClaimFeeCount                                  -- 売掛金(OEM請求手数料件数)
,      SUM(IFNULL(AR_OemClaimFeeAmount, 0)) AS AR_OemClaimFeeAmount                                -- 売掛金(OEM請求手数料金額)
,      SUM(IFNULL(AR_OemMonthlyFee, 0)) AS AR_OemMonthlyFee                                        -- 売掛金(OEM月額固定費)
,      SUM(IFNULL(AR_OemIncludeMonthlyFee, 0)) AS AR_OemIncludeMonthlyFee                          -- 売掛金(OEM同梱月額固定費)
,      SUM(IFNULL(AR_OemApiMonthlyFee, 0)) AS AR_OemApiMonthlyFee                                  -- 売掛金(OEMAPI月額固定費)
,      SUM(IFNULL(AR_OemCreditNoticeMonthlyFee, 0)) AS AR_OemCreditNoticeMonthlyFee                -- 売掛金(OEM与信結果通知ｻｰﾋﾞｽ月額固定費)
,      SUM(IFNULL(AR_OemNCreditNoticeMonthlyFee, 0)) AS AR_OemNCreditNoticeMonthlyFee              -- 売掛金(OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費)
,      SUM(IFNULL(AR_OemReserveMonthlyFee, 0)) AS AR_OemReserveMonthlyFee                          -- 売掛金(OEM月額固定費予備)
,      SUM(IFNULL(AR_AccountsPayableOffset, 0)) AS AR_AccountsPayableOffset                        -- 売掛金(OEM未払金相殺)
,      SUM(IFNULL(AR_AccountsPayableTransfer, 0)) AS AR_AccountsPayableTransfer                    -- 売掛金(OEM未収金へ振替)
,      SUM(IFNULL(AR_AccountsMonthPaymentCount, 0)) AS AR_AccountsMonthPaymentCount                -- 売掛金(当月入金件数)
,      SUM(IFNULL(AR_AccountsMonthPayment, 0)) AS AR_AccountsMonthPayment                          -- 売掛金(当月入金)
,      SUM(IFNULL(AR_BadDebtAmountCount, 0)) AS AR_BadDebtAmountCount                              -- 売掛金(貸倒件数)
,      SUM(IFNULL(AR_BadDebtAmount, 0)) AS AR_BadDebtAmount                                        -- 売掛金(貸倒金額)
,      0 AS AR_PreAccountsReceivableBalance                                                        -- 売掛金(当月末売掛金残)
,      SUM(IFNULL(AR_AccountsReceivableToal, 0)) AS AR_AccountsReceivableToal                      -- 売掛金(売掛金合計)
,      0 AS AR_Difference                                                                          -- 売掛金(差額)
,      0 AS AD_AccountsDueBalance                                                                  -- 未収金(前月未収金残)
,      0 AS AD_TransferAmountCount                                                                 -- 未収金(移管件数)
,      0 AS AD_TransferAmount                                                                      -- 未収金(移管金額)
,      SUM(IFNULL(AD_AccountsPayableOffset, 0)) AS AD_AccountsPayableOffset                        -- 未収金(未払金金相殺)
,      SUM(IFNULL(AD_AccountsMonthCount, 0)) AS AD_AccountsMonthCount                              -- 未収金(当月入金件数)
,      SUM(IFNULL(AD_AccountsMonthPayment, 0)) AS AD_AccountsMonthPayment                          -- 未収金(当月入金)
,      SUM(IFNULL(AD_BadDebtCount, 0)) AS AD_BadDebtCount                                          -- 未収金(貸倒金額)
,      SUM(IFNULL(AD_BadDebtAmount, 0)) AS AD_BadDebtAmount                                        -- 未収金(貸倒金額)
,      0 AS AD_PerAccountsDueBalance                                                               -- 未収金(当月末未収金残)
,      SUM(IFNULL(AD_CurrentAccountsDue, 0)) AS AD_CurrentAccountsDue                              -- 未収金(現在未収金)
,      0 AS AD_Difference                                                                          -- 未収金(差額)
,      NOW() AS RegistDate                                                                         -- 登録日時
,      1 AS RegistId                                                                               -- 登録者
,      NOW() AS UpdateDate                                                                         -- 更新日時
,      1 AS UpdateId                                                                               -- 更新者
,      1 AS ValidFlg                                                                               -- 有効ﾌﾗｸﾞ
FROM   (
        /* ②精算日計 */
        SELECT at_pda.OemId AS OemId
        ,      at_pda.EnterpriseId AS EnterpriseId
        ,      NULL AS AP_AccountsPayableBalance                     -- □当月末残保管(未払金／売掛金／未収金) ※Oem単位
        ,      NULL AS AP_ChargeCount
        ,      NULL AS AP_ChargeAmount
        ,      NULL AS AP_CancelCount
        ,      NULL AS AP_CalcelAmount
        ,      NULL AS AP_SettlementBackCount
        ,      NULL AS AP_SettlementBackAmount
        ,      at_pda.AccountsReceivableTotal AS AP_AccountsReceivableOffset
        ,      0 AS AP_AccountsPayableOffset                         -- ■AT_PayOff_DailyAccount2対象項目
        ,      IFNULL(at_pda.StampFee, 0) + IFNULL(at_pda.AdjustmentAmount, 0) + IFNULL(at_pda.EnterpriseRefund, 0) AS AP_OtherAccountsPayableOffset
        ,      0 AS AP_AccountsPayableTransfer                       -- ■AT_PayOff_DailyAccount2対象項目
        ,      0 AS AP_AmountPaymentCount                            -- ■0固定(※帳票上非表示)
        ,      0 AS AP_AmountPayment                                 -- ■AT_PayOff_DailyAccount2対象項目
        ,      NULL AS AP_BadDebtAmountCount
        ,      NULL AS AP_BadDebtAmount
        ,      NULL AS AP_PreAccountsPayableBalance
        ,      NULL AS AP_UseAmountTotal
        ,      NULL AS AP_Difference
        ,      (-1) * IFNULL(at_pda.StampFee, 0) + IFNULL(at_pda.AdjustmentAmount, 0) + IFNULL(at_pda.EnterpriseRefund, 0) AS Other_AccountsPayableOffset
        ,      (CASE WHEN at_pda.StampFee <> 0 THEN 1 ELSE 0 END) AS Other_StampFeeCount
        ,      (-1) * at_pda.StampFee AS Other_StampFee
        ,      0 AS Other_TransferCommissionCount
        ,      (-1) * 0 AS Other_TransferCommission
        ,      (CASE WHEN at_pda.AdjustmentAmount <> 0 THEN 1 ELSE 0 END) AS Other_AdjustmentAmountCount
        ,      (-1) * at_pda.AdjustmentAmount AS Other_AdjustmentAmount
        ,      (CASE WHEN at_pda.EnterpriseRefund <> 0 THEN 1 ELSE 0 END) AS Other_RefundCount
        ,      (-1) * at_pda.EnterpriseRefund AS Other_Refund
        ,      NULL AS AR_AccountsReceivableBalance                  -- □当月末残保管(未払金／売掛金／未収金) ※Oem単位
        ,      NULL AS AR_AccountsReceivableIncrease
        ,      NULL AS AR_OemSettlementFeeCount
        ,      NULL AS AR_OemSettlementFeeAmount
        ,      NULL AS AR_OemClaimFeeCount
        ,      NULL AS AR_OemClaimFeeAmount
        ,      NULL AS AR_OemMonthlyFee
        ,      NULL AS AR_OemIncludeMonthlyFee
        ,      NULL AS AR_OemApiMonthlyFee
        ,      NULL AS AR_OemCreditNoticeMonthlyFee
        ,      NULL AS AR_OemNCreditNoticeMonthlyFee
        ,      NULL AS AR_OemReserveMonthlyFee
        ,      at_pda.AccountsReceivableTotal AS AR_AccountsPayableOffset
        ,      0 AS AR_AccountsPayableTransfer
        ,      NULL AS AR_AccountsMonthPaymentCount
        ,      NULL AS AR_AccountsMonthPayment
        ,      NULL AS AR_BadDebtAmountCount
        ,      NULL AS AR_BadDebtAmount
        ,      NULL AS AR_PreAccountsReceivableBalance
        ,      NULL AS AR_AccountsReceivableToal
        ,      NULL AS AR_Difference
        ,      NULL AS AD_AccountsDueBalance                         -- □当月末残保管(未払金／売掛金／未収金) ※Oem単位
        ,      0 AS AD_TransferAmountCount                           -- ■0固定(※帳票上非表示)
        ,      0 AS AD_TransferAmount                                -- ■AT_PayOff_DailyAccount2対象項目
        ,      0 AS AD_AccountsPayableOffset                         -- ■AT_PayOff_DailyAccount2対象項目
        ,      NULL AS AD_AccountsMonthCount
        ,      NULL AS AD_AccountsMonthPayment
        ,      NULL AS AD_BadDebtCount
        ,      NULL AS AD_BadDebtAmount
        ,      NULL AS AD_PerAccountsDueBalance
        ,      NULL AS AD_CurrentAccountsDue
        FROM   AT_PayOff_DailyAccount at_pda
        WHERE  at_pda.ProcessingDate >= DATE_FORMAT(:AccountDate, '%Y-%m-%d') AND at_pda.ProcessingDate < DATE_FORMAT(:AccountDate + INTERVAL 1 MONTH, '%Y-%m-%d')
        AND    at_pda.OemId = :OemId
        /* ③OEM日次統計表(AT_Oem_DailyStatisticsTable) */
        UNION ALL
        SELECT OemId
        ,      EnterpriseId
        ,      NULL
        ,      M_ChargeCount
        ,      M_ChargeAmount
        ,      M_CancelCount
        ,      M_CancelAmount
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      0
        ,      M_SettlementFeeTotal + M_SettlementFeeTaxTotal
        ,      0
        ,      M_ClaimFeeTotal + M_ClaimFeeTaxTotal
        ,      M_MonthlyFeeTotal + M_MonthlyFeeTaxTotal
        ,      M_OemIncludeMonthlyFeeTotal + M_OemIncludeMonthlyFeeTaxTotal
        ,      M_OemApiMonthlyFeeTotal + M_OemApiMonthlyFeeTaxTotal
        ,      M_OemCreditNoticeMonthlyFeeTotal + M_OemCreditNoticeMonthlyFeeTaxTotal
        ,      M_OemNCreditNoticeMonthlyFeeTotal + M_OemNCreditNoticeMonthlyFeeTaxTotal
        ,      0
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        FROM   AT_Oem_DailyStatisticsTable-- at_odst
        WHERE  AccountDate = :AccountDate
        AND    OemId = :OemId
        AND    DailyMonthlyFlg = 1
        /* ④無保証立替金戻し明細 */
        UNION ALL
        SELECT at_asb.OemId
        ,      at_asb.EnterpriseId
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      CASE WHEN IFNULL(at_asb.ClaimAmount, 0) <> 0 THEN 1 ELSE 0 END
        ,      at_asb.ClaimAmount
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        FROM   AT_AdvancesSettlementBack at_asb
        WHERE  at_asb.AccountDate = :AccountDate
        AND    at_asb.OemId = :OemId
        /* ⑨OEM未払金･売掛金･加盟店未収金明細 */
        UNION ALL
        SELECT c.Class3
        ,      at_oemapr.EnterpriseId
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      at_oemapr.UseAmount
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      at_oemapr.AccountsReceivableTotal
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      NULL
        ,      at_oemapr.AccountsDue
        FROM   AT_Oem_Accounts_PayableReceivable at_oemapr
               LEFT JOIN T_Enterprise e ON (at_oemapr.EnterpriseId = e.EnterpriseId)
               LEFT JOIN M_Code c ON (c.CodeId = 160 AND c.KeyCode = IFNULL(e.OemId, 0))
        WHERE  at_oemapr.AccountDate = :AccountDate
        AND    at_oemapr.OemId = :OemId
        AND    c.Class1 <> 0
       ) aw_aps
       LEFT JOIN T_Enterprise e ON (aw_aps.EnterpriseId = e.EnterpriseId)
       LEFT JOIN M_Code c ON (c.CodeId = 160 AND c.KeyCode = aw_aps.OemId)
GROUP BY
       aw_aps.EnterpriseId
WITH ROLLUP
EOQ;
    }

    /**
     * (サマリー限定)OEM未払金・売掛金・OEM未収金統計表取得ベースSQL
     *
     * @return string
     */
    protected function getOEMMibaraiTokeiBaseQuery_Summary() {
        return <<<EOQ
SELECT  '' AS Seq
    ,   DailyMonthlyFlg
    ,   MIN(ProcessingDate) AS ProcessingDate
    ,   MIN(AccountDate) AS AccountDate
    ,   NULL AS OemId
    ,   NULL AS OemNameKj
    ,   NULL AS EnterpriseId
    ,   '総合計' AS EnterpriseNameKj
    ,   SUM(IFNULL(AP_AccountsPayableBalance, 0)) AS AP_AccountsPayableBalance
    ,   SUM(IFNULL(AP_ChargeCount, 0)) AS AP_ChargeCount
    ,   SUM(IFNULL(AP_ChargeAmount, 0)) AS AP_ChargeAmount
    ,   SUM(IFNULL(AP_CancelCount, 0)) AS AP_CancelCount
    ,   SUM(IFNULL(AP_CalcelAmount, 0)) AS AP_CalcelAmount
    ,   SUM(IFNULL(AP_SettlementBackCount, 0)) AS AP_SettlementBackCount
    ,   SUM(IFNULL(AP_SettlementBackAmount, 0)) AS AP_SettlementBackAmount
    ,   SUM(IFNULL(AP_AccountsReceivableOffset, 0)) AS AP_AccountsReceivableOffset
    ,   SUM(IFNULL(AP_AccountsPayableOffset, 0)) AS AP_AccountsPayableOffset
    ,   SUM(IFNULL(AP_OtherAccountsPayableOffset, 0)) AS AP_OtherAccountsPayableOffset
    ,   SUM(IFNULL(AP_AccountsPayableTransfer, 0)) AS AP_AccountsPayableTransfer
    ,   SUM(IFNULL(AP_AmountPaymentCount, 0)) AS AP_AmountPaymentCount
    ,   SUM(IFNULL(AP_AmountPayment, 0)) AS AP_AmountPayment
    ,   SUM(IFNULL(AP_BadDebtAmountCount, 0)) AS AP_BadDebtAmountCount
    ,   SUM(IFNULL(AP_BadDebtAmount, 0)) AS AP_BadDebtAmount
    ,   SUM(IFNULL(AP_PreAccountsPayableBalance, 0)) AS AP_PreAccountsPayableBalance
    ,   SUM(IFNULL(AP_UseAmountTotal, 0)) AS AP_UseAmountTotal
    ,   SUM(IFNULL(AP_Difference, 0)) AS AP_Difference
    ,   SUM(IFNULL(Other_AccountsPayableOffset, 0)) AS Other_AccountsPayableOffset
    ,   SUM(IFNULL(Other_StampFeeCount, 0)) AS Other_StampFeeCount
    ,   SUM(IFNULL(Other_StampFee, 0)) AS Other_StampFee
    ,   SUM(IFNULL(Other_TransferCommissionCount, 0)) AS Other_TransferCommissionCount
    ,   SUM(IFNULL(Other_TransferCommission, 0)) AS Other_TransferCommission
    ,   SUM(IFNULL(Other_AdjustmentAmountCount, 0)) AS Other_AdjustmentAmountCount
    ,   SUM(IFNULL(Other_AdjustmentAmount, 0)) AS Other_AdjustmentAmount
    ,   SUM(IFNULL(Other_RefundCount, 0)) AS Other_RefundCount
    ,   SUM(IFNULL(Other_Refund, 0)) AS Other_Refund
    ,   SUM(IFNULL(AR_AccountsReceivableBalance, 0)) AS AR_AccountsReceivableBalance
    ,   SUM(IFNULL(AR_AccountsReceivableIncrease, 0)) AS AR_AccountsReceivableIncrease
    ,   SUM(IFNULL(AR_OemSettlementFeeCount, 0)) AS AR_OemSettlementFeeCount
    ,   SUM(IFNULL(AR_OemSettlementFeeAmount, 0)) AS AR_OemSettlementFeeAmount
    ,   SUM(IFNULL(AR_OemClaimFeeCount, 0)) AS AR_OemClaimFeeCount
    ,   SUM(IFNULL(AR_OemClaimFeeAmount, 0)) AS AR_OemClaimFeeAmount
    ,   SUM(IFNULL(AR_OemMonthlyFee, 0)) AS AR_OemMonthlyFee
    ,   SUM(IFNULL(AR_OemIncludeMonthlyFee, 0)) AS AR_OemIncludeMonthlyFee
    ,   SUM(IFNULL(AR_OemApiMonthlyFee, 0)) AS AR_OemApiMonthlyFee
    ,   SUM(IFNULL(AR_OemCreditNoticeMonthlyFee, 0)) AS AR_OemCreditNoticeMonthlyFee
    ,   SUM(IFNULL(AR_OemNCreditNoticeMonthlyFee, 0)) AS AR_OemNCreditNoticeMonthlyFee
    ,   SUM(IFNULL(AR_OemReserveMonthlyFee, 0)) AS AR_OemReserveMonthlyFee
    ,   SUM(IFNULL(AR_AccountsPayableOffset, 0)) AS AR_AccountsPayableOffset
    ,   SUM(IFNULL(AR_AccountsPayableTransfer, 0)) AS AR_AccountsPayableTransfer
    ,   SUM(IFNULL(AR_AccountsMonthPayment, 0)) AS AR_AccountsMonthPayment
    ,   SUM(IFNULL(AR_BadDebtAmount, 0)) AS AR_BadDebtAmount
    ,   SUM(IFNULL(AR_PreAccountsReceivableBalance, 0)) AS AR_PreAccountsReceivableBalance
    ,   SUM(IFNULL(AR_AccountsReceivableToal, 0)) AS AR_AccountsReceivableToal
    ,   SUM(IFNULL(AR_Difference, 0)) AS AR_Difference
    ,   SUM(IFNULL(AD_AccountsDueBalance, 0)) AS AD_AccountsDueBalance
    ,   SUM(IFNULL(AD_TransferAmountCount, 0)) AS AD_TransferAmountCount
    ,   SUM(IFNULL(AD_TransferAmount, 0)) AS AD_TransferAmount
    ,   SUM(IFNULL(AD_AccountsPayableOffset, 0)) AS AD_AccountsPayableOffset
    ,   SUM(IFNULL(AD_AccountsMonthCount, 0)) AS AD_AccountsMonthCount
    ,   SUM(IFNULL(AD_AccountsMonthPayment, 0)) AS AD_AccountsMonthPayment
    ,   SUM(IFNULL(AD_BadDebtCount, 0)) AS AD_BadDebtCount
    ,   SUM(IFNULL(AD_BadDebtAmount, 0)) AS AD_BadDebtAmount
    ,   SUM(IFNULL(AD_PerAccountsDueBalance, 0)) AS AD_PerAccountsDueBalance
    ,   SUM(IFNULL(AD_CurrentAccountsDue, 0)) AS AD_CurrentAccountsDue
    ,   SUM(IFNULL(AD_Difference, 0)) AS AD_Difference
FROM    AT_OemAccounts_PayableStatisticsTable
WHERE   DailyMonthlyFlg = 1
AND     AccountDate = :AccountDate
%s
GROUP BY
        DailyMonthlyFlg
EOQ;
    }

    /**
     * OEM未払金・売掛金・OEM未収金統計表OEM合計取得SQL
     *
     * @return string
     */
    protected function getOEMMibaraiTokeiSubTotalQuery() {
        return <<<EOQ
SELECT  DailyMonthlyFlg
    ,   MIN(ProcessingDate) AS ProcessingDate
    ,   MIN(AccountDate) AS AccountDate
    ,   OemId
    ,   MAX(OemNameKj) AS OemNameKj
    ,   'OEM合計' AS EnterpriseNameKj
    ,   SUM(IFNULL(AP_AccountsPayableBalance, 0)) AS AP_AccountsPayableBalance
    ,   SUM(IFNULL(AP_ChargeCount, 0)) AS AP_ChargeCount
    ,   SUM(IFNULL(AP_ChargeAmount, 0)) AS AP_ChargeAmount
    ,   SUM(IFNULL(AP_CancelCount, 0)) AS AP_CancelCount
    ,   SUM(IFNULL(AP_CalcelAmount, 0)) AS AP_CalcelAmount
    ,   SUM(IFNULL(AP_SettlementBackCount, 0)) AS AP_SettlementBackCount
    ,   SUM(IFNULL(AP_SettlementBackAmount, 0)) AS AP_SettlementBackAmount
    ,   SUM(IFNULL(AP_AccountsReceivableOffset, 0)) AS AP_AccountsReceivableOffset
    ,   SUM(IFNULL(AP_AccountsPayableOffset, 0)) AS AP_AccountsPayableOffset
    ,   SUM(IFNULL(AP_OtherAccountsPayableOffset, 0)) AS AP_OtherAccountsPayableOffset
    ,   SUM(IFNULL(AP_AccountsPayableTransfer, 0)) AS AP_AccountsPayableTransfer
    ,   SUM(IFNULL(AP_AmountPaymentCount, 0)) AS AP_AmountPaymentCount
    ,   SUM(IFNULL(AP_AmountPayment, 0)) AS AP_AmountPayment
    ,   SUM(IFNULL(AP_BadDebtAmountCount, 0)) AS AP_BadDebtAmountCount
    ,   SUM(IFNULL(AP_BadDebtAmount, 0)) AS AP_BadDebtAmount
    ,   SUM(IFNULL(AP_PreAccountsPayableBalance, 0)) AS AP_PreAccountsPayableBalance
    ,   SUM(IFNULL(AP_UseAmountTotal, 0)) AS AP_UseAmountTotal
    ,   SUM(IFNULL(AP_Difference, 0)) AS AP_Difference
    ,   SUM(IFNULL(Other_AccountsPayableOffset, 0)) AS Other_AccountsPayableOffset
    ,   SUM(IFNULL(Other_StampFeeCount, 0)) AS Other_StampFeeCount
    ,   SUM(IFNULL(Other_StampFee, 0)) AS Other_StampFee
    ,   SUM(IFNULL(Other_TransferCommissionCount, 0)) AS Other_TransferCommissionCount
    ,   SUM(IFNULL(Other_TransferCommission, 0)) AS Other_TransferCommission
    ,   SUM(IFNULL(Other_AdjustmentAmountCount, 0)) AS Other_AdjustmentAmountCount
    ,   SUM(IFNULL(Other_AdjustmentAmount, 0)) AS Other_AdjustmentAmount
    ,   SUM(IFNULL(Other_RefundCount, 0)) AS Other_RefundCount
    ,   SUM(IFNULL(Other_Refund, 0)) AS Other_Refund
    ,   SUM(IFNULL(AR_AccountsReceivableBalance, 0)) AS AR_AccountsReceivableBalance
    ,   SUM(IFNULL(AR_AccountsReceivableIncrease, 0)) AS AR_AccountsReceivableIncrease
    ,   SUM(IFNULL(AR_OemSettlementFeeCount, 0)) AS AR_OemSettlementFeeCount
    ,   SUM(IFNULL(AR_OemSettlementFeeAmount, 0)) AS AR_OemSettlementFeeAmount
    ,   SUM(IFNULL(AR_OemClaimFeeCount, 0)) AS AR_OemClaimFeeCount
    ,   SUM(IFNULL(AR_OemClaimFeeAmount, 0)) AS AR_OemClaimFeeAmount
    ,   SUM(IFNULL(AR_OemMonthlyFee, 0)) AS AR_OemMonthlyFee
    ,   SUM(IFNULL(AR_OemIncludeMonthlyFee, 0)) AS AR_OemIncludeMonthlyFee
    ,   SUM(IFNULL(AR_OemApiMonthlyFee, 0)) AS AR_OemApiMonthlyFee
    ,   SUM(IFNULL(AR_OemCreditNoticeMonthlyFee, 0)) AS AR_OemCreditNoticeMonthlyFee
    ,   SUM(IFNULL(AR_OemNCreditNoticeMonthlyFee, 0)) AS AR_OemNCreditNoticeMonthlyFee
    ,   SUM(IFNULL(AR_OemReserveMonthlyFee, 0)) AS AR_OemReserveMonthlyFee
    ,   SUM(IFNULL(AR_AccountsPayableOffset, 0)) AS AR_AccountsPayableOffset
    ,   SUM(IFNULL(AR_AccountsPayableTransfer, 0)) AS AR_AccountsPayableTransfer
    ,   SUM(IFNULL(AR_AccountsMonthPayment, 0)) AS AR_AccountsMonthPayment
    ,   SUM(IFNULL(AR_BadDebtAmount, 0)) AS AR_BadDebtAmount
    ,   SUM(IFNULL(AR_PreAccountsReceivableBalance, 0)) AS AR_PreAccountsReceivableBalance
    ,   SUM(IFNULL(AR_AccountsReceivableToal, 0)) AS AR_AccountsReceivableToal
    ,   SUM(IFNULL(AR_Difference, 0)) AS AR_Difference
    ,   SUM(IFNULL(AD_AccountsDueBalance, 0)) AS AD_AccountsDueBalance
    ,   SUM(IFNULL(AD_TransferAmountCount, 0)) AS AD_TransferAmountCount
    ,   SUM(IFNULL(AD_TransferAmount, 0)) AS AD_TransferAmount
    ,   SUM(IFNULL(AD_AccountsPayableOffset, 0)) AS AD_AccountsPayableOffset
    ,   SUM(IFNULL(AD_AccountsMonthCount, 0)) AS AD_AccountsMonthCount
    ,   SUM(IFNULL(AD_AccountsMonthPayment, 0)) AS AD_AccountsMonthPayment
    ,   SUM(IFNULL(AD_BadDebtCount, 0)) AS AD_BadDebtCount
    ,   SUM(IFNULL(AD_BadDebtAmount, 0)) AS AD_BadDebtAmount
    ,   SUM(IFNULL(AD_PerAccountsDueBalance, 0)) AS AD_PerAccountsDueBalance
    ,   SUM(IFNULL(AD_CurrentAccountsDue, 0)) AS AD_CurrentAccountsDue
    ,   SUM(IFNULL(AD_Difference, 0)) AS AD_Difference
FROM    AT_OemAccounts_PayableStatisticsTable
WHERE   DailyMonthlyFlg = 1
AND     AccountDate = :AccountDate
AND     IFNULL(OemId, 0) = :OemId
%s
GROUP BY
        DailyMonthlyFlg
    ,   OemId
ORDER BY
        OemId
EOQ;
    }

    /**
     * 売上明細ベースSQL
     *
     * @return string
     */
    protected function getUriageMeisaiQuery() {
        return <<<EOQ
SELECT  OemId
    ,   OemNameKj
    ,   EnterpriseId
    ,   EnterpriseNameKj
    ,   OrderId
    ,   OutOfAmends
    ,   SalesDefiniteConditions
    ,   SalesDefiniteDate
    ,   FixedDate
    ,   ExecScheduleDate
    ,   ProcessingDate
    ,   AccountDate
    ,   JournalNumber
    ,   ManCustId
    ,   ManCusNameKj
    ,   UseAmountTotal
    ,   SettlementFeeRate
    ,   SettlementFee
    ,   ClaimFee
    ,   ClaimFeeTax
    ,   MonthlyFee
    ,   MonthlyFeeTax
    ,   IncludeMonthlyFee
    ,   IncludeMonthlyFeeTax
    ,   ApiMonthlyFee
    ,   ApiMonthlyFeeTax
    ,   CreditNoticeMonthlyFee
    ,   CreditNoticeMonthlyFeeTax
    ,   NCreditNoticeMonthlyFee
    ,   NCreditNoticeMonthlyFeeTax
    ,   TotalSales
    ,   OemSettlementFeeRate
    ,   OemSettlementFee
    ,   OemSettlementFeeTax
    ,   OemClaimFee
    ,   OemClaimFeeTax
    ,   OemMonthlyFee
    ,   OemMonthlyFeeTax
    ,   OemIncludeMonthlyFee
    ,   OemIncludeMonthlyFeeTax
    ,   OemApiMonthlyFee
    ,   OemApiMonthlyFeeTax
    ,   OemCreditNoticeMonthlyFee
    ,   OemCreditNoticeMonthlyFeeTax
    ,   OemNCreditNoticeMonthlyFee
    ,   OemNCreditNoticeMonthlyFeeTax
    ,   OemTotalSales
FROM    AT_Daily_SalesDetails
WHERE   1 = 1
%s
UNION ALL
SELECT  NULL AS OemId
    ,   NULL AS OemNameKj
    ,   NULL AS EnterpriseId
    ,   NULL AS EnterpriseNameKj
    ,   NULL AS OrderId
    ,   NULL AS OutOfAmends
    ,   NULL AS SalesDefiniteConditions
    ,   NULL AS SalesDefiniteDate
    ,   NULL AS FixedDate
    ,   NULL AS ExecScheduleDate
    ,   NULL AS ProcessingDate
    ,   NULL AS AccountDate
    ,   NULL AS JournalNumber
    ,   NULL AS ManCustId
    ,   NULL AS ManCusNameKj
    ,   SUM(UseAmountTotal)
    ,   NULL AS SettlementFeeRate
    ,   SUM(SettlementFee)
    ,   SUM(ClaimFee)
    ,   SUM(ClaimFeeTax)
    ,   SUM(MonthlyFee)
    ,   SUM(MonthlyFeeTax)
    ,   SUM(IncludeMonthlyFee)
    ,   SUM(IncludeMonthlyFeeTax)
    ,   SUM(ApiMonthlyFee)
    ,   SUM(ApiMonthlyFeeTax)
    ,   SUM(CreditNoticeMonthlyFee)
    ,   SUM(CreditNoticeMonthlyFeeTax)
    ,   SUM(NCreditNoticeMonthlyFee)
    ,   SUM(NCreditNoticeMonthlyFeeTax)
    ,   SUM(TotalSales)
    ,   NULL AS OemSettlementFeeRate
    ,   SUM(OemSettlementFee)
    ,   SUM(OemSettlementFeeTax)
    ,   SUM(OemClaimFee)
    ,   SUM(OemClaimFeeTax)
    ,   SUM(OemMonthlyFee)
    ,   SUM(OemMonthlyFeeTax)
    ,   SUM(OemIncludeMonthlyFee)
    ,   SUM(OemIncludeMonthlyFeeTax)
    ,   SUM(OemApiMonthlyFee)
    ,   SUM(OemApiMonthlyFeeTax)
    ,   SUM(OemCreditNoticeMonthlyFee)
    ,   SUM(OemCreditNoticeMonthlyFeeTax)
    ,   SUM(OemNCreditNoticeMonthlyFee)
    ,   SUM(OemNCreditNoticeMonthlyFeeTax)
    ,   SUM(OemTotalSales)
FROM    AT_Daily_SalesDetails
WHERE   1 = 1
%s
ORDER BY
        IFNULL(OemId, 9999999)
    ,   EnterpriseId
    ,   OrderId
EOQ;
    }

    /**
     * 消費者未収金明細ベースSQL
     *
     * @return string
     */
    protected function getShohishaMishukinMeisaiQuery() {
        return <<<EOQ
SELECT  ManCustId
    ,   ManCusNameKj
    ,   OrderId
    ,   OutOfAmends
    ,   SalesDefiniteConditions
    ,   SalesDefiniteDate
    ,   OemTransferDate
    ,   ReceivablesRemainingAmount
    ,   OemId
    ,   OemNameKj
    ,   EnterpriseId
    ,   EnterpriseNameKj
    ,   F_LimitDate
    ,   F_ClaimAmount
    ,   FinalReceiptDate
    ,   AfterTheFinalPaymentDays
    ,   OverdueClassification
FROM    AT_Consumer_AccountsDue
WHERE   DailyMonthlyFlg = 1
%s
UNION ALL
SELECT  NULL AS ManCustId
    ,   NULL AS ManCusNameKj
    ,   NULL AS OrderId
    ,   NULL AS OutOfAmends
    ,   NULL AS SalesDefiniteConditions
    ,   NULL AS SalesDefiniteDate
    ,   NULL AS OemTransferDate
    ,   SUM(ReceivablesRemainingAmount)
    ,   NULL AS OemId
    ,   NULL AS OemNameKj
    ,   NULL AS EnterpriseId
    ,   NULL AS EnterpriseNameKj
    ,   NULL AS F_LimitDate
    ,   SUM(F_ClaimAmount)
    ,   NULL AS FinalReceiptDate
    ,   NULL AS AfterTheFinalPaymentDays
    ,   NULL AS OverdueClassification
FROM    AT_Consumer_AccountsDue
WHERE   DailyMonthlyFlg = 1
%s
ORDER BY
        IFNULL(OemTransferDate, '9999-99-99')
    ,   IFNULL(OemId, 9999999)
    ,   EnterpriseId
    ,   OrderId
EOQ;
    }

    /**
     * 収納代行委託先未収金明細ベースSQL
     *
     * @return string
     */
    protected function getShunodaikoMishukinMeisaiQuery() {
        return <<<EOQ
SELECT  ReceiptAgentName
    ,   PaymentMethod
    ,   ManCustId
    ,   ManCusNameKj
    ,   OrderId
    ,   OutOfAmends
    ,   OemTransferDate
    ,   OemId
    ,   OemNameKj
    ,   EnterpriseId
    ,   EnterpriseNameKj
    ,   ConsumerPayments
    ,   ConsumerPaymentDate
    ,   PaymentDueDate
FROM    AT_ReceiptAgen_AccountsDue
WHERE   DailyMonthlyFlg = 0
%s
UNION ALL
SELECT  NULL AS ReceiptAgentName
    ,   NULL AS PaymentMethod
    ,   NULL AS ManCustId
    ,   NULL AS ManCusNameKj
    ,   NULL AS OrderId
    ,   NULL AS OutOfAmends
    ,   NULL AS OemTransferDate
    ,   NULL AS OemId
    ,   NULL AS OemNameKj
    ,   NULL AS EnterpriseId
    ,   NULL AS EnterpriseNameKj
    ,   SUM(ConsumerPayments)
    ,   NULL AS ConsumerPaymentDate
    ,   NULL AS PaymentDueDate
FROM    AT_ReceiptAgen_AccountsDue
WHERE   DailyMonthlyFlg = 0
%s
ORDER BY
        IFNULL(EnterpriseId, 999999999)
    ,   OrderId
EOQ;
    }

    /**
     * OEM仮払金明細ベースSQL
     *
     * @return string
     */
    protected function getOEMKaribaraikinMeisaiQuery() {
        return <<<EOQ
SELECT  OemId
    ,   OemNameKj
    ,   EnterpriseId
    ,   EnterpriseNameKj
    ,   OrderId
    ,   OutOfAmends
    ,   SalesDefiniteConditions
    ,   SalesDefiniteDate
    ,   OemSuspensePayments
    ,   F_LimitDate
    ,   F_ClaimAmount
    ,   FinalReceiptDate
    ,   AfterTheFinalPaymentDays
    ,   OverdueClassification
    ,   AdvancesImplementationDate
FROM    AT_Oem_SuspensePayments
WHERE   DailyMonthlyFlg = 1
%s
UNION ALL
SELECT  NULL AS OemId
    ,   NULL AS OemNameKj
    ,   NULL AS EnterpriseId
    ,   NULL AS EnterpriseNameKj
    ,   NULL AS OrderId
    ,   NULL AS OutOfAmends
    ,   NULL AS SalesDefiniteConditions
    ,   NULL AS SalesDefiniteDate
    ,   SUM(OemSuspensePayments)
    ,   NULL AS F_LimitDate
    ,   SUM(F_ClaimAmount)
    ,   NULL AS FinalReceiptDate
    ,   NULL AS AfterTheFinalPaymentDays
    ,   NULL AS OverdueClassification
    ,   NULL AS AdvancesImplementationDate
FROM    AT_Oem_SuspensePayments
WHERE   DailyMonthlyFlg = 1
%s
ORDER BY
        IFNULL(OemId, 9999999)
    ,   EnterpriseId
    ,   OrderId
EOQ;
    }

    /**
     * 直営未払金・売掛金・加盟店未収金明細ベースSQL
     *
     * @return string
     */
    protected function getChokueiUrikakeMeisaiQuery() {
        return <<<EOQ
SELECT  EnterpriseId
    ,   EnterpriseNameKj
    ,   OrderId
    ,   DebtDefiniteConditions
    ,   DebtFixedDate
    ,   SettlementCosingDate
    ,   SettlementExpectedDate
    ,   ManCustId
    ,   ManCusNameKj
    ,   AccountsPayablePending
    ,   UseAmount
    ,   SettlementFeeRate
    ,   SettlementFee
    ,   ClaimFee
    ,   MonthlyFee
    ,   IncludeMonthlyFee
    ,   ApiMonthlyFee
    ,   CreditNoticeMonthlyFee
    ,   NCreditNoticeMonthlyFee
    ,   ReserveMonthlyFee
    ,   AccountsReceivableTotal
    ,   AccountsDue
    ,   CarryOverAmount
    ,   InitiallyRemainAccountsPayable
    ,   InitiallyRemainAccountsReceivable
    ,   InitiallyRemainStampFee
    ,   InitiallyRemainAdjustmentAmount
    ,   InitiallyRemainRefund
FROM    AT_Cb_Accounts_PayableReceivable
WHERE   DailyMonthlyFlg = 1
%s
UNION ALL
SELECT  NULL AS EnterpriseId
    ,   NULL AS EnterpriseNameKj
    ,   NULL AS OrderId
    ,   NULL AS DebtDefiniteConditions
    ,   NULL AS DebtFixedDate
    ,   NULL AS SettlementCosingDate
    ,   NULL AS SettlementExpectedDate
    ,   NULL AS ManCustId
    ,   NULL AS ManCusNameKj
    ,   SUM(AccountsPayablePending)
    ,   SUM(UseAmount)
    ,   NULL AS SettlementFeeRate
    ,   SUM(SettlementFee)
    ,   SUM(ClaimFee)
    ,   SUM(MonthlyFee)
    ,   SUM(IncludeMonthlyFee)
    ,   SUM(ApiMonthlyFee)
    ,   SUM(CreditNoticeMonthlyFee)
    ,   SUM(NCreditNoticeMonthlyFee)
    ,   SUM(ReserveMonthlyFee)
    ,   SUM(AccountsReceivableTotal)
    ,   SUM(AccountsDue)
    ,   SUM(CarryOverAmount)
    ,   SUM(InitiallyRemainAccountsPayable)
    ,   SUM(InitiallyRemainAccountsReceivable)
    ,   SUM(InitiallyRemainStampFee)
    ,   SUM(InitiallyRemainAdjustmentAmount)
    ,   SUM(InitiallyRemainRefund)
FROM    AT_Cb_Accounts_PayableReceivable
WHERE   DailyMonthlyFlg = 1
%s
ORDER BY
        IFNULL(EnterpriseId, 999999999)
    ,   OrderId
EOQ;
    }

    /**
     * OEM未払金・売掛金・OEM未収金明細ベースSQL
     *
     * @return string
     */
    protected function getOEMUrikakeMeisaiQurey() {
        return <<<EOQ
SELECT  OemId
    ,   OemNameKj
    ,   EnterpriseId
    ,   EnterpriseNameKj
    ,   OrderId
    ,   DebtDefiniteConditions
    ,   DebtFixedDate
    ,   CASE WHEN SettlementCosingDate IS NULL THEN LAST_DAY(AccountDate)
             ELSE SettlementCosingDate
        END AS SettlementCosingDate
    ,   CASE WHEN SettlementExpectedDate IS NOT NULL THEN SettlementExpectedDate
             ELSE DATE_FORMAT((AccountDate + INTERVAL 1 MONTH), '%%Y-%%m-15')
        END AS SettlementExpectedDate
    ,   ManCustId
    ,   ManCusNameKj
    ,   AccountsPayablePending
    ,   UseAmount
    ,   SettlementFeeRate
    ,   SettlementFee
    ,   ClaimFee
    ,   MonthlyFee
    ,   IncludeMonthlyFee
    ,   ApiMonthlyFee
    ,   CreditNoticeMonthlyFee
    ,   NCreditNoticeMonthlyFee
    ,   AccountsReceivableTotal
    ,   AccountsDue
    ,   CarryOverAmount
    ,   InitiallyRemainAccountsPayable
    ,   InitiallyRemainAccountsReceivable
    ,   InitiallyRemainStampFee
    ,   InitiallyRemainAdjustmentAmount
    ,   InitiallyRemainRefund
FROM    AT_Oem_Accounts_PayableReceivable
WHERE   DailyMonthlyFlg = 1
%s
UNION ALL
SELECT  NULL AS OemId
    ,   NULL AS OemNameKj
    ,   NULL AS EnterpriseId
    ,   NULL AS EnterpriseNameKj
    ,   NULL AS OrderId
    ,   NULL AS DebtDefiniteConditions
    ,   NULL AS DebtFixedDate
    ,   NULL AS SettlementCosingDate
    ,   NULL AS SettlementExpectedDate
    ,   NULL AS ManCustId
    ,   NULL AS ManCusNameKj
    ,   SUM(AccountsPayablePending)
    ,   SUM(UseAmount)
    ,   NULL AS SettlementFeeRate
    ,   SUM(SettlementFee)
    ,   SUM(ClaimFee)
    ,   SUM(MonthlyFee)
    ,   SUM(IncludeMonthlyFee)
    ,   SUM(ApiMonthlyFee)
    ,   SUM(CreditNoticeMonthlyFee)
    ,   SUM(NCreditNoticeMonthlyFee)
    ,   SUM(AccountsReceivableTotal)
    ,   SUM(AccountsDue)
    ,   SUM(CarryOverAmount)
    ,   SUM(InitiallyRemainAccountsPayable)
    ,   SUM(InitiallyRemainAccountsReceivable)
    ,   SUM(InitiallyRemainStampFee)
    ,   SUM(InitiallyRemainAdjustmentAmount)
    ,   SUM(InitiallyRemainRefund)
FROM    AT_Oem_Accounts_PayableReceivable
WHERE   DailyMonthlyFlg = 1
%s
ORDER BY
        IFNULL(OemId, 9999999)
    ,   EnterpriseId
    ,   OrderId
EOQ;
    }

    /**
     * 未収金日計ベースSQL
     *
     * @return string
     */
    protected function getMishukinHikeiQuery() {
        return <<<EOQ
SELECT  ProcessingDate
    ,   AccountDate
    ,   PaymentDate
    ,   ReceiptProcessDate
    ,   PaymentAccountTitle
    ,   PaymentTargetAccountTitle
    ,   PaymentNumber
    ,   Amount
FROM    AT_AccountsDue_DailyAccount
WHERE   1 = 1
%s
ORDER BY
        ProcessingDate
    ,   AccountDate
    ,   PaymentDate
    ,   ReceiptProcessDate
    ,   PaymentAccountTitle
EOQ;
    }

    /**
     * 仮払金日計ベースSQL
     *
     * @return string
     */
    protected function getKaribaraikinHikeiQuery() {
        return <<<EOQ
SELECT  ProcessingDate
    ,   AccountDate
    ,   PaymentDate
    ,   ReceiptProcessDate
    ,   PaymentAccountTitle
    ,   PaymentTargetAccountTitle
    ,   PaymentNumber
    ,   Amount
FROM    AT_SuspensePayments_DailyAccount
WHERE   1 = 1
%s
ORDER BY
        ProcessingDate
    ,   AccountDate
    ,   PaymentDate
    ,   ReceiptProcessDate
    ,   PaymentAccountTitle
EOQ;
    }

    /**
     * 精算日計ベースSQL
     *
     * @return string
     */
    protected function getSeisanHikeiQuery() {
        return <<<EOQ
SELECT  OemId
    ,   OemNameKj
    ,   EnterpriseId
    ,   EnterpriseNameKj
    ,   MerchantNumber
    ,   AdvancesDate
    ,   AdvancesAmount
    ,   EnterpriseAccountsDue
    ,   AccountsPayablePending
    ,   ClaimAndObligationsDecision
    ,   UseAmount
    ,   CancelAmount
    ,   UseSettlementBackOffse
    ,   AccountsPayableTotal
    ,   SettlementFee
    ,   ClaimFee
    ,   MonthlyFee
    ,   IncludeMonthlyFee
    ,   ApiMonthlyFee
    ,   CreditNoticeMonthlyFee
    ,   NextClaimCreditNoticeMonthlyFee
    ,   AccountsReceivableTotal
    ,   StampFee
    ,   TransferCommission
    ,   AdjustmentAmount
    ,   EnterpriseRefund
    ,   AccountsDueOffsetAmount
    ,   AccountsPayablePendingAmount
    ,   AdvancesFixedDate
FROM    AT_PayOff_DailyAccount
WHERE   DailyMonthlyFlg = 0
%s
UNION ALL
SELECT  NULL AS OemId
    ,   NULL AS OemNameKj
    ,   NULL AS EnterpriseId
    ,   NULL AS EnterpriseNameKj
    ,   SUM(MerchantNumber)
    ,   NULL AS AdvancesDate
    ,   SUM(AdvancesAmount)
    ,   SUM(EnterpriseAccountsDue)
    ,   SUM(AccountsPayablePending)
    ,   NULL AS ClaimAndObligationsDecision
    ,   SUM(UseAmount)
    ,   SUM(CancelAmount)
    ,   SUM(UseSettlementBackOffse)
    ,   SUM(AccountsPayableTotal)
    ,   SUM(SettlementFee)
    ,   SUM(ClaimFee)
    ,   SUM(MonthlyFee)
    ,   SUM(IncludeMonthlyFee)
    ,   SUM(ApiMonthlyFee)
    ,   SUM(CreditNoticeMonthlyFee)
    ,   SUM(NextClaimCreditNoticeMonthlyFee)
    ,   SUM(AccountsReceivableTotal)
    ,   SUM(StampFee)
    ,   SUM(TransferCommission)
    ,   SUM(AdjustmentAmount)
    ,   SUM(EnterpriseRefund)
    ,   SUM(AccountsDueOffsetAmount)
    ,   SUM(AccountsPayablePendingAmount)
    ,   NULL AS AdvancesFixedDate
FROM    AT_PayOff_DailyAccount
WHERE   DailyMonthlyFlg = 0
%s
ORDER BY
        IFNULL(OemId, 9999999)
EOQ;
    }

    /**
     * 貸倒債権一覧ベースSQL
     *
     * @return string
     */
    protected function getKashidaoreSaikenIchiranQuery() {
        return <<<EOQ
SELECT  CrediTarget
    ,   CreditProcessingDate
    ,   CrediAmount
    ,   OrderId
    ,   ManCustId
    ,   ManCusNameKj
    ,   OemId
    ,   OemNameKj
    ,   EnterpriseId
    ,   EnterpriseNameKj
    ,   OutOfAmends
    ,   SalesDefiniteConditions
    ,   SalesDefiniteDate
    ,   OemTransferDate
    ,   F_LimitDate
    ,   F_ClaimAmount
    ,   CrediAmount AS BadDebtBalance
    ,   FinalReceiptDate
    ,   AfterTheFinalPaymentDays
    ,   OverdueClassification
FROM    AT_BadDebt_List
WHERE   1 = 1
%s
UNION ALL
SELECT  NULL AS CrediTarget
    ,   NULL AS CreditProcessingDate
    ,   SUM(CrediAmount)
    ,   NULL AS OrderId
    ,   NULL AS ManCustId
    ,   NULL AS ManCusNameKj
    ,   NULL AS OemId
    ,   NULL AS OemNameKj
    ,   NULL AS EnterpriseId
    ,   NULL AS EnterpriseNameKj
    ,   NULL AS OutOfAmends
    ,   NULL AS SalesDefiniteConditions
    ,   NULL AS SalesDefiniteDate
    ,   NULL AS OemTransferDate
    ,   NULL AS F_LimitDate
    ,   SUM(F_ClaimAmount)
    ,   SUM(CrediAmount) AS BadDebtBalance
    ,   NULL AS FinalReceiptDate
    ,   NULL AS AfterTheFinalPaymentDays
    ,   NULL AS OverdueClassification
FROM    AT_BadDebt_List
WHERE   1 = 1
%s
ORDER BY
        IFNULL(CreditProcessingDate, '9999-99-99')
    ,   OemId
    ,   EnterpriseId
EOQ;
    }

    /**
     * 過剰金一覧ベースSQL
     *
     * @return string
     */
    protected function getKajokinIchiranQuery() {
        return <<<EOQ
SELECT  OemId
    ,   OemNameKj
    ,   ManCustId
    ,   ManCusNameKj
    ,   ExcessAmount
    ,   OrderId
    ,   OutOfAmends
    ,   SalesDefiniteConditions
    ,   SalesDefiniteDate
    ,   OemTransferDate
    ,   SettlementBackDate
    ,   EnterpriseId
    ,   EnterpriseNameKj
    ,   F_LimitDate
    ,   F_ClaimAmount
    ,   FinalReceiptDate
    ,   AfterTheFinalPaymentDays
    ,   OverdueClassification
FROM    AT_Excess_List
WHERE 1 = 1
%s
UNION ALL
SELECT  NULL AS OemId
    ,   NULL AS OemNameKj
    ,   NULL AS ManCustId
    ,   NULL AS ManCusNameKj
    ,   SUM(ExcessAmount)
    ,   NULL AS OrderId
    ,   NULL AS OutOfAmends
    ,   NULL AS SalesDefiniteConditions
    ,   NULL AS SalesDefiniteDate
    ,   NULL AS OemTransferDate
    ,   NULL AS SettlementBackDate
    ,   NULL AS EnterpriseId
    ,   NULL AS EnterpriseNameKj
    ,   NULL AS F_LimitDate
    ,   SUM(F_ClaimAmount)
    ,   NULL AS FinalReceiptDate
    ,   NULL AS AfterTheFinalPaymentDays
    ,   NULL AS OverdueClassification
FROM    AT_Excess_List
WHERE 1 = 1
%s
ORDER BY
        IFNULL(OemId, 9999999)
    ,   EnterpriseId
    ,   OrderId
EOQ;
    }

    /**
     * 再発行手数料明細ベースSQL
     *
     * @return string
     */
    protected function getSaihakkotesuryoMeisaiQuery() {
        return <<<EOQ
SELECT  OemId
    ,   OemNameKj
    ,   ManCustId
    ,   ManCusNameKj
    ,   OrderId
    ,   OverdueClassification
    ,   Clm_L_ClaimFee
    ,   Clm_L_ClaimFeeTax
    ,   Clm_L_DamageInterestAmount
    ,   OutOfAmends
    ,   SalesDefiniteConditions
    ,   SalesDefiniteDate
    ,   OemTransferDate
    ,   EnterpriseId
    ,   EnterpriseNameKj
    ,   F_LimitDate
    ,   F_ClaimAmount
    ,   ClaimAmount
    ,   FinalReceiptDate
    ,   AfterTheFinalPaymentDays
FROM    AT_ReissueFeeSpecification
WHERE   1 = 1
%s
UNION ALL
SELECT  NULL AS OemId
    ,   NULL AS OemNameKj
    ,   NULL AS ManCustId
    ,   NULL AS ManCusNameKj
    ,   NULL AS OrderId
    ,   NULL AS OverdueClassification
    ,   SUM(Clm_L_ClaimFee)
    ,   SUM(Clm_L_ClaimFeeTax)
    ,   SUM(Clm_L_DamageInterestAmount)
    ,   NULL AS OutOfAmends
    ,   NULL AS SalesDefiniteConditions
    ,   NULL AS SalesDefiniteDate
    ,   NULL AS OemTransferDate
    ,   NULL AS EnterpriseId
    ,   NULL AS EnterpriseNameKj
    ,   NULL AS F_LimitDate
    ,   SUM(F_ClaimAmount)
    ,   SUM(ClaimAmount)
    ,   NULL AS FinalReceiptDate
    ,   NULL AS AfterTheFinalPaymentDays
FROM    AT_ReissueFeeSpecification
WHERE   1 = 1
%s
ORDER BY
        IFNULL(OemId, 9999999)
    ,   EnterpriseId
    ,   OrderId
EOQ;
    }

    /**
     * 無保証立替金戻し明細ベースSQL
     *
     * @return string
     */
    protected function getMuhoshoTatekaekinmodoshiMeisaiQuery() {
        return <<<EOQ
SELECT  OemId
    ,   OemNameKj
    ,   SettlementBackDate AS AdvancesSettlementBackDate
    ,   SettlementBackOffsetDate AS AdvancesSettlementBackOffsetDate
    ,   EnterpriseId
    ,   EnterpriseNameKj
    ,   OrderId
    ,   SalesDefiniteConditions
    ,   SalesDefiniteDate
    ,   FirstBillingDate
    ,   FirstCaimAfterTheNumberOfDays
    ,   JournalNumber
    ,   ManCustId
    ,   ManCusNameKj
    ,   ClaimAmount
    ,   F_ClaimAmount
FROM    AT_AdvancesSettlementBack
WHERE   1 = 1
%s
UNION ALL
SELECT  NULL AS OemId
    ,   NULL AS OemNameKj
    ,   NULL AS AdvancesSettlementBackDate
    ,   NULL AS AdvancesSettlementBackOffsetDate
    ,   NULL AS EnterpriseId
    ,   NULL AS EnterpriseNameKj
    ,   NULL AS OrderId
    ,   NULL AS SalesDefiniteConditions
    ,   NULL AS SalesDefiniteDate
    ,   NULL AS FirstBillingDate
    ,   NULL AS FirstCaimAfterTheNumberOfDays
    ,   NULL AS JournalNumber
    ,   NULL AS ManCustId
    ,   NULL AS ManCusNameKj
    ,   SUM(ClaimAmount)
    ,   SUM(F_ClaimAmount)
FROM    AT_AdvancesSettlementBack
WHERE   1 = 1
%s
ORDER BY
        IFNULL(AdvancesSettlementBackDate, '9999-99-99')
    ,   OemId
    ,   EnterpriseId
EOQ;
    }

    /**
     * OEM移管明細ベースSQL
     *
     * @return string
     */
    protected function getOEMIkanMeisaiQuery() {
        return <<<EOQ
SELECT  OemId
    ,   OemNameKj
    ,   OemTransferDate
    ,   ReceivablesTransferredAmount
    ,   OrderId
    ,   SalesDefiniteConditions
    ,   SalesDefiniteDate
    ,   EnterpriseId
    ,   EnterpriseNameKj
    ,   ManCustId
    ,   ManCusNameKj
    ,   F_LimitDate
    ,   F_ClaimAmount
    ,   FinalReceiptDate
    ,   AfterTheFinalPaymentDays
    ,   OverdueClassification
FROM    AT_Oem_TransferSpecification
WHERE   1 = 1
%s
UNION ALL
SELECT  NULL AS OemId
    ,   NULL AS OemNameKj
    ,   NULL AS OemTransferDate
    ,   SUM(ReceivablesTransferredAmount)
    ,   NULL AS OrderId
    ,   NULL AS SalesDefiniteConditions
    ,   NULL AS SalesDefiniteDate
    ,   NULL AS EnterpriseId
    ,   NULL AS EnterpriseNameKj
    ,   NULL AS ManCustId
    ,   NULL AS ManCusNameKj
    ,   NULL AS F_LimitDate
    ,   SUM(F_ClaimAmount)
    ,   NULL AS FinalReceiptDate
    ,   NULL AS AfterTheFinalPaymentDays
    ,   NULL AS OverdueClassification
FROM    AT_Oem_TransferSpecification
WHERE   1 = 1
%s
ORDER BY
        IFNULL(OemId, 9999999)
    ,   EnterpriseId
    ,   OrderId
EOQ;
    }

    /**
     * 調整金一覧ベースSQL
     *
     * @return string
     */
    protected function getChoseikinIchiranQuery() {
        return <<<EOQ
SELECT  OemId
    ,   OemNameKj
    ,   EnterpriseId
    ,   EnterpriseNameKj
    ,   '' AS AccountingCourses
    ,   AccountingCourses AS AccountingCoursesContent
    ,   AdjustGoldFixedDate
    ,   AdvancesDate
    ,   OrderId
    ,   AdjustAmount AS AdjustAmountTotal
FROM    AT_AdjustGoldList
WHERE   DailyMonthlyFlg = 0
%s
UNION ALL
SELECT  NULL AS OemId
    ,   NULL AS OemNameKj
    ,   NULL AS EnterpriseId
    ,   NULL AS EnterpriseNameKj
    ,   NULL AS AccountingCourses
    ,   NULL AS AccountingCoursesContent
    ,   NULL AS AdjustGoldFixedDate
    ,   NULL AS AdvancesDate
    ,   NULL AS OrderId
    ,   SUM(AdjustAmount)
FROM    AT_AdjustGoldList
WHERE   DailyMonthlyFlg = 0
%s
ORDER BY
        IFNULL(OemId, 9999999)
    ,   EnterpriseId
    ,   OrderId
EOQ;
    }

    /**
     * 入金トータルベースSQL
     *
     * @return string
     */
    protected function getNyukinTotalQuery() {
        return <<<EOQ
SELECT  ReceiptName
    ,   ReceiptDate
    ,   PaymentDestinationName
    ,   PaymentDestinationDetails
    ,   PaymentAmount
    ,   PaymentPerson
    ,   ProcessingDate
FROM    AT_PaymentPlaceTotal
WHERE   DailyMonthlyFlg = 0
%s
UNION ALL
SELECT  NULL AS ReceiptName
    ,   NULL AS ReceiptDate
    ,   NULL AS PaymentDestinationName
    ,   NULL AS PaymentDestinationDetails
    ,   SUM(PaymentAmount)
    ,   NULL AS PaymentPerson
    ,   NULL AS ProcessingDate
FROM    AT_PaymentPlaceTotal
WHERE   DailyMonthlyFlg = 0
%s
ORDER BY
        IFNULL(ReceiptDate, '9999-99-99')
    ,   PaymentDestinationName
EOQ;
    }

    /**
     * 会計帳票/CSV作成処理(日次)
     */
    public function executedairyAction()
    {
        $params = $this->getParams();

        // 今日の日付
        $today = $params['day'];

        // ZIPファイル作成
        $zip = new \ZipArchive();

        // 出力時刻
        $formatNowStr = date('Ymd', strtotime($today));

        // 出力ファイル名
        $outFileName= ('kaikei_dairy_' . $formatNowStr . '.zip');

        // TEMP領域作成
        $tmpFilePath = tempnam( sys_get_temp_dir(), 'tmp' );

        // ZIPファイルオープン
        $zip->open( $tmpFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE );

        // 抽出条件(指定日)
        $processingDate = BaseGeneralUtils::makeWhereDate(
            'ProcessingDate',
            BaseGeneralUtils::convertWideToNarrow($today),
            BaseGeneralUtils::convertWideToNarrow($today)
        );
        $whereProcessingDate = (' AND ' . $processingDate);

        $unlinkList = array();

        // 1. 直営日次統計表(CSV)
        $filename = $this->CsvCBNichijiTokei(" AND DailyMonthlyFlg = 0 " . $whereProcessingDate, $formatNowStr, $tmpFilePath);
        if ($filename != '' ) {
            $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
            $addFilePath = file_get_contents( $filename );
            $zip->addFromString( mb_convert_encoding($pathcutfilename, 'sjis-win'), $addFilePath );
            $unlinkList[] = $filename;
        }

        // 2. OEM日次統計表(CSV)
        $filename = $this->CsvOEMNichijiTokei(" AND DailyMonthlyFlg = 0 " . $whereProcessingDate, $formatNowStr, $tmpFilePath);
        if ($filename != '' ) {
            $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
            $addFilePath = file_get_contents( $filename );
            $zip->addFromString( mb_convert_encoding($pathcutfilename, 'sjis-win'), $addFilePath );
            $unlinkList[] = $filename;
        }

        // 5. 売上明細(CSV)
        $filename = $this->CsvUriageMeisai($whereProcessingDate, $formatNowStr, $tmpFilePath);
        if ($filename != '' ) {
            $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
            $addFilePath = file_get_contents( $filename );
            $zip->addFromString( mb_convert_encoding($pathcutfilename, 'sjis-win'), $addFilePath );
            $unlinkList[] = $filename;
        }

        // 7. 収納代行委託先未収金明細(CSV)
        $filename = $this->CsvShunodaikoMishukinMeisai($whereProcessingDate, $formatNowStr, $tmpFilePath);
        if ($filename != '' ) {
            $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
            $addFilePath = file_get_contents( $filename );
            $zip->addFromString( mb_convert_encoding($pathcutfilename, 'sjis-win'), $addFilePath );
            $unlinkList[] = $filename;
        }

        // 11. 未収金日計(CSV)
        $filename = $this->CsvMishukinHikei($whereProcessingDate, $formatNowStr, $tmpFilePath);
        if ($filename != '' ) {
            $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
            $addFilePath = file_get_contents( $filename );
            $zip->addFromString( mb_convert_encoding($pathcutfilename, 'sjis-win'), $addFilePath );
            $unlinkList[] = $filename;
        }

        // 12. 仮払金日計(CSV)
        $filename = $this->CsvKaribaraikinHikei($whereProcessingDate, $formatNowStr, $tmpFilePath);
        if ($filename != '' ) {
            $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
            $addFilePath = file_get_contents( $filename );
            $zip->addFromString( mb_convert_encoding($pathcutfilename, 'sjis-win'), $addFilePath );
            $unlinkList[] = $filename;
        }

        // 13. 精算日計(CSV)
        $ri = $this->app->dbAdapter->query(" SELECT 0 AS OemId, 'cb' AS AccessId UNION ALL SELECT OemId, AccessId FROM T_Oem ORDER BY OemId ")->execute(null);
        foreach ($ri as $row) {
            $isUseOemTemplate = ($row['OemId'] == 0 || $row['OemId'] == 2) ? false : true;
            $filename = $this->CsvSeisanHikei($whereProcessingDate . (" AND OemId = " . $row['OemId']), $formatNowStr . ("_" . $row['AccessId']), $tmpFilePath, $isUseOemTemplate, $row['OemId'], BaseGeneralUtils::convertWideToNarrow($today));
            if ($filename != '' ) {
                $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
                $addFilePath = file_get_contents( $filename );
                $zip->addFromString( mb_convert_encoding($pathcutfilename, 'sjis-win'), $addFilePath );
                $unlinkList[] = $filename;
            }
        }

        // 14. 貸倒債権一覧(CSV)
        $filename = $this->CsvKashidaoreSaikenIchiran($whereProcessingDate, $formatNowStr, $tmpFilePath);
        if ($filename != '' ) {
            $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
            $addFilePath = file_get_contents( $filename );
            $zip->addFromString( mb_convert_encoding($pathcutfilename, 'sjis-win'), $addFilePath );
            $unlinkList[] = $filename;
        }

        // 15. 過剰金一覧(CSV)
        $filename = $this->CsvKajokinIchiran(" AND DailyMonthlyFlg = 0 " . $whereProcessingDate, $formatNowStr, $tmpFilePath);
        if ($filename != '' ) {
            $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
            $addFilePath = file_get_contents( $filename );
            $zip->addFromString( mb_convert_encoding($pathcutfilename, 'sjis-win'), $addFilePath );
            $unlinkList[] = $filename;
        }

        // 16. 再発行手数料明細(CSV)
        $filename = $this->CsvSaihakkotesuryoMeisai($whereProcessingDate, $formatNowStr, $tmpFilePath);
        if ($filename != '' ) {
            $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
            $addFilePath = file_get_contents( $filename );
            $zip->addFromString( mb_convert_encoding($pathcutfilename, 'sjis-win'), $addFilePath );
            $unlinkList[] = $filename;
        }

        // 17. 無保証立替金戻し明細(CSV)
        $filename = $this->CsvMuhoshoTatekaekinmodoshiMeisai(" AND DailyMonthlyFlg = 0 " . $whereProcessingDate, $formatNowStr, $tmpFilePath);
        if ($filename != '' ) {
            $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
            $addFilePath = file_get_contents( $filename );
            $zip->addFromString( mb_convert_encoding($pathcutfilename, 'sjis-win'), $addFilePath );
            $unlinkList[] = $filename;
        }

        // 18. OEM移管明細(CSV)
        $filename = $this->CsvOEMIkanMeisai($whereProcessingDate, $formatNowStr, $tmpFilePath);
        if ($filename != '' ) {
            $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
            $addFilePath = file_get_contents( $filename );
            $zip->addFromString( mb_convert_encoding($pathcutfilename, 'sjis-win'), $addFilePath );
            $unlinkList[] = $filename;
        }

        // 19. 調整金一覧(CSV)
        $filename = $this->CsvChoseikinIchiran(" AND DailyMonthlyFlg = 0 " . $whereProcessingDate, $formatNowStr, $tmpFilePath);
        if ($filename != '' ) {
            $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
            $addFilePath = file_get_contents( $filename );
            $zip->addFromString( mb_convert_encoding($pathcutfilename, 'sjis-win'), $addFilePath );
            $unlinkList[] = $filename;
        }

        // 20. 調整金一覧(CSV)
        $filename = $this->CsvNyukinTotal(" AND DailyMonthlyFlg = 0 " . $whereProcessingDate, $formatNowStr, $tmpFilePath);
        if ($filename != '' ) {
            $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
            $addFilePath = file_get_contents( $filename );
            $zip->addFromString( mb_convert_encoding($pathcutfilename, 'sjis-win'), $addFilePath );
            $unlinkList[] = $filename;
        }

        // 1. 直営日次統計表(PDF)
        $filename = $this->PdfCBNichijiTokei(" AND DailyMonthlyFlg = 0 " . $whereProcessingDate, $formatNowStr, $tmpFilePath, 0);
        if ($filename != '' ) {
            $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
            $addFilePath = file_get_contents( $filename );
            $zip->addFromString( mb_convert_encoding($pathcutfilename, 'sjis-win'), $addFilePath );
            $unlinkList[] = $filename;
        }

        // 2. OEM日次統計表(PDF)
        $filename = $this->PdfOEMNichijiTokei(" AND DailyMonthlyFlg = 0 " . $whereProcessingDate, $formatNowStr, $tmpFilePath, 0);
        if ($filename != '' ) {
            $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
            $addFilePath = file_get_contents( $filename );
            $zip->addFromString( mb_convert_encoding($pathcutfilename, 'sjis-win'), $addFilePath );
            $unlinkList[] = $filename;
        }

        // ZIPファイルクローズ
        $zip->close();

        // ヘッダ
        header( 'Content-Type: application/octet-stream; name="' . $outFileName . '"' );
        header( 'Content-Disposition: attachment; filename="' . $outFileName . '"' );
        header( 'Content-Length: ' . filesize( $tmpFilePath ) );

        // 出力
        echo readfile( $tmpFilePath );

        // count関数対策
        $unlinkListCount = 0;
        if (!empty($unlinkList)) {
            $unlinkListCount = count($unlinkList);
        }

        // TEMP領域削除
        for ($i=0; $i<$unlinkListCount; $i++) {
            unlink( $unlinkList[$i] );
        }
        unlink( $tmpFilePath );

        return $this->view;
    }

    /**
     * 01.直営日次統計表(月次)調整用変数
     *
     * @var array
     */
    protected $_ary01 = array();

    /**
     * 02.OEM日次統計表(月次)調整用変数
     *
     * @var array
     */
    protected $_ary02 = array();

    /**
     * 01.直営日次統計表(月次／加盟店別)調整用変数
     *
     * @var array
     */
    protected $_aryEnt01 = array();

    /**
     * 02.OEM日次統計表(月次／加盟店)調整用変数
     *
     * @var array
    */
    protected $_aryEnt02 = array();

    /**
     * 01.直営日次統計表(月次)調整用変数sub
     *
     * @var array
     */
    protected $_ary01sub = array();

    /**
     * 02.OEM日次統計表(月次)調整用変数sub
     *
     * @var array
     */
    protected $_ary02sub = array();

    /**
     * 会計帳票/CSV作成処理(月次)
     */
    public function executemonthlyAction()
    {
        $params = $this->getParams();

        // 今日の日付
        $today = date('Y-m-d');

        // 対象月
        $accountDate = date('Y-m-01', strtotime($params['month']));

        // ZIPファイル作成
        $zip = new \ZipArchive();

        // 出力時刻
        $formatNowStr = date('Ymd', strtotime($today));

        // 出力ファイル名
        $outFileName= ('kaikei_monthly_' . $formatNowStr . '.zip');

        // TEMP領域作成
        $tmpFilePath = tempnam( sys_get_temp_dir(), 'tmp' );

        // ZIPファイルオープン
        $zip->open( $tmpFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE );

        // 抽出条件(指定月)
        $whereProcessingDate .= (" AND AccountDate = '" . $accountDate . "'");

        $unlinkList = array();

        $this->_ary01 = array();// 調整用変数初期化
        $this->_ary02 = array();// 調整用変数初期化
        $this->_ary01sub = array();// 調整用変数初期化
        $this->_ary02sub = array();// 調整用変数初期化

        // 5. 売上明細(CSV)
        $filename = $this->CsvUriageMeisaiMonth($formatNowStr, $tmpFilePath);
        if ($filename != '' ) {
            $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
            $addFilePath = file_get_contents( $filename );
            $zip->addFromString( mb_convert_encoding($pathcutfilename, 'sjis-win'), $addFilePath );
            $unlinkList[] = $filename;
        }

        // 6. 消費者未収金明細(CSV)
        $filename = $this->CsvShohishaMishukinMeisai($whereProcessingDate, $formatNowStr, $tmpFilePath);
        if ($filename != '' ) {
            $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
            $addFilePath = file_get_contents( $filename );
            $zip->addFromString( mb_convert_encoding($pathcutfilename, 'sjis-win'), $addFilePath );
            $unlinkList[] = $filename;
        }

        // 8. OEM仮払金明細(CSV)
        $filename = $this->CsvOEMKaribaraikinMeisai($whereProcessingDate, $formatNowStr, $tmpFilePath);
        if ($filename != '' ) {
            $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
            $addFilePath = file_get_contents( $filename );
            $zip->addFromString( mb_convert_encoding($pathcutfilename, 'sjis-win'), $addFilePath );
            $unlinkList[] = $filename;
        }

        // 9. 直営未払金兼売掛金明細(CSV)
        $filename = $this->CsvChokueiUrikakeMeisai($whereProcessingDate, $formatNowStr, $tmpFilePath);
        if ($filename != '' ) {
            $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
            $addFilePath = file_get_contents( $filename );
            $zip->addFromString( mb_convert_encoding($pathcutfilename, 'sjis-win'), $addFilePath );
            $unlinkList[] = $filename;
        }

        // 10. OEM未払金兼売掛金明細(CSV)
        $filename = $this->CsvOEMUrikakeMeisai($whereProcessingDate, $formatNowStr, $tmpFilePath);
        if ($filename != '' ) {
            $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
            $addFilePath = file_get_contents( $filename );
            $zip->addFromString( mb_convert_encoding($pathcutfilename, 'sjis-win'), $addFilePath );
            $unlinkList[] = $filename;
        }

        // 11. 未収金日計(CSV)
        $filename = $this->CsvMishukinHikeiMonth($formatNowStr, $tmpFilePath);
        if ($filename != '' ) {
            $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
            $addFilePath = file_get_contents( $filename );
            $zip->addFromString( mb_convert_encoding($pathcutfilename, 'sjis-win'), $addFilePath );
            $unlinkList[] = $filename;
        }

        // 12. 仮払金日計(CSV)
        $filename = $this->CsvKaribaraikinHikeiMonth($formatNowStr, $tmpFilePath);
        if ($filename != '' ) {
            $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
            $addFilePath = file_get_contents( $filename );
            $zip->addFromString( mb_convert_encoding($pathcutfilename, 'sjis-win'), $addFilePath );
            $unlinkList[] = $filename;
        }

        // 14. 貸倒債権一覧(CSV)
        $filename = $this->CsvKashidaoreSaikenIchiran($whereProcessingDate, $formatNowStr, $tmpFilePath);
        if ($filename != '' ) {
            $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
            $addFilePath = file_get_contents( $filename );
            $zip->addFromString( mb_convert_encoding($pathcutfilename, 'sjis-win'), $addFilePath );
            $unlinkList[] = $filename;
        }

        // 15. 過剰金一覧(CSV)
        $filename = $this->CsvKajokinIchiran(" AND DailyMonthlyFlg = 1 " . $whereProcessingDate, $formatNowStr, $tmpFilePath);
        if ($filename != '' ) {
            $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
            $addFilePath = file_get_contents( $filename );
            $zip->addFromString( mb_convert_encoding($pathcutfilename, 'sjis-win'), $addFilePath );
            $unlinkList[] = $filename;
        }

        // 16. 再発行手数料明細(CSV)
        $filename = $this->CsvSaihakkotesuryoMeisaiMonth($formatNowStr, $tmpFilePath);
        if ($filename != '' ) {
            $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
            $addFilePath = file_get_contents( $filename );
            $zip->addFromString( mb_convert_encoding($pathcutfilename, 'sjis-win'), $addFilePath );
            $unlinkList[] = $filename;
        }

        // 17. 無保証立替金戻し明細(CSV)
        $filename = $this->CsvMuhoshoTatekaekinmodoshiMeisai(" AND DailyMonthlyFlg = 1 " . $whereProcessingDate, $formatNowStr, $tmpFilePath);
        if ($filename != '' ) {
            $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
            $addFilePath = file_get_contents( $filename );
            $zip->addFromString( mb_convert_encoding($pathcutfilename, 'sjis-win'), $addFilePath );
            $unlinkList[] = $filename;
        }

        // 18. OEM移管明細(CSV)
        $filename = $this->CsvOEMIkanMeisaiMonth($formatNowStr, $tmpFilePath);
        if ($filename != '' ) {
            $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
            $addFilePath = file_get_contents( $filename );
            $zip->addFromString( mb_convert_encoding($pathcutfilename, 'sjis-win'), $addFilePath );
            $unlinkList[] = $filename;
        }

        // 1. 直営日次統計表(CSV)
        $filename = $this->CsvCBNichijiTokeiMonth(" AND DailyMonthlyFlg = 1 " . $whereProcessingDate, $formatNowStr, $tmpFilePath);
        if ($filename != '' ) {
            $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
            $addFilePath = file_get_contents( $filename );
            $zip->addFromString( mb_convert_encoding($pathcutfilename, 'sjis-win'), $addFilePath );
            $unlinkList[] = $filename;
        }

        // 2. OEM日次統計表(CSV)
        $filename = $this->CsvOEMNichijiTokeiMonth(" AND DailyMonthlyFlg = 1 " . $whereProcessingDate, $formatNowStr, $tmpFilePath);
        if ($filename != '' ) {
            $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
            $addFilePath = file_get_contents( $filename );
            $zip->addFromString( mb_convert_encoding($pathcutfilename, 'sjis-win'), $addFilePath );
            $unlinkList[] = $filename;
        }

        // 1. 直営日次統計表(PDF)
        $filename = $this->PdfCBNichijiTokei(" AND DailyMonthlyFlg = 1 " . $whereProcessingDate, $formatNowStr, $tmpFilePath, 1);
        if ($filename != '' ) {
            $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
            $addFilePath = file_get_contents( $filename );
            $zip->addFromString( mb_convert_encoding($pathcutfilename, 'sjis-win'), $addFilePath );
            $unlinkList[] = $filename;
        }

        // 2. OEM日次統計表(PDF)
        $filename = $this->PdfOEMNichijiTokei(" AND DailyMonthlyFlg = 1 " . $whereProcessingDate, $formatNowStr, $tmpFilePath, 1);
        if ($filename != '' ) {
            $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
            $addFilePath = file_get_contents( $filename );
            $zip->addFromString( mb_convert_encoding($pathcutfilename, 'sjis-win'), $addFilePath );
            $unlinkList[] = $filename;
        }

        // 3. 直営未払金・売掛金・加盟店未収金統計表(PDF)
        $filename = $this->PdfCBMibaraiTokei('', $formatNowStr, $tmpFilePath, array(':AccountDate' => $accountDate));
        if ($filename != '' ) {
            $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
            $addFilePath = file_get_contents( $filename );
            $zip->addFromString( mb_convert_encoding($pathcutfilename, 'sjis-win'), $addFilePath );
            $unlinkList[] = $filename;
        }

        // 3. 直営未払金・売掛金・加盟店未収金統計表(CSV)
        $filename = $this->CsvCBMibaraiTokei('', $formatNowStr, $tmpFilePath, array(':AccountDate' => $accountDate));
        if ($filename != '' ) {
            $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
            $addFilePath = file_get_contents( $filename );
            $zip->addFromString( mb_convert_encoding($pathcutfilename, 'sjis-win'), $addFilePath );
            $unlinkList[] = $filename;
        }

        // 4. OEM未払金・売掛金・OEM未収金統計表(PDF)
        $datas04 = array();
        $filename = $this->PdfOEMMibaraiTokei('', $formatNowStr, $tmpFilePath, array(':AccountDate' => $accountDate), $datas04);
        if ($filename != '' ) {
            $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
            $addFilePath = file_get_contents( $filename );
            $zip->addFromString( mb_convert_encoding($pathcutfilename, 'sjis-win'), $addFilePath );
            $unlinkList[] = $filename;
        }

        // 4. OEM未払金・売掛金・OEM未収金統計表(CSV) ※Oem単位版
        foreach ($datas04 as $row_oem) {
            if ($row_oem['OemId'] == NULL) {
                continue;
            }
            $filename = $this->CsvOEMMibaraiTokeiByOem('', $formatNowStr, $tmpFilePath, array(':AccountDate' => $accountDate, ':OemId' => $row_oem['OemId']), $row_oem);
            if ($filename != '' ) {
                $pathcutfilename = str_replace( $tmpFilePath, '', $filename );
                $addFilePath = file_get_contents( $filename );
                $zip->addFromString( mb_convert_encoding($pathcutfilename, 'sjis-win'), $addFilePath );
                $unlinkList[] = $filename;
            }
        }

        // ZIPファイルクローズ
        $zip->close();

        // ヘッダ
        header( 'Content-Type: application/octet-stream; name="' . $outFileName . '"' );
        header( 'Content-Disposition: attachment; filename="' . $outFileName . '"' );
        header( 'Content-Length: ' . filesize( $tmpFilePath ) );

        // 出力
        echo readfile( $tmpFilePath );

        // count関数対策
        $unlinkListCount = 0;
        if (!empty($unlinkList)) {
            $unlinkListCount = count($unlinkList);
        }

         // TEMP領域削除
        for ($i=0; $i<$unlinkListCount; $i++) {
            unlink( $unlinkList[$i] );
        }
        unlink( $tmpFilePath );

        return $this->view;
    }

    //------------------------------------------------------------------------------
    // PDF群(Dairy)
    //------------------------------------------------------------------------------
    /**
     * PDF出力を行う[1. 直営日次統計表(PDF)]
     *
     * @param string $whereProcessingDate 抽出条件(指定日or月)
     * @param string $formatNowStr 書式化年月日時分秒
     * @param string $tmpFilePath TEMP領域
     * @param string $dailyMonthlyFlg 日次／月次フラグ
     * @return ファイル名 ※出力が行われなかった場合は''を戻す
     */
    protected function PdfCBNichijiTokei($whereProcessingDate, $formatNowStr, $tmpFilePath, $dailyMonthlyFlg)
    {
        $sql = $this->getCBNichijiTokeiBaseQuery_Summary();
        $sql = sprintf($sql, $whereProcessingDate, $whereProcessingDate);
        $ri = $this->app->dbAdapter->query($sql)->execute(null);
        $datas = ResultInterfaceToArray($ri);

        //-----------------------------
        // 調整(ここから)
        if (isset($this->_ary01[16])) {
            // (16.再発行手数料明細)
            $datas[0]['M_ReClaimFeeCount']                  = nvl($this->_ary01[16]['CNT_ClaimFee'], 0);
            $datas[0]['M_ReClaimFeeAmount']                 = nvl($this->_ary01[16]['SUM_ClaimFee'], 0);
            $datas[0]['M_DamageCount']                      = nvl($this->_ary01[16]['CNT_Dmg'], 0);
            $datas[0]['M_DamageAmount']                     = nvl($this->_ary01[16]['SUM_Dmg'], 0);
            $datas[0]['M_AddClaimFee']                      = nvl($this->_ary01[16]['SUM_ClaimFee'], 0) - nvl($this->_ary01[16]['SUM_ClaimFeeTax'], 0);    // [当月累計CB売上]項目
            $datas[0]['M_AddClaimFeeTax']                   = nvl($this->_ary01[16]['SUM_ClaimFeeTax'], 0); // [当月累計CB売上]項目
            $datas[0]['M_DamageInterestAmount']             = nvl($this->_ary01[16]['SUM_Dmg'], 0);         // [当月累計CB売上]項目
        }

        if (isset($this->_ary01[18])) {
            // (18.OEM移管明細)
            $datas[0]['M_TransferCount']                    = nvl($this->_ary01[18]['CNT_OemId'], 0);
            $datas[0]['M_TransferAmount']                   = nvl($this->_ary01[18]['SUM_Amount'], 0);
        }

        if (isset($this->_ary01[5])) {
            // (05.売上明細)
            $datas[0]['M_ChargeCount']                      = nvl($this->_ary01[5]['CNT_OemId'], 0);
            $datas[0]['M_ChargeAmount']                     = nvl($this->_ary01[5]['SUM_UseAmount'], 0);
            $datas[0]['M_CancelCount']                      = nvl($this->_ary01[5]['CNCLCNT_OemId'], 0);
            $datas[0]['M_CancelAmount']                     = nvl($this->_ary01[5]['CNCLSUM_UseAmount'], 0);

            $datas[0]['M_SettlementFee']                    = nvl($this->_ary01[5]['SUM_SettlementFee'], 0);
            $datas[0]['M_CanSettlementFee']                 = nvl($this->_ary01[5]['SUM_CNCLSettlementFee'], 0);
            $datas[0]['M_ClaimFee']                         = nvl($this->_ary01[5]['SUM_ClaimFee'], 0);
            $datas[0]['M_CanClaimFee']                      = nvl($this->_ary01[5]['SUM_CNCLClaimFee'], 0);
            $datas[0]['M_ClaimFeeTax']                      = nvl($this->_ary01[5]['SUM_ClaimFeeTax'], 0);
            $datas[0]['M_CanClaimFeeTax']                   = nvl($this->_ary01[5]['SUM_CNCLClaimFeeTax'], 0);

            $datas[0]['M_MonthlyFee']                       = nvl($this->_ary01[5]['SUM_MonthlyFee'], 0);
            $datas[0]['M_MonthlyFeeTax']                    = nvl($this->_ary01[5]['SUM_MonthlyFeeTax'], 0);
            $datas[0]['M_IncludeMonthlyFee']                = nvl($this->_ary01[5]['SUM_IncludeMonthlyFee'], 0);
            $datas[0]['M_IncludeMonthlyFeeTax']             = nvl($this->_ary01[5]['SUM_IncludeMonthlyFeeTax'], 0);
            $datas[0]['M_ApiMonthlyFee']                    = nvl($this->_ary01[5]['SUM_ApiMonthlyFee'], 0);
            $datas[0]['M_ApiMonthlyFeeTax']                 = nvl($this->_ary01[5]['SUM_ApiMonthlyFeeTax'], 0);
            $datas[0]['M_CreditNoticeMonthlyFee']           = nvl($this->_ary01[5]['SUM_CreditNoticeMonthlyFee'], 0);
            $datas[0]['M_CreditNoticeMonthlyFeeTax']        = nvl($this->_ary01[5]['SUM_CreditNoticeMonthlyFeeTax'], 0);
            $datas[0]['M_NCreditNoticeMonthlyFee']          = nvl($this->_ary01[5]['SUM_NCreditNoticeMonthlyFee'], 0);
            $datas[0]['M_NCreditNoticeMonthlyFeeTax']       = nvl($this->_ary01[5]['SUM_NCreditNoticeMonthlyFeeTax'], 0);

            // (<集計行>)
            $datas[0]['M_SettlementFeeTotal']               = $datas[0]['M_SettlementFee'] + $datas[0]['M_CanSettlementFee'];
            $datas[0]['M_ClaimFeeTotal']                    = $datas[0]['M_ClaimFee'] + $datas[0]['M_CanClaimFee'];
            $datas[0]['M_ClaimFeeTaxTotal']                 = $datas[0]['M_ClaimFeeTax'] + $datas[0]['M_CanClaimFeeTax'];
            $datas[0]['M_MonthlyFeeTotal']                  = $datas[0]['M_MonthlyFee'];
            $datas[0]['M_MonthlyFeeTaxTotal']               = $datas[0]['M_MonthlyFeeTax'];
            $datas[0]['M_IncludeMonthlyFeeTotal']           = $datas[0]['M_IncludeMonthlyFee'];
            $datas[0]['M_IncludeMonthlyFeeTaxTotal']        = $datas[0]['M_IncludeMonthlyFeeTax'];
            $datas[0]['M_ApiMonthlyFeeTotal']               = $datas[0]['M_ApiMonthlyFee'];
            $datas[0]['M_ApiMonthlyFeeTaxTotal']            = $datas[0]['M_ApiMonthlyFeeTax'];
            $datas[0]['M_CreditNoticeMonthlyFeeTotal']      = $datas[0]['M_CreditNoticeMonthlyFee'];
            $datas[0]['M_CreditNoticeMonthlyFeeTaxTotal']   = $datas[0]['M_CreditNoticeMonthlyFeeTax'];
            $datas[0]['M_NCreditNoticeMonthlyFeeTotal']     = $datas[0]['M_NCreditNoticeMonthlyFee'];
            $datas[0]['M_NCreditNoticeMonthlyFeeTaxTotal']  = $datas[0]['M_NCreditNoticeMonthlyFeeTax'];
            $datas[0]['M_AddClaimFeeTotal']                 = $datas[0]['M_AddClaimFee'];
            $datas[0]['M_AddClaimFeeTaxTotal']              = $datas[0]['M_AddClaimFeeTax'];
            $datas[0]['M_DamageInterestAmountTotal']        = $datas[0]['M_DamageInterestAmount'];
            $datas[0]['M_AllTotal']                         =   $datas[0]['M_SettlementFeeTotal']
                                                            + $datas[0]['M_ClaimFeeTotal']
                                                            + $datas[0]['M_ClaimFeeTaxTotal']
                                                            + $datas[0]['M_MonthlyFeeTotal']
                                                            + $datas[0]['M_MonthlyFeeTaxTotal']
                                                            + $datas[0]['M_IncludeMonthlyFeeTotal']
                                                            + $datas[0]['M_IncludeMonthlyFeeTaxTotal']
                                                            + $datas[0]['M_ApiMonthlyFeeTotal']
                                                            + $datas[0]['M_ApiMonthlyFeeTaxTotal']
                                                            + $datas[0]['M_CreditNoticeMonthlyFeeTotal']
                                                            + $datas[0]['M_CreditNoticeMonthlyFeeTaxTotal']
                                                            + $datas[0]['M_NCreditNoticeMonthlyFeeTotal']
                                                            + $datas[0]['M_NCreditNoticeMonthlyFeeTaxTotal']
                                                            + $datas[0]['M_AddClaimFeeTotal']
                                                            + $datas[0]['M_AddClaimFeeTaxTotal']
                                                            + $datas[0]['M_DamageInterestAmountTotal'];

            $datas[0]['M_SettlementFeeOther']               = $datas[0]['M_SettlementFeeTotal'];
            $datas[0]['M_ClaimFeeOther']                    = $datas[0]['M_ClaimFeeTotal'];
            $datas[0]['M_ClaimFeeTaxOther']                 = $datas[0]['M_ClaimFeeTaxTotal'];
            $datas[0]['M_MonthlyFeeOther']                  = $datas[0]['M_MonthlyFeeTotal'];
            $datas[0]['M_MonthlyFeeTaxOther']               = $datas[0]['M_MonthlyFeeTaxTotal'];
            $datas[0]['M_IncludeMonthlyFeeOther']           = $datas[0]['M_IncludeMonthlyFeeTotal'];
            $datas[0]['M_IncludeMonthlyFeeTaxOther']        = $datas[0]['M_IncludeMonthlyFeeTaxTotal'];
            $datas[0]['M_ApiMonthlyFeeOther']               = $datas[0]['M_ApiMonthlyFeeTotal'];
            $datas[0]['M_ApiMonthlyFeeTaxOther']            = $datas[0]['M_ApiMonthlyFeeTaxTotal'];
            $datas[0]['M_CreditNoticeMonthlyFeeOther']      = $datas[0]['M_CreditNoticeMonthlyFeeTotal'];
            $datas[0]['M_CreditNoticeMonthlyFeeTaxOther']   = $datas[0]['M_CreditNoticeMonthlyFeeTaxTotal'];
            $datas[0]['M_NCreditNoticeMonthlyFeeOther']     = $datas[0]['M_NCreditNoticeMonthlyFeeTotal'];
            $datas[0]['M_NCreditNoticeMonthlyFeeTaxOther']  = $datas[0]['M_NCreditNoticeMonthlyFeeTaxTotal'];
            $datas[0]['M_AddClaimFeeOther']                 = $datas[0]['M_AddClaimFeeTotal'];
            $datas[0]['M_AddClaimFeeTaxOther']              = $datas[0]['M_AddClaimFeeTaxTotal'];
            $datas[0]['M_DamageInterestAmountOther']        = $datas[0]['M_DamageInterestAmountTotal'];

            $datas[0]['M_SettlementFeeDiff']                = 0;
            $datas[0]['M_ClaimFeeDiff']                     = 0;
            $datas[0]['M_ClaimFeeTaxDiff']                  = 0;
            $datas[0]['M_MonthlyFeeDiff']                   = 0;
            $datas[0]['M_MonthlyFeeTaxDiff']                = 0;
            $datas[0]['M_IncludeMonthlyFeeDiff']            = 0;
            $datas[0]['M_IncludeMonthlyFeeTaxDiff']         = 0;
            $datas[0]['M_ApiMonthlyFeeDiff']                = 0;
            $datas[0]['M_ApiMonthlyFeeTaxDiff']             = 0;
            $datas[0]['M_CreditNoticeMonthlyFeeDiff']       = 0;
            $datas[0]['M_CreditNoticeMonthlyFeeTaxDiff']    = 0;
            $datas[0]['M_NCreditNoticeMonthlyFeeDiff']      = 0;
            $datas[0]['M_NCreditNoticeMonthlyFeeTaxDiff']   = 0;
            $datas[0]['M_AddClaimFeeDiff']                  = 0;
            $datas[0]['M_AddClaimFeeTaxDiff']               = 0;
            $datas[0]['M_DamageInterestAmountDiff']         = 0;
        }

        if (isset($this->_ary01[6])) {
            // (06.消費者未収金明細)
            ;// 考慮不要の明示 ※CSV出力時に適切にUPDATE/INSERTが行われている為(調整済み)
        }

        if (isset($this->_ary01[11])) {
            // (11.未収金日計) ﾏｯﾋﾟﾝｸﾞ＝0:入金／1:返金／2:貸倒／3:その他
            $datas[0]['M_ReceiptCount']         = nvl($this->_ary01[11]['CNT_SUMType0'], 0);
            $datas[0]['M_ReceiptAmount']        = nvl($this->_ary01[11]['SUM_Amount0'] , 0);
            $datas[0]['M_RepayCount']           = nvl($this->_ary01[11]['CNT_SUMType1'], 0);
            $datas[0]['M_RepayAmount']          = nvl($this->_ary01[11]['SUM_Amount1'] , 0);
            $datas[0]['M_BadDebtCount']         = nvl($this->_ary01[11]['CNT_SUMType2'], 0);
            $datas[0]['M_BadDebtAmount']        = nvl($this->_ary01[11]['SUM_Amount2'] , 0);
            $datas[0]['M_OtherPaymentCount']    = nvl($this->_ary01[11]['CNT_SUMType3'], 0);
            $datas[0]['M_OtherPaymentAmount']   = nvl($this->_ary01[11]['SUM_Amount3'] , 0);
        }

        $this->_ary01sub = $datas;

        // 調整(ここまで)
        //-----------------------------

        $fileName  = ('01.直営日次統計表_' . $formatNowStr . '.pdf');
        $tmpFileName = $tmpFilePath . $fileName;

        $this->setTemplate('atchokueinichijitokei');
        $this->view->assign('datas', $datas);
        $this->view->assign('DailyMonthlyFlg', $dailyMonthlyFlg);
        $this->view->assign('documentRoot', $_SERVER['DOCUMENT_ROOT']);
        $this->view->assign('title', $fileName);

        $fileName  = ('01.CBNichijiTokei_' . $formatNowStr . '.pdf');

        // HTML作成
        $viewRender = $this->getServiceLocator()->get('ViewRenderer');
        $html = $viewRender->render($this->view);

        // 一時ファイルの保存先
        $mdlsp = new \models\Table\TableSystemProperty($this->app->dbAdapter);
        $tempDir = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'TempFileDir');
        $tempDir = realpath($tempDir);

        // 中間ファイル名
        $fname_html = ($tempDir . '/__tmp_' . $fileName . '__.html');
        $fname_pdf  = ($tempDir . '/__tmp_' . $fileName . '__.pdf');

        // HTML出力
        file_put_contents($fname_html, $html);

        // PDF変換(外部プログラム起動)
        $ename = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'wkhtmltopdf');
        $option = " --page-size A3 --orientation landscape --margin-left 0 --margin-right 0 --margin-top 0 --margin-bottom 0 ";
        exec($ename . $option . $fname_html . ' ' . $fname_pdf);

        // ファイルの読み込み
        $pdf = file_get_contents($fname_pdf);

        // ファイルに保存
        file_put_contents($tmpFileName, $pdf);

        unlink( $fname_html );
        unlink( $fname_pdf );

        return $tmpFileName;
    }

    /**
     * PDF出力を行う[2. OEM日次統計表(PDF)]
     *
     * @param string $whereProcessingDate 抽出条件(指定日or月)
     * @param string $formatNowStr 書式化年月日時分秒
     * @param string $tmpFilePath TEMP領域
     * @return ファイル名 ※出力が行われなかった場合は''を戻す
     */
    protected function PdfOEMNichijiTokei($whereProcessingDate, $formatNowStr, $tmpFilePath, $dailyMonthlyFlg)
    {
        // OEM単位取得
        $sql = " SELECT DISTINCT AccountDate, OemId FROM AT_Oem_DailyStatisticsTable WHERE 1 = 1 " . $whereProcessingDate . " ORDER BY AccountDate, OemId ";        // 2016/02/10 Y.Suzuki 会計月が混在する場合の考慮 Mod
        $ri_oem = $this->app->dbAdapter->query($sql)->execute(null);
        // 2016/02/10 Y.Suzuki Mod 会計月が混在する場合の考慮 Stt
        // 初回かどうかの判定
        $isFirst = true;
        foreach ($ri_oem as $row_oem) {
            // 会計月を取得
            $accountDate = $row_oem['AccountDate'];
            if ($isFirst) {
                // バックアップ用変数に初期値を設定
                $accountDateBk = $row_oem['AccountDate'];
                // ﾌﾗｸﾞを落とす。
                $isFirst = false;
            }
            // 会計月が変わっていたら、総合計を取得
            if ($accountDateBk <> $accountDate) {
                // サマリー取得
                $sql = $this->getOEMNichijiTokeiBaseQuery_Summary();
                $whereAccountDate = " AND AccountDate = '" . $accountDateBk . "'";
                $sql = sprintf($sql, $whereProcessingDate . $whereAccountDate, $whereProcessingDate . $whereAccountDate);
                $datas[] = $this->app->dbAdapter->query($sql)->execute(null)->current();
            }

            // OEM + 会計月の総計を取得
            $sql = $this->getOEMNichijiTokeiSubTotalQuery();
            $whereAccountDate = " AND AccountDate = '" . $accountDate . "'";
            $sql = sprintf($sql, $whereProcessingDate . $whereAccountDate, $whereProcessingDate . $whereAccountDate);
            $datas[] = $this->app->dbAdapter->query($sql)->execute(array(':OemId' => $row_oem['OemId']))->current();

            // 会計月をバックアップ
            $accountDateBk = $accountDate;
        }

        // サマリー取得
        $sql = $this->getOEMNichijiTokeiBaseQuery_Summary();
        $sql = sprintf($sql, $whereProcessingDate . $whereAccountDate, $whereProcessingDate . $whereAccountDate);
        $datas[] = $this->app->dbAdapter->query($sql)->execute(null)->current();
        // 2016/02/10 Y.Suzuki Mod 会計月が混在する場合の考慮 End

        //-----------------------------
        // 調整
        if (isset($this->_ary02[16])) {
            // (16.再発行手数料明細)
            $CNT_ClaimFee = 0;
            $SUM_ClaimFee = 0;
            $CNT_Dmg = 0;
            $SUM_Dmg = 0;
            $SUM_ClaimFeeTax = 0;
            foreach ($this->_ary02[16] as $ar) {
                $CNT_ClaimFee += $ar['CNT_ClaimFee'];
                $SUM_ClaimFee += $ar['SUM_ClaimFee'];
                $CNT_Dmg += $ar['CNT_Dmg'];
                $SUM_Dmg += $ar['SUM_Dmg'];
                $SUM_ClaimFeeTax += $ar['SUM_ClaimFeeTax'];
            }

            // count関数対策
            $datas_count = 0;
            if (!empty($datas)) {
                $datas_count = count($datas);
            }

            for ($i=0; $i<$datas_count; $i++) {
                if (is_null($datas[$i]['OemId'])) {
                    $datas[$i]['M_ReClaimFeeCount']     = $CNT_ClaimFee;
                    $datas[$i]['M_ReClaimFeeAmount']    = $SUM_ClaimFee;
                    $datas[$i]['M_DamageCount']         = $CNT_Dmg;
                    $datas[$i]['M_DamageAmount']        = $SUM_Dmg;
                    $datas[$i]['M_AddClaimFee']         = $SUM_ClaimFee - $SUM_ClaimFeeTax;    // [当月累計CB売上]項目
                    $datas[$i]['M_AddClaimFeeTax']      = $SUM_ClaimFeeTax; // [当月累計CB売上]項目
                    $datas[$i]['M_DamageInterestAmount']= $SUM_Dmg;         // [当月累計CB売上]項目
                }
                else {
                    $datas[$i]['M_ReClaimFeeCount']     = nvl($this->_ary02[16][$datas[$i]['OemId']]['CNT_ClaimFee'], 0);
                    $datas[$i]['M_ReClaimFeeAmount']    = nvl($this->_ary02[16][$datas[$i]['OemId']]['SUM_ClaimFee'], 0);
                    $datas[$i]['M_DamageCount']         = nvl($this->_ary02[16][$datas[$i]['OemId']]['CNT_Dmg'], 0);
                    $datas[$i]['M_DamageAmount']        = nvl($this->_ary02[16][$datas[$i]['OemId']]['SUM_Dmg'], 0);
                    $datas[$i]['M_AddClaimFee']         = nvl($this->_ary02[16][$datas[$i]['OemId']]['SUM_ClaimFee'], 0) - nvl($this->_ary02[16][$datas[$i]['OemId']]['SUM_ClaimFeeTax'], 0);   // [当月累計CB売上]項目
                    $datas[$i]['M_AddClaimFeeTax']      = nvl($this->_ary02[16][$datas[$i]['OemId']]['SUM_ClaimFeeTax'], 0);// [当月累計CB売上]項目
                    $datas[$i]['M_DamageInterestAmount']= nvl($this->_ary02[16][$datas[$i]['OemId']]['SUM_Dmg'], 0);        // [当月累計CB売上]項目
                }
            }
        }

        if (isset($this->_ary02[18])) {
            // (18.OEM移管明細)
            $CNT_OemId = 0;
            $SUM_Amount = 0;
            foreach ($this->_ary02[18] as $ar) {
                $CNT_OemId += $ar['CNT_OemId'];
                $SUM_Amount += $ar['SUM_Amount'];
            }

            // count関数対策
            $datas_count = 0;
            if (!empty($datas)) {
                $datas_count = count($datas);
            }

            for ($i=0; $i<$datas_count; $i++) {
                if (is_null($datas[$i]['OemId'])) {
                    $datas[$i]['M_OemTransferCount']  = $CNT_OemId;
                    $datas[$i]['M_OemTransferAmount'] = $SUM_Amount;
                }
                else {
                    $datas[$i]['M_OemTransferCount']  = nvl($this->_ary02[18][$datas[$i]['OemId']]['CNT_OemId'], 0);
                    $datas[$i]['M_OemTransferAmount'] = nvl($this->_ary02[18][$datas[$i]['OemId']]['SUM_Amount'], 0);
                }
            }
        }

        if (isset($this->_ary02[5])) {
            // (05.売上明細)
            $CNT_OemId = 0;
            $SUM_UseAmount = 0;
            $CNCLCNT_OemId = 0;
            $CNCLSUM_UseAmount = 0;

            $SUM_SettlementFee = 0;
            $SUM_CNCLSettlementFee = 0;
            $SUM_ClaimFee = 0;
            $SUM_CNCLClaimFee = 0;
            $SUM_SettlementFeeTax = 0;
            $SUM_CNCLSettlementFeeTax = 0;
            $SUM_ClaimFeeTax = 0;
            $SUM_CNCLClaimFeeTax = 0;

            $SUM_MonthlyFee = 0;
            $SUM_MonthlyFeeTax = 0;
            $SUM_IncludeMonthlyFee = 0;
            $SUM_IncludeMonthlyFeeTax = 0;
            $SUM_ApiMonthlyFee = 0;
            $SUM_ApiMonthlyFeeTax = 0;
            $SUM_CreditNoticeMonthlyFee = 0;
            $SUM_CreditNoticeMonthlyFeeTax = 0;
            $SUM_NCreditNoticeMonthlyFee = 0;
            $SUM_NCreditNoticeMonthlyFeeTax = 0;

            foreach ($this->_ary02[5] as $ar) {
                $CNT_OemId += $ar['CNT_OemId'];
                $SUM_UseAmount += $ar['SUM_UseAmount'];
                $CNCLCNT_OemId += $ar['CNCLCNT_OemId'];
                $CNCLSUM_UseAmount += $ar['CNCLSUM_UseAmount'];

                $SUM_SettlementFee += $ar['SUM_SettlementFee'];
                $SUM_CNCLSettlementFee += $ar['SUM_CNCLSettlementFee'];
                $SUM_ClaimFee += $ar['SUM_ClaimFee'];
                $SUM_CNCLClaimFee += $ar['SUM_CNCLClaimFee'];
                $SUM_SettlementFeeTax += $ar['SUM_SettlementFeeTax'];
                $SUM_CNCLSettlementFeeTax += $ar['SUM_CNCLSettlementFeeTax'];
                $SUM_ClaimFeeTax += $ar['SUM_ClaimFeeTax'];
                $SUM_CNCLClaimFeeTax += $ar['SUM_CNCLClaimFeeTax'];

                $SUM_MonthlyFee += $ar['SUM_MonthlyFee'];
                $SUM_MonthlyFeeTax += $ar['SUM_MonthlyFeeTax'];
                $SUM_IncludeMonthlyFee += $ar['SUM_IncludeMonthlyFee'];
                $SUM_IncludeMonthlyFeeTax += $ar['SUM_IncludeMonthlyFeeTax'];
                $SUM_ApiMonthlyFee += $ar['SUM_ApiMonthlyFee'];
                $SUM_ApiMonthlyFeeTax += $ar['SUM_ApiMonthlyFeeTax'];
                $SUM_CreditNoticeMonthlyFee += $ar['SUM_CreditNoticeMonthlyFee'];
                $SUM_CreditNoticeMonthlyFeeTax += $ar['SUM_CreditNoticeMonthlyFeeTax'];
                $SUM_NCreditNoticeMonthlyFee += $ar['SUM_NCreditNoticeMonthlyFee'];
                $SUM_NCreditNoticeMonthlyFeeTax += $ar['SUM_NCreditNoticeMonthlyFeeTax'];
            }

            // count関数対策
            $datas_count = 0;
            if (!empty($datas)) {
                $datas_count = count($datas);
            }

            for ($i=0; $i<$datas_count; $i++) {
                if (is_null($datas[$i]['OemId'])) {
                    $datas[$i]['M_ChargeCount']  = $CNT_OemId;
                    $datas[$i]['M_ChargeAmount'] = $SUM_UseAmount;
                    $datas[$i]['M_CancelCount']  = $CNCLCNT_OemId;
                    $datas[$i]['M_CancelAmount'] = $CNCLSUM_UseAmount;

                    $datas[$i]['M_SettlementFee'] = $SUM_SettlementFee;
                    $datas[$i]['M_CanSettlementFee'] = $SUM_CNCLSettlementFee;
                    $datas[$i]['M_ClaimFee'] = $SUM_ClaimFee;
                    $datas[$i]['M_CanClaimFee'] = $SUM_CNCLClaimFee;
                    $datas[$i]['M_SettlementFeeTax'] = $SUM_SettlementFeeTax;
                    $datas[$i]['M_CanSettlementFeeTax'] = $SUM_CNCLSettlementFeeTax;
                    $datas[$i]['M_ClaimFeeTax'] = $SUM_ClaimFeeTax;
                    $datas[$i]['M_CanClaimFeeTax'] = $SUM_CNCLClaimFeeTax;

                    $datas[$i]['M_MonthlyFee'] = $SUM_MonthlyFee;
                    $datas[$i]['M_MonthlyFeeTax'] = $SUM_MonthlyFeeTax;
                    $datas[$i]['M_OemIncludeMonthlyFee'] = $SUM_IncludeMonthlyFee;
                    $datas[$i]['M_OemIncludeMonthlyFeeTax'] = $SUM_IncludeMonthlyFeeTax;
                    $datas[$i]['M_OemApiMonthlyFee'] = $SUM_ApiMonthlyFee;
                    $datas[$i]['M_OemApiMonthlyFeeTax'] = $SUM_ApiMonthlyFeeTax;
                    $datas[$i]['M_OemCreditNoticeMonthlyFee'] = $SUM_CreditNoticeMonthlyFee;
                    $datas[$i]['M_OemCreditNoticeMonthlyFeeTax'] = $SUM_CreditNoticeMonthlyFeeTax;
                    $datas[$i]['M_OemNCreditNoticeMonthlyFee'] = $SUM_NCreditNoticeMonthlyFee;
                    $datas[$i]['M_OemNCreditNoticeMonthlyFeeTax'] = $SUM_NCreditNoticeMonthlyFeeTax;
                }
                else {
                    $datas[$i]['M_ChargeCount']  = nvl($this->_ary02[5][$datas[$i]['OemId']]['CNT_OemId'], 0);
                    $datas[$i]['M_ChargeAmount'] = nvl($this->_ary02[5][$datas[$i]['OemId']]['SUM_UseAmount'], 0);
                    $datas[$i]['M_CancelCount']  = nvl($this->_ary02[5][$datas[$i]['OemId']]['CNCLCNT_OemId'], 0);
                    $datas[$i]['M_CancelAmount'] = nvl($this->_ary02[5][$datas[$i]['OemId']]['CNCLSUM_UseAmount'], 0);

                    $datas[$i]['M_SettlementFee']  = nvl($this->_ary02[5][$datas[$i]['OemId']]['SUM_SettlementFee'], 0);
                    $datas[$i]['M_CanSettlementFee']  = nvl($this->_ary02[5][$datas[$i]['OemId']]['SUM_CNCLSettlementFee'], 0);
                    $datas[$i]['M_ClaimFee']  = nvl($this->_ary02[5][$datas[$i]['OemId']]['SUM_ClaimFee'], 0);
                    $datas[$i]['M_CanClaimFee']  = nvl($this->_ary02[5][$datas[$i]['OemId']]['SUM_CNCLClaimFee'], 0);
                    $datas[$i]['M_SettlementFeeTax']  = nvl($this->_ary02[5][$datas[$i]['OemId']]['SUM_SettlementFeeTax'], 0);
                    $datas[$i]['M_CanSettlementFeeTax']  = nvl($this->_ary02[5][$datas[$i]['OemId']]['SUM_CNCLSettlementFeeTax'], 0);
                    $datas[$i]['M_ClaimFeeTax']  = nvl($this->_ary02[5][$datas[$i]['OemId']]['SUM_ClaimFeeTax'], 0);
                    $datas[$i]['M_CanClaimFeeTax']  = nvl($this->_ary02[5][$datas[$i]['OemId']]['SUM_CNCLClaimFeeTax'], 0);

                    $datas[$i]['M_MonthlyFee']  = nvl($this->_ary02[5][$datas[$i]['OemId']]['SUM_MonthlyFee'], 0);
                    $datas[$i]['M_MonthlyFeeTax']  = nvl($this->_ary02[5][$datas[$i]['OemId']]['SUM_MonthlyFeeTax'], 0);
                    $datas[$i]['M_OemIncludeMonthlyFee']  = nvl($this->_ary02[5][$datas[$i]['OemId']]['SUM_IncludeMonthlyFee'], 0);
                    $datas[$i]['M_OemIncludeMonthlyFeeTax']  = nvl($this->_ary02[5][$datas[$i]['OemId']]['SUM_IncludeMonthlyFeeTax'], 0);
                    $datas[$i]['M_OemApiMonthlyFee']  = nvl($this->_ary02[5][$datas[$i]['OemId']]['SUM_ApiMonthlyFee'], 0);
                    $datas[$i]['M_OemApiMonthlyFeeTax']  = nvl($this->_ary02[5][$datas[$i]['OemId']]['SUM_ApiMonthlyFeeTax'], 0);
                    $datas[$i]['M_OemCreditNoticeMonthlyFee']  = nvl($this->_ary02[5][$datas[$i]['OemId']]['SUM_CreditNoticeMonthlyFee'], 0);
                    $datas[$i]['M_OemCreditNoticeMonthlyFeeTax']  = nvl($this->_ary02[5][$datas[$i]['OemId']]['SUM_CreditNoticeMonthlyFeeTax'], 0);
                    $datas[$i]['M_OemNCreditNoticeMonthlyFee']  = nvl($this->_ary02[5][$datas[$i]['OemId']]['SUM_NCreditNoticeMonthlyFee'], 0);
                    $datas[$i]['M_OemNCreditNoticeMonthlyFeeTax']  = nvl($this->_ary02[5][$datas[$i]['OemId']]['SUM_NCreditNoticeMonthlyFeeTax'], 0);
                }

                // (<集計行:共通>)
                $datas[$i]['M_SettlementFeeTotal']                  = $datas[$i]['M_SettlementFee'] + $datas[$i]['M_CanSettlementFee'];
                $datas[$i]['M_SettlementFeeTaxTotal']               = $datas[$i]['M_SettlementFeeTax'] + $datas[$i]['M_CanSettlementFeeTax'];
                $datas[$i]['M_ClaimFeeTotal']                       = $datas[$i]['M_ClaimFee'] + $datas[$i]['M_CanClaimFee'];
                $datas[$i]['M_ClaimFeeTaxTotal']                    = $datas[$i]['M_ClaimFeeTax'] + $datas[$i]['M_CanClaimFeeTax'];
                $datas[$i]['M_MonthlyFeeTotal']                     = $datas[$i]['M_MonthlyFee'];
                $datas[$i]['M_MonthlyFeeTaxTotal']                  = $datas[$i]['M_MonthlyFeeTax'];
                $datas[$i]['M_OemIncludeMonthlyFeeTotal']           = $datas[$i]['M_OemIncludeMonthlyFee'];
                $datas[$i]['M_OemIncludeMonthlyFeeTaxTotal']        = $datas[$i]['M_OemIncludeMonthlyFeeTax'];
                $datas[$i]['M_OemApiMonthlyFeeTotal']               = $datas[$i]['M_OemApiMonthlyFee'];
                $datas[$i]['M_OemApiMonthlyFeeTaxTotal']            = $datas[$i]['M_OemApiMonthlyFeeTax'];
                $datas[$i]['M_OemCreditNoticeMonthlyFeeTotal']      = $datas[$i]['M_OemCreditNoticeMonthlyFee'];
                $datas[$i]['M_OemCreditNoticeMonthlyFeeTaxTotal']   = $datas[$i]['M_OemCreditNoticeMonthlyFeeTax'];
                $datas[$i]['M_OemNCreditNoticeMonthlyFeeTotal']     = $datas[$i]['M_OemNCreditNoticeMonthlyFee'];
                $datas[$i]['M_OemNCreditNoticeMonthlyFeeTaxTotal']  = $datas[$i]['M_OemNCreditNoticeMonthlyFeeTax'];
                $datas[$i]['M_AddClaimFeeTotal']                    = $datas[$i]['M_AddClaimFee'];
                $datas[$i]['M_AddClaimFeeTaxTotal']                 = $datas[$i]['M_AddClaimFeeTax'];
                $datas[$i]['M_DamageInterestAmountTotal']           = $datas[$i]['M_DamageInterestAmount'];
                $datas[$i]['M_AllTotal']                            = $datas[$i]['M_SettlementFeeTotal']
                                                                    + $datas[$i]['M_SettlementFeeTaxTotal']
                                                                    + $datas[$i]['M_ClaimFeeTotal']
                                                                    + $datas[$i]['M_ClaimFeeTaxTotal']
                                                                    + $datas[$i]['M_MonthlyFeeTotal']
                                                                    + $datas[$i]['M_MonthlyFeeTaxTotal']
                                                                    + $datas[$i]['M_OemIncludeMonthlyFeeTotal']
                                                                    + $datas[$i]['M_OemIncludeMonthlyFeeTaxTotal']
                                                                    + $datas[$i]['M_OemApiMonthlyFeeTotal']
                                                                    + $datas[$i]['M_OemApiMonthlyFeeTaxTotal']
                                                                    + $datas[$i]['M_OemCreditNoticeMonthlyFeeTotal']
                                                                    + $datas[$i]['M_OemCreditNoticeMonthlyFeeTaxTotal']
                                                                    + $datas[$i]['M_OemNCreditNoticeMonthlyFeeTotal']
                                                                    + $datas[$i]['M_OemNCreditNoticeMonthlyFeeTaxTotal']
                                                                    + $datas[$i]['M_AddClaimFeeTotal']
                                                                    + $datas[$i]['M_AddClaimFeeTaxTotal']
                                                                    + $datas[$i]['M_DamageInterestAmountTotal'];

                $datas[$i]['M_SettlementFeeOther']                  = $datas[$i]['M_SettlementFeeTotal'];
                $datas[$i]['M_SettlementFeeTaxOther']               = $datas[$i]['M_SettlementFeeTaxTotal'];
                $datas[$i]['M_ClaimFeeOther']                       = $datas[$i]['M_ClaimFeeTotal'];
                $datas[$i]['M_ClaimFeeTaxOther']                    = $datas[$i]['M_ClaimFeeTaxTotal'];
                $datas[$i]['M_MonthlyFeeOther']                     = $datas[$i]['M_MonthlyFeeTotal'];
                $datas[$i]['M_MonthlyFeeTaxOther']                  = $datas[$i]['M_MonthlyFeeTaxTotal'];
                $datas[$i]['M_OemIncludeMonthlyFeeOther']           = $datas[$i]['M_OemIncludeMonthlyFeeTotal'];
                $datas[$i]['M_OemIncludeMonthlyFeeTaxOther']        = $datas[$i]['M_OemIncludeMonthlyFeeTaxTotal'];
                $datas[$i]['M_OemApiMonthlyFeeOther']               = $datas[$i]['M_OemApiMonthlyFeeTotal'];
                $datas[$i]['M_OemApiMonthlyFeeTaxOther']            = $datas[$i]['M_OemApiMonthlyFeeTaxTotal'];
                $datas[$i]['M_OemCreditNoticeMonthlyFeeOther']      = $datas[$i]['M_OemCreditNoticeMonthlyFeeTotal'];
                $datas[$i]['M_OemCreditNoticeMonthlyFeeTaxOther']   = $datas[$i]['M_OemCreditNoticeMonthlyFeeTaxTotal'];
                $datas[$i]['M_OemNCreditNoticeMonthlyFeeOther']     = $datas[$i]['M_OemNCreditNoticeMonthlyFeeTotal'];
                $datas[$i]['M_OemNCreditNoticeMonthlyFeeTaxOther']  = $datas[$i]['M_OemNCreditNoticeMonthlyFeeTaxTotal'];
                $datas[$i]['M_AddClaimFeeOther']                    = $datas[$i]['M_AddClaimFeeTotal'];
                $datas[$i]['M_AddClaimFeeTaxOther']                 = $datas[$i]['M_AddClaimFeeTaxTotal'];
                $datas[$i]['M_DamageInterestAmountOther']           = $datas[$i]['M_DamageInterestAmountTotal'];

                $datas[$i]['M_SettlementFeeDiff']                   = 0;
                $datas[$i]['M_SettlementFeeTaxDiff']                = 0;
                $datas[$i]['M_ClaimFeeDiff']                        = 0;
                $datas[$i]['M_ClaimFeeTaxDiff']                     = 0;
                $datas[$i]['M_MonthlyFeeDiff']                      = 0;
                $datas[$i]['M_MonthlyFeeTaxDiff']                   = 0;
                $datas[$i]['M_OemIncludeMonthlyFeeDiff']            = 0;
                $datas[$i]['M_OemIncludeMonthlyFeeTaxDiff']         = 0;
                $datas[$i]['M_OemApiMonthlyFeeDiff']                = 0;
                $datas[$i]['M_OemApiMonthlyFeeTaxDiff']             = 0;
                $datas[$i]['M_OemCreditNoticeMonthlyFeeDiff']       = 0;
                $datas[$i]['M_OemCreditNoticeMonthlyFeeTaxDiff']    = 0;
                $datas[$i]['M_OemNCreditNoticeMonthlyFeeDiff']      = 0;
                $datas[$i]['M_OemNCreditNoticeMonthlyFeeTaxDiff']   = 0;
                $datas[$i]['M_AddClaimFeeDiff']                     = 0;
                $datas[$i]['M_AddClaimFeeTaxDiff']                  = 0;
                $datas[$i]['M_DamageInterestAmountDiff']            = 0;
            }
        }

        if (isset($this->_ary02[8])) {
            // (08.OEM仮払金明細)
            $SUM_BAccountsReceivableBalance     = 0; // 月初未収金残高
            $SUM_AccountsReceivableBalance      = 0; // 当月時点未収金残高
            $SUM_SuspensePaymentsAmount         = 0; // OEM仮払金明細残高
            $SUM_AccountsReceivableBalanceDiff  = 0; // 差額

            ;// 「_ary02」に対する考慮不要の明示 ※CSV出力時に適切にUPDATE/INSERTが行われている為(調整済み)

            // count関数対策
            $datas_count = 0;
            if (!empty($datas)) {
                $datas_count = count($datas);
            }

            for ($i=0; $i<$datas_count; $i++) {
                if (is_null($datas[$i]['OemId'])) {
                    $datas[$i]['MB__AccountsReceivableBalance']     = $SUM_BAccountsReceivableBalance;
                    $datas[$i]['M_AccountsReceivableBalance']       = $SUM_AccountsReceivableBalance;
                    $datas[$i]['M_SuspensePaymentsAmount']          = $SUM_SuspensePaymentsAmount;
                    $datas[$i]['M_AccountsReceivableBalanceDiff']   = $SUM_AccountsReceivableBalanceDiff;
                }
                else {
                    $SUM_BAccountsReceivableBalance     += $datas[$i]['MB__AccountsReceivableBalance'];
                    $SUM_AccountsReceivableBalance      += $datas[$i]['M_AccountsReceivableBalance'];
                    $SUM_SuspensePaymentsAmount         += $datas[$i]['M_SuspensePaymentsAmount'];
                    $SUM_AccountsReceivableBalanceDiff  += $datas[$i]['M_AccountsReceivableBalanceDiff'];
                }
            }
        }

        if (isset($this->_ary02[12])) {
            // (12.仮払金日計) ﾏｯﾋﾟﾝｸﾞ＝0:入金／1:返金／2:貸倒／3:その他
            $CNT_SUMType0 = 0;
            $SUM_Amount0  = 0;
            $CNT_SUMType1 = 0;
            $SUM_Amount1  = 0;
            $CNT_SUMType3 = 0;
            $SUM_Amount3  = 0;

            foreach ($this->_ary02[12] as $ar) {
                $CNT_SUMType0 += $ar['CNT_SUMType0'];
                $SUM_Amount0  += $ar['SUM_Amount0'];
                $CNT_SUMType1 += $ar['CNT_SUMType1'];
                $SUM_Amount1  += $ar['SUM_Amount1'];
                $CNT_SUMType3 += $ar['CNT_SUMType3'];
                $SUM_Amount3  += $ar['SUM_Amount3'];
            }

            // count関数対策
            $datas_count = 0;
            if (!empty($datas)) {
                $datas_count = count($datas);
            }

            for ($i=0; $i<$datas_count; $i++) {
                if (is_null($datas[$i]['OemId'])) {
                    $datas[$i]['M_ReceiptCount']        = $CNT_SUMType0;
                    $datas[$i]['M_ReceiptAmount']       = $SUM_Amount0;
                    $datas[$i]['M_RepayCount']          = $CNT_SUMType1;
                    $datas[$i]['M_RepayAmount']         = $SUM_Amount1;
                    $datas[$i]['M_OtherPaymentCount']   = $CNT_SUMType3;
                    $datas[$i]['M_OtherPaymentAmount']  = $SUM_Amount3;
                }
                else {
                    $datas[$i]['M_ReceiptCount']        = nvl($this->_ary02[12][$datas[$i]['OemId']]['CNT_SUMType0'], 0);
                    $datas[$i]['M_ReceiptAmount']       = nvl($this->_ary02[12][$datas[$i]['OemId']]['SUM_Amount0'] , 0);
                    $datas[$i]['M_RepayCount']          = nvl($this->_ary02[12][$datas[$i]['OemId']]['CNT_SUMType1'], 0);
                    $datas[$i]['M_RepayAmount']         = nvl($this->_ary02[12][$datas[$i]['OemId']]['SUM_Amount1'] , 0);
                    $datas[$i]['M_OtherPaymentCount']   = nvl($this->_ary02[12][$datas[$i]['OemId']]['CNT_SUMType3'], 0);
                    $datas[$i]['M_OtherPaymentAmount']  = nvl($this->_ary02[12][$datas[$i]['OemId']]['SUM_Amount3'] , 0);
                }
            }
        }

        $this->_ary02sub = $datas;

        // 調整(ここまで)
        //-----------------------------

        $fileName  = ('02.OEM日次統計表_' . $formatNowStr . '.pdf');
        $tmpFileName = $tmpFilePath . $fileName;

        $this->setTemplate('atoemnichijitokei');
        $this->view->assign('datas', $datas);
        $this->view->assign('DailyMonthlyFlg', $dailyMonthlyFlg);
        $this->view->assign('documentRoot', $_SERVER['DOCUMENT_ROOT']);
        $this->view->assign('title', $fileName);

        $fileName  = ('02.OEMNichijiTokei_' . $formatNowStr . '.pdf');

        // HTML作成
        $viewRender = $this->getServiceLocator()->get('ViewRenderer');
        $html = $viewRender->render($this->view);

        // 一時ファイルの保存先
        $mdlsp = new \models\Table\TableSystemProperty($this->app->dbAdapter);
        $tempDir = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'TempFileDir');
        $tempDir = realpath($tempDir);

        // 中間ファイル名
        $fname_html = ($tempDir . '/__tmp_' . $fileName . '__.html');
        $fname_pdf  = ($tempDir . '/__tmp_' . $fileName . '__.pdf');

        // HTML出力
        file_put_contents($fname_html, $html);

        // PDF変換(外部プログラム起動)
        $ename = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'wkhtmltopdf');
        $option = " --page-size A3 --orientation landscape --margin-left 0 --margin-right 0 --margin-top 0 --margin-bottom 0 ";
        exec($ename . $option . $fname_html . ' ' . $fname_pdf);

        // ファイルの読み込み
        $pdf = file_get_contents($fname_pdf);

        // ファイルに保存
        file_put_contents($tmpFileName, $pdf);

        unlink( $fname_html );
        unlink( $fname_pdf );

        return $tmpFileName;
    }

    //------------------------------------------------------------------------------
    // PDF群(monthly) ※Dailyに共通関数のないもの
    //------------------------------------------------------------------------------
    /**
     * PDF出力を行う[3. 直営未払金・売掛金・加盟店未収金統計表(PDF)]
     *
     * @param string $whereProcessingDate 抽出条件(指定日or月)
     * @param string $formatNowStr 書式化年月日時分秒
     * @param string $tmpFilePath TEMP領域
     * @param array $params パラメタ
     * @return ファイル名 ※出力が行われなかった場合は''を戻す
     */
    protected function PdfCBMibaraiTokei($whereProcessingDate, $formatNowStr, $tmpFilePath, $params)
    {
        $datas = array();
        $datas[0]['DailyMonthlyFlg']                    = '1';
        $datas[0]['AccountDate']                        = $params[':AccountDate'];

        // 前月末残取得＆設定(未払金／売掛金／未収金)
        $datas[0]['AP_AccountsPayableBalance']          = 0; // 初期化;
        $datas[0]['AR_AccountsReceivableBalance']       = 0; // 初期化;
        $datas[0]['AD_AccountsDueBalance']              = 0; // 初期化;

        $sql = <<<EOQ
SELECT IFNULL(SUM(AP_PreAccountsPayableBalance),0) AS sumPayableBalance
,      IFNULL(SUM(AR_PreAccountsReceivableBalance),0) AS sumReceivableBalance
,      IFNULL(SUM(AD_PerAccountsDueBalance),0) AS sumDueBalance
FROM   AT_Accounts_PayableStatisticsTable
WHERE  AccountDate =  DATE_FORMAT(:AccountDate - INTERVAL 1 MONTH, '%Y-%m-01')
AND    EnterpriseId <> 99999999
EOQ;
        $row = $this->app->dbAdapter->query($sql)->execute($params)->current();
        if ($row) {
            $datas[0]['AP_AccountsPayableBalance']      = $row['sumPayableBalance'];
            $datas[0]['AR_AccountsReceivableBalance']   = $row['sumReceivableBalance'];
            $datas[0]['AD_AccountsDueBalance']          = $row['sumDueBalance'];
        }

        // (無条件のゼロ化)
        $datas[0]['AP_BadDebtAmount']                   = 0;
        $datas[0]['AR_AccountsPayableTransfer']         = 0;
        $datas[0]['AR_AccountsMonthPayment']            = 0;
        $datas[0]['AR_BadDebtAmount']                   = 0;

        // (1. 直営日次統計表(PDF)データより)
        $datas[0]['AP_ChargeCount']                     = $this->_ary01sub[0]['M_ChargeCount'];
        $datas[0]['AP_ChargeAmount']                    = $this->_ary01sub[0]['M_ChargeAmount'];
        $datas[0]['AP_CancelCount']                     = $this->_ary01sub[0]['M_CancelCount'];
        $datas[0]['AP_CalcelAmount']                    = $this->_ary01sub[0]['M_CancelAmount'];
        $datas[0]['AP_SettlementBackAmount']            = $this->_ary01sub[0]['M_SettlementBackAmount'];
        $datas[0]['AR_SettlementFeeAmount']             = $this->_ary01sub[0]['M_SettlementFeeTotal'];
        $datas[0]['AR_ClaimFeeAmount']                  = $this->_ary01sub[0]['M_ClaimFeeTotal']
                                                        + $this->_ary01sub[0]['M_ClaimFeeTaxTotal'];
        $datas[0]['AR_MonthlyFee']                      = $this->_ary01sub[0]['M_MonthlyFeeTotal']
                                                        + $this->_ary01sub[0]['M_MonthlyFeeTaxTotal'];
        $datas[0]['AR_IncludeMonthlyFee']               = $this->_ary01sub[0]['M_IncludeMonthlyFeeTotal']
                                                        + $this->_ary01sub[0]['M_IncludeMonthlyFeeTaxTotal'];
        $datas[0]['AR_ApiMonthlyFee']                   = $this->_ary01sub[0]['M_ApiMonthlyFeeTotal']
                                                        + $this->_ary01sub[0]['M_ApiMonthlyFeeTaxTotal'];
        $datas[0]['AR_CreditNoticeMonthlyFee']          = $this->_ary01sub[0]['M_CreditNoticeMonthlyFeeTotal']
                                                        + $this->_ary01sub[0]['M_CreditNoticeMonthlyFeeTaxTotal'];
        $datas[0]['AR_NCreditNoticeMonthlyFee']         = $this->_ary01sub[0]['M_NCreditNoticeMonthlyFeeTotal']
                                                        + $this->_ary01sub[0]['M_NCreditNoticeMonthlyFeeTaxTotal'];
        $datas[0]['AR_AccountsReceivableIncrease']      = $datas[0]['AR_SettlementFeeAmount']
                                                        + $datas[0]['AR_ClaimFeeAmount']
                                                        + $datas[0]['AR_MonthlyFee']
                                                        + $datas[0]['AR_IncludeMonthlyFee']
                                                        + $datas[0]['AR_ApiMonthlyFee']
                                                        + $datas[0]['AR_CreditNoticeMonthlyFee']
                                                        + $datas[0]['AR_NCreditNoticeMonthlyFee'];

        // (精算日計より)
        $sql = <<<EOQ
SELECT SUM(t.AccountsReceivableTotal) AS sumAccountsReceivableTotal
,      SUM(CASE WHEN t.AccountsReceivableTotal <> 0 THEN 1 ELSE 0 END) AS cntAccountsReceivableTotal
,      SUM(t.AdvancesAmount) AS sumAdvancesAmount
,      SUM(CASE WHEN t.AdvancesAmount <> 0 THEN 1 ELSE 0 END) AS cntAdvancesAmount
,      SUM(t.StampFee) AS sumStampFee
,      SUM(CASE WHEN t.StampFee <> 0 THEN 1 ELSE 0 END) AS cntStampFee
,      SUM(t.TransferCommission) AS sumTransferCommission
,      SUM(CASE WHEN t.TransferCommission <> 0 THEN 1 ELSE 0 END) AS cntTransferCommission
,      SUM(t.AdjustmentAmount) AS sumAdjustmentAmount
,      SUM(CASE WHEN t.AdjustmentAmount <> 0 THEN 1 ELSE 0 END) AS cntAdjustmentAmount
,      SUM(t.EnterpriseRefund) AS sumEnterpriseRefund
,      SUM(CASE WHEN t.EnterpriseRefund <> 0 THEN 1 ELSE 0 END) AS cntEnterpriseRefund
,      SUM(t.EnterpriseAccountsDue) AS sumEnterpriseAccountsDue
,      SUM(CASE WHEN t.EnterpriseAccountsDue <> 0 THEN 1 ELSE 0 END) AS cntEnterpriseAccountsDue
,      SUM(t.AccountsDueOffsetAmount) AS sumAccountsDueOffsetAmount
,      SUM(CASE WHEN t.AccountsDueOffsetAmount <> 0 THEN 1 ELSE 0 END) AS cntAccountsDueOffsetAmount
FROM   AT_PayOff_DailyAccount t
       INNER JOIN M_Code cod ON (cod.KeyCode = t.OemId)
WHERE  cod.CodeId = 160
AND    cod.Class1 = 0
AND    t.ProcessingDate >= DATE_FORMAT(:AccountDate, '%Y-%m-%d') AND t.ProcessingDate < DATE_FORMAT(:AccountDate + INTERVAL 1 MONTH, '%Y-%m-%d')
EOQ;
        $row = $this->app->dbAdapter->query($sql)->execute($params)->current();
        $datas[0]['AP_AccountsReceivableOffset']        = $row['sumAccountsReceivableTotal'];
        $datas[0]['AP_AccountsPayableOffset']           = $row['sumAccountsDueOffsetAmount'];
        $datas[0]['AP_AccountsPayableTransfer']         = $row['sumEnterpriseAccountsDue'];
        $datas[0]['AP_AmountPaymentCount']              = $row['cntAdvancesAmount'];
        $datas[0]['AP_AmountPayment']                   = (-1) * $row['sumAdvancesAmount'];
        $datas[0]['Other_StampFeeCount']                = $row['cntStampFee'];
        $datas[0]['Other_StampFee']                     = (-1) * $row['sumStampFee'];
        $datas[0]['Other_TransferCommissionCount']      = $row['cntTransferCommission'];
        $datas[0]['Other_TransferCommission']           = (-1) * $row['sumTransferCommission'];
        $datas[0]['Other_AdjustmentAmountCount']        = $row['cntAdjustmentAmount'];
        $datas[0]['Other_AdjustmentAmount']             = (-1) * $row['sumAdjustmentAmount'];
        $datas[0]['Other_RefundCount']                  = $row['cntEnterpriseRefund'];
        $datas[0]['Other_Refund']                       = (-1) * $row['sumEnterpriseRefund'];
        $datas[0]['Other_AccountsPayableOffset']        = $datas[0]['Other_StampFee']
                                                        + $datas[0]['Other_TransferCommission']
                                                        + $datas[0]['Other_AdjustmentAmount']
                                                        + $datas[0]['Other_Refund'];
        $datas[0]['AP_OtherAccountsPayableOffset']      = (-1) * $datas[0]['Other_AccountsPayableOffset'];
        $datas[0]['AR_AccountsPayableOffsetCount']      = $row['cntAccountsReceivableTotal'];
        $datas[0]['AR_AccountsPayableOffset']           = $row['sumAccountsReceivableTotal'];
        $datas[0]['AD_TransferAmountCount']             = $row['cntEnterpriseAccountsDue'];
        $datas[0]['AD_TransferAmount']                  = $row['sumEnterpriseAccountsDue'];
        $datas[0]['AD_AccountsPayableOffsetCount']      = $row['cntAccountsDueOffsetAmount'];
        $datas[0]['AD_AccountsPayableOffset']           = $row['sumAccountsDueOffsetAmount'];

        // (直営未払金兼売掛金明細より)
        $sql = <<<EOQ
SELECT SUM(UseAmount) AS sumUseAmount
,      SUM(AccountsReceivableTotal) AS sumAccountsReceivableTotal
,      SUM(AccountsDue) AS sumAccountsDue
FROM   AT_Cb_Accounts_PayableReceivable
WHERE  DailyMonthlyFlg = 1
AND    AccountDate = :AccountDate
EOQ;
        $row = $this->app->dbAdapter->query($sql)->execute($params)->current();
        $datas[0]['AP_UseAmountTotal']                  = $row['sumUseAmount'];
        $datas[0]['AR_AccountsReceivableToal']          = $row['sumAccountsReceivableTotal'];
        $datas[0]['AD_CurrentAccountsDue']              = (-1) * $row['sumAccountsDue'];

        // (貸倒債権一覧より)
        $sql = <<<EOQ
SELECT IFNULL(SUM(CrediAmount),0) AS sumCrediAmount
FROM   AT_BadDebt_List
WHERE  CrediTarget = '加盟店'
AND    AccountDate = :AccountDate
EOQ;
        $row = $this->app->dbAdapter->query($sql)->execute($params)->current();
        $datas[0]['AD_BadDebtAmount']                   = $row['sumCrediAmount'];

        // (未収金日計より)
        $sql = <<<EOQ
SELECT COUNT(1) AS cntAmount
,      IFNULL(SUM(Amount),0) AS sumAmount
FROM   AW_MishukinHikeiMonth
WHERE  PaymentTargetAccountTitle = '加盟店未収金'
EOQ;
        $row = $this->app->dbAdapter->query($sql)->execute(null)->current();
        $datas[0]['AD_AccountsMonthCount']              = $row['cntAmount'];
        $datas[0]['AD_AccountsMonthPayment']            = $row['sumAmount'];

        // (集計フィールド)
        $datas[0]['AP_PreAccountsPayableBalance']       = $datas[0]['AP_AccountsPayableBalance']
                                                        + $datas[0]['AP_ChargeAmount']
                                                        + $datas[0]['AP_CalcelAmount']
                                                        + $datas[0]['AP_SettlementBackAmount']
                                                        + $datas[0]['AP_AccountsReceivableOffset']
                                                        + $datas[0]['AP_AccountsPayableOffset']
                                                        + $datas[0]['AP_OtherAccountsPayableOffset']
                                                        + $datas[0]['AP_AccountsPayableTransfer']
                                                        + $datas[0]['AP_AmountPayment']
                                                        + $datas[0]['AP_BadDebtAmount'];
        $datas[0]['AP_Difference']                      = $datas[0]['AP_PreAccountsPayableBalance']
                                                        - $datas[0]['AP_UseAmountTotal'];
        $datas[0]['AR_PreAccountsReceivableBalance']    = $datas[0]['AR_AccountsReceivableBalance']
                                                        + $datas[0]['AR_AccountsReceivableIncrease']
                                                        + $datas[0]['AR_AccountsPayableOffset']
                                                        + $datas[0]['AR_AccountsPayableTransfer']
                                                        + $datas[0]['AR_AccountsMonthPayment']
                                                        + $datas[0]['AR_BadDebtAmount'];
        $datas[0]['AR_Difference']                      = $datas[0]['AR_PreAccountsReceivableBalance']
                                                        - $datas[0]['AR_AccountsReceivableToal'];
        $datas[0]['AD_PerAccountsDueBalance']           = $datas[0]['AD_AccountsDueBalance']
                                                        + $datas[0]['AD_TransferAmount']
                                                        + $datas[0]['AD_AccountsPayableOffset']
                                                        + $datas[0]['AD_AccountsMonthPayment']
                                                        + $datas[0]['AD_BadDebtAmount'];
        $datas[0]['AD_Difference']                      = $datas[0]['AD_PerAccountsDueBalance']
                                                        - $datas[0]['AD_CurrentAccountsDue'];

        $fileName  = ('03.直営未払金・売掛金・加盟店未収金統計表_' . $formatNowStr . '.pdf');
        $tmpFileName = $tmpFilePath . $fileName;

        $this->setTemplate('atchokueimibaraitokei');
        $this->view->assign('datas', $datas);
        $this->view->assign('documentRoot', $_SERVER['DOCUMENT_ROOT']);
        $this->view->assign('title', $fileName);

        $fileName  = ('03.CBMibaraiTokei_' . $formatNowStr . '.pdf');

        // HTML作成
        $viewRender = $this->getServiceLocator()->get('ViewRenderer');
        $html = $viewRender->render($this->view);

        // 一時ファイルの保存先
        $mdlsp = new \models\Table\TableSystemProperty($this->app->dbAdapter);
        $tempDir = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'TempFileDir');
        $tempDir = realpath($tempDir);

        // 中間ファイル名
        $fname_html = ($tempDir . '/__tmp_' . $fileName . '__.html');
        $fname_pdf  = ($tempDir . '/__tmp_' . $fileName . '__.pdf');

        // HTML出力
        file_put_contents($fname_html, $html);

        // PDF変換(外部プログラム起動)
        $ename = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'wkhtmltopdf');
        $option = " --page-size A4 --orientation landscape --margin-left 0 --margin-right 0 --margin-top 0 --margin-bottom 0 ";
        exec($ename . $option . $fname_html . ' ' . $fname_pdf);

        // ファイルの読み込み
        $pdf = file_get_contents($fname_pdf);

        // ファイルに保存
        file_put_contents($tmpFileName, $pdf);

        unlink( $fname_html );
        unlink( $fname_pdf );

        return $tmpFileName;
    }

    /**
     * PDF出力を行う[4. OEM未払金・売掛金・OEM未収金統計表(PDF)]
     *
     * @param string $whereProcessingDate 抽出条件(指定日or月)
     * @param string $formatNowStr 書式化年月日時分秒
     * @param string $tmpFilePath TEMP領域
     * @param array $params パラメタ
     * @param array &$datas 帳票作成時に生成されたデータ
     * @return ファイル名 ※出力が行われなかった場合は''を戻す
     */
    protected function PdfOEMMibaraiTokei($whereProcessingDate, $formatNowStr, $tmpFilePath, $params, &$datas)
    {
        // OEM単位取得
        $sql = <<<EOQ
SELECT t.OemId, t.AccessId, t.OemNameKj
FROM   T_Oem t
       INNER JOIN M_Code cod ON (cod.KeyCode = t.OemId)
WHERE  cod.CodeId = 160
AND    cod.Class1 = 1
ORDER BY OemId
EOQ;
        $ri_oem = $this->app->dbAdapter->query($sql)->execute(null);
        foreach ($ri_oem as $row_oem) {
            $data = array();
            $data['DailyMonthlyFlg']                    = '1';
            $data['AccountDate']                        = $params[':AccountDate'];
            $data['OemId']                              = $row_oem['OemId'];
            $data['OemNameKj']                          = $row_oem['OemNameKj'];
            $data['EnterpriseId']                       = NULL;
            $data['EnterpriseNameKj']                   = 'OEM合計';
            $data['AccessId']                           = $row_oem['AccessId'];

            // 前月末残取得＆設定(未払金／売掛金／未収金) ※Oem単位
            $data['AP_AccountsPayableBalance']          = 0; // 初期化
            $data['AR_AccountsReceivableBalance']       = 0; // 初期化
            $data['AD_AccountsDueBalance']              = 0; // 初期化

            $sql = <<<EOQ
SELECT IFNULL(AP_PreAccountsPayableBalance,0) AS sumPayableBalance
,      IFNULL(AR_PreAccountsReceivableBalance,0) AS sumReceivableBalance
,      IFNULL(AD_PerAccountsDueBalance,0) AS sumDueBalance
FROM   AT_OemAccounts_PayableStatisticsTable
WHERE  AccountDate =  DATE_FORMAT(:AccountDate - INTERVAL 1 MONTH, '%Y-%m-01')
AND    EnterpriseId = :EnterpriseId
EOQ;
            $row = $this->app->dbAdapter->query($sql)->execute(array_merge($params, array(':EnterpriseId' => (99999900 + $row_oem['OemId']))))->current();
            if ($row) {
                $data['AP_AccountsPayableBalance']      = $row['sumPayableBalance'];
                $data['AR_AccountsReceivableBalance']   = $row['sumReceivableBalance'];
                $data['AD_AccountsDueBalance']          = $row['sumDueBalance'];
            }

            // (無条件のゼロ化)
            $data['AP_BadDebtAmount']                   = 0;
            $data['AR_AccountsPayableTransfer']         = 0;
            $data['AR_AccountsMonthPayment']            = 0;
            $data['AR_BadDebtAmount']                   = 0;
            $data['AD_BadDebtAmount']                   = 0;    // 貸倒金額(OEM未収金)

            // (対象OEMのインデックス取得)
            $idx = 0;

            // count関数対策
            $_ary02sub_count = 0;
            if (!empty($this->_ary02sub)) {
                $_ary02sub_count = count($this->_ary02sub);
            }

            for ($i=0; $i<$_ary02sub_count; $i++ ) {
                if ($this->_ary02sub[$i]['OemId'] == $row_oem['OemId']) {
                    $idx = $i;
                    break;
                }
            }

            // (2. OEM日次統計表(PDF)データより)
            $data['AP_ChargeCount']                     = $this->_ary02sub[$idx]['M_ChargeCount'];
            $data['AP_ChargeAmount']                    = $this->_ary02sub[$idx]['M_ChargeAmount'];
            $data['AP_CancelCount']                     = $this->_ary02sub[$idx]['M_CancelCount'];
            $data['AP_CalcelAmount']                    = $this->_ary02sub[$idx]['M_CancelAmount'];
            $data['AP_SettlementBackAmount']            = $this->_ary02sub[$idx]['M_SettlementBackAmount'];
            $data['AR_OemSettlementFeeAmount']          = $this->_ary02sub[$idx]['M_SettlementFeeTotal']
                                                        + $this->_ary02sub[$idx]['M_SettlementFeeTaxTotal'];
            $data['AR_OemClaimFeeAmount']               = $this->_ary02sub[$idx]['M_ClaimFeeTotal']
                                                        + $this->_ary02sub[$idx]['M_ClaimFeeTaxTotal'];
            $data['AR_OemMonthlyFee']                   = $this->_ary02sub[$idx]['M_MonthlyFeeTotal']
                                                        + $this->_ary02sub[$idx]['M_MonthlyFeeTaxTotal'];
            $data['AR_OemIncludeMonthlyFee']            = $this->_ary02sub[$idx]['M_OemIncludeMonthlyFeeTotal']
                                                        + $this->_ary02sub[$idx]['M_OemIncludeMonthlyFeeTaxTotal'];
            $data['AR_OemApiMonthlyFee']                = $this->_ary02sub[$idx]['M_OemApiMonthlyFeeTotal']
                                                        + $this->_ary02sub[$idx]['M_OemApiMonthlyFeeTaxTotal'];
            $data['AR_OemCreditNoticeMonthlyFee']       = $this->_ary02sub[$idx]['M_OemCreditNoticeMonthlyFeeTotal']
                                                        + $this->_ary02sub[$idx]['M_OemCreditNoticeMonthlyFeeTaxTotal'];
            $data['AR_OemNCreditNoticeMonthlyFee']      = $this->_ary02sub[$idx]['M_OemNCreditNoticeMonthlyFeeTotal']
                                                        + $this->_ary02sub[$idx]['M_OemNCreditNoticeMonthlyFeeTaxTotal'];
            $data['AR_AccountsReceivableIncrease']      = $data['AR_OemSettlementFeeAmount']
                                                        + $data['AR_OemClaimFeeAmount']
                                                        + $data['AR_OemMonthlyFee']
                                                        + $data['AR_OemIncludeMonthlyFee']
                                                        + $data['AR_OemApiMonthlyFee']
                                                        + $data['AR_OemCreditNoticeMonthlyFee']
                                                        + $data['AR_OemNCreditNoticeMonthlyFee'];

            // (精算日計より)
            $sql = <<<EOQ
SELECT SUM(t.AccountsReceivableTotal) AS sumAccountsReceivableTotal
,      SUM(CASE WHEN t.AccountsReceivableTotal <> 0 THEN 1 ELSE 0 END) AS cntAccountsReceivableTotal
,      SUM(t.AdvancesAmount) AS sumAdvancesAmount
,      SUM(CASE WHEN t.AdvancesAmount <> 0 THEN 1 ELSE 0 END) AS cntAdvancesAmount
,      SUM(t.StampFee) AS sumStampFee
,      SUM(CASE WHEN t.StampFee <> 0 THEN 1 ELSE 0 END) AS cntStampFee
,      SUM(t.TransferCommission) AS sumTransferCommission
,      SUM(CASE WHEN t.TransferCommission <> 0 THEN 1 ELSE 0 END) AS cntTransferCommission
,      SUM(t.AdjustmentAmount) AS sumAdjustmentAmount
,      SUM(CASE WHEN t.AdjustmentAmount <> 0 THEN 1 ELSE 0 END) AS cntAdjustmentAmount
,      SUM(t.EnterpriseRefund) AS sumEnterpriseRefund
,      SUM(CASE WHEN t.EnterpriseRefund <> 0 THEN 1 ELSE 0 END) AS cntEnterpriseRefund
,      SUM(t.EnterpriseAccountsDue) AS sumEnterpriseAccountsDue
,      SUM(CASE WHEN t.EnterpriseAccountsDue <> 0 THEN 1 ELSE 0 END) AS cntEnterpriseAccountsDue
,      SUM(t.AccountsDueOffsetAmount) AS sumAccountsDueOffsetAmount
,      SUM(CASE WHEN t.AccountsDueOffsetAmount <> 0 THEN 1 ELSE 0 END) AS cntAccountsDueOffsetAmount
FROM   AT_PayOff_DailyAccount t
       INNER JOIN M_Code cod ON (cod.KeyCode = t.OemId)
WHERE  cod.CodeId = 160
AND    cod.Class1 = 1
AND    t.OemId = :OemId
AND    t.ProcessingDate >= DATE_FORMAT(:AccountDate, '%Y-%m-%d') AND t.ProcessingDate < DATE_FORMAT(:AccountDate + INTERVAL 1 MONTH, '%Y-%m-%d')
EOQ;
            $row = $this->app->dbAdapter->query($sql)->execute(array_merge($params, array(':OemId' => $row_oem['OemId'])))->current();

            // (精算日計２[集計専用テーブル]より)
            // NOTE.件数はいずれも0非表示対応化する為「上書き」は行わない
            $sql = <<<EOQ
SELECT SUM(AccountsDueOffsetAmount) AS sumAccountsDueOffsetAmount
,      SUM(EnterpriseAccountsDue) AS sumEnterpriseAccountsDue
,      SUM(AdvancesAmount) AS sumAdvancesAmount
FROM   AT_PayOff_DailyAccount2
WHERE  OemId = :OemId
AND    ProcessingDate >= DATE_FORMAT(:AccountDate, '%Y-%m-%d') AND ProcessingDate < DATE_FORMAT(:AccountDate + INTERVAL 1 MONTH, '%Y-%m-%d')
EOQ;
            $row2= $this->app->dbAdapter->query($sql)->execute(array_merge($params, array(':OemId' => $row_oem['OemId'])))->current();

            $data['AP_AccountsReceivableOffset']        = $row['sumAccountsReceivableTotal'];
            $data['AP_AccountsPayableOffset']           = $row['sumAccountsDueOffsetAmount'];
            $data['AP_AccountsPayableOffset']           = $row2['sumAccountsDueOffsetAmount'];      // 集計専用テーブル値で上書き
            $data['AP_AccountsPayableTransfer']         = $row['sumEnterpriseAccountsDue'];
            $data['AP_AccountsPayableTransfer']         = $row2['sumEnterpriseAccountsDue'];        // 集計専用テーブル値で上書き
            $data['AP_AmountPaymentCount']              = $row['cntAdvancesAmount'];
            $data['AP_AmountPayment']                   = (-1) * $row['sumAdvancesAmount'];
            $data['AP_AmountPayment']                   = (-1) * $row2['sumAdvancesAmount'];        // 集計専用テーブル値で上書き
            $data['Other_StampFeeCount']                = $row['cntStampFee'];
            $data['Other_StampFee']                     = (-1) * $row['sumStampFee'];
            $data['Other_TransferCommissionCount']      = $row['cntTransferCommission'];
            $data['Other_TransferCommission']           = (-1) * $row['sumTransferCommission'];
            $data['Other_AdjustmentAmountCount']        = $row['cntAdjustmentAmount'];
            $data['Other_AdjustmentAmount']             = (-1) * $row['sumAdjustmentAmount'];
            $data['Other_RefundCount']                  = $row['cntEnterpriseRefund'];
            $data['Other_Refund']                       = (-1) * $row['sumEnterpriseRefund'];
            $data['Other_AccountsPayableOffset']        = $data['Other_StampFee']
                                                        + $data['Other_TransferCommission']
                                                        + $data['Other_AdjustmentAmount']
                                                        + $data['Other_Refund'];
            $data['AP_OtherAccountsPayableOffset']      = (-1) * $data['Other_AccountsPayableOffset'];
            $data['AR_AccountsPayableOffsetCount']      = $row['cntAccountsReceivableTotal'];
            $data['AR_AccountsPayableOffset']           = $row['sumAccountsReceivableTotal'];
            $data['AD_TransferAmountCount']             = $row['cntEnterpriseAccountsDue'];
            $data['AD_TransferAmount']                  = $row['sumEnterpriseAccountsDue'];
            $data['AD_TransferAmount']                  = $row2['sumEnterpriseAccountsDue'];        // 集計専用テーブル値で上書き
            $data['AD_AccountsPayableOffsetCount']      = $row['cntAccountsDueOffsetAmount'];
            $data['AD_AccountsPayableOffset']           = $row['sumAccountsDueOffsetAmount'];
            $data['AD_AccountsPayableOffset']           = $row2['sumAccountsDueOffsetAmount'];      // 集計専用テーブル値で上書き

            // (OEM未払金兼売掛金明細より)
            $sql = <<<EOQ
SELECT SUM(UseAmount) AS sumUseAmount
,      SUM(AccountsReceivableTotal) AS sumAccountsReceivableTotal
,      SUM(AccountsDue) AS sumAccountsDue
FROM   AT_Oem_Accounts_PayableReceivable
WHERE  DailyMonthlyFlg = 1
AND    AccountDate = :AccountDate
AND    OemId = :OemId
EOQ;
            $row = $this->app->dbAdapter->query($sql)->execute(array_merge($params, array(':OemId' => $row_oem['OemId'])))->current();
            $data['AP_UseAmountTotal']                  = $row['sumUseAmount'];
            $data['AR_AccountsReceivableToal']          = $row['sumAccountsReceivableTotal'];
            $data['AD_CurrentAccountsDue']              = (-1) * $row['sumAccountsDue'];

            // (仮払金日計明細より)
            $sql = <<<EOQ
SELECT COUNT(1) AS cntAmount
,      IFNULL(SUM(Amount),0) AS sumAmount
FROM   AT_SuspensePayments_DailyAccountDetails
WHERE  PaymentTargetAccountTitle = 'OEM未収金'
AND    AccountDate = :AccountDate
AND    OemId = :OemId
EOQ;
            $row = $this->app->dbAdapter->query($sql)->execute(array_merge($params, array(':OemId' => $row_oem['OemId'])))->current();
            $data['AD_AccountsMonthCount']              = $row['cntAmount'];
            $data['AD_AccountsMonthPayment']            = $row['sumAmount'];

            // (集計フィールド)
            $data['AP_PreAccountsPayableBalance']       = $data['AP_AccountsPayableBalance']
                                                        + $data['AP_ChargeAmount']
                                                        + $data['AP_CalcelAmount']
                                                        + $data['AP_SettlementBackAmount']
                                                        + $data['AP_AccountsReceivableOffset']
                                                        + $data['AP_AccountsPayableOffset']
                                                        + $data['AP_OtherAccountsPayableOffset']
                                                        + $data['AP_AccountsPayableTransfer']
                                                        + $data['AP_AmountPayment']
                                                        + $data['AP_BadDebtAmount'];
            $data['AP_Difference']                      = $data['AP_PreAccountsPayableBalance']
                                                        - $data['AP_UseAmountTotal'];
            $data['AR_PreAccountsReceivableBalance']    = $data['AR_AccountsReceivableBalance']
                                                        + $data['AR_AccountsReceivableIncrease']
                                                        + $data['AR_AccountsPayableOffset']
                                                        + $data['AR_AccountsPayableTransfer']
                                                        + $data['AR_AccountsMonthPayment']
                                                        + $data['AR_BadDebtAmount'];
            $data['AR_Difference']                      = $data['AR_PreAccountsReceivableBalance']
                                                        - $data['AR_AccountsReceivableToal'];
            $data['AD_PerAccountsDueBalance']           = $data['AD_AccountsDueBalance']
                                                        + $data['AD_TransferAmount']
                                                        + $data['AD_AccountsPayableOffset']
                                                        + $data['AD_AccountsMonthPayment']
                                                        + $data['AD_BadDebtAmount'];
            $data['AD_Difference']                      = $data['AD_PerAccountsDueBalance']
                                                        - $data['AD_CurrentAccountsDue'];

            $datas[] = $data;
        }

        // サマリー(初期化＆合算) NOTE.SQL発行なし
        // (初期化)
        $row = array(
            'Seq' => '',
            'DailyMonthlyFlg' => '1',
            'ProcessingDate' => $params[':AccountDate'],    // NOTE.従来はMIN(ProcessingDate)だが、未使用故AccountDateをそのままアサイン
            'AccountDate' => $params[':AccountDate'],
            'OemId' => NULL,
            'OemNameKj' => NULL,
            'EnterpriseId' => NULL,
            'EnterpriseNameKj' => '総合計',
            'AP_AccountsPayableBalance' => 0,
            'AP_ChargeCount' => 0,
            'AP_ChargeAmount' => 0,
            'AP_CancelCount' => 0,
            'AP_CalcelAmount' => 0,
            'AP_SettlementBackCount' => 0,
            'AP_SettlementBackAmount' => 0,
            'AP_AccountsReceivableOffset' => 0,
            'AP_AccountsPayableOffset' => 0,
            'AP_OtherAccountsPayableOffset' => 0,
            'AP_AccountsPayableTransfer' => 0,
            'AP_AmountPaymentCount' => 0,
            'AP_AmountPayment' => 0,
            'AP_BadDebtAmountCount' => 0,
            'AP_BadDebtAmount' => 0,
            'AP_PreAccountsPayableBalance' => 0,
            'AP_UseAmountTotal' => 0,
            'AP_Difference' => 0,
            'Other_AccountsPayableOffset' => 0,
            'Other_StampFeeCount' => 0,
            'Other_StampFee' => 0,
            'Other_TransferCommissionCount' => 0,
            'Other_TransferCommission' => 0,
            'Other_AdjustmentAmountCount' => 0,
            'Other_AdjustmentAmount' => 0,
            'Other_RefundCount' => 0,
            'Other_Refund' => 0,
            'AR_AccountsReceivableBalance' => 0,
            'AR_AccountsReceivableIncrease' => 0,
            'AR_OemSettlementFeeCount' => 0,
            'AR_OemSettlementFeeAmount' => 0,
            'AR_OemClaimFeeCount' => 0,
            'AR_OemClaimFeeAmount' => 0,
            'AR_OemMonthlyFee' => 0,
            'AR_OemIncludeMonthlyFee' => 0,
            'AR_OemApiMonthlyFee' => 0,
            'AR_OemCreditNoticeMonthlyFee' => 0,
            'AR_OemNCreditNoticeMonthlyFee' => 0,
            'AR_AccountsPayableOffsetCount' => 0,
            'AR_AccountsPayableOffset' => 0,
            'AR_AccountsPayableTransfer' => 0,
            'AR_AccountsMonthPaymentCount' => 0,
            'AR_AccountsMonthPayment' => 0,
            'AR_BadDebtAmountCount' => 0,
            'AR_BadDebtAmount' => 0,
            'AR_PreAccountsReceivableBalance' => 0,
            'AR_AccountsReceivableToal' => 0,
            'AR_Difference' => 0,
            'AD_AccountsDueBalance' => 0,
            'AD_TransferAmountCount' => 0,
            'AD_TransferAmount' => 0,
            'AD_AccountsPayableOffsetCount' => 0,
            'AD_AccountsPayableOffset' => 0,
            'AD_AccountsMonthCount' => 0,
            'AD_AccountsMonthPayment' => 0,
            'AD_BadDebtCount' => 0,
            'AD_BadDebtAmount' => 0,
            'AD_PerAccountsDueBalance' => 0,
            'AD_CurrentAccountsDue' => 0,
            'AD_Difference' => 0,
        );

        // count関数対策
        $datas_count = 0;
        if (!empty($datas)) {
            $datas_count = count($datas);
        }

        // (合算)
        for ($i=0; $i<$datas_count; $i++) {
            $row['AP_AccountsPayableBalance'] += $datas[$i]['AP_AccountsPayableBalance'];
            $row['AP_ChargeCount'] += $datas[$i]['AP_ChargeCount'];
            $row['AP_ChargeAmount'] += $datas[$i]['AP_ChargeAmount'];
            $row['AP_CancelCount'] += $datas[$i]['AP_CancelCount'];
            $row['AP_CalcelAmount'] += $datas[$i]['AP_CalcelAmount'];
            $row['AP_SettlementBackCount'] += $datas[$i]['AP_SettlementBackCount'];
            $row['AP_SettlementBackAmount'] += $datas[$i]['AP_SettlementBackAmount'];
            $row['AP_AccountsReceivableOffset'] += $datas[$i]['AP_AccountsReceivableOffset'];
            $row['AP_AccountsPayableOffset'] += $datas[$i]['AP_AccountsPayableOffset'];
            $row['AP_OtherAccountsPayableOffset'] += $datas[$i]['AP_OtherAccountsPayableOffset'];
            $row['AP_AccountsPayableTransfer'] += $datas[$i]['AP_AccountsPayableTransfer'];
            $row['AP_AmountPaymentCount'] += $datas[$i]['AP_AmountPaymentCount'];
            $row['AP_AmountPayment'] += $datas[$i]['AP_AmountPayment'];
            $row['AP_BadDebtAmountCount'] += $datas[$i]['AP_BadDebtAmountCount'];
            $row['AP_BadDebtAmount'] += $datas[$i]['AP_BadDebtAmount'];
            $row['AP_PreAccountsPayableBalance'] += $datas[$i]['AP_PreAccountsPayableBalance'];
            $row['AP_UseAmountTotal'] += $datas[$i]['AP_UseAmountTotal'];
            $row['AP_Difference'] += $datas[$i]['AP_Difference'];
            $row['Other_AccountsPayableOffset'] += $datas[$i]['Other_AccountsPayableOffset'];
            $row['Other_StampFeeCount'] += $datas[$i]['Other_StampFeeCount'];
            $row['Other_StampFee'] += $datas[$i]['Other_StampFee'];
            $row['Other_TransferCommissionCount'] += $datas[$i]['Other_TransferCommissionCount'];
            $row['Other_TransferCommission'] += $datas[$i]['Other_TransferCommission'];
            $row['Other_AdjustmentAmountCount'] += $datas[$i]['Other_AdjustmentAmountCount'];
            $row['Other_AdjustmentAmount'] += $datas[$i]['Other_AdjustmentAmount'];
            $row['Other_RefundCount'] += $datas[$i]['Other_RefundCount'];
            $row['Other_Refund'] += $datas[$i]['Other_Refund'];
            $row['AR_AccountsReceivableBalance'] += $datas[$i]['AR_AccountsReceivableBalance'];
            $row['AR_AccountsReceivableIncrease'] += $datas[$i]['AR_AccountsReceivableIncrease'];
            $row['AR_OemSettlementFeeCount'] += $datas[$i]['AR_OemSettlementFeeCount'];
            $row['AR_OemSettlementFeeAmount'] += $datas[$i]['AR_OemSettlementFeeAmount'];
            $row['AR_OemClaimFeeCount'] += $datas[$i]['AR_OemClaimFeeCount'];
            $row['AR_OemClaimFeeAmount'] += $datas[$i]['AR_OemClaimFeeAmount'];
            $row['AR_OemMonthlyFee'] += $datas[$i]['AR_OemMonthlyFee'];
            $row['AR_OemIncludeMonthlyFee'] += $datas[$i]['AR_OemIncludeMonthlyFee'];
            $row['AR_OemApiMonthlyFee'] += $datas[$i]['AR_OemApiMonthlyFee'];
            $row['AR_OemCreditNoticeMonthlyFee'] += $datas[$i]['AR_OemCreditNoticeMonthlyFee'];
            $row['AR_OemNCreditNoticeMonthlyFee'] += $datas[$i]['AR_OemNCreditNoticeMonthlyFee'];
            $row['AR_AccountsPayableOffsetCount'] += $datas[$i]['AR_AccountsPayableOffsetCount'];
            $row['AR_AccountsPayableOffset'] += $datas[$i]['AR_AccountsPayableOffset'];
            $row['AR_AccountsPayableTransfer'] += $datas[$i]['AR_AccountsPayableTransfer'];
            $row['AR_AccountsMonthPaymentCount'] += $datas[$i]['AR_AccountsMonthPaymentCount'];
            $row['AR_AccountsMonthPayment'] += $datas[$i]['AR_AccountsMonthPayment'];
            $row['AR_BadDebtAmountCount'] += $datas[$i]['AR_BadDebtAmountCount'];
            $row['AR_BadDebtAmount'] += $datas[$i]['AR_BadDebtAmount'];
            $row['AR_PreAccountsReceivableBalance'] += $datas[$i]['AR_PreAccountsReceivableBalance'];
            $row['AR_AccountsReceivableToal'] += $datas[$i]['AR_AccountsReceivableToal'];
            $row['AR_Difference'] += $datas[$i]['AR_Difference'];
            $row['AD_AccountsDueBalance'] += $datas[$i]['AD_AccountsDueBalance'];
            $row['AD_TransferAmountCount'] += $datas[$i]['AD_TransferAmountCount'];
            $row['AD_TransferAmount'] += $datas[$i]['AD_TransferAmount'];
            $row['AD_AccountsPayableOffsetCount'] += $datas[$i]['AD_AccountsPayableOffsetCount'];
            $row['AD_AccountsPayableOffset'] += $datas[$i]['AD_AccountsPayableOffset'];
            $row['AD_AccountsMonthCount'] += $datas[$i]['AD_AccountsMonthCount'];
            $row['AD_AccountsMonthPayment'] += $datas[$i]['AD_AccountsMonthPayment'];
            $row['AD_BadDebtCount'] += $datas[$i]['AD_BadDebtCount'];
            $row['AD_BadDebtAmount'] += $datas[$i]['AD_BadDebtAmount'];
            $row['AD_PerAccountsDueBalance'] += $datas[$i]['AD_PerAccountsDueBalance'];
            $row['AD_CurrentAccountsDue'] += $datas[$i]['AD_CurrentAccountsDue'];
            $row['AD_Difference'] += $datas[$i]['AD_Difference'];
        }

        $datas[] = $row;

        $fileName  = ('04.OEM未払金・売掛金・OEM未収金統計表_' . $formatNowStr . '.pdf');
        $tmpFileName = $tmpFilePath . $fileName;

        $this->setTemplate('atoemmibaraitokei');
        $this->view->assign('datas', $datas);
        $this->view->assign('documentRoot', $_SERVER['DOCUMENT_ROOT']);
        $this->view->assign('title', $fileName);

        $fileName  = ('04.OEMMibaraiTokei_' . $formatNowStr . '.pdf');

        // HTML作成
        $viewRender = $this->getServiceLocator()->get('ViewRenderer');
        $html = $viewRender->render($this->view);

        // 一時ファイルの保存先
        $mdlsp = new \models\Table\TableSystemProperty($this->app->dbAdapter);
        $tempDir = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'TempFileDir');
        $tempDir = realpath($tempDir);

        // 中間ファイル名
        $fname_html = ($tempDir . '/__tmp_' . $fileName . '__.html');
        $fname_pdf  = ($tempDir . '/__tmp_' . $fileName . '__.pdf');

        // HTML出力
        file_put_contents($fname_html, $html);

        // PDF変換(外部プログラム起動)
        $ename = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'wkhtmltopdf');
        $option = " --page-size A4 --orientation landscape --margin-left 0 --margin-right 0 --margin-top 0 --margin-bottom 0 ";
        exec($ename . $option . $fname_html . ' ' . $fname_pdf);

        // ファイルの読み込み
        $pdf = file_get_contents($fname_pdf);

        // ファイルに保存
        file_put_contents($tmpFileName, $pdf);

        unlink( $fname_html );
        unlink( $fname_pdf );

        return $tmpFileName;
    }

    //------------------------------------------------------------------------------
    // CSV群(01～04)
    //------------------------------------------------------------------------------
    /**
     * CSV出力を行う[1. 直営日次統計表(CSV)]
     *
     * @param string $whereProcessingDate 抽出条件(指定日or月)
     * @param string $formatNowStr 書式化年月日
     * @param string $tmpFilePath TEMP領域
     * @return ファイル名 ※出力が行われなかった場合は''を戻す
     */
    protected function CsvCBNichijiTokei($whereProcessingDate, $formatNowStr, $tmpFilePath)
    {
        $sql = $this->getCBNichijiTokeiBaseQuery();
        $sql = sprintf($sql, $whereProcessingDate, $whereProcessingDate);
        $ri = $this->app->dbAdapter->query($sql)->execute(null);
        $datas = ResultInterfaceToArray($ri);

        $templateId = 'CKI24174_01'; // 直営日次統計表
        $templateClass = 0;
        $seq = 0;
        $templatePattern = 0;
        $tmpFileName = $tmpFilePath . '01.直営日次統計表_' . $formatNowStr . '.csv';

        $logicTemplate = new LogicTemplate($this->app->dbAdapter);
        $result = $logicTemplate->convertArraytoFile( $datas, $tmpFileName, $templateId, $templateClass, $seq, $templatePattern );

        return $tmpFileName;
    }

    /**
     * CSV出力を行う[1. 直営日次統計表(CSV)]
     *
     * @param string $whereProcessingDate 抽出条件(指定日or月)
     * @param string $formatNowStr 書式化年月日
     * @param string $tmpFilePath TEMP領域
     * @return ファイル名 ※出力が行われなかった場合は''を戻す
     */
    protected function CsvCBNichijiTokeiMonth($whereProcessingDate, $formatNowStr, $tmpFilePath)
    {
        $mdlatdst = new \models\Table\ATableDailyStatisticsTable($this->app->dbAdapter);

        // 業務日付と会計月の取得
        $mdlsp = new TableSystemProperty($this->app->dbAdapter);
        $businessDate = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'BusinessDate');
        $accountingMonth = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'AccountingMonth');
        $datas = array();
        $beforeMonth =date('Y-m-d', strtotime($accountingMonth . " -1 Month")); // １月前会計月

        // 加盟店別にクエリを抽出
        $sql = <<<EOQ
SELECT  EnterpriseId
,       EnterpriseNameKj
FROM    T_Enterprise e
        INNER JOIN M_Code mc1
                ON mc1.CodeId = 160
               AND mc1.KeyCode = IFNULL(e.OemId, 0)
WHERE mc1.Class1 = 0
UNION ALL
SELECT  99999999 AS EnterpriseId
,       'OEM移管分' AS EnterpriseNameKj
EOQ;

        $ri = $this->app->dbAdapter->query($sql)->execute(null);

        $sqlTkBase = $this->getCBNichijiTokeiBaseQuery();
        foreach ($ri as $rowent) {
            $eid = $rowent['EnterpriseId'];
            $enamekj = $rowent['EnterpriseNameKj'];

            $where = $whereProcessingDate . ' AND EnterpriseId = ' . $eid;
            $sqlTk = sprintf($sqlTkBase, $where);

            $addflg = false;

            $row = $this->app->dbAdapter->query($sqlTk)->execute(null)->current();
            $addflg = $row ? true : false;

            if (!$row) {
                // データが取れない場合は初期化
                $row = array(
                        'Seq' => 0,
                        'DailyMonthlyFlg' => 1,
                        'ProcessingDate' => $businessDate, // 業務日付
                        'AccountDate' => $accountingMonth, // 会計月
                        'EnterpriseId' => $eid,
                        'EnterpriseNameKj' => $enamekj,
                        'MB__AccountsReceivableBalance' => 0,
                        'M_ChargeCount' => 0,
                        'M_ChargeAmount' => 0,
                        'M_CancelCount' => 0,
                        'M_CancelAmount' => 0,
                        'M_SettlementBackCount' => 0,
                        'M_SettlementBackAmount' => 0,
                        'M_TransferCount' => 0,
                        'M_TransferAmount' => 0,
                        'M_ReClaimFeeCount' => 0,
                        'M_ReClaimFeeAmount' => 0,
                        'M_DamageCount' => 0,
                        'M_DamageAmount' => 0,
                        'M_ReceiptCount' => 0,
                        'M_ReceiptAmount' => 0,
                        'M_RepayCount' => 0,
                        'M_RepayAmount' => 0,
                        'M_BadDebtCount' => 0,
                        'M_BadDebtAmount' => 0,
                        'M_OtherPaymentCount' => 0,
                        'M_OtherPaymentAmount' => 0,
                        'M_AccountsReceivableBalance' => 0,
                        'M_SuspensePaymentsAmount' => 0,
                        'M_AccountsReceivableBalanceDiff' => 0,
                        'M_SettlementFee' => 0,
                        'M_ClaimFee' => 0,
                        'M_ClaimFeeTax' => 0,
                        'M_MonthlyFee' => 0,
                        'M_MonthlyFeeTax' => 0,
                        'M_IncludeMonthlyFee' => 0,
                        'M_IncludeMonthlyFeeTax' => 0,
                        'M_ApiMonthlyFee' => 0,
                        'M_ApiMonthlyFeeTax' => 0,
                        'M_CreditNoticeMonthlyFee' => 0,
                        'M_CreditNoticeMonthlyFeeTax' => 0,
                        'M_NCreditNoticeMonthlyFee' => 0,
                        'M_NCreditNoticeMonthlyFeeTax' => 0,
                        'M_AddClaimFee' => 0,
                        'M_AddClaimFeeTax' => 0,
                        'M_DamageInterestAmount' => 0,
                        'M_CanSettlementFee' => 0,
                        'M_CanClaimFee' => 0,
                        'M_CanClaimFeeTax' => 0,
                        'M_SettlementFeeTotal' => 0,
                        'M_ClaimFeeTotal' => 0,
                        'M_ClaimFeeTaxTotal' => 0,
                        'M_MonthlyFeeTotal' => 0,
                        'M_MonthlyFeeTaxTotal' => 0,
                        'M_IncludeMonthlyFeeTotal' => 0,
                        'M_IncludeMonthlyFeeTaxTotal' => 0,
                        'M_ApiMonthlyFeeTotal' => 0,
                        'M_ApiMonthlyFeeTaxTotal' => 0,
                        'M_CreditNoticeMonthlyFeeTotal' => 0,
                        'M_CreditNoticeMonthlyFeeTaxTotal' => 0,
                        'M_NCreditNoticeMonthlyFeeTotal' => 0,
                        'M_NCreditNoticeMonthlyFeeTaxTotal' => 0,
                        'M_AddClaimFeeTotal' => 0,
                        'M_AddClaimFeeTaxTotal' => 0,
                        'M_DamageInterestAmountTotal' => 0,
                        'M_AllTotal' => 0,
                        'M_SettlementFeeOther' => 0,
                        'M_ClaimFeeOther' => 0,
                        'M_ClaimFeeTaxOther' => 0,
                        'M_MonthlyFeeOther' => 0,
                        'M_MonthlyFeeTaxOther' => 0,
                        'M_IncludeMonthlyFeeOther' => 0,
                        'M_IncludeMonthlyFeeTaxOther' => 0,
                        'M_ApiMonthlyFeeOther' => 0,
                        'M_ApiMonthlyFeeTaxOther' => 0,
                        'M_CreditNoticeMonthlyFeeOther' => 0,
                        'M_CreditNoticeMonthlyFeeTaxOther' => 0,
                        'M_NCreditNoticeMonthlyFeeOther' => 0,
                        'M_NCreditNoticeMonthlyFeeTaxOther' => 0,
                        'M_AddClaimFeeOther' => 0,
                        'M_AddClaimFeeTaxOther' => 0,
                        'M_DamageInterestAmountOther' => 0,
                        'M_SettlementFeeDiff' => 0,
                        'M_ClaimFeeDiff' => 0,
                        'M_ClaimFeeTaxDiff' => 0,
                        'M_MonthlyFeeDiff' => 0,
                        'M_MonthlyFeeTaxDiff' => 0,
                        'M_IncludeMonthlyFeeDiff' => 0,
                        'M_IncludeMonthlyFeeTaxDiff' => 0,
                        'M_ApiMonthlyFeeDiff' => 0,
                        'M_ApiMonthlyFeeTaxDiff' => 0,
                        'M_CreditNoticeMonthlyFeeDiff' => 0,
                        'M_CreditNoticeMonthlyFeeTaxDiff' => 0,
                        'M_NCreditNoticeMonthlyFeeDiff' => 0,
                        'M_NCreditNoticeMonthlyFeeTaxDiff' => 0,
                        'M_ReserveMonthlyFeeDiff' => 0,
                        'M_ReserveMonthlyFeeTaxDiff' => 0,
                        'M_AddClaimFeeDiff' => 0,
                        'M_AddClaimFeeTaxDiff' => 0,
                        'M_DamageInterestAmountDiff' => 0,
                );
            }

            // (MB__AccountsReceivableBalanceを、１月前の会計月の値で更新する)
            $prevalgetsql = " SELECT M_AccountsReceivableBalance FROM AT_DailyStatisticsTable WHERE DailyMonthlyFlg = 1 AND AccountDate = :AccountDate AND EnterpriseId = :EnterpriseId ";
            $prevalgetrow = $this->app->dbAdapter->query($prevalgetsql)->execute(array(':AccountDate' => $beforeMonth, ':EnterpriseId' => $eid))->current();
            if ($prevalgetrow) {
                $row['MB__AccountsReceivableBalance'] = nvl($prevalgetrow['M_AccountsReceivableBalance'], 0);
                $addflg = true;
            }

            // -----------------------------------------
            // (16.再発行手数料明細)
            // -----------------------------------------
            if (isset($this->_aryEnt01[16][$eid])) {
                $addflg = true;
            }
            $row['M_ReClaimFeeCount']                  = nvl($this->_aryEnt01[16][$eid]['CNT_ClaimFee'], 0);
            $row['M_ReClaimFeeAmount']                 = nvl($this->_aryEnt01[16][$eid]['SUM_ClaimFee'], 0);
            $row['M_DamageCount']                      = nvl($this->_aryEnt01[16][$eid]['CNT_Dmg'], 0);
            $row['M_DamageAmount']                     = nvl($this->_aryEnt01[16][$eid]['SUM_Dmg'], 0);
            $row['M_AddClaimFee']                      = nvl($this->_aryEnt01[16][$eid]['SUM_ClaimFee'], 0) - nvl($this->_aryEnt01[16][$eid]['SUM_ClaimFeeTax'], 0);    // [当月累計CB売上]項目
            $row['M_AddClaimFeeTax']                   = nvl($this->_aryEnt01[16][$eid]['SUM_ClaimFeeTax'], 0); // [当月累計CB売上]項目
            $row['M_DamageInterestAmount']             = nvl($this->_aryEnt01[16][$eid]['SUM_Dmg'], 0);         // [当月累計CB売上]項目

            // -----------------------------------------
            // (18.OEM移管明細)
            // -----------------------------------------
            if ($eid == 99999999) {
                if (isset($this->_aryEnt01[18]['CNT_OemId'])) {
                    $addflg = true;
                }
                $row['M_TransferCount']                    = nvl($this->_aryEnt01[18]['CNT_OemId'], 0);

                if (isset($this->_aryEnt01[18]['SUM_Amount'])) {
                    $addflg = true;
                }
                $row['M_TransferAmount']                   = nvl($this->_aryEnt01[18]['SUM_Amount'], 0);
            }

            // -----------------------------------------
            // (05.売上明細)
            // -----------------------------------------
            if (isset($this->_aryEnt01[5][$eid])) {
                $addflg = true;
            }
            $row['M_ChargeCount']                      = nvl($this->_aryEnt01[5][$eid]['CNT_OemId'], 0);
            $row['M_ChargeAmount']                     = nvl($this->_aryEnt01[5][$eid]['SUM_UseAmount'], 0);
            $row['M_CancelCount']                      = nvl($this->_aryEnt01[5][$eid]['CNCLCNT_OemId'], 0);
            $row['M_CancelAmount']                     = nvl($this->_aryEnt01[5][$eid]['CNCLSUM_UseAmount'], 0);

            $row['M_SettlementFee']                    = nvl($this->_aryEnt01[5][$eid]['SUM_SettlementFee'], 0);
            $row['M_CanSettlementFee']                 = nvl($this->_aryEnt01[5][$eid]['SUM_CNCLSettlementFee'], 0);
            $row['M_ClaimFee']                         = nvl($this->_aryEnt01[5][$eid]['SUM_ClaimFee'], 0);
            $row['M_CanClaimFee']                      = nvl($this->_aryEnt01[5][$eid]['SUM_CNCLClaimFee'], 0);
            $row['M_ClaimFeeTax']                      = nvl($this->_aryEnt01[5][$eid]['SUM_ClaimFeeTax'], 0);
            $row['M_CanClaimFeeTax']                   = nvl($this->_aryEnt01[5][$eid]['SUM_CNCLClaimFeeTax'], 0);

            $row['M_MonthlyFee']                       = nvl($this->_aryEnt01[5][$eid]['SUM_MonthlyFee'], 0);
            $row['M_MonthlyFeeTax']                    = nvl($this->_aryEnt01[5][$eid]['SUM_MonthlyFeeTax'], 0);
            $row['M_IncludeMonthlyFee']                = nvl($this->_aryEnt01[5][$eid]['SUM_IncludeMonthlyFee'], 0);
            $row['M_IncludeMonthlyFeeTax']             = nvl($this->_aryEnt01[5][$eid]['SUM_IncludeMonthlyFeeTax'], 0);
            $row['M_ApiMonthlyFee']                    = nvl($this->_aryEnt01[5][$eid]['SUM_ApiMonthlyFee'], 0);
            $row['M_ApiMonthlyFeeTax']                 = nvl($this->_aryEnt01[5][$eid]['SUM_ApiMonthlyFeeTax'], 0);
            $row['M_CreditNoticeMonthlyFee']           = nvl($this->_aryEnt01[5][$eid]['SUM_CreditNoticeMonthlyFee'], 0);
            $row['M_CreditNoticeMonthlyFeeTax']        = nvl($this->_aryEnt01[5][$eid]['SUM_CreditNoticeMonthlyFeeTax'], 0);
            $row['M_NCreditNoticeMonthlyFee']          = nvl($this->_aryEnt01[5][$eid]['SUM_NCreditNoticeMonthlyFee'], 0);
            $row['M_NCreditNoticeMonthlyFeeTax']       = nvl($this->_aryEnt01[5][$eid]['SUM_NCreditNoticeMonthlyFeeTax'], 0);

            // (<集計行>)
            $row['M_SettlementFeeTotal']               = $row['M_SettlementFee'] + $row['M_CanSettlementFee'];
            $row['M_ClaimFeeTotal']                    = $row['M_ClaimFee'] + $row['M_CanClaimFee'];
            $row['M_ClaimFeeTaxTotal']                 = $row['M_ClaimFeeTax'] + $row['M_CanClaimFeeTax'];
            $row['M_MonthlyFeeTotal']                  = $row['M_MonthlyFee'];
            $row['M_MonthlyFeeTaxTotal']               = $row['M_MonthlyFeeTax'];
            $row['M_IncludeMonthlyFeeTotal']           = $row['M_IncludeMonthlyFee'];
            $row['M_IncludeMonthlyFeeTaxTotal']        = $row['M_IncludeMonthlyFeeTax'];
            $row['M_ApiMonthlyFeeTotal']               = $row['M_ApiMonthlyFee'];
            $row['M_ApiMonthlyFeeTaxTotal']            = $row['M_ApiMonthlyFeeTax'];
            $row['M_CreditNoticeMonthlyFeeTotal']      = $row['M_CreditNoticeMonthlyFee'];
            $row['M_CreditNoticeMonthlyFeeTaxTotal']   = $row['M_CreditNoticeMonthlyFeeTax'];
            $row['M_NCreditNoticeMonthlyFeeTotal']     = $row['M_NCreditNoticeMonthlyFee'];
            $row['M_NCreditNoticeMonthlyFeeTaxTotal']  = $row['M_NCreditNoticeMonthlyFeeTax'];
            $row['M_AddClaimFeeTotal']                 = $row['M_AddClaimFee'];
            $row['M_AddClaimFeeTaxTotal']              = $row['M_AddClaimFeeTax'];
            $row['M_DamageInterestAmountTotal']        = $row['M_DamageInterestAmount'];
            $row['M_AllTotal']                         =   $row['M_SettlementFeeTotal']
            + $row['M_ClaimFeeTotal']
            + $row['M_ClaimFeeTaxTotal']
            + $row['M_MonthlyFeeTotal']
            + $row['M_MonthlyFeeTaxTotal']
            + $row['M_IncludeMonthlyFeeTotal']
            + $row['M_IncludeMonthlyFeeTaxTotal']
            + $row['M_ApiMonthlyFeeTotal']
            + $row['M_ApiMonthlyFeeTaxTotal']
            + $row['M_CreditNoticeMonthlyFeeTotal']
            + $row['M_CreditNoticeMonthlyFeeTaxTotal']
            + $row['M_NCreditNoticeMonthlyFeeTotal']
            + $row['M_NCreditNoticeMonthlyFeeTaxTotal']
            + $row['M_AddClaimFeeTotal']
            + $row['M_AddClaimFeeTaxTotal']
            + $row['M_DamageInterestAmountTotal'];

            $row['M_SettlementFeeOther']               = $row['M_SettlementFeeTotal'];
            $row['M_ClaimFeeOther']                    = $row['M_ClaimFeeTotal'];
            $row['M_ClaimFeeTaxOther']                 = $row['M_ClaimFeeTaxTotal'];
            $row['M_MonthlyFeeOther']                  = $row['M_MonthlyFeeTotal'];
            $row['M_MonthlyFeeTaxOther']               = $row['M_MonthlyFeeTaxTotal'];
            $row['M_IncludeMonthlyFeeOther']           = $row['M_IncludeMonthlyFeeTotal'];
            $row['M_IncludeMonthlyFeeTaxOther']        = $row['M_IncludeMonthlyFeeTaxTotal'];
            $row['M_ApiMonthlyFeeOther']               = $row['M_ApiMonthlyFeeTotal'];
            $row['M_ApiMonthlyFeeTaxOther']            = $row['M_ApiMonthlyFeeTaxTotal'];
            $row['M_CreditNoticeMonthlyFeeOther']      = $row['M_CreditNoticeMonthlyFeeTotal'];
            $row['M_CreditNoticeMonthlyFeeTaxOther']   = $row['M_CreditNoticeMonthlyFeeTaxTotal'];
            $row['M_NCreditNoticeMonthlyFeeOther']     = $row['M_NCreditNoticeMonthlyFeeTotal'];
            $row['M_NCreditNoticeMonthlyFeeTaxOther']  = $row['M_NCreditNoticeMonthlyFeeTaxTotal'];
            $row['M_AddClaimFeeOther']                 = $row['M_AddClaimFeeTotal'];
            $row['M_AddClaimFeeTaxOther']              = $row['M_AddClaimFeeTaxTotal'];
            $row['M_DamageInterestAmountOther']        = $row['M_DamageInterestAmountTotal'];

            $row['M_SettlementFeeDiff']                = 0;
            $row['M_ClaimFeeDiff']                     = 0;
            $row['M_ClaimFeeTaxDiff']                  = 0;
            $row['M_MonthlyFeeDiff']                   = 0;
            $row['M_MonthlyFeeTaxDiff']                = 0;
            $row['M_IncludeMonthlyFeeDiff']            = 0;
            $row['M_IncludeMonthlyFeeTaxDiff']         = 0;
            $row['M_ApiMonthlyFeeDiff']                = 0;
            $row['M_ApiMonthlyFeeTaxDiff']             = 0;
            $row['M_CreditNoticeMonthlyFeeDiff']       = 0;
            $row['M_CreditNoticeMonthlyFeeTaxDiff']    = 0;
            $row['M_NCreditNoticeMonthlyFeeDiff']      = 0;
            $row['M_NCreditNoticeMonthlyFeeTaxDiff']   = 0;
            $row['M_AddClaimFeeDiff']                  = 0;
            $row['M_AddClaimFeeTaxDiff']               = 0;
            $row['M_DamageInterestAmountDiff']         = 0;

            // -----------------------------------------
            // (06.消費者未収金明細)
            // -----------------------------------------
            if (isset($this->_aryEnt01[6][$eid])) {
                $addflg = true;
            }
            $row['M_SuspensePaymentsAmount']       = nvl($this->_aryEnt01[6][$eid]['SUM_ReceivablesRemainingAmount'], 0);

            // -----------------------------------------
            // (11.未収金日計) ﾏｯﾋﾟﾝｸﾞ＝0:入金／1:返金／2:貸倒／3:その他
            // -----------------------------------------
            if (isset($this->_aryEnt01[11][$eid])) {
                $addflg = true;
            }
            $row['M_ReceiptCount']         = nvl($this->_aryEnt01[11][$eid]['CNT_SUMType0'], 0);
            $row['M_ReceiptAmount']        = nvl($this->_aryEnt01[11][$eid]['SUM_Amount0'] , 0);
            $row['M_RepayCount']           = nvl($this->_aryEnt01[11][$eid]['CNT_SUMType1'], 0);
            $row['M_RepayAmount']          = nvl($this->_aryEnt01[11][$eid]['SUM_Amount1'] , 0);
            $row['M_BadDebtCount']         = nvl($this->_aryEnt01[11][$eid]['CNT_SUMType2'], 0);
            $row['M_BadDebtAmount']        = nvl($this->_aryEnt01[11][$eid]['SUM_Amount2'] , 0);
            $row['M_OtherPaymentCount']    = nvl($this->_aryEnt01[11][$eid]['CNT_SUMType3'], 0);
            $row['M_OtherPaymentAmount']   = nvl($this->_aryEnt01[11][$eid]['SUM_Amount3'] , 0);

            if ($addflg == false) {
                // 追加する行が存在しない場合は次の行へ
                continue;
            }

            // -----------------------------------------
            // 直営日次統計表.月初未収金残高、の更新
            // -----------------------------------------
            try {
                $row['M_AccountsReceivableBalance']     = $row['MB__AccountsReceivableBalance'] // 月初未収金残高
                                                        + $row['M_ChargeAmount']                // 立替金額
                                                        + $row['M_CancelAmount']                // キャンセル金額
                                                        + 0                                     // 無保証立替金戻し金額
                                                        + $row['M_TransferAmount']              // OEM移管金額
                                                        + $row['M_ReClaimFeeAmount']            // 再発行手数料額
                                                        + $row['M_DamageAmount']                // 遅延損害金額
                                                        + $row['M_ReceiptAmount']               // 入金額
                                                        + $row['M_RepayAmount']                 // 返金額
                                                        + $row['M_BadDebtAmount']               // 貸倒金額
                                                        + $row['M_OtherPaymentAmount']          // その他金額
                                                        ;
                $row['M_AccountsReceivableBalanceDiff'] = $row['M_AccountsReceivableBalance'] - $row['M_SuspensePaymentsAmount'];

                if ($row['Seq'] > 0) {
                    // UPDATE対象
                    $mdlatdst->saveUpdate($row, $row['Seq']);
                }
                else {
                    // INSERT対象
                    $row['RegistId'] = 1;
                    $row['UpdateId'] = 1;
                    $mdlatdst->saveNew($row);
                }
            }
            catch (\Exception $e) {
                // 例外発生時はﾛｸﾞ出力のみにとどめる
                $this->app->logger->debug(' AccountReportController::CsvCBNichijiTokeiMonth ERROR = ' . $e->getMessage());
            }

            $datas[] = $row;
        }

        $templateId = 'CKI24174_01'; // 直営日次統計表
        $templateClass = 0;
        $seq = 0;
        $templatePattern = 0;
        $tmpFileName = $tmpFilePath . '01.直営日次統計表_' . $formatNowStr . '.csv';

        $logicTemplate = new LogicTemplate($this->app->dbAdapter);
        $result = $logicTemplate->convertArraytoFile( $datas, $tmpFileName, $templateId, $templateClass, $seq, $templatePattern );

        return $tmpFileName;
    }

    /**
     * CSV出力を行う[2. OEM日次統計表(CSV)]
     *
     * @param string $whereProcessingDate 抽出条件(指定日or月)
     * @param string $formatNowStr 書式化年月日
     * @param string $tmpFilePath TEMP領域
     * @return ファイル名 ※出力が行われなかった場合は''を戻す
     */
    protected function CsvOEMNichijiTokei($whereProcessingDate, $formatNowStr, $tmpFilePath)
    {
        $sql = $this->getOEMNichijiTokeiBaseQuery();
        $sql = sprintf($sql, $whereProcessingDate, $whereProcessingDate);
        $ri = $this->app->dbAdapter->query($sql)->execute(null);
        $datas = ResultInterfaceToArray($ri);

        $templateId = 'CKI24174_02'; // OEM日次統計表
        $templateClass = 0;
        $seq = 0;
        $templatePattern = 0;
        $tmpFileName = $tmpFilePath . '02.OEM日次統計表_' . $formatNowStr . '.csv';

        $logicTemplate = new LogicTemplate($this->app->dbAdapter);
        $result = $logicTemplate->convertArraytoFile( $datas, $tmpFileName, $templateId, $templateClass, $seq, $templatePattern );

        return $tmpFileName;
    }

    /**
     * CSV出力を行う[2. OEM日次統計表(CSV)]
     *
     * @param string $whereProcessingDate 抽出条件(指定日or月)
     * @param string $formatNowStr 書式化年月日
     * @param string $tmpFilePath TEMP領域
     * @return ファイル名 ※出力が行われなかった場合は''を戻す
     */
    protected function CsvOEMNichijiTokeiMonth($whereProcessingDate, $formatNowStr, $tmpFilePath)
    {
        $mdlatodst = new \models\Table\ATableOemDailyStatisticsTable($this->app->dbAdapter);

        // 業務日付と会計月の取得
        $mdlsp = new TableSystemProperty($this->app->dbAdapter);
        $businessDate = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'BusinessDate');
        $accountingMonth = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'AccountingMonth');
        $datas = array();
        $beforeMonth =date('Y-m-d', strtotime($accountingMonth . " -1 Month")); // １月前会計月

        // 加盟店別にクエリを抽出
        // TODO: SMBCのコードマスター対応が必要
        $sql = <<<EOQ
SELECT e.EnterpriseId
,      e.EnterpriseNameKj
,      e.OemId
,      o.OemNameKj
FROM T_Enterprise e
     INNER JOIN T_Oem o
             ON e.OemId = o.OemId
     INNER JOIN M_Code mc1
                ON mc1.CodeId = 160
               AND mc1.KeyCode = IFNULL(e.OemId, 0)
WHERE mc1.Class1 = 1
EOQ;

        $ri = $this->app->dbAdapter->query($sql)->execute(null);

        $sqlTkBase = $this->getOEMNichijiTokeiBaseQuery();
        foreach ($ri as $rowent) {
            $eid = $rowent['EnterpriseId'];
            $enamekj = $rowent['EnterpriseNameKj'];
            $oemid = $rowent['OemId'];
            $onamekj = $rowent['OemNameKj'];

            $where = $whereProcessingDate . ' AND EnterpriseId = ' . $eid;
            $sqlTk = sprintf($sqlTkBase, $where);

            $addflg = false;

            $row = $this->app->dbAdapter->query($sqlTk)->execute(null)->current();
            $addflg = $row ? true : false;

            if (!$row) {
                // データが取れない場合は初期化
                $row = array(
                        'Seq' => 0,
                        'DailyMonthlyFlg' => 1,
                        'ProcessingDate' => $businessDate,
                        'AccountDate' => $accountingMonth,
                        'OemId' => $oemid,
                        'OemNameKj' => $onamekj,
                        'EnterpriseId' => $eid,
                        'EnterpriseNameKj' => $enamekj,
                        'MB__AccountsReceivableBalance' => 0,
                        'M_ChargeCount' => 0,
                        'M_ChargeAmount' => 0,
                        'M_CancelCount' => 0,
                        'M_CancelAmount' => 0,
                        'M_SettlementBackCount' => 0,
                        'M_SettlementBackAmount' => 0,
                        'M_OemTransferCount' => 0,
                        'M_OemTransferAmount' => 0,
                        'M_ReClaimFeeCount' => 0,
                        'M_ReClaimFeeAmount' => 0,
                        'M_DamageCount' => 0,
                        'M_DamageAmount' => 0,
                        'M_ReceiptCount' => 0,
                        'M_ReceiptAmount' => 0,
                        'M_RepayCount' => 0,
                        'M_RepayAmount' => 0,
                        'M_BadDebtCount' => 0,
                        'M_BadDebtAmount' => 0,
                        'M_OtherPaymentCount' => 0,
                        'M_OtherPaymentAmount' => 0,
                        'M_AccountsReceivableBalance' => 0,
                        'M_SuspensePaymentsAmount' => 0,
                        'M_AccountsReceivableBalanceDiff' => 0,
                        'M_SettlementFee' => 0,
                        'M_SettlementFeeTax' => 0,
                        'M_ClaimFee' => 0,
                        'M_ClaimFeeTax' => 0,
                        'M_MonthlyFee' => 0,
                        'M_MonthlyFeeTax' => 0,
                        'M_OemIncludeMonthlyFee' => 0,
                        'M_OemIncludeMonthlyFeeTax' => 0,
                        'M_OemApiMonthlyFee' => 0,
                        'M_OemApiMonthlyFeeTax' => 0,
                        'M_OemCreditNoticeMonthlyFee' => 0,
                        'M_OemCreditNoticeMonthlyFeeTax' => 0,
                        'M_OemNCreditNoticeMonthlyFee' => 0,
                        'M_OemNCreditNoticeMonthlyFeeTax' => 0,
                        'M_AddClaimFee' => 0,
                        'M_AddClaimFeeTax' => 0,
                        'M_DamageInterestAmount' => 0,
                        'M_CanSettlementFee' => 0,
                        'M_CanSettlementFeeTax' => 0,
                        'M_CanClaimFee' => 0,
                        'M_CanClaimFeeTax' => 0,
                        'M_SettlementFeeTotal' => 0,
                        'M_SettlementFeeTaxTotal' => 0,
                        'M_ClaimFeeTotal' => 0,
                        'M_ClaimFeeTaxTotal' => 0,
                        'M_MonthlyFeeTotal' => 0,
                        'M_MonthlyFeeTaxTotal' => 0,
                        'M_OemIncludeMonthlyFeeTotal' => 0,
                        'M_OemIncludeMonthlyFeeTaxTotal' => 0,
                        'M_OemApiMonthlyFeeTotal' => 0,
                        'M_OemApiMonthlyFeeTaxTotal' => 0,
                        'M_OemCreditNoticeMonthlyFeeTotal' => 0,
                        'M_OemCreditNoticeMonthlyFeeTaxTotal' => 0,
                        'M_OemNCreditNoticeMonthlyFeeTotal' => 0,
                        'M_OemNCreditNoticeMonthlyFeeTaxTotal' => 0,
                        'M_AddClaimFeeTotal' => 0,
                        'M_AddClaimFeeTaxTotal' => 0,
                        'M_DamageInterestAmountTotal' => 0,
                        'M_AllTotal' => 0,
                        'M_SettlementFeeOther' => 0,
                        'M_SettlementFeeTaxOther' => 0,
                        'M_ClaimFeeOther' => 0,
                        'M_ClaimFeeTaxOther' => 0,
                        'M_MonthlyFeeOther' => 0,
                        'M_MonthlyFeeTaxOther' => 0,
                        'M_OemIncludeMonthlyFeeOther' => 0,
                        'M_OemIncludeMonthlyFeeTaxOther' => 0,
                        'M_OemApiMonthlyFeeOther' => 0,
                        'M_OemApiMonthlyFeeTaxOther' => 0,
                        'M_OemCreditNoticeMonthlyFeeOther' => 0,
                        'M_OemCreditNoticeMonthlyFeeTaxOther' => 0,
                        'M_OemNCreditNoticeMonthlyFeeOther' => 0,
                        'M_OemNCreditNoticeMonthlyFeeTaxOther' => 0,
                        'M_AddClaimFeeOther' => 0,
                        'M_AddClaimFeeTaxOther' => 0,
                        'M_DamageInterestAmountOther' => 0,
                        'M_SettlementFeeDiff' => 0,
                        'M_SettlementFeeTaxDiff' => 0,
                        'M_ClaimFeeDiff' => 0,
                        'M_ClaimFeeTaxDiff' => 0,
                        'M_MonthlyFeeDiff' => 0,
                        'M_MonthlyFeeTaxDiff' => 0,
                        'M_OemIncludeMonthlyFeeDiff' => 0,
                        'M_OemIncludeMonthlyFeeTaxDiff' => 0,
                        'M_OemApiMonthlyFeeDiff' => 0,
                        'M_OemApiMonthlyFeeTaxDiff' => 0,
                        'M_OemCreditNoticeMonthlyFeeDiff' => 0,
                        'M_OemCreditNoticeMonthlyFeeTaxDiff' => 0,
                        'M_OemNCreditNoticeMonthlyFeeDiff' => 0,
                        'M_OemNCreditNoticeMonthlyFeeTaxDiff' => 0,
                        'M_AddClaimFeeDiff' => 0,
                        'M_AddClaimFeeTaxDiff' => 0,
                        'M_DamageInterestAmountDiff' => 0,
                );
            }

            // (MB__AccountsReceivableBalanceを、１月前の会計月の値で更新する)
            $prevalgetsql = " SELECT M_AccountsReceivableBalance FROM AT_Oem_DailyStatisticsTable WHERE DailyMonthlyFlg = 1 AND AccountDate = :AccountDate AND EnterpriseId = :EnterpriseId ";
            $prevalgetrow = $this->app->dbAdapter->query($prevalgetsql)->execute(array(':AccountDate' => $beforeMonth, ':EnterpriseId' => $eid))->current();
            if ($prevalgetrow) {
                $row['MB__AccountsReceivableBalance'] = nvl($prevalgetrow['M_AccountsReceivableBalance'], 0);
                $addflg = true;
            }

            // -----------------------------------
            // (16.再発行手数料明細)
            // -----------------------------------
            if (isset($this->_aryEnt02[16][$eid])) {
                $addflg = true;
            }
            $row['M_ReClaimFeeCount']     = nvl($this->_aryEnt02[16][$eid]['CNT_ClaimFee'], 0);
            $row['M_ReClaimFeeAmount']    = nvl($this->_aryEnt02[16][$eid]['SUM_ClaimFee'], 0);
            $row['M_DamageCount']         = nvl($this->_aryEnt02[16][$eid]['CNT_Dmg'], 0);
            $row['M_DamageAmount']        = nvl($this->_aryEnt02[16][$eid]['SUM_Dmg'], 0);
            $row['M_AddClaimFee']         = nvl($this->_aryEnt02[16][$eid]['SUM_ClaimFee'], 0) - nvl($this->_aryEnt02[16][$eid]['SUM_ClaimFeeTax'], 0);   // [当月累計CB売上]項目
            $row['M_AddClaimFeeTax']      = nvl($this->_aryEnt02[16][$eid]['SUM_ClaimFeeTax'], 0);// [当月累計CB売上]項目
            $row['M_DamageInterestAmount']= nvl($this->_aryEnt02[16][$eid]['SUM_Dmg'], 0);        // [当月累計CB売上]項目

            // -----------------------------------
            // (18.OEM移管明細)
            // -----------------------------------
            if (isset($this->_aryEnt02[18][$eid])) {
                $addflg = true;
            }
            $row['M_OemTransferCount']  = nvl($this->_aryEnt02[18][$eid]['CNT_OemId'], 0);
            $row['M_OemTransferAmount'] = nvl($this->_aryEnt02[18][$eid]['SUM_Amount'], 0);


            if (isset($this->_aryEnt02[5][$eid])) {
                $addflg = true;
            }
            $row['M_ChargeCount']  = nvl($this->_aryEnt02[5][$eid]['CNT_OemId'], 0);
            $row['M_ChargeAmount'] = nvl($this->_aryEnt02[5][$eid]['SUM_UseAmount'], 0);
            $row['M_CancelCount']  = nvl($this->_aryEnt02[5][$eid]['CNCLCNT_OemId'], 0);
            $row['M_CancelAmount'] = nvl($this->_aryEnt02[5][$eid]['CNCLSUM_UseAmount'], 0);

            $row['M_SettlementFee']  = nvl($this->_aryEnt02[5][$eid]['SUM_SettlementFee'], 0);
            $row['M_CanSettlementFee']  = nvl($this->_aryEnt02[5][$eid]['SUM_CNCLSettlementFee'], 0);
            $row['M_ClaimFee']  = nvl($this->_aryEnt02[5][$eid]['SUM_ClaimFee'], 0);
            $row['M_CanClaimFee']  = nvl($this->_aryEnt02[5][$eid]['SUM_CNCLClaimFee'], 0);
            $row['M_SettlementFeeTax']  = nvl($this->_aryEnt02[5][$eid]['SUM_SettlementFeeTax'], 0);
            $row['M_CanSettlementFeeTax']  = nvl($this->_aryEnt02[5][$eid]['SUM_CNCLSettlementFeeTax'], 0);
            $row['M_ClaimFeeTax']  = nvl($this->_aryEnt02[5][$eid]['SUM_ClaimFeeTax'], 0);
            $row['M_CanClaimFeeTax']  = nvl($this->_aryEnt02[5][$eid]['SUM_CNCLClaimFeeTax'], 0);

            $row['M_MonthlyFee']  = nvl($this->_aryEnt02[5][$eid]['SUM_MonthlyFee'], 0);
            $row['M_MonthlyFeeTax']  = nvl($this->_aryEnt02[5][$eid]['SUM_MonthlyFeeTax'], 0);
            $row['M_OemIncludeMonthlyFee']  = nvl($this->_aryEnt02[5][$eid]['SUM_IncludeMonthlyFee'], 0);
            $row['M_OemIncludeMonthlyFeeTax']  = nvl($this->_aryEnt02[5][$eid]['SUM_IncludeMonthlyFeeTax'], 0);
            $row['M_OemApiMonthlyFee']  = nvl($this->_aryEnt02[5][$eid]['SUM_ApiMonthlyFee'], 0);
            $row['M_OemApiMonthlyFeeTax']  = nvl($this->_aryEnt02[5][$eid]['SUM_ApiMonthlyFeeTax'], 0);
            $row['M_OemCreditNoticeMonthlyFee']  = nvl($this->_aryEnt02[5][$eid]['SUM_CreditNoticeMonthlyFee'], 0);
            $row['M_OemCreditNoticeMonthlyFeeTax']  = nvl($this->_aryEnt02[5][$eid]['SUM_CreditNoticeMonthlyFeeTax'], 0);
            $row['M_OemNCreditNoticeMonthlyFee']  = nvl($this->_aryEnt02[5][$eid]['SUM_NCreditNoticeMonthlyFee'], 0);
            $row['M_OemNCreditNoticeMonthlyFeeTax']  = nvl($this->_aryEnt02[5][$eid]['SUM_NCreditNoticeMonthlyFeeTax'], 0);

            // (<集計行:共通>)
            $row['M_SettlementFeeTotal']                  = $row['M_SettlementFee'] + $row['M_CanSettlementFee'];
            $row['M_SettlementFeeTaxTotal']               = $row['M_SettlementFeeTax'] + $row['M_CanSettlementFeeTax'];
            $row['M_ClaimFeeTotal']                       = $row['M_ClaimFee'] + $row['M_CanClaimFee'];
            $row['M_ClaimFeeTaxTotal']                    = $row['M_ClaimFeeTax'] + $row['M_CanClaimFeeTax'];
            $row['M_MonthlyFeeTotal']                     = $row['M_MonthlyFee'];
            $row['M_MonthlyFeeTaxTotal']                  = $row['M_MonthlyFeeTax'];
            $row['M_OemIncludeMonthlyFeeTotal']           = $row['M_OemIncludeMonthlyFee'];
            $row['M_OemIncludeMonthlyFeeTaxTotal']        = $row['M_OemIncludeMonthlyFeeTax'];
            $row['M_OemApiMonthlyFeeTotal']               = $row['M_OemApiMonthlyFee'];
            $row['M_OemApiMonthlyFeeTaxTotal']            = $row['M_OemApiMonthlyFeeTax'];
            $row['M_OemCreditNoticeMonthlyFeeTotal']      = $row['M_OemCreditNoticeMonthlyFee'];
            $row['M_OemCreditNoticeMonthlyFeeTaxTotal']   = $row['M_OemCreditNoticeMonthlyFeeTax'];
            $row['M_OemNCreditNoticeMonthlyFeeTotal']     = $row['M_OemNCreditNoticeMonthlyFee'];
            $row['M_OemNCreditNoticeMonthlyFeeTaxTotal']  = $row['M_OemNCreditNoticeMonthlyFeeTax'];
            $row['M_AddClaimFeeTotal']                    = $row['M_AddClaimFee'];
            $row['M_AddClaimFeeTaxTotal']                 = $row['M_AddClaimFeeTax'];
            $row['M_DamageInterestAmountTotal']           = $row['M_DamageInterestAmount'];
            $row['M_AllTotal']                            = $row['M_SettlementFeeTotal']
            + $row['M_SettlementFeeTaxTotal']
            + $row['M_ClaimFeeTotal']
            + $row['M_ClaimFeeTaxTotal']
            + $row['M_MonthlyFeeTotal']
            + $row['M_MonthlyFeeTaxTotal']
            + $row['M_OemIncludeMonthlyFeeTotal']
            + $row['M_OemIncludeMonthlyFeeTaxTotal']
            + $row['M_OemApiMonthlyFeeTotal']
            + $row['M_OemApiMonthlyFeeTaxTotal']
            + $row['M_OemCreditNoticeMonthlyFeeTotal']
            + $row['M_OemCreditNoticeMonthlyFeeTaxTotal']
            + $row['M_OemNCreditNoticeMonthlyFeeTotal']
            + $row['M_OemNCreditNoticeMonthlyFeeTaxTotal']
            + $row['M_AddClaimFeeTotal']
            + $row['M_AddClaimFeeTaxTotal']
            + $row['M_DamageInterestAmountTotal'];

            $row['M_SettlementFeeOther']                  = $row['M_SettlementFeeTotal'];
            $row['M_SettlementFeeTaxOther']               = $row['M_SettlementFeeTaxTotal'];
            $row['M_ClaimFeeOther']                       = $row['M_ClaimFeeTotal'];
            $row['M_ClaimFeeTaxOther']                    = $row['M_ClaimFeeTaxTotal'];
            $row['M_MonthlyFeeOther']                     = $row['M_MonthlyFeeTotal'];
            $row['M_MonthlyFeeTaxOther']                  = $row['M_MonthlyFeeTaxTotal'];
            $row['M_OemIncludeMonthlyFeeOther']           = $row['M_OemIncludeMonthlyFeeTotal'];
            $row['M_OemIncludeMonthlyFeeTaxOther']        = $row['M_OemIncludeMonthlyFeeTaxTotal'];
            $row['M_OemApiMonthlyFeeOther']               = $row['M_OemApiMonthlyFeeTotal'];
            $row['M_OemApiMonthlyFeeTaxOther']            = $row['M_OemApiMonthlyFeeTaxTotal'];
            $row['M_OemCreditNoticeMonthlyFeeOther']      = $row['M_OemCreditNoticeMonthlyFeeTotal'];
            $row['M_OemCreditNoticeMonthlyFeeTaxOther']   = $row['M_OemCreditNoticeMonthlyFeeTaxTotal'];
            $row['M_OemNCreditNoticeMonthlyFeeOther']     = $row['M_OemNCreditNoticeMonthlyFeeTotal'];
            $row['M_OemNCreditNoticeMonthlyFeeTaxOther']  = $row['M_OemNCreditNoticeMonthlyFeeTaxTotal'];
            $row['M_AddClaimFeeOther']                    = $row['M_AddClaimFeeTotal'];
            $row['M_AddClaimFeeTaxOther']                 = $row['M_AddClaimFeeTaxTotal'];
            $row['M_DamageInterestAmountOther']           = $row['M_DamageInterestAmountTotal'];

            $row['M_SettlementFeeDiff']                   = 0;
            $row['M_SettlementFeeTaxDiff']                = 0;
            $row['M_ClaimFeeDiff']                        = 0;
            $row['M_ClaimFeeTaxDiff']                     = 0;
            $row['M_MonthlyFeeDiff']                      = 0;
            $row['M_MonthlyFeeTaxDiff']                   = 0;
            $row['M_OemIncludeMonthlyFeeDiff']            = 0;
            $row['M_OemIncludeMonthlyFeeTaxDiff']         = 0;
            $row['M_OemApiMonthlyFeeDiff']                = 0;
            $row['M_OemApiMonthlyFeeTaxDiff']             = 0;
            $row['M_OemCreditNoticeMonthlyFeeDiff']       = 0;
            $row['M_OemCreditNoticeMonthlyFeeTaxDiff']    = 0;
            $row['M_OemNCreditNoticeMonthlyFeeDiff']      = 0;
            $row['M_OemNCreditNoticeMonthlyFeeTaxDiff']   = 0;
            $row['M_AddClaimFeeDiff']                     = 0;
            $row['M_AddClaimFeeTaxDiff']                  = 0;
            $row['M_DamageInterestAmountDiff']            = 0;
            // -----------------------------------
            // (08.OEM仮払金明細)
            // -----------------------------------
            if (isset($this->_aryEnt02[8][$eid])) {
                $addflg = true;
            }
            $row['M_SuspensePaymentsAmount']  = nvl($this->_aryEnt02[8][$eid]['SUM_OemSuspensePayments'], 0);

            // -----------------------------------
            // (12.仮払金日計) ﾏｯﾋﾟﾝｸﾞ＝0:入金／1:返金／2:貸倒／3:その他
            // -----------------------------------
            if (isset($this->_aryEnt02[12][$eid])) {
                $addflg = true;
            }
            $row['M_ReceiptCount']        = nvl($this->_aryEnt02[12][$eid]['CNT_SUMType0'], 0);
            $row['M_ReceiptAmount']       = nvl($this->_aryEnt02[12][$eid]['SUM_Amount0'] , 0);
            $row['M_RepayCount']          = nvl($this->_aryEnt02[12][$eid]['CNT_SUMType1'], 0);
            $row['M_RepayAmount']         = nvl($this->_aryEnt02[12][$eid]['SUM_Amount1'] , 0);
            $row['M_OtherPaymentCount']   = nvl($this->_aryEnt02[12][$eid]['CNT_SUMType3'], 0);
            $row['M_OtherPaymentAmount']  = nvl($this->_aryEnt02[12][$eid]['SUM_Amount3'] , 0);

            if ($addflg == false) {
                // 追加する行が存在しない場合は次の行へ
                continue;
            }

            // -----------------------------------------
            // OEM日次統計表.月初未収金残高、の更新
            // -----------------------------------------
            try {
                $row['M_AccountsReceivableBalance']     = $row['MB__AccountsReceivableBalance'] // 月初未収金残高
                                                        + $row['M_ChargeAmount']                // 立替金額
                                                        + $row['M_CancelAmount']                // キャンセル金額
                                                        + 0                                     // 無保証立替金戻し金額
                                                        + $row['M_OemTransferAmount']           // OEM移管金額
                                                        + $row['M_ReClaimFeeAmount']            // 再発行手数料額
                                                        + $row['M_DamageAmount']                // 遅延損害金額
                                                        + $row['M_ReceiptAmount']               // 入金額
                                                        + $row['M_RepayAmount']                 // 返金額
                                                        + $row['M_OtherPaymentAmount']          // その他金額
                                                        ;
                $row['M_AccountsReceivableBalanceDiff'] = $row['M_AccountsReceivableBalance'] - $row['M_SuspensePaymentsAmount'];

                if ($row['Seq'] > 0) {
                    // UPDATE対象
                    $mdlatodst->saveUpdate($row, $row['Seq']);
                }
                else {
                    // INSERT対象
                    $row['RegistId'] = 1;
                    $row['UpdateId'] = 1;
                    $mdlatodst->saveNew($row);
                }
            }
            catch (\Exception $e) {
                // 例外発生時はﾛｸﾞ出力のみにとどめる
                $this->app->logger->debug(' AccountReportController::CsvOEMNichijiTokeiMonth ERROR = ' . $e->getMessage());
            }

            $datas[] = $row;

        }

        $templateId = 'CKI24174_02'; // OEM日次統計表
        $templateClass = 0;
        $seq = 0;
        $templatePattern = 0;
        $tmpFileName = $tmpFilePath . '02.OEM日次統計表_' . $formatNowStr . '.csv';

        $logicTemplate = new LogicTemplate($this->app->dbAdapter);
        $result = $logicTemplate->convertArraytoFile( $datas, $tmpFileName, $templateId, $templateClass, $seq, $templatePattern );

        return $tmpFileName;
    }

    /**
     * CSV出力を行う[3. 直営未払金・売掛金・加盟店未収金統計表(CSV)]
     *
     * @param string $whereProcessingDate 抽出条件(指定日or月)
     * @param string $formatNowStr 書式化年月日
     * @param string $tmpFilePath TEMP領域
     * @param array $params パラメタ
     * @return ファイル名 ※出力が行われなかった場合は''を戻す
     */
    protected function CsvCBMibaraiTokei($whereProcessingDate, $formatNowStr, $tmpFilePath, $params)
    {
        $sql = $this->getCBMibaraiTokeiBaseQuery();
        $ri = $this->app->dbAdapter->query($sql)->execute($params);
        $datas = ResultInterfaceToArray($ri);

        // 当月未残(未払金／売掛金／未収金)の登録
        $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
        $sql = <<<EOQ
INSERT INTO AT_Accounts_PayableStatisticsTable (AccountDate, EnterpriseId, AP_PreAccountsPayableBalance, AR_PreAccountsReceivableBalance, AD_PerAccountsDueBalance, RegistDate, RegistId) VALUES (
    :AccountDate
,   :EnterpriseId
,   :AP_PreAccountsPayableBalance
,   :AR_PreAccountsReceivableBalance
,   :AD_PerAccountsDueBalance
,   :RegistDate
,   :RegistId)
EOQ;
        $stm = $this->app->dbAdapter->query($sql);
        try {
            foreach ($datas as $data) {
                $stm->execute(array(
                        ':AccountDate'                      => $data['AccountDate']
                    ,   ':EnterpriseId'                     => $data['EnterpriseId']
                    ,   ':AP_PreAccountsPayableBalance'     => $data['AP_PreAccountsPayableBalance']
                    ,   ':AR_PreAccountsReceivableBalance'  => $data['AR_PreAccountsReceivableBalance']
                    ,   ':AD_PerAccountsDueBalance'         => $data['AD_PerAccountsDueBalance']
                    ,   ':RegistDate'                       => date('Y-m-d')
                    ,   ':RegistId'                         => 1
                ));
            }
            $this->app->dbAdapter->getDriver()->getConnection()->commit();
        }
        catch (\Exception $e) {
            $this->app->dbAdapter->getDriver()->getConnection()->rollback();
            // 例外発生時はﾛｸﾞ出力のみにとどめる
            $this->app->logger->debug(' AccountReportController::CsvCBMibaraiTokei ERROR = ' . $e->getMessage());
        }

        $templateId = 'CKI24174_03'; // 直営未払金・売掛金・加盟店未収金統計表
        $templateClass = 0;
        $seq = 0;
        $templatePattern = 0;
        $tmpFileName = $tmpFilePath . '03.直営未払金・売掛金・加盟店未収金統計表_' . $formatNowStr . '.csv';

        $logicTemplate = new LogicTemplate($this->app->dbAdapter);
        $result = $logicTemplate->convertArraytoFile( $datas, $tmpFileName, $templateId, $templateClass, $seq, $templatePattern );

        return $tmpFileName;
    }

    /**
     * CSV出力を行う[4. OEM未払金・売掛金・OEM未収金統計表(CSV)]
     *
     * @param string $whereProcessingDate 抽出条件(指定日or月)
     * @param string $formatNowStr 書式化年月日
     * @param string $tmpFilePath TEMP領域
     * @param array $params パラメタ
     * @return ファイル名 ※出力が行われなかった場合は''を戻す
     */
    protected function CsvOEMMibaraiTokei($whereProcessingDate, $formatNowStr, $tmpFilePath, $params)
    {
        $sql = $this->getOEMMibaraiTokeiBaseQuery();
        $sql = sprintf($sql, $whereProcessingDate, $whereProcessingDate);
        $ri = $this->app->dbAdapter->query($sql)->execute($params);
        $datas = ResultInterfaceToArray($ri);

        $templateId = 'CKI24174_04'; // 直営未払金・売掛金・加盟店未収金統計表
        $templateClass = 0;
        $seq = 0;
        $templatePattern = 0;
        $tmpFileName = $tmpFilePath . '04.OEM未払金・売掛金・OEM未収金統計表_' . $formatNowStr . '.csv';

        $logicTemplate = new LogicTemplate($this->app->dbAdapter);
        $result = $logicTemplate->convertArraytoFile( $datas, $tmpFileName, $templateId, $templateClass, $seq, $templatePattern );

        return $tmpFileName;
    }

    /**
     * CSV出力を行う[4. OEM未払金・売掛金・OEM未収金統計表(CSV)]
     * (Oem単位版)
     *
     * @param string $whereProcessingDate 抽出条件(指定日or月)
     * @param string $formatNowStr 書式化年月日
     * @param string $tmpFilePath TEMP領域
     * @param array $params パラメタ
     * @param array $oemInfo PDF出力で使用されたデータ
     * @return ファイル名 ※出力が行われなかった場合は''を戻す
     */
    protected function CsvOEMMibaraiTokeiByOem($whereProcessingDate, $formatNowStr, $tmpFilePath, $params, $oemInfo)
    {
        $sql = $this->getOEMMibaraiTokeiBaseQueryByOem();
        $ri = $this->app->dbAdapter->query($sql)->execute($params);
        $datas = ResultInterfaceToArray($ri);

        // count関数対策
        $datas_count = 0;
        if(!empty($datas)){
            $datas_count = count($datas);
        }

        // 最終行(WITH ROLLUP行)へのアサイン
        $idx = $datas_count - 1;
        // (前月残)
        $datas[$idx]['AP_AccountsPayableBalance']       = $oemInfo['AP_AccountsPayableBalance'];
        $datas[$idx]['AR_AccountsReceivableBalance']    = $oemInfo['AR_AccountsReceivableBalance'];
        $datas[$idx]['AD_AccountsDueBalance']           = $oemInfo['AD_AccountsDueBalance'];
        // (精算日計２)
        $datas[$idx]['AP_AccountsPayableOffset']        = $oemInfo['AP_AccountsPayableOffset'];
        $datas[$idx]['AP_AccountsPayableTransfer']      = $oemInfo['AP_AccountsPayableTransfer'];
        $datas[$idx]['AP_AmountPayment']                = $oemInfo['AP_AmountPayment'];
        $datas[$idx]['AD_TransferAmount']               = $oemInfo['AD_TransferAmount'];
        $datas[$idx]['AD_AccountsPayableOffset']        = $oemInfo['AD_AccountsPayableOffset'];
        // (当月残＆差)
        $datas[$idx]['AP_PreAccountsPayableBalance']    = $oemInfo['AP_PreAccountsPayableBalance'];
        $datas[$idx]['AP_Difference']                   = $oemInfo['AP_Difference'];
        $datas[$idx]['AR_PreAccountsReceivableBalance'] = $oemInfo['AR_PreAccountsReceivableBalance'];
        $datas[$idx]['AR_Difference']                   = $oemInfo['AR_Difference'];
        $datas[$idx]['AD_PerAccountsDueBalance']        = $oemInfo['AD_PerAccountsDueBalance'];
        $datas[$idx]['AD_Difference']                   = $oemInfo['AD_Difference'];
        // (その他)
        $datas[$idx]['OemId']                           = NULL;
        $datas[$idx]['OemNameKj']                       = NULL;
        $datas[$idx]['EnterpriseId']                    = NULL;
        $datas[$idx]['EnterpriseNameKj']                = 'OEM合計';
        $datas[$idx]['ProcessingDate']                  = NULL;
        $datas[$idx]['AccountDate']                     = NULL;

        // 当月末残保管(未払金／売掛金／未収金) ※Oem単位
        $sql = <<<EOQ
INSERT INTO AT_OemAccounts_PayableStatisticsTable (AccountDate,EnterpriseId,AP_PreAccountsPayableBalance,AR_PreAccountsReceivableBalance,AD_PerAccountsDueBalance) VALUES (
    :AccountDate
,   :EnterpriseId
,   :AP_PreAccountsPayableBalance
,   :AR_PreAccountsReceivableBalance
,   :AD_PerAccountsDueBalance
)
EOQ;
        $saveprms = array(
                    ':AccountDate'                      => $params[':AccountDate']
                ,   ':EnterpriseId'                     => (99999900 + (int)$params[':OemId'])
                ,   ':AP_PreAccountsPayableBalance'     => $oemInfo['AP_PreAccountsPayableBalance']
                ,   ':AR_PreAccountsReceivableBalance'  => $oemInfo['AR_PreAccountsReceivableBalance']
                ,   ':AD_PerAccountsDueBalance'         => $oemInfo['AD_PerAccountsDueBalance']
        );

        $this->app->dbAdapter->query($sql)->execute($saveprms);

        $templateId = 'CKI24174_04'; // 直営未払金・売掛金・加盟店未収金統計表
        $templateClass = 0;
        $seq = 0;
        $templatePattern = 0;
        $tmpFileName = $tmpFilePath . '04.OEM未払金・売掛金・OEM未収金統計表_' . $oemInfo['AccessId'] . '_' . $formatNowStr . '.csv';

        $logicTemplate = new LogicTemplate($this->app->dbAdapter);
        $result = $logicTemplate->convertArraytoFile( $datas, $tmpFileName, $templateId, $templateClass, $seq, $templatePattern );

        return $tmpFileName;
    }

    //------------------------------------------------------------------------------
    // CSV群(Dairy)
    //------------------------------------------------------------------------------
    /**
     * CSV出力を行う[5. 売上明細(CSV)]
     *
     * @param string $whereProcessingDate 抽出条件(指定日or月)
     * @param string $formatNowStr 書式化年月日
     * @param string $tmpFilePath TEMP領域
     * @return ファイル名 ※出力が行われなかった場合は''を戻す
     */
    protected function CsvUriageMeisai($whereProcessingDate, $formatNowStr, $tmpFilePath)
    {
        $sql = $this->getUriageMeisaiQuery();
        $sql = sprintf($sql, $whereProcessingDate, $whereProcessingDate);
        $ri = $this->app->dbAdapter->query($sql)->execute(null);
        $datas = ResultInterfaceToArray($ri);

        $templateId = 'CKI24174_05'; // 売上明細
        $templateClass = 0;
        $seq = 0;
        $templatePattern = 0;
        $tmpFileName = $tmpFilePath . '05.売上明細_' . $formatNowStr . '.csv';

        $logicTemplate = new LogicTemplate($this->app->dbAdapter);
        $result = $logicTemplate->convertArraytoFile( $datas, $tmpFileName, $templateId, $templateClass, $seq, $templatePattern );

        return $tmpFileName;
    }

    /**
     * CSV出力を行う[7. 収納代行委託先未収金明細(CSV)]
     *
     * @param string $whereProcessingDate 抽出条件(指定日or月)
     * @param string $formatNowStr 書式化年月日
     * @param string $tmpFilePath TEMP領域
     * @return ファイル名 ※出力が行われなかった場合は''を戻す
     */
    protected function CsvShunodaikoMishukinMeisai($whereProcessingDate, $formatNowStr, $tmpFilePath)
    {
        $sql = $this->getShunodaikoMishukinMeisaiQuery();
        $sql = sprintf($sql, $whereProcessingDate, $whereProcessingDate);
        $ri = $this->app->dbAdapter->query($sql)->execute(null);
        $datas = ResultInterfaceToArray($ri);

        $templateId = 'CKI24174_07'; // 収納代行委託先未収金明細
        $templateClass = 0;
        $seq = 0;
        $templatePattern = 0;
        $tmpFileName = $tmpFilePath . '07.収納代行委託先未収金明細_' . $formatNowStr . '.csv';

        $logicTemplate = new LogicTemplate($this->app->dbAdapter);
        $result = $logicTemplate->convertArraytoFile( $datas, $tmpFileName, $templateId, $templateClass, $seq, $templatePattern );

        return $tmpFileName;
    }

    /**
     * CSV出力を行う[11. 未収金日計(CSV)]
     *
     * @param string $whereProcessingDate 抽出条件(指定日or月)
     * @param string $formatNowStr 書式化年月日
     * @param string $tmpFilePath TEMP領域
     * @return ファイル名 ※出力が行われなかった場合は''を戻す
     */
    protected function CsvMishukinHikei($whereProcessingDate, $formatNowStr, $tmpFilePath)
    {
        $sql = $this->getMishukinHikeiQuery();
        $sql = sprintf($sql, $whereProcessingDate, $whereProcessingDate);
        $ri = $this->app->dbAdapter->query($sql)->execute(null);
        $datas = ResultInterfaceToArray($ri);

        $templateId = 'CKI24174_11'; // 未収金日計
        $templateClass = 0;
        $seq = 0;
        $templatePattern = 0;
        $tmpFileName = $tmpFilePath . '11.未収金日計_' . $formatNowStr . '.csv';

        $logicTemplate = new LogicTemplate($this->app->dbAdapter);
        $result = $logicTemplate->convertArraytoFile( $datas, $tmpFileName, $templateId, $templateClass, $seq, $templatePattern );

        return $tmpFileName;
    }

    /**
     * CSV出力を行う[12. 仮払金日計(CSV)]
     *
     * @param string $whereProcessingDate 抽出条件(指定日or月)
     * @param string $formatNowStr 書式化年月日
     * @param string $tmpFilePath TEMP領域
     * @return ファイル名 ※出力が行われなかった場合は''を戻す
     */
    protected function CsvKaribaraikinHikei($whereProcessingDate, $formatNowStr, $tmpFilePath)
    {
        $sql = $this->getKaribaraikinHikeiQuery();
        $sql = sprintf($sql, $whereProcessingDate, $whereProcessingDate);
        $ri = $this->app->dbAdapter->query($sql)->execute(null);
        $datas = ResultInterfaceToArray($ri);

        $templateId = 'CKI24174_12'; // 仮払金日計
        $templateClass = 0;
        $seq = 0;
        $templatePattern = 0;
        $tmpFileName = $tmpFilePath . '12.仮払金日計_' . $formatNowStr . '.csv';

        $logicTemplate = new LogicTemplate($this->app->dbAdapter);
        $result = $logicTemplate->convertArraytoFile( $datas, $tmpFileName, $templateId, $templateClass, $seq, $templatePattern );

        return $tmpFileName;
    }

    /**
     * CSV出力を行う[13. 精算日計(CSV)]
     *
     * @param string $whereProcessingDate 抽出条件(指定日or月)
     * @param string $formatNowStr 書式化年月日
     * @param string $tmpFilePath TEMP領域
     * @param boolean $isUseOemTemplate OEM版テンプレートを使うか？
     * @param int $oemId OemId
     * @param string $processingDate 例)'2016-11-15'
     * @return ファイル名 ※出力が行われなかった場合は''を戻す
     */
    protected function CsvSeisanHikei($whereProcessingDate, $formatNowStr, $tmpFilePath, $isUseOemTemplate, $oemId, $processingDate)
    {
        $sql = $this->getSeisanHikeiQuery();
        $sql = sprintf($sql, $whereProcessingDate, $whereProcessingDate);
        $ri = $this->app->dbAdapter->query($sql)->execute(null);
        $datas = ResultInterfaceToArray($ri);

        if ($isUseOemTemplate) {

            // 前回集計取得＆集計行他の調整
            $prevProcessingDate = $this->app->dbAdapter->query(
                " SELECT IFNULL(MAX(ProcessingDate), '1970-01-01') AS PrevProcessingDate FROM AT_PayOff_DailyAccount2 WHERE OemId = :OemId AND ProcessingDate <> :ProcessingDate "
                )->execute(array(':OemId' => $oemId, ':ProcessingDate' => $processingDate))->current()['PrevProcessingDate'];

            $prevWhereProcessingDate = str_replace($processingDate, $prevProcessingDate, $whereProcessingDate);
            $sql = " SELECT * FROM AT_PayOff_DailyAccount2 WHERE DailyMonthlyFlg = 0 %s ";
            $sql = sprintf($sql, $prevWhereProcessingDate);
            $ri = $this->app->dbAdapter->query($sql)->execute(null);
            $prevDatas = ResultInterfaceToArray($ri);

            // 集計行調整：前回[OEM未収金][OEM未払金保留]を、今回[OEM前精算時未収金相殺][OEM前精算時未払金保留額]へアサイン

            // count関数対策
            $datas_count = 0;
            if(!empty($datas)){
                $datas_count = count($datas);
            }

            // count関数対策
            $prevDatas_count = 0;
            if(!empty($prevDatas)){
                $prevDatas_count = count($prevDatas);
            }

            $rowIndexSummaryDatas = $datas_count - 1;          // 今回集計結果の集計行インデックス
            $rowIndexSummaryPrevDatas = $prevDatas_count - 1;  // 前回集計結果の集計行インデックス
            if ($rowIndexSummaryPrevDatas >= 0) {
                $datas[$rowIndexSummaryDatas]['AccountsDueOffsetAmount'] = $prevDatas[$rowIndexSummaryPrevDatas]['EnterpriseAccountsDue'] * -1;
                $datas[$rowIndexSummaryDatas]['AccountsPayablePendingAmount'] = $prevDatas[$rowIndexSummaryPrevDatas]['AccountsPayablePending'] * -1;
            }

            // 今回集計行他の調整
            $datas = $this->CsvSeisanHikeiOemEx($datas);

            // 最終結果をAT_PayOff_DailyAccount2へ保存
            $mdlPo2 = new ATablePayOffDailyAccount2($this->app->dbAdapter);
            $mdlPo2->saveNew(array_merge($datas[$rowIndexSummaryDatas], array('DailyMonthlyFlg' => 0, 'ProcessingDate' => $processingDate, 'OemId' => $oemId, 'RegistId' => '1', 'UpdateId' => '1', 'ValidFlg' => '1')));

        }

        $templateId = ($isUseOemTemplate) ? 'CKI24174_13_O' : 'CKI24174_13'; // 精算日計
        $templateClass = 0;
        $seq = 0;
        $templatePattern = 0;
        $tmpFileName = $tmpFilePath . '13.精算日計_' . $formatNowStr . '.csv';

        $logicTemplate = new LogicTemplate($this->app->dbAdapter);
        $result = $logicTemplate->convertArraytoFile( $datas, $tmpFileName, $templateId, $templateClass, $seq, $templatePattern );

        return $tmpFileName;
    }

    /**
     * CSV出力を行う[13. 精算日計(CSV)](サブ関数:集計行他の調整)
     *
     * @param array $datas
     * @return array 集計行他の調整されたarray
     */
    protected function CsvSeisanHikeiOemEx($datas)
    {
        $mdlSysp = new TableSystemProperty($this->app->dbAdapter);
        $tAmount = $mdlSysp->getIncludeTaxAmount(date('Y-m-d'), 300);  // 税込の振込手数料を取得

        // count関数対策
        $datas_count = 0;
        if(!empty($datas)){
            $datas_count = count($datas);
        }

        // OEM(smbc除く)時限定
        $datasCount = $datas_count;
        $rowIndexSummary = ($datasCount - 1);
        if ($datasCount > 1) {
            // 明細行が存在する時に限定した処理
            for ($i=0; $i<$datasCount - 1; $i++) {
                // 明細項目[OEM立替実行金額][OEM未収金][OEM未払金保留][OEM前精算時未収金相殺][OEM前精算時未払金保留額]はブランク化
                $datas[$i]['AdvancesAmount'] = '';
                $datas[$i]['EnterpriseAccountsDue'] = '';
                $datas[$i]['AccountsPayablePending'] = '';
                $datas[$i]['AccountsDueOffsetAmount'] = '';
                $datas[$i]['AccountsPayablePendingAmount'] = '';
            }

            // 債権債務判定の再集計
            $datas[$rowIndexSummary]['ClaimAndObligationsDecision'] =
                    $datas[$rowIndexSummary]['AccountsPayableTotal'] +
                    $datas[$rowIndexSummary]['AccountsReceivableTotal'] +
                    $datas[$rowIndexSummary]['StampFee'] +
                    $datas[$rowIndexSummary]['TransferCommission'] +
                    $datas[$rowIndexSummary]['AdjustmentAmount'] +
                    $datas[$rowIndexSummary]['EnterpriseRefund'] +
                    $datas[$rowIndexSummary]['AccountsDueOffsetAmount'] +
                    $datas[$rowIndexSummary]['AccountsPayablePendingAmount'];

            // 合計行の0初期化
            $datas[$rowIndexSummary]['AdvancesAmount'] = '0';
            $datas[$rowIndexSummary]['EnterpriseAccountsDue'] = '0';
            $datas[$rowIndexSummary]['AccountsPayablePending'] = '0';

            if ($datas[$rowIndexSummary]['ClaimAndObligationsDecision'] >= 0 && $datas[$rowIndexSummary]['ClaimAndObligationsDecision'] > $tAmount) {
                // 債権債務判定金額(合計)がプラス、且つ振込手数料より大きい
                $datas[$rowIndexSummary]['AdvancesAmount'] = $datas[$rowIndexSummary]['ClaimAndObligationsDecision'];
            }
            else if ($datas[$rowIndexSummary]['ClaimAndObligationsDecision'] >= 0 && $datas[$rowIndexSummary]['ClaimAndObligationsDecision'] <= $tAmount) {
                // 債権債務判定金額(合計)がプラス、且つ振込手数料以下の場合
                $datas[$rowIndexSummary]['AccountsPayablePending'] = $datas[$rowIndexSummary]['ClaimAndObligationsDecision'] * -1;
            }
            else {
                // 債権債務判定金額(合計)がマイナスの場合
                $datas[$rowIndexSummary]['EnterpriseAccountsDue'] = $datas[$rowIndexSummary]['ClaimAndObligationsDecision'] * -1;
            }
        }

        return $datas;
    }

    /**
     * CSV出力を行う[14. 貸倒債権一覧(CSV)]
     *
     * @param string $whereProcessingDate 抽出条件(指定日or月)
     * @param string $formatNowStr 書式化年月日
     * @param string $tmpFilePath TEMP領域
     * @return ファイル名 ※出力が行われなかった場合は''を戻す
     */
    protected function CsvKashidaoreSaikenIchiran($whereProcessingDate, $formatNowStr, $tmpFilePath)
    {
        $sql = $this->getKashidaoreSaikenIchiranQuery();
        $sql = sprintf($sql, $whereProcessingDate, $whereProcessingDate);
        $ri = $this->app->dbAdapter->query($sql)->execute(null);
        $datas = ResultInterfaceToArray($ri);

        $templateId = 'CKI24174_14'; // 貸倒債権一覧
        $templateClass = 0;
        $seq = 0;
        $templatePattern = 0;
        $tmpFileName = $tmpFilePath . '14.貸倒債権一覧_' . $formatNowStr . '.csv';

        $logicTemplate = new LogicTemplate($this->app->dbAdapter);
        $result = $logicTemplate->convertArraytoFile( $datas, $tmpFileName, $templateId, $templateClass, $seq, $templatePattern );

        return $tmpFileName;
    }

    /**
     * CSV出力を行う[15. 過剰金一覧(CSV)]
     *
     * @param string $whereProcessingDate 抽出条件(指定日or月)
     * @param string $formatNowStr 書式化年月日
     * @param string $tmpFilePath TEMP領域
     * @return ファイル名 ※出力が行われなかった場合は''を戻す
     */
    protected function CsvKajokinIchiran($whereProcessingDate, $formatNowStr, $tmpFilePath)
    {
        $sql = $this->getKajokinIchiranQuery();
        $sql = sprintf($sql, $whereProcessingDate, $whereProcessingDate);
        $ri = $this->app->dbAdapter->query($sql)->execute(null);
        $datas = ResultInterfaceToArray($ri);

        $templateId = 'CKI24174_15'; // 過剰金一覧
        $templateClass = 0;
        $seq = 0;
        $templatePattern = 0;
        $tmpFileName = $tmpFilePath . '15.過剰金一覧_' . $formatNowStr . '.csv';

        $logicTemplate = new LogicTemplate($this->app->dbAdapter);
        $result = $logicTemplate->convertArraytoFile( $datas, $tmpFileName, $templateId, $templateClass, $seq, $templatePattern );

        return $tmpFileName;
    }

    /**
     * CSV出力を行う[16. 再発行手数料明細(CSV)]
     *
     * @param string $whereProcessingDate 抽出条件(指定日or月)
     * @param string $formatNowStr 書式化年月日
     * @param string $tmpFilePath TEMP領域
     * @return ファイル名 ※出力が行われなかった場合は''を戻す
     */
    protected function CsvSaihakkotesuryoMeisai($whereProcessingDate, $formatNowStr, $tmpFilePath)
    {
        $sql = $this->getSaihakkotesuryoMeisaiQuery();
        $sql = sprintf($sql, $whereProcessingDate, $whereProcessingDate);
        $ri = $this->app->dbAdapter->query($sql)->execute(null);
        $datas = ResultInterfaceToArray($ri);

        $templateId = 'CKI24174_16'; // 再発行手数料明細
        $templateClass = 0;
        $seq = 0;
        $templatePattern = 0;
        $tmpFileName = $tmpFilePath . '16.再発行手数料明細_' . $formatNowStr . '.csv';

        $logicTemplate = new LogicTemplate($this->app->dbAdapter);
        $result = $logicTemplate->convertArraytoFile( $datas, $tmpFileName, $templateId, $templateClass, $seq, $templatePattern );

        return $tmpFileName;
    }

    /**
     * CSV出力を行う[17. 無保証立替金戻し明細(CSV)]
     *
     * @param string $whereProcessingDate 抽出条件(指定日or月)
     * @param string $formatNowStr 書式化年月日
     * @param string $tmpFilePath TEMP領域
     * @return ファイル名 ※出力が行われなかった場合は''を戻す
     */
     protected function CsvMuhoshoTatekaekinmodoshiMeisai($whereProcessingDate, $formatNowStr, $tmpFilePath)
     {
         $sql = $this->getMuhoshoTatekaekinmodoshiMeisaiQuery();
         $sql = sprintf($sql, $whereProcessingDate, $whereProcessingDate);
         $ri = $this->app->dbAdapter->query($sql)->execute(null);
         $datas = ResultInterfaceToArray($ri);

         $templateId = 'CKI24174_17'; // 無保証立替金戻し明細
         $templateClass = 0;
         $seq = 0;
         $templatePattern = 0;
         $tmpFileName = $tmpFilePath . '17.無保証立替金戻し明細_' . $formatNowStr . '.csv';

         $logicTemplate = new LogicTemplate($this->app->dbAdapter);
         $result = $logicTemplate->convertArraytoFile( $datas, $tmpFileName, $templateId, $templateClass, $seq, $templatePattern );

         return $tmpFileName;
     }

    /**
     * CSV出力を行う[18. OEM移管明細(CSV)]
     *
     * @param string $whereProcessingDate 抽出条件(指定日or月)
     * @param string $formatNowStr 書式化年月日
     * @param string $tmpFilePath TEMP領域
     * @return ファイル名 ※出力が行われなかった場合は''を戻す
     */
    protected function CsvOEMIkanMeisai($whereProcessingDate, $formatNowStr, $tmpFilePath)
    {
        $sql = $this->getOEMIkanMeisaiQuery();
        $sql = sprintf($sql, $whereProcessingDate, $whereProcessingDate);
        $ri = $this->app->dbAdapter->query($sql)->execute(null);
        $datas = ResultInterfaceToArray($ri);

        $templateId = 'CKI24174_18'; // OEM移管明細
        $templateClass = 0;
        $seq = 0;
        $templatePattern = 0;
        $tmpFileName = $tmpFilePath . '18.OEM移管明細_' . $formatNowStr . '.csv';

        $logicTemplate = new LogicTemplate($this->app->dbAdapter);
        $result = $logicTemplate->convertArraytoFile( $datas, $tmpFileName, $templateId, $templateClass, $seq, $templatePattern );

        return $tmpFileName;
    }

    /**
     * CSV出力を行う[19. 調整金一覧(CSV)]
     *
     * @param string $whereProcessingDate 抽出条件(指定日or月)
     * @param string $formatNowStr 書式化年月日
     * @param string $tmpFilePath TEMP領域
     * @return ファイル名 ※出力が行われなかった場合は''を戻す
     */
    protected function CsvChoseikinIchiran($whereProcessingDate, $formatNowStr, $tmpFilePath)
    {
        $sql = $this->getChoseikinIchiranQuery();
        $sql = sprintf($sql, $whereProcessingDate, $whereProcessingDate);
        $ri = $this->app->dbAdapter->query($sql)->execute(null);
        $datas = ResultInterfaceToArray($ri);

        $templateId = 'CKI24174_19'; // 調整金一覧
        $templateClass = 0;
        $seq = 0;
        $templatePattern = 0;
        $tmpFileName = $tmpFilePath . '19.調整金一覧_' . $formatNowStr . '.csv';

        $logicTemplate = new LogicTemplate($this->app->dbAdapter);
        $result = $logicTemplate->convertArraytoFile( $datas, $tmpFileName, $templateId, $templateClass, $seq, $templatePattern );

        return $tmpFileName;
    }

    /**
     * CSV出力を行う[20. 入金先トータル(CSV)]
     *
     * @param string $whereProcessingDate 抽出条件(指定日or月)
     * @param string $formatNowStr 書式化年月日
     * @param string $tmpFilePath TEMP領域
     * @return ファイル名 ※出力が行われなかった場合は''を戻す
     */
    protected function CsvNyukinTotal($whereProcessingDate, $formatNowStr, $tmpFilePath)
    {
        $sql = $this->getNyukinTotalQuery();
        $sql = sprintf($sql, $whereProcessingDate, $whereProcessingDate);
        $ri = $this->app->dbAdapter->query($sql)->execute(null);
        $datas = ResultInterfaceToArray($ri);

        $templateId = 'CKI24174_20'; // 入金先トータル
        $templateClass = 0;
        $seq = 0;
        $templatePattern = 0;
        $tmpFileName = $tmpFilePath . '20.入金先トータル_' . $formatNowStr . '.csv';

        $logicTemplate = new LogicTemplate($this->app->dbAdapter);
        $result = $logicTemplate->convertArraytoFile( $datas, $tmpFileName, $templateId, $templateClass, $seq, $templatePattern );

        return $tmpFileName;
    }

    //------------------------------------------------------------------------------

    //------------------------------------------------------------------------------
    // CSV群(Monthly) ※Dailyに共通関数のないもの
    //------------------------------------------------------------------------------
    /**
     * CSV出力を行う[6. 消費者未収金明細(CSV)]
     *
     * @param string $whereAccountDate 抽出条件(指定日or月)
     * @param string $formatNowStr 書式化年月日
     * @param string $tmpFilePath TEMP領域
     * @return ファイル名 ※出力が行われなかった場合は''を戻す
     */
    protected function CsvShohishaMishukinMeisai($whereProcessingDate, $formatNowStr, $tmpFilePath)
    {
        $sql = $this->getShohishaMishukinMeisaiQuery();
        $sql = sprintf($sql, $whereProcessingDate, $whereProcessingDate);
        $ri = $this->app->dbAdapter->query($sql)->execute(null);
        $datas = ResultInterfaceToArray($ri);

        $templateId = 'CKI24174_06'; // 消費者未収金明細
        $templateClass = 0;
        $seq = 0;
        $templatePattern = 0;
        $tmpFileName = $tmpFilePath . '06.消費者未収金明細_' . $formatNowStr . '.csv';

        $logicTemplate = new LogicTemplate($this->app->dbAdapter);
        $result = $logicTemplate->convertArraytoFile( $datas, $tmpFileName, $templateId, $templateClass, $seq, $templatePattern );

        //-----------------------------------------------------------
        // [01.直営日次統計表(月次)]用の処理
        //-----------------------------------------------------------
        $mdlsp = new \models\Table\TableSystemProperty($this->app->dbAdapter);

        // 会計月の取得
        $accountingMonth = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'AccountingMonth');

        $prm = array (':AccountingMonth' => $accountingMonth );

        $sql = <<<EOQ
SELECT  CASE WHEN OemId > 0 THEN 99999999
             ELSE EnterpriseId
        END EnterpriseId
,       SUM(ReceivablesRemainingAmount) AS SUM_ReceivablesRemainingAmount
FROM    AT_Consumer_AccountsDue
WHERE   DailyMonthlyFlg = 1
AND     AccountDate = :AccountingMonth
GROUP BY CASE WHEN OemId > 0 THEN 99999999
             ELSE EnterpriseId
         END
WITH ROLLUP
EOQ;
        $ri = $this->app->dbAdapter->query($sql)->execute($prm);
        foreach ($ri as $row) {
            if ($row['EnterpriseId'] == null) {
                // 総合計
                $this->_ary01[6] = $row;
            } else {
                // 加盟店別合計
                $this->_aryEnt01[6][$row['EnterpriseId']] = $row;
            }
        }

        return $tmpFileName;
    }

    /**
     * CSV出力を行う[8. OEM仮払金明細(CSV)]
     *
     * @param string $whereAccountDate 抽出条件(指定日or月)
     * @param string $formatNowStr 書式化年月日
     * @param string $tmpFilePath TEMP領域
     * @return ファイル名 ※出力が行われなかった場合は''を戻す
     */
    protected function CsvOEMKaribaraikinMeisai($whereProcessingDate, $formatNowStr, $tmpFilePath)
    {
        $sql = $this->getOEMKaribaraikinMeisaiQuery();
        $sql = sprintf($sql, $whereProcessingDate, $whereProcessingDate);
        $ri = $this->app->dbAdapter->query($sql)->execute(null);
        $datas = ResultInterfaceToArray($ri);

        $templateId = 'CKI24174_08'; // OEM仮払金明細
        $templateClass = 0;
        $seq = 0;
        $templatePattern = 0;
        $tmpFileName = $tmpFilePath . '08.OEM仮払金明細_' . $formatNowStr . '.csv';

        $logicTemplate = new LogicTemplate($this->app->dbAdapter);
        $result = $logicTemplate->convertArraytoFile( $datas, $tmpFileName, $templateId, $templateClass, $seq, $templatePattern );

        //-----------------------------------------------------------
        // [02.OEM日次統計表(月次)]用の処理
        //-----------------------------------------------------------
        $mdlsp = new \models\Table\TableSystemProperty($this->app->dbAdapter);

        // 会計月の取得
        $accountingMonth = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'AccountingMonth');

        $prm = array (':AccountingMonth' => $accountingMonth );

        $sql = <<<EOQ
SELECT  OemId
,       EnterpriseId
,       SUM(OemSuspensePayments) AS SUM_OemSuspensePayments
FROM    AT_Oem_SuspensePayments
WHERE   DailyMonthlyFlg = 1
AND     AccountDate = :AccountingMonth
GROUP BY OemId, EnterpriseId WITH ROLLUP
EOQ;
        $ri = $this->app->dbAdapter->query($sql)->execute($prm);
        foreach ($ri as $row) {
            if ($row['OemId'] != null && $row['EnterpriseId'] == null) {
                // OEM別合計
                $this->_ary02[8][$row['OemId']] = $row;

            } elseif ($row['EnterpriseId'] != null) {
                // 加盟店別合計
                $this->_aryEnt02[8][$row['EnterpriseId']] = $row;

            }
        }

        return $tmpFileName;
    }

    /**
     * CSV出力を行う[9. 直営未払金兼売掛金明細(CSV)]
     *
     * @param string $whereAccountDate 抽出条件(指定日or月)
     * @param string $formatNowStr 書式化年月日
     * @param string $tmpFilePath TEMP領域
     * @return ファイル名 ※出力が行われなかった場合は''を戻す
     */
    protected function CsvChokueiUrikakeMeisai($whereProcessingDate, $formatNowStr, $tmpFilePath)
    {
        $sql = $this->getChokueiUrikakeMeisaiQuery();
        $sql = sprintf($sql, $whereProcessingDate, $whereProcessingDate);
        $ri = $this->app->dbAdapter->query($sql)->execute(null);
        $datas = ResultInterfaceToArray($ri);

        $templateId = 'CKI24174_09'; // 直営未払金兼売掛金明細
        $templateClass = 0;
        $seq = 0;
        $templatePattern = 0;
        $tmpFileName = $tmpFilePath . '09.直営未払金兼売掛金明細_' . $formatNowStr . '.csv';

        $logicTemplate = new LogicTemplate($this->app->dbAdapter);
        $result = $logicTemplate->convertArraytoFile( $datas, $tmpFileName, $templateId, $templateClass, $seq, $templatePattern );

        return $tmpFileName;
    }

    /**
     * CSV出力を行う[10. OEM未払金兼売掛金明細(CSV)]
     *
     * @param string $whereAccountDate 抽出条件(指定日or月)
     * @param string $formatNowStr 書式化年月日
     * @param string $tmpFilePath TEMP領域
     * @return ファイル名 ※出力が行われなかった場合は''を戻す
     */
    protected function CsvOEMUrikakeMeisai($whereProcessingDate, $formatNowStr, $tmpFilePath)
    {
        $sql = $this->getOEMUrikakeMeisaiQurey();
        $sql = sprintf($sql, $whereProcessingDate, $whereProcessingDate);
        $ri = $this->app->dbAdapter->query($sql)->execute(null);
        $datas = ResultInterfaceToArray($ri);

        $templateId = 'CKI24174_10'; // OEM未払金兼売掛金明細
        $templateClass = 0;
        $seq = 0;
        $templatePattern = 0;
        $tmpFileName = $tmpFilePath . '10.OEM未払金兼売掛金明細_' . $formatNowStr . '.csv';

        $logicTemplate = new LogicTemplate($this->app->dbAdapter);
        $result = $logicTemplate->convertArraytoFile( $datas, $tmpFileName, $templateId, $templateClass, $seq, $templatePattern );

        return $tmpFileName;
    }

    /**
     * 会計帳票ダウンロードリスト
     */
    public function accdllistAction()
    {
        $params = $this->getParams();

        // 日次リスト
        $sql = " SELECT CreateDate, PresentMonth FROM AT_ReportFileDaily WHERE ValidFlg = 1 ORDER BY CreateDate DESC ";
        $dairyList = ResultInterfaceToArray($this->app->dbAdapter->query($sql)->execute(null));

        // 月次リスト
        $sql = " SELECT CreateDate, PresentMonth FROM AT_ReportFileMonthly WHERE ValidFlg = 1 ORDER BY CreateDate DESC ";
        $monthlyList = ResultInterfaceToArray($this->app->dbAdapter->query($sql)->execute(null));

        $this->view->assign('dairyList', $dairyList);
        $this->view->assign('monthlyList', $monthlyList);

        return $this->view;
    }

    /**
     * 会計帳票ダウンロード
     * ・日次 : accdllist/type/d/day/2015-11-26
     * ・月次 : accdllist/type/m/month/2015-11-01
     */
    public function accdlAction()
    {
        $params = $this->getParams();

        $mdlsp = new \models\Table\TableSystemProperty($this->app->dbAdapter);
        $category = 'systeminfo';
        $name = 'TempFileDir';
        $transDir = $mdlsp->getValue(\models\Table\TableSystemProperty::DEFAULT_MODULE, $category, $name);

        $filename = '';
        $fileBlob = null;

        if ($params['type'] == 'd') {
            // 日次
            $fileBlob = $this->app->dbAdapter->query(" SELECT ReportFile FROM AT_ReportFileDaily WHERE CreateDate = :CreateDate "
                )->execute(array(':CreateDate' => $params['day']))->current()['ReportFile'];
            $filename = '会計日次_' . date('Ymd', strtotime($params['day'])) . '.zip';
        }
        else {
            // 月次
            $fileBlob = $this->app->dbAdapter->query(" SELECT ReportFile FROM AT_ReportFileMonthly WHERE PresentMonth = :PresentMonth "
                )->execute(array(':PresentMonth' => $params['month']))->current()['ReportFile'];
            $filename = '会計月次_' . date('Ym', strtotime($params['month'])) . '.zip';
        }

        // ファイルフルパス
        $pathFileName = $transDir . '/' . $filename;

        // 同名ファイルがある場合はファイル削除
        if (file_exists($pathFileName)) {
            unlink($pathFileName);
        }

        // ファイルに保存
        file_put_contents($pathFileName, $fileBlob);

        // レスポンスヘッダの出力
        $filename = mb_convert_encoding($filename, 'sjis-win');
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=$filename");

        // データ出力
        readfile($pathFileName);

        unlink($pathFileName);

        return $this->response;
    }


    /**
     * CSV出力を行う[5. 売上明細(CSV)](月次版)
     *
     * @param string $formatNowStr 書式化年月日
     * @param string $tmpFilePath TEMP領域
     * @return ファイル名 ※出力が行われなかった場合は''を戻す
     */
    protected function CsvUriageMeisaiMonth($formatNowStr, $tmpFilePath)
    {
        $mdlsp = new \models\Table\TableSystemProperty($this->app->dbAdapter);

        // 業務日付と会計月の取得
        $businessDate = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'BusinessDate');
        $accountingMonth = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'AccountingMonth');

        $prm = array (':BusinessDate' => $businessDate, ':AccountingMonth' => $accountingMonth );

        // 月跨ぎ考慮
        $sql = " SELECT DATE_FORMAT((SELECT MAX(tmp.BusinessDate + INTERVAL 1 DAY) FROM (SELECT BusinessDate FROM T_BusinessCalendar WHERE BusinessDate >= :AccountingMonth AND BusinessFlg = 1 LIMIT 3) tmp), '%Y-%m-%d 03:00:00') AS BefAccountingDate ";
        $befAccountingDate = $this->app->dbAdapter->query($sql)->execute(array(':AccountingMonth' => $accountingMonth))->current()['BefAccountingDate'];
        $prm[':BefAccountingDate'] = $befAccountingDate;

        // 会計月の[月初][月末]の消費税率が同じ場合は、税率をバインド化
        // NOTE. 通常月の半ばで消費税率が変わることはない為、以降は本バインド実装ルートを必ず経由する(2017-10-23)
        $accountingMonthLastDay = date('Y-m-d', strtotime('last day of ' . $accountingMonth));
        $taxRate = 0;
        $isSameTaxRate = $mdlsp->isSameTaxRate($accountingMonth, $accountingMonthLastDay, $taxRate);
        if ($isSameTaxRate) {
            $prm[':tax'] = $taxRate;
        }

        //---------------------------------------
        // 主データの取得
        //---------------------------------------
        if (!$isSameTaxRate) {
        // (通常分)
        $sql1 = <<<EOQ
SELECT
     mc1.Class3 AS OemId
,    mc1.Class2 AS OemNameKj
,    o.EnterpriseId AS EnterpriseId
,    e.EnterpriseNameKj AS EnterpriseNameKj
,    o.OrderId AS OrderId
,    mc2.Class1 AS OutOfAmends
,    F_GetSalesDefiniteConditions(o.OrderSeq) AS SalesDefiniteConditions
,    DATE(apas.ATUriDay) AS SalesDefiniteDate
,    pc.FixedDate AS FixedDate
,    pc.ExecScheduleDate AS ExecScheduleDate
,    CASE WHEN apas.ATUriType = 1 THEN DATE(apas.Deli_ConfirmArrivalInputDate) -- 着荷
          WHEN apas.ATUriType = 2 THEN -- 入金
                (
                    SELECT DATE(MIN(tmprc.ReceiptProcessDate))
                    FROM T_ClaimControl tmpcc
                        ,T_ReceiptControl tmprc
                    WHERE tmpcc.OrderSeq = tmprc.OrderSeq
                    AND tmpcc.OrderSeq = o.P_OrderSeq
                    AND tmpcc.MinClaimAmount - (SELECT SUM(ReceiptAmount) FROM T_ReceiptControl WHERE OrderSeq = tmpcc.OrderSeq AND ReceiptSeq <= tmprc.ReceiptSeq) <= 0
                    AND tmprc.ReceiptSeq > IFNULL((SELECT MAX(sub1.ReceiptSeq) FROM T_ReceiptControl sub1,AT_ReceiptControl sub2 WHERE sub1.ReceiptSeq = sub2.ReceiptSeq AND sub1.OrderSeq = tmprc.OrderSeq AND Rct_CancelFlg = 1), 0)
                )
          WHEN apas.ATUriType = 3 THEN pas.ClearConditionDate -- 役務
     END AS ProcessingDate
,    CASE WHEN DATE_FORMAT(apas.ATUriDay, '%Y%m') < DATE_FORMAT(:AccountingMonth, '%Y%m') THEN :AccountingMonth
          ELSE DATE_FORMAT(apas.ATUriDay, '%Y-%m-01')
     END AS AccountDate
,    os.Deli_JournalNumber AS JournalNumber
,    mc.ManCustId AS ManCustId
,    mc.NameKj AS ManCusNameKj
,    pas.UseAmount AS UseAmountTotal
,    CASE WHEN mc1.Class1 = 0 THEN pas.AppSettlementFeeRate
          ELSE NULL
     END AS SettlementFeeRate
,    CASE WHEN mc1.Class1 = 0 THEN pas.SettlementFee
          ELSE NULL
     END AS SettlementFee
,    CASE WHEN mc1.Class1 = 0 THEN
         CASE WHEN pas.ClaimFee < 0 THEN ((pas.ClaimFee * -1) - FLOOR((IFNULL(pas.ClaimFee, 0) * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))) * -1
              ELSE pas.ClaimFee - FLOOR(IFNULL(pas.ClaimFee, 0) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))
         END
         ELSE NULL
     END AS ClaimFee
,    CASE WHEN mc1.Class1 = 0 THEN
         CASE WHEN pas.ClaimFee < 0 THEN (FLOOR((IFNULL(pas.ClaimFee, 0) * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))) * -1
              ELSE FLOOR(IFNULL(pas.ClaimFee, 0) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))
         END
         ELSE NULL
     END AS ClaimFeeTax
,    NULL AS MonthlyFee
,    NULL AS MonthlyFeeTax
,    NULL AS IncludeMonthlyFee
,    NULL AS IncludeMonthlyFeeTax
,    NULL AS ApiMonthlyFee
,    NULL AS ApiMonthlyFeeTax
,    NULL AS CreditNoticeMonthlyFee
,    NULL AS CreditNoticeMonthlyFeeTax
,    NULL AS NCreditNoticeMonthlyFee
,    NULL AS NCreditNoticeMonthlyFeeTax
,    CASE WHEN mc1.Class1 = 0 THEN
         CASE WHEN pas.ClaimFee < 0 THEN IFNULL(pas.SettlementFee, 0) + (IFNULL(((pas.ClaimFee * -1) - FLOOR((IFNULL(pas.ClaimFee, 0) * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))), 0)) * -1
              ELSE IFNULL(pas.SettlementFee, 0) + IFNULL(pas.ClaimFee - FLOOR(IFNULL(pas.ClaimFee, 0) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay)))), 0)
         END
         ELSE NULL
     END AS TotalSales
,    CASE WHEN mc1.Class1 = 0 THEN NULL
          ELSE TRUNCATE( osf.AppSettlementFeeRate * 100 / (100 + F_GetTaxRate(DATE(apas.ATUriDay))) , 5)
     END AS OemSettlementFeeRate
,    CASE WHEN mc1.Class1 = 0 THEN NULL
          ELSE
              CASE WHEN IFNULL(osf.SettlementFee, 0) < 0 THEN ((osf.SettlementFee * -1) - FLOOR((IFNULL(osf.SettlementFee, 0) * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))) * -1
                   ELSE osf.SettlementFee - FLOOR(IFNULL(osf.SettlementFee, 0) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))
              END
     END AS OemSettlementFee
,    CASE WHEN mc1.Class1 = 0 THEN NULL
          ELSE
              CASE WHEN IFNULL(osf.SettlementFee, 0) < 0 THEN (FLOOR((IFNULL(osf.SettlementFee, 0) * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))) * -1
                   ELSE FLOOR(IFNULL(osf.SettlementFee, 0) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))
              END
     END AS OemSettlementFeeTax
,    CASE WHEN mc1.Class1 = 0 THEN NULL
          ELSE
              CASE WHEN IFNULL(ocf.ClaimFee, 0) < 0 THEN ((ocf.ClaimFee * -1) - FLOOR((IFNULL(ocf.ClaimFee, 0) * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))) * -1
                   ELSE ocf.ClaimFee - FLOOR(IFNULL(ocf.ClaimFee, 0) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))
              END
     END AS OemClaimFee
,    CASE WHEN mc1.Class1 = 0 THEN NULL
          ELSE
              CASE WHEN IFNULL(ocf.ClaimFee, 0) < 0 THEN (FLOOR((IFNULL(ocf.ClaimFee, 0) * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))) * -1
                   ELSE FLOOR(IFNULL(ocf.ClaimFee, 0) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))
              END
     END AS OemClaimFeeTax
,    NULL AS OemMonthlyFee
,    NULL AS OemMonthlyFeeTax
,    NULL AS OemIncludeMonthlyFee
,    NULL AS OemIncludeMonthlyFeeTax
,    NULL AS OemApiMonthlyFee
,    NULL AS OemApiMonthlyFeeTax
,    NULL AS OemCreditNoticeMonthlyFee
,    NULL AS OemCreditNoticeMonthlyFeeTax
,    NULL AS OemNCreditNoticeMonthlyFee
,    NULL AS OemNCreditNoticeMonthlyFeeTax
,    CASE WHEN mc1.Class1 = 0 THEN NULL
          ELSE
              (CASE WHEN IFNULL(osf.SettlementFee, 0) < 0
                  THEN (IFNULL((osf.SettlementFee * -1) - FLOOR((IFNULL(osf.SettlementFee, 0) * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay)))), 0)) * -1
                  ELSE IFNULL(osf.SettlementFee - FLOOR(IFNULL(osf.SettlementFee, 0) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay)))), 0)
              END
              +
              CASE WHEN IFNULL(ocf.ClaimFee, 0) < 0
                  THEN (IFNULL((ocf.ClaimFee * -1) - FLOOR((IFNULL(ocf.ClaimFee, 0) * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay)))), 0)) * -1
                  ELSE IFNULL(ocf.ClaimFee - FLOOR(IFNULL(ocf.ClaimFee, 0) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay)))), 0)
              END)
     END AS OemTotalSales
FROM  T_PayingAndSales pas
      INNER JOIN AT_PayingAndSales apas ON (apas.Seq = pas.Seq)
      INNER JOIN T_Order o ON (o.OrderSeq = pas.OrderSeq)
      INNER JOIN M_Code mc1 ON (mc1.CodeId = 160 AND mc1.KeyCode = IFNULL(o.OemId, 0))
      INNER JOIN T_Enterprise e ON (e.EnterpriseId = o.EnterpriseId)
      INNER JOIN M_Code mc2 ON (mc2.CodeId = 159 AND mc2.KeyCode = IFNULL(o.OutOfAmends, 0))
      INNER JOIN T_OrderSummary os ON (os.OrderSeq = pas.OrderSeq)
      INNER JOIN T_Customer c ON (c.OrderSeq = o.OrderSeq)
      INNER JOIN T_EnterpriseCustomer ec ON (ec.EntCustSeq = c.EntCustSeq)
      INNER JOIN T_ManagementCustomer mc ON (mc.ManCustId = ec.ManCustId)
      LEFT OUTER JOIN T_ClaimControl cc ON (cc.OrderSeq = o.P_OrderSeq)
      LEFT OUTER JOIN T_Cancel can ON (can.OrderSeq = o.OrderSeq AND can.ValidFlg = 1)
      LEFT OUTER JOIN T_ReceiptControl rc ON (rc.ReceiptSeq = cc.LastReceiptSeq)
      LEFT OUTER JOIN T_PayingControl pc ON (pc.Seq = pas.PayingControlSeq)
      LEFT OUTER JOIN T_OemSettlementFee osf ON (osf.OrderSeq = o.OrderSeq)
      LEFT OUTER JOIN T_OemClaimFee ocf ON (ocf.OrderSeq = o.OrderSeq)
WHERE apas.ATUriDay >= DATE_FORMAT(:AccountingMonth, '%Y%m%d') AND apas.ATUriDay < DATE_FORMAT(:AccountingMonth + INTERVAL 1 MONTH, '%Y%m%d')
-- AND   ( CASE WHEN apas.ATUriDay = DATE_FORMAT(o.Deli_ConfirmArrivalDate, '%Y%m%d') THEN DATE(IFNULL(apas.Deli_ConfirmArrivalInputDate, '1970-01-01')) <= :BusinessDate ELSE TRUE END)
AND   IFNULL( can.ApprovalDate , '2100-01-01') >= (:AccountingMonth + INTERVAL 1 MONTH)
EOQ;
        $sql1 .= <<<EOQ
UNION ALL
SELECT
     mc1.Class3 AS OemId
,    mc1.Class2 AS OemNameKj
,    o.EnterpriseId AS EnterpriseId
,    e.EnterpriseNameKj AS EnterpriseNameKj
,    o.OrderId AS OrderId
,    mc2.Class1 AS OutOfAmends
,    F_GetSalesDefiniteConditions(o.OrderSeq) AS SalesDefiniteConditions
,    DATE(apas.ATUriDay) AS SalesDefiniteDate
,    pc.FixedDate AS FixedDate
,    pc.ExecScheduleDate AS ExecScheduleDate
,    CASE WHEN apas.ATUriType = 1 THEN DATE(apas.Deli_ConfirmArrivalInputDate) -- 着荷
          WHEN apas.ATUriType = 2 THEN -- 入金
                (
                    SELECT DATE(MIN(tmprc.ReceiptProcessDate))
                    FROM T_ClaimControl tmpcc
                        ,T_ReceiptControl tmprc
                    WHERE tmpcc.OrderSeq = tmprc.OrderSeq
                    AND tmpcc.OrderSeq = o.P_OrderSeq
                    AND tmpcc.MinClaimAmount - (SELECT SUM(ReceiptAmount) FROM T_ReceiptControl WHERE OrderSeq = tmpcc.OrderSeq AND ReceiptSeq <= tmprc.ReceiptSeq) <= 0
                    AND tmprc.ReceiptSeq > IFNULL((SELECT MAX(sub1.ReceiptSeq) FROM T_ReceiptControl sub1,AT_ReceiptControl sub2 WHERE sub1.ReceiptSeq = sub2.ReceiptSeq AND sub1.OrderSeq = tmprc.OrderSeq AND Rct_CancelFlg = 1), 0)
                )
          WHEN apas.ATUriType = 3 THEN pas.ClearConditionDate -- 役務
     END AS ProcessingDate
,    CASE WHEN DATE_FORMAT(apas.ATUriDay, '%Y%m') < DATE_FORMAT(:AccountingMonth, '%Y%m') THEN :AccountingMonth
          ELSE DATE_FORMAT(apas.ATUriDay, '%Y-%m-01')
     END AS AccountDate
,    os.Deli_JournalNumber AS JournalNumber
,    mc.ManCustId AS ManCustId
,    mc.NameKj AS ManCusNameKj
,    pas.UseAmount AS UseAmountTotal
,    CASE WHEN mc1.Class1 = 0 THEN pas.AppSettlementFeeRate
          ELSE NULL
     END AS SettlementFeeRate
,    CASE WHEN mc1.Class1 = 0 THEN pas.SettlementFee
          ELSE NULL
     END AS SettlementFee
,    CASE WHEN mc1.Class1 = 0 THEN
         CASE WHEN pas.ClaimFee < 0 THEN ((pas.ClaimFee * -1) - FLOOR((IFNULL(pas.ClaimFee, 0) * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))) * -1
              ELSE pas.ClaimFee - FLOOR(IFNULL(pas.ClaimFee, 0) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))
         END
         ELSE NULL
     END AS ClaimFee
,    CASE WHEN mc1.Class1 = 0 THEN
         CASE WHEN pas.ClaimFee < 0 THEN (FLOOR((IFNULL(pas.ClaimFee, 0) * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))) * -1
              ELSE FLOOR(IFNULL(pas.ClaimFee, 0) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))
         END
         ELSE NULL
     END AS ClaimFeeTax
,    NULL AS MonthlyFee
,    NULL AS MonthlyFeeTax
,    NULL AS IncludeMonthlyFee
,    NULL AS IncludeMonthlyFeeTax
,    NULL AS ApiMonthlyFee
,    NULL AS ApiMonthlyFeeTax
,    NULL AS CreditNoticeMonthlyFee
,    NULL AS CreditNoticeMonthlyFeeTax
,    NULL AS NCreditNoticeMonthlyFee
,    NULL AS NCreditNoticeMonthlyFeeTax
,    CASE WHEN mc1.Class1 = 0 THEN
         CASE WHEN pas.ClaimFee < 0 THEN IFNULL(pas.SettlementFee, 0) + (IFNULL(((pas.ClaimFee * -1) - FLOOR((IFNULL(pas.ClaimFee, 0) * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))), 0)) * -1
              ELSE IFNULL(pas.SettlementFee, 0) + IFNULL(pas.ClaimFee - FLOOR(IFNULL(pas.ClaimFee, 0) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay)))), 0)
         END
         ELSE NULL
     END AS TotalSales
,    CASE WHEN mc1.Class1 = 0 THEN NULL
          ELSE TRUNCATE( osf.AppSettlementFeeRate * 100 / (100 + F_GetTaxRate(DATE(apas.ATUriDay))) , 5)
     END AS OemSettlementFeeRate
,    CASE WHEN mc1.Class1 = 0 THEN NULL
          ELSE
              CASE WHEN IFNULL(osf.SettlementFee, 0) < 0 THEN ((osf.SettlementFee * -1) - FLOOR((IFNULL(osf.SettlementFee, 0) * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))) * -1
                   ELSE osf.SettlementFee - FLOOR(IFNULL(osf.SettlementFee, 0) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))
              END
     END AS OemSettlementFee
,    CASE WHEN mc1.Class1 = 0 THEN NULL
          ELSE
              CASE WHEN IFNULL(osf.SettlementFee, 0) < 0 THEN (FLOOR((IFNULL(osf.SettlementFee, 0) * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))) * -1
                   ELSE FLOOR(IFNULL(osf.SettlementFee, 0) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))
              END
     END AS OemSettlementFeeTax
,    CASE WHEN mc1.Class1 = 0 THEN NULL
          ELSE
              CASE WHEN IFNULL(ocf.ClaimFee, 0) < 0 THEN ((ocf.ClaimFee * -1) - FLOOR((IFNULL(ocf.ClaimFee, 0) * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))) * -1
                   ELSE ocf.ClaimFee - FLOOR(IFNULL(ocf.ClaimFee, 0) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))
              END
     END AS OemClaimFee
,    CASE WHEN mc1.Class1 = 0 THEN NULL
          ELSE
              CASE WHEN IFNULL(ocf.ClaimFee, 0) < 0 THEN (FLOOR((IFNULL(ocf.ClaimFee, 0) * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))) * -1
                   ELSE FLOOR(IFNULL(ocf.ClaimFee, 0) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))
              END
     END AS OemClaimFeeTax
,    NULL AS OemMonthlyFee
,    NULL AS OemMonthlyFeeTax
,    NULL AS OemIncludeMonthlyFee
,    NULL AS OemIncludeMonthlyFeeTax
,    NULL AS OemApiMonthlyFee
,    NULL AS OemApiMonthlyFeeTax
,    NULL AS OemCreditNoticeMonthlyFee
,    NULL AS OemCreditNoticeMonthlyFeeTax
,    NULL AS OemNCreditNoticeMonthlyFee
,    NULL AS OemNCreditNoticeMonthlyFeeTax
,    CASE WHEN mc1.Class1 = 0 THEN NULL
          ELSE
              (CASE WHEN IFNULL(osf.SettlementFee, 0) < 0
                  THEN (IFNULL((osf.SettlementFee * -1) - FLOOR((IFNULL(osf.SettlementFee, 0) * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay)))), 0)) * -1
                  ELSE IFNULL(osf.SettlementFee - FLOOR(IFNULL(osf.SettlementFee, 0) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay)))), 0)
              END
              +
              CASE WHEN IFNULL(ocf.ClaimFee, 0) < 0
                  THEN (IFNULL((ocf.ClaimFee * -1) - FLOOR((IFNULL(ocf.ClaimFee, 0) * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay)))), 0)) * -1
                  ELSE IFNULL(ocf.ClaimFee - FLOOR(IFNULL(ocf.ClaimFee, 0) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay)))), 0)
              END)
     END AS OemTotalSales
FROM  T_PayingAndSales pas
      INNER JOIN AT_PayingAndSales apas ON (apas.Seq = pas.Seq)
      INNER JOIN T_Order o ON (o.OrderSeq = pas.OrderSeq)
      INNER JOIN M_Code mc1 ON (mc1.CodeId = 160 AND mc1.KeyCode = IFNULL(o.OemId, 0))
      INNER JOIN T_Enterprise e ON (e.EnterpriseId = o.EnterpriseId)
      INNER JOIN M_Code mc2 ON (mc2.CodeId = 159 AND mc2.KeyCode = IFNULL(o.OutOfAmends, 0))
      INNER JOIN T_OrderSummary os ON (os.OrderSeq = pas.OrderSeq)
      INNER JOIN T_Customer c ON (c.OrderSeq = o.OrderSeq)
      INNER JOIN T_EnterpriseCustomer ec ON (ec.EntCustSeq = c.EntCustSeq)
      INNER JOIN T_ManagementCustomer mc ON (mc.ManCustId = ec.ManCustId)
      LEFT OUTER JOIN T_ClaimControl cc ON (cc.OrderSeq = o.P_OrderSeq)
      LEFT OUTER JOIN T_Cancel can ON (can.OrderSeq = o.OrderSeq AND can.ValidFlg = 1)
      LEFT OUTER JOIN T_ReceiptControl rc ON (rc.ReceiptSeq = cc.LastReceiptSeq)
      LEFT OUTER JOIN T_PayingControl pc ON (pc.Seq = pas.PayingControlSeq)
      LEFT OUTER JOIN T_OemSettlementFee osf ON (osf.OrderSeq = o.OrderSeq)
      LEFT OUTER JOIN T_OemClaimFee ocf ON (ocf.OrderSeq = o.OrderSeq)
WHERE
-- 月跨ぎ(着荷)
(      apas.ATUriType IN (1, 3)
   AND apas.Deli_ConfirmArrivalInputDate >= :BefAccountingDate AND apas.Deli_ConfirmArrivalInputDate < DATE_FORMAT(:AccountingMonth + INTERVAL 1 MONTH, '%Y-%m-%d 00:00:00')
   AND apas.ATUriDay >= DATE_FORMAT(:AccountingMonth - INTERVAL 1 MONTH, '%Y%m%d') AND apas.ATUriDay < DATE_FORMAT(:AccountingMonth, '%Y%m%d')
)
UNION ALL
SELECT
     mc1.Class3 AS OemId
,    mc1.Class2 AS OemNameKj
,    o.EnterpriseId AS EnterpriseId
,    e.EnterpriseNameKj AS EnterpriseNameKj
,    o.OrderId AS OrderId
,    mc2.Class1 AS OutOfAmends
,    F_GetSalesDefiniteConditions(o.OrderSeq) AS SalesDefiniteConditions
,    DATE(apas.ATUriDay) AS SalesDefiniteDate
,    pc.FixedDate AS FixedDate
,    pc.ExecScheduleDate AS ExecScheduleDate
,    CASE WHEN apas.ATUriType = 1 THEN DATE(apas.Deli_ConfirmArrivalInputDate) -- 着荷
          WHEN apas.ATUriType = 2 THEN -- 入金
                (
                    SELECT DATE(MIN(tmprc.ReceiptProcessDate))
                    FROM T_ClaimControl tmpcc
                        ,T_ReceiptControl tmprc
                    WHERE tmpcc.OrderSeq = tmprc.OrderSeq
                    AND tmpcc.OrderSeq = o.P_OrderSeq
                    AND tmpcc.MinClaimAmount - (SELECT SUM(ReceiptAmount) FROM T_ReceiptControl WHERE OrderSeq = tmpcc.OrderSeq AND ReceiptSeq <= tmprc.ReceiptSeq) <= 0
                    AND tmprc.ReceiptSeq > IFNULL((SELECT MAX(sub1.ReceiptSeq) FROM T_ReceiptControl sub1,AT_ReceiptControl sub2 WHERE sub1.ReceiptSeq = sub2.ReceiptSeq AND sub1.OrderSeq = tmprc.OrderSeq AND Rct_CancelFlg = 1), 0)
                )
          WHEN apas.ATUriType = 3 THEN pas.ClearConditionDate -- 役務
     END AS ProcessingDate
,    CASE WHEN DATE_FORMAT(apas.ATUriDay, '%Y%m') < DATE_FORMAT(:AccountingMonth, '%Y%m') THEN :AccountingMonth
          ELSE DATE_FORMAT(apas.ATUriDay, '%Y-%m-01')
     END AS AccountDate
,    os.Deli_JournalNumber AS JournalNumber
,    mc.ManCustId AS ManCustId
,    mc.NameKj AS ManCusNameKj
,    pas.UseAmount AS UseAmountTotal
,    CASE WHEN mc1.Class1 = 0 THEN pas.AppSettlementFeeRate
          ELSE NULL
     END AS SettlementFeeRate
,    CASE WHEN mc1.Class1 = 0 THEN pas.SettlementFee
          ELSE NULL
     END AS SettlementFee
,    CASE WHEN mc1.Class1 = 0 THEN
         CASE WHEN pas.ClaimFee < 0 THEN ((pas.ClaimFee * -1) - FLOOR((IFNULL(pas.ClaimFee, 0) * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))) * -1
              ELSE pas.ClaimFee - FLOOR(IFNULL(pas.ClaimFee, 0) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))
         END
         ELSE NULL
     END AS ClaimFee
,    CASE WHEN mc1.Class1 = 0 THEN
         CASE WHEN pas.ClaimFee < 0 THEN (FLOOR((IFNULL(pas.ClaimFee, 0) * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))) * -1
              ELSE FLOOR(IFNULL(pas.ClaimFee, 0) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))
         END
         ELSE NULL
     END AS ClaimFeeTax
,    NULL AS MonthlyFee
,    NULL AS MonthlyFeeTax
,    NULL AS IncludeMonthlyFee
,    NULL AS IncludeMonthlyFeeTax
,    NULL AS ApiMonthlyFee
,    NULL AS ApiMonthlyFeeTax
,    NULL AS CreditNoticeMonthlyFee
,    NULL AS CreditNoticeMonthlyFeeTax
,    NULL AS NCreditNoticeMonthlyFee
,    NULL AS NCreditNoticeMonthlyFeeTax
,    CASE WHEN mc1.Class1 = 0 THEN
         CASE WHEN pas.ClaimFee < 0 THEN IFNULL(pas.SettlementFee, 0) + (IFNULL(((pas.ClaimFee * -1) - FLOOR((IFNULL(pas.ClaimFee, 0) * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))), 0)) * -1
              ELSE IFNULL(pas.SettlementFee, 0) + IFNULL(pas.ClaimFee - FLOOR(IFNULL(pas.ClaimFee, 0) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay)))), 0)
         END
         ELSE NULL
     END AS TotalSales
,    CASE WHEN mc1.Class1 = 0 THEN NULL
          ELSE TRUNCATE( osf.AppSettlementFeeRate * 100 / (100 + F_GetTaxRate(DATE(apas.ATUriDay))) , 5)
     END AS OemSettlementFeeRate
,    CASE WHEN mc1.Class1 = 0 THEN NULL
          ELSE
              CASE WHEN IFNULL(osf.SettlementFee, 0) < 0 THEN ((osf.SettlementFee * -1) - FLOOR((IFNULL(osf.SettlementFee, 0) * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))) * -1
                   ELSE osf.SettlementFee - FLOOR(IFNULL(osf.SettlementFee, 0) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))
              END
     END AS OemSettlementFee
,    CASE WHEN mc1.Class1 = 0 THEN NULL
          ELSE
              CASE WHEN IFNULL(osf.SettlementFee, 0) < 0 THEN (FLOOR((IFNULL(osf.SettlementFee, 0) * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))) * -1
                   ELSE FLOOR(IFNULL(osf.SettlementFee, 0) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))
              END
     END AS OemSettlementFeeTax
,    CASE WHEN mc1.Class1 = 0 THEN NULL
          ELSE
              CASE WHEN IFNULL(ocf.ClaimFee, 0) < 0 THEN ((ocf.ClaimFee * -1) - FLOOR((IFNULL(ocf.ClaimFee, 0) * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))) * -1
                   ELSE ocf.ClaimFee - FLOOR(IFNULL(ocf.ClaimFee, 0) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))
              END
     END AS OemClaimFee
,    CASE WHEN mc1.Class1 = 0 THEN NULL
          ELSE
              CASE WHEN IFNULL(ocf.ClaimFee, 0) < 0 THEN (FLOOR((IFNULL(ocf.ClaimFee, 0) * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))) * -1
                   ELSE FLOOR(IFNULL(ocf.ClaimFee, 0) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))
              END
     END AS OemClaimFeeTax
,    NULL AS OemMonthlyFee
,    NULL AS OemMonthlyFeeTax
,    NULL AS OemIncludeMonthlyFee
,    NULL AS OemIncludeMonthlyFeeTax
,    NULL AS OemApiMonthlyFee
,    NULL AS OemApiMonthlyFeeTax
,    NULL AS OemCreditNoticeMonthlyFee
,    NULL AS OemCreditNoticeMonthlyFeeTax
,    NULL AS OemNCreditNoticeMonthlyFee
,    NULL AS OemNCreditNoticeMonthlyFeeTax
,    CASE WHEN mc1.Class1 = 0 THEN NULL
          ELSE
              (CASE WHEN IFNULL(osf.SettlementFee, 0) < 0
                  THEN (IFNULL((osf.SettlementFee * -1) - FLOOR((IFNULL(osf.SettlementFee, 0) * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay)))), 0)) * -1
                  ELSE IFNULL(osf.SettlementFee - FLOOR(IFNULL(osf.SettlementFee, 0) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay)))), 0)
              END
              +
              CASE WHEN IFNULL(ocf.ClaimFee, 0) < 0
                  THEN (IFNULL((ocf.ClaimFee * -1) - FLOOR((IFNULL(ocf.ClaimFee, 0) * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay)))), 0)) * -1
                  ELSE IFNULL(ocf.ClaimFee - FLOOR(IFNULL(ocf.ClaimFee, 0) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay)))), 0)
              END)
     END AS OemTotalSales
FROM  T_PayingAndSales pas
      INNER JOIN AT_PayingAndSales apas ON (apas.Seq = pas.Seq)
      INNER JOIN T_Order o ON (o.OrderSeq = pas.OrderSeq)
      INNER JOIN M_Code mc1 ON (mc1.CodeId = 160 AND mc1.KeyCode = IFNULL(o.OemId, 0))
      INNER JOIN T_Enterprise e ON (e.EnterpriseId = o.EnterpriseId)
      INNER JOIN M_Code mc2 ON (mc2.CodeId = 159 AND mc2.KeyCode = IFNULL(o.OutOfAmends, 0))
      INNER JOIN T_OrderSummary os ON (os.OrderSeq = pas.OrderSeq)
      INNER JOIN T_Customer c ON (c.OrderSeq = o.OrderSeq)
      INNER JOIN T_EnterpriseCustomer ec ON (ec.EntCustSeq = c.EntCustSeq)
      INNER JOIN T_ManagementCustomer mc ON (mc.ManCustId = ec.ManCustId)
      LEFT OUTER JOIN T_ClaimControl cc ON (cc.OrderSeq = o.P_OrderSeq)
      LEFT OUTER JOIN T_Cancel can ON (can.OrderSeq = o.OrderSeq AND can.ValidFlg = 1)
      LEFT OUTER JOIN T_ReceiptControl rc ON (rc.ReceiptSeq = cc.LastReceiptSeq)
      LEFT OUTER JOIN T_PayingControl pc ON (pc.Seq = pas.PayingControlSeq)
      LEFT OUTER JOIN T_OemSettlementFee osf ON (osf.OrderSeq = o.OrderSeq)
      LEFT OUTER JOIN T_OemClaimFee ocf ON (ocf.OrderSeq = o.OrderSeq)
      LEFT OUTER JOIN V_CloseReceiptControl vcrc ON (vcrc.OrderSeq = cc.OrderSeq)
WHERE
-- 月跨ぎ(入金)
(      apas.ATUriType = 2
   AND vcrc.ReceiptProcessDate >= :BefAccountingDate AND vcrc.ReceiptProcessDate < DATE_FORMAT(:AccountingMonth + INTERVAL 1 MONTH, '%Y-%m-%d 00:00:00')
   AND apas.ATUriDay < DATE_FORMAT(:AccountingMonth, '%Y%m%d')
)
EOQ;
        // (キャンセル分)
        $sql2 = <<<EOQ
SELECT
     mc1.Class3 AS OemId
,    mc1.Class2 AS OemNameKj
,    o.EnterpriseId AS EnterpriseId
,    e.EnterpriseNameKj AS EnterpriseNameKj
,    o.OrderId AS OrderId
,    mc2.Class1 AS OutOfAmends
,    'キャンセル日' AS SalesDefiniteConditions
,    DATE(can.ApprovalDate) AS SalesDefiniteDate
,    pc.FixedDate AS FixedDate
,    pc.ExecScheduleDate AS ExecScheduleDate
,    DATE(can.ApprovalDate) AS ProcessingDate
,    CASE WHEN DATE_FORMAT(can.ApprovalDate, '%Y%m') < DATE_FORMAT(:AccountingMonth, '%Y%m') THEN :AccountingMonth
          ELSE DATE_FORMAT(can.ApprovalDate, '%Y-%m-01')
     END AS AccountDate
,    os.Deli_JournalNumber AS JournalNumber
,    mc.ManCustId AS ManCustId
,    mc.NameKj AS ManCusNameKj
,    pas.UseAmount * -1 AS UseAmountTotal
,    CASE WHEN mc1.Class1 = 0 THEN pas.AppSettlementFeeRate
          ELSE NULL
     END AS SettlementFeeRate
,    CASE WHEN mc1.Class1 = 0 THEN IFNULL(pas.SettlementFee, 0) * -1
          ELSE NULL
     END AS SettlementFee
,    CASE WHEN mc1.Class1 = 0 THEN
         CASE WHEN pas.ClaimFee < 0 THEN (((pas.ClaimFee * -1) - FLOOR((IFNULL(pas.ClaimFee, 0) * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))) * -1) * -1
              ELSE (pas.ClaimFee - FLOOR(IFNULL(pas.ClaimFee, 0) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))) * -1
         END
         ELSE NULL
     END AS ClaimFee
,    CASE WHEN mc1.Class1 = 0 THEN
         CASE WHEN pas.ClaimFee < 0 THEN (FLOOR((IFNULL(pas.ClaimFee, 0) * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay)))) * -1) * -1
              ELSE FLOOR(IFNULL(pas.ClaimFee, 0) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay)))) * -1
         END
         ELSE NULL
     END AS ClaimFeeTax
,    NULL AS MonthlyFee
,    NULL AS MonthlyFeeTax
,    NULL AS IncludeMonthlyFee
,    NULL AS IncludeMonthlyFeeTax
,    NULL AS ApiMonthlyFee
,    NULL AS ApiMonthlyFeeTax
,    NULL AS CreditNoticeMonthlyFee
,    NULL AS CreditNoticeMonthlyFeeTax
,    NULL AS NCreditNoticeMonthlyFee
,    NULL AS NCreditNoticeMonthlyFeeTax
,    CASE WHEN mc1.Class1 = 0 THEN
        (CASE WHEN pas.ClaimFee < 0
            THEN (IFNULL(pas.SettlementFee, 0) +
                  IFNULL((pas.ClaimFee * -1) - FLOOR((IFNULL(pas.ClaimFee, 0) * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay)))), 0)) * -1
            ELSE IFNULL(pas.SettlementFee, 0) +
                 IFNULL(pas.ClaimFee - FLOOR(IFNULL(pas.ClaimFee, 0) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay)))), 0)
         END) * -1
        ELSE NULL
     END AS TotalSales
,    CASE WHEN mc1.Class1 = 0 THEN NULL
          ELSE TRUNCATE( osf.AppSettlementFeeRate * 100 / (100 + F_GetTaxRate(DATE(apas.ATUriDay))) , 5)
     END AS OemSettlementFeeRate
,    CASE WHEN mc1.Class1 = 0 THEN NULL
          ELSE
              CASE WHEN IFNULL(osf.SettlementFee, 0) < 0 THEN (((osf.SettlementFee * -1) - FLOOR((IFNULL(osf.SettlementFee, 0) * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))) * -1) * -1
                   ELSE (osf.SettlementFee - FLOOR(IFNULL(osf.SettlementFee, 0) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))) * -1
              END
     END AS OemSettlementFee
,    CASE WHEN mc1.Class1 = 0 THEN NULL
          ELSE
              CASE WHEN IFNULL(osf.SettlementFee, 0) < 0 THEN (FLOOR((IFNULL(osf.SettlementFee, 0) * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay)))) * -1) * -1
                   ELSE FLOOR(IFNULL(osf.SettlementFee, 0) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay)))) * -1
              END
     END AS OemSettlementFeeTax
,    CASE WHEN mc1.Class1 = 0 THEN NULL
          ELSE
              CASE WHEN IFNULL(ocf.ClaimFee, 0) < 0 THEN (((ocf.ClaimFee * -1) - FLOOR((IFNULL(ocf.ClaimFee, 0) * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))) * -1) * -1
                   ELSE (ocf.ClaimFee - FLOOR(IFNULL(ocf.ClaimFee, 0) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))) * -1
              END
     END AS OemClaimFee
,    CASE WHEN mc1.Class1 = 0 THEN NULL
          ELSE
              CASE WHEN IFNULL(ocf.ClaimFee, 0) < 0 THEN (FLOOR((IFNULL(ocf.ClaimFee, 0) * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay)))) * -1) * -1
                   ELSE FLOOR(IFNULL(ocf.ClaimFee, 0) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay)))) * -1
              END
     END AS OemClaimFeeTax
,    NULL AS OemMonthlyFee
,    NULL AS OemMonthlyFeeTax
,    NULL AS OemIncludeMonthlyFee
,    NULL AS OemIncludeMonthlyFeeTax
,    NULL AS OemApiMonthlyFee
,    NULL AS OemApiMonthlyFeeTax
,    NULL AS OemCreditNoticeMonthlyFee
,    NULL AS OemCreditNoticeMonthlyFeeTax
,    NULL AS OemNCreditNoticeMonthlyFee
,    NULL AS OemNCreditNoticeMonthlyFeeTax
,    CASE WHEN mc1.Class1 = 0 THEN NULL
          ELSE

              (CASE WHEN IFNULL(osf.SettlementFee, 0) < 0
                  THEN (IFNULL((osf.SettlementFee * -1) - FLOOR((IFNULL(osf.SettlementFee, 0) * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay)))), 0)) * -1
                  ELSE IFNULL(osf.SettlementFee - FLOOR(IFNULL(osf.SettlementFee, 0) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay)))), 0)
               END
               +
               CASE WHEN IFNULL(ocf.ClaimFee, 0) < 0
                  THEN (IFNULL((ocf.ClaimFee * -1) - FLOOR((IFNULL(ocf.ClaimFee, 0) * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay)))), 0)) * -1
                  ELSE IFNULL(ocf.ClaimFee - FLOOR(IFNULL(ocf.ClaimFee, 0) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay)))), 0)
               END) * -1
     END AS OemTotalSales
FROM  T_PayingAndSales pas
      INNER JOIN AT_PayingAndSales apas ON (apas.Seq = pas.Seq)
      INNER JOIN T_Order o ON (o.OrderSeq = pas.OrderSeq)
      INNER JOIN M_Code mc1 ON (mc1.CodeId = 160 AND mc1.KeyCode = IFNULL(o.OemId, 0))
      INNER JOIN T_Enterprise e ON (e.EnterpriseId = o.EnterpriseId)
      INNER JOIN M_Code mc2 ON (mc2.CodeId = 159 AND mc2.KeyCode = IFNULL(o.OutOfAmends, 0))
      INNER JOIN T_OrderSummary os ON (os.OrderSeq = pas.OrderSeq)
      INNER JOIN T_Customer c ON (c.OrderSeq = o.OrderSeq)
      INNER JOIN T_EnterpriseCustomer ec ON (ec.EntCustSeq = c.EntCustSeq)
      INNER JOIN T_ManagementCustomer mc ON (mc.ManCustId = ec.ManCustId)
      INNER JOIN T_Cancel can ON (can.OrderSeq = o.OrderSeq AND can.ValidFlg = 1)
      LEFT OUTER JOIN T_ClaimControl cc ON (cc.OrderSeq = o.P_OrderSeq)
      LEFT OUTER JOIN T_ReceiptControl rc ON (rc.ReceiptSeq = cc.LastReceiptSeq)
      LEFT OUTER JOIN T_PayingControl pc ON (pc.Seq = pas.PayingControlSeq)
      LEFT OUTER JOIN T_OemSettlementFee osf ON (osf.OrderSeq = o.OrderSeq)
      LEFT OUTER JOIN T_OemClaimFee ocf ON (ocf.OrderSeq = o.OrderSeq)
WHERE apas.ATUriDay < DATE_FORMAT(:AccountingMonth, '%Y%m%d')
AND   (can.ApprovalDate >= :AccountingMonth AND can.ApprovalDate < (:AccountingMonth + INTERVAL 1 MONTH))
EOQ;
        }
        else {
        // (通常分 : 税率のバインド方式)
        $sql1 = <<<EOQ
SELECT
     mc1.Class3 AS OemId
,    mc1.Class2 AS OemNameKj
,    o.EnterpriseId AS EnterpriseId
,    e.EnterpriseNameKj AS EnterpriseNameKj
,    o.OrderId AS OrderId
,    mc2.Class1 AS OutOfAmends
,    F_GetSalesDefiniteConditions(o.OrderSeq) AS SalesDefiniteConditions
,    DATE(apas.ATUriDay) AS SalesDefiniteDate
,    pc.FixedDate AS FixedDate
,    pc.ExecScheduleDate AS ExecScheduleDate
,    CASE WHEN apas.ATUriType = 1 THEN DATE(apas.Deli_ConfirmArrivalInputDate) -- 着荷
          WHEN apas.ATUriType = 2 THEN -- 入金
                (
                    SELECT DATE(MIN(tmprc.ReceiptProcessDate))
                    FROM T_ClaimControl tmpcc
                        ,T_ReceiptControl tmprc
                    WHERE tmpcc.OrderSeq = tmprc.OrderSeq
                    AND tmpcc.OrderSeq = o.P_OrderSeq
                    AND tmpcc.MinClaimAmount - (SELECT SUM(ReceiptAmount) FROM T_ReceiptControl WHERE OrderSeq = tmpcc.OrderSeq AND ReceiptSeq <= tmprc.ReceiptSeq) <= 0
                    AND tmprc.ReceiptSeq > IFNULL((SELECT MAX(sub1.ReceiptSeq) FROM T_ReceiptControl sub1,AT_ReceiptControl sub2 WHERE sub1.ReceiptSeq = sub2.ReceiptSeq AND sub1.OrderSeq = tmprc.OrderSeq AND Rct_CancelFlg = 1), 0)
                )
          WHEN apas.ATUriType = 3 THEN pas.ClearConditionDate -- 役務
     END AS ProcessingDate
,    CASE WHEN DATE_FORMAT(apas.ATUriDay, '%Y%m') < DATE_FORMAT(:AccountingMonth, '%Y%m') THEN :AccountingMonth
          ELSE DATE_FORMAT(apas.ATUriDay, '%Y-%m-01')
     END AS AccountDate
,    os.Deli_JournalNumber AS JournalNumber
,    mc.ManCustId AS ManCustId
,    mc.NameKj AS ManCusNameKj
,    pas.UseAmount AS UseAmountTotal
,    CASE WHEN mc1.Class1 = 0 THEN pas.AppSettlementFeeRate
          ELSE NULL
     END AS SettlementFeeRate
,    CASE WHEN mc1.Class1 = 0 THEN pas.SettlementFee
          ELSE NULL
     END AS SettlementFee
,    CASE WHEN mc1.Class1 = 0 THEN
         CASE WHEN pas.ClaimFee < 0 THEN ((pas.ClaimFee * -1) - FLOOR((IFNULL(pas.ClaimFee, 0) * -1) * :tax / (100 + :tax))) * -1
              ELSE pas.ClaimFee - FLOOR(IFNULL(pas.ClaimFee, 0) * :tax / (100 + :tax))
         END
         ELSE NULL
     END AS ClaimFee
,    CASE WHEN mc1.Class1 = 0 THEN
         CASE WHEN pas.ClaimFee < 0 THEN (FLOOR((IFNULL(pas.ClaimFee, 0) * -1) * :tax / (100 + :tax))) * -1
              ELSE FLOOR(IFNULL(pas.ClaimFee, 0) * :tax / (100 + :tax))
         END
         ELSE NULL
     END AS ClaimFeeTax
,    NULL AS MonthlyFee
,    NULL AS MonthlyFeeTax
,    NULL AS IncludeMonthlyFee
,    NULL AS IncludeMonthlyFeeTax
,    NULL AS ApiMonthlyFee
,    NULL AS ApiMonthlyFeeTax
,    NULL AS CreditNoticeMonthlyFee
,    NULL AS CreditNoticeMonthlyFeeTax
,    NULL AS NCreditNoticeMonthlyFee
,    NULL AS NCreditNoticeMonthlyFeeTax
,    CASE WHEN mc1.Class1 = 0 THEN
         CASE WHEN pas.ClaimFee < 0 THEN IFNULL(pas.SettlementFee, 0) + (IFNULL(((pas.ClaimFee * -1) - FLOOR((IFNULL(pas.ClaimFee, 0) * -1) * :tax / (100 + :tax))), 0)) * -1
              ELSE IFNULL(pas.SettlementFee, 0) + IFNULL(pas.ClaimFee - FLOOR(IFNULL(pas.ClaimFee, 0) * :tax / (100 + :tax)), 0)
         END
         ELSE NULL
     END AS TotalSales
,    CASE WHEN mc1.Class1 = 0 THEN NULL
          ELSE TRUNCATE( osf.AppSettlementFeeRate * 100 / (100 + :tax) , 5)
     END AS OemSettlementFeeRate
,    CASE WHEN mc1.Class1 = 0 THEN NULL
          ELSE
              CASE WHEN IFNULL(osf.SettlementFee, 0) < 0 THEN ((osf.SettlementFee * -1) - FLOOR((IFNULL(osf.SettlementFee, 0) * -1) * :tax / (100 + :tax))) * -1
                   ELSE osf.SettlementFee - FLOOR(IFNULL(osf.SettlementFee, 0) * :tax / (100 + :tax))
              END
     END AS OemSettlementFee
,    CASE WHEN mc1.Class1 = 0 THEN NULL
          ELSE
              CASE WHEN IFNULL(osf.SettlementFee, 0) < 0 THEN (FLOOR((IFNULL(osf.SettlementFee, 0) * -1) * :tax / (100 + :tax))) * -1
                   ELSE FLOOR(IFNULL(osf.SettlementFee, 0) * :tax / (100 + :tax))
              END
     END AS OemSettlementFeeTax
,    CASE WHEN mc1.Class1 = 0 THEN NULL
          ELSE
              CASE WHEN IFNULL(ocf.ClaimFee, 0) < 0 THEN ((ocf.ClaimFee * -1) - FLOOR((IFNULL(ocf.ClaimFee, 0) * -1) * :tax / (100 + :tax))) * -1
                   ELSE ocf.ClaimFee - FLOOR(IFNULL(ocf.ClaimFee, 0) * :tax / (100 + :tax))
              END
     END AS OemClaimFee
,    CASE WHEN mc1.Class1 = 0 THEN NULL
          ELSE
              CASE WHEN IFNULL(ocf.ClaimFee, 0) < 0 THEN (FLOOR((IFNULL(ocf.ClaimFee, 0) * -1) * :tax / (100 + :tax))) * -1
                   ELSE FLOOR(IFNULL(ocf.ClaimFee, 0) * :tax / (100 + :tax))
              END
     END AS OemClaimFeeTax
,    NULL AS OemMonthlyFee
,    NULL AS OemMonthlyFeeTax
,    NULL AS OemIncludeMonthlyFee
,    NULL AS OemIncludeMonthlyFeeTax
,    NULL AS OemApiMonthlyFee
,    NULL AS OemApiMonthlyFeeTax
,    NULL AS OemCreditNoticeMonthlyFee
,    NULL AS OemCreditNoticeMonthlyFeeTax
,    NULL AS OemNCreditNoticeMonthlyFee
,    NULL AS OemNCreditNoticeMonthlyFeeTax
,    CASE WHEN mc1.Class1 = 0 THEN NULL
          ELSE
              (CASE WHEN IFNULL(osf.SettlementFee, 0) < 0
                  THEN (IFNULL((osf.SettlementFee * -1) - FLOOR((IFNULL(osf.SettlementFee, 0) * -1) * :tax / (100 + :tax)), 0)) * -1
                  ELSE IFNULL(osf.SettlementFee - FLOOR(IFNULL(osf.SettlementFee, 0) * :tax / (100 + :tax)), 0)
              END
              +
              CASE WHEN IFNULL(ocf.ClaimFee, 0) < 0
                  THEN (IFNULL((ocf.ClaimFee * -1) - FLOOR((IFNULL(ocf.ClaimFee, 0) * -1) * :tax / (100 + :tax)), 0)) * -1
                  ELSE IFNULL(ocf.ClaimFee - FLOOR(IFNULL(ocf.ClaimFee, 0) * :tax / (100 + :tax)), 0)
              END)
     END AS OemTotalSales
FROM  T_PayingAndSales pas
      INNER JOIN AT_PayingAndSales apas ON (apas.Seq = pas.Seq)
      INNER JOIN T_Order o ON (o.OrderSeq = pas.OrderSeq)
      INNER JOIN M_Code mc1 ON (mc1.CodeId = 160 AND mc1.KeyCode = IFNULL(o.OemId, 0))
      INNER JOIN T_Enterprise e ON (e.EnterpriseId = o.EnterpriseId)
      INNER JOIN M_Code mc2 ON (mc2.CodeId = 159 AND mc2.KeyCode = IFNULL(o.OutOfAmends, 0))
      INNER JOIN T_OrderSummary os ON (os.OrderSeq = pas.OrderSeq)
      INNER JOIN T_Customer c ON (c.OrderSeq = o.OrderSeq)
      INNER JOIN T_EnterpriseCustomer ec ON (ec.EntCustSeq = c.EntCustSeq)
      INNER JOIN T_ManagementCustomer mc ON (mc.ManCustId = ec.ManCustId)
      LEFT OUTER JOIN T_ClaimControl cc ON (cc.OrderSeq = o.P_OrderSeq)
      LEFT OUTER JOIN T_Cancel can ON (can.OrderSeq = o.OrderSeq AND can.ValidFlg = 1)
      LEFT OUTER JOIN T_ReceiptControl rc ON (rc.ReceiptSeq = cc.LastReceiptSeq)
      LEFT OUTER JOIN T_PayingControl pc ON (pc.Seq = pas.PayingControlSeq)
      LEFT OUTER JOIN T_OemSettlementFee osf ON (osf.OrderSeq = o.OrderSeq)
      LEFT OUTER JOIN T_OemClaimFee ocf ON (ocf.OrderSeq = o.OrderSeq)
WHERE apas.ATUriDay >= DATE_FORMAT(:AccountingMonth, '%Y%m%d') AND apas.ATUriDay < DATE_FORMAT(:AccountingMonth + INTERVAL 1 MONTH, '%Y%m%d')
-- AND   ( CASE WHEN apas.ATUriDay = DATE_FORMAT(o.Deli_ConfirmArrivalDate, '%Y%m%d') THEN DATE(IFNULL(apas.Deli_ConfirmArrivalInputDate, '1970-01-01')) <= :BusinessDate ELSE TRUE END)
AND   IFNULL( can.ApprovalDate , '2100-01-01') >= (:AccountingMonth + INTERVAL 1 MONTH)
EOQ;
        $sql1 .= <<<EOQ
UNION ALL
SELECT
     mc1.Class3 AS OemId
,    mc1.Class2 AS OemNameKj
,    o.EnterpriseId AS EnterpriseId
,    e.EnterpriseNameKj AS EnterpriseNameKj
,    o.OrderId AS OrderId
,    mc2.Class1 AS OutOfAmends
,    F_GetSalesDefiniteConditions(o.OrderSeq) AS SalesDefiniteConditions
,    DATE(apas.ATUriDay) AS SalesDefiniteDate
,    pc.FixedDate AS FixedDate
,    pc.ExecScheduleDate AS ExecScheduleDate
,    CASE WHEN apas.ATUriType = 1 THEN DATE(apas.Deli_ConfirmArrivalInputDate) -- 着荷
          WHEN apas.ATUriType = 2 THEN -- 入金
                (
                    SELECT DATE(MIN(tmprc.ReceiptProcessDate))
                    FROM T_ClaimControl tmpcc
                        ,T_ReceiptControl tmprc
                    WHERE tmpcc.OrderSeq = tmprc.OrderSeq
                    AND tmpcc.OrderSeq = o.P_OrderSeq
                    AND tmpcc.MinClaimAmount - (SELECT SUM(ReceiptAmount) FROM T_ReceiptControl WHERE OrderSeq = tmpcc.OrderSeq AND ReceiptSeq <= tmprc.ReceiptSeq) <= 0
                    AND tmprc.ReceiptSeq > IFNULL((SELECT MAX(sub1.ReceiptSeq) FROM T_ReceiptControl sub1,AT_ReceiptControl sub2 WHERE sub1.ReceiptSeq = sub2.ReceiptSeq AND sub1.OrderSeq = tmprc.OrderSeq AND Rct_CancelFlg = 1), 0)
                )
          WHEN apas.ATUriType = 3 THEN pas.ClearConditionDate -- 役務
     END AS ProcessingDate
,    CASE WHEN DATE_FORMAT(apas.ATUriDay, '%Y%m') < DATE_FORMAT(:AccountingMonth, '%Y%m') THEN :AccountingMonth
          ELSE DATE_FORMAT(apas.ATUriDay, '%Y-%m-01')
     END AS AccountDate
,    os.Deli_JournalNumber AS JournalNumber
,    mc.ManCustId AS ManCustId
,    mc.NameKj AS ManCusNameKj
,    pas.UseAmount AS UseAmountTotal
,    CASE WHEN mc1.Class1 = 0 THEN pas.AppSettlementFeeRate
          ELSE NULL
     END AS SettlementFeeRate
,    CASE WHEN mc1.Class1 = 0 THEN pas.SettlementFee
          ELSE NULL
     END AS SettlementFee
,    CASE WHEN mc1.Class1 = 0 THEN
         CASE WHEN pas.ClaimFee < 0 THEN ((pas.ClaimFee * -1) - FLOOR((IFNULL(pas.ClaimFee, 0) * -1) * :tax / (100 + :tax))) * -1
              ELSE pas.ClaimFee - FLOOR(IFNULL(pas.ClaimFee, 0) * :tax / (100 + :tax))
         END
         ELSE NULL
     END AS ClaimFee
,    CASE WHEN mc1.Class1 = 0 THEN
         CASE WHEN pas.ClaimFee < 0 THEN (FLOOR((IFNULL(pas.ClaimFee, 0) * -1) * :tax / (100 + :tax))) * -1
              ELSE FLOOR(IFNULL(pas.ClaimFee, 0) * :tax / (100 + :tax))
         END
         ELSE NULL
     END AS ClaimFeeTax
,    NULL AS MonthlyFee
,    NULL AS MonthlyFeeTax
,    NULL AS IncludeMonthlyFee
,    NULL AS IncludeMonthlyFeeTax
,    NULL AS ApiMonthlyFee
,    NULL AS ApiMonthlyFeeTax
,    NULL AS CreditNoticeMonthlyFee
,    NULL AS CreditNoticeMonthlyFeeTax
,    NULL AS NCreditNoticeMonthlyFee
,    NULL AS NCreditNoticeMonthlyFeeTax
,    CASE WHEN mc1.Class1 = 0 THEN
         CASE WHEN pas.ClaimFee < 0 THEN IFNULL(pas.SettlementFee, 0) + (IFNULL(((pas.ClaimFee * -1) - FLOOR((IFNULL(pas.ClaimFee, 0) * -1) * :tax / (100 + :tax))), 0)) * -1
              ELSE IFNULL(pas.SettlementFee, 0) + IFNULL(pas.ClaimFee - FLOOR(IFNULL(pas.ClaimFee, 0) * :tax / (100 + :tax)), 0)
         END
         ELSE NULL
     END AS TotalSales
,    CASE WHEN mc1.Class1 = 0 THEN NULL
          ELSE TRUNCATE( osf.AppSettlementFeeRate * 100 / (100 + :tax) , 5)
     END AS OemSettlementFeeRate
,    CASE WHEN mc1.Class1 = 0 THEN NULL
          ELSE
              CASE WHEN IFNULL(osf.SettlementFee, 0) < 0 THEN ((osf.SettlementFee * -1) - FLOOR((IFNULL(osf.SettlementFee, 0) * -1) * :tax / (100 + :tax))) * -1
                   ELSE osf.SettlementFee - FLOOR(IFNULL(osf.SettlementFee, 0) * :tax / (100 + :tax))
              END
     END AS OemSettlementFee
,    CASE WHEN mc1.Class1 = 0 THEN NULL
          ELSE
              CASE WHEN IFNULL(osf.SettlementFee, 0) < 0 THEN (FLOOR((IFNULL(osf.SettlementFee, 0) * -1) * :tax / (100 + :tax))) * -1
                   ELSE FLOOR(IFNULL(osf.SettlementFee, 0) * :tax / (100 + :tax))
              END
     END AS OemSettlementFeeTax
,    CASE WHEN mc1.Class1 = 0 THEN NULL
          ELSE
              CASE WHEN IFNULL(ocf.ClaimFee, 0) < 0 THEN ((ocf.ClaimFee * -1) - FLOOR((IFNULL(ocf.ClaimFee, 0) * -1) * :tax / (100 + :tax))) * -1
                   ELSE ocf.ClaimFee - FLOOR(IFNULL(ocf.ClaimFee, 0) * :tax / (100 + :tax))
              END
     END AS OemClaimFee
,    CASE WHEN mc1.Class1 = 0 THEN NULL
          ELSE
              CASE WHEN IFNULL(ocf.ClaimFee, 0) < 0 THEN (FLOOR((IFNULL(ocf.ClaimFee, 0) * -1) * :tax / (100 + :tax))) * -1
                   ELSE FLOOR(IFNULL(ocf.ClaimFee, 0) * :tax / (100 + :tax))
              END
     END AS OemClaimFeeTax
,    NULL AS OemMonthlyFee
,    NULL AS OemMonthlyFeeTax
,    NULL AS OemIncludeMonthlyFee
,    NULL AS OemIncludeMonthlyFeeTax
,    NULL AS OemApiMonthlyFee
,    NULL AS OemApiMonthlyFeeTax
,    NULL AS OemCreditNoticeMonthlyFee
,    NULL AS OemCreditNoticeMonthlyFeeTax
,    NULL AS OemNCreditNoticeMonthlyFee
,    NULL AS OemNCreditNoticeMonthlyFeeTax
,    CASE WHEN mc1.Class1 = 0 THEN NULL
          ELSE
              (CASE WHEN IFNULL(osf.SettlementFee, 0) < 0
                  THEN (IFNULL((osf.SettlementFee * -1) - FLOOR((IFNULL(osf.SettlementFee, 0) * -1) * :tax / (100 + :tax)), 0)) * -1
                  ELSE IFNULL(osf.SettlementFee - FLOOR(IFNULL(osf.SettlementFee, 0) * :tax / (100 + :tax)), 0)
              END
              +
              CASE WHEN IFNULL(ocf.ClaimFee, 0) < 0
                  THEN (IFNULL((ocf.ClaimFee * -1) - FLOOR((IFNULL(ocf.ClaimFee, 0) * -1) * :tax / (100 + :tax)), 0)) * -1
                  ELSE IFNULL(ocf.ClaimFee - FLOOR(IFNULL(ocf.ClaimFee, 0) * :tax / (100 + :tax)), 0)
              END)
     END AS OemTotalSales
FROM  T_PayingAndSales pas
      INNER JOIN AT_PayingAndSales apas ON (apas.Seq = pas.Seq)
      INNER JOIN T_Order o ON (o.OrderSeq = pas.OrderSeq)
      INNER JOIN M_Code mc1 ON (mc1.CodeId = 160 AND mc1.KeyCode = IFNULL(o.OemId, 0))
      INNER JOIN T_Enterprise e ON (e.EnterpriseId = o.EnterpriseId)
      INNER JOIN M_Code mc2 ON (mc2.CodeId = 159 AND mc2.KeyCode = IFNULL(o.OutOfAmends, 0))
      INNER JOIN T_OrderSummary os ON (os.OrderSeq = pas.OrderSeq)
      INNER JOIN T_Customer c ON (c.OrderSeq = o.OrderSeq)
      INNER JOIN T_EnterpriseCustomer ec ON (ec.EntCustSeq = c.EntCustSeq)
      INNER JOIN T_ManagementCustomer mc ON (mc.ManCustId = ec.ManCustId)
      LEFT OUTER JOIN T_ClaimControl cc ON (cc.OrderSeq = o.P_OrderSeq)
      LEFT OUTER JOIN T_Cancel can ON (can.OrderSeq = o.OrderSeq AND can.ValidFlg = 1)
      LEFT OUTER JOIN T_ReceiptControl rc ON (rc.ReceiptSeq = cc.LastReceiptSeq)
      LEFT OUTER JOIN T_PayingControl pc ON (pc.Seq = pas.PayingControlSeq)
      LEFT OUTER JOIN T_OemSettlementFee osf ON (osf.OrderSeq = o.OrderSeq)
      LEFT OUTER JOIN T_OemClaimFee ocf ON (ocf.OrderSeq = o.OrderSeq)
WHERE
-- 月跨ぎ(着荷)
(      apas.ATUriType IN (1, 3)
   AND apas.Deli_ConfirmArrivalInputDate >= :BefAccountingDate AND apas.Deli_ConfirmArrivalInputDate < DATE_FORMAT(:AccountingMonth + INTERVAL 1 MONTH, '%Y-%m-%d 00:00:00')
   AND apas.ATUriDay >= DATE_FORMAT(:AccountingMonth - INTERVAL 1 MONTH, '%Y%m%d') AND apas.ATUriDay < DATE_FORMAT(:AccountingMonth, '%Y%m%d')
)
UNION ALL
SELECT
     mc1.Class3 AS OemId
,    mc1.Class2 AS OemNameKj
,    o.EnterpriseId AS EnterpriseId
,    e.EnterpriseNameKj AS EnterpriseNameKj
,    o.OrderId AS OrderId
,    mc2.Class1 AS OutOfAmends
,    F_GetSalesDefiniteConditions(o.OrderSeq) AS SalesDefiniteConditions
,    DATE(apas.ATUriDay) AS SalesDefiniteDate
,    pc.FixedDate AS FixedDate
,    pc.ExecScheduleDate AS ExecScheduleDate
,    CASE WHEN apas.ATUriType = 1 THEN DATE(apas.Deli_ConfirmArrivalInputDate) -- 着荷
          WHEN apas.ATUriType = 2 THEN -- 入金
                (
                    SELECT DATE(MIN(tmprc.ReceiptProcessDate))
                    FROM T_ClaimControl tmpcc
                        ,T_ReceiptControl tmprc
                    WHERE tmpcc.OrderSeq = tmprc.OrderSeq
                    AND tmpcc.OrderSeq = o.P_OrderSeq
                    AND tmpcc.MinClaimAmount - (SELECT SUM(ReceiptAmount) FROM T_ReceiptControl WHERE OrderSeq = tmpcc.OrderSeq AND ReceiptSeq <= tmprc.ReceiptSeq) <= 0
                    AND tmprc.ReceiptSeq > IFNULL((SELECT MAX(sub1.ReceiptSeq) FROM T_ReceiptControl sub1,AT_ReceiptControl sub2 WHERE sub1.ReceiptSeq = sub2.ReceiptSeq AND sub1.OrderSeq = tmprc.OrderSeq AND Rct_CancelFlg = 1), 0)
                )
          WHEN apas.ATUriType = 3 THEN pas.ClearConditionDate -- 役務
     END AS ProcessingDate
,    CASE WHEN DATE_FORMAT(apas.ATUriDay, '%Y%m') < DATE_FORMAT(:AccountingMonth, '%Y%m') THEN :AccountingMonth
          ELSE DATE_FORMAT(apas.ATUriDay, '%Y-%m-01')
     END AS AccountDate
,    os.Deli_JournalNumber AS JournalNumber
,    mc.ManCustId AS ManCustId
,    mc.NameKj AS ManCusNameKj
,    pas.UseAmount AS UseAmountTotal
,    CASE WHEN mc1.Class1 = 0 THEN pas.AppSettlementFeeRate
          ELSE NULL
     END AS SettlementFeeRate
,    CASE WHEN mc1.Class1 = 0 THEN pas.SettlementFee
          ELSE NULL
     END AS SettlementFee
,    CASE WHEN mc1.Class1 = 0 THEN
         CASE WHEN pas.ClaimFee < 0 THEN ((pas.ClaimFee * -1) - FLOOR((IFNULL(pas.ClaimFee, 0) * -1) * :tax / (100 + :tax))) * -1
              ELSE pas.ClaimFee - FLOOR(IFNULL(pas.ClaimFee, 0) * :tax / (100 + :tax))
         END
         ELSE NULL
     END AS ClaimFee
,    CASE WHEN mc1.Class1 = 0 THEN
         CASE WHEN pas.ClaimFee < 0 THEN (FLOOR((IFNULL(pas.ClaimFee, 0) * -1) * :tax / (100 + :tax))) * -1
              ELSE FLOOR(IFNULL(pas.ClaimFee, 0) * :tax / (100 + :tax))
         END
         ELSE NULL
     END AS ClaimFeeTax
,    NULL AS MonthlyFee
,    NULL AS MonthlyFeeTax
,    NULL AS IncludeMonthlyFee
,    NULL AS IncludeMonthlyFeeTax
,    NULL AS ApiMonthlyFee
,    NULL AS ApiMonthlyFeeTax
,    NULL AS CreditNoticeMonthlyFee
,    NULL AS CreditNoticeMonthlyFeeTax
,    NULL AS NCreditNoticeMonthlyFee
,    NULL AS NCreditNoticeMonthlyFeeTax
,    CASE WHEN mc1.Class1 = 0 THEN
         CASE WHEN pas.ClaimFee < 0 THEN IFNULL(pas.SettlementFee, 0) + (IFNULL(((pas.ClaimFee * -1) - FLOOR((IFNULL(pas.ClaimFee, 0) * -1) * :tax / (100 + :tax))), 0)) * -1
              ELSE IFNULL(pas.SettlementFee, 0) + IFNULL(pas.ClaimFee - FLOOR(IFNULL(pas.ClaimFee, 0) * :tax / (100 + :tax)), 0)
         END
         ELSE NULL
     END AS TotalSales
,    CASE WHEN mc1.Class1 = 0 THEN NULL
          ELSE TRUNCATE( osf.AppSettlementFeeRate * 100 / (100 + :tax) , 5)
     END AS OemSettlementFeeRate
,    CASE WHEN mc1.Class1 = 0 THEN NULL
          ELSE
              CASE WHEN IFNULL(osf.SettlementFee, 0) < 0 THEN ((osf.SettlementFee * -1) - FLOOR((IFNULL(osf.SettlementFee, 0) * -1) * :tax / (100 + :tax))) * -1
                   ELSE osf.SettlementFee - FLOOR(IFNULL(osf.SettlementFee, 0) * :tax / (100 + :tax))
              END
     END AS OemSettlementFee
,    CASE WHEN mc1.Class1 = 0 THEN NULL
          ELSE
              CASE WHEN IFNULL(osf.SettlementFee, 0) < 0 THEN (FLOOR((IFNULL(osf.SettlementFee, 0) * -1) * :tax / (100 + :tax))) * -1
                   ELSE FLOOR(IFNULL(osf.SettlementFee, 0) * :tax / (100 + :tax))
              END
     END AS OemSettlementFeeTax
,    CASE WHEN mc1.Class1 = 0 THEN NULL
          ELSE
              CASE WHEN IFNULL(ocf.ClaimFee, 0) < 0 THEN ((ocf.ClaimFee * -1) - FLOOR((IFNULL(ocf.ClaimFee, 0) * -1) * :tax / (100 + :tax))) * -1
                   ELSE ocf.ClaimFee - FLOOR(IFNULL(ocf.ClaimFee, 0) * :tax / (100 + :tax))
              END
     END AS OemClaimFee
,    CASE WHEN mc1.Class1 = 0 THEN NULL
          ELSE
              CASE WHEN IFNULL(ocf.ClaimFee, 0) < 0 THEN (FLOOR((IFNULL(ocf.ClaimFee, 0) * -1) * :tax / (100 + :tax))) * -1
                   ELSE FLOOR(IFNULL(ocf.ClaimFee, 0) * :tax / (100 + :tax))
              END
     END AS OemClaimFeeTax
,    NULL AS OemMonthlyFee
,    NULL AS OemMonthlyFeeTax
,    NULL AS OemIncludeMonthlyFee
,    NULL AS OemIncludeMonthlyFeeTax
,    NULL AS OemApiMonthlyFee
,    NULL AS OemApiMonthlyFeeTax
,    NULL AS OemCreditNoticeMonthlyFee
,    NULL AS OemCreditNoticeMonthlyFeeTax
,    NULL AS OemNCreditNoticeMonthlyFee
,    NULL AS OemNCreditNoticeMonthlyFeeTax
,    CASE WHEN mc1.Class1 = 0 THEN NULL
          ELSE
              (CASE WHEN IFNULL(osf.SettlementFee, 0) < 0
                  THEN (IFNULL((osf.SettlementFee * -1) - FLOOR((IFNULL(osf.SettlementFee, 0) * -1) * :tax / (100 + :tax)), 0)) * -1
                  ELSE IFNULL(osf.SettlementFee - FLOOR(IFNULL(osf.SettlementFee, 0) * :tax / (100 + :tax)), 0)
              END
              +
              CASE WHEN IFNULL(ocf.ClaimFee, 0) < 0
                  THEN (IFNULL((ocf.ClaimFee * -1) - FLOOR((IFNULL(ocf.ClaimFee, 0) * -1) * :tax / (100 + :tax)), 0)) * -1
                  ELSE IFNULL(ocf.ClaimFee - FLOOR(IFNULL(ocf.ClaimFee, 0) * :tax / (100 + :tax)), 0)
              END)
     END AS OemTotalSales
FROM  T_PayingAndSales pas
      INNER JOIN AT_PayingAndSales apas ON (apas.Seq = pas.Seq)
      INNER JOIN T_Order o ON (o.OrderSeq = pas.OrderSeq)
      INNER JOIN M_Code mc1 ON (mc1.CodeId = 160 AND mc1.KeyCode = IFNULL(o.OemId, 0))
      INNER JOIN T_Enterprise e ON (e.EnterpriseId = o.EnterpriseId)
      INNER JOIN M_Code mc2 ON (mc2.CodeId = 159 AND mc2.KeyCode = IFNULL(o.OutOfAmends, 0))
      INNER JOIN T_OrderSummary os ON (os.OrderSeq = pas.OrderSeq)
      INNER JOIN T_Customer c ON (c.OrderSeq = o.OrderSeq)
      INNER JOIN T_EnterpriseCustomer ec ON (ec.EntCustSeq = c.EntCustSeq)
      INNER JOIN T_ManagementCustomer mc ON (mc.ManCustId = ec.ManCustId)
      LEFT OUTER JOIN T_ClaimControl cc ON (cc.OrderSeq = o.P_OrderSeq)
      LEFT OUTER JOIN T_Cancel can ON (can.OrderSeq = o.OrderSeq AND can.ValidFlg = 1)
      LEFT OUTER JOIN T_ReceiptControl rc ON (rc.ReceiptSeq = cc.LastReceiptSeq)
      LEFT OUTER JOIN T_PayingControl pc ON (pc.Seq = pas.PayingControlSeq)
      LEFT OUTER JOIN T_OemSettlementFee osf ON (osf.OrderSeq = o.OrderSeq)
      LEFT OUTER JOIN T_OemClaimFee ocf ON (ocf.OrderSeq = o.OrderSeq)
      LEFT OUTER JOIN V_CloseReceiptControl vcrc ON (vcrc.OrderSeq = cc.OrderSeq)
WHERE
-- 月跨ぎ(入金)
(      apas.ATUriType = 2
   AND vcrc.ReceiptProcessDate >= :BefAccountingDate AND vcrc.ReceiptProcessDate < DATE_FORMAT(:AccountingMonth + INTERVAL 1 MONTH, '%Y-%m-%d 00:00:00')
   AND apas.ATUriDay < DATE_FORMAT(:AccountingMonth, '%Y%m%d')
)
EOQ;
        // (キャンセル分)
        $sql2 = <<<EOQ
SELECT
     mc1.Class3 AS OemId
,    mc1.Class2 AS OemNameKj
,    o.EnterpriseId AS EnterpriseId
,    e.EnterpriseNameKj AS EnterpriseNameKj
,    o.OrderId AS OrderId
,    mc2.Class1 AS OutOfAmends
,    'キャンセル日' AS SalesDefiniteConditions
,    DATE(can.ApprovalDate) AS SalesDefiniteDate
,    pc.FixedDate AS FixedDate
,    pc.ExecScheduleDate AS ExecScheduleDate
,    DATE(can.ApprovalDate) AS ProcessingDate
,    CASE WHEN DATE_FORMAT(can.ApprovalDate, '%Y%m') < DATE_FORMAT(:AccountingMonth, '%Y%m') THEN :AccountingMonth
          ELSE DATE_FORMAT(can.ApprovalDate, '%Y-%m-01')
     END AS AccountDate
,    os.Deli_JournalNumber AS JournalNumber
,    mc.ManCustId AS ManCustId
,    mc.NameKj AS ManCusNameKj
,    pas.UseAmount * -1 AS UseAmountTotal
,    CASE WHEN mc1.Class1 = 0 THEN pas.AppSettlementFeeRate
          ELSE NULL
     END AS SettlementFeeRate
,    CASE WHEN mc1.Class1 = 0 THEN IFNULL(pas.SettlementFee, 0) * -1
          ELSE NULL
     END AS SettlementFee
,    CASE WHEN mc1.Class1 = 0 THEN
         CASE WHEN pas.ClaimFee < 0 THEN (((pas.ClaimFee * -1) - FLOOR((IFNULL(pas.ClaimFee, 0) * -1) * :tax / (100 + :tax))) * -1) * -1
              ELSE (pas.ClaimFee - FLOOR(IFNULL(pas.ClaimFee, 0) * :tax / (100 + :tax))) * -1
         END
         ELSE NULL
     END AS ClaimFee
,    CASE WHEN mc1.Class1 = 0 THEN
         CASE WHEN pas.ClaimFee < 0 THEN (FLOOR((IFNULL(pas.ClaimFee, 0) * -1) * :tax / (100 + :tax)) * -1) * -1
              ELSE FLOOR(IFNULL(pas.ClaimFee, 0) * :tax / (100 + :tax)) * -1
         END
         ELSE NULL
     END AS ClaimFeeTax
,    NULL AS MonthlyFee
,    NULL AS MonthlyFeeTax
,    NULL AS IncludeMonthlyFee
,    NULL AS IncludeMonthlyFeeTax
,    NULL AS ApiMonthlyFee
,    NULL AS ApiMonthlyFeeTax
,    NULL AS CreditNoticeMonthlyFee
,    NULL AS CreditNoticeMonthlyFeeTax
,    NULL AS NCreditNoticeMonthlyFee
,    NULL AS NCreditNoticeMonthlyFeeTax
,    CASE WHEN mc1.Class1 = 0 THEN
        (CASE WHEN pas.ClaimFee < 0
            THEN (IFNULL(pas.SettlementFee, 0) +
                  IFNULL((pas.ClaimFee * -1) - FLOOR((IFNULL(pas.ClaimFee, 0) * -1) * :tax / (100 + :tax)), 0)) * -1
            ELSE IFNULL(pas.SettlementFee, 0) +
                 IFNULL(pas.ClaimFee - FLOOR(IFNULL(pas.ClaimFee, 0) * :tax / (100 + :tax)), 0)
         END) * -1
        ELSE NULL
     END AS TotalSales
,    CASE WHEN mc1.Class1 = 0 THEN NULL
          ELSE TRUNCATE( osf.AppSettlementFeeRate * 100 / (100 + :tax) , 5)
     END AS OemSettlementFeeRate
,    CASE WHEN mc1.Class1 = 0 THEN NULL
          ELSE
              CASE WHEN IFNULL(osf.SettlementFee, 0) < 0 THEN (((osf.SettlementFee * -1) - FLOOR((IFNULL(osf.SettlementFee, 0) * -1) * :tax / (100 + :tax))) * -1) * -1
                   ELSE (osf.SettlementFee - FLOOR(IFNULL(osf.SettlementFee, 0) * :tax / (100 + :tax))) * -1
              END
     END AS OemSettlementFee
,    CASE WHEN mc1.Class1 = 0 THEN NULL
          ELSE
              CASE WHEN IFNULL(osf.SettlementFee, 0) < 0 THEN (FLOOR((IFNULL(osf.SettlementFee, 0) * -1) * :tax / (100 + :tax)) * -1) * -1
                   ELSE FLOOR(IFNULL(osf.SettlementFee, 0) * :tax / (100 + :tax)) * -1
              END
     END AS OemSettlementFeeTax
,    CASE WHEN mc1.Class1 = 0 THEN NULL
          ELSE
              CASE WHEN IFNULL(ocf.ClaimFee, 0) < 0 THEN (((ocf.ClaimFee * -1) - FLOOR((IFNULL(ocf.ClaimFee, 0) * -1) * :tax / (100 + :tax))) * -1) * -1
                   ELSE (ocf.ClaimFee - FLOOR(IFNULL(ocf.ClaimFee, 0) * :tax / (100 + :tax))) * -1
              END
     END AS OemClaimFee
,    CASE WHEN mc1.Class1 = 0 THEN NULL
          ELSE
              CASE WHEN IFNULL(ocf.ClaimFee, 0) < 0 THEN (FLOOR((IFNULL(ocf.ClaimFee, 0) * -1) * :tax / (100 + :tax)) * -1) * -1
                   ELSE FLOOR(IFNULL(ocf.ClaimFee, 0) * :tax / (100 + :tax)) * -1
              END
     END AS OemClaimFeeTax
,    NULL AS OemMonthlyFee
,    NULL AS OemMonthlyFeeTax
,    NULL AS OemIncludeMonthlyFee
,    NULL AS OemIncludeMonthlyFeeTax
,    NULL AS OemApiMonthlyFee
,    NULL AS OemApiMonthlyFeeTax
,    NULL AS OemCreditNoticeMonthlyFee
,    NULL AS OemCreditNoticeMonthlyFeeTax
,    NULL AS OemNCreditNoticeMonthlyFee
,    NULL AS OemNCreditNoticeMonthlyFeeTax
,    CASE WHEN mc1.Class1 = 0 THEN NULL
          ELSE

              (CASE WHEN IFNULL(osf.SettlementFee, 0) < 0
                  THEN (IFNULL((osf.SettlementFee * -1) - FLOOR((IFNULL(osf.SettlementFee, 0) * -1) * :tax / (100 + :tax)), 0)) * -1
                  ELSE IFNULL(osf.SettlementFee - FLOOR(IFNULL(osf.SettlementFee, 0) * :tax / (100 + :tax)), 0)
               END
               +
               CASE WHEN IFNULL(ocf.ClaimFee, 0) < 0
                  THEN (IFNULL((ocf.ClaimFee * -1) - FLOOR((IFNULL(ocf.ClaimFee, 0) * -1) * :tax / (100 + :tax)), 0)) * -1
                  ELSE IFNULL(ocf.ClaimFee - FLOOR(IFNULL(ocf.ClaimFee, 0) * :tax / (100 + :tax)), 0)
               END) * -1
     END AS OemTotalSales
FROM  T_PayingAndSales pas
      INNER JOIN AT_PayingAndSales apas ON (apas.Seq = pas.Seq)
      INNER JOIN T_Order o ON (o.OrderSeq = pas.OrderSeq)
      INNER JOIN M_Code mc1 ON (mc1.CodeId = 160 AND mc1.KeyCode = IFNULL(o.OemId, 0))
      INNER JOIN T_Enterprise e ON (e.EnterpriseId = o.EnterpriseId)
      INNER JOIN M_Code mc2 ON (mc2.CodeId = 159 AND mc2.KeyCode = IFNULL(o.OutOfAmends, 0))
      INNER JOIN T_OrderSummary os ON (os.OrderSeq = pas.OrderSeq)
      INNER JOIN T_Customer c ON (c.OrderSeq = o.OrderSeq)
      INNER JOIN T_EnterpriseCustomer ec ON (ec.EntCustSeq = c.EntCustSeq)
      INNER JOIN T_ManagementCustomer mc ON (mc.ManCustId = ec.ManCustId)
      INNER JOIN T_Cancel can ON (can.OrderSeq = o.OrderSeq AND can.ValidFlg = 1)
      LEFT OUTER JOIN T_ClaimControl cc ON (cc.OrderSeq = o.P_OrderSeq)
      LEFT OUTER JOIN T_ReceiptControl rc ON (rc.ReceiptSeq = cc.LastReceiptSeq)
      LEFT OUTER JOIN T_PayingControl pc ON (pc.Seq = pas.PayingControlSeq)
      LEFT OUTER JOIN T_OemSettlementFee osf ON (osf.OrderSeq = o.OrderSeq)
      LEFT OUTER JOIN T_OemClaimFee ocf ON (ocf.OrderSeq = o.OrderSeq)
WHERE apas.ATUriDay < DATE_FORMAT(:AccountingMonth, '%Y%m%d')
AND   (can.ApprovalDate >= :AccountingMonth AND can.ApprovalDate < (:AccountingMonth + INTERVAL 1 MONTH))
EOQ;
        }
        // (月額固定費分)
        $sql3 = <<<EOQ
SELECT
     OemId
,    OemNameKj
,    EnterpriseId
,    EnterpriseNameKj
,    OrderId
,    OutOfAmends
,    SalesDefiniteConditions
,    SalesDefiniteDate
,    FixedDate
,    ExecScheduleDate
,    ProcessingDate
,    AccountDate
,    JournalNumber
,    ManCustId
,    ManCusNameKj
,    UseAmountTotal
,    SettlementFeeRate
,    SettlementFee
,    ClaimFee
,    ClaimFeeTax
,    MonthlyFee
,    MonthlyFeeTax
,    IncludeMonthlyFee
,    IncludeMonthlyFeeTax
,    ApiMonthlyFee
,    ApiMonthlyFeeTax
,    CreditNoticeMonthlyFee
,    CreditNoticeMonthlyFeeTax
,    NCreditNoticeMonthlyFee
,    NCreditNoticeMonthlyFeeTax
,    TotalSales
,    OemSettlementFeeRate
,    OemSettlementFee
,    OemSettlementFeeTax
,    OemClaimFee
,    OemClaimFeeTax
,    OemMonthlyFee
,    OemMonthlyFeeTax
,    OemIncludeMonthlyFee
,    OemIncludeMonthlyFeeTax
,    OemApiMonthlyFee
,    OemApiMonthlyFeeTax
,    OemCreditNoticeMonthlyFee
,    OemCreditNoticeMonthlyFeeTax
,    OemNCreditNoticeMonthlyFee
,    OemNCreditNoticeMonthlyFeeTax
,    OemTotalSales
FROM  AT_Daily_SalesDetails
WHERE AccountDate = :AccountingMonth
AND   SalesDefiniteConditions = '月額固定費'
EOQ;

        $sql = $sql1 . " UNION ALL " .
        $sql2 . " UNION ALL " .
        $sql3 . " ORDER BY IFNULL(OemId, 9999999), EnterpriseId, OrderId ";

        $ri = $this->app->dbAdapter->query($sql)->execute($prm);
        $datas = ResultInterfaceToArray($ri);

        $templateId = 'CKI24174_05'; // 売上明細
        $templateClass = 0;
        $seq = 0;
        $templatePattern = 0;
        $tmpFileName = $tmpFilePath . '05.売上明細_' . $formatNowStr . '.csv';

        $logicTemplate = new LogicTemplate($this->app->dbAdapter);
        $result = $logicTemplate->convertArraytoFile( $datas, $tmpFileName, $templateId, $templateClass, $seq, $templatePattern );

        //-----------------------------------------------------------
        // [01.直営日次統計表(月次)][02.OEM日次統計表(月次)]用の処理
        //-----------------------------------------------------------
        $sumsql = <<<EOQ
SELECT
     w.OemId
,    w.EnterpriseId
,    COUNT(w.OemId) AS CNTALL_OemId
     /* 以下、売上確定条件による分岐が必要な項目 */
,    /* (当月累計精算用) */SUM(CASE WHEN w.SalesDefiniteConditions IN ('キャンセル日', '月額固定費') THEN 0 ELSE 1 END) AS CNT_OemId
,    /* (当月累計精算用) */SUM(CASE WHEN w.SalesDefiniteConditions IN ('キャンセル日', '月額固定費') THEN 0 ELSE IFNULL(w.UseAmountTotal,0) END) AS SUM_UseAmount
,    /* (当月累計精算用) */SUM(CASE WHEN w.SalesDefiniteConditions = 'キャンセル日' THEN 1 ELSE 0 END) AS CNCLCNT_OemId
,    /* (当月累計精算用) */SUM(CASE WHEN w.SalesDefiniteConditions = 'キャンセル日' THEN IFNULL(w.UseAmountTotal,0) ELSE 0 END) AS CNCLSUM_UseAmount
,    SUM(CASE WHEN w.SalesDefiniteConditions = 'キャンセル日' THEN 0 ELSE (CASE WHEN w.OemId = 0 THEN IFNULL(w.SettlementFee,0) ELSE IFNULL(w.OemSettlementFee,0) END) END) AS SUM_SettlementFee
,    SUM(CASE WHEN w.SalesDefiniteConditions = 'キャンセル日' THEN (CASE WHEN w.OemId = 0 THEN IFNULL(w.SettlementFee,0) ELSE IFNULL(w.OemSettlementFee,0) END) ELSE 0 END) AS SUM_CNCLSettlementFee
,    SUM(CASE WHEN w.SalesDefiniteConditions = 'キャンセル日' THEN 0 ELSE (CASE WHEN w.OemId = 0 THEN IFNULL(w.ClaimFee,0) ELSE IFNULL(w.OemClaimFee,0) END) END) AS SUM_ClaimFee
,    SUM(CASE WHEN w.SalesDefiniteConditions = 'キャンセル日' THEN (CASE WHEN w.OemId = 0 THEN IFNULL(w.ClaimFee,0) ELSE IFNULL(w.OemClaimFee,0) END) ELSE 0 END) AS SUM_CNCLClaimFee
,    SUM(CASE WHEN w.SalesDefiniteConditions = 'キャンセル日' THEN 0 ELSE (CASE WHEN w.OemId = 0 THEN 0 ELSE IFNULL(w.OemSettlementFeeTax,0) END) END) AS SUM_SettlementFeeTax
,    SUM(CASE WHEN w.SalesDefiniteConditions = 'キャンセル日' THEN (CASE WHEN w.OemId = 0 THEN 0 ELSE IFNULL(w.OemSettlementFeeTax,0) END) ELSE 0 END) AS SUM_CNCLSettlementFeeTax
,    SUM(CASE WHEN w.SalesDefiniteConditions = 'キャンセル日' THEN 0 ELSE (CASE WHEN w.OemId = 0 THEN IFNULL(w.ClaimFeeTax,0) ELSE IFNULL(w.OemClaimFeeTax,0) END) END) AS SUM_ClaimFeeTax
,    SUM(CASE WHEN w.SalesDefiniteConditions = 'キャンセル日' THEN (CASE WHEN w.OemId = 0 THEN IFNULL(w.ClaimFeeTax,0) ELSE IFNULL(w.OemClaimFeeTax,0) END) ELSE 0 END) AS SUM_CNCLClaimFeeTax
     /* 以下、売上確定条件による分岐が不要な項目 */
,    SUM(CASE WHEN w.OemId = 0 THEN IFNULL(w.MonthlyFee,0) ELSE IFNULL(w.OemMonthlyFee,0) END) AS SUM_MonthlyFee
,    SUM(CASE WHEN w.OemId = 0 THEN IFNULL(w.MonthlyFeeTax,0) ELSE IFNULL(w.OemMonthlyFeeTax,0) END) AS SUM_MonthlyFeeTax
,    SUM(CASE WHEN w.OemId = 0 THEN IFNULL(w.IncludeMonthlyFee,0) ELSE IFNULL(w.OemIncludeMonthlyFee,0) END) AS SUM_IncludeMonthlyFee
,    SUM(CASE WHEN w.OemId = 0 THEN IFNULL(w.IncludeMonthlyFeeTax,0) ELSE IFNULL(w.OemIncludeMonthlyFeeTax,0) END) AS SUM_IncludeMonthlyFeeTax
,    SUM(CASE WHEN w.OemId = 0 THEN IFNULL(w.ApiMonthlyFee,0) ELSE IFNULL(w.OemApiMonthlyFee,0) END) AS SUM_ApiMonthlyFee
,    SUM(CASE WHEN w.OemId = 0 THEN IFNULL(w.ApiMonthlyFeeTax,0) ELSE IFNULL(w.OemApiMonthlyFeeTax,0) END) AS SUM_ApiMonthlyFeeTax
,    SUM(CASE WHEN w.OemId = 0 THEN IFNULL(w.CreditNoticeMonthlyFee,0) ELSE IFNULL(w.OemCreditNoticeMonthlyFee,0) END) AS SUM_CreditNoticeMonthlyFee
,    SUM(CASE WHEN w.OemId = 0 THEN IFNULL(w.CreditNoticeMonthlyFeeTax,0) ELSE IFNULL(w.OemCreditNoticeMonthlyFeeTax,0) END) AS SUM_CreditNoticeMonthlyFeeTax
,    SUM(CASE WHEN w.OemId = 0 THEN IFNULL(w.NCreditNoticeMonthlyFee,0) ELSE IFNULL(w.OemNCreditNoticeMonthlyFee,0) END) AS SUM_NCreditNoticeMonthlyFee
,    SUM(CASE WHEN w.OemId = 0 THEN IFNULL(w.NCreditNoticeMonthlyFeeTax,0) ELSE IFNULL(w.OemNCreditNoticeMonthlyFeeTax,0) END) AS SUM_NCreditNoticeMonthlyFeeTax
FROM (
EOQ;
        $sumsql .= $sql1;
        $sumsql .= " UNION ALL ";
        $sumsql .= $sql2;
        $sumsql .= " UNION ALL ";
        $sumsql .= $sql3;
        $sumsql .= " ) w GROUP BY w.OemId, w.EnterpriseId WITH ROLLUP ";

        $ri = $this->app->dbAdapter->query($sumsql)->execute($prm);

        foreach ($ri as $row) {
            if ($row['OemId'] != null && $row['EnterpriseId'] == null) {
                // OEM別合計
                if ($row['OemId'] > 0) { $this->_ary02[5][$row['OemId']] = $row; }
                else                   { $this->_ary01[5] = $row; }

            } elseif ($row['EnterpriseId'] != null) {
                // 加盟店別合計
                if ($row['OemId'] > 0) { $this->_aryEnt02[5][$row['EnterpriseId']] = $row; }
                else                   { $this->_aryEnt01[5][$row['EnterpriseId']] = $row; }

            }
        }

        return $tmpFileName;
    }

    /**
     * CSV出力を行う[18. OEM移管明細(CSV)](月次版)
     *
     * @param string $formatNowStr 書式化年月日
     * @param string $tmpFilePath TEMP領域
     * @return ファイル名 ※出力が行われなかった場合は''を戻す
     */
    protected function CsvOEMIkanMeisaiMonth($formatNowStr, $tmpFilePath)
    {
        $mdlsp = new \models\Table\TableSystemProperty($this->app->dbAdapter);

        // 業務日付と会計月の取得
        $businessDate = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'BusinessDate');
        $accountingMonth = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'AccountingMonth');

        $prm = array (':BusinessDate' => $businessDate, ':AccountingMonth' => $accountingMonth );

        $sql = <<<EOQ
SELECT
      c3.Class3 AS OemId
,     c3.Class2 AS OemNameKj
,     o.OemClaimTransDate AS OemTransferDate
,     cc.UseAmountTotal
      - IFNULL( ( SELECT SUM(CheckingUseAmount) FROM T_ReceiptControl WHERE cc.OrderSeq = OrderSeq AND DATE(ReceiptDate) < o.OemClaimTransDate), 0)
      + IFNULL( ( SELECT SUM(CheckingUseAmount) FROM T_RepaymentControl WHERE cc.ClaimId = ClaimId AND DATE(DecisionDate) < o.OemClaimTransDate), 0)
      - IFNULL( ( SELECT SUM(CheckingUseAmount) FROM T_SundryControl WHERE cc.OrderSeq = OrderSeq AND SundryType = 1 AND DATE(ProcessDate) < o.OemClaimTransDate), 0)
      + IFNULL( ( SELECT SUM(CheckingUseAmount) FROM T_SundryControl WHERE cc.OrderSeq = OrderSeq AND SundryType = 0 AND DATE(ProcessDate) < o.OemClaimTransDate), 0)
      AS ReceivablesTransferredAmount
,     o.OrderId AS OrderId
,     c2.Class1 AS OutOfAmends
,     F_GetSalesDefiniteConditions(o.OrderSeq) AS SalesDefiniteConditions
,     DATE(apas.ATUriDay) AS SalesDefiniteDate
,     o.EnterpriseId AS EnterpriseId
,     e.EnterpriseNameKj AS EnterpriseNameKj
,     mc.ManCustId AS ManCustId
,     mc.NameKj AS ManCusNameKj
,     cc.F_LimitDate AS F_LimitDate
,     cc.F_ClaimAmount AS F_ClaimAmount
,     rc.ReceiptDate AS FinalReceiptDate
,     CASE WHEN rc.ReceiptDate IS NULL THEN NULL ELSE DATEDIFF(:BusinessDate, rc.ReceiptDate) END AS AfterTheFinalPaymentDays
,     c1.Class3 AS OverdueClassification
FROM  T_PayingAndSales pas
      INNER JOIN AT_PayingAndSales apas ON (pas.Seq = apas.Seq)
      INNER JOIN T_Order o ON (pas.OrderSeq = o.OrderSeq)
      INNER JOIN T_Customer c ON (o.OrderSeq = c.OrderSeq)
      INNER JOIN T_EnterpriseCustomer ec ON (c.EntCustSeq = ec.EntCustSeq)
      INNER JOIN T_ManagementCustomer mc ON (ec.ManCustId = mc.ManCustId)
      INNER JOIN T_Enterprise e ON (o.EnterpriseId = e.EnterpriseId)
      LEFT OUTER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
      LEFT OUTER JOIN M_Code c2 ON (c2.CodeId = 159 AND c2.KeyCode = IFNULL(o.OutOfAmends, 0))
      INNER JOIN M_Code c3 ON (c3.CodeId = 160 AND c3.KeyCode = IFNULL( o.OemId, 0 ))
      INNER JOIN T_ClaimControl cc ON (o.OrderSeq = cc.OrderSeq)
      LEFT OUTER JOIN T_Cancel can ON (pas.OrderSeq = can.OrderSeq AND can.ValidFlg = 1)
      LEFT OUTER JOIN T_ReceiptControl rc ON (rc.ReceiptSeq = cc.LastReceiptSeq)
WHERE IFNULL(can.ApprovalDate, '2100-01-01') >= (:AccountingMonth + INTERVAL 1 MONTH)
AND   (c1.Class1 <> 0 AND (o.OemClaimTransDate >= :AccountingMonth AND o.OemClaimTransDate < (:AccountingMonth + INTERVAL 1 MONTH)))
UNION ALL
SELECT
      c3.Class3
,     c3.Class2
,     DATE(can.ApprovalDate)
,     cc.BalanceUseAmount
      + IFNULL( ( SELECT SUM(CheckingUseAmount) FROM T_ReceiptControl WHERE cc.OrderSeq = OrderSeq AND DATE(ReceiptDate) >= o.OemClaimTransDate), 0)
      - IFNULL( ( SELECT SUM(CheckingUseAmount) FROM T_RepaymentControl WHERE cc.ClaimId = ClaimId AND DATE(DecisionDate) >= o.OemClaimTransDate), 0)
      + IFNULL( ( SELECT SUM(CheckingUseAmount) FROM T_SundryControl WHERE cc.OrderSeq = OrderSeq AND SundryType = 1 AND DATE(ProcessDate) >= o.OemClaimTransDate), 0)
      - IFNULL( ( SELECT SUM(CheckingUseAmount) FROM T_SundryControl WHERE cc.OrderSeq = OrderSeq AND SundryType = 0 AND DATE(ProcessDate) >= o.OemClaimTransDate), 0)
      * -1 AS ReceivablesTransferredAmount
,     o.OrderId
,     c2.Class1
,     'キャンセル日'
,     DATE(can.ApprovalDate)
,     o.EnterpriseId
,     e.EnterpriseNameKj
,     mc.ManCustId
,     mc.NameKj
,     cc.F_LimitDate
,     cc.F_ClaimAmount
,     rc.ReceiptDate
,     CASE WHEN rc.ReceiptDate IS NULL THEN NULL ELSE DATEDIFF(:BusinessDate, rc.ReceiptDate) END AS AfterTheFinalPaymentDays
,     c1.Class3
FROM  T_PayingAndSales pas
      INNER JOIN AT_PayingAndSales apas ON (pas.Seq = apas.Seq)
      INNER JOIN T_Order o ON (pas.OrderSeq = o.OrderSeq)
      INNER JOIN T_Customer c ON (o.OrderSeq = c.OrderSeq)
      INNER JOIN T_EnterpriseCustomer ec ON (c.EntCustSeq = ec.EntCustSeq)
      INNER JOIN T_ManagementCustomer mc ON (ec.ManCustId = mc.ManCustId)
      INNER JOIN T_Enterprise e ON (o.EnterpriseId = e.EnterpriseId)
      LEFT OUTER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
      LEFT OUTER JOIN M_Code c2 ON (c2.CodeId = 159 AND c2.KeyCode = IFNULL(o.OutOfAmends, 0))
      INNER JOIN M_Code c3 ON (c3.CodeId = 160 AND c3.KeyCode = IFNULL( o.OemId, 0 ))
      INNER JOIN T_ClaimControl cc ON (pas.OrderSeq = cc.OrderSeq)
      INNER JOIN T_Cancel can ON (pas.OrderSeq = can.OrderSeq AND can.ValidFlg = 1)
      LEFT OUTER JOIN T_ReceiptControl rc ON (rc.ReceiptSeq = cc.LastReceiptSeq)
WHERE (IFNULL(can.ApprovalDate, '2100-01-01') >= :AccountingMonth AND IFNULL(can.ApprovalDate, '2100-01-01') < (:AccountingMonth + INTERVAL 1 MONTH))
AND   (c1.Class1 <> 0 AND o.OemClaimTransDate < :AccountingMonth)
EOQ;

        $ri = $this->app->dbAdapter->query($sql . " ORDER BY IFNULL(OemId, 9999999), EnterpriseId, OrderId ")->execute($prm);
        $datas = ResultInterfaceToArray($ri);

        $templateId = 'CKI24174_18'; // OEM移管明細
        $templateClass = 0;
        $seq = 0;
        $templatePattern = 0;
        $tmpFileName = $tmpFilePath . '18.OEM移管明細_' . $formatNowStr . '.csv';

        $logicTemplate = new LogicTemplate($this->app->dbAdapter);
        $result = $logicTemplate->convertArraytoFile( $datas, $tmpFileName, $templateId, $templateClass, $seq, $templatePattern );

        //-----------------------------------------------------------
        // [01.直営日次統計表(月次)][02.OEM日次統計表(月次)]用の処理
        //-----------------------------------------------------------
        $sumsql  = " SELECT w.OemId, w.EnterpriseId, COUNT(w.OemId) AS CNT_OemId, SUM(w.ReceivablesTransferredAmount) * -1 AS SUM_Amount FROM ( ";
        $sumsql .= $sql;
        $sumsql .= " )  w GROUP BY w.OemId , w.EnterpriseId WITH ROLLUP ";
        $ri = $this->app->dbAdapter->query($sumsql)->execute($prm);

        foreach ($ri as $row) {
            if ($row['OemId'] != null && $row['EnterpriseId'] == null) {
                // (02.OEM日次統計表(月次))
                $this->_ary02[18][$row['OemId']] = $row;
                // (01.直営日次統計表(月次))
                $this->_ary01[18]['CNT_OemId'] += $row['CNT_OemId'];
                $this->_ary01[18]['SUM_Amount'] += ($row['SUM_Amount'] * -1);   // 符号反転ｾｯﾄ

            } elseif ($row['EnterpriseId'] != null) {
                // (02.OEM日次統計表(月次))
                $this->_aryEnt02[18][$row['EnterpriseId']] = $row;
                // (01.直営日次統計表(月次))
                $this->_aryEnt01[18]['CNT_OemId'] += $row['CNT_OemId'];
                $this->_aryEnt01[18]['SUM_Amount'] += ($row['SUM_Amount'] * -1);   // 符号反転ｾｯﾄ

            }
        }

        return $tmpFileName;
    }

    /**
     * CSV出力を行う[16. 再発行手数料明細(CSV)](月次版)
     *
     * @param string $formatNowStr 書式化年月日
     * @param string $tmpFilePath TEMP領域
     * @return ファイル名 ※出力が行われなかった場合は''を戻す
     */
    protected function CsvSaihakkotesuryoMeisaiMonth($formatNowStr, $tmpFilePath)
    {
        $mdlsp = new \models\Table\TableSystemProperty($this->app->dbAdapter);

        // 業務日付と会計月の取得
        $businessDate = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'BusinessDate');
        $accountingMonth = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'AccountingMonth');

        $taxRate = $this->app->dbAdapter->query(" SELECT F_GetTaxRate(:BusinessDate) AS TaxRate ")->execute(
            array (':BusinessDate' => $businessDate))->current()['TaxRate'];

        $prm = array (':BusinessDate' => $businessDate, ':AccountingMonth' => $accountingMonth, ':TaxRate' => $taxRate );

        // 直営分SQL
        $sql1 = <<<EOQ
SELECT
      c6.Class3 AS OemId
,     c6.Class2 AS OemNameKj
,     mc.ManCustId
,     mc.NameKj
,     o.OrderId
,     c1.Class3 AS OverdueClassification
,     CASE WHEN rc.CheckingClaimFee < 0 THEN ((rc.CheckingClaimFee * -1) - FLOOR((rc.CheckingClaimFee * -1) * :TaxRate / (100 + :TaxRate))) * -1
           ELSE rc.CheckingClaimFee - FLOOR(rc.CheckingClaimFee * :TaxRate / (100 + :TaxRate))
      END AS Clm_L_ClaimFee
,     CASE WHEN rc.CheckingClaimFee < 0 THEN (FLOOR((rc.CheckingClaimFee * -1) * :TaxRate / (100 + :TaxRate))) * -1
           ELSE FLOOR(rc.CheckingClaimFee * :TaxRate / (100 + :TaxRate))
      END AS Clm_L_ClaimFeeTax
,     rc.CheckingDamageInterestAmount AS Clm_L_DamageInterestAmount
,     c5.Class1 AS OutOfAmends
,     F_GetSalesDefiniteConditions(o.OrderSeq) AS SalesDefiniteConditions
,     DATE(apas.ATUriDay) AS SalesDefiniteDate
,     o.OemClaimTransDate AS OemTransferDate
,     o.EnterpriseId
,     e.EnterpriseNameKj
,     cc.F_LimitDate
,     cc.F_ClaimAmount
,     cc.BalanceUseAmount AS ClaimAmount
,     rc.ReceiptDate AS FinalReceiptDate
,     DATEDIFF( :BusinessDate, rc.ReceiptDate ) AS AfterTheFinalPaymentDays
FROM  T_ReceiptControl rc
      INNER JOIN T_Order o ON (o.OrderSeq = rc.OrderSeq)
      INNER JOIN T_Customer c ON (c.OrderSeq = o.OrderSeq)
      INNER JOIN T_PayingAndSales pas ON (pas.OrderSeq = o.OrderSeq)
      INNER JOIN AT_PayingAndSales apas ON (apas.Seq = pas.Seq)
      INNER JOIN T_ClaimControl cc ON (cc.OrderSeq = o.OrderSeq)
      LEFT OUTER JOIN T_Enterprise e ON (o.EnterpriseId = e.EnterpriseId)
      LEFT OUTER JOIN T_EnterpriseCustomer ec ON (ec.EntCustSeq = c.EntCustSeq)
      LEFT OUTER JOIN T_ManagementCustomer mc ON (mc.ManCustId = ec.ManCustId)
      LEFT OUTER JOIN M_Code c1 ON (c1.CodeId = 12 AND c1.KeyCode = cc.ClaimPattern)
      LEFT OUTER JOIN M_Code c5 ON (c5.CodeId = 159 AND c5.KeyCode = IFNULL( o.OutOfAmends, 0 ))
      INNER JOIN M_Code c6 ON (c6.CodeId = 160 AND c6.KeyCode = IFNULL( o.OemId, 0 ))
WHERE ((c6.Class1 = 0 OR (c6.Class1 <> 0 AND DATE(o.OemClaimTransDate) <= DATE(rc.ReceiptDate)))
       AND (rc.ReceiptDate >= :AccountingMonth AND rc.ReceiptDate < (:AccountingMonth + INTERVAL 1 MONTH))
       AND (rc.CheckingDamageInterestAmount <> 0 OR rc.CheckingClaimFee <> 0)
      )
      OR
      (rc.ReceiptDate < :AccountingMonth
       AND rc.ReceiptProcessDate >= (SELECT MAX(tmp.BusinessDate + INTERVAL 1 DAY) FROM (SELECT BusinessDate FROM T_BusinessCalendar WHERE BusinessDate >= :AccountingMonth AND BusinessFlg = 1 LIMIT 3) tmp)
       AND (c6.Class1 = 0 OR (c6.Class1 <> 0 AND DATE(o.OemClaimTransDate) <= DATE(rc.ReceiptDate)))
      )
EOQ;

        // (02.OEM日次統計表(月次))
        $sql2 = <<<EOQ
SELECT
      c6.Class3 AS OemId
,     c6.Class2 AS OemNameKj
,     mc.ManCustId
,     mc.NameKj
,     o.OrderId
,     c1.Class3 AS OverdueClassification
,     CASE WHEN rc.CheckingClaimFee < 0 THEN ((rc.CheckingClaimFee * -1) - FLOOR((rc.CheckingClaimFee * -1) * :TaxRate / (100 + :TaxRate))) * -1
           ELSE rc.CheckingClaimFee - FLOOR(rc.CheckingClaimFee * :TaxRate / (100 + :TaxRate))
      END AS Clm_L_ClaimFee
,     CASE WHEN rc.CheckingClaimFee < 0 THEN (FLOOR((rc.CheckingClaimFee * -1) * :TaxRate / (100 + :TaxRate))) * -1
           ELSE FLOOR(rc.CheckingClaimFee * :TaxRate / (100 + :TaxRate))
      END AS Clm_L_ClaimFeeTax
,     rc.CheckingDamageInterestAmount AS Clm_L_DamageInterestAmount
,     c5.Class1 AS OutOfAmends
,     F_GetSalesDefiniteConditions(o.OrderSeq) AS SalesDefiniteConditions
,     DATE(apas.ATUriDay) AS SalesDefiniteDate
,     o.OemClaimTransDate AS OemTransferDate
,     o.EnterpriseId
,     e.EnterpriseNameKj
,     cc.F_LimitDate
,     cc.F_ClaimAmount
,     cc.BalanceUseAmount AS ClaimAmount
,     rc.ReceiptDate AS FinalReceiptDate
,     DATEDIFF( :BusinessDate, rc.ReceiptDate ) AS AfterTheFinalPaymentDays
FROM  T_ReceiptControl rc
      INNER JOIN T_Order o ON (o.OrderSeq = rc.OrderSeq)
      INNER JOIN T_Customer c ON (c.OrderSeq = o.OrderSeq)
      INNER JOIN T_PayingAndSales pas ON (pas.OrderSeq = o.OrderSeq)
      INNER JOIN AT_PayingAndSales apas ON (apas.Seq = pas.Seq)
      INNER JOIN T_ClaimControl cc ON (cc.OrderSeq = o.OrderSeq)
      LEFT OUTER JOIN T_Enterprise e ON (o.EnterpriseId = e.EnterpriseId)
      LEFT OUTER JOIN T_EnterpriseCustomer ec ON (ec.EntCustSeq = c.EntCustSeq)
      LEFT OUTER JOIN T_ManagementCustomer mc ON (mc.ManCustId = ec.ManCustId)
      LEFT OUTER JOIN M_Code c1 ON (c1.CodeId = 12 AND c1.KeyCode = cc.ClaimPattern)
      LEFT OUTER JOIN M_Code c5 ON (c5.CodeId = 159 AND c5.KeyCode = IFNULL( o.OutOfAmends, 0 ))
      INNER JOIN M_Code c6 ON (c6.CodeId = 160 AND c6.KeyCode = IFNULL( o.OemId, 0 ))
WHERE (((c6.Class1 <> 0 AND o.OemClaimTransDate IS NULL) OR (c6.Class1 <> 0 AND DATE(o.OemClaimTransDate) > DATE(rc.ReceiptDate)))
       AND (rc.ReceiptDate >= :AccountingMonth AND rc.ReceiptDate < (:AccountingMonth + INTERVAL 1 MONTH))
       AND (rc.CheckingDamageInterestAmount <> 0 OR rc.CheckingClaimFee <> 0)
      )
      OR
      (rc.ReceiptDate < :AccountingMonth
       AND rc.ReceiptProcessDate >= (SELECT MAX(tmp.BusinessDate + INTERVAL 1 DAY) FROM (SELECT BusinessDate FROM T_BusinessCalendar WHERE BusinessDate >= :AccountingMonth AND BusinessFlg = 1 LIMIT 3) tmp)
       AND ((c6.Class1 <> 0 AND o.OemClaimTransDate IS NULL) OR (c6.Class1 <> 0 AND DATE(o.OemClaimTransDate) > DATE(rc.ReceiptDate)))
      )
EOQ;

        $sql = "";
        $sql .= $sql1;
        $sql .= " UNION ALL ";
        $sql .= $sql2;
        $sql .= " ORDER BY OemId ";

        $ri = $this->app->dbAdapter->query($sql)->execute($prm);
        $datas = ResultInterfaceToArray($ri);

        $templateId = 'CKI24174_16'; // 再発行手数料明細
        $templateClass = 0;
        $seq = 0;
        $templatePattern = 0;
        $tmpFileName = $tmpFilePath . '16.再発行手数料明細_' . $formatNowStr . '.csv';

        $logicTemplate = new LogicTemplate($this->app->dbAdapter);
        $result = $logicTemplate->convertArraytoFile( $datas, $tmpFileName, $templateId, $templateClass, $seq, $templatePattern );

        //-----------------------------------------------------------
        // [01.直営日次統計表(月次)][02.OEM日次統計表(月次)]用の処理
        //-----------------------------------------------------------
        // (01.直営日次統計表(月次))
        $sumsql  = " SELECT 0 AS OemId ";
        $sumsql .= " ,      (CASE WHEN w.OemId = 0 THEN w.EnterpriseId ELSE 99999999 END) AS EnterpriseId ";
        $sumsql .= " ,      SUM(CASE WHEN w.Clm_L_ClaimFee + w.Clm_L_ClaimFeeTax <> 0 THEN 1 ELSE 0 END) AS CNT_ClaimFee ";
        $sumsql .= " ,      SUM(w.Clm_L_ClaimFee + w.Clm_L_ClaimFeeTax) AS SUM_ClaimFee ";
        $sumsql .= " ,      SUM(w.Clm_L_ClaimFeeTax) AS SUM_ClaimFeeTax ";
        $sumsql .= " ,      SUM(CASE WHEN w.Clm_L_DamageInterestAmount <> 0 THEN 1 ELSE 0 END) AS CNT_Dmg ";
        $sumsql .= " ,      SUM(w.Clm_L_DamageInterestAmount) AS SUM_Dmg ";
        $sumsql .= " FROM ( ";
        $sumsql .= $sql1;
        $sumsql .= " ) w ";
        $sumsql .= " GROUP BY (CASE WHEN OemId = 0 THEN EnterpriseId ELSE 99999999 END) ";
        $sumsql .= " WITH ROLLUP ";

        $ri = $this->app->dbAdapter->query($sumsql)->execute($prm);
        foreach ($ri as $row) {
            if ($row['EnterpriseId'] == null) {
                // 総合計
                $this->_ary01[16] = $row;

            } else {
                // 加盟店別合計
                $this->_aryEnt01[16][$row['EnterpriseId']] = $row;

            }
        }

        $sumsql = <<<EOQ
SELECT w.OemId
,      w.EnterpriseId
,      SUM(CASE WHEN w.Clm_L_ClaimFee + w.Clm_L_ClaimFeeTax <> 0 THEN 1 ELSE 0 END) AS CNT_ClaimFee
,      SUM(w.Clm_L_ClaimFee + w.Clm_L_ClaimFeeTax) AS SUM_ClaimFee
,      SUM(w.Clm_L_ClaimFeeTax) AS SUM_ClaimFeeTax
,      SUM(CASE WHEN w.Clm_L_DamageInterestAmount <> 0 THEN 1 ELSE 0 END) AS CNT_Dmg
,      SUM(w.Clm_L_DamageInterestAmount) AS SUM_Dmg
FROM (
        $sql2
) w GROUP BY w.OemId, w.EnterpriseId WITH ROLLUP;
EOQ;

        $ri = $this->app->dbAdapter->query($sumsql)->execute($prm);
        foreach ($ri as $row) {
            if ($row['OemId'] != null && $row['EnterpriseId'] == null) {
                // OEM別合計
                $this->_ary02[16][$row['OemId']] = $row;

            } elseif ($row['EnterpriseId'] != null) {
                // 加盟店別合計
                $this->_aryEnt02[16][$row['EnterpriseId']] = $row;

            }
        }

        return $tmpFileName;
    }

    /**
     * CSV出力を行う[11. 未収金日計(CSV)](月次版)
     *
     * @param string $formatNowStr 書式化年月日
     * @param string $tmpFilePath TEMP領域
     * @return ファイル名 ※出力が行われなかった場合は''を戻す
     */
    protected function CsvMishukinHikeiMonth($formatNowStr, $tmpFilePath)
    {
        $mdlsp = new \models\Table\TableSystemProperty($this->app->dbAdapter);

        // 会計月の取得
        $accountingMonth = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'AccountingMonth');

        $prm = array (':AccountingMonth' => $accountingMonth);

        // ﾜｰｸﾃｰﾌﾞﾙﾄﾗﾝｹｰﾄ
        $sql = ' TRUNCATE TABLE AW_MishukinHikeiMonth ';
        $this->app->dbAdapter->query($sql)->execute(null);

        $sql = <<<EOQ
INSERT INTO AW_MishukinHikeiMonth(ProcessingDate,ReceiptDate,DepositDate,PaymentAccountTitle,PaymentTargetAccountTitle,Amount,OemId,SUMType,EnterpriseId,OutType,OrderSeq)
    /* 以下、入金関連 */
    /* (収納代行会社 ReceiptAgentId IS NOT NULL) */
    -- [SQL02]
    SELECT DATE(rc.ReceiptProcessDate) AS ProcessingDate
    ,      rc.ReceiptDate AS ReceiptDate
    ,      rc.DepositDate AS DepositDate
    ,      c.Class3 AS PaymentAccountTitle
    ,      F_GetCode(162, 1, 1) AS PaymentTargetAccountTitle
    ,      rc.CheckingUseAmount AS Amount
    ,      c1.Class3 AS OemId
    ,      0 AS SUMType
    ,      o.EnterpriseId AS EnterpriseId
    ,      1 AS OutType
    ,      rc.OrderSeq AS OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 101 AND c.KeyCode = rc.ReceiptAgentId)
    WHERE  (rc.ReceiptDate >= :AccountingMonth AND rc.ReceiptDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    (c1.Class1 = 0 OR (c1.Class1 <> 0 AND DATE(o.OemClaimTransDate) <= DATE(rc.ReceiptDate)) OR (c1.Class1 <> 0 AND rc.ReceiptDate < o.OemClaimTransDate AND o.OemClaimTransDate <= DATE(rc.ReceiptProcessDate) AND DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))
    AND    rc.ReceiptAgentId IS NOT NULL
    AND    rc.CheckingUseAmount <> 0
    AND    arc.Rct_CancelFlg = 0
    UNION ALL
    SELECT DATE(rc.ReceiptProcessDate)
    ,      rc.ReceiptDate
    ,      rc.DepositDate
    ,      c.Class3
    ,      F_GetCode(162, 1, 1)
    ,      rc.CheckingUseAmount
    ,      c1.Class3
    ,      0
    ,      o.EnterpriseId AS EnterpriseId
    ,      1
    ,      rc.OrderSeq AS OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 101 AND c.KeyCode = rc.ReceiptAgentId)
    WHERE  (rc.ReceiptDate < :AccountingMonth AND rc.ReceiptProcessDate >= (SELECT MAX(tmp.BusinessDate + INTERVAL 1 DAY) FROM (SELECT BusinessDate FROM T_BusinessCalendar WHERE BusinessDate >= :AccountingMonth AND BusinessFlg = 1 LIMIT 3) tmp))
    AND    (c1.Class1 = 0 OR (c1.Class1 <> 0 AND DATE(o.OemClaimTransDate) <= DATE(rc.ReceiptDate)) OR (c1.Class1 <> 0 AND rc.ReceiptDate < o.OemClaimTransDate AND o.OemClaimTransDate <= DATE(rc.ReceiptProcessDate) AND DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))
    AND    rc.ReceiptAgentId IS NOT NULL
    AND    rc.CheckingUseAmount <> 0
    AND    arc.Rct_CancelFlg = 0

    UNION ALL
    -- [SQL03]
    SELECT DATE(rc.ReceiptProcessDate)
    ,      rc.ReceiptDate
    ,      rc.DepositDate
    ,      c.Class3
    ,      F_GetCode(162, 2, 1)
    ,      rc.CheckingDamageInterestAmount
    ,      c1.Class3
    ,      0
    ,      o.EnterpriseId AS EnterpriseId
    ,      1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 101 AND c.KeyCode = rc.ReceiptAgentId)
    WHERE  (rc.ReceiptDate >= :AccountingMonth AND rc.ReceiptDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    (c1.Class1 = 0 OR (c1.Class1 <> 0 AND DATE(o.OemClaimTransDate) <= DATE(rc.ReceiptDate)) OR (c1.Class1 <> 0 AND rc.ReceiptDate < o.OemClaimTransDate AND o.OemClaimTransDate <= DATE(rc.ReceiptProcessDate) AND DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))
    AND    rc.ReceiptAgentId IS NOT NULL
    AND    rc.CheckingDamageInterestAmount <> 0
    AND    arc.Rct_CancelFlg = 0
    UNION ALL
    SELECT DATE(rc.ReceiptProcessDate)
    ,      rc.ReceiptDate
    ,      rc.DepositDate
    ,      c.Class3
    ,      F_GetCode(162, 2, 1)
    ,      rc.CheckingDamageInterestAmount
    ,      c1.Class3
    ,      0
    ,      o.EnterpriseId AS EnterpriseId
    ,      1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 101 AND c.KeyCode = rc.ReceiptAgentId)
    WHERE  (rc.ReceiptDate < :AccountingMonth AND rc.ReceiptProcessDate >= (SELECT MAX(tmp.BusinessDate + INTERVAL 1 DAY) FROM (SELECT BusinessDate FROM T_BusinessCalendar WHERE BusinessDate >= :AccountingMonth AND BusinessFlg = 1 LIMIT 3) tmp))
    AND    (c1.Class1 = 0 OR (c1.Class1 <> 0 AND DATE(o.OemClaimTransDate) <= DATE(rc.ReceiptDate)) OR (c1.Class1 <> 0 AND rc.ReceiptDate < o.OemClaimTransDate AND o.OemClaimTransDate <= DATE(rc.ReceiptProcessDate) AND DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))
    AND    rc.ReceiptAgentId IS NOT NULL
    AND    rc.CheckingDamageInterestAmount <> 0
    AND    arc.Rct_CancelFlg = 0

    UNION ALL
    -- [SQL04]
    SELECT DATE(rc.ReceiptProcessDate)
    ,      rc.ReceiptDate
    ,      rc.DepositDate
    ,      c.Class3
    ,      F_GetCode(162, 3, 1)
    ,      rc.CheckingClaimFee
    ,      c1.Class3
    ,      0
    ,      o.EnterpriseId AS EnterpriseId
    ,      1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 101 AND c.KeyCode = rc.ReceiptAgentId)
    WHERE  (rc.ReceiptDate >= :AccountingMonth AND rc.ReceiptDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    (c1.Class1 = 0 OR (c1.Class1 <> 0 AND DATE(o.OemClaimTransDate) <= DATE(rc.ReceiptDate)) OR (c1.Class1 <> 0 AND rc.ReceiptDate < o.OemClaimTransDate AND o.OemClaimTransDate <= DATE(rc.ReceiptProcessDate) AND DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))
    AND    rc.ReceiptAgentId IS NOT NULL
    AND    rc.CheckingClaimFee <> 0
    AND    arc.Rct_CancelFlg = 0
    UNION ALL
    SELECT DATE(rc.ReceiptProcessDate)
    ,      rc.ReceiptDate
    ,      rc.DepositDate
    ,      c.Class3
    ,      F_GetCode(162, 3, 1)
    ,      rc.CheckingClaimFee
    ,      c1.Class3
    ,      0
    ,      o.EnterpriseId AS EnterpriseId
    ,      1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 101 AND c.KeyCode = rc.ReceiptAgentId)
    WHERE  (rc.ReceiptDate < :AccountingMonth AND rc.ReceiptProcessDate >= (SELECT MAX(tmp.BusinessDate + INTERVAL 1 DAY) FROM (SELECT BusinessDate FROM T_BusinessCalendar WHERE BusinessDate >= :AccountingMonth AND BusinessFlg = 1 LIMIT 3) tmp))
    AND    (c1.Class1 = 0 OR (c1.Class1 <> 0 AND DATE(o.OemClaimTransDate) <= DATE(rc.ReceiptDate)) OR (c1.Class1 <> 0 AND rc.ReceiptDate < o.OemClaimTransDate AND o.OemClaimTransDate <= DATE(rc.ReceiptProcessDate) AND DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))
    AND    rc.ReceiptAgentId IS NOT NULL
    AND    rc.CheckingClaimFee <> 0
    AND    arc.Rct_CancelFlg = 0

    /* (銀行 ReceiptClass=3) */
    UNION ALL
    -- [SQL05]
    SELECT DATE(rc.ReceiptProcessDate)
    ,      rc.ReceiptDate
    ,      rc.DepositDate
    ,      c.Class3
    ,      F_GetCode(162, 1, 1)
    ,      rc.CheckingUseAmount
    ,      c1.Class3
    ,      0
    ,      o.EnterpriseId AS EnterpriseId
    ,      1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 151 AND c.KeyCode = rc.BranchBankId)
    WHERE  (rc.ReceiptDate >= :AccountingMonth AND rc.ReceiptDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    (c1.Class1 = 0 OR (c1.Class1 <> 0 AND DATE(o.OemClaimTransDate) <= DATE(rc.ReceiptDate)) OR (c1.Class1 <> 0 AND rc.ReceiptDate < o.OemClaimTransDate AND o.OemClaimTransDate <= DATE(rc.ReceiptProcessDate) AND DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))
    AND    rc.ReceiptAgentId IS NULL
    AND    rc.ReceiptClass = 3
    AND    rc.CheckingUseAmount <> 0
    AND    arc.Rct_CancelFlg = 0

    UNION ALL
    SELECT DATE(rc.ReceiptProcessDate)
    ,      rc.ReceiptDate
    ,      rc.DepositDate
    ,      c.Class3
    ,      F_GetCode(162, 1, 1)
    ,      rc.CheckingUseAmount
    ,      c1.Class3
    ,      0
    ,      o.EnterpriseId AS EnterpriseId
    ,      1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 151 AND c.KeyCode = rc.BranchBankId)
    WHERE  (rc.ReceiptDate < :AccountingMonth AND rc.ReceiptProcessDate >= (SELECT MAX(tmp.BusinessDate + INTERVAL 1 DAY) FROM (SELECT BusinessDate FROM T_BusinessCalendar WHERE BusinessDate >= :AccountingMonth AND BusinessFlg = 1 LIMIT 3) tmp))
    AND    (c1.Class1 = 0 OR (c1.Class1 <> 0 AND DATE(o.OemClaimTransDate) <= DATE(rc.ReceiptDate)) OR (c1.Class1 <> 0 AND rc.ReceiptDate < o.OemClaimTransDate AND o.OemClaimTransDate <= DATE(rc.ReceiptProcessDate) AND DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))
    AND    rc.ReceiptAgentId IS NULL
    AND    rc.ReceiptClass = 3
    AND    rc.CheckingUseAmount <> 0
    AND    arc.Rct_CancelFlg = 0

    UNION ALL
    -- [SQL06]
    SELECT DATE(rc.ReceiptProcessDate)
    ,      rc.ReceiptDate
    ,      rc.DepositDate
    ,      c.Class3
    ,      F_GetCode(162, 2, 1)
    ,      rc.CheckingDamageInterestAmount
    ,      c1.Class3
    ,      0
    ,      o.EnterpriseId AS EnterpriseId
    ,      1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 151 AND c.KeyCode = rc.BranchBankId)
    WHERE  (rc.ReceiptDate >= :AccountingMonth AND rc.ReceiptDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    (c1.Class1 = 0 OR (c1.Class1 <> 0 AND DATE(o.OemClaimTransDate) <= DATE(rc.ReceiptDate)) OR (c1.Class1 <> 0 AND rc.ReceiptDate < o.OemClaimTransDate AND o.OemClaimTransDate <= DATE(rc.ReceiptProcessDate) AND DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))
    AND    rc.ReceiptAgentId IS NULL
    AND    rc.ReceiptClass = 3
    AND    rc.CheckingDamageInterestAmount <> 0
    AND    arc.Rct_CancelFlg = 0
    UNION ALL
    SELECT DATE(rc.ReceiptProcessDate)
    ,      rc.ReceiptDate
    ,      rc.DepositDate
    ,      c.Class3
    ,      F_GetCode(162, 2, 1)
    ,      rc.CheckingDamageInterestAmount
    ,      c1.Class3
    ,      0
    ,      o.EnterpriseId AS EnterpriseId
    ,      1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 151 AND c.KeyCode = rc.BranchBankId)
    WHERE  (rc.ReceiptDate < :AccountingMonth AND rc.ReceiptProcessDate >= (SELECT MAX(tmp.BusinessDate + INTERVAL 1 DAY) FROM (SELECT BusinessDate FROM T_BusinessCalendar WHERE BusinessDate >= :AccountingMonth AND BusinessFlg = 1 LIMIT 3) tmp))
    AND    (c1.Class1 = 0 OR (c1.Class1 <> 0 AND DATE(o.OemClaimTransDate) <= DATE(rc.ReceiptDate)) OR (c1.Class1 <> 0 AND rc.ReceiptDate < o.OemClaimTransDate AND o.OemClaimTransDate <= DATE(rc.ReceiptProcessDate) AND DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))
    AND    rc.ReceiptAgentId IS NULL
    AND    rc.ReceiptClass = 3
    AND    rc.CheckingDamageInterestAmount <> 0
    AND    arc.Rct_CancelFlg = 0

    UNION ALL
    -- [SQL07]
    SELECT DATE(rc.ReceiptProcessDate)
    ,      rc.ReceiptDate
    ,      rc.DepositDate
    ,      c.Class3
    ,      F_GetCode(162, 3, 1)
    ,      rc.CheckingClaimFee
    ,      c1.Class3
    ,      0
    ,      o.EnterpriseId AS EnterpriseId
    ,      1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 151 AND c.KeyCode = rc.BranchBankId)
    WHERE  (rc.ReceiptDate >= :AccountingMonth AND rc.ReceiptDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    (c1.Class1 = 0 OR (c1.Class1 <> 0 AND DATE(o.OemClaimTransDate) <= DATE(rc.ReceiptDate)) OR (c1.Class1 <> 0 AND rc.ReceiptDate < o.OemClaimTransDate AND o.OemClaimTransDate <= DATE(rc.ReceiptProcessDate) AND DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))
    AND    rc.ReceiptAgentId IS NULL
    AND    rc.ReceiptClass = 3
    AND    rc.CheckingClaimFee <> 0
    AND    arc.Rct_CancelFlg = 0
    UNION ALL
    SELECT DATE(rc.ReceiptProcessDate)
    ,      rc.ReceiptDate
    ,      rc.DepositDate
    ,      c.Class3
    ,      F_GetCode(162, 3, 1)
    ,      rc.CheckingClaimFee
    ,      c1.Class3
    ,      0
    ,      o.EnterpriseId AS EnterpriseId
    ,      1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 151 AND c.KeyCode = rc.BranchBankId)
    WHERE  (rc.ReceiptDate < :AccountingMonth AND rc.ReceiptProcessDate >= (SELECT MAX(tmp.BusinessDate + INTERVAL 1 DAY) FROM (SELECT BusinessDate FROM T_BusinessCalendar WHERE BusinessDate >= :AccountingMonth AND BusinessFlg = 1 LIMIT 3) tmp))
    AND    (c1.Class1 = 0 OR (c1.Class1 <> 0 AND DATE(o.OemClaimTransDate) <= DATE(rc.ReceiptDate)) OR (c1.Class1 <> 0 AND rc.ReceiptDate < o.OemClaimTransDate AND o.OemClaimTransDate <= DATE(rc.ReceiptProcessDate) AND DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))
    AND    rc.ReceiptAgentId IS NULL
    AND    rc.ReceiptClass = 3
    AND    rc.CheckingClaimFee <> 0
    AND    arc.Rct_CancelFlg = 0
EOQ;

        // SQL実行
        $this->app->dbAdapter->query($sql)->execute($prm);

        $sql = <<<EOQ
INSERT INTO AW_MishukinHikeiMonth(ProcessingDate,ReceiptDate,DepositDate,PaymentAccountTitle,PaymentTargetAccountTitle,Amount,OemId,SUMType,EnterpriseId,OutType,OrderSeq)
    /* (郵便局 ReceiptClass=2) */
    -- [SQL08]
    SELECT DATE(rc.ReceiptProcessDate)
    ,      rc.ReceiptDate
    ,      rc.DepositDate
    ,      c.Class3
    ,      F_GetCode(162, 1, 1)
    ,      rc.CheckingUseAmount
    ,      c1.Class3
    ,      0
    ,      o.EnterpriseId AS EnterpriseId
    ,      1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 166 AND c.Class1 = arc.AccountNumber)
    WHERE  (rc.ReceiptDate >= :AccountingMonth AND rc.ReceiptDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    (c1.Class1 = 0 OR (c1.Class1 <> 0 AND DATE(o.OemClaimTransDate) <= DATE(rc.ReceiptDate)) OR (c1.Class1 <> 0 AND rc.ReceiptDate < o.OemClaimTransDate AND o.OemClaimTransDate <= DATE(rc.ReceiptProcessDate) AND DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))
    AND    rc.ReceiptAgentId IS NULL
    AND    rc.ReceiptClass = 2
    AND    rc.CheckingUseAmount <> 0
    AND    arc.Rct_CancelFlg = 0
    UNION ALL
    SELECT DATE(rc.ReceiptProcessDate)
    ,      rc.ReceiptDate
    ,      rc.DepositDate
    ,      c.Class3
    ,      F_GetCode(162, 1, 1)
    ,      rc.CheckingUseAmount
    ,      c1.Class3
    ,      0
    ,      o.EnterpriseId AS EnterpriseId
    ,      1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 166 AND c.Class1 = arc.AccountNumber)
    WHERE  (rc.ReceiptDate < :AccountingMonth AND rc.ReceiptProcessDate >= (SELECT MAX(tmp.BusinessDate + INTERVAL 1 DAY) FROM (SELECT BusinessDate FROM T_BusinessCalendar WHERE BusinessDate >= :AccountingMonth AND BusinessFlg = 1 LIMIT 3) tmp))
    AND    (c1.Class1 = 0 OR (c1.Class1 <> 0 AND DATE(o.OemClaimTransDate) <= DATE(rc.ReceiptDate)) OR (c1.Class1 <> 0 AND rc.ReceiptDate < o.OemClaimTransDate AND o.OemClaimTransDate <= DATE(rc.ReceiptProcessDate) AND DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))
    AND    rc.ReceiptAgentId IS NULL
    AND    rc.ReceiptClass = 2
    AND    rc.CheckingUseAmount <> 0
    AND    arc.Rct_CancelFlg = 0

    UNION ALL
    -- [SQL09]
    SELECT DATE(rc.ReceiptProcessDate)
    ,      rc.ReceiptDate
    ,      rc.DepositDate
    ,      c.Class3
    ,      F_GetCode(162, 2, 1)
    ,      rc.CheckingDamageInterestAmount
    ,      c1.Class3
    ,      0
    ,      o.EnterpriseId AS EnterpriseId
    ,      1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 166 AND c.Class1 = arc.AccountNumber)
    WHERE  (rc.ReceiptDate >= :AccountingMonth AND rc.ReceiptDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    (c1.Class1 = 0 OR (c1.Class1 <> 0 AND DATE(o.OemClaimTransDate) <= DATE(rc.ReceiptDate)) OR (c1.Class1 <> 0 AND rc.ReceiptDate < o.OemClaimTransDate AND o.OemClaimTransDate <= DATE(rc.ReceiptProcessDate) AND DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))
    AND    rc.ReceiptAgentId IS NULL
    AND    rc.ReceiptClass = 2
    AND    rc.CheckingDamageInterestAmount <> 0
    AND    arc.Rct_CancelFlg = 0
    UNION ALL
    SELECT DATE(rc.ReceiptProcessDate)
    ,      rc.ReceiptDate
    ,      rc.DepositDate
    ,      c.Class3
    ,      F_GetCode(162, 2, 1)
    ,      rc.CheckingDamageInterestAmount
    ,      c1.Class3
    ,      0
    ,      o.EnterpriseId AS EnterpriseId
    ,      1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 166 AND c.Class1 = arc.AccountNumber)
    WHERE  (rc.ReceiptDate < :AccountingMonth AND rc.ReceiptProcessDate >= (SELECT MAX(tmp.BusinessDate + INTERVAL 1 DAY) FROM (SELECT BusinessDate FROM T_BusinessCalendar WHERE BusinessDate >= :AccountingMonth AND BusinessFlg = 1 LIMIT 3) tmp))
    AND    (c1.Class1 = 0 OR (c1.Class1 <> 0 AND DATE(o.OemClaimTransDate) <= DATE(rc.ReceiptDate)) OR (c1.Class1 <> 0 AND rc.ReceiptDate < o.OemClaimTransDate AND o.OemClaimTransDate <= DATE(rc.ReceiptProcessDate) AND DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))
    AND    rc.ReceiptAgentId IS NULL
    AND    rc.ReceiptClass = 2
    AND    rc.CheckingDamageInterestAmount <> 0
    AND    arc.Rct_CancelFlg = 0

    UNION ALL
    -- [SQL10]
    SELECT DATE(rc.ReceiptProcessDate)
    ,      rc.ReceiptDate
    ,      rc.DepositDate
    ,      c.Class3
    ,      F_GetCode(162, 3, 1)
    ,      rc.CheckingClaimFee
    ,      c1.Class3
    ,      0
    ,      o.EnterpriseId AS EnterpriseId
    ,      1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 166 AND c.Class1 = arc.AccountNumber)
    WHERE  (rc.ReceiptDate >= :AccountingMonth AND rc.ReceiptDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    (c1.Class1 = 0 OR (c1.Class1 <> 0 AND DATE(o.OemClaimTransDate) <= DATE(rc.ReceiptDate)) OR (c1.Class1 <> 0 AND rc.ReceiptDate < o.OemClaimTransDate AND o.OemClaimTransDate <= DATE(rc.ReceiptProcessDate) AND DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))
    AND    rc.ReceiptAgentId IS NULL
    AND    rc.ReceiptClass = 2
    AND    rc.CheckingClaimFee <> 0
    AND    arc.Rct_CancelFlg = 0
    UNION ALL
    SELECT DATE(rc.ReceiptProcessDate)
    ,      rc.ReceiptDate
    ,      rc.DepositDate
    ,      c.Class3
    ,      F_GetCode(162, 3, 1)
    ,      rc.CheckingClaimFee
    ,      c1.Class3
    ,      0
    ,      o.EnterpriseId AS EnterpriseId
    ,      1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 166 AND c.Class1 = arc.AccountNumber)
    WHERE  (rc.ReceiptDate < :AccountingMonth AND rc.ReceiptProcessDate >= (SELECT MAX(tmp.BusinessDate + INTERVAL 1 DAY) FROM (SELECT BusinessDate FROM T_BusinessCalendar WHERE BusinessDate >= :AccountingMonth AND BusinessFlg = 1 LIMIT 3) tmp))
    AND    (c1.Class1 = 0 OR (c1.Class1 <> 0 AND DATE(o.OemClaimTransDate) <= DATE(rc.ReceiptDate)) OR (c1.Class1 <> 0 AND rc.ReceiptDate < o.OemClaimTransDate AND o.OemClaimTransDate <= DATE(rc.ReceiptProcessDate) AND DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))
    AND    rc.ReceiptAgentId IS NULL
    AND    rc.ReceiptClass = 2
    AND    rc.CheckingClaimFee <> 0
    AND    arc.Rct_CancelFlg = 0

    /* (その他 ReceiptClass=9) */
    UNION ALL
    -- [SQL11]
    SELECT DATE(rc.ReceiptProcessDate)
    ,      rc.ReceiptDate
    ,      rc.DepositDate
    ,      c.Class3
    ,      F_GetCode(162, 1, 1)
    ,      rc.CheckingUseAmount
    ,      c1.Class3
    ,      0
    ,      o.EnterpriseId AS EnterpriseId
    ,      1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 155 AND c.KeyCode = arc.ClassDetails)
    WHERE  (rc.ReceiptDate >= :AccountingMonth AND rc.ReceiptDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    (c1.Class1 = 0 OR (c1.Class1 <> 0 AND DATE(o.OemClaimTransDate) <= DATE(rc.ReceiptDate)) OR (c1.Class1 <> 0 AND rc.ReceiptDate < o.OemClaimTransDate AND o.OemClaimTransDate <= DATE(rc.ReceiptProcessDate) AND DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))
    AND    rc.ReceiptAgentId IS NULL
    AND    rc.ReceiptClass = 9
    AND    rc.CheckingUseAmount <> 0
    AND    arc.Rct_CancelFlg = 0
    UNION ALL
    SELECT DATE(rc.ReceiptProcessDate)
    ,      rc.ReceiptDate
    ,      rc.DepositDate
    ,      c.Class3
    ,      F_GetCode(162, 1, 1)
    ,      rc.CheckingUseAmount
    ,      c1.Class3
    ,      0
    ,      o.EnterpriseId AS EnterpriseId
    ,      1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 155 AND c.KeyCode = arc.ClassDetails)
    WHERE  (rc.ReceiptDate < :AccountingMonth AND rc.ReceiptProcessDate >= (SELECT MAX(tmp.BusinessDate + INTERVAL 1 DAY) FROM (SELECT BusinessDate FROM T_BusinessCalendar WHERE BusinessDate >= :AccountingMonth AND BusinessFlg = 1 LIMIT 3) tmp))
    AND    (c1.Class1 = 0 OR (c1.Class1 <> 0 AND DATE(o.OemClaimTransDate) <= DATE(rc.ReceiptDate)) OR (c1.Class1 <> 0 AND rc.ReceiptDate < o.OemClaimTransDate AND o.OemClaimTransDate <= DATE(rc.ReceiptProcessDate) AND DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))
    AND    rc.ReceiptAgentId IS NULL
    AND    rc.ReceiptClass = 9
    AND    rc.CheckingUseAmount <> 0
    AND    arc.Rct_CancelFlg = 0

    UNION ALL
    -- [SQL12]
    SELECT DATE(rc.ReceiptProcessDate)
    ,      rc.ReceiptDate
    ,      rc.DepositDate
    ,      c.Class3
    ,      F_GetCode(162, 2, 1)
    ,      rc.CheckingDamageInterestAmount
    ,      c1.Class3
    ,      0
    ,      o.EnterpriseId AS EnterpriseId
    ,      1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 155 AND c.KeyCode = arc.ClassDetails)
    WHERE  (rc.ReceiptDate >= :AccountingMonth AND rc.ReceiptDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    (c1.Class1 = 0 OR (c1.Class1 <> 0 AND DATE(o.OemClaimTransDate) <= DATE(rc.ReceiptDate)) OR (c1.Class1 <> 0 AND rc.ReceiptDate < o.OemClaimTransDate AND o.OemClaimTransDate <= DATE(rc.ReceiptProcessDate) AND DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))
    AND    rc.ReceiptAgentId IS NULL
    AND    rc.ReceiptClass = 9
    AND    rc.CheckingDamageInterestAmount <> 0
    AND    arc.Rct_CancelFlg = 0
    UNION ALL
    SELECT DATE(rc.ReceiptProcessDate)
    ,      rc.ReceiptDate
    ,      rc.DepositDate
    ,      c.Class3
    ,      F_GetCode(162, 2, 1)
    ,      rc.CheckingDamageInterestAmount
    ,      c1.Class3
    ,      0
    ,      o.EnterpriseId AS EnterpriseId
    ,      1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 155 AND c.KeyCode = arc.ClassDetails)
    WHERE  (rc.ReceiptDate < :AccountingMonth AND rc.ReceiptProcessDate >= (SELECT MAX(tmp.BusinessDate + INTERVAL 1 DAY) FROM (SELECT BusinessDate FROM T_BusinessCalendar WHERE BusinessDate >= :AccountingMonth AND BusinessFlg = 1 LIMIT 3) tmp))
    AND    (c1.Class1 = 0 OR (c1.Class1 <> 0 AND DATE(o.OemClaimTransDate) <= DATE(rc.ReceiptDate)) OR (c1.Class1 <> 0 AND rc.ReceiptDate < o.OemClaimTransDate AND o.OemClaimTransDate <= DATE(rc.ReceiptProcessDate) AND DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))
    AND    rc.ReceiptAgentId IS NULL
    AND    rc.ReceiptClass = 9
    AND    rc.CheckingDamageInterestAmount <> 0
    AND    arc.Rct_CancelFlg = 0

    UNION ALL
    -- [SQL13]
    SELECT DATE(rc.ReceiptProcessDate)
    ,      rc.ReceiptDate
    ,      rc.DepositDate
    ,      c.Class3
    ,      F_GetCode(162, 3, 1)
    ,      rc.CheckingClaimFee
    ,      c1.Class3
    ,      0
    ,      o.EnterpriseId AS EnterpriseId
    ,      1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 155 AND c.KeyCode = arc.ClassDetails)
    WHERE  (rc.ReceiptDate >= :AccountingMonth AND rc.ReceiptDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    (c1.Class1 = 0 OR (c1.Class1 <> 0 AND DATE(o.OemClaimTransDate) <= DATE(rc.ReceiptDate)) OR (c1.Class1 <> 0 AND rc.ReceiptDate < o.OemClaimTransDate AND o.OemClaimTransDate <= DATE(rc.ReceiptProcessDate) AND DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))
    AND    rc.ReceiptAgentId IS NULL
    AND    rc.ReceiptClass = 9
    AND    rc.CheckingClaimFee <> 0
    AND    arc.Rct_CancelFlg = 0
    UNION ALL
    SELECT DATE(rc.ReceiptProcessDate)
    ,      rc.ReceiptDate
    ,      rc.DepositDate
    ,      c.Class3
    ,      F_GetCode(162, 3, 1)
    ,      rc.CheckingClaimFee
    ,      c1.Class3
    ,      0
    ,      o.EnterpriseId AS EnterpriseId
    ,      1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 155 AND c.KeyCode = arc.ClassDetails)
    WHERE  (rc.ReceiptDate < :AccountingMonth AND rc.ReceiptProcessDate >= (SELECT MAX(tmp.BusinessDate + INTERVAL 1 DAY) FROM (SELECT BusinessDate FROM T_BusinessCalendar WHERE BusinessDate >= :AccountingMonth AND BusinessFlg = 1 LIMIT 3) tmp))
    AND    (c1.Class1 = 0 OR (c1.Class1 <> 0 AND DATE(o.OemClaimTransDate) <= DATE(rc.ReceiptDate)) OR (c1.Class1 <> 0 AND rc.ReceiptDate < o.OemClaimTransDate AND o.OemClaimTransDate <= DATE(rc.ReceiptProcessDate) AND DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))
    AND    rc.ReceiptAgentId IS NULL
    AND    rc.ReceiptClass = 9
    AND    rc.CheckingClaimFee <> 0
    AND    arc.Rct_CancelFlg = 0

    /* 以下、雑損失／雑収入関連 */
    -- (雑収入 SundryType=0)
    UNION ALL
    -- [SQL15]
    SELECT DATE(sc.ProcessDate)
    ,      sc.ProcessDate
    ,      sc.ProcessDate
    ,      c3.Class1
    ,      F_GetCode(162, 1, 1)
    ,      sc.CheckingUseAmount * -1
    ,      c2.Class3
    ,      (CASE WHEN c1.Class3 = 1 THEN 2 ELSE 3 END) -- SUMType=2(貸倒)／3(その他)
    ,      o.EnterpriseId AS EnterpriseId
    ,      1
    ,      sc.OrderSeq
    FROM   T_SundryControl sc
           INNER JOIN M_Code c1 ON (c1.CodeId = 96 AND c1.KeyCode = sc.SundryClass)
           INNER JOIN T_Order o ON (sc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c2 ON (c2.CodeId = 160 AND c2.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c3 ON (c3.CodeId = 165 AND c3.KeyCode = sc.SundryClass)
    WHERE  (sc.ProcessDate >= :AccountingMonth AND sc.ProcessDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    c1.Class2 = 1
    AND    (c2.Class1 = 0  OR (c2.Class1 = 1 AND DATE(o.OemClaimTransDate) <= DATE(sc.ProcessDate)))
    AND    sc.SundryType = 0
    AND    sc.CheckingUseAmount <> 0

    UNION ALL
    -- [SQL16]
    SELECT DATE(sc.ProcessDate)
    ,      sc.ProcessDate
    ,      sc.ProcessDate
    ,      c3.Class1
    ,      F_GetCode(162, 2, 1)
    ,      sc.CheckingDamageInterestAmount * -1
    ,      c2.Class3
    ,      (CASE WHEN c1.Class3 = 1 THEN 2 ELSE 3 END) -- SUMType=2(貸倒)／3(その他)
    ,      o.EnterpriseId AS EnterpriseId
    ,      1
    ,      sc.OrderSeq
    FROM   T_SundryControl sc
           INNER JOIN M_Code c1 ON (c1.CodeId = 96 AND c1.KeyCode = sc.SundryClass)
           INNER JOIN T_Order o ON (sc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c2 ON (c2.CodeId = 160 AND c2.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c3 ON (c3.CodeId = 165 AND c3.KeyCode = sc.SundryClass)
    WHERE  (sc.ProcessDate >= :AccountingMonth AND sc.ProcessDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    c1.Class2 = 1
    AND    (c2.Class1 = 0  OR (c2.Class1 = 1 AND DATE(o.OemClaimTransDate) <= DATE(sc.ProcessDate)))
    AND    sc.SundryType = 0
    AND    sc.CheckingDamageInterestAmount <> 0

    UNION ALL
    -- [SQL17]
    SELECT DATE(sc.ProcessDate)
    ,      sc.ProcessDate
    ,      sc.ProcessDate
    ,      c3.Class1
    ,      F_GetCode(162, 3, 1)
    ,      sc.CheckingClaimFee * -1
    ,      c2.Class3
    ,      (CASE WHEN c1.Class3 = 1 THEN 2 ELSE 3 END) -- SUMType=2(貸倒)／3(その他)
    ,      o.EnterpriseId AS EnterpriseId
    ,      1
    ,      sc.OrderSeq
    FROM   T_SundryControl sc
           INNER JOIN M_Code c1 ON (c1.CodeId = 96 AND c1.KeyCode = sc.SundryClass)
           INNER JOIN T_Order o ON (sc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c2 ON (c2.CodeId = 160 AND c2.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c3 ON (c3.CodeId = 165 AND c3.KeyCode = sc.SundryClass)
    WHERE  (sc.ProcessDate >= :AccountingMonth AND sc.ProcessDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    c1.Class2 = 1
    AND    (c2.Class1 = 0  OR (c2.Class1 = 1 AND DATE(o.OemClaimTransDate) <= DATE(sc.ProcessDate)))
    AND    sc.SundryType = 0
    AND    sc.CheckingClaimFee <> 0

    -- (雑損失 SundryType=1)
    UNION ALL
    -- [SQL18]
    SELECT DATE(sc.ProcessDate)
    ,      sc.ProcessDate
    ,      sc.ProcessDate
    ,      c3.Class1
    ,      F_GetCode(162, 1, 1)
    ,      sc.CheckingUseAmount
    ,      c2.Class3
    ,      (CASE WHEN c1.Class3 = 1 THEN 2 ELSE 3 END) -- SUMType=2(貸倒)／3(その他)
    ,      o.EnterpriseId AS EnterpriseId
    ,      1
    ,      sc.OrderSeq
    FROM   T_SundryControl sc
           INNER JOIN M_Code c1 ON (c1.CodeId = 96 AND c1.KeyCode = sc.SundryClass)
           INNER JOIN T_Order o ON (sc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c2 ON (c2.CodeId = 160 AND c2.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c3 ON (c3.CodeId = 165 AND c3.KeyCode = sc.SundryClass)
    WHERE  (sc.ProcessDate >= :AccountingMonth AND sc.ProcessDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    c1.Class2 = 1
    AND    (c2.Class1 = 0  OR (c2.Class1 = 1 AND DATE(o.OemClaimTransDate) <= DATE(sc.ProcessDate)))
    AND    sc.SundryType = 1
    AND    sc.CheckingUseAmount <> 0

    UNION ALL
    -- [SQL19]
    SELECT DATE(sc.ProcessDate)
    ,      sc.ProcessDate
    ,      sc.ProcessDate
    ,      c3.Class1
    ,      F_GetCode(162, 2, 1)
    ,      sc.CheckingDamageInterestAmount
    ,      c2.Class3
    ,      (CASE WHEN c1.Class3 = 1 THEN 2 ELSE 3 END) -- SUMType=2(貸倒)／3(その他)
    ,      o.EnterpriseId AS EnterpriseId
    ,      1
    ,      sc.OrderSeq
    FROM   T_SundryControl sc
           INNER JOIN M_Code c1 ON (c1.CodeId = 96 AND c1.KeyCode = sc.SundryClass)
           INNER JOIN T_Order o ON (sc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c2 ON (c2.CodeId = 160 AND c2.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c3 ON (c3.CodeId = 165 AND c3.KeyCode = sc.SundryClass)
    WHERE  (sc.ProcessDate >= :AccountingMonth AND sc.ProcessDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    c1.Class2 = 1
    AND    (c2.Class1 = 0  OR (c2.Class1 = 1 AND DATE(o.OemClaimTransDate) <= DATE(sc.ProcessDate)))
    AND    sc.SundryType = 1
    AND    sc.CheckingDamageInterestAmount <> 0

    UNION ALL
    -- [SQL20]
    SELECT DATE(sc.ProcessDate)
    ,      sc.ProcessDate
    ,      sc.ProcessDate
    ,      c3.Class1
    ,      F_GetCode(162, 3, 1)
    ,      sc.CheckingClaimFee
    ,      c2.Class3
    ,      (CASE WHEN c1.Class3 = 1 THEN 2 ELSE 3 END) -- SUMType=2(貸倒)／3(その他)
    ,      o.EnterpriseId AS EnterpriseId
    ,      1
    ,      sc.OrderSeq
    FROM   T_SundryControl sc
           INNER JOIN M_Code c1 ON (c1.CodeId = 96 AND c1.KeyCode = sc.SundryClass)
           INNER JOIN T_Order o ON (sc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c2 ON (c2.CodeId = 160 AND c2.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c3 ON (c3.CodeId = 165 AND c3.KeyCode = sc.SundryClass)
    WHERE  (sc.ProcessDate >= :AccountingMonth AND sc.ProcessDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    c1.Class2 = 1
    AND    (c2.Class1 = 0  OR (c2.Class1 = 1 AND DATE(o.OemClaimTransDate) <= DATE(sc.ProcessDate)))
    AND    sc.SundryType = 1
    AND    sc.CheckingClaimFee <> 0

    /* (返金関連) */
    UNION ALL
    -- [SQL21]
    SELECT DATE(rc.DecisionDate)
    ,      DATE(rc.DecisionDate)
    ,      DATE(rc.DecisionDate)
    ,      c2.Class2
    ,      F_GetCode(162, 8, 1)
    ,      rc.RepayAmount * -1
    ,      c1.Class3
    ,      1 -- SUMType=1(返金)
    ,      o.EnterpriseId AS EnterpriseId
    ,      1
    ,      o.OrderSeq
    FROM   T_RepaymentControl rc
           INNER JOIN AT_RepaymentControl arc ON (arc.RepaySeq = rc.RepaySeq)
           INNER JOIN T_ClaimControl cc ON (rc.ClaimId = cc.ClaimId)
           INNER JOIN T_Order o ON (cc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c2 ON (c2.CodeId = 151 AND c2.KeyCode = F_GetSystemProperty('[DEFAULT]', 'systeminfo', 'RepayBankId'))
    WHERE  (rc.DecisionDate >= :AccountingMonth AND rc.DecisionDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    rc.RepayStatus = 1
    AND    rc.RepayAmount <> 0
    AND    (c1.Class1 = 0 OR (c1.Class1 = 1 AND o.OemClaimTransDate <= DATE( rc.DecisionDate )))

    UNION ALL
    -- [キャンセル関連]
    SELECT DATE(pc.ExecDate)
    ,      DATE(pc.DecisionDate)
    ,      DATE(pc.ExecDate)
    ,      F_GetCode(162, 9, 1)
    ,      F_GetCode(162, 8, 1)
    ,      cc.ReceiptAmountTotal * -1
    ,      c1.Class3
    ,      1
    ,      o.EnterpriseId AS EnterpriseId
    ,      1
    ,      o.OrderSeq
    FROM   T_PayingControl pc
           INNER JOIN T_Cancel can ON (pc.Seq = can.PayingControlSeq)
           INNER JOIN T_ClaimControl cc ON (cc.OrderSeq = can.OrderSeq)
           INNER JOIN T_Order o ON (can.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(pc.OemId, 0))
    WHERE  (pc.ExecDate >= :AccountingMonth AND pc.ExecDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    cc.ReceiptAmountTotal <> 0
    AND    can.CancelPhase IN (3, 4)
    AND    can.ValidFlg = 1
    AND    c1.Class1 = 0

    /* 加盟店入金関連 */
    UNION ALL
    -- [SQL24]
    SELECT erh.ReceiptDate
    ,      erh.ReceiptDate
    ,      erh.ReceiptDate
    ,      c2.Class1
    ,      F_GetCode(162, 4, 1)
    ,      aerh.ReceiptAmountDue
    ,      c.Class3
    ,      0
    ,      erh.EnterpriseId AS EnterpriseId
    ,      1
    ,      NULL
    FROM   T_EnterpriseReceiptHistory erh
           INNER JOIN AT_EnterpriseReceiptHistory aerh ON (erh.EntRcptSeq = aerh.EntRcptSeq)
           INNER JOIN T_Enterprise e ON (erh.EnterpriseId = e.EnterpriseId)
           INNER JOIN M_Code c ON (c.CodeId = 160 AND c.KeyCode = IFNULL(e.OemId, 0))
           INNER JOIN M_Code c1 ON (c1.CodeId = 95 AND c1.KeyCode = erh.ReceiptClass)
           LEFT OUTER JOIN M_Code c2 ON (c2.CodeId = 167 AND c2.KeyCode = aerh.ReceiptAmountSource)
    WHERE  c.Class1 = 0
    AND    (erh.ReceiptProcessDate >= :AccountingMonth AND erh.ReceiptProcessDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    c1.Class1 = 0
    AND    aerh.ReceiptAmountDue <> 0

    UNION ALL
    -- [SQL25]
    SELECT erh.ReceiptDate
    ,      erh.ReceiptDate
    ,      erh.ReceiptDate
    ,      c2.Class1
    ,      F_GetCode(162, 6, 1)
    ,      aerh.ReceiptAmountRece
    ,      c.Class3
    ,      0
    ,      erh.EnterpriseId AS EnterpriseId
    ,      1
    ,      NULL
    FROM   T_EnterpriseReceiptHistory erh
           INNER JOIN AT_EnterpriseReceiptHistory aerh ON (erh.EntRcptSeq = aerh.EntRcptSeq)
           INNER JOIN T_Enterprise e ON (erh.EnterpriseId = e.EnterpriseId)
           INNER JOIN M_Code c ON (c.CodeId = 160 AND c.KeyCode = IFNULL(e.OemId, 0))
           INNER JOIN M_Code c1 ON (c1.CodeId = 95 AND c1.KeyCode = erh.ReceiptClass)
           LEFT OUTER JOIN M_Code c2 ON (c2.CodeId = 167 AND c2.KeyCode = aerh.ReceiptAmountSource)
    WHERE  c.Class1 = 0
    AND    (erh.ReceiptProcessDate >= :AccountingMonth AND erh.ReceiptProcessDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    c1.Class1 = 0
    AND    aerh.ReceiptAmountRece <> 0

    UNION ALL
    -- [SQL26]
    SELECT erh.ReceiptDate
    ,      erh.ReceiptDate
    ,      erh.ReceiptDate
    ,      c2.Class1
    ,      F_GetCode(162, 4, 1)
    ,      erh.ReceiptAmount
    ,      c.Class3
    ,      0
    ,      erh.EnterpriseId AS EnterpriseId
    ,      1
    ,      NULL
    FROM   T_EnterpriseReceiptHistory erh
           INNER JOIN AT_EnterpriseReceiptHistory aerh ON (erh.EntRcptSeq = aerh.EntRcptSeq)
           INNER JOIN T_Enterprise e ON (erh.EnterpriseId = e.EnterpriseId)
           INNER JOIN M_Code c ON (c.CodeId = 160 AND c.KeyCode = IFNULL(e.OemId, 0))
           INNER JOIN M_Code c1 ON (c1.CodeId = 95 AND c1.KeyCode = erh.ReceiptClass)
           LEFT OUTER JOIN M_Code c2 ON (c2.CodeId = 165 AND c2.KeyCode = erh.ReceiptClass)
    WHERE  c.Class1 = 0
    AND    (erh.ReceiptProcessDate >= :AccountingMonth AND erh.ReceiptProcessDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    c1.Class1 = 1
    AND    erh.ReceiptAmount <> 0
EOQ;

        // SQL実行
        $this->app->dbAdapter->query($sql)->execute($prm);

$sql = <<<EOQ
INSERT INTO AW_MishukinHikeiMonth(ProcessingDate,ReceiptDate,DepositDate,PaymentAccountTitle,PaymentTargetAccountTitle,Amount,OemId,SUMType,EnterpriseId,OutType,OrderSeq)
    /* 以下、入金取消分 */
    SELECT DATE(rc.ReceiptProcessDate) AS ProcessingDate
    ,      rc2.ReceiptDate AS ReceiptDate
    ,      rc2.DepositDate AS DepositDate
    ,      c.Class3 AS PaymentAccountTitle
    ,      F_GetCode(162, 1, 1) AS PaymentTargetAccountTitle
    ,      rc2.CheckingUseAmount * -1 AS Amount
    ,      c1.Class3 AS OemId
    ,      0 AS SUMType
    ,      o.EnterpriseId AS EnterpriseId
    ,      -1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_ReceiptControl rc2 ON (rc2.ReceiptSeq < rc.ReceiptSeq AND rc2.OrderSeq = rc.OrderSeq)
           INNER JOIN AT_ReceiptControl arc2 ON (rc2.ReceiptSeq = arc2.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 101 AND c.KeyCode = rc2.ReceiptAgentId)
    WHERE  (rc2.ReceiptDate >= :AccountingMonth AND rc2.ReceiptDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    (c1.Class1 = 0 OR (c1.Class1 <> 0 AND DATE(o.OemClaimTransDate) <= DATE(rc.ReceiptDate)) OR (c1.Class1 <> 0 AND rc.ReceiptDate < o.OemClaimTransDate AND o.OemClaimTransDate <= DATE(rc.ReceiptProcessDate) AND DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))
    AND    rc2.ReceiptAgentId IS NOT NULL
    AND    rc2.CheckingUseAmount <> 0
    AND    arc.Rct_CancelFlg = 1
    AND    rc2.ReceiptSeq BETWEEN (SELECT IFNULL(MAX(tmp.ReceiptSeq), 0) FROM T_ReceiptControl tmp, AT_ReceiptControl tmp2 WHERE tmp.ReceiptSeq = tmp2.ReceiptSeq AND tmp.ReceiptSeq < rc.ReceiptSeq AND tmp.OrderSeq = rc.OrderSeq AND tmp2.Rct_CancelFlg = 1) + 1 AND rc.ReceiptSeq - 1
    UNION ALL
    SELECT DATE(rc.ReceiptProcessDate)
    ,      rc2.ReceiptDate
    ,      rc2.DepositDate
    ,      c.Class3
    ,      F_GetCode(162, 1, 1)
    ,      rc2.CheckingUseAmount * -1
    ,      c1.Class3
    ,      0
    ,      o.EnterpriseId AS EnterpriseId
    ,      -1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_ReceiptControl rc2 ON (rc2.ReceiptSeq < rc.ReceiptSeq AND rc2.OrderSeq = rc.OrderSeq)
           INNER JOIN AT_ReceiptControl arc2 ON (rc2.ReceiptSeq = arc2.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 101 AND c.KeyCode = rc2.ReceiptAgentId)
    WHERE  (rc2.ReceiptDate < :AccountingMonth AND rc.ReceiptProcessDate >= (SELECT MAX(tmp.BusinessDate + INTERVAL 1 DAY) FROM (SELECT BusinessDate FROM T_BusinessCalendar WHERE BusinessDate >= :AccountingMonth AND BusinessFlg = 1 LIMIT 3) tmp))
    AND    (c1.Class1 = 0 OR (c1.Class1 <> 0 AND DATE(o.OemClaimTransDate) <= DATE(rc.ReceiptDate)) OR (c1.Class1 <> 0 AND rc.ReceiptDate < o.OemClaimTransDate AND o.OemClaimTransDate <= DATE(rc.ReceiptProcessDate) AND DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))
    AND    rc2.ReceiptAgentId IS NOT NULL
    AND    rc2.CheckingUseAmount <> 0
    AND    arc.Rct_CancelFlg = 1
    AND    rc2.ReceiptSeq BETWEEN (SELECT IFNULL(MAX(tmp.ReceiptSeq), 0) FROM T_ReceiptControl tmp, AT_ReceiptControl tmp2 WHERE tmp.ReceiptSeq = tmp2.ReceiptSeq AND tmp.ReceiptSeq < rc.ReceiptSeq AND tmp.OrderSeq = rc.OrderSeq AND tmp2.Rct_CancelFlg = 1) + 1 AND rc.ReceiptSeq - 1

    UNION ALL
    -- [SQL03]
    SELECT DATE(rc.ReceiptProcessDate)
    ,      rc2.ReceiptDate
    ,      rc2.DepositDate
    ,      c.Class3
    ,      F_GetCode(162, 2, 1)
    ,      rc2.CheckingDamageInterestAmount * -1
    ,      c1.Class3
    ,      0
    ,      o.EnterpriseId AS EnterpriseId
    ,      -1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_ReceiptControl rc2 ON (rc2.ReceiptSeq < rc.ReceiptSeq AND rc2.OrderSeq = rc.OrderSeq)
           INNER JOIN AT_ReceiptControl arc2 ON (rc2.ReceiptSeq = arc2.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 101 AND c.KeyCode = rc2.ReceiptAgentId)
    WHERE  (rc2.ReceiptDate >= :AccountingMonth AND rc2.ReceiptDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    (c1.Class1 = 0 OR (c1.Class1 <> 0 AND DATE(o.OemClaimTransDate) <= DATE(rc.ReceiptDate)) OR (c1.Class1 <> 0 AND rc.ReceiptDate < o.OemClaimTransDate AND o.OemClaimTransDate <= DATE(rc.ReceiptProcessDate) AND DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))
    AND    rc2.ReceiptAgentId IS NOT NULL
    AND    rc2.CheckingDamageInterestAmount <> 0
    AND    arc.Rct_CancelFlg = 1
    AND    rc2.ReceiptSeq BETWEEN (SELECT IFNULL(MAX(tmp.ReceiptSeq), 0) FROM T_ReceiptControl tmp, AT_ReceiptControl tmp2 WHERE tmp.ReceiptSeq = tmp2.ReceiptSeq AND tmp.ReceiptSeq < rc.ReceiptSeq AND tmp.OrderSeq = rc.OrderSeq AND tmp2.Rct_CancelFlg = 1) + 1 AND rc.ReceiptSeq - 1
    UNION ALL
    SELECT DATE(rc.ReceiptProcessDate)
    ,      rc2.ReceiptDate
    ,      rc2.DepositDate
    ,      c.Class3
    ,      F_GetCode(162, 2, 1)
    ,      rc2.CheckingDamageInterestAmount * -1
    ,      c1.Class3
    ,      0
    ,      o.EnterpriseId AS EnterpriseId
    ,      -1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_ReceiptControl rc2 ON (rc2.ReceiptSeq < rc.ReceiptSeq AND rc2.OrderSeq = rc.OrderSeq)
           INNER JOIN AT_ReceiptControl arc2 ON (rc2.ReceiptSeq = arc2.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 101 AND c.KeyCode = rc2.ReceiptAgentId)
    WHERE  (rc2.ReceiptDate < :AccountingMonth AND rc.ReceiptProcessDate >= (SELECT MAX(tmp.BusinessDate + INTERVAL 1 DAY) FROM (SELECT BusinessDate FROM T_BusinessCalendar WHERE BusinessDate >= :AccountingMonth AND BusinessFlg = 1 LIMIT 3) tmp))
    AND    (c1.Class1 = 0 OR (c1.Class1 <> 0 AND DATE(o.OemClaimTransDate) <= DATE(rc.ReceiptDate)) OR (c1.Class1 <> 0 AND rc.ReceiptDate < o.OemClaimTransDate AND o.OemClaimTransDate <= DATE(rc.ReceiptProcessDate) AND DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))
    AND    rc2.ReceiptAgentId IS NOT NULL
    AND    rc2.CheckingDamageInterestAmount <> 0
    AND    arc.Rct_CancelFlg = 1
    AND    rc2.ReceiptSeq BETWEEN (SELECT IFNULL(MAX(tmp.ReceiptSeq), 0) FROM T_ReceiptControl tmp, AT_ReceiptControl tmp2 WHERE tmp.ReceiptSeq = tmp2.ReceiptSeq AND tmp.ReceiptSeq < rc.ReceiptSeq AND tmp.OrderSeq = rc.OrderSeq AND tmp2.Rct_CancelFlg = 1) + 1 AND rc.ReceiptSeq - 1

    UNION ALL
    -- [SQL04]
    SELECT DATE(rc.ReceiptProcessDate)
    ,      rc2.ReceiptDate
    ,      rc2.DepositDate
    ,      c.Class3
    ,      F_GetCode(162, 3, 1)
    ,      rc2.CheckingClaimFee * -1
    ,      c1.Class3
    ,      0
    ,      o.EnterpriseId AS EnterpriseId
    ,      -1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_ReceiptControl rc2 ON (rc2.ReceiptSeq < rc.ReceiptSeq AND rc2.OrderSeq = rc.OrderSeq)
           INNER JOIN AT_ReceiptControl arc2 ON (rc2.ReceiptSeq = arc2.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 101 AND c.KeyCode = rc2.ReceiptAgentId)
    WHERE  (rc2.ReceiptDate >= :AccountingMonth AND rc2.ReceiptDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    (c1.Class1 = 0 OR (c1.Class1 <> 0 AND DATE(o.OemClaimTransDate) <= DATE(rc.ReceiptDate)) OR (c1.Class1 <> 0 AND rc.ReceiptDate < o.OemClaimTransDate AND o.OemClaimTransDate <= DATE(rc.ReceiptProcessDate) AND DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))
    AND    rc2.ReceiptAgentId IS NOT NULL
    AND    rc2.CheckingClaimFee <> 0
    AND    arc.Rct_CancelFlg = 1
    AND    rc2.ReceiptSeq BETWEEN (SELECT IFNULL(MAX(tmp.ReceiptSeq), 0) FROM T_ReceiptControl tmp, AT_ReceiptControl tmp2 WHERE tmp.ReceiptSeq = tmp2.ReceiptSeq AND tmp.ReceiptSeq < rc.ReceiptSeq AND tmp.OrderSeq = rc.OrderSeq AND tmp2.Rct_CancelFlg = 1) + 1 AND rc.ReceiptSeq - 1
    UNION ALL
    SELECT DATE(rc.ReceiptProcessDate)
    ,      rc2.ReceiptDate
    ,      rc2.DepositDate
    ,      c.Class3
    ,      F_GetCode(162, 3, 1)
    ,      rc2.CheckingClaimFee * -1
    ,      c1.Class3
    ,      0
    ,      o.EnterpriseId AS EnterpriseId
    ,      -1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_ReceiptControl rc2 ON (rc2.ReceiptSeq < rc.ReceiptSeq AND rc2.OrderSeq = rc.OrderSeq)
           INNER JOIN AT_ReceiptControl arc2 ON (rc2.ReceiptSeq = arc2.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 101 AND c.KeyCode = rc2.ReceiptAgentId)
    WHERE  (rc2.ReceiptDate < :AccountingMonth AND rc.ReceiptProcessDate >= (SELECT MAX(tmp.BusinessDate + INTERVAL 1 DAY) FROM (SELECT BusinessDate FROM T_BusinessCalendar WHERE BusinessDate >= :AccountingMonth AND BusinessFlg = 1 LIMIT 3) tmp))
    AND    (c1.Class1 = 0 OR (c1.Class1 <> 0 AND DATE(o.OemClaimTransDate) <= DATE(rc.ReceiptDate)) OR (c1.Class1 <> 0 AND rc.ReceiptDate < o.OemClaimTransDate AND o.OemClaimTransDate <= DATE(rc.ReceiptProcessDate) AND DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))
    AND    rc2.ReceiptAgentId IS NOT NULL
    AND    rc2.CheckingClaimFee <> 0
    AND    arc.Rct_CancelFlg = 1
    AND    rc2.ReceiptSeq BETWEEN (SELECT IFNULL(MAX(tmp.ReceiptSeq), 0) FROM T_ReceiptControl tmp, AT_ReceiptControl tmp2 WHERE tmp.ReceiptSeq = tmp2.ReceiptSeq AND tmp.ReceiptSeq < rc.ReceiptSeq AND tmp.OrderSeq = rc.OrderSeq AND tmp2.Rct_CancelFlg = 1) + 1 AND rc.ReceiptSeq - 1

    /* (銀行 ReceiptClass=3) */
    UNION ALL
    -- [SQL05]
    SELECT DATE(rc.ReceiptProcessDate)
    ,      rc2.ReceiptDate
    ,      rc2.DepositDate
    ,      c.Class3
    ,      F_GetCode(162, 1, 1)
    ,      rc2.CheckingUseAmount * -1
    ,      c1.Class3
    ,      0
    ,      o.EnterpriseId AS EnterpriseId
    ,      -1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_ReceiptControl rc2 ON (rc2.ReceiptSeq < rc.ReceiptSeq AND rc2.OrderSeq = rc.OrderSeq)
           INNER JOIN AT_ReceiptControl arc2 ON (rc2.ReceiptSeq = arc2.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 151 AND c.KeyCode = rc2.BranchBankId)
    WHERE  (rc2.ReceiptDate >= :AccountingMonth AND rc2.ReceiptDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    (c1.Class1 = 0 OR (c1.Class1 <> 0 AND DATE(o.OemClaimTransDate) <= DATE(rc.ReceiptDate)) OR (c1.Class1 <> 0 AND rc.ReceiptDate < o.OemClaimTransDate AND o.OemClaimTransDate <= DATE(rc.ReceiptProcessDate) AND DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))
    AND    rc2.ReceiptAgentId IS NULL
    AND    rc2.ReceiptClass = 3
    AND    rc2.CheckingUseAmount <> 0
    AND    arc.Rct_CancelFlg = 1
    AND    rc2.ReceiptSeq BETWEEN (SELECT IFNULL(MAX(tmp.ReceiptSeq), 0) FROM T_ReceiptControl tmp, AT_ReceiptControl tmp2 WHERE tmp.ReceiptSeq = tmp2.ReceiptSeq AND tmp.ReceiptSeq < rc.ReceiptSeq AND tmp.OrderSeq = rc.OrderSeq AND tmp2.Rct_CancelFlg = 1) + 1 AND rc.ReceiptSeq - 1

    UNION ALL
    SELECT DATE(rc.ReceiptProcessDate)
    ,      rc2.ReceiptDate
    ,      rc2.DepositDate
    ,      c.Class3
    ,      F_GetCode(162, 1, 1)
    ,      rc2.CheckingUseAmount * -1
    ,      c1.Class3
    ,      0
    ,      o.EnterpriseId AS EnterpriseId
    ,      -1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_ReceiptControl rc2 ON (rc2.ReceiptSeq < rc.ReceiptSeq AND rc2.OrderSeq = rc.OrderSeq)
           INNER JOIN AT_ReceiptControl arc2 ON (rc2.ReceiptSeq = arc2.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 151 AND c.KeyCode = rc2.BranchBankId)
    WHERE  (rc2.ReceiptDate < :AccountingMonth AND rc.ReceiptProcessDate >= (SELECT MAX(tmp.BusinessDate + INTERVAL 1 DAY) FROM (SELECT BusinessDate FROM T_BusinessCalendar WHERE BusinessDate >= :AccountingMonth AND BusinessFlg = 1 LIMIT 3) tmp))
    AND    (c1.Class1 = 0 OR (c1.Class1 <> 0 AND DATE(o.OemClaimTransDate) <= DATE(rc.ReceiptDate)) OR (c1.Class1 <> 0 AND rc.ReceiptDate < o.OemClaimTransDate AND o.OemClaimTransDate <= DATE(rc.ReceiptProcessDate) AND DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))
    AND    rc2.ReceiptAgentId IS NULL
    AND    rc2.ReceiptClass = 3
    AND    rc2.CheckingUseAmount <> 0
    AND    arc.Rct_CancelFlg = 1
    AND    rc2.ReceiptSeq BETWEEN (SELECT IFNULL(MAX(tmp.ReceiptSeq), 0) FROM T_ReceiptControl tmp, AT_ReceiptControl tmp2 WHERE tmp.ReceiptSeq = tmp2.ReceiptSeq AND tmp.ReceiptSeq < rc.ReceiptSeq AND tmp.OrderSeq = rc.OrderSeq AND tmp2.Rct_CancelFlg = 1) + 1 AND rc.ReceiptSeq - 1

    UNION ALL
    -- [SQL06]
    SELECT DATE(rc.ReceiptProcessDate)
    ,      rc2.ReceiptDate
    ,      rc2.DepositDate
    ,      c.Class3
    ,      F_GetCode(162, 2, 1)
    ,      rc2.CheckingDamageInterestAmount * -1
    ,      c1.Class3
    ,      0
    ,      o.EnterpriseId AS EnterpriseId
    ,      -1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_ReceiptControl rc2 ON (rc2.ReceiptSeq < rc.ReceiptSeq AND rc2.OrderSeq = rc.OrderSeq)
           INNER JOIN AT_ReceiptControl arc2 ON (rc2.ReceiptSeq = arc2.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 151 AND c.KeyCode = rc2.BranchBankId)
    WHERE  (rc2.ReceiptDate >= :AccountingMonth AND rc2.ReceiptDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    (c1.Class1 = 0 OR (c1.Class1 <> 0 AND DATE(o.OemClaimTransDate) <= DATE(rc.ReceiptDate)) OR (c1.Class1 <> 0 AND rc.ReceiptDate < o.OemClaimTransDate AND o.OemClaimTransDate <= DATE(rc.ReceiptProcessDate) AND DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))
    AND    rc2.ReceiptAgentId IS NULL
    AND    rc2.ReceiptClass = 3
    AND    rc2.CheckingDamageInterestAmount <> 0
    AND    arc.Rct_CancelFlg = 1
    AND    rc2.ReceiptSeq BETWEEN (SELECT IFNULL(MAX(tmp.ReceiptSeq), 0) FROM T_ReceiptControl tmp, AT_ReceiptControl tmp2 WHERE tmp.ReceiptSeq = tmp2.ReceiptSeq AND tmp.ReceiptSeq < rc.ReceiptSeq AND tmp.OrderSeq = rc.OrderSeq AND tmp2.Rct_CancelFlg = 1) + 1 AND rc.ReceiptSeq - 1
    UNION ALL
    SELECT DATE(rc.ReceiptProcessDate)
    ,      rc2.ReceiptDate
    ,      rc2.DepositDate
    ,      c.Class3
    ,      F_GetCode(162, 2, 1)
    ,      rc2.CheckingDamageInterestAmount * -1
    ,      c1.Class3
    ,      0
    ,      o.EnterpriseId AS EnterpriseId
    ,      -1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_ReceiptControl rc2 ON (rc2.ReceiptSeq < rc.ReceiptSeq AND rc2.OrderSeq = rc.OrderSeq)
           INNER JOIN AT_ReceiptControl arc2 ON (rc2.ReceiptSeq = arc2.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 151 AND c.KeyCode = rc2.BranchBankId)
    WHERE  (rc2.ReceiptDate < :AccountingMonth AND rc.ReceiptProcessDate >= (SELECT MAX(tmp.BusinessDate + INTERVAL 1 DAY) FROM (SELECT BusinessDate FROM T_BusinessCalendar WHERE BusinessDate >= :AccountingMonth AND BusinessFlg = 1 LIMIT 3) tmp))
    AND    (c1.Class1 = 0 OR (c1.Class1 <> 0 AND DATE(o.OemClaimTransDate) <= DATE(rc.ReceiptDate)) OR (c1.Class1 <> 0 AND rc.ReceiptDate < o.OemClaimTransDate AND o.OemClaimTransDate <= DATE(rc.ReceiptProcessDate) AND DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))
    AND    rc2.ReceiptAgentId IS NULL
    AND    rc2.ReceiptClass = 3
    AND    rc2.CheckingDamageInterestAmount <> 0
    AND    arc.Rct_CancelFlg = 1
    AND    rc2.ReceiptSeq BETWEEN (SELECT IFNULL(MAX(tmp.ReceiptSeq), 0) FROM T_ReceiptControl tmp, AT_ReceiptControl tmp2 WHERE tmp.ReceiptSeq = tmp2.ReceiptSeq AND tmp.ReceiptSeq < rc.ReceiptSeq AND tmp.OrderSeq = rc.OrderSeq AND tmp2.Rct_CancelFlg = 1) + 1 AND rc.ReceiptSeq - 1

    UNION ALL
    -- [SQL07]
    SELECT DATE(rc.ReceiptProcessDate)
    ,      rc2.ReceiptDate
    ,      rc2.DepositDate
    ,      c.Class3
    ,      F_GetCode(162, 3, 1)
    ,      rc2.CheckingClaimFee * -1
    ,      c1.Class3
    ,      0
    ,      o.EnterpriseId AS EnterpriseId
    ,      -1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_ReceiptControl rc2 ON (rc2.ReceiptSeq < rc.ReceiptSeq AND rc2.OrderSeq = rc.OrderSeq)
           INNER JOIN AT_ReceiptControl arc2 ON (rc2.ReceiptSeq = arc2.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 151 AND c.KeyCode = rc2.BranchBankId)
    WHERE  (rc2.ReceiptDate >= :AccountingMonth AND rc2.ReceiptDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    (c1.Class1 = 0 OR (c1.Class1 <> 0 AND DATE(o.OemClaimTransDate) <= DATE(rc.ReceiptDate)) OR (c1.Class1 <> 0 AND rc.ReceiptDate < o.OemClaimTransDate AND o.OemClaimTransDate <= DATE(rc.ReceiptProcessDate) AND DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))
    AND    rc2.ReceiptAgentId IS NULL
    AND    rc2.ReceiptClass = 3
    AND    rc2.CheckingClaimFee <> 0
    AND    arc.Rct_CancelFlg = 1
    AND    rc2.ReceiptSeq BETWEEN (SELECT IFNULL(MAX(tmp.ReceiptSeq), 0) FROM T_ReceiptControl tmp, AT_ReceiptControl tmp2 WHERE tmp.ReceiptSeq = tmp2.ReceiptSeq AND tmp.ReceiptSeq < rc.ReceiptSeq AND tmp.OrderSeq = rc.OrderSeq AND tmp2.Rct_CancelFlg = 1) + 1 AND rc.ReceiptSeq - 1
    UNION ALL
    SELECT DATE(rc.ReceiptProcessDate)
    ,      rc2.ReceiptDate
    ,      rc2.DepositDate
    ,      c.Class3
    ,      F_GetCode(162, 3, 1)
    ,      rc2.CheckingClaimFee * -1
    ,      c1.Class3
    ,      0
    ,      o.EnterpriseId AS EnterpriseId
    ,      -1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_ReceiptControl rc2 ON (rc2.ReceiptSeq < rc.ReceiptSeq AND rc2.OrderSeq = rc.OrderSeq)
           INNER JOIN AT_ReceiptControl arc2 ON (rc2.ReceiptSeq = arc2.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 151 AND c.KeyCode = rc2.BranchBankId)
    WHERE  (rc2.ReceiptDate < :AccountingMonth AND rc.ReceiptProcessDate >= (SELECT MAX(tmp.BusinessDate + INTERVAL 1 DAY) FROM (SELECT BusinessDate FROM T_BusinessCalendar WHERE BusinessDate >= :AccountingMonth AND BusinessFlg = 1 LIMIT 3) tmp))
    AND    (c1.Class1 = 0 OR (c1.Class1 <> 0 AND DATE(o.OemClaimTransDate) <= DATE(rc.ReceiptDate)) OR (c1.Class1 <> 0 AND rc.ReceiptDate < o.OemClaimTransDate AND o.OemClaimTransDate <= DATE(rc.ReceiptProcessDate) AND DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))
    AND    rc2.ReceiptAgentId IS NULL
    AND    rc2.ReceiptClass = 3
    AND    rc2.CheckingClaimFee <> 0
    AND    arc.Rct_CancelFlg = 1
    AND    rc2.ReceiptSeq BETWEEN (SELECT IFNULL(MAX(tmp.ReceiptSeq), 0) FROM T_ReceiptControl tmp, AT_ReceiptControl tmp2 WHERE tmp.ReceiptSeq = tmp2.ReceiptSeq AND tmp.ReceiptSeq < rc.ReceiptSeq AND tmp.OrderSeq = rc.OrderSeq AND tmp2.Rct_CancelFlg = 1) + 1 AND rc.ReceiptSeq - 1

    /* (郵便局 ReceiptClass=2) */
    UNION ALL
    -- [SQL08]
    SELECT DATE(rc.ReceiptProcessDate)
    ,      rc2.ReceiptDate
    ,      rc2.DepositDate
    ,      c.Class3
    ,      F_GetCode(162, 1, 1)
    ,      rc2.CheckingUseAmount * -1
    ,      c1.Class3
    ,      0
    ,      o.EnterpriseId AS EnterpriseId
    ,      -1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_ReceiptControl rc2 ON (rc2.ReceiptSeq < rc.ReceiptSeq AND rc2.OrderSeq = rc.OrderSeq)
           INNER JOIN AT_ReceiptControl arc2 ON (rc2.ReceiptSeq = arc2.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 166 AND c.Class1 = arc2.AccountNumber)
    WHERE  (rc2.ReceiptDate >= :AccountingMonth AND rc2.ReceiptDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    (c1.Class1 = 0 OR (c1.Class1 <> 0 AND DATE(o.OemClaimTransDate) <= DATE(rc.ReceiptDate)) OR (c1.Class1 <> 0 AND rc.ReceiptDate < o.OemClaimTransDate AND o.OemClaimTransDate <= DATE(rc.ReceiptProcessDate) AND DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))
    AND    rc2.ReceiptAgentId IS NULL
    AND    rc2.ReceiptClass = 2
    AND    rc2.CheckingUseAmount <> 0
    AND    arc.Rct_CancelFlg = 1
    AND    rc2.ReceiptSeq BETWEEN (SELECT IFNULL(MAX(tmp.ReceiptSeq), 0) FROM T_ReceiptControl tmp, AT_ReceiptControl tmp2 WHERE tmp.ReceiptSeq = tmp2.ReceiptSeq AND tmp.ReceiptSeq < rc.ReceiptSeq AND tmp.OrderSeq = rc.OrderSeq AND tmp2.Rct_CancelFlg = 1) + 1 AND rc.ReceiptSeq - 1
    UNION ALL
    SELECT DATE(rc.ReceiptProcessDate)
    ,      rc2.ReceiptDate
    ,      rc2.DepositDate
    ,      c.Class3
    ,      F_GetCode(162, 1, 1)
    ,      rc2.CheckingUseAmount * -1
    ,      c1.Class3
    ,      0
    ,      o.EnterpriseId AS EnterpriseId
    ,      -1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_ReceiptControl rc2 ON (rc2.ReceiptSeq < rc.ReceiptSeq AND rc2.OrderSeq = rc.OrderSeq)
           INNER JOIN AT_ReceiptControl arc2 ON (rc2.ReceiptSeq = arc2.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 166 AND c.Class1 = arc2.AccountNumber)
    WHERE  (rc2.ReceiptDate < :AccountingMonth AND rc.ReceiptProcessDate >= (SELECT MAX(tmp.BusinessDate + INTERVAL 1 DAY) FROM (SELECT BusinessDate FROM T_BusinessCalendar WHERE BusinessDate >= :AccountingMonth AND BusinessFlg = 1 LIMIT 3) tmp))
    AND    (c1.Class1 = 0 OR (c1.Class1 <> 0 AND DATE(o.OemClaimTransDate) <= DATE(rc.ReceiptDate)) OR (c1.Class1 <> 0 AND rc.ReceiptDate < o.OemClaimTransDate AND o.OemClaimTransDate <= DATE(rc.ReceiptProcessDate) AND DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))
    AND    rc2.ReceiptAgentId IS NULL
    AND    rc2.ReceiptClass = 2
    AND    rc2.CheckingUseAmount <> 0
    AND    arc.Rct_CancelFlg = 1
    AND    rc2.ReceiptSeq BETWEEN (SELECT IFNULL(MAX(tmp.ReceiptSeq), 0) FROM T_ReceiptControl tmp, AT_ReceiptControl tmp2 WHERE tmp.ReceiptSeq = tmp2.ReceiptSeq AND tmp.ReceiptSeq < rc.ReceiptSeq AND tmp.OrderSeq = rc.OrderSeq AND tmp2.Rct_CancelFlg = 1) + 1 AND rc.ReceiptSeq - 1

    UNION ALL
    -- [SQL09]
    SELECT DATE(rc.ReceiptProcessDate)
    ,      rc2.ReceiptDate
    ,      rc2.DepositDate
    ,      c.Class3
    ,      F_GetCode(162, 2, 1)
    ,      rc2.CheckingDamageInterestAmount * -1
    ,      c1.Class3
    ,      0
    ,      o.EnterpriseId AS EnterpriseId
    ,      -1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_ReceiptControl rc2 ON (rc2.ReceiptSeq < rc.ReceiptSeq AND rc2.OrderSeq = rc.OrderSeq)
           INNER JOIN AT_ReceiptControl arc2 ON (rc2.ReceiptSeq = arc2.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 166 AND c.Class1 = arc2.AccountNumber)
    WHERE  (rc2.ReceiptDate >= :AccountingMonth AND rc2.ReceiptDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    (c1.Class1 = 0 OR (c1.Class1 <> 0 AND DATE(o.OemClaimTransDate) <= DATE(rc.ReceiptDate)) OR (c1.Class1 <> 0 AND rc.ReceiptDate < o.OemClaimTransDate AND o.OemClaimTransDate <= DATE(rc.ReceiptProcessDate) AND DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))
    AND    rc2.ReceiptAgentId IS NULL
    AND    rc2.ReceiptClass = 2
    AND    rc2.CheckingDamageInterestAmount <> 0
    AND    arc.Rct_CancelFlg = 1
    AND    rc2.ReceiptSeq BETWEEN (SELECT IFNULL(MAX(tmp.ReceiptSeq), 0) FROM T_ReceiptControl tmp, AT_ReceiptControl tmp2 WHERE tmp.ReceiptSeq = tmp2.ReceiptSeq AND tmp.ReceiptSeq < rc.ReceiptSeq AND tmp.OrderSeq = rc.OrderSeq AND tmp2.Rct_CancelFlg = 1) + 1 AND rc.ReceiptSeq - 1
    UNION ALL
    SELECT DATE(rc.ReceiptProcessDate)
    ,      rc2.ReceiptDate
    ,      rc2.DepositDate
    ,      c.Class3
    ,      F_GetCode(162, 2, 1)
    ,      rc2.CheckingDamageInterestAmount * -1
    ,      c1.Class3
    ,      0
    ,      o.EnterpriseId AS EnterpriseId
    ,      -1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_ReceiptControl rc2 ON (rc2.ReceiptSeq < rc.ReceiptSeq AND rc2.OrderSeq = rc.OrderSeq)
           INNER JOIN AT_ReceiptControl arc2 ON (rc2.ReceiptSeq = arc2.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 166 AND c.Class1 = arc2.AccountNumber)
    WHERE  (rc2.ReceiptDate < :AccountingMonth AND rc.ReceiptProcessDate >= (SELECT MAX(tmp.BusinessDate + INTERVAL 1 DAY) FROM (SELECT BusinessDate FROM T_BusinessCalendar WHERE BusinessDate >= :AccountingMonth AND BusinessFlg = 1 LIMIT 3) tmp))
    AND    (c1.Class1 = 0 OR (c1.Class1 <> 0 AND DATE(o.OemClaimTransDate) <= DATE(rc.ReceiptDate)) OR (c1.Class1 <> 0 AND rc.ReceiptDate < o.OemClaimTransDate AND o.OemClaimTransDate <= DATE(rc.ReceiptProcessDate) AND DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))
    AND    rc2.ReceiptAgentId IS NULL
    AND    rc2.ReceiptClass = 2
    AND    rc2.CheckingDamageInterestAmount <> 0
    AND    arc.Rct_CancelFlg = 1
    AND    rc2.ReceiptSeq BETWEEN (SELECT IFNULL(MAX(tmp.ReceiptSeq), 0) FROM T_ReceiptControl tmp, AT_ReceiptControl tmp2 WHERE tmp.ReceiptSeq = tmp2.ReceiptSeq AND tmp.ReceiptSeq < rc.ReceiptSeq AND tmp.OrderSeq = rc.OrderSeq AND tmp2.Rct_CancelFlg = 1) + 1 AND rc.ReceiptSeq - 1

    UNION ALL
    -- [SQL10]
    SELECT DATE(rc.ReceiptProcessDate)
    ,      rc2.ReceiptDate
    ,      rc2.DepositDate
    ,      c.Class3
    ,      F_GetCode(162, 3, 1)
    ,      rc2.CheckingClaimFee * -1
    ,      c1.Class3
    ,      0
    ,      o.EnterpriseId AS EnterpriseId
    ,      -1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_ReceiptControl rc2 ON (rc2.ReceiptSeq < rc.ReceiptSeq AND rc2.OrderSeq = rc.OrderSeq)
           INNER JOIN AT_ReceiptControl arc2 ON (rc2.ReceiptSeq = arc2.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 166 AND c.Class1 = arc2.AccountNumber)
    WHERE  (rc2.ReceiptDate >= :AccountingMonth AND rc2.ReceiptDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    (c1.Class1 = 0 OR (c1.Class1 <> 0 AND DATE(o.OemClaimTransDate) <= DATE(rc.ReceiptDate)) OR (c1.Class1 <> 0 AND rc.ReceiptDate < o.OemClaimTransDate AND o.OemClaimTransDate <= DATE(rc.ReceiptProcessDate) AND DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))
    AND    rc2.ReceiptAgentId IS NULL
    AND    rc2.ReceiptClass = 2
    AND    rc2.CheckingClaimFee <> 0
    AND    arc.Rct_CancelFlg = 1
    AND    rc2.ReceiptSeq BETWEEN (SELECT IFNULL(MAX(tmp.ReceiptSeq), 0) FROM T_ReceiptControl tmp, AT_ReceiptControl tmp2 WHERE tmp.ReceiptSeq = tmp2.ReceiptSeq AND tmp.ReceiptSeq < rc.ReceiptSeq AND tmp.OrderSeq = rc.OrderSeq AND tmp2.Rct_CancelFlg = 1) + 1 AND rc.ReceiptSeq - 1
    UNION ALL
    SELECT DATE(rc.ReceiptProcessDate)
    ,      rc2.ReceiptDate
    ,      rc2.DepositDate
    ,      c.Class3
    ,      F_GetCode(162, 3, 1)
    ,      rc2.CheckingClaimFee * -1
    ,      c1.Class3
    ,      0
    ,      o.EnterpriseId AS EnterpriseId
    ,      -1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_ReceiptControl rc2 ON (rc2.ReceiptSeq < rc.ReceiptSeq AND rc2.OrderSeq = rc.OrderSeq)
           INNER JOIN AT_ReceiptControl arc2 ON (rc2.ReceiptSeq = arc2.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 166 AND c.Class1 = arc2.AccountNumber)
    WHERE  (rc2.ReceiptDate < :AccountingMonth AND rc.ReceiptProcessDate >= (SELECT MAX(tmp.BusinessDate + INTERVAL 1 DAY) FROM (SELECT BusinessDate FROM T_BusinessCalendar WHERE BusinessDate >= :AccountingMonth AND BusinessFlg = 1 LIMIT 3) tmp))
    AND    (c1.Class1 = 0 OR (c1.Class1 <> 0 AND DATE(o.OemClaimTransDate) <= DATE(rc.ReceiptDate)) OR (c1.Class1 <> 0 AND rc.ReceiptDate < o.OemClaimTransDate AND o.OemClaimTransDate <= DATE(rc.ReceiptProcessDate) AND DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))
    AND    rc2.ReceiptAgentId IS NULL
    AND    rc2.ReceiptClass = 2
    AND    rc2.CheckingClaimFee <> 0
    AND    arc.Rct_CancelFlg = 1
    AND    rc2.ReceiptSeq BETWEEN (SELECT IFNULL(MAX(tmp.ReceiptSeq), 0) FROM T_ReceiptControl tmp, AT_ReceiptControl tmp2 WHERE tmp.ReceiptSeq = tmp2.ReceiptSeq AND tmp.ReceiptSeq < rc.ReceiptSeq AND tmp.OrderSeq = rc.OrderSeq AND tmp2.Rct_CancelFlg = 1) + 1 AND rc.ReceiptSeq - 1

    /* (その他 ReceiptClass=9) */
    UNION ALL
    -- [SQL11]
    SELECT DATE(rc.ReceiptProcessDate)
    ,      rc2.ReceiptDate
    ,      rc2.DepositDate
    ,      c.Class3
    ,      F_GetCode(162, 1, 1)
    ,      rc2.CheckingUseAmount * -1
    ,      c1.Class3
    ,      0
    ,      o.EnterpriseId AS EnterpriseId
    ,      -1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_ReceiptControl rc2 ON (rc2.ReceiptSeq < rc.ReceiptSeq AND rc2.OrderSeq = rc.OrderSeq)
           INNER JOIN AT_ReceiptControl arc2 ON (rc2.ReceiptSeq = arc2.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 155 AND c.KeyCode = arc2.ClassDetails)
    WHERE  (rc2.ReceiptDate >= :AccountingMonth AND rc2.ReceiptDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    (c1.Class1 = 0 OR (c1.Class1 <> 0 AND DATE(o.OemClaimTransDate) <= DATE(rc.ReceiptDate)) OR (c1.Class1 <> 0 AND rc.ReceiptDate < o.OemClaimTransDate AND o.OemClaimTransDate <= DATE(rc.ReceiptProcessDate) AND DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))
    AND    rc2.ReceiptAgentId IS NULL
    AND    rc2.ReceiptClass = 9
    AND    rc2.CheckingUseAmount <> 0
    AND    arc.Rct_CancelFlg = 1
    AND    rc2.ReceiptSeq BETWEEN (SELECT IFNULL(MAX(tmp.ReceiptSeq), 0) FROM T_ReceiptControl tmp, AT_ReceiptControl tmp2 WHERE tmp.ReceiptSeq = tmp2.ReceiptSeq AND tmp.ReceiptSeq < rc.ReceiptSeq AND tmp.OrderSeq = rc.OrderSeq AND tmp2.Rct_CancelFlg = 1) + 1 AND rc.ReceiptSeq - 1

    UNION ALL
    SELECT DATE(rc.ReceiptProcessDate)
    ,      rc2.ReceiptDate
    ,      rc2.DepositDate
    ,      c.Class3
    ,      F_GetCode(162, 1, 1)
    ,      rc2.CheckingUseAmount * -1
    ,      c1.Class3
    ,      0
    ,      o.EnterpriseId AS EnterpriseId
    ,      -1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_ReceiptControl rc2 ON (rc2.ReceiptSeq < rc.ReceiptSeq AND rc2.OrderSeq = rc.OrderSeq)
           INNER JOIN AT_ReceiptControl arc2 ON (rc2.ReceiptSeq = arc2.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 155 AND c.KeyCode = arc2.ClassDetails)
    WHERE  (rc2.ReceiptDate < :AccountingMonth AND rc.ReceiptProcessDate >= (SELECT MAX(tmp.BusinessDate + INTERVAL 1 DAY) FROM (SELECT BusinessDate FROM T_BusinessCalendar WHERE BusinessDate >= :AccountingMonth AND BusinessFlg = 1 LIMIT 3) tmp))
    AND    (c1.Class1 = 0 OR (c1.Class1 <> 0 AND DATE(o.OemClaimTransDate) <= DATE(rc.ReceiptDate)) OR (c1.Class1 <> 0 AND rc.ReceiptDate < o.OemClaimTransDate AND o.OemClaimTransDate <= DATE(rc.ReceiptProcessDate) AND DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))
    AND    rc2.ReceiptAgentId IS NULL
    AND    rc2.ReceiptClass = 9
    AND    rc2.CheckingUseAmount <> 0
    AND    arc.Rct_CancelFlg = 1
    AND    rc2.ReceiptSeq BETWEEN (SELECT IFNULL(MAX(tmp.ReceiptSeq), 0) FROM T_ReceiptControl tmp, AT_ReceiptControl tmp2 WHERE tmp.ReceiptSeq = tmp2.ReceiptSeq AND tmp.ReceiptSeq < rc.ReceiptSeq AND tmp.OrderSeq = rc.OrderSeq AND tmp2.Rct_CancelFlg = 1) + 1 AND rc.ReceiptSeq - 1

    UNION ALL
    -- [SQL12]
    SELECT DATE(rc.ReceiptProcessDate)
    ,      rc2.ReceiptDate
    ,      rc2.DepositDate
    ,      c.Class3
    ,      F_GetCode(162, 2, 1)
    ,      rc2.CheckingDamageInterestAmount * -1
    ,      c1.Class3
    ,      0
    ,      o.EnterpriseId AS EnterpriseId
    ,      -1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_ReceiptControl rc2 ON (rc2.ReceiptSeq < rc.ReceiptSeq AND rc2.OrderSeq = rc.OrderSeq)
           INNER JOIN AT_ReceiptControl arc2 ON (rc2.ReceiptSeq = arc2.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 155 AND c.KeyCode = arc2.ClassDetails)
    WHERE  (rc2.ReceiptDate >= :AccountingMonth AND rc2.ReceiptDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    (c1.Class1 = 0 OR (c1.Class1 <> 0 AND DATE(o.OemClaimTransDate) <= DATE(rc.ReceiptDate)) OR (c1.Class1 <> 0 AND rc.ReceiptDate < o.OemClaimTransDate AND o.OemClaimTransDate <= DATE(rc.ReceiptProcessDate) AND DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))
    AND    rc2.ReceiptAgentId IS NULL
    AND    rc2.ReceiptClass = 9
    AND    rc2.CheckingDamageInterestAmount <> 0
    AND    arc.Rct_CancelFlg = 1
    AND    rc2.ReceiptSeq BETWEEN (SELECT IFNULL(MAX(tmp.ReceiptSeq), 0) FROM T_ReceiptControl tmp, AT_ReceiptControl tmp2 WHERE tmp.ReceiptSeq = tmp2.ReceiptSeq AND tmp.ReceiptSeq < rc.ReceiptSeq AND tmp.OrderSeq = rc.OrderSeq AND tmp2.Rct_CancelFlg = 1) + 1 AND rc.ReceiptSeq - 1
    UNION ALL
    SELECT DATE(rc.ReceiptProcessDate)
    ,      rc2.ReceiptDate
    ,      rc2.DepositDate
    ,      c.Class3
    ,      F_GetCode(162, 2, 1)
    ,      rc2.CheckingDamageInterestAmount * -1
    ,      c1.Class3
    ,      0
    ,      o.EnterpriseId AS EnterpriseId
    ,      -1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_ReceiptControl rc2 ON (rc2.ReceiptSeq < rc.ReceiptSeq AND rc2.OrderSeq = rc.OrderSeq)
           INNER JOIN AT_ReceiptControl arc2 ON (rc2.ReceiptSeq = arc2.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 155 AND c.KeyCode = arc2.ClassDetails)
    WHERE  (rc2.ReceiptDate < :AccountingMonth AND rc.ReceiptProcessDate >= (SELECT MAX(tmp.BusinessDate + INTERVAL 1 DAY) FROM (SELECT BusinessDate FROM T_BusinessCalendar WHERE BusinessDate >= :AccountingMonth AND BusinessFlg = 1 LIMIT 3) tmp))
    AND    (c1.Class1 = 0 OR (c1.Class1 <> 0 AND DATE(o.OemClaimTransDate) <= DATE(rc.ReceiptDate)) OR (c1.Class1 <> 0 AND rc.ReceiptDate < o.OemClaimTransDate AND o.OemClaimTransDate <= DATE(rc.ReceiptProcessDate) AND DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))
    AND    rc2.ReceiptAgentId IS NULL
    AND    rc2.ReceiptClass = 9
    AND    rc2.CheckingDamageInterestAmount <> 0
    AND    arc.Rct_CancelFlg = 1
    AND    rc2.ReceiptSeq BETWEEN (SELECT IFNULL(MAX(tmp.ReceiptSeq), 0) FROM T_ReceiptControl tmp, AT_ReceiptControl tmp2 WHERE tmp.ReceiptSeq = tmp2.ReceiptSeq AND tmp.ReceiptSeq < rc.ReceiptSeq AND tmp.OrderSeq = rc.OrderSeq AND tmp2.Rct_CancelFlg = 1) + 1 AND rc.ReceiptSeq - 1

    UNION ALL
    -- [SQL13]
    SELECT DATE(rc.ReceiptProcessDate)
    ,      rc2.ReceiptDate
    ,      rc2.DepositDate
    ,      c.Class3
    ,      F_GetCode(162, 3, 1)
    ,      rc2.CheckingClaimFee * -1
    ,      c1.Class3
    ,      0
    ,      o.EnterpriseId AS EnterpriseId
    ,      -1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_ReceiptControl rc2 ON (rc2.ReceiptSeq < rc.ReceiptSeq AND rc2.OrderSeq = rc.OrderSeq)
           INNER JOIN AT_ReceiptControl arc2 ON (rc2.ReceiptSeq = arc2.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 155 AND c.KeyCode = arc2.ClassDetails)
    WHERE  (rc2.ReceiptDate >= :AccountingMonth AND rc2.ReceiptDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    (c1.Class1 = 0 OR (c1.Class1 <> 0 AND DATE(o.OemClaimTransDate) <= DATE(rc.ReceiptDate)) OR (c1.Class1 <> 0 AND rc.ReceiptDate < o.OemClaimTransDate AND o.OemClaimTransDate <= DATE(rc.ReceiptProcessDate) AND DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))
    AND    rc2.ReceiptAgentId IS NULL
    AND    rc2.ReceiptClass = 9
    AND    rc2.CheckingClaimFee <> 0
    AND    arc.Rct_CancelFlg = 1
    AND    rc2.ReceiptSeq BETWEEN (SELECT IFNULL(MAX(tmp.ReceiptSeq), 0) FROM T_ReceiptControl tmp, AT_ReceiptControl tmp2 WHERE tmp.ReceiptSeq = tmp2.ReceiptSeq AND tmp.ReceiptSeq < rc.ReceiptSeq AND tmp.OrderSeq = rc.OrderSeq AND tmp2.Rct_CancelFlg = 1) + 1 AND rc.ReceiptSeq - 1
    UNION ALL
    SELECT DATE(rc.ReceiptProcessDate)
    ,      rc2.ReceiptDate
    ,      rc2.DepositDate
    ,      c.Class3
    ,      F_GetCode(162, 3, 1)
    ,      rc2.CheckingClaimFee * -1
    ,      c1.Class3
    ,      0
    ,      o.EnterpriseId AS EnterpriseId
    ,      -1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_ReceiptControl rc2 ON (rc2.ReceiptSeq < rc.ReceiptSeq AND rc2.OrderSeq = rc.OrderSeq)
           INNER JOIN AT_ReceiptControl arc2 ON (rc2.ReceiptSeq = arc2.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 155 AND c.KeyCode = arc2.ClassDetails)
    WHERE  (rc2.ReceiptDate < :AccountingMonth AND rc.ReceiptProcessDate >= (SELECT MAX(tmp.BusinessDate + INTERVAL 1 DAY) FROM (SELECT BusinessDate FROM T_BusinessCalendar WHERE BusinessDate >= :AccountingMonth AND BusinessFlg = 1 LIMIT 3) tmp))
    AND    (c1.Class1 = 0 OR (c1.Class1 <> 0 AND DATE(o.OemClaimTransDate) <= DATE(rc.ReceiptDate)) OR (c1.Class1 <> 0 AND rc.ReceiptDate < o.OemClaimTransDate AND o.OemClaimTransDate <= DATE(rc.ReceiptProcessDate) AND DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))
    AND    rc2.ReceiptAgentId IS NULL
    AND    rc2.ReceiptClass = 9
    AND    rc2.CheckingClaimFee <> 0
    AND    arc.Rct_CancelFlg = 1
    AND    rc2.ReceiptSeq BETWEEN (SELECT IFNULL(MAX(tmp.ReceiptSeq), 0) FROM T_ReceiptControl tmp, AT_ReceiptControl tmp2 WHERE tmp.ReceiptSeq = tmp2.ReceiptSeq AND tmp.ReceiptSeq < rc.ReceiptSeq AND tmp.OrderSeq = rc.OrderSeq AND tmp2.Rct_CancelFlg = 1) + 1 AND rc.ReceiptSeq - 1
EOQ;

        // SQL実行
        $this->app->dbAdapter->query($sql)->execute($prm);

        // 入金取消の相殺
        $sql = 'SELECT Seq, ProcessingDate, ReceiptDate, DepositDate, PaymentAccountTitle, PaymentTargetAccountTitle, Amount, OrderSeq FROM AW_MishukinHikeiMonth WHERE SUMType = 0 AND Amount < 0 ';  // 入金取消のレコードを取得
        $ri = $this->app->dbAdapter->query($sql)->execute(null);
        foreach ($ri as $row) {
            $wsql  = ' SELECT IFNULL(MIN(Seq), -1) As Seq ';
            $wsql .= ' FROM   AW_MishukinHikeiMonth ';
            $wsql .= ' WHERE  1 = 1 ';
            $wsql .= ' AND    OrderSeq = :OrderSeq ';
            $wsql .= ' AND    Amount = :Amount ';
            $wsql .= ' AND    ProcessingDate = :ProcessingDate ';
            $wsql .= ' AND    ReceiptDate = :ReceiptDate ';
            $wsql .= ' AND    PaymentAccountTitle = :PaymentAccountTitle ';
            $wsql .= ' AND    PaymentTargetAccountTitle = :PaymentTargetAccountTitle ';
            $wsql .= ' AND    TargetFlg = 1 ';

            $wprm = array(
                ':OrderSeq' => $row['OrderSeq'],
                ':Amount' => $row['Amount'] * -1,
                ':ProcessingDate' => $row['ProcessingDate'],
                ':ReceiptDate' => $row['ReceiptDate'],
                ':PaymentAccountTitle' => $row['PaymentAccountTitle'],
                ':PaymentTargetAccountTitle' => $row['PaymentTargetAccountTitle'],
            );

            $wrow = $this->app->dbAdapter->query($wsql)->execute($wprm)->current();

            if ($wrow['Seq'] > 0) {
                $wsql = ' UPDATE AW_MishukinHikeiMonth SET TargetFlg = 0 WHERE Seq = :Seq ';
                $this->app->dbAdapter->query($wsql)->execute(array(':Seq' => $row['Seq']));
                $this->app->dbAdapter->query($wsql)->execute(array(':Seq' => $wrow['Seq']));
            }

        }

        $basesql = ' SELECT * FROM AW_MishukinHikeiMonth WHERE TargetFlg = 1 ';

        $sql  = " SELECT tmp.ProcessingDate ";
        $sql .= " ,      :AccountingMonth AS AccountDate ";
        $sql .= " ,      tmp.ReceiptDate AS PaymentDate ";
        $sql .= " ,      tmp.DepositDate AS ReceiptProcessDate ";
        $sql .= " ,      tmp.PaymentAccountTitle ";
        $sql .= " ,      tmp.PaymentTargetAccountTitle ";
        $sql .= " ,      COUNT(1) AS PaymentNumber ";
        $sql .= " ,      SUM(tmp.Amount) AS Amount ";
        $sql .= " FROM ( ";
        $sql .= $basesql;
        $sql .= " ) tmp ";
        $sql .= " GROUP BY tmp.ProcessingDate, tmp.ReceiptDate, tmp.DepositDate, tmp.PaymentAccountTitle, tmp.PaymentTargetAccountTitle ";
        $sql .= " ORDER BY ProcessingDate, AccountDate, PaymentDate, ReceiptProcessDate, PaymentAccountTitle ";

        $ri = $this->app->dbAdapter->query($sql)->execute($prm);
        $datas = ResultInterfaceToArray($ri);

        $templateId = 'CKI24174_11'; // 未収金日計
        $templateClass = 0;
        $seq = 0;
        $templatePattern = 0;
        $tmpFileName = $tmpFilePath . '11.未収金日計_' . $formatNowStr . '.csv';

        $logicTemplate = new LogicTemplate($this->app->dbAdapter);
        $result = $logicTemplate->convertArraytoFile( $datas, $tmpFileName, $templateId, $templateClass, $seq, $templatePattern );

        //-----------------------------------------------------------
        // [01.直営日次統計表(月次)]用の処理
        //-----------------------------------------------------------
        $sumsql  = " SELECT 0 AS OemId ";
        $sumsql .= " ,      (CASE WHEN tmp.OemId = 0 THEN tmp.EnterpriseId ELSE 99999999 END) AS EnterpriseId ";
        $sumsql .= " ,      SUM(CASE WHEN tmp.SUMType = 0 THEN 1 ELSE 0 END) AS CNT_SUMType0 ";
        $sumsql .= " ,      SUM(CASE WHEN tmp.SUMType = 0 THEN tmp.Amount ELSE 0 END) * -1 AS SUM_Amount0 ";
        $sumsql .= " ,      SUM(CASE WHEN tmp.SUMType = 1 THEN 1 ELSE 0 END) AS CNT_SUMType1 ";
        $sumsql .= " ,      SUM(CASE WHEN tmp.SUMType = 1 THEN tmp.Amount ELSE 0 END) * -1 AS SUM_Amount1 ";
        $sumsql .= " ,      SUM(CASE WHEN tmp.SUMType = 2 THEN 1 ELSE 0 END) AS CNT_SUMType2 ";
        $sumsql .= " ,      SUM(CASE WHEN tmp.SUMType = 2 THEN tmp.Amount ELSE 0 END) * -1 AS SUM_Amount2 ";
        $sumsql .= " ,      SUM(CASE WHEN tmp.SUMType = 3 THEN 1 ELSE 0 END) AS CNT_SUMType3 ";
        $sumsql .= " ,      SUM(CASE WHEN tmp.SUMType = 3 THEN tmp.Amount ELSE 0 END) * -1 AS SUM_Amount3 ";
        $sumsql .= " FROM ( ";
        $sumsql .= $basesql;
        $sumsql .= " ) tmp ";
        $sumsql .= " GROUP BY (CASE WHEN OemId = 0 THEN EnterpriseId ELSE 99999999 END) ";
        $sumsql .= " WITH ROLLUP ";

        $ri = $this->app->dbAdapter->query($sumsql)->execute($prm);
        foreach ($ri as $row) {
            if ( $row['EnterpriseId'] == null ) {
                $this->_ary01[11] = $row;
            } else {
                $this->_aryEnt01[11][$row['EnterpriseId']] = $row;
            }
        }

        return $tmpFileName;
    }

    /**
     * CSV出力を行う[12. 仮払金日計(CSV)](月次版)
     *
     * @param string $formatNowStr 書式化年月日
     * @param string $tmpFilePath TEMP領域
     * @return ファイル名 ※出力が行われなかった場合は''を戻す
     */
    protected function CsvKaribaraikinHikeiMonth($formatNowStr, $tmpFilePath)
    {
        $mdlsp = new \models\Table\TableSystemProperty($this->app->dbAdapter);

        // 会計月の取得
        $accountingMonth = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'AccountingMonth');

        $prm = array (':AccountingMonth' => $accountingMonth);

        // ﾜｰｸﾃｰﾌﾞﾙﾄﾗﾝｹｰﾄ
        $sql = ' TRUNCATE TABLE AW_KaribaraikinHikeiMonth ';
        $this->app->dbAdapter->query($sql)->execute(null);

        $sql = <<<EOQ
INSERT INTO AW_KaribaraikinHikeiMonth(ProcessingDate,ReceiptDate,DepositDate,PaymentAccountTitle,PaymentTargetAccountTitle,Amount,OemId,SUMType,EnterpriseId,OutType,OrderSeq)
    /* 以下、入金関連 */
    /* (収納代行会社 ReceiptAgentId IS NOT NULL) */
    -- [SQL02]
    SELECT DATE(rc.ReceiptProcessDate) AS ProcessingDate
    ,      rc.ReceiptDate AS ReceiptDate
    ,      rc.DepositDate AS DepositDate
    ,      c.Class3 AS PaymentAccountTitle
    ,      F_GetCode(161, 1, 1) AS PaymentTargetAccountTitle
    ,      rc.CheckingUseAmount AS Amount
    ,      c1.Class3 AS OemId
    ,      0 AS SUMType
    ,      o.EnterpriseId AS EnterpriseId
    ,      1 AS OutType
    ,      rc.OrderSeq AS OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 101 AND c.KeyCode = rc.ReceiptAgentId)
    WHERE  (rc.ReceiptDate >= :AccountingMonth AND rc.ReceiptDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    (c1.Class1 <> 0 AND (o.OemClaimTransDate IS NULL OR (DATE(o.OemClaimTransDate) > DATE(rc.ReceiptDate) AND NOT (DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))))
    AND    rc.ReceiptAgentId IS NOT NULL
    AND    rc.CheckingUseAmount <> 0
    AND    arc.Rct_CancelFlg = 0
    UNION ALL
    SELECT DATE(rc.ReceiptProcessDate) AS ProcessingDate
    ,      rc.ReceiptDate AS ReceiptDate
    ,      rc.DepositDate AS DepositDate
    ,      c.Class3 AS PaymentAccountTitle
    ,      F_GetCode(161, 1, 1) AS PaymentTargetAccountTitle
    ,      rc.CheckingUseAmount AS Amount
    ,      c1.Class3 AS OemId
    ,      0 AS SUMType
    ,      o.EnterpriseId AS EnterpriseId
    ,      1 AS OutType
    ,      rc.OrderSeq AS OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 101 AND c.KeyCode = rc.ReceiptAgentId)
    WHERE  (rc.ReceiptDate < :AccountingMonth AND rc.ReceiptProcessDate >= (SELECT MAX(tmp.BusinessDate + INTERVAL 1 DAY) FROM (SELECT BusinessDate FROM T_BusinessCalendar WHERE BusinessDate >= :AccountingMonth AND BusinessFlg = 1 LIMIT 3) tmp))
    AND    (c1.Class1 <> 0 AND (o.OemClaimTransDate IS NULL OR (DATE(o.OemClaimTransDate) > DATE(rc.ReceiptDate) AND NOT (DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))))
    AND    rc.ReceiptAgentId IS NOT NULL
    AND    rc.CheckingUseAmount <> 0
    AND    arc.Rct_CancelFlg = 0

    UNION ALL
    -- [SQL03]
    SELECT DATE(rc.ReceiptProcessDate) AS ProcessingDate
    ,      rc.ReceiptDate AS ReceiptDate
    ,      rc.DepositDate AS DepositDate
    ,      c.Class3 AS PaymentAccountTitle
    ,      F_GetCode(161, 2, 1) AS PaymentTargetAccountTitle
    ,      rc.CheckingDamageInterestAmount AS Amount
    ,      c1.Class3 AS OemId
    ,      0 AS SUMType
    ,      o.EnterpriseId AS EnterpriseId
    ,      1 AS OutType
    ,      rc.OrderSeq AS OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 101 AND c.KeyCode = rc.ReceiptAgentId)
    WHERE  (rc.ReceiptDate >= :AccountingMonth AND rc.ReceiptDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    (c1.Class1 <> 0 AND (o.OemClaimTransDate IS NULL OR (DATE(o.OemClaimTransDate) > DATE(rc.ReceiptDate) AND NOT (DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))))
    AND    rc.ReceiptAgentId IS NOT NULL
    AND    rc.CheckingDamageInterestAmount <> 0
    AND    arc.Rct_CancelFlg = 0
    UNION ALL
    SELECT DATE(rc.ReceiptProcessDate) AS ProcessingDate
    ,      rc.ReceiptDate AS ReceiptDate
    ,      rc.DepositDate AS DepositDate
    ,      c.Class3 AS PaymentAccountTitle
    ,      F_GetCode(161, 2, 1) AS PaymentTargetAccountTitle
    ,      rc.CheckingDamageInterestAmount AS Amount
    ,      c1.Class3 AS OemId
    ,      0 AS SUMType
    ,      o.EnterpriseId AS EnterpriseId
    ,      1 AS OutType
    ,      rc.OrderSeq AS OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 101 AND c.KeyCode = rc.ReceiptAgentId)
    WHERE  (rc.ReceiptDate < :AccountingMonth AND rc.ReceiptProcessDate >= (SELECT MAX(tmp.BusinessDate + INTERVAL 1 DAY) FROM (SELECT BusinessDate FROM T_BusinessCalendar WHERE BusinessDate >= :AccountingMonth AND BusinessFlg = 1 LIMIT 3) tmp))
    AND    (c1.Class1 <> 0 AND (o.OemClaimTransDate IS NULL OR (DATE(o.OemClaimTransDate) > DATE(rc.ReceiptDate) AND NOT (DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))))
    AND    rc.ReceiptAgentId IS NOT NULL
    AND    rc.CheckingDamageInterestAmount <> 0
    AND    arc.Rct_CancelFlg = 0

    UNION ALL
    -- [SQL04]
    SELECT DATE(rc.ReceiptProcessDate) AS ProcessingDate
    ,      rc.ReceiptDate AS ReceiptDate
    ,      rc.DepositDate AS DepositDate
    ,      c.Class3 AS PaymentAccountTitle
    ,      F_GetCode(161, 3, 1) AS PaymentTargetAccountTitle
    ,      rc.CheckingClaimFee AS Amount
    ,      c1.Class3 AS OemId
    ,      0 AS SUMType
    ,      o.EnterpriseId AS EnterpriseId
    ,      1 AS OutType
    ,      rc.OrderSeq AS OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 101 AND c.KeyCode = rc.ReceiptAgentId)
    WHERE  (rc.ReceiptDate >= :AccountingMonth AND rc.ReceiptDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    (c1.Class1 <> 0 AND (o.OemClaimTransDate IS NULL OR (DATE(o.OemClaimTransDate) > DATE(rc.ReceiptDate) AND NOT (DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))))
    AND    rc.ReceiptAgentId IS NOT NULL
    AND    rc.CheckingClaimFee <> 0
    AND    arc.Rct_CancelFlg = 0
    UNION ALL
    SELECT DATE(rc.ReceiptProcessDate) AS ProcessingDate
    ,      rc.ReceiptDate AS ReceiptDate
    ,      rc.DepositDate AS DepositDate
    ,      c.Class3 AS PaymentAccountTitle
    ,      F_GetCode(161, 3, 1) AS PaymentTargetAccountTitle
    ,      rc.CheckingClaimFee AS Amount
    ,      c1.Class3 AS OemId
    ,      0 AS SUMType
    ,      o.EnterpriseId AS EnterpriseId
    ,      1 AS OutType
    ,      rc.OrderSeq AS OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 101 AND c.KeyCode = rc.ReceiptAgentId)
    WHERE  (rc.ReceiptDate < :AccountingMonth AND rc.ReceiptProcessDate >= (SELECT MAX(tmp.BusinessDate + INTERVAL 1 DAY) FROM (SELECT BusinessDate FROM T_BusinessCalendar WHERE BusinessDate >= :AccountingMonth AND BusinessFlg = 1 LIMIT 3) tmp))
    AND    (c1.Class1 <> 0 AND (o.OemClaimTransDate IS NULL OR (DATE(o.OemClaimTransDate) > DATE(rc.ReceiptDate) AND NOT (DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))))
    AND    rc.ReceiptAgentId IS NOT NULL
    AND    rc.CheckingClaimFee <> 0
    AND    arc.Rct_CancelFlg = 0

    /* (銀行 ReceiptClass=3) */
    UNION ALL
    -- [SQL05]
    SELECT DATE(rc.ReceiptProcessDate) AS ProcessingDate
    ,      rc.ReceiptDate AS ReceiptDate
    ,      rc.DepositDate AS DepositDate
    ,      c.Class3 AS PaymentAccountTitle
    ,      F_GetCode(161, 1, 1) AS PaymentTargetAccountTitle
    ,      rc.CheckingUseAmount AS Amount
    ,      c1.Class3 AS OemId
    ,      0 AS SUMType
    ,      o.EnterpriseId AS EnterpriseId
    ,      1 AS OutType
    ,      rc.OrderSeq AS OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 151 AND c.KeyCode = rc.BranchBankId)
    WHERE  (rc.ReceiptDate >= :AccountingMonth AND rc.ReceiptDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    (c1.Class1 <> 0 AND (o.OemClaimTransDate IS NULL OR (DATE(o.OemClaimTransDate) > DATE(rc.ReceiptDate) AND NOT (DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))))
    AND    rc.ReceiptAgentId IS NULL
    AND    rc.ReceiptClass = 3
    AND    rc.CheckingUseAmount <> 0
    AND    arc.Rct_CancelFlg = 0
    UNION ALL
    SELECT DATE(rc.ReceiptProcessDate) AS ProcessingDate
    ,      rc.ReceiptDate AS ReceiptDate
    ,      rc.DepositDate AS DepositDate
    ,      c.Class3 AS PaymentAccountTitle
    ,      F_GetCode(161, 1, 1) AS PaymentTargetAccountTitle
    ,      rc.CheckingUseAmount AS Amount
    ,      c1.Class3 AS OemId
    ,      0 AS SUMType
    ,      o.EnterpriseId AS EnterpriseId
    ,      1 AS OutType
    ,      rc.OrderSeq AS OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 151 AND c.KeyCode = rc.BranchBankId)
    WHERE  (rc.ReceiptDate < :AccountingMonth AND rc.ReceiptProcessDate >= (SELECT MAX(tmp.BusinessDate + INTERVAL 1 DAY) FROM (SELECT BusinessDate FROM T_BusinessCalendar WHERE BusinessDate >= :AccountingMonth AND BusinessFlg = 1 LIMIT 3) tmp))
    AND    (c1.Class1 <> 0 AND (o.OemClaimTransDate IS NULL OR (DATE(o.OemClaimTransDate) > DATE(rc.ReceiptDate) AND NOT (DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))))
    AND    rc.ReceiptAgentId IS NULL
    AND    rc.ReceiptClass = 3
    AND    rc.CheckingUseAmount <> 0
    AND    arc.Rct_CancelFlg = 0

    UNION ALL
    -- [SQL06]
    SELECT DATE(rc.ReceiptProcessDate) AS ProcessingDate
    ,      rc.ReceiptDate AS ReceiptDate
    ,      rc.DepositDate AS DepositDate
    ,      c.Class3 AS PaymentAccountTitle
    ,      F_GetCode(161, 2, 1) AS PaymentTargetAccountTitle
    ,      rc.CheckingDamageInterestAmount AS Amount
    ,      c1.Class3 AS OemId
    ,      0 AS SUMType
    ,      o.EnterpriseId AS EnterpriseId
    ,      1 AS OutType
    ,      rc.OrderSeq AS OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 151 AND c.KeyCode = rc.BranchBankId)
    WHERE  (rc.ReceiptDate >= :AccountingMonth AND rc.ReceiptDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    (c1.Class1 <> 0 AND (o.OemClaimTransDate IS NULL OR (DATE(o.OemClaimTransDate) > DATE(rc.ReceiptDate) AND NOT (DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))))
    AND    rc.ReceiptAgentId IS NULL
    AND    rc.ReceiptClass = 3
    AND    rc.CheckingDamageInterestAmount <> 0
    AND    arc.Rct_CancelFlg = 0
    UNION ALL
    SELECT DATE(rc.ReceiptProcessDate) AS ProcessingDate
    ,      rc.ReceiptDate AS ReceiptDate
    ,      rc.DepositDate AS DepositDate
    ,      c.Class3 AS PaymentAccountTitle
    ,      F_GetCode(161, 2, 1) AS PaymentTargetAccountTitle
    ,      rc.CheckingDamageInterestAmount AS Amount
    ,      c1.Class3 AS OemId
    ,      0 AS SUMType
    ,      o.EnterpriseId AS EnterpriseId
    ,      1 AS OutType
    ,      rc.OrderSeq AS OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 151 AND c.KeyCode = rc.BranchBankId)
    WHERE  (rc.ReceiptDate < :AccountingMonth AND rc.ReceiptProcessDate >= (SELECT MAX(tmp.BusinessDate + INTERVAL 1 DAY) FROM (SELECT BusinessDate FROM T_BusinessCalendar WHERE BusinessDate >= :AccountingMonth AND BusinessFlg = 1 LIMIT 3) tmp))
    AND    (c1.Class1 <> 0 AND (o.OemClaimTransDate IS NULL OR (DATE(o.OemClaimTransDate) > DATE(rc.ReceiptDate) AND NOT (DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))))
    AND    rc.ReceiptAgentId IS NULL
    AND    rc.ReceiptClass = 3
    AND    rc.CheckingDamageInterestAmount <> 0
    AND    arc.Rct_CancelFlg = 0

    UNION ALL
    -- [SQL07]
    SELECT DATE(rc.ReceiptProcessDate) AS ProcessingDate
    ,      rc.ReceiptDate AS ReceiptDate
    ,      rc.DepositDate AS DepositDate
    ,      c.Class3 AS PaymentAccountTitle
    ,      F_GetCode(161, 3, 1) AS PaymentTargetAccountTitle
    ,      rc.CheckingClaimFee AS Amount
    ,      c1.Class3 AS OemId
    ,      0 AS SUMType
    ,      o.EnterpriseId AS EnterpriseId
    ,      1 AS OutType
    ,      rc.OrderSeq AS OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 151 AND c.KeyCode = rc.BranchBankId)
    WHERE  (rc.ReceiptDate >= :AccountingMonth AND rc.ReceiptDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    (c1.Class1 <> 0 AND (o.OemClaimTransDate IS NULL OR (DATE(o.OemClaimTransDate) > DATE(rc.ReceiptDate) AND NOT (DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))))
    AND    rc.ReceiptAgentId IS NULL
    AND    rc.ReceiptClass = 3
    AND    rc.CheckingClaimFee <> 0
    AND    arc.Rct_CancelFlg = 0
    UNION ALL
    SELECT DATE(rc.ReceiptProcessDate) AS ProcessingDate
    ,      rc.ReceiptDate AS ReceiptDate
    ,      rc.DepositDate AS DepositDate
    ,      c.Class3 AS PaymentAccountTitle
    ,      F_GetCode(161, 3, 1) AS PaymentTargetAccountTitle
    ,      rc.CheckingClaimFee AS Amount
    ,      c1.Class3 AS OemId
    ,      0 AS SUMType
    ,      o.EnterpriseId AS EnterpriseId
    ,      1 AS OutType
    ,      rc.OrderSeq AS OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 151 AND c.KeyCode = rc.BranchBankId)
    WHERE  (rc.ReceiptDate < :AccountingMonth AND rc.ReceiptProcessDate >= (SELECT MAX(tmp.BusinessDate + INTERVAL 1 DAY) FROM (SELECT BusinessDate FROM T_BusinessCalendar WHERE BusinessDate >= :AccountingMonth AND BusinessFlg = 1 LIMIT 3) tmp))
    AND    (c1.Class1 <> 0 AND (o.OemClaimTransDate IS NULL OR (DATE(o.OemClaimTransDate) > DATE(rc.ReceiptDate) AND NOT (DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))))
    AND    rc.ReceiptAgentId IS NULL
    AND    rc.ReceiptClass = 3
    AND    rc.CheckingClaimFee <> 0
    AND    arc.Rct_CancelFlg = 0

    /* (郵便局 ReceiptClass=2) */
    UNION ALL
    -- [SQL08]
    SELECT DATE(rc.ReceiptProcessDate) AS ProcessingDate
    ,      rc.ReceiptDate AS ReceiptDate
    ,      rc.DepositDate AS DepositDate
    ,      c.Class3 AS PaymentAccountTitle
    ,      F_GetCode(161, 1, 1) AS PaymentTargetAccountTitle
    ,      rc.CheckingUseAmount AS Amount
    ,      c1.Class3 AS OemId
    ,      0 AS SUMType
    ,      o.EnterpriseId AS EnterpriseId
    ,      1 AS OutType
    ,      rc.OrderSeq AS OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 166 AND c.Class1 = arc.AccountNumber)
    WHERE  (rc.ReceiptDate >= :AccountingMonth AND rc.ReceiptDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    (c1.Class1 <> 0 AND (o.OemClaimTransDate IS NULL OR (DATE(o.OemClaimTransDate) > DATE(rc.ReceiptDate) AND NOT (DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))))
    AND    rc.ReceiptAgentId IS NULL
    AND    rc.ReceiptClass = 2
    AND    rc.CheckingUseAmount <> 0
    AND    arc.Rct_CancelFlg = 0
    UNION ALL
    SELECT DATE(rc.ReceiptProcessDate) AS ProcessingDate
    ,      rc.ReceiptDate AS ReceiptDate
    ,      rc.DepositDate AS DepositDate
    ,      c.Class3 AS PaymentAccountTitle
    ,      F_GetCode(161, 1, 1) AS PaymentTargetAccountTitle
    ,      rc.CheckingUseAmount AS Amount
    ,      c1.Class3 AS OemId
    ,      0 AS SUMType
    ,      o.EnterpriseId AS EnterpriseId
    ,      1 AS OutType
    ,      rc.OrderSeq AS OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 166 AND c.Class1 = arc.AccountNumber)
    WHERE  (rc.ReceiptDate < :AccountingMonth AND rc.ReceiptProcessDate >= (SELECT MAX(tmp.BusinessDate + INTERVAL 1 DAY) FROM (SELECT BusinessDate FROM T_BusinessCalendar WHERE BusinessDate >= :AccountingMonth AND BusinessFlg = 1 LIMIT 3) tmp))
    AND    (c1.Class1 <> 0 AND (o.OemClaimTransDate IS NULL OR (DATE(o.OemClaimTransDate) > DATE(rc.ReceiptDate) AND NOT (DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))))
    AND    rc.ReceiptAgentId IS NULL
    AND    rc.ReceiptClass = 2
    AND    rc.CheckingUseAmount <> 0
    AND    arc.Rct_CancelFlg = 0

    UNION ALL
    -- [SQL09]
    SELECT DATE(rc.ReceiptProcessDate) AS ProcessingDate
    ,      rc.ReceiptDate AS ReceiptDate
    ,      rc.DepositDate AS DepositDate
    ,      c.Class3 AS PaymentAccountTitle
    ,      F_GetCode(161, 2, 1) AS PaymentTargetAccountTitle
    ,      rc.CheckingDamageInterestAmount AS Amount
    ,      c1.Class3 AS OemId
    ,      0 AS SUMType
    ,      o.EnterpriseId AS EnterpriseId
    ,      1 AS OutType
    ,      rc.OrderSeq AS OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 166 AND c.Class1 = arc.AccountNumber)
    WHERE  (rc.ReceiptDate >= :AccountingMonth AND rc.ReceiptDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    (c1.Class1 <> 0 AND (o.OemClaimTransDate IS NULL OR (DATE(o.OemClaimTransDate) > DATE(rc.ReceiptDate) AND NOT (DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))))
    AND    rc.ReceiptAgentId IS NULL
    AND    rc.ReceiptClass = 2
    AND    rc.CheckingDamageInterestAmount <> 0
    AND    arc.Rct_CancelFlg = 0
    UNION ALL
    SELECT DATE(rc.ReceiptProcessDate) AS ProcessingDate
    ,      rc.ReceiptDate AS ReceiptDate
    ,      rc.DepositDate AS DepositDate
    ,      c.Class3 AS PaymentAccountTitle
    ,      F_GetCode(161, 2, 1) AS PaymentTargetAccountTitle
    ,      rc.CheckingDamageInterestAmount AS Amount
    ,      c1.Class3 AS OemId
    ,      0 AS SUMType
    ,      o.EnterpriseId AS EnterpriseId
    ,      1 AS OutType
    ,      rc.OrderSeq AS OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 166 AND c.Class1 = arc.AccountNumber)
    WHERE  (rc.ReceiptDate < :AccountingMonth AND rc.ReceiptProcessDate >= (SELECT MAX(tmp.BusinessDate + INTERVAL 1 DAY) FROM (SELECT BusinessDate FROM T_BusinessCalendar WHERE BusinessDate >= :AccountingMonth AND BusinessFlg = 1 LIMIT 3) tmp))
    AND    (c1.Class1 <> 0 AND (o.OemClaimTransDate IS NULL OR (DATE(o.OemClaimTransDate) > DATE(rc.ReceiptDate) AND NOT (DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))))
    AND    rc.ReceiptAgentId IS NULL
    AND    rc.ReceiptClass = 2
    AND    rc.CheckingDamageInterestAmount <> 0
    AND    arc.Rct_CancelFlg = 0

    UNION ALL
    -- [SQL10]
    SELECT DATE(rc.ReceiptProcessDate) AS ProcessingDate
    ,      rc.ReceiptDate AS ReceiptDate
    ,      rc.DepositDate AS DepositDate
    ,      c.Class3 AS PaymentAccountTitle
    ,      F_GetCode(161, 3, 1) AS PaymentTargetAccountTitle
    ,      rc.CheckingClaimFee AS Amount
    ,      c1.Class3 AS OemId
    ,      0 AS SUMType
    ,      o.EnterpriseId AS EnterpriseId
    ,      1 AS OutType
    ,      rc.OrderSeq AS OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 166 AND c.Class1 = arc.AccountNumber)
    WHERE  (rc.ReceiptDate >= :AccountingMonth AND rc.ReceiptDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    (c1.Class1 <> 0 AND (o.OemClaimTransDate IS NULL OR (DATE(o.OemClaimTransDate) > DATE(rc.ReceiptDate) AND NOT (DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))))
    AND    rc.ReceiptAgentId IS NULL
    AND    rc.ReceiptClass = 2
    AND    rc.CheckingClaimFee <> 0
    AND    arc.Rct_CancelFlg = 0
    UNION ALL
    SELECT DATE(rc.ReceiptProcessDate) AS ProcessingDate
    ,      rc.ReceiptDate AS ReceiptDate
    ,      rc.DepositDate AS DepositDate
    ,      c.Class3 AS PaymentAccountTitle
    ,      F_GetCode(161, 3, 1) AS PaymentTargetAccountTitle
    ,      rc.CheckingClaimFee AS Amount
    ,      c1.Class3 AS OemId
    ,      0 AS SUMType
    ,      o.EnterpriseId AS EnterpriseId
    ,      1 AS OutType
    ,      rc.OrderSeq AS OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 166 AND c.Class1 = arc.AccountNumber)
    WHERE  (rc.ReceiptDate < :AccountingMonth AND rc.ReceiptProcessDate >= (SELECT MAX(tmp.BusinessDate + INTERVAL 1 DAY) FROM (SELECT BusinessDate FROM T_BusinessCalendar WHERE BusinessDate >= :AccountingMonth AND BusinessFlg = 1 LIMIT 3) tmp))
    AND    (c1.Class1 <> 0 AND (o.OemClaimTransDate IS NULL OR (DATE(o.OemClaimTransDate) > DATE(rc.ReceiptDate) AND NOT (DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))))
    AND    rc.ReceiptAgentId IS NULL
    AND    rc.ReceiptClass = 2
    AND    rc.CheckingClaimFee <> 0
    AND    arc.Rct_CancelFlg = 0

    /* (その他 ReceiptClass=9) */
    UNION ALL
    -- [SQL11]
    SELECT DATE(rc.ReceiptProcessDate) AS ProcessingDate
    ,      rc.ReceiptDate AS ReceiptDate
    ,      rc.DepositDate AS DepositDate
    ,      c.Class3 AS PaymentAccountTitle
    ,      F_GetCode(161, 1, 1) AS PaymentTargetAccountTitle
    ,      rc.CheckingUseAmount AS Amount
    ,      c1.Class3 AS OemId
    ,      0 AS SUMType
    ,      o.EnterpriseId AS EnterpriseId
    ,      1 AS OutType
    ,      rc.OrderSeq AS OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 155 AND c.KeyCode = arc.ClassDetails)
    WHERE  (rc.ReceiptDate >= :AccountingMonth AND rc.ReceiptDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    (c1.Class1 <> 0 AND (o.OemClaimTransDate IS NULL OR (DATE(o.OemClaimTransDate) > DATE(rc.ReceiptDate) AND NOT (DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))))
    AND    rc.ReceiptAgentId IS NULL
    AND    rc.ReceiptClass = 9
    AND    rc.CheckingUseAmount <> 0
    AND    arc.Rct_CancelFlg = 0
    UNION ALL
    SELECT DATE(rc.ReceiptProcessDate) AS ProcessingDate
    ,      rc.ReceiptDate AS ReceiptDate
    ,      rc.DepositDate AS DepositDate
    ,      c.Class3 AS PaymentAccountTitle
    ,      F_GetCode(161, 1, 1) AS PaymentTargetAccountTitle
    ,      rc.CheckingUseAmount AS Amount
    ,      c1.Class3 AS OemId
    ,      0 AS SUMType
    ,      o.EnterpriseId AS EnterpriseId
    ,      1 AS OutType
    ,      rc.OrderSeq AS OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 155 AND c.KeyCode = arc.ClassDetails)
    WHERE  (rc.ReceiptDate < :AccountingMonth AND rc.ReceiptProcessDate >= (SELECT MAX(tmp.BusinessDate + INTERVAL 1 DAY) FROM (SELECT BusinessDate FROM T_BusinessCalendar WHERE BusinessDate >= :AccountingMonth AND BusinessFlg = 1 LIMIT 3) tmp))
    AND    (c1.Class1 <> 0 AND (o.OemClaimTransDate IS NULL OR (DATE(o.OemClaimTransDate) > DATE(rc.ReceiptDate) AND NOT (DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))))
    AND    rc.ReceiptAgentId IS NULL
    AND    rc.ReceiptClass = 9
    AND    rc.CheckingUseAmount <> 0
    AND    arc.Rct_CancelFlg = 0

    UNION ALL
    -- [SQL12]
    SELECT DATE(rc.ReceiptProcessDate) AS ProcessingDate
    ,      rc.ReceiptDate AS ReceiptDate
    ,      rc.DepositDate AS DepositDate
    ,      c.Class3 AS PaymentAccountTitle
    ,      F_GetCode(161, 2, 1) AS PaymentTargetAccountTitle
    ,      rc.CheckingDamageInterestAmount AS Amount
    ,      c1.Class3 AS OemId
    ,      0 AS SUMType
    ,      o.EnterpriseId AS EnterpriseId
    ,      1 AS OutType
    ,      rc.OrderSeq AS OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 155 AND c.KeyCode = arc.ClassDetails)
    WHERE  (rc.ReceiptDate >= :AccountingMonth AND rc.ReceiptDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    (c1.Class1 <> 0 AND (o.OemClaimTransDate IS NULL OR (DATE(o.OemClaimTransDate) > DATE(rc.ReceiptDate) AND NOT (DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))))
    AND    rc.ReceiptAgentId IS NULL
    AND    rc.ReceiptClass = 9
    AND    rc.CheckingDamageInterestAmount <> 0
    AND    arc.Rct_CancelFlg = 0
    UNION ALL
    SELECT DATE(rc.ReceiptProcessDate) AS ProcessingDate
    ,      rc.ReceiptDate AS ReceiptDate
    ,      rc.DepositDate AS DepositDate
    ,      c.Class3 AS PaymentAccountTitle
    ,      F_GetCode(161, 2, 1) AS PaymentTargetAccountTitle
    ,      rc.CheckingDamageInterestAmount AS Amount
    ,      c1.Class3 AS OemId
    ,      0 AS SUMType
    ,      o.EnterpriseId AS EnterpriseId
    ,      1 AS OutType
    ,      rc.OrderSeq AS OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 155 AND c.KeyCode = arc.ClassDetails)
    WHERE  (rc.ReceiptDate < :AccountingMonth AND rc.ReceiptProcessDate >= (SELECT MAX(tmp.BusinessDate + INTERVAL 1 DAY) FROM (SELECT BusinessDate FROM T_BusinessCalendar WHERE BusinessDate >= :AccountingMonth AND BusinessFlg = 1 LIMIT 3) tmp))
    AND    (c1.Class1 <> 0 AND (o.OemClaimTransDate IS NULL OR (DATE(o.OemClaimTransDate) > DATE(rc.ReceiptDate) AND NOT (DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))))
    AND    rc.ReceiptAgentId IS NULL
    AND    rc.ReceiptClass = 9
    AND    rc.CheckingDamageInterestAmount <> 0
    AND    arc.Rct_CancelFlg = 0

    UNION ALL
    -- [SQL13]
    SELECT DATE(rc.ReceiptProcessDate) AS ProcessingDate
    ,      rc.ReceiptDate AS ReceiptDate
    ,      rc.DepositDate AS DepositDate
    ,      c.Class3 AS PaymentAccountTitle
    ,      F_GetCode(161, 3, 1) AS PaymentTargetAccountTitle
    ,      rc.CheckingClaimFee AS Amount
    ,      c1.Class3 AS OemId
    ,      0 AS SUMType
    ,      o.EnterpriseId AS EnterpriseId
    ,      1 AS OutType
    ,      rc.OrderSeq AS OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 155 AND c.KeyCode = arc.ClassDetails)
    WHERE  (rc.ReceiptDate >= :AccountingMonth AND rc.ReceiptDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    (c1.Class1 <> 0 AND (o.OemClaimTransDate IS NULL OR (DATE(o.OemClaimTransDate) > DATE(rc.ReceiptDate) AND NOT (DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))))
    AND    rc.ReceiptAgentId IS NULL
    AND    rc.ReceiptClass = 9
    AND    rc.CheckingClaimFee <> 0
    AND    arc.Rct_CancelFlg = 0
    UNION ALL
    SELECT DATE(rc.ReceiptProcessDate) AS ProcessingDate
    ,      rc.ReceiptDate AS ReceiptDate
    ,      rc.DepositDate AS DepositDate
    ,      c.Class3 AS PaymentAccountTitle
    ,      F_GetCode(161, 3, 1) AS PaymentTargetAccountTitle
    ,      rc.CheckingClaimFee AS Amount
    ,      c1.Class3 AS OemId
    ,      0 AS SUMType
    ,      o.EnterpriseId AS EnterpriseId
    ,      1 AS OutType
    ,      rc.OrderSeq AS OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 155 AND c.KeyCode = arc.ClassDetails)
    WHERE  (rc.ReceiptDate < :AccountingMonth AND rc.ReceiptProcessDate >= (SELECT MAX(tmp.BusinessDate + INTERVAL 1 DAY) FROM (SELECT BusinessDate FROM T_BusinessCalendar WHERE BusinessDate >= :AccountingMonth AND BusinessFlg = 1 LIMIT 3) tmp))
    AND    (c1.Class1 <> 0 AND (o.OemClaimTransDate IS NULL OR (DATE(o.OemClaimTransDate) > DATE(rc.ReceiptDate) AND NOT (DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))))
    AND    rc.ReceiptAgentId IS NULL
    AND    rc.ReceiptClass = 9
    AND    rc.CheckingClaimFee <> 0
    AND    arc.Rct_CancelFlg = 0

    /* 以下、雑損失／雑収入関連 */
    -- (雑収入 SundryType=0)
    UNION ALL
    -- [SQL15]
    SELECT DATE(sc.ProcessDate)
    ,      sc.ProcessDate
    ,      sc.ProcessDate
    ,      c3.Class1
    ,      F_GetCode(161, 1, 1)
    ,      sc.CheckingUseAmount * -1
    ,      c2.Class3
    ,      (CASE WHEN c1.Class3 = 1 THEN 2 ELSE 3 END) -- SUMType=2(貸倒)／3(その他)
    ,      o.EnterpriseId AS EnterpriseId
    ,      1
    ,      sc.OrderSeq
    FROM   T_SundryControl sc
           INNER JOIN M_Code c1 ON (c1.CodeId = 96 AND c1.KeyCode = sc.SundryClass)
           INNER JOIN T_Order o ON (sc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c2 ON (c2.CodeId = 160 AND c2.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c3 ON (c3.CodeId = 165 AND c3.KeyCode = sc.SundryClass)
    WHERE  (sc.ProcessDate >= :AccountingMonth AND sc.ProcessDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    c1.Class2 = 1
    AND    (c2.Class1 <> 0 AND (o.OemClaimTransDate IS NULL OR DATE(o.OemClaimTransDate) > DATE(sc.ProcessDate)))
    AND    sc.SundryType = 0
    AND    sc.CheckingUseAmount <> 0

    UNION ALL
    -- [SQL16]
    SELECT DATE(sc.ProcessDate)
    ,      sc.ProcessDate
    ,      sc.ProcessDate
    ,      c3.Class1
    ,      F_GetCode(161, 2, 1)
    ,      sc.CheckingDamageInterestAmount * -1
    ,      c2.Class3
    ,      (CASE WHEN c1.Class3 = 1 THEN 2 ELSE 3 END) -- SUMType=2(貸倒)／3(その他)
    ,      o.EnterpriseId AS EnterpriseId
    ,      1
    ,      sc.OrderSeq
    FROM   T_SundryControl sc
           INNER JOIN M_Code c1 ON (c1.CodeId = 96 AND c1.KeyCode = sc.SundryClass)
           INNER JOIN T_Order o ON (sc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c2 ON (c2.CodeId = 160 AND c2.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c3 ON (c3.CodeId = 165 AND c3.KeyCode = sc.SundryClass)
    WHERE  (sc.ProcessDate >= :AccountingMonth AND sc.ProcessDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    c1.Class2 = 1
    AND    (c2.Class1 <> 0 AND (o.OemClaimTransDate IS NULL OR DATE(o.OemClaimTransDate) > DATE(sc.ProcessDate)))
    AND    sc.SundryType = 0
    AND    sc.CheckingDamageInterestAmount <> 0

    UNION ALL
    -- [SQL17]
    SELECT DATE(sc.ProcessDate)
    ,      sc.ProcessDate
    ,      sc.ProcessDate
    ,      c3.Class1
    ,      F_GetCode(161, 3, 1)
    ,      sc.CheckingClaimFee * -1
    ,      c2.Class3
    ,      (CASE WHEN c1.Class3 = 1 THEN 2 ELSE 3 END) -- SUMType=2(貸倒)／3(その他)
    ,      o.EnterpriseId AS EnterpriseId
    ,      1
    ,      sc.OrderSeq
    FROM   T_SundryControl sc
           INNER JOIN M_Code c1 ON (c1.CodeId = 96 AND c1.KeyCode = sc.SundryClass)
           INNER JOIN T_Order o ON (sc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c2 ON (c2.CodeId = 160 AND c2.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c3 ON (c3.CodeId = 165 AND c3.KeyCode = sc.SundryClass)
    WHERE  (sc.ProcessDate >= :AccountingMonth AND sc.ProcessDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    c1.Class2 = 1
    AND    (c2.Class1 <> 0 AND (o.OemClaimTransDate IS NULL OR DATE(o.OemClaimTransDate) > DATE(sc.ProcessDate)))
    AND    sc.SundryType = 0
    AND    sc.CheckingClaimFee <> 0

    -- (雑損失 SundryType=1)
    UNION ALL
    -- [SQL18]
    SELECT DATE(sc.ProcessDate)
    ,      sc.ProcessDate
    ,      sc.ProcessDate
    ,      c3.Class1
    ,      F_GetCode(161, 1, 1)
    ,      sc.CheckingUseAmount
    ,      c2.Class3
    ,      (CASE WHEN c1.Class3 = 1 THEN 2 ELSE 3 END) -- SUMType=2(貸倒)／3(その他)
    ,      o.EnterpriseId AS EnterpriseId
    ,      1
    ,      sc.OrderSeq
    FROM   T_SundryControl sc
           INNER JOIN M_Code c1 ON (c1.CodeId = 96 AND c1.KeyCode = sc.SundryClass)
           INNER JOIN T_Order o ON (sc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c2 ON (c2.CodeId = 160 AND c2.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c3 ON (c3.CodeId = 165 AND c3.KeyCode = sc.SundryClass)
    WHERE  (sc.ProcessDate >= :AccountingMonth AND sc.ProcessDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    c1.Class2 = 1
    AND    (c2.Class1 <> 0 AND (o.OemClaimTransDate IS NULL OR DATE(o.OemClaimTransDate) > DATE(sc.ProcessDate)))
    AND    sc.SundryType = 1
    AND    sc.CheckingUseAmount <> 0

    UNION ALL
    -- [SQL19]
    SELECT DATE(sc.ProcessDate)
    ,      sc.ProcessDate
    ,      sc.ProcessDate
    ,      c3.Class1
    ,      F_GetCode(161, 2, 1)
    ,      sc.CheckingDamageInterestAmount
    ,      c2.Class3
    ,      (CASE WHEN c1.Class3 = 1 THEN 2 ELSE 3 END) -- SUMType=2(貸倒)／3(その他)
    ,      o.EnterpriseId AS EnterpriseId
    ,      1
    ,      sc.OrderSeq
    FROM   T_SundryControl sc
           INNER JOIN M_Code c1 ON (c1.CodeId = 96 AND c1.KeyCode = sc.SundryClass)
           INNER JOIN T_Order o ON (sc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c2 ON (c2.CodeId = 160 AND c2.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c3 ON (c3.CodeId = 165 AND c3.KeyCode = sc.SundryClass)
    WHERE  (sc.ProcessDate >= :AccountingMonth AND sc.ProcessDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    c1.Class2 = 1
    AND    (c2.Class1 <> 0 AND (o.OemClaimTransDate IS NULL OR DATE(o.OemClaimTransDate) > DATE(sc.ProcessDate)))
    AND    sc.SundryType = 1
    AND    sc.CheckingDamageInterestAmount <> 0

    UNION ALL
    -- [SQL20]
    SELECT DATE(sc.ProcessDate)
    ,      sc.ProcessDate
    ,      sc.ProcessDate
    ,      c3.Class1
    ,      F_GetCode(161, 3, 1)
    ,      sc.CheckingClaimFee
    ,      c2.Class3
    ,      (CASE WHEN c1.Class3 = 1 THEN 2 ELSE 3 END) -- SUMType=2(貸倒)／3(その他)
    ,      o.EnterpriseId AS EnterpriseId
    ,      1
    ,      sc.OrderSeq
    FROM   T_SundryControl sc
           INNER JOIN M_Code c1 ON (c1.CodeId = 96 AND c1.KeyCode = sc.SundryClass)
           INNER JOIN T_Order o ON (sc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c2 ON (c2.CodeId = 160 AND c2.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c3 ON (c3.CodeId = 165 AND c3.KeyCode = sc.SundryClass)
    WHERE  (sc.ProcessDate >= :AccountingMonth AND sc.ProcessDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    c1.Class2 = 1
    AND    (c2.Class1 <> 0 AND (o.OemClaimTransDate IS NULL OR DATE(o.OemClaimTransDate) > DATE(sc.ProcessDate)))
    AND    sc.SundryType = 1
    AND    sc.CheckingClaimFee <> 0

    /* (返金関連) */
    UNION ALL
    -- [SQL21]
    SELECT DATE(rc.DecisionDate)
    ,      DATE(rc.DecisionDate)
    ,      DATE(rc.DecisionDate)
    ,      c2.Class2
    ,      F_GetCode(161, 8, 1)
    ,      rc.RepayAmount * -1
    ,      c1.Class3
    ,      1 -- SUMType=1(返金)
    ,      o.EnterpriseId AS EnterpriseId
    ,      1
    ,      o.OrderSeq
    FROM   T_RepaymentControl rc
           INNER JOIN AT_RepaymentControl arc ON (arc.RepaySeq = rc.RepaySeq)
           INNER JOIN T_ClaimControl cc ON (rc.ClaimId = cc.ClaimId)
           INNER JOIN T_Order o ON (cc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c2 ON (c2.CodeId = 151 AND c2.KeyCode = F_GetSystemProperty('[DEFAULT]', 'systeminfo', 'RepayBankId'))
    WHERE  (rc.DecisionDate >= :AccountingMonth AND rc.DecisionDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    rc.RepayStatus = 1
    AND    rc.RepayAmount <> 0
    AND    (c1.Class1 <> 0 AND (o.OemClaimTransDate IS NULL OR DATE(o.OemClaimTransDate) > DATE(rc.DecisionDate)))

    UNION ALL
    -- [キャンセル関連]
    SELECT DATE(oc.ExecDate)
    ,      DATE(oc.ProcessDate)
    ,      DATE(oc.ExecDate)
    ,      F_GetCode(161, 9, 1)
    ,      F_GetCode(161, 8, 1)
    ,      (o.UseAmount - osf.SettlementFee - ocf.ClaimFee) * -1
    ,      c1.Class3
    ,      1
    ,      o.EnterpriseId AS EnterpriseId
    ,      1
    ,      o.OrderSeq
    FROM   T_OemClaimed oc
           INNER JOIN T_PayingControl pc ON (oc.OemClaimedSeq = pc.OemClaimedSeq)
           INNER JOIN T_Cancel can ON (pc.Seq = can.PayingControlSeq)
           INNER JOIN T_ClaimControl cc ON (cc.OrderSeq = can.OrderSeq)
           INNER JOIN T_Order o ON (can.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(oc.OemId, 0))
           INNER JOIN T_OemSettlementFee osf ON (osf.OrderSeq = o.OrderSeq)
           INNER JOIN T_OemClaimFee ocf ON (ocf.OrderSeq = o.OrderSeq)
    WHERE  (oc.ExecDate >= :AccountingMonth AND oc.ExecDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    (o.UseAmount - osf.SettlementFee - ocf.ClaimFee) <> 0
    AND    can.CancelPhase IN (3)
    AND    can.ValidFlg = 1
    AND    c1.Class1 <> 0

    /* 加盟店入金関連 */
    UNION ALL
    -- [SQL24]
    SELECT erh.ReceiptDate
    ,      erh.ReceiptDate
    ,      erh.ReceiptDate
    ,      c2.Class1
    ,      F_GetCode(161, 4, 1)
    ,      aerh.ReceiptAmountDue
    ,      c.Class3
    ,      0
    ,      erh.EnterpriseId AS EnterpriseId
    ,      1
    ,      NULL
    FROM   T_EnterpriseReceiptHistory erh
           INNER JOIN AT_EnterpriseReceiptHistory aerh ON (erh.EntRcptSeq = aerh.EntRcptSeq)
           INNER JOIN T_Enterprise e ON (erh.EnterpriseId = e.EnterpriseId)
           INNER JOIN M_Code c ON (c.CodeId = 160 AND c.KeyCode = IFNULL(e.OemId, 0))
           INNER JOIN M_Code c1 ON (c1.CodeId = 95 AND c1.KeyCode = erh.ReceiptClass)
           LEFT OUTER JOIN M_Code c2 ON (c2.CodeId = 167 AND c2.KeyCode = aerh.ReceiptAmountSource)
    WHERE  c.Class1 <> 0
    AND    (erh.ReceiptProcessDate >= :AccountingMonth AND erh.ReceiptProcessDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    c1.Class1 = 0
    AND    aerh.ReceiptAmountDue <> 0

    UNION ALL
    -- [SQL25]
    SELECT erh.ReceiptDate
    ,      erh.ReceiptDate
    ,      erh.ReceiptDate
    ,      c2.Class1
    ,      F_GetCode(161, 6, 1)
    ,      aerh.ReceiptAmountRece
    ,      c.Class3
    ,      0
    ,      erh.EnterpriseId AS EnterpriseId
    ,      1
    ,      NULL
    FROM   T_EnterpriseReceiptHistory erh
           INNER JOIN AT_EnterpriseReceiptHistory aerh ON (erh.EntRcptSeq = aerh.EntRcptSeq)
           INNER JOIN T_Enterprise e ON (erh.EnterpriseId = e.EnterpriseId)
           INNER JOIN M_Code c ON (c.CodeId = 160 AND c.KeyCode = IFNULL(e.OemId, 0))
           INNER JOIN M_Code c1 ON (c1.CodeId = 95 AND c1.KeyCode = erh.ReceiptClass)
           LEFT OUTER JOIN M_Code c2 ON (c2.CodeId = 167 AND c2.KeyCode = aerh.ReceiptAmountSource)
    WHERE  c.Class1 <> 0
    AND    (erh.ReceiptProcessDate >= :AccountingMonth AND erh.ReceiptProcessDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    c1.Class1 = 0
    AND    aerh.ReceiptAmountRece <> 0

    UNION ALL
    -- [SQL26]
    SELECT erh.ReceiptDate
    ,      erh.ReceiptDate
    ,      erh.ReceiptDate
    ,      c2.Class1
    ,      F_GetCode(161, 4, 1)
    ,      erh.ReceiptAmount
    ,      c.Class3
    ,      0
    ,      erh.EnterpriseId AS EnterpriseId
    ,      1
    ,      NULL
    FROM   T_EnterpriseReceiptHistory erh
           INNER JOIN AT_EnterpriseReceiptHistory aerh ON (erh.EntRcptSeq = aerh.EntRcptSeq)
           INNER JOIN T_Enterprise e ON (erh.EnterpriseId = e.EnterpriseId)
           INNER JOIN M_Code c ON (c.CodeId = 160 AND c.KeyCode = IFNULL(e.OemId, 0))
           INNER JOIN M_Code c1 ON (c1.CodeId = 95 AND c1.KeyCode = erh.ReceiptClass)
           LEFT OUTER JOIN M_Code c2 ON (c2.CodeId = 165 AND c2.KeyCode = erh.ReceiptClass)
    WHERE  c.Class1 <> 0
    AND    (erh.ReceiptProcessDate >= :AccountingMonth AND erh.ReceiptProcessDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    c1.Class1 = 1
    AND    erh.ReceiptAmount <> 0

    /* 以下、入金取消関連 */
    UNION ALL
    /* (収納代行会社 ReceiptAgentId IS NOT NULL) */
    -- [SQL02]
    SELECT DATE(rc.ReceiptProcessDate) AS ProcessingDate
    ,      rc2.ReceiptDate AS ReceiptDate
    ,      rc2.DepositDate AS DepositDate
    ,      c.Class3 AS PaymentAccountTitle
    ,      F_GetCode(161, 1, 1) AS PaymentTargetAccountTitle
    ,      rc2.CheckingUseAmount * -1 AS Amount
    ,      c1.Class3 AS OemId
    ,      0 AS SUMType
    ,      o.EnterpriseId AS EnterpriseId
    ,      -1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_ReceiptControl rc2 ON (rc2.ReceiptSeq < rc.ReceiptSeq AND rc2.OrderSeq = rc.OrderSeq)
           INNER JOIN AT_ReceiptControl arc2 ON (rc2.ReceiptSeq = arc2.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 101 AND c.KeyCode = rc2.ReceiptAgentId)
    WHERE  (rc2.ReceiptDate >= :AccountingMonth AND rc2.ReceiptDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    (c1.Class1 <> 0 AND (o.OemClaimTransDate IS NULL OR (DATE(o.OemClaimTransDate) > DATE(rc.ReceiptDate) AND NOT (DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))))
    AND    rc2.ReceiptAgentId IS NOT NULL
    AND    rc2.CheckingUseAmount <> 0
    AND    arc.Rct_CancelFlg = 1
    AND    rc2.ReceiptSeq BETWEEN (SELECT IFNULL(MAX(tmp.ReceiptSeq), 0) FROM T_ReceiptControl tmp, AT_ReceiptControl tmp2 WHERE tmp.ReceiptSeq = tmp2.ReceiptSeq AND tmp.ReceiptSeq < rc.ReceiptSeq AND tmp.OrderSeq = rc.OrderSeq AND tmp2.Rct_CancelFlg = 1) + 1 AND rc.ReceiptSeq - 1
    UNION ALL
    SELECT DATE(rc.ReceiptProcessDate) AS ProcessingDate
    ,      rc2.ReceiptDate AS ReceiptDate
    ,      rc2.DepositDate AS DepositDate
    ,      c.Class3 AS PaymentAccountTitle
    ,      F_GetCode(161, 1, 1) AS PaymentTargetAccountTitle
    ,      rc2.CheckingUseAmount * -1 AS Amount
    ,      c1.Class3 AS OemId
    ,      0 AS SUMType
    ,      o.EnterpriseId AS EnterpriseId
    ,      -1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_ReceiptControl rc2 ON (rc2.ReceiptSeq < rc.ReceiptSeq AND rc2.OrderSeq = rc.OrderSeq)
           INNER JOIN AT_ReceiptControl arc2 ON (rc2.ReceiptSeq = arc2.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 101 AND c.KeyCode = rc2.ReceiptAgentId)
    WHERE  (rc2.ReceiptDate < :AccountingMonth AND rc.ReceiptProcessDate >= (SELECT MAX(tmp.BusinessDate + INTERVAL 1 DAY) FROM (SELECT BusinessDate FROM T_BusinessCalendar WHERE BusinessDate >= :AccountingMonth AND BusinessFlg = 1 LIMIT 3) tmp))
    AND    (c1.Class1 <> 0 AND (o.OemClaimTransDate IS NULL OR (DATE(o.OemClaimTransDate) > DATE(rc.ReceiptDate) AND NOT (DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))))
    AND    rc2.ReceiptAgentId IS NOT NULL
    AND    rc2.CheckingUseAmount <> 0
    AND    arc.Rct_CancelFlg = 1
    AND    rc2.ReceiptSeq BETWEEN (SELECT IFNULL(MAX(tmp.ReceiptSeq), 0) FROM T_ReceiptControl tmp, AT_ReceiptControl tmp2 WHERE tmp.ReceiptSeq = tmp2.ReceiptSeq AND tmp.ReceiptSeq < rc.ReceiptSeq AND tmp.OrderSeq = rc.OrderSeq AND tmp2.Rct_CancelFlg = 1) + 1 AND rc.ReceiptSeq - 1

    UNION ALL
    -- [SQL03]
    SELECT DATE(rc.ReceiptProcessDate) AS ProcessingDate
    ,      rc2.ReceiptDate AS ReceiptDate
    ,      rc2.DepositDate AS DepositDate
    ,      c.Class3 AS PaymentAccountTitle
    ,      F_GetCode(161, 2, 1) AS PaymentTargetAccountTitle
    ,      rc2.CheckingDamageInterestAmount * -1 AS Amount
    ,      c1.Class3 AS OemId
    ,      0 AS SUMType
    ,      o.EnterpriseId AS EnterpriseId
    ,      -1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_ReceiptControl rc2 ON (rc2.ReceiptSeq < rc.ReceiptSeq AND rc2.OrderSeq = rc.OrderSeq)
           INNER JOIN AT_ReceiptControl arc2 ON (rc2.ReceiptSeq = arc2.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 101 AND c.KeyCode = rc2.ReceiptAgentId)
    WHERE  (rc2.ReceiptDate >= :AccountingMonth AND rc2.ReceiptDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    (c1.Class1 <> 0 AND (o.OemClaimTransDate IS NULL OR (DATE(o.OemClaimTransDate) > DATE(rc.ReceiptDate) AND NOT (DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))))
    AND    rc2.ReceiptAgentId IS NOT NULL
    AND    rc2.CheckingDamageInterestAmount <> 0
    AND    arc.Rct_CancelFlg = 1
    AND    rc2.ReceiptSeq BETWEEN (SELECT IFNULL(MAX(tmp.ReceiptSeq), 0) FROM T_ReceiptControl tmp, AT_ReceiptControl tmp2 WHERE tmp.ReceiptSeq = tmp2.ReceiptSeq AND tmp.ReceiptSeq < rc.ReceiptSeq AND tmp.OrderSeq = rc.OrderSeq AND tmp2.Rct_CancelFlg = 1) + 1 AND rc.ReceiptSeq - 1

    UNION ALL
    SELECT DATE(rc.ReceiptProcessDate) AS ProcessingDate
    ,      rc2.ReceiptDate AS ReceiptDate
    ,      rc2.DepositDate AS DepositDate
    ,      c.Class3 AS PaymentAccountTitle
    ,      F_GetCode(161, 2, 1) AS PaymentTargetAccountTitle
    ,      rc2.CheckingDamageInterestAmount * -1 AS Amount
    ,      c1.Class3 AS OemId
    ,      0 AS SUMType
    ,      o.EnterpriseId AS EnterpriseId
    ,      -1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_ReceiptControl rc2 ON (rc2.ReceiptSeq < rc.ReceiptSeq AND rc2.OrderSeq = rc.OrderSeq)
           INNER JOIN AT_ReceiptControl arc2 ON (rc2.ReceiptSeq = arc2.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 101 AND c.KeyCode = rc2.ReceiptAgentId)
    WHERE  (rc2.ReceiptDate < :AccountingMonth AND rc.ReceiptProcessDate >= (SELECT MAX(tmp.BusinessDate + INTERVAL 1 DAY) FROM (SELECT BusinessDate FROM T_BusinessCalendar WHERE BusinessDate >= :AccountingMonth AND BusinessFlg = 1 LIMIT 3) tmp))
    AND    (c1.Class1 <> 0 AND (o.OemClaimTransDate IS NULL OR (DATE(o.OemClaimTransDate) > DATE(rc.ReceiptDate) AND NOT (DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))))
    AND    rc2.ReceiptAgentId IS NOT NULL
    AND    rc2.CheckingDamageInterestAmount <> 0
    AND    arc.Rct_CancelFlg = 1
    AND    rc2.ReceiptSeq BETWEEN (SELECT IFNULL(MAX(tmp.ReceiptSeq), 0) FROM T_ReceiptControl tmp, AT_ReceiptControl tmp2 WHERE tmp.ReceiptSeq = tmp2.ReceiptSeq AND tmp.ReceiptSeq < rc.ReceiptSeq AND tmp.OrderSeq = rc.OrderSeq AND tmp2.Rct_CancelFlg = 1) + 1 AND rc.ReceiptSeq - 1

    UNION ALL
    -- [SQL04]
    SELECT DATE(rc.ReceiptProcessDate) AS ProcessingDate
    ,      rc2.ReceiptDate AS ReceiptDate
    ,      rc2.DepositDate AS DepositDate
    ,      c.Class3 AS PaymentAccountTitle
    ,      F_GetCode(161, 3, 1) AS PaymentTargetAccountTitle
    ,      rc2.CheckingClaimFee * -1 AS Amount
    ,      c1.Class3 AS OemId
    ,      0 AS SUMType
    ,      o.EnterpriseId AS EnterpriseId
    ,      -1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_ReceiptControl rc2 ON (rc2.ReceiptSeq < rc.ReceiptSeq AND rc2.OrderSeq = rc.OrderSeq)
           INNER JOIN AT_ReceiptControl arc2 ON (rc2.ReceiptSeq = arc2.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 101 AND c.KeyCode = rc2.ReceiptAgentId)
    WHERE  (rc2.ReceiptDate >= :AccountingMonth AND rc2.ReceiptDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    (c1.Class1 <> 0 AND (o.OemClaimTransDate IS NULL OR (DATE(o.OemClaimTransDate) > DATE(rc.ReceiptDate) AND NOT (DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))))
    AND    rc2.ReceiptAgentId IS NOT NULL
    AND    rc2.CheckingClaimFee <> 0
    AND    arc.Rct_CancelFlg = 1
    AND    rc2.ReceiptSeq BETWEEN (SELECT IFNULL(MAX(tmp.ReceiptSeq), 0) FROM T_ReceiptControl tmp, AT_ReceiptControl tmp2 WHERE tmp.ReceiptSeq = tmp2.ReceiptSeq AND tmp.ReceiptSeq < rc.ReceiptSeq AND tmp.OrderSeq = rc.OrderSeq AND tmp2.Rct_CancelFlg = 1) + 1 AND rc.ReceiptSeq - 1
    UNION ALL
    SELECT DATE(rc.ReceiptProcessDate) AS ProcessingDate
    ,      rc2.ReceiptDate AS ReceiptDate
    ,      rc2.DepositDate AS DepositDate
    ,      c.Class3 AS PaymentAccountTitle
    ,      F_GetCode(161, 3, 1) AS PaymentTargetAccountTitle
    ,      rc2.CheckingClaimFee * -1 AS Amount
    ,      c1.Class3 AS OemId
    ,      0 AS SUMType
    ,      o.EnterpriseId AS EnterpriseId
    ,      -1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_ReceiptControl rc2 ON (rc2.ReceiptSeq < rc.ReceiptSeq AND rc2.OrderSeq = rc.OrderSeq)
           INNER JOIN AT_ReceiptControl arc2 ON (rc2.ReceiptSeq = arc2.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 101 AND c.KeyCode = rc2.ReceiptAgentId)
    WHERE  (rc2.ReceiptDate < :AccountingMonth AND rc.ReceiptProcessDate >= (SELECT MAX(tmp.BusinessDate + INTERVAL 1 DAY) FROM (SELECT BusinessDate FROM T_BusinessCalendar WHERE BusinessDate >= :AccountingMonth AND BusinessFlg = 1 LIMIT 3) tmp))
    AND    (c1.Class1 <> 0 AND (o.OemClaimTransDate IS NULL OR (DATE(o.OemClaimTransDate) > DATE(rc.ReceiptDate) AND NOT (DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))))
    AND    rc2.ReceiptAgentId IS NOT NULL
    AND    rc2.CheckingClaimFee <> 0
    AND    arc.Rct_CancelFlg = 1
    AND    rc2.ReceiptSeq BETWEEN (SELECT IFNULL(MAX(tmp.ReceiptSeq), 0) FROM T_ReceiptControl tmp, AT_ReceiptControl tmp2 WHERE tmp.ReceiptSeq = tmp2.ReceiptSeq AND tmp.ReceiptSeq < rc.ReceiptSeq AND tmp.OrderSeq = rc.OrderSeq AND tmp2.Rct_CancelFlg = 1) + 1 AND rc.ReceiptSeq - 1

    /* (銀行 ReceiptClass=3) */
    UNION ALL
    -- [SQL05]
    SELECT DATE(rc.ReceiptProcessDate) AS ProcessingDate
    ,      rc2.ReceiptDate AS ReceiptDate
    ,      rc2.DepositDate AS DepositDate
    ,      c.Class3 AS PaymentAccountTitle
    ,      F_GetCode(161, 1, 1) AS PaymentTargetAccountTitle
    ,      rc2.CheckingUseAmount * -1 AS Amount
    ,      c1.Class3 AS OemId
    ,      0 AS SUMType
    ,      o.EnterpriseId AS EnterpriseId
    ,      -1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_ReceiptControl rc2 ON (rc2.ReceiptSeq < rc.ReceiptSeq AND rc2.OrderSeq = rc.OrderSeq)
           INNER JOIN AT_ReceiptControl arc2 ON (rc2.ReceiptSeq = arc2.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 151 AND c.KeyCode = rc2.BranchBankId)
    WHERE  (rc2.ReceiptDate >= :AccountingMonth AND rc2.ReceiptDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    (c1.Class1 <> 0 AND (o.OemClaimTransDate IS NULL OR (DATE(o.OemClaimTransDate) > DATE(rc.ReceiptDate) AND NOT (DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))))
    AND    rc2.ReceiptAgentId IS NULL
    AND    rc2.ReceiptClass = 3
    AND    rc2.CheckingUseAmount <> 0
    AND    arc.Rct_CancelFlg = 1
    AND    rc2.ReceiptSeq BETWEEN (SELECT IFNULL(MAX(tmp.ReceiptSeq), 0) FROM T_ReceiptControl tmp, AT_ReceiptControl tmp2 WHERE tmp.ReceiptSeq = tmp2.ReceiptSeq AND tmp.ReceiptSeq < rc.ReceiptSeq AND tmp.OrderSeq = rc.OrderSeq AND tmp2.Rct_CancelFlg = 1) + 1 AND rc.ReceiptSeq - 1
    UNION ALL
    SELECT DATE(rc.ReceiptProcessDate) AS ProcessingDate
    ,      rc2.ReceiptDate AS ReceiptDate
    ,      rc2.DepositDate AS DepositDate
    ,      c.Class3 AS PaymentAccountTitle
    ,      F_GetCode(161, 1, 1) AS PaymentTargetAccountTitle
    ,      rc2.CheckingUseAmount * -1 AS Amount
    ,      c1.Class3 AS OemId
    ,      0 AS SUMType
    ,      o.EnterpriseId AS EnterpriseId
    ,      -1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_ReceiptControl rc2 ON (rc2.ReceiptSeq < rc.ReceiptSeq AND rc2.OrderSeq = rc.OrderSeq)
           INNER JOIN AT_ReceiptControl arc2 ON (rc2.ReceiptSeq = arc2.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 151 AND c.KeyCode = rc2.BranchBankId)
    WHERE  (rc2.ReceiptDate < :AccountingMonth AND rc.ReceiptProcessDate >= (SELECT MAX(tmp.BusinessDate + INTERVAL 1 DAY) FROM (SELECT BusinessDate FROM T_BusinessCalendar WHERE BusinessDate >= :AccountingMonth AND BusinessFlg = 1 LIMIT 3) tmp))
    AND    (c1.Class1 <> 0 AND (o.OemClaimTransDate IS NULL OR (DATE(o.OemClaimTransDate) > DATE(rc.ReceiptDate) AND NOT (DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))))
    AND    rc2.ReceiptAgentId IS NULL
    AND    rc2.ReceiptClass = 3
    AND    rc2.CheckingUseAmount <> 0
    AND    arc.Rct_CancelFlg = 1
    AND    rc2.ReceiptSeq BETWEEN (SELECT IFNULL(MAX(tmp.ReceiptSeq), 0) FROM T_ReceiptControl tmp, AT_ReceiptControl tmp2 WHERE tmp.ReceiptSeq = tmp2.ReceiptSeq AND tmp.ReceiptSeq < rc.ReceiptSeq AND tmp.OrderSeq = rc.OrderSeq AND tmp2.Rct_CancelFlg = 1) + 1 AND rc.ReceiptSeq - 1

    UNION ALL
    -- [SQL06]
    SELECT DATE(rc.ReceiptProcessDate) AS ProcessingDate
    ,      rc2.ReceiptDate AS ReceiptDate
    ,      rc2.DepositDate AS DepositDate
    ,      c.Class3 AS PaymentAccountTitle
    ,      F_GetCode(161, 2, 1) AS PaymentTargetAccountTitle
    ,      rc2.CheckingDamageInterestAmount * -1 AS Amount
    ,      c1.Class3 AS OemId
    ,      0 AS SUMType
    ,      o.EnterpriseId AS EnterpriseId
    ,      -1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_ReceiptControl rc2 ON (rc2.ReceiptSeq < rc.ReceiptSeq AND rc2.OrderSeq = rc.OrderSeq)
           INNER JOIN AT_ReceiptControl arc2 ON (rc2.ReceiptSeq = arc2.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 151 AND c.KeyCode = rc2.BranchBankId)
    WHERE  (rc2.ReceiptDate >= :AccountingMonth AND rc2.ReceiptDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    (c1.Class1 <> 0 AND (o.OemClaimTransDate IS NULL OR (DATE(o.OemClaimTransDate) > DATE(rc.ReceiptDate) AND NOT (DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))))
    AND    rc2.ReceiptAgentId IS NULL
    AND    rc2.ReceiptClass = 3
    AND    rc2.CheckingDamageInterestAmount <> 0
    AND    arc.Rct_CancelFlg = 1
    AND    rc2.ReceiptSeq BETWEEN (SELECT IFNULL(MAX(tmp.ReceiptSeq), 0) FROM T_ReceiptControl tmp, AT_ReceiptControl tmp2 WHERE tmp.ReceiptSeq = tmp2.ReceiptSeq AND tmp.ReceiptSeq < rc.ReceiptSeq AND tmp.OrderSeq = rc.OrderSeq AND tmp2.Rct_CancelFlg = 1) + 1 AND rc.ReceiptSeq - 1
    UNION ALL
    SELECT DATE(rc.ReceiptProcessDate) AS ProcessingDate
    ,      rc2.ReceiptDate AS ReceiptDate
    ,      rc2.DepositDate AS DepositDate
    ,      c.Class3 AS PaymentAccountTitle
    ,      F_GetCode(161, 2, 1) AS PaymentTargetAccountTitle
    ,      rc2.CheckingDamageInterestAmount * -1 AS Amount
    ,      c1.Class3 AS OemId
    ,      0 AS SUMType
    ,      o.EnterpriseId AS EnterpriseId
    ,      -1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_ReceiptControl rc2 ON (rc2.ReceiptSeq < rc.ReceiptSeq AND rc2.OrderSeq = rc.OrderSeq)
           INNER JOIN AT_ReceiptControl arc2 ON (rc2.ReceiptSeq = arc2.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 151 AND c.KeyCode = rc2.BranchBankId)
    WHERE  (rc2.ReceiptDate < :AccountingMonth AND rc.ReceiptProcessDate >= (SELECT MAX(tmp.BusinessDate + INTERVAL 1 DAY) FROM (SELECT BusinessDate FROM T_BusinessCalendar WHERE BusinessDate >= :AccountingMonth AND BusinessFlg = 1 LIMIT 3) tmp))
    AND    (c1.Class1 <> 0 AND (o.OemClaimTransDate IS NULL OR (DATE(o.OemClaimTransDate) > DATE(rc.ReceiptDate) AND NOT (DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))))
    AND    rc2.ReceiptAgentId IS NULL
    AND    rc2.ReceiptClass = 3
    AND    rc2.CheckingDamageInterestAmount <> 0
    AND    arc.Rct_CancelFlg = 1
    AND    rc2.ReceiptSeq BETWEEN (SELECT IFNULL(MAX(tmp.ReceiptSeq), 0) FROM T_ReceiptControl tmp, AT_ReceiptControl tmp2 WHERE tmp.ReceiptSeq = tmp2.ReceiptSeq AND tmp.ReceiptSeq < rc.ReceiptSeq AND tmp.OrderSeq = rc.OrderSeq AND tmp2.Rct_CancelFlg = 1) + 1 AND rc.ReceiptSeq - 1

    UNION ALL
    -- [SQL07]
    SELECT DATE(rc.ReceiptProcessDate) AS ProcessingDate
    ,      rc2.ReceiptDate AS ReceiptDate
    ,      rc2.DepositDate AS DepositDate
    ,      c.Class3 AS PaymentAccountTitle
    ,      F_GetCode(161, 3, 1) AS PaymentTargetAccountTitle
    ,      rc2.CheckingClaimFee * -1 AS Amount
    ,      c1.Class3 AS OemId
    ,      0 AS SUMType
    ,      o.EnterpriseId AS EnterpriseId
    ,      -1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_ReceiptControl rc2 ON (rc2.ReceiptSeq < rc.ReceiptSeq AND rc2.OrderSeq = rc.OrderSeq)
           INNER JOIN AT_ReceiptControl arc2 ON (rc2.ReceiptSeq = arc2.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 151 AND c.KeyCode = rc2.BranchBankId)
    WHERE  (rc2.ReceiptDate >= :AccountingMonth AND rc2.ReceiptDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    (c1.Class1 <> 0 AND (o.OemClaimTransDate IS NULL OR (DATE(o.OemClaimTransDate) > DATE(rc.ReceiptDate) AND NOT (DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))))
    AND    rc2.ReceiptAgentId IS NULL
    AND    rc2.ReceiptClass = 3
    AND    rc2.CheckingClaimFee <> 0
    AND    arc.Rct_CancelFlg = 1
    AND    rc2.ReceiptSeq BETWEEN (SELECT IFNULL(MAX(tmp.ReceiptSeq), 0) FROM T_ReceiptControl tmp, AT_ReceiptControl tmp2 WHERE tmp.ReceiptSeq = tmp2.ReceiptSeq AND tmp.ReceiptSeq < rc.ReceiptSeq AND tmp.OrderSeq = rc.OrderSeq AND tmp2.Rct_CancelFlg = 1) + 1 AND rc.ReceiptSeq - 1
    UNION ALL
    SELECT DATE(rc.ReceiptProcessDate) AS ProcessingDate
    ,      rc2.ReceiptDate AS ReceiptDate
    ,      rc2.DepositDate AS DepositDate
    ,      c.Class3 AS PaymentAccountTitle
    ,      F_GetCode(161, 3, 1) AS PaymentTargetAccountTitle
    ,      rc2.CheckingClaimFee * -1 AS Amount
    ,      c1.Class3 AS OemId
    ,      0 AS SUMType
    ,      o.EnterpriseId AS EnterpriseId
    ,      -1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_ReceiptControl rc2 ON (rc2.ReceiptSeq < rc.ReceiptSeq AND rc2.OrderSeq = rc.OrderSeq)
           INNER JOIN AT_ReceiptControl arc2 ON (rc2.ReceiptSeq = arc2.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 151 AND c.KeyCode = rc2.BranchBankId)
    WHERE  (rc2.ReceiptDate < :AccountingMonth AND rc.ReceiptProcessDate >= (SELECT MAX(tmp.BusinessDate + INTERVAL 1 DAY) FROM (SELECT BusinessDate FROM T_BusinessCalendar WHERE BusinessDate >= :AccountingMonth AND BusinessFlg = 1 LIMIT 3) tmp))
    AND    (c1.Class1 <> 0 AND (o.OemClaimTransDate IS NULL OR (DATE(o.OemClaimTransDate) > DATE(rc.ReceiptDate) AND NOT (DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))))
    AND    rc2.ReceiptAgentId IS NULL
    AND    rc2.ReceiptClass = 3
    AND    rc2.CheckingClaimFee <> 0
    AND    arc.Rct_CancelFlg = 1
    AND    rc2.ReceiptSeq BETWEEN (SELECT IFNULL(MAX(tmp.ReceiptSeq), 0) FROM T_ReceiptControl tmp, AT_ReceiptControl tmp2 WHERE tmp.ReceiptSeq = tmp2.ReceiptSeq AND tmp.ReceiptSeq < rc.ReceiptSeq AND tmp.OrderSeq = rc.OrderSeq AND tmp2.Rct_CancelFlg = 1) + 1 AND rc.ReceiptSeq - 1

    /* (郵便局 ReceiptClass=2) */
    UNION ALL
    -- [SQL08]
    SELECT DATE(rc.ReceiptProcessDate) AS ProcessingDate
    ,      rc2.ReceiptDate AS ReceiptDate
    ,      rc2.DepositDate AS DepositDate
    ,      c.Class3 AS PaymentAccountTitle
    ,      F_GetCode(161, 1, 1) AS PaymentTargetAccountTitle
    ,      rc2.CheckingUseAmount * -1 AS Amount
    ,      c1.Class3 AS OemId
    ,      0 AS SUMType
    ,      o.EnterpriseId AS EnterpriseId
    ,      -1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_ReceiptControl rc2 ON (rc2.ReceiptSeq < rc.ReceiptSeq AND rc2.OrderSeq = rc.OrderSeq)
           INNER JOIN AT_ReceiptControl arc2 ON (rc2.ReceiptSeq = arc2.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 166 AND c.Class1 = arc2.AccountNumber)
    WHERE  (rc2.ReceiptDate >= :AccountingMonth AND rc2.ReceiptDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    (c1.Class1 <> 0 AND (o.OemClaimTransDate IS NULL OR (DATE(o.OemClaimTransDate) > DATE(rc.ReceiptDate) AND NOT (DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))))
    AND    rc2.ReceiptAgentId IS NULL
    AND    rc2.ReceiptClass = 2
    AND    rc2.CheckingUseAmount <> 0
    AND    arc.Rct_CancelFlg = 1
    AND    rc2.ReceiptSeq BETWEEN (SELECT IFNULL(MAX(tmp.ReceiptSeq), 0) FROM T_ReceiptControl tmp, AT_ReceiptControl tmp2 WHERE tmp.ReceiptSeq = tmp2.ReceiptSeq AND tmp.ReceiptSeq < rc.ReceiptSeq AND tmp.OrderSeq = rc.OrderSeq AND tmp2.Rct_CancelFlg = 1) + 1 AND rc.ReceiptSeq - 1
    UNION ALL
    SELECT DATE(rc.ReceiptProcessDate) AS ProcessingDate
    ,      rc2.ReceiptDate AS ReceiptDate
    ,      rc2.DepositDate AS DepositDate
    ,      c.Class3 AS PaymentAccountTitle
    ,      F_GetCode(161, 1, 1) AS PaymentTargetAccountTitle
    ,      rc2.CheckingUseAmount * -1 AS Amount
    ,      c1.Class3 AS OemId
    ,      0 AS SUMType
    ,      o.EnterpriseId AS EnterpriseId
    ,      -1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_ReceiptControl rc2 ON (rc2.ReceiptSeq < rc.ReceiptSeq AND rc2.OrderSeq = rc.OrderSeq)
           INNER JOIN AT_ReceiptControl arc2 ON (rc2.ReceiptSeq = arc2.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 166 AND c.Class1 = arc2.AccountNumber)
    WHERE  (rc2.ReceiptDate < :AccountingMonth AND rc.ReceiptProcessDate >= (SELECT MAX(tmp.BusinessDate + INTERVAL 1 DAY) FROM (SELECT BusinessDate FROM T_BusinessCalendar WHERE BusinessDate >= :AccountingMonth AND BusinessFlg = 1 LIMIT 3) tmp))
    AND    (c1.Class1 <> 0 AND (o.OemClaimTransDate IS NULL OR (DATE(o.OemClaimTransDate) > DATE(rc.ReceiptDate) AND NOT (DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))))
    AND    rc2.ReceiptAgentId IS NULL
    AND    rc2.ReceiptClass = 2
    AND    rc2.CheckingUseAmount <> 0
    AND    arc.Rct_CancelFlg = 1
    AND    rc2.ReceiptSeq BETWEEN (SELECT IFNULL(MAX(tmp.ReceiptSeq), 0) FROM T_ReceiptControl tmp, AT_ReceiptControl tmp2 WHERE tmp.ReceiptSeq = tmp2.ReceiptSeq AND tmp.ReceiptSeq < rc.ReceiptSeq AND tmp.OrderSeq = rc.OrderSeq AND tmp2.Rct_CancelFlg = 1) + 1 AND rc.ReceiptSeq - 1

    UNION ALL
    -- [SQL09]
    SELECT DATE(rc.ReceiptProcessDate) AS ProcessingDate
    ,      rc2.ReceiptDate AS ReceiptDate
    ,      rc2.DepositDate AS DepositDate
    ,      c.Class3 AS PaymentAccountTitle
    ,      F_GetCode(161, 2, 1) AS PaymentTargetAccountTitle
    ,      rc2.CheckingDamageInterestAmount * -1 AS Amount
    ,      c1.Class3 AS OemId
    ,      0 AS SUMType
    ,      o.EnterpriseId AS EnterpriseId
    ,      -1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_ReceiptControl rc2 ON (rc2.ReceiptSeq < rc.ReceiptSeq AND rc2.OrderSeq = rc.OrderSeq)
           INNER JOIN AT_ReceiptControl arc2 ON (rc2.ReceiptSeq = arc2.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 166 AND c.Class1 = arc2.AccountNumber)
    WHERE  (rc2.ReceiptDate >= :AccountingMonth AND rc2.ReceiptDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    (c1.Class1 <> 0 AND (o.OemClaimTransDate IS NULL OR (DATE(o.OemClaimTransDate) > DATE(rc.ReceiptDate) AND NOT (DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))))
    AND    rc2.ReceiptAgentId IS NULL
    AND    rc2.ReceiptClass = 2
    AND    rc2.CheckingDamageInterestAmount <> 0
    AND    arc.Rct_CancelFlg = 1
    AND    rc2.ReceiptSeq BETWEEN (SELECT IFNULL(MAX(tmp.ReceiptSeq), 0) FROM T_ReceiptControl tmp, AT_ReceiptControl tmp2 WHERE tmp.ReceiptSeq = tmp2.ReceiptSeq AND tmp.ReceiptSeq < rc.ReceiptSeq AND tmp.OrderSeq = rc.OrderSeq AND tmp2.Rct_CancelFlg = 1) + 1 AND rc.ReceiptSeq - 1
    UNION ALL
    SELECT DATE(rc.ReceiptProcessDate) AS ProcessingDate
    ,      rc2.ReceiptDate AS ReceiptDate
    ,      rc2.DepositDate AS DepositDate
    ,      c.Class3 AS PaymentAccountTitle
    ,      F_GetCode(161, 2, 1) AS PaymentTargetAccountTitle
    ,      rc2.CheckingDamageInterestAmount * -1 AS Amount
    ,      c1.Class3 AS OemId
    ,      0 AS SUMType
    ,      o.EnterpriseId AS EnterpriseId
    ,      -1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_ReceiptControl rc2 ON (rc2.ReceiptSeq < rc.ReceiptSeq AND rc2.OrderSeq = rc.OrderSeq)
           INNER JOIN AT_ReceiptControl arc2 ON (rc2.ReceiptSeq = arc2.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 166 AND c.Class1 = arc2.AccountNumber)
    WHERE  (rc2.ReceiptDate < :AccountingMonth AND rc.ReceiptProcessDate >= (SELECT MAX(tmp.BusinessDate + INTERVAL 1 DAY) FROM (SELECT BusinessDate FROM T_BusinessCalendar WHERE BusinessDate >= :AccountingMonth AND BusinessFlg = 1 LIMIT 3) tmp))
    AND    (c1.Class1 <> 0 AND (o.OemClaimTransDate IS NULL OR (DATE(o.OemClaimTransDate) > DATE(rc.ReceiptDate) AND NOT (DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))))
    AND    rc2.ReceiptAgentId IS NULL
    AND    rc2.ReceiptClass = 2
    AND    rc2.CheckingDamageInterestAmount <> 0
    AND    arc.Rct_CancelFlg = 1
    AND    rc2.ReceiptSeq BETWEEN (SELECT IFNULL(MAX(tmp.ReceiptSeq), 0) FROM T_ReceiptControl tmp, AT_ReceiptControl tmp2 WHERE tmp.ReceiptSeq = tmp2.ReceiptSeq AND tmp.ReceiptSeq < rc.ReceiptSeq AND tmp.OrderSeq = rc.OrderSeq AND tmp2.Rct_CancelFlg = 1) + 1 AND rc.ReceiptSeq - 1

    UNION ALL
    -- [SQL10]
    SELECT DATE(rc.ReceiptProcessDate) AS ProcessingDate
    ,      rc2.ReceiptDate AS ReceiptDate
    ,      rc2.DepositDate AS DepositDate
    ,      c.Class3 AS PaymentAccountTitle
    ,      F_GetCode(161, 3, 1) AS PaymentTargetAccountTitle
    ,      rc2.CheckingClaimFee * -1 AS Amount
    ,      c1.Class3 AS OemId
    ,      0 AS SUMType
    ,      o.EnterpriseId AS EnterpriseId
    ,      -1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_ReceiptControl rc2 ON (rc2.ReceiptSeq < rc.ReceiptSeq AND rc2.OrderSeq = rc.OrderSeq)
           INNER JOIN AT_ReceiptControl arc2 ON (rc2.ReceiptSeq = arc2.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 166 AND c.Class1 = arc2.AccountNumber)
    WHERE  (rc2.ReceiptDate >= :AccountingMonth AND rc2.ReceiptDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    (c1.Class1 <> 0 AND (o.OemClaimTransDate IS NULL OR (DATE(o.OemClaimTransDate) > DATE(rc.ReceiptDate) AND NOT (DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))))
    AND    rc2.ReceiptAgentId IS NULL
    AND    rc2.ReceiptClass = 2
    AND    rc2.CheckingClaimFee <> 0
    AND    arc.Rct_CancelFlg = 1
    AND    rc2.ReceiptSeq BETWEEN (SELECT IFNULL(MAX(tmp.ReceiptSeq), 0) FROM T_ReceiptControl tmp, AT_ReceiptControl tmp2 WHERE tmp.ReceiptSeq = tmp2.ReceiptSeq AND tmp.ReceiptSeq < rc.ReceiptSeq AND tmp.OrderSeq = rc.OrderSeq AND tmp2.Rct_CancelFlg = 1) + 1 AND rc.ReceiptSeq - 1
    UNION ALL
    SELECT DATE(rc.ReceiptProcessDate) AS ProcessingDate
    ,      rc2.ReceiptDate AS ReceiptDate
    ,      rc2.DepositDate AS DepositDate
    ,      c.Class3 AS PaymentAccountTitle
    ,      F_GetCode(161, 3, 1) AS PaymentTargetAccountTitle
    ,      rc2.CheckingClaimFee * -1 AS Amount
    ,      c1.Class3 AS OemId
    ,      0 AS SUMType
    ,      o.EnterpriseId AS EnterpriseId
    ,      -1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_ReceiptControl rc2 ON (rc2.ReceiptSeq < rc.ReceiptSeq AND rc2.OrderSeq = rc.OrderSeq)
           INNER JOIN AT_ReceiptControl arc2 ON (rc2.ReceiptSeq = arc2.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 166 AND c.Class1 = arc2.AccountNumber)
    WHERE  (rc2.ReceiptDate < :AccountingMonth AND rc.ReceiptProcessDate >= (SELECT MAX(tmp.BusinessDate + INTERVAL 1 DAY) FROM (SELECT BusinessDate FROM T_BusinessCalendar WHERE BusinessDate >= :AccountingMonth AND BusinessFlg = 1 LIMIT 3) tmp))
    AND    (c1.Class1 <> 0 AND (o.OemClaimTransDate IS NULL OR (DATE(o.OemClaimTransDate) > DATE(rc.ReceiptDate) AND NOT (DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))))
    AND    rc2.ReceiptAgentId IS NULL
    AND    rc2.ReceiptClass = 2
    AND    rc2.CheckingClaimFee <> 0
    AND    arc.Rct_CancelFlg = 1
    AND    rc2.ReceiptSeq BETWEEN (SELECT IFNULL(MAX(tmp.ReceiptSeq), 0) FROM T_ReceiptControl tmp, AT_ReceiptControl tmp2 WHERE tmp.ReceiptSeq = tmp2.ReceiptSeq AND tmp.ReceiptSeq < rc.ReceiptSeq AND tmp.OrderSeq = rc.OrderSeq AND tmp2.Rct_CancelFlg = 1) + 1 AND rc.ReceiptSeq - 1

    /* (その他 ReceiptClass=9) */
    UNION ALL
    -- [SQL11]
    SELECT DATE(rc.ReceiptProcessDate) AS ProcessingDate
    ,      rc2.ReceiptDate AS ReceiptDate
    ,      rc2.DepositDate AS DepositDate
    ,      c.Class3 AS PaymentAccountTitle
    ,      F_GetCode(161, 1, 1) AS PaymentTargetAccountTitle
    ,      rc2.CheckingUseAmount * -1 AS Amount
    ,      c1.Class3 AS OemId
    ,      0 AS SUMType
    ,      o.EnterpriseId AS EnterpriseId
    ,      -1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_ReceiptControl rc2 ON (rc2.ReceiptSeq < rc.ReceiptSeq AND rc2.OrderSeq = rc.OrderSeq)
           INNER JOIN AT_ReceiptControl arc2 ON (rc2.ReceiptSeq = arc2.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 155 AND c.KeyCode = arc2.ClassDetails)
    WHERE  (rc2.ReceiptDate >= :AccountingMonth AND rc2.ReceiptDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    (c1.Class1 <> 0 AND (o.OemClaimTransDate IS NULL OR (DATE(o.OemClaimTransDate) > DATE(rc.ReceiptDate) AND NOT (DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))))
    AND    rc2.ReceiptAgentId IS NULL
    AND    rc2.ReceiptClass = 9
    AND    rc2.CheckingUseAmount <> 0
    AND    arc.Rct_CancelFlg = 1
    AND    rc2.ReceiptSeq BETWEEN (SELECT IFNULL(MAX(tmp.ReceiptSeq), 0) FROM T_ReceiptControl tmp, AT_ReceiptControl tmp2 WHERE tmp.ReceiptSeq = tmp2.ReceiptSeq AND tmp.ReceiptSeq < rc.ReceiptSeq AND tmp.OrderSeq = rc.OrderSeq AND tmp2.Rct_CancelFlg = 1) + 1 AND rc.ReceiptSeq - 1
    UNION ALL
    SELECT DATE(rc.ReceiptProcessDate) AS ProcessingDate
    ,      rc2.ReceiptDate AS ReceiptDate
    ,      rc2.DepositDate AS DepositDate
    ,      c.Class3 AS PaymentAccountTitle
    ,      F_GetCode(161, 1, 1) AS PaymentTargetAccountTitle
    ,      rc2.CheckingUseAmount * -1 AS Amount
    ,      c1.Class3 AS OemId
    ,      0 AS SUMType
    ,      o.EnterpriseId AS EnterpriseId
    ,      -1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_ReceiptControl rc2 ON (rc2.ReceiptSeq < rc.ReceiptSeq AND rc2.OrderSeq = rc.OrderSeq)
           INNER JOIN AT_ReceiptControl arc2 ON (rc2.ReceiptSeq = arc2.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 155 AND c.KeyCode = arc2.ClassDetails)
    WHERE  (rc2.ReceiptDate < :AccountingMonth AND rc.ReceiptProcessDate >= (SELECT MAX(tmp.BusinessDate + INTERVAL 1 DAY) FROM (SELECT BusinessDate FROM T_BusinessCalendar WHERE BusinessDate >= :AccountingMonth AND BusinessFlg = 1 LIMIT 3) tmp))
    AND    (c1.Class1 <> 0 AND (o.OemClaimTransDate IS NULL OR (DATE(o.OemClaimTransDate) > DATE(rc.ReceiptDate) AND NOT (DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))))
    AND    rc2.ReceiptAgentId IS NULL
    AND    rc2.ReceiptClass = 9
    AND    rc2.CheckingUseAmount <> 0
    AND    arc.Rct_CancelFlg = 1
    AND    rc2.ReceiptSeq BETWEEN (SELECT IFNULL(MAX(tmp.ReceiptSeq), 0) FROM T_ReceiptControl tmp, AT_ReceiptControl tmp2 WHERE tmp.ReceiptSeq = tmp2.ReceiptSeq AND tmp.ReceiptSeq < rc.ReceiptSeq AND tmp.OrderSeq = rc.OrderSeq AND tmp2.Rct_CancelFlg = 1) + 1 AND rc.ReceiptSeq - 1

    UNION ALL
    -- [SQL12]
    SELECT DATE(rc.ReceiptProcessDate) AS ProcessingDate
    ,      rc2.ReceiptDate AS ReceiptDate
    ,      rc2.DepositDate AS DepositDate
    ,      c.Class3 AS PaymentAccountTitle
    ,      F_GetCode(161, 2, 1) AS PaymentTargetAccountTitle
    ,      rc2.CheckingDamageInterestAmount * -1 AS Amount
    ,      c1.Class3 AS OemId
    ,      0 AS SUMType
    ,      o.EnterpriseId AS EnterpriseId
    ,      -1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_ReceiptControl rc2 ON (rc2.ReceiptSeq < rc.ReceiptSeq AND rc2.OrderSeq = rc.OrderSeq)
           INNER JOIN AT_ReceiptControl arc2 ON (rc2.ReceiptSeq = arc2.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 155 AND c.KeyCode = arc2.ClassDetails)
    WHERE  (rc2.ReceiptDate >= :AccountingMonth AND rc2.ReceiptDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    (c1.Class1 <> 0 AND (o.OemClaimTransDate IS NULL OR (DATE(o.OemClaimTransDate) > DATE(rc.ReceiptDate) AND NOT (DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))))
    AND    rc2.ReceiptAgentId IS NULL
    AND    rc2.ReceiptClass = 9
    AND    rc2.CheckingDamageInterestAmount <> 0
    AND    arc.Rct_CancelFlg = 1
    AND    rc2.ReceiptSeq BETWEEN (SELECT IFNULL(MAX(tmp.ReceiptSeq), 0) FROM T_ReceiptControl tmp, AT_ReceiptControl tmp2 WHERE tmp.ReceiptSeq = tmp2.ReceiptSeq AND tmp.ReceiptSeq < rc.ReceiptSeq AND tmp.OrderSeq = rc.OrderSeq AND tmp2.Rct_CancelFlg = 1) + 1 AND rc.ReceiptSeq - 1
    UNION ALL
    SELECT DATE(rc.ReceiptProcessDate) AS ProcessingDate
    ,      rc2.ReceiptDate AS ReceiptDate
    ,      rc2.DepositDate AS DepositDate
    ,      c.Class3 AS PaymentAccountTitle
    ,      F_GetCode(161, 2, 1) AS PaymentTargetAccountTitle
    ,      rc2.CheckingDamageInterestAmount * -1 AS Amount
    ,      c1.Class3 AS OemId
    ,      0 AS SUMType
    ,      o.EnterpriseId AS EnterpriseId
    ,      -1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_ReceiptControl rc2 ON (rc2.ReceiptSeq < rc.ReceiptSeq AND rc2.OrderSeq = rc.OrderSeq)
           INNER JOIN AT_ReceiptControl arc2 ON (rc2.ReceiptSeq = arc2.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 155 AND c.KeyCode = arc2.ClassDetails)
    WHERE  (rc2.ReceiptDate < :AccountingMonth AND rc.ReceiptProcessDate >= (SELECT MAX(tmp.BusinessDate + INTERVAL 1 DAY) FROM (SELECT BusinessDate FROM T_BusinessCalendar WHERE BusinessDate >= :AccountingMonth AND BusinessFlg = 1 LIMIT 3) tmp))
    AND    (c1.Class1 <> 0 AND (o.OemClaimTransDate IS NULL OR (DATE(o.OemClaimTransDate) > DATE(rc.ReceiptDate) AND NOT (DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))))
    AND    rc2.ReceiptAgentId IS NULL
    AND    rc2.ReceiptClass = 9
    AND    rc2.CheckingDamageInterestAmount <> 0
    AND    arc.Rct_CancelFlg = 1
    AND    rc2.ReceiptSeq BETWEEN (SELECT IFNULL(MAX(tmp.ReceiptSeq), 0) FROM T_ReceiptControl tmp, AT_ReceiptControl tmp2 WHERE tmp.ReceiptSeq = tmp2.ReceiptSeq AND tmp.ReceiptSeq < rc.ReceiptSeq AND tmp.OrderSeq = rc.OrderSeq AND tmp2.Rct_CancelFlg = 1) + 1 AND rc.ReceiptSeq - 1

    UNION ALL
    -- [SQL13]
    SELECT DATE(rc.ReceiptProcessDate) AS ProcessingDate
    ,      rc2.ReceiptDate AS ReceiptDate
    ,      rc2.DepositDate AS DepositDate
    ,      c.Class3 AS PaymentAccountTitle
    ,      F_GetCode(161, 3, 1) AS PaymentTargetAccountTitle
    ,      rc2.CheckingClaimFee * -1 AS Amount
    ,      c1.Class3 AS OemId
    ,      0 AS SUMType
    ,      o.EnterpriseId AS EnterpriseId
    ,      -1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_ReceiptControl rc2 ON (rc2.ReceiptSeq < rc.ReceiptSeq AND rc2.OrderSeq = rc.OrderSeq)
           INNER JOIN AT_ReceiptControl arc2 ON (rc2.ReceiptSeq = arc2.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 155 AND c.KeyCode = arc2.ClassDetails)
    WHERE  (rc2.ReceiptDate >= :AccountingMonth AND rc2.ReceiptDate < DATE(:AccountingMonth + INTERVAL 1 MONTH))
    AND    (c1.Class1 <> 0 AND (o.OemClaimTransDate IS NULL OR (DATE(o.OemClaimTransDate) > DATE(rc.ReceiptDate) AND NOT (DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))))
    AND    rc2.ReceiptAgentId IS NULL
    AND    rc2.ReceiptClass = 9
    AND    rc2.CheckingClaimFee <> 0
    AND    arc.Rct_CancelFlg = 1
    AND    rc2.ReceiptSeq BETWEEN (SELECT IFNULL(MAX(tmp.ReceiptSeq), 0) FROM T_ReceiptControl tmp, AT_ReceiptControl tmp2 WHERE tmp.ReceiptSeq = tmp2.ReceiptSeq AND tmp.ReceiptSeq < rc.ReceiptSeq AND tmp.OrderSeq = rc.OrderSeq AND tmp2.Rct_CancelFlg = 1) + 1 AND rc.ReceiptSeq - 1
    UNION ALL
    SELECT DATE(rc.ReceiptProcessDate) AS ProcessingDate
    ,      rc2.ReceiptDate AS ReceiptDate
    ,      rc2.DepositDate AS DepositDate
    ,      c.Class3 AS PaymentAccountTitle
    ,      F_GetCode(161, 3, 1) AS PaymentTargetAccountTitle
    ,      rc2.CheckingClaimFee * -1 AS Amount
    ,      c1.Class3 AS OemId
    ,      0 AS SUMType
    ,      o.EnterpriseId AS EnterpriseId
    ,      -1
    ,      rc.OrderSeq
    FROM   T_ReceiptControl rc
           INNER JOIN AT_ReceiptControl arc ON (rc.ReceiptSeq = arc.ReceiptSeq)
           INNER JOIN T_ReceiptControl rc2 ON (rc2.ReceiptSeq < rc.ReceiptSeq AND rc2.OrderSeq = rc.OrderSeq)
           INNER JOIN AT_ReceiptControl arc2 ON (rc2.ReceiptSeq = arc2.ReceiptSeq)
           INNER JOIN T_Order o ON (rc.OrderSeq = o.OrderSeq)
           INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0))
           LEFT OUTER JOIN M_Code c ON (c.CodeId = 155 AND c.KeyCode = arc2.ClassDetails)
    WHERE  (rc2.ReceiptDate < :AccountingMonth AND rc.ReceiptProcessDate >= (SELECT MAX(tmp.BusinessDate + INTERVAL 1 DAY) FROM (SELECT BusinessDate FROM T_BusinessCalendar WHERE BusinessDate >= :AccountingMonth AND BusinessFlg = 1 LIMIT 3) tmp))
    AND    (c1.Class1 <> 0 AND (o.OemClaimTransDate IS NULL OR (DATE(o.OemClaimTransDate) > DATE(rc.ReceiptDate) AND NOT (DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01') AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01'))))))
    AND    rc2.ReceiptAgentId IS NULL
    AND    rc2.ReceiptClass = 9
    AND    rc2.CheckingClaimFee <> 0
    AND    arc.Rct_CancelFlg = 1
    AND    rc2.ReceiptSeq BETWEEN (SELECT IFNULL(MAX(tmp.ReceiptSeq), 0) FROM T_ReceiptControl tmp, AT_ReceiptControl tmp2 WHERE tmp.ReceiptSeq = tmp2.ReceiptSeq AND tmp.ReceiptSeq < rc.ReceiptSeq AND tmp.OrderSeq = rc.OrderSeq AND tmp2.Rct_CancelFlg = 1) + 1 AND rc.ReceiptSeq - 1

EOQ;
        // SQL実行
        $this->app->dbAdapter->query($sql)->execute($prm);


        // 入金取消の相殺
        $sql = 'SELECT Seq, ProcessingDate, ReceiptDate, DepositDate, PaymentAccountTitle, PaymentTargetAccountTitle, Amount, OrderSeq FROM AW_KaribaraikinHikeiMonth WHERE SUMType = 0 AND Amount < 0 ';  // 入金取消のレコードを取得
        $ri = $this->app->dbAdapter->query($sql)->execute(null);
        foreach ($ri as $row) {
            $wsql  = ' SELECT IFNULL(MIN(Seq), -1) As Seq ';
            $wsql .= ' FROM   AW_KaribaraikinHikeiMonth ';
            $wsql .= ' WHERE  1 = 1 ';
            $wsql .= ' AND    OrderSeq = :OrderSeq ';
            $wsql .= ' AND    Amount = :Amount ';
            $wsql .= ' AND    ProcessingDate = :ProcessingDate ';
            $wsql .= ' AND    ReceiptDate = :ReceiptDate ';
            $wsql .= ' AND    PaymentAccountTitle = :PaymentAccountTitle ';
            $wsql .= ' AND    PaymentTargetAccountTitle = :PaymentTargetAccountTitle ';
            $wsql .= ' AND    TargetFlg = 1 ';

            $wprm = array(
                    ':OrderSeq' => $row['OrderSeq'],
                    ':Amount' => $row['Amount'] * -1,
                    ':ProcessingDate' => $row['ProcessingDate'],
                    ':ReceiptDate' => $row['ReceiptDate'],
                    ':PaymentAccountTitle' => $row['PaymentAccountTitle'],
                    ':PaymentTargetAccountTitle' => $row['PaymentTargetAccountTitle'],
            );

            $wrow = $this->app->dbAdapter->query($wsql)->execute($wprm)->current();

            if ($wrow['Seq'] > 0) {
                $wsql = ' UPDATE AW_KaribaraikinHikeiMonth SET TargetFlg = 0 WHERE Seq = :Seq ';
                $this->app->dbAdapter->query($wsql)->execute(array(':Seq' => $row['Seq']));
                $this->app->dbAdapter->query($wsql)->execute(array(':Seq' => $wrow['Seq']));
            }

        }

        $basesql = ' SELECT * FROM AW_KaribaraikinHikeiMonth WHERE TargetFlg = 1 ';

        $sql  = " SELECT tmp.ProcessingDate ";
        $sql .= " ,      :AccountingMonth AS AccountDate ";
        $sql .= " ,      tmp.ReceiptDate AS PaymentDate ";
        $sql .= " ,      tmp.DepositDate AS ReceiptProcessDate ";
        $sql .= " ,      tmp.PaymentAccountTitle ";
        $sql .= " ,      tmp.PaymentTargetAccountTitle ";
        $sql .= " ,      COUNT(1) AS PaymentNumber ";
        $sql .= " ,      SUM(tmp.Amount) AS Amount ";
        $sql .= " FROM ( ";
        $sql .= $basesql;
        $sql .= " ) tmp ";
        $sql .= " GROUP BY tmp.ProcessingDate, tmp.ReceiptDate, tmp.DepositDate, tmp.PaymentAccountTitle, tmp.PaymentTargetAccountTitle ";
        $sql .= " ORDER BY ProcessingDate, AccountDate, PaymentDate, ReceiptProcessDate, PaymentAccountTitle ";

        $ri = $this->app->dbAdapter->query($sql)->execute($prm);
        $datas = ResultInterfaceToArray($ri);

        $templateId = 'CKI24174_12'; // 仮払金日計
        $templateClass = 0;
        $seq = 0;
        $templatePattern = 0;
        $tmpFileName = $tmpFilePath . '12.仮払金日計_' . $formatNowStr . '.csv';

        $logicTemplate = new LogicTemplate($this->app->dbAdapter);
        $result = $logicTemplate->convertArraytoFile( $datas, $tmpFileName, $templateId, $templateClass, $seq, $templatePattern );

        //-----------------------------------------------------------
        // [02.OEM日次統計表(月次)]用の処理
        //-----------------------------------------------------------
        $sumsql  = " SELECT tmp.OemId ";
        $sumsql .= " ,      tmp.EnterpriseId AS EnterpriseId ";
        $sumsql .= " ,      SUM(CASE WHEN tmp.SUMType = 0 THEN 1 ELSE 0 END) AS CNT_SUMType0 ";
        $sumsql .= " ,      SUM(CASE WHEN tmp.SUMType = 0 THEN tmp.Amount ELSE 0 END) * -1 AS SUM_Amount0 ";
        $sumsql .= " ,      SUM(CASE WHEN tmp.SUMType = 1 THEN 1 ELSE 0 END) AS CNT_SUMType1 ";
        $sumsql .= " ,      SUM(CASE WHEN tmp.SUMType = 1 THEN tmp.Amount ELSE 0 END) * -1 AS SUM_Amount1 ";
        $sumsql .= " ,      SUM(CASE WHEN tmp.SUMType = 3 THEN 1 ELSE 0 END) AS CNT_SUMType3 ";
        $sumsql .= " ,      SUM(CASE WHEN tmp.SUMType = 3 THEN tmp.Amount ELSE 0 END) * -1 AS SUM_Amount3 ";
        $sumsql .= " FROM ( ";
        $sumsql .= $basesql;
        $sumsql .= " ) tmp ";
        $sumsql .= " GROUP BY tmp.OemId, tmp.EnterpriseId ";
        $sumsql .= " WITH ROLLUP ";

        $ri = $this->app->dbAdapter->query($sumsql)->execute($prm);
        foreach ($ri as $row) {
            if ( $row['OemId'] != null && $row['EnterpriseId'] == null ) {
                $this->_ary02[12][$row['OemId']] = $row;

            } elseif( $row['EnterpriseId'] != null ) {
                $this->_aryEnt02[12][$row['EnterpriseId']] = $row;

            }
        }

        return $tmpFileName;
    }
}
