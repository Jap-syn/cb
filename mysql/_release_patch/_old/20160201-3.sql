-- 一旦、前月分としてUPDATE
UPDATE AT_EnterpriseMonthlyClosingInfo emc
SET PayingControlSeq = ( SELECT MIN(Seq) FROM T_PayingControl WHERE EnterpriseId = emc.EnterpriseId AND AddUpFixedMonth = DATE_ADD(emc.ClosingMonthly, INTERVAL 1 MONTH) ) ;

-- 2015-11-30の月末締めのみ当月としてUPDATE
UPDATE AT_EnterpriseMonthlyClosingInfo emc 
SET PayingControlSeq = ( SELECT MIN(Seq) FROM T_PayingControl WHERE EnterpriseId = emc.EnterpriseId AND AddUpFixedMonth = emc.ClosingMonthly ) 
WHERE EXISTS( SELECT * FROM T_PayingControl WHERE EnterpriseId = emc.EnterpriseId AND AddUpFixedMonth = emc.ClosingMonthly AND FixedDate = '2015-11-30' ) 
AND   ( SELECT COUNT(*) FROM T_PayingControl WHERE EnterpriseId = emc.EnterpriseId AND AddUpFixedMonth = emc.ClosingMonthly ) = 1;

-- 2015-12-31の月末締めのみ当月としてUPDATE
UPDATE AT_EnterpriseMonthlyClosingInfo emc 
SET PayingControlSeq = ( SELECT MIN(Seq) FROM T_PayingControl WHERE EnterpriseId = emc.EnterpriseId AND AddUpFixedMonth = emc.ClosingMonthly ) 
WHERE EXISTS( SELECT * FROM T_PayingControl WHERE EnterpriseId = emc.EnterpriseId AND AddUpFixedMonth = emc.ClosingMonthly AND FixedDate = '2015-12-31' )
AND   ( SELECT COUNT(*) FROM T_PayingControl WHERE EnterpriseId = emc.EnterpriseId AND AddUpFixedMonth = emc.ClosingMonthly ) = 1;

-- 2016-01-31の月末締めのみ当月としてUPDATE
UPDATE AT_EnterpriseMonthlyClosingInfo emc 
SET PayingControlSeq = ( SELECT MIN(Seq) FROM T_PayingControl WHERE EnterpriseId = emc.EnterpriseId AND AddUpFixedMonth = emc.ClosingMonthly ) 
WHERE EXISTS( SELECT * FROM T_PayingControl WHERE EnterpriseId = emc.EnterpriseId AND AddUpFixedMonth = emc.ClosingMonthly AND FixedDate = '2016-01-31' )
AND   ( SELECT COUNT(*) FROM T_PayingControl WHERE EnterpriseId = emc.EnterpriseId AND AddUpFixedMonth = emc.ClosingMonthly ) = 1;


-- 立替確定したか否か
UPDATE AT_EnterpriseMonthlyClosingInfo emc ,T_PayingControl pc
SET  emc.PayingControlAddUpFlg = pc.PayingControlStatus
WHERE emc.PayingControlSeq = pc.Seq;



-- 複数月額固定費取得してしまったものを算出→データ取得を行う
select PayingControlSeq,count(1)
from AT_EnterpriseMonthlyClosingInfo
group by PayingControlSeq
having count(1) > 1;