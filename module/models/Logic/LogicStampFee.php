<?php
namespace models\Logic;

/**
 * 収入印紙フラグを扱うロジック
 */
class LogicStampFee {
    /** 閾値設定で使用されるデフォルトキー */
    const THRESHOLD_LIST_DEFAULT_KEY = '[DEFAULT]';

    /** 閾金額適用日付が設定されないデフォルト閾金額のデフォルト値（～2014.3.31） */
    const DEFAULT_THRESHOLD_PRICE = 31500;

    /**
     * 閾金額と適用開始日付の設定リスト
     *
     * @access protected
     * @var array
     */
    protected $_threshold_list;

    /**
     * 閾金額適用日付が設定されないデフォルト閾金額
     * 通常はDEFAULT_THRESHOLD_PRICE定数の値を設定しておく
     *
     * @access protected
     * @var int
     */
    protected $_default_threshold_price;

    /**
     * 閾金額設定データを適用して、LogicStampFeeの新しい
     * インスタンスを初期化する。
     * 閾金額設定データは以下のように適用開始日をキー、適用閾金額を値とした
     * 連想配列を設定する。
     * array(
     *  '2015-04-01' => 55000
     *  '2014-04-01' => 54000
     * )
     * 特殊キーとして、THRESHOLD_LIST_DEFAULT_KEY定数で定義されているキーを
     * 指定した場合、関連付けられた閾金額は設定されているすべての適用開始日よりも
     * 以前の日付において適用されるべき閾金額を規定する。
     * これはデフォルトでは2014年3月31日以前で31,500円と規定され通常は変更する必要はない。
     *
     * @param null | array $threshold_settings 閾金額設定データ
     */
    public function __construct(array $threshold_settings = array()) {
        $this->_default_threshold_price = self::DEFAULT_THRESHOLD_PRICE;
        $this->importSettings($threshold_settings);
    }

    /**
     * 現在の閾金額設定データを連想配列でエクスポートする
     *
     * @return array importSettingsメソッドでインポート可能な閾金額設定データ
     */
    public function exportSettings() {
        $results = array(
            self::THRESHOLD_LIST_DEFAULT_KEY => $this->_default_threshold_price
        );
        foreach($this->_threshold_list as $setting) {
            $key = $setting['date'];
            $value = $setting['price'];
            $results[$key] = $value;
        }
        return $results;
    }

    /**
     * 閾金額設定データをインポートして内部を初期化する。
     * 閾金額設定データは以下のように適用開始日をキー、適用閾金額を値とした
     * 連想配列を設定する。
     * array(
     *  '2015-04-01' => 55000
     *  '2014-04-01' => 54000
     * )
     * 特殊キーとして、THRESHOLD_LIST_DEFAULT_KEY定数で定義されているキーを
     * 指定した場合、関連付けられた閾金額は設定されているすべての適用開始日よりも
     * 以前の日付において適用されるべき閾金額を規定する。
     * これはデフォルトでは2014年3月31日以前で31,500円と規定され通常は変更する必要はない。
     *
     * @param null | array $threshold_settings 閾金額設定データ
     * @return LogicStampFee このインスタンス
     */
    public function importSettings(array $threshold_settings = array()) {
        if(!is_array($threshold_settings)) $threshold_settings = array();
        $new_list = array();
        $keys = array_keys($threshold_settings);
        sort($keys);
        $keys = array_reverse($keys);
        foreach($keys as $key) {
            if(!is_numeric($threshold_settings[$key])) continue;
            $value = (int)($threshold_settings[$key]);
            if($key == self::THRESHOLD_LIST_DEFAULT_KEY) {
                $this->_default_threshold_price = $value;
            } else
            if(IsDate($key)) {
                $dt = date('Y-m-d', $key);
                $key = $dt;
                $new_list[] = array(
                    'date' => $key,
                    'price' => $value
                );
            }
        }
        $this->_threshold_list = $new_list;

        return $this;
    }

    /**
     * 閾金額設定リストをエクスポートする。
     * 戻り値は配列で、各要素がキー'date'に適用日付、キー'price'に閾金額を格納した形式となる
     *
     * @return array 閾金額設定リスト
     */
    public function exportList() {
        return array_merge(array(), $this->_threshold_list);
    }

    /**
     * 指定金額が現在日において印紙フラグを必要とするかを判断する
     *
     * @param int $price 判別する金額
     * @return boolean $priceの金額が現在日において印紙フラグを必要とする金額以上の場合はtrue、それ以外はfalse
     */
    public function judgeStampFeeFlg($price) {
        return $this->judgeStampFeeFlgAtDate($price, date('Y-m-d'));
    }

    /**
     * 指定金額が指定日付において印紙フラグを必要とするかを判断する
     *
     * @param int $price 判別する金額
     * @param string $date 判別する日付
     * @return boolean $priceの金額が$dateにおいて印紙フラグを必要とする金額以上の場合はtrue、それ以外はfalse
     */
    public function judgeStampFeeFlgAtDate($price, $date) {
        return $price >= $this->getStampFeeThresholdAt($date);
    }

    /**
     * いま現在の印紙フラグ適用閾金額を取得する
     *
     * @return int 印紙フラグ適用の閾金額
     */
    public function getStampFeeThreshold() {
        return $this->getStampFeeThresholdAt(date('Y-m-d'));
    }

    /**
     * 指定日付における印紙フラグ適用閾金額を取得する
     *
     * @param string $date 判別する日付
     * @return int $dateにおける印紙フラグ適用の閾金額
     */
    public function getStampFeeThresholdAt($date) {
        if(!IsDate($date)) {
            throw new \Exception('invalid date-string specified.');
        }

        $dt = $date;
        $date = $dt;

        foreach($this->_threshold_list as $setting) {
            if($date >= $setting['date']) {
                return $setting['price'];
            }
        }
        return $this->_default_threshold_price;
    }
}
