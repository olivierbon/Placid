<?php
namespace Craft;

class Placid_TokenController extends BaseController
{

    public function actionSave()
    {
        Craft::log(__METHOD__, LogLevel::Info, true);

        if($id = craft()->request->getPost('tokenId')) {
          $model = craft()->placid_token->findRequestById($id);
        } else {
          $model = craft()->placid_token->newToken($id);
        }

        $token = craft()->security->hashData(craft()->request->getPost('accessToken'));
        $atts = array(
          'name' => craft()->request->getPost('requestName'),
          'encoded_token' => $token,
          'token_handle' => craft()->request->getPost('handle'),
        );


        $model->setAttributes($atts);


        if(craft()->placid_token->saveToken($model)) {
          craft()->userSession->setNotice(Craft::t('Access token saved'));
          return $this->redirectToPostedUrl(array('tokenId' => $model->getAttribute('id')));
        } else {
          craft()->userSession->setError(Craft::t("Couldn't save token."));

            craft()->urlManager->setRouteVariables(array('auth' => $model));
        }
    }

     /**
     * Delete Token
     *
     * Delete an existing ingredient
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