<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m140906_081222_placid_AddCacheIdToRequests extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{

		if(! craft()->db->columnExists('cache_id', 'placid_requests') ) {
			// $this->addColumnAfter('placid_requests', 'userMapping', array(AttributeType::String, 'required' => false), 'userId');
			$this->addColumnAfter('placid_requests', 'cache_id', array('column' => ColumnType::Text), 'id');

		}

		return true;
	}
}
