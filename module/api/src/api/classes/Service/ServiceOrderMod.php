<?php
namespace api\classes\Service;

use api\Application;
use api\classes\Service\ServiceAbstract;
use api\classes\Service\Response\ServiceResponseOrderMod;
use api\classes\Service\Response\ServiceResponseStatus;
use api\classes\Service\Status\ServiceStatusConst;
use api\classes\Service\OrderMod\ServiceOrderModConst;
use api\classes\Service\OrderMod\ServiceOrderModOrderConst;
use api\classes\Service\OrderMod\ServiceOrderModCustomerConst;
use api\classes\Service\OrderMod\ServiceOrderModDestinationConst;
use api\classes\Service\OrderMod\ServiceOrderModItemsConst;
use api\classes\Service\OrderMod\ServiceOrderModSchemaMap;
use Coral\Base\BaseDelegate;
use Coral\Base\BaseGeneralUtils;
use Coral\Coral\Validate\CoralValidateUtility;
use Coral\Coral\Mail\CoralMail;
use Coral\Coral\CoralValidate;
use models\Logic\LogicOrderRegister;
use models\Table\TableCsvSchema;
use models\Table\TableSite;
use models\Logic\CreditJudge\LogicCreditJudgeSequencer;
use models\Logic\CreditJudge\LogicCreditJudgeAbstract;
use models\Logic\CreditJudge\LogicCreditJudgePrejudgeThread;
use models\Logic\LogicThreadPool;
use Zend\Db\ResultSet\ResultSet;
use models\Table\TableTemplateHeader;
use models\Table\TableTemplateField;
use models\Table\TableOrder;
use models\Table\TableEnterprise;
use models\Table\TableUser;
use models\Table\TableOrderItems;
use models\Logic\LogicPayeasy;
use models\Logic\LogicConstant;


/**
 * 注文修正サービスクラス
 */
class ServiceOrderMod extends ServiceAbstract {

    const CSVSCHEMA_CLASS = 1;

    /**
     * 注文修正APIのサービスID
     * @var string
     */
    protected $_serviceId = "11";

    /**
     * OrderStatusを返却するか
     * @var string
     */
    public $_rtOrderStatus;

    /**
     * スレッドロックアイテム
     *
     * @access protected
     * @var LogicThreadPoolItem
     */
    protected $_lockItem;

    /**
     * (non-PHPdoc)
     * @see api/classes/Service/ServiceAbstract#init()
     */
    protected function init() {

        // レスポンスを初期化
        $this->_response = new ServiceResponseOrderMod();

        // 任意注文ID
        $order = $this->_data[ServiceOrderModConst::ORDER];
        $this->_response->orderId = $order[ServiceOrderModOrderConst::$Ent_OrderId->name];
        // 注文ID (後払いシステム採番)
        $this->_response->systemOrderId = $order[ServiceOrderModOrderConst::$OrderId->name];
        // 与信状況コード
        $this->_response->orderStatus = "";

        // 認証用
        $this->_apiUserId    = $order[ServiceOrderModOrderConst::$ApiUserId->name];
        $this->_enterpriseId = $order[ServiceOrderModOrderConst::$EnterpriseId->name];
        $this->_siteId       = $order[ServiceOrderModOrderConst::$SiteId->name];

        // ログ出力
        Application::getInstance()->logger->info(
            get_class($this) . '#init() ' .
            "ReceiptOrderDate: " . $order[ServiceOrderModOrderConst::$ReceiptOrderDate->name] . ", " .
            "EnterpriseId: " . $this->_enterpriseId . ", " .
            "SiteId: " . $this->_siteId . ", " .
            "ApiUserId: " . $this->_apiUserId . ", " .
            "Ent_OrderId: " . $this->_response->orderId . ", " .
            "RemoteAddr: " . f_get_client_address());       // 2015/09/23 Y.Suzuki Mod f_get_client_address をｺｰﾙするように変更

        // サイト情報の取得
        $sedsite = new TableSite($this->_db);
        $sedsiteInfo = $sedsite->findSite($this->_siteId)->current();

        // 加盟店情報の取得
        $sedEnterprise = new TableEnterprise($this->_db);
        $enterpriseInfo = $sedEnterprise->findEnterprise($this->_enterpriseId)->current();

        // 注文情報の取得
        $mdlo = new TableOrder($this->_db);
        $orderDatas = ResultInterfaceToArray($mdlo->findOrder(
        array('OrderId'      => $order[ServiceOrderModOrderConst::$OrderId->name]
                ,'EnterpriseId' => $order[ServiceOrderModOrderConst::$EnterpriseId->name]
             )
        ));
        // 明細に外税レコードが存在するか判定
        $taxClass = 0;
        $orderDatasCount = 0;
        if (!empty($orderDatas)) {
            $orderDatasCount = count($orderDatas);
        }
        if($orderDatasCount == 1) {
            $mdloi = new TableOrderItems($this->_db);
            $oi = ResultInterfaceToArray($mdloi->findByOrderSeq($orderDatas[0]['OrderSeq']));
            foreach ($oi as $row) {
                if ($row['DataClass'] == 4) {
                    $taxClass = 1;
                    break;
                }
            }
        }

        // 役務対象区分の設定
        $this->_data[ServiceOrderModConst::ORDER][ServiceOrderModOrderConst::$ServiceTargetClass->name] = $sedsiteInfo['ServiceTargetClass'];

        // テスト注文自動与信審査区分の設定
        if ( !empty($this->_data[ServiceOrderModConst::ORDER][ServiceOrderModOrderConst::$T_OrderAutoCreditJudgeClass->name])
        &&   $this->_data[ServiceOrderModConst::ORDER][ServiceOrderModOrderConst::$T_OrderAutoCreditJudgeClass->name] != 0
        &&   $this->_data[ServiceOrderModConst::ORDER][ServiceOrderModOrderConst::$T_OrderAutoCreditJudgeClass->name] != 2 ) {
            $this->_data[ServiceOrderModConst::ORDER][ServiceOrderModOrderConst::$T_OrderAutoCreditJudgeClass->name] = 1;
        }

        // 請求書別送の設定
        if ( !empty($this->_data[ServiceOrderModConst::ORDER][ServiceOrderModOrderConst::$SeparateShipment->name])
        && $this->_data[ServiceOrderModConst::ORDER][ServiceOrderModOrderConst::$SeparateShipment->name] != 1 ) {
            $this->_data[ServiceOrderModConst::ORDER][ServiceOrderModOrderConst::$SeparateShipment->name] = 0;
        }

        // 外税額の設定
        if ( $taxClass != 1 ) {
            $this->_data[ServiceOrderModConst::ORDER][ServiceOrderModOrderConst::$ItemOutsideTaxs->name] = 0;
            $order[ServiceOrderModOrderConst::$ItemOutsideTaxs->name] = 0;
        }

        // 請求書送付区分の設定
        if ( $enterpriseInfo['SelfBillingMode'] > 0 && $sedsiteInfo['SelfBillingFlg'] == 1){
            if ($this->_data[ServiceOrderModConst::ORDER][ServiceOrderModOrderConst::$SeparateShipment->name] == 1){
                $this->_data[ServiceOrderModConst::ORDER][ServiceOrderModOrderConst::$ClaimSendingClass->name] = 12;
            }else{
                $this->_data[ServiceOrderModConst::ORDER][ServiceOrderModOrderConst::$ClaimSendingClass->name] = 11;
            }
        }else{
            $this->_data[ServiceOrderModConst::ORDER][ServiceOrderModOrderConst::$ClaimSendingClass->name] = 21;
        }

        // 基本情報内の送料、店舗手数料を商品にコピー
        $items = $this->_data[ServiceOrderModConst::ITEMS];

        // 送料
        $items[ServiceOrderModItemsConst::KEY_ITEM_CARRIAGE] = array(
            'DeliDestId'                                 => -1,            // 配送先ID
            'DataClass'                                  => 2,            // 送料
            ServiceOrderModItemsConst::$ItemNameKj->name    => '送料',
            ServiceOrderModItemsConst::$UnitPrice->name     =>                // データがない場合は0円と見なす
                                                            ((int)$order[ServiceOrderModOrderConst::$ItemCarriage->name]),
            ServiceOrderModItemsConst::$ItemNum->name       => 1
        );

        // 店舗手数料
        $items[ServiceOrderModItemsConst::KEY_ITEM_CHARGE] = array(
            'DeliDestId'                                 => -1,            // 配送先ID
            'DataClass'                                  => 3,            // 手数料
            ServiceOrderModItemsConst::$ItemNameKj->name    => '店舗手数料',
            ServiceOrderModItemsConst::$UnitPrice->name     =>                // データがない場合は0円と見なす
                                                            ((int)$order[ServiceOrderModOrderConst::$ItemCharge->name]),
            ServiceOrderModItemsConst::$ItemNum->name       => 1
        );
        // 外税額
        if ($taxClass == 1) {
            // 元の注文にデータクラス=4の明細が存在する場合のみ作成
            $items[ServiceOrderModItemsConst::KEY_ITEM_TAX] = array(
                    'DeliDestId'                                 => -1,            // 配送先ID
                    'DataClass'                                  => 4,            // 手数料
                    ServiceOrderModItemsConst::$ItemNameKj->name    => '外税額',
                    ServiceOrderModItemsConst::$UnitPrice->name     =>                // データがない場合は0円と見なす
                    ((int)$order[ServiceOrderModOrderConst::$ItemOutsideTaxs->name]),
                    ServiceOrderModItemsConst::$ItemNum->name       => 1
            );
        }
        // 口座振替申請
        if (( $enterpriseInfo['CreditTransferFlg'] != 1 ) && ( $enterpriseInfo['CreditTransferFlg'] != 2 ) && ( $enterpriseInfo['CreditTransferFlg'] != 3 )) {
            // 加盟店の設定で口座振替を利用しない場合は、強制的に 0:利用しない とする
            $this->_data[ServiceOrderModConst::ORDER][ServiceOrderModOrderConst::$CreditTransferRequestFlg->name] = 0;

        }

        $this->_data[ServiceOrderModConst::ITEMS] = $items;
    }

    /**
     * (non-PHPdoc)
     * @see api/classes/Service/ServiceAbstract#check()
     */
    protected function check() {
        $result = true;

        // 注文有効のチェック
        $mdlo = new TableOrder($this->_db);
        $orderDatas = $mdlo->findOrder(
        array('OrderId'      => $this->_data[ServiceOrderModConst::ORDER][ServiceOrderModOrderConst::$OrderId->name]
                ,'EnterpriseId' => $this->_data[ServiceOrderModConst::ORDER][ServiceOrderModOrderConst::$EnterpriseId->name]
        )
        );
        if($orderDatas->count() != 1) {
            $this->_response->addMessage("E" . $this->_serviceId . "304", "注文ID : 指定の注文が無効です");
            return false;
        }

        // 修正可能な状態かのチェック
        $row = $orderDatas->current();
        if (!($row['DataStatus'] < 41 && $row['Cnl_Status'] == 0)) {
            $this->_response->addMessage("E" . $this->_serviceId . "306", "指定の注文は登録されていないか修正可能な状態ではありません");
            return false;
        }

        // 基本情報の入力チェック
        $order = $this->_data[ServiceOrderModConst::ORDER];
        $result = $result & $this->_validation( $order, ServiceOrderModOrderConst::$ReceiptOrderDate );
        $result = $result & $this->_validation( $order, ServiceOrderModOrderConst::$Ent_OrderId );
        $result = $result & $this->_validation( $order, ServiceOrderModOrderConst::$Ent_Note );
        $result = $result & $this->_validation( $order, ServiceOrderModOrderConst::$UseAmount );
        $result = $result & $this->_validation( $order, ServiceOrderModOrderConst::$Oem_OrderId );
        $result = $result & $this->_validation( $order, ServiceOrderModOrderConst::$T_OrderAutoCreditJudgeClass );
        $result = $result & $this->_validation( $order, ServiceOrderModOrderConst::$SeparateShipment );
        $result = $result & $this->_validation( $order, ServiceOrderModOrderConst::$AnotherDeliFlg );

        // 役務提供予定日の必須チェック
        if ( $order[ServiceOrderModOrderConst::$ServiceTargetClass->name] == 1 ) {
            // 役務サイト

            // 必須チェック
            if ( empty($order[ServiceOrderModOrderConst::$ServiceExpectedDate->name]) ) {
                $this->_response->addMessage("E" . $this->_serviceId . "201", "役務提供予定日 : データを0または空にすることはできません");
                $result = false;
            }

            // 日付形式チェック
            $isdate = $this->_validation( $order, ServiceOrderModOrderConst::$ServiceExpectedDate );
            $result = $result & $isdate;

            // 過去日チェック
            if ( $isdate ) {
                // 役務提供予定日が変更された場合のみチェックする
                $isValueChange = (date('Y-m-d', strtotime($order[ServiceOrderModOrderConst::$ServiceExpectedDate->name])) == $row['ServiceExpectedDate']) ? false : true;
                $diffDate = BaseGeneralUtils::CalcSpanDays(date('Y-m-d', strtotime($order[ServiceOrderModOrderConst::$ServiceExpectedDate->name])), date('Y-m-d'));
                if ( $isValueChange && $diffDate >= 30 ) {
                    $this->_response->addMessage("E" . $this->_serviceId . "305", "役務提供予定日 : 過去日を指定することはできません");
                    $result = false;
                }
            }
        }
        else if ( $order[ServiceOrderModOrderConst::$ServiceTargetClass->name] == 0 ) {
            // 物販サイト

            // 設定がある場合エラー
            if (!empty($order[ServiceOrderModOrderConst::$ServiceExpectedDate->name]) ) {
                $this->_response->addMessage("E" . $this->_serviceId . "307", "役務提供予定日 : 受付サイトが役務対象でないため、役務提供予定日は設定できません");
                $result = false;
            }
        }

        $mdlEnt = new TableEnterprise($this->_db);
        $oemid =  $mdlEnt->find($order[ServiceOrderModOrderConst::$EnterpriseId->name])->current()['OemId'];
//        $logicpayeasy = new LogicPayeasy($this->_db);
//        $PayeasyFlg = $logicpayeasy->isPayeasyOem($oemid);

        // ご注文者（請求先）情報の入力チェック
        $customer = $this->_data[ServiceOrderModConst::CUSTOMER];
        $result = $result & $this->_validation( $customer, ServiceOrderModCustomerConst::$PostalCode );
        $result = $result & $this->_validation( $customer, ServiceOrderModCustomerConst::$UnitingAddress );
        $result = $result & $this->_validation( $customer, ServiceOrderModCustomerConst::$NameKj, true, $oemid );
        $result = $result & $this->_validation( $customer, ServiceOrderModCustomerConst::$NameKn, true, $oemid );
        $result = $result & $this->_validation( $customer, ServiceOrderModCustomerConst::$Phone, true, $oemid );
        $result = $result & $this->_validation( $customer, ServiceOrderModCustomerConst::$MailAddress, true, $oemid );
        $result = $result & $this->_validation( $customer, ServiceOrderModCustomerConst::$Occupation );
        $result = $result & $this->_validation( $customer, ServiceOrderModCustomerConst::$EntCustId );
        $result = $result & $this->_validation( $customer, ServiceOrderModCustomerConst::$CorporateName );
        $result = $result & $this->_validation( $customer, ServiceOrderModCustomerConst::$DivisionName );
        $result = $result & $this->_validation( $customer, ServiceOrderModCustomerConst::$CpNameKj );

        // メールアドレスが必須なサイトでメールアドレス未指定
        $mdlsite = new TableSite($this->_db);
        $siteInfo = $mdlsite->findSite($this->_siteId)->current();
        if ( $siteInfo['ReqMailAddrFlg'] == 1 && empty($customer[ServiceOrderModCustomerConst::$MailAddress->name]) ) {
            $this->_response->addMessage("E" . $this->_serviceId . "301", "メールアドレス : メールアドレスが必須なサイトとして登録されているため、メールアドレスを空にすることはできません");
            $result = false;
        }

        // 別配送先情報の入力チェック
        if ( $order[ServiceOrderModOrderConst::$AnotherDeliFlg->name] == '1' ) {
            $dest = $this->_data[ServiceOrderModConst::DESTINATION];
            $result = $result & $this->_validation( $dest, ServiceOrderModDestinationConst::$PostalCode );
            $result = $result & $this->_validation( $dest, ServiceOrderModDestinationConst::$UnitingAddress );
            $result = $result & $this->_validation( $dest, ServiceOrderModDestinationConst::$DestNameKj );
            $result = $result & $this->_validation( $dest, ServiceOrderModDestinationConst::$DestNameKn );
            $result = $result & $this->_validation( $dest, ServiceOrderModDestinationConst::$Phone );
        }

        // 加盟店の[利用額端数計算設定]取得
        $adapter = Application::getInstance()->dbAdapter;
        $row_ent = $adapter->query(" SELECT * FROM T_Enterprise WHERE EnterpriseId = :EnterpriseId ")->execute(array(':EnterpriseId' => $this->_enterpriseId))->current();
        $useAmountFractionClass = (int)$row_ent['UseAmountFractionClass'];
        $creditTransferFlg = $row_ent['CreditTransferFlg'];

        // 商品情報の入力チェック
        $sumMount = 0;
        $itemCount = 0;
        foreach ( $this->_data[ServiceOrderModConst::ITEMS] as $idx => $item) {
            // 商品のみ対象
            if ( $item['DataClass'] == 1) {
                $itemCheck = true;
                // 組み合わせチェック (この時点ではレスポンスにエラー情報を設定しない)
                $itemCheck = $itemCheck && $this->_validation( $item, ServiceOrderModItemsConst::$ItemNameKj, false );
                $itemCheck = $itemCheck && $this->_validation( $item, ServiceOrderModItemsConst::$UnitPrice, false );
                $itemCheck = $itemCheck && $this->_validation( $item, ServiceOrderModItemsConst::$ItemNum, false, $oemid);
                //消費税エラーチェック
                if (!($item['TaxRate'] == '' || ($item['TaxRate'] == 0 ||$item['TaxRate'] == 8 || $item['TaxRate'] == 10))) {
                    $itemCheck = false;
                }
                if ( !$itemCheck ) {
                    // ItemNameKj, UnitPrice, ItemNum のいずれかにエラーがある場合
                    $this->_response->addMessage("E" . $this->_serviceId . "203", "商品情報 ( $idx 番目 ) : 入力データが不正です");
                    $result = false;
                }
                else {
                    // 全て入力されている場合は金額加算
                    $lineSum = (int)$item[ServiceOrderModItemsConst::$UnitPrice->name] * $item[ServiceOrderModItemsConst::$ItemNum->name];
                    if ($useAmountFractionClass == 0) { $lineSum = floor( $lineSum ); }
                    if ($useAmountFractionClass == 1) { $lineSum = round( $lineSum ); }
                    if ($useAmountFractionClass == 2) { $lineSum = ceil(  $lineSum ); }
                    $sumMount += $lineSum;

                    $itemCount++;
                }
            }
        }

        // 送料、手数料、外税額
        $result = $result & $this->_validation( $order, ServiceOrderModOrderConst::$ItemCarriage );
        $result = $result & $this->_validation( $order, ServiceOrderModOrderConst::$ItemCharge );
        $result = $result & $this->_validation( $order, ServiceOrderModOrderConst::$ItemOutsideTaxs );
        if ( $result ) {
            if ( !empty($order[ServiceOrderModOrderConst::$ItemCarriage->name]) ) {
                $sumMount += (int)($order[ServiceOrderModOrderConst::$ItemCarriage->name]);
            }
            if ( !empty($order[ServiceOrderModOrderConst::$ItemCharge->name]) ) {
                $sumMount += (int)($order[ServiceOrderModOrderConst::$ItemCharge->name]);
            }
            if ( !empty($order[ServiceOrderModOrderConst::$ItemOutsideTaxs->name]) ) {
                $sumMount += (int)($order[ServiceOrderModOrderConst::$ItemOutsideTaxs->name]);
            }

        }

        // 商品明細件数チェック
        if ( $result &&  $itemCount == 0 ) {
            $this->_response->addMessage("E" . $this->_serviceId . "302", "商品情報 : 商品情報が存在しません");
            $result = false;
        }

        // 請求金額チェック
        if ( $result && ($sumMount != $order[ServiceOrderModOrderConst::$UseAmount->name])) {
            $this->_response->addMessage("E" . $this->_serviceId . "303", "請求金額合計 : 請求金額が誤っています");
            Application::getInstance()->logger->debug("計算結果 : $sumMount ／ 入力データ : " . $order[ServiceOrderModOrderConst::$UseAmount->name] );
            $result = false;
        }
        // 口座振替利用チェック（受付事業者が口座振替を利用する＋口座振替利用0,1,2以外＝エラー）
        if ($creditTransferFlg == "1" && !preg_match('/^[0-2]$/', $order[ServiceOrderModOrderConst::$CreditTransferRequestFlg->name]))
        {
            $this->_response->addMessage("E" . $this->_serviceId . "309", "口座振替利用フラグ：入力データが不正です");
            $result = false;
        }
        if ($creditTransferFlg == "2" && !preg_match('/^[0-2]$/', $order[ServiceOrderModOrderConst::$CreditTransferRequestFlg->name]))
        {
            $this->_response->addMessage("E" . $this->_serviceId . "309", "口座振替利用フラグ：入力データが不正です");
            $result = false;
        }
        if ($creditTransferFlg == "3" && !preg_match('/^[0-2]$/', $order[ServiceOrderModOrderConst::$CreditTransferRequestFlg->name]))
        {
            $this->_response->addMessage("E" . $this->_serviceId . "309", "口座振替利用フラグ：入力データが不正です");
            $result = false;
        }
        // 口座振替利用チェック（口座振替する＋WEB申込＋メールアドレスなし＝エラー）
        if (($creditTransferFlg == "1")
        && ($order[ServiceOrderModOrderConst::$CreditTransferRequestFlg->name] == "1")
        && (empty($customer[ServiceOrderModCustomerConst::$MailAddress->name]))
        )
        {
            $this->_response->addMessage("E" . $this->_serviceId . "308", "メールアドレス : メールアドレスを空にすることはできません");
            $result = false;
        }
        if (($creditTransferFlg == "2")
            && ($order[ServiceOrderModOrderConst::$CreditTransferRequestFlg->name] == "1")
            && (empty($customer[ServiceOrderModCustomerConst::$MailAddress->name]))
        )
        {
            $this->_response->addMessage("E" . $this->_serviceId . "308", "メールアドレス : メールアドレスを空にすることはできません");
            $result = false;
        }
        if (($creditTransferFlg == "3")
            && ($order[ServiceOrderModOrderConst::$CreditTransferRequestFlg->name] == "1")
            && (empty($customer[ServiceOrderModCustomerConst::$MailAddress->name]))
        )
        {
            $this->_response->addMessage("E" . $this->_serviceId . "308", "メールアドレス : メールアドレスを空にすることはできません");
            $result = false;
        }

        return $result;
    }

    /**
     * (non-PHPdoc)
     * @see api/classes/Service/ServiceAbstract#exec()
     */
    protected function exec() {
        $db = Application::getInstance()->dbAdapter;
        $logic = new LogicOrderRegister( $db );

        // 最終整形処理 - OEM任意注文番号 → 空の場合はキーを削除してnullが入るようにする
        if(!strlen($this->_data[ServiceOrderModConst::ORDER][ServiceOrderModOrderConst::$Oem_OrderId->name])) {
            unset($this->_data[ServiceOrderModConst::ORDER][ServiceOrderModOrderConst::$Oem_OrderId->name]);
        }

        // APIユーザーID
        $mdluser = new TableUser($this->_db);
        $opId = $mdluser->getUserId(3, $this->_apiUserId);

        $this->_data[ServiceOrderModConst::ORDER][ServiceOrderModOrderConst::$UpdateId->name] = $opId;
        $this->_data[ServiceOrderModConst::CUSTOMER][ServiceOrderModCustomerConst::$UpdateId->name] = $opId;
        $this->_data[ServiceOrderModConst::DESTINATION][ServiceOrderModDestinationConst::$RegistId->name] = $opId;
        $this->_data[ServiceOrderModConst::DESTINATION][ServiceOrderModDestinationConst::$UpdateId->name] = $opId;
        foreach ($this->_data[ServiceOrderModConst::ITEMS] as $idx => $item)
        {
            $this->_data[ServiceOrderModConst::ITEMS][$idx][ServiceOrderModItemsConst::$RegistId->name] = $opId;
            $this->_data[ServiceOrderModConst::ITEMS][$idx][ServiceOrderModItemsConst::$UpdateId->name] = $opId;
            // 単価と数量が設定されていたら金額を設定
            if( isset( $this->_data[ServiceOrderModConst::ITEMS][$idx][ServiceOrderModItemsConst::$UnitPrice->name] )
            &&  isset( $this->_data[ServiceOrderModConst::ITEMS][$idx][ServiceOrderModItemsConst::$ItemNum->name] ) ) {
                $this->_data[ServiceOrderModConst::ITEMS][$idx][ServiceOrderModItemsConst::$SumMoney->name] =
                    ((int)$this->_data[ServiceOrderModConst::ITEMS][$idx][ServiceOrderModItemsConst::$UnitPrice->name])
                        * ((int)$this->_data[ServiceOrderModConst::ITEMS][$idx][ServiceOrderModItemsConst::$ItemNum->name]);
            }
        }

        // 注文API取得
        $mdloApi = new TableOrder($this->_db);
        $orderDataApi = $mdloApi->findOrder(
        array('OrderId'      => $this->_data[ServiceOrderModConst::ORDER][ServiceOrderModOrderConst::$OrderId->name]
                ,'EnterpriseId' => $this->_data[ServiceOrderModConst::ORDER][ServiceOrderModOrderConst::$EnterpriseId->name]
        )
        )->current();
        $this->_data[ServiceOrderModConst::ORDER][ServiceOrderModOrderConst::$ApiUserId->name] = $orderDataApi['ApiUserId'];

        // 与信実行判定
        $judgeAuthExec = $logic->judgeAuthExec($this->_data);

        // 注文登録処理呼び出し
        $datastatus = $judgeAuthExec ? 12 : 0; // API注文時はDataStatusを12で登録する
        $this->_response->systemOrderId = $logic->modify( $this->_data,  $datastatus);

        // 登録されたOrderIdからOrderSeqを逆引き（2009.08.31 eda）
        $seq = $logic->getOrderSeqByOrderId($this->_response->systemOrderId);

        // 与信結果用(注文状況)
        $stsMap = ServiceResponseStatus::getStatusMap();

        if ($judgeAuthExec) {
            // 与信ロジック初期化
            LogicCreditJudgeSequencer::setUserId($opId);
            $judge_logic = new LogicCreditJudgeSequencer($db);

            // ロック向けにコールバックを設定
            $judge_logic
            // 審査システムへの情報登録時に呼び出されるコールバック → スレッドプールのロック獲得を試行
            ->setCallback(LogicCreditJudgeSequencer::CALLBACK_BEGIN_PREJUDGE, new BaseDelegate($this, 'onBeginSystemConnect'))

            // 審査システムスコアによる与信処理実行時に呼び出されるコールバック → onBeginSystemConnectでロック未獲得なら処理をスキップするよう制御
            ->setCallback(LogicCreditJudgeSequencer::CALLBACK_BEGIN_ILUSYS, new BaseDelegate($this, 'onBeginILuSys'))

            // 与信完了時に呼び出されるコールバック → 獲得したロックをリリース
            ->setCallback(LogicCreditJudgeSequencer::CALLBACK_END_JUDGEMENT, new BaseDelegate($this, 'onEndJudgement'));

            // 与信処理実行
            $result = $judge_logic->doJudgementForApi($seq);

            // 与信結果のディスパッチ
            $orderStatus = array(
                    'cd' => ServiceResponseStatus::STATUS_NOW_PROCESSING
            );
            switch($result) {
                case LogicCreditJudgeSequencer::RESULT_OK:
                    // 与信OK確定
                    $orderStatus['cd'] = ServiceResponseStatus::STATUS_INCRE_OK;
                    break;
                case LogicCreditJudgeSequencer::RESULT_NG:
                    // 与信NG確定
                    $orderStatus['cd'] = ServiceResponseStatus::STATUS_INCRE_NG;
                    break;
                default:
                    // その他の場合は与信中にする
                    $orderStatus['cd'] = ServiceResponseStatus::STATUS_NOW_PROCESSING;
                    break;
            }
            // レスポンス確定
            $orderStatus['msg'] = $stsMap[$orderStatus['cd']];
            $this->_response->orderStatus = $orderStatus;
        }
        else {
            // 現在の与信状態
            $orderStatus = array(
                    'cd' => ServiceResponseStatus::STATUS_NOW_PROCESSING
            );
            // 状態判定
            $ds = (int)$orderDataApi['DataStatus'];
            // 与信中
            if($ds < 31) {
                $orderStatus['cd'] = ServiceResponseStatus::STATUS_NOW_PROCESSING;
            }
            // 与信NG
            elseif($orderDataApi['DataStatus'] == 91 && $orderDataApi['CloseReason'] == 3) {
                $orderStatus['cd'] = ServiceResponseStatus::STATUS_INCRE_NG;
            }
            // 与信OK
            else {
                $orderStatus['cd'] = ServiceResponseStatus::STATUS_INCRE_OK;
            }
            // レスポンス確定
            $orderStatus['msg'] = $stsMap[$orderStatus['cd']];
            $this->_response->orderStatus = $orderStatus;
        }

        return true;
    }

    /**
     * (non-PHPdoc)
     * @see api/classes/Service/ServiceAbstract#serialize()
     */
    protected function returnResponse() {
        return $this->_response->serialize();
    }


    // 入力値検証用
    private $_coluSchemaCache;
    /**
     * T_CsvSchemaテーブルから検証ルールを取得する.
     * @param $ordinal
     * @return unknown_type
     */
    private function findColumnSchema( $ordinal ) {
        // 無効な値はnullを返却
        if( $ordinal < 0 ) return null;

        if( empty( $this->_coluSchemaCache ) ) {

            $mdlht = new TableTemplateHeader($this->_db);
            $mdlth = new TableTemplateField($this->_db);

            $templateSeq = $mdlht->getTemplateSeq('API009', 0, 0);

            $rs = new ResultSet();
            $schema = $rs->initialize($mdlth->get($templateSeq))->toArray();

            $this->_coluSchemaCache = array();
            foreach( $schema as $schema_row ) {
                $this->_coluSchemaCache[ $schema_row['ListNumber'] ] = $schema_row;
            }
        }

        return $this->_coluSchemaCache[ $ordinal ];
    }

    /**
     * 検証処理
     * @param array $dataArray
     *                    検証対象のデータが格納された連想配列
     * @param ServiceOrderSchemaMap $columnRule
     *                    カラムの検証ルール
     * @param boolean $addError
     *                    検証エラーをレスポンスに設定するか否か
     * @param int $oemId
     *                    OEM ID
     * @return bool 正常
     */
    private function _validation( &$dataArray, $columnRule, $addError = true, $oemId = 0) {
        $result = true;

        // 定義された正規表現で検証実行
        $schema = $this->findColumnSchema( $columnRule->ordinal );
        $value = $dataArray[$columnRule->name];

        // Payeasy判定
        $logicpayeasy = new LogicPayeasy($this->_db);
        $PayeasyFlg = $logicpayeasy->isPayeasyOem($oemId);

        // 値がブランクで任意項目かつペイジーの検証対象項目でない場合はチェック対象外
        if (( $value == null || $value == '') && !$columnRule->required && !($PayeasyFlg && $columnRule->name == 'NameKn')) {
            return true;
        }

        // 型チェック
        if ( !empty($schema) ) {
            $name = $schema['LogicalName'];

            if (! empty($schema['ValidationRegex']) ) {
                // 定義された正規表現で検証実行
                if( ! mb_ereg( preg_replace('/((^\/)|(\/[img]*$))/', '', $schema['ValidationRegex']), $value ) ) {
                    // 必須チェック
                    if( ( $columnRule->required || $schema['RequiredFlg'] ) && empty($value) ) {
                        if ( $addError ) $this->_response->addMessage("E" . $this->_serviceId . "201", "$name : データを0または空にすることはできません");
                        $result = false;
                    }
                    else {
                        if ( $addError ) $this->_response->addMessage("E" . $this->_serviceId . "202", "$name : 入力データが不正です");
                        $result = false;
                    }
                }
            }
            else {
                // 正規表現がない場合、必須チェックのみ実行
                // 必須チェック
                if( ( $columnRule->required || $schema['RequiredFlg'] ) && empty($value) ) {
                    if ( $addError ) $this->_response->addMessage("E" . $this->_serviceId . "201", "$name : データを0または空にすることはできません");
                    $result = false;
                }
            }
            //数量の検証
            //正規表現で0が通ってしまうため個別にチェック
            if( ($schema['PhysicalName'] == 'ItemNum') ) {
                if ($value == 0) {
                    if ( $addError ) $this->_response->addMessage("E" . $this->_serviceId . "201", "$name : データを0または空にすることはできません");
                    $result = false;
                }
                if (($oemId == LogicConstant::OEM_ID_SMBC) && ($value > 999)) {
                    if ( $addError ) $this->_response->addMessage("E" . $this->_serviceId . "201", "$name : データを3桁以上にすることはできません");
                    $result = false;
                }
            }
            //日付の妥当性を検証
            if( ($schema['PhysicalName'] == 'ReceiptOrderDate' || $schema['PhysicalName'] == 'ServiceExpectedDate')
            &&  !empty($value) ) {
                // 注文日のみ、日付としての妥当性を検証
                // 年、月または日が0の場合は無効、それ以外は有効と見なす (2008.02.22 Zend_Date::isDate()から変更)

                // 年月日の書式に[.][/]を認める(20151120)
                $value_original = $value;
                $value = str_replace('.', '-', $value);
                $value = str_replace('/', '-', $value);
                $parts = explode('-', $value);
                $partsCount = 0;
                if (!empty($parts)) {
                    $partsCount = count($parts);
                }
                if( $partsCount == 3 && \Coral\Base\Reflection\BaseReflectionUtility::isNumeric( $parts[0] ) && ((int)$parts[0]) < 1000 ) {
                    // 1000以下（＝1～3桁）の場合は2000年ベースに読み替える
                    $parts[0] = 2000 + ((int)$parts[0]);
                    $value = join('-', $parts);
                    $dataArray[$columnRule->name] = $value; // 引数値の置換
                }

                try {
                    // accept_languageによっては例外が出るので念のためフォーマット指定（2011.6.30 eda）
                    //                    $date = Zend_Locale_Format::getDate( $value, array('date_format' => 'yyyy-MM-dd') );
                    $date = array('year' => date('Y', strtotime($value)), 'month' => date('m', strtotime($value)), 'day' => date('d', strtotime($value)));
                } catch(\Exception $err) {
                    // 例外が発生したら指定データそのものが処理できないフォーマットと見なす（2011.6.30 eda）
                    $date = array('year' => 0, 'month' => 0, 'day' => 0);
                }
                if( ((int)$date['year']) == 0 || ((int)$date['month']) == 0 || ((int)$date['day']) == 0 ) {
                    if ( $addError ) $this->_response->addMessage("E" . $this->_serviceId . "204", "$name : '$value_original' は有効な日付データではありません");
                    $result = false;
                    //                } else if( ! Zend_Date::isDate( $value, 'yyyy-MM-dd' ) ) {
                } else if( ! IsValidDate( $value ) ) {
                    // 9/31などの実日付としての不正を検出できなかったため組み合わせ条件としてZend_Date::isDate復活（08.9.30）
                    if ( $addError ) $this->_response->addMessage("E" . $this->_serviceId . "204", "$name : '$value_original' は有効な日付データではありません");
                    $result = false;
                }
            }
        }
        // TCsvSchemaに定義が存在しない場合
        else if ( $columnRule->name == 'AnotherDeliFlg' ) {
            // 別配送先フラグ
            $name = "別配送先フラグ";
            if( !empty($value) && $value != "0" && $value != "1" ) {
                if ( $addError ) $this->_response->addMessage("E" . $this->_serviceId . "202", "$name : 入力データが不正です");
                $result = false;
            }
        }
        if($PayeasyFlg){
            if($columnRule->name == 'NameKj' && $result){
                $err = CoralValidateUtility::checkPeNameKj($value);
                if (isset($err)) {
                    if ( $addError ) $this->_response->addMessage("E" . $this->_serviceId . $err['error_code'], $err['subject'].$err['message']);
                    $result = false;
                }
            }
            if($columnRule->name == 'NameKn' && $result){
                $err = CoralValidateUtility::checkPeNameKn($value);
                if (isset($err)) {
                    if ( $addError ) $this->_response->addMessage("E" . $this->_serviceId . $err['error_code'], $err['subject'].$err['message']);
                    $result = false;
                }
            }
            if($columnRule->name == 'MailAddress' && $result){
                $err = CoralValidateUtility::checkPeMailAddress($value);
                if (isset($err)) {
                    if ( $addError ) $this->_response->addMessage("E" . $this->_serviceId . $err['error_code'], $err['subject'].$err['message']);
                    $result = false;
                }
            }
            if($columnRule->name == 'Phone' && $result){
                $err = CoralValidateUtility::checkPePhoneNumber($value);
                if (isset($err)) {
                    if ( $addError ) $this->_response->addMessage("E" . $this->_serviceId . $err['error_code'], $err['subject'].$err['message']);
                    $result = false;
                }
            }
        }

        // 文字列長チェック
        //   TCsvSchemaの正規表現で文字数チェックも行なわれているものもあるため、
        //   既にエラーとなっている場合はチェックしない。
        // 独自文字列長チェック廃止（2014.4.4 eda）
        // → これ以前の検証処理で必要な状況は担保できているため
        //if ( $result && $columnRule->maxLength > 0 && mb_strlen($value) > $columnRule->maxLength ) {
        //    if ( $addError ) $this->_response->addMessage("E" . $this->_serviceId . "202", "$name : 入力データが不正です");
        //    $result = false;
        //}

        // 独自文字列長チェック復帰（2014.7.18 eda）
        // → これまでT_CsvSchemaによる検証が行われない項目は数値項目のみだったため文字列長チェックは不要だったが、
        //   Oem_OrderIdが追加されたことによりスキーマ定義はないが文字列長チェックを行う必要が発生したための復帰。
        //   ServiceOrderSchemaMap::ordinalがNEED_LENCHK_ORDINAL_VALUEに一致する項目のみが対象
//         if($result && !$schema && $columnRule->maxLength > 0 && $columnRule->ordinal == ServiceOrderSchemaMap::NEED_LENCHK_ORDINAL_VALUE) {
//             if(mb_strlen($value) > $columnRule->maxLength) {
//                 if($addError) $this->_response->addMessage(sprintf('E%s202', $this->_serviceId), sprintf('%s : 入力データが不正です', $name));
//                 $result = false;
//             }
//         }

        return $result;
    }

    /**
     * このタスクで使用するスレッドプールを取得する
     *
     * @access protected
     * @retrun Logci_ThreadPool
     */
    protected function getThreadPool() {
        $loaded_options = Application::getInstance()->getThreadOptions('order');
        $groupName = nvl($loaded_options['group_name'], 'api-order-rest');

        $options = array();
        $options[LogicThreadPool::OPTION_DB_ADAPTER] = Application::getInstance()->dbAdapter;
        if(isset($loaded_options['thread_limit'])) {
            $options[LogicThreadPool::OPTION_THREAD_LIMIT] = $loaded_options['thread_limit'];
        }
        if(isset($loaded_options['lockwait_timeout'])) {
            $options[LogicThreadPool::OPTION_LOCKWAIT_TIMEOUT] = $loaded_options['lockwait_timeout'];
        }
        if(isset($loaded_options['lock_retry_interval'])) {
            $options[LogicThreadPool::OPTION_LOCK_RETRY_INTERVAL] = $loaded_options['lock_retry_interval'];
        }

        // スレッドプール初期化
        return LogicThreadPool::getPool($groupName, $options);
    }

    /**
     * ILU審査システム連携が開始された場合に呼び出されるコールバック
     *
     * @param LogicCreditJudgePrejudgeThread $prejudge
     * @param int $oseq
     * @return boolean スレッドプールからロックが獲得できなかった場合はfalse、それ以外はtrue
     */
    public function onBeginSystemConnect(LogicCreditJudgePrejudgeThread $prejudge, $oseq) {
        $logger = Application::getInstance()->logger;

        // ロックアイテムを初期化
        $this->_lockItem = null;
        /** @var LogicThreadPool */
        $pool = $this->getThreadPool();

        try {
            $this->_lockItem = $pool->open($this->_response->systemOrderId);
            $logger->debug(sprintf('%s : lock acquired. thread id = %s', $this->_response->systemOrderId, $this->_lockItem->getThreadId()));
        } catch(Exception $err) {
            if($this->_lockItem && $this->_lockItem->isAlive()) {
                try {
                    $this->_lockItem->abend($err->getMessage());
                } catch(Exception $innerError) {}
            }
            $this->_lockItem = null;
            $logger->info(sprintf('%s : lock acquire failed. reason = %s', $this->_response->systemOrderId, $err->getMessage()));

            // ロック獲得に失敗したので明示的にfalseを返す
            return false;
        }
        return true;
    }

    /**
     * ILU審査システム連携による与信が開始される場合に呼び出されるコールバック
     *
     * @param LogicCreditJudgeSequencer $sequencer
     * @param int $oseq
     * @return boolean onBeginSystemConnectでロック獲得に失敗していたらfalse、それ以外はtrue
     */
    public function onBeginILuSys(LogicCreditJudgeSequencer $sequencer, $oseq) {
        $logger = Application::getInstance()->logger;
        if(!$this->_lockItem || !$this->_lockItem->isAlive()) {
            $logger->debug(sprintf('%s : lock does not acquired.', $this->_response->systemOrderId));
            return false;
        }
        try {
            $this->_lockItem->processing();
        } catch(Exception $err) {}
        return true;
    }

    /**
     * 1件分の与信処理が完了した場合に呼び出されるコールバック。常にtrueを返す
     *
     * @param LogicCreditJudgeSequencer $sequencer
     * @param int $oseq
     * @return boolean 常にtrue
     */
    public function onEndJudgement(LogicCreditJudgeSequencer $sequencer, $oseq) {
        $logger = Application::getInstance()->logger;
        if($this->_lockItem && $this->_lockItem->isAlive()) {
            try {
                $this->_lockItem->terminate();
                $logger->debug(sprintf('%s : lock released normally.', $this->_response->systemOrderId));
            } catch(Exception $err) {
            }
        }
        $this->_lockItem = null;
        return true;
    }
}
