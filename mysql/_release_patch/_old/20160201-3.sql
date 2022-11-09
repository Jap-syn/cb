-- ��U�A�O�����Ƃ���UPDATE
UPDATE AT_EnterpriseMonthlyClosingInfo emc
SET PayingControlSeq = ( SELECT MIN(Seq) FROM T_PayingControl WHERE EnterpriseId = emc.EnterpriseId AND AddUpFixedMonth = DATE_ADD(emc.ClosingMonthly, INTERVAL 1 MONTH) ) ;

-- 2015-11-30�̌������߂̂ݓ����Ƃ���UPDATE
UPDATE AT_EnterpriseMonthlyClosingInfo emc 
SET PayingControlSeq = ( SELECT MIN(Seq) FROM T_PayingControl WHERE EnterpriseId = emc.EnterpriseId AND AddUpFixedMonth = emc.ClosingMonthly ) 
WHERE EXISTS( SELECT * FROM T_PayingControl WHERE EnterpriseId = emc.EnterpriseId AND AddUpFixedMonth = emc.ClosingMonthly AND FixedDate = '2015-11-30' ) 
AND   ( SELECT COUNT(*) FROM T_PayingControl WHERE EnterpriseId = emc.EnterpriseId AND AddUpFixedMonth = emc.ClosingMonthly ) = 1;

-- 2015-12-31�̌������߂̂ݓ����Ƃ���UPDATE
UPDATE AT_EnterpriseMonthlyClosingInfo emc 
SET PayingControlSeq = ( SELECT MIN(Seq) FROM T_PayingControl WHERE EnterpriseId = emc.EnterpriseId AND AddUpFixedMonth = emc.ClosingMonthly ) 
WHERE EXISTS( SELECT * FROM T_PayingControl WHERE EnterpriseId = emc.EnterpriseId AND AddUpFixedMonth = emc.ClosingMonthly AND FixedDate = '2015-12-31' )
AND   ( SELECT COUNT(*) FROM T_PayingControl WHERE EnterpriseId = emc.EnterpriseId AND AddUpFixedMonth = emc.ClosingMonthly ) = 1;

-- 2016-01-31�̌������߂̂ݓ����Ƃ���UPDATE
UPDATE AT_EnterpriseMonthlyClosingInfo emc 
SET PayingControlSeq = ( SELECT MIN(Seq) FROM T_PayingControl WHERE EnterpriseId = emc.EnterpriseId AND AddUpFixedMonth = emc.ClosingMonthly ) 
WHERE EXISTS( SELECT * FROM T_PayingControl WHERE EnterpriseId = emc.EnterpriseId AND AddUpFixedMonth = emc.ClosingMonthly AND FixedDate = '2016-01-31' )
AND   ( SELECT COUNT(*) FROM T_PayingControl WHERE EnterpriseId = emc.EnterpriseId AND AddUpFixedMonth = emc.ClosingMonthly ) = 1;


-- ���֊m�肵�����ۂ�
UPDATE AT_EnterpriseMonthlyClosingInfo emc ,T_PayingControl pc
SET  emc.PayingControlAddUpFlg = pc.PayingControlStatus
WHERE emc.PayingControlSeq = pc.Seq;



-- �������z�Œ��擾���Ă��܂������̂��Z�o���f�[�^�擾���s��
select PayingControlSeq,count(1)
from AT_EnterpriseMonthlyClosingInfo
group by PayingControlSeq
having count(1) > 1;