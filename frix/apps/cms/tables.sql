SET FOREIGN_KEY_CHECKS=0;

CREATE TABLE `cms_file` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `parent` bigint(20) unsigned default NULL,
  `name` char(80) NOT NULL default '',
  `file` char(100) NOT NULL,
  `visible` tinyint(1) unsigned NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `NewIndex1` (`parent`),
  CONSTRAINT `FK_cms_file` FOREIGN KEY (`parent`) REFERENCES `cms_page` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `cms_image` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `parent` bigint(20) unsigned default NULL,
  `name` char(80) NOT NULL default '',
  `file` char(100) NOT NULL,
  `visible` tinyint(1) unsigned NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `NewIndex1` (`parent`),
  CONSTRAINT `FK_cms_image` FOREIGN KEY (`parent`) REFERENCES `cms_page` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `cms_menu` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `title` char(40) NOT NULL,
  `slug` char(40) NOT NULL default '',
  `visible` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `cms_menu_item` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `menu` bigint(20) unsigned NOT NULL,
  `title` char(40) NOT NULL,
  `link` char(150) NOT NULL,
  `visible` tinyint(1) unsigned NOT NULL,
  `pos` bigint(20) NOT NULL,
  PRIMARY KEY  (`id`,`menu`),
  UNIQUE KEY `id` (`id`),
  KEY `FK_cms_menu_item` (`menu`),
  KEY `pos` (`pos`),
  CONSTRAINT `FK_cms_menu_item` FOREIGN KEY (`menu`) REFERENCES `cms_menu` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `cms_page` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `parent` bigint(20) unsigned default NULL,
  `title` char(40) NOT NULL,
  `slug` char(40) NOT NULL default '',
  `content` text NOT NULL,
  `visible` tinyint(1) unsigned NOT NULL,
  `pos` bigint(20) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `FK_cms_page` (`parent`),
  KEY `pos` (`pos`),
  CONSTRAINT `FK_cms_page` FOREIGN KEY (`parent`) REFERENCES `cms_page` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `cms_video` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `parent` bigint(20) unsigned default NULL,
  `name` char(80) NOT NULL default '',
  `file` char(100) NOT NULL,
  `image` char(100) NOT NULL,
  `visible` tinyint(1) unsigned NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `i_parent` (`parent`),
  CONSTRAINT `FK_cms_video` FOREIGN KEY (`parent`) REFERENCES `cms_page` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

SET FOREIGN_KEY_CHECKS=1;
