<?php
namespace cbadmin\classes;

use Coral\Coral\Converter\CoralConverterInterface;
use Coral\Coral\Converter\CoralConverterDefault;
use Coral\Base\IO\BaseIOUtility;
use Zend\Json\Json;
use Zend\Loader\StandardAutoloader;

/**
 * {@link OemCsvWriter}の出力・変換設定を定義するクラス。
 */
class OemCsvSettings {
   /**
    * コンバータクラスの名前空間
    * @var string
    */
   const CONVERTER_NAMESPACE = 'Coral\Coral\Converter';

   /**
    * 指定パスにあるJSON形式の設定ファイルから、
    * {@link OemCsvSettings}の新しいインスタンスを生成する
    * @static
    * @param string 設定ファイルのパス
    * @return OemCsvSettings このクラスの新しいインスタンス
    */
   public static function fromJsonFile($path) {
      $src = file_get_contents($path);

      $result = new self();
      return $result->addSettings(Json::decode($src, Json::TYPE_ARRAY));
   }

   /**
    * コンバータ設定
    * @var array
    */
   protected $_converters;

   /**
    * {@link OemCsvSettings}の新しいインスタンスを初期化する
    */
   public function __construct() {
      $this->_converters = array();
      $this->_classCache = array();
   }

   /**
    * 変換設定情報を格納した連想配列を指定して、コンバータ設定を追加する。
    * 引数の連想配列は以下の構成をとる必要がある
    *  - field : 入力元のT_Oemのカラム名。内部連想配列のキーとしても採用される
    *  - title : 出力時のカラムヘッダに使用するラベル文字列
    *  - converterName : コンバータクラスの名前。実際のコンバータクラスの名前空間'Coral\Coral\Converter'を除いた形式
    *  - args : コンバータクラスのコンストラクタパラメータの配列
    *  - disabled : 無効設定フラグ。trueを指定した場合、その設定は使用されない
    * @param array $setting 設定情報を格納した連想配列
    * @return OemCsvSettings このインスタンス自身
    */
   public function addSetting($setting) {
       if( is_array($setting) ) {
         $key = $setting['field'];

         // コンバータクラスの名前を構築
         $converterClass = BaseIOUtility::buildPath(
                 self::CONVERTER_NAMESPACE,
                 empty($setting['converterName']) ? 'CoralConverter'. 'Default' : 'CoralConverter' . $setting['converterName'],
                 '\\' );

         // 無効フラグがtrueで無い場合のみ追加処理を実行
         if( ! $setting['disabled'] ) {
            // クラス定義ロード
//            Zend_Loader::loadClass( $converterClass );

            // リフレクションAPIを利用して、create静的メソッドを実行しインスタンスを得る
            $ref = new \ReflectionClass($converterClass);
            $converter = $ref->getMethod('create')->invokeArgs(null, $setting['args']);

            // インスタンスの生成に失敗したら即時例外
            if( $converter == null  ) throw new \Exception( "converter '$converterClass' cannot initialized." );

            // インスタンスをバインドし、コンバータリストに関連付ける
            $setting['converter'] = $converter;
            $this->_converters[$key] = $setting;
         }
      }
      return $this;
   }

   /**
    * 変換設定の配列を指定して、コンバータ設定リストを追加する。
    * 各要素のフォーマットは{@link addSetting}メソッドを参照。
    * @param array $settings 変換設定の配列
    * @return OemCsvSettings このインスタンス自身
    * @see addSetting
    */
   public function addSettings($settings = array()) {
      if( ! is_array($settings) ) $settings = array($settings);
      foreach($settings as $setting) $this->addSetting($setting);

      return $this;
   }

   /**
    * 指定のキーに関連付けられたコンバータを取得する。
    * キーは、入力データとなるT_Oemのカラム名に一致する
    * @param string $key コンバータキー
    * @return CoralConverterInterface $keyに関連付けられたコンバータインスタンス
    */
   public function getConverter($key) {
      $setting = $this->_converters[$key];
      if( is_array($setting) ) return $setting['converter'];

      // 関連する設定が無い場合はデフォルトコンバータを返す
      return new CoralConverterDefault();
   }

   /**
    * 指定キーに関連付けられたヘッダタイトルを取得する
    * @param string $key コンバータキー
    * @return string $keyに関連付けられたカラムヘッダタイトル
    */
   public function getTitle($key) {
      $setting = $this->_converters[$key];
      if( is_array($setting) ) return $setting['title'];

      // 関連する設定がない場合はキー自体を返す
      return $key;
   }

   /**
    * 現在の設定で定義されているすべての処理カラム名を、設定の順序を保持したまま取得する
    * @return array 処理カラム名の配列
    */
   public function getFields() {
      return array_keys( $this->_converters );
   }

   /**
    * 現在の設定で定義されているすべてのカラムヘッダタイトルを、設定の順序を保持したまま取得する
    * @return array カラムヘッダタイトルの配列
    */
   public function getTitles() {
      $result = array();
      foreach($this->getFields() as $field) {
         $result[] = $this->getTitle($field);
      }
      return $result;
   }
}
