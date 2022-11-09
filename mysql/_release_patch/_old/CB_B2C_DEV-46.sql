-- コメントコードテーブル
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES 
('163', '10', 'ゆうちょPay', NULL, NULL, NULL, '', '0', NOW(), '0',NOW(), '0', '1'),
('163', '11', '楽天銀行コンビニ払', NULL, NULL, NULL, '', '0', NOW(), '0',NOW(), '0', '1'),
('198', '10', 'ゆうちょPay', NULL, NULL, NULL, '', '0', NOW(), '0',NOW(), '0', '1'),
('198', '11', '楽天銀行コンビニ払', NULL, NULL, NULL, '', '0', NOW(), '0',NOW(), '0', '1');

-- サイト情報テーブル 加入者固有コード追加
ALTER TABLE `T_Site` ADD `ReceiptAgentId` BIGINT NOT NULL COMMENT '収納代行会社ID' AFTER `BarcodeLimitDays` ,
ADD `SubscriberCode` VARCHAR( 5 ) NULL COMMENT '加入者固有コード' AFTER `ReceiptAgentId` ;

-- サイト情報 初期値
-- 直販設定済み
UPDATE T_Enterprise,T_Site 
   SET T_Site.SubscriberCode = T_Enterprise.SubscriberCode, 
       T_Site.ReceiptAgentId = 2,
       T_Site.UpdateDate     = CURRENT_TIMESTAMP,
       T_Site.UpdateId       = 1
 WHERE T_Site.EnterpriseId = T_Enterprise.EnterpriseId 
   AND T_Enterprise.SubscriberCode IS NOT NULL
   AND T_Enterprise.EnterpriseId  > 0
   AND (T_Enterprise.OemId IS NULL or T_Enterprise.OemId = 0)
;
-- Eストア設定済み
UPDATE T_Enterprise,T_Site 
   SET T_Site.SubscriberCode = IFNULL(NULLIF(T_Enterprise.SubscriberCode,''),'71200'), 
       T_Site.ReceiptAgentId = 2,
       T_Site.UpdateDate     = CURRENT_TIMESTAMP,
       T_Site.UpdateId       = 1
 WHERE T_Site.EnterpriseId = T_Enterprise.EnterpriseId 
   AND T_Enterprise.SubscriberCode IS NOT NULL
   AND T_Enterprise.EnterpriseId  > 0
   AND T_Enterprise.OemId = 1
;
-- SMBC設定済み
UPDATE T_Enterprise,T_Site 
   SET T_Site.SubscriberCode = IFNULL(NULLIF(T_Enterprise.SubscriberCode,''),'71160'), 
       T_Site.ReceiptAgentId = 2,
       T_Site.UpdateDate     = CURRENT_TIMESTAMP,
       T_Site.UpdateId       = 1
 WHERE T_Site.EnterpriseId = T_Enterprise.EnterpriseId 
   AND T_Enterprise.SubscriberCode IS NOT NULL
   AND T_Enterprise.EnterpriseId  > 0
   AND T_Enterprise.OemId = 2
;
-- 西濃設定済み
UPDATE T_Enterprise,T_Site 
   SET T_Site.SubscriberCode = IFNULL(NULLIF(T_Enterprise.SubscriberCode,''),'71220'), 
       T_Site.ReceiptAgentId = 2,
       T_Site.UpdateDate     = CURRENT_TIMESTAMP,
       T_Site.UpdateId       = 1
 WHERE T_Site.EnterpriseId = T_Enterprise.EnterpriseId 
   AND T_Enterprise.SubscriberCode IS NOT NULL
   AND T_Enterprise.EnterpriseId  > 0
   AND T_Enterprise.OemId = 3
;
-- BASE設定済み
UPDATE T_Enterprise,T_Site 
   SET T_Site.SubscriberCode = IFNULL(NULLIF(T_Enterprise.SubscriberCode,''),'71230'), 
       T_Site.ReceiptAgentId = 2,
       T_Site.UpdateDate     = CURRENT_TIMESTAMP,
       T_Site.UpdateId       = 1
 WHERE T_Site.EnterpriseId = T_Enterprise.EnterpriseId 
   AND T_Enterprise.SubscriberCode IS NOT NULL
   AND T_Enterprise.EnterpriseId  > 0
   AND T_Enterprise.OemId = 4
;
-- テモナ設定済み
UPDATE T_Enterprise,T_Site 
   SET T_Site.SubscriberCode = IFNULL(NULLIF(T_Enterprise.SubscriberCode,''),'71280'), 
       T_Site.ReceiptAgentId = 2,
       T_Site.UpdateDate     = CURRENT_TIMESTAMP,
       T_Site.UpdateId       = 1
 WHERE T_Site.EnterpriseId = T_Enterprise.EnterpriseId 
   AND T_Enterprise.SubscriberCode IS NOT NULL
   AND T_Enterprise.EnterpriseId  > 0
   AND T_Enterprise.OemId = 5
;
-- みずほ設定済み
UPDATE T_Enterprise,T_Site 
   SET T_Site.SubscriberCode = IFNULL(NULLIF(T_Enterprise.SubscriberCode,''),'36920'), 
       T_Site.ReceiptAgentId = 2,
       T_Site.UpdateDate     = CURRENT_TIMESTAMP,
       T_Site.UpdateId       = 1
 WHERE T_Site.EnterpriseId = T_Enterprise.EnterpriseId 
   AND T_Enterprise.SubscriberCode IS NOT NULL
   AND T_Enterprise.EnterpriseId  > 0
   AND T_Enterprise.OemId = 6
;

-- 直販
UPDATE T_Enterprise,T_Site 
   SET T_Site.SubscriberCode = IFNULL(NULLIF(T_Enterprise.SubscriberCode,''),'71160'), 
       T_Site.ReceiptAgentId = 2,
       T_Site.UpdateDate     = CURRENT_TIMESTAMP,
       T_Site.UpdateId       = 1
 WHERE T_Site.EnterpriseId = T_Enterprise.EnterpriseId 
   AND T_Enterprise.SubscriberCode IS NULL
   AND T_Enterprise.EnterpriseId  > 0
   AND (T_Enterprise.OemId IS NULL or T_Enterprise.OemId = 0)
;
-- Eストア
UPDATE T_Enterprise,T_Site 
   SET T_Site.SubscriberCode = IFNULL(NULLIF(T_Enterprise.SubscriberCode,''),'71160'), 
       T_Site.ReceiptAgentId = 2,
       T_Site.UpdateDate     = CURRENT_TIMESTAMP,
       T_Site.UpdateId       = 1
 WHERE T_Site.EnterpriseId = T_Enterprise.EnterpriseId 
   AND T_Enterprise.SubscriberCode IS NULL
   AND T_Enterprise.EnterpriseId  > 0
   AND T_Enterprise.OemId = 1
;
-- SMBC
UPDATE T_Enterprise,T_Site 
   SET T_Site.SubscriberCode = IFNULL(NULLIF(T_Enterprise.SubscriberCode,''),'71160'), 
       T_Site.ReceiptAgentId = 2,
       T_Site.UpdateDate     = CURRENT_TIMESTAMP,
       T_Site.UpdateId       = 1
 WHERE T_Site.EnterpriseId = T_Enterprise.EnterpriseId 
   AND T_Enterprise.SubscriberCode IS NULL
   AND T_Enterprise.EnterpriseId  > 0
   AND T_Enterprise.OemId = 2
;
-- 西濃
UPDATE T_Enterprise,T_Site 
   SET T_Site.SubscriberCode = IFNULL(NULLIF(T_Enterprise.SubscriberCode,''),'71220'), 
       T_Site.ReceiptAgentId = 2,
       T_Site.UpdateDate     = CURRENT_TIMESTAMP,
       T_Site.UpdateId       = 1
 WHERE T_Site.EnterpriseId = T_Enterprise.EnterpriseId 
   AND T_Enterprise.SubscriberCode IS NULL
   AND T_Enterprise.EnterpriseId  > 0
   AND T_Enterprise.OemId = 3
;
-- BASE
UPDATE T_Enterprise,T_Site 
   SET T_Site.SubscriberCode = IFNULL(NULLIF(T_Enterprise.SubscriberCode,''),'71160'), 
       T_Site.ReceiptAgentId = 2,
       T_Site.UpdateDate     = CURRENT_TIMESTAMP,
       T_Site.UpdateId       = 1
 WHERE T_Site.EnterpriseId = T_Enterprise.EnterpriseId 
   AND T_Enterprise.SubscriberCode IS NULL
   AND T_Enterprise.EnterpriseId  > 0
   AND T_Enterprise.OemId = 4
;
-- テモナ
UPDATE T_Enterprise,T_Site 
   SET T_Site.SubscriberCode = IFNULL(NULLIF(T_Enterprise.SubscriberCode,''),'71160'), 
       T_Site.ReceiptAgentId = 2,
       T_Site.UpdateDate     = CURRENT_TIMESTAMP,
       T_Site.UpdateId       = 1
 WHERE T_Site.EnterpriseId = T_Enterprise.EnterpriseId 
   AND T_Enterprise.SubscriberCode IS NULL
   AND T_Enterprise.EnterpriseId  > 0
   AND T_Enterprise.OemId = 5
;
-- みずほ 
UPDATE T_Enterprise,T_Site 
   SET T_Site.SubscriberCode = IFNULL(NULLIF(T_Enterprise.SubscriberCode,''),'71160'), 
       T_Site.ReceiptAgentId = 2,
       T_Site.UpdateDate     = CURRENT_TIMESTAMP,
       T_Site.UpdateId       = 1
 WHERE T_Site.EnterpriseId = T_Enterprise.EnterpriseId 
   AND T_Enterprise.SubscriberCode IS NULL
   AND T_Enterprise.EnterpriseId  > 0
   AND T_Enterprise.OemId = 6
;

-- 直販
UPDATE T_Enterprise
   SET IndividualSubscriberCodeFlg = 0, 
       SubscriberCode              = null
 WHERE (OemId IS NULL or OemId = 0)
;
-- Eストア
UPDATE T_Enterprise
   SET IndividualSubscriberCodeFlg = 0, 
       SubscriberCode              = null
 WHERE OemId = 1
;
-- 西濃
UPDATE T_Enterprise
   SET IndividualSubscriberCodeFlg = 0, 
       SubscriberCode              = null
 WHERE OemId = 3
;
-- テモナ
UPDATE T_Enterprise
   SET IndividualSubscriberCodeFlg = 0, 
       SubscriberCode              = null
 WHERE OemId = 5
;


-- 加入者固有コード管理マスタ
 CREATE TABLE `M_SubscriberCode` (
`ReceiptAgentId` BIGINT NOT NULL COMMENT '収納代行会社ID ',
`SubscriberCode` VARCHAR( 5 ) NOT NULL COMMENT '加入者固有コード',
`SubscriberName` VARCHAR( 50 ) NOT NULL COMMENT '加入者固有名称',
`LinePayUseFlg` TINYINT NOT NULL DEFAULT '0' COMMENT 'LINE使用可否区分　0:不可　1:可',
`Class2` VARCHAR( 50 ) NULL COMMENT '区分2',
`Class3` VARCHAR( 50 ) NULL COMMENT '区分3',
`Class4` VARCHAR( 50 ) NULL COMMENT '区分4',
`Class5` VARCHAR( 50 ) NULL COMMENT '区分5',
`ValidFlg` TINYINT NOT NULL DEFAULT '1' COMMENT '有効フラグ　（0：無効　1：有効）',
`RegistDate` DATETIME NOT NULL ,
`RegistId` INT NOT NULL ,
`UpdateDate` DATETIME NOT NULL ,
`UpdateId` INT NOT NULL ,
UNIQUE (
`ReceiptAgentId` ,
`SubscriberCode`
)
) ENGINE = MYISAM;

INSERT INTO M_SubscriberCode VALUES (2,71290,'[個別]株式会社マキシム(KOBE LETTUCE)',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,71300,'[個別]宮古ガス',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,71320,'[個別]有限会社家所商店',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,71340,'[個別]株式会社アスカコーポレーション',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,71350,'[個別]株式会社グースカンパニー',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,71370,'[個別]株式会社ＡＲＲＯＷＳ',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,71380,'[個別]株式会社パースジャパン',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,71390,'[個別]有限会社中川海苔店',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,71400,'[個別]株式会社マキシム(Isn''t She?)',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,71410,'[個別]株式会社マキシム(BELL PALETTE)',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,71420,'[個別]株式会社マキシム(N WITH.)',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,71430,'[個別]株式会社Ten Buｒger',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,71440,'[個別]株式会社コックス',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,71450,'[個別]文隆堂',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,71460,'[個別]株式会社桃屋',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,71470,'[個別]株式会社アクトエデュケーション',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,71480,'[個別]プリントネット株式会社',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,71490,'[個別]MEGALOPOLIS株式会社',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,71500,'[個別]ナカムラ教材株式会社',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,71510,'[個別]株式会社アオヤマ教材',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,71520,'[個別]株式会社岩崎文昌堂',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,71530,'[個別]株式会社黒木教材社',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,71540,'[個別]株式会社泉教材',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,71550,'[個別]有限会社教学社',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,71560,'[個別]有限会社山口教材社',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,71570,'[個別]有限会社氏田',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,71580,'[個別]有限会社小嶋商店',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,71590,'[個別]有限会社小林商会',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,71600,'[個別]有限会社西村教材',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,71610,'[個別]グロリアス製薬株式会社',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,71630,'[個別]有限会社金井教材社',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72040,'[個別]株式会社イーブックイニシアティブジャパン',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72050,'[個別]株式会社新生技術開発研究所',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72060,'[個別]ユニバーサルミュージック合同会社',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72070,'[個別]株式会社メロンブックス',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72080,'[個別]株式会社JALUX',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72090,'[個別]株式会社Ｅｒｉｉｎａ',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72100,'[個別]ＲＥＮＩ',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72110,'[個別]ロート製薬株式会社',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72120,'[個別]株式会社デジサーチアンドアドバタイジング',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72130,'[個別]ユニフォームネクスト、リンナイ、マキシム、アスカ',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72140,'[個別]マキシム、ｐｕｐｕ、ｔｉｆｉ、HUGME',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72150,'[個別]江崎グリコ株式会社',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72160,'[個別]株式会社コスメ・コム',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72170,'[個別]ポーラ、AMBER BLOOM、PLUEST',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,71160,'株式会社キャッチボール',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,71200,'株式会社Ｅストア',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,71220,'ｾｲﾉｰﾌｨﾅﾝｼｬﾙ株式会社',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,71270,'株式会社キャッチボール（BtoB）',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,71280,'テモナ株式会社',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72020,'[グループ]株式会社キャッチボール（LINE審査なし）',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72030,'[グループ]株式会社キャッチボール（LINE審査NG）',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72180,'[グループ]B2B',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72190,'[グループ]B2B',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72200,'[グループ]オフィス',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72210,'[グループ]オフィス',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72220,'[グループ]グルメ',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72230,'[グループ]グルメ',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72240,'[グループ]グルメ',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72250,'[グループ]グルメ',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72260,'[グループ]グルメ',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72270,'[グループ]グルメ',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72280,'[グループ]ファッション',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72290,'[グループ]ファッション',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72300,'[グループ]ファッション',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72310,'[グループ]ファッション',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72320,'[グループ]レディースファッション',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72330,'[グループ]レディースファッション',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72340,'[グループ]口振',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72350,'[グループ]車・スポーツ',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72360,'[グループ]車・スポーツ',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72370,'[グループ]総合',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72380,'[グループ]電気/工具/エンタメ',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72390,'[グループ]電気/工具/エンタメ',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72400,'[グループ]電気/工具/エンタメ',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72410,'[グループ]電気/工具/エンタメ',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72420,'[グループ]電気/工具/エンタメ',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72430,'[グループ]日常生活',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72440,'[グループ]日常生活',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72450,'[グループ]日常生活',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72460,'[グループ]日常生活',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72470,'[グループ]日常生活',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72480,'[グループ]日常生活',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72490,'[グループ]日常生活',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72500,'[グループ]日常生活',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72510,'[グループ]美容・健康',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72520,'[グループ]美容・健康',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72530,'[グループ]美容・健康',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72540,'[グループ]美容・健康',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72550,'[グループ]美容・健康',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72560,'[グループ]閉鎖',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72570,'[グループ]役務・サービス',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72580,'[グループ]役務・サービス',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72590,'[グループ]B2B',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72600,'[グループ]グルメ',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72610,'[グループ]ファッション',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72620,'[グループ]レディースファッション',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72630,'[グループ]車・スポーツ',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72640,'[グループ]電気/工具/エンタメ',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72650,'[グループ]日常生活',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72660,'[グループ]美容・健康',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72670,'[グループ]役務・サービス',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72680,'[個別]株式会社スワロースポーツ',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72690,'[グループ]車・スポーツ',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72700,'[個別]株式会社Ｌｉｆｅｉｔ',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72710,'[グループ]電気/工具/エンタメ',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72720,'[個別]トクラス株式会社',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72730,'[個別]株式会社ゆとりの空間',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72740,'[グループ]日常生活',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72750,'[個別]株式会社リアルネット',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72760,'[個別]株式会社アイシス',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72770,'[個別]株式会社豆腐の盛田屋',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72780,'[個別]株式会社コスメ・コム',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72790,'[個別]江崎グリコ株式会社',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72800,'[個別]株式会社ビービーラボラトリーズ',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72810,'[グループ]美容・健康',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72820,'[グループ]役務・サービス',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72830,'[個別]株式会社キャラアニ',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,71620,'[個別]CBD研究所株式会社',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,71640,'[個別]株式会社インターゲート',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,71650,'[個別]フォーエース・カンパニー株式会社',1,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72840,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72850,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72860,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72870,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72880,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72890,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72900,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72910,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72920,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72930,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72940,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72950,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72960,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72970,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72980,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,72990,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73000,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73010,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73020,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73030,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73040,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73050,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73060,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73070,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73080,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73090,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73100,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73110,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73120,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73130,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73140,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73150,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73160,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73170,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73180,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73190,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73200,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73210,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73220,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73230,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73240,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73250,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73260,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73270,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73280,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73290,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73300,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73310,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73320,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73330,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73340,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73350,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73360,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73370,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73380,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73390,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73400,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73410,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73420,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73430,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73440,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73450,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73460,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73470,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73480,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73490,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73500,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73510,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73520,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73530,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73540,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73550,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73560,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73570,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73580,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73590,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73600,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73610,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73620,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73630,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73640,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73650,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73660,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73670,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73680,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73690,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73700,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73710,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73720,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73730,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73740,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73750,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73760,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73770,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73780,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73790,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73800,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73810,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73820,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73830,'未登録',0,null,null,null,null,1,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73840,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73850,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73860,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73870,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73880,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73890,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73900,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73910,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73920,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73930,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73940,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73950,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73960,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73970,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73980,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,73990,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74000,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74010,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74020,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74030,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74040,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74050,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74060,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74070,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74080,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74090,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74100,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74110,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74120,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74130,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74140,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74150,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74160,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74170,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74180,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74190,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74200,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74210,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74220,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74230,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74240,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74250,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74260,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74270,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74280,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74290,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74300,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74310,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74320,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74330,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74340,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74350,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74360,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74370,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74380,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74390,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74400,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74410,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74420,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74430,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74440,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74450,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74460,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74470,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74480,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74490,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74500,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74510,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74520,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74530,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74540,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74550,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74560,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74570,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74580,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74590,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74600,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74610,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74620,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74630,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74640,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74650,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74660,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74670,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74680,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74690,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74700,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74710,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74720,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74730,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74740,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74750,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74760,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74770,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74780,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74790,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74800,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74810,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74820,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74830,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74840,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74850,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74860,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74870,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74880,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74890,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74900,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74910,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74920,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74930,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74940,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74950,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74960,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74970,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74980,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,74990,'未登録',0,null,null,null,null,0,now(),1,now(),1);
INSERT INTO M_SubscriberCode VALUES (2,75000,'未登録',0,null,null,null,null,0,now(),1,now(),1);
