-- =============================================================================
-- ■連携DB　与信審査結果詳細キュー
-- =============================================================================
DROP TABLE IF EXISTS `T_CjResult_Detail_Que`;
CREATE TABLE T_CjResult_Detail_Que
(
	QueSeq bigint NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'キューSeq',
	AccessType char(1) NOT NULL COMMENT 'アクセス種別 C：登録 U：更新 D：削除',
	Seq bigint NOT NULL COMMENT 'シーケンス',
	RegistDate datetime COMMENT '登録日時',
	UpdateDate datetime COMMENT '更新日時',
	ValidFlg int DEFAULT 1 NOT NULL COMMENT '有効フラグ（0：無効　1：有効）'
) AUTO_INCREMENT = 1 ENGINE = InnoDB COMMENT = '与信審査結果詳細キュー' DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;