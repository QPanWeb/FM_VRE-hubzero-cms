<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright 2005-2019 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Components\Tags\Admin\Controllers;

use Hubzero\Component\AdminController;
use Components\Tags\Models\Cloud;
use Components\Tags\Models\Tag;
use Components\Tags\Models\Substitute;
use Request;
use Notify;
use Cache;
use Event;
use Route;
use Lang;
use App;

/**
 * Tags controller class for managing entries
 */
class Entries extends AdminController
{
	/**
	 * Execute a task
	 *
	 * @return  void
	 */
	public function execute()
	{
		$this->registerTask('add', 'edit');
		$this->registerTask('apply', 'save');

		parent::execute();
	}

	/**
	 * List all tags
	 *
	 * @return  void
	 */
	public function displayTask()
	{
		// Incoming
		$filters = array(
			'search' => urldecode(Request::getState(
				$this->_option . '.' . $this->_controller . '.search',
				'search',
				''
			)),
			'by' => strtolower(Request::getState(
				$this->_option . '.' . $this->_controller . '.by',
				'filterby',
				'all'
			)),
			'sort' => Request::getState(
				$this->_option . '.' . $this->_controller . '.sort',
				'filter_order',
				'raw_tag'
			),
			'sort_Dir' => Request::getState(
				$this->_option . '.' . $this->_controller . '.sortdir',
				'filter_order_Dir',
				'ASC'
			)
		);

		$model = Tag::all();

		$t = $model->getTableName();
		$s = Substitute::blank()->getTableName();

		//$model
		//	->select('DISTINCT ' . $t . '.*');

		if ($filters['search'])
		{
			$filters['search'] = strtolower((string)$filters['search']);

			$model
				->join($s, $s . '.tag_id', $t . '.id', 'left')
				->whereLike($t . '.raw_tag', $filters['search'], 1)
				->orWhereLike($t . '.tag', $filters['search'], 1)
				->orWhereLike($s . '.raw_tag', $filters['search'], 1)
				->orWhereLike($s . '.tag', $filters['search'], 1)
				->resetDepth();
		}

		if ($filters['by'] == 'admin')
		{
			$model->whereEquals($t . '.admin', 1);
		}
		else if ($filters['by'] == 'user')
		{
			$model->whereEquals($t . '.admin', 0);
		}

		// The query used for getting a total record count in
		// the paginated() method has a flaw in that it will return
		// the number of JOINS from substitutions, rather than the
		// actual number of tags.
		//
		// So, shenanigans happen here:
		$modelc = $model->copy();

		$modelc
			->select('COUNT(DISTINCT ' . $t . '.id)', 'count');

		$first = $modelc->rows(false)->first();
		$total = $first ? (int)$first->count : 0;

		$model
			->select('DISTINCT ' . $t . '.*');

		$model->pagination = \Hubzero\Database\Pagination::init($model->getModelName(), $total, 'limitstart', 'limit');
		$model->start($model->pagination->start);
		$model->limit($model->pagination->limit);

		// Get records
		$rows = $model
			->order($t . '.' . $filters['sort'], $filters['sort_Dir'])
			//->paginated('limitstart', 'limit')
			->rows();

		// Output the HTML
		$this->view
			->set('filters', $filters)
			->set('rows', $rows)
			->display();
	}

	/**
	 * Edit an entry
	 *
	 * @param   object  $tag  Tag being edited
	 * @return  void
	 */
	public function editTask($tag=null)
	{
		if (!User::authorise('core.edit', $this->_option)
		 && !User::authorise('core.create', $this->_option))
		{
			App::abort(403, Lang::txt('JERROR_ALERTNOAUTHOR'));
		}

		Request::setVar('hidemainmenu', 1);

		// Load a tag object if one doesn't already exist
		if (!is_object($tag))
		{
			// Incoming
			$id = Request::getArray('id', array(0));
			if (is_array($id) && !empty($id))
			{
				$id = $id[0];
			}

			$tag = Tag::oneOrNew(intval($id));
		}

		// Output the HTML
		$this->view
			->set('tag', $tag)
			->setLayout('edit')
			->display();
	}

	/**
	 * Save an entry
	 *
	 * @return  void
	 */
	public function saveTask()
	{
		// Check for request forgeries
		Request::checkToken();

		// Permissions check
		if (!User::authorise('core.edit', $this->_option)
		 && !User::authorise('core.create', $this->_option))
		{
			App::abort(403, Lang::txt('JERROR_ALERTNOAUTHOR'));
		}

		// Incoming
		$fields = Request::getArray('fields', array(), 'post');

		$subs = '';
		if (isset($fields['substitutions']))
		{
			$subs = $fields['substitutions'];
			unset($fields['substitutions']);
		}

		// Bend the data
		$row = Tag::oneOrNew(intval($fields['id']))->set($fields);

		$row->set('admin', 0);
		if (isset($fields['admin']) && $fields['admin'])
		{
			$row->set('admin', $fields['admin']);
		}

		// Trigger before save event
		$isNew  = $row->isNew();
		$result = Event::trigger('tags.onTagBeforeSave', array(&$row, $isNew));

		if (in_array(false, $result, true))
		{
			Notify::error($row->getError());
			return $this->editTask($row);
		}

		// Save content
		if (!$row->save())
		{
			Notify::error($row->getError());
			return $this->editTask($row);
		}

		if (!$row->saveSubstitutions($subs))
		{
			Notify::error($row->getError());
			return $this->editTask($row);
		}

		// Trigger after save event
		Event::trigger('tags.onTagAfterSave', array(&$row, $isNew));

		// Notify of success
		Notify::success(Lang::txt('COM_TAGS_TAG_SAVED'));

		// Redirect to main listing or go back to edit form
		if ($this->getTask() == 'apply')
		{
			return $this->editTask($row);
		}

		$this->cancelTask();
	}

	/**
	 * Remove one or more entries
	 *
	 * @return  void
	 */
	public function removeTask()
	{
		// Check for request forgeries
		Request::checkToken();

		// Permissions check
		if (!User::authorise('core.delete', $this->_option))
		{
			App::abort(403, Lang::txt('JERROR_ALERTNOAUTHOR'));
		}

		$ids = Request::getArray('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		// Make sure we have an ID
		if (empty($ids))
		{
			Notify::warning(Lang::txt('COM_TAGS_ERROR_NO_ITEMS_SELECTED'));

			return $this->cancelTask();
		}

		foreach ($ids as $id)
		{
			$id = intval($id);

			// Trigger before delete event
			Event::trigger('tags.onTagDelete', array($id));

			// Remove the tag
			$tag = Tag::oneOrFail($id);
			$tag->destroy();
		}

		$this->cleancacheTask(false);

		Notify::success(Lang::txt('COM_TAGS_TAG_REMOVED'));

		$this->cancelTask();
	}

	/**
	 * Clean cached tags data
	 *
	 * @param   boolean  $redirect  Redirect after?
	 * @return  void
	 */
	public function cleancacheTask($redirect=true)
	{
		Cache::clean('tags');

		if (!$redirect)
		{
			return true;
		}

		$this->cancelTask();
	}

	/**
	 * Merge two tags into one
	 *
	 * @return  void
	 */
	public function mergeTask()
	{
		// Permissions check
		if (!User::authorise('core.edit', $this->_option)
		 && !User::authorise('core.manage', $this->_option))
		{
			App::abort(403, Lang::txt('JERROR_ALERTNOAUTHOR'));
		}

		// Incoming
		$ids = Request::getArray('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$step = Request::getInt('step', 1);
		$step = ($step) ? $step : 1;

		// Make sure we have some IDs to work with
		if ($step == 1
		&& (!$ids || count($ids) < 1))
		{
			return $this->cancelTask();
		}

		$idstr = implode(',', $ids);

		switch ($step)
		{
			case 1:
				Request::setVar('hidemainmenu', 1);

				$tags = array();

				// Loop through the IDs of the tags we want to merge
				foreach ($ids as $id)
				{
					// Add the tag object to an array
					$tags[] = Tag::oneOrFail(intval($id));
				}

				// Output the HTML
				$this->view
					->set('step', 2)
					->set('idstr', $idstr)
					->set('tags', $tags)
					->display();
			break;

			case 2:
				// Check for request forgeries
				Request::checkToken();

				// Get the string of tag IDs we plan to merge
				$ind = Request::getString('ids', '', 'post');
				if ($ind)
				{
					$ids = explode(',', $ind);
				}
				else
				{
					$ids = array();
				}

				// Incoming
				$tag_exist = Request::getInt('existingtag', 0, 'post');
				$tag_new   = Request::getString('newtag', '', 'post');

				// Are we merging tags into a totally new tag?
				if ($tag_new)
				{
					// Yes, we are
					$newtag = Tag::oneByTag($tag_new);
					if (!$newtag->get('id'))
					{
						$newtag->set('raw_tag', $tag_new);
					}
					if (!$newtag->save())
					{
						$this->setError($newtag->getError());
					}
					$mtag = $newtag->get('id');
				}
				else
				{
					// No, we're merging into an existing tag
					$existtag = Tag::oneOrFail($tag_exist);
					$mtag = $existtag->get('id');
				}

				if ($this->getError())
				{
					Notyf::error($this->getError());
					return $this->cancelTask();
				}

				if (!$mtag)
				{
					Notify::error(Lang::txt('Failed to find destination tag.'));
					return $this->cancelTask();
				}

				foreach ($ids as $id)
				{
					if ($mtag == $id)
					{
						continue;
					}

					$oldtag = Tag::oneOrFail(intval($id));

					if (!$oldtag->mergeWith($mtag))
					{
						$this->setError($oldtag->getError());
					}
				}

				if ($this->getError())
				{
					Notify::error($this->getError());
				}
				else
				{
					Notify::success(Lang::txt('COM_TAGS_TAGS_MERGED'));
				}

				$this->cancelTask();
			break;
		}
	}

	/**
	 * Copy all tag associations from one tag to another
	 *
	 * @return  void
	 */
	public function pierceTask()
	{
		// Permissions check
		if (!User::authorise('core.edit', $this->_option)
		 && !User::authorise('core.manage', $this->_option))
		{
			App::abort(403, Lang::txt('JERROR_ALERTNOAUTHOR'));
		}

		// Incoming
		$ids = Request::getArray('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$step = Request::getInt('step', 1);
		$step = ($step) ? $step : 1;

		// Make sure we have some IDs to work with
		if ($step == 1
		 && (!$ids || count($ids) < 1))
		{
			return $this->cancelTask();
		}

		$idstr = implode(',', $ids);

		switch ($step)
		{
			case 1:
				Request::setVar('hidemainmenu', 1);

				$tags = array();

				// Loop through the IDs of the tags we want to merge
				foreach ($ids as $id)
				{
					// Load the tag's info
					$tags[] = Tag::oneOrFail(intval($id));
				}

				// Output the HTML
				$this->view
					->set('step', 2)
					->set('idstr', $idstr)
					->set('tags', $tags)
					->display();
			break;

			case 2:
				// Check for request forgeries
				Request::checkToken();

				// Get the string of tag IDs we plan to merge
				$ind = Request::getString('ids', '', 'post');
				if ($ind)
				{
					$ids = explode(',', $ind);
				}
				else
				{
					$ids = array();
				}

				// Incoming
				$tag_exist = Request::getInt('existingtag', 0, 'post');
				$tag_new   = Request::getString('newtag', '', 'post');

				// Are we merging tags into a totally new tag?
				if ($tag_new)
				{
					// Yes, we are
					$newtag = Tag::oneByTag($tag_new);
					if (!$newtag->get('id'))
					{
						$newtag->set('raw_tag', $tag_new);
					}
					if (!$newtag->save())
					{
						$this->setError($newtag->getError());
					}
					$mtag = $newtag->get('id');
				}
				else
				{
					// No, we're merging into an existing tag
					$mtag = $tag_exist;
				}

				foreach ($ids as $id)
				{
					if ($mtag == $id)
					{
						continue;
					}

					$oldtag = Tag::oneOrFail(intval($id));
					if (!$oldtag->copyTo($mtag))
					{
						$this->setError($oldtag->getError());
					}
				}

				if ($this->getError())
				{
					Notify::error($this->getError());
				}
				else
				{
					Notify::success(Lang::txt('COM_TAGS_TAGS_COPIED'));
				}

				$this->cancelTask();
			break;
		}
	}

	/**
	 * Re-calculate associated content for one or more tags
	 *
	 * @return  void
	 */
	public function calculateTask()
	{
		$ids = Request::getArray('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		// Make sure we have an ID
		if (empty($ids))
		{
			Notify::warning(Lang::txt('COM_TAGS_ERROR_NO_ITEMS_SELECTED'));

			return $this->cancelTask();
		}

		$success = 0;
		foreach ($ids as $id)
		{
			$id = intval($id);

			// Recalculate associated content
			$tag = Tag::oneOrFail($id);
			if ($tag->hasAttribute('objects'))
			{
				$tag->set('objects', $tag->objects()->total());
			}
			if ($tag->hasAttribute('substitutes'))
			{
				$tag->set('substitutes', $tag->substitutes()->total());
			}

			if (!$tag->save())
			{
				Notify::error($tag->getError());
				continue;
			}

			$success++;
		}

		if ($success)
		{
			Notify::success(Lang::txt('COM_TAGS_TAGS_UPDATED', $success));
		}

		$this->cancelTask();
	}
}
