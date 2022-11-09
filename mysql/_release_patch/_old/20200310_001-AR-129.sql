/* T_OrderNotClose 登録 */
DROP TABLE IF EXISTS `T_OrderNotClose`;
CREATE TABLE `T_OrderNotClose`
( `OrderSeq`   bigint(20) NOT NULL
, `RegistDate` datetime   DEFAULT NULL
, `RegistId`   int(11)    DEFAULT NULL
, `UpdateDate` datetime   DEFAULT NULL
, `UpdateId`   int(11)    DEFAULT NULL
, `ValidFlg`   int(11)    NOT NULL DEFAULT '1'
, PRIMARY KEY (`OrderSeq`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
;

/* 現状未完了注文データ（２年前） */
INSERT INTO T_OrderNotClose ( OrderSeq, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
SELECT OrderSeq, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg
  FROM T_Order
 WHERE RegistDate > DATE_ADD(NOW(), INTERVAL -2 YEAR )
   AND DataStatus <> 91
;
