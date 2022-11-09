/* T_SiteçÄñ⁄í«â¡ */
ALTER TABLE T_Site ADD COLUMN `OtherSitesAuthCheckFlg` TINYINT NOT NULL DEFAULT 1 AFTER `ChatBotFlg`;
UPDATE T_Site SET OtherSitesAuthCheckFlg = 0;

/* T_SiteFreeItems í«â¡ */
DROP TABLE IF EXISTS `T_SiteFreeItems`;
CREATE TABLE `T_SiteFreeItems` (
  `SiteId` bigint(20) NOT NULL ,
  `Free1` text DEFAULT NULL,
  `Free2` text DEFAULT NULL,
  `Free3` text DEFAULT NULL,
  `Free4` text DEFAULT NULL,
  `Free5` text DEFAULT NULL,
  `Free6` text DEFAULT NULL,
  `Free7` text DEFAULT NULL,
  `Free8` text DEFAULT NULL,
  `Free9` text DEFAULT NULL,
  `Free10` text DEFAULT NULL,
  `Free11` text DEFAULT NULL,
  `Free12` text DEFAULT NULL,
  `Free13` text DEFAULT NULL,
  `Free14` text DEFAULT NULL,
  `Free15` text DEFAULT NULL,
  `Free16` text DEFAULT NULL,
  `Free17` text DEFAULT NULL,
  `Free18` text DEFAULT NULL,
  `Free19` text DEFAULT NULL,
  `Free20` text DEFAULT NULL,
  PRIMARY KEY (`SiteId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

