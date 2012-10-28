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

require_once '../assets/constants.php';
require_once '../../com_joomoobase/requests/JoomoobaseUpdateDb.php';
require_once '../../com_joomoobase/utilities/JoomoobaseEmailer.php';
require_once '../../../administrator/components/com_joomoocomments/tables/joomoocomments.php';
require_once '../../../libraries/joomla/plugin/helper.php';

/**
 * Ajax web service to update a comment row in the database
 * This class runs outside of the joomla! framework
 */
class JoomoocommentUpdateDb extends JoomoobaseUpdateDb
{
	/**
	 * Tasks are actually the names columns we are going to increment
	 * @access private
	 */
	private $_validTasks;

	/**
	 * constructor
	 * @access public
	 */
	public function __construct()
	{
		//	print "Hello from JoomoocommentUpdateDb::__construct()<br />\n";

		parent::__construct();
		$this->_tableName = '#__joomoocomments';
	}

	/**
	 * driver function to increment count columns in the database
	 * @access public
	 * @return void
	 */
	public function updateDatabase( )
	{
		//	print "Hello from JoomoocommentUpdateDb::updateDatabase()<br />\n";

		$data = JRequest::get( 'post' );
		$this->_id = $data['id'];
		$this->_validTasks = array ( 'likes', 'dislikes', 'spam' );

		if ( $this->_id )
		{
			$task = $data['task'];
			if ( in_array($task, $this->_validTasks) )
			{
				$db =& $this->_getDb();
				//	$dbClass = get_class( $db );
				//	print "updateDatabase(): dbClass = \"$dbClass\"<br />\n";

				$query = 'UPDATE ' . $db->nameQuote($this->_tableName) .
					' SET ' . $db->nameQuote($task) . '=' . $db->nameQuote($task) . '+1 ' .
					'WHERE ' . $db->nameQuote('id') . '=' . $this->_id . ';';
				$db->setQuery( $query );
				$updatedOk = $db->query();
				if ( $updatedOk )
				{
					$getValue = 'SELECT ' . $db->nameQuote($task) .
						'FROM' . $db->nameQuote($this->_tableName) .
						'WHERE ' . $db->nameQuote('id') . '=' . $this->_id . ';';
					$db->setQuery( $getValue );
					$result = $db->loadResult();
					print COMMENT_UPDATED_OK . COMMENTS_RESPONSE_DELIMITER . $result;
					$spam_flag_email = $data['spam_flag_email'];
					if ( $task == 'spam' && $spam_flag_email )
					{
						$base = $data['base'];
						if ( $this->_sendSpamFlagNotification( $base ) )
						{
							print COMMENTS_RESPONSE_DELIMITER . 'Notification sent OK.';
						}
						else
						{
							print COMMENTS_RESPONSE_DELIMITER . 'Error sending notification.';
						}
					}
				}
				else
				{
					//	print "updateDatabase(): query = \"$query\"<br />\n";
					$this->_message = $this->_db->getError();
					print "Error running query (' . $query . '): " . $this->_message;
				}
			}
			else
			{
				print 'Error updating comment - invalid task specified: "' . $task . '".';
			}
		}
		else
		{
			print 'Error updating comment - no id specified.';
		}

		return $updatedOk;
	}
	/**
	 * sends spam email notification
	 * @access private
	 * @return boolean return value from call to mail() (True if successful else False)
	 */
	private function _sendSpamFlagNotification( $base=null )
	{
		$db =& $this->_getDb();
		$table = new TableJoomoocomments( $db );
		$table->load( $this->_id );
		$this->_row = $table;

		$headers  = '';
		$message  = '';
		$message .= 'A user flagged a comment as spam.' . "\n\n";

		if ( 0 < $this->_row->contentid )
		{
			$subject = 'Comment ' . $this->_row->id . ' for Article ' . $this->_row->contentid . ' Flagged as Spam';
			$message .= $this->_getContentDetails( $db );
		}
		else if ( 0 < $this->_row->gallerygroupid )
		{
			$subject = 'Comment ' . $this->_row->id . ' for Gallery Group ' . $this->_row->gallerygroupid . ' Flagged as Spam';
			$message .= $this->_getGalleryGroupDetails( $db );
		}
		else if ( 0 < $this->_row->galleryimageid )
		{
			$subject = 'Comment ' . $this->_row->id . ' for Gallery Image ' . $this->_row->galleryimageid . ' Flagged as Spam';
			$message .= $this->_getGalleryImageDetails( $db );
		}
		else
		{
			$subject = 'Comment ' . $this->_row->id . ' Flagged as Spam - Unknown Article/Gallery Group/Gallery Image!';
			$message .= 'Internal error: comment not linked to an article, gallery group, or gallery page!';
		}

		$message .= 'User ' . $this->_row->created_by . ': ' . $this->_row->name . "\n";

		$this->_row->ip_address == null ?
			$message .= 'IP address: not saved' . "\n" :
			$message .= 'IP address: ' . $this->_row->ip_address . "\n";

		$message .= 'Comment ' . $this->_id . ': ' . $this->_row->text . "\n\n";

		if ( $base != null )
		{
			$message .= 'Link to article: ' . $base . "\n";
		}

		JFactory::getConfig( "../../../configuration.php" );
		$config = new JConfig();

		$mailer = new JoomoobaseEmailer();
		$mailer->recipient = $config->mailfrom;
		$mailer->sender    = 'do_not_reply@' . $config->sitename;
		$mailer->subject = $subject;
		$mailer->body = $message;
		$mailer->headers = $headers;

		return $mailer->sendEmailJMail( );
	}
	/**
	 * gets details about article containing comment flagged as spam
	 * @access private
	 * @return string containing details retrieved from content table
	 */
	private function _getContentDetails( &$db )
	{
		$message = '';
		$getValue = 'SELECT ' . $db->nameQuote('title') .
						'FROM' . $db->nameQuote('#__content') .
						'WHERE ' . $db->nameQuote('id') . '=' . $this->_row->contentid . ';';
		$db->setQuery( $getValue );
		$title = $db->loadResult();

		$message .= 'Article ' . $this->_row->contentid . ': ' . $title . "\n";

		return $message;
	}
	/**
	 * gets details about gallery image containing coment flagged as spam
	 * @access private
	 * @return string containing details retrieved from content table
	 */
	private function _getGalleryGroupDetails( &$db )
	{
		$getValue = 'SELECT ' . $db->nameQuote('title') .
						'FROM' . $db->nameQuote('#__joomoogallerygroups') .
						'WHERE ' . $db->nameQuote('id') . '=' . $this->_row->gallerygroupid . ';';
		$db->setQuery( $getValue );
		$title = $db->loadResult();

		$message .= 'Gallery Group ' . $this->_row->gallerygroupid . ': ' . $title . "\n";

		return $message;
	}
	/**
	 * gets details about gallery image containing coment flagged as spam
	 * @access private
	 * @return string containing details retrieved from content table
	 */
	private function _getGalleryImageDetails( &$db )
	{
		$getValue = 'SELECT ' . $db->nameQuote('title') .
						'FROM' . $db->nameQuote('#__joomoogalleryimages') .
						'WHERE ' . $db->nameQuote('id') . '=' . $this->_row->galleryimageid . ';';
		$db->setQuery( $getValue );
		$title = $db->loadResult();

		$message .= 'Gallery Image ' . $this->_row->galleryimageid . ': ' . $title . "\n";

		return $message;
	}
}
?>
