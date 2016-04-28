<?php
/**
 * Placid OAuth class
 *
 * This class will take care of all the juicy OAuth stuff, works with OAuth Plugin from
 * dukt http://www.dukt.net/plugins/oauth
 *
 * @author    Alec Ritson. <info@alecritson.co.uk>
 * @copyright Copyright (c) 2015, Alec Ritson.
 * @license   http://buildwithcraft.com/license Craft License Agreement
 * @link      http://itsalec.co.uk
 * @package   craft.plugins.placid.services
 * @since     1.3.0
 */
namespace Craft;

class Placid_OAuthService extends BaseApplicationComponent
{
	/**
	 * The token model
	 * @var [type]
	 */
	var $token;


	/**
	 * Returns a model representing the token we want to get from
	 * the OAuth plugin
	 *
	 * @param  String $handle The handle of the token we want to find, usually this is just the
	 *                        name of the provider itself.
	 *
	 * @return Mixed  null|Placid_OAuthModel Either nothing or the model representation
	 */
	public function findByHandle($handle)
	{
		Craft::log(__METHOD__, LogLevel::Info, true);

		// Get the request record by its handle
		// ---------------------------------------------
		$record =  Placid_OAuthRecord::model()->find(
		  'handle=:handle',
		  array(
		    ':handle' => $handle
		    )
		  );
		if($record)
		{
		  return Placid_OAuthModel::populateModel($record);
		}
		return null;
	}

	public function findAllProviders()
	{
		$args = array('order' => 't.id');
    	$records = Placid_OAuthRecord::model()->findAll($args);
    	return Placid_OAuthModel::populateModels($records, 'id');
	}
	/**
	 * Saves a Placid_OAuthModel and also the Oauth_TokenModel which is handy
	 * @param  String           $provider Name of the provider we want to save
	 * @param  Oauth_TokenModel $token    The OAuth token model we want to save
	 * @return Bool                       1 or 0 if successfull
	 */
	public function saveToken($provider, Oauth_TokenModel $token)
	{
		$tokenModel = $this->findByHandle($provider);

		$existingToken = null;

		if($tokenModel)
		{
			$existingToken = craft()->oauth->getTokenById($tokenModel->tokenId);
			$record = Placid_OAuthRecord::findByPk($tokenModel->id);
		}
		else
		{
			$record = new Placid_OAuthRecord();
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

	/**
	 * Gets a token so we can authenticate the request when we need to
	 * @param  String $provider The name of the provider we want to get the token for
	 * @return Oauth_TokenModel           Model representation of the token
	 */
	public function getToken($provider)
	{
		// Get the model
		$tokenModel = $this->findByHandle($provider);

		$tokenId = null;

		if($tokenModel)
		{
			$tokenId = $tokenModel->tokenId;
		}

		$token = craft()->oauth->getTokenById($tokenId);

		if($token)
		{
			return $token;
		}
	}

	/**
	 * Deletes a token from both Placid and the OAuth plugin
	 * @param  String $provider Name of the provider's token we want to delete
	 * @return Bool           true if delete was successful
	 */
	public function deleteToken($provider)
	{
		$tokenModel = $this->findByHandle($provider);

		$oauthTokenModel = craft()->oauth->getTokenById($tokenModel->id);

		craft()->oauth->deleteToken($oauthTokenModel);

		if($tokenModel)
		{
			Placid_OAuthRecord::model()->deleteByPk($tokenModel->id);
		}
		return true;
	}
}
