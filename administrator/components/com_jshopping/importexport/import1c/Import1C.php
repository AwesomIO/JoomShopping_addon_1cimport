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

require_once(JPATH_BASE.'/../cml/vendor/autoload.php');
require_once __DIR__.'/helpers/CategoryHelper.php';
require_once __DIR__.'/helpers/ProductHelper.php';

function getCurDate(){
    return date("Y-m-d H:i:s", time());
}

class IeImport1C extends IeController
{
    public $_app;
    public $jsConfig;
    public $parameters;
    public $_alias;
    public $_categories;

	function view()
	{
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

	private function init(){
	    $this->_app = &JFactory::getApplication();
	    $this->jsConfig = &JSFactory::getConfig();
	    $this->parameters = new \Joomla\Registry\Registry;
	    $this->_alias = &JSFactory::getModel("alias");
        $this->_categories = &JSFactory::getModel('categories', 'JshoppingModel');

	    // set parameters of import-export
        $ie_id = $this->_app->input->getInt("ie_id");
        if (!$ie_id) $ie_id = intval($this->get('ie_id'));
        $ie = $this->getIE($ie_id);
	    $this->parameters->set('ie', $ie);

        // get languages of component
        $lang = JSFactory::getModel("languages");
        $this->parameters->set('languages', $lang->getAllLanguages(1));
    }

    static function getIE(int $ieId){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->update($db->quoteName('#__jshopping_import_export'))
            ->set($db->quoteName('endstart') .'='. time())
            ->where($db->quoteName('id') .' = '. $ieId);
        $db->setQuery($query);
        $db->execute();
        $query->clear();
        $query->select('*')
            ->from($db->quoteName('#__jshopping_import_export'))
            ->where($db->quoteName('id') .' = '. $ieId);
        $db->setQuery($query);
        $ie = $db->loadAssoc();
        unset($db, $query);
        return $ie;
    }

    private function makeUpload($dir){
        require_once(JPATH_COMPONENT_SITE . '/lib/uploadfile.class.php');

        $upload = new UploadFile($_FILES['file']);
        $upload->setAllowFile(array('xml'));
        $upload->setDir($dir);
        if ($upload->upload())
        {
            @chmod($dir . "/" . $upload->getName(), 0777);
            return $upload->getName();
        }
        else
        {
            JError::raiseWarning("", _JSHOP_ERROR_UPLOADING);
            $this->end();
            return false;
        }
    }

	function save()
	{
	    $this->init();

		$dir = $this->jsConfig->importexport_path . $this->parameters->get('ie')['alias'] .'/';
        if($file=$this->makeUpload($dir))
        {
            $filename = $dir . '/' . $file;
			$this->parse($filename);
			//@unlink($filename);
		}
		else {
            $this->end();
		}

//		if (!$app->input->getInt("noredirect")){
//			$app->redirect("index.php?option=com_jshopping&controller=importexport&task=view&ie_id=".$ie_id, _JSHOP_COMPLETED);
//		}
	}

	function parse($filename)
	{
		$parser = new \CommerceMLParser\Parser;

		$that = &$this;

		$parser
			->addListener("CategoryEvent",
                function (\CommerceMLParser\Event\CategoryEvent $categoryEvent) use (&$that)
                {
                    CategoryHelper::helper($categoryEvent->getCategory()->fetch(), $that);
                });


        $parser->addListener("ProductEvent", function (\CommerceMLParser\Event\ProductEvent $ProductEvent) use (&$that) {
            ProductHelper::helper($ProductEvent->getProduct(), $that);
        });

		$parser->parse($filename);
	}

	private function end(){
        if (!$this->_app->input->getInt("noredirect")){
		    $this->_app->redirect("index.php?option=com_jshopping&controller=importexport&task=view&ie_id=".$this->parameters->get('ie')['id'], _JSHOP_COMPLETED);
		}
    }
}