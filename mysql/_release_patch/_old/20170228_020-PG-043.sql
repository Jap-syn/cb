-- �T�C�g�e�[�u���Ɂu���̑��v���ׂ������Ŏ��^�u���̑��v���ׂ������Ŏ��w�蕶���A�J�����ǉ�
ALTER TABLE `T_Site` 
ADD COLUMN `EtcAutoArrivalFlg` TINYINT NOT NULL DEFAULT 0 AFTER `Ent_OrderIdcheck`,
ADD COLUMN `EtcAutoArrivalNumber` VARCHAR(255) NULL AFTER `EtcAutoArrivalFlg`;

-- �T�C�g�e�[�u���Ɂu���̑��v���ׂ������Ŏ��ɃC���f�b�N�X�t�^
ALTER TABLE `T_Site` 
ADD INDEX `Idx_T_Site02` (`EtcAutoArrivalFlg` ASC);
