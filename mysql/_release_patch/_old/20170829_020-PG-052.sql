/* �T�C�g�e�[�u���̃f�t�H���g�l���C�� */
ALTER TABLE `T_Site` 
CHANGE COLUMN `NgChangeFlg` `NgChangeFlg` TINYINT(4) NOT NULL DEFAULT 0 ,
CHANGE COLUMN `ShowNgReason` `ShowNgReason` TINYINT(4) NOT NULL DEFAULT 0 ;
