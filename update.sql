INSERT INTO `#__jshopping_import_export` (`name`, `alias`, `description`, `params`, `endstart`, `steptime`) VALUES
('Импорт из 1С', 'Import1C', 'Импортирует каталог из 1С в формате CommerceML.', '', 0, 0);

CREATE TABLE `#__jshopping_import1c_categoriec`
(
  id INT PRIMARY KEY AUTO_INCREMENT,
  category_id INT NOT NULL,
  xml_id CHAR(32) NOT NULL,
  xml_parent_id CHAR(32)
);
CREATE UNIQUE INDEX `#__jshopping_import1c_categoriec_category_id_uindex` ON `#__jshopping_import1c_categoriec` (category_id);
CREATE UNIQUE INDEX `#__jshopping_import1c_categoriec_xml_id_uindex` ON `#__jshopping_import1c_categoriec` (xml_id);