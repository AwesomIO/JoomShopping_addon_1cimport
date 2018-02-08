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
    static function getPrepareDataSave(&$input, &$db, &$query){
        $post = array();
        $jshConfig = JSFactory::getConfig();
        $_alias = JSFactory::getModel("alias");
        $_lang = JSFactory::getModel("languages");
        $languages = $_lang->getAllLanguages(1);



        foreach($languages as $lang){
            $post['name_'.$lang->language] = trim($input->getName());
            if ($jshConfig->create_alias_product_category_auto){
                $post['alias_'.$lang->language] = $post['name_'.$lang->language];
            }
            $post['alias_'.$lang->language] = JApplication::stringURLSafe($post['alias_'.$lang->language]);
            if ($post['alias_'.$lang->language]!="" && !$_alias->checkExistAlias2Group($post['alias_'.$lang->language], $lang->language, $post['product_id'])){
                $post['alias_'.$lang->language] = "";
                JError::raiseWarning("", _JSHOP_ERROR_ALIAS_ALREADY_EXIST);
            }
            $post['description_'.$lang->language] = $input->getDescription();
        }


        $query->clear();

        /*echo '<pre>';
        var_dump($query);
        die();*/
        //$query->clear();

        $query->select($db->quoteName('category_id'))
            ->from($db->quoteName('#__jshopping_import1c_categories'))
            ->where($db->quoteName('xml_id') . ' IN (' . $db->quote(implode(',', $input->getCategories()->fetch())) . ')');
        $db->setQuery($query);
        //echo $query->dump();
        $categoryId = $db->loadColumn();

        if(count($categoryId)){
            $post['category_id'] = $categoryId;
        }
        $post['product_ean'] = $input->getCode();

        return $post;
    }

    static function add(&$input, &$db, &$query){
        $post = self::getPrepareDataSave($input, $db, $query);
        $_products = JSFactory::getModel('products', 'JshoppingModel');
        $_products->save($post);

        //print_r($post);

        return true;
    }
}