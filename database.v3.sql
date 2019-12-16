CREATE TABLE `users` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`email` TINYTEXT NOT NULL,
	`token` CHAR(32) NOT NULL,
	PRIMARY KEY (`id`)
);
ALTER TABLE `users` ADD UNIQUE (`email`);
ALTER TABLE `users` ADD UNIQUE (`token`);

UPDATE settings SET dbversion = 3;
