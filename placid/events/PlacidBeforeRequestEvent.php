<?php
namespace Craft;

/**
 * Placid beforeRequest event
 */
class PlacidBeforeRequestEvent extends Event
{
	/**
	 * @var bool Whether to make the request
	 */
	public $makeRequest = true;

	/**
	 * @var bool Whether to bypass the cache
	 */
	public $bypassCache = false;
}
