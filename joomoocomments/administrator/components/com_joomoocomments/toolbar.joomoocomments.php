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

require_once( JApplicationHelper::getPath( 'toolbar_html' ) );

/**
 * @package     Joomla
 * @subpackage  Joomoocomments
 */
switch( $task )
{
	case 'add':
	case 'edit':
		TOOLBAR_joomoocomments:: _NEW();
		break;

	default:
		TOOLBAR_joomoocomments::_DEFAULT();
		break;
}
?>
