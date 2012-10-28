<?php
/**
 * @package		Joomla
 * @subpackage	Content
 * @copyright	Copyright (C) 2010 - Tom Hartung.  All rights reserved.
 * @license		TBD.
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );

// // This is a ContentViewFrontpage object
// $this_class = get_class( $this );
// print "Loading plgContentJoomoocomments.php: this class = " . $this_class . "<br />\n";

/**
 * Plugin for Joomoo Comments
 *
 * @package		Joomla
 * @subpackage	Content
 * @since 		1.5
 */
class plgContentJoomoocomments extends JPlugin
{
	/**
	 * placeholder regular expression indicating we want to allow comments to this article
	 * value is JOOMOO_COMMENTS_PLACEHOLDER ensconced in delimiters; JOOMOO_COMMENTS_PLACEHOLDER defined in
	 *    components/com_joomoocomments/assets/constants.php
	 * @access private
	 * @var string
	 * @note we use a <br ...> tag so if plugin is missing or disabled we just get some whitespace
	 */
	private $_placeholderRegEx;
	/**
	 * User currently logged in - or not
	 * @var JUser Object
	 */
	private $_user;
	/**
	 * TRUE if current user can post comments else FALSE
	 * @var boolean
	 */
	private $_thisUserCanComment = FALSE;
	/**
	 * text we append to post
	 * @var string
	 */
	private $_appendText;
	/**
	 * id of article in content table, or 0 if not processing content
	 * @var int
	 */
	private $_contentid = 0;
	/**
	 * id of group row in joomoo gallerygroups table, or 0 if not processing a gallery group
	 * @var int
	 */
	private $_gallerygroupid = 0;
	/**
	 * id of image row in joomoo galleryimages table, or 0 if not processing a gallery image
	 * @var int
	 */
	private $_galleryimageid = 0;
	/**
	 * AND clause (for query) based on whether contentid, gallerygroupid, or galleryimageid is set
	 * @var string
	 */
	private $_andClause = null;
	/**
	 * query string used to get the comments
	 * made a member variable so we can see it if something goes wrong - see _debugSeeCommentRows()
	 * @var string
	 */
	private $_query;
	/**
	 * form number - to support multiple comment forms per page
	 * @var int
	 */
	private $_formNumber;

	/**
	 * If the joomoouser extension is installed, we support follow-up emails when someone posts a comment
	 * @var boolean
	 */
	private $_followUpEmailsEnabled = TRUE;
	/**
	 * Object that knows how to generate the xhtml (mostly javascript) that sends follow-up emails when a comment is posted
	 * @var GetFollowUpEmailXhtml object
	 */
	private $_getFollowUpEmailXhtmlObject = null;

	/**
	 * Constructor
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 * @param object $subject The object to observe
	 * @param object $params  The object that holds the plugin parameters
	 * @since 1.5
	 */
	public function __construct( &$subject, $params )
	{
		parent::__construct( $subject, $params );
		$this->_appendText = '';

		$document =& JFactory::getDocument();  // JDocumentHTML object
		$document->addStyleSheet( DS.'components'.DS.'com_joomoobase'.DS.'assets'.DS.'joomoobase.css' );
		$document->addStyleSheet( DS.'components'.DS.'com_joomoocomments'.DS.'assets'.DS.'joomoocomments.css' );
		$document->addScript( DS.'components'.DS.'com_joomoobase'.DS.'javascript'.DS.'myTypeOf.js' );
		$document->addScript( DS.'components'.DS.'com_joomoobase'.DS.'javascript'.DS.'JoomooRequest.js' );
		$document->addScript( DS.'components'.DS.'com_joomoocomments'.DS.'javascript'.DS.'JoomooCommentsLib.js' );
		$document->addScript( DS.'components'.DS.'com_joomoocomments'.DS.'javascript'.DS.'JoomooCommentsAjax.js' );
		$document->addScript( DS.'components'.DS.'com_joomoocomments'.DS.'javascript'.DS.'joomoocommentsMoocode.js' );

		$baseConstantsFilePath = JPATH_SITE.DS.'components'.DS.'com_joomoobase'.DS.'assets'.DS.'constants.php';
		$constantsFilePath = JPATH_SITE.DS.'components'.DS.'com_joomoocomments'.DS.'assets'.DS.'constants.php';
		require_once( $baseConstantsFilePath );
		require_once( $constantsFilePath );

		$this->_placeholderRegEx = '&' . JOOMOO_COMMENTS_PLACEHOLDER . '&';
		$this->_user = & JFactory::getUser();
		$allow_anonymous = $this->params->get('allow_anonymous', FALSE);

		if ( 0 < $this->_user->id || $allow_anonymous )
		{
			$this->_thisUserCanComment = TRUE;
		}

		//	JHTML::_('behavior.modal');     // adds link tag for modal.css and script tag for modal.js to page header
	}

	/**
	 * Add comment count, if it's the right thing to do
	 * @param 	string		The type of content being passed, eg. 'com_content.article'
	 * @param 	object		The article object.  Note $article->text is also available
	 * @param 	object		The article params
	 * @param 	int			The 'page' number
	 * @access	public
	 * @return	string
	 */
	public function onContentPrepare( $context, &$article, &$params, $page=0 )
	{
		$appendText  = '';
		$this->_setIds( $article );

		//
		// If the joomoouser extension is installed, we support the sending of follow-up emails when a comment is posted
		//
		$getFollowUpEmailXhtmlClassPath = JPATH_SITE .DS. 'components' .DS. 'com_joomoouser' .DS. 'classes' .DS. 'getFollowUpEmailXhtml.php';
		if ( file_exists($getFollowUpEmailXhtmlClassPath) )
		{
			$this->_followUpEmailsEnabled = TRUE;
			require_once $getFollowUpEmailXhtmlClassPath;
			$this->_getFollowUpEmailXhtmlObject = new GetFollowUpEmailXhtml();
		}
		else
		{
			$this->_followUpEmailsEnabled = FALSE;
		}

		// print '<br />article: ' . print_r($article,true) . '<br />';
		//
		// article->text - what the system is going to print now
		//     it equals either the introtext, fulltext, or introtext + fulltext
		// article->introtext - text the user sees before clicking on the read more link
		// article->fulltext - text the user sees only after clicking on the read more link
		//
		$comment_count_text = $this->params->get( 'comment_count_text', '' );
		if ( $comment_count_text != 'omit' )
		{
			$placeholderInText = preg_match( $this->_placeholderRegEx, $article->text );
			isset($article->fulltext) ?
				$placeholderInFulltext = preg_match( $this->_placeholderRegEx, $article->fulltext ) :
				$placeholderInFulltext = FALSE;
			if ( $placeholderInFulltext && ! $placeholderInText )   // placeholder in article but not in text being printed now
			{
				$commentRowCount = $this->_getCommentRowCount( );
				$commentRowCount == 1 ? $commentOrComments = 'comment' : $commentOrComments = 'comments';
				$ccRegEx = '&%cc%&';
				if ( preg_match($ccRegEx,$comment_count_text) )
				{
					$replacementString = $commentRowCount . ' ' . $commentOrComments . '.';
					$commentCountString = preg_replace($ccRegEx, $replacementString, $comment_count_text);
				}
				else
				{
					$commentCountString = $comment_count_text . $commentRowCount . ' ' . $commentOrComments . '.';
				}
				$appendText .= $commentCountString;
			}
		}

		$article->text .= $appendText;
	}
	//
	// -------------------------------------------------------------------
	// private utility functions to support getting data from the database
	// -------------------------------------------------------------------
	//
	/**
	 * get the number of comments for this article
	 * @access private
	 * @return string number of comments
	 */
	private function _getCommentRowCount( )
	{
		$db =& JFactory::getDBO();
		$andClause = $this->_getAndClause( $db );

		$this->_query = 'SELECT count(*) FROM ' . $db->nameQuote('#__joomoocomments') .
		         ' WHERE ' . $db->nameQuote('published') . ' = 1' . $andClause;
		$db->setQuery( $this->_query );
		$rowCount = $db->loadResult();

		return $rowCount;
	}
	/**
	 * get the rows of comments for this article
	 * @access private
	 * @return list of objects containing comments
	 */
	private function _getCommentRows()
	{
		$db =& JFactory::getDBO();
		$andClause = $this->_getAndClause( $db );

		$this->_query = 'SELECT * FROM ' . $db->nameQuote('#__joomoocomments') .
		         ' WHERE ' . $db->nameQuote('published') . ' = 1 ' . $andClause .
		         ' ORDER BY ' . $db->nameQuote('created');
		$db->setQuery( $this->_query );
		$rows = $db->loadObjectList();

		return $rows;
	}
	/**
	 * get AND clause (for WHERE clause of queries) based on which id is set
	 * @access private
	 * @return string containing 'AND ...'
	 */
	private function _getAndClause( $db )
	{
		if ( $this->_andClause == null )
		{
			if ( $this->_contentid != 0 )
			{
				$this->_andClause = ' AND ' . $db->nameQuote('contentid') . ' = ' . $this->_contentid;
			}
			else if ( $this->_gallerygroupid != 0 )
			{
				$this->_andClause = ' AND ' . $db->nameQuote('gallerygroupid') . ' = ' . $this->_gallerygroupid;
			}
			else if ( $this->_galleryimageid != 0 )
			{
				$this->_andClause = ' AND ' . $db->nameQuote('galleryimageid') . ' = ' . $this->_galleryimageid;
			}
			else
			{
				$this->_andClause = '';   // should not occur!
			}
		}

		return $this->_andClause;
	}

	//
	// --------------------------------------------------------------------------------------------
	// Main driver method that prints the comments, changing the article just before it's displayed
	// --------------------------------------------------------------------------------------------
	// It is followed by the private methods it uses
	//
	/**
	 * If placeholder is present, this before display content method appends comments and form to article text
	 * @param 	string		The type of content being passed, eg. 'com_content.article'
	 * @param 	object		The article object.  Note $article->text is also available
	 * @param 	object		The article params
	 * @param 	int			The 'page' number
	 * @access	public
	 * @return	string
	 */
	public function onContentBeforeDisplay( $context, &$article, &$params=null, $page=0 )
	{
		$this->_appendText = '';
		$this->_andClause = null;
		isset($article->formNumber) ? $this->_formNumber = $article->formNumber : $this->_formNumber = 0;
		$formNumber = $this->_formNumber;
		$this->_setIds( $article );
		$returnText = '';

		//	$returnText .= '<br />article:<br />' . print_r($article,true) . '<br />';
		//	$returnText .= '<br />this->_contentid = ' . $this->_contentid . '<br />';
		//	$returnText .= '<br />this->_gallerygroupid = ' . $this->_gallerygroupid . '<br />';
		//	$this->_appendText .= '<br />this->_contentid = ' . $this->_contentid . '<br />';
		//	$this->_appendText .= 'this->_gallerygroupid = ' . $this->_gallerygroupid . '<br />';
		//	$this->_appendText .= 'this->_galleryimageid = ' . $this->_galleryimageid . '<br />';

		//
		// Based on all_articles param (set in back end) either we allow comments for all articles
		// or we check for the place-holder
		//
		$all_articles = $this->params->get( 'all_articles', FALSE );
		$result = FALSE;
		if ( $all_articles )
		{
			$commentsOkForThisArticle = TRUE;
		}
		else
		{
			$result = preg_match( $this->_placeholderRegEx, $article->text );
			if ( $result )
			{
				$commentsOkForThisArticle = TRUE;
				$article->text = preg_replace( $this->_placeholderRegEx, '', $article->text );
			}
		}

		//	$returnText .= '<br />commentsOkForThisArticle = ' . $commentsOkForThisArticle . '<br />';
		//	$returnText .= '<br />all_articles = ' . $all_articles . '<br />';
		//	$returnText .= '<br />result = ' . $result . '<br />';
		//	$returnText .= '<br />this->_placeholderRegEx = ' . $this->_placeholderRegEx . '<br />';
		if ( isset($commentsOkForThisArticle) && $commentsOkForThisArticle )
		{
			$this->_appendText .= '<div class="all_joomoocomments" id="all_joomoocomments">' . "\n";
			$this->_appendText .= ' <h4>Comments</h4>' . "\n";
			$this->_appendText .= '  <noscript>' . "\n";
			$this->_appendText .= '   <div class="center">' . "\n";
			$this->_appendText .= '    <p class="joomoocomment_js_off">' . "\n";
			$this->_appendText .=       'For best results, please enable javascript in your browser\'s options.</p>' . "\n";
			$this->_appendText .= '   </div>' . "\n";
			$this->_appendText .= '  </noscript>' . "\n";
			$this->_appendText .= $this->_getXhtmlComments( $article );
			$this->_appendText .= ' <div id="new_joomoocomment_' . $formNumber . '_0"></div>' . "\n";
			if ( $this->_thisUserCanComment )
			{
				$this->_appendText .= $this->_exportValuesToJavascript();
				$ajax_or_full = $this->params->get( 'ajax_or_full', JOOMOO_USE_AJAX_OR_FULL );
				if ( $ajax_or_full == JOOMOO_USE_FULL_ONLY )
				{
					$this->_appendText .= $this->_getXhtmlCommentForm( $article );
				}
				else
				{
					$this->_appendText .= $this->_getAjaxCommentForm( $article );
				}
			}
			else
			{
				$this->_appendText .= '<div class="joomoocomment_login_message">';
				$this->_appendText .= ' <h4>Log in to leave your comment</h4>' . "\n";
				$this->_appendText .= '</div>' . "\n";
			}
			$this->_appendText .= '</div>' . "\n";
			$this->_contentid > 0 ?                      // when processing a content article
				$article->text .= $this->_appendText :   //    append comments to article
				$returnText = $this->_appendText;        // else return the comments
		}

		return $returnText;
	}

	/**
	 * set the *id member variables: contentid, gallerygroupid, and galleryimageid
	 * @access private
	 * @return void
	 */
	private function _setIds( $article )
	{
		if ( isset($article->gallerygroupid) && 0 < $article->gallerygroupid )
		{
			$this->_contentid = 0;
			$this->_gallerygroupid = $article->gallerygroupid;
			$this->_galleryimageid = 0;
		}
		else if ( isset($article->galleryimageid) && 0 < $article->galleryimageid )
		{
			$this->_contentid = 0;
			$this->_gallerygroupid = 0;
			$this->_galleryimageid = $article->galleryimageid;
		}
		else if ( isset($article->id) && 0 < $article->id )
		{
			$this->_contentid = $article->id;
			$this->_gallerygroupid = 0;
			$this->_galleryimageid = 0;
		}
		else
		{
			$this->_contentid = 0;
			$this->_gallerygroupid = 0;
			$this->_galleryimageid = 0;
		}
	}
	/**
	 * export constants and other values needed to validate the form to javascript
	 * @access private
	 * @return string html containing values
	 */
	private function _exportValuesToJavascript( )
	{
		$max_consecutive_comments = $this->params->get( 'max_consecutive_comments', 5 );
		is_numeric($max_consecutive_comments) ?
			$maxConsecutiveString = 'parseInt(' . $max_consecutive_comments . ')' :
			$maxConsecutiveString = 'Infinity' . "\n";

		$email_on_form    = $this->params->get( 'email_on_form', OPTIONAL_FIELD );
		$website_on_form  = $this->params->get( 'website_on_form', OPTIONAL_FIELD );
		$spam_flag_email  = $this->params->get( 'spam_flag_email', 1 );
		$log_ips          = $this->params->get( 'log_ips', ANONYMOUS_USERS );

		$values = '';
		$values .= '<script type="text/javascript">' . "\n";
		$values .= ' //<![CDATA[ ' . "\n";
		$values .= '  JoomooCommentsAjax.max_consecutive_comments = ' . $maxConsecutiveString . ';' . "\n";
		$values .= '  JoomooCommentsLib.email_on_form = "' . $email_on_form . '";' . "\n";
		$values .= '  JoomooCommentsLib.website_on_form = "' . $website_on_form . '";' . "\n";

		$values .= '  JoomooCommentsLib.spam_flag_email = "' . $spam_flag_email . '";' . "\n";
		if ( $spam_flag_email )
		{
			$document =& JFactory::getDocument();
			$base = $document->getBase();          // url of the current page
			if ( 0 < $this->_galleryimageid )
			{
				$base .= '?option=com_joomoogallery&view=joomoogalleryimage&id=' . $this->_galleryimageid;
			}
			$values .= '  JoomooCommentsLib.base = "' . $base . '";' . "\n";
		}

		$log_ips == ALL_USERS || ( $this->_user->id == 0 && $log_ips == ANONYMOUS_USERS ) ?
			$values .= '  JoomooCommentsLib.ip_address = "' . $_SERVER['REMOTE_ADDR'] . '";' . "\n" :
			$values .= '  JoomooCommentsLib.ip_address = "";' . "\n";
		$values .= '  JoomooCommentsLib.MINIMUM_COMMENT_LENGTH = ' . MINIMUM_COMMENT_LENGTH . ';' . "\n";
		$values .= '  JoomooCommentsLib.MAXIMUM_COMMENT_LENGTH = ' . MAXIMUM_COMMENT_LENGTH . ';' . "\n";
		$values .= '  JoomooCommentsLib.REQUIRED_FIELD = "' . REQUIRED_FIELD . '";' . "\n";
		$values .= '  JoomooCommentsLib.OPTIONAL_FIELD = "' . OPTIONAL_FIELD . '";' . "\n";
		$values .= '  JoomooCommentsLib.OMIT_FIELD = "' . OMIT_FIELD . '";' . "\n";
		$values .= ' //]]>' . "\n";
		$values .= '</script>' . "\n";

		return $values;
	}
	/**
	 * get the comments and return html containing them
	 * @access private
	 * @return string html containing comments
	 */
	private function _getXhtmlComments( $article )
	{
		$commentsXhtml  = '';
		$commentRows = $this->_getCommentRows( );
		$formNumber = $this->_formNumber;
		$commentRowCount = count( $commentRows );
		$first_last = $this->params->get( 'first_last', 'all' );
		$showingFirstOrLast = substr( $first_last, 0, 1 );

		if ( $first_last == 'all' )
		{
			$numberToHide = 0;
			$numberToShow = $commentRowCount;
		}
		else
		{
			$numberToShow = substr( $first_last, 1, 2 ) + 0;
			$minimum_to_hide = $this->params->get( 'minimum_to_hide', 3 );    // seems silly to just hide one or two
			if ( $numberToShow <= $commentRowCount - $minimum_to_hide )
			{
				$numberToHide = $commentRowCount - $numberToShow;
			}
			else
			{
				$numberToHide = 0;
				$numberToShow = $commentRowCount;
			}
		}

		//	$commentsXhtml .= $this->_debugSeeCommentRows( $commentRows );
		//	$commentsXhtml .= 'commentRowCount = ' . $commentRowCount . "<br />\n";
		//	$commentsXhtml .= 'first_last = ' . $first_last . "<br />\n";
		//	$commentsXhtml .= 'showingFirstOrLast = ' . $showingFirstOrLast . "<br />\n";
		//	$commentsXhtml .= 'numberToShow = ' . $numberToShow . "<br />\n";
		//	$commentsXhtml .= 'numberToHide = ' . $numberToHide . "<br />\n";
		//
		// Use javascript to write the links to show the hidden comments because
		// if javascript is off the comments will not be hidden
		//
		if ( 0 < $numberToHide && $showingFirstOrLast == 'l' )     // showing only the 'l'ast X comments
		{
			$commentsXhtml .= ' <script type="text/javascript">' . "\n";
			$commentsXhtml .= ' //<![CDATA[ ' . "\n";
			$commentsXhtml .= '  JoomooCommentsLib.hiddenCommentsTopLink' .
				'(' . $numberToShow . ', ' . $numberToHide . ', ' . $commentRowCount . ', ' . $formNumber . ");\n";
			$commentsXhtml .= '  JoomooCommentsLib.startHidingCommentsBeforeLoop( ' . $formNumber . ' );' . "\n";
			$commentsXhtml .= ' //]]>' . "\n";
			$commentsXhtml .= ' </script>' . "\n";
		}

		for ( $rowNum = 0; $rowNum < $commentRowCount; $rowNum++ )
		{
			if ( 0 < $numberToHide && $showingFirstOrLast == 'f' )            // showing only the 'f'irst X comments
			{
				if ( $rowNum == $numberToShow )
				{
					$commentsXhtml .= ' <script type="text/javascript">' . "\n";
					$commentsXhtml .= ' //<![CDATA[ ' . "\n";
					$commentsXhtml .= '  JoomooCommentsLib.startHidingCommentsInLoop( ' . $formNumber . ' );' . "\n";
					$commentsXhtml .= ' //]]>' . "\n";
					$commentsXhtml .= ' </script>' . "\n";
				}
			}
			$commentRow = $commentRows[$rowNum];
			$commentsXhtml .= $this->_getXhtmlForACommentRow( $commentRow, $article );
			if ( 0 < $numberToHide && $showingFirstOrLast == 'l' )       // showing only the 'l'ast X comments
			{
				if ( $rowNum == ($numberToHide - 1) )
				{
					$commentsXhtml .= ' <script type="text/javascript">' . "\n";
					$commentsXhtml .= ' //<![CDATA[ ' . "\n";
					$commentsXhtml .= '  JoomooCommentsLib.endHidingCommentsInLoop( ' . $formNumber . ' );' . "\n";
					$commentsXhtml .= ' //]]>' . "\n";
					$commentsXhtml .= ' </script>' . "\n";
				}
			}
		}

		//
		// Use javascript to write the links to show the hidden comments because
		// if javascript is off the comments will not be hidden
		//
		if ( 0 < $numberToHide && $showingFirstOrLast == 'f' )    // showing only the 'f'irst X comments
		{
			$commentsXhtml .= ' <script type="text/javascript">' . "\n";
			$commentsXhtml .= ' //<![CDATA[ ' . "\n";
			$commentsXhtml .= '  JoomooCommentsLib.endHidingCommentsAfterLoop( ' . $formNumber . ' );' . "\n";
			$commentsXhtml .= '  JoomooCommentsLib.hiddenCommentsBottomLink' .
				'(' . $numberToShow . ', ' . $numberToHide . ', ' . $commentRowCount . ', ' . $formNumber . ");\n";
			$commentsXhtml .= ' //]]>' . "\n";
			$commentsXhtml .= ' </script>' . "\n";
		}

		if ( $this->_followUpEmailsEnabled )
		{
			$followUpEmailXhtml = $this->_getFollowUpEmailXhtmlObject->getFollowUpEmailXhtml( $article, $commentRows );
			// print '<br />this->_followUpEmailsEnabled = "' . $this->_followUpEmailsEnabled . '"<br />';
			// print '<br />followUpEmailXhtml: ' . $followUpEmailXhtml . '<br />';
			$commentsXhtml .= $followUpEmailXhtml . "\n";
		}
		else
		{
			$commentsXhtml .= ' <script type="text/javascript">' . "\n";
			$commentsXhtml .= ' //<![CDATA[ ' . "\n";
			$commentsXhtml .= '  JoomooCommentsAjax._followUpEmailsEnabled = false;' .
			$commentsXhtml .= ' //]]>' . "\n";
			$commentsXhtml .= ' </script>' . "\n";
		}

		return $commentsXhtml;
	}
	/**
	 * get the xhtml for a single comment
	 * @access private
	 * @return string html containing comments
	 * @note the matching javascript function - that you will probably also want to change - is
	 *   _displayNewComment() in components/com_joomoocomments/javascript/JoomooCommentsAjax.js
	 */
	private function _getXhtmlForACommentRow( $commentRow, $article )
	{
		$ajax_or_full = $this->params->get( 'ajax_or_full', JOOMOO_USE_AJAX_OR_FULL );

		if ( isset($commentRow->website) && 0 < strlen($commentRow->website) )
		{
			$nameSiteDate = $commentRow->name . '&nbsp;(' .
				'<a href="http://' . $commentRow->website . '" target="_blank">' .
				  'http://' . $commentRow->website . '</a>)&nbsp;&mdash;&nbsp;' . $commentRow->created . ':';
		}
		else
		{
			$nameSiteDate = $commentRow->name . '&nbsp;&mdash;&nbsp;' . $commentRow->created . ':';
		}

		$xhtmlForAComment  = '';
		$xhtmlForAComment .= ' <div id="joomoocomment_' . $commentRow->id . '" class="a_joomoocomment">' . "\n";
		//	$xhtmlForAComment .= 'ajax_or_full = ' . $ajax_or_full . "<br />\n";

		if ( ( $commentRow->created_by != 0 && $commentRow->created_by == $this->_user->id ) ||
				MINIMUM_GID_TO_DELETE_ANY <= $this->_user->gid )
		{
			$id = $commentRow->id;
			$readmore_link = $article->readmore_link;
			$noJavascriptDeleteLink = '<a ' .
				'href="/index.php/joomoocomments?task=delete&amp;readmore_link=' . $readmore_link . '&amp;id=' . $id . '" ' .
				'title="Delete this comment" onclick="return confirm(\'Are you sure you want to delete this comment?\');">' .
				TEXT_FOR_DELETE_LINK . '</a>';
			if ( $ajax_or_full == JOOMOO_USE_FULL_ONLY )
			{
				$deleteLink = $noJavascriptDeleteLink . "\n";
			}
			else
			{
				$deleteLink = '';
				$deleteLink .= '<script type="text/javascript">' . "\n";
				$deleteLink .= ' //<![CDATA[ ' . "\n";
				$deleteLink .= '  document.write( \'<a href="#" title="Delete this comment" ' .
					'onclick="return JoomooCommentsAjax.deleteFromDatabase(' . $id . ');">' .
					TEXT_FOR_DELETE_LINK . '</a>' . "');\n";
				$deleteLink .= ' //]]>' . "\n";
				$deleteLink .= '</script>' . "\n";
				if ( $ajax_or_full == JOOMOO_USE_AJAX_ONLY )
				{
					$deleteLink .= '<noscript>';
					$deleteLink .= 'To delete comments you must enable javascript.';
					$deleteLink .= '</noscript>' . "\n";
				}
				else
				{
					$deleteLink .= '<noscript>' . "\n";
					$deleteLink .= ' ' . $noJavascriptDeleteLink . "\n";
					$deleteLink .= '</noscript>' . "\n";
				}
			}
			$xhtmlForAComment .= '  <table class="joomoocomment_heading"><tr>' . "\n";
			$xhtmlForAComment .= '   <td class="small underline">' . $nameSiteDate . '</td>' . "\n";
			$xhtmlForAComment .= '   <td class="joomoocomment_delete_link">' . $deleteLink . "</td>\n";
			$xhtmlForAComment .= '  </tr></table>' . "\n";
		}
		else
		{
			$xhtmlForAComment .= '  <p class="joomoocomment_heading">' . "\n";
			$xhtmlForAComment .= '   <span class="small underline">' . $nameSiteDate . '</span>' . "\n";
			$xhtmlForAComment .= '  </p>' . "\n";
		}

		$xhtmlForAComment .= '  <p class="joomoocomment_body">';
		$xhtmlForAComment .= $commentRow->text . '</p>' . "\n";

		//
		// Voting is enabled only when it's OK to use Ajax
		//
		if ( $ajax_or_full != JOOMOO_USE_FULL_ONLY )
		{
			$id = $commentRow->id;
			$likes = $commentRow->likes;
			$dislikes = $commentRow->dislikes;
			$spam = $commentRow->spam;
			$spam == 1 ? $timeOrTimes = 'time' : $timeOrTimes = 'times';

			$xhtmlForAComment .= '  <script type="text/javascript">' . "\n";
			$xhtmlForAComment .= '   //<![CDATA[ ' . "\n";
			$xhtmlForAComment .= '    JoomooCommentsAjax.writeVotingLinks(' . $id . ',' . $likes . ',' . $dislikes . ',' . $spam . ');' . "\n";
			$xhtmlForAComment .= '   //]]>' . "\n";
			$xhtmlForAComment .= '  </script>' . "\n";
			$xhtmlForAComment .= '  <noscript>' . "\n";
			$xhtmlForAComment .= '  <div>' . "\n";
			$xhtmlForAComment .= '    <span class="joomoocomment_vote">';
			$xhtmlForAComment .=       'Likes:&nbsp;' . $likes . "</span>\n";
			$xhtmlForAComment .= '    <span class="joomoocomment_vote">';
			$xhtmlForAComment .=       'Dislikes:&nbsp;' . $dislikes . "</span>\n";
			$xhtmlForAComment .= '    <span class="joomoocomment_vote">';
			$xhtmlForAComment .=       'Flagged as spam:&nbsp;' . $spam . '&nbsp;' . $timeOrTimes . '</span>' . "\n";
			$xhtmlForAComment .= '    <p class="joomoocomment_js_off">';
			$xhtmlForAComment .=       "To vote on comments you must enable javascript in your browser's options.</p>\n";
			$xhtmlForAComment .= '   </div>' . "\n";
			$xhtmlForAComment .= '  </noscript>' . "\n";
		}

		$xhtmlForAComment .= ' </div>' . "\n";

		return $xhtmlForAComment;
	}

	/**
	 * assembles javascript allowing user to use ajax to leave a comment
	 * @access private
	 * @return string
	 */
	private function _getAjaxCommentForm( $article )
	{
		$formNumber = $this->_formNumber;
		$formAjax  = '';

		//
		// This form uses the mootools Fx.Slider class to remain hidden until user clicks the link
		//
		$formAjax .= '<div id="joomoocomment_ajax_form_' . $formNumber . '" class="joomoocomment_ajax_form">' . "\n";
		$formAjax .= ' <h4>Enter a Comment</h4>' . "\n";
		$formAjax .= $this->_getFormTag( $article );
		$formAjax .= '<br />' . "\n";                   // hack: only seeing the top part of the submit button
		$formAjax .= '</div>' . "\n";

		$formAjax .= '<script type="text/javascript">' . "\n";
		$formAjax .= ' //<![CDATA[ ' . "\n";
		$formAjax .= '  JoomooCommentsAjax.TEXT_FOR_DELETE_LINK = "' . TEXT_FOR_DELETE_LINK . '";' . "\n";  // export constants
		$formAjax .= '  JoomooCommentsAjax.COMMENT_SAVED_OK = "' . COMMENT_SAVED_OK . '";' . "\n";
		$formAjax .= '  JoomooCommentsAjax.COMMENT_DELETED_OK = "' . COMMENT_DELETED_OK . '";' . "\n";
		$formAjax .= '  JoomooCommentsAjax.COMMENT_UPDATED_OK = "' . COMMENT_UPDATED_OK . '";' . "\n";
		$formAjax .= '  JoomooCommentsAjax.COMMENTS_RESPONSE_DELIMITER = "' . COMMENTS_RESPONSE_DELIMITER . '";' . "\n";
		$formAjax .= '  JoomooCommentsAjax.ajax_or_full  = "' . $this->params->get('ajax_or_full', JOOMOO_USE_AJAX_OR_FULL)  . '";' . "\n";
		$formAjax .= '  JoomooCommentsAjax.JOOMOO_USE_AJAX_ONLY = "' . JOOMOO_USE_AJAX_ONLY . '";' . "\n";
		$formAjax .= '  JoomooCommentsAjax.JOOMOO_USE_FULL_ONLY = "' . JOOMOO_USE_FULL_ONLY . '";' . "\n";
		$formAjax .= '  JoomooCommentsAjax.JOOMOO_USE_AJAX_OR_FULL = "' . JOOMOO_USE_AJAX_OR_FULL . '";' . "\n";

		$readmore_link = isset($article->readmore_link) ? $article->readmore_link : '';
		$formAjax .= '  JoomooCommentsAjax.readmore_link = "' . $readmore_link . '";' . "\n";

		$require_captcha = $this->params->get( 'require_captcha', 'A' );
		if ( $require_captcha == ALL_USERS || ( $require_captcha == ANONYMOUS_USERS && 0 == $this->_user->id ) )
		{
			$formAjax .= '  JoomooCommentsAjax.captcha_required = "1";' . "\n";
			$formAjax .= '  JoomooCommentsAjax.captcha_type     = "' . $this->params->get( 'captcha_type', 'O' )     . '";' . "\n";
			$formAjax .= '  JoomooCommentsAjax.CAPTCHA_TYPE_RECAPTCHA = "' . CAPTCHA_TYPE_RECAPTCHA . '";' . "\n";
			$formAjax .= '  JoomooCommentsAjax.CAPTCHA_TYPE_OPENCAPTCHA = "' . CAPTCHA_TYPE_OPENCAPTCHA . '";' . "\n";
		}

	//	$formAjax .= '  JoomooCommentsLib.modalFormLink(' . $formNumber . ');' . "\n";      // UNUSED - for possible future reference
	//	$formAjax .= '  JoomooCommentsLib.hiddenFormLink(' . $formNumber . ');' . "\n";
		$formAjax .= '  JoomooCommentsAjax.writeAjaxLog();' . "\n";   // Used for results of ajax request
	//	$formAjax .= '  JoomooRequest.writeDebugDivs();' . "\n";      // for debugging only (eg. ajax events etc.)
		$formAjax .= ' //]]>' . "\n";
		$formAjax .= '</script>' . "\n";

		return $formAjax;
	}
	/**
	 * assembles xhtml containing the comment form
	 * @access private
	 * @return string xhtml containing comment form
	 */
	private function _getXhtmlCommentForm( $article )
	{
		$formXhtml  = '';

		$formXhtml .= '<div class="joomoocomment_xhtml_form">' . "\n";
		$formXhtml .= ' <h4>Enter a Comment</h4>' . "\n";
		$formXhtml .= $this->_getFormTag( $article );
		$formXhtml .= '</div>' . "\n";

		return $formXhtml;
	}
	/**
	 * assembles xhtml containing the comment form
	 * @access private
	 * @return string xhtml containing comment form
	 */
	private function _getFormTag( $article )
	{
		$formTag  = '';
		$formNumber = $this->_formNumber;
		$created_by = $this->_user->id;
		$created_by ? $name = $this->_user->name : $name = NAME_FOR_ANONYMOUS_USERS;
		$email_on_form   = $this->params->get( 'email_on_form', OPTIONAL_FIELD );
		$website_on_form = $this->params->get( 'website_on_form', OPTIONAL_FIELD );
		$ajax_or_full    = $this->params->get( 'ajax_or_full', JOOMOO_USE_AJAX_OR_FULL );
		$editable_name   = $this->params->get( 'editable_name', 0 );
		$log_ips         = $this->params->get( 'log_ips', ANONYMOUS_USERS );
		$honeypot        = $this->params->get( 'honeypot', 1 );
		$require_captcha = $this->params->get( 'require_captcha', 'A' );
		$readmore_link = isset($article->readmore_link) ? htmlspecialchars($article->readmore_link) : '';

		if ( $ajax_or_full == JOOMOO_USE_FULL_ONLY )
		{
			$action = '/index.php/joomoocomments';
			$onclickAttr = 'onclick="return JoomooCommentsLib.validateForm(' . $created_by . ', ' . $formNumber . ');" ';
		}
		else
		{
			$action = '#';
			$autopub_anonymous = $this->params->get( 'autopub_anonymous', 0 );
			if ( 0 < $this->_user->id || $autopub_anonymous )
			{
				$published = 1;
			}
			else
			{
				$published = 0;
			}
			$onclickAttr = 'onclick="return JoomooCommentsAjax.ajaxCommentIntoDb' .
				'(' . $this->_contentid . ',' . $this->_gallerygroupid . ', ' . $this->_galleryimageid . ', ' .
				  $created_by . ',' . $published . ',' . $formNumber . ');" ';
		}

		//
		// Just because we want to use ajax doesn't mean user has javascript enabled in their browser
		//  and we can have only one opening form tag...
		//
		$formTag .= ' <div class="joomoocomment_form">' . "\n";
		$formTag .= '  <script type="text/javascript">' . "\n";
		$formTag .= '   //<![CDATA[ ' . "\n";
		$formTag .= '    document.write( \'<form name="joomoocomment_form_' . $formNumber . '" ' .
		                   'action="' . $action . '" method="post" class="joomoocomment_form"> \');' . "\n";
		$formTag .= '   //]]>' . "\n";
		$formTag .= '  </script>' . "\n";

		if ( $ajax_or_full == JOOMOO_USE_AJAX_ONLY )
		{
			$formTag .= '  <noscript>' . "\n";
			$formTag .= '    <p class="joomoocomment_js_off">';
			$formTag .=       "To submit a comment you must enable javascript in your browser's options.</p>\n";
			$formTag .= '  </noscript>' . "\n";
		}
		else
		{
			$formTag .= '  <noscript>' . "\n";
			$formTag .= '   <form name="noscript_joomoocomment_form_' . $formNumber . '" ' .
		                  	'action="/index.php/joomoocomments" method="post" class="joomoocomment_form">' . "\n";
			$formTag .= '  </noscript>' . "\n";
		}

		//	$formTag .= '  <fieldset class="joomoocomment_form">' . "\n";
		$inputNameId = 'joomoocomment_name_' . $formNumber;
		$formTag .= '  <div>' . "\n";
		if ( $editable_name )
		{
			$formTag .= '   <label for="' . $inputNameId . '">Name:&nbsp;' . "\n";
			$formTag .= '    <input id="' . $inputNameId . '" type="text" name="name" value="' . $name . '" />' . "\n";
			$formTag .= '   </label>' . "\n";
		}
		else
		{
			$formTag .= '   Name:&nbsp<span class="underline">' . $name . '</span>' . "\n";
			$formTag .= '   <input id="' . $inputNameId . '" type="hidden" name="name" value="' . $name . '" />' . "\n";
		}
		$formTag .= '  </div>' . "\n";

		if ( ! $this->_user->id && $email_on_form != OMIT_FIELD )
		{
			$inputEmailId = 'joomoocomment_email_' . $formNumber;
			$formTag .= '    <div><label for="' . $inputEmailId . '">Email:' . "\n";
			$email_on_form == REQUIRED_FIELD ? $formTag .= '*&nbsp;' : $formTag .= '';
			$formTag .= '     <input type="text" name="email" id="' . $inputEmailId . '" class="joomoocomment_form" />';
			$email_on_form == REQUIRED_FIELD ? $formTag .= '&nbsp;(required)' : $formTag .= '&nbsp;(optional)';
			$formTag .= '    </label></div>' . "\n";
		}

		if ( $website_on_form != OMIT_FIELD )
		{
			$inputWebsiteId = 'joomoocomment_website_' . $formNumber;
			$formTag .= '    <div><label for="' . $inputWebsiteId . '">Website:' . "\n";
			$website_on_form == REQUIRED_FIELD ? $formTag .= '*&nbsp;' : $formTag .= '';
			$formTag .= '     <input type="text" name="website" id="' . $inputWebsiteId . '" class="joomoocomment_form" />';
			$website_on_form == REQUIRED_FIELD ? $formTag .= '&nbsp;(required)' : $formTag .= '&nbsp;(optional)';
			$formTag .= '    </label></div>' . "\n";
		}

		$inputCommentId = 'joomoocomment_text_' . $formNumber;
		$formTag .= '    <div><label for="' . $inputCommentId . '">Comment:<br />' . "\n";
		$formTag .= '    <textarea id="' . $inputCommentId . '" name="text" class="joomoocomment_form" ';
		$formTag .=       'rows="' . POSTING_COMMENT_ROWS . '" cols="' . POSTING_COMMENT_COLUMNS . '">';
		$formTag .=       '</textarea>' . "\n";
		$formTag .= '    </label></div>' . "\n";

		//
		// The assumption is that spam bots will see the input field and fill it in
		// The trick is that we set the display style of this div to hidden, bwahahahah!
		// And we are uber-devious in that if they figure out they shouldn't fill in this value,
		//    then we can chage the name of it by editing the constants.php file.
		//
		if ( $honeypot )
		{
			$inputHpId = 'joomoocomment_' . HONEYPOT_FIELD_NAME . '_' . $formNumber;
			$formTag .= '    <div id="joomoocomment_hp">' . "\n";
			$formTag .= '     <label for="' . $inputHpId . '">' . HONEYPOT_FIELD_LABEL . "\n";
			$formTag .= '      <input type="text" name="' . HONEYPOT_FIELD_NAME . '" ' .
				'id="' . $inputHpId . '" class="joomoocomment_form" />' . "\n";
			$formTag .= '    </label></div>' . "\n";
		}

		if ( $require_captcha == ALL_USERS || ( $require_captcha == ANONYMOUS_USERS && 0 == $this->_user->id ) )
		{
			$formTag .= $this->_getXhtmlForCaptcha();
			$formTag .= '  <input type="hidden" name="captcha_required" value="1" />' . "\n";
		}
		else
		{
			$formTag .= '  <input type="hidden" name="captcha_required" value="0" />' . "\n";
		}

		$inputSubmitId = 'joomoocomment_text_' . $formNumber;
		$formTag .= '    <center>' . "\n";
		$formTag .= '     <input id="submit_joomoocomment" class="submit_joomoocomment" type="submit" name="post_comment" ';
		$formTag .=        $onclickAttr . ' value="Post Your Comment" />' . "\n";
		$formTag .= '    </center>' . "\n";
		//	$formTag .= '  </fieldset>' . "\n";

		$formTag .= '  <input type="hidden" name="task" value="post_comment" />' . "\n";
		$formTag .= '  <input type="hidden" name="contentid" value="' . $this->_contentid . '" />' . "\n";
		$formTag .= '  <input type="hidden" name="gallerygroupid" value="' . $this->_gallerygroupid . '" />' . "\n";
		$formTag .= '  <input type="hidden" name="galleryimageid" value="' . $this->_galleryimageid . '" />' . "\n";
		$formTag .= '  <input type="hidden" name="readmore_link" value="' . $readmore_link . "\" />\n";
		$formTag .= '  <input type="hidden" name="email_on_form" value="' . $email_on_form . "\" />\n";
		$formTag .= '  <input type="hidden" name="website_on_form" value="' . $website_on_form . "\" />\n";
		$formTag .= '  <input type="hidden" name="check_hp" value="' . $honeypot . "\" />\n";

		if ( $log_ips == ALL_USERS || ( $this->_user->id == 0 && $log_ips == ANONYMOUS_USERS ) )
		{
			$formTag .= '     <input type="hidden" name="ip_address" value="' . $_SERVER['REMOTE_ADDR'] . '" />' . "\n";
		}

		$formTag .= ' </form>' . "\n";
		$formTag .= '</div>' . "\n";

		return $formTag;
	}
	/**
	 * get the xhtml required for captcha image and input tag
	 * @access private
	 * @return string html containing captcha image and input tag
	 */
	private function _getXhtmlForCaptcha()
	{
		$captcha_type = $this->params->get( 'captcha_type', CAPTCHA_TYPE_OPENCAPTCHA );
		$captchaFilePath = JPATH_SITE.DS.'components'.DS.'com_joomoobase'.DS.'captcha'.DS.'JoomoobaseCaptcha.php';
		require_once( $captchaFilePath );

		$captchaObject = new JoomoobaseCaptcha( $captcha_type );
		$captchaString = $captchaObject->getCaptchaString();

		return $captchaString;
	}

	//
	// ----------------------------
	// methods useful for debugging
	// ----------------------------
	// uncomment lines and what-not as appropriate to see what's what
	//
	/**
	 * get the comments and return html containing them in raw form
	 * @access private
	 * @return string html containing comments
	 */
	private function _debugSeeCommentRows( $commentRows )
	{

		$seeCommentData .= '<br />----------------------------------';
		$seeCommentData .= '<br />this->_query = ' . $this->_query;
		$seeCommentData .= '<br />----------------------------------';
		$seeCommentData .= '<br />Running print_r on commentRows object:';
		$seeCommentData .= '<br />---------------------------------------<br />';
		$seeCommentData .= print_r( $commentRows, TRUE );
		$seeCommentData .= '<br />----------------------------------------';
		$seeCommentData .= '<br />End of print_r output for commentRows object.';
		$seeCommentData .= '<br />---------------------------------------------<br />';

		return $seeCommentData;
	}
}
