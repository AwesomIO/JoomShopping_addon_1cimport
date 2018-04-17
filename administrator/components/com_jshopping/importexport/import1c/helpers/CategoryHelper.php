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
    public static function helper($category, &$instIE)
    {
        $post = self::getPrepareDataSave($category, $instIE);
        $catId = self::save($post);

        if($catId){
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->insert($db->qn('#__jshopping_import_export_categories'))
                ->columns($db->qn(array('category_id', 'xml_id')))
                ->values($db->q($catId) .','. $db->q($category->id));
            $db->setQuery($query);
            $db->execute();
        }
    }
    static function getPrepareDataSave(&$input, &$instIE){
        $post = array();
        foreach($instIE->parameters->get('languages') as $lang)
        {
            $post['name_'.$lang->language] = trim($input->name);

            if ($instIE->jsConfig->create_alias_product_category_auto)
            {
                $post['alias_'.$lang->language] = $post['name_'.$lang->language];
            }
            $post['alias_'.$lang->language] = JApplicationHelper::stringURLSafe($post['alias_'.$lang->language]);
            $post['description_'.$lang->language] = "";
            $post['short_description_'.$lang->language] = "";
            $post['meta_title_'.$lang->language] =  $post['name_'.$lang->language];
        }
        $post['ordering']=$input->ordering;
        $post['category_publish'] = 1;
        $post['category_add_date'] = date("Y-m-d H:i:s");
        return $post;
    }

    static function save(array $post){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $col=array();
        $val=array();
        foreach ($post as $k=>$v){
            $col[]=$k;
            $val[]=$db->q($v);
        }
        unset($k, $v);

        $query->insert($db->qn('#__jshopping_categories'))
            ->columns($db->qn($col))
            ->values(implode(',', $val));
        $db->setQuery($query);
        $db->execute();
        $catId = $db->insertid();
        $query->clear();
        unset($col, $val);
        return $catId;
    }
}