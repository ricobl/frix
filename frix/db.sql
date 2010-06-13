/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

create database if not exists `frix`;

USE `frix`;

/*Table structure for table `auth_user` */

CREATE TABLE `auth_user` (
  `id` bigint(11) unsigned NOT NULL auto_increment,
  `username` char(30) NOT NULL,
  `password` char(128) NOT NULL,
  `email` char(75) NOT NULL,
  `is_staff` tinyint(1) unsigned NOT NULL,
  `is_active` tinyint(1) unsigned NOT NULL,
  `is_super` tinyint(1) unsigned NOT NULL,
  `first_name` char(30) NOT NULL,
  `last_name` char(30) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Table structure for table `settings_property` */

CREATE TABLE `settings_property` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `name` char(30) NOT NULL default '',
  `value` char(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
