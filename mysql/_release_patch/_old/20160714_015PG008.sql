-- ---------------------------
-- 基幹スキーマ向け
-- ---------------------------
ALTER TABLE T_MypageOrder ADD COLUMN `AccessKey` VARCHAR(100)  NULL AFTER `OemId`;
ALTER TABLE T_MypageOrder ADD COLUMN `AccessKeyValidToDate` DATETIME  NULL AFTER `AccessKey`;

ALTER TABLE `T_MypageOrder` 
ADD INDEX `Idx_T_MypageOrder05` (`AccessKey` ASC);


INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'OrderMypageAccessUrlValidDays', '14', '注文ﾏｲﾍﾟｰｼﾞｱｸｾｽ用URL期間日数', NOW(), 9, NOW(), 9, '1');

INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `SystemFlg` ,`RegistDate` ,`RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('72', '285', '注文ﾏｲﾍﾟｰｼﾞURL', '{OrderPageAccessUrl}', '4', '5', '1',NOW() , '1' ,NOW() ,'1' , '1');
UPDATE `M_Code` SET `KeyContent`='https://www.atobarai.jp/orderpage' WHERE `CodeId`='105' and`KeyCode`='2';

INSERT INTO M_CodeManagement VALUES(185 ,'ＰＣ用注文マイページ　アクセス期限切れ文言' ,NULL ,'OEMID' ,1 ,NULL ,0,NULL,0,NULL, NOW(),1,NOW(),1,1);
INSERT INTO M_CodeManagement VALUES(186 ,'スマホ用注文マイページ　アクセス期限切れ文言' ,NULL ,'OEMID' ,1 ,NULL ,0,NULL,0,NULL, NOW(),1,NOW(),1,1);
INSERT INTO M_Code VALUES(185,0 ,'直営注文マイページPCﾍﾟｼﾞ',NULL ,NULL , NULL ,'注文情報確認ページの閲覧は一定期間で終了いたします。<br>注文情報のお問い合わせにつきましては弊社サポートセンターまでお問い合わせください。<br>サポートセンター電話番号： TEL: 0120-667-690（10:00 ～ 18:00）' ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(185,1 ,'Ｅストア注文マイページPCﾍﾟｼﾞ',NULL ,NULL , NULL ,'注文情報確認ページの閲覧は一定期間で終了いたします。<br>注文情報のお問い合わせにつきましては弊社サポートセンターまでお問い合わせください。<br>サポートセンター電話番号： TEL: 0120-667-690（10:00 ～ 18:00）' ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(185,3 ,'セイノー注文マイページPCﾍﾟｼﾞ',NULL ,NULL , NULL ,'注文情報確認ページの閲覧は一定期間で終了いたします。<br>注文情報のお問い合わせにつきましては弊社サポートセンターまでお問い合わせください。<br>サポートセンター電話番号： TEL: 0120-667-690（10:00 ～ 18:00）' ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(185,4 ,'ＢＡＳＥ注文マイページPCﾍﾟｼﾞ',NULL ,NULL , NULL ,'注文情報確認ページの閲覧は一定期間で終了いたします。<br>注文情報のお問い合わせにつきましては弊社サポートセンターまでお問い合わせください。<br>サポートセンター電話番号： TEL: 0120-667-690（10:00 ～ 18:00）' ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(186,0 ,'直営注文マイページｽﾏﾎﾍﾟｰｼﾞ',NULL ,NULL , NULL ,'注文情報確認ページの閲覧は一定期間で終了いたします。<br>注文情報のお問い合わせにつきましては弊社サポートセンターまでお問い合わせください。<br>サポートセンター電話番号： TEL: 0120-667-690（10:00 ～ 18:00）' ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(186,1 ,'Ｅストア注文マイページｽﾏﾎﾍﾟｰｼﾞ',NULL ,NULL , NULL ,'注文情報確認ページの閲覧は一定期間で終了いたします。<br>注文情報のお問い合わせにつきましては弊社サポートセンターまでお問い合わせください。<br>サポートセンター電話番号： TEL: 0120-667-690（10:00 ～ 18:00）' ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(186,3 ,'セイノー注文マイページｽﾏﾎﾍﾟｰｼﾞ',NULL ,NULL , NULL ,'注文情報確認ページの閲覧は一定期間で終了いたします。<br>注文情報のお問い合わせにつきましては弊社サポートセンターまでお問い合わせください。<br>サポートセンター電話番号： TEL: 0120-667-690（10:00 ～ 18:00）' ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(186,4 ,'ＢＡＳＥ注文マイページｽﾏﾎﾍﾟｰｼﾞ',NULL ,NULL , NULL ,'注文情報確認ページの閲覧は一定期間で終了いたします。<br>注文情報のお問い合わせにつきましては弊社サポートセンターまでお問い合わせください。<br>サポートセンター電話番号： TEL: 0120-667-690（10:00 ～ 18:00）' ,0, NOW(), 1, NOW(), 1, 1);


-- 過去データに対し、ｱｸｾｽキーを設定
UPDATE T_MypageOrder mo
SET AccessKey = SUBSTRING(CONCAT(mo.Seq, SHA2(MD5(RAND()), 512)), 1, 50) -- 一意にするために、ここではPKを利用する
   ,AccessKeyValidToDate = DATE_ADD(( SELECT LimitDate FROM T_ClaimHistory WHERE OrderSeq = mo.OrderSeq AND ValidFlg = 1 AND ClaimPattern = 1 ORDER BY Seq DESC LIMIT 1), INTERVAL 14 DAY)
;

SELECT AccessKey, count(1)
FROM T_MypageOrder
GROUP BY AccessKey
HAVING count(1) > 1;


-- ---------------------------
-- マイページスキーマ向け
-- ---------------------------
-- Viewの再構成 ※運用環境、公開環境はスキーマが異なるので注意
DROP VIEW IF EXISTS `MV_MypageOrder`;

CREATE VIEW `MV_MypageOrder` AS
    SELECT *
    FROM coraldb_new01.T_MypageOrder
;
