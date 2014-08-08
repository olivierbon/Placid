<?php
namespace Craft;

class Placid_RequestsController extends BaseController
{

    private function _prepParams($array, $params = null)
    {
      $params = base64_encode(serialize($array));
      return $params;
    }
    public function actionSaveRequest()
    {
        Craft::log(__METHOD__, LogLevel::Info, true);

        if($id = craft()->request->getPost('requestId')) {
          $model = craft()->placid_requests->findRequestById($id);
        } else {
          $model = craft()->placid_requests->newRequest($id);
        }

       $params = craft()->request->getPost('params');

       $params = $this->_prepParams($params);

        $atts = array(
          'name' => craft()->request->getPost('requestName'),
          'handle' => craft()->request->getPost('handle'),
          'oauth' => craft()->request->getPost('oauth'),
          'tokenId' => craft()->request->getPost('tokenId'),
          'url' => craft()->request->getPost('requestUrl'),
          'params' => $params,
        );

        $model->setAttributes($atts);

        if(craft()->placid_requests->saveRequest($model)) {
          craft()->userSession->setNotice(Craft::t('Request saved'));
          return $this->redirectToPostedUrl(array('requestId' => $model->getAttribute('id')));
        } else {
          craft()->userSession->setError(Craft::t("Couldn't save request."));

            craft()->urlManager->setRouteVariables(array('request' => $model));
        }
    }

     /**
     * Delete Ingredient
     *
     * Delete an existing ingredient
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
        if($response = craft()->oauth->connect(array(
            'plugin' => 'placid',
            'provider' => $provider
        )))
        {
            if($response['success'])
            {
                // token
                $token = $response['token'];

                // save token
                craft()->placid_requests->saveToken($token, $provider);

                // session notice
                craft()->userSession->setNotice(Craft::t("Connected to Twitter."));
            }
            else
            {
                craft()->userSession->setError(Craft::t($response['errorMsg']));
            }

            $this->redirect($response['redirect']);
        }
    }

    /**
     * Disconnect
     */
    public function actionDisconnect($provider)
    {
        // reset token
        craft()->placid_requests->saveToken(null, $provider);

        // set notice
        craft()->userSession->setNotice(Craft::t("Disconnected from Twitter."));

        // redirect
        $redirect = craft()->request->getUrlReferrer();
        $this->redirect($redirect);
    }
}