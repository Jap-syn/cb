/* ���ցE����Ǘ�_��v��[����m�����][����m����t]��ǉ� */
ALTER TABLE `AT_PayingAndSales` 
ADD COLUMN `ATUriType` TINYINT(4) NULL DEFAULT 99 AFTER `Deli_ConfirmArrivalInputDate`,
ADD COLUMN `ATUriDay` VARCHAR(8) NULL DEFAULT '99999999' AFTER `ATUriType`;

/* ���ցE����Ǘ�_��v[����m�����][����m����t]�փC���f�b�N�X�t�^ */
ALTER TABLE `AT_PayingAndSales` 
ADD INDEX `Idx_AT_PayingAndSales02` (`ATUriType` ASC),
ADD INDEX `Idx_AT_PayingAndSales03` (`ATUriDay` ASC);
