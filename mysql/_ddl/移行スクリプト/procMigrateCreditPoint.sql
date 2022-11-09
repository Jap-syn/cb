DROP procedure IF EXISTS `procMigrateCreditPoint`;

DELIMITER $$
CREATE PROCEDURE `procMigrateCreditPoint` ()
BEGIN
    /* 移行処理：社内与信ポイントマスター */

    DECLARE
        updDttm    datetime;
/*
    INSERT INTO `M_CreditPoint`(`CreditCriterionId`,`CpId`,`Caption`,`Point`,`Message`,`Description`,`Dependence`,`GeneralProp`,`SetCategory`,`CreditCriterionName`,`Rate`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
	VALUES(0, 1, '青い表示の条件', 1, null, 'ポイント以上をリストで青く表示', 1, null, null, '与信判定基準1', 1, updDttm, 9, updDttm, 9, 1);

    INSERT INTO `M_CreditPoint`(`CreditCriterionId`,`CpId`,`Caption`,`Point`,`Message`,`Description`,`Dependence`,`GeneralProp`,`SetCategory`,`CreditCriterionName`,`Rate`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
	VALUES(0, 2, '赤い表示の条件', -1, null, 'ポイント以上をリストで赤く表示', 1, null, null, '与信判定基準1', 1, updDttm, 9, updDttm, 9, 1);

    INSERT INTO `M_CreditPoint`(`CreditCriterionId`,`CpId`,`Caption`,`Point`,`Message`,`Description`,`Dependence`,`GeneralProp`,`SetCategory`,`CreditCriterionName`,`Rate`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
	VALUES(0, 3, '優良顧客住所', 1000, '優良顧客です', null, 1, null, null, '与信判定基準1', 1, updDttm, 9, updDttm, 9, 1);

    INSERT INTO `M_CreditPoint`(`CreditCriterionId`,`CpId`,`Caption`,`Point`,`Message`,`Description`,`Dependence`,`GeneralProp`,`SetCategory`,`CreditCriterionName`,`Rate`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
	VALUES(0, 4, '優良顧客氏名', 0, '優良顧客と氏名一致', null, 1, null, null, '与信判定基準1', 1, updDttm, 9, updDttm, 9, 1);

    INSERT INTO `M_CreditPoint`(`CreditCriterionId`,`CpId`,`Caption`,`Point`,`Message`,`Description`,`Dependence`,`GeneralProp`,`SetCategory`,`CreditCriterionName`,`Rate`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
	VALUES(0, 5, 'ブラック住所', -1000, 'ブラックです', null, 1, null, null, '与信判定基準1', 1, updDttm, 9, updDttm, 9, 1);

    INSERT INTO `M_CreditPoint`(`CreditCriterionId`,`CpId`,`Caption`,`Point`,`Message`,`Description`,`Dependence`,`GeneralProp`,`SetCategory`,`CreditCriterionName`,`Rate`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
	VALUES(0, 6, 'ブラック氏名', -10, 'ブラック氏名と一致', null, 1, null, null, '与信判定基準1', 1, updDttm, 9, updDttm, 9, 1);

    INSERT INTO `M_CreditPoint`(`CreditCriterionId`,`CpId`,`Caption`,`Point`,`Message`,`Description`,`Dependence`,`GeneralProp`,`SetCategory`,`CreditCriterionName`,`Rate`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
	VALUES(0, 7, '再請求住所', 0, '過去に再請求あり', null, 1, null, null, '与信判定基準1', 1, updDttm, 9, updDttm, 9, 1);

    INSERT INTO `M_CreditPoint`(`CreditCriterionId`,`CpId`,`Caption`,`Point`,`Message`,`Description`,`Dependence`,`GeneralProp`,`SetCategory`,`CreditCriterionName`,`Rate`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
	VALUES(0, 8, '再々請求住所', 0, '過去に再2回目あり', null, 1, null, null, '与信判定基準1', 1, updDttm, 9, updDttm, 9, 1);

    INSERT INTO `M_CreditPoint`(`CreditCriterionId`,`CpId`,`Caption`,`Point`,`Message`,`Description`,`Dependence`,`GeneralProp`,`SetCategory`,`CreditCriterionName`,`Rate`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
	VALUES(0, 9, '住所相違', -20, '同一氏名別住所', null, 1, null, null, '与信判定基準1', 1, updDttm, 9, updDttm, 9, 1);

    INSERT INTO `M_CreditPoint`(`CreditCriterionId`,`CpId`,`Caption`,`Point`,`Message`,`Description`,`Dependence`,`GeneralProp`,`SetCategory`,`CreditCriterionName`,`Rate`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
	VALUES(0, 10, '郵便番号チェック', 0, '郵便番号と住所が矛盾あり', null, 1, null, null, '与信判定基準1', 1, updDttm, 9, updDttm, 9, 1);

    INSERT INTO `M_CreditPoint`(`CreditCriterionId`,`CpId`,`Caption`,`Point`,`Message`,`Description`,`Dependence`,`GeneralProp`,`SetCategory`,`CreditCriterionName`,`Rate`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
	VALUES(0, 11, '限度額オーバー', 0, null, null, 1, null, null, '与信判定基準1', 1, updDttm, 9, updDttm, 9, 1);

    INSERT INTO `M_CreditPoint`(`CreditCriterionId`,`CpId`,`Caption`,`Point`,`Message`,`Description`,`Dependence`,`GeneralProp`,`SetCategory`,`CreditCriterionName`,`Rate`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
	VALUES(0, 12, 'メールチェック：OK', 0, 'アドレスOK', null, 1, null, null, '与信判定基準1', 1, updDttm, 9, updDttm, 9, 1);
    
    INSERT INTO `M_CreditPoint`(`CreditCriterionId`,`CpId`,`Caption`,`Point`,`Message`,`Description`,`Dependence`,`GeneralProp`,`SetCategory`,`CreditCriterionName`,`Rate`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
	VALUES(0, 13, 'メールチェック：NG', 0, 'アドレスNG', null, 1, null, null, '与信判定基準1', 1, updDttm, 9, updDttm, 9, 1);

    INSERT INTO `M_CreditPoint`(`CreditCriterionId`,`CpId`,`Caption`,`Point`,`Message`,`Description`,`Dependence`,`GeneralProp`,`SetCategory`,`CreditCriterionName`,`Rate`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
	VALUES(0, 14, '電話：コールのみ', 0, null, null, 1, null, null, '与信判定基準1', 1, updDttm, 9, updDttm, 9, 1);

    INSERT INTO `M_CreditPoint`(`CreditCriterionId`,`CpId`,`Caption`,`Point`,`Message`,`Description`,`Dependence`,`GeneralProp`,`SetCategory`,`CreditCriterionName`,`Rate`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
	VALUES(0, 15, '電話：機械留守電', 0, null, null, 1, null, null, '与信判定基準1', 1, updDttm, 9, updDttm, 9, 1);

    INSERT INTO `M_CreditPoint`(`CreditCriterionId`,`CpId`,`Caption`,`Point`,`Message`,`Description`,`Dependence`,`GeneralProp`,`SetCategory`,`CreditCriterionName`,`Rate`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
	VALUES(0, 16, '電話：生声留守電', 0, null, null, 1, null, null, '与信判定基準1', 1, updDttm, 9, updDttm, 9, 1);

    INSERT INTO `M_CreditPoint`(`CreditCriterionId`,`CpId`,`Caption`,`Point`,`Message`,`Description`,`Dependence`,`GeneralProp`,`SetCategory`,`CreditCriterionName`,`Rate`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
	VALUES(0, 17, '電話：本人OK', 0, null, null, 1, null, null, '与信判定基準1', 1, updDttm, 9, updDttm, 9, 1);

    INSERT INTO `M_CreditPoint`(`CreditCriterionId`,`CpId`,`Caption`,`Point`,`Message`,`Description`,`Dependence`,`GeneralProp`,`SetCategory`,`CreditCriterionName`,`Rate`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
	VALUES(0, 18, '電話：家族OK', 0, null, null, 1, null, null, '与信判定基準1', 1, updDttm, 9, updDttm, 9, 1);

    INSERT INTO `M_CreditPoint`(`CreditCriterionId`,`CpId`,`Caption`,`Point`,`Message`,`Description`,`Dependence`,`GeneralProp`,`SetCategory`,`CreditCriterionName`,`Rate`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
	VALUES(0, 19, '電話：未払い／存在せず／他人', 0, null, null, 1, null, null, '与信判定基準1', 1, updDttm, 9, updDttm, 9, 1);

    INSERT INTO `M_CreditPoint`(`CreditCriterionId`,`CpId`,`Caption`,`Point`,`Message`,`Description`,`Dependence`,`GeneralProp`,`SetCategory`,`CreditCriterionName`,`Rate`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
	VALUES(0, 20, '請求総額設定①', -5, '★☆★与信しなさい☆★☆', '以上の総額時', 2, 10000, null, '与信判定基準1', 1, updDttm, 9, updDttm, 9, 1);

    INSERT INTO `M_CreditPoint`(`CreditCriterionId`,`CpId`,`Caption`,`Point`,`Message`,`Description`,`Dependence`,`GeneralProp`,`SetCategory`,`CreditCriterionName`,`Rate`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
	VALUES(0, 21, '請求総額設定②', 0, '逆に警戒額です', '以下の総額時', 2, 300, null, '与信判定基準1', 1, updDttm, 9, updDttm, 9, 1);

    INSERT INTO `M_CreditPoint`(`CreditCriterionId`,`CpId`,`Caption`,`Point`,`Message`,`Description`,`Dependence`,`GeneralProp`,`SetCategory`,`CreditCriterionName`,`Rate`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
	VALUES(0, 22, '「要TEL判断」', 0, null, '以上の時MSG表示', 4, 0, null, '与信判定基準1', 1, updDttm, 9, updDttm, 9, 1);

    INSERT INTO `M_CreditPoint`(`CreditCriterionId`,`CpId`,`Caption`,`Point`,`Message`,`Description`,`Dependence`,`GeneralProp`,`SetCategory`,`CreditCriterionName`,`Rate`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
	VALUES(0, 23, '住所', -20, '特定住所に該当あり', '指定住所を含む時', 3, null, 1, '与信判定基準1', 1, updDttm, 9, updDttm, 9, 1);

    INSERT INTO `M_CreditPoint`(`CreditCriterionId`,`CpId`,`Caption`,`Point`,`Message`,`Description`,`Dependence`,`GeneralProp`,`SetCategory`,`CreditCriterionName`,`Rate`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
	VALUES(0, 24, '氏名', -10, '特定氏名に該当あり', '指定氏名を含む時', 3, null, 2, '与信判定基準1', 1, updDttm, 9, updDttm, 9, 1);

    INSERT INTO `M_CreditPoint`(`CreditCriterionId`,`CpId`,`Caption`,`Point`,`Message`,`Description`,`Dependence`,`GeneralProp`,`SetCategory`,`CreditCriterionName`,`Rate`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
	VALUES(0, 25, 'メールアドレス', -300, '★★極悪メール★★', '指定ドメインを含む時', 3, null, 3, '与信判定基準1', 1, updDttm, 9, updDttm, 9, 1);

    INSERT INTO `M_CreditPoint`(`CreditCriterionId`,`CpId`,`Caption`,`Point`,`Message`,`Description`,`Dependence`,`GeneralProp`,`SetCategory`,`CreditCriterionName`,`Rate`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
	VALUES(0, 26, '商品名', -30, '★★警戒商品あり★★', '指定商品を含む時', 3, null, 4, '与信判定基準1', 1, updDttm, 9, updDttm, 9, 1);

    INSERT INTO `M_CreditPoint`(`CreditCriterionId`,`CpId`,`Caption`,`Point`,`Message`,`Description`,`Dependence`,`GeneralProp`,`SetCategory`,`CreditCriterionName`,`Rate`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
	VALUES(0, 27, '事業者ID', 0, '多被害店舗', '指定事業者IDを含む時', 3, null, 5, '与信判定基準1', 1, updDttm, 9, updDttm, 9, 1);

    INSERT INTO `M_CreditPoint`(`CreditCriterionId`,`CpId`,`Caption`,`Point`,`Message`,`Description`,`Dependence`,`GeneralProp`,`SetCategory`,`CreditCriterionName`,`Rate`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
	VALUES(0, 28, '優良住所', 100, '優良住所', '指定住所を含む時', 3, null, 6, '与信判定基準1', 1, updDttm, 9, updDttm, 9, 1);

    INSERT INTO `M_CreditPoint`(`CreditCriterionId`,`CpId`,`Caption`,`Point`,`Message`,`Description`,`Dependence`,`GeneralProp`,`SetCategory`,`CreditCriterionName`,`Rate`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
	VALUES(0, 29, '優良事業者ID', 0, '優良事業者ID', '指定事業者IDを含む時', 3, null, 7, '与信判定基準1', 1, updDttm, 9, updDttm, 9, 1);
*/
    
END$$

DELIMITER ;

