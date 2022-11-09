<?php
namespace api\classes\Service;

use api\Application;
use api\classes\Service\ServiceAbstract;
use api\classes\Service\Response\ServiceResponseOrder;
use api\classes\Service\Response\ServiceResponseStatus;
use api\classes\Service\Status\ServiceStatusConst;
use api\classes\Service\Order\ServiceOrderConst;
use api\classes\Service\Order\ServiceOrderOrderConst;
use api\classes\Service\Order\ServiceOrderCustomerConst;
use api\classes\Service\Order\ServiceOrderDestinationConst;
use api\classes\Service\Order\ServiceOrderItemsConst;
use api\classes\Service\Order\ServiceOrderSchemaMap;
use Coral\Base\BaseDelegate;
use Coral\Base\BaseGeneralUtils;
use Coral\Coral\Validate\CoralValidateUtility;
use Coral\Coral\Mail\CoralMail;
use Coral\Coral\CoralValidate;
use models\Logic\LogicOrderRegister;
use models\Table\TableCsvSchema;
use models\Table\TableSite;
use models\Table\TableEnterprise;
use models\Logic\CreditJudge\LogicCreditJudgeSequencer;
use models\Logic\CreditJudge\LogicCreditJudgeAbstract;
use models\Logic\CreditJudge\LogicCreditJudgePrejudgeThread;
use models\Logic\LogicThreadPool;
use Zend\Db\ResultSet\ResultSet;
use models\Table\TableTemplateHeader;
use models\Table\TableTemplateField;
use models\Table\TableUser;
use models\Table\ATableOrder;
use models\Table\TablePostalCode;
use models\Table\TableSystemProperty;
use models\Table\TableOrder;
use models\Table\TableOrderNotClose;
use models\Logic\LogicPayeasy;
use models\Logic\LogicConstant;

/**
 * 注文登録サービスクラス
 */
class ServiceOrder extends ServiceAbstract {
    const CSVSCHEMA_CLASS = 1;

    /**
     * 注文登録APIのサービスID
     * @var string
     */
    protected $_serviceId = "00";

    /**
     * OrderStatusを返却するか
     * @var string
     */
    public $_rtOrderStatus;

    /**
     * ApiNewClassを返却するか
     * @var string
     */
    public $_rtApiNewClass;

    /**
     * スレッドロックアイテム
     *
     * @access protected
     * @var LogicThreadPoolItem
     */
    protected $_lockItem;

    /**
     * タイムアウト計測用処理開始時間
     * @var object
     */
    public $_actionStateTimestamp;

    /**
     * (non-PHPdoc)
     * @see api/classes/Service/ServiceAbstract#init()
     */
    protected function init() {

        // レスポンスを初期化
        $this->_response = new ServiceResponseOrder();

        // 任意注文ID
        $order = $this->_data[ServiceOrderConst::ORDER];
        $this->_response->orderId = $order[ServiceOrderOrderConst::$Ent_OrderId->name];
        // 注文ID (後払いシステム採番)
        $this->_response->systemOrderId = "";

        // 認証用
        $this->_apiUserId    = $order[ServiceOrderOrderConst::$ApiUserId->name];
        $this->_enterpriseId = $order[ServiceOrderOrderConst::$EnterpriseId->name];
        $this->_siteId       = $order[ServiceOrderOrderConst::$SiteId->name];

        // ログ出力
        Application::getInstance()->logger->info(
            get_class($this) . '#init() ' .
            "ReceiptOrderDate: " . $order[ServiceOrderOrderConst::$ReceiptOrderDate->name] . ", " .
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
        // 役務対象区分の設定
        $this->_data[ServiceOrderConst::ORDER][ServiceOrderOrderConst::$ServiceTargetClass->name]=$sedsiteInfo['ServiceTargetClass'];
        // テスト注文区分の設定
        if ( !empty($this->_data[ServiceOrderConst::ORDER][ServiceOrderOrderConst::$T_OrderClass->name])
        && $this->_data[ServiceOrderConst::ORDER][ServiceOrderOrderConst::$T_OrderClass->name] != 1 ) {
            $this->_data[ServiceOrderConst::ORDER][ServiceOrderOrderConst::$T_OrderClass->name] = 0;
        }
        // テスト注文自動与信審査区分の設定
        if ( !empty($this->_data[ServiceOrderConst::ORDER][ServiceOrderOrderConst::$T_OrderAutoCreditJudgeClass->name])
        &&   $this->_data[ServiceOrderConst::ORDER][ServiceOrderOrderConst::$T_OrderAutoCreditJudgeClass->name] != 0
        &&   $this->_data[ServiceOrderConst::ORDER][ServiceOrderOrderConst::$T_OrderAutoCreditJudgeClass->name] != 2 ) {
            $this->_data[ServiceOrderConst::ORDER][ServiceOrderOrderConst::$T_OrderAutoCreditJudgeClass->name] = 1;
        }
        // 請求書別送の設定
        if ( !empty($this->_data[ServiceOrderConst::ORDER][ServiceOrderOrderConst::$SeparateShipment->name])
        && $this->_data[ServiceOrderConst::ORDER][ServiceOrderOrderConst::$SeparateShipment->name] != 1 ) {
            $this->_data[ServiceOrderConst::ORDER][ServiceOrderOrderConst::$SeparateShipment->name] = 0;
        }
        // 外税額の設定
        if ( $enterpriseInfo['TaxClass'] != 1 ) {
            $this->_data[ServiceOrderConst::ORDER][ServiceOrderOrderConst::$ItemOutsideTaxs->name] = 0;
            $order[ServiceOrderOrderConst::$ItemOutsideTaxs->name] = 0;
        }
        // 請求書送付区分の設定
        if ( $enterpriseInfo['SelfBillingMode'] > 0 && $sedsiteInfo['SelfBillingFlg'] == 1){
            if ($this->_data[ServiceOrderConst::ORDER][ServiceOrderOrderConst::$SeparateShipment->name] == 1){
                $this->_data[ServiceOrderConst::ORDER][ServiceOrderOrderConst::$ClaimSendingClass->name] = 12;
            }else{
                $this->_data[ServiceOrderConst::ORDER][ServiceOrderOrderConst::$ClaimSendingClass->name] = 11;
            }
        }else{
            $this->_data[ServiceOrderConst::ORDER][ServiceOrderOrderConst::$ClaimSendingClass->name] = 21;
        }
        // 注文強制同梱化
        if ($enterpriseInfo['SelfBillingMode'] > 0 && $sedsiteInfo['SelfBillingFlg'] == 1 && $sedsiteInfo['SelfBillingFixFlg'] == 1 ) {
            $this->_data[ServiceOrderConst::ORDER][ServiceOrderOrderConst::$ClaimSendingClass->name] = 11;
        }

        // 基本情報内の送料、店舗手数料を商品にコピー
        $items = $this->_data[ServiceOrderConst::ITEMS];

        // 送料
        $items[ServiceOrderItemsConst::KEY_ITEM_CARRIAGE] = array(
            'DeliDestId'                                 => -1,            // 配送先ID
            'DataClass'                                  => 2,            // 送料
            ServiceOrderItemsConst::$ItemNameKj->name    => '送料',
            ServiceOrderItemsConst::$UnitPrice->name     =>                // データがない場合は0円と見なす
                                                            ((int)$order[ServiceOrderOrderConst::$ItemCarriage->name]),
            ServiceOrderItemsConst::$ItemNum->name       => 1
        );
        // 店舗手数料
        $items[ServiceOrderItemsConst::KEY_ITEM_CHARGE] = array(
            'DeliDestId'                                 => -1,            // 配送先ID
            'DataClass'                                  => 3,            // 手数料
            ServiceOrderItemsConst::$ItemNameKj->name    => '店舗手数料',
            ServiceOrderItemsConst::$UnitPrice->name     =>                // データがない場合は0円と見なす
                                                            ((int)$order[ServiceOrderOrderConst::$ItemCharge->name]),
            ServiceOrderItemsConst::$ItemNum->name       => 1
        );
        // 外税額
        if ( $enterpriseInfo['TaxClass'] == 1 ) {
            // 加盟店.税区分=1(外税)のみ作成
            $items[ServiceOrderItemsConst::KEY_ITEM_OUTSIDETAX] = array(
                    'DeliDestId'                                 => -1,            // 配送先ID
                    'DataClass'                                  => 4,             // 外税額
                    ServiceOrderItemsConst::$ItemNameKj->name    => '税額',
                    ServiceOrderItemsConst::$UnitPrice->name     =>                // データがない場合は0円と見なす
                                                                    ((int)$order[ServiceOrderOrderConst::$ItemOutsideTaxs->name]),
                    ServiceOrderItemsConst::$ItemNum->name       => 1
            );
        }
        // 口座振替申請
        if (( $enterpriseInfo['CreditTransferFlg'] != 1 ) && ( $enterpriseInfo['CreditTransferFlg'] != 2 ) && ( $enterpriseInfo['CreditTransferFlg'] != 3 )) {
            // 加盟店の設定で口座振替を利用しない場合は、強制的に 0:利用しない とする
            $this->_data[ServiceOrderConst::ORDER][ServiceOrderOrderConst::$CreditTransferRequestFlg->name] = 0;

        }

        $this->_data[ServiceOrderConst::ITEMS] = $items;
    }

    /**
     * (non-PHPdoc)
     * @see api/classes/Service/ServiceAbstract#check()
     */
    protected function check() {
        $result = true;

        // 基本情報の入力チェック
        $order = $this->_data[ServiceOrderConst::ORDER];
        $result = $result & $this->_validation( $order, ServiceOrderOrderConst::$ReceiptOrderDate );
        $result = $result & $this->_validation( $order, ServiceOrderOrderConst::$Ent_OrderId );
        $result = $result & $this->_validation( $order, ServiceOrderOrderConst::$Ent_Note );
        $result = $result & $this->_validation( $order, ServiceOrderOrderConst::$UseAmount );
        $result = $result & $this->_validation( $order, ServiceOrderOrderConst::$AnotherDeliFlg );
        $result = $result & $this->_validation( $order, ServiceOrderOrderConst::$Oem_OrderId );

        $mdlEnt = new TableEnterprise($this->_db);
        $oemid =  $mdlEnt->find($order[ServiceOrderOrderConst::$EnterpriseId->name])->current()['OemId'];
//        $logicpayeasy =new LogicPayeasy($this->_db);
//        $PayeasyFlg = $logicpayeasy->isPayeasyOem($oemid);

        // ご注文者（請求先）情報の入力チェック
        $customer = $this->_data[ServiceOrderConst::CUSTOMER];
        $result = $result & $this->_validation( $customer, ServiceOrderCustomerConst::$PostalCode );
        $result = $result & $this->_validation( $customer, ServiceOrderCustomerConst::$UnitingAddress );
        $result = $result & $this->_validation( $customer, ServiceOrderCustomerConst::$NameKj, true, $oemid );
        $result = $result & $this->_validation( $customer, ServiceOrderCustomerConst::$NameKn, true, $oemid );
        $result = $result & $this->_validation( $customer, ServiceOrderCustomerConst::$Phone, true, $oemid );
        $result = $result & $this->_validation( $customer, ServiceOrderCustomerConst::$MailAddress, true, $oemid );
        $result = $result & $this->_validation( $customer, ServiceOrderCustomerConst::$Occupation );

        // メールアドレスが必須なサイトでメールアドレス未指定
        $mdlsite = new TableSite($this->_db);
        $siteInfo = $mdlsite->findSite($this->_siteId)->current();
        if ( $siteInfo['ReqMailAddrFlg'] == 1 && empty($customer[ServiceOrderCustomerConst::$MailAddress->name]) ) {
            $this->_response->addMessage("E" . $this->_serviceId . "301", "メールアドレス : メールアドレスが必須なサイトとして登録されているため、メールアドレスを空にすることはできません");
            $result = false;
        }

        // 別配送先情報の入力チェック
        if ( $order[ServiceOrderOrderConst::$AnotherDeliFlg->name] == '1' ) {
            $dest = $this->_data[ServiceOrderConst::DESTINATION];
            $result = $result & $this->_validation( $dest, ServiceOrderDestinationConst::$PostalCode );
            $result = $result & $this->_validation( $dest, ServiceOrderDestinationConst::$UnitingAddress );
            $result = $result & $this->_validation( $dest, ServiceOrderDestinationConst::$DestNameKj );
            $result = $result & $this->_validation( $dest, ServiceOrderDestinationConst::$DestNameKn );
            $result = $result & $this->_validation( $dest, ServiceOrderDestinationConst::$Phone );
        }

        // 加盟店の[利用額端数計算設定]と[口座振替利用]取得
        $adapter = Application::getInstance()->dbAdapter;
        $row_ent = $adapter->query(" SELECT * FROM T_Enterprise WHERE EnterpriseId = :EnterpriseId ")->execute(array(':EnterpriseId' => $this->_enterpriseId))->current();
        $useAmountFractionClass = (int)$row_ent['UseAmountFractionClass'];
        $creditTransferFlg = $row_ent['CreditTransferFlg'];

        // 商品情報の入力チェック
        $sumMount = 0;
        $itemCount = 0;
        foreach ( $this->_data[ServiceOrderConst::ITEMS] as $idx => $item) {
            // 商品のみ対象
            if ( $item['DataClass'] == 1) {
                $itemCheck = true;
                // 組み合わせチェック (この時点ではレスポンスにエラー情報を設定しない)
                $itemCheck = $itemCheck && $this->_validation( $item, ServiceOrderItemsConst::$ItemNameKj, false );
                $itemCheck = $itemCheck && $this->_validation( $item, ServiceOrderItemsConst::$UnitPrice, false );
                $itemCheck = $itemCheck && $this->_validation( $item, ServiceOrderItemsConst::$ItemNum, false , $oemid);
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
                    $lineSum = (int)$item[ServiceOrderItemsConst::$UnitPrice->name] * $item[ServiceOrderItemsConst::$ItemNum->name];
                    if ($useAmountFractionClass == 0) { $lineSum = floor( $lineSum ); }
                    if ($useAmountFractionClass == 1) { $lineSum = round( $lineSum ); }
                    if ($useAmountFractionClass == 2) { $lineSum = ceil(  $lineSum ); }
                    $sumMount += $lineSum;

                    $itemCount++;
                }
            }
        }

        // 送料、手数料
        $result = $result & $this->_validation( $order, ServiceOrderOrderConst::$ItemCarriage );
        $result = $result & $this->_validation( $order, ServiceOrderOrderConst::$ItemCharge );
        if ( $result ) {
            if ( !empty($order[ServiceOrderOrderConst::$ItemCarriage->name]) ) {
                $sumMount += (int)($order[ServiceOrderOrderConst::$ItemCarriage->name]);
            }
            if ( !empty($order[ServiceOrderOrderConst::$ItemCharge->name]) ) {
                $sumMount += (int)($order[ServiceOrderOrderConst::$ItemCharge->name]);
            }
        }

        // 商品明細件数チェック
        if ( $result &&  $itemCount == 0 ) {
            $this->_response->addMessage("E" . $this->_serviceId . "302", "商品情報 : 商品情報が存在しません");
            $result = false;
        }

        // 追加項目の入力チェック
        // 役務提供予定日のチェック
        if ( $order[ServiceOrderOrderConst::$ServiceTargetClass->name] == 1 ) {
            // 役務サイト

            // 必須チェック
            if ( empty($order[ServiceOrderOrderConst::$ServiceExpectedDate->name]) ) {
                $this->_response->addMessage("E" . $this->_serviceId . "201", "役務提供予定日 : データを0または空にすることはできません");
                $result = false;
            }

            // 日付形式チェック
            $isdate = $this->_validation( $order, ServiceOrderOrderConst::$ServiceExpectedDate );
            $result = $result & $isdate;

            // 過去日チェック
            if ( $isdate ) {
                $diffDate = BaseGeneralUtils::CalcSpanDays(date('Y-m-d', strtotime($order[ServiceOrderOrderConst::$ServiceExpectedDate->name])), date('Y-m-d'));
                if ( $diffDate >= 30 ) {
                    $this->_response->addMessage("E" . $this->_serviceId . "304", "役務提供予定日 : 過去日を指定することはできません");
                    $result = false;
                }
            }
        }
        else if ( $order[ServiceOrderOrderConst::$ServiceTargetClass->name] == 0 ) {
            // 物販サイト

            // 設定がある場合エラー
            if ( !empty($order[ServiceOrderOrderConst::$ServiceExpectedDate->name]) ) {
                $this->_response->addMessage("E" . $this->_serviceId . "305", "役務提供予定日 : 受付サイトが役務対象でないため、役務提供予定日は設定できません");
                $result = false;
            }
        }
        $result = $result & $this->_validation( $order, ServiceOrderOrderConst::$T_OrderClass );
        $result = $result & $this->_validation( $order, ServiceOrderOrderConst::$T_OrderAutoCreditJudgeClass );
        $result = $result & $this->_validation( $customer, ServiceOrderCustomerConst::$EntCustId );
        $result = $result & $this->_validation( $customer, ServiceOrderCustomerConst::$CorporateName );
        $result = $result & $this->_validation( $customer, ServiceOrderCustomerConst::$DivisionName );
        $result = $result & $this->_validation( $customer, ServiceOrderCustomerConst::$CpNameKj );
        $result = $result & $this->_validation( $order, ServiceOrderOrderConst::$SeparateShipment );
        $result = $result & $this->_validation( $order, ServiceOrderOrderConst::$ItemOutsideTaxs );
        if ( $result ) {
            if ( !empty($order[ServiceOrderOrderConst::$ItemOutsideTaxs->name]) ) {
                $sumMount += (int)($order[ServiceOrderOrderConst::$ItemOutsideTaxs->name]);
            }
        }

        // 請求金額チェック
        if ( $result && ($sumMount != $order[ServiceOrderOrderConst::$UseAmount->name])) {
            $this->_response->addMessage("E" . $this->_serviceId . "303", "請求金額合計 : 請求金額が誤っています");
            Application::getInstance()->logger->debug("計算結果 : $sumMount ／ 入力データ : " . $order[ServiceOrderOrderConst::$UseAmount->name] );
            $result = false;
        }

        // 加盟店別のチェック
        $mdlpc = new TablePostalCode($this->_db);
        // 期間外注文チェック
        if ( $siteInfo['OutOfTermcheck'] == 1 ) {
            // 注文登録標準期間日数、の取得
            $obj = new TableSystemProperty($this->_db);
            // (過去日:デフォルト 60日)
            $daysPast   = (int)$obj->getValue('[DEFAULT]', 'systeminfo', 'OrderDefaultDaysPast'  );
            $daysPast   = ($daysPast > 0) ? $daysPast : 60;
            // (未来日:デフォルト180日)
            $daysFuture = (int)$obj->getValue('[DEFAULT]', 'systeminfo', 'OrderDefaultDaysFuture');
            $daysFuture = ($daysFuture > 0) ? $daysFuture : 180;

            $today = date('Y-m-d');
            if (date('Y-m-d') < $order[ServiceOrderOrderConst::$ReceiptOrderDate->name]) {
                // 未来日が指定されている時
                $diffDate = BaseGeneralUtils::CalcSpanDays($today, $order[ServiceOrderOrderConst::$ReceiptOrderDate->name]);
                if ($daysFuture <= $diffDate) {
                    $this->_response->addMessage("E" . $this->_serviceId . "306", "注文日：受付可能期間外の日付が指定されています");
                    $result = false;
                }
            }
            else if (date('Y-m-d') > $order[ServiceOrderOrderConst::$ReceiptOrderDate->name]) {
                // 過去日が指定されている時
                $diffDate = BaseGeneralUtils::CalcSpanDays($order[ServiceOrderOrderConst::$ReceiptOrderDate->name], $today);
                if ($daysPast <= $diffDate) {
                    $this->_response->addMessage("E" . $this->_serviceId . "306", "注文日：受付可能期間外の日付が指定されています");
                    $result = false;
                }
            }
        }
        // 電話番号不備チェック
        if ( $siteInfo['Telcheck'] == 1 ) {
            if ( strlen( str_replace( '-', '', $customer[ServiceOrderCustomerConst::$Phone->name] )) < 10 ) {
                $schema = $this->findColumnSchema(ServiceOrderCustomerConst::$Phone->ordinal );
                $name = $schema['LogicalName'];
                $this->_response->addMessage("E" . $this->_serviceId . "202", $name . "：入力データが不正です");
                $result = false;
            }
            if ( $order[ServiceOrderOrderConst::$AnotherDeliFlg->name] == '1' ) {
                if ( strlen( str_replace( '-', '', $dest[ServiceOrderDestinationConst::$Phone->name] )) < 10 ) {
                    $schema = $this->findColumnSchema(ServiceOrderDestinationConst::$Phone->ordinal );
                    $name = $schema['LogicalName'];
                    $this->_response->addMessage("E" . $this->_serviceId . "202", $name . "：入力データが不正です");
                    $result = false;
                }
            }
        }
        // 住所不備チェック
        if ( $siteInfo['Addresscheck'] == 1 ) {
            if ($mdlpc->isPerfectMatchPostAddressKanji($customer[ServiceOrderCustomerConst::$PostalCode->name], $customer[ServiceOrderCustomerConst::$UnitingAddress->name])) {
                $schema = $this->findColumnSchema(ServiceOrderCustomerConst::$UnitingAddress->ordinal );
                $name = $schema['LogicalName'];
                $this->_response->addMessage("E" . $this->_serviceId . "307", $name . "：番地が入力されていません");
                $result = false;
            }
            if ( $order[ServiceOrderOrderConst::$AnotherDeliFlg->name] == '1' ) {
                if ($mdlpc->isPerfectMatchPostAddressKanji($dest[ServiceOrderDestinationConst::$PostalCode->name], $dest[ServiceOrderDestinationConst::$UnitingAddress->name])) {
                    $schema = $this->findColumnSchema(ServiceOrderDestinationConst::$UnitingAddress->ordinal );
                    $name = $schema['LogicalName'];
                    $this->_response->addMessage("E" . $this->_serviceId . "307", $name . "：番地が入力されていません");
                    $result = false;
                }
            }
        }
        // 郵便番号不備チェック
        if ( $siteInfo['PostalCodecheck'] == 1 ) {
            $datas = $mdlpc->findPostalCode7($customer[ServiceOrderCustomerConst::$PostalCode->name]);
            if (empty($datas)) {
                $schema = $this->findColumnSchema(ServiceOrderCustomerConst::$PostalCode->ordinal );
                $name = $schema['LogicalName'];
                $this->_response->addMessage("E" . $this->_serviceId . "308", $name . "：不明な郵便番号が指定されています");
                $result = false;
            }
            if ( $order[ServiceOrderOrderConst::$AnotherDeliFlg->name] == '1' ) {
                $datas = $mdlpc->findPostalCode7($dest[ServiceOrderDestinationConst::$PostalCode->name]);
                if (empty($datas)) {
                    $schema = $this->findColumnSchema(ServiceOrderDestinationConst::$PostalCode->ordinal );
                    $name = $schema['LogicalName'];
                    $this->_response->addMessage("E" . $this->_serviceId . "308", $name . "：不明な郵便番号が指定されています");
                    $result = false;
                }
            }
        }
        // 任意注文番号チェック
        if ( $siteInfo['Ent_OrderIdcheck'] == 1 && nvl($order[ServiceOrderOrderConst::$Ent_OrderId->name], "") <> "") {
            $mdo = new TableOrder($this->_db);
            $datas = ResultInterfaceToArray($mdo->findOrder(array('EnterpriseId' => $order[ServiceOrderOrderConst::$EnterpriseId->name], 'Ent_OrderId' => $order[ServiceOrderOrderConst::$Ent_OrderId->name])));
            if (!empty($datas)) {
                $this->_response->addMessage("E" . $this->_serviceId . "309", "任意注文番号：任意注文番号を重複して登録することは出来ません");
                $result = false;
            }
        }
        // 口座振替利用チェック（受付事業者が口座振替を利用する＋口座振替利用0,1,2以外＝エラー）
        if ($creditTransferFlg == "1" && !preg_match('/^[0-2]$/', $order[ServiceOrderOrderConst::$CreditTransferRequestFlg->name]))
        {
            $this->_response->addMessage("E" . $this->_serviceId . "311", "口座振替利用フラグ：入力データが不正です");
            $result = false;
        }
        if ($creditTransferFlg == "2" && !preg_match('/^[0-2]$/', $order[ServiceOrderOrderConst::$CreditTransferRequestFlg->name]))
        {
            $this->_response->addMessage("E" . $this->_serviceId . "311", "口座振替利用フラグ：入力データが不正です");
            $result = false;
        }
        if ($creditTransferFlg == "3" && !preg_match('/^[0-2]$/', $order[ServiceOrderOrderConst::$CreditTransferRequestFlg->name]))
        {
            $this->_response->addMessage("E" . $this->_serviceId . "311", "口座振替利用フラグ：入力データが不正です");
            $result = false;
        }
        // 口座振替利用チェック（口座振替する＋WEB申込＋メールアドレスなし＝エラー）
        if (($creditTransferFlg == "1")
         && ($order[ServiceOrderOrderConst::$CreditTransferRequestFlg->name] == "1")
         && (empty($customer[ServiceOrderCustomerConst::$MailAddress->name]))
           )
        {
            $this->_response->addMessage("E" . $this->_serviceId . "310", "メールアドレス : メールアドレスを空にすることはできません");
            $result = false;
        }
        if (($creditTransferFlg == "2")
            && ($order[ServiceOrderOrderConst::$CreditTransferRequestFlg->name] == "1")
            && (empty($customer[ServiceOrderCustomerConst::$MailAddress->name]))
        )
        {
            $this->_response->addMessage("E" . $this->_serviceId . "310", "メールアドレス : メールアドレスを空にすることはできません");
            $result = false;
        }
        if (($creditTransferFlg == "3")
            && ($order[ServiceOrderOrderConst::$CreditTransferRequestFlg->name] == "1")
            && (empty($customer[ServiceOrderCustomerConst::$MailAddress->name]))
        )
        {
            $this->_response->addMessage("E" . $this->_serviceId . "310", "メールアドレス : メールアドレスを空にすることはできません");
            $result = false;
        }

        return $result;
    }

    /**
     * (non-PHPdoc)
     * @see api/classes/Service/ServiceAbstract#exec()
     */
    protected function exec() {
Application::getInstance()->logger->debug('exec_0');
        $db = Application::getInstance()->dbAdapter;
        $logic = new LogicOrderRegister( $db );
Application::getInstance()->logger->debug('exec_1');
        // 最終整形処理 - OEM任意注文番号 → 空の場合はキーを削除してnullが入るようにする
        if(!strlen($this->_data[ServiceOrderConst::ORDER][ServiceOrderOrderConst::$Oem_OrderId->name])) {
            unset($this->_data[ServiceOrderConst::ORDER][ServiceOrderOrderConst::$Oem_OrderId->name]);
        }

        // APIユーザーID
        $mdluser = new TableUser($this->_db);
        $opId = $mdluser->getUserId(3, $this->_apiUserId);
Application::getInstance()->logger->debug('exec_2');
        $this->_data[ServiceOrderConst::ORDER][ServiceOrderOrderConst::$RegistId->name] = $opId;
        $this->_data[ServiceOrderConst::ORDER][ServiceOrderOrderConst::$UpdateId->name] = $opId;
        $this->_data[ServiceOrderConst::CUSTOMER][ServiceOrderCustomerConst::$RegistId->name] = $opId;
        $this->_data[ServiceOrderConst::CUSTOMER][ServiceOrderCustomerConst::$UpdateId->name] = $opId;
        $this->_data[ServiceOrderConst::DESTINATION][ServiceOrderDestinationConst::$RegistId->name] = $opId;
        $this->_data[ServiceOrderConst::DESTINATION][ServiceOrderDestinationConst::$UpdateId->name] = $opId;
        foreach ($this->_data[ServiceOrderConst::ITEMS] as $idx => $item)
        {
            $this->_data[ServiceOrderConst::ITEMS][$idx][ServiceOrderItemsConst::$RegistId->name] = $opId;
            $this->_data[ServiceOrderConst::ITEMS][$idx][ServiceOrderItemsConst::$UpdateId->name] = $opId;
            // 単価と数量が設定されていたら金額を設定
            if( isset( $this->_data[ServiceOrderConst::ITEMS][$idx][ServiceOrderItemsConst::$UnitPrice->name] )
            &&  isset( $this->_data[ServiceOrderConst::ITEMS][$idx][ServiceOrderItemsConst::$ItemNum->name] ) ) {
                $this->_data[ServiceOrderConst::ITEMS][$idx][ServiceOrderItemsConst::$SumMoney->name] = ((int)$this->_data[ServiceOrderConst::ITEMS][$idx][ServiceOrderItemsConst::$UnitPrice->name])
                                                                                                      * ((int)$this->_data[ServiceOrderConst::ITEMS][$idx][ServiceOrderItemsConst::$ItemNum->name]);
            }
        }
Application::getInstance()->logger->debug('exec_3');
        // 注文登録処理呼び出し
        $datastatus = 12; // API注文時はDataStatusを12で登録する
        $this->_response->systemOrderId = $logic->register( $this->_data,  $datastatus);
Application::getInstance()->logger->debug('exec_4');
        // 登録されたOrderIdからOrderSeqを逆引き（2009.08.31 eda）
        $seq = $logic->getOrderSeqByOrderId($this->_response->systemOrderId);
Application::getInstance()->logger->debug('exec_5');
        // 親注文SEQを[OrderSeq同値]で更新
        $this->_db->query(" UPDATE T_Order SET P_OrderSeq = :OrderSeq WHERE OrderSeq = :OrderSeq ")->execute(array(':OrderSeq' => $seq));
Application::getInstance()->logger->debug('exec_6');
        // 2015/09/18 Y.Suzuki Add 会計対応 Stt
        // 会計用項目のINSERT
        $atOrderDate = array('OrderSeq' => $seq
                           , 'Dmg_DailySummaryFlg' => 0
                           , 'CreditTransferRequestFlg' => $this->_data[ServiceOrderConst::ORDER][ServiceOrderOrderConst::$CreditTransferRequestFlg->name]
        );

        $mdlato = new ATableOrder($this->_db);
        $mdlato->saveNew($atOrderDate);
        // 2015/09/18 Y.Suzuki Add 会計対応 End

Application::getInstance()->logger->debug('exec_7');
        // 未クローズ注文を登録する
        $tOrderNotCloseDate = array(
                'OrderSeq' => $seq
              , 'RegistId' => $opId
              , 'UpdateId' => $opId
        );
        $tOrderNotClose = new TableOrderNotClose($this->_db);
        $tOrderNotClose->saveNew($tOrderNotCloseDate);

Application::getInstance()->logger->debug('exec_8');
        // 与信ロジック初期化
        $stsMap = ServiceResponseStatus::getStatusMap();
        LogicCreditJudgeSequencer::setUserId($opId);
        $judge_logic = new LogicCreditJudgeSequencer($db);
Application::getInstance()->logger->debug('exec_9');
        // ロック向けにコールバックを設定
        $judge_logic
            // 審査システムへの情報登録時に呼び出されるコールバック → スレッドプールのロック獲得を試行
            ->setCallback(LogicCreditJudgeSequencer::CALLBACK_BEGIN_PREJUDGE, new BaseDelegate($this, 'onBeginSystemConnect'))

            // 審査システムスコアによる与信処理実行時に呼び出されるコールバック → onBeginSystemConnectでロック未獲得なら処理をスキップするよう制御
            ->setCallback(LogicCreditJudgeSequencer::CALLBACK_BEGIN_ILUSYS, new BaseDelegate($this, 'onBeginILuSys'))

            // 与信完了時に呼び出されるコールバック → 獲得したロックをリリース
            ->setCallback(LogicCreditJudgeSequencer::CALLBACK_END_JUDGEMENT, new BaseDelegate($this, 'onEndJudgement'));
Application::getInstance()->logger->debug('exec_10');
        // 与信処理実行
        $result = $judge_logic->doJudgementForApi($seq, $this->_actionStateTimestamp);
Application::getInstance()->logger->debug('exec_11');
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
Application::getInstance()->logger->debug('exec_12');
        // レスポンス確定
        $orderStatus['msg'] = $stsMap[$orderStatus['cd']];
        $this->_response->orderStatus = $orderStatus;
Application::getInstance()->logger->debug('exec_13');
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

            $templateSeq = $mdlht->getTemplateSeq('API005', 0, 0);

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
        $logicpayeasy =new LogicPayeasy($this->_db);
        $PayeasyFlg = $logicpayeasy->isPayeasyOem($oemId);

        // 値がブランクで任意項目の場合はチェック対象外 ペイジー対象の場合は追加検証のため対象外にしない
        if ( ( $value == null || $value == '')  && !$columnRule->required && !($PayeasyFlg && $columnRule->name == 'NameKn')) {
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
//             }
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
        $useServer = Application::getInstance()->nonUseThreadPoolServer();
        // 新サーバー
        if ($useServer){
            $groupName = 'asapi-order-rest';
        }
        // もともとの設定
        else {
            $groupName = nvl($loaded_options['group_name'], 'api-order-rest');
        }

        $options = array();
        $options[LogicThreadPool::OPTION_DB_ADAPTER] = Application::getInstance()->dbAdapter;
        // 上限は新サーバーでは設定しない(設定しなければデフォルト値:上限なし)
        if(isset($loaded_options['thread_limit']) && !$useServer) {
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
            $this->_lockItem = $pool->openApi($this->_response->systemOrderId);
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
