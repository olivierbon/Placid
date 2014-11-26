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
    public function getaccessTokens()
    {
        return craft()->placid_token->getAllTokens();
    }
    public function getAllRequests()
    {
        return craft()->placid_requests->getAllRequests();
    }
    public function accesstoken($id) {
        return craft()->placid_token->findTokenById($id);
    }
    public function token($provider) {
        return craft()->placid_requests->getToken($provider);
    }
    public function findRequestById($id)
    {
        return craft()->placid_requests->findRequestById($id);
    }
    public function selectTokens()
    {
        $values = array();
        $tokens = craft()->placid_token->getAllTokens();
        $values[null] = 'None';
        foreach($tokens as $key => $value) {
            $values[$value['id']] = ucfirst($value['name']);
        }
        return $values;
    }
    public function getProviders()
    {
        $values = array();
        $providers = craft()->oauth->getProviders();
        $values[null] = 'None';
        foreach($providers as $key => $value) {
            $values[$key] = $value['name'];
        }
        return $values;
    }
}