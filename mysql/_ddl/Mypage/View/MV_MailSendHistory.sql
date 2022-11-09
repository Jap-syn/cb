DROP VIEW IF EXISTS `MV_MailSendHistory`;

CREATE VIEW `MV_MailSendHistory` AS
    SELECT *
    FROM coraldb_new01.T_MailSendHistory
;
