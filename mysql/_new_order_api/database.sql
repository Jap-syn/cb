-- =============================================================================
--  C��
-- =============================================================================
CREATE DATABASE coraldb_test DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE USER 'coraluser'@'%' IDENTIFIED BY 'coralmaster';
GRANT ALL PRIVILEGES ON coraldb_test . * TO 'coraluser'@'%' WITH GRANT OPTION;
FLUSH PRIVILEGES;


-- =============================================================================
--  A��
-- =============================================================================
CREATE DATABASE coraldb DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE USER 'coraluser'@'%' IDENTIFIED BY 'coralmaster';
GRANT ALL PRIVILEGES ON coraldb . * TO 'coraluser'@'%' WITH GRANT OPTION;
FLUSH PRIVILEGES;
