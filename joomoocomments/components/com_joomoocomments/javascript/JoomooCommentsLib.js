/**
 * @author      Tom Hartung <webmaster@tomhartung.com>
 * @package     Joomla
 * @subpackage  joomoocomments
 * @copyright   Copyright (C) 2010 Tom Hartung. All rights reserved.
 * @since       1.5
 * @license     GNU/GPL, see LICENSE.php
 */

/**
 * data members and functions to support joomoocomments extension
 * --------------------------------------------------------------
 */
var JoomooCommentsLib = {};         // declare our class name in the global namespace
function JoomooCommentsLib () { };  // dummy constructor (singleton class)

/*
 * We need to know whether user is using Internet Exploder
 */
JoomooCommentsLib.usingIE = false;
/*@cc_on
 @if ( @_jscript )
 	JoomooCommentsLib.usingIE = true;
/*@end
 @*/
/*
 * If the browser is IE and we are using reCAPTCHA
 *   we do not slide out the form initially because it doesn't look right
 */
JoomooCommentsLib.initiallySlideOutForm = true;
JoomooCommentsLib.hiddenCommentsId = [];
JoomooCommentsLib.hiddenCommentsSlider = [];
JoomooCommentsLib.slideinCommentsTopId = [];
JoomooCommentsLib.slideinCommentsBottomId = [];
JoomooCommentsLib.showCommentsTopLinkId = [];
JoomooCommentsLib.showCommentsTopSlider = [];
JoomooCommentsLib.showCommentsBottomLinkId = [];
JoomooCommentsLib.showCommentsBottomSlider = [];
JoomooCommentsLib.slideInCommentsFunction;
JoomooCommentsLib.slideOutTopSliderFunction;
JoomooCommentsLib.slideOutBottomSliderFunction;
JoomooCommentsLib.joomoocommentAjaxFormId = [];
JoomooCommentsLib.showCommentFormLinkId = [];
JoomooCommentsLib.showCommentFormDivId = [];
JoomooCommentsLib.showCommentFormSlider = [];
JoomooCommentsLib.hideCommentFormLinkSlider = [];
JoomooCommentsLib.slideInCommentFormFunction;
JoomooCommentsLib.slideOutCommentFormFunction;
JoomooCommentsLib.getSlideInCommentsFunction = function ( formNumber )
{
	return function(e){
		e = new Event(e);
		JoomooCommentsLib.hiddenCommentsSlider[formNumber].slideIn();
		e.stop();
	};
}
JoomooCommentsLib.getSlideOutTopSliderFunction = function ( formNumber )
{
	return function(e) {
		e = new Event(e);
		JoomooCommentsLib.showCommentsTopSlider[formNumber].slideOut();
		if ( $(JoomooCommentsLib.showCommentsTopLinkId[formNumber]) != null )
		{
			$(JoomooCommentsLib.showCommentsTopLinkId[formNumber]).setStyle('height',0);
		}
		e.stop();
	};
}
JoomooCommentsLib.getSlideOutBottomSliderFunction = function ( formNumber )
{
	return function(e) {
		e = new Event(e);
		JoomooCommentsLib.showCommentsBottomSlider[formNumber].slideOut();
		if ( $(JoomooCommentsLib.showCommentsBottomLinkId[formNumber]) != null )
		{
			$(JoomooCommentsLib.showCommentsBottomLinkId[formNumber]).setStyle('height',0);
		}
		e.stop();
	};
}
JoomooCommentsLib.getSlideInCommentFormFunction = function ( formNumber)
{
	return function (e) {
		e = new Event(e);
		JoomooCommentsLib.showCommentFormSlider[formNumber].slideIn();
		e.stop();
	}
}
JoomooCommentsLib.getHideCommentFormFunction = function ( formNumber )
{
	return function (e) {
		e = new Event(e);
		JoomooCommentsLib.hideCommentFormLinkSlider[formNumber].slideOut();
		if ( $(JoomooCommentsLib.showCommentFormDivId[formNumber]) != null )
		{
			$(JoomooCommentsLib.showCommentFormDivId[formNumber]).setStyle('height',0);
		}
		e.stop();
	}
}
/**
 * validate form before submitting it to the server
 * The system also validates data before storing it in the db, in check() which is in:
 *    administrator/components/com_joomoocomments/tables/joomoocomments.php
 */
JoomooCommentsLib.validateForm = function ( created_by, formNumber )
{
	var email = '';
	var website = '';
	var text = '';
	var emailId   = 'joomoocomment_email_' + formNumber;
	var websiteId = 'joomoocomment_website_' + formNumber;
	var textId    = 'joomoocomment_text_' + formNumber;

	if ( ! JoomooCommentsAjax.okToComment(formNumber) )
	{
		var message;
		var numComments = JoomooCommentsAjax.numberOfComments[formNumber];
		numComments == 1 ?
			message = 'You ave already posted a comment to this article!' :
			message = 'You ave already posted ' + numComments + ' comments to this article!';
		alert( message );
		return false;
	}

	if ( $(emailId) != null )
	{
		email = $(emailId).value;
	}

	if ( $(websiteId) != null )
	{
		website = $(websiteId).value;
	}

	if ( $(textId) != null )
	{
		text = $(textId).value;
	}

	if ( typeof(email) == 'string' && 0 < email.length )
	{
		var emailRegEx = /^\S+@\S+\.\S+$/;
		if ( ! emailRegEx.test(email) )
		{
			alert( 'The specified email address (' + email + ') is not in the proper format.' );
			return false;
		}
	}
	else if ( created_by == 0 && JoomooCommentsLib.email_on_form == JoomooCommentsLib.REQUIRED_FIELD )
	{
		alert( 'You must specify your email address.' );
		return false;
	}

	if ( typeof(website) == 'string' && 0 < website.length )
	{
		var websiteRegEx = /^\S+\.\S+$/;
		if ( ! websiteRegEx.test(website) )
		{
			alert( 'The specified website (' + website + ') is not in the proper format.' );
			return false;
		}
	}
	else if ( JoomooCommentsLib.website_on_form == JoomooCommentsLib.REQUIRED_FIELD )
	{
		alert( 'You must specify a website to your website.' );
		return false;
	}

	if ( text.length < JoomooCommentsLib.MINIMUM_COMMENT_LENGTH )
	{
		alert( 'Your comment (' + text + ') is too short.  ' +
			'Comments must contain at least ' + JoomooCommentsLib.MINIMUM_COMMENT_LENGTH + ' characters.' );
		return false;
	}
	if ( text.length > JoomooCommentsLib.MAXIMUM_COMMENT_LENGTH )
	{
		alert( 'Your comment (' + text + ') is too long.  ' +
			'Comments cannot contain more than ' + JoomooCommentsLib.MAXIMUM_COMMENT_LENGTH + ' characters.' );
		return false;
	}

	return true;
}

/**
 * write xhtml for top link when comments are hidden
 * we do this in javascript because the comments won't be hidden when javascript is off
 * so we want to show the xhatml for this link only when javascript is on
 */
JoomooCommentsLib.hiddenCommentsTopLink = function ( numberToShow, numberToHide, commentRowCount, formNumber )
{
	var whatsShowing;    // text describing comments that are showing (ie. "last X of ...")
	var whatToShow;      // text describing comments that are hidden (ie. "comment" or "comments")

	numberToShow == 1 ?
		whatsShowing = 'comment only of ' + commentRowCount + ' total comments' :
		whatsShowing = numberToShow + ' of ' + commentRowCount + ' comments';
	numberToHide == 1 ? whatToShow = 'comment' : whatToShow = numberToHide + ' comments';

	document.write( ' <div id="show_comments_top_link_' + formNumber + '" class="comments_hidden_link comments_top_link">' );
	document.write( '  <center>' );
	document.write( '   <p><a id="slidein_comments_top_' + formNumber + '" class="slidein_comments" href="#" ' );
	document.write(    'title="Slide in ' + numberToHide + ' comments">' );
	document.write(    'Show first ' + whatToShow + '.</a>' );
	document.write(    '</p>' );
	document.write( '   <p>Showing last ' + whatsShowing + '.' );
	document.write(    '</p>' );
	document.write( '  </center>' + "\n" );
	document.write( ' </div>' + "\n" );

	return
}
/**
 * write xhtml for opening div tag for hidden comments before loop
 * used when showing 'l'ast X comments only
 * should run immediately after hiddenCommentsTopLink() above but has nothing to do with printing the xhtml for the link
 */
JoomooCommentsLib.startHidingCommentsBeforeLoop = function( formNumber )
{
	var hiddenCommentsId = 'hidden_comments_' + formNumber;
	document.write( ' <div id="' + hiddenCommentsId + '"><!-- Start hiding comments - before loop. -->' );
	return
}
/**
 * write xhtml for opening div tag for hidden comments when inside loop
 * used when showing 'f'irst X comments only
 */
JoomooCommentsLib.startHidingCommentsInLoop = function( formNumber )
{
	var hiddenCommentsId = 'hidden_comments_' + formNumber;
	document.write( ' <div id="' + hiddenCommentsId + '"><!-- Start hiding comments - in loop. -->' );
	return
}
/**
 * write xhtml for closing div tag for hidden comments when inside loop
 * used when showing 'l'ast X comments only
 */
JoomooCommentsLib.endHidingCommentsInLoop = function( formNumber )
{
	document.write( ' </div><!-- End hiding comments - div id = "hidden_comments_' + formNumber + ' - in loop. -->' );
	return
}
/**
 * write xhtml for closing div tag for hidden comments after loop
 * used when showing 'f'irst X comments only
 * should run immediately before hiddenCommentsBottomLink() below but has nothing to do with printing the xhtml for the link
 */
JoomooCommentsLib.endHidingCommentsAfterLoop = function( formNumber )
{
	document.write( ' </div><!-- End hiding comments - div id = "hidden_comments_' + formNumber + '- after loop. -->' );
	return
}
/**
 * write xhtml for bottom link when comments are hidden
 * analogous to hiddenCommentsTopLink() above
 */
JoomooCommentsLib.hiddenCommentsBottomLink = function ( numberToShow, numberToHide, commentRowCount, formNumber )
{
	var whatsShowing;    // text describing comments that are showing (ie. "last X of ...")
	var whatToShow;      // text describing comments that are hidden (ie. "comment" or "comments")

	numberToShow == 1 ?
		whatsShowing = 'comment only of ' + commentRowCount + ' total comments' :
		whatsShowing = numberToShow + ' of ' + commentRowCount + ' comments';
	numberToHide == 1 ? whatToShow = 'comment' : whatToShow = numberToHide + ' comments';
	document.write( ' <div id="show_comments_bottom_link_' + formNumber + '" class="comments_hidden_link">' );
	document.write( '  <center>' );
	document.write( '   <p>Showing first ' + whatsShowing + '.' );
	document.write(    '</p>' );
	document.write( '   <p><a id="slidein_comments_bottom_' + formNumber + '" class="slidein_comments" href="#" ' );
	document.write(   'title="Slide in ' + numberToHide + ' comments">' );
	document.write(   'Show last ' + whatToShow + '.</a>' );
	document.write(   '</p>' );
	document.write( '  </center>' );
	document.write( ' </div>' + "\n" );

	return
}
/**
 * write xhtml for link to display normal in-line comments form
 * ***************************************************************
 * *** we are not currently using this method - hope that's OK ***
 * ***************************************************************
 */
JoomooCommentsLib.hiddenFormLink = function( formNumber )
{
	if ( ! JoomooCommentsLib.usingIE )
	{
		document.write( ' <div id="show_comment_form_div_' + formNumber + '" class="comment_form_hidden_link">' );
		document.write( '  <center>' );
		document.write(  '<a id="show_comment_form_link_' + formNumber + '" class="slidein_comment_form" href="#" ' );
		document.write(  'title="Slide in comment form">' );
		document.write(  'Enter a comment</a>' );
		document.write( '  <br style="height: 30px;" />' + "\n" );    // Hack - doesn't slidein well after user submits a comment
		document.write( '  </center>' );
		document.write( ' </div>' );
		document.write( ' <div>&nbsp;</div>' );
	}
}
/**
 * write xhtml for link to display pop-up modal comments form
 * **********************************************
 * *** we are not currently using this method ***
 * **********************************************
 */
JoomooCommentsLib.modalFormLink = function ( formNumber )
{
	document.write( ' <div id="comment_form_modal_link" class="comment_form_modal_link">' );
	document.write( '  <center>' );
	document.write(  '<a id="show_comment_form_link_' + formNumber + '" class="modal slidein_comment_form" ' );
	document.write(    'href="/components/com_joomoocomments/classes/getModalCommentForm.php" ' );
	document.write(    'rel="{handler: \'iframe\', size: {x: 500, y: 400}}" ' );
	document.write(  'title="Popup comment form">' );
	document.write(  'Enter a comment (popup)</a>' );
	document.write( '  <br style="height: 30px;" />' + "\n" );    // Hack - doesn't slidein well after user submits a comment
	document.write( '  </center>' );
	document.write( ' </div>' );
	document.write( ' <div>&nbsp;</div>' );
}
