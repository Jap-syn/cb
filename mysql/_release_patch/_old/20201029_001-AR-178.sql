/* 印刷キュー転送実行ワーク */
DROP TABLE IF EXISTS `W_Enqueue`;
CREATE TABLE IF NOT EXISTS `W_Enqueue` (
  `Seq` bigint(20) NOT NULL auto_increment,
  `OrderSeq` bigint(20) default NULL,
  PRIMARY KEY  (`Seq`),
  UNIQUE KEY `OrderSeq` (`OrderSeq`),
  KEY `idx_W_Enqueue01` (`OrderSeq`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=0;