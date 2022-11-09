<?php
namespace api\classes\Service\Modify;
/**
 * {@link Service_Modify}向けの定数ホルダ
 */
class ServiceModifyConst {
    /** 事業者IDを指定するAPIパラメータキー @var string */
    const ENTERPRISE_ID = 'EnterpriseId';

    /** APIユーザIDを指定するAPIパラメータキー @var string */
    const API_USER_ID = 'ApiUserId';

    /** 注文IDを指定するAPIパラメータキー @var string */
    const ORDER_ID = 'OrderId';

    /** リクエストパラメータを指定するパラメータキー @var string*/
    const REQ_PARAMS = 'Request Parameters';

    /** パラメータグループキー：注文情報グループ @var string */
    const GROUP_ORDER = 'order';

    /** パラメータグループキー：購入者情報グループ @var string */
    const GROUP_CUSTOMER = 'customer';

    /** パラメータグループキー：配送先情報グループ @var string */
    const GROUP_DESTINATION = 'destination';

    /** パラメータグループキー：配送伝票情報グループ @var string */
    const GROUP_JOURNAL = 'journal';

    /** パラメータグループキー：商品明細情報グループ @var string */
    const GROUP_ITEMS = 'items';

}
