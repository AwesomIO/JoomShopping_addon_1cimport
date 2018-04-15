INSERT INTO `#__jshopping_import_export` (`name`, `alias`, `description`, `params`, `endstart`, `steptime`) VALUES
('Импорт из 1С', 'Import1C', 'Импортирует каталог из 1С в формате CommerceML.', '', 0, 0);
  
DROP PROCEDURE IF EXISTS `CreateIndex`;
CREATE PROCEDURE `CreateIndex`
  (
    given_table    VARCHAR(64),
    given_index    VARCHAR(64),
    given_columns  VARCHAR(64)
  )
  BEGIN

    DECLARE IndexIsThere INTEGER;

    SELECT COUNT(1) INTO IndexIsThere
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE table_name   = given_table
          AND   index_name   = given_index;

    IF IndexIsThere = 0 THEN
      SET @sqlstmt = CONCAT('CREATE INDEX ',given_index,' ON ',
                            given_table,' (',given_columns,')');
      PREPARE st FROM @sqlstmt;
      EXECUTE st;
      DEALLOCATE PREPARE st;
    ELSE
      SELECT CONCAT('Index ',given_index,' already exists on Table ',
                    given_table) CreateindexErrorMessage;
    END IF;

  END;


create table if not exists `#__tmp_ie_categories`
(
  `id` varchar(128) default '' not null primary key,
  `name` text not null,
  `parent` varchar(128) default '' not null,
  `order` int default '0' not null,
  constraint `#__tmp_ie_categories___category`
  foreign key (`parent`) references `#__tmp_ie_categories` (`id`)
)
  engine = InnoDB;

call createindex('#__tmp_ie_categories','#__tmp_ie_categories___category','parent');

create table if not exists  `#__tmp_ie_products`
(
  `id` varchar(128) default '' not null
    primary key,
  `name` text not null,
  `description` text not null,
  `code` varchar(128) default '' not null,
  `barcode` varchar(128) default '' not null,
  `manufacturer` varchar(128) default '' not null
)
  engine = InnoDB;

CREATE TABLE if not exists `#__tmp_ie_product_categories`
(
  `product` nvarchar(128) DEFAULT '' NOT NULL,
  `category` nvarchar(128) DEFAULT '' NOT NULL,
  CONSTRAINT `#__tmp_ie_product_categories___category`
  FOREIGN KEY (`category`) REFERENCES `#__tmp_ie_categories` (`id`),
  CONSTRAINT `#__tmp_ie_product_categories___product`
  FOREIGN KEY (`product`) REFERENCES `#__tmp_ie_products` (`id`)
)
  engine = InnoDB;

call createindex('#__tmp_ie_product_categories','#__tmp_ie_product_categories___category','category');
call createindex('#__tmp_ie_product_categories','#__tmp_ie_product_categories___product','product');

CREATE TABLE IF NOT EXISTS `#__tmp_ie_product_units`
(
  product nvarchar(128) DEFAULT '' NOT NULL,
  value nvarchar(128) DEFAULT '' NOT NULL,
  code nvarchar(128) DEFAULT '' NOT NULL,
  nameFull text NOT NULL,
  nameShort text NOT NULL,
  nameInterShort text NOT NULL,
  CONSTRAINT `#__tmp_ie_product_units___product` FOREIGN KEY (product) REFERENCES `#__tmp_ie_products` (id)
)
  engine = InnoDB;

call createindex('#__tmp_ie_product_units','#__tmp_ie_product_units___product','product');

CREATE TABLE IF NOT EXISTS `#__tmp_ie_PriceType`
(
  id nvarchar(128) DEFAULT '' PRIMARY KEY NOT NULL,
  type text NOT NULL,
  currency text NOT NULL
);

CREATE TABLE IF NOT EXISTS `#__tmp_ie_prices`
(
  product nvarchar(128) DEFAULT '' NOT NULL,
  type nvarchar(128) DEFAULT '' NOT NULL,
  unit int DEFAULT 0 NOT NULL,
  currency text NOT NULL,
  baseUnit text NOT NULL,
  koeff int DEFAULT 1 NOT NULL,
  CONSTRAINT `#__tmp_ie_prices___product` FOREIGN KEY (product) REFERENCES `#__tmp_ie_products` (id),
  CONSTRAINT `#__tmp_ie_prices___pricetype` FOREIGN KEY (type) REFERENCES `#__tmp_ie_PriceType` (id)
);

call createindex('#__tmp_ie_prices','#__tmp_ie_prices___product','product');
call createindex('#__tmp_ie_prices','#__tmp_ie_prices___pricetype','type');

CREATE TABLE IF NOT EXISTS `#__tmp_ie_rests`
(
  product nvarchar(128) DEFAULT '' NOT NULL,
  rest_id nvarchar(128) DEFAULT '' NOT NULL,
  rest int DEFAULT 0 NOT NULL,
  CONSTRAINT `#__tmp_ie_rests___product` FOREIGN KEY (product) REFERENCES `#__tmp_ie_products` (id)
);

call createindex('#__tmp_ie_rests','#__tmp_ie_rests___product','product');

CREATE TABLE IF NOT EXISTS `#__jshopping_import_export_products`
(
  product_id int NOT NULL,
  xml_id nvarchar(128) NOT NULL,
  last_import datetime DEFAULT NOW() NOT NULL,
  CONSTRAINT `#__jshopping_import_export_products___product_id` FOREIGN KEY (product_id) REFERENCES `#__jshopping_products` (product_id),
  CONSTRAINT `#__jshopping_import_export_products___id` FOREIGN KEY (xml_id) REFERENCES `#__tmp_ie_products` (id)
);

call createindex('#__jshopping_import_export_products','#__jshopping_import_export_products___product_id','product_id');
call createindex('#__jshopping_import_export_products','#__jshopping_import_export_products___id','xml_id');

CREATE TABLE IF NOT EXISTS #__jshopping_import_export_categories
(
  category_id int NOT NULL,
  xml_id nvarchar(128) NOT NULL,
  last_import datetime DEFAULT NOW() NOT NULL,
  CONSTRAINT `#__jshopping_import_export_categories___category_id` FOREIGN KEY (category_id) REFERENCES `#__jshopping_categories (category_id)`,
  CONSTRAINT `#__jshopping_import_export_categories___id` FOREIGN KEY (xml_id) REFERENCES `#__tmp_ie_categories (id)`
);

call createindex('#__jshopping_import_export_categories','#__jshopping_import_export_categories___category_id','category_id');
call createindex('#__jshopping_import_export_categories','#__jshopping_import_export_categories___id','xml_id');

DROP PROCEDURE IF EXISTS `CreateIndex`;
