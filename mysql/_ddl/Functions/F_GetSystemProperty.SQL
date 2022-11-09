DROP FUNCTION IF EXISTS F_GetSystemProperty;

DELIMITER $$

CREATE FUNCTION F_GetSystemProperty(pi_module VARCHAR(50), pi_category VARCHAR(50), pi_name VARCHAR(255))
RETURNS text
BEGIN
/******************************************************************************
 *
 * ファンクション名：F_GetSystemProperty
 *
 * 概要            ：T_SystemPropertyよりプロパティ値を取得する
 *
 * 引数            ：[I/ ]pi_module               モジュール
 *                   [I/ ]pi_category             カテゴリー
 *                   [I/ ]pi_name                 プロパティ名
 *
 * 戻り値          ：プロパティ値
 *
 * 履歴            ：2015/10/01 NDC 新規作成
 *
 *****************************************************************************/

    -- 変数宣言部
    DECLARE v_PropValue TEXT DEFAULT '';

    -- (その他)
    DECLARE no_data_found INT DEFAULT 1;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET no_data_found = 0;

    SELECT PropValue
    INTO   v_PropValue
    FROM   T_SystemProperty
    WHERE  Module   = pi_module
    AND    Category = pi_category
    AND    Name     = pi_name;

    -- 該当無し時はエラーで戻る
    IF no_data_found = 0 THEN
        RETURN '';
    END IF;

    RETURN v_PropValue;
END