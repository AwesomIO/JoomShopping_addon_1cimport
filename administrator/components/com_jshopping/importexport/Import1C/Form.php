<?php
/**
 * @package     Jshopping
 * @subpackage  Import1C
 *
 * @copyright   Copyright (c) 2018 AwesomIO. All rights reserved.
 * @license     GNU General Public License v3.0; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');
?>
<form action="index.php?option=com_jshopping&controller=importexport" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="hidemainmenu" value="0" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="ie_id" value="<?php echo $ie_id;?>" />

	<?php echo 'Выберите файл для импорта'?> (*.xml):
	<input type="file" name="file">
</form>