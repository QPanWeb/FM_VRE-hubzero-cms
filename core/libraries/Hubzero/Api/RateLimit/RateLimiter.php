<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2015 Purdue University. All rights reserved.
 *
 * This file is part of: The HUBzero(R) Platform for Scientific Collaboration
 *
 * The HUBzero(R) Platform for Scientific Collaboration (HUBzero) is free
 * software: you can redistribute it and/or modify it under the terms of
 * the GNU Lesser General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * HUBzero is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * HUBzero is a registered trademark of Purdue University.
 *
 * @package   hubzero-cms
 * @author    Christopher Smoak <csmoak@purdue.edu>
 * @copyright Copyright 2015 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

namespace Hubzero\Api\RateLimit;

use Hubzero\Api\RateLimit\Storage\StorageInterface;
use Hubzero\Utility\Date;

/**
 * Rate Limiter
 */
class RateLimiter
{
	/**
	 * Storage object
	 * 
	 * @var  object
	 */
	private $storage;

	/**
	 * Configuration
	 * 
	 * @var  array
	 */
	private $config;

	/**
	 * New rate limiter instance
	 * 
	 * @param   object  $storage  Storage object
	 * @return  void
	 */
	public function __construct(StorageInterface $storage, $config = [])
	{
		$this->storage = $storage;
		$this->config  = array_merge([
			'short' => [
				'period' => 1, // 1 minute
				'limit'  => 60 // 60 requests
			],
			'long' => [
				'period' => 1440, // 1 day (in minutes)
				'limit'  => 10000 // 10,000 requests
			]
		], $config);
	}

	/**
	 * Rate limit for application & user
	 * 
	 * @param  int    $applicationId  Application identifier
	 * @param  int    $userId         User identifier
	 * @return array                  Array of rate limit data
	 */
	public function rateLimit($applicationId, $userId)
	{
		// load limit data, creating initial record if doesnt exist
		if (!$data = $this->storage->getRateLimitData($applicationId, $userId))
		{
			$data = $this->createRateLimitData($applicationId, $userId);
		}

		// check if we can reset short expiration
		if (time() > with(new Date($data->expires_short))->toUnix())
		{
			$newShortDate = $this->getNewExpiresDateString('short');
			$this->storage->resetShort($data->id, 0, $newShortDate);
		}

		// check if we can reset long expiration
		if (time() > with(new Date($data->expires_long))->toUnix())
		{
			$newLongDate = $this->getNewExpiresDateString('long');
			$this->storage->resetLong($data->id, 0, $newLongDate);
		}

		// increment data then refetch
		$this->storage->incrementRateLimitData($data->id);

		// refetch record after incrementing
		$data = $this->storage->getRateLimitData($applicationId, $userId);

		// check to see if were over short or long limits
		$data->exceeded_short = false;
		$data->exceeded_long  = false;
		if ($data->count_short >= $data->limit_short)
		{
			$data->exceeded_short = true;
		}
		if ($data->count_long >= $data->limit_long)
		{
			$data->exceeded_long = true;
		}

		// return data
		return $data;
	}

	/**
	 * Create initial limit data
	 * 
	 * @param  int    $applicationId  Application identifier
	 * @param  int    $userId         User identifier
	 * @return array                  Array of rate limit data
	 */
	private function createRateLimitData($applicationId, $userId)
	{
		// data needed to create record
		$ipAddress    = \Request::ip();
		$countShort   = 0;
		$countLong    = 0;
		$limitShort   = $this->config['short']['limit'];
		$limitLong    = $this->config['long']['limit'];
		$created      = with(new Date('now'))->toSql();
		$expiresShort = $this->getNewExpiresDateString('short');
		$expiresLong  = $this->getNewExpiresDateString('long');

		// create initial limit record
		return $this->storage->createRateLimitData(
			$applicationId, 
			$userId, 
			$ipAddress,
			$limitShort,
			$limitLong,
			$countShort,
			$countLong,
			$expiresShort,
			$expiresLong,
			$created
		);
	}

	/**
	 * Get new expires date string
	 * 
	 * @param  string $type Short or long period
	 * @return string       Date string
	 */
	private function getNewExpiresDateString($type = 'short')
	{
		$modifier = $this->config[$type]['period'];
		return with(new Date('now'))->modify('+' . $modifier . ' MINUTES')->toSql();
	}
}