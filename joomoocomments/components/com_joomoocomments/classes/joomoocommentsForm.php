<?php
/**
 * @package		Joomla
 * @subpackage	Content
 * @copyright	Copyright (C) 2010 - Tom Hartung.  All rights reserved.
 * @license		TBD.
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Returns form tag for Joomoo Comments
 *
 * @package		Joomla
 * @subpackage	Content
 * @since 		1.5
 */
class JoomoocommentsForm
{
	/**
	 * User currently logged in - or not
	 * @var JUser Object
	 */
	private $_user;

	/**
	 * Constructor
	 * @param object $subject The object to observe
	 * @param object $params  The object that holds the plugin parameters
	 * @since 1.5
	 */
	public function JoomoocommentsForm( $pluginParams, $user )
	{
		$this->_user = $user;
	}

	/**
	 * assembles xhtml containing the comment form
	 * @access public
	 * @return string xhtml containing comment form
	 */
	public function getFormTag( $article=null, $submitViaAjax=FALSE )
	{
		$formTag  = '';
		$this->_user->id ? $name = $this->_user->name : $name = NAME_FOR_ANONYMOUS_USERS;

		//
		// FIX MEEEEE!!
		//
		$article->id = 0;
		$article->readmore_link = 'How do we get this in here?';

		if ( $submitViaAjax )
		{
			$action = '#';
			$contentid  = $article->id;
			$created_by = $this->_user->id;
			if ( 0 < $this->_user->id )
			{
				$published = 1;
			}
			else
			{
				$published = 0;
			}
			$onclickAttr = 'onclick="return JoomooCommentsAjax.ajaxCommentIntoDb' .
				'(' . $contentid . ', ' . $gallerygroupid . ', ' . $galleryimageid . ', ' . $created_by . ', ' . $published . ');" ';
		}
		else
		{
			$action = '/index.php/joomoocomments';
			$onclickAttr = '';
		}

		$formTag .= ' <form action="' . $action . '" method="post" class="joomoocomment_form">' . "\n";
		//	$formTag .= '  <fieldset class="joomoocomment_form">' . "\n";
		$formTag .= '    <input type="text" id="joomoocomment_name" ';
		$formTag .=       'value="' . $name . '" class="joomoocomment_form" ';
		$formTag .=       'disabled="disabled" readonly="readonly" /><br />' . "\n";
		$formTag .= '    <textarea id="joomoocomment_text" name="text" class="joomoocomment_form" ';
		$formTag .=       'rows="' . POSTING_COMMENT_ROWS . '" cols="' . POSTING_COMMENT_COLUMNS . '">';
		$formTag .=       '</textarea>' . "\n";
		$formTag .= '    <center>' . "\n";
		$formTag .= '    <input id="submit_joomoocomment" class="submit_joomoocomment" type="submit" name="post_comment" ';
		$formTag .=       $onclickAttr . ' value="Post Comment" />' . "\n";
		$formTag .= '    </center>' . "\n";
		//	$formTag .= '  </fieldset>' . "\n";

		if ( ! $submitViaAjax )
		{
			$formTag .= '<input type="hidden" name="task" value="post_comment" ' . "/>\n";
			$formTag .= '<input type="hidden" name="contentid" value="' . $article->id . '" ' . "/>\n";
			$formTag .= '<input type="hidden" name="readmore_link" value="' . $article->readmore_link . "\" />\n";
		}

		$formTag .= ' </form>' . "\n";

		return $formTag;
	}
}
