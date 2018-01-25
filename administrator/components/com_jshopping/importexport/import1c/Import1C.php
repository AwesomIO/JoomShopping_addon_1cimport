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


		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$columns = array('category_id','xml_id','xml_parent_id');
		$values = array();

		$values[] = '1, 23423, 234';
		$values[] = '2, 23223, 23423';

		$query->insert($db->quoteName('#__jshopping_import1c_categoriec'));
		$query->columns($columns);
		$query->values($values);
		$db->setQuery($query);
		$db->query();

		$ie_id = $app->input->getInt("ie_id");
		if (!$ie_id) $ie_id = $this->get('ie_id');

		$lang = JSFactory::getLang();
		$db = JFactory::getDBO();

		$_importexport = JSFactory::getTable('ImportExport', 'jshop');
		$_importexport->load($ie_id);
		$alias = $_importexport->get('alias');
		$_importexport->set('endstart', time());
		$_importexport->store();

		//get list category
		//$query = "SELECT category_id as id, `".$lang->get("name")."` as name FROM `#__jshopping_categories`";


		$_categories = JSFactory::getModel('categories', 'JshoppingModel');

		$dir = $jshopConfig->importexport_path.$alias."/";

		$upload = new UploadFile($_FILES['file']);
		$upload->setAllowFile(array('csv'));
		$upload->setDir($dir);

		if ($upload->upload())
		{



			$test_id = 1;

			$filename = $dir . "/" . $upload->getName();
			@chmod($filename, 0777);

			/*$categories = $this->parse($filename);

			if(count($categories)){
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);

				$columns = array('category_id','xml_id','xml_parent_id');
				$values = array();

				$test_id = 1;

				foreach($categories as $category)
				{
					$values[] = "$test_id, $category->id, $category->parent";
					$test_id++;
				}

				$query->insert($db->quoteName('#__jshopping_import1c_categoriec'));
				$query->columns($columns);
				$query->values($values);
				$db->setQuery($query);
				$db->query();
			}
			else {
				JError::raiseWarning("", _JSHOP_ERROR_UPLOADING);
			}*/

			@unlink($filename);
		}

		if (!$app->input->getInt("noredirect")){
			$app->redirect("index.php?option=com_jshopping&controller=importexport&task=view&ie_id=".$ie_id, _JSHOP_COMPLETED);
		}
	}

	function parse($filename)
	{
		$categories = [];

		require_once(JPATH_BASE.'../cml/vendor/autoload.php');

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
}