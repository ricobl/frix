CREATE TABLE `settings_property` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `name` char(30) NOT NULL default '',
  `value` char(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
