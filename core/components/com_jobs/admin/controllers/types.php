<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Components\Jobs\Admin\Controllers;

use Components\Jobs\Tables\JobType;
use Hubzero\Component\AdminController;
use Exception;
use Request;
use Notify;
use Config;
use Route;
use Lang;
use App;

/**
 * Controller class for job types
 */
class Types extends AdminController
{
	/**
	 * Display a list of types
	 *
	 * @return     void
	 */
	public function displayTask()
	{
		$this->view->filters = array(
			// Get paging variables
			'limit' => Request::getState(
				$this->_option . '.' . $this->_controller . '.limit',
				'limit',
				Config::get('list_limit'),
				'int'
			),
			'start' => Request::getState(
				$this->_option . '.' . $this->_controller . '.limitstart',
				'limitstart',
				0,
				'int'
			),
			// Get sorting variables
			'sort' => Request::getState(
				$this->_option . '.' . $this->_controller . '.sort',
				'filter_order',
				'id'
			),
			'sort_Dir' => Request::getState(
				$this->_option . '.' . $this->_controller . '.sortdir',
				'filter_order_Dir',
				'ASC'
			)
		);

		// Instantiate an object
		$jt = new JobType($this->database);

		// Get records
		$this->view->rows = $jt->getTypes($this->view->filters['sort'], $this->view->filters['sort_Dir']);
		$this->view->total = count($this->view->rows);

		// Load default types if none found
		if (!$this->view->total)
		{
			$default = array(
				array(
					'id' => 0,
					'category' => ucfirst(Lang::txt('COM_JOBS_CATEGORY_FULLTIME'))
				),
				array(
					'id' => 0,
					'category' => ucfirst(Lang::txt('COM_JOBS_CATEGORY_PARTTIME'))
				),
				array(
					'id' => 0,
					'category' => ucfirst(Lang::txt('COM_JOBS_CATEGORY_CONTRACT'))
				),
				array(
					'id' => 0,
					'category' => ucfirst(Lang::txt('COM_JOBS_CATEGORY_INTERNSHIP'))
				),
				array(
					'id' => 0,
					'category' => ucfirst(Lang::txt('COM_JOBS_CATEGORY_TEMPORARY'))
				)
			);

			foreach ($default as $d)
			{
				if (!$jt->bind($d))
				{
					throw new Exception($jt->getError());
				}
				if (!$jt->store())
				{
					throw new Exception($jt->getError());
				}
			}

			// Get new records
			$this->view->rows = $jt->getTypes();
			$this->view->total = count($this->view->rows);
		}

		// Output the HTML
		$this->view->display();
	}

	/**
	 * Create a new type
	 * Displays the edit form
	 *
	 * @return  void
	 */
	public function addTask()
	{
		$this->editTask();
	}

	/**
	 * Edit a type
	 *
	 * @return     void
	 */
	public function editTask($row=null)
	{
		Request::setVar('hidemainmenu', 1);

		if (!is_object($row))
		{
			// Incoming (expecting an array)
			$id = Request::getArray('id', array(0));
			$id = (is_array($id)) ? $id[0] : $id;

			// Load the object
			$row = new JobType($this->database);
			$row->load($id);
		}

		$this->view->row = $row;

		// Output the HTML
		$this->view
			->setLayout('edit')
			->display();
	}

	/**
	 * Save type
	 *
	 * @return     void
	 */
	public function saveTask()
	{
		// Check for request forgeries
		Request::checkToken();

		// Initiate extended database class
		$row = new JobType($this->database);
		if (!$row->bind($_POST))
		{
			Notify::error($row->getError());
			return $this->editTask($row);
		}

		// Store new content
		if (!$row->store())
		{
			Notify::error($row->getError());
			return $this->editTask($row);
		}

		// Redirect
		App::redirect(
			Route::url('index.php?option=' . $this->_option . '&controller=' . $this->_controller, false),
			Lang::txt('COM_JOBS_ITEM_SAVED')
		);
	}

	/**
	 * Remove type
	 *
	 * @return     void
	 */
	public function removeTask()
	{
		// Check for request forgeries
		Request::checkToken();

		// Incoming (expecting an array)
		$ids = Request::getArray('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		// Ensure we have an ID to work with
		if (empty($ids))
		{
			App::redirect(
				Route::url('index.php?option=' . $this->_option . '&controller=' . $this->_controller, false),
				Lang::txt('COM_JOBS_ERROR_NO_ITEM_SELECTED'),
				'error'
			);
			return;
		}

		$jt = new JobType($this->database);

		foreach ($ids as $id)
		{
			// Delete the type
			$jt->delete(intval($id));
		}

		// Redirect
		App::redirect(
			Route::url('index.php?option=' . $this->_option . '&controller=' . $this->_controller, false),
			Lang::txt('COM_JOBS_ITEMS_REMOVED', count($ids))
		);
	}
}
