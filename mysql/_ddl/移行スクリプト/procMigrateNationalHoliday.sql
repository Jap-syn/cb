DROP procedure IF EXISTS `procMigrateNationalHoliday`;

DELIMITER $$
CREATE PROCEDURE `procMigrateNationalHoliday` ()
BEGIN

    /* 移行処理：祝日マスター */

    DECLARE
        updDttm    datetime;    -- 更新日時

    SET updDttm = now();

    INSERT INTO `M_NationalHoliday` (`BusinessDate`,`NationalHolidayName`,`NationalHolidayFlg`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)VALUES('2015/1/1','元日',1,updDttm,9,updDttm,9,1);
    INSERT INTO `M_NationalHoliday` (`BusinessDate`,`NationalHolidayName`,`NationalHolidayFlg`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)VALUES('2015/1/12','成人の日',1,updDttm,9,updDttm,9,1);
    INSERT INTO `M_NationalHoliday` (`BusinessDate`,`NationalHolidayName`,`NationalHolidayFlg`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)VALUES('2015/2/11','建国記念の日',1,updDttm,9,updDttm,9,1);
    INSERT INTO `M_NationalHoliday` (`BusinessDate`,`NationalHolidayName`,`NationalHolidayFlg`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)VALUES('2015/3/21','春分の日',1,updDttm,9,updDttm,9,1);
    INSERT INTO `M_NationalHoliday` (`BusinessDate`,`NationalHolidayName`,`NationalHolidayFlg`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)VALUES('2015/4/29','昭和の日',1,updDttm,9,updDttm,9,1);
    INSERT INTO `M_NationalHoliday` (`BusinessDate`,`NationalHolidayName`,`NationalHolidayFlg`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)VALUES('2015/5/3','憲法記念日',1,updDttm,9,updDttm,9,1);
    INSERT INTO `M_NationalHoliday` (`BusinessDate`,`NationalHolidayName`,`NationalHolidayFlg`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)VALUES('2015/5/4','みどりの日',1,updDttm,9,updDttm,9,1);
    INSERT INTO `M_NationalHoliday` (`BusinessDate`,`NationalHolidayName`,`NationalHolidayFlg`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)VALUES('2015/5/5','こどもの日',1,updDttm,9,updDttm,9,1);
    INSERT INTO `M_NationalHoliday` (`BusinessDate`,`NationalHolidayName`,`NationalHolidayFlg`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)VALUES('2015/7/20','海の日',1,updDttm,9,updDttm,9,1);
    INSERT INTO `M_NationalHoliday` (`BusinessDate`,`NationalHolidayName`,`NationalHolidayFlg`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)VALUES('2015/9/21','敬老の日',1,updDttm,9,updDttm,9,1);
    INSERT INTO `M_NationalHoliday` (`BusinessDate`,`NationalHolidayName`,`NationalHolidayFlg`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)VALUES('2015/9/23','秋分の日',1,updDttm,9,updDttm,9,1);
    INSERT INTO `M_NationalHoliday` (`BusinessDate`,`NationalHolidayName`,`NationalHolidayFlg`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)VALUES('2015/10/12','体育の日',1,updDttm,9,updDttm,9,1);
    INSERT INTO `M_NationalHoliday` (`BusinessDate`,`NationalHolidayName`,`NationalHolidayFlg`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)VALUES('2015/11/3','文化の日',1,updDttm,9,updDttm,9,1);
    INSERT INTO `M_NationalHoliday` (`BusinessDate`,`NationalHolidayName`,`NationalHolidayFlg`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)VALUES('2015/11/23','勤労感謝の日',1,updDttm,9,updDttm,9,1);
    INSERT INTO `M_NationalHoliday` (`BusinessDate`,`NationalHolidayName`,`NationalHolidayFlg`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)VALUES('2015/12/23','天皇誕生日',1,updDttm,9,updDttm,9,1);
    INSERT INTO `M_NationalHoliday` (`BusinessDate`,`NationalHolidayName`,`NationalHolidayFlg`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)VALUES('2016/1/1','元日',1,updDttm,9,updDttm,9,1);
    INSERT INTO `M_NationalHoliday` (`BusinessDate`,`NationalHolidayName`,`NationalHolidayFlg`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)VALUES('2016/1/11','成人の日',1,updDttm,9,updDttm,9,1);
    INSERT INTO `M_NationalHoliday` (`BusinessDate`,`NationalHolidayName`,`NationalHolidayFlg`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)VALUES('2016/2/11','建国記念の日',1,updDttm,9,updDttm,9,1);
    INSERT INTO `M_NationalHoliday` (`BusinessDate`,`NationalHolidayName`,`NationalHolidayFlg`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)VALUES('2016/3/20','春分の日',1,updDttm,9,updDttm,9,1);
    INSERT INTO `M_NationalHoliday` (`BusinessDate`,`NationalHolidayName`,`NationalHolidayFlg`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)VALUES('2016/3/21','振替休日',1,updDttm,9,updDttm,9,1);
    INSERT INTO `M_NationalHoliday` (`BusinessDate`,`NationalHolidayName`,`NationalHolidayFlg`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)VALUES('2016/4/29','昭和の日',1,updDttm,9,updDttm,9,1);
    INSERT INTO `M_NationalHoliday` (`BusinessDate`,`NationalHolidayName`,`NationalHolidayFlg`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)VALUES('2016/5/3','憲法記念日',1,updDttm,9,updDttm,9,1);
    INSERT INTO `M_NationalHoliday` (`BusinessDate`,`NationalHolidayName`,`NationalHolidayFlg`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)VALUES('2016/5/4','みどりの日',1,updDttm,9,updDttm,9,1);
    INSERT INTO `M_NationalHoliday` (`BusinessDate`,`NationalHolidayName`,`NationalHolidayFlg`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)VALUES('2016/5/5','こどもの日',1,updDttm,9,updDttm,9,1);
    INSERT INTO `M_NationalHoliday` (`BusinessDate`,`NationalHolidayName`,`NationalHolidayFlg`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)VALUES('2016/7/18','海の日',1,updDttm,9,updDttm,9,1);
    INSERT INTO `M_NationalHoliday` (`BusinessDate`,`NationalHolidayName`,`NationalHolidayFlg`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)VALUES('2016/9/19','敬老の日',1,updDttm,9,updDttm,9,1);
    INSERT INTO `M_NationalHoliday` (`BusinessDate`,`NationalHolidayName`,`NationalHolidayFlg`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)VALUES('2016/9/22','秋分の日',1,updDttm,9,updDttm,9,1);
    INSERT INTO `M_NationalHoliday` (`BusinessDate`,`NationalHolidayName`,`NationalHolidayFlg`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)VALUES('2016/10/10','体育の日',1,updDttm,9,updDttm,9,1);
    INSERT INTO `M_NationalHoliday` (`BusinessDate`,`NationalHolidayName`,`NationalHolidayFlg`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)VALUES('2016/11/3','文化の日',1,updDttm,9,updDttm,9,1);
    INSERT INTO `M_NationalHoliday` (`BusinessDate`,`NationalHolidayName`,`NationalHolidayFlg`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)VALUES('2016/11/23','勤労感謝の日',1,updDttm,9,updDttm,9,1);
    INSERT INTO `M_NationalHoliday` (`BusinessDate`,`NationalHolidayName`,`NationalHolidayFlg`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)VALUES('2016/12/23','天皇誕生日',1,updDttm,9,updDttm,9,1);
    INSERT INTO `M_NationalHoliday` (`BusinessDate`,`NationalHolidayName`,`NationalHolidayFlg`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)VALUES('2017/1/1','元日',1,updDttm,9,updDttm,9,1);
    INSERT INTO `M_NationalHoliday` (`BusinessDate`,`NationalHolidayName`,`NationalHolidayFlg`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)VALUES('2017/1/2','振替休日',1,updDttm,9,updDttm,9,1);
    INSERT INTO `M_NationalHoliday` (`BusinessDate`,`NationalHolidayName`,`NationalHolidayFlg`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)VALUES('2017/1/9','成人の日',1,updDttm,9,updDttm,9,1);
    INSERT INTO `M_NationalHoliday` (`BusinessDate`,`NationalHolidayName`,`NationalHolidayFlg`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)VALUES('2017/2/11','建国記念の日',1,updDttm,9,updDttm,9,1);
    INSERT INTO `M_NationalHoliday` (`BusinessDate`,`NationalHolidayName`,`NationalHolidayFlg`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)VALUES('2017/3/20','春分の日',1,updDttm,9,updDttm,9,1);
    INSERT INTO `M_NationalHoliday` (`BusinessDate`,`NationalHolidayName`,`NationalHolidayFlg`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)VALUES('2017/4/29','昭和の日',1,updDttm,9,updDttm,9,1);
    INSERT INTO `M_NationalHoliday` (`BusinessDate`,`NationalHolidayName`,`NationalHolidayFlg`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)VALUES('2017/5/3','憲法記念日',1,updDttm,9,updDttm,9,1);
    INSERT INTO `M_NationalHoliday` (`BusinessDate`,`NationalHolidayName`,`NationalHolidayFlg`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)VALUES('2017/5/4','みどりの日',1,updDttm,9,updDttm,9,1);
    INSERT INTO `M_NationalHoliday` (`BusinessDate`,`NationalHolidayName`,`NationalHolidayFlg`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)VALUES('2017/5/5','こどもの日',1,updDttm,9,updDttm,9,1);
    INSERT INTO `M_NationalHoliday` (`BusinessDate`,`NationalHolidayName`,`NationalHolidayFlg`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)VALUES('2017/7/17','海の日',1,updDttm,9,updDttm,9,1);
    INSERT INTO `M_NationalHoliday` (`BusinessDate`,`NationalHolidayName`,`NationalHolidayFlg`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)VALUES('2017/9/18','敬老の日',1,updDttm,9,updDttm,9,1);
    INSERT INTO `M_NationalHoliday` (`BusinessDate`,`NationalHolidayName`,`NationalHolidayFlg`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)VALUES('2017/9/23','秋分の日',1,updDttm,9,updDttm,9,1);
    INSERT INTO `M_NationalHoliday` (`BusinessDate`,`NationalHolidayName`,`NationalHolidayFlg`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)VALUES('2017/10/9','体育の日',1,updDttm,9,updDttm,9,1);
    INSERT INTO `M_NationalHoliday` (`BusinessDate`,`NationalHolidayName`,`NationalHolidayFlg`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)VALUES('2017/11/3','文化の日',1,updDttm,9,updDttm,9,1);
    INSERT INTO `M_NationalHoliday` (`BusinessDate`,`NationalHolidayName`,`NationalHolidayFlg`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)VALUES('2017/11/23','勤労感謝の日',1,updDttm,9,updDttm,9,1);
    INSERT INTO `M_NationalHoliday` (`BusinessDate`,`NationalHolidayName`,`NationalHolidayFlg`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)VALUES('2017/12/23','天皇誕生日',1,updDttm,9,updDttm,9,1);
END
$$

DELIMITER ;
