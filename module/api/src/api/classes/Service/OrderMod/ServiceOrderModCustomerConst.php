<?php
namespace api\classes\Service\OrderMod;

use api\classes\Service\OrderMod\ServiceOrderModSchemaMap;
use models\Logic\classes\LogicclassesOrderInputInfo;

/**
 *
 *
 */
class ServiceOrderModCustomerConst {
    public static $PostalCode;
    public static $UnitingAddress;
    public static $NameKj;
    public static $NameKn;
    public static $Phone;
    public static $MailAddress;
    public static $Occupation;
    public static $EntCustId;
    public static $CorporateName;
    public static $DivisionName;
    public static $CpNameKj;
    public static $UpdateId;

}
// 設定読み込み
ServiceOrderModSchemaMap::load("api\classes\Service\OrderMod\ServiceOrderModCustomerConst", "customer2");
