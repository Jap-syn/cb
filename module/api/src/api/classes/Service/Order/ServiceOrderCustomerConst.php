<?php
namespace api\classes\Service\Order;

use api\classes\Service\Order\ServiceOrderSchemaMap;
use models\Logic\classes\LogicclassesOrderInputInfo;

/**
 *
 *
 */
class ServiceOrderCustomerConst {
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
	public static $AddressKn;
	public static $RegistId;
	public static $UpdateId;
}
// 設定読み込み
ServiceOrderSchemaMap::load("api\classes\Service\Order\ServiceOrderCustomerConst", "customer");
