<?php
namespace oemadmin\Controller;

use Coral\Coral\Controller\CoralControllerAction;
use DOMPDFModule\View\Model\PdfModel;
use models\Logic\LogicOemTradingSettlement;
use models\Table\TableOem;
use models\Table\TableOemClaimed;
use models\Table\TableSystemProperty;
use oemadmin\Application;

class PdfController extends CoralControllerAction
{
    /**
     * アプリケーションオブジェクト
     * @var Application
     */
    private $app;

    protected function _init()
    {
        $this->app = Application::getInstance();
    }

    /**
     * 精算データPDFダウンロード
     */
    public function monthlysettlementAction() {

        $params = $this->getParams();
        $fixedDate = !isset( $params['fd'] ) ? -1 : $params['fd'];

        $cb['logo'] = 'Atobarai_logo_3.gif';
        $cb['company'] = '株式会社キャッチボール';
        $cb['postAddr'] = '〒140-0002';
        $cb['addr'] = '東京都品川区東品川2-2-24';
        $cb['subAddr'] = '天王洲セントラルタワー 12F';

        $oemInfo = Application::getInstance()->getOemInfo();
        $mdlOem = new TableOem($this->app->dbAdapter);
        $oem = $mdlOem->findOem2($oemInfo['OemId'])->current();

        $mdloc = new TableOemClaimed($this->app->dbAdapter);

        $data_list = array();

        //$fdをFromとToに分解
        $search_range = explode("_", $fixedDate);
        $data_list = $mdloc->findOemClaimed($oemInfo['OemId'],isset($search_range[0])?$search_range[0]:null,
        isset($search_range[1])?$search_range[1]:null)->current();

        //データ取得に失敗していた時用に初期化
        if(empty($data_list)){
            $data_list = array(
                    "SpanFrom" => "",
                    "SpanTo" => "",
                    "UseAmount" => "",
                    "PC_DecisionPayment" => "",
                    "FixedTransferAmount" => "",
                    "OM_TotalProfit" => "",
                    "SettlePlanDate" => "",
                    "CB_MonthlyFee" => "",
                    "OM_ShopTotal" => "",
                    "OM_SettleShopTotal" => "",
                    "EntMonthlyFeeTotal" => "",
                    "CB_EntMonthlyFee" => "",
                    "OM_EntMonthlyFee" => "",
                    "OrderCount" => "",
                    "SettlementFeeTotal" => "",
                    "CB_SettlementFee" => "",
                    "OM_SettlementFee" => "",
                    "ClaimFeeBSTotal" => "",
                    "CB_ClaimFeeBS" => "",
                    "OM_ClaimFeeBS" => "",
                    "ClaimFeeDKTotal" => "",
                    "CB_ClaimFeeDK" => "",
                    "OM_ClaimFeeDK" => "",
                    "CR_TotalAmount" => "",
                    "CR_OemAmount" => "",
                    "CR_EntAmount" => "",
                    "OM_AdjustmentAmount" => "",
                    "PC_TransferCommission" => "",
                    "AgencyFee" => ""
            );
        }

        // 消費税算出　（短命につきハードコーディング）
        $mdlsp = new TableSystemProperty($this->app->dbAdapter);
        $taxRate = $mdlsp->getTaxRateAt($data_list['ProcessDate']) * 0.01;
        $data_list['TotalProfitTax'] = floor(($data_list['OM_TotalProfit'] > 0 ? $data_list['OM_TotalProfit'] : 0) * $taxRate);
        $data_list['DspTaxFlg'] = ($oem['DspTaxFlg'] == 1);

        // 調整額一覧
        $logicots = new LogicOemTradingSettlement($this->app->dbAdapter);
        $adjustment_list = $logicots->getOemAdjustmentAmount(empty($data_list)?null:$data_list['OemClaimedSeq']);

        $fileName = sprintf( 'payingdata_%s.pdf', date( "YmdHis" ) );

        $this->view->assign('cb', $cb);
        $this->view->assign('oemData', $oemInfo);
        $this->view->assign('data_list', $data_list);
        $this->view->assign('adjustment_list', $adjustment_list);
        $this->view->assign( 'documentRoot', $_SERVER['DOCUMENT_ROOT'] );
        $this->view->assign( 'title', $fileName );

        $viewRender = $this->getServiceLocator()->get('ViewRenderer');
        $html = $viewRender->render($this->view);

        // 一時ファイルの保存先
        $mdlsp = new \models\Table\TableSystemProperty($this->app->dbAdapter);
        $tempDir = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'TempFileDir');
        $tempDir = realpath($tempDir);

        // 出力ファイル名
        $outFileName = $fileName;

        // 中間ファイル名
        $fname_html = ($tempDir . '/__tmp_' . $fileName . '__.html');
        $fname_pdf  = ($tempDir . '/__tmp_' . $fileName . '__.pdf');

        // HTML出力
        file_put_contents($fname_html, $html);

        // PDF変換(外部プログラム起動)
        $ename = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'wkhtmltopdf');
        $option = " --page-size A4 --orientation landscape  --margin-left 0 --margin-right 0 --margin-top 0 --margin-bottom 0 ";
        exec($ename . $option . $fname_html . ' ' . $fname_pdf);

        unlink($fname_html);

        header( 'Content-Type: application/octet-stream; name="' . $outFileName . '"' );
        header( 'Content-Disposition: attachment; filename="' . $outFileName . '"' );
        header( 'Content-Length: ' . filesize( $fname_pdf ) );

        // 出力
        echo readfile( $fname_pdf );

        unlink( $fname_pdf );
        die();
    }
}
