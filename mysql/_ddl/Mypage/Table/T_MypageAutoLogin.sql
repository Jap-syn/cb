CREATE TABLE `T_MypageAutoLogin` (
  `MailAddress` varchar(255) NOT NULL,
  `AutoLoginKey` varchar(255) NOT NULL,
  `BrowserInfo` varchar(4000) NOT NULL,
  `RegistDate` datetime DEFAULT NULL,
  `UpdateDate` datetime DEFAULT NULL,
  `OemId` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
