/*cbadminのデータベース*/
-- M_Code
-- 185 --
UPDATE `M_Code` SET `Note` = '<table><tr><td style=\"font-size: 14px ;font-weight: normal\"><B>注文情報確認ページの閲覧は一定期間で終了いたします。</td></tr><tr><td style=\"font-size: 14px ;font-weight: normal\"><B>注文情報のお問い合わせにつきましては弊社サポートセンターまでお問い合わせください。</td></tr><tr><td style=\"font-size: 14px ;font-weight: normal\"><B>サポートセンター電話番号： TEL: 0120-667-690（9:00 ～ 18:00）</td></tr><tr><td style=\"font-size: 14px ;font-weight: nrmal\"><B>または、請求書に記載されている情報を元に<a href=\"login/login\"><U>こちら</U></a>へログインしてください。</td></tr></table>', UpdateDate = now(), UpdateId = 1 WHERE (`CodeId` = '185') and (`KeyCode` = '0');
-- 186 --
UPDATE `M_Code` SET `Note` = '<table><tr><td style=\"font-size: 14px ;font-weight: normal\"><B>注文情報確認ページの閲覧は一定期間で終了いたします。</td></tr><tr><td style=\"font-size: 14px ;font-weight: normal\"><B>注文情報のお問い合わせにつきましては弊社サポートセンターまでお問い合わせください。</td></tr><tr><td style=\"font-size: 14px ;font-weight: normal\"><B>サポートセンター電話番号： TEL: 0120-667-690（9:00 ～ 18:00）</td></tr><tr><td style=\"font-size: 14px ;font-weight: nrmal\"><B>または、請求書に記載されている情報を元に<a href=\"login/login\"><U>こちら</U></a>へログインしてください。</td></tr></table>', UpdateDate = now(), UpdateId = 1 WHERE (`CodeId` = '186') and (`KeyCode` = '0');
-- 199 --
UPDATE `M_Code` SET `Note`='https://atobarai-user.jp/todoitekara/#linepay', UpdateDate = now(), UpdateId = 1 WHERE `CodeId`='199' and`KeyCode`='22';
UPDATE `M_Code` SET `Note`='https://atobarai-user.jp/todoitekara/#post-office', UpdateDate = now(), UpdateId = 1 WHERE `CodeId`='199' and`KeyCode`='23';
UPDATE `M_Code` SET `Note`='https://atobarai-user.jp/todoitekara/#bank', UpdateDate = now(), UpdateId = 1 WHERE `CodeId`='199' and`KeyCode`='24';

-- T_SystemProperty
UPDATE T_SystemProperty SET PropValue = REPLACE(PropValue, '後払い.COM会員サービス', '株式会社キャッチボール会員サービス') WHERE Name="MembershipAgreement"

-- T_MailTemplate
-- delete mail class 106 and 107
DELETE FROM `T_MailTemplate` WHERE (`Class` = '106');
DELETE FROM `T_MailTemplate` WHERE (`Class` = '107');
insert into `T_MailTemplate`(`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (127,'マイページ連携バッチエラー','届いてから払い','=?UTF-8?B?GyRCRk8kJCRGJCskaUonJCQbKEI=?=','todoitekara@ato-barai.com',null,null,null,'マイページ連携バッチエラー','=?UTF-8?B?5bGK44GE44Gm44GL44KJ5omV44GE?=','担当者様

マイページ → 基幹システムの連携バッチでエラーが発生しました。
基幹システムの入金情報が反映されていない可能性があります。

＜対象注文＞
{OrderId}

以上',0,now(),1,now(),1,1);

-- T_ToBackMypageIF
CREATE TABLE `T_ToBackMypageIF` (
    `Seq` BIGINT(20) NOT NULL AUTO_INCREMENT COMMENT 'ｼｰｹﾝｽ',
    `Status` TINYINT(4) NOT NULL COMMENT 'ステータス 0：指示、1：クローズ、9：エラー',
    `IFClass` TINYINT(4) NOT NULL COMMENT '連携区分 1：入金 2：入金取消',
    `IFData` text NULL DEFAULT NULL COMMENT'連携内容 JSON形式で保存。内容は連携区分毎に異なる。 ※シート「基幹反映指示インタフェース項目一覧」参照',
    `OrderSeq` BIGINT(20) NOT NULL COMMENT '注文SEQ',
    `RegistDate` DATETIME NOT NULL COMMENT '登録日時',
    `UpdateDate` DATETIME NOT NULL COMMENT '更新日時',
    `ValidFlg` TINYINT(4) NOT NULL DEFAULT '1' COMMENT '有効フラグ',
    PRIMARY KEY (`Seq`),
    KEY `i1` (`Status`),
    KEY `i2` (`OrderSeq`),
    KEY `i3` (`RegistDate`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- M_SbpsPayment
UPDATE `M_SbpsPayment` SET `LogoUrl`='my_page/todo_docomo.jpg' WHERE `SbpsPaymentId`='7';
UPDATE `M_SbpsPayment` SET `LogoUrl`='my_page/todo_au.jpg' WHERE `SbpsPaymentId`='8';
UPDATE `M_SbpsPayment` SET `MailParameterNameKj`='auかんたん決済 (au / UQ mobile)' WHERE `SbpsPaymentId`='8';