<?php
namespace models\Logic;

use Zend\Db\Adapter\Adapter;
use Zend\Config\Reader\Ini;
use Coral\Base\BaseLog;
use models\Table\TableSmbcRelationLog;
use models\Logic\SmbcRelation\Service\LogicSmbcRelationServiceAbstract;
use models\Logic\SmbcRelation\Service\LogicSmbcRelationServiceRegister;
use models\Logic\SmbcRelation\Service\LogicSmbcRelationServiceCancel;
use models\Logic\SmbcRelation\Service\LogicSmbcRelationServiceTestRegister;

/**
 * SMBC決済ステーション連携用ユーティリティ
 */
class LogicSmbcRelation {
    /**
     * 指定のフィールド名が指定の対象機能で有効な名前であるかを判断する
     *
     * @static
     * @param string $fieldName 検査するフィールド名
     * @param int $targetFuncCode 対象機能識別コード
     * @return boolean
     */
    public static function isValidFieldName($fieldName, $targetFuncCode) {
        return in_array($fieldName, self::getValidFieldsFor($targetFuncCode));
    }

    /**
     * 指定対象機能への送信データとして有効なフィールド名のリストを取得する
     *
     * @static
     * @param int $targetFuncCode 対象機能識別コード
     * @return array
     */
    public static function getValidFieldsFor($targetFuncCode) {
        $valid_fields = array();

        if($targetFuncCode == TableSmbcRelationLog::TARGET_FUNC_REGISTER) {
            // 請求情報登録向け
            $valid_fields = self::getValidFieldsForRegister();
        }
        if($targetFuncCode == TableSmbcRelationLog::TARGET_FUNC_CANCEL) {
            // 請求取消向け
            $valid_fields = self::getValidFieldsForCancel();
        }

        return $valid_fields;
    }

    /**
     * 請求情報登録の送信データで有効なフィールド名の一覧を取得する
     *
     * @static
     * @return array
     */
    public static function getValidFieldsForRegister() {
        return array(
            'version',
            'bill_method',
            'kessai_id',
            'shop_cd',
            'syuno_co_cd',
            'kyoten_cd',
            'shop_pwd',
            'shoporder_no',
            'seikyuu_kingaku',
            'shouhi_tax',
            'souryou',
            'bill_no',
            'bill_name',
            'bill_kana',
            'bill_zip',
            'bill_adr_1',
            'bill_adr_2',
            'bill_adr_3',
            'bill_adr_4',
            'bill_adr_5',
            'bill_phon',
            'bill_mail',
            'bill_mail_kbn',
            'msg_1',
            'msg_2',
            'seiyaku_date',
            'shiharai_date',
            'shiharai_time',
			'goods_name_1',
			'unit_price_1',
			'quantity_1',
			'goods_name_2',
			'unit_price_2',
			'quantity_2',
			'goods_name_3',
			'unit_price_3',
			'quantity_3',
			'goods_name_4',
			'unit_price_4',
			'quantity_4',
			'goods_name_5',
			'unit_price_5',
			'quantity_5',
			'goods_name_6',
			'unit_price_6',
			'quantity_6',
			'goods_name_7',
			'unit_price_7',
			'quantity_7',
			'goods_name_8',
			'unit_price_8',
			'quantity_8',
			'goods_name_9',
			'unit_price_9',
			'quantity_9',
			'goods_name_10',
			'unit_price_10',
			'quantity_10',
			'goods_name_11',
			'unit_price_11',
			'quantity_11',
			'goods_name_12',
			'unit_price_12',
			'quantity_12',
			'goods_name_13',
			'unit_price_13',
			'quantity_13',
			'goods_name_14',
			'unit_price_14',
			'quantity_14',
			'goods_name_15',
			'unit_price_15',
			'quantity_15',
			'goods_name_16',
			'unit_price_16',
			'quantity_16',
			'goods_name_17',
			'unit_price_17',
			'quantity_17',
			'goods_name_18',
			'unit_price_18',
			'quantity_18',
			'goods_name_19',
			'unit_price_19',
			'quantity_19',
			'goods_name_20',
			'unit_price_20',
			'quantity_20',
            'seikyuu_name',
            'seikyuu_kana',
            'riyou_nengetsu',
            'seikyuu_nengetsu',
            'tokusoku_kbn',
            'tuuchisho_kbn',
            'card_no',
            'card_yukokigen',
            'shiharai_kbn',
            'shiharai_kinyukikan_cd',
            'hakkou_kbn',
            'yuusousaki_kbn'
        );
    }

    /**
     * 請求取消の送信データで有効なフィールド名の一覧を取得する
     *
     * @static
     * @return array
     */
    public static function getValidFieldsForCancel() {
        return array(
            'version',
            'bill_method',
            'kessai_id',
            'shop_cd',
            'syuno_co_cd',
            'kyoten_cd',
            'shop_pwd',
            'shoporder_no',
            'seikyuu_kingaku',
            'sku_msg_1',
            'sku_msg_2'
        );
    }

    /**
     * 連携サービスを初期化するための設定データ
     *
     * @static
     * @access private
     * @var array
     */
    private static $__defaultServiceConfig = null;

    /**
     * 連携サービスを初期化するためのデフォルトの設定データを取得する
     *
     * @static
     * @access private
     * @return array
     */
    private static function __getDefaultConfig() {
        if(self::$__defaultServiceConfig == null) {
            self::$__defaultServiceConfig = array(
                    LogicSmbcRelationServiceAbstract::OPT_SERVICE_BASE_URL       => 'https://www.paymentstation.jp/cooperation',
                    LogicSmbcRelationServiceAbstract::OPT_SERVICE_TEXT_ENC       => 'sjis-win',
                    LogicSmbcRelationServiceAbstract::OPT_SERVICE_REQ_TIMEOUT    => 10,
                    LogicSmbcRelationServiceAbstract::OPT_SERVICE_REQ_RETRY      => 2,
                    LogicSmbcRelationServiceAbstract::OPT_SERVICE_ADAPTER        => 'Http',
                    LogicSmbcRelationServiceAbstract::OPT_SERVICE_INTERFACE_PATH => array(
                            'register' => 'sf/at/ksuketsukeinforeg/uketsukeInfoRegInit.do',
                            'cancel' => 'sf/cd/skuinfokt/skuinfoKakutei.do'
                    )
            );
        }
        return self::$__defaultServiceConfig;
    }

    /**
     * 指定のiniファイルの指定セクションを、連携サービス初期化設定としてロードする
     *
     * @static
     * @param array $ini 連携サービス設定値
     */
    public static function loadServiceConfig($ini) {
        $conf = self::__getDefaultConfig();
        if (isset($ini)) {
            $conf = array_merge($conf, $ini);
        }
        self::$__defaultServiceConfig = $conf;
    }

    /**
     * SMBC決済ステーション請求情報登録サービスを開く
     *
     * @static
     * @param Adapter $adapter アダプタ
     * @return LogicSmbcRelationServiceRegister
     */
    public static function openRegisterService(Adapter $adapter, BaseLog $logger = null) {
        $config = self::__getDefaultConfig();
        $config[LogicSmbcRelationServiceAbstract::OPT_SERVICE_INTERFACE_PATH] =
        $config[LogicSmbcRelationServiceAbstract::OPT_SERVICE_INTERFACE_PATH]['register'];

        $logic = new LogicSmbcRelationServiceRegister($adapter, $config);
        $logic->setLogger($logger);
        return $logic;
    }

    /**
     * SMBC決済ステーション請求取消サービスを開く
     *
     * @static
     * @param Adapter $adapter アダプタ
     * @return LogicSmbcRelationServiceCancel
     */
    public static function openCancelService(Adapter $adapter, BaseLog $logger = null) {
        $config = self::__getDefaultConfig();
        $config[LogicSmbcRelationServiceAbstract::OPT_SERVICE_INTERFACE_PATH] =
        $config[LogicSmbcRelationServiceAbstract::OPT_SERVICE_INTERFACE_PATH]['cancel'];

        $logic =  new LogicSmbcRelationServiceCancel($adapter, $config);
        $logic->setLogger($logger);
        return $logic;
    }

    /**
     * SMBC決済ステーション請求情報登録サービス（テスト用）を開く
     *
     * @static
     * @param Adapter $adapter アダプタ
     * @return LogicSmbcRelationServiceRegister
     */
    public static function openTestRegisterService(Adapter $adapter, BaseLog $logger = null) {
        $config = self::__getDefaultConfig();
        $config[LogicSmbcRelationServiceAbstract::OPT_SERVICE_INTERFACE_PATH] =
        $config[LogicSmbcRelationServiceAbstract::OPT_SERVICE_INTERFACE_PATH]['register'];

        $logic = new LogicSmbcRelationServiceTestRegister($adapter, $config);
        $logic->setLogger($logger);
        return $logic;
    }
}
