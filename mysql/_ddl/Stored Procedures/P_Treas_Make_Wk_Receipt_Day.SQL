DROP PROCEDURE IF EXISTS P_Treas_Make_Wk_Receipt_Day;

DELIMITER $$

CREATE PROCEDURE P_Treas_Make_Wk_Receipt_Day ()
proc:
/******************************************************************************
 *
 * ��ۼ��ެ��       �F  P_Treas_Make_Wk_Receipt_Day
 *
 * �T�v             �F  ��v�����pܰ������ް��쐬
 *
 * ����             �F  �Ȃ�
 *
 * ����             �F  2016/02/01  NDC �V�K�쐬
 *
 *****************************************************************************/
BEGIN

    -- ------------------------------
    -- �ϐ��錾
    -- ------------------------------
    DECLARE v_BusinessDate  DATE;               -- �Ɩ����t

    -- ------------------------------
    -- �ϐ�������
    -- ------------------------------
    SET v_BusinessDate  = F_GetSystemProperty('[DEFAULT]', 'systeminfo', 'BusinessDate');   -- �Ɩ����t

    -- AW_Receipt3�S�ް��폜
    TRUNCATE TABLE AW_Receipt3;

    -- AW_Receipt3�쐬
    INSERT
    INTO AW_Receipt3 (OrderSeq, Rct_CancelFlg)
    SELECT  rc.OrderSeq
        ,   MAX(arc.Rct_CancelFlg)
    FROM    T_ReceiptControl rc
            INNER JOIN AT_ReceiptControl arc ON (arc.ReceiptSeq = rc.ReceiptSeq)
    WHERE   DATE(rc.ReceiptProcessDate) <= v_BusinessDate    -- �Ɩ����t�ȑO
    GROUP BY
            rc.OrderSeq
    ;
END
$$

DELIMITER ;