<?php
namespace Coral\Base\Extra\FFmpeg\Information;

require_once 'NetB/Extra/FFmpeg/Exception.php';

/**
 * @interface
 *
 * ffmpeg�̎��s���ʂ��������邽�߂̃C���^�[�t�F�C�X�ŁA
 * �o�[�W����������̓t�@�C���Ȃǂ̃J�e�S���P�ʂŏ������s���B
 * �C���^�[�t�F�C�X�Ƃ��ẮA�^����ꂽ�ƕ�������A
 * �����̃J�e�S���̑����Ƃ��Ă̏��������݂�parseLine()�݂̂��������邪�A
 * �����N���X�͑��Ɏ����̃J�e�S���̍ŏ��̍s�ł��邩�𔻒f���邽�߂�
 * canHandle()�X�^�e�B�b�N���\�b�h����������K�v������B
 */
interface BaseExtraFFmpegInformationInterface {
	/**
	 * @abstract
	 *
	 * �w��̍s������������̃J�e�S���̃v���p�e�B�Ƃ��ď��������݂�B
	 * ���������������ꍇ�͎��g�̃v���p�e�B���X�V�������true��Ԃ��A
	 * �����ł��Ȃ��ꍇ��false��Ԃ��K�v������B
	 *
	 * @param string $line �����Ώۂ̍s������
	 * @return bool $line�̏����ɐ����������̃v���p�e�B���X�V���ꂽ�ꍇ��true�A����ȊO��false
	 */
	public function parseLine($line);

	/**
	 * @abstract
	 *
	 * ��͍ς݂̏���A�z�z��Ŏ擾����
	 *
	 * @return array
	 */
	public function toArray();
}

