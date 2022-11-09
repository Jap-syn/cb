<?php
namespace cbadmin\Controller;

use cbadmin\Application;
use Coral\Coral\Controller\CoralControllerAction;
use Coral\Base\BaseHtmlUtils;
use models\Table\TableOrder;
use models\Table\TableOem;
use models\Table\TableEnterprise;
use models\Table\TableOemClaimed;
use models\Table\TableOemEnterpriseClaimed;
use models\Table\TableOemAdjustmentAmount;
use models\Table\TablePayingControl;
use models\Table\TableCode;
use models\Table\TableUser;
use models\Table\TableSite;
use models\Logic\LogicTemplate;
use models\Logic\LogicOemTradingSettlement;
use models\Table\TableSystemProperty;

class OemMonthlyController extends CoralControllerAction
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
        $this->addStyleSheet('../css/default02.css')
            ->addJavaScript('../js/prototype.js');

        $this->setPageTitle("後払い.com - OEM月次明細");
    }

    /**
     * OEM精算書
     */
    public function settlementAction()
    {
        $params = $this->getParams();

        //対象期間取得
        $fixedDate = !isset( $params['fd'] ) ? -1 : $params['fd'];

        //OemId取得
        $oemId = !isset( $params['oemid'] ) ? -1 : $params['oemid'];

        //セレクトボックス作成
        $date_list = $this->_createMonthList($oemId);
        $fixedMonthTag = BaseHtmlUtils::SelectTag(
            'fd',
            $date_list,
            $fixedDate
        );
        $this->view->assign('fixedMonthTag', $fixedMonthTag);

        //該当のOEM情報取得
        $mdloem = new TableOem($this->app->dbAdapter);
        $oemData = $mdloem->findOem2($oemId)->current();

        if(!empty($oemData)){

            $oemInfo = array("OemId" => $oemId,
                "OemNameKj" => $oemData['OemNameKj'],
                "PostalCode" => $oemData['PostalCode'],
                "PrefectureName" => $oemData['PrefectureName'],
                "City" => $oemData['City'],
                "Town" => $oemData['Town'],
                "Building" => $oemData['Building']);
        }else{
            $oemInfo = array("OemId" => "",
                "OemNameKj" => "",
                "PostalCode" => "",
                "PrefectureName" => "",
                "City" => "",
                "Town" => "",
                "Building" => "");

        }

        $mdloc = new TableOemClaimed($this->app->dbAdapter);

        $date_list = array();


        //fdがなければ取得した最初のデータのみ
        if($fixedDate == -1){
            $data_list = ResultInterfaceToArray( $mdloc->findOemClaimed($oemId) );

        }else{
            //fdがあればそれを用いてデータ取得

            //$fdをFromとToに分解
            $search_range = explode("_", $fixedDate);

            $data_list = ResultInterfaceToArray( $mdloc->findOemClaimed($oemId,isset($search_range[0])?$search_range[0]:null,
                                                                        isset($search_range[1])?$search_range[1]:null) );

        }

        $sql  = ' SELECT OA.SerialNumber ';
        $sql .= ' ,      OA.OrderId ';
        $sql .= ' ,      OA.ItemCode ';
        $sql .= ' ,      OA.AdjustmentAmount ';
        $sql .= ' ,      OA.RegistDate ';
        $sql .= ' ,      F_GetLoginUserName( OA.RegistId ) AS RegistName ';
        $sql .= ' ,      C.NameKj ';
        $sql .= ' FROM T_OemAdjustmentAmount OA LEFT JOIN ';
        $sql .= '      T_Customer C ON C.OrderSeq = OA.OrderSeq ';
        $sql .= ' WHERE OemClaimedSeq = :OemClaimedSeq ';
        $sql .= ' AND OA.ValidFlg = 1 ';
        $stm = $this->app->dbAdapter->query( $sql );

        //データ取得に失敗していた時用に初期化
        if(empty($data_list)){
            $data_list[0] = array(
                "OemClaimedSeq" => "",
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
                "CB_SettlementCount" => "",
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
                "PC_StampFeeTotal" => "",
                "PC_TransferCommission" => "",
            );
        }

        // OEM調整額管理
        if( !empty( $data_list[0] ) ) {
            $prm = array(
                ':OemClaimedSeq' => $data_list[0]['OemClaimedSeq'],
            );
            $oemAdjustmentAmount = ResultInterfaceToArray( $stm->execute( $prm ) );
        }

        // 調整金科目取得
        $mdlc = new TableCode( $this->app->dbAdapter );
        $itemCodeList = ResultInterfaceToArray( $mdlc->getMasterByClass( 89 ) );


        $this->view->assign( 'oemInfo', $oemInfo );
        if( $fixedDate == -1 && !empty( $data_list ) ) {
            $this->view->assign( 'fd', $data_list[0]['SpanFrom'] . '_' . $data_list[0]['SpanTo'] );
        }
        else {
            $this->view->assign( 'fd', $fixedDate );
        }

        //現在日時の消費税率
        $propertyTable = new TableSystemProperty($this->app->dbAdapter);
        $taxRate = $propertyTable->getTaxRateAt($data_list[0]['SettlePlanDate']);

        // 消費税算出　（短命につきハードコーディング）
        $data_list[0]['TotalProfitTax'] = floor(($data_list[0]['OM_TotalProfit'] > 0 ? $data_list[0]['OM_TotalProfit'] : 0) * $taxRate / 100 );
        $data_list[0]['DspTaxFlg'] = ($oemData['DspTaxFlg'] == 1);

        $this->view->assign( 'settlement', $data_list[0] );
        $this->view->assign( 'oemAdjustmentAmount' , $oemAdjustmentAmount );
        $this->view->assign( 'itemCodeList', $itemCodeList );
        $this->view->assign( 'current_page', 'settlement' );

        return $this->view;
    }

    /**
     * 調整額更新
     */
    public function updateAction()
    {
        $params = $this->getParams();

        if( !isset( $params ) ) {
            $this->view->assign( 'OemId', -1 );
            $this->view->assign( 'fd', -1 );
            $this->view->assign( 'errors', array() );

            return $this->view;
        }

        $delSerialNumbers = array();
        $orderIds = array();
        $itemCodes = array();
        $adjustmentAmounts = array();

        foreach( $params as $key => $param ) {
            // $key から SerialNumber 取得
            if( strstr( $key, 'Delete_' ) != false ) {
                $delSerialNumbers[] = str_replace( 'Delete_', '', $key );
            }
            else if( strstr( $key, 'OrderId_' ) != false ) {
                $orderIds[str_replace( 'OrderId_', '', $key )] = $param;
            }
            else if( strstr( $key, 'ItemCode_' ) != false ) {
                $itemCodes[str_replace( 'ItemCode_', '', $key )] = $param;
            }
            else if( strstr( $key, 'AdjustmentAmount_' ) != false ) {
                $adjustmentAmounts[str_replace( 'AdjustmentAmount_', '', $key )] = $param;
            }
            else if( $key == 'OemId' ) {
                $oemId = $param;
            }
            else if( $key == 'OemClaimedSeq' ) {
                $oemClaimedSeq = $param;
            }
            else if( $key == 'fd' ) {
                $fd = $param;
            }
        }

        // ユーザーIDの取得
        $obj = new TableUser( $this->app->dbAdapter );
        $userId = $obj->getUserId( 0, $this->app->authManagerAdmin->getUserInfo()->OpId );

        // OEM調整額管理テーブル
        $mdloa = new TableOemAdjustmentAmount( $this->app->dbAdapter );
        // 注文テーブル
        $mdlo = new TableOrder( $this->app->dbAdapter );

        $errors = array();
        // 調整額の削除
        foreach( $delSerialNumbers as $serialNumber ) {
            // 新規行は無視
            if( strstr( $serialNumber, 'new') != false ) {
                continue;
            }
            else {
                try {
                    $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();

                    // OEM調整額管理の論理削除
                    $data = array( 'ValidFlg' => 0, 'UpdateId' => $userId );
                    $mdloa->saveUpdate( $data, $oemClaimedSeq, $serialNumber );

                    $this->app->dbAdapter->getDriver()->getConnection()->commit();

                } catch( \Exception $e ) {
                    $this->app->dbAdapter->getDriver()->getConnection()->rollBack();
                    throw $e;
                }
            }
        }

        // 調整額の更新
        foreach( $adjustmentAmounts as $key => $adjustmentAmount ) {
            if( !empty( $adjustmentAmount ) ) {
                // 更新実行フラグ
                $update = true;
                foreach( $delSerialNumbers as $serialNumber ) {
                    // $delSerialNumbers に 含まれていなければ更新対象
                    if( $key == $serialNumber ) {
                        $update = false;
                        break;
                    }
                }
                if( $update == true ) {
                    $serialNumber = $key;
                    $data = array();

                    $orderId = $orderIds[$serialNumber];
                    if( !empty( $orderId ) ) {
                        $conditionArray = array( 'OemId' => $oemId, 'OrderId' => $orderId );
                        $order = $mdlo->findOrder( $conditionArray )->current();
                        // 入力されていたOrderIdが該当OEMの注文ではなかったら次へ
                        if( $order == false ) {
                            $errors[] = "指定した注文ID $orderId が該当のOEMの注文ではないので登録できませんでした。";
                            continue;
                        }
                        else {
                            $data = array(
                                'OrderId' => $order['OrderId'],
                                'OrderSeq' => $order['OrderSeq'],
                            );
                        }
                    }
                    try {
                        $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
                        // 新規行は新規追加
                        if( strstr( $serialNumber, 'new' ) != false ) {
                            // SerialNumberのMAX+1を作成
                            $sql  = ' SELECT SerialNumber ';
                            $sql .= ' FROM T_OemAdjustmentAmount ';
                            $sql .= ' WHERE OemClaimedSeq = :OemClaimedSeq ';
                            $stm = $this->app->dbAdapter->query( $sql );
                            $prm = array( ':OemClaimedSeq' => $oemClaimedSeq );
                            $numbers = array();
                            $values = ResultInterfaceToArray( $stm->execute( $prm ) );
                            foreach( $values as $value ) {
                                $numbers[] = intval( $value['SerialNumber'] );
                            }
                            // 該当[OEM請求データシーケンス]があれば[連番]+1を、ない場合は1を指定する
                            // count関数対策
                            $maxnumber = (!empty($numbers)) ? (max( $numbers ) + 1) : 1;

                            $data += array(
                                'OemClaimedSeq' => $oemClaimedSeq,
                                'SerialNumber' => $maxnumber,
                                'ItemCode' => $itemCodes[$serialNumber],
                                'AdjustmentAmount' => $adjustmentAmount,
                                'RegistId' => $userId,
                                'UpdateId' => $userId
                            );
                            $mdloa->saveNew( $data );
                        }
                        // 更新行は更新
                        else {
                            $data += array(
                                'ItemCode' => $itemCodes[$serialNumber],
                                'AdjustmentAmount' => $adjustmentAmount,
                                'UpdateId' => $userId
                            );
                            $mdloa->saveUpdate( $data, $oemClaimedSeq, $serialNumber );
                        }
                        $this->app->dbAdapter->getDriver()->getConnection()->commit();
                    } catch( \Exception $e ) {
                        $this->app->dbAdapter->getDriver()->getConnection()->rollBack();
                        throw $e;
                    }
                }
            }
        }

        $adjustmentAmount = 0;
        // OEM請求のサマリー
        $sql  = ' SELECT AdjustmentAmount ';
        $sql .= ' FROM T_OemAdjustmentAmount ';
        $sql .= ' WHERE OemClaimedSeq = :OemClaimedSeq ';
        $sql .= ' AND ValidFlg = 1 ';
        $stm = $this->app->dbAdapter->query( $sql );
        $prm = array( ':OemClaimedSeq' => $oemClaimedSeq );
        $adjustmentAmountList = ResultInterfaceToArray( $stm->execute( $prm ) );
        foreach( $adjustmentAmountList as $value ) {
            $adjustmentAmount += intval( $value['AdjustmentAmount'] );
        }

        try {
            $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();

            $mdloc = new TableOemClaimed( $this->app->dbAdapter );

            $mdloc->updateAdjustmentAmount($oemClaimedSeq, $adjustmentAmount, $userId);

            $this->app->dbAdapter->getDriver()->getConnection()->commit();

        } catch( \Exception $e ) {
            $this->app->dbAdapter->getDriver()->getConnection()->rollBack();
            throw $e;
        }

        $this->view->assign( 'OemId', $oemId );
        $this->view->assign( 'fd', $fd );
        $this->view->assign( 'errors', $errors );

        return $this->view;
    }

    /**
     * OEM精算明細一覧
     */
    public function settlementlistAction()
    {
        $params = $this->getParams();

        // 対象期間取得
        $fixedDate = !isset( $params['fd'] ) ? -1 : $params['fd'];

        // OemId取得
        $oemId = !isset( $params['oemid'] ) ? -1 : $params['oemid'];

        // セレクトボックス作成
        $date_list = $this->_createMonthList( $oemId );
        $fixedMonthTag = BaseHtmlUtils::SelectTag(
            'fd',
            $date_list,
            $fixedDate
        );

        $mdloc = new TableOemClaimed( $this->app->dbAdapter );
        $mdloec = new TableOemEnterpriseClaimed( $this->app->dbAdapter );
        $enterprise_claimed_data = array();

        // fdがなければ取得した最初のデータのみ
        if( $fixedDate == -1 ) {
            // OEM請求データ取得
            $claimed_data = ResultInterfaceToArray( $mdloc->findOemClaimed( $oemId ) );
        }
        else {
            // $fdをFromとToに分解
            $search_range = explode( "_", $fixedDate );

            // OEM請求データ取得
            $claimed_data = ResultInterfaceToArray( $mdloc->findOemClaimed($oemId,
                                                                           isset( $search_range[0] ) ? $search_range[0] : null,
                                                                           isset( $search_range[1] ) ? $search_range[1] : null ) );
        }

        // OEM請求データが取れていれば
        if( !empty( $claimed_data ) ) {
            // 加盟店ごとデータ取得
            $enterprise_claimed_data = ResultInterfaceToArray( $mdloec->findOemEnterpriseClaimed( $oemId,
                                                                                                  $claimed_data[0]['SpanFrom'],
                                                                                                  $claimed_data[0]['SpanTo'] ) );
        }

        $this->view->assign( 'fixedMonthTag', $fixedMonthTag );
        $oemInfo = array( "OemId" => $oemId );
        $this->view->assign( 'oemInfo', $oemInfo );
        if( $fixedDate == -1 && !empty( $claimed_data ) ) {
            $this->view->assign( 'fd', $claimed_data[0]['SpanFrom'] . '_' . $claimed_data[0]['SpanTo'] );
        }
        else {
            $this->view->assign( 'fd', $fixedDate );
        }
        $this->view->assign( 'enterprise_settlement', $enterprise_claimed_data );
        $this->view->assign( 'current_page', 'settlementlist' );

        return $this->view;
    }

    /**
     * OEM請求書兼納品書
     */
    public function summaryAction()
    {
        $params = $this->getParams();

        // 対象期間取得
        $fixedDate = !isset( $params['fd'] ) ? -1 : $params['fd'];

        // OemId取得
        $oemId = !isset( $params['oemid'] ) ? -1 : $params['oemid'];

        // EnterpriseId取得
        $eId = !isset( $params['eid'] ) ? -1 : $params['eid'];

        // 該当のOEM情報取得
        $mdloem = new TableOem( $this->app->dbAdapter );
        $oemData = $mdloem->findOem2( $oemId )->current();

        // 該当のEnerprise情報取得
        $mdlent = new TableEnterprise( $this->app->dbAdapter );
        $entData = $mdlent->findEnterprise2( $eId )->current();

        $search_range = array();
        if( $fixedDate != -1 ) {
            // $fdをFromとToに分解
            $search_range = explode( "_", $fixedDate );
        }

        // 加盟店ごとデータ取得
        $enterprise_claimed_data = array();
        $mdloec = new TableOemEnterpriseClaimed( $this->app->dbAdapter );
        $enterprise_claimed_data = ResultInterfaceToArray( $mdloec->findOemEnterpriseClaimed( $oemId, $search_range[0], $search_range[1], $eId ) );

        $this->view->assign( 'oemInfo', $oemData );
        $this->view->assign( 'entInfo', $entData );
        $this->view->assign( 'fd', $fixedDate );
        $this->view->assign( 'list', $enterprise_claimed_data );
        $dataInformation = '';
        if( !empty( $enterprise_claimed_data ) ) {
            $dataInformation = $oemData['OemNameKj'] . date( 'Y年m月次', strtotime( $enterprise_claimed_data[0]['FixedMonth'] ) ) . '(' . $entData['LoginId'] . '　' . $entData['EnterpriseNameKj'] . ')';
        }
        $this->view->assign( 'dataInformation', $dataInformation );
        $this->view->assign( 'current_page', 'summary' );

        return $this->view;
    }

    /**
     * OEMお取引別精算明細
     */
    public function chargelistAction()
    {
        $params = $this->getParams();

        // 対象期間取得
        $fixedDate = !isset( $params['fd'] ) ? -1 : $params['fd'];

        // OemId取得
        $oemId = !isset( $params['oemid'] ) ? -1 : $params['oemid'];

        // EnterpriseId取得
        $eId = !isset( $params['eid'] ) ? -1 : $params['eid'];

        // 該当のOEM情報取得
        $mdloem = new TableOem( $this->app->dbAdapter );
        $oemData = $mdloem->findOem2( $oemId )->current();

        // 該当のEnerprise情報取得
        $mdlent = new TableEnterprise( $this->app->dbAdapter );
        $entData = $mdlent->findEnterprise2( $eId )->current();

        $search_range = array();
        if( $fixedDate != -1 ) {
            // $fdをFromとToに分解
            $search_range = explode( "_", $fixedDate );
        }

        // 加盟店ごとデータ取得
        $enterprise_claimed_data = array();
        $mdloec = new TableOemEnterpriseClaimed( $this->app->dbAdapter );
        $enterprise_claimed_data = ResultInterfaceToArray( $mdloec->findOemEnterpriseClaimed( $oemId, $search_range[0], $search_range[1], $eId ) );

        $list = array();
        // データ取得
        $sql = <<<EOQ
SELECT O.OrderSeq
,      O.OrderId
,      O.Ent_OrderId
,      C.NameKj
,      O.ReceiptOrderDate
,      ( SELECT MAX( Deli_JournalIncDate ) FROM T_OrderItems WHERE OrderSeq = O.OrderSeq ) AS MaxDeliJournalIncDate
,      PC.FixedDate
,      PAS.UseAmount
,      ( -1 * PAS.SettlementFee ) AS SettlementFee
,      ( -1 * PAS.ClaimFee ) AS ClaimFee
,      ( -1 * ( PAS.SettlementFee + PAS.ClaimFee - ( OSF.SettlementFee + OCF.ClaimFee ) ) ) AS OemFee
,      ( -1 * IFNULL( SF.StampFee, 0 ) ) AS StampFee
,      ( PAS.UseAmount + ( -1 * PAS.SettlementFee ) +  ( -1 * PAS.ClaimFee ) + ( -1 * IFNULL( SF.StampFee, 0 ) ) ) AS sagaku
,      S.SiteId
,      S.SiteNameKj
FROM   T_PayingControl PC INNER JOIN
       T_OemEnterpriseClaimed OEC ON ( PC.OemClaimedSeq = OEC.OemClaimedSeq AND PC.OemId = OEC.OemId AND PC.EnterpriseId = OEC.EnterpriseId ) INNER JOIN
       T_PayingAndSales PAS ON ( PC.Seq = PAS.PayingControlSeq ) INNER JOIN
       T_Order O ON ( PAS.OrderSeq = O.OrderSeq ) LEFT OUTER JOIN
       T_StampFee SF ON ( O.OrderSeq = SF.OrderSeq ) INNER JOIN
       T_Customer C ON ( O.OrderSeq = C.OrderSeq ) INNER JOIN
       T_Enterprise E ON ( PC.EnterpriseId = E.EnterpriseId AND PC.OemId = E.OemId ) INNER JOIN
       T_Site S ON ( O.SiteId = S.SiteId ) INNER JOIN
       T_OemSettlementFee OSF ON ( O.OrderSeq = OSF.OrderSeq ) LEFT OUTER JOIN
       T_OemClaimFee OCF ON ( O.OrderSeq = OCF.OrderSeq ) INNER JOIN
       T_Oem OEM ON ( O.OemId = OEM.OemId )
WHERE  OEC.OemId = :OemId
AND    OEC.EnterpriseId = :EnterpriseId
AND    OEC.SpanFrom = :SpanFrom
AND    OEC.SpanTo = :SpanTo
ORDER BY S.SiteId, O.OrderId
EOQ;
        $prm = array(
            ':OemId' => $oemId,
            ':EnterpriseId' => $eId,
            ':SpanFrom' => $search_range[0],
            ':SpanTo' => $search_range[1]
        );
        $stm = $this->app->dbAdapter->query( $sql );
        $ri = $stm->execute( $prm );
        $list = $this->grouping( ResultInterfaceToArray( $ri ), 'SiteNameKj' );

        // OEM精算仮締め対象外有無確認
        $sql = "SELECT Class1 FROM M_Code WHERE CodeId = 221 AND KeyCode = :OemId";
        $class1 = $this->app->dbAdapter->query($sql)->execute(array(':OemId'=>$oemId))->current()["Class1"];

        if($class1 == 0) {
            foreach ($list["届いてから"] as $k => $v) {
                $sql = "SELECT ExtraPayType FROM AT_Order WHERE OrderSeq = :OrderSeq";
                $extraPayType = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq'=>$v['OrderSeq']))->current()['ExtraPayType'];
                if ((!is_null($extraPayType)) && ($extraPayType == 1)) {
                    unset($list["届いてから"][$k]);
                }
            }
        }

        $this->view->assign( 'oemInfo', $oemData );
        $this->view->assign( 'entInfo', $entData );
        $this->view->assign( 'fd', $fixedDate );
        $this->view->assign( 'list', $list );
        $dataInformation = '';
        if( !empty( $enterprise_claimed_data ) ) {
            $dataInformation = $oemData['OemNameKj'] . date( 'Y年m月次', strtotime( $enterprise_claimed_data[0]['FixedMonth'] ) ) . '(' . $entData['LoginId'] . '　' . $entData['EnterpriseNameKj'] . ')';
        }
        $this->view->assign( 'dataInformation', $dataInformation );
        $this->view->assign( 'current_page', 'chargelist' );

        return $this->view;
    }

    /**
     * OEM印紙代明細
     */
    public function stamplistAction()
    {
        $params = $this->getParams();

        // 対象期間取得
        $fixedDate = !isset( $params['fd'] ) ? -1 : $params['fd'];

        // OemId取得
        $oemId = !isset( $params['oemid'] ) ? -1 : $params['oemid'];

        // EnterpriseId取得
        $eId = !isset( $params['eid'] ) ? -1 : $params['eid'];

        // 該当のOEM情報取得
        $mdloem = new TableOem( $this->app->dbAdapter );
        $oemData = $mdloem->findOem2( $oemId )->current();

        // 該当のEnerprise情報取得
        $mdlent = new TableEnterprise( $this->app->dbAdapter );
        $entData = $mdlent->findEnterprise2( $eId )->current();

        $search_range = array();
        if( $fixedDate != -1 ) {
            // $fdをFromとToに分解
            $search_range = explode( "_", $fixedDate );
        }

        // 加盟店ごとデータ取得
        $enterprise_claimed_data = array();
        $mdloec = new TableOemEnterpriseClaimed( $this->app->dbAdapter );
        $enterprise_claimed_data = ResultInterfaceToArray( $mdloec->findOemEnterpriseClaimed( $oemId, $search_range[0], $search_range[1], $eId ) );

        $list = array();
        // データ取得
        $sql = <<<EOQ
SELECT O.OrderSeq
,      O.OrderId
,      O.Ent_OrderId
,      C.NameKj
,      O.ReceiptOrderDate
,      ( SELECT MAX( Deli_JournalIncDate ) FROM T_OrderItems WHERE OrderSeq = O.OrderSeq ) AS MaxDeliJournalIncDate
,      PC.FixedDate
,      O.UseAmount
,      SF.StampFee
,      S.SiteId
,      S.SiteNameKj
FROM   T_PayingControl PC INNER JOIN
       T_OemEnterpriseClaimed OEC ON ( PC.OemClaimedSeq = OEC.OemClaimedSeq AND PC.OemId = OEC.OemId AND PC.EnterpriseId = OEC.EnterpriseId ) INNER JOIN
       T_StampFee SF ON ( PC.Seq = SF.PayingControlSeq ) INNER JOIN
       T_Order O ON ( SF.OrderSeq = O.OrderSeq ) INNER JOIN
       T_Customer C ON ( O.OrderSeq = C.OrderSeq ) INNER JOIN
       T_Site S ON ( O.SiteId = S.SiteId )
WHERE  OEC.OemId = :OemId
AND    OEC.EnterpriseId = :EnterpriseId
AND    OEC.SpanFrom = :SpanFrom
AND    OEC.SpanTo = :SpanTo
ORDER BY S.SiteId, O.OrderId
EOQ;
        $prm = array(
            ':OemId' => $oemId,
            ':EnterpriseId' => $eId,
            ':SpanFrom' => $search_range[0],
            ':SpanTo' => $search_range[1]
        );
        $stm = $this->app->dbAdapter->query( $sql );
        $ri = $stm->execute( $prm );
        $list = $this->grouping( ResultInterfaceToArray( $ri ), 'SiteNameKj' );

        $this->view->assign( 'oemInfo', $oemData );
        $this->view->assign( 'entInfo', $entData );
        $this->view->assign( 'fd', $fixedDate );
        $this->view->assign( 'list', $list );
        $dataInformation = '';
        if( !empty( $enterprise_claimed_data ) ) {
            $dataInformation = $oemData['OemNameKj'] . date( 'Y年m月次', strtotime( $enterprise_claimed_data[0]['FixedMonth'] ) ) . '(' . $entData['LoginId'] . '　' . $entData['EnterpriseNameKj'] . ')';
        }
        $this->view->assign( 'dataInformation', $dataInformation );
        $this->view->assign( 'current_page', 'stamplist' );

        return $this->view;
    }

    /**
     * OEMキャンセル返金明細
     */
    public function cancellistAction()
    {
        $params = $this->getParams();

        // 対象期間取得
        $fixedDate = !isset( $params['fd'] ) ? -1 : $params['fd'];

        // OemId取得
        $oemId = !isset( $params['oemid'] ) ? -1 : $params['oemid'];

        // EnterpriseId取得
        $eId = !isset( $params['eid'] ) ? -1 : $params['eid'];

        // 該当のOEM情報取得
        $mdloem = new TableOem( $this->app->dbAdapter );
        $oemData = $mdloem->findOem2( $oemId )->current();

        // 該当のEnerprise情報取得
        $mdlent = new TableEnterprise( $this->app->dbAdapter );
        $entData = $mdlent->findEnterprise2( $eId )->current();

        $search_range = array();
        if( $fixedDate != -1 ) {
            // $fdをFromとToに分解
            $search_range = explode( "_", $fixedDate );
        }

        // 加盟店ごとデータ取得
        $enterprise_claimed_data = array();
        $mdloec = new TableOemEnterpriseClaimed( $this->app->dbAdapter );
        $enterprise_claimed_data = ResultInterfaceToArray( $mdloec->findOemEnterpriseClaimed( $oemId, $search_range[0], $search_range[1], $eId ) );

        $list = array();
        // データ取得
        $sql = <<<EOQ
SELECT O.OrderSeq
,      O.OrderId
,      O.Ent_OrderId
,      C.NameKj
,      O.ReceiptOrderDate
,      PC.FixedDate
,      CNL.CancelDate
,      O.UseAmount
,      CNL.RepayTotal
,      S.SiteId
,      S.SiteNameKj
FROM   T_PayingControl PC INNER JOIN
       T_OemEnterpriseClaimed OEC ON ( PC.OemClaimedSeq = OEC.OemClaimedSeq AND PC.OemId = OEC.OemId AND PC.EnterpriseId = OEC.EnterpriseId ) INNER JOIN
       T_Cancel CNL ON ( PC.Seq = CNL.PayingControlSeq ) INNER JOIN
       T_Order O ON ( CNL.OrderSeq = O.OrderSeq ) INNER JOIN
       T_Customer C ON ( O.OrderSeq = C.OrderSeq ) INNER JOIN
       T_Site S ON ( O.SiteId = S.SiteId )
WHERE  OEC.OemId = :OemId
AND    OEC.EnterpriseId = :EnterpriseId
AND    OEC.SpanFrom = :SpanFrom
AND    OEC.SpanTo = :SpanTo
AND    CNL.ValidFlg = 1
ORDER BY S.SiteId, O.OrderId
EOQ;
        $prm = array(
            ':OemId' => $oemId,
            ':EnterpriseId' => $eId,
            ':SpanFrom' => $search_range[0],
            ':SpanTo' => $search_range[1]
        );
        $stm = $this->app->dbAdapter->query( $sql );
        $ri = $stm->execute( $prm );
        $list = $this->grouping( ResultInterfaceToArray( $ri ), 'SiteNameKj' );

        // OEM精算仮締め対象外有無確認
        $sql = "SELECT Class1 FROM M_Code WHERE CodeId = 221 AND KeyCode = :OemId";
        $class1 = $this->app->dbAdapter->query($sql)->execute(array(':OemId'=>$oemId))->current()["Class1"];

        if($class1 == 0) {
            foreach ($list["届いてから"] as $k => $v) {
                $sql = "SELECT ExtraPayType FROM AT_Order WHERE OrderSeq = :OrderSeq";
                $extraPayType = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq'=>$v['OrderSeq']))->current()['ExtraPayType'];
                if ((!is_null($extraPayType)) && ($extraPayType == 1)) {
                    unset($list["届いてから"][$k]);
                }
            }
        }

        $this->view->assign( 'oemInfo', $oemData );
        $this->view->assign( 'entInfo', $entData );
        $this->view->assign( 'fd', $fixedDate );
        $this->view->assign( 'list', $list );
        $dataInformation = '';
        if( !empty( $enterprise_claimed_data ) ) {
            $dataInformation = $oemData['OemNameKj'] . date( 'Y年m月次', strtotime( $enterprise_claimed_data[0]['FixedMonth'] ) ) . '(' . $entData['LoginId'] . '　' . $entData['EnterpriseNameKj'] . ')';
        }
        $this->view->assign( 'dataInformation', $dataInformation );
        $this->view->assign( 'current_page', 'cancellist' );

        return $this->view;
    }

    /**
     * OEM調整金明細
     */
    public function adjustmentlistAction()
    {
        $params = $this->getParams();

        // 対象期間取得
        $fixedDate = !isset( $params['fd'] ) ? -1 : $params['fd'];

        // OemId取得
        $oemId = !isset( $params['oemid'] ) ? -1 : $params['oemid'];

        // EnterpriseId取得
        $eId = !isset( $params['eid'] ) ? -1 : $params['eid'];

        // 該当のOEM情報取得
        $mdloem = new TableOem( $this->app->dbAdapter );
        $oemData = $mdloem->findOem2( $oemId )->current();

        // 該当のEnerprise情報取得
        $mdlent = new TableEnterprise( $this->app->dbAdapter );
        $entData = $mdlent->findEnterprise2( $eId )->current();

        $search_range = array();
        if( $fixedDate != -1 ) {
            $search_range = explode( "_", $fixedDate );
        }

        // 加盟店ごとデータ取得
        $enterprise_claimed_data = array();
        $mdloec = new TableOemEnterpriseClaimed( $this->app->dbAdapter );
        $enterprise_claimed_data = ResultInterfaceToArray( $mdloec->findOemEnterpriseClaimed( $oemId, $search_range[0], $search_range[1], $eId ) );

        $list = array();
        // データ取得
        $sql = <<<EOQ
SELECT O.OrderSeq
,      PC.FixedDate
,      AA.OrderId
,      O.Ent_OrderId
,      C.NameKj
,      O.ReceiptOrderDate
,      ( SELECT KeyContent FROM M_Code WHERE CodeId = 89 AND KeyCode = AA.ItemCode ) AS kamoku
,      AA.AdjustmentAmount
FROM   T_PayingControl PC INNER JOIN
       T_OemEnterpriseClaimed OEC ON ( PC.OemClaimedSeq = OEC.OemClaimedSeq AND PC.OemId = OEC.OemId AND PC.EnterpriseId = OEC.EnterpriseId ) INNER JOIN
       T_AdjustmentAmount AA ON ( PC.Seq = AA.PayingControlSeq ) LEFT OUTER JOIN
       T_Order O ON ( AA.OrderSeq = O.OrderSeq ) LEFT OUTER JOIN
       T_Customer C ON ( AA.OrderSeq = C.OrderSeq )
WHERE  OEC.OemId = :OemId
AND    OEC.EnterpriseId = :EnterpriseId
AND    OEC.SpanFrom = :SpanFrom
AND    OEC.SpanTo = :SpanTo
ORDER BY AA.SerialNumber
EOQ;
        $prm = array(
            ':OemId' => $oemId,
            ':EnterpriseId' => $eId,
            ':SpanFrom' => $search_range[0],
            ':SpanTo' => $search_range[1]
        );
        $stm = $this->app->dbAdapter->query( $sql );
        $list = ResultInterfaceToArray( $stm->execute( $prm ) );

        $this->view->assign( 'oemInfo', $oemData );
        $this->view->assign( 'entInfo', $entData );
        $this->view->assign( 'fd', $fixedDate );
        $this->view->assign( 'list', $list );
        $dataInformation = '';
        if( !empty( $enterprise_claimed_data ) ) {
            $dataInformation = $oemData['OemNameKj'] . date( 'Y年m月次', strtotime( $enterprise_claimed_data[0]['FixedMonth'] ) ) . '(' . $entData['LoginId'] . '　' . $entData['EnterpriseNameKj'] . ')';
        }
        $this->view->assign( 'dataInformation', $dataInformation );
        $this->view->assign( 'current_page', 'adjustmentlist' );

        return $this->view;
    }

    /**
     * OEM立替精算戻し
     */
    public function paybacklistAction()
    {
        $params = $this->getParams();

        // 対象期間取得
        $fixedDate = !isset( $params['fd'] ) ? -1 : $params['fd'];

        // OemId取得
        $oemId = !isset( $params['oemid'] ) ? -1 : $params['oemid'];

        // EnterpriseId取得
        $eId = !isset( $params['eid'] ) ? -1 : $params['eid'];

        // 該当のOEM情報取得
        $mdloem = new TableOem( $this->app->dbAdapter );
        $oemData = $mdloem->findOem2( $oemId )->current();

        // 該当のEnerprise情報取得
        $mdlent = new TableEnterprise( $this->app->dbAdapter );
        $entData = $mdlent->findEnterprise2( $eId )->current();

        $search_range = array();
        if( $fixedDate != -1 ) {
            $search_range = explode( "_", $fixedDate );
        }

        // 加盟店ごとデータ取得
        $enterprise_claimed_data = array();
        $mdloec = new TableOemEnterpriseClaimed( $this->app->dbAdapter );
        $enterprise_claimed_data = ResultInterfaceToArray( $mdloec->findOemEnterpriseClaimed( $oemId, $search_range[0], $search_range[1], $eId ) );

        $list = array();
        // データ取得
        $sql = <<<EOQ
SELECT O.OrderSeq
,      O.OrderId
,      O.Ent_OrderId
,      C.NameKj
,      O.ReceiptOrderDate
,      ( SELECT MAX( Deli_JournalIncDate ) FROM T_OrderItems WHERE OrderSeq = O.OrderSeq ) AS MaxDeliJournalIncDate
,      PC.FixedDate
,      PAS.UseAmount
,      ( -1 * ( PAS.SettlementFee ) ) AS SettlementFee
,      ( -1 * ( PAS.ClaimFee ) ) AS ClaimFee
,      ( -1 * ( PAS.SettlementFee + PAS.ClaimFee - ( OSF.SettlementFee + OCF.ClaimFee ) ) ) AS OemFee
,      ( -1 * IFNULL( SF.StampFee, 0 ) ) AS StampFee
,      PBC.PayBackAmount
,      ( PAS.UseAmount + ( -1 * PAS.SettlementFee ) + ( -1 * PAS.ClaimFee ) + ( -1 * IFNULL( SF.StampFee, 0 ) ) ) AS sagaku
,      S.SiteId
,      S.SiteNameKj
FROM   T_PayingControl PC
       INNER JOIN T_OemEnterpriseClaimed OEC ON ( PC.OemClaimedSeq = OEC.OemClaimedSeq AND PC.OemId = OEC.OemId AND PC.EnterpriseId = OEC.EnterpriseId )
       INNER JOIN T_PayingBackControl PBC ON ( PC.Seq = PBC.PayingControlSeq )
       INNER JOIN T_PayingAndSales PAS ON ( PAS.OrderSeq = PBC.OrderSeq )
       INNER JOIN T_Order O ON ( PBC.OrderSeq = O.OrderSeq  )
       LEFT OUTER JOIN T_StampFee SF ON ( SF.OrderSeq = O.OrderSeq  )
       INNER JOIN T_Customer C ON ( O.OrderSeq = C.OrderSeq )
       INNER JOIN T_Site S ON ( O.SiteId = S.SiteId )
       INNER JOIN T_OemSettlementFee OSF ON ( O.OrderSeq = OSF.OrderSeq )
       INNER JOIN T_OemClaimFee OCF ON ( O.OrderSeq = OCF.OrderSeq )
WHERE  OEC.OemId = :OemId
AND    OEC.EnterpriseId = :EnterpriseId
AND    OEC.SpanFrom = :SpanFrom
AND    OEC.SpanTo = :SpanTo
ORDER BY S.SiteId, O.OrderId
EOQ;
        $prm = array(
            ':OemId' => $oemId,
            ':EnterpriseId' => $eId,
            ':SpanFrom' => $search_range[0],
            ':SpanTo' => $search_range[1]
        );
        $stm = $this->app->dbAdapter->query( $sql );
        $ri = $stm->execute( $prm );
        $list = $this->grouping( ResultInterfaceToArray( $ri ), 'SiteNameKj' );

        $this->view->assign( 'oemInfo', $oemData );
        $this->view->assign( 'entInfo', $entData );
        $this->view->assign( 'fd', $fixedDate );
        $this->view->assign('list', $list );
        $dataInformation = '';
        if( !empty( $enterprise_claimed_data ) ) {
            $dataInformation = $oemData['OemNameKj'] . date( 'Y年m月次', strtotime( $enterprise_claimed_data[0]['FixedMonth'] ) ) . '(' . $entData['LoginId'] . '　' . $entData['EnterpriseNameKj'] . ')';
        }
        $this->view->assign( 'dataInformation', $dataInformation );
        $this->view->assign( 'current_page', 'paybacklist' );

        return $this->view;
    }

    /**
     * OEM精算書CSVダウンロード
     */
    public function dseisansyoAction() {
        $params = $this->getParams();

        $oemId = !isset( $params['oemid'] ) ? -1 : $params['oemid'];
        $fixedDate = !isset( $params['fd'] ) ? -1 : $params['fd'];

        // ファイル名
        $fileName = sprintf( "Oem_Seisansyo_%s.csv", date( "YmdHis" ) );

        $search_range = explode( "_", $fixedDate );

        $logicots = new LogicOemTradingSettlement( $this->app->dbAdapter );
        $settlement_data = $logicots->getOemTradingSettlementCsv( $oemId, $search_range[0], $search_range[1] );

        // OEM精算仮締め対象外有無確認
        $sql = "SELECT Class1 FROM M_Code WHERE CodeId = 221 AND KeyCode = :OemId";
        $class1 = $this->app->dbAdapter->query($sql)->execute(array(':OemId'=>$oemId))->current()["Class1"];

        foreach( $settlement_data as $value ) {
            //入金方法を文字に変換
            switch( $value['ReceiptClass'] ) {
                //コンビニ
                case 1:
                    $value['ReceiptClass'] = "コンビニ";
                    break;
                //郵便局
                case 2:
                    $value['ReceiptClass'] = "郵便局";
                    break;
                //銀行
                case 3:
                    $value['ReceiptClass'] = "銀行";
                    break;
                //LINE Pay
                case 4:
                    $value['ReceiptClass'] = "LINE Pay";
                    break;
                default:
                    $value['ReceiptClass'] = "";
                    break;
            }

            // 差引合計（顧客請求金額 - 決済手数料 - 請求書発行手数料）
            $value['ChargeAmount'] = $value['UseAmount'] - $value['SettlementFee'] - $value['ClaimFee'];

            if($class1 == 0) {
                $sql = "SELECT ao.ExtraPayType FROM T_Order o INNER JOIN AT_Order ao ON (ao.OrderSeq = o.OrderSeq) WHERE o.OrderId=:OrderId";
                $extraPayType = $this->app->dbAdapter->query($sql)->execute(array(':OrderId'=>$value['OrderId']))->current()['ExtraPayType'];
                if ((!is_null($extraPayType)) && ($extraPayType == 1)) {
                    continue;
                }
            }
            $datas[] = $value;
        }

        $templateId = 'CKI13097_2'; // OEM精算書CSV
        $templateClass = 1;
        $seq = $oemId;
        $templatePattern = 0;

        $logicTemplate = new LogicTemplate( $this->app->dbAdapter );
        $response = $logicTemplate->convertArraytoResponse( $datas, $fileName, $templateId, $templateClass, $seq, $templatePattern, $this->getResponse() );

        if( $response == false ) {
            throw new \Exception( $logicTemplate->getErrorMessage() );
        }

        return $response;
    }

    /**
     * OEM精算明細一覧CSVダウンロード
     */
    public function dmeisaiichiranAction() {
        $params = $this->getParams();

        $oemId = !isset( $params['oemid'] ) ? -1 : $params['oemid'];
        $fixedDate = !isset( $params['fd'] ) ? -1 : $params['fd'];

        // ファイル名
        $fileName = sprintf( "Oem_Meisai_Ichiran_%s.csv", date( "YmdHis" ) );

        $search_range = explode( "_", $fixedDate );

        // データ取得
        $sql = <<<EOQ
SELECT O.OemId
,      O.OemNameKj
,      E.LoginId
,      E.EnterpriseNameKj
,      OEC.SpanFrom
,      OEC.SpanTo
,      OEC.OrderCount
,      OEC.UseAmount
,      OEC.CB_SettlementFee
,      OEC.OM_SettlementFee
,      OEC.CB_ClaimFeeBS
,      OEC.CB_ClaimFeeDK
,      OEC.OM_ClaimFeeBS
,      OEC.OM_ClaimFeeDK
,      OEC.OM_SettlementFee
,      OEC.AgencyFee
,      OEC.PC_StampFeeTotal
,      OEC.PC_MonthlyFee
,      OEC.OM_EntMonthlyFee
,      OEC.CB_EntMonthlyFee
,      OEC.PC_CarryOver
,      OEC.PC_CalcelAmount
,      OEC.PC_TransferCommission
,      OEC.PC_AdjustmentAmount
,      OEC.PayBackAmount
,      OEC.PC_DecisionPayment
,      OEC.FixedTransferAmount
FROM   T_OemEnterpriseClaimed OEC INNER JOIN
       T_Enterprise E ON ( OEC.EnterpriseId = E.EnterpriseId ) INNER JOIN
       T_Oem O ON ( OEC.OemId = O.OemId )
WHERE  OEC.OemId = :OemId
AND    OEC.SpanFrom = :SpanFrom
AND    OEC.SpanTo = :SpanTo
ORDER BY E.LoginId
EOQ;
        $prm = array(
            ':OemId' => $oemId,
            ':SpanFrom' => $search_range[0],
            ':SpanTo' => $search_range[1]
        );
        $stm = $this->app->dbAdapter->query( $sql );
        $lists = ResultInterfaceToArray( $stm->execute( $prm ) );

        foreach( $lists as $data ) {
            // 決済手数料 = CB利益-決済手数料 + OEM利益-決済手数料
            $data['SettlementFee'] = $data['CB_SettlementFee'] + $data['OM_SettlementFee'];
            // 請求手数料 = CB利益-請求手数料(別送) + OEM利益-請求手数料(別送) + CB利益-請求手数料(同梱) + OEM利益-請求手数料(同梱)
            $data['ClaimFee'] = $data['CB_ClaimFeeBS'] + $data['CB_ClaimFeeDK'] + $data['OM_ClaimFeeBS'] + $data['OM_ClaimFeeDK'];
            // OEM手数料 = OEM利益-決済手数料 + OEM利益-請求手数料(別送) + OEM利益-請求手数料(同梱)
            $data['Oem_ReclaimFee'] = $data['OM_SettlementFee'] + $data['OM_ClaimFeeBS'] + $data['OM_ClaimFeeDK'];
            // 代理店固定費 = 立替-月額固定費 - CB利益-店舗月額 - OEM利益-店舗月額
            $data['AgencyMonthlyFee'] = $data['PC_MonthlyFee'] - $data['CB_EntMonthlyFee'] - $data['OM_EntMonthlyFee'];

            $datas[] = $data;
        }

        $templateId = 'CKI13098_1'; // OEM精算明細一覧CSV
        $templateClass = 1;
        $seq = $oemId;
        $templatePattern = 0;

        $logicTemplate = new LogicTemplate( $this->app->dbAdapter );
        $response = $logicTemplate->convertArraytoResponse( $datas, $fileName, $templateId, $templateClass, $seq, $templatePattern, $this->getResponse() );

        if( $response == false ) {
            throw new \Exception( $logicTemplate->getErrorMessage() );
        }

        return $response;
    }

    /**
     * OEM注文明細一覧CSVダウンロード
     */
    public function dorderichiranAction() {
        $params = $this->getParams();

        $oemId = !isset( $params['oemid'] ) ? -1 : $params['oemid'];
        $fixedDate = !isset( $params['fd'] ) ? -1 : $params['fd'];

        // ファイル名
        $fileName = sprintf( "Oem_Order_Ichiran_%s.csv", date( "YmdHis" ) );

        $search_range = explode( "_", $fixedDate );

        // データ取得
        $sql = <<<EOQ
SELECT OEM.OemNameKj
,      OEC.FixedMonth
,      OEC.SpanFrom
,      OEC.SpanTo
,      E.EnterpriseId
,      E.EnterpriseNameKj
,      S.SiteNameKj
,      O.OrderId
,      O.Ent_OrderId
,      C.NameKj
,      O.ReceiptOrderDate
,      ( SELECT MAX( Deli_JournalIncDate ) FROM T_OrderItems WHERE OrderSeq = O.OrderSeq ) AS Deli_JournalIncDate
,      PC.FixedDate
,      PAS.UseAmount
,      ( -1 * PAS.SettlementFee ) AS SettlementFee
,      ( -1 * PAS.ClaimFee ) AS ClaimFee
,      ( -1 * ( PAS.SettlementFee + PAS.ClaimFee - ( OSF.SettlementFee + OCF.ClaimFee ) ) ) AS OemFee
,      ( -1 * IFNULL( SF.StampFee, 0 ) ) AS StampFee
,      ( PAS.UseAmount + ( -1 * PAS.SettlementFee ) +  ( -1 * PAS.ClaimFee ) + ( -1 * IFNULL(SF.StampFee, 0 ) ) ) AS Chg_ChargeAmount
,      S.SiteId
,      S.SiteNameKj
FROM   T_PayingControl PC INNER JOIN
       T_OemEnterpriseClaimed OEC ON ( PC.OemClaimedSeq = OEC.OemClaimedSeq AND PC.OemId = OEC.OemId AND PC.EnterpriseId = OEC.EnterpriseId ) INNER JOIN
       T_PayingAndSales PAS ON ( PC.Seq = PAS.PayingControlSeq ) INNER JOIN
       T_Order O ON ( PAS.OrderSeq = O.OrderSeq ) LEFT OUTER JOIN
       T_StampFee SF ON ( O.OrderSeq = SF.OrderSeq ) INNER JOIN
       T_Customer C ON ( O.OrderSeq = C.OrderSeq ) INNER JOIN
       T_Enterprise E ON ( PC.EnterpriseId = E.EnterpriseId ) INNER JOIN
       T_Site S ON ( O.SiteId = S.SiteId ) INNER JOIN
       T_OemSettlementFee OSF ON ( O.OrderSeq = OSF.OrderSeq ) INNER JOIN
       T_OemClaimFee OCF ON ( O.OrderSeq = OCF.OrderSeq ) INNER JOIN
       T_Oem OEM ON ( O.OemId = OEM.OemId )
WHERE  OEC.OemId = :OemId
AND    OEC.SpanFrom = :SpanFrom
AND    OEC.SpanTo = :SpanTo
ORDER BY E.EnterpriseId, S.SiteId, O.OrderId
EOQ;
        $prm = array(
            ':OemId' => $oemId,
            ':SpanFrom' => $search_range[0],
            ':SpanTo' => $search_range[1]
        );
        $stm = $this->app->dbAdapter->query( $sql );
        $lists = ResultInterfaceToArray( $stm->execute( $prm ) );

        $i = 0;
        foreach( $lists as $data ) {
            $data['No'] = ++$i;
            $datas[] = $data;
        }

        $templateId = 'CKI13098_2'; // OEM注文明細一覧CSV
        $templateClass = 1;
        $seq = $oemId;
        $templatePattern = 0;

        $logicTemplate = new LogicTemplate( $this->app->dbAdapter );
        $response = $logicTemplate->convertArraytoResponse( $datas, $fileName, $templateId, $templateClass, $seq, $templatePattern, $this->getResponse() );

        if( $response == false ) {
            throw new \Exception( $logicTemplate->getErrorMessage() );
        }

        return $response;
    }

    /**
     * 明細一式ダウンロード
     */
    public function downloadAction() {
        $params = $this->getParams();

        // ZIPファイル作成
        $zip = new \ZipArchive();

        // 出力ファイル名
        $outFileName= 'MeisaiIchiran.zip';

        // TEMP領域作成
        $tmpFilePath = tempnam( sys_get_temp_dir(), 'tmp' );

        // ZIPファイルオープン
        $zip->open( $tmpFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE );

        $unlinkList = array();

        //  OEM請求書兼納品書CSVファイル作成
        $tmpFileName1 = $this->createdecisionTransferFile( $params, $tmpFilePath );
        $unlinkList[] = $tmpFileName1;
        $fileName1 = str_replace( $tmpFilePath, '', $tmpFileName1 );

        // ZIPファイルにファイル追加
        $addFilePath = file_get_contents( $tmpFileName1 );
        $zip->addFromString( $fileName1, $addFilePath );

        // OEMお取引別精算明細CSVファイル作成
        $tmpFileName2 = $this->createordermeisaiFile( $params, $tmpFilePath );
        $unlinkList[] = $tmpFileName2;
        $fileName2 = str_replace( $tmpFilePath, '', $tmpFileName2 );

        // ZIPファイルにファイル追加
        $addFilePath = file_get_contents( $tmpFileName2 );
        $zip->addFromString( $fileName2, $addFilePath );

        // OEM印紙代明細CSVファイル作成
        $tmpFileName3 = $this->createstampfeeFile( $params, $tmpFilePath );
        $unlinkList[] = $tmpFileName3;
        $fileName3 = str_replace( $tmpFilePath, '', $tmpFileName3 );

        // ZIPファイルにファイル追加
        $addFilePath = file_get_contents( $tmpFileName3 );
        $zip->addFromString( $fileName3, $addFilePath );

        // OEMキャンセル返金明細CSVファイル作成
        $tmpFileName4 = $this->createCancelFile( $params, $tmpFilePath );
        $unlinkList[] = $tmpFileName4;
        $fileName4 = str_replace( $tmpFilePath, '', $tmpFileName4 );

        // ZIPファイルにファイル追加
        $addFilePath = file_get_contents( $tmpFileName4 );
        $zip->addFromString( $fileName4, $addFilePath );

        // OEM調整金明細CSVファイル作成
        $tmpFileName5 = $this->createadjustmentamountFile( $params, $tmpFilePath );
        $unlinkList[] = $tmpFileName5;
        $fileName5 = str_replace( $tmpFilePath, '', $tmpFileName5 );

        // ZIPファイルにファイル追加
        $addFilePath = file_get_contents( $tmpFileName5 );
        $zip->addFromString( $fileName5, $addFilePath );

        // OEM立替精算戻しCSVファイル作成
        $tmpFileName6 = $this->createpayingbackFile( $params, $tmpFilePath );
        $unlinkList[] = $tmpFileName6;
        $fileName6 = str_replace( $tmpFilePath, '', $tmpFileName6 );

        // ZIPファイルにファイル追加
        $addFilePath = file_get_contents( $tmpFileName6 );
        $zip->addFromString( $fileName6, $addFilePath );

        // ZIPファイルクローズ
        $zip->close();

        // ヘッダ
        header( 'Content-Type: application/octet-stream; name="' . $outFileName . '"' );
        header( 'Content-Disposition: attachment; filename="' . $outFileName . '"' );
        header( 'Content-Length: ' . filesize( $tmpFilePath ) );

        // 出力
        echo readfile( $tmpFilePath );

        // TEMP領域削除
        // count関数対策
        $unlinkListCount = 0;
        if (!empty($unlinkList)) {
            $unlinkListCount = count($unlinkList);
        }
        for ($i=0; $i<$unlinkListCount; $i++) {
            unlink( $unlinkList[$i] );
        }
        unlink( $tmpFilePath );
        die();
    }

    /**
     * OEM請求書兼納品書CSVダウンロード
     */
    public function ddecisionTransferAction() {
        $params = $this->getParams();

        // ファイル名
        $fileName = sprintf( "Oem_DecisionTransfer_%s.csv", date( "YmdHis" ) );

        $datas = $this->getdecisionTransfer( $params );

        $templateId = 'CKI13099_1'; // OEM請求書兼納品書CSV
        $templateClass = 1;
        $seq = $oemId;
        $templatePattern = 0;

        $logicTemplate = new LogicTemplate( $this->app->dbAdapter );
        $response = $logicTemplate->convertArraytoResponse( $datas, $fileName, $templateId, $templateClass, $seq, $templatePattern, $this->getResponse() );

        if( $response == false ) {
            throw new \Exception( $logicTemplate->getErrorMessage() );
        }

        return $response;
    }

    /**
     * OEMお取引別精算明細CSVダウンロード
     */
    public function dordermeisaiAction() {
        $params = $this->getParams();

        // ファイル名
        $fileName = sprintf( "Oem_OrderMeisai_%s.csv", date( "YmdHis" ) );

        $datas = $this->getordermeisai( $params );

        $templateId = 'CKI13100_1'; // OEMお取引別精算明細CSV
        $templateClass = 1;
        $seq = $oemId;
        $templatePattern = 0;

        $logicTemplate = new LogicTemplate( $this->app->dbAdapter );
        $response = $logicTemplate->convertArraytoResponse( $datas, $fileName, $templateId, $templateClass, $seq, $templatePattern, $this->getResponse() );

        if( $response == false ) {
            throw new \Exception( $logicTemplate->getErrorMessage() );
        }

        return $response;
    }

    /**
     * OEM印紙代明細CSVダウンロード
     */
    public function dstampfeeAction() {
        $params = $this->getParams();

        // ファイル名
        $fileName = sprintf( "Oem_StampFee_%s.csv", date( "YmdHis" ) );

        $datas = $this->getstampfee( $params );

        $templateId = 'CKI13101_1'; // OEM印紙代明細CSV
        $templateClass = 1;
        $seq = $oemId;
        $templatePattern = 0;

        $logicTemplate = new LogicTemplate( $this->app->dbAdapter );
        $response = $logicTemplate->convertArraytoResponse( $datas, $fileName, $templateId, $templateClass, $seq, $templatePattern, $this->getResponse() );

        if( $response == false ) {
            throw new \Exception( $logicTemplate->getErrorMessage() );
        }

        return $response;
    }

    /**
     * OEMキャンセル返金明細CSVダウンロード
     */
    public function dcancelAction() {
        $params = $this->getParams();

        // ファイル名
        $fileName = sprintf( "Oem_Cancel_%s.csv", date( "YmdHis" ) );

        $datas = $this->getcancel( $params );

        $templateId = 'CKI13102_1'; // OEMキャンセル返金明細CSV
        $templateClass = 1;
        $seq = $oemId;
        $templatePattern = 0;

        $logicTemplate = new LogicTemplate( $this->app->dbAdapter );
        $response = $logicTemplate->convertArraytoResponse( $datas, $fileName, $templateId, $templateClass, $seq, $templatePattern, $this->getResponse() );

        if( $response == false ) {
            throw new \Exception( $logicTemplate->getErrorMessage() );
        }

        return $response;
    }

    /**
     * OEM調整金明細CSVダウンロード
     */
    public function dadjustmentamountAction() {
        $params = $this->getParams();

        // ファイル名
        $fileName = sprintf( "Oem_AdjustmentAmount_%s.csv", date( "YmdHis" ) );

        $datas = $this->getadjustmentamount( $params );

        $templateId = 'CKI13103_1'; // OEM調整金明細CSV
        $templateClass = 1;
        $seq = $oemId;
        $templatePattern = 0;

        $logicTemplate = new LogicTemplate( $this->app->dbAdapter );
        $response = $logicTemplate->convertArraytoResponse( $datas, $fileName, $templateId, $templateClass, $seq, $templatePattern, $this->getResponse() );

        if( $response == false ) {
            throw new \Exception( $logicTemplate->getErrorMessage() );
        }

        return $response;
    }

    /**
     * OEM立替精算戻しCSVダウンロード
     */
    public function dpayingbackAction() {
        $params = $this->getParams();

        // ファイル名
        $fileName = sprintf( "Oem_PayingBack_%s.csv", date( "YmdHis" ) );

        $datas = $this->getpayingback( $params );

        $templateId = 'CKI13104_1'; // OEM立替精算戻しCSV
        $templateClass = 1;
        $seq = $oemId;
        $templatePattern = 0;

        $logicTemplate = new LogicTemplate( $this->app->dbAdapter );
        $response = $logicTemplate->convertArraytoResponse( $datas, $fileName, $templateId, $templateClass, $seq, $templatePattern, $this->getResponse() );

        if( $response == false ) {
            throw new \Exception( $logicTemplate->getErrorMessage() );
        }

        return $response;
    }

    /**
     * OEM請求書兼納品書CSVファイル作成
     * @param $params パラメータ
     * @param $tmpFilePath TEMP領域
     * @return ファイル名
     */
    private function createdecisionTransferFile( $params, $tmpFilePath ) {
        // ファイル名
        $fileName = sprintf( "Oem_DecisionTransfer_%s.csv", date( "YmdHis" ) );

        $datas = $this->getdecisionTransfer( $params );

        $templateId = 'CKI13099_1'; // OEM請求書兼納品書CSV
        $templateClass = 1;
        $seq = $oemId;
        $templatePattern = 0;

        $logicTemplate = new LogicTemplate( $this->app->dbAdapter );

        $tmpFileName = $tmpFilePath . $fileName;

        $result = $logicTemplate->convertArraytoFile( $datas, $tmpFileName, $templateId, $templateClass, $seq, $templatePattern );

        if( $result == false ) {
            throw new \Exception( $logicTemplate->getErrorMessage() );
        }

        return $tmpFileName;
    }

    /**
     * OEMお取引別精算明細CSVファイル作成
     * @param $params パラメータ
     * @param $tmpFilePath TEMP領域
     * @return ファイル名
     */
    private function createordermeisaiFile( $params, $tmpFilePath ) {
        // ファイル名
        $fileName = sprintf( "Oem_OrderMeisai_%s.csv", date( "YmdHis" ) );

        $datas = $this->getordermeisai( $params );

        $templateId = 'CKI13100_1'; // OEMお取引別精算明細CSV
        $templateClass = 1;
        $seq = $oemId;
        $templatePattern = 0;

        $logicTemplate = new LogicTemplate( $this->app->dbAdapter );

        $tmpFileName = $tmpFilePath . $fileName;

        $result = $logicTemplate->convertArraytoFile( $datas, $tmpFileName, $templateId, $templateClass, $seq, $templatePattern );

        if( $result == false ) {
            throw new \Exception( $logicTemplate->getErrorMessage() );
        }

        return $tmpFileName;
    }

    /**
     * OEM印紙代明細CSVファイル作成
     * @param $params パラメータ
     * @param $tmpFilePath TEMP領域
     * @return ファイル名
     */
    private function createstampfeeFile( $params, $tmpFilePath ) {
        // ファイル名
        $fileName = sprintf( "Oem_StampFee_%s.csv", date( "YmdHis" ) );

        $datas = $this->getstampfee( $params );

        $templateId = 'CKI13101_1'; // OEM印紙代明細CSV
        $templateClass = 1;
        $seq = $oemId;
        $templatePattern = 0;

        $logicTemplate = new LogicTemplate( $this->app->dbAdapter );

        $tmpFileName = $tmpFilePath . $fileName;

        $result = $logicTemplate->convertArraytoFile( $datas, $tmpFileName, $templateId, $templateClass, $seq, $templatePattern );

        if( $result == false ) {
            throw new \Exception( $logicTemplate->getErrorMessage() );
        }

        return $tmpFileName;
    }

    /**
     * OEMキャンセル返金明細CSVファイル作成
     * @param $params パラメータ
     * @param $tmpFilePath TEMP領域
     * @return ファイル名
     */
    private function createCancelFile( $params, $tmpFilePath ) {
        // ファイル名
        $fileName = sprintf( "Oem_Cancel_%s.csv", date( "YmdHis" ) );

        $datas = $this->getcancel( $params );

        $templateId = 'CKI13102_1'; // OEMキャンセル返金明細CSV
        $templateClass = 1;
        $seq = $oemId;
        $templatePattern = 0;

        $logicTemplate = new LogicTemplate( $this->app->dbAdapter );

        $tmpFileName = $tmpFilePath . $fileName;

        $result = $logicTemplate->convertArraytoFile( $datas, $tmpFileName, $templateId, $templateClass, $seq, $templatePattern );

        if( $result == false ) {
            throw new \Exception( $logicTemplate->getErrorMessage() );
        }

        return $tmpFileName;
    }

    /**
     * OEM調整金明細CSVファイル作成
     * @param $params パラメータ
     * @param $tmpFilePath TEMP領域
     * @return ファイル名
     */
    private function createadjustmentamountFile( $params, $tmpFilePath ) {
        // ファイル名
        $fileName = sprintf( "Oem_AdjustmentAmount_%s.csv", date( "YmdHis" ) );

        $datas = $this->getadjustmentamount( $params );

        $templateId = 'CKI13103_1'; // OEM調整金明細CSV
        $templateClass = 1;
        $seq = $oemId;
        $templatePattern = 0;

        $logicTemplate = new LogicTemplate( $this->app->dbAdapter );

        $tmpFileName = $tmpFilePath . $fileName;

        $result = $logicTemplate->convertArraytoFile( $datas, $tmpFileName, $templateId, $templateClass, $seq, $templatePattern );

        if( $result == false ) {
            throw new \Exception( $logicTemplate->getErrorMessage() );
        }

        return $tmpFileName;
    }

    /**
     * OEM立替精算戻しCSVファイル作成
     * @param $params パラメータ
     * @param $tmpFilePath TEMP領域
     * @return ファイル名
     */
    private function createpayingbackFile( $params, $tmpFilePath ) {
        // ファイル名
        $fileName = sprintf( "Oem_PayingBack_%s.csv", date( "YmdHis" ) );

        $datas = $this->getpayingback( $params );

        $templateId = 'CKI13104_1'; // OEM立替精算戻しCSV
        $templateClass = 1;
        $seq = $oemId;
        $templatePattern = 0;

        $logicTemplate = new LogicTemplate( $this->app->dbAdapter );

        $tmpFileName = $tmpFilePath . $fileName;

        $result = $logicTemplate->convertArraytoFile( $datas, $tmpFileName, $templateId, $templateClass, $seq, $templatePattern );

        if( $result == false ) {
            throw new \Exception( $logicTemplate->getErrorMessage() );
        }

        return $tmpFileName;
    }

    /**
     * OEM請求書兼納品書データ取得
     * @param $params パラメータ
     * @return array
     */
    private function getdecisionTransfer( $params ) {
        $oemId = !isset( $params['oemid'] ) ? -1 : $params['oemid'];
        $eId = !isset( $params['eid'] ) ? -1 : $params['eid'];
        $fixedDate = !isset( $params['fd'] ) ? -1 : $params['fd'];

        $search_range = explode( "_", $fixedDate );

        // OEM情報取得
        $mdloem = new TableOem( $this->app->dbAdapter );
        $oemData = $mdloem->findOem2( $oemId )->current();

        $mdloec = new TableOemEnterpriseClaimed( $this->app->dbAdapter );

        $lists = ResultInterfaceToArray( $mdloec->findOemEnterpriseClaimed( $oemId, $search_range[0], $search_range[1], $eId ) );

        foreach( $lists as $data ) {
            $data['OemNameKj'] = $oemData['OemNameKj'];
            // 決済手数料 = CB利益-決済手数料 + OEM利益-決済手数料
            $data['SettlementFee'] = $data['CB_SettlementFee'] + $data['OM_SettlementFee'];
            // 請求手数料 = CB利益-請求手数料(別送) + OEM利益-請求手数料(別送) + CB利益-請求手数料(同梱) + OEM利益-請求手数料(同梱)
            $data['ClaimFee'] = $data['CB_ClaimFeeBS'] + $data['CB_ClaimFeeDK'] + $data['OM_ClaimFeeBS'] + $data['OM_ClaimFeeDK'];
            // OEM手数料 = OEM利益-決済手数料 + OEM利益-請求手数料(別送) + OEM利益-請求手数料(同梱)
            $data['Oem_ReclaimFee'] = $data['OM_SettlementFee'] + $data['OM_ClaimFeeBS'] + $data['OM_ClaimFeeDK'];
            // 代理店固定費 = 立替-月額固定費 - CB利益-店舗月額 - OEM利益-店舗月額
            $data['AgencyMonthlyFee'] = $data['PC_MonthlyFee'] - $data['CB_EntMonthlyFee'] - $data['OM_EntMonthlyFee'];

            $datas[] = $data;
        }

        return $datas;
    }

    /**
     * OEMお取引別精算明細データ取得
     * @param $params パラメータ
     * @return array
     */
    private function getordermeisai( $params ) {
        $oemId = !isset( $params['oemid'] ) ? -1 : $params['oemid'];
        $eId = !isset( $params['eid'] ) ? -1 : $params['eid'];
        $fixedDate = !isset( $params['fd'] ) ? -1 : $params['fd'];

        $search_range = explode( "_", $fixedDate );

        // データ取得
        $sql = <<<EOQ
SELECT OEM.OemId
,      OEM.OemNameKj
,      E.LoginId
,      E.EnterpriseNameKj
,      S.SiteId
,      S.SiteNameKj
,      PC.AddUpFixedMonth
,      O.OrderId
,      O.Ent_OrderId
,      C.NameKj
,      O.ReceiptOrderDate
,      ( SELECT MAX( Deli_JournalIncDate ) FROM T_OrderItems WHERE OrderSeq = O.OrderSeq ) AS Deli_JournalIncDate
,      PC.FixedDate
,      PAS.UseAmount
,      ( -1 * PAS.SettlementFee ) AS SettlementFee
,      ( -1 * PAS.ClaimFee ) AS ClaimFee
,      ( -1 * ( PAS.SettlementFee + PAS.ClaimFee - ( OSF.SettlementFee + OCF.ClaimFee ) ) ) AS Oem_ReclaimFee
,      ( -1 * IFNULL( SF.StampFee, 0 ) ) AS StampFee
,      ( PAS.UseAmount + ( -1 * PAS.SettlementFee ) +  ( -1 * PAS.ClaimFee ) + ( -1 * IFNULL( SF.StampFee, 0 ) ) ) AS Chg_ChargeAmount
FROM   T_PayingControl PC INNER JOIN
       T_OemEnterpriseClaimed OEC ON ( PC.OemClaimedSeq = OEC.OemClaimedSeq AND PC.OemId = OEC.OemId AND PC.EnterpriseId = OEC.EnterpriseId ) INNER JOIN
       T_PayingAndSales PAS ON ( PC.Seq = PAS.PayingControlSeq ) INNER JOIN
       T_Order O ON ( PAS.OrderSeq = O.OrderSeq ) LEFT OUTER JOIN
       T_StampFee SF ON ( O.OrderSeq = SF.OrderSeq ) INNER JOIN
       T_Customer C ON ( O.OrderSeq = C.OrderSeq ) INNER JOIN
       T_Enterprise E ON ( PC.EnterpriseId = E.EnterpriseId AND PC.OemId = E.OemId ) INNER JOIN
       T_Site S ON ( O.SiteId = S.SiteId ) INNER JOIN
       T_OemSettlementFee OSF ON ( O.OrderSeq = OSF.OrderSeq ) INNER JOIN
       T_OemClaimFee OCF ON ( O.OrderSeq = OCF.OrderSeq ) INNER JOIN
       T_Oem OEM ON ( O.OemId = OEM.OemId )
WHERE  OEC.OemId = :OemId
AND    OEC.EnterpriseId = :EnterpriseId
AND    OEC.SpanFrom = :SpanFrom
AND    OEC.SpanTo = :SpanTo
ORDER BY E.EnterpriseId, S.SiteId, O.OrderId
EOQ;
        $prm = array(
            ':OemId' => $oemId,
            ':EnterpriseId' => $eId,
            ':SpanFrom' => $search_range[0],
            ':SpanTo' => $search_range[1]
        );
        $stm = $this->app->dbAdapter->query( $sql );
        $lists = ResultInterfaceToArray( $stm->execute( $prm ) );

        $i = 0;
        foreach( $lists as $data ) {
            $data['No'] = ++$i;
            $datas[] = $data;
        }

        return $datas;
    }

    /**
     * OEM印紙代明細データ取得
     * @param $params パラメータ
     * @return array
     */
    private function getstampfee( $params ) {
        $oemId = !isset( $params['oemid'] ) ? -1 : $params['oemid'];
        $eId = !isset( $params['eid'] ) ? -1 : $params['eid'];
        $fixedDate = !isset( $params['fd'] ) ? -1 : $params['fd'];

        $search_range = explode( "_", $fixedDate );

        // データ取得
        $sql = <<<EOQ
SELECT OEM.OemId
,      OEM.OemNameKj
,      E.LoginId
,      E.EnterpriseNameKj
,      S.SiteId
,      S.SiteNameKj
,      PC.AddUpFixedMonth
,      O.OrderId
,      O.Ent_OrderId
,      C.NameKj
,      O.ReceiptOrderDate
,      ( SELECT MAX( Deli_JournalIncDate ) FROM T_OrderItems WHERE OrderSeq = O.OrderSeq ) AS Deli_JournalIncDate
,      PC.FixedDate
,      O.UseAmount
,      SF.StampFee
FROM   T_PayingControl PC INNER JOIN
       T_OemEnterpriseClaimed OEC ON ( PC.OemClaimedSeq = OEC.OemClaimedSeq AND PC.OemId = OEC.OemId AND PC.EnterpriseId = OEC.EnterpriseId ) INNER JOIN
       T_StampFee SF ON ( PC.Seq = SF.PayingControlSeq ) INNER JOIN
       T_Order O ON ( SF.OrderSeq = O.OrderSeq ) INNER JOIN
       T_Customer C ON ( O.OrderSeq = C.OrderSeq ) INNER JOIN
       T_Site S ON ( O.SiteId = S.SiteId ) INNER JOIN
       T_Oem OEM ON ( O.OemId = OEM.OemId ) INNER JOIN
       T_Enterprise E ON ( PC.EnterpriseId = E.EnterpriseId AND PC.OemId = E.OemId )
WHERE  OEC.OemId = :OemId
AND    OEC.EnterpriseId = :EnterpriseId
AND    OEC.SpanFrom = :SpanFrom
AND    OEC.SpanTo = :SpanTo
ORDER BY E.EnterpriseId, S.SiteId, O.OrderId
EOQ;
        $prm = array(
            ':OemId' => $oemId,
            ':EnterpriseId' => $eId,
            ':SpanFrom' => $search_range[0],
            ':SpanTo' => $search_range[1]
        );
        $stm = $this->app->dbAdapter->query( $sql );
        $lists = ResultInterfaceToArray( $stm->execute( $prm ) );

        $i = 0;
        foreach( $lists as $data ) {
            $data['No'] = ++$i;
            $datas[] = $data;
        }

        return $datas;
    }

    /**
     * OEMキャンセル返金明細データ取得
     * @param $params パラメータ
     * @return array
     */
    private function getcancel( $params ) {
        $oemId = !isset( $params['oemid'] ) ? -1 : $params['oemid'];
        $eId = !isset( $params['eid'] ) ? -1 : $params['eid'];
        $fixedDate = !isset( $params['fd'] ) ? -1 : $params['fd'];

        $search_range = explode( "_", $fixedDate );

        // データ取得
        $sql = <<<EOQ
SELECT OEM.OemId
,      OEM.OemNameKj
,      E.LoginId
,      E.EnterpriseNameKj
,      S.SiteId
,      S.SiteNameKj
,      PC.AddUpFixedMonth
,      O.OrderId
,      O.Ent_OrderId
,      C.NameKj
,      O.ReceiptOrderDate
,      ( SELECT MAX( Deli_JournalIncDate ) FROM T_OrderItems WHERE OrderSeq = O.OrderSeq ) AS Deli_JournalIncDate
,      PC.FixedDate
,      CNL.CancelDate
,      O.UseAmount
,      CNL.RepayTotal
FROM   T_PayingControl PC INNER JOIN
       T_OemEnterpriseClaimed OEC ON ( PC.OemClaimedSeq = OEC.OemClaimedSeq AND PC.OemId = OEC.OemId AND PC.EnterpriseId = OEC.EnterpriseId ) INNER JOIN
       T_Cancel CNL ON ( PC.Seq = CNL.PayingControlSeq ) INNER JOIN
       T_Order O ON ( CNL.OrderSeq = O.OrderSeq ) INNER JOIN
       T_Customer C ON ( O.OrderSeq = C.OrderSeq ) INNER JOIN
       T_Site S ON ( O.SiteId = S.SiteId ) INNER JOIN
       T_Oem OEM ON ( O.OemId = OEM.OemId ) INNER JOIN
       T_Enterprise E ON ( PC.EnterpriseId = E.EnterpriseId AND PC.OemId = E.OemId )
WHERE  OEC.OemId = :OemId
AND    OEC.EnterpriseId = :EnterpriseId
AND    OEC.SpanFrom = :SpanFrom
AND    OEC.SpanTo = :SpanTo
AND    CNL.ValidFlg = 1
ORDER BY S.SiteId, O.OrderId
EOQ;
        $prm = array(
            ':OemId' => $oemId,
            ':EnterpriseId' => $eId,
            ':SpanFrom' => $search_range[0],
            ':SpanTo' => $search_range[1]
        );
        $stm = $this->app->dbAdapter->query( $sql );
        $lists = ResultInterfaceToArray( $stm->execute( $prm ) );

        $i = 0;
        foreach( $lists as $data ) {
            $data['No'] = ++$i;
            $datas[] = $data;
        }

        return $datas;
    }

    /**
     * OEM調整金明細データ取得
     * @param $params パラメータ
     * @return array
     */
    private function getadjustmentamount( $params ) {
        $oemId = !isset( $params['oemid'] ) ? -1 : $params['oemid'];
        $eId = !isset( $params['eid'] ) ? -1 : $params['eid'];
        $fixedDate = !isset( $params['fd'] ) ? -1 : $params['fd'];

        $search_range = explode( "_", $fixedDate );

        // データ取得
        $sql = <<<EOQ
SELECT OEC.FixedMonth
,      PC.FixedDate
,      AA.OrderId
,      O.Ent_OrderId
,      C.NameKj
,      O.ReceiptOrderDate
,      ( SELECT KeyContent FROM M_Code WHERE CodeId = 89 AND KeyCode = AA.ItemCode ) AS kamoku
,      AA.ItemCode
,      AA.AdjustmentAmount
FROM   T_PayingControl PC INNER JOIN
       T_OemEnterpriseClaimed OEC ON ( PC.OemClaimedSeq = OEC.OemClaimedSeq AND PC.OemId = OEC.OemId AND PC.EnterpriseId = OEC.EnterpriseId ) INNER JOIN
       T_AdjustmentAmount AA ON ( PC.Seq = AA.PayingControlSeq ) LEFT OUTER JOIN
       T_Order O ON ( AA.OrderSeq = O.OrderSeq ) LEFT OUTER JOIN
       T_Customer C ON ( AA.OrderSeq = C.OrderSeq )
WHERE  OEC.OemId = :OemId
AND    OEC.EnterpriseId = :EnterpriseId
AND    OEC.SpanFrom = :SpanFrom
AND    OEC.SpanTo = :SpanTo
ORDER BY AA.SerialNumber
EOQ;
        $prm = array(
            ':OemId' => $oemId,
            ':EnterpriseId' => $eId,
            ':SpanFrom' => $search_range[0],
            ':SpanTo' => $search_range[1]
        );
        $stm = $this->app->dbAdapter->query( $sql );
        $lists = ResultInterfaceToArray( $stm->execute( $prm ) );

        // OEM情報取得
        $mdloem = new TableOem( $this->app->dbAdapter );
        $oemData = $mdloem->findOem2( $oemId )->current();

        // ENTERPRISE情報取得
        $mdlent = new TableEnterprise( $this->app->dbAdapter );
        $entData = $mdlent->findEnterprise2( $eId )->current();

        // ORDER情報取得
        $mdlo = new TableOrder( $this->app->dbAdapter );

        // SITE情報取得
        $mdls = new TableSite( $this->app->dbAdapter );

        $i = 0;
        foreach( $lists as $data ) {
            $data['No'] = ++$i;
            $data['OemId'] = $oemId;
            $data['OemNameKj'] = $oemData['OemNameKj'];
            $data['LoginId'] = $entData['LoginId'];
            $data['EnterpriseNameKj'] = $entData['EnterpriseNameKj'];
            $data['SiteId'] = !empty( $data['OrderId'] ) ? $mdlo->findOrder( array( 'OrderId' => $data['OrderId'] ) )->current()['SiteId'] : '';
            $data['SiteNameKj'] = !empty( $data['SiteId'] ) ? $mdls->findSite( $data['SiteId'] )->current()['SiteNameKj'] : '';
            $datas[] = $data;
        }

        return $datas;
    }

    /**
     * OEM立替精算戻しCSVダウンロード
     * @param $params パラメータ
     * @return array
     */
    private function getpayingback( $params ) {
        $oemId = !isset( $params['oemid'] ) ? -1 : $params['oemid'];
        $eId = !isset( $params['eid'] ) ? -1 : $params['eid'];
        $fixedDate = !isset( $params['fd'] ) ? -1 : $params['fd'];

        $search_range = explode( "_", $fixedDate );

        // データ取得
        $sql = <<<EOQ
SELECT OEM.OemId
,      OEM.OemNameKj
,      E.LoginId
,      E.EnterpriseNameKj
,      S.SiteId
,      S.SiteNameKj
,      PC.AddUpFixedMonth
,      O.OrderId
,      O.Ent_OrderId
,      C.NameKj
,      O.ReceiptOrderDate
,      ( SELECT MAX( Deli_JournalIncDate ) FROM T_OrderItems WHERE OrderSeq = O.OrderSeq ) AS Deli_JournalIncDate
,      PC.FixedDate
,      PAS.UseAmount
,      ( -1 * ( PAS.SettlementFee ) ) AS SettlementFee
,      ( -1 * ( PAS.ClaimFee ) ) AS ClaimFee
,      ( -1 * ( PAS.SettlementFee + PAS.ClaimFee - ( OSF.SettlementFee + OCF.ClaimFee ) ) ) AS OemFee
,      ( -1 * IFNULL( SF.StampFee, 0 ) ) AS StampFee
,      PBC.PayBackAmount
FROM   T_PayingControl PC
       INNER JOIN T_OemEnterpriseClaimed OEC ON ( PC.OemClaimedSeq = OEC.OemClaimedSeq AND PC.OemId = OEC.OemId AND PC.EnterpriseId = OEC.EnterpriseId )
       INNER JOIN T_PayingBackControl PBC ON ( PC.Seq = PBC.PayingControlSeq )
       INNER JOIN T_PayingAndSales PAS ON ( PAS.OrderSeq = PBC.OrderSeq )
       INNER JOIN T_Order O ON ( PBC.OrderSeq = O.OrderSeq  )
       LEFT OUTER JOIN T_StampFee SF ON ( SF.OrderSeq = O.OrderSeq  )
       INNER JOIN T_Customer C ON ( O.OrderSeq = C.OrderSeq )
       INNER JOIN T_Site S ON ( O.SiteId = S.SiteId )
       INNER JOIN T_OemSettlementFee OSF ON ( O.OrderSeq = OSF.OrderSeq )
       INNER JOIN T_OemClaimFee OCF ON ( O.OrderSeq = OCF.OrderSeq )
       INNER JOIN T_Oem OEM ON ( O.OemId = OEM.OemId )
       INNER JOIN T_Enterprise E ON ( PC.EnterpriseId = E.EnterpriseId AND PC.OemId = E.OemId )
WHERE  OEC.OemId = :OemId
AND    OEC.EnterpriseId = :EnterpriseId
AND    OEC.SpanFrom = :SpanFrom
AND    OEC.SpanTo = :SpanTo
ORDER BY S.SiteId, O.OrderId
EOQ;
        $prm = array(
            ':OemId' => $oemId,
            ':EnterpriseId' => $eId,
            ':SpanFrom' => $search_range[0],
            ':SpanTo' => $search_range[1]
        );
        $stm = $this->app->dbAdapter->query( $sql );
        $lists = ResultInterfaceToArray( $stm->execute( $prm ) );

        $i = 0;
        foreach( $lists as $data ) {
            $data['No'] = ++$i;
            $datas[] = $data;
        }

        return $datas;
    }

    /**
     * 年月選択リスト作成
     */
    protected function _createMonthList($oemId) {

        $mdloc = new TableOemClaimed($this->app->dbAdapter);

        $date_list = array();

        //OEM請求データ取得
        $oem_claimed = ResultInterfaceToArray( $mdloc->findOemClaimed($oemId) );

        //年月作成
        foreach($oem_claimed as $value){
            $from = date('Y年m月d日', strtotime($value['SpanFrom']));
            $to   = date('Y年m月d日', strtotime($value['SpanTo']));
            $date_list += array($value['SpanFrom']."_".$value['SpanTo'] => date('Y年m月度　'.$from."～".$to, strtotime($value['FixedMonth'])));

        }

        return $date_list;
    }

    /**
     * SQL実行結果の配列を、指定のカラム名でグルーピングした
     * 連想配列として詰めなおす
     *
     * @param array $list クエリ実行結果の配列
     * @param string $key_name グルーピングキーとなる、$listの1要素中のカラム名
     * @return array
     */
    private function grouping($list, $key_name) {
        $results = array();
        foreach($list as $row) {
            $key = $row[$key_name];
            if( is_array($results[$key]) ) {
                $results[$key][] = $row;
            } else {
                $results[$key] = array($row);
            }
        }
        return $results;
    }
}

