-- �J����[�����XID]�ǉ�
ALTER TABLE `T_CreditCondition` 
ADD COLUMN `EnterpriseId` BIGINT(20) NULL AFTER `JintecManualReqFlg`;

-- [�����XID]�փC���f�b�N�X�t�^
ALTER TABLE `T_CreditCondition` 
ADD INDEX `Idx_T_CreditCondition10` (`EnterpriseId` ASC);
