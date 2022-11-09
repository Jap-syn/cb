<!-- #!/usr/local/bin/php -->

<?php
error_reporting(E_ALL & ~E_NOTICE);
chdir(dirname(__DIR__));

require 'init_autoloader.php';

use Coral\Base\Application\BaseApplicationAbstract;
use Zend\Db\Adapter\Adapter;
use Zend\Config\Reader\Ini;
use models\Table\TablePostalCode;

class Application extends BaseApplicationAbstract {
	protected $_application_id = 'tools';

	/**
	 * Application の唯一のインスタンスを取得します。
	 *
	 * @static
	 * @access public
	 * @return Application
	 */
	public static function getInstance() {
		if( self::$_instance === null ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Application の新しいインスタンスを初期化します。
	 *
	 * @ignore
	 * @access private
	 */
	private function __construct() {
		parent::init();
	}

	/**
	 * @var Adapter
	 */
	public $dbAdapter;

	/**
	 * 20181025 ADD fgetcsvにバグがあるため、代替関数追加
	 * ファイルポインタから行を取得し、CSVフィールドを処理する
	 * @param resource handle
	 * @param int length
	 * @param string delimiter
	 * @param string enclosure
	 * @return ファイルの終端に達した場合を含み、エラー時にFALSEを返します。
	 */
    function fgetcsv_reg(&$handle,$length=NULL,$d=',',$e='"'){
	    $d=preg_quote($d);
	    $e=preg_quote($e);
	    $_line="";
	    $eof=false;
	    while(($eof!=true) && (!feof($handle))){
	        $_line.=(empty($length) ? fgets($handle) : fgets($handle,$length));
	        $itemcnt=preg_match_all('/'.$e.'/',$_line,$dummy);
	        if($itemcnt%2==0){
	            $eof=true;
	        }
	    }

	    $_csv_line=preg_replace('/(?:\\r\\n|[\\r\\n])?$/',$d,trim($_line));
	    $_csv_pattern='/('.$e.'[^'.$e.']*(?:'.$e.$e.'[^'.$e.']*)*'.$e.'|[^'.$d.']*)'.$d.'/';
	    preg_match_all($_csv_pattern,$_csv_line,$_csv_matches);
	    $_csv_data=$_csv_matches[1];
	    for($_csv_i=0;$_csv_i<count($_csv_data);$_csv_i++){
	        $_csv_data[$_csv_i]=preg_replace('/^'.$e.'(.*)'.$e.'$/s','$1',$_csv_data[$_csv_i]);
	        $_csv_data[$_csv_i]=str_replace($e.$e, $e, $_csv_data[$_csv_i]);
	    }
	    return empty($_line) ? false : $_csv_data;
	}

	/**
	 * アプリケーションを実行します。
	 *
	 * @access public
	 */
	public function run() {
        define('FROM_ENCODING', 'sjis-win');
        mb_regex_encoding( 'UTF-8' );

        try
        {

        	$configPath = __DIR__ . '/../module/cbadmin/config/config.ini';
        	// データベースアダプタをiniファイルから初期化します
        	$data = array();
        	if (file_exists($configPath))
        	{
        	    $reader = new Ini();
        	    $data = $reader->fromFile($configPath);
        	}
        	$this->dbAdapter = new Adapter($data['database']);

            define('POST_FILE', $data['postalcode']['post_file']);

            if ( !file_exists(POST_FILE) ) {
                // ファイルが存在しない場合は終了
                return;
            }

            $mdlpcd = new TablePostalCode($this->dbAdapter);
        	$mdlpcd->deleteByPostalCode();

        	$handle = fopen(POST_FILE, "r");
        	while (($data = $this->fgetcsv_reg($handle)) !== FALSE)
        	{
        		$tKana = mb_convert_encoding($data[5], 'UTF-8', FROM_ENCODING);
        		$tKanji = mb_convert_encoding($data[8], 'UTF-8', FROM_ENCODING);

        		if ($tKana == "ｲｶﾆｹｲｻｲｶﾞﾅｲﾊﾞｱｲ")
        		{
        			$tKana = "";
        		}

        		if ($tKanji == "以下に掲載がない場合")
        		{
        			$tKanji = "";
        		}

        		$tKana = mb_ereg_replace("[(].*[)]", "", $tKana);
        		$tKanji = mb_ereg_replace("[（].*[）]", "", $tKanji);

        		$localGroupCode 			= $data[0];
        		$postalCode5 				= $data[1];
        		$postalCode7 				= $data[2];
        		$prefectureKana 			= mb_convert_encoding($data[3], 'UTF-8', FROM_ENCODING);
        		$cityKana 					= mb_convert_encoding($data[4], 'UTF-8', FROM_ENCODING);
        		$townKana 					= $tKana;
        		$prefectureKanji 			= mb_convert_encoding($data[6], 'UTF-8', FROM_ENCODING);
        		$cityKanji 					= mb_convert_encoding($data[7], 'UTF-8', FROM_ENCODING);
        		$townKanji 					= $tKanji;
        		$oneTownPluralNumberFlg 	= $data[9];
        		$numberingEachKoazaFlg 		= $data[10];
        		$townIncludeChoumeFlg 		= $data[11];
        		$oneNumberPluralTownFlg 	= $data[12];
        		$updateFlg 					= $data[13];
        		$modifiedReasonCode 		= $data[14];

        		$mdlpcd->saveNew(
        		    array(
        		            'LocalGroupCode'           => $localGroupCode,
        		            'PostalCode5'              => $postalCode5,
        		            'PostalCode7'              => $postalCode7,
        		            'PrefectureKana'           => $prefectureKana,
        		            'CityKana'                 => $cityKana,
        		            'TownKana'                 => $townKana,
        		            'PrefectureKanji'          => $prefectureKanji,
        		            'CityKanji'                => $cityKanji,
        		            'TownKanji'                => $townKanji,
        		            'OneTownPluralNumberFlg'   => $oneTownPluralNumberFlg,
        		            'NumberingEachKoazaFlg'    => $numberingEachKoazaFlg,
        		            'TownIncludeChoumeFlg'     => $townIncludeChoumeFlg,
        		            'OneNumberPluralTownFlg'   => $oneNumberPluralTownFlg,
        		            'UpdateFlg'                => $updateFlg,
        		            'ModifiedReasonCode'       => $modifiedReasonCode,
        		    )
        		);

        	}

        	fclose($handle);

        	$dbh = null;
        }
        catch (PDOException $e)
        {
           echo "PDOエラー: " . $e->getMessage();
           die();
        }
        catch (Exception $e)
        {
           echo "その他エラー: " . $e->getMessage();
           die();
        }
	}
}
Application::getInstance()->run();
// unlink(TEMP_FILE);