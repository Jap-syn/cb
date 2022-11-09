<?php
namespace api\classes\Service\Modify;

/**
 * 注文修正APIのリクエストパラメータを整形するユーティリティ
 */
class ServiceParameterUtility {
    /**
     * 注文情報グループ向けの有効なキー一覧を取得する
     * @static
     * @return array
     */
    public static function getOrderGroupKeys() {
        // TODO: 正式版では以下のコメントアウトを解除
        return array(
           'ReceiptOrderDate',
           'SiteId',
           'Ent_OrderId',
           'Ent_Note',
           'UseAmount'
        );
    }

    /**
     * 購入者情報グループ向けの有効なキー一覧を取得する
     * @static
     * @return array
     */
    public static function getCustomerGroupKeys() {
        // TODO: 正式版では以下のコメントアウトを解除
        return array(
           'NameKj',
           'NameKn',
           'PostalCode',
           'UnitingAddress',
           'Phone',
           'MailAddress'
        );
    }

    /**
     * 配送先情報グループ向けの有効なキー一覧を取得する
     * @static
     * @return array
     */
    public static function getDestinationGroupKeys() {
        // TODO: 正式版では以下のコメントアウトを解除
        return array(
           'DestNameKj',
           'DestnameKn',
           'PostalCode',
           'UnitingAddress',
           'Phone'
        );
    }

    /**
     * 配送伝票情報グループ向けの有効なキー一覧を取得する
     * @static
     * @return array
     */
    public static function getJournalGroupKeys() {
        // TODO: 正式版では以下のコメントアウトを解除
        return array(
           'JournalDate',
           'DelivId',
           'JournalNum'
        );
    }

    /**
     * 商品明細情報グループ向けの有効なキー一覧を取得する
     * @static
     * @return array
     */
    public static function getItemsGroupKeys() {
        // TODO: 正式版では以下のコメントアウトを解除
        return array(
           'UnitPrice',
           'ItemNameKj',
           'ItemNum'
        );
    }

    /**
     * 送料・店舗手数料向けのキー一覧を取得する（注文情報グループ向け）
     * @static
     * @return array
     */
    public static function getFeeGroupKeys() {
        // TODO: 正式版では以下のコメントアウトを解除
        return array(
           'ItemCarriage',
           'ItemCharge'
        );
    }

    /**
     * ServiceModifyParameterUtilityの新しいインスタンスを初期化する
     */
    public function __construct() {
    }

    /**
     * リクエストパラメータを格納した連想配列をすべて展開・整備する
     *
     * @param array $params リクエストパラメータ
     * @return array
     */
    public function parseAll(array $params = array()) {
        return array(
            ServiceModifyConst::GROUP_ORDER => $this->parseOrderGroup($params),
            ServiceModifyConst::GROUP_CUSTOMER => $this->parseCustomerGroup($params),
            ServiceModifyConst::GROUP_DESTINATION => $this->parseDestinationGroup($params),
            ServiceModifyConst::GROUP_JOURNAL => $this->parseJournalGroup($params),
            ServiceModifyConst::GROUP_ITEMS => $this->parseItemsGroup($params)
        );
    }

    /**
     * リクエストパラメータを展開し、注文情報グループのパラメータ配列を構築する
     *
     * @param array $params リクエストパラメータ
     * @return array
     */
    public function parseOrderGroup(array $params = array()) {
        // 注文情報グループのみ、O_プレフィックス項目に加えてI_プレフィックスを持つ送料・店舗手数料を含める
        return array_merge(
                              $this->_parseInternal($params, self::getOrderGroupKeys(), 'O_'),
                              $this->parseFeeGroup($params) );
    }

    /**
     * リクエストパラメータを展開し、購入者情報グループのパラメータ配列を構築する
     *
     * @param array $params リクエストパラメータ
     * @return array
     */
    public function parseCustomerGroup(array $params = array()) {
        return $this->_parseInternal($params, self::getCustomerGroupKeys(), 'C_');
    }

    /**
     * リクエストパラメータを展開し、配送先情報グループのパラメータ配列を構築する
     *
     * @param array $params リクエストパラメータ
     * @return array
     */
    public function parseDestinationGroup(array $params = array()) {
        return $this->_parseInternal($params, self::getDestinationGroupKeys(), 'D_');
    }

    /**
     * リクエストパラメータを展開し、配送伝票情報グループのパラメータ配列を構築する
     *
     * @param array $params リクエストパラメータ
     * @return array
     */
    public function parseJournalGroup(array $params = array()) {
        return $this->_parseInternal($params, self::getJournalGroupKeys(), 'Deli_');
    }

    /**
     * リクエストパラメータを展開し、商品明細情報グループのパラメータ配列を構築する
     *
     * @param array $params リクエストパラメータ
     * @return array
     */
    public function parseItemsGroup(array $params = array()) {
        $result = array();
        if(!is_array($params)) $params = array();

        $keys = self::getItemsGroupKeys();
        $prefix = 'I_';
        // I_で始まるキープレフィックスを先に構築
        $in_keys = array();
        foreach($keys as $key) {
            $in_keys[$key] = ($prefix . $key);
        }

        // 渡されたパラメータすべてを走査
        foreach($params as $name => $value) {
            foreach($in_keys as $key => $in_key) {
                if(strpos($name, $in_key . '_') === 0) {            // 'I_'で始まるキープレフィックスに一致した場合
                    if(preg_match('/_(\d+)$/', $name, $matches)) {  // 末尾が_(\d)に一致した場合
                        $idx = (int)$matches[1];
                        if(!is_array($result[$idx])) {
                            $result[$idx] = array();
                        }
                        $result[$idx][$key] = nvl($value);
                    }
                }
            }
        }

        // 検出時のインデックス情報は不要なので値のみを返す
        return array_values($result);
    }

    /**
     * リクエストパラメータを展開し、送料・店舗手数料のパラメータ配列を構築する
     *
     * @param array $params リクエストパラメータ
     * @return array
     */
    public function parseFeeGroup(array $params = array()) {
        return $this->_parseInternal($params, self::getFeeGroupKeys(), 'I_');
    }

    /**
     * 指定のリクエストパラメータを、指定キー＋プレフィックスの組み合わせで抽出しパラメータ配列に展開する
     *
     * @access protected
     * @param array $params リクエストパラメータ
     * @param array $keys 使用する有効キー配列
     * @param string $prefix キーに適用するプレフィックス
     * @return array
     */
    protected function _parseInternal(array $params, array $keys, $prefix) {
        $result = array();
        if(!is_array($params)) $params = array();

        foreach($keys as $key) {
            $in_key = $prefix . $key;
            if(isset($params[$in_key])) $result[$key] = nvl($params[$in_key]);
        }

        return $result;
    }
}
