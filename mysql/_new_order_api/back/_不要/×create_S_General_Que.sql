-- =============================================================================
-- ■連携DB　汎用シーケンスキュー
-- =============================================================================
DROP TABLE IF EXISTS `S_General_Que`;
CREATE TABLE S_General_Que
(
	QueSeq bigint NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'キューSeq',
	AccessType char(1) NOT NULL COMMENT 'アクセス種別 C：登録 U：更新 D：削除',
	SeqName varchar(50) NOT NULL COMMENT 'シーケンス名',
	RegistDate datetime COMMENT '登録日時',
	UpdateDate datetime COMMENT '更新日時',
	ValidFlg int DEFAULT 1 NOT NULL COMMENT '有効フラグ（0：無効　1：有効）'
) AUTO_INCREMENT = 1 ENGINE = InnoDB COMMENT = '汎用シーケンスキュー' DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;