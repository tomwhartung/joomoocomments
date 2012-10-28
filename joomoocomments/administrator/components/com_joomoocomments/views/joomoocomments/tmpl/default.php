<?php
/**
 * @version     $Id: default.php,v 1.8 2008/10/31 06:17:57 tomh Exp tomh $
 * @author      Tom Hartung <webmaster@tomhartung.com>
 * @package     Joomla
 * @subpackage  Joomoocomments
 * @copyright   Copyright (C) 2010 Tom Hartung. All rights reserved.
 * @since       1.5
 * @license     GNU/GPL, see LICENSE.php
 */
/*
 * default.php: when task not handled by controller (e.g., it's blank), display rows from table
 * --------------------------------------------------------------------------------------------
 * call model code to get data from DB
 * call function defined in this file to produce the HTML
 */

defined( '_JEXEC' ) or die( 'Restricted access' );      // no direct access

JToolBarHelper::title( JText::_( 'Joomla Mootools Comments: Manage Comments' ), 'generic.png' );
$document = & JFactory::getDocument();
$document->setTitle(JText::_('Joomla Mootools Comments: Manage Comments'));

//
// Get data from the model and list the rows
//
// $tableName  =& $this->get( 'tableName'  );       // calls getTableName () in the model
// print "default.php: tableName = $tableName.<br />\n";

$rows            =& $this->get( 'Rows' );                // calls getRows() in the model
$pagination      =& $this->get( 'Pagination' );          // calls getPagination() in the model
$contentids      =& $this->get( 'Contentids' );          // calls getContentids() in the model
$gallerygroupids =& $this->get( 'Gallerygroupids' );     // calls getGallerygroupids() in the model
$galleryimageids =& $this->get( 'Galleryimageids' );     // calls getGalleryimageids() in the model
$lists           =& $this->get( 'lists' );               // calls getLists() in the model

listRows( $rows, $pagination, $contentids, $gallerygroupids, $galleryimageids, $lists );

/**
 * outputs HTML to display the list of rows
 * @return void
 */
function listRows( $rows, $pagination, $contentids, $gallerygroupids, $galleryimageids, $lists )
{
	$option = JRequest::getCmd('option');

	// print "listRows function for images: option: \"" . $option . "\"<br />\n";

	jimport( 'joomla.filter.output' );

	//
	// contentids is an array that also includes the article titles
	// contentids is in the format needed by the drop down (in lists['contentid'])
	// Here we create a new array containing the contentid values as keys
	// Each (numeric) contentid references the appropriate article title
	//
	$contentTitles = array();
	foreach( $contentids as $contentidAndTitle )
	{
		$contentid = $contentidAndTitle->value;
		$contentTitle = $contentidAndTitle->text;
		$contentTitles[$contentid] = $contentTitle;
		// print "contentid = $contentid;<br />\n";
		// print "contentTitle = $contentTitle;<br />\n";
		// print "contentTitles[$contentid] = $contentTitles[$contentid];<br />\n";
	}

	//	print_r( $gallerygroupids );
	//	print "<br />";
	//	print "<br />";

	//
	// gallerygroupids is an array similar to contentids above - for more info. see those comments
	//
	$gallerygroupTitles = array();
	foreach( $gallerygroupids as $gallerygroupidAndTitle )
	{
		$gallerygroupid = $gallerygroupidAndTitle->value;
		$gallerygroupTitle = $gallerygroupidAndTitle->text;
		$gallerygroupTitles[$gallerygroupid] = $gallerygroupTitle;
		// print "gallerygroupid = $gallerygroupid;<br />\n";
		// print "contentTitle = $contentTitle;<br />\n";
		// print "galleryimageTitles[$gallerygroupid] = $galleryimageTitles[$gallerygroupid];<br />\n";
	}

	//
	// galleryimageids is an array similar to contentids above - for more info. see those comments
	//
	$galleryimageTitles = array();
	foreach( $galleryimageids as $galleryimageidAndTitle )
	{
		$galleryimageid = $galleryimageidAndTitle->value;
		$galleryimageTitle = $galleryimageidAndTitle->text;
		$galleryimageTitles[$galleryimageid] = $galleryimageTitle;
		// print "galleryimageid = $galleryimageid;<br />\n";
		// print "contentTitle = $contentTitle;<br />\n";
		// print "galleryimageTitles[$galleryimageid] = $galleryimageTitles[$galleryimageid];<br />\n";
	}

	$rowCount = count( $rows );
	$rowClassSuffix = 0;
	$maxCharsToDisplay = 200;

	print '<form action="index.php" method="post" name="adminForm" id="adminForm">' . "\n";
	print ' <table>' . "\n";
	print '  <tr>' . "\n";

	print '   <td align="left" width="100%">';
	echo JText::_('filter');
	print '   <input type="text" name="filter_search" id="search" ';
	print      'value="' . $lists['search'] . '" ';
	print      'class="text_area" onchange="document.adminForm.submit();" />' . "\n";
	print '   <button onclick="this.form.submit();">';
	echo       JText::_('Go');
	print    '</button>' . "\n";
	print '   <button onclick="document.adminForm.filter_search.value=' . "''" . ';';
	print        'this.form.submit();">';
	echo       JText::_('Reset');
	print    '</button>' . "\n";
	print '   </td>';

	print '   <td nowrap="nowrap">';
	echo  $lists['contentid'];       // requires contentids (function parameter) see setupContentidFiltering
	print "\n";

	if ( isset($lists['gallerygroupid']) )
	{
		print '   <td nowrap="nowrap">';
		echo  $lists['gallerygroupid'];  // requires gallerygroupids (function parameter) see setupGallerygroupidFiltering
		print "\n";
	}

	print '   <td nowrap="nowrap">';
	echo  $lists['galleryimageid'];  // requires galleryimageids (function parameter) see setupGalleryimageidFiltering
	print "\n";

	print '   <td nowrap="nowrap">';
	echo  $lists['state'];
	print "\n";
	print '  </tr>' . "\n";
	print ' </table>' . "\n";

	print ' <table class="adminlist">' . "\n";
	print '  <tr>' . "\n";
	print '   <th width="15px" style="text-align: center;">';
	echo  JHTML::_('grid.sort', 'Id', 'id', $lists['order_Dir'], $lists['order']);
	print '</th>' . "\n";

	print '   <th width="15px" style="text-align: center">' . "\n";
	print '    <input type="checkbox" name="toggle" value="" ';
	print       'onclick="checkAll(' . count($rows) . ');" />' . "\n";
	print '   </th>' . "\n";

	print '   <th width="100px" style="text-align: left;">';
	echo  JHTML::_('grid.sort',
	               'Comment (first ' . $maxCharsToDisplay . ' characters)',
	               'text',
	               $lists['order_Dir'],
	               $lists['order']);
	print    '</th>' . "\n";

	print '   <th width="15px" style="text-align: center;">';
	echo  JHTML::_('grid.sort', 'Published', 'published', $lists['order_Dir'], $lists['order']);
	print '</th>' . "\n";

	print '   <th width="15px" style="text-align: center;">';
	echo  JHTML::_('grid.sort', 'Spam', 'spam', $lists['order_Dir'], $lists['order']);
	print '</th>' . "\n";

	print '   <th width="15px" style="text-align: center;">';
	echo  JHTML::_('grid.sort', 'Likes', 'likes', $lists['order_Dir'], $lists['order']);
	print '</th>' . "\n";

	print '   <th width="15px" style="text-align: center;">';
	echo  JHTML::_('grid.sort', 'Dislikes', 'dislikes', $lists['order_Dir'], $lists['order']);
	print '</th>' . "\n";

	print '   <th width="15px" style="text-align: center;">';
	echo  JHTML::_('grid.sort', 'User Id', 'created_by', $lists['order_Dir'], $lists['order']);
	print '</th>' . "\n";

	print '   <th width="50px" style="text-align: left;">';
	echo  JHTML::_('grid.sort', 'Name', 'name', $lists['order_Dir'], $lists['order']);
	print '</th>' . "\n";

	print '   <th width="15px" style="text-align: center;">';
	echo  JHTML::_('grid.sort', 'Content Id', 'contentid', $lists['order_Dir'], $lists['order']);
	print '</th>' . "\n";

	print '   <th width="15px" style="text-align: center;">';
	echo  JHTML::_('grid.sort', 'Gallery Group Id', 'gallerygroupid', $lists['order_Dir'], $lists['order']);
	print '</th>' . "\n";

	print '   <th width="15px" style="text-align: center;">';
	echo  JHTML::_('grid.sort', 'Gallery Image Id', 'galleryimageid', $lists['order_Dir'], $lists['order']);
	print '</th>' . "\n";

	print '   <th width="75px" style="text-align: left;">';
	print 'Article or Image Title';
	print '</th>' . "\n";

	print '   <th width="20px" style="text-align: center;">';
	echo  JHTML::_('grid.sort', 'Email', 'email', $lists['order_Dir'], $lists['order']);
	print '</th>' . "\n";

	print '   <th width="20px" style="text-align: center">';
	echo  JHTML::_('grid.sort', 'Website', 'website', $lists['order_Dir'], $lists['order']);
	print '</th>' . "\n";

	print '   <th width="20px" style="text-align: center">';
	echo  JHTML::_('grid.sort', 'IP Address', 'ip_address', $lists['order_Dir'], $lists['order']);
	print '</th>' . "\n";

	print '   <th width="30px" style="text-align: left">';
	echo  JHTML::_('grid.sort', 'Timestamp', 'created', $lists['order_Dir'], $lists['order']);
	print '</th>' . "\n";

	print '   <th width="15px" style="text-align: center">';
	echo  JHTML::_('grid.sort', 'Ordering', 'ordering', $lists['order_Dir'], $lists['order']);
	print '</th>' . "\n";
	print '  </tr>' . "\n";

	for ( $rowNum = 0; $rowNum < $rowCount; $rowNum++ )
	{
		$row =& $rows[$rowNum];
		$checked = JHTML::_( 'grid.id', $rowNum, $row->id );
		$shortText = substr( $row->text, 0, $maxCharsToDisplay );
		$published = JHTML::_( 'grid.published', $row, $rowNum );
		$editRowLink = JRoute::_( 'index.php?option=' . $option . '&task=edit&cid[]='. $row->id );
		$ordering = '<input class="text_area" style="text-align: center" type="text" name="order[]" size="5" value="' .
		             $row->ordering . '" />';
		if ( 0 < $row->contentid && isset($contentTitles[$row->contentid]) )
		{
			$title = $contentTitles[$row->contentid];
		}
		else if ( 0 < $row->gallerygroupid )
		{
			$title = $gallerygroupTitles[$row->gallerygroupid];
		}
		else if ( 0 < $row->galleryimageid )
		{
			$title = $galleryimageTitles[$row->galleryimageid];
		}
		else
		{
			$title = '*** Orphaned comment ***';
		}

		print '  <tr class="row' . $rowClassSuffix . '">' . "\n";
		print '   <td style="text-align: right">';
		print '    <a href="' . $editRowLink . '">' . $row->id . "</a>\n";
		print '   </td>' . "\n";
		print '   <td style="text-align: center">' . $checked . "</td>\n";
		print '   <td style="text-align: left">' . "\n";
		print '    <a href="' . $editRowLink . '">' . $shortText . "</a>\n";
		print '   </td>' . "\n";
		print '   <td style="text-align: center">' . $published . "</td>\n";
		print '   <td style="text-align: center">' . $row->spam . "</td>\n";
		print '   <td style="text-align: center">' . $row->likes . "</td>\n";
		print '   <td style="text-align: center">' . $row->dislikes . "</td>\n";
		print '   <td style="text-align: center">' . $row->created_by . "</td>\n";
		print '   <td style="text-align: left">'   . $row->name . "</td>\n";
		print '   <td style="text-align: center">' . $row->contentid . "</td>\n";
		print '   <td style="text-align: center">' . $row->gallerygroupid . "</td>\n";
		print '   <td style="text-align: center">' . $row->galleryimageid . "</td>\n";
		print '   <td style="text-align: left">'   . $title . "</td>\n";
		print '   <td style="text-align: center">' . $row->email . "</td>\n";
		print '   <td style="text-align: center">' . $row->website . "</td>\n";
		print '   <td style="text-align: center">' . $row->ip_address . "</td>\n";
		print '   <td style="text-align: center">' . $row->created . "</td>\n";
		print '   <td style="text-align: right">'  . $ordering . "</td>\n";
		print '  </tr>' . "\n";

		$rowClassSuffix = 1 - $rowClassSuffix;      // alternates between values of 0 and 1 (to no avail!)
	}

	if ( is_a($pagination, 'JPagination') )
	{
		print '  <tfoot>' . "\n";
		print '   <td colspan="18">' . $pagination->getListFooter() . "\n";
		print '   </td>' . "\n";
		print '  </tfoot>' . "\n";
	}
	else
	{
		$pagination_class_name = get_class($pagination);
		print '  <tfoot>' . "\n";
		print '   <td colspan="18">' . "\n";
		print '     Oops, WTF, pagination is a member of the "' . $pagination_class_name . '" class?!?';
		print '   </td>' . "\n";
		print '  </tfoot>' . "\n";
	}

	print ' </table>' . "\n";

	print ' <input type="hidden" name="option" value="' . $option . '" />' . "\n";
	print ' <input type="hidden" name="task" value="" />' . "\n";
	print ' <input type="hidden" name="ctlr" value="images" />' . "\n";
	print ' <input type="hidden" name="boxchecked" value="0" />' . "\n";

	if ( is_a($pagination, 'JPagination') )
	{
		print ' <input type="hidden" name="list_limit" value="' . $pagination->limit . '" />' . "\n";
	}
	print "lists['order']: " . print_r($lists['order'],true) . "<br />\n";
	print "lists['orderDir']: " . print_r($lists['order'],true) . "<br />\n";

//	print ' <input type="hidden" name="filter_order" value="';
//	print    $lists['order'] . '" />' . "\n";
//	print ' <input type="hidden" name="filter_order_Dir" value="';
//	print    $lists['order_Dir'] . '" />' . "\n";

	print '</form>' . "\n";
	print '' . "\n";

	return;

}
?>
