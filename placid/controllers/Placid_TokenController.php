<?php
namespace Craft;

class Placid_TokenController extends BaseController
{
  /**
  * Action to save request
  *
  * @return null
  */
  public function actionSave()
  {
    Craft::log(__METHOD__, LogLevel::Info, true);

    // Determine whether this is an existing or new request
    // -----------------------------------------------------------------------------

    if($id = craft()->request->getPost('tokenId')) {
      $model = craft()->placid_token->findTokenById($id);
    } else {
      $model = craft()->placid_token->newToken($id);
    }

    // Hash the token
    $token = craft()->security->hashData(craft()->request->getPost('accessToken'));
        
    // Set the attributes for the model
    $atts = array(
      'name' => craft()->request->getPost('requestName'),
      'encoded_token' => $token,
      'token_handle' => craft()->request->getPost('handle'),
    );

    // Set the attributes to the model
    $model->setAttributes($atts);

    // Try and save the token, otherwise show an error
    // -----------------------------------------------------------------

    if(craft()->placid_token->saveToken($model))
    {
      craft()->userSession->setNotice(Craft::t('Access token saved'));
      return $this->redirectToPostedUrl(array('tokenId' => $model->getAttribute('id')));
    }
    else
    {
      craft()->userSession->setError(Craft::t("Couldn't save token."));
      craft()->urlManager->setRouteVariables(array('auth' => $model));
    }
  }

  /**
  * Delete Token
  *
  * Delete an existing token
  */
  public function actionDeleteToken()
  {
    $this->requirePostRequest();
    $this->requireAjaxRequest();

    $id = craft()->request->getRequiredPost('id');
    craft()->placid_token->deleteRecordById($id);

    $this->returnJson(array('success' => true));
  }
}