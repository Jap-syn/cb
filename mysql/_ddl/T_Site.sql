CREATE TABLE IF NOT EXISTS `T_Site` (
`SiteId` bigint(20) NOT NULL AUTO_INCREMENT,
`RegistDate` datetime DEFAULT NULL,
`EnterpriseId` bigint(20) DEFAULT NULL,
`SiteNameKj` varchar(160) DEFAULT NULL,
`SiteNameKn` varchar(160) DEFAULT NULL,
`NickName` varchar(160) DEFAULT NULL,
`Url` varchar(255) DEFAULT NULL,
`ReqMailAddrFlg` int(11) DEFAULT NULL,
`ValidFlg` int(11) NOT NULL DEFAULT '1',
`SiteForm` int(11) DEFAULT NULL,
`CombinedClaimFlg` int(11) DEFAULT NULL,
`OutOfAmendsFlg` int(11) DEFAULT NULL,
`FirstClaimLayoutMode` int(11) NOT NULL DEFAULT '0',
`ServiceTargetClass` tinyint(4) NOT NULL DEFAULT '0',
`AutoCreditLimitAmount` int(11) DEFAULT NULL,
`ClaimJournalClass` tinyint(4) NOT NULL DEFAULT '0',
`SettlementAmountLimit` int(11) DEFAULT NULL,
`SettlementFeeRate` decimal(16,5) DEFAULT NULL,
`ClaimFeeBS` int(11) DEFAULT NULL,
`ClaimFeeDK` int(11) DEFAULT NULL,
`ReClaimFeeSetting` tinyint(4) NOT NULL DEFAULT '0',
`ReClaimFee` int(11) DEFAULT NULL,
`ReClaimFee1` int(11) DEFAULT NULL,
`ReClaimFee3` int(11) DEFAULT NULL,
`ReClaimFee4` int(11) DEFAULT NULL,
`ReClaimFee5` int(11) DEFAULT NULL,
`ReClaimFee6` int(11) DEFAULT NULL,
`ReClaimFee7` int(11) DEFAULT NULL,
`ReClaimFeeStartRegistDate` date DEFAULT NULL,
`ReClaimFeeStartDate` date DEFAULT NULL,
`FirstCreditTransferClaimFee` int(11) DEFAULT NULL,
`FirstCreditTransferClaimFeeWeb` int(11) DEFAULT NULL,
`CreditTransferClaimFee` int(11) DEFAULT NULL,
`OemSettlementFeeRate` decimal(16,5) DEFAULT NULL,
`OemClaimFee` int(11) DEFAULT NULL,
`SystemFee` int(11) DEFAULT NULL,
`CreditCriterion` int(11) NOT NULL,
`CreditOrderUseAmount` int(11) DEFAULT NULL,
`AutoCreditDateFrom` date DEFAULT NULL,
`AutoCreditDateTo` date DEFAULT NULL,
`AutoCreditCriterion` int(11) NOT NULL,
`AutoClaimStopFlg` tinyint(4) NOT NULL DEFAULT '0',
`SelfBillingFlg` tinyint(4) NOT NULL DEFAULT '0',
`SelfBillingFixFlg` tinyint(4) NOT NULL DEFAULT '0',
`CombinedClaimDate` int(11) DEFAULT NULL,
`LimitDatePattern` int(11) DEFAULT NULL,
`LimitDay` int(11) DEFAULT NULL,
`PayingBackFlg` tinyint(4) NOT NULL DEFAULT '0',
`PayingBackDays` int(11) DEFAULT NULL,
`SiteConfDate` date DEFAULT NULL,
`CreaditStartMail` tinyint(4) NOT NULL DEFAULT '0',
`CreaditCompMail` tinyint(4) NOT NULL DEFAULT '0',
`ClaimMail` tinyint(4) NOT NULL DEFAULT '0',
`ReceiptMail` tinyint(4) NOT NULL DEFAULT '0',
`CancelMail` tinyint(4) NOT NULL DEFAULT '0',
`AddressMail` tinyint(4) NOT NULL DEFAULT '0',
`SoonPaymentMail` tinyint(4) NOT NULL DEFAULT '0',
`NotPaymentConfMail` tinyint(4) NOT NULL DEFAULT '0',
`CreditResultMail` tinyint(4) NOT NULL DEFAULT '0',
`AutoJournalDeliMethodId` bigint(20) NOT NULL DEFAULT '0',
`AutoJournalIncMode` int(11) NOT NULL DEFAULT '0',
`SitClass` tinyint(4) NOT NULL DEFAULT '0',
`T_OrderClass` tinyint(4) NOT NULL DEFAULT '0',
`PrintFormDK` int(11) DEFAULT NULL,
`PrintFormBS` int(11) DEFAULT NULL,
`FirstClaimKisanbiDelayDays` int(11) NOT NULL DEFAULT '0',
`KisanbiDelayDays` int(11) NOT NULL DEFAULT '0',
`RemindStopClass` int(11) NOT NULL DEFAULT '0',
`BarcodeLimitDays` int(11) NOT NULL DEFAULT '0',
`ReceiptAgentId` bigint(20) NOT NULL COMMENT '??????????????????ID',
`SubscriberCode` varchar(5) DEFAULT NULL COMMENT '????????????????????????',
`CombinedClaimChargeFeeFlg` tinyint(4) NOT NULL DEFAULT '0',
`YuchoMT` tinyint(4) NOT NULL DEFAULT '0',
`CreditJudgeMethod` tinyint(4) NOT NULL DEFAULT '0',
`AverageUnitPriceRate` float DEFAULT NULL,
`SelfBillingOemClaimFee` int(11) DEFAULT NULL,
`ClaimDisposeMail` tinyint(4) NOT NULL DEFAULT '0',
`MultiOrderCount` int(11) DEFAULT NULL,
`MultiOrderScore` int(11) DEFAULT NULL,
`NgChangeFlg` tinyint(4) NOT NULL DEFAULT '0',
`ShowNgReason` tinyint(4) NOT NULL DEFAULT '0',
`MuhoshoChangeDays` int(11) NOT NULL DEFAULT '7',
`JintecManualReqFlg` tinyint(4) NOT NULL DEFAULT '0',
`OutOfTermcheck` tinyint(4) NOT NULL DEFAULT '0',
`Telcheck` tinyint(4) NOT NULL DEFAULT '0',
`Addresscheck` tinyint(4) NOT NULL DEFAULT '0',
`PostalCodecheck` tinyint(4) NOT NULL DEFAULT '0',
`Ent_OrderIdcheck` tinyint(4) NOT NULL DEFAULT '0',
`EtcAutoArrivalFlg` tinyint(4) NOT NULL DEFAULT '0',
`EtcAutoArrivalNumber` varchar(255) DEFAULT NULL,
`JintecJudge` int(11) NOT NULL DEFAULT '0',
`JintecJudge0` int(11) NOT NULL DEFAULT '0',
`JintecJudge1` int(11) NOT NULL DEFAULT '0',
`JintecJudge2` int(11) NOT NULL DEFAULT '0',
`JintecJudge3` int(11) NOT NULL DEFAULT '0',
`JintecJudge4` int(11) NOT NULL DEFAULT '0',
`JintecJudge5` int(11) NOT NULL DEFAULT '0',
`JintecJudge6` int(11) NOT NULL DEFAULT '0',
`JintecJudge7` int(11) NOT NULL DEFAULT '0',
`JintecJudge8` int(11) NOT NULL DEFAULT '0',
`JintecJudge9` int(11) NOT NULL DEFAULT '0',
`JintecJudge10` tinyint(4) NOT NULL DEFAULT '0',
`PaymentAfterArrivalFlg` tinyint(4) NOT NULL DEFAULT '0',
`PaymentAfterArrivalName` varchar(255) COMMENT "???????????????????????????",
`MerchantId` varchar(10) DEFAULT NULL,
`ServiceId` varchar(10) DEFAULT NULL,
`HashKey` varchar(100) DEFAULT NULL,
`BasicId` varchar(10) DEFAULT NULL,
`BasicPw` varchar(100) DEFAULT NULL,
`ReceiptUsedFlg` tinyint(4) NOT NULL DEFAULT '0',
`ReceiptIssueProviso` varchar(255) DEFAULT NULL,
`MufjBarcodeUsedFlg` tinyint(4) NOT NULL DEFAULT '0' COMMENT '??????UF??????????????????????????????',
`MufjBarcodeSubscriberCode` varchar(10) DEFAULT NULL COMMENT '??????UF???????????????????????????????????????',
`SmallLogo` text,
`SpecificTransUrl` varchar(255) DEFAULT NULL,
`CSSettlementFeeRate` decimal(16,5) DEFAULT NULL,
`CSClaimFeeBS` int(11) DEFAULT NULL,
`CSClaimFeeDK` int(11) DEFAULT NULL,
`ReissueCount` int(11) DEFAULT NULL,
`ClaimAutoJournalIncMode` int(11) NOT NULL DEFAULT '0',
`ChatBotFlg` tinyint(4) NOT NULL DEFAULT '0',
`OtherSitesAuthCheckFlg` tinyint(4) NOT NULL DEFAULT '1',
`RegistId` int(11) DEFAULT NULL,
`UpdateDate` datetime DEFAULT NULL,
`UpdateId` int(11) DEFAULT NULL,
PRIMARY KEY (`SiteId`),
KEY `Idx_T_Site01` (`EnterpriseId`),
KEY `Idx_T_Site02` (`EtcAutoArrivalFlg`)
) ENGINE=InnoDB AUTO_INCREMENT=34730 DEFAULT CHARSET=utf8