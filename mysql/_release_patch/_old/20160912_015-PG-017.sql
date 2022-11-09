CREATE TABLE `T_NgAccessEnterprise` (
  `EnterpriseId` BIGINT NOT NULL,
  `NgAccessCount` INT NOT NULL DEFAULT 0,
  `NgAccessReferenceDate` DATETIME NULL,
  `UpdateDate` DATETIME NOT NULL,
  PRIMARY KEY (`EnterpriseId`));
