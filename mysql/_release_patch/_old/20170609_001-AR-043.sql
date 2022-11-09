/* ‰Á–¿“Xƒ}ƒXƒ^ */
ALTER TABLE `T_Enterprise` 
ADD COLUMN `ExecStopFlg` TINYINT(4) NOT NULL DEFAULT 0 AFTER `SendMailRequestModifyJournalFlg`;
