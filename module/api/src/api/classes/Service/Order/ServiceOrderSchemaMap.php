<?php
namespace api\classes\Service\Order;

use api\Application;
use api\classes\Service\Order\ServiceOrderOrderConst;
use api\classes\Service\Order\ServiceOrderCustomerConst;
use api\classes\Service\Order\ServiceOrderDestinationConst;
use api\classes\Service\Order\ServiceOrderItemsConst;
use Zend\Json\Json;

/**
 * Csvスキーマとの紐付きを管理するクラス.<br>
 *
 * ServiceOrderSchemaMap::load($className, $jsonName)メソッドにて、<br>
 * 親となる定義クラスへの一括代入が可能。
 */
class ServiceOrderSchemaMap {
    // CSVスキーマによる検証対象外で独自に文字列長チェックを必要とする項目を
    // 特定するためのServiceOrderSchemaMap::ordinal値を定義する定数
    const NEED_LENCHK_ORDINAL_VALUE = -9;

    /** @var string */
    public $name;
    /** @var bool */
    public $required;
    /** @var int */
    public $maxLength;
    /** @var int */
    public $ordinal;

    /**
     * コンストラクタ.
     * @param $name
     * @param $required;
     * @param $maxLength
     * @param $ordinal
     */
    public function __construct($name, $required, $maxLength, $ordinal) {
        $this->name = $name;
        $this->required = $required;
        $this->maxLength = $maxLength;
        $this->ordinal = $ordinal;
    }

    /**
     * 一括読み込みメソッド.
     *
     * @param $className 本クラスをstaticプロパティとして定義する親クラスの名前
     * @param $jsonName jsonファイル名
     */
     public static function load($className, $jsonName) {
        $jsonString = file_get_contents("./module/api/config/schemamap/$jsonName.json");
        $arr = Json::decode($jsonString);

        foreach ( $arr as $schemaMap ) {
            $name = $schemaMap->name;
            $required = $schemaMap->required;
            $maxLength = $schemaMap->maxLength;
            $ordinal = $schemaMap->ordinal;

            $clazz = new \ReflectionClass($className);
            $clazz->setStaticPropertyValue(
                $name,
                new ServiceOrderSchemaMap($name, $required, $maxLength, $ordinal));
        }
    }
}
