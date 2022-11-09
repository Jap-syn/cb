delimiter $$
CREATE ALGORITHM=UNDEFINED DEFINER=`coraluser`@`%` SQL SECURITY DEFINER VIEW `coraldb_mypage01`.`MV_SbpsPayment` AS select 
`coraldb_new01`.`M_SbpsPayment`.`SbpsPaymentId` AS `SbpsPaymentId`,
`coraldb_new01`.`M_SbpsPayment`.`OemId` AS `OemId`,
`coraldb_new01`.`M_SbpsPayment`.`PaymentName` AS `PaymentName`,
`coraldb_new01`.`M_SbpsPayment`.`PaymentNameKj` AS `PaymentNameKj` ,
`coraldb_new01`.`M_SbpsPayment`.`SortId` AS `SortId`,
`coraldb_new01`.`M_SbpsPayment`.`LogoUrl` AS `LogoUrl`,
`coraldb_new01`.`M_SbpsPayment`.`MailParameterNameKj` AS `MailParameterNameKj`
from `coraldb_new01`.`M_SbpsPayment`$$