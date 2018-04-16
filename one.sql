/*ОДНОРАЗОВЫЕ ЗАПРОСЫ*/

SET FOREIGN_KEY_CHECKS = 0;
INSERT INTO km71m_jshopping_import_export_categories (km71m_jshopping_import_export_categories.category_id, km71m_jshopping_import_export_categories.xml_id)
  (SELECT km71m_jshopping_categories.category_id, km71m_jshopping_categories.xml_id FROM km71m_jshopping_categories
WHERE km71m_jshopping_categories.xml_id != ''
      AND km71m_jshopping_categories.xml_id IS NOT NULL) ;
SET FOREIGN_KEY_CHECKS = 1;

SET FOREIGN_KEY_CHECKS = 0;
INSERT INTO km71m_jshopping_import_export_products (km71m_jshopping_import_export_products.product_id, km71m_jshopping_import_export_products.xml_id)
  (SELECT km71m_jshopping_products.product_id, km71m_jshopping_products.xml_id FROM km71m_jshopping_products
  WHERE km71m_jshopping_products.xml_id != ''
        AND km71m_jshopping_products.xml_id IS NOT NULL) ;
SET FOREIGN_KEY_CHECKS = 1;

/*ВЫБИРАЕТ ТЕХ КОГО НЕТ В КАТАЛОГЕ*/
SELECT iec.id FROM km71m_tmp_ie_categories as iec
LEFT JOIN km71m_jshopping_import_export_categories AS jiec ON jiec.xml_id = iec.id
WHERE jiec.xml_id IS NULL;

SELECT `iec`.`id` AS `id`,`iec`.`name` AS `name`,`iec`.`order` AS `ordering`, `jiecp`.`category_id`
FROM `km71m_tmp_ie_categories` AS `iec`
  LEFT JOIN `km71m_jshopping_import_export_categories` AS `jiec` ON (`jiec`.`xml_id` = `iec`.`id`)
  LEFT JOIN `km71m_jshopping_import_export_categories` AS `jiecp` ON (`jiecp`.`xml_id` = `iec`.`parent`)
WHERE `jiec`.`xml_id`IS NULL AND `iec`.`id` <> ''

SELECT c.category_parent_id, parent.category_id, parent.xml_id, category2.parent FROM km71m_jshopping_categories as c
LEFT JOIN km71m_jshopping_import_export_categories category on c.category_id = category.category_id
left join km71m_tmp_ie_categories category2 on category.xml_id = category2.id
left join km71m_jshopping_import_export_categories parent on category2.parent = parent.xml_id
WHERE category2.parent <> ''

SELECT `name`, `parent` FROM km71m_tmp_ie_categories WHERE id='894f0cbe-9781-4fef-b8f3-dddd00007745'