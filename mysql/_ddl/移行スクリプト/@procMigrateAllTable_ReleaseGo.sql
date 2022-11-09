DROP procedure IF EXISTS `procMigrateAllTable_ReleaseGo`;

DELIMITER $$
CREATE PROCEDURE `procMigrateAllTable_ReleaseGo` ()
BEGIN

-- ----------------------------------------------------------------------
-- ここからは本番稼動後にCALLする
-- ----------------------------------------------------------------------
--  T_EnterpriseCustomer_管理顧客② 
    CALL var_dump('procMigrateManagementCustomer2' , 'Start');
    CALL procMigrateManagementCustomer2();
    CALL var_dump('procMigrateManagementCustomer2' , 'End');
--  移行時の履歴を取得する
    CALL var_dump('procMigrateOrderHistory' , 'Start');
    CALL procMigrateOrderHistory();
    CALL var_dump('procMigrateOrderHistory' , 'End');

END
$$

DELIMITER ;

 