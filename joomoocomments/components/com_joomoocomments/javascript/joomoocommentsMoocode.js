/**
 * @author      Tom Hartung <webmaster@tomhartung.com>
 * @package     Joomla
 * @subpackage  joomoocomments
 * @copyright   Copyright (C) 2010 Tom Hartung. All rights reserved.
 * @since       1.5
 * @license     GNU/GPL, see LICENSE.php
 */

/*
 * All mootools events calls rely on the document's elements having already been created
 * So we run this code only when the DOM is ready, ie the domready event fires
 */
window.addEvent('domready', function() {
/*
 * Code to show hidden comments and *horizontally* hide the links that show the comments:
 * --------------------------------------------------------------------------------------
 * We put this in a loop and use arras of ids to support pages
 * (eg. gallery page) that have more than one set of comments
 */
	if ( JoomooCommentsLib.usingIE )
	{
		JoomooCommentsLib.initiallySlideOutForm = false;
	}

	for ( var formNumber = 0; formNumber < 999; formNumber++ )
	{
		JoomooCommentsLib.hiddenCommentsId[formNumber]         = 'hidden_comments_'         + formNumber;
		JoomooCommentsLib.slideinCommentsTopId[formNumber]     = 'slidein_comments_top_'    + formNumber;
		JoomooCommentsLib.slideinCommentsBottomId[formNumber]  = 'slidein_comments_bottom_' + formNumber;
		JoomooCommentsLib.showCommentsTopLinkId[formNumber]    = 'show_comments_top_link_'    + formNumber;
		JoomooCommentsLib.showCommentsBottomLinkId[formNumber] = 'show_comments_bottom_link_' + formNumber;
		if ( $(JoomooCommentsLib.hiddenCommentsId[formNumber]) == null )
		{
			break;
		}
		else
		{
			JoomooCommentsLib.hiddenCommentsSlider[formNumber] = new Fx.Slide(JoomooCommentsLib.hiddenCommentsId[formNumber]);
			JoomooCommentsLib.hiddenCommentsSlider[formNumber].slideOut();
			if ( $(JoomooCommentsLib.slideinCommentsTopId[formNumber]) != null )        // ensure element is in page
			{
				JoomooCommentsLib.slideInCommentsFunction = JoomooCommentsLib.getSlideInCommentsFunction( formNumber );
				$(JoomooCommentsLib.slideinCommentsTopId[formNumber]).addEvent('click', JoomooCommentsLib.slideInCommentsFunction);
			}
			if ( $(JoomooCommentsLib.slideinCommentsBottomId[formNumber]) != null )     // ensure element is in page
			{
				JoomooCommentsLib.slideInCommentsFunction = JoomooCommentsLib.getSlideInCommentsFunction( formNumber );
				$(JoomooCommentsLib.slideinCommentsBottomId[formNumber]).addEvent('click', JoomooCommentsLib.slideInCommentsFunction);
			}
		}
		if ( $(JoomooCommentsLib.showCommentsTopLinkId[formNumber]) != null )       // click causes element to hide itself
		{
			JoomooCommentsLib.showCommentsTopSlider[formNumber] =
				new Fx.Slide(JoomooCommentsLib.showCommentsTopLinkId[formNumber], {mode: 'horizontal'});
			if ( $(JoomooCommentsLib.slideinCommentsTopId[formNumber]) != null )
			{
				JoomooCommentsLib.slideOutTopSliderFunction = JoomooCommentsLib.getSlideOutTopSliderFunction( formNumber );
				$(JoomooCommentsLib.slideinCommentsTopId[formNumber]).addEvent('click', JoomooCommentsLib.slideOutTopSliderFunction );
			}
		}
		if ( $(JoomooCommentsLib.showCommentsBottomLinkId[formNumber]) != null )       // click causes element to hide itself
		{
			JoomooCommentsLib.showCommentsBottomSlider[formNumber] =
				new Fx.Slide(JoomooCommentsLib.showCommentsBottomLinkId[formNumber], {mode: 'horizontal'});
			if ( $(JoomooCommentsLib.slideinCommentsBottomId[formNumber]) != null )
			{
				JoomooCommentsLib.slideOutBottomSliderFunction = JoomooCommentsLib.getSlideOutBottomSliderFunction( formNumber );
				$(JoomooCommentsLib.slideinCommentsBottomId[formNumber]).addEvent('click', JoomooCommentsLib.slideOutBottomSliderFunction);
			}
		}
		//
		// Code to show and hide the form and the link that displays the form:
		//
		JoomooCommentsLib.joomoocommentAjaxFormId[formNumber] = 'joomoocomment_ajax_form_' + formNumber;
		JoomooCommentsLib.showCommentFormLinkId[formNumber]   = 'show_comment_form_link_' + formNumber;
		JoomooCommentsLib.showCommentFormDivId[formNumber]    = 'show_comment_form_div_' + formNumber;
		if ( $(JoomooCommentsLib.joomoocommentAjaxFormId[formNumber]) != null )
		{
			JoomooCommentsLib.showCommentFormSlider[formNumber] = new Fx.Slide(JoomooCommentsLib.joomoocommentAjaxFormId[formNumber]);
			if ( JoomooCommentsLib.initiallySlideOutForm )
			{
				JoomooCommentsLib.showCommentFormSlider[formNumber].slideOut();
			}
			if ( $(JoomooCommentsLib.showCommentFormLinkId[formNumber]) != null )   // ensure element is in page
			{
				JoomooCommentsLib.slideInCommentFormFunction = JoomooCommentsLib.getSlideInCommentFormFunction( formNumber );
				$(JoomooCommentsLib.showCommentFormLinkId[formNumber]).addEvent( 'click', JoomooCommentsLib.slideInCommentFormFunction );
			}
		}
		if ( $(JoomooCommentsLib.showCommentFormDivId[formNumber]) != null )
		{
			JoomooCommentsLib.hideCommentFormLinkSlider[formNumber] = new Fx.Slide(JoomooCommentsLib.showCommentFormDivId[formNumber]);
			if ( $(JoomooCommentsLib.showCommentFormLinkId[formNumber]) != null )   // click causes element to hide itself
			{
				JoomooCommentsLib.slideOutCommentFormFunction = JoomooCommentsLib.getHideCommentFormFunction( formNumber );
				$(JoomooCommentsLib.showCommentFormLinkId[formNumber]).addEvent( 'click', JoomooCommentsLib.slideOutCommentFormFunction );
			}
		}
	}
});
