delimiter $$

CREATE DEFINER=`skip-grants user`@`skip-grants host` PROCEDURE `P_ReceiptControl`(  IN  pi_receipt_amount   BIGINT(20)
                                ,   IN  pi_order_seq        BIGINT(20)
                                ,   IN  pi_receipt_date     DATE
                                ,   IN  pi_receipt_class    INT
                                ,   IN  pi_branch_bank_id   BIGINT
                                ,   IN  pi_receipt_agent_id BIGINT
                                ,   IN  pi_deposit_date     DATE
                                ,   IN  pi_user_id          VARCHAR(20)
                                , 	IN pi_receipt_note 	TEXT
                                ,   OUT po_ret_sts          INT
                                ,   OUT po_ret_errcd        VARCHAR(100)
                                ,   OUT po_ret_sqlcd        INT
                                ,   OUT po_ret_msg          VARCHAR(255)
                                 )
proc:
/******************************************************************************
 *
 * ��ۼ��ެ��   �F  P_ReceiptControl
 *
 * �T�v         �F  �����֘A����
 *
 * ����         �F  [I/ ]pi_receipt_amount                      �����z
 *              �F  [I/ ]pi_order_seq                           �e����Seq
 *              �F  [I/ ]pi_receipt_date                        ������
 *              �F  [I/ ]pi_receipt_class                       �����`��
 *              �F  [I/ ]pi_branch_bank_id                      ��s�x�XID
 *              �F  [I/ ]pi_receipt_agent_id                    ���[��sID
 *              �F  [I/ ]pi_deposit_date                        ����������
 *              �F  [I/ ]pi_user_id                             հ�ްID
 *              �F  [I/ ]pi_receipt_note                       ���l
 *              �F  [ /O]po_ret_sts                             ���ݽð��
 *              �F  [ /O]po_ret_errcd                           ���ݺ���
 *              �F  [ /O]po_ret_sqlcd                           ����SQL����
 *              �F  [ /O]po_ret_msg                             ����ү����
 *
 * ����         �F  2015/05/13  NDC �V�K�쐬
 *                  2016/02/04  NDC ���z�����̏ꍇ��ۼޯ�������邽�߁A��������z�̍l����ǉ��B
 *                  2022/06/07  OMINEXT �ڍד�����ʂ̉��C�Ŕ��l��ǉ��B

 *
 *****************************************************************************/
BEGIN
    -- ---------------------
    -- �ϐ��錾
    -- ---------------------
    -- �����ް��擾�p
    DECLARE v_OrderId                           VARCHAR(50) DEFAULT '';
    DECLARE v_DataStatus                        INT DEFAULT 0;
    DECLARE v_Cnt                               INT;
    DECLARE v_P_OrderSeq                        BIGINT(20) DEFAULT 0;

    -- �c�����X�V�p
    DECLARE v_BalanceClaimAmount                BIGINT(20) DEFAULT 0;
    DECLARE v_BalanceUseAmount                  BIGINT(20) DEFAULT 0;
    DECLARE v_BalanceDamageInterestAmount       BIGINT(20) DEFAULT 0;
    DECLARE v_BalanceClaimFee                   BIGINT(20) DEFAULT 0;
    DECLARE v_BalanceAdditionalClaimFee         BIGINT(20) DEFAULT 0;
    -- �������X�V�p
    DECLARE v_CheckingClaimAmount               BIGINT(20) DEFAULT 0;
    DECLARE v_CheckingUseAmount                 BIGINT(20) DEFAULT 0;
    DECLARE v_CheckingClaimFee                  BIGINT(20) DEFAULT 0;
    DECLARE v_CheckingDamageInterestAmount      BIGINT(20) DEFAULT 0;
    DECLARE v_CheckingAdditionalClaimFee        BIGINT(20) DEFAULT 0;
    -- �G�����E�G�����X�V�p
    DECLARE v_SundryAmount                      BIGINT(20) DEFAULT 0;
    DECLARE v_SundryUseAmount                   BIGINT(20) DEFAULT 0;
    DECLARE v_SundryClaimFee                    BIGINT(20) DEFAULT 0;
    DECLARE v_SundryDamageInterestAmount        BIGINT(20) DEFAULT 0;
    DECLARE v_SundryAdditionalClaimFee          BIGINT(20) DEFAULT 0;
    -- �c���擾�p
    DECLARE v_CalculationAmount                 BIGINT(20) DEFAULT 0;
    -- ���z�擾�p
    DECLARE v_diffClaimFee                      BIGINT(20) DEFAULT 0;
    DECLARE v_diffDamageInterestAmount          BIGINT(20) DEFAULT 0;
    DECLARE v_diffAdditionalClaimFee            BIGINT(20) DEFAULT 0;
    -- ����۰�ތ�����ް��쐬�p
    DECLARE v_InsReceiptAmount                  BIGINT(20) DEFAULT 0;
    DECLARE v_InsReceiptUseAmount               BIGINT(20) DEFAULT 0;
    DECLARE v_InsReceiptClaimFee                BIGINT(20) DEFAULT 0;
    DECLARE v_InsReceiptDamageInterestAmount    BIGINT(20) DEFAULT 0;
    DECLARE v_InsReceiptAdditionalClaimFee      BIGINT(20) DEFAULT 0;

    -- �����ް��擾�p
    DECLARE v_ClaimId                           BIGINT(20) DEFAULT 0;
    DECLARE v_ClaimPattern                      INT;
    DECLARE v_UseAmountTotal                    BIGINT(20) DEFAULT 0;
    DECLARE v_ClaimFee                          BIGINT(20) DEFAULT 0;
    DECLARE v_DamageInterestAmount              BIGINT(20) DEFAULT 0;
    DECLARE v_AdditionalClaimFee                BIGINT(20) DEFAULT 0;
    DECLARE v_ClaimAmount                       BIGINT(20) DEFAULT 0;
    DECLARE v_ClaimedBalance                    BIGINT(20) DEFAULT 0;
    DECLARE v_MinClaimAmount                    BIGINT(20) DEFAULT 0;
    DECLARE v_MinUseAmount                      BIGINT(20) DEFAULT 0;
    DECLARE v_MinClaimFee                       BIGINT(20) DEFAULT 0;
    DECLARE v_MinDamageInterestAmount           BIGINT(20) DEFAULT 0;
    DECLARE v_MinAdditionalClaimFee             BIGINT(20) DEFAULT 0;

    -- �����ް��擾�p
    DECLARE v_ReceiptAmount                     BIGINT(20) DEFAULT 0;
    DECLARE v_ReceiptUseAmount                  BIGINT(20) DEFAULT 0;
    DECLARE v_ReceiptClaimFee                   BIGINT(20) DEFAULT 0;
    DECLARE v_ReceiptDamageInterestAmount       BIGINT(20) DEFAULT 0;
    DECLARE v_ReceiptAdditionalClaimFee         BIGINT(20) DEFAULT 0;
    DECLARE v_ReceiptSeq                        BIGINT(20) DEFAULT 0;
    DECLARE v_OrderSeq                          BIGINT(20) DEFAULT 0;

    -- ���̑�
    DECLARE no_data_found INT DEFAULT 1;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET no_data_found = 0;

    /* ********************* *
     * �����J�n
     * ********************* */
    -- ------------------------------
    -- 1.�߂�l������
    -- ------------------------------
    SET po_ret_sts      =   0;
    SET po_ret_errcd    =   '';
    SET po_ret_sqlcd    =   0;
    SET po_ret_msg      =   '����I��';

    -- ------------------------------
    -- 2.�����ް��擾
    -- ------------------------------
    -- ���܂Ƃ߂��Ă���ꍇ�̍l��
    -- 1���ł��L���Ȓ����ް������݂���΁A�����\
    SELECT  COUNT(*)
    INTO    v_Cnt
    FROM    T_Order
    -- ���L�����Z�� ���� �����m�F�҂��܂��͈ꕔ����
    -- ���L�����Z�� ���� �����ςݐ���N���[�Y
    -- �L�����Z���N���[�Y���}�C�i�X�����i�ԋ��j
    WHERE   ((Cnl_Status = 0 AND DataStatus IN (51, 61))
          OR (Cnl_Status = 0 AND DataStatus = 91 AND CloseReason = 1)
          OR (Cnl_Status > 0 AND DataStatus = 91 AND CloseReason = 2 AND pi_receipt_amount < 0)
            )
    AND     P_OrderSeq  =   pi_order_seq
    ;

    IF  v_Cnt = 0  THEN
        SET po_ret_sts  =   -1;
        SET po_ret_msg  =   '�����Ώۂ̃f�[�^�����݂��܂���B';
        LEAVE proc;
    END IF;

    -- �e�̒���ID���擾
    -- �e��������ݾق���Ă���ꍇ���l������DataStatus���̏����͊܂܂Ȃ�
    SELECT  OrderId
    INTO    v_OrderId
    FROM    T_Order
    WHERE   OrderSeq    =   P_OrderSeq
    AND     P_OrderSeq  =   pi_order_seq
    ;

    -- �ŏ����ް��ð�����擾
    -- ���܂Ƃ߂��Ă���ꍇ���l������P_OrderSeq�Ÿ�ٰ�߉������ŏ����ް��ð�����擾����
    SELECT  P_OrderSeq
        ,   MIN(DataStatus)
    INTO    v_P_OrderSeq
        ,   v_DataStatus
    FROM    T_Order
    WHERE   (DataStatus IN (51, 61) OR (DataStatus = 91 AND CloseReason = 1))
    AND     P_OrderSeq  =   pi_order_seq
    GROUP BY
            P_OrderSeq
    ;

    -- ------------------------------
    -- 3.�����m�F�҂��̏ꍇ
    -- ------------------------------
    IF  v_DataStatus = 51   THEN
        -- ------------------------------
        -- 3-1.�����ް��擾
        -- ------------------------------
        SELECT  ClaimId                     -- ����ID
            ,   ClaimPattern                -- ���������
            ,   ClaimAmount                 -- �����z
            ,   UseAmountTotal              -- ���p�z���v
            ,   ClaimFee                    -- �����萔��
            ,   DamageInterestAmount        -- �x�����Q��
            ,   AdditionalClaimFee          -- �����ǉ��萔��
            ,   ClaimedBalance              -- �����c��
            ,   MinClaimAmount              -- �Œᐿ�����|�������z
            ,   MinUseAmount                -- �Œᐿ�����|���p�z
            ,   MinClaimFee                 -- �Œᐿ�����|�����萔��
            ,   MinDamageInterestAmount     -- �Œᐿ�����|�x�����Q��
            ,   MinAdditionalClaimFee       -- �Œᐿ�����|�����ǉ��萔��
        INTO    v_ClaimId
            ,   v_ClaimPattern
            ,   v_ClaimAmount
            ,   v_UseAmountTotal
            ,   v_ClaimFee
            ,   v_DamageInterestAmount
            ,   v_AdditionalClaimFee
            ,   v_ClaimedBalance
            ,   v_MinClaimAmount
            ,   v_MinUseAmount
            ,   v_MinClaimFee
            ,   v_MinDamageInterestAmount
            ,   v_MinAdditionalClaimFee
        FROM    T_ClaimControl
        WHERE   OrderSeq    =   pi_order_seq
        ;

        IF  no_data_found = 0   THEN
            SET po_ret_sts  =   -1;
            SET po_ret_msg  =   '�����Ώۂ̃f�[�^�����݂��܂���B';
            LEAVE proc;
        END IF;

        -- ------------------------------
        -- 3-2.���������v�Z
        -- ------------------------------
        /* -------------------------------------------------------------------------------------------
        -- 2015/08/03_����
        -- ���z�̏������@
        -- �ア�������������۰�ނ��邩 �������c���ƂȂ邽�߁AFROM�i�Œᐿ�����j�����������
        --  �܂�E�E�E
        --   �Œᐿ���z�i�萔�����j�������� �� ���p�z�������� �� �ŏI�����z�i�萔�����j��������
        --  1.�Œᐿ�����|�����ǉ��萔���iMinAdditionalClaimFee�j��������
        --  2.�Œᐿ�����|�x�����Q���iMinDamageInterestAmount�j��������
        --  3.�Œᐿ�����|�����萔���iMinClaimFee�j��������
        --  4.���p�z�iUseAmountTotal�j��������
        --  5.�����ǉ��萔���iAdditionalClaimFee�j�̍��z����������
        --  6.�x�����Q���iDamageInterestAmount�j�̍��z����������
        --  7.�����萔���iClaimFee�j�̍��z����������
        -- ------------------------------------------------------------------------------------------- */
        -- �ŏI�����z�ƍŒᐿ���z�̍��z���擾
        -- �����萔��
        SET v_diffClaimFee = v_ClaimFee - v_MinClaimFee;
        -- �x�����Q��
        SET v_diffDamageInterestAmount = v_DamageInterestAmount - v_MinDamageInterestAmount;
        -- �����ǉ��萔��
        SET v_diffAdditionalClaimFee = v_AdditionalClaimFee - v_MinAdditionalClaimFee;

        -- +++++++++++++++++++++++++++++++++++++++++++++
        -- 1. �Œᐿ�����|�����ǉ��萔�� ��������
        -- +++++++++++++++++++++++++++++++++++++++++++++
        -- �����z �� �Œᐿ�����|�����ǉ��萔�� �ȏ�̏ꍇ
        IF  pi_receipt_amount >= v_MinAdditionalClaimFee    THEN
            -- �������|�����ǉ��萔�� = �Œᐿ�����|�����ǉ��萔��
            SET v_CheckingAdditionalClaimFee = v_MinAdditionalClaimFee;
            -- �����z����������|�����ǉ��萔�������Z���ē����z�̎c�����擾
            SET v_CalculationAmount = pi_receipt_amount - v_CheckingAdditionalClaimFee;
        ELSE
            -- �������|�����ǉ��萔�� = �����z
            SET v_CheckingAdditionalClaimFee = pi_receipt_amount;
            -- �����z���S�� �������|�����ǉ��萔�� �Ȃ̂ŁA�c���� 0�i��ہj
            SET v_CalculationAmount = 0;
        END IF;

        -- 1.�Ŏc�������݂���ꍇ�͈ȉ��������s���B
        IF  v_CalculationAmount > 0 THEN
            -- +++++++++++++++++++++++++++++++++++++++++++++
            -- 2. �Œᐿ�����|�x�����Q�� ��������
            -- +++++++++++++++++++++++++++++++++++++++++++++
            -- 1.�̎c�� �� �Œᐿ�����|�x�����Q�� �ȏ�̏ꍇ
            IF  v_CalculationAmount >= v_MinDamageInterestAmount    THEN
                -- �������|�x�����Q�� = �Œᐿ�����|�x�����Q��
                SET v_CheckingDamageInterestAmount = v_MinDamageInterestAmount;
                -- 1.�̎c������������|�x�����Q�������Z���ē����z�̎c�����擾
                SET v_CalculationAmount = v_CalculationAmount - v_CheckingDamageInterestAmount;
            ELSE
                -- �������|�x�����Q�� = �c��
                SET v_CheckingDamageInterestAmount = v_CalculationAmount;
                -- 1.�̎c�����S�� �������|�x�����Q�� �Ȃ̂ŁA�c���� 0�i��ہj
                SET v_CalculationAmount = 0;
            END IF;
        END IF;

        -- 2.�Ŏc�������݂���ꍇ�͈ȉ��������s���B
        IF  v_CalculationAmount > 0 THEN
            -- +++++++++++++++++++++++++++++++++++++++++++++
            -- 3. �Œᐿ�����|�����萔�� ��������
            -- +++++++++++++++++++++++++++++++++++++++++++++
            -- 2.�̎c�� �� �Œᐿ�����|�����萔�� �ȏ�̏ꍇ
            IF  v_CalculationAmount >= v_MinClaimFee    THEN
                -- �������|�����萔�� = �Œᐿ�����|�����萔��
                SET v_CheckingClaimFee = v_MinClaimFee;
                -- 2.�̎c������������|�����萔�������Z���ē����z�̎c�����擾
                SET v_CalculationAmount = v_CalculationAmount - v_CheckingClaimFee;
            ELSE
                -- �������|�����萔�� = �c��
                SET v_CheckingClaimFee = v_CalculationAmount;
                -- 2.�̎c�����S�� �������|�����萔�� �Ȃ̂ŁA�c���� 0�i��ہj
                SET v_CalculationAmount = 0;
            END IF;
        END IF;

        -- 3.�Ŏc�������݂���ꍇ�͈ȉ��������s���B
        IF  v_CalculationAmount > 0 THEN
            -- +++++++++++++++++++++++++++++++++++++++++++++
            -- 4. ���p�z ��������
            -- +++++++++++++++++++++++++++++++++++++++++++++
            -- 3.�̎c�� �� ���p���v�z �ȏ�̏ꍇ
            IF  v_CalculationAmount >= v_UseAmountTotal THEN
                -- �������|���p�z = ���p���v�z
                SET v_CheckingUseAmount = v_UseAmountTotal;
                -- 3.�̎c������������|���p�z�����Z���ē����z�̎c�����擾
                SET v_CalculationAmount = v_CalculationAmount - v_CheckingUseAmount;
            -- ����ȊO�̏ꍇ
            ELSE
                -- �������|���p�z = �c��
                SET v_CheckingUseAmount = v_CalculationAmount;
                -- 3.�̎c�����S�� �������|���p�z �Ȃ̂ŁA�c���� 0�i��ہj
                SET v_CalculationAmount = 0;
            END IF;
        END IF;

        -- 4.�Ŏc�������݂���ꍇ�͈ȉ��������s���B
        IF  v_CalculationAmount > 0 THEN
            -- +++++++++++++++++++++++++++++++++++++++++++++
            -- 5. �����ǉ��萔���̍��z�� ��������
            -- +++++++++++++++++++++++++++++++++++++++++++++
            -- 4.�̎c�� �� �����ǉ��萔���̍��z�� �ȏ�̏ꍇ
            IF  v_CalculationAmount >= v_diffAdditionalClaimFee THEN
                -- �������|�����ǉ��萔�� �� �����ǉ��萔���̍��z �𑫂�����
                SET v_CheckingAdditionalClaimFee = v_CheckingAdditionalClaimFee + v_diffAdditionalClaimFee;
                -- 4.�̎c�����琿���ǉ��萔���̍��z�����Z���ē����z�̎c�����擾
                SET v_CalculationAmount = v_CalculationAmount - v_diffAdditionalClaimFee;
            -- ����ȊO�̏ꍇ
            ELSE
                -- �������|�����ǉ��萔�� �� 4.�̎c���𑫂�����
                SET v_CheckingAdditionalClaimFee = v_CheckingAdditionalClaimFee + v_CalculationAmount;
                -- 4.�̎c�����S�� �������|�����ǉ��萔�� �Ȃ̂ŁA�c���� 0�i��ہj
                SET v_CalculationAmount = 0;
            END IF;
        END IF;

        -- 5.�Ŏc�������݂���ꍇ�͈ȉ��������s���B
        IF  v_CalculationAmount > 0 THEN
            -- +++++++++++++++++++++++++++++++++++++++++++++
            -- 6. �x�����Q���̍��z�� ��������
            -- +++++++++++++++++++++++++++++++++++++++++++++
            -- 5.�̎c�� �� �x�����Q���̍��z�� �ȏ�̏ꍇ
            IF  v_CalculationAmount >= v_diffDamageInterestAmount   THEN
                -- �������|�x�����Q�� �� �x�����Q���̍��z �𑫂�����
                SET v_CheckingDamageInterestAmount = v_CheckingDamageInterestAmount + v_diffDamageInterestAmount;
                -- 5.�̎c������x�����Q���̍��z�����Z���ē����z�̎c�����擾
                SET v_CalculationAmount = v_CalculationAmount - v_diffDamageInterestAmount;
            -- ����ȊO�̏ꍇ
            ELSE
                -- �������|�x�����Q�� �� 5.�̎c���𑫂�����
                SET v_CheckingDamageInterestAmount = v_CheckingDamageInterestAmount + v_CalculationAmount;
                -- 5.�̎c�����S�� �������|�x�����Q�� �Ȃ̂ŁA�c���� 0�i��ہj
                SET v_CalculationAmount = 0;
            END IF;
        END IF;

        -- 6.�Ŏc�������݂���ꍇ�͈ȉ��������s���B
        IF  v_CalculationAmount > 0 THEN
            -- +++++++++++++++++++++++++++++++++++++++++++++
            -- 7. �����萔���̍��z�� ��������
            -- +++++++++++++++++++++++++++++++++++++++++++++
            -- 6.�̎c�� �� �����萔���̍��z �ȏ�̏ꍇ
            IF  v_CalculationAmount >= v_diffClaimFee   THEN
                -- �������|�����萔�� �� 6.�̎c���𑫂�����
                SET v_CheckingClaimFee = v_CheckingClaimFee + v_diffClaimFee;
                -- 6.�̎c�����琿���萔���̍��z�����Z���ē����z�̎c�����擾
                SET v_CalculationAmount = v_CalculationAmount - v_diffClaimFee;
            ELSE
                -- �������|�����萔�� �� 6.�̎c���𑫂�����
                SET v_CheckingClaimFee = v_CheckingClaimFee + v_CalculationAmount;
                -- 6.�̎c�����S�� �������|�����萔�� �Ȃ̂ŁA�c���� 0�i��ہj
                SET v_CalculationAmount = 0;
            END IF;
        END IF;

        -- 7.�Ŏc�������݂���ꍇ�͈ȉ��������s���B
        IF  v_CalculationAmount > 0 THEN
            -- �ߏ���Ƃ��� �������|���p�z �� 7.�̎c�� �𑫂�����
            SET v_CheckingUseAmount = v_CheckingUseAmount + v_CalculationAmount;
        END IF;

        -- ++++++++++++++++++++++++++++++++++++++++
        -- 8. �������|�������z���v �����߂�
        -- ++++++++++++++++++++++++++++++++++++++++
        SET v_CheckingClaimAmount = v_CheckingUseAmount + v_CheckingClaimFee + v_CheckingDamageInterestAmount + v_CheckingAdditionalClaimFee;

        -- ------------------------------
        -- 3-3.�c�������v�Z
        -- ------------------------------
        -- �c�����͐����z��������z�����Z���Ď擾����
        -- 1) �c�����|�c�����v
        SET v_BalanceClaimAmount = v_ClaimAmount - v_CheckingClaimAmount;

        -- 2) �c�����|���p�z
        SET v_BalanceUseAmount = v_UseAmountTotal - v_CheckingUseAmount;

        -- 3) �c�����|�����萔��
        SET v_BalanceClaimFee = v_ClaimFee - v_CheckingClaimFee;

        -- 4) �c�����|�x�����Q��
        SET v_BalanceDamageInterestAmount = v_DamageInterestAmount - v_CheckingDamageInterestAmount;

        -- 5) �c�����|�����ǉ��萔��
        SET v_BalanceAdditionalClaimFee = v_AdditionalClaimFee - v_CheckingAdditionalClaimFee;

        -- ------------------------------
        -- 3-4.�����ް��̍쐬
        -- ------------------------------
        INSERT
        INTO    T_ReceiptControl(   ReceiptProcessDate              -- ����������
                                ,   ReceiptDate                     -- �ڋq������
                                ,   ReceiptClass                    -- �����Ȗځi�������@�j
                                ,   ReceiptAmount                   -- ���z
                                ,   ClaimId                         -- ����ID
                                ,   OrderSeq                        -- ����SEQ
                                ,   CheckingUseAmount               -- �������|���p�z
                                ,   CheckingClaimFee                -- �������|�����萔��
                                ,   CheckingDamageInterestAmount    -- �������|�x�����Q��
                                ,   CheckingAdditionalClaimFee      -- �������|�����ǉ��萔��
                                ,   DailySummaryFlg                 -- �����X�V�׸�
                                ,   BranchBankId                    -- ��s�x�XID
                                ,   DepositDate                     -- �����\���
                                ,   ReceiptAgentId                  -- ���[��s���ID
                                ,   Receipt_Note                     -- ���l
                                ,   RegistDate                      -- �o�^����
                                ,   RegistId                        -- �o�^��
                                ,   UpdateDate                      -- �X�V����
                                ,   UpdateId                        -- �X�V��
                                ,   ValidFlg                        -- �L���׸�
                                )
                                VALUES
                                (   NOW()                           -- ����������
                                ,   pi_receipt_date                 -- �ڋq������
                                ,   pi_receipt_class                -- �����Ȗځi�������@�j
                                ,   pi_receipt_amount               -- ���z
                                ,   v_ClaimId                       -- ����ID
                                ,   pi_order_seq                    -- ����SEQ
                                ,   v_CheckingUseAmount             -- �������|���p�z
                                ,   v_CheckingClaimFee              -- �������|�����萔��
                                ,   v_CheckingDamageInterestAmount  -- �������|�x�����Q��
                                ,   v_CheckingAdditionalClaimFee    -- �������|�����ǉ��萔��
                                ,   0                               -- �����X�V�׸�
                                ,   pi_branch_bank_id               -- ��s�x�XID
                                ,   pi_deposit_date                 -- �����\���
                                ,   pi_receipt_agent_id             -- ���[��s���ID
                                ,   pi_receipt_note                    -- ���l
                                ,   NOW()                           -- �o�^����
                                ,   pi_user_id                      -- �o�^��
                                ,   NOW()                           -- �X�V����
                                ,   pi_user_id                      -- �X�V��
                                ,   1                               -- �L���׸�
                                );

        -- ------------------------------
        -- 3-4'.����Seq���擾
        -- ------------------------------
        SELECT  OrderSeq
            ,   MAX(ReceiptSeq)
        INTO    v_OrderSeq
            ,   v_ReceiptSeq
        FROM    T_ReceiptControl
        WHERE   OrderSeq = pi_order_seq
        GROUP BY
                OrderSeq
        ;

        -- ------------------------------
        -- 3-5.�Œᐿ���z > �����z �̏ꍇ
        -- ------------------------------
        IF  v_MinClaimAmount > pi_receipt_amount    THEN
            -- ------------------------------
            -- 3-5-1.�����ް��̍X�V
            -- ------------------------------
            UPDATE  T_ClaimControl
            SET     ClaimedBalance                  =   v_ClaimedBalance - v_CheckingClaimAmount        -- �����c��
                ,   LastProcessDate                 =   DATE(NOW())                                     -- �ŏI����������
                ,   LastReceiptSeq                  =   v_ReceiptSeq                                    -- �ŏI����SEQ
                ,   CheckingClaimAmount             =   v_CheckingClaimAmount                           -- �������|�������z���v
                ,   CheckingUseAmount               =   v_CheckingUseAmount                             -- �������|���p�z
                ,   CheckingClaimFee                =   v_CheckingClaimFee                              -- �������|�����萔��
                ,   CheckingDamageInterestAmount    =   v_CheckingDamageInterestAmount                  -- �������|�x�����Q��
                ,   CheckingAdditionalClaimFee      =   v_CheckingAdditionalClaimFee                    -- �������|�����ǉ��萔��
                ,   BalanceClaimAmount              =   v_BalanceClaimAmount                            -- �c�����|�c�����v
                ,   BalanceUseAmount                =   v_BalanceUseAmount                              -- �c�����|���p�z
                ,   BalanceClaimFee                 =   v_BalanceClaimFee                               -- �c�����|�����萔��
                ,   BalanceDamageInterestAmount     =   v_BalanceDamageInterestAmount                   -- �c�����|�x�����Q��
                ,   BalanceAdditionalClaimFee       =   v_BalanceAdditionalClaimFee                     -- �c�����|�����ǉ��萔��
                ,   ReceiptAmountTotal              =   ReceiptAmountTotal + pi_receipt_amount          -- �����z���v
                ,   UpdateDate                      =   NOW()
                ,   UpdateId                        =   pi_user_id
            WHERE   ClaimId =   v_ClaimId
            ;

            -- ------------------------------
            -- 3-5-2.�����ް��̍X�V
            -- ------------------------------
            UPDATE  T_Order
            SET     DataStatus  =   61              -- �ް��ð���i�ꕔ�����j
                ,   Rct_Status  =   1               -- �ڋq����ð���i�����ς݁j
                ,   UpdateDate  =   NOW()
                ,   UpdateId    =   pi_user_id
            WHERE   P_OrderSeq  =   pi_order_seq
            AND     Cnl_Status  =   0
            ;
        -- ------------------------------
        -- 3-6.�Œᐿ���z = �����z �̏ꍇ
        -- ------------------------------
        ELSEIF  v_MinClaimAmount = pi_receipt_amount    THEN
            -- ------------------------------
            -- 3-6-1.�Œᐿ���z = �ŏI�����z �̏ꍇ
            -- ------------------------------
            IF  v_MinClaimAmount = v_ClaimAmount    THEN
                -- ------------------------------
                -- 3-6-1-1.�����ް��̍X�V
                -- ------------------------------
                UPDATE  T_ClaimControl
                SET     ClaimedBalance                  =   v_ClaimedBalance - v_CheckingClaimAmount        -- �����c��
                    ,   LastProcessDate                 =   DATE(NOW())                                     -- �ŏI����������
                    ,   LastReceiptSeq                  =   v_ReceiptSeq                                    -- �ŏI����SEQ
                    ,   CheckingClaimAmount             =   v_CheckingClaimAmount                           -- �������|�������z���v
                    ,   CheckingUseAmount               =   v_CheckingUseAmount                             -- �������|���p�z
                    ,   CheckingClaimFee                =   v_CheckingClaimFee                              -- �������|�����萔��
                    ,   CheckingDamageInterestAmount    =   v_CheckingDamageInterestAmount                  -- �������|�x�����Q��
                    ,   CheckingAdditionalClaimFee      =   v_CheckingAdditionalClaimFee                    -- �������|�����ǉ��萔��
                    ,   BalanceClaimAmount              =   v_BalanceClaimAmount                            -- �c�����|�c�����v
                    ,   BalanceUseAmount                =   v_BalanceUseAmount                              -- �c�����|���p�z
                    ,   BalanceClaimFee                 =   v_BalanceClaimFee                               -- �c�����|�����萔��
                    ,   BalanceDamageInterestAmount     =   v_BalanceDamageInterestAmount                   -- �c�����|�x�����Q��
                    ,   BalanceAdditionalClaimFee       =   v_BalanceAdditionalClaimFee                     -- �c�����|�����ǉ��萔��
                    ,   ReceiptAmountTotal              =   ReceiptAmountTotal + pi_receipt_amount          -- �����z���v
                    ,   UpdateDate                      =   NOW()
                    ,   UpdateId                        =   pi_user_id
                WHERE   ClaimId =   v_ClaimId
                ;

            -- ------------------------------
            -- 3-6-2.�ŏI�����z > �Œᐿ���z �̏ꍇ
            -- ------------------------------
            ELSEIF  v_ClaimAmount > v_MinClaimAmount    THEN
                -- ------------------------------
                -- 3-6-2-1.�G�������̎擾
                -- ------------------------------
                -- �ŏI�����z����������z�����Z���č������Z�o����
                -- 1) ���p�z
                SET v_SundryUseAmount = v_UseAmountTotal - v_CheckingUseAmount;

                -- 2) �����萔��
                SET v_SundryClaimFee = v_ClaimFee - v_CheckingClaimFee;

                -- 3) �x�����Q��
                SET v_SundryDamageInterestAmount = v_DamageInterestAmount - v_CheckingDamageInterestAmount;

                -- 4) �����ǉ��萔��
                SET v_SundryAdditionalClaimFee = v_AdditionalClaimFee - v_CheckingAdditionalClaimFee;

                -- 5) ���z
                SET v_SundryAmount = v_SundryUseAmount + v_SundryClaimFee + v_SundryDamageInterestAmount + v_SundryAdditionalClaimFee;

                -- ------------------------------
                -- 3-6-2-2.�G�����ް��̍쐬
                -- ------------------------------
                INSERT
                INTO    T_SundryControl(    ProcessDate                     -- ��������
                                        ,   SundryType                      -- ��ށi�G�����^�G�����j
                                        ,   SundryAmount                    -- ���z
                                        ,   SundryClass                     -- �G�����E�G�����Ȗ�
                                        ,   OrderSeq                        -- ����SEQ
                                        ,   OrderId                         -- ����ID
                                        ,   ClaimId                         -- ����ID
                                        ,   Note                            -- ���l
                                        ,   CheckingUseAmount               -- �������|���p�z
                                        ,   CheckingClaimFee                -- �������|�����萔��
                                        ,   CheckingDamageInterestAmount    -- �������|�x�����Q��
                                        ,   CheckingAdditionalClaimFee      -- �������|�����ǉ��萔��
                                        ,   DailySummaryFlg                 -- �����X�V�׸�
                                        ,   RegistDate                      -- �o�^����
                                        ,   RegistId                        -- �o�^��
                                        ,   UpdateDate                      -- �X�V����
                                        ,   UpdateId                        -- �X�V��
                                        ,   ValidFlg                        -- �L���׸�
                                       )
                                       VALUES
                                       (    DATE(NOW())                     -- ��������
                                        ,   1                               -- ��ށi�G�����^�G�����j
                                        ,   v_SundryAmount                  -- ���z
                                        ,   99                              -- �G�����E�G�����Ȗ�
                                        ,   pi_order_seq                    -- ����SEQ
                                        ,   v_OrderId                       -- ����ID
                                        ,   v_ClaimId                       -- ����ID
                                        ,   NULL                            -- ���l
                                        ,   v_SundryUseAmount               -- �������|���p�z
                                        ,   v_SundryClaimFee                -- �������|�����萔��
                                        ,   v_SundryDamageInterestAmount    -- �������|�x�����Q��
                                        ,   v_SundryAdditionalClaimFee      -- �������|�����ǉ��萔��
                                        ,   0                               -- �����X�V�׸�
                                        ,   NOW()                           -- �o�^����
                                        ,   pi_user_id                      -- �o�^��
                                        ,   NOW()                           -- �X�V����
                                        ,   pi_user_id                      -- �X�V��
                                        ,   1                               -- �L���׸�
                                       );

                -- ------------------------------
                -- 3-6-2-3.�����ް��̍X�V
                -- ------------------------------
                UPDATE  T_ClaimControl
                SET     ClaimedBalance                  =   v_ClaimedBalance - v_CheckingClaimAmount - v_SundryAmount           -- �����c��
                    ,   LastProcessDate                 =   DATE(NOW())                                                         -- �ŏI����������
                    ,   LastReceiptSeq                  =   v_ReceiptSeq                                                        -- �ŏI����SEQ
                    ,   CheckingClaimAmount             =   v_CheckingClaimAmount + v_SundryAmount                              -- �������|�������z���v
                    ,   CheckingUseAmount               =   v_CheckingUseAmount + v_SundryUseAmount                             -- �������|���p�z
                    ,   CheckingClaimFee                =   v_CheckingClaimFee + v_SundryClaimFee                               -- �������|�����萔��
                    ,   CheckingDamageInterestAmount    =   v_CheckingDamageInterestAmount + v_SundryDamageInterestAmount       -- �������|�x�����Q��
                    ,   CheckingAdditionalClaimFee      =   v_CheckingAdditionalClaimFee + v_SundryAdditionalClaimFee           -- �������|�����ǉ��萔��
                    ,   BalanceClaimAmount              =   v_BalanceClaimAmount - v_SundryAmount                               -- �c�����|�c�����v
                    ,   BalanceUseAmount                =   v_BalanceUseAmount - v_SundryUseAmount                              -- �c�����|���p�z
                    ,   BalanceClaimFee                 =   v_BalanceClaimFee - v_SundryClaimFee                                -- �c�����|�����萔��
                    ,   BalanceDamageInterestAmount     =   v_BalanceDamageInterestAmount - v_SundryDamageInterestAmount        -- �c�����|�x�����Q��
                    ,   BalanceAdditionalClaimFee       =   v_BalanceAdditionalClaimFee - v_SundryAdditionalClaimFee            -- �c�����|�����ǉ��萔��
                    ,   ReceiptAmountTotal              =   ReceiptAmountTotal + pi_receipt_amount                              -- �����z���v
                    ,   SundryLossTotal                 =   SundryLossTotal + v_SundryAmount                                    -- �G�������v
                    ,   UpdateDate                      =   NOW()
                    ,   UpdateId                        =   pi_user_id
                WHERE   ClaimId =   v_ClaimId
                ;
            END IF;

            -- ------------------------------
            -- 3-6-3.�����ް��̍X�V
            -- ------------------------------
            UPDATE  T_Order
            SET     DataStatus  =   91      -- �ް��ð���i�۰�ށj
                ,   CloseReason =   1       -- �۰�ޗ��R�i�����ςݐ���۰�ށj
                ,   Rct_Status  =   1       -- �ڋq����ð���i�����ς݁j
                ,   UpdateDate  =   NOW()
                ,   UpdateId    =   pi_user_id
            WHERE   P_OrderSeq  =   pi_order_seq
            AND     Cnl_Status  =   0
            ;
        -- ------------------------------
        -- 3-7.�ŏI�����z > �����z > �Œᐿ���z �̏ꍇ
        -- ------------------------------
        ELSEIF  v_ClaimAmount > pi_receipt_amount AND pi_receipt_amount > v_MinClaimAmount  THEN
            -- ------------------------------
            -- 3-7-1.�G�������̎擾
            -- ------------------------------
            -- �ŏI�������z����������z�����Z���č������Z�o
            -- 1) ���p�z
            SET v_SundryUseAmount = v_UseAmountTotal - v_CheckingUseAmount;

            -- 2) �����萔��
            SET v_SundryClaimFee = v_ClaimFee - v_CheckingClaimFee;

            -- 3) �x�����Q��
            SET v_SundryDamageInterestAmount = v_DamageInterestAmount - v_CheckingDamageInterestAmount;

            -- 4) �����ǉ��萔��
            SET v_SundryAdditionalClaimFee = v_AdditionalClaimFee - v_CheckingAdditionalClaimFee;

            -- 5) ���z
            SET v_SundryAmount = v_SundryUseAmount + v_SundryClaimFee + v_SundryDamageInterestAmount + v_SundryAdditionalClaimFee;

            -- ------------------------------
            -- 3-7-2.�G�����ް��̍쐬
            -- ------------------------------
            INSERT
            INTO    T_SundryControl(    ProcessDate                     -- ��������
                                    ,   SundryType                      -- ��ށi�G�����^�G�����j
                                    ,   SundryAmount                    -- ���z
                                    ,   SundryClass                     -- �G�����E�G�����Ȗ�
                                    ,   OrderSeq                        -- ����SEQ
                                    ,   OrderId                         -- ����ID
                                    ,   ClaimId                         -- ����ID
                                    ,   Note                            -- ���l
                                    ,   CheckingUseAmount               -- �������|���p�z
                                    ,   CheckingClaimFee                -- �������|�����萔��
                                    ,   CheckingDamageInterestAmount    -- �������|�x�����Q��
                                    ,   CheckingAdditionalClaimFee      -- �������|�����ǉ��萔��
                                    ,   DailySummaryFlg                 -- �����X�V�׸�
                                    ,   RegistDate                      -- �o�^����
                                    ,   RegistId                        -- �o�^��
                                    ,   UpdateDate                      -- �X�V����
                                    ,   UpdateId                        -- �X�V��
                                    ,   ValidFlg                        -- �L���׸�
                                   )
                                   VALUES
                                   (    DATE(NOW())                     -- ��������
                                    ,   1                               -- ��ށi�G�����^�G�����j
                                    ,   v_SundryAmount                  -- ���z
                                    ,   99                              -- �G�����E�G�����Ȗ�
                                    ,   pi_order_seq                    -- ����SEQ
                                    ,   v_OrderId                       -- ����ID
                                    ,   v_ClaimId                       -- ����ID
                                    ,   NULL                            -- ���l
                                    ,   v_SundryUseAmount               -- �������|���p�z
                                    ,   v_SundryClaimFee                -- �������|�����萔��
                                    ,   v_SundryDamageInterestAmount    -- �������|�x�����Q��
                                    ,   v_SundryAdditionalClaimFee      -- �������|�����ǉ��萔��
                                    ,   0                               -- �����X�V�׸�
                                    ,   NOW()                           -- �o�^����
                                    ,   pi_user_id                      -- �o�^��
                                    ,   NOW()                           -- �X�V����
                                    ,   pi_user_id                      -- �X�V��
                                    ,   1                               -- �L���׸�
                                   );

            -- ------------------------------
            -- 3-7-3.�����ް��̍X�V
            -- ------------------------------
            UPDATE  T_ClaimControl
            SET     ClaimedBalance                  =   v_ClaimedBalance - v_CheckingClaimAmount - v_SundryAmount           -- �����c��
                ,   LastProcessDate                 =   DATE(NOW())                                                         -- �ŏI����������
                ,   LastReceiptSeq                  =   v_ReceiptSeq                                                        -- �ŏI����SEQ
                ,   CheckingClaimAmount             =   v_CheckingClaimAmount + v_SundryAmount                              -- �������|�������z���v
                ,   CheckingUseAmount               =   v_CheckingUseAmount + v_SundryUseAmount                             -- �������|���p�z
                ,   CheckingClaimFee                =   v_CheckingClaimFee + v_SundryClaimFee                               -- �������|�����萔��
                ,   CheckingDamageInterestAmount    =   v_CheckingDamageInterestAmount + v_SundryDamageInterestAmount       -- �������|�x�����Q��
                ,   CheckingAdditionalClaimFee      =   v_CheckingAdditionalClaimFee + v_SundryAdditionalClaimFee           -- �������|�����ǉ��萔��
                ,   BalanceClaimAmount              =   v_BalanceClaimAmount - v_SundryAmount                               -- �c�����|�c�����v
                ,   BalanceUseAmount                =   v_BalanceUseAmount - v_SundryUseAmount                              -- �c�����|���p�z
                ,   BalanceClaimFee                 =   v_BalanceClaimFee - v_SundryClaimFee                                -- �c�����|�����萔��
                ,   BalanceDamageInterestAmount     =   v_BalanceDamageInterestAmount - v_SundryDamageInterestAmount        -- �c�����|�x�����Q��
                ,   BalanceAdditionalClaimFee       =   v_BalanceAdditionalClaimFee - v_SundryAdditionalClaimFee            -- �c�����|�����ǉ��萔��
                ,   ReceiptAmountTotal              =   ReceiptAmountTotal + pi_receipt_amount                              -- �����z���v
                ,   SundryLossTotal                 =   SundryLossTotal + v_SundryAmount                                    -- �G�������v
                ,   UpdateDate                      =   NOW()
                ,   UpdateId                        =   pi_user_id
            WHERE   ClaimId =   v_ClaimId
            ;

            -- ------------------------------
            -- 3-7-4.�����ް��̍X�V
            -- ------------------------------
            UPDATE  T_Order
            SET     DataStatus  =   91      -- �ް��ð���i�۰�ށj
                ,   CloseReason =   1       -- �۰�ޗ��R�i�����ςݐ���۰�ށj
                ,   Rct_Status  =   1       -- �ڋq����ð���i�����ς݁j
                ,   UpdateDate  =   NOW()
                ,   UpdateId    =   pi_user_id
            WHERE   P_OrderSeq  =   pi_order_seq
            AND     Cnl_Status  =   0
            ;

        -- ------------------------------
        -- 3-8.�����z >= �ŏI�����z �̏ꍇ
        -- ------------------------------
        ELSEIF  pi_receipt_amount >= v_ClaimAmount  THEN
            -- ------------------------------
            -- 3-8-1.�����ް��̍X�V
            -- ------------------------------
            UPDATE  T_ClaimControl
            SET     ClaimedBalance                  =   v_ClaimedBalance - v_CheckingClaimAmount    -- �����c��
                ,   LastProcessDate                 =   DATE(NOW())                                 -- �ŏI����������
                ,   LastReceiptSeq                  =   v_ReceiptSeq                                -- �ŏI����SEQ
                ,   CheckingClaimAmount             =   v_CheckingClaimAmount                       -- �������|�����z���v
                ,   CheckingUseAmount               =   v_CheckingUseAmount                         -- �������|���p�z
                ,   CheckingClaimFee                =   v_CheckingClaimFee                          -- �������|�����萔��
                ,   CheckingDamageInterestAmount    =   v_CheckingDamageInterestAmount              -- �������|�x�����Q��
                ,   CheckingAdditionalClaimFee      =   v_CheckingAdditionalClaimFee                -- �������|�����ǉ��萔��
                ,   BalanceClaimAmount              =   v_BalanceClaimAmount                        -- �c�����|�c�����v
                ,   BalanceUseAmount                =   v_BalanceUseAmount                          -- �c�����|���p�z
                ,   BalanceClaimFee                 =   v_BalanceClaimFee                           -- �c�����|�����萔��
                ,   BalanceDamageInterestAmount     =   v_BalanceDamageInterestAmount               -- �c�����|�x�����Q��
                ,   BalanceAdditionalClaimFee       =   v_BalanceAdditionalClaimFee                 -- �c�����|�����ǉ��萔��
                ,   ReceiptAmountTotal              =   ReceiptAmountTotal + pi_receipt_amount      -- �����ϊz
                ,   UpdateDate                      =   NOW()
                ,   UpdateId                        =   pi_user_id
            WHERE   ClaimId =   v_ClaimId
            ;

            -- --------------------------
            -- 3-8-2.�����ް��̍X�V
            -- --------------------------
            UPDATE  T_Order
            SET     DataStatus  =   91      -- �ް��ð���i�۰�ށj
                ,   CloseReason =   1       -- �۰�ޗ��R�i�����ςݐ���۰�ށj
                ,   Rct_Status  =   1       -- �ڋq����ð���i�����ς݁j
                ,   UpdateDate  =   NOW()
                ,   UpdateId    =   pi_user_id
            WHERE   P_OrderSeq  =   pi_order_seq
            AND     Cnl_Status  =   0
            ;
        END IF;

    -- ------------------------------
    -- 4.�ꕔ�����̏ꍇ
    -- ------------------------------
    ELSEIF  v_DataStatus = 61   THEN
        -- ------------------------------
        -- 4-1.�����ς݂��ް����擾
        -- ------------------------------
        SELECT  SUM(ReceiptAmount)                   -- ���z
            ,   SUM(CheckingUseAmount)               -- �������|���p�z
            ,   SUM(CheckingClaimFee)                -- �������|�����萔��
            ,   SUM(CheckingDamageInterestAmount)    -- �������|�x�����Q��
            ,   SUM(CheckingAdditionalClaimFee)      -- �������|�����ǉ��萔��
        INTO    v_ReceiptAmount
            ,   v_ReceiptUseAmount
            ,   v_ReceiptClaimFee
            ,   v_ReceiptDamageInterestAmount
            ,   v_ReceiptAdditionalClaimFee
        FROM    T_ReceiptControl
        WHERE   OrderSeq    =   pi_order_seq
        ;

        IF  no_data_found = 0   THEN
            SET po_ret_sts  =   -1;
            SET po_ret_msg  =   '�����ς݂̃f�[�^�����݂��܂���B';
            LEAVE proc;
        END IF;

        -- ------------------------------
        -- 4-2.�����ް��擾
        -- ------------------------------
        SELECT  ClaimId                         -- ����ID
            ,   ClaimAmount                     -- �����z
            ,   UseAmountTotal                  -- ���p�z���v
            ,   ClaimFee                        -- �����萔��
            ,   DamageInterestAmount            -- �x�����Q��
            ,   AdditionalClaimFee              -- �����ǉ��萔��
            ,   ClaimedBalance                  -- �����c��
            ,   MinClaimAmount                  -- �Œᐿ�����|�������z
            ,   MinUseAmount                    -- �Œᐿ�����|���p�z
            ,   MinClaimFee                     -- �Œᐿ�����|�����萔��
            ,   MinDamageInterestAmount         -- �Œᐿ�����|�x�����Q��
            ,   MinAdditionalClaimFee           -- �Œᐿ�����|�����ǉ��萔��
        INTO    v_ClaimId
            ,   v_ClaimAmount
            ,   v_UseAmountTotal
            ,   v_ClaimFee
            ,   v_DamageInterestAmount
            ,   v_AdditionalClaimFee
            ,   v_ClaimedBalance
            ,   v_MinClaimAmount
            ,   v_MinUseAmount
            ,   v_MinClaimFee
            ,   v_MinDamageInterestAmount
            ,   v_MinAdditionalClaimFee
        FROM    T_ClaimControl
        WHERE   OrderSeq    =   pi_order_seq
        ;

        IF  no_data_found = 0   THEN
            SET po_ret_sts  =   -1;
            SET po_ret_msg  =   '�����Ώۂ̃f�[�^�����݂��܂���B';
            LEAVE proc;
        END IF;

        -- ------------------------------
        -- 4-3.���������v�Z
        -- ------------------------------
        /* -------------------------------------------------------------------------------------------
        -- 2015/08/04_����
        -- ���z�̏������@
        --  �ꕔ�����̏ꍇ�A�����ς݂̋��z�����݂��邽�߁A�������@�ɒ��ӂ��K�v�B
        --  �ア�������������۰�ނ��邩 �̏����͓����m�F�҂��̂Ƃ��ƕύX�͂Ȃ��B
        --   �܂�E�E�E�����ς݊z���l�����AFROM�i�Œᐿ�����j����������ށA���������B
        --    �Œᐿ���z�i�萔�����j�������� �� ���p�z�������� �� �ŏI�����z�i�萔�����j��������
        --  1.�Œᐿ�����|�����ǉ��萔���iMinAdditionalClaimFee�j��������
        --  2.�Œᐿ�����|�x�����Q���iMinDamageInterestAmount�j��������
        --  3.�Œᐿ�����|�����萔���iMinClaimFee�j��������
        --  4.���p�z�iUseAmountTotal�j��������
        --  5.�����ǉ��萔���iAdditionalClaimFee�j�̍��z����������
        --  6.�x�����Q���iDamageInterestAmount�j�̍��z����������
        --  7.�����萔���iClaimFee�j�̍��z����������
        -- 2016/02/04_�ǋL
        --  ���z�����i�Œᐿ�����ȉ��̓����j�̏ꍇ�A����ۼޯ��ł͎c����񂪂��������Ȃ�B
        --   �� �����ς݂̋��z�ȊO�ɁA��������z���������z�̔�������Ƃ���K�v����B
        -- ------------------------------------------------------------------------------------------- */
        -- �ŏI�����z�ƍŒᐿ���z�̍��z���擾
        -- �����萔��
        SET v_diffClaimFee = v_ClaimFee - v_MinClaimFee;
        -- �x�����Q��
        SET v_diffDamageInterestAmount = v_DamageInterestAmount - v_MinDamageInterestAmount;
        -- �����ǉ��萔��
        SET v_diffAdditionalClaimFee = v_AdditionalClaimFee - v_MinAdditionalClaimFee;

        -- +++++++++++++++++++++++++++++++++++++++++++++
        -- 1. �Œᐿ�����|�����ǉ��萔�� ��������
        -- +++++++++++++++++++++++++++++++++++++++++++++
        -- �����ς݂̐����ǉ��萔�� �� �Œᐿ�����|�����ǉ��萔�� �ȏ�̏ꍇ
        IF  v_ReceiptAdditionalClaimFee >= v_MinAdditionalClaimFee  THEN
            -- �������|�����ǉ��萔���ɑ΂���ǉ������͂Ȃ�
            SET v_CheckingAdditionalClaimFee = 0;
            -- �c���͓����z
            SET v_CalculationAmount = pi_receipt_amount;
        -- ����ȊO�̏ꍇ
        ELSE
            -- �����z �� �Œᐿ�����|�����ǉ��萔�� ���� �����ς݂̐����ǉ��萔�������Z�������� �ȏ�̏ꍇ
            IF  pi_receipt_amount >= v_MinAdditionalClaimFee - v_ReceiptAdditionalClaimFee  THEN
                -- �Œᐿ�����|�����ǉ��萔�� ���� �����ς݂̐����ǉ��萔�������Z���ď������|�����ǉ��萔�� ���Z�o
                SET v_CheckingAdditionalClaimFee = v_MinAdditionalClaimFee - v_ReceiptAdditionalClaimFee;
            ELSE
                -- �������|�����ǉ��萔�� �� �����z
                SET v_CheckingAdditionalClaimFee = pi_receipt_amount;
            END IF;
            -- �����z ���� �������|�����ǉ��萔�������Z���Ďc�����擾
            SET v_CalculationAmount = pi_receipt_amount - v_CheckingAdditionalClaimFee;
        END IF;

        -- 1.�Ŏc�������݂���ꍇ�͈ȉ��������s���B
        IF  v_CalculationAmount > 0 THEN
            -- +++++++++++++++++++++++++++++++++++++++++++++
            -- 2. �Œᐿ�����|�x�����Q�� ��������
            -- +++++++++++++++++++++++++++++++++++++++++++++
            -- �����ς݂̒x�����Q�� �� �Œᐿ�����|�x�����Q�� �ȏ�̏ꍇ
            IF  v_ReceiptDamageInterestAmount >= v_MinDamageInterestAmount  THEN
                -- �������|�x�����Q���ɑ΂���ǉ������͂Ȃ�
                SET v_CheckingDamageInterestAmount = 0;
                -- �c����1.�̎c��
                SET v_CalculationAmount = v_CalculationAmount;
            -- ����ȊO�̏ꍇ
            ELSE
                -- 1.�̎c�� �� �Œᐿ�����|�x�����Q�� ���� �����ς݂̒x�����Q�������Z�������� �ȏ�̏ꍇ
                IF  v_CalculationAmount >= v_MinDamageInterestAmount - v_ReceiptDamageInterestAmount    THEN
                    -- �Œᐿ�����|�x�����Q�� ���� �����ς݂̒x�����Q�������Z���� �������|�x�����Q�� ���Z�o
                    SET v_CheckingDamageInterestAmount = v_MinDamageInterestAmount - v_ReceiptDamageInterestAmount;
                ELSE
                    -- �������|�x�����Q�� �� 1.�̎c��
                    SET v_CheckingDamageInterestAmount = v_CalculationAmount;
                END IF;
                -- 1.�̎c������������|�x�����Q�������Z���ē����z�̎c�����擾
                SET v_CalculationAmount = v_CalculationAmount - v_CheckingDamageInterestAmount;
            END IF;
        END IF;

        -- 2.�Ŏc�������݂���ꍇ�͈ȉ��������s���B
        IF  v_CalculationAmount > 0 THEN
            -- +++++++++++++++++++++++++++++++++++++++++++++
            -- 3. �Œᐿ�����|�����萔�� ��������
            -- +++++++++++++++++++++++++++++++++++++++++++++
            -- �����ς݂̐����萔�� �� �Œᐿ�����|�����萔�� �ȏ�̏ꍇ
            IF  v_ReceiptClaimFee >= v_MinClaimFee  THEN
                -- �������|�����萔���ɑ΂���ǉ������͂Ȃ�
                SET v_CheckingClaimFee = 0;
                -- �c����2.�̎c��
                SET v_CalculationAmount = v_CalculationAmount;
            -- ����ȊO�̏ꍇ
            ELSE
                -- 2.�̎c�� �� �Œᐿ�����|�����萔�� ���� �����ς݂̐����萔�������Z�������� �ȏ�̏ꍇ
                IF  v_CalculationAmount >= v_MinClaimFee  - v_ReceiptClaimFee   THEN
                    -- �Œᐿ�����|�����萔�� ���� �����ς݂̐����萔�������Z���� �������|�����萔�� ���Z�o
                    SET v_CheckingClaimFee = v_MinClaimFee - v_ReceiptClaimFee;
                ELSE
                    -- �������|�����萔�� �� 2.�̎c��
                    SET v_CheckingClaimFee = v_CalculationAmount;
                END IF;
                -- 2.�̎c�� ����������|�����萔�������Z���ē����z�̎c�����擾
                SET v_CalculationAmount = v_CalculationAmount - v_CheckingClaimFee;
            END IF;
        END IF;

        -- 3.�Ŏc�������݂���ꍇ�͈ȉ��������s���B
        IF  v_CalculationAmount > 0 THEN
            -- +++++++++++++++++++++++++++++++++++++++++++++
            -- 4. ���p�z ��������
            -- +++++++++++++++++++++++++++++++++++++++++++++
            -- 3.�̎c�� �� ���p�z���v�Ɠ����ς݂̗��p�z�̍��� �ȏ�̏ꍇ
            IF  v_CalculationAmount >= v_UseAmountTotal - v_ReceiptUseAmount    THEN
                -- �������|���p�z �� ���p�z���v�Ɠ����ς݂̗��p�z�̍���
                SET v_CheckingUseAmount = v_UseAmountTotal - v_ReceiptUseAmount;
                -- 3.�̎c�� ����������|���p�z�����Z���ē����z�̎c�����擾
                SET v_CalculationAmount = v_CalculationAmount - v_CheckingUseAmount;
            -- ����ȊO�̏ꍇ
            ELSE
                -- �������|���p�z �� 3.�̎c��
                SET v_CheckingUseAmount = v_CalculationAmount;
                -- 3.�̎c�����S�� �������|���p�z �Ȃ̂ŁA�c���� 0�i��ہj
                SET v_CalculationAmount = 0;
            END IF;
        END IF;

        -- 4.�Ŏc�������݂���ꍇ�͈ȉ��������s���B
        IF  v_CalculationAmount > 0 THEN
            -- +++++++++++++++++++++++++++++++++++++++++++++
            -- 5. �����ǉ��萔���̍��z�� ��������
            -- +++++++++++++++++++++++++++++++++++++++++++++
            -- 4.�̎c�� �� �����ǉ��萔���̍��z�� �ȏ�̏ꍇ
            IF  v_CalculationAmount >= v_diffAdditionalClaimFee THEN
                -- �������|�����ǉ��萔�� �� �����ǉ��萔���̍��z �𑫂�����
                SET v_CheckingAdditionalClaimFee = v_CheckingAdditionalClaimFee + v_diffAdditionalClaimFee;
                -- 4.�̎c�����琿���ǉ��萔���̍��z�����Z���ē����z�̎c�����擾
                SET v_CalculationAmount = v_CalculationAmount - v_diffAdditionalClaimFee;
            -- ����ȊO�̏ꍇ
            ELSE
                -- �������|�����ǉ��萔�� �� 4.�̎c���𑫂�����
                SET v_CheckingAdditionalClaimFee = v_CheckingAdditionalClaimFee + v_CalculationAmount;
                -- 4.�̎c�����S�� �������|�����ǉ��萔�� �Ȃ̂ŁA�c���� 0�i��ہj
                SET v_CalculationAmount = 0;
            END IF;
        END IF;

        -- 5.�Ŏc�������݂���ꍇ�͈ȉ��������s���B
        IF  v_CalculationAmount > 0 THEN
            -- +++++++++++++++++++++++++++++++++++++++++++++
            -- 6. �x�����Q���̍��z�� ��������
            -- +++++++++++++++++++++++++++++++++++++++++++++
            -- 5.�̎c�� �� �x�����Q���̍��z�� �ȏ�̏ꍇ
            IF  v_CalculationAmount >= v_diffDamageInterestAmount   THEN
                -- �������|�x�����Q�� �� �x�����Q���̍��z �𑫂�����
                SET v_CheckingDamageInterestAmount = v_CheckingDamageInterestAmount + v_diffDamageInterestAmount;
                -- 5.�̎c������x�����Q���̍��z�����Z���ē����z�̎c�����擾
                SET v_CalculationAmount = v_CalculationAmount - v_diffDamageInterestAmount;
            -- ����ȊO�̏ꍇ
            ELSE
                -- �������|�x�����Q�� �� 5.�̎c���𑫂�����
                SET v_CheckingDamageInterestAmount = v_CheckingDamageInterestAmount + v_CalculationAmount;
                -- 5.�̎c�����S�� �������|�x�����Q�� �Ȃ̂ŁA�c���� 0�i��ہj
                SET v_CalculationAmount = 0;
            END IF;
        END IF;

        -- 6.�Ŏc�������݂���ꍇ�͈ȉ��������s���B
        IF  v_CalculationAmount > 0 THEN
            -- +++++++++++++++++++++++++++++++++++++++++++++
            -- 7. �����萔���̍��z�� ��������
            -- +++++++++++++++++++++++++++++++++++++++++++++
            -- 6.�̎c�� �� �����萔���̍��z �ȏ�̏ꍇ
            IF  v_CalculationAmount >= v_diffClaimFee   THEN
                -- �������|�����萔�� �� 6.�̎c���𑫂�����
                SET v_CheckingClaimFee = v_CheckingClaimFee + v_diffClaimFee;
                -- 6.�̎c�����琿���萔���̍��z�����Z���ē����z�̎c�����擾
                SET v_CalculationAmount = v_CalculationAmount - v_diffClaimFee;
            ELSE
                -- �������|�����萔�� �� 6.�̎c���𑫂�����
                SET v_CheckingClaimFee = v_CheckingClaimFee + v_CalculationAmount;
                -- 6.�̎c�����S�� �������|�����萔�� �Ȃ̂ŁA�c���� 0�i��ہj
                SET v_CalculationAmount = 0;
            END IF;
        END IF;

        -- 7.�Ŏc�������݂���ꍇ�͈ȉ��������s���B
        IF  v_CalculationAmount > 0 THEN
            -- �ߏ���Ƃ��� �������|���p�z �� 7.�̎c�� �𑫂�����
            SET v_CheckingUseAmount = v_CheckingUseAmount + v_CalculationAmount;
        END IF;

        -- ++++++++++++++++++++++++++++++++++++++++
        -- 8. �������|�������z���v �����߂�
        -- ++++++++++++++++++++++++++++++++++++++++
        SET v_CheckingClaimAmount = v_CheckingUseAmount + v_CheckingClaimFee + v_CheckingDamageInterestAmount + v_CheckingAdditionalClaimFee;

        -- ------------------------------
        -- 4-4.�����ް��̍쐬
        -- ------------------------------
        INSERT
        INTO    T_ReceiptControl(   ReceiptProcessDate              -- ����������
                                ,   ReceiptDate                     -- �ڋq������
                                ,   ReceiptClass                    -- �����Ȗځi�������@�j
                                ,   ReceiptAmount                   -- ���z
                                ,   ClaimId                         -- ����ID
                                ,   OrderSeq                        -- ����SEQ
                                ,   CheckingUseAmount               -- �������|���p�z
                                ,   CheckingClaimFee                -- �������|�����萔��
                                ,   CheckingDamageInterestAmount    -- �������|�x�����Q��
                                ,   CheckingAdditionalClaimFee      -- �������|�����ǉ��萔��
                                ,   DailySummaryFlg                 -- �����X�V�׸�
                                ,   BranchBankId                    -- ��s�x�XID
                                ,   DepositDate                     -- �����\���
                                ,   ReceiptAgentId                  -- ���[��s���ID
                                ,   Receipt_Note                     -- ���l
                                ,   RegistDate                      -- �o�^����
                                ,   RegistId                        -- �o�^��
                                ,   UpdateDate                      -- �X�V����
                                ,   UpdateId                        -- �X�V��
                                ,   ValidFlg                        -- �L���׸�
                                )
                                VALUES
                                (   NOW()                           -- ����������
                                ,   pi_receipt_date                 -- �ڋq������
                                ,   pi_receipt_class                -- �����Ȗځi�������@�j
                                ,   pi_receipt_amount               -- ���z
                                ,   v_ClaimId                       -- ����ID
                                ,   pi_order_seq                    -- ����SEQ
                                ,   v_CheckingUseAmount             -- �������|���p�z
                                ,   v_CheckingClaimFee              -- �������|�����萔��
                                ,   v_CheckingDamageInterestAmount  -- �������|�x�����Q��
                                ,   v_CheckingAdditionalClaimFee    -- �������|�����ǉ��萔��
                                ,   0                               -- �����X�V�׸�
                                ,   pi_branch_bank_id               -- ��s�x�XID
                                ,   pi_deposit_date                 -- �����\���
                                ,   pi_receipt_agent_id             -- ���[��s���ID
								, 	pi_receipt_note                  -- ���l
                                ,   NOW()                           -- �o�^����
                                ,   pi_user_id                      -- �o�^��
                                ,   NOW()                           -- �X�V����
                                ,   pi_user_id                      -- �X�V��
                                ,   1                               -- �L���׸�
                                );

        -- ------------------------------
        -- 4-4'.����Seq���擾
        -- ------------------------------
        SELECT  OrderSeq
            ,   MAX(ReceiptSeq)
        INTO    v_OrderSeq
            ,   v_ReceiptSeq
        FROM    T_ReceiptControl
        WHERE   OrderSeq = pi_order_seq
        GROUP BY
                OrderSeq
        ;

        -- ------------------------------
        -- 4-5.�Œᐿ���z > �����z �̏ꍇ
        -- ------------------------------
        IF  v_MinClaimAmount > pi_receipt_amount + v_ReceiptAmount  THEN
            -- ------------------------------
            -- 4-5-1.�����ް��̍X�V
            -- ------------------------------
            UPDATE  T_ClaimControl
            SET     ClaimedBalance                  =   ClaimedBalance - v_CheckingClaimAmount                              -- �����c��
                ,   LastProcessDate                 =   DATE(NOW())                                                         -- �ŏI����������
                ,   LastReceiptSeq                  =   v_ReceiptSeq                                                        -- �ŏI����SEQ
                ,   CheckingClaimAmount             =   CheckingClaimAmount + v_CheckingClaimAmount                         -- �������|�����z���v
                ,   CheckingUseAmount               =   CheckingUseAmount + v_CheckingUseAmount                             -- �������|���p�z
                ,   CheckingClaimFee                =   CheckingClaimFee + v_CheckingClaimFee                               -- �������|�����萔��
                ,   CheckingDamageInterestAmount    =   CheckingDamageInterestAmount + v_CheckingDamageInterestAmount       -- �������|�x�����Q��
                ,   CheckingAdditionalClaimFee      =   CheckingAdditionalClaimFee + v_CheckingAdditionalClaimFee           -- �������|�����ǉ��萔��
                ,   BalanceClaimAmount              =   BalanceClaimAmount - v_CheckingClaimAmount                          -- �c�����|�c�����v
                ,   BalanceUseAmount                =   BalanceUseAmount - v_CheckingUseAmount                              -- �c�����|���p�z
                ,   BalanceClaimFee                 =   BalanceClaimFee - v_CheckingClaimFee                                -- �c�����|�����萔��
                ,   BalanceDamageInterestAmount     =   BalanceDamageInterestAmount - v_CheckingDamageInterestAmount        -- �c�����|�x�����Q��
                ,   BalanceAdditionalClaimFee       =   BalanceAdditionalClaimFee - v_CheckingAdditionalClaimFee            -- �c�����|�����ǉ��萔��
                ,   ReceiptAmountTotal              =   ReceiptAmountTotal + pi_receipt_amount                              -- �����z���v
                ,   UpdateDate                      =   NOW()
                ,   UpdateId                        =   pi_user_id
            WHERE   ClaimId =   v_ClaimId
            ;

        -- ------------------------------
        -- 4-6.�Œᐿ���z = �����z �̏ꍇ
        -- ------------------------------
        ELSEIF  v_MinClaimAmount = pi_receipt_amount + v_ReceiptAmount  THEN
            -- ------------------------------
            -- 4-6-1.�Œᐿ���z = �ŏI�����z �̏ꍇ
            -- ------------------------------
            IF  v_MinClaimAmount = v_ClaimAmount    THEN
                -- ------------------------------
                -- 4-6-1-1.�����ް��̍X�V
                -- ------------------------------
                UPDATE  T_ClaimControl
                SET     ClaimedBalance                  =   ClaimedBalance - v_CheckingClaimAmount                              -- �����c��
                    ,   LastProcessDate                 =   DATE(NOW())                                                         -- �ŏI����������
                    ,   LastReceiptSeq                  =   v_ReceiptSeq                                                        -- �ŏI����SEQ
                    ,   CheckingClaimAmount             =   CheckingClaimAmount + v_CheckingClaimAmount                         -- �������|�����z���v
                    ,   CheckingUseAmount               =   CheckingUseAmount + v_CheckingUseAmount                             -- �������|���p�z
                    ,   CheckingClaimFee                =   CheckingClaimFee + v_CheckingClaimFee                               -- �������|�����萔��
                    ,   CheckingDamageInterestAmount    =   CheckingDamageInterestAmount + v_CheckingDamageInterestAmount       -- �������|�x�����Q��
                    ,   CheckingAdditionalClaimFee      =   CheckingAdditionalClaimFee + v_CheckingAdditionalClaimFee           -- �������|�����ǉ��萔��
                    ,   BalanceClaimAmount              =   BalanceClaimAmount - v_CheckingClaimAmount                          -- �c�����|�c�����v
                    ,   BalanceUseAmount                =   BalanceUseAmount - v_CheckingUseAmount                              -- �c�����|���p�z
                    ,   BalanceClaimFee                 =   BalanceClaimFee - v_CheckingClaimFee                                -- �c�����|�����萔��
                    ,   BalanceDamageInterestAmount     =   BalanceDamageInterestAmount - v_CheckingDamageInterestAmount        -- �c�����|�x�����Q��
                    ,   BalanceAdditionalClaimFee       =   BalanceAdditionalClaimFee - v_CheckingAdditionalClaimFee            -- �c�����|�����ǉ��萔��
                    ,   ReceiptAmountTotal              =   ReceiptAmountTotal + pi_receipt_amount                              -- �����z���v
                    ,   UpdateDate                      =   NOW()
                    ,   UpdateId                        =   pi_user_id
                WHERE   ClaimId =   v_ClaimId
                ;

            -- ------------------------------
            -- 4-6-2.�ŏI�����z > �Œᐿ���z �̏ꍇ
            -- ------------------------------
            ELSEIF  v_ClaimAmount > v_MinClaimAmount    THEN
                -- ------------------------------
                -- 4-6-2-1.�G�������̎擾
                -- ------------------------------
                -- �ŏI�����z �� �Œᐿ���z �̍������Z�o�i�G�����z�ɂȂ�j
                -- 1) ���p�z�i�ŏI�����z�ƍŒᐿ���z�Ƃ̍��� �� ���p�z�̍����͑��݂��Ȃ��̂ŁA��ہj
                SET v_SundryUseAmount = 0;

                -- 2) �����萔��
                SET v_SundryClaimFee = v_ClaimFee - v_MinClaimFee;

                -- 3) �x�����Q��
                SET v_SundryDamageInterestAmount = v_DamageInterestAmount - v_MinDamageInterestAmount;

                -- 4) �����ǉ��萔��
                SET v_SundryAdditionalClaimFee = v_AdditionalClaimFee - v_MinAdditionalClaimFee;

                -- 5) ���z
                SET v_SundryAmount = v_SundryUseAmount + v_SundryClaimFee + v_SundryDamageInterestAmount + v_SundryAdditionalClaimFee;

                -- ------------------------------
                -- 4-6-2-2.�G�����ް��̍쐬
                -- ------------------------------
                INSERT
                INTO    T_SundryControl(    ProcessDate                     -- ��������
                                        ,   SundryType                      -- ��ށi�G�����^�G�����j
                                        ,   SundryAmount                    -- ���z
                                        ,   SundryClass                     -- �G�����E�G�����Ȗ�
                                        ,   OrderSeq                        -- ����SEQ
                                        ,   OrderId                         -- ����ID
                                        ,   ClaimId                         -- ����ID
                                        ,   Note                            -- ���l
                                        ,   CheckingUseAmount               -- �������|���p�z
                                        ,   CheckingClaimFee                -- �������|�����萔��
                                        ,   CheckingDamageInterestAmount    -- �������|�x�����Q��
                                        ,   CheckingAdditionalClaimFee      -- �������|�����ǉ��萔��
                                        ,   DailySummaryFlg                 -- �����X�V�׸�
                                        ,   RegistDate                      -- �o�^����
                                        ,   RegistId                        -- �o�^��
                                        ,   UpdateDate                      -- �X�V����
                                        ,   UpdateId                        -- �X�V��
                                        ,   ValidFlg                        -- �L���׸ށ@�i0�F�����@1�F�L���j
                                       )
                                       VALUES
                                       (    DATE(NOW())                     -- ��������
                                        ,   1                               -- ��ށi�G�����^�G�����j
                                        ,   v_SundryAmount                  -- ���z
                                        ,   99                              -- �G�����E�G�����Ȗ�
                                        ,   pi_order_seq                    -- ����SEQ
                                        ,   v_OrderId                       -- ����ID
                                        ,   v_ClaimId                       -- ����ID
                                        ,   NULL                            -- ���l
                                        ,   v_SundryUseAmount               -- �������|���p�z
                                        ,   v_SundryClaimFee                -- �������|�����萔��
                                        ,   v_SundryDamageInterestAmount    -- �������|�x�����Q��
                                        ,   v_SundryAdditionalClaimFee      -- �������|�����ǉ��萔��
                                        ,   0                               -- �����X�V�׸�
                                        ,   NOW()                           -- �o�^����
                                        ,   pi_user_id                      -- �o�^��
                                        ,   NOW()                           -- �X�V����
                                        ,   pi_user_id                      -- �X�V��
                                        ,   1                               -- �L���׸�
                                       );

                -- ------------------------------
                -- 4-6-2-3.�����ް��̍X�V
                -- ------------------------------
                UPDATE  T_ClaimControl
                SET     ClaimedBalance                  =   ClaimedBalance - v_CheckingClaimAmount - v_SundryAmount                                             -- �����c��
                    ,   LastProcessDate                 =   DATE(NOW())                                                                                         -- �ŏI����������
                    ,   LastReceiptSeq                  =   v_ReceiptSeq                                                                                        -- �ŏI����SEQ
                    ,   CheckingClaimAmount             =   CheckingClaimAmount + v_CheckingClaimAmount + v_SundryAmount                                        -- �������|�����z���v
                    ,   CheckingUseAmount               =   CheckingUseAmount + v_CheckingUseAmount + v_SundryUseAmount                                         -- �������|���p�z
                    ,   CheckingClaimFee                =   CheckingClaimFee + v_CheckingClaimFee + v_SundryClaimFee                                            -- �������|�����萔��
                    ,   CheckingDamageInterestAmount    =   CheckingDamageInterestAmount + v_CheckingDamageInterestAmount + v_SundryDamageInterestAmount        -- �������|�x�����Q��
                    ,   CheckingAdditionalClaimFee      =   CheckingAdditionalClaimFee + v_CheckingAdditionalClaimFee + v_SundryAdditionalClaimFee              -- �������|�����ǉ��萔��
                    ,   BalanceClaimAmount              =   BalanceClaimAmount - v_CheckingClaimAmount - v_SundryAmount                                         -- �c�����|�c�����v
                    ,   BalanceUseAmount                =   BalanceUseAmount - v_CheckingUseAmount - v_SundryUseAmount                                          -- �c�����|���p�z
                    ,   BalanceClaimFee                 =   BalanceClaimFee - v_CheckingClaimFee - v_SundryClaimFee                                             -- �c�����|�����萔��
                    ,   BalanceDamageInterestAmount     =   BalanceDamageInterestAmount - v_CheckingDamageInterestAmount - v_SundryDamageInterestAmount         -- �c�����|�x�����Q��
                    ,   BalanceAdditionalClaimFee       =   BalanceAdditionalClaimFee - v_CheckingAdditionalClaimFee - v_SundryAdditionalClaimFee               -- �c�����|�����ǉ��萔��
                    ,   ReceiptAmountTotal              =   ReceiptAmountTotal + pi_receipt_amount                                                              -- �����z���v
                    ,   SundryLossTotal                 =   SundryLossTotal + v_SundryAmount                                                                    -- �G�������v
                    ,   UpdateDate                      =   NOW()
                    ,   UpdateId                        =   pi_user_id
                WHERE   ClaimId =   v_ClaimId
                ;
            END IF;

            -- ------------------------------
            -- 4-6-3.�����ް��̍X�V
            -- ------------------------------
            UPDATE  T_Order
            SET     DataStatus  =   91      -- �ް��ð���i�۰�ށj
                ,   CloseReason =   1       -- �۰�ޗ��R�i�����ςݐ���۰�ށj
                ,   Rct_Status  =   1       -- �ڋq����ð���i�����ς݁j
                ,   UpdateDate  =   NOW()
                ,   UpdateId    =   pi_user_id
            WHERE   P_OrderSeq  =   pi_order_seq
            AND     Cnl_Status  =   0
            ;

        -- ------------------------------
        -- 4-7.�ŏI�����z > �����z > �Œᐿ���z �̏ꍇ
        -- ------------------------------
        ELSEIF  v_ClaimAmount > pi_receipt_amount + v_ReceiptAmount AND pi_receipt_amount + v_ReceiptAmount > v_MinClaimAmount  THEN
            -- ------------------------------
            -- 4-7-1.�G�������̎擾
            -- ------------------------------
            -- �ŏI�̋��z����������z�Ɠ����ς݊z�����Z���č������Z�o
            -- 1) ���p�z
            SET v_SundryUseAmount = v_UseAmountTotal - (v_CheckingUseAmount + v_ReceiptUseAmount);

            -- 2) �����萔��
            SET v_SundryClaimFee = v_ClaimFee - (v_CheckingClaimFee + v_ReceiptClaimFee);

            -- 3) �x�����Q��
            SET v_SundryDamageInterestAmount = v_DamageInterestAmount - (v_CheckingDamageInterestAmount + v_ReceiptDamageInterestAmount);

            -- 4) �����ǉ��萔��
            SET v_SundryAdditionalClaimFee = v_AdditionalClaimFee - (v_CheckingAdditionalClaimFee + v_ReceiptAdditionalClaimFee);

            -- 5) ���z
            SET v_SundryAmount = v_SundryUseAmount + v_SundryClaimFee + v_SundryDamageInterestAmount + v_SundryAdditionalClaimFee;

            -- ------------------------------
            -- 4-7-2.�G�����ް��̍쐬
            -- ------------------------------
            INSERT
            INTO    T_SundryControl(    ProcessDate                     -- ��������
                                    ,   SundryType                      -- ��ށi�G�����^�G�����j
                                    ,   SundryAmount                    -- ���z
                                    ,   SundryClass                     -- �G�����E�G�����Ȗ�
                                    ,   OrderSeq                        -- ����SEQ
                                    ,   OrderId                         -- ����ID
                                    ,   ClaimId                         -- ����ID
                                    ,   Note                            -- ���l
                                    ,   CheckingUseAmount               -- �������|���p�z
                                    ,   CheckingClaimFee                -- �������|�����萔��
                                    ,   CheckingDamageInterestAmount    -- �������|�x�����Q��
                                    ,   CheckingAdditionalClaimFee      -- �������|�����ǉ��萔��
                                    ,   DailySummaryFlg                 -- �����X�V�׸�
                                    ,   RegistDate                      -- �o�^����
                                    ,   RegistId                        -- �o�^��
                                    ,   UpdateDate                      -- �X�V����
                                    ,   UpdateId                        -- �X�V��
                                    ,   ValidFlg                        -- �L���׸ށ@�i0�F�����@1�F�L���j
                                   )
                                   VALUES
                                   (    DATE(NOW())                     -- ��������
                                    ,   1                               -- ��ށi�G�����^�G�����j
                                    ,   v_SundryAmount                  -- ���z
                                    ,   99                              -- �G�����E�G�����Ȗ�
                                    ,   pi_order_seq                    -- ����SEQ
                                    ,   v_OrderId                       -- ����ID
                                    ,   v_ClaimId                       -- ����ID
                                    ,   NULL                            -- ���l
                                    ,   v_SundryUseAmount               -- �������|���p�z
                                    ,   v_SundryClaimFee                -- �������|�����萔��
                                    ,   v_SundryDamageInterestAmount    -- �������|�x�����Q��
                                    ,   v_SundryAdditionalClaimFee      -- �������|�����ǉ��萔��
                                    ,   0                               -- �����X�V�׸�
                                    ,   NOW()                           -- �o�^����
                                    ,   pi_user_id                      -- �o�^��
                                    ,   NOW()                           -- �X�V����
                                    ,   pi_user_id                      -- �X�V��
                                    ,   1                               -- �L���׸�
                                   );

            -- ------------------------------
            -- 4-7-3.�����ް��̍X�V
            -- ------------------------------
            UPDATE  T_ClaimControl
            SET     ClaimedBalance                  =   v_ClaimedBalance - v_CheckingClaimAmount - v_SundryAmount                                           -- �����c��
                ,   LastProcessDate                 =   DATE(NOW())                                                                                         -- �ŏI����������
                ,   LastReceiptSeq                  =   v_ReceiptSeq                                                                                        -- �ŏI����SEQ
                ,   CheckingClaimAmount             =   CheckingClaimAmount + v_CheckingClaimAmount + v_SundryAmount                                        -- �������|�����z���v
                ,   CheckingUseAmount               =   CheckingUseAmount + v_CheckingUseAmount + v_SundryUseAmount                                         -- �������|���p�z
                ,   CheckingClaimFee                =   CheckingClaimFee + v_CheckingClaimFee + v_SundryClaimFee                                            -- �������|�����萔��
                ,   CheckingDamageInterestAmount    =   CheckingDamageInterestAmount + v_CheckingDamageInterestAmount + v_SundryDamageInterestAmount        -- �������|�x�����Q��
                ,   CheckingAdditionalClaimFee      =   CheckingAdditionalClaimFee + v_CheckingAdditionalClaimFee + v_SundryAdditionalClaimFee              -- �������|�����ǉ��萔��
                ,   BalanceClaimAmount              =   BalanceClaimAmount - v_CheckingClaimAmount - v_SundryAmount                                         -- �c�����|�c�����v
                ,   BalanceUseAmount                =   BalanceUseAmount - v_CheckingUseAmount - v_SundryUseAmount                                          -- �c�����|���p�z
                ,   BalanceClaimFee                 =   BalanceClaimFee - v_CheckingClaimFee - v_SundryClaimFee                                             -- �c�����|�����萔��
                ,   BalanceDamageInterestAmount     =   BalanceDamageInterestAmount - v_CheckingDamageInterestAmount - v_SundryDamageInterestAmount         -- �c�����|�x�����Q��
                ,   BalanceAdditionalClaimFee       =   BalanceAdditionalClaimFee - v_CheckingAdditionalClaimFee - v_SundryAdditionalClaimFee               -- �c�����|�����ǉ��萔��
                ,   ReceiptAmountTotal              =   ReceiptAmountTotal + pi_receipt_amount                                                              -- �����z���v
                ,   SundryLossTotal                 =   SundryLossTotal + v_SundryAmount                                                                    -- �G�������v
                ,   UpdateDate                      =   NOW()
                ,   UpdateId                        =   pi_user_id
            WHERE   ClaimId =   v_ClaimId
            ;

            -- ------------------------------
            -- 4-7-4.�����ް��̍X�V
            -- ------------------------------
            UPDATE  T_Order
            SET     DataStatus  =   91      -- �ް��ð���i�۰�ށj
                ,   CloseReason =   1       -- �۰�ޗ��R�i�����ςݐ���۰�ށj
                ,   Rct_Status  =   1       -- �ڋq����ð���i�����ς݁j
                ,   UpdateDate  =   NOW()
                ,   UpdateId    =   pi_user_id
            WHERE   P_OrderSeq  =   pi_order_seq
            AND     Cnl_Status  =   0
            ;

        -- ------------------------------
        -- 4-8.�ŏI�����z <= �����z �̏ꍇ
        -- ------------------------------
        ELSEIF  v_ClaimAmount <= pi_receipt_amount + v_ReceiptAmount    THEN
            -- ------------------------------
            -- 4-8-1.�����ް��̍X�V
            -- ------------------------------
            UPDATE  T_ClaimControl
            SET     ClaimedBalance                  =   ClaimedBalance - v_CheckingClaimAmount                              -- �����c��
                ,   LastProcessDate                 =   DATE(NOW())                                                         -- �ŏI����������
                ,   LastReceiptSeq                  =   v_ReceiptSeq                                                        -- �ŏI����SEQ
                ,   CheckingClaimAmount             =   CheckingClaimAmount + v_CheckingClaimAmount                         -- �������|�����z���v
                ,   CheckingUseAmount               =   CheckingUseAmount + v_CheckingUseAmount                             -- �������|���p�z
                ,   CheckingClaimFee                =   CheckingClaimFee + v_CheckingClaimFee                               -- �������|�����萔��
                ,   CheckingDamageInterestAmount    =   CheckingDamageInterestAmount + v_CheckingDamageInterestAmount       -- �������|�x�����Q��
                ,   CheckingAdditionalClaimFee      =   CheckingAdditionalClaimFee + v_CheckingAdditionalClaimFee           -- �������|�����ǉ��萔��
                ,   BalanceClaimAmount              =   BalanceClaimAmount - v_CheckingClaimAmount                          -- �c�����|�c�����v
                ,   BalanceUseAmount                =   BalanceUseAmount - v_CheckingUseAmount                              -- �c�����|���p�z
                ,   BalanceClaimFee                 =   BalanceClaimFee - v_CheckingClaimFee                                -- �c�����|�����萔��
                ,   BalanceDamageInterestAmount     =   BalanceDamageInterestAmount - v_CheckingDamageInterestAmount        -- �c�����|�x�����Q��
                ,   BalanceAdditionalClaimFee       =   BalanceAdditionalClaimFee - v_CheckingAdditionalClaimFee            -- �c�����|�����ǉ��萔��
                ,   ReceiptAmountTotal              =   ReceiptAmountTotal + pi_receipt_amount                              -- �����z���v
                ,   UpdateDate                      =   NOW()
                ,   UpdateId                        =   pi_user_id
            WHERE   ClaimId =   v_ClaimId
            ;

            -- ------------------------------
            -- 4-8-2.�����ް��̍X�V
            -- ------------------------------
            UPDATE  T_Order
            SET     DataStatus  =   91      -- �ް��ð���i�۰�ށj
                ,   CloseReason =   1       -- �۰�ޗ��R�i�����ςݐ���۰�ށj
                ,   Rct_Status  =   1       -- �ڋq����ð���i�����ς݁j
                ,   UpdateDate  =   NOW()
                ,   UpdateId    =   pi_user_id
            WHERE   P_OrderSeq  =   pi_order_seq
            AND     Cnl_Status  =   0
            ;
        END IF;

    -- ------------------------------
    -- 5.����۰�ތ�̓����̏ꍇ
    -- ------------------------------
    ELSE
        -- ------------------------------
        -- 5-1.�����ς݂��ް����擾
        -- ------------------------------
        SELECT  SUM(ReceiptAmount)                   -- ���z
            ,   SUM(CheckingUseAmount)               -- �������|���p�z
            ,   SUM(CheckingClaimFee)                -- �������|�����萔��
            ,   SUM(CheckingDamageInterestAmount)    -- �������|�x�����Q��
            ,   SUM(CheckingAdditionalClaimFee)      -- �������|�����ǉ��萔��
            ,   COUNT(*)
        INTO    v_ReceiptAmount
            ,   v_ReceiptUseAmount
            ,   v_ReceiptClaimFee
            ,   v_ReceiptDamageInterestAmount
            ,   v_ReceiptAdditionalClaimFee
            ,   v_Cnt
        FROM    T_ReceiptControl
        WHERE   OrderSeq    =   pi_order_seq
        ;

        IF  v_Cnt = 0   THEN
            SET po_ret_sts  =   -1;
            SET po_ret_msg  =   '�����ς݂̃f�[�^�����݂��܂���B';
            LEAVE proc;
        END IF;

        -- ------------------------------
        -- 5-2.�����ް��擾
        -- ------------------------------
        SELECT  ClaimId                         -- ����ID
            ,   ClaimAmount                     -- �����z
            ,   UseAmountTotal                  -- ���p�z���v
            ,   ClaimFee                        -- �����萔��
            ,   DamageInterestAmount            -- �x�����Q��
            ,   AdditionalClaimFee              -- �����ǉ��萔��
            ,   ClaimedBalance                  -- �����c��
            ,   MinClaimAmount                  -- �Œᐿ�����|�������z
            ,   MinUseAmount                    -- �Œᐿ�����|���p�z
            ,   MinClaimFee                     -- �Œᐿ�����|�����萔��
            ,   MinDamageInterestAmount         -- �Œᐿ�����|�x�����Q��
            ,   MinAdditionalClaimFee           -- �Œᐿ�����|�����ǉ��萔��
        INTO    v_ClaimId
            ,   v_ClaimAmount
            ,   v_UseAmountTotal
            ,   v_ClaimFee
            ,   v_DamageInterestAmount
            ,   v_AdditionalClaimFee
            ,   v_ClaimedBalance
            ,   v_MinClaimAmount
            ,   v_MinUseAmount
            ,   v_MinClaimFee
            ,   v_MinDamageInterestAmount
            ,   v_MinAdditionalClaimFee
        FROM    T_ClaimControl
        WHERE   OrderSeq    =   pi_order_seq
        ;

        IF  no_data_found = 0   THEN
            SET po_ret_sts  =   -1;
            SET po_ret_msg  =   '�����Ώۂ̃f�[�^�����݂��܂���B';
            LEAVE proc;
        END IF;

        -- ------------------------------
        -- 5-3.�����p�������̌v�Z
        -- ------------------------------
        -- ++++++++++++++++++++++++++++++++++++++++
        -- 1. �������|�����ǉ��萔�� �����߂�
        -- ++++++++++++++++++++++++++++++++++++++++
        -- �����ς݂̐����ǉ��萔�� �� �����ǉ��萔�� �ȏ�̏ꍇ
        IF  v_ReceiptAdditionalClaimFee >= v_AdditionalClaimFee THEN
            -- �������|�����ǉ��萔�� �ɑ΂���������݂͂Ȃ�
            SET v_InsReceiptAdditionalClaimFee = 0;
            -- �c���͓����z
            SET v_CalculationAmount = pi_receipt_amount;
        -- ����ȊO�̏ꍇ
        ELSE
            -- �������|�����ǉ��萔�� �� �����ǉ��萔�� ���� �����ς݂̐����ǉ��萔�� �����Z���Ď擾
            SET v_InsReceiptAdditionalClaimFee = v_AdditionalClaimFee - v_ReceiptAdditionalClaimFee;
            -- �����z ���� �������|�����ǉ��萔�� �����Z���Ďc�����Z�o
            SET v_CalculationAmount = pi_receipt_amount - v_InsReceiptAdditionalClaimFee;
        END IF;

        -- 1.�̎c�������݂���ꍇ�ȉ��������s��
        IF  v_CalculationAmount > 0 THEN
            -- ++++++++++++++++++++++++++++++++++++++++
            -- 2. �������|�x�����Q�� �����߂�
            -- ++++++++++++++++++++++++++++++++++++++++
            -- �����ς݂̒x�����Q�� �� �x�����Q�� �ȏ�̏ꍇ
            IF  v_ReceiptDamageInterestAmount >= v_DamageInterestAmount THEN
                -- �������|�x�����Q�� �ɑ΂���������݂͂Ȃ�
                SET v_InsReceiptDamageInterestAmount = 0;
                -- �c����1.�̎c��
                SET v_CalculationAmount = v_CalculationAmount;
            -- ����ȊO�̏ꍇ
            ELSE
                -- �������|�x�����Q�� �� �x�����Q�� ���� �����ς݂̒x�����Q�� �����Z���Ď擾
                SET v_InsReceiptDamageInterestAmount = v_DamageInterestAmount - v_ReceiptDamageInterestAmount;
                -- 1.�̎c�� ���� �������|�x�����Q�� �����Z���ē����z�̎c�����Z�o
                SET v_CalculationAmount = v_CalculationAmount - v_InsReceiptDamageInterestAmount;
            END IF;
        END IF;

        -- 2.�̎c�������݂���ꍇ�ȉ��������s��
        IF  v_CalculationAmount > 0 THEN
            -- ++++++++++++++++++++++++++++++++++++++++
            -- 3. �������|�����萔�� �����߂�
            -- ++++++++++++++++++++++++++++++++++++++++
            -- �����ς݂̐����萔�� �� �����萔�� �ȏ�̏ꍇ
            IF  v_ReceiptClaimFee >= v_ClaimFee THEN
                -- �������|�����萔�� �ɑ΂���������݂͂Ȃ�
                SET v_InsReceiptClaimFee = 0;
                -- �c����2.�̎c��
                SET v_CalculationAmount = v_CalculationAmount;
            -- ����ȊO�̏ꍇ
            ELSE
                -- �������|�����萔�� �� �����萔�� ���� �����ς݂̐����萔�� �����Z���Ď擾
                SET v_InsReceiptClaimFee = v_ClaimFee - v_ReceiptClaimFee;
                -- 2.�̎c�� ���� �������|�����萔�� �����Z���ĎZ�o
                SET v_CalculationAmount = v_CalculationAmount - v_InsReceiptClaimFee;
            END IF;
        END IF;

        -- 3.�̎c�������݂���ꍇ�ȉ��������s��
        IF  v_CalculationAmount > 0 THEN
            -- ++++++++++++++++++++++++++++++++++++++++
            -- 4. �������|���p�z �����߂�
            -- ++++++++++++++++++++++++++++++++++++++++
            -- �ߏ�����ɓ�����̂ŁA�c���͑S�z �������|���p�z�ɂȂ�
            SET v_InsReceiptUseAmount = v_CalculationAmount;
        END IF;

        -- ++++++++++++++++++++++++++++++++++++++++
        -- 5. �������|�������z���v �����߂�
        -- ++++++++++++++++++++++++++++++++++++++++
        SET v_InsReceiptAmount = v_InsReceiptUseAmount + v_InsReceiptClaimFee + v_InsReceiptDamageInterestAmount + v_InsReceiptAdditionalClaimFee;

        -- ------------------------------
        -- 5-4.�����ް��̍쐬
        -- ------------------------------
        INSERT
        INTO    T_ReceiptControl(   ReceiptProcessDate                  -- ����������
                                ,   ReceiptDate                         -- �ڋq������
                                ,   ReceiptClass                        -- �����Ȗځi�������@�j
                                ,   ReceiptAmount                       -- ���z
                                ,   ClaimId                             -- ����ID
                                ,   OrderSeq                            -- ����SEQ
                                ,   CheckingUseAmount                   -- �������|���p�z
                                ,   CheckingClaimFee                    -- �������|�����萔��
                                ,   CheckingDamageInterestAmount        -- �������|�x�����Q��
                                ,   CheckingAdditionalClaimFee          -- �������|�����ǉ��萔��
                                ,   DailySummaryFlg                     -- �����X�V�׸�
                                ,   BranchBankId                        -- ��s�x�XID
                                ,   DepositDate                         -- �����\���
                                ,   ReceiptAgentId                      -- ���[��s���ID
                                ,   Receipt_Note                         -- ���l
                                ,   RegistDate                          -- �o�^����
                                ,   RegistId                            -- �o�^��
                                ,   UpdateDate                          -- �X�V����
                                ,   UpdateId                            -- �X�V��
                                ,   ValidFlg                            -- �L���׸�
                                )
                                VALUES
                                (   NOW()                               -- ����������
                                ,   pi_receipt_date                     -- �ڋq������
                                ,   pi_receipt_class                    -- �����Ȗځi�������@�j
                                ,   pi_receipt_amount                   -- ���z
                                ,   v_ClaimId                           -- ����ID
                                ,   pi_order_seq                        -- ����SEQ
                                ,   v_InsReceiptUseAmount               -- �������|���p�z
                                ,   v_InsReceiptClaimFee                -- �������|�����萔��
                                ,   v_InsReceiptDamageInterestAmount    -- �������|�x�����Q��
                                ,   v_InsReceiptAdditionalClaimFee      -- �������|�����ǉ��萔��
                                ,   0                                   -- �����X�V�׸�
                                ,   pi_branch_bank_id                   -- ��s�x�XID
                                ,   pi_deposit_date                     -- �����\���
                                ,   pi_receipt_agent_id                 -- ���[��s���ID
                                ,   pi_receipt_note                     -- ���l
                                ,   NOW()                               -- �o�^����
                                ,   pi_user_id                          -- �o�^��
                                ,   NOW()                               -- �X�V����
                                ,   pi_user_id                          -- �X�V��
                                ,   1                                   -- �L���׸�
                                );

        -- ------------------------------
        -- 5-4'.����Seq���擾
        -- ------------------------------
        SELECT  OrderSeq
            ,   MAX(ReceiptSeq)
        INTO    v_OrderSeq
            ,   v_ReceiptSeq
        FROM    T_ReceiptControl
        WHERE   OrderSeq = pi_order_seq
        GROUP BY
                OrderSeq
        ;

        -- ------------------------------
        -- 5-5.�����ϊz >= �ŏI�����z �̏ꍇ
        -- ------------------------------
        IF  v_ReceiptAmount >= v_ClaimAmount    THEN
            -- ------------------------------
            -- 5-5-1.�����ް��̍X�V
            -- ------------------------------
            UPDATE  T_ClaimControl
            SET     ClaimedBalance      =   ClaimedBalance - pi_receipt_amount          -- �����c��
                ,   LastProcessDate     =   DATE(NOW())                                 -- �ŏI����������
                ,   LastReceiptSeq      =   v_ReceiptSeq                                -- �ŏI����SEQ
                ,   CheckingClaimAmount =   CheckingClaimAmount + pi_receipt_amount     -- �������|�������z���v
                ,   CheckingUseAmount   =   CheckingUseAmount + pi_receipt_amount       -- �������|���p�z
                ,   BalanceClaimAmount  =   BalanceClaimAmount - pi_receipt_amount      -- �c�����|�c�����v
                ,   BalanceUseAmount    =   BalanceUseAmount - pi_receipt_amount        -- �c�����|���p�z
                ,   ReceiptAmountTotal  =   ReceiptAmountTotal + pi_receipt_amount      -- �����z���v
                ,   UpdateDate          =   NOW()
                ,   UpdateId            =   pi_user_id
            WHERE   ClaimId =   v_ClaimId
            ;

        -- ------------------------------
        -- 5-6.�����ϊz = �Œᐿ���z �̏ꍇ
        -- ------------------------------
        ELSEIF  v_ReceiptAmount = v_MinClaimAmount  THEN
            -- ------------------------------
            -- 5-6-1.�G�������ް����擾
            -- ------------------------------
            -- �擾������1���Ƃ͌���Ȃ����߁AOrderSeq �ɕR�Â��G�����ް��̻�؂��擾����i���ͻ�؂��Ȃ��Ă������Ƃ͎v�����ǥ���O�̂��ߥ���j
            SELECT  SUM(SundryAmount)                   -- ���z
                ,   SUM(CheckingUseAmount)              -- �������|���p�z
                ,   SUM(CheckingClaimFee)               -- �������|�����萔��
                ,   SUM(CheckingDamageInterestAmount)   -- �������|�x�����Q��
                ,   SUM(CheckingAdditionalClaimFee)     -- �������|�����ǉ��萔��
                ,   COUNT(*)
            INTO    v_SundryAmount
                ,   v_SundryUseAmount
                ,   v_SundryClaimFee
                ,   v_SundryDamageInterestAmount
                ,   v_SundryAdditionalClaimFee
                ,   v_Cnt
            FROM    T_SundryControl
            WHERE   OrderSeq    =   pi_order_seq
            ;

            IF  v_Cnt = 0   THEN
                SET po_ret_sts  =   -1;
                SET po_ret_msg  =   '�G�����f�[�^�����݂��܂���B';
                LEAVE proc;
            END IF;

            -- ------------------------------
            -- 5-6-2.�ԓ`�p���������v�Z
            -- ------------------------------
            -- ++++++++++++++++++++++++++++++++++++++++
            -- 1. �������|�����ǉ��萔�� �����߂�
            -- ++++++++++++++++++++++++++++++++++++++++
            -- 1) �������|�����ǉ��萔�� �� �����z �ȏ�̏ꍇ
            IF  v_SundryAdditionalClaimFee >= pi_receipt_amount THEN
                -- �������|�����ǉ��萔�� �� �����z
                SET v_CheckingAdditionalClaimFee = pi_receipt_amount;
                -- �c���� 0�i��ہj
                SET v_CalculationAmount = 0;
            -- ����ȊO
            ELSE
                SET v_CheckingAdditionalClaimFee = v_SundryAdditionalClaimFee;
                SET v_CalculationAmount = pi_receipt_amount - v_CheckingAdditionalClaimFee;
            END IF;

            -- 1.�Ŏc�������݂���ꍇ
            IF  v_CalculationAmount > 0 THEN
                -- ++++++++++++++++++++++++++++++++++++++++
                -- 2. �������|�x�����Q�� �����߂�
                -- ++++++++++++++++++++++++++++++++++++++++
                -- 1) �������|�x�����Q�� �� �c�� �ȏ�̏ꍇ
                IF  v_SundryDamageInterestAmount >= v_CalculationAmount THEN
                    -- �������|�x�����Q�� �� �c��
                    SET v_CheckingDamageInterestAmount = v_CalculationAmount;
                    -- �c���� 0�i��ہj
                    SET v_CalculationAmount = 0;
                -- ����ȊO
                ELSE
                    SET v_CheckingDamageInterestAmount = v_SundryDamageInterestAmount;
                    SET v_CalculationAmount = v_CalculationAmount - v_CheckingDamageInterestAmount;
                END IF;
            END IF;

            -- 2.�Ŏc�������݂���ꍇ
            IF  v_CalculationAmount > 0 THEN
                -- ++++++++++++++++++++++++++++++++++++++++
                -- 3. �������|�����萔�� �����߂�
                -- ++++++++++++++++++++++++++++++++++++++++
                -- 1) �������|�����萔�� �� �c�� �ȏ�̏ꍇ
                IF  v_SundryClaimFee >= v_CalculationAmount THEN
                    -- �������|�����萔�� �� �c��
                    SET v_CheckingClaimFee = v_CalculationAmount;
                    -- �c���� 0�i��ہj
                    SET v_CalculationAmount = 0;
                -- ����ȊO
                ELSE
                    SET v_CheckingClaimFee = v_SundryClaimFee;
                    SET v_CalculationAmount = v_CalculationAmount - v_CheckingClaimFee;
                END IF;
            END IF;

            -- 3.�Ŏc�������݂���ꍇ
            IF  v_CalculationAmount > 0 THEN
                -- ++++++++++++++++++++++++++++++++++++++++
                -- 4. �������|���p�z �����߂�
                -- ++++++++++++++++++++++++++++++++++++++++
                -- 1) �������|���p�z �� �c�� �ȏ�̏ꍇ
                IF  v_SundryUseAmount >= v_CalculationAmount    THEN
                    -- �������|���p�z �� �c��
                    SET v_CheckingUseAmount = v_CalculationAmount;
                    -- �c���� 0�i��ہj
                    SET v_CalculationAmount = 0;
                -- ����ȊO
                ELSE
                    SET v_CheckingUseAmount = v_SundryUseAmount;
                    SET v_CalculationAmount = v_CalculationAmount - v_CheckingUseAmount;
                END IF;
            END IF;

            -- ++++++++++++++++++++++++++++++++++++++++
            -- 5. �������|�������z���v �����߂�
            -- ++++++++++++++++++++++++++++++++++++++++
            SET v_CheckingClaimAmount = v_CheckingUseAmount + v_CheckingClaimFee + v_CheckingDamageInterestAmount + v_CheckingAdditionalClaimFee;

            -- ------------------------------
            -- 5-6-3.�G�����ԓ`�ް��̍쐬
            -- ------------------------------
            -- �擾������������-1���|����
            INSERT
            INTO    T_SundryControl(    ProcessDate                             -- ��������
                                    ,   SundryType                              -- ��ށi�G�����^�G�����j
                                    ,   SundryAmount                            -- ���z
                                    ,   SundryClass                             -- �G�����E�G�����Ȗ�
                                    ,   OrderSeq                                -- ����SEQ
                                    ,   OrderId                                 -- ����ID
                                    ,   ClaimId                                 -- ����ID
                                    ,   Note                                    -- ���l
                                    ,   CheckingUseAmount                       -- �������|���p�z
                                    ,   CheckingClaimFee                        -- �������|�����萔��
                                    ,   CheckingDamageInterestAmount            -- �������|�x�����Q��
                                    ,   CheckingAdditionalClaimFee              -- �������|�����ǉ��萔��
                                    ,   DailySummaryFlg                         -- �����X�V�׸�
                                    ,   RegistDate                              -- �o�^����
                                    ,   RegistId                                -- �o�^��
                                    ,   UpdateDate                              -- �X�V����
                                    ,   UpdateId                                -- �X�V��
                                    ,   ValidFlg                                -- �L���׸�
                                   )
                                   VALUES
                                   (    DATE(NOW())                             -- ��������
                                    ,   1                                       -- ��ށi�G�����^�G�����j
                                    ,   v_CheckingClaimAmount * -1              -- ���z
                                    ,   99                                      -- �G�����E�G�����Ȗ�
                                    ,   pi_order_seq                            -- ����SEQ
                                    ,   v_OrderId                               -- ����ID
                                    ,   v_ClaimId                               -- ����ID
                                    ,   NULL                                    -- ���l
                                    ,   v_CheckingUseAmount * -1                -- �������|���p�z
                                    ,   v_CheckingClaimFee * -1                 -- �������|�����萔��
                                    ,   v_CheckingDamageInterestAmount * -1     -- �������|�x�����Q��
                                    ,   v_CheckingAdditionalClaimFee * -1       -- �������|�����ǉ��萔��
                                    ,   0                                       -- �����X�V�׸�
                                    ,   NOW()                                   -- �o�^����
                                    ,   pi_user_id                              -- �o�^��
                                    ,   NOW()                                   -- �X�V����
                                    ,   pi_user_id                              -- �X�V��
                                    ,   1                                       -- �L���׸�
                                   );

            -- ------------------------------
            -- 5-6-4.�ŏI�����z >= �����z �̏ꍇ
            -- ------------------------------
            IF  v_ClaimAmount >= pi_receipt_amount + v_ReceiptAmount THEN
                -- ------------------------------
                -- 5-6-4-1.�����ް��̍X�V
                -- ------------------------------
                UPDATE  T_ClaimControl
                SET     LastProcessDate     =   DATE(NOW())                                     -- �ŏI����������
                    ,   LastReceiptSeq      =   v_ReceiptSeq                                    -- �ŏI����SEQ
                    ,   ReceiptAmountTotal  =   ReceiptAmountTotal + pi_receipt_amount          -- �����z���v
                    ,   SundryLossTotal     =   SundryLossTotal - v_CheckingClaimAmount         -- �G�������v
                    ,   UpdateDate          =   NOW()
                    ,   UpdateId            =   pi_user_id
                WHERE   ClaimId =   v_ClaimId
                ;

            -- ------------------------------
            -- 5-6-5.�����z > �ŏI�����z �̏ꍇ
            -- ------------------------------
            ELSEIF  pi_receipt_amount + v_ReceiptAmount > v_ClaimAmount THEN
                -- ------------------------------
                -- 5-6-5-1.�����ް��̍X�V
                -- ------------------------------
                -- �ߏ�������𑫂�����
                UPDATE  T_ClaimControl
                SET     ClaimedBalance      =   ClaimedBalance - v_InsReceiptUseAmount          -- �����c��
                    ,   LastProcessDate     =   DATE(NOW())                                     -- �ŏI����������
                    ,   LastReceiptSeq      =   v_ReceiptSeq                                    -- �ŏI����SEQ
                    ,   CheckingClaimAmount =   CheckingClaimAmount + v_InsReceiptUseAmount     -- �������|�������z���v
                    ,   CheckingUseAmount   =   CheckingUseAmount + v_InsReceiptUseAmount       -- �������|���p�z
                    ,   BalanceClaimAmount  =   BalanceClaimAmount - v_InsReceiptUseAmount      -- �c�����|�c�����v
                    ,   BalanceUseAmount    =   BalanceUseAmount - v_InsReceiptUseAmount        -- �c�����|���p�z
                    ,   ReceiptAmountTotal  =   ReceiptAmountTotal + pi_receipt_amount          -- �����z���v
                    ,   SundryLossTotal     =   SundryLossTotal - v_SundryAmount                -- �G�������v
                    ,   UpdateDate          =   NOW()
                    ,   UpdateId            =   pi_user_id
                WHERE   ClaimId =   v_ClaimId
                ;
            END IF;
        -- ------------------------------
        -- 5-7.�ŏI�����z > �����ϊz > �Œᐿ���z �̏ꍇ
        -- ------------------------------
        ELSEIF  v_ClaimAmount > v_ReceiptAmount AND v_ReceiptAmount > v_MinClaimAmount  THEN
            -- ------------------------------
            -- 5-7-1.�G�������ް����擾
            -- ------------------------------
            -- �擾������1���Ƃ͌���Ȃ����߁AOrderSeq �ɕR�Â��G�����ް��̻�؂��擾����
            SELECT  SUM(SundryAmount)                   -- ���z
                ,   SUM(CheckingUseAmount)              -- �������|���p�z
                ,   SUM(CheckingClaimFee)               -- �������|�����萔��
                ,   SUM(CheckingDamageInterestAmount)   -- �������|�x�����Q��
                ,   SUM(CheckingAdditionalClaimFee)     -- �������|�����ǉ��萔��
                ,   COUNT(*)
            INTO    v_SundryAmount
                ,   v_SundryUseAmount
                ,   v_SundryClaimFee
                ,   v_SundryDamageInterestAmount
                ,   v_SundryAdditionalClaimFee
                ,   v_Cnt
            FROM    T_SundryControl
            WHERE   OrderSeq    =   pi_order_seq
            ;

            IF  v_Cnt = 0   THEN
                SET po_ret_sts  =   -1;
                SET po_ret_msg  =   '�G�����f�[�^�����݂��܂���B';
                LEAVE proc;
            END IF;

            -- ------------------------------
            -- 5-7-2.�G�������z > �����z �̏ꍇ
            -- ------------------------------
            IF  v_SundryAmount > pi_receipt_amount  THEN
                -- ------------------------------
                -- 5-7-2-1.�ԓ`�p���������v�Z
                -- ------------------------------
                -- ++++++++++++++++++++++++++++++++++++++++
                -- 1. �������|�����ǉ��萔�� �����߂�
                -- ++++++++++++++++++++++++++++++++++++++++
                -- 1) �������|�����ǉ��萔�� �� �����z �ȏ�̏ꍇ
                IF  v_SundryAdditionalClaimFee >= pi_receipt_amount THEN
                    -- �������|�����ǉ��萔�� �� �����z
                    SET v_CheckingAdditionalClaimFee = pi_receipt_amount;
                    -- �c���� 0�i��ہj
                    SET v_CalculationAmount = 0;
                -- ����ȊO
                ELSE
                    SET v_CheckingAdditionalClaimFee = v_SundryAdditionalClaimFee;
                    SET v_CalculationAmount = pi_receipt_amount - v_CheckingAdditionalClaimFee;
                END IF;

                -- 1.�Ŏc�������݂���ꍇ
                IF  v_CalculationAmount > 0 THEN
                    -- ++++++++++++++++++++++++++++++++++++++++
                    -- 2. �������|�x�����Q�� �����߂�
                    -- ++++++++++++++++++++++++++++++++++++++++
                    -- 1) �������|�x�����Q�� �� �c�� �ȏ�̏ꍇ
                    IF  v_SundryDamageInterestAmount >= v_CalculationAmount THEN
                        -- �������|�x�����Q�� �� �c��
                        SET v_CheckingDamageInterestAmount = v_CalculationAmount;
                        -- �c���� 0�i��ہj
                        SET v_CalculationAmount = 0;
                    -- ����ȊO
                    ELSE
                        SET v_CheckingDamageInterestAmount = v_SundryDamageInterestAmount;
                        SET v_CalculationAmount = v_CalculationAmount - v_CheckingDamageInterestAmount;
                    END IF;
                END IF;

                -- 2.�Ŏc�������݂���ꍇ
                IF  v_CalculationAmount > 0 THEN
                    -- ++++++++++++++++++++++++++++++++++++++++
                    -- 3. �������|�����萔�� �����߂�
                    -- ++++++++++++++++++++++++++++++++++++++++
                    -- 1) �������|�����萔�� �� �c�� �ȏ�̏ꍇ
                    IF  v_SundryClaimFee >= v_CalculationAmount THEN
                        -- �������|�����萔�� �� �c��
                        SET v_CheckingClaimFee = v_CalculationAmount;
                        -- �c���� 0�i��ہj
                        SET v_CalculationAmount = 0;
                    -- ����ȊO
                    ELSE
                        SET v_CheckingClaimFee = v_SundryClaimFee;
                        SET v_CalculationAmount = v_CalculationAmount - v_CheckingClaimFee;
                    END IF;
                END IF;

                -- 3.�Ŏc�������݂���ꍇ
                IF  v_CalculationAmount > 0 THEN
                    -- ++++++++++++++++++++++++++++++++++++++++
                    -- 4. �������|���p�z �����߂�
                    -- ++++++++++++++++++++++++++++++++++++++++
                    -- 1) �������|���p�z �� �c�� �ȏ�̏ꍇ
                    IF  v_SundryUseAmount >= v_CalculationAmount    THEN
                        -- �������|���p�z �� �c��
                        SET v_CheckingUseAmount = v_CalculationAmount;
                        -- �c���� 0�i��ہj
                        SET v_CalculationAmount = 0;
                    -- ����ȊO
                    ELSE
                        SET v_CheckingUseAmount = v_SundryUseAmount;
                        SET v_CalculationAmount = v_CalculationAmount - v_CheckingUseAmount;
                    END IF;
                END IF;

                -- ++++++++++++++++++++++++++++++++++++++++
                -- 5. �������|�������z���v �����߂�
                -- ++++++++++++++++++++++++++++++++++++++++
                SET v_CheckingClaimAmount = v_CheckingUseAmount + v_CheckingClaimFee + v_CheckingDamageInterestAmount + v_CheckingAdditionalClaimFee;

                -- ------------------------------
                -- 5-7-2-2.�G�����ԓ`�ް��̍쐬
                -- ------------------------------
                -- �擾������������-1���|����
                INSERT
                INTO    T_SundryControl(    ProcessDate                             -- ��������
                                        ,   SundryType                              -- ��ށi�G�����^�G�����j
                                        ,   SundryAmount                            -- ���z
                                        ,   SundryClass                             -- �G�����E�G�����Ȗ�
                                        ,   OrderSeq                                -- ����SEQ
                                        ,   OrderId                                 -- ����ID
                                        ,   ClaimId                                 -- ����ID
                                        ,   Note                                    -- ���l
                                        ,   CheckingUseAmount                       -- �������|���p�z
                                        ,   CheckingClaimFee                        -- �������|�����萔��
                                        ,   CheckingDamageInterestAmount            -- �������|�x�����Q��
                                        ,   CheckingAdditionalClaimFee              -- �������|�����ǉ��萔��
                                        ,   DailySummaryFlg                         -- �����X�V�׸�
                                        ,   RegistDate                              -- �o�^����
                                        ,   RegistId                                -- �o�^��
                                        ,   UpdateDate                              -- �X�V����
                                        ,   UpdateId                                -- �X�V��
                                        ,   ValidFlg                                -- �L���׸�
                                       )
                                       VALUES
                                       (    DATE(NOW())                             -- ��������
                                        ,   1                                       -- ��ށi�G�����^�G�����j
                                        ,   v_CheckingClaimAmount * -1              -- ���z
                                        ,   99                                      -- �G�����E�G�����Ȗ�
                                        ,   pi_order_seq                            -- ����SEQ
                                        ,   v_OrderId                               -- ����ID
                                        ,   v_ClaimId                               -- ����ID
                                        ,   NULL                                    -- ���l
                                        ,   v_CheckingUseAmount * -1                -- �������|���p�z
                                        ,   v_CheckingClaimFee * -1                 -- �������|�����萔��
                                        ,   v_CheckingDamageInterestAmount * -1     -- �������|�x�����Q��
                                        ,   v_CheckingAdditionalClaimFee * -1       -- �������|�����ǉ��萔��
                                        ,   0                                       -- �����X�V�׸�
                                        ,   NOW()                                   -- �o�^����
                                        ,   pi_user_id                              -- �o�^��
                                        ,   NOW()                                   -- �X�V����
                                        ,   pi_user_id                              -- �X�V��
                                        ,   1                                       -- �L���׸�
                                       );

                -- ------------------------------
                -- 5-7-2-3.�����ް��̍X�V
                -- ------------------------------
                UPDATE  T_ClaimControl
                SET     LastProcessDate     =   DATE(NOW())                                 -- �ŏI����������
                    ,   LastReceiptSeq      =   v_ReceiptSeq                                -- �ŏI����SEQ
                    ,   ReceiptAmountTotal  =   ReceiptAmountTotal + pi_receipt_amount      -- �����z���v
                    ,   SundryLossTotal     =   SundryLossTotal - v_CheckingClaimAmount     -- �G�������v
                    ,   UpdateDate          =   NOW()
                    ,   UpdateId            =   pi_user_id
                WHERE   ClaimId =   v_ClaimId
                ;

            -- ------------------------------
            -- 5-7-3.�����z >= �G�������z �̏ꍇ
            -- ------------------------------
            ELSEIF  pi_receipt_amount >= v_SundryAmount THEN
                -- ------------------------------
                -- 5-7-3-1.�G�����ԓ`�ް��̍쐬
                -- ------------------------------
                -- �擾�����G�����z��-1���|����
                INSERT
                INTO    T_SundryControl(    ProcessDate                         -- ��������
                                        ,   SundryType                          -- ��ށi�G�����^�G�����j
                                        ,   SundryAmount                        -- ���z
                                        ,   SundryClass                         -- �G�����E�G�����Ȗ�
                                        ,   OrderSeq                            -- ����SEQ
                                        ,   OrderId                             -- ����ID
                                        ,   ClaimId                             -- ����ID
                                        ,   Note                                -- ���l
                                        ,   CheckingUseAmount                   -- �������|���p�z
                                        ,   CheckingClaimFee                    -- �������|�����萔��
                                        ,   CheckingDamageInterestAmount        -- �������|�x�����Q��
                                        ,   CheckingAdditionalClaimFee          -- �������|�����ǉ��萔��
                                        ,   DailySummaryFlg                     -- �����X�V�׸�
                                        ,   RegistDate                          -- �o�^����
                                        ,   RegistId                            -- �o�^��
                                        ,   UpdateDate                          -- �X�V����
                                        ,   UpdateId                            -- �X�V��
                                        ,   ValidFlg                            -- �L���׸�
                                       )
                                       VALUES
                                       (    DATE(NOW())                         -- ��������
                                        ,   1                                   -- ��ށi�G�����^�G�����j
                                        ,   v_SundryAmount * -1                 -- ���z
                                        ,   99                                  -- �G�����E�G�����Ȗ�
                                        ,   pi_order_seq                        -- ����SEQ
                                        ,   v_OrderId                           -- ����ID
                                        ,   v_ClaimId                           -- ����ID
                                        ,   NULL                                -- ���l
                                        ,   v_SundryUseAmount * -1              -- �������|���p�z
                                        ,   v_SundryClaimFee * -1               -- �������|�����萔��
                                        ,   v_SundryDamageInterestAmount * -1   -- �������|�x�����Q��
                                        ,   v_SundryAdditionalClaimFee * -1     -- �������|�����ǉ��萔��
                                        ,   0                                   -- �����X�V�׸�
                                        ,   NOW()                               -- �o�^����
                                        ,   pi_user_id                          -- �o�^��
                                        ,   NOW()                               -- �X�V����
                                        ,   pi_user_id                          -- �X�V��
                                        ,   1                                   -- �L���׸�
                                       );

                -- ------------------------------
                -- 5-7-3-2.�����z = �G�������z �̏ꍇ
                -- ------------------------------
                IF  pi_receipt_amount = v_SundryAmount  THEN
                    -- ------------------------------
                    -- 5-7-3-2-1.�����ް��̍X�V
                    -- ------------------------------
                    UPDATE  T_ClaimControl
                    SET     LastProcessDate     =   DATE(NOW())                                 -- �ŏI����������
                        ,   LastReceiptSeq      =   v_ReceiptSeq                                -- �ŏI����SEQ
                        ,   ReceiptAmountTotal  =   ReceiptAmountTotal + pi_receipt_amount      -- �����z���v
                        ,   SundryLossTotal     =   SundryLossTotal - v_SundryAmount            -- �G�������v
                        ,   UpdateDate          =   NOW()
                        ,   UpdateId            =   pi_user_id
                    WHERE   ClaimId =   v_ClaimId
                    ;
                END IF;

                -- ------------------------------
                -- 5-7-3-3.�����z > �G�������z �̏ꍇ
                -- ------------------------------
                IF  pi_receipt_amount > v_SundryAmount  THEN
                    -- ------------------------------
                    -- 5-7-2-3.�����ް��̍X�V
                    -- ------------------------------
                    -- �ߏ蕪�𑫂�����
                    UPDATE  T_ClaimControl
                    SET     ClaimedBalance      =   ClaimedBalance - v_InsReceiptUseAmount          -- �����c��
                        ,   LastProcessDate     =   DATE(NOW())                                     -- �ŏI����������
                        ,   LastReceiptSeq      =   v_ReceiptSeq                                    -- �ŏI����SEQ
                        ,   CheckingClaimAmount =   CheckingClaimAmount + v_InsReceiptUseAmount     -- �������|�������z���v
                        ,   CheckingUseAmount   =   CheckingUseAmount + v_InsReceiptUseAmount       -- �������|���p�z
                        ,   BalanceClaimAmount  =   BalanceClaimAmount - v_InsReceiptUseAmount      -- �c�����|�c�����v
                        ,   BalanceUseAmount    =   BalanceUseAmount - v_InsReceiptUseAmount        -- �c�����|���p�z
                        ,   ReceiptAmountTotal  =   ReceiptAmountTotal + pi_receipt_amount          -- �����z���v
                        ,   SundryLossTotal     =   SundryLossTotal - v_SundryAmount                -- �G�������v
                        ,   UpdateDate          =   NOW()
                        ,   UpdateId            =   pi_user_id
                    WHERE   ClaimId =   v_ClaimId
                    ;
                END IF;
            END IF;
        END IF;
    END IF;
END$$