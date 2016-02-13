<?php
namespace Craft;

class PlacidVariable
{
    public function get($handle, $options = array())
    {
        return $this->request($handle, $options);
    }
    public function request($handle, $config = array())
    {
        $response = craft()->placid_requests->request($handle, $config);
        return new Placid_ResponseVariable($response);
    }
    public function getOauthToken($provider)
    {
        return craft()->placid_oAuth->findByHandle($provider);
    }
    public function token($provider) {
        return craft()->placid_requests->getToken($provider);
    }
}