/* ƒ[ƒ‹‘—M“ú •„†”½“] */
UPDATE `T_SystemProperty` SET `PropValue`=-1*`PropValue` WHERE `Module` = '[DEFAULT]' AND `Category` = 'systeminfo' AND `Name` = 'PaymentSoonDays';
