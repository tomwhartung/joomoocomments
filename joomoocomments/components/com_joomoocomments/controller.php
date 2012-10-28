<?php
/**
 * @version     $Id: controller.php,v 1.20 2008/10/31 06:31:54 tomh Exp tomh $
 * @author      Tom Hartung <webmaster@tomhartung.com>
 * @package     Joomla
 * @subpackage  Joomoocomments
 * @copyright   Copyright (C) 2010 Tom Hartung. All rights reserved.
 * @since       1.5
 * @license     GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.controller');

/**
 * Joomoocomments Component Controller
 */
class JoomoocommentsController extends JController
{
	//
	// This component uses the comments db model defined in the backend
	//
	/**
	 * path to code for parent class to comments model class - in joomooobase!
	 * @access private
	 * @var string file path for parent class to comments model class
	 */
	private $_parentModelPath;
	/**
	 * path to code for comments model class
	 * @access private
	 * @var string file path to comments model class
	 */
	private $_commentsModelPath;
	/**
	 * model supporting access to comments table in DB
	 * @access private
	 * @var instance of JoomoocommentsModelJoomoocomments
	 */
	private $_commentsModel = '';

	/**
	 * Constructor: set the model paths
	 * @access public
	 */
	public function __construct( $default = array() )
	{
		parent::__construct( $default );

		// print "Hello from JoomoocommentsController::__construct()<br />\n";

		$this->_parentModelPath   = 'components'.DS.'com_joomoobase'.DS.'models'.DS.'joomoobaseDb.php';
		$this->_commentsModelPath = 'administrator'.DS.'components'.DS.'com_joomoocomments'.DS.'models'.DS.'joomoocomments.php';
	}

	/**
	 * Unused - we either post and redirect or do nothin' - kept as kind of a safety-net
	 * @access public
	 */
	public function display()
	{
		print "Hello from JoomoocommentsController::display() in file controller.php<br />\n";

		// parent::display();
	}
	/**
	 * posts a comment and redirects to article
	 * @access public
	 * @return TRUE if successful else FALSE
	 */
	public function post_comment( )
	{
		// print "Hello from JoomoocommentsController::post_comment() in file controller.php<br />\n";

		$plugin =& JPluginHelper::getPlugin( 'content', 'joomoocomments' );
		$pluginParams = new JParameter( $plugin->params );
		$link = JRequest::getVar( 'readmore_link', 'index.php' );
		$captcha_required = JRequest::getVar( 'captcha_required' );
		$message = '';

		if ( $captcha_required )
		{
			$captcha_type = $pluginParams->get( "captcha_type" );
 			$baseConstantsFilePath = JPATH_SITE.DS.'components'.DS.'com_joomoobase'.DS.'assets'.DS.'constants.php';
 			require_once( $baseConstantsFilePath );
 			$captchaFilePath = JPATH_SITE.DS.'components'.DS.'com_joomoobase'.DS.'captcha'.DS.'JoomoobaseCaptcha.php';
 			require_once( $captchaFilePath );
			$captchaObject = new JoomoobaseCaptcha( $captcha_type );
			if ( ! $captchaObject->checkCaptchaResponse() )
			{
				$message .= $captchaObject->getError();
				// print "post_comment (captcha required) not redirecting; link = " . $link . "; message = " . $message . "<br />\n";
				$this->setRedirect( $link, $message );
				return False;
			}
		}

		require_once $this->_parentModelPath;
		require_once $this->_commentsModelPath;
		$this->_commentsModel = new JoomoocommentsModeljoomoocomments();

		$autopub_anonymous = $pluginParams->get( "autopub_anonymous" );
		$storedOk = $this->_commentsModel->store( $autopub_anonymous );

		if ( $storedOk )
		{
			$user = & JFactory::getUser();
			if ( 0 < $user->id || $autopub_anonymous )
			{
				$message .= 'Comment Posted - Thanks!!';
			}
			else
			{
				$message .= 'Comment will be posted pending approval by a site admin.<br />';
				$message .= 'Please check back later to see your comment!';
			}
		}
		else
		{
			$errorMessage = $this->_commentsModel->getError();
			if ( 0 < strlen($errorMessage) )
			{
				$message .= "Error saving comment: " . $errorMessage . '<br />';
				$message .= 'Please try again.';
			}
			else
			{
				$message .= 'Something went wrong and we are unable to post your comment at this time.<br />';
				$message .= 'Please try again later.';
			}
		}

		// $tableName = $this->_commentsModel->getTableName();
		// $id = $this->_commentsModel->getId();
		// $message .= '<br />tableName = ' . $tableName;
		// $message .= '<br />id = ' . $id;

		// print "post_comment not redirecting; link = " . $link . "; message = " . $message . "<br />\n";
		$this->setRedirect( $link, $message );

		return $storedOk;
	}
	/**
	 * deletes a comment and redirects to article
	 * @access public
	 * @return TRUE if successful else FALSE
	 */
	public function delete( )
	{
		print "Hello from JoomoocommentsController::delete() in file controller.php<br />\n";

		require_once $this->_parentModelPath;
		require_once $this->_commentsModelPath;
		$this->_commentsModel = new JoomoocommentsModeljoomoocomments();

		$link = JRequest::getVar( 'readmore_link', 'index.php' );
		$id = JRequest::getVar( 'id', 0 );
		$user = & JFactory::getUser();

		if ( $id == 0 )
		{
			$message = 'Unable to delete comment (id=0).';
		}
		else
		{
			$this->_commentsModel->setId( $id );
			$row = $this->_commentsModel->getRow();
			print 'JoomoocommentsController::delete(): = this->_commentsModel->getId() ' . $this->_commentsModel->getId() . '<br />';
			if ( $user->id )
			{
				if ( 0 < $row->created_by && $row->created_by == $user->id )
				{
					$deletedOk = $this->_commentsModel->delete();
					if ( $deletedOk )
					{
						$message = 'Comment deleted Ok.';
					}
					else
					{
						$errorMessage = $this->_commentsModel->getError();
						strlen($errorMessage) > 0 ? $message = 'Unable to delete comment: ' . $errorMessage . '.' :
							$message = 'Unable to delete comment - please try again later.';
					}
				}
				else
				{
					$message = 'Unable to delete comment - current user is not the owner of it.';
				}
			}
			else
			{
				$message = 'You must be logged-in to delete comments.';
			}
		}

		// print "delete not redirecting; link = " . $link . "; message = " . $message . "<br />\n";
		$this->setRedirect( $link, $message );
	}
}
