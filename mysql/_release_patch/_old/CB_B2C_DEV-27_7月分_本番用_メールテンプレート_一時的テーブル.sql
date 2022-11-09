-- Create temporary MailTemplate to insert data from www3
CREATE TABLE `T_MailTemplate_Tmp` (
  `Id` bigint(20) NOT NULL AUTO_INCREMENT,
  `Class` int(11) DEFAULT NULL,
  `ClassName` varchar(256) DEFAULT NULL,
  `FromTitle` varchar(1000) DEFAULT NULL,
  `FromTitleMime` varchar(1000) DEFAULT NULL,
  `FromAddress` varchar(1000) DEFAULT NULL,
  `ToTitle` varchar(1000) DEFAULT NULL,
  `ToTitleMime` varchar(1000) DEFAULT NULL,
  `ToAddress` varchar(1000) DEFAULT NULL,
  `Subject` varchar(1000) DEFAULT NULL,
  `SubjectMime` varchar(1000) DEFAULT NULL,
  `Body` varchar(4000) DEFAULT NULL,
  `OemId` bigint(20) DEFAULT NULL,
  `RegistDate` datetime DEFAULT NULL,
  `RegistId` int(11) DEFAULT NULL,
  `UpdateDate` datetime DEFAULT NULL,
  `UpdateId` int(11) DEFAULT NULL,
  `ValidFlg` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- insert data from w3
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (1,1,'���Ǝғo�^�����i�T�[�r�X�J�n�j���[��','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z�A�J�E���g���s�̂��m�点','{ServiceName}','{EnterpriseNameKj}�@�l

���̓x�͕��ЃT�[�r�X�y{ServiceName}�z�ɂ��\���݂��������A
�܂��Ƃɂ��肪�Ƃ��������܂��B

�A�J�E���g���s�������������܂����̂�
�T�[�r�X�J�n�܂łɕK�v�ȍŏI�菇�̂��ē����������܂��B
�Ō�܂ł������������B


���ϊǗ��V�X�e���̃��O�C���������m�点�������܂��B

�y�Ǘ��T�C�g�t�q�k�z
https://www.atobarai.jp/member/

�y���O�C���h�c�z
{LoginId}
�@���@���O�C���p�X���[�h�͕ʃ��[���ɂĂ��m�点�������܂��B


���I���I���I���I���I���I���I���I���I���I���I���I���I���I��

�@�T�[�r�X�J�n�܂ŁA�ȉ�STEP.1�`4�܂ł̂��葱�����K�v�ł��B
�@�K�����m�F���������B

���I���I���I���I���I���I���I���I���I���I���I���I���I���I��


�@�������@STEP.1�@�o�^���e�̂��m�F�@������

���ϊǗ��V�X�e���Ƀ��O�C�����������A
�u�o�^���Ǘ��v�̃��j���[���
�o�^����Ă���X�܏�񂪂��ԈႢ�Ȃ��������m�F���������B
�i�T�C�g���A�����p�v�����Ȃǁj

�@�������@STEP.2�@��^���̃T�C�g�f�ځ@������

�T�C�g��ɁA����җl�֌������T�C�g�f�ڗp��^�������f�ڂ��������B
�ڍׂ͓����z�M�̕ʃ��[���ɂĂ��ē��������܂��B
�i�f�ډӏ��F���菤����@�y�[�W�⌈�ϑI����ʂȂǁj

�@���@�f�ڂ������������_����̃T�[�r�X�J�n�ƂȂ�܂��B

�@�������@STEP.3�@�T�[�r�X�J�n�̓��Ђւ̂��ʒm�@������

�T�[�r�X�̊J�n�i�T�C�g�f�ڗp��^���̌f�ځj���������ꂽ�|��
���Ђ܂Ń��[���ɂĂ��m�点���������B
mail�F{ServiceMail}

�@�������@STEP.4�@���Ђ����ω�ʂ��m�F�@������

���ВS�����e�y�[�W��q�����A��肪�Ȃ����
���̂܂ܕ��ЃT�[�r�X���^�p���������Ė�育�����܂���B

�@���@�ꍇ�ɂ��C���̂��肢�����邱�Ƃ��������܂��B


�ȏ�ł������܂��B

����Ƃ����i�����t�������̒��A��낵�����肢�������܂��B


������ЃL���b�`�{�[��
�@{ServiceName}���ƕ��@�X�^�b�t�ꓯ

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',null,'2015/08/31 22:42:31',9,'2022/04/19 16:34:27',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (2,2,'�����o�^�i�^�M�J�n�j���[��','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z�^�M�J�n�̂��m�点�i{OrderCount}���j','{ServiceName}','{EnterpriseNameKj} �l

�����y{ServiceName}�z�������p���������A�܂��Ƃɂ��肪�Ƃ��������܂��B
�ȉ��̂��������󂯕t���������܂����B
������^�M�ɓ���܂��̂ŁA���i���܂���������Ȃ��悤�����Ӊ������B

��t���������F{OrderCount}��

�������Җ��i���������z�j
--------------------------------------------------------------
{OrderSummary}
--------------------------------------------------------------
��L������̗^�M������ɁA�^�M�������[���𑗐M�������܂��B


��18:00�ȍ~�̗^�M�́A�ʏ헂��11:00�܂ł̉񓚂ƂȂ�܂��̂ł����Ӊ������B
�������ɂ��^�M�ɂ����鎞�Ԃ��قȂ�ꍇ���������܂��B���̏ꍇ�A�^�M���ʂ�
�o�����̂��玩���ŗ^�M�������[�������M����܂��̂ŁA���炩���߂������������B


�����������������������@�L�����Z�������������ꍇ�@����������������������

���o�^���ꂽ�����̃L�����Z�����������ꍇ�́A���萔�ł����u���������v����
���������������A�Y���̂�������N���b�N���ăL�����Z���������s���ĉ������B

������������������������������������������������������������������������

�y�Ǘ���ʂt�q�k�z
https://www.atobarai.jp/member/

���s���ȓ_�Ȃǂ������܂�����A���C�y�ɂ��₢���킹�������B

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',null,'2015/08/31 22:42:31',9,'2022/04/19 16:35:43',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (3,3,'�^�M�������[��','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z�^�M�����̂��m�点�F�v{CreditCount}���i����NG{NgCount}���j','{ServiceName}','{EnterpriseNameKj}�@�l

�����y{ServiceName}�z�������p���������A�܂��Ƃɂ��肪�Ƃ��������܂��B

�^�M�����F{CreditCount} ��

�̗^�M���ʂ��o�܂����̂ł��񍐂������܂��B

�y�Ǘ���ʂt�q�k�z
https://www.atobarai.jp/member/

���uNG�v�̂������͖��ۏ؂ł���΁uOK�v�ɕύX�ł���ꍇ���������܂��B
���ۏ؂�{ServiceName}����]�̕��̓��[���ɂĂ��A���������B
�i���ۏ؂ł��uOK�v�ɕύX�ł��Ȃ��ꍇ���������܂��̂ŁA���Ђ����
�ԐM���[�������m�F���������Ă���A���i�����Ȃǂ��s���Ă��������B�j

{Orders}

�yOK�Č��̏����z
�^�M���ʉ߂���������Ɋւ��܂��ẮA

1.���i�̔���
2.�z���`�[�ԍ��o�^

�ɂ��i�݉������B

�yNG�Č��̏����z
�^�M���ʂ�NG�̂�����Ɋւ��܂��ẮA�܂��Ƃɂ��萔�ł����A���ۏ؂ł�{ServiceName}
�T�[�r�X�ɐ؂�ւ��Ă����������A�����߂ɂ��w���җl�ɑ��̌��ϕ��@�̂��I����
���������Ȃǂ̂��Ή������肢�������܂��B

���s���ȓ_�Ȃǂ������܂�����A���[���������͂��d�b�ɂĂ��⍇���������B

�Ȃ��A�^�M���ʗ��R�ɂ��܂��Ă͋���Ȃ���A���񓚂��邱�Ƃ��ł��܂���̂�
���炩���߂��������������܂��B

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',null,'2015/08/31 22:42:31',9,'2022/04/19 16:36:30',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (4,4,'���������s���[���iPC�j','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z���������s�ē��@�i�n�K�L�œ͂��܂��j�@�������ԍ��F{OrderId}','{ServiceName}','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM���������B

���z�[���y�[�W�ɂāA�u�悭���鎿��v���f�ڂ��Ă���܂��̂ŁA�����Ă��Q�Ƃ��������B
�@�z�[���y�[�W�Fhttps://atobarai-user.jp/
�@�������ɂ��āFhttps://atobarai-user.jp/faq/invoice/
�@�����������E�����ɂ��āFhttps://atobarai-user.jp/faq/non-arrival

�������p�󋵂͉��L�������m�F�y�[�W�ł��m�F���������܂��B
�@�ȈՃ��O�C���y�[�W�@{OrderPageAccessUrl}
�@(�ȈՃ��O�C���L�����Ԃ�{LimitDate}���14���ԂƂȂ�܂��B)

�������[������M���Ă���P�T�Ԍo�߂��Ă����������͂��Ȃ��ꍇ��A
�@�������̕����Ȃǂɂ��y�Ĕ��s�˗��z��
�@��L��URL���炨�葱�����������܂��B
��������������������������������������������������������������������
 
{CustomerNameKj}�l

����� {SiteNameKj}�l�ł̂���������
�y{ServiceName}�z�������p���������܂��āA
�܂��Ƃɂ��肪�Ƃ��������܂��B

���������𔭍s�������܂����B
������������A�����܂łɂ��x�������������܂��悤�A
���肢�\���グ�܂��B
���x����������{LimitDate}�ł������܂��B

���������́A���ʗX�ւł̔����ƂȂ�܂��̂ŁA���q�l�̂��茳�ɓ͂��܂�
��T�Ԓ��x������ꍇ���������܂��B�܂����i�̔����󋵂ɂ��A
���i����ɐ��������͂��\�����������܂��B
���̏ꍇ�́A���i���������Ă��炨�x�������������܂��B

����T�Ԃقǂ��҂����������Ă����������͂��Ȃ��ꍇ�́A
��ς��萔�ł͂������܂����A�����[���`���́y�ȈՃ��O�C���y�[�W�z���
�Ĕ��s�̂��葱���������������A���̃��[���̖����ɋL�ڂ��Ă���܂�
{ServiceName}�J�X�^�}�[�Z���^�[�܂ł���񂭂������܂��B


�y���������e�z
�������ԍ��F{OrderId}
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
���������z�F{UseAmount}


<�悭���鎿��W>
Q1.���i�ɂ��Ė₢���킹���������ł��B
A1.���i�Ɋւ��邨�₢���킹�̏ꍇ�́A
�w���X�ɒ��ڂ��₢���킹�����肢�������܂��B
�w���X�܁F{SiteNameKj}�@
�d�b�F{Phone}

Q2.�x�����������߂��Ă��܂��܂������A�ǂ�������悢�ł����H
A2.���󋵂̂��m�F�������Ă��������܂��̂Ŏ��}���Ёi03-4326-3600�j
�܂ł��A�������肢�������܂��B
�������Ɋւ��邨�₢���킹�́A�ȉ��̏��ԂŃ{�^����������肢���܂��B
�y�����K�C�_���X�z���y1�z���y1�z�������Ɋւ��邨�₢���킹

Q3.���������m�F������@�������Ă��������B
A3.�������́A�������ɋL�ڂ��Ă���܂��B���ڂ������́A
���L�E�F�u�y�[�W�ł����m�F���������܂��B
�E�������m�F�y�[�W�@{OrderPageUrl}
�@�����O�C���ɂ͂��������̂��d�b�ԍ��ƁA
�@�@�������ɋL�ڂ���Ă���p�X���[�h�������p���������B

���łɌ㕥���h�b�g�R���E�͂��Ă��略������o�^�����ς̂��q�l�͉��L��胍�O�C�����Ă��������B
�E����l�p�}�C�y�[�W�@https://www.atobarai.jp/mypage
�@�����O�C���ɂ͉���o�^���̃��[���A�h���X�ƁA
�@�@�C�ӂł��o�^�����������p�X���[�h�������p���������B

Q4.�ǂ��Ŏx�������ł��܂����H
A4.�����[���`���́y�ȈՃ��O�C���y�[�W�z��育�m�F���������܂��悤���肢�\���グ�܂��B

Q5.������x���i�𒍕��������ł��B
A5.���萔�ł����A���w�����ꂽ�X�ܗl�ōēx���������肢�������܂��B

���L�y�[�W�ł�������Ɋւ��铚�����������邱�Ƃ��ł��܂��B
https://atobarai-user.jp/

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',null,'2015/08/31 22:42:31',9,'2022/04/20 5:07:07',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (5,5,'���������s���[���iCEL�j','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z���������s�ē��@�i�n�K�L�œ͂��܂��j�@�������ԍ��F{OrderId}','{ServiceName}','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM���������B

���z�[���y�[�W�ɂāA�u�悭���鎿��v���f�ڂ��Ă���܂��̂ŁA�����Ă��Q�Ƃ��������B
�@�z�[���y�[�W�Fhttps://atobarai-user.jp/
�@�������ɂ��āFhttps://atobarai-user.jp/faq/invoice/
�@�����������E�����ɂ��āFhttps://atobarai-user.jp/faq/non-arrival

�������p�󋵂͉��L�������m�F�y�[�W�ł��m�F���������܂��B
�@�ȈՃ��O�C���y�[�W�@{OrderPageAccessUrl}
�@(�ȈՃ��O�C���L�����Ԃ�{LimitDate}���14���ԂƂȂ�܂��B)

�������[������M���Ă���P�T�Ԍo�߂��Ă����������͂��Ȃ��ꍇ��A
�@�������̕����Ȃǂɂ��y�Ĕ��s�˗��z��
�@��L��URL���炨�葱�����������܂��B
��������������������������������������������������������������������
 
{CustomerNameKj}�l

����� {SiteNameKj}�l�ł̂���������
�y{ServiceName}�z�������p���������܂��āA
�܂��Ƃɂ��肪�Ƃ��������܂��B

���������𔭍s�������܂����B
������������A�����܂łɂ��x�������������܂��悤�A
���肢�\���グ�܂��B
���x����������{LimitDate}�ł������܂��B

���������́A���ʗX�ւł̔����ƂȂ�܂��̂ŁA���q�l�̂��茳�ɓ͂��܂�
��T�Ԓ��x������ꍇ���������܂��B�܂����i�̔����󋵂ɂ��A
���i����ɐ��������͂��\�����������܂��B
���̏ꍇ�́A���i���������Ă��炨�x�������������܂��B

����T�Ԃقǂ��҂����������Ă����������͂��Ȃ��ꍇ�́A
��ς��萔�ł͂������܂����A�����[���`���́y�ȈՃ��O�C���y�[�W�z���
�Ĕ��s�̂��葱���������������A���̃��[���̖����ɋL�ڂ��Ă���܂�
{ServiceName}�J�X�^�}�[�Z���^�[�܂ł���񂭂������܂��B


�y���������e�z
�������ԍ��F{OrderId}
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
���������z�F{UseAmount}


<�悭���鎿��W>
Q1.���i�ɂ��Ė₢���킹���������ł��B
A1.���i�Ɋւ��邨�₢���킹�̏ꍇ�́A
�w���X�ɒ��ڂ��₢���킹�����肢�������܂��B
�w���X�܁F{SiteNameKj}�@
�d�b�F{Phone}

Q2.�x�����������߂��Ă��܂��܂������A�ǂ�������悢�ł����H
A2.���󋵂̂��m�F�������Ă��������܂��̂Ŏ��}���Ёi03-4326-3600�j
�܂ł��A�������肢�������܂��B
�������Ɋւ��邨�₢���킹�́A�ȉ��̏��ԂŃ{�^����������肢���܂��B
�y�����K�C�_���X�z���y1�z���y1�z�������Ɋւ��邨�₢���킹

Q3.���������m�F������@�������Ă��������B
A3.�������́A�������ɋL�ڂ��Ă���܂��B���ڂ������́A
���L�E�F�u�y�[�W�ł����m�F���������܂��B
�E�������m�F�y�[�W�@{OrderPageUrl}
�@�����O�C���ɂ͂��������̂��d�b�ԍ��ƁA
�@�@�������ɋL�ڂ���Ă���p�X���[�h�������p���������B

���łɌ㕥���h�b�g�R���E�͂��Ă��略������o�^�����ς̂��q�l�͉��L��胍�O�C�����Ă��������B
�E����l�p�}�C�y�[�W�@https://www.atobarai.jp/mypage
�@�����O�C���ɂ͉���o�^���̃��[���A�h���X�ƁA
�@�@�C�ӂł��o�^�����������p�X���[�h�������p���������B

Q4.�ǂ��Ŏx�������ł��܂����H
A4.�����[���`���́y�ȈՃ��O�C���y�[�W�z��育�m�F���������܂��悤���肢�\���グ�܂��B

Q5.������x���i�𒍕��������ł��B
A5.���萔�ł����A���w�����ꂽ�X�ܗl�ōēx���������肢�������܂��B

���L�y�[�W�ł�������Ɋւ��铚�����������邱�Ƃ��ł��܂��B
https://atobarai-user.jp/

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',null,'2015/08/31 22:42:31',9,'2022/04/20 5:07:30',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (6,6,'�����m�F���[���iPC�j','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z���������m�F�������܂����B�������ԍ��F {OrderId} ','{ServiceName}','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
�������������������������������������������������������������������� 

{CustomerNameKj}�l

���̓x�́A{SiteNameKj}�l�ŏ��i���w���̍ۂɁA
{ServiceName}�������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂����B

{ReceiptDate}��{CustomerNameKj}�l����̂�������
�m�F�������܂����̂ł��񍐂������܂��B

�ȉ����A���񂲓��������������������̓��e�ł������܂��B

�y�̎��ς݂��������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���������z�F{UseAmount}

�܂��̂����p��S��肨�҂����Ă���܂��B

�Ȃ��A�������z�Ƃ��������z�ɍ��ق�����ꍇ�́A���A���A������
�������Ă��������ꍇ���������܂��B

���̑��A���x���Ɋւ��Ă��s���ȓ_�͉��L�t�q�k�����m�F���������B
https://atobarai-user.jp/

�����i�̕ԕi�E�����ȂǏ��i�ɂ��Ă�
���ڂ��w���X�l�ɂ��₢���킹���������B
���w���X�l�F{SiteNameKj}
�d�b�F{Phone}
URL�F{SiteUrl}

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------

',null,'2015/08/31 22:42:31',9,'2022/04/26 2:58:07',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (7,7,'�����m�F���[���iCEL�j','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z���������m�F�������܂����B�������ԍ��F {OrderId} ','{ServiceName}','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
�������������������������������������������������������������������� 

{CustomerNameKj}�l

���̓x�́A{SiteNameKj}�l�ŏ��i���w���̍ۂɁA
{ServiceName}�������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂����B

{ReceiptDate}��{CustomerNameKj}�l����̂�������
�m�F�������܂����̂ł��񍐂������܂��B

�ȉ����A���񂲓��������������������̓��e�ł������܂��B

�y�̎��ς݂��������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���������z�F{UseAmount}

�܂��̂����p��S��肨�҂����Ă���܂��B

�Ȃ��A�������z�Ƃ��������z�ɍ��ق�����ꍇ�́A���A���A������
�������Ă��������ꍇ���������܂��B

���̑��A���x���Ɋւ��Ă��s���ȓ_�͉��L�t�q�k�����m�F���������B
https://atobarai-user.jp/

�����i�̕ԕi�E�����ȂǏ��i�ɂ��Ă�
���ڂ��w���X�l�ɂ��₢���킹���������B
���w���X�l�F{SiteNameKj}
�d�b�F{Phone}
URL�F{SiteUrl}

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------

',null,'2015/08/31 22:42:31',9,'2022/04/26 2:58:17',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (8,8,'���֊������[��','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z���֋����x�����̂���','{ServiceName}','{EnterpriseNameKj} �l

�����y{ServiceName}�z�������p���������܂��āA
�܂��Ƃɂ��肪�Ƃ��������܂��B

���֕��̂��x�����������������܂����̂ŁA
�񍐐\���グ�܂��B

�y�Ǘ���ʂt�q�k�z
https://www.atobarai.jp/member/

���x���T�C�g�@�@�F�@{FixedPattern}
���֒����@�@�@�@�F�@{FixedDate}
�U�����s���@�@�@�F�@{ExecDate}
���x���z�@�@�@�@�F�@{DecisionPayment}�~
���ώ萔���@�@�@�F�@{SettlementFee}�~
�����萔���@�@�@�F�@{ClaimFee}�~
�󎆑㍇�v�@�@�@�F�@{StampFee}�~
�L�����Z���ԋ��@�F�@{CancelAmount}�~
���z�Œ��@�@�@�F�@{MonthlyFee}�~
���U���ݎ萔���@�F�@{TransferCommission}�~

���x���Ɋւ��܂��Ă��s���ȓ_�Ȃǂ������܂�����A
���L�A����܂ł��C�y�ɂ��₢���킹�������܂��B

����Ƃ����ЃT�[�r�X�y{ServiceName}�z���A��낵��
���肢�\���グ�܂��B

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',null,'2015/08/31 22:42:31',9,'2022/04/20 5:49:29',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (9,9,'�L�����Z���m�F���[��','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z�L�����Z���m��̂���({OrderId})','{ServiceName}','{EnterpriseNameKj}�@�l

�����y{ServiceName}�z�������p���������܂��āA�܂��Ƃɂ��肪�Ƃ��������܂��B

�ȉ��̂������̃L�����Z��������܂����̂ŁA���m�F�������B

�y�Ǘ���ʂt�q�k�z
https://www.atobarai.jp/member/

�y�L�����Z���m����z
�L�����Z���敪�F{CancelPhase}
������ID�F{OrderId}
�w���җl�����F{CustomerNameKj}�@�l
���������z�F{UseAmount}
���������F{OrderDate}

���w���җl���X�ܗl�̌����֒��ړ������ꂽ�ꍇ��A�X�ܗl������đ������
�@�������ꂽ�ꍇ���́A�w���җl�ƓX�ܗl�Ԃł̂�������������Ă���ꍇ��
�@�L�����Z�������̍ۂɂ́A����̎萔�������񗧑֎��̒����z�ɂ�
�@���������Ă��������܂��B

���s���ȓ_�Ȃǂ������܂�����A���C�y�ɓ��Ђ܂ł��₢���킹�������B


-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',null,'2015/08/31 22:42:31',9,'2023/01/04 13:21:02',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (10,10,'�A�h���X�m�F���[��','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z�̂��I�����肪�Ƃ��������܂��B�������ԍ��F {OrderId} ','{ServiceName}','{CustomerNameKj}�l

���̓x�́A���x�����@�Ɂy{ServiceName}�z�����I�����������A
�܂��Ƃɂ��肪�Ƃ��������܂��B

�������܁A���L�̂������ɂ����܂���{ServiceName}�������p���������邩�A
�R�����������Ă���܂��B

�y���������e�z
���x���ҁF{CustomerNameKj}�@�l
���w���X�ܖ��F{SiteNameKj}�@
���w�����F{OrderDate}
���x�����z�F{UseAmount}
���w�����i���ׁF���i���^���^�w���i�ڌv
{OrderItems}
���ώ萔��  {SettlementFee}�~
����        {DeliveryFee}�~


���ʂɂ��܂��ẮA���������������܂����X�ܗl���A
��قǂ��A��������܂��̂ŁA�������X���҂��������܂��B

���s���ȓ_�Ȃǂ������܂�����A���C�y�ɂ��₢���킹�������B

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�@�{���ɓ���Ă��⍇�����������B

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',null,'2015/08/31 22:42:31',9,'2022/04/19 16:42:13',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (11,11,'�����������x�����[���iPC�j','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z�����������m�F�̂��m�点�@�������ԍ��F{OrderId}','{ServiceName}','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
�������������������������������������������������������������������� 

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɐ����������茳�ɓ͂��Ă���������A�������͂������̂��葱����
�@���ς̂悤�ł���Γ����[���ւ̕ԐM�͂��s�v�ł������܂��B

{CustomerNameKj}�l

���̓x�́A{SiteNameKj}�l�ł̂��������ɁA
�y{ServiceName}�z�������p���������܂��āA�܂��Ƃɂ��肪�Ƃ��������܂��B
{IssueDate}�ɐ������������肢�����܂������A���茳�ɂ��͂��ł��傤���B

�y���������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
���������z�F{UseAmount}

�����������܂��͂��Ă��Ȃ��ꍇ�͑�ς��萔�ł����A
���}�� 03-4326-3600 �ɂ���񂭂������B
�c�Ǝ��ԁF9:00�`18:00

�����L�����֒��ڂ��U���݂��������܂��Ă��������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ƂȂ�܂�)
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B


�y��s�U�������z
{Bk_BankName}�@{Bk_BranchName}
���ʌ����@{Bk_AccountNumber}
{Bk_AccountHolderKn}
�����������ƂɌ����ԍ����قȂ��Ă���܂��B
����x�����������܂��ƍēx���������󂯕t���邱�Ƃ�
�@�ł��܂���̂ł����ӂ��������B

�y�X�֐U�֌����z
�����L���F00120�]7
�����ԍ��F670031
�J�j�L���b�`�{�[��

�����i�̕ԕi�E�����ȂǏ��i�Ɋւ��邨�₢���킹�́A��ς��萔�ł���
���ڍw���X�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}
�d�b�F{Phone}


���s���ȓ_�Ȃǂ������܂����牺�L�t�q�k�́u�悭���邲����v���������������B
https://atobarai-user.jp/faq/


-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',null,'2015/08/31 22:42:31',9,'2022/04/20 2:06:57',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (12,12,'�����������x�����[���iCEL�j','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z�����������m�F�̂��m�点�@�������ԍ��F{OrderId}','{ServiceName}','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
�������������������������������������������������������������������� 

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɐ����������茳�ɓ͂��Ă���������A�������͂������̂��葱����
�@���ς̂悤�ł���Γ����[���ւ̕ԐM�͂��s�v�ł������܂��B

{CustomerNameKj}�l

���̓x�́A{SiteNameKj}�l�ł̂��������ɁA
�y{ServiceName}�z�������p���������܂��āA�܂��Ƃɂ��肪�Ƃ��������܂��B
{IssueDate}�ɐ������������肢�����܂������A���茳�ɂ��͂��ł��傤���B

�y���������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
���������z�F{UseAmount}

�����������܂��͂��Ă��Ȃ��ꍇ�͑�ς��萔�ł����A
���}�� 03-4326-3600 �ɂ���񂭂������B
�c�Ǝ��ԁF9:00�`18:00

�����L�����֒��ڂ��U���݂��������܂��Ă��������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ƂȂ�܂�)
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B


�y��s�U�������z
{Bk_BankName}�@{Bk_BranchName}
���ʌ����@{Bk_AccountNumber}
{Bk_AccountHolderKn}
�����������ƂɌ����ԍ����قȂ��Ă���܂��B
����x�����������܂��ƍēx���������󂯕t���邱�Ƃ�
�@�ł��܂���̂ł����ӂ��������B

�y�X�֐U�֌����z
�����L���F00120�]7
�����ԍ��F670031
�J�j�L���b�`�{�[��

�����i�̕ԕi�E�����ȂǏ��i�Ɋւ��邨�₢���킹�́A��ς��萔�ł���
���ڍw���X�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}
�d�b�F{Phone}

���s���ȓ_�Ȃǂ������܂����牺�L�t�q�k�́u�悭���邲����v���������������B
https://atobarai-user.jp/faq/


-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',null,'2015/08/31 22:42:31',9,'2022/04/20 2:07:14',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (13,13,'���x�������m�F���[���iPC�j','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z{OrderDate}�@{SiteNameKj}�ł̂��������̌��@�������ԍ��F{OrderId}','{ServiceName}','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɂ������ς݂̏ꍇ�͓����[���ւ̕ԐM�͂��s�v�ł������܂��B


{CustomerNameKj}�l

{OrderDate}��{SiteNameKj}�l�ł̂��������ɁA
�y{ServiceName}�z�����p�����������肪�Ƃ��������܂��B

{IssueDate}�ɐ������������肢�����܂������A
�{�����݂������̊m�F���ł��Ă���܂���B

�y���������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
���������z�F{TotalAmount}

�����x���������߂��Ă��܂��A�ēx�����������s����܂���
�Đ����萔�������Z�����ꍇ���������܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ƂȂ�܂�)
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z
{Bk_BankName}�@{Bk_BranchName}
���ʌ����@{Bk_AccountNumber}
{Bk_AccountHolderKn}
�����������ƂɌ����ԍ����قȂ��Ă���܂��B
����x�����������܂��ƍēx���������󂯕t���邱�Ƃ�
�@�ł��܂���̂ł����ӂ��������B

�y�X�֐U�֌����z
�����L���F00120�]7
�����ԍ��F670031
�J�j�L���b�`�{�[��

���̑��A���x���Ɋւ��Ă��s���ȓ_�͉��L�t�q�k�����m�F���������B

https://atobarai-user.jp/

�����i�̕ԕi�E�����ȂǏ��i�Ɋւ��邨�₢���킹�́F
���ڍw���X�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@
�d�b�F{Phone}

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',null,'2015/08/31 22:42:31',9,'2022/04/20 5:52:11',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (14,14,'���x�������m�F���[���iCEL�j','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z{OrderDate}�@{SiteNameKj}�ł̂��������̌��@�������ԍ��F{OrderId}','{ServiceName}','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɂ������ς݂̏ꍇ�͓����[���ւ̕ԐM�͂��s�v�ł������܂��B


{CustomerNameKj}�l

{OrderDate}��{SiteNameKj}�l�ł̂��������ɁA
�y{ServiceName}�z�����p�����������肪�Ƃ��������܂��B

{IssueDate}�ɐ������������肢�����܂������A
�{�����݂������̊m�F���ł��Ă���܂���B

�y���������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
���������z�F{TotalAmount}

�����x���������߂��Ă��܂��A�ēx�����������s����܂���
�Đ����萔�������Z�����ꍇ���������܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ƂȂ�܂�)
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z
{Bk_BankName}�@{Bk_BranchName}
���ʌ����@{Bk_AccountNumber}
{Bk_AccountHolderKn}
�����������ƂɌ����ԍ����قȂ��Ă���܂��B
����x�����������܂��ƍēx���������󂯕t���邱�Ƃ�
�@�ł��܂���̂ł����ӂ��������B

�y�X�֐U�֌����z
�����L���F00120�]7
�����ԍ��F670031
�J�j�L���b�`�{�[��

���̑��A���x���Ɋւ��Ă��s���ȓ_�͉��L�t�q�k�����m�F���������B

https://atobarai-user.jp/

�����i�̕ԕi�E�����ȂǏ��i�Ɋւ��邨�₢���킹�́F
���ڍw���X�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@
�d�b�F{Phone}

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',null,'2015/08/31 22:42:31',9,'2022/04/20 5:52:18',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (15,15,'�`�[�ԍ��m�F�̂��肢','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z�����֒������ɂ��Ă��m�F�����肢�������܂�','{ServiceName}','{EnterpriseNameKj}�l

�����y{ServiceName}�z�������p���������A�@
�܂��Ƃɂ��肪�Ƃ��������܂��B

���L�̂������ɂ��āA
���݁A(WEB���)���׊m�F����ꂸ�A
���x�����������߂��Ă������̊m�F���ł��Ă��Ȃ����߁A
���ւ��ł��Ă��Ȃ��󋵂ł������܂��̂�
���m�F�����������������A�����������܂����B

�܂��Ƃɂ��萔���������������܂����A
���e�����m�F���������A�y2�T�Ԉȓ��z��
���Ёu���ϊǗ��V�X�e���v���C���A
�܂��͂��A�������������܂��悤���肢�������܂��B
 
���������ɂ��ύX�܂��͂��A���������������A
�z����Ђ̒ǐՃT�[�r�X�ɂĒ��ׂ̊m�F�����Ȃ��Ȃ����ꍇ�A
�w���ۏ؁x�����ƂȂ�A�����w���ԋp�x�i�������̃L�����Z���j��
�����Ă��������܂��̂ł����ӊ肢�܂��B

�����ID �F{OrderId}
�������җl�� �F{CustomerNameKj} �l
�`�[�ԍ��o�^�� �F{Deli_JournalIncDate}
�o�^�z����ЁF{DeliMethodName}
�o�^�`�[�ԍ� �F{Deli_JournalNumber}

�Ȃ��A���ׂ̊m�F�����Ă��Ȃ������Ƃ������܂��ẮA
���L�̂����ꂩ�ɊY������\�����������܂��B

���z����Ђ̑I���ԈႢ
���z���`�[�ԍ��̓��͊ԈႢ
�����q�l�ɏ��i���͂��Ă��Ȃ�
���L�����Z���\���R��
 
�܂��A��̏����������܂��ꍇ�ɂ͓Y�t�����������
���ЂɂĊm�F�������܂��B
 
 
���s���ȓ_�Ȃǂ������܂�����A���C�y�ɂ��₢���킹���������B
 
����Ƃ���낵�����肢�������܂��B

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',null,'2015/08/31 22:42:31',9,'2022/04/20 5:52:48',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (16,16,'�߂萿���Z���m�F���[��','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z�y�d�v�z���Z���m�F�̘A���ł��B{OrderId} ','{ServiceName}','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������  

{CustomerNameKj}�l

{OrderDate}��{SiteNameKj}�ŁA
�y{ServiceName}�z�������p�����������肪�Ƃ��������܂��B
{ClaimDate}�ɂ����肢�����܂��������������Ђɖ߂��Ă��Ă���܂��̂ŁA
���Z���̊m�F�������Ă��������������A�������Ă��������܂����B

�i���q�l�Z���j�@{UnitingAddress}

��L�Z���ɕs��������܂�����A�ēx�������𔭍s�����Ă��������܂��̂�
���A���̒��A��낵�����肢�v���܂��B

�Z���ɕs�����Ȃ��ꍇ�ł��A�\�D����������Ă����ꍇ�ȂǂŁA�X�֕����͂��Ȃ��P�[�X��
����܂��̂ŁA�������������B

�܂��A��s�E�X�֋ǂ���̂��������\�ł��̂�
�����ԍ��������肳���Ă��������܂��B

�y��s�U�������z
{Bk_BankName}�@{Bk_BranchName}
���ʌ����@{Bk_AccountNumber}
{Bk_AccountHolderKn}
�����������ƂɌ����ԍ����قȂ��Ă���܂��B
����x�����������܂��ƍēx���������󂯕t���邱�Ƃ�
�@�ł��܂���̂ł����ӂ��������B

�y�X�֐U�֌����z
�����L���F00120-7
�����ԍ��F670031
�J�j�L���b�`�{�[��


�y���������ׁz
���i���@�@�F{ItemNameKj}
���i����@�F{ItemAmount}�~
�����@�@�@�F{DeliveryFee}�~
�萔���@�@�F{SettlementFee}�~
{OptionFee}
���v�@�@�@�F{UseAmount}�~
 �i�U���萔���͂��q�l�����S�ƂȂ�܂��B�j 

���̑����s���ȓ_�A�������̂����k���͓��Ђ܂ł��₢���킹���������B

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',null,'2015/08/31 22:42:31',9,'2022/04/20 2:08:29',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (17,17,'���������s���[���i�����c�[�������FPC�j','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z�����p�ɂ��Ă̂��ē��@�������ԍ��F{OrderId}','{ServiceName}','��������������������������������������������������������������������
�����肢�F���₢���킹�����ہA�K�����L���[�����ʂ��c�����܂܂��ԐM����������
�������������������������������������������������������������������� 

�������[���́A�y{ServiceName}�z�������p���������܂������q�l�ւ����肵�Ă���܂��B
�u���i�����̂��m�点���[���v�ł͂������܂���B
���i�̔����E�����\����ɂ��Ă͂��w���X�l�ւ̂��₢���킹�����肢�\���グ�܂��B 

 
 {CustomerNameKj}�l

����� {SiteNameKj}�l�ł̂���������
�y{ServiceName}�z�������p���������܂��āA
�܂��Ƃɂ��肪�Ƃ��������܂��B

������������������������������������������������������������������������

���������i�����p���j�͏��i�ƈꏏ�ɂ��͂��������܂��̂ŁA
���i������A�������ɋL�ڂ̂��x�����������܂łɂ��x�������������܂��悤�A
���肢�\���グ�܂��B

������������������������������������������������������������������������


�y���������e�z
���x���ҁF{CustomerNameKj}�@�l
�������ԍ��F{OrderId}
���w���X�ܖ��F{SiteNameKj}�@
���w�����F{OrderDate}
���x�����z�F{UseAmount}
���w�����i���ׁF���i���^���^�w���i�ڌv
{OrderItems}
���ώ萔��                              {SettlementFee}�~
����                                    {DeliveryFee}�~

�����i�Ƌ��ɐ������������Ă��Ȃ��ꍇ�ɂ́A��ς��萔�ł����A
�@�y{ServiceName}�z�ւ���񂭂������܂��悤�A���肢�\���グ�܂��B

���L�����Z���i���\���j����Ă���ꍇ�ł��A�s���Ⴂ�ɂē����[����
�@�z�M����Ă��܂��ꍇ���������܂��B���̍ۂ͑�ς��萔�ł����A���L
�@�w���X�ܗl�ɒ��ڂ��⍇�����������B

���������̑����s���ȓ_�͉��L�t�q�k�����m�F���������B������
�@�@�@�@https://atobarai-user.jp/faq/

�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹��
���ڍw���X�ܗl�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}

�����x�����Ɋւ��邨�₢���킹��
������ЃL���b�`�{�[��
TEL:03-4326-3600 (�����y��9:00�`18:00)
Mail: {ServiceMail}

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',null,'2015/08/31 22:42:31',9,'2022/04/20 2:09:43',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (18,18,'���������s���[���i�����c�[�������FCEL�j','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z�����p�ɂ��Ă̂��ē��@�������ԍ��F{OrderId}','{ServiceName}','��������������������������������������������������������������������
�����肢�F���₢���킹�����ہA�K�����L���[�����ʂ��c�����܂܂��ԐM����������
�������������������������������������������������������������������� 

�������[���́A�y{ServiceName}�z�������p���������܂������q�l�ւ����肵�Ă���܂��B
�u���i�����̂��m�点���[���v�ł͂������܂���B
���i�̔����E�����\����ɂ��Ă͂��w���X�l�ւ̂��₢���킹�����肢�\���グ�܂��B 

 
 {CustomerNameKj}�l

����� {SiteNameKj}�l�ł̂���������
�y{ServiceName}�z�������p���������܂��āA
�܂��Ƃɂ��肪�Ƃ��������܂��B

������������������������������������������������������������������������

���������i�����p���j�͏��i�ƈꏏ�ɂ��͂��������܂��̂ŁA
���i������A�������ɋL�ڂ̂��x�����������܂łɂ��x�������������܂��悤�A
���肢�\���グ�܂��B

������������������������������������������������������������������������


�y���������e�z
���x���ҁF{CustomerNameKj}�@�l
�������ԍ��F{OrderId}
���w���X�ܖ��F{SiteNameKj}�@
���w�����F{OrderDate}
���x�����z�F{UseAmount}
���w�����i���ׁF���i���^���^�w���i�ڌv
{OrderItems}
���ώ萔��                              {SettlementFee}�~
����                                    {DeliveryFee}�~

�����i�Ƌ��ɐ������������Ă��Ȃ��ꍇ�ɂ́A��ς��萔�ł����A
�@�y{ServiceName}�z�ւ���񂭂������܂��悤�A���肢�\���グ�܂��B

���L�����Z���i���\���j����Ă���ꍇ�ł��A�s���Ⴂ�ɂē����[����
�@�z�M����Ă��܂��ꍇ���������܂��B���̍ۂ͑�ς��萔�ł����A���L
�@�w���X�ܗl�ɒ��ڂ��⍇�����������B

���������̑����s���ȓ_�͉��L�t�q�k�����m�F���������B������
�@�@�@�@https://atobarai-user.jp/faq/

�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹��
���ڍw���X�ܗl�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}

�����x�����Ɋւ��邨�₢���킹��
������ЃL���b�`�{�[��
TEL:03-4326-3600 (�����y��9:00�`18:00)
Mail: {ServiceMail}

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',null,'2015/08/31 22:42:31',9,'2022/04/20 2:09:34',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (19,19,'�^�M���ʃ��[��(OK, PC)','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y {ServiceName} �z�^�M���ʂ̂��m�点','{ServiceName}','��������������������������������������������������������������������
�����肢�F���₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������


{CustomerNameKj} �l

���̂��т�{SiteNameKj}��{OneOrderItem}�����������ꂽ�ۂ̂��x�������@�ɁA
�y{ServiceName}�z�����I�����������܂��āA�܂��Ƃɂ��肪�Ƃ��������܂��B

���̂��т̂������ɂ��܂��āA�y{ServiceName}�z�̗^�M�R����
�ʉ߂������܂������Ƃ����񍐐\���グ�܂��B
���������𔭍s�̍ۂ́A���߂ĕ��Ђ�胁�[���������肢�����܂��̂ł��m�F���������B


�Ȃ��A���������������܂������i�ɂ��A�ȉ��̓��e�Ɋւ��܂��Ă�
{SiteNameKj}�ł̑Ή��ƂȂ�܂��̂ŁA���ڂ��A�����������܂��悤���肢�\���グ�܂��B

�@�E���i�Ɋւ��邨�₢���킹
�@�E���������e�̕ύX
�@�E�������̃L�����Z��

�y{SiteNameKj}�z
{ContactPhoneNumber}


�܂��A{ServiceName}���ςɊւ��A���s���ȓ_�Ȃǂ������܂�����A
���L�A�y{ServiceName}�z�J�X�^�}�[�Z���^�[�ւ��₢���킹���������B

�y�y{ServiceName}�z�J�X�^�}�[�Z���^�[�z
�^�c��ЁF(��)�L���b�`�{�[��
TEL:03-4326-3600
�c�Ǝ��ԁF9:00�`18:00�@�N�����x(�N���E�N�n���̂���)

�ȏ�A����Ƃ��A��낵�����肢�\���グ�܂��B

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',null,'2015/08/31 22:42:31',9,'2022/04/20 2:28:00',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (20,20,'�^�M���ʃ��[��(OK, CEL)','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y {ServiceName} �z�^�M���ʂ̂��m�点','{ServiceName}','��������������������������������������������������������������������
�����肢�F���₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������


{CustomerNameKj} �l

���̂��т�{SiteNameKj}��{OneOrderItem}�����������ꂽ�ۂ̂��x�������@�ɁA
�y{ServiceName}�z�����I�����������܂��āA�܂��Ƃɂ��肪�Ƃ��������܂��B

���̂��т̂������ɂ��܂��āA�y{ServiceName}�z�̗^�M�R����
�ʉ߂������܂������Ƃ����񍐐\���グ�܂��B
���������𔭍s�̍ۂ́A���߂ĕ��Ђ�胁�[���������肢�����܂��̂ł��m�F���������B


�Ȃ��A���������������܂������i�ɂ��A�ȉ��̓��e�Ɋւ��܂��Ă�
{SiteNameKj}�ł̑Ή��ƂȂ�܂��̂ŁA���ڂ��A�����������܂��悤���肢�\���グ�܂��B

�@�E���i�Ɋւ��邨�₢���킹
�@�E���������e�̕ύX
�@�E�������̃L�����Z��

�y{SiteNameKj}�z
{ContactPhoneNumber}


�܂��A{ServiceName}���ςɊւ��A���s���ȓ_�Ȃǂ������܂�����A
���L�A�y{ServiceName}�z�J�X�^�}�[�Z���^�[�ւ��₢���킹���������B

�y�y{ServiceName}�z�J�X�^�}�[�Z���^�[�z
�^�c��ЁF(��)�L���b�`�{�[��
TEL:03-4326-3600
�c�Ǝ��ԁF9:00�`18:00�@�N�����x(�N���E�N�n���̂���)

�ȏ�A����Ƃ��A��낵�����肢�\���グ�܂��B

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',null,'2015/08/31 22:42:31',9,'2022/04/20 2:28:08',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (21,21,'�^�M���ʃ��[��(NG, PC)','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y {ServiceName} �z�^�M���ʂ̂��m�点','{ServiceName}','��������������������������������������������������������������������
�����肢�F���₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

{CustomerNameKj} �l

���̂��т�{SiteNameKj}��{OneOrderItem}�����������ꂽ�ۂ̂��x�������@��
�y{ServiceName}�z�����I�����������܂��āA�܂��Ƃɂ��肪�Ƃ��������܂��B

���̂��т̂������ɂ��A�y{ServiceName}�z�̗^�M�R���̌��ʁA
�R�����ʉ߂������܂���ł����̂ł��m�点�������܂��B

���܂��ẮA�܂��Ƃɂ��萔�ł͂������܂����A 
{SiteNameKj}�ւ��A���̂����A���̂��x�������@�ɂ��ύX�����������������܂��B

�y{SiteNameKj}�z
{ContactPhoneNumber}

�����x�������@�����ύX�����������ꍇ�A
�@�y{ServiceName}�z�Ɋւ���萔���͈�ؔ����������܂���B


�Ȃ��A���������������܂������i�ɂ��A�ȉ��̓��e�Ɋւ��܂��Ă�
{SiteNameKj}�ł̑Ή��ƂȂ�܂��̂ŁA���ڂ��A�����������܂��悤���肢�\���グ�܂��B

�@�E���i�Ɋւ��邨�₢���킹
�@�E���������e�̕ύX
�@�E�������̃L�����Z��


�܂��A�y{ServiceName}�z�̗^�M�R���ɂ��܂��ẮA
�y{ServiceName}�z���^�c���Ă���܂�(��)�L���b�`�{�[���ɂčs���Ă���܂��B

�^�M�R�����ʂɊւ���ڍׂ̓��e�ɂ��܂��ẮA�l�����܂ޓ��e�ɂȂ�܂����߁A
���Ђ���{SiteNameKj}�ւ͈�؊J�����Ă���܂���B

�^�M�R�����ʂɊւ��邨�₢���킹�ɂ��܂��ẮA
���ڂ��d�b�ɂĕ��Ђւ��A�����������܂��悤���肢�\���グ�܂��B

�y�y{ServiceName}�z�J�X�^�}�[�Z���^�[�z
�^�c��ЁF�i���j�L���b�`�{�[��
TEL:03-4326-3600
�c�Ǝ��ԁF9:00�`18:00�@�N�����x(�N���E�N�n���̂���)

�ȏ�A��낵�����肢�\���グ�܂��B

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',null,'2015/08/31 22:42:31',9,'2022/04/20 2:28:13',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (22,22,'�^�M���ʃ��[��(NG, CEL)','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y {ServiceName} �z�^�M���ʂ̂��m�点','{ServiceName}','��������������������������������������������������������������������
�����肢�F���₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

{CustomerNameKj} �l

���̂��т�{SiteNameKj}��{OneOrderItem}�����������ꂽ�ۂ̂��x�������@��
�y{ServiceName}�z�����I�����������܂��āA�܂��Ƃɂ��肪�Ƃ��������܂��B

���̂��т̂������ɂ��A�y{ServiceName}�z�̗^�M�R���̌��ʁA
�R�����ʉ߂������܂���ł����̂ł��m�点�������܂��B

���܂��ẮA�܂��Ƃɂ��萔�ł͂������܂����A 
{SiteNameKj}�ւ��A���̂����A���̂��x�������@�ɂ��ύX�����������������܂��B

�y{SiteNameKj}�z
{ContactPhoneNumber}

�����x�������@�����ύX�����������ꍇ�A
�@�y{ServiceName}�z�Ɋւ���萔���͈�ؔ����������܂���B


�Ȃ��A���������������܂������i�ɂ��A�ȉ��̓��e�Ɋւ��܂��Ă�
{SiteNameKj}�ł̑Ή��ƂȂ�܂��̂ŁA���ڂ��A�����������܂��悤���肢�\���グ�܂��B

�@�E���i�Ɋւ��邨�₢���킹
�@�E���������e�̕ύX
�@�E�������̃L�����Z��


�܂��A�y{ServiceName}�z�̗^�M�R���ɂ��܂��ẮA
�y{ServiceName}�z���^�c���Ă���܂�(��)�L���b�`�{�[���ɂčs���Ă���܂��B

�^�M�R�����ʂɊւ���ڍׂ̓��e�ɂ��܂��ẮA�l�����܂ޓ��e�ɂȂ�܂����߁A
���Ђ���{SiteNameKj}�ւ͈�؊J�����Ă���܂���B

�^�M�R�����ʂɊւ��邨�₢���킹�ɂ��܂��ẮA
���ڂ��d�b�ɂĕ��Ђւ��A�����������܂��悤���肢�\���グ�܂��B

�y�y{ServiceName}�z�J�X�^�}�[�Z���^�[�z
�^�c��ЁF�i���j�L���b�`�{�[��
TEL:03-4326-3600
�c�Ǝ��ԁF9:00�`18:00�@�N�����x(�N���E�N�n���̂���)

�ȏ�A��낵�����肢�\���グ�܂��B

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',null,'2015/08/31 22:42:31',9,'2022/04/20 2:28:18',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (23,23,'�p�X���[�h��񂨒m�点���[��','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z�p�X���[�h���̂��m�点','{ServiceName}','{EnterpriseNameKj}�@�l

���̓x�͕��ЃT�[�r�X�y{ServiceName}�z�ɂ��\���݂��������A
�܂��Ƃɂ��肪�Ƃ��������܂��B


���ϊǗ��V�X�e���̃��O�C���ɕK�v��
�p�X���[�h�����m�点�������܂��B

PW�F{GeneratedPassword}


�ȏ�ł������܂��B


����Ƃ������A��낵�����肢�������܂��B

������ЃL���b�`�{�[��
�@�y{ServiceName}�z�@�X�^�b�t�ꓯ

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------

',null,'2015/08/31 22:42:31',9,'2022/04/20 2:11:52',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (24,4,'���������s���[���iPC�j','������Ђd�X�g�A�[�i�㕥�������j','=?UTF-8?B?GyRCM3Q8MDJxPFIjRSU5JUglIiE8IUo4ZUonJCRBazh9IUsbKEI=?=','ato-barai.sp@estore.co.jp',null,null,null,'�y���A���z�������𔭍s���܂����@�i�n�K�L�œ͂��܂��j','=?UTF-8?B?5qCq5byP5Lya56S+77yl44K544OI44Ki44O877yI5b6M5omV44GE56qT5Y+j?=
 =?UTF-8?B?77yJ?=','��������������������������������������������������������������������������
�����肢�F���₢���킹�����ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������������

{CustomerNameKj}�l


����͂��������������܂��āA���ɂ��肪�Ƃ��������܂��B


���L�̃V���b�s���O�̂���������{�����s�������܂��̂ŁA������������A
�������ɋL�ڂ���Ă��邨�x���������܂łɂ��x�������������܂��悤�A
���肢�\���グ�܂��B


�y���������e�z

���x���ҁF{CustomerNameKj}�@�l

���w���X�ܖ��F{SiteNameKj}�@

���w�����F{OrderDate}

���x�����z�F{UseAmount}

���w�����i���ׁF���i���^���^�w���i�ڌv

{OrderItems}

���ώ萔��                              \{SettlementFee}

����                                    \{DeliveryFee}


���X�����̂Ȃǂɂ��A���������͂��Ȃ����Ƃ��������܂��B
��T�Ԃقǂ��҂����������Ă����������͂��Ȃ��ꍇ�ɂ́A��ς��萔�ł����A
���L�A����ւ���񂭂������܂��悤�A���肢�\���グ�܂��B


�����i�ɂ��܂��Ă��A���[���ւȂǂ̔z�����@�̏ꍇ�ɂ́A�z�����̂Ȃǂɂ��
�͂��Ȃ��ꍇ���������܂��B
������A���i���͂��Ă��Ȃ��ꍇ�ɂ͑�ς��萔�ł͂������܂����A���������ꂽ
�X�ܗl�܂Œ��ڂ��⍇�����������܂��B


�����������тɖ{���[�����A���i���������������Ă��܂����Ƃ��������܂����A
�������e�͉������܂��悤���肢�\���グ�܂��B
�܂��A���������ɖ��ׂ��܂܂�Ă���܂��̂ł��m�F���������܂��B


�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�{���ɓ���Ă��⍇�����������B


���L�����Z���i���\���j����Ă���ꍇ�ł��A�s���Ⴂ�ɂē����[����
�z�M����Ă��܂��ꍇ���������܂��B���̍ۂ͑�ς��萔�ł����A���L
�w���X�ܗl�ɒ��ڂ��⍇�����������B



�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F

�@���ڍw���X�ܗl�ɂ��₢���킹���������B
�@�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}



�����x�����Ɋւ��邨�₢���킹�́F

  ���⍇����F03-6908-5100
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: ato-barai.sp@estore.co.jp
  �^�c��ЁF������Ђd�X�g�A�[�@�㕥������ 
�@�Z���F��105-0003 �����s�`�搼�V��1-10-2�@�Z�F�������V���r��9F
',1,'2015/08/31 22:42:31',9,'2015/12/01 11:47:58',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (25,5,'���������s���[���iCEL�j','������Ђd�X�g�A�[�i�㕥�������j','=?UTF-8?B?GyRCM3Q8MDJxPFIjRSU5JUglIiE8IUo4ZUonJCRBazh9IUsbKEI=?=','ato-barai.sp@estore.co.jp',null,null,null,'�y���A���z�������i�n�K�L�j�𔭍s���܂���','=?UTF-8?B?5qCq5byP5Lya56S+77yl44K544OI44Ki44O877yI5b6M5omV44GE56qT5Y+j?=
 =?UTF-8?B?77yJ?=','�����肢�F���₢���킹�����ہA���L���[�����ʂ��c�����܂܂��ԐM����������


{CustomerNameKj}�l


����͂��������������܂��āA���ɂ��肪�Ƃ��������܂��B

���L�̃V���b�s���O�̂���������{�����s�������܂��̂ŁA������������A
�������ɋL�ڂ���Ă��邨�x���������܂łɂ��x�������������܂��悤�A
���肢�\���グ�܂��B
{OrderPageAccessUrl}

�y���������e�z
���x���ҁF{CustomerNameKj}�@�l
���w���X�ܖ��F{SiteNameKj}�@
���w�����F{OrderDate}
���x�����z�F{UseAmount}
���w�����i���ׁF���i���^���^�w���i�ڌv
{OrderItems}
���ώ萔��  \{SettlementFee}
���� \{DeliveryFee}

���X�����̂Ȃǂɂ��A���������͂��Ȃ����Ƃ��������܂��B
��T�Ԃقǂ��҂����������Ă����������͂��Ȃ��ꍇ�ɂ́A��ς��萔�ł����A
���L�A����ւ���񂭂������܂��悤�A���肢�\���グ�܂��B

�����i�ɂ��܂��Ă��A���[���ւȂǂ̔z�����@�̏ꍇ�ɂ́A�z�����̂Ȃǂɂ��
�͂��Ȃ��ꍇ���������܂��B
������A���i���͂��Ă��Ȃ��ꍇ�ɂ͑�ς��萔�ł͂������܂����A���������ꂽ
�X�ܗl�܂Œ��ڂ��⍇�����������܂��B

�����������тɖ{���[���A���i���������������Ă��܂����Ƃ��������܂����A
�������e�͉������܂��悤���肢�\���グ�܂��B
�܂��A���������ɖ��ׂ��܂܂�Ă���܂��̂ł��m�F���������܂��B

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�{���ɓ���Ă��⍇�����������B

���L�����Z���i���\���j����Ă���ꍇ�ł��A�s���Ⴂ�ɂē����[����
�z�M����Ă��܂��ꍇ���������܂��B���̍ۂ͑�ς��萔�ł����A���L
�w���X�ܗl�ɒ��ڂ��⍇�����������B

�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
���ڍw���X�ܗl�ɂ��₢���킹���������B
�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}

�����x�����Ɋւ��邨�₢���킹�́F
03-6908-5100
 �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 ato-barai.sp@estore.co.jp
 �^�c��ЁF������Ђd�X�g�A�[�@�㕥������
��105-0003 �����s�`�搼�V��1-10-2�@�Z�F�������V���r��9F',1,'2015/08/31 22:42:31',9,'2021/03/10 14:29:31',18008,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (26,6,'�����m�F���[���iPC�j','������Ђd�X�g�A�[�i�㕥�������j','=?UTF-8?B?GyRCM3Q8MDJxPFIjRSU5JUglIiE8IUo4ZUonJCRBazh9IUsbKEI=?=','ato-barai.sp@estore.co.jp',null,null,null,'�y���A���z���������m�F���܂���','=?UTF-8?B?5qCq5byP5Lya56S+77yl44K544OI44Ki44O877yI5b6M5omV44GE56qT5Y+j?=
 =?UTF-8?B?77yJ?=','��������������������������������������������������������������������������
�����肢�F���₢���킹�����ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������������

{CustomerNameKj}�@�l


����͂��������������܂��āA���ɂ��肪�Ƃ��������܂��B

{ReceiptDate}��{CustomerNameKj}�l����̂�������
�m�F�������܂����̂ł��񍐐\���グ�܂��B


�ȉ����A���񂲓��������������������̓��e�ƂȂ�܂��B


�y�̎��ς݂��������e�z

���x���ҁF{CustomerNameKj}�@�l

���w���X�ܖ��F{SiteNameKj}�@

���w�����F{OrderDate}

���x�����z�F{UseAmount}

���w�����i���ׁF���i���^���^�w���i�ڌv

{OrderItems}

���ώ萔��                              \{SettlementFee}

����                                    \{DeliveryFee}



���w���X�ܖ��F{SiteNameKj}
���A����F{Phone}
�Z���F{Address}


���s���ȓ_�Ȃǂ������܂�����A���C�y�ɂ��₢���킹���������B
�܂��̂����p��S���A���҂����Ă���܂��B


�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹��
���ڍw���X�ܗl�ɂ��₢���킹���������B


�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O
�i�t���l�[���j��{���ɓ���Ă��⍇�����������B


�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F

�@���ڍw���X�ܗl�ɂ��₢���킹���������B
�@�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}


�����x�����Ɋւ��邨�₢���킹�́F

  ���⍇����F03-6908-5100
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: ato-barai.sp@estore.co.jp
  �^�c��ЁF������Ђd�X�g�A�[�@�㕥������ 
�@�Z���F��105-0003 �����s�`�搼�V��1-10-2�@�Z�F�������V���r��9F',1,'2015/08/31 22:42:31',9,'2015/12/01 11:59:35',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (27,7,'�����m�F���[���iCEL�j','������Ђd�X�g�A�[�i�㕥�������j','=?UTF-8?B?GyRCM3Q8MDJxPFIjRSU5JUglIiE8IUo4ZUonJCRBazh9IUsbKEI=?=','ato-barai.sp@estore.co.jp',null,null,null,'�y���A���z���������m�F���܂���','=?UTF-8?B?5qCq5byP5Lya56S+77yl44K544OI44Ki44O877yI5b6M5omV44GE56qT5Y+j?=
 =?UTF-8?B?77yJ?=','
�����肢�F���₢���킹�����ہA���L���[�����ʂ��c�����܂܂��ԐM����������


{CustomerNameKj}�l
����͂��������������܂��āA���ɂ��肪�Ƃ��������܂��B

{ReceiptDate}��{CustomerNameKj}�l����̂�������
�m�F�������܂����̂ł��񍐐\���グ�܂��B

�ȉ����A���񂲓��������������������̓��e�ƂȂ�܂��B

�y�̎��ς݂��������e�z
���x���ҁF{CustomerNameKj}�@�l
���w���X�ܖ��F{SiteNameKj}�@
���w�����F{OrderDate}
���x�����z�F{UseAmount}
���w�����i���ׁF���i���^���^�w���i�ڌv
{OrderItems}
���ώ萔�� \{SettlementFee}
����       \{DeliveryFee}

���w���X�ܖ��F{SiteNameKj}
���A����F{Phone}
�Z���F{Address}

���s���ȓ_�Ȃǂ������܂�����A���C�y�ɂ��₢���킹���������B

�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹��
���ڍw���X�ܗl�ɂ��₢���킹���������B

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O
�i�t���l�[���j��{���ɓ���Ă��⍇�����������B

�܂��̂����p��S���A���҂����Ă���܂��B

�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
���ڍw���X�ܗl�ɂ��₢���킹���������B
�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}

�����x�����Ɋւ��邨�₢���킹�́F
03-6908-5100
 �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 ato-barai.sp@estore.co.jp
 �^�c��ЁF������Ђd�X�g�A�[�@�㕥������
��105-0003 �����s�`�搼�V��1-10-2�@�Z�F�������V���r��9F',1,'2015/08/31 22:42:31',9,'2015/12/01 12:55:11',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (28,11,'�����������x�����[���iPC�j','������Ђd�X�g�A�[�i�㕥�������j','=?UTF-8?B?GyRCM3Q8MDJxPFIjRSU5JUglIiE8IUo4ZUonJCRBazh9IUsbKEI=?=','ato-barai.sp@estore.co.jp',null,null,null,'�y���A���z�����������x���������ł�','=?UTF-8?B?5qCq5byP5Lya56S+77yl44K544OI44Ki44O877yI5b6M5omV44GE56qT5Y+j?=
 =?UTF-8?B?77yJ?=','��������������������������������������������������������������������������
�����肢�F���₢���킹�����ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������������

{CustomerNameKj}�l


����͂��������������܂��āA���ɂ��肪�Ƃ��������܂��B


{IssueDate}�ɐ������������肢�����܂������A���茳�ɂ��͂��ł��傤���B
�����肵���������̂��x�����������߂Â��Ă܂���܂����̂ŁA���m�点�������܂��B


���y���E�j�Փ��͓����̊m�F�����Ȃ��ׁA���̊Ԃɂ��葱�������������ꍇ�A
����Ⴂ�œ����[���������Ă��܂��܂��B
���̏ꍇ�́A�܂��Ƃɐ\���󂲂����܂��񂪁A�����[�����폜���Ă��������܂��悤
���肢�\���グ�܂��B

�i�X�֋ǂł��葱�������������ꍇ�A�m�F�ɍő�4�c�Ɠ�������ꍇ���������܂��̂ŁA
�O����O�X���Ɍ�葱�����������Ă���܂��Ă��A�����悤�ɓ���Ⴂ�œ����[����
�͂��Ă��܂��ꍇ���������܂��B�j


���x���������F{LimitDate}

���������F{OrderDate}

�������X�܁F{SiteNameKj}

���������z�F{UseAmount}

���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}

�������F{IssueDate}

 
�܂����x�������������Ă��Ȃ��ꍇ�́A���Ђ�肨���肢�����܂�����������
���m�F�̂����A��L�������܂łɂ��x�������������܂��悤�A���肢�\���グ�܂��B


���������߂��Ă��܂��܂���
�Đ����萔�������Z����܂��̂ŁA�����ӂ��������B


�����L�����֒��ڂ��U���݂��������܂��Ă��A�������̊m�F�͎��܂��B
�����ւ��U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z
�W���p���l�b�g��s
�����ߎx�X
���ʗa���@6291494
������ЃL���b�`�{�[���^�d�X�g�A�[��p����

�y�X�֐U�֌����z
�����L���F00140-5
�����ԍ��F665145
������Ё@�L���b�`�{�[���^�d�X�g�A�[��p

���X�֋ǁ^��s���炨�U���݂��������ꍇ�A�U���萔�������q�l�����S�ƂȂ�܂��B

���X�֋ǂ̌����������̏ꍇ�́A�X�֋ǂ̂`�s�l�𗘗p���Č�������
����������������΁A�X�֐U���萔���͂�����܂���B(�X�܌��ώ萔���Ƃ͕ʂł��B)

�����ꐿ���������茳�ɓ͂��Ă��Ȃ��ꍇ��A���x���Ɋւ��܂��āA���s���ȓ_��
�������܂�����A���L�܂ł��C�y�ɂ��₢���킹���������܂��B

�����i�ɂ��܂��Ă��A���[���ւȂǂ̔z�����@�̏ꍇ�ɂ́A�z�����̂Ȃǂɂ��
�͂��Ȃ��ꍇ���������܂��B
������A���i���͂��Ă��Ȃ��ꍇ�ɂ͑�ς��萔�ł͂������܂����A���������ꂽ
�X�ܗl�܂Œ��ڂ��⍇�����������܂��B


�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�{���ɓ���Ă��⍇�����������B

���L�����Z���i���\���j����Ă���ꍇ�ł��A�s���Ⴂ�ɂē����[����
�z�M����Ă��܂��ꍇ���������܂��B���̍ۂ͑�ς��萔�ł����A�������ꂽ
�X�ܗl�ɒ��ڂ��⍇�����������B


�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F

�@���ڍw���X�ܗl�ɂ��₢���킹���������B
�@�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}

�����x�����Ɋւ��邨�₢���킹�́F

  ���⍇����F03-6908-5100
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: ato-barai.sp@estore.co.jp
  �^�c��ЁF������Ђd�X�g�A�[�@�㕥������
  �Z���F��105-0003�@�����s�`�搼�V��1-10-2  �Z�F�������V���r���XF',1,'2015/08/31 22:42:31',9,'2015/12/01 12:03:09',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (29,12,'�����������x�����[���iCEL�j','������Ђd�X�g�A�[�i�㕥�������j','=?UTF-8?B?GyRCM3Q8MDJxPFIjRSU5JUglIiE8IUo4ZUonJCRBazh9IUsbKEI=?=','ato-barai.sp@estore.co.jp',null,null,null,'�y���A���z�����������x���������ł�','=?UTF-8?B?5qCq5byP5Lya56S+77yl44K544OI44Ki44O877yI5b6M5omV44GE56qT5Y+j?=
 =?UTF-8?B?77yJ?=','
�����肢�F���₢���킹�����ہA���L���[�����ʂ��c�����܂܂��ԐM����������


{CustomerNameKj}�l


����͂��������������܂��āA���ɂ��肪�Ƃ��������܂��B


{IssueDate}�ɐ������������肢�����܂������A���茳�ɂ��͂��ł��傤���B
�����肵���������̂��x�����������߂Â��Ă܂���܂����̂ŁA���m�点�������܂��B


���y���E�j�Փ��͓����̊m�F�����Ȃ��ׁA���̊Ԃɂ��葱�������������ꍇ�A
����Ⴂ�œ����[���������Ă��܂��܂��B
���̏ꍇ�́A�܂��Ƃɐ\���󂲂����܂��񂪁A�����[�����폜���Ă��������܂��悤
���肢�\���グ�܂��B

�i�X�֋ǂł��葱�������������ꍇ�A�m�F�ɍő�4�c�Ɠ�������ꍇ���������܂��̂ŁA
�O����O�X���Ɍ�葱�����������Ă���܂��Ă��A�����悤�ɓ���Ⴂ�œ����[����
�͂��Ă��܂��ꍇ���������܂��B�j


���x���������F{LimitDate}

���������F{OrderDate}

�������X�܁F{SiteNameKj}

���������z�F{UseAmount}

���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}

�������F{IssueDate}

 
�܂����x�������������Ă��Ȃ��ꍇ�́A���Ђ�肨���肢�����܂�����������
���m�F�̂����A��L�������܂łɂ��x�������������܂��悤�A���肢�\���グ�܂��B


���������߂��Ă��܂��܂���
�Đ����萔�������Z����܂��̂ŁA�����ӂ��������B


�����L�����֒��ڂ��U���݂��������܂��Ă��A�������̊m�F�͎��܂��B
�����ւ��U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z
�W���p���l�b�g��s
�����ߎx�X
���ʗa���@6291494
������ЃL���b�`�{�[���^�d�X�g�A�[��p����

�y�X�֐U�֌����z
�����L���F00140-5
�����ԍ��F665145
������Ё@�L���b�`�{�[���^�d�X�g�A�[��p

���X�֋ǁ^��s���炨�U���݂��������ꍇ�A�U���萔�������q�l�����S�ƂȂ�܂��B

���X�֋ǂ̌����������̏ꍇ�́A�X�֋ǂ̂`�s�l�𗘗p���Č�������
����������������΁A�X�֐U���萔���͂�����܂���B(�X�܌��ώ萔���Ƃ͕ʂł��B)

�����ꐿ���������茳�ɓ͂��Ă��Ȃ��ꍇ��A���x���Ɋւ��܂��āA���s���ȓ_��
�������܂�����A���L�܂ł��C�y�ɂ��₢���킹���������܂��B

�����i�ɂ��܂��Ă��A���[���ւȂǂ̔z�����@�̏ꍇ�ɂ́A�z�����̂Ȃǂɂ��
�͂��Ȃ��ꍇ���������܂��B
������A���i���͂��Ă��Ȃ��ꍇ�ɂ͑�ς��萔�ł͂������܂����A���������ꂽ
�X�ܗl�܂Œ��ڂ��⍇�����������܂��B


�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�{���ɓ���Ă��⍇�����������B

���L�����Z���i���\���j����Ă���ꍇ�ł��A�s���Ⴂ�ɂē����[����
�z�M����Ă��܂��ꍇ���������܂��B���̍ۂ͑�ς��萔�ł����A�������ꂽ
�X�ܗl�ɒ��ڂ��⍇�����������B


�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F

�@���ڍw���X�ܗl�ɂ��₢���킹���������B
�@�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}

�����x�����Ɋւ��邨�₢���킹�́F

  ���⍇����F03-6908-5100
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: ato-barai.sp@estore.co.jp
  �^�c��ЁF������Ђd�X�g�A�[�@�㕥������
  �Z���F��105-0003�@�����s�`�搼�V��1-10-2  �Z�F�������V���r���XF',1,'2015/08/31 22:42:31',9,'2015/12/01 12:55:30',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (32,16,'�߂萿���Z���m�F���[��','������Ђd�X�g�A�[�i�㕥�������j','=?UTF-8?B?GyRCM3Q8MDJxPFIjRSU5JUglIiE8IUo4ZUonJCRBazh9IUsbKEI=?=','ato-barai.sp@estore.co.jp',null,null,null,'�y�d�v�z���Z���m�F�̂��肢�i�d�X�g�A�[�㕥�������j','=?UTF-8?B?5qCq5byP5Lya56S+77yl44K544OI44Ki44O877yI5b6M5omV44GE56qT5Y+j?=
 =?UTF-8?B?77yJ?=','��������������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������������

{CustomerNameKj}�l

����͂��������������܂��āA���ɂ��肪�Ƃ��������܂��B

{ClaimDate}�ɂ����肢�����܂��������������Ђɖ߂��Ă��Ă���܂��̂ŁA
���Z���̊m�F�������Ă��������������A���������グ�܂����B


�i���q�l�Z���j�@{UnitingAddress}


��L���Z���ɂ��ԈႢ�͂Ȃ��ł��傤���B

���Z���ɕs�����Ȃ��Ă��A�\�D����������Ă����ꍇ��
�X�֕����͂��Ȃ��P�[�X���������܂��B
�K�����m�点���������܂��悤���肢�������܂��B

�܂��A��s�E�X�֋ǂ���̂��������\�ł��̂�
�����ԍ��������肳���Ă��������܂��B

�y��s�U�������z
�W���p���l�b�g��s�@�����ߎx�X
���ʗa���@6291494
������ЃL���b�`�{�[���^�d�X�g�A�[��p����

�y�X�֐U�֌����z
�����L���F00140-5
�����ԍ��F665145
������Ё@�L���b�`�{�[���^�d�X�g�A�[��p

�y���������ׁz
���i���@�@�F{ItemNameKj}
���i����@�F{ItemAmount}�~
�����@�@�@�F{DeliveryFee}�~
�萔���@�@�F{SettlementFee}�~
{OptionFee}

���v�@�@�@�F{UseAmount}�~

���̑����s���ȓ_�A�������̂����k���͓��Ђ܂ł��₢���킹���������B

�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
�@���ڍw���X�ܗl�ɂ��₢���킹���������B
�@�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}

�����x�����Ɋւ��邨�₢���킹�́F
  ���⍇����F03-6908-5100
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: ato-barai.sp@estore.co.jp
  �^�c��ЁF������Ђd�X�g�A�[�@�㕥������
  �Z���F��105-0003�@�����s�`�搼�V��1-10-2  �Z�F�������V���r���XF',1,'2015/08/31 22:42:31',9,'2015/12/01 12:21:27',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (33,1,'���Ǝғo�^�����i�T�[�r�X�J�n�j���[��','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer@ato-barai.com',null,null,null,'�y�㕥��.com�z �X�ܐR���ʉ߂̂��m�点','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{EnterpriseNameKj}�@�l

���̓x�͕��ЃT�[�r�X�A�y�㕥���h�b�g�R���z�ɂ��\�����������A
�܂��Ƃɂ��肪�Ƃ��������܂��B

�R���̌��ʁA�ʉ߂ƂȂ�܂����̂ŁA�㕥�����ϊǗ��V�X�e����
�����p���������̂ɕK�v��ID��񍐐\���グ�܂��B

�d�v�Ȃ��ē��ƂȂ�܂��̂ŁA�Ō�܂ł��ǂ݂��������B

�y�Ǘ��T�C�g�t�q�k�z

https://www.ato-barai.jp/smbcfs/member/

ID : {LoginId}


���p�X���[�h�͕ʓr���[���ɂĂ����肳���Ă��������܂��B
���T�C�g�h�c�͏�L�h�c�Ƃ͈قȂ�܂��̂ł����ӂ��������B
�T�C�g�h�c�̎Q�ƕ��@�͈ȉ��̒ʂ�ł��B

�y1�z�Ǘ��T�C�g�Ƀ��O�C��
�@�@���@���@���@��
�y2�z�u�o�^���Ǘ��v���N���b�N
�@�@���@���@���@��
�y3�z�u�T�C�g���v���N���b�N
�@�@���@���@���@��
�y4�z�u�T�C�g�h�c�v���ɕ\������܂��B

 ���}�j���A���̃_�E�����[�h�i�K�{�j
���L��URL���A�y�㕥���h�b�g�R���z�̉^�p�}�j���A�����_�E�����[�h
���Ă��g�p�������B
�T�[�r�X�J�n�ɕK�v�ȃ}�j���A���ƂȂ��Ă���܂��̂ŁA�K�����m�F
���������܂��悤���肢�\���グ�܂��B

  https://www.ato-barai.jp/doc/help/Manual_SMBC.pdf

���{���ɂ�Adobe PDF Reader ���K�v�ł��B�C���X�g�[������Ă��Ȃ�
���́A���L��URL��蓯�\�t�g�̃C���X�g�[�������肢�������܂��B

  http://www.adobe.com/jp/products/acrobat/readstep2.html

�Ǘ��V�X�e���̂����p���@�́A�_�E�����[�h���Ă����������}�j���A��
�����m�F���������B

�T�[�r�X�̊J�n�܂ŁA�X�ܗl�ɂ͈ȉ��̂悤�ȍ�Ƃ����Ă��������܂��B
�J�n�̂��A�������Y��Ȃ��悤�A���肢�\���グ�܂��B

�������@STEP 1�@�������o�^���e�̂��m�F

�Ǘ��T�C�g�Ƀ��O�C���A�X�܏����m�F�i�v�������̑��̏��j

�������@STEP 2�@��������^���͂̃T�C�g�f��

�}�j���A���ɂ��������āA�X�ܗl�T�C�g��ɓ����ϕ��@�p�̒�^���͂��f��
�i���菤����@�y�[�W�⌈�ϑI����ʂȂǁj

�T�C�g�f�ڗp��^���E�摜�񋟃y�[�W�F

http://www.ato-barai.com/for_shops/s.tokuteishou.html

�����̎��_�ŃT�[�r�X�J�n�ƂȂ�܂�

������җl�����㕥���h�b�g�R�����恕�̑��o�i�[�_�E�����[�h�y�[�W
http://www.ato-barai.com/download/

����җl��������́A���߂ē����ς������p�ɂȂ����җl�ɂƂ���
������Ղ��Ȃ�A���⍇�������点����ʂ����҂ł��܂��B
����ɔ̑��o�i�[�́A�㕥�����ς��o���邨�X�Ƃ��ăA�s�[���ł��邽�߁A
�̑��̌��ʂɂ��Ȃ���܂��̂ŁA������������Ă����p���������B

�������@STEP 3�@�������T�[�r�X�J�n�̓��Ђւ̂��ʒm

�T�[�r�X���J�n�����|���A���Ђ܂Ń��[���������͂��d�b�ɂĂ��A���������B
 mail: customer@ato-barai.com
 tel:  0120-667-690

�������@STEP 4�@���������Ђ����ω�ʂ��m�F

���ВS�������ω�ʂ��m�F�����Ă��������A��肪�Ȃ���΂��̂܂܉^�c�A
��肪����ΏC���̂��肢�������Ă����������Ƃ��������܂��B

  �������u����v�͂����܂�

������җl�ւ̐������̂��ē��p���̃_�E�����[�h�i�C�Ӂj
���L�̂t�q�k��萿�����̂��ē��p�����_�E�����[�h���āA���i�ɓ���
���Ă��������B
�i���ē��p���̓����͓X�ܗl�̂����f�ɂ��C�ӂōs���Ă���������
�@����܂����A���߂ē����ς������p�Ȃ����җl�ɂƂ��Ă͕�����Ղ�
�@�Ȃ�A���⍇�������邱�Ƃɂ��q����܂��̂ŁA�������Ă�����������
�@�𐄏����Ă���܂��B�j

https://www.atobarai.jp/doc/download/doukonnyou.xls


�T�[�r�X�J�n�ɓ������āA�܂��A�^�c�Ɋւ��邨�₢���킹���́A
���[�������̂��A����ɂ��C�y�ɂ��⍇���������B

�������������������������y���ӎ����z������������������������

�P�j�ȉ��ɊY�����邲�����́A�ۏؑΏۊO�ƂȂ��Ă��܂��܂��̂�
�@�@�����ӂ��������B

���ۏ؊O�Ƃ́A�������̕ۏ؂��t�����A����җl����̓�����
�@�Ȃ�����͓X�ܗl�֓��������Ă������������ł��܂���B
�@
�E���i�������ɁA���[���ւ��`�O�X�֓��́A��̈󖔂�
�@����̃T�C���������z�����@�ɂď��i�𔭑����ꂽ������
�EWeb��ɂĂ��ו��̒ǐՂ��ł��Ȃ��z�����@���g��ꂽ������
�E�`�[�o�^���ɔz����Ђ�`�[�ԍ�����������œo�^���ꂽ������
�E�z�B�󋵂��L�����Z���E�����߂蓙�ɂ��z�B�����̊m�F��
�@�Ƃ�Ȃ�������
�E���ۂɔ������ꂽ�z�����@�Ɋւ�炸�A�`�[�o�^���̔z�����@��
�@�y���[���ցz��I�����ēo�^���ꂽ������
�E�����������邲����

�Q�j�z���`�[�ԍ������o�^�����������A�������́A���c�Ɠ���
�@�@�������җl�ɑ΂��āA������������������܂��B
�����i�����O�ɔz���`�[�ԍ������o�^���������܂��ƁA�����������i
�@����ɓ͂��Ă��܂��\���������Ȃ�܂��̂ŁA���i�������
�@�z���`�[�ԍ��̂��o�^�����肢�������܂��B

�R�j�����܂łɕ��Б��ŏ��i�̒��׊m�F���Ƃꂽ����������
�@�@���Y�������̗��֑ΏۂƂȂ�܂��B
���`�[�ԍ��o�^����A�z�B�������ł͂Ȃ��A���Б��Œ��׊m�F��
�@�Ƃꂽ�����x�[�X�ƂȂ�܂��̂ł����ӂ��������B

�S�j�V���ɃE�F�u�T�C�g�܂��̓J�^���O���Ō㕥���h�b�g�R���̃T�[�r�X��
    �����p���������ꍇ�A�������͐V���ȏ��i��̔�����ꍇ�A
    �V���ȃT�[�r�X�����񋟂����ꍇ�͎��O�ɂ��A�����������܂��悤
    ���肢�\�������܂��B 
�����R���̂��̂́A�㕥���h�b�g�R���̃T�[�r�X�͂����p���������܂���B

������������������������������������������������������������


����Ƃ����i�����t�������̒��A�X�������肢�\���グ�܂��B

������ЃL���b�`�{�[���@�㕥���h�b�g�R�����ƕ��@�X�^�b�t�ꓯ

--------------------------------------------------------------

�y�㕥���h�b�g�R���z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`

  ���⍇����F0120-667-690
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com

  �^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F

--------------------------------------------------------------',2,'2015/08/31 22:42:31',9,'2022/07/03 15:54:24',63,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (34,2,'�����o�^�i�^�M�J�n�j���[��','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer@ato-barai.com',null,null,null,'�y�㕥��.com�z�^�M�J�n�̂��m�点�i{OrderCount}���j','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{EnterpriseNameKj} �l

�����y�㕥���h�b�g�R���z�������p���������A�܂��Ƃɂ��肪�Ƃ��������܂��B
�ȉ��̂��������󂯕t���������܂����B
������^�M�ɓ���܂��̂ŁA���i���܂���������Ȃ��悤�����Ӊ������B

��t���������F{OrderCount}��

�������Җ��i���������z�j
--------------------------------------------------------------
{OrderSummary}
--------------------------------------------------------------
��L������̗^�M������ɁA�^�M�������[���𑗐M�������܂��B


��18:00�ȍ~�̗^�M�́A�ʏ헂��11:00�܂ł̉񓚂ƂȂ�܂��̂ł����Ӊ������B
�������ɂ��^�M�ɂ����鎞�Ԃ��قȂ�ꍇ���������܂��B���̏ꍇ�A�^�M���ʂ�
�o�����̂��玩���ŗ^�M�������[�������M����܂��̂ŁA���炩���߂������������B

�����������������������@�L�����Z�������������ꍇ�@����������������������

���o�^���ꂽ�����̃L�����Z�����������ꍇ�́A���萔�ł����u���������v����
���������������A�Y���̂�������N���b�N���ăL�����Z���������s���ĉ������B

������������������������������������������������������������������������

�y�Ǘ���ʂt�q�k�z
https://www.ato-barai.jp/smbcfs/member/

���s���ȓ_�Ȃǂ������܂�����A���C�y�ɂ��₢���킹�������B

--------------------------------------------------------------

�y�㕥���h�b�g�R���z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`

  ���⍇����F0120-667-690
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com

  �^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F

--------------------------------------------------------------',2,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (35,3,'�^�M�������[��','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�y�㕥��.com�z�^�M�����̂��m�点�F�v{CreditCount}���i����NG{NgCount}���j','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{EnterpriseNameKj}�@�l

�����y�㕥���h�b�g�R���z�������p���������A�܂��Ƃɂ��肪�Ƃ��������܂��B

�^�M�����F{CreditCount} ��

�̗^�M���ʂ��o�܂����̂ł��񍐂������܂��B

�y�Ǘ���ʂt�q�k�z
https://www.ato-barai.jp/smbcfs/member/

���^�M��NG�̂������ł����Ă��ANG���R�ɂ���ẮA���ۏ؂ɂāuOK�v�ɕύX�ł���ꍇ���������܂��B
���ۏ؂Ō㕥���T�[�r�X����]�̕��͈ȉ��ɋL�ڂ́yNG���R�ɂ�鏈�����@�ɂ��āz���Q�l�ɂ��Ă��������B
�i���ۏ؂ł��uOK�v�ɕύX�ł��Ȃ��ꍇ���������܂��̂ŁA���Ђ����
�ԐM���[�������m�F���������Ă���A���i�����Ȃǂ��s���Ă��������B�j

{Orders}

�yOK�Č��̏����z
�^�M���ʉ߂���������Ɋւ��܂��ẮA

1.���i�̔���
2.�z���`�[�ԍ��o�^

�ɂ��i�݉������B

�yNG���R�ɂ�鏈�����@�ɂ��āz
�� NG���R���u�����x�����v�u���z�ۗ��v�u���ۏؕύX�\�v�̏ꍇ
���ۏ؂ł̌㕥���T�[�r�X�� �؂�ւ��Ē������Ƃ��\�ł��B
���ۏ؂ɕύX����ꍇ�́A���̃��[�����{OutOfAmendsDays}���ȓ��Ɍ㕥�����ϊǗ��V�X�e����
���O�C����ɑ�������{���Ă��������B

�� ��L�ȊO��NG���R�̏ꍇ
���̑���NG���R�̂�����Ɋւ��܂��ẮA�����߂ɂ��w���җl�ɑ��̌��ϕ��@�̂��I����
���������Ȃǂ̂��Ή������肢�������܂��B

--------------------------------------------------------------

�y�㕥���h�b�g�R���z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`

  ���⍇����F0120-667-690
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com

  �^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h6-14-1�@�V�h�O���[���^���[14�K 

--------------------------------------------------------------',2,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (36,4,'���������s���[���iPC�j','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�y�㕥��.com�z���������s�ē��@�i�n�K�L�œ͂��܂��j','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','��������������������������������������������������������������������
�����肢�F���₢���킹�����ہA���L���[�����ʂ��c�����܂܂��ԐM����������
�������������������������������������������������������������������� 

{CustomerNameKj}�l

����� {SiteNameKj}�l�ł̂���������
�y�㕥���h�b�g�R���z�������p���������܂��āA
�܂��Ƃɂ��肪�Ƃ��������܂��B

���L�̃V���b�s���O�̂���������{�����s�������܂��̂ŁA������������A
�������ɋL�ڂ���Ă��邨�x���������܂łɂ��x�������������܂��悤�A
���肢�\���グ�܂��B

{OrderPageAccessUrl}

�y���������e�z
���x���ҁF{CustomerNameKj}�@�l
���w���X�ܖ��F{SiteNameKj}�@
���w�����F{OrderDate}
���x�����z�F{UseAmount}
���w�����i���ׁF���i���^���^�w���i�ڌv
{OrderItems}
���ώ萔��                              \{SettlementFee}
����                                    \{DeliveryFee}

���X�����̂Ȃǂɂ��A���������͂��Ȃ����Ƃ��������܂��B
�@��T�Ԃقǂ��҂����������Ă����������͂��Ȃ��ꍇ�ɂ́A��ς��萔�ł����A
�@���L�A����ւ���񂭂������܂��悤�A���肢�\���グ�܂��B

�����i�ɂ��܂��Ă��A���[���ւȂǂ̔z�����@�̏ꍇ�ɂ́A�z�����̂Ȃǂɂ��
�@�͂��Ȃ��ꍇ���������܂��B
�@������A���i���͂��Ă��Ȃ��ꍇ�ɂ͑�ς��萔�ł͂������܂����A���������ꂽ
�@�X�ܗl�܂Œ��ڂ��⍇�����������܂��B

�����������тɖ{���[�����A���i���������������Ă��܂����Ƃ��������܂����A
�������e�͉������܂��悤���肢�\���グ�܂��B
�܂��A���������ɖ��ׂ��܂܂�Ă���܂��̂ł��m�F���������܂��B

���L�����Z���i���\���j����Ă���ꍇ�ł��A�s���Ⴂ�ɂē����[����
�@�z�M����Ă��܂��ꍇ���������܂��B���̍ۂ͑�ς��萔�ł����A���L
�@�w���X�ܗl�ɒ��ڂ��⍇�����������B

���������̑����s���ȓ_�͉��L�t�q�k�����m�F���������B������

�@�@�@  http://www.ato-barai.com/guidance/faq.html

�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
���ڍw���X�ܗl�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}

�����x�����Ɋւ��邨�₢���킹�́F
������ЃL���b�`�{�[���@�㕥���h�b�g�R�����ƕ�
TEL:03-5332-3490(�����y��9:00�`18:00)
Mail: customer@ato-barai.com

--------------------------------------------------------------

�y�㕥���h�b�g�R���z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`

  ���⍇����F03-5332-3490
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com

  �^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F

--------------------------------------------------------------',2,'2015/08/31 22:42:31',9,'2017/12/26 15:13:50',59,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (37,5,'���������s���[���iCEL�j','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�y�㕥��.com�z���������s�ē��@�i�n�K�L�œ͂��܂��j','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','��������������������������������������������������������������������
�����肢�F���₢���킹�����ہA���L���[�����ʂ��c�����܂܂��ԐM����������
�������������������������������������������������������������������� 

{CustomerNameKj}�l

����� {SiteNameKj}�l�ł̂���������
�y�㕥���h�b�g�R���z�������p���������܂��āA
�܂��Ƃɂ��肪�Ƃ��������܂��B

���L�̃V���b�s���O�̂���������{�����s�������܂��̂ŁA������������A
�������ɋL�ڂ���Ă��邨�x���������܂łɂ��x�������������܂��悤�A
���肢�\���グ�܂��B

�y���������e�z
���x���ҁF{CustomerNameKj}�@�l
���w���X�ܖ��F{SiteNameKj}�@
���w�����F{OrderDate}
���x�����z�F{UseAmount}
���w�����i���ׁF���i���^���^�w���i�ڌv
{OrderItems}
���ώ萔��  \{SettlementFee}
���� \{DeliveryFee}

���X�����̂Ȃǂɂ��A���������͂��Ȃ����Ƃ��������܂��B
�@��T�Ԃقǂ��҂����������Ă����������͂��Ȃ��ꍇ�ɂ́A��ς��萔�ł����A
�@���L�A����ւ���񂭂������܂��悤�A���肢�\���グ�܂��B

�����i�ɂ��܂��Ă��A���[���ւȂǂ̔z�����@�̏ꍇ�ɂ́A�z�����̂Ȃǂɂ��
�@�͂��Ȃ��ꍇ���������܂��B
�@������A���i���͂��Ă��Ȃ��ꍇ�ɂ͑�ς��萔�ł͂������܂����A���������ꂽ
�@�X�ܗl�܂Œ��ڂ��⍇�����������܂��B

�����������тɖ{���[���A���i���������������Ă��܂����Ƃ��������܂����A
�������e�͉������܂��悤���肢�\���グ�܂��B
�܂��A���������ɖ��ׂ��܂܂�Ă���܂��̂ł��m�F���������܂��B

���L�����Z���i���\���j����Ă���ꍇ�ł��A�s���Ⴂ�ɂē����[����
�@�z�M����Ă��܂��ꍇ���������܂��B���̍ۂ͑�ς��萔�ł����A���L
�@�w���X�ܗl�ɒ��ڂ��⍇�����������B

���������̑����s���ȓ_�͉��L�t�q�k�����m�F���������B������

�@�@�@  http://www.ato-barai.com/guidance/faq.html


�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
���ڍw���X�ܗl�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@
�d�b�F{Phone}

�����x�����Ɋւ��邨�₢���킹�́F
������ЃL���b�`�{�[���@�㕥���h�b�g�R�����ƕ�
�Z���F��160-0023 �����s�V�h�搼�V�h7-8-2 �����r�� 4F
TEL:03-5332-3490(�����y��9:00�`18:00)
Mail: customer@ato-barai.com
URL: http://www.ato-barai.com�i�p�\�R����p�j

--------------------------------------------------------------

�y�㕥���h�b�g�R���z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`

  ���⍇����F03-5332-3490
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail:customer@ato-barai.com

  �^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F

--------------------------------------------------------------',2,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (38,6,'�����m�F���[���iPC�j','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�y�㕥���h�b�g�R���z�������m�F�̂���','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','��������������������������������������������������������������������
�����肢�F���₢���킹�����ہA���L���[�����ʂ��c�����܂܂��ԐM����������
�������������������������������������������������������������������� 
{CustomerNameKj}�@�l

���̓x�́A{SiteNameKj}�l�ł̂��������ɁA
�y�㕥���h�b�g�R���z�������p���������܂��āA
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ReceiptDate}��{CustomerNameKj}�l����̂�������
�m�F�������܂����̂ł��񍐐\���グ�܂��B

�ȉ����A���񂲓��������������������̓��e�ƂȂ�܂��B

�y�̎��ς݂��������e�z
���x���ҁF{CustomerNameKj}�@�l
���w���X�ܖ��F{SiteNameKj}�@
���w�����F{OrderDate}
���x�����z�F{UseAmount}
���w�����i���ׁF���i���^���^�w���i�ڌv
{OrderItems}
���ώ萔��                              \{SettlementFee}
����                                    \{DeliveryFee}

���w���X�ܖ��F{SiteNameKj}
���A����F{Phone}
�Z���F{Address}


�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹��
���ڍw���X�ܗl�ɂ��₢���킹�������B

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O
�i�t���l�[���j��{���ɓ���Ă��⍇�����������B

���������̑����s���ȓ_�͉��L�t�q�k�����m�F���������B������

�@�@�@ http://www.ato-barai.com/guidance/faq.html


�܂��̂����p��S���A���҂����Ă���܂��B

--------------------------------------------------------------

�y�㕥���h�b�g�R���z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`

  ���⍇����F03-5332-3490
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com

  �^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F

--------------------------------------------------------------',2,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (39,7,'�����m�F���[���iCEL�j','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�y�㕥���h�b�g�R���z�������m�F�̂���','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','��������������������������������������������������������������������
�����肢�F���₢���킹�����ہA���L���[�����ʂ��c�����܂܂��ԐM����������
�������������������������������������������������������������������� 

{CustomerNameKj}�@�l

���̓x�́A{SiteNameKj}�l�ł̂��������ɁA
�y�㕥���h�b�g�R���z�������p���������܂��āA
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ReceiptDate}��{CustomerNameKj}�l����̂�������
�m�F�������܂����̂ł��񍐐\���グ�܂��B

�ȉ����A���񂲓��������������������̓��e�ƂȂ�܂��B

�y�̎��ς݂��������e�z
���x���ҁF{CustomerNameKj}�@�l
���w���X�ܖ��F{SiteNameKj}�@
���w�����F{OrderDate}
���x�����z�F{UseAmount}
���w�����i���ׁF���i���^���^�w���i�ڌv
{OrderItems}
���ώ萔�� \{SettlementFee}
����       \{DeliveryFee}

���w���X�ܖ��F{SiteNameKj}
���A����F{Phone}
�Z���F{Address}


�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹��
���ڍw���X�ܗl�ɂ��₢���킹�������B

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O
�i�t���l�[���j��{���ɓ���Ă��⍇�����������B

���������̑����s���ȓ_�͉��L�t�q�k�����m�F���������B������

�@�@�@ http://www.ato-barai.com/guidance/faq.html


�܂��̂����p��S���A���҂����Ă���܂��B

--------------------------------------------------------------

�y�㕥���h�b�g�R���z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`

  ���⍇����F03-5332-3490
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com

  �^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F

--------------------------------------------------------------',2,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (40,8,'���֊������[��','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer@ato-barai.com',null,null,null,'�y�㕥��.com�z���֋����x�����̂���','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{EnterpriseNameKj} �l

�����y�㕥���h�b�g�R���z�������p���������܂��āA
�܂��Ƃɂ��肪�Ƃ��������܂��B

���֕��̂��x�����������������܂����̂ŁA
�񍐐\���グ�܂��B

�y�Ǘ���ʂt�q�k�z
https://www.ato-barai.jp/smbcfs/member/

���x���T�C�g�@�@�F�@{FixedPattern}
���֒����@�@�@�@�F�@{FixedDate}
�U�����s���@�@�@�F�@{ExecDate}
���x���z�@�@�@�@�F�@{DecisionPayment}�~
���ώ萔���@�@�@�F�@{SettlementFee}�~
�����萔���@�@�@�F�@{ClaimFee}�~
�󎆑㍇�v�@�@�@�F�@{StampFee}�~
�L�����Z���ԋ��@�F�@{CancelAmount}�~
���z�Œ��@�@�@�F�@{MonthlyFee}�~
���U���ݎ萔���@�F�@{TransferCommission}�~

���x���Ɋւ��܂��Ă��s���ȓ_�Ȃǂ������܂�����A
���L�A����܂ł��C�y�ɂ��₢���킹�������܂��B

����Ƃ����ЃT�[�r�X�y�㕥���h�b�g�R���z���A��낵��
���肢�\���グ�܂��B

--------------------------------------------------------------

�y�㕥���h�b�g�R���z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`

  ���⍇����F0120-667-690
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com

  �^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F

--------------------------------------------------------------',2,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (41,9,'�L�����Z���m�F���[��','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer@ato-barai.com',null,null,null,'�y�㕥��.com�z�L�����Z���m��̂���({OrderId})','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{EnterpriseNameKj}�@�l

�����y�㕥���h�b�g�R���z�������p���������܂��āA�܂��Ƃɂ��肪�Ƃ��������܂��B

�ȉ��̂������̃L�����Z��������܂����̂ŁA���m�F�������B
�܂��A�L�����Z���̃^�C�~���O�ɂ���āA���̌�̏���җl�ւ̑Ή����قȂ�܂�
�̂ł����Ӊ������B�i���ȉ��́y1�z�`�y4�z�����Q�Ɖ������B�j

�y�Ǘ���ʂt�q�k�z
https://www.atobarai.jp/member/

�y�L�����Z���m����z
�L�����Z���敪�F{CancelPhase}
������ID�F{OrderId}
�����掁���F{CustomerNameKj}�@�l
���������z�F{UseAmount}
���������F{OrderDate}

�y1�z�����ֈČ��̃L�����Z��
�ԋ����͔������܂���B�����������łɔ�������Ă���ꍇ�́A���q�l
�ɐ������j���̂��肢�����A�������肢�\���グ�܂��B

�y2�z���֍ς݈Č��̃L�����Z��
���񗧑֎��ɁA���֍ς݂̋��z���A���E�ɂ��ԋ������Ă��������܂��B
�X�ܗl���ł̍�Ƃ͕K�v�������܂���B�܂��A���ώ萔���������������܂���B

�y3�z���֍ς݁E���q�l�������ς݈Č��̃L�����Z��
��قǓ��Ђ��X�ܗl�ɘA���������Ă��������܂��̂ŁA���̌�ɂ��q�l�ցA
���i�����X�ܗl��育�ԋ������������ƂɂȂ�܂��B
���ώ萔���͔����������܂���̂ŁA���񗧑֎��Ɏ萔����ԋ��������܂��B

�y4�z�����ւ��E���q�l�����ς݈Č��̃L�����Z��
��قǓ��Ђ��X�ܗl�ɘA���������Ă��������܂��̂ŁA���̌�ɂ��q�l�ցA
���i�����X�ܗl��育�ԋ������������ƂɂȂ�܂��B
�܂��A���q�l����̂������������񗧑֎��ɓ��Ђ��X�ܗl�֕ԋ������Ă���
�����܂��B���̏ꍇ�����ώ萔���͔����������܂���B

�����q�l���X�ܗl�̌����֒��ړ������ꂽ�ꍇ��A�X�ܗl������đ������
�@�������ꂽ�ꍇ���́A���q�l�ƓX�ܗl�Ԃł̂�������������Ă���ꍇ��
�@�L�����Z�������̍ۂɂ́A��L�y1�z�`�y4�z�̂�����̏ꍇ������̎萔��
�@�����񗧑֎��̒����z�ɂĒ��������Ă��������܂��B

���s���ȓ_�Ȃǂ������܂�����A���C�y�ɓ��Ђ܂ł��₢���킹�������B

--------------------------------------------------------------

�y�㕥���h�b�g�R���z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`

  ���⍇����F0120-667-690
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com

  �^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F

--------------------------------------------------------------',2,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (42,10,'�A�h���X�m�F���[��','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�y�㕥���h�b�g�R���z�̂��I�����肪�Ƃ��������܂�','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{CustomerNameKj}�l

���̓x�́A���x�����@�Ɂy�㕥���h�b�g�R���z�����I�����������A
�܂��Ƃɂ��肪�Ƃ��������܂��B

�������܁A���L�̂������ɂ����܂��Č㕥��.com�������p���������邩�A
�R�����������Ă���܂��B

�y���������e�z
���x���ҁF{CustomerNameKj}�@�l
���w���X�ܖ��F{SiteNameKj}�@
���w�����F{OrderDate}
���x�����z�F{UseAmount}
���w�����i���ׁF���i���^���^�w���i�ڌv
{OrderItems}
���ώ萔��  \{SettlementFee}
����        \{DeliveryFee}


���ʂɂ��܂��ẮA���������������܂����X�ܗl���A
��قǂ��A��������܂��̂ŁA�������X���҂��������܂��B

���s���ȓ_�Ȃǂ������܂�����A���C�y�ɂ��₢���킹�������B

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�@�{���ɓ���Ă��⍇�����������B

--------------------------------------------------------------

�y�㕥���h�b�g�R���z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`

  ���⍇����F03-5332-3490
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com

  �^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F

--------------------------------------------------------------',2,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (43,11,'�����������x�����[���iPC�j','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�y�㕥���h�b�g�R���z�����������x���������ł�','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','��������������������������������������������������������������������
�����肢�F���₢���킹�����ہA���L���[�����ʂ��c�����܂܂��ԐM����������
�������������������������������������������������������������������� 

{CustomerNameKj}�l


���̓x�́A{SiteNameKj}�l�ł̂��������ɁA
�y�㕥���h�b�g�R���z�������p���������܂��āA�܂��Ƃɂ��肪�Ƃ��������܂��B
{IssueDate}�ɐ������������肢�����܂������A���茳�ɂ��͂��ł��傤���B

�����肵���������̂��x�����������߂Â��Ă܂���܂����̂ŁA���m�点�������܂��B

���y���E�j�Փ��͓����̊m�F�����Ȃ��ׁA���̊ԂɌ�葱�������������ꍇ�A
����Ⴂ�œ����[���������Ă��܂��܂��B
���̏ꍇ�́A�܂��Ƃɐ\���󂲂����܂��񂪁A�����[�����폜���Ă��������܂��悤
���肢�\���グ�܂��B
�i�X�֋ǂŌ�葱�������������ꍇ�A�m�F�ɍő�4�c�Ɠ�������ꍇ���������܂��̂ŁA
�O����O�X���Ɍ�葱�����������Ă���܂��Ă��A�����悤�ɓ���Ⴂ�œ����[����
�͂��Ă��܂��ꍇ���������܂��B�j

���x���������F{LimitDate}
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���������z�F{UseAmount}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
�������F{IssueDate}
 
�܂����x�������������Ă��Ȃ��ꍇ�́A���Ђ�肨���肢�����܂�����������
���m�F�̂����A��L�������܂łɂ��x�������������܂��悤�A���肢�\���グ�܂��B

���������߂��Ă��܂��܂��ƁA�Đ����萔�������Z����܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��A�������̊m�F�͎��܂��B
�����ւ��U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z
{Bk_BankName}�@{Bk_BranchName}
���ʌ����@{Bk_AccountNumber}
{Bk_AccountHolderKn}


�y�X�֐U�֌����z
�����L���F00120�]7
�����ԍ��F670031
�J�j�L���b�`�{�[��

���X�֋ǁ^��s���炨�U���݂��������ꍇ�A�U���萔�������q�l�����S�ƂȂ�܂��B

���X�֋ǂ̌����������̏ꍇ�́A�X�֋ǂ̂`�s�l�𗘗p���Č������炲����������
����΁A�X�֐U���萔���͂�����܂���B(�X�܌��ώ萔���Ƃ͕ʂł��B)

�����i�ɂ��܂��Ă��A���[���ւȂǂ̔z�����@�̏ꍇ�ɂ́A�z�����̂Ȃǂɂ��
�͂��Ȃ��ꍇ���������܂��B
������A���i���͂��Ă��Ȃ��ꍇ�ɂ͑�ς��萔�ł͂������܂����A���������ꂽ
�X�ܗl�܂Œ��ڂ��⍇�����������܂��B

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�@�{���ɓ���Ă��⍇�����������B

���L�����Z���i���\���j����Ă���ꍇ�ł��A�s���Ⴂ�ɂē����[����
�@�z�M����Ă��܂��ꍇ���������܂��B���̍ۂ͑�ς��萔�ł����A�������ꂽ
�@�X�ܗl�ɒ��ڂ��⍇�����������B

�������ꐿ���������茳�ɓ͂��Ă��Ȃ��ꍇ��A���x���Ɋւ��܂��āA���s���ȓ_��
�@�������܂�����A���L�t�q�k�����m�F���������B

�@http://www.ato-barai.com/guidance/faq.html

����Ƃ����ЃT�[�r�X�y�㕥���h�b�g�R���z����낵�����肢�\���グ�܂��B

--------------------------------------------------------------

�y�㕥���h�b�g�R���z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`

  ���⍇����F03-5332-3490
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com
  �^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F

--------------------------------------------------------------',2,'2015/08/31 22:42:31',9,'2015/12/01 14:00:41',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (44,12,'�����������x�����[���iCEL�j','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�y�㕥���h�b�g�R���z�����������x���������ł�','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','��������������������������������������������������������������������
�����肢�F���₢���킹�����ہA���L���[�����ʂ��c�����܂܂��ԐM����������
�������������������������������������������������������������������� 

{CustomerNameKj}�l


���̓x�́A{SiteNameKj}�l�ł̂��������ɁA
�y�㕥���h�b�g�R���z�������p���������܂��āA�܂��Ƃɂ��肪�Ƃ��������܂��B
{IssueDate}�ɐ������������肢�����܂������A���茳�ɂ��͂��ł��傤���B

�����肵���������̂��x�����������߂Â��Ă܂���܂����̂ŁA���m�点�������܂��B

���y���E�j�Փ��͓����̊m�F�����Ȃ��ׁA���̊ԂɌ�葱�������������ꍇ�A
����Ⴂ�œ����[���������Ă��܂��܂��B
���̏ꍇ�́A�܂��Ƃɐ\���󂲂����܂��񂪁A�����[�����폜���Ă��������܂��悤
���肢�\���グ�܂��B
�i�X�֋ǂŌ�葱�������������ꍇ�A�m�F�ɍő�4�c�Ɠ�������ꍇ���������܂��̂ŁA
�O����O�X���Ɍ�葱�����������Ă���܂��Ă��A�����悤�ɓ���Ⴂ�œ����[����
�͂��Ă��܂��ꍇ���������܂��B�j

���x���������F{LimitDate}
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���������z�F{UseAmount}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
�������F{IssueDate}
 
�܂����x�������������Ă��Ȃ��ꍇ�́A���Ђ�肨���肢�����܂�����������
���m�F�̂����A��L�������܂łɂ��x�������������܂��悤�A���肢�\���グ�܂��B

���������߂��Ă��܂��܂��ƁA�Đ����萔�������Z����܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��A�������̊m�F�͎��܂��B
�����ւ��U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B


�y��s�U�������z
{Bk_BankName}�@{Bk_BranchName}
���ʌ����@{Bk_AccountNumber}
{Bk_AccountHolderKn}


�y�X�֐U�֌����z
�����L���F00120�]7
�����ԍ��F670031
�J�j�L���b�`�{�[��

���X�֋ǁ^��s���炨�U���݂��������ꍇ�A�U���萔�������q�l�����S�ƂȂ�܂��B

���X�֋ǂ̌����������̏ꍇ�́A�X�֋ǂ̂`�s�l�𗘗p���Č������炲����������
����΁A�X�֐U���萔���͂�����܂���B(�X�܌��ώ萔���Ƃ͕ʂł��B)

�����i�ɂ��܂��Ă��A���[���ւȂǂ̔z�����@�̏ꍇ�ɂ́A�z�����̂Ȃǂɂ��
�͂��Ȃ��ꍇ���������܂��B
������A���i���͂��Ă��Ȃ��ꍇ�ɂ͑�ς��萔�ł͂������܂����A���������ꂽ
�X�ܗl�܂Œ��ڂ��⍇�����������܂��B

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�@�{���ɓ���Ă��⍇�����������B

���L�����Z���i���\���j����Ă���ꍇ�ł��A�s���Ⴂ�ɂē����[����
�@�z�M����Ă��܂��ꍇ���������܂��B���̍ۂ͑�ς��萔�ł����A�������ꂽ
�@�X�ܗl�ɒ��ڂ��⍇�����������B

�������ꐿ���������茳�ɓ͂��Ă��Ȃ��ꍇ��A���x���Ɋւ��܂��āA���s���ȓ_��
�@�������܂�����A���L�t�q�k�����m�F���������B

�@http://www.ato-barai.com/guidance/faq.html

����Ƃ����ЃT�[�r�X�y�㕥���h�b�g�R���z����낵�����肢�\���グ�܂��B

--------------------------------------------------------------

�y�㕥���h�b�g�R���z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`

  ���⍇����F03-5332-3490
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com

  �^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F

--------------------------------------------------------------',2,'2015/08/31 22:42:31',9,'2015/12/01 14:01:01',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (45,13,'���x�������m�F���[���iPC�j','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�㕥��.com�F{OrderDate}�@{SiteNameKj}�ł̂��������̌�','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','��������������������������������������������������������������������
�����肢�F���₢���킹�����ہA���L���[�����ʂ��c�����܂܂��ԐM����������
�������������������������������������������������������������������� 

{CustomerNameKj}�l


{OrderDate}��{SiteNameKj}�l�ł̂��������ɁA
�㕥���h�b�g�R���������p�����������肪�Ƃ��������܂��B

{IssueDate}�ɐ������������肢�����܂������A�{�����݂������̊m�F���ł���
����܂���B

���y���E�j�Փ��͓����̊m�F�����Ȃ��ׁA���̊ԂɌ�葱�������������ꍇ�A
����Ⴂ�œ����[���������Ă��܂��܂��B
���̏ꍇ�́A�܂��Ƃɐ\���󂲂����܂��񂪁A�����[�����폜���Ă��������܂��悤
���肢�\���グ�܂��B
�i�X�֋ǂŌ�葱�������������ꍇ�A�m�F�ɍő�4�c�Ɠ�������ꍇ���������܂��̂ŁA
�O����O�X���Ɍ�葱�����������Ă���܂��Ă��A�����悤�ɓ���Ⴂ�œ����[����
�͂��Ă��܂��ꍇ���������܂��B�j

�܂����x�������������Ă��Ȃ��ꍇ�́A�R���r�j�G���X�X�g�A�A�X�֋ǂ܂��͋�s
��肨�x�������������܂��B

���Đ����萔�������Z����܂��̂ŁA�����߂ɂ��A���܂��́A
���������������܂��悤�A���肢�\���グ�܂��B

�y���������ׁz
���i���i��i�ڂ̂ݕ\���j�F{OneOrderItem}�@��
���v�i�����E�萔���܂ށj�F{UseAmount}�~
�Đ����萔���F{ReClaimFee}�~
�x�����Q���F{DamageInterest}�~(�O�񐿋������s�����_)
���̑��F{InstPlanAmount}�~
���v�F{TotalAmount2}�~

�����L�����֒��ڂ��U���݂��������܂��Ă��A�������̊m�F�͎��܂��B
�����ւ��U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

���X�֋ǁ^��s���炨�U���݂��������ꍇ�A�U���萔���͂��q�l�����S�ƂȂ�܂��B

���X�֋ǂ̌����������̏ꍇ�́A�X�֋ǂ̂`�s�l�𗘗p���Č������炲����������
����΁A�X�֐U���萔���͂�����܂���B(�X�܌��ώ萔���Ƃ͕ʂł��B)

�y��s�U�������z
�O��Z�F��s�@
�V�h�ʎx�X�@
���ʌ����@8047001
�J�j�L���b�`�{�[��

�y�X�֐U�֌����z
�����L���F00120�]7
�����ԍ��F670031
�J�j�L���b�`�{�[��


�����i�ɂ��܂��Ă��A���[���ւȂǂ̔z�����@�̏ꍇ�ɂ́A�z�����̂Ȃǂɂ��
�͂��Ȃ��ꍇ���������܂��B
������A���i���͂��Ă��Ȃ��ꍇ�ɂ͑�ς��萔�ł͂������܂����A���������ꂽ
�X�ܗl�܂Œ��ڂ��⍇�����������܂��B

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�@�{���ɓ���Ă��⍇�����������B

���L�����Z���i���\���j����Ă���ꍇ�ł��A�s���Ⴂ�ɂē����[����
�@�z�M����Ă��܂��ꍇ���������܂��B���̍ۂ͑�ς��萔�ł����A�������ꂽ
�@�X�ܗl�ɒ��ڂ��⍇�����������B

�������ꐿ���������茳�ɓ͂��Ă��Ȃ��ꍇ��A���x���Ɋւ��܂��āA���s���ȓ_��
  �������܂�����A���L�t�q�k�����m�F���������B

  http://www.ato-barai.com/guidance/faq.html

--------------------------------------------------------------

�y�㕥���h�b�g�R���z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`

  ���⍇����F03-5332-3490
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com

  �^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F

--------------------------------------------------------------',2,'2015/08/31 22:42:31',9,'2015/12/01 13:58:09',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (46,14,'���x�������m�F���[���iCEL�j','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�㕥��.com�F{OrderDate}�@{SiteNameKj}�ł̂��������̌�','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','��������������������������������������������������������������������
�����肢�F���₢���킹�����ہA���L���[�����ʂ��c�����܂܂��ԐM����������
�������������������������������������������������������������������� 

{CustomerNameKj}�l



{OrderDate}��{SiteNameKj}�l�ł̂��������ɁA
�㕥���h�b�g�R���������p�����������肪�Ƃ��������܂��B

{IssueDate}�ɐ������������肢�����܂������A�{�����݂������̊m�F���ł���
����܂���B

���y���E�j�Փ��͓����̊m�F�����Ȃ��ׁA���̊ԂɌ�葱�������������ꍇ�A
����Ⴂ�œ����[���������Ă��܂��܂��B
���̏ꍇ�́A�܂��Ƃɐ\���󂲂����܂��񂪁A�����[�����폜���Ă��������܂��悤
���肢�\���グ�܂��B
�i�X�֋ǂŌ�葱�������������ꍇ�A�m�F�ɍő�4�c�Ɠ�������ꍇ���������܂��̂ŁA
�O����O�X���Ɍ�葱�����������Ă���܂��Ă��A�����悤�ɓ���Ⴂ�œ����[����
�͂��Ă��܂��ꍇ���������܂��B�j

�܂����x�������������Ă��Ȃ��ꍇ�́A�R���r�j�G���X�X�g�A�A�X�֋ǂ܂��͋�s
��肨�x�������������܂��B

���Đ����萔�������Z����܂��̂ŁA�����߂ɂ��A���܂��́A
���������������܂��悤�A���肢�\���グ�܂��B

�y���������ׁz
���i���i��i�ڂ̂ݕ\���j�F{OneOrderItem}�@��
���v�i�����E�萔���܂ށj�F{UseAmount}�~
�Đ����萔���F{ReClaimFee}�~
�x�����Q���F{DamageInterest}�~(�O�񐿋������s�����_)
���̑��F{InstPlanAmount}�~
���v�F{TotalAmount2}�~

�����L�����֒��ڂ��U���݂��������܂��Ă��A�������̊m�F�͎��܂��B
�����ւ��U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

���X�֋ǁ^��s���炨�U���݂��������ꍇ�A�U���萔���͂��q�l�����S�ƂȂ�܂��B

���X�֋ǂ̌����������̏ꍇ�́A�X�֋ǂ̂`�s�l�𗘗p���Č������炲����������
����΁A�X�֐U���萔���͂�����܂���B(�X�܌��ώ萔���Ƃ͕ʂł��B)

�y��s�U�������z
�O��Z�F��s�@
�V�h�ʎx�X�@
���ʌ����@8047001
�J�j�L���b�`�{�[��

�y�X�֐U�֌����z
�����L���F00120�]7
�����ԍ��F670031
�J�j�L���b�`�{�[��


�����i�ɂ��܂��Ă��A���[���ւȂǂ̔z�����@�̏ꍇ�ɂ́A�z�����̂Ȃǂɂ��
�͂��Ȃ��ꍇ���������܂��B
������A���i���͂��Ă��Ȃ��ꍇ�ɂ͑�ς��萔�ł͂������܂����A���������ꂽ
�X�ܗl�܂Œ��ڂ��⍇�����������܂��B

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�@�{���ɓ���Ă��⍇�����������B

���L�����Z���i���\���j����Ă���ꍇ�ł��A�s���Ⴂ�ɂē����[����
�@�z�M����Ă��܂��ꍇ���������܂��B���̍ۂ͑�ς��萔�ł����A�������ꂽ
�@�X�ܗl�ɒ��ڂ��⍇�����������B

�������ꐿ���������茳�ɓ͂��Ă��Ȃ��ꍇ��A���x���Ɋւ��܂��āA���s���ȓ_��
  �������܂�����A���L�t�q�k�����m�F���������B

  http://www.ato-barai.com/guidance/faq.html

--------------------------------------------------------------

�y�㕥���h�b�g�R���z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`

  ���⍇����F03-5332-3490
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com

  �^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F

--------------------------------------------------------------',2,'2015/08/31 22:42:31',9,'2015/12/01 13:58:21',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (47,15,'�`�[�ԍ��m�F�̂��肢','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer@ato-barai.com',null,null,null,'�y�㕥��.com�z�`�[�ԍ��̂��m�F�����肢���܂�','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{EnterpriseNameKj}
{CpNameKj} �l

�����b�ɂȂ��Ă���܂��B�y�㕥���h�b�g�R���z�J�X�^�}�[�Z���^�[�ł��B

{ReceiptOrderDate}�ɂ������o�^���������܂����A���L���q�l�̒��׊m�F��
���Ȃ��ׁA���󗧑ւ������Ă����������Ƃ��ł��Ă���܂���B

���o�^�����܂����A�z���`�[�ԍ��ɓ��̓~�X�����邩�A
���i�����q�l�ɓ͂��Ă��Ȃ��\�����������܂��B

���i�̔z����ЁA�z���`�[�ԍ��A���тɔz���󋵂�
�l���̌��ˍ������������܂��̂�
���萔�ł�����x�X�ܗl���ł��m�F���������A
�X�ܗl�Ǘ��T�C�g�ォ��C�����Ă������������v���܂��B
���ҏW���@�͗��������������̂��q�l���i�荞��ł��������A
�w�o�^���e�̏C���x����C���������Ȃ��ĉ������B

�����ID �F{OrderId}
�������җl�� �F{CustomerNameKj} �l
�`�[�ԍ��o�^�� �F{Deli_JournalIncDate}
�o�^�`�[�ԍ� �F{Deli_JournalNumber}

���A�����Ԃɓn�育�ύX�������������A�z����Ђ̒ǐՃT�[�r�X�ɂ�
���ׂ̊m�F�����Ȃ��Ȃ��Ă��܂����ꍇ�A���ۏ؈����ƂȂ�܂��̂�
�����ӂ��������B

���s���_�Ȃǂ������܂�����A���Ѓt���[�_�C�����i0120-667-690�j�܂�
���A������������΂Ǝv���܂��B
������낵�����肢�������܂��B


--------------------------------------------------------------

�y�㕥���h�b�g�R���z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`

  ���⍇����F0120-667-690
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com

  �^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F

--------------------------------------------------------------',2,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (48,16,'�߂萿���Z���m�F���[��','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer@ato-barai.com',null,null,null,'�㕥��.com�F�y�d�v�z���Z���m�F�̘A���ł��B','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','��������������������������������������������������������������������
�ԐM�̍ۂ̂��肢�F���q�l�ւ̐��m�Ȃ��ē��񋟂̂��߁A���ԐM��������
�ۂ͈��p�ԐM�܂��́A�����ɂ������җl�̎����̂��L�������肢�v���܂��B
�������������������������������������������������������������������� 

{CustomerNameKj}�l

{ReceiptOrderDate}��{SiteNameKj}�ŁA
�㕥���h�b�g�R�����ς�I�����Ă����������肪�Ƃ��������܂��B
{ClaimDate}�ɂ����肢�����܂��������������Ђɖ߂��Ă��Ă���܂��̂ŁA
���Z���̊m�F�������Ă��������������A�������Ă��������܂����B

�i���q�l�Z���j�@{UnitingAddress}

��L�Z���ɕs��������܂�����A�ēx�������𔭍s�����Ă��������܂��̂�
���A���̒��A��낵�����肢�v���܂��B

�Z���ɕs�����Ȃ��ꍇ�ł��A�\�D����������Ă����ꍇ�ȂǂŁA�X�֕����͂��Ȃ��P�[�X��
����܂��̂ŁA�������������B

�܂��A��s�A�X�֋ǂ���̂��������\�ł��̂�
�����ԍ��������肳���Ă��������܂��B

�y��s�U�������z
�O��Z�F��s�@�V�h�ʎx�X�@�J�j�L���b�`�{�[��
���ʌ����@8047001

�y�X�֐U�֌����z
�����ԍ��F00120-7
�����ԍ��F670031
�J�j�L���b�`�{�[��


�y���������ׁz
���i���@�@�F{ItemNameKj}
���i����@�F{ItemAmount}�~
�����@�@�@�F{DeliveryFee}�~
�萔���@�@�F{SettlementFee}�~
{OptionFee}
���v�@�@�@�F{UseAmount}�~

���̑����s���ȓ_�A�������̂����k���͓��Ђ܂ł��₢���킹���������B

--------------------------------------------------------------

�y�㕥���h�b�g�R���z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`

  ���⍇����F03-5332-3490
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com

  �^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F

--------------------------------------------------------------',2,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (49,17,'���������s���[���i�����c�[�������FPC�j','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�y�㕥��.com�z���������s�ē�','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','��������������������������������������������������������������������
�����肢�F���₢���킹�����ہA���L���[�����ʂ��c�����܂܂��ԐM����������
�������������������������������������������������������������������� 

 {CustomerNameKj}�l

����� {SiteNameKj}�l�ł̂���������
�y�㕥���h�b�g�R���z�������p���������܂��āA
�܂��Ƃɂ��肪�Ƃ��������܂��B

���L�̃V���b�s���O�̐�������{�����s�������܂����B
���i�ɓ�������Ă��鐿�����ɋL�ڂ̂��x���������܂ł�
���x�������������܂��悤�A���肢�\���グ�܂��B

�������[���́A�u���i�����̂��m�点���[���v�ł͂������܂���B
�@������������������_�Ń��[���������肵�Ă���܂��֌W�ŁA
�@�������̔��s���ƁA���i�̔��������قȂ�ꍇ���������܂��̂ŁA
�@�\�߂��������������܂��B

�y���������e�z
���x���ҁF{CustomerNameKj}�@�l
���w���X�ܖ��F{SiteNameKj}�@
���w�����F{OrderDate}
���x�����z�F{UseAmount}
���w�����i���ׁF���i���^���^�w���i�ڌv
{OrderItems}
���ώ萔��                              \{SettlementFee}
����                                    \{DeliveryFee}

�����i�Ƌ��ɐ������������Ă��Ȃ��ꍇ�ɂ́A��ς��萔�ł����A
�@���L�A����ւ���񂭂������܂��悤�A���肢�\���グ�܂��B

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�@�{���ɓ���Ă��⍇�����������B

���L�����Z���i���\���j����Ă���ꍇ�ł��A�s���Ⴂ�ɂē����[����
�@�z�M����Ă��܂��ꍇ���������܂��B���̍ۂ͑�ς��萔�ł����A���L
�@�w���X�ܗl�ɒ��ڂ��⍇�����������B

���������̑����s���ȓ_�͉��L�t�q�k�����m�F���������B������

       http://www.ato-barai.com/guidance/faq.html

�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
���ڍw���X�ܗl�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}

�����x�����Ɋւ��邨�₢���킹�́F
������ЃL���b�`�{�[���@�㕥���h�b�g�R�����ƕ�
TEL:03-5332-3490(�����y��9:00�`18:00)
Mail: customer@ato-barai.com

--------------------------------------------------------------

�y�㕥���h�b�g�R���z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`

  ���⍇����F03-5332-3490
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com

  �^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F

--------------------------------------------------------------',2,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (50,18,'���������s���[���i�����c�[�������FCEL�j','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�y�㕥��.com�z���������s�ē�','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','��������������������������������������������������������������������
�����肢�F���₢���킹�����ہA���L���[�����ʂ��c�����܂܂��ԐM����������
�������������������������������������������������������������������� 

{CustomerNameKj}�l

����� {SiteNameKj}�l�ł̂���������
�y�㕥���h�b�g�R���z�������p���������܂��āA
�܂��Ƃɂ��肪�Ƃ��������܂��B

���L�̃V���b�s���O�̐�������{�����s�������܂����B
���i�ɓ�������Ă��鐿�����ɋL�ڂ̂��x���������܂ł�
���x�������������܂��悤�A���肢�\���グ�܂��B

�������[���́A�u���i�����̂��m�点���[���v�ł͂������܂���B
�@������������������_�Ń��[���������肵�Ă���܂��֌W�ŁA
�@�������̔��s���ƁA���i�̔��������قȂ�ꍇ���������܂��̂ŁA
�@�\�߂��������������܂��B

�y���������e�z
���x���ҁF{CustomerNameKj}�@�l
���w���X�ܖ��F{SiteNameKj}�@
���w�����F{OrderDate}
���x�����z�F{UseAmount}
���w�����i���ׁF���i���^���^�w���i�ڌv
{OrderItems}
���ώ萔��  \{SettlementFee}
���� \{DeliveryFee}

�����i�Ƌ��ɐ������������Ă��Ȃ��ꍇ�ɂ́A��ς��萔�ł����A
�@���L�A����ւ���񂭂������܂��悤�A���肢�\���グ�܂��B

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�@�{���ɓ���Ă��⍇�����������B

���L�����Z���i���\���j����Ă���ꍇ�ł��A�s���Ⴂ�ɂē����[����
�@�z�M����Ă��܂��ꍇ���������܂��B���̍ۂ͑�ς��萔�ł����A���L
�@�w���X�ܗl�ɒ��ڂ��⍇�����������B

���������̑����s���ȓ_�͉��L�t�q�k�����m�F���������B������

       http://www.ato-barai.com/guidance/faq.html

�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
���ڍw���X�ܗl�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@
�d�b�F{Phone}

�����x�����Ɋւ��邨�₢���킹�́F
������ЃL���b�`�{�[���@�㕥���h�b�g�R�����ƕ�
�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F
TEL:03-5332-3490(�����y��9:00�`18:00)
Mail: customer@ato-barai.com
URL: http://www.ato-barai.com�i�p�\�R����p�j

--------------------------------------------------------------

�y�㕥���h�b�g�R���z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`

  ���⍇����F03-5332-3490
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com

  �^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F

--------------------------------------------------------------',2,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (51,19,'�^�M���ʃ��[��(OK, PC)','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�^�M���ʂ̂��m�点','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','��������������������������������������������������������������������
�ԐM�̍ۂ̂��肢�F���q�l�ւ̐��m�Ȃ��ē��񋟂̂��߁A���ԐM��������
�ۂ͈��p�ԐM�܂��́A�����ɂ������җl�̎����̂��L�������肢�v���܂��B
�������������������������������������������������������������������� 

{CustomerNameKj}�l

���̓x��{SiteNameKj}��{OneOrderItem}�����������ꂽ�ۂ̂��x�������@�ɁA�㕥���h�b�g�R������I�𒸂��܂��āA�܂��Ƃɂ��肪�Ƃ��������܂��B

�㕥�����ς̗^�M�R�������Ȃ��ʉ߂������܂����̂ł��񍐐\���グ�܂��B

�����������܂������i�ɂ��Ă̂��₢���킹�E���������e�̂��ύX�A�������̃L�����Z�����Ɋւ��܂��ẮA{SiteNameKj}�ł̑Ή��ƂȂ�܂��̂ŁA���ڂ��A�����Ē����܂��悤���肢�\���グ�܂��B

�y{SiteNameKj}�z
{ContactPhoneNumber}
{MailAddress}

���A�������̔��s�ɂ��Ă͕��Ђ�胁�[����������v���܂��̂�
���m�F���������B

�㕥�����ςɊւ��Ă��s���ȓ_�Ȃǂ������܂�����A���L�̌㕥���h�b�g�R���J�X�^�}�[�Z���^�[�ւ��₢���킹���������B

�y�㕥���h�b�g�R���J�X�^�}�[�Z���^�[�z
�^�c��ЁF�i���j�L���b�`�{�[��
TEL:03-5332-3490
',2,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (52,20,'�^�M���ʃ��[��(OK, CEL)','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�^�M���ʂ̂��m�点','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','��������������������������������������������������������������������
�ԐM�̍ۂ̂��肢�F���q�l�ւ̐��m�Ȃ��ē��񋟂̂��߁A���ԐM��������
�ۂ͈��p�ԐM�܂��́A�����ɂ������җl�̎����̂��L�������肢�v���܂��B
�������������������������������������������������������������������� 

{CustomerNameKj}�l

���̓x��{SiteNameKj}��{OneOrderItem}�����������ꂽ�ۂ̂��x�������@�ɁA�㕥���h�b�g�R������I�𒸂��܂��āA�܂��Ƃɂ��肪�Ƃ��������܂��B

�㕥�����ς̗^�M�R�������Ȃ��ʉ߂������܂����̂ł��񍐐\���グ�܂��B

�����������܂������i�ɂ��Ă̂��₢���킹�E���������e�̂��ύX�A�������̃L�����Z�����Ɋւ��܂��ẮA{SiteNameKj}�ł̑Ή��ƂȂ�܂��̂ŁA���ڂ��A�����Ē����܂��悤���肢�\���グ�܂��B

�y{SiteNameKj}�z
{ContactPhoneNumber}
{MailAddress}

���A�������̔��s�ɂ��Ă͕��Ђ�胁�[����������v���܂��̂�
���m�F���������B

�㕥�����ςɊւ��Ă��s���ȓ_�Ȃǂ������܂�����A���L�̌㕥���h�b�g�R���J�X�^�}�[�Z���^�[�ւ��₢���킹���������B

�y�㕥���h�b�g�R���J�X�^�}�[�Z���^�[�z
�^�c��ЁF�i���j�L���b�`�{�[��
TEL:03-5332-3490
',2,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (53,21,'�^�M���ʃ��[��(NG, PC)','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�^�M���ʂ̂��m�点','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{CustomerNameKj}�l

���̓x��{SiteNameKj}��{OneOrderItem}�����������ꂽ�ۂ̂��x�������@�ɁA�㕥���h�b�g�R������I�𒸂��܂��āA�܂��Ƃɂ��肪�Ƃ��������܂��B

�^�M�R���̌��ʁA����̌䒍���ɂ��܂��āA�㕥�����ς̗^�M�R�����ʉ߂������܂���ł����������񍐐\���グ�܂��B

��ς��萔�ł͂������܂����A{SiteNameKj}�ւ��A���̏�A���̂��x�������@�ɂ��ύX���������Ƒ����܂��B

�y{SiteNameKj}�z
{ContactPhoneNumber}
{MailAddress}

�����������܂������i�ɂ��Ă̂��₢���킹�E���������e�̂��ύX�A�������̃L�����Z�����Ɋւ��܂��Ă��A{SiteNameKj}�ł̑Ή��ƂȂ�܂��̂ŁA���ڂ��A�����Ē����܂��悤���肢�\���グ�܂��B

���x�������@�����ύX�������ꍇ�A�㕥�����ςɊւ���萔���͈�ؔ����������܂���B

�㕥�����ς̗^�M�R���ɂ��܂��ẮA�㕥���h�b�g�R�����^�c���Ă���܂��i���j�L���b�`�{�[���ɂčs���Ă���܂��B�^�M�R�����ʂ̗��R�Ȃǂɂ��܂��ẮA�l�����܂ޓ��e�ɂȂ�ׁA���Ђ���{SiteNameKj}�ւ͈�؊J���������Ă���܂���B���܂��ẮA�^�M�R�����ʂ̗��R�Ȃǂ̂��₢���킹�Ɋւ��܂��ẮA���ځA���d�b�ɂē��Ђ֌�A�������܂��悤���肢�\���グ�܂��B

�y�㕥���h�b�g�R���J�X�^�}�[�Z���^�[�z
�^�c��ЁF�i���j�L���b�`�{�[��
TEL:03-5332-3490
�c�Ǝ���:10:00�`18:00�@�N�����x(�N���E�N�n�̂���)

�^�M�R���Ɋւ��܂��ẮA���{�l�l��育�A�������܂��ƍĐR�����\�ł������܂��B
�㕥���̐R�������Ƃ������܂��āA���{�l�l�m�F�����邱�Ƃ��K�{�ƂȂ��Ă���܂��B
���̂��߁A���Z���₨�d�b�ԍ��̕s���A�������͂��m�荇���₲�e�ʂ̕��Ƃ������ŁA���Z���₨�d�b�ԍ��̂����`���������җl�ƈقȂ�ꍇ(�c�����Ⴄ�ꍇ)�ȂǁA�^�M�R�����ʉ߂������܂���B
�@�l�p�E�X�ܗp���l���`�ɂĂ��������ꂽ�ꍇ�����l�ł������܂��B

�������S�����肪����C�����\�ȏꍇ�ɂ́A�ēx�^�M�R�����������܂��̂ŁA
��L�̌㕥���h�b�g�R���J�X�^�}�[�Z���^�[�܂Ō�A�����������܂��悤�A���肢�\���グ�܂��B

���萔���������������܂��āA�܂��Ƃɋ��k�ł͂������܂����A���Ή��̒���낵�����肢�\���グ�܂��B
',2,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (54,22,'�^�M���ʃ��[��(NG, CEL)','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�^�M���ʂ̂��m�点','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{CustomerNameKj}�l

���̓x��{SiteNameKj}��{OneOrderItem}�����������ꂽ�ۂ̂��x�������@�ɁA�㕥���h�b�g�R������I�𒸂��܂��āA�܂��Ƃɂ��肪�Ƃ��������܂��B

�^�M�R���̌��ʁA����̌䒍���ɂ��܂��āA�㕥�����ς̗^�M�R�����ʉ߂������܂���ł����������񍐐\���グ�܂��B

��ς��萔�ł͂������܂����A{SiteNameKj}�ւ��A���̏�A���̂��x�������@�ɂ��ύX���������Ƒ����܂��B

�y{SiteNameKj}�z
{ContactPhoneNumber}
{MailAddress}

�����������܂������i�ɂ��Ă̂��₢���킹�E���������e�̂��ύX�A�������̃L�����Z�����Ɋւ��܂��Ă��A{SiteNameKj}�ł̑Ή��ƂȂ�܂��̂ŁA���ڂ��A�����Ē����܂��悤���肢�\���グ�܂��B

���x�������@�����ύX�������ꍇ�A�㕥�����ςɊւ���萔���͈�ؔ����������܂���B

�㕥�����ς̗^�M�R���ɂ��܂��ẮA�㕥���h�b�g�R�����^�c���Ă���܂��i���j�L���b�`�{�[���ɂčs���Ă���܂��B�^�M�R�����ʂ̗��R�Ȃǂɂ��܂��ẮA�l�����܂ޓ��e�ɂȂ�ׁA���Ђ���{SiteNameKj}�ւ͈�؊J���������Ă���܂���B���܂��ẮA�^�M�R�����ʂ̗��R�Ȃǂ̂��₢���킹�Ɋւ��܂��ẮA���ځA���d�b�ɂē��Ђ֌�A�������܂��悤���肢�\���グ�܂��B

�y�㕥���h�b�g�R���J�X�^�}�[�Z���^�[�z
�^�c��ЁF�i���j�L���b�`�{�[��
TEL:03-5332-3490
�c�Ǝ���:10:00�`18:00�@�N�����x(�N���E�N�n�̂���)

�^�M�R���Ɋւ��܂��ẮA���{�l�l��育�A�������܂��ƍĐR�����\�ł������܂��B
�㕥���̐R�������Ƃ������܂��āA���{�l�l�m�F�����邱�Ƃ��K�{�ƂȂ��Ă���܂��B
���̂��߁A���Z���₨�d�b�ԍ��̕s���A�������͂��m�荇���₲�e�ʂ̕��Ƃ������ŁA���Z���₨�d�b�ԍ��̂����`���������җl�ƈقȂ�ꍇ(�c�����Ⴄ�ꍇ)�ȂǁA�^�M�R�����ʉ߂������܂���B
�@�l�p�E�X�ܗp���l���`�ɂĂ��������ꂽ�ꍇ�����l�ł������܂��B

�������S�����肪����C�����\�ȏꍇ�ɂ́A�ēx�^�M�R�����������܂��̂ŁA
��L�̌㕥���h�b�g�R���J�X�^�}�[�Z���^�[�܂Ō�A�����������܂��悤�A���肢�\���グ�܂��B

���萔���������������܂��āA�܂��Ƃɋ��k�ł͂������܂����A���Ή��̒���낵�����肢�\���グ�܂��B
',2,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (55,23,'�p�X���[�h��񂨒m�点���[��','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer@ato-barai.com',null,null,null,'�y�㕥��.com�z �p�X���[�h���̂��m�点','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{EnterpriseNameKj}�@�l

���̓x�͕��ЃT�[�r�X�A�y�㕥���h�b�g�R���z�ɂ��\�����������A
�܂��Ƃɂ��肪�Ƃ��������܂��B

�㕥�����ϊǗ��V�X�e���Ƀ��O�C�����Ă��������ׂɕK�v��
�p�X���[�h�������肳���Ă��������܂��B

PW :{GeneratedPassword}

�T�[�r�X�J�n�ɓ������āA�܂��A�^�c�Ɋւ��邨�₢���킹���́A
���[�������̂��A����ɂ��C�y�ɂ��⍇���������B


����Ƃ����i�����t�������̒��A�X�������肢�\���グ�܂��B

������ЃL���b�`�{�[���@�㕥���h�b�g�R�����ƕ��@�X�^�b�t�ꓯ

--------------------------------------------------------------

�y�㕥���h�b�g�R���z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`

  ���⍇����F0120-667-690
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com

  �^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F

--------------------------------------------------------------',2,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (56,1,'���Ǝғo�^�����i�T�[�r�X�J�n�j���[��','�㕥�����σT�[�r�X','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'�y�㕥�����σT�[�r�X�z �X�ܐR���ʉ߂̂��m�点','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','{EnterpriseNameKj}�@�l

���̓x�͕��ЃT�[�r�X�A�y�㕥�����σT�[�r�X�z�ɂ��\�����������A
�܂��Ƃɂ��肪�Ƃ��������܂��B
 
�R���̌��ʁA�ʉ߂ƂȂ�܂����̂ŁA�㕥�����ϊǗ��V�X�e����
�����p���������̂ɕK�v��ID�ƃp�X���[�h�𕹂��ĕ񍐐\���グ�܂��B

�y�Ǘ��T�C�g�t�q�k�z
https://atobarai.seino.co.jp/seino-financial/member/

ID : {LoginId}
���p�X���[�h�͕ʓr���[���ɂĂ����肳���Ă��������܂��B
���T�C�g�h�c�͏�L�h�c�Ƃ͈قȂ�܂��̂ł����ӂ��������B
�T�C�g�h�c�̎Q�ƕ��@�͈ȉ��̒ʂ�ł��B

�y1�z�Ǘ��T�C�g�Ƀ��O�C��
�@�@���@���@���@��
�y2�z�u�o�^���Ǘ��v���N���b�N
�@�@���@���@���@��
�y3�z�u�T�C�g���v���N���b�N
�@�@���@���@���@��
�y4�z�u�T�C�g�h�c�v���ɕ\������܂��B

 ���}�j���A���̃_�E�����[�h�i�K�{�j
���L��URL���A�y�㕥�����σT�[�r�X�z�̉^�p�}�j���A�����_�E�����[�h
���Ă��g�p�������B
�T�[�r�X�J�n�ɕK�v�ȃ}�j���A���ƂȂ��Ă���܂��̂ŁA�K�����m�F
���������܂��悤���肢�\���グ�܂��B

  http://www.seino.co.jp/financial/atobarai/Shop_Manual.pdf

���{���ɂ�Adobe PDF Reader ���K�v�ł��B�C���X�g�[������Ă��Ȃ�
���́A���L��URL��蓯�\�t�g�̃C���X�g�[�������肢�������܂��B

  http://www.adobe.com/jp/products/acrobat/readstep2.html

�Ǘ��V�X�e���̂����p���@�́A�_�E�����[�h���Ă����������}�j���A��
�����m�F���������B

�T�[�r�X�̊J�n�܂ŁA�X�ܗl�ɂ͈ȉ��̂悤�ȍ�Ƃ����Ă��������܂��B
�J�n�̂��A�������Y��Ȃ��悤�A���肢�\���グ�܂��B

�������@STEP 1�@�������o�^���e�̂��m�F

�Ǘ��T�C�g�Ƀ��O�C���A�X�܏����m�F�i�v�������̑��̏��j

�������@STEP 2�@��������^���͂̃T�C�g�f��

�}�j���A���ɂ��������āA�X�ܗl�T�C�g��ɓ����ϕ��@�p�̒�^���͂��f��
�i���菤����@�y�[�W�⌈�ϑI����ʂȂǁj

�T�C�g�f�ڗp��^���E�摜�񋟃y�[�W�F
http://www.seino.co.jp/financial/atobarai/tokushoho/
�����̎��_�ŃT�[�r�X�J�n�ƂȂ�܂�

�������@STEP 3�@�������T�[�r�X�J�n�̓��Ђւ̂��ʒm

�T�[�r�X���J�n�����|���A���Ђ܂Ń��[���������͂��d�b�ɂĂ��A���������B
 mail: sfc-atobarai@seino.co.jp
 tel:  03-6908-7888

�������@STEP 4�@���������Ђ����ω�ʂ��m�F

���ВS�������ω�ʂ��m�F�����Ă��������A��肪�Ȃ���΂��̂܂܉^�c�A
��肪����ΏC���̂��肢�������Ă����������Ƃ��������܂��B

  �������u����v�͂����܂�

������җl�ւ̐������̂��ē��p���̃_�E�����[�h�i�C�Ӂj
���L�̂t�q�k��萿�����̂��ē��p�����_�E�����[�h���āA���i�ɓ���
���Ă��������B
�i���ē��p���̓����͓X�ܗl�̂����f�ɂ��C�ӂōs���Ă���������
�@����܂����A���߂ē����ς������p�Ȃ����җl�ɂƂ��Ă͕�����Ղ�
�@�Ȃ�A���⍇�������邱�Ƃɂ��q����܂��̂ŁA�������Ă�����������
�@�𐄏����Ă���܂��B�j

http://www.seino.co.jp/financial/atobarai/dokon.xls

�T�[�r�X�J�n�ɓ������āA�܂��A�^�c�Ɋւ��邨�₢���킹���́A
���[�������̂��A����ɂ��C�y�ɂ��⍇���������B


����Ƃ����i�����t�������̒��A�X�������肢�\���グ�܂��B

�Z�C�m�[�t�B�i���V����������Ё@�㕥�����σT�[�r�X�S���@�X�^�b�t�ꓯ

--------------------------------------------------------------

�y�㕥�����σT�[�r�X�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`

  ���⍇����F03-6908-7888
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: sfc-atobarai@seino.co.jp

  �^�c��ЁF�Z�C�m�[�t�B�i���V�����������
�@�Z���F��503-8501 �򕌌���_�s�c�����P�Ԓn

--------------------------------------------------------------',3,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (57,2,'�����o�^�i�^�M�J�n�j���[��','�㕥�����σT�[�r�X','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'�y�㕥�����σT�[�r�X�z�^�M�J�n�̂��m�点�i{OrderCount}���j','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','{EnterpriseNameKj} �l

�����y�㕥�����σT�[�r�X�z�������p���������A�܂��Ƃɂ��肪�Ƃ��������܂��B
�ȉ��̂��������󂯕t���������܂����B
������^�M�ɓ���܂��̂ŁA���i���܂���������Ȃ��悤�����Ӊ������B

��t���������F{OrderCount}��

�������Җ��i���������z�j
--------------------------------------------------------------
{OrderSummary}
--------------------------------------------------------------
��L������̗^�M������ɁA�^�M�������[���𑗐M�������܂��B


��18:00�ȍ~�̗^�M�́A�ʏ헂��11:00�܂ł̉񓚂ƂȂ�܂��̂ł����Ӊ������B
�������ɂ��^�M�ɂ����鎞�Ԃ��قȂ�ꍇ���������܂��B���̏ꍇ�A�^�M���ʂ�
�o�����̂��玩���ŗ^�M�������[�������M����܂��̂ŁA���炩���߂������������B

�����������������������@�L�����Z�������������ꍇ�@����������������������

���o�^���ꂽ�����̃L�����Z�����������ꍇ�́A���萔�ł����u���������v����
���������������A�Y���̂�������N���b�N���ăL�����Z���������s���ĉ������B

������������������������������������������������������������������������

�y�Ǘ���ʂt�q�k�z
https://atobarai.seino.co.jp/seino-financial/member/

���s���ȓ_�Ȃǂ������܂�����A���C�y�ɂ��₢���킹�������B

--------------------------------------------------------------

�y�㕥�����σT�[�r�X�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`

  ���⍇����F03-6908-7888
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: sfc-atobarai@seino.co.jp

  �^�c��ЁF�Z�C�m�[�t�B�i���V�����������
�@�Z���F��503-8501 �򕌌���_�s�c�����P�Ԓn

--------------------------------------------------------------',3,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (58,3,'�^�M�������[��','�㕥�����σT�[�r�X','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'�y�㕥�����σT�[�r�X�z�^�M�����̂��m�点�F�v{CreditCount}���i����NG{NgCount}���j','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','{EnterpriseNameKj}�@�l

�����y�㕥�����σT�[�r�X�z�������p���������A�܂��Ƃɂ��肪�Ƃ��������܂��B

�^�M�����F{CreditCount} ��

�̗^�M���ʂ��o�܂����̂ł��񍐂������܂��B

�y�Ǘ���ʂt�q�k�z
https://atobarai.seino.co.jp/seino-financial/member/

���^�M��NG�̂������ł����Ă��ANG���R�ɂ���ẮA���ۏ؂ɂāuOK�v�ɕύX�ł���ꍇ���������܂��B
���ۏ؂Ō㕥���T�[�r�X����]�̕��͈ȉ��ɋL�ڂ́yNG���R�ɂ�鏈�����@�ɂ��āz���Q�l�ɂ��Ă��������B
�i���ۏ؂ł��uOK�v�ɕύX�ł��Ȃ��ꍇ���������܂��̂ŁA���Ђ����
�ԐM���[�������m�F���������Ă���A���i�����Ȃǂ��s���Ă��������B�j

{Orders}

�yOK�Č��̏����z
�^�M���ʉ߂���������Ɋւ��܂��ẮA

1.���i�̔���
2.�z���`�[�ԍ��o�^

�ɂ��i�݉������B

�yNG���R�ɂ�鏈�����@�ɂ��āz
�� NG���R���u�����x�����v�u���z�ۗ��v�u���ۏؕύX�\�v�̏ꍇ
���ۏ؂ł̌㕥���T�[�r�X�� �؂�ւ��Ē������Ƃ��\�ł��B
���ۏ؂ɕύX����ꍇ�́A���̃��[�����{OutOfAmendsDays}���ȓ��Ɍ㕥�����ϊǗ��V�X�e����
���O�C����ɑ�������{���Ă��������B

�� ��L�ȊO��NG���R�̏ꍇ
���̑���NG���R�̂�����Ɋւ��܂��ẮA�����߂ɂ��w���җl�ɑ��̌��ϕ��@�̂��I����
���������Ȃǂ̂��Ή������肢�������܂��B

--------------------------------------------------------------

�y�㕥�����σT�[�r�X�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`

  ���⍇����F03-5909-4500
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: sfc-atobarai@seino.co.jp

  �^�c��ЁF�Z�C�m�[�t�B�i���V�����������
�@�Z���F��503-8501 �򕌌���_�s�c�����P�Ԓn

--------------------------------------------------------------',3,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (59,4,'���������s���[���iPC�j','�㕥�����σT�[�r�X','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'�y�㕥�����σT�[�r�X�z���������s�ē��@�i�n�K�L�œ͂��܂��j','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','��������������������������������������������������������������������������
�����肢�F���₢���킹�����ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������������

{CustomerNameKj}�l

����� {SiteNameKj}�l�ł̂���������
�y�㕥�����σT�[�r�X�z�������p���������܂��āA
�܂��Ƃɂ��肪�Ƃ��������܂��B

���L�̃V���b�s���O�̂���������{�����s�������܂��̂ŁA������������A
�������ɋL�ڂ���Ă��邨�x���������܂łɂ��x�������������܂��悤�A
���肢�\���グ�܂��B

{OrderPageAccessUrl}

�y���������e�z
���x���ҁF{CustomerNameKj}�@�l
���w���X�ܖ��F{SiteNameKj}�@
���w�����F{OrderDate}
���x�����z�F{UseAmount}
���w�����i���ׁF���i���^���^�w���i�ڌv
{OrderItems}
���ώ萔��                              \{SettlementFee}
����                                    \{DeliveryFee}

���X�����̂Ȃǂɂ��A���������͂��Ȃ����Ƃ��������܂��B
�@��T�Ԃقǂ��҂����������Ă����������͂��Ȃ��ꍇ�ɂ́A��ς��萔�ł����A
�@���L�A����ւ���񂭂������܂��悤�A���肢�\���グ�܂��B

�����i�ɂ��܂��Ă��A���[���ւȂǂ̔z�����@�̏ꍇ�ɂ́A�z�����̂Ȃǂɂ��
�@�͂��Ȃ��ꍇ���������܂��B
�@������A���i���͂��Ă��Ȃ��ꍇ�ɂ͑�ς��萔�ł͂������܂����A���������ꂽ
�@�X�ܗl�܂Œ��ڂ��⍇�����������܂��B

�����������тɖ{���[�����A���i���������������Ă��܂����Ƃ��������܂����A
�������e�͉������܂��悤���肢�\���グ�܂��B
�܂��A���������ɖ��ׂ��܂܂�Ă���܂��̂ł��m�F���������܂��B

�ڂ����͉��L�p�\�R���pURL�������������B

http://www.seino.co.jp/financial/atobarai/guidance/

���s���ȓ_�Ȃǂ������܂�����A���C�y�ɉ��L�܂ł��⍇���������B

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�@�{���ɓ���Ă��⍇�����������B

���L�����Z���i���\���j����Ă���ꍇ�ł��A�s���Ⴂ�ɂē����[����
�@�z�M����Ă��܂��ꍇ���������܂��B���̍ۂ͑�ς��萔�ł����A���L
�@�w���X�ܗl�ɒ��ڂ��⍇�����������B

�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
���ڍw���X�ܗl�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}

�����x�����Ɋւ��邨�₢���킹�́F
�Z�C�m�[�t�B�i���V����������Ё@�㕥�����σT�[�r�X�S��
TEL:03-6908-7888 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
Mail: sfc-atobarai@seino.co.jp

--------------------------------------------------------------

�y�㕥�����σT�[�r�X�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`

  ���⍇����F03-6908-7888
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: sfc-atobarai@seino.co.jp

  �^�c��ЁF�Z�C�m�[�t�B�i���V�����������
�@�Z���F��503-8501 �򕌌���_�s�c�����P�Ԓn

--------------------------------------------------------------',3,'2015/08/31 22:42:31',9,'2017/12/26 15:14:00',59,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (60,5,'���������s���[���iCEL�j','�㕥�����σT�[�r�X','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'�y�㕥�����σT�[�r�X�z���������s�ē��@�i�n�K�L�œ͂��܂��j','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','
�����肢�F���₢���킹�����ہA���L���[�����ʂ��c�����܂܂��ԐM����������


{CustomerNameKj}�l

����� {SiteNameKj}�l�ł̂���������
�y�㕥�����σT�[�r�X�z�������p���������܂��āA
�܂��Ƃɂ��肪�Ƃ��������܂��B

���L�̃V���b�s���O�̂���������{�����s�������܂��̂ŁA������������A
�������ɋL�ڂ���Ă��邨�x���������܂łɂ��x�������������܂��悤�A
���肢�\���グ�܂��B

�y���������e�z
���x���ҁF{CustomerNameKj}�@�l
���w���X�ܖ��F{SiteNameKj}�@
���w�����F{OrderDate}
���x�����z�F{UseAmount}
���w�����i���ׁF���i���^���^�w���i�ڌv
{OrderItems}
���ώ萔��  \{SettlementFee}
���� \{DeliveryFee}

���X�����̂Ȃǂɂ��A���������͂��Ȃ����Ƃ��������܂��B
�@��T�Ԃقǂ��҂����������Ă����������͂��Ȃ��ꍇ�ɂ́A��ς��萔�ł����A
�@���L�A����ւ���񂭂������܂��悤�A���肢�\���グ�܂��B

�����i�ɂ��܂��Ă��A���[���ւȂǂ̔z�����@�̏ꍇ�ɂ́A�z�����̂Ȃǂɂ��
�@�͂��Ȃ��ꍇ���������܂��B
�@������A���i���͂��Ă��Ȃ��ꍇ�ɂ͑�ς��萔�ł͂������܂����A���������ꂽ
�@�X�ܗl�܂Œ��ڂ��⍇�����������܂��B

�����������тɖ{���[���A���i���������������Ă��܂����Ƃ��������܂����A
�������e�͉������܂��悤���肢�\���グ�܂��B
�܂��A���������ɖ��ׂ��܂܂�Ă���܂��̂ł��m�F���������܂��B

�ڂ����͉��L�p�\�R���pURL�������������B

http://www.seino.co.jp/financial/atobarai/guidance/

���s���ȓ_�Ȃǂ������܂�����A���C�y�ɉ��L�܂ł��⍇���������B

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�@�{���ɓ���Ă��⍇�����������B

���L�����Z���i���\���j����Ă���ꍇ�ł��A�s���Ⴂ�ɂē����[����
�@�z�M����Ă��܂��ꍇ���������܂��B���̍ۂ͑�ς��萔�ł����A���L
�@�w���X�ܗl�ɒ��ڂ��⍇�����������B

�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
���ڍw���X�ܗl�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@
�d�b�F{Phone}

�����x�����Ɋւ��邨�₢���킹�́F
�Z�C�m�[�t�B�i���V����������Ё@�㕥�����σT�[�r�X�S��
�Z���F��503-8501 �򕌌���_�s�c�����P�Ԓn
TEL:03-6908-7888 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
Mail: sfc-atobarai@seino.co.jp
URL: http://www.seino.co.jp/financial�i�p�\�R����p�j

--------------------------------------------------------------

�y�㕥�����σT�[�r�X�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`

  ���⍇����F03-6908-7888
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: sfc-atobarai@seino.co.jp

  �^�c��ЁF�Z�C�m�[�t�B�i���V�����������
�@�Z���F��503-8501 �򕌌���_�s�c�����P�Ԓn

--------------------------------------------------------------',3,'2015/08/31 22:42:31',9,'2015/12/01 12:58:08',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (61,6,'�����m�F���[���iPC�j','�㕥�����σT�[�r�X','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'�y�㕥�����σT�[�r�X�z�������m�F�̂���','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','��������������������������������������������������������������������������
�����肢�F���₢���킹�����ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������������

{CustomerNameKj}�@�l

���̓x�́A{SiteNameKj}�l�ł̂��������ɁA
�y�㕥�����σT�[�r�X�z�������p���������܂��āA
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ReceiptDate}��{CustomerNameKj}�l����̂�������
�m�F�������܂����̂ł��񍐐\���グ�܂��B

�ȉ����A���񂲓��������������������̓��e�ƂȂ�܂��B

�y�̎��ς݂��������e�z
���x���ҁF{CustomerNameKj}�@�l
���w���X�ܖ��F{SiteNameKj}�@
���w�����F{OrderDate}
���x�����z�F{UseAmount}
���w�����i���ׁF���i���^���^�w���i�ڌv
{OrderItems}
���ώ萔��                              \{SettlementFee}
����                                    \{DeliveryFee}

���w���X�ܖ��F{SiteNameKj}
���A����F{Phone}
�Z���F{Address}

���s���ȓ_�Ȃǂ������܂�����A���C�y�ɂ��₢���킹�������B

�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹��
���ڍw���X�ܗl�ɂ��₢���킹�������B

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O
�i�t���l�[���j��{���ɓ���Ă��⍇�����������B

�܂��̂����p��S���A���҂����Ă���܂��B

--------------------------------------------------------------

�y�㕥�����σT�[�r�X�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`

  ���⍇����F03-6908-7888
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: sfc-atobarai@seino.co.jp

  �^�c��ЁF�Z�C�m�[�t�B�i���V�����������
�@�Z���F��503-8501 �򕌌���_�s�c�����P�Ԓn

--------------------------------------------------------------',3,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (62,7,'�����m�F���[���iCEL�j','�㕥�����σT�[�r�X','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'�y�㕥�����σT�[�r�X�z�������m�F�̂���','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','
�����肢�F���₢���킹�����ہA���L���[�����ʂ��c�����܂܂��ԐM����������


{CustomerNameKj}�@�l

���̓x�́A{SiteNameKj}�l�ł̂��������ɁA
�y�㕥�����σT�[�r�X�z�������p���������܂��āA
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ReceiptDate}��{CustomerNameKj}�l����̂�������
�m�F�������܂����̂ł��񍐐\���グ�܂��B

�ȉ����A���񂲓��������������������̓��e�ƂȂ�܂��B

�y�̎��ς݂��������e�z
���x���ҁF{CustomerNameKj}�@�l
���w���X�ܖ��F{SiteNameKj}�@
���w�����F{OrderDate}
���x�����z�F{UseAmount}
���w�����i���ׁF���i���^���^�w���i�ڌv
{OrderItems}
���ώ萔�� \{SettlementFee}
����       \{DeliveryFee}

���w���X�ܖ��F{SiteNameKj}
���A����F{Phone}
�Z���F{Address}

���s���ȓ_�Ȃǂ������܂�����A���C�y�ɂ��₢���킹�������B

�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹��
���ڍw���X�ܗl�ɂ��₢���킹�������B

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O
�i�t���l�[���j��{���ɓ���Ă��⍇�����������B

�܂��̂����p��S���A���҂����Ă���܂��B

--------------------------------------------------------------

�y�㕥�����σT�[�r�X�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`

  ���⍇����F03-6908-7888
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: sfc-atobarai@seino.co.jp

  �^�c��ЁF�Z�C�m�[�t�B�i���V�����������
�@�Z���F��503-8501 �򕌌���_�s�c�����P�Ԓn

--------------------------------------------------------------',3,'2015/08/31 22:42:31',9,'2015/12/01 12:58:23',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (63,8,'���֊������[��','','','',null,null,null,'','','',3,'2015/08/31 22:42:31',9,'2015/12/01 13:01:10',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (64,9,'�L�����Z���m�F���[��','�㕥�����σT�[�r�X','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'�y�㕥�����σT�[�r�X�z�L�����Z���m��̂���({OrderId})','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','{EnterpriseNameKj}�@�l

�����y�㕥�����σT�[�r�X�z�������p���������܂��āA�܂��Ƃɂ��肪�Ƃ��������܂��B

�ȉ��̂������̃L�����Z��������܂����̂ŁA���m�F�������B
�܂��A�L�����Z���̃^�C�~���O�ɂ���āA���̌�̏���җl�ւ̑Ή����قȂ�܂�
�̂ł����Ӊ������B�i���ȉ��́y1�z�`�y4�z�����Q�Ɖ������B�j

�y�Ǘ���ʂt�q�k�z
https://atobarai.seino.co.jp/seino-financial/member/

�y�L�����Z���m����z
�L�����Z���敪�F{CancelPhase}
������ID�F{OrderId}
�����掁���F{CustomerNameKj}�@�l
���������z�F{UseAmount}
���������F{OrderDate}

�y1�z�����ֈČ��̃L�����Z��
�ԋ����͔������܂���B�����������łɔ�������Ă���ꍇ�́A���q�l
�ɐ������j���̂��肢�����A�������肢�\���グ�܂��B

�y2�z���֍ς݈Č��̃L�����Z��
���񗧑֎��ɁA���֍ς݂̋��z���A���E�ɂ��ԋ������Ă��������܂��B
�X�ܗl���ł̍�Ƃ͕K�v�������܂���B�܂��A���ώ萔���������������܂���B

�y3�z���֍ς݁E���q�l�������ς݈Č��̃L�����Z��
��قǓ��Ђ��X�ܗl�ɘA���������Ă��������܂��̂ŁA���̌�ɂ��q�l�ցA
���i�����X�ܗl��育�ԋ������������ƂɂȂ�܂��B
���ώ萔���͔����������܂���̂ŁA���񗧑֎��Ɏ萔����ԋ��������܂��B

�y4�z�����ւ��E���q�l�����ς݈Č��̃L�����Z��
��قǓ��Ђ��X�ܗl�ɘA���������Ă��������܂��̂ŁA���̌�ɂ��q�l�ցA
���i�����X�ܗl��育�ԋ������������ƂɂȂ�܂��B
�܂��A���q�l����̂������������񗧑֎��ɓ��Ђ��X�ܗl�֕ԋ������Ă���
�����܂��B���̏ꍇ�����ώ萔���͔����������܂���B

�����q�l���X�ܗl�̌����֒��ړ������ꂽ�ꍇ��A�X�ܗl������đ������
�@�������ꂽ�ꍇ���́A���q�l�ƓX�ܗl�Ԃł̂�������������Ă���ꍇ��
�@�L�����Z�������̍ۂɂ́A��L�y1�z�`�y4�z�̂�����̏ꍇ������̎萔��
�@�����񗧑֎��̒����z�ɂĒ��������Ă��������܂��B

���s���ȓ_�Ȃǂ������܂�����A���C�y�ɓ��Ђ܂ł��₢���킹�������B

--------------------------------------------------------------

�y�㕥�����σT�[�r�X�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`

  ���⍇����F03-6908-7888
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: sfc-atobarai@seino.co.jp

  �^�c��ЁF�Z�C�m�[�t�B�i���V�����������
�@�Z���F��503-8501 �򕌌���_�s�c�����P�Ԓn

--------------------------------------------------------------',3,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (65,10,'�A�h���X�m�F���[��','�㕥�����σT�[�r�X','','sfc-atobarai@seino.co.jp',null,null,null,'�y�㕥�����σT�[�r�X�z�̂��I�����肪�Ƃ��������܂�','','{CustomerNameKj}�l

���̓x�́A���x�����@�Ɂy�㕥�����σT�[�r�X�z�����I�����������A
�܂��Ƃɂ��肪�Ƃ��������܂��B

�������܁A���L�̂������ɂ����܂��Č㕥�����σT�[�r�X�������p���������邩�A
�R�����������Ă���܂��B

�y���������e�z
���x���ҁF{CustomerNameKj}�@�l
���w���X�ܖ��F{SiteNameKj}�@
���w�����F{OrderDate}
���x�����z�F{UseAmount}
���w�����i���ׁF���i���^���^�w���i�ڌv
{OrderItems}
���ώ萔��  \{SettlementFee}
����        \{DeliveryFee}


���ʂɂ��܂��ẮA���������������܂����X�ܗl���A
��قǂ��A��������܂��̂ŁA�������X���҂��������܂��B

���s���ȓ_�Ȃǂ������܂�����A���C�y�ɂ��₢���킹�������B

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�@�{���ɓ���Ă��⍇�����������B

--------------------------------------------------------------

�y�㕥�����σT�[�r�X�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`

  ���⍇����F03-6908-7888
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: sfc-atobarai@seino.co.jp

  �^�c��ЁF�Z�C�m�[�t�B�i���V�����������
�@�Z���F��503-8501 �򕌌���_�s�c�����P�Ԓn

--------------------------------------------------------------',3,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (66,11,'�����������x�����[���iPC�j','�㕥�����σT�[�r�X','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'�y�㕥�����σT�[�r�X�z�����������x���������ł�','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','��������������������������������������������������������������������������
�����肢�F���₢���킹�����ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������������

{CustomerNameKj}�l


���̓x�́A{SiteNameKj}�l�ł̂��������ɁA
�y�㕥�����σT�[�r�X�z�������p���������܂��āA�܂��Ƃɂ��肪�Ƃ��������܂��B
{IssueDate}�ɐ������������肢�����܂������A���茳�ɂ��͂��ł��傤���B

�����肵���������̂��x�����������߂Â��Ă܂���܂����̂ŁA���m�点�������܂��B

���y���E�j�Փ��͓����̊m�F�����Ȃ��ׁA���̊ԂɌ�葱�������������ꍇ�A
����Ⴂ�œ����[���������Ă��܂��܂��B
���̏ꍇ�́A�܂��Ƃɐ\���󂲂����܂��񂪁A�����[�����폜���Ă��������܂��悤
���肢�\���グ�܂��B
�i�X�֋ǂŌ�葱�������������ꍇ�A�m�F�ɍő�4�c�Ɠ�������ꍇ���������܂��̂ŁA
�O����O�X���Ɍ�葱�����������Ă���܂��Ă��A�����悤�ɓ���Ⴂ�œ����[����
�͂��Ă��܂��ꍇ���������܂��B�j

���x���������F{LimitDate}
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���������z�F{UseAmount}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
�������F{IssueDate}
 
�܂����x�������������Ă��Ȃ��ꍇ�́A���Ђ�肨���肢�����܂�����������
���m�F�̂����A��L�������܂łɂ��x�������������܂��悤�A���肢�\���グ�܂��B

��������������߂��Ă��܂��܂��ƁA����Ҍ_��@�Ɋ�Â��x�����Q���y�сA
�Đ����萔�������Z����܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��A�������̊m�F�͎��܂��B
�����ւ��U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z
�W���p���l�b�g��s�@
���~�W�x�X�@
���ʌ����@0015015
�Z�C�m�[�t�B�i���V�����i�J

�y�X�֐U�֌����z
�����L���F00100-7
�����ԍ��F292043
������ЃL���b�`�{�[���@�Z�C�m�[FC�W

���X�֋ǁ^��s���炨�U���݂��������ꍇ�A�U���萔�������q�l�����S�ƂȂ�܂��B

���X�֋ǂ̌����������̏ꍇ�́A�X�֋ǂ̂`�s�l�𗘗p���Č������炲����������
����΁A�X�֐U���萔���͂�����܂���B(�X�܌��ώ萔���Ƃ͕ʂł��B)

�����ꐿ���������茳�ɓ͂��Ă��Ȃ��ꍇ��A���x���Ɋւ��܂��āA���s���ȓ_��
�������܂�����A���L�܂ł��C�y�ɂ��₢���킹�������܂��B

�����i�ɂ��܂��Ă��A���[���ւȂǂ̔z�����@�̏ꍇ�ɂ́A�z�����̂Ȃǂɂ��
�͂��Ȃ��ꍇ���������܂��B
������A���i���͂��Ă��Ȃ��ꍇ�ɂ͑�ς��萔�ł͂������܂����A���������ꂽ
�X�ܗl�܂Œ��ڂ��⍇�����������܂��B

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�@�{���ɓ���Ă��⍇�����������B

���L�����Z���i���\���j����Ă���ꍇ�ł��A�s���Ⴂ�ɂē����[����
�@�z�M����Ă��܂��ꍇ���������܂��B���̍ۂ͑�ς��萔�ł����A�������ꂽ
�@�X�ܗl�ɒ��ڂ��⍇�����������B

����Ƃ����ЃT�[�r�X�y�㕥�����σT�[�r�X�z����낵�����肢�\���グ�܂��B

--------------------------------------------------------------

�y�㕥�����σT�[�r�X�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`

  ���⍇����F03-6908-7888
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: sfc-atobarai@seino.co.jp

  �^�c��ЁF�Z�C�m�[�t�B�i���V�����������
�@�Z���F��503-8501 �򕌌���_�s�c�����P�Ԓn

--------------------------------------------------------------',3,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (67,12,'�����������x�����[���iCEL�j','�㕥�����σT�[�r�X','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'�y�㕥�����σT�[�r�X�z�����������x���������ł�','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','
�����肢�F���₢���킹�����ہA���L���[�����ʂ��c�����܂܂��ԐM����������


{CustomerNameKj}�l


���̓x�́A{SiteNameKj}�l�ł̂��������ɁA
�y�㕥�����σT�[�r�X�z�������p���������܂��āA�܂��Ƃɂ��肪�Ƃ��������܂��B
{IssueDate}�ɐ������������肢�����܂������A���茳�ɂ��͂��ł��傤���B

�����肵���������̂��x�����������߂Â��Ă܂���܂����̂ŁA���m�点�������܂��B

���y���E�j�Փ��͓����̊m�F�����Ȃ��ׁA���̊ԂɌ�葱�������������ꍇ�A
����Ⴂ�œ����[���������Ă��܂��܂��B
���̏ꍇ�́A�܂��Ƃɐ\���󂲂����܂��񂪁A�����[�����폜���Ă��������܂��悤
���肢�\���グ�܂��B
�i�X�֋ǂŌ�葱�������������ꍇ�A�m�F�ɍő�4�c�Ɠ�������ꍇ���������܂��̂ŁA
�O����O�X���Ɍ�葱�����������Ă���܂��Ă��A�����悤�ɓ���Ⴂ�œ����[����
�͂��Ă��܂��ꍇ���������܂��B�j

���x���������F{LimitDate}
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���������z�F{UseAmount}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
�������F{IssueDate}
 
�܂����x�������������Ă��Ȃ��ꍇ�́A���Ђ�肨���肢�����܂�����������
���m�F�̂����A��L�������܂łɂ��x�������������܂��悤�A���肢�\���グ�܂��B

��������������߂��Ă��܂��܂��ƁA����Ҍ_��@�Ɋ�Â��x�����Q���y�сA
�Đ����萔�������Z����܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��A�������̊m�F�͎��܂��B
�����ւ��U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B


�y��s�U�������z
�W���p���l�b�g��s�@
���~�W�x�X�@
���ʌ����@0015015
�Z�C�m�[�t�B�i���V�����i�J

�y�X�֐U�֌����z
�����L���F00100-7
�����ԍ��F292043
������ЃL���b�`�{�[���@�Z�C�m�[FC�W

���X�֋ǁ^��s���炨�U���݂��������ꍇ�A�U���萔�������q�l�����S�ƂȂ�܂��B

���X�֋ǂ̌����������̏ꍇ�́A�X�֋ǂ̂`�s�l�𗘗p���Č������炲����������
����΁A�X�֐U���萔���͂�����܂���B(�X�܌��ώ萔���Ƃ͕ʂł��B)

�����ꐿ���������茳�ɓ͂��Ă��Ȃ��ꍇ��A���x���Ɋւ��܂��āA���s���ȓ_��
�������܂�����A���L�܂ł��C�y�ɂ��₢���킹�������܂��B

�����i�ɂ��܂��Ă��A���[���ւȂǂ̔z�����@�̏ꍇ�ɂ́A�z�����̂Ȃǂɂ��
�͂��Ȃ��ꍇ���������܂��B
������A���i���͂��Ă��Ȃ��ꍇ�ɂ͑�ς��萔�ł͂������܂����A���������ꂽ
�X�ܗl�܂Œ��ڂ��⍇�����������܂��B

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�@�{���ɓ���Ă��⍇�����������B

���L�����Z���i���\���j����Ă���ꍇ�ł��A�s���Ⴂ�ɂē����[����
�@�z�M����Ă��܂��ꍇ���������܂��B���̍ۂ͑�ς��萔�ł����A�������ꂽ
�@�X�ܗl�ɒ��ڂ��⍇�����������B

����Ƃ����ЃT�[�r�X�y�㕥�����σT�[�r�X�z����낵�����肢�\���グ�܂��B

--------------------------------------------------------------

�y�㕥�����σT�[�r�X�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`

  ���⍇����F03-6908-7888
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: sfc-atobarai@seino.co.jp

  �^�c��ЁF�Z�C�m�[�t�B�i���V�����������
�@�Z���F��503-8501 �򕌌���_�s�c�����P�Ԓn

--------------------------------------------------------------',3,'2015/08/31 22:42:31',9,'2015/12/01 13:06:36',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (70,15,'�`�[�ԍ��m�F�̂��肢','�㕥�����σT�[�r�X','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'�y�㕥�����σT�[�r�X�z�`�[�ԍ��̂��m�F�����肢���܂�','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','{EnterpriseNameKj}
{CpNameKj} �l

�����b�ɂȂ��Ă���܂��B�y�㕥�����σT�[�r�X�z�J�X�^�}�[�Z���^�[�ł��B

{ReceiptOrderDate}�ɂ������o�^���������܂����A
���L���q�l�̒��׊m�F�����Ă���܂���B

���o�^�����܂����A�z���`�[�ԍ��ɓ��̓~�X�����邩�A
���i�����q�l�ɓ͂��Ă��Ȃ��\�����������܂��B

���i�̔z����ЁA�z���`�[�ԍ��A���тɔz���󋵂�
�l���̌��ˍ������������܂��̂�
���萔�ł�����x�X�ܗl���ł��m�F���������A
�X�ܗl�Ǘ��T�C�g�ォ��C�����Ă������������v���܂��B
���ҏW���@�͗��������������̂��q�l���i�荞��ł��������A
�w�o�^���e�̏C���x����C���������Ȃ��ĉ������B

�����ID �F{OrderId}
�������җl�� �F{CustomerNameKj} �l
�`�[�ԍ��o�^�� �F{Deli_JournalIncDate}
�o�^�`�[�ԍ� �F{Deli_JournalNumber}

���A�����Ԃɓn�育�ύX�������������A�z����Ђ̒ǐՃT�[�r�X�ɂ�
���ׂ̊m�F�����Ȃ��Ȃ��Ă��܂����ꍇ�A���ۏ؈����ƂȂ�܂��̂�
�����ӂ��������B

���s���_�Ȃǂ������܂�����A���� 03-6908-7888 �܂�
���A������������΂Ǝv���܂��B
������낵�����肢�������܂��B


--------------------------------------------------------------

�y�㕥�����σT�[�r�X�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`

  ���⍇����F03-6908-7888
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: sfc-atobarai@seino.co.jp

  �^�c��ЁF�Z�C�m�[�t�B�i���V�����������
�@�Z���F��503-8501 �򕌌���_�s�c�����P�Ԓn

--------------------------------------------------------------',3,'2015/08/31 22:42:31',9,'2015/12/01 13:07:46',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (71,16,'�߂萿���Z���m�F���[��','�㕥�����σT�[�r�X','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'�y�㕥�����σT�[�r�X�z�y�d�v�z���Z���m�F�̘A���ł��B','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','��������������������������������������������������������������������������
�����肢�F���₢���킹�����ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������������

{CustomerNameKj}�l

{ReceiptOrderDate}��{SiteNameKj}�ŁA
�㕥�����σT�[�r�X���ς�I�����Ă����������肪�Ƃ��������܂��B
{ClaimDate}�ɂ����肢�����܂��������������Ђɖ߂��Ă��Ă���܂��̂ŁA
���Z���̊m�F�������Ă��������������A�������Ă��������܂����B

�i���q�l�Z���j�@{UnitingAddress}

��L�Z���ɕs��������܂�����A�ēx�������𔭍s�����Ă��������܂��̂�
���A���̒��A��낵�����肢�v���܂��B

�Z���ɕs�����Ȃ��ꍇ�ł��A�\�D����������Ă����ꍇ�ȂǂŁA�X�֕����͂��Ȃ��P�[�X��
����܂��̂ŁA�������������B

�܂��A��s�A�X�֋ǂ���̂��������\�ł��̂�
�����ԍ��������肳���Ă��������܂��B

�y��s�U�������z
�W���p���l�b�g��s�@
���~�W�x�X�@
���ʌ����@0015015
�Z�C�m�[�t�B�i���V�����i�J

�y�X�֐U�֌����z
�����L���F00100-7
�����ԍ��F292043
������ЃL���b�`�{�[���@�Z�C�m�[FC�W

�y���������ׁz
���i���@�@�F{ItemNameKj}
���i����@�F{ItemAmount}�~
�����@�@�@�F{DeliveryFee}�~
�萔���@�@�F{SettlementFee}�~
{OptionFee}
���v�@�@�@�F{UseAmount}�~

���̑����s���ȓ_�A�������̂����k���͓��Ђ܂ł��₢���킹���������B

--------------------------------------------------------------

�y�㕥�����σT�[�r�X�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`

  ���⍇����F03-6908-7888
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: sfc-atobarai@seino.co.jp

  �^�c��ЁF�Z�C�m�[�t�B�i���V�����������
�@�Z���F��503-8501 �򕌌���_�s�c�����P�Ԓn

--------------------------------------------------------------',3,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (72,17,'���������s���[���i�����c�[�������FPC�j','�㕥�����σT�[�r�X','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'�y�㕥�����σT�[�r�X�z���������s�ē�','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','��������������������������������������������������������������������������
�����肢�F���₢���킹�����ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������������

 {CustomerNameKj}�l

����� {SiteNameKj}�l�ł̂���������
�y�㕥�����σT�[�r�X�z�������p���������܂��āA
�܂��Ƃɂ��肪�Ƃ��������܂��B

���L�̃V���b�s���O�̐�������{�����s�������܂����B
���i�ɓ�������Ă��鐿�����ɋL�ڂ̂��x���������܂ł�
���x�������������܂��悤�A���肢�\���グ�܂��B

���������̔��s���Ə��i�̔������͈قȂ�ꍇ���������܂��B
�@�\�߂��������������܂��B

�y���������e�z
���x���ҁF{CustomerNameKj}�@�l
���w���X�ܖ��F{SiteNameKj}�@
���w�����F{OrderDate}
���x�����z�F{UseAmount}
���w�����i���ׁF���i���^���^�w���i�ڌv
{OrderItems}
���ώ萔��                              \{SettlementFee}
����                                    \{DeliveryFee}

�����i�Ƌ��ɐ������������Ă��Ȃ��ꍇ�ɂ́A��ς��萔�ł����A
�@���L�A����ւ���񂭂������܂��悤�A���肢�\���グ�܂��B

�ڂ����͉��L�p�\�R���pURL�������������B

http://www.seino.co.jp/financial/atobarai/guidance/

���s���ȓ_�Ȃǂ������܂�����A���C�y�ɉ��L�܂ł��⍇���������B

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�@�{���ɓ���Ă��⍇�����������B

���L�����Z���i���\���j����Ă���ꍇ�ł��A�s���Ⴂ�ɂē����[����
�@�z�M����Ă��܂��ꍇ���������܂��B���̍ۂ͑�ς��萔�ł����A���L
�@�w���X�ܗl�ɒ��ڂ��⍇�����������B

�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
���ڍw���X�ܗl�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}

�����x�����Ɋւ��邨�₢���킹�́F
�Z�C�m�[�t�B�i���V����������Ё@�㕥�����σT�[�r�X�S��
TEL:03-6908-7888 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
Mail: sfc-atobarai@seino.co.jp

--------------------------------------------------------------

�y�㕥�����σT�[�r�X�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`

  ���⍇����F03-6908-7888
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: sfc-atobarai@seino.co.jp

  �^�c��ЁF�Z�C�m�[�t�B�i���V�����������
�@�Z���F��503-8501 �򕌌���_�s�c�����P�Ԓn

--------------------------------------------------------------',3,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (73,18,'���������s���[���i�����c�[�������FCEL�j','�㕥�����σT�[�r�X','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'�y�㕥�����σT�[�r�X�z���������s�ē�','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','��������������������������������������������������������������������������
�����肢�F���₢���킹�����ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������������

{CustomerNameKj}�l

����� {SiteNameKj}�l�ł̂���������
�y�㕥�����σT�[�r�X�z�������p���������܂��āA
�܂��Ƃɂ��肪�Ƃ��������܂��B

���L�̃V���b�s���O�̐�������{�����s�������܂����B
���i�ɓ�������Ă��鐿�����ɋL�ڂ̂��x���������܂ł�
���x�������������܂��悤�A���肢�\���グ�܂��B

���������̔��s���Ə��i�̔������͈قȂ�ꍇ���������܂��B
�@�\�߂��������������܂��B

�y���������e�z
���x���ҁF{CustomerNameKj}�@�l
���w���X�ܖ��F{SiteNameKj}�@
���w�����F{OrderDate}
���x�����z�F{UseAmount}
���w�����i���ׁF���i���^���^�w���i�ڌv
{OrderItems}
���ώ萔��  \{SettlementFee}
���� \{DeliveryFee}

�����i�Ƌ��ɐ������������Ă��Ȃ��ꍇ�ɂ́A��ς��萔�ł����A
�@���L�A����ւ���񂭂������܂��悤�A���肢�\���グ�܂��B

�ڂ����͉��L�p�\�R���pURL�������������B

http://www.seino.co.jp/financial/atobarai/guidance/

���s���ȓ_�Ȃǂ������܂�����A���C�y�ɉ��L�܂ł��⍇���������B

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�@�{���ɓ���Ă��⍇�����������B

���L�����Z���i���\���j����Ă���ꍇ�ł��A�s���Ⴂ�ɂē����[����
�@�z�M����Ă��܂��ꍇ���������܂��B���̍ۂ͑�ς��萔�ł����A���L
�@�w���X�ܗl�ɒ��ڂ��⍇�����������B

�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
���ڍw���X�ܗl�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@
�d�b�F{Phone}

�����x�����Ɋւ��邨�₢���킹�́F
�Z�C�m�[�t�B�i���V����������Ё@�㕥�����σT�[�r�X�S��
�Z���F��503-8501 �򕌌���_�s�c�����P�Ԓn
TEL:03-6908-7888 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
Mail: sfc-atobarai@seino.co.jp
URL: http://http://www.seino.co.jp/financial�i�p�\�R����p�j

--------------------------------------------------------------

�y�㕥�����σT�[�r�X�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`

  ���⍇����F03-6908-7888
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: sfc-atobarai@seino.co.jp

  �^�c��ЁF�Z�C�m�[�t�B�i���V�����������
�@�Z���F��503-8501 �򕌌���_�s�c�����P�Ԓn

--------------------------------------------------------------',3,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (74,19,'�^�M���ʃ��[��(OK, PC)','�㕥�����σT�[�r�X','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'�y�㕥�����σT�[�r�X�z�^�M���ʂ̂��m�点','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','{CustomerNameKj}�l

���̓x��{SiteNameKj}��{OneOrderItem}�����������ꂽ�ۂ̂��x�������@�ɁA�㕥�����σT�[�r�X����I�𒸂��܂��āA�܂��Ƃɂ��肪�Ƃ��������܂��B

�㕥�����ς̗^�M�R�������Ȃ��ʉ߂������܂����̂ł��񍐐\���グ�܂��B

�����������܂������i�ɂ��Ă̂��₢���킹�E���������e�̂��ύX�A�������̃L�����Z�����Ɋւ��܂��ẮA{SiteNameKj}�ł̑Ή��ƂȂ�܂��̂ŁA���ڂ��A�����Ē����܂��悤���肢�\���グ�܂��B

�y{SiteNameKj}�z
{ContactPhoneNumber}
{MailAddress}

���A�������̔��s�ɂ��Ă͕��Ђ�胁�[����������v���܂��̂�
���m�F���������B

�㕥�����ςɊւ��Ă��s���ȓ_�Ȃǂ������܂�����A���L�̌㕥�����σT�[�r�X�J�X�^�}�[�Z���^�[�ւ��₢���킹���������B

�y�㕥�����σT�[�r�X�J�X�^�}�[�Z���^�[�z
�^�c��ЁF�Z�C�m�[�t�B�i���V�����������
TEL:03-6908-7888',3,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (75,20,'�^�M���ʃ��[��(OK, CEL)','�㕥�����σT�[�r�X','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'�y�㕥�����σT�[�r�X�z�^�M���ʂ̂��m�点','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','{CustomerNameKj}�l

���̓x��{SiteNameKj}��{OneOrderItem}�����������ꂽ�ۂ̂��x�������@�ɁA�㕥�����σT�[�r�X����I�𒸂��܂��āA�܂��Ƃɂ��肪�Ƃ��������܂��B

�㕥�����ς̗^�M�R�������Ȃ��ʉ߂������܂����̂ł��񍐐\���グ�܂��B

�����������܂������i�ɂ��Ă̂��₢���킹�E���������e�̂��ύX�A�������̃L�����Z�����Ɋւ��܂��ẮA{SiteNameKj}�ł̑Ή��ƂȂ�܂��̂ŁA���ڂ��A�����Ē����܂��悤���肢�\���グ�܂��B

�y{SiteNameKj}�z
{ContactPhoneNumber}
{MailAddress}

���A�������̔��s�ɂ��Ă͕��Ђ�胁�[����������v���܂��̂�
���m�F���������B

�㕥�����ςɊւ��Ă��s���ȓ_�Ȃǂ������܂�����A���L�̌㕥�����σT�[�r�X�J�X�^�}�[�Z���^�[�ւ��₢���킹���������B

�y�㕥�����σT�[�r�X�J�X�^�}�[�Z���^�[�z
�^�c��ЁF�Z�C�m�[�t�B�i���V�����������
TEL:03-6908-7888',3,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (76,21,'�^�M���ʃ��[��(NG, PC)','�㕥�����σT�[�r�X','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'�y�㕥�����σT�[�r�X�z�^�M���ʂ̂��m�点','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','{CustomerNameKj}�l

���̓x��{SiteNameKj}��{OneOrderItem}�����������ꂽ�ۂ̂��x�������@�ɁA
�㕥�����σT�[�r�X����I�𒸂��܂��āA�܂��Ƃɂ��肪�Ƃ��������܂��B

�^�M�R���̌��ʁA����̌䒍���ɂ��܂��āA
�㕥�����ς̗^�M�R�����ʉ߂������܂���ł����������񍐐\���グ�܂��B

��ς��萔�ł͂������܂����A{SiteNameKj}�ւ��A���̏�A
���̂��x�������@�ɂ��ύX���������Ƒ����܂��B

�y{SiteNameKj}�z
{ContactPhoneNumber}
{MailAddress}

�����������܂������i�ɂ��Ă̂��₢���킹�E���������e�̂��ύX�A
�������̃L�����Z�����Ɋւ��܂��Ă��A{SiteNameKj}�ł̑Ή��ƂȂ�܂��̂ŁA
���ڂ��A�����Ē����܂��悤���肢�\���グ�܂��B

���x�������@�����ύX�������ꍇ�A
�㕥�����ςɊւ���萔���͈�ؔ����������܂���B

�㕥�����ς̗^�M�R���ɂ��܂��ẮA
�㕥�����σT�[�r�X���^�c���Ă���܂�
�Z�C�m�[�t�B�i���V����������Ђɂčs���Ă���܂��B
�^�M�R�����ʂ̗��R�Ȃǂɂ��܂��ẮA
�l�����܂ޓ��e�ɂȂ�ׁA���Ђ���{SiteNameKj}�ւ͈�؊J���������Ă���܂���B
���܂��ẮA�^�M�R�����ʂ̗��R�Ȃǂ̂��₢���킹�Ɋւ��܂��ẮA
���ځA���d�b�ɂē��Ђ֌�A�������܂��悤���肢�\���グ�܂��B

�y�㕥�����σT�[�r�X�J�X�^�}�[�Z���^�[�z
�^�c��ЁF�Z�C�m�[�t�B�i���V�����������
TEL:03-6908-7888
�c�Ǝ���:10:00�`18:00�@�N�����x(�N���E�N�n�̂���)

�^�M�R���Ɋւ��܂��ẮA���{�l�l��育�A�������܂��ƍĐR�����\�ł������܂��B
�㕥���̐R�������Ƃ������܂��āA���{�l�l�m�F�����邱�Ƃ��K�{�ƂȂ��Ă���܂��B
���̂��߁A���Z���₨�d�b�ԍ��̕s���A
�������͂��m�荇���₲�e�ʂ̕��Ƃ������ŁA
���Z���₨�d�b�ԍ��̂����`���������җl�ƈقȂ�ꍇ(�c�����Ⴄ�ꍇ)�ȂǁA
�^�M�R�����ʉ߂������܂���B
�@�l�p�E�X�ܗp���l���`�ɂĂ��������ꂽ�ꍇ�����l�ł������܂��B

�������S�����肪����C�����\�ȏꍇ�ɂ́A�ēx�^�M�R�����������܂��̂ŁA
��L�̌㕥�����σT�[�r�X�J�X�^�}�[�Z���^�[�܂Ō�A�����������܂��悤�A
���肢�\���グ�܂��B

���萔���������������܂��āA�܂��Ƃɋ��k�ł͂������܂����A
���Ή��̒���낵�����肢�\���グ�܂��B
',3,'2015/08/31 22:42:31',9,'2015/12/01 13:17:24',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (77,22,'�^�M���ʃ��[��(NG, CEL)','�㕥�����σT�[�r�X','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'�y�㕥�����σT�[�r�X�z�^�M���ʂ̂��m�点','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','{CustomerNameKj}�l

���̓x��{SiteNameKj}��{OneOrderItem}�����������ꂽ�ۂ̂��x�������@�ɁA
�㕥�����σT�[�r�X����I�𒸂��܂��āA�܂��Ƃɂ��肪�Ƃ��������܂��B

�^�M�R���̌��ʁA����̌䒍���ɂ��܂��āA
�㕥�����ς̗^�M�R�����ʉ߂������܂���ł����������񍐐\���グ�܂��B

��ς��萔�ł͂������܂����A{SiteNameKj}�ւ��A���̏�A
���̂��x�������@�ɂ��ύX���������Ƒ����܂��B

�y{SiteNameKj}�z
{ContactPhoneNumber}
{MailAddress}

�����������܂������i�ɂ��Ă̂��₢���킹�E���������e�̂��ύX�A
�������̃L�����Z�����Ɋւ��܂��Ă��A{SiteNameKj}�ł̑Ή��ƂȂ�܂��̂ŁA
���ڂ��A�����Ē����܂��悤���肢�\���グ�܂��B

���x�������@�����ύX�������ꍇ�A�㕥�����ςɊւ���萔���͈�ؔ����������܂���B

�㕥�����ς̗^�M�R���ɂ��܂��ẮA
�㕥�����σT�[�r�X���^�c���Ă���܂�
�Z�C�m�[�t�B�i���V����������Ђɂčs���Ă���܂��B
�^�M�R�����ʂ̗��R�Ȃǂɂ��܂��ẮA�l�����܂ޓ��e�ɂȂ�ׁA
���Ђ���{SiteNameKj}�ւ͈�؊J���������Ă���܂���B
���܂��ẮA�^�M�R�����ʂ̗��R�Ȃǂ̂��₢���킹�Ɋւ��܂��ẮA
���ځA���d�b�ɂē��Ђ֌�A�������܂��悤���肢�\���グ�܂��B

�y�㕥�����σT�[�r�X�J�X�^�}�[�Z���^�[�z
�^�c��ЁF�Z�C�m�[�t�B�i���V�����������
TEL:03-6908-7888
�c�Ǝ���:10:00�`18:00�@�N�����x(�N���E�N�n�̂���)

�^�M�R���Ɋւ��܂��ẮA���{�l�l��育�A�������܂��ƍĐR�����\�ł������܂��B
�㕥���̐R�������Ƃ������܂��āA���{�l�l�m�F�����邱�Ƃ��K�{�ƂȂ��Ă���܂��B
���̂��߁A���Z���₨�d�b�ԍ��̕s���A
�������͂��m�荇���₲�e�ʂ̕��Ƃ������ŁA
���Z���₨�d�b�ԍ��̂����`���������җl�ƈقȂ�ꍇ(�c�����Ⴄ�ꍇ)�ȂǁA
�^�M�R�����ʉ߂������܂���B
�@�l�p�E�X�ܗp���l���`�ɂĂ��������ꂽ�ꍇ�����l�ł������܂��B

�������S�����肪����C�����\�ȏꍇ�ɂ́A�ēx�^�M�R�����������܂��̂ŁA
��L�̌㕥�����σT�[�r�X�J�X�^�}�[�Z���^�[�܂Ō�A�����������܂��悤
�A���肢�\���グ�܂��B

���萔���������������܂��āA�܂��Ƃɋ��k�ł͂������܂����A
���Ή��̒���낵�����肢�\���グ�܂��B',3,'2015/08/31 22:42:31',9,'2015/12/01 13:18:12',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (78,23,'�p�X���[�h��񂨒m�点���[��','�㕥�����σT�[�r�X','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'�y�㕥�����σT�[�r�X�z �p�X���[�h���̂��m�点','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','{EnterpriseNameKj}�@�l

���̓x�͕��ЃT�[�r�X�A�y�㕥�����σT�[�r�X�z�ɂ��\�����������A
�܂��Ƃɂ��肪�Ƃ��������܂��B

�㕥�����ϊǗ��V�X�e���Ƀ��O�C�����Ă��������ׂɕK�v��
�p�X���[�h�������肳���Ă��������܂��B

PW :{GeneratedPassword}

�T�[�r�X�J�n�ɓ������āA�܂��A�^�c�Ɋւ��邨�₢���킹���́A
���[�������̂��A����ɂ��C�y�ɂ��⍇���������B


����Ƃ����i�����t�������̒��A�X�������肢�\���グ�܂��B

�Z�C�m�[�t�B�i���V����������Ё@�㕥�����σT�[�r�X�S���@�X�^�b�t�ꓯ

--------------------------------------------------------------

�y�㕥�����σT�[�r�X�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`

  ���⍇����F03-6908-7888
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: sfc-atobarai@seino.co.jp

  �^�c��ЁF�Z�C�m�[�t�B�i���V�����������
�@�Z���F��503-8501 �򕌌���_�s�c�����P�Ԓn

--------------------------------------------------------------',3,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (79,2,'�����o�^�i�^�M�J�n�j���[��','','','',null,null,null,'','','',1,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (80,30,'�L�����Z���\�����[��','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z�L�����Z���\��������t�������܂���','{ServiceName}','{EnterpriseNameKj}�@�l

�����y{ServiceName}�z�������p���������܂��āA�܂��Ƃɂ��肪�Ƃ��������܂��B

�ȉ��̂������̃L�����Z���\���̂���t�������܂����B

�y�Ǘ���ʂt�q�k�z
https://www.atobarai.jp/member/

�y�L�����Z����t���z
������ID�F{OrderId}
�����掁���F{CustomerNameKj}�@�l
���������z�F{UseAmount}
���������F{OrderDate}

���Ђł̊m�F��A�ēx���F�A���������Ă��������܂��B

���s���ȓ_�Ȃǂ������܂�����A���C�y�ɓ��Ђ܂ł��₢���킹�������B

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',0,'2015/09/17 15:27:09',9,'2022/04/20 2:12:09',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (81,31,'�L�����Z���\��������[��','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z�L�����Z���\�����������t�������܂���','{ServiceName}','{EnterpriseNameKj}�@�l

�����y{ServiceName}�z�������p���������܂��āA�܂��Ƃɂ��肪�Ƃ��������܂��B

�ȉ��̂������̃L�����Z���\���̎�����������܂����̂ŁA���m�F�������B

�y�Ǘ���ʂt�q�k�z
https://www.atobarai.jp/member/

�y�L�����Z��������z
������ID�F{OrderId}
�����掁���F{CustomerNameKj}�@�l
���������z�F{UseAmount}
���������F{OrderDate}
�L�����Z���\�����F{CancelDate} 

���s���ȓ_�Ȃǂ������܂�����A���C�y�ɓ��Ђ܂ł��₢���킹�������B

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',0,'2015/09/17 15:27:09',9,'2022/04/20 5:55:56',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (116,1,'���Ǝғo�^�����i�T�[�r�X�J�n�j���[��','������Ђd�X�g�A�[�i�㕥�������j','=?UTF-8?B?GyRCM3Q8MDJxPFIjRSU5JUglIiE8IUo4ZUonJCRBazh9IUsbKEI=?=','ato-barai.sp@estore.co.jp',null,null,null,'�e�X�g','=?UTF-8?B?5qCq5byP5Lya56S+77yl44K544OI44Ki44O877yI5b6M5omV44GE56qT5Y+j?=
 =?UTF-8?B?77yJ?=','{EnterpriseNameKj}�@�l

���̓x�͕��ЃT�[�r�X�A�y�㕥���h�b�g�R���z�ɂ��\�����������A
�܂��Ƃɂ��肪�Ƃ��������܂��B

�R���̌��ʁA�ʉ߂ƂȂ�܂����̂ŁA�㕥�����ϊǗ��V�X�e����
�����p���������̂ɕK�v��ID��񍐐\���グ�܂��B

�d�v�Ȃ��ē��ƂȂ�܂��̂ŁA�Ō�܂ł��ǂ݂��������B

�y�Ǘ��T�C�g�t�q�k�z
https://www.atobarai.jp/member/




ID : {LoginId}




���p�X���[�h�͕ʓr���[���ɂĂ����肳���Ă��������܂��B
���T�C�g�h�c�͏�L�h�c�Ƃ͈قȂ�܂��̂ł����ӂ��������B
�T�C�g�h�c�̎Q�ƕ��@�͈ȉ��̒ʂ�ł��B

�y1�z�Ǘ��T�C�g�Ƀ��O�C��
�@�@���@���@���@��
�y2�z�u�o�^���Ǘ��v���N���b�N
�@�@���@���@���@��
�y3�z�u�T�C�g���v���N���b�N
�@�@���@���@���@��
�y4�z�u�T�C�g�h�c�v���ɕ\������܂��B

 ���}�j���A���̃_�E�����[�h�i�K�{�j
���L��URL���A�y�㕥���h�b�g�R���z�̉^�p�}�j���A�����_�E�����[�h
���Ă��g�p�������B
�T�[�r�X�J�n�ɕK�v�ȃ}�j���A���ƂȂ��Ă���܂��̂ŁA�K�����m�F
���������܂��悤���肢�\���グ�܂��B

 https://www.atobarai.jp/doc/help/Atobarai.com_Manual.pdf

���{���ɂ�Adobe PDF Reader ���K�v�ł��B�C���X�g�[������Ă��Ȃ�
���́A���L��URL��蓯�\�t�g�̃C���X�g�[�������肢�������܂��B

  http://www.adobe.com/jp/products/acrobat/readstep2.html

�Ǘ��V�X�e���̂����p���@�́A�_�E�����[�h���Ă����������}�j���A��
�����m�F���������B

�T�[�r�X�̊J�n�܂ŁA�X�ܗl�ɂ͈ȉ��̂悤�ȍ�Ƃ����Ă��������܂��B
�J�n�̂��A�������Y��Ȃ��悤�A���肢�\���グ�܂��B

�������@STEP 1�@�������o�^���e�̂��m�F

�Ǘ��T�C�g�Ƀ��O�C���A�X�܏����m�F�i�v�������̑��̏��j

�������@STEP 2�@��������^���͂̃T�C�g�f��

�}�j���A���ɂ��������āA�X�ܗl�T�C�g��ɓ����ϕ��@�p�̒�^���͂��f��
�i���菤����@�y�[�W�⌈�ϑI����ʂȂǁj

�T�C�g�f�ڗp��^���E�摜�񋟃y�[�W�F

http://www.ato-barai.com/for_shops/tokuteishou.html

�����̎��_�ŃT�[�r�X�J�n�ƂȂ�܂�

������җl�����㕥���h�b�g�R�����恕�̑��o�i�[�_�E�����[�h�y�[�W
http://www.ato-barai.com/download/

����җl��������́A���߂ē����ς������p�ɂȂ����җl�ɂƂ���
������Ղ��Ȃ�A���⍇�������点����ʂ����҂ł��܂��B
����ɔ̑��o�i�[�́A�㕥�����ς��o���邨�X�Ƃ��ăA�s�[���ł��邽�߁A
�̑��̌��ʂɂ��Ȃ���܂��̂ŁA������������Ă����p���������B

�������@STEP 3�@�������T�[�r�X�J�n�̓��Ђւ̂��ʒm

�T�[�r�X���J�n�����|���A���Ђ܂Ń��[���������͂��d�b�ɂĂ��A���������B
 mail: customer@ato-barai.com
 tel:  0120-667-690

�������@STEP 4�@���������Ђ����ω�ʂ��m�F

���ВS�������ω�ʂ��m�F�����Ă��������A��肪�Ȃ���΂��̂܂܉^�c�A
��肪����ΏC���̂��肢�������Ă����������Ƃ��������܂��B

  �������u����v�͂����܂�

������җl�ւ̐������̂��ē��p���̃_�E�����[�h�i�C�Ӂj
���L�̂t�q�k��萿�����̂��ē��p�����_�E�����[�h���āA���i�ɓ���
���Ă��������B
�i���ē��p���̓����͓X�ܗl�̂����f�ɂ��C�ӂōs���Ă���������
�@����܂����A���߂ē����ς������p�Ȃ����җl�ɂƂ��Ă͕�����Ղ�
�@�Ȃ�A���⍇�������邱�Ƃɂ��q����܂��̂ŁA�������Ă�����������
�@�𐄏����Ă���܂��B�j

https://www.atobarai.jp/doc/download/doukonnyou.xls


�T�[�r�X�J�n�ɓ������āA�܂��A�^�c�Ɋւ��邨�₢���킹���́A
���[�������̂��A����ɂ��C�y�ɂ��⍇���������B

�������������������������y���ӎ����z������������������������

�P�j�ȉ��ɊY�����邲�����́A�ۏؑΏۊO�ƂȂ��Ă��܂��܂��̂�
�@�@�����ӂ��������B

���ۏ؊O�Ƃ́A�������̕ۏ؂��t�����A����җl����̓�����
�@�Ȃ�����͓X�ܗl�֓��������Ă������������ł��܂���B
�@
�EWeb��ɂĂ��ו��̒ǐՂ��ł��Ȃ��z�����@���g��ꂽ������
�E�`�[�o�^���ɔz����Ђ�`�[�ԍ�����������œo�^���ꂽ������
�E�z�B�󋵂��L�����Z���E�����߂蓙�ɂ��z�B�����̊m�F��
�@�Ƃ�Ȃ�������
�E���ۂɔ������ꂽ�z�����@�Ɋւ�炸�A�`�[�o�^���̔z�����@��
�@�y���[���ցz��I�����ēo�^���ꂽ������
�E�����������邲����

�Q�j�z���`�[�ԍ������o�^�����������A�������́A���c�Ɠ���
�@�@�������җl�ɑ΂��āA������������������܂��B
�����i�����O�ɔz���`�[�ԍ������o�^���������܂��ƁA�����������i
�@����ɓ͂��Ă��܂��\���������Ȃ�܂��̂ŁA���i�������
�@�z���`�[�ԍ��̂��o�^�����肢�������܂��B

�R�j�����܂łɕ��Б��ŏ��i�̒��׊m�F���Ƃꂽ����������
�@�@���Y�������̗��֑ΏۂƂȂ�܂��B
���`�[�ԍ��o�^����A�z�B�������ł͂Ȃ��A���Б��Œ��׊m�F��
�@�Ƃꂽ�����x�[�X�ƂȂ�܂��̂ł����ӂ��������B

������������������������������������������������������������


����Ƃ����i�����t�������̒��A�X�������肢�\���グ�܂��B

������ЃL���b�`�{�[���@�㕥���h�b�g�R�����ƕ��@�X�^�b�t�ꓯ

--------------------------------------------------------------

�y�㕥���h�b�g�R���z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`

  ���⍇����F0120-667-690
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com

  �^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F

--------------------------------------------------------------',1,'2015/10/01 13:24:18',8394,'2016/01/19 18:28:25',43,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (188,32,'�������j�����[���iPC�j','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z�������j���̂��肢','{ServiceName}','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

{CustomerNameKj}�l

���̓x��{SiteNameKj}�ŏ��i���w���̍ۂɁA
�y{ServiceName}�z�������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂����B

{CancelDate}��{SiteNameKj}���y{ServiceName}�z��
�L�����Z���̂��A�������������܂������A
���ɐ������������肵�Ă��܂��Ă���܂��̂ŁA
��ς��萔�ł͂������܂����j�����Ă��������悤���肢�������܂��B

�s����s���_�Ȃǂ������܂�����A
���C�y�ɂ��⍇�����������܂��B

���̓x��{SiteNameKj}�Ɓy{ServiceName}�z�������p��������
�܂��Ƃɂ��肪�Ƃ��������܂����B

����Ƃ������A��낵�����肢�������܂��B

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',0,'2015/07/23 15:27:30',9,'2022/04/20 5:56:46',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (189,33,'�������j�����[���iCEL�j','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z�������j���̂��肢','{ServiceName}','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

{CustomerNameKj}�l

���̓x��{SiteNameKj}�ŏ��i���w���̍ۂɁA
�y{ServiceName}�z�������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂����B

{CancelDate}��{SiteNameKj}���y{ServiceName}�z��
�L�����Z���̂��A�������������܂������A
���ɐ������������肵�Ă��܂��Ă���܂��̂ŁA
��ς��萔�ł͂������܂����j�����Ă��������悤���肢�������܂��B

�s����s���_�Ȃǂ������܂�����A
���C�y�ɂ��⍇�����������܂��B

���̓x��{SiteNameKj}�Ɓy{ServiceName}�z�������p��������
�܂��Ƃɂ��肪�Ƃ��������܂����B

����Ƃ������A��낵�����肢�������܂��B

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',0,'2015/07/23 15:27:30',9,'2022/04/20 5:56:52',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (190,34,'�ߏ�������[���iPC�j','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z�ߏ�����̂��A��{OrderId} ','{ServiceName}','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

{CustomerNameKj}�l

���̓x��{SiteNameKj}�l�ŏ��i���w���̍ۂɁA
�y{ServiceName}�z�������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂����B


{ReceiptDate}��{ReceiptClass}��育�������m�F�������܂������A
{OverReceiptAmount}�~�������x�������������Ă���܂����̂�
���ԋ������Ă������������A���A�����������܂����B

������������A�ԋ��̂��ē��n�K�L�ɂ�
���q�l���g�ŕԋ��̂��葱�������肢�������܂��B

���ԋ��n�K�L�̔����ɂ��Ă͍������{�A�������͌�����
�w�y{ServiceName}�z���ԋ��̂��A���x�̌����̃��[���ł��ē��������܂��B

�܂��A�����[���Ɍ�������ԐM����������΁A
���ЂɂĐU���̂��葱���������Ă������������\�ł������܂��B


���萔�ł͂������܂����A���L�����L���̂����A�����[���ւ��ԐM���������B
�E��s���F
�E�x�X���F
�E������ځF
�E�����ԍ��F
�E�������`(�J�i)�F

�����L���������������e�ɕs�����������܂��ƕԋ�������
���������˂܂��̂ł����ӂ��������B

�Ȃ��A���ԋ��̍ۂ̎萔��330�~�͂��q�l���S�ɂȂ�|�A
����������܂��悤���肢�������܂��B

�s���_�Ȃǂ������܂�����A���C�y�ɂ��⍇�����������B

������낵�����肢�������܂��B

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',0,'2015/07/23 15:27:30',9,'2022/04/20 2:20:06',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (191,35,'�ߏ�������[���iCEL�j','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z�ߏ�����̂��A��{OrderId} ','{ServiceName}','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

{CustomerNameKj}�l

���̓x��{SiteNameKj}�l�ŏ��i���w���̍ۂɁA
�y{ServiceName}�z�������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂����B


{ReceiptDate}��{ReceiptClass}��育�������m�F�������܂������A
{OverReceiptAmount}�~�������x�������������Ă���܂����̂�
���ԋ������Ă������������A���A�����������܂����B

������������A�ԋ��̂��ē��n�K�L�ɂ�
���q�l���g�ŕԋ��̂��葱�������肢�������܂��B

���ԋ��n�K�L�̔����ɂ��Ă͍������{�A�������͌�����
�w�y{ServiceName}�z���ԋ��̂��A���x�̌����̃��[���ł��ē��������܂��B

�܂��A�����[���Ɍ�������ԐM����������΁A
���ЂɂĐU���̂��葱���������Ă������������\�ł������܂��B


���萔�ł͂������܂����A���L�����L���̂����A�����[���ւ��ԐM���������B
�E��s���F
�E�x�X���F
�E������ځF
�E�����ԍ��F
�E�������`(�J�i)�F

�����L���������������e�ɕs�����������܂��ƕԋ�������
���������˂܂��̂ł����ӂ��������B

�Ȃ��A���ԋ��̍ۂ̎萔��330�~�͂��q�l���S�ɂȂ�|�A
����������܂��悤���肢�������܂��B

�s���_�Ȃǂ������܂�����A���C�y�ɂ��⍇�����������B

������낵�����肢�������܂��B

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',0,'2015/07/23 15:27:30',9,'2022/04/20 2:20:00',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (192,38,'�����C���������[��','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z���������e�̏C��������܂����i{OrderCount}���j','{ServiceName}','{EnterpriseNameKj}�l

�����y{ServiceName}�z�������p���������A
�܂��Ƃɂ��肪�Ƃ��������܂��B

�ȉ��̂��������e�̏C�����󂯕t���������܂����B


�C�����������F{OrderCount}��

�������Җ��F{OrderSummary}


�y�Ǘ���ʂt�q�k�z
https://www.atobarai.jp/member/

���s���ȓ_�Ȃǂ������܂�����A���C�y�ɂ��₢���킹���������܂��B

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',0,'2015/07/23 15:27:30',9,'2022/04/20 2:20:22',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (193,39,'�x���������߃��[���i�ĂP�j�iPC�j','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z{OrderDate}{SiteNameKj}�ł̂��������̌�({OrderId})','{ServiceName}','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɂ������̂��葱�������ς̂悤�ł����
�@�����[���ւ̕ԐM�͂��s�v�ł������܂��B


{CustomerNameKj}�l

���̓x��{SiteNameKj}�ŏ��i���w���̍ۂɁA
�y{ServiceName}�z�������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂��B

���x�����������߂��Ă��������̊m�F�����Ă���܂���ł����̂�
�Đ������𔭍s�E�����������܂����B

����T�Ԃقǂ��҂����������Ă����������͂��Ȃ��ꍇ��
��ς��萔�ł����A03-4326-3600�ɂ���񂭂������B

�y���������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
���������z�F{TotalAmount}

�����x���������߂��Ă��܂��A�ēx�����������s����܂���
�Đ����萔�������Z�����ꍇ���������܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ł������܂�)
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z
{Bk_BankName}�@{Bk_BranchName}
���ʌ����@{Bk_AccountNumber}
{Bk_AccountHolderKn}
�����������ƂɌ����ԍ����قȂ��Ă���܂��B
����x�����������܂��ƍēx���������󂯕t���邱�Ƃ�
�@�ł��܂���̂ł����ӂ��������B

�y�X�֐U�֌����z
�����L���F00120�]7
�����ԍ��F670031
�J�j�L���b�`�{�[��

���̑��A���x���Ɋւ��Ă��s���ȓ_�͉��L�t�q�k�����m�F���������B
https://atobarai-user.jp/

�����i�̕ԕi�E�����ȂǏ��i�Ɋւ��邨�₢���킹�́F
���ڍw���X�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@
�d�b�F{Phone}

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',0,'2015/07/23 15:27:30',9,'2022/04/20 5:57:54',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (194,40,'�x���������߃��[���i�ĂP�j�iCEL�j','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z{OrderDate}{SiteNameKj}�ł̂��������̌�({OrderId})','{ServiceName}','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɂ������̂��葱�������ς̂悤�ł����
�@�����[���ւ̕ԐM�͂��s�v�ł������܂��B


{CustomerNameKj}�l

���̓x��{SiteNameKj}�ŏ��i���w���̍ۂɁA
�y{ServiceName}�z�������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂��B

���x�����������߂��Ă��������̊m�F�����Ă���܂���ł����̂�
�Đ������𔭍s�E�����������܂����B

����T�Ԃقǂ��҂����������Ă����������͂��Ȃ��ꍇ��
��ς��萔�ł����A03-4326-3600�ɂ���񂭂������B

�y���������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
���������z�F{TotalAmount}

�����x���������߂��Ă��܂��A�ēx�����������s����܂���
�Đ����萔�������Z�����ꍇ���������܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ł������܂�)
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z
{Bk_BankName}�@{Bk_BranchName}
���ʌ����@{Bk_AccountNumber}
{Bk_AccountHolderKn}
�����������ƂɌ����ԍ����قȂ��Ă���܂��B
����x�����������܂��ƍēx���������󂯕t���邱�Ƃ�
�@�ł��܂���̂ł����ӂ��������B

�y�X�֐U�֌����z
�����L���F00120�]7
�����ԍ��F670031
�J�j�L���b�`�{�[��

���̑��A���x���Ɋւ��Ă��s���ȓ_�͉��L�t�q�k�����m�F���������B
https://atobarai-user.jp/

�����i�̕ԕi�E�����ȂǏ��i�Ɋւ��邨�₢���킹�́F
���ڍw���X�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@
�d�b�F{Phone}

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',0,'2015/07/23 15:27:30',9,'2022/04/20 5:58:01',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (195,41,'�x���������߃��[���i�ĂR�j�iPC�j','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z{OrderDate}{SiteNameKj}�ł̂��������̌�({OrderId})','{ServiceName}','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɂ������̂��葱�������ς̂悤�ł����
�@�����[���ւ̕ԐM�͂��s�v�ł������܂��B


{CustomerNameKj}�l

���̓x��{SiteNameKj}�ŏ��i���w���̍ۂɁA
�y{ServiceName}�z�������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂��B

���x�����������߂��Ă��������̊m�F�����Ă���܂���ł����̂�
�Đ������𔭍s�E�����������܂����B
���茳�ɓ͂�����A�����܂łɂ��x���������肢�������܂��B

����T�Ԃقǂ��҂����������Ă����������͂��Ȃ��ꍇ��
��ς��萔�ł����A03-4326-3600�ɂ���񂭂������B


�y���������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
�Đ����ǉ��萔���F{ReClaimFee}
���������z�F{TotalAmount}

�����x���������߂��Ă��܂��A�ēx�����������s����܂���
�Đ����萔�������Z�����ꍇ���������܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ł������܂�)
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z
{Bk_BankName}�@{Bk_BranchName}
���ʌ����@{Bk_AccountNumber}
{Bk_AccountHolderKn}
�����������ƂɌ����ԍ����قȂ��Ă���܂��B
����x�����������܂��ƍēx���������󂯕t���邱�Ƃ�
�@�ł��܂���̂ł����ӂ��������B

�y�X�֐U�֌����z
�����L���F00120�]7
�����ԍ��F670031
�J�j�L���b�`�{�[��

���̑��A���x���Ɋւ��Ă��s���ȓ_�͉��L�t�q�k�����m�F���������B
https://atobarai-user.jp/

�����i�̕ԕi�E�����ȂǏ��i�Ɋւ��邨�₢���킹�́F
���ڍw���X�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@
�d�b�F{Phone}

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',0,'2015/07/23 15:27:30',9,'2022/04/20 5:58:23',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (196,42,'�x���������߃��[���i�ĂR�j�iCEL�j','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z{OrderDate}{SiteNameKj}�ł̂��������̌�({OrderId})','{ServiceName}','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɂ������̂��葱�������ς̂悤�ł����
�@�����[���ւ̕ԐM�͂��s�v�ł������܂��B


{CustomerNameKj}�l

���̓x��{SiteNameKj}�ŏ��i���w���̍ۂɁA
�y{ServiceName}�z�������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂��B

���x�����������߂��Ă��������̊m�F�����Ă���܂���ł����̂�
�Đ������𔭍s�E�����������܂����B
���茳�ɓ͂�����A�����܂łɂ��x���������肢�������܂��B

����T�Ԃقǂ��҂����������Ă����������͂��Ȃ��ꍇ��
��ς��萔�ł����A03-4326-3600�ɂ���񂭂������B


�y���������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
�Đ����ǉ��萔���F{ReClaimFee}
���������z�F{TotalAmount}

�����x���������߂��Ă��܂��A�ēx�����������s����܂���
�Đ����萔�������Z�����ꍇ���������܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ł������܂�)
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z
{Bk_BankName}�@{Bk_BranchName}
���ʌ����@{Bk_AccountNumber}
{Bk_AccountHolderKn}
�����������ƂɌ����ԍ����قȂ��Ă���܂��B
����x�����������܂��ƍēx���������󂯕t���邱�Ƃ�
�@�ł��܂���̂ł����ӂ��������B

�y�X�֐U�֌����z
�����L���F00120�]7
�����ԍ��F670031
�J�j�L���b�`�{�[��

���̑��A���x���Ɋւ��Ă��s���ȓ_�͉��L�t�q�k�����m�F���������B
https://atobarai-user.jp/

�����i�̕ԕi�E�����ȂǏ��i�Ɋւ��邨�₢���킹�́F
���ڍw���X�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@
�d�b�F{Phone}

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',0,'2015/07/23 15:27:30',9,'2022/04/20 5:58:29',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (197,43,'�x���������߃��[���i�ĂS�j�iPC�j','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z{OrderDate}{SiteNameKj}�ł̂��������̌�({OrderId})','{ServiceName}','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɂ������̂��葱�������ς̂悤�ł����
�@�����[���ւ̕ԐM�͂��s�v�ł������܂��B


{CustomerNameKj}�l

���̓x��{SiteNameKj}�ŏ��i���w���̍ۂɁA
�y{ServiceName}�z�������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ClaimDate}�ɂ����肵���������̂��x�����������߂��Ă�
�������̊m�F�����Ă���܂���ł����̂�
�Đ������𔭍s�E�����������܂����B

���[���̂��x�����ɂ��A���茳�ɓ͂�����
���}���Ή����������܂��悤���肢�������܂��B

����T�Ԃقǂ��҂����������Ă����������͂��Ȃ��ꍇ��
��ς��萔�ł����A03-4326-3600�ɂ���񂭂������B

�y���������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
�Đ����ǉ��萔���F{ReClaimFee}
���������z�F{TotalAmount}

�����x���������߂��Ă��܂��A�ēx�����������s����܂���
�Đ����萔�������Z�����ꍇ���������܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ł������܂�)
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z
{Bk_BankName}�@{Bk_BranchName}
���ʌ����@{Bk_AccountNumber}
{Bk_AccountHolderKn}
�����������ƂɌ����ԍ����قȂ��Ă���܂��B
����x�����������܂��ƍēx���������󂯕t���邱�Ƃ�
�@�ł��܂���̂ł����ӂ��������B

�y�X�֐U�֌����z
�����L���F00120�]7
�����ԍ��F670031
�J�j�L���b�`�{�[��

���̑��A���x���Ɋւ��Ă��s���ȓ_�͉��L�t�q�k�����m�F���������B
https://atobarai-user.jp/

�����i�̕ԕi�E�����ȂǏ��i�Ɋւ��邨�₢���킹�́F
���ڍw���X�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@
�d�b�F{Phone}

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',0,'2015/07/23 15:27:30',9,'2022/04/20 5:58:50',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (198,44,'�x���������߃��[���i�ĂS�j�iCEL�j','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y�㕥���h�b�g�R���z{OrderDate}{SiteNameKj}�ł̂��������̌�({OrderId})','{ServiceName}','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɂ������̂��葱�������ς̂悤�ł����
�@�����[���ւ̕ԐM�͂��s�v�ł������܂��B


{CustomerNameKj}�l

���̓x��{SiteNameKj}�ŏ��i���w���̍ۂɁA
�y{ServiceName}�z�������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ClaimDate}�ɂ����肵���������̂��x�����������߂��Ă�
�������̊m�F�����Ă���܂���ł����̂�
�Đ������𔭍s�E�����������܂����B

���[���̂��x�����ɂ��A���茳�ɓ͂�����
���}���Ή����������܂��悤���肢�������܂��B

����T�Ԃقǂ��҂����������Ă����������͂��Ȃ��ꍇ��
��ς��萔�ł����A03-4326-3600�ɂ���񂭂������B

�y���������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
�Đ����ǉ��萔���F{ReClaimFee}
���������z�F{TotalAmount}

�����x���������߂��Ă��܂��A�ēx�����������s����܂���
�Đ����萔�������Z�����ꍇ���������܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ł������܂�)
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z
{Bk_BankName}�@{Bk_BranchName}
���ʌ����@{Bk_AccountNumber}
{Bk_AccountHolderKn}
�����������ƂɌ����ԍ����قȂ��Ă���܂��B
����x�����������܂��ƍēx���������󂯕t���邱�Ƃ�
�@�ł��܂���̂ł����ӂ��������B

�y�X�֐U�֌����z
�����L���F00120�]7
�����ԍ��F670031
�J�j�L���b�`�{�[��

���̑��A���x���Ɋւ��Ă��s���ȓ_�͉��L�t�q�k�����m�F���������B
https://atobarai-user.jp/

�����i�̕ԕi�E�����ȂǏ��i�Ɋւ��邨�₢���킹�́F
���ڍw���X�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@
�d�b�F{Phone}

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',0,'2015/07/23 15:27:30',9,'2022/04/20 5:58:56',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (199,45,'�x���������߃��[���i�ĂT�j�iPC�j','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z{OrderDate}{SiteNameKj}�ł̂��������̌�({OrderId})','{ServiceName}','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɂ������̂��葱�������ς̂悤�ł����
�@�����[���ւ̕ԐM�͂��s�v�ł������܂��B

{CustomerNameKj}�l

���̓x��{SiteNameKj}�ŏ��i���w���̍ۂɁA
�y{ServiceName}�z�������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ClaimDate}�ɂ����肵���������̂��x�����������߂��Ă�
�������̊m�F�����Ă���܂���ł����̂�
�Đ������𔭍s�E�����������܂����B

��L����ł����m�点���Ă���ʂ�A
���x�������m�F�ł��Ȃ��ꍇ
���q�l�̐M�p����ȂǕs���v��������\�����������܂��B
���܂��Ă͑��₩�Ȃ��Ή������肢�������܂��B

����T�Ԃقǂ��҂����������Ă����������͂��Ȃ��ꍇ��
��ς��萔�ł����A03-4326-3600�ɂ���񂭂������B


�y���������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
�Đ����ǉ��萔���F{ReClaimFee}
���������z�F{TotalAmount}

�����x���������߂��Ă��܂��A�ēx�����������s����܂���
�Đ����萔�������Z�����ꍇ���������܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ł������܂�)
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z
{Bk_BankName}�@{Bk_BranchName}
���ʌ����@{Bk_AccountNumber}
{Bk_AccountHolderKn}
�����������ƂɌ����ԍ����قȂ��Ă���܂��B
����x�����������܂��ƍēx���������󂯕t���邱�Ƃ�
�@�ł��܂���̂ł����ӂ��������B

�y�X�֐U�֌����z
�����L���F00120�]7
�����ԍ��F670031
�J�j�L���b�`�{�[��

���̑��A���x���Ɋւ��Ă��s���ȓ_�͉��L�t�q�k�����m�F���������B
https://atobarai-user.jp/

�����i�̕ԕi�E�����ȂǏ��i�Ɋւ��邨�₢���킹�́F
���ڍw���X�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@
�d�b�F{Phone}

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',0,'2015/07/23 15:27:30',9,'2022/04/20 5:59:16',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (200,46,'�x���������߃��[���i�ĂT�j�iCEL�j','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z{OrderDate}{SiteNameKj}�ł̂��������̌�({OrderId})','{ServiceName}','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɂ������̂��葱�������ς̂悤�ł����
�@�����[���ւ̕ԐM�͂��s�v�ł������܂��B

{CustomerNameKj}�l

���̓x��{SiteNameKj}�ŏ��i���w���̍ۂɁA
�y{ServiceName}�z�������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ClaimDate}�ɂ����肵���������̂��x�����������߂��Ă�
�������̊m�F�����Ă���܂���ł����̂�
�Đ������𔭍s�E�����������܂����B

��L����ł����m�点���Ă���ʂ�A
���x�������m�F�ł��Ȃ��ꍇ
���q�l�̐M�p����ȂǕs���v��������\�����������܂��B
���܂��Ă͑��₩�Ȃ��Ή������肢�������܂��B

����T�Ԃقǂ��҂����������Ă����������͂��Ȃ��ꍇ��
��ς��萔�ł����A03-4326-3600�ɂ���񂭂������B


�y���������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
�Đ����ǉ��萔���F{ReClaimFee}
���������z�F{TotalAmount}

�����x���������߂��Ă��܂��A�ēx�����������s����܂���
�Đ����萔�������Z�����ꍇ���������܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ł������܂�)
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z
{Bk_BankName}�@{Bk_BranchName}
���ʌ����@{Bk_AccountNumber}
{Bk_AccountHolderKn}
�����������ƂɌ����ԍ����قȂ��Ă���܂��B
����x�����������܂��ƍēx���������󂯕t���邱�Ƃ�
�@�ł��܂���̂ł����ӂ��������B

�y�X�֐U�֌����z
�����L���F00120�]7
�����ԍ��F670031
�J�j�L���b�`�{�[��

���̑��A���x���Ɋւ��Ă��s���ȓ_�͉��L�t�q�k�����m�F���������B
https://atobarai-user.jp/

�����i�̕ԕi�E�����ȂǏ��i�Ɋւ��邨�₢���킹�́F
���ڍw���X�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@
�d�b�F{Phone}

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',0,'2015/07/23 15:27:30',9,'2022/04/20 5:59:22',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (201,47,'�x���������߃��[���i�ĂU�j�iPC�j','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z{OrderDate}{SiteNameKj}�ł̂��������̌�({OrderId})','{ServiceName}','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɂ������̂��葱�������ς̂悤�ł����
�@�����[���ւ̕ԐM�͂��s�v�ł������܂��B

{CustomerNameKj}�l

���̓x��{SiteNameKj}�ŏ��i���w���̍ۂɁA
�y{ServiceName}�z�������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ClaimDate}�ɓ���������肢�����܂������A
�{�����݂������̊m�F���ł��Ă���܂���B

���̂܂ܖ�������Ԃ��p������܂��ƁA
���Ђł̑Ή�������ƂȂ�
�ʒm�L�ڂ̑Ή��ƂȂ�ꍇ������܂��B
���܂��Ă͎��}���x�����ɂ���
���Ή����������܂��悤���肢�������܂��B

����T�Ԃقǂ��҂����������Ă����������͂��Ȃ��ꍇ��
��ς��萔�ł����A03-4326-3600�ɂ���񂭂������B


�y���������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
�Đ����ǉ��萔���F{ReClaimFee}
���������z�F{TotalAmount}

�����x���������߂��Ă��܂��A�ēx�����������s����܂���
�Đ����萔�������Z�����ꍇ���������܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ł������܂�)
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z
{Bk_BankName}�@{Bk_BranchName}
���ʌ����@{Bk_AccountNumber}
{Bk_AccountHolderKn}
�����������ƂɌ����ԍ����قȂ��Ă���܂��B
����x�����������܂��ƍēx���������󂯕t���邱�Ƃ�
�@�ł��܂���̂ł����ӂ��������B

�y�X�֐U�֌����z
�����L���F00120�]7
�����ԍ��F670031
�J�j�L���b�`�{�[��

���̑��A���x���Ɋւ��Ă��s���ȓ_�͉��L�t�q�k�����m�F���������B
https://atobarai-user.jp/

�����i�̕ԕi�E�����ȂǏ��i�Ɋւ��邨�₢���킹�́F
���ڍw���X�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@
�d�b�F{Phone}

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',0,'2015/07/23 15:27:30',9,'2022/04/20 5:59:43',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (202,48,'�x���������߃��[���i�ĂU�j�iCEL�j','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z{OrderDate}{SiteNameKj}�ł̂��������̌�({OrderId})','{ServiceName}','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɂ������̂��葱�������ς̂悤�ł����
�@�����[���ւ̕ԐM�͂��s�v�ł������܂��B

{CustomerNameKj}�l

���̓x��{SiteNameKj}�ŏ��i���w���̍ۂɁA
�y{ServiceName}�z�������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ClaimDate}�ɓ���������肢�����܂������A
�{�����݂������̊m�F���ł��Ă���܂���B

���̂܂ܖ�������Ԃ��p������܂��ƁA
���Ђł̑Ή�������ƂȂ�
�ʒm�L�ڂ̑Ή��ƂȂ�ꍇ������܂��B
���܂��Ă͎��}���x�����ɂ���
���Ή����������܂��悤���肢�������܂��B

����T�Ԃقǂ��҂����������Ă����������͂��Ȃ��ꍇ��
��ς��萔�ł����A03-4326-3600�ɂ���񂭂������B


�y���������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
�Đ����ǉ��萔���F{ReClaimFee}
���������z�F{TotalAmount}

�����x���������߂��Ă��܂��A�ēx�����������s����܂���
�Đ����萔�������Z�����ꍇ���������܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ł������܂�)
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z
{Bk_BankName}�@{Bk_BranchName}
���ʌ����@{Bk_AccountNumber}
{Bk_AccountHolderKn}
�����������ƂɌ����ԍ����قȂ��Ă���܂��B
����x�����������܂��ƍēx���������󂯕t���邱�Ƃ�
�@�ł��܂���̂ł����ӂ��������B

�y�X�֐U�֌����z
�����L���F00120�]7
�����ԍ��F670031
�J�j�L���b�`�{�[��

���̑��A���x���Ɋւ��Ă��s���ȓ_�͉��L�t�q�k�����m�F���������B
https://atobarai-user.jp/

�����i�̕ԕi�E�����ȂǏ��i�Ɋւ��邨�₢���킹�́F
���ڍw���X�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@
�d�b�F{Phone}

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',0,'2015/07/23 15:27:30',9,'2022/04/20 5:59:51',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (203,49,'�x���������߃��[���i�ĂV�j�iPC�j','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z{OrderDate}{SiteNameKj}�ł̂��������̌�({OrderId})','{ServiceName}','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɂ������̂��葱�������ς̂悤�ł����
�@�����[���ւ̕ԐM�͂��s�v�ł������܂��B


{CustomerNameKj}�l

���̓x��{SiteNameKj}�ŏ��i���w���̍ۂɁA
�y{ServiceName}�z�������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ClaimDate}�ɂ����肵���������̂��x�����������߂��Ă�
�������̊m�F�����Ă���܂���ł����̂�
�Đ������𔭍s�E�����������܂����B

�ĎO�ɂ킽��A���ԍςɑ΂����s�����悤���ʒm�������܂�����
�M�a��落�ӂ̂��邲�Ή��𒸂��Ă��Ȃ��󋵂ƂȂ��Ă���܂��B
����ɂ��܂��Ă��A���A���E���x�������m�F�ł��Ȃ��ꍇ��
�ٌ�m�ւ̉���ϔC�������͖@�I�葱���Ɉڍs����������܂���B
�������Ȃ���A���Ѝ��Ǘ����ł�
�M�a�̍����s�ɑ΂�������}��ׂ̑��k������݂��Ă���
���k�ɂ��������\�ȏꍇ���������܂��B
���܂��ẮA�����Ɍ������}���A�����������܂��悤���肢�������܂��B

����T�Ԃقǂ��҂����������Ă����������͂��Ȃ��ꍇ��
��ς��萔�ł����A03-4326-3600�ɂ���񂭂������B

�y���������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
�Đ����ǉ��萔���F{ReClaimFee}
���������z�F{TotalAmount}

�����x���������߂��Ă��܂��A�ēx�����������s����܂���
�Đ����萔�������Z�����ꍇ���������܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ł������܂�)
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z
{Bk_BankName}�@{Bk_BranchName}
���ʌ����@{Bk_AccountNumber}
{Bk_AccountHolderKn}
�����������ƂɌ����ԍ����قȂ��Ă���܂��B
����x�����������܂��ƍēx���������󂯕t���邱�Ƃ�
�@�ł��܂���̂ł����ӂ��������B

�y�X�֐U�֌����z
�����L���F00120�]7
�����ԍ��F670031
�J�j�L���b�`�{�[��

���̑��A���x���Ɋւ��Ă��s���ȓ_�͉��L�t�q�k�����m�F���������B
https://atobarai-user.jp/

�����i�̕ԕi�E�����ȂǏ��i�Ɋւ��邨�₢���킹�́F
���ڍw���X�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@
�d�b�F{Phone}

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',0,'2015/07/23 15:27:30',9,'2022/04/20 6:00:12',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (204,50,'�x���������߃��[���i�ĂV�j�iCEL�j','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z{OrderDate}{SiteNameKj}�ł̂��������̌�({OrderId})','{ServiceName}','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɂ������̂��葱�������ς̂悤�ł����
�@�����[���ւ̕ԐM�͂��s�v�ł������܂��B


{CustomerNameKj}�l

���̓x��{SiteNameKj}�ŏ��i���w���̍ۂɁA
�y{ServiceName}�z�������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ClaimDate}�ɂ����肵���������̂��x�����������߂��Ă�
�������̊m�F�����Ă���܂���ł����̂�
�Đ������𔭍s�E�����������܂����B

�ĎO�ɂ킽��A���ԍςɑ΂����s�����悤���ʒm�������܂�����
�M�a��落�ӂ̂��邲�Ή��𒸂��Ă��Ȃ��󋵂ƂȂ��Ă���܂��B
����ɂ��܂��Ă��A���A���E���x�������m�F�ł��Ȃ��ꍇ��
�ٌ�m�ւ̉���ϔC�������͖@�I�葱���Ɉڍs����������܂���B
�������Ȃ���A���Ѝ��Ǘ����ł�
�M�a�̍����s�ɑ΂�������}��ׂ̑��k������݂��Ă���
���k�ɂ��������\�ȏꍇ���������܂��B
���܂��ẮA�����Ɍ������}���A�����������܂��悤���肢�������܂��B

����T�Ԃقǂ��҂����������Ă����������͂��Ȃ��ꍇ��
��ς��萔�ł����A03-4326-3600�ɂ���񂭂������B

�y���������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
�Đ����ǉ��萔���F{ReClaimFee}
���������z�F{TotalAmount}

�����x���������߂��Ă��܂��A�ēx�����������s����܂���
�Đ����萔�������Z�����ꍇ���������܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ł������܂�)
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z
{Bk_BankName}�@{Bk_BranchName}
���ʌ����@{Bk_AccountNumber}
{Bk_AccountHolderKn}
�����������ƂɌ����ԍ����قȂ��Ă���܂��B
����x�����������܂��ƍēx���������󂯕t���邱�Ƃ�
�@�ł��܂���̂ł����ӂ��������B

�y�X�֐U�֌����z
�����L���F00120�]7
�����ԍ��F670031
�J�j�L���b�`�{�[��

���̑��A���x���Ɋւ��Ă��s���ȓ_�͉��L�t�q�k�����m�F���������B
https://atobarai-user.jp/

�����i�̕ԕi�E�����ȂǏ��i�Ɋւ��邨�₢���킹�́F
���ڍw���X�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@
�d�b�F{Phone}

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',0,'2015/07/23 15:27:30',9,'2022/04/20 6:00:18',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (205,51,'CB�����������܂Ƃ߃G���[���[��','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,'daisuke-koie@scroll.co.jp','�y{ServiceName}�z�������܂Ƃ߃G���[���[��','{ServiceName}','{EnterpriseNameKj} �l

������ς����b�ɂȂ��Ă���܂��B
�y{ServiceName}�z�J�X�^�}�[�Z���^�[�ł������܂��B

�������܂Ƃߎ��s���ɃG���[���������܂����B 
���L�̂������𐿋����܂Ƃߒ����ꗗ����m�F�����A���߂Č�w�������肢�v���܂��B
                      
���܂Ƃ߂Ɏ��s�������܂Ƃߎw���O���[�v�̒����F
{OrderSummary}

���R�F
{Error}

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',0,'2015/07/23 15:27:30',9,'2022/04/20 6:00:40',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (206,52,'���ƎҌ����������܂Ƃ߃G���[���[��','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z�������܂Ƃ߃G���[���[��','{ServiceName}','{EnterpriseNameKj} �l

������ς����b�ɂȂ��Ă���܂��B
�y{ServiceName}�z�J�X�^�}�[�Z���^�[�ł������܂��B

�������܂Ƃߎ��s���ɃG���[���������܂����B 
���L�̂������𐿋����܂Ƃߒ����ꗗ����m�F�����A���߂Č�w�������肢�v���܂��B
                      
���܂Ƃ߂Ɏ��s�������܂Ƃߎw���O���[�v�̒����F
{OrderSummary}

���R�F
{Error}

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',0,'2015/07/23 15:27:30',9,'2022/04/20 6:01:07',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (207,53,'�}�C�y�[�W���o�^�������[���iPC�j','�㕥���h�b�g�R�� / �͂��Ă��略��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQiAvIBskQkZPJCQkRiQrJGlKJyQkGyhC?=','customer@ato-barai.com',null,null,null,'�y�㕥���h�b�g�R�� / �͂��Ă��略���z������o�^�̂��ē�','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44OgIC8g5bGK44GE44Gm44GL44KJ5omV?=
 =?UTF-8?B?44GE?=','���̓x�́y�㕥���h�b�g�R�� / �͂��Ă��略���z�ɂ��\���݂�������
�܂��Ƃɂ��肪�Ƃ��������܂��B

���L��URL���N���b�N���āA�y�㕥���h�b�g�R�� / �͂��Ă��略���z�ł�
����o�^��i�߂Ă��������B

{MypageRegistUrl}


�������ӎ�����
�E�{���[�������󂯎���A24���Ԉȓ��Ɂy�㕥���h�b�g�R�� / �͂��Ă��略���z����o�^��
�������Ă��������܂��悤���肢�������܂��B
�E24���Ԉȓ��ɂ��o�^����������Ȃ��ꍇ�͉��o�^�̂��葱����������
�Ȃ�܂��̂ł��炩���߂������肢�܂��B
�E24���Ԃ��߂��Ă��܂����ꍇ�́A�������܂����ēx���o�^�̂��葱����
���肢�������܂��B


------------------------------------
���o�^�̎菇�ɂ���
------------------------------------

�P.��LURL�ɃA�N�Z�X���A��ʂɂ��������ĕK�v�����������͂��������B

�Q.�����͓��e�����m�F�̂����A���q�l����o�^���Ă��������B

�R.�y�㕥���h�b�g�R�� / �͂��Ă��略���z����o�^�����̂��m�点�h�Ƃ������[�����͂��܂��B

�ȏ�Ły�㕥���h�b�g�R�� / �͂��Ă��略���z����o�^�����ƂȂ�܂��B



���o�^�����܂������Ȃ��ꍇ�́A
��ϋ������܂���customer@ato-barai.com�܂�
���₢���킹�����肢�������܂��B

���c�Ǝ��ԊO�̂��₢���킹�ɂ��܂��Ă�
���ԐM�ɂ����Ԃ����������ꍇ���������܂��B


���̓x�͂��\���݂��肪�Ƃ��������܂����B


-----------------------------------------------------------
�y�㕥���h�b�g�R�� / �͂��Ă��略���z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�Fcustomer@ato-barai.com
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',0,'2015/07/23 15:27:30',9,'2022/07/14 11:25:15',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (208,54,'�}�C�y�[�W���o�^�������[���iCEL�j','�㕥���h�b�g�R�� / �͂��Ă��略��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQiAvIBskQkZPJCQkRiQrJGlKJyQkGyhC?=','customer@ato-barai.com',null,null,null,'�y�㕥���h�b�g�R�� / �͂��Ă��略���z������o�^�̂��ē�','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44OgIC8g5bGK44GE44Gm44GL44KJ5omV?=
 =?UTF-8?B?44GE?=','���̓x�́y�㕥���h�b�g�R�� / �͂��Ă��略���z�ɂ��\���݂�������
�܂��Ƃɂ��肪�Ƃ��������܂��B

���L��URL���N���b�N���āA�y�㕥���h�b�g�R�� / �͂��Ă��略���z�ł�
����o�^��i�߂Ă��������B

{MypageRegistUrl}


�������ӎ�����
�E�{���[�������󂯎���A24���Ԉȓ��Ɂy�㕥���h�b�g�R�� / �͂��Ă��略���z����o�^��
�������Ă��������܂��悤���肢�������܂��B
�E24���Ԉȓ��ɂ��o�^����������Ȃ��ꍇ�͉��o�^�̂��葱����������
�Ȃ�܂��̂ł��炩���߂������肢�܂��B
�E24���Ԃ��߂��Ă��܂����ꍇ�́A�������܂����ēx���o�^�̂��葱����
���肢�������܂��B


------------------------------------
���o�^�̎菇�ɂ���
------------------------------------

�P.��LURL�ɃA�N�Z�X���A��ʂɂ��������ĕK�v�����������͂��������B

�Q.�����͓��e�����m�F�̂����A���q�l����o�^���Ă��������B

�R.�y�㕥���h�b�g�R�� / �͂��Ă��略���z����o�^�����̂��m�点�h�Ƃ������[�����͂��܂��B

�ȏ�Ły�㕥���h�b�g�R�� / �͂��Ă��略���z����o�^�����ƂȂ�܂��B



���o�^�����܂������Ȃ��ꍇ�́A
��ϋ������܂���customer@ato-barai.com�܂�
���₢���킹�����肢�������܂��B

���c�Ǝ��ԊO�̂��₢���킹�ɂ��܂��Ă�
���ԐM�ɂ����Ԃ����������ꍇ���������܂��B


���̓x�͂��\���݂��肪�Ƃ��������܂����B


-----------------------------------------------------------
�y�㕥���h�b�g�R�� / �͂��Ă��略���z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�Fcustomer@ato-barai.com
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',0,'2015/07/23 15:27:30',9,'2022/07/14 11:25:36',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (209,55,'�}�C�y�[�W�{�o�^�������[���iPC�j','�㕥���h�b�g�R�� / �͂��Ă��略��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQiAvIBskQkZPJCQkRiQrJGlKJyQkGyhC?=','customer@ato-barai.com',null,null,null,'�y�㕥���h�b�g�R�� / �͂��Ă��略���z����o�^�����̂��m�点','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44OgIC8g5bGK44GE44Gm44GL44KJ5omV?=
 =?UTF-8?B?44GE?=','{MyPageNameKj}�l

���̓x�́y�㕥���h�b�g�R�� / �͂��Ă��略���z�ɂ��\���݂�������
�܂��Ƃɂ��肪�Ƃ��������܂��B

�y�㕥���h�b�g�R�� / �͂��Ă��略���z�ł̉���o�^�������������܂����B
���L�y�[�W��胍�O�C�����Ă����p�����������Ƃ��ł��܂��B
����p�}�C�y�[�W�@https://www.atobarai.jp/mypage
ID:���o�^�̃��[���A�h���X
�p�X���[�h�F���o�^�̃p�X���[�h




�������[���ɂ��S������̂Ȃ����́A
�������܂������L���[���A�h���X�܂ł��A�������肢�������܂��B
�܂��A���T�[�r�X�Ɋւ��邻�̑��̂��₢���킹��
���L�A�h���X�ɂď����Ă���܂��B

���̓x�͂��\���݂��肪�Ƃ��������܂����B


-----------------------------------------------------------
�y�㕥���h�b�g�R�� / �͂��Ă��略���z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�Fcustomer@ato-barai.com
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',0,'2015/07/23 15:27:30',9,'2022/07/14 11:27:37',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (210,56,'�}�C�y�[�W�{�o�^�������[���iCEL�j','�㕥���h�b�g�R�� / �͂��Ă��略��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQiAvIBskQkZPJCQkRiQrJGlKJyQkGyhC?=','{ServiceMail}',null,null,null,'�y�㕥���h�b�g�R�� / �͂��Ă��略���z����o�^�����̂��m�点','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44OgIC8g5bGK44GE44Gm44GL44KJ5omV?=
 =?UTF-8?B?44GE?=','{MyPageNameKj}�l

���̓x�́y�㕥���h�b�g�R�� / �͂��Ă��略���z�ɂ��\���݂�������
�܂��Ƃɂ��肪�Ƃ��������܂��B

�y�㕥���h�b�g�R�� / �͂��Ă��略���z�ł̉���o�^�������������܂����B
���L�y�[�W��胍�O�C�����Ă����p�����������Ƃ��ł��܂��B
����p�}�C�y�[�W�@https://www.atobarai.jp/mypage
ID:���o�^�̃��[���A�h���X
�p�X���[�h�F���o�^�̃p�X���[�h




�������[���ɂ��S������̂Ȃ����́A
�������܂������L���[���A�h���X�܂ł��A�������肢�������܂��B
�܂��A���T�[�r�X�Ɋւ��邻�̑��̂��₢���킹��
���L�A�h���X�ɂď����Ă���܂��B

���̓x�͂��\���݂��肪�Ƃ��������܂����B


-----------------------------------------------------------
�y�㕥���h�b�g�R�� / �͂��Ă��略���z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�Fcustomer@ato-barai.com
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',0,'2015/07/23 15:27:30',9,'2022/07/14 11:27:24',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (211,57,'�}�C�y�[�W�p�X���[�h�ύX���[���iPC�j','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z�p�X���[�h�ύX������܂���','{ServiceName}','{MyPageNameKj}�l

�����y{ServiceName}�z�������p��������
�܂��Ƃɂ��肪�Ƃ��������܂��B

�}�C�y�[�W�̃p�X���[�h�ύX������܂����̂ł��񍐂������܂��B

���LURL�փA�N�Z�X���A���O�C�����s���Ă��������B

https://www.atobarai.jp/mypage

�������[���ɂ��S������̂Ȃ����́A
�������܂������L���[���A�h���X�܂ł��A�������肢�������܂��B
�܂��A���T�[�r�X�Ɋւ��邻�̑��̂��₢���킹��
���L�A�h���X�ɂď����Ă���܂��B

����Ƃ��A���T�[�r�X����낵�����肢�������܂��B

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',0,'2015/07/23 15:27:30',9,'2022/04/20 6:02:18',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (212,58,'�}�C�y�[�W�p�X���[�h�ύX���[���iCEL�j','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z�p�X���[�h�ύX������܂���','{ServiceName}','{MyPageNameKj}�l

�����y{ServiceName}�z�������p��������
�܂��Ƃɂ��肪�Ƃ��������܂��B

�}�C�y�[�W�̃p�X���[�h�ύX������܂����̂ł��񍐂������܂��B

���LURL�փA�N�Z�X���A���O�C�����s���Ă��������B

https://www.atobarai.jp/mypage

�������[���ɂ��S������̂Ȃ����́A
�������܂������L���[���A�h���X�܂ł��A�������肢�������܂��B
�܂��A���T�[�r�X�Ɋւ��邻�̑��̂��₢���킹��
���L�A�h���X�ɂď����Ă���܂��B

����Ƃ��A���T�[�r�X����낵�����肢�������܂��B

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',0,'2015/07/23 15:27:30',9,'2022/04/20 6:02:27',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (213,59,'�}�C�y�[�W�މ�����[���iPC�j','�㕥���h�b�g�R�� / �͂��Ă��略��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQiAvIBskQkZPJCQkRiQrJGlKJyQkGyhC?=','customer@ato-barai.com',null,null,null,'�y�㕥���h�b�g�R�� / �͂��Ă��略���z�މ���̂��m�点','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44OgIC8g5bGK44GE44Gm44GL44KJ5omV?=
 =?UTF-8?B?44GE?=','{MyPageNameKj}�l

���̓x�́y�㕥���h�b�g�R�� / �͂��Ă��略���z�������p��������
�܂��Ƃɂ��肪�Ƃ��������܂����B

�މ�葱���������������܂����̂ł��񍐂������܂��B

�܂��̂����p��S��肨�҂����Ă���܂��B



���̃��[���͑މ�葱�������ꂽ���[���A�h���X��
�����Ŕz�M���Ă���܂��B
�ēx����o�^�������ۂ́A���LURL�փA�N�Z�X���������܂��B

https://www.atobarai.jp/mypage

-----------------------------------------------------------
�y�㕥���h�b�g�R�� / �͂��Ă��略���z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�Fcustomer@ato-barai.com
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',0,'2015/07/23 15:27:30',9,'2022/07/14 11:28:09',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (214,60,'�}�C�y�[�W�މ�����[���iCEL�j','�㕥���h�b�g�R�� / �͂��Ă��略��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQiAvIBskQkZPJCQkRiQrJGlKJyQkGyhC?=','customer@ato-barai.com',null,null,null,'�y�㕥���h�b�g�R�� / �͂��Ă��略���z�މ���̂��m�点','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44OgIC8g5bGK44GE44Gm44GL44KJ5omV?=
 =?UTF-8?B?44GE?=','{MyPageNameKj}�l

���̓x�́y�㕥���h�b�g�R�� / �͂��Ă��略���z�������p��������
�܂��Ƃɂ��肪�Ƃ��������܂����B

�މ�葱���������������܂����̂ł��񍐂������܂��B

�܂��̂����p��S��肨�҂����Ă���܂��B



���̃��[���͑މ�葱�������ꂽ���[���A�h���X��
�����Ŕz�M���Ă���܂��B
�ēx����o�^�������ۂ́A���LURL�փA�N�Z�X���������܂��B

https://www.atobarai.jp/mypage

-----------------------------------------------------------
�y�㕥���h�b�g�R�� / �͂��Ă��略���z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�Fcustomer@ato-barai.com
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',0,'2015/07/23 15:27:30',9,'2022/07/14 11:28:25',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (215,61,'�Г��^�M�ۗ����[��','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z�^�M�̌��ł��m�F�����肢�������܂�','{ServiceName}','{EnterpriseNameKj}�l

������ς����b�ɂȂ��Ă���܂��B
�y{ServiceName}�z�J�X�^�}�[�Z���^�[�ł������܂��B

�{���^�M�����������܂���

{OrderId} {CustomerNameKj}�l�ł���

{PendingReason}

{PendingDate}�܂ŗ^�M�ۗ��Ƃ����Ă��������܂��̂�
���萔�ł͂������܂����A�������������m�F��������
�Ǘ��T�C�g��ł��ύX�̏���������������
���Ђ܂ł��A�������������܂��悤���肢�������܂��B

�����������������������@�������C���������ۂ̒��Ӂ@����������������������

�C�����e�������͂�����������A�u���̓��e�œo�^�v���N���b�N�����
���e�̊m�F��ʂɑJ�ڂ��܂��B���e�����m�F�̂����A������x
�u���̓��e�œo�^�v���N���b�N����ƏC���������ƂȂ�܂��B
�i���m�F��ʂ���ʂ̃y�[�W�Ɉڂ��Ă��܂�����
���Ă��܂����肷��ƁA�C�������f����܂���B�j

������������������������������������������������������������������������

�y�Ǘ���ʂt�q�k�z
https://www.atobarai.jp/member/

���s���ȓ_�Ȃǂ������܂�����A���C�y�ɂ��₢���킹���������܂��B

������낵�����肢�������܂��B

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',0,'2015/10/30 21:58:02',9,'2022/04/20 6:03:08',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (216,81,'�s�������A�����[���iPC�j','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z�������z���s�����Ă���܂�','{ServiceName}','������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

{CustomerNameKj}�l

���̓x��{SiteNameKj}�ŏ��i���w���̍ۂɁA
�y{ServiceName}�z�������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂����B

{ReceiptDate}��{ReceiptClass}���{UseAmount}�~�̂����������������܂������A
{ShortfallAmount}�~���s���ƂȂ��Ă���܂��B

��ς��萔�ł����s������{ShortfallAmount}�~��{�����P�T�Ԉȓ���
���L�����܂ł��U���݂��������܂��悤���肢�������܂��B
(�U���ݎ萔���͂��q�l�����S�ƂȂ�܂��B)

�y��s�U�������z
�O��Z�F��s�@�V�h�ʎx�X
���ʌ����@8047001
�J�j�L���b�`�{�[��

�y�X�֐U�֌����z
�����L���F00120-7
�����ԍ��F670031
������ЃL���b�`�{�[��

�s���_�Ȃǂ������܂�����
���C�y�ɂ��⍇�����������܂��B

�����A��낵�����肢�������܂��B

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',0,'2015/07/23 15:27:30',9,'2022/04/20 6:03:25',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (217,82,'�s�������A�����[���iCEL�j','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z�������z���s�����Ă���܂�','{ServiceName}','������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

{CustomerNameKj}�l

���̓x��{SiteNameKj}�ŏ��i���w���̍ۂɁA
�y{ServiceName}�z�������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂����B

{ReceiptDate}��{ReceiptClass}���{UseAmount}�~�̂����������������܂������A
{ShortfallAmount}�~���s���ƂȂ��Ă���܂��B

��ς��萔�ł����s������{ShortfallAmount}�~��{�����P�T�Ԉȓ���
���L�����܂ł��U���݂��������܂��悤���肢�������܂��B
(�U���ݎ萔���͂��q�l�����S�ƂȂ�܂��B)

�y��s�U�������z
�O��Z�F��s�@�V�h�ʎx�X
���ʌ����@8047001
�J�j�L���b�`�{�[��

�y�X�֐U�֌����z
�����L���F00120-7
�����ԍ��F670031
������ЃL���b�`�{�[��

�s���_�Ȃǂ������܂�����
���C�y�ɂ��⍇�����������܂��B

�����A��낵�����肢�������܂��B

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',0,'2015/07/23 15:27:30',9,'2022/04/20 6:03:33',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (218,83,'�}�C�y�[�W�g���؃A�b�v���[�h���[��','�㕥���h�b�g�R�� / �͂��Ă��略��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQiAvIBskQkZPJCQkRiQrJGlKJyQkGyhC?=','customer@ato-barai.com',null,null,null,'�V�X�e���Ŏ����I�ɐݒ肳��܂�','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44OgIC8g5bGK44GE44Gm44GL44KJ5omV?=
 =?UTF-8?B?44GE?=','�V�X�e���Ŏ����I�ɐݒ肳��܂�',0,'2015/07/23 15:27:30',9,'2022/07/14 11:28:53',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (219,86,'���Ǝ҃��[���o�^�o�b�`�G���[���[��','{ServiceName}','{ServiceName}','{ServiceMail}','�㕥���h�b�g�R��(�I�y���[�^�[)',null,'cb-360resysmember@mb.scroll360.jp','�y{ServiceName}�z���Ǝ҃��[���o�^�o�b�`�G���[���[��','{ServiceName}','�ȉ��̎��Ǝғo�^���[���ɑ΂��鏈���Ɏ��s���܂����B

------------------------------
{body}',0,'2015/10/05 18:15:52',9,'2022/04/20 5:38:37',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (220,88,'�ԋ����[���i�L�����Z���j(CEL)','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z���ԋ��̂��A��','{ServiceName}','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

{CustomerNameKj}�l

���̓x��{SiteNameKj}�l�ŏ��i���w���̍ۂɁA
�y{ServiceName}�z�������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂����B

{ReceiptDate}��{ReceiptClass}��育�������m�F�������܂������A
���ɓX�ܗl��育�����L�����Z���̂��A�������������Ă���܂����̂�
���ԋ������Ă������������A�����̊m�F�̂��A���������グ�܂����B

���萔�ł͂������܂���
�E��s���F
�E�x�X���F
�E������ځF
�E�����ԍ��F
�E�������`(�J�i)�F
��L�����L���̂����A�����[���ւ��ԐM���������܂��B

�s���_�Ȃǂ������܂�����A���C�y�ɂ��⍇�����������܂��B

������낵�����肢�������܂��B

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------

',0,'2015/11/06 16:54:46',9,'2022/04/20 6:04:20',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (221,87,'�ԋ����[���i�L�����Z���j(PC)','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z���ԋ��̂��A��','{ServiceName}','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

{CustomerNameKj}�l

���̓x��{SiteNameKj}�l�ŏ��i���w���̍ۂɁA
�y{ServiceName}�z�������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂����B

{ReceiptDate}��{ReceiptClass}��育�������m�F�������܂������A
���ɓX�ܗl��育�����L�����Z���̂��A�������������Ă���܂����̂�
���ԋ������Ă������������A�����̊m�F�̂��A���������グ�܂����B

���萔�ł͂������܂���
�E��s���F
�E�x�X���F
�E������ځF
�E�����ԍ��F
�E�������`(�J�i)�F
��L�����L���̂����A�����[���ւ��ԐM���������܂��B

�s���_�Ȃǂ������܂�����A���C�y�ɂ��⍇�����������܂��B

������낵�����肢�������܂��B

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------

',0,'2015/11/06 16:54:45',9,'2022/04/20 6:04:14',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (352,4,'���������s���[���iPC�j','�yBASE�㕥�����ρz','=?UTF-8?B?GyRCIVobKEJCQVNFGyRCOGVKJyQkN2g6USFbGyhC?=','atobarai@thebase.in',null,null,null,'�yBASE�㕥�����ρz���������s�ē��@�i�n�K�L�œ͂��܂��j','=?UTF-8?B?44CQQkFTReW+jOaJleOBhOaxuua4iOOAkQ==?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
�������������������������������������������������������������������� 

{CustomerNameKj}�l

����� {SiteNameKj}�l�ł������������Ē���
�܂��Ƃɂ��肪�Ƃ��������܂��B

���L�̃V���b�s���O�̂���������{�����s�������܂��̂ŁA������������A
�������ɋL�ڂ���Ă��邨�x���������܂łɂ��x�������������܂��悤�A
���肢�\���グ�܂��B

�y���������e�z

���x���ҁF{CustomerNameKj}�@�l

���w���X�ܖ��F{SiteNameKj}

���w�����F{OrderDate}

���x�����z�F{UseAmount}

���w�����i���ׁF���i���^���^�w���i�ڌv

{OrderItems}

���ώ萔��                              \{SettlementFee}

����                                    \{DeliveryFee}

���X�����̂Ȃǂɂ��A���������͂��Ȃ����Ƃ��������܂��B
�@��T�Ԃقǂ��҂����������Ă����������͂��Ȃ��ꍇ�ɂ́A��ς��萔�ł����A
�@���L�A����ւ���񂭂������܂��悤�A���肢�\���グ�܂��B

�����i�ɂ��܂��Ă��A���[���ւȂǂ̔z�����@�̏ꍇ�ɂ́A�z�����̂Ȃǂɂ��
�@�͂��Ȃ��ꍇ���������܂��B
�@������A���i���͂��Ă��Ȃ��ꍇ�ɂ͑�ς��萔�ł͂������܂����A���������ꂽ
�@�X�ܗl�܂Œ��ڂ��⍇�����������܂��B

�����������тɖ{���[�����A���i���������������Ă��܂����Ƃ��������܂����A
�@�������e�͉������܂��悤���肢�\���グ�܂��B
�@�܂��A���������ɖ��ׂ��܂܂�Ă���܂��̂ł��m�F���������܂��B


�ڂ����͉��LURL�������������B
http://thebase.in/pages/help.html#category14_146

���s���ȓ_�Ȃǂ������܂�����A���C�y�ɉ��L�܂ł��⍇���������B

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�@�{���ɓ���Ă��⍇�����������B

���L�����Z���i���\���j����Ă���ꍇ�ł��A�s���Ⴂ�ɂē����[����
�@�z�M����Ă��܂��ꍇ���������܂��B���̍ۂ͑�ς��萔�ł����A���L
�@�w���X�ܗl�ɒ��ڂ��⍇�����������B


�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F

���ڍw���X�ܗl�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@
�d�b�F{Phone}

�����i��������7���ȍ~�̃L�����Z���͂ł��܂���̂ł����ӂ��������B
�X�܂Ƃ̓��ӂ̏�L�����Z���ɂ�菤�i��ԕi�����ꍇ�͂��̎|�A
���L���[���A�h���X�܂Œ������e�̂��A�������肢���܂��B

support@thebase.in

�L�����Z�����s�Ȃ�Ȃ���
���i����x������
�������͂������܂��̂ł����ӂ��������B

{SiteNameKj} �� BASE ( https://thebase.in ) �ō쐬����Ă��܂��B 
BASE�͒N�ł��ȒP�ɖ����Ńl�b�g�V���b�v���J�݂ł���T�[�r�X�ł��B

�����x�����Ɋւ��邨�₢���킹�́F

BASE �㕥�����ρ@����
TEL:[03-6279-1149](�����y��9:00�`18:00)
Mail: atobarai@thebase.in


����������������������������������������������������������������������

BASE (�x�C�X)
https://thebase.in

 ���⍇����:[03-6279-1149]
 �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: atobarai@thebase.in

����������������������������������������������������������������������',4,'2015/08/31 22:42:31',9,'2015/12/01 8:36:00',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (353,5,'���������s���[���iCEL�j','�yBASE�㕥�����ρz','=?UTF-8?B?GyRCIVobKEJCQVNFGyRCOGVKJyQkN2g6USFbGyhC?=','atobarai@thebase.in',null,null,null,'�yBASE�㕥�����ρz���������s�ē��@�i�n�K�L�œ͂��܂��j','=?UTF-8?B?44CQQkFTReW+jOaJleOBhOaxuua4iOOAkQ==?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
�������������������������������������������������������������������� 

{CustomerNameKj}�l

����� {SiteNameKj}�l�ł������������Ē���
�܂��Ƃɂ��肪�Ƃ��������܂��B

���L�̃V���b�s���O�̂���������{�����s�������܂��̂ŁA������������A
�������ɋL�ڂ���Ă��邨�x���������܂łɂ��x�������������܂��悤�A
���肢�\���グ�܂��B

�y���������e�z

���x���ҁF{CustomerNameKj}�@�l

���w���X�ܖ��F{SiteNameKj}

���w�����F{OrderDate}

���x�����z�F{UseAmount}

���w�����i���ׁF���i���^���^�w���i�ڌv

{OrderItems}

���ώ萔��                              \{SettlementFee}

����                                    \{DeliveryFee}

���X�����̂Ȃǂɂ��A���������͂��Ȃ����Ƃ��������܂��B
�@��T�Ԃقǂ��҂����������Ă����������͂��Ȃ��ꍇ�ɂ́A��ς��萔�ł����A
�@���L�A����ւ���񂭂������܂��悤�A���肢�\���グ�܂��B

�����i�ɂ��܂��Ă��A���[���ւȂǂ̔z�����@�̏ꍇ�ɂ́A�z�����̂Ȃǂɂ��
�@�͂��Ȃ��ꍇ���������܂��B
�@������A���i���͂��Ă��Ȃ��ꍇ�ɂ͑�ς��萔�ł͂������܂����A���������ꂽ
�@�X�ܗl�܂Œ��ڂ��⍇�����������܂��B

�����������тɖ{���[�����A���i���������������Ă��܂����Ƃ��������܂����A
�@�������e�͉������܂��悤���肢�\���グ�܂��B
�@�܂��A���������ɖ��ׂ��܂܂�Ă���܂��̂ł��m�F���������܂��B


�ڂ����͉��LURL�������������B
http://thebase.in/pages/help.html#category14_146

���s���ȓ_�Ȃǂ������܂�����A���C�y�ɉ��L�܂ł��⍇���������B

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�@�{���ɓ���Ă��⍇�����������B

���L�����Z���i���\���j����Ă���ꍇ�ł��A�s���Ⴂ�ɂē����[����
�@�z�M����Ă��܂��ꍇ���������܂��B���̍ۂ͑�ς��萔�ł����A���L
�@�w���X�ܗl�ɒ��ڂ��⍇�����������B


�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F

���ڍw���X�ܗl�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@
�d�b�F{Phone}

�����i��������7���ȍ~�̃L�����Z���͂ł��܂���̂ł����ӂ��������B
�X�܂Ƃ̓��ӂ̏�L�����Z���ɂ�菤�i��ԕi�����ꍇ�͂��̎|�A
���L���[���A�h���X�܂Œ������e�̂��A�������肢���܂��B

support@thebase.in

�L�����Z�����s�Ȃ�Ȃ���
���i����x������
�������͂������܂��̂ł����ӂ��������B

{SiteNameKj} �� BASE ( https://thebase.in ) �ō쐬����Ă��܂��B 
BASE�͒N�ł��ȒP�ɖ����Ńl�b�g�V���b�v���J�݂ł���T�[�r�X�ł��B

�����x�����Ɋւ��邨�₢���킹�́F

BASE �㕥�����ρ@����
TEL:[03-6279-1149](�����y��9:00�`18:00)
Mail: atobarai@thebase.in


����������������������������������������������������������������������

BASE (�x�C�X)
https://thebase.in

 ���⍇����:[03-6279-1149]
 �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: atobarai@thebase.in

����������������������������������������������������������������������
',4,'2015/08/31 22:42:31',9,'2015/12/01 8:39:22',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (354,6,'�����m�F���[���iPC�j','�yBASE�㕥�����ρz','=?UTF-8?B?GyRCIVobKEJCQVNFGyRCOGVKJyQkN2g6USFbGyhC?=','atobarai@thebase.in',null,null,null,'�yBASE�㕥�����ρz�������m�F�̂���','=?UTF-8?B?44CQQkFTReW+jOaJleOBhOaxuua4iOOAkQ==?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
�������������������������������������������������������������������� 

{CustomerNameKj}�l

���̓x�́A{SiteNameKj}�l�ł������������Ē���
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ReceiptDate}��{CustomerNameKj}�l����̂�������
�m�F�������܂����̂ł��񍐂������܂��B

�ȉ����A���񂲓��������������������̓��e�ł������܂��B

�y�̎��ς݂��������e�z

���x���ҁF{CustomerNameKj}�@�l

���w���X�ܖ��F{SiteNameKj}�@

���w�����F{OrderDate}

���x�����z�F{UseAmount}

���w�����i���ׁF���i���^���^�w���i�ڌv

{OrderItems}

���ώ萔��                              \{SettlementFee}

����                                    \{DeliveryFee}


���w���X�ܖ��F{SiteNameKj}

���A����F{Phone}

�Z���F{Address}


���s���ȓ_�Ȃǂ������܂�����A���C�y�ɂ��₢���킹�������B


�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F

���ڍw���X�ܗl�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}


{SiteNameKj} �� BASE ( https://thebase.in ) �ō쐬����Ă��܂��B 
BASE�͒N�ł��ȒP�ɖ����Ńl�b�g�V���b�v���J�݂ł���T�[�r�X�ł��B

�����x�����Ɋւ��邨�₢���킹�́F

BASE �㕥�����ρ@����
TEL:[03-6279-1149](�����y��9:00�`18:00)
Mail: atobarai@thebase.in


����������������������������������������������������������������������

BASE (�x�C�X)
https://thebase.in

 ���⍇����:[03-6279-1149]
 �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: atobarai@thebase.in

����������������������������������������������������������������������',4,'2015/08/31 22:42:31',9,'2015/12/01 8:42:35',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (355,7,'�����m�F���[���iCEL�j','�yBASE�㕥�����ρz','=?UTF-8?B?GyRCIVobKEJCQVNFGyRCOGVKJyQkN2g6USFbGyhC?=','atobarai@thebase.in',null,null,null,'�yBASE�㕥�����ρz�������m�F�̂���','=?UTF-8?B?44CQQkFTReW+jOaJleOBhOaxuua4iOOAkQ==?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
�������������������������������������������������������������������� 

{CustomerNameKj}�l

���̓x�́A{SiteNameKj}�l�ł������������Ē���
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ReceiptDate}��{CustomerNameKj}�l����̂�������
�m�F�������܂����̂ł��񍐂������܂��B

�ȉ����A���񂲓��������������������̓��e�ł������܂��B

�y�̎��ς݂��������e�z

���x���ҁF{CustomerNameKj}�@�l

���w���X�ܖ��F{SiteNameKj}�@

���w�����F{OrderDate}

���x�����z�F{UseAmount}

���w�����i���ׁF���i���^���^�w���i�ڌv

{OrderItems}

���ώ萔��                              \{SettlementFee}

����                                    \{DeliveryFee}


���w���X�ܖ��F{SiteNameKj}

���A����F{Phone}

�Z���F{Address}


���s���ȓ_�Ȃǂ������܂�����A���C�y�ɂ��₢���킹�������B


�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F

���ڍw���X�ܗl�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}


{SiteNameKj} �� BASE ( https://thebase.in ) �ō쐬����Ă��܂��B 
BASE�͒N�ł��ȒP�ɖ����Ńl�b�g�V���b�v���J�݂ł���T�[�r�X�ł��B

�����x�����Ɋւ��邨�₢���킹�́F

BASE �㕥�����ρ@����
TEL:[03-6279-1149](�����y��9:00�`18:00)
Mail: atobarai@thebase.in


����������������������������������������������������������������������

BASE (�x�C�X)
https://thebase.in

 ���⍇����:[03-6279-1149]
 �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: atobarai@thebase.in

����������������������������������������������������������������������',4,'2015/08/31 22:42:31',9,'2015/12/01 11:07:32',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (359,11,'�����������x�����[���iPC�j','�yBASE�㕥�����ρz','=?UTF-8?B?GyRCIVobKEJCQVNFGyRCOGVKJyQkN2g6USFbGyhC?=','atobarai@thebase.in',null,null,null,'�yBASE�㕥�����ρz�����������m�F�̂��m�点','=?UTF-8?B?44CQQkFTReW+jOaJleOBhOaxuua4iOOAkQ==?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
�������������������������������������������������������������������� 

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɂ������̂��葱�������ς̂悤�ł����
�@�����[���ւ̕ԐM�͂��s�v�ł������܂��B

{CustomerNameKj}�l

�����{SiteNameKj}�l�ł������������Ē���
�܂��Ƃɂ��肪�Ƃ��������܂��B

{IssueDate}�ɐ������������肢�����܂������A���茳�ɂ��͂��ł��傤���B

�y���������e�z							
���������F{OrderDate}							
�������X�܁F{SiteNameKj} 
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}						
���������z�F{UseAmount}	
���x���������i{LimitDate}�j						
 							
�����L�����֒��ڂ��U���݂��������܂��Ă��A�������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ł������܂�)	
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�����������܂��͂��Ă��Ȃ��ꍇ�͑�ς��萔�ł����A
���}�� 03-6279-1149 �ɂ���񂭂������B

�����L�����֒��ڂ��U���݂��������܂��Ă��A�������̊m�F�͎��܂��B
  �����ւ��U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B


�y��s�U�������z
�W���p���l�b�g��s
���~�W�x�X
����
3721018
�x�C�X�J�u�V�L�K�C�V��
�a�`�r�d�������

�y�X�֐U�������z
�L���F001600-8
�ԍ��F450807
������ЃL���b�`�{�[���@�a�`�r�d��p����
�i�J�u�V�L�K�C�V���L���b�`�{�[���@�x�C�X�Z�����E�R�E�U�j

���X�֋ǁ^��s���炨�U���݂��������ꍇ�A�U���萔�������q�l�����S�ƂȂ�܂��B

���X�֋ǂ̌����������̏ꍇ�́A�X�֋ǂ̂`�s�l�𗘗p���Č������炲��������������΁A
  �X�֐U���萔���͂�����܂���B(�X�܌��ώ萔���Ƃ͕ʂł��B)


�����ꐿ���������茳�ɓ͂��Ă��Ȃ��ꍇ��A���x���Ɋւ��܂��āA���s���ȓ_��
�������܂�����A���L�܂ł��C�y�ɂ��₢���킹�������܂��B


�����i�ɂ��܂��Ă��A���[���ւȂǂ̔z�����@�̏ꍇ�ɂ́A
�@�z�����̂Ȃǂɂ��͂��Ȃ��ꍇ���������܂��B
�@������A���i���͂��Ă��Ȃ��ꍇ�ɂ͑�ς��萔�ł͂������܂����A
�@���������ꂽ�X�ܗl�܂Œ��ڂ��⍇�����������܂��B

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�@�{���ɓ���Ă��⍇�����������B

���L�����Z���i���\���j����Ă���ꍇ�ł��A�s���Ⴂ�ɂē����[����
�@�z�M����Ă��܂��ꍇ���������܂��B���̍ۂ͑�ς��萔�ł����A�������ꂽ
�@�X�ܗl�ɒ��ڂ��⍇�����������B


�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F

���ڍw���X�ܗl�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}


{SiteNameKj}�@ �� BASE ( https://thebase.in ) �ō쐬����Ă��܂��B 
BASE�͒N�ł��ȒP�ɖ����Ńl�b�g�V���b�v���J�݂ł���T�[�r�X�ł��B

�����x�����Ɋւ��邨�₢���킹�́F

BASE �㕥�����ρ@����
TEL:[03-6279-1149](�����y��9:00�`18:00)
Mail: atobarai@thebase.in


����������������������������������������������������������������������

BASE (�x�C�X)
https://thebase.in

 ���⍇����:[03-6279-1149]
 �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: atobarai@thebase.in

����������������������������������������������������������������������',4,'2015/08/31 22:42:31',9,'2015/12/01 9:01:31',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (360,12,'�����������x�����[���iCEL�j','�yBASE�㕥�����ρz','=?UTF-8?B?GyRCIVobKEJCQVNFGyRCOGVKJyQkN2g6USFbGyhC?=','atobarai@thebase.in',null,null,null,'�yBASE�㕥�����ρz�����������m�F�̂��m�点','=?UTF-8?B?44CQQkFTReW+jOaJleOBhOaxuua4iOOAkQ==?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
�������������������������������������������������������������������� 

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɂ������̂��葱�������ς̂悤�ł����
�@�����[���ւ̕ԐM�͂��s�v�ł������܂��B

{CustomerNameKj}�l

�����{SiteNameKj}�l�ł������������Ē���
�܂��Ƃɂ��肪�Ƃ��������܂��B

{IssueDate}�ɐ������������肢�����܂������A���茳�ɂ��͂��ł��傤���B

�y���������e�z							
���������F{OrderDate}							
�������X�܁F{SiteNameKj} 
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}						
���������z�F{UseAmount}	
���x���������i{LimitDate}�j						
 							
�����L�����֒��ڂ��U���݂��������܂��Ă��A�������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ł������܂�)	
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�����������܂��͂��Ă��Ȃ��ꍇ�͑�ς��萔�ł����A
���}�� 03-6279-1149 �ɂ���񂭂������B

�����L�����֒��ڂ��U���݂��������܂��Ă��A�������̊m�F�͎��܂��B
  �����ւ��U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B


�y��s�U�������z
�W���p���l�b�g��s
���~�W�x�X
����
3721018
�x�C�X�J�u�V�L�K�C�V��
�a�`�r�d�������

�y�X�֐U�������z
�L���F001600-8
�ԍ��F450807
������ЃL���b�`�{�[���@�a�`�r�d��p����
�i�J�u�V�L�K�C�V���L���b�`�{�[���@�x�C�X�Z�����E�R�E�U�j

���X�֋ǁ^��s���炨�U���݂��������ꍇ�A�U���萔�������q�l�����S�ƂȂ�܂��B

���X�֋ǂ̌����������̏ꍇ�́A�X�֋ǂ̂`�s�l�𗘗p���Č������炲��������������΁A
  �X�֐U���萔���͂�����܂���B(�X�܌��ώ萔���Ƃ͕ʂł��B)


�����ꐿ���������茳�ɓ͂��Ă��Ȃ��ꍇ��A���x���Ɋւ��܂��āA���s���ȓ_��
�������܂�����A���L�܂ł��C�y�ɂ��₢���킹�������܂��B


�����i�ɂ��܂��Ă��A���[���ւȂǂ̔z�����@�̏ꍇ�ɂ́A
�@�z�����̂Ȃǂɂ��͂��Ȃ��ꍇ���������܂��B
�@������A���i���͂��Ă��Ȃ��ꍇ�ɂ͑�ς��萔�ł͂������܂����A
�@���������ꂽ�X�ܗl�܂Œ��ڂ��⍇�����������܂��B

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�@�{���ɓ���Ă��⍇�����������B

���L�����Z���i���\���j����Ă���ꍇ�ł��A�s���Ⴂ�ɂē����[����
�@�z�M����Ă��܂��ꍇ���������܂��B���̍ۂ͑�ς��萔�ł����A�������ꂽ
�@�X�ܗl�ɒ��ڂ��⍇�����������B


�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F

���ڍw���X�ܗl�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}


{SiteNameKj}�@ �� BASE ( https://thebase.in ) �ō쐬����Ă��܂��B 
BASE�͒N�ł��ȒP�ɖ����Ńl�b�g�V���b�v���J�݂ł���T�[�r�X�ł��B

�����x�����Ɋւ��邨�₢���킹�́F

BASE �㕥�����ρ@����
TEL:[03-6279-1149](�����y��9:00�`18:00)
Mail: atobarai@thebase.in


����������������������������������������������������������������������

BASE (�x�C�X)
https://thebase.in

 ���⍇����:[03-6279-1149]
 �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: atobarai@thebase.in

����������������������������������������������������������������������',4,'2015/08/31 22:42:31',9,'2015/12/01 9:01:58',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (364,16,'�߂萿���Z���m�F���[��','�yBASE�㕥�����ρz','=?UTF-8?B?GyRCIVobKEJCQVNFGyRCOGVKJyQkN2g6USFbGyhC?=','atobarai@thebase.in',null,null,null,'BASE�y�d�v�z���Z���m�F�̘A���ł��B','=?UTF-8?B?44CQQkFTReW+jOaJleOBhOaxuua4iOOAkQ==?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������  

{CustomerNameKj}�l

{ReceiptOrderDate}��{SiteNameKj}�ŁA
�㕥�����ς�I�����Ă����������肪�Ƃ��������܂��B

{ClaimDate}�ɂ����肢�����܂��������������Ђɖ߂��Ă��Ă���܂��̂ŁA
���Z���̊m�F�������Ă��������������A�������Ă��������܂����B

�i���q�l�Z���j�@{UnitingAddress}

��L�Z���ɕs��������܂�����A�ēx�������𔭍s�����Ă��������܂��̂�
���A���̒��A��낵�����肢�v���܂��B

�Z���ɕs�����Ȃ��ꍇ�ł��A�\�D����������Ă����ꍇ�ȂǂŁA�X�֕����͂��Ȃ��P�[�X��
����܂��̂ŁA�������������B

�܂��A��s�A�X�֋ǂ���̂��������\�ł��̂�
�����ԍ��������肳���Ă��������܂��B


�y��s�U�������z
�W���p���l�b�g��s
���~�W�x�X
����
3721018
�x�C�X�J�u�V�L�K�C�V��
�a�`�r�d�������

�y�X�֐U�������z
�L���F001600-8
�ԍ��F450807
������ЃL���b�`�{�[���@�a�`�r�d��p����
�i�J�u�V�L�K�C�V���L���b�`�{�[���@�x�C�X�Z�����E�R�E�U�j


�y���������ׁz

���i���@�@�F{ItemNameKj}

���i����@�F{ItemAmount}�~

�����@�@�@�F{DeliveryFee}�~

�萔���@�@�F{SettlementFee}�~

{OptionFee}

���v�@�@�@�F{UseAmount}�~


�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F

���ڍw���X�ܗl�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}


{SiteNameKj}�@ �� BASE ( https://thebase.in ) �ō쐬����Ă��܂��B 
BASE�͒N�ł��ȒP�ɖ����Ńl�b�g�V���b�v���J�݂ł���T�[�r�X�ł��B

�����x�����Ɋւ��邨�₢���킹�́F

BASE �㕥�����ρ@����
TEL:[03-6279-1149](�����y��9:00�`18:00)
Mail: atobarai@thebase.in


����������������������������������������������������������������������

BASE (�x�C�X)
https://thebase.in

 ���⍇����:[03-6279-1149]
 �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: atobarai@thebase.in

����������������������������������������������������������������������',4,'2015/08/31 22:42:31',9,'2015/12/01 10:14:50',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (374,32,'�������j�����[���iPC�j','�yBASE�㕥�����ρz','=?UTF-8?B?GyRCIVobKEJCQVNFGyRCOGVKJyQkN2g6USFbGyhC?=','atobarai@thebase.in',null,null,null,'�yBASE�㕥�����ρz�������j���̂��肢','=?UTF-8?B?44CQQkFTReW+jOaJleOBhOaxuua4iOOAkQ==?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

{CustomerNameKj}�l

�����{SiteNameKj}�l�ł������������Ē���
�܂��Ƃɂ��肪�Ƃ��������܂��B

{CancelDate}��{SiteNameKj}���BASE�㕥��������
�L�����Z���̂��A�������������܂������A
���ɐ������������肵�Ă��܂��Ă���܂��̂ŁA
��ς��萔�ł͂������܂����j�����Ă��������悤���肢�������܂��B

���̓x��{SiteNameKj}��BASE�㕥�����ς������p��������
�܂��Ƃɂ��肪�Ƃ��������܂����B

���s���ȓ_�Ȃǂ������܂�����A���C�y�ɉ��L�܂ł��⍇���������B

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�@�{���ɓ���Ă��⍇�����������B


�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
���ڍw���X�ܗl�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}


{SiteNameKj}�@ �� BASE ( https://thebase.in ) �ō쐬����Ă��܂��B 
BASE�͒N�ł��ȒP�ɖ����Ńl�b�g�V���b�v���J�݂ł���T�[�r�X�ł��B


�����x�����Ɋւ��邨�₢���킹�́F
BASE �㕥�����ρ@����
TEL:[03-6279-1149](�����y��9:00�`18:00)
Mail: atobarai@thebase.in

����������������������������������������������������������������
BASE (�x�C�X)
https://thebase.in
 ���⍇����:[03-6279-1149]
 �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: atobarai@thebase.in
����������������������������������������������������������������',4,'2015/07/23 15:27:30',9,'2015/12/01 11:03:22',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (375,33,'�������j�����[���iCEL�j','�yBASE�㕥�����ρz','=?UTF-8?B?GyRCIVobKEJCQVNFGyRCOGVKJyQkN2g6USFbGyhC?=','atobarai@thebase.in',null,null,null,'�yBASE�㕥�����ρz�������j���̂��肢','=?UTF-8?B?44CQQkFTReW+jOaJleOBhOaxuua4iOOAkQ==?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

{CustomerNameKj}�l

�����{SiteNameKj}�l�ł������������Ē���
�܂��Ƃɂ��肪�Ƃ��������܂��B

{CancelDate}��{SiteNameKj}���BASE�㕥��������
�L�����Z���̂��A�������������܂������A
���ɐ������������肵�Ă��܂��Ă���܂��̂ŁA
��ς��萔�ł͂������܂����j�����Ă��������悤���肢�������܂��B

���̓x��{SiteNameKj}��BASE�㕥�����ς������p��������
�܂��Ƃɂ��肪�Ƃ��������܂����B

���s���ȓ_�Ȃǂ������܂�����A���C�y�ɉ��L�܂ł��⍇���������B

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�@�{���ɓ���Ă��⍇�����������B


�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
���ڍw���X�ܗl�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}


{SiteNameKj}�@ �� BASE ( https://thebase.in ) �ō쐬����Ă��܂��B 
BASE�͒N�ł��ȒP�ɖ����Ńl�b�g�V���b�v���J�݂ł���T�[�r�X�ł��B


�����x�����Ɋւ��邨�₢���킹�́F
BASE �㕥�����ρ@����
TEL:[03-6279-1149](�����y��9:00�`18:00)
Mail: atobarai@thebase.in

����������������������������������������������������������������
BASE (�x�C�X)
https://thebase.in
 ���⍇����:[03-6279-1149]
 �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: atobarai@thebase.in
����������������������������������������������������������������',4,'2015/07/23 15:27:30',9,'2015/12/01 11:02:59',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (376,34,'�ߏ�������[���iPC�j','�yBASE�㕥�����ρz','=?UTF-8?B?GyRCIVobKEJCQVNFGyRCOGVKJyQkN2g6USFbGyhC?=','atobarai@thebase.in',null,null,null,'�yBASE�㕥�����ρz���ԋ��̂��A��','=?UTF-8?B?44CQQkFTReW+jOaJleOBhOaxuua4iOOAkQ==?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

{CustomerNameKj}�l

���̓x��{SiteNameKj}�ŏ��i���w���̍ۂɁA
BASE�㕥�����ς������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂����B

{ReceiptDate}��{ReceiptClass}��育�������m�F�������܂������A
{OverReceiptAmount}�~�������x�������������Ă���܂����̂�
���ԋ������Ă������������A�����̊m�F�̂��A���������グ�܂����B

���萔�ł͂������܂���
�E��s���F
�E�x�X���F
�E������ځF
�E�����ԍ��F
�E�������`(�J�i)�F
��L�����L���̂����A�����[���ւ��ԐM���������܂��B

������낵�����肢�������܂��B

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�@�{���ɓ���Ă��⍇�����������B


�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
���ڍw���X�ܗl�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@


{SiteNameKj}�@ �� BASE ( https://thebase.in ) �ō쐬����Ă��܂��B 
BASE�͒N�ł��ȒP�ɖ����Ńl�b�g�V���b�v���J�݂ł���T�[�r�X�ł��B


�����x�����Ɋւ��邨�₢���킹�́F
BASE �㕥�����ρ@����
TEL:[03-6279-1149](�����y��9:00�`18:00)
Mail: atobarai@thebase.in

����������������������������������������������������������������
BASE (�x�C�X)
https://thebase.in
 ���⍇����:[03-6279-1149]
 �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: atobarai@thebase.in
����������������������������������������������������������������
',4,'2015/07/23 15:27:30',9,'2015/12/01 10:29:20',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (377,35,'�ߏ�������[���iCEL�j','�yBASE�㕥�����ρz','=?UTF-8?B?GyRCIVobKEJCQVNFGyRCOGVKJyQkN2g6USFbGyhC?=','atobarai@thebase.in',null,null,null,'�yBASE�㕥�����ρz���ԋ��̂��A��','=?UTF-8?B?44CQQkFTReW+jOaJleOBhOaxuua4iOOAkQ==?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

{CustomerNameKj}�l

���̓x��{SiteNameKj}�ŏ��i���w���̍ۂɁA
BASE�㕥�����ς������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂����B

{ReceiptDate}��{ReceiptClass}��育�������m�F�������܂������A
{OverReceiptAmount}�~�������x�������������Ă���܂����̂�
���ԋ������Ă������������A�����̊m�F�̂��A���������グ�܂����B

���萔�ł͂������܂���
�E��s���F
�E�x�X���F
�E������ځF
�E�����ԍ��F
�E�������`(�J�i)�F
��L�����L���̂����A�����[���ւ��ԐM���������܂��B

������낵�����肢�������܂��B

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�@�{���ɓ���Ă��⍇�����������B


�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
���ڍw���X�ܗl�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@


{SiteNameKj}�@ �� BASE ( https://thebase.in ) �ō쐬����Ă��܂��B 
BASE�͒N�ł��ȒP�ɖ����Ńl�b�g�V���b�v���J�݂ł���T�[�r�X�ł��B


�����x�����Ɋւ��邨�₢���킹�́F
BASE �㕥�����ρ@����
TEL:[03-6279-1149](�����y��9:00�`18:00)
Mail: atobarai@thebase.in

����������������������������������������������������������������
BASE (�x�C�X)
https://thebase.in
 ���⍇����:[03-6279-1149]
 �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: atobarai@thebase.in
����������������������������������������������������������������
',4,'2015/07/23 15:27:30',9,'2015/12/01 10:30:21',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (379,39,'�x���������߃��[���i�ĂP�j�iPC�j','������ЃL���b�`�{�[��','=?UTF-8?B?GyRCM3Q8MDJxPFIlLSVjJUMlQSVcITwlaxsoQg==?=','customer2@ato-barai.com',null,null,null,'�y���m�F���������z�F{OrderDate}�@{SiteNameKj}�ł̂��������̌�({OrderId})','=?UTF-8?B?5qCq5byP5Lya56S+44Kt44Oj44OD44OB44Oc44O844Or?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɂ������̂��葱�������ς̂悤�ł����
�@�����[���ւ̕ԐM�͂��s�v�ł������܂��B

{CustomerNameKj}�l

�����{SiteNameKj}�l�ł������������Ē���
�܂��Ƃɂ��肪�Ƃ��������܂��B

{IssueDate}�ɐ������������肢�����܂������A
�{�����݂������̊m�F���ł��Ă���܂���B

�y���������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
���������z�F{TotalAmount}

��������A���x���������߂��Ă��܂��܂���
�Đ����萔�������Z����܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ł������܂�)
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z							
�O��Z�F��s�@�V�h�ʎx�X
���ʌ����@8047001
�J�j�L���b�`�{�[��				

�y�X�֐U�֌����z							
�����L���F00120�]7							
�����ԍ��F670031							
�J�j�L���b�`�{�[��							

������A�����������茳�ɓ͂��Ă��Ȃ��ꍇ��
���̑����s���ȓ_�A�������̂����k����
03-6908-6662(9�F00�`18�F00)�܂ł��₢���킹�������B


�����i�ɂ��܂��Ă��A���[���ւȂǂ̔z�����@�̏ꍇ�ɂ́A�z�����̂Ȃǂɂ��
�͂��Ȃ��ꍇ���������܂��B
������A���i���͂��Ă��Ȃ��ꍇ�ɂ͑�ς��萔�ł͂������܂����A
���������ꂽ�X�ܗl�܂Œ��ڂ��⍇�����������܂��B


�����[���ɂĂ��⍇�������������ꍇ�́A
�K�����������̂����O�i�t���l�[���j��
�{���ɓ���Ă��⍇�����������B


���L�����Z���i���\���j����Ă���ꍇ�ł��A
�s���Ⴂ�ɂē����[�����z�M����Ă��܂��ꍇ���������܂��B
���̍ۂ͑�ς��萔�ł����������ꂽ�X�ܗl�ɒ��ڂ��⍇�����������B


�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
�@���ڍw���X�ܗl�ɂ��₢���킹���������B
�@�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}


�����x�����Ɋւ��邨�₢���킹�́F
  ���⍇����F03-6908-6662
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com
  �^�c��ЁF������ЃL���b�`�{�[��  
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F',4,'2015/07/23 15:27:30',9,'2015/12/01 10:59:17',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (380,40,'�x���������߃��[���i�ĂP�j�iCEL�j','������ЃL���b�`�{�[��','=?UTF-8?B?GyRCM3Q8MDJxPFIlLSVjJUMlQSVcITwlaxsoQg==?=','customer2@ato-barai.com',null,null,null,'�y���m�F���������z�F{OrderDate}�@{SiteNameKj}�ł̂��������̌�({OrderId})','=?UTF-8?B?5qCq5byP5Lya56S+44Kt44Oj44OD44OB44Oc44O844Or?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɂ������̂��葱�������ς̂悤�ł����
�@�����[���ւ̕ԐM�͂��s�v�ł������܂��B

{CustomerNameKj}�l

�����{SiteNameKj}�l�ł������������Ē���
�܂��Ƃɂ��肪�Ƃ��������܂��B

{IssueDate}�ɐ������������肢�����܂������A
�{�����݂������̊m�F���ł��Ă���܂���B

�y���������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
���������z�F{TotalAmount}

��������A���x���������߂��Ă��܂��܂���
�Đ����萔�������Z����܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ł������܂�)
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z							
�O��Z�F��s�@�V�h�ʎx�X
���ʌ����@8047001
�J�j�L���b�`�{�[��				

�y�X�֐U�֌����z							
�����L���F00120�]7							
�����ԍ��F670031							
�J�j�L���b�`�{�[��							

������A�����������茳�ɓ͂��Ă��Ȃ��ꍇ��
���̑����s���ȓ_�A�������̂����k����
03-6908-6662(9�F00�`18�F00)�܂ł��₢���킹�������B


�����i�ɂ��܂��Ă��A���[���ւȂǂ̔z�����@�̏ꍇ�ɂ́A�z�����̂Ȃǂɂ��
�͂��Ȃ��ꍇ���������܂��B
������A���i���͂��Ă��Ȃ��ꍇ�ɂ͑�ς��萔�ł͂������܂����A
���������ꂽ�X�ܗl�܂Œ��ڂ��⍇�����������܂��B


�����[���ɂĂ��⍇�������������ꍇ�́A
�K�����������̂����O�i�t���l�[���j��
�{���ɓ���Ă��⍇�����������B


���L�����Z���i���\���j����Ă���ꍇ�ł��A
�s���Ⴂ�ɂē����[�����z�M����Ă��܂��ꍇ���������܂��B
���̍ۂ͑�ς��萔�ł����������ꂽ�X�ܗl�ɒ��ڂ��⍇�����������B


�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
�@���ڍw���X�ܗl�ɂ��₢���킹���������B
�@�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}


�����x�����Ɋւ��邨�₢���킹�́F
  ���⍇����F03-6908-6662
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com
  �^�c��ЁF������ЃL���b�`�{�[��  
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F',4,'2015/07/23 15:27:30',9,'2015/12/01 10:58:55',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (381,41,'�x���������߃��[���i�ĂR�j�iPC�j','������ЃL���b�`�{�[��','=?UTF-8?B?GyRCM3Q8MDJxPFIlLSVjJUMlQSVcITwlaxsoQg==?=','customer2@ato-barai.com',null,null,null,'�y���m�F���������z�F{OrderDate}�@{SiteNameKj}�ł̂��������̌�({OrderId})','=?UTF-8?B?5qCq5byP5Lya56S+44Kt44Oj44OD44OB44Oc44O844Or?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɂ������̂��葱�������ς̂悤�ł����
�@�����[���ւ̕ԐM�͂��s�v�ł������܂��B

{CustomerNameKj}�l

�����{SiteNameKj}�l�ł������������Ē���
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ClaimDate}�ɍĐ������������肢�����܂������A
�{�����݂������̊m�F���ł��Ă���܂���B

��ς��萔�ł����A��L�Đ����������m�F��������
���x�������������܂��悤���肢�������܂��B


�y���������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
�Đ����ǉ��萔���F{ReClaimFee}
�x�����Q���F{DamageInterest}
���������z�F{TotalAmount}

��������A���x���������߂��Ă��܂��܂���
�Đ����萔�������Z����܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ł������܂�)
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z							
�O��Z�F��s�@�V�h�ʎx�X
���ʌ����@8047001
�J�j�L���b�`�{�[��				

�y�X�֐U�֌����z							
�����L���F00120�]7							
�����ԍ��F670031							
�J�j�L���b�`�{�[��							

������A�����������茳�ɓ͂��Ă��Ȃ��ꍇ��
���̑����s���ȓ_�A�������̂����k����
03-6908-6662(9�F00�`18�F00)�܂ł��₢���킹�������B


�����i�ɂ��܂��Ă��A���[���ւȂǂ̔z�����@�̏ꍇ�ɂ́A�z�����̂Ȃǂɂ��
�͂��Ȃ��ꍇ���������܂��B
������A���i���͂��Ă��Ȃ��ꍇ�ɂ͑�ς��萔�ł͂������܂����A
���������ꂽ�X�ܗl�܂Œ��ڂ��⍇�����������܂��B


�����[���ɂĂ��⍇�������������ꍇ�́A
�K�����������̂����O�i�t���l�[���j��
�{���ɓ���Ă��⍇�����������B


���L�����Z���i���\���j����Ă���ꍇ�ł��A
�s���Ⴂ�ɂē����[�����z�M����Ă��܂��ꍇ���������܂��B
���̍ۂ͑�ς��萔�ł����������ꂽ�X�ܗl�ɒ��ڂ��⍇�����������B


�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
�@���ڍw���X�ܗl�ɂ��₢���킹���������B
�@�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}


�����x�����Ɋւ��邨�₢���킹�́F
  ���⍇����F03-6908-6662
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com
  �^�c��ЁF������ЃL���b�`�{�[��  
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F',4,'2015/07/23 15:27:30',9,'2015/12/01 10:41:14',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (382,42,'�x���������߃��[���i�ĂR�j�iCEL�j','������ЃL���b�`�{�[��','=?UTF-8?B?GyRCM3Q8MDJxPFIlLSVjJUMlQSVcITwlaxsoQg==?=','customer2@ato-barai.com',null,null,null,'�y���m�F���������z�F{OrderDate}�@{SiteNameKj}�ł̂��������̌�({OrderId})','=?UTF-8?B?5qCq5byP5Lya56S+44Kt44Oj44OD44OB44Oc44O844Or?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɂ������̂��葱�������ς̂悤�ł����
�@�����[���ւ̕ԐM�͂��s�v�ł������܂��B

{CustomerNameKj}�l

�����{SiteNameKj}�l�ł������������Ē���
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ClaimDate}�ɍĐ������������肢�����܂������A
�{�����݂������̊m�F���ł��Ă���܂���B

��ς��萔�ł����A��L�Đ����������m�F��������
���x�������������܂��悤���肢�������܂��B


�y���������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
�Đ����ǉ��萔���F{ReClaimFee}
�x�����Q���F{DamageInterest}
���������z�F{TotalAmount}

��������A���x���������߂��Ă��܂��܂���
�Đ����萔�������Z����܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ł������܂�)
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z							
�O��Z�F��s�@�V�h�ʎx�X
���ʌ����@8047001
�J�j�L���b�`�{�[��				

�y�X�֐U�֌����z							
�����L���F00120�]7							
�����ԍ��F670031							
�J�j�L���b�`�{�[��							

������A�����������茳�ɓ͂��Ă��Ȃ��ꍇ��
���̑����s���ȓ_�A�������̂����k����
03-6908-6662(9�F00�`18�F00)�܂ł��₢���킹�������B


�����i�ɂ��܂��Ă��A���[���ւȂǂ̔z�����@�̏ꍇ�ɂ́A�z�����̂Ȃǂɂ��
�͂��Ȃ��ꍇ���������܂��B
������A���i���͂��Ă��Ȃ��ꍇ�ɂ͑�ς��萔�ł͂������܂����A
���������ꂽ�X�ܗl�܂Œ��ڂ��⍇�����������܂��B


�����[���ɂĂ��⍇�������������ꍇ�́A
�K�����������̂����O�i�t���l�[���j��
�{���ɓ���Ă��⍇�����������B


���L�����Z���i���\���j����Ă���ꍇ�ł��A
�s���Ⴂ�ɂē����[�����z�M����Ă��܂��ꍇ���������܂��B
���̍ۂ͑�ς��萔�ł����������ꂽ�X�ܗl�ɒ��ڂ��⍇�����������B


�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
�@���ڍw���X�ܗl�ɂ��₢���킹���������B
�@�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}


�����x�����Ɋւ��邨�₢���킹�́F
  ���⍇����F03-6908-6662
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com
  �^�c��ЁF������ЃL���b�`�{�[��  
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F',4,'2015/07/23 15:27:30',9,'2015/12/01 10:58:31',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (383,43,'�x���������߃��[���i�ĂS�j�iPC�j','������ЃL���b�`�{�[��','=?UTF-8?B?GyRCM3Q8MDJxPFIlLSVjJUMlQSVcITwlaxsoQg==?=','customer2@ato-barai.com',null,null,null,'�y���m�F���������z�F{OrderDate}�@{SiteNameKj}�ł̂��������̌�({OrderId})','=?UTF-8?B?5qCq5byP5Lya56S+44Kt44Oj44OD44OB44Oc44O844Or?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɂ������̂��葱�������ς̂悤�ł����
�@�����[���ւ̕ԐM�͂��s�v�ł������܂��B

{CustomerNameKj}�l

����� {SiteNameKj}�ł������������Ē���
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ClaimDate}�ɓ���������肢�����܂������A
�{�����݂������̊m�F���ł��Ă���܂���B

���܂��ẮA���[���̂��x�����ɂ�
���}���Ή����������܂��悤���肢�������܂��B

�y���������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
�Đ����ǉ��萔���F{ReClaimFee}
�x�����Q���F{DamageInterest}
���������z�F{TotalAmount}

��������A���x���������߂��Ă��܂��܂���
�Đ����萔�������Z����܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ł������܂�)
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z							
�O��Z�F��s�@�V�h�ʎx�X
���ʌ����@8047001
�J�j�L���b�`�{�[��				

�y�X�֐U�֌����z							
�����L���F00120�]7							
�����ԍ��F670031							
�J�j�L���b�`�{�[��							

������A�����������茳�ɓ͂��Ă��Ȃ��ꍇ��
���̑����s���ȓ_�A�������̂����k����
03-6908-6662(9�F00�`18�F00)�܂ł��₢���킹�������B


�����i�ɂ��܂��Ă��A���[���ւȂǂ̔z�����@�̏ꍇ�ɂ́A�z�����̂Ȃǂɂ��
�͂��Ȃ��ꍇ���������܂��B
������A���i���͂��Ă��Ȃ��ꍇ�ɂ͑�ς��萔�ł͂������܂����A
���������ꂽ�X�ܗl�܂Œ��ڂ��⍇�����������܂��B


�����[���ɂĂ��⍇�������������ꍇ�́A
�K�����������̂����O�i�t���l�[���j��
�{���ɓ���Ă��⍇�����������B


���L�����Z���i���\���j����Ă���ꍇ�ł��A
�s���Ⴂ�ɂē����[�����z�M����Ă��܂��ꍇ���������܂��B
���̍ۂ͑�ς��萔�ł����������ꂽ�X�ܗl�ɒ��ڂ��⍇�����������B


�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
�@���ڍw���X�ܗl�ɂ��₢���킹���������B
�@�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}


�����x�����Ɋւ��邨�₢���킹�́F
  ���⍇����F03-6908-6662
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com
  �^�c��ЁF������ЃL���b�`�{�[��  
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F',4,'2015/07/23 15:27:30',9,'2015/12/01 10:58:03',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (384,44,'�x���������߃��[���i�ĂS�j�iCEL�j','������ЃL���b�`�{�[��','=?UTF-8?B?GyRCM3Q8MDJxPFIlLSVjJUMlQSVcITwlaxsoQg==?=','customer2@ato-barai.com',null,null,null,'�y���m�F���������z�F{OrderDate}�@{SiteNameKj}�ł̂��������̌�({OrderId})','=?UTF-8?B?5qCq5byP5Lya56S+44Kt44Oj44OD44OB44Oc44O844Or?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɂ������̂��葱�������ς̂悤�ł����
�@�����[���ւ̕ԐM�͂��s�v�ł������܂��B

{CustomerNameKj}�l

����� {SiteNameKj}�ł������������Ē���
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ClaimDate}�ɓ���������肢�����܂������A
�{�����݂������̊m�F���ł��Ă���܂���B

���܂��ẮA���[���̂��x�����ɂ�
���}���Ή����������܂��悤���肢�������܂��B

�y���������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
�Đ����ǉ��萔���F{ReClaimFee}
�x�����Q���F{DamageInterest}
���������z�F{TotalAmount}

��������A���x���������߂��Ă��܂��܂���
�Đ����萔�������Z����܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ł������܂�)
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z							
�O��Z�F��s�@�V�h�ʎx�X
���ʌ����@8047001
�J�j�L���b�`�{�[��				

�y�X�֐U�֌����z							
�����L���F00120�]7							
�����ԍ��F670031							
�J�j�L���b�`�{�[��							

������A�����������茳�ɓ͂��Ă��Ȃ��ꍇ��
���̑����s���ȓ_�A�������̂����k����
03-6908-6662(9�F00�`18�F00)�܂ł��₢���킹�������B


�����i�ɂ��܂��Ă��A���[���ւȂǂ̔z�����@�̏ꍇ�ɂ́A�z�����̂Ȃǂɂ��
�͂��Ȃ��ꍇ���������܂��B
������A���i���͂��Ă��Ȃ��ꍇ�ɂ͑�ς��萔�ł͂������܂����A
���������ꂽ�X�ܗl�܂Œ��ڂ��⍇�����������܂��B


�����[���ɂĂ��⍇�������������ꍇ�́A
�K�����������̂����O�i�t���l�[���j��
�{���ɓ���Ă��⍇�����������B


���L�����Z���i���\���j����Ă���ꍇ�ł��A
�s���Ⴂ�ɂē����[�����z�M����Ă��܂��ꍇ���������܂��B
���̍ۂ͑�ς��萔�ł����������ꂽ�X�ܗl�ɒ��ڂ��⍇�����������B


�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
�@���ڍw���X�ܗl�ɂ��₢���킹���������B
�@�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}


�����x�����Ɋւ��邨�₢���킹�́F
  ���⍇����F03-6908-6662
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com
  �^�c��ЁF������ЃL���b�`�{�[��  
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F',4,'2015/07/23 15:27:30',9,'2015/12/01 10:57:37',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (385,45,'�x���������߃��[���i�ĂT�j�iPC�j','������ЃL���b�`�{�[��','=?UTF-8?B?GyRCM3Q8MDJxPFIlLSVjJUMlQSVcITwlaxsoQg==?=','customer2@ato-barai.com',null,null,null,'�y���m�F���������z�F{OrderDate}�@{SiteNameKj}�ł̂��������̌�({OrderId})','=?UTF-8?B?5qCq5byP5Lya56S+44Kt44Oj44OD44OB44Oc44O844Or?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɂ������̂��葱�������ς̂悤�ł����
�@�����[���ւ̕ԐM�͂��s�v�ł������܂��B

{CustomerNameKj}�l

�����{SiteNameKj}�l�ł������������Ē���
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ClaimDate}�ɓ���������肢�����܂������A
�{�����݂������̊m�F���ł��Ă���܂���B


��L����ł����m�点���Ă���ʂ�A
���x�������m�F�ł��Ȃ��ꍇ
���q�l�̐M�p����ȂǕs���v��������\�����������܂��B
���܂��Ă͑��₩�Ȃ��Ή������肢�������܂��B

�y���������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
�Đ����ǉ��萔���F{ReClaimFee}
�x�����Q���F{DamageInterest}
���������z�F{TotalAmount}

��������A���x���������߂��Ă��܂��܂��ƁA
�Đ����萔�������Z����܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ł������܂�)
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z							
�O��Z�F��s�@�V�h�ʎx�X
���ʌ����@8047001
�J�j�L���b�`�{�[��				

�y�X�֐U�֌����z							
�����L���F00120�]7							
�����ԍ��F670031							
�J�j�L���b�`�{�[��							

������A�����������茳�ɓ͂��Ă��Ȃ��ꍇ��
���̑����s���ȓ_�A�������̂����k����
03-6908-6662(9�F00�`18�F00)�܂ł��₢���킹�������B


�����i�ɂ��܂��Ă��A���[���ւȂǂ̔z�����@�̏ꍇ�ɂ́A�z�����̂Ȃǂɂ��
�͂��Ȃ��ꍇ���������܂��B
������A���i���͂��Ă��Ȃ��ꍇ�ɂ͑�ς��萔�ł͂������܂����A
���������ꂽ�X�ܗl�܂Œ��ڂ��⍇�����������܂��B


�����[���ɂĂ��⍇�������������ꍇ�́A
�K�����������̂����O�i�t���l�[���j��
�{���ɓ���Ă��⍇�����������B


���L�����Z���i���\���j����Ă���ꍇ�ł��A
�s���Ⴂ�ɂē����[�����z�M����Ă��܂��ꍇ���������܂��B
���̍ۂ͑�ς��萔�ł����������ꂽ�X�ܗl�ɒ��ڂ��⍇�����������B


�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
�@���ڍw���X�ܗl�ɂ��₢���킹���������B
�@�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}


�����x�����Ɋւ��邨�₢���킹�́F
  ���⍇����F03-6908-6662
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com
  �^�c��ЁF������ЃL���b�`�{�[��  
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F',4,'2015/07/23 15:27:30',9,'2015/12/01 10:57:23',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (386,46,'�x���������߃��[���i�ĂT�j�iCEL�j','������ЃL���b�`�{�[��','=?UTF-8?B?GyRCM3Q8MDJxPFIlLSVjJUMlQSVcITwlaxsoQg==?=','customer2@ato-barai.com',null,null,null,'�y���m�F���������z�F{OrderDate}�@{SiteNameKj}�ł̂��������̌�({OrderId})','=?UTF-8?B?5qCq5byP5Lya56S+44Kt44Oj44OD44OB44Oc44O844Or?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɂ������̂��葱�������ς̂悤�ł����
�@�����[���ւ̕ԐM�͂��s�v�ł������܂��B

{CustomerNameKj}�l

�����{SiteNameKj}�l�ł������������Ē���
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ClaimDate}�ɓ���������肢�����܂������A
�{�����݂������̊m�F���ł��Ă���܂���B


��L����ł����m�点���Ă���ʂ�A
���x�������m�F�ł��Ȃ��ꍇ
���q�l�̐M�p����ȂǕs���v��������\�����������܂��B
���܂��Ă͑��₩�Ȃ��Ή������肢�������܂��B

�y���������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
�Đ����ǉ��萔���F{ReClaimFee}
�x�����Q���F{DamageInterest}
���������z�F{TotalAmount}

��������A���x���������߂��Ă��܂��܂���
�Đ����萔�������Z����܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ł������܂�)
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z							
�O��Z�F��s�@�V�h�ʎx�X
���ʌ����@8047001
�J�j�L���b�`�{�[��				

�y�X�֐U�֌����z							
�����L���F00120�]7							
�����ԍ��F670031							
�J�j�L���b�`�{�[��							

������A�����������茳�ɓ͂��Ă��Ȃ��ꍇ��
���̑����s���ȓ_�A�������̂����k����
03-6908-6662(9�F00�`18�F00)�܂ł��₢���킹�������B


�����i�ɂ��܂��Ă��A���[���ւȂǂ̔z�����@�̏ꍇ�ɂ́A�z�����̂Ȃǂɂ��
�͂��Ȃ��ꍇ���������܂��B
������A���i���͂��Ă��Ȃ��ꍇ�ɂ͑�ς��萔�ł͂������܂����A
���������ꂽ�X�ܗl�܂Œ��ڂ��⍇�����������܂��B


�����[���ɂĂ��⍇�������������ꍇ�́A
�K�����������̂����O�i�t���l�[���j��
�{���ɓ���Ă��⍇�����������B


���L�����Z���i���\���j����Ă���ꍇ�ł��A
�s���Ⴂ�ɂē����[�����z�M����Ă��܂��ꍇ���������܂��B
���̍ۂ͑�ς��萔�ł����������ꂽ�X�ܗl�ɒ��ڂ��⍇�����������B


�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
�@���ڍw���X�ܗl�ɂ��₢���킹���������B
�@�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}


�����x�����Ɋւ��邨�₢���킹�́F
  ���⍇����F03-6908-6662
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com
  �^�c��ЁF������ЃL���b�`�{�[��  
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F',4,'2015/07/23 15:27:30',9,'2015/12/01 10:57:03',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (387,47,'�x���������߃��[���i�ĂU�j�iPC�j','������ЃL���b�`�{�[��','=?UTF-8?B?GyRCM3Q8MDJxPFIlLSVjJUMlQSVcITwlaxsoQg==?=','customer2@ato-barai.com',null,null,null,'�y���m�F���������z�F{OrderDate}�@{SiteNameKj}�ł̂��������̌�({OrderId})','=?UTF-8?B?5qCq5byP5Lya56S+44Kt44Oj44OD44OB44Oc44O844Or?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɂ������̂��葱�������ς̂悤�ł����
�@�����[���ւ̕ԐM�͂��s�v�ł������܂��B

{CustomerNameKj}�l

�����{SiteNameKj}�l�ł������������Ē���
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ClaimDate}�ɓ���������肢�����܂������A
�{�����݂������̊m�F���ł��Ă���܂���B

���̂܂ܖ�������Ԃ��p������܂��ƁA
���Ђł̑Ή�������ƂȂ�
�ʒm�L�ڂ̑Ή��ƂȂ�ꍇ������܂��B
���܂��Ă͎��}���x�����ɂ���
���Ή����������܂��悤���肢�������܂��B

�y���������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
�Đ����ǉ��萔���F{ReClaimFee}
�x�����Q���F{DamageInterest}
���������z�F{TotalAmount}

��������A���x���������߂��Ă��܂��܂��ƁA
�Đ����萔�������Z����܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ł������܂�)
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z							
�O��Z�F��s�@�V�h�ʎx�X
���ʌ����@8047001
�J�j�L���b�`�{�[��				

�y�X�֐U�֌����z							
�����L���F00120�]7							
�����ԍ��F670031							
�J�j�L���b�`�{�[��							

������A�����������茳�ɓ͂��Ă��Ȃ��ꍇ��
���̑����s���ȓ_�A�������̂����k����
03-6908-6662(9�F00�`18�F00)�܂ł��₢���킹�������B


�����i�ɂ��܂��Ă��A���[���ւȂǂ̔z�����@�̏ꍇ�ɂ́A�z�����̂Ȃǂɂ��
�͂��Ȃ��ꍇ���������܂��B
������A���i���͂��Ă��Ȃ��ꍇ�ɂ͑�ς��萔�ł͂������܂����A
���������ꂽ�X�ܗl�܂Œ��ڂ��⍇�����������܂��B


�����[���ɂĂ��⍇�������������ꍇ�́A
�K�����������̂����O�i�t���l�[���j��
�{���ɓ���Ă��⍇�����������B


���L�����Z���i���\���j����Ă���ꍇ�ł��A
�s���Ⴂ�ɂē����[�����z�M����Ă��܂��ꍇ���������܂��B
���̍ۂ͑�ς��萔�ł����������ꂽ�X�ܗl�ɒ��ڂ��⍇�����������B


�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
�@���ڍw���X�ܗl�ɂ��₢���킹���������B
�@�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}


�����x�����Ɋւ��邨�₢���킹�́F
  ���⍇����F03-6908-6662
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com
  �^�c��ЁF������ЃL���b�`�{�[��  
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F',4,'2015/07/23 15:27:30',9,'2015/12/01 10:50:22',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (388,48,'�x���������߃��[���i�ĂU�j�iCEL�j','������ЃL���b�`�{�[��','=?UTF-8?B?GyRCM3Q8MDJxPFIlLSVjJUMlQSVcITwlaxsoQg==?=','customer2@ato-barai.com',null,null,null,'�y���m�F���������z�F{OrderDate}�@{SiteNameKj}�ł̂��������̌�({OrderId})','=?UTF-8?B?5qCq5byP5Lya56S+44Kt44Oj44OD44OB44Oc44O844Or?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɂ������̂��葱�������ς̂悤�ł����
�@�����[���ւ̕ԐM�͂��s�v�ł������܂��B

{CustomerNameKj}�l

�����{SiteNameKj}�l�ł������������Ē���
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ClaimDate}�ɓ���������肢�����܂������A
�{�����݂������̊m�F���ł��Ă���܂���B

���̂܂ܖ�������Ԃ��p������܂��ƁA
���Ђł̑Ή�������ƂȂ�
�ʒm�L�ڂ̑Ή��ƂȂ�ꍇ������܂��B
���܂��Ă͎��}���x�����ɂ���
���Ή����������܂��悤���肢�������܂��B

�y���������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
�Đ����ǉ��萔���F{ReClaimFee}
�x�����Q���F{DamageInterest}
���������z�F{TotalAmount}

��������A���x���������߂��Ă��܂��܂��ƁA
�Đ����萔�������Z����܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ł������܂�)
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z							
�O��Z�F��s�@�V�h�ʎx�X
���ʌ����@8047001
�J�j�L���b�`�{�[��				

�y�X�֐U�֌����z							
�����L���F00120�]7							
�����ԍ��F670031							
�J�j�L���b�`�{�[��							

������A�����������茳�ɓ͂��Ă��Ȃ��ꍇ��
���̑����s���ȓ_�A�������̂����k����
03-6908-6662(9�F00�`18�F00)�܂ł��₢���킹�������B


�����i�ɂ��܂��Ă��A���[���ւȂǂ̔z�����@�̏ꍇ�ɂ́A�z�����̂Ȃǂɂ��
�͂��Ȃ��ꍇ���������܂��B
������A���i���͂��Ă��Ȃ��ꍇ�ɂ͑�ς��萔�ł͂������܂����A
���������ꂽ�X�ܗl�܂Œ��ڂ��⍇�����������܂��B


�����[���ɂĂ��⍇�������������ꍇ�́A
�K�����������̂����O�i�t���l�[���j��
�{���ɓ���Ă��⍇�����������B


���L�����Z���i���\���j����Ă���ꍇ�ł��A
�s���Ⴂ�ɂē����[�����z�M����Ă��܂��ꍇ���������܂��B
���̍ۂ͑�ς��萔�ł����������ꂽ�X�ܗl�ɒ��ڂ��⍇�����������B


�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
�@���ڍw���X�ܗl�ɂ��₢���킹���������B
�@�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}


�����x�����Ɋւ��邨�₢���킹�́F
  ���⍇����F03-6908-6662
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com
  �^�c��ЁF������ЃL���b�`�{�[��  
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F',4,'2015/07/23 15:27:30',9,'2015/12/01 10:50:46',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (389,49,'�x���������߃��[���i�ĂV�j�iPC�j','������ЃL���b�`�{�[��','=?UTF-8?B?GyRCM3Q8MDJxPFIlLSVjJUMlQSVcITwlaxsoQg==?=','customer2@ato-barai.com',null,null,null,'�y���m�F���������z�F{OrderDate}�@{SiteNameKj}�ł̂��������̌�({OrderId})','=?UTF-8?B?5qCq5byP5Lya56S+44Kt44Oj44OD44OB44Oc44O844Or?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɂ������̂��葱�������ς̂悤�ł����
�@�����[���ւ̕ԐM�͂��s�v�ł������܂��B

{CustomerNameKj}�l

�����{SiteNameKj}�l�ł������������Ē���
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ClaimDate}�ɓ���������肢�����܂������A
�{�����݂������̊m�F���ł��Ă���܂���B

�ĎO�ɂ킽��A���ԍςɑ΂����s�����悤���ʒm�������܂�����
�M�a��落�ӂ̂��邲�Ή��𒸂��Ă��Ȃ��󋵂ƂȂ��Ă���܂��B
����ɂ��܂��Ă��A���A���E���x�������m�F�ł��Ȃ��ꍇ��
�ٌ�m�ւ̉���ϔC�������͖@�I�葱���Ɉڍs�����邨���܂���B
�������Ȃ���A���Ѝ��Ǘ����ł�
�M�a�̍����s�ɑ΂�������}��ׂ̑��k������݂��Ă���
���k�ɂ��������\�ȏꍇ���������܂��B
���܂��ẮA�����Ɍ������}���A�����������܂��悤���肢�������܂��B

�y���������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
�Đ����ǉ��萔���F{ReClaimFee}
�x�����Q���F{DamageInterest}
���������z�F{TotalAmount}

��������A���x���������߂��Ă��܂��܂��ƁA
�Đ����萔�������Z����܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ł������܂�)
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z							
�O��Z�F��s�@�V�h�ʎx�X
���ʌ����@8047001
�J�j�L���b�`�{�[��				

�y�X�֐U�֌����z							
�����L���F00120�]7							
�����ԍ��F670031							
�J�j�L���b�`�{�[��							

������A�����������茳�ɓ͂��Ă��Ȃ��ꍇ��
���̑����s���ȓ_�A�������̂����k����
03-6908-6662(9�F00�`18�F00)�܂ł��₢���킹�������B


�����i�ɂ��܂��Ă��A���[���ւȂǂ̔z�����@�̏ꍇ�ɂ́A�z�����̂Ȃǂɂ��
�͂��Ȃ��ꍇ���������܂��B
������A���i���͂��Ă��Ȃ��ꍇ�ɂ͑�ς��萔�ł͂������܂����A
���������ꂽ�X�ܗl�܂Œ��ڂ��⍇�����������܂��B


�����[���ɂĂ��⍇�������������ꍇ�́A
�K�����������̂����O�i�t���l�[���j��
�{���ɓ���Ă��⍇�����������B


���L�����Z���i���\���j����Ă���ꍇ�ł��A
�s���Ⴂ�ɂē����[�����z�M����Ă��܂��ꍇ���������܂��B
���̍ۂ͑�ς��萔�ł����������ꂽ�X�ܗl�ɒ��ڂ��⍇�����������B


�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
�@���ڍw���X�ܗl�ɂ��₢���킹���������B
�@�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}


�����x�����Ɋւ��邨�₢���킹�́F
  ���⍇����F03-6908-6662
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com
  �^�c��ЁF������ЃL���b�`�{�[��  
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F',4,'2015/07/23 15:27:30',9,'2015/12/01 10:51:44',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (390,50,'�x���������߃��[���i�ĂV�j�iCEL�j','������ЃL���b�`�{�[��','=?UTF-8?B?GyRCM3Q8MDJxPFIlLSVjJUMlQSVcITwlaxsoQg==?=','customer2@ato-barai.com',null,null,null,'�y���m�F���������z�F{OrderDate}�@{SiteNameKj}�ł̂��������̌�({OrderId})','=?UTF-8?B?5qCq5byP5Lya56S+44Kt44Oj44OD44OB44Oc44O844Or?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɂ������̂��葱�������ς̂悤�ł����
�@�����[���ւ̕ԐM�͂��s�v�ł������܂��B

{CustomerNameKj}�l

�����{SiteNameKj}�l�ł������������Ē���
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ClaimDate}�ɓ���������肢�����܂������A
�{�����݂������̊m�F���ł��Ă���܂���B

�ĎO�ɂ킽��A���ԍςɑ΂����s�����悤���ʒm�������܂�����
�M�a��落�ӂ̂��邲�Ή��𒸂��Ă��Ȃ��󋵂ƂȂ��Ă���܂��B
����ɂ��܂��Ă��A���A���E���x�������m�F�ł��Ȃ��ꍇ��
�ٌ�m�ւ̉���ϔC�������͖@�I�葱���Ɉڍs�����邨���܂���B
�������Ȃ���A���Ѝ��Ǘ����ł�
�M�a�̍����s�ɑ΂�������}��ׂ̑��k������݂��Ă���
���k�ɂ��������\�ȏꍇ���������܂��B
���܂��ẮA�����Ɍ������}���A�����������܂��悤���肢�������܂��B

�y���������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
�Đ����ǉ��萔���F{ReClaimFee}
�x�����Q���F{DamageInterest}
���������z�F{TotalAmount}

��������A���x���������߂��Ă��܂��܂��ƁA
�Đ����萔�������Z����܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ł������܂�)
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z							
�O��Z�F��s�@�V�h�ʎx�X
���ʌ����@8047001
�J�j�L���b�`�{�[��				

�y�X�֐U�֌����z							
�����L���F00120�]7							
�����ԍ��F670031							
�J�j�L���b�`�{�[��							

������A�����������茳�ɓ͂��Ă��Ȃ��ꍇ��
���̑����s���ȓ_�A�������̂����k����
03-6908-6662(9�F00�`18�F00)�܂ł��₢���킹�������B


�����i�ɂ��܂��Ă��A���[���ւȂǂ̔z�����@�̏ꍇ�ɂ́A�z�����̂Ȃǂɂ��
�͂��Ȃ��ꍇ���������܂��B
������A���i���͂��Ă��Ȃ��ꍇ�ɂ͑�ς��萔�ł͂������܂����A
���������ꂽ�X�ܗl�܂Œ��ڂ��⍇�����������܂��B


�����[���ɂĂ��⍇�������������ꍇ�́A
�K�����������̂����O�i�t���l�[���j��
�{���ɓ���Ă��⍇�����������B


���L�����Z���i���\���j����Ă���ꍇ�ł��A
�s���Ⴂ�ɂē����[�����z�M����Ă��܂��ꍇ���������܂��B
���̍ۂ͑�ς��萔�ł����������ꂽ�X�ܗl�ɒ��ڂ��⍇�����������B


�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
�@���ڍw���X�ܗl�ɂ��₢���킹���������B
�@�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}


�����x�����Ɋւ��邨�₢���킹�́F
  ���⍇����F03-6908-6662
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com
  �^�c��ЁF������ЃL���b�`�{�[��  
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F',4,'2015/07/23 15:27:30',9,'2015/12/01 10:52:16',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (402,81,'�s�������A�����[���iPC�j','�yBASE�㕥�����ρz','=?UTF-8?B?GyRCIVobKEJCQVNFGyRCOGVKJyQkN2g6USFbGyhC?=','atobarai@thebase.in',null,null,null,'�yBASE�㕥�����ρz�������z���s�����Ă���܂�','=?UTF-8?B?44CQQkFTReW+jOaJleOBhOaxuua4iOOAkQ==?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

{CustomerNameKj}�l

�����{SiteNameKj}�l�ł������������Ē���
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ReceiptDate}��{ReceiptClass}���{UseAmount}�~�̂����������������܂������A
{ShortfallAmount}�~���s���ƂȂ��Ă���܂��B

��ς��萔�ł����s������{ShortfallAmount}�~��
���L�����܂ł��U���݂��������܂��悤���肢�������܂��B

�y��s�U�������z
�W���p���l�b�g��s�@���~�W�x�X
���ʁ@3721018
�a�`�r�d�������
�i�x�C�X�J�u�V�L�K�C�V���j

�y�X�֐U�������z
�L���F001600-8
�ԍ��F450807
������ЃL���b�`�{�[���@�a�`�r�d��p����
�i�J�u�V�L�K�C�V���L���b�`�{�[���@�x�C�X�Z�����E�R�E�U�j

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�@�{���ɓ���Ă��⍇�����������B


�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
���ڍw���X�ܗl�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}


{SiteNameKj}�@ �� BASE ( https://thebase.in ) �ō쐬����Ă��܂��B 
BASE�͒N�ł��ȒP�ɖ����Ńl�b�g�V���b�v���J�݂ł���T�[�r�X�ł��B


�����x�����Ɋւ��邨�₢���킹�́F
BASE �㕥�����ρ@����
TEL:[03-6279-1149](�����y��9:00�`18:00)
Mail: atobarai@thebase.in

����������������������������������������������������������������
BASE (�x�C�X)
https://thebase.in
 ���⍇����:[03-6279-1149]
 �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: atobarai@thebase.in
����������������������������������������������������������������',4,'2015/07/23 15:27:30',9,'2015/12/01 10:53:30',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (403,82,'�s�������A�����[���iCEL�j','�yBASE�㕥�����ρz','=?UTF-8?B?GyRCIVobKEJCQVNFGyRCOGVKJyQkN2g6USFbGyhC?=','atobarai@thebase.in',null,null,null,'�yBASE�㕥�����ρz�������z���s�����Ă���܂�','=?UTF-8?B?44CQQkFTReW+jOaJleOBhOaxuua4iOOAkQ==?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

{CustomerNameKj}�l

�����{SiteNameKj}�l�ł������������Ē���
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ReceiptDate}��{ReceiptClass}���{UseAmount}�~�̂����������������܂������A
{ShortfallAmount}�~���s���ƂȂ��Ă���܂��B

��ς��萔�ł����s������{ShortfallAmount}�~��
���L�����܂ł��U���݂��������܂��悤���肢�������܂��B

�y��s�U�������z
�W���p���l�b�g��s�@���~�W�x�X
���ʁ@3721018
�a�`�r�d�������
�i�x�C�X�J�u�V�L�K�C�V���j

�y�X�֐U�������z
�L���F001600-8
�ԍ��F450807
������ЃL���b�`�{�[���@�a�`�r�d��p����
�i�J�u�V�L�K�C�V���L���b�`�{�[���@�x�C�X�Z�����E�R�E�U�j

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�@�{���ɓ���Ă��⍇�����������B


�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
���ڍw���X�ܗl�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}


{SiteNameKj}�� BASE ( https://thebase.in ) �ō쐬����Ă��܂��B 
BASE�͒N�ł��ȒP�ɖ����Ńl�b�g�V���b�v���J�݂ł���T�[�r�X�ł��B


�����x�����Ɋւ��邨�₢���킹�́F
BASE �㕥�����ρ@����
TEL:[03-6279-1149](�����y��9:00�`18:00)
Mail: atobarai@thebase.in

����������������������������������������������������������������
BASE (�x�C�X)
https://thebase.in
 ���⍇����:[03-6279-1149]
 �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: atobarai@thebase.in
����������������������������������������������������������������',4,'2015/07/23 15:27:30',9,'2015/12/01 10:54:10',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (406,88,'�ԋ����[���i�L�����Z���j(CEL)','�yBASE�㕥�����ρz','=?UTF-8?B?GyRCIVobKEJCQVNFGyRCOGVKJyQkN2g6USFbGyhC?=','atobarai@thebase.in',null,null,null,'�yBASE�㕥�����ρz���ԋ��̂��A��','=?UTF-8?B?44CQQkFTReW+jOaJleOBhOaxuua4iOOAkQ==?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

{CustomerNameKj}�l

�����{SiteNameKj}�l�ł������������Ē���
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ReceiptDate}��{ReceiptClass}��育�������m�F�������܂������A
���ɓX�ܗl��育�����L�����Z���̂��A�������������Ă���܂����̂�
���ԋ������Ă������������A�����̊m�F�̂��A���������グ�܂����B

���萔�ł͂������܂���
�E��s���F
�E�x�X���F
�E������ځF
�E�����ԍ��F
�E�������`(�J�i)�F
��L�����L���̂����A�����[���ւ��ԐM���������܂��B

���s���ȓ_�Ȃǂ������܂�����A���C�y�ɉ��L�܂ł��⍇���������B

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�@�{���ɓ���Ă��⍇�����������B


�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
���ڍw���X�ܗl�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}


{SiteNameKj}�@ �� BASE ( https://thebase.in ) �ō쐬����Ă��܂��B 
BASE�͒N�ł��ȒP�ɖ����Ńl�b�g�V���b�v���J�݂ł���T�[�r�X�ł��B


�����x�����Ɋւ��邨�₢���킹�́F
BASE �㕥�����ρ@����
TEL:[03-6279-1149](�����y��9:00�`18:00)
Mail: atobarai@thebase.in

����������������������������������������������������������������
BASE (�x�C�X)
https://thebase.in
 ���⍇����:[03-6279-1149]
 �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: atobarai@thebase.in
����������������������������������������������������������������',4,'2015/11/06 16:54:46',9,'2015/12/01 10:55:43',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (407,87,'�ԋ����[���i�L�����Z���j(PC)','�yBASE�㕥�����ρz','=?UTF-8?B?GyRCIVobKEJCQVNFGyRCOGVKJyQkN2g6USFbGyhC?=','atobarai@thebase.in',null,null,null,'�yBASE�㕥�����ρz���ԋ��̂��A��','=?UTF-8?B?44CQQkFTReW+jOaJleOBhOaxuua4iOOAkQ==?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

{CustomerNameKj}�l

�����{SiteNameKj}�l�ł������������Ē���
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ReceiptDate}��{ReceiptClass}��育�������m�F�������܂������A
���ɓX�ܗl��育�����L�����Z���̂��A�������������Ă���܂����̂�
���ԋ������Ă������������A�����̊m�F�̂��A���������グ�܂����B

���萔�ł͂������܂���
�E��s���F
�E�x�X���F
�E������ځF
�E�����ԍ��F
�E�������`(�J�i)�F
��L�����L���̂����A�����[���ւ��ԐM���������܂��B

���s���ȓ_�Ȃǂ������܂�����A���C�y�ɉ��L�܂ł��⍇���������B

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�@�{���ɓ���Ă��⍇�����������B


�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
���ڍw���X�ܗl�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}


{SiteNameKj}�@ �� BASE ( https://thebase.in ) �ō쐬����Ă��܂��B 
BASE�͒N�ł��ȒP�ɖ����Ńl�b�g�V���b�v���J�݂ł���T�[�r�X�ł��B


�����x�����Ɋւ��邨�₢���킹�́F
BASE �㕥�����ρ@����
TEL:[03-6279-1149](�����y��9:00�`18:00)
Mail: atobarai@thebase.in

����������������������������������������������������������������
BASE (�x�C�X)
https://thebase.in
 ���⍇����:[03-6279-1149]
 �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: atobarai@thebase.in
����������������������������������������������������������������',4,'2015/11/06 16:54:45',9,'2015/12/01 10:55:13',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (412,32,'�������j�����[���iPC�j','�d�X�g�A�[�@�㕥������','=?UTF-8?B?GyRCI0UlOSVIJSIhPCEhOGVKJyQkQWs4fRsoQg==?=','ato-barai.sp@estore.co.jp',null,null,null,'�y���A���z�������j���̂��肢�i{OrderId}�j','=?UTF-8?B?77yl44K544OI44Ki44O844CA5b6M5omV44GE56qT5Y+j?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

{CustomerNameKj}�l

����͂��������������܂��āA���ɂ��肪�Ƃ��������܂��B

{CancelDate}��{SiteNameKj}���㕥��������
�L�����Z���̂��A�������������܂������A
���ɐ������������肵�Ă��܂��Ă���܂��̂ŁA
��ς��萔�ł͂������܂����j�����Ă��������悤���肢�������܂��B

���̓x��{SiteNameKj}�ƕ��Ќ㕥���T�[�r�X�������p��������
�܂��Ƃɂ��肪�Ƃ��������܂����B

����Ƃ������A��낵�����肢�������܂��B


�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
�@���ڍw���X�ܗl�ɂ��₢���킹���������B
�@�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}


�����x�����Ɋւ��邨�₢���킹�́F
  ���⍇����F03-6908-5100
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: ato-barai.sp@estore.co.j
  �^�c��ЁF������Ђd�X�g�A�[�@�㕥������  
�@�Z���F��105-0003 �����s�`�搼�V��1-10-2�@�Z�F�������V���r��9F
',1,'2015/12/01 12:22:35',32,'2015/12/01 12:23:44',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (413,33,'�������j�����[���iCEL�j','�d�X�g�A�[�@�㕥������','=?UTF-8?B?GyRCI0UlOSVIJSIhPCEhOGVKJyQkQWs4fRsoQg==?=','ato-barai.sp@estore.co.jp',null,null,null,'�y���A���z�������j���̂��肢�i{OrderId}�j','=?UTF-8?B?77yl44K544OI44Ki44O844CA5b6M5omV44GE56qT5Y+j?=','
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������


{CustomerNameKj}�l

����͂��������������܂��āA���ɂ��肪�Ƃ��������܂��B

{CancelDate}��{SiteNameKj}���㕥��������
�L�����Z���̂��A�������������܂������A
���ɐ������������肵�Ă��܂��Ă���܂��̂ŁA
��ς��萔�ł͂������܂����j�����Ă��������悤���肢�������܂��B

���̓x��{SiteNameKj}�ƕ��Ќ㕥���T�[�r�X�������p��������
�܂��Ƃɂ��肪�Ƃ��������܂����B

����Ƃ������A��낵�����肢�������܂��B


�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
�@���ڍw���X�ܗl�ɂ��₢���킹���������B
�@�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}


�����x�����Ɋւ��邨�₢���킹�́F
  ���⍇����F03-6908-5100
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: ato-barai.sp@estore.co.j
  �^�c��ЁF������Ђd�X�g�A�[�@�㕥������  
�@�Z���F��105-0003 �����s�`�搼�V��1-10-2�@�Z�F�������V���r��9F
',1,'2015/12/01 12:24:08',32,'2015/12/01 12:50:25',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (414,34,'�ߏ�������[���iPC�j','�d�X�g�A�[�@�㕥������','=?UTF-8?B?GyRCI0UlOSVIJSIhPCEhOGVKJyQkQWs4fRsoQg==?=','ato-barai.sp@estore.co.jp',null,null,null,'�y���m�F�z���ԋ��̂��A��','=?UTF-8?B?77yl44K544OI44Ki44O844CA5b6M5omV44GE56qT5Y+j?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

{CustomerNameKj}�l

����͂��������������܂��āA���ɂ��肪�Ƃ��������܂��B

{ReceiptDate}��{ReceiptClass}��育�������m�F�������܂������A
{OverReceiptAmount}�~�������x�������������Ă���܂����̂�
���ԋ������Ă������������A�����̊m�F�̂��A���������グ�܂����B

���萔�ł͂������܂���
�E��s���F
�E�x�X���F
�E������ځF
�E�����ԍ��F
�E�������`(�J�i)�F
��L�����L���̂����A�����[���ւ��ԐM���������܂��B

������낵�����肢�������܂��B


�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
�@���ڍw���X�ܗl�ɂ��₢���킹���������B
�@�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}


�����x�����Ɋւ��邨�₢���킹�́F
  ���⍇����F03-6908-5100
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: ato-barai.sp@estore.co.j
  �^�c��ЁF������Ђd�X�g�A�[�@�㕥������  
�@�Z���F��105-0003 �����s�`�搼�V��1-10-2�@�Z�F�������V���r��9F
',1,'2015/12/01 12:24:46',32,'2015/12/01 12:24:46',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (415,35,'�ߏ�������[���iCEL�j','�d�X�g�A�[�@�㕥������','=?UTF-8?B?GyRCI0UlOSVIJSIhPCEhOGVKJyQkQWs4fRsoQg==?=','ato-barai.sp@estore.co.jp',null,null,null,'�y���m�F�z���ԋ��̂��A��','=?UTF-8?B?77yl44K544OI44Ki44O844CA5b6M5omV44GE56qT5Y+j?=','
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������


{CustomerNameKj}�l

����͂��������������܂��āA���ɂ��肪�Ƃ��������܂��B

{ReceiptDate}��{ReceiptClass}��育�������m�F�������܂������A
{OverReceiptAmount}�~�������x�������������Ă���܂����̂�
���ԋ������Ă������������A�����̊m�F�̂��A���������グ�܂����B

���萔�ł͂������܂���
�E��s���F
�E�x�X���F
�E������ځF
�E�����ԍ��F
�E�������`(�J�i)�F
��L�����L���̂����A�����[���ւ��ԐM���������܂��B

������낵�����肢�������܂��B


�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
�@���ڍw���X�ܗl�ɂ��₢���킹���������B
�@�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}


�����x�����Ɋւ��邨�₢���킹�́F
  ���⍇����F03-6908-5100
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: ato-barai.sp@estore.co.j
  �^�c��ЁF������Ђd�X�g�A�[�@�㕥������  
�@�Z���F��105-0003 �����s�`�搼�V��1-10-2�@�Z�F�������V���r��9F
',1,'2015/12/01 12:25:09',32,'2015/12/01 12:50:43',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (416,39,'�x���������߃��[���i�ĂP�j�iPC�j','','','',null,null,null,'','','',1,'2015/12/01 12:31:18',32,'2015/12/01 12:44:14',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (417,40,'�x���������߃��[���i�ĂP�j�iCEL�j','','','',null,null,null,'','','',1,'2015/12/01 12:31:53',32,'2015/12/01 12:44:00',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (418,41,'�x���������߃��[���i�ĂR�j�iPC�j','','','',null,null,null,'','','',1,'2015/12/01 12:41:45',32,'2015/12/01 12:43:36',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (419,81,'�s�������A�����[���iPC�j','�d�X�g�A�[�@�㕥������','=?UTF-8?B?GyRCI0UlOSVIJSIhPCEhOGVKJyQkQWs4fRsoQg==?=','ato-barai.sp@estore.co.jp',null,null,null,'�y���A���z�������z���s�����Ă���܂�','=?UTF-8?B?77yl44K544OI44Ki44O844CA5b6M5omV44GE56qT5Y+j?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

{CustomerNameKj}�l

����͂��������������܂��āA���ɂ��肪�Ƃ��������܂��B

{ReceiptDate}��{ReceiptClass}���{UseAmount}�~�̂����������������܂������A
{ShortfallAmount}�~���s���ƂȂ��Ă���܂��B

��ς��萔�ł����s������{ShortfallAmount}�~��
���L�����܂ł��U���݂��������܂��悤���肢�������܂��B

�y��s�U�������z
�W���p���l�b�g��s �����ߎx�X
���ʗa���@6291494
������ЃL���b�`�{�[���^�d�X�g�A�[��p����

�y�X�֐U�֌����z
�����L���F00140-5
�����ԍ��F665145
������ЃL���b�`�{�[���@�d�X�g�A�[��p

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�{���ɓ���Ă��⍇�����������B


�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
�@���ڍw���X�ܗl�ɂ��₢���킹���������B
�@�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}


�����x�����Ɋւ��邨�₢���킹�́F
  ���⍇����F03-6908-5100
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: ato-barai.sp@estore.co.jp
  �^�c��ЁF������Ђd�X�g�A�[�@�㕥������ 
  �Z���F��105-0003�@�����s�`�搼�V��1-10-2  �Z�F�������V���r���XF
',1,'2015/12/01 12:51:38',32,'2015/12/01 15:06:22',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (420,82,'�s�������A�����[���iCEL�j','�d�X�g�A�[�@�㕥������','=?UTF-8?B?GyRCI0UlOSVIJSIhPCEhOGVKJyQkQWs4fRsoQg==?=','ato-barai.sp@estore.co.jp',null,null,null,'�y���A���z�������z���s�����Ă���܂�','=?UTF-8?B?77yl44K544OI44Ki44O844CA5b6M5omV44GE56qT5Y+j?=','�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������


{CustomerNameKj}�l

����͂��������������܂��āA���ɂ��肪�Ƃ��������܂��B

{ReceiptDate}��{ReceiptClass}���{UseAmount}�~�̂����������������܂������A
{ShortfallAmount}�~���s���ƂȂ��Ă���܂��B

��ς��萔�ł����s������{ShortfallAmount}�~��
���L�����܂ł��U���݂��������܂��悤���肢�������܂��B

�y��s�U�������z
�W���p���l�b�g��s �����ߎx�X
���ʗa���@6291494
������ЃL���b�`�{�[���^�d�X�g�A�[��p����

�y�X�֐U�֌����z
�����L���F00140-5
�����ԍ��F665145
������ЃL���b�`�{�[���@�d�X�g�A�[��p

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�{���ɓ���Ă��⍇�����������B


�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
�@���ڍw���X�ܗl�ɂ��₢���킹���������B
�@�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}


�����x�����Ɋւ��邨�₢���킹�́F
  ���⍇����F03-6908-5100
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: ato-barai.sp@estore.co.jp
  �^�c��ЁF������Ђd�X�g�A�[�@�㕥������ 
  �Z���F��105-0003�@�����s�`�搼�V��1-10-2  �Z�F�������V���r���XF
',1,'2015/12/01 12:52:10',32,'2015/12/01 15:07:04',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (421,87,'�ԋ����[���i�L�����Z���j(PC)','ato-barai.sp@estore.co.jp','ato-barai.sp@estore.co.jp','�d�X�g�A�[�@�㕥������',null,null,null,'�y���A���z���ԋ��̂��A��','ato-barai.sp@estore.co.jp','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

{CustomerNameKj}�l

����͂��������������܂��āA���ɂ��肪�Ƃ��������܂��B

{ReceiptDate}��{ReceiptClass}��育�������m�F�������܂������A
���ɓX�ܗl��育�����L�����Z���̂��A�������������Ă���܂����̂�
���ԋ������Ă������������A�����̊m�F�̂��A���������グ�܂����B

���萔�ł͂������܂���
�E��s���F
�E�x�X���F
�E������ځF
�E�����ԍ��F
�E�������`(�J�i)�F
��L�����L���̂����A�����[���ւ��ԐM���������܂��B

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�{���ɓ���Ă��⍇�����������B


�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
�@���ڍw���X�ܗl�ɂ��₢���킹���������B
�@�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}


�����x�����Ɋւ��邨�₢���킹�́F
  ���⍇����F03-6908-5100
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: ato-barai.sp@estore.co.jp
  �^�c��ЁF������Ђd�X�g�A�[�@�㕥������ 
  �Z���F��105-0003�@�����s�`�搼�V��1-10-2  �Z�F�������V���r���XF
',1,'2015/12/01 12:53:37',32,'2015/12/01 12:53:37',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (422,88,'�ԋ����[���i�L�����Z���j(CEL)','�d�X�g�A�[�@�㕥������','=?UTF-8?B?GyRCI0UlOSVIJSIhPCEhOGVKJyQkQWs4fRsoQg==?=','ato-barai.sp@estore.co.jp',null,null,null,'�y���A���z���ԋ��̂��A��','=?UTF-8?B?77yl44K544OI44Ki44O844CA5b6M5omV44GE56qT5Y+j?=','
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������


{CustomerNameKj}�l

����͂��������������܂��āA���ɂ��肪�Ƃ��������܂��B

{ReceiptDate}��{ReceiptClass}��育�������m�F�������܂������A
���ɓX�ܗl��育�����L�����Z���̂��A�������������Ă���܂����̂�
���ԋ������Ă������������A�����̊m�F�̂��A���������グ�܂����B

���萔�ł͂������܂���
�E��s���F
�E�x�X���F
�E������ځF
�E�����ԍ��F
�E�������`(�J�i)�F
��L�����L���̂����A�����[���ւ��ԐM���������܂��B

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�{���ɓ���Ă��⍇�����������B


�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
�@���ڍw���X�ܗl�ɂ��₢���킹���������B
�@�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}


�����x�����Ɋւ��邨�₢���킹�́F
  ���⍇����F03-6908-5100
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: ato-barai.sp@estore.co.jp
  �^�c��ЁF������Ђd�X�g�A�[�@�㕥������ 
  �Z���F��105-0003�@�����s�`�搼�V��1-10-2  �Z�F�������V���r���XF
',1,'2015/12/01 12:54:03',32,'2015/12/01 12:54:03',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (423,32,'�������j�����[���iPC�j','�Z�C�m�[�t�B�i���V�����㕥������','=?UTF-8?B?GyRCJTslJCVOITwlVSUjJUolcyU3JWMlazhlSickJEFrOH0bKEI=?=','sfc-atobarai@seino.co.jp',null,null,null,'�y�㕥�����σT�[�r�X�z�������j���̂��肢','=?UTF-8?B?44K744Kk44OO44O844OV44Kj44OK44Oz44K344Oj44Or5b6M5omV44GE56qT?=
 =?UTF-8?B?5Y+j?=','��������������������������������������������������������������������������
�����肢�F���₢���킹�����ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������������

 {CustomerNameKj}�l

����� {SiteNameKj}�l�ł̂���������
�y�㕥�����σT�[�r�X�z�������p���������܂��āA
�܂��Ƃɂ��肪�Ƃ��������܂��B

{CancelDate}��{SiteNameKj}���y�㕥�����σT�[�r�X�z������
�L�����Z���̂��A�������������܂������A
���ɐ������������肵�Ă��܂��Ă���܂��̂ŁA
��ς��萔�ł͂������܂����j�����Ă��������悤���肢�������܂��B

���̓x��{SiteNameKj}�Ɓy�㕥�����σT�[�r�X�z�������p��������
�܂��Ƃɂ��肪�Ƃ��������܂����B

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�@�{���ɓ���Ă��⍇�����������B

�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
���ڍw���X�ܗl�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}

�����x�����Ɋւ��邨�₢���킹�́F
�Z�C�m�[�t�B�i���V����������Ё@�㕥�����σT�[�r�X�S��
TEL:03-6908-7888 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
Mail: sfc-atobarai@seino.co.jp

',3,'2015/12/01 13:21:31',32,'2015/12/01 13:21:31',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (424,33,'�������j�����[���iCEL�j','�Z�C�m�[�t�B�i���V�����㕥������','=?UTF-8?B?GyRCJTslJCVOITwlVSUjJUolcyU3JWMlazhlSickJEFrOH0bKEI=?=','sfc-atobarai@seino.co.jp',null,null,null,'�y�㕥�����σT�[�r�X�z�������j���̂��肢','=?UTF-8?B?44K744Kk44OO44O844OV44Kj44OK44Oz44K344Oj44Or5b6M5omV44GE56qT?=
 =?UTF-8?B?5Y+j?=','
�����肢�F���₢���킹�����ہA���L���[�����ʂ��c�����܂܂��ԐM����������


 {CustomerNameKj}�l

����� {SiteNameKj}�l�ł̂���������
�y�㕥�����σT�[�r�X�z�������p���������܂��āA
�܂��Ƃɂ��肪�Ƃ��������܂��B

{CancelDate}��{SiteNameKj}���y�㕥�����σT�[�r�X�z������
�L�����Z���̂��A�������������܂������A
���ɐ������������肵�Ă��܂��Ă���܂��̂ŁA
��ς��萔�ł͂������܂����j�����Ă��������悤���肢�������܂��B

���̓x��{SiteNameKj}�Ɓy�㕥�����σT�[�r�X�z�������p��������
�܂��Ƃɂ��肪�Ƃ��������܂����B

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�@�{���ɓ���Ă��⍇�����������B

�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
���ڍw���X�ܗl�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}

�����x�����Ɋւ��邨�₢���킹�́F
�Z�C�m�[�t�B�i���V����������Ё@�㕥�����σT�[�r�X�S��
TEL:03-6908-7888 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
Mail: sfc-atobarai@seino.co.jp

',3,'2015/12/01 13:22:00',32,'2015/12/01 13:22:00',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (425,34,'�ߏ�������[���iPC�j','�Z�C�m�[�t�B�i���V�����㕥������','=?UTF-8?B?GyRCJTslJCVOITwlVSUjJUolcyU3JWMlazhlSickJEFrOH0bKEI=?=','sfc-atobarai@seino.co.jp',null,null,null,'�y�㕥�����σT�[�r�X�z���ԋ��̂��A��','=?UTF-8?B?44K744Kk44OO44O844OV44Kj44OK44Oz44K344Oj44Or5b6M5omV44GE56qT?=
 =?UTF-8?B?5Y+j?=','��������������������������������������������������������������������������
�����肢�F���₢���킹�����ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������������

{CustomerNameKj}�l

����� {SiteNameKj}�l�ł̂���������
�y�㕥�����σT�[�r�X�z�������p���������܂��āA
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ReceiptDate}��{ReceiptClass}��育�������m�F�������܂������A
{OverReceiptAmount}�~�������x�������������Ă���܂����̂�
���ԋ������Ă������������A�����̊m�F�̂��A���������グ�܂����B

���萔�ł͂������܂���
�E��s���F
�E�x�X���F
�E������ځF
�E�����ԍ��F
�E�������`(�J�i)�F
��L�����L���̂����A�����[���ւ��ԐM���������܂��B

���s���ȓ_�Ȃǂ������܂�����A���C�y�ɉ��L�܂ł��⍇���������B

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�@�{���ɓ���Ă��⍇�����������B

�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
���ڍw���X�ܗl�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}

�����x�����Ɋւ��邨�₢���킹�́F
�Z�C�m�[�t�B�i���V����������Ё@�㕥�����σT�[�r�X�S��
TEL:03-6908-7888 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
Mail: sfc-atobarai@seino.co.jp

',3,'2015/12/01 13:22:57',32,'2015/12/24 15:28:52',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (426,35,'�ߏ�������[���iCEL�j','�Z�C�m�[�t�B�i���V�����㕥������ ','=?UTF-8?B?GyRCJTslJCVOITwlVSUjJUolcyU3JWMlazhlSickJEFrOH0bKEIg?=','sfc-atobarai@seino.co.jp',null,null,null,'�y�㕥�����σT�[�r�X�z���ԋ��̂��A��','=?UTF-8?B?44K744Kk44OO44O844OV44Kj44OK44Oz44K344Oj44Or5b6M5omV44GE56qT?=
 =?UTF-8?B?5Y+jIA==?=','
�����肢�F���₢���킹�����ہA���L���[�����ʂ��c�����܂܂��ԐM����������


 {CustomerNameKj}�l

����� {SiteNameKj}�l�ł̂���������
�y�㕥�����σT�[�r�X�z�������p���������܂��āA
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ReceiptDate}��{ReceiptClass}��育�������m�F�������܂������A
{OverReceiptAmount}�~�������x�������������Ă���܂����̂�
���ԋ������Ă������������A�����̊m�F�̂��A���������グ�܂����B

���萔�ł͂������܂���
�E��s���F
�E�x�X���F
�E������ځF
�E�����ԍ��F
�E�������`(�J�i)�F
��L�����L���̂����A�����[���ւ��ԐM���������܂��B

���s���ȓ_�Ȃǂ������܂�����A���C�y�ɉ��L�܂ł��⍇���������B

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�@�{���ɓ���Ă��⍇�����������B

�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
���ڍw���X�ܗl�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}

�����x�����Ɋւ��邨�₢���킹�́F
�Z�C�m�[�t�B�i���V����������Ё@�㕥�����σT�[�r�X�S��
TEL:03-6908-7888 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
Mail: sfc-atobarai@seino.co.jp

',3,'2015/12/01 13:28:13',32,'2015/12/01 13:28:13',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (427,81,'�s�������A�����[���iPC�j','�Z�C�m�[�t�B�i���V�����㕥������','=?UTF-8?B?GyRCJTslJCVOITwlVSUjJUolcyU3JWMlazhlSickJEFrOH0bKEI=?=','sfc-atobarai@seino.co.jp',null,null,null,'�y�㕥�����σT�[�r�X�z�������z���s�����Ă���܂�','=?UTF-8?B?44K744Kk44OO44O844OV44Kj44OK44Oz44K344Oj44Or5b6M5omV44GE56qT?=
 =?UTF-8?B?5Y+j?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

{CustomerNameKj}�l

���̓x��{SiteNameKj}�ŏ��i���w���̍ۂɁA
�㕥�����σT�[�r�X�������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ReceiptDate}��{ReceiptClass}���{UseAmount}�~�̂����������������܂������A
{ShortfallAmount}�~���s���ƂȂ��Ă���܂��B

��ς��萔�ł����s������{ShortfallAmount}�~��
���L�����܂ł��U���݂��������܂��悤���肢�������܂��B

�y��s�U�������z
�W���p���l�b�g��s�@
���~�W�x�X�@
���ʌ����@0015015
�Z�C�m�[�t�B�i���V�����i�J

�y�X�֐U�֌����z
�����L���F00100-7
�����ԍ��F292043
������ЃL���b�`�{�[���@�Z�C�m�[FC�W

���s���ȓ_�Ȃǂ������܂�����A���C�y�ɉ��L�܂ł��⍇���������B

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�@�{���ɓ���Ă��⍇�����������B

�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
���ڍw���X�ܗl�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}

�����x�����Ɋւ��邨�₢���킹�́F
�Z�C�m�[�t�B�i���V����������Ё@�㕥�����σT�[�r�X�S��
TEL:03-6908-7888 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
Mail: sfc-atobarai@seino.co.jp
',3,'2015/12/01 13:29:25',32,'2015/12/01 13:30:13',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (428,82,'�s�������A�����[���iCEL�j','�Z�C�m�[�t�B�i���V�����㕥������','=?UTF-8?B?GyRCJTslJCVOITwlVSUjJUolcyU3JWMlazhlSickJEFrOH0bKEI=?=','sfc-atobarai@seino.co.jp',null,null,null,'�y�㕥�����σT�[�r�X�z�������z���s�����Ă���܂�','=?UTF-8?B?44K744Kk44OO44O844OV44Kj44OK44Oz44K344Oj44Or5b6M5omV44GE56qT?=
 =?UTF-8?B?5Y+j?=','
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������


{CustomerNameKj}�l

���̓x��{SiteNameKj}�ŏ��i���w���̍ۂɁA
�㕥�����σT�[�r�X�������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ReceiptDate}��{ReceiptClass}���{UseAmount}�~�̂����������������܂������A
{ShortfallAmount}�~���s���ƂȂ��Ă���܂��B

��ς��萔�ł����s������{ShortfallAmount}�~��
���L�����܂ł��U���݂��������܂��悤���肢�������܂��B

�y��s�U�������z
�W���p���l�b�g��s�@
���~�W�x�X�@
���ʌ����@0015015
�Z�C�m�[�t�B�i���V�����i�J

�y�X�֐U�֌����z
�����L���F00100-7
�����ԍ��F292043
������ЃL���b�`�{�[���@�Z�C�m�[FC�W

���s���ȓ_�Ȃǂ������܂�����A���C�y�ɉ��L�܂ł��⍇���������B

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�@�{���ɓ���Ă��⍇�����������B

�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
���ڍw���X�ܗl�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}

�����x�����Ɋւ��邨�₢���킹�́F
�Z�C�m�[�t�B�i���V����������Ё@�㕥�����σT�[�r�X�S��
TEL:03-6908-7888 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
Mail: sfc-atobarai@seino.co.jp
',3,'2015/12/01 13:30:57',32,'2015/12/01 13:30:57',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (429,87,'�ԋ����[���i�L�����Z���j(PC)','�Z�C�m�[�t�B�i���V�����㕥������','=?UTF-8?B?GyRCJTslJCVOITwlVSUjJUolcyU3JWMlazhlSickJEFrOH0bKEI=?=','sfc-atobarai@seino.co.jp',null,null,null,'�y�㕥�����σT�[�r�X�z���ԋ��̂��A��','=?UTF-8?B?44K744Kk44OO44O844OV44Kj44OK44Oz44K344Oj44Or5b6M5omV44GE56qT?=
 =?UTF-8?B?5Y+j?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

{CustomerNameKj}�l

���̓x��{SiteNameKj}�ŏ��i���w���̍ۂɁA
�㕥�����σT�[�r�X�������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂����B

{ReceiptDate}��{ReceiptClass}��育�������m�F�������܂������A
���ɓX�ܗl��育�����L�����Z���̂��A�������������Ă���܂����̂�
���ԋ������Ă������������A�����̊m�F�̂��A���������グ�܂����B

���萔�ł͂������܂���
�E��s���F
�E�x�X���F
�E������ځF
�E�����ԍ��F
�E�������`(�J�i)�F
��L�����L���̂����A�����[���ւ��ԐM���������܂��B

���s���ȓ_�Ȃǂ������܂�����A���C�y�ɉ��L�܂ł��⍇���������B

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�@�{���ɓ���Ă��⍇�����������B

�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
���ڍw���X�ܗl�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}

�����x�����Ɋւ��邨�₢���킹�́F
�Z�C�m�[�t�B�i���V����������Ё@�㕥�����σT�[�r�X�S��
TEL:03-6908-7888 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
Mail: sfc-atobarai@seino.co.jp
',3,'2015/12/01 13:36:11',32,'2015/12/01 13:36:34',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (430,88,'�ԋ����[���i�L�����Z���j(CEL)','�Z�C�m�[�t�B�i���V�����㕥������','=?UTF-8?B?GyRCJTslJCVOITwlVSUjJUolcyU3JWMlazhlSickJEFrOH0bKEI=?=','sfc-atobarai@seino.co.jp',null,null,null,'�y�㕥�����σT�[�r�X�z���ԋ��̂��A��','=?UTF-8?B?44K744Kk44OO44O844OV44Kj44OK44Oz44K344Oj44Or5b6M5omV44GE56qT?=
 =?UTF-8?B?5Y+j?=','
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������


{CustomerNameKj}�l

���̓x��{SiteNameKj}�ŏ��i���w���̍ۂɁA
�㕥�����σT�[�r�X�������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂����B

{ReceiptDate}��{ReceiptClass}��育�������m�F�������܂������A
���ɓX�ܗl��育�����L�����Z���̂��A�������������Ă���܂����̂�
���ԋ������Ă������������A�����̊m�F�̂��A���������グ�܂����B

���萔�ł͂������܂���
�E��s���F
�E�x�X���F
�E������ځF
�E�����ԍ��F
�E�������`(�J�i)�F
��L�����L���̂����A�����[���ւ��ԐM���������܂��B

���s���ȓ_�Ȃǂ������܂�����A���C�y�ɉ��L�܂ł��⍇���������B

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�@�{���ɓ���Ă��⍇�����������B

�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
���ڍw���X�ܗl�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}

�����x�����Ɋւ��邨�₢���킹�́F
�Z�C�m�[�t�B�i���V����������Ё@�㕥�����σT�[�r�X�S��
TEL:03-6908-7888 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
Mail: sfc-atobarai@seino.co.jp
',3,'2015/12/01 13:36:59',32,'2015/12/01 13:36:59',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (431,1,'���Ǝғo�^�����i�T�[�r�X�J�n�j���[��','','','customer@ato-barai.com',null,null,null,'','','',4,'2015/12/04 18:15:25',43,'2015/12/04 18:15:25',43,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (432,23,'�p�X���[�h��񂨒m�点���[��','','','ato-barai.sp@estore.co.jp',null,null,null,'','','',1,'2015/12/04 18:16:52',43,'2015/12/04 18:16:52',43,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (433,30,'�L�����Z���\�����[��','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer@ato-barai.com',null,null,null,'�y�㕥���h�b�g�R���z�L�����Z���\��������t�������܂���','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{EnterpriseNameKj}�@�l

�����y�㕥���h�b�g�R���z�������p���������܂��āA�܂��Ƃɂ��肪�Ƃ��������܂��B

�ȉ��̂������̃L�����Z���\���̂���t�������܂����B

�y�Ǘ���ʂt�q�k�z
https://www.atobarai.jp/member/

�y�L�����Z����t���z
������ID�F{OrderId}
�����掁���F{CustomerNameKj}�@�l
���������z�F{UseAmount}
���������F{OrderDate}

���Ђł̊m�F��A�ēx���F�A���������Ă��������܂��B

���s���ȓ_�Ȃǂ������܂�����A���C�y�ɓ��Ђ܂ł��₢���킹�������B

--------------------------------------------------------------

�y�㕥���h�b�g�R���z
  ���⍇����F0120-667-690
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com

  �^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F

--------------------------------------------------------------',2,'2015/12/22 16:34:14',32,'2015/12/22 16:34:14',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (434,31,'�L�����Z���\��������[��','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer@ato-barai.com',null,null,null,'�y�㕥���h�b�g�R���z�L�����Z���\�����������t�������܂���','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{EnterpriseNameKj}�@�l

�����y�㕥���h�b�g�R���z�������p���������܂��āA�܂��Ƃɂ��肪�Ƃ��������܂��B

�ȉ��̂������̃L�����Z���\���̎���������������܂����̂ŁA���m�F�������B

�y�Ǘ���ʂt�q�k�z
https://www.atobarai.jp/member/

�y�L�����Z��������z
������ID�F{OrderId}
�����掁���F{CustomerNameKj}�@�l
���������z�F{UseAmount}
���������F{OrderDate}
�L�����Z���\�����F{CancelDate} 

���s���ȓ_�Ȃǂ������܂�����A���C�y�ɓ��Ђ܂ł��₢���킹�������B

--------------------------------------------------------------

�y�㕥���h�b�g�R���z
  ���⍇����F0120-667-690
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com

  �^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F

--------------------------------------------------------------',2,'2015/12/22 16:35:01',32,'2015/12/22 16:35:01',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (435,32,'�������j�����[���iPC�j','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�y�㕥���h�b�g�R���z�������j���̂��肢','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

{CustomerNameKj}�l

���̓x��{SiteNameKj}�ŏ��i���w���̍ۂɁA
�㕥���h�b�g�R���������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂����B

{CancelDate}��{SiteNameKj}���㕥���h�b�g�R����
�L�����Z���̂��A�������������܂������A
���ɐ������������肵�Ă��܂��Ă���܂��̂ŁA
��ς��萔�ł͂������܂����j�����Ă��������悤���肢�������܂��B

�s����s���_�Ȃǂ������܂�����A
���C�y�ɂ��⍇�����������܂��B

���̓x��{SiteNameKj}�ƌ㕥���h�b�g�R���������p��������
�܂��Ƃɂ��肪�Ƃ��������܂����B

����Ƃ������A��낵�����肢�������܂��B

--------------------------------------------------------------
�y�㕥���h�b�g�R���z

  ���⍇����F03-5332-3490
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com
  
�@�^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F
-------------------------------------------------------------- 
',2,'2015/12/22 16:36:05',32,'2015/12/22 16:36:05',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (436,33,'�������j�����[���iCEL�j','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�y�㕥���h�b�g�R���z�������j���̂��肢','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

{CustomerNameKj}�l

���̓x��{SiteNameKj}�ŏ��i���w���̍ۂɁA
�㕥���h�b�g�R���������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂����B

{CancelDate}��{SiteNameKj}���㕥���h�b�g�R����
�L�����Z���̂��A�������������܂������A
���ɐ������������肵�Ă��܂��Ă���܂��̂ŁA
��ς��萔�ł͂������܂����j�����Ă��������悤���肢�������܂��B

�s����s���_�Ȃǂ������܂�����A
���C�y�ɂ��⍇�����������܂��B

���̓x��{SiteNameKj}�ƌ㕥���h�b�g�R���������p��������
�܂��Ƃɂ��肪�Ƃ��������܂����B

����Ƃ������A��낵�����肢�������܂��B

--------------------------------------------------------------
�y�㕥���h�b�g�R���z

  ���⍇����F03-5332-3490
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com
  
�@�^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F
-------------------------------------------------------------- 
',2,'2015/12/22 16:36:27',32,'2015/12/22 16:36:27',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (437,34,'�ߏ�������[���iPC�j','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�y�㕥���h�b�g�R���z���ԋ��̂��A��','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

{CustomerNameKj}�l

���̓x��{SiteNameKj}�ŏ��i���w���̍ۂɁA
�㕥���h�b�g�R���������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂����B

{ReceiptDate}��{ReceiptClass}��育�������m�F�������܂������A
{OverReceiptAmount}�~�������x�������������Ă���܂����̂�
���ԋ������Ă������������A�����̊m�F�̂��A���������グ�܂����B

���萔�ł͂������܂���
�E��s���F
�E�x�X���F
�E������ځF
�E�����ԍ��F
�E�������`(�J�i)�F
��L�����L���̂����A�����[���ւ��ԐM���������܂��B

�s���_�Ȃǂ������܂�����A���C�y�ɂ��⍇�����������܂��B

������낵�����肢�������܂��B

--------------------------------------------------------------
�y�㕥���h�b�g�R���z

  ���⍇����F03-5332-3490
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com
  
�@�^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F
-------------------------------------------------------------- 
',2,'2015/12/22 16:37:01',32,'2015/12/22 16:37:01',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (438,35,'�ߏ�������[���iCEL�j','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�y�㕥���h�b�g�R���z���ԋ��̂��A��','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

{CustomerNameKj}�l

���̓x��{SiteNameKj}�ŏ��i���w���̍ۂɁA
�㕥���h�b�g�R���������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂����B

{ReceiptDate}��{ReceiptClass}��育�������m�F�������܂������A
{OverReceiptAmount}�~�������x�������������Ă���܂����̂�
���ԋ������Ă������������A�����̊m�F�̂��A���������グ�܂����B

���萔�ł͂������܂���
�E��s���F
�E�x�X���F
�E������ځF
�E�����ԍ��F
�E�������`(�J�i)�F
��L�����L���̂����A�����[���ւ��ԐM���������܂��B

�s���_�Ȃǂ������܂�����A���C�y�ɂ��⍇�����������܂��B

������낵�����肢�������܂��B

--------------------------------------------------------------
�y�㕥���h�b�g�R���z

  ���⍇����F03-5332-3490
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com
  
�@�^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F
-------------------------------------------------------------- 
',2,'2015/12/22 16:37:22',32,'2015/12/22 16:37:22',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (439,38,'�����C���������[��','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer@ato-barai.com',null,null,null,'�y�㕥���h�b�g�R���z���������e�̏C��������܂����i{OrderCount}���j','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{EnterpriseNameKj}�l

�����㕥���h�b�g�R���������p���������A
�܂��Ƃɂ��肪�Ƃ��������܂��B

�ȉ��̂��������e�̏C�����󂯕t���������܂����B


�C�����������F{OrderCount}��

�������Җ��F{OrderSummary}



�����������������������@�L�����Z�������������ꍇ�@����������������������

���o�^���ꂽ�����̃L�����Z�����������ꍇ�́A���萔�ł����u���������v����
���������������A�Y���̂�������N���b�N���ăL�����Z���������s���Ă��������B

������������������������������������������������������������������������

�y�Ǘ���ʂt�q�k�z
https://www.atobarai.jp/member/

���s���ȓ_�Ȃǂ������܂�����A���C�y�ɂ��₢���킹���������܂��B

--------------------------------------------------------------
�y�㕥���h�b�g�R���z

  ���⍇����F0120-667-690
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com

  �^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F
--------------------------------------------------------------
',2,'2015/12/22 16:37:59',32,'2015/12/22 16:37:59',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (440,38,'�����C���������[��','�Z�C�m�[�t�B�i���V�����㕥������','=?UTF-8?B?GyRCJTslJCVOITwlVSUjJUolcyU3JWMlazhlSickJEFrOH0bKEI=?=','sfc-atobarai@seino.co.jp',null,null,null,'���������e�̏C��������܂����i{OrderCount}���j','=?UTF-8?B?44K744Kk44OO44O844OV44Kj44OK44Oz44K344Oj44Or5b6M5omV44GE56qT?=
 =?UTF-8?B?5Y+j?=','
{EnterpriseNameKj} �l

�����y�㕥�����σT�[�r�X�z�������p���������܂��āA
�܂��Ƃɂ��肪�Ƃ��������܂��B

�ȉ��̂��������e�̏C�����󂯕t���������܂����B


�C�����������F{OrderCount}��

�������Җ��F{OrderSummary}



�����������������������@�L�����Z�������������ꍇ�@����������������������

���o�^���ꂽ�����̃L�����Z�����������ꍇ�́A���萔�ł����u���������v����
���������������A�Y���̂�������N���b�N���ăL�����Z���������s���Ă��������B

������������������������������������������������������������������������

�y�Ǘ���ʂt�q�k�z
https://atobarai.seino.co.jp/seino-financial/member/

���s���ȓ_�Ȃǂ������܂�����A���C�y�ɂ��₢���킹���������܂��B


--------------------------------------------------------------

�Z�C�m�[�t�B�i���V����������Ё@�㕥�����σT�[�r�X�S��
TEL:03-6908-7888 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
Mail: sfc-atobarai@seino.co.jp

--------------------------------------------------------------',3,'2015/12/22 16:39:07',32,'2015/12/22 16:39:07',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (441,39,'�x���������߃��[���i�ĂP�j�iPC�j','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�y�㕥���h�b�g�R���z{OrderDate}{SiteNameKj}�ł̂��������̌�({OrderId})','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɂ������̂��葱�������ς̂悤�ł����
�@�����[���ւ̕ԐM�͂��s�v�ł������܂��B

{CustomerNameKj}�l

���̓x��{SiteNameKj}�ŏ��i���w���̍ۂɁA
�㕥���h�b�g�R���������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂��B

���x�����������߂��Ă��������̊m�F�����Ă���܂���ł����̂�
�{���A�Đ������𔭍s�E�����������܂����B
���茳�ɓ͂�����A�����܂łɂ��x���������肢�������܂��B

�y���������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
���������z�F{TotalAmount}

�����x���������߂��Ă��܂��܂��ƁA
�Đ����萔�������Z����܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ł������܂�)
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z
{Bk_BankName}�@{Bk_BranchName}
���ʌ����@{Bk_AccountNumber}
{Bk_AccountHolderKn}

�y�X�֐U�֌����z
�����L���F00120�]7
�����ԍ��F670031
�J�j�L���b�`�{�[��

���̑��A���x���Ɋւ��Ă��s���ȓ_�͉��L�t�q�k�����m�F���������B
http://www.ato-barai.com/guidance/faq.html

�����i�̕ԕi�E�����ȂǏ��i�Ɋւ��邨�₢���킹�́F
���ڍw���X�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@
�d�b�F{Phone}

--------------------------------------------------------------
�y�㕥���h�b�g�R���z

  ���⍇����F03-5332-3490
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com
  
�@�^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F
--------------------------------------------------------------
',2,'2015/12/22 16:47:05',32,'2015/12/22 16:47:05',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (442,40,'�x���������߃��[���i�ĂP�j�iCEL�j','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�y�㕥���h�b�g�R���z{OrderDate}{SiteNameKj}�ł̂��������̌�({OrderId})','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɂ������̂��葱�������ς̂悤�ł����
�@�����[���ւ̕ԐM�͂��s�v�ł������܂��B

{CustomerNameKj}�l

���̓x��{SiteNameKj}�ŏ��i���w���̍ۂɁA
�㕥���h�b�g�R���������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂��B

���x�����������߂��Ă��������̊m�F�����Ă���܂���ł����̂�
�{���A�Đ������𔭍s�E�����������܂����B
���茳�ɓ͂�����A�����܂łɂ��x���������肢�������܂��B
�y���������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
���������z�F{TotalAmount}

�����x���������߂��Ă��܂��܂��ƁA
�Đ����萔�������Z����܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ł������܂�)
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z
{Bk_BankName}�@{Bk_BranchName}
���ʌ����@{Bk_AccountNumber}
{Bk_AccountHolderKn}

�y�X�֐U�֌����z
�����L���F00120�]7
�����ԍ��F670031
�J�j�L���b�`�{�[��

���̑��A���x���Ɋւ��Ă��s���ȓ_�͉��L�t�q�k�����m�F���������B
http://www.ato-barai.com/guidance/faq.html

�����i�̕ԕi�E�����ȂǏ��i�Ɋւ��邨�₢���킹�́F
���ڍw���X�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@
�d�b�F{Phone}

--------------------------------------------------------------
�y�㕥���h�b�g�R���z

  ���⍇����F03-5332-3490
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com
  
�@�^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F
--------------------------------------------------------------
',2,'2015/12/22 16:47:54',32,'2015/12/22 16:47:54',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (443,41,'�x���������߃��[���i�ĂR�j�iPC�j','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�y�㕥���h�b�g�R���z{OrderDate}{SiteNameKj}�ł̂��������̌�({OrderId})','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɂ������̂��葱�������ς̂悤�ł����
�@�����[���ւ̕ԐM�͂��s�v�ł������܂��B

{CustomerNameKj}�l

���̓x��{SiteNameKj}�ŏ��i���w���̍ۂɁA
�㕥���h�b�g�R���������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ClaimDate}�ɂ����肵���������̂��x�����������߂��Ă�
�������̊m�F�����Ă���܂���ł����̂�
�{���A�Đ������𔭍s�E�����������܂����B
���茳�ɓ͂�����A�����܂łɂ��x���������肢�������܂��B


�y���������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
���������z�F{TotalAmount}

�����x���������߂��Ă��܂��܂��ƁA
�Đ����萔�������Z����܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ł������܂�)
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z
{Bk_BankName}�@{Bk_BranchName}
���ʌ����@{Bk_AccountNumber}
{Bk_AccountHolderKn}

�y�X�֐U�֌����z
�����L���F00120�]7
�����ԍ��F670031
�J�j�L���b�`�{�[��

���̑��A���x���Ɋւ��Ă��s���ȓ_�͉��L�t�q�k�����m�F���������B
http://www.ato-barai.com/guidance/faq.html

�����i�̕ԕi�E�����ȂǏ��i�Ɋւ��邨�₢���킹�́F
���ڍw���X�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@
�d�b�F{Phone}

--------------------------------------------------------------
�y�㕥���h�b�g�R���z

  ���⍇����F03-5332-3490
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com
  
�@�^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F
--------------------------------------------------------------
',2,'2015/12/22 16:48:42',32,'2015/12/22 16:51:04',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (444,42,'�x���������߃��[���i�ĂR�j�iCEL�j','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�y�㕥���h�b�g�R���z{OrderDate}{SiteNameKj}�ł̂��������̌�({OrderId})','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɂ������̂��葱�������ς̂悤�ł����
�@�����[���ւ̕ԐM�͂��s�v�ł������܂��B

{CustomerNameKj}�l

���̓x��{SiteNameKj}�ŏ��i���w���̍ۂɁA
�㕥���h�b�g�R���������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ClaimDate}�ɂ����肵���������̂��x�����������߂��Ă�
�������̊m�F�����Ă���܂���ł����̂�
�{���A�Đ������𔭍s�E�����������܂����B
���茳�ɓ͂�����A�����܂łɂ��x���������肢�������܂��B


�y���������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
�Đ����ǉ��萔���F{ReClaimFee}
�x�����Q���F{DamageInterest}
���������z�F{TotalAmount}

�����x���������߂��Ă��܂��܂��ƁA
�Đ����萔�������Z����܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ł������܂�)
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z
{Bk_BankName}�@{Bk_BranchName}
���ʌ����@{Bk_AccountNumber}
{Bk_AccountHolderKn}

�y�X�֐U�֌����z
�����L���F00120�]7
�����ԍ��F670031
�J�j�L���b�`�{�[��

���̑��A���x���Ɋւ��Ă��s���ȓ_�͉��L�t�q�k�����m�F���������B
http://www.ato-barai.com/guidance/faq.html

�����i�̕ԕi�E�����ȂǏ��i�Ɋւ��邨�₢���킹�́F
���ڍw���X�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@
�d�b�F{Phone}

--------------------------------------------------------------
�y�㕥���h�b�g�R���z

  ���⍇����F03-5332-3490
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com
  
�@�^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F
--------------------------------------------------------------
',2,'2015/12/22 16:51:57',32,'2015/12/22 16:51:57',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (445,43,'�x���������߃��[���i�ĂS�j�iPC�j','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�y�㕥���h�b�g�R���z{OrderDate}{SiteNameKj}�ł̂��������̌�({OrderId})','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɂ������̂��葱�������ς̂悤�ł����
�@�����[���ւ̕ԐM�͂��s�v�ł������܂��B

{CustomerNameKj}�l

���̓x��{SiteNameKj}�ŏ��i���w���̍ۂɁA
�㕥���h�b�g�R���������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ClaimDate}�ɂ����肵���������̂��x�����������߂��Ă�
�������̊m�F�����Ă���܂���ł����̂�
�{���A�Đ������𔭍s�E�����������܂����B

���[���̂��x�����ɂ��A���茳�ɓ͂�����
���}���Ή����������܂��悤���肢�������܂��B


�y���������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
�Đ����ǉ��萔���F{ReClaimFee}
�x�����Q���F{DamageInterest}
���������z�F{TotalAmount}

�����x���������߂��Ă��܂��܂��ƁA
�Đ����萔�������Z����܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ł������܂�)
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z
{Bk_BankName}�@{Bk_BranchName}
���ʌ����@{Bk_AccountNumber}
{Bk_AccountHolderKn}

�y�X�֐U�֌����z
�����L���F00120�]7
�����ԍ��F670031
�J�j�L���b�`�{�[��

���̑��A���x���Ɋւ��Ă��s���ȓ_�͉��L�t�q�k�����m�F���������B
http://www.ato-barai.com/guidance/faq.html

�����i�̕ԕi�E�����ȂǏ��i�Ɋւ��邨�₢���킹�́F
���ڍw���X�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@
�d�b�F{Phone}

--------------------------------------------------------------
�y�㕥���h�b�g�R���z

  ���⍇����F03-5332-3490
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com
  
�@�^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F
--------------------------------------------------------------
',2,'2015/12/22 16:53:51',32,'2015/12/22 16:54:16',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (446,44,'�x���������߃��[���i�ĂS�j�iCEL�j','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�y�㕥���h�b�g�R���z{OrderDate}{SiteNameKj}�ł̂��������̌�({OrderId})','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɂ������̂��葱�������ς̂悤�ł����
�@�����[���ւ̕ԐM�͂��s�v�ł������܂��B

{CustomerNameKj}�l

���̓x��{SiteNameKj}�ŏ��i���w���̍ۂɁA
�㕥���h�b�g�R���������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ClaimDate}�ɂ����肵���������̂��x�����������߂��Ă�
�������̊m�F�����Ă���܂���ł����̂�
�{���A�Đ������𔭍s�E�����������܂����B

���[���̂��x�����ɂ��A���茳�ɓ͂�����
���}���Ή����������܂��悤���肢�������܂��B

�y���������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
�Đ����ǉ��萔���F{ReClaimFee}
�x�����Q���F{DamageInterest}
���������z�F{TotalAmount}

�����x���������߂��Ă��܂��܂��ƁA
�Đ����萔�������Z����܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ł������܂�)
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z
{Bk_BankName}�@{Bk_BranchName}
���ʌ����@{Bk_AccountNumber}
{Bk_AccountHolderKn}

�y�X�֐U�֌����z
�����L���F00120�]7
�����ԍ��F670031
�J�j�L���b�`�{�[��

���̑��A���x���Ɋւ��Ă��s���ȓ_�͉��L�t�q�k�����m�F���������B
http://www.ato-barai.com/guidance/faq.html

�����i�̕ԕi�E�����ȂǏ��i�Ɋւ��邨�₢���킹�́F
���ڍw���X�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@
�d�b�F{Phone}

--------------------------------------------------------------
�y�㕥���h�b�g�R���z

  ���⍇����F03-5332-3490
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com
  
�@�^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F
--------------------------------------------------------------
',2,'2015/12/22 16:55:03',32,'2015/12/22 16:55:25',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (447,45,'�x���������߃��[���i�ĂT�j�iPC�j','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�y�㕥���h�b�g�R���z{OrderDate}{SiteNameKj}�ł̂��������̌�({OrderId})','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɂ������̂��葱�������ς̂悤�ł����
�@�����[���ւ̕ԐM�͂��s�v�ł������܂��B

{CustomerNameKj}�l

���̓x��{SiteNameKj}�ŏ��i���w���̍ۂɁA
�㕥���h�b�g�R���������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ClaimDate}�ɂ����肵���������̂��x�����������߂��Ă�
�������̊m�F�����Ă���܂���ł����̂�
�{���A�Đ������𔭍s�E�����������܂����B

��L����ł����m�点���Ă���ʂ�A
���x�������m�F�ł��Ȃ��ꍇ
���q�l�̐M�p����ȂǕs���v��������\�����������܂��B
���܂��Ă͑��₩�Ȃ��Ή������肢�������܂��B

�y���������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
�Đ����ǉ��萔���F{ReClaimFee}
�x�����Q���F{DamageInterest}
���������z�F{TotalAmount}

�����x���������߂��Ă��܂��܂��ƁA
�Đ����萔�������Z����܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ł������܂�)
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z
{Bk_BankName}�@{Bk_BranchName}
���ʌ����@{Bk_AccountNumber}
{Bk_AccountHolderKn}

�y�X�֐U�֌����z
�����L���F00120�]7
�����ԍ��F670031
�J�j�L���b�`�{�[��

���̑��A���x���Ɋւ��Ă��s���ȓ_�͉��L�t�q�k�����m�F���������B
http://www.ato-barai.com/guidance/faq.html

�����i�̕ԕi�E�����ȂǏ��i�Ɋւ��邨�₢���킹�́F
���ڍw���X�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@
�d�b�F{Phone}

--------------------------------------------------------------
�y�㕥���h�b�g�R���z

  ���⍇����F03-5332-3490
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com
  
�@�^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F
--------------------------------------------------------------
',2,'2015/12/22 17:00:36',32,'2015/12/22 17:00:36',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (448,46,'�x���������߃��[���i�ĂT�j�iCEL�j','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�y�㕥���h�b�g�R���z{OrderDate}{SiteNameKj}�ł̂��������̌�({OrderId})','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɂ������̂��葱�������ς̂悤�ł����
�@�����[���ւ̕ԐM�͂��s�v�ł������܂��B

{CustomerNameKj}�l

���̓x��{SiteNameKj}�ŏ��i���w���̍ۂɁA
�㕥���h�b�g�R���������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ClaimDate}�ɂ����肵���������̂��x�����������߂��Ă�
�������̊m�F�����Ă���܂���ł����̂�
�{���A�Đ������𔭍s�E�����������܂����B


��L����ł����m�点���Ă���ʂ�A
���x�������m�F�ł��Ȃ��ꍇ
���q�l�̐M�p����ȂǕs���v��������\�����������܂��B
���܂��Ă͑��₩�Ȃ��Ή������肢�������܂��B

�y���������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
�Đ����ǉ��萔���F{ReClaimFee}
�x�����Q���F{DamageInterest}
���������z�F{TotalAmount}

�����x���������߂��Ă��܂��܂��ƁA
�Đ����萔�������Z����܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ł������܂�)
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z
{Bk_BankName}�@{Bk_BranchName}
���ʌ����@{Bk_AccountNumber}
{Bk_AccountHolderKn}

�y�X�֐U�֌����z
�����L���F00120�]7
�����ԍ��F670031
�J�j�L���b�`�{�[��

���̑��A���x���Ɋւ��Ă��s���ȓ_�͉��L�t�q�k�����m�F���������B
http://www.ato-barai.com/guidance/faq.html

�����i�̕ԕi�E�����ȂǏ��i�Ɋւ��邨�₢���킹�́F
���ڍw���X�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@
�d�b�F{Phone}

--------------------------------------------------------------
�y�㕥���h�b�g�R���z

  ���⍇����F03-5332-3490
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com
  
�@�^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F
--------------------------------------------------------------
',2,'2015/12/22 17:01:34',32,'2015/12/22 17:01:34',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (449,47,'�x���������߃��[���i�ĂU�j�iPC�j','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�y�㕥���h�b�g�R���z{OrderDate}{SiteNameKj}�ł̂��������̌�({OrderId})','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɂ������̂��葱�������ς̂悤�ł����
�@�����[���ւ̕ԐM�͂��s�v�ł������܂��B

{CustomerNameKj}�l

���̓x��{SiteNameKj}�ŏ��i���w���̍ۂɁA
�㕥���h�b�g�R���������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ClaimDate}�ɂ����肵���������̂��x�����������߂��Ă�
�������̊m�F�����Ă���܂���ł����̂�
�{���A�Đ������𔭍s�E�����������܂����B

���̂܂ܖ�������Ԃ��p������܂��ƁA
���Ђł̑Ή�������ƂȂ�
�ʒm�L�ڂ̑Ή��ƂȂ�ꍇ������܂��B
���܂��Ă͎��}���x�����ɂ���
���Ή����������܂��悤���肢�������܂��B

�y���������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
�Đ����ǉ��萔���F{ReClaimFee}
�x�����Q���F{DamageInterest}
���������z�F{TotalAmount}

�����x���������߂��Ă��܂��܂��ƁA
�Đ����萔�������Z����܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ł������܂�)
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z
{Bk_BankName}�@{Bk_BranchName}
���ʌ����@{Bk_AccountNumber}
{Bk_AccountHolderKn}

�y�X�֐U�֌����z
�����L���F00120�]7
�����ԍ��F670031
�J�j�L���b�`�{�[��

���̑��A���x���Ɋւ��Ă��s���ȓ_�͉��L�t�q�k�����m�F���������B
http://www.ato-barai.com/guidance/faq.html

�����i�̕ԕi�E�����ȂǏ��i�Ɋւ��邨�₢���킹�́F
���ڍw���X�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@
�d�b�F{Phone}

--------------------------------------------------------------
�y�㕥���h�b�g�R���z

  ���⍇����F03-5332-3490
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com
  
�@�^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F
--------------------------------------------------------------
',2,'2015/12/22 17:02:34',32,'2015/12/22 17:02:34',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (450,48,'�x���������߃��[���i�ĂU�j�iCEL�j','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�y�㕥���h�b�g�R���z{OrderDate}{SiteNameKj}�ł̂��������̌�({OrderId})','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɂ������̂��葱�������ς̂悤�ł����
�@�����[���ւ̕ԐM�͂��s�v�ł������܂��B

{CustomerNameKj}�l

���̓x��{SiteNameKj}�ŏ��i���w���̍ۂɁA
�㕥���h�b�g�R���������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ClaimDate}�ɂ����肵���������̂��x�����������߂��Ă�
�������̊m�F�����Ă���܂���ł����̂�
�{���A�Đ������𔭍s�E�����������܂����B

���̂܂ܖ�������Ԃ��p������܂��ƁA
���Ђł̑Ή�������ƂȂ�
�ʒm�L�ڂ̑Ή��ƂȂ�ꍇ������܂��B
���܂��Ă͎��}���x�����ɂ���
���Ή����������܂��悤���肢�������܂��B

�y���������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
�Đ����ǉ��萔���F{ReClaimFee}
�x�����Q���F{DamageInterest}
���������z�F{TotalAmount}

�����x���������߂��Ă��܂��܂��ƁA
�Đ����萔�������Z����܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ł������܂�)
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z
{Bk_BankName}�@{Bk_BranchName}
���ʌ����@{Bk_AccountNumber}
{Bk_AccountHolderKn}

�y�X�֐U�֌����z
�����L���F00120�]7
�����ԍ��F670031
�J�j�L���b�`�{�[��

���̑��A���x���Ɋւ��Ă��s���ȓ_�͉��L�t�q�k�����m�F���������B
http://www.ato-barai.com/guidance/faq.html

�����i�̕ԕi�E�����ȂǏ��i�Ɋւ��邨�₢���킹�́F
���ڍw���X�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@
�d�b�F{Phone}

--------------------------------------------------------------
�y�㕥���h�b�g�R���z

  ���⍇����F03-5332-3490
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com
  
�@�^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F
--------------------------------------------------------------
',2,'2015/12/22 17:03:10',32,'2015/12/22 17:03:10',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (451,49,'�x���������߃��[���i�ĂV�j�iPC�j','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�y�㕥���h�b�g�R���z{OrderDate}{SiteNameKj}�ł̂��������̌�({OrderId})','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɂ������̂��葱�������ς̂悤�ł����
�@�����[���ւ̕ԐM�͂��s�v�ł������܂��B

{CustomerNameKj}�l

���̓x��{SiteNameKj}�ŏ��i���w���̍ۂɁA
�㕥���h�b�g�R���������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ClaimDate}�ɂ����肵���������̂��x�����������߂��Ă�
�������̊m�F�����Ă���܂���ł����̂�
�{���A�Đ������𔭍s�E�����������܂����B

�ĎO�ɂ킽��A���ԍςɑ΂����s�����悤���ʒm�������܂�����
�M�a��落�ӂ̂��邲�Ή��𒸂��Ă��Ȃ��󋵂ƂȂ��Ă���܂��B
����ɂ��܂��Ă��A���A���E���x�������m�F�ł��Ȃ��ꍇ��
�ٌ�m�ւ̉���ϔC�������͖@�I�葱���Ɉڍs�����邨���܂���B
�������Ȃ���A���Ѝ��Ǘ����ł�
�M�a�̍����s�ɑ΂�������}��ׂ̑��k������݂��Ă���
���k�ɂ��������\�ȏꍇ���������܂��B
���܂��ẮA�����Ɍ������}���A�����������܂��悤���肢�������܂��B

�y���������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
�Đ����ǉ��萔���F{ReClaimFee}
�x�����Q���F{DamageInterest}
���������z�F{TotalAmount}

�����x���������߂��Ă��܂��܂��ƁA
�Đ����萔�������Z����܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ł������܂�)
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z
{Bk_BankName}�@{Bk_BranchName}
���ʌ����@{Bk_AccountNumber}
{Bk_AccountHolderKn}

�y�X�֐U�֌����z
�����L���F00120�]7
�����ԍ��F670031
�J�j�L���b�`�{�[��

���̑��A���x���Ɋւ��Ă��s���ȓ_�͉��L�t�q�k�����m�F���������B
http://www.ato-barai.com/guidance/faq.html

�����i�̕ԕi�E�����ȂǏ��i�Ɋւ��邨�₢���킹�́F
���ڍw���X�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@
�d�b�F{Phone}

--------------------------------------------------------------
�y�㕥���h�b�g�R���z

  ���⍇����F03-5332-3490
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com
  
�@�^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F
--------------------------------------------------------------
',2,'2015/12/22 17:04:01',32,'2015/12/22 17:04:34',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (452,50,'�x���������߃��[���i�ĂV�j�iCEL�j','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�y�㕥���h�b�g�R���z{OrderDate}{SiteNameKj}�ł̂��������̌�({OrderId})','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɂ������̂��葱�������ς̂悤�ł����
�@�����[���ւ̕ԐM�͂��s�v�ł������܂��B

{CustomerNameKj}�l

���̓x��{SiteNameKj}�ŏ��i���w���̍ۂɁA
�㕥���h�b�g�R���������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ClaimDate}�ɂ����肵���������̂��x�����������߂��Ă�
�������̊m�F�����Ă���܂���ł����̂�
�{���A�Đ������𔭍s�E�����������܂����B

�ĎO�ɂ킽��A���ԍςɑ΂����s�����悤���ʒm�������܂�����
�M�a��落�ӂ̂��邲�Ή��𒸂��Ă��Ȃ��󋵂ƂȂ��Ă���܂��B
����ɂ��܂��Ă��A���A���E���x�������m�F�ł��Ȃ��ꍇ��
�ٌ�m�ւ̉���ϔC�������͖@�I�葱���Ɉڍs�����邨���܂���B
�������Ȃ���A���Ѝ��Ǘ����ł�
�M�a�̍����s�ɑ΂�������}��ׂ̑��k������݂��Ă���
���k�ɂ��������\�ȏꍇ���������܂��B
���܂��ẮA�����Ɍ������}���A�����������܂��悤���肢�������܂��B

�y���������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
�Đ����ǉ��萔���F{ReClaimFee}
�x�����Q���F{DamageInterest}
���������z�F{TotalAmount}

�����x���������߂��Ă��܂��܂��ƁA
�Đ����萔�������Z����܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ł������܂�)
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z
{Bk_BankName}�@{Bk_BranchName}
���ʌ����@{Bk_AccountNumber}
{Bk_AccountHolderKn}

�y�X�֐U�֌����z
�����L���F00120�]7
�����ԍ��F670031
�J�j�L���b�`�{�[��

���̑��A���x���Ɋւ��Ă��s���ȓ_�͉��L�t�q�k�����m�F���������B
http://www.ato-barai.com/guidance/faq.html

�����i�̕ԕi�E�����ȂǏ��i�Ɋւ��邨�₢���킹�́F
���ڍw���X�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@
�d�b�F{Phone}

--------------------------------------------------------------
�y�㕥���h�b�g�R���z

  ���⍇����F03-5332-3490
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com
  
�@�^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F
--------------------------------------------------------------
',2,'2015/12/22 17:05:11',32,'2015/12/22 17:05:11',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (453,39,'�x���������߃��[���i�ĂP�j�iPC�j','�㕥�����σT�[�r�X','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','customer2@ato-barai.com',null,null,null,'�y�㕥�����σT�[�r�X�z{OrderDate}{SiteNameKj}�ł̂��������̌�({OrderId})','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɂ������̂��葱�������ς̂悤�ł����
�@�����[���ւ̕ԐM�͂��s�v�ł������܂��B

{CustomerNameKj}�l

����� {SiteNameKj}�l�ł̂���������
�y�㕥�����σT�[�r�X�z�������p���������܂��āA
�܂��Ƃɂ��肪�Ƃ��������܂��B

���x�����������߂��Ă��������̊m�F�����Ă���܂���ł����̂�
�{���A�Đ������𔭍s�E�����������܂����B
���茳�ɓ͂�����A�����܂łɂ��x���������肢�������܂��B

�y���������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
���������z�F{TotalAmount}

�����x���������߂��Ă��܂��܂��ƁA
�Đ����萔�������Z����܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ł������܂�)
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z
�W���p���l�b�g��s�@
���~�W�x�X�@
���ʌ����@0015015
�Z�C�m�[�t�B�i���V�����i�J

�y�X�֐U�֌����z
�����L���F00100-7
�����ԍ��F292043
������ЃL���b�`�{�[���@�Z�C�m�[FC�W

���s���ȓ_�Ȃǂ������܂�����A���C�y�ɉ��L�܂ł��⍇���������B

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�@�{���ɓ���Ă��⍇�����������B

�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
���ڍw���X�ܗl�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}

�����x�����Ɋւ��邨�₢���킹�́F
�Z�C�m�[�t�B�i���V����������Ё@�㕥�����σT�[�r�X�S��
TEL:03-6908-7888 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
Mail: sfc-atobarai@seino.co.jp

',3,'2015/12/24 8:08:57',32,'2015/12/24 8:08:57',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (454,40,'�x���������߃��[���i�ĂP�j�iCEL�j','�㕥�����σT�[�r�X','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'�y�㕥�����σT�[�r�X�z{OrderDate}{SiteNameKj}�ł̂��������̌�({OrderId})','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɂ������̂��葱�������ς̂悤�ł����
�@�����[���ւ̕ԐM�͂��s�v�ł������܂��B

{CustomerNameKj}�l

����� {SiteNameKj}�l�ł̂���������
�y�㕥�����σT�[�r�X�z�������p���������܂��āA
�܂��Ƃɂ��肪�Ƃ��������܂��B

���x�����������߂��Ă��������̊m�F�����Ă���܂���ł����̂�
�{���A�Đ������𔭍s�E�����������܂����B
���茳�ɓ͂�����A�����܂łɂ��x���������肢�������܂��B

�y���������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
���������z�F{TotalAmount}

�����x���������߂��Ă��܂��܂��ƁA
�Đ����萔�������Z����܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ł������܂�)
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z
�W���p���l�b�g��s�@
���~�W�x�X�@
���ʌ����@0015015
�Z�C�m�[�t�B�i���V�����i�J

�y�X�֐U�֌����z
�����L���F00100-7
�����ԍ��F292043
������ЃL���b�`�{�[���@�Z�C�m�[FC�W

���s���ȓ_�Ȃǂ������܂�����A���C�y�ɉ��L�܂ł��⍇���������B

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�@�{���ɓ���Ă��⍇�����������B

�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
���ڍw���X�ܗl�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}

�����x�����Ɋւ��邨�₢���킹�́F
�Z�C�m�[�t�B�i���V����������Ё@�㕥�����σT�[�r�X�S��
TEL:03-6908-7888 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
Mail: sfc-atobarai@seino.co.jp

',3,'2015/12/24 8:09:37',32,'2015/12/24 8:09:37',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (455,41,'�x���������߃��[���i�ĂR�j�iPC�j','�㕥�����σT�[�r�X','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'�y�㕥�����σT�[�r�X�z{OrderDate}{SiteNameKj}�ł̂��������̌�({OrderId})','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɂ������̂��葱�������ς̂悤�ł����
�@�����[���ւ̕ԐM�͂��s�v�ł������܂��B

{CustomerNameKj}�l

����� {SiteNameKj}�l�ł̂���������
�y�㕥�����σT�[�r�X�z�������p���������܂��āA
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ClaimDate}�ɂ����肵���������̂��x�����������߂��Ă�
�������̊m�F�����Ă���܂���ł����̂�
�{���A�Đ������𔭍s�E�����������܂����B
���茳�ɓ͂�����A�����܂łɂ��x���������肢�������܂��B


�y���������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
�Đ����ǉ��萔���F{ReClaimFee}
�x�����Q���F{DamageInterest}
���������z�F{TotalAmount}

�����x���������߂��Ă��܂��܂��ƁA
�Đ����萔�������Z����܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ł������܂�)
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z
�W���p���l�b�g��s�@
���~�W�x�X�@
���ʌ����@0015015
�Z�C�m�[�t�B�i���V�����i�J

�y�X�֐U�֌����z
�����L���F00100-7
�����ԍ��F292043
������ЃL���b�`�{�[���@�Z�C�m�[FC�W

���s���ȓ_�Ȃǂ������܂�����A���C�y�ɉ��L�܂ł��⍇���������B

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�@�{���ɓ���Ă��⍇�����������B

�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
���ڍw���X�ܗl�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}

�����x�����Ɋւ��邨�₢���킹�́F
�Z�C�m�[�t�B�i���V����������Ё@�㕥�����σT�[�r�X�S��
TEL:03-6908-7888 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
Mail: sfc-atobarai@seino.co.jp
',3,'2015/12/24 8:10:58',32,'2015/12/24 8:10:58',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (456,42,'�x���������߃��[���i�ĂR�j�iCEL�j','�㕥�����σT�[�r�X','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'�y�㕥�����σT�[�r�X�z{OrderDate}{SiteNameKj}�ł̂��������̌�({OrderId})','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɂ������̂��葱�������ς̂悤�ł����
�@�����[���ւ̕ԐM�͂��s�v�ł������܂��B

{CustomerNameKj}�l

����� {SiteNameKj}�l�ł̂���������
�y�㕥�����σT�[�r�X�z�������p���������܂��āA
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ClaimDate}�ɂ����肵���������̂��x�����������߂��Ă�
�������̊m�F�����Ă���܂���ł����̂�
�{���A�Đ������𔭍s�E�����������܂����B
���茳�ɓ͂�����A�����܂łɂ��x���������肢�������܂��B


�y���������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
�Đ����ǉ��萔���F{ReClaimFee}
�x�����Q���F{DamageInterest}
���������z�F{TotalAmount}

�����x���������߂��Ă��܂��܂��ƁA
�Đ����萔�������Z����܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ł������܂�)
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z
�W���p���l�b�g��s�@
���~�W�x�X�@
���ʌ����@0015015
�Z�C�m�[�t�B�i���V�����i�J

�y�X�֐U�֌����z
�����L���F00100-7
�����ԍ��F292043
������ЃL���b�`�{�[���@�Z�C�m�[FC�W

���s���ȓ_�Ȃǂ������܂�����A���C�y�ɉ��L�܂ł��⍇���������B

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�@�{���ɓ���Ă��⍇�����������B

�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
���ڍw���X�ܗl�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}

�����x�����Ɋւ��邨�₢���킹�́F
�Z�C�m�[�t�B�i���V����������Ё@�㕥�����σT�[�r�X�S��
TEL:03-6908-7888 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
Mail: sfc-atobarai@seino.co.jp
',3,'2015/12/24 8:11:48',32,'2015/12/24 8:11:48',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (457,43,'�x���������߃��[���i�ĂS�j�iPC�j','�㕥�����σT�[�r�X','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'�y�㕥�����σT�[�r�X�z{OrderDate}{SiteNameKj}�ł̂��������̌�({OrderId})','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɂ������̂��葱�������ς̂悤�ł����
�@�����[���ւ̕ԐM�͂��s�v�ł������܂��B

{CustomerNameKj}�l

����� {SiteNameKj}�l�ł̂���������
�y�㕥�����σT�[�r�X�z�������p���������܂��āA
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ClaimDate}�ɂ����肵���������̂��x�����������߂��Ă�
�������̊m�F�����Ă���܂���ł����̂�
�{���A�Đ������𔭍s�E�����������܂����B

���[���̂��x�����ɂ��A���茳�ɓ͂�����
���}���Ή����������܂��悤���肢�������܂��B

�y���������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
�Đ����ǉ��萔���F{ReClaimFee}
�x�����Q���F{DamageInterest}
���������z�F{TotalAmount}

�����x���������߂��Ă��܂��܂��ƁA
�Đ����萔�������Z����܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ł������܂�)
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z
�W���p���l�b�g��s�@
���~�W�x�X�@
���ʌ����@0015015
�Z�C�m�[�t�B�i���V�����i�J

�y�X�֐U�֌����z
�����L���F00100-7
�����ԍ��F292043
������ЃL���b�`�{�[���@�Z�C�m�[FC�W

���s���ȓ_�Ȃǂ������܂�����A���C�y�ɉ��L�܂ł��⍇���������B

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�@�{���ɓ���Ă��⍇�����������B

�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
���ڍw���X�ܗl�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}

�����x�����Ɋւ��邨�₢���킹�́F
�Z�C�m�[�t�B�i���V����������Ё@�㕥�����σT�[�r�X�S��
TEL:03-6908-7888 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
Mail: sfc-atobarai@seino.co.jp
',3,'2015/12/24 8:13:36',32,'2015/12/24 8:13:36',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (458,44,'�x���������߃��[���i�ĂS�j�iCEL�j','�㕥�����σT�[�r�X','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'�y�㕥�����σT�[�r�X�z{OrderDate}{SiteNameKj}�ł̂��������̌�({OrderId})','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɂ������̂��葱�������ς̂悤�ł����
�@�����[���ւ̕ԐM�͂��s�v�ł������܂��B

{CustomerNameKj}�l

����� {SiteNameKj}�l�ł̂���������
�y�㕥�����σT�[�r�X�z�������p���������܂��āA
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ClaimDate}�ɂ����肵���������̂��x�����������߂��Ă�
�������̊m�F�����Ă���܂���ł����̂�
�{���A�Đ������𔭍s�E�����������܂����B

���[���̂��x�����ɂ��A���茳�ɓ͂�����
���}���Ή����������܂��悤���肢�������܂��B

�y���������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
�Đ����ǉ��萔���F{ReClaimFee}
�x�����Q���F{DamageInterest}
���������z�F{TotalAmount}

�����x���������߂��Ă��܂��܂��ƁA
�Đ����萔�������Z����܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ł������܂�)
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z
�W���p���l�b�g��s�@
���~�W�x�X�@
���ʌ����@0015015
�Z�C�m�[�t�B�i���V�����i�J

�y�X�֐U�֌����z
�����L���F00100-7
�����ԍ��F292043
������ЃL���b�`�{�[���@�Z�C�m�[FC�W

���s���ȓ_�Ȃǂ������܂�����A���C�y�ɉ��L�܂ł��⍇���������B

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�@�{���ɓ���Ă��⍇�����������B

�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
���ڍw���X�ܗl�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}

�����x�����Ɋւ��邨�₢���킹�́F
�Z�C�m�[�t�B�i���V����������Ё@�㕥�����σT�[�r�X�S��
TEL:03-6908-7888 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
Mail: sfc-atobarai@seino.co.jp
',3,'2015/12/24 8:14:07',32,'2015/12/24 8:14:07',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (459,46,'�x���������߃��[���i�ĂT�j�iCEL�j','�㕥�����σT�[�r�X','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'�y�㕥�����σT�[�r�X�z{OrderDate}{SiteNameKj}�ł̂��������̌�({OrderId})','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɂ������̂��葱�������ς̂悤�ł����
�@�����[���ւ̕ԐM�͂��s�v�ł������܂��B

{CustomerNameKj}�l

����� {SiteNameKj}�l�ł̂���������
�y�㕥�����σT�[�r�X�z�������p���������܂��āA
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ClaimDate}�ɂ����肵���������̂��x�����������߂��Ă�
�������̊m�F�����Ă���܂���ł����̂�
�{���A�Đ������𔭍s�E�����������܂����B

��L����ł����m�点���Ă���ʂ�A
���x�������m�F�ł��Ȃ��ꍇ
���q�l�̐M�p����ȂǕs���v��������\�����������܂��B
���܂��Ă͑��₩�Ȃ��Ή������肢�������܂��B

�y���������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
�Đ����ǉ��萔���F{ReClaimFee}
�x�����Q���F{DamageInterest}
���������z�F{TotalAmount}

�����x���������߂��Ă��܂��܂��ƁA
�Đ����萔�������Z����܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ł������܂�)
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z
�W���p���l�b�g��s�@
���~�W�x�X�@
���ʌ����@0015015
�Z�C�m�[�t�B�i���V�����i�J

�y�X�֐U�֌����z
�����L���F00100-7
�����ԍ��F292043
������ЃL���b�`�{�[���@�Z�C�m�[FC�W

���s���ȓ_�Ȃǂ������܂�����A���C�y�ɉ��L�܂ł��⍇���������B

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�@�{���ɓ���Ă��⍇�����������B

�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
���ڍw���X�ܗl�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}

�����x�����Ɋւ��邨�₢���킹�́F
�Z�C�m�[�t�B�i���V����������Ё@�㕥�����σT�[�r�X�S��
TEL:03-6908-7888 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
Mail: sfc-atobarai@seino.co.jp
',3,'2015/12/24 8:15:09',32,'2015/12/24 8:15:09',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (460,45,'�x���������߃��[���i�ĂT�j�iPC�j','�㕥�����σT�[�r�X','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'�y�㕥�����σT�[�r�X�z{OrderDate}{SiteNameKj}�ł̂��������̌�({OrderId})','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɂ������̂��葱�������ς̂悤�ł����
�@�����[���ւ̕ԐM�͂��s�v�ł������܂��B

{CustomerNameKj}�l

����� {SiteNameKj}�l�ł̂���������
�y�㕥�����σT�[�r�X�z�������p���������܂��āA
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ClaimDate}�ɂ����肵���������̂��x�����������߂��Ă�
�������̊m�F�����Ă���܂���ł����̂�
�{���A�Đ������𔭍s�E�����������܂����B

��L����ł����m�点���Ă���ʂ�A
���x�������m�F�ł��Ȃ��ꍇ
���q�l�̐M�p����ȂǕs���v��������\�����������܂��B
���܂��Ă͑��₩�Ȃ��Ή������肢�������܂��B

�y���������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
�Đ����ǉ��萔���F{ReClaimFee}
�x�����Q���F{DamageInterest}
���������z�F{TotalAmount}

�����x���������߂��Ă��܂��܂��ƁA
�Đ����萔�������Z����܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ł������܂�)
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z
�W���p���l�b�g��s�@
���~�W�x�X�@
���ʌ����@0015015
�Z�C�m�[�t�B�i���V�����i�J

�y�X�֐U�֌����z
�����L���F00100-7
�����ԍ��F292043
������ЃL���b�`�{�[���@�Z�C�m�[FC�W

���s���ȓ_�Ȃǂ������܂�����A���C�y�ɉ��L�܂ł��⍇���������B

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�@�{���ɓ���Ă��⍇�����������B

�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
���ڍw���X�ܗl�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}

�����x�����Ɋւ��邨�₢���킹�́F
�Z�C�m�[�t�B�i���V����������Ё@�㕥�����σT�[�r�X�S��
TEL:03-6908-7888 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
Mail: sfc-atobarai@seino.co.jp
',3,'2015/12/24 8:15:43',32,'2015/12/24 8:15:43',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (461,47,'�x���������߃��[���i�ĂU�j�iPC�j','�㕥�����σT�[�r�X','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'�y�㕥�����σT�[�r�X�z{OrderDate}{SiteNameKj}�ł̂��������̌�({OrderId})','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɂ������̂��葱�������ς̂悤�ł����
�@�����[���ւ̕ԐM�͂��s�v�ł������܂��B

{CustomerNameKj}�l

����� {SiteNameKj}�l�ł̂���������
�y�㕥�����σT�[�r�X�z�������p���������܂��āA
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ClaimDate}�ɂ����肵���������̂��x�����������߂��Ă�
�������̊m�F�����Ă���܂���ł����̂�
�{���A�Đ������𔭍s�E�����������܂����B

���̂܂ܖ�������Ԃ��p������܂��ƁA
���Ђł̑Ή�������ƂȂ�
�ʒm�L�ڂ̑Ή��ƂȂ�ꍇ������܂��B
���܂��Ă͎��}���x�����ɂ���
���Ή����������܂��悤���肢�������܂��B

�y���������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
�Đ����ǉ��萔���F{ReClaimFee}
�x�����Q���F{DamageInterest}
���������z�F{TotalAmount}

�����x���������߂��Ă��܂��܂��ƁA
�Đ����萔�������Z����܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ł������܂�)
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z
�W���p���l�b�g��s�@
���~�W�x�X�@
���ʌ����@0015015
�Z�C�m�[�t�B�i���V�����i�J

�y�X�֐U�֌����z
�����L���F00100-7
�����ԍ��F292043
������ЃL���b�`�{�[���@�Z�C�m�[FC�W

���s���ȓ_�Ȃǂ������܂�����A���C�y�ɉ��L�܂ł��⍇���������B

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�@�{���ɓ���Ă��⍇�����������B

�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
���ڍw���X�ܗl�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}

�����x�����Ɋւ��邨�₢���킹�́F
�Z�C�m�[�t�B�i���V����������Ё@�㕥�����σT�[�r�X�S��
TEL:03-6908-7888 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
Mail: sfc-atobarai@seino.co.jp
',3,'2015/12/24 8:17:15',32,'2015/12/24 8:17:15',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (462,48,'�x���������߃��[���i�ĂU�j�iCEL�j','�㕥�����σT�[�r�X','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'�y�㕥�����σT�[�r�X�z{OrderDate}{SiteNameKj}�ł̂��������̌�({OrderId})','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɂ������̂��葱�������ς̂悤�ł����
�@�����[���ւ̕ԐM�͂��s�v�ł������܂��B

{CustomerNameKj}�l

����� {SiteNameKj}�l�ł̂���������
�y�㕥�����σT�[�r�X�z�������p���������܂��āA
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ClaimDate}�ɂ����肵���������̂��x�����������߂��Ă�
�������̊m�F�����Ă���܂���ł����̂�
�{���A�Đ������𔭍s�E�����������܂����B

���̂܂ܖ�������Ԃ��p������܂��ƁA
���Ђł̑Ή�������ƂȂ�
�ʒm�L�ڂ̑Ή��ƂȂ�ꍇ������܂��B
���܂��Ă͎��}���x�����ɂ���
���Ή����������܂��悤���肢�������܂��B

�y���������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
�Đ����ǉ��萔���F{ReClaimFee}
�x�����Q���F{DamageInterest}
���������z�F{TotalAmount}

�����x���������߂��Ă��܂��܂��ƁA
�Đ����萔�������Z����܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ł������܂�)
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z
�W���p���l�b�g��s�@
���~�W�x�X�@
���ʌ����@0015015
�Z�C�m�[�t�B�i���V�����i�J

�y�X�֐U�֌����z
�����L���F00100-7
�����ԍ��F292043
������ЃL���b�`�{�[���@�Z�C�m�[FC�W

���s���ȓ_�Ȃǂ������܂�����A���C�y�ɉ��L�܂ł��⍇���������B

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�@�{���ɓ���Ă��⍇�����������B

�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
���ڍw���X�ܗl�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}

�����x�����Ɋւ��邨�₢���킹�́F
�Z�C�m�[�t�B�i���V����������Ё@�㕥�����σT�[�r�X�S��
TEL:03-6908-7888 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
Mail: sfc-atobarai@seino.co.jp
',3,'2015/12/24 8:17:40',32,'2015/12/24 8:17:40',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (463,49,'�x���������߃��[���i�ĂV�j�iPC�j','�㕥�����σT�[�r�X','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'�y�㕥�����σT�[�r�X�z{OrderDate}{SiteNameKj}�ł̂��������̌�({OrderId})','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɂ������̂��葱�������ς̂悤�ł����
�@�����[���ւ̕ԐM�͂��s�v�ł������܂��B

{CustomerNameKj}�l

����� {SiteNameKj}�l�ł̂���������
�y�㕥�����σT�[�r�X�z�������p���������܂��āA
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ClaimDate}�ɂ����肵���������̂��x�����������߂��Ă�
�������̊m�F�����Ă���܂���ł����̂�
�{���A�Đ������𔭍s�E�����������܂����B

�ĎO�ɂ킽��A���ԍςɑ΂����s�����悤���ʒm�������܂�����
�M�a��落�ӂ̂��邲�Ή��𒸂��Ă��Ȃ��󋵂ƂȂ��Ă���܂��B
����ɂ��܂��Ă��A���A���E���x�������m�F�ł��Ȃ��ꍇ��
�ٌ�m�ւ̉���ϔC�������͖@�I�葱���Ɉڍs����������܂���B
�������Ȃ���A���Ѝ��Ǘ����ł�
�M�a�̍����s�ɑ΂�������}��ׂ̑��k������݂��Ă���
���k�ɂ��������\�ȏꍇ���������܂��B
���܂��ẮA�����Ɍ������}���A�����������܂��悤���肢�������܂��B

�y���������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
�Đ����ǉ��萔���F{ReClaimFee}
�x�����Q���F{DamageInterest}
���������z�F{TotalAmount}

�����x���������߂��Ă��܂��܂��ƁA
�Đ����萔�������Z����܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ł������܂�)
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z
�W���p���l�b�g��s�@
���~�W�x�X�@
���ʌ����@0015015
�Z�C�m�[�t�B�i���V�����i�J

�y�X�֐U�֌����z
�����L���F00100-7
�����ԍ��F292043
������ЃL���b�`�{�[���@�Z�C�m�[FC�W

���s���ȓ_�Ȃǂ������܂�����A���C�y�ɉ��L�܂ł��⍇���������B

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�@�{���ɓ���Ă��⍇�����������B

�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
���ڍw���X�ܗl�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}

�����x�����Ɋւ��邨�₢���킹�́F
�Z�C�m�[�t�B�i���V����������Ё@�㕥�����σT�[�r�X�S��
TEL:03-6908-7888 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
Mail: sfc-atobarai@seino.co.jp
',3,'2015/12/24 8:18:28',32,'2015/12/24 8:20:46',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (464,50,'�x���������߃��[���i�ĂV�j�iCEL�j','�㕥�����σT�[�r�X','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'�y�㕥�����σT�[�r�X�z{OrderDate}{SiteNameKj}�ł̂��������̌�({OrderId})','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɂ������̂��葱�������ς̂悤�ł����
�@�����[���ւ̕ԐM�͂��s�v�ł������܂��B

{CustomerNameKj}�l

����� {SiteNameKj}�l�ł̂���������
�y�㕥�����σT�[�r�X�z�������p���������܂��āA
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ClaimDate}�ɂ����肵���������̂��x�����������߂��Ă�
�������̊m�F�����Ă���܂���ł����̂�
�{���A�Đ������𔭍s�E�����������܂����B

�ĎO�ɂ킽��A���ԍςɑ΂����s�����悤���ʒm�������܂�����
�M�a��落�ӂ̂��邲�Ή��𒸂��Ă��Ȃ��󋵂ƂȂ��Ă���܂��B
����ɂ��܂��Ă��A���A���E���x�������m�F�ł��Ȃ��ꍇ��
�ٌ�m�ւ̉���ϔC�������͖@�I�葱���Ɉڍs����������܂���B
�������Ȃ���A���Ѝ��Ǘ����ł�
�M�a�̍����s�ɑ΂�������}��ׂ̑��k������݂��Ă���
���k�ɂ��������\�ȏꍇ���������܂��B
���܂��ẮA�����Ɍ������}���A�����������܂��悤���肢�������܂��B

�y���������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
�Đ����ǉ��萔���F{ReClaimFee}
�x�����Q���F{DamageInterest}
���������z�F{TotalAmount}

�����x���������߂��Ă��܂��܂��ƁA
�Đ����萔�������Z����܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ł������܂�)
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z
�W���p���l�b�g��s�@
���~�W�x�X�@
���ʌ����@0015015
�Z�C�m�[�t�B�i���V�����i�J

�y�X�֐U�֌����z
�����L���F00100-7
�����ԍ��F292043
������ЃL���b�`�{�[���@�Z�C�m�[FC�W

���s���ȓ_�Ȃǂ������܂�����A���C�y�ɉ��L�܂ł��⍇���������B

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�@�{���ɓ���Ă��⍇�����������B

�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
���ڍw���X�ܗl�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}

�����x�����Ɋւ��邨�₢���킹�́F
�Z�C�m�[�t�B�i���V����������Ё@�㕥�����σT�[�r�X�S��
TEL:03-6908-7888 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
Mail: sfc-atobarai@seino.co.jp
',3,'2015/12/24 8:21:19',32,'2015/12/24 8:21:19',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (465,51,'CB�����������܂Ƃ߃G���[���[��','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer@ato-barai.com',null,null,null,'','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','�ȉ��̎��Ǝ҂Ŏ��܂Ƃ߂Ɏ��s���܂����B

���ƎҖ��F
{EnterpriseNameKj}

���܂Ƃ߂Ɏ��s�������܂Ƃߎw���O���[�v�̒����F
{OrderSummary}

���R�F
{Error}
',2,'2015/12/24 8:21:55',32,'2015/12/24 8:21:55',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (466,52,'���ƎҌ����������܂Ƃ߃G���[���[��','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer@ato-barai.com',null,null,null,'�y�㕥���h�b�g�R���z�������܂Ƃ߃G���[���[��','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{EnterpriseNameKj} �l

������ς����b�ɂȂ��Ă���܂��B
�㕥���h�b�g�R���J�X�^�}�[�Z���^�[�ł������܂��B

�������܂Ƃߎ��s���ɃG���[���������܂����B 
���L�̂������𐿋����܂Ƃߒ����ꗗ����m�F�����A���߂Č�w�������肢�v���܂��B
                      
���܂Ƃ߂Ɏ��s�������܂Ƃߎw���O���[�v�̒����F
{OrderSummary}

���R�F
{Error}
',2,'2015/12/24 8:22:59',32,'2015/12/24 8:23:04',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (467,53,'�}�C�y�[�W���o�^�������[���iPC�j','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�y�㕥���h�b�g�R���z������o�^�̂��ē�','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','
���̓x�͌㕥���h�b�g�R���ɂ��\���݂�������
�܂��Ƃɂ��肪�Ƃ��������܂��B

���L��URL���N���b�N���Č㕥���h�b�g�R���ł�
����o�^��i�߂Ă��������B

{MypageRegistUrl}


�������ӎ�����
�E�{���[�������󂯎���A�Q�S���Ԉȓ���
�㕥���h�b�g�R������o�^�����������܂��悤���肢�������܂��B
�E�Q�S���Ԉȓ��ɂ��o�^����������Ȃ��ꍇ��
���o�^�̂��葱���������ƂȂ�܂��̂ł��炩���߂������肢�܂��B
�E�Q�S���Ԃ��߂��Ă��܂����ꍇ�́A
�������܂����ēx���o�^�̂��葱�������肢�������܂��B


------------------------------------
���o�^�̎菇�ɂ���
------------------------------------

�P.��LURL�ɃA�N�Z�X���A��ʂɂ��������ĕK�v�����������͂��������B

�Q.�����͓��e�����m�F�̂����A���q�l����o�^���Ă��������B

�R.�g�y�㕥���h�b�g�R���z����o�^�����̂��m�点�h�Ƃ������[�����͂��܂��B

�ȏ�Ō㕥���h�b�g�R������o�^�����ƂȂ�܂��B


------------------------------------
���o�^�����܂������Ȃ��ꍇ�́A
��ϋ������܂���customer@ato-barai.com�܂�
���₢���킹�����肢�������܂��B

���c�Ǝ��ԊO�̂��₢���킹�ɂ��܂��Ă�
���ԐM�ɂ����Ԃ����������ꍇ���������܂��B


���̓x�͂��\���݂��肪�Ƃ��������܂����B


--------------------------------------------------------------
�y�㕥���h�b�g�R���z

  ���⍇����F03-5332-3490
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com
  
�@�^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F
--------------------------------------------------------------
',2,'2015/12/24 8:24:01',32,'2015/12/24 8:24:17',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (468,54,'�}�C�y�[�W���o�^�������[���iCEL�j','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�y�㕥���h�b�g�R���z������o�^�̂��ē�','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','���̓x�͌㕥���h�b�g�R���ɂ��\���݂�������
�܂��Ƃɂ��肪�Ƃ��������܂��B

���L��URL���N���b�N���Č㕥���h�b�g�R���ł�
����o�^��i�߂Ă��������B

{MypageRegistUrl}


�������ӎ�����
�E�{���[�������󂯎���A�Q�S���Ԉȓ���
�㕥���h�b�g�R������o�^�����������܂��悤���肢�������܂��B
�E�Q�S���Ԉȓ��ɂ��o�^����������Ȃ��ꍇ��
���o�^�̂��葱���������ƂȂ�܂��̂ł��炩���߂������肢�܂��B
�E�Q�S���Ԃ��߂��Ă��܂����ꍇ�́A
�������܂����ēx���o�^�̂��葱�������肢�������܂��B


------------------------------------
���o�^�̎菇�ɂ���
------------------------------------

�P.��LURL�ɃA�N�Z�X���A��ʂɂ��������ĕK�v�����������͂��������B

�Q.�����͓��e�����m�F�̂����A���q�l����o�^���Ă��������B

�R.�g�y�㕥���h�b�g�R���z����o�^�����̂��m�点�h�Ƃ������[�����͂��܂��B

�ȏ�Ō㕥���h�b�g�R������o�^�����ƂȂ�܂��B


------------------------------------
���o�^�����܂������Ȃ��ꍇ�́A
��ϋ������܂���customer@ato-barai.com�܂�
���₢���킹�����肢�������܂��B

���c�Ǝ��ԊO�̂��₢���킹�ɂ��܂��Ă�
���ԐM�ɂ����Ԃ����������ꍇ���������܂��B


���̓x�͂��\���݂��肪�Ƃ��������܂����B


--------------------------------------------------------------
�y�㕥���h�b�g�R���z

  ���⍇����F03-5332-3490
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com
  
�@�^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F
--------------------------------------------------------------
',2,'2015/12/24 8:24:58',32,'2015/12/24 8:24:58',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (469,55,'�}�C�y�[�W�{�o�^�������[���iPC�j','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�y�㕥���h�b�g�R���z����o�^�����̂��m�点','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{MyPageNameKj}�l

���̓x�͌㕥���h�b�g�R���ɂ��\���݂�������
�܂��Ƃɂ��肪�Ƃ��������܂��B
�㕥���h�b�g�R���ł̉���o�^�������������܂����B


------------------------------------


�������[���ɂ��S������̂Ȃ����́A
�������܂������L���[���A�h���X�܂ł��A�������肢�������܂��B
�܂��A���T�[�r�X�Ɋւ��邻�̑��̂��₢���킹��
���L�A�h���X�ɂď����Ă���܂��B

���̓x�͂��\���݂��肪�Ƃ��������܂����B


--------------------------------------------------------------
�y�㕥���h�b�g�R���z

  ���⍇����F03-5332-3490
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com
  
�@�^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F
--------------------------------------------------------------
',2,'2015/12/24 8:25:41',32,'2015/12/24 8:25:41',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (470,56,'�}�C�y�[�W�{�o�^�������[���iCEL�j','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�y�㕥���h�b�g�R���z����o�^�����̂��m�点','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{MyPageNameKj}�l

���̓x�͌㕥���h�b�g�R���ɂ��\���݂�������
�܂��Ƃɂ��肪�Ƃ��������܂��B
�㕥���h�b�g�R���ł̉���o�^�������������܂����B


------------------------------------


�������[���ɂ��S������̂Ȃ����́A
�������܂������L���[���A�h���X�܂ł��A�������肢�������܂��B
�܂��A���T�[�r�X�Ɋւ��邻�̑��̂��₢���킹��
���L�A�h���X�ɂď����Ă���܂��B

���̓x�͂��\���݂��肪�Ƃ��������܂����B


--------------------------------------------------------------
�y�㕥���h�b�g�R���z

  ���⍇����F03-5332-3490
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com
  
�@�^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F
--------------------------------------------------------------
',2,'2015/12/24 8:26:07',32,'2015/12/24 8:26:07',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (471,57,'�}�C�y�[�W�p�X���[�h�ύX���[���iPC�j','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�y�㕥���h�b�g�R���z�p�X���[�h�ύX������܂���','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{MyPageNameKj}�l

�����㕥���h�b�g�R���������p��������
�܂��Ƃɂ��肪�Ƃ��������܂��B

�}�C�y�[�W�̃p�X���[�h�ύX������܂����̂ł��񍐂������܂��B

���LURL�փA�N�Z�X���A���O�C�����s���Ă��������B

https://www.atobarai.jp/mypage

�������[���ɂ��S������̂Ȃ����́A
�������܂������L���[���A�h���X�܂ł��A�������肢�������܂��B
�܂��A���T�[�r�X�Ɋւ��邻�̑��̂��₢���킹��
���L�A�h���X�ɂď����Ă���܂��B

����Ƃ��A���T�[�r�X����낵�����肢�������܂��B

--------------------------------------------------------------
�y�㕥���h�b�g�R���z

  ���⍇����F03-5332-3490
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com
  
�@�^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F
--------------------------------------------------------------
',2,'2015/12/24 8:27:23',32,'2015/12/24 8:27:23',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (472,58,'�}�C�y�[�W�p�X���[�h�ύX���[���iCEL�j','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�y�㕥���h�b�g�R���z�p�X���[�h�ύX������܂���','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{MyPageNameKj}�l

�����㕥���h�b�g�R���������p��������
�܂��Ƃɂ��肪�Ƃ��������܂��B

�}�C�y�[�W�̃p�X���[�h�ύX������܂����̂ł��񍐂������܂��B

���LURL�փA�N�Z�X���A���O�C�����s���Ă��������B

https://www.atobarai.jp/mypage

�������[���ɂ��S������̂Ȃ����́A
�������܂������L���[���A�h���X�܂ł��A�������肢�������܂��B
�܂��A���T�[�r�X�Ɋւ��邻�̑��̂��₢���킹��
���L�A�h���X�ɂď����Ă���܂��B

����Ƃ��A���T�[�r�X����낵�����肢�������܂��B

--------------------------------------------------------------
�y�㕥���h�b�g�R���z

  ���⍇����F03-5332-3490
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com
  
�@�^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F
--------------------------------------------------------------
',2,'2015/12/24 8:27:40',32,'2015/12/24 8:27:40',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (473,59,'�}�C�y�[�W�މ�����[���iPC�j','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�y�㕥���h�b�g�R���z�މ���̂��m�点','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{MyPageNameKj}�l

���̓x�͌㕥���h�b�g�R���������p��������
�܂��Ƃɂ��肪�Ƃ��������܂����B

�މ�葱���������������܂����̂ł��񍐂������܂��B

�܂��̂����p��S��肨�҂����Ă���܂��B

--------------------------------------------------------------

���̃��[���͑މ�葱�������ꂽ���[���A�h���X��
�����Ŕz�M���Ă���܂��B
�ēx����o�^�������ۂ́A���LURL�փA�N�Z�X���������܂��B

https://www.atobarai.jp/mypage

--------------------------------------------------------------
�y�㕥���h�b�g�R���z

  ���⍇����F03-5332-3490
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com
  
�@�^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F
--------------------------------------------------------------
',2,'2015/12/24 8:28:04',32,'2015/12/24 8:28:04',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (474,60,'�}�C�y�[�W�މ�����[���iCEL�j','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�y�㕥���h�b�g�R���z�މ���̂��m�点','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{MyPageNameKj}�l

���̓x�͌㕥���h�b�g�R���������p��������
�܂��Ƃɂ��肪�Ƃ��������܂����B

�މ�葱���������������܂����̂ł��񍐂������܂��B

�܂��̂����p��S��肨�҂����Ă���܂��B

--------------------------------------------------------------

���̃��[���͑މ�葱�������ꂽ���[���A�h���X��
�����Ŕz�M���Ă���܂��B
�ēx����o�^�������ۂ́A���LURL�փA�N�Z�X���������܂��B

https://www.atobarai.jp/mypage

--------------------------------------------------------------
�y�㕥���h�b�g�R���z

  ���⍇����F03-5332-3490
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com
  
�@�^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F
--------------------------------------------------------------
',2,'2015/12/24 8:28:21',32,'2015/12/24 8:28:21',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (475,61,'�Г��^�M�ۗ����[��','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer@ato-barai.com',null,null,null,'�y�㕥���h�b�g�R���z�^�M�̌��ł��m�F�����肢�������܂�','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{EnterpriseNameKj}�l

������ς����b�ɂȂ��Ă���܂��B
�㕥���h�b�g�R���J�X�^�}�[�Z���^�[�ł������܂��B

�{���^�M�����������܂���

{OrderId} {CustomerNameKj}�l�ł���

{PendingReason}�B

{PendingDate}�܂ŗ^�M�ۗ��Ƃ����Ă��������܂��̂�
���萔�ł͂������܂����A�������������m�F��������
�Ǘ��T�C�g��ł��ύX�̏���������������
���Ђ܂ł��A�������������܂��悤���肢�������܂��B


�����������������������@�������C���������ۂ̒��Ӂ@����������������������

�C�����e�������͂�����������A�u���̓��e�œo�^�v���N���b�N�����
���e�̊m�F��ʂɑJ�ڂ��܂��B���e�����m�F�̂����A������x
�u���̓��e�œo�^�v���N���b�N����ƏC���������ƂȂ�܂��B
�i���m�F��ʂ���ʂ̃y�[�W�Ɉڂ��Ă��܂�����
���Ă��܂����肷��ƁA�C�������f����܂���B�j

������������������������������������������������������������������������

�y�Ǘ���ʂt�q�k�z
https://www.atobarai.jp/member/

���s���ȓ_�Ȃǂ������܂�����A���C�y�ɂ��₢���킹���������܂��B

������낵�����肢�������܂��B

--------------------------------------------------------------
�y�㕥���h�b�g�R���z

  ���⍇����F0120-667-690
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com

  �^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F
--------------------------------------------------------------
',2,'2015/12/24 8:28:57',32,'2015/12/24 8:28:57',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (476,81,'�s�������A�����[���iPC�j','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�y�㕥���h�b�g�R���z�������z���s�����Ă���܂�','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

{CustomerNameKj}�l

���̓x��{SiteNameKj}�ŏ��i���w���̍ۂɁA
�㕥���h�b�g�R���������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ReceiptDate}��{ReceiptClass}���{UseAmount}�~�̂����������������܂������A
{ShortfallAmount}�~���s���ƂȂ��Ă���܂��B

��ς��萔�ł����s������{ShortfallAmount}�~��
���L�����܂ł��U���݂��������܂��悤���肢�������܂��B

�y��s�U�������z
�O��Z�F��s�@�V�h�ʎx�X
���ʌ����@8047001
�J�j�L���b�`�{�[��

�y�X�֐U�֌����z
�����L���F00120-7
�����ԍ��F670031
������ЃL���b�`�{�[��

�s���_�Ȃǂ������܂�����
���C�y�ɂ��⍇�����������܂��B

�����A��낵�����肢�������܂��B

--------------------------------------------------------------
�y�㕥���h�b�g�R���z

  ���⍇����F03-5332-3490
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com
  
�@�^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F
--------------------------------------------------------------
',2,'2015/12/24 8:29:41',32,'2015/12/24 8:31:27',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (477,82,'�s�������A�����[���iCEL�j','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�y�㕥���h�b�g�R���z�������z���s�����Ă���܂�','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

{CustomerNameKj}�l

���̓x��{SiteNameKj}�ŏ��i���w���̍ۂɁA
�㕥���h�b�g�R���������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ReceiptDate}��{ReceiptClass}���{UseAmount}�~�̂����������������܂������A
{ShortfallAmount}�~���s���ƂȂ��Ă���܂��B

��ς��萔�ł����s������{ShortfallAmount}�~��
���L�����܂ł��U���݂��������܂��悤���肢�������܂��B

�y��s�U�������z
�O��Z�F��s�@�V�h�ʎx�X
���ʌ����@8047001
�J�j�L���b�`�{�[��

�y�X�֐U�֌����z
�����L���F00120-7
�����ԍ��F670031
������ЃL���b�`�{�[��

�s���_�Ȃǂ������܂�����
���C�y�ɂ��⍇�����������܂��B

�����A��낵�����肢�������܂��B

--------------------------------------------------------------
�y�㕥���h�b�g�R���z

  ���⍇����F03-5332-3490
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com
  
�@�^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F
--------------------------------------------------------------
',2,'2015/12/24 8:31:45',32,'2015/12/24 8:31:45',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (478,86,'���Ǝ҃��[���o�^�o�b�`�G���[���[��','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer@ato-barai.com',null,null,null,'�y�㕥���h�b�g�R���z���Ǝ҃��[���o�^�o�b�`�G���[���[��','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','�ȉ��̎��Ǝғo�^���[���ɑ΂��鏈���Ɏ��s���܂����B

------------------------------
{body}',2,'2015/12/24 8:32:38',32,'2015/12/24 8:32:38',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (479,87,'�ԋ����[���i�L�����Z���j(PC)','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�㕥���h�b�g�R���z���ԋ��̂��A��','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

{CustomerNameKj}�l

���̓x��{SiteNameKj}�ŏ��i���w���̍ۂɁA
�㕥���h�b�g�R���������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂����B

{ReceiptDate}��{ReceiptClass}��育�������m�F�������܂������A
���ɓX�ܗl��育�����L�����Z���̂��A�������������Ă���܂����̂�
���ԋ������Ă������������A�����̊m�F�̂��A���������グ�܂����B

���萔�ł͂������܂���
�E��s���F
�E�x�X���F
�E������ځF
�E�����ԍ��F
�E�������`(�J�i)�F
��L�����L���̂����A�����[���ւ��ԐM���������܂��B

�s���_�Ȃǂ������܂�����A���C�y�ɂ��⍇�����������܂��B

������낵�����肢�������܂��B

--------------------------------------------------------------
�y�㕥���h�b�g�R���z

  ���⍇����F03-5332-3490
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com
  
�@�^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F
-------------------------------------------------------------- 
',2,'2015/12/24 8:33:01',32,'2015/12/24 8:33:01',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (480,88,'�ԋ����[���i�L�����Z���j(CEL)','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�y�㕥���h�b�g�R���z���ԋ��̂��A��','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

{CustomerNameKj}�l

���̓x��{SiteNameKj}�ŏ��i���w���̍ۂɁA
�㕥���h�b�g�R���������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂����B

{ReceiptDate}��{ReceiptClass}��育�������m�F�������܂������A
���ɓX�ܗl��育�����L�����Z���̂��A�������������Ă���܂����̂�
���ԋ������Ă������������A�����̊m�F�̂��A���������グ�܂����B

���萔�ł͂������܂���
�E��s���F
�E�x�X���F
�E������ځF
�E�����ԍ��F
�E�������`(�J�i)�F
��L�����L���̂����A�����[���ւ��ԐM���������܂��B

�s���_�Ȃǂ������܂�����A���C�y�ɂ��⍇�����������܂��B

������낵�����肢�������܂��B

--------------------------------------------------------------
�y�㕥���h�b�g�R���z

  ���⍇����F03-5332-3490
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com
  
�@�^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F
-------------------------------------------------------------- 
',2,'2015/12/24 8:33:24',32,'2015/12/24 8:33:24',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (481,51,'CB�����������܂Ƃ߃G���[���[��','�㕥�����σT�[�r�X','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'�������܂Ƃ߃G���[���[��','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','�ȉ��̎��Ǝ҂Ŏ��܂Ƃ߂Ɏ��s���܂����B

���ƎҖ��F
{EnterpriseNameKj}

���܂Ƃ߂Ɏ��s�������܂Ƃߎw���O���[�v�̒����F
{OrderSummary}

���R�F
{Error}
',3,'2015/12/24 8:39:08',32,'2015/12/24 8:39:08',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (482,52,'���ƎҌ����������܂Ƃ߃G���[���[��','�㕥�����σT�[�r�X','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'�y�㕥�����σT�[�r�X�z�������܂Ƃ߃G���[���[��','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','{EnterpriseNameKj} �l

�����y�㕥�����σT�[�r�X�z�������p���������܂��āA
�܂��Ƃɂ��肪�Ƃ��������܂��B

�������܂Ƃߎ��s���ɃG���[���������܂����B 
���L�̂������𐿋����܂Ƃߒ����ꗗ����m�F�����A���߂Č�w�������肢�v���܂��B
                      
���܂Ƃ߂Ɏ��s�������܂Ƃߎw���O���[�v�̒����F
{OrderSummary}

���R�F
{Error}
',3,'2015/12/24 8:41:18',32,'2015/12/24 8:42:32',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (483,53,'�}�C�y�[�W���o�^�������[���iPC�j','�㕥�����σT�[�r�X','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','customer2@ato-barai.com',null,null,null,'�y�㕥�����σ}�C�y�[�W�z������o�^�̂��ē�','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','���̓x�́y�㕥�����σ}�C�y�[�W�z�ɂ��\���݂�������
�܂��Ƃɂ��肪�Ƃ��������܂��B

���L��URL���N���b�N���āy�㕥�����σ}�C�y�[�W�z�ł�
����o�^��i�߂Ă��������B

{MypageRegistUrl}


�������ӎ�����
�E�{���[�������󂯎���A�Q�S���Ԉȓ���
  �y�㕥�����σ}�C�y�[�W�z����o�^�����������܂��悤���肢�������܂��B
�E�Q�S���Ԉȓ��ɂ��o�^����������Ȃ��ꍇ��
  ���o�^�̂��葱���������ƂȂ�܂��̂ł��炩���߂������肢�܂��B
�E�Q�S���Ԃ��߂��Ă��܂����ꍇ�́A
  �������܂����ēx���o�^�̂��葱�������肢�������܂��B


------------------------------------
���o�^�̎菇�ɂ���
------------------------------------

�P.��LURL�ɃA�N�Z�X���A��ʂɂ��������ĕK�v�����������͂��������B

�Q.�����͓��e�����m�F�̂����A���q�l����o�^���Ă��������B

�R.�g�y�㕥�����σ}�C�y�[�W�z����o�^�����̂��m�点�h�Ƃ������[�����͂��܂��B

�ȏ�Ły�㕥�����σ}�C�y�[�W�z����o�^�����ƂȂ�܂��B


------------------------------------
���o�^�����܂������Ȃ��ꍇ�́A
��ϋ������܂��� sfc-atobarai@seino.co.jp �܂�
���₢���킹�����肢�������܂��B

���c�Ǝ��ԊO�̂��₢���킹�ɂ��܂��Ă�
  ���ԐM�ɂ����Ԃ����������ꍇ���������܂��B


���̓x�͂��\���݂��肪�Ƃ��������܂����B


--------------------------------------------------------------

�y�㕥�����σT�[�r�X�z
  ���⍇����F03-6908-7888
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: sfc-atobarai@seino.co.jp

  �^�c��ЁF�Z�C�m�[�t�B�i���V�����������
�@�Z���F��503-8501 �򕌌���_�s�c�����P�Ԓn

--------------------------------------------------------------
',3,'2015/12/24 8:44:34',32,'2015/12/24 15:54:44',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (484,54,'�}�C�y�[�W���o�^�������[���iCEL�j','�㕥�����σT�[�r�X','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','customer2@ato-barai.com',null,null,null,'�y�㕥�����σ}�C�y�[�W�z������o�^�̂��ē�','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','���̓x�́y�㕥�����σ}�C�y�[�W�z�ɂ��\���݂�������
�܂��Ƃɂ��肪�Ƃ��������܂��B

���L��URL���N���b�N���āy�㕥�����σ}�C�y�[�W�z�ł�
����o�^��i�߂Ă��������B

{MypageRegistUrl}


�������ӎ�����
�E�{���[�������󂯎���A�Q�S���Ԉȓ���
  �y�㕥�����σ}�C�y�[�W�z����o�^�����������܂��悤���肢�������܂��B
�E�Q�S���Ԉȓ��ɂ��o�^����������Ȃ��ꍇ��
  ���o�^�̂��葱���������ƂȂ�܂��̂ł��炩���߂������肢�܂��B
�E�Q�S���Ԃ��߂��Ă��܂����ꍇ�́A
  �������܂����ēx���o�^�̂��葱�������肢�������܂��B


------------------------------------
���o�^�̎菇�ɂ���
------------------------------------

�P.��LURL�ɃA�N�Z�X���A��ʂɂ��������ĕK�v�����������͂��������B

�Q.�����͓��e�����m�F�̂����A���q�l����o�^���Ă��������B

�R.�g�y�㕥�����σ}�C�y�[�W�z����o�^�����̂��m�点�h�Ƃ������[�����͂��܂��B

�ȏ�Ły�㕥�����σ}�C�y�[�W�z����o�^�����ƂȂ�܂��B


------------------------------------
���o�^�����܂������Ȃ��ꍇ�́A
��ϋ������܂��� sfc-atobarai@seino.co.jp �܂�
���₢���킹�����肢�������܂��B

���c�Ǝ��ԊO�̂��₢���킹�ɂ��܂��Ă�
  ���ԐM�ɂ����Ԃ����������ꍇ���������܂��B


���̓x�͂��\���݂��肪�Ƃ��������܂����B


--------------------------------------------------------------

�y�㕥�����σT�[�r�X�z
  ���⍇����F03-6908-7888
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: sfc-atobarai@seino.co.jp

  �^�c��ЁF�Z�C�m�[�t�B�i���V�����������
�@�Z���F��503-8501 �򕌌���_�s�c�����P�Ԓn

--------------------------------------------------------------
',3,'2015/12/24 11:26:28',32,'2015/12/24 15:54:51',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (485,55,'�}�C�y�[�W�{�o�^�������[���iPC�j','�㕥�����σT�[�r�X','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'�y�㕥�����σ}�C�y�[�W�z����o�^�����̂��m�点','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','���̓x�́y�㕥�����σ}�C�y�[�W�z�ɂ��\���݂�������
�܂��Ƃɂ��肪�Ƃ��������܂��B

�y�㕥�����σ}�C�y�[�W�z�ł̉���o�^�������������܂����B


------------------------------------


�������[���ɂ��S������̂Ȃ����́A
�������܂������L���[���A�h���X�܂ł��A�������肢�������܂��B
�܂��A���T�[�r�X�Ɋւ��邻�̑��̂��₢���킹��
���L�A�h���X�ɂď����Ă���܂��B

���̓x�͂��\���݂��肪�Ƃ��������܂����B


--------------------------------------------------------------

�y�㕥�����σT�[�r�X�z
  ���⍇����F03-6908-7888
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: sfc-atobarai@seino.co.jp

  �^�c��ЁF�Z�C�m�[�t�B�i���V�����������
�@�Z���F��503-8501 �򕌌���_�s�c�����P�Ԓn

--------------------------------------------------------------
',3,'2015/12/24 11:27:02',32,'2015/12/24 11:27:02',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (486,56,'�}�C�y�[�W�{�o�^�������[���iCEL�j','�㕥�����σT�[�r�X','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'�y�㕥�����σ}�C�y�[�W�z����o�^�����̂��m�点','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','���̓x�́y�㕥�����σ}�C�y�[�W�z�ɂ��\���݂�������
�܂��Ƃɂ��肪�Ƃ��������܂��B

�y�㕥�����σ}�C�y�[�W�z�ł̉���o�^�������������܂����B


------------------------------------


�������[���ɂ��S������̂Ȃ����́A
�������܂������L���[���A�h���X�܂ł��A�������肢�������܂��B
�܂��A���T�[�r�X�Ɋւ��邻�̑��̂��₢���킹��
���L�A�h���X�ɂď����Ă���܂��B

���̓x�͂��\���݂��肪�Ƃ��������܂����B


--------------------------------------------------------------

�y�㕥�����σT�[�r�X�z
  ���⍇����F03-6908-7888
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: sfc-atobarai@seino.co.jp

  �^�c��ЁF�Z�C�m�[�t�B�i���V�����������
�@�Z���F��503-8501 �򕌌���_�s�c�����P�Ԓn

--------------------------------------------------------------
',3,'2015/12/24 11:27:43',32,'2015/12/24 11:27:43',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (487,57,'�}�C�y�[�W�p�X���[�h�ύX���[���iPC�j','�㕥�����σT�[�r�X','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','customer2@ato-barai.com',null,null,null,'�y�㕥�����σ}�C�y�[�W�z�p�X���[�h�ύX������܂���','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','�����㕥�����σ}�C�y�[�W�����p��������
�܂��Ƃɂ��肪�Ƃ��������܂��B

�}�C�y�[�W�̃p�X���[�h�ύX������܂����̂ł��񍐂������܂��B

���LURL�փA�N�Z�X���A���O�C�����s���Ă��������B


https://atobarai.seino.co.jp/seino-financial/mypage



�������[���ɂ��S������̂Ȃ����́A
�������܂������L���[���A�h���X�܂ł��A�������肢�������܂��B
�܂��A���T�[�r�X�Ɋւ��邻�̑��̂��₢���킹��
���L�A�h���X�ɂď����Ă���܂��B

����Ƃ��A���T�[�r�X����낵�����肢�������܂��B


--------------------------------------------------------------

�y�㕥�����σT�[�r�X�z
  ���⍇����F03-6908-7888
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: sfc-atobarai@seino.co.jp

  �^�c��ЁF�Z�C�m�[�t�B�i���V�����������
�@�Z���F��503-8501 �򕌌���_�s�c�����P�Ԓn

--------------------------------------------------------------
',3,'2015/12/24 11:28:23',32,'2015/12/24 11:28:23',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (488,58,'�}�C�y�[�W�p�X���[�h�ύX���[���iCEL�j','�㕥�����σT�[�r�X','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','customer2@ato-barai.com',null,null,null,'�y�㕥�����σ}�C�y�[�W�z�p�X���[�h�ύX������܂���','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','�����㕥�����σ}�C�y�[�W�����p��������
�܂��Ƃɂ��肪�Ƃ��������܂��B

�}�C�y�[�W�̃p�X���[�h�ύX������܂����̂ł��񍐂������܂��B

���LURL�փA�N�Z�X���A���O�C�����s���Ă��������B


https://atobarai.seino.co.jp/seino-financial/mypage



�������[���ɂ��S������̂Ȃ����́A
�������܂������L���[���A�h���X�܂ł��A�������肢�������܂��B
�܂��A���T�[�r�X�Ɋւ��邻�̑��̂��₢���킹��
���L�A�h���X�ɂď����Ă���܂��B

����Ƃ��A���T�[�r�X����낵�����肢�������܂��B


--------------------------------------------------------------

�y�㕥�����σT�[�r�X�z
  ���⍇����F03-6908-7888
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: sfc-atobarai@seino.co.jp

  �^�c��ЁF�Z�C�m�[�t�B�i���V�����������
�@�Z���F��503-8501 �򕌌���_�s�c�����P�Ԓn

--------------------------------------------------------------
',3,'2015/12/24 11:28:56',32,'2015/12/24 11:28:56',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (489,59,'�}�C�y�[�W�މ�����[���iPC�j','�㕥�����σT�[�r�X','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'�y�㕥�����σ}�C�y�[�W�z�މ���̂��m�点','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','���̓x�́y�㕥�����σ}�C�y�[�W�z�����p��������
�܂��Ƃɂ��肪�Ƃ��������܂����B

�މ�葱���������������܂����̂ł��񍐂������܂��B

�܂��̂����p��S��肨�҂����Ă���܂��B

--------------------------------------------------------------

���̃��[���͑މ�葱�������ꂽ���[���A�h���X��
�����Ŕz�M���Ă���܂��B
�ēx����o�^�������ۂ́A���LURL�փA�N�Z�X���������܂��B

https://atobarai.seino.co.jp/seino-financial/mypage

--------------------------------------------------------------

�y�㕥�����σT�[�r�X�z
  ���⍇����F03-6908-7888
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: sfc-atobarai@seino.co.jp

  �^�c��ЁF�Z�C�m�[�t�B�i���V�����������
�@�Z���F��503-8501 �򕌌���_�s�c�����P�Ԓn

--------------------------------------------------------------',3,'2015/12/24 11:29:43',32,'2015/12/24 11:29:43',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (490,60,'�}�C�y�[�W�މ�����[���iCEL�j','�㕥�����σT�[�r�X','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'�y�㕥�����σ}�C�y�[�W�z�p�X���[�h�ύX������܂���','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','�����㕥�����σ}�C�y�[�W�����p��������
�܂��Ƃɂ��肪�Ƃ��������܂��B

�}�C�y�[�W�̃p�X���[�h�ύX������܂����̂ł��񍐂������܂��B

���LURL�փA�N�Z�X���A���O�C�����s���Ă��������B


https://atobarai.seino.co.jp/seino-financial/mypage



�������[���ɂ��S������̂Ȃ����́A
�������܂������L���[���A�h���X�܂ł��A�������肢�������܂��B
�܂��A���T�[�r�X�Ɋւ��邻�̑��̂��₢���킹��
���L�A�h���X�ɂď����Ă���܂��B

����Ƃ��A���T�[�r�X����낵�����肢�������܂��B


--------------------------------------------------------------

�y�㕥�����σT�[�r�X�z
  ���⍇����F03-6908-7888
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: sfc-atobarai@seino.co.jp

  �^�c��ЁF�Z�C�m�[�t�B�i���V�����������
�@�Z���F��503-8501 �򕌌���_�s�c�����P�Ԓn

--------------------------------------------------------------
',3,'2015/12/24 11:41:38',32,'2015/12/24 11:41:38',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (491,61,'�Г��^�M�ۗ����[��','�㕥�����σT�[�r�X','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'�y�㕥�����σ}�C�y�[�W�z�p�X���[�h�ύX������܂���','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','{EnterpriseNameKj} �l

�����y�㕥�����σT�[�r�X�z�������p���������܂��āA
�܂��Ƃɂ��肪�Ƃ��������܂��B

�{���^�M�����������܂���

{OrderId} {CustomerNameKj}�l�ł���

{PendingReason}�B

{PendingDate}�܂ŗ^�M�ۗ��Ƃ����Ă��������܂��̂�
���萔�ł͂������܂����A�������������m�F��������
�Ǘ��T�C�g��ł��ύX�̏���������������
���Ђ܂ł��A�������������܂��悤���肢�������܂��B


�����������������������@�������C���������ۂ̒��Ӂ@����������������������

�C�����e�������͂�����������A�u���̓��e�œo�^�v���N���b�N�����
���e�̊m�F��ʂɑJ�ڂ��܂��B���e�����m�F�̂����A������x
�u���̓��e�œo�^�v���N���b�N����ƏC���������ƂȂ�܂��B
�i���m�F��ʂ���ʂ̃y�[�W�Ɉڂ��Ă��܂�����
���Ă��܂����肷��ƁA�C�������f����܂���B�j

������������������������������������������������������������������������

�y�Ǘ���ʂt�q�k�z
https://atobarai.seino.co.jp/seino-financial/member/

���s���ȓ_�Ȃǂ������܂�����A���C�y�ɂ��₢���킹���������܂��B

������낵�����肢�������܂��B


--------------------------------------------------------------

�Z�C�m�[�t�B�i���V����������Ё@�㕥�����σT�[�r�X�S��

TEL:03-6908-7888 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
Mail: sfc-atobarai@seino.co.jp

--------------------------------------------------------------
',3,'2015/12/24 11:47:40',32,'2015/12/24 11:47:40',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (492,89,'�O�~���������񍐃��[��','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,'daisuke-koie@scroll.co.jp','�y{ServiceName}�z�O�~���������񍐃��[��','{ServiceName}','�ȉ��̒����͐����z���O�~�ɂȂ�܂��B

------------------------------
{body}',null,'2016/02/23 14:00:00',1,'2022/04/20 5:39:24',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (493,90,'�l�b�gDE��惁�[���iPC�j','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z���ԋ��̂��A��','{ServiceName}','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

{CustomerNameKj}�l

���̓x��{SiteNameKj}�ŏ��i���w���̍ۂɁA
�y{ServiceName}�z�������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂����B

{ReceiptDate}��{ReceiptClass}��育�������m�F�������܂������A
�������x�������������Ă���܂����̂�
���ԋ������Ă��������������A�������グ�܂����B

�ԋ��̕��@�̂��ē����A�����җl���Z�����Ƀn�K�L�ɂĂ����肵�܂��B
���ʗX�ւł̔����ƂȂ�܂��̂ŁA���q�l�̂��茳�ɓ͂��܂�
��T�Ԓ��x������ꍇ���������܂��B
��T�Ԃقǂ��҂����������Ă��͂��Ȃ��ꍇ�́A
��ς��萔�ł͂������܂����A���̃��[���̖����ɋL�ڂ��Ă���܂�
�y{ServiceName}�z�J�X�^�}�[�Z���^�[�܂ł���񂭂������܂��B
�Ȃ��A���ԋ��̍ۂ̎萔��330�~�͂��q�l���S�ɂȂ�|�A 
����������܂��悤���肢�������܂��B 


�y���������e�z
������ID�F{OrderId}
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
���������z�F{UseAmount}



�s���_�Ȃǂ������܂�����A���C�y�ɂ��⍇�����������܂��B

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',null,'2016/02/23 14:00:00',1,'2022/04/20 6:04:54',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (494,91,'�l�b�gDE��惁�[���iCEL�j','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z���ԋ��̂��A��','{ServiceName}','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

{CustomerNameKj}�l

���̓x��{SiteNameKj}�ŏ��i���w���̍ۂɁA
�y{ServiceName}�z�������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂����B

{ReceiptDate}��{ReceiptClass}��育�������m�F�������܂������A
�������x�������������Ă���܂����̂�
���ԋ������Ă��������������A�������グ�܂����B

�ԋ��̕��@�̂��ē����A�����җl���Z�����Ƀn�K�L�ɂĂ����肵�܂��B
���ʗX�ւł̔����ƂȂ�܂��̂ŁA���q�l�̂��茳�ɓ͂��܂�
��T�Ԓ��x������ꍇ���������܂��B
��T�Ԃقǂ��҂����������Ă��͂��Ȃ��ꍇ�́A
��ς��萔�ł͂������܂����A���̃��[���̖����ɋL�ڂ��Ă���܂�
�y{ServiceName}�z�J�X�^�}�[�Z���^�[�܂ł���񂭂������܂��B
�Ȃ��A���ԋ��̍ۂ̎萔��330�~�͂��q�l���S�ɂȂ�|�A 
����������܂��悤���肢�������܂��B 


�y���������e�z
������ID�F{OrderId}
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
���������z�F{UseAmount}



�s���_�Ȃǂ������܂�����A���C�y�ɂ��⍇�����������܂��B

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',null,'2016/02/23 14:00:00',1,'2022/04/20 6:05:02',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (495,92,'�}�C�y�[�W�p�X���[�h�Ĕ��s���[���iPC�j','�㕥���h�b�g�R�� / �͂��Ă��略��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQiAvIBskQkZPJCQkRiQrJGlKJyQkGyhC?=','customer@ato-barai.com',null,null,null,'�y�㕥���h�b�g�R�� / �͂��Ă��略���z�p�X���[�h�Ĕ��s�̂��ē�','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44OgIC8g5bGK44GE44Gm44GL44KJ5omV?=
 =?UTF-8?B?44GE?=','���̓x�́y�㕥���h�b�g�R�� / �͂��Ă��略���z�������p��������
�܂��Ƃɂ��肪�Ƃ��������܂��B

���L��URL���N���b�N���āy�㕥���h�b�g�R�� / �͂��Ă��略���z�ł�
�p�X���[�h�Đݒ��i�߂Ă��������B

{MypagePasswordResetUrl}


�������ӎ�����
�E�{���[�������󂯎���A�Q�S���Ԉȓ��Ƀp�X���[�h�Đݒ��
�������Ă��������܂��悤���肢�������܂��B
�E�Q�S���Ԉȓ��Ƀp�X���[�h�Đݒ肪��������Ȃ��ꍇ�͂��葱����������
�Ȃ�܂��̂ł��炩���߂������肢�܂��B
�E�Q�S���Ԃ��߂��Ă��܂����ꍇ�́A�������܂����ēx�Ĕ��s�̂��葱����
���肢�������܂��B


------------------------------------
�Đݒ�̎菇�ɂ���
------------------------------------

�P.��LURL�ɃA�N�Z�X���A��ʂɂ��������ĕK�v�����������͂��������B

�Q.�����͓��e�����m�F�̂����A�V�����p�X���[�h��o�^���Ă��������B

�ȏ�Ńp�X���[�h�Đݒ芮���ƂȂ�܂��B


------------------------------------
�Đݒ肪���܂������Ȃ��ꍇ�́A
��ϋ������܂���customer@ato-barai.com�܂�
���₢���킹�����肢�������܂��B

���c�Ǝ��ԊO�̂��₢���킹�ɂ��܂��Ă�
���ԐM�ɂ����Ԃ����������ꍇ���������܂��B


���̓x�͂����p���肪�Ƃ��������܂����B


-----------------------------------------------------------
�y�㕥���h�b�g�R�� / �͂��Ă��略���z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�Fcustomer@ato-barai.com
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',0,'2017/03/02 16:00:00',1,'2022/07/14 11:29:45',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (496,93,'�}�C�y�[�W�p�X���[�h�Ĕ��s���[���iCEL�j','�㕥���h�b�g�R�� / �͂��Ă��略��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQiAvIBskQkZPJCQkRiQrJGlKJyQkGyhC?=','customer@ato-barai.com',null,null,null,'�y�㕥���h�b�g�R�� / �͂��Ă��略���z�p�X���[�h�Ĕ��s�̂��ē�','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44OgIC8g5bGK44GE44Gm44GL44KJ5omV?=
 =?UTF-8?B?44GE?=','���̓x�́y�㕥���h�b�g�R�� / �͂��Ă��略���z�������p��������
�܂��Ƃɂ��肪�Ƃ��������܂��B

���L��URL���N���b�N���āy�㕥���h�b�g�R�� / �͂��Ă��略���z�ł�
�p�X���[�h�Đݒ��i�߂Ă��������B

{MypagePasswordResetUrl}


�������ӎ�����
�E�{���[�������󂯎���A�Q�S���Ԉȓ��Ƀp�X���[�h�Đݒ��
�������Ă��������܂��悤���肢�������܂��B
�E�Q�S���Ԉȓ��Ƀp�X���[�h�Đݒ肪��������Ȃ��ꍇ�͂��葱����������
�Ȃ�܂��̂ł��炩���߂������肢�܂��B
�E�Q�S���Ԃ��߂��Ă��܂����ꍇ�́A�������܂����ēx�Ĕ��s�̂��葱����
���肢�������܂��B


------------------------------------
�Đݒ�̎菇�ɂ���
------------------------------------

�P.��LURL�ɃA�N�Z�X���A��ʂɂ��������ĕK�v�����������͂��������B

�Q.�����͓��e�����m�F�̂����A�V�����p�X���[�h��o�^���Ă��������B

�ȏ�Ńp�X���[�h�Đݒ芮���ƂȂ�܂��B


------------------------------------
�Đݒ肪���܂������Ȃ��ꍇ�́A
��ϋ������܂���customer@ato-barai.com�܂�
���₢���킹�����肢�������܂��B

���c�Ǝ��ԊO�̂��₢���킹�ɂ��܂��Ă�
���ԐM�ɂ����Ԃ����������ꍇ���������܂��B


���̓x�͂����p���肪�Ƃ��������܂����B


-----------------------------------------------------------
�y�㕥���h�b�g�R�� / �͂��Ă��略���z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�Fcustomer@ato-barai.com
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',0,'2017/03/02 16:00:00',1,'2022/07/14 11:29:59',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (497,92,'�}�C�y�[�W�p�X���[�h�Ĕ��s���[���iPC�j','�㕥�����σT�[�r�X','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','customer2@ato-barai.com',null,null,null,'�y�㕥�����σ}�C�y�[�W�z�p�X���[�h�Ĕ��s�̂��ē�','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','���̓x�́y�㕥�����σ}�C�y�[�W�z�������p��������
�܂��Ƃɂ��肪�Ƃ��������܂��B

���L��URL���N���b�N���āy�㕥�����σ}�C�y�[�W�z�ł�
�p�X���[�h�Đݒ��i�߂Ă��������B

{MypagePasswordResetUrl}


�������ӎ�����
�E�{���[�������󂯎���A�Q�S���Ԉȓ��Ƀp�X���[�h�Đݒ��
�������Ă��������܂��悤���肢�������܂��B
�E�Q�S���Ԉȓ��Ƀp�X���[�h�Đݒ肪��������Ȃ��ꍇ�͂��葱����������
�Ȃ�܂��̂ł��炩���߂������肢�܂��B
�E�Q�S���Ԃ��߂��Ă��܂����ꍇ�́A�������܂����ēx�Ĕ��s�̂��葱����
���肢�������܂��B


------------------------------------
�Đݒ�̎菇�ɂ���
------------------------------------

�P.��LURL�ɃA�N�Z�X���A��ʂɂ��������ĕK�v�����������͂��������B

�Q.�����͓��e�����m�F�̂����A�V�����p�X���[�h��o�^���Ă��������B

�ȏ�Ńp�X���[�h�Đݒ芮���ƂȂ�܂��B


------------------------------------
�Đݒ肪���܂������Ȃ��ꍇ�́A
��ϋ������܂���sfc-atobarai@seino.co.jp�܂�
���₢���킹�����肢�������܂��B

���c�Ǝ��ԊO�̂��₢���킹�ɂ��܂��Ă�
���ԐM�ɂ����Ԃ����������ꍇ���������܂��B


���̓x�͂����p���肪�Ƃ��������܂����B


--------------------------------------------------------------

�y�㕥�����σT�[�r�X�z
  ���⍇����F03-5909-4500
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: sfc-atobarai@seino.co.jp

  �^�c��ЁF�Z�C�m�[�t�B�i���V�����������
�@�Z���F��503-8501 �򕌌���_�s�c�����P�Ԓn

--------------------------------------------------------------',3,'2017/03/06 10:50:00',1,'2017/03/06 10:57:58',83,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (498,93,'�}�C�y�[�W�p�X���[�h�Ĕ��s���[���iCEL�j','�㕥�����σT�[�r�X','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','customer2@ato-barai.com',null,null,null,'�y�㕥�����σ}�C�y�[�W�z�p�X���[�h�Ĕ��s�̂��ē�','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','���̓x�́y�㕥�����σ}�C�y�[�W�z�������p��������
�܂��Ƃɂ��肪�Ƃ��������܂��B

���L��URL���N���b�N���āy�㕥�����σ}�C�y�[�W�z�ł�
�p�X���[�h�Đݒ��i�߂Ă��������B

{MypagePasswordResetUrl}


�������ӎ�����
�E�{���[�������󂯎���A�Q�S���Ԉȓ��Ƀp�X���[�h�Đݒ��
�������Ă��������܂��悤���肢�������܂��B
�E�Q�S���Ԉȓ��Ƀp�X���[�h�Đݒ肪��������Ȃ��ꍇ�͂��葱����������
�Ȃ�܂��̂ł��炩���߂������肢�܂��B
�E�Q�S���Ԃ��߂��Ă��܂����ꍇ�́A�������܂����ēx�Ĕ��s�̂��葱����
���肢�������܂��B


------------------------------------
�Đݒ�̎菇�ɂ���
------------------------------------

�P.��LURL�ɃA�N�Z�X���A��ʂɂ��������ĕK�v�����������͂��������B

�Q.�����͓��e�����m�F�̂����A�V�����p�X���[�h��o�^���Ă��������B

�ȏ�Ńp�X���[�h�Đݒ芮���ƂȂ�܂��B


------------------------------------
�Đݒ肪���܂������Ȃ��ꍇ�́A
��ϋ������܂���sfc-atobarai@seino.co.jp�܂�
���₢���킹�����肢�������܂��B

���c�Ǝ��ԊO�̂��₢���킹�ɂ��܂��Ă�
���ԐM�ɂ����Ԃ����������ꍇ���������܂��B


���̓x�͂����p���肪�Ƃ��������܂����B


--------------------------------------------------------------

�y�㕥�����σT�[�r�X�z
  ���⍇����F03-5909-4500
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: sfc-atobarai@seino.co.jp

  �^�c��ЁF�Z�C�m�[�t�B�i���V�����������
�@�Z���F��503-8501 �򕌌���_�s�c�����P�Ԓn

--------------------------------------------------------------',3,'2017/03/06 10:50:00',1,'2017/03/06 10:58:20',83,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (499,94,'�ԈႢ�`�[�C���˗����[��','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z�`�[�ԍ��̂��m�F�����肢�������܂� ','{ServiceName}','{EnterpriseNameKj}�l 

�����y{ServiceName}�z�������p���������A�@
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ReceiptOrderDate}�ɂ������o�^�����������܂����A���L���q�l�ł������܂����A
���݁A���׊m�F����ꂸ�A���x�����������߂��Ă������̊m�F��
�ł��Ă��Ȃ����߁A���ւ��ł��Ă��Ȃ��󋵂ł������܂��̂�
���m�F�����������������A�����������܂����B

�Ώۂ̂��q�l�̃f�[�^��Y�t�������܂��̂ŁA���e�����m�F���������A
2�T�Ԉȓ��ɂ��ύX�܂��͂��A�������������܂��悤���肢�������܂��B

���������ɂ��ύX�܂��͂��A���������������A
�z����Ђ̒ǐՃT�[�r�X�ɂĒ��ׂ̊m�F�����Ȃ��Ȃ����ꍇ�A
���ۏ؈����ƂȂ�A�������ԋp�������Ă��������܂��̂�
�����ӊ肢�܂��B
 
���ׂ̊m�F�����Ă��Ȃ������Ƃ������܂��ẮA
���L�̂����ꂩ�ɊY������\�����������܂��B
�E�z����ЁA�z���`�[�ԍ��̊ԈႢ
�E���q�l�ɏ��i���͂��Ă��Ȃ�
�E�L�����Z���\���R��

���萔���������������܂���
�l���̌��ˍ������������܂��̂�
���i�̔z����ЁA�z���`�[�ԍ��A���тɔz���󋵂�
��x�X�ܗl���ł��m�F���������A
�X�ܗl�Ǘ��T�C�g�ォ��C�����������܂��悤���肢�������܂��B

�����ID �F{OrderId} 

�� �ڍ׏��ɂ��ẮA�Y�t�̃t�@�C��(�𓀌��CSV�`��)�����Q�Ƃ��������B
�� �Y�t�t�@�C���́A�l���ی�̊ϓ_����p�X���[�h��ݒ肵�Ă���܂��B
�� �Y�t�t�@�C���̃p�X���[�h�͕ʂ̃��[���ɂĂ��m�点�������܂��B
�� �Y�t�t�@�C���́A�u�ꊇ�����L�����Z���iCSV�j�v�ɂāA�L�����Z���\���p��CSV�Ƃ��āA���̂܂܂����p���������܂��B

�܂��A��̏����������܂��ꍇ�ɂ͓Y�t�����������
���ЂɂĊm�F�������܂��B
 
���ҏW���@�͗��������������̂��q�l���i�荞��ł��������A
�w�o�^���e�̏C���x����C�������肢���܂��B
���܂��A�ԑ��E���ϕ��@�ύX�̏ꍇ�ŃL�����Z���\���R��̏ꍇ��
�X�ܗl�Ǘ��T�C�g�ォ��\�������肢�������܂��B

���s���ȓ_�Ȃǂ������܂�����A���C�y�ɂ��₢���킹���������܂��B 

����Ƃ���낵�����肢�������܂��B 


-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',0,'2017/03/15 17:00:00',1,'2022/04/20 6:05:51',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (500,95,'�𓀃p�X���[�h�ʒm���[��','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z �𓀃p�X���[�h�ʒm���[��','{ServiceName}','{EnterpriseNameKj}�l 

�����y{ServiceName}�z�������p���������A�@
�܂��Ƃɂ��肪�Ƃ��������܂��B

��قǁA�u�y{ServiceName}�z�`�[�ԍ��̂��m�F�����肢�������܂� �v�̌����ŁA
�����肵�����[���ɓY�t���ꂽ�t�@�C���̊J���p�X���[�h�����m�点�������܂��B

�Y�t�t�@�C����: {FileName} 
�𓀃p�X���[�h: {Password} 

���s���ȓ_�Ȃǂ������܂�����A���C�y�ɂ��₢���킹���������܂��B 

����Ƃ���낵�����肢�������܂��B 

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',0,'2017/03/15 17:00:00',1,'2022/04/20 6:06:04',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (501,96,'CB�������ۏؕύX�ʒm���[��','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,'daisuke-koie@scroll.co.jp','���ۏؕύX�ʒm���[���i{LoginId} {OrderId}�j','{ServiceName}','�ȉ��̒��������ۏ؂ɕύX����܂����B
�����X�F{LoginId} {EnterpriseName}
����ID�F{OrderId}
NG���R�F{NgReason}
',0,'2019/05/29 21:13:36',1,'2022/04/20 6:07:05',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (502,97,'�����X�������ۏؕύX�ʒm���[��','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y {ServiceName} �z���ۏ؏�����t','{ServiceName}','{EnterpriseNameKj}
���S���җl

������ς����b�ɂȂ��Ă���܂��B
�y{ServiceName}�z�J�X�^�}�[�Z���^�[�ł������܂��B

���ϊǗ��V�X�e���́u���ۏ؂ɕύX�v�{�^���ɂĂ��\�����݂����������܂���
{OrderId} {CustomerNameKj}�l�̒����𖳕ۏ؂ɂĎ�t�������܂����B

���m�F���������A�s����s���_�Ȃǂ������܂�����
���C�y�ɂ��⍇�����������܂��B

����Ƃ������A��낵�����肢�������܂��B


-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',0,'2019/05/29 21:13:37',1,'2022/04/20 2:31:39',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (503,98,'�����f�[�^���M�o�b�`�G���[���[��','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,'daisuke-koie@scroll.co.jp','�y{ServiceName}�z�����f�[�^���M�o�b�`�G���[���[��','{ServiceName}','������ЃG�N�V�[�h�@���S���җl

������ЃL���b�`�{�[����V�X�e���̃G���[���[���ł��B

������ЃL���b�`�{�[����V�X�e�����A���m���Ɗ�����Ј��̐����f�[�^���M�����s���܂����B

�I�y���[�V�����ɂ��f�[�^���M�����肢�v���܂��B
',0,'2020/04/13 21:24:08',1,'2022/04/20 5:41:41',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (505,53,'�}�C�y�[�W���o�^�������[���iPC�j','�}�C�y�[�W���o�^','=?UTF-8?B?GyRCJV4lJCVaITwlODI+RVBPPxsoQg==?=','customer2@ato-barai.com',null,null,null,'������o�^�̂��ē�','=?UTF-8?B?44Oe44Kk44Oa44O844K45Luu55m76Yyy?=','���L��URL���N���b�N���Č㕥���h�b�g�R���ł�
����o�^��i�߂Ă��������B

{MypageRegistUrl}
',1,'2019/12/02 16:52:13',83,'2019/12/02 16:52:13',83,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (506,100,'���UWEB�\���݈ē����[���iPC�j','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z�����������Ƃ� WEB���\�����݂̂��ē��F{OrderId}','{ServiceName}','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������
 
{CustomerNameKj}�l�i{OrderId}�j


{SiteNameKj}�l�ł̌����������Ƃ��Ɋւ��邲�A���ł��B

�����������Ƃ��ł̌��ςɂ�����uWEB�\���݁v��I�����ꂽ���q�l�ցA
�ȉ��A���\�����ݎ菇�����ē��������܂��B


�y�P�z���L�ǂ��炩��URL���A�}�C�y�[�W�փ��O�C�����Ă��������B

�E�ȈՃ��O�C��URL
{OrderPageAccessUrl}

���O�C���ɂ͂��o�^�̂��d�b�ԍ��������p���������B
�ȈՃ��O�C��URL�̗L�����Ԃ�{LimitDate}���14���ԂƂȂ�܂��B

{LimitDate}���14���Ԃ��o�߂���Ă���ꍇ��
���L�A�ʏ탍�O�C��URL��胍�O�C�����������B


�E�ʏ탍�O�C��URL
{OrderPageUrl}

���O�C���ɂ͂��o�^�̂��d�b�ԍ��ƁA�������E������̕[��
�L�ڂ���Ă���p�X���[�h�������p���������B

���������E������̕[�����茳�ɂ������łȂ�
�@�p�X���[�h��������Ȃ��ꍇ�́A���̃��[���ւ��ԐM���������B


�y�Q�z�w�����U�ց@���̓o�^�ցx�̃����N�֐i�݁A
�\�������ē��ɏ]���A���葱�����������B


�ȏ�ł������܂��B

���葱������������܂ł́A�y{ServiceName}�z��肨���肷��
�������ł̂��x�����ƂȂ�܂��B



���s���ȓ_���������܂����炲�ԐM�ɂĂ��₢���킹���������B

��낵�����肢�������܂��B


-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',null,'2020/02/09 22:25:48',9,'2022/04/20 6:08:06',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (507,101,'���UWEB�\���݈ē����[���iCEL�j','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z�����������Ƃ� WEB���\�����݂̂��ē��F{OrderId}','{ServiceName}','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������
 
{CustomerNameKj}�l�i{OrderId}�j


{SiteNameKj}�l�ł̌����������Ƃ��Ɋւ��邲�A���ł��B

�����������Ƃ��ł̌��ςɂ�����uWEB�\���݁v��I�����ꂽ���q�l�ցA
�ȉ��A���\�����ݎ菇�����ē��������܂��B


�y�P�z���L�ǂ��炩��URL���A�}�C�y�[�W�փ��O�C�����Ă��������B

�E�ȈՃ��O�C��URL
{OrderPageAccessUrl}

���O�C���ɂ͂��o�^�̂��d�b�ԍ��������p���������B
�ȈՃ��O�C��URL�̗L�����Ԃ�{LimitDate}���14���ԂƂȂ�܂��B

{LimitDate}���14���Ԃ��o�߂���Ă���ꍇ��
���L�A�ʏ탍�O�C��URL��胍�O�C�����������B


�E�ʏ탍�O�C��URL
{OrderPageUrl}

���O�C���ɂ͂��o�^�̂��d�b�ԍ��ƁA�������E������̕[��
�L�ڂ���Ă���p�X���[�h�������p���������B

���������E������̕[�����茳�ɂ������łȂ�
�@�p�X���[�h��������Ȃ��ꍇ�́A���̃��[���ւ��ԐM���������B


�y�Q�z�w�����U�ց@���̓o�^�ցx�̃����N�֐i�݁A
�\�������ē��ɏ]���A���葱�����������B


�ȏ�ł������܂��B

���葱������������܂ł́A�y{ServiceName}�z��肨���肷��
�������ł̂��x�����ƂȂ�܂��B



���s���ȓ_���������܂����炲�ԐM�ɂĂ��₢���킹���������B

��낵�����肢�������܂��B


-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',null,'2020/02/09 22:25:48',9,'2022/04/20 6:08:15',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (508,100,'���UWEB�\���݈ē����[���iPC�j','������Ђd�X�g�A�[�i�㕥�������j','=?UTF-8?B?GyRCM3Q8MDJxPFIjRSU5JUglIiE8IUo4ZUonJCRBazh9IUsbKEI=?=','ato-barai.sp@estore.co.jp',null,null,null,'�y���A���z���U�������𔭍s���܂����@�i�n�K�L�œ͂��܂��j','=?UTF-8?B?5qCq5byP5Lya56S+77yl44K544OI44Ki44O877yI5b6M5omV44GE56qT5Y+j?=
 =?UTF-8?B?77yJ?=','��������������������������������������������������������������������������
�����肢�F���₢���킹�����ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������������

{CustomerNameKj}�l


����͂��������������܂��āA���ɂ��肪�Ƃ��������܂��B


���L�̃V���b�s���O�̂���������{�����s�������܂��̂ŁA������������A
�������ɋL�ڂ���Ă��邨�x���������܂łɂ��x�������������܂��悤�A
���肢�\���グ�܂��B


�y���������e�z

���x���ҁF{CustomerNameKj}�@�l

���w���X�ܖ��F{SiteNameKj}�@

���w�����F{OrderDate}

���x�����z�F{UseAmount}

���w�����i���ׁF���i���^���^�w���i�ڌv

{OrderItems}

���ώ萔��                              \{SettlementFee}

����                                    \{DeliveryFee}


���X�����̂Ȃǂɂ��A���������͂��Ȃ����Ƃ��������܂��B
��T�Ԃقǂ��҂����������Ă����������͂��Ȃ��ꍇ�ɂ́A��ς��萔�ł����A
���L�A����ւ���񂭂������܂��悤�A���肢�\���グ�܂��B


�����i�ɂ��܂��Ă��A���[���ւȂǂ̔z�����@�̏ꍇ�ɂ́A�z�����̂Ȃǂɂ��
�͂��Ȃ��ꍇ���������܂��B
������A���i���͂��Ă��Ȃ��ꍇ�ɂ͑�ς��萔�ł͂������܂����A���������ꂽ
�X�ܗl�܂Œ��ڂ��⍇�����������܂��B


�����������тɖ{���[�����A���i���������������Ă��܂����Ƃ��������܂����A
�������e�͉������܂��悤���肢�\���グ�܂��B
�܂��A���������ɖ��ׂ��܂܂�Ă���܂��̂ł��m�F���������܂��B


�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�{���ɓ���Ă��⍇�����������B


���L�����Z���i���\���j����Ă���ꍇ�ł��A�s���Ⴂ�ɂē����[����
�z�M����Ă��܂��ꍇ���������܂��B���̍ۂ͑�ς��萔�ł����A���L
�w���X�ܗl�ɒ��ڂ��⍇�����������B



�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F

�@���ڍw���X�ܗl�ɂ��₢���킹���������B
�@�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}



�����x�����Ɋւ��邨�₢���킹�́F

  ���⍇����F03-6908-5100
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: ato-barai.sp@estore.co.jp
  �^�c��ЁF������Ђd�X�g�A�[�@�㕥������ 
�@�Z���F��105-0003 �����s�`�搼�V��1-10-2�@�Z�F�������V���r��9F
',1,'2020/02/09 22:25:48',9,'2020/02/09 22:25:48',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (509,101,'���UWEB�\���݈ē����[���iCEL�j','������Ђd�X�g�A�[�i�㕥�������j','=?UTF-8?B?GyRCM3Q8MDJxPFIjRSU5JUglIiE8IUo4ZUonJCRBazh9IUsbKEI=?=','ato-barai.sp@estore.co.jp',null,null,null,'�y���A���z���U�������i�n�K�L�j�𔭍s���܂���','=?UTF-8?B?5qCq5byP5Lya56S+77yl44K544OI44Ki44O877yI5b6M5omV44GE56qT5Y+j?=
 =?UTF-8?B?77yJ?=','
�����肢�F���₢���킹�����ہA���L���[�����ʂ��c�����܂܂��ԐM����������


{CustomerNameKj}�l


����͂��������������܂��āA���ɂ��肪�Ƃ��������܂��B

���L�̃V���b�s���O�̂���������{�����s�������܂��̂ŁA������������A
�������ɋL�ڂ���Ă��邨�x���������܂łɂ��x�������������܂��悤�A
���肢�\���グ�܂��B

�y���������e�z
���x���ҁF{CustomerNameKj}�@�l
���w���X�ܖ��F{SiteNameKj}�@
���w�����F{OrderDate}
���x�����z�F{UseAmount}
���w�����i���ׁF���i���^���^�w���i�ڌv
{OrderItems}
���ώ萔��  \{SettlementFee}
���� \{DeliveryFee}

���X�����̂Ȃǂɂ��A���������͂��Ȃ����Ƃ��������܂��B
��T�Ԃقǂ��҂����������Ă����������͂��Ȃ��ꍇ�ɂ́A��ς��萔�ł����A
���L�A����ւ���񂭂������܂��悤�A���肢�\���グ�܂��B

�����i�ɂ��܂��Ă��A���[���ւȂǂ̔z�����@�̏ꍇ�ɂ́A�z�����̂Ȃǂɂ��
�͂��Ȃ��ꍇ���������܂��B
������A���i���͂��Ă��Ȃ��ꍇ�ɂ͑�ς��萔�ł͂������܂����A���������ꂽ
�X�ܗl�܂Œ��ڂ��⍇�����������܂��B

�����������тɖ{���[���A���i���������������Ă��܂����Ƃ��������܂����A
�������e�͉������܂��悤���肢�\���グ�܂��B
�܂��A���������ɖ��ׂ��܂܂�Ă���܂��̂ł��m�F���������܂��B

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�{���ɓ���Ă��⍇�����������B

���L�����Z���i���\���j����Ă���ꍇ�ł��A�s���Ⴂ�ɂē����[����
�z�M����Ă��܂��ꍇ���������܂��B���̍ۂ͑�ς��萔�ł����A���L
�w���X�ܗl�ɒ��ڂ��⍇�����������B

�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
���ڍw���X�ܗl�ɂ��₢���킹���������B
�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}

�����x�����Ɋւ��邨�₢���킹�́F
03-6908-5100
 �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 ato-barai.sp@estore.co.jp
 �^�c��ЁF������Ђd�X�g�A�[�@�㕥������
��105-0003 �����s�`�搼�V��1-10-2�@�Z�F�������V���r��9F',1,'2020/02/09 22:25:48',9,'2020/02/09 22:25:48',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (510,100,'���UWEB�\���݈ē����[���iPC�j','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�y�㕥��.com�zSMBC���U���������s�ē��@�i�n�K�L�œ͂��܂��j','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','��������������������������������������������������������������������
�����肢�F���₢���킹�����ہA���L���[�����ʂ��c�����܂܂��ԐM����������
�������������������������������������������������������������������� 

{CustomerNameKj}�l

����� {SiteNameKj}�l�ł̂���������
�y�㕥���h�b�g�R���z�������p���������܂��āA
�܂��Ƃɂ��肪�Ƃ��������܂��B

���L�̃V���b�s���O�̂���������{�����s�������܂��̂ŁA������������A
�������ɋL�ڂ���Ă��邨�x���������܂łɂ��x�������������܂��悤�A
���肢�\���グ�܂��B

{OrderPageAccessUrl}

�y���������e�z
���x���ҁF{CustomerNameKj}�@�l
���w���X�ܖ��F{SiteNameKj}�@
���w�����F{OrderDate}
���x�����z�F{UseAmount}
���w�����i���ׁF���i���^���^�w���i�ڌv
{OrderItems}
���ώ萔��                              \{SettlementFee}
����                                    \{DeliveryFee}

���X�����̂Ȃǂɂ��A���������͂��Ȃ����Ƃ��������܂��B
�@��T�Ԃقǂ��҂����������Ă����������͂��Ȃ��ꍇ�ɂ́A��ς��萔�ł����A
�@���L�A����ւ���񂭂������܂��悤�A���肢�\���グ�܂��B

�����i�ɂ��܂��Ă��A���[���ւȂǂ̔z�����@�̏ꍇ�ɂ́A�z�����̂Ȃǂɂ��
�@�͂��Ȃ��ꍇ���������܂��B
�@������A���i���͂��Ă��Ȃ��ꍇ�ɂ͑�ς��萔�ł͂������܂����A���������ꂽ
�@�X�ܗl�܂Œ��ڂ��⍇�����������܂��B

�����������тɖ{���[�����A���i���������������Ă��܂����Ƃ��������܂����A
�������e�͉������܂��悤���肢�\���グ�܂��B
�܂��A���������ɖ��ׂ��܂܂�Ă���܂��̂ł��m�F���������܂��B

���L�����Z���i���\���j����Ă���ꍇ�ł��A�s���Ⴂ�ɂē����[����
�@�z�M����Ă��܂��ꍇ���������܂��B���̍ۂ͑�ς��萔�ł����A���L
�@�w���X�ܗl�ɒ��ڂ��⍇�����������B

���������̑����s���ȓ_�͉��L�t�q�k�����m�F���������B������

�@�@�@  http://www.ato-barai.com/guidance/faq.html

�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
���ڍw���X�ܗl�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}

�����x�����Ɋւ��邨�₢���킹�́F
������ЃL���b�`�{�[���@�㕥���h�b�g�R�����ƕ�
TEL:03-5332-3490(�����y��9:00�`18:00)
Mail: customer@ato-barai.com

--------------------------------------------------------------

�y�㕥���h�b�g�R���z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`

  ���⍇����F03-5332-3490
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com

  �^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F

--------------------------------------------------------------',2,'2020/02/09 22:25:48',9,'2020/07/12 10:58:05',17202,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (511,101,'���UWEB�\���݈ē����[���iCEL�j','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�y�㕥��.com�z���U���������s�ē��@�i�n�K�L�œ͂��܂��j','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','��������������������������������������������������������������������
�����肢�F���₢���킹�����ہA���L���[�����ʂ��c�����܂܂��ԐM����������
�������������������������������������������������������������������� 

{CustomerNameKj}�l

����� {SiteNameKj}�l�ł̂���������
�y�㕥���h�b�g�R���z�������p���������܂��āA
�܂��Ƃɂ��肪�Ƃ��������܂��B

���L�̃V���b�s���O�̂���������{�����s�������܂��̂ŁA������������A
�������ɋL�ڂ���Ă��邨�x���������܂łɂ��x�������������܂��悤�A
���肢�\���グ�܂��B

�y���������e�z
���x���ҁF{CustomerNameKj}�@�l
���w���X�ܖ��F{SiteNameKj}�@
���w�����F{OrderDate}
���x�����z�F{UseAmount}
���w�����i���ׁF���i���^���^�w���i�ڌv
{OrderItems}
���ώ萔��  \{SettlementFee}
���� \{DeliveryFee}

���X�����̂Ȃǂɂ��A���������͂��Ȃ����Ƃ��������܂��B
�@��T�Ԃقǂ��҂����������Ă����������͂��Ȃ��ꍇ�ɂ́A��ς��萔�ł����A
�@���L�A����ւ���񂭂������܂��悤�A���肢�\���グ�܂��B

�����i�ɂ��܂��Ă��A���[���ւȂǂ̔z�����@�̏ꍇ�ɂ́A�z�����̂Ȃǂɂ��
�@�͂��Ȃ��ꍇ���������܂��B
�@������A���i���͂��Ă��Ȃ��ꍇ�ɂ͑�ς��萔�ł͂������܂����A���������ꂽ
�@�X�ܗl�܂Œ��ڂ��⍇�����������܂��B

�����������тɖ{���[���A���i���������������Ă��܂����Ƃ��������܂����A
�������e�͉������܂��悤���肢�\���グ�܂��B
�܂��A���������ɖ��ׂ��܂܂�Ă���܂��̂ł��m�F���������܂��B

���L�����Z���i���\���j����Ă���ꍇ�ł��A�s���Ⴂ�ɂē����[����
�@�z�M����Ă��܂��ꍇ���������܂��B���̍ۂ͑�ς��萔�ł����A���L
�@�w���X�ܗl�ɒ��ڂ��⍇�����������B

���������̑����s���ȓ_�͉��L�t�q�k�����m�F���������B������

�@�@�@  http://www.ato-barai.com/guidance/faq.html


�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
���ڍw���X�ܗl�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@
�d�b�F{Phone}

�����x�����Ɋւ��邨�₢���킹�́F
������ЃL���b�`�{�[���@�㕥���h�b�g�R�����ƕ�
�Z���F��160-0023 �����s�V�h�搼�V�h7-8-2 �����r�� 4F
TEL:03-5332-3490(�����y��9:00�`18:00)
Mail: customer@ato-barai.com
URL: http://www.ato-barai.com�i�p�\�R����p�j

--------------------------------------------------------------

�y�㕥���h�b�g�R���z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`

  ���⍇����F03-5332-3490
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail:customer@ato-barai.com

  �^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h7-7-30 ���c�}���؃r�� 8F

--------------------------------------------------------------',2,'2020/02/09 22:25:48',9,'2020/02/09 22:25:48',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (512,100,'���UWEB�\���݈ē����[���iPC�j','�㕥�����σT�[�r�X','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'�y�㕥�����σT�[�r�X�z���U���������s�ē��@�i�n�K�L�œ͂��܂��j','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','��������������������������������������������������������������������������
�����肢�F���₢���킹�����ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������������

{CustomerNameKj}�l

����� {SiteNameKj}�l�ł̂���������
�y�㕥�����σT�[�r�X�z�������p���������܂��āA
�܂��Ƃɂ��肪�Ƃ��������܂��B

���L�̃V���b�s���O�̂���������{�����s�������܂��̂ŁA������������A
�������ɋL�ڂ���Ă��邨�x���������܂łɂ��x�������������܂��悤�A
���肢�\���グ�܂��B

{OrderPageAccessUrl}

�y���������e�z
���x���ҁF{CustomerNameKj}�@�l
���w���X�ܖ��F{SiteNameKj}�@
���w�����F{OrderDate}
���x�����z�F{UseAmount}
���w�����i���ׁF���i���^���^�w���i�ڌv
{OrderItems}
���ώ萔��                              \{SettlementFee}
����                                    \{DeliveryFee}

���X�����̂Ȃǂɂ��A���������͂��Ȃ����Ƃ��������܂��B
�@��T�Ԃقǂ��҂����������Ă����������͂��Ȃ��ꍇ�ɂ́A��ς��萔�ł����A
�@���L�A����ւ���񂭂������܂��悤�A���肢�\���グ�܂��B

�����i�ɂ��܂��Ă��A���[���ւȂǂ̔z�����@�̏ꍇ�ɂ́A�z�����̂Ȃǂɂ��
�@�͂��Ȃ��ꍇ���������܂��B
�@������A���i���͂��Ă��Ȃ��ꍇ�ɂ͑�ς��萔�ł͂������܂����A���������ꂽ
�@�X�ܗl�܂Œ��ڂ��⍇�����������܂��B

�����������тɖ{���[�����A���i���������������Ă��܂����Ƃ��������܂����A
�������e�͉������܂��悤���肢�\���グ�܂��B
�܂��A���������ɖ��ׂ��܂܂�Ă���܂��̂ł��m�F���������܂��B

�ڂ����͉��L�p�\�R���pURL�������������B

http://www.seino.co.jp/financial/atobarai/guidance/

���s���ȓ_�Ȃǂ������܂�����A���C�y�ɉ��L�܂ł��⍇���������B

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�@�{���ɓ���Ă��⍇�����������B

���L�����Z���i���\���j����Ă���ꍇ�ł��A�s���Ⴂ�ɂē����[����
�@�z�M����Ă��܂��ꍇ���������܂��B���̍ۂ͑�ς��萔�ł����A���L
�@�w���X�ܗl�ɒ��ڂ��⍇�����������B

�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
���ڍw���X�ܗl�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}

�����x�����Ɋւ��邨�₢���킹�́F
�Z�C�m�[�t�B�i���V����������Ё@�㕥�����σT�[�r�X�S��
TEL:03-6908-7888 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
Mail: sfc-atobarai@seino.co.jp

--------------------------------------------------------------

�y�㕥�����σT�[�r�X�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`

  ���⍇����F03-6908-7888
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: sfc-atobarai@seino.co.jp

  �^�c��ЁF�Z�C�m�[�t�B�i���V�����������
�@�Z���F��503-8501 �򕌌���_�s�c�����P�Ԓn

--------------------------------------------------------------',3,'2020/02/09 22:25:49',9,'2020/02/09 22:25:49',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (513,101,'���UWEB�\���݈ē����[���iCEL�j','�㕥�����σT�[�r�X','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'�y�㕥�����σT�[�r�X�z���U���������s�ē��@�i�n�K�L�œ͂��܂��j','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','
�����肢�F���₢���킹�����ہA���L���[�����ʂ��c�����܂܂��ԐM����������


{CustomerNameKj}�l

����� {SiteNameKj}�l�ł̂���������
�y�㕥�����σT�[�r�X�z�������p���������܂��āA
�܂��Ƃɂ��肪�Ƃ��������܂��B

���L�̃V���b�s���O�̂���������{�����s�������܂��̂ŁA������������A
�������ɋL�ڂ���Ă��邨�x���������܂łɂ��x�������������܂��悤�A
���肢�\���グ�܂��B

�y���������e�z
���x���ҁF{CustomerNameKj}�@�l
���w���X�ܖ��F{SiteNameKj}�@
���w�����F{OrderDate}
���x�����z�F{UseAmount}
���w�����i���ׁF���i���^���^�w���i�ڌv
{OrderItems}
���ώ萔��  \{SettlementFee}
���� \{DeliveryFee}

���X�����̂Ȃǂɂ��A���������͂��Ȃ����Ƃ��������܂��B
�@��T�Ԃقǂ��҂����������Ă����������͂��Ȃ��ꍇ�ɂ́A��ς��萔�ł����A
�@���L�A����ւ���񂭂������܂��悤�A���肢�\���グ�܂��B

�����i�ɂ��܂��Ă��A���[���ւȂǂ̔z�����@�̏ꍇ�ɂ́A�z�����̂Ȃǂɂ��
�@�͂��Ȃ��ꍇ���������܂��B
�@������A���i���͂��Ă��Ȃ��ꍇ�ɂ͑�ς��萔�ł͂������܂����A���������ꂽ
�@�X�ܗl�܂Œ��ڂ��⍇�����������܂��B

�����������тɖ{���[���A���i���������������Ă��܂����Ƃ��������܂����A
�������e�͉������܂��悤���肢�\���グ�܂��B
�܂��A���������ɖ��ׂ��܂܂�Ă���܂��̂ł��m�F���������܂��B

�ڂ����͉��L�p�\�R���pURL�������������B

http://www.seino.co.jp/financial/atobarai/guidance/

���s���ȓ_�Ȃǂ������܂�����A���C�y�ɉ��L�܂ł��⍇���������B

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�@�{���ɓ���Ă��⍇�����������B

���L�����Z���i���\���j����Ă���ꍇ�ł��A�s���Ⴂ�ɂē����[����
�@�z�M����Ă��܂��ꍇ���������܂��B���̍ۂ͑�ς��萔�ł����A���L
�@�w���X�ܗl�ɒ��ڂ��⍇�����������B

�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
���ڍw���X�ܗl�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@
�d�b�F{Phone}

�����x�����Ɋւ��邨�₢���킹�́F
�Z�C�m�[�t�B�i���V����������Ё@�㕥�����σT�[�r�X�S��
�Z���F��503-8501 �򕌌���_�s�c�����P�Ԓn
TEL:03-6908-7888 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
Mail: sfc-atobarai@seino.co.jp
URL: http://www.seino.co.jp/financial�i�p�\�R����p�j

--------------------------------------------------------------

�y�㕥�����σT�[�r�X�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`

  ���⍇����F03-6908-7888
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: sfc-atobarai@seino.co.jp

  �^�c��ЁF�Z�C�m�[�t�B�i���V�����������
�@�Z���F��503-8501 �򕌌���_�s�c�����P�Ԓn

--------------------------------------------------------------',3,'2020/02/09 22:25:49',9,'2020/02/09 22:25:49',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (514,100,'���UWEB�\���݈ē����[���iPC�j','�yBASE�㕥�����ρz','=?UTF-8?B?GyRCIVobKEJCQVNFGyRCOGVKJyQkN2g6USFbGyhC?=','customer2@ato-barai.com',null,null,null,'�yBASE�㕥�����ρz���U���������s�ē��@�i�n�K�L�œ͂��܂��j','=?UTF-8?B?44CQQkFTReW+jOaJleOBhOaxuua4iOOAkQ==?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
�������������������������������������������������������������������� 

{CustomerNameKj}�l

����� {SiteNameKj}�l�ł������������Ē���
�܂��Ƃɂ��肪�Ƃ��������܂��B

���L�̃V���b�s���O�̂���������{�����s�������܂��̂ŁA������������A
�������ɋL�ڂ���Ă��邨�x���������܂łɂ��x�������������܂��悤�A
���肢�\���グ�܂��B

�y���������e�z

���x���ҁF{CustomerNameKj}�@�l

���w���X�ܖ��F{SiteNameKj}

���w�����F{OrderDate}

���x�����z�F{UseAmount}

���w�����i���ׁF���i���^���^�w���i�ڌv

{OrderItems}

���ώ萔��                              \{SettlementFee}

����                                    \{DeliveryFee}

���X�����̂Ȃǂɂ��A���������͂��Ȃ����Ƃ��������܂��B
�@��T�Ԃقǂ��҂����������Ă����������͂��Ȃ��ꍇ�ɂ́A��ς��萔�ł����A
�@���L�A����ւ���񂭂������܂��悤�A���肢�\���グ�܂��B

�����i�ɂ��܂��Ă��A���[���ւȂǂ̔z�����@�̏ꍇ�ɂ́A�z�����̂Ȃǂɂ��
�@�͂��Ȃ��ꍇ���������܂��B
�@������A���i���͂��Ă��Ȃ��ꍇ�ɂ͑�ς��萔�ł͂������܂����A���������ꂽ
�@�X�ܗl�܂Œ��ڂ��⍇�����������܂��B

�����������тɖ{���[�����A���i���������������Ă��܂����Ƃ��������܂����A
�@�������e�͉������܂��悤���肢�\���グ�܂��B
�@�܂��A���������ɖ��ׂ��܂܂�Ă���܂��̂ł��m�F���������܂��B


�ڂ����͉��LURL�������������B
http://thebase.in/pages/help.html#category14_146

���s���ȓ_�Ȃǂ������܂�����A���C�y�ɉ��L�܂ł��⍇���������B

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�@�{���ɓ���Ă��⍇�����������B

���L�����Z���i���\���j����Ă���ꍇ�ł��A�s���Ⴂ�ɂē����[����
�@�z�M����Ă��܂��ꍇ���������܂��B���̍ۂ͑�ς��萔�ł����A���L
�@�w���X�ܗl�ɒ��ڂ��⍇�����������B


�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F

���ڍw���X�ܗl�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@
�d�b�F{Phone}

�����i��������7���ȍ~�̃L�����Z���͂ł��܂���̂ł����ӂ��������B
�X�܂Ƃ̓��ӂ̏�L�����Z���ɂ�菤�i��ԕi�����ꍇ�͂��̎|�A
���L���[���A�h���X�܂Œ������e�̂��A�������肢���܂��B

support@thebase.in

�L�����Z�����s�Ȃ�Ȃ���
���i����x������
�������͂������܂��̂ł����ӂ��������B

{SiteNameKj} �� BASE ( https://thebase.in ) �ō쐬����Ă��܂��B 
BASE�͒N�ł��ȒP�ɖ����Ńl�b�g�V���b�v���J�݂ł���T�[�r�X�ł��B

�����x�����Ɋւ��邨�₢���킹�́F

BASE �㕥�����ρ@����
TEL:[03-6279-1149](�����y��9:00�`18:00)
Mail: atobarai@thebase.in


����������������������������������������������������������������������

BASE (�x�C�X)
https://thebase.in

 ���⍇����:[03-6279-1149]
 �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: atobarai@thebase.in

����������������������������������������������������������������������',4,'2020/02/09 22:25:49',9,'2020/02/09 22:25:49',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (515,101,'���UWEB�\���݈ē����[���iCEL�j','�yBASE�㕥�����ρz','=?UTF-8?B?GyRCIVobKEJCQVNFGyRCOGVKJyQkN2g6USFbGyhC?=','customer2@ato-barai.com',null,null,null,'�yBASE�㕥�����ρz���U���������s�ē��@�i�n�K�L�œ͂��܂��j','=?UTF-8?B?44CQQkFTReW+jOaJleOBhOaxuua4iOOAkQ==?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
�������������������������������������������������������������������� 

{CustomerNameKj}�l

����� {SiteNameKj}�l�ł������������Ē���
�܂��Ƃɂ��肪�Ƃ��������܂��B

���L�̃V���b�s���O�̂���������{�����s�������܂��̂ŁA������������A
�������ɋL�ڂ���Ă��邨�x���������܂łɂ��x�������������܂��悤�A
���肢�\���グ�܂��B

�y���������e�z

���x���ҁF{CustomerNameKj}�@�l

���w���X�ܖ��F{SiteNameKj}

���w�����F{OrderDate}

���x�����z�F{UseAmount}

���w�����i���ׁF���i���^���^�w���i�ڌv

{OrderItems}

���ώ萔��                              \{SettlementFee}

����                                    \{DeliveryFee}

���X�����̂Ȃǂɂ��A���������͂��Ȃ����Ƃ��������܂��B
�@��T�Ԃقǂ��҂����������Ă����������͂��Ȃ��ꍇ�ɂ́A��ς��萔�ł����A
�@���L�A����ւ���񂭂������܂��悤�A���肢�\���グ�܂��B

�����i�ɂ��܂��Ă��A���[���ւȂǂ̔z�����@�̏ꍇ�ɂ́A�z�����̂Ȃǂɂ��
�@�͂��Ȃ��ꍇ���������܂��B
�@������A���i���͂��Ă��Ȃ��ꍇ�ɂ͑�ς��萔�ł͂������܂����A���������ꂽ
�@�X�ܗl�܂Œ��ڂ��⍇�����������܂��B

�����������тɖ{���[�����A���i���������������Ă��܂����Ƃ��������܂����A
�@�������e�͉������܂��悤���肢�\���グ�܂��B
�@�܂��A���������ɖ��ׂ��܂܂�Ă���܂��̂ł��m�F���������܂��B


�ڂ����͉��LURL�������������B
http://thebase.in/pages/help.html#category14_146

���s���ȓ_�Ȃǂ������܂�����A���C�y�ɉ��L�܂ł��⍇���������B

�����[���ɂĂ��⍇�������������ꍇ�́A�K�����������̂����O�i�t���l�[���j��
�@�{���ɓ���Ă��⍇�����������B

���L�����Z���i���\���j����Ă���ꍇ�ł��A�s���Ⴂ�ɂē����[����
�@�z�M����Ă��܂��ꍇ���������܂��B���̍ۂ͑�ς��萔�ł����A���L
�@�w���X�ܗl�ɒ��ڂ��⍇�����������B


�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F

���ڍw���X�ܗl�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@
�d�b�F{Phone}

�����i��������7���ȍ~�̃L�����Z���͂ł��܂���̂ł����ӂ��������B
�X�܂Ƃ̓��ӂ̏�L�����Z���ɂ�菤�i��ԕi�����ꍇ�͂��̎|�A
���L���[���A�h���X�܂Œ������e�̂��A�������肢���܂��B

support@thebase.in

�L�����Z�����s�Ȃ�Ȃ���
���i����x������
�������͂������܂��̂ł����ӂ��������B

{SiteNameKj} �� BASE ( https://thebase.in ) �ō쐬����Ă��܂��B 
BASE�͒N�ł��ȒP�ɖ����Ńl�b�g�V���b�v���J�݂ł���T�[�r�X�ł��B

�����x�����Ɋւ��邨�₢���킹�́F

BASE �㕥�����ρ@����
TEL:[03-6279-1149](�����y��9:00�`18:00)
Mail: atobarai@thebase.in


����������������������������������������������������������������������

BASE (�x�C�X)
https://thebase.in

 ���⍇����:[03-6279-1149]
 �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: atobarai@thebase.in

����������������������������������������������������������������������
',4,'2020/02/09 22:25:49',9,'2020/02/09 22:25:49',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (516,102,'���ԋ��Č����[���iPC�j','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z���ԋ��̂��A��','{ServiceName}','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������
{CustomerNameKj}�l

���̓x��{SiteNameKj}�ŏ��i���w���̍ۂɁA
�y{ServiceName}�z�������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂����B

{ReceiptDate}��{ReceiptClass}��育���������������܂������A
�ߏ�ł��x�������������Ă���܂����̂�
�ȑO�A�����җl�̏Z�����Ɂu�ԋ��̂��ē��v�̃n�K�L��
�����肵�Ă���܂��B

�{�����݁A�܂����ԋ��̎葱�����������Ă��Ȃ��󋵂ł������܂��B
�葱���������܂��Ă���܂��̂ŁA���m�F�������܂��悤���肢���܂��B

�y���������e�z
������ID�F{OrderId}
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
���������z�F{UseAmount}

�Ȃ��A���茳�Ɂu�ԋ��̂��ē��v�̃n�K�L���Ȃ��ꍇ�ɂ�
���ЂɂĂ��葱�����������܂��̂ŁA
���L�����L���̂����A�����[���ւ��ԐM���������B �@

�E��s���F 
�E�x�X���F 
�E������ځF 
�E�����ԍ��F 
�E�������`(�J�i)�F 

�s���_�Ȃǂ������܂�����A���C�y�ɂ��⍇�����������B

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',null,'2016/02/23 14:00:00',1,'2022/04/20 6:08:34',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (517,103,'���ԋ��Č����[���iCEL�j','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z���ԋ��̂��A��','{ServiceName}','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������
{CustomerNameKj}�l

���̓x��{SiteNameKj}�ŏ��i���w���̍ۂɁA
�y{ServiceName}�z�������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂����B

{ReceiptDate}��{ReceiptClass}��育���������������܂������A
�ߏ�ł��x�������������Ă���܂����̂�
�ȑO�A�����җl�̏Z�����Ɂu�ԋ��̂��ē��v�̃n�K�L��
�����肵�Ă���܂��B

�{�����݁A�܂����ԋ��̎葱�����������Ă��Ȃ��󋵂ł������܂��B
�葱���������܂��Ă���܂��̂ŁA���m�F�������܂��悤���肢���܂��B

�y���������e�z
������ID�F{OrderId}
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
���������z�F{UseAmount}

�Ȃ��A���茳�Ɂu�ԋ��̂��ē��v�̃n�K�L���Ȃ��ꍇ�ɂ�
���ЂɂĂ��葱�����������܂��̂ŁA
���L�����L���̂����A�����[���ւ��ԐM���������B �@

�E��s���F 
�E�x�X���F 
�E������ځF 
�E�����ԍ��F 
�E�������`(�J�i)�F 

�s���_�Ȃǂ������܂�����A���C�y�ɂ��⍇�����������B

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',null,'2016/02/23 14:00:00',1,'2022/04/20 6:08:46',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (520,104,'�͂��Ă��猈�ϐ��������s���[���iPC�j','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z�����p�ɂ��Ă̂��ē��@ �`���D���Ȃ��x�������@�����I�т��������܂��`�@�������ԍ��F{OrderId}','{ServiceName}','��������������������������������������������������������������������                               
�����肢�F���₢���킹�����ہA�K�����L���[�����ʂ��c�����܂܂��ԐM����������                               
�������������������������������������������������������������������� 
                             
�������[���́A�y{ServiceName}�z�������p���������܂������q�l�ցA                               
���������̂��͂��ɂ��Ă��ē������Ă����������[���ł��B                               
                               
                                
 {CustomerNameKj}�l                               
                               
����� {SiteNameKj}�l�ł̂���������                               
�y{ServiceName}�z�������p���������܂��āA                               
�܂��Ƃɂ��肪�Ƃ��������܂��B                               
                               
���������𔭍s�������܂����B
�����܂ō����΂炭���҂����������܂��悤���肢�������܂��B                              
                               
�������[���́u���i�����̂��m�点���[���v�ł͂������܂���B                               
���i�̔����E�����\����ɂ��Ă͂��w���X�l�ւ̂��₢���킹�����肢�\���グ�܂��B                               
                               
                               
�y���������e�z                               
�������ԍ��F{OrderId}                               
���w���X�ܖ��F{SiteNameKj}�@                               
���w�����F{OrderDate}                               
���x�����z�F{UseAmount}                               
���w�����i���ׁF���i���^���^�w���i�ڌv                               
{OrderItems}                               
���ώ萔��                              {SettlementFee}�~                               
����                                    {DeliveryFee}�~                               
                               
�� {PaymentMethod}���ς�����]�������͉��LURL�ɂāy {CreditLimitDate} �z�܂ł�
�@���葱�������肢�������܂��B
�@�ȈՃ��O�C���y�[�W�@{OrderPageAccessUrl}  
 
���������̓�����҂�����LURL���炨�x�������������܂��B
�@�������͕K���X������܂��̂ŁA���x�����ς̏ꍇ�́A
�@���萔�ł����͂����������͔j�������肢���܂��B
                               
���L�����Z���i���\���j����Ă���ꍇ�ł��A�s���Ⴂ�ɂē����[����                               
�@�z�M����Ă��܂��ꍇ���������܂��B���̍ۂ͑�ς��萔�ł����A���L                               
�@�w���X�ܗl�ɒ��ڂ��⍇�����������B                               
                               
���������̑����s���ȓ_�͉��L�t�q�k�����m�F���������B������                               
�@�@�@�@https://atobarai-user.jp/                               
                               
�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F                               
���ڍw���X�ܗl�ɂ��₢���킹�������B                               
�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}                               
                               
�����x�����Ɋւ��邨�₢���킹�́F                               
������ЃL���b�`�{�[���@�y{ServiceName}�z  
TEL:03-4326-3600 (�����y��9:00�`18:00)                               
Mail: {ServiceMail}                              
                               
-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',null,'2024/01/10 0:54:17',1,'2022/06/08 18:41:19',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (521,105,'�͂��Ă��猈�ϐ��������s���[���iCEL�j','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z�����p�ɂ��Ă̂��ē��@ �`���D���Ȃ��x�������@�����I�т��������܂��`�@�������ԍ��F{OrderId}','{ServiceName}','��������������������������������������������������������������������                               
�����肢�F���₢���킹�����ہA�K�����L���[�����ʂ��c�����܂܂��ԐM����������                               
�������������������������������������������������������������������� 
                             
�������[���́A�y{ServiceName}�z�������p���������܂������q�l�ցA                               
���������̂��͂��ɂ��Ă��ē������Ă����������[���ł��B                               
                               
                                
 {CustomerNameKj}�l                               
                               
����� {SiteNameKj}�l�ł̂���������                               
�y{ServiceName}�z�������p���������܂��āA                               
�܂��Ƃɂ��肪�Ƃ��������܂��B                               
                               
���������𔭍s�������܂����B
�����܂ō����΂炭���҂����������܂��悤���肢�������܂��B                              
                               
�������[���́u���i�����̂��m�点���[���v�ł͂������܂���B                               
���i�̔����E�����\����ɂ��Ă͂��w���X�l�ւ̂��₢���킹�����肢�\���グ�܂��B                               
                               
                               
�y���������e�z                               
�������ԍ��F{OrderId}                               
���w���X�ܖ��F{SiteNameKj}�@                               
���w�����F{OrderDate}                               
���x�����z�F{UseAmount}                               
���w�����i���ׁF���i���^���^�w���i�ڌv                               
{OrderItems}                               
���ώ萔��                              {SettlementFee}�~                               
����                                    {DeliveryFee}�~                               
                               
�� {PaymentMethod}���ς�����]�������͉��LURL�ɂāy {CreditLimitDate} �z�܂ł�
�@���葱�������肢�������܂��B
�@�ȈՃ��O�C���y�[�W�@{OrderPageAccessUrl}  
 
���������̓�����҂�����LURL���炨�x�������������܂��B
�@�������͕K���X������܂��̂ŁA���x�����ς̏ꍇ�́A
�@���萔�ł����͂����������͔j�������肢���܂��B                 
                                                 
���L�����Z���i���\���j����Ă���ꍇ�ł��A�s���Ⴂ�ɂē����[����                               
�@�z�M����Ă��܂��ꍇ���������܂��B���̍ۂ͑�ς��萔�ł����A���L                               
�@�w���X�ܗl�ɒ��ڂ��⍇�����������B                               
                               
���������̑����s���ȓ_�͉��L�t�q�k�����m�F���������B������                               
�@�@�@�@https://atobarai-user.jp/                               
                               
�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F                               
���ڍw���X�ܗl�ɂ��₢���킹�������B                               
�w���X�܁F{SiteNameKj}�@�d�b�F{Phone}                               
                               
�����x�����Ɋւ��邨�₢���킹�́F                               
������ЃL���b�`�{�[���@�y{ServiceName}�z  
TEL:03-4326-3600 (�����y��9:00�`18:00)                               
Mail: {ServiceMail}                              
                               
-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',null,'2024/01/10 0:54:17',1,'2022/06/08 18:41:27',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (522,108,'�͂��Ă��猈�ϐ��������s���[��(����)�iPC�j','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z�����p�ɂ��Ă̂��ē��@ �`���D���Ȃ��x�������@�����I�т��������܂��`�@�������ԍ��F{OrderId}','{ServiceName}','�������������������������������������������������������������������� 
�����肢�F���₢���킹�����ہA�K�����L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

�������[���́A�y{ServiceName}�z�������p���������܂������q�l�ւ����肵�Ă���܂��B
�u���i�����̂��m�点���[���v�ł͂������܂���B
���i�̔����E�����\����ɂ��Ă͂��w���X�l�ւ̂��₢���킹�����肢�\���グ�܂��B

                                
 {CustomerNameKj}�l

����� {SiteNameKj}�l�ł̂���������
�y{ServiceName}�z�������p���������܂��āA
�܂��Ƃɂ��肪�Ƃ��������܂��B

������������������������������������������������������������������������

���������i�����p���j�͏��i�ƈꏏ�ɂ��͂��������܂��̂ŁA
���i������A�������ɋL�ڂ̂��x�����������܂łɂ��x�������������܂��悤�A
���肢�\���グ�܂��B 

������������������������������������������������������������������������ 


�y���������e�z
�������ԍ��F{OrderId}
���w���X�ܖ��F{SiteNameKj}
���w�����F{OrderDate}
���x�����z�F{UseAmount} 
���w�����i���ׁF���i���^���^�w���i�ڌv 
{OrderItems} 
���ώ萔��                              {SettlementFee}�~   
����                                    {DeliveryFee}�~ 

�� {PaymentMethod}���ς�����]�������͉��LURL�ɂāy {CreditLimitDate} �z�܂ł�
�@���葱�������肢�������܂��B
�@�ȈՃ��O�C���y�[�W�@{OrderPageAccessUrl}   

���������̓�����҂�����LURL���炨�x�������������܂��B
  �������͕K�����i�ɓ�������Ă���܂��̂ŁA���x�����ς̏ꍇ�́A
  ���萔�ł����͂����������͔j�������肢���܂��B       

�����i�Ƌ��ɐ������������Ă��Ȃ��ꍇ�ɂ́A��ς��萔�ł����A
�@�y{ServiceName}�z�J�X�^�}�[�Z���^�[�ւ���񂭂������܂��悤�A���肢�\���グ�܂��B
 
���L�����Z���i���\���j����Ă���ꍇ�ł��A�s���Ⴂ�ɂē����[����
�@�z�M����Ă��܂��ꍇ���������܂��B���̍ۂ͑�ς��萔�ł����A���L
�@�w���X�ܗl�ɒ��ڂ��⍇�����������B
 
���������̑����s���ȓ_�͉��L�t�q�k�����m�F���������B������
�@�@�@�@https://atobarai-user.jp/

�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
���ڍw���X�ܗl�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@�d�b�F{Phone} 

�����x�����Ɋւ��邨�₢���킹�́F 
������ЃL���b�`�{�[���@�y{ServiceName}�z
TEL:03-4326-3600 (�����y��9:00�`18:00) 
Mail: {ServiceMail} 

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',null,'2025/08/01 11:22:35',1,'2022/06/08 18:41:34',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (523,109,'�͂��Ă��猈�ϐ��������s���[��(����)�iCEL�j','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z�����p�ɂ��Ă̂��ē��@ �`���D���Ȃ��x�������@�����I�т��������܂��`�@�������ԍ��F{OrderId}','{ServiceName}','�������������������������������������������������������������������� 
�����肢�F���₢���킹�����ہA�K�����L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

�������[���́A�y{ServiceName}�z�������p���������܂������q�l�ւ����肵�Ă���܂��B
�u���i�����̂��m�点���[���v�ł͂������܂���B
���i�̔����E�����\����ɂ��Ă͂��w���X�l�ւ̂��₢���킹�����肢�\���グ�܂��B

                                
 {CustomerNameKj}�l

����� {SiteNameKj}�l�ł̂���������
�y{ServiceName}�z�������p���������܂��āA
�܂��Ƃɂ��肪�Ƃ��������܂��B

������������������������������������������������������������������������

���������i�����p���j�͏��i�ƈꏏ�ɂ��͂��������܂��̂ŁA
���i������A�������ɋL�ڂ̂��x�����������܂łɂ��x�������������܂��悤�A
���肢�\���グ�܂��B 

������������������������������������������������������������������������ 


�y���������e�z
�������ԍ��F{OrderId}
���w���X�ܖ��F{SiteNameKj}
���w�����F{OrderDate}
���x�����z�F{UseAmount} 
���w�����i���ׁF���i���^���^�w���i�ڌv 
{OrderItems} 
���ώ萔��                              {SettlementFee}�~   
����                                    {DeliveryFee}�~ 

�� {PaymentMethod}���ς�����]�������͉��LURL�ɂāy {CreditLimitDate} �z�܂ł�
�@���葱�������肢�������܂��B
�@�ȈՃ��O�C���y�[�W�@{OrderPageAccessUrl}   

���������̓�����҂�����LURL���炨�x�������������܂��B
  �������͕K�����i�ɓ�������Ă���܂��̂ŁA���x�����ς̏ꍇ�́A
  ���萔�ł����͂����������͔j�������肢���܂��B        

�����i�Ƌ��ɐ������������Ă��Ȃ��ꍇ�ɂ́A��ς��萔�ł����A
�@�y{ServiceName}�z�J�X�^�}�[�Z���^�[�ւ���񂭂������܂��悤�A���肢�\���グ�܂��B
 
���L�����Z���i���\���j����Ă���ꍇ�ł��A�s���Ⴂ�ɂē����[����
�@�z�M����Ă��܂��ꍇ���������܂��B���̍ۂ͑�ς��萔�ł����A���L
�@�w���X�ܗl�ɒ��ڂ��⍇�����������B
 
���������̑����s���ȓ_�͉��L�t�q�k�����m�F���������B������
�@�@�@�@https://atobarai-user.jp/

�����i�E�ԕi�E�z���Ɋւ��邨�₢���킹�́F
���ڍw���X�ܗl�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@�d�b�F{Phone} 

�����x�����Ɋւ��邨�₢���킹�́F 
������ЃL���b�`�{�[���@�y{ServiceName}�z
TEL:03-4326-3600 (�����y��9:00�`18:00) 
Mail: {ServiceMail} 

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',null,'2025/08/01 11:22:35',1,'2022/06/08 18:41:42',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (524,1,'���Ǝғo�^�����i�T�[�r�X�J�n�j���[��','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer@ato-barai.com',null,null,null,'�y�㕥��.com�z �X�ܐR���ʉ߂̂��m�点','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{EnterpriseNameKj}�@�l

���̓x�͕��ЃT�[�r�X�A�y�㕥���h�b�g�R���z�ɂ��\�����������A
�܂��Ƃɂ��肪�Ƃ��������܂��B

�R���̌��ʁA�ʉ߂ƂȂ�܂����̂ŁA�㕥�����ϊǗ��V�X�e����
�����p���������̂ɕK�v��ID��񍐐\���グ�܂��B

�d�v�Ȃ��ē��ƂȂ�܂��̂ŁA�Ō�܂ł��ǂ݂��������B

�y�Ǘ��T�C�g�t�q�k�z
https://www.atobarai.jp/member/




ID : {LoginId}




���p�X���[�h�͕ʓr���[���ɂĂ����肳���Ă��������܂��B
���T�C�g�h�c�͏�L�h�c�Ƃ͈قȂ�܂��̂ł����ӂ��������B
�T�C�g�h�c�̎Q�ƕ��@�͈ȉ��̒ʂ�ł��B

�y1�z�Ǘ��T�C�g�Ƀ��O�C��
�@�@���@���@���@��
�y2�z�u�o�^���Ǘ��v���N���b�N
�@�@���@���@���@��
�y3�z�u�T�C�g���v���N���b�N
�@�@���@���@���@��
�y4�z�u�T�C�g�h�c�v���ɕ\������܂��B

 ���}�j���A���̃_�E�����[�h�i�K�{�j
���L��URL���A�y�㕥���h�b�g�R���z�̉^�p�}�j���A�����_�E�����[�h
���Ă��g�p�������B
�T�[�r�X�J�n�ɕK�v�ȃ}�j���A���ƂȂ��Ă���܂��̂ŁA�K�����m�F
���������܂��悤���肢�\���グ�܂��B

 https://www.atobarai.jp/doc/help/Atobarai.com_Manual.pdf

���{���ɂ�Adobe PDF Reader ���K�v�ł��B�C���X�g�[������Ă��Ȃ�
���́A���L��URL��蓯�\�t�g�̃C���X�g�[�������肢�������܂��B

  http://www.adobe.com/jp/products/acrobat/readstep2.html

�Ǘ��V�X�e���̂����p���@�́A�_�E�����[�h���Ă����������}�j���A��
�����m�F���������B

�T�[�r�X�̊J�n�܂ŁA�X�ܗl�ɂ͈ȉ��̂悤�ȍ�Ƃ����Ă��������܂��B
�J�n�̂��A�������Y��Ȃ��悤�A���肢�\���グ�܂��B

�������@STEP 1�@�������o�^���e�̂��m�F

�Ǘ��T�C�g�Ƀ��O�C���A�X�܏����m�F�i�v�������̑��̏��j

�������@STEP 2�@��������^���͂̃T�C�g�f��

�}�j���A���ɂ��������āA�X�ܗl�T�C�g��ɓ����ϕ��@�p�̒�^���͂��f��
�i���菤����@�y�[�W�⌈�ϑI����ʂȂǁj

�T�C�g�f�ڗp��^���E�摜�񋟃y�[�W�F

http://www.ato-barai.com/for_shops/tokuteishou.html

�����̎��_�ŃT�[�r�X�J�n�ƂȂ�܂�

������җl�����㕥���h�b�g�R�����恕�̑��o�i�[�_�E�����[�h�y�[�W
http://www.ato-barai.com/download/

����җl��������́A���߂ē����ς������p�ɂȂ����җl�ɂƂ���
������Ղ��Ȃ�A���⍇�������点����ʂ����҂ł��܂��B
����ɔ̑��o�i�[�́A�㕥�����ς��o���邨�X�Ƃ��ăA�s�[���ł��邽�߁A
�̑��̌��ʂɂ��Ȃ���܂��̂ŁA������������Ă����p���������B

�������@STEP 3�@�������T�[�r�X�J�n�̓��Ђւ̂��ʒm

�T�[�r�X���J�n�����|���A���Ђ܂Ń��[���������͂��d�b�ɂĂ��A���������B
 mail: customer@ato-barai.com
 tel:  0120-667-690

�������@STEP 4�@���������Ђ����ω�ʂ��m�F

���ВS�������ω�ʂ��m�F�����Ă��������A��肪�Ȃ���΂��̂܂܉^�c�A
��肪����ΏC���̂��肢�������Ă����������Ƃ��������܂��B

  �������u����v�͂����܂�

������җl�ւ̐������̂��ē��p���̃_�E�����[�h�i�C�Ӂj
���L�̂t�q�k��萿�����̂��ē��p�����_�E�����[�h���āA���i�ɓ���
���Ă��������B
�i���ē��p���̓����͓X�ܗl�̂����f�ɂ��C�ӂōs���Ă���������
�@����܂����A���߂ē����ς������p�Ȃ����җl�ɂƂ��Ă͕�����Ղ�
�@�Ȃ�A���⍇�������邱�Ƃɂ��q����܂��̂ŁA�������Ă�����������
�@�𐄏����Ă���܂��B�j

https://www.atobarai.jp/doc/download/doukonnyou.xls


�T�[�r�X�J�n�ɓ������āA�܂��A�^�c�Ɋւ��邨�₢���킹���́A
���[�������̂��A����ɂ��C�y�ɂ��⍇���������B

�������������������������y���ӎ����z������������������������

�P�j�ȉ��ɊY�����邲�����́A�ۏؑΏۊO�ƂȂ��Ă��܂��܂��̂�
�@�@�����ӂ��������B

���ۏ؊O�Ƃ́A�������̕ۏ؂��t�����A����җl����̓�����
�@�Ȃ�����͓X�ܗl�֓��������Ă������������ł��܂���B
�@
�EWeb��ɂĂ��ו��̒ǐՂ��ł��Ȃ��z�����@���g��ꂽ������
�E�`�[�o�^���ɔz����Ђ�`�[�ԍ�����������œo�^���ꂽ������
�E�z�B�󋵂��L�����Z���E�����߂蓙�ɂ��z�B�����̊m�F��
�@�Ƃ�Ȃ�������
�E���ۂɔ������ꂽ�z�����@�Ɋւ�炸�A�`�[�o�^���̔z�����@��
�@�y���[���ցz��I�����ēo�^���ꂽ������
�E�����������邲����

�Q�j�z���`�[�ԍ������o�^�����������A�������́A���c�Ɠ���
�@�@�������җl�ɑ΂��āA������������������܂��B
�����i�����O�ɔz���`�[�ԍ������o�^���������܂��ƁA�����������i
�@����ɓ͂��Ă��܂��\���������Ȃ�܂��̂ŁA���i�������
�@�z���`�[�ԍ��̂��o�^�����肢�������܂��B

�R�j�����܂łɕ��Б��ŏ��i�̒��׊m�F���Ƃꂽ����������
�@�@���Y�������̗��֑ΏۂƂȂ�܂��B
���`�[�ԍ��o�^����A�z�B�������ł͂Ȃ��A���Б��Œ��׊m�F��
�@�Ƃꂽ�����x�[�X�ƂȂ�܂��̂ł����ӂ��������B

�S�j�V���ɃE�F�u�T�C�g�܂��̓J�^���O���Ō㕥���h�b�g�R���̃T�[�r�X��
    �����p���������ꍇ�A�������͐V���ȏ��i��̔�����ꍇ�A
    �V���ȃT�[�r�X�����񋟂����ꍇ�͎��O�ɂ��A�����������܂��悤
    ���肢�\�������܂��B 
�����R���̂��̂́A�㕥���h�b�g�R���̃T�[�r�X�͂����p���������܂���B

������������������������������������������������������������


����Ƃ����i�����t�������̒��A�X�������肢�\���グ�܂��B

������ЃL���b�`�{�[���@�㕥���h�b�g�R�����ƕ��@�X�^�b�t�ꓯ

--------------------------------------------------------------

�y�㕥���h�b�g�R���z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`

  ���⍇����F0120-667-690
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com

  �^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h6-14-1�@�V�h�O���[���^���[14�K 

--------------------------------------------------------------',6,'2026/04/09 12:59:17',83,'2026/04/09 12:59:49',83,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (525,1,'���Ǝғo�^�����i�T�[�r�X�J�n�j���[��','','','customer@ato-barai.com',null,null,null,'','','',5,'2020/11/05 16:34:54',83,'2020/11/05 16:34:54',83,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (526,39,'�x���������߃��[���i�ĂP�j�iPC�j','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer@ato-barai.com',null,null,null,'�ĂP�y�㕥���h�b�g�R���z{OrderDate}{SiteNameKj}�ł̂��������̌�({OrderId})','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɂ������̂��葱�������ς̂悤�ł����
�@�����[���ւ̕ԐM�͂��s�v�ł������܂��B

{CustomerNameKj}�l

���̓x��{SiteNameKj}�ŏ��i���w���̍ۂɁA
�㕥���h�b�g�R���������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂��B

���x�����������߂��Ă��������̊m�F�����Ă���܂���ł����̂�
�{���A�Đ������𔭍s�E�����������܂����B
���茳�ɓ͂�����A�����܂łɂ��x���������肢�������܂��B

�y���������e�z
�������ԍ��F{OrderId}
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
���������z�F{TotalAmount}

�����x���������߂��Ă��܂��܂��ƁA
�Đ����萔�������Z����܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ł������܂�)
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z
{Bk_BankName}�@{Bk_BranchName}
���ʌ����@{Bk_AccountNumber}
{Bk_AccountHolderKn}

�y�X�֐U�֌����z
�����L���F00120�]7
�����ԍ��F670031
�J�j�L���b�`�{�[��

���̑��A���x���Ɋւ��Ă��s���ȓ_�͉��L�t�q�k�����m�F���������B
http://www.ato-barai.com/guidance/faq.html

�����i�̕ԕi�E�����ȂǏ��i�Ɋւ��邨�₢���킹�́F
���ڍw���X�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@
�d�b�F{Phone}

--------------------------------------------------------------
�y�㕥���h�b�g�R���z

  ���⍇����F03-5332-3490
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com
  
�@�^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h6-14-1�@�V�h�O���[���^���[14�K 
--------------------------------------------------------------
',6,'2021/08/16 18:39:52',83,'2022/05/08 13:51:24',83,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (527,110,'�^�M���s�҂������ʒm','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y��Q�z30���ȏ�^�M���s�҂��ɂȂ��Ă��钍��������܂�','{ServiceName}','30���ȏ�^�M���s�҂��ɂȂ��Ă��钍��������܂��B
�X���b�h�̈ꎞ�I�ȕύX�����肢���܂��B
�Ώے����͈ȉ�
{OrderList}',null,'2021/03/03 16:17:23',1,'2022/04/07 16:36:50',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (528,100,'���UWEB�\���݈ē����[���iPC�j','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�y�㕥���h�b�g�R���z�����������Ƃ� WEB���\�����݂̂��ē��F{OrderId}','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
�������������������������������������������������������������������� 

{CustomerNameKj}�l�i{OrderId}�j


{SiteNameKj}�l�ł̌����������Ƃ��Ɋւ��邲�A���ł��B

�����������Ƃ��ł̌��ςɂ�����uWEB�\���݁v��I�����ꂽ���q�l�ցA
�ȉ��A���\�����ݎ菇�����ē��������܂��B


�y�P�z���L�ǂ��炩��URL���A�}�C�y�[�W�փ��O�C�����Ă��������B

�E�ȈՃ��O�C��URL
{OrderPageAccessUrl}

���O�C���ɂ͂��������̂��d�b�ԍ��������p���������B
�ȈՃ��O�C��URL�̗L�����Ԃ�{LimitDate}���14���ԂƂȂ�܂��B

{LimitDate}���14���Ԃ��o�߂���Ă���ꍇ��
���L�A�ʏ탍�O�C��URL��胍�O�C�����������B


�E�ʏ탍�O�C��URL
https://www.atobarai.jp/orderpage

���O�C���ɂ͂��������̂��d�b�ԍ��ƁA�������E������̕[��
�L�ڂ���Ă���p�X���[�h�������p���������B

���������E������̕[�����茳�ɂ������łȂ�
�@�p�X���[�h��������Ȃ��ꍇ�́A���̃��[���ւ��ԐM���������B


�y�Q�z�w�����U�ց@���̓o�^�ցx�̃����N�֐i�݁A
�\�������ē��ɏ]���A���葱�����������B


�ȏ�ł������܂��B

���葱������������܂ł́A�㕥���h�b�g�R����肨���肷��
�������ł̂��x�����ƂȂ�܂��B



���s���ȓ_���������܂����炲�ԐM�ɂĂ��₢���킹���������B

��낵�����肢�������܂��B


--------------------------------------------------------------
�y�㕥���h�b�g�R���z

  ���⍇����F03-4326-3600
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com
  
�@�^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��140-0002�@
�@�@�@�@�����s�i��擌�i��2-2-24�@�V���F�Z���g�����^���[ 12F
--------------------------------------------------------------',6,'2021/10/05 19:40:26',18051,'2021/10/05 19:40:26',18051,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (529,93,'�}�C�y�[�W�p�X���[�h�Ĕ��s���[���iCEL�j','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�y�㕥���h�b�g�R���z�����������Ƃ� WEB���\�����݂̂��ē��F{OrderId}','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
�������������������������������������������������������������������� 

{CustomerNameKj}�l�i{OrderId}�j


{SiteNameKj}�l�ł̌����������Ƃ��Ɋւ��邲�A���ł��B

�����������Ƃ��ł̌��ςɂ�����uWEB�\���݁v��I�����ꂽ���q�l�ցA
�ȉ��A���\�����ݎ菇�����ē��������܂��B


�y�P�z���L�ǂ��炩��URL���A�}�C�y�[�W�փ��O�C�����Ă��������B

�E�ȈՃ��O�C��URL
{OrderPageAccessUrl}

���O�C���ɂ͂��������̂��d�b�ԍ��������p���������B
�ȈՃ��O�C��URL�̗L�����Ԃ�{LimitDate}���14���ԂƂȂ�܂��B

{LimitDate}���14���Ԃ��o�߂���Ă���ꍇ��
���L�A�ʏ탍�O�C��URL��胍�O�C�����������B


�E�ʏ탍�O�C��URL
https://www.atobarai.jp/orderpage

���O�C���ɂ͂��������̂��d�b�ԍ��ƁA�������E������̕[��
�L�ڂ���Ă���p�X���[�h�������p���������B

���������E������̕[�����茳�ɂ������łȂ�
�@�p�X���[�h��������Ȃ��ꍇ�́A���̃��[���ւ��ԐM���������B


�y�Q�z�w�����U�ց@���̓o�^�ցx�̃����N�֐i�݁A
�\�������ē��ɏ]���A���葱�����������B


�ȏ�ł������܂��B

���葱������������܂ł́A�㕥���h�b�g�R����肨���肷��
�������ł̂��x�����ƂȂ�܂��B



���s���ȓ_���������܂����炲�ԐM�ɂĂ��₢���킹���������B

��낵�����肢�������܂��B


--------------------------------------------------------------
�y�㕥���h�b�g�R���z

  ���⍇����F03-4326-3600
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com
  
�@�^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��140-0002�@
�@�@�@�@�����s�i��擌�i��2-2-24�@�V���F�Z���g�����^���[ 12F
--------------------------------------------------------------',6,'2021/10/05 19:40:54',18051,'2021/10/05 19:40:54',18051,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (540,111,'�����U�֐������[���i����j(PC)','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�����U�֐������[���i����j','{ServiceName}','�݂��كt�@�N�^�[�����X�̂ݗ��p�\�ׁ̈A�ݒ肵�Ȃ�',null,'2021/12/30 21:03:08',0,'2022/04/07 16:36:45',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (541,112,'�����U�֐������[���i����j(CEL)','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�����U�֐������[���i����j','{ServiceName}','�����U�֐������[���i����j',null,'2021/12/30 21:03:08',0,'2022/04/07 16:36:37',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (542,113,'�����U�֐������[���i�G���[�A���~�A�o�^�������j(PC)','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�����U�֐������[���i�G���[�A���~�A�o�^�������j','{ServiceName}','�݂��كt�@�N�^�[�����X�̂ݗ��p�\�ׁ̈A�ݒ肵�Ȃ�',null,'2021/12/30 21:03:08',0,'2022/04/07 16:36:29',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (543,114,'�����U�֐������[���i�G���[�A���~�A�o�^�������j(CEL)','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�����U�֐������[���i�G���[�A���~�A�o�^�������j','{ServiceName}','',null,'2021/12/30 21:03:08',0,'2022/04/07 16:36:22',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (544,115,'�������������U�փ��[��(PC)','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z�����U�ւ̂��ē�','{ServiceName}','{CustomerNameKj}�l


���̓x�́A{SiteNameKj}�ł̗��p�����̂��x�����ɁA
�y(��)�L���b�`�{�[���z���֕������U�ւ������p���������܂��āA
�܂��Ƃɂ��肪�Ƃ��������܂��B

���L�̂����p���e�ŏ����Ă��܂��̂ŁA�����U�ւ̐\��������������
�����ɁA�O���܂łɈ����ɕK�v�ȋ��z�����������������悤���肢�\���グ�܂��B

�������U�ֈ������F{CreditTransferDate} 
���x�����z�F{UseAmount}�@






�y�����p���e�z
���_��ҁF{CustomerNameKj}�@�l
�����p�X�ܖ��F{SiteNameKj}�@
�����p�\�����F{OrderDate}
�����p�̗������e�F{OneOrderItem}

���L�����Z���i���\���j����Ă���ꍇ�ł��A�s���Ⴂ�ɂē����[����
�@�z�M����Ă��܂��ꍇ���������܂��B
�@���̍ۂ͑�ς��萔�ł����A���L�����p�X�ܗl�ɒ��ڂ��⍇�����������B

�������̑����s���ȓ_�͉��L�t�q�k�����m�F���������B
�@�@�@  �@�@�@https://atobarai-user.jp/faq/

�������p�̗������e�F���i���Ɋւ��邨�₢���킹�́F
���ڂ����p�X�ܗl�ɂ��₢���킹�������B
�����p�X�܁F{SiteNameKj}�@�d�b�F{Phone}

�����x�����Ɋւ��邨�₢���킹�́F
������ЃL���b�`�{�[���@�y{ServiceName}�z
TEL:03-4326-3600(�����y��9:00�`18:00)
Mail: {ServiceMail}

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',null,'2021/12/30 21:03:08',0,'2022/06/08 18:42:42',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (545,116,'�������������U�փ��[��(CEL)','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z�����U�ւ̂��ē�','{ServiceName}','{CustomerNameKj}�l


���̓x�́A{SiteNameKj}�ł̗��p�����̂��x�����ɁA
�y(��)�L���b�`�{�[���z���֕������U�ւ������p���������܂��āA
�܂��Ƃɂ��肪�Ƃ��������܂��B

���L�̂����p���e�ŏ����Ă��܂��̂ŁA�����U�ւ̐\��������������
�����ɁA�O���܂łɈ����ɕK�v�ȋ��z�����������������悤���肢�\���グ�܂��B

�������U�ֈ������F{CreditTransferDate} 
���x�����z�F{UseAmount}�@






�y�����p���e�z
���_��ҁF{CustomerNameKj}�@�l
�����p�X�ܖ��F{SiteNameKj}�@
�����p�\�����F{OrderDate}
�����p�̗������e�F{OneOrderItem}

���L�����Z���i���\���j����Ă���ꍇ�ł��A�s���Ⴂ�ɂē����[����
�@�z�M����Ă��܂��ꍇ���������܂��B
�@���̍ۂ͑�ς��萔�ł����A���L�����p�X�ܗl�ɒ��ڂ��⍇�����������B

�������̑����s���ȓ_�͉��L�t�q�k�����m�F���������B
�@�@�@  �@�@�@https://atobarai-user.jp/faq/

�������p�̗������e�F���i���Ɋւ��邨�₢���킹�́F
���ڂ����p�X�ܗl�ɂ��₢���킹�������B
�����p�X�܁F{SiteNameKj}�@�d�b�F{Phone}

�����x�����Ɋւ��邨�₢���킹�́F
������ЃL���b�`�{�[���@�y{ServiceName}�z
TEL:03-4326-3600(�����y��9:00�`18:00)
Mail: {ServiceMail}

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',null,'2021/12/30 21:03:08',0,'2022/06/08 18:46:08',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (546,117,'�����U�ց@�����������[��(PC)','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z���������m�F�������܂���','{ServiceName}','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
�������������������������������������������������������������������� 

{CustomerNameKj}�l

���̓x�́A{SiteNameKj}�l�ŏ��i���w���̍ۂɁA
�y{ServiceName}�z�������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂����B

{ReceiptDate}��{CustomerNameKj}�l����̂�������
�m�F�������܂����̂ł��񍐂������܂��B

�ȉ����A���񂲓��������������������̓��e�ł������܂��B

�y�̎��ς݂��������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���F{OrderItems}
���������z�F{UseAmount}
�������U�ֈ������F{CreditTransferDate} 

�܂��̂����p��S��肨�҂����Ă���܂��B

�Ȃ��A�������z�Ƃ��������z�ɍ��ق�����ꍇ�́A���A���A������
�������Ă��������ꍇ���������܂��B

���̑��A���x���Ɋւ��Ă��s���ȓ_�͉��L�t�q�k�����m�F���������B
https://atobarai-user.jp/

�����i�̕ԕi�E�����ȂǏ��i�ɂ��Ă�
���ڂ��w���X�l�ɂ��₢���킹���������B
���w���X�l�F{SiteNameKj}
�d�b�F{Phone}
URL�F{SiteUrl}

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',null,'2021/12/30 21:03:08',0,'2022/04/20 6:13:51',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (547,118,'�����U�ց@�����������[��(CEL)','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z���������m�F�������܂���','{ServiceName}','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
�������������������������������������������������������������������� 

{CustomerNameKj}�l

���̓x�́A{SiteNameKj}�l�ŏ��i���w���̍ۂɁA
�y{ServiceName}�z�������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂����B

{ReceiptDate}��{CustomerNameKj}�l����̂�������
�m�F�������܂����̂ł��񍐂������܂��B

�ȉ����A���񂲓��������������������̓��e�ł������܂��B

�y�̎��ς݂��������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���F{OrderItems}
���������z�F{UseAmount}
�������U�ֈ������F{CreditTransferDate} 

�܂��̂����p��S��肨�҂����Ă���܂��B

�Ȃ��A�������z�Ƃ��������z�ɍ��ق�����ꍇ�́A���A���A������
�������Ă��������ꍇ���������܂��B

���̑��A���x���Ɋւ��Ă��s���ȓ_�͉��L�t�q�k�����m�F���������B
https://atobarai-user.jp/

�����i�̕ԕi�E�����ȂǏ��i�ɂ��Ă�
���ڂ��w���X�l�ɂ��₢���킹���������B
���w���X�l�F{SiteNameKj}
�d�b�F{Phone}
URL�F{SiteUrl}

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',null,'2021/12/30 21:03:08',0,'2022/04/20 6:13:59',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (548,119,'�x���������߃��[���i���U ��1�j(PC)','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z�����p�������x���̂��肢','{ServiceName}','{CustomerNameKj}�l

���̓x�́A{SiteNameKj}�ł̗��p�����̂��x�����ɁA
�y(��)�L���b�`�{�[���z���֕������U�ւ������p���������܂��āA
�܂��Ƃɂ��肪�Ƃ��������܂��B

{CustomerNameKj}�l����̂��������m�F�ł��܂���ł����̂�
�{���A�Đ������𔭍s�E�����������܂����B
�i�Đ����萔�������Z�����Ă��������Ă��܂��j
���茳�ɓ͂�����A�����܂łɂ��x���������肢�������܂��B

���x�����z�F{TotalAmount}�@

�y�����p���e�z
���_��ҁF{CustomerNameKj}�@�l
�����p�X�ܖ��F{SiteNameKj}�@
�����p�\�����F{OrderDate}
�����p�̗������e�F{OneOrderItem}


�����x���������߂��Ă��܂��܂��ƁA�X�ɍĐ����萔�������Z����܂��̂ŁA�����Ӊ������B

�����̑����s���ȓ_�͉��L�t�q�k�����m�F���������B
�@�@�@  �@�@�@https://atobarai-user.jp/faq/�@

�������p�̗������e�F���i���Ɋւ��邨�₢���킹�́F
���ڂ����p�X�ܗl�ɂ��₢���킹�������B
�����p�X�܁F{SiteNameKj}�@�d�b�F{Phone}

�����x�����Ɋւ��邨�₢���킹�́F
������ЃL���b�`�{�[���@�y{ServiceName}�z
TEL:03-4326-3600(�����y��9:00�`18:00)
Mail: {ServiceMail}

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',null,'2021/12/30 21:03:08',0,'2022/04/20 6:14:16',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (549,120,'�x���������߃��[���i���U ��1�j(CEL)','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z�����p�������x���̂��肢','{ServiceName}','{CustomerNameKj}�l

���̓x�́A{SiteNameKj}�ł̗��p�����̂��x�����ɁA
�y(��)�L���b�`�{�[���z���֕������U�ւ������p���������܂��āA
�܂��Ƃɂ��肪�Ƃ��������܂��B

{CustomerNameKj}�l����̂��������m�F�ł��܂���ł����̂�
�{���A�Đ������𔭍s�E�����������܂����B
�i�Đ����萔�������Z�����Ă��������Ă��܂��j
���茳�ɓ͂�����A�����܂łɂ��x���������肢�������܂��B

���x�����z�F{TotalAmount}�@

�y�����p���e�z
���_��ҁF{CustomerNameKj}�@�l
�����p�X�ܖ��F{SiteNameKj}�@
�����p�\�����F{OrderDate}
�����p�̗������e�F{OneOrderItem}


�����x���������߂��Ă��܂��܂��ƁA�X�ɍĐ����萔�������Z����܂��̂ŁA�����Ӊ������B

�����̑����s���ȓ_�͉��L�t�q�k�����m�F���������B
�@�@�@  �@�@�@https://atobarai-user.jp/faq/�@

�������p�̗������e�F���i���Ɋւ��邨�₢���킹�́F
���ڂ����p�X�ܗl�ɂ��₢���킹�������B
�����p�X�܁F{SiteNameKj}�@�d�b�F{Phone}

�����x�����Ɋւ��邨�₢���킹�́F
������ЃL���b�`�{�[���@�y{ServiceName}�z
TEL:03-4326-3600(�����y��9:00�`18:00)
Mail: {ServiceMail}

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
 ���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
 �c�Ǝ��ԁF 9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
 mail�F{ServiceMail}
 �^�c��ЁF������ЃL���b�`�{�[��
 �Z���F��140-0002
�@�@�@ �����s�i��擌�i��2-2-24 �V���F�Z���g�����^���[ 12F
-----------------------------------------------------------
',null,'2021/12/30 21:03:09',0,'2022/04/20 6:14:25',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (550,115,'�������������U�փ��[��(PC)','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�������������U�փ��[��','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{CustomerNameKj}�l


���̓x�́A{SiteNameKj}�ł̗��p�����̂��x�����ɁA
�y(��)�L���b�`�{�[���z���֕������U�ւ������p���������܂��āA
�܂��Ƃɂ��肪�Ƃ��������܂��B

���L�̂����p���e�ŏ����Ă��܂��̂ŁA�����U�ւ̐\��������������
�����ɁA�O���܂łɈ����ɕK�v�ȋ��z�����������������悤���肢�\���グ�܂��B

�������U�ֈ������F{CreditTransferDate}
���x�����z�F{UseAmount}�@






�y�����p���e�z
���_��ҁF{CustomerNameKj}�@�l
�����p�X�ܖ��F{SiteNameKj}�@
�����p�\�����F{OrderDate}
�����p�̗������e�F{OneOrderItem}

�������U�ւɂ����p�������������̒ʒ���l�b�g�o���L���O�̓��o�����ׂɂ́A
�ȉ��̂悤�ȓE�v���L�ڂ���܂��̂ŁA�����Ӊ�����
�@�@�@�uMHF){MhfCreditTransferDisplayName}�v

���L�����Z���i���\���j����Ă���ꍇ�ł��A�s���Ⴂ�ɂē����[����
�@�z�M����Ă��܂��ꍇ���������܂��B
�@���̍ۂ͑�ς��萔�ł����A���L�����p�X�ܗl�ɒ��ڂ��⍇�����������B

�������̑����s���ȓ_�͉��L�t�q�k�����m�F���������B
�@�@�@  �@�@�@https://atobarai-user.jp/faq/

�������p�̗������e�F���i���Ɋւ��邨�₢���킹�́F
���ڂ����p�X�ܗl�ɂ��₢���킹�������B
�����p�X�܁F{SiteNameKj}�@�d�b�F{Phone}

�����x�����Ɋւ��邨�₢���킹�́F
������ЃL���b�`�{�[���@�㕥���h�b�g�R�����ƕ�
TEL:03-4326-3600(�����y��9:00�`18:00)
Mail: customer@ato-barai.com

--------------------------------------------------------------
�y�㕥���h�b�g�R���z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
  ���⍇����F03-4326-3600
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com
 
  �^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��140-0002�@�����s�i��擌�i��2-2-24�@�V���F�Z���g�����^���[ 12F
--------------------------------------------------------------
',6,'2022/01/13 19:37:04',83,'2022/03/08 8:41:17',83,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (551,116,'�������������U�փ��[��(CEL)','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�������������U�փ��[��','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{CustomerNameKj}�l


���̓x�́A{SiteNameKj}�ł̗��p�����̂��x�����ɁA
�y(��)�L���b�`�{�[���z���֕������U�ւ������p���������܂��āA
�܂��Ƃɂ��肪�Ƃ��������܂��B

���L�̂����p���e�ŏ����Ă��܂��̂ŁA�����U�ւ̐\��������������
�����ɁA�O���܂łɈ����ɕK�v�ȋ��z�����������������悤���肢�\���グ�܂��B

�������U�ֈ������F{CreditTransferDate} /����26���i��s�x�Ɠ��͗��c�Ɠ��j
���x�����z�F{UseAmount}�@






�y�����p���e�z
���x���ҁF{CustomerNameKj}�@�l
�����p�X�ܖ��F{SiteNameKj}�@
�����p�\�����F{OrderDate}
�����p�̗������e�F{OneOrderItem}

�������U�ւɂ����p�������������̒ʒ���l�b�g�o���L���O�̓��o�����ׂɂ́A�ȉ��̂悤�ȓE�v���L�ڂ���܂��̂ŁA�����Ӊ�����
�@�@�@�uMHF){MhfCreditTransferDisplayName}�v

���L�����Z���i���\���j����Ă���ꍇ�ł��A�s���Ⴂ�ɂē����[����
�@�z�M����Ă��܂��ꍇ���������܂��B
�@���̍ۂ͑�ς��萔�ł����A���L�����p�X�ܗl�ɒ��ڂ��⍇�����������B

�������̑����s���ȓ_�͉��L�t�q�k�����m�F���������B
�@�@�@  �@�@�@https://atobarai-user.jp/faq/

�������p�̗������e�F���i���Ɋւ��邨�₢���킹�́F
���ڂ����p�X�ܗl�ɂ��₢���킹�������B
�����p�X�܁F{SiteNameKj}�@�d�b�F{Phone}

�����x�����Ɋւ��邨�₢���킹�́F
������ЃL���b�`�{�[���@�㕥���h�b�g�R�����ƕ�
TEL:03-4326-3600(�����y��9:00�`18:00)
Mail: customer@ato-barai.com

--------------------------------------------------------------
�y�㕥���h�b�g�R���z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
  ���⍇����F03-4326-3600
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com
 
  �^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��140-0002�@�����s�i��擌�i��2-2-24�@�V���F�Z���g�����^���[ 12F
--------------------------------------------------------------
',6,'2022/01/13 19:37:25',83,'2022/01/23 22:35:51',83,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (552,117,'�����U�ց@�����������[��(PC)','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�����U�ց@�����������[��','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{CustomerNameKj}�@�l

���̓x�́A{SiteNameKj}�ł̗��p�����̂��x�����ɁA
�y(��)�L���b�`�{�[���z���֕������U�ւ������p���������܂��āA
�܂��Ƃɂ��肪�Ƃ��������܂��B

{CustomerNameKj}�l����̂�������
�m�F�������܂����̂ł��񍐐\���グ�܂��B

�ȉ����A���񂲓������������������p���e�ƂȂ�܂��B

�������U�ֈ������F{CreditTransferDate}
 ���x�����z�F{UseAmount}�@

�y�����p���e�z
���_��ҁF{CustomerNameKj}�@�l
�����p�X�ܖ��F{SiteNameKj}�@
�����p�\�����F{OrderDate}
�����p�̗������e�F{OneOrderItem}
�@
�������U�ւɂ����p�������������̒ʒ���l�b�g�o���L���O�̓��o�����ׂɂ́A
�ȉ��̂悤�ȓE�v���L�ڂ���܂��̂ŁA�����Ӊ������@
�@�@�@�uMHF){MhfCreditTransferDisplayName}�v

�����̑����s���ȓ_�͉��L�t�q�k�����m�F���������B
�@�@�@  �@�@�@https://atobarai-user.jp/faq/�@

�������p�̗������e�F���i���Ɋւ��邨�₢���킹�́F
���ڂ����p�X�ܗl�ɂ��₢���킹�������B
�����p�X�܁F{SiteNameKj}�@�d�b�F{Phone}

�����x�����Ɋւ��邨�₢���킹�́F
������ЃL���b�`�{�[���@�㕥���h�b�g�R�����ƕ�
TEL:03-4326-3600(�����y��9:00�`18:00)
Mail: customer@ato-barai.com
--------------------------------------------------------------
�y�㕥���h�b�g�R���z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
  ���⍇����F03-4326-3600
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com
 
  �^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��140-0002�@�����s�i��擌�i��2-2-24�@�V���F�Z���g�����^���[ 12F
--------------------------------------------------------------
',6,'2022/01/13 19:37:46',83,'2022/03/08 8:41:31',83,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (553,118,'�����U�ց@�����������[��(CEL)','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�����U�ց@�����������[��','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{CustomerNameKj}�@�l

���̓x�́A{SiteNameKj}�ł̗��p�����̂��x�����ɁA
�y(��)�L���b�`�{�[���z���֕������U�ւ������p���������܂��āA
�܂��Ƃɂ��肪�Ƃ��������܂��B

{CustomerNameKj}�l����̂�������
�m�F�������܂����̂ł��񍐐\���グ�܂��B

�ȉ����A���񂲓������������������p���e�ƂȂ�܂��B

�������U�ֈ������F{CreditTransferDate} /����26���i��s�x�Ɠ��͗��c�Ɠ��j
 ���x�����z�F{UseAmount}�@

�y�����p���e�z
���x���ҁF{CustomerNameKj}�@�l
�����p�X�ܖ��F{SiteNameKj}�@
�����p�\�����F{OrderDate}
�����p�̗������e�F���i�����@{OneOrderItem}
�@
�������U�ւɂ����p�������������̒ʒ���l�b�g�o���L���O�̓��o�����ׂɂ́A�ȉ��̂悤�ȓE�v��
�@�L�ڂ���܂��̂ŁA�����Ӊ������@
�@�@�@�uMHF){MhfCreditTransferDisplayName}�v

�����̑����s���ȓ_�͉��L�t�q�k�����m�F���������B
�@�@�@  �@�@�@https://atobarai-user.jp/faq/�@

�������p�̗������e�F���i���Ɋւ��邨�₢���킹�́F
���ڂ����p�X�ܗl�ɂ��₢���킹�������B
�����p�X�܁F{SiteNameKj}�@�d�b�F{Phone}

�����x�����Ɋւ��邨�₢���킹�́F
������ЃL���b�`�{�[���@�㕥���h�b�g�R�����ƕ�
TEL:03-4326-3600(�����y��9:00�`18:00)
Mail: customer@ato-barai.com
--------------------------------------------------------------
�y�㕥���h�b�g�R���z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
  ���⍇����F03-4326-3600
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com
 
  �^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��140-0002�@�����s�i��擌�i��2-2-24�@�V���F�Z���g�����^���[ 12F
--------------------------------------------------------------
',6,'2022/01/13 19:38:11',83,'2022/01/23 22:33:49',83,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (554,111,'�����U�֐������[���i����j(PC)','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�����U�֐������[���i����j','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','�����U�֐������[���i����j',6,'2022/01/13 19:38:41',83,'2022/01/13 19:38:41',83,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (555,112,'�����U�֐������[���i����j(CEL)','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�����U�֐������[���i����j','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','�����U�֐������[���i����j',6,'2022/01/13 19:39:17',83,'2022/01/13 19:39:17',83,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (556,113,'�����U�֐������[���i�G���[�A���~�A�o�^�������j(PC)','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�����U�֐������[���i�G���[�A���~�A�o�^�������j','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','�����U�֐������[���i�G���[�A���~�A�o�^�������j',6,'2022/01/13 19:39:38',83,'2022/01/13 19:39:38',83,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (557,114,'�����U�֐������[���i�G���[�A���~�A�o�^�������j(CEL)','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�����U�֐������[���i�G���[�A���~�A�o�^�������j','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','�����U�֐������[���i�G���[�A���~�A�o�^�������j',6,'2022/01/13 19:39:58',83,'2022/01/13 19:39:58',83,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (558,119,'�x���������߃��[���i���U ��1�j(PC)','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�x���������߃��[���i���U ��1�j','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{CustomerNameKj}�l

���̓x�́A{SiteNameKj}�ł̗��p�����̂��x�����ɁA
�y(��)�L���b�`�{�[���z���֕������U�ւ������p���������܂��āA
�܂��Ƃɂ��肪�Ƃ��������܂��B

{CustomerNameKj}�l����̂��������m�F�ł��܂���ł����̂�
�{���A�Đ������𔭍s�E�����������܂����B
�i�Đ����萔�������Z�����Ă��������Ă��܂��j
���茳�ɓ͂�����A�����܂łɂ��x���������肢�������܂��B

���x�����z�F{TotalAmount}�@

�y�����p���e�z
���_��ҁF{CustomerNameKj}�@�l
�����p�X�ܖ��F{SiteNameKj}�@
�����p�\�����F{OrderDate}
�����p�̗������e�F{OneOrderItem}


�����x���������߂��Ă��܂��܂��ƁA�X�ɍĐ����萔�������Z����܂��̂ŁA�����Ӊ������B

�����̑����s���ȓ_�͉��L�t�q�k�����m�F���������B
�@�@�@  �@�@�@https://atobarai-user.jp/faq/�@

�������p�̗������e�F���i���Ɋւ��邨�₢���킹�́F
���ڂ����p�X�ܗl�ɂ��₢���킹�������B
�����p�X�܁F{SiteNameKj}�@�d�b�F{Phone}

�����x�����Ɋւ��邨�₢���킹�́F
������ЃL���b�`�{�[���@�㕥���h�b�g�R�����ƕ�
TEL:03-4326-3600(�����y��9:00�`18:00)
Mail: customer@ato-barai.com

--------------------------------------------------------------
�y�㕥���h�b�g�R���z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
  ���⍇����F03-4326-3600
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com
 
  �^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��140-0002�@�����s�i��擌�i��2-2-24�@�V���F�Z���g�����^���[ 12F
--------------------------------------------------------------
',6,'2022/01/13 19:40:27',83,'2022/03/08 8:41:43',83,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (559,120,'�x���������߃��[���i���U ��1�j(CEL)','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�x���������߃��[���i���U ��1�j','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{CustomerNameKj}�l

���̓x�́A{SiteNameKj}�ł̗��p�����̂��x�����ɁA
�y(��)�L���b�`�{�[���z���֕������U�ւ������p���������܂��āA
�܂��Ƃɂ��肪�Ƃ��������܂��B

{CustomerNameKj}�l����̂��������m�F�ł��܂���ł����̂�
�{���A�Đ������𔭍s�E�����������܂����B
�i�Đ����萔�������Z�����Ă��������Ă��܂��j
���茳�ɓ͂�����A�����܂łɂ��x���������肢�������܂��B

���x�����z�F{UseAmount}�@

�y�����p���e�z
���x���ҁF{CustomerNameKj}�@�l
�����p�X�ܖ��F{SiteNameKj}�@
�����p�\�����F{OrderDate}
�����p�̗������e�F{OneOrderItem}


�����x���������߂��Ă��܂��܂��ƁA�X�ɍĐ����萔�������Z����܂��̂ŁA�����Ӊ������B

�����̑����s���ȓ_�͉��L�t�q�k�����m�F���������B
�@�@�@  �@�@�@https://atobarai-user.jp/faq/�@

�������p�̗������e�F���i���Ɋւ��邨�₢���킹�́F
���ڂ����p�X�ܗl�ɂ��₢���킹�������B
�����p�X�܁F{SiteNameKj}�@�d�b�F{Phone}

�����x�����Ɋւ��邨�₢���킹�́F
������ЃL���b�`�{�[���@�㕥���h�b�g�R�����ƕ�
TEL:03-4326-3600(�����y��9:00�`18:00)
Mail: customer@ato-barai.com

--------------------------------------------------------------
�y�㕥���h�b�g�R���z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
  ���⍇����F03-4326-3600
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com
 
  �^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��140-0002�@�����s�i��擌�i��2-2-24�@�V���F�Z���g�����^���[ 12F
--------------------------------------------------------------
',6,'2022/01/13 19:40:46',83,'2022/01/23 22:36:00',83,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (560,41,'�x���������߃��[���i�ĂR�j�iPC�j','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�ĂR�y�㕥���h�b�g�R���z{OrderDate}{SiteNameKj}�ł̂��������̌�({OrderId})','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɂ������̂��葱�������ς̂悤�ł����
�@�����[���ւ̕ԐM�͂��s�v�ł������܂��B

{CustomerNameKj}�l

���̓x��{SiteNameKj}�ŏ��i���w���̍ۂɁA
�㕥���h�b�g�R���������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ClaimDate}�ɂ����肵���������̂��x�����������߂��Ă�
�������̊m�F�����Ă���܂���ł����̂�
�{���A�Đ������𔭍s�E�����������܂����B
���茳�ɓ͂�����A�����܂łɂ��x���������肢�������܂��B


�y���������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
�Đ����ǉ��萔���F{ReClaimFee}
�x�����Q���F{DamageInterest}
���������z�F{TotalAmount}

�����x���������߂��Ă��܂��܂��ƁA
�Đ����萔�������Z����܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ł������܂�)
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z
{Bk_BankName}�@{Bk_BranchName}
���ʌ����@{Bk_AccountNumber}
{Bk_AccountHolderKn}

�y�X�֐U�֌����z
�����L���F00120�]7
�����ԍ��F670031
�J�j�L���b�`�{�[��

���̑��A���x���Ɋւ��Ă��s���ȓ_�͉��L�t�q�k�����m�F���������B
http://www.ato-barai.com/guidance/faq.html

�����i�̕ԕi�E�����ȂǏ��i�Ɋւ��邨�₢���킹�́F
���ڍw���X�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@
�d�b�F{Phone}

--------------------------------------------------------------
�y�㕥���h�b�g�R���z

  ���⍇����F03-5332-3490
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com
  
�@�^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h6-14-1�@�V�h�O���[���^���[14�K 
--------------------------------------------------------------
',6,'2022/01/18 13:41:49',83,'2022/05/08 13:51:31',83,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (561,43,'�x���������߃��[���i�ĂS�j�iPC�j','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�ĂS�y�㕥���h�b�g�R���z{OrderDate}{SiteNameKj}�ł̂��������̌�({OrderId})','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɂ������̂��葱�������ς̂悤�ł����
�@�����[���ւ̕ԐM�͂��s�v�ł������܂��B

{CustomerNameKj}�l

���̓x��{SiteNameKj}�ŏ��i���w���̍ۂɁA
�㕥���h�b�g�R���������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ClaimDate}�ɂ����肵���������̂��x�����������߂��Ă�
�������̊m�F�����Ă���܂���ł����̂�
�{���A�Đ������𔭍s�E�����������܂����B

���[���̂��x�����ɂ��A���茳�ɓ͂�����
���}���Ή����������܂��悤���肢�������܂��B

�y���������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
�Đ����ǉ��萔���F{ReClaimFee}
�x�����Q���F{DamageInterest}
���������z�F{TotalAmount}

�����x���������߂��Ă��܂��܂��ƁA
�Đ����萔�������Z����܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ł������܂�)
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z
{Bk_BankName}�@{Bk_BranchName}
���ʌ����@{Bk_AccountNumber}
{Bk_AccountHolderKn}

�y�X�֐U�֌����z
�����L���F00120�]7
�����ԍ��F670031
�J�j�L���b�`�{�[��

���̑��A���x���Ɋւ��Ă��s���ȓ_�͉��L�t�q�k�����m�F���������B
http://www.ato-barai.com/guidance/faq.html

�����i�̕ԕi�E�����ȂǏ��i�Ɋւ��邨�₢���킹�́F
���ڍw���X�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@
�d�b�F{Phone}

--------------------------------------------------------------
�y�㕥���h�b�g�R���z

  ���⍇����F03-5332-3490
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com
  
�@�^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h6-14-1�@�V�h�O���[���^���[14�K 
--------------------------------------------------------------
',6,'2022/01/18 13:42:17',83,'2022/05/08 13:51:38',83,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (562,45,'�x���������߃��[���i�ĂT�j�iPC�j','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�ĂT�y�㕥���h�b�g�R���z{OrderDate}{SiteNameKj}�ł̂��������̌�({OrderId})','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɂ������̂��葱�������ς̂悤�ł����
�@�����[���ւ̕ԐM�͂��s�v�ł������܂��B

{CustomerNameKj}�l

���̓x��{SiteNameKj}�ŏ��i���w���̍ۂɁA
�㕥���h�b�g�R���������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ClaimDate}�ɂ����肵���������̂��x�����������߂��Ă�
�������̊m�F�����Ă���܂���ł����̂�
�{���A�Đ������𔭍s�E�����������܂����B

��L����ł����m�点���Ă���ʂ�A
���x�������m�F�ł��Ȃ��ꍇ
���q�l�̐M�p����ȂǕs���v��������\�����������܂��B
���܂��Ă͑��₩�Ȃ��Ή������肢�������܂��B

�y���������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
�Đ����ǉ��萔���F{ReClaimFee}
�x�����Q���F{DamageInterest}
���������z�F{TotalAmount}

�����x���������߂��Ă��܂��܂��ƁA
�Đ����萔�������Z����܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ł������܂�)
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z
{Bk_BankName}�@{Bk_BranchName}
���ʌ����@{Bk_AccountNumber}
{Bk_AccountHolderKn}

�y�X�֐U�֌����z
�����L���F00120�]7
�����ԍ��F670031
�J�j�L���b�`�{�[��

���̑��A���x���Ɋւ��Ă��s���ȓ_�͉��L�t�q�k�����m�F���������B
http://www.ato-barai.com/guidance/faq.html

�����i�̕ԕi�E�����ȂǏ��i�Ɋւ��邨�₢���킹�́F
���ڍw���X�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@
�d�b�F{Phone}

--------------------------------------------------------------
�y�㕥���h�b�g�R���z

  ���⍇����F03-5332-3490
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com
  
�@�^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h6-14-1�@�V�h�O���[���^���[14�K 
--------------------------------------------------------------
',6,'2022/01/18 13:42:39',83,'2022/05/08 13:51:46',83,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (563,47,'�x���������߃��[���i�ĂU�j�iPC�j','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�ĂU�y�㕥���h�b�g�R���z{OrderDate}{SiteNameKj}�ł̂��������̌�({OrderId})','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɂ������̂��葱�������ς̂悤�ł����
�@�����[���ւ̕ԐM�͂��s�v�ł������܂��B

{CustomerNameKj}�l

���̓x��{SiteNameKj}�ŏ��i���w���̍ۂɁA
�㕥���h�b�g�R���������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ClaimDate}�ɂ����肵���������̂��x�����������߂��Ă�
�������̊m�F�����Ă���܂���ł����̂�
�{���A�Đ������𔭍s�E�����������܂����B

���̂܂ܖ�������Ԃ��p������܂��ƁA
���Ђł̑Ή�������ƂȂ�
�ʒm�L�ڂ̑Ή��ƂȂ�ꍇ������܂��B
���܂��Ă͎��}���x�����ɂ���
���Ή����������܂��悤���肢�������܂��B

�y���������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
�Đ����ǉ��萔���F{ReClaimFee}
�x�����Q���F{DamageInterest}
���������z�F{TotalAmount}

�����x���������߂��Ă��܂��܂��ƁA
�Đ����萔�������Z����܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ł������܂�)
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z
{Bk_BankName}�@{Bk_BranchName}
���ʌ����@{Bk_AccountNumber}
{Bk_AccountHolderKn}

�y�X�֐U�֌����z
�����L���F00120�]7
�����ԍ��F670031
�J�j�L���b�`�{�[��

���̑��A���x���Ɋւ��Ă��s���ȓ_�͉��L�t�q�k�����m�F���������B
http://www.ato-barai.com/guidance/faq.html

�����i�̕ԕi�E�����ȂǏ��i�Ɋւ��邨�₢���킹�́F
���ڍw���X�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@
�d�b�F{Phone}

--------------------------------------------------------------
�y�㕥���h�b�g�R���z

  ���⍇����F03-5332-3490
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com
  
�@�^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h6-14-1�@�V�h�O���[���^���[14�K 
--------------------------------------------------------------
',6,'2022/01/18 13:43:05',83,'2022/05/08 13:51:55',83,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (564,49,'�x���������߃��[���i�ĂV�j�iPC�j','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'�ĂV�y�㕥���h�b�g�R���z{OrderDate}{SiteNameKj}�ł̂��������̌�({OrderId})','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','��������������������������������������������������������������������
�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������
��������������������������������������������������������������������

���������m�F�ɍő��4�c�Ɠ������Ԃ�������ꍇ���������܂��B
�@���ɂ������̂��葱�������ς̂悤�ł����
�@�����[���ւ̕ԐM�͂��s�v�ł������܂��B

{CustomerNameKj}�l

���̓x��{SiteNameKj}�ŏ��i���w���̍ۂɁA
�㕥���h�b�g�R���������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂��B

{ClaimDate}�ɂ����肵���������̂��x�����������߂��Ă�
�������̊m�F�����Ă���܂���ł����̂�
�{���A�Đ������𔭍s�E�����������܂����B

�ĎO�ɂ킽��A���ԍςɑ΂����s�����悤���ʒm�������܂�����
�M�a��落�ӂ̂��邲�Ή��𒸂��Ă��Ȃ��󋵂ƂȂ��Ă���܂��B
����ɂ��܂��Ă��A���A���E���x�������m�F�ł��Ȃ��ꍇ��
�ٌ�m�ւ̉���ϔC�������͖@�I�葱���Ɉڍs����������܂���B
�������Ȃ���A���Ѝ��Ǘ����ł�
�M�a�̍����s�ɑ΂�������}��ׂ̑��k������݂��Ă���
���k�ɂ��������\�ȏꍇ���������܂��B
���܂��ẮA�����Ɍ������}���A�����������܂��悤���肢�������܂��B

�y���������e�z
���������F{OrderDate}
�������X�܁F{SiteNameKj}
���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}
�Đ����ǉ��萔���F{ReClaimFee}
�x�����Q���F{DamageInterest}
���������z�F{TotalAmount}

�����x���������߂��Ă��܂��܂��ƁA
�Đ����萔�������Z����܂��̂ŁA�����Ӊ������B

�����L�����֒��ڂ��U���݂��������܂��Ă��������\�ł��B
(�U���ݎ萔���͂��q�l�����S�ł������܂�)
���U���݂��������ꍇ�́A�������̂����O�Ɠ���̂����O�ł��U���݂��������B

�y��s�U�������z
{Bk_BankName}�@{Bk_BranchName}
���ʌ����@{Bk_AccountNumber}
{Bk_AccountHolderKn}

�y�X�֐U�֌����z
�����L���F00120�]7
�����ԍ��F670031
�J�j�L���b�`�{�[��

���̑��A���x���Ɋւ��Ă��s���ȓ_�͉��L�t�q�k�����m�F���������B
http://www.ato-barai.com/guidance/faq.html

�����i�̕ԕi�E�����ȂǏ��i�Ɋւ��邨�₢���킹�́F
���ڍw���X�ɂ��₢���킹�������B
�w���X�܁F{SiteNameKj}�@
�d�b�F{Phone}

--------------------------------------------------------------
�y�㕥���h�b�g�R���z

  ���⍇����F03-5332-3490
  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
  mail: customer@ato-barai.com
  
�@�^�c��ЁF������ЃL���b�`�{�[��
�@�Z���F��160-0023 �����s�V�h�搼�V�h6-14-1�@�V�h�O���[���^���[14�K 
--------------------------------------------------------------
',6,'2022/01/18 13:43:27',83,'2022/05/08 13:52:02',83,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (565,4,'���������s���[���iPC�j','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'���������s�ē����[��','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','���������s�ē����[��',6,'2022/01/18 13:32:06',83,'2022/01/18 13:32:08',83,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (566,121,'�͂��Ă��猈�ϊ������[���iPC�j(�̎����R�����g����)','{ServiceName} ','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z{PaymentMethod}���ςւ̎葱�������̂��m�点 (�����z�M���[���j {OrderId} ','{ServiceName}','{CustomerNameKj}�l

���̓x�́A{SiteNameKj}�l�ŏ��i���w���̍ۂɁA
{ServiceName}�������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂����B

{PaymentMethod}���ς̎葱���������������܂����̂�
���񍐂������܂��B

�ȉ����A���񂲒����̓��e�ł������܂��B

�y���������e�z
������ID�F{OrderId}
���������F{OrderDate}
�������X�܁F{SiteNameKj}
�����p���z�F{UseAmount}�~

�܂��̂����p��S��肨�҂����Ă���܂��B


�̎������K�v�ȏꍇ�͉��LURL��育�m�F�����肢�������܂��B
�E�������m�F�y�[�W�@{OrderPageUrl}
�@�����O�C���ɂ͂��������̂��d�b�ԍ��ƁA
�@�@�������ɋL�ڂ���Ă���p�X���[�h�������p���������B


�����i�̕ԕi�E�����ȂǏ��i�ɂ��Ă�
���ڂ��w���X�l�ɂ��₢���킹���������B
���w���X�l�F{SiteNameKj}
�d�b�F{Phone}
URL�F{SiteUrl}

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
�c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
mail�F{ServiceMail}
�^�c��ЁF������ЃL���b�`�{�[��
�Z���F��140-0002
�@�@�@�����s�i��擌�i��2-2-24�V���F�Z���g�����^���[12F
-----------------------------------------------------------
',null,'2022/03/06 4:47:05',1,'2023/01/04 13:26:36',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (567,122,'�͂��Ă��猈�ϊ������[���iCEL�j(�̎����R�����g����)','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z{PaymentMethod}���ςւ̎葱�������̂��m�点 (�����z�M���[���j {OrderId} ','{ServiceName}','{CustomerNameKj}�l

���̓x�́A{SiteNameKj}�l�ŏ��i���w���̍ۂɁA
{ServiceName}�������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂����B

{PaymentMethod}���ς̎葱���������������܂����̂�
���񍐂������܂��B

�ȉ����A���񂲒����̓��e�ł������܂��B

�y���������e�z
������ID�F{OrderId}
���������F{OrderDate}
�������X�܁F{SiteNameKj}
�����p���z�F{UseAmount}�~

�܂��̂����p��S��肨�҂����Ă���܂��B


�̎������K�v�ȏꍇ�͉��LURL��育�m�F�����肢�������܂��B
�E�������m�F�y�[�W�@{OrderPageUrl}
�@�����O�C���ɂ͂��������̂��d�b�ԍ��ƁA
�@�@�������ɋL�ڂ���Ă���p�X���[�h�������p���������B


�����i�̕ԕi�E�����ȂǏ��i�ɂ��Ă�
���ڂ��w���X�l�ɂ��₢���킹���������B
���w���X�l�F{SiteNameKj}
�d�b�F{Phone}
URL�F{SiteUrl}

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
�c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
mail�F{ServiceMail}
�^�c��ЁF������ЃL���b�`�{�[��
�Z���F��140-0002
�@�@�@�����s�i��擌�i��2-2-24�V���F�Z���g�����^���[12F
-----------------------------------------------------------
',null,'2022/03/06 4:50:00',1,'2023/01/04 13:26:43',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (568,123,'�͂��Ă��猈�ϊ������[���iPC�j(�̎����R�����g�Ȃ�)','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z{PaymentMethod}���ςւ̎葱�������̂��m�点 (�����z�M���[���j {OrderId} ','{ServiceName}','{CustomerNameKj}�l

���̓x�́A{SiteNameKj}�l�ŏ��i���w���̍ۂɁA
{ServiceName}�������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂����B

{PaymentMethod}���ς̎葱���������������܂����̂�
���񍐂������܂��B

�ȉ����A���񂲒����̓��e�ł������܂��B

�y���������e�z
������ID�F{OrderId}
���������F{OrderDate}
�������X�܁F{SiteNameKj}
�����p���z�F{UseAmount}�~

�܂��̂����p��S��肨�҂����Ă���܂��B



�����i�̕ԕi�E�����ȂǏ��i�ɂ��Ă�
���ڂ��w���X�l�ɂ��₢���킹���������B
���w���X�l�F{SiteNameKj}
�d�b�F{Phone}
URL�F{SiteUrl}

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
�c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
mail�F{ServiceMail}
�^�c��ЁF������ЃL���b�`�{�[��
�Z���F��140-0002
�@�@�@�����s�i��擌�i��2-2-24�V���F�Z���g�����^���[12F
-----------------------------------------------------------
',null,'2022/03/06 4:50:08',1,'2023/01/04 13:26:48',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (569,124,'�͂��Ă��猈�ϊ������[���iCEL�j(�̎����R�����g�Ȃ�)','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'�y{ServiceName}�z{PaymentMethod}���ςւ̎葱�������̂��m�点 (�����z�M���[���j {OrderId} ','{ServiceName}','{CustomerNameKj}�l

���̓x�́A{SiteNameKj}�l�ŏ��i���w���̍ۂɁA
{ServiceName}�������p���������܂���
�܂��Ƃɂ��肪�Ƃ��������܂����B

{PaymentMethod}���ς̎葱���������������܂����̂�
���񍐂������܂��B

�ȉ����A���񂲒����̓��e�ł������܂��B

�y���������e�z
������ID�F{OrderId}
���������F{OrderDate}
�������X�܁F{SiteNameKj}
�����p���z�F{UseAmount}�~

�܂��̂����p��S��肨�҂����Ă���܂��B



�����i�̕ԕi�E�����ȂǏ��i�ɂ��Ă�
���ڂ��w���X�l�ɂ��₢���킹���������B
���w���X�l�F{SiteNameKj}
�d�b�F{Phone}
URL�F{SiteUrl}

-----------------------------------------------------------
�y{ServiceName}�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`
���₢���킹��@TEL�F03-4326-3600�@FAX�F03-4326-3690
�c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j
mail�F{ServiceMail}
�^�c��ЁF������ЃL���b�`�{�[��
�Z���F��140-0002
�@�@�@�����s�i��擌�i��2-2-24�V���F�Z���g�����^���[12F
-----------------------------------------------------------
',null,'2022/03/06 4:50:16',1,'2023/01/04 13:26:52',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (570,125,'����p�^�[���`�F�b�N�G���[','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'����p�^�[���`�F�b�N�G���[','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','����p�^�[���`�F�b�N�ŃG���[���������܂����B
�T�C�g�}�X�^�̍X�V�����Ă��������B

{body}',null,'2022/04/03 23:28:54',83,'2022/04/23 2:19:41',83,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (571,126,'���Ǝ҈��e�X�g���[�� ','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'{ServiceName}�@���B�e�X�g���[��','{ServiceName}','�{���[���͑��B�m�F�p�̃e�X�g���[���ł��B

���ƎҁF{EnterpriseNameKj} �l
���O�C��ID�F{LoginId}',null,'2022/07/06 2:16:26',1,'2022/07/06 2:16:26',1,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (572,127,'�}�C�y�[�W�A�g�o�b�`�G���[','�͂��Ă��略��','=?UTF-8?B?GyRCRk8kJCRGJCskaUonJCQbKEI=?=','todoitekara@ato-barai.com',null,null,null,'�}�C�y�[�W�A�g�o�b�`�G���[','=?UTF-8?B?5bGK44GE44Gm44GL44KJ5omV44GE?=','�S���җl

�}�C�y�[�W �� ��V�X�e���̘A�g�o�b�`�ŃG���[���������܂����B
��V�X�e���̓�����񂪔��f����Ă��Ȃ��\��������܂��B

���Ώے�����
{OrderId}

�ȏ�',null,'2022/07/06 2:19:30',1,'2022/07/20 18:10:48',18051,1);



-- Update data from Temporary table to main table with OemId IS NULL OR = 1
UPDATE T_MailTemplate a
LEFT JOIN T_MailTemplate_Tmp b
ON a.Id = b.Id
SET a.ClassName = b.ClassName
,a.FromTitle = b.FromTitle
,a.FromTitleMime = b.FromTitleMime
,a.FromAddress = b.FromAddress
,a.ToTitle = b.ToTitle
,a.ToTitleMime = b.ToTitleMime
,a.ToAddress = b.ToAddress
,a.Subject = b.Subject
,a.SubjectMime = b.SubjectMime
,a.Body = b.Body
,a.UpdateDate = DATE_ADD(NOW(), INTERVAL 9 HOUR)
,a.UpdateId = 1
WHERE a.OemId IS NULL OR a.OemId = 0;

-- Delete temp table
DROP TABLE `coraldb_new01`.`T_MailTemplate_Tmp`;