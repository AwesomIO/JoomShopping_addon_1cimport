/*СОБИРАЕМ КАТЕГОРИИ*/
SET FOREIGN_KEY_CHECKS = 0;
INSERT INTO km71m_jshopping_import_export_categories (km71m_jshopping_import_export_categories.category_id, km71m_jshopping_import_export_categories.xml_id)
  (SELECT km71m_jshopping_categories.category_id, km71m_jshopping_categories.xml_id FROM km71m_jshopping_categories
WHERE km71m_jshopping_categories.xml_id != ''
      AND km71m_jshopping_categories.xml_id IS NOT NULL) ;
SET FOREIGN_KEY_CHECKS = 1;
/*СОБИРАЕМ ПРОДУКТЫ*/
SET FOREIGN_KEY_CHECKS = 0;
INSERT INTO km71m_jshopping_import_export_products (km71m_jshopping_import_export_products.product_id, km71m_jshopping_import_export_products.xml_id)
  (SELECT km71m_jshopping_products.product_id, km71m_jshopping_products.xml_id FROM km71m_jshopping_products
  WHERE km71m_jshopping_products.xml_id != ''
        AND km71m_jshopping_products.xml_id IS NOT NULL) ;
SET FOREIGN_KEY_CHECKS = 1;
/*СОБИРАЕМ КАТЕГОРИИ, КОТОРЫХ НЕТ В КАТАЛОГЕ*/
SELECT iec.id FROM km71m_tmp_ie_categories as iec
LEFT JOIN km71m_jshopping_import_export_categories AS jiec ON jiec.xml_id = iec.id
WheRE jiec.xml_id IS NULL;