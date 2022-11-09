<?php
namespace api\classes\Service\Shipping;
/**
 * {@link ServiceShipping}向けの定数ホルダ
 */
class ServiceShippingConst {
    /** 事業者IDを指定するAPIパラメータキー @var string */
    const ENTERPRISE_ID = 'EnterpriseId';

    /** APIユーザIDを指定するAPIパラメータキー @var string */
    const API_USER_ID = 'ApiUserId';

    /** 注文IDを指定するAPIパラメータキー @var string */
    const ORDER_ID = 'OrderId';

    /** 配送会社IDを指定するAPIパラメータキー @var string */
    const DELIV_ID = 'DelivId';

    /** 伝票番号を指定するAPIパラメータキー @var string */
    const JOURNAL_NUMBER = 'JournalNum';

}
