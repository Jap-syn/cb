-- ikou_verify02 �ŉ�UPDATE�i���ۂ�UPDATE����ꍇ�ɂ́AMenuSeq��v�m�F�I�I�I�j
UPDATE T_Menu SET Title='�����o�^(��)', Text='�����o�^�i�ʁj' WHERE MenuSeq='2';
UPDATE T_Menu SET Title='�ꊇ�����o�^(CSV)', Text='�ꊇ�����o�^�iCSV�j' WHERE MenuSeq='3';
UPDATE T_Menu SET Title='���������s(����)', Text='���������s�i�����j' WHERE MenuSeq='5';
UPDATE T_Menu SET Title='�ꊇ�����L�����Z��(CSV)', Text='�ꊇ�����L�����Z���iCSV�j' WHERE MenuSeq='25';
UPDATE T_Menu SET Title='�ꊇ�����C��(CSV)', Text='�ꊇ�����C���iCSV�j' WHERE MenuSeq='26';
UPDATE T_Menu SET Title='�z���`�[�ԍ�����(��)', Text='�z���`�[�ԍ����́i�ʁj' WHERE MenuSeq='7';
UPDATE T_Menu SET Title='�ꊇ�z���`�[�ԍ�����(CSV)', Text='�ꊇ�z���`�[�ԍ����́iCSV�j' WHERE MenuSeq='8';
UPDATE T_Menu SET Title='�ꊇ�z���`�[�ԍ��C��(CSV)', Text='�ꊇ�z���`�[�ԍ��C���iCSV�j' WHERE MenuSeq='9';
UPDATE T_Menu SET Text='�ꊇ�����o�^�iCSV�j' WHERE MenuSeq='18';
UPDATE T_Menu SET Text='�����o�^�i�ʁj' WHERE MenuSeq='19';
UPDATE T_Menu SET Text='�ꊇ�z���`�[�ԍ����́iCSV�j' WHERE MenuSeq='20';
UPDATE T_Menu SET Text='�z���`�[�ԍ����́i�ʁj' WHERE MenuSeq='21';

-- T_Menu �ւ�INSERT
INSERT INTO T_Menu (Module, Class, Id, Ordinal, Href, Title, Text, Desc, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES ('member', 'submenus', 'header_menu_3', '0', 'search/search', '��������', '��������', '�ߋ�������ꗗ�\��', NOW(), '9', NOW(), '9', '1');
INSERT INTO T_Menu (Module, Class, Id, Ordinal, Href, Title, Text, Desc, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES ('member', 'submenus', 'header_menu_5', '0', 'account/index', '�o�^���Ǘ�', '�o�^���Ǘ�', '���X�̏���\��', NOW(), '9', NOW(), '9', '1');
INSERT INTO T_Menu (Module, Class, Id, Ordinal, Href, Title, Text, Desc, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES ('member', 'submenus', 'header_menu_7', '0', 'index/download', '�_�E�����[�h', '�_�E�����[�h', '�T���v��CSV�͂�����', NOW(), '9', NOW(), '9', '1');

-- T_MenuAuthority �ւ�INSERT
-- MenuSeq �͗v�m�F�I�I�I
INSERT INTO T_MenuAuthority (MenuSeq, RoleCode, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES ('180', '301', NOW(), '9', NOW(), '9', '1');
INSERT INTO T_MenuAuthority (MenuSeq, RoleCode, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES ('180', '399', NOW(), '9', NOW(), '9', '1');
INSERT INTO T_MenuAuthority (MenuSeq, RoleCode, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES ('181', '301', NOW(), '9', NOW(), '9', '1');
INSERT INTO T_MenuAuthority (MenuSeq, RoleCode, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES ('181', '399', NOW(), '9', NOW(), '9', '1');
INSERT INTO T_MenuAuthority (MenuSeq, RoleCode, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES ('182', '301', NOW(), '9', NOW(), '9', '1');
INSERT INTO T_MenuAuthority (MenuSeq, RoleCode, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES ('182', '399', NOW(), '9', NOW(), '9', '1');
