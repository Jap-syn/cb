/* ���[�����M�� �������] */
UPDATE `T_SystemProperty` SET `PropValue`=-1*`PropValue` WHERE `Module` = '[DEFAULT]' AND `Category` = 'systeminfo' AND `Name` = 'PaymentSoonDays';
