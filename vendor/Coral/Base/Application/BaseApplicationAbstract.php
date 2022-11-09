<?php
namespace Coral\Base\Application;

// Noticeレポートは回避させる
// 当箇所に記載した理由は、バッチにてNoticeが発生しないように
// 全バッチに記載する必要をなくすため。
error_reporting(30711);

use Zend\Loader\StandardAutoloader;
use Zend\Db\Adapter\Adapter;

/**
 * Zend_Controllerを利用したMVCアプリケーション向けの汎用アプリケーションの抽象クラスです。
 * インクルードパスの設定やクラスライブラリの初期ロード、フロントコントローラの初期設定などの
 * 機能を提供します。
 *
 * 派生クラスは{@link run}抽象メソッドをオーバーライドし、フロントコントローラの
 * 起動を行う必要があります。
 *
 * @abstract
 */
abstract class BaseApplicationAbstract {
    /**
     * BaseApplicationAbstract クラスの唯一のインスタンスです。
     *
     * @access protected
     * @static
     */
    protected static $_instance = null;

    // Del By Takemasa(NDC) 20141203 Stt static関数をabstractクラスへ含むこと不可故
    //     /**
    //      * このクラスの唯一のインスタンスを取得します。
    //      * 派生クラスは必ずこのメソッドをオーバーライドしてください。
    //      *
    //      * @abstract
    //      * @static
    //      * @access public
    //      * @return BaseApplicationAbstract
    //      */
    //      public abstract static function getInstance();
    // Del By Takemasa(NDC) 20141203 End static関数をabstractクラスへ含むこと不可故

    /**
    * 汎用プロパティコンテナです。
    *
    * @var array
    * @access protected
    */
    protected $_properties;

    // Del By Takemasa(NDC) 20141209 Stt Zend_Controller_FrontはZF2では存在しない
    //     /**
    //      * MVCアプリケーションをコントロールするフロントコントローラです。
    //      *
    //      * @var Zend_Controller_Front
    //      * @access protected
    //      */
    //     protected $_frontController;
    // Del By Takemasa(NDC) 20141209 End Zend_Controller_FrontはZF2では存在しない

    /**
    * アプリケーションを識別するIDです。
    * 派生クラスでオーバーライドしてください。
    *
    * @var string
    * @access protected
    */
     protected $_application_id = 'BaseApplication';

     /**
      * Separator for nesting levels of configuration data identifiers.
      *
      * @var string
      */
     protected $nestSeparator = '.';

     /**
     * BaseApplicationAbstract のインスタンスを初期化します。
     * 派生クラスはコンストラクタからこのメソッドを必ず呼び出す必要があります。
     *
     * @access protected
     */
     protected function init() {
     $this->_properties = array();

     // Del By Takemasa(NDC) 20141209 Stt Zend_Controller_FrontはZF2では存在しない
     //         $this->_frontController = Zend_Controller_Front::getInstance();
     // Del By Takemasa(NDC) 20141209 End Zend_Controller_FrontはZF2では存在しない
     }

     /**
     * メソッドパラメータの型違反を通知する例外を生成します。
     *
     * @access protected
     * @param array $types メソッドが要求する型の名前の配列
     * @return Exception
         */
         protected function createParameterException(array $types) {
         return new \Exception( join( ' or ', $types ) );
         }

         /**
         * 外部PHPファイルのインクルードパスを追加します。
         *
         * @access public
         * @param string|array $path 追加するインクルードパス、またはパスの配列
         * @return BaseApplicationAbstract
         */
         public function addIncludePath($path) {
             if( is_string($path) ) {
             $path = array( $path );
         } else if( ! is_array( $path ) ) {
             throw $this->createParameterException( array('string', 'array' ) );
         }

         foreach($path as $p) {
         set_include_path( get_include_path() . PATH_SEPARATOR . $p );
}

return $this;
}

/**
* {@link run}メソッドでメイン処理を実行する前にロードするクラスを追加します。
*
* @access public
* @param string|array $class ロードするクラスの名前またはクラス名の配列
* @return BaseApplicationAbstract
    */
        public function addClass($class) {
        if( is_string($class) ) {
            $class = array( $class );
        } else if( ! is_array($class) ) {
        throw $this->createParameterException( array('string', 'array' ) );
}

//zzz ↓これ、いらんよね？
//         foreach($class as $c) {
//             Zend_Loader::loadClass( $c );
//         }

$al = new StandardAutoloader();
foreach($class as $c) {
$al->autoload( $c );
         }

         return $this;
         }

         // Del By Takemasa(NDC) 20141209 Stt Zend_Controller_FrontはZF2では存在しない
         //     /**
         //      * コントローラディレクトリのパスをフロントコントローラへ追加します
         //      *
         //      * @access public
         //      * @param string $directory
         //      * @param string $module Optional argument; module with which to associate directory. If none provided, assumes 'defualt'
         //      * @return Zend_Controller_Front
         //      * @throws Zend_Controller_Exception if directory not found or readable
         //      */
         //     public function addControllerDirectory($directory, $module = null) {
         //         $this->_frontController->addControllerDirectory( $directory, $module );
         //
         //         return $this;
         //     }
         //
         //     /**
         //      * コントローラディレクトリをフロントコントローラへ設定します
         //      *
         //      * @access public
         //      * @param string|array $directory Path to Zend_Controller_Action controller
         //      * classes or array of such paths
         //      * @param  string $module Optional module name to use with string $directory
         //      * @return Zend_Controller_Front
         //      */
         //     public function setControllerDirectory($directory, $module = null) {
         //         $this->_frontController->setControllerDirectory( $directory, $module );
         //
         //         return $this;
         //     }
         //
         //     /**
         //      * フロントコントローラに設定されているコントローラディレクトリスタックを取得します
         //      *
         //      * @access public
         //      * @param  string $name Default null
         //      * @return array|string|null
         //      */
         //     public function getControllerDirectory($name = null) {
         //         return $this->_frontController->getControllerDirectory($name);
         //     }
         //
         //     /**
         //      * フロントコントローラを取得します。
         //      *
         //      * @access public
         //      * @return Zend_Controller_Front
         //      */
         //     public function getFrontController() {
         //         return $this->_frontController;
         //     }
         //
         //     /**
         //      * 現在のWebアプリケーションのルートURLを絶対URLで取得します。
         //      * パス情報はリクエストから取得するため、リクエスト情報が取得できない場合
         //      * このメソッドは false を返します。
         //      *
         //      * @access public
         //      * @return string|false
         //      */
         //     public function getApplicationUrl() {
         //         require_once 'NetB/Controller/Utility.php';
         //         return NetB_Controller_Utility::getApplicationUrl( $this->getFrontController()->getRequest() );
         //     }
         //
         //     /**
         //      * 現在のリクエストを絶対URIで取得します。
         //      * リクエスト情報が取得できない場合、このメソッドは false を返します。
         //      *
         //      * @access public
         //      * @return string|false
         //      */
         //     public function getAbsoluteRequestUri() {
         //         require_once 'NetB/Controller/Utility.php';
         //         return NetB_Controller_Utility::getAbsoluteRequestUri( $this->getFrontController()->getRequest() );
         //     }
         // Del By Takemasa(NDC) 20141209 End Zend_Controller_FrontはZF2では存在しない

         /**
         * MVCアプリケーションを実行します。
         * 派生クラスはこのメソッドをオーバーライドし、フロントコントローラの初期化・実行を
         * 行う必要があります。
         *
         * @abstract
         * @access public
         * @return void
         */
         public abstract function run();

         /**
         * BaseApplicationAbstractの動的プロパティを取得します。
         *
     * @magic
         * @ignore
         * @access public
     * @param string $key 取得するプロパティ名
     * @return mixed
    */
    public function __get($key) {
        return $this->_properties[ $key ];
    }

    /**
     * BaseApplicationAbstractの動的プロパティへ値を設定します。
     *
     * @magic
     * @ignore
     * @access public
     * @param string $key 設定するプロパティの名前
     * @param mixed $value プロパティに設定する値
     * @return void
     */
    public function __set($key, $value) {
        $this->_properties[ $key ] = $value;
    }

    /**
     * アプリケーションIDを取得します。
     *
     * @return string
     */
    public function getApplicationId() {
        return $this->_application_id;
    }

    /**
     * 403エラーレスポンスを返す
     */
    public function return403Error() {
        header('HTTP/1.1 403 Forbidden');
        header('Content-Type: text/html; charset=iso-8859-1');
        $src = <<<EOH
<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html><head>
<title>403 Forbidden</title>
</head><body>
<h1>Forbidden</h1>
<p>You don't have permission to access %s
on this server.</p>
</body></html>
EOH;
        die(sprintf($src, f_e($_SERVER['REQUEST_URI'])));
    }

    /**
     * 404エラーレスポンスを返す
     */
    public function return404Error() {
        header('HTTP/1.1 404 Not Found');
        header('Content-Type: text/html; charset=iso-8859-1');
        $src = <<<EOH
<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html><head>
<title>404 Not Found</title>
</head><body>
<h1>Not Found</h1>
<p>The requested URL %s was not found on this server.</p>
</body></html>
EOH;
        die(sprintf($src, $_SERVER['REQUEST_URI']));
    }

    /**
     * モジュールの設定ファイルをシステムプロパティからロードします
     * @param  Adapter $adapter
     * @param  string  $module
     * @return array
     */
    public function getApplicationiInfo($adapter, $module){

        // 必要な設定データをモジュール指定で取得
$sql = <<<EOQ
        SELECT  Category, Name, PropValue
          FROM  T_SystemProperty
         WHERE  Module      = :Module
           AND  ValidFlg    = 1
        ORDER BY Category, Name
EOQ;

        // パラメーターを指定
        $prm = array(
            ':Module' => $module,
        );

        // SQL発行
        $ri = $adapter->query($sql)->execute($prm);
        $arr = ResultInterfaceToArray($ri);

        // 従来のiniファイルの読み込み結果と同等に変換
        $categoryResult = array();
        $results = array();
        foreach($arr as $key => $value) {

            $category   = $value['Category'];
            $name       = $value['Name'];
            $propvalue  = $value['PropValue'];

            if ($category != $bef_category && is_null($bef_category) == false ) {
                // カテゴリが変わった場合は、マージ
                $results = array_merge($results, array($bef_category => $categoryResult));

                // 初期化
                $categoryResult = array();
            }

            // １カテゴリ分のKey=>Valueを作りこみ
            $categoryResult = array_merge($categoryResult, array($name => $propvalue));

            $bef_category = $category;

        }

        // 最後のカテゴリをマージ
        $results = array_merge($results, array($category => $categoryResult));

        return $this->process($results);

    }

    /**
     * Process data from the parsed ini file.
     *
     * @param  array $data
     * @return array
     */
    protected function process(array $data)
    {
        $config = array();

        foreach ($data as $section => $value) {
            if (is_array($value)) {
                if (strpos($section, $this->nestSeparator) !== false) {
                    $sections = explode($this->nestSeparator, $section);
                    $config = array_merge_recursive($config, $this->buildNestedSection($sections, $value));
                } else {
                    $config[$section] = $this->processSection($value);
                }
            } else {
                $this->processKey($section, $value, $config);
            }
        }

        return $config;
    }

    /**
     * Process a nested section
     *
     * @param array $sections
     * @param mixed $value
     * @return array
     */
    private function buildNestedSection($sections, $value)
    {
        if (count($sections) == 0) {
            return $this->processSection($value);
        }

        $nestedSection = array();

        $first = array_shift($sections);
        $nestedSection[$first] = $this->buildNestedSection($sections, $value);

        return $nestedSection;
    }

    /**
     * Process a section.
     *
     * @param  array $section
     * @return array
     */
    protected function processSection(array $section)
    {
        $config = array();

        foreach ($section as $key => $value) {
            $this->processKey($key, $value, $config);
        }

        return $config;
    }

    /**
     * Process a key.
     *
     * @param  string $key
     * @param  string $value
     * @param  array  $config
     * @return array
     * @throws Exception\RuntimeException
     */
    protected function processKey($key, $value, array &$config)
    {
        if (strpos($key, $this->nestSeparator) !== false) {
            $pieces = explode($this->nestSeparator, $key, 2);

            if (!strlen($pieces[0]) || !strlen($pieces[1])) {
                throw new \Exception(sprintf('Invalid key "%s"', $key));
            } elseif (!isset($config[$pieces[0]])) {
                if ($pieces[0] === '0' && !empty($config)) {
                    $config = array($pieces[0] => $config);
                } else {
                    $config[$pieces[0]] = array();
                }
            } elseif (!is_array($config[$pieces[0]])) {
                throw new \Exception(
                sprintf('Cannot create sub-key for "%s", as key already exists', $pieces[0])
                );
            }

            $this->processKey($pieces[1], $value, $config[$pieces[0]]);
        } else {
            if ($key === '@include') {
                if ($this->directory === null) {
                    throw new \Exception('Cannot process @include statement for a string config');
                }

                $reader  = clone $this;
                $include = $reader->fromFile($this->directory . '/' . $value);
                $config  = array_replace_recursive($config, $include);
            } else {
                $config[$key] = $value;
            }
        }
    }
}
