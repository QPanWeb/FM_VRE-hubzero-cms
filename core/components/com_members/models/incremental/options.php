<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Components\Members\Models\Incremental;

use Module;
use User;
use App;

/**
 * Class for incremental registration options
 */
class Options
{
	/**
	 * Database connection
	 *
	 * @var  object
	 */
	private static $current = null;

	/**
	 * Get award value per field
	 *
	 * @return  integer
	 */
	public function getAwardPerField()
	{
		$cur = self::getCurrent();
		return $cur['award_per'];
	}

	/**
	 * Check if enabled
	 *
	 * @param   integer  $uid
	 * @return  boolean
	 */
	public function isEnabled($uid = null)
	{
		if (!$uid)
		{
			$uid = (int)User::get('id');
		}
		if (!$uid || !Module::isEnabled('incremental_registration'))
		{
			return false;
		}

		$dbh = App::get('db');
		$dbh->setQuery('SELECT `activation` FROM `#__users` WHERE `id` = ' . $uid);
		if ($dbh->loadResult() < 0)
		{
			return false;
		}

		$cur = self::getCurrent();
		if (!$cur['test_group'])
		{
			return true;
		}

		$dbh->setQuery(
			'SELECT 1 FROM `#__xgroups_members` xme WHERE xme.gidNumber = ' . $cur['test_group'] . ' AND xme.uidNumber = ' . $uid . '
			UNION SELECT 1 FROM #__xgroups_managers xma WHERE xma.gidNumber = ' . $cur['test_group'] . ' AND xma.uidNumber = ' . $uid . ' LIMIT 1'
		);
		return (bool)$dbh->loadResult();
	}

	/**
	 * Check if the curl enabled
	 *
	 * @param   integer  $uid
	 * @return  boolean
	 */
	public function isCurlEnabled($uid = null)
	{
		if (!$this->isEnabled($uid))
		{
			return false;
		}

		$uid = $uid ?: (int)User::get('id');

		$dbh = App::get('db');
		$dbh->setQuery('SELECT edited_profile FROM `#__profile_completion_awards` WHERE user_id = ' . $uid);
		return !$dbh->loadResult();
	}

	/**
	 * Get the database connection
	 *
	 * @return  object
	 */
	private static function getCurrent()
	{
		if (!self::$current)
		{
			$dbh = App::get('db');
			$dbh->setQuery('SELECT * FROM `#__incremental_registration_options` ORDER BY added DESC LIMIT 1');
			self::$current = $dbh->loadAssoc();
		}
		return self::$current;
	}
}
