<?php
namespace api\classes\Service\Order;

use api\classes\Service\Order\ServiceOrderSchemaMap;
use models\Logic\classes\LogicclassesOrderInputInfo;

/**
 *
 *
 */
class ServiceOrderOrderConst {
    public static $ReceiptOrderDate;
    public static $EnterpriseId;
    public static $SiteId;
    public static $ApiUserId;
    public static $Ent_OrderId;
    public static $Ent_Note;
    public static $UseAmount;
    public static $AnotherDeliFlg;
    public static $ItemCarriage;
    public static $ItemCharge;
    public static $ItemOutsideTaxs;
    public static $Oem_OrderId;
    public static $ServiceTargetClass;
    public static $ServiceExpectedDate;
    public static $T_OrderClass;
    public static $T_OrderAutoCreditJudgeClass;
    public static $SeparateShipment;
    public static $ClaimSendingClass;
    public static $RegistId;
    public static $UpdateId;
    public static $CreditTransferRequestFlg;
}
// 設定読み込み
ServiceOrderSchemaMap::load("api\classes\Service\Order\ServiceOrderOrderConst", "order");
