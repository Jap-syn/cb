<?php
namespace Coral\Coral;

// require_once 'Zend/Db.php';
// require_once 'Zend/Date.php';
// require_once 'NetB/IO/Utility.php';
// require_once 'NetB/IO/Exception.php';
// require_once 'NetB/Reflection/Utility.php';
use Coral\Base\IO\BaseIOUtility;
use Coral\Base\IO\BaseIOException;

/**
 * クライアントからアップロードされたファイルの永続化とメタ情報を管理する
 * マネージャクラス
 */
class CoralUploadManager {
	/**
	 * メタ情報を格納するDBテーブル名
	 *
	 * @var string
	 */
	const METAINFO_TABLE_NAME = 'metainfo';

	/**
	 * コンストラクタオプションに使用する、ファイル作成者を指定するキー
	 *
	 * @var string
	 */
	const OPTION_AUTHOR_NAME = 'author';

	/**
	 * コンストラクタオプションに使用する、クライアント情報を指定するキー
	 *
	 * @var string
	 */
	const OPTION_CLIENT_INFO = 'client';

	/**
	 * @static
	 * アップロード先のルートディレクトリパス
	 *
	 * @access protected
	 * @var string
	 */
	protected static $__rootDirectory;

	/**
	 * @static
	 * 現在アップロード先ディレクトリに書き込み可能かを示すフラグ
	 *
	 * @access protected
	 * @var bool
	 */
	protected static $__canWrite = false;

	/**
	 * @static
	 * このクラスの現在の状態を示す文字列。$__canWriteがfalseの場合はその事由を示す。
	 *
	 * @access protected
	 * @var string
	 */
	protected static $__status = '初期化されていません';

	/**
	 * @static
	 *
	 * ルートディレクトリおよびインスタンスが所持するサブディレクトリに適用するumask値
	 *
	 * @access public
	 * @var int
	 */
	public static $directoryUmask = 0755;

	/**
	 * @static
	 * アップロードルートディレクトリを取得する
	 *
	 * @return string
	 */
	public static function getRootDirectory() {
		return self::$__rootDirectory;
	}
	/**
	 * @static
	 * アップロードルートディレクトリを設定する
	 *
	 * @param string $dir ルートにするディレクトリのパス
	 */
	public static function setRootDirectory($dir) {
		$dir = trim( "$dir" );
		if( empty( $dir ) ) throw new BaseIOException( 'ルートディレクトリに空データを設定しようとしました' );
		self::$__rootDirectory = $dir;
		try {
			// ルートの親ディレクトリの存在チェック
			$parentDir = realpath( dirname( $dir ) );
			if( ! file_exists( $parentDir ) ) {
				throw new BaseIOException( 'ルートディレクトリに不正なパスを設定しようとしました' );
			}

			if( ! file_exists( realpath( $dir ) ) ) {
				try {
					// 指定パスのディレクトリを作成する
					mkdir( $dir, self::$directoryUmask );
				} catch(\Exception $err) {
					throw new BaseIOException(
						'ディレクトリの作成に失敗しました。事由：' .
						$err->getMessage()
					);
				}
			}
			// 実パスで設定更新
			self::$__rootDirectory = $dir = realpath( $dir );

			// ダミーファイルを作成して書き込み可能性を確認
			try {
				$temp_file = tempnam( $dir, 'write_test' );
				// ダミーファイルが作成できないので例外
				if( ! $temp_file ) throw new \Exception();
				// ダミーファイルを削除
				unlink( $temp_file );
			} catch(\Exception $err) {
				throw new BaseIOException( '書き込みできません' );
			}

			// 書き込み可能状態でルートディレクトリが確保できた
			self::$__canWrite = true;
			self::$__status = 'OK';
		} catch(BaseIOException $ioError) {
			self::$__canWrite = false;
			self::$__status = $ioError->getMessage();
		}
	}

	/**
	 * @static
	 *
	 * 設定されたルートディレクトリに書き込みが可能かを判断する
	 *
	 * @return bool
	 */
	public static function isWritable() {
		return self::$__canWrite;
	}

	/**
	 * @static
	 *
	 * 現在のステータスを確認する。isWritable()がfalseを返す状況では、
	 * なぜ書き込みができないかの事由が取得できる。isWritable()がtrueを
	 * 返す正常な状態では、このメソッドはOKを返す
	 *
	 * @return string
	 */
	public static function getStatus() {
		return self::$__status;
	}

	/**
	 * @static
	 * アップロードファイルのメタ情報を格納するためのデータベースを開く。
	 * データベースはSQLiteで、アップロードルート直下に配置される。
	 * DBスキーマは自動的に作成される。
	 *
	 * @return Zend_Db_Adapter_Abstract
	 */
	public static function openMetaDb() {
//***************************
		$dbname = NetB_IO_Utility::buildPath( self::getRootDirectory(), 'metainfo.sqlite' );
		$db = Zend_Db::factory( 'Pdo_Sqlite', array( 'dbname' => $dbname ) );
		$tableName = self::METAINFO_TABLE_NAME;
		if( count( $db->query("select * from sqlite_master where type = 'table' and name = '$tableName';")->fetchAll() ) == 0 ) {
			// メタ情報テーブルが存在しないので作成する
			$queries = array(
				"CREATE TABLE $tableName (" .
				join( ',', array(
					'id INTEGER PRIMARY KEY',
					'directory TEXT',
					'date DATETIME',
					'author TEXT',		// option of instance
					'client TEXT',		// option of instance
					'file_name TEXT',	// option
					'real_name TEXT',
					'size INTEGER',
					'memo TEXT'
				) ) .
				');',
				'CREATE INDEX idx_metainfo_01 ON metainfo ( directory );',
				'CREATE INDEX idx_metainfo_02 ON metainfo ( date );',
				'CREATE INDEX idx_metainfo_03 ON metainfo ( memo );'
			);
			foreach( $queries as $query ) $db->query( $query );
		}
		return $db;
	}

	/**
	 * @static
	 *
	 * アップロード先として使用するサブディレクトリを指定して、
	 * CoralUploadManagerの新しいインスタンスを作成する。
	 * このメソッドはインスタンスを生成するファクトリメソッドで、コンストラクタへのエイリアスでもある。
	 *
	 * @param string $dir 作成するインスタンスが使用するディレクトリ。ルートからの相対パスで指定する
	 * @param null|array $options オプションの連想配列。OPTION_AUTHOR_NAME、OPTION_CLIENT_INFOをキーとして利用可能
	 */
	public static function create($dir, $options = array()) {
		return new self($dir, $options);
	}

	/**
	 * このインスタンスが管理するディレクトリの名前
	 *
	 * @access protected
	 * @var string
	 */
	protected $_dir;

	/**
	 * このインスタンスが管理するディレクトリの実パス
	 *
	 * @access protected
	 * @var string
	 */
	protected $_path;

	/**
	 * このインスタンスを生成した、現在のセッションユーザ名
	 *
	 * @access protected
	 * @var string
	 */
	protected $_author;

	/**
	 * このインスタンスを生成した現在のセッションユーザのリモートホスト名
	 *
	 * @access protected
	 * @var string
	 */
	protected $_client;

	/**
	 * アップロード先として使用するディレクトリを指定して、
	 * CoralUploadManagerの新しいインスタンスを初期化する。
	 *
	 * @param string $dir このインスタンスが使用するディレクトリ。CoralUploadManager::getRootDirectory()で
	 *                    取得するルートからの相対パスで指定する
	 * @param null|array $options オプションの連想配列。OPTION_AUTHOR_NAME、OPTION_CLIENT_INFOをキーとして利用可能
	 */
	public function __construct($dir, $options = array()) {
		$this->_dir = trim( "$dir" );
		// 指定サブディレクトリの絶対パス作成
		$path = BaseIOUtility::buildPath( self::getRootDirectory(), $this->_dir );

		// 存在しないパスなのでディレクトリの生成を試みる
		if( ! file_exists( $path ) ) {
			mkdir( $path, self::$directoryUmask, true );
		}
		$this->_path = realpath( $path );

		// オプション指定の処理
		if( ! is_array( $options ) ) $options = array();
		$options = array_merge( array(
			self::OPTION_AUTHOR_NAME => '',
			self::OPTION_CLIENT_INFO => '(n/a)'
		), $options );
		foreach( $options as $key => $value ) {
			switch($key) {
			case self::OPTION_AUTHOR_NAME:
				// セッションユーザ名の設定
				$this->setAuthorName( $value );
				break;
			case self::OPTION_CLIENT_INFO:
				// リモートホストの設定
				$this->setClientInfo( $value );
				break;
			}
		}
	}

	/**
	 * このインスタンスが使用するアップロード先ディレクトリの名前を取得する
	 *
	 * @return string
	 */
	public function getDirName() {
		return $this->_dir;
	}

	/**
	 * このインスタンスが使用するアップロード先ディレクトリの絶対パスを取得する
	 *
	 * @return string
	 */
	public function getDirPath() {
		return $this->_path;
	}

	/**
	 * 現在のセッションユーザ名を取得する。この値は
	 * オプションで、コンストラクタまたはsetAuthorName()で明示的に
	 * 値を設定した場合のみ、有効な値を返す
	 *
	 * @return string
	 */
	public function getAuthorName() {
		return $this->_author;
	}
	/**
	 * 現在のセッションユーザ名を設定する。この値は
	 * オプションで、ファイル追加時のメタデータに記録される。
	 *
	 * @param string $author セッションユーザ名
	 * @return CoralUploadManager
	 */
	public function setAuthorName($author) {
		$this->_author = "$author";
		return $this;
	}

	/**
	 * 現在のセッションユーザのリモートホスト情報を取得する。
	 * この値はオプションで、コンストラクタまたはsetClientInfo()で
	 * 明示的に値を設定した場合のみ有効な値を返す
	 *
	 * @return string
	 */
	public function getClientInfo() {
		return $this->_client;
	}
	/**
	 * 現在のセッションユーザの、リモートホスト情報を設定する。
	 * この値はオプションで、ファイル追加時のメタデータに記録される。
	 *
	 * @param string $client リモートホスト情報。IPアドレスやホスト名などを指定する
	 * @return CoralUploadManager
	 */
	public function setClientInfo($client) {
		$this->_client = "$client";
		return $this;
	}

	/**
	 * 現在の永続化先ディレクトリに指定のファイルを追加しメタ情報を記録する
	 *
	 * @param string $path 追加するファイルの現在のパス
	 * @param string $baseName 永続化時のファイル名。重複する場合は自動的に番号が付与される
	 * @param null|string $displayName メタ情報に記録する表示ファイル名。アップロード時のファイル名などを指定する
	 * @param null|string $memo このファイルに関するメモ
	 * @return string 追加したファイルの絶対パス
	 */
	public function addFile($path, $baseName, $displayName = '', $memo = '') {

	    $baseName = trim("$baseName");
		$displayName = trim("$displayName");
		$memo = "$memo";

		if( empty( $baseName ) ) throw new BaseIOException( '不正なファイル名です' );

		$dest = $this->createFileName($baseName);
		if( empty($displayName) ) $displayName = basename( $dest );

		// ファイルの追加処理
		if( is_uploaded_file( $path ) ) {
			// HTTPでアップロードされたファイル
			move_uploaded_file( $path, $dest );
		} else {
			// その他のファイル
			rename( $path, $dest );
		}

		// 追加したファイルのアクセス権を変更する
		// ※：move_uploaded_file()で200になることへの対応
		chmod( $path, 0644 );

		// メタ情報追加
		$db = self::openMetaDb();//ooo
		$row = array(
			'directory' => $this->getDirName(),
			'date' => date('Y-m-d H:i:s'),
			'author' => $this->getAuthorName(),
			'client' => $this->getClientInfo(),
			'file_name' => $displayName,
			'real_name' => basename( $dest ),
			'size' => filesize( $dest ),
			'memo' => $memo
		);
		$db->insert( self::METAINFO_TABLE_NAME, $row );//ooo

		return realpath( $dest );
	}

	/**
	 * アップロード済みのファイルのメタ情報を取得する
	 *
	 * @param int|string $id ファイルのメタデータIDまたは実ファイル名（'real_name'列）
	 * @return array $idに一致するファイルメタデータのデータ配列
	 */
	public function getFileInfo($id) {
//***************************
	    $db = self::openMetaDb();
		$select = $db->select()
			->from( self::METAINFO_TABLE_NAME )
			->where( 'directory = ?', $this->getDirName() );

		if( NetB_Reflection_Utility::isInteger($id) ) {
			$select = $select->where( 'id = ?', $id );
		} else {
			$select = $select->where( 'real_name = ?', $id );
		}

		$row = $select->query()->fetch();
		return $row ? $row : null;
	}

	/**
	 * このディレクトリにアップロード済みのすべてのファイルのメタ情報を返す
	 *
	 * @return array
	 */
	public function getAllFileInfo() {
//***************************
	    $result = array();
		$db = self::openMetaDb();

		$rows = $db->select()
			->from( self::METAINFO_TABLE_NAME )
			->where( 'directory = ?', $this->getDirName() )
			->query()
			->fetchAll();

		foreach($rows as $row) {
			$path = NetB_IO_Utility::buildPath( $this->getDirPath(), $row['real_name'] );
			if( ! file_exists( $path ) ) {
				// ファイルの実態が存在しない場合はメタ情報の削除を試みる
				try {
					$db->delete(
						self::METAINFO_TABLE_NAME,
						$db->quoteInto( 'id = ?', $row['id'] )
					);
				} catch(Exception $err) {
					// 例外が発生しても何もしない
				}
			} else {
				// ファイルの実態が存在する場合のみ結果に含める
				$result[] = $row;
			}
		}
		return $result;
	}

	/**
	 * アップロードされたファイルを削除する
	 *
	 * @param int|string $file 削除するファイルのメタデータIDまたは実ファイル名
	 * @return bool ファイル削除の成否
	 */
	public function removeFile($file) {
//***************************
	    $row = $this->getFileInfo($file);

		if( ! $row ) return false;
		try {
			// ファイルを削除する
			$path = BaseIOUtility::buildPath( $this->getDirPath(), $row['real_name'] );
			if( ! unlink( $path ) ) {
				throw new BaseIOException( "ファイル '{$row['file_name']}' を削除できません" );
			}

			$db = self::openMetaDb();
			try {
				$count = $db->delete(
					self::METAINFO_TABLE_NAME, $db->quoteInto( 'id = ?', $row['id'] )
				);
				return $count ? true : false;
			} catch(\Exception $err) {
				return false;
			}
		} catch(\Exception $err) {
		}
		return false;
	}

	/**
	 * 指定のベース名を基準に、現在の永続化先ディレクトリ内でユニークなファイル名を
	 * 生成する。
	 * 重複がない場合は$baseNameがそのまま採用されるが、重複する名前が存在する場合は
	 * 連番がサフィックスされる。
	 *
	 * @param string $baseName 基準とするファイル名
	 * @return $baseNameを基準に、同一ディレクトリで重複がない新しいファイル名
	 */
	protected function createFileName($baseName) {
		$path = BaseIOUtility::buildPath( $this->getDirPath(), $baseName );
		if( ! is_file( $path ) ) return $path;

		$i = 1;
		while( true ) {
			$num = substr( '00000' . $i++, -5 );
			$path = BaseIOUtility::buildPath( $this->getDirPath(), "$baseName.$num" );
			if( ! is_file( $path ) ) return $path;
		}
	}
}

