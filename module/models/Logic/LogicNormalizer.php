<?php
namespace models\Logic;

use models\Logic\Normalizer\LogicNormalizerInterface;

/**
 * 複数の正規化フィルタを組み合わせて入力文字列を正規化する。
 * アプリケーション仕様に適合したフィルタ条件構築済みのインスタンスを
 * 取得するためのファクトリメソッドも提供する
 */
class LogicNormalizer {
    /**
     * 郵便番号向けのインスタンスを生成するための定義済みキー
     * @var string
     */
    const FILTER_FOR_POSTALCODE = 'postal_code';

    /**
     * 住所向けのインスタンスを生成するための定義済みキー
     * @var string
     */
    const FILTER_FOR_ADDRESS = 'address';

    /**
     * 氏名向けのインスタンスを生成するための定義済みキー
     * @var string
     */
    const FILTER_FOR_NAME = 'name';

    /**
     * 氏名カナ向けのインスタンスを生成するための定義済みキー
     * @var string
     */
    const FILTER_FOR_KANA_NAME = 'kana_name';

    /**
     * 電話番号向けのインスタンスを生成するための定義済みキー
     * @var string
     */
    const FILTER_FOR_TEL = 'tel';

    /**
     * メールアドレス向けのインスタンスを生成するための定義済みキー
     * @var string
     */
    const FILTER_FOR_MAIL = 'mail';

    /**
     * 商品名向けのインスタンスを生成するための定義済みキー
     * @var string
     */
    const FILTER_FOR_ITEM_NAME = 'item_name';

    /**
     * 商品名カナ向けのインスタンスを生成するための定義済みキー
     * @var string
     */
    const FILTER_FOR_KANA_ITEM_NAME = 'kana_item_name';

    /**
     * 金額向けのインスタンスを生成するための定義済みキー
     * @var string
     */
    const FILTER_FOR_MONEY = 'money';

	/**
     * 事業者IDインスタンスを生成するための定義済みキー
     * @var string
     */
    const FILTER_FOR_ID = 'id_name';


    /**
     * 特定目的の入力データを正規化するためのインスタンスを生成する。
     *
     * @param string $kind 入力データの種別を指定する、このクラスの定義済みキー
     * @return LogicNormalizer 指定種別の正規化を行う、LogicNormalizerインスタンス
     */
    public static function create($kind) {
        $result = new self();
        switch($kind) {
            case self::FILTER_FOR_POSTALCODE:   // 郵便番号向け
                $result
                    ->addFilterName('ZenToHan')
                    ->addFilterName('DeleteNoNumeric')
                    ->addFilterName('FixedPostalCode');
                break;
            case self::FILTER_FOR_ADDRESS:      // 住所向け
                $result
                    ->addFilterName('HanToZen')
                    ->addFilterName('HiraToKata')
                    ->addFilterName('DeleteBlankChars')
                    ->addFilterName('CjToArabic');
                break;
            case self::FILTER_FOR_NAME:         // 氏名/商品名向け
            case self::FILTER_FOR_ITEM_NAME:
                $result
                    ->addFilterName('HanToZen')
                    ->addFilterName('HiraToKata')
                    ->addFilterName('DeleteBlankChars')
                    ->addFilterName('Hyphens')
                    ->addFilterName('HyphenCompaction');
                break;
            case self::FILTER_FOR_KANA_NAME:    // 氏名カナ/商品名カナ向け
            case self::FILTER_FOR_KANA_ITEM_NAME:
                $result
                    ->addFilterName('HanToZen')
                    ->addFilterName('HiraToKata')
                    ->addFilterName('DeleteNoKatakana');
                break;
            case self::FILTER_FOR_TEL:          // 電話番号向け
                $result
                    ->addFilterName('ZenToHan')
                    ->addFilterName('DeleteNoNumeric')
                    ->addFilterName('DeleteZeroPrefix');
                break;
            case self::FILTER_FOR_MONEY:          // 金額向け
                $result
                    ->addFilterName('ZenToHan')
                    ->addFilterName('DeleteNoNumeric')
                    ->addFilterName('DeleteZeroPrefix');
                break;
            case self::FILTER_FOR_MAIL:         // メールアドレス向け
                $result
                    ->addFilterName('ZenToHan')
                    ->addFilterName('DeleteNoAsciiChars');
                break;
            case self::FILTER_FOR_ID:         // 事業者ＩＤ
                $result
                    ->addFilterName('ZenToHan');
                break;
            default:
                // 定義済み以外のパラメータが指定されたら例外をスロー
                throw new \Exception('invalid filter-kind specified');
        }

        return $result;
    }

    /**
     * フィルタクラスのリスト
     *
     * @access protected
     * @var array
     */
    protected $_filters;

    /**
     * LogicNormalizerの新しいインスタンスを初期化する
     */
    public function __construct() {
        $this->_filters = array();
    }

    /**
     * クラス名を指定して、正規化フィルタを追加する。
     * クラス名は、名前空間「LogicNormalizer」を覗いた部分のみとするため、
     * たとえば全角→半角フィルタを追加する場合は'ZenToHan'のように
     * 指定する
     *
     * @param string $filterClass 正規化フィルタクラス名
     * @return LogicNormalizer このインスタンス自身
     */
    public function addFilterName($filterClass) {
        try {
            $className = sprintf('models\Logic\Normalizer\LogicNormalizer%s', $filterClass);
            return $this->addFilter(new $className());
        }
        catch(\Exception $err) {
            throw $err;
        }
    }

    /**
     * 正規化フィルタのインスタンスを追加する
     *
     * @param LogicNormalizerInterface 追加する正規化フィルタのインスタンス
     * @return LogicNormalizer このインスタンス自身
     */
    public function addFilter(LogicNormalizerInterface $filter) {
        $this->_filters[] = $filter;
        return $this;
    }

    /**
     * 現在のフィルタ構成で正規化フィルタ処理を適用する
     *
     * @param string $input フィルタを適用する文字列
     * @return string 登録済みフィルタをすべて適用した後の文字列
     */
    public function normalize($input) {
        foreach($this->_filters as $filter) {
            $input = $filter->normalize($input);
        }
        return $input;
    }
}
