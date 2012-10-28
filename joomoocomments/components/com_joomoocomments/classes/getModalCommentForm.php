<?php
/**
 * @package		Joomla
 * @subpackage	Content
 * @copyright	Copyright (C) 2010 - Tom Hartung.  All rights reserved.
 * @license		TBD.
 */

/**
 * ================================================
 * This class runs outside of the joomla! framework
 * ================================================
 * Therefore we define _JEXEC (rather than check to see if it's defined)
 */
define( '_JEXEC', 1 );
define( 'JPATH_BASE', dirname(__FILE__) );
define( 'JPATH_PLATFORM', dirname(__FILE__));
//print "<p>JPATH_BASE = '" . JPATH_BASE . "'</p>\n";
//print "<p>JPATH_PLATFORM = '" . JPATH_PLATFORM . "'</p>\n";

if ( !defined('DIRECTORY_SEPARATOR') )
{
	define( 'DIRECTORY_SEPARATOR', "/" );
}
define('DS', DIRECTORY_SEPARATOR);

require_once "joomoocommentsForm.php";
require_once "../assets/constants.php";

require_once "../../../configuration.php";
require_once "../../../libraries/loader.php";
require_once "../../../libraries/joomla/base/object.php";
require_once "../../../libraries/joomla/factory.php";
require_once "../../../libraries/joomla/database/database.php";
require_once "../../../libraries/joomla/database/database/mysql.php";
require_once "../../../libraries/joomla/database/table.php";
require_once "../../../libraries/joomla/error/error.php";
require_once "../../../libraries/joomla/environment/request.php";
require_once "../../../libraries/joomla/filter/filterinput.php";
require_once "../../../libraries/joomla/methods.php";
require_once "../../../libraries/joomla/user/user.php";

//	require_once "../../../libraries/joomla/plugin/helper.php";
//	$plugin =& JPluginHelper::getPlugin( 'content', 'joomoocomments' );
//	$pluginParams = new JParameter( $plugin->params );
$user = & JFactory::getUser();

$form = new JoomoocommentsForm( $pluginParams, $user );
$article =  new stdClass;
print $form->getFormTag( $article, TRUE );

?>
