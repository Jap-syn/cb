<?php
namespace api\classes\Service\OrderMod;

use models\Logic\classes\LogicclassesOrderInputInfo;

/**
 * データ定義クラス.<br>
 */
class ServiceOrderModConst {
    const ORDER       = LogicclassesOrderInputInfo::ARRKEY_ORDER;
    const CUSTOMER    = LogicclassesOrderInputInfo::ARRKEY_CUSTO;
    const DESTINATION = LogicclassesOrderInputInfo::ARRKEY_DELIV;
    const ITEMS       = LogicclassesOrderInputInfo::ARRKEY_ITEMS;
}
