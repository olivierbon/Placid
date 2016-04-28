<?php
namespace Craft;

class Placid_RequestsController extends BaseController
{
    protected $allowAnonymous = array('actionRequest');

    /**
    * Action to save request
    *
    * @return null
    */

    public function actionSaveRequest()
    {
      Craft::log(__METHOD__, LogLevel::Info, true);

      // Determine whether this is an existing or new request
      // -----------------------------------------------------------------------------

      if($id = craft()->request->getPost('requestId')) {
        $model = craft()->placid_requests->getRequestById($id);
      } else {
        $model = new Placid_RequestsModel();
      }

      // Get the params from the form
      $params = craft()->request->getPost('params');

      if($params)
      {
        $params = json_encode($params);
      }

      // Get the headers
      $headers = craft()->request->getPost('headers');

      if($headers)
      {
        $headers = json_encode($headers);
      }

      // Prepare the params for entry
      // -----------------------------------------------------------------------------

      // Define the record attributes
      $atts = array(
        'name' => craft()->request->getPost('requestName'),
        'handle' => craft()->request->getPost('handle'),
        'oauth' => craft()->request->getPost('oauth'),
        'tokenId' => craft()->request->getPost('tokenId'),
        'url' => craft()->request->getPost('requestUrl'),
        'cache' => craft()->request->getPost('cache'),
        'params' => $params,
        'headers' => $headers,
      );

      // Set these new attributes in the model
      $model->setAttributes($atts);

      // Try and save the request, otherwise show an error
      // -----------------------------------------------------------------------------


      if(craft()->placid_requests->saveRequest($model))
      {
        craft()->userSession->setNotice(Craft::t('Request saved'));
        return $this->redirectToPostedUrl(array('requestId' => $model->getAttribute('id')));
      }
      else
      {
        craft()->userSession->setError(Craft::t("Couldn't save request."));
        craft()->urlManager->setRouteVariables(array('request' => $model));
      }

    }

    public function actionRequest()
    {
      $this->requireAjaxRequest();

      $handle = craft()->request->getQuery('handle');
      $options = craft()->request->getQuery('options', array());

      $response = craft()->placid_requests->request($handle, $options);

      $this->returnJson($response);
    }
    /**
     * Delete a request
     */

    public function actionDeleteRequest()
    {
       $this->requirePostRequest();
       $this->requireAjaxRequest();

       $id = craft()->request->getRequiredPost('id');
       craft()->placid_requests->deleteRecordById($id);

       $this->returnJson(array('success' => true));
    }


    // Oauth
    /**
     * Connect
     */
    public function actionConnect($provider, array $variables = array())
    {
      // Referer
      $referer = craft()->httpSession->get($provider.'.referer');

      if(!$referer)
      {
        $referer = craft()->request->getUrlReferrer();
        craft()->httpSession->add($provider.'.referer', $referer);
      }

      if($response = craft()->oauth->connect(array(
        'plugin' => 'placid',
        'provider' => $provider,
      )))
      {
        if($response['success'])
        {
          // token
          $token = $response['token'];

          // save token
          craft()->placid_oAuth->saveToken($provider, $token);

          // session notice
          craft()->userSession->setNotice(Craft::t("Connected to {$provider}"));
        }
        else
        {
          craft()->userSession->setError(Craft::t($response['errorMsg']));
        }

        craft()->httpSession->remove('twitter.referer');

        $this->redirect('placid/oauth');
      }
    }

    /**
     * Disconnect
     */
    public function actionDisconnect($provider)
    {
        if(craft()->placid_oAuth->deleteToken($provider))
        {
            craft()->userSession->setNotice(Craft::t("Disconnected from {$provider}."));
        }
        else
        {
            craft()->userSession->setNotice(Craft::t("Couldn’t disconnect from {$provider}."));
        }

        // redirect
        $redirect = craft()->request->getUrlReferrer();
        $this->redirect($redirect);
    }


    /**
     * Get the params from the database
     */

    private function _prepParams($array, $params = null)
    {
      $params = base64_encode(serialize($array));
      return $params;
    }

    public function poop($nugget)
  {
    echo "<pre>";
    print_r($nugget);
    die();
  }
}
