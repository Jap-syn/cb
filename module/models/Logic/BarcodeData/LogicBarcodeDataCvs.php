<?php
namespace models\Logic\BarcodeData;

use models\Logic\LogicStampFee;
use models\Logic\BarcodeData\Cvs\LogicBarcodeDataCvsInterface;
use models\Logic\BarcodeData\Cvs\LogicBarcodeDataCvsAbstract;
use models\Logic\BarcodeData\Cvs\LogicBarcodeDataCvsAplus;
use models\Logic\BarcodeData\Cvs\LogicBarcodeDataCvsAtPayment;
use models\Logic\BarcodeData\Cvs\LogicBarcodeDataCvsNTTSmartTrade;
use models\Logic\BarcodeData\Cvs\LogicBarcodeDataCvsIndividualPay;
use models\Logic\BarcodeData\Cvs\LogicBarcodeDataCvsKessaiNavi;
use models\Logic\BarcodeData\Cvs\LogicBarcodeDataCvsCvsNet;

/**
 * バーコードデータ生成ロジックのファクトリ機能を提供するユーティリティ
 */
final class LogicBarcodeDataCvs {
    /**
     * このクラスに設定されているLogicStampFee向け初期設定データ
     * @static
     * @access private
     * @var array
     */
    private static $__stampFeeSettings = null;

    /**
     * createGeneratorメソッドで正常に生成されたバーコード生成ロジッククラス名のキャッシュ
     * @static
     * @access private
     * @var array
     */
    private static $__validClassName = array();

    /**
     * createGeneratorメソッドで生成に失敗した、不正なバーコード生成ロジッククラス名のキャッシュ
     * @static
     * @access private
     * @var array
     */
    private static $__invalidClassName = array();

    /**
     * このクラス内部で保持する、LogicStampFeeの初期設定データ
     * @static
     * @access private
     * @return array
     */
    private static function __getInnerStampFeeSettings() {
        return array(
            '[DEFAULT]' => 31500,
            '2014-04-01' => 54000
        );
    }

    /**
     * このクラスに関連付けられているLogicStampFeeの初期化に必要な閾金額設定データを取得する
     * @static
     * @return array
     */
    public static function getStampFeeSettings() {
        if(self::$__stampFeeSettings === null) {
            self::$__stampFeeSettings = self::__getInnerStampFeeSettings();
        }
        return self::$__stampFeeSettings;
    }

    /**
     * 指定のLogicStampFee向け閾金額設定データをこのクラスに関連付ける
     * @static
     * @param array $settings LogicStampFeeの初期化に使用する閾金額設定データ
     */
    public static function setStampFeeSettings(array $settings) {
        self::$__stampFeeSettings = $settings;
    }

    /**
     * 指定のバーコード生成ロジッククラスのインスタンスを生成する
     *
     * @static
     * @param string $className バーコード生成ロジッククラスのフルネーム
     * @param string $makerCode 5桁の収納代行会社固有コード
     * @param string $corporateCode 加入者固有コード
     * @return LogicBarcodeDataCvsInterface $classNameで指定されたバーコード生成ロジックの新しいインスタンス
     */
    public static function createGenerator($className, $makerCode, $corporateCode) {
        try {
            // インスタンスの生成と初期化を実行
            if ($className == 'LogicBarcodeDataCvsAplus') {
                $instance = new LogicBarcodeDataCvsAplus($makerCode);
            }
            else if ($className == 'LogicBarcodeDataCvsAtPayment') {
                $instance = new LogicBarcodeDataCvsAtPayment($makerCode);
            }
            else if ($className == 'LogicBarcodeDataCvsNTTSmartTrade') {
                $instance = new LogicBarcodeDataCvsNTTSmartTrade($makerCode);
            }
            else if ($className == 'LogicBarcodeDataCvsIndividualPay') {
                $instance = new LogicBarcodeDataCvsIndividualPay($makerCode);
            }
            // CB_B2C_DEV-46
            else if ($className == 'LogicBarcodeDataCvsKessaiNavi') {
            	$instance = new LogicBarcodeDataCvsKessaiNavi($makerCode);
            }
            else if ($className == 'LogicBarcodeDataCvsCvsNet') {
                $instance = new LogicBarcodeDataCvsCvsNet($makerCode);
            }
            else
            {
                $instance = new \models\Logic\BarcodeData\Cvs\LogicBarcodeDataCvsSmbcfs($makerCode);
            }

            $instance->setCorporateCode($corporateCode);

            // ここまでエラーがなく且つ今回新規に生成された場合は生成済みに登録しておく
            if(!isset(self::$__validClassName[$className])) {
                self::$__validClassName[$className] = true;
            }

            // 生成したインスタンスを返す
            return $instance;
        } catch(\Exception $err) {
            // 例外が発生したらエラー情報を記録しておく
            if(!isset(self::$__invalidClassName[$className])) {
                self::$__invalidClassName[$className] = array();
            }
            self::$__invalidClassName[$className][] = sprintf('%s (%s)', $err->getMessage(), get_class($err));

            // 上位にはそのままエラーをthrowする
            throw $err;
        }
    }

    /**
     * LogicStampFeeの新しいインスタンスを生成する
     *
     * @param null | array $settings 閾金額設定データ。getStampFeeSettings()の内容にマージされる
     * @return LogicStampFee
     */
    public static function createStampFeeLogic(array $settings = array()) {
        if(!is_array($settings)) $settings = array();
        $settings = array_merge(self::getStampFeeSettings(), $settings);
        return new LogicStampFee($settings);
    }
}
