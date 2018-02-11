<?php
/**
 * @package     ${NAMESPACE}
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

defined('_JEXEC') or die();


class ProductHelper
{
    public static function helper(&$product, &$instIE)
    {
        $post = self::getPrepareDataSave($product, $instIE);
        self::save($post, $instIE);
        unset($post);
        return true;
    }

    static function getPrepareDataSave(&$input, &$instIE){
        $post = array();

        foreach($instIE->parameters->get('languages') as $lang)
        {
            $post['name_'.$lang->language] = trim($input->getName());

            if ($instIE->jsConfig->create_alias_product_category_auto)
            {
                $post['alias_'.$lang->language] = $post['name_'.$lang->language];
            }
            $post['alias_'.$lang->language] = JApplicationHelper::stringURLSafe($post['alias_'.$lang->language]);
            $post['description_'.$lang->language] = "";
            $post['short_description_'.$lang->language] = "";
        }

        unset($lang);

        $post['product_ean'] = $input->getCode(); //код товара

        $post['product_date_added'] = getCurDate();
        $post['date_modify'] = getCurDate();

        $post['product_tax_id'] = 1;
        $post['currency_id'] = 1;

        $post['product_template'] = 'default';
        $post['product_url'] = '';

        $post['product_old_price'] = 0;
        $post['product_buy_price'] = 0;
        $post['product_price'] = 0; //базовая цена
        $post['min_price'] = 0; //минимальная цена
        $post['different_prices'] = 0; //дополнительные цены

        $post['product_weight'] = 0;
        $post['image'] = '';
        $post['product_manufacturer_id'] = 0;
        $post['product_is_add_price'] = 1;
        $post['add_price_unit_id'] = 3;
        $post['average_rating'] = 0;
        $post['reviews_count'] = 0;
        $post['delivery_times_id'] = 0;
        $post['hits'] = 0;
        $post['weight_volume_units'] = 0;
        $post['basic_price_unit_id'] = 3;
        $post['label_id'] = 0;
        $post['vendor_id'] = 0;
        $post['access'] = 1;

        $post['product_publish'] = 1;

        $post['xml_id'] = $input->getId();
        $post['xml_parent_id'] = $input->getCategories()->fetch();

        return $post;
    }

    static function save($post, &$instIE){
        $columns =array();
        $values =array();
        $db = JFactory::getDbo();

        foreach ($post as $key=>&$val){
            $columns [] = $key;
            if($key == 'xml_parent_id'){
                $values [] = $db->quote($val[0]);
            }
            else{
                $values [] = $db->quote($val);
            }
        }

        $query = $db->getQuery(true);
        $query->insert($db->quoteName('#__jshopping_products'))
            ->columns($db->quoteName($columns))
            ->values(implode(',', $values));

        $db->setQuery($query);
        if($db->execute())
            $id = $db->insertid();
        $query->clear();

        if(isset($id)){

        }
        unset($db, $query, $key, $val);
    }

}