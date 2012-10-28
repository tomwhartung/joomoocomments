<?php
/**
 * @version     $Id: joomoocomments.php,v 1.6 2008/10/31 06:12:03 tomh Exp tomh $
 * @author      Tom Hartung <webmaster@tomhartung.com>
 * @package     Joomla
 * @subpackage  Joomoocomments
 * @copyright   Copyright (C) 2008 Tom Hartung. All rights reserved.
 * @since       1.5
 * @license     GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.controller');

/**
 * Joomla interface to #__joomoocomments table
 */
class TableJoomoocomments extends JTable
{
	/**
	 * @var int Primary Key
	 */
	public $id = null;
	/**
	 * @var int Foreign Key: link to #__users table
	 */
	public $created_by = null;
	/**
	 * @var string name of person posting comment
	 */
	public $name = null;
	/**
	 * @var string email of person posting comment
	 */
	public $email = null;
	/**
	 * @var string website for person posting comment
	 */
	public $website = null;
	/**
	 * @var string ip address of person posting comment
	 */
	public $ip_address = null;
	/**
	 * @var string text of comment
	 */
	public $text = null;
	/**
	 * @var int Foreign Key: link to #__content table
	 */
	public $contentid = null;
	/**
	 * @var int Foreign Key: link to #__joomoogallerygroups table
	 */
	public $gallerygroupid = null;
	/**
	 * @var int Foreign Key: link to #__joomoogalleryimages table
	 */
	public $galleryimageid = null;
	/**
	 * @var date timestamp when user added comment
	 */
	public $created = null;
	/**
	 * @var int Published flag
	 */
	public $published = null;
	/**
	 * @var int count of like votes
	 */
	public $likes = null;
	/**
	 * @var int count of dislike votes
	 */
	public $dislikes = null;
	/**
	 * @var int spam flag
	 */
	public $spam = null;
	/**
	 * @var int Sequence of image within group
	 */
	public $ordering = null;

	/**
	 * Constructor
	 */
	function __construct( &$db )
	{
		// print "Hello from TableJoomoocomments::__construct()<br />\n";
		parent::__construct( '#__joomoocomments', 'id', $db );

		$filePath = JPATH_SITE.DS.'components'.DS.'com_joomoocomments'.DS.'assets'.DS.'constants.php';

		if ( ! file_exists($filePath) )      // use a different path when we are running as an ajax request
		{
			$filePath = '..'.DS.'assets'.DS.'constants.php';
		}

		require_once ($filePath);
	}

	/**
	 * run htmlspecialchars on the text of the comment
	 * @param mixed $data array or objects to bind
	 * @access public
	 * @return bool
	 */
	public function bind( $data )
	{
		// Note: won't print if we redirect
		// print "Hello from TableJoomoocomments::bind()<br />\n";
		// print "Running print_r on the data arg:<br />\n";
		// print_r ( $data );
		// print "<br />\n";

		$new_text = trim( $data['text'] );
		$data['text'] = $new_text;

		$dateString = date('Y-m-d H:i:s');
		$data['created'] = $dateString;

		return parent::bind( $data );
	}

	/**
	 * Validator: e.g., ensure the comment text has at least MINIMUM_COMMENT_LENGTH characters, etc
	 * Javascript validation is done in JoomooCommentsLib.validateForm(), which is in:
	 *    components/com_joomoocomments/javascript/JoomooCommentsLib.js 
	 * @return boolean True if values are valid else False
	 */
	public function check( $email_on_form=null, $website_on_form=null )
	{
		// // Note: won't print if we redirect
		//	print "Hello from TableJoomoocomments::check<br />\n";
		//	print "email_on_form = " . $email_on_form . "<br />\n";
		//	print "website_on_form = " . $website_on_form . "<br />\n";
		//	print "Running print_r on this:<br />\n";
		//	print_r ( $this );
		//	print "<br />\n";

		if ( ! (is_numeric($this->contentid) && 0 < $this->contentid) &&
		     ! (is_numeric($this->gallerygroupid) && 0 < $this->gallerygroupid) &&
		     ! (is_numeric($this->galleryimageid) && 0 < $this->galleryimageid)   )
		{
			$message  = 'Unable to store comment.  ';
			$message .= 'You must specify either a contentid ("' . $this->contentid . '"), a ';
			$message .= 'gallerygroupid ("' . $this->gallerygroupid . '") or a ';
			$message .= 'galleryimageid ("' . $this->galleryimageid . '").';
			$this->setError( JText::_($message) );
			return False;
		}

		if ( ! (is_numeric($this->created_by) && 0 <= $this->created_by) )
		{
			$message = 'Invalid created_by ("' . $this->created_by . '").';
			$this->setError( JText::_($message) );
			return False;
		}

		//
		// We require an email address only for anonymous users
		//
		if ( $this->created_by == 0 )
		{
			if ( isset($this->email) && 0 < strlen($this->email) )
			{
				$email = trim( $this->email );
				if ( 4 < strlen($email) )
				{
					$emailRegEx = '&^\S+@\S+\.\S+$&';
					if ( ! preg_match($emailRegEx,$email) )
					{
						$message = 'The specified email address (' . $email . ') is not in the proper format.';
						$this->setError( JText::_($message) );
						return False;
					}
				}
				$this->email = $email;
			}
			else
			{
				if ( $email_on_form == REQUIRED_FIELD )
				{
					$message = 'You must specify an email address.';
					$this->setError( JText::_($message) );
					return False;
				}
			}
		}

		if ( isset($this->website) )
		{
			$website = trim( $this->website );
			$website = preg_replace( '&^http://&', '', $website );
			if ( 0 < strlen($website) )
			{
				$websiteRegEx = '&^\S+\.\S+$&';
				if ( ! preg_match($websiteRegEx,$website) )
				{
					$message = 'The specified website (' . $website . ') is not in the proper format.';
					$this->setError( JText::_($message) );
					return False;
				}
			}
			$this->website = $website;
		}
		else if ( isset($website_on_form) && $website_on_form == REQUIRED_FIELD )
		{
			$message = 'You must specify a website.';
			$this->setError( JText::_($message) );
			return False;
		}

		if ( strlen($this->name) < 1 )
		{
			$message = 'Please specify a name.';
			$this->setError( JText::_($message) );
			return False;
		}

		$text = trim( $this->text );
		$text = preg_replace( '&\s\s+&', ' ', $text );
		$textLength = strlen($text);

		if ( $textLength < MINIMUM_COMMENT_LENGTH )
		{
			$message = 'Your comment ("' . $text . '") contains ' . $textLength . ' character(s), ' .
				'which is less than the minimum (' . MINIMUM_COMMENT_LENGTH . ').';
			$this->setError( JText::_($message) );
			return False;
		}
		else if ( MAXIMUM_COMMENT_LENGTH < $textLength )
		{
			$message = 'Your comment ("' . $text . '") contains ' . $textLength . ' characters, ' .
				'which is more than the maximum (' . MAXIMUM_COMMENT_LENGTH . ').';
			$this->setError( JText::_($message) );
			return False;
		}

		$this->text = $text;

		return True;
	}
}
