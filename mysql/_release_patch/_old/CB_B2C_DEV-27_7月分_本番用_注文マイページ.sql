/*マイページのデータベース*/
-- Create T_SbpsReceiptControl
CREATE TABLE `T_SbpsReceiptControl` (
  `Seq` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '注文Seq',
  `OrderSeq` bigint(20) NOT NULL COMMENT '注文Seq',
  `PayType` int(1) NOT NULL COMMENT '追加支払い方法_区分1：届いてから決済',
  `PaymentName` varchar(30) NOT NULL COMMENT '支払方法（SBPSからの戻り値を設定。）',
  `ReceiptDate` datetime NOT NULL COMMENT '決済完了日時',
  `RegistDate` datetime NOT NULL COMMENT '登録日時',
  `RegistId` int(11) NOT NULL COMMENT '登録者',
  `UpdateDate` datetime NOT NULL COMMENT '更新日時',
  `UpdateId` int(11) NOT NULL COMMENT '更新者',
  `ValidFlg` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`Seq`),
  KEY `Idx_01` (`OrderSeq`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='届いてから決済管理';

-- Create View M_Payment
delimiter $$
CREATE ALGORITHM=UNDEFINED DEFINER=`coraluser`@`%` SQL SECURITY DEFINER VIEW `coraldb_mypage01`.`MV_Payment` AS select 
`coraldb_new01`.`M_Payment`.`PaymentId` AS `PaymentId`,
`coraldb_new01`.`M_Payment`.`OemId` AS `OemId`,
`coraldb_new01`.`M_Payment`.`PaymentGroupName` AS `PaymentGroupName`,
`coraldb_new01`.`M_Payment`.`PaymentName` AS `PaymentName`,
`coraldb_new01`.`M_Payment`.`SortId` AS `SortId`,
`coraldb_new01`.`M_Payment`.`UseFlg` AS `UseFlg`,
`coraldb_new01`.`M_Payment`.`FixedId` AS `FixedId`,
`coraldb_new01`.`M_Payment`.`ValidFlg` AS `ValidFlg`,
`coraldb_new01`.`M_Payment`.`LogoUrl` AS `LogoUrl`
from `coraldb_new01`.`M_Payment`$$

-- Create View MV_SitePayment
delimiter $$
CREATE ALGORITHM=UNDEFINED DEFINER=`coraluser`@`%` SQL SECURITY DEFINER VIEW `coraldb_mypage01`.`MV_SitePayment` AS select 
`coraldb_new01`.`T_SitePayment`.`SitePaymentId` AS `SitePaymentId`,
`coraldb_new01`.`T_SitePayment`.`SiteId` AS `SiteId`,
`coraldb_new01`.`T_SitePayment`.`PaymentId` AS `PaymentId`,
`coraldb_new01`.`T_SitePayment`.`UseFlg` AS `UseFlg`,
`coraldb_new01`.`T_SitePayment`.`ApplyDate` AS `ApplyDate`,
`coraldb_new01`.`T_SitePayment`.`UseStartDate` AS `UseStartDate`,
`coraldb_new01`.`T_SitePayment`.`UseEndDate` AS `UseEndDate`,
`coraldb_new01`.`T_SitePayment`.`ApplyFinishDate` AS `ApplyFinishDate`,
`coraldb_new01`.`T_SitePayment`.`UseStartFixFlg` AS `UseStartFixFlg`,
`coraldb_new01`.`T_SitePayment`.`ValidFlg` AS `ValidFlg`
from `coraldb_new01`.`T_SitePayment`$$

-- Create View MV_SbpsPayment
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

-- Create View MV_SiteSbpsPayment
delimiter $$
CREATE ALGORITHM=UNDEFINED DEFINER=`coraluser`@`%` SQL SECURITY DEFINER VIEW `coraldb_mypage01`.`MV_SiteSbpsPayment` AS select `coraldb_new01`.`T_SiteSbpsPayment`.`PaymentId` AS `PaymentId`,`coraldb_new01`.`T_SiteSbpsPayment`.`NumUseDay` AS `NumUseDay`,`coraldb_new01`.`T_SiteSbpsPayment`.`UseStartDate` AS `UseStartDate`,`coraldb_new01`.`T_SiteSbpsPayment`.`ValidFlg` AS `ValidFlg`,`coraldb_new01`.`T_SiteSbpsPayment`.`SiteId` AS `SiteId` from `coraldb_new01`.`T_SiteSbpsPayment`$$

-- Alter View MV_Site
delimiter $$
ALTER VIEW  `coraldb_mypage01`.`MV_Site` AS select `coraldb_new01`.`T_Site`.`SiteId` AS `SiteId`,`coraldb_new01`.`T_Site`.`RegistDate` AS `RegistDate`,`coraldb_new01`.`T_Site`.`EnterpriseId` AS `EnterpriseId`,`coraldb_new01`.`T_Site`.`SiteNameKj` AS `SiteNameKj`,`coraldb_new01`.`T_Site`.`SiteNameKn` AS `SiteNameKn`,`coraldb_new01`.`T_Site`.`NickName` AS `NickName`,`coraldb_new01`.`T_Site`.`Url` AS `Url`,`coraldb_new01`.`T_Site`.`ReqMailAddrFlg` AS `ReqMailAddrFlg`,`coraldb_new01`.`T_Site`.`ValidFlg` AS `ValidFlg`,`coraldb_new01`.`T_Site`.`SiteForm` AS `SiteForm`,`coraldb_new01`.`T_Site`.`CombinedClaimFlg` AS `CombinedClaimFlg`,`coraldb_new01`.`T_Site`.`OutOfAmendsFlg` AS `OutOfAmendsFlg`,`coraldb_new01`.`T_Site`.`FirstClaimLayoutMode` AS `FirstClaimLayoutMode`,`coraldb_new01`.`T_Site`.`ServiceTargetClass` AS `ServiceTargetClass`,`coraldb_new01`.`T_Site`.`AutoCreditLimitAmount` AS `AutoCreditLimitAmount`,`coraldb_new01`.`T_Site`.`ClaimJournalClass` AS `ClaimJournalClass`,`coraldb_new01`.`T_Site`.`SettlementAmountLimit` AS `SettlementAmountLimit`,`coraldb_new01`.`T_Site`.`SettlementFeeRate` AS `SettlementFeeRate`,`coraldb_new01`.`T_Site`.`ClaimFeeBS` AS `ClaimFeeBS`,`coraldb_new01`.`T_Site`.`ClaimFeeDK` AS `ClaimFeeDK`,`coraldb_new01`.`T_Site`.`ReClaimFeeSetting` AS `ReClaimFeeSetting`,`coraldb_new01`.`T_Site`.`ReClaimFee` AS `ReClaimFee`,`coraldb_new01`.`T_Site`.`ReClaimFee1` AS `ReClaimFee1`,`coraldb_new01`.`T_Site`.`ReClaimFee3` AS `ReClaimFee3`,`coraldb_new01`.`T_Site`.`ReClaimFee4` AS `ReClaimFee4`,`coraldb_new01`.`T_Site`.`ReClaimFee5` AS `ReClaimFee5`,`coraldb_new01`.`T_Site`.`ReClaimFee6` AS `ReClaimFee6`,`coraldb_new01`.`T_Site`.`ReClaimFee7` AS `ReClaimFee7`,`coraldb_new01`.`T_Site`.`ReClaimFeeStartRegistDate` AS `ReClaimFeeStartRegistDate`,`coraldb_new01`.`T_Site`.`ReClaimFeeStartDate` AS `ReClaimFeeStartDate`,`coraldb_new01`.`T_Site`.`FirstCreditTransferClaimFee` AS `FirstCreditTransferClaimFee`,`coraldb_new01`.`T_Site`.`FirstCreditTransferClaimFeeWeb` AS `FirstCreditTransferClaimFeeWeb`,`coraldb_new01`.`T_Site`.`CreditTransferClaimFee` AS `CreditTransferClaimFee`,`coraldb_new01`.`T_Site`.`OemSettlementFeeRate` AS `OemSettlementFeeRate`,`coraldb_new01`.`T_Site`.`OemClaimFee` AS `OemClaimFee`,`coraldb_new01`.`T_Site`.`SystemFee` AS `SystemFee`,`coraldb_new01`.`T_Site`.`CreditCriterion` AS `CreditCriterion`,`coraldb_new01`.`T_Site`.`CreditOrderUseAmount` AS `CreditOrderUseAmount`,`coraldb_new01`.`T_Site`.`AutoCreditDateFrom` AS `AutoCreditDateFrom`,`coraldb_new01`.`T_Site`.`AutoCreditDateTo` AS `AutoCreditDateTo`,`coraldb_new01`.`T_Site`.`AutoCreditCriterion` AS `AutoCreditCriterion`,`coraldb_new01`.`T_Site`.`AutoClaimStopFlg` AS `AutoClaimStopFlg`,`coraldb_new01`.`T_Site`.`SelfBillingFlg` AS `SelfBillingFlg`,`coraldb_new01`.`T_Site`.`SelfBillingFixFlg` AS `SelfBillingFixFlg`,`coraldb_new01`.`T_Site`.`CombinedClaimDate` AS `CombinedClaimDate`,`coraldb_new01`.`T_Site`.`LimitDatePattern` AS `LimitDatePattern`,`coraldb_new01`.`T_Site`.`LimitDay` AS `LimitDay`,`coraldb_new01`.`T_Site`.`PayingBackFlg` AS `PayingBackFlg`,`coraldb_new01`.`T_Site`.`PayingBackDays` AS `PayingBackDays`,`coraldb_new01`.`T_Site`.`SiteConfDate` AS `SiteConfDate`,`coraldb_new01`.`T_Site`.`CreaditStartMail` AS `CreaditStartMail`,`coraldb_new01`.`T_Site`.`CreaditCompMail` AS `CreaditCompMail`,`coraldb_new01`.`T_Site`.`ClaimMail` AS `ClaimMail`,`coraldb_new01`.`T_Site`.`ReceiptMail` AS `ReceiptMail`,`coraldb_new01`.`T_Site`.`CancelMail` AS `CancelMail`,`coraldb_new01`.`T_Site`.`AddressMail` AS `AddressMail`,`coraldb_new01`.`T_Site`.`SoonPaymentMail` AS `SoonPaymentMail`,`coraldb_new01`.`T_Site`.`NotPaymentConfMail` AS `NotPaymentConfMail`,`coraldb_new01`.`T_Site`.`CreditResultMail` AS `CreditResultMail`,`coraldb_new01`.`T_Site`.`AutoJournalDeliMethodId` AS `AutoJournalDeliMethodId`,`coraldb_new01`.`T_Site`.`AutoJournalIncMode` AS `AutoJournalIncMode`,`coraldb_new01`.`T_Site`.`SitClass` AS `SitClass`,`coraldb_new01`.`T_Site`.`T_OrderClass` AS `T_OrderClass`,`coraldb_new01`.`T_Site`.`PrintFormDK` AS `PrintFormDK`,`coraldb_new01`.`T_Site`.`PrintFormBS` AS `PrintFormBS`,`coraldb_new01`.`T_Site`.`FirstClaimKisanbiDelayDays` AS `FirstClaimKisanbiDelayDays`,`coraldb_new01`.`T_Site`.`KisanbiDelayDays` AS `KisanbiDelayDays`,`coraldb_new01`.`T_Site`.`RemindStopClass` AS `RemindStopClass`,`coraldb_new01`.`T_Site`.`BarcodeLimitDays` AS `BarcodeLimitDays`,`coraldb_new01`.`T_Site`.`CombinedClaimChargeFeeFlg` AS `CombinedClaimChargeFeeFlg`,`coraldb_new01`.`T_Site`.`YuchoMT` AS `YuchoMT`,`coraldb_new01`.`T_Site`.`CreditJudgeMethod` AS `CreditJudgeMethod`,`coraldb_new01`.`T_Site`.`AverageUnitPriceRate` AS `AverageUnitPriceRate`,`coraldb_new01`.`T_Site`.`SelfBillingOemClaimFee` AS `SelfBillingOemClaimFee`,`coraldb_new01`.`T_Site`.`ClaimDisposeMail` AS `ClaimDisposeMail`,`coraldb_new01`.`T_Site`.`MultiOrderCount` AS `MultiOrderCount`,`coraldb_new01`.`T_Site`.`MultiOrderScore` AS `MultiOrderScore`,`coraldb_new01`.`T_Site`.`NgChangeFlg` AS `NgChangeFlg`,`coraldb_new01`.`T_Site`.`ShowNgReason` AS `ShowNgReason`,`coraldb_new01`.`T_Site`.`MuhoshoChangeDays` AS `MuhoshoChangeDays`,`coraldb_new01`.`T_Site`.`JintecManualReqFlg` AS `JintecManualReqFlg`,`coraldb_new01`.`T_Site`.`OutOfTermcheck` AS `OutOfTermcheck`,`coraldb_new01`.`T_Site`.`Telcheck` AS `Telcheck`,`coraldb_new01`.`T_Site`.`Addresscheck` AS `Addresscheck`,`coraldb_new01`.`T_Site`.`PostalCodecheck` AS `PostalCodecheck`,`coraldb_new01`.`T_Site`.`Ent_OrderIdcheck` AS `Ent_OrderIdcheck`,`coraldb_new01`.`T_Site`.`EtcAutoArrivalFlg` AS `EtcAutoArrivalFlg`,`coraldb_new01`.`T_Site`.`EtcAutoArrivalNumber` AS `EtcAutoArrivalNumber`,`coraldb_new01`.`T_Site`.`JintecJudge` AS `JintecJudge`,`coraldb_new01`.`T_Site`.`JintecJudge0` AS `JintecJudge0`,`coraldb_new01`.`T_Site`.`JintecJudge1` AS `JintecJudge1`,`coraldb_new01`.`T_Site`.`JintecJudge2` AS `JintecJudge2`,`coraldb_new01`.`T_Site`.`JintecJudge3` AS `JintecJudge3`,`coraldb_new01`.`T_Site`.`JintecJudge4` AS `JintecJudge4`,`coraldb_new01`.`T_Site`.`JintecJudge5` AS `JintecJudge5`,`coraldb_new01`.`T_Site`.`JintecJudge6` AS `JintecJudge6`,`coraldb_new01`.`T_Site`.`JintecJudge7` AS `JintecJudge7`,`coraldb_new01`.`T_Site`.`JintecJudge8` AS `JintecJudge8`,`coraldb_new01`.`T_Site`.`JintecJudge9` AS `JintecJudge9`,`coraldb_new01`.`T_Site`.`PaymentAfterArrivalFlg` AS `PaymentAfterArrivalFlg`,`coraldb_new01`.`T_Site`.`MerchantId` AS `MerchantId`,`coraldb_new01`.`T_Site`.`ServiceId` AS `ServiceId`,`coraldb_new01`.`T_Site`.`HashKey` AS `HashKey`,`coraldb_new01`.`T_Site`.`BasicId` AS `BasicId`,`coraldb_new01`.`T_Site`.`BasicPw` AS `BasicPw`,`coraldb_new01`.`T_Site`.`ReceiptIssueProviso` AS `ReceiptIssueProviso`,`coraldb_new01`.`T_Site`.`SmallLogo` AS `SmallLogo`,`coraldb_new01`.`T_Site`.`SpecificTransUrl` AS `SpecificTransUrl`,`coraldb_new01`.`T_Site`.`CSSettlementFeeRate` AS `CSSettlementFeeRate`,`coraldb_new01`.`T_Site`.`CSClaimFeeBS` AS `CSClaimFeeBS`,`coraldb_new01`.`T_Site`.`CSClaimFeeDK` AS `CSClaimFeeDK`,`coraldb_new01`.`T_Site`.`ReissueCount` AS `ReissueCount`,`coraldb_new01`.`T_Site`.`ClaimAutoJournalIncMode` AS `ClaimAutoJournalIncMode`,`coraldb_new01`.`T_Site`.`RegistId` AS `RegistId`,`coraldb_new01`.`T_Site`.`UpdateDate` AS `UpdateDate`,`coraldb_new01`.`T_Site`.`UpdateId` AS `UpdateId`,`coraldb_new01`.`T_Site`.`PaymentAfterArrivalName` AS `PaymentAfterArrivalName`,`coraldb_new01`.`T_Site`.`ReceiptUsedFlg` AS `ReceiptUsedFlg` from `coraldb_new01`.`T_Site`;

-- T_ReceiptIssueHistory
ALTER TABLE `T_ReceiptIssueHistory` 
ADD COLUMN `ValidFlg` INT(11) NOT NULL DEFAULT 1 AFTER `RegistId`;

-- Insert purchased order to T_SbpsReceiptControl
REPLACE INTO T_SbpsReceiptControl(
OrderSeq
,PayType
,PaymentName
,ReceiptDate
,RegistDate
,RegistId
,UpdateDate
,UpdateId
,ValidFlg
)
SELECT
OrderSeq
,1
,"credit_vm"
,RegistDate
,now()
,1
,now()
,1
,1
FROM T_MypageToBackIF
WHERE IFClass = 4