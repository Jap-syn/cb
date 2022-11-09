-- ---------------------------
-- ��X�L�[�}����
-- ---------------------------
ALTER TABLE T_MypageOrder ADD COLUMN `AccessKey` VARCHAR(100)  NULL AFTER `OemId`;
ALTER TABLE T_MypageOrder ADD COLUMN `AccessKeyValidToDate` DATETIME  NULL AFTER `AccessKey`;

ALTER TABLE `T_MypageOrder` 
ADD INDEX `Idx_T_MypageOrder05` (`AccessKey` ASC);


INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'OrderMypageAccessUrlValidDays', '14', '����ϲ�߰�ޱ����pURL���ԓ���', NOW(), 9, NOW(), 9, '1');

INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `SystemFlg` ,`RegistDate` ,`RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('72', '285', '����ϲ�߰��URL', '{OrderPageAccessUrl}', '4', '5', '1',NOW() , '1' ,NOW() ,'1' , '1');
UPDATE `M_Code` SET `KeyContent`='https://www.atobarai.jp/orderpage' WHERE `CodeId`='105' and`KeyCode`='2';

INSERT INTO M_CodeManagement VALUES(185 ,'�o�b�p�����}�C�y�[�W�@�A�N�Z�X�����؂ꕶ��' ,NULL ,'OEMID' ,1 ,NULL ,0,NULL,0,NULL, NOW(),1,NOW(),1,1);
INSERT INTO M_CodeManagement VALUES(186 ,'�X�}�z�p�����}�C�y�[�W�@�A�N�Z�X�����؂ꕶ��' ,NULL ,'OEMID' ,1 ,NULL ,0,NULL,0,NULL, NOW(),1,NOW(),1,1);
INSERT INTO M_Code VALUES(185,0 ,'���c�����}�C�y�[�WPC�߼�',NULL ,NULL , NULL ,'�������m�F�y�[�W�̉{���͈����ԂŏI���������܂��B<br>�������̂��₢���킹�ɂ��܂��Ă͕��ЃT�|�[�g�Z���^�[�܂ł��₢���킹���������B<br>�T�|�[�g�Z���^�[�d�b�ԍ��F TEL: 0120-667-690�i10:00 �` 18:00�j' ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(185,1 ,'�d�X�g�A�����}�C�y�[�WPC�߼�',NULL ,NULL , NULL ,'�������m�F�y�[�W�̉{���͈����ԂŏI���������܂��B<br>�������̂��₢���킹�ɂ��܂��Ă͕��ЃT�|�[�g�Z���^�[�܂ł��₢���킹���������B<br>�T�|�[�g�Z���^�[�d�b�ԍ��F TEL: 0120-667-690�i10:00 �` 18:00�j' ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(185,3 ,'�Z�C�m�[�����}�C�y�[�WPC�߼�',NULL ,NULL , NULL ,'�������m�F�y�[�W�̉{���͈����ԂŏI���������܂��B<br>�������̂��₢���킹�ɂ��܂��Ă͕��ЃT�|�[�g�Z���^�[�܂ł��₢���킹���������B<br>�T�|�[�g�Z���^�[�d�b�ԍ��F TEL: 0120-667-690�i10:00 �` 18:00�j' ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(185,4 ,'�a�`�r�d�����}�C�y�[�WPC�߼�',NULL ,NULL , NULL ,'�������m�F�y�[�W�̉{���͈����ԂŏI���������܂��B<br>�������̂��₢���킹�ɂ��܂��Ă͕��ЃT�|�[�g�Z���^�[�܂ł��₢���킹���������B<br>�T�|�[�g�Z���^�[�d�b�ԍ��F TEL: 0120-667-690�i10:00 �` 18:00�j' ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(186,0 ,'���c�����}�C�y�[�W����߰��',NULL ,NULL , NULL ,'�������m�F�y�[�W�̉{���͈����ԂŏI���������܂��B<br>�������̂��₢���킹�ɂ��܂��Ă͕��ЃT�|�[�g�Z���^�[�܂ł��₢���킹���������B<br>�T�|�[�g�Z���^�[�d�b�ԍ��F TEL: 0120-667-690�i10:00 �` 18:00�j' ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(186,1 ,'�d�X�g�A�����}�C�y�[�W����߰��',NULL ,NULL , NULL ,'�������m�F�y�[�W�̉{���͈����ԂŏI���������܂��B<br>�������̂��₢���킹�ɂ��܂��Ă͕��ЃT�|�[�g�Z���^�[�܂ł��₢���킹���������B<br>�T�|�[�g�Z���^�[�d�b�ԍ��F TEL: 0120-667-690�i10:00 �` 18:00�j' ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(186,3 ,'�Z�C�m�[�����}�C�y�[�W����߰��',NULL ,NULL , NULL ,'�������m�F�y�[�W�̉{���͈����ԂŏI���������܂��B<br>�������̂��₢���킹�ɂ��܂��Ă͕��ЃT�|�[�g�Z���^�[�܂ł��₢���킹���������B<br>�T�|�[�g�Z���^�[�d�b�ԍ��F TEL: 0120-667-690�i10:00 �` 18:00�j' ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(186,4 ,'�a�`�r�d�����}�C�y�[�W����߰��',NULL ,NULL , NULL ,'�������m�F�y�[�W�̉{���͈����ԂŏI���������܂��B<br>�������̂��₢���킹�ɂ��܂��Ă͕��ЃT�|�[�g�Z���^�[�܂ł��₢���킹���������B<br>�T�|�[�g�Z���^�[�d�b�ԍ��F TEL: 0120-667-690�i10:00 �` 18:00�j' ,0, NOW(), 1, NOW(), 1, 1);


-- �ߋ��f�[�^�ɑ΂��A�����L�[��ݒ�
UPDATE T_MypageOrder mo
SET AccessKey = SUBSTRING(CONCAT(mo.Seq, SHA2(MD5(RAND()), 512)), 1, 50) -- ��ӂɂ��邽�߂ɁA�����ł�PK�𗘗p����
   ,AccessKeyValidToDate = DATE_ADD(( SELECT LimitDate FROM T_ClaimHistory WHERE OrderSeq = mo.OrderSeq AND ValidFlg = 1 AND ClaimPattern = 1 ORDER BY Seq DESC LIMIT 1), INTERVAL 14 DAY)
;

SELECT AccessKey, count(1)
FROM T_MypageOrder
GROUP BY AccessKey
HAVING count(1) > 1;


-- ---------------------------
-- �}�C�y�[�W�X�L�[�}����
-- ---------------------------
-- View�̍č\�� ���^�p���A���J���̓X�L�[�}���قȂ�̂Œ���
DROP VIEW IF EXISTS `MV_MypageOrder`;

CREATE VIEW `MV_MypageOrder` AS
    SELECT *
    FROM coraldb_new01.T_MypageOrder
;
