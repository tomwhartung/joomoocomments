<?php
/**
 * @author      Tom Hartung <webmaster@tomhartung.com>
 * @package     Joomla
 * @subpackage  Joomoocomments
 * @copyright   Copyright (C) 2010 Tom Hartung. All rights reserved.
 * @since       1.5
 * @license     GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

/**
 * @package     Joomla
 * @subpackage  Joomoocomments
 */
class TOOLBAR_joomoocomments
{
	/**
	 * Setup Joomoocomments toolbars
	 */
	function _NEW()
	{
		JToolBarHelper::save();
		JToolBarHelper::apply();
		JToolBarHelper::cancel();
	}

	function _DEFAULT()
	{
		JToolBarHelper::publishList();
		JToolBarHelper::unpublishList();
		JToolBarHelper::addNew();
		JToolBarHelper::editList();
		JToolBarHelper::deleteList( "Are you sure you want to delete these rows?" );
	//	JToolBarHelper::preferences('com_joomoocomments', '500');
	}
}
?>
