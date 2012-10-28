<?php
/**
 * @version     $Id: view.html.php,v 1.2 2008/10/31 06:17:34 tomh Exp tomh $
 * @author      Tom Hartung <webmaster@tomhartung.com>
 * @package     Joomla
 * @subpackage  Joomoocomments
 * @link        http://dev.joomla.org/component/option,com_jd-wiki/Itemid,31/id,tutorials:components/
 * @copyright   Copyright (C) 2010 Tom Hartung. All rights reserved.
 * @since       1.5
 * @license     GNU/GPL, see LICENSE.php
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.view');

/**
 * Joomoocomments View class for com_joomoocomments component
 */
class JoomoocommentsViewJoomoocomments extends JView
{
	/**
	 * constructor
	 * @access public
	 */
	public function __construct()
	{
		parent::__construct();
		// print "Hello from __construct() in com_joomoocomments/views/joomoocomments/view.html.php<br />\n";
	}

	/**
	 * Joomoocomments view display method
	 * @return void
	 **/
	function display($tpl = null)
	{
		// print "Hello from JoomoocommentsViewJoomoocomments::display()<br />\n";
		// print "tpl = \"" . $tpl . "\"<br />\n";
		// $tpl = 'html';   // loads tmpl/default_html.php (instead of tmpl/default.php)

		parent::display($tpl);
	}
}
?>
