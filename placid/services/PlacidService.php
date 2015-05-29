<?php

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

	public function getAll()
	{
		$args = array('order' => 't.id');
    	$records = $this->record->findAll($args);
    	return $this->model->populateModels($records, 'id');
	}

	/**
	 * Gets a record by its id
	 * @param  Int $id The id of the record
	 * @return Placid_RequestsModel     A model representation of the record
	 */
	public function getById($id)
	{
		if($record = $this->record->findByPk($id))
		{
		    return $this->model->populateModel($record);
		}
		return null;
	}

	public function poop($nugget)
	{
		echo "<pre>";
		print_r($nugget);
		die();
	}
}