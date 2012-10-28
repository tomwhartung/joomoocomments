<?php
/**
 * @version     $Id: joomoocomments.php,v 1.7 2008/10/30 06:18:46 tomh Exp tomh $
 * @author      Tom Hartung <webmaster@tomhartung.com>
 * @package     Joomla
 * @subpackage  Joomoocomments
 * @link        http://dev.joomla.org/component/option,com_jd-wiki/Itemid,31/id,tutorials:components/
 * @copyright   Copyright (C) 2008 Tom Hartung. All rights reserved.
 * @since       1.5
 * @license     GNU/GPL, see LICENSE.php
 */

defined( '_JEXEC' ) or die( 'Restricted access' );      // no direct access

//
// Load code for the model and controller classes
// ----------------------------------------------
// JoomoocommentsController - in controllers/joomoocomments.php
// JoomoocommentsModelJoomoocomments - model class - in models/joomoocomments.php
//
// print "Hello from joomoocomments.php<br />\n";
require_once( JPATH_COMPONENT.DS.'controllers'.DS.'joomoocomments.php' );
require_once( JPATH_SITE.DS.'components'.DS.'com_joomoobase'.DS.'models'.DS.'joomoobaseDb.php' );
require_once( JPATH_COMPONENT.DS.'models'.DS.'joomoocomments.php' );

JTable::addIncludePath( JPATH_COMPONENT.DS.'tables' );  // enables JTable to find subclasses in tables subdir.

// print "joomoocomments.php: instantiating controller<br />\n";
$controller = new JoomoocommentsController();                      // Create the controller

// $task = JRequest::getVar('task');
// print "joomoocomments.php: task: \"$task\"<br />\n";

$controller->execute( JRequest::getVar( 'task' ) );   // Perform the Request task
$controller->redirect();                              // Redirect if set by the controller

?>
