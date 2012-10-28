<?php
/********************************************************/
/* Copyright (C) 2009 Tom Hartung, All Rights Reserved. */
/********************************************************/

/**
 * @version     $Id: helloWorld.php,v 1.3 2009/05/04 14:04:01 tomh Exp tomh $
 * @author      Tom Hartung <webmaster@tomhartung.com>
 * @package     Joomla
 * @subpackage  templateparameters
 * @copyright   Copyright (C) 2009 Tom Hartung. All rights reserved.
 * @since       1.5
 * @license     GNU/GPL, see LICENSE.php .
 */

/**
 * ================================================
 * This class runs outside of the joomla! framework
 * ================================================
 * Therefore we define _JEXEC (rather than check to see if it's defined)
 */
define( '_JEXEC', 1 );

define( 'JPATH_BASE', dirname(__FILE__) );

/*
 * Just print a short message so we know we're connecting
 */
print "<p>Hello world from helloWorld.php</p>\n";

?>
