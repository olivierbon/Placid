<?php
namespace Craft;

/**
 * Guest Entries event
 */
class PlacidRequestEvent extends Event
{
	/**
	 * @var bool Whether to make the request
	 */
	public $makeRequest = true;

	/**
	 * @var bool Whether to bypass the cache
	 */
	public $bypassCache = true;
}
