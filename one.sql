
SET FOREIGN_KEY_CHECKS = 0;
INSERT INTO km71m_jshopping_import_export_categories (km71m_jshopping_import_export_categories.category_id, km71m_jshopping_import_export_categories.xml_id)
  (SELECT km71m_jshopping_categories.category_id, km71m_jshopping_categories.xml_id FROM km71m_jshopping_categories
  WHERE km71m_jshopping_categories.xml_id != ''
        AND km71m_jshopping_categories.xml_id IS NOT NULL) ;

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

SELECT jc.`name_ru-RU`, jc.category_id, jc.category_parent_id, cp.`name_ru-RU`, cp.category_id FROM km71m_jshopping_categories as jc
  left join km71m_jshopping_import_export_categories jiec on jc.category_id = jiec.category_id
  left join km71m_tmp_ie_categories tiec on jiec.xml_id = tiec.id
  left join km71m_tmp_ie_categories tiep on tiec.parent = tiep.id
  left join km71m_jshopping_import_export_categories jiep on tiep.id = jiep.xml_id
  left join km71m_jshopping_categories cp on jiep.category_id = cp.category_id
where jiec.last_import between '2018-04-17 21:00:00' and NOW()

UPDATE km71m_jshopping_categories jc
  left join km71m_jshopping_import_export_categories jiec on jc.category_id = jiec.category_id
  left join km71m_tmp_ie_categories tiec on jiec.xml_id = tiec.id
  left join km71m_tmp_ie_categories tiep on tiec.parent = tiep.id
  left join km71m_jshopping_import_export_categories jiep on tiep.id = jiep.xml_id
  left join km71m_jshopping_categories cp on jiep.category_id = cp.category_id
set jc.category_parent_id = cp.category_id
where jiec.last_import between '2018-04-17 21:00:00' and NOW()
      and tiec.parent <> ''
      and jc.category_parent_id = 0;

SELECT tiep.id, tiep.name FROM km71m_tmp_ie_products as tiep
  LEFT JOIN km71m_jshopping_import_export_products jiep on tiep.id = jiep.xml_id
WHERE jiep.xml_id IS NULL;

insert into km71m_jshopping_products_to_categories (product_id, category_id)
  SELECT jiep.product_id, jiec.category_id FROM km71m_tmp_ie_product_categories as tiepc
    left join km71m_jshopping_import_export_categories as jiec on tiepc.category=jiec.xml_id
    left join km71m_jshopping_import_export_products as jiep on tiepc.product=jiep.xml_id
    left join km71m_jshopping_products_to_categories as jpt on jiep.product_id=jpt.product_id
  where jpt.product_id IS NULL;


INSERT INTO `km71m_jshopping_products_to_categories`
  SELECT `jiep`.`product_id`,`jiec`.`category_id`
  FROM `km71m_tmp_ie_product_categories` AS `tiepc`
    LEFT JOIN `km71m_jshopping_import_export_categories` AS `jiec`on`tiepc`.`category`=`jiec`.`xml_id`
    LEFT JOIN `km71m_jshopping_import_export_products` AS `jiep`on`tiepc`.`product`=`jiep`.`xml_id`
    LEFT JOIN `km71m_jshopping_products_to_categories` AS `jpt`on`jiep`.`product_id`=`jpt`.`product_id`
  WHERE `jpt`.`product_id` IS NULL;

UPDATE km71m_jshopping_products jp
  left join km71m_jshopping_import_export_products jiep on jp.product_id = jiep.product_id
  left join km71m_tmp_ie_products tiep on jiep.xml_id = tiep.id
  INNER join (
               select r.product, SUM(r.rest) as restssum from km71m_tmp_ie_rests r
               group by r.product
             ) tier on tiep.id = tier.product
  left join km71m_tmp_ie_prices price on tiep.id = price.product
SET jp.product_quantity =tier.restssum,
  jp.min_price=price.unit,
  jp.product_buy_price=price.unit,
  jp.product_price=price.unit,
  jp.date_modify=NOW();