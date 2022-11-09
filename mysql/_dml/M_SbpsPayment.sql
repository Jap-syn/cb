INSERT INTO `M_SbpsPayment` (`SbpsPaymentId`, `OemId`, `PaymentGroupName`, `PaymentName`, `PaymentNameKj`, `SortId`, `LogoUrl`, `CancelApiId`, `MailParameterNameKj`, `ValidFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`) VALUES
	(1, 0, '届いてから', 'credit', 'クレジット(VISA/MASTER）', 1, 'my_page/credit_VISA-Master.png', 'ST02-00303-101', 'クレジット', 1, now(), 1, now(), 1),
	(2, 0, '届いてから', 'credit', 'クレジット(JCB/AMEX）', 2, 'my_page/credit_JCB-Amex.png', 'ST02-00303-101', 'クレジット', 1, now(), 1, now(), 1),
	(3, 0, '届いてから', 'credit', 'クレジット(Dinars）', 3, 'my_page/credit_DinersClub.png', 'ST02-00303-101', 'クレジット', 1, now(), 1, now(), 1),
	(4, 0, '届いてから', 'paypay', 'PayPay（オンライン決済）', 4, 'my_page/todo_paypay.png', 'ST02-00306-311', 'PayPay（オンライン決済）', 1, now(), 1, now(), 1),
	(5, 0, '届いてから', 'linepay', 'LINEPay', 5, 'my_page/todo_LINEpay.png', 'ST02-00306-310', 'LINEPay', 1, now(), 1, now(), 1),
	(6, 0, '届いてから', 'softbank2', 'ソフトバンクまとめて支払い', 6, 'my_page/todo_softbank.png', 'ST02-00303-405', 'ソフトバンクまとめて支払い,ワイモバイルまとめて支払い', 1, now(), 1, now(), 1),
	(7, 0, '届いてから', 'docomo', 'ドコモ払い', 7, 'my_page/todo_docomo.png', 'ST02-00303-401', 'ドコモ払い', 1, now(), 1, now(), 1),
	(8, 0, '届いてから', 'auone', 'auかんたん決済', 8, 'my_page/todo_au.png', 'ST02-00303-402', 'auかんたん決済', 1, now(), 1, now(), 1),
	(9, 0, '届いてから', 'rakuten', '楽天ペイ（オンライン決済）', 9, 'my_page/todo_Rakuten.png', 'ST02-00306-305', '楽天ペイ（オンライン決済）', 1, now(), 1, now(), 1);
	