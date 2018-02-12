<?php
/**
 * @package     ${NAMESPACE}
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

class OfferHelper
{
    public static function helper(&$offer, &$instIE)
    {
        if($post = self::getPrepareDataSave($offer, $instIE))
            self::save($post, $instIE);

        //var_dump($post);

        unset($post);
        return true;
    }

    static function getPrepareDataSave(&$input, &$instIE){
        $post = array();

        $post['xml_id'] = $input->getId();

        $price = &$input->getPrices()->fetch()[0];
        if(isset($price) && count($price)){

            //для таблицы доп. цен
            $post['discount'] = -20;
            $post['product_quantity_start'] = 1;
            $post['product_quantity_finish'] = 0;
            $post['xml_price_id'] = $price->getTypeId();

            $post['currency_id'] = 1;

            $post['min_price'] = floatval($price->getUnit());
            $post['weight_volume_units'] = 1;
            $post['product_buy_price'] = $post['product_price'] = (float) $post['min_price'] * 0.8;
            $post['different_prices'] = 1; //дополнительные цены

            $post['product_is_add_price'] = 1;
            $post['add_price_unit_id'] = 3;
            $post['basic_price_unit_id'] = 3; //Единица

            $post['product_publish'] = 1;
            $post['date_modify'] = getCurDate();
        }

        $rests = $input->getRests()->fetch();
        $stock = 0;
        if(isset($rests) && count($rests)){
            foreach($rests as &$rest){
               $stock = $stock + $rest->getRest();
            }
            $post['product_quantity'] = $stock;
        }

        return $post;
    }

    static function save($post, &$instIE){
        $db = JFactory::getDbo();

        $columns = array(
            'currency_id',
            'min_price',
            'product_buy_price',
            'product_price',
            'weight_volume_units',
            'different_prices',
            'product_is_add_price',
            'add_price_unit_id',
            'basic_price_unit_id',
            'product_publish',
            'date_modify',
            'product_quantity',
        );

        $fields = array();
        foreach($columns as $key){
            if(!strlen($post[$key])) continue;
            $fields[] = $db->quoteName($key) . ' = ' . $db->quote($post[$key]);
        }

        $condition = $db->quoteName('xml_id') . ' = '. $post['xml_id'];

        $query = $db->getQuery(true);
        $query->update($db->quoteName('#__jshopping_products'))
            ->set($fields)
            ->where($condition);

        $db->setQuery($query);
        $db->execute();
        $query->clear();
        unset($columns, $fields, $condition);

        if($post['min_price']){
            $columns = array(
                'discount',
                'product_quantity_start',
                'product_quantity_finish',
                'xml_price_id',
                'xml_id'
            );
            $values = array();
            foreach($columns as $key){
                $values[] = isset($post[$key]) ? $db->quote($post[$key]) : '';
            }

            $query->insert('#__jshopping_products_prices')
                ->columns($db->quoteName($columns))
                ->values(implode(',', $values));

            $db->setQuery($query);
            $db->execute();
            $query->clear();
        }

        unset($db, $query, $key, $val);
    }
}