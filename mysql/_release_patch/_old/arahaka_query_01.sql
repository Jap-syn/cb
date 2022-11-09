CREATE TABLE `T_LoginMemberLog` (
  `LogId` bigint(20) NOT NULL auto_increment,
  `EnterpriseId` bigint(20) NOT NULL,
  `EnterpriseLoginId` text NOT NULL,
  `OpId` bigint(20) NOT NULL,
  `OpLoginId` text NOT NULL,
  `UserAgent` text NOT NULL,
  `RegistDate` datetime NOT NULL,
   primary key(LogId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
