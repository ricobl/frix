SET FOREIGN_KEY_CHECKS=0;

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

SET FOREIGN_KEY_CHECKS=1;
