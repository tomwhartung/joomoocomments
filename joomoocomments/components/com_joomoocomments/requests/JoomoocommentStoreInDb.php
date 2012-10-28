<?php
/********************************************************/
/* Copyright (C) 2010 Tom Hartung, All Rights Reserved. */
/********************************************************/

/**
 * @author      Tom Hartung <webmaster@tomhartung.com>
 * @package     Joomla
 * @subpackage  templateparameters
 * @copyright   Copyright (C) 2010 Tom Hartung. All rights reserved.
 * @since       1.5
 * @license     GNU/GPL, see LICENSE.php .
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

require_once '../assets/constants.php';
require_once '../../com_joomoobase/requests/JoomoobaseStoreInDb.php';
require_once '../../../administrator/components/com_joomoocomments/tables/joomoocomments.php';
require_once '../../../libraries/joomla/plugin/helper.php';

/**
 * Ajax web service to save comment data in database
 * This class runs outside of the joomla! framework
 */
class JoomoocommentStoreInDb extends JoomoobaseStoreInDb
{
	/**
	 * constructor
	 * @access public
	 */
	public function __construct()
	{
		// print "Hello from JoomoocommentStoreInDb::__construct()<br />\n";

		parent::__construct();
		$this->_tableName = '#__joomoocomments';
	}

	/**
	 * driver function to save a comment
	 * @access public
	 * @return void
	 */
	public function storeInDatabase( )
	{
		//	print "Hello from JoomoocommentStoreInDb::storeInDatabase()<br />\n";

		$captcha_required = JRequest::getVar( 'captcha_required', '0' );

		if ( $captcha_required )
		{
			$captchaPathPrefix = '..' .DS. '..' .DS. 'com_joomoobase' .DS;
			$captcha_type = JRequest::getVar( "captcha_type" );
			require_once "../../com_joomoobase/assets/constants.php";
			require_once "../../com_joomoobase/captcha/JoomoobaseCaptcha.php";
			$captchaObject = new JoomoobaseCaptcha( $captcha_type, $captchaPathPrefix );
			if ( ! $captchaObject->checkCaptchaResponse() )
			{
				$message = $captchaObject->getError();
				print "Error storing data: " . $message;
				return False;
			}
		}

		$storedOk = parent::storeInDatabase();
	
		if ( $storedOk )
		{
			print COMMENT_SAVED_OK . COMMENTS_RESPONSE_DELIMITER . $this->_id . COMMENTS_RESPONSE_DELIMITER . $this->_data['form_number'];
		}
		else
		{
			$this->_message = $this->_db->getError();
			print "Error storing data: " . $this->_message;
		}

		return $storedOk;
	}
	/**
	 * set data (stdClass object) to values set in POST variables
	 * @access protected
	 * @return void
	 */
	protected function _setData( )
	{
		//	print "Hello from JoomoocommentStoreInDb::_setData()<br />\n";
		// $this->_printAllPostVariables();

		$this->_data = JRequest::get( 'post' );

		//	print "JoomoocommentStoreInDb::_setData():<br />\n";
		//	print "this->_data['contentid'] = '" . $this->_data['contentid'] . "'<br />\n";
		//	print "this->_data['created_by'] = '" . $this->_data['created_by'] . "'<br />\n";
		//	print "this->_data['name'] = '" . $this->_data['name'] . "'<br />\n";
		//	print "this->_data['text'] = '" . $this->_data['text'] . "'<br />\n";
		//	print "this->_data['published'] = '" . $this->_data['published'] . "'<br />\n";
	}

	/**
	 * store new value(s) for template parameter(s)
	 * @access protected
	 * @return True if successful, else False
	 */
	protected function _storeData( )
	{
		//	print "Hello from JoomoocommentStoreInDb::_storeData()<br />\n";

		$db =& $this->_getDb();
		$table = new TableJoomoocomments( $db );
		$email_on_form = $this->_data['email_on_form'];
		$website_on_form = $this->_data['website_on_form'];

		if ( ! $table->bind($this->_data) )
		{
			print "JoomoocommentStoreInDb::_storeData - bind error: " . $table->getError() . "<br />\n";
			$db->setError( $table->getError() );
			return FALSE;
		}
		//	else
		//	{
		//		print "JoomoocommentStoreInDb::_storeData(): table->bind() ran OK.<br />\n";
		//	}

		if ( ! $table->check($email_on_form, $website_on_form) )
		{
			//	print "JoomoocommentStoreInDb::_storeData - check error: " . $table->getError() . "<br />\n";
			$db->setError( $table->getError() );
			return FALSE;
		}
		//	else
		//	{
		//		print "JoomoocommentStoreInDb::_storeData(): table->check() ran OK.<br />\n";
		//	}

		if ( ! $table->store() )
		{
			print "JoomoocommentStoreInDb::_storeData - store error: " . $table->getError() . "<br />\n";
			$db->setError( $table->getError() );
			return FALSE;
		}
		//	else
		//	{
		//		print "JoomoocommentStoreInDb::_storeData(): table->store() ran OK. table->id = " . $table->id . "<br />\n";
		//	}

		$this->_id = $table->id;
		$this->_row =& $table;

		return TRUE;
	}
}
?>
