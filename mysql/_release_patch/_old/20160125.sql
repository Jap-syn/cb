-- [001-IL-278]コードマスタへのINERT文
INSERT INTO M_Code VALUES(72,284 ,'事業者電話番号','{Phone}' ,'16' , NULL ,NULL ,1, NOW(), 1, NOW(), 1, 1);


-- [001-IL-272]立替精算戻しを「しない」に更新する
UPDATE T_Site
SET    PayingBackFlg = 0    -- 「しない」へ更新する
;

-- [001-IL-265]伝票間違いメールのﾊﾟﾗﾒｰﾀｰ修正
UPDATE T_MailTemplate SET Body = REPLACE(Body, '{NameKj}', '{CustomerNameKj}') WHERE Class = 15;

