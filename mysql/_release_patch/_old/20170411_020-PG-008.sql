--------------------------------------------------------------------------------
-- �ȉ��A�}�C�y�[�W���X�L�[�}�֓o�^
--------------------------------------------------------------------------------
-- �����}�C�y�[�W���O�C�����e�[�u���쐬
DROP TABLE IF EXISTS `T_MypageOrderLogin`;
CREATE TABLE `T_MypageOrderLogin` (
  `Seq` bigint(20) NOT NULL,
  `LastLoginDate` datetime NOT NULL,
  PRIMARY KEY (`Seq`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



--------------------------------------------------------------------------------
-- �ȉ��A�{�̑��X�L�[�}�֓o�^
--------------------------------------------------------------------------------
DROP VIEW IF EXISTS `MPV_MypageOrderLogin`;
CREATE VIEW `MPV_MypageOrderLogin` AS SELECT * FROM coraldb_mypage01.T_MypageOrderLogin;
