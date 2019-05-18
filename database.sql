/*
SQLyog Ultimate v12.09 (64 bit)
MySQL - 5.6.17 : Database - gnugrid
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
/*Table structure for table `device` */

DROP TABLE IF EXISTS `device`;

CREATE TABLE `device` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `imei` varchar(100) NOT NULL,
  `phone_no` varchar(100) NOT NULL,
  `_when_added` datetime NOT NULL,
  `_status` varchar(100) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `imei` (`imei`,`_status`),
  UNIQUE KEY `phone_no` (`phone_no`,`_status`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

/*Data for the table `device` */

insert  into `device`(`id`,`imei`,`phone_no`,`_when_added`,`_status`) values (1,'00211676','2567801562533','2019-05-14 22:19:50','1');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
