<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m150530_153009_placid_AddHeadersColumn extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
		if(! craft()->db->columnExists('headers', 'placid_requests') ) {
			// $this->addColumnAfter('placid_requests', 'userMapping', array(AttributeType::String, 'required' => false), 'userId');
			$this->addColumnAfter('placid_requests', 'headers', array('column' => ColumnType::Text), 'params');
		}
		return true;
	}
}
