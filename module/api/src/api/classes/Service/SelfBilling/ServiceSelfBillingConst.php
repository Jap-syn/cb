<?php
namespace api\classes\Service\SelfBilling;
/**
 * {@link ServiceSelfBilling}向けの定数ホルダ
 */
class ServiceSelfBillingConst {
    /** 事業者IDを指定するAPIパラメータキー @var string */
    const ENTERPRISE_ID = 'EnterpriseId';

    /** APIユーザIDを指定するAPIパラメータキー @var string */
    const API_USER_ID = 'ApiUserId';

    /** 同梱ユーザアクセスキー @var string */
    const ACCESS_TOKEN = 'AccessToken';

    /** アクション種別 @var string */
    const ACTION = 'Action';

    /** アクション種別 @var string */
    const PARAM = 'Param';

    /** 注文ID @var string */
    const ORDER_ID = 'OrderId';

    /** 同梱別送モード @var string */
    const MODE = 'Mode';

    /** 注文SEQ(Logic送信用） @var string */
    const ORDER_SEQ= 'Seq';

    /** バージョン（Logic送信用） @var string */
    const VERSION = 'Version';

    /** コマンド（Logic送信用） @var string */
    const COMMAND = 'Command';

}
