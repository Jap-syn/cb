DROP FUNCTION IF EXISTS F_GetTaxRate;

DELIMITER $$

CREATE FUNCTION F_GetTaxRate( pi_businessdate DATE )
RETURNS INT
BEGIN
/****************************************************************************************
 *
 * ﾌｧﾝｸｼｮﾝ名        ：  F_GetTaxRate
 *
 * 概要             ：  消費税率取得関数
 *
 * 引数             ：  [I/ ]pi_businessdate              処理日付
 *
 * 戻り値           ：  取得した消費税率
 *
 * 履歴             ：  2015/10/01  NDC 新規作成
 *
 ***************************************************************************************/

    -- 変数宣言
    DECLARE v_TaxRate           FLOAT;                      -- 消費税率
    DECLARE v_TaxRateDefault    FLOAT;                      -- 消費税率（ﾃﾞﾌｫﾙﾄ）
    
    -- ｶｰｿﾙ用
    DECLARE v_Name              VARCHAR(255);               -- ﾌﾟﾛﾊﾟﾃｨ名
    DECLARE v_Value             TEXT;                       -- ﾌﾟﾛﾊﾟﾃｨ値

    -- その他
    DECLARE v_done              INT;

    -- ｶｰｿﾙ宣言
    DECLARE cur CURSOR FOR
        SELECT 
             Name
            ,PropValue
        FROM
            T_SystemProperty
        WHERE
            Category = 'taxconf'
        ORDER BY Name ASC;

    -- ﾊﾝﾄﾞﾗｰ
    DECLARE CONTINUE HANDLER FOR SQLSTATE '02000' SET v_done = 1;   -- cursor読み取り終了

    SET v_TaxRate = 0;
    SET v_TaxRateDefault = 0;
    
    -- 消費税率取得処理
    OPEN cur;

    READ_LOOP :LOOP
        FETCH cur INTO
             v_Name
            ,v_Value;

        IF pi_BusinessDate > v_Name THEN
            SET v_TaxRate = SUBSTRING_INDEX( v_Value, ':', 1 );
        END IF;

        IF v_Name = '[DEFAULT]' THEN
            SET v_TaxRateDefault = SUBSTRING_INDEX( v_Value, ':', 1 );
        END IF;

        IF v_done THEN LEAVE READ_LOOP;
        END IF;

    END LOOP;

    -- ｶｰｿﾙｸﾛｰｽﾞ
    CLOSE cur;
    
    -- 取得した消費税率を返す。
    IF v_TaxRate = 0 THEN
        RETURN  v_TaxRateDefault;
    ELSE
        RETURN  v_TaxRate;
    END IF;

END
$$

DELIMITER ;
