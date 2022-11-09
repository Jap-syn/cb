DROP FUNCTION IF EXISTS F_GetEscapeItemNameKj;

DELIMITER $$

CREATE FUNCTION F_GetEscapeItemNameKj(pi_itemnamekj VARCHAR(255))
RETURNS VARCHAR(255)
BEGIN

/******************************************************************************
 *
 * ファンクション名：F_GetEscapeItemNameKj
 *
 * 概要            ：注文商品名をエスケープする(注文履歴上の注文商品情報用)
 *
 * 引数            ：[I/ ]pi_itemnamekj                              注文商品名(T_OrderItems.ItemNameKj)
 *
 * 戻り値          ：エスケープされた注文商品名
 *
 * 履歴            ：2015/11/18 NDC 新規作成
 *
 *****************************************************************************/

    -- 変数宣言部
    DECLARE v_ItemNameKj VARCHAR(255) DEFAULT '';

    SET v_ItemNameKj = pi_itemnamekj;
    SELECT REPLACE(v_ItemNameKj, ',', '\,') INTO v_ItemNameKj;
    SELECT REPLACE(v_ItemNameKj, '"', '\\"') INTO v_ItemNameKj;

    RETURN v_ItemNameKj;

END
$$

DELIMITER ;
