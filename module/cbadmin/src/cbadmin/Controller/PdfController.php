<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace cbadmin\Controller;

use cbadmin\Application;
use Coral\Coral\Controller\CoralControllerAction;
use DOMPDFModule\View\Model\PdfModel;
use models\Table\TableOemClaimed;
use models\Table\TableOem;
use Zend\Config\Reader\Ini;
use Coral\Base\IO\BaseIOCsvWriter;
use models\Logic\LogicTemplate;

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

    public function doemmonthlyseisansyoAction()
    {
        $params = $this->getParams();

        $oemId = !isset( $params['oemid'] ) ? -1 : $params['oemid'];
        $fixedDate = !isset( $params['fd'] ) ? -1 : $params['fd'];

        $search_range = explode( "_", $fixedDate );

        // OEMデータ取得
        $mdloem = new TableOem( $this->app->dbAdapter );
        $oemData = $mdloem->findOem2( $oemId )->current();

        // OEM請求データ取得
        $mdloc = new TableOemClaimed( $this->app->dbAdapter );
        $data = $mdloc->findOemClaimed( $oemId, $search_range[0], $search_range[1] )->current();

        // 消費税算出　（短命につきハードコーディング）
        $data['TotalProfitTax'] = floor( ( $data['OM_TotalProfit'] > 0 ? $data['OM_TotalProfit'] : 0 ) * 0.08 );

        // 生産調整額データ取得
        $sql  = ' SELECT OA.SerialNumber ';
        $sql .= ' ,      OA.OrderId ';
        $sql .= ' ,      ( SELECT KeyContent FROM M_Code WHERE CodeId = 89 AND KeyCode = OA.ItemCode ) AS ItemCodeName ';
        $sql .= ' ,      OA.AdjustmentAmount ';
        $sql .= ' ,      OA.RegistDate ';
        $sql .= ' ,      F_GetLoginUserName( OA.RegistId ) AS RegistName ';
        $sql .= ' ,      C.NameKj ';
        $sql .= ' FROM T_OemAdjustmentAmount OA LEFT JOIN ';
        $sql .= '      T_Customer C ON C.OrderSeq = OA.OrderSeq ';
        $sql .= ' WHERE OemClaimedSeq = :OemClaimedSeq ';
        $sql .= ' AND OA.ValidFlg = 1 ';
        $stm = $this->app->dbAdapter->query( $sql );

        $prm = array( ':OemClaimedSeq' => $data['OemClaimedSeq'] );
        $oemAdjustmentAmount = ResultInterfaceToArray( $stm->execute( $prm ) );

        $fileName = sprintf( 'Seikyu_%s_%s.pdf', date( "YmdHis" ), $data['OemId'] );

        $this->view->assign( 'data', $data );
        $this->view->assign( 'oemData', $oemData );
        $this->view->assign( 'oemAdjustmentAmount', $oemAdjustmentAmount );
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
        $option = " --page-size A4 --orientation landscape --margin-left 0 --margin-right 0 --margin-top 0 --margin-bottom 0 ";
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
