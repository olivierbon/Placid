<?php

namespace Craft;

use Guzzle\Http\Client;


class Placid_RequestsService extends BaseApplicationComponent
{

    protected $requestRecord;
    protected $placid_settings;
    private $token;

    public function __construct($requestRecord = null)
    {
        $this->requestRecord = $requestRecord;

        if(is_null($this->requestRecord))
        {
            $this->requestRecord = Placid_RequestsRecord::model();
        }

        // Get the plugin
        $plugin = craft()->plugins->getPlugin('placid');

        // Get the plugin settings
        $this->placid_settings = $plugin->getSettings();
    }

    /**
    * Get the token from a provider
    *                                   
    * @param string|null      $provider     The handle of the provider
    *
    * @return string   the token if the method was successful. 
    *                  A null value will be returned if no token exists
    */

    public function getToken($provider = null)
    {

        if($this->token)
        {
            return $this->token;
        }
        else
        {
            // get plugin
            $plugin = craft()->plugins->getPlugin('placid');

            // get settings
            $settings = $plugin->getSettings();

            // get tokenId
            $tokenId = $settings[$provider];

            // get token
            $token = craft()->oauth->getTokenById($tokenId);


            if($token && $token->token)
            {
                $this->token = $token;
                return $this->token;
            }

            return null;
        }
    }

     /**
     * Save the token
     *                                   
     * @param string          $token  The token which needs to be saved
     *
     * @param string|null     $provider The provider handle    
     *
     * @return boolean        true if token is saved
     *          
     */

     public function saveToken($token, $provider = null)
     {
        // get plugin
        $plugin = craft()->plugins->getPlugin('placid');
  
        // get settings
        $settings = $plugin->getSettings();
    
        // get tokenId
        $tokenId = $settings[$provider];
    
        // get token
        $model = craft()->oauth->getTokenById($tokenId);
  
        // populate token model
        if(!$model)
        {
            $model = new Oauth_TokenModel;
        }
  
        $model->providerHandle = $provider;
        $model->pluginHandle = 'placid';
        $model->encodedToken = craft()->oauth->encodeToken($token);
  
        // save token
        craft()->oauth->saveToken($model);
  

        // If its an instagram token, save that into the tokens bit on placid
        // -----------------------------------------------------------------------------
  
        if($model->providerHandle == 'instagram' && $model->encodedToken != '') {
  
          // Get the decoded token from the OAuth plugin
          $tokenModel = craft()->oauth->decodeToken($model->encodedToken);
  
          // Encrypt the token the Placid way
          $token = craft()->security->hashData($tokenModel->getAccessToken());
  
          // Set the attributes for the model
          $atts = array(
            'name' => ucfirst($model->providerHandle),
            'encoded_token' => $token,
            'token_handle' => $model->providerHandle,
          );
  
          // Set the new token model with the attributes
          $placidTokenModel = craft()->placid_token->newToken($atts);
  
          // Save the token in the access tokens part
          craft()->placid_token->saveToken($placidTokenModel);
  
        }
  
        // set token ID
        $settings[$provider] = $model->id;
  
        // save plugin settings
        craft()->plugins->savePluginSettings($plugin, $settings);
  
        return true;
    }

    /**
    * Create a new model object of a request
    *                                   
    * @param array     $attributes  The attributes to save against the model 
    *
    * @return model    returns Placid_RequestsModel object
    *          
    */

    public function newRequest($attributes = array())
    {

      // Create the new Placid_RequestsModel
      // -----------------------------------------------------------------------------
      $model = new Placid_RequestsModel();

      // Set the attributes from the array
      $model->setAttributes($attributes);

      // Return the Placid_RequestsModel model
      return $model;
    }

     /**
     * Save a request
     *
     * @param object RequestsModel object
     *
     * @return bool true or false if request has been saved
     */

    public function saveRequest(Placid_RequestsModel &$model)
    {
        // Determine whether this is an existing request or if we need to create a new one
        // --------------------------------------------------------------------------------

        if($id = $model->getAttribute('id')) 
        {
            $record = $this->requestRecord->findByPk($id);
        } 
        else 
        {
            $record = $this->requestRecord->create();
        }

        // Get the attributes from the passed model
        $attributes = $model->getAttributes();

        // Set the new attributes to the record
        $record->setAttributes($attributes, false);

        // Save the new request
        // -----------------------------------------------------------------------------

        if($record->save())
        {
            $model->setAttribute('id', $record->getAttribute('id'));
            return true;
        } 
        else 
        {
            $model->addErrors($record->getErrors());
            return false;
        }
    }

    /**
     * Get all placid requests
     *
     * @return requests model object 
     */

    public function getAllRequests()
    {
      // Find all the requests and order them by ID
      // -----------------------------------------------------------------------------
      $args = array('order' => 't.id');
      $records = $this->requestRecord->findAll($args);
      return Placid_RequestsModel::populateModels($records, 'id');
    }

    /**
    * Find request by ID
    *
    * @param string $id 
    *
    * @return request model object
    */
    public function findRequestById($id)
    {

     // Determine if there is a request record and return it
     // -----------------------------------------------------------------------------

     if($record = $this->requestRecord->findByPk($id))
     {
       $params = $record->getAttribute('params');
       $decodedParams = unserialize(base64_decode($params));
       $record->setAttribute('params', $decodedParams);
       return Placid_RequestsModel::populateModel($record);
     }
    }

    /**
    * Return the request
    *
    * @param string $handle
    *
    * @param array $options
    *
    * @throws Exception
    *
    * @return mixed
    */

    public function findRequestByHandle($handle, $options)
    {
      Craft::log(__METHOD__, LogLevel::Info, true);

      // Get the request record by its handle
      // ---------------------------------------------

      $requestRecord =  $this->requestRecord->find(
        'handle=:handle',
        array(
          ':handle' => $handle
        )
      );

      // Determine if there is a requestRecord and act accordingly
      // -----------------------------------------------------------------------------

      if($requestRecord)
      {

        // Get a cached request
        $cachedRequest = craft()->placid_cache->get($requestRecord['cache_id']);

        // Do we need to try serve a cached version or not
        $c = array_key_exists('cache', $options) ? $options['cache'] : true;

        // If cache_id is null or craft cache returns false, continue with live pull
        if( ! $c || ! $requestRecord['cache_id'] || ! $cachedRequest )
        {
          return $this->_get($requestRecord, $options);
        }

        return $cachedRequest;
      }
      else
      {
        throw new Exception(Craft::t('Can\'t find request with handle "{handle}"', array('handle' => $handle)));
      }
    }



    /**
     * Authenticate the request, used if OAuth provider is chosen on request creation
     *
     * @param string $auth
     * @param object $client
     * @return boolean
     */

    private function _authenticateOauth($auth, $client)
    {

        $provider = craft()->oauth->getProvider($auth);
        $tokenModel = $this->getToken($auth);

        if(!$tokenModel)
        {
            return null;
        }

        $token = $tokenModel->token;
        
        if(!$provider || !$token)
        {
            return null;
        }

        $oauth = new \Guzzle\Plugin\Oauth\OauthPlugin(array(
            'consumer_key'    => $provider->clientId,
            'consumer_secret' => $provider->clientSecret,
            'token'           => $token->getAccessToken(),
            'token_secret'    => $token->getAccessTokenSecret()
        ));

        $client->addSubscriber($oauth);

        return true;
    }

    /**
     * Creates the Guzzle client and sends
     * the GET request
     *
     * @param  model $requestRecord
     *
     * @param  array $options null
     *
     * @param  string $method
     *
     * @param  string $headers null
     *
     * @param  array $postFields null
     *
     * @return array $response
     */

    private function _get($requestRecord,$options = null, $method = 'get', $headers = null, $postFields = null)
    {

      // Get the url from the request record
      $url = $requestRecord->getAttribute('url');

      // Create a new Guzzle Client
      $client = new Client($url);

      // Check whether there is an oauth attribute and if so, authenticate the request
      $requestRecord->getAttribute('oauth') ? $this->_authenticateOauth($requestRecord->getAttribute('oauth'), $client) : '';

      // Get the params from the builder
      $params = $this->_buildParams($requestRecord, $options);

      // Get the segments from the builder
      $segments = $this->_buildSegments($options);

      // If there are segments, add them to the url
      if($segments)
      {
          $url .= $segments;
      }

      // If there are params, add them to the url
      if($params)
      {
          $url .= '?'.$params;
      }

      // Get the access token from the record
      $accesstoken = $requestRecord->getAttribute('tokenId');

      // If there is an access token, we need to build it into the query
      if($accesstoken)
      {
        // If there are no current params, we will need to start with a ?
        $params ? $url .= '&' : $url .= '?';
        $url .= $this->_buildAccessTokenQuery($requestRecord, $accesstoken);
      }
 
      // Use Guzzle to GET the request assign the response to a variable
      $response = $client->get($url, $headers, $postFields)->send();

      // Update this variable with a JSON conversion
      $response = $response->json();

      // If cache is enabled save a new cache, first check if it is set in template, if not, load from settings
      $cache = array_key_exists('cache', $options) ? $options['cache'] : $this->placid_settings['cache'];

      if($cache)
      {
          craft()->placid_cache->set($requestRecord, $response);
      }

      return $response;
    }

    /**
    * Build the params for the url
    *
    * Determine how we are going to add the params (this needs improving)
    * and then build the parameters to append to the url.
    *
    * @param  model $requestRecord
    * @param  array $options
    * @return string
    */

    private function _buildParams($requestRecord, $options)
    {
      $params = array();
      // Get any control panel parameters
      $cpParams = unserialize(base64_decode($requestRecord->getAttribute('params')));
      $optionParams = array_key_exists('params', $options) ? $options['params'] : null;

      if(is_array($optionParams))
      {
        $params = array_merge($optionParams, $params);
      } 
      else if(is_array($cpParams))
      {
        foreach($cpParams as $key => $param)
        {
          $params[$param['key']] = $param['value'];
        }
      }

      $params = http_build_query($params, '', '&');
    
      return $params;
    }

    /**
     * Builds the access token query
     *
     * @param  model $requestRecord
     * @param  int $tokenId
     * @return string
     */

    public function _buildAccessTokenQuery($requestRecord, $tokenId)
    {
      $tokenModel = craft()->placid_token->findTokenById($tokenId);
      $token = $tokenModel->getAttribute('encoded_token');
      $query = 'access_token=' . $token;
      return $query;
    }

    /**
     * Build the segments from options
     *
     * @param  array $options
     * @return mixed segments or null
     */

    private function _buildSegments($options)
    {
      if(isset($options['segments']))
      {
          $segments = $options['segments'];
          return $segments;
      }
      return null;
    }
    
    /**
     * Delete a request from the database.
     *
     * @param  int $id
     * @return int The number of rows affected
     */
    public function deleteRecordById($id)
    {
        return $this->requestRecord->deleteByPk($id);
    }

}