<?php
/**
 * @version     $Id: edit_row.php,v 1.9 2008/10/31 06:18:46 tomh Exp tomh $
 * @author      Tom Hartung <webmaster@tomhartung.com>
 * @package     Joomla
 * @subpackage  Joomoocomments
 * @copyright   Copyright (C) 2010 Tom Hartung. All rights reserved.
 * @since       1.5
 * @license     GNU/GPL, see LICENSE.php
 */
/*
 * edit_row.php: when task is 'add' or 'edit' display form allowing user to edit the row
 * -------------------------------------------------------------------------------------
 * call model code to get data from DB
 * call function defined in this file to produce the HTML
 */

defined( '_JEXEC' ) or die( 'Restricted access' );      // no direct access

JToolBarHelper::title( JText::_( 'Joomla Mootools Comments: Update Comment Data' ), 'generic.png' );
$document = & JFactory::getDocument();
$document->setTitle(JText::_('Joomla Mootools Comments: Update Comment Data'));

//
// Get row data from the model and display form allowing editing of the row
//
$row =& $this->get( 'Row' );              // calls getRow() in model

$contentidsInDb      =& $this->get( 'Contentids' );          // calls getContentids() in the model
$gallerygroupidsInDb =& $this->get( 'Gallerygroupids' );     // calls getGallerygroupids() in the model
$galleryimageidsInDb =& $this->get( 'Galleryimageids' );     // calls getGalleryimageids() in the model

if ( $row->id == 0 )     // only get these if it's a new comment
{
	$noArticleClass = new stdClass();
	$noArticleClass->value = "0";
	$noArticleClass->text = '(Select Either an Article Title OR a Gallery Group Title OR a Gallery Image Title)';
	$noArticleArray = array( '-1' => $noArticleClass );
	$contentids = array_merge( $noArticleArray, $contentidsInDb );
	$gallerygroupids = array_merge( $noArticleArray, $gallerygroupidsInDb );
	$galleryimageids = array_merge( $noArticleArray, $galleryimageidsInDb );
}
else
{
	$contentids      = $contentidsInDb;
	$gallerygroupids = $gallerygroupidsInDb;
	$galleryimageids = $galleryimageidsInDb;
}

displayRow( $row, $contentids, $gallerygroupids, $galleryimageids );

/**
 * outputs HTML allowing user to add a new or edit an existing row in the DB
 */
function displayRow( $row, $contentids, $gallerygroupids, $galleryimageids )
{
	$option = JRequest::getCmd('option');

	$task = JRequest::getCmd('task');
	$user = & JFactory::getUser();

	if ( $task == 'edit' )
	{
		$published = $row->published;
		$name = $row->name;
	}
	else
	{
		$published = 1;
		$name = $user->name;
	}

	$lists = array();
	$gallerygroupTitle = '';

	//	print_r( $gallerygroupids );
	//	print '<br />';
	//	print '<br />';

	if ( $task == 'edit' )
	{
		$contentTitle = '';
		$galleryimageTitle = '';
		if ( $row->contentid > 0 )
		{
			$contentid = $row->contentid;
			foreach( $contentids as $contentidObject )
			{
				if ( $contentidObject->value == $contentid )
				{
					$contentTitle = '#' . $contentid . ': ' . $contentidObject->text;
					break;
				}
			}
		}
		else if ( $row->gallerygroupid > 0 )
		{
			$gallerygroupid = $row->gallerygroupid;
			foreach( $gallerygroupids as $gallerygroupidObject )
			{
				if ( $gallerygroupidObject->value == $gallerygroupid )
				{
					$gallerygroupTitle = '#' . $gallerygroupid . ': ' . $gallerygroupidObject->text;
					break;
				}
			}
		}
		else if ( $row->galleryimageid > 0 )
		{
			$galleryimageid = $row->galleryimageid;
			foreach( $galleryimageids as $galleryimageidObject )
			{
				if ( $galleryimageidObject->value == $galleryimageid )
				{
					$galleryimageTitle = '#' . $galleryimageid . ': ' . $galleryimageidObject->text;
					break;
				}
			}
		}
	}
	else
	{
		$lists['contentids'] = JHTML::_(
			'select.genericlist', $contentids, 'contentid', null, 'value', 'text', 0 );
		$lists['gallerygroupids'] = JHTML::_(
			'select.genericlist', $gallerygroupids, 'gallerygroupid', null, 'value', 'text', 0 );
		$lists['galleryimageids'] = JHTML::_(
			'select.genericlist', $galleryimageids, 'galleryimageid', null, 'value', 'text', 0 );
	}

	$lists['published']  = JHTML::_( 'select.booleanlist', 'published', 'class="inputBox"', $published );
	$lists['ordering']   = JHTML::_( 'select.integerlist', 0, 199, 1, 'ordering', '', $row->ordering );
	$editor =& JFactory::getEditor();
	JHTML::_( 'behavior.calendar' );

	print '<form action="index.php" method="post" name="adminForm" id="adminForm">' . "\n";
	print ' <fieldset class="adminform">' . "\n";
	print '  <legend>Details</legend>' . "\n";
	print '  <table class="admintable">' . "\n";

	if ( $task == 'edit' )
	{
		print '   <tr>' . "\n";
		print '    <td width="100px" align="right" class="key">Row ID:</td>' . "\n";
		print '    <td>' . $row->id . '</td>' . "\n";
		print '   </tr>' . "\n";
	}

	print '   <tr>' . "\n";
	print '    <td width="100px" align="right" class="key">Created By:</td>' . "\n";
	print '    <td>' . $row->created_by . "\n";
	print '    </td>' . "\n";
	print '   </tr>' . "\n";

	print '   <tr>' . "\n";
	print '    <td width="100px" align="right" class="key">Name:</td>' . "\n";
	print '    <td>' . "\n";
	print '     <input class="text_area" type="text" name="name" id="name" ';
	print         'size="50" maxlength="50" value="' . $name . '" />' . "\n";
	print '    </td>' . "\n";
	print '   </tr>' . "\n";

	print '   <tr>' . "\n";
	print '    <td width="100px" align="right" class="key">Email:</td>' . "\n";
	print '    <td>' . "\n";
	print '     <input class="text_area" type="text" name="email" id="email" ';
	print         'size="70" maxlength="150" value="' . $row->email . '" />' . "\n";
	print '    </td>' . "\n";
	print '   </tr>' . "\n";

	print '   <tr>' . "\n";
	print '    <td width="100px" align="right" class="key">Website:</td>' . "\n";
	print '    <td>' . "\n";
	print '     <input class="text_area" type="text" name="website" id="website" ';
	print         'size="70" maxlength="150" value="' . $row->website . '" />' . "\n";
	print '    </td>' . "\n";
	print '   </tr>' . "\n";

	print '   <tr>' . "\n";
	print '    <td width="100px" align="right" class="key">IP Address:</td>' . "\n";
	print '    <td>' . "\n";
	print '     <input class="text_area" type="text" name="ip_address" id="ip_address" ';
	print         'size="70" maxlength="150" value="' . $row->ip_address . '" />' . "\n";
	print '    </td>' . "\n";
	print '   </tr>' . "\n";

	print '   <tr>' . "\n";
	print '    <td width="100px" align="right" class="key">Published:</td>' . "\n";
	print '    <td>' . "\n";
	echo $lists['published'];
	print '    </td>' . "\n";
	print '   </tr>' . "\n";

	print '   <tr>' . "\n";
	print '    <td width="100px" align="right" class="key">Spam:</td>' . "\n";
	print '    <td>' . "\n";
	print '     <input class="text_area" type="text" name="spam" id="spam" ';
	print         'size="10" maxlength="10" value="' . $row->spam . '" />' . "\n";
	print '    </td>' . "\n";
	print '   </tr>' . "\n";

	print '   <tr>' . "\n";
	print '    <td width="100px" align="right" class="key">Like Count:</td>' . "\n";
	print '    <td>' . "\n";
	print '     <input class="text_area" type="text" name="likes" id="likes" ';
	print         'size="10" maxlength="10" value="' . $row->likes . '" />' . "\n";
	print '    </td>' . "\n";
	print '   </tr>' . "\n";

	print '   <tr>' . "\n";
	print '    <td width="100px" align="right" class="key">Dislike Count:</td>' . "\n";
	print '    <td>' . "\n";
	print '     <input class="text_area" type="text" name="dislikes" id="dislikes" ';
	print         'size="10" maxlength="10" value="' . $row->dislikes . '" />' . "\n";
	print '    </td>' . "\n";
	print '   </tr>' . "\n";

	print '   <tr>' . "\n";
	print '    <td width="100px" align="right" class="key">Article Title:</td>' . "\n";
	print '    <td>' . "\n";
	if ( $task == 'edit' )
	{
		print $contentTitle;
	}
	else
	{
		print $lists['contentids'];
	}
	print '    </td>' . "\n";
	print '   </tr>' . "\n";

	print '   <tr>' . "\n";
	print '    <td width="100px" align="right" class="key">Gallery Group Title:</td>' . "\n";
	print '    <td>' . "\n";
	if ( $task == 'edit' )
	{
		print $gallerygroupTitle;
	}
	else
	{
		isset($lists['gallerygroupids']) ? print $lists['gallerygroupids'] : print '';
	}
	print '    </td>' . "\n";
	print '   </tr>' . "\n";

	print '   <tr>' . "\n";
	print '    <td width="100px" align="right" class="key">Gallery Image Title:</td>' . "\n";
	print '    <td>' . "\n";
	$task == 'edit' ? print $galleryimageTitle : print $lists['galleryimageids'];
	print '    </td>' . "\n";
	print '   </tr>' . "\n";

	print '   <tr>' . "\n";
	print '    <td width="100px" align="right" class="key">Comment Text:</td>' . "\n";
	print '    <td>' . "\n";
	echo $editor->display( 'text', $row->text, '100%', '250','40', '5', 0 );  // last 0 -> no buttons
	print '    </td>' . "\n";
	print '   </tr>' . "\n";

	print '   <tr>' . "\n";
	print '    <td width="100px" align="right" class="key">Ordering:</td>' . "\n";
	print '    <td>' . "\n";
	echo $lists['ordering'];
	print '    </td>' . "\n";
	print '   </tr>' . "\n";

	print '  </table>' . "\n";

	print ' </fieldset>' . "\n";
	print ' <input type="hidden" name="id" value="' . $row->id . '" />' . "\n";
	print ' <input type="hidden" name="option" value="' . $option . '" />' . "\n";

	if ( $task == 'edit' )
	{
		print ' <input type="hidden" name="created_by" value="' . $row->created_by . '" />' . "\n";
		print ' <input type="hidden" name="contentid" value="' . $row->contentid . '" />' . "\n";
		print ' <input type="hidden" name="gallerygroupid" value="' . $row->gallerygroupid . '" />' . "\n";
		print ' <input type="hidden" name="galleryimageid" value="' . $row->galleryimageid . '" />' . "\n";
		print ' <input type="hidden" name="task" value="edit" />' . "\n";
	}
	else
	{
		print ' <input type="hidden" name="created_by" value="' . $user->id . '" />' . "\n";
		print ' <input type="hidden" name="task" value="post_comment" />' . "\n";
	}

	print '</form>' . "\n";
	print '' . "\n";
}
?>
