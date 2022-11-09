DELIMITER $$
CREATE PROCEDURE `var_dump`(IN $name TEXT, IN $value TEXT)
BEGIN
          /**
           * デバッグ用プロシージャ
           * @param TEXT $name 変数名
           * @param TEXT $value 変数値
           */

          -- テーブルにダンプする
          INSERT INTO `var_dump` SET `name` = $name, `value` = $value, `created` = NOW();
     END$$
DELIMITER ;
