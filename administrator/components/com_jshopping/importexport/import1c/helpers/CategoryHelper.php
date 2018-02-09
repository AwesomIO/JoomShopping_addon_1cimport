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
                var_dump($post);

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
            //if ($post['alias_'.$lang->language]!="" && !$instIE->_alias->checkExistAlias1Group($post['alias_'.$lang->language], $lang->language, $input->getId(), 0) && !$post['category_id'])
            //{
            //    $post['alias_'.$lang->language] = "";
            //    JError::raiseWarning("",_JSHOP_ERROR_ALIAS_ALREADY_EXIST);
            //}
            $post['description_'.$lang->language] = "";
            $post['short_description_'.$lang->language] = "";
        }
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
   /* static function getPrepareDataSave(&$input, &$instIE)
    {
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

    function setParents()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(false);
        $query->clear();

        $query->update($db->quoteName('#__jshopping_categories', 'jsh_cats'))
            ->join('INNER', $db->quoteName('#__jshopping_import1c_categories', 's1') . ' ON (' . $db->quoteName('s1.category_id') . ' = ' . $db->quoteName('jsh_cats.category_id') . ')')
            ->join('INNER' ,$db->quoteName('#__jshopping_import1c_categories', 's2') . ' ON (' . $db->quoteName('s2.xml_id') . ' = ' . $db->quoteName('s1.xml_parent_id') . ')')
            ->set($db->quoteName('jsh_cats.category_parent_id') . '=' . $db->quoteName('s2.category_id'));
        $db->setQuery($query);
        $db->query();
    }

$db = JFactory::getDbo();

if(count($result['categories'])){

    //usort($result['categories'], "cmp");

$query = $db->getQuery(true);

$query->select($db->quoteName(array('xml_id', 'category_id')))
->from($db->quoteName('#__jshopping_import1c_categories'));

$db->setQuery($query);
$existCats = $db->loadObjectList();

$columns = array(
$db->quoteName('category_id'),
$db->quoteName('xml_id'),
$db->quoteName('xml_parent_id')
);
$values = array();

foreach($result['categories'] as &$category)
{
$post = CategoryHelper::getPrepareDataSave($category, $existCats);
$cat = $_categories->save($post, null);

if($post['category_id'])
continue;

$valuesArr = $db->quote(
array(
$cat->category_id,
$category->getId(),
$category->getParent()
)
);
$values[] = implode (',', $valuesArr);
}
unset($category);

if(count($values)){
    $query->insert($db->quoteName('#__jshopping_import1c_categories'))
        ->columns($columns)
        ->values($values);
    $db->setQuery($query);
    $db->query();
}

$this->setParents();
}*/
}