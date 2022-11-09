-- 定型備考定義テーブル作成
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

-- 定型備考関連付けテーブル作成
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


INSERT INTO T_Menu VALUES (184, 'cbadmin', 'kanriMenus', 'fixednote', NULL, '***', '定型備考管理', '定型備考管理', '', '', '', NOW(), 9, NOW(), 9, 1);
INSERT INTO T_MenuAuthority VALUES (184, 1, NOW(), 9, NOW(), 9, 0);
INSERT INTO T_MenuAuthority VALUES (184, 11, NOW(), 9, NOW(), 9, 1);
INSERT INTO T_MenuAuthority VALUES (184, 101, NOW(), 9, NOW(), 9, 1);

/* 定型備考定義データ登録 */
INSERT INTO T_FixedNoteDefine VALUES(1, 0, '連絡種別（直販）', 1, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(2, 1, 'エンドよりTEL(登録番号)', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(3, 1, 'エンド家族・第三者よりTEL', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(4, 1, '店舗よりTEL', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(5, 1, 'エンドよりメール', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(6, 1, '店舗よりメール', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(7, 1, 'エンドへTEL', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(8, 1, '店舗へTEL', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(9, 1, 'エンドへメール', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(10, 1, '店舗へメール', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(11, 1, 'その他', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(12, 0, '連絡種別（Eストアー）', 2, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(13, 1, 'EストアーよりTEL', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(14, 1, 'Eストアーよりメール', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(15, 1, 'ｓｐ請求書再発行依頼', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(16, 1, 'ｓｐ伝票番号変更依頼', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(17, 0, '連絡種別（セイノー）', 3, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(18, 1, 'セイノーよりTEL', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(19, 1, 'セイノーよりメール', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(20, 0, '連絡種別（BASE）', 4, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(21, 1, 'BASEよりTEL', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(22, 1, 'BASEよりメール', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(23, 0, '請求書(問い合わせ内容)', 5, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(24, 1, '請求書紛失・破損', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(25, 1, '請求書未着', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(26, 1, '名義・住所変更', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(27, 1, 'バーコード読取不可', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(28, 1, '同梱漏れ', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(29, 1, '差額請求書', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(30, 0, '請求書(対応内容)', 6, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(31, 1, '手数料了承の上再発行。', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(32, 1, '手数料確認中。', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(33, 1, '住所相違ないので初回再発行。', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(34, 1, '住所相違ないのでもう少し待ってて。', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(35, 1, '初回再発行。', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(36, 1, '再1ゼロ円で再発行。', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(37, 0, '支払い相談(問い合わせ内容)', 7, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(38, 1, '請求書期限切れ', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(39, 1, '支払い期限延長', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(40, 1, '分割希望', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(41, 0, '支払い相談(対応内容)', 8, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(42, 1, '手数料了承の上再発行。', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(43, 1, '支払い約束。', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(44, 1, '再連絡約束。', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(45, 1, '弁護士誘導。', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(46, 1, '支払い不履行。', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(47, 1, '口座案内。', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(48, 1, '債権管理部へエスカレ。', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(49, 0, '与信関係(問い合わせ内容)', 9, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(50, 1, 'NG問い', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(51, 1, '再審査', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(52, 1, '無保証依頼', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(53, 0, '与信関係(対応内容)', 10, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(54, 1, 'AK●不払いのためNG。', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(55, 1, 'AK●　●日遅れのためNG。', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(56, 1, '統計NG。', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(57, 1, 'TEL無効のためNG。', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(58, 1, 'TEL不備のためNG。', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(59, 1, '期限内あるためNG。', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(60, 1, '追加情報あれば再与信可能。', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(61, 1, '無保証OK。', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(62, 1, '無保証でもNG。', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(63, 0, '支払済(問い合わせ内容)', 11, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(64, 1, '支払済なのに請求が来ている', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(65, 1, '二重に支払ってしまった', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(66, 0, '支払済(対応内容)', 12, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(67, 1, '入れ違い請求書。破棄依頼。', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(68, 1, '別件請求と勘違い。支払い誘導。', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(69, 1, '入金確認できず。', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(70, 1, '返金案内。', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(71, 1, '店舗直接入金。', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(72, 1, '重複登録。', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(73, 0, '商品(問い合わせ内容)', 13, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(74, 1, '商品未着', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(75, 1, 'キャンセル(定期解約)', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(76, 1, '商品について', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(77, 1, '別決済で支払い済', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(78, 1, '決済方法変更希望', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(79, 1, '注文の覚えなし', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(80, 0, '商品(対応内容)', 14, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(81, 1, '店舗誘導', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(82, 1, 'CBから店舗へ確認', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(83, 0, '戻り請求(連絡内容)', 15, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(84, 1, '初回戻り請求書。', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(85, 1, '再1戻り請求書。', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(86, 1, '再3戻り請求書。', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(87, 1, '再4戻り請求書。', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(88, 1, '再5戻り請求書。', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(89, 1, '再6戻り請求書。', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(90, 1, '再7戻り請求書。', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(91, 0, '戻り請求(対応内容)', 16, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(92, 1, '不達メール送信', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(93, 1, '配送先へ初回再発行', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(94, 1, '登録TEL架電', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(95, 1, '対応済のため処理せず', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(96, 1, 'キャンセル済のため処理せず', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(97, 1, '入金済のため処理せず', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(98, 0, '返金連絡(連絡内容)', 17, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(99, 1, '返金口座確認', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(100, 1, '充当か返金か', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(101, 0, '返金連絡(対応内容)', 18, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(102, 1, '返金口座確認', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(103, 1, '反響待ち【再連絡】', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(104, 1, '充当', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(105, 1, '店舗直接入金', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(106, 1, '再登録後、充当', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(107, 1, 'CBより返金', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(108, 1, '店舗より返金', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(109, 1, '店舗確認中', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(110, 0, 'キャンセル(連絡内容)', 19, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(111, 1, '【区分3】', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(112, 1, '【区分4】', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(113, 0, 'キャンセル(対応内容)', 20, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(114, 1, 'キャンセルで間違いない。確定する', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(115, 1, '店舗確認中。連絡待ち', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(116, 0, '督促-架電・受電（問い合わせ内容)', 21, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(117, 1, '架電→本人', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(118, 1, '架電→出ず', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(119, 1, '架電→留守電吹込み', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(120, 1, '登録電話より受電', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(121, 1, '架電→使用なし', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(122, 1, '架電→通話中', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(123, 1, '架電→別人', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(124, 1, '架電→都合停止', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(125, 1, '架電→従業員', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(126, 1, '架電→着信拒否', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(127, 1, '架電→家族伝言', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(128, 1, '架電→従業員伝言', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(129, 1, '架電→FAX', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(130, 1, '架電→データ専用', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(131, 1, '架電→圏外', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(132, 1, '架電→故障', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(133, 0, 'サービサー(問い合わせ内容)', 22, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(134, 1, '東新宿法律事務所送り', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(135, 1, '坂本法律事務所送り', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(136, 1, '田中・下元法律事務所送り', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(137, 1, '東新宿法律事務所分 敗北', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(138, 1, 'コモンズ法律事務所送り（東新宿戻り分）', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(139, 0, '債権返却(問い合わせ内容)', 23, 1, 1, NOW(), 1, NOW(), 1, 1);
INSERT INTO T_FixedNoteDefine VALUES(140, 1, '店舗へ債権返却', NULL, 1, 1, NOW(), 1, NOW(), 1, 1);


/* 定型備考関連付けデータ登録 */

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
