-- ikou_verify02 で仮UPDATE（実際にUPDATEする場合には、MenuSeqを要確認！！！）
UPDATE T_Menu SET Title='注文登録(個別)', Text='注文登録（個別）' WHERE MenuSeq='2';
UPDATE T_Menu SET Title='一括注文登録(CSV)', Text='一括注文登録（CSV）' WHERE MenuSeq='3';
UPDATE T_Menu SET Title='請求書発行(同梱)', Text='請求書発行（同梱）' WHERE MenuSeq='5';
UPDATE T_Menu SET Title='一括注文キャンセル(CSV)', Text='一括注文キャンセル（CSV）' WHERE MenuSeq='25';
UPDATE T_Menu SET Title='一括注文修正(CSV)', Text='一括注文修正（CSV）' WHERE MenuSeq='26';
UPDATE T_Menu SET Title='配送伝票番号入力(個別)', Text='配送伝票番号入力（個別）' WHERE MenuSeq='7';
UPDATE T_Menu SET Title='一括配送伝票番号入力(CSV)', Text='一括配送伝票番号入力（CSV）' WHERE MenuSeq='8';
UPDATE T_Menu SET Title='一括配送伝票番号修正(CSV)', Text='一括配送伝票番号修正（CSV）' WHERE MenuSeq='9';
UPDATE T_Menu SET Text='一括注文登録（CSV）' WHERE MenuSeq='18';
UPDATE T_Menu SET Text='注文登録（個別）' WHERE MenuSeq='19';
UPDATE T_Menu SET Text='一括配送伝票番号入力（CSV）' WHERE MenuSeq='20';
UPDATE T_Menu SET Text='配送伝票番号入力（個別）' WHERE MenuSeq='21';

-- T_Menu へのINSERT
INSERT INTO T_Menu (Module, Class, Id, Ordinal, Href, Title, Text, Desc, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES ('member', 'submenus', 'header_menu_3', '0', 'search/search', '履歴検索', '履歴検索', '過去取引を一覧表示', NOW(), '9', NOW(), '9', '1');
INSERT INTO T_Menu (Module, Class, Id, Ordinal, Href, Title, Text, Desc, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES ('member', 'submenus', 'header_menu_5', '0', 'account/index', '登録情報管理', '登録情報管理', 'お店の情報を表示', NOW(), '9', NOW(), '9', '1');
INSERT INTO T_Menu (Module, Class, Id, Ordinal, Href, Title, Text, Desc, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES ('member', 'submenus', 'header_menu_7', '0', 'index/download', 'ダウンロード', 'ダウンロード', 'サンプルCSVはこちら', NOW(), '9', NOW(), '9', '1');

-- T_MenuAuthority へのINSERT
-- MenuSeq は要確認！！！
INSERT INTO T_MenuAuthority (MenuSeq, RoleCode, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES ('180', '301', NOW(), '9', NOW(), '9', '1');
INSERT INTO T_MenuAuthority (MenuSeq, RoleCode, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES ('180', '399', NOW(), '9', NOW(), '9', '1');
INSERT INTO T_MenuAuthority (MenuSeq, RoleCode, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES ('181', '301', NOW(), '9', NOW(), '9', '1');
INSERT INTO T_MenuAuthority (MenuSeq, RoleCode, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES ('181', '399', NOW(), '9', NOW(), '9', '1');
INSERT INTO T_MenuAuthority (MenuSeq, RoleCode, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES ('182', '301', NOW(), '9', NOW(), '9', '1');
INSERT INTO T_MenuAuthority (MenuSeq, RoleCode, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES ('182', '399', NOW(), '9', NOW(), '9', '1');
