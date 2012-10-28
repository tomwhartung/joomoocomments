<?php
/**
 * @version     $Id: joomoocomments.php,v 1.8 2008/10/31 06:15:48 tomh Exp tomh $
 * @author      Tom Hartung <webmaster@tomhartung.com>
 * @package     Joomla
 * @subpackage  Joomoocomments
 * @link        http://dev.joomla.org/component/option,com_jd-wiki/Itemid,31/id,tutorials:components/
 * @copyright   Copyright (C) 2008 Tom Hartung. All rights reserved.
 * @since       1.5
 * @license     GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport( 'joomla.application.component.model' );

/**
 * Joomoocomments Model for com_joomoocomments component
 * =====================================================
 *
 * =====================================================
 * *** Note: base class is in joomoobase project!!!! ***
 * =====================================================
 */
class JoomoocommentsModelJoomoocomments extends JoomoobaseModelJoomoobaseDb
{
	/**
	 * ids of articles in the #__content table
	 * @access private
	 * @var array
	 */
	private $_contentids = array();
	/**
	 * ids of groups in the #__joomoogallerygroups table
	 * @access private
	 * @var array
	 */
	private $_gallerygroupids = array();
	/**
	 * ids of images in the #__joomoogalleryimages table
	 * @access private
	 * @var array
	 */
	private $_galleryimageids = array();

	/**
	 * Overridden constructor
	 * @access public
	 */
	public function __construct()
	{
		parent::__construct();

		// print "Hello from JoomoocommentsModelJoomoocomments::__construct()<br />\n";

		$this->_tableName = "#__joomoocomments";
	}

	/**
	 * get array of ids of articles posted to front page
	 * @access public
	 * @return array of integers - id column values from #__content
	 */
	public function getContentids( )
	{
		// print "Hello from JoomoocommentsModelJoomoocomments::getContentids()<br />\n";

		if ( count($this->_contentids) == 0 )
		{
			$query = 'SELECT fp.content_id AS value, cont.title AS text ' .
			    ' FROM #__content_frontpage AS fp, #__content AS cont ' .
				' WHERE fp.content_id = cont.id ' .
			    ' ORDER BY cont.created DESC;';
			$db =& $this->getDBO();
			$db->setQuery( $query );
			$this->_contentids = $db->loadObjectList();
		}

		return $this->_contentids;
	}
	/**
	 * get array of ids of gallery groups that can have comments
	 * @access public
	 * @return array of integers - id column values from #__joomoogallerygroups
	 */
	public function getGallerygroupids( )
	{
		// print "Hello from JoomoocommentsModelJoomoocomments::getGallerygroupids()<br />\n";

		if ( count($this->_gallerygroupids) == 0 )
		{
			$query = 'SELECT id AS value, title AS text ' .
			    ' FROM #__joomoogallerygroups ' .
			    ' WHERE comments = 1 ';
			    ' ORDER BY title ASC;';
			$db =& $this->getDBO();
			$db->setQuery( $query );
			$this->_gallerygroupids = $db->loadObjectList();
		}

		return $this->_gallerygroupids;
	}
	/**
	 * get array of ids of gallery images that can have comments
	 * @access public
	 * @return array of integers - id column values from #__joomoogalleryimages
	 */
	public function getGalleryimageids( )
	{
		// print "Hello from JoomoocommentsModelJoomoocomments::getGalleryimageids()<br />\n";

		if ( count($this->_galleryimageids) == 0 )
		{
			$query = 'SELECT id AS value, title AS text ' .
			    ' FROM #__joomoogalleryimages ' .
			    ' WHERE comments = 1 ';
			    ' ORDER BY title ASC;';
			$db =& $this->getDBO();
			$db->setQuery( $query );
			$this->_galleryimageids = $db->loadObjectList();
		}

		return $this->_galleryimageids;
	}

	/**
	 * store a comment
	 * @access public
	 * @return boolean True on success else false
	 */
	public function store( $autopub_anonymous=FALSE )
	{
		// print "Hello from JoomoocommentsModelJoomoocomments::store()<br />\n";

		$data = JRequest::get( 'post' );

		$check_hp = $data['check_hp'];
		$value_to_check = $data[HONEYPOT_FIELD_NAME];

		if ( $check_hp && strlen($value_to_check) > 0 )
		{
			// print "Assuming a spambot filled in field that should be blank.  Silently ignoring comment!<br />";
			return true;
		}

		$email_on_form = $data['email_on_form'];
		$website_on_form = $data['website_on_form'];

		$user = & JFactory::getUser();

		if ( $user->id )
		{
			$data['created_by'] = $user->id;
			$data['published'] = 1;
		}
		else
		{
			$data['created_by'] = 0;
			$data['name'] = NAME_FOR_ANONYMOUS_USERS;
			$autopub_anonymous ? $data['published'] = 1 : $data['published'] = 0;
		}

		if ( strlen($data['name']) < 1 )
		{
			$data['name'] = $user->name;
		}

		// print '<br />Running print_r on data array (after additions):';
		// print '<br />------------------------------------------------<br />';
		// print print_r( $data, TRUE );
		// print '<br />--------------------------------------------------------';
		// print '<br />End of print_r output for data object (after additions).';
		// print '<br />--------------------------------------------------------<br />';

		$this->_row =& $this->getTable();   // so we can run methods in tables/joomoocomments.php

		if ( !$this->_row->bind($data) )    // Bind the data to the table
		{
			$this->setError( "bind error: " . $this->_row->getError() );
			return FALSE;
		}

		// print "store calling check:<br />\n";
		if ( !$this->_row->check($email_on_form,$website_on_form) )        // Make sure the data is valid
		{
			$this->setError( $this->_row->getError() );
			return FALSE;
		}

		return parent::store( $data );
	}

	//
	// Relatively standard methods customized for this table
	//
	/**
	 * create lists array containing ordering and filtering lists
	 * @access public
	 * @return array lists to use when outputing HTML to display the list of rows
	 */
	public function getLists( )
	{
		// print "Hello from JoomoocommentsModelJoomoocomments::getLists()<br />\n";
		// print "getLists before: count(this->_lists) = " . count($this->_lists) . "<br />\n";

		if ( count($this->_lists) == 0 )
		{
			$this->_setupContentidFiltering( );
			$this->_setupGalleryimageidFiltering( );
			$this->_setupGallerygroupidFiltering( );
			parent::getLists();
		//	print "getLists after: count(this->_lists) = " . count($this->_lists) . "<br />\n";
		}

		return $this->_lists;
	}
	/**
	 * builds order by clause for _listquery (implements ordering) - from p. 230 of Mastering book
	 * @access protected
	 * @return: order by clause for query
	 */
	protected function _buildQueryOrderBy()
	{
		// print "Hello from JoomoocommentsModelJoomoocomments::_buildQueryOrderBy()<br />\n";
		//
		// array of fields that can be sorted in back end:
		//
		$orderByColumns = array(
			'id',
			'created_by',
			'name',
			'text',
			'published',
			'contentid',
			'gallerygroupid',
			'galleryimageid',
			'created',
			'email',
			'website',
			'ip_address',
			'likes',
			'dislikes',
			'spam',
			'ordering'
		);
		$orderByClause = $this->_getOrderByClause( $orderByColumns );

		// print "_buildQueryOrderBy: returning orderByClause = \"$orderByClause\"<br />\n";

		return $orderByClause;
	}
	/**
	 * builds order by clause for _listquery (implements ordering)
	 * @access protected
	 * @return: order by clause for query
	 */
	protected function _getOrderByClause( $orderByColumns )
	{
		//	print "Hello from JoomoocommentsModelJoomoocomments::_getOrderByClause()<br />\n";

		$default_filter_order = 'created';
		$orderByClause = parent::_getOrderByClause( $orderByColumns, $default_filter_order );

		//	print "JoomoocommentsModelJoomoocomments::_getOrderByClause: returning orderByClause = \"$orderByClause\"<br />\n";

		return $orderByClause;
	}

	/**
	 * builds where clause for _listquery (implements filtering) - from pp. 233-240 of Mastering book
	 * @access protected
	 * @return: where clause for query
	 */
	protected function _buildQueryWhere()
	{
		$app = JFactory::getApplication();
		$option = JRequest::getCmd('option');

		// print "Hello from JoomoocommentsModelJoomoocomments::_buildQueryWhere()<br />\n";
		//
		// get the filter for the search state ([un]published)
		// get the filter for the contentid value
		// get the filter for the gallerygroupid value
		// get the filter for the galleryimageid value
		// set the where clause based on the filters
		//
		$whereClause = '';
		$whereConstraint = $this->_getSearchAndStateConstraints();

		$filter_contentid = $app->getUserStateFromRequest( $option.'filter_contentid', 'filter_contentid' );
		$filter_contentid = (int)$filter_contentid;
		if ( $filter_contentid )
		{
			$whereConstraint['contentid'] = ' contentid = ' . $filter_contentid;
		}

		$filter_gallerygroupid = $app->getUserStateFromRequest( $option.'filter_gallerygroupid', 'filter_gallerygroupid' );
		$filter_gallerygroupid = (int)$filter_gallerygroupid;
		if ( $filter_gallerygroupid )
		{
			$whereConstraint['gallerygroupid'] = ' gallerygroupid = ' . $filter_gallerygroupid;
		}

		$filter_galleryimageid = $app->getUserStateFromRequest( $option.'filter_galleryimageid', 'filter_galleryimageid' );
		$filter_galleryimageid = (int)$filter_galleryimageid;
		if ( $filter_galleryimageid )
		{
			$whereConstraint['galleryimageid'] = ' galleryimageid = ' . $filter_galleryimageid;
		}

		$constraintCount = 0;

		foreach ( $whereConstraint as $constraint )
		{
			if ( $constraintCount == 0 )
			{
				$whereClause = ' WHERE ';
				$constraintCount++;
			}
			else
			{
				$whereClause .= ' AND ';
			}
			$whereClause .= $constraint;
		}

		// print "_buildQueryWhere: returning whereClause = \"$whereClause\"<br />\n";
		return $whereClause;
	}

	/**
	 * Set up filtering on contentid
	 * @return array including contentid element
	 */
	private function _setupContentidFiltering( )
	{
		$app = JFactory::getApplication();
		$option = JRequest::getCmd('option');

		// print "Hello from _setupContentidFiltering<br />\n";
		//
		// add the first select option then append the contentids from the DB
		// build the form control (select list)
		//
		$options = array();
		$filter_contentid = $app->getUserStateFromRequest( $option.'filter_contentid', 'filter_contentid' );
		$js = "onchange=\"if (this.options[selectedIndex].value!=''){document.adminForm.submit();}\"";
		$options[] = JHTML::_('select.option', '0', '- '.JText::_('Select Article Title').' -' );
		$options = array_merge( $options, $this->_contentids );
		$this->_lists['contentid'] = JHTML::_('select.genericlist',
		                                     $options,
		                                     'filter_contentid',
		                                     'class="inputbox" size="1" '.$js,
		                                     'value', 'text', $filter_contentid);
		return $this->_lists;
	}
	/**
	 * Set up filtering on gallerygroupid
	 * @return array including gallerygroupid element
	 */
	private function _setupGallerygroupidFiltering( )
	{
		$app = JFactory::getApplication();
		$option = JRequest::getCmd('option');

		// print "Hello from _setupGalleryimageidFiltering<br />\n";
		//
		// add the first select option then append the gallerygroupids from the DB
		// build the form control (select list)
		//
		$options = array();
		$filter_gallerygroupid = $app->getUserStateFromRequest( $option.'filter_gallerygroupid', 'filter_gallerygroupid' );
		$js = "onchange=\"if (this.options[selectedIndex].value!=''){document.adminForm.submit();}\"";
		$options[] = JHTML::_('select.option', '0', '- '.JText::_('Select Gallery Group Title').' -' );
		$options = array_merge( $options, $this->_gallerygroupids );
		$this->_lists['gallerygroupid'] = JHTML::_('select.genericlist',
		                                     $options,
		                                     'filter_gallerygroupid',
		                                     'class="inputbox" size="1" '.$js,
		                                     'value', 'text', $filter_gallerygroupid);
		return $this->_lists;
	}
	/**
	 * Set up filtering on galleryimageid
	 * @return array including galleryimageid element
	 */
	private function _setupGalleryimageidFiltering( )
	{
		$app = JFactory::getApplication();
		$option = JRequest::getCmd('option');

		// print "Hello from _setupGalleryimageidFiltering<br />\n";
		//
		// add the first select option then append the galleryimageids from the DB
		// build the form control (select list)
		//
		$options = array();
		$filter_galleryimageid = $app->getUserStateFromRequest( $option.'filter_galleryimageid', 'filter_galleryimageid' );
		$js = "onchange=\"if (this.options[selectedIndex].value!=''){document.adminForm.submit();}\"";
		$options[] = JHTML::_('select.option', '0', '- '.JText::_('Select Gallery Image Title').' -' );
		$options = array_merge( $options, $this->_galleryimageids );
		$this->_lists['galleryimageid'] = JHTML::_('select.genericlist',
		                                     $options,
		                                     'filter_galleryimageid',
		                                     'class="inputbox" size="1" '.$js,
		                                     'value', 'text', $filter_galleryimageid);
		return $this->_lists;
	}
}
?>
