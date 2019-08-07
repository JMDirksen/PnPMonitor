CREATE TABLE `monitor` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(15) NOT NULL,
  `type` enum('page','port') NOT NULL,
  `url` tinytext DEFAULT NULL,
  `text` tinytext NOT NULL DEFAULT '',
  `host` tinytext DEFAULT NULL,
  `port` smallint(5) unsigned DEFAULT NULL,
  `failed` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
);

CREATE TABLE `settings` (
	`dbversion` INT(10) UNSIGNED NOT NULL
);
INSERT INTO `settings` (`dbversion`) VALUES	(1);
