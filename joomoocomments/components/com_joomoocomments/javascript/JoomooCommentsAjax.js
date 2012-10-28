/**
 * @author      Tom Hartung <webmaster@tomhartung.com>
 * @package     Joomla
 * @subpackage  JoomooComments
 * @copyright   Copyright (C) 2010 Tom Hartung. All rights reserved.
 * @since       1.5
 * @license     GNU/GPL, see LICENSE.php
 */

/**
 * functions to support using ajax for the joomoocomments extension
 * ----------------------------------------------------------------
 */
var JoomooCommentsAjax = {};         // declare our class name in the global namespace
function JoomooCommentsAjax () { };  // dummy constructor (singleton class)

/**
 * Use javascript to write the Like, Dislike, and Flag as Spam links because
 * if user has javascript turned off they won't work anyway
 */
JoomooCommentsAjax.writeVotingLinks = function ( id, likes, dislikes, spam )
{
	document.write( '<div class="joomoocomment_footer">' );
	document.write( '<span id="joomoocomment_likes_' + id + '" class="joomoocomment_vote">' );
	document.write(     '<a href="#" title="Like this comment" ' );
	document.write(       'onclick="return JoomooCommentsAjax.likeComment(' + id + ')">' );
	document.write(       'Like</a>&nbsp;(' + likes + ")</span>\n" );
	document.write( '<span id="joomoocomment_dislikes_' + id + '" class="joomoocomment_vote">' );
	document.write(     '<a href="#" title="Dislike this comment" ' );
	document.write(       'onclick="return JoomooCommentsAjax.dislikeComment(' + id + ')">' );
	document.write(   'Dislike</a>&nbsp;(' + dislikes + ")</span>\n" );
	document.write( '<span id="joomoocomment_spam_' + id + '" class="joomoocomment_vote">' );
	document.write(     '<a href="#" title="Flag this comment as spam" ' );
	document.write(       'onclick="return JoomooCommentsAjax.flagCommentAsSpam(' + id + ')">' );
	document.write(      'Flag as spam</a>&nbsp;(' + spam + ")</span>\n" );
	document.write( '</div>' );
}

//
// functions to support using ajax to store a comment in the database
// ------------------------------------------------------------------
//
//	JoomooCommentsAjax._storeUrl = '/components/com_joomoocomments/requests/helloWorld.php';  // for debugging
JoomooCommentsAjax._storeUrl = '/components/com_joomoocomments/requests/storeComment.php';
JoomooCommentsAjax._storeData = {};

/**
 * event handler for when comment is successfully stored in DB
 */
JoomooCommentsAjax.storedSuccessfully = function ( responseText )
{
	//	alert ( 'responseText = ' + responseText );
	//	if ( $('response_text') != null ) { $('response_text').set( 'html', responseText + '<\/span>' ); }

	var message;
	var responsePieces;
	var formNumber;
	var joomoocommentAjaxFormId;

	if ( responseText != null )
	{
		responsePieces = responseText.split( JoomooCommentsAjax.COMMENTS_RESPONSE_DELIMITER );
		formNumber = responsePieces[2];
		joomoocommentAjaxFormId = 'joomoocomment_ajax_form_' + formNumber;
	}

	if ( responseText.indexOf(JoomooCommentsAjax.COMMENT_SAVED_OK) == 0 )
	{
		JoomooCommentsAjax._storeData.commentid = responsePieces[1];
		if ( JoomooCommentsAjax._storeData.published == 0 )
		{
			message = 'Comment saved and will be published pending approval.';
		}
		else
		{
			message = responsePieces[0];
			JoomooCommentsAjax._displayNewComment( formNumber );
		}
		//
		// If we are using the captcha then we wipe out the form with the message
		//    because they can't submit more than one comment for only one captcha image
		// TODO (maybe): figure out how to generate a new captcha image after successful comment submission
		//
		if ( JoomooCommentsAjax.captcha_required && $(joomoocommentAjaxFormId) != null )
		{
			$(joomoocommentAjaxFormId).set( 'html', message + '<\/span>' );
		}
		else if ( $('ajax_log') != null )
		{
			$('ajax_log').set( 'html', message + '<\/span>' );
		}
	}
	else if ( responseText.indexOf('Error') == 0 )   // known error, eg. comment too short
	{
		alert( responseText );
		if ( $('ajax_log') != null )
		{
			$('ajax_log').set( 'html', 'Unable to store comment: ' + responseText + '.<\/span>' );
		}
	}
	else
	{
		if ( $('ajax_log') != null )
		{
			$('ajax_log').set( 'html', responseText + '<\/span>' );
		}
		alert( 'Sorry, some sort of error occurred.  You may want to try again later.' );
	}
}
/**
 * Error handler for when the request returns an error
 */
JoomooCommentsAjax.storeError  = function ( status, statusText )
{
	if ( $('response_text') != null )
	{ $('response_text').set( 'html', 'Error ' + status + ': ' + statusText + '<\/span>' ); }
	alert( 'Error ' + status + ': ' + statusText );
}

/**
 * Get, setup, and send the request to store a comment
 */
JoomooCommentsAjax.ajaxCommentIntoDb = function ( contentid, gallerygroupid, galleryimageid, created_by, published, formNumber )
{
	var myJoomooRequest;

	if ( JoomooCommentsLib.validateForm(created_by, formNumber) )
	{
		var nameId    = 'joomoocomment_name_' + formNumber;
		var emailId   = 'joomoocomment_email_' + formNumber;
		var websiteId = 'joomoocomment_website_' + formNumber;
		var textId    = 'joomoocomment_text_' + formNumber;
		JoomooCommentsAjax._storeData.form_number = formNumber;
		JoomooCommentsAjax._storeData.contentid = contentid;
		JoomooCommentsAjax._storeData.gallerygroupid = gallerygroupid;
		JoomooCommentsAjax._storeData.galleryimageid = galleryimageid;
		JoomooCommentsAjax._storeData.created_by = created_by;
		JoomooCommentsAjax._storeData.published = published;
		if ( $(nameId) != null )
		{
			JoomooCommentsAjax._storeData.name = $(nameId).value;
		}
		if ( $(emailId) != null )
		{
			JoomooCommentsAjax._storeData.email = $(emailId).value;
		}
		if ( $(websiteId) != null )
		{
			JoomooCommentsAjax._storeData.website = $(websiteId).value;
		}
		if ( $(textId) != null )
		{
			JoomooCommentsAjax._storeData.text = $(textId).value;
		}
		JoomooCommentsAjax._storeData.email_on_form = JoomooCommentsLib.email_on_form;         // used to validate request on server
		JoomooCommentsAjax._storeData.website_on_form  = JoomooCommentsLib.website_on_form;    // used to validate request on server
		if ( JoomooCommentsLib.ip_address.length > 0 )
		{
			JoomooCommentsAjax._storeData.ip_address  = JoomooCommentsLib.ip_address;
		}
		if ( JoomooCommentsAjax.captcha_required )
		{
			JoomooCommentsAjax._storeData.captcha_required = JoomooCommentsAjax.captcha_required;
			JoomooCommentsAjax._storeData.captcha_type     = JoomooCommentsAjax.captcha_type;
			if ( JoomooCommentsAjax.captcha_type == JoomooCommentsAjax.CAPTCHA_TYPE_OPENCAPTCHA )
			{
				var img = document.getElementsByName('img')[0];
				var code = document.getElementsByName('code')[0];
				if ( img != null )
				{
					JoomooCommentsAjax._storeData.img = img.value;
				}
				if ( code != null )
				{
					JoomooCommentsAjax._storeData.code = code.value;
				}
			}
			else if ( JoomooCommentsAjax.captcha_type == JoomooCommentsAjax.CAPTCHA_TYPE_RECAPTCHA )
			{
				var recaptcha_challenge_field = document.getElementsByName('recaptcha_challenge_field')[0];
				var recaptcha_response_field = document.getElementsByName('recaptcha_response_field')[0];
				if ( recaptcha_challenge_field != null )
				{
					JoomooCommentsAjax._storeData.recaptcha_challenge_field = recaptcha_challenge_field.value;
				}
				if ( recaptcha_response_field != null )
				{
					JoomooCommentsAjax._storeData.recaptcha_response_field = recaptcha_response_field.value;
				}
			}
		}

		myJoomooRequest = new JoomooRequest( JoomooCommentsAjax._storeUrl, JoomooCommentsAjax.storedSuccessfully, JoomooCommentsAjax.storeError );
		myJoomooRequest.sendPostRequest( JoomooCommentsAjax._storeData );
		JoomooCommentsAjax._sendFollowUpEmail();
	}

	return false;
}

/**
 * display the comment on the page after it's been stored successfully in DB
 * we put the first comment in an already-existing empty div with id="new_joomoocomment_0" and
 * create a new empty div with id="new_joomoocomment_1" etc. in case user wants to add multiple comments
 * the corresponding php function - that you will probably also want to change - is
 *    _getXhtmlForACommentRow() in plugins/content/joomoocomments.php
 */
JoomooCommentsAjax.commentCount = [];
JoomooCommentsAjax._displayNewComment = function ( formNumber )
{
	var created = new Date();
	var commentid = JoomooCommentsAjax._storeData.commentid;
	var dateString = created.toLocaleDateString() + '&nbsp;' + created.toLocaleTimeString();
	var nameSiteDate;
	var deleteLink;
	var headingStart;
	var headingBody;
	var headingEnd;
	var commentHeading;       // user name, date and time, and - if not anonymous - delete link in case user changes his mind
	var commentBody;          // the comment
	var newCommentString;     // comment head + comment body
	var nextCommentString;    // empty div - placeholder for next comment if user wants to add more than one

	if ( JoomooCommentsAjax.commentCount[formNumber] == undefined )
	{
		JoomooCommentsAjax.commentCount[formNumber] = 0;
	}

	if ( JoomooCommentsAjax._storeData.website != null &&
	     0 < JoomooCommentsAjax._storeData.website.length )
	{
		website = JoomooCommentsAjax._storeData.website.replace( /http:\/\//, '' );
		nameSiteDate = JoomooCommentsAjax._storeData.name + '&nbsp;(' +
			'<a href="http://' + website + '" target="_blank">' +
			   'http://' + website + '</a>)&nbsp;&mdash;&nbsp;' + dateString + ':';
	}
	else
	{
		nameSiteDate = JoomooCommentsAjax._storeData.name + '&nbsp;&mdash;&nbsp;' + dateString + ':';
	}

	if ( 0 < JoomooCommentsAjax._storeData.created_by )
	{
		if ( JoomooCommentsAjax.ajax_or_full == JoomooCommentsAjax.JOOMOO_USE_FULL_ONLY )
		{
			deleteLink = '<a href="/index.php/joomoocomments?task=delete&amp;' +
				'readmore_link=' + JoomooCommentsAjax.readmore_link + '&amp;id=' + JoomooCommentsAjax._storeData.id + '" ' +
				'title="Delete this comment" onclick="return confirm(\'Are you sure you want to delete this comment?\');">' +
				JoomooCommentsAjax.TEXT_FOR_DELETE_LINK + '</a>';
		}
		else
		{
			deleteLink = '<a href="#" title="Delete this comment" ' +
				'onclick="return JoomooCommentsAjax.deleteFromDatabase(' + commentid + ');">' +
				JoomooCommentsAjax.TEXT_FOR_DELETE_LINK + '</a>';
		}
		headingStart = '<table class="joomoocomment_heading"><tr>';
		headingBody = '<td class="small underline">' + nameSiteDate + '</td>' +
			'<td class="joomoocomment_delete_link" style="margin-right: 0px; text-align: right;">' + deleteLink + '</td>';
		headingEnd = '</tr></table>';
	}
	else
	{
		headingStart = '<p class="joomoocomment_heading">';
		headingBody = '<span class="small underline">' + nameSiteDate + '</span>';
		headingEnd = '</p>';
	}

	commentHeading = headingStart + headingBody + headingEnd;
	commentBody = '<p id="new_joomoocomment_body_' + commentid + '" class="joomoocomment_body">' + JoomooCommentsAjax._storeData.text + '</p>';

	newCommentString = '<div id="joomoocomment_' + commentid + '" class="a_joomoocomment">' + commentHeading + commentBody + '</div>';
	var thisCommentId = 'new_joomoocomment_' + formNumber + '_' + JoomooCommentsAjax.commentCount[formNumber]++;
	var textFieldToClear = 'joomoocomment_text_' + formNumber;

	var nextCommentId = 'new_joomoocomment_' + formNumber + '_' + JoomooCommentsAjax.commentCount[formNumber];
	nextCommentString = '<div id="' + nextCommentId + '"></div>';
	$(thisCommentId).set( 'html', newCommentString + nextCommentString );
	$(textFieldToClear).value = '';                           // clear the comment text box

	if ( JoomooCommentsAjax.max_consecutive_comments <= JoomooCommentsAjax.commentCount[formNumber] )
	{
		var formId = 'joomoocomment_ajax_form_' + formNumber;
		alert( 'This site allows you to enter only ' + JoomooCommentsAjax.max_consecutive_comments + ' comments at a time.' );
		$(formId).set( 'html', '' );
	}
	//	else { }

	//	if ( $('response_text') != null ) { $('response_text').set( 'html', responseText + '<\/span>' ); }
	//	if ( $('debug_text_1') != null ) { $('debug_text_1').set( 'html', 'thisCommentId = ', thisCommentId ); }
	//	if ( $('debug_text_2') != null ) { $('debug_text_2').set( 'html', 'nextCommentString = ', nextCommentString ) ; }

	return;
}

/**
 * Checks to see whether user has already voted too many times for this instance
 * @return true if user has not exceeded limit else false this user can NOT vote
 */
JoomooCommentsAjax.okToComment = function ( formNumber )
{
//	alert( 'JoomooCommentsAjax.commentCount[formNumber] = ' + JoomooCommentsAjax.commentCount[formNumber] + '; ' +
//		'JoomooCommentsAjax.max_consecutive_comments = ' + JoomooCommentsAjax.max_consecutive_comments );
	var returnValue = true;

	if ( typeof(JoomooCommentsAjax.commentCount[formNumber]) == 'number' )
	{
		JoomooCommentsAjax.commentCount[formNumber] < JoomooCommentsAjax.max_consecutive_comments ?
			returnValue = true :
			returnValue = false;
	}
	return returnValue;
}

/**
 * ensconce the basic span tag for message received by server in a div tag
 */
JoomooCommentsAjax.writeAjaxLog = function ( )
{
	document.write( '  <div id="joomoocomments_ajax_log" class="joomoocomments_ajax_log">' );
	document.write( '   <center>' );
	JoomooRequest.writeAjaxLog();
	document.write( '   <center>' );
	document.write(    '</div>' );
}
//
// functions to support sending follow up emails when a comment is posted
// ----------------------------------------------------------------------
//
/**
 * event handler for when follow up emails have been successfully sent
 */
JoomooCommentsAjax.emailSentSuccessfully = function ( responseText )
{
	// if ( $('ajax_log') == null )
	// {
	//	alert( 'responseText: ' + responseText );
	// }
	// else
	// {
	//	 $('ajax_log').set( 'html', responseText + '<\/span>' );
	// }
}
/**
 * event handler for when an error occurs sending follow up emails
 */
JoomooCommentsAjax.sendEmailError = function ( status, statusText )
{
	if ( $('response_text') != null )
	{ $('response_text').set( 'html', 'Error ' + status + ': ' + statusText + '<\/span>' ); }
	alert( 'Error ' + status + ': ' + statusText );
}
/**
 * After comment is posted, send follow up emails
 */
// JoomooCommentsAjax._followUpEmailUrl = '/components/com_joomoouser/requests/arrayExp.php';
JoomooCommentsAjax._followUpEmailUrl = '/components/com_joomoouser/requests/sendFollowUpEmails.php';
JoomooCommentsAjax._followUpEmailData = {};
JoomooCommentsAjax._sendFollowUpEmail = function()
{
	if ( JoomooCommentsAjax._followUpEmailsEnabled && JoomooCommentsAjax._followUpEmailsNeeded )
	{
		JoomooCommentsAjax._followUpEmailData.text = JoomooCommentsAjax._storeData.text;
		JoomooCommentsAjax._followUpEmailData.commentAuthorName = JoomooCommentsAjax._commentAuthorName;
		JoomooCommentsAjax._followUpEmailData.readmore_link = JoomooCommentsAjax._readmore_link;
		JoomooCommentsAjax._followUpEmailData.idsToEmail = JoomooCommentsAjax._idsToEmail;
		JoomooCommentsAjax._followUpEmailData.comment_posted_email = JoomooCommentsAjax._comment_posted_email;
		var myJoomooRequest;
		myJoomooRequest = new JoomooRequest( JoomooCommentsAjax._followUpEmailUrl, JoomooCommentsAjax.emailSentSuccessfully, JoomooCommentsAjax.sendEmailError );
		myJoomooRequest.sendPostRequest( JoomooCommentsAjax._followUpEmailData );
	}
	// else
	// {
	// 	alert( 'Follow up emails are either not enabled or not necessary (eg. no recipients found)' );
	// }

	return false;
}

//
// functions to support using ajax to delete a comment from the database
// ---------------------------------------------------------------------
//
JoomooCommentsAjax._deleteUrl = '/components/com_joomoocomments/requests/deleteComment.php';
JoomooCommentsAjax._deleteData = {};

/**
 * event handler for when comment is successfully deleted from DB
 */
JoomooCommentsAjax.deletedSuccessfully = function ( responseText )
{
	//	alert ( 'responseText = ' + responseText );
	//	if ( $('response_text') != null ) { $('response_text').set( 'html', responseText + '<\/span>' ); }

	var message;

	if ( responseText.indexOf(JoomooCommentsAjax.COMMENT_DELETED_OK) == 0 )
	{
		var idToDelete = 'joomoocomment_' + JoomooCommentsAjax._deleteData.id;
		message = responseText;
		if ( $(idToDelete) != null )
		{
			$(idToDelete).set( 'html', message + '<\/span>' );
		}
	}
	else if ( responseText.indexOf('Error') == 0 )   // known error, eg. comment too short
	{
		alert( responseText );
		if ( $('ajax_log') != null )
		{
			$('ajax_log').set( 'html', 'Unable to delete comment.<\/span>' );
		}
	}
	else
	{
		if ( $('ajax_log') != null )
		{
			$('ajax_log').set( 'html', responseText + '<\/span>' );
		}
		alert( 'Sorry, some sort of error occurred.  You may want to try again later.' );
	}
}
/**
 * Error handler for when the request returns an error
 */
JoomooCommentsAjax.deleteError  = function ( status, statusText )
{
	if ( $('response_text') != null )
	{ $('response_text').set( 'html', 'Error ' + status + ': ' + statusText + '<\/span>' ); }
	alert( 'Error ' + status + ': ' + statusText );
}

/**
 * Get, setup, and send the request to delete a comment
 */
JoomooCommentsAjax.deleteFromDatabase = function ( id )
{
	var myJoomooRequest;

	if ( confirm('Are you sure you want to delete this comment?') )
	{
		JoomooCommentsAjax._deleteData.id = id;
		myJoomooRequest = new JoomooRequest( JoomooCommentsAjax._deleteUrl, JoomooCommentsAjax.deletedSuccessfully, JoomooCommentsAjax.deleteError );
		myJoomooRequest.sendPostRequest( JoomooCommentsAjax._deleteData );
	}

	return false;
}

//
// functions to support using ajax to like, dislike, and report a comment as spam
// ------------------------------------------------------------------------------
//
JoomooCommentsAjax._updateUrl = '/components/com_joomoocomments/requests/updateComment.php';
JoomooCommentsAjax._updateData = {};

/**
 * event handler for when comment is successfully updated in DB
 */
JoomooCommentsAjax.updatedSuccessfully = function ( responseText )
{
	//	alert ( 'responseText = ' + responseText );
	//	if ( $('response_text') != null ) { $('response_text').set( 'html', responseText + '<\/span>' ); }

	var message;
	var idToUpdate;
	var newCount;
	var responsePieces = responseText.split( JoomooCommentsAjax.COMMENTS_RESPONSE_DELIMITER );

	if ( responseText.indexOf(JoomooCommentsAjax.COMMENT_UPDATED_OK) == 0 )
	{
		idToUpdate = 'joomoocomment_' + JoomooCommentsAjax._updateData.task + '_' + JoomooCommentsAjax._updateData.id;
		newCount = responsePieces[1];
		message = 'Thanks! (' + newCount + ')';
		if ( $(idToUpdate) != null )
		{
			$(idToUpdate).set( 'html', message + '<\/span>' );
		}
	}
	else if ( responseText.indexOf('Error') == 0 )   // known error, eg. comment too short
	{
		alert( responseText );
		if ( $('ajax_log') != null )
		{
			$('ajax_log').set( 'html', 'Unable to update comment.<\/span>' );
		}
	}
	else
	{
		if ( $('ajax_log') != null )
		{
			$('ajax_log').set( 'html', responseText + '<\/span>' );
		}
		alert( 'Sorry, some sort of error occurred.  You may want to try again later.' );
	}
}
/**
 * Error handler for when the request returns an error
 */
JoomooCommentsAjax.updateError  = function ( status, statusText )
{
	if ( $('response_text') != null )
	{ $('response_text').set( 'html', 'Error ' + status + ': ' + statusText + '<\/span>' ); }
	alert( 'Error ' + status + ': ' + statusText );
}
/**
 * Get, setup, and send the request to update a comment
 */
JoomooCommentsAjax.updateDatabase = function ( id )
{
	var myJoomooRequest;
	JoomooCommentsAjax._updateData.id = id;
	JoomooCommentsAjax._updateData.spam_flag_email = JoomooCommentsLib.spam_flag_email;

	if ( JoomooCommentsAjax._updateData.task == 'spam' && JoomooCommentsLib.spam_flag_email )
	{
		JoomooCommentsAjax._updateData.base = JoomooCommentsLib.base;
	}

	myJoomooRequest = new JoomooRequest( JoomooCommentsAjax._updateUrl, JoomooCommentsAjax.updatedSuccessfully, JoomooCommentsAjax.updateError );
	myJoomooRequest.sendPostRequest( JoomooCommentsAjax._updateData );

	return false;
}
/**
 * Increment likes counter for a comment
 */
JoomooCommentsAjax.likeComment = function ( id )
{
	JoomooCommentsAjax._updateData.task = 'likes';

	return JoomooCommentsAjax.updateDatabase ( id );
}
/**
 * Increment dislikes counter for a comment
 */
JoomooCommentsAjax.dislikeComment = function ( id )
{
	JoomooCommentsAjax._updateData.task = 'dislikes';

	return JoomooCommentsAjax.updateDatabase ( id );
}
/**
 * Flags a comment as spam
 */
JoomooCommentsAjax.flagCommentAsSpam = function ( id )
{
	JoomooCommentsAjax._updateData.task = 'spam';

	return JoomooCommentsAjax.updateDatabase ( id );
}
