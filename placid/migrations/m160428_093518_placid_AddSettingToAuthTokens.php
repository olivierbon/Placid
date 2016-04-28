<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m160428_093518_placid_AddSettingToAuthTokens extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
		if (!craft()->db->columnExists('cache', 'placid_accesstokens')) {
			$this->addColumnBefore('placid_accesstokens', 'forceQuery', array('column' => ColumnType::Bool), 'encoded_token');
		}
		return true;
	}
}
