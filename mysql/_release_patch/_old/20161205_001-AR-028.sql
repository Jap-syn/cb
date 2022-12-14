/* ^MèlÖ[WebNè®^M­§ðÝè][WebNè®^M­§ðÝè][WebNè®^M­§ðÝè]ðÇÁ */
ALTER TABLE `T_CreditJudgeThreshold`
ADD COLUMN `JintecManualJudgeUnpaidFlg` INT(11) NOT NULL DEFAULT '0' AFTER `CoreSystemHoldMIN`,
ADD COLUMN `JintecManualJudgeNonPaymentFlg` INT(11) NOT NULL DEFAULT '0' AFTER `JintecManualJudgeUnpaidFlg`,
ADD COLUMN `JintecManualJudgeSns` TEXT NULL AFTER `JintecManualJudgeNonPaymentFlg`;

/* ^MÊOÖ[è®^Móâ»èÊ]ðÇÁ */
ALTER TABLE `T_CreditLog`
ADD COLUMN `JintecManualJudgeFlg` INT(11) DEFAULT '0' NULL AFTER `Jud_ManualStatus`;

/* Ú[WebNÊ.è®^Móâ»èÊ]Ë[^MÊO.è®^Móâ»èÊ] */
UPDATE T_CreditLog cl, T_JtcResult jr
SET cl.JintecManualJudgeFlg = jr.JintecManualJudgeFlg
WHERE cl.JtcSeq = jr.Seq;

/* [^MVXe»èîCSVo^EC³]ÖRÚÇÁ */
INSERT INTO M_TemplateField VALUES ( 47 , 168, 'ManualJudge-UnpaidFlg' ,'^M­§è®»-¢¥¢1' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 47 , 169, 'ManualJudge-NonPaymentFlg' ,'^M­§è®»-s¥¢1' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 47 , 170, 'ManualJudge-JintecManualJudgeSns' ,'^M­§è®»-R¸VXeñð' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
