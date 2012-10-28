<?php
/*************************************************************/
/* Copyright (C) 2006-2010 Tom Hartung, All Rights Reserved. */
/*************************************************************/

/**
 * @version     $Id: constants.php,v 1.57 2008/11/12 22:51:49 tomh Exp tomh $
 * @author      Tom Hartung <webmaster@tomhartung.com>
 * @package     Joomla
 * @subpackage  joomoocomments
 * @copyright   Copyright (C) 2010 Tom Hartung. All rights reserved.
 * @since       1.5
 * @license     TBD.
 */
	//
	// constants.php: constants and default values
	// -------------------------------------------
	//
	// This first batch of constants allows for further customization of the extension
	//
	/**
	 * string to use for name when commenter is not logged in
	 * if you change this you should definitely also change the default value in mysql
	 */
	define ( 'NAME_FOR_ANONYMOUS_USERS', 'Anonymous' );

	/**
	 * string to use for delete link in upper left corner of comments posted by the current user
	 */
	define ( 'TEXT_FOR_DELETE_LINK', '(X)' );
	/**
	 * minimum gid to allow user to delete any user's comments
	 * allows administrators to delete any comment in the front end
	 * 21 = Publisher; 23 = Manager; 24 = Administrator; 25 = Super Administrator
	 */
	define ( 'MINIMUM_GID_TO_DELETE_ANY', 24 );

	/**
	 * number of rows for comment text area in form
	 */
	define ( 'POSTING_COMMENT_ROWS', 7 );
	/**
	 * number of columns for comment text area in form
	 */
	define ( 'POSTING_COMMENT_COLUMNS', 49 );
	/**
	 * minimum length of comments (text)
	 */
	define ( 'MINIMUM_COMMENT_LENGTH', 3 );
	/**
	 * maximum length of comments (text)
	 */
	define ( 'MAXIMUM_COMMENT_LENGTH', 420 );
	/**
	 * response from server when ajax request to save a comment is successful
	 */
	define ( 'COMMENT_SAVED_OK', 'Comment saved OK - Thanks!' );
	/**
	 * response from server when ajax request to delete a comment is successful
	 */
	define ( 'COMMENT_DELETED_OK', 'Comment deleted OK.' );
	/**
	 * response from server when ajax request to update a comment is successful
	 */
	define ( 'COMMENT_UPDATED_OK', 'Comment updated OK.' );
	/**
	 * delimiter used in response from server (seems silly to use xml or jason when all we are passing is the new id...)
	 */
	define ( 'COMMENTS_RESPONSE_DELIMITER', '|' );
	/**
	 * honeypot input field name - don't use any special characters
	 */
	define ( 'HONEYPOT_FIELD_NAME', 'url' );
	/**
	 * honeypot input field label - tell humans to leave it blank
	 */
	define ( 'HONEYPOT_FIELD_LABEL', 'Do not fill in:' );
	/**
	//
	// ---------------------------------------------
	// The extension uses these constants internally
	// ---------------------------------------------
	// Change the following constants at your own risk!!
	// -------------------------------------------------
	//
	/**
	 * Possible values of one or more options defined in plugins/content/joomoocomments.xml
	 * Based on paramters set in the back end, fields such as email and website may be required, optional, or omitted
	 */
	define ( 'REQUIRED_FIELD', 'Y' );
	define ( 'OPTIONAL_FIELD', 'O' );
	define ( 'OMIT_FIELD', 'N' );
?>
