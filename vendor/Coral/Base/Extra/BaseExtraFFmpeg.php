<?php
namespace Coral\Base\Extra;

require_once 'NetB/Reflection/Utility.php';
require_once 'NetB/Shell/Command.php';
require_once 'NetB/Extra/FFmpeg/Exception.php';
require_once 'NetB/Extra/FFmpeg/Information.php';

class BaseExtraFFmpeg {
	//-------------------------------------------------------------------------
	// 定数
	/**
	 * @const
	 *
	 * FFmpegのコマンドを指定するオプション定数
	 *
	 * @var string
	 */
	const FFMPEG_COMMAND = 'command';

	/**
	 * @const
	 *
	 * デフォルトのFFmpegコマンド。ffmpeg自体が実行環境でパスが通っていない位置に
	 * 設置されていた場合はインスタンスのsetCommand()でフルパスを指定する。
	 *
	 * @var string
	 */
	const DEFAULT_FFMPEG_COMMAND = 'ffmpeg';

	/**
	 * @const
	 *
	 * ファイルフォーマットを指定するオプション定数
	 *
	 * @var string
	 */
	const FILE_FORMAT = '-f';

	/**
	 * @const
	 *
	 * 再生時間を指定するオプション定数
	 *
	 * @var string
	 */
	const DURATION = '-t';

	/**
	 * @const
	 *
	 * 映像フォーマットを指定するオプション定数
	 *
	 * @var string
	 */
	const VIDEO_FORMAT = '-vcodec';

	/**
	 * @const
	 *
	 * 画面サイズを指定するオプション定数
	 *
	 * @var string
	 */
	const VIDEO_SIZE = '-s';

	/**
	 * @const
	 *
	 * 映像フレームレートを指定するオプション定数
	 *
	 * @var string
	 */
	const VIDEO_FRAME_RATE = '-r';

	/**
	 * @const
	 *
	 * 映像ビットレートを指定するオプション定数
	 *
	 * @var string
	 */
	const VIDEO_BIT_RATE = '-b';

	/**
	 * @const
	 *
	 * 音声フォーマットを指定するオプション定数
	 *
	 * @var string
	 */
	const AUDIO_FORMAT = '-acodec';

	/**
	 * @const
	 *
	 * 音声サンプリングレートを指定するオプション定数
	 *
	 * @var string
	 */
	const AUDIO_SAMPLING_RATE = '-ar';

	/**
	 * @const
	 *
	 * 音声ビットレートを指定するオプション定数
	 *
	 * @var string
	 */
	const AUDIO_BIT_RATE = '-ab';

	/**
	 * @const
	 *
	 * 音声チャンネルを指定するオプション定数
	 *
	 * @var string
	 */
	const AUDIO_CHANNELS = '-ac';


	//-------------------------------------------------------------------------
	// スタティックメンバ
	/**
	 * @static
	 *
	 * オプション指定用のすべての定数値を格納した配列
	 *
	 * @var array
	 */
	protected static $__option_keys = array();

	/**
	 * @static
	 *
	 * オプション指定用のすべての定数値を配列で取得する
	 *
	 * @return array
	 */
	public static function getOptionKeys() {
		if( empty( self::$__option_keys ) ) {
			self::$__option_keys = array(
				self::FILE_FORMAT,
				self::DURATION,
				self::VIDEO_FORMAT,
				self::VIDEO_SIZE,
				self::VIDEO_FRAME_RATE,
				self::VIDEO_BIT_RATE,
				self::AUDIO_FORMAT,
				self::AUDIO_SAMPLING_RATE,
				self::AUDIO_BIT_RATE,
				self::AUDIO_CHANNELS
			);
		}

		return array_merge( array(), self::$__option_keys );
	}

	//-------------------------------------------------------------------------
	// インスタンスメンバ
	/**
	 * @protected
	 *
	 * FFmpegコマンド
	 *
	 * @var string
	 */
	protected $_command;

	/**
	 * @protected
	 *
	 * 入力ファイル
	 *
	 * @var string
	 */
	protected $_input;

	/**
	 * @protected
	 *
	 * オプションの連想配列
	 *
	 * @var array
	 */
	protected $_options;

	//-------------------------------------------------------------------------
	// コンストラクタ
	/**
	 * @constructor
	 *
	 * NetB_Extra_FFmpegの新しいインスタンスを初期化する
	 *
	 * @param string|null $input 入力ファイルのパス
	 * @param null|array $options オプション引数の連想配列
	 */
	public function __construct($input = null, $options = null) {
		if( ! is_string( $input ) ) $input = null;
		if( ! is_array( $options) ) $options = array();

		$options = array_merge( array(
			self::FFMPEG_COMMAND => self::DEFAULT_FFMPEG_COMMAND
		), $options );

		$this->_options = array();
		$this
			->setInput( $input )
			->setOptions( $options );
	}

	/**
	 * オプションを設定する
	 *
	 * @param null|array $options オプション値を格納した連想配列。
	 * キーはこのクラスのオプション定数で定義されているもののみ有効
	 * @return NetB_Extra_FFmpeg
	 */
	public function setOptions($options = null) {
		if( ! is_array( $options ) ) $options = array();
		foreach( $options as $key => $value ) {
			switch( $key ) {
			case self::FILE_FORMAT:
				$this->setFileFormat( $value );
				break;
			case self::DURATION:
				$this->setDuration( $value );
				break;
			case self::VIDEO_FORMAT:
				$this->setVideoFormat( $value );
				break;
			case self::VIDEO_SIZE:
				$this->setVideoSize( $value );
				break;
			case self::VIDEO_FRAME_RATE:
				$this->setVideoFrameRate( $value );
				break;
			case self::VIDEO_BIT_RATE:
				$this->setVideoBitRate( $value );
				break;
			case self::AUDIO_FORMAT:
				$this->setAudioFormat( $value );
				break;
			case self::AUDIO_SAMPLING_RATE:
				$this->setAudioSamplingRate( $value );
				break;
			case self::AUDIO_BIT_RATE:
				$this->setAudioBitRate( $value );
				break;
			case self::AUDIO_CHANNELS:
				$this->setAudioChannels( $value );
				break;
			case self::FFMPEG_COMMAND:
				$this->setCommand( $value );
				break;
			}
		}

		return $this;
	}

	//-------------------------------------------------------------------------
	// コマンド設定

	/**
	 * FFmpegコマンドを取得する
	 *
	 * @return string FFmpegコマンド
	 */
	public function getCommand() {
		return $this->_command;
	}
	/**
	 * FFmpegコマンドを設定する。実行時環境でffmpegにパスが通っていない場合は
	 * このメソッドでフルパスを指定する必要がある。
	 *
	 * @param string $command ffmpegコマンド
	 * @return NetB_Extra_FFmpeg
	 */
	public function setCommand($command) {
		$command = "$command";
		if( empty( $command ) ) $command = self::DEFAULT_FFMPEG_COMMAND;
		$this->_command = $command;
		return $this;
	}

	//-------------------------------------------------------------------------
	// 入力設定関連

	/**
	 * 入力ファイルが設定されているかを判断する
	 *
	 * @return bool 入力ファイルが設定されている場合はtrue、それ以外はfalse
	 */
	public function hasInput() {
		return ! empty( $this->_input );
	}

	/**
	 * 設定されている入力ファイルのパスを取得する。
	 * このプロパティの値はffmpegの'-i'オプションとして使用される。
	 *
	 * @return string 入力ファイルのパス
	 */
	public function getInput() {
		return $this->_input;
	}
	/**
	 * 入力ファイルのパスを設定する。
	 * $inputが実在するパスでない場合はなにも処理されない
	 * このプロパティの値はffmpegの'-i'オプションとして使用される。
	 *
	 * @param string $input 入力ファイルのパス
	 * @return NetB_Extra_FFmpeg
	 */
	public function setInput($input) {
		$input = trim( "$input" );
		if( is_file( $input ) ) $this->_input = $input;

		return $this;
	}

	//-------------------------------------------------------------------------
	// メインオプション

	/**
	 * 出力するファイルのフォーマットを取得する
	 * このプロパティの値はffmpegの'-f'オプションとして使用される。
	 *
	 * @return string
	 */
	public function getFileFormat() {
		return $this->_options[ self::FILE_FORMAT ];
	}
	/**
	 * 出力ファイルのフォーマットを設定する。
	 * $formatが空文字またはnullの場合はフォーマット指定がクリアされる
	 * このプロパティの値はffmpegの'-f'オプションとして使用される。
	 *
	 * @param null|string $format 出力ファイルフォーマット
	 * @return NetB_Extra_FFmpeg
	 */
	public function setFileFormat($format) {
		$format = trim( "$format" );
		if( ! empty( $format ) ) {
			$this->_options[ self::FILE_FORMAT ] = $format;
		} else {
			unset( $this->_options[ self::FILE_FORMAT ] );
		}

		return $this;
	}

	/**
	 * 出力ファイルの再生時間を秒単位で取得する。
	 * 再生時間が未設定の場合はnullを返す。
	 * このプロパティの値はffmpegの'-t'オプションとして使用される。
	 *
	 * @return null|float
	 */
	public function getDuration() {
		return $this->_options[ self::DURATION ];
	}
	/**
	 * 出力ファイルの再生時間を秒単位で設定する。
	 * $durationが0より大きい数値でない場合は設定は無視され、
	 * nullを渡した場合は再生時間の設定がクリアされる。
	 * このプロパティの値はffmpegの'-t'オプションとして使用される。
	 *
	 * @param null|float $duration 設定する再生時間
	 * @return NetB_Extra_FFmpeg
	 */
	public function setDuration($duration) {
		$duration = trim("$duration");

		if( NetB_Reflection_Utility::isPositiveNumeric( $duration ) ) {
			$this->_options[ self::DURATION ] = ((float)$duration);
		} else if( NetB_Reflection_Utility::isEmpty( $duration ) ) {
			unset( $this->_options[ self::DURATION ] );
		}

		return $this;
	}

	//-------------------------------------------------------------------------
	// 映像オプション

	/**
	 * 映像エンコードを取得する。
	 * 映像エンコードが未指定の場合はnullを返す。
	 * このプロパティの値はffmpegの'-vcodec'オプションとして使用される。
	 *
	 * @return null|string
	 */
	public function getVideoFormat() {
		return $this->_options[ self::VIDEO_FORMAT ];
	}
	/**
	 * 映像エンコードを設定する。
	 * $formatがnullまたは空の文字列の場合、エンコード指定はクリアされる。
	 * このプロパティの値はffmpegの'-vcodec'オプションとして使用される。
	 *
	 * @param string $format 映像エンコードフォーマット
	 * @return NetB_Extra_FFmpeg
	 */
	public function setVideoFormat($format) {
		$format = trim( "$format" );
		if( ! empty( $format ) ) {
			$this->_options[ self::VIDEO_FORMAT ] = $format;
		} else {
			unset( $this->_options[ self::VIDEO_FORMAT ] );
		}

		return $this;
	}

	/**
	 * 映像の画面サイズを取得する。
	 * サイズ未指定時はnullを返す。
	 * このプロパティの値はffmpegの'-s'オプションとして使用される。
	 *
	 * @return null|string
	 */
	public function getVideoSize() {
		return $this->_options[ self::VIDEO_SIZE ];
	}
	/**
	 * 映像の画面サイズを設定する。
	 * $sizeがnullまたは空の文字列の場合、サイズ設定はクリアされる。
	 * このプロパティの値はffmpegの'-s'オプションとして使用される。
	 *
	 * @param string $size 設定する画面サイズ
	 * @return NetB_Extra_FFmpeg
	 */
	public function setVideoSize($size) {
		$size = trim("$size");
		if( ! empty( $size ) ) {
			$this->_options[ self::VIDEO_SIZE ] = $size;
		} else {
			unset( $this->_options[ self::VIDEO_SIZE ] );
		}

		return $this;
	}

	/**
	 * 映像のフレームレートをfpsで取得する。
	 * 映像フレームレートが未設定の場合はnullを返す
	 * このプロパティの値はffmpegの'-r'オプションとして使用される。
	 *
	 * @return null|float
	 */
	public function getVideoFrameRate() {
		return $this->_options[ self::VIDEO_FRAME_RATE ];
	}
	/**
	 * 映像フレームレートをfpsで設定する。
	 * $rateが0より大きい数値でない場合、設定は無視され、
	 * nullを渡すとフレームレート設定がクリアされる。
	 * このプロパティの値はffmpegの'-r'オプションとして使用される。
	 *
	 * @param float $rate 映像のフレームレート
	 * @return NetB_Extra_FFmpeg
	 */
	public function setVideoFrameRate($rate) {
		$rate = trim("$rate");

		if( NetB_Reflection_Utility::isPositiveNumeric( $rate ) ) {
			$this->_options[ self::VIDEO_FRAME_RATE ] = ((float)$rate);
		} else if( NetB_Reflection_Utility::isEmpty( $rate ) ) {
			unset( $this->_options[ self::VIDEO_FRAME_RATE ] );
		}

		return $this;
	}

	/**
	 * 映像のビットレートをbpsで取得する。
	 * 映像ビットレートが未指定の場合はnullを返す。
	 * このプロパティの値はffmpegの'-b'オプションとして使用される。
	 *
	 * @return null|int
	 */
	public function getVideoBitRate() {
		return $this->_options[ self::VIDEO_BIT_RATE ];
	}
	/**
	 * 映像ビットレートをbpsで設定する。値は'k'表記も許容される。
	 * $rateが0より大きい数値でない場合、設定は無視され、
	 * nullを渡すとビットレート設定がクリアされる。また実数を渡した場合は整数に丸められる。
	 * このプロパティの値はffmpegの'-b'オプションとして使用される。
	 *
	 * @param int|string $rate 映像ビットレート
	 * @return NetB_Extra_FFmpeg
	 */
	public function setVideoBitRate($rate) {
		// 末尾に'k'があった場合は1,000倍と見なす
		$rate = preg_replace( '/(.*)k$/i', '${1}000', trim("$rate") );

		if( NetB_Reflection_Utility::isPositiveNumeric( $rate ) ) {
			$this->_options[ self::VIDEO_BIT_RATE ] = ((int)$rate);
		} else if( NetB_Reflection_Utility::isEmpty( $rate ) ) {
			unset( $this->_options[ self::VIDEO_BIT_RATE ] );
		}

		return $this;
	}

	//-------------------------------------------------------------------------
	// 音声オプション

	/**
	 * 音声エンコードを指定する。
	 * 音声エンコードが未設定の場合はnullを返す。
	 * このプロパティの値はffmpegの'-acodec'オプションとして使用される。
	 *
	 * @return null|string
	 */
	public function getAudioFormat() {
		return $this->_options[ self::AUDIO_FORMAT ];
	}
	/**
	 * 音声エンコードを設定する。
	 * $formatがnullまたは空の文字列の場合、エンコード指定はクリアされる。
	 * このプロパティの値はffmpegの'-acodec'オプションとして使用される。
	 *
	 * @param string $format 音声エンコードフォーマット
	 * @return NetB_Extra_FFmpeg
	 */
	public function setAudioFormat($format) {
		$format = trim( "$format" );
		if( ! empty( $format ) ) {
			$this->_options[ self::AUDIO_FORMAT ] = $format;
		} else {
			unset( $this->_options[ self::AUDIO_FORMAT ] );
		}

		return $this;
	}

	/**
	 * 音声のサンプリングレートをHz単位で取得する。
	 * サンプリングレートが未指定の場合はnullを返す。
	 * このプロパティの値はffmpegの'-ar'オプションとして使用される。
	 *
	 * @return null|int
	 */
	public function getAudioSamplingRate() {
		return $this->_options[ self::AUDIO_SAMPLING_RATE ];
	}
	/**
	 * 音声のサンプリングレートをHz単位で設定する。
	 * $rateが0以上の整数値でない場合、設定は無視され、
	 * nullを渡すとサンプリングレートの設定はクリアされる。
	 * このプロパティの値はffmpegの'-ar'オプションとして使用される。
	 *
	 * @param int $rate 音声サンプリングレート
	 * @return NetB_Extra_FFmpeg
	 */
	public function setAudioSamplingRate($rate) {
		$rate = trim( "$rate" );
		if( NetB_Reflection_Utility::isPositiveInteger( $rate ) ) {
			$this->_options[ self::AUDIO_SAMPLING_RATE ] = ((int)$rate);
		} else if( NetB_Reflection_Utility::isEmpty( $rate ) ) {
			unset( $this->_options[ self::AUDIO_SAMPLING_RATE ] );
		}

		return $this;
	}

	/**
	 * 音声のビットレートをbpsで取得する
	 * 音声ビットレートが未設定の場合はnullを返す。
	 * このプロパティの値はffmpegの'-ab'オプションとして使用される。
	 *
	 * @return null|int
	 */
	public function getAudioBitRate() {
		return $this->_options[ self::AUDIO_BIT_RATE ];
	}
	/**
	 * 音声ビットレートをbpsで設定する。値は'k'表記も許容される。
	 * $rateが0より大きい数値でない場合、設定は無視され、
	 * nullを渡すとビットレート設定がクリアされる。また実数を渡した場合は整数に丸められる。
	 * このプロパティの値はffmpegの'-ab'オプションとして使用される。
	 *
	 * @param int|string $rate 音声ビットレート
	 * @return NetB_Extra_FFmpeg
	 */
	public function setAudioBitRate($rate) {
		// 末尾に'k'があった場合は1,000倍と見なす
		$rate = preg_replace( '/(.*)k$/i', '${1}000', trim("$rate") );

		if( NetB_Reflection_Utility::isPositiveNumeric( $rate ) ) {
			$this->_options[ self::AUDIO_BIT_RATE ] = ((int)$rate);
		} else if( NetB_Reflection_Utility::isEmpty( $rate ) ) {
			unset( $this->_options[ self::AUDIO_BIT_RATE ] );
		}

		return $this;
	}

	/**
	 * 音声チャネル数を取得する。
	 * 音声チャネル数が未設定の場合はnullを返す。
	 * このプロパティの値はffmpegの'-ac'オプションとして使用される。
	 *
	 * @return null|int
	 */
	public function getAudioChannels() {
		return $this->_options[ self::AUDIO_CHANNELS ];
	}
	/**
	 * 音声チャネル数を設定する。
	 * $channelsが正の整数でない場合、設定は無視され、
	 * nullを渡すと音声チャネル設定はクリアされる。
	 * このプロパティの値はffmpegの'-ac'オプションとして使用される。
	 *
	 * @param int $channels 音声チャネル数
	 * @return NetB_Extra_FFmpeg
	 */
	public function setAudioChannels($channels) {
		$channels = trim( "$channels" );
		if( NetB_Reflection_Utility::isPositiveInteger( $channels ) ) {
			$this->_options[ self::AUDIO_CHANNELS ] = ((int)$channels);
		} else if( NetB_Reflection_Utility::isEmpty( $channels ) ) {
			unset( $this->_options[ self::AUDIO_CHANNELS ] );
		}

		return $this;
	}

	//-------------------------------------------------------------------------
	// パブリックメソッド

	/**
	 * FFmpegのバージョン情報を取得する
	 *
	 * @return NetB_Extra_FFmpeg_Information_Version
	 */
	public function getVersionInfo() {
		return $this->_execCommand( array( '-version' ) )->getVersionInfo();
	}

	/**
	 * 現在の入力ファイルの情報を取得する。
	 * 入力ファイルが未設定または処理できないフォーマットの場合は例外がスローされる
	 *
	 * @return NetB_Extra_FFmpeg_Information_Input
	 */
	public function getMediaInfo() {
		$this->_checkInputFile();

		return $this->_execCommand( array(), null, true )->getInputInfo();
	}

	/**
	 * 現在の入力ファイルのサムネイルを出力する。
	 * 画像ファイルのフォーマットは$outputの拡張子から自動的に判別される
	 *
	 * @param string $output サムネイル出力先のパス
	 * @param null|float $start_time キャプチャする再生時刻。省略時はメディアの開始時刻になる
	 * @return NetB_Extra_FFmpeg_Information_Output
	 */
	public function generateThumbnail($output, $start_time = null) {
		$output = trim( "$output" );
		if( empty($output) ) throw new NetB_Extra_FFmpeg_Exception( 'output file is not specified.' );


		$media = $this->getMediaInfo()->getGeneralInfo();
		if( ! NetB_Reflection_Utility::isPositiveNumeric( $start_time ) ) {
			$start_time = $media['start'];
		}

		$options = array(
			'-vframes' => 1,
			'-f' => 'image2',
			'-ss' => (float)$start_time
		);

		$info = $this->_execCommand( $options, $output )->getOutputInfo();
		return $info[0];
	}

	/**
	 * 現在のオプション設定で変換処理を実行する
	 *
	 * @param string $output 出力ファイルのパス
	 * @return NetB_Extra_FFmpeg_Information_Output
	 */
	public function convertTo($output) {
		$output = trim( "$output" );
		if( empty($output) ) throw new NetB_Extra_FFmpeg_Exception( 'output file is not specified.' );

		// 入力チェック
		$this->_checkInputFile();

		$info = $this->_execCommand( $this->_options, $output )->getOutputInfo();
		return $info[0];
	}
	
	//-------------------------------------------------------------------------
	// プロテクトメソッド・プライベートメソッド
	/**
	 * @protected
	 *
	 * 指定のオプションでffmpegコマンドを実行する
	 *
	 * @param array $options オプション引数。-i、-yおよび出力ファイルの設定は自動的に付与される
	 * @param null|string $output 出力ファイルのパス
	 * @param null|bool $need_input 入力ファイル情報が必須かのフラグで、trueを設定して入力ファイル情報がない場合は例外。省略時はfalse
	 * @return NetB_Extra_FFmpeg_Information ffmpegコマンドの実行結果を格納した情報クラス
	 */
	protected function _execCommand(array $options, $output = null, $need_input = false) {
		// FFmpegコマンドの構築
		$command = NetB_Shell_Command::create( $this->_command, array( '-i' => $this->getInput() ) )
			->setOption( $options );

		// $outputが指定されていたら上書き許可で設定する
		if( ! empty( $output ) ) {
			// 出力指定がある場合は入力必須
			$need_input = true;
			$command->setOption( '-y' )->setOption( $output );
		}
		// シェルコマンド実行
		$result = exec( "$command 2>&1", $logs, $return_code );

		// 出力結果を解析
		$info = new NetB_Extra_FFmpeg_Information( $logs );

		// バージョン情報が含まれない場合はコマンド実行エラー
		if( ! $info->hasVersionInfo() ) {
			// $resultは標準出力の最終行なのでそれをメッセージにして例外をスロー
			throw new NetB_Extra_FFmpeg_Exception( $result );
		}
		// 入力情報必須フラグがONの場合
		if( $need_input && ! $info->hasInputInfo() ) {
			// 入力情報が返らないということは未対応フォーマット等が
			// 考えられるので例外をスロー
			throw new NetB_Extra_FFmpeg_Exception( $result );
		}

		// 出力指定があるが出力情報がない場合
		if( ! empty( $output ) && ! $info->hasOutputInfo() ) {
			throw new NetB_Extra_FFmpeg_Exception( $result );
		}

		// 出力ファイルサイズが0バイトの場合
		if( $info->hasOutputInfo() && filesize( $output ) == 0 ) {
			$last_line = $logs[ count($logs) - 1 ];
			throw new NetB_Extra_FFmpeg_Exception( "output failed. reason : '$last_line'" );
		}
		// 解析結果を返す
		return $info;
	}

	/**
	 * @protected
	 *
	 * 入力ファイルが設定されているかを検査するチェックメソッド。
	 * 入力ファイルが設定されていない場合は例外をスローする。
	 *
	 * @return NetB_Extra_FFmpeg
	 */
	protected function _checkInputFile() {
		if( ! $this->hasInput() ) throw new NetB_Extra_FFmpeg_Exception( 'input file is not specified.' );
		return $this;
	}

}
