<?php
/**
 * @version     $Id: joomoocomments.php,v 1.10 2008/10/31 19:25:26 tomh Exp tomh $
 * @author      Tom Hartung <webmaster@tomhartung.com>
 * @package     Joomla
 * @subpackage  Joomoocomments
 * @copyright   Copyright (C) 2008 Tom Hartung. All rights reserved.
 * @since       1.5
 * @license     GNU/GPL, see LICENSE.php
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// print 'JPATH_SITE = ' . JPATH_SITE . "<br />\n";
JTable::addIncludePath( JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_joomoocomments'.DS.'tables' );

require_once (JPATH_COMPONENT.DS.'assets'.DS.'constants.php');   // Require constants
require_once (JPATH_COMPONENT.DS.'controller.php');         // Require controller code
$controller = new JoomoocommentsController( );              // Create the controller

//
// get the task - even though it is most likely blank - because that's what's expected
//    in this component the controller uses the view name to determine what to do
//    this is because what we do depends on which menu option has been selected
//
$task = JRequest::getCmd('task');

// $view = JRequest::getVar('view');
// print "joomoocomments.php: view = \"$view\"<br />\n";
// $Itemid = JRequest::getVar('Itemid');
print "joomoocomments.php: task = \"$task\"<br />\n";

$controller->execute( $task );   // Perform the Request task

$controller->redirect();         // Redirect if set by the controller
?>
