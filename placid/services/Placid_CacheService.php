<?php

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

	public function set($url, $value, $duration)
	{
		// Set the cacheId
		$cacheId = base64_encode( urlencode($url) );

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