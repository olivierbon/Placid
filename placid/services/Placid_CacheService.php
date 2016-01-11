<?php
/**
 * Placid Cache class
 *
 * Handles caching of requests and ting
 *
 * @author    Alec Ritson. <info@alecritson.co.uk>
 * @copyright Copyright (c) 2015, Alec Ritson.
 * @license   http://buildwithcraft.com/license Craft License Agreement
 * @link      http://itsalec.co.uk
 * @package   craft.plugins.placid.services
 * @since     1.0.0
 */
namespace Craft;

class Placid_CacheService extends BaseApplicationComponent
{

	protected $cacheRecord;
	protected $cacheModel;

	/**
	 * Caches the request
	 *
	 * @param string            $id         The key identifying the value to be cached.
	 * @param mixed             $value      The value to be cached.
	 *                                      expire.
	 *
	 * @return bool true if the value is successfully stored into cache, false otherwise.
	 */

	public function set($cacheId, $value, $duration)
	{
		return craft()->cache->set($cacheId, $value, $duration, null);
	}

	/**
	 * Get the request from cache
	 *
	 * @param string            $id         The key identifying the value to be cached.
	 *
	 * @return mixed the cached content if exists, null if not
	 */
	public function get($id)
	{
		return craft()->cache->get($id);
	}
}
