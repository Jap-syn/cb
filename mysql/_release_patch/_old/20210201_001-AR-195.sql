/* ���[���e���v���[�g�ǉ�*/
INSERT INTO `T_MailTemplate` (`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) 
VALUES (110,'�^�M���s�҂������ʒm','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer@ato-barai.com',NULL,NULL,NULL,'�y��Q�z30���ȏ�^�M���s�҂��ɂȂ��Ă��钍��������܂�','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','30���ȏ�^�M���s�҂��ɂȂ��Ă��钍��������܂��B\r\n�X���b�h�̈ꎞ�I�ȕύX�����肢���܂��B\r\n�Ώے����͈ȉ�\r\n{OrderList}',NULL,NOW(),1,NOW(),83,1);

/* ���ߍ��ݕ����ǉ� */
INSERT INTO `M_Code` (`CodeId`,`KeyCode`,`KeyContent`,`Class1`,`Class2`,`Class3`,`Note`,`SystemFlg`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) 
VALUES (72,307,'�������X�g','{OrderList}',110,null,null,1,NOW(),1,NOW(),1,1);

/*�R�����g�ݒ�Ǘ�*/
INSERT INTO M_CodeManagement VALUES(209,'�^�M���s�҂������f�[�^���M�Ώ�',NULL,'�^�M���s�҂������f�[�^���M�Ώ�',0,NULL,0,NULL,0,NULL,NOW(),1,NOW(),1,1);

/*�R�����g�ݒ�̃t�B�[���h�ɒǉ�*/
INSERT INTO M_Code VALUES(209,1,'���M�惁�[���A�h���X',NULL,NULL,NULL,'system@ato-barai.com,cb-360resysmember@mb.scroll360.jp',0,NOW(),1,NOW(),1,1);