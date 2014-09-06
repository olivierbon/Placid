<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m140806_212644_placid_AddForeignKeyToRequestsTable extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{

		if (! craft()->db->columnExists('tokenId', 'placid_requests')) {
			$this->addColumnAfter('placid_requests', 'tokenId', array('column' => ColumnType::Int), 'id');
			$this->addForeignKey('placid_requests', 'tokenId', 'placid_accesstokens', 'id', 'SET NULL', null);

		}
		return true;
	}
}
