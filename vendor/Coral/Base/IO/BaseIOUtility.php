<?php
namespace Coral\Base\IO;

class BaseIOUtility {
	/**
	 * ファイル読み込みバッファの設定可能な最小値
	 *
	 * @var int
	 */
	const READ_BUFFER_MINIMUM = 128;

	/**
	 * ファイル読み込みバッファの設定可能な最大値
	 *
	 * @var int
	 */
	const READ_BUFFER_MAXIMUM = 65535;

	/**
	 * ASCIIエンコード
	 *
	 * @var string
	 */
	const ENCODING_ASCII = 'ASCII';

	/**
	 * JISエンコード
	 *
	 * @var string
	 */
	const ENCODING_JIS = 'JIS';

	/**
	 * UTF-8エンコード
	 *
	 * @var string
	 */
	const ENCODING_UTF8 = 'UTF-8';

	/**
	 * WindowsにおけるEUC-JPエンコード
	 *
	 * @var string
	 */
	const ENCODING_WIN_EUC_JP = 'eucjp-win';

	/**
	 * WindowsにおけるShift_JISエンコード
	 *
	 * @var string
	 */
	const ENCODING_WIN_SJIS = 'sjis-win';

	/**
	 * EUC-JPエンコード
	 *
	 * @var string
	 */
	const ENCODING_EUC_JP = 'EUC-JP';

	/**
	 * Shift_JISエンコード
	 *
	 * @var string
	 */
	const ENCODING_SJIS = 'SJIS';

	/**
	 * ファイル読み込みバッファサイズ
	 *
	 * @var int
	 */
	protected static $_read_buffer_size = 512;

	/**
	 * 文字エンコードの検出順序リスト
	 *
	 * @var array
	 */
	protected static $_encoding_list = null;

	/**
	 * ファイル読み込みバッファサイズを取得する
	 *
	 * @return int
	 */
	public static function getReadBufferSize() {
		return self::$_read_buffer_size;
	}

	/**
	 * ファイル読み込みバッファサイズを設定する
	 *
	 * @param int $size バッファサイズ。128～65535の間で設定される
	 */
	public static function setReadBufferSize($size) {
		$size = (int)$size;
		if( $size < self::READ_BUFFER_MINIMUM ) $size = self::READ_BUFFER_MINIMUM;
		if( $size > self::READ_BUFFER_MAXIMUM ) $size = self::READ_BUFFER_MAXIMUM;

		self::$_read_buffer_size = $size;
	}

	/**
	 * 文字エンコードの検出に使用されるエンコードリストを取得する
	 *
	 * @return array
	 */
	public static function getEncodingList() {
		if( self::$_encoding_list == null ) {
			self::$_encoding_list = array(
				self::ENCODING_ASCII,
				self::ENCODING_JIS,
				self::ENCODING_EUC_JP,
				self::ENCODING_WIN_EUC_JP,
				self::ENCODING_UTF8,
				self::ENCODING_WIN_SJIS,
				self::ENCODING_SJIS
			);
		}
		return self::$_encoding_list;
	}

	/**
	 * 文字エンコードの検出に使用されるエンコードリストを設定する
	 *
	 * @param array $encList 文字エンコードの配列
	 */
	public static function setEncodingList(array $encList) {
		self::$_encoding_list = $encList;
	}

	/**
	 * 指定のファイルの文字エンコードを検出する。
	 * 第二引数$limitでバッファサイズを指定すると、そのサイズを超えない長さだけ読み込んで判断する。
	 * 省略時はファイルをすべて読み込む。
	 *
	 * @param string $path エンコードを検出するファイルのパス
	 * @param int $limit エンコード検出に使用する文字列バッファのサイズ。省略時は0
	 * @return string $pathの文字エンコード名
	 */
	public static function detectFileEncoding($path, $limit = 0) {
		// 判断に使用する文字列バッファ
		$buffer = '';

		// バッファ上限をFIX
		$limit = (int)$limit;
		if( $limit < 0 ) $limit = 0;

		// ファイルハンドルを開く
		$handle = @fopen( $path, "r" );
		if( ! $handle ) throw new \Exception( "cannot open file '$path'" );

		$handle_closed = false;
		try {
			while( ! feof( $handle ) ) {
				$buffer .= fgets( $handle, self::getReadBufferSize() );
				if( $limit ) {
					// バッファ上限が設定されていたら上限までの読み込みで中断
					if( strlen( $buffer ) > $limit ) break;
				}
			}
			fclose( $handle );
			$handle_closed = true;
		} catch(\Exception $err) {
			if( ! $handle_closed ) {
				fclose( $handle );
			}
			throw $err;
		}

		// 文字エンコードを取得
		return self::detectEncoding( $buffer );
	}

	/**
	 * 指定の文字列の文字エンコードを検出する。このメソッドは
	 * エンコードの検出順序にBaseIOUtility::getEncodingList()で取得できる
	 * エンコードリストを適用する点を除いて、mb_detect_encoding()を呼び出すことと変わりがない
	 *
	 * @param string $str エンコードを検出する文字列
	 * @return string $strの文字エンコード名
	 */
	public static function detectEncoding($str) {
		return mb_detect_encoding( $str, join( ', ', self::getEncodingList() ) );
	}

	/**
	 * 指定のソースファイルの文字エンコードを指定のエンコードで保存しなおす。
	 * 第四引数$destPathを省略すると$srcPathを上書きする動作になるが、第三引数$overwriteがfalse
	 * の場合は例外がスローされる
	 *
	 * @param string $srcPath 変換元のソースファイルパス
	 * @param string $toEnc 保存するファイルのエンコード
	 * @param bool $overwrite 保存先のファイルが存在する場合に上書きするかのフラグ
	 * @param string $destPath 保存先ファイルのパス。省略時は$srcPathが保存先になる
	 */
	public static function convertFileEncoding($srcPath, $destPath = null, $toEnc = null, $overwrite = false) {
		// 保存先パスのFIX
		if( empty( $destPath ) ) $destPath = $srcPath;

		// 保存エンコードのFIX
		if( empty( $toEnc ) ) $toEnc = mb_internal_encoding();

		// 上書きフラグのFIX
		$overwrite = $overwrite ? true : false;

		// 上書き未許可で元ファイルを上書きしようとしたら例外
		if( ! $overwrite && self::pathEquals( $srcPath, $destPath ) ) {
			throw new \Exception( "cannot overwrite file. '$srcPath' equals '$destPath'." );
		}

		// 上書き未許可で保存先が存在する場合は例外
		if( ! $overwrite && realpath( $destPath ) ) {
			throw new \Exception( "cannot overwrite file. '$destPath' already exists." );
		}

		// 変換実行
		$src = @file_get_contents( $srcPath );
		@file_put_contents( $destPath, mb_convert_encoding( $src, $toEnc, self::detectEncoding( $src ) ) );
	}

	/**
	 * 指定のパス同士が同じパスを示しているかを比較する。
	 * このメソッドは、稼動しているオペレーティングシステムがWindowsかによって
	 * 動作が若干異なる。Windows上では大文字小文字が区別されずに比較される。
	 *
	 * @param string $srcPath 比較するパスの1つ
	 * @param string $destPath 比較するもう一方のパス
	 * @return bool
	 */
	public static function pathEquals($srcPath, $destPath) {
		$srcPath = trim( realpath( $srcPath ) );
		$destPath = trim( realpath( $destPath ) );

		if( strtoupper( substr( PHP_OS, 0, 3 ) ) === 'WIN' ) {
			// Windows上で稼動中はパスの大文字小文字を区別しない
			return ( strcasecmp( $srcPath, $destPath ) == 0 ? true : false );
		} else {
			return ( strcmp( $srcPath, $destPath ) == 0 ? true : false );
		}
	}

	/**
	 * 指定の2つのパスを結合してパス文字列を返す。
	 *
	 * @param string $path1 結合するパスの上位部分
	 * @param string $path2 結合するパスの下位部分
	 * @return string $path1と$path2をDIRECTORY_SEPARATORで適切に結合したパス文字列
	 */
	public static function buildPath($path1, $path2, $separator = null) {
		$separator = "$separator";
		if( empty( $separator ) ) $separator = DIRECTORY_SEPARATOR;
		return join( $separator, array(
			preg_replace( '/\\' . $separator . '$/i', '', trim("$path1") ),
			mb_ereg_replace( '/^\\' . $separator . '/i', '', trim("$path2") )
		) );
	}
}
