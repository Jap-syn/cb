/* 雑収入・雑損失管理[発生日]へインデックス付与 */
ALTER TABLE `T_SundryControl` ADD INDEX `Idx_T_SundryControl04` (`ProcessDate` ASC);
