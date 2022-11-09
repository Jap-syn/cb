/* 与信結果ログへカラム[社内与信結果スナップショット]追加 */
ALTER TABLE `T_CreditLog` ADD COLUMN `Incre_SnapShot` TEXT NULL DEFAULT NULL AFTER `JintecManualJudgeFlg`;
