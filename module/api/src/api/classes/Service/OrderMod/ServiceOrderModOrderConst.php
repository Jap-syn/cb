<?php
namespace api\classes\Service\OrderMod;

use api\classes\Service\OrderMod\ServiceOrderModSchemaMap;
use models\Logic\classes\LogicclassesOrderInputInfo;

/**
 *
 *
 */
class ServiceOrderModOrderConst {
    public static $EnterpriseId;
    public static $ApiUserId;
    public static $OrderId;
    public static $ReceiptOrderDate;
    public static $SiteId;
    public static $Ent_OrderId;
    public static $Ent_Note;
    public static $UseAmount;
    public static $ServiceExpectedDate;
    public static $ServiceTargetClass;
    public static $Oem_OrderId;
    public static $T_OrderAutoCreditJudgeClass;
    public static $AnotherDeliFlg;
    public static $ItemCarriage;
    public static $ItemCharge;
    public static $ItemOutsideTaxs;
    public static $RTOrderStatus;
    public static $SeparateShipment;
    public static $ClaimSendingClass;
    public static $UpdateId;
    public static $CreditTransferRequestFlg;
}
// 設定読み込み
ServiceOrderModSchemaMap::load("api\classes\Service\OrderMod\ServiceOrderModOrderConst", "order2");
