<?php
namespace Coral\Base\Shell;

require_once 'NetB/Shell/Command/Builder.php';

class BaseShellCommand {
	/**
	 * @static
	 *
	 * NetB_Shell_Command_Builder�̃C���X�^���X��V�K�ɍ쐬����t�@�N�g�����\�b�h�B
	 * ���̃X�^�e�B�b�N���\�b�h�͒P�ɃR���X�g���N�^�ւ̃G�C���A�X�����A
	 * ����ŃC���X�^���X�𐶐�����Ƃ����Ƀ��\�b�h�`�F�[���ɂ��p�����[�^�\�z��
	 * �s�����Ƃ��ł���B
	 *
	 * @param string $command ���s����R�}���h���C���̃R�}���h
	 * @param null|array $options �I�v�V��������
	 * @return NetB_Shell_Command_Builder
	*/
	public static function create($command, $options = array()) {
	   return new NetB_Shell_Command_Builder( $command, $options );
	}


}

