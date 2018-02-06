<?php
/**
 * @package     Jshopping
 * @subpackage  Import1C
 *
 * @copyright   Copyright (c) 2018 AwesomIO. All rights reserved.
 * @license     GNU General Public License v3.0; see LICENSE
 */

defined('_JEXEC') or die();

jimport('joomla.filesystem.folder');


function recursive_array_search($needle, $parametr, $haystack) {
    foreach($haystack as $key=>$value) {
        $current_key=$key;
        if($needle==$value->{$parametr}) {
            return $current_key;
        }
    }
    return false;
}

class IeImport1C extends IeController
{
	function view()
	{
		$app = JFactory::getApplication();
		$jshopConfig = JSFactory::getConfig();
		$ie_id = $this->ie_id;
		$_importexport = JSFactory::getTable('ImportExport', 'jshop');
		$_importexport->load($ie_id);
		$name = $_importexport->get('name');

		JToolBarHelper::title(_JSHOP_IMPORT. ' "'.$name.'"', 'generic.png' );
		JToolBarHelper::custom("backtolistie", "arrow-left", 'arrow-left', _JSHOP_BACK_TO.' "'._JSHOP_PANEL_IMPORT_EXPORT.'"', false );
		JToolBarHelper::spacer();
		JToolBarHelper::save("save", _JSHOP_IMPORT);

		include(dirname(__FILE__)."/form.php");
	}

	function save()
	{
		$app = JFactory::getApplication();
		$jshopConfig = JSFactory::getConfig();
		require_once(JPATH_COMPONENT_SITE . '/lib/uploadfile.class.php');

		$ie_id = $app->input->getInt("ie_id");
		if (!$ie_id) $ie_id = $this->get('ie_id');

		$lang = JSFactory::getLang();
		$db = JFactory::getDBO();

		$_importexport = JSFactory::getTable('ImportExport', 'jshop');
		$_importexport->load($ie_id);
		$alias = $_importexport->get('alias');
		$_importexport->set('endstart', time());
		$_importexport->store();

		$_categories = JSFactory::getModel('categories', 'JshoppingModel');

		$dir = $jshopConfig->importexport_path.$alias."/";

		$upload = new UploadFile($_FILES['file']);
		$upload->setAllowFile(array('xml'));
		$upload->setDir($dir);

		if ($upload->upload())
		{

			$filename = $dir . "/" . $upload->getName();
			@chmod($filename, 0777);

			$categories = $this->parse($filename);

			if(count($categories)){
				$db = JFactory::getDbo();
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

				foreach($categories as &$category)
				{
					$post = $this->getPrepareDataSave($category, $existCats);
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
			}

			@unlink($filename);
		}
		else {
			JError::raiseWarning("", _JSHOP_ERROR_UPLOADING);
		}

		if (!$app->input->getInt("noredirect")){
			$app->redirect("index.php?option=com_jshopping&controller=importexport&task=view&ie_id=".$ie_id, _JSHOP_COMPLETED);
		}
	}

	function parse($filename)
	{
		$categories = [];

		require_once(JPATH_BASE.'/../cml/vendor/autoload.php');

		$parser = new \CommerceMLParser\Parser;
		$parser
			->addListener("CategoryEvent",
			function (\CommerceMLParser\Event\CategoryEvent $categoryEvent) use (&$categories)
			{
				$categories = $categoryEvent->getCategory()->fetch();
			});
		$parser->parse($filename);

		return $categories;
	}

	function getPrepareDataSave(&$input, &$catList)
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

        foreach($languages as $lang)
		{
			$post['name_'.$lang->language] = trim($input->getName());

			if ($jshopConfig->create_alias_product_category_auto)
			{
				$post['alias_'.$lang->language] = $post['name_'.$lang->language];
			}
			$post['alias_'.$lang->language] = JApplication::stringURLSafe($post['alias_'.$lang->language]);
			if ($post['alias_'.$lang->language]!="" && !$_alias->checkExistAlias1Group($post['alias_'.$lang->language], $lang->language, $input->getId(), 0))
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
		$query = $db->getQuery(true);

		$query->update($db->quoteName('#__jshopping_categories', 'jsh_cats'))
			->join('INNER', $db->quoteName('#__jshopping_import1c_categories', 's1') . ' ON (' . $db->quoteName('s1.category_id') . ' = ' . $db->quoteName('jsh_cats.category_id') . ')')
			->join('INNER' ,$db->quoteName('#__jshopping_import1c_categories', 's2') . ' ON (' . $db->quoteName('s2.xml_id') . ' = ' . $db->quoteName('s1.xml_parent_id') . ')')
			->set($db->quoteName('jsh_cats.category_parent_id') . '=' . $db->quoteName('s2.category_id'));
		$db->setQuery($query);
		$db->query();
	}
}