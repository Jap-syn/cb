/* コードマスターに検索方法を追加 */
INSERT INTO M_CodeManagement VALUES(193, 'CB請求書郵便口座（加入者負担用）', NULL, '郵便口座', 1, 'AK種類＆回数', 1, '郵便口座番号', 1, '口座名義', NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(193, 1, 'CB初回', 'AK0', '001305901557', '株式会社キャッチボール', NULL, 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(193, 11, 'Eストアー初回', 'EA1', '001405665145', '株式会社キャッチボール／Ｅストアー専用', NULL, 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(193, 21, 'SMBC初回', 'AB1', '001506900331', 'ＳＭＢＣファイナンスサービス株式会社', NULL, 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(193, 31, 'セイノー初回', 'SC1', '001007292043', '株式会社キャッチボール　セイノーＦＣ係', NULL, 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(193, 41, 'BASE初回', 'AB1', '001608450807', '株式会社キャッチボール　ＢＡＳＥ専用口座', NULL, 0, NOW(), 1, NOW(), 1, 1);


/* 加盟店テーブルに項目「払込負担区分」を追加 */
ALTER TABLE `T_Enterprise` 
ADD COLUMN `ChargeClass` TINYINT(4) NOT NULL DEFAULT 0 AFTER `ExecStopFlg`;


/* 同梱ツールを利用する加盟店について、払込負担区分を1：払込人負担に更新 */
UPDATE T_Enterprise 
SET ChargeClass = 1 
WHERE IFNULL(SelfBillingMode, 0) > 0;

