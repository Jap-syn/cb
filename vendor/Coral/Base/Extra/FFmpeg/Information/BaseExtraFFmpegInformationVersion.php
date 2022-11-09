<?php
namespace Coral\Base\Extra\FFmpeg\Information;

require_once 'NetB/Extra/FFmpeg/Information/Interface.php';

/**
 * @class
 *
 * �o�[�W����������͂���NetB_Extra_FFmpeg_Information_Interface�����N���X
 */
class BaseExtraFFmpegInformationVersion implements BaseExtraFFmpegInformationInterface {
	/**
	 * @static
	 * @protected
	 *
	 * �o�[�W�������̊J�n�s�Ɉ�v����Perl�݊����K�\���̃p�^�[��
	 *
	 * @var string
	 */
	protected static $__firstLine = '/^FFmpeg version ([^,]+), (.+)$/';

	/**
	 * @static
	 * @protected
	 *
	 * �o�[�W�������̊e�s����͂��鐳�K�\���p�^�[���̔z��
	 *
	 * @var array
	 */
	protected static $__expressions;

	/**
	 * @static
	 *
	 * �w��̍s�����񂪃o�[�W�������J�n�s�Ɉ�v���邩�𔻒f����
	 *
	 * @param string $line ��������s������
	 * @return bool $line���o�[�W�������̊J�n�s�̏ꍇ��true�A����ȊO��false
	 */
	public static function canHandle($line) {
		return preg_match( self::$__firstLine, $line ) ? true : false;
	}

	/**
	 * @static
	 * @protected
	 *
	 * �o�[�W�������̊e�s����͂��鐳�K�\���̔z����擾����
	 *
	 * @return array
	 */
	protected static function _getExpressions() {
		if( self::$__expressions == null ) {
			self::$__expressions = array(
				'config' => '/  configuration: (.+)$/',
				'library' => '/  ([^\s]+) version: (.+)$/',
				'build_info' => '/  built on ([^,]+), ([^:]+: .+)$/'
			);
		}
		return self::$__expressions;
	}

	/**
	 * @protected
	 *
	 * FFmpeg�̃o�[�W����������
	 *
	 * @var string
	 */
	protected $_version;

	/**
	 * @protected
	 *
	 * FFmpeg�̒��쌠��񕶎���
	 *
	 * @var string
	 */
	protected $_copyright;

	/**
	 * @protected
	 *
	 * ��v���C�u�����̏����i�[����z��B
	 * �e�v�f�̓L�[�Ƀ��C�u�������́A�l�Ƀo�[�W���������񂪊i�[�����
	 *
	 * @var array
	 */
	protected $_libraries = array();

	/**
	 * @protected
	 *
	 * FFmpeg��configure�����i�[����z��
	 *
	 * @var array
	 */
	protected $_configuration;

	/**
	 * @protected
	 *
	 * FFmpeg�̃r���h�����i�[����A�z�z��B
	 * �r���h���t������'build date'�Ǝg�p���ꂽ�R���p�C����������'compiler'�̃L�[�����B
	 *
	 * @var array
	 */
	protected $_build_info;

	/**
	 * @constructor
	 *
	 * �o�[�W�������J�n�s���w�肵��NetB_Extra_FFmpeg_Information_Version��
	 * �V�����C���X�^���X������������
	 *
	 * @param string $line �o�[�W�������J�n�s�BcanHandle()�ÓI���\�b�h��true��Ԃ���������̂ݏ����\
	 */
	public function __construct($line) {
		if( ! self::canHandle( $line ) ) {
			$msg = "line '" . ( substr( $line, 0, 30 ) ) . "' is unknown format.";
			throw new NetB_Extra_FFmpeg_Exception( $msg );
		}

		preg_match( self::$__firstLine, $line, $matches );
		
		$this->_version = $matches[1];
		$this->_copyright = $matches[2];
	}

	/**
	 * �w��̍s��������o�[�W�������Ƃ��ĉ�͂����݂�B
	 * $line���o�[�W�������Ɋ܂܂��v�f�̏ꍇ�A���̃C���X�^���X��
	 * ������񂪍X�V����true��Ԃ����A�����ł��Ȃ�������̏ꍇ��
	 * false��Ԃ��A���̃C���X�^���X�̓��e�͂Ȃɂ��ω����Ȃ��B
	 *
	 * @param string $line ��͂���s������
	 * @return bool $line���o�[�W�������Ƃ��ĉ�͂ł����ꍇ��true�A����ȊO��false
	 */
	public function parseLine($line) {
		foreach( self::_getExpressions() as $key => $exp ) {
			$hit = preg_match( $exp, $line, $matches );
			if( ! $hit ) continue;

			// �q�b�g�����p�[�X�������Ƃɏ����𕪊�
			switch( $key ) {
			case 'config':
				// configure���
				$this->_configuration = split( ' ', $matches[1] );
				break;
			case 'library':
				// ���C�u�������
				$this->_libraries[ $matches[1] ] = $matches[2];
				break;
			case 'build_info':
				// �r���h���
				$this->_build_info = array(
					'build date' => $matches[1],
					'compiler' => $matches[2]
				);
				break;
			}
			// �s�������ł����̂�true�ŏI��
			return true;
		}
		// ��v����p�^�[�����Ȃ��̂�false�ŏI��
		return false;
	}

	/**
	 * FFmpeg�̃o�[�W������������擾����
	 *
	 * @return string
	 */
	public function getVersion() {
		return $this->_version;
	}

	/**
	 * FFmpeg�̒��쌠��������擾����
	 *
	 * @return string
	 */
	public function getCopyright() {
		return $this->_copyright;
	}

	/**
	 * configure�����擾����
	 *
	 * @return array
	 */
	public function getConfigOptions() {
		return $this->_configuration;
	}

	/**
	 * ��v���C�u�����̃o�[�W���������擾����B
	 * �߂�l�̔z��̓L�[�����C�u�������A�l�����̃��C�u�����̃o�[�W�����ɂȂ�B
	 *
	 * @return array
	 */
	public function getLibraryVersion() {
		return $this->_libraries;
	}

	/**
	 * �r���h�����擾����B
	 * �߂�l�͘A�z�z��ŁA�r���h���t������'build date'��
	 * �r���h�Ɏg�p���ꂽ�R���p�C����������'compiler'���L�[�Ɏ��B
	 *
	 * @return array
	 */
	public function getBuildInfo() {
		return $this->_build_info;
	}

	/**
	 * ���̃C���X�^���X�̉�͓��e��A�z�z��Ŏ擾����
	 *
	 * @return array
	 */
	public function toArray() {
		return array(
			'version' => $this->_version,
			'copyright' => $this->_copyright,
			'library version' => $this->_libraries,
			'configuration options' => $this->_configuration,
			'build info' => $this->_build_info
		);
	}
}

