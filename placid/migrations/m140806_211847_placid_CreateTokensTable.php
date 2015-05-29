<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m140806_211847_placid_CreateTokensTable extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
		$tokensTable = $this->dbConnection->schema->getTable('placid_accesstokens');

		if(!$tokensTable) {
			// Create the craft_placid_accesstokens table
			craft()->db->createCommand()->createTable('placid_accesstokens', array(
				'name'          => array('required' => true),
				'encoded_token' => array('column' => 'text', 'required' => false),
			), null, true);
		}
		
		return true;
	}
}
