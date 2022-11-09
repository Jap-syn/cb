<?php
namespace api\classes\Service\Order;

use api\classes\Service\Order\ServiceOrderSchemaMap;
use models\Logic\classes\LogicclassesOrderInputInfo;

/**
 *
 *
 */
class ServiceOrderItemsConst {
    public static $ItemNameKj;
    public static $UnitPrice;
    public static $ItemNum;
    public static $SumMoney;
    public static $RegistId;
    public static $UpdateId;

    const KEY_ITEM_CARRIAGE = '_item_carriage';
    const KEY_ITEM_CHARGE   = '_item_charge';
    const KEY_ITEM_OUTSIDETAX   = '_item_outsidetax';
}
// 設定読み込み
ServiceOrderSchemaMap::load("api\classes\Service\Order\ServiceOrderItemsConst", "item");
