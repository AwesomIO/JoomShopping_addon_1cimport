INSERT INTO `#__jshopping_import_export` (`name`, `alias`, `description`, `params`, `endstart`, `steptime`) VALUES
('Импорт из 1С', 'Import1C', 'Импортирует каталог из 1С в формате CommerceML.', '', 0, 0);

CREATE TABLE `#__jshopping_import1c_categories`
(
  category_id INT NOT NULL,
  xml_id CHAR(32) NOT NULL,
  xml_parent_id CHAR(32)
);
ALTER TABLE `#__jshopping_import1c_categories`
  ADD PRIMARY KEY (`xml_id`);