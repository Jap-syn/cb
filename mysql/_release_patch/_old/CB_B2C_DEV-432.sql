-- コメント設定・修正画面：区分１，２使用可
UPDATE M_CodeManagement
    SET
		Class1ValidFlg=1,
		Class2ValidFlg=1 
	WHERE
		CodeId = 198;

-- 入金方法：区分２更新
UPDATE M_Code
	SET
		Class2 = CASE WHEN KeyCode
			IN (4, 5, 6, 7, 10, 13, 14)
			THEN 1
			ELSE 0
		END
	WHERE
		CodeId = 198;