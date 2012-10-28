<?php
/**
 * @author      Tom Hartung <webmaster@tomhartung.com>
 * @package     Joomla
 * @subpackage  joomoocomments
 * @copyright   Copyright (C) 2010 Tom Hartung. All rights reserved.
 * @since       1.5
 * @license     GNU/GPL, see LICENSE.php .
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

require_once "../assets/constants.php";
require_once "../../com_joomoobase/requests/JoomoobaseUpdateDb.php";
require_once "../../../administrator/components/com_joomoocomments/tables/joomoocomments.php";
require_once "../../../libraries/joomla/plugin/helper.php";

/**
 * Ajax web service to delete a comment row from the database
 * This class runs outside of the joomla! framework
 */
class JoomoocommentDeleteFromDb extends JoomoobaseUpdateDb
{
	/**
	 * constructor
	 * @access public
	 */
	public function __construct()
	{
		//	print "Hello from JoomoocommentDeleteFromDb::__construct()<br />\n";

		parent::__construct();
		$this->_tableName = '#__joomoocomments';
	}

	/**
	 * driver function to save parameters
	 * @access public
	 * @return void
	 */
	public function deleteFromDatabase( )
	{
		//	print "Hello from JoomoocommentDeleteFromDb::deleteFromDatabase()<br />\n";

		$data = JRequest::get( 'post' );
		$this->_id = $data['id'];

		if ( $this->_id )
		{
			$db =& $this->_getDb();
			$table = new TableJoomoocomments( $db );
			//	$tableClass = get_class( $table );
			//	print "In JoomoocommentDeleteFromDb::_deleteRow(): tableClass = \"$tableClass\"<br />\n";

			$deletedOk = $table->delete( $this->_id );
			if ( $deletedOk )
			{
				print COMMENT_DELETED_OK;
			}
			else
			{
				$this->_message = $this->_db->getError();
				print "Error deleting comment: " . $this->_message;
			}
		}
		else
		{
			print 'Error deleting comment - no id specified.';
		}

		return $deletedOk;
	}
}
?>
