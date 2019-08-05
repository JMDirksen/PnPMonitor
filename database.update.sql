ALTER TABLE `monitor`
	ADD COLUMN IF NOT EXISTS `name` CHAR(15) NOT NULL AFTER `id`;

CREATE TABLE IF NOT EXISTS `version` (
	`number` INT UNSIGNED NOT NULL
)
COLLATE='utf8_general_ci';

INSERT INTO `version` (`number`)
	SELECT 1 WHERE NOT EXISTS (SELECT * FROM `version`);
