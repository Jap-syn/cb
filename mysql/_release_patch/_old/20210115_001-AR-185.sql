/* OEMテーブルに発行元会社名見出しのカラムを追加 */
ALTER TABLE T_Oem ADD COLUMN ChangeIssuerNameFlg INT NOT NULL DEFAULT 0 AFTER StyleSheets;