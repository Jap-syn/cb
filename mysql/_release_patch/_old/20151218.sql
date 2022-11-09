-- 日次売上明細
ALTER TABLE AT_Daily_SalesDetails ADD COLUMN `FixedDate` DATE  NULL AFTER `SalesDefiniteDate`;
ALTER TABLE AT_Daily_SalesDetails ADD COLUMN `ExecScheduleDate` DATE  NULL AFTER `FixedDate`;

-- ﾃﾝﾌﾟﾚｰﾄﾌｨｰﾙﾄﾞのUPDATE（ListNumber に対するUPDATEなので、下から行う。）
UPDATE M_TemplateField SET ListNumber='47' WHERE TemplateSeq='60' and PhysicalName='OemTotalSales';
UPDATE M_TemplateField SET ListNumber='46' WHERE TemplateSeq='60' and PhysicalName='OemNCreditNoticeMonthlyFeeTax';
UPDATE M_TemplateField SET ListNumber='45' WHERE TemplateSeq='60' and PhysicalName='OemNCreditNoticeMonthlyFee';
UPDATE M_TemplateField SET ListNumber='44' WHERE TemplateSeq='60' and PhysicalName='OemCreditNoticeMonthlyFeeTax';
UPDATE M_TemplateField SET ListNumber='43' WHERE TemplateSeq='60' and PhysicalName='OemCreditNoticeMonthlyFee';
UPDATE M_TemplateField SET ListNumber='42' WHERE TemplateSeq='60' and PhysicalName='OemApiMonthlyFeeTax';
UPDATE M_TemplateField SET ListNumber='41' WHERE TemplateSeq='60' and PhysicalName='OemApiMonthlyFee';
UPDATE M_TemplateField SET ListNumber='40' WHERE TemplateSeq='60' and PhysicalName='OemIncludeMonthlyFeeTax';
UPDATE M_TemplateField SET ListNumber='39' WHERE TemplateSeq='60' and PhysicalName='OemIncludeMonthlyFee';
UPDATE M_TemplateField SET ListNumber='38' WHERE TemplateSeq='60' and PhysicalName='OemMonthlyFeeTax';
UPDATE M_TemplateField SET ListNumber='37' WHERE TemplateSeq='60' and PhysicalName='OemMonthlyFee';
UPDATE M_TemplateField SET ListNumber='36' WHERE TemplateSeq='60' and PhysicalName='OemClaimFeeTax';
UPDATE M_TemplateField SET ListNumber='35' WHERE TemplateSeq='60' and PhysicalName='OemClaimFee';
UPDATE M_TemplateField SET ListNumber='34' WHERE TemplateSeq='60' and PhysicalName='OemSettlementFeeTax';
UPDATE M_TemplateField SET ListNumber='33' WHERE TemplateSeq='60' and PhysicalName='OemSettlementFee';
UPDATE M_TemplateField SET ListNumber='32' WHERE TemplateSeq='60' and PhysicalName='OemSettlementFeeRate';
UPDATE M_TemplateField SET ListNumber='31' WHERE TemplateSeq='60' and PhysicalName='TotalSales';
UPDATE M_TemplateField SET ListNumber='30' WHERE TemplateSeq='60' and PhysicalName='NCreditNoticeMonthlyFeeTax';
UPDATE M_TemplateField SET ListNumber='29' WHERE TemplateSeq='60' and PhysicalName='NCreditNoticeMonthlyFee';
UPDATE M_TemplateField SET ListNumber='28' WHERE TemplateSeq='60' and PhysicalName='CreditNoticeMonthlyFeeTax';
UPDATE M_TemplateField SET ListNumber='27' WHERE TemplateSeq='60' and PhysicalName='CreditNoticeMonthlyFee';
UPDATE M_TemplateField SET ListNumber='26' WHERE TemplateSeq='60' and PhysicalName='ApiMonthlyFeeTax';
UPDATE M_TemplateField SET ListNumber='25' WHERE TemplateSeq='60' and PhysicalName='ApiMonthlyFee';
UPDATE M_TemplateField SET ListNumber='24' WHERE TemplateSeq='60' and PhysicalName='IncludeMonthlyFeeTax';
UPDATE M_TemplateField SET ListNumber='23' WHERE TemplateSeq='60' and PhysicalName='IncludeMonthlyFee';
UPDATE M_TemplateField SET ListNumber='22' WHERE TemplateSeq='60' and PhysicalName='MonthlyFeeTax';
UPDATE M_TemplateField SET ListNumber='21' WHERE TemplateSeq='60' and PhysicalName='MonthlyFee';
UPDATE M_TemplateField SET ListNumber='20' WHERE TemplateSeq='60' and PhysicalName='ClaimFeeTax';
UPDATE M_TemplateField SET ListNumber='19' WHERE TemplateSeq='60' and PhysicalName='ClaimFee';
UPDATE M_TemplateField SET ListNumber='18' WHERE TemplateSeq='60' and PhysicalName='SettlementFee';
UPDATE M_TemplateField SET ListNumber='17' WHERE TemplateSeq='60' and PhysicalName='SettlementFeeRate';
UPDATE M_TemplateField SET ListNumber='16' WHERE TemplateSeq='60' and PhysicalName='UseAmountTotal';
UPDATE M_TemplateField SET ListNumber='15' WHERE TemplateSeq='60' and PhysicalName='ManCusNameKj';
UPDATE M_TemplateField SET ListNumber='14' WHERE TemplateSeq='60' and PhysicalName='ManCustId';
UPDATE M_TemplateField SET ListNumber='13' WHERE TemplateSeq='60' and PhysicalName='JournalNumber';
UPDATE M_TemplateField SET ListNumber='12' WHERE TemplateSeq='60' and PhysicalName='AccountDate';
UPDATE M_TemplateField SET ListNumber='11' WHERE TemplateSeq='60' and PhysicalName='ProcessingDate';

-- ﾃﾝﾌﾟﾚｰﾄﾌｨｰﾙﾄﾞのINSERT
INSERT INTO M_TemplateField VALUES ( 60 , 9, 'FixedDate' ,'立替締め日' ,'DATE' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 60 , 10, 'ExecScheduleDate' ,'立替予定日' ,'DATE' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
