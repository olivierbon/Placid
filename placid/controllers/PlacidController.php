<?php
namespace Craft;

class PlacidController extends BaseController
{

	// Request CP methods
	// =============================================================================

	/**
	 * The main cp page for placid in craft
	 * @return  Loads template
	 */
	public function actionPlacidIndex()
	{
		$variables['requests'] = craft()->placid_requests->findAllRequests();
		$this->renderTemplate('placid/requests/index', $variables);
	}

	/**
	 * Action to show the request in admin area
	 * @param  array  $variables Variables to go back to the templates
	 * @return null
	 */
	public function actionEditRequest(array $variables = array())
	{
		// $this->poop($variables);

		if(!array_key_exists('request', $variables))
		{
			$variables['request'] = null;
		}

		// Get the request by its ID or not
		if(array_key_exists('requestId', $variables))
		{
			$variables['request'] = craft()->placid_requests->getRequestById($variables['requestId']);
			if(!$variables['request'])
			{
				throw new HttpException(404);
			}
		}
		// Get the access tokens
		$variables['accessTokens'] = $this->_getAccessTokens();
		// Get any oauth providers
		$variables['providers'] = $this->_getProviders();
		$this->renderTemplate('placid/requests/edit', $variables);
	}

	// Auth CP methods
	// =============================================================================

	/**
	 * Renders the Auth template in the CP
	 * @param  array  $variables Variables available to the template
	 * @return
	 */
	public function actionAuthIndex(array $variables = array())
	{
		$variables['tokens'] = craft()->placid_token->getAllTokens();
		$this->renderTemplate('placid/auth/index', $variables);
	}

	public function actionEditAuth(array $variables = array())
	{
		if(!array_key_exists('token', $variables))
		{
			$variables['token'] = null;
		}

		// Get the request by its ID or not
		if(array_key_exists('tokenId', $variables))
		{
			$variables['token'] = craft()->placid_token->findTokenById($variables['tokenId']);
			if(!$variables['token'])
			{
				throw new HttpException(404);
			}
		}
		// Get the access tokens
		$variables['tokens'] = $this->_getAccessTokens();

		$this->renderTemplate('placid/auth/edit', $variables);
	}

	// OAuth CP methods
	// =============================================================================

	public function actionOAuthIndex(array $variables = array())
	{
		// Get the OAuth Plugin
		$variables['oauthPlugin'] = craft()->plugins->getPlugin('oauth');

		// By default, no providers are set
		$variables['providers'] = null;

		// If we have the plugin and it's installed and enabled, get some providers!
		if($variables['oauthPlugin'] && $variables['oauthPlugin']->isInstalled && $variables['oauthPlugin']->isEnabled)
		{
			$variables['providers'] = craft()->oauth->getProviders();
		}

		$this->renderTemplate('placid/oauth/index', $variables);
	}

	// Private methods
	// =============================================================================

	/**
	 * Returns any OAuth providers
	 * @return Mixed null|Array Either null or an array of providers
	 */
	private function _getProviders()
	{
		$providers = craft()->placid_oAuth->findAllProviders();
		if($providers)
		{
			$values = array();
			$values[null] = 'None';
			foreach($providers as $key => $value) {
	            $values[$value['handle']] = ucfirst($value['handle']);
	        }
	        return $values;
		}
        return null;
	}

	/**
	 * Returns an array of access tokens
	 * @return Array Access tokens for a select
	 */
	private function _getAccessTokens()
	{
		$values = array();
        $tokens = craft()->placid_token->getAllTokens();
        $values[null] = 'None';
        foreach($tokens as $key => $value) {
            $values[$value['id']] = ucfirst($value['name']);
        }
        return $values;
	}
}
