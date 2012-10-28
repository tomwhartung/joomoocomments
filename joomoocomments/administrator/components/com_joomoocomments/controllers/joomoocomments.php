<?php
/**
 * @version     $Id: joomoocomments.php,v 1.13 2008/10/31 18:06:42 tomh Exp tomh $
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
 * joomoocomments component controller
 */
class JoomoocommentsController extends JController
{
	/**
	 * name of the model class this controller uses
	 * @access protected
	 * @var string
	 */
	protected $_modelName = '';

	/**
	 * Constructor
	 */
	public function __construct( $default = array() )
	{
		// print( "Hello from JoomoocommentsController::__construct()<br />\n" );

		parent::__construct( $default );

		$this->registerTask( 'add',   'edit' );   // when $task = 'add' framework calls edit() function in controller
		$this->registerTask( 'apply', 'save' );   // similarly, $task = 'apply' calls save()
		$this->registerTask( 'unpublish', 'publish' );

		$this->_modelName = 'Joomoocomments';
	}

	/**
	 * get model and view for component and call display() in view
	 * --> called by framework when task is not handled by another method in this class (i.e. when task is blank)
	 * @access public
	 * @return void
	 */
	public function display()
	{
		// print "Hello from JoomoocommentsController::display()<br />\n";
		// $this->printTask();

		$model =& $this->getModel( $this->getModelName() );         // instantiates model class
		$view  =& $this->getView( 'Joomoocomments', 'html' );       // 'html': use view.html.php (not view.php)
		$view->setModel( $model, true );                            // true: this is the default model

		$view->display();
	}

	/**
	 * display the single-record edit form - views/joomoocomments/tmpl/form.php
	 * --> called by framework when task is 'edit' (or 'add', when that task is registered as such)
	 * @access public
	 * @return void
	 * @link http://docs.joomla.org/Tutorial:Developing_a_Model-View-Controller_Component_-_Part_4_-_Creating_an_Administrator_Interface
	 */
	public function edit()
	{
		// print "Hello from JoomoocommentsController::edit()<br />\n";
		// $this->printTask();
		// $this->printTaskArray();

		JRequest::setVar( 'view', 'joomoocomments' );     // sets view directory to views/joomoocomments/
		JRequest::setVar( 'layout', 'edit_row' );         // sets layout (view file) to (view directory)/tmpl/edit_row.php
	 	JRequest::setVar( 'hidemainmenu', 1 );            // turn off main menu while we are editing a single row
 
		parent::display();
	}

	/**
	 * Returns name of this controller's model
	 * @return string name of model for this component
	 */
	public function getModelName()
	{
		// print "Hello from JoomoocommentsController::getModelName()<br />\n";
		// print "returning this->_modelName = \"$this->_modelName\" <br />\n";

		return $this->_modelName;
	}

	/**
	 * save a row and redirect to main page
	 * --> called by framework when task is 'save'
	 * @access public
	 * @return void
	 */
	public function save()
	{
		$option = JRequest::getCmd('option');

		// print "Hello from JoomoocommentsController::save()<br />\n";

		$model = $this->getModel( $this->getModelName() );

		if ( $model->store() )
		{
			$message = JText::_( 'Joomoo Comments data saved OK!' );
			$storedOk = True;
		}
		else
		{
			$message  = JText::_( 'Error saving Joomoo Comments data: ' );
			$message .= JText::_( $model->getError() );
			$storedOk = False;
		}

		$task = $this->getTask();
		$link = 'index.php?option=' . $option;
 
		if ( $task == 'apply' || ! $storedOk )
 		{
			$id = $model->getId();
			$link .= '&task=edit&cid[]=' . $id;
		}

		$this->setRedirect( $link, $message );
	}

	/**
	 * Sets published flag in DB to appropriate value
	 * --> runs when user clicks on (Un)Published icon in listing of rows
	 * --> also runs when user checks on one or more rows and clicks on Publish or Unpublish
	 * @access public
	 * @return void
	 */
	public function publish( )
	{
		$option = JRequest::getCmd('option');

		if ( $this->getTask() == 'unpublish' )
		{
			$published = 0;
		}
		else
		{
			$published = 1;
		}

		$model = $this->getModel( $this->getModelName() );
		$model->setPublished( $published );

		$link = 'index.php?option=' . $option;
		$this->setRedirect( $link );
	}

	/**
	 * remove record(s) (and redirect to main page) - calls delete() method in model
	 * --> called by framework when task is 'remove'
	 * @access public
	 * @return void
	 */
	public function remove()
	{
		$option = JRequest::getCmd('option');

		// print( "Hello world from JoomoocommentsimagesController::remove()<br />\n" );
		// $model = $this->getModel('');
		$model = $this->getModel( $this->getModelName() );

		if ( $model->delete() )
		{
			$message = JText::_( 'Row(s) deleted OK.' );
		}
		else
		{
			$message  = JText::_( 'Error: Unable to delete one or more rows: ' );
			$message .= JText::_( $model->getError() );
		}
 
		$link = 'index.php?option=' . $option;
		$this->setRedirect( $link, $message );
	}
	/**
	 * cancel editing a record
	 * @access public
	 * @return void
	 */
	public function cancel()
	{
		$option = JRequest::getCmd('option');

		$message = JText::_( 'Operation cancelled.' );

		// $link = 'index.php?option=' . $option . '&ctlr=images';
		$link = 'index.php?option=' . $option;
		$this->setRedirect( $link, $message );
	}

	//	//
	//	// Methods useful when learning and debugging:
	//	// -------------------------------------------
	//	//
	//	/**
	//	 * print current task (hopefully helpful when learning and debugging)
	//	 * @access protected
	//	 * @return void
	//	 */
	//	protected function printTask()
	//	{
	//		$task = $this->getTask();
	//		print "task = $task<br />\n";
	//	}
	//	/**
	//	 * print array of available tasks in controller (hopefully helpful when learning and debugging)
	//	 * an available task is a public or protected function in this class (except constructor and display)
	//	 * @access protected
	//	 * @return void
	//	 */
	//	protected function printTaskArray()
	//	{
	//		$taskArray = $this->getTasks();
	//		foreach ( $taskArray as $key => $thisTask )
	//		{
	//			print "taskArray[$key] = $thisTask<br />\n";
	//		}
	//	}
}
?>
