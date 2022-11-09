DROP FUNCTION IF EXISTS F_GetLoginUserName;

DELIMITER $$

CREATE FUNCTION F_GetLoginUserName(pi_user_id INT)
RETURNS VARCHAR(160)
BEGIN
/******************************************************************************
 *
 * ファンクション名：F_GetLoginUserName
 *
 * 概要            ：T_User.UserIdより名称を取得する
 *
 * 引数            ：[I/ ]pi_user_id                                 ﾕｰｻﾞｰID
 *
 * 戻り値          ：ﾕｰｻﾞ名文字列
 *
 * 履歴            ：2015/03/25 NDC 新規作成
 *
 *****************************************************************************/

    -- 変数宣言部
    DECLARE v_Count INT(11) DEFAULT 0;
    DECLARE v_UserClass INT(11) DEFAULT 0;
    DECLARE v_Seq BIGINT(20) DEFAULT 0;
    DECLARE v_NameKj VARCHAR(160) DEFAULT '';

    -- (その他)
    DECLARE no_data_found INT DEFAULT 1;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET no_data_found = 0;

    -- T_UserからUserClass／Seqの抽出
    SELECT UserClass, Seq INTO v_UserClass, v_Seq FROM T_User WHERE UserId = pi_user_id;

    -- 該当無し時はエラーで戻る
    IF no_data_found = 0 THEN
        RETURN '';
    END IF;

    -- UserClassにより抽出対象テーブルが異なる
    IF v_UserClass = 0 THEN
        -- T_Operatorよりの抽出
        SELECT NameKj INTO v_NameKj FROM T_Operator WHERE OpId = v_Seq;
    ELSEIF v_UserClass = 1 THEN
        -- T_OemOperatorよりの抽出
        SELECT NameKj INTO v_NameKj FROM T_OemOperator WHERE OemOpId = v_Seq;
    ELSEIF v_UserClass = 2 THEN
        -- T_Enterpriseよりの抽出
        SELECT EnterpriseNameKj INTO v_NameKj FROM T_Enterprise WHERE EnterpriseId = v_Seq;
    ELSEIF v_UserClass = 3 THEN
        -- T_ApiUserよりの抽出
        SELECT ApiUserNameKj INTO v_NameKj FROM T_ApiUser WHERE ApiUserId = v_Seq;
    ELSEIF v_UserClass = 99 THEN
        -- T_Codeよりの抽出
        SELECT KeyContent INTO v_NameKj FROM M_Code WHERE CodeId = 86 AND KeyCode = v_Seq;
    END IF;

    -- 該当無し時はエラーで戻る
    IF no_data_found = 0 THEN
        RETURN '';
    END IF;

    RETURN v_NameKj;
END
$$

DELIMITER ;

