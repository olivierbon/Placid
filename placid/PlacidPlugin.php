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
    return '1.0.6';
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
            'placid/edit/request/(?P<requestId>\d+)' => 'placid/_edit',
            'placid/edit/token/(?P<tokenId>\d+)' => 'placid/_editToken',
            'placid/addToken' => 'placid/_editToken',
            'placid/add' => 'placid/_edit',
            'placid/oauth' => 'placid/_oauth',
            'placid/auth' => 'placid/_auth',
       );
  }
  function registerCachePaths()
  {
      return array(
          craft()->path->getStoragePath().'placid_requests/' => Craft::t('Placid requests'),
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