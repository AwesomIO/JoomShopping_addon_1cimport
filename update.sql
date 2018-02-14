INSERT INTO `#__jshopping_import_export` (`name`, `alias`, `description`, `params`, `endstart`, `steptime`) VALUES
('Импорт из 1С', 'Import1C', 'Импортирует каталог из 1С в формате CommerceML.', '', 0, 0);

ALTER TABLE `#__jshopping_categories`
  ADD COLUMN `xml_id` VARCHAR(64) NOT NULL AFTER `category_parent_id`,
  ADD COLUMN `xml_parent_id` VARCHAR(64) NULL DEFAULT NULL AFTER `xml_id`,
  DROP INDEX `category_parent_id`,
  ADD INDEX `category_parent_id` (`category_parent_id`, `xml_parent_id`);

ALTER TABLE `#__jshopping_products`
  ADD COLUMN `xml_id` VARCHAR(64) NOT NULL AFTER `parent_id`,
  ADD COLUMN `xml_parent_id` VARCHAR(64) NULL DEFAULT NULL AFTER `xml_id`;

ALTER TABLE `#__jshopping_products_to_categories`
  ADD COLUMN `xml_id` VARCHAR(64) NOT NULL AFTER `category_id`;