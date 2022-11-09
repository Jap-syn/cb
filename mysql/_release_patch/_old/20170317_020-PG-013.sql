-- ��^���l��`�e�[�u���쐬
CREATE TABLE `T_FixedNoteDefine` (
  `Seq` bigint(20) NOT NULL AUTO_INCREMENT,
  `Type` tinyint(4) NOT NULL DEFAULT '0',
  `Note` TEXT NOT NULL,
  `ListNumber` int(11),
  `UseType1` tinyint(4),
  `UseType2` tinyint(4),
  `RegistDate` datetime DEFAULT NULL,
  `RegistId` int(11) DEFAULT NULL,
  `UpdateDate` datetime DEFAULT NULL,
  `UpdateId` int(11) DEFAULT NULL,
  `ValidFlg` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`Seq`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ��^���l�֘A�t���e�[�u���쐬
CREATE TABLE `T_FixedNoteRelate` (
  `HeaderSeq` bigint(20) NOT NULL,
  `DetailSeq` bigint(20) NOT NULL,
  `ListNumber` int(11) NOT NULL,
  `RegistDate` datetime DEFAULT NULL,
  `RegistId` int(11) DEFAULT NULL,
  `UpdateDate` datetime DEFAULT NULL,
  `UpdateId` int(11) DEFAULT NULL,
  `ValidFlg` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`HeaderSeq`, `DetailSeq`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT INTO T_Menu VALUES (184, 'cbadmin', 'kanriMenus', 'fixednote', NULL, '***', '��^���l�Ǘ�', '��^���l�Ǘ�', '', '', '', NOW(), 9, NOW(), 9, 1);
INSERT INTO T_MenuAuthority VALUES (184, 1, NOW(), 9, NOW(), 9, 0);
INSERT INTO T_MenuAuthority VALUES (184, 11, NOW(), 9, NOW(), 9, 1);
INSERT INTO T_MenuAuthority VALUES (184, 101, NOW(), 9, NOW(), 9, 1);

/* ��^���l��`�f�[�^�o�^ */
INSERT INTO T_FixedNoteDefine VALUES(1, 0, '�A����ʁi���́j', 1, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(2, 1, '�G���h���TEL(�o�^�ԍ�)', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(3, 1, '�G���h�Ƒ��E��O�҂��TEL', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(4, 1, '�X�܂��TEL', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(5, 1, '�G���h��胁�[��', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(6, 1, '�X�܂�胁�[��', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(7, 1, '�G���h��TEL', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(8, 1, '�X�܂�TEL', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(9, 1, '�G���h�փ��[��', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(10, 1, '�X�܂փ��[��', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(11, 1, '���̑�', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(12, 0, '�A����ʁiE�X�g�A�[�j', 2, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(13, 1, 'E�X�g�A�[���TEL', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(14, 1, 'E�X�g�A�[��胁�[��', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(15, 1, '�����������Ĕ��s�˗�', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(16, 1, '�����`�[�ԍ��ύX�˗�', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(17, 0, '�A����ʁi�Z�C�m�[�j', 3, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(18, 1, '�Z�C�m�[���TEL', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(19, 1, '�Z�C�m�[��胁�[��', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(20, 0, '�A����ʁiBASE�j', 4, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(21, 1, 'BASE���TEL', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(22, 1, 'BASE��胁�[��', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(23, 0, '������(�₢���킹���e)', 5, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(24, 1, '�����������E�j��', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(25, 1, '����������', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(26, 1, '���`�E�Z���ύX', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(27, 1, '�o�[�R�[�h�ǎ�s��', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(28, 1, '�����R��', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(29, 1, '���z������', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(30, 0, '������(�Ή����e)', 6, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(31, 1, '�萔�������̏�Ĕ��s�B', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(32, 1, '�萔���m�F���B', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(33, 1, '�Z������Ȃ��̂ŏ���Ĕ��s�B', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(34, 1, '�Z������Ȃ��̂ł��������҂��ĂāB', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(35, 1, '����Ĕ��s�B', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(36, 1, '��1�[���~�ōĔ��s�B', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(37, 0, '�x�������k(�₢���킹���e)', 7, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(38, 1, '�����������؂�', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(39, 1, '�x������������', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(40, 1, '������]', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(41, 0, '�x�������k(�Ή����e)', 8, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(42, 1, '�萔�������̏�Ĕ��s�B', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(43, 1, '�x�����񑩁B', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(44, 1, '�ĘA���񑩁B', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(45, 1, '�ٌ�m�U���B', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(46, 1, '�x�����s���s�B', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(47, 1, '�����ē��B', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(48, 1, '���Ǘ����փG�X�J���B', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(49, 0, '�^�M�֌W(�₢���킹���e)', 9, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(50, 1, 'NG�₢', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(51, 1, '�ĐR��', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(52, 1, '���ۏ؈˗�', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(53, 0, '�^�M�֌W(�Ή����e)', 10, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(54, 1, 'AK���s�����̂���NG�B', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(55, 1, 'AK���@�����x��̂���NG�B', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(56, 1, '���vNG�B', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(57, 1, 'TEL�����̂���NG�B', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(58, 1, 'TEL�s���̂���NG�B', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(59, 1, '���������邽��NG�B', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(60, 1, '�ǉ���񂠂�΍ė^�M�\�B', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(61, 1, '���ۏ�OK�B', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(62, 1, '���ۏ؂ł�NG�B', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(63, 0, '�x����(�₢���킹���e)', 11, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(64, 1, '�x���ςȂ̂ɐ��������Ă���', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(65, 1, '��d�Ɏx�����Ă��܂���', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(66, 0, '�x����(�Ή����e)', 12, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(67, 1, '����Ⴂ�������B�j���˗��B', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(68, 1, '�ʌ������Ɗ��Ⴂ�B�x�����U���B', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(69, 1, '�����m�F�ł����B', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(70, 1, '�ԋ��ē��B', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(71, 1, '�X�ܒ��ړ����B', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(72, 1, '�d���o�^�B', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(73, 0, '���i(�₢���킹���e)', 13, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(74, 1, '���i����', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(75, 1, '�L�����Z��(������)', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(76, 1, '���i�ɂ���', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(77, 1, '�ʌ��ςŎx������', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(78, 1, '���ϕ��@�ύX��]', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(79, 1, '�����̊o���Ȃ�', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(80, 0, '���i(�Ή����e)', 14, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(81, 1, '�X�ܗU��', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(82, 1, 'CB����X�܂֊m�F', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(83, 0, '�߂萿��(�A�����e)', 15, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(84, 1, '����߂萿�����B', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(85, 1, '��1�߂萿�����B', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(86, 1, '��3�߂萿�����B', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(87, 1, '��4�߂萿�����B', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(88, 1, '��5�߂萿�����B', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(89, 1, '��6�߂萿�����B', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(90, 1, '��7�߂萿�����B', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(91, 0, '�߂萿��(�Ή����e)', 16, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(92, 1, '�s�B���[�����M', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(93, 1, '�z����֏���Ĕ��s', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(94, 1, '�o�^TEL�˓d', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(95, 1, '�Ή��ς̂��ߏ�������', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(96, 1, '�L�����Z���ς̂��ߏ�������', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(97, 1, '�����ς̂��ߏ�������', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(98, 0, '�ԋ��A��(�A�����e)', 17, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(99, 1, '�ԋ������m�F', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(100, 1, '�[�����ԋ���', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(101, 0, '�ԋ��A��(�Ή����e)', 18, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(102, 1, '�ԋ������m�F', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(103, 1, '�����҂��y�ĘA���z', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(104, 1, '�[��', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(105, 1, '�X�ܒ��ړ���', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(106, 1, '�ēo�^��A�[��', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(107, 1, 'CB���ԋ�', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(108, 1, '�X�܂��ԋ�', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(109, 1, '�X�܊m�F��', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(110, 0, '�L�����Z��(�A�����e)', 19, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(111, 1, '�y�敪3�z', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(112, 1, '�y�敪4�z', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(113, 0, '�L�����Z��(�Ή����e)', 20, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(114, 1, '�L�����Z���ŊԈႢ�Ȃ��B�m�肷��', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(115, 1, '�X�܊m�F���B�A���҂�', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(116, 0, '��-�˓d�E��d�i�₢���킹���e)', 21, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(117, 1, '�˓d���{�l', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(118, 1, '�˓d���o��', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(119, 1, '�˓d������d������', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(120, 1, '�o�^�d�b����d', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(121, 1, '�˓d���g�p�Ȃ�', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(122, 1, '�˓d���ʘb��', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(123, 1, '�˓d���ʐl', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(124, 1, '�˓d���s����~', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(125, 1, '�˓d���]�ƈ�', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(126, 1, '�˓d�����M����', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(127, 1, '�˓d���Ƒ��`��', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(128, 1, '�˓d���]�ƈ��`��', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(129, 1, '�˓d��FAX', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(130, 1, '�˓d���f�[�^��p', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(131, 1, '�˓d�����O', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(132, 1, '�˓d���̏�', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(133, 0, '�T�[�r�T�[(�₢���킹���e)', 22, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(134, 1, '���V�h�@������������', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(135, 1, '��{�@������������', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(136, 1, '�c���E�����@������������', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(137, 1, '���V�h�@���������� �s�k', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(138, 1, '�R�����Y�@������������i���V�h�߂蕪�j', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(139, 0, '���ԋp(�₢���킹���e)', 23, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(140, 1, '�X�܂֍��ԋp', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);


/* ��^���l�֘A�t���f�[�^�o�^ */

INSERT INTO T_FixedNoteRelate VALUES (1, 2,1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (1, 3,2, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (1, 4,3, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (1, 5,4, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (1, 6,5, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (1, 7,6, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (1, 8,7, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (1, 9,8, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (1, 10,9, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (1, 11,10, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (12, 2,1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (12, 3,2, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (12, 4,3, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (12, 5,4, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (12, 6,5, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (12, 7,6, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (12, 8,7, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (12, 9,8, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (12, 10,9, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (12, 11,10, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (12, 13,11, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (12, 14,12, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (12, 15,13, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (12, 16,14, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (17, 2,1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (17, 3,2, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (17, 4,3, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (17, 5,4, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (17, 6,5, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (17, 7,6, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (17, 8,7, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (17, 9,8, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (17, 10,9, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (17, 11,10, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (17, 18,11, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (17, 19,12, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (20, 2,1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (20, 3,2, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (20, 4,3, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (20, 5,4, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (20, 6,5, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (20, 7,6, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (20, 8,7, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (20, 9,8, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (20, 10,9, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (20, 11,10, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (20, 21,11, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (20, 22,12, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (23, 24,1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (23, 25,2, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (23, 26,3, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (23, 27,4, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (23, 28,5, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (23, 29,6, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (30, 31,1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (30, 32,2, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (30, 33,3, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (30, 34,4, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (30, 35,5, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (30, 36,6, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (37, 38,1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (37, 39,2, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (37, 40,3, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (41, 42,1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (41, 43,2, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (41, 44,3, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (41, 45,4, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (41, 46,5, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (41, 47,6, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (41, 48,7, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (49, 50,1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (49, 51,2, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (49, 52,3, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (53, 54,1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (53, 55,2, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (53, 56,3, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (53, 57,4, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (53, 58,5, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (53, 59,6, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (53, 60,7, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (53, 61,8, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (53, 62,9, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (63, 64,1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (63, 65,2, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (66, 67,1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (66, 68,2, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (66, 69,3, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (66, 70,4, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (66, 71,5, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (66, 72,6, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (73, 74,1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (73, 75,2, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (73, 76,3, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (73, 77,4, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (73, 78,5, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (73, 79,6, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (80, 81,1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (80, 82,2, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (83, 84,1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (83, 85,2, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (83, 86,3, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (83, 87,4, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (83, 88,5, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (83, 89,6, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (83, 90,7, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (91, 92,1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (91, 93,2, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (91, 94,3, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (91, 95,4, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (91, 96,5, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (91, 97,6, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (98, 99,1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (98, 100,2, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (101, 102,1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (101, 103,2, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (101, 104,3, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (101, 105,4, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (101, 106,5, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (101, 107,6, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (101, 108,7, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (101, 109,8, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (110, 111,1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (110, 112,2, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (113, 114,1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (113, 115,2, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (116, 117,1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (116, 118,2, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (116, 119,3, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (116, 120,4, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (116, 121,5, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (116, 122,6, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (116, 123,7, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (116, 124,8, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (116, 125,9, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (116, 126,10, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (116, 127,11, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (116, 128,12, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (116, 129,13, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (116, 130,14, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (116, 131,15, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (116, 132,16, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (133, 134,1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (133, 135,2, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (133, 136,3, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (133, 137,4, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (133, 138,5, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteRelate VALUES (139, 140,1, NOW(), 1, NOW(), 1, 1);
