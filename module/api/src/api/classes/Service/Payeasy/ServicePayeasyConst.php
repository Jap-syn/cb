<?php
namespace api\classes\Service\Payeasy;
/**
 * {@link ServicePayeasy}向けの定数ホルダ
 */
class ServicePayeasyConst {
    /** プロトコルバージョン @var string */
    const P_VER = 'p_ver';

    /** データ作成日 @var string */
    const STDATE = 'stdate';

    /** 加盟店取引番号 @var string */
    const STRAN = 'stran';

    /** 収納機関コード @var string */
    const BKCODE = 'bkcode';

    /** 加盟店コード @var string */
    const SHOPID = 'shopid';

    /** 加盟店サブコード @var string */
    const CSHOPID = 'cshopid';

    /** 取引金額 @var string */
    const AMOUNT = 'amount';

    /** 取引番号 @var string */
    const MBTRAN = 'mbtran';

    /** 収納機関受付番号 @var string */
    const BKTRANS = 'bktrans';

    /** 消込識別情報 @var string */
    const TRANID = 'tranid';

    /** 処理日付 @var string */
    const DDATE = 'ddate';

    /** 振込日(入金日) @var string */
    const TDATE = 'tdate';

    /** 結果コード @var string */
    const RSLTCD = 'rsltcd';

    /** ハッシュ値 @var string */
    const RCHKSUM = 'rchksum';

}
