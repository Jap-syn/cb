<?php
namespace api\classes\Service\OrderMod;

use api\classes\Service\OrderMod\ServiceOrderModSchemaMap;
use models\Logic\classes\LogicclassesOrderInputInfo;

/**
 *
 *
 */
class ServiceOrderModDestinationConst {
    public static $PostalCode;
    public static $UnitingAddress;
    public static $DestNameKj;
    public static $DestNameKn;
    public static $Phone;
    public static $RegistId;
    public static $UpdateId;
}
// 設定読み込み
ServiceOrderModSchemaMap::load("api\classes\Service\OrderMod\ServiceOrderModDestinationConst", "destination");
