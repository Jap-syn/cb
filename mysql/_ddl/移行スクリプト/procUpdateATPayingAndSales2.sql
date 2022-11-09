DROP procedure IF EXISTS `procUpdateATPayingAndSales2`;

DELIMITER $$
CREATE PROCEDURE `procUpdateATPayingAndSales2` ()
BEGIN
    /* 2. AT_PayingAndSales����(ATUriType�^ATUriDay) */

    DECLARE l_Seq                           BIGINT;     -- SEQ
    DECLARE l_OrderSeq                      BIGINT;     -- ����SEQ
    DECLARE l_P_OrderSeq                    BIGINT;     -- �e����SEQ
    DECLARE l_ClearConditionDate            DATE;       -- ���֏����N���A��
    DECLARE l_Deli_ConfirmArrivalFlg        INT;        -- �z���|���׊m�F
    DECLARE l_Deli_ConfirmArrivalDate       DATETIME;   -- �z���|�m�F��
    DECLARE l_Deli_ConfirmArrivalInputDate  DATETIME;   -- ���׊m�F���͓�
    DECLARE l_ServiceTargetClass            INT;        -- �𖱑Ώۋ敪

    DECLARE l_rc_ReceiptSeq                 BIGINT;     -- ����SEQ
    DECLARE l_rc_cnt                        INT;        -- ��������
    DECLARE l_rc_ReceiptDate                DATE;       -- �ڋq������
    DECLARE l_rc_ReceiptProcessDate         DATETIME;   -- ����������

    -- NO_DATA_FOUND ���������חp�ϐ�
    DECLARE no_data_found INT DEFAULT 1;

    -- ���ِ錾
    DECLARE l_cur CURSOR FOR
        SELECT apas.Seq
        ,      pas.OrderSeq
        ,      o.P_OrderSeq
        ,      pas.ClearConditionDate
        ,      o.Deli_ConfirmArrivalFlg
        ,      o.Deli_ConfirmArrivalDate
        ,      apas.Deli_ConfirmArrivalInputDate
        ,      o.ServiceTargetClass
        FROM   AT_PayingAndSales apas
               INNER JOIN T_PayingAndSales pas ON (apas.Seq = pas.Seq)
               INNER JOIN T_Order o ON (pas.OrderSeq = o.OrderSeq)
        WHERE  apas.ATUriType = 99
        AND    apas.ATUriDay = '99999999'
        AND    pas.ClearConditionForCharge = 1
        AND    pas.ClearConditionDate IS NOT NULL
        ;

    -- NO_DATA_FOUND �p���������א錾
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET no_data_found = 0;

    -- ���ٵ����
    OPEN    l_cur;
        -- �ŏ��̶��ق�Fetch
        FETCH l_cur INTO l_Seq, l_OrderSeq, l_P_OrderSeq, l_ClearConditionDate, l_Deli_ConfirmArrivalFlg, l_Deli_ConfirmArrivalDate, l_Deli_ConfirmArrivalInputDate, l_ServiceTargetClass;
        -- whileٰ�߂ŏ�������
        WHILE no_data_found != 0  DO

            IF l_ServiceTargetClass = 0 THEN
                -- �ʏ풍��

                -- ���֏����N���A�����ɊY����������̃`�F�b�N
                SELECT MIN(rc.ReceiptSeq)
                ,      COUNT(rc.ReceiptSeq)
                INTO   l_rc_ReceiptSeq
                ,      l_rc_cnt
                FROM   T_ClaimControl cc
                ,      T_ReceiptControl rc
                WHERE  cc.OrderSeq = rc.OrderSeq
                AND    cc.OrderSeq = l_P_OrderSeq
                AND    cc.MinClaimAmount - (SELECT SUM(ReceiptAmount) FROM T_ReceiptControl WHERE OrderSeq = cc.OrderSeq AND ReceiptSeq <= rc.ReceiptSeq) <= 0
                AND    rc.ReceiptSeq > IFNULL((SELECT MAX(sub1.ReceiptSeq) FROM T_ReceiptControl sub1,AT_ReceiptControl sub2 WHERE sub1.ReceiptSeq = sub2.ReceiptSeq AND sub1.OrderSeq = rc.OrderSeq AND Rct_CancelFlg = 1), 0);

                IF l_rc_cnt > 0 THEN
                    -- ��������

                    -- �ڋq�������^�����������̎擾
                    SELECT ReceiptDate
                    ,      ReceiptProcessDate
                    INTO   l_rc_ReceiptDate
                    ,      l_rc_ReceiptProcessDate
                    FROM   T_ReceiptControl
                    WHERE  ReceiptSeq = l_rc_ReceiptSeq;

                    IF l_Deli_ConfirmArrivalFlg = 1 AND (l_Deli_ConfirmArrivalInputDate  < l_rc_ReceiptProcessDate) THEN
                        -- (���ׂ��芎�A���׊m�F���͓���������������菬������)
                        UPDATE AT_PayingAndSales
                        SET    ATUriType = 1                                                    -- 1:����
                        ,      ATUriDay = DATE_FORMAT(l_Deli_ConfirmArrivalDate, '%Y%m%d')      -- ���׊m�F��
                        WHERE  Seq = l_Seq;
                        
                    ELSE
                        -- (��L�����藧���Ȃ���)
                        UPDATE AT_PayingAndSales
                        SET    ATUriType = 2                                                    -- 2:����
                        ,      ATUriDay = DATE_FORMAT(l_rc_ReceiptDate, '%Y%m%d')               -- �ڋq������
                        WHERE  Seq = l_Seq;
                        
                    END IF;
                    
                ELSE
                    -- �����Ȃ�
                    IF l_Deli_ConfirmArrivalDate IS NULL THEN
                        -- (�������A���ׂɂĂ̗��֏����N���A�ƂȂ�Ȃ��P�[�X [�G����])
                        UPDATE AT_PayingAndSales
                        SET    ATUriType = 2                                                    -- 2:����
                        ,      ATUriDay = DATE_FORMAT(l_ClearConditionDate, '%Y%m%d')           -- ���֏����N���A��
                        WHERE  Seq = l_Seq;
                        
                    ELSE
                        UPDATE AT_PayingAndSales
                        SET    ATUriType = 1                                                    -- 1:����
                        ,      ATUriDay = DATE_FORMAT(l_Deli_ConfirmArrivalDate, '%Y%m%d')      -- ���׊m�F��
                        WHERE  Seq = l_Seq;
                        
                    END IF;
                    
                END IF;

            ELSE
                -- ��
                UPDATE AT_PayingAndSales
                SET    ATUriType = 3                                                            -- 3:��
                ,      ATUriDay = DATE_FORMAT(l_ClearConditionDate, '%Y%m%d')                   -- ���֏����N���A��
                WHERE  Seq = l_Seq;
                
            END IF;

            -- ���̶��ق�Fetch
            FETCH l_cur INTO l_Seq, l_OrderSeq, l_P_OrderSeq, l_ClearConditionDate, l_Deli_ConfirmArrivalFlg, l_Deli_ConfirmArrivalDate, l_Deli_ConfirmArrivalInputDate, l_ServiceTargetClass;
        END WHILE;
    -- ���ٸ۰��
    CLOSE   l_cur;

END
$$

DELIMITER ;

