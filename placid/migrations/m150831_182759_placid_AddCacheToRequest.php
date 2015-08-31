<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m150831_182759_placid_AddCacheToRequest extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{

		if (!craft()->db->columnExists('cache', 'placid_requests')) {
			$this->addColumnBefore('placid_requests', 'cache', array('column' => ColumnType::Bool), 'cache_id');
		}
		return true;
	}
}
