DROP VIEW IF EXISTS `MV_MailTemplate`;

CREATE VIEW `MV_MailTemplate` AS
    SELECT *
    FROM coraldb_new01.T_MailTemplate
;
