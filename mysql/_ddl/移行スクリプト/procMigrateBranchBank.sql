DROP procedure IF EXISTS `procMigrateBranchBank`;

DELIMITER $$
CREATE PROCEDURE `procMigrateBranchBank` ()
BEGIN

    /* 移行処理：銀行支店マスター */

    DECLARE updDttm     datetime;

    SET updDttm = now();

    INSERT INTO `M_BranchBank`(`BranchBankId`,`BankCode`,`BranchCode`,`BankName`,`BranchName`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) VALUES (10, '0009', '661', '三井住友銀行', '新宿通支店', updDttm, 9, updDttm, 9, 1);

    INSERT INTO `M_BranchBank`(`BranchBankId`,`BankCode`,`BranchCode`,`BankName`,`BranchName`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) VALUES (20, '0033', '002', 'ジャパンネット銀行', 'すずめ支店', updDttm, 9, updDttm, 9, 1);

    INSERT INTO `M_BranchBank`(`BranchBankId`,`BankCode`,`BranchCode`,`BankName`,`BranchName`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) VALUES (30, '0033', '702', 'ジャパンネット銀行', 'モミジ支店', updDttm, 9, updDttm, 9, 1);



END
$$

DELIMITER ;

