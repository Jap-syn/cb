/*マイページのデータベース*/
-- Create View MV_SbpsPayment
drop view `coraldb_mypage03`.`MV_SbpsPayment`;
delimiter $$
CREATE ALGORITHM=UNDEFINED DEFINER=`coraluser`@`%` SQL SECURITY DEFINER VIEW `coraldb_mypage01`.`MV_SbpsPayment` AS select 
`coraldb_test03`.`M_SbpsPayment`.`SbpsPaymentId` AS `SbpsPaymentId`,
`coraldb_test03`.`M_SbpsPayment`.`OemId` AS `OemId`,
`coraldb_test03`.`M_SbpsPayment`.`PaymentName` AS `PaymentName`,
`coraldb_test03`.`M_SbpsPayment`.`PaymentNameKj` AS `PaymentNameKj` ,
`coraldb_test03`.`M_SbpsPayment`.`SortId` AS `SortId`,
`coraldb_test03`.`M_SbpsPayment`.`LogoUrl` AS `LogoUrl`,
`coraldb_test03`.`M_SbpsPayment`.`MailParameterNameKj` AS `MailParameterNameKj`
from `coraldb_test03`.`M_SbpsPayment`$$
