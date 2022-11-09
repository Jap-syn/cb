/* T_ShippingApiExecLog“o˜^ */
DROP TABLE IF EXISTS `T_ShippingApiExecLog`;
CREATE TABLE `T_ShippingApiExecLog` (
   `Seq`             bigint(20)   NOT NULL AUTO_INCREMENT
,  `OrderId`         varchar(50)  DEFAULT NULL
,  `EnterpriseId`    BIGINT(255)  DEFAULT NULL
,  `RegistDate`      datetime     DEFAULT NULL
,  PRIMARY KEY (`Seq`)
,  KEY `Idx_T_ShippingApiExecLog01` (`OrderId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;