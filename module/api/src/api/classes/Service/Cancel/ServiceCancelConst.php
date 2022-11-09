<?php
namespace api\classes\Service\Cancel;
/**
 * {@link Service_Cancel}向けの定数ホルダ
 */
class ServiceCancelConst {
    /** 事業者IDを指定するAPIパラメータキー @var string */
    const ENTERPRISE_ID = 'EnterpriseId';

    /** APIユーザIDを指定するAPIパラメータキー @var string */
    const API_USER_ID = 'ApiUserId';

    /** 注文IDを指定するAPIパラメータキー @var string */
    const ORDER_ID = 'OrderId';

    /** 退避する注文IDを指定するAPIパラメータキー @var string */
    const ORDER_ID_BK = 'OrderIdBk';

    /** キャンセル理由を指定するAPIパラメータキー @var string */
    const CANCEL_REASON = 'Reason';

    /** 注文IDとキャンセル理由を対で管理するパラメータリスト用キー @var string */
    const REQUEST_DATA = 'request';

    /** キャンセル理由につけるプレフィックス文字列 @var string */
    const CANCEL_REASON_PREFIX = '[API]';

}
