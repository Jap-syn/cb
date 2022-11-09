-- T_ClaimControlリリース前日


-- 1.履歴テーブル作成
CREATE TABLE `T_ClaimControl_History` (
  `HistoryId` bigint unsigned NOT NULL AUTO_INCREMENT,
  `HistoryType` varchar(1) NOT NULL,
  `HistoryExecType` tinyint NOT NULL DEFAULT '0',

  `ClaimId` bigint(20) NOT NULL,
  `OrderSeq` bigint(20) DEFAULT NULL,
  `EntCustSeq` bigint(20) DEFAULT NULL,
  `ClaimDate` date DEFAULT NULL,
  `ClaimCpId` int(11) DEFAULT NULL,
  `ClaimPattern` int(11) NOT NULL DEFAULT '1',
  `LimitDate` date DEFAULT NULL,
  `UseAmountTotal` bigint(20) NOT NULL DEFAULT '0',
  `DamageDays` int(11) DEFAULT NULL,
  `DamageBaseDate` date DEFAULT NULL,
  `DamageInterestAmount` bigint(20) NOT NULL DEFAULT '0',
  `ClaimFee` bigint(20) NOT NULL DEFAULT '0',
  `AdditionalClaimFee` bigint(20) NOT NULL DEFAULT '0',
  `PrintedDate` datetime DEFAULT NULL,
  `ClaimAmount` bigint(20) NOT NULL DEFAULT '0',
  `ReceiptAmountTotal` bigint(20) NOT NULL DEFAULT '0',
  `RepayAmountTotal` bigint(20) NOT NULL DEFAULT '0',
  `SundryLossTotal` bigint(20) NOT NULL DEFAULT '0',
  `SundryIncomeTotal` bigint(20) NOT NULL DEFAULT '0',
  `ClaimedBalance` bigint(20) NOT NULL DEFAULT '0',
  `SundryLossTarget` tinyint(4) NOT NULL DEFAULT '0',
  `SundryIncomeTarget` tinyint(4) NOT NULL DEFAULT '0',
  `Clm_Count` int(11) DEFAULT NULL,
  `F_ClaimDate` date DEFAULT NULL,
  `F_OpId` int(11) DEFAULT NULL,
  `F_LimitDate` date DEFAULT NULL,
  `F_ClaimAmount` bigint(20) DEFAULT NULL,
  `Re1_ClaimAmount` bigint(20) DEFAULT NULL,
  `Re3_ClaimAmount` bigint(20) DEFAULT NULL,
  `AutoSundryStatus` tinyint(4) NOT NULL DEFAULT '0',
  `ReissueClass` tinyint(4) NOT NULL DEFAULT '0',
  `ReissueRequestDate` date DEFAULT NULL,
  `LastProcessDate` date DEFAULT NULL,
  `LastReceiptSeq` bigint(20) DEFAULT NULL,
  `MinClaimAmount` bigint(20) NOT NULL DEFAULT '0',
  `MinUseAmount` bigint(20) NOT NULL DEFAULT '0',
  `MinClaimFee` bigint(20) NOT NULL DEFAULT '0',
  `MinDamageInterestAmount` bigint(20) NOT NULL DEFAULT '0',
  `MinAdditionalClaimFee` bigint(20) NOT NULL DEFAULT '0',
  `CheckingClaimAmount` bigint(20) NOT NULL DEFAULT '0',
  `CheckingUseAmount` bigint(20) NOT NULL DEFAULT '0',
  `CheckingClaimFee` bigint(20) NOT NULL DEFAULT '0',
  `CheckingDamageInterestAmount` bigint(20) NOT NULL DEFAULT '0',
  `CheckingAdditionalClaimFee` bigint(20) NOT NULL DEFAULT '0',
  `BalanceClaimAmount` bigint(20) NOT NULL DEFAULT '0',
  `BalanceUseAmount` bigint(20) NOT NULL DEFAULT '0',
  `BalanceClaimFee` bigint(20) NOT NULL DEFAULT '0',
  `BalanceDamageInterestAmount` bigint(20) NOT NULL DEFAULT '0',
  `BalanceAdditionalClaimFee` bigint(20) NOT NULL DEFAULT '0',
  `MypageReissueClass` tinyint(4) NOT NULL DEFAULT '0',
  `MypageReissueRequestDate` date DEFAULT NULL,
  `MypageReissueDate` date DEFAULT NULL,
  `MypageReissueReClaimFee` bigint(20) NOT NULL DEFAULT '0',
  `CreditSettlementDecisionDate` date DEFAULT NULL,

  `CancelNoticePrintDate` DATE  DEFAULT NULL,
  `CancelNoticePrintStopStatus` TINYINT NOT NULL DEFAULT 1,
  `CreditTransferFlg` TINYINT NOT NULL DEFAULT 0,

  `ReissueCount` int(11) NOT NULL DEFAULT '0',
  `RegistDate` datetime DEFAULT NULL,
  `RegistId` int(11) DEFAULT NULL,
  `UpdateDate` datetime DEFAULT NULL,
  `UpdateId` int(11) DEFAULT NULL,
  `ValidFlg` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`HistoryId`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8

-- 2.履歴テーブル登録トリガ
DROP TRIGGER IF EXISTS `insert_T_ClaimControl_History`;
DELIMITER ;;
CREATE TRIGGER `insert_T_ClaimControl_History` AFTER INSERT ON `T_ClaimControl` FOR EACH ROW
 INSERT
   INTO `T_ClaimControl_History` (
        `HistoryType`
      , `ClaimId`
      , `OrderSeq`
      , `EntCustSeq`
      , `ClaimDate`
      , `ClaimCpId`
      , `ClaimPattern`
      , `LimitDate`
      , `UseAmountTotal`
      , `DamageDays`
      , `DamageBaseDate`
      , `DamageInterestAmount`
      , `ClaimFee`
      , `AdditionalClaimFee`
      , `PrintedDate`
      , `ClaimAmount`
      , `ReceiptAmountTotal`
      , `RepayAmountTotal`
      , `SundryLossTotal`
      , `SundryIncomeTotal`
      , `ClaimedBalance`
      , `SundryLossTarget`
      , `SundryIncomeTarget`
      , `Clm_Count`
      , `F_ClaimDate`
      , `F_OpId`
      , `F_LimitDate`
      , `F_ClaimAmount`
      , `Re1_ClaimAmount`
      , `Re3_ClaimAmount`
      , `AutoSundryStatus`
      , `ReissueClass`
      , `ReissueRequestDate`
      , `LastProcessDate`
      , `LastReceiptSeq`
      , `MinClaimAmount`
      , `MinUseAmount`
      , `MinClaimFee`
      , `MinDamageInterestAmount`
      , `MinAdditionalClaimFee`
      , `CheckingClaimAmount`
      , `CheckingUseAmount`
      , `CheckingClaimFee`
      , `CheckingDamageInterestAmount`
      , `CheckingAdditionalClaimFee`
      , `BalanceClaimAmount`
      , `BalanceUseAmount`
      , `BalanceClaimFee`
      , `BalanceDamageInterestAmount`
      , `BalanceAdditionalClaimFee`
      , `MypageReissueClass`
      , `MypageReissueRequestDate`
      , `MypageReissueDate`
      , `MypageReissueReClaimFee`
      , `CreditSettlementDecisionDate`
      , `ReissueCount`
      , `RegistDate`
      , `RegistId`
      , `UpdateDate`
      , `UpdateId`
      , `ValidFlg`
   ) VALUES (
        'I'
      , new.ClaimId
      , new.OrderSeq
      , new.EntCustSeq
      , new.ClaimDate
      , new.ClaimCpId
      , new.ClaimPattern
      , new.LimitDate
      , new.UseAmountTotal
      , new.DamageDays
      , new.DamageBaseDate
      , new.DamageInterestAmount
      , new.ClaimFee
      , new.AdditionalClaimFee
      , new.PrintedDate
      , new.ClaimAmount
      , new.ReceiptAmountTotal
      , new.RepayAmountTotal
      , new.SundryLossTotal
      , new.SundryIncomeTotal
      , new.ClaimedBalance
      , new.SundryLossTarget
      , new.SundryIncomeTarget
      , new.Clm_Count
      , new.F_ClaimDate
      , new.F_OpId
      , new.F_LimitDate
      , new.F_ClaimAmount
      , new.Re1_ClaimAmount
      , new.Re3_ClaimAmount
      , new.AutoSundryStatus
      , new.ReissueClass
      , new.ReissueRequestDate
      , new.LastProcessDate
      , new.LastReceiptSeq
      , new.MinClaimAmount
      , new.MinUseAmount
      , new.MinClaimFee
      , new.MinDamageInterestAmount
      , new.MinAdditionalClaimFee
      , new.CheckingClaimAmount
      , new.CheckingUseAmount
      , new.CheckingClaimFee
      , new.CheckingDamageInterestAmount
      , new.CheckingAdditionalClaimFee
      , new.BalanceClaimAmount
      , new.BalanceUseAmount
      , new.BalanceClaimFee
      , new.BalanceDamageInterestAmount
      , new.BalanceAdditionalClaimFee
      , new.MypageReissueClass
      , new.MypageReissueRequestDate
      , new.MypageReissueDate
      , new.MypageReissueReClaimFee
      , new.CreditSettlementDecisionDate
      , new.ReissueCount
      , new.RegistDate
      , new.RegistId
      , new.UpdateDate
      , new.UpdateId
      , new.ValidFlg
   )
;;
DELIMITER ;

-- 3.履歴テーブル更新トリガ
DROP TRIGGER IF EXISTS `update_T_ClaimControl_History`;
DELIMITER ;;
CREATE TRIGGER `update_T_ClaimControl_History` AFTER UPDATE ON `T_ClaimControl` FOR EACH ROW
 INSERT
   INTO `T_ClaimControl_History` (
        `HistoryType`
      , `ClaimId`
      , `OrderSeq`
      , `EntCustSeq`
      , `ClaimDate`
      , `ClaimCpId`
      , `ClaimPattern`
      , `LimitDate`
      , `UseAmountTotal`
      , `DamageDays`
      , `DamageBaseDate`
      , `DamageInterestAmount`
      , `ClaimFee`
      , `AdditionalClaimFee`
      , `PrintedDate`
      , `ClaimAmount`
      , `ReceiptAmountTotal`
      , `RepayAmountTotal`
      , `SundryLossTotal`
      , `SundryIncomeTotal`
      , `ClaimedBalance`
      , `SundryLossTarget`
      , `SundryIncomeTarget`
      , `Clm_Count`
      , `F_ClaimDate`
      , `F_OpId`
      , `F_LimitDate`
      , `F_ClaimAmount`
      , `Re1_ClaimAmount`
      , `Re3_ClaimAmount`
      , `AutoSundryStatus`
      , `ReissueClass`
      , `ReissueRequestDate`
      , `LastProcessDate`
      , `LastReceiptSeq`
      , `MinClaimAmount`
      , `MinUseAmount`
      , `MinClaimFee`
      , `MinDamageInterestAmount`
      , `MinAdditionalClaimFee`
      , `CheckingClaimAmount`
      , `CheckingUseAmount`
      , `CheckingClaimFee`
      , `CheckingDamageInterestAmount`
      , `CheckingAdditionalClaimFee`
      , `BalanceClaimAmount`
      , `BalanceUseAmount`
      , `BalanceClaimFee`
      , `BalanceDamageInterestAmount`
      , `BalanceAdditionalClaimFee`
      , `MypageReissueClass`
      , `MypageReissueRequestDate`
      , `MypageReissueDate`
      , `MypageReissueReClaimFee`
      , `CreditSettlementDecisionDate`
      , `ReissueCount`
      , `RegistDate`
      , `RegistId`
      , `UpdateDate`
      , `UpdateId`
      , `ValidFlg`
   ) VALUES (
        'U'
      , new.ClaimId
      , new.OrderSeq
      , new.EntCustSeq
      , new.ClaimDate
      , new.ClaimCpId
      , new.ClaimPattern
      , new.LimitDate
      , new.UseAmountTotal
      , new.DamageDays
      , new.DamageBaseDate
      , new.DamageInterestAmount
      , new.ClaimFee
      , new.AdditionalClaimFee
      , new.PrintedDate
      , new.ClaimAmount
      , new.ReceiptAmountTotal
      , new.RepayAmountTotal
      , new.SundryLossTotal
      , new.SundryIncomeTotal
      , new.ClaimedBalance
      , new.SundryLossTarget
      , new.SundryIncomeTarget
      , new.Clm_Count
      , new.F_ClaimDate
      , new.F_OpId
      , new.F_LimitDate
      , new.F_ClaimAmount
      , new.Re1_ClaimAmount
      , new.Re3_ClaimAmount
      , new.AutoSundryStatus
      , new.ReissueClass
      , new.ReissueRequestDate
      , new.LastProcessDate
      , new.LastReceiptSeq
      , new.MinClaimAmount
      , new.MinUseAmount
      , new.MinClaimFee
      , new.MinDamageInterestAmount
      , new.MinAdditionalClaimFee
      , new.CheckingClaimAmount
      , new.CheckingUseAmount
      , new.CheckingClaimFee
      , new.CheckingDamageInterestAmount
      , new.CheckingAdditionalClaimFee
      , new.BalanceClaimAmount
      , new.BalanceUseAmount
      , new.BalanceClaimFee
      , new.BalanceDamageInterestAmount
      , new.BalanceAdditionalClaimFee
      , new.MypageReissueClass
      , new.MypageReissueRequestDate
      , new.MypageReissueDate
      , new.MypageReissueReClaimFee
      , new.CreditSettlementDecisionDate
      , new.ReissueCount
      , new.RegistDate
      , new.RegistId
      , new.UpdateDate
      , new.UpdateId
      , new.ValidFlg
   )
;;
DELIMITER ;

-- 4.リリーステーブル作成
-- ★AUTO_INCREMENTの値を修正すること
CREATE TABLE `T_ClaimControl_Tmp` (
  `ClaimId` bigint(20) NOT NULL AUTO_INCREMENT,
  `OrderSeq` bigint(20) DEFAULT NULL,
  `EntCustSeq` bigint(20) DEFAULT NULL,
  `ClaimDate` date DEFAULT NULL,
  `ClaimCpId` int(11) DEFAULT NULL,
  `ClaimPattern` int(11) NOT NULL DEFAULT '1',
  `LimitDate` date DEFAULT NULL,
  `UseAmountTotal` bigint(20) NOT NULL DEFAULT '0',
  `DamageDays` int(11) DEFAULT NULL,
  `DamageBaseDate` date DEFAULT NULL,
  `DamageInterestAmount` bigint(20) NOT NULL DEFAULT '0',
  `ClaimFee` bigint(20) NOT NULL DEFAULT '0',
  `AdditionalClaimFee` bigint(20) NOT NULL DEFAULT '0',
  `PrintedDate` datetime DEFAULT NULL,
  `ClaimAmount` bigint(20) NOT NULL DEFAULT '0',
  `ReceiptAmountTotal` bigint(20) NOT NULL DEFAULT '0',
  `RepayAmountTotal` bigint(20) NOT NULL DEFAULT '0',
  `SundryLossTotal` bigint(20) NOT NULL DEFAULT '0',
  `SundryIncomeTotal` bigint(20) NOT NULL DEFAULT '0',
  `ClaimedBalance` bigint(20) NOT NULL DEFAULT '0',
  `SundryLossTarget` tinyint(4) NOT NULL DEFAULT '0',
  `SundryIncomeTarget` tinyint(4) NOT NULL DEFAULT '0',
  `Clm_Count` int(11) DEFAULT NULL,
  `F_ClaimDate` date DEFAULT NULL,
  `F_OpId` int(11) DEFAULT NULL,
  `F_LimitDate` date DEFAULT NULL,
  `F_ClaimAmount` bigint(20) DEFAULT NULL,
  `Re1_ClaimAmount` bigint(20) DEFAULT NULL,
  `Re3_ClaimAmount` bigint(20) DEFAULT NULL,
  `AutoSundryStatus` tinyint(4) NOT NULL DEFAULT '0',
  `ReissueClass` tinyint(4) NOT NULL DEFAULT '0',
  `ReissueRequestDate` date DEFAULT NULL,
  `LastProcessDate` date DEFAULT NULL,
  `LastReceiptSeq` bigint(20) DEFAULT NULL,
  `MinClaimAmount` bigint(20) NOT NULL DEFAULT '0',
  `MinUseAmount` bigint(20) NOT NULL DEFAULT '0',
  `MinClaimFee` bigint(20) NOT NULL DEFAULT '0',
  `MinDamageInterestAmount` bigint(20) NOT NULL DEFAULT '0',
  `MinAdditionalClaimFee` bigint(20) NOT NULL DEFAULT '0',
  `CheckingClaimAmount` bigint(20) NOT NULL DEFAULT '0',
  `CheckingUseAmount` bigint(20) NOT NULL DEFAULT '0',
  `CheckingClaimFee` bigint(20) NOT NULL DEFAULT '0',
  `CheckingDamageInterestAmount` bigint(20) NOT NULL DEFAULT '0',
  `CheckingAdditionalClaimFee` bigint(20) NOT NULL DEFAULT '0',
  `BalanceClaimAmount` bigint(20) NOT NULL DEFAULT '0',
  `BalanceUseAmount` bigint(20) NOT NULL DEFAULT '0',
  `BalanceClaimFee` bigint(20) NOT NULL DEFAULT '0',
  `BalanceDamageInterestAmount` bigint(20) NOT NULL DEFAULT '0',
  `BalanceAdditionalClaimFee` bigint(20) NOT NULL DEFAULT '0',
  `MypageReissueClass` tinyint(4) NOT NULL DEFAULT '0',
  `MypageReissueRequestDate` date DEFAULT NULL,
  `MypageReissueDate` date DEFAULT NULL,
  `MypageReissueReClaimFee` bigint(20) NOT NULL DEFAULT '0',
  `CreditSettlementDecisionDate` date DEFAULT NULL,

  `CancelNoticePrintDate` DATE  DEFAULT NULL,
  `CancelNoticePrintStopStatus` TINYINT NOT NULL DEFAULT 1,
  `CreditTransferFlg` TINYINT NOT NULL DEFAULT 0,

  `ReissueCount` int(11) NOT NULL DEFAULT '0',
  `RegistDate` datetime DEFAULT NULL,
  `RegistId` int(11) DEFAULT NULL,
  `UpdateDate` datetime DEFAULT NULL,
  `UpdateId` int(11) DEFAULT NULL,
  `ValidFlg` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`ClaimId`),
  KEY `Idx_T_ClaimControl01` (`OrderSeq`),
  KEY `Idx_T_ClaimControl02` (`EntCustSeq`),
  KEY `Idx_T_ClaimControl03` (`BalanceClaimAmount`),
  KEY `Idx_T_ClaimControl04` (`F_LimitDate`),
  KEY `Idx_T_ClaimControl05` (`ClaimedBalance`)
) ENGINE=InnoDB AUTO_INCREMENT=52291545 DEFAULT CHARSET=utf8

-- 5.リリーステーブルデータ移行
-- 時刻: 4480.930ms　74分
INSERT INTO T_ClaimControl_Tmp(
        ClaimId
      , OrderSeq
      , EntCustSeq
      , ClaimDate
      , ClaimCpId
      , ClaimPattern
      , LimitDate
      , UseAmountTotal
      , DamageDays
      , DamageBaseDate
      , DamageInterestAmount
      , ClaimFee
      , AdditionalClaimFee
      , PrintedDate
      , ClaimAmount
      , ReceiptAmountTotal
      , RepayAmountTotal
      , SundryLossTotal
      , SundryIncomeTotal
      , ClaimedBalance
      , SundryLossTarget
      , SundryIncomeTarget
      , Clm_Count
      , F_ClaimDate
      , F_OpId
      , F_LimitDate
      , F_ClaimAmount
      , Re1_ClaimAmount
      , Re3_ClaimAmount
      , AutoSundryStatus
      , ReissueClass
      , ReissueRequestDate
      , LastProcessDate
      , LastReceiptSeq
      , MinClaimAmount
      , MinUseAmount
      , MinClaimFee
      , MinDamageInterestAmount
      , MinAdditionalClaimFee
      , CheckingClaimAmount
      , CheckingUseAmount
      , CheckingClaimFee
      , CheckingDamageInterestAmount
      , CheckingAdditionalClaimFee
      , BalanceClaimAmount
      , BalanceUseAmount
      , BalanceClaimFee
      , BalanceDamageInterestAmount
      , BalanceAdditionalClaimFee
      , MypageReissueClass
      , MypageReissueRequestDate
      , MypageReissueDate
      , MypageReissueReClaimFee
      , CreditSettlementDecisionDate
      , ReissueCount
      , RegistDate
      , RegistId
      , UpdateDate
      , UpdateId
      , ValidFlg
) SELECT 
        ClaimId
      , OrderSeq
      , EntCustSeq
      , ClaimDate
      , ClaimCpId
      , ClaimPattern
      , LimitDate
      , UseAmountTotal
      , DamageDays
      , DamageBaseDate
      , DamageInterestAmount
      , ClaimFee
      , AdditionalClaimFee
      , PrintedDate
      , ClaimAmount
      , ReceiptAmountTotal
      , RepayAmountTotal
      , SundryLossTotal
      , SundryIncomeTotal
      , ClaimedBalance
      , SundryLossTarget
      , SundryIncomeTarget
      , Clm_Count
      , F_ClaimDate
      , F_OpId
      , F_LimitDate
      , F_ClaimAmount
      , Re1_ClaimAmount
      , Re3_ClaimAmount
      , AutoSundryStatus
      , ReissueClass
      , ReissueRequestDate
      , LastProcessDate
      , LastReceiptSeq
      , MinClaimAmount
      , MinUseAmount
      , MinClaimFee
      , MinDamageInterestAmount
      , MinAdditionalClaimFee
      , CheckingClaimAmount
      , CheckingUseAmount
      , CheckingClaimFee
      , CheckingDamageInterestAmount
      , CheckingAdditionalClaimFee
      , BalanceClaimAmount
      , BalanceUseAmount
      , BalanceClaimFee
      , BalanceDamageInterestAmount
      , BalanceAdditionalClaimFee
      , MypageReissueClass
      , MypageReissueRequestDate
      , MypageReissueDate
      , MypageReissueReClaimFee
      , CreditSettlementDecisionDate
      , ReissueCount
      , RegistDate
      , RegistId
      , UpdateDate
      , UpdateId
      , ValidFlg
FROM T_ClaimControl
;

-- 6.リリーステーブル登録トリガ
DROP TRIGGER IF EXISTS `insert_T_ClaimControl_Tmp`;
DELIMITER ;;
CREATE TRIGGER `insert_T_ClaimControl_Tmp` AFTER INSERT ON `T_ClaimControl` FOR EACH ROW
 INSERT
   INTO `T_ClaimControl_Tmp` (
        `ClaimId`
      , `OrderSeq`
      , `EntCustSeq`
      , `ClaimDate`
      , `ClaimCpId`
      , `ClaimPattern`
      , `LimitDate`
      , `UseAmountTotal`
      , `DamageDays`
      , `DamageBaseDate`
      , `DamageInterestAmount`
      , `ClaimFee`
      , `AdditionalClaimFee`
      , `PrintedDate`
      , `ClaimAmount`
      , `ReceiptAmountTotal`
      , `RepayAmountTotal`
      , `SundryLossTotal`
      , `SundryIncomeTotal`
      , `ClaimedBalance`
      , `SundryLossTarget`
      , `SundryIncomeTarget`
      , `Clm_Count`
      , `F_ClaimDate`
      , `F_OpId`
      , `F_LimitDate`
      , `F_ClaimAmount`
      , `Re1_ClaimAmount`
      , `Re3_ClaimAmount`
      , `AutoSundryStatus`
      , `ReissueClass`
      , `ReissueRequestDate`
      , `LastProcessDate`
      , `LastReceiptSeq`
      , `MinClaimAmount`
      , `MinUseAmount`
      , `MinClaimFee`
      , `MinDamageInterestAmount`
      , `MinAdditionalClaimFee`
      , `CheckingClaimAmount`
      , `CheckingUseAmount`
      , `CheckingClaimFee`
      , `CheckingDamageInterestAmount`
      , `CheckingAdditionalClaimFee`
      , `BalanceClaimAmount`
      , `BalanceUseAmount`
      , `BalanceClaimFee`
      , `BalanceDamageInterestAmount`
      , `BalanceAdditionalClaimFee`
      , `MypageReissueClass`
      , `MypageReissueRequestDate`
      , `MypageReissueDate`
      , `MypageReissueReClaimFee`
      , `CreditSettlementDecisionDate`
      , `ReissueCount`
      , `RegistDate`
      , `RegistId`
      , `UpdateDate`
      , `UpdateId`
      , `ValidFlg`
   ) VALUES (
        new.ClaimId
      , new.OrderSeq
      , new.EntCustSeq
      , new.ClaimDate
      , new.ClaimCpId
      , new.ClaimPattern
      , new.LimitDate
      , new.UseAmountTotal
      , new.DamageDays
      , new.DamageBaseDate
      , new.DamageInterestAmount
      , new.ClaimFee
      , new.AdditionalClaimFee
      , new.PrintedDate
      , new.ClaimAmount
      , new.ReceiptAmountTotal
      , new.RepayAmountTotal
      , new.SundryLossTotal
      , new.SundryIncomeTotal
      , new.ClaimedBalance
      , new.SundryLossTarget
      , new.SundryIncomeTarget
      , new.Clm_Count
      , new.F_ClaimDate
      , new.F_OpId
      , new.F_LimitDate
      , new.F_ClaimAmount
      , new.Re1_ClaimAmount
      , new.Re3_ClaimAmount
      , new.AutoSundryStatus
      , new.ReissueClass
      , new.ReissueRequestDate
      , new.LastProcessDate
      , new.LastReceiptSeq
      , new.MinClaimAmount
      , new.MinUseAmount
      , new.MinClaimFee
      , new.MinDamageInterestAmount
      , new.MinAdditionalClaimFee
      , new.CheckingClaimAmount
      , new.CheckingUseAmount
      , new.CheckingClaimFee
      , new.CheckingDamageInterestAmount
      , new.CheckingAdditionalClaimFee
      , new.BalanceClaimAmount
      , new.BalanceUseAmount
      , new.BalanceClaimFee
      , new.BalanceDamageInterestAmount
      , new.BalanceAdditionalClaimFee
      , new.MypageReissueClass
      , new.MypageReissueRequestDate
      , new.MypageReissueDate
      , new.MypageReissueReClaimFee
      , new.CreditSettlementDecisionDate
      , new.ReissueCount
      , new.RegistDate
      , new.RegistId
      , new.UpdateDate
      , new.UpdateId
      , new.ValidFlg
   )
;;
DELIMITER ;

-- 7.リリーステーブル更新トリガ
DROP TRIGGER IF EXISTS `update_T_ClaimControl_Tmp`;
DELIMITER ;;
CREATE TRIGGER `update_T_ClaimControl_Tmp` AFTER UPDATE ON `T_ClaimControl` FOR EACH ROW
UPDATE T_ClaimControl_Tmp SET
        ClaimId = new.ClaimId
      , OrderSeq = new.OrderSeq
      , EntCustSeq = new.EntCustSeq
      , ClaimDate = new.ClaimDate
      , ClaimCpId = new.ClaimCpId
      , ClaimPattern = new.ClaimPattern
      , LimitDate = new.LimitDate
      , UseAmountTotal = new.UseAmountTotal
      , DamageDays = new.DamageDays
      , DamageBaseDate = new.DamageBaseDate
      , DamageInterestAmount = new.DamageInterestAmount
      , ClaimFee = new.ClaimFee
      , AdditionalClaimFee = new.AdditionalClaimFee
      , PrintedDate = new.PrintedDate
      , ClaimAmount = new.ClaimAmount
      , ReceiptAmountTotal = new.ReceiptAmountTotal
      , RepayAmountTotal = new.RepayAmountTotal
      , SundryLossTotal = new.SundryLossTotal
      , SundryIncomeTotal = new.SundryIncomeTotal
      , ClaimedBalance = new.ClaimedBalance
      , SundryLossTarget = new.SundryLossTarget
      , SundryIncomeTarget = new.SundryIncomeTarget
      , Clm_Count = new.Clm_Count
      , F_ClaimDate = new.F_ClaimDate
      , F_OpId = new.F_OpId
      , F_LimitDate = new.F_LimitDate
      , F_ClaimAmount = new.F_ClaimAmount
      , Re1_ClaimAmount = new.Re1_ClaimAmount
      , Re3_ClaimAmount = new.Re3_ClaimAmount
      , AutoSundryStatus = new.AutoSundryStatus
      , ReissueClass = new.ReissueClass
      , ReissueRequestDate = new.ReissueRequestDate
      , LastProcessDate = new.LastProcessDate
      , LastReceiptSeq = new.LastReceiptSeq
      , MinClaimAmount = new.MinClaimAmount
      , MinUseAmount = new.MinUseAmount
      , MinClaimFee = new.MinClaimFee
      , MinDamageInterestAmount = new.MinDamageInterestAmount
      , MinAdditionalClaimFee = new.MinAdditionalClaimFee
      , CheckingClaimAmount = new.CheckingClaimAmount
      , CheckingUseAmount = new.CheckingUseAmount
      , CheckingClaimFee = new.CheckingClaimFee
      , CheckingDamageInterestAmount = new.CheckingDamageInterestAmount
      , CheckingAdditionalClaimFee = new.CheckingAdditionalClaimFee
      , BalanceClaimAmount = new.BalanceClaimAmount
      , BalanceUseAmount = new.BalanceUseAmount
      , BalanceClaimFee = new.BalanceClaimFee
      , BalanceDamageInterestAmount = new.BalanceDamageInterestAmount
      , BalanceAdditionalClaimFee = new.BalanceAdditionalClaimFee
      , MypageReissueClass = new.MypageReissueClass
      , MypageReissueRequestDate = new.MypageReissueRequestDate
      , MypageReissueDate = new.MypageReissueDate
      , MypageReissueReClaimFee = new.MypageReissueReClaimFee
      , CreditSettlementDecisionDate = new.CreditSettlementDecisionDate
      , ReissueCount = new.ReissueCount
      , RegistDate = new.RegistDate
      , RegistId = new.RegistId
      , UpdateDate = new.UpdateDate
      , UpdateId = new.UpdateId
      , ValidFlg = new.ValidFlg
WHERE ClaimId = old.ClaimId
;;
DELIMITER ;

-- 8.履歴テーブル登録トリガ修正
DROP TRIGGER IF EXISTS `insert_T_ClaimControl_History`;
DELIMITER ;;
CREATE TRIGGER `insert_T_ClaimControl_History` AFTER INSERT ON `T_ClaimControl` FOR EACH ROW
 INSERT
   INTO `T_ClaimControl_History` (
        `HistoryType`
      , `HistoryExecType`
      , `ClaimId`
      , `OrderSeq`
      , `EntCustSeq`
      , `ClaimDate`
      , `ClaimCpId`
      , `ClaimPattern`
      , `LimitDate`
      , `UseAmountTotal`
      , `DamageDays`
      , `DamageBaseDate`
      , `DamageInterestAmount`
      , `ClaimFee`
      , `AdditionalClaimFee`
      , `PrintedDate`
      , `ClaimAmount`
      , `ReceiptAmountTotal`
      , `RepayAmountTotal`
      , `SundryLossTotal`
      , `SundryIncomeTotal`
      , `ClaimedBalance`
      , `SundryLossTarget`
      , `SundryIncomeTarget`
      , `Clm_Count`
      , `F_ClaimDate`
      , `F_OpId`
      , `F_LimitDate`
      , `F_ClaimAmount`
      , `Re1_ClaimAmount`
      , `Re3_ClaimAmount`
      , `AutoSundryStatus`
      , `ReissueClass`
      , `ReissueRequestDate`
      , `LastProcessDate`
      , `LastReceiptSeq`
      , `MinClaimAmount`
      , `MinUseAmount`
      , `MinClaimFee`
      , `MinDamageInterestAmount`
      , `MinAdditionalClaimFee`
      , `CheckingClaimAmount`
      , `CheckingUseAmount`
      , `CheckingClaimFee`
      , `CheckingDamageInterestAmount`
      , `CheckingAdditionalClaimFee`
      , `BalanceClaimAmount`
      , `BalanceUseAmount`
      , `BalanceClaimFee`
      , `BalanceDamageInterestAmount`
      , `BalanceAdditionalClaimFee`
      , `MypageReissueClass`
      , `MypageReissueRequestDate`
      , `MypageReissueDate`
      , `MypageReissueReClaimFee`
      , `CreditSettlementDecisionDate`
      , `ReissueCount`
      , `RegistDate`
      , `RegistId`
      , `UpdateDate`
      , `UpdateId`
      , `ValidFlg`
   ) VALUES (
        'I'
      , 8
      , new.ClaimId
      , new.OrderSeq
      , new.EntCustSeq
      , new.ClaimDate
      , new.ClaimCpId
      , new.ClaimPattern
      , new.LimitDate
      , new.UseAmountTotal
      , new.DamageDays
      , new.DamageBaseDate
      , new.DamageInterestAmount
      , new.ClaimFee
      , new.AdditionalClaimFee
      , new.PrintedDate
      , new.ClaimAmount
      , new.ReceiptAmountTotal
      , new.RepayAmountTotal
      , new.SundryLossTotal
      , new.SundryIncomeTotal
      , new.ClaimedBalance
      , new.SundryLossTarget
      , new.SundryIncomeTarget
      , new.Clm_Count
      , new.F_ClaimDate
      , new.F_OpId
      , new.F_LimitDate
      , new.F_ClaimAmount
      , new.Re1_ClaimAmount
      , new.Re3_ClaimAmount
      , new.AutoSundryStatus
      , new.ReissueClass
      , new.ReissueRequestDate
      , new.LastProcessDate
      , new.LastReceiptSeq
      , new.MinClaimAmount
      , new.MinUseAmount
      , new.MinClaimFee
      , new.MinDamageInterestAmount
      , new.MinAdditionalClaimFee
      , new.CheckingClaimAmount
      , new.CheckingUseAmount
      , new.CheckingClaimFee
      , new.CheckingDamageInterestAmount
      , new.CheckingAdditionalClaimFee
      , new.BalanceClaimAmount
      , new.BalanceUseAmount
      , new.BalanceClaimFee
      , new.BalanceDamageInterestAmount
      , new.BalanceAdditionalClaimFee
      , new.MypageReissueClass
      , new.MypageReissueRequestDate
      , new.MypageReissueDate
      , new.MypageReissueReClaimFee
      , new.CreditSettlementDecisionDate
      , new.ReissueCount
      , new.RegistDate
      , new.RegistId
      , new.UpdateDate
      , new.UpdateId
      , new.ValidFlg
   )
;;
DELIMITER ;

-- 9.履歴テーブル更新トリガ修正
DROP TRIGGER IF EXISTS `update_T_ClaimControl_History`;
DELIMITER ;;
CREATE TRIGGER `update_T_ClaimControl_History` AFTER UPDATE ON `T_ClaimControl` FOR EACH ROW
 INSERT
   INTO `T_ClaimControl_History` (
        `HistoryType`
      , `HistoryExecType`
      , `ClaimId`
      , `OrderSeq`
      , `EntCustSeq`
      , `ClaimDate`
      , `ClaimCpId`
      , `ClaimPattern`
      , `LimitDate`
      , `UseAmountTotal`
      , `DamageDays`
      , `DamageBaseDate`
      , `DamageInterestAmount`
      , `ClaimFee`
      , `AdditionalClaimFee`
      , `PrintedDate`
      , `ClaimAmount`
      , `ReceiptAmountTotal`
      , `RepayAmountTotal`
      , `SundryLossTotal`
      , `SundryIncomeTotal`
      , `ClaimedBalance`
      , `SundryLossTarget`
      , `SundryIncomeTarget`
      , `Clm_Count`
      , `F_ClaimDate`
      , `F_OpId`
      , `F_LimitDate`
      , `F_ClaimAmount`
      , `Re1_ClaimAmount`
      , `Re3_ClaimAmount`
      , `AutoSundryStatus`
      , `ReissueClass`
      , `ReissueRequestDate`
      , `LastProcessDate`
      , `LastReceiptSeq`
      , `MinClaimAmount`
      , `MinUseAmount`
      , `MinClaimFee`
      , `MinDamageInterestAmount`
      , `MinAdditionalClaimFee`
      , `CheckingClaimAmount`
      , `CheckingUseAmount`
      , `CheckingClaimFee`
      , `CheckingDamageInterestAmount`
      , `CheckingAdditionalClaimFee`
      , `BalanceClaimAmount`
      , `BalanceUseAmount`
      , `BalanceClaimFee`
      , `BalanceDamageInterestAmount`
      , `BalanceAdditionalClaimFee`
      , `MypageReissueClass`
      , `MypageReissueRequestDate`
      , `MypageReissueDate`
      , `MypageReissueReClaimFee`
      , `CreditSettlementDecisionDate`
      , `ReissueCount`
      , `RegistDate`
      , `RegistId`
      , `UpdateDate`
      , `UpdateId`
      , `ValidFlg`
   ) VALUES (
        'U'
      , 8
      , new.ClaimId
      , new.OrderSeq
      , new.EntCustSeq
      , new.ClaimDate
      , new.ClaimCpId
      , new.ClaimPattern
      , new.LimitDate
      , new.UseAmountTotal
      , new.DamageDays
      , new.DamageBaseDate
      , new.DamageInterestAmount
      , new.ClaimFee
      , new.AdditionalClaimFee
      , new.PrintedDate
      , new.ClaimAmount
      , new.ReceiptAmountTotal
      , new.RepayAmountTotal
      , new.SundryLossTotal
      , new.SundryIncomeTotal
      , new.ClaimedBalance
      , new.SundryLossTarget
      , new.SundryIncomeTarget
      , new.Clm_Count
      , new.F_ClaimDate
      , new.F_OpId
      , new.F_LimitDate
      , new.F_ClaimAmount
      , new.Re1_ClaimAmount
      , new.Re3_ClaimAmount
      , new.AutoSundryStatus
      , new.ReissueClass
      , new.ReissueRequestDate
      , new.LastProcessDate
      , new.LastReceiptSeq
      , new.MinClaimAmount
      , new.MinUseAmount
      , new.MinClaimFee
      , new.MinDamageInterestAmount
      , new.MinAdditionalClaimFee
      , new.CheckingClaimAmount
      , new.CheckingUseAmount
      , new.CheckingClaimFee
      , new.CheckingDamageInterestAmount
      , new.CheckingAdditionalClaimFee
      , new.BalanceClaimAmount
      , new.BalanceUseAmount
      , new.BalanceClaimFee
      , new.BalanceDamageInterestAmount
      , new.BalanceAdditionalClaimFee
      , new.MypageReissueClass
      , new.MypageReissueRequestDate
      , new.MypageReissueDate
      , new.MypageReissueReClaimFee
      , new.CreditSettlementDecisionDate
      , new.ReissueCount
      , new.RegistDate
      , new.RegistId
      , new.UpdateDate
      , new.UpdateId
      , new.ValidFlg
   )
;;
DELIMITER ;



-- T_ClaimHistoryリリース前日
-- 1.履歴テーブル作成
CREATE TABLE `T_ClaimHistory_History` (
  `HistoryId` bigint unsigned NOT NULL AUTO_INCREMENT,
  `HistoryType` varchar(1) NOT NULL,
  `HistoryExecType` tinyint NOT NULL DEFAULT '0',

  `Seq` bigint(20) NOT NULL,
  `OrderSeq` bigint(20) DEFAULT NULL,
  `ClaimSeq` int(11) DEFAULT NULL,
  `ClaimDate` date DEFAULT NULL,
  `ClaimCpId` int(11) DEFAULT NULL,
  `ClaimPattern` int(11) DEFAULT NULL,
  `LimitDate` date DEFAULT NULL,
  `DamageDays` int(11) DEFAULT NULL,
  `DamageBaseDate` date DEFAULT NULL,
  `DamageInterestAmount` bigint(20) DEFAULT NULL,
  `ClaimFee` bigint(20) DEFAULT NULL,
  `AdditionalClaimFee` bigint(20) DEFAULT NULL,
  `PrintedFlg` int(11) DEFAULT NULL,
  `PrintedDate` datetime DEFAULT NULL,
  `MailFlg` int(11) DEFAULT NULL,
  `CreditTransferMailFlg` int(11) DEFAULT NULL,
  `ClaimFileOutputClass` int(11) DEFAULT NULL,
  `EnterpriseBillingCode` varchar(255) DEFAULT NULL,
  `ClaimAmount` bigint(20) DEFAULT NULL,
  `ClaimId` bigint(20) DEFAULT NULL,
  `ReissueClass` tinyint(4) NOT NULL DEFAULT '0',
  `ReissueRequestDate` date DEFAULT NULL,
  `PrintedStatus` tinyint(4) NOT NULL DEFAULT '0',
  `MailRetryCount` tinyint(4) NOT NULL DEFAULT '0',
  `CreditTransferRequestStatus` int(11) DEFAULT NULL,
  `CreditMailRetryCount` tinyint(4) DEFAULT '0',

  `CreditTransferMethod` INT NOT NULL DEFAULT 0,
  `ZeroAmountClaimMailFlg` TINYINT NOT NULL DEFAULT 9,

  `PayeasyFee` int(11) DEFAULT '0',
  `RegistDate` datetime DEFAULT NULL,
  `RegistId` int(11) DEFAULT NULL,
  `UpdateDate` datetime DEFAULT NULL,
  `UpdateId` int(11) DEFAULT NULL,
  `ValidFlg` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`HistoryId`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8

-- 2.履歴テーブル登録トリガ
DROP TRIGGER IF EXISTS `insert_T_ClaimHistory`;
DELIMITER ;;
CREATE TRIGGER `insert_T_ClaimHistory` AFTER INSERT ON `T_ClaimHistory` FOR EACH ROW
 INSERT
   INTO `T_ClaimHistory_History` (
        `HistoryType`
      , `Seq`
      , `OrderSeq`
      , `ClaimSeq`
      , `ClaimDate`
      , `ClaimCpId`
      , `ClaimPattern`
      , `LimitDate`
      , `DamageDays`
      , `DamageBaseDate`
      , `DamageInterestAmount`
      , `ClaimFee`
      , `AdditionalClaimFee`
      , `PrintedFlg`
      , `PrintedDate`
      , `MailFlg`
      , `CreditTransferMailFlg`
      , `ClaimFileOutputClass`
      , `EnterpriseBillingCode`
      , `ClaimAmount`
      , `ClaimId`
      , `ReissueClass`
      , `ReissueRequestDate`
      , `PrintedStatus`
      , `MailRetryCount`
      , `CreditTransferRequestStatus`
      , `CreditMailRetryCount`
      , `PayeasyFee`
      , `RegistDate`
      , `RegistId`
      , `UpdateDate`
      , `UpdateId`
      , `ValidFlg`
   ) VALUES (
        'I'
      , new.Seq
      , new.OrderSeq
      , new.ClaimSeq
      , new.ClaimDate
      , new.ClaimCpId
      , new.ClaimPattern
      , new.LimitDate
      , new.DamageDays
      , new.DamageBaseDate
      , new.DamageInterestAmount
      , new.ClaimFee
      , new.AdditionalClaimFee
      , new.PrintedFlg
      , new.PrintedDate
      , new.MailFlg
      , new.CreditTransferMailFlg
      , new.ClaimFileOutputClass
      , new.EnterpriseBillingCode
      , new.ClaimAmount
      , new.ClaimId
      , new.ReissueClass
      , new.ReissueRequestDate
      , new.PrintedStatus
      , new.MailRetryCount
      , new.CreditTransferRequestStatus
      , new.CreditMailRetryCount
      , new.PayeasyFee
      , new.RegistDate
      , new.RegistId
      , new.UpdateDate
      , new.UpdateId
      , new.ValidFlg
   )
;;
DELIMITER ;

-- 3.履歴テーブル更新トリガ
DROP TRIGGER IF EXISTS `update_T_ClaimHistory`;
DELIMITER ;;
CREATE TRIGGER `update_T_ClaimHistory` AFTER UPDATE ON `T_ClaimHistory` FOR EACH ROW
 INSERT
   INTO `T_ClaimHistory_History` (
        `HistoryType`
      , `Seq`
      , `OrderSeq`
      , `ClaimSeq`
      , `ClaimDate`
      , `ClaimCpId`
      , `ClaimPattern`
      , `LimitDate`
      , `DamageDays`
      , `DamageBaseDate`
      , `DamageInterestAmount`
      , `ClaimFee`
      , `AdditionalClaimFee`
      , `PrintedFlg`
      , `PrintedDate`
      , `MailFlg`
      , `CreditTransferMailFlg`
      , `ClaimFileOutputClass`
      , `EnterpriseBillingCode`
      , `ClaimAmount`
      , `ClaimId`
      , `ReissueClass`
      , `ReissueRequestDate`
      , `PrintedStatus`
      , `MailRetryCount`
      , `CreditTransferRequestStatus`
      , `CreditMailRetryCount`
      , `PayeasyFee`
      , `RegistDate`
      , `RegistId`
      , `UpdateDate`
      , `UpdateId`
      , `ValidFlg`
   ) VALUES (
        'U'
      , new.Seq
      , new.OrderSeq
      , new.ClaimSeq
      , new.ClaimDate
      , new.ClaimCpId
      , new.ClaimPattern
      , new.LimitDate
      , new.DamageDays
      , new.DamageBaseDate
      , new.DamageInterestAmount
      , new.ClaimFee
      , new.AdditionalClaimFee
      , new.PrintedFlg
      , new.PrintedDate
      , new.MailFlg
      , new.CreditTransferMailFlg
      , new.ClaimFileOutputClass
      , new.EnterpriseBillingCode
      , new.ClaimAmount
      , new.ClaimId
      , new.ReissueClass
      , new.ReissueRequestDate
      , new.PrintedStatus
      , new.MailRetryCount
      , new.CreditTransferRequestStatus
      , new.CreditMailRetryCount
      , new.PayeasyFee
      , new.RegistDate
      , new.RegistId
      , new.UpdateDate
      , new.UpdateId
      , new.ValidFlg
   )
;;
DELIMITER ;

-- 4.リリーステーブル作成
-- ★AUTO_INCREMENTの値を修正すること
CREATE TABLE `T_ClaimHistory_Tmp` (
  `Seq` bigint(20) NOT NULL AUTO_INCREMENT,
  `OrderSeq` bigint(20) DEFAULT NULL,
  `ClaimSeq` int(11) DEFAULT NULL,
  `ClaimDate` date DEFAULT NULL,
  `ClaimCpId` int(11) DEFAULT NULL,
  `ClaimPattern` int(11) DEFAULT NULL,
  `LimitDate` date DEFAULT NULL,
  `DamageDays` int(11) DEFAULT NULL,
  `DamageBaseDate` date DEFAULT NULL,
  `DamageInterestAmount` bigint(20) DEFAULT NULL,
  `ClaimFee` bigint(20) DEFAULT NULL,
  `AdditionalClaimFee` bigint(20) DEFAULT NULL,
  `PrintedFlg` int(11) DEFAULT NULL,
  `PrintedDate` datetime DEFAULT NULL,
  `MailFlg` int(11) DEFAULT NULL,
  `CreditTransferMailFlg` int(11) DEFAULT NULL,
  `ClaimFileOutputClass` int(11) DEFAULT NULL,
  `EnterpriseBillingCode` varchar(255) DEFAULT NULL,
  `ClaimAmount` bigint(20) DEFAULT NULL,
  `ClaimId` bigint(20) DEFAULT NULL,
  `ReissueClass` tinyint(4) NOT NULL DEFAULT '0',
  `ReissueRequestDate` date DEFAULT NULL,
  `PrintedStatus` tinyint(4) NOT NULL DEFAULT '0',
  `MailRetryCount` tinyint(4) NOT NULL DEFAULT '0',
  `CreditTransferRequestStatus` int(11) DEFAULT NULL,
  `CreditMailRetryCount` tinyint(4) DEFAULT '0',

  `CreditTransferMethod` INT NOT NULL DEFAULT 0,
  `ZeroAmountClaimMailFlg` TINYINT NOT NULL DEFAULT 9,

  `PayeasyFee` int(11) DEFAULT '0',
  `RegistDate` datetime DEFAULT NULL,
  `RegistId` int(11) DEFAULT NULL,
  `UpdateDate` datetime DEFAULT NULL,
  `UpdateId` int(11) DEFAULT NULL,
  `ValidFlg` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`Seq`),
  KEY `Idx_T_ClaimHistory01` (`OrderSeq`),
  KEY `Idx_T_ClaimHistory02` (`ClaimSeq`),
  KEY `Idx_T_ClaimHistory03` (`ClaimPattern`,`PrintedFlg`),
  KEY `Idx_T_ClaimHistory04` (`EnterpriseBillingCode`),
  KEY `Idx_T_ClaimHistory05` (`PrintedFlg`)
) ENGINE=InnoDB AUTO_INCREMENT=40765197 DEFAULT CHARSET=utf8

-- 5.リリーステーブルデータ移行
-- 時刻: 2745.809ms　45分
INSERT INTO T_ClaimHistory_Tmp(
        Seq
      , OrderSeq
      , ClaimSeq
      , ClaimDate
      , ClaimCpId
      , ClaimPattern
      , LimitDate
      , DamageDays
      , DamageBaseDate
      , DamageInterestAmount
      , ClaimFee
      , AdditionalClaimFee
      , PrintedFlg
      , PrintedDate
      , MailFlg
      , CreditTransferMailFlg
      , ClaimFileOutputClass
      , EnterpriseBillingCode
      , ClaimAmount
      , ClaimId
      , ReissueClass
      , ReissueRequestDate
      , PrintedStatus
      , MailRetryCount
      , CreditTransferRequestStatus
      , CreditMailRetryCount
      , PayeasyFee
      , RegistDate
      , RegistId
      , UpdateDate
      , UpdateId
      , ValidFlg
) SELECT 
        Seq
      , OrderSeq
      , ClaimSeq
      , ClaimDate
      , ClaimCpId
      , ClaimPattern
      , LimitDate
      , DamageDays
      , DamageBaseDate
      , DamageInterestAmount
      , ClaimFee
      , AdditionalClaimFee
      , PrintedFlg
      , PrintedDate
      , MailFlg
      , CreditTransferMailFlg
      , ClaimFileOutputClass
      , EnterpriseBillingCode
      , ClaimAmount
      , ClaimId
      , ReissueClass
      , ReissueRequestDate
      , PrintedStatus
      , MailRetryCount
      , CreditTransferRequestStatus
      , CreditMailRetryCount
      , PayeasyFee
      , RegistDate
      , RegistId
      , UpdateDate
      , UpdateId
      , ValidFlg
FROM T_ClaimHistory
;

-- 6.リリーステーブル登録トリガ
DROP TRIGGER IF EXISTS `insert_T_ClaimHistory_Tmp`;
DELIMITER ;;
CREATE TRIGGER `insert_T_ClaimHistory_Tmp` AFTER INSERT ON `T_ClaimHistory` FOR EACH ROW
 INSERT
   INTO `T_ClaimHistory_Tmp` (
        `Seq`
      , `OrderSeq`
      , `ClaimSeq`
      , `ClaimDate`
      , `ClaimCpId`
      , `ClaimPattern`
      , `LimitDate`
      , `DamageDays`
      , `DamageBaseDate`
      , `DamageInterestAmount`
      , `ClaimFee`
      , `AdditionalClaimFee`
      , `PrintedFlg`
      , `PrintedDate`
      , `MailFlg`
      , `CreditTransferMailFlg`
      , `ClaimFileOutputClass`
      , `EnterpriseBillingCode`
      , `ClaimAmount`
      , `ClaimId`
      , `ReissueClass`
      , `ReissueRequestDate`
      , `PrintedStatus`
      , `MailRetryCount`
      , `CreditTransferRequestStatus`
      , `CreditMailRetryCount`
      , `PayeasyFee`
      , `RegistDate`
      , `RegistId`
      , `UpdateDate`
      , `UpdateId`
      , `ValidFlg`
   ) VALUES (
        new.Seq
      , new.OrderSeq
      , new.ClaimSeq
      , new.ClaimDate
      , new.ClaimCpId
      , new.ClaimPattern
      , new.LimitDate
      , new.DamageDays
      , new.DamageBaseDate
      , new.DamageInterestAmount
      , new.ClaimFee
      , new.AdditionalClaimFee
      , new.PrintedFlg
      , new.PrintedDate
      , new.MailFlg
      , new.CreditTransferMailFlg
      , new.ClaimFileOutputClass
      , new.EnterpriseBillingCode
      , new.ClaimAmount
      , new.ClaimId
      , new.ReissueClass
      , new.ReissueRequestDate
      , new.PrintedStatus
      , new.MailRetryCount
      , new.CreditTransferRequestStatus
      , new.CreditMailRetryCount
      , new.PayeasyFee
      , new.RegistDate
      , new.RegistId
      , new.UpdateDate
      , new.UpdateId
      , new.ValidFlg
   )
;;
DELIMITER ;


-- 7.リリーステーブル更新トリガ
DROP TRIGGER IF EXISTS `update_T_ClaimHistory_Tmp`;
DELIMITER ;;
CREATE TRIGGER `update_T_ClaimHistory_Tmp` AFTER UPDATE ON `T_ClaimHistory` FOR EACH ROW
UPDATE T_ClaimHistory_Tmp SET
        Seq = new.Seq
      , OrderSeq = new.OrderSeq
      , ClaimSeq = new.ClaimSeq
      , ClaimDate = new.ClaimDate
      , ClaimCpId = new.ClaimCpId
      , ClaimPattern = new.ClaimPattern
      , LimitDate = new.LimitDate
      , DamageDays = new.DamageDays
      , DamageBaseDate = new.DamageBaseDate
      , DamageInterestAmount = new.DamageInterestAmount
      , ClaimFee = new.ClaimFee
      , AdditionalClaimFee = new.AdditionalClaimFee
      , PrintedFlg = new.PrintedFlg
      , PrintedDate = new.PrintedDate
      , MailFlg = new.MailFlg
      , CreditTransferMailFlg = new.CreditTransferMailFlg
      , ClaimFileOutputClass = new.ClaimFileOutputClass
      , EnterpriseBillingCode = new.EnterpriseBillingCode
      , ClaimAmount = new.ClaimAmount
      , ClaimId = new.ClaimId
      , ReissueClass = new.ReissueClass
      , ReissueRequestDate = new.ReissueRequestDate
      , PrintedStatus = new.PrintedStatus
      , MailRetryCount = new.MailRetryCount
      , CreditTransferRequestStatus = new.CreditTransferRequestStatus
      , CreditMailRetryCount = new.CreditMailRetryCount
      , PayeasyFee = new.PayeasyFee
      , RegistDate = new.RegistDate
      , RegistId = new.RegistId
      , UpdateDate = new.UpdateDate
      , UpdateId = new.UpdateId
      , ValidFlg = new.ValidFlg
WHERE Seq = old.Seq
;;
DELIMITER ;

-- 8.履歴テーブル登録トリガ修正
DROP TRIGGER IF EXISTS `insert_T_ClaimHistory`;
DELIMITER ;;
CREATE TRIGGER `insert_T_ClaimHistory` AFTER INSERT ON `T_ClaimHistory` FOR EACH ROW
 INSERT
   INTO `T_ClaimHistory_History` (
        `HistoryType`
      , `HistoryExecType`
      , `Seq`
      , `OrderSeq`
      , `ClaimSeq`
      , `ClaimDate`
      , `ClaimCpId`
      , `ClaimPattern`
      , `LimitDate`
      , `DamageDays`
      , `DamageBaseDate`
      , `DamageInterestAmount`
      , `ClaimFee`
      , `AdditionalClaimFee`
      , `PrintedFlg`
      , `PrintedDate`
      , `MailFlg`
      , `CreditTransferMailFlg`
      , `ClaimFileOutputClass`
      , `EnterpriseBillingCode`
      , `ClaimAmount`
      , `ClaimId`
      , `ReissueClass`
      , `ReissueRequestDate`
      , `PrintedStatus`
      , `MailRetryCount`
      , `CreditTransferRequestStatus`
      , `CreditMailRetryCount`
      , `PayeasyFee`
      , `RegistDate`
      , `RegistId`
      , `UpdateDate`
      , `UpdateId`
      , `ValidFlg`
   ) VALUES (
        'I'
      , 8
      , new.Seq
      , new.OrderSeq
      , new.ClaimSeq
      , new.ClaimDate
      , new.ClaimCpId
      , new.ClaimPattern
      , new.LimitDate
      , new.DamageDays
      , new.DamageBaseDate
      , new.DamageInterestAmount
      , new.ClaimFee
      , new.AdditionalClaimFee
      , new.PrintedFlg
      , new.PrintedDate
      , new.MailFlg
      , new.CreditTransferMailFlg
      , new.ClaimFileOutputClass
      , new.EnterpriseBillingCode
      , new.ClaimAmount
      , new.ClaimId
      , new.ReissueClass
      , new.ReissueRequestDate
      , new.PrintedStatus
      , new.MailRetryCount
      , new.CreditTransferRequestStatus
      , new.CreditMailRetryCount
      , new.PayeasyFee
      , new.RegistDate
      , new.RegistId
      , new.UpdateDate
      , new.UpdateId
      , new.ValidFlg
   )
;;
DELIMITER ;

-- 9.履歴テーブル更新トリガ修正
DROP TRIGGER IF EXISTS `update_T_ClaimHistory`;
DELIMITER ;;
CREATE TRIGGER `update_T_ClaimHistory` AFTER UPDATE ON `T_ClaimHistory` FOR EACH ROW
 INSERT
   INTO `T_ClaimHistory_History` (
        `HistoryType`
      , `HistoryExecType`
      , `Seq`
      , `OrderSeq`
      , `ClaimSeq`
      , `ClaimDate`
      , `ClaimCpId`
      , `ClaimPattern`
      , `LimitDate`
      , `DamageDays`
      , `DamageBaseDate`
      , `DamageInterestAmount`
      , `ClaimFee`
      , `AdditionalClaimFee`
      , `PrintedFlg`
      , `PrintedDate`
      , `MailFlg`
      , `CreditTransferMailFlg`
      , `ClaimFileOutputClass`
      , `EnterpriseBillingCode`
      , `ClaimAmount`
      , `ClaimId`
      , `ReissueClass`
      , `ReissueRequestDate`
      , `PrintedStatus`
      , `MailRetryCount`
      , `CreditTransferRequestStatus`
      , `CreditMailRetryCount`
      , `PayeasyFee`
      , `RegistDate`
      , `RegistId`
      , `UpdateDate`
      , `UpdateId`
      , `ValidFlg`
   ) VALUES (
        'U'
      , 8
      , new.Seq
      , new.OrderSeq
      , new.ClaimSeq
      , new.ClaimDate
      , new.ClaimCpId
      , new.ClaimPattern
      , new.LimitDate
      , new.DamageDays
      , new.DamageBaseDate
      , new.DamageInterestAmount
      , new.ClaimFee
      , new.AdditionalClaimFee
      , new.PrintedFlg
      , new.PrintedDate
      , new.MailFlg
      , new.CreditTransferMailFlg
      , new.ClaimFileOutputClass
      , new.EnterpriseBillingCode
      , new.ClaimAmount
      , new.ClaimId
      , new.ReissueClass
      , new.ReissueRequestDate
      , new.PrintedStatus
      , new.MailRetryCount
      , new.CreditTransferRequestStatus
      , new.CreditMailRetryCount
      , new.PayeasyFee
      , new.RegistDate
      , new.RegistId
      , new.UpdateDate
      , new.UpdateId
      , new.ValidFlg
   )
;;
DELIMITER ;








-- 8.履歴テーブル更新トリガ削除
DROP TRIGGER IF EXISTS `insert_T_ClaimControl_History`;
DROP TRIGGER IF EXISTS `update_T_ClaimControl_History`;
-- 8.履歴テーブル更新トリガ削除
DROP TRIGGER IF EXISTS `insert_T_ClaimHistory_History`;
DROP TRIGGER IF EXISTS `update_T_ClaimHistory_History`;
