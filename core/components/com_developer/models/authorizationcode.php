<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Components\Developer\Models;

use Hubzero\Database\Relational;
use Hubzero\Utility\Validate;
use Hubzero\Utility\Date;
use Lang;

/**
 * Authorization code model
 */
class Authorizationcode extends Relational
{
	/**
	 * The table namespace
	 *
	 * @var string
	 */
	protected $namespace = 'developer';

	/**
	 * The table to which the class pertains
	 *
	 * This will default to #__{namespace}_{modelName} unless otherwise
	 * overwritten by a given subclass. Definition of this property likely
	 * indicates some derivation from standard naming conventions.
	 *
	 * @var  string
	 */
	protected $table = '#__developer_authorization_codes';

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public $orderBy = 'expires';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public $orderDir = 'asc';

	/**
	 * Fields and their validation criteria
	 *
	 * @var  array
	 */
	protected $rules = array(
		'authorization_code' => 'notempty',
		'uidNumber'          => 'positive|nonzero',
		'application_id'     => 'positive|nonzero'
	);

	/**
	 * Sets up additional custom rules
	 *
	 * @return  void
	 **/
	public function setup()
	{
		$this->addRule('redirect_uri', function($data)
		{
			if (!isset($data['redirect_uri']) || !$data['redirect_uri'])
			{
				return Lang::txt('COM_DEVELOPER_API_APPLICATION_MISSING_REDIRECT_URI');
			}

			$uris = array_map('trim', explode(PHP_EOL, $data['redirect_uri']));

			// must have one
			if (empty($uris))
			{
				return Lang::txt('COM_DEVELOPER_API_APPLICATION_MISSING_REDIRECT_URI');
			}

			// validate each one
			$invalid = array();
			foreach ($uris as $uri)
			{
				if (!Validate::url($uri))
				{
					$invalid[] = $uri;
				}
			}

			// if we have any invalid URIs lets inform the user
			if (!empty($invalid))
			{
				return Lang::txt('COM_DEVELOPER_API_APPLICATION_INVALID_REDIRECT_URI', implode('<br />', $invalid));
			}

			return false;
		});
	}

	/**
	 * Load code details by code
	 * 
	 * @param   string  $code
	 * @return  object
	 */
	public static function oneByCode($code)
	{
		$code = self::all()
			->whereEquals('authorization_code', $code)
			->row();

		return $code;
	}

	/**
	 * Defines a belongs to one relationship between entry and user
	 *
	 * @return  object
	 */
	public function user()
	{
		return $this->belongsToOne('Hubzero\User\User', 'uidNumber');
	}

	/**
	 * Return Instance of application for token
	 * 
	 * @return  object
	 */
	public function application()
	{
		return $this->belongsToOne('Application', 'application_id');
	}

	/** 
	 * Expire code
	 * 
	 * @return  bool
	 */
	public function expire()
	{
		$this->set('state', self::STATE_DELETED);
		$this->set('expires', with(new Date('now'))->toSql());

		if (!$this->save())
		{
			return false;
		}

		return true;
	}
}
