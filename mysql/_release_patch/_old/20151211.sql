-- ----------------------------------
-- Customerの注文SEQをユニークキーに変更
-- ----------------------------------
ALTER TABLE `T_Customer` 
ADD UNIQUE INDEX `Idx_T_Customer09` (`OrderSeq` ASC);

ALTER TABLE `T_Customer` 
DROP INDEX `Idx_T_Customer02` ;
