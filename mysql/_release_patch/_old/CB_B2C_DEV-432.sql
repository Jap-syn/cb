-- �R�����g�ݒ�E�C����ʁF�敪�P�C�Q�g�p��
UPDATE M_CodeManagement
    SET
		Class1ValidFlg=1,
		Class2ValidFlg=1 
	WHERE
		CodeId = 198;

-- �������@�F�敪�Q�X�V
UPDATE M_Code
	SET
		Class2 = CASE WHEN KeyCode
			IN (4, 5, 6, 7, 10, 13, 14)
			THEN 1
			ELSE 0
		END
	WHERE
		CodeId = 198;