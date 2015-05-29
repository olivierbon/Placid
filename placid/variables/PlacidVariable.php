<?php
namespace Craft;



class PlacidVariable
{

    public function getToken($provider)
    {
        return craft()->placid_requests->getToken($provider);
    }
    public function get($handle, $options = array())
    {
        return $this->request($handle, $options);
    }
    public function request($handle, $options = array())
    {
        return craft()->placid_requests->getOptions($options)->request($handle);
    }
    public function token($provider) {
        return craft()->placid_requests->getToken($provider);
    }
}