/* �ʒm���e�Ǘ��̓o�^ */
DROP TABLE IF EXISTS `T_NotificationManage`;
CREATE TABLE `T_NotificationManage` (
  `Seq` bigint(20) NOT NULL AUTO_INCREMENT,
  `Token` varchar(30) NOT NULL,
  `ReceivedData` mediumtext,
  `ReceivedData2` longtext,
  PRIMARY KEY (`Seq`),
  UNIQUE KEY `Idx_T_NotificationManage01` (`Token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/* �R�[�h�}�X�^�[��API���_�C���N�g��ǉ� */
INSERT INTO M_CodeManagement VALUES(194, 'API���_�C���N�g�ݒ�', NULL, NULL, 1, '�L���t���O(0:�����^1:�L��)', 0, NULL, 0, NULL, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(194, 1, '���_�C���N�g��(E�X�g�A�[)', '0', NULL, NULL, NULL, 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(194, 2, '���_�C���N�g��(SMBC)'     , '1', NULL, NULL, 'https://www.ato-barai.jp/smbcfs/api', 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(194, 3, '���_�C���N�g��(�Z�C�m�[)' , '1', NULL, NULL, 'https://atobarai.seino.co.jp/seino-financial/api', 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(194, 4, '���_�C���N�g��(BASE)'     , '0', NULL, NULL, NULL, 0, NOW(), 1, NOW(), 1, 1);
