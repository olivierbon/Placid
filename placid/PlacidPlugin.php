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
    return '1.8.122';
  }
  public function getSchemaVersion()
  {
      return '1.1.0';
  }
  function getDeveloper()
  {
    return 'Alec Ritson';
  }
  function getDocumentationUrl()
  {
    return 'https://github.com/alecritson/Placid/wiki';
  }
  public function getReleaseFeedUrl()
  {
      return 'https://raw.githubusercontent.com/alecritson/Placid/master/placid/manifest.json#';
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
            'placid'                              => array('action' => 'placid/placidIndex'),
            'placid/requests/(?P<requestId>\d+)'  => array('action' => 'placid/editRequest'),
            'placid/requests/new'                 => array('action' => 'placid/editRequest'),

            'placid/auth'                         => array('action' => 'placid/authIndex'),
            'placid/auth/(?P<tokenId>\d+)'        => array('action' => 'placid/editAuth'),
            'placid/auth/new'                     => array('action' => 'placid/editAuth'),

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
          'cache' => array(AttributeType::Bool, 'default' => true),
          'widgetTemplatesPath' => array(AttributeType::String, 'default' => '_widgets/placid')
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
