<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m150529_104654_placid_AddOAuthTable extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
		// Create the craft_placid_oauth_tokens table
		craft()->db->createCommand()->createTable('placid_oauth_tokens', array(
			'handle'  => array('required' => true),
			'tokenId' => array('required' => true),
		), null, true);

		// Add indexes to craft_placid_oauth_tokens
		craft()->db->createCommand()->createIndex('placid_oauth_tokens', 'handle', true);
	}
}
