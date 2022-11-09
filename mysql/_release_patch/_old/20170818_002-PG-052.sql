/* 加盟店マスタテーブルの更新 */
UPDATE T_Enterprise
SET HoldBoxFlg = 0;

/* サイトテーブルの更新 */
UPDATE T_Site
SET NgChangeFlg = 0
   ,ShowNgReason = 0;

