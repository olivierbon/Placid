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
        
    // Set the attributes for the model
    $atts = array(
      'name' => craft()->request->getPost('name'),
      'forceQuery' => craft()->request->getPost('forceQuery'),
      'encoded_token' => craft()->request->getPost('token'),
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
      craft()->urlManager->setRouteVariables(array('token' => $model));
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
    craft()->placid_token->deleteTokenById($id);

    $this->returnJson(array('success' => true));
  }
}