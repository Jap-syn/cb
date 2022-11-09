/* サイトへカラム[延滞起算猶予（初回請求）]追加 */
ALTER TABLE `T_Site` ADD COLUMN `FirstClaimKisanbiDelayDays` INT NOT NULL DEFAULT 0 AFTER `PrintFormBS`;