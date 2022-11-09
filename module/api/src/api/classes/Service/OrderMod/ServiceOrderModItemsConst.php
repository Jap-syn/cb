<?php
namespace api\classes\Service\OrderMod;

use api\classes\Service\OrderMod\ServiceOrderModSchemaMap;
use models\Logic\classes\LogicclassesOrderInputInfo;

/**
 *
 *
 */
class ServiceOrderModItemsConst {
    public static $ItemNameKj;
    public static $UnitPrice;
    public static $ItemNum;
    public static $SumMoney;
    public static $RegistId;
    public static $UpdateId;

    const KEY_ITEM_CARRIAGE = '_item_carriage';
    const KEY_ITEM_CHARGE   = '_item_charge';
    const KEY_ITEM_TAX   = '_item_outsidetax';
}
// 設定読み込み
ServiceOrderModSchemaMap::load("api\classes\Service\OrderMod\ServiceOrderModItemsConst", "item");
