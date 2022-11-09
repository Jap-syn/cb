DROP FUNCTION IF EXISTS F_GetCode;

DELIMITER $$

CREATE FUNCTION F_GetCode(pi_code_id INT, pi_key_code INT, pi_class_id INT)
RETURNS text
BEGIN
/******************************************************************************
 *
 * ファンクション名：F_GetCode
 *
 * 概要            ：M_Codeより区分値を取得する
 *
 * 引数            ：[I/ ]pi_code_id                コード識別ID
 *                   [I/ ]pi_key_code               KEYコード
 *                   [I/ ]pi_class_id               区分名
 *
 * 戻り値          ：区分値
 *
 * 履歴            ：2015/10/01 NDC 新規作成
 *
 *****************************************************************************/

    -- 変数宣言部
    DECLARE v_class VARCHAR(30) DEFAULT '';

    -- (その他)
    DECLARE no_data_found INT DEFAULT 1;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET no_data_found = 0;

    SELECT CASE pi_class_id
           WHEN 1 THEN Class1
           WHEN 2 THEN Class2
           WHEN 3 THEN Class3
           END
    INTO   v_class
    FROM   M_Code
    WHERE  CodeId  = pi_code_id
    AND    KeyCode = pi_key_code;

    -- 該当無し時はエラーで戻る
    IF no_data_found = 0 THEN
        RETURN '';
    END IF;

    RETURN v_class;
END