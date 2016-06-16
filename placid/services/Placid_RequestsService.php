<?php
/**
 * Placid requests service class
 *
 * This class does most of the heavy lifting when it comes to making requests, authenticating
 * and putting in querys, paths all that config stuff.
 *
 * @author    Alec Ritson. <info@alecritson.co.uk>
 * @copyright Copyright (c) 2014, Alec Ritson.
 * @license   http://buildwithcraft.com/license Craft License Agreement
 * @link      http://itsalec.co.uk
 * @package   craft.plugins.placid.services
 * @since     0.8.0
 */
namespace Craft;

use Guzzle\Http\Client;
use Guzzle\Http\Message\EntityEnclosingRequest;
use Guzzle\Http\Exception\RequestException;

class Placid_RequestsService extends BaseApplicationComponent
{
  /**
   * Placid plugin settings
   * @var Array
   */
  protected $settings;

  /**
   * Placid request config
   * @var Array
   */
  protected $config;

  /**
   * The cache id of the request
   * @var String
   */
  protected $cacheId;

  public function __construct()
  {
    // Get the plugin settings
    $this->settings = craft()->plugins->getPlugin('placid')->getSettings();
  }

  /**
  * Make the request
  *
  * This method will create a new client and get a response from a Guzzle request
  *
  * @param string|null      $handle     The handle of the request record
  *
  * @return array   the req
  */

  public function request($handle = null, array $config = array())
  {
    // Get our request from the database...or not
    if(!array_key_exists('url', $config))
    {
      $model = $this->findRequestByHandle($handle);
    }
    else
    {
      $model = null;
    }

    $this->config = array_merge(
      array(
        'method' => 'GET',
        'cache' => ($model ? $model->cache : true),
        'duration' => 3600, // 1 hour
      ),
      $config
    );

    // if(isset($config['segments']))
    
    if(isset($this->config['segments']))
    {
      $model->url = $this->parseSegments($model->url, $this->config['segments']);
    }

    $this->_swapDeprecatedConfig('query', 'params');

    // Create a new guzzle client
    $client = new Client();

    if($model)
    {
      $request = $this->_createRequest($client, $model);
    }
    else
    {
      $request = $client->createRequest($this->config['method'], $this->config['url']);
    }

    // Get a cached request
    $cachedRequest = craft()->placid_cache->get($this->_getCacheId());

    // Import the onBeforeRequest event
    Craft::import('plugins.placid.events.PlacidBeforeRequestEvent');
    $event = new PlacidBeforeRequestEvent($this, array('request' => $request));
    craft()->placid_requests->onBeforeRequest($event);

    // Check to make sure no other plugins have change anything
    if($event->makeRequest)
    {
      if( (! $this->config['cache'] || ! $cachedRequest) && ! $event->bypassCache)
      {
        $response = $this->_getResponse($client, $request);
      }
      else
      {
        $response = $cachedRequest;
      }
    }
    else {
      return false;
    }

    Craft::import('plugins.placid.events.PlacidAfterRequestEvent');

    $event = new PlacidAfterRequestEvent($this, array('response' => $response));

    $this->onAfterRequest($event);

    return $response;

  }

  public function getDataFromResponse($response)
  {
    $responseBody = $response->getBody();

    $contentType = preg_match('/.+?(?=;)/', $responseBody->getContentType(), $matches);

    $contentType = implode($matches, '');
    
    try {   
      if($contentType == 'text/xml')
      {
        $output = $response->xml();
      }
      else
      {
        $output = $response->json();
      }
    } catch (\Guzzle\Common\Exception\RuntimeException $e) {
      PlacidPlugin::log($e->getMessage(), LogLevel::Error);
      $output = null;
    }

    return $output;
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

  public function findRequestByHandle($handle)
  {
    Craft::log(__METHOD__, LogLevel::Info, true);

    // Get the request record by its handle
    // ---------------------------------------------

    $record =  Placid_RequestsRecord::model()->find(
      'handle=:handle',
      array(
        ':handle' => $handle
        )
      );

    if($record)
    {
      return Placid_RequestsModel::populateModel($record);
    }
    else
    {
      throw new Exception(Craft::t('Can\'t find request with handle "{handle}"', array('handle' => $handle)));
    }
  }

  // Record Methods
  // =============================================================================

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
      $record = Placid_RequestsRecord::model()->findByPk($id);
    }
    else
    {
      $record = new Placid_RequestsRecord();
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

  public function findAllRequests()
  {
    $args = array('order' => 't.id');
    $records = Placid_RequestsRecord::model()->findAll($args);
    return Placid_RequestsModel::populateModels($records, 'id');
  }

  public function getRequestById($id)
  {
    if($record = Placid_RequestsRecord::model()->findByPk($id))
    {
        return Placid_RequestsModel::populateModel($record);
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
    // Get all a users widgets
    $this->_deleteWidgetsByRecord($id);
    return Placid_RequestsRecord::model()->deleteByPk($id);
  }

  // Events
  // =============================================================================

  /**
  * Fires an 'onBeforeRequest' event.
  *
  * @param PlacidBeforeRequestEvent $event
  */
  public function onBeforeRequest(PlacidBeforeRequestEvent $event)
  {
    $this->raiseEvent('onBeforeRequest', $event);
  }

  /**
  * Fires an 'onAfterRequest' event.
  *
  * @param PlacidAfterRequestEvent $event
  */
  public function onAfterRequest(PlacidAfterRequestEvent $event)
  {
    $this->raiseEvent('onAfterRequest', $event);
  }

  // Private Methods
  // =============================================================================

  /**
  * Create a new request object
  *
  * @param array     $attributes  The attributes to save against the model
  *
  * @return object    returns EntityEnclosingRequest $request
  *
  */
  private function _createRequest($client, $record = null)
  {

    if($record && $recordUrl = $record->getAttribute('url'))
    {
      $this->config['url'] = $recordUrl;
    }

    $request = $client->createRequest($this->config['method'], $this->config['url']);

    $this->cacheId = $request->getUrl();

    if(array_key_exists('body', $this->config))
    {
      $request->setBody($this->config['body']);
    }

    // Is a new path set?
    if(array_key_exists('path', $this->config))
    {
      $request->setPath($this->config['path']);
    }

    // Have headers been set in the admin area?
    // If so add them in otherwise check if there are any passed through the template
    $cpHeaders = $record->getAttribute('headers');

    if($cpHeaders)
    {
      foreach($cpHeaders as $k => $q)
      {
        $request->addHeader($q['key'], $q['value']);
      }
    }
    elseif(array_key_exists('headers', $this->config) && is_array($this->config['headers']))
    {
      foreach ($this->config['headers'] as $key => $value)
      {
        $request->addHeader($key, $value);
      }
    }

    // Get the parameters from the record
    $cpQuery = $record->getAttribute('params');

    if($request->getMethod() == 'GET')
    {
      // Get the query from the request
      $query = $request->getQuery();
    }
    else
    {
      $query = $request->getPostFields();
    }

    // If they exist, add them to the query
    if($cpQuery && is_array($cpQuery))
    {
      foreach($cpQuery as $k => $q)
      {
        $query->set($q['key'], $q['value']);
      }
    }
    elseif(array_key_exists('query', $this->config))
    {
      foreach($this->config['query'] as $key => $value)
      {
        $query->set($key, $value);
      }
    }

    if($query)
    {
      $this->cacheId .= '?' . $query;
    }

    // Do we need to do some OAuth magic?
    if($provider = $record->getAttribute('oauth'))
    {
      $this->_authenticate($request,$provider);
    }


    // Has the request got an access token we need to attach?
    if($tokenId = $record->getAttribute('tokenId'))
    {
      
      $tokenModel = craft()->placid_token->findTokenById($tokenId);

      if(!$tokenModel->forceQuery)
      {
        $request->addHeader('Authorization', 'Bearer ' . $tokenModel->encoded_token);
      }
      else
      {
        $query->set('access_token', $tokenModel->encoded_token);
      }

    }

    
    return $request;
  }

  /**
   * Get the response from a client and request
   *
   * @param Client $client a guzzle client
   * @param object $request a guzzle request object
   * @return array the response
   */
  private function _getResponse(Client $client, $request)
  {

    try {
      $response = $client->send($request);
    } catch(RequestException $e) {
      PlacidPlugin::log($e->getMessage(), LogLevel::Error);

      $message = array('failed' => true);

      $response = null;

      if(method_exists($e, 'getResponse'))
      {
        $response = $e->getResponse();
        $message['statusCode'] = $response->getStatusCode();
      }
      
      return $response;
    }
    
    if($this->config['cache'])
    {
      craft()->placid_cache->set($this->_getCacheId(), $response, $this->config['duration']);
    }
    return $response;
  }

  /**
   * Authenticate the request, used if OAuth provider is chosen on request creation
   *
   * @param string $auth
   * @param object $client
   * @return boolean
   */
  private function _authenticate($client, $auth)
  {
    $provider = craft()->oauth->getProvider($auth);

    $token = craft()->placid_oAuth->getToken($auth);

    $subscriber = $provider->getSubscriber($token);

    $client->addSubscriber($subscriber);
  }

  /**
   * If there have been any config naming changes, this function will handle it
   * gracefully so templates don't start failing everywhere because I change my
   * mind too much!
   *
   * @param  String $new The new config key
   * @param  String $old The old config key to be replaced
   * @return Bool     Whether it was a success
   */
  private function _swapDeprecatedConfig($new, $old)
  {
    // Segments is now called path, allow for templates still using segments
    if(array_key_exists($old, $this->config))
    {
      $this->config[$new] = $this->config[$old];
      unset($this->config[$old]);
    }
    return true;
  }

  /**
   * Deletes a Widget based on the record
   * @param  Int $id The id of the record
   * @return Bool    True
   */
  private function _deleteWidgetsByRecord($id)
  {
    $record = Placid_RequestsRecord::model()->findByPk($id);

    $currentWidgets = craft()->dashboard->getUserWidgets();

    foreach($currentWidgets as $widget)
    {
      $settings = $widget->settings;
      if($settings && is_array($settings))
      {
        if(array_key_exists('request', $settings) && $settings['request'] == $record->handle)
        {
         craft()->dashboard->deleteUserWidgetById($widget->id);
        }
      }
    }
    return true;
  }

  /**
   * Returns the encoded cacheId for this request
   * @return String The cache id
   */
  private function _getCacheId()
  {
    return base64_encode(urlencode($this->cacheId));
  }

  private function parseSegments($str, $segments)
  {
    foreach ($segments as $key => $value)
    {
      $str = str_replace('{'.$key.'}', $value, $str);
    }

    return $str;
  }
}
