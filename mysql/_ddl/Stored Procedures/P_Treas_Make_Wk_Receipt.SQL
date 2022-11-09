DROP PROCEDURE IF EXISTS P_Treas_Make_Wk_Receipt;

DELIMITER $$

CREATE PROCEDURE P_Treas_Make_Wk_Receipt ()
proc:
/******************************************************************************
 *
 * ��ۼ��ެ��       �F  P_Treas_Make_Wk_Receipt
 *
 * �T�v             �F  ��v�����p���[�N�����f�[�^�쐬
 *
 * ����             �F  �Ȃ�
 *
 * ����             �F  2016/01/14  NDC �V�K�쐬
 *
 *****************************************************************************/
BEGIN

    -- ------------------------------
    -- �ϐ��錾
    -- ------------------------------
    DECLARE v_AccountingMonth   DATE;                   -- ��v��

    -- ------------------------------
    -- �ϐ�������
    -- ------------------------------
    SET v_AccountingMonth = F_GetSystemProperty('[DEFAULT]', 'systeminfo', 'AccountingMonth');   -- ��v��

    -- AW_Receipt1�S�f�[�^�폜
    TRUNCATE TABLE AW_Receipt1;

    -- AW_Receipt2�S�f�[�^�폜
    TRUNCATE TABLE AW_Receipt2;

    -- AW_Receipt1�쐬
    INSERT INTO AW_Receipt1 (OrderSeq, ReceiptSeq, ReceiptAmount, ReceiptDate)
    SELECT OrderSeq
    ,      MAX(ReceiptSeq)
    ,      SUM(ReceiptAmount)
    ,      MAX(ReceiptDate)
    FROM   T_ReceiptControl
    WHERE  DATE_FORMAT(ReceiptDate, '%Y-%m') <= DATE_FORMAT(v_AccountingMonth, '%Y-%m')
    GROUP BY OrderSeq;

    -- AW_Receipt2�쐬
    INSERT INTO AW_Receipt2 (OrderSeq, Sum_CheckingUseAmount, Max_ReceiptDate)
    SELECT OrderSeq
    ,      SUM(IFNULL(CheckingUseAmount, 0))
    ,      MAX(ReceiptDate)
    FROM   T_ReceiptControl
    WHERE  DATE_FORMAT(ReceiptDate, '%Y-%m') > DATE_FORMAT(v_AccountingMonth, '%Y-%m')
    GROUP BY OrderSeq;

END
$$

DELIMITER ;