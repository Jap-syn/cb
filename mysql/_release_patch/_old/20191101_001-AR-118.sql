/* �����X�փJ�����ǉ� */
ALTER TABLE T_Enterprise ADD COLUMN `LinePayUseFlg` TINYINT NOT NULL DEFAULT 0 AFTER `ExecStopFlg`;

/* ���σX�e�[�V�����A�g�A�J�E���g�փJ�����ǉ� */
ALTER TABLE T_SmbcRelationAccount ADD COLUMN `SyunoCoCd4` VARCHAR(8) NOT NULL NULL AFTER `SyunoCoCd3`;
ALTER TABLE T_SmbcRelationAccount ADD COLUMN `SyunoCoCd5` VARCHAR(8) NOT NULL NULL AFTER `SyunoCoCd4`;
ALTER TABLE T_SmbcRelationAccount ADD COLUMN `SyunoCoCd6` VARCHAR(8) NOT NULL NULL AFTER `SyunoCoCd5`;
ALTER TABLE T_SmbcRelationAccount ADD COLUMN `ShopPwd4` VARCHAR(20) NOT NULL NULL AFTER `ShopPwd3`;
ALTER TABLE T_SmbcRelationAccount ADD COLUMN `ShopPwd5` VARCHAR(20) NOT NULL NULL AFTER `ShopPwd4`;
ALTER TABLE T_SmbcRelationAccount ADD COLUMN `ShopPwd6` VARCHAR(20) NOT NULL NULL AFTER `ShopPwd5`;


