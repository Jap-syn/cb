<?php
namespace api\classes\Service\Order;

use api\classes\Service\Order\ServiceOrderSchemaMap;
use models\Logic\classes\LogicclassesOrderInputInfo;

/**
 *
 *
 */
class ServiceOrderDestinationConst {
    public static $PostalCode;
    public static $UnitingAddress;
    public static $DestNameKj;
    public static $DestNameKn;
    public static $Phone;
    public static $RegistId;
    public static $UpdateId;
}
// 設定読み込み
ServiceOrderSchemaMap::load("api\classes\Service\Order\ServiceOrderDestinationConst", "destination");
