<?php
namespace Coral\Base\Extra\FFmpeg\Information;

require_once 'NetB/Extra/FFmpeg/Information/Interface.php';

/**
 * @class
 *
 * �o�̓t�@�C��������͂���NetB_Extra_FFmpeg_Information_Interface�����N���X
 */
class BaseExtraFFmpegInformationOutput implements BaseExtraFFmpegInformationInterface {
	/**
	 * @static
	 * @protected
	 *
	 * �o�̓t�@�C�����̊J�n�s�Ɉ�v����Perl�݊����K�\���̃p�^�[��
	 *
	 * @var string
	 */
	protected static $__firstLine = '/^Output #(\d+), ([^,]+), to ([^:]+):$/';

	/**
	 * @static
	 * @protected
	 *
	 * �o�̓t�@�C�����̊e�s����͂��鐳�K�\���p�^�[���̔z��
	 *
	 * @var array
	 */
	protected static $__expressions;

	/**
	 * @static
	 *
	 * �w��̍s�����񂪃t�@�C�����͏��̊J�n�s�Ɉ�v���邩�𔻒f����
	 *
	 * @param string $line ��������s������
	 * @return bool $line���o�̓t�@�C�����̊J�n�s�̏ꍇ��true�A����ȊO��false
	 */
	public static function canHandle($line) {
		return preg_match( self::$__firstLine, $line ) ? true : false;
	}

	/**
	 * @static
	 * @protected
	 *
	 * �o�̓t�@�C�����̊e�s����͂��鐳�K�\���̔z����擾����
	 *
	 * @return array
	 */
	protected static function _getExpressions() {
		if( self::$__expressions == null ) {
			self::$__expressions = array(
				'stream' => '/    Stream #([^:]+): ([^:]+): (.+)$/'
			);
		}
		return self::$__expressions;
	}

	/**
	 * @protected
	 *
	 * ���̓t�@�C���̃��f�B�A�t�H�[�}�b�g
	 *
	 * @var string
	 */
	protected $_format;

	/**
	 * @protected
	 *
	 * ���̓t�@�C���̃p�X
	 *
	 * @var string
	 */
	protected $_path;

	/**
	 * @protected
	 *
	 * �t�@�C���Ɋ܂܂��e�X�g���[���̏���ێ�����A�z�z��B
	 * �L�[���X�g���[���̎�ʁA�e���ڂ����̃X�g���[���̏��������A�z�z��ɂȂ�B
	 *
	 * @var array
	 */
	protected $_streams = array();

	/**
	 * @constructor
	 *
	 * �o�̓t�@�C�����J�n�s���w�肵�āANetB_Extra_FFmpeg_Information_Output��
	 * �V�����C���X�^���X������������
	 *
	 * @param string $line ���̓t�@�C�����J�n�s�BcanHandle()�ÓI���\�b�h��true��Ԃ���������̂ݏ����\
	 */
	public function __construct($line) {
		if( ! self::canHandle( $line ) ) {
			$msg = "line '" . ( substr( $line, 0, 30 ) ) . "' is unknown format.";
			throw new NetB_Extra_FFmpeg_Exception( $msg );
		}

		preg_match( self::$__firstLine, $line, $matches );

		$this->_format = $matches[2];
		$this->_path = str_replace( "'", '', "$matches[3]" );
	}

	/**
	 * �w��̍s���������̓t�@�C�����Ƃ��ĉ�͂����݂�B
	 * $line�����̓t�@�C�����Ɋ܂܂��v�f�̏ꍇ�A���̃C���X�^���X��
	 * ������񂪍X�V����true��Ԃ����A�����ł��Ȃ�������̏ꍇ��
	 * false��Ԃ��A���̃C���X�^���X�̓��e�͂Ȃɂ��ω����Ȃ��B
	 *
	 * @param string $line ��͂��镶����
	 * @return bool $line�����̓t�@�C�����Ƃ��ĉ�͂ł����ꍇ��true�A����ȊO��false
	 */
	public function parseLine($line) {
		foreach( self::_getExpressions() as $key => $exp ) {
			$hit = preg_match( $exp, $line, $matches );
			if( ! $hit ) continue;

			// �q�b�g�����p�[�X�������Ƃɏ����𕪊�
			switch( $key ) {
			case 'stream':
				// �X�g���[�����
				$kind = strtolower( $matches[2] );
				$map = null;
				$props = split( ', ', $matches[3] );
				
				// ��͂����X�g���[���̃v���p�e�B���ƈʒu����v����v���p�e�B����
				// �X�g���[���^�C�v���Ƃɍ\�z
				switch( $kind ) {
				case 'video':
					$map = split( '/', 'format/pixel format/size/quality/bitrate/frame rate' );
					break;
				case 'audio':
					$map = split( '/', 'encoder/sampling rate/channels/bitrate' );
					break;
				}
				if( $map ) {
					$info = array();
					foreach( split( ', ', $matches[3] ) as $i => $prop ) {
						$name = $map[$i];
						switch( $name ) {
						case 'size':
							// �r�f�I�T�C�Y�̏ꍇ�̓A�X�y�N�g��Ȃǂ̏��[]�ň͂܂�Ă�̂�
							// �؂藎�Ƃ�
							$info[$name] = preg_replace( '/ \[[^\]]*\]$/', '', "$prop" );
							// �\�Ȃ�T�C�Y���͕��ƍ����̐��l�ɕ���
							if( preg_match( '/^(\d+)x(\d+)$/', $info[$name], $size_info ) ) {
								$info[$name] = array( 'width' => (int)$size_info[1], 'height' => (int)$size_info[2] );
							}
							break;
						case 'frame rate':
							// �t���[�����[�g�������ɕs�v�ȕ����񂪂���̂Ő؂藎�Ƃ�
							$info[$name] = preg_replace( '/ t[bc]\(.\)$/', '', "$prop" );
							break;
						default:
							$info[$name] = "$prop";
							break;
						}
					}
					$this->_streams[$kind] = $info;
				}
				break;
			}
			return true;
		}
		return false;
	}

	/**
	 * �o�̓t�@�C���̃t�H�[�}�b�g���擾����B
	 * �����ffmpeg���o�̓G���R�[�h�ɗp�������C�u�����̖��O�������ꍇ������B
	 *
	 * @return string
	 */
	public function getFormat() {
		return $this->_format;
	}

	/**
	 * �o�̓t�@�C���̃p�X���擾����B
	 *
	 * @return string
	 */
	public function getFilePath() {
		return $this->_path;
	}

	/**
	 * ���f�B�A�X�g���[���̏���A�z�z��Ŏ擾����B
	 * �߂�l�͂��ꂼ�ꓮ��X�g���[��������'video'�A�����X�g���[��������'audio'��
	 * �L�[�Ƃ��A�Ή�����l�͊e�X�g���[���̃v���p�e�B�i�r�b�g���[�g�E�T�C�Y�Ȃǁj��
	 * �ێ�����A�z�z��ɂȂ�B
	 *
	 * @return array
	 */
	public function getStreamInfo() {
		return $this->_streams;
	}

	/**
	 * ���̃C���X�^���X�̉�͓��e��A�z�z��Ŏ擾����
	 *
	 * @return array
	 */
	public function toArray() {
		return array(
			'format' => $this->_format,
			'file path' => $this->_path,
			'stream info' => $this->_streams
		);
	}
}

