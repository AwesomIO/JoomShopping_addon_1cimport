<?php
/**
 * @package     ${NAMESPACE}
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

defined('_JEXEC') or die();

class CategoryHelper
{
    public static function helper(&$collection, &$instIE)
    {
        if(count($collection)){
            foreach ($collection as &$category){
                $post = self::getPrepareDataSave($category, $instIE);
                $instIE->_categories->save($post, null);
            }
            self::updateParentsCategories();
        }
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

        $post['category_publish'] = 1;
        $post['xml_id'] = $input->getId();
        $post['xml_parent_id'] = $input->getParent();

        return $post;
    }

    static function updateParentsCategories(){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->update($db->quoteName('#__jshopping_categories', 'jsc'))
            ->join('INNER', $db->quoteName('#__jshopping_categories', 'jscp') . ' ON (' . $db->quoteName('jsc.xml_parent_id') . ' = ' . $db->quoteName('jscp.xml_id') . ')')
            ->set($db->quoteName('jsc.category_parent_id') . '=' . $db->quoteName('jscp.category_id'))
        ->where($db->quoteName('jsc.category_parent_id') .'= 0' );
        $db->setQuery($query);
        $db->execute();
    }
}