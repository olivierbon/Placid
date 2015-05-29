<?php

namespace Craft;

class PlacidPlugin extends BasePlugin
{
  function getName()
  {
    return Craft::t('Placid');
  }
  function getVersion()
  {
    return '1.3.0';
  }
  function getDeveloper()
  {
    return 'Alec Ritson';
  }
  function getDeveloperUrl()
  {
    return 'http://alecritson.co.uk';
  }
  public function hasCpSection()
    {
        return true;
    }
  public function registerCpRoutes()
    {
        return array(
            'placid' => array('action' => 'placid/placidIndex'),
            'placid/requests/(?P<requestId>\d+)'  => array('action' => 'placid/editRequest'),
            'placid/requests/new'                 => array('action' => 'placid/editRequest'),

            'placid/auth'                       => array('action' => 'placid/authIndex'),
            'placid/auth/(?P<tokenId>\d+)'      => array('action' => 'placid/editAuth'),
            'placid/auth/new'                   => array('action' => 'placid/editAuth'),

            'placid/oauth'                        => array('action' => 'placid/oauthIndex'),
       );
  }
  /**
   * Defines the settings.
   *
   * @access protected
   * @return array
   */
  protected function defineSettings()
  {
      return array(
          'twitter' => array(AttributeType::Number),
          'github' => array(AttributeType::Number),
          'instagram' => array(AttributeType::Number),
          'cache' => array(AttributeType::Bool, 'default' => true),
      );
  }

  public function getSettingsHtml()
    {
       return craft()->templates->render('placid/settings', array(
           'settings' => $this->getSettings()
       ));
   }

/**
 * Remove all tokens related to this plugin when uninstalled
 */
public function onBeforeUninstall()
{
    if(isset(craft()->oauth))
    {
        craft()->oauth->deleteTokensByPlugin('placid');
    }
}

  public function onAfterInstall()
  {
      $exampleRequest = array('name'=> 'Dribbble shots', 'url' => 'http://api.dribbble.com/shots/everyone', 'handle' => 'dribbbleShots', 'oauth' => '', 'params' => '');
      craft()->db->createCommand()->insert('placid_requests', $exampleRequest);
  }

}