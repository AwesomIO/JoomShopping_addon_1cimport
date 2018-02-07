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
    static function getPrepareDataSave(&$input, &$catList)
    {
        $jshopConfig = JSFactory::getConfig();
        $_lang = JSFactory::getModel("languages");
        $languages = $_lang->getAllLanguages(1);
        $_alias = JSFactory::getModel("alias");
        $post = array();

        $key=recursive_array_search($input->getId(),'xml_id', $catList);
        if($key!==false)
            $post['category_id'] = $catList[$key]->category_id;
        unset($key);

        if($input->getOrder())
            $post['ordering'] = $input->getOrder();

        foreach($languages as $lang)
        {
            $post['name_'.$lang->language] = trim($input->getName());

            if ($jshopConfig->create_alias_product_category_auto)
            {
                $post['alias_'.$lang->language] = $post['name_'.$lang->language];
            }
            $post['alias_'.$lang->language] = JApplication::stringURLSafe($post['alias_'.$lang->language]);
            if ($post['alias_'.$lang->language]!="" && !$_alias->checkExistAlias1Group($post['alias_'.$lang->language], $lang->language, $input->getId(), 0) && !$post['category_id'])
            {
                $post['alias_'.$lang->language] = "";
                JError::raiseWarning("",_JSHOP_ERROR_ALIAS_ALREADY_EXIST);
            }
            $post['description_'.$lang->language] = "";
            $post['short_description_'.$lang->language] = "";
        }
        return $post;
    }
}