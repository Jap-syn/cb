-- ----------------------------------
-- Customerのメールアドレスにインデックスを付与
-- ----------------------------------
ALTER TABLE `T_Customer` 
ADD INDEX `Idx_T_Customer08` (`MailAddress` ASC);
