/* �R�[�h�}�X�^�[�Ɍ������@��ǉ� */
INSERT INTO M_CodeManagement VALUES(193, 'CB�������X�֌����i�����ҕ��S�p�j', NULL, '�X�֌���', 1, 'AK��ށ���', 1, '�X�֌����ԍ�', 1, '�������`', NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(193, 1, 'CB����', 'AK0', '001305901557', '������ЃL���b�`�{�[��', NULL, 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(193, 11, 'E�X�g�A�[����', 'EA1', '001405665145', '������ЃL���b�`�{�[���^�d�X�g�A�[��p', NULL, 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(193, 21, 'SMBC����', 'AB1', '001506900331', '�r�l�a�b�t�@�C�i���X�T�[�r�X�������', NULL, 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(193, 31, '�Z�C�m�[����', 'SC1', '001007292043', '������ЃL���b�`�{�[���@�Z�C�m�[�e�b�W', NULL, 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(193, 41, 'BASE����', 'AB1', '001608450807', '������ЃL���b�`�{�[���@�a�`�r�d��p����', NULL, 0, NOW(), 1, NOW(), 1, 1);


/* �����X�e�[�u���ɍ��ځu�������S�敪�v��ǉ� */
ALTER TABLE `T_Enterprise` 
ADD COLUMN `ChargeClass` TINYINT(4) NOT NULL DEFAULT 0 AFTER `ExecStopFlg`;


/* �����c�[���𗘗p��������X�ɂ��āA�������S�敪��1�F�����l���S�ɍX�V */
UPDATE T_Enterprise 
SET ChargeClass = 1 
WHERE IFNULL(SelfBillingMode, 0) > 0;

