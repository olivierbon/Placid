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

	public function set($record, $value)
	{
		// Set the cacheId
		$cacheId = $this->_generateCacheId($record['handle']);

		$record->setAttribute('cache_id', $cacheId);
		$record->save();

		return craft()->cache->set($cacheId, $value, null, null);
	}

	public function get($id)
	{
		return craft()->cache->get($id);
	}

	private function _generateCacheId($handle) {
		return str_shuffle(sha1(time().microtime() . $handle));
	}
}