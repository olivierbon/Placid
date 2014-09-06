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
        if(is_null($this->requestRecord)) {
            $this->requestRecord = Placid_RequestsRecord::model();
        }

        $plugin = craft()->plugins->getPlugin('placid');
        $this->placid_settings = $plugin->getSettings();
    }

    /**
     * Get OAuth Token
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
        }
    }

     /** * Save OAuth Token */

     public function saveToken($token, $provider = null) {
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

      // set token ID
      $settings[$provider] = $model->id;

      // save plugin settings
      craft()->plugins->savePluginSettings($plugin, $settings);
    }

    public function newRequest($attributes = array())
    {
        $model = new Placid_RequestsModel();
        $model->setAttributes($attributes);
        return $model;
    }

     /**
     * Save a request
     *
     * @param object RequestsModel object
     * @return bool true or false if request has been saved
     */

    public function saveRequest(Placid_RequestsModel &$model)
    {
        // Find a request model, if none exists it must be a new record
        if($id = $model->getAttribute('id')) {
            $record = $this->requestRecord->findByPk($id);
        } else {
            $record = $this->requestRecord->create();
        }
        // Get attributes from model, if any
        $attributes = $model->getAttributes();
        // Fix this, only saves as unsafe value
        $record->setAttributes($attributes, false);
        if($record->save()) {
            $model->setAttribute('id', $record->getAttribute('id'));
            return true;
        } else {
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
        $records = $this->requestRecord->findAll(array('order' => 't.id'));
        return Placid_RequestsModel::populateModels($records, 'id');
    }

     /**
     * Find request by ID
     *
     * @param string $id 
     * @return request model object 
     */
    public function findRequestById($id)
    {
     if( $record = $this->requestRecord->findByPk($id)) {

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
     * @param array $options
     * @return mixed
     */

    public function findRequestByHandle($handle, $options)
    {

         Craft::log(__METHOD__, LogLevel::Info, true);
            $requestRecord =  $this->requestRecord->find(
                // conditions
                'handle=:handle',
                // params
                array(
                    ':handle' => $handle
                )
             );


        if($requestRecord) {
            
            $cachedRequest = craft()->placid_cache->get($requestRecord['cache_id']);

            $c = array_key_exists('cache', $options) ? $options['cache'] : true;

            // If cache_id is null or craft cache returns false, continue with live pull
            if( ! $c || ! $requestRecord['cache_id'] || ! $cachedRequest ) {

                return $this->_get($requestRecord, $options);
            }

           return $cachedRequest;
    
        } else {
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
     * @param  array $options null
     * @param  string $method
     * @param  string $headers null
     * @param  array $postFields null
     * @return array $response
     */

    private function _get($requestRecord,$options = null, $method = 'get', $headers = null, $postFields = null)
    {

            $url = $requestRecord->getAttribute('url');

            $client = new Client($url);

            // Check whether there is an oauth attribute and if so, authenticate the request
            $requestRecord->getAttribute('oauth') ? $this->_authenticateOauth($requestRecord->getAttribute('oauth'), $client) : '';
           
            $params = $this->_buildParams($requestRecord, $options);

            $segments = $this->_buildSegments($options);

            if($segments) {
                $url .= $segments;
            }
            if($params) {
                $url .= '?' . $params;
            }
            $accesstoken = $requestRecord->getAttribute('tokenId');

            
            if($accesstoken) {
              // If there are no current params, we will need to start with a ?
              $params ? $url .= '&' : $url .= '?';
              $url .= $this->_buildAccessTokenQuery($requestRecord, $accesstoken);
            }

            $response = $client->get($url, $headers, $postFields)->send();
            $response = $response->json();

            // If cache is enabled save a new cache
            $cache = array_key_exists('cache', $options) ? $options['cache'] : $this->placid_settings['cache'];

            if( $cache ) {
                craft()->placid_cache->set($requestRecord, $response);
            }

            return $response;
    }

    /**
     * Builds the params to attach to url
     *
     * @param  model $requestRecord
     * @param  array $options
     * @return string
     */

    private function _buildParams($requestRecord, $options)
    {
        $params = '';
        $cpParams = unserialize(base64_decode($requestRecord->getAttribute('params')));
            if(isset($options['params'])) {
                $params = $options['params'];
                if(is_array($params)) {
                    $params = http_build_query($params, '', '&amp;');
                } else {
                    throw new Exception(Craft::t('Parameters argument is not an array'));
                }
            } elseif(is_array($cpParams)) {
                $counter = 0;
                foreach($cpParams as $key => $value) {
                    $params .= ($counter++ >= 1 ? '&' : '') . $value['key'] . '=' . $value['value'];
                }
            } 
            
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
      if(isset($options['segments'])) {
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