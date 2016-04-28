<?php
/**
 * The main Placid class
 *
 * This class will take care of all repetitive tasks that are required.
 *
 * @author    Alec Ritson. <info@alecritson.co.uk>
 * @copyright Copyright (c) 2014, Alec Ritson.
 * @license   http://buildwithcraft.com/license Craft License Agreement
 * @link      http://buildwithcraft.com
 * @package   craft.plugins.placid.services
 * @since     1.3.0
 */
namespace Craft;

class PlacidService extends BaseApplicationComponent
{
	var $record;
	var $model;
	var $settings;

	public function __construct()
	{
		$this->settings = craft()->plugins->getPlugin('placid')->getSettings();
	}

	/**
	 * Gets all the records for the given record type
	 * @return Array An array of Model objects
	 */
	public function getAll($record)
	{
		$args = array('order' => 't.id');
    	$records = $record->findAll($args);
    	return $record->model()->populateModels($records, 'id');
	}

	/**
	 * Gets a record by its id
	 * @param  Int $id The id of the record
	 * @return  A model representation of the record
	 */
	public function getById($id)
	{
		if($record = $this->record->findByPk($id))
		{
		    return $this->model->populateModel($record);
		}
		return null;
	}
}