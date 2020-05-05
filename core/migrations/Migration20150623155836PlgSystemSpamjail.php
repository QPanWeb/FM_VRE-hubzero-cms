<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright 2005-2019 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

use Hubzero\Content\Migration\Base;

/**
 * Migration script for adding spamjail plugin
 **/
class Migration20150623155836PlgSystemSpamjail extends Base
{
	/**
	 * Up
	 **/
	public function up()
	{
		$this->addPluginEntry('system', 'spamjail');
	}

	/**
	 * Down
	 **/
	public function down()
	{
		$this->deletePluginEntry('system', 'spamjail');
	}
}
