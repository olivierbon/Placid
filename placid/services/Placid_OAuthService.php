<?php

namespace Craft;

class Placid_OAuthService extends PlacidService
{
	var $token;

	public function __construct()
	{
		parent::__construct();
		$this->model = new Placid_OAuthModel;
		$this->record = new Placid_OAuthRecord;
	}

	public function findByHandle($handle)
	{
		Craft::log(__METHOD__, LogLevel::Info, true);

		// Get the request record by its handle
		// ---------------------------------------------
		$record =  $this->record->find(
		  'handle=:handle',
		  array(
		    ':handle' => $handle
		    )
		  );

		if($record)
		{
		  return $this->model->populateModel($record);
		}
		return null;
	}
	public function saveToken($provider, Oauth_TokenModel $token)
	{
		$tokenModel = $this->findByHandle($provider);

		$existingToken = null;

		if($tokenModel)
		{
			$existingToken = craft()->oauth->getTokenById($tokenModel->tokenId);
			$record = $this->record->findByPk($tokenModel->id);
		}
		else
		{
			$record = $this->record->create();
		}
		
		if($existingToken)
		{
			$token->id = $existingToken->id;
		}

		// save token
    	craft()->oauth->saveToken($token);

    	$attributes = array(
    		'tokenId' => $token->id,
    		'handle' => $provider
    	);

    	// Set the new attributes to the record
    	$record->setAttributes($attributes, false);

    	return $record->save();
	}
	public function getToken($provider)
	{
		

		if($this->token)
		{
			return $this->token;
		}
		else
		{

			// Get the model
			$tokenModel = $this->findByHandle($provider);


			$tokenId = null;
			if($tokenModel)
			{
				$tokenId = $tokenModel->id;
			}
			// $this->poop($tokenId);
			$token = craft()->oauth->getTokenById($tokenId);


			if($token)
			{
				$this->token = $token;
				return $this->token;
			}
		}
	}
	public function deleteToken($provider)
	{
		$tokenModel = $this->findByHandle($provider);

		$oauthTokenModel = craft()->oauth->getTokenById($tokenModel->id);

		craft()->oauth->deleteToken($oauthTokenModel);
		
		if($tokenModel)
		{
			$this->record->deleteByPk($tokenModel->id);
		}
		return true;
	}
}