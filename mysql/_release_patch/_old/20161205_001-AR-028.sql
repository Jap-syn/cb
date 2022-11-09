/* 与信閾値へ[ジンテック手動与信強制条件設定][ジンテック手動与信強制条件設定][ジンテック手動与信強制条件設定]を追加 */
ALTER TABLE `T_CreditJudgeThreshold`
ADD COLUMN `JintecManualJudgeUnpaidFlg` INT(11) NOT NULL DEFAULT '0' AFTER `CoreSystemHoldMIN`,
ADD COLUMN `JintecManualJudgeNonPaymentFlg` INT(11) NOT NULL DEFAULT '0' AFTER `JintecManualJudgeUnpaidFlg`,
ADD COLUMN `JintecManualJudgeSns` TEXT NULL AFTER `JintecManualJudgeNonPaymentFlg`;

/* 与信結果ログへ[手動与信候補判定結果]を追加 */
ALTER TABLE `T_CreditLog`
ADD COLUMN `JintecManualJudgeFlg` INT(11) DEFAULT '0' NULL AFTER `Jud_ManualStatus`;

/* 移送[ジンテック結果.手動与信候補判定結果]⇒[与信結果ログ.手動与信候補判定結果] */
UPDATE T_CreditLog cl, T_JtcResult jr
SET cl.JintecManualJudgeFlg = jr.JintecManualJudgeFlg
WHERE cl.JtcSeq = jr.Seq;

/* [与信システム判定基準CSV登録・修正]へ３項目追加 */
INSERT INTO M_TemplateField VALUES ( 47 , 168, 'ManualJudge-UnpaidFlg' ,'与信強制手動化-未払い1' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 47 , 169, 'ManualJudge-NonPaymentFlg' ,'与信強制手動化-不払い1' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 47 , 170, 'ManualJudge-JintecManualJudgeSns' ,'与信強制手動化-審査システム回答条件' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
