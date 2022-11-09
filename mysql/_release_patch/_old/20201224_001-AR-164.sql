/* システムプロパティ登録( SMBCパーフェクト用ディレクトリ指定 ) */
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES ('[DEFAULT]','smbcpa', 'TempFileDir', '../../data/temp', 'SMBCパーフェクト用ファイル一時保存ディレクトリ', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES ('[DEFAULT]','smbcpa', 'DownloadDir', '', 'SakuraKCS用ダウンロード元ディレクトリ', NOW(), 9, NOW(), 9, '1');
