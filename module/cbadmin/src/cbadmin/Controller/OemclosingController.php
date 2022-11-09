<?php
namespace cbadmin\Controller;

use cbadmin\Application;
use Coral\Coral\Controller\CoralControllerAction;
use Coral\Coral\CoralValidate;
use Coral\Base\BaseGeneralUtils;
use models\Table\TablePayingControl;
use models\Table\TableOemEnterpriseClaimed;
use models\Table\TableOemClaimed;
use models\Table\TableOem;
use models\Logic\LogicTemplate;
use models\Table\TableUser;

class OemclosingController extends CoralControllerAction
{
    protected $_componentRoot = './application/views/components';

    /**
     * アプリケーションオブジェクト
     * @var Application
     */
    private $app;

    /**
     * OEMリスト
     * @var array
     */
    protected $oemList;

    //過去xxか月前からの明細表示デフォルト値
    const DEFAULT_FROM_MONTH = 6;

    /**
     * Controllerを初期化する
     */
    public function _init()
    {
        $this->app = Application::getInstance();
        $this->view->assign('userInfo', $this->app->authManagerAdmin->getUserInfo());

        $this->addStyleSheet('../css/default02.css')
        ->addStyleSheet('../css/cbadmin/oemclosing/list/default.css')
        ->addJavaScript('../js/prototype.js');

        $this->setPageTitle("後払い.com - OEM明細確認");

        // OEM先リスト作成
        $mdlOem = new TableOem($this->app->dbAdapter);
        $oems = ResultInterfaceToArray( $mdlOem->getAllOem() );
        foreach( $oems as $oem ) {
            $this->oemList[$oem['OemId']] = $oem['OemNameKj'];
        }
    }

    /**
     *
     */
    public function indexAction()
    {
        $params = $this->getParams();
        $f = !isset( $params['f'] ) ? '' : $params['f'];
        $t = !isset( $params['t'] ) ? '' : $params['t'];
        $oemId = !isset( $params['oemid'] ) ? '' : $params['oemid'];

        //日付チェック
        if (!CoralValidate::isDate($t)){
            $t = date('Y-m-t');
        }
//        $t_time = $t." 23:59:59";

        //日付チェック
        if (!CoralValidate::isDate($f)){

            //NGの場合xxヶ月前とする
            $f = date("Y-m-d",strtotime('-'.self::DEFAULT_FROM_MONTH.' month' , strtotime($t)));

        }

        $list = array();
        if( !empty( $oemId ) ) {
//            $list = $this->getOemClaimedList($oemId, $f,$t_time);
            $list = $this->getOemClaimedList($oemId, $f,$t);
        }

        // count関数対策
        $dataCount = 0;
        if(!empty($list)) {
            $dataCount = count($list);
        }

        $this->view->assign('list', $list);
        $this->view->assign('DataCount', $dataCount);
        $this->view->assign('f', $f);
        $this->view->assign('t', $t);
        $this->view->assign('oemid', $oemId);
        $this->view->assign('oemList', $this->oemList);

        return $this->view;
    }

    /**
     * OEM締め処理および債権明細の作成を行う
     */
    public function closingAction()
    {
        $params  = $this->getParams();

        $oemId = $params['oemid'];
        $t = $params['t'];
        $f = $params['f'];

        if( !CoralValidate::isDate( $t ) ) {
            $t = date('Y-m-t');
        }

        //日付チェック
        if ( !CoralValidate::isDate( $f ) ) {
            // NGの場合xxヶ月前とする
            $f = date("Y-m-d", strtotime('-'.self::DEFAULT_FROM_MONTH.' month' , strtotime( $t ) ) );
        }

        // ユーザーIDの取得
        $obj = new TableUser( $this->app->dbAdapter );
        $userId = $obj->getUserId( 0, $this->app->authManagerAdmin->getUserInfo()->OpId );

        // OEM加盟店請求のリストを取得
        $mdlOEC = new TableOemEnterpriseClaimed( $this->app->dbAdapter );
        $oemEnterpriseClaimedList = ResultInterfaceToArray( $mdlOEC->findOemEnterpriseClaimed( $oemId ) );

        // OEM請求のリストを取得
        $lastday = date('Y-m-d', strtotime(date('Y-m-d') . " -1 day")); // 前日
        $to = $t;
        if ($t >= $lastday) {
            // toが前日以降の場合、前日までを範囲とする
            $to = $lastday;
        }
        $oemClaimedList = $this->getOemClaimedList( $oemId, $f, $to );

        $mdlOC = new TableOemClaimed( $this->app->dbAdapter );

        foreach( $oemEnterpriseClaimedList as $oemEnterpriseClaimed ) {
            foreach( $oemClaimedList as $oemClaimed ) {
                if( $oemEnterpriseClaimed['OemClaimedSeq'] == $oemClaimed['OemClaimedSeq']
                  && $oemClaimed['PayingControlStatus'] == 0 ) {
                      $mdlOEC->saveUpdate( array( 'ProcessDate' => date( 'Y-m-d' ), 'UpdateId' => $userId ), $oemEnterpriseClaimed['OemEnterpriseClaimedSeq'] );
                      $mdlOC->saveUpdate( array( 'ProcessDate' => date( 'Y-m-d' ), 'PayingControlStatus' => 1, 'UpdateId' => $userId ), $oemClaimed['OemClaimedSeq'] );
                }
            }
        }

        $list = $this->getOemClaimedList( $oemId, $f, $t );
        $params['list'] = $list;
        $params['DataCount'] = count( $list );

//         // 処理が終わったらリダイレクト
//         $this->_redirect('oemclosing/index');
        // 処理が終わったらフォワード
        return $this->_forward( 'index', $params );
    }

    /**
     * OEM明細CSVダウンロード
     */
    public function dmeisaiAction()
    {
        $params  = $this->getParams();

        $oemId = $params['oemid'];
        $t = $params['t'];
        $f = $params['f'];

        if( !CoralValidate::isDate( $t ) ) {
            $t = date('Y-m-t');
        }

        //日付チェック
        if ( !CoralValidate::isDate( $f ) ) {
            // NGの場合xxヶ月前とする
            $f = date("Y-m-d", strtotime('-'.self::DEFAULT_FROM_MONTH.' month' , strtotime( $t ) ) );
        }

        $oemClaimedList = $this->getOemClaimedList( $oemId, $f, $t );

        foreach( $oemClaimedList as $i => $oemClaimed ) {
            $oemClaimed['PayingControlStatus'] = $oemClaimed['PayingControlStatus'] == '0' ? '仮' : '本';
            $oemClaimed['Span'] = sprintf( "%s　～　%s", $oemClaimed['SpanFrom'], $oemClaimed['SpanTo'] );
            $oemClaimedList[$i] = $oemClaimed;
        }

        $templateId = 'CKI13096_1';    // OEM明細CSV
        $templateClass = 1;
        $seq = $oemId;
        $templatePattern = 0;

        $logicTemplate = new LogicTemplate( $this->app->dbAdapter );
        $response = $logicTemplate->convertArraytoResponse( $oemClaimedList, sprintf( 'Oem_Meisai_%s.csv', date('YmdHis') ), $templateId, $templateClass, $seq, $templatePattern, $this->getResponse() );

        if( $response == false ) {
            throw new \Exception( $logicTemplate->getErrorMessage() );
        }

        return $response;
    }

    /**
     * OEM請求データを取得する
     * @return array
     */
    private function getOemClaimedList($oemId, $from = null,$to = null)
    {
        $wheres = array();
        $query = <<<EOQ
                SELECT
                    OCD.OemClaimedSeq as OemClaimedSeq,
                    OEM.OemId,
                    OEM.OemNameKj,
                    OCD.SpanFrom,
                    OCD.SpanTo,
                    OCD.FixedTransferAmount,
                    OCD.SettlePlanDate,
                    OCD.PC_AdjustmentAmount,
                    OCD.PayingControlStatus,
                    F_GetLoginUserName( OCD.RegistId ) AS RegistName,
                    OCD.RegistDate,
                    F_GetLoginUserName( OCD.UpdateId ) AS UpdateName,
                    OCD.UpdateDate,
                    OCD.ExecDate
                FROM
                    T_OemClaimed OCD INNER JOIN T_Oem OEM ON (OCD.OemId = OEM.OemId)
                WHERE
                    OEM.OemId = :OemId
                AND
                    SpanFrom >= :SpanFrom
                AND
                    SpanTo <= :SpanTo
EOQ;

//         $from_where = BaseGeneralUtils::makeWhereDateTime('SpanFrom',
//                             BaseGeneralUtils::convertWideToNarrow($from), null);
        $from_where = BaseGeneralUtils::convertWideToNarrow($from);

        if ($from_where != '')
        {
            $wheres[] = $from_where;
        }

//         $to_where = BaseGeneralUtils::makeWhereDateTime('SpanTo',
//                             null, BaseGeneralUtils::convertWideToNarrow($to));
        $to_where = BaseGeneralUtils::convertWideToNarrow($to);
        $to_where = $to_where . ' 23:59:59';

        if ($to_where != '')
        {
            $wheres[] =  $to_where;
        }

//        $query = sprintf($query, join(' AND ', $wheres));
        $stmt = $this->app->dbAdapter->query($query);
        $wheres = array(
            ':OemId' => $oemId,
            ':SpanFrom' => $from_where,
            ':SpanTo' => $to_where
        );

        $oemClaimedList = ResultInterfaceToArray( $stmt->execute( $wheres ) );

        return $oemClaimedList;
    }

    /**
     * (Ajax)支払完了処理
     */
    public function updateexecdateAction()
    {
        try {
            $params = $this->getParams();
            $ocs = isset($params['OemClaimedSeq']) ? $params['OemClaimedSeq'] : 0;
            $mdl = new TableOemClaimed($this->app->dbAdapter);
            $mdl->saveUpdate(array('ExecDate' => date('Y-m-d')), $ocs);
            $msg = '1';
        }
        catch(\Exception $e) {
            $msg = $e->getMessage();
        }

        echo \Zend\Json\Json::encode(array('status' => $msg));
        return $this->response;
    }
}

