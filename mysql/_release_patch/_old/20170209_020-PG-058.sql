/* ‰Á–¿“X‚ÖƒJƒ‰ƒ€’Ç‰Á */
ALTER TABLE `T_Enterprise` ADD COLUMN `ClaimOrder1` tinyint(4) NOT NULL DEFAULT '1' AFTER `DispOrder3`;
ALTER TABLE `T_Enterprise` ADD COLUMN `ClaimOrder2` TINYINT(4) NOT NULL DEFAULT '1' AFTER `ClaimOrder1`;
