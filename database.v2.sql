ALTER TABLE `monitor`
	CHANGE COLUMN `failed` `failCount` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `port`;
ALTER TABLE `monitor`
	ADD COLUMN `successCount` INT UNSIGNED NOT NULL DEFAULT 1 AFTER `port`;
UPDATE settings SET dbversion = 2;
